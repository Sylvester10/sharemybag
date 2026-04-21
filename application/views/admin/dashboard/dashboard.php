<?php
$tiles = array(
    array('icon' => 'las la-users', 'value' => $total_users, 'label' => 'Total Users'),
    array('icon' => 'las la-suitcase-rolling', 'value' => $total_approved_travellers, 'label' => 'Approved Travellers'),
    array('icon' => 'las la-history', 'value' => $total_pending_travellers, 'label' => 'Pending Travellers'),
    array('icon' => 'las la-briefcase', 'value' => $total_bookings, 'label' => 'Total Bookings'),
);
$this->load->view('admin/partials/stat_tiles', array('tiles' => $tiles));
?>


<div class="panel with-nav-tabs panel-default">
	<div class="panel-heading">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#quick_email" data-toggle="tab">Quick Mail</a></li>
			<li class=""><a href="#bulk_email" data-toggle="tab">Bulk Mail</a></li>
		</ul>
	</div>
	<div class="panel-body">
		<div class="tab-content">

			<div class="tab-pane active" id="quick_email">
				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

						<?php
						echo form_open('message/send_email', 'id="submit_button"'); ?>

						<h3 class="m-b-30 text-bold tw-noselect">Quick Mail</h3>
						<div class="form-group">
							<label class="form-control-label">Email</label>
							<input type="email" name="email" class="form-control" required />
						</div>
						<div class="form-group">
							<label class="form-control-label">Subject</label>
							<input type="text" name="subject" class="form-control" required />
						</div>
						<div class="form-group">
							<label class="form-control-label">Message</label>
							<textarea id="email_message" name="message" class="form-control t100" required></textarea>
						</div>

						<div id="q_status_msg"></div>

						<div class="form-group">
							<button type="submit" id="send_mail_btn" class="btn btn-lg btn-primary">
								<span id="btn_text">Send Mail</span>
								<span id="loading_icon" style="display: none;"><i class="las la-spinner la-spin"></i></span>
							</button>
						</div>

						<?php echo form_close(); ?>

					</div>

				</div>
			</div>

			<div class="tab-pane fade" id="bulk_email">
				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

						<?php
						echo form_open('message/send_bulk_mail', 'id="submit_buttons"'); ?>

						<h3 class="m-b-30 text-bold">Bulk Mail</h3>
						<div class="form-group">
							<label class="form-control-label">Mailing List</label>
							<select class="form-control" name="mailing_list" required>
								<option value="">Select Mailing List</option>
								<option value="all_users">All Users (<?php echo $all_users; ?>)</option>
								<option value="approved_users">Approved Users (<?php echo $approved_users; ?>)</option>
							</select>
						</div>
						<div class="form-group">
							<label class="form-control-label">Subject</label>
							<input type="text" name="subject" class="form-control" required />
						</div>
						<div class="form-group">
							<label class="form-control-label">Message</label>
							<textarea id="email_messages" name="message" class="form-control t100" required></textarea>
						</div>

						<div id="b_status_msg"></div>

						<div class="form-group">
							<button type="submit" id="send_mail_btns" class="btn btn-lg btn-primary">
								<span id="btn_texts">Send Mail</span>
								<span id="loading_icons" style="display: none;"><i class="las la-spinner la-spin"></i></span>
							</button>
						</div>

						<?php echo form_close(); ?>

					</div>

				</div>
			</div>

		</div>
	</div>
</div>
