
<?php
include('header.php');

$msg = "";
$otp = "";

// Check whether a valid OTP session exists.
$validOtpSession = (
    isset($_SESSION['reset_otp_hash']) &&
    isset($_SESSION['reset_otp_email']) &&
    isset($_SESSION['reset_otp_expiry']) &&
    isset($_SESSION['reset_otp_verified']) &&
    $_SESSION['reset_otp_verified'] === false
);

// Clear expired or invalid OTP session.
if (!$validOtpSession) {

    if (
        isset($_SESSION['reset_otp_verified']) &&
        $_SESSION['reset_otp_verified'] === true
    ) {
        redirect('reset_password.php');
        exit;
    }

    $msg = "No valid OTP request found. Please request a new OTP.";

} elseif (time() >= $_SESSION['reset_otp_expiry']) {

    unset(
        $_SESSION['reset_otp_hash'],
        $_SESSION['reset_otp_email'],
        $_SESSION['reset_otp_expiry'],
        $_SESSION['reset_otp_verified'],
        $_SESSION['reset_otp_attempts']
    );

    $validOtpSession = false;

    $msg = "OTP has expired. Please request a new OTP.";
}

// Handle OTP verification.
if (isset($_POST['verify'])) {

    // Recheck OTP session before verification.
    if (
        !isset($_SESSION['reset_otp_hash']) ||
        !isset($_SESSION['reset_otp_email']) ||
        !isset($_SESSION['reset_otp_expiry']) ||
        !isset($_SESSION['reset_otp_verified']) ||
        $_SESSION['reset_otp_verified'] !== false
    ) {

        $msg = "Invalid OTP session. Please request a new OTP.";

    } elseif (time() >= $_SESSION['reset_otp_expiry']) {

        unset(
            $_SESSION['reset_otp_hash'],
            $_SESSION['reset_otp_email'],
            $_SESSION['reset_otp_expiry'],
            $_SESSION['reset_otp_verified'],
            $_SESSION['reset_otp_attempts']
        );

        $validOtpSession = false;

        $msg = "OTP expired. Please request a new OTP.";

    } else {

        $otp = isset($_POST['otp'])
            ? trim($_POST['otp'])
            : "";

        // Validate six-digit OTP.
        if (!preg_match('/^[0-9]{6}$/', $otp)) {

            $msg = "Please enter a valid 6-digit OTP.";

        } else {

            if (!isset($_SESSION['reset_otp_attempts'])) {
                $_SESSION['reset_otp_attempts'] = 0;
            }

            // Maximum five attempts.
            if ($_SESSION['reset_otp_attempts'] >= 5) {

                unset(
                    $_SESSION['reset_otp_hash'],
                    $_SESSION['reset_otp_email'],
                    $_SESSION['reset_otp_expiry'],
                    $_SESSION['reset_otp_verified'],
                    $_SESSION['reset_otp_attempts']
                );

                $validOtpSession = false;

                $msg = "Too many incorrect attempts. Please request a new OTP.";

            } else {

                $_SESSION['reset_otp_attempts']++;

                // Verify entered OTP.
                if (password_verify(
                    $otp,
                    $_SESSION['reset_otp_hash']
                )) {

                    // Mark OTP as verified.
                    $_SESSION['reset_otp_verified'] = true;

                    // Regenerate session ID.
                    session_regenerate_id(true);

                    // Remove OTP hash and attempt counter.
                    // Keep expiry time for password reset validation.
                    unset(
                        $_SESSION['reset_otp_hash'],
                        $_SESSION['reset_otp_attempts']
                    );

                    redirect('reset_password.php');
                    exit;

                } else {

                    if ($_SESSION['reset_otp_attempts'] >= 5) {

                        unset(
                            $_SESSION['reset_otp_hash'],
                            $_SESSION['reset_otp_email'],
                            $_SESSION['reset_otp_expiry'],
                            $_SESSION['reset_otp_verified'],
                            $_SESSION['reset_otp_attempts']
                        );

                        $validOtpSession = false;

                        $msg = "Too many incorrect attempts. Please request a new OTP.";

                    } else {

                        $remainingAttempts = 5 - $_SESSION['reset_otp_attempts'];

                        $msg = "Incorrect OTP. " .
                            $remainingAttempts .
                            " attempts remaining.";

                    }
                }
            }
        }
    }
}

// Calculate remaining OTP time.
$remainingTime = 0;

if (
    $validOtpSession &&
    isset($_SESSION['reset_otp_expiry']) &&
    time() < $_SESSION['reset_otp_expiry']
) {

    $remainingTime = $_SESSION['reset_otp_expiry'] - time();

}
?>

<style>

.verify-box {
    width: 420px;
    max-width: 95%;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 5px 25px rgba(0,0,0,.10);
    box-sizing: border-box;
}

.verify-box h2 {
    text-align: center;
    margin-bottom: 15px;
    color: #1e293b;
}

.verify-box p {
    text-align: center;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 20px;
}

.verify-box input[type=text] {
    width: 100%;
    padding: 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 22px;
    text-align: center;
    letter-spacing: 8px;
    box-sizing: border-box;
    outline: none;
}

.verify-box input[type=text]:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.10);
}

.countdown {
    text-align: center;
    margin: 18px 0;
    font-weight: 600;
    color: #15803d;
    font-size: 17px;
}

.countdown.expired {
    color: #dc2626;
}

.verify-btn {
    width: 100%;
    margin-top: 15px;
    padding: 14px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.verify-btn:hover {
    background: #1d4ed8;
}

.verify-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
}

.msg {
    margin-top: 15px;
    text-align: center;
    color: #dc2626;
    font-weight: 600;
    line-height: 1.5;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #2563eb;
    text-decoration: none;
}

.back:hover {
    text-decoration: underline;
}

</style>

<div class="verify-box">

    <h2>🔐 Verify OTP</h2>

    <p>
        Enter the 6-digit OTP sent to your registered Gmail.
    </p>

    <?php if ($validOtpSession && $remainingTime > 0) { ?>

        <div class="countdown" id="countdown">
            OTP expires in: <span id="timer">
                <?php echo $remainingTime; ?>
            </span> seconds
        </div>

        <form method="post" id="otpForm">

            <input
                type="text"
                name="otp"
                maxlength="6"
                minlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                placeholder="000000"
                required
                autocomplete="one-time-code"
                autofocus
            >

            <input
                type="submit"
                name="verify"
                id="verifyButton"
                value="Verify OTP"
                class="verify-btn"
            >

        </form>

    <?php } ?>

    <?php if ($msg != "") { ?>

        <div class="msg">
            <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>

    <?php } ?>

    <a href="forgot_password.php" class="back">
        ← Request New OTP
    </a>

</div>

<?php if ($validOtpSession && $remainingTime > 0) { ?>

<script>

// OTP countdown timer.
var remainingSeconds = <?php echo (int)$remainingTime; ?>;

var timerElement = document.getElementById('timer');
var countdownElement = document.getElementById('countdown');
var verifyButton = document.getElementById('verifyButton');
var otpForm = document.getElementById('otpForm');

var countdownInterval = setInterval(function() {

    remainingSeconds--;

    if (remainingSeconds <= 0) {

        clearInterval(countdownInterval);

        timerElement.textContent = "0";

        countdownElement.textContent =
            "OTP Expired!";

        countdownElement.classList.add('expired');

        verifyButton.disabled = true;

        verifyButton.value = "OTP Expired";

        otpForm.querySelector('input[name="otp"]').disabled = true;

        return;
    }

    timerElement.textContent = remainingSeconds;

}, 1000);

</script>

<?php } ?>

<?php
include('footer.php');
?>
