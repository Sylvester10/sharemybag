<?php
  defined('BASEPATH') OR exit('No direct script access allowed');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Phpmailer_library{

    public function __construct()
    {
        log_message('Debug', 'PHPMailer class is loaded.');
    }

    public function load()
    {
        require_once(APPPATH."third_party/PHPMailer/src/Exception.php");
        require_once(APPPATH."third_party/PHPMailer/src/PHPMailer.php");
        require_once(APPPATH."third_party/PHPMailer/src/SMTP.php");
        require_once(APPPATH."third_party/email_config.php");
        
        $mail = new PHPMailer;
         //Server settings
        //$mail->SMTPDebug = 2;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Mailer = $config['protocol'];
        $mail->Host       = $config['smtp_host'];                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $config['smtp_user'];                     // SMTP username
        $mail->Password   = $config['smtp_pass'];                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $config['smtp_port'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        //$mail->Hostname = 'localhost.localdomain';
        $mail->SMTPSecure = $config['smtp_crypto'];
        
        $mail->isHTML(true); 

        $mail->setFrom($config['smtp_user'], $config['org_name']);

        return $mail;
    }


}
































?>