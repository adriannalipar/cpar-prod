<legend class="red_legend title-legend">To be filled-in by IMS</legend>

<div class="row">
	<div class="col-md-12">
		<div class="form-group required">
		  <label class="col-md-2 control-label" for="accomplish_by">Should be accomplished by</label>
		  <div class="col-md-2">
		  	<?php
		  		$due_date = '';
		  		//if due_date from addressee_fields is not null/empty, use that instead
		  		if(isset($addr_fields->accomplish_by) && !($addr_fields->accomplish_by == null || empty($addr_fields->accomplish_by)) && strcmp($addr_fields->accomplish_by, NULL_DATE_ONLY) != 0) {
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
				<input type="hidden" name="is_ims_review" value="true" />
				<span class="ims_review_instruction common-pbt"> * 1.&nbsp;&nbsp;Select action to be done for this request.</span>

				<!-- Mark as reviewed -->
				<div class="form-group">
				  <div class="col-md-11 col-md-offset-1">
				    <input id="mark_as_reviewed" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_S3_MARK_REV; ?>"/> &nbsp; Mark as reviewed.
				  </div>
				</div>
				<div>
					<!-- Follow Up Date -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="next_due_date">Follow Up Date</label>
					  <div class="col-md-8">
					    <div class="input-group">
			          <input id="ff_up_date" name="ff_up_date" type="text" class="past_not_allowed datepicker form-control">
			          <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			        </div>
					  </div>
					</div>
				</div>

				<!-- Incomplete Information. Return to Requester for Corrections. -->
				<div class="form-group common-pbt">
				  <div class="col-md-11 col-md-offset-1">
				    <input id="push_back" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_S3_PUSH_BACK; ?>"/> 
				    &nbsp; Incomplete information. Return to Addressee for corrections.
				  </div>
				</div>
				<div>
					<!-- Stage -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="pb_stage">Stage</label>
					  <div class="col-md-8">
					    <select id="pb_stage" name="pb_stage" class="form-control input-md">
					    	<option value="<?php echo CPAR_STAGE_2 . PUSH_BACK_SEPARATOR . CPAR_MINI_STATUS_S2_2A1; ?>">Stage 2 - For CA/PA</option>
					    </select>
					  </div>
					</div>

					<!-- Next Due Date -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="next_due_date">Next Due Date</label>
					  <div class="col-md-8">
					    <div class="input-group">
			          <input id="next_due_date" name="next_due_date" type="text" class="past_not_allowed datepicker form-control">
			          <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			        </div>
					  </div>
					</div>
				</div>

				<hr class="top-dashborder">

				<span class="ims_review_instruction"> * 2.&nbsp;&nbsp;Enter your remarks here.</span>
				<br/>
				
				<!-- Remarks -->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="remarks">Remarks</label>
				  <div class="col-md-8">
				    <textarea id="remarks" name="remarks" class="form-control"></textarea>
				  </div>
				</div>
				
			</fieldset>
		</div>
	</div>
</div>