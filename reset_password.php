
<?php
include('header.php');

$msg = "";
$success = false;

// Check OTP verification status and expiry.
if (
    !isset($_SESSION['reset_otp_verified']) ||
    $_SESSION['reset_otp_verified'] !== true ||
    !isset($_SESSION['reset_otp_email']) ||
    !isset($_SESSION['reset_otp_expiry'])
) {
    redirect('forgot_password.php');
    exit;
}

// Check OTP expiry.
if (time() >= $_SESSION['reset_otp_expiry']) {

    unset(
        $_SESSION['reset_otp_hash'],
        $_SESSION['reset_otp_email'],
        $_SESSION['reset_otp_expiry'],
        $_SESSION['reset_otp_verified'],
        $_SESSION['reset_otp_attempts']
    );

    redirect('forgot_password.php');
    exit;
}

// Handle password reset.
if (isset($_POST['reset'])) {

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check OTP expiry again.
    if (time() >= $_SESSION['reset_otp_expiry']) {

        unset(
            $_SESSION['reset_otp_hash'],
            $_SESSION['reset_otp_email'],
            $_SESSION['reset_otp_expiry'],
            $_SESSION['reset_otp_verified'],
            $_SESSION['reset_otp_attempts']
        );

        redirect('forgot_password.php');
        exit;

    } elseif (strlen($password) < 8) {

        $msg = "Password must be at least 8 characters.";

    } elseif (!preg_match('/[A-Z]/', $password)) {

        $msg = "Password must contain one uppercase letter.";

    } elseif (!preg_match('/[a-z]/', $password)) {

        $msg = "Password must contain one lowercase letter.";

    } elseif (!preg_match('/[0-9]/', $password)) {

        $msg = "Password must contain one number.";

    } elseif (!preg_match('/[^a-zA-Z0-9\s]/', $password)) {

        $msg = "Password must contain one special character.";

    } elseif ($password !== $confirm_password) {

        $msg = "Password and Confirm Password do not match.";

    } else {

        $email = $_SESSION['reset_otp_email'];

        // Verify that the account still exists.
        $checkStmt = mysqli_prepare(
            $con,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        if ($checkStmt) {

            mysqli_stmt_bind_param($checkStmt, "s", $email);

            if (mysqli_stmt_execute($checkStmt)) {

                mysqli_stmt_store_result($checkStmt);

                if (mysqli_stmt_num_rows($checkStmt) > 0) {

                    mysqli_stmt_close($checkStmt);

                    // Hash the new password.
                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    if ($hashed_password !== false) {

                        // Update the account password.
                        $stmt = mysqli_prepare(
                            $con,
                            "UPDATE users SET password = ? WHERE email = ?"
                        );

                        if ($stmt) {

                            mysqli_stmt_bind_param(
                                $stmt,
                                "ss",
                                $hashed_password,
                                $email
                            );

                            if (mysqli_stmt_execute($stmt)) {

                                $success = true;

                                // Clear password reset session data.
                                unset(
                                    $_SESSION['reset_otp_hash'],
                                    $_SESSION['reset_otp_email'],
                                    $_SESSION['reset_otp_expiry'],
                                    $_SESSION['reset_otp_verified'],
                                    $_SESSION['reset_otp_attempts']
                                );

                            } else {

                                $msg = "Database error. Please try again.";

                            }

                            mysqli_stmt_close($stmt);

                        } else {

                            $msg = "Database connection error.";

                        }

                    } else {

                        $msg = "Password security error. Please try again.";

                    }

                } else {

                    mysqli_stmt_close($checkStmt);

                    $msg = "Account not found. Please request a new OTP.";

                }

            } else {

                mysqli_stmt_close($checkStmt);

                $msg = "Database error. Please try again.";

            }

        } else {

            $msg = "Database connection error.";

        }
    }
}
?>

<style>

/* Main reset container */

.reset-box {
    width: 450px;
    max-width: 95%;
    margin: 50px auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.10);
    box-sizing: border-box;
}

.reset-box h2 {
    text-align: center;
    margin-bottom: 12px;
    color: #1e293b;
}

