<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cpar_s3 extends MY_Controller {

	var $success_msg = NULL;

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->library('uri');
		$this->load->library('upload');
		$this->load->library('email');

		$this->load->model('users_model');
		$this->load->model('cpar_model');
		$this->load->model('review_history_model');
		$this->load->model('addressee_fields_model');
		$this->load->model('ff_up_history_model');
		$this->load->model('audit_log_model');

		$this->load->helper('cpar_helper');
		$this->load->helper('cpar_file_helper');
	}

	public function index() {
		$this->show_custom_404();
	}

	public function review() {
		$errors = array();
		$cpar_no = $this->uri->segment(3);

		if($cpar_no == null || empty($cpar_no)) {
			$this->show_custom_404();
		} else {
			$cpar = $this->cpar_model->getCpar($cpar_no);

			if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
				array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
			} else {
				$errors = array();
				$errors = $this->validatePermissions($cpar);

				if(empty($errors)) {
					//for header text
					$header_text = '';
					if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3A) == 0) {
						$header_text = '(For IMS Review)';
					}

					$addressee_fields = $this->addressee_fields_model->getAddresseeFields($cpar_no);

					$data['header_text'] = $header_text;
					$data['cpar'] = $cpar;
					$data['cpar_no'] = $cpar_no;
					$data['addr_fields'] = $addressee_fields;
					$data['access'] = $this->users_model->getUserAccessRights($this->session->userdata('loggedIn'));

					//get uploaded files
					$data['filenames'] = getFileNames($cpar_no, CPAR_STAGE_1);
					$data['rad_filenames'] = getFileNames($cpar_no, UPLOAD_2RAD_PREFIX);
					$data['rca_filenames'] = getFileNames($cpar_no, UPLOAD_2RCA_PREFIX);
					
					$data['ap_attachments'] = getActionPlanFiles($cpar_no);

					//get review history
					$review_history = $this->review_history_model->getReviewHistory($cpar_no);
					$data['review_history'] = $review_history;

					//get action plan details
					$tasks = $this->addressee_fields_model->getActionPlanDetails($cpar_no);
					formatTasksForRender($tasks);
					$serialized_tasks = '';
					if($tasks) {
						$serialized_tasks = json_encode($tasks);
					}
					$data['serialized_tasks'] = $serialized_tasks;

					//get root cause analysis tools
					$tools_left = array();
					$tools_right = array();
					$rca_tools = $this->cpar_model->getIdSortedGenericList($this->cpar_model->tbl_rca_tools);
					divideToolsList($rca_tools, $tools_left, $tools_right);

					$data['tools_left'] = $tools_left;
					$data['tools_right'] = $tools_right;
					$data['rca_tools'] = $rca_tools;
				}
			}

			if(!empty($errors)) {
				$this->session->set_userdata('search_page_errors', $errors);
				redirect('http://' . base_url() . 'cpar/');
			} else {
				//get dropdown lists
				$data['team_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_team);
				$data['raaro_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_raaro);
				$data['process_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_process);

				$data['screen_title'] = 'Review CPAR';

				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				
				//determine user edit rights
				$can_edit_cpar = false;
				$can_edit_actions = false;

				$this->canEdit($cpar, $can_edit_cpar, $can_edit_actions);

				$data['can_edit_cpar'] = $can_edit_cpar;
				$data['can_edit_actions'] = $can_edit_actions;

				$this->load->view('cpar_s3/review', $data);				
				$this->load->view('template/footer');
			}
		}
	}

	public function edit() {
		$errors = array();
		$cpar_no = $this->uri->segment(3);

		if($cpar_no == null || empty($cpar_no)) {
			$this->show_custom_404();
		} else {
			$cpar = $this->cpar_model->getCpar($cpar_no);

			if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
				array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
			} else {
				$errors = array();
				$errors = $this->validatePermissions($cpar);

				if(empty($errors)) {
					$addressee_fields = $this->addressee_fields_model->getAddresseeFields($cpar_no);

					$data['cpar'] = $cpar;
					$data['cpar_no'] = $cpar_no;
					$data['addr_fields'] = $addressee_fields;
					$data['access'] = $this->users_model->getUserAccessRights($this->session->userdata('loggedIn'));

					//get uploaded files
					$data['filenames'] = getFileNames($cpar_no, CPAR_STAGE_1);
					$data['rad_filenames'] = getFileNames($cpar_no, UPLOAD_2RAD_PREFIX);
					$data['rca_filenames'] = getFileNames($cpar_no, UPLOAD_2RCA_PREFIX);
					
					$data['ap_attachments'] = getActionPlanFiles($cpar_no);

					//get review history
					$review_history = $this->review_history_model->getReviewHistory($cpar_no);
					$data['review_history'] = $review_history;

					//get follow up history
					$ff_up_history = $this->ff_up_history_model->getReviewHistory($cpar_no);
					$this->getFfUpUploadedFiles($cpar_no, $ff_up_history);
					$data['ff_up_history'] = $ff_up_history;

					//get action plan details
					$tasks = $this->addressee_fields_model->getActionPlanDetails($cpar_no);
					formatTasksForRender($tasks);
					$serialized_tasks = '';
					if($tasks) {
						$serialized_tasks = json_encode($tasks);
					}
					$data['serialized_tasks'] = $serialized_tasks;

					//get root cause analysis tools
					$tools_left = array();
					$tools_right = array();
					$rca_tools = $this->cpar_model->getIdSortedGenericList($this->cpar_model->tbl_rca_tools);
					divideToolsList($rca_tools, $tools_left, $tools_right);

					$data['tools_left'] = $tools_left;
					$data['tools_right'] = $tools_right;
					$data['rca_tools'] = $rca_tools;

					//add string of "Verified"
					$data['verified_string'] = getTaskStatusName(APD_STATUS_VERIFIED);
				}
			}

			if(!empty($errors)) {
				$this->session->set_userdata('search_page_errors', $errors);
				redirect('http://' . base_url() . 'cpar/');
			} else {
				//get dropdown lists
				$data['team_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_team);
				$data['raaro_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_raaro);
				$data['process_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_process);

				$data['screen_title'] = 'Edit CPAR';

				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				
				//determine user edit rights
				$can_edit_cpar = false;
				$can_edit_actions = false;

				$this->canEdit($cpar, $can_edit_cpar, $can_edit_actions);

				$data['can_edit_cpar'] = $can_edit_cpar;
				$data['can_edit_actions'] = $can_edit_actions;

				//header text
				$header_text = '(For Addressee Input)';
				if($can_edit_cpar) {
					$header_text = '(For IMS Follow-up Review)';
				}
				$data['header_text'] = $header_text;

				$this->load->view('cpar_s3/edit', $data);
				$this->load->view('template/footer');
			}
		}
	}

	public function saving() {
		echo $this->stages->validate_stage3();
	}

	public function saveReview() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
			
			$cpar_no = $form['id'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validatePermissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validateImsReview($form);
			}

		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$result = $this->processReviewForm($form);
		}

		if($result != null && $result->success) {
			$result->success = true;
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	public function saveReview_3b() {
		
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
			
			$cpar_no = $form['id'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validatePermissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validateImsReview_3b($form);
			}
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$result = $this->processReviewForm($form);
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

		echo json_encode($result);
	}

	public function updateAddresseeRemarks() {
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validateUpdateTask_AddresseePermissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validateUpdateTasRemarks($form);
			}
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$new_remarks = fix_output2(trim($form["new_remarks"]));
			$new_remarks = fix_output2($new_remarks);
			$task_id = $form["task_id"];
			$apd = new stdClass();
			$apd->remarks_addr = $new_remarks;

			$result = $this->addressee_fields_model->updateActionPlanDetails($apd, $task_id);

			//get updated APD
			$apd = $this->addressee_fields_model->getSingleActionPlanDetail($task_id);
			formatSingleTaskForRender($apd);
			$result->apd = $apd;
		}

		if($result != null && $result->success) {
			$result->success = true;
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	public function updateImsRemarks() {
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validateUpdateTask_ImsPermissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validateUpdateTasRemarks($form);
			}
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$new_remarks = fix_output2(trim($form["new_remarks"]));
			$task_id = $form["task_id"];
			$apd = new stdClass();
			$apd->remarks_ims = $new_remarks;

			$result = $this->addressee_fields_model->updateActionPlanDetails($apd, $task_id);

			//get updated APD
			$apd = $this->addressee_fields_model->getSingleActionPlanDetail($task_id);
			formatSingleTaskForRender($apd);
			$result->apd = $apd;
		}

		if($result != null && $result->success) {
			$result->success = true;
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	public function markAsOngoing() {
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validateUpdateTask_AddresseePermissions($cpar);
			
			$can_edit_cpar = false;
			$can_edit_actions = false;
			
			$this->canEdit($cpar, $can_edit_cpar, $can_edit_actions);
			
			##OVERRRIDE
			if($can_edit_cpar) {
				$errors = array();
			}
	
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$task_id = $form["task_id"];
			$apd = new stdClass();
			$apd->status = APD_STATUS_ONGOING;

			$result = $this->addressee_fields_model->updateActionPlanDetails($apd, $task_id);
			
			//get updated APD
			$apd = $this->addressee_fields_model->getSingleActionPlanDetail($task_id);
			formatSingleTaskForRender($apd);
			$result->apd = $apd;
		}

		if($result != null && $result->success) {
			$result->success = true;
			
			#LOG HERE
			$l_id = $this->session->userdata('loggedIn');
			$log = new stdClass();
			$log->cpar_no = $cpar_no;
			$log->action = LOG_MARK_TASK_ONGOING;
			$log->stage = $cpar->status;
			$log->sub_status = $cpar->sub_status;
			$log->remarks = '';
			$log->notes = json_encode($form);
			$log->created_by = $l_id;
			$log->created_date = date('Y-m-d H:i:s');
			$this->audit_log_model->insertLog($log);

		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	public function markAsDone() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validateUpdateTask_AddresseePermissions($cpar);
			
			$this->canEdit($cpar, $can_edit_cpar, $can_edit_actions);
			
			##OVERRRIDE
			if($can_edit_cpar) {
				$errors = array();
			}

		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$task_id = $form["task_id"];
			$apd = new stdClass();
			$apd->status = APD_STATUS_DONE;
			$apd->completed_date = date('Y-m-d');

			$result = $this->addressee_fields_model->updateActionPlanDetails($apd, $task_id);
			
			//get updated APD
			$apd = $this->addressee_fields_model->getSingleActionPlanDetail($task_id);
			formatSingleTaskForRender($apd);
			$result->apd = $apd;
		}

		if($result != null && $result->success) {
			$result->success = true;
			
			#LOG HERE
			$l_id = $this->session->userdata('loggedIn');
			$log = new stdClass();
			$log->cpar_no = $cpar_no;
			$log->action = LOG_MARK_TASK_DONE;
			$log->stage = $cpar->status;
			$log->sub_status = $cpar->sub_status;
			$log->remarks = '';
			$log->notes = json_encode($form);
			$log->created_by = $l_id;
			$log->created_date = date('Y-m-d H:i:s');
			$this->audit_log_model->insertLog($log);
			
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	public function markAsVerified() {
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validateUpdateTask_ImsPermissions($cpar);
			
			$this->canEdit($cpar, $can_edit_cpar, $can_edit_actions);
			
			##OVERRRIDE
			if($can_edit_cpar) {
				$errors = array();
			}

		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}


		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$task_id = $form["task_id"];
			$apd = new stdClass();
			$apd->status = APD_STATUS_VERIFIED;
			$apd->completed_date = date('Y-m-d');

			$result = $this->addressee_fields_model->updateActionPlanDetails($apd, $task_id);
			
			//get updated APD
			$apd = $this->addressee_fields_model->getSingleActionPlanDetail($task_id);
			formatSingleTaskForRender($apd);
			$result->apd = $apd;
		}

		if($result != null && $result->success) {
			$result->success = true;
			
			#LOG HERE
			$l_id = $this->session->userdata('loggedIn');
			$log = new stdClass();
			$log->cpar_no = $cpar_no;
			$log->action = LOG_MARK_TASK_VERIFIED;
			$log->stage = $cpar->status;
			$log->sub_status = $cpar->sub_status;
			$log->remarks = '';
			$log->notes = json_encode($form);
			$log->created_by = $l_id;
			$log->created_date = date('Y-m-d H:i:s');
			$this->audit_log_model->insertLog($log);
			
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	private function validatePermissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		} else if($cpar->status != CPAR_STAGE_3) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			$access = $this->users_model->getUserAccessRights($l_id);
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
				$resp_persons = normalizeResponsiblePersons($this->addressee_fields_model->getResponsiblePersons($cpar->id));
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

	private function validateUpdateTask_ImsPermissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($l_id);

		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		} else if($cpar->status != CPAR_STAGE_3) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			if($cpar->status == CPAR_STAGE_3 && 
				((int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3B || (int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3B2)) {
				if((int)$l_id != (int)$cpar->assigned_ims && (int)$access->access_level != ACCESS_LEVEL_ADMIN_FLAG) {
					array_push($errors, "Unable to update <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else {
				#array_push($errors, "This record has been updated. Please reopen the record from CPAR list.");
			}
		}

		return $errors;
	}

	private function validateUpdateTask_AddresseePermissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		} else if($cpar->status != CPAR_STAGE_3) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			if($cpar->status == CPAR_STAGE_3 && 
				((int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3B || (int)$cpar->sub_status == CPAR_MINI_STATUS_S3_3B2)) {
				if((int)$l_id != (int)$cpar->addressee) {
					array_push($errors, "Unable to update <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else {
				#array_push($errors, "This record has been updated. Please reopen the record from CPAR list.");
				array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
			}
		}

		return $errors;
	}

	private function canEdit($cpar, &$can_edit_cpar, &$can_edit_actions) {
		$can_edit_cpar = false;
		$can_edit_actions = false;

		$l_id = (int)$this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($l_id);

		if($cpar->status == CPAR_STAGE_3) {
			$admin_access = ((int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3A) == 0 || 
				strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3B) == 0 || 
				strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3B2) == 0) {
				//assigned IMS and admin can edit cpar and corrective actions
				if((int)$cpar->assigned_ims == $l_id || $admin_access) {
					$can_edit_cpar = true;
					$can_edit_actions = true;
				}
			}
		}
	}

	private function validateImsReview($form) {
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
				array_push($errors, "Remarks " . $is_required_suffix);
			}
		} else {
			if(strlen($remarks) < MIN_CPAR_REMARKS || strlen($remarks) > MAX_CPAR_REMARKS) {
				array_push($errors, "Remarks should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
			}
		}

		return $errors;
	}

	private function validateImsReview_3b($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
		if(empty($review_action)) {
			array_push($errors, 'Please choose a review action.');
		} else if((int)$review_action == REVIEW_ACTIONS_S3_MARK_IMPL) {
			$ev_ff_up_date = isset($form["ev_ff_up_date"]) ? $form["ev_ff_up_date"] : '';
			$ev_ff_up_date = trim($ev_ff_up_date);
			if(empty($ev_ff_up_date)) {
				array_push($errors, "Efficiency Verification Follow-up Date" . $is_required_suffix);
			}

			//validate verified tasks
			$tasks = $this->addressee_fields_model->getActionPlanDetails($form['id']);

			foreach ($tasks as $task) {
				if((int)$task['status'] != APD_STATUS_VERIFIED) {
					array_push($errors, "Cannot mark CPAR Record as Implemented. CPAR Records can only be marked as Implemented if all Corrective Action Plan Detail Tasks / Preventive Action (Continual Improvement Action) Plan Detail Tasks are verified.");
					break;
				}
			}

		} else if((int)$review_action == REVIEW_ACTIONS_S3_MARK_FF_UP) {
			$next_ff_up_date = isset($form["next_ff_up_date"]) ? $form["next_ff_up_date"] : '';
			$next_ff_up_date = trim($next_ff_up_date);
			if(empty($next_ff_up_date)) {
				array_push($errors, "Next Follow - Up Date" . $is_required_suffix);
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
			if(empty($review_action) || (int)$review_action == REVIEW_ACTIONS_S3_PUSH_BACK || (int)$review_action == REVIEW_ACTIONS_S3_MARK_FF_UP) {
				array_push($errors, "Remarks" . $is_required_suffix);	
			}
		} else {
			if(strlen($remarks) < MIN_CPAR_REMARKS || strlen($remarks) > MAX_CPAR_REMARKS) {
				array_push($errors, "Remarks should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
			}
		}

		return $errors;
	}

	private function validateUpdateTasRemarks($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		$task_id = isset($form["task_id"]) ? $form["task_id"] : '';
		$task_id = trim($task_id);
		if(empty($task_id)) {
			array_push($errors, "Task ID" . $is_required_suffix);
		}

		$new_remarks = isset($form["new_remarks"]) ? $form["new_remarks"] : '';
		$new_remarks = trim($new_remarks);
		if(empty($new_remarks)) {
			array_push($errors, "Remarks" . $is_required_suffix);
		} else {
			if(strlen($new_remarks) < MIN_CPAR_REMARKS || strlen($new_remarks) > MAX_CPAR_REMARKS) {
				array_push($errors, "Remarks should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
			}
		}

		return $errors;
	}

	private function processReviewForm($form) {
		$l_id = $this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($l_id);
		
		$errors = array();
		
		$result = new stdClass();
		$upload_error = false;
		
		$cpar = new stdClass();
		$cpar_no = $form['id'];
		$addressee_fields = new stdClass();
		$old_cpar = $this->cpar_model->getCpar($cpar_no);

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
			$this->createFfUpHistory($cpar_no, $ev_ff_up_date, FF_UP_RESULT_IMPL, $remarks);

			//upload attachments
			$insert_id = $this->db->insert_id();
			
			if(!$this->uploadFiles($cpar_no, $review_action, $insert_id)) {
				$this->ff_up_history_model->rollbackFFUpHistory($insert_id);
				$upload_error = true;
				array_push($errors, $this->upload->display_errors('',''));
			} else {
				$b_send_emails = TRUE;
				$mail_stage = '4';
				$mail_sub_stage = 'A';
				
				$this->success_msg = MSG_REVIEW_ACTIONS_S3_MARK_IMPL;			
			}

		} else if((int)$review_action == REVIEW_ACTIONS_S3_MARK_FF_UP) {
			$status = CPAR_STAGE_3;
			$sub_status = CPAR_MINI_STATUS_S3_3B;

			//update next_ff_up_date
			$next_ff_up_date = isset($form["next_ff_up_date"]) ? $form["next_ff_up_date"] : '';
			$remarks = (isset($form["remarks"]) ? fix_output2($form["remarks"]) : '');
			$cpar->ff_up_date = formatDateForDB($next_ff_up_date);

			//insert follow up history
			$this->createFfUpHistory($cpar_no, $next_ff_up_date, FF_UP_RESULT_FOR_FF, $remarks);

			//upload attachments
			$insert_id = $this->db->insert_id();
			
			if(!$this->uploadFiles($cpar_no, $review_action, $insert_id)) {
				$this->ff_up_history_model->rollbackFFUpHistory($insert_id);
				$upload_error = true;
				array_push($errors, $this->upload->display_errors('',''));
			} else {
				$this->success_msg = MSG_REVIEW_ACTIONS_S3_MARK_FF_UP;		
			}
			
		} else if((int)$review_action == REVIEW_ACTIONS_S3_PUSH_BACK) {
			$status = CPAR_STAGE_2;
			$sub_status = CPAR_MINI_STATUS_S2_2A2;

			$cpar->pb_user = $l_id;
			$cpar->pb_date = date('Y-m-d H:i:s');
			$cpar->pb_remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';

			//update due date
			$cpar->date_due = formatDateForDB(isset($form["next_due_date"]) ? $form["next_due_date"] : '');

			$addressee_fields->cpar_no = $cpar_no;
			$addressee_fields->accomplish_by = formatDateForDB(isset($form["next_due_date"]) ? $form["next_due_date"] : '');
			$result = $this->addressee_fields_model->updateAddresseeFields($addressee_fields);
			
			$b_send_emails = TRUE;
			$mail_stage = '3';
			$mail_sub_stage = 'AX';
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S3_PUSH_BACK;

		}

		$cpar->status = $status;
		$cpar->sub_status = $sub_status;
		$cpar->updated_by = $l_id;
		$cpar->updated_date = date('Y-m-d H:i:s');

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$result = $this->cpar_model->updateCpar($cpar, $cpar_no);
			
			if($result && $b_send_emails && $mail_stage && $mail_sub_stage && !$upload_error) {
				$this->email->processEmails($cpar_no, $mail_stage, $mail_sub_stage);
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
					$this->review_history_model->insertReviewHistory($rev_hist);
				}
				
				#LOG HERE
				$log = new stdClass();
				$log->cpar_no = $cpar_no;
				$log->action = $this->audit_log_model->get_action($review_action);
				$log->stage = CPAR_STAGE_3;
				$log->sub_status = $old_cpar->sub_status;
				$log->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
				$log->notes = json_encode($form);
				$log->created_by = $l_id;
				$log->created_date = date('Y-m-d H:i:s');
				$this->audit_log_model->insertLog($log);
			}	
		}

		return $result;
	}

	private function createFfUpHistory($cpar_no, $ff_up_date, $ff_result, $remarks) {
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

		$apd = $this->addressee_fields_model->getActionPlanDetails($cpar_no);
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

		$this->ff_up_history_model->insertFfUpHistory($ff_up_history);
	}

	private function uploadFiles($cpar_no, $review_action, $ff_up_id) {
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
				$this->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no . '_' . $ff_up_id, UPLOAD_3FFUP_PREFIX, 's3impl_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $s3impl_upload = $this->upload->do_multi_upload('s3impl_attachments');
			}

			if((int)$review_action == REVIEW_ACTIONS_S3_MARK_FF_UP && 
				isset($_FILES['s3ffup_attachments']) && !empty($_FILES['s3ffup_attachments'])) {
				$this->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no . '_' . $ff_up_id, UPLOAD_3FFUP_PREFIX, 's3ffup_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $s3ffup_upload = $this->upload->do_multi_upload('s3ffup_attachments');
			}

	    	if(!$s3impl_upload || !$s3ffup_upload) {
	    		$success = false;
	    		$result = new stdClass();
	    		$result->success = false;
				$result->errors = array();
				array_push($result->errors, $this->upload->display_errors('',''));
	    	}
		}

		return $success;
	}

	private function getFfUpUploadedFiles($cpar_no, &$ff_up_history) {
		if(!empty($ff_up_history)) {
			foreach ($ff_up_history as $key => $item) {
				$filenames = $this->getFileNames($cpar_no . '_' . $item['id'], UPLOAD_3FFUP_PREFIX);
				$item['filenames'] = $filenames;
				$ff_up_history[$key] = $item;				
			}
		}
	}

	private function getFileNames($cpar_no, $stage) {
	    $prefix = $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR;
	    $filenames = preg_grep('~^' . $prefix . '.*$~', scandir(UPLOAD_PATH));

	    $obj = null;
	    $arr = array();
	    foreach ($filenames as $filename) {
	        $obj = new stdClass();
	        $obj->filename = $filename;
	        $obj->orig_filename = splitn($filename, UPLOAD_FILENAME_SEPARATOR, UPLOAD_FILENAME_OFFSET + 1);

	        array_push($arr, $obj);
	    }

	    return $arr;
	}
}