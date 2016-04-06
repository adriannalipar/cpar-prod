<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . '/third_party/google_auth/src/Google_Client.php');
require_once(APPPATH . '/third_party/google_auth/src/contrib/Google_Oauth2Service.php');

class Login extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->helper('url');
		$this->load->model('users_model');
	}

	public function index() {
		if ($this->session->userdata('loggedIn')) redirect('http://' . base_url() . 'cpar');

		$user_id = false;
		$isExistingEmail = false;

		//initialize google client
		$googleClient = new Google_Client();
		$googleClient->setApplicationName('dev.aboitiz.com');
		$googleClient->setClientId(CLIENT_ID);
		$googleClient->setClientSecret(CLIENT_SECRET);
		$googleClient->setRedirectUri(REDIRECT_URL);
		$googleClient->setDeveloperKey(API_KEY);
		$googleClient->setAccessType('online');
 		$googleClient->setApprovalPrompt('auto');
 		
 		if($this->input->get('route')) {
 			$googleClient->setState($this->input->get('route'));
 		}
 		
		$google_oauthV2 = new Google_Oauth2Service($googleClient);

		//if successful validation from Google
		if (isset($_GET['code'])) {
			$googleClient->authenticate($_GET['code']);

			$gdata = $google_oauthV2->userinfo->get();
			$user = $this->users_model->isExistingEmail($gdata['email'], null);

			if($user != null && $user->status == USER_STATUS_ACTIVE_FLAG) {
				$isExistingEmail = true;
				$this->session->set_userdata('logged_in_user', $user);
			} else {
				redirect('..?err=1');
			}

			// $_SESSION['token'] = $googleClient->getAccessToken();
		}

		// Action if token is sent
		// if (isset($_SESSION['token'])) { 
		// 	$googleClient->setAccessToken($_SESSION['token']);
		// }

		if ($googleClient->getAccessToken() && $isExistingEmail) {
			$this->session->set_userdata('loggedIn', $user->id);
			$this->session->set_userdata('udata', $user);
			$this->session->set_userdata('gdata', $gdata);
			if($this->input->get('state')) {
					redirect('http://' . base_url() . 'cpar/link/'.$this->input->get('state'));
			} else {
					redirect('http://' . base_url() . 'cpar');
			}
		} else {
			$data['googleLoginUrl'] = $googleClient->createAuthUrl();
			
			if($this->input->get('err')) {
				$errors = array();
				array_push($errors, 'The email address used is not a valid user of this system.');
				$data['errors'] = $errors;
			} else {
  				redirect($data['googleLoginUrl']);
			}

			
			$this->load->view('login', $data);
		}
	}

	public function logout() {
		$user_data = $this->session->all_userdata();
        foreach ($user_data as $key => $value) {
            if ($key != 'session_id' && $key != 'ip_address' && $key != 'user_agent' && $key != 'last_activity') {
                $this->session->unset_userdata($key);
            }
        }        
	    $this->session->sess_destroy();
	    redirect('http://' . base_url(). 'login/loggedout');
	}
	
	public function loggedout() {
		if ($this->session->userdata('loggedIn')) redirect('http://' . base_url() . 'cpar');
	
		$googleClient = new Google_Client();
		$googleClient->setApplicationName('dev.aboitiz.com');
		$googleClient->setClientId(CLIENT_ID);
		$googleClient->setClientSecret(CLIENT_SECRET);
		$googleClient->setRedirectUri(REDIRECT_URL);
		$googleClient->setDeveloperKey(API_KEY);
		$googleClient->setAccessType('online');
 		$googleClient->setApprovalPrompt('auto');
 		
 		$google_oauthV2 = new Google_Oauth2Service($googleClient);
	
		$data = array();
		
		$data['googleLoginUrl'] = $googleClient->createAuthUrl();

		$this->load->view('logout', $data);
	}
}