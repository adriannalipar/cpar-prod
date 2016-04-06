<!-- CONTENT -->
<div id="content">
  <div class="container">
    <!-- Steps -->
    <?php $this->load->view('partials/steps', array(
	    												'step_title' => $header_text_1.' <span class="de-em">'.$header_text_2.'</span>', 
	    												'step_sub_header' => $header_text_3, 
	    												'step_active' => 1)
    												); ?>

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
	    	<?php $this->load->view('partials/main_form_edit'); ?>
	    	<?php require_once('partial/_cpar_form_edit.php'); ?>
	    </div>

	    <hr class="top-separator">

	    <!-- Addressed to / Requestor or Originator -->
	    <?php require_once('partial/_addressee_requestor_info_edit.php'); ?>

	    <!-- Only rendered when CPAR is for IMS Review -->
	 		<?php
	 			/* Save banner for IMS Review */
	 			if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0) {
			    	require_once('partial/_ims_review.php');
				} else {
					#<!-- SHOULD ONLY BE SHOWN IF IMS -->
					if($is_ims) {
						$this->load->view('partials/actions_per_stage/stage1_ims');
					}
					#<!--// SHOULD ONLY BE SHOWN IF IMS -->
				}
	    ?>

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
	    		<?php if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0) { ?>
	    			<a id="proceed_btn" class="submit_btn btn btn-primary"><i class="glyphicon glyphicon-chevron-right"></i>&nbsp;Proceed</a>
		    		&nbsp;
	    		<?php } else { ?>
	    			<?php if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_DRAFT) == 0) { ?>
		    			<a class="btn btn-black" data-toggle="modal" data-target="#confirm_delete_modal" ><i class="glyphicon glyphicon-trash"></i>&nbsp;Delete</a>
			    		&nbsp;
			    		<a id="save_draft_btn" class="submit_btn btn btn-black"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Save as Draft</a>
			        &nbsp;
		        <?php } ?>
		        <?php if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_PUSHED_BACK) == 0) { ?>
			        <a id="save_only_btn" class="submit_btn btn btn-black"><i class="glyphicon glyphicon-floppy-disk"></i>&nbsp;Save Only</a>
				      &nbsp;
			      <?php } ?>
						<a id="submit_btn" class="submit_btn btn btn-primary"><i class="glyphicon glyphicon-floppy-saved"></i>&nbsp;Submit</a>
		        &nbsp;
	    		<?php } ?>
	        <a id="back_button" href="/cpar<?php echo ($cpar && $cpar->status) ? '?tab='.$cpar->status : ''; ?>" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i>&nbsp;Back</a>
				</div>
	    </div>
    </form>

    <!-- delete form -->
    <form id="cpar_delete_form" action="/cpar/delete" method="post" style="display: none;">
			<input type="hidden" id="cpar_id_hdn" name="id" value="<?php echo $cpar->id; ?>" />
		</form>

  </div>
</div>
<!-- END OF CONTENT -->
 
<div id="modals_container"> 
	<div class="modal fade" id="confirm_delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog medium_dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">Confirm Delete</h4>
	      </div>
	      <div class="modal-body">
	        Are you sure you want to delete this record?
	      </div>
	      <div class="modal-footer">
	      	<a id="true_delete_btn" class="btn btn-primary">
	          &nbsp;Ok
	        </a>
	        <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
	          Cancel
	        </a>
	      </div>
	    </div> 
	  </div>
	</div>
</div>

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

<div class="modal fade" id="successful_save_nr_modal" tabindex="-1" role="dialog" aria-hidden="true">
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
<script src="/_js/cpar/create.js"></script>
<script src="/_js/select2/select2.min.js"></script>
<link href="/_js/select2/select2.css" rel="stylesheet">
<link href="/_js/select2/select2-bootstrap.css" rel="stylesheet">