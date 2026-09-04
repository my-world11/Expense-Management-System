<?php

if(isset($_SESSION['UROLE']) && $_SESSION['UROLE'] == 'Admin'){

?>

<div class="side-menu">

    <div class="side-title">

        <i class="fa-solid fa-wallet"></i>

        Expense

    </div>


    <a href="category.php">

        <i class="fa-solid fa-layer-group"></i>

        Category

    </a>


    <a href="users.php">

        <i class="fa-solid fa-users"></i>

        Users

    </a>


    <a href="logout.php">

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>

</div>


<?php

}
else{

?>

<div class="side-menu">

    <div class="side-title">

        <i class="fa-solid fa-wallet"></i>

        Expense

    </div>


    <a href="dashboard.php">

        <i class="fa-solid fa-house"></i>

        Dashboard

    </a>


    <a href="expense.php">

        <i class="fa-solid fa-money-bill-wave"></i>

        Expense

    </a>


    <a href="income.php">

        <i class="fa-solid fa-money-bill-trend-up"></i>

        Income

    </a>


    <a href="reports.php">

        <i class="fa-solid fa-chart-column"></i>

        Reports

    </a>


    <a href="logout.php">

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>

</div>


<?php

}

?>


<style>

.side-menu{

    position:fixed;
    left:0;
    top:0;
    width:230px;
    height:100vh;
    background:#ffffff;
    border-right:1px solid #ddd;
    box-shadow:2px 0 10px rgba(0,0,0,.08);
    padding-top:20px;
    z-index:1000;

}

.side-title{

    font-size:22px;
    font-weight:bold;
    padding:15px 20px 25px 20px;
    color:#263238;

}

.side-title i{

    margin-right:8px;
    color:#0d6efd;

}

.side-menu a{

    display:block;
    padding:14px 20px;
    color:#555;
    font-size:16px;
    font-weight:500;
    text-decoration:none;
    transition:.2s;

}

.side-menu a i{

    width:25px;
    margin-right:8px;
    color:#777;

}

.side-menu a:hover{

    background:#eef5ff;
    color:#0d6efd;

}

.side-menu a:hover i{

    color:#0d6efd;

}

.container{

    margin-left:230px;
    width:calc(100% - 230px);
    padding:20px;

}

</style>