<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/app.php';

function sendMail(string $to, string $subject, string $html): bool
{
    error_log("MAIL: Attempting to send email to $to with subject: $subject");
    error_log("MAIL: SMTP Host: " . MAIL_HOST);
    error_log("MAIL: SMTP Port: " . MAIL_PORT);
    error_log("MAIL: SMTP User: " . MAIL_USER);
    
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);

        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = strip_tags(preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html));

        error_log("MAIL: Connecting to SMTP server...");
        $mail->send();
        error_log("MAIL: Email sent successfully to $to");
        return true;

    } catch (Exception $e) {
        $errorMsg = "Mailer error: {$mail->ErrorInfo} | Exception: {$e->getMessage()}";
        error_log($errorMsg);
        return false;
    }
}
