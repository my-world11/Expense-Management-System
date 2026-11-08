
<?php

include('header.php');
checkUser();
userArea();
include('user_header.php');

// Format amount using K, M and B
function formatIncomeAmount($amount) {
    $amount = (float)$amount;

    if ($amount >= 1000000000) {
        return rtrim(rtrim(number_format($amount / 1000000000, 2, '.', ''), '0'), '.') . 'B';
    }

    if ($amount >= 1000000) {
        return rtrim(rtrim(number_format($amount / 1000000, 2, '.', ''), '0'), '.') . 'M';
    }

    if ($amount >= 1000) {
        return rtrim(rtrim(number_format($amount / 1000, 2, '.', ''), '0'), '.') . 'K';
    }

    return number_format($amount, 2);
}

// Delete Income Logic
if (
    isset($_GET['type']) &&
    $_GET['type'] == 'delete' &&
    isset($_GET['id']) &&
    $_GET['id'] > 0
) {

    $id = get_safe_value($_GET['id']);

    mysqli_query(
        $con,
        "DELETE FROM income
         WHERE id=$id
         AND added_by='" . $_SESSION['UID'] . "'"
    );

    redirect('income.php');
}

// Fetch Income Data - Ascending Order
$res = mysqli_query(
    $con,
    "SELECT *
     FROM income
     WHERE added_by='" . $_SESSION['UID'] . "'
     ORDER BY id ASC"
);

// Chart Data (Current Year)
$monthlyIncome = array_fill(1, 12, 0);
$currentYear = date('Y');

$chartRes = mysqli_query(
    $con,
    "SELECT
        MONTH(income_date) AS month_no,
        SUM(amount) AS total
     FROM income
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND YEAR(income_date)='$currentYear'
     GROUP BY MONTH(income_date)
     ORDER BY MONTH(income_date)"
);

if ($chartRes) {
    while ($chartRow = mysqli_fetch_assoc($chartRes)) {
        $monthNo = (int)$chartRow['month_no'];
        $monthlyIncome[$monthNo] = (float)$chartRow['total'];
    }
}

// Total Income Summary
$totalIncome = 0;
$totalRes = mysqli_query(
    $con,
    "SELECT SUM(amount) AS total
     FROM income
     WHERE added_by='" . $_SESSION['UID'] . "'"
);

if ($totalRes) {
    $totalRow = mysqli_fetch_assoc($totalRes);
    $totalIncome = (float)$totalRow['total'];
}

// Monthly Income
$monthIncomeRes = mysqli_query(
    $con,
    "SELECT SUM(amount) AS total
     FROM income
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND MONTH(income_date)=MONTH(CURDATE())
     AND YEAR(income_date)=YEAR(CURDATE())"
);

$monthIncomeRow = mysqli_fetch_assoc($monthIncomeRes);
$monthIncome = (float)$monthIncomeRow['total'];

// Yearly Income
$yearIncomeRes = mysqli_query(
    $con,
    "SELECT SUM(amount) AS total
     FROM income
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND YEAR(income_date)=YEAR(CURDATE())"
);

$yearIncomeRow = mysqli_fetch_assoc($yearIncomeRes);
$yearIncome = (float)$yearIncomeRow['total'];

?>

<style>
.income-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    box-sizing: border-box;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: #0f172a;
    background-color: #f8fafc;
}

.income-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    margin-bottom: 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.income-title-area {
    display: flex;
    align-items: center;
    gap: 14px;
}

.income-main-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #ecfdf5;
    font-size: 24px;
}

.income-title-area h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
}

.income-title-area h1 span {
    color: #0369a1;
}

.income-title-area p {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 13px;
}

.income-add-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    background: #047857;
    color: #ffffff !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(4, 120, 87, 0.2);
}

.income-add-button:hover {
    background: #065f46;
    transform: translateY(-1px);
}

.income-summary-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.income-summary-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.summary-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 20px;
    flex-shrink: 0;
}

.icon-green { background: #dcfce7; }
.icon-blue { background: #e0f2fe; }
.icon-purple { background: #f3e8ff; }
.icon-orange { background: #ffedd5; }

.income-summary-card small {
    display: block;
    margin-bottom: 2px;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
}

.income-summary-card strong {
    display: block;
    color: #0369a1;
    font-size: 18px;
    font-weight: 700;
}

.income-summary-card .record-count {
    color: #ea580c;
}

.income-list-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.income-list-header h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
}

.income-list-header p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #64748b;
}

.records-badge {
    padding: 4px 12px;
    background: #e2e8f0;
    border-radius: 20px;
    font-size: 12px;
    color: #475569;
    font-weight: 600;
}

.records-badge span {
    color: #047857;
    font-weight: 700;
}

.income-search-area {
    margin-bottom: 16px;
}

.income-search-box {
    width: 100%;
    height: 42px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 16px;
    box-sizing: border-box;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}

.income-search-box input {
    width: 100%;
    height: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    font-size: 14px;
    color: #0f172a;
}

.income-table-card {
    width: 100%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05);
}

.income-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.income-table-wrap table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
    margin: 0;
    background: #ffffff;
}

