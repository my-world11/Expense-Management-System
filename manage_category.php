<?php
include('header.php');
checkUser();
$msg="";
if(isset($_POST['submit'])){
     $name=get_safe_value ($_POST['name']);

    $res=mysqli_query($con,"select * from category where name='$name'");
    if(mysqli_num_rows($res)>0){
        $msg="Category already exists";
     
    }else{
     mysqli_query($con,"insert into category(name) values('$name')"); 
     redirect('category.php');
}
    }
include('user_header.php');
?>
<h2>Add Category</h2>
<a href="category.php">back</a>
<br><br>

<form method="post">
    <table>
        <tr>
            <td>Category</td>
            <td><input type="text" name="name" required></td>
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