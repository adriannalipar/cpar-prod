<?php
ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

class Audit_log_model extends CI_Model {

	public $tbl_audit_log = 'audit_log';

	public function __construct() {
		$this->load->database();
	}

	public function insertLog($log) {
		$ret = new stdClass();
		$dbret = $this->db->insert($this->tbl_audit_log, $log);

		if (!$dbret) {
			$ret->success = false;
			$ret->error = 'Error while inserting record.';
	    } else {
	    	$ret->success = true;
	    }

	    return $ret;
	}
	
	public function get_action($review_action) {
		$s_return = '';
		switch($review_action) {
			case REVIEW_ACTIONS_MARK_REV:
				$s_return = 'Marked as Reviewed';
				break;
			case REVIEW_ACTIONS_PUSH_BACK:
				$s_return = 'Pushed Back';
				break;
			case REVIEW_ACTIONS_MARK_INV:
				$s_return = 'Marked as Invalid';
				break;
			case REVIEW_ACTIONS_S2_MARK_APPR:
				$s_return = 'Marked as Approved';
				break;
			case REVIEW_ACTIONS_S2_MARK_INV:
				$s_return = 'Marked as Invalid';
				break;
			case REVIEW_ACTIONS_S3_MARK_REV:
				$s_return = 'Marked as Reviewed';
				break;
			case REVIEW_ACTIONS_S3_PUSH_BACK:
				$s_return = 'Pushed Back';
				break;
			case REVIEW_ACTIONS_S3_MARK_IMPL:
				$s_return = 'Marked as Implemented';
				break;
			case REVIEW_ACTIONS_S3_MARK_FF_UP:
				$s_return = 'Marked as for Follow Up';
				break;
			case REVIEW_ACTIONS_S4_MARK_EFF:
				$s_return = 'Marked as Effective';
				break;
			case REVIEW_ACTIONS_S4_PUSH_BACK:
				$s_return = 'Pushed Back';
				break;
			case REVIEW_ACTIONS_S4_MARK_CLOSED:
				$s_return = 'Marked as Closed';
				break;
			case REVIEW_ACTIONS_S4_PUSH_BACK_4A:
				$s_return = 'Pushed Back';
				break;
			
			case REVIEW_ACTIONS_RE_ASSIGN:
				$s_return = 'Re-assigned';
				break;
		}
		
		return $s_return;
	}
}