<?php
include('header.php');
checkUser();
adminArea();

$msg="";
$username="";
$email="";
$role="User";
$status="Active";
$label="Add";

if(isset($_GET['id']) && $_GET['id']>0){

    $label="Edit";

    $id=get_safe_value($_GET['id']);

    $res=mysqli_query($con,"select * from users where id='$id'");

    if(mysqli_num_rows($res)==0){
        redirect('users.php');
        die();
    }

    $row=mysqli_fetch_assoc($res);

    $username=$row['username'];
    $email=$row['email'];
    $role=$row['role'];
    $status=$row['status'];

}

if(isset($_POST['submit'])){

    $username=get_safe_value($_POST['username']);
    $email=get_safe_value($_POST['email']);
    $password=get_safe_value($_POST['password']);
    $role=get_safe_value($_POST['role']);
    $status=get_safe_value($_POST['status']);

    $sub_sql="";

    if(isset($_GET['id']) && $_GET['id']>0){
        $sub_sql=" and id!=$id";
    }

    $check=mysqli_query($con,"select * from users where username='$username' $sub_sql");

    if(mysqli_num_rows($check)>0){

        $msg="Username already exists";

    }else{

        $check=mysqli_query($con,"select * from users where email='$email' $sub_sql");

        if(mysqli_num_rows($check)>0){

            $msg="Email already exists";

        }else{

            if(isset($_GET['id']) && $_GET['id']>0){

                if($password!=""){

                    $password=password_hash($password,PASSWORD_DEFAULT);

                    if($status=="Suspended"){

                        $date=date('Y-m-d',strtotime('+20 days'));

                        mysqli_query($con,"update users set
                        username='$username',
                        email='$email',
                        password='$password',
                        role='$role',
                        status='$status',
                        suspend_until='$date'
                        where id='$id'");

                    }else{

                        mysqli_query($con,"update users set
                        username='$username',
                        email='$email',
                        password='$password',
                        role='$role',
                        status='$status',
                        suspend_until=NULL
                        where id='$id'");

                    }

                }else{

                    if($status=="Suspended"){

                        $date=date('Y-m-d',strtotime('+20 days'));

                        mysqli_query($con,"update users set
                        username='$username',
                        email='$email',
                        role='$role',
                        status='$status',
                        suspend_until='$date'
                        where id='$id'");

                    }else{

                        mysqli_query($con,"update users set
                        username='$username',
                        email='$email',
                        role='$role',
                        status='$status',
                        suspend_until=NULL
                        where id='$id'");

                    }

                }

            }else{

                $password=password_hash($password,PASSWORD_DEFAULT);

if($status=="Suspended"){

    $date=date('Y-m-d',strtotime('+20 days'));

    mysqli_query($con,"INSERT INTO users
    (username,email,password,role,status,suspend_until)
    VALUES
    ('$username','$email','$password','$role','$status','$date')");

}else{

    mysqli_query($con,"INSERT INTO users
    (username,email,password,role,status,suspend_until)
    VALUES
    ('$username','$email','$password','$role','Active',NULL)");

}
            }

            redirect('users.php');

        }

    }

}

include('user_header.php');
?>
<style>

.form-box{
    width:450px;
    margin:30px auto;
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:20px;
}

.form-box h2{
    text-align:center;
    margin-bottom:20px;
}

.form-box label{
    display:block;
    margin-top:10px;
    font-weight:bold;
}

.form-box input,
.form-box select{
    width:100%;
    padding:10px;
    margin-top:5px;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

.btn{
    width:100%;
    padding:10px;
    margin-top:20px;
    background:#0d6efd;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:16px;
}

.btn:hover{
    background:#0954c6;
}

.back{
    text-decoration:none;
}

.error{
    color:red;
    text-align:center;
    margin-top:10px;
    font-weight:bold;
}

</style>

<div class="form-box">

<h2><?php echo $label;?> User</h2>

<a class="back" href="users.php">← Back</a>

<form method="post">

<label>Username</label>
<input
type="text"
name="username"
required
value="<?php echo $username;?>">

<label>Gmail</label>
<input
type="email"
name="email"
required
value="<?php echo $email;?>">

<label>Password</label>
<input
type="password"
name="password"
id="password"
<?php if($label=="Add"){ echo "required"; } ?>>

<label style="margin-top:8px;font-weight:normal;">
<input
type="checkbox"
onclick="showPassword()">
 Show Password
</label>

<label>Role</label>

<select name="role">

<option value="User"
<?php if($role=="User") echo "selected"; ?>>
User
</option>

<option value="Admin"
<?php if($role=="Admin") echo "selected"; ?>>
Admin
</option>

</select>

<label>Status</label>

<select name="status">

<option value="Active"
<?php if($status=="Active") echo "selected"; ?>>
Active
</option>

<option value="Suspended"
<?php if($status=="Suspended") echo "selected"; ?>>
Suspended
</option>

</select>

<input
type="submit"
name="submit"
value="<?php echo $label;?> User"
class="btn">

</form>

<?php
if($msg!=""){
?>
<div class="error">
<?php echo $msg;?>
</div>
<?php
}
?>

</div>

<script>

function showPassword(){

var x=document.getElementById("password");

if(x.type=="password"){
    x.type="text";
}else{
    x.type="password";
}

}

</script>

<?php
include('footer.php');
?>