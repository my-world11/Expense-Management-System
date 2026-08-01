<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

if(isset($_GET['type']) && $_GET['type']=='delete' && isset($_GET['id']) && $_GET['id']>0){
   $id=get_safe_value($_GET['id']);
   mysqli_query($con,"delete from income where id=$id");
   echo "<br>Data is Deleted<br>";
}

$res=mysqli_query($con,"select * from income where added_by='".$_SESSION['UID']."' order by income_date asc");
?>

<h2>Income</h2>

<a href="manage_income.php">Add Income</a>

<br><br>

<?php
if(mysqli_num_rows($res)>0){
?>

<table border="1">

<tr>
    <td>ID</td>
    <td>Source</td>
    <td>Amount</td>
    <td>Details</td>
    <td>Income Date</td>
    <td>Action</td>
</tr>

<?php
while($row=mysqli_fetch_assoc($res)){
?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['source']; ?></td>

<td><?php echo $row['amount']; ?></td>

<td><?php echo $row['details']; ?></td>

<td><?php echo $row['income_date']; ?></td>

<td>

<a href="manage_income.php?id=<?php echo $row['id']; ?>">
Edit
</a>

&nbsp;

<a href="javascript:void(0)"
onclick="delete_confir('<?php echo $row['id'];?>','income.php')">
Delete
</a>

</td>

</tr>

<?php } ?>

</table>

<?php
}else{
    echo "No data found";
}
?>

<?php
include('footer.php');
?>