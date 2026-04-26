<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function getMailer()
{
    $mail = new PHPMailer(true);

    // Server settings — all values sourced from .env
    $mail->isSMTP();
    $mail->Mailer     = 'smtp';
    $mail->Host       = $_ENV['SMTP_HOST']   ?? 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER']   ?? '';
    $mail->Password   = $_ENV['SMTP_PASS']   ?? '';
    $mail->SMTPSecure = $_ENV['SMTP_CRYPTO'] ?? 'ssl';
    $mail->Port       = (int) ($_ENV['SMTP_PORT'] ?? 465);
    $mail->Hostname   = 'localhost.localdomain';
    $mail->addReplyTo($_ENV['SMTP_USER'] ?? '', 'no-reply');
    $mail->setFrom($_ENV['SMTP_USER'] ?? '', 'SMB-Support');

    return $mail;
}

