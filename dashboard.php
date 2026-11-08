
<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

function dashboardNumber($data){
    preg_match('/[0-9]+(\.[0-9]+)?/', strip_tags($data), $matches);
    return isset($matches[0]) ? (float)$matches[0] : 0;
}

function dashboardMoney($amount){
    $amount = (float)$amount;

    if($amount >= 1000000000){
        return rtrim(rtrim(number_format($amount / 1000000000, 2), '0'), '.') . 'B';
    }

    if($amount >= 1000000){
        return rtrim(rtrim(number_format($amount / 1000000, 2), '0'), '.') . 'M';
    }

    if($amount >= 1000){
        return rtrim(rtrim(number_format($amount / 1000, 2), '0'), '.') . 'K';
    }

    return number_format($amount, 2);
}

function dashboardRange($period){
    $today = date('Y-m-d');

    if($period == 'today'){
        return [$today, $today];
    }

    if($period == 'yesterday'){
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        return [$yesterday, $yesterday];
    }

    if($period == 'month'){
        return [date('Y-m-d', strtotime('-1 month')), $today];
    }

    if($period == 'year'){
        return [date('Y-m-d', strtotime('-1 year')), $today];
    }

    return [$today, $today];
}

$incToday     = dashboardNumber(getDashboardIncome('today'));
$incYesterday = dashboardNumber(getDashboardIncome('yesterday'));
$incMonth     = dashboardNumber(getDashboardIncome('month'));
$incYear      = dashboardNumber(getDashboardIncome('year'));

$expToday     = dashboardNumber(getDashboardExpense('today'));
$expYesterday = dashboardNumber(getDashboardExpense('yesterday'));
$expMonth     = dashboardNumber(getDashboardExpense('month'));
$expYear      = dashboardNumber(getDashboardExpense('year'));

$monthlyWarning = ($incMonth > 0 && $expMonth > ($incMonth * 0.40));
$warningPercent = $incMonth > 0 ? ($expMonth / $incMonth) * 100 : 0;

$daysInCurrentMonth = (int)date('t');
$monthlyAllowedExpense = $incMonth * 0.40;
$dailyAllowedExpense = $daysInCurrentMonth > 0 ? ($monthlyAllowedExpense / $daysInCurrentMonth) : 0;
$remainingMonthlyAllowance = max(0, $monthlyAllowedExpense - $expMonth);

$periods = ['today','yesterday','month','year'];

$incomeCategories = [];
$expenseCategories = [];

$transactions = [
    'income' => [],
    'expense' => []
];

foreach($periods as $period){

    [$from, $to] = dashboardRange($period);

    $incomeCategories[$period] = [];
    $expenseCategories[$period] = [];

    $incomeSql = "SELECT source, income_category, amount, details, income_date
                  FROM income
                  WHERE added_by='" . $_SESSION['UID'] . "'
                  AND income_date BETWEEN '$from' AND '$to'
                  ORDER BY income_date DESC, id DESC";

    $incomeRes = mysqli_query($con, $incomeSql);

    if($incomeRes){

        while($row = mysqli_fetch_assoc($incomeRes)){

            $category = !empty($row['income_category']) ? $row['income_category'] : 'Other';
            $amount = (float)$row['amount'];

            if(!isset($incomeCategories[$period][$category])){
                $incomeCategories[$period][$category] = 0;
            }

            $incomeCategories[$period][$category] += $amount;

            $transactions['income'][$period][] = [
                'source' => $row['source'],
                'category' => $category,
                'amount' => $amount,
                'details' => !empty($row['details']) ? $row['details'] : 'No details',
                'date' => $row['income_date']
            ];
        }
    }

    $expenseSql = "SELECT expense.item, expense.price, expense.details,
                          expense.expense_date, expense.expense_type,
                          category.name AS category_name
                   FROM expense
                   LEFT JOIN category ON expense.category_id = category.id
                   WHERE expense.added_by='" . $_SESSION['UID'] . "'
                   AND expense.expense_date BETWEEN '$from' AND '$to'
                   ORDER BY expense.expense_date DESC, expense.id DESC";

    $expenseRes = mysqli_query($con, $expenseSql);

    if($expenseRes){

        while($row = mysqli_fetch_assoc($expenseRes)){

            $category = !empty($row['category_name']) ? $row['category_name'] : 'Other';
            $amount = (float)$row['price'];

            if(!isset($expenseCategories[$period][$category])){
                $expenseCategories[$period][$category] = 0;
            }

            $expenseCategories[$period][$category] += $amount;

            $transactions['expense'][$period][] = [
                'item' => $row['item'],
                'category' => $category,
                'amount' => $amount,
                'details' => !empty($row['details']) ? $row['details'] : 'No details',
                'date' => $row['expense_date'],
                'type' => !empty($row['expense_type']) ? $row['expense_type'] : 'Other'
            ];
        }
    }
}

