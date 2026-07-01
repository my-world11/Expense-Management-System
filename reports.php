<?php
include('header.php');
checkUser();
include('user_header.php');

$res = mysqli_query($con,"select sum(expense.price) as price, category.name from expense, category
where expense.category_id = category.id group by expense.category_id");
?>
<h2>Reports</h2>


<table border="1">
    <tr>
    <td>Category</td>
    <td>Price</td>
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
    <td>Total</td>
    <td><?php echo $final_price ?></td>
 </tr>


</table>

<?php
include('footer.php');
?>