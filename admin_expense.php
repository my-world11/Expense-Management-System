
<?php
include('header.php');
checkUser();
adminArea();

function expenseEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* Format large amounts using K, M and B */
function formatExpenseAmount($amount) {
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

/* Delete expense */
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $delete_id = (int)$_GET['delete'];

    mysqli_query($con, "DELETE FROM expense WHERE id='$delete_id'");

    header("Location: admin_expense.php");
    exit;
}

/* Filter values */
$today = date('Y-m-d');
$default_from = date('Y-m-01');

$from_date = isset($_GET['from']) ? $_GET['from'] : $default_from;
$to_date = isset($_GET['to']) ? $_GET['to'] : $today;
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : 0;

/* Validate date values */
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)) {
    $from_date = $default_from;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
    $to_date = $today;
}

if ($from_date > $today) {
    $from_date = $today;
}

if ($to_date > $today) {
    $to_date = $today;
}

/* Category list */
$category_result = mysqli_query(
    $con,
    "SELECT id, name FROM category ORDER BY name ASC"
);

/* User list */
$user_result = mysqli_query(
    $con,
    "SELECT id, username
     FROM users
     WHERE role = 'User'
     ORDER BY username ASC"
);

/* Build shared filters for list and total */
$where = " WHERE expense.expense_date BETWEEN ? AND ?";
$params = [$from_date, $to_date];
$types = "ss";

if ($category_filter > 0) {
    $where .= " AND expense.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if ($user_filter > 0) {
    $where .= " AND expense.added_by = ?";
    $params[] = $user_filter;
    $types .= "i";
}

/* Expense list */
$sql = "
    SELECT
        expense.id,
        expense.item,
        expense.price,
        expense.details,
        expense.expense_date,
        expense.added_by,
        category.name AS category_name,
        users.username
    FROM expense
    LEFT JOIN category ON category.id = expense.category_id
    LEFT JOIN users ON users.id = expense.added_by
    $where
    ORDER BY expense.id DESC
";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$expense_result = mysqli_stmt_get_result($stmt);

/* Total uses the same filters */
$total_sql = "
    SELECT COALESCE(SUM(expense.price), 0) AS total
    FROM expense
    $where
";

$total_stmt = mysqli_prepare($con, $total_sql);
mysqli_stmt_bind_param($total_stmt, $types, ...$params);
mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total_expense = $total_row['total'] ?? 0;
?>

<link rel="stylesheet" href="admin_expense.css?v=6">

