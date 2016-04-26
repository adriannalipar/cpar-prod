<html lang="en" ng-app="cparNg">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Aboitiz | <?php echo isset($screen_title) ? $screen_title : 'CPAR Database'; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!--Scripts --> 
		<script src="/_theme/js/jquery.min.js"></script>
		
		<!--Legacy jQuery support for quicksand plugin-->
		
		<link href="/_js/datepicker/css/datepicker3.css" rel="stylesheet">


		<!--Custom scripts mainly used to trigger libraries --> 
		<script src="/_theme/js/script.min.js"></script>

		<!-- Bootstrap CSS -->
		<link href="/_theme/css/bootstrap.min.css" rel="stylesheet">

		<!-- Font Awesome -->
		<link href="/_theme/css/font-awesome.min.css" rel="stylesheet">

		<!-- Plugins -->
		<link href="/_theme/plugins/prism/prism.css" media="screen" rel="stylesheet" />
		<link href="/_theme/plugins/slider-revolution/rs-plugin/css/settings.css?v=4.2" media="screen" rel="stylesheet" />
		<link href="/_theme/plugins/animate/animate.css" rel="stylesheet">
		<link href="/_theme/plugins/flexslider/flexslider.css" rel="stylesheet">
		<link href="/_theme/plugins/clingify/clingify.css" rel="stylesheet">

		<!-- Theme style -->
		<link href="/_theme/css/theme-style.css" rel="stylesheet">
		<link href="/_theme/css/colour-aboitiz-red.css" id="colour-scheme" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300|Rambla|Calligraffitti' rel='stylesheet' type='text/css'>

		<!-- Your custom override -->
		<link href="/_theme/css/custom-style.css" rel="stylesheet">
		
		<?php if($this->session->userdata('logged_in_user') && $this->session->userdata('logged_in_user')->access_level == ACCESS_LEVEL_ADMIN_FLAG) { ?>
			<link href="/_css/cell_datepicker.css" rel="stylesheet">
		<?php } ?>
		
		<link rel="shortcut icon" type="image/png" href="/_images/favicon.png" />
		<script>
			var MIN_CPAR_TITLE = <?php echo MIN_CPAR_TITLE?>;
			var MIN_CPAR_DETAILS = <?php echo MIN_CPAR_DETAILS?>;
			var MIN_CPAR_JUSTIFICATION = <?php echo MIN_CPAR_JUSTIFICATION?>;
			var MIN_CPAR_REFERENCES = <?php echo MIN_CPAR_REFERENCES?>;
			var MIN_CPAR_REMARKS = <?php echo MIN_CPAR_REMARKS?>;
			
			var MAX_CPAR_TITLE = <?php echo MAX_CPAR_TITLE?>;
			var MAX_CPAR_DETAILS = <?php echo MAX_CPAR_DETAILS?>;
			var MAX_CPAR_JUSTIFICATION = <?php echo MAX_CPAR_JUSTIFICATION?>;
			var MAX_CPAR_REFERENCES = <?php echo MAX_CPAR_REFERENCES?>;
			var MAX_CPAR_REMARKS = <?php echo MAX_CPAR_REMARKS?>;
			
			var CPAR_MINI_STATUS_DRAFT = "<?php echo CPAR_MINI_STATUS_DRAFT?>";
			var CPAR_MINI_STATUS_FOR_IMS_REVIEW = "<?php echo CPAR_MINI_STATUS_FOR_IMS_REVIEW?>";
			var CPAR_MINI_STATUS_PUSHED_BACK = "<?php echo CPAR_MINI_STATUS_PUSHED_BACK?>";
			var CPAR_MINI_STATUS_CLOSED = "<?php echo CPAR_MINI_STATUS_CLOSED?>";
			
			var MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION = <?php echo MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION?>;
			var MIN_CPAR_CORR_PREV_ACTION = <?php echo MIN_CPAR_CORR_PREV_ACTION?>;
			var MIN_CPAR_OTHERS = <?php echo MIN_CPAR_OTHERS?>;
			var MIN_CPAR_TASK = <?php echo MIN_CPAR_TASK?>;
			
			var CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES = "<?php echo CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES?>";
			var CPAR_SUBMIT_S1_SAVE_ONLY = "<?php echo CPAR_SUBMIT_S1_SAVE_ONLY?>";
			var CPAR_SUBMIT_S1_PROCEED = "<?php echo CPAR_SUBMIT_S1_PROCEED?>";
			var CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES = "<?php echo CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES?>";
			
			var MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION = <?php echo MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION?>;
			var MAX_CPAR_CORR_PREV_ACTION = <?php echo MAX_CPAR_CORR_PREV_ACTION?>;
			var MAX_CPAR_OTHERS = <?php echo MAX_CPAR_OTHERS?>;
			var MAX_CPAR_TASK = <?php echo MAX_CPAR_TASK?>;
			
			//Tools Used (Others)
			var TOOLS_USED_OTHERS_ID = <?php echo TOOLS_USED_OTHERS_ID?>;
						
			//Task Status
			var APD_STATUS_PENDING = <?php echo APD_STATUS_PENDING?>;
			var APD_STATUS_ONGOING = <?php echo APD_STATUS_ONGOING?>;
			var APD_STATUS_DONE = <?php echo APD_STATUS_DONE?>;
			var APD_STATUS_OVERDUE = <?php echo APD_STATUS_OVERDUE?>;
			
			//Pop-up before Timeout
			var POP_UP_BEFORE_TIMEOUT = <?php echo POP_UP_BEFORE_TIMEOUT?>;
			
			//Idle time
			var IDLE_TIME = <?php echo IDLE_TIME?>;
			var EXECUTE_LOGOUT;
		</script>
	</head>

	<body class="page page-user-mgt">
		<div class="page-wrap">
		<!-- HEADER -->
		<div id="navigation" class="wrapper"> 
		  <!--Branding & Navigation Region-->
		  <div class="navbar-static-top"> 	    
		    <!--Header & Branding region-->
		    <div class="header">
		      <div class="header-inner container">
		        <div class="row">
		          <div id="aboitiz_logo_col" class="col-md-8">
		          	<div id="aboitiz_logo_container">
          			  <img src="/_images/aboitiz_red_logo.png" alt="Aboitiz Logo" ></img>
		          	</div>
		          </div>
		          <div id="app_name_container" class="col-md-4">
		          	<span id="app_name">CPAR Database</span>
		          </div>
		        </div>
		      </div>
		    </div>	    
		   
		  	<div class="navbar"> 
		    	<div class="container" data-toggle="clingify">
	        	<a class="navbar-btn" data-toggle="jpanel-menu" data-target=".navbar-collapse"> <span class="bar"></span> <span class="bar"></span> <span class="bar"></span> <span class="bar"></span> </a> 
		        
	        	<?php 
	        		date_default_timezone_set('Asia/Manila');
	        	?>

		        <!--user menu-->
		        <div class="btn-group user-menu pull-right">
		        	<table>
		        		<tr>
		        			<td><span class="welcome_message">Welcome, <?php echo (isset($logged_in_full_name) ? $logged_in_full_name : ''); ?> 
		        				<strong>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</strong> <?php echo date('F d, Y'); ?></span></td>
		        			<!-- <td><a href="/login/logout" class="btn btn-primary signup">Logout</a></td> -->
		        			<td><div id="logout_btn_placeholder"></div></td>
		        		</tr>
		        	</table>
		        </div>
		        
		        <!--everything within this div is collapsed on mobile-->
		        <div class="navbar-collapse collapse"> 
		          
		          <!--main navigation-->
		          <ul class="nav navbar-nav">
		            <li class="home-link"><a href="/"><i class="fa fa-home"></i><span class="hidden">Home</span></a></li>
		            <li><a href="/" class="menu-item">Home</a></li>
		            <li><a href="/cpar/create" class="menu-item">Create CPAR</a></li>
		            <?php 
		            	if($is_admin) { ?>
	            			<li><a href="/user" class="menu-item">Manage Users</a></li>
		            <?php } ?>
		            <li><a href="/login/logout" class="menu-item">Logout</a></li>
		          </ul>
		        </div>
		        <!--/.navbar-collapse -->
		      </div>
		    </div>
		  </div>
		</div>
		<!-- breadcrumb -->
		<!--
<div class="container breadcrumb">
			<div>
				<a href="/">Home</a>&nbsp;&nbsp;&gt;&nbsp;&nbsp;<?php echo isset($screen_title) ? $screen_title : ''; ?>
			</div>
		</div>
-->
		<!--/. breadcrumb -->

		<form id="download_form" action="/file/get" method="POST">
			<input id="download_file_name" name="file_name" type="hidden" value="" />
		</form>
		
		<form id="ap_download_form" action="/file/action_plan" method="POST">
			<input name="file_name" type="hidden" value="" />
			<input name="cpar_no" type="hidden" value="" />
		</form>
		
		<form id="task_download_form" action="/file/task" method="POST">
			<input name="file_name" type="hidden" value="" />
			<input name="cpar_no" type="hidden" value="" />
			<input name="id" type="hidden" value="" />
		</form>

		<!-- END OF HEADER -->