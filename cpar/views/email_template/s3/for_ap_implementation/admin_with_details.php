Dear ADMIN (<?php echo $admin_name; ?>),<br/>
<br/>
CPAR (<?php echo $cpar_link; ?>) issued to Addressee (<?php echo $addressee_name; ?>) is due for follow-up implementation of IMS on <?php echo $month; ?> <?php echo $day; ?>, <?php echo $year; ?>.<br/>
<br/>
CPAR #: <?php echo $cpar_no; ?><br/>
Title: <?php echo $title; ?><br/>
<br/>
Action Plan Details:<br/>
<?php #action plan details here in tabular format 
	echo $table;
	
?>
<br/>
Thank you,<br/>
CPAR Database