$incomeCategories = array_map(function($items){

    $result = [];

    foreach($items as $name => $amount){
        $result[] = [
            'name' => $name,
            'amount' => $amount
        ];
    }

    return $result;

}, $incomeCategories);

$expenseCategories = array_map(function($items){

    $result = [];

    foreach($items as $name => $amount){
        $result[] = [
            'name' => $name,
            'amount' => $amount
        ];
    }

    return $result;

}, $expenseCategories);
?>

<link rel="stylesheet" href="dashboard.css?v=6">

<div class="dashboard-page">

    <div class="dashboard-heading">

        <div>
            <h2><i class="fa-solid fa-chart-pie"></i> Dashboard</h2>
            <p>Overview of your income and expenses</p>
        </div>

        <div class="dashboard-date">
            <i class="fa-regular fa-calendar"></i>
            <?php echo date('d M Y'); ?>
        </div>

    </div>

    <div class="dashboard-tabs">

        <button class="dashboard-tab active" id="incomeTab" onclick="showDashboardTab('income')">
            <i class="fa-solid fa-money-bill-trend-up"></i> Income
        </button>

        <button class="dashboard-tab" id="expenseTab" onclick="showDashboardTab('expense')">
            <i class="fa-solid fa-money-bill-wave"></i> Expense
        </button>

    </div>

    <!-- INCOME SECTION -->

    <section id="incomeSection" class="dashboard-section">

        <div class="summary-grid">

            <div class="summary-card income-card today-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-wallet"></i></div>
                    <span>Today Income</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($incToday); ?></h3>

                <button class="details-btn" onclick="openDetails('income','today')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="summary-card income-card yesterday-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <span>Yesterday Income</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($incYesterday); ?></h3>

                <button class="details-btn" onclick="openDetails('income','yesterday')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="summary-card income-card month-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <span>This Month Income</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($incMonth); ?></h3>

                <button class="details-btn" onclick="openDetails('income','month')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="summary-card income-card year-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-trophy"></i></div>
                    <span>This Year Income</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($incYear); ?></h3>

                <button class="details-btn" onclick="openDetails('income','year')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

        </div>

        <div class="analysis-grid">

            <div class="analysis-card large-chart-card">

                <div class="analysis-title">
                    <div>
                        <h3><i class="fa-solid fa-chart-line"></i> Income Overview</h3>
                        <p>Income amount across your dashboard periods</p>
                    </div>
                </div>

                <canvas id="incomeTrendChart"></canvas>

            </div>

            <div class="analysis-card pie-card">

                <div class="analysis-title">

                    <div>
                        <h3><i class="fa-solid fa-chart-pie"></i> Income Breakdown</h3>
                        <p id="incomePiePeriod">This Month</p>
                    </div>

                    <div class="period-buttons income-period-buttons">

                        <button onclick="setPeriodButton('.income-period-buttons','today'); drawIncomePie('today')">Today</button>

                        <button onclick="setPeriodButton('.income-period-buttons','yesterday'); drawIncomePie('yesterday')">Yesterday</button>

                        <button class="active" onclick="setPeriodButton('.income-period-buttons','month'); drawIncomePie('month')">This Month</button>

                        <button onclick="setPeriodButton('.income-period-buttons','year'); drawIncomePie('year')">This Year</button>

                    </div>

                </div>

                <div class="pie-wrap">
                    <canvas id="incomePieChart"></canvas>
                    <div id="incomePieEmpty" class="chart-empty">No income data</div>
                </div>

                <div id="incomeTotalBelow" class="pie-total"></div>
                <div id="incomeLegend" class="chart-legend"></div>

            </div>

        </div>

    </section>

    <!-- EXPENSE SECTION -->

    <section id="expenseSection" class="dashboard-section" style="display:none;">

        <div class="summary-grid">

            <div class="summary-card expense-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-wallet"></i></div>
                    <span>Today Expense</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($expToday); ?></h3>

                <button class="details-btn" onclick="openDetails('expense','today')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="summary-card expense-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <span>Yesterday Expense</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($expYesterday); ?></h3>

                <button class="details-btn" onclick="openDetails('expense','yesterday')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="summary-card expense-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <span>This Month Expense</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($expMonth); ?></h3>

                <button class="details-btn" onclick="openDetails('expense','month')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

            <div class="summary-card expense-card">

                <div class="summary-top">
                    <div class="summary-icon"><i class="fa-solid fa-calendar"></i></div>
                    <span>This Year Expense</span>
                </div>

                <h3>Rs. <?php echo dashboardMoney($expYear); ?></h3>

                <button class="details-btn" onclick="openDetails('expense','year')">
                    View Details <i class="fa-solid fa-arrow-right"></i>
                </button>

            </div>

        </div>

        <?php if($incMonth > 0){ ?>

            <div class="monthly-warning <?php echo $monthlyWarning ? 'warning-danger' : 'warning-safe'; ?>">

                <div class="warning-icon">
                    <i class="fa-solid <?php echo $monthlyWarning ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?>"></i>
                </div>

                <div class="warning-content">

                    <?php if($monthlyWarning){ ?>

                        <strong>Monthly Expense Warning</strong>

                        <p>
                            Your expense is <?php echo number_format($warningPercent,1); ?>%
                            of your monthly income and has crossed the 40% limit.
                        </p>

                    <?php }else{ ?>

                        <strong>Daily Spending Guidance</strong>

                        <p>
                            Based on your monthly income, you can spend up to
                            <b>Rs. <?php echo dashboardMoney($dailyAllowedExpense); ?></b>
                            per day to keep monthly expense within 40%.
                        </p>

                    <?php } ?>

                    <div class="budget-figures">

                        <span>
                            40% Monthly Limit:
                            <b>Rs. <?php echo dashboardMoney($monthlyAllowedExpense); ?></b>
                        </span>

                        <span>
                            Remaining:
                            <b>Rs. <?php echo dashboardMoney($remainingMonthlyAllowance); ?></b>
                        </span>

                    </div>

                </div>

            </div>

        <?php }else{ ?>

            <div class="monthly-warning warning-neutral">

                <div class="warning-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div class="warning-content">

                    <strong>Spending Guidance</strong>

                    <p>
                        Add monthly income to calculate your 40% expense limit
                        and recommended daily spending amount.
                    </p>

                </div>

            </div>

        <?php } ?>

        <div class="analysis-grid">

            <div class="analysis-card large-chart-card">

                <div class="analysis-title">
                    <div>
                        <h3><i class="fa-solid fa-chart-line"></i> Expense Overview</h3>
                        <p>Expense amount across your dashboard periods</p>
                    </div>
                </div>

                <canvas id="expenseTrendChart"></canvas>

            </div>

            <div class="analysis-card pie-card">

                <div class="analysis-title">

                    <div>
                        <h3><i class="fa-solid fa-chart-pie"></i> Expense Breakdown</h3>
                        <p id="expensePiePeriod">This Month</p>
                    </div>

                    <div class="period-buttons expense-period-buttons">

                        <button onclick="setPeriodButton('.expense-period-buttons','today'); drawExpensePie('today')">Today</button>

                        <button onclick="setPeriodButton('.expense-period-buttons','yesterday'); drawExpensePie('yesterday')">Yesterday</button>

                        <button class="active" onclick="setPeriodButton('.expense-period-buttons','month'); drawExpensePie('month')">This Month</button>

                        <button onclick="setPeriodButton('.expense-period-buttons','year'); drawExpensePie('year')">This Year</button>

                    </div>

                </div>

                <div class="pie-wrap">
                    <canvas id="expensePieChart"></canvas>
                    <div id="expensePieEmpty" class="chart-empty">No expense data</div>
                </div>

                <div id="expenseTotalBelow" class="pie-total"></div>
                <div id="expenseLegend" class="chart-legend"></div>

            </div>

        </div>

    </section>

