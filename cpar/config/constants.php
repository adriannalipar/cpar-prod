<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

//General
define('DEFAULT_TABLE_ROWS', 10);
define('DEFAULT_PAGE_NUMBER', 1);
define('TYPEAHEAD_MAX_ROWS', 10);
define('DB_ERROR_DUPLICATE', 1062);
define('RPP_ALL', 'All');
define('PAGES_PER_SET', 3);

define('DEFAULT_CPAR_TAB', 'All');
define('CPAR_TAB_ALL', DEFAULT_CPAR_TAB);
define('CPAR_TAB_STAGE_1', 1);
define('CPAR_TAB_STAGE_2', 2);
define('CPAR_TAB_STAGE_3', 3);
define('CPAR_TAB_STAGE_4', 4);
define('CPAR_TAB_STAGE_5', 5);

define('DEFAULT_USER_SORT', 'ASC');
define('DEFAULT_USER_SORT_BY', 'first_name');
define('DEFAULT_CPAR_SORT', 'DESC');
#define('DEFAULT_CPAR_SORT_BY', 'cpar_main.id');
define('DEFAULT_CPAR_SORT_BY', 'cpar_main.updated_date');

//Access Levels
define('ACCESS_LEVEL_ADMIN_FLAG', 1);
define('ACCESS_LEVEL_ADMIN', 'Administrator');
define('ACCESS_LEVEL_USER', 'User');

//User Roles
define('MR_FLAG', 1);
define('NON_MR_FLAG', 0);
define('IMS_FLAG', 1);
define('NON_IMS_FLAG', 0);
define('IMS_USER_ROLE', 'IMS');
define('MR_USER_ROLE', 'MR');
define('USER_USER_ROLE', 'Normal User');

//User Status
define('USER_STATUS_ACTIVE_FLAG', 1);
define('USER_STATUS_INACTIVE_FLAG', 0);
define('USER_STATUS_ACTIVE', 'Active');
define('USER_STATUS_INACTIVE', 'Inactive');

//Dropdown Values
define('DDVAL_GENERAL_ALL', 'All');

define('DDVAL_ROLE_IMS_ONLY', 1);
define('DDVAL_ROLE_MR_ONLY', 2);
define('DDVAL_ROLE_NORMAL_USER', 3);

define('DDVAL_ACCESSLVL_ADMIN', 1);
define('DDVAL_ACCESSLVL_USER', 2);

define('DDVAL_STATUS_ACTIVE', 1);
define('DDVAL_STATUS_INACTIVE', 0);

//Controllers
define('PAGE_USER', 'user');

//Min/Max Constants
define('MIN_USER_FNAME', 2);
define('MIN_USER_MNAME', 2);
define('MIN_USER_LNAME', 2);
define('MIN_USER_EMAIL_ADDRESS', 5);
define('MIN_USER_POS_TITLE', 2);

define('MAX_USER_FNAME', 50);
define('MAX_USER_MNAME', 50);
define('MAX_USER_LNAME', 50);
define('MAX_USER_EMAIL_ADDRESS', 50);
define('MAX_USER_POS_TITLE', 200);

define('MAX_CPAR_TITLE', 100000);
define('MAX_CPAR_DETAILS', 100000);
define('MAX_CPAR_JUSTIFICATION', 100000);
define('MAX_CPAR_REFERENCES', 100000);

define('MIN_CPAR_TITLE', 2);
define('MIN_CPAR_DETAILS', 1);
define('MIN_CPAR_JUSTIFICATION', 1);
define('MIN_CPAR_REFERENCES', 1);

define('MIN_CPAR_REMARKS', 1);
define('MAX_CPAR_REMARKS', 100000);

define('MIN_CPAR_IMMEDIATE_REMEDIAL_ACTION', 1);
define('MIN_CPAR_CORR_PREV_ACTION', 1);

define('MAX_CPAR_IMMEDIATE_REMEDIAL_ACTION', 100000);
define('MAX_CPAR_CORR_PREV_ACTION', 100000);

define('MIN_CPAR_OTHERS', 1);
define('MAX_CPAR_OTHERS', 100000);

define('MIN_CPAR_TASK', 1);
define('MAX_CPAR_TASK', 100000);

/* CPAR */
define('CPAR_NO_SEPARATOR', '-');

//Types
define('CPAR_TYPE_C', 1);
define('CPAR_TYPE_P', 2);
define('CPAR_TYPE_C_NAME', 'Corrective Action Request');
define('CPAR_TYPE_P_NAME', 'Preventive Action Request (Continual Improvement Action)');

define('CPAR_TYPE_NONE_START_CHAR', 'X');
define('CPAR_TYPE_NONE_SHORT_NAME', CPAR_TYPE_NONE_START_CHAR . 'AR');

