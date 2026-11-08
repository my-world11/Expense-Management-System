
<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

$from = '';
$to = '';
$category_id = '';
$income_category = '';

if(isset($_GET['from'])){
    $from = get_safe_value($_GET['from']);
}

if(isset($_GET['to'])){
    $to = get_safe_value($_GET['to']);
}

if(isset($_GET['category_id'])){
    $category_id = get_safe_value($_GET['category_id']);
}

if(isset($_GET['income_category'])){
    $income_category = get_safe_value($_GET['income_category']);
}

$user_id = (int)$_SESSION['UID'];

// Format amounts using K, M and B
function formatReportAmount($amount){
    $amount = (float)$amount;

    if($amount >= 1000000000){
        return rtrim(rtrim(number_format($amount / 1000000000, 2, '.', ''), '0'), '.') . 'B';
    }

    if($amount >= 1000000){
        return rtrim(rtrim(number_format($amount / 1000000, 2, '.', ''), '0'), '.') . 'M';
    }

    if($amount >= 1000){
        return rtrim(rtrim(number_format($amount / 1000, 2, '.', ''), '0'), '.') . 'K';
    }

    return number_format($amount, 2);
}

// Get Admin Income Categories
$income_category_res = mysqli_query($con, "
    SELECT name
    FROM category
    WHERE type = 'Income'
    AND created_by = 'Admin'
    ORDER BY id ASC
");

// Get Admin Expense Categories
$expense_category_res = mysqli_query($con, "
    SELECT id, name
    FROM category
    WHERE type = 'Expense'
    AND created_by = 'Admin'
    ORDER BY id ASC
");

// Build date conditions
$date_sql_expense = '';
$date_sql_income = '';

if($from != '' && $to != ''){
    $date_sql_expense = " AND expense.expense_date BETWEEN '$from' AND '$to'";
    $date_sql_income = " AND income.income_date BETWEEN '$from' AND '$to'";
} elseif($from != ''){
    $date_sql_expense = " AND expense.expense_date >= '$from'";
    $date_sql_income = " AND income.income_date >= '$from'";
} elseif($to != ''){
    $date_sql_expense = " AND expense.expense_date <= '$to'";
    $date_sql_income = " AND income.income_date <= '$to'";
}

// Expense category condition
$category_sql = '';

if($category_id != '' && is_numeric($category_id)){
    $category_id = (int)$category_id;
    $category_sql = " AND expense.category_id='$category_id'";
}

// Income category condition
$income_category_sql = '';

if($income_category != ''){
    $income_category_safe = mysqli_real_escape_string($con, $income_category);
    $income_category_sql = " AND income.income_category='$income_category_safe'";
}

// Expense report query
$expense_res = mysqli_query($con, "
    SELECT
        expense.price,
        category.name,
        expense.item,
        expense.expense_date
    FROM expense
    JOIN category ON expense.category_id = category.id
    WHERE expense.added_by='$user_id'
    $date_sql_expense
    $category_sql
    ORDER BY expense.expense_date DESC
");

// Income report query
$income_res = mysqli_query($con, "
    SELECT
        income.source,
        income.income_category,
        income.amount,
        income.details,
        income.income_date
    FROM income
    WHERE income.added_by='$user_id'
    $date_sql_income
    $income_category_sql
    ORDER BY income.income_date DESC
");

// Store expense records
$expense_rows = [];
$total_expense = 0;

if($expense_res){
    while($row = mysqli_fetch_assoc($expense_res)){
        $expense_rows[] = $row;
        $total_expense += (float)$row['price'];
    }
}

// Store income records
$income_rows = [];
$total_income = 0;

if($income_res){
    while($row = mysqli_fetch_assoc($income_res)){
        $income_rows[] = $row;
        $total_income += (float)$row['amount'];
    }
}
?>

<style>
.reports-page-wrapper {
    width: 100%;
    padding: 24px;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8fafc;
}

.reports-top-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.title-with-icon {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-icon-box {
    width: 42px;
    height: 42px;
    background: #eff6ff;
    color: #2563eb;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.title-with-icon h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
}

.title-with-icon p {
    margin: 2px 0 0;
    font-size: 13px;
    color: #64748b;
}

.header-date-info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
}

.reports-filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.filter-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 14px;
}

.filter-grid-form {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}

.form-group-item {
    flex: 1;
    min-width: 170px;
}

.form-group-item label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.form-group-item input,
.select-dropdown-wrap select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    font-size: 13px;
    color: #334155;
    outline: none;
    box-sizing: border-box;
}

.form-group-item input:focus,
.select-dropdown-wrap select:focus {
    border-color: #2563eb;
}

.income-select-wrap select {
    border-color: #86efac;
}

.income-select-wrap select:focus {
    border-color: #16a34a;
}

.form-actions-btn {
    display: flex;
    gap: 10px;
}

.btn-search-action,
.btn-reset-action {
    height: 40px;
    padding: 0 22px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-search-action {
    background-color: #2563eb;
    color: #ffffff;
}

.btn-search-action:hover {
    background-color: #1d4ed8;
}

.btn-reset-action {
    background-color: #10b981;
    color: #ffffff;
}

.btn-reset-action:hover {
    background-color: #059669;
}

/* Report sections */
.report-section {
    margin-bottom: 28px;
}

.report-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.report-section-heading h3 {
    margin: 0;
    font-size: 18px;
    color: #1e293b;
}

.report-section-heading p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #64748b;
}

.report-total-badge {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
}

.expense-total-badge {
    background: #fee2e2;
    color: #dc2626;
}

.income-total-badge {
    background: #dcfce7;
    color: #15803d;
}

/* Tables */
.reports-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.custom-reports-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
}

.custom-reports-table thead th {
    background-color: #f0f7ff;
    color: #2563eb;
    font-size: 13px;
    font-weight: 700;
    padding: 14px 20px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.income-table thead th {
    background-color: #f0fdf4;
    color: #15803d;
}

.custom-reports-table tbody td {
    padding: 14px 20px;
    font-size: 13px;
    border-bottom: 1px solid #f1f5f9;
}

.item-title {
    color: #334155;
}

.date-text {
    color: #64748b;
}

.expense-price {
    color: #dc2626;
    font-weight: 700;
}

.income-price {
    color: #16a34a;
    font-weight: 700;
}

.text-right-align {
    text-align: right !important;
}

/* Category badges */
.category-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.pill-electronics {
    background-color: #f3e8ff;
    color: #7e22ce;
}

.pill-shopping {
    background-color: #e0f2fe;
    color: #0284c7;
}

.pill-food {
    background-color: #ffedd5;
    color: #c2410c;
}

.pill-entertainment {
    background-color: #ffe4e6;
    color: #be123c;
}

.pill-default {
    background-color: #f1f5f9;
    color: #475569;
}

/* Total rows */
.custom-reports-table tfoot tr {
    background-color: #f0f7ff;
}

.income-table tfoot tr {
    background-color: #f0fdf4;
}

.footer-total-label {
    padding: 14px 20px;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
}

.footer-total-box {
    padding: 14px 20px;
    background-color: #2563eb;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    text-align: right;
}

.income-table .footer-total-box {
    background-color: #16a34a;
}

/* Empty state */
.empty-data-wrapper {
    padding: 35px;
    text-align: center;
}

.empty-icon {
    font-size: 34px;
    margin-bottom: 10px;
    color: #cbd5e1;
}

.empty-data-wrapper h3 {
    margin: 5px 0;
    color: #334155;
}

.empty-data-wrapper p {
    color: #64748b;
    font-size: 13px;
}

@media(max-width: 900px) {
    .form-group-item {
        min-width: 200px;
    }
}

@media(max-width: 650px) {
    .reports-page-wrapper {
        padding: 14px;
    }

    .reports-top-header {
        align-items: flex-start;
        gap: 12px;
        flex-direction: column;
    }

    .reports-filter-card {
        padding: 16px;
    }

    .form-group-item {
        min-width: 100%;
    }

    .form-actions-btn {
        width: 100%;
    }

    .btn-search-action,
    .btn-reset-action {
        justify-content: center;
        flex: 1;
    }

    .report-section-heading {
        align-items: flex-start;
        gap: 10px;
        flex-direction: column;
    }
}
</style>

<div class="reports-page-wrapper">

    <!-- Page Header -->
    <div class="reports-top-header">
        <div class="title-with-icon">
            <div class="header-icon-box">▤</div>
            <div>
                <h2>Income & Expense Reports</h2>
                <p>View your income and expense records in one place.</p>
            </div>
        </div>

        <div class="header-date-info">
            <span>📅</span>
            <span><?php echo date('d M Y'); ?></span>
        </div>
    </div>

    <!-- Filters -->
    <div class="reports-filter-card">
        <div class="filter-card-title">Filter Reports</div>

        <form method="get" class="filter-grid-form">

            <div class="form-group-item">
                <label>From Date</label>
                <input
                    type="date"
                    name="from"
                    value="<?php echo htmlspecialchars($from); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    id="from_date"
                    onchange="set_to_date()"
                >
            </div>

            <div class="form-group-item">
                <label>To Date</label>
                <input
                    type="date"
                    name="to"
                    value="<?php echo htmlspecialchars($to); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                    id="to_date"
                >
            </div>

            <!-- Expense Category Filter -->
            <div class="form-group-item">
                <label>Expense Category</label>

                <div class="select-dropdown-wrap">
                    <select name="category_id">

                        <option value="">All Expense Categories</option>

                        <?php
                        if($expense_category_res){
                            while($expense_cat = mysqli_fetch_assoc($expense_category_res)){
                        ?>

                            <option
                                value="<?php echo (int)$expense_cat['id']; ?>"
                                <?php
                                if($category_id == $expense_cat['id']){
                                    echo 'selected';
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($expense_cat['name']); ?>
                            </option>

                        <?php
                            }
                        }
                        ?>

                    </select>
                </div>
            </div>

            <!-- Income Category Filter -->
            <div class="form-group-item">
                <label>Income Category</label>

                <div class="select-dropdown-wrap income-select-wrap">
                    <select name="income_category">

                        <option value="">All Income Categories</option>

                        <?php
                        if($income_category_res){
                            while($income_cat = mysqli_fetch_assoc($income_category_res)){
                        ?>

                            <option
                                value="<?php echo htmlspecialchars($income_cat['name']); ?>"
                                <?php
                                if($income_category == $income_cat['name']){
                                    echo 'selected';
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($income_cat['name']); ?>
                            </option>

                        <?php
                            }
                        }
                        ?>

                    </select>
                </div>
            </div>

            <div class="form-actions-btn">
                <button type="submit" class="btn-search-action">
                    Search Reports
                </button>

                <a href="reports.php" class="btn-reset-action">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- EXPENSE REPORT -->
    <div class="report-section">

        <div class="report-section-heading">
            <div>
                <h3>Expense Report</h3>
                <p>Your spending records and expense details.</p>
            </div>

            <div class="report-total-badge expense-total-badge">
                Total Expense: Rs. <?php echo formatReportAmount($total_expense); ?>
            </div>
        </div>

        <?php if(count($expense_rows) > 0){ ?>

            <div class="reports-table-card">
                <table class="custom-reports-table">

                    <thead>
                        <tr>
                            <th width="28%">Category</th>
                            <th width="32%">Item</th>
                            <th width="22%">Expense Date</th>
                            <th width="18%" class="text-right-align">Price</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach($expense_rows as $row){

                        $cat_lower = strtolower(trim($row['name']));
                        $pill_theme = 'pill-default';
                        $icon_text = '●';

                        if(strpos($cat_lower, 'elect') !== false){
                            $pill_theme = 'pill-electronics';
                            $icon_text = '⚡';
                        } elseif(strpos($cat_lower, 'shop') !== false){
                            $pill_theme = 'pill-shopping';
                            $icon_text = '🛒';
                        } elseif(strpos($cat_lower, 'food') !== false || strpos($cat_lower, 'din') !== false){
                            $pill_theme = 'pill-food';
                            $icon_text = '🍽';
                        } elseif(strpos($cat_lower, 'entertain') !== false){
                            $pill_theme = 'pill-entertainment';
                            $icon_text = '🎮';
                        }
                    ?>

                        <tr>
                            <td>
                                <span class="category-pill <?php echo $pill_theme; ?>">
                                    <?php echo $icon_text; ?>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </span>
                            </td>

                            <td class="item-title">
                                <?php echo htmlspecialchars($row['item']); ?>
                            </td>

                            <td class="date-text">
                                <?php echo htmlspecialchars($row['expense_date']); ?>
                            </td>

                            <td class="text-right-align expense-price">
                                Rs. <?php echo formatReportAmount($row['price']); ?>
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3" class="footer-total-label">
                                Total Expense
                            </td>

                            <td class="footer-total-box">
                                Rs. <?php echo formatReportAmount($total_expense); ?>
                            </td>
                        </tr>
                    </tfoot>

                </table>
            </div>

        <?php } else { ?>

            <div class="reports-table-card empty-data-wrapper">
                <div class="empty-icon">📂</div>
                <h3>No Expense Records Found</h3>
                <p>No expense data available for the selected filters.</p>
            </div>

        <?php } ?>

    </div>

    <!-- INCOME REPORT -->
    <div class="report-section">

        <div class="report-section-heading">
            <div>
                <h3>Income Report</h3>
                <p>Your income sources and earning details.</p>
            </div>

            <div class="report-total-badge income-total-badge">
                Total Income: Rs. <?php echo formatReportAmount($total_income); ?>
            </div>
        </div>

        <?php if(count($income_rows) > 0){ ?>

            <div class="reports-table-card">
                <table class="custom-reports-table income-table">

                    <thead>
                        <tr>
                            <th width="25%">Income Category</th>
                            <th width="25%">Income Source</th>
                            <th width="25%">Details</th>
                            <th width="15%">Income Date</th>
                            <th width="18%" class="text-right-align">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach($income_rows as $row){ ?>

                        <tr>
                            <td>
                                <span class="category-pill pill-default">
                                    💰
                                    <?php
                                    echo !empty($row['income_category'])
                                        ? htmlspecialchars($row['income_category'])
                                        : '-';
                                    ?>
                                </span>
                            </td>

                            <td class="item-title">
                                <?php echo htmlspecialchars($row['source']); ?>
                            </td>

                            <td class="item-title">
                                <?php
                                echo !empty($row['details'])
                                    ? htmlspecialchars($row['details'])
                                    : '-';
                                ?>
                            </td>

                            <td class="date-text">
                                <?php echo htmlspecialchars($row['income_date']); ?>
                            </td>

                            <td class="text-right-align income-price">
                                Rs. <?php echo formatReportAmount($row['amount']); ?>
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="4" class="footer-total-label">
                                Total Income
                            </td>

                            <td class="footer-total-box">
                                Rs. <?php echo formatReportAmount($total_income); ?>
                            </td>
                        </tr>
                    </tfoot>

                </table>
            </div>

        <?php } else { ?>

            <div class="reports-table-card empty-data-wrapper">
                <div class="empty-icon">📂</div>
                <h3>No Income Records Found</h3>
                <p>No income data available for the selected filters.</p>
            </div>

        <?php } ?>

    </div>

</div>

<script>
function set_to_date(){

    let fromDate = document.getElementById('from_date');
    let toDate = document.getElementById('to_date');

    if(fromDate.value !== ''){
        toDate.min = fromDate.value;

        if(toDate.value !== '' && toDate.value < fromDate.value){
            toDate.value = fromDate.value;
        }
    } else {
        toDate.removeAttribute('min');
    }
}

set_to_date();
</script>

<?php include('footer.php'); ?>
