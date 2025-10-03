<div class="new-item">
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers'); ?>"><i class="fa-solid fa-person-walking-luggage"></i> Available Travellers</a>
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers/pending'); ?>">
        <i class="fa-solid fa-clock-rotate-left"></i> Pending Travellers
    </a>
</div>

<div class="tw-text-[25px] max-[460px]:tw-text-[20px] tw-text-center tw-d-flex tw-mt-4">
    <?= $y->location ?> <i class="fa-solid fa-plane-departure"></i> <?= $y->destination ?>
</div>

<hr>

<?php
echo form_open_multipart('admin_travellers/recycle_traveller_ajax/' . $y->id, 'id="submit_button"', 'target="_blank"'); ?>

<!-- Itinerary details -->
<div class="tw-text-[20px] max-[460px]:tw-text-[20px] tw-mt-3 tw-mb-4">
    <i class="fa-solid fa-ticket"></i> Itinerary Details
</div>

<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12">

        <div class="form-group">
            <label class="form-control-label">Upload Itinerary
                <small>(Optional. Only jpg and png files allowed. Max 2MB)</small>
            </label>
            <input type="file" name="itinerary_photo" id="the_image" class="form-control" accept=".jpeg,.jpg,.png" required />
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-xs-12">
        <!-- Image preview-->
        <?php echo generate_image_preview(); ?>
    </div>

</div>


<!-- Personal details -->
<div class="tw-text-[20px] max-[460px]:tw-text-[20px] tw-mt-3 tw-mb-4">
    <i class="fa-solid fa-user-circle"></i> Personal Details
</div>

<div class="row">

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
                    $states = nigerian_states();
                    foreach ($states as $state) { ?>
                        <option value="<?php echo $state; ?>"><?php echo $state; ?></option>
                    <?php }
                } else {
                    $cities = english_cities();
                    foreach ($cities as $city) { ?>
                        <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                <?php }
                }
                ?>

            </select>
        </div>

    </div>

</div>

<!-- Travel details -->
<div class="tw-text-[20px] max-[460px]:tw-text-[20px] tw-mt-3 tw-mb-4 mt-5">
    <i class="fa-solid fa-plane"></i> Travel Details
</div>

