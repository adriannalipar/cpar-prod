<?php
class Users_model extends CI_Model {

	private $tbl_user = 'user';
	private $tbl_team = 'team';
	private $tbl_location = 'location';

	public function __construct() {
		$this->load->database();
	}

	public function getUserAccessRights($id) {
		$this->db->select('id, ims_flag, mr_flag, access_level', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->where('id', $id);

		$query = $this->db->get();
		$obj = $query->row();

		return $obj;
	}

	public function isExistingID($id) {
		$this->db->select('id, COUNT(id) as count', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->where('id', $id);

		$query = $this->db->get();
		$obj = $query->row();

		return ($obj != null && $obj->count > 0 ? $obj->id : false);
	}

	public function isExistingEmail($email, $id) {
		$this->db->select('*, COUNT(id) as count', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->where('email_address', $email);
		
		if($id != null) {
			$this->db->where('id !=', $id);	
		}

		$query = $this->db->get();
		$obj = $query->row();

		return ($obj != null && $obj->count > 0 ? $obj : false);
	}

	public function searchUserList($filters, $rpp, $pn, $sort_by, $sort) {
		$arr = array();

		$this->db->select('user.id, ' . 
			'CONCAT_WS(" ", user.first_name, user.middle_name, user.last_name) as full_name,' . 
			'user.email_address, team.name as team_name, user.mr_flag, ' . 
			'user.ims_flag, user.access_level, user.status', false);
		$this->db->from($this->tbl_user);
		$this->db->join($this->tbl_team, 'team.id = user.team', 'left');
		$this->db->order_by($sort_by, $sort);

		if(strcmp(strval($rpp), 'All') != 0) {
			$this->db->limit($rpp, (intval($pn) - 1) * $rpp);
		}

		//filters
		if(!empty($filters)) {
			$this->addFilters($filters);
		}

		$query = $this->db->get();
		$arr = $query->result_array();

		return $arr;
	}

	public function countSearchUserList($filters) {
		$this->db->select('COUNT(id) as count', false);
		$this->db->from($this->tbl_user);

		//filters
		if(!empty($filters)) {
			$this->addFilters($filters);
		}

		$query = $this->db->get();
		$obj = $query->row();

		return $obj->count;
	}

	private function addFilters($filters) {
		$name = $filters['name'];
		$email = $filters['email'];
		$role = $filters['role'];
		$access_level = $filters['access_level'];
		$team = $filters['team'];
		$status = $filters['status'];

		if(!empty($name)) {
			$this->db->where('(REPLACE(UPPER(CONCAT_WS(" ", first_name, middle_name, last_name)), " ", "") LIKE "%' . 
				str_replace(' ', '', strtoupper($name)) . '%" OR ' .
				'REPLACE(UPPER(CONCAT_WS(" ", first_name, last_name)), " ", "") LIKE "%' . 
				str_replace(' ', '', strtoupper($name)) . '%")');
		}

		if(!empty($email)) {
			$this->db->like('UPPER(email_address)', strtoupper($email), FALSE);
		}

		if(strcmp($role, DDVAL_GENERAL_ALL) != 0) {
			if(strcmp($role, DDVAL_ROLE_IMS_ONLY) == 0) {
				$this->db->where('ims_flag', IMS_FLAG);
				// $this->db->where('mr_flag', NON_MR_FLAG);
			} else if (strcmp($role, DDVAL_ROLE_MR_ONLY) == 0) {
				$this->db->where('mr_flag', MR_FLAG);
				//$this->db->where('ims_flag', NON_IMS_FLAG);
			} else {
				$this->db->where('ims_flag', NON_MR_FLAG);
				$this->db->where('mr_flag', NON_MR_FLAG);
			}
		}

		if(strcmp($access_level, DDVAL_GENERAL_ALL) != 0) {
			$this->db->where('access_level', $access_level);
		}

		if(strcmp($team, DDVAL_GENERAL_ALL) != 0) {
			$this->db->where('team', $team);
		}

		if(strcmp($status, DDVAL_GENERAL_ALL) != 0) {
			$this->db->where('status', $status);
		}
	}

	public function getTeams() {
		$this->db->order_by('name', 'ASC');
		$query = $this->db->get($this->tbl_team);
		return $query->result_array();
	}	

	public function getLocations() {
		$this->db->order_by('name', 'ASC');
		$query = $this->db->get($this->tbl_location);
		return $query->result_array();
	}

	public function getMRUser() {
		$return = null;
		$condition = array('mr_flag' => 1, 'status' => 1);
		$this->db->select('first_name, middle_name, last_name');
		$query = $this->db->get_where($this->tbl_user, $condition, 1);

		$mrUser = $query->result_array();
		if(!empty($mrUser)) {
			$middle_name = $mrUser[0]['middle_name'];
			$return = $mrUser[0]['first_name'] . (!empty($middle_name) ? ' ' . $middle_name : '') . ' ' . $mrUser[0]['last_name'];
		}

		return $return;
	}
	
	public function getMRUserObject() {
		$return = null;
		$condition = array('mr_flag' => 1, 'status' => 1);
		$this->db->select('id, first_name, middle_name, last_name, email_address');
		$query = $this->db->get_where($this->tbl_user, $condition, 1);

		$mrUsers = $query->result();
		if(!empty($mrUsers)) {
			$return = $mrUsers[0];
		}

		return $return;
	}

	public function getUsers($term, $is_ims = false) {
		$arr = array();

		$this->db->select('id, CONCAT_WS(" ", first_name, middle_name, last_name) as full_name', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->like('UPPER(CONCAT_WS(" ", first_name, middle_name, last_name))', strtoupper($term), FALSE);
		$this->db->limit(TYPEAHEAD_MAX_ROWS);
		
		if($is_ims) {
			$this->db->where('ims_flag', IMS_FLAG);
			$this->db->where('status', USER_STATUS_ACTIVE_FLAG);
		}

		$query = $this->db->get();
				
		if($query) {
			$arr = $query->result_array();
		}
		
		return $arr;
	}

	public function removeMRUserRights($email) {
		$data = array('mr_flag' => NON_MR_FLAG);

		$this->db->where('mr_flag', MR_FLAG);
		$this->db->where('email_address !=', $email);
		$this->db->update($this->tbl_user, $data);
	}

	public function insertUser($user) {
		$ret = new stdClass();

		$dbret = $this->db->insert($this->tbl_user, $user);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting record.';
			if($this->db->_error_number() == DB_ERROR_DUPLICATE) {
				$ret->success = false;
				$ret->error = 'Email address already exists.';
			}
	    } else {
	    	$ret->success = true;
	    	$ret->id = $this->db->insert_id();
	    }

	    return $ret;
	}

	public function updateUser($user) {
		$ret = new stdClass();
		$this->db->where('id', $user->id);
		$dbret = $this->db->update($this->tbl_user, $user);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while updating record.';
			if($this->db->_error_number() == DB_ERROR_DUPLICATE) {				
				$ret->error = 'Email address already exists.';
			}
	    } else {
	    	$ret->success = true;
	    	$ret->id = $user->id;
	    }

	    return $ret;
	}

	public function getUser($id) {
		$user = null;

		$this->db->select('user.*, CONCAT_WS(" ", user.first_name, user.middle_name, user.last_name) AS user_full_name, team.name as team_name, location.name as location_name, 
			CONCAT_WS(" ", team_lead.first_name, team_lead.middle_name, team_lead.last_name) as team_lead_name', FALSE);		
		$this->db->from($this->tbl_user);
		$this->db->join($this->tbl_team, 'team.id = user.team', 'left');
		$this->db->join($this->tbl_location, 'location.id = user.location', 'left');
		$this->db->join($this->tbl_user . ' as team_lead', 'team_lead.id = user.team_lead', 'left');
		$this->db->where('user.id', $id);

		$query = $this->db->get();
		$user = $query->row();

		return $user;
	}

	public function isMR($id) {
		$this->db->select('mr_flag');
		$this->db->from($this->tbl_user);
		$this->db->where('id', $id);

		$query = $this->db->get();
		$user = $query->row();

		$isMR = false;
		if(!($user == null || empty($user)) && (int)$user->mr_flag == MR_FLAG) {
			$isMR = true;
		}

		return $isMR;
	}

	public function isIMS($id) {
		$this->db->select('ims_flag');
		$this->db->from($this->tbl_user);
		$this->db->where('id', $id);

		$query = $this->db->get();
		$user = $query->row();

		$isIMS = false;
		if(!($user == null || empty($user)) && (int)$user->ims_flag == IMS_FLAG) {
			$isIMS = true;
		}

		return $isIMS;
	}
	
	public function canEditDates($id) {
		$this->db->select('access_level');
		$this->db->from($this->tbl_user);
		$this->db->where('id', $id);
		$this->db->where('access_level', ACCESS_LEVEL_ADMIN_FLAG);
		
		$query = $this->db->get();
		
		if($query && $user = $query->row()) {
			return TRUE;
		}
		
		return FALSE;
	}

	public function deleteUser($id) {
		return $this->db->delete($this->tbl_user, array('id' => $id));
	}

	public function countIMSUsers() {
		$count = 0;		

		$this->db->select('COUNT(*) as count', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->where('ims_flag', IMS_FLAG);

		$query = $this->db->get();
		$res = $query->row();

		if(!($res == null || empty($res))) {
			$count = (int)$res->count;
		}

		return $count;
	}

	public function getEmails($admin, $mr, $ims, $id_array) {
		$arr = array();

		$this->db->select('DISTINCT email_address', false);
		$this->db->from($this->tbl_user);
		
		if($admin) {
			$this->db->or_where('access_level', ACCESS_LEVEL_ADMIN_FLAG);
		}

		if($mr) {
			$this->db->or_where('mr_flag', MR_FLAG);
		}

		if($ims) {
			$this->db->or_where('ims_flag', IMS_FLAG);
		}

		if(!($id_array == null || empty($id_array))) {
			$this->db->or_where_in('id', $id_array);
		}

		$query = $this->db->get();
		$arr = $query->result_array();

		return $arr;
	}
	
	public function getAdmins() {
		$a_return = array();
		
		$this->db->select('id, email_address, first_name, last_name, middle_name');
		$this->db->from($this->tbl_user);
		
		$this->db->where('access_level', ACCESS_LEVEL_ADMIN_FLAG);
		$this->db->where('status','1');
		
		if($query = $this->db->get()) {
			$a_return = $query->result_array();
		}
		
		return $a_return;
	}
}