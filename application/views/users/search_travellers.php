<div class="container-fluid">

    <div class="card">
        <div class="card-header text-bg-primary">
            <h4 class="mb-0 text-white">Search for Travellers</h4>
        </div>
        <div class="card-body">

            <?php
            $form_attributes = array("id" => "search_form",);
            echo form_open('user_bookings/search', $form_attributes); ?>

            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="input-group mt-2 mb-2">
                        <select class="form-select required" name="destination" id="select_destination" required>
                            <option value="">Where is your parcel going?</option>
                            <?php
                            $countries = countries();
                            foreach ($countries as $country) { ?>
                                <option value="<?php echo $country; ?>" <?php echo set_select('nationality', $country); ?>><?php echo $country; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button class="btn btn-rounded btn-primary justify-content-center" type="submit">
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>

    <!-- search loading -->
    <div class="card d-none mb-0" id="search-spinner">
        <div class="card-body">
            <div class="card-title mb-3 mt-3 text-center">
                <div class="spinner-border text-center" style="width: 3rem; height: 3rem" role="status"></div>
            </div>

            <p class="text-center">
                Searching database....
            </p>
        </div>
    </div>

    <div class="card">

        <div class="table-responsive" id="search-results">
            <table class="table text-nowrap mb-0 d-none" id="section-1">
                <thead>
                    <tr>
                        <th scope="col"> <i class="ti ti-calendar fs-5"></i> Date</th>
                        <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
                        <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
                        <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Departure Airport </th>
                        <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
                        <th scope="col"> </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td> ${response.travel_date} </td>
                        <td> ${response.days_remaining} </td>
                        <td> ${response.current_state} </td>
                        <td> ${response.departure_state} </td>
                        <td> ${response.available_space} KG </td>
                        <td>
                            <a href="${base_url}buy-bag-space/${response.id}" class="btn btn-primary" type="submit">
                                Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>



    <!-- Verify ID modal -->
    <div class="modal fade" id="verifyID" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header !tw-flex !tw-justify-center !tw-bg-[#d20913]">
                    <h4 class="modal-title text-white">
                        Unable to complete
                    </h4>
                </div>
                <div class="modal-body !tw-text-center">
                    <div class="!tw-flex !tw-justify-center mt-2">
                        <i class="ti ti-face-id fs-13"></i>
                    </div>
                    <p class="!tw-flex !tw-justify-center mt-3">Please complete your Identity Verification to buy bag space.</p>

                    <div class="!tw-flex !tw-justify-center mt-3">
                        <?php
                        if ($is_verified == 0) { ?>

                            <?php if ($user_details) { ?>

                                <a class="btn bg-success-subtle text-success waves-effect text-end" href="<?php echo base_url('profile'); ?>">
                                    Click to update your profile
                                </a>

                            <?php } else { ?>

                                <a type="button" class="btn bg-primary-subtle text-primary waves-effect text-end" href="<?php echo base_url('kyc'); ?>">
                                    Click to begin verification
                                </a>

                            <?php } ?>

                        <?php } elseif ($is_verified == 1) { ?>

                            <button type="button" class="btn bg-warning text-white waves-effect text-end disabled">
                                Your ID verification pending
                            </button>

                        <?php } ?>
                    </div>

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