.reset-box > p {
    text-align: center;
    color: #64748b;
    margin-bottom: 22px;
    line-height: 1.6;
}

.reset-box label {
    display: block;
    font-weight: 600;
    margin-bottom: 7px;
    margin-top: 16px;
    color: #334155;
}

/* Password input */

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding: 13px 45px 13px 13px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 15px;
    outline: none;
}

.password-wrapper input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.10);
}

.toggle-password {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    user-select: none;
}

/* Password requirements box */

.password-rules {
    margin-top: 12px;
    padding: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.password-rules strong {
    display: block;
    margin-bottom: 12px;
    color: #334155;
    font-size: 14px;
}

/* Requirement items */

.password-rules p {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 7px 9px;
    margin: 6px 0;
    border-radius: 6px;
    background: #fff1f2;
    color: #dc2626;
    font-size: 13px;
    text-align: left;
    line-height: 1.4;
    transition: 0.3s;
}

/* Red cross circle */

.password-rules p span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 19px;
    height: 19px;
    min-width: 19px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    font-size: 12px;
    font-weight: bold;
}

/* Green completed requirement */

.password-rules p.valid {
    background: #f0fdf4;
    color: #15803d;
}

/* Green tick circle */

.password-rules p.valid span {
    background: #16a34a;
    color: #ffffff;
}

/* Password strength bar */

.password-strength {
    height: 7px;
    width: 100%;
    background: #e2e8f0;
    border-radius: 10px;
    margin-top: 13px;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0%;
    background: #dc2626;
    transition: width 0.3s, background 0.3s;
}

.strength-text {
    font-size: 13px;
    font-weight: 600;
    margin-top: 8px;
    color: #64748b;
}

/* Confirm password message */

.password-match {
    font-size: 13px;
    margin-top: 9px;
    font-weight: 600;
}

/* Submit button */