define('CPAR_TYPE_C_SHORT_NAME', 'CAR');
define('CPAR_TYPE_P_SHORT_NAME', 'PAR');

//Stages/Status
define('CPAR_STAGE_1', 1);
define('CPAR_STAGE_2', 2);
define('CPAR_STAGE_3', 3);
define('CPAR_STAGE_4', 4);
define('CPAR_STAGE_5', 5);

define('CPAR_MINI_STATUS_DRAFT', /*'Draft'*/11);
define('CPAR_MINI_STATUS_FOR_IMS_REVIEW', /*'For IMS Review'*/12);
define('CPAR_MINI_STATUS_PUSHED_BACK', /*'Pushed Back'*/13);
define('CPAR_MINI_STATUS_CLOSED', /*'Closed'*/19);

define('CPAR_MINI_STATUS_S2_2A1', /*'For Addressee Input'*/21);
define('CPAR_MINI_STATUS_S2_2B', /*'For TL Review'*/22);
define('CPAR_MINI_STATUS_S2_2A2', /*'Pushed Back'*/23);

define('CPAR_MINI_STATUS_S3_3A', /*'For IMS Review'*/31);
define('CPAR_MINI_STATUS_S3_3B', /*'For AP Implementation'*/32);
define('CPAR_MINI_STATUS_S3_3B2', /*'For AP Implementation - Pushed Back'*/33);

define('CPAR_MINI_STATUS_S4_4A', /*'Effectiveness Verification'*/41);
define('CPAR_MINI_STATUS_S4_4B', /*'For MR Closure'*/42);
define('CPAR_MINI_STATUS_S4_4A2', /*'Effectiveness Verification - Pushed Back'*/43);

define('CPAR_MINI_STATUS_S5_5A', /*'Closed'*/51);

//Stage 1 Submit buttons
define('CPAR_SUBMIT_S1_SAVE_ONLY', 'save_only');
define('CPAR_SUBMIT_S1_SAVE_CPAR_CHANGES', 'save_cpar_changes');
define('CPAR_SUBMIT_S1_PROCEED', 'proceed');

//Stage 1 Submit buttons
define('CPAR_SUBMIT_S2_SAVE_CPAR_CHANGES', 'save_cpar_changes_2');

define('DELETED_FLAG', 1);

define('REVIEW_ACTIONS_MARK_REV', 1);
define('REVIEW_ACTIONS_PUSH_BACK', 2);
define('REVIEW_ACTIONS_MARK_INV', 3);

define('REVIEW_ACTIONS_S2_MARK_APPR', 4);
define('REVIEW_ACTIONS_S2_MARK_INV', 5);

define('REVIEW_ACTIONS_S3_MARK_REV', 6);
define('REVIEW_ACTIONS_S3_PUSH_BACK', 7);
define('REVIEW_ACTIONS_S3_MARK_IMPL', 8);
define('REVIEW_ACTIONS_S3_MARK_FF_UP', 9);

define('REVIEW_ACTIONS_S4_MARK_EFF', 10);
define('REVIEW_ACTIONS_S4_PUSH_BACK', 11);
define('REVIEW_ACTIONS_S4_MARK_CLOSED', 12);
define('REVIEW_ACTIONS_S4_PUSH_BACK_4A', 13);

define('REVIEW_ACTIONS_RE_ASSIGN', 99);

define('PUSH_BACK_SEPARATOR', '_');
define('UPLOAD_FILENAME_SEPARATOR', '_');

define('NULL_DATE', '0000-00-00 00:00:00');
define('NULL_DATE_ONLY', '0000-00-00');

define('UPLOAD_PATH', S_CI_DIR.'uploads/');
define('UPLOAD_ALLOWED_TYPES', '*');
define('UPLOAD_FILENAME_OFFSET', '2');

define('UPLOAD_2RAD_PREFIX', '2RAD');
define('UPLOAD_2RCA_PREFIX', '2RCA');
define('UPLOAD_3IMPL_PREFIX', '3IMPL');
define('UPLOAD_3FFUP_PREFIX', '3FFUP');

define('RAISED_AS_A_RESULT_OF_OTHERS_ID', 10);
define('RAISED_AS_A_RESULT_OF_OTHERS', 'Others');

//Tools Used
define('TOOLS_USED_OTHERS', 'Others');
define('TOOLS_USED_OTHERS_ID', 7);

//Action Plan Details Status
define('APD_STATUS_PENDING', 1);
define('APD_STATUS_ONGOING', 2);
define('APD_STATUS_DONE', 3);
define('APD_STATUS_OVERDUE', 4);
define('APD_STATUS_VERIFIED', 5);

//Review Roles
define('REVIEW_ROLE_MR', 'MR');
define('REVIEW_ROLE_IMS', 'IMS');
define('REVIEW_ROLE_ADMIN', 'Admin');
define('REVIEW_ROLE_ADDRESSEE_TEAM_LEAD', 'Team Leader');

