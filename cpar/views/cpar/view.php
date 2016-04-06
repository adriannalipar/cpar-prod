<!-- CONTENT -->
<div id="content">
  <div class="container">

    <!-- Steps -->
    <?php $this->load->view('partials/steps', array('step_title' => $header_text_1.' <span class="de-em">'.$header_text_2.'</span>', 'step_sub_header' => $header_text_3, 'step_active' => 1)); ?>

    <form id="cpar_form" class="form-horizontal basic_form">
	    <div class="row">
	    	<?php 
	    		$sub_form = array('main_form_edit_disabled' => TRUE);
	    		$this->load->view('partials/main_form_edit', $sub_form); 
	    	?>
	    	<?php require_once('partial/_cpar_form_view.php'); ?>
	    </div>

	    <hr class="top-separator">

	    <!-- Addressed to / Requestor or Originator -->
	    <?php require_once('partial/_addressee_requestor_info_view.php'); ?>

	    <br/>
	    <br/>	    

	    <!-- form buttons -->
	    <div class="edit_buttons_container row">
	    	<div class="col-md-12 button_div">
				<!-- Export PDF Button -->
				<a id="export_to_pdf_btn" class="btn btn-black">
					<i class="glyphicon glyphicon-export"></i>&nbsp;Export to PDF
				</a>
				&nbsp;
				<!--// Export PDF Button -->
	        <a id="back_button" href="/cpar<?php echo ($cpar && $cpar->status) ? '?tab='.$cpar->status : ''; ?>" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back</a>
				</div>
	    </div>
    </form>

  </div>
</div>
<!-- END OF CONTENT -->