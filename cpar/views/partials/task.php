					<fieldset>
						<div class="col-md-6">
							<input type="hidden" id="serialized_tasks" value='<?php echo urlencode($serialized_tasks); ?>' />
						
							<!-- Task -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="corr_task">Task</label>
								<div class="col-md-9">
									<input id="corr_task" name="corr_task" type="text" class="form-control" value="" />
								</div>
							</div>
						
							<!-- Responsible Person -->
							<div class="form-group">
								<label class="col-md-3 control-label res-person" for="corr_resp_per">Responsible Person</label>  
								<div class="col-md-9">
									<input id="corr_resp_per" name="corr_resp_per" type="hidden" class="adr_name form-control" />
								</div>
							</div>
						
							<!-- Due Date -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="corr_due_date">Due Date</label>  
									<div class="col-md-9">
										<div class="input-group date">
											<input id="corr_due_date" name="corr_due_date" type="text" value="" class="past_not_allowed datepicker form-control input-md">
											<span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
										</div>
									</div>
							</div>
							
							<!-- Upload File -->
							<div id="task_attachments" class="form-group">
								<label class="col-md-3 control-label" for="btn_task_attachments">Attachments</label>
								<div class="col-md-9">
									<span>
									</span>
									<button id="btn_task_attachments" data-upload-name="task_attachments" class="btn btn-danger task-upload-btn"><i class="glyphicon glyphicon-upload"></i>&nbsp;Add File</button>
								</div>
							</div>					
						</div>
						<div class="col-md-6">
							<!-- Remarks (by Addressee) -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="corr_remarks">Remarks<br/>(by Addressee)</label>  
								<div class="col-md-9">
									<textarea id="corr_remarks" name="corr_remarks" class="form-control" ></textarea>
								</div>
							</div>
						</div>
						<a id="add_task_btn" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i>&nbsp;Add</a>
					</fieldset>