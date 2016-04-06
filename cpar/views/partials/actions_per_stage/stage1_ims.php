<legend class="red_legend title-legend">To be filled-up by the IMS</legend>
<div class="row">
	<div class="col-md-8">
		<div class="form_content">
			<fieldset>
				<input type="hidden" name="is_ims_creating" value="true" />
				<span class="ims_review_instruction"> * 1.&nbsp;&nbsp;Select action to be done for this request.</span>
				<div>		
					<!-- Submit Response By -->
					<?php
					
					$next_due_date = '';
					
					if(isset($cpar) && $cpar && $cpar->date_due) {
						
						if(!(strcmp($cpar->date_due, NULL_DATE_ONLY) == 0 || empty($cpar->date_due))) {
							$date = new DateTime($cpar->date_due);
							$next_due_date = $date->format('m/d/Y');
						}
												
					}
					
					?>				
					<div class="form-group">
						<label class="col-md-4 control-label" for="next_due_date">Submit Response By</label>
						<div class="col-md-8">
							<div class="input-group">
								<input id="next_due_date" name="next_due_date" type="text" value="<?php echo $next_due_date; ?>" class="datepicker form-control">
								<span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
						</div>
					</div>
		
				</div>
			</fieldset>
		</div>
	</div>
</div>