<?php if (!empty($phone_is_verified)): ?>
    <span class="input-group-text text-bg-success profile-phone-verified" id="phoneVerificationState" aria-label="Phone number verified" title="Phone number verified">
        <i class="ti ti-circle-check text-white" aria-hidden="true"></i>
    </span>
<?php else: ?>
    <button type="button" class="btn btn-primary profile-phone-verify-button" id="requestPhoneVerification">
        <span class="profile-phone-verify-label">Verify Number</span>
        <span class="spinner-border spinner-border-sm ms-1 d-none" id="phoneVerificationRequestSpinner" aria-hidden="true"></span>
    </button>
<?php endif; ?>
