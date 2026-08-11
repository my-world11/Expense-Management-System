<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

function getCleanNumber($data) {
    preg_match('/[0-9]+(\.[0-9]+)?/', strip_tags($data), $matches);
    return isset($matches[0]) ? (float)$matches[0] : 0;
}

// Data fetch
$incToday     = getCleanNumber(getDashboardIncome('today'));
$incYesterday = getCleanNumber(getDashboardIncome('yesterday'));
$incMonth     = getCleanNumber(getDashboardIncome('month'));
$incYear      = getCleanNumber(getDashboardIncome('year'));

$expToday     = getCleanNumber(getDashboardExpense('today'));
$expYesterday = getCleanNumber(getDashboardExpense('yesterday'));
$expMonth     = getCleanNumber(getDashboardExpense('month'));
$expYear      = getCleanNumber(getDashboardExpense('year'));

// Red Warning limit setup
$limitToday = 1000;
$limitMonth = 25000;

// =========================================================================
// database बाट user ले save गरेको category र amount तानेर JSON मा राख्ने helper functions
// (तपाईंको database function हरू अनुसार यसलाई मिलाउन सक्नुहुन्छ)
// =========================================================================
function getCategoriesJson($type, $period) {
    global $con; // यदि तपाईंको db connection variable अर्कै छ भने यहाँ मिलाउनुहोला
    
    // यहाँ database बाट user ले लेखेको category name र sum(amount) select गर्ने query हुन्छ।
    // जस्तै: SELECT category_name as name, SUM(amount) as amount FROM transactions WHERE type='$type' AND ... GROUP BY category_name
    
    // अहिलेलाई तपाईंले बुझ्न र टेस्ट गर्न सजिलो होोस् भनेर यसले database table बाट वा function बाट data तान्ने तरिका देखाएको छ:
    $categories = [];
    
    // यदि तपाईंको प्रोजेक्टमा पहिले नै category तान्ने function छ भने यहाँ राख्न सक्नुहुन्छ।
    // उदाहरणको लागि database query को format:
    /*
    $userId = $_SESSION['USER_ID'];
    $sql = "SELECT category AS name, SUM(amount) AS amount FROM my_table WHERE user_id='$userId' AND type='$type' ... GROUP BY category";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)){
        $categories[] = $row;
    }
    */
    
    // Fallback: यदि database मा query मिलेन भने खाली array जान्छ तर तल JavaScript मा dynamic handle हुन्छ
    return json_encode($categories);
}
?>

<style>
/* Base Styling */
body { background: #f8fafc; font-family: 'Inter', system-ui, sans-serif; color: #0f172a; }

.tab-container { margin: 20px 0; }
.tab-btn {
    padding: 12px 26px;
    font-weight: 800;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: #cbd5e1;
    color: #1e293b;
    font-size: 15px;
}
.tab-btn.active {
    background: #2563eb;
    color: white;
}

/* Cards Grid */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}

.card {
    background: white;
    padding: 22px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    border: 1px solid #e2e8f0;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #0f172a;
    font-weight: 800;
    text-transform: uppercase;
}

.card-icon { font-size: 24px; }

.card p { 
    font-size: 28px; 
    font-weight: 900; 
    margin: 10px 0 16px 0; 
    color: #0f172a; 
}

.card.red-alert {
    background: #ef4444;
    color: white;
    border: none;
}
.card.red-alert .card-title, .card.red-alert p { color: white; }

.btn {
    padding: 9px 16px;
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 800;
    width: 100%;
}
.card.red-alert .btn { background: white; color: #ef4444; border: none; }

/* Charts Layout */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    padding: 22px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    border: 1px solid #e2e8f0;
}

.chart-card h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 900;
    color: #0f172a;
}

/* Fullscreen Modal Styling */
.fullscreen-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(8px);
    z-index: 99999;
    justify-content: center;
    align-items: center;
}

.fs-modal-box {
    background: white;
    padding: 28px;
    border-radius: 20px;
    width: 90%;
    max-width: 480px;
    text-align: center;
    position: relative;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    max-height: 90vh;
    overflow-y: auto;
}

.fs-close-btn {
    position: absolute;
    top: 15px; right: 20px;
    font-size: 26px;
    font-weight: bold;
    cursor: pointer;
    color: #0f172a;
}

