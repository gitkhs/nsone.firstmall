<?php if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once(APPPATH ."controllers/base/common_base".EXT);

class selleradmin_base extends common_base {
	var $AdminMenu			= array();
	var $skin;
	var $managerInfo;
	var $auth_msg			= "권한이 없습니다.";

	public function __construct() {
		parent::__construct();
		checkEnvironmentValidation();

		/* 만기도래 체크(로그인화면 제외) */
		$file_path = $this->config_system['sadminSkin']."/common/blank.html";
		$this->template->define(array('warningScript'=>$file_path));

		if(!preg_match("/^selleradmin\/login(^_)*/",uri_string()) && !preg_match("/^selleradmin\/main_index/",uri_string()) && uri_string()!='selleradmin'){
			warningExpireDate();
		}

		### 데모세션처리
		$this->set_demo();

		define('__SELLERADMIN__',true);//입점사페이지 if( defined('__SELLERADMIN__') === true ) {

		$auto_logout = config_load('autoLogout');		
		$this->template->assign(array('autoLogout'=>$auto_logout));

		$this->skin = 'default';//$this->config_system['sadminSkin'];

		/* PC용 스킨 */
		$this->realSkin = isset($this->config_system['skin']) ? $this->config_system['skin'] : null;
		$this->workingSkin = isset($this->config_system['workingSkin']) ? $this->config_system['workingSkin'] : null;

		/* 모바일용 스킨 */
		$this->realMobileSkin = isset($this->config_system['mobileSkin']) ? $this->config_system['mobileSkin'] : null;
		$this->workingMobileSkin = isset($this->config_system['workingMobileSkin']) ? $this->config_system['workingMobileSkin'] : null;

		/* 페이머스용 스킨 */
		$this->realFammerceSkin = isset($this->config_system['fammerceSkin']) ? $this->config_system['fammerceSkin'] : null;
		$this->workingFammerceSkin = isset($this->config_system['workingFammerceSkin']) ? $this->config_system['workingFammerceSkin'] : null;

		/* 아이디자인에서 처리할 스킨 */
		if		($this->mobileMode)		$this->designWorkingSkin = $this->workingMobileSkin;
		elseif	($this->fammerceMode)	$this->designWorkingSkin = $this->workingFammerceSkin;
		else 							$this->designWorkingSkin = $this->workingSkin;

		### MANAGER SESSION
		$this->managerInfo = $this->session->userdata('manager');
		$this->template->assign(array('managerInfo' => $this->managerInfo));

		### PROVIDER SESSION
		$this->providerInfo = $this->session->userdata('provider');
		$this->template->assign(array('providerInfo' => $this->providerInfo));

		### ADMIN SESSION TYPE
		$this->adminSessionType = 'provider';
		$this->template->assign(array('adminSessionType' => $this->adminSessionType));

		if (! isset($this->providerInfo['provider_seq']) ) {
			if( !strpos($this->template_path(),'login') &&  !strpos($this->template_path(),'facebook') ){
				if((stristr($this->template_path(),'_process') ||stristr($this->template_path(),'webftp') || stristr($this->template_path(),'design')) && stristr( $this ->input->user_agent(),'shockwave') )
				{
					// 디자인관리에서 Uploadify(플래시)를 통해서 업로드할 경우 세션체크가 안되므로 풀어줌.
				}else{
					redirect("/selleradmin/login/index");
					exit;
				}
			}
		}

		/* 모바일용 도메인 */
		$host = parse_url($_SERVER['HTTP_HOST']);
		$host = preg_replace("/^m\./","",$host['path']);
		$this->pcDomain = $host;
		$this->mobileDomain = "m.".preg_replace("/^www\./","",$host);
		$this->template->assign('pcDomain',$this->pcDomain);
		$this->template->assign('mobileDomain',$this->mobileDomain);

		/* 페이스북 연결 여부 */
		$page_id_f_ar				= (isset($this->arrSns['page_id_f']))?explode(",",$this->arrSns['page_id_f']):'';
		$page_name_ar			= (isset($this->arrSns['page_name_f']))?explode(",",$this->arrSns['page_name_f']):'';
		$page_url_ar				= (isset($this->arrSns['page_url_f']))?explode(",",$this->arrSns['page_url_f']):'';
		$page_app_link_f_ar	= (isset($this->arrSns['page_app_link_f']))?explode(",",$this->arrSns['page_app_link_f']):'';
		if($page_id_f_ar){
			foreach($page_id_f_ar as $pagen=>$v) {
				if($page_id_f_ar[$pagen] && $page_app_link_f_ar[$pagen]) {
					$this->template->assign('facebookConnected',1);
					$this->template->assign('facebookapp_url',str_replace("]","",str_replace("[","",$page_app_link_f_ar[$pagen])));
					break;
				}
			}
		}
		$this->load->model('authmodel');
		$cfg_goods = config_load("goods");
		if( $cfg_goods['ucc_id'] && $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ) {
			$this->template->assign('video_use',1);
		}


	}

	// 관리자 메뉴 로딩
	public function admin_menu(){
		$this->load->model("admin_menu");

		$adminMenuCurrent = $this->uri->rsegments[1];

		$this->template->assign(array(
			'adminMenu' => $this->admin_menu->arr_menu,
			'adminMenuLimit' => 5,
			'adminMenuCurrent' => $adminMenuCurrent
		));
	}

	// 디자인 모듈 로딩
	public function tempate_modules(){

		$filePath = APPPATH."../selleradmin/skin/".$this->skin."/_modules/";
		$map = directory_map($filePath);

		foreach($map as $dir => $dirRow) {
			if(is_array($dirRow)) {
				foreach($dirRow as $modulePath) {
					$modulesList[$dir."_".substr($modulePath,0,-5)] = $this->skin."/_modules/".$dir."/".$modulePath;
				}
			}
		}
		$this->template->define($modulesList);
	}

	public function template_path(){
		return $this->skin."/".implode('/',$this->uri->rsegments).".html";
	}

	### 데모세션처리
	public function set_demo(){
		$filename = ROOTPATH.APPPATH."helpers/demo_helper".EXT;
		if(file_exists($filename)){
			$this->load->helper('demo');
		}
	}

}

// END
/* End of file selleradmin_base.php */
/* Location: ./app/base/selleradmin_base.php */