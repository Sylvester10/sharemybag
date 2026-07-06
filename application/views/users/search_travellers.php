<div class="container-fluid">

    <?php if ($account_status == 0): ?>

        <div class="card !tw-bg-[#020713]">
            <div class="card-body">
                <h6 class="card-text text-white text-center fw-bolder text-uppercase">
                    <i class="ti ti-alert-circle fs-5"></i> Access Restricted!!
                </h6>
                <p class="text-white text-center mt-3 mb-0">
                    You are unable to access this page. Please contact Admin.
                </p>
            </div>
        </div>

    <?php else: ?>

        <div class="traveller-search-panel overflow-hidden">
            <div class="card-body p-4">
                <h4 class="mb-2 text-white">Search for Travellers</h4>
                <p class="text-white mb-5 fs-3">
                    Find available travellers for your route and choose who will carry your parcel.
                </p>

                <?php $form_attributes = array("id" => "search_form");
                echo form_open('user_bookings/search', $form_attributes); ?>

                <div class="traveller-search-box">
                    <select class="form-select required traveller-search-select" name="destination" id="select_destination" required>
                        <option value="">Where is your parcel going?</option>
                        <?php foreach (countries() as $country): ?>
                            <option value="<?php echo $country; ?>" <?php echo set_select('destination', $country); ?>>
                                <?php echo $country; ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                    <button class="btn btn-primary traveller-search-submit" type="submit">
                        Search
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>

    <?php endif; ?>

    <!-- search loading -->
    <div class="card d-none mb-0" id="search-spinner">
        <div class="card-body">
            <div class="card-title mb-3 mt-3 text-center">
                <div class="spinner-border text-center" style="width: 3rem; height: 3rem" role="status"></div>
            </div>

            <p class="text-center">
                Finding available travellers....
            </p>
        </div>
    </div>

    <div class="card search-results-card">
        <div class="card-body">
            <div id="search-results" aria-live="polite"></div>
        </div>
    </div>

    <!-- Verify ID modal -->
    <div class="modal fade" id="verifyID" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header !tw-flex !tw-justify-center !tw-bg-[#d20913]">
                    <h4 class="modal-title text-white">
                        <i class="ti ti-alert-circle"></i> Attention
                    </h4>
                </div>
                <div class="modal-body !tw-text-center">
                    <?php
                    if ($is_verified == VERIFY_NONE) { ?>

                        <?php if ($is_profile_complete) { ?>
                            <div class="!tw-flex !tw-justify-center mt-2">
                                <i class="ti ti-user fs-13"></i>
                            </div>

                            <p class="!tw-flex !tw-justify-center mt-3">Your profile is incomplete. Please complete your profile before starting verification.</p>

                            <div class="!tw-flex !tw-justify-center mt-3">

                                <a class="btn bg-success-subtle text-success waves-effect text-end" href="<?php echo base_url('profile'); ?>">
                                    Complete Profile
                                </a>
                            </div>

                        <?php } else { ?>

                            <div class="!tw-flex !tw-justify-center mt-2">
                                <dotlottie-wc src="https://lottie.host/b3d01a07-111f-4c8b-984f-effae09ea9da/mdwRLRcbfu.lottie" style="width: 350px;height:170px"
                                    autoplay loop></dotlottie-wc>
                            </div>

                            <p class="!tw-flex !tw-justify-center mt-3">Identity verification required. Click the button to begin.</p>

                            <div class="!tw-flex !tw-justify-center mt-3">

                                <a type="button" class="btn bg-primary-subtle text-primary waves-effect text-end" href="<?php echo base_url('kyc'); ?>">
                                    Begin Verification
                                </a>
                            </div>

                        <?php } ?>

                    <?php } elseif ($is_verified == VERIFY_PENDING) { ?>
                        <p class="!tw-flex !tw-justify-center mt-3">Your documents have been submitted and are currently being reviewed</p>

                    <?php } ?>

                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

    <!--Goto profile modal -->
    <div class="modal fade" id="goToProfile" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header !tw-flex !tw-justify-center !tw-bg-[#d20913]">
                    <h4 class="modal-title text-white">
                        Unable to complete
                    </h4>
                </div>
                <div class="modal-body !tw-text-center">
                    <div class="!tw-flex !tw-justify-center mt-2">
                        <i class="ti ti-user fs-13"></i>
                    </div>
                    <p class="!tw-flex !tw-justify-center mt-3">Please update your Profile to buy bag space.</p>

                    <div class="!tw-flex !tw-justify-center mt-3">
                        <a class="btn bg-success-subtle text-success waves-effect text-end" href="<?php echo base_url('profile'); ?>">
                            Click to update your profile
                        </a>
                    </div>

                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

</div>