.pie-chart-container {
    margin: 15px auto;
    width: 200px;
    height: 200px;
}

.source-list {
    margin-top: 15px;
    text-align: left;
    border-top: 1px solid #e2e8f0;
    padding-top: 15px;
}

.source-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    font-size: 14px;
    border-bottom: 1px dashed #f1f5f9;
}

.source-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    color: #0f172a;
}

.source-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.source-val {
    font-weight: 900;
    color: #0f172a;
}

@media(max-width: 850px) {
    .charts-grid { grid-template-columns: 1fr; }
}
</style>

<h2>💳 Dashboard</h2>

<!-- Switch Tabs -->
<div class="tab-container">
    <button class="tab-btn active" id="incBtn" onclick="showTab('income')">💰 Income</button>
    <button class="tab-btn" id="expBtn" onclick="showTab('expense')">💸 Expense</button>
</div>

<!-- Income Cards Grid -->
<div id="incomeArea" class="card-grid">
    <div class="card">
        <div class="card-title"><span class="card-icon">☀️</span> Today Income</div>
        <p>Rs. <?php echo number_format($incToday); ?></p>
        <button class="btn" onclick="openFullModal('Today Income', <?php echo $incToday; ?>, 'Income', <?php echo getCategoriesJson('Income', 'today'); ?>)">View Details</button>
    </div>
    <div class="card">
        <div class="card-title"><span class="card-icon">⏪</span> Yesterday Income</div>
        <p>Rs. <?php echo number_format($incYesterday); ?></p>
        <button class="btn" onclick="openFullModal('Yesterday Income', <?php echo $incYesterday; ?>, 'Income', <?php echo getCategoriesJson('Income', 'yesterday'); ?>)">View Details</button>
    </div>
    <div class="card">
        <div class="card-title"><span class="card-icon">🗓️</span> This Month Income</div>
        <p>Rs. <?php echo number_format($incMonth); ?></p>
        <button class="btn" onclick="openFullModal('This Month Income', <?php echo $incMonth; ?>, 'Income', <?php echo getCategoriesJson('Income', 'month'); ?>)">View Details</button>
    </div>
    <div class="card">
        <div class="card-title"><span class="card-icon">🏆</span> This Year Income</div>
        <p>Rs. <?php echo number_format($incYear); ?></p>
        <button class="btn" onclick="openFullModal('This Year Income', <?php echo $incYear; ?>, 'Income', <?php echo getCategoriesJson('Income', 'year'); ?>)">View Details</button>
    </div>
</div>

<!-- Expense Cards Grid -->
<div id="expenseArea" class="card-grid" style="display:none;">
    <div class="card <?php echo ($expToday > $limitToday) ? 'red-alert' : ''; ?>">
        <div class="card-title"><span class="card-icon">🚨</span> Today Expense <?php if($expToday > $limitToday) echo '⚠️'; ?></div>
        <p>Rs. <?php echo number_format($expToday); ?></p>
        <button class="btn" onclick="openFullModal('Today Expense', <?php echo $expToday; ?>, 'Expense', <?php echo getCategoriesJson('Expense', 'today'); ?>)">View Details</button>
    </div>

    <div class="card">
        <div class="card-title"><span class="card-icon">⏳</span> Yesterday Expense</div>
        <p>Rs. <?php echo number_format($expYesterday); ?></p>
        <button class="btn" onclick="openFullModal('Yesterday Expense', <?php echo $expYesterday; ?>, 'Expense', <?php echo getCategoriesJson('Expense', 'yesterday'); ?>)">View Details</button>
    </div>

    <div class="card <?php echo ($expMonth > $limitMonth) ? 'red-alert' : ''; ?>">
        <div class="card-title"><span class="card-icon">📉</span> This Month Expense <?php if($expMonth > $limitMonth) echo '⚠️'; ?></div>
        <p>Rs. <?php echo number_format($expMonth); ?></p>
        <button class="btn" onclick="openFullModal('This Month Expense', <?php echo $expMonth; ?>, 'Expense', <?php echo getCategoriesJson('Expense', 'month'); ?>)">View Details</button>
    </div>

    <div class="card">
        <div class="card-title"><span class="card-icon">📅</span> This Year Expense</div>
        <p>Rs. <?php echo number_format($expYear); ?></p>
        <button class="btn" onclick="openFullModal('This Year Expense', <?php echo $expYear; ?>, 'Expense', <?php echo getCategoriesJson('Expense', 'year'); ?>)">View Details</button>
    </div>
