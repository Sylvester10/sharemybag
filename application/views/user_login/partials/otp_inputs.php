<?php
$hidden_name = isset($hidden_name) ? $hidden_name : 'verification_code';
$hidden_id = isset($hidden_id) ? $hidden_id : 'verificationCode';
$input_id_prefix = isset($input_id_prefix) ? $input_id_prefix : 'otp';
$group_label = isset($group_label) ? $group_label : 'Verification code';
$described_by = isset($described_by) ? trim($described_by) : '';
$autofocus = !empty($autofocus);
$auto_submit = !empty($auto_submit);
?>
<div class="otp-input-container"
     data-otp-target="<?php echo html_escape($hidden_id); ?>"
     <?php echo $auto_submit ? 'data-otp-auto-submit="true"' : ''; ?>
     role="group"
     aria-label="<?php echo html_escape($group_label); ?>"
     <?php echo $described_by !== '' ? 'aria-describedby="' . html_escape($described_by) . '"' : ''; ?>>
    <?php for ($index = 1; $index <= 6; $index++): ?>
        <input type="text"
               class="form-control otp-input"
               id="<?php echo html_escape($input_id_prefix . $index); ?>"
               maxlength="1"
               inputmode="numeric"
               pattern="[0-9]"
               <?php if ($index === 1): ?>
                   autocomplete="one-time-code"
               <?php else: ?>
                   autocomplete="off"
               <?php endif; ?>
               aria-label="Digit <?php echo $index; ?> of 6"
               required
               <?php echo $autofocus && $index === 1 ? 'autofocus' : ''; ?>>
    <?php endfor; ?>
</div>
<input type="hidden"
       name="<?php echo html_escape($hidden_name); ?>"
       id="<?php echo html_escape($hidden_id); ?>"
       value="">
