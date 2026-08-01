<?php
include('header.php');
checkUser();
adminArea();
include('user_header.php');

/* Suspend User */
if(isset($_GET['type']) && $_GET['type']=='suspend' && isset($_GET['id']) && $_GET['id']>0){
    $id=get_safe_value($_GET['id']);
    mysqli_query($con,"update users set status='Suspended' where id=$id");
}

/* Activate User */
if(isset($_GET['type']) && $_GET['type']=='active' && isset($_GET['id']) && $_GET['id']>0){
    $id=get_safe_value($_GET['id']);
    mysqli_query($con,"update users set status='Active' where id=$id");
}

/* Search */
$search="";
$sql="select * from users where role='User'";

if(isset($_GET['search']) && $_GET['search']!=""){
    $search=get_safe_value($_GET['search']);
    $sql.=" and username like '%$search%'";
}

$sql.=" order by id desc";

$res=mysqli_query($con,$sql);
?>

<style>

.title{
    font-size:28px;
    font-weight:bold;
    margin-bottom:20px;
}

.top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.btn{
    padding:10px 18px;
    text-decoration:none;
    border-radius:5px;
    color:white;
}

.add{
    background:green;
}

.search{
    padding:8px;
    width:220px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

table th{
    background:#0d6efd;
    color:white;
    padding:10px;
}

table td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

table tr:hover{
    background:#f5f5f5;
}

.edit{
    color:blue;
    text-decoration:none;
}

.suspend{
    color:red;
    text-decoration:none;
}

.active{
    color:green;
    text-decoration:none;
}

.status-active{
    color:green;
    font-weight:bold;
}

.status-suspend{
    color:red;
    font-weight:bold;
}

</style>

<div class="title">Users Management</div>

<div class="top">

<a href="manage_user.php" class="btn add">
➕ Add User
</a>

<form>

<input
type="text"
name="search"
class="search"
placeholder="Search Username..."
value="<?php echo $search;?>">

<input type="submit" value="Search">

</form>

</div>

<?php
if(mysqli_num_rows($res)>0){
?>

<table>

<tr>

<th>ID</th>
<th>Username</th>
<th>Email</th>
<th>Status</th>
<th>Action</th>

</tr>

<?php
while($row=mysqli_fetch_assoc($res)){
?>

<tr>

<td><?php echo $row['id'];?></td>

<td><?php echo $row['username'];?></td>

<td><?php echo $row['email'];?></td>

<td>

<?php
if($row['status']=="Active"){
    echo "<span class='status-active'>Active</span>";
}else{
    echo "<span class='status-suspend'>Suspended</span>";
}
?>

</td>

<td>

<a class="edit"
href="manage_user.php?id=<?php echo $row['id'];?>">
✏ Edit
</a>

&nbsp;&nbsp;

<?php
if($row['status']=="Active"){
?>

<a class="suspend"
href="users.php?type=suspend&id=<?php echo $row['id'];?>">
🚫 Suspend
</a>

<?php
}else{
?>

<a class="active"
href="users.php?type=active&id=<?php echo $row['id'];?>">
✔ Activate
</a>

<?php
}
?>

</td>

</tr>

<?php
}
?>

</table>

<?php
}else{
echo "<h3>No User Found</h3>";
}

include('footer.php');
?>