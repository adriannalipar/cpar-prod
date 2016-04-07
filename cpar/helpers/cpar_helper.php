<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function isCorrectDateRange($d_from, $d_to) {
    $ret = false;

    $d_from = new DateTime($d_from);
    $d_to = new DateTime($d_to);

    return ($d_from <= $d_to);
}

function formatDateForDB($str) {
    $ret = '';

    if(!($str == null || empty($str))) {
        $date = new DateTime($str);
        $ret = $date->format('Y-m-d');
    }

    return $ret;
}

function formatDateForDisplay($str) {
    $ret = '';

    if(!($str == null || empty($str))) {
        if(strcmp($str, NULL_DATE) != 0) {
            $date = new DateTime($str);
            $ret = $date->format('M d, Y');
        }
    }

    return $ret;
}

function normalizeSelect2Value($str) {
    $val = $str;

    if(!ctype_digit($str)) {
        $obj = json_decode($str);
        if($obj != null) {
            $val = $obj[0]->id;
        }
    }

    return $val;
}

function getReviewActionName($r_action) {
    $ret = '';

    $r_action_name = array();
    $r_action_name[REVIEW_ACTIONS_MARK_REV] = 'Verified';
    $r_action_name[REVIEW_ACTIONS_PUSH_BACK] = 'Pushed Back';
    $r_action_name[REVIEW_ACTIONS_MARK_INV] = 'Marked as Invalid';

    $r_action_name[REVIEW_ACTIONS_S2_MARK_APPR] = 'Approved';
    $r_action_name[REVIEW_ACTIONS_S2_MARK_INV] = 'Pushed Back';

    $r_action_name[REVIEW_ACTIONS_S3_MARK_REV] = 'Reviewed';
    $r_action_name[REVIEW_ACTIONS_S3_PUSH_BACK] = 'Pushed Back';
    $r_action_name[REVIEW_ACTIONS_S3_MARK_IMPL] = 'Followed Up';
    $r_action_name[REVIEW_ACTIONS_S3_MARK_FF_UP] = 'Followed Up';

    $r_action_name[REVIEW_ACTIONS_S4_MARK_EFF] = 'Verified';
    $r_action_name[REVIEW_ACTIONS_S4_PUSH_BACK] = 'Pushed Back';
    $r_action_name[REVIEW_ACTIONS_S4_MARK_CLOSED] = 'Closed';
    $r_action_name[REVIEW_ACTIONS_S4_PUSH_BACK_4A] = 'Pushed Back';

    $r_action_name[REVIEW_ACTIONS_RE_ASSIGN] = 'Re-assigned';

    if(isset($r_action_name[$r_action])) {
        $ret = $r_action_name[$r_action];
    }

    return $ret;
}

function getSubStatusName($sub_status) {
    $ret = '';

    $sub_status_name = array();

    //Stage 1
    $sub_status_name[CPAR_MINI_STATUS_DRAFT] = 'Draft';
    $sub_status_name[CPAR_MINI_STATUS_FOR_IMS_REVIEW] = 'For IMS Review';
    $sub_status_name[CPAR_MINI_STATUS_PUSHED_BACK] = 'Draft';
    $sub_status_name[CPAR_MINI_STATUS_CLOSED] = 'Invalid';

    //Stage 2
    $sub_status_name[CPAR_MINI_STATUS_S2_2A1] = 'For CA / PA';
    $sub_status_name[CPAR_MINI_STATUS_S2_2B] = 'For Team Leader Review';
    $sub_status_name[CPAR_MINI_STATUS_S2_2A2] = 'For CA / PA';

    //Stage 3
    $sub_status_name[CPAR_MINI_STATUS_S3_3A] = 'For IMS Review';
    $sub_status_name[CPAR_MINI_STATUS_S3_3B] = 'For AP Implementation';
    $sub_status_name[CPAR_MINI_STATUS_S3_3B2] = 'For AP Implementation';

    //Stage 4
    $sub_status_name[CPAR_MINI_STATUS_S4_4A] = 'For IMS Review';
    $sub_status_name[CPAR_MINI_STATUS_S4_4B] = 'For MR Review';
    $sub_status_name[CPAR_MINI_STATUS_S4_4A2] = 'For IMS Review';

    //Stage 5
    $sub_status_name[CPAR_MINI_STATUS_S5_5A] = 'Closed';

    if(isset($sub_status_name[$sub_status])) {
        $ret = $sub_status_name[$sub_status];
    }

    return $ret;
}

