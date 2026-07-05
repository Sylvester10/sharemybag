<div class="new-item admin-page-actions">
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers'); ?>"><i class="las la-suitcase-rolling"></i> Available Travellers</a>
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers/pending_travellers'); ?>">
        <i class="las la-history"></i> Pending Travellers
    </a>
</div>

<div class="admin-summary-chip">
    <?= $y->location ?> <i class="las la-plane-departure"></i> <?= $y->destination ?>
</div>

<?php
echo form_open_multipart('admin_travellers/update_traveller_ajax/' . $y->id, 'id="submit_button"'); ?>

<div class="admin-form-section">
    <h3 class="admin-form-heading"><i class="las la-user-circle"></i> Personal Details</h3>

    <div class="row ">

        <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="form-group">
                <label class="form-control-label">Full Name * <small>(Surname first)</small></label>
                <br />
                <input type="text" name="fullname" value="<?php echo set_value('fullname', $y->fullname); ?>" class="form-control" />
                <div class="form-error">
                    <?php echo form_error('fullname'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Phone Number *</label>
                <br />
                <input type="text" name="phone" value="<?php echo set_value('phone', $y->phone); ?>" class="form-control numbers-only" required readonly />
                <div class="form-error">
                    <?php echo form_error('phone'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Street Address *</label>
                <br />
                <input type="text" name="address" value="<?php echo set_value('address', $y->address); ?>" class="form-control" required />
                <div class="form-error">
                    <?php echo form_error('address'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Area * </label>
                <br />
                <input type="text" name="area" value="<?php echo set_value('area', $y->area); ?>" class="form-control" required />
                <div class="form-error">
                    <?php echo form_error('area'); ?>
                </div>
            </div>


            <div class="form-group d-none">
                <label class="form-control-label">Residential Country *</label>
                <br />
                <input type="text" name="location" id="current_location" value="<?php echo set_value('location', $y->location); ?>" class="form-control" required readonly />
                <div class="form-error">
                    <?php echo form_error('location'); ?>
                </div>
            </div>

            <div class="form-group d-none">
                <label class="form-control-label">Destination Country *</label>
                <br />
                <input type="text" name="destination" value="<?php echo set_value('destination', $y->destination); ?>" class="form-control" required readonly />
                <div class="form-error">
                    <?php echo form_error('destination'); ?>
                </div>
            </div>

        </div>
        <!--/.col-->

        <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="form-group">
                <label class="form-control-label">Email *</label>
                <br />
                <input type="email" name="email" class="form-control" value="<?php echo set_value('email', $y->email); ?>" required />
                <div class="form-error">
                    <?php echo form_error('email'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Alt Phone Number *</label>
                <br />
                <input type="text" name="alt_phone" value="<?php echo set_value('alt_phone', $y->alt_phone); ?>" class="form-control numbers-only" readonly />
                <div class="form-error">
                    <?php echo form_error('alt_phone'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">City of Residence *</label>
                <select class="form-control" name="current_state" required>
                    <option selected value="<?php echo $y->current_state; ?>"><?php echo $y->current_state; ?></option>

                    <?php
                    if ($y->location == 'Nigeria') {
                        $states = ng_cities();
                        foreach ($states as $state) { ?>
                            <option value="<?php echo $state; ?>"><?php echo $state; ?></option>
                        <?php }
                    } elseif ($y->location == 'United Kingdom') {
                        $cities = uk_cities();
                        foreach ($cities as $city) { ?>
                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                        <?php }
                    } else {
                        $cities = ca_cities();
                        foreach ($cities as $city) { ?>
                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                    <?php }
                    }
                    ?>

                </select>
            </div>

        </div>

    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-heading"><i class="las la-plane"></i> Travel Details</h3>

    <div class="row ">
        <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="form-group">
                <label class="form-control-label">Bag Space (KG) *</label>
                <input type="text" name="available_space" value="<?php echo set_value('available_space', $y->available_space); ?>" class="form-control" readonly />
                <div class="form-error">
                    <?php echo form_error('available_space'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Travel Date *</label>
                <div class="input-group date calendar_date_datepicker">
                    <input type="text" class="form-control" name="travel_date" value="<?php echo set_value('travel_date', $y->travel_date); ?>" readonly required />
                    <div class="input-group-addon">
                        <i class="las la-calendar"></i>
                    </div>
                    <div class="form-error">
                        <?php echo form_error('travel_date'); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Arrival Date</label>
                <div class="input-group date calendar_date_datepicker">
                    <input type="text" class="form-control" name="arrival_date" value="<?php echo set_value('arrival_date', $y->arrival_date); ?>" readonly required />
                    <div class="input-group-addon"> <i class="las la-calendar"></i> </div>
                    <div class="form-error">
                        <?php echo form_error('arrival_date'); ?>
                    </div>
                </div>
            </div>

            <?php
            // Define the country constants for clarity
            $NIGERIA = 'Nigeria';
            $CANADA = 'Canada';
            $UK = 'United Kingdom';

            ?>

            <div class="form-group">
                <label class="form-control-label">Departure Airport*</label>
                <select class="form-control" name="departure_state" required>
                    <option selected value="<?php echo $y->departure_state; ?>"><?php echo $y->departure_state; ?></option>

                    <?php
                    // Start main conditional check for departure location
                    if ($y->location == $NIGERIA) {
                        // Options for Nigeria Departure
                        $airports = ng_airports();
                        foreach ($airports as $airport) {
                    ?>
                            <option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
                        <?php
                        }
                    } else if ($y->location == $CANADA) {
                        // Options for Canada Departure
                        $airports = ca_airports();
                        foreach ($airports as $airport) {
                        ?>
                            <option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
                        <?php
                        }
                    } else {
                        // Options for United Kingdom Departure (default/other)
                        $airports = uk_airports();
                        foreach ($airports as $airport) {
                        ?>
                            <option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
                    <?php
                        }
                    }
                    // End main conditional check
                    ?>

                </select>
            </div>

            <?php
            if ($y->destination == $NIGERIA) {
                //NIGERIA ARRIVAL
                $airports = ng_airports();
                $cities = ng_cities();
            ?>
                <div class="form-group">
                    <label class="form-control-label">Arrival Airport*</label>
                    <select class="form-control" name="arrival_airport">
                        <option selected value="<?php echo $y->arrival_airport; ?>"><?php echo $y->arrival_airport; ?></option>
                        <?php
                        foreach ($airports as $airport) { ?>
                            <option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Final Destination*</label>
                    <select class="form-control" name="arrival_state">
                        <option selected value="<?php echo $y->arrival_state; ?>"><?php echo $y->arrival_state; ?></option>
                        <?php
                        foreach ($cities as $city) { ?>
                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                        <?php } ?>
                    </select>
                </div>

            <?php } else if ($y->destination == $CANADA) {
                // CANADA ARRIVAL
                $airports = ca_airports();
                $cities = ca_cities();
            ?>
                <div class="form-group">
                    <label class="form-control-label">Arrival Airport*</label>
                    <select class="form-control" name="arrival_airport">
                        <option selected value="<?php echo $y->arrival_airport; ?>"><?php echo $y->arrival_airport; ?></option>
                        <?php
                        foreach ($airports as $airport) { ?>
                            <option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Final Destination*</label>
                    <select class="form-control" name="arrival_state">
                        <option selected value="<?php echo $y->arrival_state; ?>"><?php echo $y->arrival_state; ?></option>
                        <?php
                        foreach ($cities as $city) { ?>
                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                        <?php } ?>
                    </select>
                </div>

            <?php } else {
                // UNITED KINGDOM ARRIVAL
                $airports = uk_airports(); // Assuming this helper function is available
                $cities = uk_cities(); // This function was referenced in your original code
            ?>

                <div class="form-group">
                    <label class="form-control-label">Arrival Airport*</label>
                    <select class="form-control" name="arrival_airport">
                        <option selected value="<?php echo $y->arrival_airport; ?>"><?php echo $y->arrival_airport; ?></option>
                        <?php
                        foreach ($airports as $airport) { ?>
                            <option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Final Destination*</label>
                    <select class="form-control" name="arrival_state">
                        <option selected value="<?php echo $y->arrival_state; ?>"><?php echo $y->arrival_state; ?></option>
                        <?php
                        foreach ($cities as $city) { ?>
                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php }
            ?>

            <div class="form-group">
                <label class="form-control-label">Final Destination Area <small>(optional)</small></label>
                <input type="text" name="destination_area" value="<?php echo set_value('destination_area', isset($y->destination_area) ? $y->destination_area : ''); ?>" class="form-control" maxlength="150" placeholder="e.g. Lekki, Mississauga, Birmingham city centre" />
                <div class="form-error">
                    <?php echo form_error('destination_area'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Airline *</label>
                <select class="form-control" name="airline" required>
                    <option selected value="<?php echo $y->airline; ?>"><?php echo $y->airline; ?></option>
                    <?php
                    $airlines = airlines();
                    foreach ($airlines as $airline) { ?>
                        <option value="<?php echo $airline; ?>" <?php echo set_select('airline', $airline); ?>><?php echo $airline; ?></option>
                    <?php } ?>
                </select>
                <div class="form-error">
                    <?php echo form_error('airline'); ?>
                </div>
            </div>

        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">

            <div class="form-group">
                <label class="form-control-label">First Drop Off Address <input type="checkbox" id="populateDropAddress" /> </label>
                <br />
                <input type="text" name="drop_address1" value="<?php echo set_value('drop_address1', $y->drop_address1); ?>" class="form-control" />
                <div class="form-error">
                    <?php echo form_error('drop_address1'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">First Drop Off Date (optional)</label>
                <div class="input-group date calendar_date_datepicker" data-date-format="yyyy-mm-dd">
                    <input type="text" class="form-control" name="drop_date1" value="<?php echo set_value('drop_date1', $y->drop_date1); ?>" readonly />
                    <div class="input-group-addon">
                        <i class="las la-calendar"></i>
                    </div>
                    <div class="form-error">
                        <?php echo form_error('drop_date1'); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Last Drop Off Address * (Same a Street Address?) <input type="checkbox" id="populateDropAddress2" /> </label>
                <br />
                <input type="text" name="drop_address2" value="<?php echo set_value('drop_address2', $y->drop_address2); ?>" class="form-control" />
                <div class="form-error">
                    <?php echo form_error('drop_address2'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Last Drop Off Date *</label>
                <div class="input-group date calendar_date_datepicker" data-date-format="yyyy-mm-dd">
                    <input type="text" class="form-control" name="drop_date2" value="<?php echo set_value('drop_date2', $y->drop_date2); ?>" readonly />
                    <div class="input-group-addon">
                        <i class="las la-calendar"></i>
                    </div>
                    <div class="form-error">
                        <?php echo form_error('drop_date2'); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Unwanted Items</label>
                <select multiple class="form-control selectpicker" name="unwanted_items[]">
                    <?php
                    $selected_items = explode(', ', $y->unwanted_items ?? '');
                    $all_items = ['Medication', 'Fish', 'Tobacco', 'Oil', 'Cream', 'Food Items', 'Fresh Items', 'Frozen Items'];
                    foreach ($all_items as $item) { ?>
                        <option value="<?php echo $item; ?>" <?php echo in_array($item, $selected_items) ? 'selected' : ''; ?>>
                            <?php echo $item; ?>
                        </option>
                    <?php } ?>
                </select>
                <div class="form-error">
                    <?php echo form_error('unwanted_items'); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label">Additional Information</label>
                <textarea class="form-control t200" name="additional_info"><?php echo set_value('additional_info', strip_tags($y->additional_info)); ?></textarea>
                <div class="form-error"><?php echo form_error('additional_info'); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row ">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="admin-form-actions">
            <button type="submit" id="send_mail_btn" class="btn btn-lg btn-primary">
                <span id="btn_text">Update Traveller</span>
                <span id="loading_icon" style="display: none;"><i class="las la-spinner la-spin"></i></span>
            </button>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
