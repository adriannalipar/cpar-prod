<div class="row">
	<div class="col-md-12">
		<div class="save_banner alert alert-danger">
			<div class="save_banner_ontent">
				<div class="left-paragraph">
					<span class="sb_header">Have you made any changes to the CPAR record above?</span>
					Please click on the button below to save your updates. Otherwise, you will lose your changes.
				</div>
				<div class="right-buttons">
					<a id="save_cpar_changes_btn" class="submit_btn btn btn-black">
						<i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Save CPAR Changes
					</a>
					&nbsp;
					<a id="send_reminder" class="btn btn-black">
						<i class="glyphicon glyphicon-envelope"></i>&nbsp;Send Reminder
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<legend class="red_legend title-legend">To be filled-up by the IMS</legend>
<div class="row">
	<div class="col-md-8">
		<div class="form_content">
			<fieldset>
				<input type="hidden" name="is_ims_review" value="true" />
				<span class="ims_review_instruction"> * 1.&nbsp;&nbsp;Select action to be done for this request.</span>
				
				<!-- Re-assign -->
								
				<div class="form-group reassign-pbt">
				  <div class="col-md-11 col-md-offset-1">
				    <input id="re_assign" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_RE_ASSIGN; ?>"/> &nbsp; Re-assign.
				  </div>
				</div>
				<div>
					<?php if($cpar->assigned_ims) : ?>
					<!-- Current IMS Assignee -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="re_assign">Current IMS Assignee</label>
					  <div class="col-md-8 control-label" style="text-align:left;">
					    <?php echo $cpar->assigned_ims_name; ?>
					  </div>
					</div>
					
					<!-- Current Review By Date -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="re_assign">Current Review By Date</label>
					  <div class="col-md-8 control-label" style="text-align:left;">
					    <?php echo date('m/d/Y', strtotime($cpar->date_due)); ?>
					  </div>
					</div>
					<?php endif; ?>
					<!-- IMS Assignee -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="re_assign">IMS Assignee</label>
					  <div class="col-md-8">
					    <input id="re_assign_to" name="re_assign_to" type="hidden" class="assigned_ims_name form-control" value='' />
					  </div>
					</div>
					
					<!-- Submit Response By -->
					<div class="form-group">
						<label class="col-md-4 control-label" for="next_due_date">Review By</label>
						<div class="col-md-8">
							<div class="input-group">
								<input id="review_by_due_date" name="review_by_due_date" type="text" class="datepicker form-control">
								<span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
						</div>
					</div>					
				</div>
				
				<!-- Mark as reviewed -->
				<div class="form-group common-pbt">
				  <div class="col-md-11 col-md-offset-1">
				    <input id="mark_as_reviewed" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_MARK_REV; ?>"/> &nbsp; Mark as reviewed.
				  </div>
				</div>
				
				<div>
					<!-- IMS Assignee -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="reviewed_by">IMS Assignee</label>
					  <div class="col-md-8">
					    <input id="reviewed_by" name="reviewed_by" type="hidden" class="assigned_ims_name form-control" value='[{"id":"<?php echo $logged_in_id; ?>","text":"<?php echo $logged_in_name; ?>"}]' />
					  </div>
					</div>

					<!-- Submit Response By -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="next_due_date">Submit Response By</label>
					  <div class="col-md-8">
					    <div class="input-group">
			          <input id="next_due_date" name="next_due_date" type="text" class="datepicker form-control">
			          <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			        </div>
					  </div>
					</div>
				</div>

				<!-- Incomplete Information. Return to Requester for Corrections. -->
				<div class="form-group common-pbt">
				  <div class="col-md-11 col-md-offset-1">
				    <input id="push_back" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_PUSH_BACK; ?>"/> 
				    &nbsp; Incomplete Information. Return to Requester for Corrections.
				  </div>
				</div>
				
				<div>
					<!-- Stage -->
					<div class="form-group">
					  <label class="col-md-4 control-label" for="pb_stage">Stage</label>
					  <div class="col-md-8">
					    <select id="pb_stage" name="pb_stage" class="form-control input-md">
					    	<option value="<?php echo CPAR_STAGE_1 . PUSH_BACK_SEPARATOR . CPAR_MINI_STATUS_PUSHED_BACK; ?>">Stage 1 - CPAR Request</option>
					    </select>
					  </div>
					</div>
				</div>

				<!-- Mark as invalid -->
				<div class="form-group common-pbt">
				  <div class="col-md-11 col-md-offset-1">
				    <input id="mark_as_invalid" type="radio" name="review_action" value="<?php echo REVIEW_ACTIONS_MARK_INV; ?>"/> &nbsp; Mark as invalid.
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