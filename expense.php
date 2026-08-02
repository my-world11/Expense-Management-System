<?php
include('header.php');
checkUser();
userArea();
include('user_header.php');

if(isset($_GET['type']) && $_GET['type']=='delete' && isset($_GET['id']) && $_GET['id']>0){
    $id=get_safe_value($_GET['id']);
    mysqli_query($con,"delete from expense where id=$id");
    echo "<br>Data is Deleted<br>";
}

$res=mysqli_query($con,"select expense.*,category.name from expense,category where expense.category_id=category.id and expense.added_by='".$_SESSION['UID']."' order by expense.expense_date desc");
?>

<style>

.page-title{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:25px 0;
}

.page-title h2{
    font-size:32px;
    color:#0d6efd;
}

.add-btn{
    background:#0d6efd;
    color:#fff;
    padding:12px 25px;
    border-radius:8px;
    text-decoration:none;
    font-size:18px;
    font-weight:bold;
}

.add-btn:hover{
    background:#0b5ed7;
    text-decoration:none;
    color:#fff;
}

.search-box{
    display:flex;
    gap:15px;
    margin-bottom:25px;
    flex-wrap:wrap;
}

.search-box input,
.search-box select{
    height:50px;
    padding:0 15px;
    font-size:18px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
}

.search-box input{
    width:300px;
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
    padding:15px;
    font-size:18px;
    text-align:center;
}

table td{
    padding:14px;
    border-bottom:1px solid #ddd;
    text-align:center;
    font-size:17px;
}

table tr:hover{
    background:#f5f9ff;
}

.edit-btn{
    background:#198754;
    color:#fff;
    padding:8px 15px;
    border-radius:6px;
    text-decoration:none;
    margin-right:5px;
}

.edit-btn:hover{
    background:#157347;
    color:#fff;
    text-decoration:none;
}

.delete-btn{
    background:#dc3545;
    color:#fff;
    padding:8px 15px;
    border-radius:6px;
    text-decoration:none;
}

.delete-btn:hover{
    background:#bb2d3b;
    color:#fff;
    text-decoration:none;
}

.no-data{
    text-align:center;
    font-size:22px;
    color:#666;
    padding:40px;
}

</style>

<div class="page-title">

<h2><i class="fa-solid fa-wallet"></i> Expense List</h2>

<a href="manage_expense.php" class="add-btn">
<i class="fa-solid fa-plus"></i> Add Expense
</a>

</div>

<div class="search-box">

<input type="text" id="searchInput" placeholder="Search Expense...">

</div>

<?php
if(mysqli_num_rows($res)>0){
?>

<div class="table-box">

<table>

<tr>
<th>ID</th>
<th>Category</th>
<th>Item</th>
<th>Price</th>
<th>Details</th>
<th>Expense Date</th>
<th>Action</th>
</tr>
<?php
while($row=mysqli_fetch_assoc($res)){
?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['item']; ?></td>

<td><b>Rs. <?php echo number_format($row['price'],2); ?></b></td>

<td><?php echo $row['details']; ?></td>

<td><?php echo $row['expense_date']; ?></td>

<td>

<a href="manage_expense.php?id=<?php echo $row['id']; ?>" class="edit-btn">
<i class="fa-solid fa-pen-to-square"></i> Edit
</a>

<a href="javascript:void(0)"
class="delete-btn"
onclick="delete_confir('<?php echo $row['id']; ?>','expense.php')">
<i class="fa-solid fa-trash"></i> Delete
</a>

</td>

</tr>

<?php
}
?>

</table>

</div>

<?php
}else{
?>

<div class="table-box">
<div class="no-data">
<i class="fa-solid fa-folder-open"></i><br><br>
No Expense Found
</div>
</div>

<?php
}
?>

<script>

const searchInput=document.getElementById("searchInput");

searchInput.addEventListener("keyup",function(){

let filter=this.value.toLowerCase();

let rows=document.querySelectorAll(".table-box table tr");

for(let i=1;i<rows.length;i++){

let text=rows[i].innerText.toLowerCase();

if(text.indexOf(filter)>-1){
rows[i].style.display="";
}else{
rows[i].style.display="none";
}

}

});

</script>

<?php
include('footer.php');
?>