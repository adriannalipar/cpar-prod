<script src="/_js/user/view.js"></script>

<!-- CONTENT -->
<div id="content">
  <div class="container">
    <h2 class="no_top_margin title-divider"><span>View <span class="de-em">User</span></span></h2>

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
		      <form id="user_view_form" class="form-horizontal">
					<fieldset>
						<legend class="red_legend">User Information</legend>
						<div class="form-group required">
						  <label class="col-md-2 control-label" for="fname">First Name</label>  
						  <div class="col-md-7">
						  <input id="fname" name="fname" type="text" class="form-control input-md" value="<?php echo $user->first_name; ?>" disabled />
						  </div>
						</div>

						<div class="form-group">
						  <label class="col-md-2 control-label" for="mname">Middle Name</label>  
						  <div class="col-md-7">
						  <input id="mname" name="mname" type="text" class="form-control input-md" value="<?php echo $user->middle_name; ?>" disabled />						    
						  </div>
						</div>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="lname">Last Name</label>  
						  <div class="col-md-7">
						  <input id="lname" name="lname" type="text" class="form-control input-md" value="<?php echo $user->last_name; ?>" disabled />
						  </div>
						</div>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="pos_title">Position Title</label>  
						  <div class="col-md-7">
						  <input id="pos_title" name="pos_title" type="text" class="form-control input-md" value="<?php echo $user->position_title; ?>" disabled />
						  </div>
						</div>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="location">Location</label>
						  <div class="col-md-7">
						    <input id="location" name="location" type="text" class="form-control" value="<?php echo $user->location_name; ?>" disabled />
						  </div>
						</div>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="team">Team</label>
						  <div class="col-md-7">
						    <input id="team" name="team" type="text" class="form-control" value="<?php echo $user->team_name; ?>" disabled />
						  </div>
						</div>

						<div class="form-group">
						  <label class="col-md-2 control-label" for="team_lead">Team Leader</label>  
						  <div class="col-md-7">
						  	<input id="team_lead" name="team_lead" class="form-control input-md" value="<?php echo $user->team_lead_name; ?>" disabled />
						  </div>
						</div>
					</fieldset>

					<br/>

					<fieldset>

						<legend class="red_legend">Account Details</legend>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="email">Email Address</label>  
						  <div class="col-md-7">
						  <input id="email" name="email" type="text" class="form-control input-md" value="<?php echo $user->email_address; ?>" disabled />
						    
						  </div>
						</div>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="access_level">Access Level</label>
						  <div class="col-md-7">
						  	<?php
						  		if($user->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
						  			$access_level = ACCESS_LEVEL_ADMIN;
						  		} else {
						  			$access_level = ACCESS_LEVEL_USER;
						  		}
						  	?>
						    <input id="access_level" name="access_level" type="text" class="form-control" value="<?php echo $access_level; ?>" disabled />
						  </div>
						</div>

						<div class="form-group required">
						  <label class="col-md-2 control-label" for="user_status">User Status</label>
						  <div class="col-md-7">
						  	<?php
						  		$isActive = ($user->status == USER_STATUS_ACTIVE_FLAG);
						  		$isInactive = $isActive ? false : true;
						  	?>
						    <label class="radio-inline" for="user_status-0">
						      <input type="radio" name="user_status" id="user_status-1" value="1" <?php echo $isActive ? 'checked="checked"' : '' ?> disabled />
						      Active
						    </label> 
						    <label class="radio-inline" for="user_status-1">
						      <input type="radio" name="user_status" id="user_status-0" value="0" <?php echo $isInactive ? 'checked="checked"' : '' ?> disabled />
						      Inactive
						    </label>
						  </div>
						</div>

						<div class="form-group">
						  <label class="col-md-2 control-label" for="user_role">User Roles</label>
						  <div class="col-md-7">
						  	<?php
						  		$mrFlag = ($user->mr_flag == MR_FLAG);
						  		$imsFlag = ($user->ims_flag == IMS_FLAG);
						  	?>
						    <label class="checkbox-inline" for="user_role-0">
						      <input type="checkbox" name="ims_flag" id="ims_flag" <?php echo $imsFlag ? 'checked="checked"' : '' ?> value="1" disabled />
						      IMS User
						    </label>
						    <label class="checkbox-inline" for="user_role-1">
						      <input type="checkbox" name="mr_flag" id="mr_flag" <?php echo $mrFlag ? 'checked="checked"' : '' ?> value="1" disabled />
						      Management Representative
						    </label>
						  </div>
						</div>

						<div class="col-md-7 col-md-offset-2">
							<div class="alert alert-info">
								* only 1 Management Representative can be assigned for the entire system
							</div>
						</div>
						<div class="col-md-9 button_div">
							<a href="/user/edit/<?php echo $user->id; ?>" id="save_btn" class="btn btn-primary" data-toggle="modal">
			          <i class="glyphicon glyphicon-pencil"></i>&nbsp;Edit
			        </a>
			        &nbsp;
			        <a class="btn btn-black" data-toggle="modal" data-target="#confirm_delete_modal" >
			          <i class="glyphicon glyphicon-trash"></i>&nbsp;Delete
			        </a>
			        &nbsp;
			        <a id="back_button" href="/user/" class="btn btn-default">
			          <i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back
			        </a>
						</div>
					</fieldset>
				</form>
				<form id="user_delete_form" action="/user/delete" method="post" style="display: none;">
					<input type="hidden" id="user_id_hdn" name="id" value="<?php echo $user->id; ?>" />
				</form>
    		</div>
    	</div>
    </div>
  </div>
</div>
<!-- END OF CONTENT -->

<!-- MODALS -->
<div id="modals_container"> 
	<div class="modal fade" id="confirm_delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog medium_dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">Confirm Delete</h4>
	      </div>
	      <div class="modal-body">
	        Are you sure you want to delete this record?
	      </div>
	      <div class="modal-footer">
	      	<a id="true_delete_btn" class="btn btn-primary">
	          &nbsp;Ok
	        </a>
	        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
	          Cancel
	        </a>
	      </div>
	    </div> 
	  </div>
	</div>
</div>