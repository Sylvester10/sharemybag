<div class="container-fluid">

    <div class="traveller-search-panel overflow-hidden">
        <div class="card-body p-4">
            <h4 class="mb-2 text-white">Profile</h4>
            <p class="text-white mb-1 fs-3">
                View and update your profile details.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">

        <ul class="nav nav-pills user-profile-tab" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                    <i class="ti ti-user-circle me-2 fs-6"></i>
                    <span class="d-none d-md-block">Account</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false">
                    <i class="ti ti-lock me-2 fs-6"></i>
                    <span class="d-none d-md-block">Security</span>
                </button>
            </li>
        </ul>

        <!-- referral link -->
        <div class="card-body border-bottom pb-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">Referral Link</h5>
                    <p class="text-muted fs-2 mb-0">Share this link with your friends and family to earn rewards.</p>
                </div>
                <div class="referal-link-btn">
                    <button type="button" id="referal-link-to-us" class="copy-referral" data-bs-placement="top" data-bs-toggle="tooltip" data-bs-original-title="Click To Copy Referral Link">
                        <span class="r-link"><?= $referral_link ?></span>
                        <span class="r-icon"><i class="ti ti-link"></i></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                    <div class="row">
                        <div class="col-12">

                            <form action="<?= base_url('profile/profile_ajax/' . $user_details->id) ?>" class="form-ajax" method="POST" enctype="multipart/form-data"
                                target="_blank" redirect="<?= base_url('kyc') ?>">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control border border-primary" id="exampleInputtext" value="<?= $user_details->firstname ?> <?= $user_details->lastname ?>" readonly />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control border border-primary" value="<?= html_escape($user_details->email) ?>" readonly />
                                                <span class="input-group-text text-bg-success">
                                                    <i class="ti ti-circle-check text-white me-1" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Country</label>
                                            <input type="text" class="form-control border border-primary" value="<?= $user_details->country ?>" readonly>
                                        </div>
                                    </div>

                                    <?php $phone_is_verified = !empty($user_details->phone_verified_at) && !empty($user_details->verified_phone_e164); ?>
                                    <?php $phone_signin_enabled = $phone_is_verified && !empty($user_details->phone_signin_enabled); ?>
                                    <div class="col-lg-6 mb-3 profile-phone-signin<?php echo !empty($user_details->number) ? ' has-number' : ''; ?>" id="profilePhoneSignIn">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                            <label class="form-label mb-0" for="profilePhoneNumber">Phone Number <span class="text-danger">*</span></label>
                                        </div>
                                    <?php $this->load->view('partials/phone_input', array(
                                        'wrapper_class' => '',
                                        'group_class' => 'smb-phone-input input-group profile-phone-input-group',
                                        'field_name' => 'number',
                                        'country_code_name' => 'country_code',
                                        'country_code_id' => 'profileCountryCode',
                                        'input_id' => 'profilePhoneNumber',
                                        'value' => $user_details->number,
                                        'label' => '',
                                        'placeholder' => '7911123456',
                                        'required' => true,
                                        'readonly' => $phone_is_verified,
                                        'input_class' => 'required form-control border border-primary smb-phone-input__number',
                                        'select_class' => 'form-control border border-primary smb-phone-input__country',
                                        'trailing_view' => 'users/partials/phone_verification_control',
                                        'trailing_view_data' => array('phone_is_verified' => $phone_is_verified),
                                    )); ?>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control required border border-primary" value="<?= $user_details->address ?>" <?= empty($user_details->address) ? '' : 'readonly' ?>>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">State</label>
                                            <input type="text" name="state" class="form-control required border border-primary" value="<?= $user_details->state ?>" <?= empty($user_details->state) ? '' : 'readonly' ?>>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Post Code</label>
                                            <input type="text" name="post_code" class="form-control required border border-primary" placeholder="ABC-123" value="<?= $user_details->post_code ?>" <?= empty($user_details->post_code) ? '' : 'readonly' ?>>
                                        </div>
                                    </div>

                                    <?php $profile_is_complete = !empty($user_details->post_code) && !empty($user_details->state) && !empty($user_details->address) && !empty($user_details->number); ?>
                                        <div class="col-12<?php echo $profile_is_complete ? ' d-none' : ''; ?>" id="profileSubmitAction">
                                            <div class="d-flex align-items-center justify-content-start mt-4">
                                                <button class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>

                                        <div class="col-12<?php echo $profile_is_complete ? '' : ' d-none'; ?>" id="profileSupportNotice">
                                            <div class="alert alert-dark mb-0 mt-4 text-center text-white fs-3">
                                                Contact Support to update your profile details.
                                            </div>
                                        </div>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab" tabindex="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" class="profile-security-card" data-bs-toggle="modal" data-bs-target="#profilePasswordModal">
                                <span class="profile-security-card__icon"><i class="ti ti-lock" aria-hidden="true"></i></span>
                                <span class="profile-security-card__text"><strong>Change Password</strong><span>Update your account password.</span></span>
                                <i class="ti ti-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="profile-security-card" id="profileSignInCodesCard" data-bs-toggle="modal" data-bs-target="#profileSignInCodesModal">
                                <span class="profile-security-card__icon"><i class="ti ti-key" aria-hidden="true"></i></span>
                                <span class="profile-security-card__text"><strong>Sign-in Codes</strong><span>Choose how you receive sign-in codes.</span></span>
                                <i class="ti ti-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="phoneVerificationModal" tabindex="-1" role="dialog" aria-modal="true" aria-label="Phone verification" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="verifyPhoneNumberForm" action="<?= base_url('profile/verify_phone_ajax') ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="challenge_token" id="phoneVerificationChallengeToken">
                    <label class="form-label" for="phoneVerificationOtp1">Enter your verification code</label>
                    <div class="profile-phone-otp">
                    <?php $this->load->view('user_login/partials/otp_inputs', array(
                        'hidden_name' => 'code',
                        'hidden_id' => 'phoneVerificationCode',
                        'auto_submit' => true,
                        'input_id_prefix' => 'phoneVerificationOtp',
                        'group_label' => 'Enter your verification code',
                        'described_by' => 'phoneVerificationModalStatus',
                    )); ?>
                    </div>
                    <div id="phoneVerificationModalStatus" class="mt-3" aria-live="polite"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="profilePasswordModal" tabindex="-1" aria-labelledby="profilePasswordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePasswordModalTitle">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('profile/change_password/' . $user_details->id) ?>" class="form-ajax" method="post" redirect="<?= base_url('profile') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profileNewPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control required" name="password" id="profileNewPassword" autocomplete="new-password" required minlength="6">
                    </div>
                    <div>
                        <label for="profileConfirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control required" name="confirm_password" id="profileConfirmPassword" autocomplete="new-password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $signin_method = $phone_signin_enabled ? (($user_details->phone_otp_channel ?? 'sms') === 'whatsapp' ? 'whatsapp' : 'sms') : 'email'; ?>
