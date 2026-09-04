<?php
include('header.php');
checkUser();
adminArea();

$msg="";

$username="";
$email="";
$password="";
$role="User";
$status="active";
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
    $confirm_password=get_safe_value($_POST['confirm_password']);
    $role=get_safe_value($_POST['role']);
    $status=get_safe_value($_POST['status']);


    if($label=="Add" && $password!=$confirm_password){

        $msg="Password and Confirm Password do not match";

    }else{

        $sub_sql="";

        if(isset($_GET['id']) && $_GET['id']>0){
            $sub_sql=" and id!=$id";
        }


        $check=mysqli_query($con,
            "select * from users
             where username='$username' $sub_sql"
        );

        if(mysqli_num_rows($check)>0){

            $msg="Username already exists";

        }else{

            $check=mysqli_query($con,
                "select * from users
                 where email='$email' $sub_sql"
            );

            if(mysqli_num_rows($check)>0){

                $msg="Email already exists";

            }else{


                /* EDIT USER */

                if(isset($_GET['id']) && $_GET['id']>0){

                    if($password!=""){

                        $password=password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        mysqli_query($con,"update users set
                            username='$username',
                            email='$email',
                            password='$password',
                            role='$role',
                            status='$status'
                            where id='$id'
                        ");

                    }else{

                        mysqli_query($con,"update users set
                            username='$username',
                            email='$email',
                            role='$role',
                            status='$status'
                            where id='$id'
                        ");
                    }


                }else{


                    /* ADD USER */

                    if($password==""){

                        $msg="Password is required";

                    }else{

                        $password=password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        mysqli_query($con,"insert into users
                            (username,email,password,role,status,suspend_until)
                            values
                            ('$username','$email','$password',
                             '$role','$status',NULL)
                        ");

                        redirect('users.php');
                    }
                }


                if($msg==""){
                    redirect('users.php');
                }
            }
        }
    }
}


include('user_header.php');
?>


<style>

.register-box{
    width:500px;
    max-width:95%;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.register-box h2{
    text-align:center;
    margin-bottom:20px;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-size:18px;
    font-weight:bold;
}

.form-group input,
.form-group select{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:7px;
    box-sizing:border-box;
    font-size:17px;
}

.btn{
    width:100%;
    padding:12px;
    margin-top:10px;
    background:#0d6efd;
    color:white;
    border:none;
    border-radius:7px;
    cursor:pointer;
    font-size:17px;
}

.btn:hover{
    background:#0b5ed7;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
}

.error{
    color:red;
    text-align:center;
    margin-top:15px;
    font-weight:bold;
}

.show-password{
    font-weight:normal !important;
    font-size:15px !important;
}

</style>


<div class="register-box">


<h2>
    👤 <?php echo $label; ?> User
</h2>


<a class="back" href="users.php">
    ← Back
</a>


<form method="post">


<div class="form-group">

<label>Username</label>

<input
    type="text"
    name="username"
    required
    value="<?php echo $username; ?>"
>

</div>


<div class="form-group">

<label>Gmail</label>

<input
    type="email"
    name="email"
    required
    placeholder="example@gmail.com"
    value="<?php echo $email; ?>"
>

</div>


<div class="form-group">

<label>Password</label>

<input
    type="password"
    name="password"
    id="password"
    <?php
    if($label=="Add"){
        echo "required";
    }
    ?>
>

</div>


<?php if($label=="Add"){ ?>

<div class="form-group">

<label>Confirm Password</label>

<input
    type="password"
    name="confirm_password"
    id="confirm_password"
    required
>

</div>

<?php } ?>


<div class="form-group">

<label class="show-password">

<input
    type="checkbox"
    onclick="showPassword()"
>

 Show Password

</label>

</div>


<div class="form-group">

<label>Role</label>

<select name="role">

<option value="User"
<?php
if($role=="User"){
    echo "selected";
}
?>
>
User
</option>

<option value="Admin"
<?php
if($role=="Admin"){
    echo "selected";
}
?>
>
Admin
</option>

</select>

</div>


<div class="form-group">

<label>Status</label>

<select name="status">

<option value="active"
<?php
if($status=="active"){
    echo "selected";
}
?>
>
active
</option>

<option value="suspended"
<?php
if($status=="suspended"){
    echo "selected";
}
?>
>
suspended
</option>

</select>

</div>


<input
    type="submit"
    name="submit"
    value="<?php echo $label; ?> User"
    class="btn"
>


</form>


<?php

if($msg!=""){

?>

<div class="error">
    <?php echo $msg; ?>
</div>

<?php

}

?>


</div>


<script>

function showPassword(){

    var password=document.getElementById("password");

    var confirmPassword=document.getElementById("confirm_password");

    if(password.type=="password"){

        password.type="text";

        if(confirmPassword){
            confirmPassword.type="text";
        }

    }else{

        password.type="password";

        if(confirmPassword){
            confirmPassword.type="password";
        }

    }

}

</script>


<?php
include('footer.php');
?>