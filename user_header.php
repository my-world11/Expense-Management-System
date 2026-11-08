
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
.side-menu {
    position: fixed;
    top: 0 ;
    left: 0 ;
    width: 180px ;
    height: 100vh ;
    background: #ffffff ;
    border-right: 1px solid #e5e7eb ;
    box-shadow: 2px 0 10px rgba(0,0,0,.06) ;
    padding-top: 18px ;
    box-sizing: border-box ;
    display: flex ;
    flex-direction: column ;
    align-items: stretch ;
    z-index: 99999 ;
    overflow: hidden ;
}

.side-title {
    display: flex ;
    align-items: center ;
    justify-content: flex-start ;
    gap: 8px ;
    width: 100% ;
    margin: 0 ;
    padding: 12px 16px 20px ;
    box-sizing: border-box ;
    color: #111827 ;
    font-size: 17px ;
    font-weight: 700 ;
}

.side-title i {
    display: flex ;
    align-items: center ;
    justify-content: center ;
    color: #2563eb ;
    font-size: 16px ;
    width: 20px ;
    min-width: 20px ;
}

.side-title span {
    display: block ;
    color: #111827 ;
    font-size: 17px ;
    white-space: nowrap ;
}

.side-menu a {
    display: flex ;
    align-items: center ;
    justify-content: flex-start ;
    width: calc(100% - 16px) ;
    height: 42px ;
    min-height: 42px ;
    margin: 3px 8px ;
    padding: 0 12px ;
    box-sizing: border-box ;
    background: #ffffff ;
    color: #1f2937 ;
    border-radius: 7px ;
    font-size: 13px ;
    font-weight: 500 ;
    text-decoration: none ;
    white-space: nowrap ;
    transition: .2s ease ;
    flex-shrink: 0 ;
}

.side-menu a i {
    display: flex ;
    align-items: center ;
    justify-content: center ;
    width: 20px ;
    min-width: 20px ;
    height: 20px ;
    margin-right: 8px ;
    color: #374151 ;
    font-size: 14px ;
    flex-shrink: 0 ;
}

.side-menu a span {
    display: block ;
    margin: 0 ;
    padding: 0 ;
    color: inherit ;
    font-size: 13px ;
    white-space: nowrap ;
}

.side-menu a:hover {
    background: #eff6ff ;
    color: #2563eb ;
}

.side-menu a:hover i {
    color: #2563eb ;
}

.side-menu a.active {
    background: #2563eb ;
    color: #ffffff ;
    font-weight: 600 ;
    box-shadow: 0 3px 8px rgba(37,99,235,.20) ;
}

.side-menu a.active i {
    color: #ffffff ;
}

.container {
    margin-left: 180px ;
    width: calc(100% - 180px) ;
    box-sizing: border-box ;
}

@media(max-width:500px) {
    .side-menu {
        width: 65px ;
        height: 100vh ;
    }

    .side-title {
        justify-content: center ;
        padding: 15px 5px ;
        font-size: 0 ;
    }

    .side-title span {
        display: none ;
    }

    .side-title i {
        margin: 0 ;
        font-size: 18px ;
    }

    .side-menu a {
        justify-content: center ;
        width: calc(100% - 10px) ;
        margin: 3px 5px ;
        padding: 0 ;
        font-size: 0 ;
    }

    .side-menu a span {
        display: none ;
    }

    .side-menu a i {
        margin: 0 ;
        width: auto ;
        min-width: 0 ;
        font-size: 16px ;
    }

    .container {
        margin-left: 65px ;
        width: calc(100% - 65px) ;
    }
}
</style>

<?php if (isset($_SESSION['UROLE']) && $_SESSION['UROLE'] == 'Admin') { ?>

<div class="side-menu">

    <div class="side-title">
        <i class="fa-solid fa-wallet"></i>
        <span>Expense</span>
    </div>

    <a href="admin_dashboard.php"
       class="<?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>

    <a href="users.php"
       class="<?php echo ($current_page == 'users.php' || $current_page == 'manage_user.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>Users</span>
    </a>

    <a href="category.php"
       class="<?php echo ($current_page == 'category.php' || $current_page == 'manage_category.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-layer-group"></i>
        <span>Categories</span>
    </a>

    <a href="admin_expense.php"
       class="<?php echo ($current_page == 'admin_expense.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-money-bill-wave"></i>
        <span>Expenses</span>
    </a>

    <a href="admin_income.php"
       class="<?php echo ($current_page == 'admin_income.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-money-bill-trend-up"></i>
        <span>Income</span>
    </a>

    <a href="logout.php"
       class="<?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
    </a>

</div>

<?php } else { ?>

<div class="side-menu">

    <div class="side-title">
        <i class="fa-solid fa-wallet"></i>
        <span>Expense</span>
    </div>

    <a href="dashboard.php"
       class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>

    <a href="expense.php"
       class="<?php echo ($current_page == 'expense.php' || $current_page == 'manage_expense.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-money-bill-wave"></i>
        <span>Expense</span>
    </a>

    <a href="income.php"
       class="<?php echo ($current_page == 'income.php' || $current_page == 'manage_income.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-money-bill-trend-up"></i>
        <span>Income</span>
    </a>

    <a href="reports.php"
       class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-column"></i>
        <span>Reports</span>
    </a>

    <a href="logout.php"
       class="<?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
    </a>

</div>

<?php } ?>
