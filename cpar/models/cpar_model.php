<?php
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);

class Cpar_model extends CI_Model {

	public $tbl_cpar_main = 'cpar_main';
	public $tbl_user = 'user';
	public $tbl_team = 'team';
	public $tbl_raaro = 'raised_as_a_result_of';
	public $tbl_process = 'process';
	public $tbl_rca_tools = 'rca_tools';
	public $tbl_action_plan_details = 'action_plan_details';

	public function __construct() {
		$this->load->database();
	}

	public function getGenericList($tbl_name) {
		$this->db->order_by('name', 'ASC');
		$query = $this->db->get($tbl_name);
		
		$result_array = array();
		
		if($query) {
			$result_array = $query->result_array();
			
			if($tbl_name == $this->tbl_raaro) {
				$new_array = array();
				$others = NULL;
			
				foreach($result_array as $item) {
					if($item['name'] == RAISED_AS_A_RESULT_OF_OTHERS) {
						$others = $item;
					} else {
						$new_array[] = $item;	
					}
				}
				
				if($others) {
					$new_array[] = $others;
				}
				
				$result_array = $new_array;
			}

		}
	
		return $result_array;
	}

	public function getIdSortedGenericList($tbl_name) {
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get($tbl_name);

		return $query->result_array();
	}

	public function searchCparList($filters, $rpp, $pn, $sort_by, $sort, $stage, $access) {
		$arr = array();

		$this->db->select('DISTINCT cpar_main.id, cpar_main.created_date, cpar_main.date_filed, ' . 
			'CONCAT_WS(" ", req.first_name, req.middle_name, req.last_name) as req_name,' . 
			'CONCAT_WS(" ", adr.first_name, adr.middle_name, adr.last_name) as adr_name,' . 
			'raaro.name as raaro_name, cpar_main.title, cpar_main.status, cpar_main.sub_status, cpar_main.date_due, cpar_main.ff_up_date, cpar_main.closure_date'
			, FALSE);
		$this->db->from($this->tbl_cpar_main);
		$this->db->join($this->tbl_user . ' as req', 'req.id = cpar_main.requestor', 'left');		
		$this->db->join($this->tbl_user . ' as adr', 'adr.id = cpar_main.addressee', 'left');
		$this->db->join($this->tbl_raaro . ' as raaro', 'raaro.id = cpar_main.raised_as_a_result_of', 'left');
		$this->db->join($this->tbl_action_plan_details . ' as apd', 'apd.cpar_no = cpar_main.id AND apd.responsible_person = '
		 . $access->id, 'left', FALSE);
		$this->db->order_by($sort_by, $sort);

		if(strcmp(strval($rpp), 'All') != 0) {
			$this->db->limit($rpp, (intval($pn) - 1) * $rpp);
		}
		
		//filters
		if(!empty($filters)) {
			$this->addFilters($filters);
		}

		//stage filter
		if(strcmp($stage, CPAR_TAB_ALL) != 0) {
			$this->db->where('cpar_main.status', $stage);
		}

		//permission filter
		$this->addPermissionFilters($stage, $access);

		//do not show deleted records
		$this->db->where('cpar_main.is_deleted !=', DELETED_FLAG);

		$query = $this->db->get();

		$arr = $query->result_array();

		return $arr;
	}

	public function countSearchCparList($filters, $stage, $access) {
		$this->db->select('COUNT(DISTINCT cpar_main.id) as count', false);
		$this->db->from($this->tbl_cpar_main);
		$this->db->join($this->tbl_user . ' as req', 'req.id = cpar_main.requestor', 'left');
		$this->db->join($this->tbl_user . ' as adr', 'adr.id = cpar_main.addressee', 'left');
		$this->db->join($this->tbl_raaro . ' as raaro', 'raaro.id = cpar_main.raised_as_a_result_of', 'left');
		$this->db->join($this->tbl_action_plan_details . ' as apd', 'apd.cpar_no = cpar_main.id AND apd.responsible_person = '
		 . $access->id, 'left', FALSE);

		//filters
		if(!empty($filters)) {
			$this->addFilters($filters);
		}

		//stage filter
		if(strcmp($stage, CPAR_TAB_ALL) != 0) {
			$this->db->where('cpar_main.status', $stage);
		}

		//permission filter
		$this->addPermissionFilters($stage, $access);

		//do not show deleted records
		$this->db->where('cpar_main.is_deleted !=', DELETED_FLAG);

		$query = $this->db->get();
		$obj = $query->row();

		return $obj->count;
	}