<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12">

        <div class="form-group">
            <label class="form-control-label">Bag Space *</label>
            <select class="form-control" name="available_space" required>
                <option selected value="<?php echo $y->available_space; ?>"><?php echo $y->available_space; ?> KG</option>
                <?php
                $available_spaces = kilogram();
                foreach ($available_spaces as $available_space) { ?>
                    <option value="<?php echo $available_space; ?>" <?php echo set_select('available_space', $available_space); ?>>
                        <?php echo $available_space; ?> KG
                    </option>
                <?php } ?>
            </select>
            <div class="form-error">
                <?php echo form_error('available_space'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-control-label">Travel Date *</label>
            <div class="input-group date calendar_date_datepicker">
                <input type="text" class="form-control" name="travel_date" value="<?php echo set_value('travel_date', $y->travel_date); ?>" readonly required />
                <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
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
                <div class="input-group-addon"> <i class="fa fa-calendar"></i> </div>
                <div class="form-error">
                    <?php echo form_error('arrival_date'); ?>
                </div>
            </div>
        </div>


        <div class="form-group">
            <label class="form-control-label">Departure Airport*</label>
            <select class="form-control" name="departure_state" required>
                <option selected value="<?php echo $y->departure_state; ?>"><?php echo $y->departure_state; ?></option>

                <?php if ($y->location == 'Nigeria') { ?>

                    <option value="Abuja International Airport">Abuja International Airport</option>
                    <option value="Lagos International Airport">Lagos International Airport</option>

                <?php  } else { ?>

                    <option value="Birmingham Airport">Birmingham Airport</option>
                    <option value="Bristol Airport">Bristol Airport</option>
                    <option value="Cardiff Airport">Cardiff Airport</option>
                    <option value="East Midlands Airport">East Midlands Airport</option>
                    <option value="Liverpool John Lennon Airport">Liverpool John Lennon Airport</option>
                    <option value="London City Airport">London City Airport</option>
                    <option value="London Gatwick Airport">London Gatwick Airport</option>
                    <option value="London Heathrow Airport">London Heathrow Airport</option>
                    <option value="London Luton Airport">London Luton Airport</option>
                    <option value="London Stansted Airport">London Stansted Airport</option>
                    <option value="Manchester Airport">Manchester Airport</option>
                    <option value="Newcastle International Airport">Newcastle International Airport</option>
                    <option value="Southampton Airport">Southampton Airport</option>
                    <option value="Leeds Bradford Airport">Leeds Bradford Airport</option>

                <?php  } ?>

            </select>
        </div>


        <?php if ($y->destination == 'Nigeria') { ?>

            <!-- arrival airport -->
            <div class="form-group">
                <label class="form-control-label">Arrival Airport*</label>
                <select class="form-control" name="arrival_airport">
                    <option selected value="<?php echo $y->arrival_airport; ?>"><?php echo $y->arrival_airport; ?></option>
                    <option value="Abuja International Airport">Abuja International Airport</option>
                    <option value="Lagos International Airport">Lagos International Airport</option>
                </select>
            </div>

            <!-- final destination -->
            <div class="form-group">
                <label class="form-control-label">Final Destination*</label>
                <select class="form-control" name="arrival_state">
                    <option selected value="<?php echo $y->arrival_state; ?>"><?php echo $y->arrival_state; ?></option>
                    <?php
                    $states = nigerian_states();
                    foreach ($states as $state) { ?>
                        <option value="<?php echo $state; ?>"><?php echo $state; ?></option>
                    <?php } ?>
                </select>
            </div>

        <?php } else { ?>

            <div class="form-group">
                <label class="form-control-label">Arrival Airport*</label>
                <select class="form-control" name="arrival_airport">
                    <option selected value="<?php echo $y->arrival_airport; ?>"><?php echo $y->arrival_airport; ?></option>
                    <option value="Birmingham Airport">Birmingham Airport</option>
                    <option value="Bristol Airport">Bristol Airport</option>
                    <option value="Cardiff Airport">Cardiff Airport</option>
                    <option value="East Midlands Airport">East Midlands Airport</option>
                    <option value="Liverpool John Lennon Airport">Liverpool John Lennon Airport</option>
                    <option value="London City Airport">London City Airport</option>
                    <option value="London Gatwick Airport">London Gatwick Airport</option>
                    <option value="London Heathrow Airport">London Heathrow Airport</option>
                    <option value="London Luton Airport">London Luton Airport</option>
                    <option value="London Stansted Airport">London Stansted Airport</option>
                    <option value="Manchester Airport">Manchester Airport</option>
                    <option value="Newcastle International Airport">Newcastle International Airport</option>
                    <option value="Southampton Airport">Southampton Airport</option>
                    <option value="Leeds Bradford Airport">Leeds Bradford Airport</option>
                </select>
            </div>

            <!-- final destination -->
            <div class="form-group">
                <label class="form-control-label">Final Destination*</label>
                <select class="form-control" name="arrival_state">
                    <option selected value="<?php echo $y->arrival_state; ?>"><?php echo $y->arrival_state; ?></option>
                    <?php
                    $states = english_cities();
                    foreach ($states as $state) { ?>
                        <option value="<?php echo $state; ?>"><?php echo $state; ?></option>
                    <?php } ?>
                </select>
            </div>

        <?php } ?>

        <div class="form-group">
            <label class="form-control-label">Airline *</label>
            <select class="form-control" name="airline" required>
                <option selected value="<?php echo $y->airline; ?>"><?php echo $y->airline; ?></option>
                <?php
                $airlines = airlines();
                foreach ($airlines as $airline) { ?>
                    <option value="<?php echo $airline; ?>" <?php echo set_select('airline', $airline); ?>><?php echo $airline; ?>
                    </option>
                <?php } ?>
            </select>
            <div class="form-error">
                <?php echo form_error('airline'); ?>
            </div>
        </div>

    </div>

    <div class="col-md-6 col-sm-12 col-xs-12">

        <div class="form-group">
            <label class="form-control-label">First Drop Off Address * (Same a Street Address?) <input type="checkbox" id="populateDropAddress" /> </label>
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
                    <i class="fa fa-calendar"></i>
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
                    <i class="fa fa-calendar"></i>
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
                $all_items = ['Medication', 'Fish', 'Tobacco', 'Oil', 'Cream', 'Food Items', 'Fresh Items'];
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

<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="m-t-20">
            <button type="submit" id="send_mail_btn" class="btn btn-lg btn-primary">
                <span id="btn_text">Update Traveller</span>
                <span id="loading_icon" style="display: none;"><i class="fa fa-spinner fa-spin"></i></span>
            </button>
        </div>
    </div>
</div>


<?php echo form_close(); ?>