.income-table-wrap thead th {
    padding: 14px 16px;
    background: #f1f5f9;
    color: #475569;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e2e8f0;
}

.income-table-wrap tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
    font-size: 13px;
    color: #334155;
}

.income-table-wrap tbody tr {
    background: #ffffff;
    transition: background-color 0.15s;
}

.income-table-wrap tbody tr:hover {
    background: #f8fafc;
}

.income-id {
    color: #64748b;
    font-weight: 600;
}

.income-source {
    display: flex;
    align-items: center;
    gap: 10px;
}

.source-folder {
    font-size: 16px;
}

.income-source strong {
    display: block;
    color: #0f172a;
    font-size: 14px;
}

.income-source small {
    display: block;
    color: #64748b;
    font-size: 11px;
}

.income-category {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #dcfce7;
    color: #047857;
    font-size: 12px;
    font-weight: 600;
}

.income-type {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 600;
}

.income-amount {
    color: #059669;
    font-size: 14px;
    font-weight: 700;
}

.income-details {
    color: #475569;
}

.income-date {
    color: #64748b;
}

.income-actions {
    display: flex;
    align-items: center;
    gap: 14px;
}

.income-edit {
    color: #0284c7 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
}

.income-delete {
    color: #dc2626 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
}

.income-no-search {
    display: none;
    padding: 40px;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #64748b;
    background: #ffffff;
    text-align: center;
}

.income-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
    background: #ffffff;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e2e8f0;
}

.empty-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    border-radius: 50%;
    background: #ecfdf5;
    font-size: 32px;
}

.income-chart-card {
    margin-top: 24px;
    padding: 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.chart-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.chart-heading h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.chart-heading p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #64748b;
}

.year-badge {
    padding: 4px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 12px;
    color: #047857;
    font-weight: 600;
}

.chart-area {
    width: 100%;
    overflow-x: auto;
}

.chart-area canvas {
    display: block;
    width: 100%;
    max-width: 100%;
}

@media (max-width: 1024px) {
    .income-summary-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .income-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .income-summary-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="income-page">

    <div class="income-page-header">
        <div class="income-title-area">
            <div class="income-main-icon">📈</div>
            <div>
                <h1>Financial Dashboard: <span>Income Overview</span></h1>
                <p>Track, analyze, and manage your income.</p>
            </div>
        </div>
        <a href="manage_income.php" class="income-add-button">
            <span>+</span> Add New Income
        </a>
    </div>

    <div class="income-summary-row">
        <div class="income-summary-card">
            <div class="summary-icon icon-green">💸</div>
            <div>
                <small>Total Income</small>
                <strong>Rs. <?php echo formatIncomeAmount($totalIncome); ?></strong>
            </div>
        </div>

        <div class="income-summary-card">
            <div class="summary-icon icon-blue">📅</div>
            <div>
                <small>This Month</small>
                <strong>Rs. <?php echo formatIncomeAmount($monthIncome); ?></strong>
            </div>
        </div>

        <div class="income-summary-card">
            <div class="summary-icon icon-purple">🗓️</div>
            <div>
                <small>This Year</small>
                <strong>Rs. <?php echo formatIncomeAmount($yearIncome); ?></strong>
            </div>
        </div>

        <div class="income-summary-card">
            <div class="summary-icon icon-orange">📋</div>
            <div>
                <small>Income Records</small>
                <strong id="incomeCount" class="record-count">0</strong>
            </div>
        </div>
    </div>

    <div class="income-list-header">
        <div>
            <h2>💵 Income List</h2>
            <p>All your income records</p>
        </div>
        <div class="records-badge">
            Records <span id="recordsBadgeCount">0</span>
        </div>
    </div>

    <div class="income-search-area">
        <div class="income-search-box">
            <span>🔍</span>
            <input
                type="text"
                id="incomeSearch"
                placeholder="Search item, category, amount, details..."
                autocomplete="off"
            >
        </div>
    </div>

    <?php if ($res && mysqli_num_rows($res) > 0) { ?>
        <div class="income-table-card">
            <div class="income-table-wrap">
                <table id="incomeTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Details</th>
                            <th>Income Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res)) { ?>
                        <tr>
                            <td>
                                <span class="income-id"><?php echo $row['id']; ?></span>
                            </td>

                            <td>
                                <div class="income-source">
                                    <span class="source-folder">💵</span>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['source']); ?></strong>
                                        <small>Income <?php echo $row['id']; ?></small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="income-category">
                                    <?php echo !empty($row['income_category']) ? htmlspecialchars($row['income_category']) : 'Other'; ?>
                                </span>
                            </td>

                            <td>
                                <span class="income-type">Other</span>
                            </td>

                            <td>
                                <strong class="income-amount">
                                    + Rs. <?php echo formatIncomeAmount($row['amount']); ?>
                                </strong>
                            </td>

                            <td>
                                <span class="income-details">
                                    <?php echo !empty($row['details']) ? htmlspecialchars($row['details']) : 'No details'; ?>
                                </span>
                            </td>

                            <td>
                                <span class="income-date">
                                    📅 <?php echo date('Y-m-d', strtotime($row['income_date'])); ?>
                                </span>
                            </td>

                            <td>
                                <div class="income-actions">
                                    <a href="manage_income.php?id=<?php echo $row['id']; ?>" class="income-edit">
                                        ✏️ Edit
                                    </a>
                                    <a href="javascript:void(0)" class="income-delete" onclick="delete_confir('<?php echo $row['id']; ?>','income.php')">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <div id="incomeNoSearch" class="income-no-search">
                    <span style="font-size: 24px;">🔍</span>
                    <strong>No matching income found</strong>
                    <span>Try another search term.</span>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="income-empty">
            <div class="empty-icon">💰</div>
            <h2>No Income Found</h2>
            <p>You have not added any income record yet.</p>
            <a href="manage_income.php" class="income-add-button">
                + Add Your First Income
            </a>
        </div>
    <?php } ?>

    <div class="income-chart-card">
        <div class="chart-heading">
            <div>
                <h2>📊 Monthly Income Trends</h2>
                <p>Monthly income received this year</p>
            </div>
            <div class="year-badge">
                📅 <?php echo $currentYear; ?>
            </div>
        </div>

        <div class="chart-area">
            <canvas id="incomeChart" width="1100" height="280"></canvas>
        </div>
    </div>