function getTaskStatusName($t_status) {
    $ret = '';

    $t_status_name = array();
    $t_status_name[APD_STATUS_PENDING] = 'Pending';
    $t_status_name[APD_STATUS_ONGOING] = 'Ongoing';
    $t_status_name[APD_STATUS_DONE] = 'Completed (For IMS Verification)';
    $t_status_name[APD_STATUS_OVERDUE] = 'Overdue';
    $t_status_name[APD_STATUS_VERIFIED] = 'Verified';

    if(isset($t_status_name[$t_status])) {
        $ret = $t_status_name[$t_status];
    }

    return $ret;
}

function getFfUpResultName($ff_result) {
    $ret = '';

    $ff_result_name = array();
    $ff_result_name[FF_UP_RESULT_FOR_FF] = 'For Follow-up';
    $ff_result_name[FF_UP_RESULT_IMPL] = 'Implemented';

    if(isset($ff_result_name[$ff_result])) {
        $ret = $ff_result_name[$ff_result];
    }

    return $ret;
}

function getCparTypeName($type) {
    $ret = '';

    $type_name = array();
    $type_name[CPAR_TYPE_C] = CPAR_TYPE_C_NAME;
    $type_name[CPAR_TYPE_P] = CPAR_TYPE_P_NAME;

    if(isset($type_name[$type])) {
        $ret = $type_name[$type];
    }

    return $ret;
}

function getFileNames($cpar_no, $stage) {
    $prefix = $cpar_no . UPLOAD_FILENAME_SEPARATOR . $stage . UPLOAD_FILENAME_SEPARATOR;
    $filenames = preg_grep('~^' . $prefix . '.*$~', scandir(UPLOAD_PATH));

    $obj = null;
    $arr = array();
    foreach ($filenames as $filename) {
        $obj = new stdClass();
        $obj->filename = $filename;
        $obj->orig_filename = getOrigFileName($filename);

        array_push($arr, $obj);
    }

    return $arr;
}

function getActionPlanFiles($cpar_no) {
	$filenames = array();

	if(file_exists(UPLOAD_PATH.$cpar_no.'/ap_attachments')) {
		$filenames = scandir(UPLOAD_PATH.$cpar_no.'/ap_attachments');
	}
	
    $obj = null;
    $arr = array();
    foreach ($filenames as $filename) {
    	if($filename != '.' && $filename != '..') {
	        $obj = new stdClass();
	        $obj->filename = $filename;
	        $obj->orig_filename = getOrigFileName($filename, 0);
	
	        array_push($arr, $obj);
    	}
    }

    return $arr;
}

function getTaskFiles($id, $cpar_no) {
	$filenames = array();

	if(file_exists(UPLOAD_PATH.$cpar_no.'/tasks/'.$id)) {
		$filenames = scandir(UPLOAD_PATH.$cpar_no.'/tasks/'.$id);
	}
	
    $obj = null;
    $arr = array();
    foreach ($filenames as $filename) {
    	if($filename != '.' && $filename != '..') {
	        $obj = new stdClass();
	        $obj->filename = $filename;
	        $obj->orig_filename = getOrigFileName($filename, 0);
	
	        array_push($arr, $obj);
    	}
    }

    return $arr;
} 

