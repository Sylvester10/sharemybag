<div class="container-fluid">

    <div class="card">
        <div class="card-header" style="background-color: #f36b24;">
            <h4 class="mb-0 text-white text-center"><i class="ti ti-alert-circle me-2"></i>Sending Restrictions</h4>
        </div>
        <div class="card-body">
            <div class="!tw-flex !tw-justify-center restrictionss mb-3">

                <div class="icos">
                    <i class="ti ti-biohazard fs-9"></i>
                    <b class="fs-2"> Bio Hazard</b>
                </div>

                <div class="icos">
                    <i class="ti ti-cannabis fs-9"></i>
                    <b class="fs-2">Illegal Drugs</b>
                </div>

                <div class="icos">
                    <i class="ti ti-bomb fs-9"></i>
                    <b class="fs-2">Bomb</b>
                </div>

                <div class="icos">
                    <i class="ti ti-skull fs-9"></i>
                    <b class="fs-2">Flammables</b>
                </div>

            </div>
            <div class="text-center">
                <p class="text-subtle">Please be aware of the above restrictions when sending a parcel with Share My Bag.</p>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-8">
            <div class="card">


                <div class="card-body">

                    <?php
                    if ($traveller_details->additional_info === NULL) {

                        echo '';
                    } else {

                        echo
                        '<div class="card !tw-bg-[#020713]">
                            <div class="card-body p-4">
                                <h6 class="card-text text-white text-center fw-bolder ">
                                    <i class="ti ti-alert-circle"></i> Important!!
                                </i>
                                <p class="text-white text-center">' . $traveller_details->additional_info . ' </p>
                            </div>
                        </div>';
                    }
                    ?>

                    <form action="<?= base_url('user_bookings/add_booking_ajax') ?>" class="form-wizard-ajax mt-5" id="booking_form" key="<?= $traveller_details->id ?>" method="POST" enctype="multipart/form-data" target="_blank">

                        <div class="d-none">
                            <input name="traveller_id" value="<?php echo set_value('traveller_id', $traveller_details->id); ?>" type=" text" class="form-control" />
                            <input name="traveller_name" value="<?php echo set_value('traveller_name', $traveller_details->fullname); ?>" type=" text" class="form-control" />
                            <input name="traveller_email" value="<?php echo set_value('traveller_email', $traveller_details->email); ?>" type=" text" class="form-control" />
                            <input name="traveller_contact" value="<?php echo set_value('traveller_contact', $traveller_details->phone); ?>" type=" text" class="form-control" />
                            <input name="traveller_departure_date" value="<?php echo set_value('traveller_departure_date', $traveller_details->travel_date); ?>" type=" text" class="form-control" />
                            <input name="traveller_arrival_date" value="<?php echo set_value('traveller_arrival_date', $traveller_details->arrival_date); ?>" type=" text" class="form-control" />
                            <input name="traveller_departure_state" value="<?php echo set_value('traveller_departure_state', $traveller_details->departure_state); ?>" type=" text" class="form-control" />
                            <input name="traveller_drop_date1" value="<?php echo set_value('traveller_drop_date1', $traveller_details->drop_date1); ?>" type=" text" class="form-control" />
                            <input name="traveller_drop_address1" value="<?php echo set_value('traveller_drop_address1', $traveller_details->drop_address1); ?>" type=" text" class="form-control" />
                            <input name="traveller_drop_date2" value="<?php echo set_value('traveller_drop_date2', $traveller_details->drop_date2); ?>" type=" text" class="form-control" />
                            <input name="traveller_drop_address2" value="<?php echo set_value('traveller_drop_address2', $traveller_details->drop_address2); ?>" type=" text" class="form-control" />
                            <input name="traveller_current_state" value="<?php echo set_value('traveller_current_state', $traveller_details->current_state); ?>" type=" text" class="form-control" />
                            <input name="traveller_arrival_airport" value="<?php echo set_value('traveller_arrival_airport', $traveller_details->arrival_airport); ?>" type=" text" class="form-control" />
                            <input name="traveller_arrival_state" value="<?php echo set_value('traveller_arrival_state', $traveller_details->arrival_state); ?>" type=" text" class="form-control" />
                            <input name="traveller_destination" value="<?php echo set_value('traveller_destination', $traveller_details->destination); ?>" type=" text" class="form-control" />

                            <input type="hidden" name="rate" value="<?= $currency === 'pounds' ? $one_pound : $one_dollar ?>">

                            <input type="text" name="price_calculations" class="form-control" placeholder="Calculations" id="price_calculations">

                            <input type="hidden" name="currency" value="<?= $currency ?>" class="form-control" />
                        </div>

                        <div id="user-details"
                            data-name="<?= htmlspecialchars($user_details->firstname . ' ' . $user_details->lastname) ?>"
                            data-email="<?= htmlspecialchars($user_details->email) ?>"
                            data-phone="<?= htmlspecialchars($user_details->number) ?>"
                            data-address="<?= htmlspecialchars($user_details->address) ?>"
                            data-city="<?= htmlspecialchars($user_details->state) ?>"
                            data-postcode="<?= htmlspecialchars($user_details->post_code) ?>">
                        </div>

                        <input type="hidden" id="paystack_reference" name="paystack_reference" />
                        <input type="hidden" id="payment_method" name="payment_method" />

                        <h3> <i class="ti ti-package fs-5"></i> About Your Item </h3>
                        <fieldset>
                            <h4 class="card-title mb-5"> Provide details about the <b class="!tw-text-[#f36b24]">parcel.</b></h4>

                            <div class="row">
                                <input name="items" type="text" class="form-control" id="items_input" hidden />

                                <?php

                                // Route between Nigeria and UK
                                $is_to_nigeria = $traveller_details->destination === 'Nigeria';

                                // Canadian Dollars (CAD)
                                $is_canada_nigeria_route =
                                    ($traveller_details->location === 'Canada' && $traveller_details->destination === 'Nigeria') ||
                                    ($traveller_details->location === 'Nigeria' && $traveller_details->destination === 'Canada');

                                // Prices are set based on the user's currency type (pounds or dollars)
                                if ($currency === 'pounds') {

                                    $normal_price  = $is_to_nigeria ? 6.5 : 8.5;
                                    $shopper_price = 9.5; // Updated from 7.5 to 9.5 Only applies when destination is Nigeria
                                    $special_price = $is_to_nigeria ? 6.5 : 8.5;
                                    $premium_price = 15;
                                } else {

                                    if ($is_canada_nigeria_route) {

                                        $normal_price  = 11.5;
                                        $shopper_price = 0;
                                        $special_price = 11.5;
                                        $premium_price = 20;
                                    } else {

                                        if ($is_canada_nigeria_route) {

                                            $normal_price  = 11.5;
                                            $shopper_price = 0;
                                            $special_price = 11.5;
                                            $premium_price = 20;
                                        } else {

                                            $normal_price  = $is_to_nigeria ? 6.5 : 8.5;
                                            $shopper_price = 7.5; // Only applies when destination is Nigeria
                                            $special_price = $is_to_nigeria ? 6.5 : 8.5;
                                            $premium_price = 15;
                                        }
                                    }
                                }

                                // Output select based on traveller destination (for category options)
                                if ($traveller_details->destination === 'Nigeria') { ?>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label">Category *</label>
                                        <select name="category" id="select1" class="required form-select border border-primary">
                                            <option value="">Select</option>
                                            <option value="Normal" data-price="<?= round($normal_price, 2) ?>">Normal</option>
                                            <option value="Duty Free" data-price="<?= round($shopper_price, 2) ?>">Duty Free</option>
                                            <option value="Fish/Medicine" data-price="<?= round($special_price, 2) ?>">Medicine (special)</option>
                                            <option value="Documents/Electronics" data-price="<?= round($premium_price, 2) ?>">Documents/Electronics/Gold (premium)</option>
                                        </select>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-lg-4 mb-3">
                                        <label class="form-label">Category *</label>
                                        <select name="category" id="select1" class="required form-select border border-primary">
                                            <option value="">Select</option>
                                            <option value="Normal" data-price="<?= round($normal_price, 2) ?>">Normal</option>
                                            <option value="Fish/Medicine" data-price="<?= round($special_price, 2) ?>">Fish/Medicine/Snail/Oil (special)</option>
                                            <option value="Documents/Electronics" data-price="<?= round($premium_price, 2) ?>">Documents/Electronics/Gold (premium)</option>
                                        </select>
                                    </div>
                                <?php } ?>

                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Item name *</label>
                                    <input name="item" id="item-name" type="text" class="required form-control border border-primary" placeholder="Item name" />
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <label class="form-label" id="weight-label">Weight *</label>
                                    <?php $max_space = $traveller_details->available_space; ?>
                                    <select name="size" id="select2" class="required form-select border border-primary" data-max-space="<?= $max_space ?>" data-unit="KG">
                                        <option value="">Select</option>
                                        <?php
                                        for ($i = 1; $i <= $max_space; $i++) {
                                            // The default unit is KG
                                            echo "<option value='$i'>{$i}KG</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                            </div>

                            <div class="col-lg-12 mb-5">
                                <button type="button" class="btn btn-primary !tw-w-full !tw-h-[50px]" id="add-me">Add</button>
                            </div>
                        </fieldset>

                        <h3> <i class="ti ti-user-circle fs-5"></i> Agent Details </h3>
                        <fieldset>
                            <h4 class="card-title mb-4"> The Agent is whoever is <b class="!tw-text-[#f36b24]">currently in possession</b> of the parcel to be given to the traveller.</b></h4>

                            <div class="radio_buttons mb-3">
                                <div class="form-check radio_check">
                                    <input class="form-check-input" type="radio" name="parcel_status" id="parcelWithMe" value="with_me">
                                    <label class="form-label" for="parcelWithMe">
                                        Parcel is with me
                                    </label>
                                </div>
                                <div class="form-check radio_check">
                                    <input class="form-check-input" type="radio" name="parcel_status" id="parcelWithSomeone" value="with_someone" checked>
                                    <label class="form-label" for="parcelWithSomeone">
                                        Parcel is with someone else
                                    </label>
                                </div>
                            </div>


                            <label class="form-label">Full name *</label>
                            <input name="agent_name" id="agentName" type="text" class="required form-control border border-primary mb-3" />

                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="form-label">Email *</label>
                                    <input name="agent_email" id="agentEmail" type="email" class="required email form-control border border-primary mb-3" />
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label">Phone number *</label>
                                    <input name="agent_phone" id="agentPhone" type="tel" class="required form-control border border-primary mb-3" placeholder="+44936285197" />
                                </div>
                            </div>

                            <label class="form-label">Street address *</label>
                            <input name="agent_address" id="agentAddress" type="text" class="required form-control border border-primary mb-3" />

                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="form-label">City *</label>
                                    <input name="agent_locality" id="agentCity" type="text" class="required form-control border border-primary mb-3" />
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Post Code *</label>
                                    <input name="agent_postcode" id="agentPostcode" type="text" class="required form-control border border-primary mb-3" />
                                </div>
                            </div>
                        </fieldset>

                        <h3> <i class="ti ti-user-circle fs-5"></i> Receiver Details </h3>
                        <fieldset>
                            <h4 class="card-title mb-4"> The Receiver is the person <b class="!tw-text-[#f36b24]"> whose parcel is being sent</b>.</h4>

                            <div class="radio_buttons mb-3">
                                <div class="form-check radio_check">
                                    <input class="form-check-input" type="radio" name="receiver_status" id="receiverIsMe" value="me">
                                    <label class="form-label" for="receiverIsMe">
                                        I am the receiver
                                    </label>
                                </div>
                                <div class="form-check radio_check">
                                    <input class="form-check-input" type="radio" name="receiver_status" id="receiverIsSomeoneElse" value="someone_else" checked>
                                    <label class="form-label" for="receiverIsSomeoneElse">
                                        Someone else
                                    </label>
                                </div>
                            </div>

                            <label class="form-label">Full name *</label>
                            <input name="receiver_name" id="receiverName" type="text" class="required form-control border border-primary mb-3" />

                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="form-label">Email *</label>
                                    <input name="receiver_email" id="receiverEmail" type="email" class="required email form-control border border-primary mb-3" />
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label">Phone number *</label>
                                    <input name="receiver_phone" id="receiverPhone" type="tel" class="required form-control border border-primary mb-3" placeholder="+44936285197" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Street address *</label>
                                <input name="receiver_address" id="receiverAddress" type="text" class="required form-control border border-primary" />
                                <small>Your parcel will be sent to this address. You will not be able to change the receiver's address after payment.</small>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="form-label">City *</label>
                                    <input name="receiver_locality" id="receiverCity" type="text" class="required form-control border border-primary mb-3" />
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Post Code *</label>
                                    <input name="receiver_postcode" id="receiverPostcode" type="text" class="required form-control border border-primary mb-3" />
                                </div>
                            </div>

                        </fieldset>

                        <h3> <i class="ti ti-shield-check fs-5"></i> Parcel Guarantee </h3>
                        <fieldset>
                            <h4 class="card-title mb-4">Parcel protection <b class="!tw-text-[#f36b24]">(Optional)</b></h4>

                            <div class="col-lg-6 mb-3">
                                <?php
                                // Insurance prices
                                $ins_low_val = 3.99;
                                $ins_high_val = 13.99;
                                ?>
                                <select name="insurance" id="insuranceBox" class="form-select border border-primary">
                                    <option value="">Do you want parcel insurance?</option>
                                    <option value="<?= number_format($ins_low_val, 2); ?>" data-insurance="<?= number_format($ins_low_val, 2); ?>">
                                        Parcel Guarantee <?= $symbol ?><?= number_format($ins_low_val, 2); ?>
                                    </option>
                                    <option value="<?= number_format($ins_high_val, 2); ?>" data-insurance="<?= number_format($ins_high_val, 2); ?>">
                                        Parcel Guarantee <?= $symbol ?><?= number_format($ins_high_val, 2); ?>
                                    </option>
                                </select>
                            </div>

                            <p class="card-subtitle mb-3">
                                Insurance covers your parcel up to <b class="text-black"><?= $symbol ?>100</b> when you purchase a parcel guarantee of <b class="text-black"><?= $symbol ?>3.99</b> or up to <b class="text-black"><?= $symbol ?>300</b> when you buy a parcel guarantee of <b class="text-black"><?= $symbol ?>13.99</b> in the case of loss or avoidable damage <b class="text-black">(except fish)</b> during the traveller’s journey.
                            </p>

                            <div class="form-check">
                                <label class="formlabel">
                                    <b class="text-black">Do you need help connecting this parcel to the traveller?</b>
                                </label>
                                <input class="form-check-input" type="checkbox" value="Yes" name="need_help">
                            </div>
                        </fieldset>

                        <h3> <i class="ti ti-cash fs-5"></i> Summary </h3>
                        <fieldset>
                            <h4 class="card-title mb-5"> Payment Summary</h4>
                            <div class="text-center">

                                <!-- <div class="card !tw-bg-[#020713]">
                                    <div class="card-body p-4">
                                        <h6 class="card-text text-white text-center fw-bolder ">
                                            <i class="ti ti-alert-circle"></i> Important!!
                                        </h6>
                                        <p class="text-white text-center">Please drop your items off with the traveller by your regions last drop-off date. There will be no refund or transfer of service to another traveller.</p>
                                    </div>
                                </div> -->

                                <h5 class="mt-2 mb-3"> <?= $traveller_details->location ?> <span class="cf-16 cf-<?php echo country_to_code($traveller_details->location); ?> fs-7"></span> <i class=" ti ti-plane-departure fs-7 ms-3 me-3"></i> <?= $traveller_details->destination ?> <span class="cf-16 cf-<?php echo country_to_code($traveller_details->destination); ?> fs-7"></span></h5>

                                <p class="mt-2"> Travel Date </p>

                                <h3 class=" mb-3"><b> <?= x_date($traveller_details->travel_date) ?></b></h3>

                                <p class="mt-2"> You're buying </p>

                                <h3 class=" mb-3"><b> <span id="total-kgs">10</span>KG</b></h3>

                                <p class="mt-2">bag space for</p>

                                <h3 class=" mb-3"><b> <span id="totalAmountDisplay"></span> </b></h3>

                                <div class="card !tw-bg-[#020713]">
                                    <div class="card-body p-4">
                                        <h6 class="card-text text-white text-center fw-bolder ">
                                            <i class="ti ti-alert-circle"></i> Important!!
                                        </h6>
                                        <p class="text-white text-center">Please drop your items off with the traveller by your regions last drop-off date. There will be no refund or transfer of service to another traveller.</p>
                                        <p class="text-white text-center">Payment must be made with a bank card bearing the same name as the one on your profile.</p>
                                    </div>
                                </div>

                                <p class="mt-2 mb-3">Select payment method and click "finish" to continue to payments </p>

                                <?php
                                // Logic: Paystack only for Nigerian users
                                if ($user_details->country == 'Nigeria') { ?>

                                    <div class="mb-4 radio_buttons">
                                        <div class="form-check radio_check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="paystack" value="paystack" checked>
                                            <label class="form-check-label d-flex align-items-center gap-2" for="paystack">
                                                <img src="<?php echo base_url('assets/general/paystack.svg'); ?>" alt="Paystack" width="100" height="20">
                                            </label>
                                        </div>
                                    </div>

                                <?php } else {
                                    // Stripe for Canada and UK users
                                ?>

                                    <div class="mb-4 radio_buttons">
                                        <div class="form-check radio_check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" checked>
                                            <label class="form-check-label d-flex align-items-center gap-2" for="stripe">
                                                <img src="<?php echo base_url('assets/general/stripe.svg'); ?>" alt="Stripe" width="100" height="20">
                                            </label>
                                        </div>
                                    </div>

                                <?php } ?>

                            </div>

                        </fieldset>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-none d-lg-block" id="sign-in-dialogs">
            <div class="card">
                <div class="card-header !tw-bg-[#020713]">
                    <h4 class="mb-0 text-white text-center">Booking Summary</h4>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <p class="fw-bolder fs-3 text-center">Available Space</p>
                        <div class="fs-6 fw-bolder text-black text-center" id="availableSpace"><?= $traveller_details->available_space ?>KG</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <p class="fw-bolder fs-3">Items:</p>
                        <div class="fs-2 text-black" id="item-list"></div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <p class="fw-bolder fs-3">Agent:</p>
                        <p class="fs-3 text-black mb-0" id="agentNameValue"></p>
                        <p class="fs-3 text-black" id="agentAddressValue"></p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <p class="fw-bolder fs-3">Receiver:</p>
                        <p class="fs-3 text-black mb-0" id="receiverNameValue"></p>
                        <p class="fs-3 text-black" id="receiverAddressValue"></p>
                    </div>

                    <hr>

                    <?php
                    // Set service charge based on currency.
                    $service_charge = 2.99;
                    $display_symbol = $symbol;
                    ?>
                    <div class="mb-3">
                        <p class="fw-bolder fs-3 total">
                            Selected Space: <span class="text-black tw-float-right"><span id="total-kg"></span> KG</span></span>
                        </p>
                        <p class="fw-bolder fs-3 total service_charge" charge="<?= round($service_charge, 2) ?>">
                            Service Charge: <span class="text-black tw-float-right"><span><?= $display_symbol . number_format($service_charge, 2) ?></span></span>
                        </p>
                        <p class="fw-bolder fs-3 total insurance">
                            Insurance: <span class="text-black tw-float-right"><span id="insurance-value"></span></span>
                        </p>
                        <p class="fw-bolder fs-3 total sub_total">
                            Sub Total: <span class="text-black tw-float-right"><span id="sub-total"></span></span>
                        </p>
                        <p class="fw-bolder fs-3 total special_charge">
                            Special Charge: <span class="text-black tw-float-right"><span id="special-charge-value"></span></span>
                        </p>
                        <p class="fw-bolder fs-3 total">
                            Total: <span class="text-black tw-float-right"><span id="total-price"></span></span>
                        </p>
                    </div>

                    <hr>
                </div>
            </div>
        </div>

        <span class="d-none" id="holdThisInfo"
            currency="<?= $currency ?>"
            pound_sign="&pound;"
            dollar_sign="$"
            naira_sign="&#8358;"
            one_pound="<?= $one_pound ?>"
            one_dollar="<?= $one_dollar ?>"
            symbol="<?= $symbol ?>">
        </span>

        <div class="btn_reserve_fixed text-center d-lg-none">
            <a href="javascript:void(0)" id="sign-in" class="btn btn-primary full-width"> View Summary </a>
        </div>


    </div>
</div>
