<?php
// Session-owned intent token also protects these forms when global CSRF is disabled.
$mail_token = (string) $this->session->userdata('admin_mail_token');
if ($mail_token === '') {
    $mail_token = bin2hex(random_bytes(32));
    $this->session->set_userdata('admin_mail_token', $mail_token);
}
$mail_hidden = array('admin_mail_token' => $mail_token);
echo '<div class="admin-dashboard-summary">';
$this->load->view('admin/partials/stat_tiles', array('tiles' => $overview['tiles']));
echo '</div>';
$this->load->view('admin/dashboard/activity', array('overview' => $overview));
?>

<?php if ($can_message): ?>
<div class="panel with-nav-tabs panel-default">
	<div class="panel-heading">
		<h4>Messaging</h4>
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
						echo form_open('message/send_email', 'id="submit_button"', $mail_hidden); ?>

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
						echo form_open('message/send_bulk_mail', 'id="submit_buttons"', $mail_hidden); ?>

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
								<span id="btn_texts">Queue Mail</span>
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
<?php endif; ?>
