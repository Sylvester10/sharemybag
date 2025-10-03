<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function getMailer()
{
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Mailer = "smtp";
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'customers@sharemybag.co.uk';
        $mail->Password   = 'Customers-SMB1';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->Hostname   = 'localhost.localdomain';
        $mail->addReplyTo('customers@sharemybag.co.uk', 'no-reply');
        $mail->setFrom('customers@sharemybag.co.uk', 'SMB-Support');

        return $mail;
}