//Follow-up Results
define('FF_UP_RESULT_FOR_FF', 1);
define('FF_UP_RESULT_IMPL', 2);

define('TEST_EMAIL_SUBJECT', 'Aboitiz | CPAR DB');
define('TEST_EMAIL_BODY', 'This is an auto-generated email. Please do not reply to this email.');

define('LOG_CREATED_CPAR','CPAR Created');
define('LOG_UPDATED_CPAR','CPAR Updated');
define('LOG_DUE_DATE_UPDATED', 'Due Date Updated');
define('LOG_MOVED_TO_FOR_TL_REVIEW', 'Moved to For Team Leader Review');
define('LOG_MARK_TASK_ONGOING', 'Marked Task as Ongoing');
define('LOG_MARK_TASK_DONE', 'Marked Task as Completed');
define('LOG_MARK_TASK_VERIFIED', 'Marked Task as Verified');

//CSV headers
define('CSV_HEADER_CPAR_NO', 'CPAR No.');
define('CSV_HEADER_DATE_CREATED', 'Date Created');
define('CSV_HEADER_ORIGINATOR', 'Originator');
define('CSV_HEADER_ADDRESSEE', 'Addressee');
define('CSV_HEADER_TEAM', 'Team');
define('CSV_HEADER_FAARO', 'Filed as a Result of');
define('CSV_HEADER_TITLE', 'Title');
define('CSV_HEADER_STATUS', 'Status');
//for All only
define('CSV_HEADER_STAGE', 'Stage');
define('CSV_HEADER_DUE_DATE', 'Due Date');
//for Stage 2 only
define('CSV_HEADER_NEXT_DUE_DATE', 'Next Due Date');
//for Stage 3 only
define('CSV_HEADER_COMPLETION_DATE', 'Completion Date');
define('CSV_HEADER_FOLLOW_UP_DATE', 'Follow-up Date');
//for Stage 4 only
define('CSV_HEADER_IMS_DUE_DATE', 'IMS Due Date');
//for Stage 5 only
define('CSV_HEADER_CLOSURE_DATE', 'Closure Date');

#Submission Messages
define('MSG_REVIEW_ACTIONS_MARK_REV', 'CPAR successfully marked as reviewed and submitted to addressee for CA/PA input.');
define('MSG_REVIEW_ACTIONS_PUSH_BACK', 'CPAR successfully pushed back to requester for corrections.');
define('MSG_REVIEW_ACTIONS_MARK_INV', 'CPAR successfully marked as invalid.');
define('MSG_REVIEW_ACTIONS_RE_ASSIGN', 'CPAR successfully reassigned to %s for IMS Review.');
define('MSG_FOR_IMS_REVIEW', 'CPAR successfully submitted to IMS for Review.');
define('MSG_S2_FOR_TL_REVIEW', 'CPAR successfully submitted to Team Leader for Review.');
define('MSG_REVIEW_ACTIONS_S2_MARK_APPR', 'CPAR successfully marked as reviewed and submitted to IMS for Review.');
define('MSG_REVIEW_ACTIONS_S2_MARK_INV', 'CPAR successfully pushed back to addressee for corrections.');
define('MSG_REVIEW_ACTIONS_S3_MARK_REV', 'CPAR successfully marked as reviewed and submitted to addressee for action plan implementation.');
define('MSG_REVIEW_ACTIONS_S3_PUSH_BACK', 'CPAR successfully pushed back to addressee for corrections.');
define('MSG_REVIEW_ACTIONS_S3_MARK_IMPL', 'CPAR successfully marked as implemented and submitted to IMS for verification of effectiveness.');
define('MSG_REVIEW_ACTIONS_S3_MARK_FF_UP', 'CPAR successfully marked for follow-up.');
define('MSG_REVIEW_ACTIONS_S4_MARK_EFF', 'CPAR successfully marked as effective and submitted to MR for review and closure.');
define('MSG_REVIEW_ACTIONS_S4_PUSH_BACK', 'CPAR successfully pushed back to addressee for corrections.');
define('MSG_REVIEW_ACTIONS_S4_MARK_CLOSED', 'CPAR successfully marked as closed.');
define('MSG_REVIEW_ACTIONS_S4_PUSH_BACK_4A', 'CPAR successfully pushed back to IMS for effectiveness verification.');

//Pop-up before Timeout (in milliseconds)
define('POP_UP_BEFORE_TIMEOUT', 28200000);

//Idle time 10 minutes (in milliseconds)
define('IDLE_TIME', 600000);

include(S_CI_DIR.'config/cpar/constants.php');

/* End of file constants.php */
/* Location: ./application/config/constants.php */