</div>

<!-- Visual Charts Section -->
<div class="charts-grid">
    <div class="chart-card">
        <h4>📊 Overall Comparison</h4>
        <canvas id="barChart" width="400" height="230"></canvas>
    </div>
    <div class="chart-card">
        <h4>📈 Expense Trend Curve</h4>
        <canvas id="lineChart" width="400" height="230"></canvas>
    </div>
</div>

<!-- Full Screen Modal Popup -->
<div id="fsModal" class="fullscreen-modal">
    <div class="fs-modal-box">
        <span class="fs-close-btn" onclick="closeFullModal()">&times;</span>
        <h3 id="popTitle" style="margin:0; font-weight:900; color:#0f172a; font-size: 20px;">Details Summary</h3>
        
        <h2 id="popAmount" style="color:#2563eb; font-weight:900; margin:10px 0 5px 0;">Rs. 0</h2>
        <div id="popStatus"></div>

        <div class="pie-chart-container">
            <canvas id="pieCanvas" width="200" height="200"></canvas>
        </div>

        <div class="source-list" id="sourceListArea"></div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
const incValues = {
    today: <?php echo $incToday; ?>,
    yesterday: <?php echo $incYesterday; ?>,
    month: <?php echo $incMonth; ?>,
    year: <?php echo $incYear; ?>
};

const expValues = {
    today: <?php echo $expToday; ?>,
    yesterday: <?php echo $expYesterday; ?>,
    month: <?php echo $expMonth; ?>,
    year: <?php echo $expYear; ?>
};

function showTab(type) {
    if(type === 'income') {
        document.getElementById('incomeArea').style.display = 'grid';
        document.getElementById('expenseArea').style.display = 'none';
        document.getElementById('incBtn').classList.add('active');
        document.getElementById('expBtn').classList.remove('active');
    } else {
        document.getElementById('incomeArea').style.display = 'none';
        document.getElementById('expenseArea').style.display = 'grid';
        document.getElementById('expBtn').classList.add('active');
        document.getElementById('incBtn').classList.remove('active');
    }
}

function drawBarChart() {
    const canvas = document.getElementById('barChart');
    if(!canvas) return;
    const ctx = canvas.getContext('2d');
    
    const data = [<?php echo "$incMonth, $expMonth, $incYear, $expYear"; ?>];
    const fullLabels = ["Monthly Inc", "Monthly Exp", "Yearly Inc", "Yearly Exp"];
    const colors = ["#2563eb", "#ef4444", "#3b82f6", "#f87171"];
    const maxVal = Math.max(...data, 100);

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.strokeStyle = "#e2e8f0";
    ctx.lineWidth = 1;
    for(let y = 30; y <= 170; y += 35) {
        ctx.beginPath();
        ctx.moveTo(10, y);
        ctx.lineTo(390, y);
        ctx.stroke();
    }

    data.forEach((val, i) => {
        const barHeight = (val / maxVal) * 120;
        const x = 20 + i * 92;
        const y = 170 - barHeight;

        ctx.fillStyle = colors[i];
        ctx.beginPath();
        ctx.roundRect(x, y, 55, barHeight, [6, 6, 0, 0]);
        ctx.fill();

        ctx.fillStyle = "#0f172a";
        ctx.font = "bold 12px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("Rs." + (val >= 1000 ? (val/1000).toFixed(1) + "k" : val), x + 27, y - 8);

        ctx.fillStyle = "#0f172a";
        ctx.font = "900 12px sans-serif";
        ctx.fillText(fullLabels[i], x + 27, 195);
    });
}

