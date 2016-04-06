<?php
ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class Review_history_model extends CI_Model {

	public $tbl_user = 'user';
	public $tbl_review_history = 'review_history';

	public function __construct() {
		$this->load->database();
	}

	public function getReviewHistory($cpar_no) {
		$this->db->select(
			'review_history.*, ' . 
			'CONCAT_WS(" ", rev_by.first_name, rev_by.middle_name, rev_by.last_name) as reviewed_by_name', false);
		$this->db->from($this->tbl_review_history);
		$this->db->where('review_history.cpar_no', $cpar_no);
		$this->db->join($this->tbl_user . ' as rev_by', 'rev_by.id = review_history.reviewed_by', 'left');
		$this->db->order_by('review_history.reviewed_date', 'DESC');
		$this->db->order_by('review_history.id', 'DESC');

		$query = $this->db->get();
		
		if($query) {    
			return $query->result_array();
		} else {
			return array();
		} 
	}

	public function insertReviewHistory($review_history) {
		$ret = new stdClass();
		$dbret = $this->db->insert($this->tbl_review_history, $review_history);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting record.';
	    } else {
	    	$ret->success = true;
	    }

	    return $ret;
	}
	
	public function updateReviewDate($id, $date) {
		$arr = array('reviewed_date' => $date);
	
		$this->db->where('id', $id);

		$dbret = $this->db->update($this->tbl_review_history, $arr);
		
		return $dbret;
	}
}