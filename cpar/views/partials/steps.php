<!-- Steps -->
	<div class="row">
		<div class="col-md-3">
			<h2 class="no_top_margin main-title">
				<span><?php echo $step_title; ?></span>
				<?php if(isset($step_sub_header)) { ?>
					<span class="sub_header" <?php echo isset($step_sub_header_style) ? 'style="'.$step_sub_header_style.'"' : ''?>><?php echo $step_sub_header; ?></span>
				<?php } ?>
			</h2>
		</div>
		<div class="col-md-9">
			<div class="steps">
				<div class="step <?php echo isset($step_active) && $step_active > 0 ? 'active' : ''; ?>"><span>Stage 1</span><span>CPAR Request</span></div>
				<div class="step <?php echo isset($step_active) && $step_active > 1 ? 'active' : ''; ?>"><span>Stage 2</span><span>For CA/PA</span></div>
				<div class="step <?php echo isset($step_active) && $step_active > 2 ? 'active' : ''; ?>"><span>Stage 3</span><span>Follow-up Implementation</span></div>
				<div class="step <?php echo isset($step_active) && $step_active > 3 ? 'active' : ''; ?>"><span>Stage 4</span><span>Effectiveness Verification</span></div>
				<div class="step <?php echo isset($step_active) && $step_active > 4 ? 'active' : ''; ?>"><span>Stage 5</span><span>Closed</span></div>
			</div>
		</div>
	</div>
<!--// Steps -->