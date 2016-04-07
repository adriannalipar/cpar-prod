<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cpar extends MY_Controller {

	var $success_msg = NULL;

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->library('upload');
		$this->load->library('uri');
		$this->load->library('email');

		$this->load->model('cpar_model');
		$this->load->model('review_history_model');
		$this->load->model('audit_log_model');

		$this->load->helper('cpar_helper');
		$this->load->helper('cpar_file_helper');
		
	}

	public function index($stuff = NULL) {
		$this->session->unset_userdata('search_filters');
		$this->searchP(false); //false - to render whole page
	}

	public function search() {
		$this->searchP(true); //true - to render partial view only
	}

	private function searchP($isPartial) {
		$filters = array();

		//for search and pagination only
		if($isPartial) {
			if($this->input->get('is_search')) {
				$filters = $this->input->get();
				$this->session->set_userdata('search_filters', $filters);
			} else if(!$this->input->get('change_tab')) {
				$filters = $this->session->userdata('search_filters');
			} else if($this->input->get('change_tab')) {
				$this->session->unset_userdata('search_filters');
			}
		}

		//for pagination (defaults)
		$rpp = $this->input->get('rpp');
		if(empty($rpp)) {
			$rpp = DEFAULT_TABLE_ROWS;
		}

		$pn = $this->input->get('pn');
		if(empty($pn)) {
			$pn = DEFAULT_PAGE_NUMBER;
		}

		//for sorting
		$sort_by = $this->input->get('sort_by');
		if(empty($sort_by)) {
			$sort_by = DEFAULT_CPAR_SORT_BY;
		}

		$sort = $this->input->get('sort');
		if(empty($sort)) {
			$sort = DEFAULT_CPAR_SORT;
		}

		//tab
		$tab = $this->input->get('tab');
		if(empty($tab)) {
			$tab = DEFAULT_CPAR_TAB;
		}

		$logged_in_id = $this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($logged_in_id);

		$count = $this->cpar_model->countSearchCparList($filters, $tab, $access);
		$cpar_list = $this->cpar_model->searchCparList($filters, $rpp, $pn, $sort_by, $sort, $tab, $access);
		$cpar_list = $this->finalizeCparList($cpar_list);

		$data['rpp'] = $rpp;
		$data['pn'] = $pn;
		$data['sort_by'] = $sort_by;
		$data['sort'] = $sort;

		$data['nor'] = count($cpar_list); //no of rows (this page only)
		$data['count'] = $count; //no of rows (whole)

		$data['tab'] = $tab;
		
		if(strcmp($rpp, RPP_ALL) != 0) {
			$data['pages'] = (int)($count / $rpp) + ($count % $rpp > 0 ? 1 : 0);

			//pages
			$set_no = (int)(($pn - 1) / PAGES_PER_SET) + 1;
			$data['min_page'] = (int)($set_no - 1) * PAGES_PER_SET + 1;

			$max_page = $set_no * PAGES_PER_SET;
			$data['max_page'] = $max_page > $data['pages'] ? $data['pages'] : $max_page;
		}

		$data['cpar_list'] = $cpar_list;

		if($isPartial) {
			$this->load->view('cpar/partial/_list', $data);
		} else {
			$data['screen_title'] = 'CPAR List';
		
			$this->addCommonData($data);
			$this->load->view('template/header', $data);
			$this->load->view('cpar/list', $data);
			$this->load->view('template/footer');
		}
	}

	public function exportToCSV() {
		$filters = $this->session->userdata('search_filters');

		$rpp = RPP_ALL;

		$pn = $this->input->get('pn');
		if(empty($pn)) {
			$pn = DEFAULT_PAGE_NUMBER;
		}

		//for sorting
		$sort_by = $this->input->get('sort_by');
		if(empty($sort_by)) {
			$sort_by = DEFAULT_CPAR_SORT_BY;
		}

		$sort = $this->input->get('sort');
		if(empty($sort)) {
			$sort = DEFAULT_CPAR_SORT;
		}

		//tab
		$tab = $this->input->get('tab');
		if(empty($tab)) {
			$tab = DEFAULT_CPAR_TAB;
		}

		$logged_in_id = $this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($logged_in_id);

		$cpar_list = $this->cpar_model->searchCparCsvList($filters, $rpp, $pn, $sort_by, $sort, $tab, $access);
		$cpar_list = $this->finalizeCparCSV($cpar_list, $tab);

		$this->download_send_headers("CPAR_Export_" . date("YmdHis") . ".csv");
		echo $this->array2csv($cpar_list);
		die();
	}

	public function create() {
		//get dropdown lists
		$data['team_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_team);
		$data['raaro_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_raaro);
		$data['process_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_process);

		$logged_in_id = $this->session->userdata('loggedIn');
		$this->addRequestorData($data, $logged_in_id);

		$data['screen_title'] = 'Create CPAR';

		$this->addCommonData($data);
		$this->load->view('template/header', $data);
		$this->load->view('cpar/create');
		$this->load->view('template/footer');
	}

	public function edit() {
		$errors = array();
		$cpar_no = $this->uri->segment(3);
		if($cpar_no == null || empty($cpar_no)) {
			$this->show_custom_404();
		} else {
			$cpar = $this->cpar_model->getCpar($cpar_no);

			if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG || $cpar->status != CPAR_STAGE_1) {
				array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
			} else {
				$errors = array();
				$errors = $this->validateEdit($cpar);

				if(empty($errors)) {
					$header_text_1 = 'Edit';
					$header_text_2 = 'CPAR';
					$header_text_3 = '';

					//change header text
					if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_DRAFT) == 0) {
						$header_text_3 = '(Draft Copy)';
					} else if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0) {
						$header_text_1 = 'Stage 1 CPAR';
						$header_text_2 = '- IMS Review';
					}
					
					$data['cpar'] = $cpar;
					$data['cpar_no'] = $cpar_no;
					$data['header_text_1'] = $header_text_1;
					$data['header_text_2'] = $header_text_2;
					$data['header_text_3'] = $header_text_3;

					//get uploaded files
					$data['filenames'] = $this->getFileNames($cpar_no, CPAR_STAGE_1);
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
				$view = 'cpar/view';
				if($this->canEdit($cpar)) {
					$view = 'cpar/edit';
				}
				
				$data['screen_title'] = 'Edit CPAR';

				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				$this->load->view($view);
				$this->load->view('template/footer');
			}
		}
	}

	public function view() {
		$errors = array();
		$cpar_no = $this->uri->segment(3);

		if($cpar_no == null || empty($cpar_no)) {
			$this->show_custom_404();
		} else {
			$cpar = $this->cpar_model->getCpar($cpar_no);

			if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
				array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
			} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_CLOSED) != 0) {
				array_push($errors, 'The CPAR No. you were trying to view is not yet closed.');
			} else {
				$data['cpar'] = $cpar;
				$data['cpar_no'] = $cpar_no;
			}

			if(empty($errors)) {
				$errors = $this->validateEdit($cpar);

				//get uploaded files
				$data['filenames'] = $this->getFileNames($cpar_no, CPAR_STAGE_1);
			}

			if(!empty($errors)) {
				$this->session->set_userdata('search_page_errors', $errors);
				redirect('http://' . base_url() . 'cpar/');
			} else {
				$data['header_text_1'] = 'View ';
				$data['header_text_2'] = 'CPAR';
				$data['header_text_3'] = '(Invalid)';

				//get dropdown lists
				$data['team_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_team);
				$data['raaro_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_raaro);
				$data['process_list'] = $this->cpar_model->getGenericList($this->cpar_model->tbl_process);
				
				$data['screen_title'] = 'View CPAR';
				
				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				$this->load->view('cpar/view');
				$this->load->view('template/footer');
			}
		}
	}

	public function save() {
		$result = new stdClass();
		$errors = array();
	
		if($this->input->post()) {
			$form = array_map('trim', $this->input->post());
			//skip validation when saving as draft
			if(strcmp($this->input->post('m_status'), CPAR_MINI_STATUS_DRAFT) == 0) {
				$errors = $this->validateDraft($form);
			} else {
				$errors = $this->validateForm($form);
			}
	
			if(empty($errors) && isset($form['id']) && !empty($form['id'])) {
				//validate permissions and state of CPAR if form is valid
				$cpar = $this->cpar_model->getCpar($form['id']);
				$errors = $this->validateEdit($cpar);
			}
	
		} else {
			$errors[] = 'Error posting data. Please contact system administrator.';
		}

		if(!empty($errors)) {
			$result->success = false;
			$result->errors = $errors;
		} else {
			$cpar_no = '';
			$cpar = $this->convertForm($form);

		   	//determine CPAR no.		   	
		   	if(empty($cpar->id)) {
		   		//get next ID (like a sequence in other DBMSs)
				$next_id = $this->cpar_model->getNextSeriesId($cpar->type);
				$cpar_no = $this->generateCparNumber($cpar->type, $next_id);
				$cpar->series_no = $next_id;
		   	} else {
		   		$cpar_no = $cpar->id;
		   	}

		   	//removed uploads if not empty
		    $removed_uploads = $this->input->post('removed_uploads');
		    if($removed_uploads && !empty($removed_uploads)) {
		    	$removed_uploads = json_decode($removed_uploads);
		    	$this->removeUploadedFiles($cpar_no, CPAR_STAGE_1, $removed_uploads);
		    }

		   	if(!empty($_FILES)) {
		   		//check if upload folder exists
		   		if (!is_dir(UPLOAD_PATH)) {
				    mkdir(UPLOAD_PATH);
				}

		   		//upload initialization / configuration
				$this->upload->initialize(array(
					'file_name' 	=> generateUploadFilenames($cpar_no, CPAR_STAGE_1, 'attachments', $_FILES),
			        'upload_path'   => UPLOAD_PATH,
			        'allowed_types' => UPLOAD_ALLOWED_TYPES
			    ));

		   		if($this->upload->do_multi_upload('attachments')) {
					$result = $this->processEditForm($cpar_no, $cpar, $form);
			    } else {
			    	$result->success = false;
					$result->errors = array();
					array_push($result->errors, $this->upload->display_errors('',''));
			    }
		   	} else {
		   		$result = $this->processEditForm($cpar_no, $cpar, $form);
		   	}
		   	
			if($result != null && $result->success) {
				$result->success = true;
				if($this->success_msg) {
					$result->success_msg = $this->success_msg;
				}
				if(empty($cpar->id)) {
					$result->id = $cpar_no;
			   	} else {
			   		$result->id = $cpar->id;
			   	}
				
			} else {
				$result->success = false;

				if(isset($result->error)) {
					$result->errors = array();
					array_push($result->errors, $result->error);
				}
			}
		}
		
		echo json_encode($result);
	}

	public function delete() {
		$success_msgs = array();
		$errors = array();

		$id = $this->input->post('id');

		if($id == null || empty($id)) {
			array_push($errors, 'Missing or incorrect CPAR No.');
		} else {
			if(empty($errors)) {
				$res = $this->cpar_model->deleteCpar($id);
				if($res) {
					array_push($success_msgs, 'CPAR successfully deleted.');
				} else {
					array_push($errors, 'Error encountered while trying to delete the CPAR.');
				}
			}
		}

		if(!empty($errors)) {
			$this->session->set_userdata('search_page_errors', $errors);
		} else if(!empty($success_msgs)) {
			$this->session->set_userdata('search_page_success_msgs', $success_msgs);
		}

		redirect('http://' . base_url() . 'cpar/');
	}

	public function getUsers() {
		$term = $this->input->get('term');
		$is_ims = false;
		
		if($this->input->get('is_ims') && $this->input->get('is_ims') == IMS_FLAG) {
			$is_ims = true;
		}
		
		$data = $this->users_model->getUsers($term, $is_ims);

		echo json_encode($data);
		exit;
	}

	//Ajax
	public function getAddrReqData() {
		$addr_req = new stdClass();
		$selected_id = $this->input->get('selected_id');

		if(empty($selected_id)) {
			$addr_req->team_id = '';
			$addr_req->team = '';
			$addr_req->team_lead_id = '';
			$addr_req->team_lead = '';
		} else {
			$raw = $this->cpar_model->getAddrReqData($selected_id);
			
			$addr_req->team_id =  $raw->team_id;
			$addr_req->team = $raw->team_name;

			$addr_req->team_lead_id = $raw->team_lead_id;
			$addr_req->team_lead = $raw->t_first_name . ' ' . (empty($raw->t_middle_name) ? '' : $raw->t_middle_name . ' ') . $raw->t_last_name;
		}

		echo json_encode($addr_req);
		exit();
	}

	private function addRequestorData(&$data, $id) {
		$raw = $this->cpar_model->getRequestorData($id);

		$requestor = new stdClass();
		$requestor->id = $id;
		$requestor->name = $raw->first_name . ' ' . (empty($raw->middle_name) ? '' : $raw->middle_name . ' ') . $raw->last_name;
		
		$requestor->team_id = $raw->team_id;
		$requestor->team = $raw->team_name;
		
		$requestor->team_lead_id = $raw->team_lead_id;
		$requestor->team_lead = $raw->t_first_name . ' ' . (empty($raw->t_middle_name) ? '' : $raw->t_middle_name . ' ') . $raw->t_last_name;

		$data['requestor'] = $requestor;
	}

	private function validateDraft($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		#form values
		$id = isset($form["id"]) ? $form["id"] : '';
		$date_filed = isset($form["date_filed"]) ? $form["date_filed"] : '';
		$title = isset($form["title"]) ? $form["title"] : '';
		$type = isset($form["type"]) ? $form["type"] : '';

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

		return $errors;
	}

	private function validateForm($form) {
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
		
		$review_action = isset($form["review_action"]) ? $form["review_action"] : '';

		if(!($is_ims_review == null && empty($is_ims_review)) && strcmp($m_status, CPAR_SUBMIT_S1_PROCEED) == 0) {
			//get saved CPAR
			$old_cpar = $this->cpar_model->getCpar($form['id']);

			if(empty($review_action)) {
				array_push($errors, 'Please choose a review action.');
			} else {
				if((int)$review_action == REVIEW_ACTIONS_MARK_REV) {
					$reviewed_by = isset($form["reviewed_by"]) ? $form["reviewed_by"] : '';
					$next_due_date = isset($form["next_due_date"]) ? $form["next_due_date"] : '';

					if(empty($reviewed_by)) {
						array_push($errors, "IMS assignee" . $is_required_suffix);
					} else {
						if(strcmp($old_cpar->addressee, $reviewed_by) == 0) {
							array_push($errors, 'CPAR Addressee and Assigned IMS cannot be the same person.');
						}
					}

					if(empty($next_due_date)) {
						array_push($errors, "Submit response by" . $is_required_suffix);
					}
				} else if((int)$review_action == REVIEW_ACTIONS_PUSH_BACK) {

				} else if((int)$review_action == REVIEW_ACTIONS_MARK_INV) {

				} else if((int)$review_action == REVIEW_ACTIONS_RE_ASSIGN) {
					$reviewed_by = isset($form["re_assign_to"]) ? $form["re_assign_to"] : '';
					$next_due_date = isset($form["review_by_due_date"]) ? $form["review_by_due_date"] : '';

					if(empty($reviewed_by)) {
						array_push($errors, "IMS assignee" . $is_required_suffix);
					} else {
						if(strcmp($old_cpar->addressee, $reviewed_by) == 0) {
							array_push($errors, 'CPAR Addressee and Assigned IMS cannot be the same person.');
						}
					}

					if(empty($next_due_date)) {
						array_push($errors, "Submit response by" . $is_required_suffix);
					}
				} else {
					array_push($errors, 'Please choose a review action.');
				}
			}

			$remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
			if(empty($remarks)) {
				if(empty($review_action) 
					|| (int)$review_action == REVIEW_ACTIONS_PUSH_BACK 
					|| (int)$review_action == REVIEW_ACTIONS_MARK_INV 
					|| (int)$review_action == REVIEW_ACTIONS_RE_ASSIGN) {
						array_push($errors, "Remarks" . $is_required_suffix);
					}
			} else {
				if(strlen($remarks) < MIN_CPAR_REMARKS || strlen($remarks) > MAX_CPAR_REMARKS) {
					array_push($errors, "Remarks should be " . MIN_CPAR_REMARKS . " to " . MAX_CPAR_REMARKS . " characters.");
				}
			}
		}

		return $errors;
	}

	private function convertForm($form) {
		$cpar = new stdClass();

		$cpar->id = isset($form['id']) ? $form['id'] : '';
		$cpar->title = fix_output2($form['title']);
		$cpar->date_filed = $form['date_filed'];

		//only set the type when it's a new record
		if(($cpar->id == null || empty($cpar->id)) || $this->startsWith($cpar->id, CPAR_TYPE_NONE_SHORT_NAME)) {
			$cpar->type = $form['type'];
		}

		$cpar->raised_as_a_result_of = !empty($form['result_of']) ? $form['result_of'] : NULL;
		$cpar->raised_as_a_result_of_others = (isset($form['result_of_others']) && !empty($form['result_of_others'])) ? $form['result_of_others'] : NULL;
		$cpar->process = !empty($form['process']) ? $form['process'] : NULL;
		$cpar->details = fix_output2($form['details']);
		$cpar->justification = fix_output2($form['justification']);
		$cpar->references = fix_output2($form['references']);
		
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
			} else {
				$cpar->addressee = NULL;
			}
		}

		$cpar->addressee_team = !empty($form['at_team']) ? $form['at_team'] : NULL;

		$cpar->addressee_team_lead = $form['at_team_lead'];
		if(!ctype_digit($cpar->addressee_team_lead)) {
			$addressee_obj = json_decode($cpar->addressee_team_lead);
			if($addressee_obj != null) {
				$cpar->addressee_team_lead = $addressee_obj[0]->id;
			} else {
				$cpar->addressee_team_lead = NULL;
			}
		}

		$cpar->requestor = $form['req_name'];
		if(!ctype_digit($cpar->requestor)) {
			$requestor_obj = json_decode($cpar->requestor);
			if($requestor_obj != null) {
				$cpar->requestor = $requestor_obj[0]->id;
			} else {
				$cpar->requestor = NULL;
			}
		}

		$cpar->requestor_team = $form['req_team'];

		$cpar->requestor_team_lead = $form['req_team_lead'];
		if(!ctype_digit($cpar->requestor_team_lead)) {
			$requestor_obj = json_decode($cpar->requestor_team_lead);
			if($requestor_obj != null) {
				$cpar->requestor_team_lead = $requestor_obj[0]->id;
			} else {
				$cpar->requestor_team_lead = NULL;
			}
		}
		
		if(empty($cpar->requestor_team_lead)) {
			$cpar->requestor_team_lead = NULL;
		}

		return $cpar;
	}

	private function generateCparNumber($cpar_type, $series_id) {
		if($series_id == null || empty($series_id)) {
			$series_id = 0;
		}

		$type_str = '';
		if(intval($cpar_type) == CPAR_TYPE_C) {
			$type_str = CPAR_TYPE_C_SHORT_NAME;
		} else if(intval($cpar_type) == CPAR_TYPE_P) {
			$type_str = CPAR_TYPE_P_SHORT_NAME;
		} else {
			$type_str = CPAR_TYPE_NONE_SHORT_NAME;
		}

		$year_str = date('Y');
		$id_str = str_pad($series_id, 4, '0', STR_PAD_LEFT);

		return $type_str . CPAR_NO_SEPARATOR . $year_str . CPAR_NO_SEPARATOR . $id_str;
	}

	private function finalizeCparList(&$cpar_list) {
		$final_cpar_list = array();

		foreach ($cpar_list as $cpar) {
			$final_cpar = $cpar;
			
			if(!($final_cpar['date_filed'] == null || empty($final_cpar['date_filed']))) {
				$date = new DateTime($final_cpar['date_filed']);
				$final_cpar['date_filed_formatted'] = $date->format('M d, Y');
			} else {
				$final_cpar['date_filed_formatted'] = '';
			}
			
			if(!($final_cpar['created_date'] == null || empty($final_cpar['created_date']))) {
				$date = new DateTime($final_cpar['created_date']);
				$final_cpar['created_date_formatted'] = $date->format('M d, Y');
			} else {
				$final_cpar['created_date_formatted'] = '';
			}

			if(!($final_cpar['date_due'] == null || empty($final_cpar['date_due']))) {
				$date = new DateTime($final_cpar['date_due']);
				$final_cpar['date_due_formatted'] = $date->format('M d, Y');
			} else {
				$final_cpar['date_due_formatted'] = '';
			}

			if(!($final_cpar['ff_up_date'] == null || empty($final_cpar['ff_up_date']))) {
				$date = new DateTime($final_cpar['ff_up_date']);
				$final_cpar['ff_up_date_formatted'] = $date->format('M d, Y');
			} else {
				$final_cpar['ff_up_date_formatted'] = '';
			}

			if(!($final_cpar['closure_date'] == null || empty($final_cpar['closure_date']))) {
				$date = new DateTime($final_cpar['closure_date']);
				$final_cpar['closure_date_formatted'] = $date->format('M d, Y');
			} else {
				$final_cpar['closure_date_formatted'] = '';
			}

			$final_cpar['status_name'] = 'Stage ' . $final_cpar['status'];

			array_push($final_cpar_list, $final_cpar);
		}

		return $final_cpar_list;
	}

	private function finalizeCparCSV(&$cpar_list, $tab) {
		$final_cpar_list = array();

		foreach ($cpar_list as $cpar) {
			$final_cpar = $cpar;

			//created date
			$date = new DateTime($final_cpar[CSV_HEADER_DATE_CREATED]);
			$final_cpar[CSV_HEADER_DATE_CREATED] = $date->format('M d, Y');

			//due date
			if(!empty($final_cpar[CSV_HEADER_DUE_DATE])) {
				$date = new DateTime($final_cpar[CSV_HEADER_DUE_DATE]);
				$final_cpar[CSV_HEADER_DUE_DATE] = $date->format('M d, Y');
			} else {
				$final_cpar[CSV_HEADER_DUE_DATE] = '';
			}

			//follow-up date
			if(!empty($final_cpar[CSV_HEADER_FOLLOW_UP_DATE])) {
				$date = new DateTime($final_cpar[CSV_HEADER_FOLLOW_UP_DATE]);
				$final_cpar[CSV_HEADER_FOLLOW_UP_DATE] = $date->format('M d, Y');
			} else {
				$final_cpar[CSV_HEADER_FOLLOW_UP_DATE] = '';
			}

			//closure date
			if(!empty($final_cpar[CSV_HEADER_CLOSURE_DATE])) {
				$date = new DateTime($final_cpar[CSV_HEADER_CLOSURE_DATE]);
				$final_cpar[CSV_HEADER_CLOSURE_DATE] = $date->format('M d, Y');
			} else {
				$final_cpar[CSV_HEADER_CLOSURE_DATE] = '';
			}

			$final_cpar[CSV_HEADER_STAGE] = 'Stage ' . $final_cpar[CSV_HEADER_STAGE];
			$final_cpar[CSV_HEADER_STATUS] = getSubStatusName($final_cpar[CSV_HEADER_STATUS]);

			//remove columns based on tab
			if(strcmp(CPAR_TAB_ALL, $tab) != 0) {
				unset($final_cpar[CSV_HEADER_STAGE]);
			}

			if((int)$tab != CPAR_STAGE_3) {
				unset($final_cpar[CSV_HEADER_FOLLOW_UP_DATE]);
			}

			if((int)$tab != CPAR_STAGE_5) {
				unset($final_cpar[CSV_HEADER_CLOSURE_DATE]);
			}

			if((int)$tab == CPAR_STAGE_1) {
				unset($final_cpar[CSV_HEADER_DUE_DATE]);
			} else if((int)$tab == CPAR_STAGE_2) {
				$final_cpar[CSV_HEADER_NEXT_DUE_DATE] = $final_cpar[CSV_HEADER_DUE_DATE];
				unset($final_cpar[CSV_HEADER_DUE_DATE]);
			} else if((int)$tab == CPAR_STAGE_3) {
				//current limitation is that completion_date will be the last column (instead of 2nd to the last as seen in the screen)
				//this is due to the fact that completion_date is a newer column (because it was purposely set in this else-if-then block)
				$final_cpar[CSV_HEADER_COMPLETION_DATE] = $final_cpar[CSV_HEADER_DUE_DATE];
				unset($final_cpar[CSV_HEADER_DUE_DATE]);
			} else if((int)$tab == CPAR_STAGE_4) {
				$final_cpar[CSV_HEADER_IMS_DUE_DATE] = $final_cpar[CSV_HEADER_DUE_DATE];
				unset($final_cpar[CSV_HEADER_DUE_DATE]);
			} else if((int)$tab == CPAR_STAGE_5) {
				unset($final_cpar[CSV_HEADER_DUE_DATE]);
			}


			array_push($final_cpar_list, $final_cpar);
		}

		return $final_cpar_list;
	}

	private function array2csv(array &$array) {
		if (count($array) == 0) {
			return null;
	   	}

		ob_start();
		$df = fopen("php://output", 'w');

		//print headers
		fputcsv($df, array_keys($array[0]));

		foreach ($array as $row) {
			fputcsv($df, $row);
		}

		fclose($df);
		return ob_get_clean();
	}

	private function download_send_headers($filename) {
	    // disable caching
	    $now = gmdate("D, d M Y H:i:s");
	    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	    header("Last-Modified: {$now} GMT");

	    // force download  
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");

	    // disposition / encoding on response body
	    header("Content-Disposition: attachment;filename={$filename}");
	    header("Content-Transfer-Encoding: binary");
	}

	private function validateEdit($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else {
			$access = $this->users_model->getUserAccessRights($l_id);
			$allowed_access_partial = ($access->mr_flag == MR_FLAG || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if($cpar->status == CPAR_STAGE_1 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_DRAFT) == 0) {
				if(!$allowed_access_partial && !($l_id == $cpar->created_by || $l_id == $cpar->requestor || $l_id == $cpar->requestor_team_lead)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else if($cpar->status == CPAR_STAGE_1 && 
				(strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0 || 
				 strcmp($cpar->sub_status, CPAR_MINI_STATUS_PUSHED_BACK) == 0 || 
				 strcmp($cpar->sub_status, CPAR_MINI_STATUS_CLOSED) == 0)) { 
				if(!$allowed_access_partial && !($l_id == $cpar->created_by || $l_id == $cpar->requestor || $l_id == $cpar->requestor_team_lead || $l_id == $cpar->assigned_ims)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			} else if($cpar->status == CPAR_STAGE_2 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0) {
				if(!$allowed_access_partial && 
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
				if(!$allowed_access_partial && 
					!($l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->addressee_team_lead ||
						$l_id == $cpar->assigned_ims)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			}
		}

		if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		}

		return $errors;
	}

	private function processEditForm($cpar_no, $cpar, $form) {
		$result = new stdClass();
		$m_status = $form["m_status"];
		$logged_in_id = $this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($logged_in_id);

		if(!($cpar->id == null || empty($cpar->id))) {
			$is_ims_review = isset($form["is_ims_review"]) ? $form["is_ims_review"] : '';
			$status = CPAR_STAGE_1;
			$sub_status = '';

			//adjust sub_status accordingly
			if(strcmp($m_status, CPAR_MINI_STATUS_DRAFT) == 0) {
				$sub_status = CPAR_MINI_STATUS_DRAFT;
				
				if($access->ims_flag == IMS_FLAG) {
						
					if(isset($form["next_due_date"]) && strlen($form["next_due_date"]) > 0) {
						$due_date_date = new DateTime($form["next_due_date"]);
						if($due_date_date) {
							$cpar->date_due = $due_date_date->format('Y-m-d');	
						}
					}

					$cpar->assigned_ims = $logged_in_id;
									
				}
				
			} else if(strcmp($m_status, CPAR_SUBMIT_S1_SAVE_ONLY) == 0) {
				$sub_status = CPAR_MINI_STATUS_PUSHED_BACK;
			} else if(strcmp($m_status, CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES) == 0) {
				$status = CPAR_STAGE_2;
			} else if(!($is_ims_review == null && empty($is_ims_review))) {
				if(strcmp($m_status, CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES) == 0) {
								
					$sub_status = CPAR_MINI_STATUS_FOR_IMS_REVIEW; 
					$this->success_msg = '';
				
				} else { 
					$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
					if(intval($review_action) == REVIEW_ACTIONS_MARK_REV) {
						$status = CPAR_STAGE_2;
						$sub_status = CPAR_MINI_STATUS_S2_2A1;

						$due_date_date = new DateTime($form["next_due_date"]);
						$cpar->date_due = $due_date_date->format('Y-m-d');

						//set assigned_ims
						$assigned_ims = isset($form["reviewed_by"]) ? $form["reviewed_by"] : '';
						$cpar->assigned_ims = normalizeSelect2Value($assigned_ims);
						
						$this->success_msg = MSG_REVIEW_ACTIONS_MARK_REV;
						
					} else if(intval($review_action) == REVIEW_ACTIONS_PUSH_BACK) {
						$sub_status = CPAR_MINI_STATUS_PUSHED_BACK;

						//update push back
						$cpar->pb_user = $logged_in_id;
						$cpar->pb_date = date('Y-m-d H:i:s');
						$cpar->pb_remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
						
						$this->success_msg = MSG_REVIEW_ACTIONS_PUSH_BACK;
					} else if(intval($review_action) == REVIEW_ACTIONS_MARK_INV) {
						$cpar->pb_user = $logged_in_id;
						$cpar->pb_date = date('Y-m-d H:i:s');
						$cpar->pb_remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';

						$sub_status = CPAR_MINI_STATUS_CLOSED;
						
						$this->success_msg = MSG_REVIEW_ACTIONS_MARK_INV;
					} else if(intval($review_action) == REVIEW_ACTIONS_RE_ASSIGN) {
					
						$review_by_due_date = new DateTime($form["review_by_due_date"]);
						$cpar->date_due = $review_by_due_date->format('Y-m-d');

						//set assigned_ims
						$assigned_ims = isset($form["re_assign_to"]) ? $form["re_assign_to"] : '';
						$cpar->assigned_ims = normalizeSelect2Value($assigned_ims);
						
						$new_ims = $this->users_model->getUser($assigned_ims);
						
						$this->success_msg = sprintf(MSG_REVIEW_ACTIONS_RE_ASSIGN, $new_ims->user_full_name);
					}
				}
			} else {
			
				if($access->ims_flag == IMS_FLAG) {
			
					$status = CPAR_STAGE_2;
					$sub_status = CPAR_MINI_STATUS_S2_2A1;
			
					if(isset($form["next_due_date"]) && strlen($form["next_due_date"]) > 0) {
						$due_date_date = new DateTime($form["next_due_date"]);
						if($due_date_date) {
							$cpar->date_due = $due_date_date->format('Y-m-d');	
						}
					}

					$cpar->assigned_ims = $logged_in_id;
					
					$this->success_msg = MSG_REVIEW_ACTIONS_MARK_REV;
				
				} else {
					$sub_status = CPAR_MINI_STATUS_FOR_IMS_REVIEW;
					$this->success_msg = MSG_FOR_IMS_REVIEW;
				}
			}

			$old_id = $cpar->id;
			//update ID if no type yet (XAR)
			if($this->startsWith($cpar->id, CPAR_TYPE_NONE_START_CHAR) && ((int)$cpar->type == CPAR_TYPE_C || (int)$cpar->type == CPAR_TYPE_P)) {
				$type_str = ((int)$cpar->type == CPAR_TYPE_C) ? CPAR_TYPE_C_SHORT_NAME : CPAR_TYPE_P_SHORT_NAME;
				$cpar->id = str_replace(CPAR_TYPE_NONE_SHORT_NAME, $type_str, $cpar->id);
			}

			$cpar->status = $status;
			if(!empty($sub_status)) {
				$cpar->sub_status = $sub_status;
			}
			$cpar->updated_by = $logged_in_id;
			$cpar->updated_date = date('Y-m-d H:i:s');

			$result = $this->cpar_model->updateCpar($cpar, $old_id);

			$cpar_no = $cpar->id;
			if(strcmp($m_status, CPAR_MINI_STATUS_DRAFT) != 0) {
				if(!empty($sub_status)) {
					if($result != null && $result->success && strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0 && strcmp($m_status, CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES) != 0) {
						$this->email->processEmails($cpar_no, '1', 'A');
					} else if($result != null && $result->success && (int)$cpar->status == CPAR_STAGE_2 && strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0 && (strcmp($m_status, CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES) != 0 || strcmp($m_status, CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES) != 0)) {
						$this->email->processEmails($cpar_no, '2', 'A');
					} else if($result != null && $result->success && strcmp($cpar->sub_status, CPAR_MINI_STATUS_PUSHED_BACK) == 0 && strcmp($m_status, CPAR_SUBMIT_S1_SAVE_ONLY) != 0) {
						$this->email->processEmails($cpar_no, '1', 'AX');
					} else if($result != null && $result->success && strcmp($cpar->sub_status, CPAR_MINI_STATUS_CLOSED) == 0) {
						$this->email->processEmails($cpar_no, '1', 'B');
					}
				} else {
					if($result && isset($form["review_action"]) && $form["review_action"] == REVIEW_ACTIONS_RE_ASSIGN) {
						$this->email->processEmails($cpar_no, '1', 'AA');
					}
				}
			}

			$review_action = isset($form["review_action"]) ? $form["review_action"] : '';
			if(!($is_ims_review == null && empty($is_ims_review)) && !($review_action == null && empty($review_action)) && strcmp($m_status, CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES) != 0) {

				$rev_hist = new stdClass();
				$rev_hist->cpar_no = $cpar->id;
				$rev_hist->action = isset($form["review_action"]) ? $form["review_action"] : '';

				$rev_hist->role = $access->access_level == ACCESS_LEVEL_ADMIN_FLAG ? REVIEW_ROLE_ADMIN : REVIEW_ROLE_IMS;

				$rev_hist->stage = CPAR_STAGE_1;
				$old_cpar = $this->cpar_model->getCpar($cpar_no);
				$rev_hist->sub_status = $old_cpar->sub_status;
				$rev_hist->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';

				$rev_hist->reviewed_by = $logged_in_id;
				$rev_hist->reviewed_date = date('Y-m-d H:i:s');

				$due_date_date = new DateTime($form["next_due_date"]);
				$rev_hist->due_date = $due_date_date->format('Y-m-d');

				$this->review_history_model->insertReviewHistory($rev_hist);
				
				#LOG HERE
				$log = new stdClass();
				$log->cpar_no = $cpar_no;
				$log->action = $this->audit_log_model->get_action($review_action);
				$log->stage = CPAR_STAGE_1;
				$log->sub_status = $old_cpar->sub_status;
				$log->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
				$log->notes = json_encode($form);
				$log->created_by = $logged_in_id;
				$log->created_date = date('Y-m-d H:i:s');
				$this->audit_log_model->insertLog($log);
				
			} else {

				if(strcmp($m_status, CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES) != 0) {

					if($access->ims_flag == IMS_FLAG ) {
	
						$rev_hist = new stdClass();
						$rev_hist->cpar_no = $cpar->id;
						$rev_hist->action = REVIEW_ACTIONS_MARK_REV;
					
						$rev_hist->role = $access->access_level == ACCESS_LEVEL_ADMIN_FLAG ? REVIEW_ROLE_ADMIN : REVIEW_ROLE_IMS;
	
						$rev_hist->stage = CPAR_STAGE_1;
						$old_cpar = $this->cpar_model->getCpar($cpar_no);
						$rev_hist->sub_status = $old_cpar->sub_status;
						$rev_hist->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
	
						$rev_hist->reviewed_by = $logged_in_id;
						$rev_hist->reviewed_date = date('Y-m-d H:i:s');
	
						$due_date_date = new DateTime($form["next_due_date"]);
						$rev_hist->due_date = $due_date_date->format('Y-m-d');
	
						$this->review_history_model->insertReviewHistory($rev_hist);
								
					}
					
				}
				
				if(strcmp($m_status, CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES)) {
					#LOG HERE
					$old_cpar = $this->cpar_model->getCpar($cpar_no);
				
					$log = new stdClass();
					$log->cpar_no = $cpar_no;
					$log->action = LOG_UPDATED_CPAR;
					$log->stage = $old_cpar->status;
					$log->sub_status = $old_cpar->sub_status;
					$log->remarks = '';
					$log->notes = json_encode($form);
					$log->created_by = $logged_in_id;
					$log->created_date = date('Y-m-d H:i:s');
					$this->audit_log_model->insertLog($log);
				}
			}
			
		} else {
			$cpar->id = $cpar_no;
			$cpar->created_by = $logged_in_id;
			$cpar->created_date = date('Y-m-d H:i:s');
			$cpar->updated_by = $logged_in_id;
			$cpar->updated_date = $cpar->created_date;


			if($access->ims_flag == IMS_FLAG) {
				
				$cpar->status = (strcmp($m_status, CPAR_MINI_STATUS_DRAFT) == 0) ? CPAR_STAGE_1 : CPAR_STAGE_2;
				$cpar->sub_status = (strcmp($m_status, CPAR_MINI_STATUS_DRAFT) == 0) ? CPAR_MINI_STATUS_DRAFT : CPAR_MINI_STATUS_S2_2A1;
				
				if(isset($form["next_due_date"]) && strlen($form["next_due_date"]) > 0) {
					$due_date_date = new DateTime($form["next_due_date"]);
					if($due_date_date) {
						$cpar->date_due = $due_date_date->format('Y-m-d');	
					}
				}

				$cpar->assigned_ims = $logged_in_id;
				
				$result = $this->cpar_model->insertCpar($cpar);
				
				$this->success_msg = (strcmp($m_status, CPAR_MINI_STATUS_DRAFT) == 0) ? '' : MSG_REVIEW_ACTIONS_MARK_REV;
				
				if(strcmp($m_status, CPAR_MINI_STATUS_DRAFT) != 0) {
					$this->email->processEmails($cpar_no, '2', 'A');
					
					$rev_hist = new stdClass();
					$rev_hist->cpar_no = $cpar->id;
					$rev_hist->action = REVIEW_ACTIONS_MARK_REV;
				
					$rev_hist->role = $access->access_level == ACCESS_LEVEL_ADMIN_FLAG ? REVIEW_ROLE_ADMIN : REVIEW_ROLE_IMS;
	
					$rev_hist->stage = CPAR_STAGE_1;
					$rev_hist->sub_status = $cpar->sub_status;
					$rev_hist->remarks = isset($form["remarks"]) ? fix_output2($form["remarks"]) : '';
	
					$rev_hist->reviewed_by = $logged_in_id;
					$rev_hist->reviewed_date = date('Y-m-d H:i:s');
	
					$due_date_date = new DateTime($form["next_due_date"]);
					$rev_hist->due_date = $due_date_date->format('Y-m-d');
	
					$this->review_history_model->insertReviewHistory($rev_hist);
				}
										
				#LOG HERE
				$log = new stdClass();
				$log->cpar_no = $cpar_no;
				$log->action = LOG_CREATED_CPAR;
				$log->stage = $cpar->status;
				$log->sub_status = $cpar->sub_status;
				$log->remarks = '';
				$log->notes = json_encode($form);
				$log->created_by = $logged_in_id;
				$log->created_date = date('Y-m-d H:i:s');
				$this->audit_log_model->insertLog($log);
				
			} else {
				$cpar->status = CPAR_STAGE_1;
				$cpar->sub_status = (strcmp($m_status, CPAR_MINI_STATUS_DRAFT) == 0) ? CPAR_MINI_STATUS_DRAFT : CPAR_MINI_STATUS_FOR_IMS_REVIEW;
				
				$result = $this->cpar_model->insertCpar($cpar);
				
				$this->success_msg = (strcmp($m_status, CPAR_MINI_STATUS_DRAFT) == 0) ? '' : MSG_FOR_IMS_REVIEW;
				
				if(strcmp($m_status, CPAR_MINI_STATUS_DRAFT) != 0) {
					$this->email->processEmails($cpar_no, '1', 'A');	
				}
				
				#LOG HERE
				$log = new stdClass();
				$log->cpar_no = $cpar_no;
				$log->action = LOG_CREATED_CPAR;
				$log->stage = CPAR_STAGE_1;
				$log->sub_status = $cpar->sub_status;
				$log->remarks = '';
				$log->notes = json_encode($form);
				$log->created_by = $logged_in_id;
				$log->created_date = date('Y-m-d H:i:s');
				$this->audit_log_model->insertLog($log);

			}
			
		}
		
		return $result;
	}

	private function startsWith($haystack, $needle) {
	    return $needle === "" || strpos($haystack, $needle) === 0;
	}

	private function sendEmail($recipients, $from, $from_name, $subject, $body) {
		$config = $this->email->getEmailConfig();
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");

		$this->email->from($from, $from_name);
		$this->email->to($this->email->generateRecipients($recipients));

		$this->email->subject($subject);
		$this->email->message($body);

		$this->email->send();

		log_message('debug', $this->email->print_debugger());
	}

	private function getFileNames($cpar_no, $stage) {
		$prefix = $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR;
		$filenames = preg_grep('~^' . $prefix . '.*$~', scandir(UPLOAD_PATH));

		$obj = null;
		$arr = array();
		foreach ($filenames as $filename) {
			$obj = new stdClass();
			$obj->filename = $filename;
			$obj->orig_filename = getOrigFileName($filename);

			array_push($arr, $obj);
		}

		return $arr;
	}

	private function removeUploadedFiles($cpar_no, $stage, $removed_uploads) {
		foreach ($removed_uploads as $file) {
		    $final_name = UPLOAD_PATH . $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR . $file;
		    if(file_exists($final_name)) {
		        unlink($final_name);
		    }
		}
	}

	private function canEdit($cpar) {
		$can_edit = false;

		$l_id = (int)$this->session->userdata('loggedIn');
		$access = $this->users_model->getUserAccessRights($l_id);

		if($cpar->status == CPAR_STAGE_1) {
			$admin_access = ((int)$access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_DRAFT) == 0) {
				//creator, requestor and admin can edit
				if((int)$cpar->created_by == $l_id || (int)$cpar->requestor == $l_id || $admin_access) {
					$can_edit = true;
				}
			} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0) {
				//assigned IMS and admin can edit
				if((int)$cpar->assigned_ims == $l_id || $admin_access) {
					$can_edit = true;
				}
			} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_PUSHED_BACK) == 0) {
				//creator, requestor, assigned IMS and admin can edit
				if((int)$cpar->created_by == $l_id || (int)$cpar->requestor == $l_id || (int)$cpar->assigned_ims == $l_id || $admin_access) {
					$can_edit = true;
				}
			}
		}

		return $can_edit;
	}
	
	public function link($cpar_no) {
		
		$cpar = NULL;
		
		if (!$this->session->userdata('loggedIn')) {
			redirect('http://' . base_url().'/login?route='.$cpar_no);
		}
		
		if(isset($cpar_no)) {
			$cpar = $this->cpar_model->getCpar($cpar_no);
		} else {
			$this->show_custom_404();
			exit();
		}
	
		if($cpar) {
			if($cpar->status == CPAR_TAB_STAGE_1) {
				if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_DRAFT) == 0 || 
				strcmp($cpar->sub_status, CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0 ||
				strcmp($cpar->sub_status, CPAR_MINI_STATUS_PUSHED_BACK) == 0) {
					$item_url = 'cpar/edit/' . $cpar_no;
				} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_CLOSED) == 0) {
					$item_url = 'cpar/view/' . $cpar_no;
				}
			} else if($cpar->status == CPAR_TAB_STAGE_2) {
				if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A1) == 0 || strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2A2) == 0) {
					$item_url = 'cpar_s2/edit/' . $cpar_no;
				} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S2_2B) == 0) {
					$item_url = 'cpar_s2/review/' . $cpar_no;
				}
			} else if($cpar->status == CPAR_TAB_STAGE_3) {
				if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3A) == 0) {
					$item_url = 'cpar_s3/review/' . $cpar_no;
				} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S3_3B) == 0) {
					$item_url = 'cpar_s3/edit/' . $cpar_no;
				}
			} else if($cpar->status == CPAR_TAB_STAGE_4) {
				#if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S4_4A) == 0 || strcmp($cpar->sub_status, CPAR_MINI_STATUS_S4_4A2) == 0) {
				#	$item_url = 'cpar_s4/review/' . $cpar_no;
				#} else if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S4_4B) == 0) {
					$item_url = 'cpar_s4/review/' . $cpar_no;
				#}
			} else if($cpar->status == CPAR_TAB_STAGE_5) {
				#if(strcmp($cpar->sub_status, CPAR_MINI_STATUS_S5_5A) == 0) {
					$item_url = 'cpar_s5/view/' . $cpar_no;
				#}
			}
			
			redirect('http://'.$this->config->base_url().$item_url);
		} else {
			$this->show_custom_404();
		}
		
	}
}