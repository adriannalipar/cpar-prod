Dear Addressee (<?php echo $addressee_name; ?>),<br/>
<br/>
CPAR (<?php echo $cpar_link; ?>) issued to you was disapproved by <?php echo $sent_by == 'Team Leader' ? 'your ': ''; ?><?php echo $sent_by; ?> (<?php echo $pushed_back_name; ?>).<br/>
<br/>
CPAR #: <?php echo $cpar_no; ?><br/>
Title: <?php echo $title; ?><br/>
<br/>
<?php echo $sent_by; ?>'s Comments/Remarks:<br/>
<?php echo $remarks; ?><br/>
<br/>
Thank you,<br/>
CPAR Database