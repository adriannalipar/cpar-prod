		</div>
		<!-- FOOTER -->
		<footer id="footer">
		  <div class="container">
		    <div class="row">
	        <div class="col-md-6">
	          <p>&copy; 2014 Aboitiz Equity Ventures | All rights reserved</p>
	        </div>
	        <div id="back_to_top_container" class="col-md-6">
	          <a href="#top" class="top-link" title="Back to top">Back To Top <i class="fa fa-chevron-up"></i></a>
	        </div>
		    </div>
		  </div>
		</footer>
		
		<div class="modal fade" id="message_modal" tabindex="-1" role="dialog" aria-hidden="true">
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

		<div class="modal fade" id="session_message_modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog small_dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Session</h4>
					</div>
					<div class="modal-body">
						<i class="glyphicon glyphicon-time"></i>&nbsp;&nbsp;
						<span class="session_modal_message">Your session is about to expire. Would you wish to continue?</span>
					</div>
					<div class="modal-footer">						
						<a data-dismiss="modal" aria-hidden="true" class="btn btn-primary" id="session_update">
							Yes
						</a>
						<a class="btn btn-default" href="/login/logout">
							No
						</a>
					</div>
				</div> 
			</div>
		</div>
			
		<!-- END OF FOOTER -->
		<!--Scripts --> 
		<script src="/_theme/js/jquery-migrate-1.2.1.min.js"></script>
		<script src="/_js/common.js"></script>
		<script src="/_js/session_timeout.js"></script>
		<script src="/_js/blockUI/blockUI.js"></script>
		<!-- Bootstrap JS --> 
		<script src="/_theme/js/bootstrap.min.js"></script>

		<!--JS plugins--> 
		<script src="/_theme/plugins/prism/prism.js"></script>
		<script src="/_theme/plugins/clingify/jquery.clingify.min.js"></script>
		<script src="/_theme/plugins/jPanelMenu/jquery.jpanelmenu.min.js"></script> 
		<script src="/_theme/plugins/jRespond/js/jRespond.js"></script> 
		<script src="/_theme/plugins/quicksand/jquery.quicksand.js"></script>		
		<script src="/_js/datepicker/js/bootstrap-datepicker.js"></script>
		
		<!--Retina.js plugin - @see: http://retinajs.com/-->
		<script src="/_theme/plugins/retina/js/retina-1.1.0.min.js"></script>
		
		<script src="/_js/ss-upload.js"></script>
		<script src="/_js/export_to_pdf.js"></script>
		<script src="/_js/dateFormat.min.js"></script>
		<script src="/_js/jquery-dateFormat.min.js"></script>
		<script src="/_js/angular.js"></script>
		<script src="/_js/ng/app.js"></script>
		<script src="/_js/ng/services.js"></script>
		<script src="/_js/ng/controllers.js"></script>
		<script src="/_js/ng/filters.js"></script>
		<script src="/_js/ng/directives.js"></script>
		<?php if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) { ?>
		<script src="/_js/cell_datepicker.js"></script>
		<?php } ?>
		
	</body>
</html>