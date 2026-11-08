<?php
include('header.php');
include('smtp_mailer.php');

$msg = "";
$email = "";

// Gmail SMTP settings.
// Enter your NEW Gmail App Password here.
$gmailAddress = "missbeastya@gmail.com";
$appPassword = "sefe ppvf pncu plwe";

if (isset($_POST['submit'])) {

    $email = strtolower(trim($_POST['email']));

    // Validate email address.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $msg = "Please enter a valid email address.";

    } elseif (strtolower(substr(strrchr($email, "@"), 1)) !== "gmail.com") {

        $msg = "Please enter a valid Gmail address.";

    } else {

        // Clear previous password reset session.
        unset(
            $_SESSION['reset_otp_hash'],
            $_SESSION['reset_otp_email'],
            $_SESSION['reset_otp_expiry'],
            $_SESSION['reset_otp_verified'],
            $_SESSION['reset_otp_attempts']
        );

        // Check whether email is registered.
        $stmt = mysqli_prepare(
            $con,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $email);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 0) {

                    $msg = "This email is not registered.";

                } else {

                    // Generate six-digit OTP.
                    $otp = (string) random_int(100000, 999999);

                    $errorMessage = "";

                    // Send OTP to registered Gmail.
                    $sent = sendOtpEmail(
                        $email,
                        $otp,
                        $gmailAddress,
                        $appPassword,
                        $errorMessage
                    );

                    if ($sent) {

                        // Regenerate session ID.
                        session_regenerate_id(true);

                        // Store hashed OTP.
                        $_SESSION['reset_otp_hash'] = password_hash(
                            $otp,
                            PASSWORD_DEFAULT
                        );

                        $_SESSION['reset_otp_email'] = $email;

                        // OTP expires after 50 seconds.
                        $_SESSION['reset_otp_expiry'] = time() + 50;

                        $_SESSION['reset_otp_verified'] = false;

                        $_SESSION['reset_otp_attempts'] = 0;

                        // Go to OTP verification page.
                        redirect('verify_otp.php');
                        exit;

                    } else {

                        $msg = "OTP could not be sent. Please check Gmail SMTP settings.";

                    }
                }

            } else {

                $msg = "Database error. Please try again.";

            }

            mysqli_stmt_close($stmt);

        } else {

            $msg = "Database connection error.";

        }
    }
}
?>

<style>

.forgot-box {
    width: 420px;
    max-width: 95%;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,.12);
    box-sizing: border-box;
}

.forgot-box h2 {
    text-align: center;
    margin-bottom: 12px;
    color: #222;
}

.forgot-box p {
    text-align: center;
    color: #666;
    margin-bottom: 22px;
    line-height: 1.6;
}

.forgot-box label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.forgot-box input[type=email] {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 16px;
    outline: none;
}

.forgot-box input[type=email]:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,.10);
}

.forgot-btn {
    width: 100%;
    margin-top: 20px;
    padding: 13px;
    background: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.forgot-btn:hover {
    background: #0b5ed7;
}

.msg {
    margin-top: 15px;
    text-align: center;
    color: #d93025;
    font-weight: bold;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #0d6efd;
    text-decoration: none;
}

.back:hover {
    text-decoration: underline;
}

</style>

<div class="forgot-box">

    <h2>🔐 Forgot Password</h2>

    <p>
        Enter your registered Gmail address.
        We will send you a password reset OTP.
    </p>

    <form method="post">

        <label>Registered Gmail</label>

        <input
            type="email"
            name="email"
            required
            placeholder="Enter your registered Gmail"
            value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
        >

        <input
            type="submit"
            name="submit"
            value="Send OTP"
            class="forgot-btn"
        >

    </form>

    <?php if ($msg != "") { ?>

        <div class="msg">
            <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>

    <?php } ?>

    <a href="index.php" class="back">
        ← Back to Login
    </a>

</div>

<?php
include('footer.php');
?>
