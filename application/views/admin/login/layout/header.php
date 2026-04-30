<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sandbox | <?php echo $title; ?> - <?php echo business_name; ?></title>

    <!-- Favicons-->
    <link rel="shortcut icon" href="<?php echo base_url('assets/general/logo/favicon.ico'); ?>" type="image/x-icon">

    <!-- CSS-->
    <link rel="stylesheet" type="text/css" href="assets/admin/login/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/admin/login/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="assets/admin/login/css/iofrm-style.css">
    <link rel="stylesheet" type="text/css" href="assets/admin/login/css/iofrm-theme12.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <link rel="stylesheet" type="text/css" href="assets/general/css/tw-output.css">
    <style>
        body,
        input,
        button,
        select,
        textarea {
            font-family: "Figtree", sans-serif !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .website-logo,
        .form-content h3,
        .form-content h4 {
            font-family: "Be Vietnam Pro", sans-serif !important;
        }

        .sandbox-login-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(243, 107, 36, 0.14);
            border: 1px solid rgba(243, 107, 36, 0.4);
            color: #f36b24;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            z-index: 10;
        }
    </style>
</head>

<body>
    <span class="sandbox-login-badge">
        <i class="las la-flask"></i> Sandbox
    </span>
