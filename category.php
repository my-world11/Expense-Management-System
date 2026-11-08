<?php
include('header.php');
checkUser();
adminArea();
include('user_header.php');

$msg = "";

// Delete category safely
if (
    isset($_GET['type']) &&
    $_GET['type'] === 'delete' &&
    isset($_GET['id']) &&
    (int)$_GET['id'] > 0
) {
    $id = (int)$_GET['id'];

    $stmt = mysqli_prepare($con, "SELECT id, type FROM category WHERE id = ? AND created_by = 'Admin'");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $categoryResult = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($categoryResult) > 0) {
        $categoryRow = mysqli_fetch_assoc($categoryResult);

        // Prevent deleting categories that are already used by expenses
        if ($categoryRow['type'] === 'Expense') {
            $checkStmt = mysqli_prepare(
                $con,
                "SELECT COUNT(*) AS total FROM expense WHERE category_id = ?"
            );

            mysqli_stmt_bind_param($checkStmt, "i", $id);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $checkRow = mysqli_fetch_assoc($checkResult);

            if ($checkRow['total'] > 0) {
                $msg = "This category is already used in expenses and cannot be deleted.";
            } else {
                $deleteStmt = mysqli_prepare($con, "DELETE FROM category WHERE id = ? AND created_by = 'Admin'");
                mysqli_stmt_bind_param($deleteStmt, "i", $id);
                mysqli_stmt_execute($deleteStmt);

                redirect('category.php');
                die();
            }
        } else {
            $deleteStmt = mysqli_prepare($con, "DELETE FROM category WHERE id = ? AND created_by = 'Admin'");
            mysqli_stmt_bind_param($deleteStmt, "i", $id);
            mysqli_stmt_execute($deleteStmt);

            redirect('category.php');
            die();
        }
    } else {
        redirect('category.php');
        die();
    }
}

// Fetch expense categories
$expenseResult = mysqli_query(
    $con,
    "SELECT * FROM category WHERE type = 'Expense' AND created_by = 'Admin' ORDER BY id ASC"
);

// Fetch income categories
$incomeResult = mysqli_query(
    $con,
    "SELECT * FROM category WHERE type = 'Income' AND created_by = 'Admin' ORDER BY id ASC"
);
?>

<main class="admin-main-content">
    <div class="admin-category-page">

        <div class="category-page-heading">
            <div>
                <h2>Category Management</h2>
                <p>Manage your income and expense categories in one place.</p>
            </div>

            <a href="manage_category.php" class="category-add-btn">
                <span>+</span> Add Category
            </a>
        </div>

        <?php if (!empty($msg)) { ?>
            <div class="category-alert">
                <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php } ?>

        <!-- Expense Categories -->
        <div class="category-section expense-category-section">

            <div class="category-section-heading">
                <div class="category-section-title">
                    <div class="category-icon expense-icon">−</div>
                    <div>
                        <h3>Expense Categories</h3>
                        <p>Categories used to organize your expenses.</p>
                    </div>
                </div>

                <span class="category-count expense-count">
                    <?php echo mysqli_num_rows($expenseResult); ?> Categories
                </span>
            </div>

            <div class="category-table-wrapper">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($expenseResult) > 0) { ?>

                            <?php $expenseCount = 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($expenseResult)) { ?>
                                <tr>
                                    <td><?php echo $expenseCount++; ?></td>

                                    <td>
                                        <span class="category-name">
                                            <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="category-type-badge expense-badge">
                                            Expense
                                        </span>
                                    </td>

                                    <td>
                                        <div class="category-actions">
                                            <a
                                                href="manage_category.php?id=<?php echo (int)$row['id']; ?>"
                                                class="category-edit-btn"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="javascript:void(0)"
                                                class="category-delete-btn"
                                                onclick="delete_confir('<?php echo (int)$row['id']; ?>', 'category.php')"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="category-empty">
                                    No expense categories found.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Income Categories -->
        <div class="category-section income-category-section">

            <div class="category-section-heading">
                <div class="category-section-title">
                    <div class="category-icon income-icon">+</div>
                    <div>
                        <h3>Income Categories</h3>
                        <p>Categories used to organize your income.</p>
                    </div>
                </div>

                <span class="category-count income-count">
                    <?php echo mysqli_num_rows($incomeResult); ?> Categories
                </span>
            </div>

            <div class="category-table-wrapper">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($incomeResult) > 0) { ?>

                            <?php $incomeCount = 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($incomeResult)) { ?>
                                <tr>
                                    <td><?php echo $incomeCount++; ?></td>

                                    <td>
                                        <span class="category-name">
                                            <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="category-type-badge income-badge">
                                            Income
                                        </span>
                                    </td>

                                    <td>
                                        <div class="category-actions">
                                            <a
                                                href="manage_category.php?id=<?php echo (int)$row['id']; ?>"
                                                class="category-edit-btn"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="javascript:void(0)"
                                                class="category-delete-btn"
                                                onclick="delete_confir('<?php echo (int)$row['id']; ?>', 'category.php')"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="category-empty">
                                    No income categories found.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php
include('footer.php');
?>
