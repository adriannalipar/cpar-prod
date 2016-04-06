<?php /* ?>
<legend class="red_legend edit title-legend">Information for <b><?php echo $cpar_no; ?></b></legend>
<div class="col-md-8">
	<div class="form_content">
		<fieldset>
			

			<input type="hidden" id="cpar_no" name="id" value="<?php echo $cpar->id; ?>" />

			<!-- Title -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="title">Title</label>  
			  <div class="col-md-9">
			  	<input id="title" name="title" type="text" value="<?php echo $cpar->title; ?>" class="form-control input-md" disabled="disabled">
			  </div>
			</div>

			<!-- Type -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="type">Type</label>
			  <div class="col-md-9">
			  	<?php $type_name = (int)$cpar->type == CPAR_TYPE_C ? CPAR_TYPE_C_NAME : CPAR_TYPE_P_NAME; ?>
			    <select id="type" name="type" class="form-control" disabled="disabled">
			      <option selected="selected"><?php echo $type_name; ?></option>
			    </select>
			  </div>
			</div>

			<!-- Raised as a result of -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="result_of">Raised as a result of</label>
			  <div class="col-md-9">
			    <select id="result_of" name="result_of" class="form-control" disabled="disabled">
			      <option value="">Please Select</option>
			      <?php foreach($raaro_list as $raaro): ?>
			      	<option <?php echo $cpar->raised_as_a_result_of == $raaro['id'] ? "selected='selected'" : ""; ?> value="<?php echo $raaro['id']; ?>"><?php echo $raaro['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- Raised as a result of OTHERS -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="result_of_others"></label>
				<div class="col-md-9">
					<input id="result_of_others" name="result_of_others" type="text" class="form-control input-md" disabled="disabled" value="<?php echo $cpar->raised_as_a_result_of_others; ?>">
				</div>
			</div>

			<!-- Process -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="process">Process</label>
			  <div class="col-md-9">
			    <select id="process" name="process" class="form-control" disabled="disabled">
			      <option value="">Please Select</option>
			      <?php foreach($process_list as $process): ?>
			      	<option <?php echo $cpar->process == $process['id'] ? "selected='selected'" : ""; ?> value="<?php echo $process['id']; ?>"><?php echo $process['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- Details -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="details">Details</label>
			  <div class="col-md-9">
			    <textarea id="details" name="details" class="form-control" disabled="disabled"><?php echo $cpar->details; ?></textarea>
			  </div>
			</div>

			<!-- Justification -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="justification">Justification</label>
			  <div class="col-md-9">
			    <textarea id="justification" name="justification" class="form-control" disabled="disabled"><?php echo $cpar->justification; ?></textarea>
			  </div>
			</div>

			<!-- References -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="references">References</label>
			  <div class="col-md-9">
			    <textarea id="references" name="references" class="form-control" disabled="disabled"><?php echo $cpar->references; ?></textarea>
			  </div>
			</div>

			<!-- Attachments -->
			<div id="attachments_form_group" class="form-group">
			  <label class="col-md-3 control-label" for="attachments">Attachments</label>  
			  <div class="col-md-9">
			  	<div id="attachments_container" style="padding-top: 7px;">
			  		<?php 
			  			if(!($filenames == null || empty($filenames))) {
			  				foreach ($filenames as $file):
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
	</div>
</div>
<?php */ ?>
<?php
	/* Push Back Information */
	if($cpar->status == CPAR_STAGE_2 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A2) == 0) {
	  require_once('_pushback_info.php');
	}
?>