<div class="col-md-4">
	<!-- Additional Info -->
	<div class="additional_info">
		<?php 
			$header_text = 'This CPAR requires additional information / revision.';
		?>
		<div class="additional_info_title"><?php echo $header_text; ?></div>
		<br/>
		<span>Reviewer</span>
		<div class="adtl_info_text"><?php echo $cpar->pb_user_name; ?></div>

		<span>Date</span>
		<?php
			$date = new DateTime($cpar->pb_date);
			$pb_date_formatted = $date->format('M d, Y h:i:s A');
		?>
		<div class="adtl_info_text"><?php echo $pb_date_formatted; ?></div>

		<span>Remarks</span>
		<div class="adtl_info_text scrolled"><?php echo $cpar->pb_remarks; ?></div>
	
		<br/>
		<div class="additional_info_title">Please make the necessary corrections then click on SUBMIT button below.</div>
	</div>
</div>