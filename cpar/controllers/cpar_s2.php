<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cpar_s2 extends MY_Controller {

	var $success_msg = NULL;

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->library('upload');
		$this->load->library('uri');
		$this->load->library('email');		

		$this->load->model('users_model');
		$this->load->model('cpar_model');
		$this->load->model('review_history_model');
		$this->load->model('addressee_fields_model');
		$this->load->model('audit_log_model');

		$this->load->helper('cpar_helper');
		$this->load->helper('cpar_file_helper');
	}

	public function index() {
		$this->show_custom_404();
	}

	public function edit() {
		$forTL = false;
		$this->editOrReview($forTL);
	}

	public function review() {
		$forTL = true;
		$this->editOrReview($forTL);
	}

	private function editOrReview($forTL) {
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
					if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0) {
						$header_text = '(For Addressee Input)';
					} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2B) == 0) {
						$header_text = '(For Team Leader Review)';
					}

					$data['cpar'] = $cpar;
					$data['cpar_no'] = $cpar_no;

					$addressee_fields = $this->addressee_fields_model->getAddresseeFields($cpar_no);
					if(!$addressee_fields) {
						$addressee_fields = $this->getBlankAddresseeFields();
					}
					$data['addr_fields'] = $addressee_fields;
					
					$data['header_text'] = $header_text;
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
					$this->divideToolsList($rca_tools, $tools_left, $tools_right);

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
				
				//determine user edit rights
				$can_edit_cpar_info = false;
				$can_edit_corr_actions = false;
				$can_edit_actions = false;

				$this->canEdit($cpar, $can_edit_cpar_info, $can_edit_corr_actions, $can_edit_actions);
				
				if($forTL) {
					$data['screen_title'] = 'Review CPAR';
					if($can_edit_corr_actions) {
						$data['screen_title'] = 'Review CPAR';
					}
				} else {
					$data['screen_title'] = 'View CPAR';
					if($can_edit_corr_actions) {
						$data['screen_title'] = 'Edit CPAR';
					}
				}

				$this->addCommonData($data);
				$this->load->view('template/header', $data);

				$data['can_edit_cpar_info'] = $can_edit_cpar_info;
				$data['can_edit_corr_actions'] = $can_edit_corr_actions;
				$data['can_edit_actions'] = $can_edit_actions;
				
				if($forTL) {
					$view = 'review_view';
					if($can_edit_corr_actions) {
						$view = 'review';
					}

					$this->load->view('cpar_s2/' . $view, $data);
				} else {
					$view = 'view';
					if($can_edit_corr_actions) {
						$view = 'edit';
					}
					$this->load->view('cpar_s2/' . $view, $data);
				}
				
				$this->load->view('template/footer');
			}
		}
	}

	//almost the same with saveDraft except validateFOrm() is called, and isDraft = false
	public function save() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['id'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
			$errors = $this->validatePermissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validateForm($form, $cpar->type);
			}
	
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}
	

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$isDraft = false;

			//removed uploads if not empty
		    $removed_rad_uploads = $this->input->post('removed_rad_uploads');
		    $removed_rca_uploads = $this->input->post('removed_rca_uploads');

		    if($removed_rad_uploads && !empty($removed_rad_uploads)) {
		    	$removed_rad_uploads = json_decode($removed_rad_uploads);
		    }

		    if($removed_rca_uploads && !empty($removed_rca_uploads)) {
		    	$removed_rca_uploads = json_decode($removed_rca_uploads);
		    }

		    $this->removeUploadedFiles($cpar_no, UPLOAD_2RAD_PREFIX, $removed_rad_uploads);
		    $this->removeUploadedFiles($cpar_no, UPLOAD_2RCA_PREFIX, $removed_rca_uploads);

			if(!empty($_FILES)) {
				//check if upload folder exists
		   		if (!is_dir(UPLOAD_PATH)) {
				    mkdir(UPLOAD_PATH);
				}

				$rad_upload = true;
				$rca_upload = true;

				if(isset($_FILES['rad_attachments']) && !empty($_FILES['rad_attachments'])) {
					$this->upload->initialize(array(
						'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RAD_PREFIX, 'rad_attachments', $_FILES),
				        'upload_path'   => UPLOAD_PATH,
				        'allowed_types' => UPLOAD_ALLOWED_TYPES
				    ));
				    $rad_upload = $this->upload->do_multi_upload('rad_attachments');
				}

			    if($rad_upload) {
			    	if(isset($_FILES['rca_attachments']) && !empty($_FILES['rca_attachments'])) {
						$this->upload->initialize(array(
							'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RCA_PREFIX, 'rca_attachments', $_FILES),
					        'upload_path'   => UPLOAD_PATH,
					        'allowed_types' => UPLOAD_ALLOWED_TYPES
					    ));
					    $rca_upload = $this->upload->do_multi_upload('rca_attachments');
					}

			    	if($rca_upload) {
			    		$result = $this->processForm($form, $isDraft);
			    	} else {
			    		$result->success = false;
						$result->errors = array();
						array_push($result->errors, $this->upload->display_errors('',''));
			    	}
			    } else {
			    	$result->success = false;
					$result->errors = array();
					array_push($result->errors, $this->upload->display_errors('',''));
			    }
			} else {
				$result = $this->processForm($form, $isDraft);
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

		echo json_encode($result);
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
				$errors = $this->validateTlReview($form);
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

	public function saveDraft() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['id'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
			$errors = $this->validatePermissions($cpar);
	
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$isDraft = true;

			//removed uploads if not empty
		    $removed_rad_uploads = $this->input->post('removed_rad_uploads');
		    $removed_rca_uploads = $this->input->post('removed_rca_uploads');

		    if($removed_rad_uploads && !empty($removed_rad_uploads)) {
		    	$removed_rad_uploads = json_decode($removed_rad_uploads);
		    }

		    if($removed_rca_uploads && !empty($removed_rca_uploads)) {
		    	$removed_rca_uploads = json_decode($removed_rca_uploads);
		    }

		    $this->removeUploadedFiles($cpar_no, UPLOAD_2RAD_PREFIX, $removed_rad_uploads);
		    $this->removeUploadedFiles($cpar_no, UPLOAD_2RCA_PREFIX, $removed_rca_uploads);

			if(!empty($_FILES)) {
				//check if upload folder exists
		   		if (!is_dir(UPLOAD_PATH)) {
				    mkdir(UPLOAD_PATH);
				}

				$rad_upload = true;
				$rca_upload = true;

				if(isset($_FILES['rad_attachments']) && !empty($_FILES['rad_attachments'])) {
					$this->upload->initialize(array(
						'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RAD_PREFIX, 'rad_attachments', $_FILES),
				        'upload_path'   => UPLOAD_PATH,
				        'allowed_types' => UPLOAD_ALLOWED_TYPES
				    ));
				    $rad_upload = $this->upload->do_multi_upload('rad_attachments');
				}

			    if($rad_upload) {
			    	if(isset($_FILES['rca_attachments']) && !empty($_FILES['rca_attachments'])) {
						$this->upload->initialize(array(
							'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RCA_PREFIX, 'rca_attachments', $_FILES),
					        'upload_path'   => UPLOAD_PATH,
					        'allowed_types' => UPLOAD_ALLOWED_TYPES
					    ));
					    $rca_upload = $this->upload->do_multi_upload('rca_attachments');
					}

			    	if($rca_upload) {
			    		$result = $this->processForm($form, $isDraft);
			    	} else {
			    		$result->success = false;
						$result->errors = array();
						array_push($result->errors, $this->upload->display_errors('',''));
			    	}
			    } else {
			    	$result->success = false;
					$result->errors = array();
					array_push($result->errors, $this->upload->display_errors('',''));
			    }
			} else {
				$result = $this->processForm($form, $isDraft);
			}
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

	public function updateDueDate() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = isset($form["id"]) ? $form["id"] : '';
			$accomplish_by = isset($form["accomplish_by"]) ? $form["accomplish_by"] : '';
	
			//validate
			$is_required_suffix = " is required.";
			if(empty($cpar_no)) {
				array_push($errors, "CPAR no. cannot be empty.");
			}
	
			if(empty($accomplish_by)) {
				array_push($errors, "Accomplish by" . $is_required_suffix);
			}
	
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(empty($errors)) {
			//get CPAR and access to check permisson
			$l_id = $this->session->userdata('loggedIn');
			$cpar = $this->cpar_model->getCpar($cpar_no);
			$access = $this->users_model->getUserAccessRights($l_id);
			$old_date_due = $cpar->date_due;
			//can only update if assigned IMS or admin
			if($l_id == (int)$cpar->assigned_ims || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
				$to_update = new stdClass();
				$to_update->date_due = formatDateForDB($accomplish_by);

				$result = $this->cpar_model->updateCpar($to_update, $cpar_no);

				//update addresse_fields record if it exists
				$existing = $this->addressee_fields_model->isExisting($cpar_no);
				if(!($existing = null || empty($existing))) {
					$addresse_fields = new stdClass();
					$addresse_fields->cpar_no = $cpar_no;
					$addresse_fields->accomplish_by = formatDateForDB($accomplish_by);

					$result = $this->addressee_fields_model->updateAddresseeFields($addresse_fields);
				}
				
				if($result) {
					#LOG HERE
					$log = new stdClass();
					$log->cpar_no = $cpar_no;
					$log->action = LOG_DUE_DATE_UPDATED;
					$log->stage = $cpar->status;
					$log->sub_status = $cpar->sub_status;
					$log->remarks = '';
					$log->notes = json_encode(array('old_date_due' => $old_date_due, 'new_date_due' => $to_update->date_due));
					$log->created_by = $l_id;
					$log->created_date = date('Y-m-d H:i:s');
					$this->audit_log_model->insertLog($log);
				}
			} else {
				$result->success = false;
				array_push($errors, "You do not have permisson to change the due date of this CPAR record.");
			}
		} else {
			$result->success = false;
			$result->errors = $errors;
		}

		if($result != null && $result->success) {
			$result->success = true;
			$result->success_msg = "Due Date successfully updated.";
		} else {
			$result->success = false;

			if(isset($result->error)) {
				$result->errors = array();
				array_push($result->errors, $result->error);
			} else {
				$result->errors = $errors;
			}
		}

		echo json_encode($result);
	}

	private function divideToolsList($rca_tools, &$left_list, &$right_list) {
		foreach ($rca_tools as $tool) {
			if(intval($tool['id']) % 2 == 0) {
				array_push($right_list, $tool);
			} else {
				array_push($left_list, $tool);
			}
		}
	}

	private function validatePermissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar->status != CPAR_STAGE_2) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			$access = $this->users_model->getUserAccessRights($l_id);
			$allowed_access = ($access->mr_flag == MR_FLAG || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if($cpar->status == CPAR_STAGE_2 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0) {
				if(!$allowed_access && 
					!($l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->addressee_team_lead ||
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

	private function validateForm($form, $type) {
		$errors = array();
		$is_required_suffix = " is required.";

		//CAR fields
		if(intval($type) == CPAR_TYPE_C) {
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

			/*
$details = isset($form["details"]) ? $form["details"] : '';

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
*/

			if(!(empty($date_investigation_started) || empty($date_investigation_ended))) {
				if(!isCorrectDateRange($date_investigation_started, $date_investigation_ended)) {
					array_push($errors, 'Invalid date range. Date Investigation (Ended) should not be earlier than Date Investigation (Started).');
				}
			}
		}
		
		if(intval($type) != CPAR_TYPE_C) {
			//common fields
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
		} else {
			//common fields
			$corr_prev_action = isset($form["corr_prev_action"]) ? $form["corr_prev_action"] : '';
			$corr_prev_proposed_by = isset($form["corr_prev_proposed_by"]) ? $form["corr_prev_proposed_by"] : '';
			$corr_prev_target_start_date = isset($form["corr_prev_target_start_date"]) ? $form["corr_prev_target_start_date"] : '';
			$corr_prev_target_end_date = isset($form["corr_prev_target_end_date"]) ? $form["corr_prev_target_end_date"] : '';
	
			$corr_prev_action = trim($corr_prev_action);
			if(!empty($corr_prev_action)) {
				if(strlen($corr_prev_action) < MIN_CPAR_CORR_PREV_ACTION || strlen($corr_prev_action) > MAX_CPAR_CORR_PREV_ACTION) {
					array_push($errors, "Corrective/Preventive (Continual Improvement) action should be " . MIN_CPAR_CORR_PREV_ACTION . " to " . MAX_CPAR_CORR_PREV_ACTION . " characters.");
				}
			}
		
			if(!(empty($corr_prev_target_start_date) || empty($corr_prev_target_end_date))) {
				if(!isCorrectDateRange($corr_prev_target_start_date, $corr_prev_target_end_date)) {
					array_push($errors, 'Invalid date range. Target End Date should not be earlier than Target Start Date.');		
				}
			}
	
		}

		return $errors;
	}

	private function processForm($form, $isDraft) {
		$result = new stdClass();
		$logged_in_id = $this->session->userdata('loggedIn');
		$existing = $this->addressee_fields_model->isExisting($form['id']);

		$addressee_fields = $this->convertForm($form);
		$addressee_fields->updated_by = $logged_in_id;
		$addressee_fields->updated_date = date('Y-m-d H:i:s');

		if(isset($form['ap_upload_uid']) && $form['id']) {
			transferActionPlanAttachments($form['ap_upload_uid'], $form['id'], isset($form['ap_attachments']) ? $form['ap_attachments'] : array());
		}

		if(!$isDraft) {
					
			//if submit, then change sub_status of cpar in cpar_main table
			$cpar = new stdClass();
			$cpar->sub_status = CPAR_MINI_STATUS_S2_2B;
			$cpar->updated_by = $logged_in_id;
			$cpar->updated_date = date('Y-m-d H:i:s');
			$this->cpar_model->updateCpar($cpar, $form['id']);
			$cpar_no = $form['id'];
			$this->email->processEmails($cpar_no, '2', 'B');
			
			#LOG HERE
			$log = new stdClass();
			$log->cpar_no = $cpar_no;
			$log->action = LOG_MOVED_TO_FOR_TL_REVIEW;
			$log->stage = CPAR_STAGE_2;
			$log->sub_status = $cpar->sub_status;
			$log->remarks = '';
			$log->notes = json_encode($form);
			$log->created_by = $logged_in_id;
			$log->created_date = date('Y-m-d H:i:s');
			$this->audit_log_model->insertLog($log);
			$this->success_msg = MSG_S2_FOR_TL_REVIEW;
		}

		if($existing = null || empty($existing)) {
			//insert new addressee_fields record
			$result = $this->addressee_fields_model->insertAddresseeFields($addressee_fields);
		} else {
			$result = $this->addressee_fields_model->updateAddresseeFields($addressee_fields);
		}
		
		//batch insert action plan details
		$tasks = finalizeTasks($form);
		$task_attachments = extractAttachments($form);
		if($tasks) {
			$return = $this->addressee_fields_model->batchInsertActionPlanDetails($tasks);
			
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
			$this->addressee_fields_model->deleteActionPlanDetailsByIds($tasks_to_delete);
			foreach($tasks_to_delete as $delete_task) {
				removeTaskAttachments($delete_task, $form['id']);
			}
		}

		if(!$isDraft) {
			$this->addressee_fields_model->update_proposed_by_date($cpar_no);
		}

		return $result;
	}

	private function processReviewForm($form) {
		$l_id = $this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($l_id);
		
		$result = new stdClass();
		
		$cpar = new stdClass();
		$old_cpar = $this->cpar_model->getCpar($form['id']);

		$status = '';
		$sub_status = '';
		
		$b_send_invalid_emails = FALSE;
		$b_send_approved_emails = FALSE;

		$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
		if((int)$review_action == REVIEW_ACTIONS_S2_MARK_APPR) {
			$status = CPAR_STAGE_3;
			$sub_status = CPAR_MINI_STATUS_S3_3A;
			$b_send_approved_emails = TRUE;
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S2_MARK_APPR;
			
		} else if((int)$review_action == REVIEW_ACTIONS_S2_MARK_INV) {
			$status = CPAR_STAGE_2;
			$sub_status = CPAR_MINI_STATUS_S2_2A2;

			$cpar->pb_user = $l_id;
			$cpar->pb_date = date('Y-m-d H:i:s');
			$cpar->pb_remarks = isset($form["tl_review_remarks"]) ? fix_output2($form["tl_review_remarks"]) : '';
			
			$b_send_invalid_emails = TRUE;
			
			$this->success_msg = MSG_REVIEW_ACTIONS_S2_MARK_INV;
			
		}

		$cpar->status = $status;
		$cpar->sub_status = $sub_status;
		$cpar->updated_by = $l_id;
		$cpar->updated_date = date('Y-m-d H:i:s');

		$result = $this->cpar_model->updateCpar($cpar, $form['id']);

		$cpar_no = $form['id'];

		if($result && isset($form['id']) && $old_cpar && $b_send_invalid_emails) {
			$this->email->processEmails($cpar_no, '2', 'BX');
		}
		
		if($result && isset($form['id']) && $old_cpar && $b_send_approved_emails) {
			$this->email->processEmails($cpar_no, '3', 'A');
		}

		if(!($review_action == null && empty($review_action))) {
			//derive role
			$role = '';
			if((int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG) {
				$role = REVIEW_ROLE_ADMIN;
			} else if($l_id == $old_cpar->assigned_ims) {
				$role = REVIEW_ROLE_IMS;
			} else if($l_id == $old_cpar->addressee_team_lead) {
				$role = REVIEW_ROLE_ADDRESSEE_TEAM_LEAD;
			}

			$rev_hist = new stdClass();
			$rev_hist->cpar_no = $form['id'];
			$rev_hist->action = $review_action;
			$rev_hist->role = $role;
			$rev_hist->stage = $old_cpar->status;
			$rev_hist->sub_status = $old_cpar->sub_status;
			$rev_hist->remarks = isset($form["tl_review_remarks"]) ? fix_output2($form["tl_review_remarks"]) : '';
			$rev_hist->reviewed_by = $l_id;

			$rev_hist->reviewed_date = date('Y-m-d H:i:s');
			$rev_hist->due_date = formatDateForDB($old_cpar->date_due);

			$this->review_history_model->insertReviewHistory($rev_hist);
			
			#LOG HERE
			$log = new stdClass();
			$log->cpar_no = $cpar_no;
			$log->action = $this->audit_log_model->get_action($review_action);
			$log->stage = CPAR_STAGE_2;
			$log->sub_status = $old_cpar->sub_status;
			$log->remarks = isset($form["tl_review_remarks"]) ? $form["tl_review_remarks"] : '';
			$log->notes = json_encode($form);
			$log->created_by = $l_id;
			$log->created_date = date('Y-m-d H:i:s');
			$this->audit_log_model->insertLog($log);
		}

		return $result;
	}

	private function convertForm($form) {
		$addr_fields = new stdClass();

		$this->trimForm($form);

		//main fields
		$addr_fields->cpar_no = isset($form['id']) ? $form['id'] : '';

		//remedial action details
		$addr_fields->rad_action = isset($form['immediate_remedial_action']) ? fix_output2($form['immediate_remedial_action']) : '';		
		$addr_fields->rad_implemented_by = normalizeSelect2Value(isset($form['implemented_by']) ? $form['implemented_by'] : NULL);
		$addr_fields->rad_implemented_date = (isset($form['date_implemented']) && $form['date_implemented'] != '') ? formatDateForDB($form['date_implemented']) : NULL;

		//root cause analysis
		$has_others = false;
		$tools_used = isset($form["tools_used"]) ? $form["tools_used"] : '';
		if(!empty($tools_used)) {
			$addr_fields->rca_tools = implode(', ', $tools_used);
			foreach ($tools_used as $tool) {
				if(intval($tool) == TOOLS_USED_OTHERS_ID) {
					$addr_fields->rca_tools_others = isset($form["other_tools_used"]) ? fix_output2($form["other_tools_used"]) : '';
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
		
		$addr_fields->rca_details = isset($form['rca_details']) ? fix_output2($form['rca_details']) : NULL;

		$addr_fields->rca_investigated_by = (isset($form['investigated_by']) && $form['investigated_by'] != '') ? normalizeSelect2Value($form['investigated_by']) : NULL;
		$addr_fields->rca_investigated_date_started = (isset($form['date_investigation_started']) && $form['date_investigation_started'] != '') ? formatDateForDB($form['date_investigation_started']) : NULL;
		$addr_fields->rca_investigated_date_ended = (isset($form['date_investigation_ended']) && $form['date_investigation_ended'] != '') ? formatDateForDB($form['date_investigation_ended']) : NULL;

		//corrective/preventive action details
		$addr_fields->action = isset($form['corr_prev_action']) ? fix_output2($form['corr_prev_action']) : '';
		$addr_fields->proposed_by = normalizeSelect2Value(isset($form['corr_prev_proposed_by']) ? $form['corr_prev_proposed_by'] : '');
		$addr_fields->target_start_date = (isset($form['corr_prev_target_start_date']) && $form['corr_prev_target_start_date'] != '') ? formatDateForDB($form['corr_prev_target_start_date']) : NULL;
		$addr_fields->target_end_date = (isset($form['corr_prev_target_end_date']) && $form['corr_prev_target_end_date'] != '') ? formatDateForDB($form['corr_prev_target_end_date']) : NULL;

		return $addr_fields;
	}

	private function normalizeSelect2Value($str) {
		$val = $str;

		if(!ctype_digit($str)) {
			$obj = json_decode($str);
			if($obj != null) {
				$val = $obj[0]->id;
			}
		}

		return $val;
	}

	private function getBlankAddresseeFields() {
		$addressee_fields = new stdClass();
		$addressee_fields->cpar_no = '';
		$addressee_fields->accomplish_by = '';

		$addressee_fields->rad_action = '';
		$addressee_fields->rad_implemented_by = '';
		$addressee_fields->rad_implemented_date = '';

		$addressee_fields->rca_tools = '';
		$addressee_fields->rca_tools_others = '';
		$addressee_fields->rca_investigated_by = '';
		$addressee_fields->rca_investigated_date_started = '';
		$addressee_fields->rca_investigated_date_ended = '';
		$addressee_fields->rca_details = '';

		$addressee_fields->action = '';
		$addressee_fields->proposed_by = '';
		$addressee_fields->target_start_date = '';
		$addressee_fields->target_end_date = '';

		return $addressee_fields;
	}

	private function trimForm(&$form) {
		foreach ($form as $key => $val) {
			if(is_string($val)) {
				$form[$key] = trim($val);
			}
		}
	}
	
	private function removeUploadedFiles($cpar_no, $stage, $removed_uploads) {
		if($removed_uploads) {
			foreach ($removed_uploads as $file) {
			    $final_name = UPLOAD_PATH . $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR . $file;
			    if(file_exists($final_name)) {
			        unlink($final_name);
			    }
			}
			
		}
	}

	private function validateTlReview($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		$is_tl_review = isset($form["is_tl_review"]) ? $form["is_tl_review"] : '';
		
		$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
		
		if(!($is_tl_review == null && empty($is_tl_review))) {
			if(empty($review_action)) {
				array_push($errors, 'Please choose a review action.');
			} else {
				$remarks = isset($form["tl_review_remarks"]) ? fix_output2($form["tl_review_remarks"]) : '';
				if(empty($remarks)) {
					if(empty($review_action) || (int)$review_action == REVIEW_ACTIONS_S2_MARK_INV) {
						array_push($errors, "Remarks (Team Leader Review)" . $is_required_suffix);	
					}
				} else {
					if(strlen($remarks) < MIN_CPAR_REMARKS || strlen($remarks) > MAX_CPAR_REMARKS) {
						array_push($errors, "Remarks (Team Leader Review) should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
					}
				}
			}
		}

		return $errors;
	}

	private function canEdit($cpar, &$can_edit_cpar_info, &$can_edit_corr_actions, &$can_edit_actions) {
		$can_edit_cpar_info = false;
		$can_edit_corr_actions = false;
		$can_edit_actions = false;

		$l_id = (int)$this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($l_id);

		if($cpar->status == CPAR_STAGE_2) {
			$admin_access = ((int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0 || strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A2) == 0) {
				//assigned IMS and admin can edit cpar_info and corrective actions
				if((int)$cpar->assigned_ims == $l_id || $admin_access) {
					$can_edit_cpar_info = true;
					$can_edit_corr_actions = true;
				}

				//addressee can edit corrective actions
				if((int)$cpar->addressee == $l_id) {
					$can_edit_corr_actions = true;
				}
			} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2B) == 0) {
				//assigned IMS and admin can edit cpar_info, corrective actions and actions
				if((int)$cpar->assigned_ims == $l_id || $admin_access) {
					$can_edit_cpar_info = true;
					$can_edit_corr_actions = true;
					$can_edit_actions = true;
				}
			}
		}
	}
	
	private function validate_update_task_addressee_permissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		}

		return $errors;
	}
	
	private function validate_update_task_remarks($form) {
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
	
	private function validate_update_task_title($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		$task_id = isset($form["task_id"]) ? $form["task_id"] : '';
		$task_id = trim($task_id);
		if(empty($task_id)) {
			array_push($errors, "Task ID" . $is_required_suffix);
		}

		$new_task = isset($form["new_task"]) ? $form["new_task"] : '';
		$new_task = trim($new_task);
		if(empty($new_task)) {
			array_push($errors, "Task" . $is_required_suffix);
		} else {
			if(strlen($new_task) < MIN_CPAR_TASK || strlen($new_task) > MAX_CPAR_TASK) {
				array_push($errors, "Task should be " . MIN_CPAR_TASK . " to " . MAX_CPAR_TASK . " characters.");
			}
		}

		return $errors;
	}
		
	public function update_addressee_remarks() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validate_update_task_addressee_permissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validate_update_task_remarks($form);
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
	
	public function update_addressee_task() {
		
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
	
			$cpar_no = $form['cpar_no'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
	
			$errors = $this->validate_update_task_addressee_permissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validate_update_task_title($form);
			}

		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}
	
		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$new_task = fix_output2(trim($form["new_task"]));
			$task_id = $form["task_id"];
			$apd = new stdClass();
			$apd->task = $new_task;

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
}