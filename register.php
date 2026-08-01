<?php
include('header.php');

$msg="";

$username="";
$email="";

if(isset($_POST['register'])){

    $username=get_safe_value($_POST['username']);
    $email=get_safe_value($_POST['email']);
    $password=get_safe_value($_POST['password']);
    $cpassword=get_safe_value($_POST['confirm_password']);

    if($password!=$cpassword){

        $msg="Password and Confirm Password do not match";

    }else{

        $res=mysqli_query($con,"select * from users where username='$username'");

        if(mysqli_num_rows($res)>0){

            $msg="Username already exists";

        }else{

            $res=mysqli_query($con,"select * from users where email='$email'");

            if(mysqli_num_rows($res)>0){

                $msg="Email already exists";

            }else{

                $password=password_hash($password,PASSWORD_DEFAULT);

                mysqli_query($con,"insert into users
                (username,email,password,role,status,suspend_until)
                values
                ('$username','$email','$password','User','Active',NULL)");

                redirect('index.php');

            }

        }

    }

}
?>
<style>

.register-box{
    width:420px;
    margin:40px auto;
    background:#ffffff;
    border:1px solid #cccccc;
    border-radius:8px;
    padding:25px;
}

.register-box h2{
    text-align:center;
    margin-bottom:20px;
}

.register-box label{
    display:block;
    font-weight:bold;
    margin-top:10px;
}

.register-box input{
    width:100%;
    padding:10px;
    margin-top:5px;
    border:1px solid #cccccc;
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

.error{
    color:red;
    text-align:center;
    margin-top:15px;
    font-weight:bold;
}

.login-link{
    text-align:center;
    margin-top:20px;
}

.login-link a{
    text-decoration:none;
    color:#0d6efd;
}

</style>

<div class="register-box">

<h2>Create Account</h2>

<form method="post">

<label>Username</label>
<input type="text"
name="username"
required
value="<?php echo $username; ?>">

<label>Gmail</label>
<input type="email"
name="email"
required
placeholder="example@gmail.com"
value="<?php echo $email; ?>">

<label>Password</label>
<input type="password"
name="password"
id="password"
required>

<label>Confirm Password</label>
<input type="password"
name="confirm_password"
id="confirm_password"
required>

<label style="margin-top:10px;font-weight:normal;">
<input type="checkbox"
onclick="showPassword()">
 Show Password
</label>

<input
type="submit"
name="register"
value="Register"
class="btn">

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

<div class="login-link">
Already have an account?
<a href="index.php">Login</a>
</div>

</div>

<script>

function showPassword(){

var p=document.getElementById("password");
var cp=document.getElementById("confirm_password");

if(p.type=="password"){
    p.type="text";
    cp.type="text";
}else{
    p.type="password";
    cp.type="password";
}

}

</script>

<?php
include('footer.php');
?>