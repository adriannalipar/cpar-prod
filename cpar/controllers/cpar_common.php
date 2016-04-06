<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cpar_common extends MY_Controller {

	var $pdf_users_list = array();

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->library('my_sessions');
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

	public function sessionUpdate() {
		$this->my_sessions->regenerate_id();
	}

	public function saveWholeCpar() {
	
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = $this->input->post();
			
			$cpar_no = $form['id'];
			$cpar = $this->cpar_model->getCpar($cpar_no);
			$errors = $this->validateSavePermissions($cpar);
	
			if(empty($errors)) {
				$errors = $this->validateForm($form, $cpar->type, $cpar->status);
			}
	
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}
	
		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			//remove uploaded files deleted by user
			$this->removeUploads($cpar_no);

			//upload files
			$proceed = $this->uploadFiles($cpar_no);

			if($proceed === TRUE) {
				$result = $this->processForm($form);
			} else {
				$result = $proceed;
			}
		}

		if($result != null && isset($result->success) && $result->success) {
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

	private function validateSavePermissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else {
			$access = $this->users_model->getUserAccessRights($l_id);
			$allowed_access = ((int)$l_id == $cpar->assigned_ims || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if(!$allowed_access) {
				array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
			}
		}

		if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		}

		return $errors;
	}

	private function validateForm($form, $type, $stage) {
		$errors = array();

		$errors = $this->validateCparInfoForm($form);
		$errors = array_merge($errors, $this->validateCparCorrActions($form, $type, $stage));

		return $errors;
	}

	private function validateCparInfoForm($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		#form values
		$id = isset($form["id"]) ? $form["id"] : '';
		$date_filed = isset($form["date_filed"]) ? $form["date_filed"] : '';
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

		$date_filed = trim($date_filed);
		if(empty($title)) {
			array_push($errors, "Date Filed" . $is_required_suffix);
		}

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
		if(!($id == null || empty($id)) && !$this->cpar_model->isNotDeleted($id)) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		}

		//validate IMS review fields
		$m_status = isset($form["m_status"]) ? $form["m_status"] : '';
		$is_ims_review = isset($form["is_ims_review"]) ? $form["is_ims_review"] : '';
		if(!($is_ims_review == null && empty($is_ims_review)) && strcmp($m_status, CPAR_SUBMIT_S1_PROCEED) == 0) {
			$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
			if(empty($review_action)) {
				array_push($errors, 'Please choose a review action.');
			} else {
				if((int)$review_action == REVIEW_ACTIONS_MARK_REV) {
					$reviewed_by = isset($form["reviewed_by"]) ? $form["reviewed_by"] : '';
					$next_due_date = isset($form["next_due_date"]) ? $form["next_due_date"] : '';

					if(empty($reviewed_by)) {
						array_push($errors, "IMS assignee" . $is_required_suffix);
					}

					if(empty($next_due_date)) {
						array_push($errors, "Submit response by" . $is_required_suffix);
					}
				} else if((int)$review_action == REVIEW_ACTIONS_PUSH_BACK) {

				} else if((int)$review_action == REVIEW_ACTIONS_MARK_INV) {

				} else {
					array_push($errors, 'Please choose a review action.');
				}
			}

			$remarks = isset($form["remarks"]) ? $form["remarks"] : '';
			if(empty($remarks)) {
				array_push($errors, "Remarks" . $is_required_suffix);
			} else {
				if(strlen($remarks) < MIN_CPAR_REMARKS || strlen($remarks) > MAX_CPAR_REMARKS) {
					array_push($errors, "Remarks should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
				}
			}
		}

		return $errors;
	}

	private function validateCparCorrActions($form, $type, $stage) {
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
	
			$details = isset($form["rca_details"]) ? $form["rca_details"] : '';
			$investigated_by = isset($form["investigated_by"]) ? $form["investigated_by"] : '';
			$date_investigation_started = isset($form["date_investigation_started"]) ? $form["date_investigation_started"] : '';
			$date_investigation_ended = isset($form["date_investigation_ended"]) ? $form["date_investigation_ended"] : '';

			if(intval($stage) != 2) {
				$details = trim($details);
				if(empty($details)) {
					array_push($errors, "Details / Result of Root Cause Analysis" . $is_required_suffix);
				}
	
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
			} else {
				if(!(empty($date_investigation_started) || empty($date_investigation_ended))) {
					if(!isCorrectDateRange($date_investigation_started, $date_investigation_ended)) {
						array_push($errors, 'Invalid date range. Date Investigation (Ended) should not be earlier than Date Investigation (Started).');
					}
				}
			}

		}

		//common fields
		$corr_prev_action = isset($form["corr_prev_action"]) ? $form["corr_prev_action"] : '';
		$corr_prev_proposed_by = isset($form["corr_prev_proposed_by"]) ? $form["corr_prev_proposed_by"] : '';
		$corr_prev_target_start_date = isset($form["corr_prev_target_start_date"]) ? $form["corr_prev_target_start_date"] : '';
		$corr_prev_target_end_date = isset($form["corr_prev_target_end_date"]) ? $form["corr_prev_target_end_date"] : '';
		
		if(intval($stage) != 2) {
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
			$task_count = isset($form["task_count"]) ? $form["task_count"] : 0;
			if(empty($tasks) && empty($tasks_to_add)) {
				if(!isset($form["task_count"]) || (isset($form["task_count"]) && $form["task_count"] <= 0)) {
					array_push($errors, "Please input at least 1 Corrective/Preventive (Continual Improvement) Action Detail.");
				}
			} else {
				if(!isset($form["task_count"]) || (isset($form["task_count"]) && $form["task_count"] <= 0)) {
					$arr = json_decode($tasks);
					$arr_to_add = json_decode($tasks_to_add);
					if((!$arr || $arr == null || empty($arr)) && (!$arr_to_add || $arr_to_add == null || empty($arr_to_add))) {
						array_push($errors, "Please input at least 1 Corrective/Preventive (Continual Improvement) Action Detail.");
					}
				}
			}
		} else {
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

	private function removeUploads($cpar_no) {
		//removed uploads if not empty
		$removed_uploads = $this->input->post('removed_uploads');
	    $removed_rad_uploads = $this->input->post('removed_rad_uploads');
	    $removed_rca_uploads = $this->input->post('removed_rca_uploads');

	    if($removed_uploads && !empty($removed_uploads)) {
	    	$removed_uploads = json_decode($removed_uploads);
	    }

	    if($removed_rad_uploads && !empty($removed_rad_uploads)) {
	    	$removed_rad_uploads = json_decode($removed_rad_uploads);
	    }

	    if($removed_rca_uploads && !empty($removed_rca_uploads)) {
	    	$removed_rca_uploads = json_decode($removed_rca_uploads);
	    }

	    $this->removeUploadedFiles($cpar_no, CPAR_STAGE_1, $removed_uploads);
	    $this->removeUploadedFiles($cpar_no, UPLOAD_2RAD_PREFIX, $removed_rad_uploads);
	    $this->removeUploadedFiles($cpar_no, UPLOAD_2RCA_PREFIX, $removed_rca_uploads);
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

	private function uploadFiles($cpar_no) {
		$success = true;

		if(!empty($_FILES)) {
			//check if upload folder exists
	   		if (!is_dir(UPLOAD_PATH)) {
			    mkdir(UPLOAD_PATH);
			}

			$upload = true;
			$rad_upload = true;
			$rca_upload = true;

    		$result = new stdClass();
    		$result->success = false;
			$result->errors = array();

			if(isset($_FILES['attachments']) && !empty($_FILES['attachments'])) {
				$this->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no, CPAR_STAGE_1, 'attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $upload = $this->upload->do_multi_upload('attachments');
			    
			    if(!$upload) {
				    array_push($result->errors, $this->upload->display_errors('',''));
			    }
			}

			if($upload && isset($_FILES['rad_attachments']) && !empty($_FILES['rad_attachments'])) {
				$this->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RAD_PREFIX, 'rad_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $rad_upload = $this->upload->do_multi_upload('rad_attachments');
			    
			    if(!$rad_upload) {
				    array_push($result->errors, $this->upload->display_errors('',''));
			    }
			}

	    	if($upload && $rad_upload && isset($_FILES['rca_attachments']) && !empty($_FILES['rca_attachments'])) {
				$this->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no, UPLOAD_2RCA_PREFIX, 'rca_attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));
			    $rca_upload = $this->upload->do_multi_upload('rca_attachments');

			    if(!$rca_upload) {
				    array_push($result->errors, $this->upload->display_errors('',''));
			    }
			}

	    	if(!$upload || !$rad_upload || !$rca_upload) {
	    		$success = false;
	    	}
		}
		if(!$success) {
			return $result;
		} else {
			return $success;
		}
	}

	private function processForm($form) {
		$result = new stdClass();
		$l_id = $this->session->userdata('loggedIn');

		//save cpar_main record
		$cpar = $this->convertFormToCpar($form);
		$cpar->updated_by = $l_id;
		$cpar->updated_date = date('Y-m-d H:i:s');
		$result = $this->cpar_model->updateCpar($cpar, $form['id']);
		
		//save addressee_fields record
		$addressee_fields = $this->convertFormToAddresseeFields($form);
		$addressee_fields->updated_by = $l_id;
		$addressee_fields->updated_date = date('Y-m-d H:i:s');
		$result = $this->addressee_fields_model->updateAddresseeFields($addressee_fields);
		
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
		
		$this->addressee_fields_model->update_proposed_by_date($form['id']);
		
		if(isset($form['ap_upload_uid']) && $form['id']) {
			transferActionPlanAttachments($form['ap_upload_uid'], $form['id'], isset($form['ap_attachments']) ? $form['ap_attachments'] : array());
		}
		
		#LOG HERE
		$cpar_no = $form['id'];
		$old_cpar = $this->cpar_model->getCpar($cpar_no);
	
		$log = new stdClass();
		$log->cpar_no = $cpar_no;
		$log->action = LOG_UPDATED_CPAR;
		$log->stage = $old_cpar->status;
		$log->sub_status = $old_cpar->sub_status;
		$log->remarks = '';
		$log->notes = json_encode($form);
		$log->created_by = $l_id;
		$log->created_date = date('Y-m-d H:i:s');
		$this->audit_log_model->insertLog($log);

		return $result;
	}

	private function convertFormToCpar($form) {
		$cpar = new stdClass();

		$cpar->id = isset($form['id']) ? $form['id'] : '';
		$cpar->date_filed = $form['date_filed'];
		$cpar->title = $form['title'];
		$cpar->raised_as_a_result_of = $form['result_of'];
		$cpar->raised_as_a_result_of_others = (isset($form['result_of_others']) && !empty($form['result_of_others'])) ? $form['result_of_others'] : NULL;
		$cpar->process = $form['process'];
		$cpar->details = fix_output2($form['details']);
		$cpar->justification = fix_output2($form['justification']);
		$cpar->references = fix_output2($form['references']);
		
		if(intval($cpar->raised_as_a_result_of) != RAISED_AS_A_RESULT_OF_OTHERS_ID) {
			$cpar->raised_as_a_result_of_others = NULL;
		}


		$cpar->addressee = normalizeSelect2Value(isset($form['at_name']) ? $form['at_name'] : '');
		$cpar->addressee_team = $form['at_team'];
		$cpar->addressee_team_lead = normalizeSelect2Value(isset($form['at_team_lead']) ? $form['at_team_lead'] : '');

		$cpar->requestor = normalizeSelect2Value(isset($form['req_name']) ? $form['req_name'] : '');
		$cpar->requestor_team = $form['req_team'];
		$cpar->requestor_team_lead = normalizeSelect2Value(isset($form['req_team_lead']) ? $form['req_team_lead'] : '');

		return $cpar;
	}

	private function convertFormToAddresseeFields($form) {
		$addr_fields = new stdClass();

		$this->trimForm($form);

		//main fields
		$addr_fields->cpar_no = isset($form['id']) ? $form['id'] : '';

		//remedial action details
		$addr_fields->rad_action = isset($form['immediate_remedial_action']) ? fix_output2($form['immediate_remedial_action']) : '';		
		$addr_fields->rad_implemented_by = normalizeSelect2Value(isset($form['implemented_by']) ? $form['implemented_by'] : NULL);
		$addr_fields->rad_implemented_date = isset($form['date_implemented']) ? formatDateForDB($form['date_implemented']) : NULL;

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

	private function trimForm(&$form) {
		foreach ($form as $key => $val) {
			if(is_string($val)) {
				$form[$key] = trim($val);
			}
		}
	}

	//PDF related==========================================================
	public function exportToPdf() {
		$this->load->helper('file');
		$this->load->helper('dompdf');

		$cpar = null;
		$errors = array();
		$cpar_no = $this->input->get("cpar_no");

		if($cpar_no == null || empty($cpar_no)) {
			array_push($errors, 'Missing CPAR no.');
		} else {
			//validate
			$cpar = $this->cpar_model->getCpar($cpar_no);
			$errors = $this->validateExportToPdf($cpar);
		}

		if(empty($errors)) {
			//review history
			$review_history = $this->review_history_model->getReviewHistory($cpar_no);

			$data['supplementary'] = $this->getSupplementaryData($cpar, $review_history);
		
			$this->reformatCpar($cpar);
			$data['cpar'] = $cpar;

			//addressee fields
			$addressee_fields = $this->addressee_fields_model->getAddresseeFields($cpar_no);
			if($addressee_fields) {
				$this->reformatAddresseeFields($addressee_fields);
				$data['a_fields'] = $addressee_fields;
			} else {
				$a_fields = new stdClass();
				$a_fields->rad_implemented_date = '';
				$a_fields->rad_action = '';
				$a_fields->rad_implemented_by_name = '';
				$a_fields->rca_tools = '';
				$a_fields->rca_details = '';
				$a_fields->rca_investigated_by_name = '';
				$a_fields->rca_investigated_date_started = '';
				$a_fields->rca_investigated_date_ended = '';
				$a_fields->action = '';
				$a_fields->proposed_by_name = '';
				$a_fields->proposed_by_date = '';
				$a_fields->target_start_date = '';
				$a_fields->target_end_date = '';
				
				$data['a_fields'] = $a_fields;
			}

			//tasks
			$tasks = $this->addressee_fields_model->getActionPlanDetails($cpar_no);
			formatTasksForRender($tasks);
			$data['tasks'] = $tasks;

			if((int)$cpar->status == CPAR_STAGE_4 || (int)$cpar->status == CPAR_STAGE_5) {
				//get follow up history
				$ff_up_history = $this->ff_up_history_model->getReviewHistory($cpar_no);
				$data['ff_up_history'] = $ff_up_history;
			}

			$data['review_history'] = $review_history;

			$data['script'] = '
				if ( isset($pdf) ) {

				  $font = Font_Metrics::get_font("Calibri");
				  $size = 8;
				  $color = array(0,0,0);
				  $text_height = Font_Metrics::get_font_height($font, $size);
				
				  $foot = $pdf->open_object();
				  
				  $w = $pdf->get_width();
				  $h = $pdf->get_height();
				
				  // Draw a line along the bottom
				  $y = $h - $text_height - 24;
				  $pdf->line(32, $y, $w - 32, $y, $color, 1);
				
				  $pdf->close_object();
				  $pdf->add_object($foot, "all");
				
				  $text = "CORRECTIVE / PREVENTIVE (CONTINUAL IMPROVEMENT) ACTION REQUEST (CPAR)         CPAR#: '.$cpar_no.'"; 
				  $text2 = "Page {PAGE_NUM} of {PAGE_COUNT}";
				
				  // Center the text
				  $width = Font_Metrics::get_text_width("Page 1 of 2", $font, $size);
				  $pdf->page_text(32, $y, $text, $font, $size, $color);
				  $pdf->page_text($w-32-$width, $y, $text2, $font, $size, $color);
				  
				}';

			$html = $this->load->view('export_pdf/export_cpar_pdf', $data, true);
			pdf_create($html, $cpar_no);
			#echo $html;
		} else {
			$this->session->set_userdata('search_page_errors', $errors);
			redirect('http://' . base_url() . 'cpar/');
		}
	}
	
	public function getSupplementaryData($cpar, $review_history) {
		
		#initialize
		$supplementary = new stdClass();
		
		#Stage 3 - IMS Review
		$supplementary->immediate_remedial_action_verified_by = '';
		$supplementary->immediate_remedial_action_verified_date = '';
		
		#Stage 2B
		$supplementary->approved_by_team_leader = '';
		$supplementary->approved_by_team_leader_date = '';
		
		#Stage 3 - IMS Review
		$supplementary->corrprev_actions_verified_by = '';
		$supplementary->corrprev_actions_verified_date = '';
		
		#currently Stage 3C(?)
		$supplementary->followup_implementation_effective = NULL;
		$supplementary->followup_implementation_effective_remarks = '';
		$supplementary->followup_implementation_effective_by = '';
		$supplementary->followup_implementation_effective_date = '';
		
		#currently stage 4a
		$supplementary->verification_of_effectiveness = NULL;
		$supplementary->verification_of_effectiveness_remarks = '';
		$supplementary->verification_of_effectiveness_by = '';
		$supplementary->verification_of_effectiveness_date = '';
		
		#currently stage 4b
		$supplementary->mr_effectiveness = NULL;
		$supplementary->mr_effectiveness_remarks = '';
		$supplementary->mr_effectiveness_by = '';
		$supplementary->mr_effectiveness_date = '';

		$pb_action_array = array(REVIEW_ACTIONS_PUSH_BACK, REVIEW_ACTIONS_S3_PUSH_BACK, REVIEW_ACTIONS_S4_PUSH_BACK, REVIEW_ACTIONS_S4_PUSH_BACK_4A);
		
		$last_push_back_action = NULL;
		$last_action = NULL;
		#get last push back action
		foreach($review_history as $entry) {
		
			if(!$last_action) {
				$last_action = $entry;
			}
		
			if(in_array(intval($entry['action']), $pb_action_array)) {
				$last_push_back_action = $entry;
				break;
			}
		} 
				
		$this->report_get_stage2b_data($supplementary, $cpar, $review_history);
		$this->report_get_stage3a_data($supplementary, $cpar, $review_history);
		
		switch(intval($cpar->status)) {
			case CPAR_STAGE_2:
				if(intval($last_push_back_action['stage']) == CPAR_STAGE_4) {
					$supplementary->verification_of_effectiveness = FALSE;
					$supplementary->verification_of_effectiveness_remarks = $last_push_back_action['remarks'];
					$supplementary->verification_of_effectiveness_by = $last_push_back_action['reviewed_by'];
					$supplementary->verification_of_effectiveness_date = $last_push_back_action['reviewed_date'];
				} elseif(intval($last_push_back_action['stage']) == CPAR_STAGE_3) {
					$supplementary->followup_implementation_effective = FALSE;
					$supplementary->followup_implementation_effective_remarks = $last_push_back_action['remarks'];
					$supplementary->followup_implementation_effective_by = $last_push_back_action['reviewed_by'];
					$supplementary->followup_implementation_effective_date = $last_push_back_action['reviewed_date'];
				}
								
				break;
			case CPAR_STAGE_3:
				if(intval($last_push_back_action['stage']) == CPAR_STAGE_4) {
					$supplementary->verification_of_effectiveness = FALSE;
					$supplementary->verification_of_effectiveness_remarks = $last_push_back_action['remarks'];
					$supplementary->verification_of_effectiveness_by = $last_push_back_action['reviewed_by'];
					$supplementary->verification_of_effectiveness_date = $last_push_back_action['reviewed_date'];
				}
				
				break;
			case CPAR_STAGE_4:
				if(intval($cpar->sub_status) == CPAR_MINI_STATUS_S4_4A || intval($cpar->sub_status) == CPAR_MINI_STATUS_S4_4A2) {
					
					$supplementary->followup_implementation_effective = TRUE;
					$this->get_followup_implementation_effective_data($supplementary, $cpar, $review_history);

					if(intval($last_push_back_action['stage']) == CPAR_STAGE_4 && intval($last_action['stage']) == CPAR_STAGE_4) {

						$supplementary->mr_effectiveness = FALSE;
						$supplementary->mr_effectiveness_remarks = $last_push_back_action['remarks'];
						$supplementary->mr_effectiveness_by = $last_push_back_action['reviewed_by'];
						$supplementary->mr_effectiveness_date = $last_push_back_action['reviewed_date'];
					}
										
				} elseif(intval($cpar->sub_status) == CPAR_MINI_STATUS_S4_4B) {

					$supplementary->followup_implementation_effective = TRUE;
					$this->get_followup_implementation_effective_data($supplementary, $cpar, $review_history);
					
					$supplementary->verification_of_effectiveness = TRUE;
					$this->get_verification_of_effectiveness_data($supplementary, $cpar, $review_history);

				}
				break;
			case CPAR_STAGE_5:
				$supplementary->followup_implementation_effective = TRUE;
				$this->get_followup_implementation_effective_data($supplementary, $cpar, $review_history);
				
				$supplementary->verification_of_effectiveness = TRUE;
				$this->get_verification_of_effectiveness_data($supplementary, $cpar, $review_history);
				
				$supplementary->mr_effectiveness = TRUE;
				$this->get_mr_effectiveness_data($supplementary, $cpar, $review_history);
				
				break;
		}
		
		$this->convert_supplementary($supplementary);
		
		return $supplementary;
	}
	
	private function report_get_stage2b_data(&$supplementary, $cpar, $review_history) {
		if(intval($cpar->status) > CPAR_STAGE_1 && intval($cpar->sub_status) > CPAR_MINI_STATUS_S2_2A2) {
			foreach($review_history as $entry) {
				if(intval($entry['stage']) == CPAR_STAGE_2 && intval($entry['action']) == REVIEW_ACTIONS_S2_MARK_APPR) {

					$supplementary->approved_by_team_leader = $entry['reviewed_by'];
					$supplementary->approved_by_team_leader_date = $entry['reviewed_date'];
					break;
				}
			}
		}
	}

	private function report_get_stage3a_data(&$supplementary, $cpar, $review_history) {
		if(intval($cpar->status) > CPAR_STAGE_2 && intval($cpar->sub_status) > CPAR_MINI_STATUS_S3_3A) {
			foreach($review_history as $entry) {
				if(intval($entry['stage']) == CPAR_STAGE_3 && intval($entry['action']) == REVIEW_ACTIONS_S3_MARK_REV) {

					$supplementary->immediate_remedial_action_verified_by = $entry['reviewed_by'];
					$supplementary->immediate_remedial_action_verified_date = $entry['reviewed_date'];
					$supplementary->corrprev_actions_verified_by = $entry['reviewed_by'];
					$supplementary->corrprev_actions_verified_date = $entry['reviewed_date'];
					
					break;
				}
			}
		}
	}
	
	private function get_followup_implementation_effective_data(&$supplementary, $cpar, $review_history) {
		foreach($review_history as $entry) {
			if(intval($entry['stage']) == CPAR_STAGE_3 && intval($entry['action']) == REVIEW_ACTIONS_S3_MARK_IMPL) {
		
				$supplementary->followup_implementation_effective_remarks = $entry['remarks'];
				$supplementary->followup_implementation_effective_by = $entry['reviewed_by'];
				$supplementary->followup_implementation_effective_date = $entry['reviewed_date'];

				break;
			}
		}
	}

	private function get_verification_of_effectiveness_data(&$supplementary, $cpar, $review_history) {
		foreach($review_history as $entry) {
			if(intval($entry['stage']) == CPAR_STAGE_4 && intval($entry['action']) == REVIEW_ACTIONS_S4_MARK_EFF) {
		
				$supplementary->verification_of_effectiveness_remarks = $entry['remarks'];
				$supplementary->verification_of_effectiveness_by = $entry['reviewed_by'];
				$supplementary->verification_of_effectiveness_date = $entry['reviewed_date'];

				break;
			}
		}
	}

	private function get_mr_effectiveness_data(&$supplementary, $cpar, $review_history) {
		foreach($review_history as $entry) {
			if(intval($entry['stage']) == CPAR_STAGE_4 && intval($entry['action']) == REVIEW_ACTIONS_S4_MARK_CLOSED) {
		
				$supplementary->mr_effectiveness_remarks = $entry['remarks'];
				$supplementary->mr_effectiveness_by = $entry['reviewed_by'];
				$supplementary->mr_effectiveness_date = $entry['reviewed_date'];

				break;
			}
		}
	}

	private function convert_supplementary(&$supplementary) {
		if($supplementary->immediate_remedial_action_verified_by != '') {
			$supplementary->immediate_remedial_action_verified_by = $this->get_user_name($supplementary->immediate_remedial_action_verified_by);
		}
		
		if($supplementary->immediate_remedial_action_verified_date != '') {
			$supplementary->immediate_remedial_action_verified_date = formatDateForDisplay($supplementary->immediate_remedial_action_verified_date);
		}
		
		if($supplementary->approved_by_team_leader != '') {
			$supplementary->approved_by_team_leader = $this->get_user_name($supplementary->approved_by_team_leader);
		}
		
		if($supplementary->approved_by_team_leader_date != '') {
			$supplementary->approved_by_team_leader_date = formatDateForDisplay($supplementary->approved_by_team_leader_date);
		}
		
		if($supplementary->corrprev_actions_verified_by != '') {
			$supplementary->corrprev_actions_verified_by = $this->get_user_name($supplementary->corrprev_actions_verified_by);
		}
		
		if($supplementary->corrprev_actions_verified_date != '') {
			$supplementary->corrprev_actions_verified_date = formatDateForDisplay($supplementary->corrprev_actions_verified_date);
		}

		if($supplementary->followup_implementation_effective_by != '') {
			$supplementary->followup_implementation_effective_by = $this->get_user_name($supplementary->followup_implementation_effective_by);
		}

		if($supplementary->followup_implementation_effective_date != '') {
			$supplementary->followup_implementation_effective_date = formatDateForDisplay($supplementary->followup_implementation_effective_date);
		}

		if($supplementary->verification_of_effectiveness_by != '') {
			$supplementary->verification_of_effectiveness_by = $this->get_user_name($supplementary->verification_of_effectiveness_by);
		}

		if($supplementary->verification_of_effectiveness_date != '') {
			$supplementary->verification_of_effectiveness_date = formatDateForDisplay($supplementary->verification_of_effectiveness_date);
		}

		if($supplementary->mr_effectiveness_by != '') {
			$supplementary->mr_effectiveness_by = $this->get_user_name($supplementary->mr_effectiveness_by);
		}
		
		if($supplementary->mr_effectiveness_date != '') {
			$supplementary->mr_effectiveness_date = formatDateForDisplay($supplementary->mr_effectiveness_date);
		}

	}
	
	private function get_user_name($id) {
		$name = '';
	
		if(!isset($this->pdf_users_list[$id])) {
			$user = $this->users_model->getUser($id);
			$this->pdf_users_list[$id] = $user;
			$name = $user->user_full_name;
		} elseif(isset($this->pdf_users_list[$id])) {
			$user = $this->pdf_users_list[$id];
			$name = $user->user_full_name;
		}
		
		return $name;
	}

	public function test() {
		$cpar_no = $this->uri->segment(3);
		$cpar = $this->cpar_model->getCpar($cpar_no);

		//cpar
		$this->reformatCpar($cpar);
		$data['cpar'] = $cpar;
		
		//addressee fields
		$addressee_fields = $this->addressee_fields_model->getAddresseeFields($cpar_no);
		$this->reformatAddresseeFields($addressee_fields);
		$data['a_fields'] = $addressee_fields;

		//tasks
		$tasks = $this->addressee_fields_model->getActionPlanDetails($cpar_no);
		formatTasksForRender($tasks);
		$data['tasks'] = $tasks;

		if((int)$cpar->status == CPAR_STAGE_4 || (int)$cpar->status == CPAR_STAGE_5) {
			//get follow up history
			$ff_up_history = $this->ff_up_history_model->getReviewHistory($cpar_no);
			$data['ff_up_history'] = $ff_up_history;
		}

		//review history
		$review_history = $this->review_history_model->getReviewHistory($cpar_no);
		$data['review_history'] = $review_history;

		$this->load->view('export_pdf/export_cpar_pdf', $data);
	}

	private function validateExportToPdf($cpar) {
		$errors = array();
		$l_id = $this->session->userdata('loggedIn');

		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) { 
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
/*
		} else if(!($cpar->status == CPAR_STAGE_3 || $cpar->status == CPAR_STAGE_4 || $cpar->status == CPAR_STAGE_5)) {
			array_push($errors, 'This CPAR cannot be exported to PDF in this stage.');
*/
		} else {
			/* 20150520 Export All 
			$access = $this->users_model->getUserAccessRights($l_id);
			$allowed_access = ((int)$l_id == $cpar->assigned_ims || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if(!$allowed_access) {
				array_push($errors, "Unable to export <b>{$cpar->id}</b>. You do not have access to this record.");
			}
			*/
		}

		return $errors;
	}

	private function reformatCpar(&$cpar) {
	
		$cpar->sub_status = 'Stage '.$cpar->status.' - '.getSubStatusName($cpar->sub_status);
		
		$cpar->date_due = formatDateForDisplay($cpar->date_due);
	
		//Type
		$cpar->type_name = getCparTypeName($cpar->type);

		//Raised as a result of
		$raaro_list = $this->cpar_model->getGenericList($this->cpar_model->tbl_raaro);
		foreach ($raaro_list as $raaro) {
			if((int)$raaro['id'] == $cpar->raised_as_a_result_of) {
				if(intval($cpar->raised_as_a_result_of) == RAISED_AS_A_RESULT_OF_OTHERS_ID) {
					$cpar->raised_as_a_result_of = $raaro['name'].' ('.$cpar->raised_as_a_result_of_others.')';
				} else {
					$cpar->raised_as_a_result_of = $raaro['name'];
				}

				break;
			}
		}

		//Process
		$process_list = $this->cpar_model->getGenericList($this->cpar_model->tbl_process);
		foreach ($process_list as $process) {
			if((int)$process['id'] == $cpar->process) {
				$cpar->process = $process['name'];
				break;
			}
		}

		//Addressee: Team Name
		$team_list = $this->cpar_model->getGenericList($this->cpar_model->tbl_team);
		foreach ($team_list as $team) {
			if((int)$team['id'] == $cpar->addressee_team) {
				$cpar->addressee_team = $team['name'];
				break;
			}
		}

		//Requestor: Team Name
		foreach ($team_list as $team) {
			if((int)$team['id'] == $cpar->requestor_team) {
				$cpar->requestor_team = $team['name'];
				break;
			}
		}
	}

	private function reformatAddresseeFields(&$a_fields) {
		//Date Implemented
		$rad_implemented_date = $a_fields->rad_implemented_date;
		if(strcmp($rad_implemented_date, NULL_DATE) != 0) {
			$a_fields->rad_implemented_date = formatDateForDisplay($rad_implemented_date);
		}
		
		//Tools Used
		$rca_tools = array();
		$tools = explode(', ', $a_fields->rca_tools);
		$tools_list = $this->cpar_model->getIdSortedGenericList($this->cpar_model->tbl_rca_tools);
		foreach ($tools as $tool) {
			foreach ($tools_list as $db_tool) {
				if((int)$db_tool['id'] == $tool) {
					if($tool == TOOLS_USED_OTHERS_ID) {
						array_push($rca_tools, $db_tool['name'] . "($a_fields->rca_tools_others)");
					} else {
						array_push($rca_tools, $db_tool['name']);
					}
				}
			}
		}
		$a_fields->rca_tools = implode(', ', $rca_tools);

		//Date Investigated (Started)
		$rca_investigated_date_started = $a_fields->rca_investigated_date_started;
		if(strcmp($rca_investigated_date_started, NULL_DATE) != 0) {
			$a_fields->rca_investigated_date_started = formatDateForDisplay($rca_investigated_date_started);
		}

		//Date Investigated (Ended)
		$rca_investigated_date_ended = $a_fields->rca_investigated_date_ended;
		if(strcmp($rca_investigated_date_ended, NULL_DATE) != 0) {
			$a_fields->rca_investigated_date_ended = formatDateForDisplay($rca_investigated_date_ended);
		}

		//Target Date (Start)
		$target_start_date = $a_fields->target_start_date;
		if(strcmp($target_start_date, NULL_DATE) != 0) {
			$a_fields->target_start_date = formatDateForDisplay($target_start_date);
		}

		//Target Date (End)
		$target_end_date = $a_fields->target_end_date;
		if(strcmp($target_end_date, NULL_DATE) != 0) {
			$a_fields->target_end_date = formatDateForDisplay($target_end_date);
		}

		$proposed_by_date = $a_fields->proposed_by_date;
		if(strcmp($proposed_by_date, NULL_DATE) != 0) {
			$a_fields->proposed_by_date = formatDateForDisplay($proposed_by_date);
		}

	}
	
	public function send_reminder() {
		$data = $this->input->post();
		
		$result = new stdClass();
		$result->success = true;
		$result->success_msg = "Email reminder successfully sent.";
		
		if(isset($data['cpar_no'])) {
			$this->email->generateReminderEmail($data['cpar_no']);
		}

		echo json_encode($result);
	}
}