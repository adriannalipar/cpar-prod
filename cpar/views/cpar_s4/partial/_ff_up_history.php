<div class="row">
	<div class="col-md-12">
		<legend class="red_legend edit">Follow Up History</legend>
	</div>
	
	<div class="col-md-11 col-md-offset-1">
		<table id="review_history_tbl" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="ff_up_date center">Follow Up Date</th>
					<th class="no_of_tasks center">No of Tasks</th>
					<th class="completed_tasks center">Completed Tasks</th>
					<th class="ongoing_tasks center">Ongoing Tasks</th>
					<th class="pending_tasks center">Pending Tasks</th>
					<th class="overdue_tasks center">Overdue Tasks</th>
					<th class="ff_up_result">Follow Up Result</th>
					<th class="next_ff_up center">Next Follow-up</th>
					<th class="remarks_attachment">Remarks/File Attachments</th>
				</tr>
			</thead>
			<tbody>
				<?php
					get_instance()->load->helper('cpar_helper');

					if(!empty($ff_up_history)) {
						foreach ($ff_up_history as $ff_up):					
				?>
							<tr>
								<td class="center"><?php echo formatDateForDisplay(isset($ff_up['ff_date']) ? $ff_up['ff_date'] : ''); ?></td>
								<td class="center"><?php echo $ff_up['no_of_tasks']?></td>
								<td class="center"><?php echo $ff_up['completed_tasks']?></td>
								<td class="center"><?php echo $ff_up['ongoing_tasks']?></td>
								<td class="center"><?php echo $ff_up['pending_tasks']?></td>
								<td class="center"><?php echo $ff_up['overdue_tasks']?></td>
								<td><?php echo getFfUpResultName($ff_up['ff_result'])?></td>
								<td class="center"><?php echo formatDateForDisplay(isset($ff_up['next_ff_date']) ? $ff_up['next_ff_date'] : ''); ?></td>
								<td style="width:33%;word-break:break-all;">
									<?php echo $ff_up['remarks']?>
									<br/>
									<?php 
										$filenames = $ff_up['filenames'];
						  			if(!($filenames == null || empty($filenames))) {
						  				foreach ($filenames as $file):
						  					$url = '/file/get/' . $file->filename;
						  		?>
							  				<a href="<?php echo $url; ?>" class="file_name"><?php echo $file->orig_filename; ?></a></br>
						  		<?php 
						  				endforeach;
						  			}
						  		?>
								</td>
							</tr>
				<?php endforeach; } else { ?>
					<tr>
						<td colspan="9" class="center">No follow-up history to display.</td>
					<tr/>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>