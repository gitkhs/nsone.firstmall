<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class marketing_process extends admin_base {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('admin/setting');
	}
	### 가입
	public function marketplace()
	{	
		
		/* 관리자 권한 체크 : 시작 */		
		$auth = $this->authmodel->manager_limit_act('marketplace_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		if( in_array($_POST['navercheckout_use'],array('test','y')) && $_POST['naver_wcs_use']!='y' )
		{			
			openDialogAlert("공통인증은 필수 입니다.",400,140,'parent',$callback);
			exit;
		}
		
		### 네이버 공통인증 설정 저장
		config_save('basic',array('naver_wcs_use'=>$_POST['naver_wcs_use']));

		$config_param['accountId'] = $_POST['naver_wcs_accountid'];
		$config_param['checkoutWhitelist'] = array();
		foreach($_POST['checkoutWhitelist'] as $v){
			if(trim($v)){
				$config_param['checkoutWhitelist'][] = $v;
			}
		}
		config_save('naver_wcs',$config_param);

		### 네이버 체크아웃 설정 저장
		$config_param = array();
		$config_param['use'] 	= $_POST['navercheckout_use'];
		$config_param['shop_id'] 	= $_POST['navercheckout_shop_id'];
		$config_param['certi_key'] 	= $_POST['navercheckout_certi_key'];
		$config_param['button_key'] 	= $_POST['navercheckout_button_key'];
		$config_param['except_category_code'] = array();
		foreach($_POST['except_category_code'] as $value){
			$config_param['except_category_code'][] = array('category_code'=>$value);
		}
		$config_param['except_goods'] = array();
		foreach($_POST['except_goods'] as $value){
			$config_param['except_goods'][] = array('goods_seq'=>$value);
		}
		if( $config_param['use'] == 'y' || $config_param['use'] == 'test'){
			if( !$config_param['shop_id'] ){
				openDialogAlert("상점 ID 필수 입니다.",400,140,'parent',$callback);
				exit;
			}
			if( !$config_param['certi_key'] ){
				openDialogAlert("상점 인증키 필수 입니다.",400,140,'parent',$callback);
				exit;
			}
			if( !$config_param['button_key'] ){
				openDialogAlert("버튼키는 필수 입니다.",400,140,'parent',$callback);
				exit;
			}
		}
		

		config_save('navercheckout',$config_param);

		$config_param['naver_mileage_yn'] = $_POST['naver_mileage_yn'];
		$config_param['naver_mileage_api_id'] 	= $_POST['naver_mileage_api_id'];
		$config_param['naver_mileage_secret']	= $_POST['naver_mileage_secret'];
		if( $_POST['naver_mileage_test']) $config_param['naver_mileage_test']	= $_POST['naver_mileage_test'];

		if( $config_param['naver_mileage_yn'] == 'y' ){
			if( !$_POST['naver_mileage_api_id'] || !$_POST['naver_mileage_secret'] ){
				openDialogAlert("외부인증아이디와 인증키는 필수 입니다.",400,140,'parent',$callback);
				exit;
			}
		}

		### 설정저장
		config_save('naver_mileage',$config_param);

		### 전달이미지 설정 lwh 2014-02-28
		$config_image['daumImage']	= $_POST['daumImage'];
		$config_image['naverImage']	= $_POST['naverImage'];
		config_save('marketing_image',$config_image);

		### 입점마케팅 전달 데이터 통합 설정 - 입점마케팅 상품명,카드무이자할부 leewh 2015-01-29
		$config_feed = array();
		$config_feed['goods_name'] = ($_POST['feed_goods_name'])? $_POST['feed_goods_name'] : '';
		$config_feed['cfg_card_free'] = ($_POST['cfg_card_free'])? $_POST['cfg_card_free'] : '';
		config_save('marketing_feed',$config_feed);

		### 입점마케팅 상품 추가할인
		$config_sale = array();
		$config_sale['member'] = ($_POST['marketing_sale_member']=="Y") ? "Y" : "N";
		$config_sale['referer'] = ($_POST['marketing_sale_referer']=="Y") ? "Y" : "N";
		$config_sale['coupon'] = ($_POST['marketing_sale_coupon']=="Y") ? "Y" : "N";
		$config_sale['mobile'] = ($_POST['marketing_sale_mobile']=="Y") ? "Y" : "N";
		$config_sale['member_sale_type'] = ($_POST['member_sale_type']=="1") ? "1" : "0";
		config_save('marketing_sale',$config_sale);

		###
		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}


	public function login()
	{

		$this->load->model('marketingAdminModel');

		$vcode  = $_POST['vcode'];
		$id  = $_POST['id'];
		$pw  = $_POST['pw'];

		$out = $this->marketingAdminModel->get_id_pw();
		$md5_str = md5($id.$pw);
		if($out != $md5_str){
			openDialogAlert("로그인 실패",400,140,'parent');
			exit;
		}

		$this->marketingAdminModel->set_session_login($vcode);

		if($vcode == 'navercheckout'){
			pageRedirect("/main/index", "", "parent");
		}else{
			pageRedirect("/admin/marketing/marketplace_url", "", "parent");
		}
	}


	### 다음쇼핑하우 입점신청
	public function marketplace_daumshopping_process(){

		$this->load->helper('readurl');

		// 로고 업로드
		list($daumshopping_logo1,$daumshopping_logo2) = $this->setting->upload_daumshopping_logo();
		config_save('system',array('daumshopping_logo1'=>$daumshopping_logo1));
		config_save('system',array('daumshopping_logo2'=>$daumshopping_logo2));

		$params = $_POST;
		$params['regip']					= $_SERVER['REMOTE_ADDR'];
		$params['url']						= "http://".$this->config_system['domain'];
		$params['shopSno']					= $this->config_system['shopSno'];
		$params['logoimg1']	= "http://".$this->config_system['domain'].$daumshopping_logo1;
		$params['logoimg2']	= "http://".$this->config_system['domain'].$daumshopping_logo2;

		$result = readurl("http://interface.firstmall.kr/firstmall_plus/request.php?cmd=daumshopping_apply_process",$params);
		$result = unserialize($result);

		if($result['code']=='succ'){
			$callback = "parent.document.location.replace('/admin/marketing/marketplace_url')";
			openDialogAlert($result['msg'],400,140,'parent',$callback);
			exit;
		}else{
			if($result['msg']){
				openDialogAlert($result['msg'],400,140,'parent',$result['callbackScript']);
				exit;
			}else{
				openDialogAlert("알수없는 통신 장애입니다.<br />가비아 퍼스트몰 고객센터로 문의해주세요",400,150,'parent',$result['callbackScript']);
				exit;
			}
		}
	
	}
}