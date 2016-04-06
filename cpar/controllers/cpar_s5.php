<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cpar_s5 extends MY_Controller {

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

	public function view() {
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
					$header_text = '(Closed)';

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

					//get follow up history
					$ff_up_history = $this->ff_up_history_model->getReviewHistory($cpar_no);
					$this->getFfUpUploadedFiles($cpar_no, $ff_up_history);
					$data['ff_up_history'] = $ff_up_history;

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
				
				$data['screen_title'] = 'View CPAR';

				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				$this->load->view('cpar_s5/view', $data);				
				$this->load->view('template/footer');
			}
		}
	}

	private function validatePermissions($cpar) {
		$errors = array();

		$l_id = $this->session->userdata('loggedIn');
		if(empty($l_id)) {
			array_push($errors, 'Missing login ID information.');
		} else if($cpar == null || empty($cpar) || $cpar->is_deleted == DELETED_FLAG) {
			array_push($errors, 'The CPAR No. you were trying to edit does not exist.');
		} else if($cpar->status != CPAR_STAGE_5) {
			array_push($errors, "CPAR <b>{$cpar->id}</b> is not editable/viewable in this stage.");
		} else {
			$access = $this->users_model->getUserAccessRights($l_id);
			$allowed_access = ($access->mr_flag == MR_FLAG || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			if($cpar->status == CPAR_STAGE_5 && (int)$cpar->sub_status == CPAR_MINI_STATUS_S5_5A) {
				//get responsible persons
				$resp_persons = normalizeResponsiblePersons($this->addressee_fields_model->getResponsiblePersons($cpar->id));
				$resp_person_access = in_array($l_id, $resp_persons);

				if(!$allowed_access && !(
						$l_id == $cpar->created_by || 
						$l_id == $cpar->requestor || 
						$l_id == $cpar->requestor_team_lead ||
						$l_id == $cpar->addressee ||
						$l_id == $cpar->addressee_team_lead ||
						$l_id == $cpar->assigned_ims)) {
					array_push($errors, "Unable to view/save <b>{$cpar->id}</b>. You do not have access to this record.");
				}
			}
		}

		return $errors;
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