<?php
include('header.php');
checkUser();
$msg="";
$category_id="";
$item="";
$price="";
$details="";
$added_on="";
$label="Add";
if(isset($_GET['id']) && $_GET['id']>0){
    $label="Edit";
     $id=get_safe_value ($_GET['id']);
     $res=mysqli_query($con,"select * from expense where id=$id");
     $row=mysqli_fetch_assoc($res);

     $category_id=$row['category_id'];
     $item=$row['item'];
     $price=$row['price'];
     $details=$row['details'];
     $added_on=$row['added_on'];
}
if(isset($_POST['submit'])){
     $category_id=get_safe_value ($_POST['category_id']);
     $item=get_safe_value ($_POST['item']);
     $price=get_safe_value ($_POST['price']);
     $details=get_safe_value ($_POST['details']);
     $added_on=get_safe_value ($_POST['added_on']);

    $type="add";
    $sub_sql="";
    if(isset($_GET['id']) && $_GET['id']>0){
       $type="edit";
       $sub_sql="and id!=$id";
    }

    $sql="insert into expense(category_id,item,price,details,added_on) values('$category_id','$item','$price','$details','$added_on')";
         if(isset($_GET['id']) && $_GET['id']>0){
    
       $sql="update expense set category_id='$category_id',item='$item',price='$price',details='$details',added_on='$added_on' where id=$id";
         }
     mysqli_query($con,$sql); 
     redirect('expense.php');

}


include('user_header.php');
?>
<h2><?php echo $label ?>  Expense</h2>
<a href="expense.php">Back</a>
<br><br>

<form method="post">
    <table>
        <tr>
            <td>Category</td>
            <td><input type="text" name="name" required value="<?php echo $category ?>"></td>
        </tr>
         <tr>
            <td>item</td>
            <td><input type="text" name="item" required value="<?php echo $item ?>"></td>
        </tr>
         <tr>
            <td>Price</td>
            <td><input type="text" name="Price" required value="<?php echo $price ?>"></td>
        </tr>
         <tr>
            <td>Details</td>
            <td><input type="text" name="details" required value="<?php echo $details ?>"></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" name="submit" value="Submit"></td>
        </tr>
    </table>
</form>

<?php echo $msg; ?>
<?php
include('footer.php');
?>