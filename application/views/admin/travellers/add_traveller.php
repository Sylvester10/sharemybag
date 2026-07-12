<div class="new-item admin-page-actions">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers'); ?>"><i
			class="las la-users"></i> Available Travellers</a>
</div>

<?php
echo form_open_multipart('admin_travellers/add_traveller_ajax', 'id="submit_button"'); ?>

<div class="admin-form-card">
	<p class="admin-form-note">All fields marked * are required.</p>

<div class="row admin-form-grid">

	<div class="col-md-6 col-sm-12 col-xs-12">

		<div class="admin-form-section">
			<h3 class="admin-form-heading">Personal Details</h3>

		<div class="form-group">
			<label class="form-control-label">Name* <small>(Surname first)</small></label>
			<br />
			<input type="text" name="name" value="<?php echo set_value('name'); ?>" class="form-control" required />
			<div class="form-error"><?php echo form_error('name'); ?></div>
		</div>

		<div class="form-group">
			<?php $this->load->view('partials/phone_input', array(
				'wrapper_class' => '',
				'field_name' => 'phone',
				'country_code_name' => 'c_code1',
				'country_code_id' => 'travellerCountryCode',
				'input_id' => 'travellerPhone',
				'country_code' => set_value('c_code1', '+44'),
				'local_number' => set_value('phone'),
				'label' => 'Mobile No',
				'required' => true,
			)); ?>
			<div class="form-error"><?php echo form_error('c_code1'); ?><?php echo form_error('phone'); ?></div>
		</div>

		<div class="form-group">
			<?php $this->load->view('partials/phone_input', array(
				'wrapper_class' => '',
				'field_name' => 'alt_phone',
				'country_code_name' => 'c_code2',
				'country_code_id' => 'travellerAltCountryCode',
				'input_id' => 'travellerAltPhone',
				'country_code' => set_value('c_code2', '+44'),
				'local_number' => set_value('alt_phone'),
				'label' => 'Alternate Mobile No',
			)); ?>
			<div class="form-error"><?php echo form_error('c_code2'); ?><?php echo form_error('alt_phone'); ?></div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Email*</label>
			<br />
			<input type="email" name="email" class="form-control" value="<?php echo set_value('email'); ?>" required />
			<div class="form-error"><?php echo form_error('email'); ?></div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Current Address*</label>
			<br />
			<input type="text" name="address" value="<?php echo set_value('address'); ?>" class="form-control"
				required />
			<div class="form-error"><?php echo form_error('address'); ?></div>
		</div>
		</div>

		<div class="admin-form-section">
			<h3 class="admin-form-heading">Travel Details</h3>

		<div class="form-group">
			<label class="form-control-label">Bag Space*</label>
			<select class="form-control" name="available_space" required>
				<option value="">Select size</option>
				<option value="1">1 KG</option>
				<option value="2">2 KG</option>
				<option value="3">3 KG</option>
				<option value="4">4 KG</option>
				<option value="5">5 KG</option>
				<option value="6">6 KG</option>
				<option value="7">7 KG</option>
				<option value="8">8 KG</option>
				<option value="9">9 KG</option>
			</select>
			<div class="form-error"><?php echo form_error('available_space'); ?></div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Travel Date*</label>
			<div class="input-group date calendar_date_datepicker" data-date-format="yyyy-mm-dd">
				<input type="text" class="form-control" name="travel_date"
					value="<?php echo set_value('travel_date'); ?>" readonly required />
				<div class="input-group-addon">
					<i class="las la-calendar"></i>
				</div>
				<div class="form-error"><?php echo form_error('travel_date'); ?></div>
			</div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Arrival Date</label>
			<div class="input-group date calendar_date_datepicker" data-date-format="yyyy-mm-dd">
				<input type="text" class="form-control" name="arrival_date"
					value="<?php echo set_value('arrival_date'); ?>" readonly required />
				<div class="input-group-addon">
					<i class="las la-calendar"></i>
				</div>
				<div class="form-error"><?php echo form_error('arrival_date'); ?></div>
			</div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Airline*</label>
			<select class="form-control" name="airline" required>
				<option selected value="">Select</option>

				<?php
				$airlines = airlines();
				foreach ($airlines as $airline) { ?>
					<option value="<?php echo $airline; ?>" <?php echo set_select('airline', $airline); ?>><?php echo $airline; ?>
					</option>
				<?php } ?>
			</select>
			<div class="form-error"><?php echo form_error('airline'); ?></div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Country of Residence*</label>
			<select class="form-control" name="location" id="current_location" required>
				<option value="">Select</option>
				<?php
				$countries = countries();
				foreach ($countries as $country) { ?>
					<option value="<?php echo $country; ?>" <?php echo set_select('location', $country); ?>><?php echo $country; ?>
					</option>
				<?php } ?>
			</select>
			<div class="form-error">
				<?php echo form_error('location'); ?>
			</div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Destination Country*</label>
			<select class="form-control" name="destination" required>
				<option value="">Select Destination</option>
				<?php
				$countries = countries();
				foreach ($countries as $country) { ?>
					<option value="<?php echo $country; ?>" <?php echo set_select('nationality', $country); ?>><?php echo $country; ?></option>
				<?php } ?>
			</select>
			<div class="form-error"><?php echo form_error('destination'); ?></div>
		</div>
		</div>

		<!--<div class="form-group">
			<label class="form-control-label">Address on Arrival*</label>
			<br />
			<input type="text" name="destination_address" value="<?php echo set_value('address'); ?>" class="form-control" required />
			<div class="form-error"><?php echo form_error('destination_address'); ?></div>
		</div>-->

	</div><!--/.col-->


	<div class="col-md-6 col-sm-12 col-xs-12">

		<div class="admin-form-section">
			<h3 class="admin-form-heading">Drop-Off Details</h3>

		<div class="form-group" id="state" style="display: none;">
			<label class="form-control-label">State of Residence*</label>
			<select class="form-control" name="current_state">
				<option value="">Select</option>

				<?php
				$states = ng_cities();
				foreach ($states as $state) { ?>
					<option value="<?php echo $state; ?>"><?php echo $state; ?></option>
				<?php }
				?>

			</select>
		</div>

		<div class="form-group" id="dropaddress1" style="display: none;">
			<label class="form-control-label">Drop Off Address (Same a Current Address) <input type="checkbox" id="populateDropAddress" /> </label>
			<br />
			<input type="text" name="drop_address1" value="<?php echo set_value('drop_address1'); ?>"
				class="form-control" />
			<div class="form-error"><?php echo form_error('drop_address1'); ?></div>
		</div>

		<div class="form-group" id="dropdate1" style="display: none;">
			<label class="form-control-label">Drop Off Date*</label>
			<div class="input-group date calendar_date_datepicker" data-date-format="yyyy-mm-dd">
				<input type="text" class="form-control" name="drop_date1" value="<?php echo set_value('drop_date1'); ?>"
					readonly />
				<div class="input-group-addon">
					<i class="las la-calendar"></i>
				</div>
				<div class="form-error"><?php echo form_error('drop_date1'); ?></div>
			</div>
		</div>

		<div class="form-group" id="departurestate" style="display: none;">
			<label class="form-control-label">Departure Airport*</label>
			<select class="form-control" name="departure_state">
				<option value="">Select</option>
				<?php
				$airportstates = airport_states();
				foreach ($airportstates as $airport) { ?>
					<option value="<?php echo $airport; ?>"><?php echo $airport; ?></option>
				<?php }
				?>

			</select>
		</div>

		<div class="form-group" id="dropaddress2" style="display: none;">
			<label class="form-control-label">2nd Drop Off Address*</label>
			<br />
			<input type="text" name="drop_address2" value="<?php echo set_value('drop_address2'); ?>"
				class="form-control" />
			<div class="form-error"><?php echo form_error('drop_address2'); ?></div>
		</div>

		<div class="form-group" id="dropdate2" style="display: none;">
			<label class="form-control-label">2nd Drop Off Date*</label>
			<div class="input-group date calendar_date_datepicker" data-date-format="yyyy-mm-dd">
				<input type="text" class="form-control" name="drop_date2" value="<?php echo set_value('drop_date2'); ?>"
					readonly />
				<div class="input-group-addon">
					<i class="las la-calendar"></i>
				</div>
				<div class="form-error"><?php echo form_error('drop_date2'); ?></div>
			</div>
		</div>
		</div>

		<div class="admin-form-section">
			<h3 class="admin-form-heading">Preferences</h3>

		<div class="form-group">
			<label class="form-control-label">Unwanted Items</label>
			<select multiple class="form-control selectpicker" name="unwanted_items[]">
				<option value="">Select Item(s)</option>
				<option value="Medication">Medication</option>
				<option value="Fish">Fish</option>
				<option value="Tobacco">Tobacco</option>
				<option value="Oil">Oil</option>
				<option value="Cream">Cream</option>
			</select>
			<div class="form-error">
				<?php echo form_error('unwanted_items'); ?>
			</div>
		</div>

		<div class="admin-form-actions">
			<button type="submit" id="send_mail_btn" class="btn btn-lg btn-primary">
				<span id="btn_text">Submit</span>
				<span id="loading_icon" style="display: none;"><i class="las la-spinner la-spin"></i></span>
			</button>
		</div>
		</div>

	</div><!--/.col-->

</div><!--/.row-->
</div>


<?php echo form_close(); ?>
