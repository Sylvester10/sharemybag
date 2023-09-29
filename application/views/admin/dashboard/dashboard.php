<div class="row m-b-50">

	<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
		<div class="tile-stats custom-bg-blue">
			<div class="icon"><i class="fa fa-person-circle-check"></i></div>
			<div class="count"><?php echo $total_approved_travellers; ?></div>
			<h3 class="stats-title">Approved Travellers</h3>
		</div>
	</div>
	<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
		<div class="tile-stats custom-bg-blue">
			<div class="icon"><i class="fa fa-clock-rotate-left"></i></div>
			<div class="count"><?php echo $total_pending_travellers; ?></div>
			<h3 class="stats-title">Pending Travellers</h3>
		</div>
	</div>
	<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
		<div class="tile-stats custom-bg-blue">
			<div class="icon"><i class="fa fa-ban"></i></div>
			<div class="count"><?php echo $total_unapproved_travellers; ?></div>
			<h3 class="stats-title">Unapproved Travellers</h3>
		</div>
	</div>
	<!--<div class="animated flipInY col-lg-3 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa fa-envelope"></i></div>
				<div class="count"><?php echo $total_newsletter; ?></div>
				<h3 class="stats-title">Newsletter Subscriptions</h3>
			</div>
		</div>-->

</div>


<div class="panel with-nav-tabs panel-default">
	<div class="panel-heading">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#quick_email" data-toggle="tab">Quick Mail</a></li>
		</ul>
	</div>
	<div class="panel-body">
		<div class="tab-content">

			<div class="tab-pane active" id="quick_email">
				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

						<?php
						echo form_open('message/send_email', 'id="submit_button"'); ?>

						<h3 class="m-b-30 text-bold">Quick Mail</h3>
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
								<span id="loading_icon" style="display: none;"><i
										class="fa fa-spinner fa-spin"></i></span>
							</button>
						</div>

						<?php echo form_close(); ?>

					</div>

				</div>
			</div>

		</div>
	</div>
</div>