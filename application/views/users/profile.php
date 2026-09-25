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

                                    <?php $this->load->view('partials/phone_input', array(
                                        'wrapper_class' => 'col-lg-6',
                                        'field_name' => 'number',
                                        'country_code_name' => 'country_code',
                                        'country_code_id' => 'profileCountryCode',
                                        'input_id' => 'profilePhoneNumber',
                                        'value' => $user_details->number,
                                        'label' => 'Phone Number',
                                        'placeholder' => '7911123456',
                                        'required' => true,
                                        'readonly' => false,
                                        'input_class' => 'required form-control border border-primary smb-phone-input__number',
                                        'select_class' => 'form-control border border-primary smb-phone-input__country',
                                    )); ?>

                                    <div class="col-lg-6 offset-lg-6 mt-n0 mb-4">
                                        <div class="d-flex flex-wrap align-items-center gap-2" id="phoneVerificationState">
                                            <?php if (!empty($user_details->phone_verified_at) && !empty($user_details->verified_phone_e164)): ?>
                                                <span class="badge text-bg-success px-3 py-2">
                                                    <i class="ti ti-circle-check me-1" aria-hidden="true"></i>
                                                </span>
                                                <small class="text-muted">This number can be used for passwordless sign-in.</small>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="requestPhoneVerification">
                                                <?php echo !empty($user_details->phone_verified_at) ? 'Verify a New Number' : 'Verify Phone Number'; ?>
                                                <span class="spinner-border spinner-border-sm ms-1 d-none" id="phoneVerificationRequestSpinner" aria-hidden="true"></span>
                                            </button>
                                            <?php if (empty($user_details->phone_verified_at)): ?>
                                                <small class="text-muted fs-2">Verify once to enable WhatsApp or SMS sign-in codes.</small>
                                            <?php endif; ?>
                                        </div>
                                        <div id="phoneVerificationStatus" class="mt-2" aria-live="polite"></div>
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

                                    <?php if (empty($user_details->post_code) || empty($user_details->state) || empty($user_details->address) || empty($user_details->number)) { ?>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-start mt-4">
                                                <button class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>

                                    <?php   } else { ?>

                                        <div class="col-12">
                                            <div class="alert alert-dark mb-0 mt-4 text-center text-white fs-3">
                                                Contact Support to update your profile details.
                                            </div>
                                        </div>

                                    <?php  } ?>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab" tabindex="0">
                    <div class="row">
                        <div class="col-lg-6">

                            <p class="text-muted mb-4">Set a new password for this account. The old password is replaced immediately after a successful update.</p>

                            <form action="<?= base_url('profile/change_password/' . $user_details->id) ?>" class="form-ajax" method="POST" enctype="multipart/form-data" target="_blank" redirect="<?= base_url('profile') ?>">

                                <div class="mb-3">
                                    <label for="exampleInputPassword2" class="form-label">New Password</label>
                                    <input type="password" class="form-control required" name="password" id="exampleInputPassword2" />
                                </div>
                                <div>
                                    <label for="exampleInputPassword3" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control required" name="confirm_password" id="exampleInputPassword3" />
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-start mt-4 gap-6">
                                        <button class="btn btn-primary">Submit</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="phoneVerificationModal" tabindex="-1" aria-labelledby="phoneVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="phoneVerificationModalLabel">Verify your phone number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="verifyPhoneNumberForm" action="<?= base_url('profile/verify_phone_ajax') ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="challenge_token" id="phoneVerificationChallengeToken">
                    <p class="text-muted" id="phoneVerificationInstructions">Enter the code sent to your phone.</p>
                    <label for="phoneVerificationCode" class="form-label">Verification code</label>
                    <input type="text" class="form-control auth-profile-code-input" id="phoneVerificationCode" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" maxlength="10" required>
                    <div id="phoneVerificationModalStatus" class="mt-3" aria-live="polite"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="verifyPhoneNumberButton">
                        Verify Number
                        <span class="spinner-border spinner-border-sm ms-1 d-none" id="phoneVerificationCodeSpinner" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
