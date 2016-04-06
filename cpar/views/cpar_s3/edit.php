<script src="/_js/cpar_s3/edit.js"></script>
<script src="/_js/save_whole_cpar.js"></script>
<script src="/_js/select2/select2.min.js"></script>
<link href="/_js/select2/select2.css" rel="stylesheet">
<link href="/_js/select2/select2-bootstrap.css" rel="stylesheet">

<!-- CONTENT -->
<div id="content">
  <div class="container">
	<script>
		var is_ims = <?php echo (strcmp($this->session->userdata('loggedIn'), $cpar->assigned_ims) == 0 || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG) ? 'true' : 'false'; ?>;
		var is_addressee = <?php echo (strcmp($this->session->userdata('loggedIn'), $cpar->addressee) == 0) ? 'true' : 'false'; ?>;
		
		//validation for verified tasks
		var TASK_STATUS_VERIFIED = "<?php echo $verified_string; ?>";
	</script>

    <!-- Steps -->
	<?php 
		$this->load->view('partials/steps', array(
													'step_title' => 'Stage 3 <span class="de-em">CPAR</span>', 
													'step_sub_header' => $header_text, 
													'step_sub_header_style' => 'font-size: 14px;',
													'step_active' => 3
												)
		); 
	?>

    <!-- errors -->
    <div class="row">
    	<div class="col-md-12">
				<div class="error_container alert alert-danger">
					<button type="button" class="close custom_alert_hide">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
					<div class="error_content">
					</div>
				</div>
			</div>
    </div>

    <?php
    	$view_suffix = 'view';
    	$is_disabled = 'disabled="disabled"';
    	$is_addressee = (strcmp($this->session->userdata('loggedIn'), $cpar->addressee) == 0);
    	if($can_edit_cpar) {
    		$view_suffix = 'edit';
    		$is_disabled = '';
    	}
    ?>

    <input type="hidden" id="for_s3_edit_only" value="true" />

    <form id="cpar_form" class="form-horizontal basic_form" enctype="multipart/form-data">
    	<input type="hidden" id="cpar_no" name="id" value="<?php echo $cpar->id; ?>" />

    	<!-- CPAR Information -->
	    <div class="row">
	    	<?php 
	    		$sub_form = array('main_form_edit_disabled' => TRUE);
	    		
	    		if($can_edit_cpar) {
	    			$sub_form = array('main_form_edit_disabled' => FALSE);
	    		}
	    		
	    		$this->load->view('partials/main_form_edit', $sub_form); 
	    	?>
	    	<?php require_once('partial/_cpar_form_' . $view_suffix . '.php'); ?>
	    </div>

	    <hr class="top-separator">
	    <!-- Addressed to / Requestor or Originator -->
	    <?php require_once('partial/_addressee_requestor_info_' . $view_suffix . '.php'); ?>

	    
	    <?php
				$action_header = '';
				if((int)$cpar->type == CPAR_TYPE_C) {
					$action_header = 'Corrective';
				} else if((int)$cpar->type == CPAR_TYPE_P) {
					$action_header = 'Preventive';
				}
			?>

			<div class="row">
				<div class="col-md-12">
					<!-- <legend class="red_legend edit"><?php echo strtoupper($action_header); ?> ACTIONS</legend> -->
					<legend class="red_legend edit title-legend">TO BE FILLED IN BY THE ADDRESSEE</legend>	
				</div>
			</div>
			
			
			<?php if((int)$cpar->type == CPAR_TYPE_C) { ?>
				<div id="car_fields_wrapper">
					<fieldset>
						<legend class="red_legend indented">Remedial Action Details</legend>

						<!-- Immediate Remedial Action -->
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="immediate_remedial_action">Immediate Remedial Action</label>
						  <div class="col-md-6">
						    <textarea <?php echo $is_disabled; ?> id="immediate_remedial_action" name="immediate_remedial_action" class="form-control"><?php echo $addr_fields->rad_action; ?></textarea>
						  </div>
						</div>

						<!-- Implemented By -->
						<?php
							$implemented_by = '';
							if(!($addr_fields->rad_implemented_by == null || empty($addr_fields->rad_implemented_by))) {
								$implemented_by = '[{"id":"' . $addr_fields->rad_implemented_by . '","text":"' . $addr_fields->rad_implemented_by_name . '"}]';
							} else {
								$implemented_by = '[{"id":"' . $cpar->addressee . '","text":"' . $cpar->addressee_name . '"}]';
							}
						?>
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="implemented_by">Implemented By</label>  
						  <div class="col-md-6">
						  	<input <?php echo $is_disabled; ?> id="implemented_by" name="implemented_by" type="hidden" class="adr_name form-control" value='<?php echo $implemented_by; ?>' />
						  </div>
						</div>

						<!-- Date Implemented -->
						<?php
							$date_formatted = '';
							if(!(strcmp($addr_fields->rad_implemented_date, NULL_DATE_ONLY) == 0 || empty($addr_fields->rad_implemented_date))) {
								$date = new DateTime($addr_fields->rad_implemented_date);
								$date_formatted = $date->format('m/d/Y');
							}
						?>
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="date_implemented">Date Implemented</label>  
						  <div class="col-md-6">
						  	<div class="input-group date">
		              <input <?php echo $is_disabled; ?> id="date_implemented" name="date_implemented" type="text" value="<?php echo $date_formatted; ?>" class="datepicker form-control input-md">
		              <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		            </div>
						  </div>
						</div>

						<!-- (RAD) Supporting Evidences -->
						<div class="attachments_form_group form-group">
						  <label class="col-md-2 control-label" for="rad_attachments">Supporting Evidence</label>  
						  <div class="col-md-6">
						  	<?php if($can_edit_cpar) { ?>
						  		<span class="attachment_notice">* If record or evidence is available, please attach files.</span>
						  	<?php } ?>
						  	<div id="rad_attachments_container" style="padding-top: 7px;">
						  		<?php 
						  			if(!($rad_filenames == null || empty($rad_filenames))) {
						  				foreach ($rad_filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
						  					<div class="uploaded_file">
									  			<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a>&nbsp;&nbsp;&nbsp;
									  			<?php if($can_edit_cpar) { ?>
									  				<i id="remove_rad_attachment" class="remove_attachment glyphicon glyphicon-remove"></i>
									  			<?php } ?>
									  		</div>
						  		<?php 
						  				endforeach;
						  			}
						  		?>
						  	</div>
						  	<?php if($can_edit_cpar) { ?>
							  	<br/>
							  	<button id="rad_add_attachment" type="button" class="add_attachment btn btn-danger">
							  		<i class="glyphicon glyphicon-upload"></i>&nbsp;Add File
							  	</button>
							  <?php } ?>
						  </div>
						</div>

					</fieldset>

					
					<hr class="lightline">
					

					<fieldset>
						<legend class="red_legend indented">Root Cause Analysis</legend>

						<!-- Tools Used -->
						<div class="form-group">
						  <label class="col-md-2 control-label">Tools Used</label>
						  <?php 
						  
						  list($column1, $column2, $column3, $b_has_others, $obj_others) = generate_rca_tools($rca_tools); 
							  
						  ?>
						  <?php $tools_used_arr = explode(', ', $addr_fields->rca_tools); ?>
						  <div class="col-md-3">
						  	<?php 
						  		
						  		foreach ($column1 as $tool) {
						  			$selected = '';
						  			if(in_array($tool['id'], $tools_used_arr)) {
						  				$selected = 'checked="checked"';
						  			} 
						  	?>
						  		<input type="checkbox" <?php echo $is_disabled; ?> <?php echo $selected; ?> name="tools_used[]" value="<?php echo $tool['id']; ?>"/>&nbsp;&nbsp;<?php echo $tool['name']; ?>
						  		<br/>
						  	<?php
						  		} 
						  	?>
						  </div>
						  <div class="col-md-3">
						  	<?php 
						  		
						  		foreach ($column2 as $tool) {
						  			$selected = '';
						  			if(in_array($tool['id'], $tools_used_arr)) {
						  				$selected = 'checked="checked"';
						  			} 
						  	?>
						  		<input type="checkbox" <?php echo $is_disabled; ?> <?php echo $selected; ?> name="tools_used[]" value="<?php echo $tool['id']; ?>"/>&nbsp;&nbsp;<?php echo $tool['name']; ?>
						  		<br/>
						  	<?php
						  		} 
						  	?>
						  </div>
						  <div class="col-md-3">
						  	<?php 
						  		
						  		foreach ($column3 as $tool) {
						  			$selected = '';
						  			if(in_array($tool['id'], $tools_used_arr)) {
						  				$selected = 'checked="checked"';
						  			} 
						  	?>
						  		<input type="checkbox" <?php echo $is_disabled; ?> <?php echo $selected; ?> name="tools_used[]" value="<?php echo $tool['id']; ?>"/>&nbsp;&nbsp;<?php echo $tool['name']; ?>
						  		<br/>
						  	<?php
						  		} 
						  	?>
						  </div>
						</div>
						<?php if($b_has_others) { ?>
						<?php
							$selected = '';
							
							if(in_array($obj_others['id'], $tools_used_arr)) {
				  				$selected = 'checked="checked"';
				  			}
						?>
						<div class="form-group">
						  <label class="col-md-2 control-label">&nbsp;</label>
						  <div class="col-md-2 others">
						  		<input type="checkbox" <?php echo $is_disabled; ?> <?php echo $selected; ?> name="tools_used[]" value="<?php echo $obj_others['id']; ?>"/>&nbsp;&nbsp;Others, please specify:
						  </div>
						  <div class="col-md-4 others-field">
						  		<input id="other_tools_used" <?php echo $is_disabled; ?> name="other_tools_used" type="text" value="<?php echo $addr_fields->rca_tools_others; ?>" class="form-control input-md">
						  </div>
						</div>
						<?php } ?>

						<!-- Details / Result of Root Cause Analysis -->
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="rca_details">Details / Result of Root Cause Analysis</label>
						  <div class="col-md-6">
						    <textarea id="rca_details" <?php echo $is_disabled; ?> name="rca_details" class="form-control"><?php echo $addr_fields->rca_details; ?></textarea>
						  </div>
						</div>

						<!-- Investigated By -->
						<?php
							$investigated_by = '';
							if(!($addr_fields->rca_investigated_by == null || empty($addr_fields->rca_investigated_by))) {
								$investigated_by = '[{"id":"' . $addr_fields->rca_investigated_by . '","text":"' . $addr_fields->rca_investigated_by_name . '"}]';
							}
						?>
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="investigated_by">Investigated By</label>  
						  <div class="col-md-4">
						  	<input <?php echo $is_disabled; ?> id="investigated_by" name="investigated_by" type="hidden" class="adr_name form-control" value='<?php echo $investigated_by; ?>' />
						  </div>
						</div>

						<!-- Date Investigation (Start) and (End) -->
						<div class="form-group required">
							<?php
								$date_formatted = '';
								if(!(strcmp($addr_fields->rca_investigated_date_started, NULL_DATE_ONLY) == 0 || empty($addr_fields->rca_investigated_date_started))) {
									$date = new DateTime($addr_fields->rca_investigated_date_started);
									$date_formatted = $date->format('m/d/Y');
								}
							?>
						  <label class="col-md-2 control-label" for="date_investigation_started">Date Investigation (Started)</label>
						  <div class="col-md-4">
						  	<div class="input-group date">
		              <input <?php echo $is_disabled; ?> id="date_investigation_started" name="date_investigation_started" type="text" value="<?php echo $date_formatted; ?>" class="datepicker form-control input-md">
		              <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		            </div>
						  </div>

						  <label class="col-md-2 control-label" for="date_investigation_ended">Date Investigation (Ended)</label>
						  <?php
								$date_formatted = '';
								if(!(strcmp($addr_fields->rca_investigated_date_ended, NULL_DATE_ONLY) == 0 || empty($addr_fields->rca_investigated_date_ended))) {
									$date = new DateTime($addr_fields->rca_investigated_date_ended);
									$date_formatted = $date->format('m/d/Y');
								}
							?>
						  <div class="col-md-4">
						  	<div class="input-group date">
		              <input <?php echo $is_disabled; ?> id="date_investigation_ended" name="date_investigation_ended" type="text" value="<?php echo $date_formatted; ?>" class="datepicker form-control input-md">
		              <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		            </div>
						  </div>
						</div>

						<!-- (RCA) Supporting Evidences -->
						<div class="attachments_form_group form-group">
						  <label class="col-md-2 control-label" for="rca_attachments">Supporting Evidence</label>  
						  <div class="col-md-6">
						  	<?php if($can_edit_cpar) { ?>
						  		<span class="attachment_notice">* If record or evidence is available, please attach files.</span>
						  	<?php } ?>
						  	<div id="rca_attachments_container" style="padding-top: 7px;">
						  		<?php
						  			if(!($rca_filenames == null || empty($rca_filenames))) {
						  				foreach ($rca_filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
						  					<div class="uploaded_file">
									  			<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a>&nbsp;&nbsp;&nbsp;
									  			<?php if($can_edit_cpar) { ?>
									  				<i id="remove_rca_attachment" class="remove_attachment glyphicon glyphicon-remove"></i>
									  			<?php } ?>
									  		</div>
						  		<?php 
						  				endforeach;
						  			}
						  		?>
						  	</div>
						  	<?php if($can_edit_cpar) { ?>
							  	<br/>
							  	<button id="rca_add_attachment" type="button" class="add_attachment btn btn-danger">
							  		<i class="glyphicon glyphicon-upload"></i>&nbsp;Add File
							  	</button>
							  <?php } ?>
						  </div>
						</div>
					</fieldset>

					
				</div> <!-- end of CAR fields wrapper -->
			<?php } ?>

			<?php if((int)$cpar->type == CPAR_TYPE_C) { ?>
				<hr class="lightline">
			<?php } ?>

			<?php
			
				$this->load->view('partials/action_plan', array('action_header' => $action_header, 'is_disabled' => $is_disabled, 'ap_required' => true));
			
			?>

			<br/>
			

			<legend class="red_legend super_indented"><?php echo $action_header; ?> Action<?php echo (strtolower($action_header) == 'preventive') ? ' <i>(Continual Improvement Action)</i>' : ''; ?> Plan Details</legend>

			<!-- errors -->
	    <div class="row">
	    	<div class="col-md-11 col-md-offset-1">
					<div class="mini_error_container alert alert-danger">
						<button type="button" class="close custom_alert_hide">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<div class="mini_error_content">
						</div>
					</div>
				</div>
	    </div>

			
			<div class="row">
				<div class="col-md-11 col-md-offset-1">
					<input type="hidden" id="is_review" value='true' />
					<input type="hidden" id="serialized_tasks" value='<?php echo urlencode($serialized_tasks); ?>' />

					<?php if($can_edit_cpar) { ?>
						<input type="hidden" id="display_tasks_for_ims" value='true' />
					<?php	} else if($is_addressee) { ?>
						<input type="hidden" id="display_tasks_for_addressee" value='true' />
					<?php	} ?>

					<table id="task_tbl" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th class="corr_th_task">Task</th>
								<th class="corr_th_responsible_person">Responsible Person</th>
								<th class="corr_th_due_date center">Due Date</th>
								<th class="corr_th_completed_date center">Completed Date</th>
								<th class="corr_th_status center">Status</th>
								<th class="corr_th_remarks">Remarks (by Addressee)</th>
								<th class="corr_th_remarks_ims">Remarks (by IMS)</th>
								<th class="corr_th_attachments">Attachments</th>
								<?php if($can_edit_cpar) { ?>
									<th class="corr_th_verified center" style="width:105px;">
										<!-- Mark as Ongoing -->
										<!-- Mark as Completed -->
										<!-- Mark as Verified -->
									</th>
								<?php } else if($is_addressee) { ?>
									<th class="corr_th_ongoing_completed center" style="width:105px;">
										<!-- Mark as Ongoing -->
										<!-- Mark as Completed -->
									</th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr class="no_added_tasks">
								<td colspan="99" class="center">No added tasks.</td>
							</tr>
						</tbody>
					</table>
					<?php
						$task_remarks_note = '';
						if($can_edit_cpar) {
							$task_remarks_note = '* Please click on Remarks (by IMS) to add or edit Remarks.';
						} else {
							$task_remarks_note = '* Please click on Remarks (by Addressee) to add or edit Remarks.';
						}
					?>
					<?php if($can_edit_cpar || $is_addressee) { ?>
						<div id="tasks_tbl_note" class="pull-left">
							<i><?php echo $task_remarks_note; ?></i>
						</div>
					<?php } ?>
				</div>
			</div>

			
			<hr class="top-separator">
			

			<!-- Review History -->
			<?php $this->load->view('partials/review_history'); ?>
		
	    <?php	    	
	    	if($can_edit_cpar) {
					require_once('partial/_save_whole_cpar.php');
					require_once('partial/_ims_ff_up_review.php');
	    	}
	    ?>

	    <br/>

	    <?php
	    	$has_proceed_3b_btn = (strcmp($this->session->userdata('loggedIn'), $cpar->assigned_ims) == 0 || 
	    		$access->access_level == ACCESS_LEVEL_ADMIN_FLAG);
	    ?>

	    <!-- Form Buttons -->
	    <div class="edit_buttons_container row">
	    	<div class="col-md-12 button_div">
				<!-- Export PDF Button -->
				<a id="export_to_pdf_btn" class="btn btn-black">
					<i class="glyphicon glyphicon-export"></i>&nbsp;Export to PDF
				</a>
				&nbsp;
				<!--// Export PDF Button -->
	    		<?php if($cpar->status == CPAR_STAGE_3 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3A) == 0 && $can_edit_cpar) { ?>
	    			<a id="proceed_btn" class="submit_btn btn btn-primary"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Proceed</a>
			      &nbsp;
	    		<?php } else if($cpar->status == CPAR_STAGE_3 && $has_proceed_3b_btn) { ?>
	    			<a id="proceed_3b_btn" class="submit_btn btn btn-primary"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Proceed</a>
			      &nbsp;
	    		<?php } ?>
	        <a id="back_button" href="/cpar<?php echo ($cpar && $cpar->status) ? '?tab='.$cpar->status : ''; ?>" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back</a>
				</div>
	    </div>
    </form>

  </div>
</div>
<!-- END OF CONTENT -->

<div class="modal fade" id="successful_save_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog small_dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Success</h4>
      </div>
      <div class="modal-body">
         <i class="glyphicon glyphicon-saved"></i>&nbsp;&nbsp;
         <span class="successful_save_modal_message">CPAR successfully saved.</span>
      </div>
      <div class="modal-footer">
        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
          close
        </a>
      </div>
    </div> 
  </div>
</div>

<div class="modal fade" id="successful_save_nr_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog small_dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Success</h4>
      </div>
      <div class="modal-body">
         <i class="glyphicon glyphicon-saved"></i>&nbsp;&nbsp;
         <span class="successful_save_modal_message">CPAR successfully saved.</span>
      </div>
      <div class="modal-footer">
        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
          close
        </a>
      </div>
    </div> 
  </div>
</div>

<div id="remarks_popover_head" class="hide">Edit Addressee Remarks</div>
<div id="remarks_popover_content" class="hide">
  <textarea id="remarks_popover" name="remarks_popover" class="form-control"></textarea>
  <a id="cancel_remarks_popover_btn" class="popover_btn btn btn-default"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Cancel</a>
  <a id="update_remarks_popover_btn" class="popover_btn btn btn-primary"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Update</a>
</div>