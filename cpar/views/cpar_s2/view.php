<script src="/_js/cpar_s2/edit.js"></script>
<script src="/_js/select2/select2.min.js"></script>
<link href="/_js/select2/select2.css" rel="stylesheet">
<link href="/_js/select2/select2-bootstrap.css" rel="stylesheet">

<!-- CONTENT -->
<div id="content">
  <div class="container">
	<script>
		var is_stage_2 = <?php echo ($cpar->status == CPAR_STAGE_2) ? 'true' : 'false'; ?>;
	</script>

    <!-- Steps -->
	<?php 
		$this->load->view('partials/steps', array(
													'step_title' => 'Stage 2 <span class="de-em">CPAR</span>', 
													'step_sub_header' => $header_text, 
													'step_active' => 2)
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
    	if($can_edit_cpar_info) {
    		$view_suffix = 'edit';
    	}
    ?>

    <form id="cpar_info_form" class="form-horizontal basic_form">
    	<!-- CPAR Information -->
	    <div class="row">
	    	<?php 
	    		$sub_form = array('main_form_edit_disabled' => TRUE);
	    		
	    		if($can_edit_cpar_info) {
	    			$sub_form = array('main_form_edit_disabled' => FALSE);
	    		}
	    		
	    		$this->load->view('partials/main_form_edit', $sub_form); 
	    	?>
	    	<?php require_once('partial/_cpar_form_' . $view_suffix . '.php'); ?>
	    </div>
	    
	    <hr class="top-separator">
	    <!-- Addressed to / Requestor or Originator -->
	    <?php require_once('partial/_addressee_requestor_info_' . $view_suffix . '.php'); ?>
	    <br/>
	    

	    <?php
	    	if($can_edit_cpar_info) {
	    		require_once('partial/_save_cpar_info.php');
	    	}
	    ?>

	    <!-- Review History -->
	    <?php
	    	$this->load->view('partials/review_history');
	    ?>
    </form>


    <form id="cpar_form" class="form-horizontal basic_form" enctype="multipart/form-data">
    	<input type="hidden" id="cpar_no" name="id" value="<?php echo $cpar->id; ?>" />

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
					<!-- <legend class="red_legend edit"><?php echo strtoupper($action_header); ?> ACTIONS (TO BE FILLED IN BY THE ADDRESSEE)</legend> -->
					<legend class="red_legend edit title-legend">TO BE FILLED IN BY THE ADDRESSEE</legend>	
					<!-- Should be accomplished by: -->
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

		
			<?php if((int)$cpar->type == CPAR_TYPE_C) { ?>
				<div id="car_fields_wrapper">
					<fieldset>
						<legend class="red_legend indented">Remedial Action Details</legend>

						<!-- Immediate Remedial Action -->
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="immediate_remedial_action">Immediate Remedial Action</label>
						  <div class="col-md-6">
						    <textarea disabled="disabled" id="immediate_remedial_action" name="immediate_remedial_action" class="form-control"><?php echo $addr_fields->rad_action; ?></textarea>
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
						  	<input disabled="disabled" id="implemented_by" name="implemented_by" type="hidden" class="adr_name form-control" value='<?php echo $implemented_by; ?>' />
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
		              <input disabled="disabled" id="date_implemented" name="date_implemented" type="text" value="<?php echo $date_formatted; ?>" class="datepicker form-control input-md">
		              <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		            </div>
						  </div>
						</div>

						<!-- (RAD) Supporting Evidences -->
						<div class="attachments_form_group form-group">
						  <label class="col-md-2 control-label" for="rad_attachments">Supporting Evidence</label>  
						  <div class="col-md-6">
						  	<span class="attachment_notice">* If record or evidence is available, please attach files.</span>
						  	<div id="rad_attachments_container" style="padding-top: 7px;">
						  		<?php 
						  			if(!($rad_filenames == null || empty($rad_filenames))) {
						  				foreach ($rad_filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
						  					<div class="uploaded_file">
									  			<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a>&nbsp;&nbsp;&nbsp;
									  		</div>
						  		<?php 
						  				endforeach;
						  			}
						  		?>
						  	</div>
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
						  		<input type="checkbox" disabled="disabled" <?php echo $selected; ?> name="tools_used[]" value="<?php echo $tool['id']; ?>"/>&nbsp;&nbsp;<?php echo $tool['name']; ?>
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
						  		<input type="checkbox" disabled="disabled" <?php echo $selected; ?> name="tools_used[]" value="<?php echo $tool['id']; ?>"/>&nbsp;&nbsp;<?php echo $tool['name']; ?>
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
						  		<input type="checkbox" disabled="disabled" <?php echo $selected; ?> name="tools_used[]" value="<?php echo $tool['id']; ?>"/>&nbsp;&nbsp;<?php echo $tool['name']; ?>
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
						  		<input type="checkbox" disabled="disabled" <?php echo $selected; ?> name="tools_used[]" value="<?php echo $obj_others['id']; ?>"/>&nbsp;&nbsp;Others, please specify:
						  </div>
						  <div class="col-md-4 others-field">
						  		<input id="other_tools_used" disabled="disabled" name="other_tools_used" type="text" value="<?php echo $addr_fields->rca_tools_others; ?>" class="form-control input-md">
						  </div>
						</div>
						<?php } ?>

						<!-- Details / Result of Root Cause Analysis -->
						<div class="form-group">
						  <label class="col-md-2 control-label" for="rca_details">Details / Result of Root Cause Analysis</label>
						  <div class="col-md-6">
						    <textarea disabled="disabled" id="rca_details" name="rca_details" class="form-control"><?php echo $addr_fields->rca_details; ?></textarea>
						  </div>
						</div>

						<!-- Investigated By -->
						<?php
							$investigated_by = '';
							if(!($addr_fields->rca_investigated_by == null || empty($addr_fields->rca_investigated_by))) {
								$investigated_by = '[{"id":"' . $addr_fields->rca_investigated_by . '","text":"' . $addr_fields->rca_investigated_by_name . '"}]';
							}
						?>
						<div class="form-group">
						  <label class="col-md-2 control-label" for="investigated_by">Investigated By</label>  
						  <div class="col-md-4">
						  	<input disabled="disabled" id="investigated_by" name="investigated_by" type="hidden" class="adr_name form-control" value='<?php echo $investigated_by; ?>' />
						  </div>
						</div>

						<!-- Date Investigation (Start) and (End) -->
						<div class="form-group">
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
		              <input disabled="disabled" id="date_investigation_started" name="date_investigation_started" type="text" value="<?php echo $date_formatted; ?>" class="datepicker form-control input-md">
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
		              <input disabled="disabled" id="date_investigation_ended" name="date_investigation_ended" type="text" value="<?php echo $date_formatted; ?>" class="datepicker form-control input-md">
		              <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		            </div>
						  </div>
						</div>

						<!-- (RCA) Supporting Evidences -->
						<div class="attachments_form_group form-group">
						  <label class="col-md-2 control-label" for="rca_attachments">Supporting Evidence</label>  
						  <div class="col-md-6">
						  	<span class="attachment_notice">* If record or evidence is available, please attach files.</span>
						  	<div id="rca_attachments_container" style="padding-top: 7px;">
						  		<?php
						  			if(!($rca_filenames == null || empty($rca_filenames))) {
						  				foreach ($rca_filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
						  					<div class="uploaded_file">
									  			<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a>&nbsp;&nbsp;&nbsp;
									  		</div>
						  		<?php 
						  				endforeach;
						  			}
						  		?>
						  	</div>
						  </div>
						</div>
					</fieldset>

				
				</div> <!-- end of CAR fields wrapper -->
			<?php } ?>

			<?php if((int)$cpar->type == CPAR_TYPE_C) { ?>
				<hr class="lightline">
			<?php } ?>

			<?php
			
				$this->load->view('partials/action_plan_disabled', array('action_header' => $action_header));
			
			?>

			<br/>
			

			<legend class="red_legend super_indented"><?php echo $action_header; ?> Action<?php echo (strtolower($action_header) == 'preventive') ? ' <i>(Continual Improvement Action)</i>' : ''; ?> Plan Details</legend>
			<div class="row">
				<div id="action_details_input_wrapper" class="col-md-11 col-md-offset-1">
					<!-- errors -->
			    <div class="row">
			    	<div class="col-md-12">
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

					<fieldset>
						<div class="col-md-6">
							<input disabled="disabled" type="hidden" id="serialized_tasks" value='<?php echo urlencode($serialized_tasks); ?>' />

							<!-- Task -->
							<div class="form-group">
							  <label class="col-md-3 control-label" for="corr_task">Task</label>
							  <div class="col-md-9">
							  	<input disabled="disabled" id="corr_task" name="corr_task" type="text" class="form-control" value="" />
							  </div>
							</div>

							<!-- Responsible Person -->
							<div class="form-group">
							  <label class="col-md-3 control-label res-person" for="corr_resp_per">Responsible Person</label>  
							  <div class="col-md-9">
							  	<input id="corr_resp_per" disabled="disabled" name="corr_resp_per" type="hidden" class="adr_name form-control" />
							  </div>
							</div>

							<!-- Due Date -->
							<div class="form-group">
							  <label class="col-md-3 control-label" for="corr_due_date">Due Date</label>  
							  <div class="col-md-9">
							  	<div class="input-group date">
			              <input disabled="disabled" id="corr_due_date" name="corr_due_date" type="text" value="" class="past_not_allowed datepicker form-control input-md">
			              <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			            </div>
							  </div>
							</div>
						</div>
						<div class="col-md-6">
							<!-- Remarks (by Addressee) -->
							<div class="form-group">
							  <label class="col-md-3 control-label" for="corr_remarks">Remarks<br/>(by Addressee)</label>  
							  <div class="col-md-9">
							  	<textarea disabled="disabled" id="corr_remarks" name="corr_remarks" class="form-control" ></textarea>
							  </div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>

			<br/>

			<div class="row">
				<div class="col-md-10 col-md-offset-2">
					<!-- Table for Tasks -->
					<table id="task_tbl" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th class="corr_th_task">Task</th>
								<th class="corr_th_responsible_person">Responsible Person</th>
								<th class="corr_th_due_date center">Due Date</th>
								<th class="corr_th_remarks">Remarks (by Addressee)</th>
								<th class="corr_th_attachments">Attachments</th>
								<th class="corr_th_remove center"></th>
							</tr>
						</thead>
						<tbody>
							<tr class="no_added_tasks">
								<td colspan="6" class="center">No added tasks.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<br/>
			

	    <!-- Form Buttons -->
	    <div class="edit_buttons_container row">
	    	<div class="col-md-12 button_div">
				<!-- Export PDF Button -->
				<a id="export_to_pdf_btn" class="btn btn-black">
					<i class="glyphicon glyphicon-export"></i>&nbsp;Export to PDF
				</a>
				&nbsp;
				<!--// Export PDF Button -->					    	
	        <a id="back_button" href="/cpar<?php echo ($cpar && $cpar->status) ? '?tab='.$cpar->status : ''; ?>" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back</a>
				</div>
	    </div>
    </form>

  </div>
</div>
<!-- END OF CONTENT -->