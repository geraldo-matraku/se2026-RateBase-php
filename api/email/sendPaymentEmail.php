<?php


require __DIR__ ."../../../vendor/autoload.php";
include __DIR__ . "/../config/mail.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;



function sendPaymentEmail(string $toEmail, string $toName, float $amount,string $currency,string $paymentId) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_FROM;
        $mail->Password = MAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = "RateBase Payment Confirmation";
        $mail->Body = "
            <h2>Payment Confirmation</h2>
            <p>Hello {$toName},</p>
            <p>Your payment was completed successfully.</p>
            <p><strong>Amount:</strong> {$amount} {$currency}</p>
            <br>
            <p>Thank you,<br>RateBase Team</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}