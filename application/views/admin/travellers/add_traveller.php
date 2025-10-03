<div class="new-item">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers'); ?>"><i
			class="fa fa-users"></i> Available Travellers</a>
</div>

<?php
echo form_open_multipart('admin_travellers/add_traveller_ajax', 'id="submit_button"'); ?>

All fields marked * are required.

<div class="row">

	<div class="col-md-6 col-sm-12 col-xs-12">

		<div class="form-group">
			<label class="form-control-label">Name* <small>(Surname first)</small></label>
			<br />
			<input type="text" name="name" value="<?php echo set_value('name'); ?>" class="form-control" required />
			<div class="form-error"><?php echo form_error('name'); ?></div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Mobile No*</label>
			<br />
			<input type="text" name="phone" value="<?php echo set_value('phone'); ?>" class="form-control numbers-only"
				required />
			<div class="form-error"><?php echo form_error('phone'); ?></div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Alternate Mobile No*</label>
			<br />
			<input type="text" name="alt_phone" value="<?php echo set_value('alt_phone'); ?>"
				class="form-control numbers-only" />
			<div class="form-error"><?php echo form_error('alt_phone'); ?></div>
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
					<i class="fa fa-calendar"></i>
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
					<i class="fa fa-calendar"></i>
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

		<!--<div class="form-group">
			<label class="form-control-label">Address on Arrival*</label>
			<br />
			<input type="text" name="destination_address" value="<?php echo set_value('address'); ?>" class="form-control" required />
			<div class="form-error"><?php echo form_error('destination_address'); ?></div>
		</div>-->

	</div><!--/.col-->


	<div class="col-md-6 col-sm-12 col-xs-12">

		<div class="form-group" id="state" style="display: none;">
			<label class="form-control-label">State of Residence*</label>
			<select class="form-control" name="current_state">
				<option value="">Select</option>

				<?php
				$states = nigerian_states();
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
					<i class="fa fa-calendar"></i>
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
					<i class="fa fa-calendar"></i>
				</div>
				<div class="form-error"><?php echo form_error('drop_date2'); ?></div>
			</div>
		</div>

		<div class="form-group">
			<label class="form-control-label">Unwanted Items*</label>
			<select multiple class="form-control selectpicker" name="unwanted_items[]" required>
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

		<div class="m-t-20">
			<button type="submit" id="send_mail_btn" class="btn btn-lg btn-primary">
				<span id="btn_text">Submit</span>
				<span id="loading_icon" style="display: none;"><i class="fa fa-spinner fa-spin"></i></span>
			</button>
		</div>

	</div><!--/.col-->

</div><!--/.row-->


<?php echo form_close(); ?>