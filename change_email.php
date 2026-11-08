
<?php
include('header.php');
include('smtp_mailer.php');

$msg = "";
$success = "";
$newEmail = "";

// Require login.
if (!isset($_SESSION['UID'])) {
    redirect('index.php');
    exit;
}

$userId = (int) $_SESSION['UID'];

// Clear old OTP data whenever Change Gmail page is opened fresh.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset(
        $_SESSION['change_email_otp_hash'],
        $_SESSION['change_email_new'],
        $_SESSION['change_email_expiry'],
        $_SESSION['change_email_attempts']
    );
}

// Gmail SMTP settings.
$gmailAddress = "missbeastya@gmail.com";
$appPassword = "sefe ppvf pncu plwe";


// Send or resend OTP.
if (isset($_POST['send_otp']) || isset($_POST['resend_otp'])) {

    $isResend = isset($_POST['resend_otp']);

    if ($isResend && isset($_SESSION['change_email_new'])) {
        $newEmail = $_SESSION['change_email_new'];
    } else {
        $newEmail = strtolower(trim($_POST['new_email'] ?? ''));
    }

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {

        $msg = "Please enter a valid email address.";

    } elseif (strtolower(substr(strrchr($newEmail, "@"), 1)) !== "gmail.com") {

        $msg = "Please enter a valid Gmail address.";

    } else {

        $stmt = mysqli_prepare(
            $con,
            "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1"
        );

        if (!$stmt) {

            $msg = "Database error. Please try again.";

        } else {

            mysqli_stmt_bind_param($stmt, "si", $newEmail, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {

                $msg = "This Gmail is already used by another account.";

            } else {

                $otp = (string) random_int(100000, 999999);
                $errorMessage = "";

                $sent = sendOtpEmail(
                    $newEmail,
                    $otp,
                    $gmailAddress,
                    $appPassword,
                    $errorMessage,
                    'change_email'
                );

                if ($sent) {

                    $_SESSION['change_email_otp_hash'] =
                        password_hash($otp, PASSWORD_DEFAULT);

                    $_SESSION['change_email_new'] = $newEmail;
                    $_SESSION['change_email_expiry'] = time() + 50;
                    $_SESSION['change_email_attempts'] = 0;

                    $success = $isResend
                        ? "A new OTP has been sent successfully."
                        : "OTP sent to your new Gmail address.";

                } else {

                    $msg = "OTP could not be sent. Please check Gmail SMTP settings.";
                }
            }

            mysqli_stmt_close($stmt);
        }
    }
}


// Verify OTP and update email.
if (isset($_POST['verify_otp'])) {

    $otp = trim($_POST['otp'] ?? '');

    if (
        !isset(
            $_SESSION['change_email_otp_hash'],
            $_SESSION['change_email_new'],
            $_SESSION['change_email_expiry']
        )
    ) {

        $msg = "Please request a new OTP.";

    } elseif (time() >= $_SESSION['change_email_expiry']) {

        unset(
            $_SESSION['change_email_otp_hash'],
            $_SESSION['change_email_expiry'],
            $_SESSION['change_email_attempts']
        );

        $msg = "OTP has expired. Please resend a new OTP.";

    } elseif (($_SESSION['change_email_attempts'] ?? 0) >= 5) {

        unset(
            $_SESSION['change_email_otp_hash'],
            $_SESSION['change_email_expiry'],
            $_SESSION['change_email_attempts']
        );

        $msg = "Too many incorrect attempts. Please resend OTP.";

    } elseif (!preg_match('/^[0-9]{6}$/', $otp)) {

        $msg = "Please enter a valid six-digit OTP.";

    } elseif (!password_verify($otp, $_SESSION['change_email_otp_hash'])) {

        $_SESSION['change_email_attempts']++;

        $msg = "Incorrect OTP. Please try again.";

    } else {

        $newEmail = $_SESSION['change_email_new'];

        $stmt = mysqli_prepare(
            $con,
            "UPDATE users
             SET email = ?
             WHERE id = ?
             AND NOT EXISTS (
                 SELECT id FROM (
                     SELECT id FROM users WHERE email = ?
                 ) AS existing_users
                 WHERE existing_users.id != ?
             )"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sisi",
                $newEmail,
                $userId,
                $newEmail,
                $userId
            );

            if (
                mysqli_stmt_execute($stmt) &&
                mysqli_stmt_affected_rows($stmt) === 1
            ) {

                $success = "Your Gmail address has been changed successfully.";

                unset(
                    $_SESSION['change_email_otp_hash'],
                    $_SESSION['change_email_new'],
                    $_SESSION['change_email_expiry'],
                    $_SESSION['change_email_attempts']
                );

                $newEmail = "";

            } else {

                $msg = "Gmail could not be updated. It may already be in use.";
            }

            mysqli_stmt_close($stmt);

        } else {

            $msg = "Database error. Please try again.";
        }
    }
}


// Cancel pending email change.
if (isset($_POST['cancel_change'])) {

    unset(
        $_SESSION['change_email_otp_hash'],
        $_SESSION['change_email_new'],
        $_SESSION['change_email_expiry'],
        $_SESSION['change_email_attempts']
    );

    $newEmail = "";
    $success = "Email change request cancelled.";
}


