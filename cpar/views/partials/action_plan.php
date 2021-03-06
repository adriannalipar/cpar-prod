			<fieldset>
				<legend class="red_legend indented"><?php echo $action_header; ?> Action<?php echo (strtolower($action_header) == 'preventive') ? ' <i>(Continual Improvement Action)</i>' : ''; ?></legend>
				
				<!-- Corrective/Preventive Action -->
				<div class="form-group <?php echo (isset($ap_required) && $ap_required) ? 'required' : '';?>">
					<label class="col-md-2 control-label" for="corr_prev_action"><?php echo $action_header; ?> Action<?php echo (strtolower($action_header) == 'preventive') ? ' <i>(Continual Improvement Action)</i>' : ''; ?></label>
					<div class="col-md-4">
						<textarea <?php echo isset($is_disabled) ? $is_disabled : ''; ?> id="corr_prev_action" name="corr_prev_action" class="form-control"><?php echo $addr_fields->action; ?></textarea>
					</div>
				</div>
				
				<!-- Proposed By -->
				<?php
				$proposed_by = '';
				if(!($addr_fields->proposed_by == null || empty($addr_fields->proposed_by))) {
					$proposed_by = '[{"id":"' . $addr_fields->proposed_by . '","text":"' . $addr_fields->proposed_by_name . '"}]';
				} else {
					$proposed_by = '[{"id":"' . $cpar->addressee . '","text":"' . $cpar->addressee_name . '"}]';
				}
				?>
				<div class="form-group <?php echo (isset($ap_required) && $ap_required) ? 'required' : '';?>">
					<label class="col-md-2 control-label" for="corr_prev_proposed_by">Proposed By</label>
					<div class="col-md-4">
						<input <?php echo isset($is_disabled) ? $is_disabled : ''; ?> id="corr_prev_proposed_by" name="corr_prev_proposed_by" type="hidden" class="adr_name form-control" value='<?php echo $proposed_by; ?>' />
					</div>
				</div>
				
				<!-- Target (Start) and (End) Dates -->
				<div class="form-group <?php echo (isset($ap_required) && $ap_required) ? 'required' : '';?>">
					<?php
					$date_formatted = '';
					if(!(strcmp($addr_fields->target_start_date, NULL_DATE_ONLY) == 0 || empty($addr_fields->target_start_date))) {
						$date = new DateTime($addr_fields->target_start_date);
						$date_formatted = $date->format('m/d/Y');
					}
					?>
					<label class="col-md-2 control-label" for="corr_prev_target_start_date">Target Start Date</label>
					<div class="col-md-4">
						<div class="input-group date">
							<input <?php echo isset($is_disabled) ? $is_disabled : ''; ?> id="corr_prev_target_start_date" name="corr_prev_target_start_date" type="text" value="<?php echo $date_formatted; ?>" class="past_not_allowed datepicker form-control input-md">
							<span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
				
					<?php
					$date_formatted = '';
					if(!(strcmp($addr_fields->target_end_date, NULL_DATE_ONLY) == 0 || empty($addr_fields->target_end_date))) {
						$date = new DateTime($addr_fields->target_end_date);
						$date_formatted = $date->format('m/d/Y');
					}
					?>
					<label class="col-md-2 control-label" for="corr_prev_target_end_date">Target End Date</label>
					<div class="col-md-4">
						<div class="input-group date">
							<input <?php echo isset($is_disabled) ? $is_disabled : ''; ?> id="corr_prev_target_end_date" name="corr_prev_target_end_date" type="text" value="<?php echo $date_formatted; ?>" class="past_not_allowed datepicker form-control input-md">
							<span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
				</div>

				<!-- Upload File -->
				<input hidden="uid" name="ap_upload_uid" id="ap_upload_uid" value="<?php echo uniqid(); ?>"/>
				<div class="form-group">
					<label class="col-md-2 control-label" for="btn_ap_attachments">Attachments</label>
					<div class="col-md-4">
						<span>
						<?php
				  			if(!($ap_attachments == null || empty($ap_attachments))) {
				  				foreach ($ap_attachments as $file) {
				  					$url = '/file/action_plan/'.$file->filename;
				  		?>
								<div class="uploaded_file">
									<input type="hidden" name="ap_attachments[]" class="cpar_attachment" value="<?php echo $file->filename; ?>"/>
									<a href="<?php echo $url; ?>" class="ap_file_name"><?php echo $file->filename; ?></a>
									<?php if((!isset($is_disabled) || $is_disabled == '') && !isset($no_more_uploads)) { ?>
									&nbsp;&nbsp;&nbsp;<i class="remove-ap-attachment glyphicon glyphicon-remove" style="cursor:pointer;"></i>
									<?php  } ?>
								</div>
				  		<?php 
				  				}
				  			}
				  		?>
						</span>
						<?php if((!isset($is_disabled) || $is_disabled == '') && !isset($no_more_uploads)) { ?>
						<button id="btn_ap_attachments" data-upload-name="ap_attachments" class="btn btn-danger action-plan-upload-btn"><i class="glyphicon glyphicon-upload"></i>&nbsp;Add File</button>
						<?php  } ?>
					</div>
				</div>
				
			</fieldset>
