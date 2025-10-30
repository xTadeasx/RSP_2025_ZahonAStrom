<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//O TENTO SOUBOR MI NAPIŠTĚ A BUDE NA TEAMESCH

require_once __DIR__ . '/../hesla.php';

require __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $text)
{
    global $gmailPassword;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Password   =  $gmailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $mail->Port       = 587;

        $mail->setFrom('jahoda.tadeas@gmail.com', 'RSP Záhon a Strom');
        $mail->addAddress($to);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $text;

        $mail->send();
        echo "Email sent successfully!";
    } catch (Exception $e) {
        echo "Email sending failed. Error: {$mail->ErrorInfo}";
    }
}
