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

    <form id="cpar_form" class="form-horizontal basic_form" enctype="multipart/form-data">
    	<input type="hidden" id="cpar_no" name="id" value="<?php echo $cpar->id; ?>" />
	    <!-- CPAR Information -->
	    <div class="row">
	    	<?php 
	    		$sub_form = array('main_form_edit_disabled' => TRUE);
	    			    		
	    		$this->load->view('partials/main_form_edit', $sub_form); 
	    	?>
	    	<?php require_once('partial/_cpar_form_view.php'); ?>
	    </div>
	    
	    <hr class="top-separator">
	    <!-- Addressed to / Requestor or Originator -->
	    <?php require_once('partial/_addressee_requestor_info_view.php'); ?>
	    
	    
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
						<div class="form-group">
						  <label class="col-md-2 control-label" for="immediate_remedial_action">Immediate Remedial Action</label>
						  <div class="col-md-6">
						    <textarea disabled="disabled" id="immediate_remedial_action" disabled="disabled" name="immediate_remedial_action" class="form-control"><?php echo $addr_fields->rad_action; ?></textarea>
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
						<div class="form-group">
						  <label class="col-md-2 control-label" for="implemented_by">Implemented By</label>  
						  <div class="col-md-6">
						  	<input disabled="disabled" id="implemented_by" disabled="disabled" name="implemented_by" type="hidden" class="adr_name form-control" value='<?php echo $implemented_by; ?>' />
						  </div>
						</div>

						<!-- Date Implemented -->
						<?php
							$date_formatted = '';
							if(!(strcmp($addr_fields->rad_implemented_date, NULL_DATE_ONLY) == 0 || empty($addr_fields->rad_implemented_date))) {
								$date = new DateTime($addr_fields->rad_implemented_date);
								$date_formatted = $date->format('M d, Y');
							}
						?>
						<div class="form-group">
						  <label class="col-md-2 control-label" for="date_implemented">Date Implemented</label>  
						  <div class="col-md-6">
		            <input disabled="disabled" id="date_implemented" disabled="disabled" name="date_implemented" type="text" value="<?php echo $date_formatted; ?>" class="form-control">
						  </div>
						</div>

						<!-- (RAD) Supporting Evidences -->
						<div class="attachments_form_group form-group">
						  <label class="col-md-2 control-label" for="rad_attachments">Supporting Evidence</label>  
						  <div class="col-md-6">
						  	<div id="rad_attachments_container" style="padding-top: 7px;">
						  		<?php 
						  			if(!($rad_filenames == null || empty($rad_filenames))) {
						  				foreach ($rad_filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
						  					<div class="uploaded_file">
									  			<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a>
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
						  	<input disabled="disabled" id="investigated_by" disabled="disabled" name="investigated_by" type="hidden" class="adr_name form-control" value='<?php echo $investigated_by; ?>' />
						  </div>
						</div>

						<!-- Date Investigation (Start) and (End) -->
						<div class="form-group">
							<?php
								$date_formatted = '';
								if(!(strcmp($addr_fields->rca_investigated_date_started, NULL_DATE_ONLY) == 0 || empty($addr_fields->rca_investigated_date_started))) {
									$date = new DateTime($addr_fields->rca_investigated_date_started);
									$date_formatted = $date->format('M d, Y');
								}
							?>
						  <label class="col-md-2 control-label" for="date_investigation_started">Date Investigation (Started)</label>
						  <div class="col-md-4">
		            <input disabled="disabled" id="date_investigation_started" disabled="disabled" name="date_investigation_started" type="text" value="<?php echo $date_formatted; ?>" class="form-control input-md">
						  </div>

						  <label class="col-md-2 control-label" for="date_investigation_ended">Date Investigation (Ended)</label>
						  <?php
								$date_formatted = '';
								if(!(strcmp($addr_fields->rca_investigated_date_ended, NULL_DATE_ONLY) == 0 || empty($addr_fields->rca_investigated_date_ended))) {
									$date = new DateTime($addr_fields->rca_investigated_date_ended);
									$date_formatted = $date->format('M d, Y');
								}
							?>
						  <div class="col-md-4">
		            <input disabled="disabled" id="date_investigation_ended" disabled="disabled" name="date_investigation_ended" type="text" value="<?php echo $date_formatted; ?>" class="form-control input-md">
						  </div>
						</div>

						<!-- (RCA) Supporting Evidences -->
						<div class="attachments_form_group form-group">
						  <label class="col-md-2 control-label" for="rca_attachments">Supporting Evidence</label>  
						  <div class="col-md-6">
						  	<div id="rca_attachments_container" style="padding-top: 7px;">
						  		<?php
						  			if(!($rca_filenames == null || empty($rca_filenames))) {
						  				foreach ($rca_filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
						  					<div class="uploaded_file">
									  			<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a>
									  		</div>
						  		<?php 
						  				endforeach;
						  			}
						  		?>
						  	</div>
						  	<br/>
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
			

			<div class="row">
				<legend class="red_legend super_indented"><?php echo $action_header; ?> Action<?php echo (strtolower($action_header) == 'preventive') ? ' <i>(Continual Improvement Action)</i>' : ''; ?> Plan Details</legend>
				<div class="col-md-10 col-md-offset-2">
					<!-- Table for Tasks -->
					<input disabled="disabled" type="hidden" id="is_review" value='true' />
					<input disabled="disabled" type="hidden" id="serialized_tasks" value='<?php echo urlencode($serialized_tasks); ?>' />
					<table id="task_tbl" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th class="corr_th_task">Task</th>
								<th class="corr_th_responsible_person">Responsible Person</th>
								<th class="corr_th_due_date center">Due Date</th>
								<th class="corr_th_remarks">Remarks (by Addressee)</th>
								<th class="corr_th_attachments">Attachments</th>
							</tr>
						</thead>
						<tbody>
							<tr class="no_added_tasks">
								<td colspan="5" class="center">No added tasks.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<br/>
			

			<!-- Review History -->
	    <?php
	    	$this->load->view('partials/review_history');
	    	
	    	$l_id = (int)$this->session->userdata('loggedIn');
				if($l_id == $cpar->addressee_team_lead || $l_id == $cpar->assigned_ims || (int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
					
					echo '<hr class="top-separator">';
	    		

					require_once('partial/_tl_review.php');
				}
	    ?>

	    <br/>
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
	    		<?php if($cpar->status == CPAR_STAGE_2 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2B) == 0 &&
	    			($l_id == $cpar->addressee_team_lead || $l_id == $cpar->assigned_ims || (int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG)) { ?>
	    			<a id="proceed_btn" class="submit_btn btn btn-primary"><i class="glyphicon glyphicon-chevron-right"></i>&nbsp;Proceed</a>
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