</div>

<!-- COMPACT DETAILS MODAL -->

<div id="detailsModal" class="details-modal">

    <div class="details-box">

        <button class="modal-close" onclick="closeDetails()">&times;</button>

        <div class="modal-heading">

            <div id="modalIcon" class="modal-icon"></div>

            <div>
                <h2 id="modalTitle">Transaction Details</h2>
                <p id="modalSubtitle"></p>
            </div>

        </div>

        <div class="modal-analysis">

            <div class="modal-chart-area">
                <canvas id="detailsPieChart"></canvas>
                <div id="detailsPieEmpty" class="chart-empty">No chart data</div>
            </div>

            <div class="modal-summary-area">
                <div class="modal-total" id="modalTotal">Rs. 0</div>
                <div id="detailsPieLegend" class="chart-legend"></div>
            </div>

        </div>

        <div class="details-section-title">
            Individual Transactions
        </div>

        <div id="transactionList" class="transaction-list"></div>

    </div>

</div>

<script>

const dashboardData = <?php echo json_encode([
    'income' => [
        'today' => $incToday,
        'yesterday' => $incYesterday,
        'month' => $incMonth,
        'year' => $incYear
    ],
    'expense' => [
        'today' => $expToday,
        'yesterday' => $expYesterday,
        'month' => $expMonth,
        'year' => $expYear
    ],
    'incomeCategories' => $incomeCategories,
    'expenseCategories' => $expenseCategories,
    'transactions' => $transactions
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;


function money(value){

    value = Number(value || 0);

    if(value >= 1000000000){
        return (value / 1000000000).toFixed(2).replace(/\.?0+$/, '') + 'B';
    }

    if(value >= 1000000){
        return (value / 1000000).toFixed(2).replace(/\.?0+$/, '') + 'M';
    }

    if(value >= 1000){
        return (value / 1000).toFixed(2).replace(/\.?0+$/, '') + 'K';
    }

    return value.toLocaleString('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

}


/* DASHBOARD TAB */

function showDashboardTab(type){

    const incomeSection = document.getElementById('incomeSection');
    const expenseSection = document.getElementById('expenseSection');
    const incomeTab = document.getElementById('incomeTab');
    const expenseTab = document.getElementById('expenseTab');

    if(type === 'income'){

        incomeSection.style.display = 'block';
        expenseSection.style.display = 'none';

        incomeTab.classList.add('active');
        expenseTab.classList.remove('active');

        requestAnimationFrame(() => {

            drawTrend(
                'incomeTrendChart',
                [
                    dashboardData.income.today,
                    dashboardData.income.yesterday,
                    dashboardData.income.month,
                    dashboardData.income.year
                ],
                ['Today', 'Yesterday', 'Month', 'Year'],
                '#16a34a'
            );

        });

    }else{

        incomeSection.style.display = 'none';
        expenseSection.style.display = 'block';

        incomeTab.classList.remove('active');
        expenseTab.classList.add('active');

        requestAnimationFrame(() => {

            drawTrend(
                'expenseTrendChart',
                [
                    dashboardData.expense.today,
                    dashboardData.expense.yesterday,
                    dashboardData.expense.month,
                    dashboardData.expense.year
                ],
                ['Today', 'Yesterday', 'Month', 'Year'],
                '#ef4444'
            );

        });

    }

}


/* TREND CHART */

function drawTrend(canvasId, values, labels, lineColor){

    const canvas = document.getElementById(canvasId);

    if(!canvas) return;

    const rect = canvas.getBoundingClientRect();

    if(rect.width <= 0) return;

    const width = Math.max(320, Math.floor(rect.width));
    const height = 300;
    const dpr = window.devicePixelRatio || 1;

    canvas.width = width * dpr;
    canvas.height = height * dpr;

    canvas.style.width = '100%';
    canvas.style.height = height + 'px';

    const ctx = canvas.getContext('2d');

    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const left = 65;
    const right = 25;
    const top = 25;
    const bottom = 55;

    const chartWidth = width - left - right;
    const chartHeight = height - top - bottom;

    const actualMax = Math.max(...values);
    const maxValue = Math.max(actualMax, 100);

    const noData = values.every(value => Number(value) === 0);

    ctx.clearRect(0, 0, width, height);

    ctx.font = '12px Arial';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#64748b';

    for(let i = 0; i <= 4; i++){

        const y = top + (chartHeight / 4) * i;
        const value = maxValue - (maxValue / 4) * i;

        ctx.beginPath();
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1;
        ctx.moveTo(left, y);
        ctx.lineTo(width - right, y);
        ctx.stroke();

        ctx.fillText('Rs. ' + money(value), left - 10, y);

    }

    const points = values.map((value, index) => ({

        x: left + (chartWidth / (values.length - 1)) * index,

        y: top + chartHeight - ((value / maxValue) * chartHeight),

        value: Number(value)

    }));

    if(noData){

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = '600 14px Arial';
        ctx.fillStyle = '#94a3b8';

        ctx.fillText(
            'No data available',
            width / 2,
            top + chartHeight / 2
        );

    }else{

        const gradient = ctx.createLinearGradient(
            0,
            top,
            0,
            height - bottom
        );

        gradient.addColorStop(0, lineColor + '30');
        gradient.addColorStop(1, lineColor + '00');

        ctx.beginPath();

        ctx.moveTo(points[0].x, height - bottom);

        points.forEach(point => {
            ctx.lineTo(point.x, point.y);
        });

        ctx.lineTo(
            points[points.length - 1].x,
            height - bottom
        );

        ctx.closePath();

        ctx.fillStyle = gradient;
        ctx.fill();

        ctx.beginPath();

        points.forEach((point, index) => {

            if(index === 0){
                ctx.moveTo(point.x, point.y);
            }else{
                ctx.lineTo(point.x, point.y);
            }

        });

        ctx.strokeStyle = lineColor;
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();

        points.forEach(point => {

            ctx.beginPath();
            ctx.arc(point.x, point.y, 6, 0, Math.PI * 2);

            ctx.fillStyle = '#ffffff';
            ctx.fill();

            ctx.strokeStyle = lineColor;
            ctx.lineWidth = 3;
            ctx.stroke();

            if(point.value > 0){

                ctx.font = '700 12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.fillStyle = '#1e293b';

                ctx.fillText(
                    'Rs. ' + money(point.value),
                    point.x,
                    point.y - 12
                );

            }

        });

    }

    points.forEach((point, index) => {

        ctx.font = '600 12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#64748b';

        ctx.fillText(
            labels[index],
            point.x,
            height - 20
        );

    });

}


/* PIE CHART */

function drawPie(canvasId, emptyId, legendId, totalId, data, type){

    const canvas = document.getElementById(canvasId);
    const empty = document.getElementById(emptyId);
    const legend = document.getElementById(legendId);

    const totalBox = totalId
        ? document.getElementById(totalId)
        : null;

    if(!canvas || !empty || !legend) return;

    data = data || [];

    const total = data.reduce(
        (sum, item) => sum + Number(item.amount),
        0
    );

    if(total <= 0){

        canvas.style.display = 'none';
        empty.style.display = 'block';

        legend.innerHTML = '';

        if(totalBox){

            totalBox.innerHTML = `
                <span>Total</span>
                <strong>Rs. 0</strong>
            `;

        }

        return;

    }

    canvas.style.display = 'block';
    empty.style.display = 'none';

    const size = 230;
    const dpr = window.devicePixelRatio || 1;

    canvas.width = size * dpr;
    canvas.height = size * dpr;

    canvas.style.width = size + 'px';
    canvas.style.height = size + 'px';

    const ctx = canvas.getContext('2d');

    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    ctx.clearRect(0, 0, size, size);

    const colors = [
        '#2563eb',
        '#16a34a',
        '#f59e0b',
        '#8b5cf6',
        '#ef4444',
        '#0891b2',
        '#e11d48',
        '#64748b'
    ];

    let highestIndex = 0;

    data.forEach((item, index) => {

        if(Number(item.amount) > Number(data[highestIndex].amount)){
            highestIndex = index;
        }

    });

    let start = -Math.PI / 2;

    data.forEach((item, index) => {

        const amount = Number(item.amount);
        const slice = (amount / total) * Math.PI * 2;

        let color = colors[index % colors.length];

        if(type === 'expense' && index === highestIndex){
            color = '#dc2626';
        }

        ctx.beginPath();
        ctx.moveTo(115, 115);

        ctx.arc(
            115,
            115,
            105,
            start,
            start + slice
        );

        ctx.closePath();

        ctx.fillStyle = color;
        ctx.fill();

        start += slice;

    });

    ctx.beginPath();
    ctx.arc(115, 115, 58, 0, Math.PI * 2);

    ctx.fillStyle = '#ffffff';
    ctx.fill();

    if(totalBox){

        totalBox.innerHTML = `
            <span>Total ${type === 'expense' ? 'Expense' : 'Income'}</span>
            <strong>Rs. ${money(total)}</strong>
        `;

    }

    legend.innerHTML = '';

    data.forEach((item, index) => {

        const percent = (Number(item.amount) / total) * 100;

        let color = colors[index % colors.length];

        if(type === 'expense' && index === highestIndex){
            color = '#dc2626';
        }

        legend.innerHTML += `

            <div class="legend-item ${type === 'expense' && index === highestIndex ? 'highest-item' : ''}">

                <div class="legend-name">

                    <span class="legend-dot" style="background:${color}"></span>

                    <span>${escapeHtml(item.name)}</span>

                </div>

                <div class="legend-value">

                    <strong>Rs. ${money(item.amount)}</strong>

                    <small>${percent.toFixed(1)}%</small>

                </div>

            </div>

        `;

    });

}


/* PERIOD BUTTONS */

function setPeriodButton(selector, period){

    const buttons = document.querySelectorAll(selector + ' button');

    const indexMap = {
        today: 0,
        yesterday: 1,
        month: 2,
        year: 3
    };

    buttons.forEach(button => {
        button.classList.remove('active');
    });

    if(buttons[indexMap[period]]){
        buttons[indexMap[period]].classList.add('active');
    }

}


/* INCOME PIE */

function drawIncomePie(period){

    const labels = {
        today: 'Today',
        yesterday: 'Yesterday',
        month: 'This Month',
        year: 'This Year'
    };

    document.getElementById('incomePiePeriod').innerText = labels[period];

    drawPie(
        'incomePieChart',
        'incomePieEmpty',
        'incomeLegend',
        'incomeTotalBelow',
        dashboardData.incomeCategories[period],
        'income'
    );

}


/* EXPENSE PIE */

function drawExpensePie(period){

    const labels = {
        today: 'Today',
        yesterday: 'Yesterday',
        month: 'This Month',
        year: 'This Year'
    };

    document.getElementById('expensePiePeriod').innerText = labels[period];

    drawPie(
        'expensePieChart',
        'expensePieEmpty',
        'expenseLegend',
        'expenseTotalBelow',
        dashboardData.expenseCategories[period],
        'expense'
    );

}


/* TRANSACTION DETAILS */

function openDetails(type, period){

    const labels = {
        today: 'Today',
        yesterday: 'Yesterday',
        month: 'This Month',
        year: 'This Year'
    };

    const title = type === 'income'
        ? 'Income Details'
        : 'Expense Details';

    const records = dashboardData.transactions[type][period] || [];

    const total = dashboardData[type][period] || 0;

    const categories = type === 'income'
        ? dashboardData.incomeCategories[period]
        : dashboardData.expenseCategories[period];

    document.getElementById('detailsModal').style.display = 'flex';

    document.getElementById('modalTitle').innerText = title;

    document.getElementById('modalSubtitle').innerText =
        labels[period] + ' transactions';

    document.getElementById('modalTotal').innerText =
        'Rs. ' + money(total);

    const icon = document.getElementById('modalIcon');

    icon.innerHTML = type === 'income'
        ? '<i class="fa-solid fa-arrow-trend-up"></i>'
        : '<i class="fa-solid fa-arrow-trend-down"></i>';

    icon.className = 'modal-icon ' + type;

    drawPie(
        'detailsPieChart',
        'detailsPieEmpty',
        'detailsPieLegend',
        '',
        categories,
        type
    );

    const list = document.getElementById('transactionList');

    if(records.length === 0){

        list.innerHTML = `

            <div class="empty-transactions">

                <i class="fa-regular fa-folder-open"></i>

                <strong>No ${type} records found</strong>

                <span>No transaction was recorded for this period.</span>

            </div>

        `;

        return;

    }

    list.innerHTML = records.map(record => {

        if(type === 'income'){

            return `

                <div class="transaction-row income-row">

                    <div class="transaction-main">

                        <div class="transaction-avatar income-avatar">
                            <i class="fa-solid fa-arrow-down"></i>
                        </div>

                        <div>
                            <strong>${escapeHtml(record.source)}</strong>
                            <span>${escapeHtml(record.category)}</span>
                        </div>

                    </div>

                    <div class="transaction-info">

                        <strong class="income-text">
                            + Rs. ${money(record.amount)}
                        </strong>

                        <span>${escapeHtml(formatDate(record.date))}</span>

                    </div>

                    <div class="transaction-details">
                        ${escapeHtml(record.details)}
                    </div>

                </div>

            `;

        }

        return `

            <div class="transaction-row expense-row">

                <div class="transaction-main">

                    <div class="transaction-avatar expense-avatar">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>

                    <div>
                        <strong>${escapeHtml(record.item)}</strong>

                        <span>
                            ${escapeHtml(record.category)} ·
                            ${escapeHtml(record.type)}
                        </span>
                    </div>

                </div>

                <div class="transaction-info">

                    <strong class="expense-text">
                        - Rs. ${money(record.amount)}
                    </strong>

                    <span>${escapeHtml(formatDate(record.date))}</span>

                </div>

                <div class="transaction-details">
                    ${escapeHtml(record.details)}
                </div>

            </div>

        `;

    }).join('');

}


/* CLOSE MODAL */

function closeDetails(){

    document.getElementById('detailsModal').style.display = 'none';

}


/* DATE FORMAT */

function formatDate(dateString){

    const date = new Date(dateString + 'T00:00:00');

    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });

}


/* ESCAPE HTML */

function escapeHtml(value){

    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


/* MODAL OUTSIDE CLICK */

window.addEventListener('click', function(event){

    const modal = document.getElementById('detailsModal');

    if(event.target === modal){
        closeDetails();
    }

});


/* ESC KEY TO CLOSE MODAL */

window.addEventListener('keydown', function(event){

    if(event.key === 'Escape'){
        closeDetails();
    }

});


/* REDRAW CHARTS */

function redrawCharts(){

    drawTrend(
        'incomeTrendChart',
        [
            dashboardData.income.today,
            dashboardData.income.yesterday,
            dashboardData.income.month,
            dashboardData.income.year
        ],
        ['Today', 'Yesterday', 'Month', 'Year'],
        '#16a34a'
    );

    drawTrend(
        'expenseTrendChart',
        [
            dashboardData.expense.today,
            dashboardData.expense.yesterday,
            dashboardData.expense.month,
            dashboardData.expense.year
        ],
        ['Today', 'Yesterday', 'Month', 'Year'],
        '#ef4444'
    );

    drawIncomePie('month');
    drawExpensePie('month');

}


/* PAGE LOAD */

window.addEventListener('load', function(){

    redrawCharts();

});


/* WINDOW RESIZE */

window.addEventListener('resize', function(){

    redrawCharts();

});

</script>

<?php include('footer.php'); ?>
