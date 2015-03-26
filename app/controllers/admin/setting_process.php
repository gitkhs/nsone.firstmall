<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class setting_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('admin/setting');

	}

	/* 판매환경 설정 */
	public function config()
	{

		$callback = "parent.document.location.reload();";

		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_basic_act');
		if(!$auth){
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		$mobilesize = sizeof($_POST['mobile_price1']);
		if($mobilesize) {
			for($i=0;$i<$mobilesize;$i++) {
				$price1					= $_POST['mobile_price1'][$i];
				$price2					= $_POST['mobile_price2'][$i];
				$sale_price			= (int) $_POST['mobile_sale_price'][$i];
				$sale_emoney		= (int) $_POST['mobile_sale_emoney'][$i];
				$sale_point				=  (int)  $_POST['mobile_sale_point'][$i];

				if($price1 == 0 && $price2 == 0  ){
					openDialogAlert("모바일/테블릿  추가할인시 상품의 구매금액이 모두 \"0\"일 수는 없습니다.",500,140,'parent',$callback);
					exit;
				}

				if($sale_price == 0 && $sale_emoney == 0 && $sale_point == 0 ){
					openDialogAlert("모바일/테블릿 추가할인 혜택과 추가적립 혜택이 모두 \"0\"일 수는 없습니다.",500,140,'parent',$callback);
					exit;
				}

				if($_POST['mobile_reserve_select'][$i]=='year'){
					if($_POST['mobile_reserve_year'][$i] > 9 || $_POST['mobile_reserve_year'][$i] < 0){
						//openDialogAlert("유효기간은 최대 9년 까지 설정할 수 있습니다.",500,140,'parent',$callback);
						//exit;
					}
				}

				if($_POST['mobile_point_select'][$i]=='year'){
					if($_POST['mobile_point_year'][$i] > 9 || $_POST['mobile_point_year'][$i] < 0){
						//openDialogAlert("유효기간은 최대 9년 까지 설정할 수 있습니다.",500,140,'parent',$callback);
						//exit;
					}
				}

			}
		}

		/*
		$fblikesize = sizeof($_POST['fblike_price1']);
		if($fblikesize) {
			for($i=0;$i<$fblikesize;$i++) {
				$price1					= $_POST['fblike_price1'][$i];
				$price2					= $_POST['fblike_price2'][$i];
				$sale_price			= (int) $_POST['fblike_sale_price'][$i];
				$sale_emoney		= (int) $_POST['fblike_sale_emoney'][$i];
				$sale_point				= (int) $_POST['fblike_sale_point'][$i];

				if($price1 == 0 && $price2 == 0  ){
					openDialogAlert("좋아요  추가할인시 상품의 구매금액이 모두 \"0\"일 수는 없습니다.",500,140,'parent',$callback);
					exit;
				}

				if($sale_price == 0 && $sale_emoney == 0 && $sale_point  == 0 ){
					openDialogAlert("좋아요 추가할인 혜택과 추가적립 혜택이 모두 \"0\"일 수는 없습니다.",450,140,'parent',$callback);
					exit;
				}

				if($_POST['fblike_reserve_select'][$i]=='year'){
					if($_POST['fblike_reserve_year'][$i] > 9 || $_POST['fblike_reserve_year'][$i] < 0){
						//openDialogAlert("유효기간은 최대 9년 까지 설정할 수 있습니다.",500,140,'parent',$callback);
						//exit;
					}
				}

				if($_POST['fblike_point_select'][$i]=='year'){
					if($_POST['fblike_point_year'][$i] > 9 || $_POST['fblike_point_year'][$i] < 0){
						//openDialogAlert("유효기간은 최대 9년 까지 설정할 수 있습니다.",500,140,'parent',$callback);
						//exit;
					}
				}
			}
		}
		*/

		$this->load->model('configsalemodel');
		$this->db->delete('fm_config_sale', array('type' => 'mobile'));
		//$this->db->delete('fm_config_sale', array('type' => 'fblike'));

		$mobilesize = sizeof($_POST['mobile_price1']);
		if($mobilesize) {
			for($i=0;$i<$mobilesize;$i++) {
				$price1					= $_POST['mobile_price1'][$i];
				$price2					= $_POST['mobile_price2'][$i];
				$sale_price			= (int) $_POST['mobile_sale_price'][$i];
				$sale_emoney		= (int) $_POST['mobile_sale_emoney'][$i];
				$sale_point				=  (int)  $_POST['mobile_sale_point'][$i];
				$params['type']				= 'mobile';
				$params['price1']				= $price1;
				$params['price2']				= $price2;
				$params['sale_price']		= $sale_price;
				$params['sale_emoney']		= $sale_emoney;

				###
				if($_POST['mobile_reserve_select'][$i]=='year'){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['mobile_reserve_year'][$i]));//$_POST['mobile_reserve_year'][$i]."-12-31";
				}else if($_POST['mobile_reserve_select'][$i]=='direct'){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['mobile_reserve_direct'][$i], date("d"), date("Y")));
				}else{
					$reserve_limit = "";
				}
				if($_POST['mobile_point_select'][$i]=='year'){
					$point_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['mobile_point_year'][$i]));//$_POST['mobile_point_year'][$i]."-12-31";
				}else if($_POST['mobile_point_select'][$i]=='direct'){
					$point_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['mobile_point_direct'][$i], date("d"), date("Y")));
				}else{
					$point_limit = "";
				}
				$params['reserve_limit']		= $reserve_limit;
				$params['reserve_select']		= $_POST['mobile_reserve_select'][$i];
				$params['reserve_year']			= $_POST['mobile_reserve_year'][$i];
				$params['reserve_direct']		= $_POST['mobile_reserve_direct'][$i];


				if( $this->isplusfreenot) {//무료몰이아닌경우에만 적용 @2013-01-14

					if($_POST['mobile_point_select'][$i]=='year'){
						$point_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['mobile_point_year'][$i]));//$_POST['mobile_point_year'][$i]."-12-31";
					}else if($_POST['mobile_point_select'][$i]=='direct'){
						$point_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['mobile_point_direct'][$i], date("d"), date("Y")));
					}else{
						$point_limit = "";
					}
					$params['sale_point']			= $sale_point;
					$params['point_limit']			= $point_limit;
					$params['point_select']		= ($_POST['mobile_point_select'][$i])?$_POST['mobile_point_select'][$i]:'';
					$params['point_year']			= ($_POST['mobile_point_year'][$i])?$_POST['mobile_point_year'][$i]:'';
					$params['point_direct']		= ($_POST['mobile_point_direct'][$i])?$_POST['mobile_point_direct'][$i]:'';
				}else{
					$params['sale_point']			= 0;
					$params['point_limit']			= '';
					$params['point_select']		= '';
					$params['point_year']			= '';
					$params['point_direct']		= '';
				}

				$params['regist_date']	= date("Y-m-d H:i:s");
				$params['add']	= "";
				$this->configsalemodel->confsale_write($params);//
			}
		}

		unset($params);

		/*
		$fblikesize = sizeof($_POST['fblike_price1']);
		if($fblikesize) {
			for($i=0;$i<$fblikesize;$i++) {
				$price1					= $_POST['fblike_price1'][$i];
				$price2					= $_POST['fblike_price2'][$i];
				$sale_price			= (int) $_POST['fblike_sale_price'][$i];
				$sale_emoney		= (int) $_POST['fblike_sale_emoney'][$i];
				$sale_point				= (int) $_POST['fblike_sale_point'][$i];

				$params['type']				= 'fblike';
				$params['price1']				= $price1;
				$params['price2']				= $price2;
				$params['sale_price']		= $sale_price;
				$params['sale_emoney']	= $sale_emoney;

				###

				if($_POST['fblike_reserve_select'][$i]=='year'){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['fblike_reserve_year'][$i]));//$_POST['fblike_reserve_year'][$i]."-12-31";
				}else if($_POST['fblike_reserve_select'][$i]=='direct'){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['fblike_reserve_direct'][$i], date("d"), date("Y")));
				}else{
					$reserve_limit = "";
				}
				if($_POST['fblike_point_select'][$i]=='year'){
					$point_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['fblike_point_year'][$i]));//$_POST['fblike_point_year'][$i]."-12-31";
				}else if($_POST['fblike_point_select'][$i]=='direct'){
					$point_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['fblike_point_direct'][$i], date("d"), date("Y")));
				}else{
					$point_limit = "";
				}
				$params['reserve_limit']		= $reserve_limit;
				$params['reserve_select']		= $_POST['fblike_reserve_select'][$i];
				$params['reserve_year']			= $_POST['fblike_reserve_year'][$i];
				$params['reserve_direct']		= $_POST['fblike_reserve_direct'][$i];


				$params['sale_point']			= ($sale_point)?$sale_point:0;
				$params['point_limit']			= $point_limit;
				$params['point_select']		= ($_POST['fblike_point_select'][$i])?$_POST['fblike_point_select'][$i]:'';
				$params['point_year']			= ($_POST['fblike_point_year'][$i])?$_POST['fblike_point_year'][$i]:'';
				$params['point_direct']		= ($_POST['fblike_point_direct'][$i])?$_POST['fblike_point_direct'][$i]:'';


				$params['regist_date']	= date("Y-m-d H:i:s");
				$params['add']	= "";
				$this->configsalemodel->confsale_write($params);
			}
		}
		*/

		/* 좋아요 설정저장 */
		//config_save('order',array('fblike_ordertype'=>$_POST['fblike_ordertype']));

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		// 할인혜택 금액 저장
		//$this->load->model('goodssummarymodel');
		//$this->goodssummarymodel->set_event_price();

		if($_POST['page_id_f']) config_save('snssocial',array('page_id_f'=>$_POST['page_id_f']));

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 기본설정 */
	public function basic()
	{
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_basic_act');
		if(!$auth){
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		/* 파비콘 파일 저장 */
		$favicon = $this->setting->upload_favicon();


		/* 인증 */
		$_POST['companyEmail'] = $_POST['companyEmail'][0]."@". $_POST['companyEmail'][1];
		$_POST['partnershipEmail'] = $_POST['partnershipEmail'][0]."@". $_POST['partnershipEmail'][1];
		$this->validation->set_rules('domain', '쇼핑몰 도메인','trim|prep_urlmax_length[50]|xss_clean');
		$this->validation->set_rules('shopName', '쇼핑몰 이름','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('shopBranch[]', '쇼핑몰 분류','trim|numeric|max_length[50]|xss_clean');
		$this->validation->set_rules('shopTitleTag', '쇼핑몰 타이틀','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('shopGoodsTitleTag', '쇼핑몰 상품상세페이지 타이틀','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('shopCategoryTitleTag', '쇼핑몰 카테고리페이지 타이틀','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('metaTagDescription', '메타태그 설명','trim|max_length[255]|xss_clean');
		$this->validation->set_rules('metaTagKeyword', '메타태그 키워드','trim|max_length[255]|xss_clean');
		$this->validation->set_rules('companyName', '상호','trim|max_length[50]|xss_clean');
		$this->validation->set_rules('businessConditions', '업태','trim|max_length[20]|xss_clean');
		$this->validation->set_rules('businessLine', '종목','trim|max_length[20]|xss_clean');
		$this->validation->set_rules('businessLicense[]', '사업자 번호','trim|numeric|max_length[6]|xss_clean');
		$this->validation->set_rules('mailsellingLicense', '통신판매업 신고번호','trim|xss_clean');
		$this->validation->set_rules('ceo', '대표자','trim|max_length[20]|xss_clean');
		$this->validation->set_rules('companyPhone[]', '연락처','trim|numeric|xss_clean');
		$this->validation->set_rules('companyFax[]', '팩스번호','trim|numeric|xss_clean');
		$this->validation->set_rules('companyEmail', '이메일','trim|max_length[50]|valid_email|xss_clean');
		$this->validation->set_rules('companyZipcode[]', '우편번호','trim|max_length[3]|numeric|xss_clean');
		$this->validation->set_rules('companyAddress', '주소','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('companyAddressDetail', '상세 주소','trim|max_length[100]|xss_clean');
		//$this->validation->set_rules('partnershipEmail', '입점문의 수신 이메일','trim|max_length[50]|valid_email|xss_clean');

		###
		config_save("reserve",array('default_reserve_bookmark'=>$_POST['default_reserve_bookmark']));
		config_save("reserve",array('book_reserve_select'=>$_POST['book_reserve_select']));
		config_save("reserve",array('book_reserve_year'=>$_POST['book_reserve_year']));
		config_save("reserve",array('book_reserve_direct'=>$_POST['book_reserve_direct']));

		if( $this->isplusfreenot ) {//무료몰이아닌경우에만 적용 @2013-01-14
			config_save("reserve",array('default_point_bookmark'=>$_POST['default_point_bookmark']));
			config_save("reserve",array('book_point_select'=>$_POST['book_point_select']));
			config_save("reserve",array('book_point_year'=>$_POST['book_point_year']));
			config_save("reserve",array('book_point_direct'=>$_POST['book_point_direct']));
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		/* 설정저장 */
		$this->setting->basic($favicon);

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 마케팅설정 */
	public function admin_marketing_conf()
	{
		if(!$_POST['marketdaum']) $_POST['marketdaum'] = 'n';
		if(!$_POST['marketabout']) $_POST['marketabout'] = 'n';
		if(!$_POST['marketnaver']) $_POST['marketnaver'] = 'n';
		config_save("marketing",array('marketdaum'=>$_POST['marketdaum']));
		config_save("marketing",array('marketabout'=>$_POST['marketabout']));
		config_save("marketing",array('marketnaver'=>$_POST['marketnaver']));

		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* fammerce plus 쇼핑몰 로고 */
	public function snsconf_snslogo(){

		$snslogo = $this->setting->upload_snslogo();
		$this->setting->snsconf($snslogo);
		$callback = "parent.snslogoDisplay('{$snslogo}?".time()."');";
		openDialogAlert("등록하였습니다.",400,140,'parent',$callback);
	}

	## 메타태그저장(sns 키워드,소개 포함)
	public function snsconf_snsmetatag(){

		config_save('basic',array('metaTagDescription'=>$_POST['metaTagDescription']));
		config_save('basic',array('metaTagKeyword'=>$_POST['metaTagKeyword']));

		echo  json_encode(array("result"=>true));
		exit;

	}

	/* sns마케팅 */
	public function snsconf()
	{

		$this->load->model('configsalemodel');

		## 좋아요 : 혜택 체크
		$fblikesize = sizeof($_POST['fblike_price1']);
		if($fblikesize) {
			for($i=0;$i<$fblikesize;$i++) {
				$price1					= $_POST['fblike_price1'][$i];
				$price2					= $_POST['fblike_price2'][$i];
				$sale_price			= (int) $_POST['fblike_sale_price'][$i];
				$sale_emoney		= (int) $_POST['fblike_sale_emoney'][$i];
				$sale_point				= (int) $_POST['fblike_sale_point'][$i];

				if($price1 == 0 && $price2 == 0  ){
					openDialogAlert("좋아요  추가할인시 상품의 구매금액이 모두 \"0\"일 수는 없습니다.",500,140,'parent',$callback);
					exit;
				}

				if($sale_price == 0 && $sale_emoney == 0 && $sale_point  == 0 ){
					openDialogAlert("좋아요 추가할인 혜택과 추가적립 혜택이 모두 \"0\"일 수는 없습니다.",450,140,'parent',$callback);
					exit;
				}

				if($_POST['fblike_reserve_select'][$i]=='year'){
					if($_POST['fblike_reserve_year'][$i] > 9 || $_POST['fblike_reserve_year'][$i] < 0){
						//openDialogAlert("유효기간은 최대 9년 까지 설정할 수 있습니다.",500,140,'parent',$callback);
						//exit;
					}
				}

				if($_POST['fblike_point_select'][$i]=='year'){
					if($_POST['fblike_point_year'][$i] > 9 || $_POST['fblike_point_year'][$i] < 0){
						//openDialogAlert("유효기간은 최대 9년 까지 설정할 수 있습니다.",500,140,'parent',$callback);
						//exit;
					}
				}
			}
		}

		## 좋아요 : 좋아요 사용일때 기존 데이터 삭제 후 재 등록
		if($_POST['fb_like_box_type']) $fb_like_box_type = $_POST['fb_like_box_type'];
		if($_POST['new_fb_like_box_type']) $fb_like_box_type = $_POST['new_fb_like_box_type'];
		if($fb_like_box_type != "NO"){
			$this->db->delete('fm_config_sale', array('type' => 'fblike'));
		}

		/* snslogo 파일 저장 */
		//$snslogo = $this->setting->upload_snslogo();
		/* 설정저장 */
		//$this->setting->snsconf($snslogo);

		## 친구초대관련 이동 => 회원>승인혜택
		//$snstitle			= ($_POST['snstitle'])?$_POST['snstitle']:'';
		//$snsDescription		= ($_POST['snsDescription'])?$_POST['snsDescription']:'';

		$likeurl			= ($_POST['likeurl'])?$_POST['likeurl']:$this->config_system['subDomain'];
		$snscaption			= ($_POST['snscaption'])?$_POST['snscaption']:'';

		## facebook, twitter 로그인 사용
		config_save('snssocial',array('use_f'=>$_POST['use_f'],'use_t'=>$_POST['use_t']));
		config_save('joinform',array('use_f'=>$_POST['use_f'],'use_t'=>$_POST['use_t']));

		config_save('snssocial',array('likeurl'=> $likeurl));
		config_save('snssocial',array('snscaption'=> $snscaption));
		## 친구초대관련 이동 => 회원>승인혜택
		//config_save('snssocial',array('snstitle'=> $snstitle));
		//config_save('snssocial',array('snsDescription'=> $snsDescription));

		//짧은 주소 설정
		$shorturl_use		= ($_POST['shorturl_use'])?$_POST['shorturl_use']:'N';
		config_save('snssocial',array('shorturl_use'=> $shorturl_use));
		//config_save('snssocial',array('shorturl_app_id'=> $shorturl_app_id));
		//config_save('snssocial',array('shorturl_app_key'=> $shorturl_app_key));
		
		//$kakaotalk_use							= ($_POST['kakaotalk_use'])?$_POST['kakaotalk_use']:'N';
		/*
		$kakaotalk_app_domain				= ($_POST['kakaotalk_app_domain'])?$_POST['kakaotalk_app_domain']:'';
		$kakaotalk_app_javascript_key	= ($_POST['kakaotalk_app_javascript_key'])?$_POST['kakaotalk_app_javascript_key']:'';
		if( ($kakaotalk_app_domain && !$kakaotalk_app_javascript_key) || (!$kakaotalk_app_domain && $kakaotalk_app_javascript_key)  ){
			openDialogAlert("카카오톡의 모바일 도메인주소/API Javascript Key 를 정확히 입력해 주세요!",550,140,'parent',$callback);
			exit;
		}
		*/
		
		//카카오톡 설정
		//config_save('snssocial',array('kakaotalk_use'=> $kakaotalk_use));
		/*
		config_save('snssocial',array('kakaotalk_app_domain'=> $kakaotalk_app_domain));
		config_save('snssocial',array('kakaotalk_app_javascript_key'=> $kakaotalk_app_javascript_key));
		config_save('snssocial',array('key_k'=>$_POST['key_k']));
		*/

		$facebook_review = ($_POST['facebook_review'])?$_POST['facebook_review']:'';
		$facebook_interest = ($_POST['facebook_interest'])?$_POST['facebook_interest']:'';
		$facebook_buy = ($_POST['facebook_buy'])?$_POST['facebook_buy']:'';
		config_save('snssocial',array('facebook_review'=> $facebook_review));
		config_save('snssocial',array('facebook_interest'=> $facebook_interest));
		config_save('snssocial',array('facebook_buy'=> $facebook_buy));

		$snssocialold = ($this->arrSns)?$this->arrSns:config_load('snssocial');
		if( $snssocialold['facebook_app'] == 'new' ){
			$fb_like_box_type = ($_POST['new_fb_like_box_type'])?$_POST['new_fb_like_box_type']:'API';
		}else{
			$fb_like_box_type = ($_POST['fb_like_box_type'])?$_POST['fb_like_box_type']:'API';
		}
		config_save('snssocial',array('fb_like_box_type'=> $fb_like_box_type));

		## 좋아요 혜택 설정
		if($fblikesize) {
			for($i=0;$i<$fblikesize;$i++) {
				$price1					= $_POST['fblike_price1'][$i];
				$price2					= $_POST['fblike_price2'][$i];
				$sale_price			= (int) $_POST['fblike_sale_price'][$i];
				$sale_emoney		= (int) $_POST['fblike_sale_emoney'][$i];
				$sale_point				= (int) $_POST['fblike_sale_point'][$i];

				$params['type']				= 'fblike';
				$params['price1']				= $price1;
				$params['price2']				= $price2;
				$params['sale_price']		= $sale_price;
				$params['sale_emoney']	= $sale_emoney;

				###

				if($_POST['fblike_reserve_select'][$i]=='year'){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['fblike_reserve_year'][$i]));//$_POST['fblike_reserve_year'][$i]."-12-31";
				}else if($_POST['fblike_reserve_select'][$i]=='direct'){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['fblike_reserve_direct'][$i], date("d"), date("Y")));
				}else{
					$reserve_limit = "";
				}
				if($_POST['fblike_point_select'][$i]=='year'){
					$point_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['fblike_point_year'][$i]));//$_POST['fblike_point_year'][$i]."-12-31";
				}else if($_POST['fblike_point_select'][$i]=='direct'){
					$point_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['fblike_point_direct'][$i], date("d"), date("Y")));
				}else{
					$point_limit = "";
				}
				$params['reserve_limit']		= $reserve_limit;
				$params['reserve_select']		= $_POST['fblike_reserve_select'][$i];
				$params['reserve_year']			= $_POST['fblike_reserve_year'][$i];
				$params['reserve_direct']		= $_POST['fblike_reserve_direct'][$i];


				$params['sale_point']			= ($sale_point)?$sale_point:0;
				$params['point_limit']			= $point_limit;
				$params['point_select']		= ($_POST['fblike_point_select'][$i])?$_POST['fblike_point_select'][$i]:'';
				$params['point_year']			= ($_POST['fblike_point_year'][$i])?$_POST['fblike_point_year'][$i]:'';
				$params['point_direct']		= ($_POST['fblike_point_direct'][$i])?$_POST['fblike_point_direct'][$i]:'';


				$params['regist_date']	= date("Y-m-d H:i:s");
				$params['add']	= "";
				$this->configsalemodel->confsale_write($params);
			}
		}

		/* 좋아요 설정저장 */
		config_save('order',array('fblike_ordertype'=>$_POST['fblike_ordertype']));

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);

	}

	## 짧은 URL 설정.
	public function snsconf_shorturl(){

		//짧은 주소 설정
		$shorturl_use		= $_POST['shorturl_use2'];
		$shorturl_app_id	= $_POST['shorturl_app_id'];
		$shorturl_app_key	= $_POST['shorturl_app_key'];
		config_save('snssocial',array('shorturl_use'=> $shorturl_use));
		config_save('snssocial',array('shorturl_app_id'=> $shorturl_app_id));
		config_save('snssocial',array('shorturl_app_key'=> $shorturl_app_key));

		$shorturl_test	= 'http://'.$this->config_system['domain'].'/personal_referer/access?inflow=shorturl&mid=1';
		$shorturl		= get_shortURL($shorturl_test);
		echo json_encode(array("result"=>true,'shorturl'=>$shorturl));

	}

	/* 운영정책 설정 */
	public function operating(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_operating_act');
		if(!$auth){
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		config_save('basic',array('operating'=>$_POST['operating']));
		if(isset($_POST['general_use'])) config_save('basic',array('general_use'=>$_POST['general_use']));
		if(isset($_POST['intro_use'])) config_save('basic',array('intro_use'=>$_POST['intro_use']));
		if(isset($_POST['member_use'])) config_save('basic',array('member_use'=>$_POST['member_use']));
		if(isset($_POST['adult_use'])) config_save('basic',array('adult_use'=>$_POST['adult_use']));

		// 운영방식 모바일(태블릿) 추가 2014-05-20 leewh
		if(isset($_POST['intro_m_use'])) config_save('basic',array('intro_m_use'=>$_POST['intro_m_use']));
		if(isset($_POST['general_m_use'])) config_save('basic',array('general_m_use'=>$_POST['general_m_use']));
		if(isset($_POST['member_m_use'])) config_save('basic',array('member_m_use'=>$_POST['member_m_use']));
		if(isset($_POST['adult_m_use'])) config_save('basic',array('adult_m_use'=>$_POST['adult_m_use']));

		if(isset($_POST['adult_use']) && $_POST['adult_use']){
			$realname = config_load('realname');
			if( $realname['realnameId'] && $realname['realnamePwd'] ){
				config_save('realname',array('useRealname'=>'Y'));
			}
			if( $realname['ipinSikey'] && $realname['ipinKeyString'] ){
				config_save('realname',array('useIpin'=>'Y'));
			}
		}

		$callback = "parent.document.location.reload();";
		if(isset($_POST['adult_use']) && $_POST['adult_use']){
			openDialogAlert("접속 및 회원가입시에 성인인증을 사용하도록 자동 설정되었습니다.<br>설정>회원>본인확인에서 확인하실 수 있습니다.",550,170,'parent',$callback);
		}else{
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	/* 파비콘 삭제 */
	public function favicon_delete(){
		$favicon = config_load('system', 'favicon');
		@unlink('./'.$favicon['favicon']);
		config_save('system',array('favicon'=>''));
		echo json_encode(array('result'=>'ok'));
	}

	/* snslogo 삭제 */
	public function snslogo_delete(){
		$snslogo = config_load('system', 'snslogo');
		@unlink('.'.$snslogo['snslogo']);
		$this->setting->snsconf("");
		echo json_encode(array('result'=>'ok','message'=>$delpath));
	}

	//fblike icon save
	public function fblikeiconUpload(){
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['fblikeboxpciconFile']['tmp_name'])) {

			$dir = ROOTPATH.'/data/icon/facebook_like';
			if(!is_dir($dir) ) {
				@mkdir($dir);
				@chmod($dir,0707);
			}

			$config['upload_path'] = './data/icon/facebook_like';
			$config['max_size']	= $this->config_system['uploadLimit'];
			$tmp = getimagesize($_FILES['fblikeboxpciconFile']['tmp_name']);
			$_FILES['fblikeboxpciconFile']['type'] = $tmp['mime'];

			$config['upload_path']		= $path = ROOTPATH."/data/icon/facebook_like/";
			$file_ext = end(explode('.', $_FILES['fblikeboxpciconFile']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$file_name	= 'fblikebox.'.$file_ext;
			$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
			$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
			$config['file_name'] = $file_name;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('fblikeboxpciconFile')) {
				@chmod($config['upload_path'].$config['file_name'], 0777);

				$uploadData = $this->upload->data();
				$fb_likebox_icon = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext'];
				config_save('snssocial',array('fb_likebox_icon'=> $fb_likebox_icon));
				$callback = "parent.fblikeiconDisplay('{$fb_likebox_icon}?".time()."');";
				openDialogAlert("등록하였습니다.",400,140,'parent',$callback);
			}else{
				$callback = "";
				openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);
			}
		}else{
			$callback = "";
			openDialogAlert("등록 파일이 없습니다.",400,140,'parent',$callback);
		}
		exit;
	}

	//fbunlike icon save
	public function fbunlikeiconUpload(){
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['fbunlikeboxpciconFile']['tmp_name'])) {

			$dir = ROOTPATH.'/data/icon/facebook_like';
			if(!is_dir($dir) ) {
				@mkdir($dir);
				@chmod($dir,0707);
			}

			$config['upload_path'] = './data/icon/facebook_like';
			$config['max_size']	= $this->config_system['uploadLimit'];
			$tmp = getimagesize($_FILES['fbunlikeboxpciconFile']['tmp_name']);
			$_FILES['fbunlikeboxpciconFile']['type'] = $tmp['mime'];

			$config['upload_path']		= $path = ROOTPATH."/data/icon/facebook_like/";
			$file_ext = end(explode('.', $_FILES['fbunlikeboxpciconFile']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$file_name	= 'fbunlikebox.'.$file_ext;
			$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
			$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
			$config['file_name'] = $file_name;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('fbunlikeboxpciconFile')) {
				@chmod($config['upload_path'].$config['file_name'], 0777);

				$uploadData = $this->upload->data();
				$fb_likebox_icon = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext'];
				config_save('snssocial',array('fb_unlikebox_icon'=> $fb_likebox_icon));
				$callback = "parent.fbunlikeiconDisplay('{$fb_likebox_icon}?".time()."');";
				openDialogAlert("등록하였습니다.",400,140,'parent',$callback);
			}else{
				$callback = "";
				openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);
			}
		}else{
			$callback = "";
			openDialogAlert("등록 파일이 없습니다.",400,140,'parent',$callback);
		}
		exit;
	}


	/* fblike 삭제 */
	public function fblike_delete(){
		if( $_GET['fblikemode'] == 'unlike' ){
			$snsinfo = config_load('snssocial', 'fb_unlikebox_icon');
			@unlink('./'.$snsinfo['fb_unlikebox_icon']);
			config_save('snssocial',array('fb_unlikebox_icon'=>''));
		}else{
			$snsinfo = config_load('snssocial', 'fb_likebox_icon');
			@unlink('./'.$snsinfo['fb_likebox_icon']);
			config_save('snssocial',array('fb_likebox_icon'=>''));
		}
		echo json_encode(array('result'=>'ok'));
	}

	/* 은행설정 */
	public function bank(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_bank_act');
		if(!$auth){
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		/* 인증 */
		$this->validation->set_rules('bank[]', '은행','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('bankUser[]', '예금주','trim|xss_clean');
		$this->validation->set_rules('account[]', '계좌번호','trim|xss_clean');
		$this->validation->set_rules('accountUse[]', '사용여부','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('bankReturn[]', '반품배송비 입금계좌 은행','trim|max_length[10]|xss_clean');
		$this->validation->set_rules('bankUserReturn[]', '반품배송비 입금계좌 예금주','trim|xss_clean');
		$this->validation->set_rules('accountReturn[]', '반품배송비 입금계좌 계좌번호','trim|xss_clean');
		$this->validation->set_rules('accountUseReturn[]', '반품배송비 입금계좌 사용여부','trim|max_length[10]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		/* 설정 초기화 */
		$this->setting->bank();

		/* 설정 초기화 */
		$this->setting->bank2();

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,150,'parent',$callback);
	}

	/* 주문설정 */
	public function order(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_order_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		/* 인증 */
		$_POST['ableStockLimit'] = (int) $_POST['ableStockLimit'];
		$_POST['cartDuration'] = (int) $_POST['cartDuration'];
		$_POST['cancelDuration'] = (int) $_POST['cancelDuration'];
		$_POST['ableStockStep'] = (int) $_POST['ableStockStep'];
		$_POST['refundDuration'] = (int) $_POST['refundDuration'];

		$_POST['cashreceiptuse']		=  if_empty($_POST, 'cashreceiptuse', '0');
		$_POST['taxuse']		=  if_empty($_POST, 'taxuse', '0');

		$this->validation->set_rules('runout', '재고에 따른 상품판매 여부','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('ableStockLimit', '가용재고 품절표기 갯수','trim|numeric|xss_clean');
		$this->validation->set_rules('cartDuration', '장바구니 상품 보존기간','trim|required|numeric|xss_clean');
		$this->validation->set_rules('cancelDuration', '자동 주문 취소일','trim|required|numeric|xss_clean');
		$this->validation->set_rules('ableStockStep', '출고예약량','trim|required|numeric|xss_clean');
		//$this->validation->set_rules('refundDuration', '반품,환불,맞교환 가능일','trim|required|numeric|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,100,'parent',$callback);
			exit;
		}

		/* 설정저장 */
		config_save('order',array('runout'=>$_POST['runout']));
		config_save('order',array('ableStockLimit'=>$_POST['ableStockLimit']));
		config_save('order',array('cartDuration'=>$_POST['cartDuration']));
		config_save('order',array('cancelDuration'=>$_POST['cancelDuration']));
		config_save('order',array('ableStockStep'=>$_POST['ableStockStep']));
		config_save('order',array('refundDuration'=>$_POST['refundDuration']));
		config_save('order',array('autocancel'=>$_POST['autocancel']));
		config_save('order',array('export_err_handling'=>$_POST['export_err_handling']));

		config_save('order' ,array('buy_confirm_use'=>$_POST['buy_confirm_use']));
		config_save('order' ,array('save_term'=>$_POST['save_term']));
		config_save('order' ,array('save_type'=>$_POST['save_type']));

		config_save('order' ,array('cancellation'=>$_POST['cancellation']));//청약철회추가
		config_save('order' ,array('cancelDisabledStep35'=>$_POST['cancelDisabledStep35']));

		/* 절사 */
		config_save('system',array('cutting_sale_use'=>$_POST['cutting_sale_use']));
		config_save('system',array('cutting_sale_price'=>$_POST['cutting_sale_price']));
		config_save('system',array('cutting_sale_action'=>$_POST['cutting_sale_action']));
		config_save('system',array('cutting_settle_use'=>$_POST['cutting_settle_use']));
		config_save('system',array('cutting_settle_price'=>$_POST['cutting_settle_price']));
		config_save('system',array('cutting_settle_action'=>$_POST['cutting_settle_action']));

		//매출증빙설정
		/*
		config_save('order',array('cashreceiptuse'=>$_POST['cashreceiptuse']));
		config_save('order',array('biztype'=>$_POST['biztype']));
		config_save('order',array('cashreceiptpg'=>$_POST['cashreceiptpg']));
		config_save('order',array('cashreceiptid'=>$_POST['cashreceiptid']));
		config_save('order',array('cashreceiptkey'=>$_POST['cashreceiptkey']));
		config_save('order',array('taxuse'=>$_POST['taxuse']));
		*/

		$codecd = $_POST['codecd'];
		$reason = $_POST['reason'];
		$idx = 1;
		$this->db->query('delete from fm_return_reason where return_type = "goods" ');
		for($i=0; $i<count($codecd); $i++){
			unset($params);
			$params["return_type"] = 'goods';
			$params["idx"] = $idx;
			$params["codecd"] = $codecd[$i];
			$params["reason"] = $reason[$i];
			if($params["reason"] != ""){
				$this->db->insert('fm_return_reason', $params);
				$idx++;
			}
		}

		//쿠폰상품 환불사유
		$codecdcoupon = $_POST['codecdcoupon'];
		$reasoncoupon = $_POST['reasoncoupon'];
		$idx = 1;
		$this->db->query('delete from fm_return_reason where return_type = "coupon" ');
		for($i=0; $i<count($codecdcoupon); $i++){
			unset($params);
			$params["return_type"] = 'coupon';
			$params["idx"] = $idx;
			$params["codecd"] = $codecdcoupon[$i];
			$params["reason"] = $reasoncoupon[$i];
			if($params["reason"] != ""){
				$this->db->insert('fm_return_reason', $params);
				$idx++;
			}
		}

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 주문설정 */
	public function sale(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_order_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		//매출증빙설정
		config_save('order',array('cashreceiptuse'=>$_POST['cashreceiptuse']));
		config_save('order',array('biztype'=>$_POST['biztype']));
		config_save('order',array('cashreceiptpg'=>$_POST['cashreceiptpg']));
		config_save('order',array('cashreceiptid'=>$_POST['cashreceiptid']));
		config_save('order',array('cashreceiptkey'=>$_POST['cashreceiptkey']));
		config_save('order',array('taxuse'=>$_POST['taxuse']));
		config_save('order',array('sale_reserve_yn'=>$_POST['sale_reserve_yn']));
		config_save('order',array('sale_emoney_yn'=>$_POST['sale_emoney_yn']));





		###
		config_save('order',array('hiworks_use'=>$_POST['hiworks_use']));
		config_save('order',array('cashreceipt_auto'=>$_POST['cashreceipt_auto']));

		config_save('system',array('webmail_admin_id'=>$_POST['webmail_admin_id']));
		config_save('system',array('webmail_domain'=>$_POST['webmail_domain']));
		config_save('system',array('webmail_key'=>$_POST['webmail_key']));

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function shipping(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_shipping_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		$result = false;

		$this->validation->set_rules('shipping', '배송방법','trim|required|xss_clean');
		if($_POST['shipping']!='add_delivery'){
			$this->validation->set_rules('useYn', '사용설정','trim|required|max_length[1]|xss_clean');
			$this->validation->set_rules('summary', '설명','trim|max_length[100]|xss_clean');
		}

		/* 택배일 경우 */
		if($_POST['shipping']=='delivery'){
			$this->validation->set_rules('deliveryCompanyCode[]', '택배사','trim|required|xss_clean');
			$this->validation->set_rules('deliveryCostPolicy', '기본 배송비 선택','trim|max_length[5]|required|xss_clean');
			if($_POST['deliveryCostPolicy']=='pay'){
				$this->validation->set_rules('payDeliveryCost', '배송비','trim|required|xss_clean|numeric');
			}
			if($_POST['deliveryCostPolicy']=='ifpay'){
				$this->validation->set_rules('ifpayFreePrice', '상품판매가격의 합','trim|required|xss_clean|numeric');
				$this->validation->set_rules('ifpayDeliveryCost', '배송비','trim|required|xss_clean|numeric');
			}
			if($_POST['deliveryCostPolicy']=='pay'){
				$this->validation->set_rules('payDeliveryCost', '배송비','trim|required|xss_clean|numeric');
			}

		}

		if($_POST['shipping']=='add_delivery'){
			foreach($_POST['sigungu'] as $k => $sigungu){
				if(!$sigungu){
					unset($_POST['sigungu'][$k]);
					unset($_POST['addDeliveryCost'][$k]);
				}else{
					$result = true;
				}
			}

			if($result){
				$this->validation->set_rules('addDeliveryCost[]', '추가 배송비','trim|xss_clean|numeric');
			}else{
				$_POST['sigungu'][0] = "";
				$_POST['addDeliveryCost'][0] = 0;
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,200,'parent',$callback);
			exit;
		}

		if($_POST['shipping']!='add_delivery'){
			/* 설정저장 */
			$shipping = 'shipping'.$_POST['shipping'];
			config_save($shipping ,array('useYn'=>$_POST['useYn']));
			config_save($shipping ,array('summary'=>$_POST['summary']));
		}

		/* 택배일 경우 */
		if($_POST['shipping']=='delivery'){
			config_save($shipping ,array('deliveryCompanyCode'=>$_POST['deliveryCompanyCode']));
			config_save($shipping ,array('deliveryCostPolicy'=>$_POST['deliveryCostPolicy']));
			config_save($shipping ,array('payDeliveryCost'=>$_POST['payDeliveryCost']));
			config_save($shipping ,array('postpaidDeliveryCostYn'=>$_POST['postpaidDeliveryCostYn']));
			config_save($shipping ,array('postpaidDeliveryCost'=>$_POST['postpaidDeliveryCost']));
			config_save($shipping ,array('ifpayFreePrice'=>$_POST['ifpayFreePrice']));
			config_save($shipping ,array('ifpayDeliveryCost'=>$_POST['ifpayDeliveryCost']));
			config_save($shipping ,array('ifpostpaidDeliveryCostYn'=>$_POST['ifpostpaidDeliveryCostYn']));
			config_save($shipping ,array('ifpostpaidDeliveryCost'=>$_POST['ifpostpaidDeliveryCost']));

			config_save($shipping ,array('orderDeliveryFree'=>$_POST['orderDeliveryFree']));
			config_save($shipping ,array('issueCategoryCode'=>$_POST['issueCategoryCode']));
			config_save($shipping ,array('issueBrandCode'=>$_POST['issueBrandCode']));
			config_save($shipping ,array('issueGoods'=>$_POST['issueGoods']));
			config_save($shipping ,array('exceptIssueGoods'=>$_POST['exceptIssueGoods']));

			config_save($shipping ,array('multiDeliveryUseYn'=>$_POST['multiDeliveryUseYn']));
		}

		if($_POST['shipping']=='add_delivery'){
			config_save('shippingdelivery' ,array('sigungu'=>$_POST['sigungu']));
			config_save('shippingdelivery' ,array('sigungu_street'=>$_POST['sigungu_street']));
			config_save('shippingdelivery' ,array('addDeliveryCost'=>$_POST['addDeliveryCost']));
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function international_shipping(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_shipping_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		$this->validation->set_rules('useYn','사용설정','trim|required|max_length[1]|xss_clean');
		$this->validation->set_rules('company','방법','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('summary','설명','trim|max_length[70]|xss_clean');
		$this->validation->set_rules('defaultGoodsWeight', '설명','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('goodsWeight[]','무게','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('region[]','해외 지역','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('regionSummary[]','해외 지역 설명','trim|max_length[30]|xss_clean');
		$this->validation->set_rules('deliveryCost[]','배송비','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('exceptCategory[]','카테고리','trim|max_length[16]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,100,'parent',$callback);
			exit;
		}
		$groupcd = "internationalShipping".$_POST['company'];
		config_save($groupcd ,array('useYn'=>$_POST['useYn']));
		config_save($groupcd ,array('company'=>$_POST['company']));
		config_save($groupcd ,array('summary'=>$_POST['summary']));
		config_save($groupcd ,array('defaultGoodsWeight'=>$_POST['defaultGoodsWeight']));
		config_save($groupcd ,array('region'=>$_POST['region']));
		config_save($groupcd ,array('regionSummary'=>$_POST['regionSummary']));
		config_save($groupcd ,array('goodsWeight'=>$_POST['goodsWeight']));
		config_save($groupcd ,array('deliveryCost'=>$_POST['deliveryCost']));
		config_save($groupcd ,array('exceptCategory'=>$_POST['exceptCategory']));

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	//
	public function reserve(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_reserve_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		### Validation
		$this->validation->set_rules('default_reserve_percent','기본 적립금 적용 상품','trim|numeric|max_length[3]|xss_clean');
		$this->validation->set_rules('emoney_use_limit','보유 적립금 기준','trim|numeric|xss_clean');
		$this->validation->set_rules('emoney_price_limit','상품 판매가격 기준','trim|numeric|xss_clean');
		$this->validation->set_rules('min_emoney','최소 적립금 사용금액','trim|numeric|xss_clean');
		$this->validation->set_rules('max_emoney_percent','최대 적립금 사용금액','trim|numeric|max_length[3]|xss_clean');
		$this->validation->set_rules('max_emoney','최대 적립금 사용금액','trim|numeric|xss_clean');
		$this->validation->set_rules('max_emoney_policy','최대 적립금 사용정책','trim|required|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,130,'parent',$callback);
			exit;
		}
		$groupcd = "reserve";
		config_save($groupcd ,array('default_reserve_percent'=>$_POST['default_reserve_percent']));
		//config_save($groupcd ,array('default_reserve_bookmark'=>$_POST['default_reserve_bookmark']));
		config_save($groupcd ,array('emoney_use_limit'=>$_POST['emoney_use_limit']));
		config_save($groupcd ,array('emoney_price_limit'=>$_POST['emoney_price_limit']));
		config_save($groupcd ,array('min_emoney'=>$_POST['min_emoney']));
		config_save($groupcd ,array('max_emoney_percent'=>$_POST['max_emoney_percent']));
		config_save($groupcd ,array('max_emoney'=>$_POST['max_emoney']));
		config_save($groupcd ,array('max_emoney_policy'=>$_POST['max_emoney_policy']));


		config_save($groupcd ,array('emoney_exchange_use'=>$_POST['emoney_exchange_use']));
		config_save($groupcd ,array('emoney_minum_point'=>$_POST['minum_point']));
		config_save($groupcd ,array('emoney_point_rate'=>$_POST['point_rate']));



		### ADD 201211
		config_save($groupcd ,array('cash_use'=>$_POST['cash_use']));
		//config_save($groupcd ,array('save_step'=>$_POST['save_step']));
		//config_save($groupcd ,array('save_term'=>$_POST['save_term']));
		//config_save($groupcd ,array('save_type'=>$_POST['save_type']));

		config_save($groupcd ,array('reserve_select'=>$_POST['reserve_select']));
		config_save($groupcd ,array('reserve_year'=>$_POST['reserve_year']));
		config_save($groupcd ,array('reserve_direct'=>$_POST['reserve_direct']));


		config_save($groupcd ,array('exchange_emoney_select'=>$_POST['exchange_emoney_select']));
		config_save($groupcd ,array('exchange_emoney_year'=>$_POST['exchange_emoney_year']));
		config_save($groupcd ,array('exchange_emoney_direct'=>$_POST['exchange_emoney_direct']));

		$_POST['refundDuration'] = (int) $_POST['refundDuration'];
		config_save('order',array('refundDuration'=>$_POST['refundDuration']));
		if( $this->isplusfreenot) {//무료몰이아닌경우에만 적용@2013-01-14
			config_save($groupcd ,array('point_use'=>$_POST['point_use']));
			config_save($groupcd ,array('default_point_app'=>$_POST['default_point_app']));
			config_save($groupcd ,array('default_point_type'=>$_POST['default_point_type']));
			config_save($groupcd ,array('default_point_percent'=>$_POST['default_point_percent']));
			config_save($groupcd ,array('default_point'=>$_POST['default_point']));
			config_save($groupcd ,array('point_select'=>$_POST['point_select']));
			config_save($groupcd ,array('point_year'=>$_POST['point_year']));
			config_save($groupcd ,array('point_direct'=>$_POST['point_direct']));
		}

		/* 적립금 설정 관련 기본값 추가 leewh 2014-06-24 */
		config_save($groupcd ,array('emoney_using_unit'=>$_POST['emoney_using_unit']));
		config_save($groupcd ,array('default_reserve_limit'=>$_POST['default_reserve_limit']));

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 보안설정 저장 */
	public function protect(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_protect_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('setting_protect_act');
		if(!$auth){
			$callback = "parent.history.go(-1);";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		if(!isset($_POST['ssl'])) $_POST['ssl'] = "";
		if(!isset($_POST['protectMouseRight'])) $_POST['protectMouseRight'] = "";
		if(!isset($_POST['protectMouseDragcopy'])) $_POST['protectMouseDragcopy'] = "";
		if(!isset($_POST['protectIp'])) $_POST['protectIp'] = array();

		/* 설정저장 */
		$this->setting->protect();

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}


	/* 관리자 등록 */
	public function manager_reg() {

		if( $this->isdemo['isdemo'] ){
			$callback = "parent.document.location.reload();";
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_manager_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### required
		$this->validation->set_rules('manager_id', '아이디','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('mpasswd', '비밀번호','trim|required|min_length[10]|max_length[32]|xss_clean');
		$this->validation->set_rules('mpasswd_re', '비밀번호확인','trim|required|min_length[10]|max_length[32]|xss_clean');
		$this->validation->set_rules('mname', '이름','trim|required|max_length[32]|xss_clean');
		###
		$this->validation->set_rules('memail', '이메일','trim|max_length[64]|valid_email|xss_clean');
		$this->validation->set_rules('mcellphone', '휴대폰번호','trim|max_length[20]|xss_clean');
		$this->validation->set_rules('mphone', '연락처','trim|max_length[20]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if(isset($_POST['ip_chk']) && $_POST['ip_chk']=='Y'){
			$limit_ip = "";
			foreach($_POST["limit_ip1"] as $key=>$value){
				
				for($i=1; $i<=4; $i++){
					$value_chk_cnt = 0;
					if(preg_match("/[a-z]/",$_POST["limit_ip".$i][$key])) $value_chk_cnt += 1;					
					if(preg_match("/[A-Z]/",$_POST["limit_ip".$i][$key])) $value_chk_cnt += 1;
					if(preg_match("/[!#$%^&*()?+=\/]/",$_POST["limit_ip".$i][$key])) $value_chk_cnt += 1;

					if($_POST["limit_ip".$i][$key]){
						if($_POST["limit_ip".$i][$key] < 0 || $_POST["limit_ip".$i][$key] > 255 || $value_chk_cnt > 0){
							openDialogAlert("아이피 대역이 잘못되었습니다.<br>아이피 대역은 0~255 사이의 숫자만 입력해주세요",400,140,'parent',$callback);
							exit;
						}
					}
				}

				$limit_ip .= $_POST["limit_ip1"][$key].".".$_POST["limit_ip2"][$key].".".$_POST["limit_ip3"][$key];
				if(trim($_POST["limit_ip4"][$key])){
					$limit_ip .= ".".$_POST["limit_ip4"][$key]."|";
				}else{
					$limit_ip .= "|";
				}
			}

			$_POST['limit_ip'] = $limit_ip;
		}



		if	(preg_match('/[a-zA-Z]/', $_POST['mpasswd']))		$useChar++;
		if	(preg_match('/[0-9]/', $_POST['mpasswd']))			$useChar++;
		if	(preg_match('/[^a-zA-Z0-9]/', $_POST['mpasswd']))	$useChar++;
		if	(preg_match("/[!#$%^&*()?+=\/]/",$_POST['mpasswd']))	$useChar++;

		if	($useChar < 2){
			$callback = "parent.document.getElementsByName('mpasswd_re')[0].focus();";
			openDialogAlert("비밀번호는 영문 대소문자 또는 숫자, 특수문자 중 2가지 이상 조합이어야 합니다.",400,140,'parent',$callback);
			exit;
		}
		if($_POST['mpasswd'] != $_POST['mpasswd_re']){
			$callback = "parent.document.getElementsByName('mpasswd_re')[0].focus();";
			openDialogAlert("비밀번호 확인이 다릅니다.",400,140,'parent',$callback);
			exit;
		}

		### 회원정보 다운로드 비밀번호 체크 leewh 2014-07-10
		if ($_POST['member_download']=='Y') {
			$this->validation->set_rules('member_download_passwd', '회원엑셀다운비밀번호','trim|required|min_length[8]|max_length[20]|xss_clean');
			if	(preg_match('/[a-zA-Z]/', $_POST['member_download_passwd']))		$useMemDownChar++;
			if	(preg_match('/[0-9]/', $_POST['member_download_passwd']))			$useMemDownChar++;
			if	(preg_match('/[^a-zA-Z0-9]/', $_POST['member_download_passwd']))	$useMemDownChar++;
			if	($useMemDownChar < 2){
				$callback = "parent.document.getElementsByName('member_download_passwd')[0].focus();";
				openDialogAlert("회원정보 다운로드 비밀번호는 영문 대소문자 또는 숫자, 특수문자 중 2가지 이상 조합이어야 합니다.",400,140,'parent',$callback);
				exit;
			} else {
				// 관리자 비밀번호 등록 불가
				if ($_POST['mpasswd']==$_POST['member_download_passwd']) {
					$callback = "parent.document.getElementsByName('member_download_passwd')[0].focus();";
					openDialogAlert("사용할 수 없는 비밀번호 입니다.",400,140,'parent',$callback);
					exit;
				}

				$pwd_str_md5 = md5($_POST['member_download_passwd']);
				$pwd_str_sha256_md5 = hash('sha256',$pwd_str_md5);

				$query = "SELECT * FROM fm_manager WHERE manager_id=? and (mpasswd=? OR mpasswd=?)";
				$query = $this->db->query($query,array($this->managerInfo['manager_id'],$pwd_str_md5,$pwd_str_sha256_md5));
				$data = $query->row_array();
				if ($data) {
					$callback = "parent.document.getElementsByName('member_download_passwd')[0].focus();";
					openDialogAlert("사용할 수 없는 비밀번호 입니다.",400,140,'parent',$callback);
					exit;
				}
			}
		}

		###
		$return_result = $this->id_chk('re_chk');
		if(!$return_result['return']){
			$callback = "parent.document.getElementsByName('manager_id')[0].focus();";
			openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['hp_chk']!='Y'){ 
			$_POST['auth_hp'] = "";
		}else{
			if(trim($_POST['auth_hp']) == ''){
				openDialogAlert("인증 휴대폰 번호를 입력하여 주세요.",400,140,'parent','');
				exit;
			}
			
			$hp_value_chk_cnt = 0;
			if(preg_match("/[a-z]/",$_POST["auth_hp"])) $hp_value_chk_cnt += 1;					
			if(preg_match("/[A-Z]/",$_POST["auth_hp"])) $hp_value_chk_cnt += 1;
			if(preg_match("/[!#$%^&*()?+=\/]/",$_POST["auth_hp"])) $hp_value_chk_cnt += 1;
			
			if($hp_value_chk_cnt > 0){
				openDialogAlert("휴대폰 번호는 숫자로만 입력해주세요.",400,140,'parent','');
				exit;
			}

		}


		// 확인코드 유효성 및 중복확인
		if	(count($_POST['certify_code']) > 0){
			$this->load->model('membermodel');
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$certify_code	= trim($certify_code);
				if	(!$this->membermodel->check_certify_code($certify_code)){
					$callback = "";
					openDialogAlert("잘못된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}

				// 중복확인
				if	($_POST['certify_seq'][$k])	$param['out_seq']	= $_POST['certify_seq'][$k];
				$param['certify_code']	= $certify_code;
				$certify				= $this->membermodel->get_certify_manager($param);
				if	($certify ) {
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				unset($param);
			}//endforeach
		}//endif

		### AUTH
		$this->load->model('membermodel');
		$auth = $this->membermodel->manager_auth_list();

		###
		$params = $_POST;
		$params['mregdate']				= date('Y-m-d H:i:s');
		$params['mpasswd']				= hash('sha256',md5($_POST['mpasswd']));
		$params['passwordUpdateTime']	= date('Y-m-d H:i:s');

		### 회원정보 다운로드 미체크시 다운로드 비밀번호 삭제
		if ($_POST['member_download']=='Y') {
			$params['member_download_passwd'] = hash('sha256',md5($_POST['member_download_passwd']));
		} else {
			$params['member_download_passwd'] = "";
		}

		if($_POST['ip_chk']!='Y') $params['limit_ip'] = "";
		if($_POST['hp_chk']!='Y') $params['auth_hp'] = "";
		$data = filter_keys($params, $this->db->list_fields('fm_manager'));
		$data['manager_auth'] = $auth;
		$result = $this->db->insert('fm_manager', $data);
		$manager_seq = $this->db->insert_id();

		// 확인코드 저장
		if	(count($_POST['certify_code']) > 0){
			//$this->load->model('membermodel');//위에서정의
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;
				// 중복확인
				if	($_POST['certify_seq'][$k])	$param['out_seq']	= $_POST['certify_seq'][$k];
				$param['certify_code']	= $certify_code;
				$certify				= $this->membermodel->get_certify_manager($param);
				if	($certify ) {
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}

				$cparams['provider_seq']	= 1;
				$cparams['manager_id']		= trim($_POST['manager_id']);
				$cparams['manager_name']	= $_POST['manager_name'][$k];
				$cparams['certify_code']	= trim($certify_code);
				$this->membermodel->insert_certify($cparams);
				unset($cparams);
			}
		}
		
		//게시판접근권한
		$this->load->model('boardadmin');
		foreach($_POST['boardid'] as $k => $boardauth) { 
			$this->boardadmin->boardadmin_delete_all($manager_seq,$k);
			$board_act = ($_POST['board_act'][$k]>0)?$_POST['board_act'][$k]:'0';
			$board_view = ($_POST['board_view'][$k]>0)?$_POST['board_view'][$k]:'0';
			$board_view_pw = ($board_view && $_POST['board_view_pw'][$k]>0)?$_POST['board_view_pw'][$k]:'0';
			$badparams['boardid']				= $k;
			$badparams['manager_seq']		= $manager_seq;
			$badparams['board_act']			= $board_act;
			$badparams['board_view']			= ($board_view_pw==2)?$board_view_pw:$board_view;
			$badparams['r_manager_seq']	= $this->managerInfo['manager_seq'];
			$badparams['r_date']					= date('Y-m-d H:i:s'); 
			$this->boardadmin->boardadmin_write($badparams);
			unset($badparams);
		}

		// 슈퍼관리자의 경우
		if($managerData['manager_yn'] == 'Y'){
			config_save('noti_count',
				array(
					'order'=>$_POST['noti_count_priod_order'],
					'board'=>$_POST['noti_count_priod_board']
				)
			);
		}

		$callback = "parent.document.location.href='/admin/setting/manager';";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function manager_modify(){

		$this->load->model('membermodel');

		if( $this->isdemo['isdemo'] ){
			$callback = "parent.document.location.reload();";
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}


		### required
		if(isset($_POST['modify_passwd']) && $_POST['modify_passwd']=='Y'){
			$this->validation->set_rules('mpasswd', '비밀번호','trim|required|max_length[20]|xss_clean');
			$this->validation->set_rules('mpasswd_re', '비밀번호확인','trim|required|min_length[10]|max_length[20]|xss_clean');
			$this->validation->set_rules('manager_password', '현재 관리자 비밀번호','trim|required|max_length[32]|xss_clean');

			if	(preg_match('/[a-zA-Z]/', $_POST['mpasswd']))		$useChar++;
			if	(preg_match('/[0-9]/', $_POST['mpasswd']))			$useChar++;
			if	(preg_match('/[^a-zA-Z0-9]/', $_POST['mpasswd']))	$useChar++;
			if	(preg_match("/[!#$%^&*()?+=\/]/",$_POST['mpasswd']))	$useChar++;
				
			if	($useChar < 2){
				$callback = "parent.document.getElementsByName('mpasswd_re')[0].focus();";
				openDialogAlert("비밀번호는 영문 대소문자 또는 숫자, 특수문자 중 2가지 이상 조합이어야 합니다.",400,140,'parent',$callback);
				exit;
			}
			if($_POST['mpasswd'] != $_POST['mpasswd_re']){
				$callback = "parent.document.getElementsByName('mpasswd_re')[0].focus();";
				openDialogAlert("비밀번호 확인이 다릅니다.",400,140,'parent',$callback);
				exit;
			}
		}

		$this->validation->set_rules('mname', '이름','trim|required|max_length[32]|xss_clean');
		###
		$this->validation->set_rules('memail', '이메일','trim|max_length[64]|valid_email|xss_clean');
		$this->validation->set_rules('mcellphone', '휴대폰번호','trim|max_length[20]|xss_clean');
		$this->validation->set_rules('mphone', '연락처','trim|max_length[20]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if(isset($_POST['ip_chk']) && $_POST['ip_chk']=='Y'){
			$limit_ip = "";
			foreach($_POST["limit_ip1"] as $key=>$value){
				
				for($i=1; $i<=4; $i++){
					$value_chk_cnt = 0;
					if(preg_match("/[a-z]/",$_POST["limit_ip".$i][$key])) $value_chk_cnt += 1;					
					if(preg_match("/[A-Z]/",$_POST["limit_ip".$i][$key])) $value_chk_cnt += 1;
					if(preg_match("/[!#$%^&*()?+=\/]/",$_POST["limit_ip".$i][$key])) $value_chk_cnt += 1;

					if($_POST["limit_ip".$i][$key]){
						if($_POST["limit_ip".$i][$key] < 0 || $_POST["limit_ip".$i][$key] > 255 || $value_chk_cnt > 0){
							openDialogAlert("아이피 대역이 잘못되었습니다.<br>아이피 대역은 0~255 사이의 숫자만 입력해주세요",400,140,'parent',$callback);
							exit;
						}
					}
				}

				$limit_ip .= $_POST["limit_ip1"][$key].".".$_POST["limit_ip2"][$key].".".$_POST["limit_ip3"][$key];
				if(trim($_POST["limit_ip4"][$key])){
					$limit_ip .= ".".$_POST["limit_ip4"][$key]."|";
				}else{
					$limit_ip .= "|";
				}
			}

			$_POST['limit_ip'] = $limit_ip;
		}


		### 관리자 비밀번호 검증
		if(isset($_POST['modify_passwd']) && $_POST['modify_passwd']=='Y'){
			$str_md5 = md5($_POST['manager_password']);
			$str_sha256_md5 = hash('sha256',$str_md5);
			$query = "select * from fm_manager where manager_id=? and (mpasswd=? OR mpasswd=?)";
			$query = $this->db->query($query,array($this->managerInfo['manager_id'],$str_md5,$str_sha256_md5));
			$data = $query->row_array();
			if(!$data){
				$callback = "";
				openDialogAlert("현재 로그인된 관리자 비밀번호가 일치하지 않습니다.",400,140,'parent',$callback);
				exit;
			}
		}


		if($_POST['hp_chk']!='Y'){ 
			$_POST['auth_hp'] = "";
		}else{
			if(trim($_POST['auth_hp']) == ''){
				openDialogAlert("인증 휴대폰 번호를 입력하여 주세요.",400,140,'parent','');
				exit;
			}
			
			$hp_value_chk_cnt = 0;
			if(preg_match("/[a-z]/",$_POST["auth_hp"])) $hp_value_chk_cnt += 1;					
			if(preg_match("/[A-Z]/",$_POST["auth_hp"])) $hp_value_chk_cnt += 1;
			if(preg_match("/[!#$%^&*()?+=\/]/",$_POST["auth_hp"])) $hp_value_chk_cnt += 1;
			
			if($hp_value_chk_cnt > 0){
				openDialogAlert("휴대폰 번호는 숫자로만 입력해주세요.",400,140,'parent','');
				exit;
			}

		}


		### 회원정보 다운로드 비밀번호 체크 leewh 2014-07-10
		if ($_POST['member_download']=='Y') {
			$this->validation->set_rules('member_download_passwd', '회원엑셀다운비밀번호','trim|required|min_length[8]|max_length[20]|xss_clean');
			if	(preg_match('/[a-zA-Z]/', $_POST['member_download_passwd']))		$useMemDownChar++;
			if	(preg_match('/[0-9]/', $_POST['member_download_passwd']))			$useMemDownChar++;
			if	(preg_match('/[^a-zA-Z0-9]/', $_POST['member_download_passwd']))	$useMemDownChar++;
			if	($useMemDownChar < 2){
				$callback = "parent.document.getElementsByName('member_download_passwd')[0].focus();";
				openDialogAlert("회원정보 다운로드 비밀번호는 영문 대소문자 또는 숫자, 특수문자 중 2가지 이상 조합이어야 합니다.",400,140,'parent',$callback);
				exit;
			} else {
				if ($_POST['modify_download_passwd']!='N') {
					$pwd_str_md5 = md5($_POST['member_download_passwd']);
					$pwd_str_sha256_md5 = hash('sha256',$pwd_str_md5);

					// 로그인된 관리자 비밀번호 등록 불가
					$query = "SELECT * FROM fm_manager WHERE manager_id=? and (mpasswd=? OR mpasswd=?)";
					$query = $this->db->query($query,array($this->managerInfo['manager_id'],$pwd_str_md5,$pwd_str_sha256_md5));
					$data = $query->row_array();

					// 다운로드 권한 받는 관리자 비밀번호 등록 불가
					$query = "SELECT * FROM fm_manager WHERE manager_seq=? and (mpasswd=? OR mpasswd=?)";
					$query = $this->db->query($query,array($_POST['manager_seq'],$pwd_str_md5,$pwd_str_sha256_md5));
					$data2 = $query->row_array();

					if ($data || $data2) {
						$callback = "parent.document.getElementsByName('member_download_passwd')[0].focus();";
						openDialogAlert("사용할 수 없는 비밀번호 입니다.",400,140,'parent',$callback);
						exit;
					}
				}
			}
		}

		list($managerData) = get_data("fm_manager",array("manager_seq"=>$_POST['manager_seq']));


		// 확인코드 유효성 및 중복확인
		if	(count($_POST['certify_code']) > 0){
			$this->load->model('membermodel');
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$certify_code	= trim($certify_code);
				if	(!$this->membermodel->check_certify_code($certify_code)){
					$callback = "";
					openDialogAlert("잘못된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}

				// 중복확인
				if	($_POST['certify_seq'][$k])	$param['out_seq']	= $_POST['certify_seq'][$k];
				$param['certify_code']	= $certify_code;
				$certify				= $this->membermodel->get_certify_manager($param);
				if	($certify[0]['seq']){
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				unset($param);
			}
		}


		### AUTH
		$auth = $this->membermodel->manager_auth_list();

		$changes = array();


		###
		$params = $_POST;
		if(isset($_POST['modify_passwd']) && $_POST['modify_passwd']=='Y'){
			$params['mpasswd']				= md5($_POST['mpasswd']);
			$params['passwordUpdateTime']	= date('Y-m-d H:i:s');
			$changes[] = "비밀번호";
		}else{
			unset($params['mpasswd']);
		}

		### 회원정보 다운로드 미체크시 다운로드 비밀번호 삭제
		if($_POST['member_download']=='Y'){
			//$params['member_download_passwd']	= md5($_POST['member_download_passwd']);
			if ($_POST['modify_download_passwd']=='N') {
				$params['member_download_passwd'] = $_POST['member_download_passwd'];
			} else {
				$params['member_download_passwd']	= hash('sha256',md5($_POST['member_download_passwd']));
				$changes[] = "회원정보 다운로드 비밀번호";
			}
		} else {
			$params['member_download_passwd']	= "";
			$changes[] = "회원정보 다운로드 비밀번호 삭제";
		}

		if($managerData['manager_auth']!=$auth) $changes[] = "권한";
		if($managerData['mphone']!=$params['mphone']) $changes[] = "전화번호";
		if($managerData['mname']!=$params['mname']) $changes[] = "관리자명";
		if($managerData['mcellphone']!=$params['mcellphone']) $changes[] = "핸드폰";
		if($managerData['memail']!=$params['memail']) $changes[] = "이메일";
		if($managerData['limit_ip']!=$params['limit_ip']) $changes[] = "접속허용IP";

		$changesStr = $changes ? "(".implode(",",$changes).")" : '';

		if($_POST['ip_chk']!='Y') $params['limit_ip'] = "";
		if($_POST['hp_chk']!='Y') $params['auth_hp'] = "";
		$data = filter_keys($params, $this->db->list_fields('fm_manager'));
		unset($data['manager_id']);
		unset($data['manager_seq']);
		$data['manager_log'] = "<div>".date("Y-m-d H:i:s")." 관리자(".$this->managerInfo['manager_id'].")가 정보{$changesStr}를 수정하였습니다. (".$_SERVER['REMOTE_ADDR'].")</div>".$_POST['manager_log'];
		$data['manager_auth'] = $auth;

		$this->db->where('manager_seq', $params['manager_seq']);
		$result = $this->db->update('fm_manager', $data);

		// 확인코드 저장
		if	(count($_POST['certify_code']) > 0){
			//$this->load->model('membermodel');//위에서 정의
			$this->membermodel->delete_certify(array('provider_seq' => 1,'manager_id' =>$managerData['manager_id']));
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$cparams['provider_seq']	= 1;
				$cparams['manager_id']		= trim($managerData['manager_id']);
				$cparams['manager_name']	= $_POST['manager_name'][$k];
				$cparams['certify_code']	= trim($certify_code);
				$this->membermodel->insert_certify($cparams);
				unset($cparams);
			}
		}
		//게시판접근권한
		$this->load->model('boardadmin');
		foreach($_POST['boardid'] as $k => $boardauth) { 
			$this->boardadmin->boardadmin_delete_all($params['manager_seq'],$k);
			$board_act = ($_POST['board_act'][$k]>0)?$_POST['board_act'][$k]:'0';
			$board_view = ($_POST['board_view'][$k]>0)?$_POST['board_view'][$k]:'0';
			$board_view_pw = ($board_view && $_POST['board_view_pw'][$k]>0)?$_POST['board_view_pw'][$k]:'0';
			$badparams['boardid']				= $k;
			$badparams['manager_seq']		= $params['manager_seq'];
			$badparams['board_act']			= $board_act;
			$badparams['board_view']			= ($board_view_pw==2)?$board_view_pw:$board_view;
			$badparams['r_manager_seq']	= $this->managerInfo['manager_seq'];
			$badparams['up_date']					= date('Y-m-d H:i:s'); 
			$this->boardadmin->boardadmin_write($badparams);
			unset($badparams);
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}



	public function id_chk($chk_key = null){
		$manager_id = $_REQUEST['manager_id'];
		if(!$manager_id) die();
		//$manager_id = strtolower($manager_id);

		###
		$count = get_rows('fm_manager',array('manager_id'=>$manager_id));

		$text = "사용할 수 있는 아이디 입니다.";
		$return = true;
		if($manager_id=='gabia'){
			$text = "사용할 수 없는 아이디 입니다.";
			$return = false;
		}else if(strlen($manager_id)<4 || strlen($manager_id)>16){
			$text = "글자 제한 수를 맞춰주세요.";
			$return = false;
		}else if(preg_match("/[^a-z0-9\-_]/i", $manager_id)) {
			$text = "사용할 수 없는 아이디 입니다.";
			$return = false;
		}else if($count > 0){
			$text = "이미 사용중인 아이디 입니다.";
			$return = false;
		}
		$result = array("return_result" => $text, "manager_id" => $manager_id, "return" => $return);

		if($chk_key){
			return $result;
		}else{
			echo json_encode($result);
		}
	}

	public function manager_delete(){
		$manager_arr = $_GET['manager_seq'];
		$this->load->model('boardadmin');

		foreach($manager_arr as $k){
			$result = $this->db->delete('fm_manager', array('manager_seq' => $k)); 

			//접근권한제거
			$this->boardadmin->boardadmin_delete_manager($k);
		}
		echo $result;
	}


	public function iconUpload(){
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['manager_icon']['tmp_name'])) {
			$config['upload_path']		= $path = ROOTPATH."/data/icon/manager/";
			$file_ext = end(explode('.', $_FILES['manager_icon']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('manager_icon')) {
				@chmod($config['upload_path'].$config['file_name'], 0777);
				$callback = "parent.iconDisplay('{$config[file_name]}');";
				openDialogAlert("등록하였습니다.",400,140,'parent',$callback);
			}else{
				$callback = "";
				openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);
			}
		}else{
			$callback = "";
			openDialogAlert("등록 파일이 없습니다.",400,140,'parent',$callback);
		}
		exit;
	}

	public function goods(){
		//상품코드추가작업
		$this->db->empty_table('fm_goods_code_form');
		foreach($_POST['labelItem'] as $typearr){
			$sort_user=0;
			foreach($typearr as  $codearr){
				$sort_user++;

				if($codearr['type'] == 'goodsaddinfo' ){//goodsaddinfo
					$codearr['codesetting'] = ($codearr['codesetting'] == 1)?1:0;
					$codearr['base_type'] = ($codearr['base_type'] == '1')?'1':'0';
				}else{//goodssuboption goodsoption
					$codearr['codesetting'] = 1;//
					$codearr['base_type'] = '0';
				}
				$codearr['newtypeuse'] = ($codearr['newtypeuse'] == 1 )?1:0;
				$codearr['newtype'] = ($codearr['newtype'])?$codearr['newtype']:'none';

				$data = array(
					'codeform_seq'=> $codearr['codeform_seq'],
					'base_type' => $codearr['base_type'],
					'codesetting' => $codearr['codesetting'],
					'label_type' => $codearr['type'],
					'label_title' => $codearr['name'],
					'label_value' => $codearr['value'],
					'label_default' => $codearr['default'],
					'label_code' => $codearr['code'],
					'label_color' => $codearr['color'],
					'label_zipcode' => $codearr['zipcode'],
					'label_address_type' => $codearr['address_type'],
					'label_address' => $codearr['address'],
					'label_address_street' => $codearr['address_street'],
					'label_addressdetail' => $codearr['addressdetail'],
					'label_biztel' => $codearr['biztel'],
					'label_address_commission' => $codearr['address_commission'],
					'label_newtypeuse' => $codearr['newtypeuse'],
					'label_newtype' => $codearr['newtype'],
					'label_date' => $codearr['date'],
					'label_sdayinput' => $codearr['sdayinput'],
					'label_fdayinput' => $codearr['fdayinput'],
					'label_dayauto_type' => $codearr['dayauto_type'],
					'label_sdayauto' => $codearr['sdayauto'],
					'label_fdayauto' => $codearr['fdayauto'],
					'label_dayauto_day' => $codearr['dayauto_day'],
					'sort_seq' => $sort_user,
					'regist_date' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('fm_goods_code_form', $data);
			}
		}



		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	//동영상설정
	public function video() {
		if($_POST['video_use'] == 'Y') {//동영상사용시 ucc 설정값 필수입니다.
			$this->validation->set_rules('ucc_id','UCC 아이디','trim|required');
			$this->validation->set_rules('ucc_key','UCC 인증키','trim|required');
			$this->validation->set_rules('ucc_domain','UCC 도메인','trim|required');

			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}
		}

		config_save('goods',array(
			'video_use'=>$_POST['video_use'],
			'ucc_id'=>$_POST['ucc_id'],
			'ucc_key'=>$_POST['ucc_key'],
			'ucc_domain'=>'web.mvod.'.str_replace("web.mvod.","",$_POST['ucc_domain'])
			));

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	//상품코드 조합설정
	public function goodssetting(){
		//상품코드추가작업
		$this->db->empty_table('fm_goods_code_form');
		foreach($_POST['SettingItem'] as $typearr){
			$sort_user=0;
			foreach($typearr as  $codearr){
				$sort_user++;
				$codearr['codesetting'] = ($codearr['codesetting'] == 1)?1:0;
				$codearr['newtypeuse'] = ($codearr['newtypeuse'] == 1 )?1:0;
				$codearr['base_type'] = ($codearr['base_type'] == '1' )?'1':'0';
				$codearr['newtype'] = ($codearr['newtype'])?$codearr['newtype']:'none';
				$data = array(
					'codeform_seq'=> $codearr['codeform_seq'],
					'codesetting' => $codearr['codesetting'],
					'base_type' => $codearr['base_type'],
					'label_type' => $codearr['type'],
					'label_title' => $codearr['name'],
					'label_value' => $codearr['value'],
					'label_default' => $codearr['default'],
					'label_code' => $codearr['code'],
					'label_color' => $codearr['color'],
					'label_zipcode' => $codearr['zipcode'],
					'label_address_type' => $codearr['address_type'],
					'label_address' => $codearr['address'],
					'label_address_street' => $codearr['address_street'],
					'label_addressdetail' => $codearr['addressdetail'],
					'label_address_commission' => $codearr['address_commission'],
					'label_biztel' => $codearr['biztel'],
					'label_newtypeuse' => $codearr['newtypeuse'],
					'label_newtype' => $codearr['newtype'],
					'label_date' => $codearr['date'],
					'label_sdayinput' => $codearr['sdayinput'],
					'label_fdayinput' => $codearr['fdayinput'],
					'label_dayauto_type' => $codearr['dayauto_type'],
					'label_sdayauto' => $codearr['sdayauto'],
					'label_fdayauto' => $codearr['fdayauto'],
					'label_dayauto_day' => $codearr['dayauto_day'],
					'sort_seq' => $sort_user,
					'regist_date' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('fm_goods_code_form', $data);
			}
		}
		$callback = "parent.document.location.reload();";
		openDialogAlert("상품코드 자동생성 규칙 세팅이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function banner_upload_file(){
		$error = array('status' => 0,'msg' => '업로드 실패하였습니다.','desc' => '업로드 실패');
		$folder = "data/tmp/";
		$arrDiv = config_load('goodsImageSize');
		$newFile = date('dHis');
		$idx = $_POST['idx'];
		$filename = $newFile.$div;
		$this->load->model('goodsmodel');
		$result = $this->goodsmodel->goods_temp_image_upload($filename,$folder);
		if(!$result['status']){
			echo "[".json_encode($error)."]";
			exit;
		}

		$source = $result['fileInfo']['full_path'];
		$target = $result['fileInfo']['full_path'];
		/*
		$resizeResult = $this->goodsmodel->goods_temp_image_resize($source,$target,$arrDiv[$div]['width'],$arrDiv[$div]['height']);
		if(!$resizeResult['status']){
			echo "[".json_encode($error)."]";
			exit;
		}
		*/

		$result = array('status' => 1,'newFile' => "/".$folder.$newFile,'idx' => $idx,'ext' => $result['fileInfo']['file_ext']);
		echo "[".json_encode($result)."]";
	}

	public function watermark_setting()
	{
		$this->load->model('watermarkmodel');
		$this->watermarkmodel->watermark_setting();

	}

	function member_sale_write(){

		$service_code = $this->config_system['service']['code'];
		if($service_code == "P_ADVA" || $service_code == "P_EXPA"){
			$default_member_sale_cnt = 5;
		}else if($service_code == "P_FAMM" || $service_code == "P_PREM" || $service_code == "P_STOR"){
			$default_member_sale_cnt = 3;
		}else{
			$default_member_sale_cnt = 1;
		}

		$this->config_system['service']['max_member_sale_cnt'] += $default_member_sale_cnt;

		$sale_seq = $_POST["sale_seq"];

		if($sale_seq == ""){
			$qry = "select count(*) as cnt from fm_member_group_sale";
			$query = $this->db->query($qry);
			$saleData = $query -> row_array();

			if($saleData['cnt'] >= $this->config_system['service']['max_member_sale_cnt']){
				$callback = "parent.member_sale_payment();";
				openDialogAlert("등급별 구매혜택 세트 설정 가능 개수를 초과하였습니다.",400,140,'parent',$callback);
				exit;
			}
		}

		$this->load->model('membermodel');
		$list = $this->membermodel->member_sale_group_list();


		$this->validation->set_rules('sale_title', '할인율 이름','trim|required|max_length[100]|xss_clean');

		foreach($list as $validationdata){
			if($_POST["sale_use"][$validationdata["group_seq"]] == "Y"){
				if($_POST["sale_limit_price"][$validationdata["group_seq"]] == '0') $_POST["sale_limit_price"][$validationdata["group_seq"]] = '';
				$this->validation->set_rules('sale_limit_price['.$validationdata["group_seq"].']', '구매금액 조건','trim|required|max_length[100]|xss_clean');
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		foreach($list as $validationdata){
			$this->validation->set_rules('sale_use['.$validationdata["group_seq"].']', '추가할인 조건','trim|required|max_length[100]|xss_clean');

			//할인 미입력시 0으로 셋팅
			if($_POST["sale_use"][$validationdata["group_seq"]] == "") $_POST["sale_use"][$validationdata["group_seq"]] = "0";
			$this->validation->set_rules('sale_price['.$validationdata["group_seq"].']', '추가할인 할인','trim|required|max_length[100]|xss_clean');

			//추가옵션 미입력시 0으로 셋팅
			if($_POST["sale_option_price"][$validationdata["group_seq"]] == "") $_POST["sale_option_price"][$validationdata["group_seq"]] = "0";
			$this->validation->set_rules('sale_option_price['.$validationdata["group_seq"].']', '추가할인 추가옵션','trim|required|max_length[100]|xss_clean');

			$this->validation->set_rules('point_use['.$validationdata["group_seq"].']', '추가적립 조건','trim|required|max_length[100]|xss_clean');

			//적립금 미입력시 0으로 셋팅
			if($_POST["point_price"][$validationdata["group_seq"]] == "") $_POST["point_price"][$validationdata["group_seq"]] = "0";
			$this->validation->set_rules('point_price['.$validationdata["group_seq"].']', '추가적립 적립금','trim|required|max_length[100]|xss_clean');

			//포인트 미입력시 0으로 셋팅
			if($_POST["reserve_price"][$validationdata["group_seq"]] == "") $_POST["reserve_price"][$validationdata["group_seq"]] = "0";
			$this->validation->set_rules('reserve_price['.$validationdata["group_seq"].']', '추가적립 포인트','trim|required|max_length[100]|xss_clean');

		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}




		if($sale_seq == ""){
			$insert_params['sale_title'] = $_POST["sale_title"];
			$insert_params['regist_date'] = date("Y-m-d H:i:s");
			$insert_params['update_date'] = date("Y-m-d H:i:s");
			$insert_params['defualt_yn'] = $_POST['defualt_yn'] ? 'y' : 'n';

			$result = $this->db->insert('fm_member_group_sale', $insert_params);
			$sale_seq = $this->db->insert_id();

			$mode = "insert";
		}else{

			$insert_params['sale_title'] = $_POST["sale_title"];
			$insert_params['update_date'] = date("Y-m-d H:i:s");
			$insert_params['defualt_yn'] = $_POST['defualt_yn'] ? 'y' : 'n';

			$this->db->where('sale_seq', $sale_seq);
			$result = $this->db->update('fm_member_group_sale', $insert_params);
			$result = $this->db->delete('fm_member_group_sale_detail', array('sale_seq' => $sale_seq));
			$mode = "modify";

		}

		if($_POST['defualt_yn'] == "y"){
			$sql = "update fm_member_group_sale set defualt_yn = 'n' where sale_seq <> '".$sale_seq."'";
			$result = $this->db->query($sql);
		}


		foreach($list as $group){

			$data["sale_seq"]				= $sale_seq;
			$data["group_seq"]				= $group["group_seq"];
			$data["sale_use"]				= $_POST["sale_use"][$group["group_seq"]];
			$data["sale_limit_price"]		= $_POST["sale_limit_price"][$group["group_seq"]];
			$data["sale_price"]				= $_POST["sale_price"][$group["group_seq"]];
			$data["sale_price_type"]		= $_POST["sale_price_type"][$group["group_seq"]];
			$data["sale_option_price"] 		= $_POST["sale_option_price"][$group["group_seq"]];
			$data["sale_option_price_type"]	= $_POST["sale_option_price_type"][$group["group_seq"]];
			$data["point_use"]				= $_POST["point_use"][$group["group_seq"]];
			$data["point_limit_price"]		= $_POST["point_limit_price"][$group["group_seq"]];
			$data["point_price"]			= $_POST["point_price"][$group["group_seq"]];
			$data["point_price_type"]		= $_POST["point_price_type"][$group["group_seq"]];
			$data["reserve_price"]			= $_POST["reserve_price"][$group["group_seq"]];
			$data["reserve_price_type"]		= $_POST["reserve_price_type"][$group["group_seq"]];
			$data["reserve_select"]			= $_POST["reserve_select"][$group["group_seq"]];
			$data["reserve_year"]			= $_POST["reserve_year"][$group["group_seq"]];
			$data["reserve_direct"]			= $_POST["reserve_direct"][$group["group_seq"]];
			$data["point_select"]			= $_POST["point_select"][$group["group_seq"]];
			$data["point_year"]				= $_POST["point_year"][$group["group_seq"]];
			$data["point_direct"]			= $_POST["point_direct"][$group["group_seq"]];

			$result = $this->db->insert('fm_member_group_sale_detail', $data);


		}

		$result = $this->db->delete('fm_member_group_issuegoods', array('sale_seq' => $sale_seq));
		$result = $this->db->delete('fm_member_group_issuecategory', array('sale_seq' => $sale_seq));

		### SALE
		$group_seq = (int) $group_seq;
		for($i=0;$i<count($_POST['issueGoods']);$i++){
			if($_POST['issueGoods'][$i])
			$result = $this->db->insert('fm_member_group_issuegoods', array('group_seq'=>$group_seq, 'sale_seq'=>$sale_seq,'goods_seq'=>$_POST['issueGoods'][$i],'type'=>'sale'));
		}
		for($i=0;$i<count($_POST['issueCategoryCode']);$i++){
			$result = $this->db->insert('fm_member_group_issuecategory', array('group_seq'=>$group_seq,'sale_seq'=>$sale_seq,'category_code'=>$_POST['issueCategoryCode'][$i],'type'=>'sale'));
		}

		### EMONEY
		for($i=0;$i<count($_POST['exceptIssueGoods']);$i++){
			if($_POST['exceptIssueGoods'][$i])
			$result = $this->db->insert('fm_member_group_issuegoods', array('group_seq'=>$group_seq,'sale_seq'=>$sale_seq,'goods_seq'=>$_POST['exceptIssueGoods'][$i],'type'=>'emoney'));
		}
		for($i=0;$i<count($_POST['exceptIssueCategoryCode']);$i++){
			$result = $this->db->insert('fm_member_group_issuecategory', array('group_seq'=>$group_seq,'sale_seq'=>$sale_seq,'category_code'=>$_POST['exceptIssueCategoryCode'][$i],'type'=>'emoney'));
		}

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		$callback = "parent.location.replace('/admin/setting/member?gb=member_sale');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	function member_sale_delete(){

		$sale_seq = $_GET['sale_seq'];
		$change_seq = $_GET['change_seq'];

		$result = $this->db->delete('fm_member_group_sale_detail', array('sale_seq' => $sale_seq));
		$result = $this->db->delete('fm_member_group_sale', array('sale_seq' => $sale_seq));

		if($result){
			$sql = "update fm_goods set sale_seq = '".$change_seq."' where sale_seq = '".$sale_seq."'";
			$result = $this->db->query($sql);
		}

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		if($result){
			$callback = "parent.location.replace('/admin/setting/member?gb=member_sale');";
			openDialogAlert("삭제되었습니다.",400,140,'parent',$callback);
		}else{
			$callback = "parent.location.replace('/admin/setting/member?gb=member_sale');";
			openDialogAlert("삭제 처리중 에러가 발생하였습니다.",400,140,'parent',$callback);
		}
	}

	function search(){

		$this->load->model('searchwordmodel');

		// 필수값 체크
		/*
		foreach($_POST['page'] as $page_key => $page ){
			foreach($_POST['keyword'][$page_key] as $keyword_key => $keyword){
				if( !$_POST['search_result_link'][$page_key][$keyword_key] ){
					if( $_POST['search_result'][$page_key][$keyword_key] == 'search' ){
						openDialogAlert("검색어를 입력해주세요.",400,140,'parent','');
					}else{
						openDialogAlert("링크주소를 입력해주세요.",400,140,'parent','');
					}
					exit;
				}
			}
		}
		*/

		$this->searchwordmodel->truncate_word();

		foreach($_POST['page'] as $page_key => $page ){
			foreach($_POST['keyword'][$page_key] as $keyword_key => $keyword){
				if( $keyword ){
					$this->searchwordmodel->insert_word($_POST['page_yn'][$page_key],
						$page_key,
						$keyword,
						$_POST['search_result'][$page_key][$keyword_key],
						$_POST['search_result_link'][$page_key][$keyword_key],
						$_POST['search_result_target'][$page_key][$keyword_key]
					);
				}
			}
		}

		config_save('search',array(
			'auto_search'=>$_POST['auto_search'],
			'auto_search_limit_day'=>$_POST['auto_search_limit_day'],
			'auto_search_recomm_limit_day'=>$_POST['auto_search_recomm_limit_day'],
			'popular_search'=>$_POST['popular_search'],
			'popular_search_limit_day'=>$_POST['popular_search_limit_day'],
			'popular_search_recomm_limit_day'=>$_POST['popular_search_recomm_limit_day']
		));

		// 도로명 주소 설정 저장
		$street_ = false;
		$arr_param_street = array('zipcode_street','new_zipcode_lot_number','old_zipcode_lot_number');
		foreach( $arr_param_street as $param_street) if( $_POST[$param_street] ) $street_ = true;

		if( $street_ ){
			foreach( $arr_param_street as $param_street) $arr_cfg_zipcode[$param_street] = $_POST[$param_street];
			config_save('zipcode',$arr_cfg_zipcode);
		}else{
			openDialogAlert("주소 검색창 설정 중 하나는 반드시 사용해야합니다.",400,140,'parent','');
			exit;
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);

	}

	## 택배업무자동화서비스 세팅
	function invoice_setting(){

		$this->config_invoice = config_load('invoice');
		$this->config_shipping = config_load('shipping');

		if($_POST['invoice_notuse']){
			config_save("system",array('invoice_use'=>0));
			config_save("system",array('invoice_vendor'=>array()));

			foreach($this->config_invoice as $invoice_vendor=>$row){
				config_save('invoice',array($invoice_vendor=>null));
			}
		}else{

			if(!$this->config_shipping['returnAddress']){
				openDialogAlert("반송 주소를 먼저 세팅해주세요.",400,140,'parent');
				exit;
			}

			$this->validation->set_rules('branch_name', '계약 대리점명','required|trim|max_length[20]|xss_clean');
			$this->validation->set_rules('auth_code[]', '신용코드','required|trim|max_length[6]|numeric|xss_clean');

			if($this->validation->exec()===false){
				$err = $this->validation->error_array;
				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
				openDialogAlert($err['value'],400,140,'parent',$callback);
				exit;
			}

			$arr_invoice_vendor = array();
			foreach($_POST['auth_code'] as $invoice_vendor=>$auto_code){
				if($auto_code){
					$arr_invoice_vendor[] = $invoice_vendor;
				}
			}

			if(!$arr_invoice_vendor){
				openDialogAlert("신용코드 인증이 필요합니다.",400,140,'parent');
				exit;
			}

			foreach($_POST['auth_code'] as $invoice_vendor=>$auto_code){

				$params = array();
				$params['use'] = $auto_code?1:0;
				$params['auth_code'] = $auto_code;

				if($invoice_vendor=='hlc'){
					$params['branch_name'] = $_POST['branch_name'];
					$params['print_type'] = $_POST['print_type'];
				}

				config_save('invoice',array($invoice_vendor=>$params));
			}

			config_save("system",array('invoice_vendor'=>$arr_invoice_vendor));
			config_save("system",array('invoice_use'=>1));
			if(!$this->config_system['invoice_use']){
				config_save("system",array('invoice_use_date'=>date('Y-m-d H:i:s')));
			}
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	## 현대택배 신용코드 인증
	function hlc_auth(){
		$this->load->model('invoiceapimodel');
		$result = $this->invoiceapimodel->hlc_auth($_POST['auth_code']);
		echo json_encode($result);
	}

	function modify_shipping_address(){
		$this->validation->set_rules('senderEmail', '이메일','trim|max_length[50]|valid_email|xss_clean');
		if($_POST['senderZipcode'][0] || $_POST['senderZipcode'][1]){
		$this->validation->set_rules('senderZipcode[]', '우편번호','trim|max_length[3]|numeric|xss_clean');
		}
		$this->validation->set_rules('senderAddress', '주소','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('senderAddressDetail', '상세 주소','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('returnEmail', '이메일','trim|max_length[50]|valid_email|xss_clean');
		if($_POST['returnZipcode'][0] || $_POST['returnZipcode'][1]){
		$this->validation->set_rules('returnZipcode[]', '우편번호','trim|max_length[3]|numeric|xss_clean');
		}
		$this->validation->set_rules('returnAddress', '주소','trim|max_length[100]|xss_clean');
		$this->validation->set_rules('returnAddressDetail', '상세 주소','trim|max_length[100]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		config_save('shipping',array('senderZipcode'=>$_POST['senderZipcode']));
		config_save('shipping',array('senderAddress_type'=>$_POST['senderAddress_type']));
		config_save('shipping',array('senderAddress'=>$_POST['senderAddress']));
		config_save('shipping',array('senderAddress_street'=>$_POST['senderAddress_street']));
		config_save('shipping',array('senderAddressDetail'=>$_POST['senderAddressDetail']));

		config_save('shipping',array('returnZipcode'=>$_POST['returnZipcode']));
		config_save('shipping',array('returnAddress_type'=>$_POST['returnAddress_type']));
		config_save('shipping',array('returnAddress'=>$_POST['returnAddress']));
		config_save('shipping',array('returnAddress_street'=>$_POST['returnAddress_street']));
		config_save('shipping',array('returnAddressDetail'=>$_POST['returnAddressDetail']));

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}


	//상품코드 > 특수옵션의 자동기간 오늘일자로 미리보기
	public function goods_dayauto_setting() {
		$this->load->helper('goods');
		$deposit_date		= ($_GET['deposit_date'])?$_GET['deposit_date']:date("Y-m-d");
		$sdayauto			= ($_GET['sdayauto'])?$_GET['sdayauto']:0;
		$fdayauto			= ($_GET['fdayauto'])?$_GET['fdayauto']:0;
		$dayauto_type	= ($_GET['dayauto_type'])?$_GET['dayauto_type']:0;
		$dayauto_day		= ($_GET['dayauto_day'])?$_GET['dayauto_day']:0;
		//debug_var( $deposit_date .', '. $sdayauto .', '. $fdayauto .', '. $dayauto_type .', '. $dayauto_day );
		$resulthtml = goods_dayauto_setting_day( $deposit_date, $sdayauto, $fdayauto, $dayauto_type, $dayauto_day );
		echo json_encode($resulthtml);
	}

	function shorturl(){
		$shorturl_test = ($_POST['shorturl_test'])?$_POST['shorturl_test']:$_GET['shorturl_test'];
		$resulthtml = get_shortURL($shorturl_test);
		echo json_encode(array('resulturl'=>$resulthtml));
	}


	function setting_editor(){
		config_save('goods_contents_editor',array('type'=>$_POST['editor_type']));
		$callback = "parent.closeDialog('setting_editor_popup');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}



	public function auto_logout(){

		config_save('autoLogout',array('auto_logout'=>$_POST['auto_logout']));
		config_save('autoLogout',array('until_time'=>$_POST['until_time']));

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);


	}

	// 디스플레이 캐시 기능 on/off
	public function set_display_status(){
		$status	= trim($_POST['status']);
		if	(!$status)	$status	= 'OFF';

		if	($status == 'ON'){
			// 해당 디스플레이 캐싱 파일 삭제
			$this->load->model('goodsdisplay');
			$this->goodsdisplay->delete_display_cach();
		}

		config_save('system',array('display_cach'=>$status));
	}
}

/* End of file setting_process.php */
/* Location: ./app/controllers/admin/setting_process.php */