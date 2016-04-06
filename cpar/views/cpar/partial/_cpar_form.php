<legend class="red_legend title-legend">CPAR Information</legend>
<div class="col-md-8">
	<div class="form_content">
		<fieldset>
			<!-- Title -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="title">Title</label>  
			  <div class="col-md-9">
			  	<input id="title" name="title" type="text" class="form-control input-md">
			  </div>
			</div>

			<!-- Type -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="type">Type</label>
			  <div class="col-md-9">
			    <select id="type" name="type" class="form-control">
			    	<option value="">Please Select</option>
			      <option value="<?php echo CPAR_TYPE_C; ?>"><?php echo CPAR_TYPE_C_NAME; ?></option>
			      <option value="<?php echo CPAR_TYPE_P; ?>"><?php echo CPAR_TYPE_P_NAME; ?></option>
			    </select>
			  </div>
			</div>

			<!-- Raised as a result of -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="result_of">Raised as a result of</label>
			  <div class="col-md-9">
			    <select id="result_of" name="result_of" class="form-control">
			      <option value="">Please Select</option>
			      <?php foreach($raaro_list as $raaro): ?>
			      	<option value="<?php echo $raaro['id']; ?>"><?php echo $raaro['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- Raised as a result of OTHERS -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="result_of_others"></label>
				<div class="col-md-9">
					<input id="result_of_others" name="result_of_others" type="text" class="form-control input-md" disabled="disabled">
				</div>
			</div>


			<!-- Process -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="process">Process</label>
			  <div class="col-md-9">
			    <select id="process" name="process" class="form-control">
			      <option value="">Please Select</option>
			      <?php foreach($process_list as $process): ?>
			      	<option value="<?php echo $process['id']; ?>"><?php echo $process['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- Details -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="details">Details</label>
			  <div class="col-md-9">
			    <textarea id="details" name="details" class="form-control"></textarea>
			  </div>
			</div>

			<!-- Justification -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="justification">Justification</label>
			  <div class="col-md-9">
			    <textarea id="justification" name="justification" class="form-control"></textarea>
			  </div>
			</div>

			<!-- References -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="references">References</label>
			  <div class="col-md-9">
			    <textarea id="references" name="references" class="form-control"></textarea>
			  </div>
			</div>

			<!-- Attachments -->
			<div id="attachments_form_group" class="form-group">
			  <label class="col-md-3 control-label" for="attachments">Attachments</label>  
			  <div class="col-md-9">
			  	<div id="attachments_container"></div>
			  	<br/>
			  	<button id="add_attachment" type="button" class="btn btn-danger">
			  		<i class="glyphicon glyphicon-upload"></i>Add File
			  	</button>
			  </div>
			</div>

		</fieldset>
	</div>
</div>