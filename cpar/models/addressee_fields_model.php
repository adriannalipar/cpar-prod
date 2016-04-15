<?php

class Addressee_fields_model extends CI_Model {

	public $tbl_user = 'user';
	public $tbl_addressee_fields = 'addressee_fields';
	public $tbl_action_plan_details = 'action_plan_details';

	public function __construct() {
		$this->load->database();
		$this->load->model('cpar_model');
	}

	public function isExisting($cpar_no) {
		$this->db->select('*', false);
		$this->db->from($this->tbl_addressee_fields);
		$this->db->where('cpar_no', $cpar_no);

		$query = $this->db->get();
		
		return $query->row();
	}

	public function insertAddresseeFields($addressee_fields) {
		$ret = new stdClass();

		$dbret = $this->db->insert($this->tbl_addressee_fields, $addressee_fields);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting record.';
			if($this->db->_error_number() == DB_ERROR_DUPLICATE) {
				$ret->success = false;
				$ret->error = 'CPAR number already exists.';
			}
	    } else {
	    	#UPDATE CPAR MAIN RECORD UPDATE DATE
	    	$cpar = new stdClass();
	    	$cpar->updated_date = date('Y-m-d H:i:s');	    	
			$this->cpar_model->updateCpar($cpar, $addressee_fields->cpar_no);
	    	
	    	$ret->success = true;
	    }

