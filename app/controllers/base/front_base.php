<?php if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once(APPPATH ."controllers/base/common_base".EXT);

class front_base extends common_base {
	var $skin;
	var $realSkin;
	var $workingSkin;
	var $config_basic;
	var $userInfo;
	var $managerInfo;

	public function __construct() {
		parent::__construct();

		$this->load->model('layout');
		$this->load->helper('design');
		$config_search = config_load("search");

		$this->load->model('visitorlog');
		$this->load->model('ssl');

		/* 유입경로 저장 */
		if(!$this->managerInfo) {
			$this->visitorlog->set_referer();
		}

		/* ssl 도메인 체크 */
		$this->ssl->ssl_domain_check();

		/* 현재 보여줄 스킨 */
		$this->skin = $this->layout->get_view_skin();
		$this->template->assign(array('skin'=>$this->skin));

		/* 디자인모드 여부 */
		$this->designMode = $this->layout->is_design_mode();

		if($this->mobileMode && !empty($this->realMobileSkin)){
			$this->realSkin = $this->realMobileSkin;
			$this->workingSkin = $this->workingMobileSkin;
		}

		if($this->fammerceMode && !empty($this->realFammerceSkin)){
			$this->realSkin = $this->realFammerceSkin;
			$this->workingSkin = $this->workingFammerceSkin;
		}

		if($this->storeMode && !empty($this->realStoreSkin)){
			$this->realSkin = $this->realStoreSkin;
			$this->workingSkin = $this->workingStoreSkin;
		}

		if($this->storemobileMode && !empty($this->realStoremobileSkin)){
			$this->realSkin = $this->realStoremobileSkin;
			$this->workingSkin = $this->workingStoremobileSkin;
		}

		if($this->storefammerceMode && !empty($this->realStorefammerceSkin)){
			$this->realSkin = $this->realStorefammerceSkin;
			$this->workingSkin = $this->workingStorefammerceSkin;
		}

		/* 검색어 */
		$this->load->model('searchwordmodel');
		$uri_str = uri_string();
		if(strpos($uri_str, "goods/search") !== false){
			$search_word_data = $this->searchwordmodel->get_word_by_page('goods_search');
		}else if(strpos($uri_str, "goods/view") !== false){
			$search_word_data = $this->searchwordmodel->get_word_by_page('good_view');
		}else if(strpos($uri_str, "goods/catalog") !== false){
			$search_word_data = $this->searchwordmodel->get_word_by_page('category');
		}else if(strpos($uri_str, "goods/brand") !== false){
			$search_word_data = $this->searchwordmodel->get_word_by_page('brand');
		}else if(strpos($uri_str, "goods/location") !== false ){
			$search_word_data = $this->searchwordmodel->get_word_by_page('location');
		}else if($this->uri->rsegments[1] == 'mypage'){
			$search_word_data = $this->searchwordmodel->get_word_by_page('mypage');
		}else if($this->uri->rsegments[1] == 'mshop'){
			$search_word_data = $this->searchwordmodel->get_word_by_page('mshop');
		}else if(strpos($uri_str, "board") !== false){
			$search_word_data = $this->searchwordmodel->get_word_by_page('board');
		}else if(preg_match('/event|gift/',$_SERVER['REQUEST_URI'])){
			$search_word_data = $this->searchwordmodel->get_word_by_page('event');
		}else{
			$search_word_data = $this->searchwordmodel->get_word_by_page('main');
		}

		$auto_search_use = $search_word_data[0]['page_yn'];
		$auto_search_text = $search_word_data[0]['word'];
		$auto_search_type = $search_word_data[0]['search_result'];
		$auto_search_target = $search_word_data[0]['search_result_target'];
		$auto_search_link = $search_word_data[0]['search_result_link'];
		$auto_search_complete = $config_search['auto_search'];
		$popular_search_complete = $config_search['popular_search'];
		$this->template->assign(array('config_search'=>$config_search));

		$this->template->assign(array('auto_search_complete'=>$auto_search_complete));
		$this->template->assign(array('popular_search_complete'=>$popular_search_complete));

		$this->template->assign(array('auto_search_use'=>$auto_search_use));
		$this->template->assign(array('auto_search_text'=>$auto_search_text));
		$this->template->assign(array('auto_search_type'=>$auto_search_type));
		$this->template->assign(array('auto_search_link'=>$auto_search_link));
		$this->template->assign(array('auto_search_target'=>$auto_search_target));

		$this->template->assign(array('designMode'=>$this->designMode));
		$this->template->assign(array('mobileMode'=>$this->mobileMode));
		$this->template->assign(array('fammerceMode'=>$this->fammerceMode));
		$this->template->assign(array('storeMode'=>$this->storeMode));
		$this->template->assign(array('storemobileMode'=>$this->storemobileMode));
		$this->template->assign(array('storefammerceMode'=>$this->storefammerceMode));

		if($this->mobileMode || $this->storemobileMode || $this->storefammerceMode){
			$this->siteType = 'mobile';
			$this->template->assign('siteType',$this->siteType);
		}

		// http protocol
		$http_protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
		$this->template->assign(array('http_protocol'=>$http_protocol));

		### MEMBER SESSION

		$this->userInfo = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
		if ( isset($this->userInfo['member_seq']) ) {
			define('__ISUSER__',true);//회원로그인
			
			/* 유저정보 assign */
			$this->template->assign('userInfo',$this->userInfo);
			
			//비밀번호 변경 유도
			if($this->userInfo['password_update_date']){
				$member_config = config_load('member');
				if($member_config['modifyPW'] == "Y"){
					$password_update_date = str_replace("-", "", substr(date('Y-m-d H:i:s',time()-(int)$member_config['modifyPWMin']*24*3600), 0, 10));
					$member_password_date = str_replace("-", "", substr($this->userInfo['password_update_date'], 0, 10));
					if((int)$password_update_date >= (int)$member_password_date){
						$this->template->assign('passwordChange','Y');
						$rate_month = $member_config['modifyPWMin'] / 30;
						$this->template->assign('passwordRate',$rate_month);
						
						if(($this->mobileMode || $this->storemobileMode) && $this->uri->rsegments[2] != "popup_change_pass"&& $this->uri->rsegments[2] != "blank"){
							pageRedirect("/member/popup_change_pass?popup=1");
						}
					}
				}
			}

		}

		/**************************************************************/

		/* 가비아 통신처리시에는 아래 소스 건너뜀 */
		if($this->uri->rsegments[1]=='_gabia') return;

		/**************************************************************/

		checkEnvironmentValidation();
		checkExpireDate();

		/* 모바일기기일때 */
		if($this->_is_mobile_agent){
			/* 모바일도메인이 아닐때 모바일로 이동 */
			if($this->session->userdata('setMode')!='pc' && !$this->_is_mobile_domain){
				$mobile_domain = "m.".preg_replace("/^www\./","",$_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'];
				redirect("http://".$mobile_domain);
			}
		}

		/* 스킨 존재여부 체크 */
		$this->skin_exists_check();

		/* 차단아이피 체크 */
		$this->protect_ip_check();

		/* OPERATION SETTING */
		$this->managerInfo = $this->session->userdata('manager');
		if($this->managerInfo) {
			define('__ISADMIN__',true);//관리자인경우
			$this->template->assign(array('ISADMIN'=>__ISADMIN__));
		}
		/*
		else{
			if($this->uri->rsegments[2]!='mobile_mode_off'){
				$this->operating_check();
			}
		}
		*/
		if($this->uri->rsegments[2]!='mobile_mode_off' && uri_string() != 'main/blank'){
			$this->operating_check();
		}

		/* 쇼핑몰 타이틀 */
		$title = $this->config_basic['shopTitleTag'];
		$this->template->assign(array('shopTitle'=>$title));

		/* 즐겨찾기(북마크) */
		//홑따옴표,쌍따옴표 있을경우 즐겨찾기오류 발생으로 추가 leewh 2014-10-29
		$tmp_title = str_replace(array('&quot;', '&apos;'), array('"', "'"), $title);
		$title = str_replace("'", "\'", $tmp_title);
		$title = str_replace('"', "\'", $title);

		$bookmark = bookmarkckeck($this->session->userdata('bookmark'), $title);
		$this->template->assign('bookmark',$bookmark);

		$this->template_path = implode('/',$this->uri->rsegments).".html";

		$this->template->assign(array(
				"designMode"=>$this->designMode,
				"template_path"=>$this->template_path
		));


		if(strstr($_SERVER['HTTP_REFERER'], "/goods") !== false){
			$_SESSION["refer_adress"] = $_SERVER['HTTP_REFERER'];
		}

		if($this->mobileMode || $this->storemobileMode){
			$this->load->model('cartmodel');
			$pushes['push_count_cart'] = $this->cartmodel->get_cart_count();

			if($this->userInfo['member_seq']){
				$query = "select count(*) cnt from fm_order where member_seq=? and step > 0 and step < 75 and hidden='N'";
				$query = $this->db->query($query,array($this->userInfo['member_seq']));
				$data = $query->result_array();
				$pushes['push_count_order'] = $data[0]['cnt'];

				$this->load->model('wishmodel');
				$pushes['push_count_wish'] = $this->wishmodel->get_wish_count($this->userInfo['member_seq']);
			}

			$today_view = $_COOKIE['today_view'];
			if( $today_view ) {
				$today_view = unserialize($today_view);
				krsort($today_view);
				$this->load->model('goodsmodel');
				$pushes['push_count_today'] = count($this->goodsmodel->get_goods_list($today_view,'thumbScroll'));
			}

			$this->template->assign($pushes);
		}

		/*모바일 메인상단바를 위함*/
		if(($this->uri->segment(1) == "main") && (strpos($this->uri->segment(2),"tab_") !== FALSE)){
			$tabFile = strpos($this->uri->segment(2),".html") !== FALSE ? $this->uri->segment(2) :  $this->uri->segment(2).".html";
			redirect("topbar/index?no=".$tabFile);
			exit;
		}

		/* 영역 define */
		$defines = array();
		$defines['HTML_HEADER'] 		= $this->skin."/_modules/common/html_header.html";
		$defines['HTML_FOOTER'] 		= $this->skin."/_modules/common/html_footer.html";
		$defines['paging'] 				= $this->skin."/_modules/common/paging.html";
		$this->template->define($defines);
	}

	/* 스킨 존재여부 체크 */
	public function skin_exists_check(){
		$view_skin_path = APPPATH."../data/skin/".$this->skin;
		if(!is_dir($view_skin_path)){
			echo ("{$this->skin} 스킨 디렉토리를 찾을 수 없습니다.");
			exit;
		}
	}

	public function tempate_modules(){

		$filePath = APPPATH."../data/skin/".$this->skin."/_modules/";
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

	/* 레이아웃 출력 */
	public function print_layout($template_path)
	{

		$this->tempate_modules();

		/* 방문자 분석 기록 */
		if(!$this->managerInfo) {
			$this->visitorlog->execute();
		}

		/* 게시판일경우 게시판스킨별로 레이아웃이 별도처리되므로 분기하여 처리함 */
		if($this->uri->segment(1)=='board'){
			$board_template_path = $this->skin.'/'.$this->template_path;
			$tpl_path = substr($board_template_path,strpos($board_template_path,'/')+1);
			$layout_config = layout_config_autoload($this->skin,$tpl_path);
		}else{
			$tpl_path = substr($template_path,strpos($template_path,'/')+1);
			$layout_config = layout_config_autoload($this->skin,$tpl_path);
		}
		
		if( $this->uri->uri_string == 'member/agreement' ) {//본인인증/회원가입/약관동의 페이지 동일한 레이아웃적용
				$tpl_path_agree = 'member/agreement.html';
				$layout_config_agree = layout_config_autoload($this->skin,$tpl_path_agree);
				if($layout_config_agree){ 
					$layout_config[$tpl_path]['layoutScrollLeft'] = $layout_config_agree[$tpl_path_agree]['layoutScrollLeft'];
					$layout_config[$tpl_path]['layoutScrollRight'] = $layout_config_agree[$tpl_path_agree]['layoutScrollRight']; 
					$layout_config[$tpl_path]['layoutHeader'] = $layout_config_agree[$tpl_path_agree]['layoutHeader'];
					$layout_config[$tpl_path]['layoutTopBar'] = $layout_config_agree[$tpl_path_agree]['layoutTopBar'];
					$layout_config[$tpl_path]['layoutMainTopBar'] = $layout_config_agree[$tpl_path_agree]['layoutMainTopBar'];
					$layout_config[$tpl_path]['layoutSide'] = $layout_config_agree[$tpl_path_agree]['layoutSide'];
				}
		}

		$category_config = config_load('category');

		/* 측면영역 강제 숨김처리 */
		if($this->layout->is_fullsize_absolutly($tpl_path)){
			$tmp_layout_config = layout_config_load($this->skin,$tpl_path);
			if(!$tmp_layout_config[$tpl_path]['tpl_desc']){
				$layout_config[$tpl_path]['layoutSide'] = 'hidden';
			}
		}

		/* 페이머스 측면영역 강제 숨김처리 */
		if($this->fammerceMode){
			$layout_config[$tpl_path]['layoutSide'] = 'hidden';
		}


		/* 팝업페이지 상하양측 레이아웃 강제 숨김처리 */
		if(preg_match("/^popup\//",$tpl_path) || preg_match("/^intro\//",$tpl_path) || $_GET['quickview']){
			$layout_config[$tpl_path]['layoutScrollLeft'] = 'hidden';
			$layout_config[$tpl_path]['layoutScrollRight'] = 'hidden';
			$layout_config[$tpl_path]['layoutHeader'] = 'hidden';
			$layout_config[$tpl_path]['layoutTopBar'] = 'hidden';
			$layout_config[$tpl_path]['layoutMainTopBar'] = 'hidden';
			$layout_config[$tpl_path]['layoutFooter'] = 'hidden';
			$layout_config[$tpl_path]['layoutSide'] = 'hidden';
		}

		/* 레이아웃설정 assign */
		$this->template->assign(array('layout_config'=>$layout_config[$tpl_path]));

		/* 영역 define */
		$defines = array();
		$defines['LAYOUT'] 				= $this->skin."/_modules/layout.html";
		$defines['LAYOUT_BODY'] 		= $template_path;
		$defines['LAYOUT_SCROLL_LEFT'] 	= $this->skin."/".$layout_config[$tpl_path]['layoutScrollLeft'];
		$defines['LAYOUT_SCROLL_RIGHT'] = $this->skin."/".$layout_config[$tpl_path]['layoutScrollRight'];
		$defines['LAYOUT_HEADER'] 		= $this->skin."/".$layout_config[$tpl_path]['layoutHeader'];
		$defines['LAYOUT_TOPBAR']		= $this->skin."/".$layout_config[$tpl_path]['layoutTopBar'];
		$defines['LAYOUT_MAIN_TOPBAR']	= $this->skin."/".$layout_config[$tpl_path]['layoutMainTopBar'];
		$defines['LAYOUT_FOOTER'] 		= $this->skin."/".$layout_config[$tpl_path]['layoutFooter'];
		$defines['LAYOUT_SIDE'] 		= $this->skin."/".$layout_config[$tpl_path]['layoutSide'];

		$this->template->define($defines);

		/* 디자인모드일때 이미지태그에 tpl속성 추가 */
		if($this->designMode) {
			$this->template->compile_dir	= BASEPATH."../_compile/design";
			$this->template->prefilter		= "addImageAttributesBefore | ".$this->template->prefilter." | addImageAttributes";

			/* 컴파일 디렉토리가 없으면 생성 */
			if(!is_dir($this->template->compile_dir)){
				@mkdir($this->template->compile_dir);
				@chmod($this->template->compile_dir,0777);
			}
		}

		if( $this->isdemo['isdemo'] && !($_GET['popup'] || $_GET['iframe'] || $_GET['mobileAjaxCall']) ) {
			if($this->mobileMode || $this->storemobileMode){
				echo '<div id="layout_demo" style="width:320px;background:url(\'http://'.$_SERVER['HTTP_HOST'].'/admin/skin/default/images/design/warning_bg_m.png\') repeat;vertical-align:middle;height:30px;" align="center"><div><img src="/admin/skin/default/images/design/warning_txt_m.png"  style="margin:5px;" width="290"  width="18"></div></div>';
			}else{
				echo '<div id="layout_demo" style="width:'.$layout_config[width].';background:url(\'http://'.$_SERVER['HTTP_HOST'].'/admin/skin/default/images/design/warning_bg.png\') repeat;vertical-align:middle;height:60px;" align="center"><div><img src="/admin/skin/default/images/design/warning_txt.png"  style="margin:10px;"></div></div>';
			}
		}

		if(($this->mobileMode || $this->storemobileMode) && !empty($_GET['mobileAjaxCall'])){
			/* 모바일모드에서 AJAX호출할때 컨텐츠부분만 출력 */
			$this->template->assign(array('mobileAjaxCall'=>$_GET['mobileAjaxCall']));
			$this->template->print_('LAYOUT_BODY');
		}else{
			$this->template->print_('LAYOUT');
		}

		// 도메인 환경 체크
		$chkEnv = checkEnvironment($this->config_system['shopSno']);
		if( ! $chkEnv[0] ){
			echo "<script type='text/javascript'>$.get('../_firstmallplus/env_interface', function(data){});</script>";
		} 
  
		//회원전용 쿠폰팝업용 생일자/기념일/회원 등급 조정 쿠폰/회원 등급 조정 쿠폰 (배송비)
		if ( (isset($this->userInfo['coupon_birthday_count']) && !$_GET['popup'] && (  !in_array('promotion',$this->uri->rsegments)  &&  !in_array('coupon',$this->uri->rsegments) ) )  || ($_GET['previewlayer'] && $this->managerInfo) ) {
			/* 쿠폰팝업 */
			if($_GET['previewlayer'] && $this->managerInfo) {
				$couponpopup[] = $_GET['previewlayer'];
			}else{
				$couponpopup = array("birthday","anniversary","membergroup");
			}
			$num = $indexpoup =0;
			foreach($couponpopup as $coupontypenew) {
				 $popup_key = "designPopupcoupon_".$coupontypenew; 
				if( ($this->userInfo['coupon_'.$coupontypenew.'_count']>0 && !( $this->input->cookie($popup_key)=='1' || (time()-$this->input->cookie($popup_key) < 86400))) || ($_GET['previewlayer'] && $this->managerInfo) ) {//오늘하루 그만보기 체크된경우
					if( $this->siteType == 'mobile' && $indexpoup == 1 ) break;
			echo '<script type="text/javascript">
					//레이어띄우기
					$.ajax({
						"url": "/promotion/coupon_'.$coupontypenew.'",
						"data" : {"popup":"1","layer":"1","leftnum":"'.$num.'","previewlayer":"'.$_GET['previewlayer'].'"},
						success: function(result){
							if(result) $("body").prepend(result);	
						}
					});
					</script>'; 
					$indexpoup++;
					$num+=100;
				}
			}//endforeach
		}

	}

	/* 아이피 차단화면으로 이동 */
	public function protect_ip_check(){

		if($this->protect_ip_denined()){
			if($this->uri->rsegments[2]!='denined_ip'){
				redirect("/common/denined_ip");
				exit;
			}
		}

	}

	/* 차단 아이피 체크 */
	public function protect_ip_denined(){
		$currentIp = $_SERVER['REMOTE_ADDR'];
		$protectIps = isset($this->config_system['protectIp']) ? explode("\n",$this->config_system['protectIp']) : null;
		foreach((array)$protectIps as $protectIp){
			if($protectIp && preg_match("/^".$protectIp."/",$currentIp)){
				return true;
			}
		}
		return false;
	}




	public function operating_check(){
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

		// 운영방식 모바일(태블릿) 추가 2014-05-22 leewh
		if (!isset($basic['intro_m_use'])) { // 일반
			$basic['intro_m_use'] = ($basic['intro_use']) ? $basic['intro_use'] : 'N';
		}
		if (!isset($basic['general_m_use'])) { // 일반 모바일 운영 여부
			$basic['general_m_use'] = ($basic['general_use']) ? $basic['general_use'] : 'N';
		}
		if (!isset($basic['member_m_use'])) { // 회원 전용
			$basic['member_m_use'] = ($basic['member_use']) ? $basic['member_use'] : 'Y';
		}
		if (!isset($basic['adult_m_use'])) { // 성인 전용
			$basic['adult_m_use'] = ($basic['adult_use']) ? $basic['adult_use'] : 'Y';
		}

		$use_yn = $basic['operating']."_use";
		$operating = $basic[$use_yn];

		//모바일/태블릿
		$is_mobile = false;
		if($this->mobileMode || $this->storemobileMode){
			$is_mobile = true;
		}

		$operating_mobile = $basic[$basic['operating']."_m_use"];

		switch($basic['operating']){
			case "general":
				if(!$basic['intro_use']) $basic['intro_use'] = "N";
				if(!$is_mobile && $basic['intro_use']=="N" && $operating=="N" 
					|| $is_mobile && $basic['intro_m_use']=="N" && in_array($operating_mobile, array('N', 'P'))){		// 메인페이지 정상
					if($is_mobile && $operating_mobile=='P') {
						$this->operating_set_skin();
					}
					continue;
				}
				else if(!$is_mobile && $basic['intro_use']=="N" && $operating=="Y" 
					|| $is_mobile && $basic['intro_m_use']=="N" && $operating_mobile=="Y"){	// U:공사중, A:메인
					if(!$this->managerInfo) $this->operating_general_process();
				}
				else if(!$is_mobile && $basic['intro_use']=="Y" && $operating=="N" 
					|| $is_mobile && $basic['intro_m_use']=="Y" && in_array($operating_mobile, array('N', 'P'))){	// 인트로
					if($is_mobile && $operating_mobile=='P') {
						$this->operating_set_skin();
					}
					if(in_array('main',$this->uri->rsegments) && !$_SESSION['intro']){
						 $this->operating_intro_process();
					}
				}
				else if(!$is_mobile && $basic['intro_use']=="Y" && $operating=="Y" 
					|| $is_mobile && $basic['intro_m_use']=="Y" && $operating_mobile=="Y"){	//
					if(!$this->managerInfo){
						$this->operating_general_process();
					}else{
						if(in_array('main',$this->uri->rsegments) && !$_SESSION['intro']){
							 $this->operating_intro_process();
						}
					}
				}
				break;
			case "member":
				if(!$this->managerInfo){
					if(!$is_mobile && $operating=='Y' || $is_mobile && in_array($operating_mobile, array('Y', 'P'))) {
						if($is_mobile && $operating_mobile=='P') {
							$this->operating_set_skin();
						}
						$this->operating_member_process();
					}else {
						$this->operating_general_process();
					}
				}
				break;
			case "adult":
				if(!$this->managerInfo){
					if(!$is_mobile && $operating=='Y' || $is_mobile && in_array($operating_mobile, array('Y', 'P'))) {
						if($is_mobile && $operating_mobile=='P') {
							$this->operating_set_skin();
						}
						$this->operating_adult_process();
					} else {
						$this->operating_general_process();
					}
				}
				break;
		}
	}

	/* 공사중 */
	public function operating_general_process(){
		//한글도메인을 통한 플래시업로드
		if( stristr( $this ->input->user_agent(),'shockwave') || in_array('editor_image',$this->uri->rsegments) || in_array('category_navigation_html',$this->uri->rsegments) || in_array('brand_navigation_html',$this->uri->rsegments) || in_array('location_navigation_html',$this->uri->rsegments) ) return;
		if(in_array('register_sns_form',$this->uri->rsegments)) return;
		if(in_array('order',$this->uri->rsegments)) return;
		if(in_array('payment',$this->uri->rsegments)) return;
		if(in_array('lg_mobile',$this->uri->rsegments)) return;
		if(in_array('inicis_mobile',$this->uri->rsegments)) return;
		if(in_array('allat_mobile',$this->uri->rsegments)) return;
		if(in_array('kcp_mobile',$this->uri->rsegments)) return;
		if(in_array('naver_mileage',$this->uri->rsegments)) return;
		if(in_array('common',$this->uri->rsegments)) return;

		if(!in_array('intro',$this->uri->rsegments)){
			redirect("intro/construction");
			exit;
		}
	}
	/* 인트로 */
	public function operating_intro_process(){
		//한글도메인을 통한 플래시업로드
		if( stristr( $this ->input->user_agent(),'shockwave') || in_array('editor_image',$this->uri->rsegments) || in_array('category_navigation_html',$this->uri->rsegments) || in_array('brand_navigation_html',$this->uri->rsegments) || in_array('location_navigation_html',$this->uri->rsegments) ) return;
		if(in_array('member',$this->uri->rsegments)) return;
		if(in_array('order',$this->uri->rsegments)) return;
		if(in_array('payment',$this->uri->rsegments)) return;
		if(in_array('lg_mobile',$this->uri->rsegments)) return;
		if(in_array('inicis_mobile',$this->uri->rsegments)) return;
		if(in_array('allat_mobile',$this->uri->rsegments)) return;
		if(in_array('kcp_mobile',$this->uri->rsegments)) return;
		if(in_array('naver_mileage',$this->uri->rsegments)) return;

		if(!in_array('intro',$this->uri->rsegments)){
			redirect("intro/intro_main?".$_SERVER['QUERY_STRING']);
			exit;
		}
	}



	/* 회원전용 */
	public function operating_member_process(){
		//한글도메인을 통한 플래시업로드
		if( stristr( $this ->input->user_agent(),'shockwave') || in_array('editor_image',$this->uri->rsegments) || in_array('category_navigation_html',$this->uri->rsegments) || in_array('brand_navigation_html',$this->uri->rsegments) || in_array('location_navigation_html',$this->uri->rsegments) ) return;
		if(in_array('register_sns_form',$this->uri->rsegments)) return;

		if(isset($this->userInfo['member_seq'])) return;
		if(in_array('order',$this->uri->rsegments)) return;
		if(in_array('payment',$this->uri->rsegments)) return;
		if(in_array('lg_mobile',$this->uri->rsegments)) return;
		if(in_array('inicis_mobile',$this->uri->rsegments)) return;
		if(in_array('allat_mobile',$this->uri->rsegments)) return;
		if(in_array('kcp_mobile',$this->uri->rsegments)) return;
		if(in_array('naver_mileage',$this->uri->rsegments)) return;

		if(!in_array('common',$this->uri->rsegments) && !in_array('intro',$this->uri->rsegments) && !in_array('member',$this->uri->rsegments) && !in_array('member_process',$this->uri->rsegments) && !in_array('sns_process',$this->uri->rsegments) && !in_array('login',$this->uri->rsegments) && !in_array('login_process',$this->uri->rsegments) && !in_array('popup',$this->uri->rsegments)){
			pageRedirect("/intro/member_only");
			exit;
		}
	}

	/* 성인전용 */
	public function operating_adult_process(){

		//한글도메인을 통한 플래시업로드
		if( stristr( $this ->input->user_agent(),'shockwave') || in_array('editor_image',$this->uri->rsegments) || in_array('category_navigation_html',$this->uri->rsegments) || in_array('brand_navigation_html',$this->uri->rsegments) || in_array('location_navigation_html',$this->uri->rsegments) ) return;
		if(in_array('register_sns_form',$this->uri->rsegments)) return;
		if(isset($this->userInfo['member_seq'])) return;

		if($this->session->userdata('auth_intro')){
			$auth_intro = $this->session->userdata('auth_intro');
			if($auth_intro['auth_intro_yn']=='Y') return;
		}
		/**
		if($this->session->userdata('auth')){
			$auth = $this->session->userdata('auth');
			if($auth['auth_yn']=='Y') return;
		}
		**/
		if(!in_array('intro',$this->uri->rsegments) && !in_array('member',$this->uri->rsegments) && !in_array('member_process',$this->uri->rsegments)  && !in_array('sns_process',$this->uri->rsegments)  && !in_array('login',$this->uri->rsegments) && !in_array('login_process',$this->uri->rsegments) && !in_array('popup',$this->uri->rsegments)){
			pageRedirect("/intro/adult_only");
			exit;
		}
	}

	public function operating_set_skin(){

		// PC 스킨으로 설정 여부
		if (!$this->designMode) {
			if ($this->mobileMode) {
				$this->mobileMode = false;
				$this->skin = $this->config_system['skin'];
			} else if ($this->storemobileMode) {
				$this->storemobileMode = false;
				$this->skin = $this->config_system['storeSkin'];
			}

			$this->realSkin = $this->skin;
			$this->workingSkin = $this->skin;
		}
	}

}
?>