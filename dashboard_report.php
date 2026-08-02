<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

$from='';
$to='';
$sub_sql="";

if(isset($_GET['from'])){
    $from=get_safe_value($_GET['from']);
}

if(isset($_GET['to'])){
    $to=get_safe_value($_GET['to']);
}

if($from!='' && $to!=''){
    $sub_sql.=" and expense.expense_date between '$from' and '$to' ";
}

$res=mysqli_query($con,"select expense.price,category.name,expense.item,expense.expense_date
from expense,category
where expense.category_id=category.id
and expense.added_by='".$_SESSION['UID']."'
$sub_sql");
?>

<style>

.report-box{
    width:95%;
    max-width:1100px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.report-box h2{
    text-align:center;
    color:#0d6efd;
    font-size:34px;
    margin-bottom:25px;
}

.date-info{
    display:flex;
    justify-content:center;
    gap:40px;
    margin-bottom:20px;
    font-size:20px;
    font-weight:bold;
}

.date-card{
    background:#eaf3ff;
    padding:15px 25px;
    border-radius:10px;
}

.table-box{
    overflow:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#0d6efd;
    color:#fff;
    padding:15px;
    font-size:18px;
}

table td{
    padding:14px;
    text-align:center;
    border-bottom:1px solid #ddd;
    font-size:17px;
}

table tr:hover{
    background:#f7faff;
}

.total-row{
    background:#0d6efd;
    color:#fff;
    font-weight:bold;
}

.no-data{
    text-align:center;
    font-size:24px;
    color:#666;
    padding:50px;
}

</style>

<div class="report-box">

<h2><i class="fa-solid fa-chart-column"></i> Dashboard Reports</h2>

<?php if($from!='' && $to!=''){ ?>

<div class="date-info">

<div class="date-card">
From :
<?php echo $from; ?>
</div>

<div class="date-card">
To :
<?php echo $to; ?>
</div>

</div>

<?php } ?>

<?php
if(mysqli_num_rows($res)>0){
?>

<div class="table-box">

<table>

<tr>
<th>Category</th>
<th>Item</th>
<th>Expense Date</th>
<th>Price</th>
</tr>

<?php

$final_price=0;

while($row=mysqli_fetch_assoc($res)){

$final_price+=$row['price'];

?>
<tr>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['item']; ?></td>

<td><?php echo $row['expense_date']; ?></td>

<td><b>Rs. <?php echo number_format($row['price'],2); ?></b></td>

</tr>

<?php
}
?>

<tr class="total-row">

<th colspan="3" style="text-align:right;">
Total Expense
</th>

<th>
Rs. <?php echo number_format($final_price,2); ?>
</th>

</tr>

</table>

</div>

<?php
}else{
?>

<div class="no-data">

<i class="fa-solid fa-folder-open"
style="font-size:60px;color:#0d6efd;"></i>

<br><br>

<b>No Expense Report Found</b>

</div>

<?php
}
?>

</div>

<?php
include('footer.php');
?>