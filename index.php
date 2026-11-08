
<?php
include('header.php');

if(isset($_POST['login'])){

    $username = get_safe_value($_POST['username']);
    $email = get_safe_value($_POST['email']);
    $password = get_safe_value($_POST['password']);

    $res = mysqli_query(
        $con,
        "SELECT * FROM users
         WHERE username='$username'
         AND email='$email'"
    );

    if(mysqli_num_rows($res) > 0){

        $row = mysqli_fetch_assoc($res);

        $verify = (
            $password == $row['password'] ||
            password_verify($password, $row['password'])
        );

        if($verify == 1){

            mysqli_query(
                $con,
                "UPDATE users
                 SET last_login=NOW()
                 WHERE id='".$row['id']."'"
            );

            $_SESSION['UID'] = $row['id'];
            $_SESSION['UNAME'] = $row['username'];
            $_SESSION['UROLE'] = $row['role'];

            if($_SESSION['UROLE'] == 'User'){
                redirect('dashboard.php');
            }else{
                redirect('category.php');
            }

        }else{
            echo "
            <p class='login-error'>
                Please enter valid password
            </p>
            ";
        }

    }else{
        echo "
        <p class='login-error'>
            Please enter valid username or email
        </p>
        ";
    }
}
?>

<link rel="stylesheet" href="index.css">

<div class="login-page">

    <div class="login-container">

        <!-- Left side: existing project photo -->
        <div class="login-image">

            <div class="image-title">
                <div class="brand-mark">₹</div>
                <div>
                    <h1>Expense Manager</h1>
                    <p>Manage your money smarter</p>
                </div>
            </div>

            <img src="test.jpg" alt="Expense Management">

            <div class="feature-row">

                <div class="feature-card">
                    <span class="feature-icon">↗</span>
                    <div>
                        <strong>Track Expenses</strong>
                        <small>Control daily spending</small>
                    </div>
                </div>

                <div class="feature-card">
                    <span class="feature-icon">₹</span>
                    <div>
                        <strong>Manage Income</strong>
                        <small>Keep income records</small>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right side: login -->
        <div class="login-content">

            <div class="login-heading-icon">♙</div>

            <h2>Welcome Back</h2>
            <p class="login-subtitle">Login to your account to continue</p>

            <form method="post" autocomplete="off">

                <div class="form-group">
                    <label>Username</label>

                    <div class="input-wrapper">
                        <span class="input-icon">♙</span>

                        <input
                            type="text"
                            name="username"
                            placeholder="Enter your username"
                            autocomplete="off"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>

                    <div class="input-wrapper">
                        <span class="input-icon">✉</span>

                        <input
                            type="email"
                            name="email"
                            placeholder="example@gmail.com"
                            autocomplete="off"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <div class="input-wrapper">
                        <span class="input-icon">●</span>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            id="togglePassword"
                            onclick="showPassword()"
                            aria-label="Show password"
                        >
                            <svg
                                id="eyeIcon"
                                xmlns="http://www.w3.org/2000/svg"
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="login-options">

                    <label class="remember">
                        <input type="checkbox">
                        <span>Remember me</span>
                    </label>

                    <a href="forgot_password.php" class="forgot-link">
                        🔐 Forgot Password?
                    </a>

                </div>

                <button type="submit" name="login" class="login-button">
                    <span>➜</span>
                    Login
                </button>

            </form>

            <div class="register-text">
                Don't have an account?
                <a href="register.php">Create New Account</a>
            </div>

            <div class="secure-area">

                <div class="secure-line">
                    <span></span>
                    <b>SECURE LOGIN</b>
                    <span></span>
                </div>

                <div class="secure-message">
                    <span class="secure-icon">✓</span>
                    <div>
                        <strong>Your information is protected</strong>
                        <small>Keep your account details private and secure.</small>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
function showPassword(){

    var password = document.getElementById("password");
    var eyeIcon = document.getElementById("eyeIcon");
    var button = document.getElementById("togglePassword");

    if(password.type === "password"){

        password.type = "text";

        // Change to eye close icon
        eyeIcon.innerHTML = `
            <path d="M3 3l18 18"/>
            <path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/>
            <path d="M9.88 5.09A10.94 10.94 0 0 1 12 4.9c5 0 9 4.1 10 7.1a11.8 11.8 0 0 1-4.2 5.3"/>
            <path d="M6.61 6.61A11.8 11.8 0 0 0 2 12c1 3 5 7.1 10 7.1 1.2 0 2.3-.2 3.3-.6"/>
        `;

        button.setAttribute("aria-label", "Hide password");

    }else{

        password.type = "password";

        // Change to eye open icon
        eyeIcon.innerHTML = `
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
            <circle cx="12" cy="12" r="3"/>
        `;

        button.setAttribute("aria-label", "Show password");
    }
}
</script>

<?php
include('footer.php');
?>