</div>

<script>
const incomeSearch = document.getElementById('incomeSearch');
const incomeTable = document.getElementById('incomeTable');
const incomeCount = document.getElementById('incomeCount');
const recordsBadgeCount = document.getElementById('recordsBadgeCount');
const incomeNoSearch = document.getElementById('incomeNoSearch');

function updateIncomeCount() {
    if (!incomeTable) return;

    const rows = incomeTable.querySelectorAll('tbody tr');
    let visible = 0;

    rows.forEach(function(row) {
        if (row.style.display !== 'none') {
            visible++;
        }
    });

    if (incomeCount) incomeCount.textContent = visible;
    if (recordsBadgeCount) recordsBadgeCount.textContent = visible;

    if (incomeNoSearch) {
        incomeNoSearch.style.display = visible === 0 ? 'flex' : 'none';
    }
}

if (incomeTable && incomeSearch) {
    updateIncomeCount();

    incomeSearch.addEventListener('input', function() {
        const value = this.value.toLowerCase().trim();
        const rows = incomeTable.querySelectorAll('tbody tr');

        rows.forEach(function(row) {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });

        updateIncomeCount();
    });
}

// Format chart amounts using the same K, M and B units
function formatChartValue(value) {
    value = Number(value) || 0;

    if (value >= 1000000000) {
        return (value / 1000000000).toFixed(2).replace(/\.?0+$/, '') + 'B';
    }

    if (value >= 1000000) {
        return (value / 1000000).toFixed(2).replace(/\.?0+$/, '') + 'M';
    }

    if (value >= 1000) {
        return (value / 1000).toFixed(2).replace(/\.?0+$/, '') + 'K';
    }

    return value.toFixed(2);
}

// Chart Script
const monthlyIncome = <?php echo json_encode(array_values($monthlyIncome)); ?>;
const canvas = document.getElementById('incomeChart');

if (canvas) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;

    ctx.clearRect(0, 0, width, height);

    const left = 65;
    const right = 30;
    const top = 30;
    const bottom = 45;

    const chartWidth = width - left - right;
    const chartHeight = height - top - bottom;

    const maxIncome = Math.max(...monthlyIncome, 100);

    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;

    for (let i = 0; i <= 4; i++) {
        const y = top + (chartHeight / 4) * i;

        ctx.beginPath();
        ctx.moveTo(left, y);
        ctx.lineTo(width - right, y);
        ctx.stroke();

        const value = maxIncome - (maxIncome / 4) * i;

        ctx.fillStyle = '#64748b';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText('Rs. ' + formatChartValue(value), left - 8, y + 4);
    }

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const gap = chartWidth / 12;

    monthlyIncome.forEach(function(value, index) {
        const barWidth = Math.min(32, gap * 0.5);
        const barHeight = (value / maxIncome) * chartHeight;

        const x = left + index * gap + (gap - barWidth) / 2;
        const y = top + chartHeight - barHeight;

        ctx.fillStyle = '#10b981';

        if (barHeight > 0) {
            ctx.beginPath();

            if (ctx.roundRect) {
                ctx.roundRect(x, y, barWidth, barHeight, [4, 4, 0, 0]);
            } else {
                ctx.rect(x, y, barWidth, barHeight);
            }

            ctx.fill();
        }

        if (value > 0) {
            ctx.fillStyle = '#0f766e';
            ctx.font = 'bold 10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Rs. ' + formatChartValue(value), x + barWidth / 2, y - 6);
        }

        ctx.fillStyle = '#64748b';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(months[index], x + barWidth / 2, top + chartHeight + 20);
    });

    ctx.strokeStyle = '#cbd5e1';
    ctx.beginPath();
    ctx.moveTo(left, top + chartHeight);
    ctx.lineTo(width - right, top + chartHeight);
    ctx.stroke();
}
</script>

<?php include('footer.php'); ?>