.reset-btn {
    width: 100%;
    margin-top: 23px;
    padding: 14px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.reset-btn:hover {
    background: #1d4ed8;
}

/* Message styles */

.msg {
    margin-top: 15px;
    text-align: center;
    color: #dc2626;
    font-weight: 600;
    line-height: 1.6;
}

.success {
    text-align: center;
    color: #15803d;
    font-weight: 600;
    margin-top: 20px;
    line-height: 1.7;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
}

.back:hover {
    text-decoration: underline;
}

/* Mobile responsive */

@media (max-width: 500px) {

    .reset-box {
        margin: 25px auto;
        padding: 22px 18px;
    }

}

</style>

<div class="reset-box">

    <?php if ($success) { ?>

        <h2>✅ Password Changed</h2>

        <div class="success">
            Your password has been changed successfully.
            <br><br>
            You can now login using your new password.
        </div>

        <a href="index.php" class="back">
            Go to Login
        </a>

    <?php } else { ?>

        <h2>🔐 Create New Password</h2>

        <p>
            Create a strong password to secure your account.
        </p>

        <form method="post" id="resetForm">

            <label>New Password</label>

            <div class="password-wrapper">

                <input
                    type="password"
                    name="password"
                    id="password"
                    minlength="8"
                    required
                    placeholder="Enter new password"
                    autocomplete="new-password"
                >

                <span
                    class="toggle-password"
                    onclick="togglePassword('password', this)"
                    title="Show Password"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>

            </div>

            <!-- Password requirements -->

            <div class="password-rules">

                <strong>Password Requirements</strong>

                <p id="lengthRule">
                    <span>✗</span>
                    At least 8 characters
                </p>

                <p id="upperRule">
                    <span>✗</span>
                    One uppercase letter (A-Z)
                </p>

                <p id="lowerRule">
                    <span>✗</span>
                    One lowercase letter (a-z)
                </p>

                <p id="numberRule">
                    <span>✗</span>
                    One number (0-9)
                </p>

                <p id="specialRule">
                    <span>✗</span>
                    One special character (@#$!)
                </p>

                <div class="password-strength">
                    <div
                        class="strength-bar"
                        id="strengthBar"
                    ></div>
                </div>

                <div class="strength-text" id="strengthText">
                    Password Strength: Not Started
                </div>

            </div>

            <label>Confirm Password</label>

            <div class="password-wrapper">

                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    minlength="8"
                    required
                    placeholder="Confirm new password"
                    autocomplete="new-password"
                >

                <span
                    class="toggle-password"
                    onclick="togglePassword('confirm_password', this)"
                    title="Show Password"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>

            </div>

            <div class="password-match" id="passwordMatch"></div>

            <input
                type="submit"
                name="reset"
                value="Change Password"
                class="reset-btn"
                id="resetButton"
            >

        </form>

        <?php if ($msg != "") { ?>

            <div class="msg">
                <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
            </div>

        <?php } ?>

    <?php } ?>

</div>

<script>

// Show or hide password.
function togglePassword(inputId, icon) {

    var passwordInput = document.getElementById(inputId);

    if (passwordInput.type === "password") {

        passwordInput.type = "text";

        icon.innerHTML = `
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3l18 18"/>
                <path d="M10.6 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16.5 16.5 0 0 1-3.1 3.9"/>
                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.8-.8"/>
                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
            </svg>
        `;

        icon.title = "Hide Password";

    } else {

        passwordInput.type = "password";

        icon.innerHTML = `
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        `;

        icon.title = "Show Password";

    }
}

// Update password requirement indicator.
function updateRule(id, valid) {

    var rule = document.getElementById(id);

    rule.classList.toggle("valid", valid);

    rule.querySelector("span").textContent = valid ? "✓" : "✗";
}

// Check password strength.
function checkPasswordStrength() {

    var password = document.getElementById("password").value;

    var rules = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^a-zA-Z0-9\s]/.test(password)
    };

    updateRule("lengthRule", rules.length);
    updateRule("upperRule", rules.upper);
    updateRule("lowerRule", rules.lower);
    updateRule("numberRule", rules.number);
    updateRule("specialRule", rules.special);

    // Calculate completed requirements.
    var score = 0;

    for (var key in rules) {

        if (rules[key]) {
            score++;
        }

    }

    var bar = document.getElementById("strengthBar");
    var text = document.getElementById("strengthText");

    bar.style.width = (score * 20) + "%";

    if (password === "") {

        text.textContent = "Password Strength: Not Started";
        text.style.color = "#64748b";
        bar.style.background = "#e2e8f0";

    } else if (score <= 2) {

        bar.style.background = "#dc2626";
        text.style.color = "#dc2626";
        text.textContent = "Password Strength: Weak";

    } else if (score <= 4) {

        bar.style.background = "#f59e0b";
        text.style.color = "#d97706";
        text.textContent = "Password Strength: Medium";

    } else {

        bar.style.background = "#16a34a";
        text.style.color = "#15803d";
        text.textContent = "Password Strength: Strong";

    }

    checkPasswordMatch();
}

// Check confirm password.
function checkPasswordMatch() {

    var password = document.getElementById("password").value;

    var confirmPassword = document.getElementById("confirm_password").value;

    var result = document.getElementById("passwordMatch");

    if (confirmPassword === "") {

        result.textContent = "";

    } else if (password === confirmPassword) {

        result.textContent = "✓ Passwords match";
        result.style.color = "#15803d";

    } else {

        result.textContent = "✗ Passwords do not match";
        result.style.color = "#dc2626";

    }
}

// Listen for password changes.
document.getElementById("password").addEventListener(
    "input",
    checkPasswordStrength
);

document.getElementById("confirm_password").addEventListener(
    "input",
    checkPasswordMatch
);

// Prevent submission if password requirements are not satisfied.
document.getElementById("resetForm").addEventListener(
    "submit",
    function(event) {

        var password = document.getElementById("password").value;

        var confirmPassword = document.getElementById("confirm_password").value;

        var valid =
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[a-z]/.test(password) &&
            /[0-9]/.test(password) &&
            /[^a-zA-Z0-9\s]/.test(password);

        if (!valid || password !== confirmPassword) {

            event.preventDefault();

            alert("Please complete all password requirements and confirm your password.");

        }

    }
);

</script>

<?php
include('footer.php');
?>
