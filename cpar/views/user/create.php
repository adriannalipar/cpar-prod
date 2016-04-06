<script src="/_js/user/create.js"></script>
<script src="/_js/select2/select2.min.js"></script>
<link href="/_js/select2/select2.css" rel="stylesheet">
<link href="/_js/select2/select2-bootstrap.css" rel="stylesheet">

<!-- CONTENT -->
<div id="content">
  <div class="container">
  	<script>
  		var MIN_USER_FNAME = <?php echo MIN_USER_FNAME?>;
		var MIN_USER_MNAME = <?php echo MIN_USER_MNAME?>;
		var MIN_USER_LNAME = <?php echo MIN_USER_LNAME?>;
		var MIN_USER_EMAIL_ADDRESS = <?php echo MIN_USER_EMAIL_ADDRESS?>;
		var MIN_USER_POS_TITLE = <?php echo MIN_USER_POS_TITLE?>;

		var MAX_USER_FNAME = <?php echo MAX_USER_FNAME?>;
		var MAX_USER_MNAME = <?php echo MAX_USER_MNAME?>;
		var MAX_USER_LNAME = <?php echo MAX_USER_LNAME?>;
		var MAX_USER_EMAIL_ADDRESS = <?php echo MAX_USER_EMAIL_ADDRESS?>;
		var MAX_USER_POS_TITLE = <?php echo MAX_USER_POS_TITLE?>;
  	</script>

    <h2 class="no_top_margin title-divider"><span>Create <span class="de-em">User</span></span></h2>

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

    <div class="row">
    	<div class="col-md-12">
    		<div class="form_content">
		      <form id="user_form" class="form-horizontal">
						<fieldset>
							<legend class="red_legend">User Information</legend>
							<div class="form-group required">
							  <label class="col-md-2 control-label" for="fname">First Name</label>  
							  <div class="col-md-6">
							  <input id="fname" name="fname" type="text" class="form-control input-md" required="">
							    
							  </div>
							</div>

							<div class="form-group">
							  <label class="col-md-2 control-label" for="mname">Middle Name</label>  
							  <div class="col-md-6">
							  <input id="mname" name="mname" type="text" class="form-control input-md">
							    
							  </div>
							</div>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="lname">Last Name</label>  
							  <div class="col-md-6">
							  <input id="lname" name="lname" type="text" class="form-control input-md" required="">
							    
							  </div>
							</div>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="pos_title">Position Title</label>  
							  <div class="col-md-6">
							  <input id="pos_title" name="pos_title" type="text" class="form-control input-md" required="">
							    
							  </div>
							</div>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="location">Location</label>
							  <div class="col-md-6">
							    <select id="location" name="location" class="form-control">
							    	<option value="">Please select</option>
							      <?php foreach($locations as $loc): ?>
							      	<option value="<?php echo $loc['id']; ?>"><?php echo $loc['name']; ?></option>
							      <?php endforeach; ?>
							    </select>
							  </div>
							</div>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="team">Team</label>
							  <div class="col-md-6">
							    <select id="team" name="team" class="form-control">
							    	<option value="">Please select</option>
							      <?php foreach($teams as $team): ?>
							      	<option value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
							      <?php endforeach; ?>>
							    </select>
							  </div>
							</div>

							<div class="form-group">
							  <label class="col-md-2 control-label" for="team_lead">Team Leader</label>  
							  <div class="col-md-6">
							  	<input id="team_lead" name="team_lead" type="hidden" class="form-control input-md" required=""/>
							  </div>
							</div>
						</fieldset>

						<br/>

						<fieldset>

							<legend class="red_legend">Account Details</legend>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="email">Email Address</label>  
							  <div class="col-md-6">
							  <input id="email" name="email" type="text" class="form-control input-md" required="">
							    
							  </div>
							</div>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="access_level">Access Level</label>
							  <div class="col-md-6">
							    <select id="access_level" name="access_level" class="form-control">
							    	<option value="">Please select</option>
									<option value="1">Administrator</option>
									<option value="2">User</option>
							    </select>
							  </div>
							</div>

							<div class="form-group required">
							  <label class="col-md-2 control-label" for="user_status">User Status</label>
							  <div class="col-md-6"> 
							    <label class="radio-inline" for="user_status-1">
							      <input type="radio" name="user_status" id="user_status-1" value="1" checked="checked">
							      Active
							    </label> 
							    <label class="radio-inline" for="user_status-0">
							      <input type="radio" name="user_status" id="user_status-0" value="0">
							      Inactive
							    </label>
							  </div>
							</div>

							<div class="form-group">
							  <label class="col-md-2 control-label" for="user_role">User Roles</label>
							  <div class="col-md-6">
							    <label class="checkbox-inline" for="user_role-0">
							      <input type="checkbox" name="ims_flag" id="ims_flag" value="1">
							      IMS User
							    </label>
							    <label class="checkbox-inline" for="user_role-1">
							      <input type="checkbox" name="mr_flag" id="mr_flag" value="1">
							      Management Representative
							    </label>
							  </div>
							</div>

							<div class="col-md-6 col-md-offset-2">
								<div class="alert alert-info">
									* only 1 Management Representative can be assigned for the entire system
								</div>
							</div>

							<div class="col-md-8 button_div">
								<a id="save_btn" class="btn btn-primary" data-toggle="modal">
					        <i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Save
				        </a>
				        &nbsp;
				        <a id="back_button" href="/user/" class="btn btn-default">
				          <i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back
				        </a>
							</div>
						</fieldset>
					</form>
    		</div>
    	</div>
    </div>
  </div>
</div>
<!-- END OF CONTENT -->

<!-- MODALS -->
<div id="modals_container"> 
	<div class="modal fade" id="confirm_modal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog medium_dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">Confirm Save</h4>
	      </div>
	      <div class="modal-body">
	        <?php 
	        	if(!empty($mrUser)) { ?>
	        		<strong><?php echo $mrUser; ?></strong> is currently set as the Management Representative.
			        He/she will be replaced by this user <span id="replacement_mr_user"></span>. 
			        Please click on Proceed to continue or 
			        Back to cancel and change.
	        <?php
	        	} else { ?>
	        		This user <span id="replacement_mr_user"></span> will be set as the only Management Representative.
	        		Please click on Proceed to continue or 
			        Back to cancel and change.
	        <?php
	        	}
	        ?>
	      </div>
	      <div class="modal-footer">
	      	<a id="true_save_btn" class="btn btn-primary">
	          &nbsp;Proceed
	        </a>
	        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
	          Back
	        </a>
	      </div>
	    </div> 
	  </div>
	</div>

	<div class="modal fade" id="successful_save_modal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog small_dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">Success</h4>
	      </div>
	      <div class="modal-body">
	         <i class="glyphicon glyphicon-saved"></i>&nbsp;&nbsp;
	         <span class="successful_save_modal_message">User successfully saved.</span>
	      </div>
	      <div class="modal-footer">
	        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
	          close
	        </a>
	      </div>
	    </div> 
	  </div>
	</div>
</div>