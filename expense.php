
<?php

include('header.php');
checkUser();
userArea();
include('user_header.php');

// Format amount using K, M and B
function formatExpenseAmount($amount) {
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

// Delete Logic
if (
    isset($_GET['type']) &&
    $_GET['type'] == 'delete' &&
    isset($_GET['id']) &&
    $_GET['id'] > 0
) {

    $id = get_safe_value($_GET['id']);

    mysqli_query(
        $con,
        "DELETE FROM expense
         WHERE id=$id
         AND added_by='" . $_SESSION['UID'] . "'"
    );

    redirect('expense.php');
}

// Fetch Expense Records
$res = mysqli_query(
    $con,
    "SELECT expense.*, category.name
     FROM expense, category
     WHERE expense.category_id = category.id
     AND expense.added_by = '" . $_SESSION['UID'] . "'
     ORDER BY expense.id ASC"
);

// Summary Calculations
$totalExpense = 0;
$totalRes = mysqli_query(
    $con,
    "SELECT SUM(price) AS total
     FROM expense
     WHERE added_by='" . $_SESSION['UID'] . "'"
);

if ($totalRes) {
    $totalRow = mysqli_fetch_assoc($totalRes);
    $totalExpense = (float)$totalRow['total'];
}

// Monthly Expense
$monthExpenseRes = mysqli_query(
    $con,
    "SELECT SUM(price) AS total
     FROM expense
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND MONTH(expense_date)=MONTH(CURDATE())
     AND YEAR(expense_date)=YEAR(CURDATE())"
);

$monthExpenseRow = mysqli_fetch_assoc($monthExpenseRes);
$monthExpense = (float)$monthExpenseRow['total'];

// Needs Spending
$needRes = mysqli_query(
    $con,
    "SELECT SUM(price) AS total
     FROM expense
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND expense_type='Need'"
);

$needRow = mysqli_fetch_assoc($needRes);
$needExpense = (float)$needRow['total'];

// Wants Spending
$wantRes = mysqli_query(
    $con,
    "SELECT SUM(price) AS total
     FROM expense
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND expense_type='Want'"
);

$wantRow = mysqli_fetch_assoc($wantRes);
$wantExpense = (float)$wantRow['total'];

// Chart Data Calculation (Current Year)
$monthlyExpense = array_fill(1, 12, 0);
$currentYear = date('Y');

$chartRes = mysqli_query(
    $con,
    "SELECT
        MONTH(expense_date) AS month_no,
        SUM(price) AS total
     FROM expense
     WHERE added_by='" . $_SESSION['UID'] . "'
     AND YEAR(expense_date)='$currentYear'
     GROUP BY MONTH(expense_date)
     ORDER BY MONTH(expense_date)"
);

if ($chartRes) {
    while ($chartRow = mysqli_fetch_assoc($chartRes)) {
        $monthNo = (int)$chartRow['month_no'];
        $monthlyExpense[$monthNo] = (float)$chartRow['total'];
    }
}

?>

<style>
.expense-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    box-sizing: border-box;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: #0f172a;
    background-color: #f8fafc;
}

.expense-page-header {
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

.expense-title-area {
    display: flex;
    align-items: center;
    gap: 14px;
}

.expense-main-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #fef2f2;
    font-size: 24px;
}

.expense-title-area h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
}

.expense-title-area h1 span {
    color: #dc2626;
}

.expense-title-area p {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 13px;
}

.expense-add-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    background: #dc2626;
    color: #ffffff !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
}

.expense-add-button:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

