<!-- CONTENT -->
<div id="content">
  <div class="container">
    <!-- Steps -->
	<?php 
		$this->load->view('partials/steps', array(
													'step_title' => 'Stage 5 <span class="de-em">CPAR</span>', 
													'step_sub_header' => $header_text,
													'step_sub_header_style' => 'font-size: 14px;',
													'step_active' => 5
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
    	
    	if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
    		$is_disabled = '';
    	}
    ?>

    <form id="cpar_form" class="form-horizontal basic_form" enctype="multipart/form-data">
    	<input type="hidden" id="cpar_no" name="id" value="<?php echo $cpar->id; ?>" />

    	<!-- CPAR Information -->
	    <div class="row">
	    	<?php 
	    		$sub_form = array();
	    	
	    		if(!($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG)) {
					$sub_form = array('main_form_edit_disabled' => TRUE);
	    		}
	    			    		
	    		$this->load->view('partials/main_form_edit', $sub_form);
	    	?>
	    </div>

	    <hr class="top-separator">
	    <!-- Addressed to / Requestor or Originator -->
	    <?php 
	    	if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
				$this->load->view('partials/addressee_requestor_info_edit');
	    	} else {
		    	$this->load->view('partials/addressee_requestor_info_view');
	    	}
	    ?>

	    
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
			
				$this->load->view('partials/action_plan', array('action_header' => $action_header, 'is_disabled' => $is_disabled, 'ap_required' => true, 'no_more_uploads' => true));
			
			?>

			<br/>
			
			<legend class="red_legend super_indented"><?php echo $action_header; ?> Action<?php echo (strtolower($action_header) == 'preventive') ? ' <i>(Continual Improvement Action)</i>' : ''; ?> Plan Details</legend>

			<div class="row" ng-controller="actionController as actionCtrl" ng-init="actionCtrl.initializeTable()">
				<div class="col-md-11 col-md-offset-1">
					<!-- Table for Tasks -->
					<input type="hidden" id="is_review" value='true' />
					<input type="hidden" id="serialized_tasks" value='<?php echo urlencode($serialized_tasks); ?>' />
					<input type="hidden" id="task_count" name="task_count" value="" />
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
							</tr>
						</thead>
						<tbody>
							<tr class="old_task" ng-repeat="action in actionCtrl.actions" run-popover>
								<td>
									<input type="hidden" class="task_id" value="{{action.id}}">
<?php if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) { ?>
										<div class="task_popover">{{action.task | cpar_task}}</div>
<?php } else { ?>
										{{action.task | cpar_task}}	
<?php } ?>
								</td>
								<td>
									<input type="hidden" class="resp_per_hdn" value="{{action.responsible_person}}" >
									<span class="resp_per_name_span">{{action.responsible_person_name}}</span>
								</td>
								<td class="center action-due-date" data-id="{{action.id}}"><div class="cell-datepicker">{{action.due_date}}</div></td>
								<td class="center action-completed-date" data-id="{{action.id}}"><div class="cell-datepicker">{{action.completed_date}}</div></td>
								<td class="center">{{action.status_name}}</td>
								<td>
<?php if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) { ?>
									<div class="remarks_popover">{{action.remarks_addr | cpar_task}}</div>
<?php } else { ?>
									{{action.remarks_addr | cpar_task}}	
<?php } ?>
								</td>
								<td>{{action.remarks_ims}}</td>
								<td><span ng-repeat="files in action.attachments"><a href="javascript:void(0);" ng-click="actionCtrl.downloadFile($event,action.id,files.filename)" href="/file/task/{{ files.filename }}" class="task_file_name">{{ files.filename }}</a><br/></span></td>
							</tr>
							<!--
<tr class="no_added_tasks">
								<td colspan="7" class="center">No added tasks.</td>
							</tr>
-->
						</tbody>
					</table>
				</div>
			</div>

			
			<hr class="top-separator">
			
			<?php $this->load->view('partials/ff_up_history'); ?>

			<br/>

			<!-- Review History -->
			<?php $this->load->view('partials/review_history'); ?>
			<br/>
	    <?php	    	
	    	if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
					$this->load->view('partials/save_whole_cpar.php');
			}
		?>
	    <br/>

	    <!-- Form Buttons -->
	    <div class="edit_buttons_container row">
	    	<div class="col-md-12 button_div">
	    		<?php #if(strcmp($this->session->userdata('loggedIn'), $cpar->assigned_ims) == 0 || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG) { ?>
	    			<a id="export_to_pdf_btn" class="btn btn-black">
			        <i class="glyphicon glyphicon-export"></i>&nbsp;Export to PDF
		        </a>
	    			&nbsp;	
	    		<?php #} ?>
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

<?php 
	if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
		$this->load->view('partials/apd_popovers.php');
	}
?>

<script src="/_js/cpar_s5/edit.js"></script>
<script src="/_js/save_whole_cpar.js"></script>
<script src="/_js/select2/select2.min.js"></script>
<link href="/_js/select2/select2.css" rel="stylesheet">
<link href="/_js/select2/select2-bootstrap.css" rel="stylesheet">