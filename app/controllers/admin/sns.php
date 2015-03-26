<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class sns extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->library('snssocial');
		$this->load->library('session');
		$this->template->assign('designMode',false);
	}


	/* facebook 연동 */
	public function config_facebook()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$arrSystem = ($this->config_system)?$this->config_system:config_load('system');

		if($_GET['snsreferer']) $this->session->set_userdata('snsreferer', $_GET['snsreferer']);
		$this->template->assign('snsrefererurl',$this->session->userdata('snsreferer'));
		$this->snssocial->facebooklogin($this->router->method);
		if(!$this->session->userdata('fbuser')) {
			$login_info = array(
			'scope'			=> $this->snssocial->adminauth,
			'display'		=> 'popup',
			'redirect_uri'	=> 'http://'.$_SERVER['HTTP_HOST'].'/admin/sns/config_facebook');
			$loginUrl = $this->snssocial->facebook->getLoginUrl($login_info);

			$this->template->assign('loginUrl',$loginUrl);
		}else{
			$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
			if($fbpermissions){ 
				if( !(array_key_exists('manage_pages', $fbpermissions['data'][0]) || in_array('manage_pages', $fbpermissions) ) ) {
					$login_info = array(
					'scope'			=> 'manage_pages',
					'display'		=> 'popup',
					'redirect_uri'	=> 'http://'.$_SERVER['HTTP_HOST'].'/admin/sns/config_facebook?popup=1');
					$permissionloginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
					$this->template->assign('permissionloginUrl',$permissionloginUrl);
				}
			}

			if($this->arrSns['key_f'] ){
				$snsparams['page_id'] = $this->arrSns['page_id_f'];
				$tabs_page = $this->snssocial->facebook_page_read($snsparams, $appuseck,$this->snssocial->facebook);
				$this->template->assign('appuseck',$appuseck);
				$this->template->assign('pageloop',$tabs_page);
			}
			$this->template->assign('fbuser',$this->session->userdata('fbuser'));
		}

			if($this->arrSns['key_f'] ){
				$snsparams['page_id'] = $this->arrSns['page_id_f'];
				$tabs_page = $this->snssocial->facebook_page_read($snsparams, $appuseck,$this->snssocial->facebook);
				//debug_var($tabs_page);
				$this->template->assign('appuseck',$appuseck);
				$this->template->assign('pageloop',$tabs_page);
			}
			//debug_var($this->session->all_userdata());//exit;

		$this->template->assign($this->arrSns); //sns used
		$this->template->assign('config',true);
		$this->template->assign($arrSystem);

		$this->template->print_("tpl");


	}

	public function domain_facebook()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if($_GET['fblike_return_url'])$this->session->set_userdata('fblike_return_url', $_GET['fblike_return_url']);//재설정
		$this->template->define(array('tpl'=>$file_path));
		$arrSystem = ($this->config_system)?$this->config_system:config_load('system');
		$this->snssocial->facebooklogin($this->router->method);
		if(!$this->session->userdata('fbuser')) {
			$login_info = array(
			'scope'			=> $this->snssocial->userauth,
			'display'		=> 'popup',
			'redirect_uri'	=> 'http://'.$_SERVER['HTTP_HOST'].'/admin/sns/domain_facebook');
			$loginUrl = $this->snssocial->facebook->getLoginUrl($login_info);

			$this->template->assign('loginUrl',$loginUrl);
		}else{
			$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
			if(!$fbpermissions){
					$login_info = array(
					'scope'			=> $this->snssocial->userauth,
					'display'		=> 'popup',
					'redirect_uri'	=> 'http://'.$_SERVER['HTTP_HOST'].'/admin/sns/domain_facebook');
					$permissionloginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
					$this->template->assign('permissionloginUrl',$permissionloginUrl);
			}
			$this->template->assign('fbuser',$this->session->userdata('fbuser'));
		}
		if($_GET['code'] && !$this->session->userdata('fbuser')){
			$access_tokenuri = $this->snssocial->get_token($this->__APP_ID__, $this->__APP_SECRET__,$_GET['code']);
			$accessarar = explode("=",str_replace("\"","",$access_tokenuri));
			$access_token = $accessarar[1];
			$this->template->assign('access_token',$access_token);
		}
		$this->template->assign('fblike_return_url',$this->session->userdata('fblike_return_url'));
		$this->template->assign($this->arrSns); //sns used
		$this->template->assign('config',true);
		$this->template->assign($arrSystem);

		$this->template->print_("tpl");
	}


	public function subdomainfacebookck()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$arrSystem = ($this->config_system)?$this->config_system:config_load('system');
		$this->snssocial->facebooklogin();
		if(!$this->session->userdata('fbuser')) {
		}else{
			$this->template->assign('fbuser',$this->session->userdata('fbuser'));
		}
		$this->template->assign($this->arrSns); //sns used
		$this->template->assign('config',true);
		$this->template->assign($arrSystem);

		$this->template->print_("tpl");
	}


	public function subdomainsnsck()
	{
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if(strstr($referer['host'], $this->config_system['domain']) ) {
			if( $_GET['firstmallcartid']!=$this->session->userdata('session_id')) {
				$session_id = $_GET['firstmallcartid'];
				$this->session->set_userdata('session_id', $session_id);//재설정
			}
		}

		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$arrSystem = ($this->config_system)?$this->config_system:config_load('system');
		$this->snssocial->facebooklogin();
		if(!$this->session->userdata('fbuser')) {
		}else{
			$this->template->assign('fbuser',$this->session->userdata('fbuser'));
		}
		$this->template->assign($this->arrSns); //sns used
		$this->template->assign('config',true);
		$this->template->assign($arrSystem);
		if($this->userInfo['member_seq']){
			$this->template->assign('userid',$this->session->userdata('userid'));
		}

		$this->template->print_("tpl");
	}

}

/* End of file sns_process.php */
/* Location: ./app/controllers/admin//sns.php */