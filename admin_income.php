
<?php
include('header.php');
checkUser();
adminArea();

function incomeEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* Format large amounts using K, M and B */
function formatIncomeAmount($amount) {
    $amount = (float)$amount;

    if ($amount >= 1000000000) {
        $formatted = number_format($amount / 1000000000, 1);
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return $formatted . 'B';
    }

    if ($amount >= 1000000) {
        $formatted = number_format($amount / 1000000, 1);
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return $formatted . 'M';
    }

    if ($amount >= 1000) {
        $formatted = number_format($amount / 1000, 1);
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return $formatted . 'K';
    }

    return number_format($amount, 2);
}

$today = date('Y-m-d');
$default_from = date('Y-m-01');

$from_date = isset($_GET['from']) ? trim($_GET['from']) : $default_from;
$to_date = isset($_GET['to']) ? trim($_GET['to']) : $today;
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

/* Available income categories */
$income_categories = [
    'Salary',
    'Freelance',
    'Business',
    'Rent Received',
    'Investment',
    'Gift',
    'Bonus',
    'Commission',
    'Refund',
    'Other'
];

/* Validate dates */
if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date) ||
    !checkdate(
        (int)substr($from_date, 5, 2),
        (int)substr($from_date, 8, 2),
        (int)substr($from_date, 0, 4)
    ) ||
    $from_date > $today
) {
    $from_date = $default_from;
}

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date) ||
    !checkdate(
        (int)substr($to_date, 5, 2),
        (int)substr($to_date, 8, 2),
        (int)substr($to_date, 0, 4)
    ) ||
    $to_date > $today
) {
    $to_date = $today;
}

if ($from_date > $to_date) {
    $temp = $from_date;
    $from_date = $to_date;
    $to_date = $temp;
}

/* Validate category */
if (!in_array($category_filter, $income_categories, true)) {
    $category_filter = '';
}

/* Delete income */
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {

    $delete_id = (int)$_GET['delete'];

    $delete_stmt = mysqli_prepare(
        $con,
        "DELETE FROM income WHERE id = ?"
    );

    if ($delete_stmt) {
        mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
        mysqli_stmt_execute($delete_stmt);
        mysqli_stmt_close($delete_stmt);
    }

    header("Location: admin_income.php?" . http_build_query([
        'from' => $from_date,
        'to' => $to_date,
        'user' => $user_filter,
        'category' => $category_filter
    ]));
    exit;
}

/* User list */
$user_result = mysqli_query(
    $con,
    "SELECT id, username
     FROM users
     WHERE role = 'User'
     ORDER BY username ASC"
);

/* Build shared filters for income list and total */
$where = " WHERE income.income_date BETWEEN ? AND ?";
$params = [$from_date, $to_date];
$types = "ss";

/* Filter by user */
if ($user_filter > 0) {
    $where .= " AND income.added_by = ?";
    $params[] = $user_filter;
    $types .= "i";
}

/* Filter by income category */
if ($category_filter !== '') {
    $where .= " AND income.income_category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

/* Income list */
$sql = "
    SELECT
        income.id,
        income.source,
        income.income_category,
        income.amount,
        income.details,
        income.income_date,
        income.added_by,
        users.username
    FROM income
    LEFT JOIN users ON users.id = income.added_by
    $where
    ORDER BY income.id DESC
";

$stmt = mysqli_prepare($con, $sql);
$income_result = false;

if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $income_result = mysqli_stmt_get_result($stmt);
}

/* Total income using the same filters */
$total_sql = "
    SELECT COALESCE(SUM(income.amount), 0) AS total
    FROM income
    $where
";

$total_stmt = mysqli_prepare($con, $total_sql);
$total_income = 0;

if ($total_stmt) {
    mysqli_stmt_bind_param($total_stmt, $types, ...$params);
    mysqli_stmt_execute($total_stmt);

    $total_result = mysqli_stmt_get_result($total_stmt);
    $total_row = mysqli_fetch_assoc($total_result);

    $total_income = (float)($total_row['total'] ?? 0);

    mysqli_stmt_close($total_stmt);
}
?>

