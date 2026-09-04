<?php 
include('header.php'); 
 
 
if(isset($_POST['login'])){ 
 
    $username = get_safe_value($_POST['username']); 
    $email = get_safe_value($_POST['email']); 
    $password = get_safe_value($_POST['password']); 
 
 
    $res = mysqli_query($con, 
        "SELECT * FROM users 
         WHERE username='$username' 
         AND email='$email'" 
    ); 
 
 
    if(mysqli_num_rows($res) > 0){ 
 
        $row = mysqli_fetch_assoc($res); 
 
 
        /*
        =====================================
        CHECK SUSPENDED ACCOUNT
        =====================================
        */
 
        if(strtolower(trim($row['status'])) == "suspended"){ 
 
            $today = date('Y-m-d'); 
 
 
            /*
            =====================================
            SUSPENSION EXPIRED
            =====================================
            */
 
            if(
                !empty($row['suspend_until']) &&
                $today >= $row['suspend_until']
            ){ 
 
                mysqli_query($con, 
                    "UPDATE users 
                     SET status='Active', 
                         suspend_until=NULL 
                     WHERE id='".$row['id']."'" 
                ); 
 
                $row['status']="Active"; 
 
 
            }else{ 
 
                /*
                =====================================
                STILL SUSPENDED
                =====================================
                */
 
                $suspend_date = "";
 
                if(!empty($row['suspend_until'])){
 
                    $suspend_date = date(
                        'd-m-Y',
                        strtotime($row['suspend_until'])
                    );
 
                }
 
 
                echo " 
                <p style=' 
                    color:red; 
                    text-align:center; 
                    font-weight:bold; 
                    margin-top:20px; 
                '> 
                    Your account is suspended until "
                    .$suspend_date.
                    ".
                </p> 
                "; 
 
                include('footer.php'); 
                exit; 
 
            } 
 
        } 
 
 
        /*
        =====================================
        LOGIN ACCESS
        =====================================

        Active  = Login allowed
        Inactive = Login allowed
        Suspended = Login blocked
        =====================================
        */
 
 
        /*
        =====================================
        PASSWORD VERIFY
        =====================================
        */
 
        $verify = ( 
            $password == $row['password'] || 
            password_verify($password, $row['password']) 
        ); 
 
 
        if($verify == 1){ 
 
 
            /*
            =====================================
            UPDATE LAST LOGIN TIME
            =====================================
            */
 
            mysqli_query($con, 
                "UPDATE users 
                 SET last_login=NOW()
                 WHERE id='".$row['id']."'" 
            ); 
 
 
            /*
            =====================================
            SESSION
            =====================================
            */
 
            $_SESSION['UID'] = $row['id']; 
            $_SESSION['UNAME'] = $row['username']; 
            $_SESSION['UROLE'] = $row['role']; 
 
 
            /*
            =====================================
            REDIRECT
            =====================================
            */
 
            if($_SESSION['UROLE'] == 'User'){ 
 
                redirect('dashboard.php'); 
 
            }else{ 
 
                redirect('category.php'); 
 
            } 
 
 
        }else{ 
 
            echo " 
            <p style=' 
                color:red; 
                text-align:center; 
                font-weight:bold; 
                margin-top:15px; 
            '> 
                Please enter valid password 
            </p> 
            "; 
 
        } 
 
 
    }else{ 
 
        echo " 
        <p style=' 
            color:red; 
            text-align:center; 
            font-weight:bold; 
            margin-top:20px; 
        '> 
            Please enter valid username or email 
        </p> 
        "; 
 
    } 
 
} 
 
?> 
 
 
<style> 
 
/* ========================================= 
   MAIN LOGIN AREA 
   ========================================= */ 
 
.login-container{ 
    width:90%; 
    max-width:1100px; 
    margin:60px auto; 
    display:flex; 
    align-items:stretch; 
    gap:0; 
    background:#ffffff; 
    border-radius:12px; 
    overflow:hidden; 
    box-shadow:0 5px 25px rgba(0,0,0,0.12); 
} 
 
 
/* ========================================= 
   LEFT IMAGE 
   ========================================= */ 
 
.login-image{ 
    width:50%; 
    background:#a9d0f5; 
    display:flex; 
    align-items:center; 
    justify-content:center; 
    padding:25px; 
    box-sizing:border-box; 
} 
 
.login-image img{ 
    width:100%; 
    max-width:520px; 
    height:auto; 
    display:block; 
} 
 
 
/* ========================================= 
   RIGHT LOGIN SECTION 
   ========================================= */ 
 
