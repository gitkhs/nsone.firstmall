<?php if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once(APPPATH ."controllers/base/common_base".EXT);

class admin_base extends common_base {
	var $AdminMenu			= array();
	var $skin;
	var $managerInfo;
	var $auth_msg			= "권한이 없습니다.";

	public function __construct() {
		parent::__construct();
		checkEnvironmentValidation();

		/* 만기도래 체크(로그인화면 제외) */
		$file_path = $this->config_system['adminSkin']."/common/blank.html";
		$this->template->define(array('warningScript'=>$file_path));
		if(!preg_match("/^admin\/login(^_)*/",uri_string()) && !preg_match("/^admin\/main_index/",uri_string()) && uri_string()!='admin'){
			warningExpireDate();
		}

		define('__ADMIN__',true);//관리자페이지
			$this->template->assign(array('ADMIN'=>__ADMIN__));

		$this->skin = $this->config_system['adminSkin'];

		### MANAGER SESSION
		$this->managerInfo = $this->session->userdata('manager');
		$this->template->assign(array('managerInfo' => $this->managerInfo));
		if (! isset($this->managerInfo['manager_seq']) ) {
			if( !strpos($this->template_path(),'login') && !strpos($this->template_path(),'logout') && !strpos($this->template_path(),'facebook') ){
				if((stristr($this->template_path(),'_process') ||stristr($this->template_path(),'webftp') || stristr($this->template_path(),'design')) && stristr( $this ->input->user_agent(),'shockwave') )
				{
					// 디자인관리에서 Uploadify(플래시)를 통해서 업로드할 경우 세션체크가 안되므로 풀어줌.
				}elseif($this->session->userdata('marketing') && stristr($this->template_path(),'marketplace')){
					// 마케팅관리아이디 로그인상태일때 마케팅페이지 풀어줌
				}elseif(stristr($this->template_path(),'benifit')){
					// 혜택바로가기 레이어 팝업이므로 풀어줌
				}elseif(stristr($this->template_path(),'upload_file')) {
					//맥북 shockwave session 체크 못하기 때문에 상품이미지 업로드 풀어줌
				}else{
					if($_SERVER['REQUEST_METHOD']=='GET'){
						redirect("/admin/login/index?return_url=".urlencode($_SERVER['REQUEST_URI']));
						exit;
					}else{
						redirect("/admin/login/index");
						exit;
					}
				}
			}
		} else {
			$this->load->model('authmodel');
			$result = $this->authmodel->manager_limit_view($this->template_path());
			//echo $result." : ".$this->template_path();
			if(!$result) pageBack("권한이 없습니다.");
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
		$cfg_goods = config_load("goods");
		if( $cfg_goods['ucc_id'] && $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ) {
			$this->template->assign('video_use',1);
		}

		$firstmallplusservice = $this->skin."/_modules/layout/firstmallplusservice.html";
		$this->template->define(array('firstmallplusservice'=>$firstmallplusservice));

		$nostorfreeService = false;
		$arr_nostorfreeService_url = array(
			'/admin/statistic_sales/sales_goods',
			'/admin/statistic_sales/sales_referer',
			'/admin/statistic_sales/sales_category',
			'/admin/statistic_sales/sales_platform',
			'/admin/statistic_sales/sales_payment',
			'/admin/statistic_sales/sales_etc'
		);
		if( SERVICE_CODE == 'P_FREE' || SERVICE_CODE == 'P_STOR'){
			if(in_array($_SERVER['REQUEST_URI'],$arr_nostorfreeService_url)){
				$nostorfreeService = true;
			}
		}

		$this->template->assign('nostorfreeService',$nostorfreeService);
		$this->template->assign(array('designWorkingSkin'=>$this->designWorkingSkin));
	}

	// 관리자 메뉴 로딩
	public function admin_menu(){
		$this->load->model("admin_menu");

		$adminMenuCurrent = $this->uri->rsegments[1];

		/* 매뉴얼 바로보기 숨김처리메뉴 추가 leewh 2014-09-17 */
		$menual_hidden = false;
		if ($adminMenuCurrent == "marketing" 
			|| in_array(uri_string(),array('admin/board/board'))) {
			$menual_hidden = true;
		} else {
			if (uri_string() == "admin/setting/manager_reg") {
				$menual_url = urlencode("setting/manager");
			} else if (uri_string() == "admin/brand/batch_design_setting") {
				$menual_url = urlencode("brand/catalog");
			} else if (uri_string() == "admin/location/batch_design_setting") {
				$menual_url = urlencode("location/catalog");
			} else {
				$menual_url = urlencode(preg_replace("/^admin\//","",uri_string()));
			}
		}

		$this->template->assign(array(
			'adminMenu' => $this->admin_menu->arr_menu,
			'adminMenu2' => $this->admin_menu->arr_menu2,
			'adminMenuLimit' => 5,
			'adminMenuCurrent' => $adminMenuCurrent,
			'admin_menual_url' => $menual_url,
			'admin_menual_hidden' => $menual_hidden
		));
	}

	// 디자인 모듈 로딩
	public function tempate_modules(){

		$filePath = APPPATH."../admin/skin/".$this->skin."/_modules/";
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


}

// END
/* End of file admin_base.php */
/* Location: ./app/base/admin_base.php */