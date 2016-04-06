<html>
	<head>
		<style>
			body {
				font-family: Calibri,Candara,Segoe,"Segoe UI",Optima,Arial,sans-serif;
			}
			input, label {
				margin: 0;
				padding: 0;
			}
			h4 {
				margin: 0;
			}
			table, th, td {
			    border: 0.5px solid black;
			    border-collapse: collapse;
			    line-height: 10px;
			    padding: 0;
			    margin: 0;
			    vertical-align: middle;
			}
			table.tbl-wrapper {
				border-spacing:0px;
				font-size: 10px;
				table-layout: fixed;
				width: 100%; 
			}
			table.tbl-wrapper td {
				padding: 1px 0 1px 5px;
			}
			th {
				font-weight: normal;
			}
			td.col-logo {
				width: 25%;
			}
			.tbl-bg {
				background: #DCDCDC;
			}
			.logo-wrapper {
				height: 50px;
				text-align: left;
			}
			.logo-wrapper h1 {
				color: red;
				font-family: 'helvetica';
				font-size: 30px;
				line-height: 40px;
				margin: 0;
			}
			.logo-img {
				margin: 12px 8px;
				max-height: 25px;
			}
			.title-wrapper {
				/*font-family: 'helvetica';*/
				font-size: 16px; 
				text-transform: uppercase; 
				text-align: center;
			}
			.cpar-wrapper {
				color: #303030;
				/*font-family: 'helvetica'; */
				font-size: 12px; 
			}
			.sub-text {
				font-weight: normal; 
				font-style: italic;
				font-size: 8px;
				margin: 0;
			}
			.text-normal {
				font-weight: normal;
			}
			.check-wrapper {
				display: block;
				position: relative;
			}
			.check-position {
				display: block; 
				font-size: 11px;
				position: absolute; 
				left: 20px; 
				top: 4px;
			}
			.text-lower {
				text-transform: lowercase;
			}
			.pagebreak {
				page-break-before: always;	
			}
			@page { 
				margin: 77px 30px 140px;
			}
		     #header { 
		     	left: 0; 
		     	position: fixed; 
		     	top: -55px;
		     	right: 0; 
				text-align: center; 
		     }
		     #footer { 
		     	bottom: -140px;
		     	border-top: 1px solid #696969;
		     	color: #696969;
		     	/*font-family: 'helvetica';*/
		     	font-size: 8px;
		     	height: 70px;
		     	position: fixed; 
		     	left: 0; 
		     	right: 0; 
		     }
