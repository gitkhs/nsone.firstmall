<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class sns extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->library('snssocial');
		$this->load->library('session');
		$this->template->assign('designMode',false);
		$this->protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';
	}


	/* facebook 연동 */
	public function config_facebook()
	{
		$arrSystem = ($this->config_system)?$this->config_system:config_load('system');
		$this->fbuser = $this->snssocial->facebookuserid();
		if ( !$this->fbuser ) {
			$this->facebook = new Facebook(array(
			  'appId'  => $this->__APP_ID__,
			  'secret' => $this->__APP_SECRET__,
			  "cookie" => true
			));
			// Get User ID
			$this->fbuser = $this->facebook->getUser();
			if($this->fbuser){
				if( !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $this->fbuser);
				}
			}else{
				$this->snssocial->facebooklogin();
			}
		}else{
			if( !$this->session->userdata('fbuser') ) {
				$this->session->set_userdata('fbuser', $this->fbuser);
			}
		}

		$fbuser = $this->fbuser;//$this->snssocial->fbuser;
		if(!$fbuser) {
			$login_info = array(
			'scope'			=> 'email,user_likes, manage_pages, publish_actions, read_friendlists',
			'display'		=> 'popup',
			'redirect_uri'	=> $this->protocol.$arrSystem['subDomain'].'/sns/config_facebook');
			$loginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
			//$_SERVER['HTTP_HOST']->$arrSystem['subDomain']

			$this->template->assign('loginUrl',$loginUrl);
		}else{
			$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
			if($fbpermissions){ 
				if( !(array_key_exists('manage_pages', $fbpermissions['data'][0]) || in_array('manage_pages', $fbpermissions) ) ) {
					$login_info = array(
					'scope'			=> 'manage_pages',
					'display'		=> 'popup',
					'redirect_uri'	=> $this->protocol.$arrSystem['subDomain'].'/sns/config_facebook?popup=1');
					$permissionloginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
					$this->template->assign('permissionloginUrl',$permissionloginUrl);
					//$_SERVER['HTTP_HOST']->$arrSystem['subDomain']
				}
			}
		}

		$this->template->assign('fbuser',$fbuser);
		if($this->arrSns['key_f'] && $fbuser){
			$snsparams['page_id'] = $this->arrSns['page_id_f'];
			$tabs_page = $this->snssocial->facebook_page_read($snsparams, $appuseck);
			$this->template->assign('appuseck',$appuseck);
			$this->template->assign('pageloop',$tabs_page);
		}
		$this->template->assign($this->arrSns); //sns used
		$this->template->assign('config',true);
		$this->template->assign($arrSystem);
		$this->print_layout($this->template_path());

	}

	public function subdomainfacebookck()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$arrSystem = ($this->config_system)?$this->config_system:config_load('system');
		$this->snssocial->facebooklogin();
	}

	public function facebookuseid(){
		//$this->session->unset_userdata('fbuser');
		$fbuser = ($_POST['fbuserid'])?$_POST['fbuserid']:$_GET['fbuserid'];
		if(!$this->session->userdata('fbuser')) {//페이스북회원인경우
			$this->session->set_userdata('fbuser', $fbuser);
		}

		//$this->session->unset_userdata('fbuserid');
		echo $_GET["jsoncallback"];
		exit;
	}

	function _writeLog($msg)
	{
		/**
			$PageCall_time = date("H:i:s");
			$valuear['PageCall time'][] = $PageCall_time;
			$req = '';
			foreach ($_GET as $key => $value)
			{
				if (get_magic_quotes_gpc())
				{
					$_GET[$key] = stripslashes($value);
					$value = stripslashes($value);
				}
				$value = urlencode($value);
				$req .= "&$key=$value";
				$valuear[$key][] = $value;
			}
			foreach ($_POST as $key => $value)
			{
				if (get_magic_quotes_gpc())
				{
					$_POST[$key] = stripslashes($value);
					$value = stripslashes($value);
				}
				$value = urlencode($value);
				$req .= "&$key=$value";
				$valuear[$key][] = $value;
			}
			foreach ($_SESSION as $key => $value)
			{
				if (get_magic_quotes_gpc())
				{
					$_SESSION[$key] = stripslashes($value);
					$value = stripslashes($value);
				}
				$value = urlencode($value);
				$req .= "&$key=$value";
				$valuear[$key][] = $value;
			}
			$this->_writeLog($valuear);
		***/

	    $file = "input_".date("Ymd").".log";
		$path = "data/tmp/";//\data\tmp
	    if(!($fp = fopen($path.$file, "a+"))) return 0;

	    ob_start();
	    print_r($msg);
	    $ob_msg = ob_get_contents();
	    ob_clean();

	    if(fwrite($fp, " ".$ob_msg."\n") === FALSE)
	    {
	        fclose($fp);
	        return 0;
	    }
	    fclose($fp);
	    return 1;
	}


}

/* End of file sns_process.php */
/* Location: ./app/controllers/sns_process.php */