<div class="modal fade" id="profileSignInCodesModal" tabindex="-1" aria-labelledby="profileSignInCodesModalTitle" aria-hidden="true" data-verified="<?= $phone_is_verified ? '1' : '0' ?>" data-method="<?= $signin_method ?>">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileSignInCodesModalTitle">Sign-in Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="profile-code-methods" role="group" aria-label="Receive sign-in codes via">
                    <?php foreach (array('email' => 'Email', 'whatsapp' => 'WhatsApp', 'sms' => 'SMS') as $method => $label): ?>
                        <label class="profile-code-method" for="profileCodeMethod-<?= $method ?>">
                            <span><?= $label ?></span>
                            <span class="profile-phone-signin-toggle">
                                <input type="checkbox" role="switch" class="profile-code-method-switch" id="profileCodeMethod-<?= $method ?>" data-method="<?= $method ?>" aria-label="<?= $label ?>" <?= $signin_method === $method ? 'checked' : '' ?> <?= $method !== 'email' && !$phone_is_verified ? 'disabled' : '' ?>>
                                <span class="profile-phone-signin-track" aria-hidden="true"></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p id="profileCodeMethodsVerifyNotice" class="fs-3 text-muted mt-3 mb-0<?= $phone_is_verified ? ' d-none' : '' ?>">Verify your phone number in <button type="button" class="profile-account-link" id="profileVerifyPhoneLink">Account</button> to use WhatsApp or SMS.</p>
                <div id="profileCodeMethodStatus" class="fs-3 text-center mt-3" role="status" aria-live="polite"></div>
            </div>
        </div>
    </div>
</div>