/*
		     #footer .page:after { 
		     	content: counter(page, decimal) " of " counter(pages);
		     }
*/
		     .page {
		     	font-weight: normal;
		     	padding-left: 270px;
		     }
		     #footer div span {
		     	display: block;
		     	padding: 10px 0 0;
		     }
		     .cparno {
		     	display: inline;
		     	float: right;
		     	font-weight: normal;
		     	padding-left: 20px;
		     }
		     .sub_header_space {
			     padding-top: 10px;
		     }
		     .check-bigger {
			     font-size: 11px;
		     }
		     .content {
			     margin-left: 15px;
		     }
		     .div-margin-left {
			     margin-left: 15px;
		     }
		     .div-margin-bottom-left {
			     margin: 0 0 15px 15px;
		     }
		     .h4-no-bottom {
			     margin-bottom: 0px;
		     }
		     .add-gap {
			     margin-top: 1px;
		     } 
		     .span-lh {
			     line-height: 8px;
		     }
		     .checkbox-lh {
			     line-height: 11px;
		     }
		     .text-smaller {
			     font-size: 11px;
		     }
		</style>
	</head>	
	<body>
	<script type="text/php">
		<?php
			echo $script;
			
		    /*if ( isset($pdf) ) {
	
		      $font = Font_Metrics::get_font("helvetica", "bold");
		      $pdf->page_text(500,10, "Page: {PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(0,0,0));
	
		    }*/
	
		    if ( isset($pdf) ) { 
	
			    $pdf->page_script('
			        if ($PAGE_COUNT > 1) {
			            $font = Font_Metrics::get_font("Arial, Helvetica, sans-serif", "normal");
			            $size = 12;
			            $pageText = $PAGE_NUM . "of" . $PAGE_COUNT;
			            $y = $pdf->get_height() - 24;
			            $x = $pdf->get_width() - 15 - Font_Metrics::get_text_width($pageText, $font, $size);
			            $pdf->text($x, $y, $pageText, $font, $size);
			        } 
			    ');
			}
	    ?>
	</script>  

		<div id="header">
		     <table class="tbl-wrapper">
				<tr>
					<td class="col-logo">
						<div id="logo_container" class="logo-wrapper">
							<img id="logo" class="logo-img" src="<?php echo APPPATH; ?>resources/images/aboitiz_red_logo.png" alt=''></img>
						</div>
					</td>
					<td class="top-heading-title">
						<div class="title-wrapper">
							<b class="btm-border">C</b>orrective / <b class="btm-border">P</b>reventive <b class="btm-border">A</b>ction <b class="btm-border">R</b>equest<b> (CPAR)</b>
						</div>
					</td>
				</tr>
			</table>
	   </div>
<!--
	   <div id="footer">
	   	<div>
	   		<span>CORRECTIVE / PREVENTIVE (CONTINUAL IMPROVEMENT) ACTION REQUEST (CPAR)<b class="cparno">CPAR#: <?php echo $cpar->id; ?></b>
	   		<b class="page">Page </b>
	   		</span>
	   	</div>
	   </div>
-->
		<div id="cpar_info_container" class="cpar-wrapper">
			<!-- CPAR Information -->
			<div class="header">
				<h4>A. CPAR Information</h4>
			</div>
			
			<div class="content">
				<table class="tbl-wrapper">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">CPAR No.</span></td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $cpar->id; ?></span></td>
						<td class="tbl-border tbl-bg" width="12%"><span class="span-lh">Status</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $cpar->sub_status; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Date Filed</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo formatDateForDisplay($cpar->date_filed); #echo $cpar->rad_implemented_date; ?></span></td>
						<td class="tbl-border tbl-bg"><span class="span-lh">Type</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $cpar->type_name; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Raised as a Result of</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $cpar->raised_as_a_result_of; ?></span></td>
						<td class="tbl-border tbl-bg"><span class="span-lh">Process</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $cpar->process; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Reference/s</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $cpar->references; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Title of CPAR</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $cpar->title; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Details of the CPAR <br><h4 class="sub-text">*If record or evidence is available, please attach file</h4></span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $cpar->details; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Justifications of the CPAR</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $cpar->justification; ?></span></td>
					</tr>
				</table>
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Addressee</span></td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $cpar->addressee_name; ?></span></td>
						<td class="tbl-border tbl-bg" width="12%"><span class="span-lh">Team / Department</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $cpar->addressee_team; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Team Leader of Addressee</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $cpar->addressee_team_lead_name; ?></span></td>
					</tr>
				</table>
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">CPAR Filed by</span></td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $cpar->requestor_name; ?></span></td>
						<td class="tbl-border tbl-bg" width="12%"><span class="span-lh">Team / Department</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $cpar->requestor_team; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg"><span class="span-lh">Team Leader of Requester</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $cpar->requestor_team_lead_name; ?></span></td>
					</tr>
				</table>
			</div>
		</div>
	
		<div id="cpar_actions_container" class="cpar-wrapper">
			<?php
				if($cpar->type == CPAR_TYPE_C) {
					$prefix = 'Corrective';
					$subtext = '';
					$subtext2 = '';
					$subtext3 = '';
					$subtext4 = 'root cause analysis and';
				} else if($cpar->type == CPAR_TYPE_P) {
					$prefix = 'Preventive';
					$subtext = '&#40;Continual Improvement Action&#41;';
					$subtext2 = '&#40;Continual Improvement Action Plan Details&#41';
					$subtext3 = '&#40;Continual Improvement Action&#47;s&#41;';
					$subtext4 = '';
				}
			?>
			
			<?php if($cpar->type == CPAR_TYPE_C) { ?>
				<div class="sub_header sub_header_space">
					<h4>B. Immediate Remedial Action <span class="sub-text">(within 3 working days from the receipt of CPAR)</span></h4>
				</div>
				<div class="content">
					<table class="tbl-wrapper">
						<tr>
							<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Details of Immediate Remedial Action<br><h4 class="sub-text">*If record or evidence is available, please attach file</h4></span></td>
							<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $a_fields->rad_action; ?></span></td>
						</tr>
						<tr>
							<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Implemented by</span></td>
							<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $a_fields->rad_implemented_by_name; ?></span></td>
							<td class="tbl-border tbl-bg" width="12%"><span class="span-lh">Date Implemented</span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $a_fields->rad_implemented_date; ?></span></td>
						</tr>
					</table>
					<table class="tbl-wrapper add-gap">
						<tr>
							<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Immediate Remedial Action Verified by</span></td>
							<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $supplementary->immediate_remedial_action_verified_by; ?></span></td>
							<td class="tbl-border tbl-bg" width="12%"><span class="span-lh">Date Verified</span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $supplementary->immediate_remedial_action_verified_date; ?></span></td>
						</tr>
					</table>
				</div>
				<!-- <span class="pagebreak"></span> -->
				<!-- Root Cause Analysis -->
				<div class="sub_header sub_header_space">
					<h4>C. Root Cause Analysis <span class="sub-text">(within 7 working days from the receipt of CPAR)</span></h4>
				</div>
				<div class="content">
					<table class="tbl-wrapper">
						<tr>
							<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Root Cause Analysis Tool/s used<br><h4 class="sub-text">*If record or evidence is available, please attach file</h4></span></td>
							<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $a_fields->rca_tools; ?></span></td>
						</tr>
						<tr>
							<td class="tbl-border tbl-bg"><span class="span-lh">Details/Result of the Root Cause Analysis</span></td>
							<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $a_fields->rca_details; ?></span></td>
						</tr>
					</table>
					<table class="tbl-wrapper add-gap">
						<tr>
							<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Investigated by</span></td>
							<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $a_fields->rca_investigated_by_name; ?></span></td>
						</tr>
						<tr>
							<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Start Date</span></td>
							<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $a_fields->rca_investigated_date_started; ?></span></td>
							<td class="tbl-border tbl-bg" width="12%"><span class="span-lh">End Date</span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $a_fields->rca_investigated_date_ended; ?></span></td>
						</tr>
					</table>
				</div>
			<?php } ?>
			
			<!-- Corrective / Preventive Action -->
			<div class="sub_header sub_header_space">
				<h4>D. <?php echo $prefix; ?> Action <span class="text-normal"><?php echo $subtext; ?><span></h4>
			</div>
			<div class="content">
				<table class="tbl-wrapper">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh"><?php echo $prefix; ?> Action<br><h4 class="sub-text"><?php echo $subtext; ?> *If record or evidence is available, please attach file</h4></span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $a_fields->action; ?></span></td>
					</tr>
				</table>
			</div>

			<!-- <span class="pagebreak"></span> -->

			<!-- Corrective / Preventive Action Plan Details -->
			<div class="sub_header div-margin-left">
				<h4><?php echo $prefix; ?> Action Plan Details <span class="text-normal"><?php echo $subtext2; ?><span></h4>
			</div>
			<div class="content">
				<table class="tbl-wrapper">
					<tr>
						<th class="tbl-border tbl-bg" align="center" width="24%"><span class="span-lh">Task</span></th>
						<th class="tbl-border tbl-bg" align="center" width="19%"><span class="span-lh">Responsible Person</span></th>
						<th class="tbl-border tbl-bg" align="center" width="13%"><span class="span-lh">Due Date</span></th>
						<th class="tbl-border tbl-bg" align="center" width="17%"><span class="span-lh">Actual Completed Date</span></th>
						<th class="tbl-border tbl-bg" align="center"><span class="span-lh">Remarks</span></th>
					</tr>
					<?php
						get_instance()->load->helper('cpar_helper');

						if(!empty($tasks)) {
							foreach ($tasks as $task):					
					?>

						<tr>
							<td class="tbl-border"><span class="span-lh"><?php echo $task['task']; ?></span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $task['responsible_person_name']; ?></span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $task['due_date']; ?></span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $task['completed_date']; ?></span></td>
							<td class="tbl-border"><span class="span-lh"><?php echo $task['remarks_addr']; ?></span></td>
						</tr>
					
					<?php endforeach; } else { ?>
					<tr>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
					</tr>
					<tr>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
					</tr>
					<tr>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
					</tr>
					<tr>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
					</tr>
					<tr>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
						<td class="tbl-border">&nbsp;</td>
					</tr>
					<?php } ?>
				</table>
			</div>
			<div class="content">
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Proposed by</span></td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $a_fields->proposed_by_name; ?></span></td>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Date</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $a_fields->proposed_by_date; ?></span></td>
					</tr>
				</table>	
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Start Date of Implementation</span>/</td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $a_fields->target_start_date; ?></span></td>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">End Date of Implementation</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $a_fields->target_end_date; ?></span></td>
					</tr>
				</table>
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Approved by Team Leader</span></td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $supplementary->approved_by_team_leader; ?></span></td>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Date Approved</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $supplementary->approved_by_team_leader_date; ?></span></td>
					</tr>
				</table>
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh"><?php echo $prefix; ?> Action/s Verified by</span></td>
						<td class="tbl-border" width="32%"><span class="span-lh"><?php echo $supplementary->corrprev_actions_verified_by; ?></span></td>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Date Verified</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $supplementary->corrprev_actions_verified_date; ?></span></td>
					</tr>
				</table>
			</div>
	
			<!-- Follow Up History -->
			<div class="sub_header sub_header_space">
				<h4 class="h4-no-bottom">E. Follow-up on the Implementation of <?php echo $prefix; ?> Action/s</h4>
				<div class="text-normal div-margin-left span-lh text-smaller"><?php echo $subtext3; echo $prefix; ?> action/s is/are:</div>
				<div class="div-margin-left">
					<label class="check-wrapper checkbox-lh">
						<input type="checkbox" <?php echo ($supplementary->followup_implementation_effective !== NULL && $supplementary->followup_implementation_effective == TRUE) ? 'checked' : '';?>><span class="check-position">Implemented</span>
					</label>
					<label class="check-wrapper checkbox-lh">
						<input type="checkbox" <?php echo ($supplementary->followup_implementation_effective !== NULL && $supplementary->followup_implementation_effective == FALSE) ? 'checked' : '';?>><span class="check-position">Not implemented, review <?php echo $subtext4; ?> 
						<span class="text-lower"><?php echo $prefix; ?></span> action plan/s, next follow-up date on: <?php echo ($supplementary->followup_implementation_effective !== NULL && $supplementary->followup_implementation_effective == FALSE) ? $cpar->date_due : '';?></span>
					</label>
				</div>
			</div>
			<div class="content">
				<table class="tbl-wrapper">
					<tr>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Remarks</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $supplementary->followup_implementation_effective_remarks; ?></span></td>
					</tr>
					<tr>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Verified by</span></td>
						<td class="tbl-border" width="40%"><span class="span-lh"><?php echo $supplementary->followup_implementation_effective_by; ?></span></td>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Date</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $supplementary->followup_implementation_effective_date; ?></span></td>
					</tr>
				</table>
				<h4>Follow-up History</h4>
				<table class="tbl-wrapper">
					<tr>
						<th class="tbl-border tbl-bg" align="center" width="15%"><span class="span-lh">Date of follow-up</span></th>
						<th class="tbl-border tbl-bg" align="center" width="12%"><span class="span-lh">Total number of Tasks</span></th>
						<th class="tbl-border tbl-bg" align="center" width="11%"><span class="span-lh">No. of Task Completed</span></th>
						<th class="tbl-border tbl-bg" align="center" width="13%"><span class="span-lh">No. of On-going Task/s</span></th>
						<th class="tbl-border tbl-bg" align="center" width="12%"><span class="span-lh">No. of Overdue Task/s</span></th>
						<th class="tbl-border tbl-bg" align="center"><span class="span-lh">Status/Remarks</span></th>
					</tr>
					<?php
						get_instance()->load->helper('cpar_helper');

						if(!empty($ff_up_history)) {
							foreach ($ff_up_history as $ff_up):					
					?>
					<tr>
						<td class="tbl-border"><span class="span-lh"><?php echo formatDateForDisplay(isset($ff_up['ff_date']) ? $ff_up['ff_date'] : ''); ?></span></td>
						<td class="tbl-border" align="center"><span class="span-lh"><?php echo $ff_up['no_of_tasks']?></span></td>
						<td class="tbl-border" align="center"><span class="span-lh"><?php echo $ff_up['completed_tasks']?></span></td>
						<td class="tbl-border" align="center"><span class="span-lh"><?php echo $ff_up['ongoing_tasks']?></span></td>
						<td class="tbl-border" align="center"><span class="span-lh"><?php echo $ff_up['overdue_tasks']?></span></td>
						<td class="tbl-border" align="left"><span class="span-lh"><?php echo $ff_up['remarks']?></td>
					</tr>
					<?php endforeach; } else { ?>
					<tr>
						<td class="tbl-border" colspan="6" align="center">No follow-up history to display.</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		
			<!-- Verification on the Effectiveness of Corrective / Preventive Action -->
			<div class="sub_header sub_header_space">
				<h4 class="h4-no-bottom">F. Verification on the Effectiveness of <?php echo $prefix; ?> Action/s</h4>
				<div class="text-normal div-margin-left span-lh text-smaller"><?php echo $prefix; ?> action/s is/are:</div>
				<div class="div-margin-left">
					<label class="check-wrapper checkbox-lh">
						<input type="checkbox" <?php echo ($supplementary->verification_of_effectiveness !== NULL && $supplementary->verification_of_effectiveness == TRUE) ? 'checked' : '';?>><span class="check-position">Effective</span>
					</label>
					<label class="check-wrapper checkbox-lh">
						<input type="checkbox" <?php echo ($supplementary->verification_of_effectiveness !== NULL && $supplementary->verification_of_effectiveness == FALSE) ? 'checked' : '';?>><span class="check-position">Not effective, review <?php echo $subtext4; ?> <span class="text-lower"><?php echo $prefix; ?></span> action plan/s, next follow-up date on: <?php echo ($supplementary->verification_of_effectiveness !== NULL && $supplementary->verification_of_effectiveness == FALSE) ? $cpar->date_due : '';?></span>
					</label>
				</div>
			</div>
			<div class="content">
				<table class="tbl-wrapper">
					<tr>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Remarks</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $supplementary->verification_of_effectiveness_remarks; ?></span></td>
					</tr>
				</table>
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Verified by</span></td>
						<td class="tbl-border" width="40%"><span class="span-lh"><?php echo $supplementary->verification_of_effectiveness_by; ?></span></td>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Date</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $supplementary->verification_of_effectiveness_date; ?></span></td>
					</tr>
				</table>
			</div>
			<!-- <span class="pagebreak"></span> -->
			<!-- Management Representative Comments -->
			<div class="sub_header sub_header_space">
				<h4>G. Management Representative Comments</h4>
				<div class="div-margin-left">
					<label class="check-wrapper check-bigger checkbox-lh">
						<input type="checkbox" <?php echo ($supplementary->mr_effectiveness !== NULL && $supplementary->mr_effectiveness == TRUE) ? 'checked' : '';?>><span class="check-position">CPAR Close</span>
					</label>
					<label class="check-wrapper check-bigger checkbox-lh">
						<input type="checkbox" <?php echo ($supplementary->mr_effectiveness !== NULL && $supplementary->mr_effectiveness == FALSE) ? 'checked' : '';?>><span class="check-position">Needs to have further verification of effectiveness of <span class="text-lower"><?php echo $prefix; ?></span> actions <?php echo $subtext; ?></span>
					</label>
				</div>
			</div>
			<div class="content">
				<table class="tbl-wrapper">
					<tr>
						<td class="tbl-border tbl-bg" width="15%"><span class="span-lh">Remarks</span></td>
						<td colspan="3" class="tbl-border"><span class="span-lh"><?php echo $supplementary->mr_effectiveness_remarks; ?></span></td>
					</tr>
				</table>
				<table class="tbl-wrapper add-gap">
					<tr>
						<td class="tbl-border tbl-bg" width="25%"><span class="span-lh">Management Representative</span></td>
						<td class="tbl-border" width="45%"><span class="span-lh"><?php echo $supplementary->mr_effectiveness_by; ?></span></td>
						<td class="tbl-border tbl-bg" width="10%"><span class="span-lh">Date</span></td>
						<td class="tbl-border"><span class="span-lh"><?php echo $supplementary->mr_effectiveness_date; ?></span></td>
					</tr>
				</table>
			</div>

		</div>
	</body>
</html>