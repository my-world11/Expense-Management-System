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

$res=mysqli_query($con,"
select source,amount,details,income_date
from income
where added_by='".$_SESSION['UID']."'
$sub_sql
order by income_date desc
");
?>

<h2>💰 Income Report</h2>

<?php
if($from!='' && $to!=''){
?>
<p>
<b>From:</b> <?php echo $from; ?>
&nbsp;&nbsp;
<b>To:</b> <?php echo $to; ?>
</p>
<?php } ?>

<?php
if(mysqli_num_rows($res)>0){
?>

<table border="1" cellpadding="10" cellspacing="0">

<tr>
<th>Source</th>
<th>Amount</th>
<th>Details</th>
<th>Income Date</th>
</tr>

<?php
$total=0;

while($row=mysqli_fetch_assoc($res)){

$total+=$row['amount'];
?>

<tr>

<td><?php echo $row['source']; ?></td>

<td><?php echo $row['amount']; ?></td>

<td><?php echo $row['details']; ?></td>

<td><?php echo $row['income_date']; ?></td>

</tr>

<?php } ?>

<tr>

<th colspan="3">Total Income</th>

<th><?php echo $total; ?></th>

</tr>

</table>

<?php
}else{
echo "<b>No Income Found</b>";
}
?>

<br><br>

<a href="dashboard.php">← Back to Dashboard</a>

<?php
include('footer.php');
?>