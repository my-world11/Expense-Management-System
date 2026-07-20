<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');
?>

<h2 class="mb-4">
<i class="fa-solid fa-chart-column"></i>
Dashboard
</h2>

<div class="row">

<div class="col-md-4 mb-3">
<div class="card p-3 text-center">
<h5>Today's Expense</h5>
<h3 class="text-primary">
<?php echo getDashboardExpense('today')?>
</h3>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card p-3 text-center">
<h5>Yesterday</h5>
<h3 class="text-success">
<?php echo getDashboardExpense('yesterday')?>
</h3>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card p-3 text-center">
<h5>This Week</h5>
<h3 class="text-danger">
<?php echo getDashboardExpense('week')?>
</h3>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card p-3 text-center">
<h5>This Month</h5>
<h3 class="text-warning">
<?php echo getDashboardExpense('month')?>
</h3>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card p-3 text-center">
<h5>This Year</h5>
<h3 class="text-info">
<?php echo getDashboardExpense('year')?>
</h3>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card p-3 text-center">
<h5>Total Expense</h5>
<h3 class="text-dark">
<?php echo getDashboardExpense('total')?>
</h3>
</div>
</div>

</div>

<?php
include('footer.php');
?>