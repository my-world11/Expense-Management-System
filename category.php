<?php
include('header.php');
checkUser();
if(isset($_GET['type']) && $_GET['type']=='delete' && isset($_GET['id']) && $_GET['id']>0){
   $id=get_safe_value($_GET['id']);
   mysqli_query($con,"delete from category where id=$id");
   echo "<br>Data is Deleted<br>";
}
include('user_header.php');
  $res=mysqli_query($con,"select * from category order by id desc");
?>
<h2>Category</h2>
<a href="manage_category.php">Add Category</a>
<br><br>
<?php 
if(mysqli_num_rows($res)>0){
?>

<table>
    <tr>
    <td>ID</td>
    <td>Name</td>
    </tr>
<?php while($row=mysqli_fetch_assoc($res)){
    ?>
    <tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td>
        <a href="">Edit</a>&nbsp;
        <a href="?type=delete&?id=<?php echo $row['id']; ?>">Delete</a>
    </td>
    </tr>
    <?php } ?>
</table>
<?php 
}else{
    echo "No data found";
} ?>

<?php
include('footer.php');
?>