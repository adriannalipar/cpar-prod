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
			  	<input id="title" name="title" type="text" class="form-control input-md" value="<?php echo $cpar->title; ?>">
			  </div>
			</div>

			<!-- Type -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="type">Type</label>
			  <div class="col-md-9">
			  	<?php
			  		$attr = '';
			  		if($cpar->type == 0) { //not yet set
			  			$attr = 'name="type"';
			  		} else {
			  			$attr = 'disabled="disabled"';
			  		}
			  	?>

			    <select id="type" class="form-control" <?php echo $attr; ?>>
			    	<option value="">Please Select</option>
			      <option <?php echo $cpar->type == CPAR_TYPE_C ? "selected='selected'" : ""; ?> value="<?php echo CPAR_TYPE_C; ?>"><?php echo CPAR_TYPE_C_NAME; ?></option>
			      <option <?php echo $cpar->type == CPAR_TYPE_P ? "selected='selected'" : ""; ?> value="<?php echo CPAR_TYPE_P; ?>"><?php echo CPAR_TYPE_P_NAME; ?></option>
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
			    <select id="process" name="process" class="form-control">
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
			    <textarea id="details" name="details" class="form-control"><?php echo $cpar->details; ?></textarea>
			  </div>
			</div>

			<!-- Justification -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="justification">Justification</label>
			  <div class="col-md-9">
			    <textarea id="justification" name="justification" class="form-control"><?php echo $cpar->justification; ?></textarea>
			  </div>
			</div>

			<!-- References -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="references">References</label>
			  <div class="col-md-9">
			    <textarea id="references" name="references" class="form-control"><?php echo $cpar->references; ?></textarea>
			  </div>
			</div>

			<!-- Attachments -->
			<div id="attachments_form_group" class="attachments_form_group form-group">
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
						  			<i id="remove_attachment" class="remove_attachment glyphicon glyphicon-remove"></i>
						  		</div>
			  		<?php 
			  				endforeach;
			  			}
			  		?>
			  	</div>
				  	<br/>
				  	<button id="add_attachment" type="button" class="btn btn-danger">
				  		<i class="glyphicon glyphicon-upload"></i>Add File
				  	</button>
			  </div>
			</div>

		</fieldset>
	</div>
</div>
<?php */ ?>