.expense-summary-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.expense-summary-card {
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

.icon-red { background: #fee2e2; }
.icon-blue { background: #e0f2fe; }
.icon-green { background: #dcfce7; }
.icon-orange { background: #ffedd5; }

.expense-summary-card small {
    display: block;
    margin-bottom: 2px;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
}

.expense-summary-card strong {
    display: block;
    color: #dc2626;
    font-size: 18px;
    font-weight: 700;
}

.expense-list-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.expense-list-header h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
}

.expense-list-header p {
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
    color: #dc2626;
    font-weight: 700;
}

.expense-search-area {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}

.expense-search-box {
    flex: 1;
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

.expense-search-box input {
    width: 100%;
    height: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    font-size: 14px;
    color: #0f172a;
}

.expense-filter-select {
    height: 42px;
    padding: 0 16px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    color: #0f172a;
    outline: none;
    cursor: pointer;
}

.expense-table-card {
    width: 100%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
}

.expense-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.expense-table-wrap table {
    width: 100%;
    min-width: 950px;
    border-collapse: collapse;
    margin: 0;
    background: #ffffff;
}

.expense-table-wrap thead th {
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

.expense-table-wrap tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
    font-size: 13px;
    color: #334155;
}

.expense-table-wrap tbody tr {
    background: #ffffff;
    transition: background-color 0.15s;
}

.expense-table-wrap tbody tr:hover {
    background: #f8fafc;
}

.expense-id {
    color: #475569;
    font-weight: 600;
}

.category-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #ede9fe;
    color: #6d28d9;
    font-size: 12px;
    font-weight: 600;
}

.type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.type-badge.need {
    background: #dcfce7;
    color: #15803d;
}

.type-badge.want {
    background: #ffedd5;
    color: #c2410c;
}

.item-name {
    color: #0f172a;
    font-weight: 600;
}

.expense-amount {
    color: #dc2626;
    font-size: 14px;
    font-weight: 700;
}

.expense-details {
    color: #64748b;
}

.expense-date {
    color: #64748b;
}

.expense-actions {
    display: flex;
    align-items: center;
    gap: 14px;
}

.expense-edit {
    color: #0284c7 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
}

.expense-delete {
    color: #dc2626 !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 600;
}

.expense-no-search {
    display: none;
    padding: 40px;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #64748b;
    background: #ffffff;
    text-align: center;
}

.expense-empty {
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
    background: #fef2f2;
    font-size: 32px;
}

.expense-chart-card {
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
    color: #dc2626;
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
    .expense-summary-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .expense-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .expense-summary-row {
        grid-template-columns: 1fr;
    }

    .expense-search-area {
        flex-direction: column;
    }
}
</style>

<div class="expense-page">

    <!-- Top Header -->
    <div class="expense-page-header">
        <div class="expense-title-area">
            <div class="expense-main-icon">📉</div>
            <div>
                <h1>Financial Dashboard: <span>Expense Overview</span></h1>
                <p>Track, analyze, and control your daily spending.</p>
            </div>
        </div>
        <a href="manage_expense.php" class="expense-add-button">
            <span>+</span> Add New Expense
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="expense-summary-row">
        <div class="expense-summary-card">
            <div class="summary-icon icon-red">💸</div>
            <div>
                <small>Total Expense</small>
                <strong>Rs. <?php echo formatExpenseAmount($totalExpense); ?></strong>
            </div>
        </div>

        <div class="expense-summary-card">
            <div class="summary-icon icon-blue">📅</div>
            <div>
                <small>This Month</small>
                <strong>Rs. <?php echo formatExpenseAmount($monthExpense); ?></strong>
            </div>
        </div>

        <div class="expense-summary-card">
            <div class="summary-icon icon-green">🟢</div>
            <div>
                <small>Needs Spending</small>
                <strong style="color: #059669;">
                    Rs. <?php echo formatExpenseAmount($needExpense); ?>
                </strong>
            </div>
        </div>

        <div class="expense-summary-card">
            <div class="summary-icon icon-orange">🟠</div>
            <div>
                <small>Wants Spending</small>
                <strong style="color: #d97706;">
                    Rs. <?php echo formatExpenseAmount($wantExpense); ?>
                </strong>
            </div>
        </div>
    </div>

    <!-- Section Title & Filter Badge -->
    <div class="expense-list-header">
        <div>
            <h2>🧾 Expense List</h2>
            <p>All your expense records</p>
        </div>
        <div class="records-badge">
            Records <span id="recordsBadgeCount">0</span>
        </div>
    </div>

    <!-- Live Search & Type Filter -->
    <div class="expense-search-area">
        <div class="expense-search-box">
            <span>🔍</span>
            <input
                type="text"
                id="searchInput"
                placeholder="Search category, item, amount, details..."
                autocomplete="off"
            >
        </div>

        <select id="typeFilter" class="expense-filter-select">
            <option value="">All Types</option>
            <option value="need">Need</option>
            <option value="want">Want</option>
        </select>
    </div>

    <!-- Table Section -->
    <?php if ($res && mysqli_num_rows($res) > 0) { ?>
        <div class="expense-table-card">
            <div class="expense-table-wrap">
                <table id="expenseTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Details</th>
                            <th>Expense Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res)) { ?>
                        <tr>
                            <td>
                                <span class="expense-id"><?php echo $row['id']; ?></span>
                            </td>

                            <td>
                                <span class="category-badge">
                                    📂 <?php echo htmlspecialchars($row['name']); ?>
                                </span>
                            </td>

                            <td>
                                <?php if (strtolower($row['expense_type']) == 'need') { ?>
                                    <span class="type-badge need">🟢 Need</span>
                                <?php } else { ?>
                                    <span class="type-badge want">🟠 Want</span>
                                <?php } ?>
                            </td>

                            <td>
                                <strong class="item-name"><?php echo htmlspecialchars($row['item']); ?></strong>
                            </td>

                            <td>
                                <strong class="expense-amount">
                                    - Rs. <?php echo formatExpenseAmount($row['price']); ?>
                                </strong>
                            </td>

                            <td>
                                <span class="expense-details">
                                    <?php echo !empty($row['details']) ? htmlspecialchars($row['details']) : 'No details'; ?>
                                </span>
                            </td>

                            <td>
                                <span class="expense-date">
                                    📅 <?php echo date('Y-m-d', strtotime($row['expense_date'])); ?>
                                </span>
                            </td>

                            <td>
                                <div class="expense-actions">
                                    <a href="manage_expense.php?id=<?php echo $row['id']; ?>" class="expense-edit">
                                        ✏️ Edit
                                    </a>
                                    <a href="javascript:void(0)" class="expense-delete" onclick="delete_confir('<?php echo $row['id']; ?>','expense.php')">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <div id="expenseNoSearch" class="expense-no-search">
                    <span style="font-size: 24px;">🔍</span>
                    <strong>No matching expense found</strong>
                    <span>Try adjusting your search query or type filter.</span>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="expense-empty">
            <div class="empty-icon">🧾</div>
            <h2>No Expense Found</h2>
            <p>You have not recorded any expenses yet.</p>
            <a href="manage_expense.php" class="expense-add-button">
                + Add Your First Expense
            </a>
        </div>
    <?php } ?>

    <!-- Monthly Trend Chart -->
    <div class="expense-chart-card">
        <div class="chart-heading">
            <div>
                <h2>📊 Monthly Expense Trends</h2>
                <p>Monthly spending recorded this year</p>
            </div>
            <div class="year-badge">
                📅 <?php echo $currentYear; ?>
            </div>
        </div>

        <div class="chart-area">
            <canvas id="expenseChart" width="1100" height="280"></canvas>
        </div>
    </div>

</div>

<script>
const searchInput = document.getElementById("searchInput");
const typeFilter = document.getElementById("typeFilter");
const expenseTable = document.getElementById("expenseTable");
const recordsBadgeCount = document.getElementById("recordsBadgeCount");
const expenseNoSearch = document.getElementById("expenseNoSearch");

function filterExpenses() {
    if (!expenseTable) return;

    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const typeValue = typeFilter ? typeFilter.value.toLowerCase().trim() : "";
    const rows = expenseTable.querySelectorAll("tbody tr");

    let visibleCount = 0;

    rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();
        const matchesSearch = text.includes(searchValue);

        let matchesType = true;

        if (typeValue !== "") {
            matchesType = text.includes(typeValue);
        }

        if (matchesSearch && matchesType) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });

    if (recordsBadgeCount) recordsBadgeCount.textContent = visibleCount;

    if (expenseNoSearch) {
        expenseNoSearch.style.display = visibleCount === 0 ? "flex" : "none";
    }
}

if (searchInput) searchInput.addEventListener("keyup", filterExpenses);
if (typeFilter) typeFilter.addEventListener("change", filterExpenses);

filterExpenses();

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
const monthlyExpense = <?php echo json_encode(array_values($monthlyExpense)); ?>;
const canvas = document.getElementById("expenseChart");

if (canvas) {
    const ctx = canvas.getContext("2d");
    const width = canvas.width;
    const height = canvas.height;

    ctx.clearRect(0, 0, width, height);

    const left = 65;
    const right = 30;
    const top = 30;
    const bottom = 45;

    const chartWidth = width - left - right;
    const chartHeight = height - top - bottom;

    const maxExpense = Math.max(...monthlyExpense, 100);

    ctx.strokeStyle = "#e2e8f0";
    ctx.lineWidth = 1;

    for (let i = 0; i <= 4; i++) {
        const y = top + (chartHeight / 4) * i;

        ctx.beginPath();
        ctx.moveTo(left, y);
        ctx.lineTo(width - right, y);
        ctx.stroke();

        const value = maxExpense - (maxExpense / 4) * i;

        ctx.fillStyle = "#64748b";
        ctx.font = "11px sans-serif";
        ctx.textAlign = "right";
        ctx.fillText("Rs. " + formatChartValue(value), left - 8, y + 4);
    }

    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const gap = chartWidth / 12;

    monthlyExpense.forEach(function(value, index) {
        const barWidth = Math.min(32, gap * 0.5);
        const barHeight = (value / maxExpense) * chartHeight;

        const x = left + index * gap + (gap - barWidth) / 2;
        const y = top + chartHeight - barHeight;

        ctx.fillStyle = "#ef4444";

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
            ctx.fillStyle = "#b91c1c";
            ctx.font = "bold 10px sans-serif";
            ctx.textAlign = "center";
            ctx.fillText("Rs. " + formatChartValue(value), x + barWidth / 2, y - 6);
        }

        ctx.fillStyle = "#64748b";
        ctx.font = "11px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText(months[index], x + barWidth / 2, top + chartHeight + 20);
    });

    ctx.strokeStyle = "#cbd5e1";
    ctx.beginPath();
    ctx.moveTo(left, top + chartHeight);
    ctx.lineTo(width - right, top + chartHeight);
    ctx.stroke();
}
</script>

<?php include('footer.php'); ?>
