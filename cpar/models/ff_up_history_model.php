<?php
ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class Ff_up_history_model extends CI_Model {

	public $tbl_ff_up_history = 'ff_up_history';

	public function __construct() {
		$this->load->database();
	}

	public function insertFfUpHistory($ff_up_history) {
		$ret = new stdClass();
		$dbret = $this->db->insert($this->tbl_ff_up_history, $ff_up_history);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting record.';
	    } else {
	    	$ret->success = true;
	    }

	    return $ret;
	}

	public function getReviewHistory($cpar_no) {
		$this->db->select('*', false);
		$this->db->from($this->tbl_ff_up_history);
		$this->db->where('cpar_no', $cpar_no);
		$this->db->order_by('id', 'DESC');

		$query = $this->db->get();
		
		return $query->result_array();
	}
	
	public function rollbackFFUpHistory($id) {
		$this->db->delete($this->tbl_ff_up_history, array('id' => $id)); 
	}
	
	public function updateFFDate($id, $date) {
		$arr = array('ff_date' => $date);
	
		$this->db->where('id', $id);

		$dbret = $this->db->update($this->tbl_ff_up_history, $arr);
		
		return $dbret;
	}

	public function updateNextFFDate($id, $date) {
		$arr = array('next_ff_date' => $date);
	
		$this->db->where('id', $id);

		$dbret = $this->db->update($this->tbl_ff_up_history, $arr);
		
		return $dbret;
	}

}