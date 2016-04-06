<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Aboitiz CI | Logout</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!-- Bootstrap CSS -->
		<link href="/_theme/css/bootstrap.min.css" rel="stylesheet">
		
		<!-- Theme style -->
		<link href="/_theme/css/theme-style.css" rel="stylesheet">
		<link href="/_theme/css/colour-aboitiz-red.css" id="colour-scheme" rel="stylesheet">

		<!-- Font Awesome -->
		<link href="/_theme/css/font-awesome.min.css" rel="stylesheet">
		<!-- <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300|Rambla|Calligraffitti' rel='stylesheet' type='text/css'> -->
		
		<!-- Login CSS -->
		<link href="/_css/login.css" rel="stylesheet">

		<script src="/_theme/js/jquery.min.js"></script>
		<script src="/_js/common.js"></script>
		<link rel="shortcut icon" type="image/png" href="/_images/favicon.png" />
	</head>
	<body>
		<div id="page_wrapper">
			<div id="content">
				<div class="container">

					<div class="header">
			      <div class="header-inner container">
			        <div class="row">
			          <div id="aboitiz_logo_col" class="col-md-12">
			          	<div id="aboitiz_logo_container">
	          			  <img src="/_images/aboitiz_red_logo.png" alt="Aboitiz Logo" ></img>
			          	</div>
			          </div>
			        </div>
			      </div>
			    </div>

					<div class="row">
					<?php if(!empty($errors)) { ?>			
				    	<div class="col-md-12">
							<div class="error_container alert alert-danger">
								<button type="button" class="close custom_alert_hide">
									<span aria-hidden="true">&times;</span>
									<span class="sr-only">Close</span>
								</button>
								<div class="error_content">
									<?php 
										if(!empty($errors)) {
											foreach ($errors as $error) {
												echo "<i class='glyphicon glyphicon-exclamation-sign'></i>&nbsp;$error<br/>";
											}
										}
									?>
								</div>
							</div>
						</div>
					<?php } ?>
					</div>

					<div id="login_mid_body" class="row">
						<div id="login_background_container" class="col-md-6">
							<img src="/_images/login_background.jpg" alt="Aboitiz Login Background"></img>
						</div>
						<div id="login_body" class="col-md-6">
							<form id="login_form" class="form-login form-wrapper form-narrow">

								<span id="app_name">CPAR Database</span>
								<br/>
								<span id="app_full_name">
									<span class="uline">C</span>orrective / 
									<span class="uline">P</span>reventive 
									<span class="uline">A</span>ction 
									<span class="uline">R</span>equest
								</span>
								<br/>
								<br/>

								<h1>Thank you! <br/>You have successfully logged out. <br/><br/>If you wish to log back in, please click on the button below.</h1>
								<br/>
				      	<a id="login_btn" href="<?php echo $googleLoginUrl; ?>" class="btn btn-primary">
									Sign in with Google&nbsp;
									<i class="glyphicon glyphicon-log-in" style="font-size: 24px;"></i>
									<!-- <div class="login_btn">
										<img src="/_images/google_sign_in_button.jpg" alt="Sign in with Google"/>
									</div> -->
								</a>
					    </form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">				
			<div class="col-md-12">
				<div id="login_footer">	
					&copy; 2014 Aboitiz Equity Ventures. All rights reserved.
				</div>
			</div>
		</div>
			
		<!--Scripts --> 
		<script src="/_theme/js/jquery.min.js"></script> 
	</body>
</html>