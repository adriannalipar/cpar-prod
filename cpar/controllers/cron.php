<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->library('email');
		
		$this->load->model('users_model');
		$this->load->model('cpar_model');
	}

	public function index() {
		#get all pending cpars
		## exclude the following sub status = CPAR_MINI_STATUS_DRAFT and status = CPAR_MINI_STATUS_S5_5A
		$results = $this->cpar_model->getCparsForReminder();
		
		if($results) {
			foreach($results as $row) {
				$this->email->generateReminderEmail($row->cpar_no);
			}
		}
	}
}