
<?php
include('header.php');

$msg = "";

$username = "";
$email = "";

if(isset($_POST['register'])){

    $username = get_safe_value($_POST['username']);
    $email = get_safe_value($_POST['email']);

    $password = $_POST['password'];
    $cpassword = $_POST['confirm_password'];

    // Check password length.
    if(strlen($password) < 8){

        $msg = "Password must contain at least 8 characters.";

    }
    // Check uppercase letter.
    else if(!preg_match('/[A-Z]/', $password)){

        $msg = "Password must contain at least one uppercase letter.";

    }
    // Check lowercase letter.
    else if(!preg_match('/[a-z]/', $password)){

        $msg = "Password must contain at least one lowercase letter.";

    }
    // Check number.
    else if(!preg_match('/[0-9]/', $password)){

        $msg = "Password must contain at least one number.";

    }
    // Check special character.
    else if(!preg_match('/[^A-Za-z0-9\s]/', $password)){

        $msg = "Password must contain at least one special character.";

    }
    // Confirm password.
    else if($password !== $cpassword){

        $msg = "Password and Confirm Password do not match.";

    }else{

        // Check existing username.
        $stmt = mysqli_prepare(
            $con,
            "SELECT id FROM users WHERE username = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);
        $username_exists = mysqli_num_rows($res) > 0;

        mysqli_stmt_close($stmt);

        if($username_exists){

            $msg = "Username already exists.";

        }else{

            // Check existing email.
            $stmt = mysqli_prepare(
                $con,
                "SELECT id FROM users WHERE email = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $res = mysqli_stmt_get_result($stmt);
            $email_exists = mysqli_num_rows($res) > 0;

            mysqli_stmt_close($stmt);

            if($email_exists){

                $msg = "Email already exists.";

            }else{

                // Hash the password.
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Create new user account.
                $stmt = mysqli_prepare(
                    $con,
                    "INSERT INTO users
                    (username, email, password, role, status, suspend_until)
                    VALUES (?, ?, ?, 'User', 'active', NULL)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sss",
                    $username,
                    $email,
                    $hashed_password
                );

                if(mysqli_stmt_execute($stmt)){

                    mysqli_stmt_close($stmt);

                    redirect('index.php');
                    exit;

                }else{

                    $msg = "Registration failed. Please try again.";
                    mysqli_stmt_close($stmt);

                }
            }
        }
    }
}
?>

<style>

.register-box{
    width:520px;
    max-width:95%;
    margin:40px auto;
    background:#ffffff;
    border:1px solid #cccccc;
    border-radius:8px;
    padding:25px 30px;
    box-sizing:border-box;
}

.register-box h2{
    text-align:center;
    margin-bottom:20px;
    font-size:27px;
}

.register-box label{
    display:block;
    font-weight:bold;
    margin-top:15px;
    font-size:17px;
}

.register-box input[type=text],
.register-box input[type=email],
.register-box input[type=password],
.register-box input.password-input{
    width:100%;
    padding:13px;
    margin-top:7px;
    border:1px solid #cccccc;
    border-radius:6px;
    box-sizing:border-box;
    font-size:16px;
    outline:none;
}

.register-box input:focus{
    border-color:#0d6efd;
    box-shadow:0 0 0 3px rgba(13,110,253,0.10);
}

/* Password field and eye icon */

.password-wrapper{
    position:relative;
    width:100%;
}

.password-wrapper input{
    padding-right:48px !important;
}

.show-password{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-25%);
    width:28px;
    height:28px;
    padding:0;
    border:none;
    background:transparent;
    color:#666;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}

.show-password:hover{
    color:#0d6efd;
}

.show-password svg{
    width:22px;
    height:22px;
    fill:none;
    stroke:currentColor;
    stroke-width:1.8;
    stroke-linecap:round;
    stroke-linejoin:round;
}

/* Password requirements box */

.password-rules{
    background:#f5f7fa;
    padding:14px;
    margin-top:14px;
    border-radius:7px;
    font-size:14px;
    line-height:1.8;
    color:#555;
}

.password-rules strong{
    display:block;
    margin-bottom:8px;
    color:#222;
    font-size:15px;
}

.password-rules p{
    display:flex;
    align-items:center;
    gap:9px;
    margin:5px 0;
    padding:3px 7px;
    border-radius:5px;
    transition:0.2s;
}

/* Requirement icon */

.rule-icon{
    width:20px;
    height:20px;
    min-width:20px;
    border-radius:50%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    font-weight:bold;
    background:#ffe5e5;
    color:#dc3545;
}

/* Invalid requirement */

.password-rules p.invalid{
    color:#dc3545;
    background:#fff0f0;
}

.password-rules p.invalid .rule-icon{
    background:#dc3545;
    color:white;
}

/* Valid requirement */

.password-rules p.valid{
    color:#198754;
    background:#e7f7ed;
}

.password-rules p.valid .rule-icon{
    background:#198754;
    color:white;
}

/* Password strength */

.strength-area{
    margin-top:15px;
}

.strength-title{
    display:flex;
    justify-content:space-between;
    font-size:13px;
    font-weight:bold;
    margin-bottom:7px;
}

.strength-bar{
    width:100%;
    height:7px;
    background:#e5e7eb;
    border-radius:10px;
    overflow:hidden;
}

.strength-fill{
    width:0%;
    height:100%;
    border-radius:10px;
    transition:0.3s;
}

.strength-fill.weak{
    width:25%;
    background:#dc3545;
}

.strength-fill.medium{
    width:60%;
    background:#ffc107;
}

.strength-fill.strong{
    width:100%;
    background:#198754;
}

/* Confirm password status */

.match-message{
    display:none;
    font-size:13px;
    margin-top:7px;
    font-weight:bold;
}

.match-message.success{
    display:block;
    color:#198754;
}

.match-message.error{
    display:block;
    color:#dc3545;
}

/* Register button */

.btn{
    width:100%;
    padding:13px;
    margin-top:22px;
    background:#0d6efd;
    color:white;
    border:none;
    border-radius:7px;
    cursor:pointer;
    font-size:19px;
    transition:0.2s;
}

.btn:hover{
    background:#0954c6;
}

/* Error message */

.error{
    color:#dc3545;
    text-align:center;
    margin-top:15px;
    font-weight:bold;
}

/* Login link */

.login-link{
    text-align:center;
    margin-top:20px;
}

.login-link a{
    text-decoration:none;
    color:#0d6efd;
}

.login-link a:hover{
    text-decoration:underline;
}

@media(max-width:500px){

    .register-box{
        padding:20px;
        margin:20px auto;
    }

}

</style>

<div class="register-box">

    <h2>Create Account</h2>

    <form method="post" id="registerForm">

        <label>Username</label>

        <input
            type="text"
            name="username"
            required
            autocomplete="off"
            value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
        >

        <label>Gmail</label>

        <input
            type="email"
            name="email"
            required
            placeholder="example@gmail.com"
            autocomplete="off"
            value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
        >

        <label>Password</label>

        <div class="password-wrapper">

            <input
                type="password"
                name="password"
                id="password"
                class="password-input"
                minlength="8"
                required
                autocomplete="new-password"
            >

            <button
                type="button"
                class="show-password"
                onclick="togglePassword('password', 'passwordEye')"
                aria-label="Show password"
            >

                <svg id="passwordEye" viewBox="0 0 24 24">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>

            </button>

        </div>

        <label>Confirm Password</label>

        <div class="password-wrapper">

            <input
                type="password"
                name="confirm_password"
                id="confirm_password"
                class="password-input"
                minlength="8"
                required
                autocomplete="new-password"
            >

            <button
                type="button"
                class="show-password"
                onclick="togglePassword('confirm_password', 'confirmPasswordEye')"
                aria-label="Show password"
            >

                <svg id="confirmPasswordEye" viewBox="0 0 24 24">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>

            </button>

        </div>

        <div id="matchMessage" class="match-message"></div>

        <div class="password-rules">

            <strong>Password Requirements:</strong>

            <p id="rule-length" class="invalid">
                <span class="rule-icon">✕</span>
                At least 8 characters
            </p>

            <p id="rule-uppercase" class="invalid">
                <span class="rule-icon">✕</span>
                One uppercase letter (A-Z)
            </p>

            <p id="rule-lowercase" class="invalid">
                <span class="rule-icon">✕</span>
                One lowercase letter (a-z)
            </p>

            <p id="rule-number" class="invalid">
                <span class="rule-icon">✕</span>
                One number (0-9)
            </p>

            <p id="rule-special" class="invalid">
                <span class="rule-icon">✕</span>
                One special character (@, #, $ etc.)
            </p>

            <div class="strength-area">

                <div class="strength-title">

                    <span>Password Strength</span>

                    <span id="strengthText">Not entered</span>

                </div>

                <div class="strength-bar">

                    <div id="strengthFill" class="strength-fill"></div>

                </div>

            </div>

        </div>

        <input
            type="submit"
            name="register"
            value="Register"
            class="btn"
        >

    </form>

    <?php if($msg != ""){ ?>

        <div class="error">
            <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>

    <?php } ?>

    <div class="login-link">

        Already have an account?
        <a href="index.php">Login</a>

    </div>

</div>

<script>

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");

const matchMessage = document.getElementById("matchMessage");

const strengthText = document.getElementById("strengthText");
const strengthFill = document.getElementById("strengthFill");

// Toggle password visibility.
function togglePassword(inputId, iconId){

    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    const button = icon.closest("button");

    if(input.type === "password"){

        input.type = "text";

        // Closed eye icon.
        icon.innerHTML = `
            <path d="M3 3l18 18"/>
            <path d="M10.6 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16.5 16.5 0 0 1-3.1 3.9"/>
            <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.8-.8"/>
            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
        `;

        button.setAttribute("aria-label", "Hide password");

    }else{

        input.type = "password";

        // Open eye icon.
        icon.innerHTML = `
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
            <circle cx="12" cy="12" r="3"/>
        `;

        button.setAttribute("aria-label", "Show password");

    }

}

// Update requirement status.
function updateRule(ruleId, condition){

    const rule = document.getElementById(ruleId);
    const icon = rule.querySelector(".rule-icon");

    if(condition){

        rule.classList.remove("invalid");
        rule.classList.add("valid");

        icon.textContent = "✓";

    }else{

        rule.classList.remove("valid");
        rule.classList.add("invalid");

        icon.textContent = "✕";

    }

}

// Check password requirements.
function checkPassword(){

    const value = password.value;

    const length = value.length >= 8;
    const uppercase = /[A-Z]/.test(value);
    const lowercase = /[a-z]/.test(value);
    const number = /[0-9]/.test(value);
    const special = /[^A-Za-z0-9\s]/.test(value);

    updateRule("rule-length", length);
    updateRule("rule-uppercase", uppercase);
    updateRule("rule-lowercase", lowercase);
    updateRule("rule-number", number);
    updateRule("rule-special", special);

    // Calculate password strength.
    const score = [
        length,
        uppercase,
        lowercase,
        number,
        special
    ].filter(Boolean).length;

    strengthFill.className = "strength-fill";

    if(value.length === 0){

        strengthText.textContent = "Not entered";

    }
    else if(score <= 2){

        strengthText.textContent = "Weak";
        strengthFill.classList.add("weak");

    }
    else if(score <= 4){

        strengthText.textContent = "Medium";
        strengthFill.classList.add("medium");

    }
    else{

        strengthText.textContent = "Strong";
        strengthFill.classList.add("strong");

    }

    checkConfirmPassword();

    return score === 5;

}

// Check confirm password.
function checkConfirmPassword(){

    const p = password.value;
    const cp = confirmPassword.value;

    matchMessage.className = "match-message";

    if(cp.length === 0){

        matchMessage.textContent = "";

    }
    else if(p === cp){

        matchMessage.textContent = "✓ Passwords match";
        matchMessage.classList.add("success");

    }
    else{

        matchMessage.textContent = "✕ Passwords do not match";
        matchMessage.classList.add("error");

    }

}

// Live password checking.
password.addEventListener("input", checkPassword);

confirmPassword.addEventListener("input", checkConfirmPassword);

// Prevent invalid form submission.
document.getElementById("registerForm").addEventListener("submit", function(event){

    const validPassword = checkPassword();

    const passwordsMatch = password.value === confirmPassword.value;

    if(!validPassword || !passwordsMatch){

        event.preventDefault();

        if(!validPassword){

            alert("Please complete all password requirements.");

        }else{

            alert("Password and Confirm Password do not match.");

        }

    }

});

</script>

<?php
include('footer.php');
?>
