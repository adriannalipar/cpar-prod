<div class="row">	    	
	<div class="col-md-6">
		<fieldset>
			<legend class="red_legend">Addressed To</legend>
			
			<!-- (Addressee) Name -->
			<?php
				$sel2val = '';
				if((int)$cpar->addressee != 0) {
					$sel2val = '[{"id":"' . $cpar->addressee . '","text":"' . $cpar->addressee_name . '"}]';
				}
			?>
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="at_name">Name</label>  
			  <div class="col-md-9">
			  	<input id="at_name" name="at_name" type="hidden" class="adr_req_name form-control" value='<?php echo $sel2val; ?>' />
			  </div>
			</div>

			<!-- (Addressee) Team -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="at_team">Team</label>
			  <div class="col-md-9">
			    <select id="at_team" name="at_team" class="adr_req_team form-control">
			      <option value="">Please Select</option>
			      <?php foreach($team_list as $team): ?>
			      	<option <?php echo $team['id'] == $cpar->addressee_team ? "selected='selected'" : ""; ?> value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- (Addressee) TL -->
			<?php
				$sel2val = '';
				if((int)$cpar->addressee_team_lead != 0) {
					$sel2val = '[{"id":"' . $cpar->addressee_team_lead . '","text":"' . $cpar->addressee_team_lead_name . '"}]';
				}
			?>
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="at_team_lead">Team Leader</label>
			  <div class="col-md-9">
			  	<input id="at_team_lead" name="at_team_lead" type="hidden" class="adr_req_team_lead form-control" value='<?php echo $sel2val; ?>' />
			  </div>
			</div>

		</fieldset>
	</div>
	<div class="col-md-6">
		<fieldset>
			<legend class="red_legend">Requester / Originator</legend>

			<!-- (Requester) Name -->
			<?php
				$sel2val = '';
				if((int)$cpar->requestor != 0) {
					$sel2val = '[{"id":"' . $cpar->requestor . '","text":"' . $cpar->requestor_name . '"}]';
				}
			?>
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="req_name">Name</label>  
			  <div class="col-md-9">
			  	<input id="req_name" name="req_name" type="hidden" class="adr_req_name form-control" value='<?php echo $sel2val; ?>' />
			  </div>
			</div>

			<!-- (Requester) Team -->
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="req_team">Team</label>
			  <div class="col-md-9">
			  	<select id="req_team" name="req_team" class="adr_req_team form-control">
			      <option value="">Please Select</option>
			      <?php foreach($team_list as $team): ?>
			      	<option <?php echo $team['id'] == $cpar->requestor_team ? "selected='selected'" : ""; ?> value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
			      <?php endforeach; ?>
			    </select>
			  </div>
			</div>

			<!-- (Requester) TL -->
			<?php
				$sel2val = '';
				if((int)$cpar->requestor_team_lead != 0) {
					$sel2val = '[{"id":"' . $cpar->requestor_team_lead . '","text":"' . $cpar->requestor_team_lead_name . '"}]';
				}
			?>
			<div class="form-group required">
			  <label class="col-md-3 control-label" for="req_team_lead">Team Leader</label>
			  <div class="col-md-9">
			    <input id="req_team_lead" name="req_team_lead" type="hidden" class="adr_req_team_lead form-control" value='<?php echo $sel2val; ?>'/>
			  </div>
			</div>

		</fieldset>
	</div>
</div>