<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $email_title ?? 'Notification' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        /* ── Reset ─────────────────────────────────────────── */
        * {
            box-sizing: border-box;
        }

        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* ── Base ──────────────────────────────────────────── */
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f0ed;
            font-family: "Be Vietnam Pro", sans-serif;
            color: #4b5563;
            line-height: 1.7;
        }

        /* ── Layout ────────────────────────────────────────── */
        .wrapper {
            width: 100%;
            background-color: #f0f0ed;
            padding: 48px 20px;
        }

        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
        }

        .top-bar {
            height: 5px;
            background: linear-gradient(90deg, #f36b24 0%, #f9a472 100%);
        }

        .header {
            text-align: center;
            padding: 32px 24px 28px;
            border-bottom: 1px solid #f3f4f6;
        }

        .header img {
            width: 130px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .content {
            padding: 44px 40px;
        }

        /* ── Typography ────────────────────────────────────── */
        p {
            margin: 0 0 16px;
            font-size: 14px;
            color: #4b5563;
        }

        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 12px;
        }

        .section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #9ca3af;
            margin: 28px 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f3f4f6;
        }

        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 32px 0;
        }

        .sign-off {
            font-size: 14px;
            color: #4b5563;
            margin: 0;
        }

        .sign-off strong {
            color: #111827;
        }

        .text-link {
            color: #f36b24;
            text-decoration: none;
            font-weight: 600;
        }

        /* ── Buttons ───────────────────────────────────────── */
        .btn-wrap {
            text-align: center;
            margin: 32px 0;
        }

        .btn,
        .btn-wrap a {
            display: inline-block !important;
            background-color: #f36b24 !important;
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 14px 36px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            letter-spacing: 0.02em !important;
        }

        /* ── Alert badge (admin emails) ────────────────────── */
        .alert-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff8f4;
            border: 1px solid #fed7b8;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #c2410c;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .alert-dot {
            width: 7px;
            height: 7px;
            background: #f36b24;
            border-radius: 50%;
        }

        /* ── Info / data tables ────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .info-table tr td {
            padding: 11px 16px;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table td.label {
            color: #6b7280;
            width: 145px;
        }

        .info-table td.value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }

        .info-table td.accent {
            color: #f36b24;
            text-transform: capitalize;
            font-weight: 600;
            text-align: right;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 8px;
        }

        .data-table thead th {
            background: #f9fafb;
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 2px solid #f3f4f6;
        }

        .data-table tbody td {
            padding: 12px 14px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f9fafb;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        /* ── Payout card ───────────────────────────────────── */
        .payout-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff8f4;
            border: 1px solid #fed7b8;
            border-radius: 10px;
            overflow: hidden;
        }

        .payout-table tr td {
            padding: 13px 16px;
            font-size: 13px;
            border-bottom: 1px solid #fde8d4;
        }

        .payout-table tr:last-child td {
            border-bottom: none;
        }

        .payout-table td.label {
            color: #92400e;
            font-weight: 500;
        }

        .payout-table td.value {
            color: #f36b24;
            font-weight: 700;
            font-size: 15px;
            text-align: right;
        }

        /* ── Callout boxes ─────────────────────────────────── */
        .status-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #f36b24;
            border-radius: 8px;
            padding: 18px 20px;
            margin: 28px 0;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .status-icon {
            font-size: 22px;
            line-height: 1;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .status-body {
            flex: 1;
        }

        .status-title {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px;
        }

        .status-text {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }

        .warning-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-left: 4px solid #f36b24;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            color: #c2410c;
            font-weight: 500;
            margin: 24px 0;
        }

        .security-note {
            background: #fffbf0;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            color: #92400e;
            margin: 24px 0;
        }

        /* ── Code / OTP box ────────────────────────────────── */
        .code-wrap {
            text-align: center;
            margin: 36px 0;
        }

        .code-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 14px;
        }

        .code-box {
            display: inline-block;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: 10px;
            color: #f36b24;
            background: #fff8f4;
            padding: 22px 44px;
            border-radius: 10px;
            border: 1.5px dashed #f9b897;
        }

        /* ── Success / fail banners ────────────────────────── */
        .success-banner {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 24px 20px;
            text-align: center;
            margin: 28px 0;
        }

        .fail-banner {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }

        .approved-banner {
            background: linear-gradient(135deg, #fff8f4 0%, #fff3eb 100%);
            border: 1px solid #fed7b8;
            border-radius: 10px;
            padding: 24px 20px;
            text-align: center;
            margin: 24px 0;
        }

        .banner-icon {
            width: 36px;
            height: 36px;
            margin-bottom: 10px;
        }

        .banner-title-green {
            font-size: 16px;
            font-weight: 700;
            color: #15803d;
            margin: 0 0 6px;
        }

        .banner-sub-green {
            font-size: 13px;
            color: #166534;
            margin: 0;
        }

        .banner-title-red {
            font-size: 15px;
            font-weight: 700;
            color: #be123c;
            margin: 0;
        }

        .banner-title-orange {
            font-size: 16px;
            font-weight: 700;
            color: #c2410c;
            margin: 0 0 4px;
        }

        .banner-sub-orange {
            font-size: 13px;
            color: #9a3412;
            margin: 0;
        }

        /* ── Verification failed list ──────────────────────── */
        .reasons-label {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin: 24px 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .reasons-list {
            margin: 0 0 24px;
            padding: 0;
            list-style: none;
        }

        .reasons-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            color: #374151;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            margin-bottom: 8px;
        }

        .reasons-list li:last-child {
            margin-bottom: 0;
        }

        .reason-dot {
            width: 20px;
            height: 20px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            flex-shrink: 0;
            margin-top: 1px;
            color: #dc2626;
        }

        /* ── Feature grid (3-col) ──────────────────────────── */
        .features {
            display: table;
            width: 100%;
            border-collapse: separate;
            margin: 28px 0;
        }

        .feature-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 16px 10px;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .feature-item:first-child {
            border-radius: 8px 0 0 8px;
        }

        .feature-item:last-child {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }

        .feature-item+.feature-item {
            border-left: none;
        }

        .feature-icon {
            font-size: 20px;
            display: block;
            margin-bottom: 8px;
        }

        .feature-text {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            line-height: 1.4;
        }

        /* ── Traveller tips list ───────────────────────────── */
        .tips-list {
            list-style: none;
            margin: 0 0 24px;
            padding: 0;
        }

        .tips-list li {
            display: flex;
            gap: 14px;
            padding: 14px 16px;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 13px;
            color: #374151;
        }

        .tips-list li:last-child {
            margin-bottom: 0;
        }

        .tip-icon {
            width: 32px;
            height: 32px;
            background: #fff8f4;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .tip-body {
            flex: 1;
        }

        .tip-title {
            font-weight: 700;
            color: #111827;
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
        }

        /* ── Amount display ────────────────────────────────── */
        .amount-display {
            font-size: 28px;
            font-weight: 700;
            color: #f36b24;
        }

        /* ── ID badge ──────────────────────────────────────── */
        .id-badge {
            display: inline-block;
            background: #fff8f4;
            border: 1px solid #fed7b8;
            color: #c2410c;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.02em;
        }

        /* ── Misc ──────────────────────────────────────────── */
        .notif-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            padding: 24px 20px 32px;
            font-size: 12px;
            color: #9ca3af;
            background: #fafafa;
            border-top: 1px solid #f3f4f6;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="top-bar"></div>
            <div class="header">
                <img src="<?php echo production_url('assets/general/logo/colored_logo.png'); ?>" alt="<?= business ?>">
            </div>
            <div class="content">
