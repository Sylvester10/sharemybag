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
            <div class="card-body p-4 traveller-search-content">
                <h4 class="mb-2 text-white">Search Travellers</h4>
                <p class="text-white mb-5 fs-3">
                    Find available travellers for your route and choose who will carry your parcel.
                </p>

                <?php $form_attributes = array("id" => "search_form");
                echo form_open('user_bookings/search', $form_attributes); ?>

                <div class="traveller-search-box">
                    <div class="traveller-search-fields">
                        <div class="traveller-search-field">
                            <select class="form-select required traveller-search-select" name="location" id="select_location" required>
                                <option value="">Where is your parcel?</option>
                                <?php foreach (countries() as $country): ?>
                                    <option value="<?php echo $country; ?>" <?php echo set_select('location', $country); ?>>
                                        <?php echo $country; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="traveller-search-field">
                            <select class="form-select required traveller-search-select" name="destination" id="select_destination" required>
                                <option value="">Where is your parcel going?</option>
                                <?php foreach (countries() as $country): ?>
                                    <option value="<?php echo $country; ?>" <?php echo set_select('destination', $country); ?>>
                                        <?php echo $country; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary traveller-search-submit" type="submit">
                        Search
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>

    <?php endif; ?>

    <div class="traveller-search-loading d-none" id="search-spinner" role="status" aria-live="polite">
        <svg class="traveller-book" viewBox="0 0 96 80" width="96" height="80" aria-hidden="true" focusable="false">
            <path class="traveller-book-cover" d="M7 27Q28 22 48 32Q68 22 89 27V69Q68 65 48 73Q28 65 7 69Z" />
            <path class="traveller-book-paper" d="M11 23Q31 18 48 30V67Q30 57 11 63Z" />
            <path class="traveller-book-paper" d="M48 30Q65 18 85 23V63Q66 57 48 67Z" />
            <path class="traveller-book-spine" d="M48 30V69" />
            <g class="traveller-book-page traveller-book-page--back">
                <path d="M48 29Q63 15 82 19V58Q63 55 48 67Z" />
            </g>
            <g class="traveller-book-page traveller-book-page--front">
                <path d="M48 29Q56 9 72 6V44Q57 49 48 67Z" />
            </g>
        </svg>
        <p class="fs-3 mb-0">Searching for travellers…</p>
    </div>

    <div class="card search-results-card d-none">
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

                        <?php if ($is_profile_incomplete) { ?>
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
