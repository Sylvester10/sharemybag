<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true);

        //Server settings
        //$mail->SMTPDebug = 2;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Mailer = "smtp";
        $mail->Host       = 'mail.sharemybag.uk';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'support@sharemybag.uk';                     // SMTP username
        $mail->Password   = 'SMB-Support2122';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $mail->Hostname = 'localhost.localdomain';
        $mail->SMTPSecure = 'ssl';
        $mail->addReplyTo('support@sharemybag.uk', 'no-reply');


         //Recipients
        $mail->setFrom('support@sharemybag.uk', 'SMB-Support');
        



?>