	    return $ret;
	}

	public function updateAddresseeFields($addressee_fields) {
		$ret = new stdClass();
		$this->db->where('cpar_no', $addressee_fields->cpar_no);

		$dbret = $this->db->update($this->tbl_addressee_fields, $addressee_fields);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while updating record.';
			if($this->db->_error_number() == DB_ERROR_DUPLICATE) {
				$ret->error = 'CPAR number already exists.';
			}
	    } else {
	    	#UPDATE CPAR MAIN RECORD UPDATE DATE
	    	$cpar = new stdClass();
	    	$cpar->updated_date = date('Y-m-d H:i:s');	    	
			$this->cpar_model->updateCpar($cpar, $addressee_fields->cpar_no);
				    	
	    	$ret->success = true;
	    }

	    return $ret;
	}

	public function getAddresseeFields($cpar_no) {
		$addressee_fields = null;

		$this->db->select(
			'addressee_fields.*, ' . 
			'CONCAT_WS(" ", rad.first_name, rad.middle_name, rad.last_name) as rad_implemented_by_name, ' . 
			'CONCAT_WS(" ", rca.first_name, rca.middle_name, rca.last_name) as rca_investigated_by_name, ' . 
			'CONCAT_WS(" ", cp.first_name, cp.middle_name, cp.last_name) as proposed_by_name'
			, FALSE);
		$this->db->from($this->tbl_addressee_fields);
		$this->db->join($this->tbl_user . ' as rad', 'rad.id = addressee_fields.rad_implemented_by', 'left');
		$this->db->join($this->tbl_user . ' as rca', 'rca.id = addressee_fields.rca_investigated_by', 'left');
		$this->db->join($this->tbl_user . ' as cp', 'cp.id = addressee_fields.proposed_by', 'left');

		$this->db->where('addressee_fields.cpar_no', $cpar_no);

		$query = $this->db->get();
		$addressee_fields = $query->row();
		
		return $this->cleanAddresseeFields($addressee_fields);
	}

	public function getActionPlanDetails($cpar_no) {
		$this->db->select('action_plan_details.*, CONCAT_WS(" ", resp_per.first_name, resp_per.middle_name, resp_per.last_name) as responsible_person_name', false);
		$this->db->from($this->tbl_action_plan_details);
		$this->db->join($this->tbl_user . ' as resp_per', 'resp_per.id = action_plan_details.responsible_person', 'left');
		$this->db->where('action_plan_details.cpar_no', $cpar_no);

		$query = $this->db->get();
		
		return $query->result_array();
	}

	public function deleteActionPlanDetails($cpar_no) {
		return $this->db->delete($this->tbl_action_plan_details, array('cpar_no' => $cpar_no));
	}

	public function deleteActionPlanDetailsByIds($task_ids) {
		$this->db->where_in('id', $task_ids);
		return $this->db->delete($this->tbl_action_plan_details);
	}

	public function batchInsertActionPlanDetails($batch) {
		$ret = new stdClass();
		
		$a_ids = array();
		
		foreach($batch as $insert_data) {
			$dbret = $this->db->insert($this->tbl_action_plan_details, $insert_data);
			if (!$dbret) {
				break;
			} else {
				$a_ids[] = $this->db->insert_id();
			}
		}
				
		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting records.';
	    } else {
	    	#UPDATE CPAR MAIN RECORD UPDATE DATE
	    	if(count($batch) > 0 && isset($batch[0]['cpar_no'])) {
		    	$cpar = new stdClass();
		    	$cpar->updated_date = date('Y-m-d H:i:s');	    	
				$this->cpar_model->updateCpar($cpar, $batch[0]['cpar_no']);
	    	}

	    	$ret->success = true;
	    	$ret->ids = $a_ids;
	    }
		
	    return $ret;
	}

	public function getResponsiblePersons($cpar_no) {
		$arr = array();

		$this->db->select('responsible_person', FALSE);
		$this->db->from($this->tbl_action_plan_details);
		$this->db->where('cpar_no', $cpar_no);

		$query = $this->db->get();
		$arr = $query->result_array();

		return $arr;
	}

	public function getSingleActionPlanDetail($task_id) {
		$this->db->select('action_plan_details.*, CONCAT_WS(" ", resp_per.first_name, resp_per.middle_name, resp_per.last_name) as responsible_person_name', false);
		$this->db->from($this->tbl_action_plan_details);
		$this->db->join($this->tbl_user . ' as resp_per', 'resp_per.id = action_plan_details.responsible_person', 'left');
		$this->db->where('action_plan_details.id', $task_id);

		$query = $this->db->get();		
		return $query->row();
	}

	public function updateActionPlanDetails($action_plan_details, $task_id) {
		$ret = new stdClass();
		$this->db->where('id', $task_id);

		$dbret = $this->db->update($this->tbl_action_plan_details, $action_plan_details);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while updating record.';
	    } else {
	    	#UPDATE CPAR MAIN RECORD UPDATE DATE
	    	#get cpar_no
			$this->db->select('cpar_no');
			$this->db->from($this->tbl_action_plan_details);
			$this->db->where('id', $task_id);

			if($query = $this->db->get()) {
				if($obj = $query->row()) {
			    	$cpar = new stdClass();
			    	$cpar->updated_date = date('Y-m-d H:i:s');	    	
					$this->cpar_model->updateCpar($cpar, $obj->cpar_no);
				}
			}

	    	$ret->success = true;
	    }

	    return $ret;
	}
	
	public function update_proposed_by_date($cpar_no) {
		$proposed_date = NULL;
	
		#get addressee fields
		$this->db->select('action, proposed_by_date');
		$this->db->from($this->tbl_addressee_fields);
		$this->db->where('cpar_no',$cpar_no);
		
		if($addr_query = $this->db->get()) {
			if($addressee_fields = $addr_query->row()) {
				$addressee_fields_action = trim($addressee_fields->action);
			
				if($addressee_fields->action && !empty($addressee_fields_action)) {
					$this->db->select('id');
					$this->db->from($this->tbl_action_plan_details);
					$this->db->where('cpar_no', $cpar_no);
					
					if($apd_query = $this->db->get()) {
						if($apd_query->num_rows() > 0) {
							$proposed_date = date('Y-m-d');
						}
					}
				}
			}
		}
		
		$b_update = FALSE;
		
		if(!$proposed_date) {
			$b_update = TRUE;
		} else {
			if(!$addressee_fields->proposed_by_date) {
				$b_update = TRUE;
			}
		}
		
		if($b_update) {
			$this->db->where('cpar_no', $cpar_no);
			$to_update = array('proposed_by_date' => $proposed_date);
			$dbret = $this->db->update($this->tbl_addressee_fields, $to_update);
		}
	}
	
	public function updateActionDueDate($id, $date) {
		$arr = array('due_date' => $date);
	
		$this->db->where('id', $id);

		$dbret = $this->db->update($this->tbl_action_plan_details, $arr);
		
		return $dbret;
	}

	public function updateActionCompletedDate($id, $date) {
		$arr = array('completed_date' => $date);
	
		$this->db->where('id', $id);

		$dbret = $this->db->update($this->tbl_action_plan_details, $arr);
		
		return $dbret;
	}

	private function cleanAddresseeFields($addressee_fields) {
	    if (isset($addressee_fields) && !empty($addressee_fields)) {
	        $addressee_fields->rad_action = htmlspecialchars($addressee_fields->rad_action);
	        $addressee_fields->rad_implemented_by = htmlspecialchars($addressee_fields->rad_implemented_by);
	        $addressee_fields->rca_tools_others = htmlspecialchars($addressee_fields->rca_tools_others);
	        $addressee_fields->rca_details = htmlspecialchars($addressee_fields->rca_details);
	        $addressee_fields->rca_investigated_by = htmlspecialchars($addressee_fields->rca_investigated_by);
	        $addressee_fields->action = htmlspecialchars($addressee_fields->action);
	        $addressee_fields->proposed_by_name = htmlspecialchars($addressee_fields->proposed_by_name);
	    }

	    return $addressee_fields;
	}

}