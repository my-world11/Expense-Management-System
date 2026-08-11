<?php
include('header.php');

$msg = "";
$email = "";

if(isset($_POST['submit'])){

    $email = get_safe_value($_POST['email']);

    $res = mysqli_query($con,
        "select * from users where email='$email'"
    );

    if(mysqli_num_rows($res) == 0){

        $msg = "Email not found";

    }else{

        /*
        Temporary password reset system.
        User will be given a new temporary password.
        */

        $new_password = "12345678";

        $password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        mysqli_query($con,
            "update users set password='$password'
             where email='$email'"
        );

        $msg = "Password reset successful. Your new password is: 12345678";
    }
}
?>

<style>

.forgot-box{
    width:420px;
    max-width:95%;
    margin:50px auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.forgot-box h2{
    text-align:center;
    margin-bottom:25px;
}

.forgot-box label{
    display:block;
    font-weight:bold;
    margin-bottom:7px;
}

.forgot-box input[type=email]{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:7px;
    box-sizing:border-box;
    font-size:16px;
}

.forgot-btn{
    width:100%;
    margin-top:20px;
    padding:12px;
    background:#0d6efd;
    color:#fff;
    border:none;
    border-radius:7px;
    font-size:16px;
    cursor:pointer;
}

.forgot-btn:hover{
    background:#0b5ed7;
}

.msg{
    margin-top:15px;
    text-align:center;
    color:green;
    font-weight:bold;
}

.back{
    display:block;
    text-align:center;
    margin-top:20px;
}

</style>

<div class="forgot-box">

    <h2>🔐 Forgot Password</h2>

    <form method="post">

        <label>Gmail</label>

        <input
            type="email"
            name="email"
            required
            placeholder="Enter your registered Gmail"
            value="<?php echo $email; ?>"
        >

        <input
            type="submit"
            name="submit"
            value="Reset Password"
            class="forgot-btn"
        >

    </form>

    <?php
    if($msg!=""){
    ?>
        <div class="msg">
            <?php echo $msg; ?>
        </div>
    <?php
    }
    ?>

    <a href="index.php" class="back">
        ← Back to Login
    </a>

</div>

<?php
include('footer.php');
?>