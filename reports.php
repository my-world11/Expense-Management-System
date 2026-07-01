<?php
include('header.php');
checkUser();
include('user_header.php');

$cat_id='';
$sub_sql='';
if(isset($_GET['cat_id']) && $_GET['cat_id']>0){
$cat_id= get_safe_value ($_GET['cat_id']);
$sub_sql=" and category.id=$cat_id";
}

$res = mysqli_query($con,"select sum(expense.price) as price, category.name from expense, category
where expense.category_id = category.id $sub_sql group by expense.category_id");
?>
<h2>Reports</h2>

<form type="get">
   From <input type ="date" name="from">
   &nbsp;  &nbsp;  &nbsp;
    To <input type ="date" name="to">
</form>

<?php echo getCategory($cat_id,'reports'); 
?>
<br><br>
<table border="1">
    <tr>
    <th>Category</th>
    <th>Price</th>
 </tr>

 <?php
  $final_price=0;
 while($row=mysqli_fetch_assoc($res)){  
     $final_price= $final_price+$row['price'];
 ?>
    <tr>
     <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['price']; ?></td>
 </tr>
 <?php } ?>
  <tr>
    <th>Total</th>
    <th><?php echo $final_price ?></th>
 </tr>


</table>

<?php
include('footer.php');
?>