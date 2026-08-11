<?php
include('header.php');
checkUser();
adminArea();
include('user_header.php');


/* Suspend User */
if(isset($_GET['type']) && $_GET['type']=='suspend' && isset($_GET['id']) && $_GET['id']>0){

    $id=get_safe_value($_GET['id']);

    $date=date('Y-m-d',strtotime('+20 days'));

    mysqli_query($con,"update users set
        status='Suspended',
        suspend_until='$date'
        where id=$id");

}


/* Activate User */
if(isset($_GET['type']) && $_GET['type']=='active' && isset($_GET['id']) && $_GET['id']>0){

    $id=get_safe_value($_GET['id']);

    mysqli_query($con,"update users set
        status='Active',
        suspend_until=NULL
        where id=$id");

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
    gap:20px;
    flex-wrap:wrap;
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

.add:hover{
    background:#157347;
    color:white;
}

.search{
    padding:10px;
    width:220px;
    border:1px solid #ccc;
    border-radius:5px;
    font-size:16px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

table th{
    background:#0d6efd;
    color:white;
    padding:12px;
}

table td{
    border:1px solid #ddd;
    padding:12px;
    text-align:center;
}

table tr:hover{
    background:#f5f5f5;
}

.edit{
    color:#0d6efd;
    text-decoration:none;
    font-weight:bold;
}

.edit:hover{
    color:#084298;
}

.suspend{
    color:#dc3545;
    text-decoration:none;
    font-weight:bold;
}

.suspend:hover{
    color:#bb2d3b;
}

.active{
    color:#198754;
    text-decoration:none;
    font-weight:bold;
}

.active:hover{
    color:#146c43;
}

.status-active{
    color:#198754;
    font-weight:bold;
}

.status-suspend{
    color:#dc3545;
    font-weight:bold;
}

.no-data{
    background:#fff;
    padding:40px;
    text-align:center;
    font-size:20px;
}

</style>


<div class="title">

    <i class="fa-solid fa-users"></i>
    Users Management

</div>


<div class="top">


<a href="manage_user.php" class="btn add">

    <i class="fa-solid fa-user-plus"></i>
    Add User

</a>


<form method="get">

<input
type="text"
name="search"
class="search"
placeholder="Search Username..."
value="<?php echo $search;?>">

<input
type="submit"
value="Search">

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

<td>
    <?php echo $row['id']; ?>
</td>


<td>
    <?php echo $row['username']; ?>
</td>


<td>
    <?php echo $row['email']; ?>
</td>


<td>

<?php

if($row['status']=="Active"){

    echo "<span class='status-active'>
            <i class='fa-solid fa-circle-check'></i>
            Active
          </span>";

}else{

    echo "<span class='status-suspend'>
            <i class='fa-solid fa-circle-xmark'></i>
            Suspended
          </span>";

}

?>

</td>


<td>


<a
class="edit"
href="manage_user.php?id=<?php echo $row['id'];?>">

    <i class="fa-solid fa-pen-to-square"></i>
    Edit

</a>


&nbsp;&nbsp;


<?php

if($row['status']=="Active"){

?>


<a
class="suspend"
href="users.php?type=suspend&id=<?php echo $row['id'];?>"
onclick="return confirm('Are you sure you want to suspend this user for 20 days?');">

    <i class="fa-solid fa-ban"></i>
    Suspend

</a>


<?php

}else{

?>


<a
class="active"
href="users.php?type=active&id=<?php echo $row['id'];?>"
onclick="return confirm('Are you sure you want to activate this user?');">

    <i class="fa-solid fa-circle-check"></i>
    Activate

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

?>


<div class="no-data">

    <i class="fa-solid fa-folder-open"></i>

    <br><br>

    No User Found

</div>


<?php

}

?>


<?php
include('footer.php');
?>