.login-content{ 
    width:50%; 
    padding:45px 50px; 
    box-sizing:border-box; 
    background:#ffffff; 
} 
 
.login-content h2{ 
    text-align:center; 
    font-size:30px; 
    margin:0 0 30px 0; 
    color:#222; 
} 
 
 
/* ========================================= 
   FORM 
   ========================================= */ 
 
.login-content label{ 
    display:block; 
    font-weight:bold; 
    color:#333; 
    margin-bottom:7px; 
} 
 
.login-content input[type=text], 
.login-content input[type=email], 
.login-content input[type=password]{ 
    width:100%; 
    padding:13px; 
    margin-bottom:20px; 
    border:1px solid #d0d0d0; 
    border-radius:6px; 
    box-sizing:border-box; 
    font-size:14px; 
    outline:none; 
} 
 
.login-content input[type=text]:focus, 
.login-content input[type=email]:focus, 
.login-content input[type=password]:focus{ 
    border-color:#0d6efd; 
} 
 
 
/* ========================================= 
   SHOW PASSWORD 
   ========================================= */ 
 
.password-option{ 
    display:flex; 
    align-items:center; 
    gap:7px; 
    margin-top:-8px; 
    margin-bottom:22px; 
    font-size:13px; 
    color:#555; 
} 
 
.password-option input{ 
    width:14px; 
    height:14px; 
} 
 
 
/* ========================================= 
   LOGIN BUTTON 
   ========================================= */ 
 
.login-content input[type=submit]{ 
    width:100%; 
    padding:13px; 
    background:#0d6efd; 
    color:#ffffff; 
    border:none; 
    border-radius:6px; 
    font-size:16px; 
    font-weight:bold; 
    cursor:pointer; 
} 
 
.login-content input[type=submit]:hover{ 
    background:#0b5ed7; 
} 
 
 
/* ========================================= 
   LINKS 
   ========================================= */ 
 
.links{ 
    text-align:center; 
    margin-top:25px; 
} 
 
.links a{ 
    color:#0d6efd; 
    text-decoration:none; 
    font-size:14px; 
} 
 
.links a:hover{ 
    text-decoration:underline; 
} 
 
 
/* ========================================= 
   RESPONSIVE 
   ========================================= */ 
 
@media(max-width:800px){ 
 
    .login-container{ 
        width:92%; 
        margin:30px auto; 
        flex-direction:column; 
    } 
 
    .login-image{ 
        width:100%; 
        padding:20px; 
    } 
 
    .login-image img{ 
        max-width:450px; 
    } 
 
    .login-content{ 
        width:100%; 
        padding:35px 30px; 
    } 
 
} 
 
</style> 
 
 
<!-- ========================================= 
     LOGIN CONTAINER 
     ========================================= --> 
 
<div class="login-container"> 
 
 
    <!-- LEFT SIDE IMAGE --> 
 
    <div class="login-image"> 
 
        <img src="test.jpg" alt="Expense Management"> 
 
    </div> 
 
 
    <!-- RIGHT LOGIN SECTION --> 
 
    <div class="login-content"> 
 
        <h2>Login</h2> 
 
 
        <form method="post"> 
 
 
            <label>Username</label> 
 
            <input 
                type="text" 
                name="username" 
                required 
            > 
 
 
            <label>Gmail</label> 
 
            <input 
                type="email" 
                name="email" 
                placeholder="example@gmail.com" 
                required 
            > 
 
 
            <label>Password</label> 
 
            <input 
                type="password" 
                name="password" 
                id="password" 
                required 
            > 
 
 
            <div class="password-option"> 
 
                <input 
                    type="checkbox" 
                    onclick="showPassword()" 
                > 
 
                <span>Show Password</span> 
 
            </div> 
 
 
            <input 
                type="submit" 
                name="login" 
                value="Login" 
            > 
 
        </form> 
 
 
        <div class="links"> 
 
            <a href="register.php"> 
                Create New Account 
            </a> 
 
            <br><br> 
 
            <a href="forgot_password.php"> 
                Forgot Password? 
            </a> 
 
        </div> 
 
    </div> 
 
</div> 
 
 
<script> 
 
function showPassword(){ 
 
    var x = document.getElementById("password"); 
 
    if(x.type == "password"){ 
 
        x.type = "text"; 
 
    }else{ 
 
        x.type = "password"; 
 
    } 
 
} 
 
</script> 
 
 
<?php 
include('footer.php'); 
?>