// Keep pending email visible.
if (isset($_SESSION['change_email_new'])) {
    $newEmail = $_SESSION['change_email_new'];
}

$otpPending = isset(
    $_SESSION['change_email_otp_hash'],
    $_SESSION['change_email_new'],
    $_SESSION['change_email_expiry']
);

$remainingSeconds = 0;

if ($otpPending) {
    $remainingSeconds = max(
        0,
        $_SESSION['change_email_expiry'] - time()
    );
}
?>

<style>
.change-email-box {
    width: 440px;
    max-width: 94%;
    margin: 45px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 5px 22px rgba(0, 0, 0, 0.10);
    box-sizing: border-box;
}

.change-email-box h2 {
    text-align: center;
    color: #222;
    margin-bottom: 12px;
}

.change-email-box p {
    text-align: center;
    color: #666;
    line-height: 1.6;
    margin-bottom: 24px;
}

.change-email-box label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.change-email-box input[type="email"],
.change-email-box input[type="text"] {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    box-sizing: border-box;
    outline: none;
    margin-bottom: 16px;
}

.change-email-box input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.10);
}

.change-email-btn {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 8px;
    background: #0d6efd;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.change-email-btn:hover {
    background: #0b5ed7;
}

.change-email-btn:disabled {
    background: #999;
    cursor: not-allowed;
}

.cancel-btn {
    margin-top: 10px;
    background: #6c757d;
}

.resend-btn {
    margin-top: 10px;
    background: #198754;
}

.resend-btn:hover {
    background: #157347;
}

.change-message {
    text-align: center;
    margin-top: 16px;
    font-weight: 600;
    color: #d93025;
}

.change-success {
    color: #198754;
}

.countdown {
    text-align: center;
    margin: 15px 0;
    font-size: 18px;
    font-weight: bold;
    color: #dc3545;
}

.expired-text {
    color: #dc3545;
}

.change-back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #0d6efd;
    text-decoration: none;
}

.change-back:hover {
    text-decoration: underline;
}
</style>

<div class="change-email-box">

    <h2>📧 Change Gmail</h2>

    <p>
        Enter your new Gmail address.
        We will send a verification OTP to that address.
    </p>

    <?php if (!$otpPending) { ?>

        <form method="post" autocomplete="off">

            <label>New Gmail Address</label>

            <input
                type="email"
                name="new_email"
                required
                autocomplete="off"
                placeholder="Enter your new Gmail"
                value="<?php echo htmlspecialchars($newEmail, ENT_QUOTES, 'UTF-8'); ?>"
            >

            <button type="submit" name="send_otp" class="change-email-btn">
                Send Verification OTP
            </button>

        </form>

    <?php } else { ?>

        <p>
            OTP sent to:
            <strong>
                <?php echo htmlspecialchars($newEmail, ENT_QUOTES, 'UTF-8'); ?>
            </strong>
        </p>

        <div class="countdown" id="countdown">
            Time remaining:
            <span id="timer"><?php echo $remainingSeconds; ?></span>
            seconds
        </div>

        <form method="post" id="verifyForm" autocomplete="off">

            <label>Enter 6-Digit OTP</label>

            <input
                type="text"
                name="otp"
                id="otpInput"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                autocomplete="one-time-code"
                required
                placeholder="Enter OTP"
            >

            <button
                type="submit"
                name="verify_otp"
                id="verifyButton"
                class="change-email-btn"
                <?php echo $remainingSeconds <= 0 ? 'disabled' : ''; ?>
            >
                Verify OTP and Change Gmail
            </button>

        </form>

        <form method="post" id="resendForm"
              style="<?php echo $remainingSeconds > 0 ? 'display:none;' : ''; ?>">

            <button type="submit" name="resend_otp"
                    class="change-email-btn resend-btn">
                Resend OTP
            </button>

        </form>

        <form method="post">

            <button type="submit" name="cancel_change"
                    class="change-email-btn cancel-btn">
                Cancel
            </button>

        </form>

    <?php } ?>

    <?php if ($msg != "") { ?>
        <div class="change-message">
            <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <?php if ($success != "") { ?>
        <div class="change-message change-success">
            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <a href="dashboard.php" class="change-back">
        ← Back to Dashboard
    </a>

</div>

<?php if ($otpPending) { ?>
<script>
(function () {

    let remaining = <?php echo (int)$remainingSeconds; ?>;

    const timer = document.getElementById('timer');
    const countdown = document.getElementById('countdown');
    const verifyButton = document.getElementById('verifyButton');
    const otpInput = document.getElementById('otpInput');
    const resendForm = document.getElementById('resendForm');

    function updateCountdown() {

        timer.textContent = remaining;

        if (remaining <= 0) {

            countdown.textContent =
                "OTP expired. Please resend a new OTP.";

            countdown.classList.add('expired-text');

            verifyButton.disabled = true;
            otpInput.disabled = true;

            resendForm.style.display = 'block';

            return;
        }

        remaining--;

        setTimeout(updateCountdown, 1000);
    }

    updateCountdown();

})();
</script>
<?php } ?>

<?php
include('footer.php');
?>