function drawLineChart() {
    const canvas = document.getElementById('lineChart');
    if(!canvas) return;
    const ctx = canvas.getContext('2d');

    const points = [expValues.today, expValues.yesterday, expValues.month];
    const labels = ["Today Exp", "Yesterday Exp", "Month Total"];
    const maxVal = Math.max(...points, 100);

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.strokeStyle = "#e2e8f0";
    ctx.lineWidth = 1;
    for(let y = 30; y <= 170; y += 35) {
        ctx.beginPath();
        ctx.moveTo(20, y);
        ctx.lineTo(380, y);
        ctx.stroke();
    }

    const coords = points.map((val, i) => ({
        x: 60 + i * 135,
        y: 160 - (val / maxVal) * 110,
        val: val
    }));

    const gradient = ctx.createLinearGradient(0, 0, 0, 180);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    ctx.beginPath();
    ctx.moveTo(coords[0].x, 170);
    coords.forEach(p => ctx.lineTo(p.x, p.y));
    ctx.lineTo(coords[coords.length - 1].x, 170);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();

    ctx.beginPath();
    ctx.strokeStyle = "#2563eb";
    ctx.lineWidth = 3;
    coords.forEach((p, i) => {
        if(i === 0) ctx.moveTo(p.x, p.y);
        else ctx.lineTo(p.x, p.y);
    });
    ctx.stroke();

    coords.forEach((p, i) => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, 6, 0, 2 * Math.PI);
        ctx.fillStyle = "#ffffff";
        ctx.fill();
        ctx.strokeStyle = "#2563eb";
        ctx.lineWidth = 3;
        ctx.stroke();

        ctx.fillStyle = "#0f172a";
        ctx.font = "900 12px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("Rs." + p.val, p.x, p.y - 12);

        ctx.fillStyle = "#0f172a";
        ctx.font = "900 12px sans-serif";
        ctx.fillText(labels[i], p.x, 195);
    });
}

// =========================================================================
// यहाँ पूर्ण रूपमा डाइनामिक रूपमा database बाट आएको exact category नाम देखाउने function
// =========================================================================
function openFullModal(title, totalVal, type, categoriesData) {
    document.getElementById('fsModal').style.display = 'flex';
    document.getElementById('popTitle').innerText = title;
    document.getElementById('popAmount').innerText = 'Rs. ' + Number(totalVal).toLocaleString();

    if(type === 'Expense' && totalVal > 1000) {
        document.getElementById('popStatus').innerHTML = '<span style="color:#ef4444; font-weight:800; font-size:13px;">⚠️ High Expense Warning!</span>';
    } else {
        document.getElementById('popStatus').innerHTML = '<span style="color:#10b981; font-weight:800; font-size:13px;">✅ Normal Range</span>';
    }

    const canvas = document.getElementById('pieCanvas');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const centerX = 100;
    const centerY = 100;

    ctx.beginPath();
    ctx.arc(centerX, centerY, 85, 0, 2 * Math.PI);
    ctx.arc(centerX, centerY, 52, 0, 2 * Math.PI, true);
    ctx.fillStyle = (type === 'Income') ? '#2563eb' : '#ef4444';
    ctx.fill();

    const listArea = document.getElementById('sourceListArea');
    listArea.innerHTML = '<div style="font-size:13px; font-weight:900; color:#0f172a; margin-bottom:10px;">YOUR SAVED CATEGORIES</div>';

    // यदि database बाट data आएको छ भने exact त्यही category (जस्तै xyz, pqr आदि) देखाउने
    if(categoriesData && categoriesData.length > 0) {
        const colorsList = ['#2563eb', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'];
        
        categoriesData.forEach((item, index) => {
            let dotColor = colorsList[index % colorsList.length];
            listArea.innerHTML += `
                <div class="source-item">
                    <div class="source-name">
                        <div class="source-dot" style="background:${dotColor};"></div>
                        <span>${item.name}</span>
                    </div>
                    <div class="source-val">Rs. ${Number(item.amount).toLocaleString()}</div>
                </div>
            `;
        });
    } else {
        // यदि कुनै डेटा फेला परेको छैन भने total रकम देखाइदिने वा generic नाम देखाउने
        let defaultName = (type === 'Income') ? 'Income Record' : 'Expense Record';
        listArea.innerHTML += `
            <div class="source-item">
                <div class="source-name">
                    <div class="source-dot" style="background:${(type === 'Income') ? '#2563eb' : '#ef4444'};"></div>
                    <span>${defaultName}</span>
                </div>
                <div class="source-val">Rs. ${Number(totalVal).toLocaleString()}</div>
            </div>
        `;
    }
}

function closeFullModal() {
    document.getElementById('fsModal').style.display = 'none';
}

drawBarChart();
drawLineChart();
</script>