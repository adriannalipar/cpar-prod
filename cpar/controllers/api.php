<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends MY_Controller {

	public function __construct() {
		parent::__construct();
	
		$this->load->model('cpar_model');
		$this->load->model('review_history_model');
		$this->load->model('ff_up_history_model');
		$this->load->model('addressee_fields_model');
	}

	public function updateDateFiled() {
		if($this->can_edit_dates) {
			$cpar_no = $this->input->post('cpar_no');
			$date_filed = $this->input->post('date_filed');
					
			if($cpar_no && $date_filed) {
				$result = $this->cpar_model->updateDateFiled($cpar_no, $date_filed);
				if($result) {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => true, 'message' => 'Successfully updated Date Filed.')));
				} else {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => false, 'message' => 'Error updating Date Filed.')));
				}
			} else {
				$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => false, 'message' => 'Date Filed is required.')));
			}
		} else {
			$this->output->set_status_header('401');
		}
	}
	
	public function updateReviewDate() {
		if($this->can_edit_dates) {
			$id = $this->input->post('id');
			$date = $this->input->post('date');
			
			if($id && $date) {
				if($this->review_history_model->updateReviewDate($id, $date)) {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => true)));
				} else {
					$this->output->set_status_header('500');
				}
			} else {
				$this->output->set_status_header('500');
			}
		}
	}	
	
	public function updateFFDate() {
		if($this->can_edit_dates) {
			$id = $this->input->post('id');
			$date = $this->input->post('date');
			
			if($id && $date) {
				if($this->ff_up_history_model->updateFFDate($id, $date)) {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => true)));
				} else {
					$this->output->set_status_header('500');
				}
			} else {
				$this->output->set_status_header('500');
			}
		}
	}
	
	public function updateNextFFDate() {
		if($this->can_edit_dates) {
			$id = $this->input->post('id');
			$date = $this->input->post('date');
			
			if($id && $date) {
				if($this->ff_up_history_model->updateNextFFDate($id, $date)) {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => true)));
				} else {
					$this->output->set_status_header('500');
				}
			} else {
				$this->output->set_status_header('500');
			}
		}
	}

	public function updateActionDueDate() {
		if($this->can_edit_dates) {
			$id = $this->input->post('id');
			$date = $this->input->post('date');
			
			if($id && $date) {
				if($this->addressee_fields_model->updateActionDueDate($id, $date)) {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => true)));
				} else {
					$this->output->set_status_header('500');
				}
			} else {
				$this->output->set_status_header('500');
			}
		}
	}

	public function updateActionCompletedDate() {
		if($this->can_edit_dates) {
			$id = $this->input->post('id');
			$date = $this->input->post('date');
			
			if($id && $date) {
				if($this->addressee_fields_model->updateActionCompletedDate($id, $date)) {
					$this->output->set_content_type('application/json')->set_output(json_encode(array('result' => true)));
				} else {
					$this->output->set_status_header('500');
				}
			} else {
				$this->output->set_status_header('500');
			}
		}
	}

}