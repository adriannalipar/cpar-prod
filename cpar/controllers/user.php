<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class User extends MY_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->library('uri');
		$this->load->helper('url');
		$this->load->helper('email');		
		$this->load->model('users_model');
	}

	public function index() {
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
			} else {
				$filters = $this->session->userdata('search_filters');
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
			$sort_by = DEFAULT_USER_SORT_BY;
		}

		$sort = $this->input->get('sort');
		if(empty($sort)) {
			$sort = DEFAULT_USER_SORT;
		}

		$count = $this->users_model->countSearchUserList($filters);
		$user_list = $this->users_model->searchUserList($filters, $rpp, $pn, $sort_by, $sort);
		$user_list = $this->finalizeUserList($user_list);

		$data['rpp'] = $rpp;
		$data['pn'] = $pn;
		$data['sort_by'] = $sort_by;
		$data['sort'] = $sort;

		$data['nor'] = count($user_list); //no of rows (this page only)
		$data['count'] = $count; //no of rows (whole)
		
		if(strcmp($rpp, RPP_ALL) != 0) {
			$data['pages'] = (int)($count / $rpp) + ($count % $rpp > 0 ? 1 : 0);

			//pages
			$set_no = (int)(($pn - 1) / PAGES_PER_SET) + 1;
			$data['min_page'] = (int)($set_no - 1) * PAGES_PER_SET + 1;

			$max_page = $set_no * PAGES_PER_SET;
			$data['max_page'] = $max_page > $data['pages'] ? $data['pages'] : $max_page;
		}

		$data['user_list'] = $user_list;

		if($isPartial) {
			$this->load->view('user/_list', $data);
		} else {
			$this->addCommonData($data);
			$data['teams'] = $this->users_model->getTeams();

			$data['screen_title'] = 'User List';

			$this->load->view('template/header', $data);
			$this->load->view('user/list', $data);
			$this->load->view('template/footer');
		}
	}

	public function create() {
		//get dropdown lists
		$data['teams'] = $this->users_model->getTeams();
		$data['locations'] = $this->users_model->getLocations();
		$data['mrUser'] = $this->users_model->getMRUser();

		$this->addCommonData($data);
		
		$data['screen_title'] = 'Create User';
		
		$this->load->view('template/header', $data);
		$this->load->view('user/create', $data);
		$this->load->view('template/footer');
	}

	public function view() {
		$id = $this->uri->segment(3);
		if($id == null || empty($id)) {
			$this->show_custom_404();
		} else {
			$user = $this->users_model->getUser($id);

			if($user == null || empty($user)) {
				$errors = array();
				array_push($errors, 'The user ID you were trying to view does not exist.');

				$this->session->set_userdata('search_page_errors', $errors);
				redirect('http://' . base_url() . 'user/');
			} else {
				$data['user'] = $user;

				$data['screen_title'] = 'View User';

				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				$this->load->view('user/view', $data);
				$this->load->view('template/footer');
			}
		}
	}

	public function edit() {
		$id = $this->uri->segment(3);
		if($id == null || empty($id)) {
			$this->show_custom_404();
		} else {
			$user = $this->users_model->getUser($id);
			if($user == null || empty($user)) {
				$errors = array();
				array_push($errors, 'The user ID you were trying to edit does not exist.');

				$this->session->set_userdata('search_page_errors', $errors);
				redirect('http://' . base_url() . 'user/');
			} else {
				//get dropdown lists
				$data['teams'] = $this->users_model->getTeams();
				$data['locations'] = $this->users_model->getLocations();
				$data['mrUser'] = $this->users_model->getMRUser();

				$data['user'] = $user;
				
				$data['screen_title'] = 'Edit User';
				
				$this->addCommonData($data);
				$this->load->view('template/header', $data);
				$this->load->view('user/edit', $data);
				$this->load->view('template/footer');
			}
		}
	}

	public function delete() {
		$success_msgs = array();
		$errors = array();

		$id = $this->input->post('id');

		if($id == null || empty($id)) {
			array_push($errors, 'Missing or incorrect user ID.');
		} else if (strcmp($this->session->userdata('logged_in_user')->id, $id) == 0) {
			array_push($errors, 'You cannot delete your own account.');
		} else {
			if($this->users_model->isMR($id)) {
				array_push($errors, 'Cannot delete a Management Representative account.');
			}

			if($this->users_model->isIMS($id)) {
				if($this->users_model->countIMSUsers() <= 1) {
					array_push($errors, 'Cannot delete the last IMS user.');	
				}
			}

			if(empty($errors)) {
				$res = $this->users_model->deleteUser($id);
				if($res) {
					array_push($success_msgs, 'Successfully deleted the user.');
				} else {
					array_push($errors, 'Error encountered while trying to delete the user.');
				}
			}
		}

		if(!empty($errors)) {
			$this->session->set_userdata('search_page_errors', $errors);
		} else if(!empty($success_msgs)) {
			$this->session->set_userdata('search_page_success_msgs', $success_msgs);
		}

		redirect('http://' . base_url() . 'user/');
	}

	public function getTLs() {
		$term = $this->input->get('term');

		$data = $this->users_model->getUsers($term);

		echo json_encode($data);
		exit;
	}

	public function save() {
		$form = array_map('trim', $this->input->post());

		$result = new stdClass();
		$errors = $this->validateForm($form);
		
		if(!empty($errors)) {
			$result->success = false; 
			$result->errors = $errors;
		} else {
			$result = null;
			$user = $this->convertForm($form);

			if($this->input->post('is_edit')) {
				$result = $this->users_model->updateUser($user);
			} else {
				$result = $this->users_model->insertUser($user);
			}
			
			if($result != null && $result->success) {
				$result->success = true; 

				if($user->mr_flag) {
					$this->users_model->removeMRUserRights($user->email_address);
				}
			} else {
				$result->success = false;
				$result->errors = array();
				array_push($result->errors, $result->error);
			}
		}

		echo json_encode($result);
	}

	private function finalizeUserList(&$user_list) {
		$final_user_list = array();

		foreach ($user_list as $user) {
			$final_user = $user;

			if(intval($user['ims_flag']) == IMS_FLAG && intval($user['mr_flag']) == MR_FLAG) {
				$final_user['role'] = IMS_USER_ROLE . ' & ' . MR_USER_ROLE;
			} else if (intval($user['ims_flag']) == IMS_FLAG) {
				$final_user['role'] = IMS_USER_ROLE;
			} else if (intval($user['mr_flag']) == MR_FLAG) {
				$final_user['role'] = MR_USER_ROLE;
			} else {
				$final_user['role'] = USER_USER_ROLE;
			}

			if(intval($user['access_level']) == ACCESS_LEVEL_ADMIN_FLAG) {
				$final_user['access_level_name'] = ACCESS_LEVEL_ADMIN;
			} else {
				$final_user['access_level_name'] = ACCESS_LEVEL_USER;
			}

			if(intval($user['status']) == USER_STATUS_ACTIVE_FLAG) {
				$final_user['status_name'] = USER_STATUS_ACTIVE;
			} else {
				$final_user['status_name'] = USER_STATUS_INACTIVE;
			}

			array_push($final_user_list, $final_user);
		}

		return $final_user_list;
	}

	private function validateForm($form) {
		$errors = array();
		$is_required_suffix = " is required.";

		#form values
		$is_edit = isset($form["is_edit"]) ? $form["is_edit"] : '';
		$user_id = isset($form["user_id"]) ? $form["user_id"] : '';
		$fname = isset($form["fname"]) ? $form["fname"] : '';
		$mname = isset($form["mname"]) ? $form["mname"] : '';
		$lname = isset($form["lname"]) ? $form["lname"] : '';
		$pos_title = isset($form["pos_title"]) ? $form["pos_title"] : '';
		$location = isset($form["location"]) ? $form["location"] : '';
		$team = isset($form["team"]) ? $form["team"] : '';
		$email = isset($form["email"]) ? $form["email"] : '';
		$access_level = isset($form["access_level"]) ? $form["access_level"] : '';
		$user_status = isset($form["user_status"]) ? $form["user_status"] : '';
		$mr_flag = isset($form['mr_flag']) ? $form['mr_flag'] : false;

		if($is_edit && !$this->users_model->isExistingID($user_id)) {
			array_push($errors, 'The user ID you were trying to edit does not exist.');
		}

		if(empty($fname)) {
			array_push($errors, "First Name" . $is_required_suffix);
		} else {
			if(strlen($fname) < MIN_USER_FNAME || strlen($fname) > MAX_USER_FNAME) {
				array_push($errors, "First Name should be " . MIN_USER_FNAME . " to " . MAX_USER_FNAME . " characters.");
			}
		}

		if(!($mname == null || empty($mname))) {
			if(strlen($mname) < MIN_USER_MNAME || strlen($mname) > MAX_USER_MNAME) {
				array_push($errors, "Middle Name should be " . MIN_USER_MNAME . " to " . MAX_USER_MNAME . " characters.");
			}
		}

		if(empty($lname)) {
			array_push($errors, "Last Name" . $is_required_suffix);
		} else {
			if(strlen($lname) < MIN_USER_LNAME || strlen($lname) > MAX_USER_LNAME) {
				array_push($errors, "Last Name should be " . MIN_USER_LNAME . " to " . MAX_USER_LNAME . " characters.");
			}
		}

		if(empty($pos_title)) {
			array_push($errors, "Position Title" . $is_required_suffix);
		} else {
			if(strlen($pos_title) < MIN_USER_POS_TITLE || strlen($pos_title) > MAX_USER_POS_TITLE) {
				array_push($errors, "Position Title should be " . MIN_USER_POS_TITLE . " to " . MAX_USER_POS_TITLE . " characters.");
			}
		}

		if(empty($location)) {
			array_push($errors, "Location" . $is_required_suffix);
		}

		if(empty($team)) {
			array_push($errors, "Team" . $is_required_suffix);
		}

		if(empty($email)) {
			array_push($errors, "Email" . $is_required_suffix);
		} else if(strlen($email) < MIN_USER_EMAIL_ADDRESS || strlen($email) > MAX_USER_EMAIL_ADDRESS) {
			array_push($errors, "Email Address should be " . MIN_USER_EMAIL_ADDRESS . " to " . MAX_USER_EMAIL_ADDRESS . " characters.");
		} else if(!valid_email($email)) {
			array_push($errors, "Invalid email address.");
		} else if($is_edit && $this->users_model->isExistingEmail($email, $user_id)) {
			array_push($errors, "Email already exists.");
		}

		if(empty($access_level)) {
			array_push($errors, "Access Level" . $is_required_suffix);
		}

		if(empty($user_status) && strcmp('0', $user_status) != 0) {
			array_push($errors, "User Status" . $is_required_suffix);
		}

		if($is_edit && (int)$mr_flag == NON_MR_FLAG) {
			$user = $this->users_model->getUser($user_id);

			if((int)$user->mr_flag == MR_FLAG && (int)$mr_flag == NON_MR_FLAG) {
				array_push($errors, 'Cannot remove Management Representative role. Please create/edit another user to do so.');
			}
		}

		if(empty($errors)) {
			if((int)$user_status == USER_STATUS_INACTIVE_FLAG && (int)$mr_flag == MR_FLAG) {
				array_push($errors, 'Cannot set an inactive user as Management Representative.');
			}
		}

		return $errors;
	}

	private function convertForm($form) {
		$user = new stdClass();
		
		if(isset($form['user_id'])) {
			$user->id = $form['user_id'];
		}
		
		$user->first_name = $form['fname'];
		$user->middle_name = $form['mname'];
		$user->last_name = $form['lname'];
		$user->email_address = $form['email'];
		$user->team = $form['team'];
		
		$user->team_lead = $form['team_lead'];
		//Fix when user saves a record without editing the team_lead
		//in this case, team_lead value will look something like [{"id":"0000","text":"Full Name"}]
		//so we must extract the id from such json value
		if(!ctype_digit($user->team_lead)) {
			$team_lead_obj = json_decode($user->team_lead);
			if($team_lead_obj != null) {
				$user->team_lead = $team_lead_obj[0]->id;
			}
		}

		if(empty($user->team_lead)) {
			$user->team_lead = NULL;
		}

		$user->position_title = $form['pos_title'];
		$user->location = $form['location'];
		$user->ims_flag = isset($form['ims_flag']) ? true : false;
		$user->mr_flag = isset($form['mr_flag']) ? true : false;
		$user->access_level = $form['access_level'];
		$user->status = $form['user_status'];

		return $user;
	}
}