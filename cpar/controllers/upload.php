<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends MY_Controller {

	function __construct() {
		parent::__construct();
		
		if($this->router->method == 'index') {
			if(!$this->uri->segment(2) || !$this->uri->segment(3)) {
				show_404();
			}
		} elseif($this->router->method == 'task') {
			if(!$this->uri->segment(3)) {
				show_404();
			}
		}
		
	}

	public function index($cpar_no, $uid) {
	
		if(!$cpar_no || !$uid) {
			exit();
		}
						
		$upload_dir = UPLOAD_PATH;
		
		$tmp_dir = $upload_dir.'tmp';
		$tmp_date_dir = $tmp_dir.'/'.date('Ymd');
		$cpar_tmp_dir = $tmp_date_dir.'/'.$cpar_no;
		$uid_dir = $cpar_tmp_dir.'/'.$uid;
		
		#create temp folder if doesn't exist
		if (!file_exists($tmp_dir)) {
			mkdir($tmp_dir, 0777, true);
		}
		
		#create temp folder date if doesn't exist
		if (!file_exists($tmp_date_dir)) {
			mkdir($tmp_date_dir, 0777, true);
		}
		
		chmod($tmp_date_dir, 0777);
		
		#create cpar no in tmp folder if doesn't exist
		if (!file_exists($cpar_tmp_dir)) {
			mkdir($cpar_tmp_dir, 0777, true);
		}
		
		#create uid in tmp folder if doesn't exist
		if (!file_exists($uid_dir)) {
			mkdir($uid_dir, 0777, true);
		}
				
		$get = $this->input->get();
		$filename = '';
		
		
		
		if($get && count($get) > 0) {
			reset($get);
			$last_folder = key($get);

			$type_tmp_dir = $uid_dir.'/'.$last_folder;
			
			if (!file_exists($type_tmp_dir)) {
				mkdir($type_tmp_dir, 0777, true);
			}
			
			$this->upload->initialize(array(
		        'upload_path'   => $type_tmp_dir,
		        'allowed_types' => UPLOAD_ALLOWED_TYPES
		    ));
		    
		    header('Content-Type: application/json');
		    
		    if($upload = $this->upload->do_upload($last_folder)) {
		    	$data = $this->upload->data();
		    	
			    echo json_encode(
					array(
						'success' => true, 
						'filename' => $data['orig_name'],
						'new_filename' => $data['file_name']
					)
				);
		    } else {
			    exit(json_encode(array('success' => false, 'msg' => $this->upload->display_errors('','')))); 
		    }			
		}
	}
	
	public function task($cpar_no) {
	
		if(!$cpar_no) {
			exit();
		}
		
		$uid = uniqid();
				
		$upload_dir = UPLOAD_PATH;
		
		$tmp_dir = $upload_dir.'tmp';
		$tmp_date_dir = $tmp_dir.'/'.date('Ymd');
		$cpar_tmp_dir = $tmp_date_dir.'/'.$cpar_no;
		$uid_dir = $cpar_tmp_dir.'/'.$uid;
		
		#create temp folder if doesn't exist
		if (!file_exists($tmp_dir)) {
			mkdir($tmp_dir, 0777, true);
		}
		
		#create temp folder date if doesn't exist
		if (!file_exists($tmp_date_dir)) {
			mkdir($tmp_date_dir, 0777, true);
		}
		
		#create cpar no in tmp folder if doesn't exist
		if (!file_exists($cpar_tmp_dir)) {
			mkdir($cpar_tmp_dir, 0777, true);
		}
		
		#create uid in tmp folder if doesn't exist
		if (!file_exists($uid_dir)) {
			mkdir($uid_dir, 0777, true);
		}
				
		$get = $this->input->get();
		$filename = '';		
		
		if($get && count($get) > 0) {
			reset($get);
			$last_folder = key($get);

			$type_tmp_dir = $uid_dir.'/'.$last_folder;
			
			if (!file_exists($type_tmp_dir)) {
				mkdir($type_tmp_dir, 0777, true);
			}
			
			$this->upload->initialize(array(
		        'upload_path'   => $type_tmp_dir,
		        'allowed_types' => UPLOAD_ALLOWED_TYPES
		    ));
		    
		    header('Content-Type: application/json');
		    
		    if($upload = $this->upload->do_upload($last_folder)) {
		    	$data = $this->upload->data();
		    	
			    echo json_encode(
					array(
						'success' => true, 
						'filename' => $data['orig_name'],
						'new_filename' => $data['file_name'],
						'uid' => $uid
					)
				);
		    } else {
			    exit(json_encode(array('success' => false, 'msg' => $this->upload->display_errors('','')))); 
		    }			
		}
	}

}