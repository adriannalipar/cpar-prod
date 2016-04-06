<!-- CONTENT -->
<div id="content">
  <div class="container">
    <!-- Steps -->
	<?php $this->load->view('partials/steps', array('step_title' => 'Create <span class="de-em">CPAR</span>', 'step_active' => 1)); ?>
	
    <!-- errors -->
    <div class="row">
    	<div class="col-md-12">
				<div class="error_container alert alert-danger">
					<button type="button" class="close custom_alert_hide">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Close</span>
					</button>
					<div class="error_content">
					</div>
				</div>
			</div>
    </div>

    <form id="cpar_form" class="form-horizontal basic_form" enctype="multipart/form-data">
	    <!-- CPAR Information -->
	    <div class="row">
	    	<?php //require_once('partial/_cpar_form.php'); ?>
	    	<?php $this->load->view('partials/main_form'); ?>
	    </div>

	    <hr class="top-separator">

	    <!-- Addressed to / Requestor or Originator -->
	    <?php require_once('partial/_addressee_requestor_info.php'); ?>

		<!-- SHOULD ONLY BE SHOWN IF IMS -->
		
		<?php

			if($is_ims) {
				$this->load->view('partials/actions_per_stage/stage1_ims');	
			}

		?>

	    <br/>		
		<!--// SHOULD ONLY BE SHOWN IF IMS -->


	    <!-- form buttons -->
	    <div class="row">
	    	<div class="col-md-12 button_div">
	    		<a id="save_draft_btn" class="submit_btn btn btn-black"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Save as Draft</a>
	        &nbsp;	    		
					<a id="submit_btn" class="submit_btn btn btn-primary"><i class="glyphicon glyphicon-floppy-saved"></i>&nbsp;Submit</a>
	        &nbsp;
	        <a id="back_button" href="/cpar/" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back</a>
				</div>
	    </div>

    </form>
  </div>
</div>
<!-- END OF CONTENT -->

<div class="modal fade" id="successful_save_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog small_dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Success</h4>
      </div>
      <div class="modal-body">
         <i class="glyphicon glyphicon-saved"></i>&nbsp;&nbsp;
         <span class="successful_save_modal_message">CPAR successfully saved.</span>
      </div>
      <div class="modal-footer">
        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
          close
        </a>
      </div>
    </div> 
  </div>
</div>
<script src="/_js/select2/select2.min.js"></script>
<link href="/_js/select2/select2.css" rel="stylesheet">
<link href="/_js/select2/select2-bootstrap.css" rel="stylesheet">
<script src="/_js/cpar/create.js"></script>