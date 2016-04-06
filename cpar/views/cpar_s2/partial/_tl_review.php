<legend class="red_legend title-legend">To be filled-in by Team Leader</legend>

<div class="row">
	<div class="col-md-12">
		<div class="form-group required">
		  <label class="col-md-2 control-label" for="accomplish_by">Should be accomplished by</label>
		  <div class="col-md-2">
		  	<?php
		  		$due_date = '';
		  		//if due_date from addressee_fields is not null/empty, use that instead
		  		if(!($addr_fields->accomplish_by == null || empty($addr_fields->accomplish_by)) && strcmp($addr_fields->accomplish_by, NULL_DATE_ONLY) != 0) {
		  			$due_date = $addr_fields->accomplish_by;
		  		} else {
		  			$due_date = $cpar->date_due;
		  		}

		  		$date_formatted = '';
					if(strcmp($due_date, NULL_DATE_ONLY) != 0) {
						$date = new DateTime($due_date);
						$date_formatted = $date->format('m/d/Y');
					}
		  	?>
		  	<?php 
			  	$l_id = (int)$this->session->userdata('loggedIn');
					$can_edit_date = ($l_id == (int)$cpar->assigned_ims || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);
		  		if($can_edit_date) { ?>
		  		<input id="accomplish_by" name="accomplish_by" type="text" value="<?php echo $date_formatted; ?>" class="past_not_allowed datepicker form-control input-md">
		  	<?php } else { ?>
		  		<input id="accomplish_by" disabled="disabled" type="text" value="<?php echo $date_formatted; ?>" class="past_not_allowed datepicker form-control input-md">
		  	<?php } ?>
		  </div>
		  <div id="update_date_container" class="col-md-2">
		  	<?php if($can_edit_date) { ?>
		  		<a id="update_date_btn" class="btn btn-primary">Update Date</a>
		  	<?php } ?>
		  </div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-8">
		<div class="form_content">
			<fieldset>
				<input type="hidden" name="is_tl_review" value="true" />

				<span class="ims_review_instruction common-pbt"> * 1.&nbsp;&nbsp;Select action to be done for this request.</span>
				

				<!-- Approve -->
				<div class="form-group">
				  <div class="col-md-9 col-md-offset-3">
				    <input id="mark_as_approved" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_S2_MARK_APPR; ?>"/> &nbsp; Approved.
				  </div>
				</div>

				

				<!-- Not Accepted. Return to Addressee for Corrections. -->
				<div class="form-group">
				  <div class="col-md-9 col-md-offset-3">
				    <input id="mark_inv" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_S2_MARK_INV; ?>"/> &nbsp; Not Accepted. Return to Addressee for Corrections.
				  	<br/>
				  	<span id="tl_review_note">Please indicate your reasons and items for correction in the text area provided in Step 2 below.</span>
				  </div>
				</div>

				<hr class="top-dashborder">

				<span class="ims_review_instruction"> * 2.&nbsp;&nbsp;Enter your remarks here.</span>
				<br/>
				

				<!-- Remarks -->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="remarks">Remarks</label>
				  <div class="col-md-8">
				    <textarea id="tl_review_remarks" name="tl_review_remarks" class="form-control"></textarea>
				  </div>
				</div>
				
			</fieldset>
		</div>
	</div>
</div>