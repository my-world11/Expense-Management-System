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
    $sub_sql.=" and income.income_date between '$from' and '$to' ";
}


$res=mysqli_query($con,"select income.source,income.amount,income.details,income.income_date
from income
where income.added_by='".$_SESSION['UID']."'
$sub_sql
order by income.income_date asc");

?>


<style>

.report-title{
    font-size:30px;
    font-weight:bold;
    color:#0d6efd;
    margin:25px 0;
}

.filter-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
    margin-bottom:25px;
}

.filter-form{
    display:flex;
    gap:15px;
    align-items:end;
    flex-wrap:wrap;
}

.filter-group{
    display:flex;
    flex-direction:column;
    gap:6px;
}

.filter-group label{
    font-weight:bold;
    font-size:17px;
}

.filter-group input{
    height:45px;
    padding:8px 12px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:16px;
}

.filter-btn{
    height:45px;
    padding:0 20px;
    background:#0d6efd;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.filter-btn:hover{
    background:#0b5ed7;
}

.reset-btn{
    display:inline-flex;
    align-items:center;
    height:45px;
    padding:0 20px;
    background:#6c757d;
    color:#fff;
    border-radius:6px;
    text-decoration:none;
}

.reset-btn:hover{
    background:#5c636a;
    color:#fff;
}

.date-range{
    margin:20px 0;
    font-size:18px;
}

.table-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
    overflow:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#0d6efd;
    color:#fff;
    padding:14px;
    text-align:center;
    font-size:17px;
}

table td{
    padding:13px;
    border-bottom:1px solid #ddd;
    text-align:center;
    font-size:16px;
}

table tr:hover{
    background:#f5f9ff;
}

.total-row{
    font-weight:bold;
    font-size:18px;
    background:#f1f5ff;
}

.no-data{
    text-align:center;
    font-size:22px;
    color:#666;
    padding:40px;
}

</style>


<h2 class="report-title">
    <i class="fa-solid fa-chart-line"></i>
    Income Reports
</h2>


<div class="filter-box">

<form method="get">

<div class="filter-form">


<div class="filter-group">

    <label>From</label>

    <input
        type="date"
        name="from"
        value="<?php echo $from; ?>"
        max="<?php echo date('Y-m-d'); ?>"
        id="from_date"
        onchange="set_to_date()">

</div>


<div class="filter-group">

    <label>To</label>

    <input
        type="date"
        name="to"
        value="<?php echo $to; ?>"
        max="<?php echo date('Y-m-d'); ?>"
        id="to_date">

</div>


<div>

    <input
        type="submit"
        value="Search"
        class="filter-btn">

</div>


<div>

    <a href="income_report.php" class="reset-btn">
        Reset
    </a>

</div>


</div>

</form>

</div>


<?php if($from!='' && $to!=''){ ?>

<div class="date-range">

    <b>From:</b>
    <?php echo $from; ?>

    &nbsp;&nbsp;

    <b>To:</b>
    <?php echo $to; ?>

</div>

<?php } ?>


<?php

if(mysqli_num_rows($res)>0){

?>


<div class="table-box">

<table>

<tr>

    <th>Source</th>
    <th>Amount</th>
    <th>Details</th>
    <th>Income Date</th>

</tr>


<?php

$final_amount=0;

while($row=mysqli_fetch_assoc($res)){

    $final_amount=$final_amount+$row['amount'];

?>


<tr>

<td>
    <?php echo $row['source']; ?>
</td>

<td>
    <b>
        Rs. <?php echo number_format($row['amount'],2); ?>
    </b>
</td>

<td>
    <?php echo $row['details']; ?>
</td>

<td>
    <?php echo $row['income_date']; ?>
</td>

</tr>


<?php
}
?>


<tr class="total-row">

<td></td>

<td>
    Total Income
</td>

<td></td>

<td>
    Rs. <?php echo number_format($final_amount,2); ?>
</td>

</tr>


</table>

</div>


<?php

}else{

?>


<div class="table-box">

<div class="no-data">

    <i class="fa-solid fa-folder-open"></i>

    <br><br>

    No Income Found

</div>

</div>


<?php
}
?>


<?php
include('footer.php');
?>