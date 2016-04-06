<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Stages 
{
	var $CI;
	var $success_msg = NULL;

	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->library('session');
		$this->CI->load->library('upload');
		$this->CI->load->library('email');

		$this->CI->load->model('users_model');
		$this->CI->load->model('cpar_model');
		$this->CI->load->model('review_history_model');
		$this->CI->load->model('addressee_fields_model');
		$this->CI->load->model('ff_up_history_model');
		$this->CI->load->model('audit_log_model');

		$this->CI->load->helper('cpar_helper');
		$this->CI->load->helper('cpar_file_helper');
	}
	
	#=================================
	#STAGE3
	#=================================
	
	public function validate_stage3() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->CI->input->post()) {
			$form = $this->CI->input->post();
			
			$cpar_no = $form['id'];
			$cpar = $this->CI->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validate_permissions_stage3($cpar);
	
			$b_is_push_back = FALSE;
			$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
	
			if(!empty($review_action) && (int)$review_action == REVIEW_ACTIONS_S3_PUSH_BACK) {
				$b_is_push_back = TRUE;
			}
	
	
			if(empty($errors)) {
				$errors_ims_review = $this->validate_ims_review($form);
				
				$errors_information_fields = array();
				$errors_car_fields = array();
				$errors_common_fields = array();
				
				if(!$b_is_push_back) {
					$errors_information_fields = $this->validate_information_fields($form);
				
					if(intval($cpar->type) == CPAR_TYPE_C) {
						$errors_car_fields = $this->validate_car_fields($form);
					}
					
					$errors_common_fields = $this->validate_form_common_fields($form, $cpar->type);
					
				}	
				
				$errors = array_merge($errors_information_fields, $errors_car_fields, $errors_common_fields, $errors_ims_review);		
			}
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}
		
		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			if(!$b_is_push_back) {
				if(!$this->process_uploads($cpar_no)) {
					$result->success = false;
					$result->errors = array();
					array_push($result->errors, $this->upload->display_errors('',''));
				} else {
					$information_form_results = $this->process_information_form($form);
					$main_form_results = $this->process_main_form($form);
					$result = $this->process_review_form_stage3($form);
				}
			} else {
				$result = $this->process_review_form_stage3($form);
			}
		}

		if($result != null && $result->success) {
			$result->success = true;
			if($this->success_msg) {
				$result->success_msg = $this->success_msg;
			}
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		return json_encode($result);
	}
	
	
	
	private function validate_ims_review($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
		if(empty($review_action)) {
			array_push($errors, 'Please choose a review action.');
		} else if((int)$review_action == REVIEW_ACTIONS_S3_MARK_REV) {
			$ff_up_date = isset($form["ff_up_date"]) ? $form["ff_up_date"] : '';
			$ff_up_date = trim($ff_up_date);
			if(empty($ff_up_date)) {
				array_push($errors, "Follow Up Date" . $is_required_suffix);
			}
		} else if((int)$review_action == REVIEW_ACTIONS_S3_PUSH_BACK) {
			$pb_stage = isset($form["pb_stage"]) ? $form["pb_stage"] : '';
			$pb_stage = trim($pb_stage);
			if(empty($pb_stage)) {
				array_push($errors, "Push Back Stage" . $is_required_suffix);
			}

			$next_due_date = isset($form["next_due_date"]) ? $form["next_due_date"] : '';
			$next_due_date = trim($next_due_date);
			if(empty($next_due_date)) {
				array_push($errors, "Next Due Date" . $is_required_suffix);
			}
		}

		$remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
		if(empty($remarks)) {
			if(empty($review_action) || (int)$review_action == REVIEW_ACTIONS_S3_PUSH_BACK) {
				array_push($errors, "Remarks" . $is_required_suffix);
			}
		} else {
			if(strlen($remarks) < MIN_CPAR_REMARKS || strlen($remarks) > MAX_CPAR_REMARKS) {
				array_push($errors, "Remarks (IMS Review) should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
			}
		}

		return $errors;
	}
	
	private function process_review_form_stage3($form) {
		$l_id = $this->CI->session->userdata('loggedIn');
		$access = $this->CI->users_model->getUserAccessRights($l_id);
		
		$result = new stdClass();
		
		$cpar = new stdClass();
		$cpar_no = $form['id'];
		$addressee_fields = new stdClass();
		$old_cpar = $this->CI->cpar_model->getCpar($cpar_no);

		$status = '';
		$sub_status = '';

		$b_send_emails = FALSE;
		$mail_stage = NULL;
		$mail_sub_stage = NULL;

		$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
		if((int)$review_action == REVIEW_ACTIONS_S3_MARK_REV) {
			$status = CPAR_STAGE_3;
			$sub_status = CPAR_MINI_STATUS_S3_3B;

			//update ff_up_date
			$ff_up_date = isset($form["ff_up_date"]) ? $form["ff_up_date"] : '';
			$remarks = (isset($form["remarks"]) ? fix_output2($form["remarks"]) : '');
			$cpar->ff_up_date = formatDateForDB($ff_up_date);
			
			$b_send_emails = TRUE;
			$mail_stage = '3';
			$mail_sub_stage = 'B';
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S3_MARK_REV;
			
		} else if((int)$review_action == REVIEW_ACTIONS_S3_MARK_IMPL) {
			$status = CPAR_STAGE_4;
			$sub_status = CPAR_MINI_STATUS_S4_4A;

			$ev_ff_up_date = isset($form["ev_ff_up_date"]) ? $form["ev_ff_up_date"] : '';
			$remarks = (isset($form["remarks"]) ? fix_output2($form["remarks"]) : '');

			//insert follow up history			
			$this->create_ff_up_history($cpar_no, $ev_ff_up_date, FF_UP_RESULT_IMPL, $remarks);

			//upload attachments
			$insert_id = $this->CI->db->insert_id();
			$this->upload_files_stage3($cpar_no, $review_action, $insert_id);
			$b_send_emails = TRUE;
			$mail_stage = '4';
			$mail_sub_stage = 'A';
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S3_MARK_IMPL;
			
		} else if((int)$review_action == REVIEW_ACTIONS_S3_MARK_FF_UP) {
			$status = CPAR_STAGE_3;
			$sub_status = CPAR_MINI_STATUS_S3_3B;

			//update next_ff_up_date
			$next_ff_up_date = isset($form["next_ff_up_date"]) ? $form["next_ff_up_date"] : '';
			$remarks = (isset($form["remarks"]) ? fix_output2($form["remarks"]) : '');
			$cpar->ff_up_date = formatDateForDB($next_ff_up_date);

			//insert follow up history
			$this->create_ff_up_history($cpar_no, $next_ff_up_date, FF_UP_RESULT_FOR_FF, $remarks);

			//upload attachments
			$insert_id = $this->CI->db->insert_id();
			$this->upload_files_stage3($cpar_no, $review_action, $insert_id);
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S3_MARK_FF_UP;
			
		} else if((int)$review_action == REVIEW_ACTIONS_S3_PUSH_BACK) {
			$status = CPAR_STAGE_2;
			$sub_status = CPAR_MINI_STATUS_S2_2A2;

			$cpar->pb_user = $l_id;
			$cpar->pb_date = date('Y-m-d H:i:s');
			$cpar->pb_remarks = isset($form["remarks"]) ? $form["remarks"] : '';

			//update due date
			$cpar->date_due = formatDateForDB(isset($form["next_due_date"]) ? $form["next_due_date"] : '');

			$addressee_fields->cpar_no = $cpar_no;
			$addressee_fields->accomplish_by = formatDateForDB(isset($form["next_due_date"]) ? $form["next_due_date"] : '');
			$result = $this->CI->addressee_fields_model->updateAddresseeFields($addressee_fields);
			
			$b_send_emails = TRUE;
			$mail_stage = '3';
			$mail_sub_stage = 'AX';
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S3_PUSH_BACK;
		}

		$cpar->status = $status;
		$cpar->sub_status = $sub_status;
		$cpar->updated_by = $l_id;
		$cpar->updated_date = date('Y-m-d H:i:s');

		$result = $this->CI->cpar_model->updateCpar($cpar, $cpar_no);
		
		if(isset($form['ap_upload_uid']) && $form['id']) {
			transferActionPlanAttachments($form['ap_upload_uid'], $form['id'], isset($form['ap_attachments']) ? $form['ap_attachments'] : array());
		}
		
		if($result && $b_send_emails && $mail_stage && $mail_sub_stage) {
			$this->CI->email->processEmails($cpar_no, $mail_stage, $mail_sub_stage);
		}

		if(!($review_action == null && empty($review_action))) {
			//derive role
			$role = '';
			if((int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
				$role = REVIEW_ROLE_ADMIN;
			} else if($l_id == $old_cpar->assigned_ims) {
				$role = REVIEW_ROLE_IMS;
			}

			$rev_hist = new stdClass();
			$rev_hist->cpar_no = $cpar_no;
			$rev_hist->action = $review_action;
			$rev_hist->role = $role;
			$rev_hist->stage = $old_cpar->status;
			$rev_hist->sub_status = $old_cpar->sub_status;
			$rev_hist->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
			$rev_hist->reviewed_by = $l_id;

			$rev_hist->reviewed_date = date('Y-m-d H:i:s');
			$rev_hist->due_date = formatDateForDB($old_cpar->date_due);
			if((int)$review_action != REVIEW_ACTIONS_S3_MARK_FF_UP) {
				$this->CI->review_history_model->insertReviewHistory($rev_hist);
			}
			
			#LOG HERE
			$log = new stdClass();
			$log->cpar_no = $cpar_no;
			$log->action = $this->CI->audit_log_model->get_action($review_action);
			$log->stage = CPAR_STAGE_3;
			$log->sub_status = $old_cpar->sub_status;
			$log->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
			$log->notes = json_encode($form);
			$log->created_by = $l_id;
			$log->created_date = date('Y-m-d H:i:s');
			$this->CI->audit_log_model->insertLog($log);
		}

		return $result;
	}
	
	private function process_main_form($form) {
		$result = new stdClass();
		$logged_in_id = $this->CI->session->userdata('loggedIn');
		$existing = $this->CI->addressee_fields_model->isExisting($form['id']);

		$addressee_fields = $this->convert_main_form($form);
		$addressee_fields->updated_by = $logged_in_id;
		$addressee_fields->updated_date = date('Y-m-d H:i:s');

		if($existing = null || empty($existing)) {
			//insert new addressee_fields record
			$result = $this->CI->addressee_fields_model->insertAddresseeFields($addressee_fields);
		} else {
			$result = $this->CI->addressee_fields_model->updateAddresseeFields($addressee_fields);
		}
		
		//batch insert action plan details
		$tasks = finalizeTasks($form);
		$task_attachments = extractAttachments($form);
		if($tasks) {
			$return = $this->CI->addressee_fields_model->batchInsertActionPlanDetails($tasks);
			
			if($return->success == true) {
				$inserted_ids = $return->ids;
				for($i = 0; $i < count($inserted_ids); $i++) {
					if($task_attachments[$i]) {
						transferTaskAttachments($inserted_ids[$i], $task_attachments[$i]['attachments'], $form['id']);
					}
				}
			}
		}

		//delete tasks
		$tasks_to_delete = json_decode($form["tasks_to_delete"]);
		if($tasks_to_delete) {
			$this->CI->addressee_fields_model->deleteActionPlanDetailsByIds($tasks_to_delete);
			foreach($tasks_to_delete as $delete_task) {
				removeTaskAttachments($delete_task, $form['id']);
			}
		}
		
		$this->CI->addressee_fields_model->update_proposed_by_date($addressee_fields->cpar_no);

		return $result;
	}
	
	private function convert_main_form($form) {
		$addr_fields = new stdClass();

		$this->trim_form($form);

		//main fields
		$addr_fields->cpar_no = isset($form['id']) ? $form['id'] : '';

		//remedial action details
		$addr_fields->rad_action = isset($form['immediate_remedial_action']) ? $form['immediate_remedial_action'] : '';		
		$addr_fields->rad_implemented_by = normalizeSelect2Value(isset($form['implemented_by']) ? $form['implemented_by'] : NULL);
		$addr_fields->rad_implemented_date = isset($form['date_implemented']) ? formatDateForDB($form['date_implemented']) : NULL;

		//root cause analysis
		$has_others = false;
		$tools_used = isset($form["tools_used"]) ? $form["tools_used"] : '';
		if(!empty($tools_used)) {
			$addr_fields->rca_tools = implode(', ', $tools_used);
			foreach ($tools_used as $tool) {
				if(intval($tool) == TOOLS_USED_OTHERS_ID) {
					$addr_fields->rca_tools_others = isset($form["other_tools_used"]) ? $form["other_tools_used"] : '';
					$has_others = true;
				}
			}
		} else {
			$addr_fields->rca_tools = '';
			$addr_fields->rca_tools_others = '';
		}
		if(!$has_others) {
			$addr_fields->rca_tools_others = '';
		}
		
		$addr_fields->rca_details = isset($form['rca_details']) ? $form['rca_details'] : NULL;

		$addr_fields->rca_investigated_by = (isset($form['investigated_by']) && $form['investigated_by'] != '') ? normalizeSelect2Value($form['investigated_by']) : NULL;
		$addr_fields->rca_investigated_date_started = (isset($form['date_investigation_started']) && $form['date_investigation_started'] != '') ? formatDateForDB($form['date_investigation_started']) : NULL;
		$addr_fields->rca_investigated_date_ended = (isset($form['date_investigation_ended']) && $form['date_investigation_ended'] != '') ? formatDateForDB($form['date_investigation_ended']) : NULL;

		//corrective/preventive action details
		$addr_fields->action = isset($form['corr_prev_action']) ? $form['corr_prev_action'] : '';
		$addr_fields->proposed_by = normalizeSelect2Value(isset($form['corr_prev_proposed_by']) ? $form['corr_prev_proposed_by'] : '');
		$addr_fields->target_start_date = (isset($form['corr_prev_target_start_date']) && $form['corr_prev_target_start_date'] != '') ? formatDateForDB($form['corr_prev_target_start_date']) : NULL;
		$addr_fields->target_end_date = (isset($form['corr_prev_target_end_date']) && $form['corr_prev_target_end_date'] != '') ? formatDateForDB($form['corr_prev_target_end_date']) : NULL;

		return $addr_fields;
	}
	
	private function process_information_form($form) {
		$result = new stdClass();

		$logged_in_id = $this->CI->session->userdata('loggedIn');

		$cpar = $this->convert_information_form($form);

		$old_id = $cpar->id;

		$cpar->updated_by = $logged_in_id;
		$cpar->updated_date = date('Y-m-d H:i:s');

		$result = $this->CI->cpar_model->updateCpar($cpar, $old_id);
			

		return $result;
	}
	
	private function convert_information_form($form) {
		$cpar = new stdClass();

		$cpar->id = $form['id'];
		$cpar->title = $form['title'];

		$cpar->raised_as_a_result_of = $form['result_of'];
		$cpar->raised_as_a_result_of_others = (isset($form['result_of_others']) && !empty($form['result_of_others'])) ? $form['result_of_others'] : NULL;
		$cpar->process = $form['process'];
		$cpar->details = $form['details'];
		$cpar->justification = $form['justification'];
		$cpar->references = $form['references'];

		if(intval($cpar->raised_as_a_result_of) != RAISED_AS_A_RESULT_OF_OTHERS_ID) {
			$cpar->raised_as_a_result_of_others = NULL;
		}

		//Fix when user saves a record without editing the addressee
		//in this case, team_lead value will look something like [{"id":"0000","text":"Full Name"}]
		//so we must extract the id from such json value
		$cpar->addressee = $form['at_name'];
		if(!ctype_digit($cpar->addressee)) {
			$addressee_obj = json_decode($cpar->addressee);
			if($addressee_obj != null) {
				$cpar->addressee = $addressee_obj[0]->id;
			}
		}

		$cpar->addressee_team = $form['at_team'];

		$cpar->addressee_team_lead = $form['at_team_lead'];
		if(!ctype_digit($cpar->addressee_team_lead)) {
			$addressee_obj = json_decode($cpar->addressee_team_lead);
			if($addressee_obj != null) {
				$cpar->addressee_team_lead = $addressee_obj[0]->id;
			}
		}

		$cpar->requestor = $form['req_name'];
		if(!ctype_digit($cpar->requestor)) {
			$requestor_obj = json_decode($cpar->requestor);
			if($requestor_obj != null) {
				$cpar->requestor = $requestor_obj[0]->id;
			}
		}

		$cpar->requestor_team = $form['req_team'];

		$cpar->requestor_team_lead = $form['req_team_lead'];
		if(!ctype_digit($cpar->requestor_team_lead)) {
			$requestor_obj = json_decode($cpar->requestor_team_lead);
			if($requestor_obj != null) {
				$cpar->requestor_team_lead = $requestor_obj[0]->id;
			}
		}

		return $cpar;
	}


	private function trim_form(&$form) {
		foreach ($form as $key => $val) {
			if(is_string($val)) {
				$form[$key] = trim($val);
			}
		}
	}
	
	private function process_uploads($cpar_no) {
		//removed uploads if not empty
	    $removed_rad_uploads = $this->CI->input->post('removed_rad_uploads');
	    $removed_rca_uploads = $this->CI->input->post('removed_rca_uploads');

	    if($removed_rad_uploads && !empty($removed_rad_uploads)) {
	    	$removed_rad_uploads = json_decode($removed_rad_uploads);
	    }

	    if($removed_rca_uploads && !empty($removed_rca_uploads)) {
	    	$removed_rca_uploads = json_decode($removed_rca_uploads);
	    }

	    $this->remove_uploaded_files($cpar_no, UPLOAD_2RAD_PREFIX, $removed_rad_uploads);
	    $this->remove_uploaded_files($cpar_no, UPLOAD_2RCA_PREFIX, $removed_rca_uploads);

		if(!empty($_FILES)) {
			//check if upload folder exists
	   		if (!is_dir(UPLOAD_PATH)) {
			    mkdir(UPLOAD_PATH);
			}

			$rad_upload = true;
			$rca_upload = true;

			if(isset($_FILES['rad_attachments']) && !empty($_FILES['rad_attachments'])) {
				$this->CI->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RAD_PREFIX, 'rad_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $rad_upload = $this->CI->upload->do_multi_upload('rad_attachments');
			}

		    if($rad_upload) {
		    	if(isset($_FILES['rca_attachments']) && !empty($_FILES['rca_attachments'])) {
					$this->CI->upload->initialize(array(
						'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RCA_PREFIX, 'rca_attachments', $_FILES),
				        'upload_path'   => UPLOAD_PATH,
				        'allowed_types' => UPLOAD_ALLOWED_TYPES
				    ));
				    $rca_upload = $this->CI->upload->do_multi_upload('rca_attachments');
				}

		    	if($rca_upload) {
		    		return TRUE;
		    	} else {
		    		return FALSE;
		    	}
		    } else {
		    	return FALSE;
		    }
		} else {
			return TRUE;
		}
	}
	
	private function remove_uploaded_files($cpar_no, $stage, $removed_uploads) {
		if($removed_uploads) {
			foreach ($removed_uploads as $file) {
			    $final_name = UPLOAD_PATH . $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR . $file;
			    if(file_exists($final_name)) {
			        unlink($final_name);
			    }
			}
		}
	}

	
	private function create_ff_up_history($cpar_no, $ff_up_date, $ff_result, $remarks) {
		$ff_up_history = new stdClass();
		$ff_up_history->cpar_no = $cpar_no;
		$ff_up_history->ff_date = date('Y-m-d');
		$ff_up_history->next_ff_date = formatDateForDB($ff_up_date);
		$ff_up_history->ff_result = $ff_result;
		$ff_up_history->remarks = $remarks;

		//count follow up items
		$pending = 0;
		$ongoing = 0;
		$done = 0;
		$overdue = 0;

		$apd = $this->CI->addressee_fields_model->getActionPlanDetails($cpar_no);
		foreach ($apd as $plan) {
			//count state
			if((int)$plan['status'] == APD_STATUS_PENDING) {
				$pending++;
			} else if((int)$plan['status'] == APD_STATUS_ONGOING) {
				$ongoing++;
			} else if((int)$plan['status'] == APD_STATUS_DONE || (int)$plan['status'] == APD_STATUS_VERIFIED) {
				$done++;
			} else if((int)$plan['status'] == APD_STATUS_OVERDUE) {
				$overdue++;
			}
		}

		$ff_up_history->no_of_tasks = count($apd);
		$ff_up_history->pending_tasks = $pending;
		$ff_up_history->ongoing_tasks = $ongoing;
		$ff_up_history->completed_tasks = $done;
		$ff_up_history->overdue_tasks = $overdue;

		$this->CI->ff_up_history_model->insertFfUpHistory($ff_up_history);
	}
	
	private function upload_files_stage3($cpar_no, $review_action, $ff_up_id) {
		$success = true;

		if(!empty($_FILES)) {
			//check if upload folder exists
	   		if (!is_dir(UPLOAD_PATH)) {
			    mkdir(UPLOAD_PATH);
			}

			$s3impl_upload = true;
			$s3ffup_upload = true;

			if((int)$review_action == REVIEW_ACTIONS_S3_MARK_IMPL && 
				isset($_FILES['s3impl_attachments']) && !empty($_FILES['s3impl_attachments'])) {
				$this->CI->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no . '_' . $ff_up_id, UPLOAD_3FFUP_PREFIX, 's3impl_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $s3impl_upload = $this->CI->upload->do_multi_upload('s3impl_attachments');
			}

			if((int)$review_action == REVIEW_ACTIONS_S3_MARK_FF_UP && 
				isset($_FILES['s3ffup_attachments']) && !empty($_FILES['s3ffup_attachments'])) {
				$this->CI->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no . '_' . $ff_up_id, UPLOAD_3FFUP_PREFIX, 's3ffup_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $s3ffup_upload = $this->CI->upload->do_multi_upload('s3ffup_attachments');
			}

	    	if(!$s3impl_upload || !$s3ffup_upload) {
	    		$success = false;
	    		$result->success = false;
				$result->errors = array();
				array_push($result->errors, $this->upload->display_errors('',''));
	    	}
		}

		return $success;
	}


	
	#validate information fields
	private function validate_information_fields($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		#form values
		$id = isset($form["id"]) ? $form["id"] : '';
		$title = isset($form["title"]) ? $form["title"] : '';
		$type = isset($form["type"]) ? $form["type"] : '';
		$result_of = isset($form["result_of"]) ? $form["result_of"] : '';
		$result_of_others = isset($form["result_of_others"]) ? $form["result_of_others"] : '';
		$process = isset($form["process"]) ? $form["process"] : '';
		$details = isset($form["details"]) ? $form["details"] : '';
		$justification = isset($form["justification"]) ? $form["justification"] : '';
		$references = isset($form["references"]) ? $form["references"] : '';

		$at_name = isset($form["at_name"]) ? $form["at_name"] : '';
		$at_team = isset($form["at_team"]) ? $form["at_team"] : '';
		$at_team_lead = isset($form["at_team_lead"]) ? $form["at_team_lead"] : '';

		$req_name = isset($form["req_name"]) ? $form["req_name"] : '';
		$req_team = isset($form["req_team"]) ? $form["req_team"] : '';
		$req_team_lead = isset($form["req_team_lead"]) ? $form["req_team_lead"] : '';

		$title = trim($title);
		if(empty($title)) {
			array_push($errors, "Title" . $is_required_suffix);
		} else {
			if(strlen($title) < MIN_CPAR_TITLE || strlen($title) > MAX_CPAR_TITLE) {
				array_push($errors, "Title should be " . MIN_CPAR_TITLE . " to " . MAX_CPAR_TITLE . " characters.");
			}
		}

		//validate type if it's a new record
		if($id == null || empty($id)) {
			if(empty($type)) {
				array_push($errors, "Type" . $is_required_suffix);
			} else if(!(intval($type) == CPAR_TYPE_C || intval($type) == CPAR_TYPE_P)) {
				array_push($errors, "Invalid Type value.");
			}
		}

		if(empty($result_of)) {
			array_push($errors, "Raised as a result of" . $is_required_suffix);
		}
		
		if(intval($result_of) == RAISED_AS_A_RESULT_OF_OTHERS_ID) {
			$result_of_others = trim($result_of_others);
			if(empty($result_of_others)) {
				array_push($errors, "Raised as a result of (Others)" . $is_required_suffix);
			} else {
				if(strlen($result_of_others) < MIN_CPAR_OTHERS || strlen($result_of_others) > MAX_CPAR_OTHERS) {
					array_push($errors, "Raised as a result of (Others) should be " . MIN_CPAR_OTHERS . " to " . MAX_CPAR_OTHERS . " characters.");
				}
			}
		}
		
		if(empty($process)) {
			array_push($errors, "Process" . $is_required_suffix);
		}

		$details = trim($details);
		if(!($details == null || empty($details))) {
			if(strlen($details) < MIN_CPAR_DETAILS || strlen($details) > MAX_CPAR_DETAILS) {
				array_push($errors, "Details should be " . MIN_CPAR_DETAILS . " to " . MAX_CPAR_DETAILS . " characters.");
			}
		}

		$justification = trim($justification);
		if(!($justification == null || empty($justification))) {
			if(strlen($justification) < MIN_CPAR_JUSTIFICATION || strlen($justification) > MAX_CPAR_JUSTIFICATION) {
				array_push($errors, "Justification should be " . MIN_CPAR_JUSTIFICATION . " to " . MAX_CPAR_JUSTIFICATION . " characters.");
			}
		}

		$references = trim($references);
		if(!($references == null || empty($references))) {
			if(strlen($references) < MIN_CPAR_REFERENCES || strlen($references) > MAX_CPAR_REFERENCES) {
				array_push($errors, "References should be " . MIN_CPAR_REFERENCES . " to " . MAX_CPAR_REFERENCES . " characters.");
			}
		}

		if(empty($at_name)) {
			array_push($errors, "Addressee name" . $is_required_suffix);
		}

		if(empty($at_team)) {
			array_push($errors, "Addressee team" . $is_required_suffix);
		}

		if(empty($at_team_lead)) {
			array_push($errors, "Addressee team lead" . $is_required_suffix);
		}

		if(empty($req_name)) {
			array_push($errors, "Requestor name" . $is_required_suffix);
		}

		if(empty($req_team)) {
			array_push($errors, "Requestor team" . $is_required_suffix);
		}

		if(empty($req_team_lead)) {
			array_push($errors, "Requestor team lead" . $is_required_suffix);
		}

		//if edit, check if record is not yet deleted
		if(!($id == null || empty($id)) && !$this->CI->cpar_model->isNotDeleted($id)) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		}
		
		return $errors;
	}
	
	#validate CAR fields
	private function validate_car_fields($form) {
		$errors = array();
		$is_required_suffix = " is required.";
	
		//Remedial Action Details
		$immediate_remedial_action = isset($form["immediate_remedial_action"]) ? $form["immediate_remedial_action"] : '';
		$implemented_by = isset($form["implemented_by"]) ? $form["implemented_by"] : '';
		$date_implemented = isset($form["date_implemented"]) ? $form["date_implemented"] : '';

		$immediate_remedial_action = trim($immediate_remedial_action);
		if(empty($immediate_remedial_action)) {
			array_push($errors, "Immediate remedial action" . $is_required_suffix);
		} else {
			if(strlen($immediate_remedial_action) < MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION || strlen($immediate_remedial_action) > MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION) {
				array_push($errors, "Immediate remedial action should be " . MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION . " to " . MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION . " characters.");
			}
		}

		$implemented_by = trim($implemented_by);
		if(empty($implemented_by)) {
			array_push($errors, "Implemented by" . $is_required_suffix);
		}

		$date_implemented = trim($date_implemented);
		if(empty($date_implemented)) {
			array_push($errors, "Date implemented" . $is_required_suffix);
		}

		//Root Cause Analysis
		$tools_used = isset($form["tools_used"]) ? $form["tools_used"] : array();
		foreach ($tools_used as $tool) {
			if(intval($tool) == TOOLS_USED_OTHERS_ID) {
				$other_tools_used = isset($form["other_tools_used"]) ? $form["other_tools_used"] : '';

				$other_tools_used = trim($other_tools_used);
				if(empty($other_tools_used)) {
					array_push($errors, "Others (textbox)" . $is_required_suffix);
				} else {
					if(strlen($other_tools_used) < MIN_CPAR_OTHERS || strlen($other_tools_used) > MAX_CPAR_OTHERS) {
						array_push($errors, "Others (textbox) should be " . MIN_CPAR_OTHERS . " to " . MAX_CPAR_OTHERS . " characters.");
					}
				}
			}
		}

		$details = isset($form["rca_details"]) ? $form["rca_details"] : '';

		$details = trim($details);
		if(empty($details)) {
			array_push($errors, "Details / Result of Root Cause Analysis" . $is_required_suffix);
		}

		$investigated_by = isset($form["investigated_by"]) ? $form["investigated_by"] : '';
		$date_investigation_started = isset($form["date_investigation_started"]) ? $form["date_investigation_started"] : '';
		$date_investigation_ended = isset($form["date_investigation_ended"]) ? $form["date_investigation_ended"] : '';

		$investigated_by = trim($investigated_by);
		if(empty($investigated_by)) {
			array_push($errors, "Investigated by" . $is_required_suffix);
		}

		$date_investigation_started = trim($date_investigation_started);
		if(empty($date_investigation_started)) {
			array_push($errors, "Date Investigation (Started)" . $is_required_suffix);
		}

		$date_investigation_ended = trim($date_investigation_ended);
		if(empty($date_investigation_ended)) {
			array_push($errors, "Date Investigation (Ended)" . $is_required_suffix);
		}

		if(!(empty($date_investigation_started) || empty($date_investigation_ended))) {
			if(!isCorrectDateRange($date_investigation_started, $date_investigation_ended)) {
				array_push($errors, 'Invalid date range. Date Investigation (Ended) should not be earlier than Date Investigation (Started).');
			}
		}
		
		return $errors;
	}
	
	#validate common fields
	private function validate_form_common_fields($form, $type) {
		
		$errors = array();
		$is_required_suffix = " is required.";
		
		#common fields
		$corr_prev_action = isset($form["corr_prev_action"]) ? $form["corr_prev_action"] : '';
		$corr_prev_proposed_by = isset($form["corr_prev_proposed_by"]) ? $form["corr_prev_proposed_by"] : '';
		$corr_prev_target_start_date = isset($form["corr_prev_target_start_date"]) ? $form["corr_prev_target_start_date"] : '';
		$corr_prev_target_end_date = isset($form["corr_prev_target_end_date"]) ? $form["corr_prev_target_end_date"] : '';

		$corr_prev_action = trim($corr_prev_action);
		if(empty($corr_prev_action)) {
			array_push($errors, "Corrective/Preventive (Continual Improvement) action" . $is_required_suffix);
		} else {
			if(strlen($corr_prev_action) < MIN_CPAR_CORR_PREV_ACTION || strlen($corr_prev_action) > MAX_CPAR_CORR_PREV_ACTION) {
				array_push($errors, "Corrective/Preventive (Continual Improvement) action should be " . MIN_CPAR_CORR_PREV_ACTION . " to " . MAX_CPAR_CORR_PREV_ACTION . " characters.");
			}
		}

		$corr_prev_proposed_by = trim($corr_prev_proposed_by);
		if(empty($corr_prev_proposed_by)) {
			array_push($errors, "Proposed by" . $is_required_suffix);
		}

		$corr_prev_target_start_date = trim($corr_prev_target_start_date);
		if(empty($corr_prev_target_start_date)) {
			array_push($errors, "Target Start Date" . $is_required_suffix);
		}

		$corr_prev_target_end_date = trim($corr_prev_target_end_date);
		if(empty($corr_prev_target_end_date)) {
			array_push($errors, "Target End Date" . $is_required_suffix);
		}

		if(!(empty($corr_prev_target_start_date) || empty($corr_prev_target_end_date))) {
			if(!isCorrectDateRange($corr_prev_target_start_date, $corr_prev_target_end_date)) {
				array_push($errors, 'Invalid date range. Target End Date should not be earlier than Target Start Date.');		
			}
		}

		//validate tasks
		$tasks = isset($form["tasks"]) ? $form["tasks"] : '';
		$tasks_to_add = isset($form["tasks_to_add"]) ? $form["tasks_to_add"] : '';
		if(empty($tasks) && empty($tasks_to_add)) {
			array_push($errors, "Please input at least 1 Corrective/Preventive (Continual Improvement) Action Detail.");
		} else {
			$arr = json_decode($tasks);
			$arr_to_add = json_decode($tasks_to_add);
			if((!$arr || $arr == null || empty($arr)) && (!$arr_to_add || $arr_to_add == null || empty($arr_to_add))) {
				array_push($errors, "Please input at least 1 Corrective/Preventive (Continual Improvement) Action Detail.");
			}
		}

		return $errors;

	}
	
	/*****************************/
	
	#validate permissions: see if the user can edit, view or save, 
	private function validate_permissions_stage2($cpar) {
		$errors = array();

		$l_id = $this->CI->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar->status != CPAR_STAGE_2) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			$access = $this->CI->users_model->getUserAccessRights($l_id);
			$allowed_access = ($access->mr_flag == MR_FLAG || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if($cpar->status == CPAR_STAGE_2 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0) {
				if(!$allowed_access && 
					!($l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->assigned_ims)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else if($cpar->status == CPAR_STAGE_2 && 
				(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A2) == 0 || 
					strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2B) == 0)) {
				if(!$allowed_access && 
					!($l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->addressee_team_lead ||
						$l_id == $cpar->assigned_ims)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else if($cpar->status != CPAR_STAGE_2) {
				array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
			}
		}

		if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		}

		return $errors;
	}
	
	private function validate_permissions_stage3($cpar) {
		$errors = array();

		$l_id = $this->CI->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		} else if($cpar->status != CPAR_STAGE_3) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			$access = $this->CI->users_model->getUserAccessRights($l_id);
			$allowed_access = ($access->mr_flag == MR_FLAG || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if($cpar->status == CPAR_STAGE_3 && (int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3A) {
				if(!$allowed_access && !(
						$l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->addressee_team_lead ||
						$l_id == $cpar->assigned_ims)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else if($cpar->status == CPAR_STAGE_3 && 
				((int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3B || (int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3B2)) {
				//get responsible persons
				$resp_persons = normalizeResponsiblePersons($this->CI->addressee_fields_model->getResponsiblePersons($cpar->id));
				$resp_person_access = in_array($l_id, $resp_persons);

				if(!$allowed_access && !(
						$l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->addressee_team_lead ||
						$l_id == $cpar->assigned_ims) && !$resp_person_access) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			}
		}

		return $errors;
	}
	
}