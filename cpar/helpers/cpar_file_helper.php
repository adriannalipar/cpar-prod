<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function getOrigFileName($filename, $offset = UPLOAD_FILENAME_OFFSET) {
	return splitn($filename, UPLOAD_FILENAME_SEPARATOR, $offset);
}

//http://stackoverflow.com/questions/5956066/how-to-split-a-string-in-php-at-nth-occurrence-of-needle
function splitn($string, $needle, $offset) {
    $newString = $string;
    $totalPos = 0;
    $length = strlen($needle);

    for($i = 0; $i < $offset; $i++) {
        $pos = strpos($newString, $needle);

        if($pos === false)
            return false;
        $newString = substr($newString, $pos+$length);
        $totalPos += $pos+$length;
    }

    return substr($string, $totalPos);
}

function generateUploadFilenames($cpar_no, $stage, $name, $files) {
    $name_arr = array();

    if(isset($files[$name])) {
        foreach($files[$name]['name'] as $s) {
            array_push($name_arr, $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR . $s);
        }
    }

    return $name_arr;
}

function transferActionPlanAttachments($uid, $cpar_no, $files_pending) {
	
	$upload_dir = UPLOAD_PATH;

	$date = date('Ymd');

	$tmp_dir = $upload_dir.'tmp';
	$tmp_date_dir = $tmp_dir.'/'.$date;
	$cpar_tmp_dir = $tmp_date_dir.'/'.$cpar_no;
	$uid_dir = $cpar_tmp_dir.'/'.$uid;
	
	$files_already_in_folder = array();
	
	$files_to_remove = array();
	
	if(file_exists($upload_dir.$cpar_no.'/ap_attachments')) {
		$files_already_in_folder = scandir($upload_dir.$cpar_no.'/ap_attachments');	
	} else {
		if (!file_exists($upload_dir.$cpar_no)) {
			mkdir($upload_dir.$cpar_no, 0777, true);
		}
		
		if (!file_exists($upload_dir.$cpar_no.'/ap_attachments')) {
			mkdir($upload_dir.$cpar_no.'/ap_attachments', 0777, true);
		}
	}
	
	foreach($files_already_in_folder as $file) {
		if($file != '.' && $file != '..') {
			if(!in_array($file, $files_pending)) {
				array_push($files_to_remove, $file);
			}
		}
	}
	
	if($files_pending && count($files_pending)) {
		foreach($files_pending as $file) {
			
			if(!file_exists($upload_dir.$cpar_no.'/ap_attachments/'.$file) && file_exists($uid_dir.'/ap_attachments/'.$file)) {
				#transfer
				rename($uid_dir.'/ap_attachments/'.$file, $upload_dir.$cpar_no.'/ap_attachments/'.$file);
			}
			elseif (file_exists($upload_dir.$cpar_no.'/ap_attachments/'.$file) && file_exists($uid_dir.'/ap_attachments/'.$file)) {
				$name = pathinfo($file, PATHINFO_FILENAME);
				$extension = pathinfo($file, PATHINFO_EXTENSION);

				$increment = '';

				while(file_exists($upload_dir.$cpar_no.'/ap_attachments/'.$name . $increment . '.' . $extension)) {
				    $increment++;
				}
				
				$basename = $name . $increment . '.' . $extension;
				
				rename($uid_dir.'/ap_attachments/'.$file, $upload_dir.$cpar_no.'/ap_attachments/'.$basename);

			}
			
		}
	}

	if($files_to_remove && count($files_to_remove)) {
		foreach($files_to_remove as $file) {
			if(file_exists($upload_dir.$cpar_no.'/ap_attachments/'.$file)) {
				unlink($upload_dir.$cpar_no.'/ap_attachments/'.$file);	
			}
		}
	}
}

function transferTaskAttachments($id, $files, $cpar_no) {
	
	$upload_dir = UPLOAD_PATH;
	
	$tmp_dir = $upload_dir.'tmp';
	$tmp_date_dir = $tmp_dir.'/'.date('Ymd');
	$cpar_tmp_dir = $tmp_date_dir.'/'.$cpar_no;

	if (!file_exists($upload_dir.$cpar_no)) {
		mkdir($upload_dir.$cpar_no, 0777, true);
	}
	
	if (!file_exists($upload_dir.$cpar_no.'/tasks')) {
		mkdir($upload_dir.$cpar_no.'/tasks', 0777, true);
	}
	
	if (!file_exists($upload_dir.$cpar_no.'/tasks/'.$id)) {
		mkdir($upload_dir.$cpar_no.'/tasks/'.$id, 0777, true);
	}
	if($files && count($files)) {
		foreach($files as $file) {
			$file_decoded = json_decode($file);
			$uid_dir = $cpar_tmp_dir.'/'.$file_decoded->uid;
					
			if(file_exists($uid_dir.'/task_attachments') && file_exists($uid_dir.'/task_attachments/'.$file_decoded->new_filename)) {
				rename($uid_dir.'/task_attachments/'.$file_decoded->new_filename, $upload_dir.$cpar_no.'/tasks/'.$id.'/'.$file_decoded->new_filename);
			}
		}
	}
}

function removeTaskAttachments($id, $cpar_no) {

	$upload_dir = UPLOAD_PATH;

	if (file_exists($upload_dir.$cpar_no.'/tasks/'.$id)) {
		
		$files_in_dir = scandir($upload_dir.$cpar_no.'/tasks/'.$id);
		
		foreach($files_in_dir as $file) {
			if($file != '.' && $file != '..') {
				unlink($upload_dir.$cpar_no.'/tasks/'.$id.'/'.$file);
			}
		}
		
		rmdir($upload_dir.$cpar_no.'/tasks/'.$id);
	}
}