<link rel="stylesheet" href="admin_income.css?v=7">

<div class="admin-income-page">

    <?php include('user_header.php'); ?>

    <main class="admin-income-main">

        <!-- PAGE HEADING -->
        <div class="admin-income-heading">
            <div>
                <h1>Income Management</h1>
                <p>View and manage all income records</p>
            </div>
        </div>

        <!-- FILTER SECTION -->
        <div class="income-filter-box">

            <div class="filter-heading">
                <h2>Filter Income</h2>
                <p>Choose date, category and user to filter income records.</p>
            </div>

            <form method="GET" action="admin_income.php">

                <div class="filter-field">
                    <label for="from">From Date</label>

                    <div class="date-input">
                        <input
                            type="date"
                            id="from"
                            name="from"
                            max="<?php echo incomeEscape($today); ?>"
                            value="<?php echo incomeEscape($from_date); ?>"
                        >
                    </div>
                </div>

                <div class="filter-field">
                    <label for="to">To Date</label>

                    <div class="date-input">
                        <input
                            type="date"
                            id="to"
                            name="to"
                            min="<?php echo incomeEscape($from_date); ?>"
                            max="<?php echo incomeEscape($today); ?>"
                            value="<?php echo incomeEscape($to_date); ?>"
                        >
                    </div>
                </div>

                <div class="filter-field category-field">
                    <label for="category">Income Category</label>

                    <select name="category" id="category">

                        <option value="">All Categories</option>

                        <?php foreach ($income_categories as $category): ?>

                            <option
                                value="<?php echo incomeEscape($category); ?>"
                                <?php echo ($category_filter === $category) ? 'selected' : ''; ?>
                            >
                                <?php echo incomeEscape($category); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="filter-field category-field">
                    <label for="user">User</label>

                    <select name="user" id="user">

                        <option value="0">All Users</option>

                        <?php if ($user_result): ?>

                            <?php while ($user = mysqli_fetch_assoc($user_result)): ?>

                                <option
                                    value="<?php echo (int)$user['id']; ?>"
                                    <?php echo ($user_filter === (int)$user['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo incomeEscape($user['username']); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>
                </div>

                <button type="submit" class="search-btn">
                    Search
                </button>

                <a href="admin_income.php" class="reset-filter-btn">
                    Reset
                </a>

            </form>
        </div>

        <!-- TOTAL INCOME -->
        <div class="income-total-box">

            <div class="total-info">
                <span>Total Income</span>
                <p>Overall income based on selected filters</p>
            </div>

            <strong>
                Rs. <?php echo formatIncomeAmount($total_income); ?>
            </strong>

        </div>

        <!-- INCOME TABLE -->
        <div class="income-table-box">

            <div class="table-heading">
                <div>
                    <h2>Income Records</h2>
                    <p>All recorded income is displayed below.</p>
                </div>
            </div>

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Source</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($income_result && mysqli_num_rows($income_result) > 0): ?>

                    <?php while ($income = mysqli_fetch_assoc($income_result)): ?>

                        <?php
                        $income_id = (int)$income['id'];
                        $income_source = (string)($income['source'] ?? 'N/A');
                        $income_category = (string)($income['income_category'] ?? 'Other');
                        $income_amount = formatIncomeAmount($income['amount']);

                        $income_date = !empty($income['income_date'])
                            ? date('Y-m-d', strtotime($income['income_date']))
                            : '-';

                        $income_details = (string)($income['details'] ?? '');
                        $username = (string)($income['username'] ?? 'Unknown');
                        $added_by = (int)($income['added_by'] ?? 0);
                        ?>

                        <tr>

                            <td>
                                <?php echo $income_id; ?>
                            </td>

                            <td>
                                <?php echo incomeEscape($income_source); ?>
                            </td>

                            <td>
                                <?php echo incomeEscape($income_category); ?>
                            </td>

                            <td class="income-amount">
                                Rs. <?php echo $income_amount; ?>
                            </td>

                            <td>
                                <?php echo incomeEscape($income_date); ?>
                            </td>

                            <td>

                                <?php if ($added_by > 0 && $username !== 'Unknown'): ?>

                                    <a
                                        class="income-user-link"
                                        href="admin_income.php?<?php
                                            echo incomeEscape(http_build_query([
                                                'from' => $from_date,
                                                'to' => $to_date,
                                                'user' => $added_by,
                                                'category' => $category_filter
                                            ]));
                                        ?>"
                                        title="View income from this user"
                                    >
                                        <?php echo incomeEscape($username); ?>
                                    </a>

                                <?php else: ?>

                                    <span class="unknown-user">
                                        <?php echo incomeEscape($username); ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td class="income-actions">

                                <button
                                    type="button"
                                    class="view-btn"
                                    onclick='showIncome(
                                        <?php echo json_encode((string)$income_id); ?>,
                                        <?php echo json_encode($income_source); ?>,
                                        <?php echo json_encode($income_category); ?>,
                                        <?php echo json_encode($income_amount); ?>,
                                        <?php echo json_encode($income_date); ?>,
                                        <?php echo json_encode($username); ?>,
                                        <?php echo json_encode($income_details); ?>
                                    )'
                                >
                                    View
                                </button>

                                <a
                                    href="admin_income.php?<?php
                                        echo incomeEscape(http_build_query([
                                            'from' => $from_date,
                                            'to' => $to_date,
                                            'user' => $user_filter,
                                            'category' => $category_filter,
                                            'delete' => $income_id
                                        ]));
                                    ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this income?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="no-income">
                            No income records found for the selected filters.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>
</div>

<!-- INCOME DETAILS MODAL -->
<div class="income-modal" id="incomeModal">

    <div class="income-modal-content">

        <div class="modal-header">
            <h2>Income Details</h2>

            <button
                type="button"
                onclick="closeIncome()"
                aria-label="Close"
            >
                &times;
            </button>
        </div>

        <div class="modal-body">

            <div class="detail-row">
                <span>ID</span>
                <strong id="view_id"></strong>
            </div>

            <div class="detail-row">
                <span>Source</span>
                <strong id="view_source"></strong>
            </div>

            <div class="detail-row">
                <span>Category</span>
                <strong id="view_category"></strong>
            </div>

            <div class="detail-row">
                <span>Amount</span>
                <strong id="view_amount"></strong>
            </div>

            <div class="detail-row">
                <span>Date</span>
                <strong id="view_date"></strong>
            </div>

            <div class="detail-row">
                <span>Added By</span>
                <strong id="view_user"></strong>
            </div>

            <div class="detail-row">
                <span>Details</span>
                <strong id="view_details"></strong>
            </div>

        </div>

    </div>
</div>

<script>
function showIncome(id, source, category, amount, date, user, details) {

    document.getElementById('view_id').textContent = id;
    document.getElementById('view_source').textContent = source;
    document.getElementById('view_category').textContent = category;
    document.getElementById('view_amount').textContent = 'Rs. ' + amount;
    document.getElementById('view_date').textContent = date;
    document.getElementById('view_user').textContent = user;
    document.getElementById('view_details').textContent = details || '-';

    document.getElementById('incomeModal').classList.add('show');
}

function closeIncome() {
    document.getElementById('incomeModal').classList.remove('show');
}

window.addEventListener('click', function(event) {

    const modal = document.getElementById('incomeModal');

    if (event.target === modal) {
        closeIncome();
    }
});

document.addEventListener('keydown', function(event) {

    if (event.key === 'Escape') {
        closeIncome();
    }
});
</script>

<?php
if ($stmt) {
    mysqli_stmt_close($stmt);
}
?>

<?php include('footer.php'); ?>
