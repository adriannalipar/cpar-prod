<div class="row">
	<div class="col-md-12">
		<legend class="red_legend edit">Review History</legend>
	</div>
	
	<div class="col-md-11 col-md-offset-1">
		<table id="review_history_tbl" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="stage">Stage</th>
					<th class="reviewed_date center">Reviewed Date</th>
					<th class="reviewed_by">Reviewed By</th>
					<th class="role center">Role</th>
					<th class="remarks">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
					get_instance()->load->helper('cpar_helper');

					if(!empty($review_history)) {
					
						foreach ($review_history as $rev):
							$reviewed_date_formatted = '';
							if(isset($rev['reviewed_date']) && strcmp($rev['reviewed_date'], NULL_DATE) != 0) {
								$date = new DateTime($rev['reviewed_date']);
								$reviewed_date_formatted = $date->format('M d, Y');
							}
				?>
							<tr>
								<td><?php echo 'Stage ' . $rev['stage']; ?></td>
								<td class="center review-history" data-id="<?php echo $rev['id']; ?>"><div class="cell-datepicker"><?php echo $reviewed_date_formatted; ?></div></td>
								<td><?php echo $rev['reviewed_by_name']; ?></td>
								<td class="center"><?php echo $rev['role']; ?></td>
								<td>
									<?php
										echo '<b>' . getReviewActionName($rev['action']) . '</b>';
										echo '<br/>';
										echo 'Remarks:&nbsp;&nbsp;&nbsp;' . $rev['remarks'];
									?>
								</td>
							</tr>
				<?php endforeach; } ?>
			</tbody>
		</table>
	</div>
</div>