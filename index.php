<?php
include('header.php');

if(isset($_POST['login'])){

   $username=get_safe_value($_POST['username']);
   $email=get_safe_value($_POST['email']);
   $password=get_safe_value($_POST['password']);

   $res=mysqli_query($con,"SELECT * FROM users WHERE username='$username' AND email='$email'");

   if(mysqli_num_rows($res)>0){
      $row=mysqli_fetch_assoc($res);

      $verify=password_verify($password,$row['password']);

      if($verify==1){
         $_SESSION['UID']=$row['id'];
         $_SESSION['UNAME']=$row['username'];
         $_SESSION['UROLE']=$row['role'];

         if($_SESSION['UROLE']=='User'){
            redirect('dashboard.php');
         }else{
            redirect('category.php');
         }

      }else{
         echo "<p style='color:red;text-align:center;'>Please enter valid password</p>";
      }

   }else{
      echo "<p style='color:red;text-align:center;'>Please enter valid username or email</p>";
   }
}
?>

<style>
.login-box{
    width:380px;
    margin:60px auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 15px rgba(0,0,0,.15);
}
.login-box h2{
    text-align:center;
    margin-bottom:20px;
}
.login-box label{
    font-weight:bold;
}
.login-box input[type=text],
.login-box input[type=email],
.login-box input[type=password]{
    width:100%;
    padding:10px;
    margin:8px 0 15px;
    border:1px solid #ccc;
    border-radius:5px;
}
.login-box input[type=submit]{
    width:100%;
    background:#0d6efd;
    color:#fff;
    border:none;
    padding:10px;
    border-radius:5px;
    cursor:pointer;
}
.login-box input[type=submit]:hover{
    background:#0b5ed7;
}
.links{
    text-align:center;
    margin-top:15px;
}
.links a{
    text-decoration:none;
}
</style>

<div class="login-box">

<h2>Login</h2>

<form method="post">

<label>Username</label>
<input type="text" name="username" required>

<label>Gmail</label>
<input type="email" name="email" placeholder="example@gmail.com" required>

<label>Password</label>
<input type="password" name="password" id="password" required>

<input type="checkbox" onclick="showPassword()"> Show Password

<br><br>

<input type="submit" name="login" value="Login">

</form>

<div class="links">
<a href="register.php">Create New Account</a>
<br><br>
<a href="forgot_password.php">Forgot Password?</a>
</div>

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