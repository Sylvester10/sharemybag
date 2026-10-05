<?php
$policy_links = [
    'terms-of-use' => 'Terms of Use',
    'terms-and-conditions' => 'Terms & Conditions',
    'waiver' => 'Liability Waiver',
    'prohibited-items' => 'Prohibited Items',
    'privacy-policy' => 'Privacy Policy',
    'cookies' => 'Cookie Policy',
];
?>
<main class="smb-policy-page container">
    <header class="smb-policy-heading">
        <h1><?= html_escape($policy_title); ?></h1>
        <?php if (!empty($policy_intro)): ?>
            <div class="smb-policy-intro"><?= $policy_intro; ?></div>
        <?php endif; ?>
    </header>
    <aside class="smb-policy-sidebar">
        <h2>Policies &amp; guidelines</h2>
        <nav aria-label="Policy pages">
            <?php foreach ($policy_links as $route => $label): ?>
                <a href="<?= base_url($route); ?>" <?= $route === $policy_route ? 'aria-current="page"' : ''; ?>><?= html_escape($label); ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="smb-policy-content">
        <div class="smb-policy-mobile-menu">
            <label class="visually-hidden" for="smb-policy-selector">Policies &amp; guidelines</label>
            <div class="smb-policy-select-wrap">
            <select id="smb-policy-selector" aria-label="Choose a policy page">
                <?php foreach ($policy_links as $route => $label): ?>
                    <option value="<?= base_url($route); ?>" <?= $route === $policy_route ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
                <?php endforeach; ?>
            </select>
            </div>
        </div>
        <article class="smb-policy-document" tabindex="0" aria-label="<?= html_escape($policy_title); ?>">
