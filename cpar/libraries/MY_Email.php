<?php if(!defined("BASEPATH")){ exit("No direct script access allowed"); }
	
#error_reporting(E_ERROR | E_NOTICE);
error_reporting(E_ALL);

class MY_Email extends CI_Email {

	public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function getEmailConfig() {
    	return Array(
		    'protocol' => 'smtp',
		    'smtp_host' => SMTP_HOST,
		    'smtp_port' => SMTP_PORT,
		    'smtp_user' => SMTP_USER,
		    'smtp_pass' => SMTP_PASS,
		    'mailtype'  => 'html', 
		    'charset'   => 'utf-8',
		    'starttls'  => true
		);
    }

    public function generateRecipients($arr) {
		$recipients = '';
		foreach ($arr as $item) {
			$recipients .= ', ' . $item['email_address'];
		}
		$recipients = $this->str_lreplace(', ', '', $recipients);

		return $recipients;
	}

    private function str_lreplace($search, $replace, $subject) {
	    $pos = strrpos($subject, $search);

	    if($pos !== false) {
	        $subject = substr_replace($subject, $replace, $pos, strlen($search));
	    }

	    return $subject;
	}
	
	public function processEmails($cpar_no, $stage, $sub_stage, $data = array()) {
		$CI =& get_instance();
		
		$CI->load->model('users_model');
		$CI->load->model('cpar_model');
		
		#get all admins
		$a_admins = $CI->users_model->getAdmins();
		$o_cpar = $CI->cpar_model->getCpar($cpar_no);
		$assigned_ims = NULL;
		
		if($o_cpar->assigned_ims) {
			$assigned_ims =  $CI->users_model->getUser($o_cpar->assigned_ims);
		}
		
		$from = 'no-reply@aboitiz.com';
		$from_name = 'CPAR Automated Mail';
		
		$data['cpar_link'] = 'http://'.$CI->config->base_url().'cpar/link/'.$cpar_no;
		
		#$cpar_no
		$data['cpar_no'] = $cpar_no;
		
		#$title
		$data['title'] = $o_cpar->title;
		
		#$ims_name
		if($assigned_ims) {
			$data['ims_name'] = $assigned_ims->first_name.' '.(($assigned_ims->middle_name && strlen($assigned_ims->middle_name) > 0) ? $assigned_ims->middle_name.' ' : '').$assigned_ims->last_name;
		} else {
			$data['ims_name'] = '';
		}
		
		#$requester_name
		$data['requester_name'] = $o_cpar->requestor_name;
		
		#$addressee_name
		$data['addressee_name'] = $o_cpar->addressee_name;
		
		#$tl_name
		$data['tl_name'] = $o_cpar->addressee_team_lead_name;
		
		#check stage
		switch($stage) {
			#if stage = 1
			case '1':
				switch($sub_stage) {
					#if For IMS Review
					case 'A':						
						$subject = 'New CPAR for IMS Review';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s1/for_ims_review/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
							
							$body = $CI->load->view('email_template/s1/for_ims_review/ims', $data, true);
														
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Requester
						$to = $o_cpar->requestor_email;
						$to_name = $o_cpar->requestor_name;
						
						$body = $CI->load->view('email_template/s1/for_ims_review/requester', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						break;
					#if For Stage 1 Pushed back
					case 'AX':
						$data['sent_by'] = 'ADMIN';
						#get who pushed back						
						$pb_user = $CI->users_model->getUser($o_cpar->pb_user);
						if($pb_user->ims_flag == IMS_FLAG && $pb_user->access_level != ACCESS_LEVEL_ADMIN_FLAG) {
							$data['sent_by'] = 'IMS';
						}
												
						$subject = 'CPAR Details for Correction';
						
						#$remarks
						$data['remarks'] = $o_cpar->pb_remarks;
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s1/pushed_back/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
							
							$body = $CI->load->view('email_template/s1/pushed_back/ims', $data, true);
														
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Requester
						$to = $o_cpar->requestor_email;
						$to_name = $o_cpar->requestor_name;
						
						$body = $CI->load->view('email_template/s1/pushed_back/requester', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						break;
					#if For IMS Review Re-assign
					case 'AA':
						$subject = 'CPAR# '.$cpar_no.' re-assigned for IMS Review';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s1/for_ims_review_reassign/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
							
							$body = $CI->load->view('email_template/s1/for_ims_review_reassign/ims', $data, true);
														
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Requester
						$to = $o_cpar->requestor_email;
						$to_name = $o_cpar->requestor_name;
						
						$body = $CI->load->view('email_template/s1/for_ims_review_reassign/requester', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						break;
					#if Invalid
					case 'B':
						#$ims_name
						$o_pb_user = null;
						if($o_cpar->pb_user) {
							$o_pb_user = $CI->users_model->getUser($o_cpar->pb_user);
							$data['ims_name'] = $o_pb_user->first_name.' '.(($o_pb_user->middle_name && strlen($o_pb_user->middle_name) > 0) ? $o_pb_user->middle_name.' ' : '').$o_pb_user->last_name;
						} else {
							$data['ims_name'] = '';
						}
						
						#$remarks
						$data['remarks'] = $o_cpar->pb_remarks;
												
						$subject = 'Filed CPAR is INVALID';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s1/invalid/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($o_cpar->pb_user && $o_pb_user) {
							$to = $o_pb_user->email_address;
							$to_name = $data['ims_name'];
							
							$body = $CI->load->view('email_template/s1/invalid/ims', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Requester
						$to = $o_cpar->requestor_email;
						$to_name = $o_cpar->requestor_name;
						
						$body = $CI->load->view('email_template/s1/invalid/requester', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
				}
				break;
			#if stage = 2
			case '2':

				switch($sub_stage) {
					#if For CA / PA
					case 'A':						
						$i_date = strtotime($o_cpar->date_due);
						#$month
						$data['month'] = date('F', $i_date);
						#$day
						$data['day'] = date('d', $i_date);
						#$year
						$data['year'] = date('Y', $i_date);
						
						$subject = 'CPAR# '.$cpar_no.' Issued to Addressee';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s2/for_ca_pa/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s2/for_ca_pa/ims', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to TL
						$to = $o_cpar->addressee_team_lead_email;
						$to_name = $o_cpar->addressee_team_lead_name;
						
						$body = $CI->load->view('email_template/s2/for_ca_pa/tl', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s2/for_ca_pa/addressee', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
					
					#if For TL Review
					case 'B':
						$subject = 'CPAR# '.$cpar_no.' for Team Leader Review';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s2/for_tl_review/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s2/for_tl_review/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to TL
						$to = $o_cpar->addressee_team_lead_email;
						$to_name = $o_cpar->addressee_team_lead_name;
						
						$body = $CI->load->view('email_template/s2/for_tl_review/tl', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s2/for_tl_review/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
						
					#if For TL Review - NOT APPROVED
					case 'BX':
						#pushed back by can be TL, ADMIN or IMS
					
						$data['sent_by'] = 'Team Leader';
						#get who pushed back						
						$pb_user = $CI->users_model->getUser($o_cpar->pb_user);
						if($o_cpar->pb_user != $o_cpar->addressee_team_lead) {
							
							$data['sent_by'] = 'ADMIN';
							
							if($pb_user->ims_flag == IMS_FLAG && $pb_user->access_level != ACCESS_LEVEL_ADMIN_FLAG) {
								$data['sent_by'] = 'IMS';
							}
						}
						
						$data['pushed_back_name'] = $pb_user->first_name.' '.(($pb_user->middle_name && strlen($pb_user->middle_name) > 0) ? $pb_user->middle_name.' ' : '').$pb_user->last_name;
															
						#$remarks
						$data['remarks'] = $o_cpar->pb_remarks;
						
						$subject = 'CPAR# '.$cpar_no.' Disapproved by '.$data['sent_by'];
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s2/for_tl_review_not_approved/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s2/for_tl_review_not_approved/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s2/for_tl_review_not_approved/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;						
				}
				break;
			#if stage = 3
			case '3':
				switch($sub_stage) {
					#if For IMS Review
					case 'A':
						$subject = 'CPAR# '.$cpar_no.' for IMS Review';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s3/for_ims_review/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s3/for_ims_review/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s3/for_ims_review/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
					#if For IMS Review Return To Addressee NEW
					case 'AX':						
						$i_date = strtotime($o_cpar->date_due);
						#$month
						$data['month'] = date('F', $i_date);
						#$day
						$data['day'] = date('d', $i_date);
						#$year
						$data['year'] = date('Y', $i_date);
						
						$subject = 'CPAR# '.$cpar_no.' returned to Addressee for corrections';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s3/for_ims_review_return_to_addressee/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s3/for_ims_review_return_to_addressee/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s3/for_ims_review_return_to_addressee/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						#send to TL
						$to = $o_cpar->addressee_team_lead_email;
						$to_name = $o_cpar->addressee_team_lead_name;
						
						$body = $CI->load->view('email_template/s3/for_ims_review_return_to_addressee/tl', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
						
					#if For AP Implementation
					case 'B':
						$i_date = strtotime($o_cpar->ff_up_date);
						#$month
						$data['month'] = date('F', $i_date);
						#$day
						$data['day'] = date('d', $i_date);
						#$year
						$data['year'] = date('Y', $i_date);
						
						#table
						$data['table'] = $this->generateActionPlanDetailsTable($cpar_no);
						
						$subject = 'CPAR# '.$cpar_no.' Follow-up Actions';
					
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s3/for_ap_implementation/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#also send to admin Another email notification for the details of the action plan per CPAR
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.$o_admin['last_name'];
							$body = $CI->load->view('email_template/s3/for_ap_implementation/admin_with_details', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s3/for_ap_implementation/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s3/for_ap_implementation/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
				}
				break;
			#if stage = 4
			case '4':
				switch($sub_stage) {
					#if For IMS Review
					case 'A':
						$i_date = strtotime($o_cpar->ff_up_date);
						#$month
						$data['month'] = date('F', $i_date);
						#$day
						$data['day'] = date('d', $i_date);
						#$year
						$data['year'] = date('Y', $i_date);

						$subject = 'CPAR# '.$cpar_no.' for Verification';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s4/for_ims_review/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s4/for_ims_review/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						break;
					case 'AX2':
						#ADMIN/IMS/TL
						
						$i_date = strtotime($o_cpar->date_due);
						#$month
						$data['month'] = date('F', $i_date);
						#$day
						$data['day'] = date('d', $i_date);
						#$year
						$data['year'] = date('Y', $i_date);

						#$remarks
						$data['remarks'] = $o_cpar->pb_remarks;
						
						$subject = 'CPAR# '.$cpar_no.' for Correction';
						
						#send to ADMIN
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_2/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_2/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to TL
						$to = $o_cpar->addressee_team_lead_email;
						$to_name = $o_cpar->addressee_team_lead_name;
						
						$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_2/tl', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_2/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
						
					case 'AX3':
						#ADMIN/IMS
					
						$i_date = strtotime($o_cpar->date_due);
						#$month
						$data['month'] = date('F', $i_date);
						#$day
						$data['day'] = date('d', $i_date);
						#$year
						$data['year'] = date('Y', $i_date);

						#$remarks
						$data['remarks'] = $o_cpar->pb_remarks;
						
						$subject = 'CPAR# '.$cpar_no.' for Correction';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_3/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_3/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s4/for_ims_review_pushed_back_stage_3/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
					
					#if For MR Review
					case 'B':
						#$mr_name
						$mr = $CI->users_model->getMRUserObject();
						if($mr) {
							$data['mr_name'] = $mr->first_name.' '.(($mr->middle_name && strlen($mr->middle_name) > 0) ? $mr->middle_name.' ' : '').$mr->last_name;
						}
						
						$subject = 'CPAR# '.$cpar_no.' Verified Effective';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s4/for_mr_review/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to MR
						if($mr) {
							$to = $mr->email_address;
							$to_name = $data['mr_name'];
						
							$body = $CI->load->view('email_template/s4/for_mr_review/mr', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s4/for_mr_review/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						break;
					case 'BX' :
						$subject = 'CPAR# '.$cpar_no.' for Further Verification';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s4/for_mr_review_not_closed_by_mr/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s4/for_mr_review_not_closed_by_mr/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						break;
				}
				break;
			#if stage = 5
			case '5':
				switch($sub_stage) {
					#if Closed
					case 'A':
						$mr = $CI->users_model->getMRUserObject();
						if($mr) {
							$data['mr_name'] = $mr->first_name.' '.(($mr->middle_name && strlen($mr->middle_name) > 0) ? $mr->middle_name.' ' : '').$mr->last_name;
						}

						$subject = 'CPAR# '.$cpar_no.' is Closed';
						
						#send to Admin
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.$o_admin['last_name'];
							
							$data['admin_name'] = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							$body = $CI->load->view('email_template/s5/closed/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to IMS
						if($assigned_ims) {
							$to = $assigned_ims->email_address;
							$to_name = $data['ims_name'];
						
							$body = $CI->load->view('email_template/s5/closed/ims', $data, true);
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
						
						#send to TL
						$to = $o_cpar->addressee_team_lead_email;
						$to_name = $o_cpar->addressee_team_lead_name;
						
						$body = $CI->load->view('email_template/s5/closed/tl', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						#send to Addressee
						$to = $o_cpar->addressee_email;
						$to_name = $o_cpar->addressee_name;
						
						$body = $CI->load->view('email_template/s5/closed/addressee', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						
						#send to Requester
						$to = $o_cpar->requestor_email;
						$to_name = $o_cpar->requestor_name;
						
						$body = $CI->load->view('email_template/s5/closed/requester', $data, true);
						
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						break;
				}
				break;
		}
			
	}
	
	private function sendActualMail($from, $from_name, $to, $to_name, $subject, $body) {
		$this->initialize($this->getEmailConfig());
		$this->set_newline("\r\n");

		$this->from($from, $from_name);
		$this->to($to, $to_name);

		$this->subject($subject);
		$this->message($body);

		$this->send();
		log_message('debug', $this->print_debugger());
	}
	
	public function sendReminder($cpar_no, $recipient_id) {
		$CI =& get_instance();
		
		$from = 'no-reply@aboitiz.com';
		$from_name = 'CPAR Automated Mail';
	
		$CI->load->model('users_model');
		
		$user = $CI->users_model->getUser($recipient_id);
		
		if($user && $user->status == 1) {
			$recipient_name = $user->first_name.' '.(($user->middle_name && strlen($user->middle_name) > 0) ? $user->middle_name.' ' : '').$user->last_name;
			$recipient_email = $user->email_address;
		
			$data = array();
			$data['recipient_name'] = $recipient_name;
			$data['cpar_link'] = 'http://'.$CI->config->base_url().'cpar/'.$cpar_no;
		
			$subject = 'REMINDER: CPAR# '.$cpar_no.' Requires Action';
			
			$body = $CI->load->view('email_template/reminder/general', $data, true);
			
			$to = $recipient_email;
			$to_name = $recipient_name;
			
			$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);	
		}
	}
	
	private function generateActionPlanDetailsTable($cpar_no) {
		$CI =& get_instance();
		
		$CI->load->model('addressee_fields_model');
	
		$a_action_plans = $CI->addressee_fields_model->getActionPlanDetails($cpar_no);
		
		#Create Header
		
		$table = '<table style="border: 1px solid black; border-collapse: collapse;vertical-align: middle;">';
		
		$table .= '<tr style="background: #DCDCDC; text-align:center; font-weight:bold;">';
		$table .= '
			<th style="border: 1px solid black; border-collapse: collapse; width:150px; padding:3px;">TASK</th>
			<th style="border: 1px solid black; border-collapse: collapse; width:150px; padding:3px;">RESPONSIBLE PERSON</th>
			<th style="border: 1px solid black; border-collapse: collapse; width:150px; padding:3px;">DUE DATE</th>
			<th style="border: 1px solid black; border-collapse: collapse; width:225px; padding:3px;">REMARKS (by Addressee)</th>';
		$table .= '</tr>';
		
		foreach($a_action_plans as $o_row) {
			$table .= '<tr>';
			$table .= '
				<td style="border: 1px solid black; border-collapse: collapse; padding:3px;">'.$o_row['task'].'</td>
				<td style="border: 1px solid black; border-collapse: collapse; padding:3px;">'.$o_row['responsible_person_name'].'</td>
				<td style="border: 1px solid black; border-collapse: collapse; padding:3px;">'.formatDateForDisplay($o_row['due_date']).'</td>
				<td style="border: 1px solid black; border-collapse: collapse; padding:3px;">'.$o_row['remarks_addr'].'</td>';
			$table .= '</tr>';
		}
		
		$table .= '</table>';
		
		return $table;
		
	}
	
	public function generateReminderEmail($cpar_no) {
		$CI =& get_instance();
		
		$CI->load->model('users_model');
		$CI->load->model('cpar_model');
		
		#get all admins
		$a_admins = $CI->users_model->getAdmins();
		$o_cpar = $CI->cpar_model->getCpar($cpar_no);
		$assigned_ims = NULL;
		
		if($o_cpar->assigned_ims) {
			$assigned_ims =  $CI->users_model->getUser($o_cpar->assigned_ims);
		}
		
		$from = 'no-reply@aboitiz.com';
		$from_name = 'CPAR Automated Mail';
		
		$data['cpar_link'] = 'http://'.$CI->config->base_url().'cpar/link/'.$cpar_no;
		
		#$cpar_no
		$data['cpar_no'] = $cpar_no;
		
		#$title
		$data['title'] = $o_cpar->title;
		
		#$ims_name
		if($assigned_ims) {
			$data['ims_name'] = $assigned_ims->first_name.' '.(($assigned_ims->middle_name && strlen($assigned_ims->middle_name) > 0) ? $assigned_ims->middle_name.' ' : '').$assigned_ims->last_name;
		} else {
			$data['ims_name'] = '';
		}
		
		#$requester_name
		$data['requester_name'] = $o_cpar->requestor_name;
		
		#$addressee_name
		$data['addressee_name'] = $o_cpar->addressee_name;
		
		#$tl_name
		$data['tl_name'] = $o_cpar->addressee_team_lead_name;
		
		if($o_cpar) {
			$sub_status = (int)$o_cpar->sub_status;
		
			switch($sub_status) {
				#1 - For IMS Review (IMS)
				case CPAR_MINI_STATUS_FOR_IMS_REVIEW :
					$subject = 'New CPAR for IMS Review';
				
					if($o_cpar->assigned_ims) {
						$to = $assigned_ims->email_address;
						$to_name = $data['ims_name'];
						
						$body = $CI->load->view('email_template/s1/for_ims_review/ims', $data, true);
													
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					} else {
						foreach($a_admins as $o_admin) {
							$to = $o_admin['email_address'];
							$to_name = $o_admin['first_name'].' '.(($o_admin['middle_name'] && strlen($o_admin['middle_name']) > 0) ? $o_admin['middle_name'].' ' : '').$o_admin['last_name'];
							
							$data['admin_name'] = $to_name;
							$body = $CI->load->view('email_template/s1/for_ims_review/admin', $data, true);
							
							$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
						}
					}
					break;
					
				#2 - For CA / PA (Addressee)
				case CPAR_MINI_STATUS_S2_2A1 :
				case CPAR_MINI_STATUS_S2_2A2 :
					$i_date = strtotime($o_cpar->date_due);
					#$month
					$data['month'] = date('F', $i_date);
					#$day
					$data['day'] = date('d', $i_date);
					#$year
					$data['year'] = date('Y', $i_date);
						
					$subject = 'CPAR# '.$cpar_no.' Issued to Addressee';
				
					$to = $o_cpar->addressee_email;
					$to_name = $o_cpar->addressee_name;
					
					$body = $CI->load->view('email_template/s2/for_ca_pa/addressee', $data, true);
					
					$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					break;
					
				#2 - For TL Review (TL)
				case CPAR_MINI_STATUS_S2_2B :
					$subject = 'CPAR# '.$cpar_no.' for Team Leader Review';
				
					$to = $o_cpar->addressee_team_lead_email;
					$to_name = $o_cpar->addressee_team_lead_name;
					
					$body = $CI->load->view('email_template/s2/for_tl_review/tl', $data, true);
					$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					break;
					
				#3 - For IMS Review	(IMS)
				case CPAR_MINI_STATUS_S3_3A :
					$subject = 'CPAR# '.$cpar_no.' for IMS Review';
					
					if($assigned_ims) {
						$to = $assigned_ims->email_address;
						$to_name = $data['ims_name'];
					
						$body = $CI->load->view('email_template/s3/for_ims_review/ims', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					}
					
					break;
					
				#3 - For AP Implementation	(Addressee and IMS in Parallel)	
				case CPAR_MINI_STATUS_S3_3B :
					$i_date = strtotime($o_cpar->ff_up_date);
					#$month
					$data['month'] = date('F', $i_date);
					#$day
					$data['day'] = date('d', $i_date);
					#$year
					$data['year'] = date('Y', $i_date);
					
					#table
					$data['table'] = $this->generateActionPlanDetailsTable($cpar_no);
					
					$subject = 'CPAR# '.$cpar_no.' Follow-up Actions';
									
					#send to IMS
					if($assigned_ims) {
						$to = $assigned_ims->email_address;
						$to_name = $data['ims_name'];
					
						$body = $CI->load->view('email_template/s3/for_ap_implementation/ims', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					}
					
					#send to Addressee
					$to = $o_cpar->addressee_email;
					$to_name = $o_cpar->addressee_name;
					
					$body = $CI->load->view('email_template/s3/for_ap_implementation/addressee', $data, true);
					$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					break;
					
				#4 - For IMS Review	(IMS)
				case CPAR_MINI_STATUS_S4_4A :
					$i_date = strtotime($o_cpar->ff_up_date);
					#$month
					$data['month'] = date('F', $i_date);
					#$day
					$data['day'] = date('d', $i_date);
					#$year
					$data['year'] = date('Y', $i_date);

					$subject = 'CPAR# '.$cpar_no.' for Verification';
					
					#send to IMS
					if($assigned_ims) {
						$to = $assigned_ims->email_address;
						$to_name = $data['ims_name'];
					
						$body = $CI->load->view('email_template/s4/for_ims_review/ims', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					}
					break;
					
				#4 - For MR Review	(MR)
				case CPAR_MINI_STATUS_S4_4B :
					#$mr_name
					$mr = $CI->users_model->getMRUserObject();
					if($mr) {
						$data['mr_name'] = $mr->first_name.' '.(($mr->middle_name && strlen($mr->middle_name) > 0) ? $mr->middle_name.' ' : '').$mr->last_name;
					}
					
					$subject = 'CPAR# '.$cpar_no.' Verified Effective';
										
					#send to MR
					if($mr) {
						$to = $mr->email_address;
						$to_name = $data['mr_name'];
					
						$body = $CI->load->view('email_template/s4/for_mr_review/mr', $data, true);
						$this->sendActualMail($from, $from_name, $to, $to_name, $subject, $body);
					}
					break;
			}
		}
	}
}