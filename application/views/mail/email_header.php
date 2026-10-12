<?php require_once APPPATH . 'helpers/email_design_helper.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($email_title ?? 'Notification', ENT_QUOTES, 'UTF-8') ?></title>
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <!--<![endif]-->
    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
        }

        a,
        a:visited {
            text-decoration: none !important;
        }

        @media only screen and (max-width:480px) {
            .email-outer {
                padding: 0 8px 20px !important;
            }

            .email-content {
                padding: 28px 24px 30px !important;
            }

            .email-footer {
                padding: 24px 16px 0 !important;
            }

            .email-footer-link {
                display: inline-block !important;
                padding: 7px 5px !important;
            }

            .data-table td,
            .data-table th {
                font-size: 12px !important;
                padding: 10px 3px !important;
            }
            .payment-summary td {
                display:block !important;
                width:auto !important;
                text-align:left !important;
                padding:16px 20px 0 !important;
            }
            .payment-summary td:last-child {
                padding:4px 20px 16px !important;
            }
        }
    </style>
    <!--[if mso]><style>body,table,td,p,a,span,div {font-family:Arial,Helvetica,sans-serif !important;}</style><![endif]-->
</head>

<body style="margin:0;padding:0;background-color:#f4f2ef;<?= email_style() ?>">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#f4f2ef" style="width:100%;border-collapse:collapse;background-color:#f4f2ef;">
        <tr>
            <td class="email-outer" align="center" style="padding:12px 18px 28px;">
                <!--[if mso]><table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;border-collapse:collapse;">
                    <tr>
                        <td align="center" style="padding:28px 24px 30px;">
                            <img src="<?= production_url('assets/general/logo/colored_logo.png') ?>" alt="<?= htmlspecialchars(business, ENT_QUOTES, 'UTF-8') ?>" width="136" style="display:block;width:136px;max-width:100%;height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" bgcolor="#ffffff" style="padding:38px 48px 40px;background-color:#ffffff;border-radius:10px;text-align:left;<?= email_style('notification-message') ?>">
