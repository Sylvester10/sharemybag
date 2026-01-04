<div class="container-fluid">

    <div class="card">
        <div class="card-header text-bg-primary">
            <h4 class="mb-0 text-white">Profile</h4>
        </div>

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

        <div class="card-body">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                    <div class="row">
                        <div class="col-12">
                            <div class="card w-100 border position-relative overflow-hidden mb-0">
                                <div class="card-body p-4">
                                    <h4 class="card-title mb-4">User Account</h4>

                                    <form action="<?= base_url('profile/profile_ajax/' . $user_details->id) ?>"
                                        class="form-ajax"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        target="_blank"
                                        redirect="<?= base_url('kyc') ?>">

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
                                                    <input type="email" class="form-control border border-primary" value="<?= $user_details->email ?>" readonly />
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

                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone (including country code)</label>
                                                    <input type="text" name="number" class="form-control required border border-primary" value="<?= $user_details->number ?>" <?= empty($user_details->number) ? '' : 'readonly' ?>>
                                                </div>
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

                                                <div class="card !tw-bg-[#020713]">
                                                    <div class="card-body p-4">
                                                        <p class="text-white text-center mb-0">Contact Admin to update your profile details </p>
                                                    </div>
                                                </div>

                                            <?php  } ?>

                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab" tabindex="0">
                    <div class="row">
                        <div class="col-lg-6 d-flex align-items-stretch">
                            <div class="card w-100 border position-relative overflow-hidden">
                                <div class="card-body p-4">
                                    <h4 class="card-title mb-4">Change Password</h4>

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

    </div>
</div>
