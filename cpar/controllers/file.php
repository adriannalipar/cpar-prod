<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class File extends MY_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->helper('download');
		$this->load->helper('cpar_file_helper');
	}

	public function index() {
		$this->show_custom_404();
	}

	public function get() {
        $filename = $this->input->post('file_name');
        
        if(empty($filename)) {
            $this->show_custom_404();
            return;
        }

        $data = file_get_contents(UPLOAD_PATH . $filename);

        $file = finfo_open(FILEINFO_MIME_TYPE);
        $info = finfo_file($file, UPLOAD_PATH . $filename);
        finfo_close($file);
        $name = getOrigFileName($filename);

        header('Content-Type: ' . $info);
        header('Content-Length: ' . filesize(UPLOAD_PATH . $filename));

        force_download($name, $data);
    }

    public function get_s3() {
        $filename = $this->input->post('file_name');
        
        if(empty($filename)) {
            $this->show_custom_404();
            return;
        }

        $data = file_get_contents(UPLOAD_PATH . $filename);

        $file = finfo_open(FILEINFO_MIME_TYPE);
        $info = finfo_file($file, UPLOAD_PATH . $filename);
        finfo_close($file);
        $name = splitn($filename, UPLOAD_FILENAME_SEPARATOR, UPLOAD_FILENAME_OFFSET + 1);

        header('Content-Type: ' . $info);
        header('Content-Length: ' . filesize(UPLOAD_PATH . $filename));

        force_download($name, $data);
    }
    
    public function action_plan() {
        $filename = $this->input->post('file_name');
        $cpar_no = $this->input->post('cpar_no');
        
        $ap_file = UPLOAD_PATH . $cpar_no .'/ap_attachments/'. $filename;
        
        if(empty($filename)) {
            $this->show_custom_404();
            return;
        }

        $data = file_get_contents($ap_file);

        $file = finfo_open(FILEINFO_MIME_TYPE);
        $info = finfo_file($file, $ap_file);
        finfo_close($file);
        $name = splitn($filename, UPLOAD_FILENAME_SEPARATOR, 0);

        header('Content-Type: ' . $info);
        header('Content-Length: ' . filesize($ap_file));

        force_download($name, $data);
    }

    public function task() {
        $filename = $this->input->post('file_name');
        $cpar_no = $this->input->post('cpar_no');
        $id = $this->input->post('id');
        
        $task_file = UPLOAD_PATH . $cpar_no .'/tasks/'.$id.'/'.$filename;
        
        if(empty($filename)) {
            $this->show_custom_404();
            return;
        }

        $data = file_get_contents($task_file);

        $file = finfo_open(FILEINFO_MIME_TYPE);
        $info = finfo_file($file, $task_file);
        finfo_close($file);
        $name = splitn($filename, UPLOAD_FILENAME_SEPARATOR, 0);

        header('Content-Type: ' . $info);
        header('Content-Length: ' . filesize($task_file));

        force_download($name, $data);
    }
    
}