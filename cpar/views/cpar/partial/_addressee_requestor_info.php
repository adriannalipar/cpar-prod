<div class="row">	    	
	<div class="col-md-6">
		<fieldset>
			<legend class="red_legend">Addressed To</legend>
			
			<!-- Name -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="at_name">Name</label>  
			  <div class="col-md-9">
			  	<input id="at_name" name="at_name" type="hidden" class="adr_req_name form-control" required="" />
			  </div>
			</div>

			<!-- Team -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="at_team">Team</label>
			  <div class="col-md-9">
			    <select id="at_team" name="at_team" class="adr_req_team form-control">
			      <option value="">Please Select</option>
			      <?php foreach($team_list as $team): ?>
			      	<option <?php ?> value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- TL -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="at_team_lead">Team Leader</label>
			  <div class="col-md-9">
			  	<input id="at_team_lead" name="at_team_lead" type="hidden" class="adr_req_team_lead form-control" required="" />
			  </div>
			</div>

		</fieldset>
	</div>
	<div class="col-md-6">
		<fieldset>
			<legend class="red_legend">Requester / Originator</legend>

			<!-- Name -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="req_name">Name</label>  
			  <div class="col-md-9">
			  	<input id="req_name" name="req_name" type="hidden" class="adr_req_name form-control" value='[{"id":"<?php echo $requestor->id; ?>","text":"<?php echo $requestor->name; ?>"}]' />
			  </div>
			</div>

			<!-- Team -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="req_team">Team</label>
			  <div class="col-md-9">
			  	<select id="req_team" name="req_team" class="adr_req_team form-control">
			      <option value="">Please Select</option>
			      <?php foreach($team_list as $team): ?>
			      	<option <?php echo $team['id'] == $requestor->team_id ? "selected='selected'" : ""; ?> value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- TL -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="req_team_lead">Team Leader</label>
			  <div class="col-md-9">
			  	<input id="req_team_lead" name="req_team_lead" type="hidden" class="adr_req_team_lead form-control" value='[{"id":"<?php echo $requestor->team_lead_id; ?>","text":"<?php echo $requestor->team_lead; ?>"}]' />
			  </div>
			</div>

		</fieldset>
	</div>
</div>