	public function getAddrReqData($id) {
		$user = null;

		$this->db->select(
			'team.name as team_name, team_lead.first_name as t_first_name, team_lead.middle_name as t_middle_name, ' . 
			'team_lead.last_name as t_last_name, ' . 
			'team.id as team_id, ' . 
			'team_lead.id as team_lead_id', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->join($this->tbl_team, 'team.id = user.team', 'left');
		$this->db->join($this->tbl_user . ' as team_lead', 'team_lead.id = user.team_lead', 'left');
		$this->db->where('user.id', $id);

		$query = $this->db->get();
		$user = $query->row();

		return $user;
	}

	public function getRequestorData($id) {
		$user = null;

		$this->db->select(
			'user.first_name, user.middle_name, user.last_name, team.name as team_name, ' . 
			'team_lead.first_name as t_first_name, team_lead.middle_name as t_middle_name, ' . 
			'team_lead.last_name as t_last_name, ' . 
			'team.id as team_id, ' . 
			'team_lead.id as team_lead_id', FALSE);
		$this->db->from($this->tbl_user);
		$this->db->join($this->tbl_team, 'team.id = user.team', 'left');
		$this->db->join($this->tbl_user . ' as team_lead', 'team_lead.id = user.team_lead', 'left');
		$this->db->where('user.id', $id);

		$query = $this->db->get();
		$user = $query->row();

		return $user;
	}

	public function getNextSeriesId($cpar_type) {
		$next_id = 1;

		$this->db->select('series_no', FALSE);
		$this->db->from($this->tbl_cpar_main);
		$this->db->where('type', $cpar_type);
		$this->db->order_by('series_no', 'desc');

		$query = $this->db->get();
		$user = $query->row();

		if(!($user == null || empty($user))) {
			$next_id = $user->series_no;
			$next_id++;
		}

		return $next_id;
	}

	public function insertCpar($cpar) {
		$ret = new stdClass();
		$dbret = $this->db->insert($this->tbl_cpar_main, $cpar);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting record.';
			if($this->db->_error_number() == DB_ERROR_DUPLICATE) {
				$ret->success = false;
				$ret->error = 'CPAR number already exists.';
			}
	    } else {
	    	$ret->success = true;
	    }

	    return $ret;
	}

	public function getCpar($cpar_no) {
		$cpar = null;

		$this->db->select(
			'cpar_main.*, a_tl.id as addressee_team_lead, ' . 
			'CONCAT_WS(" ", r.first_name, r.middle_name, r.last_name) as requestor_name, ' . 
			'r.email_address as requestor_email, ' .
			'CONCAT_WS(" ", a.first_name, a.middle_name, a.last_name) as addressee_name, ' .
			'a.email_address as addressee_email, ' .
			'CONCAT_WS(" ", r_tl.first_name, r_tl.middle_name, r_tl.last_name) as requestor_team_lead_name, ' .
			'r_tl.email_address as requestor_team_lead_email, ' .
			'CONCAT_WS(" ", a_tl.first_name, a_tl.middle_name, a_tl.last_name) as addressee_team_lead_name, ' .
			'a_tl.email_address as addressee_team_lead_email, ' .
			'CONCAT_WS(" ", pb_user.first_name, pb_user.middle_name, pb_user.last_name) as pb_user_name, ' .
			'CONCAT_WS(" ", ims_user.first_name, ims_user.middle_name, ims_user.last_name) as assigned_ims_name'
			, FALSE);
		$this->db->from($this->tbl_cpar_main);
		$this->db->join($this->tbl_user . ' as r', 'r.id = cpar_main.requestor', 'left');
		$this->db->join($this->tbl_user . ' as a', 'a.id = cpar_main.addressee', 'left');
		$this->db->join($this->tbl_user . ' as r_tl', 'r_tl.id = cpar_main.requestor_team_lead', 'left');
		$this->db->join($this->tbl_user . ' as a_tl', 'a_tl.id = cpar_main.addressee_team_lead', 'left');
		$this->db->join($this->tbl_user . ' as pb_user', 'pb_user.id = cpar_main.pb_user', 'left');
		$this->db->join($this->tbl_user . ' as ims_user', 'ims_user.id = cpar_main.assigned_ims', 'left');

		$this->db->where('cpar_main.id', $cpar_no);

		$query = $this->db->get();

		$cpar = $query->row();

		return $cpar;
	}

	public function updateCpar($cpar, $id) {
		$ret = new stdClass();
		$this->db->where('id', $id);

		$dbret = $this->db->update($this->tbl_cpar_main, $cpar);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while updating record.';
			if($this->db->_error_number() == DB_ERROR_DUPLICATE) {
				$ret->error = 'Email address already exists.';
			}
	    } else {
	    	$ret->success = true;
	    }

