<div class="row">	    	
	<div class="col-md-6">
		<fieldset>
			<legend class="red_legend">Addressed To</legend>
			
			<!-- Name -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="at_name">Name</label>  
			  <div class="col-md-9">
			  	<input id="at_name" name="at_name" type="text" class="form-control" value='<?php echo $cpar->addressee_name; ?>' disabled="disabled" />
			  </div>
			</div>

			<!-- Team -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="at_team">Team</label>
			  <div class="col-md-9">
			    <select id="at_team" name="at_team" class="form-control" disabled="disabled">
			      <?php foreach($team_list as $team): ?>
			      	<option <?php echo $team['id'] == $cpar->addressee_team ? "selected='selected'" : ""; ?> value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- TL -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="at_team_lead">Team Leader</label>
			  <div class="col-md-9">
			  	<input id="at_name" name="at_name" type="text" class="form-control" value='<?php echo $cpar->addressee_team_lead_name; ?>' disabled="disabled" />
			  </div>
			</div>

			<?php
				$date_due_formatted = '';
				if(!empty($cpar->date_due)) {
					$date = new DateTime($cpar->date_due);
					$date_due_formatted = $date->format('M d, Y');
				}
			?>
		</fieldset>
	</div>
	<div class="col-md-6">
		<fieldset>
			<legend class="red_legend">Requester / Originator</legend>

			<!-- Name -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="req_name">Name</label>  
			  <div class="col-md-9">
			  	<input id="req_name" name="req_name" type="text" disabled="disabled" class="form-control" value='<?php echo $cpar->requestor_name; ?>' />
			  </div>
			</div>

			<!-- Team -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="req_team">Team</label>
			  <div class="col-md-9">
			  	<select id="req_team" name="req_team" class="form-control" disabled="disabled">
			      <option value="">Please Select</option>
			      <?php foreach($team_list as $team): ?>
			      	<option <?php echo $team['id'] == $cpar->requestor_team ? "selected='selected'" : ""; ?> value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- TL -->
			<div class="form-group">
			  <label class="col-md-3 control-label" for="req_team_lead">Team Leader</label>
			  <div class="col-md-9">
			    <input id="req_team_lead" name="req_team_lead" type="text" disabled="disabled" class="form-control" value='<?php echo $cpar->requestor_team_lead_name; ?>' />
			  </div>
			</div>

		</fieldset>
	</div>
</div>