function divideToolsList($rca_tools, &$left_list, &$right_list) {
    foreach ($rca_tools as $tool) {
        if(intval($tool['id']) % 2 == 0) {
            array_push($right_list, $tool);
        } else {
            array_push($left_list, $tool);
        }
    }
}

function formatSingleTaskForRender(&$item) {
    if($item->due_date) {
        $date = new DateTime($item->due_date);
        $item->due_date = $date->format('M d, Y');
    } else {
        $item->due_date = '';
    }

    if($item->completed_date) {
        $date = new DateTime($item->completed_date);
        $item->completed_date = $date->format('M d, Y');
    } else {
        $item->completed_date = '';
    }

    if(!empty($item->status)) {
        $item->status_name = getTaskStatusName($item->status);
    } else {
        $item->status_name = '';
    }
}

function formatTasksForRender(&$list) {
    foreach ($list as $key => $item) {
        if($item['due_date']) {
            $date = new DateTime($item['due_date']);
            $item['due_date'] = $date->format('M d, Y');
            $list[$key] = $item;
        } else {
            $item['due_date'] = '';
            $list[$key] = $item;
        }

        if($item['completed_date']) {
            $date = new DateTime($item['completed_date']);
            $item['completed_date'] = $date->format('M d, Y');
            $list[$key] = $item;
        } else {
            $item['completed_date'] = '';
            $list[$key] = $item;
        }

        if(!empty($item['status'])) {
            $item['status_name'] = getTaskStatusName($item['status']);
            $list[$key] = $item;
        } else {
            $item['status_name'] = '';
            $list[$key] = $item;
        }
        
        $list[$key]['attachments'] = getTaskFiles($item['id'], $item['cpar_no']);
    }
}

function finalizeTasks($form) {
    $cpar_no = $form['id'];
    $tasks = json_decode($form['tasks_to_add']);
    $task_arr = array();
    $arr = array();
    
    foreach ($tasks as $task) {
        $arr = array();
        $arr['cpar_no'] = $cpar_no;
        $arr['task'] = $task->task;
        $arr['responsible_person'] = $task->responsible_person;
        $arr['due_date'] = formatDateForDB($task->due_date);
        #$arr['completed_date'] = '';
        $arr['status'] = APD_STATUS_PENDING;
        $arr['remarks_addr'] = $task->remarks;
        $arr['remarks_ims'] = '';

        array_push($task_arr, $arr);
    }

    return $task_arr;
}

function extractAttachments($form) {
    $cpar_no = $form['id'];
    $tasks = json_decode($form['tasks_to_add']);
    $task_arr = array();
    $arr = array();
    
    foreach ($tasks as $task) {
        $arr['attachments'] = $task->attachments ? $task->attachments : null;

        array_push($task_arr, $arr);
    }

    return $task_arr;
}

function normalizeResponsiblePersons($r_persons) {
    $arr = array();

    foreach ($r_persons as $person) {
        array_push($arr, $person['responsible_person']);
    }

    return $arr;
}

function generate_rca_tools($rca_tools) {
	
	
	
	$column1 = array();
	$column2 = array();
	$column3 = array();
	
	$b_has_others = FALSE;
	
	$current_column = 0;
	
	$obj_others = NULL;

	foreach($rca_tools as $tool) {
		
		if(strcmp($tool['name'], TOOLS_USED_OTHERS) == 0) {
			$b_has_others = TRUE;
			$obj_others = $tool;
		} else {
			$current_column++;
		
			if($current_column == 1) {
				$column1[] = $tool;
			} elseif($current_column == 2) {
				$column2[] = $tool;
			} elseif($current_column == 3) {
				$column3[] = $tool;
				$current_column = 0;
			}		
		
		}
	}
	
	return array($column1, $column2, $column3, $b_has_others, $obj_others);
}

function fix_output2($value, $default_value = NULL){    
    return isset($value) ? trim($value) : $default_value;
}