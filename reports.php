<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

$from='';
$to='';
$category_id='';

if(isset($_GET['from'])){
    $from=get_safe_value($_GET['from']);
}

if(isset($_GET['to'])){
    $to=get_safe_value($_GET['to']);
}

if(isset($_GET['category_id'])){
    $category_id=get_safe_value($_GET['category_id']);
}

?>

<style>

.report-title{
    margin:25px 0;
    font-size:32px;
    color:#0d6efd;
}

.filter-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
    margin-bottom:25px;
}

.filter-box form{
    display:flex;
    gap:15px;
    align-items:end;
    flex-wrap:wrap;
}

.filter-group{
    flex:1;
    min-width:200px;
}

.filter-group label{
    display:block;
    font-weight:bold;
    font-size:17px;
    margin-bottom:7px;
}

.filter-group input,
.filter-group select{
    width:100%;
    height:50px;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:17px;
}

.filter-btn{
    background:#0d6efd;
    color:white;
    border:none;
    padding:14px 25px;
    border-radius:8px;
    cursor:pointer;
    font-size:17px;
}

.filter-btn:hover{
    background:#0b5ed7;
}

.reset-btn{
    background:#6c757d;
    color:white;
    padding:14px 25px;
    border-radius:8px;
    text-decoration:none;
    font-size:17px;
}

.reset-btn:hover{
    background:#5c636a;
    color:white;
}

.report-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
    overflow:auto;
}

.report-box table{
    width:100%;
    border-collapse:collapse;
}

.report-box th{
    background:#0d6efd;
    color:#fff;
    padding:14px;
    font-size:17px;
}

.report-box td{
    padding:13px;
    border-bottom:1px solid #ddd;
    text-align:center;
    font-size:16px;
}

.report-box tr:hover{
    background:#f5f9ff;
}

.total-row{
    background:#e9f2ff;
    font-weight:bold;
}

.no-data{
    text-align:center;
    padding:40px;
    font-size:22px;
    color:#666;
}

</style>

<h2 class="report-title">
    <i class="fa-solid fa-chart-column"></i>
    Expense Reports
</h2>

<div class="filter-box">

<form method="get">

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


    <div class="filter-group">

        <label>Category</label>

        <?php echo getCategory($category_id,'reports'); ?>

    </div>


    <div>
        <input
            type="submit"
            value="Search"
            class="filter-btn">
    </div>


    <div>
        <a href="reports.php" class="reset-btn">
            Reset
        </a>
    </div>

</form>

</div>


<?php

$sub_sql="";

if($from!='' && $to!=''){
    $sub_sql.=" and expense.expense_date between '$from' and '$to' ";
}

if($category_id!=''){
    $sub_sql.=" and expense.category_id='$category_id' ";
}


$res=mysqli_query($con,"
    select
        expense.price,
        category.name,
        expense.item,
        expense.expense_date
    from expense
    join category
        on expense.category_id=category.id
    where expense.added_by='".$_SESSION['UID']."'
    $sub_sql
    order by expense.expense_date desc
");


if(mysqli_num_rows($res)>0){

?>

<div class="report-box">

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

    $final_price=$final_price+$row['price'];

?>

<tr>

    <td>
        <?php echo $row['name']; ?>
    </td>

    <td>
        <?php echo $row['item']; ?>
    </td>

    <td>
        <?php echo $row['expense_date']; ?>
    </td>

    <td>
        <b>Rs. <?php echo number_format($row['price'],2); ?></b>
    </td>

</tr>

<?php
}
?>

<tr class="total-row">

    <td></td>
    <td></td>

    <th>
        Total
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

<div class="report-box">

    <div class="no-data">

        <i class="fa-solid fa-folder-open"></i>

        <br><br>

        No Expense Found

    </div>

</div>

<?php
}
?>

<?php
include('footer.php');
?>