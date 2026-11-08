
<?php

include('header.php');

checkUser();
adminArea();

// Admin information.
$admin_username = 'Admin';

if (!empty($_SESSION['USERNAME'])) {
    $admin_username = $_SESSION['USERNAME'];
}

// Total users.
$user_query = mysqli_query(
    $con,
    "SELECT COUNT(*) AS total_users
     FROM users
     WHERE role = 'User'"
);

$total_users = 0;

if ($user_query) {
    $user_row = mysqli_fetch_assoc($user_query);
    $total_users = $user_row['total_users'] ?? 0;
}

// Total categories.
$category_query = mysqli_query(
    $con,
    "SELECT COUNT(*) AS total_categories
     FROM category"
);

$total_categories = 0;

if ($category_query) {
    $category_row = mysqli_fetch_assoc($category_query);
    $total_categories = $category_row['total_categories'] ?? 0;
}

// Recent users.
$recent_users_query = mysqli_query(
    $con,
    "SELECT username, email, last_login
     FROM users
     WHERE role = 'User'
     ORDER BY id DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="admin_dashboard.css?v=3">

</head>

<body class="admin-dashboard-page">

<?php include('user_header.php'); ?>

<main class="admin-main-content">

    <!-- Dashboard Heading -->
    <section class="admin-welcome">

        <div class="welcome-text">
            <span class="welcome-label">ADMIN PANEL</span>

            <h1>Welcome, <?php echo htmlspecialchars($admin_username, ENT_QUOTES, 'UTF-8'); ?>!</h1>

            <p>Manage your Expense Management System from one place.</p>
        </div>

        <div class="welcome-date">
            <span class="date-icon">▦</span>

            <div>
                <small>Today's Date</small>
                <strong><?php echo date('F d, Y'); ?></strong>
            </div>
        </div>

    </section>


    <!-- Summary Cards -->
    <section class="admin-summary-grid">

        <div class="admin-summary-card users-card">

            <div class="summary-icon">♙</div>

            <div class="summary-content">
                <span>Total Users</span>
                <strong><?php echo number_format((int)$total_users); ?></strong>
                <small>Registered user accounts</small>
            </div>

        </div>


        <div class="admin-summary-card category-card">

            <div class="summary-icon">▦</div>

            <div class="summary-content">
                <span>Total Categories</span>
                <strong><?php echo number_format((int)$total_categories); ?></strong>
                <small>Available expense categories</small>
            </div>

        </div>

    </section>


    <!-- Recent Users -->
    <section class="admin-panel">

        <div class="panel-heading">

            <div>
                <span class="panel-label">USER MANAGEMENT</span>
                <h2>Recent Users</h2>
                <p>Latest registered user accounts.</p>
            </div>

            <div class="panel-heading-icon">♙</div>

        </div>


        <div class="user-table-wrapper">

            <table class="admin-user-table">

                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Username</th>
                        <th>Email Address</th>
                        <th>Last Login</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($recent_users_query && mysqli_num_rows($recent_users_query) > 0) { ?>

                        <?php
                        $sn = 1;

                        while ($user = mysqli_fetch_assoc($recent_users_query)) {
                        ?>

                            <tr>

                                <td><?php echo $sn++; ?></td>

                                <td>
                                    <div class="user-name-cell">
                                        <span class="user-avatar">♙</span>

                                        <strong>
                                            <?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </strong>
                                    </div>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php
                                    if (!empty($user['last_login'])) {
                                        echo htmlspecialchars(
                                            date('M d, Y', strtotime($user['last_login'])),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    } else {
                                        echo '<span class="never-login">Never logged in</span>';
                                    }
                                    ?>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="4" class="empty-users">
                                No registered users found.
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </section>


    <!-- Dashboard Footer Note -->
    <div class="dashboard-note">
        <span class="note-icon">✓</span>
        <p>Your admin dashboard is ready. Manage users and categories easily.</p>
    </div>

</main>

</body>
</html>