	    return $ret;
	}

	public function isNotDeleted($id) {
		$this->db->select('COUNT(*) as count', FALSE);
		$this->db->from($this->tbl_cpar_main);
		$this->db->where('id', $id);
		$this->db->where('is_deleted != ', DELETED_FLAG);

		$query = $this->db->get();
		$obj = $query->row();

		return ($obj != null && isset($obj->count) && $obj->count > 0);
	}

	public function deleteCpar($id) {
		$cpar = new stdClass();
		$cpar->is_deleted = DELETED_FLAG;

		$ret = false;
		$this->db->where('id', $id);
		$dbret = $this->db->update($this->tbl_cpar_main, $cpar);

		if ($dbret) {
			$ret = true;
	    }

	    return $ret;
	}

	private function addFilters($filters) {
		$cpar_no = isset($filters['cpar_no']) ? $filters['cpar_no'] : '';
		$ar_type = isset($filters['ar_type']) ? $filters['ar_type'] : '';
		$title = isset($filters['title']) ? $filters['title'] : '';
		$requestor = isset($filters['requestor']) ? $filters['requestor'] : '';
		$addressee = isset($filters['addressee']) ? $filters['addressee'] : '';
		$dcreated_from = isset($filters['dcreated_from']) ? $filters['dcreated_from'] : '';
		$dcreated_to = isset($filters['dcreated_to']) ? $filters['dcreated_to'] : '';
		$ddue_from = isset($filters['ddue_from']) ? $filters['ddue_from'] : '';
		$ddue_to = isset($filters['ddue_to']) ? $filters['ddue_to'] : '';

		if(!empty($cpar_no)) {
			$this->db->like('UPPER(cpar_main.id)', strtoupper($cpar_no), FALSE);
		}

		if(strcmp($ar_type, DDVAL_GENERAL_ALL) != 0) {
			$this->db->where('type', $ar_type);
		}

		if(!empty($title)) {
			$this->db->like('UPPER(cpar_main.title)', strtoupper($title), FALSE);
		}

		if(!(empty($dcreated_from) || empty($dcreated_to))) {
			$dcreated_from_date = new DateTime($dcreated_from);
			$dcreated_to_date = new DateTime($dcreated_to);

			$this->db->where('(DATE(cpar_main.created_date) >= "' . $dcreated_from_date->format('Y-m-d') . '" AND ' . 
							 'DATE(cpar_main.created_date) <= "' . $dcreated_to_date->format('Y-m-d') . '")');
		} else if(!empty($dcreated_from)) {
			$dcreated_from_date = new DateTime($dcreated_from);
			$this->db->where('DATE(cpar_main.created_date) >=', $dcreated_from_date->format('Y-m-d'));
		} else if(!empty($dcreated_to)) {
			$dcreated_to_date = new DateTime($dcreated_to);
			$this->db->where('DATE(cpar_main.created_date) <=', $dcreated_to_date->format('Y-m-d'));
		}

		if(!(empty($ddue_from) || empty($ddue_to))) {
			$ddue_from_date = new DateTime($ddue_from);
			$ddue_to_date = new DateTime($ddue_to);

			$this->db->where('(DATE(cpar_main.date_due) >= "' . $ddue_from_date->format('Y-m-d') . '" AND ' . 
							 'DATE(cpar_main.date_due) <= "' . $ddue_to_date->format('Y-m-d') . '")');
		} else if(!empty($ddue_from)) {
			$ddue_from_date = new DateTime($ddue_from);
			$this->db->where('DATE(cpar_main.date_due) >=', $ddue_from_date->format('Y-m-d'));
		} else if(!empty($ddue_to)) {
			$ddue_to_date = new DateTime($ddue_to);
			$this->db->where('DATE(cpar_main.date_due) <=', $ddue_to_date->format('Y-m-d'));
		}

		if(!empty($requestor)) {
			$this->db->where('(REPLACE(UPPER(CONCAT_WS(" ", req.first_name, req.middle_name, req.last_name)), " ", "") LIKE "%' . 
				str_replace(' ', '', strtoupper($requestor)) . '%" OR ' .
				'REPLACE(UPPER(CONCAT_WS(" ", req.first_name, req.last_name)), " ", "") LIKE "%' . 
				str_replace(' ', '', strtoupper($requestor)) . '%")');
		}

		if(!empty($addressee)) {
			$this->db->where('(REPLACE(UPPER(CONCAT_WS(" ", adr.first_name, adr.middle_name, adr.last_name)), " ", "") LIKE "%' . 
				str_replace(' ', '', strtoupper($addressee)) . '%" OR ' .
				'REPLACE(UPPER(CONCAT_WS(" ", adr.first_name, adr.last_name)), " ", "") LIKE "%' . 
				str_replace(' ', '', strtoupper($addressee)) . '%")');
		}
	}

	private function addPermissionFilters($stage, $access) {
		if($access == null) {
			//return nothing
			$this->db->where('cpar_main.id', null);
		} else {
			$l_id = $this->session->userdata('loggedIn');
			$allowed_access_partial = ($access->mr_flag == MR_FLAG || $access->access_level == ACCESS_LEVEL_ADMIN_FLAG);

			//get responsible persons
			$resp_persons = array();

			$s1_query = 
				'((
					/* Draft */
					cpar_main.status = 1 
					AND (cpar_main.sub_status = "' . CPAR_MINI_STATUS_DRAFT . '") 
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				) OR (
					/* For IMS Review, Pushed Back, Closed */
					cpar_main.status = 1 
					AND (cpar_main.sub_status = "' . CPAR_MINI_STATUS_FOR_IMS_REVIEW . '"' .
						' OR cpar_main.sub_status = "' . CPAR_MINI_STATUS_PUSHED_BACK . '"' .
						' OR cpar_main.sub_status = "' . CPAR_MINI_STATUS_CLOSED . '")
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				))';

			$s2_query = 
				'((
					/* 2A - For Addresse Input */
					cpar_main.status = 2 
					AND cpar_main.sub_status = "' . CPAR_MINI_STATUS_S2_2A1 . '"
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.addressee = ' . $l_id . ' OR 
						 cpar_main.addressee_team_lead = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				) OR (
					/* 2A - Pushed Back, 2B - For TL Review */
					cpar_main.status = 2 
					AND (cpar_main.sub_status = "' . CPAR_MINI_STATUS_S2_2A2 . '" 
							OR cpar_main.sub_status = "' . CPAR_MINI_STATUS_S2_2B . '") 
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.addressee = ' . $l_id . ' OR 
						 cpar_main.addressee_team_lead = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ') 
				))';

			$s3_query = 
				'((
					/* For IMS Review */
					cpar_main.status = 3 
					AND cpar_main.sub_status = "' . CPAR_MINI_STATUS_S3_3A . '"
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.addressee = ' . $l_id . ' OR 						 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				) OR (
					/* For AP Implementation */
					cpar_main.status = 3 
					AND (cpar_main.sub_status = "' . CPAR_MINI_STATUS_S3_3B . '" 
							OR cpar_main.sub_status = "' . CPAR_MINI_STATUS_S3_3B2 . '") 
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.addressee = ' . $l_id . ' OR 						 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR 
						 apd.cpar_no IS NOT NULL OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				))';

			$s4_query = 
				'((
					/* For Effectiveness Verification, For MR Closure */
					cpar_main.status = 4 
					AND (cpar_main.sub_status = "' . CPAR_MINI_STATUS_S4_4A . '" 
						OR cpar_main.sub_status = "' . CPAR_MINI_STATUS_S4_4B . '"
						OR cpar_main.sub_status = "' . CPAR_MINI_STATUS_S4_4A2 . '") 
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.addressee = ' . $l_id . ' OR 						 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR 
						 apd.cpar_no IS NOT NULL OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				))';

			$s5_query = 
				'((
					/* Closed */
					cpar_main.status = 5 
					AND cpar_main.sub_status = "' . CPAR_MINI_STATUS_S5_5A . '"
					AND (cpar_main.created_by = ' . $l_id . ' OR 
						 cpar_main.requestor = ' . $l_id . ' OR 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.addressee = ' . $l_id . ' OR 						 
						 cpar_main.requestor_team_lead = ' . $l_id . ' OR 
						 cpar_main.assigned_ims = ' . $l_id . ' OR 
						 apd.cpar_no IS NOT NULL OR 
						' . ($allowed_access_partial ? 'TRUE' : 'FALSE') . ')
				))';

			if(strcmp($stage, CPAR_TAB_ALL) == 0) {
				$all_query = '(' . $s1_query . ' OR ' . $s2_query . ' OR ' . $s3_query . ' OR ' . $s4_query . ' OR ' . $s5_query . ')';
				$this->db->where($all_query);
			} else if(intval($stage) == CPAR_STAGE_1) {
				$this->db->where($s1_query);
			} else if(intval($stage) == CPAR_STAGE_2) {
				$this->db->where($s2_query);
			} else if(intval($stage) == CPAR_STAGE_3) {
				$this->db->where($s3_query);
			} else if(intval($stage) == CPAR_STAGE_4) {
				$this->db->where($s4_query);
			} else if(intval($stage) == CPAR_STAGE_5) {
				$this->db->where($s5_query);
			}
		}
	}

	public function getAssignedIMS($cpar_no) {
		$this->db->select('assigned_ims', FALSE);
		$this->db->from($this->tbl_cpar_main);
		$this->db->where('id', $cpar_no);

		$query = $this->db->get();
		$obj = $query->row();

		return ($obj != null && isset($obj->assigned_ims)) ? $obj->assigned_ims : '';
	}
	
	public function getCparsForReminder() {
		$result = NULL;
		
		$this->db->select('id, addressee, addressee_team_lead, requestor, requestor_team_lead, status, sub_status, assigned_ims, date_due, ff_up_date');
		$this->db->from($this->tbl_cpar_main);
		$statuses = array(CPAR_MINI_STATUS_DRAFT, CPAR_MINI_STATUS_S5_5A);
		$this->db->where_not_in('sub_status', $statuses);
		$this->db->where('is_deleted','0');
		$this->db->where('date_due',date('Y-m-d', strtotime('+1 day')));

		$query = $this->db->get();

		if($query) {
			$result = $query->result();
		}

		return $result;
	}

	public function searchCparCsvList($filters, $rpp, $pn, $sort_by, $sort, $stage, $access) {
		$arr = array();

		$this->db->select('DISTINCT 
			cpar_main.id as `' . CSV_HEADER_CPAR_NO . '`, 
			cpar_main.created_date as `' . CSV_HEADER_DATE_CREATED . '`,
			CONCAT_WS(" ", req.first_name, req.middle_name, req.last_name) as `' . CSV_HEADER_ORIGINATOR . '`,
			CONCAT_WS(" ", adr.first_name, adr.middle_name, adr.last_name) as `' . CSV_HEADER_ADDRESSEE . '`,
			team.name as `' . CSV_HEADER_TEAM . '`,
			raaro.name as `' . CSV_HEADER_FAARO . '`, 
			cpar_main.title as `' . CSV_HEADER_TITLE . '`, 
			cpar_main.status as `' . CSV_HEADER_STAGE . '`, 
			cpar_main.sub_status as `' . CSV_HEADER_STATUS . '`, 
			cpar_main.date_due as `' . CSV_HEADER_DUE_DATE . '`, 
			cpar_main.ff_up_date as `' . CSV_HEADER_FOLLOW_UP_DATE . '`, 
			cpar_main.closure_date as `' . CSV_HEADER_CLOSURE_DATE . '`'
			, FALSE);
		$this->db->from($this->tbl_cpar_main);
		$this->db->join($this->tbl_user . ' as req', 'req.id = cpar_main.requestor', 'left');		
		$this->db->join($this->tbl_user . ' as adr', 'adr.id = cpar_main.addressee', 'left');
		$this->db->join($this->tbl_team . ' as team', 'team.id = cpar_main.addressee_team', 'left');
		$this->db->join($this->tbl_raaro . ' as raaro', 'raaro.id = cpar_main.raised_as_a_result_of', 'left');
		$this->db->join($this->tbl_action_plan_details . ' as apd', 'apd.cpar_no = cpar_main.id AND apd.responsible_person = '
		 . $access->id, 'left', FALSE);
		$this->db->order_by($sort_by, $sort);

		if(strcmp(strval($rpp), 'All') != 0) {
			$this->db->limit($rpp, (intval($pn) - 1) * $rpp);
		}
		
		//filters
		if(!empty($filters)) {
			$this->addFilters($filters);
		}

		//stage filter
		if(strcmp($stage, CPAR_TAB_ALL) != 0) {
			$this->db->where('cpar_main.status', $stage);
		}

		//permission filter
		$this->addPermissionFilters($stage, $access);

		//do not show deleted records
		$this->db->where('cpar_main.is_deleted !=', DELETED_FLAG);

		$query = $this->db->get();

		$arr = $query->result_array();

		return $arr;
	}
	
	public function updateDateFiled($cpar_no, $date_filed) {
		$arr = array('date_filed' => $date_filed);
	
		$this->db->where('id', $cpar_no);

		$dbret = $this->db->update($this->tbl_cpar_main, $arr);
		
		return $dbret;
	}
}