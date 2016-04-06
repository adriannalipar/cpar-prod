<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Error extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->output->set_status_header('404');
        $this->load->view('404');
	}

	public function err_403() {
		$this->output->set_status_header('403');
        $this->load->view('403');
	}
}