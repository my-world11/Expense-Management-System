
<?php

function smtpReadResponse($socket) {

    $response = '';

    while (($line = fgets($socket, 515)) !== false) {

        $response .= $line;

        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    return $response;
}


function smtpSendCommand($socket, $command, $expectedCode) {

    if (fwrite($socket, $command . "\r\n") === false) {
        throw new Exception("SMTP command failed.");
    }

    $response = smtpReadResponse($socket);

    if (substr($response, 0, 3) !== $expectedCode) {
        throw new Exception("SMTP Error: " . trim($response));
    }

    return $response;
}


function sendOtpEmail(
    $toEmail,
    $otp,
    $gmailAddress,
    $appPassword,
    &$errorMessage = '',
    $purpose = 'password_reset'
) {

    $socket = null;

    try {

        // Validate recipient and sender email.
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid recipient email.");
        }

        if (!filter_var($gmailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid sender email.");
        }

        // Validate six-digit OTP.
        if (!preg_match('/^[0-9]{6}$/', (string)$otp)) {
            throw new Exception("Invalid OTP.");
        }

        // Remove spaces from Gmail App Password.
        $appPassword = str_replace(' ', '', $appPassword);

        if ($appPassword === '') {
            throw new Exception("Gmail App Password is missing.");
        }

        // Connect to Gmail SMTP server.
        $socket = stream_socket_client(
            'tcp://smtp.gmail.com:587',
            $errno,
            $errstr,
            20
        );

        if (!$socket) {
            throw new Exception("SMTP connection failed: " . $errstr);
        }

        stream_set_timeout($socket, 20);

        // Read Gmail server response.
        $response = smtpReadResponse($socket);

        if (substr($response, 0, 3) !== '220') {
            throw new Exception("Gmail connection failed.");
        }

        // Start SMTP communication.
        smtpSendCommand($socket, 'EHLO localhost', '250');

        // Enable TLS encryption.
        smtpSendCommand($socket, 'STARTTLS', '220');

        $crypto = stream_socket_enable_crypto(
            $socket,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        );

        if ($crypto !== true) {
            throw new Exception("TLS encryption failed.");
        }

        // Authenticate with Gmail.
        smtpSendCommand($socket, 'EHLO localhost', '250');

        smtpSendCommand($socket, 'AUTH LOGIN', '334');

        smtpSendCommand(
            $socket,
            base64_encode($gmailAddress),
            '334'
        );

        smtpSendCommand(
            $socket,
            base64_encode($appPassword),
            '235'
        );

        // Set sender and recipient.
        smtpSendCommand(
            $socket,
            'MAIL FROM:<' . $gmailAddress . '>',
            '250'
        );

        smtpSendCommand(
            $socket,
            'RCPT TO:<' . $toEmail . '>',
            '250'
        );

        smtpSendCommand($socket, 'DATA', '354');


        // Select email subject and message.
        if ($purpose === 'change_email') {

            $subject = 'Expense Management - Change Gmail Verification';

            $body = "Hello,\r\n\r\n";
            $body .= "Your Gmail change verification OTP is: " . $otp . "\r\n\r\n";
            $body .= "This OTP is valid for 50 seconds only.\r\n\r\n";
            $body .= "If you did not request this change, please ignore this email.\r\n\r\n";
            $body .= "Thank you,\r\n";
            $body .= "Expense Management System";

        } else {

            $subject = 'Expense Management - Password Reset OTP';

            $body = "Hello,\r\n\r\n";
            $body .= "Your password reset OTP is: " . $otp . "\r\n\r\n";
            $body .= "This OTP is valid for 50 seconds only.\r\n\r\n";
            $body .= "If you did not request a password reset, ignore this email.\r\n\r\n";
            $body .= "Thank you,\r\n";
            $body .= "Expense Management System";
        }


        // Prepare email headers and content.
        $message = "From: Expense Management System <" . $gmailAddress . ">\r\n";
        $message .= "To: <" . $toEmail . ">\r\n";
        $message .= "Subject: " . $subject . "\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n";
        $message .= "\r\n";
        $message .= $body;


        // Normalize line endings.
        $message = str_replace(["\r\n", "\r"], "\n", $message);
        $message = str_replace("\n", "\r\n", $message);

        // Escape SMTP dot lines.
        $message = preg_replace('/^\./m', '..', $message);


        // Send email data.
        if (fwrite($socket, $message . "\r\n.\r\n") === false) {
            throw new Exception("Could not send email data.");
        }

        $response = smtpReadResponse($socket);

        if (substr($response, 0, 3) !== '250') {
            throw new Exception("Email sending failed: " . trim($response));
        }

        // Close SMTP connection.
        smtpSendCommand($socket, 'QUIT', '221');

        fclose($socket);

        return true;

    } catch (Exception $e) {

        $errorMessage = $e->getMessage();

        if (is_resource($socket)) {
            fclose($socket);
        }

        return false;
    }
}

?>