<div class="admin-expense-page">

    <?php include('user_header.php'); ?>

    <main class="admin-expense-main">

        <div class="admin-expense-heading">
            <div>
                <h1>Expense Management</h1>
                <p>View and manage all expense records</p>
            </div>
        </div>

        <div class="expense-filter-box">

            <div class="filter-heading">
                <h2>Filter Expenses</h2>
                <p>Choose date, category and user to filter expense records.</p>
            </div>

            <form method="GET" action="admin_expense.php">

                <div class="filter-field">
                    <label for="from">From Date</label>
                    <div class="date-input">
                        <input
                            type="date"
                            id="from"
                            name="from"
                            max="<?php echo $today; ?>"
                            value="<?php echo expenseEscape($from_date); ?>"
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
                            min="<?php echo expenseEscape($from_date); ?>"
                            max="<?php echo $today; ?>"
                            value="<?php echo expenseEscape($to_date); ?>"
                        >
                    </div>
                </div>

                <div class="filter-field category-field">
                    <label for="category">Category</label>
                    <select name="category" id="category">
                        <option value="0">All Categories</option>

                        <?php if ($category_result): ?>
                            <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                                <option
                                    value="<?php echo (int)$category['id']; ?>"
                                    <?php echo $category_filter === (int)$category['id'] ? 'selected' : ''; ?>
                                >
                                    <?php echo expenseEscape($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
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
                                    <?php echo $user_filter === (int)$user['id'] ? 'selected' : ''; ?>
                                >
                                    <?php echo expenseEscape($user['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="search-btn">
                    Search
                </button>

                <a href="admin_expense.php" class="reset-filter-btn">
                    Reset
                </a>

            </form>
        </div>

        <div class="expense-total-box">
            <div class="total-info">
                <span>Total Expense</span>
                <p>Overall expense based on selected filters</p>
            </div>

            <strong>
                Rs. <?php echo formatExpenseAmount($total_expense); ?>
            </strong>
        </div>

        <div class="expense-table-box">

            <div class="table-heading">
                <div>
                    <h2>Expense Records</h2>
                    <p>All recorded expenses are displayed below.</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($expense_result && mysqli_num_rows($expense_result) > 0): ?>

                    <?php while ($expense = mysqli_fetch_assoc($expense_result)): ?>

                        <tr>
                            <td><?php echo (int)$expense['id']; ?></td>

                            <td>
                                <?php echo expenseEscape($expense['category_name'] ?? 'N/A'); ?>
                            </td>

                            <td>
                                <?php echo expenseEscape($expense['item'] ?? 'N/A'); ?>

                                <?php if (!empty($expense['details'])): ?>
                                    <small>
                                        <?php echo expenseEscape($expense['details']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td class="expense-amount">
                                Rs. <?php echo formatExpenseAmount($expense['price']); ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($expense['expense_date'])
                                    ? expenseEscape(date('Y-m-d', strtotime($expense['expense_date'])))
                                    : '-';
                                ?>
                            </td>

                            <td>
                                <?php echo expenseEscape($expense['username'] ?? 'Unknown'); ?>
                            </td>

                            <td class="action-buttons">

                                <button
                                    type="button"
                                    class="view-btn"
                                    onclick='showExpense(
                                        <?php echo json_encode((string)$expense['id']); ?>,
                                        <?php echo json_encode($expense['category_name'] ?? 'N/A'); ?>,
                                        <?php echo json_encode($expense['item'] ?? 'N/A'); ?>,
                                        <?php echo json_encode($expense['details'] ?? ''); ?>,
                                        <?php echo json_encode(formatExpenseAmount($expense['price'])); ?>,
                                        <?php echo json_encode($expense['expense_date'] ?? '-'); ?>,
                                        <?php echo json_encode($expense['username'] ?? 'Unknown'); ?>
                                    )'
                                >View</button>

                                <a
                                    href="admin_expense.php?delete=<?php echo (int)$expense['id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this expense?');"
                                >Delete</a>

                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="no-expense">
                            No expense records found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </main>
</div>

<div class="expense-modal" id="expenseModal">

    <div class="expense-modal-content">

        <div class="modal-header">
            <h2>Expense Details</h2>
            <button type="button" onclick="closeExpense()">×</button>
        </div>

        <div class="modal-body">

            <div class="detail-row">
                <span>ID</span>
                <strong id="view_id"></strong>
            </div>

            <div class="detail-row">
                <span>Category</span>
                <strong id="view_category"></strong>
            </div>

            <div class="detail-row">
                <span>Item</span>
                <strong id="view_item"></strong>
            </div>

            <div class="detail-row">
                <span>Details</span>
                <strong id="view_details"></strong>
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

        </div>
    </div>
</div>

<script>
function showExpense(id, category, item, details, amount, date, user) {
    document.getElementById('view_id').textContent = id;
    document.getElementById('view_category').textContent = category;
    document.getElementById('view_item').textContent = item;
    document.getElementById('view_details').textContent = details || '-';
    document.getElementById('view_amount').textContent = 'Rs. ' + amount;
    document.getElementById('view_date').textContent = date;
    document.getElementById('view_user').textContent = user;

    document.getElementById('expenseModal').classList.add('show');
}

function closeExpense() {
    document.getElementById('expenseModal').classList.remove('show');
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('expenseModal');

    if (event.target === modal) {
        closeExpense();
    }
});
</script>

<?php include('footer.php'); ?>
