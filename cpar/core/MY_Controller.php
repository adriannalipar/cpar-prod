<?php

class MY_Controller extends CI_Controller {
    private $user_rights;

	protected $can_edit_dates = FALSE;

    function __construct() {
        parent::__construct();

        $this->load->helper('url');
        $this->load->model('users_model');

        if (!$this->session->userdata('loggedIn')) {
        	if(!($this->router->fetch_class() == 'cpar' && $this->router->fetch_method() == 'link')) {
	        	redirect('http://' . base_url());
        	}
        } else {
	        $page = $this->uri->segment(1);
	        $user = $this->session->userdata('logged_in_user');
	        $this->user_rights = $this->users_model->getUserAccessRights($user->id);
	
			$this->can_edit_dates = $this->users_model->canEditDates($user->id);
	
	        if(strcmp($page, PAGE_USER) == 0) {            
	            if((int)$this->user_rights->access_level != ACCESS_LEVEL_ADMIN_FLAG) {
	                redirect('http://' . base_url() . 'error/err_403');
	            }
	        }
        }
    }

    function show_custom_404() {    	    	
	    $this->output->set_status_header('404');
        $this->load->view('404');
	}

    function addCommonData(&$data) {
        $user = $this->session->userdata('logged_in_user');
        
        $data['is_admin'] = (int)$this->user_rights->access_level == ACCESS_LEVEL_ADMIN_FLAG;
        $data['is_ims'] = (int)$this->user_rights->ims_flag == IMS_FLAG;
        $data['logged_in_id'] = $user->id;
        $data['logged_in_name'] = $user->first_name . ' ' . $user->last_name;
        $data['logged_in_full_name'] = $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name;
    }
}