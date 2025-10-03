

    <div class="row">
      	<div class="col-sm-4 mail_list_column">
        	
        	<?php
			if ($total_records > 0) { 

				//select options bulk actions 
				$options_array = array(
					//'value' => 'Caption'
					'delete' => 'Delete'
				); 
				echo modal_bulk_actions_alt('message/bulk_actions_messages', $options_array); ?>
					
					<hr />

					<?php
					foreach ($messages as $y) { 

						//delete confirm modal
      					echo modal_delete_confirm($y->id, $y->name, 'message', 'message/delete_message'); ?>

			    		<a href="<?php echo base_url('message/single_message/'.$y->id); ?>">
			      			<div class="mail_list">
			        			<div class="left">
			          				<?php echo checkbox_bulk_action($y->id); ?>
			          				<a type="button" href="#!" data-toggle="modal" data-target="#delete<?php echo $y->id; ?>"><i class="fa fa-trash text-danger"></i></a>
			        			</div>
			        			<div class="right">
			        				<h3>
			        					<a href="<?php echo base_url('message/single_message/'.$y->id); ?>"><?php echo $y->name; ?>
			        				 	<small><?php echo time_ago($y->date_sent); ?></small>
			        				</h3>		
			          				<p><?php echo $y->subject; ?></p>
			       		 		</div>
			      			</div>
			    		</a>

			    	<?php } //endforeach ?>

    			<?php echo form_close(); ?>

    		<?php } //endif ?>

    		<!--Pagination Links-->
			<?php echo pagination_links($links, 'pagination'); ?>

      	</div>
      	<!-- /MAIL LIST -->




      	<!-- Message Details -->

      	<?php
      	//reply user modal
      	//echo modal_message_user($message->id, $message->name, 'message/reply_message');

      	//delete confirm modal
      	echo modal_delete_confirm($message->id, $message->name, 'message', 'message/delete_message'); ?>

      	<div class="col-sm-8 mail_view">
        	<div class="inbox-body">
          		<div class="mail_heading row">
            		<div class="col-md-8">
              			<div class="btn-group">
                			<!--<button class="btn btn-sm btn-primary" type="button" data-toggle="modal" data-target="#message<?php echo $message->id; ?>"><i class="fa fa-reply"></i> Reply</button>-->
                			<button class="btn btn-sm btn-danger" type="button" data-toggle="modal" data-target="#delete<?php echo $message->id; ?>"><i class="fa fa-trash"></i> Delete</button>
              			</div>
            		</div>
            		<div class="col-md-4 text-right">
              			<p class="date"><?php echo x_date($message->date_sent); ?> at <?php echo x_time_12hour($message->date_sent); ?></p>
            		</div>
            	</div>
            	<div class="row m-b-20" style="border-bottom: 1px solid #f2f2f2;">
            		<div class="col-md-12">
              			<h3><?php echo $message->subject; ?></h3>
            		</div>
          		</div>
          		<div class="sender-info">
            		<div class="row">
              			<div class="col-md-12">
                			<i class="fa fa-user"></i> <?php echo $message->name; ?> <br />
                			<i class="fa fa-envelope"></i> <?php echo $message->email; ?>
              			</div>
            		</div>
          		</div>
          		<div class="view-mail m-t-20">
            		<p><?php echo $message->message; ?></p>
          		</div>

       	 	</div>

      	</div>
      	<!-- /CONTENT MAIL -->

    </div><!-- /.row -->