<div class="row">
	<div class="col-md-12">
		<div class="save_banner alert alert-danger">
			<div class="save_banner_ontent">
				<div class="left-paragraph">
					<span class="sb_header">Have you made any changes to the CPAR record above?</span>
					Please click on the button below to save your updates. Otherwise, you will lose your changes.
				</div>
				<div class="right-buttons">
					<a id="save_whole_cpar_btn" class="submit_btn btn btn-black">
				        <i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Save CPAR Changes
			        </a>
			        &nbsp;
			        <?php if(isset($save_whole_cpar_show_reminder) && $save_whole_cpar_show_reminder) { ?>
			        <a id="send_reminder" class="btn btn-black">
						<i class="glyphicon glyphicon-envelope"></i>&nbsp;Send Reminder
					</a>
					<?php } ?>
			    </div>
			</div>
		</div>
	</div>
</div>