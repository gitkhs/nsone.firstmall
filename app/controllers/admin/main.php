<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class main extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->cach_file_path	= $_SERVER['DOCUMENT_ROOT'] . '/data/cach/';
		$this->cach_file_url	= '../../data/cach/';
		$this->cach_file_name	= 'admin_main_index.html';
		$this->cach_stat_file	= 'admin_main_stats.html';
	}

	public function main_index()
	{
		redirect("/admin/main/index");
	}

	// 메인화면
	public function index($type = '')
	{
		// $this->chk_caching();

		$this->load->model('usedmodel');
		$this->template->assign("config_basic",$this->config_basic);

		/* 카운터 바 */
		$this->get_main_count_bar('index');

		/* 판매현황 */
		$this->_print_main_order_summary('index');

		/* 상품현황 : 2014-10-21 lwh */
		$this->_print_main_goods_summary('index');

		/* 1:1문의 leewh 2014-07-30 */
		$this->_print_main_qna_summary('index');

		if(SERVICE_CODE == 'P_STOR'){
			/* 예약문의 leewh 2014-07-30 */
			$this->_print_main_reserve_upgrade_area('index');
		}else{
			/* 상품문의 leewh 2014-07-30 */
			$this->_print_main_goodsqna_summary('index');
		}

		/* MY 서비스 바 */
		$this->get_main_myservice_bar('index');

		/* 서비스기간 남은일수 */
		$remainExpireDay = round((strtotime($this->config_system['service']['expire_date'])-strtotime(date('Y-m-d')))/(3600*24));

		/* 최대 용량 */
		$maxDiskSpace = $this->usedmodel->get_disk_space_format($this->config_system['service']['disk_space']);

		/* 사용 용량 */
		$usedDiskSpace = $this->usedmodel->get_disk_space_format($this->config_system['usedDiskSpace']);
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_limit_check();

		/* 디스크 사용율 */
		$usedSpacePercent = $this->usedmodel->get_used_space_percent();

		/* 트래픽제한 */
		$trafficLimit = $this->config_system['service']['traffic'];

		/* 공지사항 :: ajax 호출로 인한 주석 :: 2015-01-09 lwh*/
		// $this->_print_main_news_notice_area('index');

		/* 업그레이드 :: ajax 호출로 인한 주석 :: 2015-01-09 lwh*/
		// $this->_print_main_news_upgrade_area('index');

		/* 관리자메모*/
		$this->_define_admin_memo_area();

		/* 통계 */
		$caching_time	= $this->chk_stats_caching();

		/* 출고예약량 */
		//config_save('reservation',array('update_date'=>''));
		$cfg_reservation = config_load('reservation');

		/* 구매확정기능 무료몰 제한 */
		$cfg_order = config_load('order');
		if($this->config_system['service']['code']=='P_FREE' && $cfg_order['buy_confirm_use']){
			config_save('order',array('buy_confirm_use'=>'0'));
		}


		/* 게시판적립금지급 */
		//$this->_define_board_emoney_form();  //{#emoneyform}

		/* 회원 주문건수/주문금액 */
		//$cfg_mbupdateorder = config_load('member_update_order');

		/* 상품의 상품후기건수 */
		//$cfg_good_review_count = config_load('good_review_count');

		/* 관리자 메뉴얼 */
		//$this->_define_main_manual_banner_area();



		$this->template->assign(array(
			'remainExpireDay'		=> $remainExpireDay,
			'maxDiskSpace'			=> $maxDiskSpace,
			'usedDiskSpace'			=> $usedDiskSpace,
			'usedSpacePercent'		=> $usedSpacePercent,
			'trafficLimit'			=> $trafficLimit,
			'cfg_reservation'		=> $cfg_reservation
		));

		/* 트래픽 :: 속도개선으로 인해 호출 안함 - 메인에서 ajax 호출 :: 2015-01-09 lwh*/
		// $traffic_arr = $this->get_traffic_data($this->config_system['subDomain']);
		// $this->template->assign(array('traffic'=>$traffic_arr['u']));

		/* Gabia Pannel */
		$pannel = config_load('pannel');
		if( $pannel['update_date'] < date('Y-m-d') ){
			$this->load->helper('admin');
			$all_banner					= getGabiaPannel('allbanner');
			unset($all_banner[0]);
			config_save('pannel', array('update_date'=>date('Y-m-d')));
			config_save('pannel', $all_banner);
		}else{
			$all_banner = $pannel;
		}

		$main_newservice_banner		= $all_banner['main_newservice_banner'];
		$main_rolling_banner		= $all_banner['main_rolling_banner'];
		$main_bottom_left_banner	= $all_banner['main_bottom_left_banner'];
		$main_bottom_middle_banner	= $all_banner['main_bottom_middle_banner'];
		$main_bottom_right_banner	= $all_banner['main_bottom_right_banner'];
		$main_bottom_big_banner		= $all_banner['main_bottom_big_banner'];
		$main_bottom_manual1_banner	= $all_banner['main_bottom_manual1_banner'];
		$main_bottom_manual2_banner	= $all_banner['main_bottom_manual2_banner'];

		$this->template->assign(array(
			'main_newservice_banner'		=> $main_newservice_banner,
			'main_rolling_banner'			=> $main_rolling_banner,
			'main_bottom_left_banner'		=> $main_bottom_left_banner,
			'main_bottom_middle_banner'		=> $main_bottom_middle_banner,
			'main_bottom_right_banner'		=> $main_bottom_right_banner,
			'main_bottom_big_banner'		=> $main_bottom_big_banner,
			'main_bottom_manual1_banner'	=> $main_bottom_manual1_banner,
			'main_bottom_manual2_banner'	=> $main_bottom_manual2_banner,
		));

		/* 비밀번호 체크 */
		$this->load->model('managermodel');
		$data_manager = $this->managermodel->get_manager($this->managerInfo['manager_seq']);
		$is_change_pass = false;
		if( $data_manager['passwordUpdateTime'] == '0000-00-00 00:00:00' ) $data_manager['passwordUpdateTime'] = $data_manager['mregdate'];
		if($data_manager['passwordUpdateTime'] < date('Y-m-d H:i:s',time()-90*24*3600)){
			$is_change_pass = true;
		}
		$this->template->assign('is_change_pass',$is_change_pass);;


		$this->admin_menu();
		$this->tempate_modules();

		$file_path	= $this->template_path();
		if	($type == 'caching')
			$file_path	= str_replace('main_caching', 'index', $file_path);

		$this->template->assign('last_reload',$caching_time);
		$this->template->assign('traffic', $traffic_arr['u']);

		$this->template->assign(array('cfg_reservation',$cfg_reservation));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 2013-10-25 lwh 트래픽 데이터 호출
	public function get_traffic_data($domain){
		if	($this->config_system['service']['hosting_code'] == 'F_SH_X'){
			$decode_arr['u']['limits']	= '0KB';
			$decode_arr['u']['usages']	= '0KB';
			$decode_arr['u']['state']	= '0';
		}else{
			$this->load->helper('readurl');
			$requestUrl = "http://traffic.firstmall.kr/traffic.php";
			$json_traffic = readurl($requestUrl,array('domain' => $domain));
			$decode_arr = json_decode($json_traffic,true);
		}

		return $decode_arr;
	}
	// 2013-10-25 lwh 트래픽 재 데이터 호출
	public function re_traffic_data(){
		if	($this->config_system['service']['hosting_code'] == 'F_SH_X'){
			$decode_arr['u']['limits']	= '0KB';
			$decode_arr['u']['usages']	= '0KB';
			$decode_arr['u']['state']	= '0';
		}else{
			$this->load->helper('readurl');
			$requestUrl = "http://traffic.firstmall.kr/traffic.php";
			$json_traffic = readurl($requestUrl,array('domain' => $_GET['domain']));
			$decode_arr = json_decode($json_traffic,true);
		}

		echo implode($decode_arr['u'],"|");
	}

	// 메인 컨텐츠 개별 호출
	public function get_main_contents_pannel(){
		switch($_GET["area"]){
			case "order":
				$this->_print_main_order_summary();
			break;
			case "qna":
				$this->_print_main_qna_summary();
			break;
			case "goodsqna":
				$this->_print_main_goodsqna_summary();
			break;
			case "notice":
				$this->_print_main_news_notice_area();
			break;
			case "upgrade":
				$this->_print_main_news_upgrade_area();
			break;
			case "reserve":
				$this->_print_main_reserve_upgrade_area();
			break;
		}
	}

	public function get_main_myservice_bar($type=""){

		//############ 서비스 설정 ##########//
		/*
			* servicetxt
			0 = 신청이미지 <img src='/admin/skin/default/images/main/btn_s_app.gif' />
			1 = 사용중
			2 = 사용(무료)
			3 = 사용(유료)
			4 = 발행안함
			5 = CSS 사용중
			6 = 미사용
			7 = 설정

			그외 = servicetxt 그대로 표현
		*/

		/* 통합결제 */
		$addService['pg'] = $this->config_system['pgCompany'];
		if($addService['pg']){
			$cssblock = 0;
			$serviceuse = $addService['pg'];
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[0]['num'] = 1;
		$serviceHtml[0]['name'] = "통합결제";
		$serviceHtml[0]['link'] = "/admin/setting/pg";
		$serviceHtml[0]['isblock'] = $cssblock;
		$serviceHtml[0]['servicetxt'] = $serviceuse;
		$serviceHtml[0]['expire'] = "";
		$serviceHtml[0]['overtxt'] = '신용카드<br/>계좌이체<br/>가상계좌';

		$serviceuse = "<p class='servicetxt_sms'></p>";
		$serviceHtml[1]['num'] = 2;
		$serviceHtml[1]['name'] = "문자";
		$serviceHtml[1]['link'] = "/admin/member/sms_charge";
		$serviceHtml[1]['isblock'] = 0;
		$serviceHtml[1]['servicetxt'] = $serviceuse;
		$serviceHtml[1]['expire'] = "";
		$serviceHtml[1]['overtxt'] = '문자<br/>자동/수동<br/>발송';

		/* 메일 잔여수 */
		$master = config_load('master');
		$addService['mail'] = $master['mail_count'];

		/* 메일 체크여부 [외부독립서버,단독서버일 때에는 메일잔여수 체크 및 표시 안함] */
		$addService['mailuse'] = preg_match("/^F_SH_/",$this->config_system['service']['hosting_code']);
		if(!$addService['mailuse']){
			$serviceHtml[2]['num'] = 3;
			$serviceHtml[2]['name'] = "메일";
			$serviceHtml[2]['link'] = "/admin/member/catalog";
			$serviceHtml[2]['isblock'] = 0;
			$serviceHtml[2]['servicetxt'] = $addService['mail']."통/월";
			$serviceHtml[2]['expire'] = "";
			$serviceHtml[2]['overtxt'] = '이메일<br/>자동/수동<br/>발송';
		}


		/* 대량메일 잔여수 */
		$email_mass = config_load('email_mass');
		$addService['bulkmail'] = ($email_mass['name']) ? 1 : 0;
		if($addService['bulkmail']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[3]['num'] = 4;
		$serviceHtml[3]['name'] = "대량메일";
		$serviceHtml[3]['link'] = "/admin/member/amail_send";
		$serviceHtml[3]['isblock'] = $cssblock;
		$serviceHtml[3]['servicetxt'] = $serviceuse;
		$serviceHtml[3]['expire'] = "";
		$serviceHtml[3]['overtxt'] = '이메일<br/>대량<br/>발송';


		/* 모바일샵 */
		$basic_config = config_load('basic');
		$oper_type	= $basic_config['operating'];
		if($oper_type == 'general'){
			$mobile_use	= ($basic_config[$oper_type.'_m_use'] == 'N') ? 'Y':'N';
		}else{
			$mobile_use	= ($basic_config[$oper_type.'_m_use'] == 'Y') ? 'Y':'N';
		}

		$mobile_shop = 1;
		$addService['mobile']	= $mobile_use=='N' ? 0 : 1;
		if($addService['mobile']){
			$cssblock = 0;
			$serviceuse = '운영중';
		}else{
			$cssblock = 1;
			$serviceuse = 7;
		}

		$serviceHtml[4]['num'] = 5;
		$serviceHtml[4]['name'] = "모바일샵";
		$serviceHtml[4]['link'] = "/admin/setting/operating";
		$serviceHtml[4]['isblock'] = $cssblock;
		$serviceHtml[4]['servicetxt'] = $serviceuse;
		$serviceHtml[4]['expire'] = "";
		$serviceHtml[4]['overtxt'] = '최적화된<br/>모바일<br/>쇼핑몰';


		/* 페이스북샵 */
		$fbconfig = config_load('snssocial');
		$addService['facebook'] = ($fbconfig['page_id_f']) ? 1 : 0;

		if($addService['facebook']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[5]['num'] = 6;
		$serviceHtml[5]['name'] = "페이스북샵";
		$serviceHtml[5]['link'] = "/admin/setting/config";
		$serviceHtml[5]['isblock'] = $cssblock;
		$serviceHtml[5]['servicetxt'] = $serviceuse;
		$serviceHtml[5]['expire'] = "";
		$serviceHtml[5]['overtxt'] = '최대SNS<br/>페이스북<br/>쇼핑몰';


		/* 보안서버 사용여부 */
		if($this->config_system['ssl_use'] && $this->config_system['ssl_pay']){
			// 유료 시 기간체크
			$ssl_expire = $this->config_system['ssl_period_expire'];
			$addService['ssl_day'] = (strtotime($ssl_expire)-strtotime(date('Y-m-d',time())))/86400;
			$ssl_service = 3;
		}else if($this->config_system['ssl_use'] && !$this->config_system['ssl_pay']){
			$ssl_service = 2;
			$addService['ssl_day'] = -1;
		}else{
			$ssl_service = 0;
			$addService['ssl_day'] = -1;
		}
		$addService['ssl'] = $ssl_service;

		if($addService['ssl']<=0){
			$cssblock = 1;
		}else{
			$cssblock = 0;
		}

		if($addService['ssl']==3 && $addService['ssl_day']>=0 && $addService['ssl_day']<11){
			$popicon = "expire";
		}else{
			$popicon = "";
		}

		$serviceHtml[6]['num'] = 7;
		$serviceHtml[6]['name'] = "보안서버";
		$serviceHtml[6]['link'] = "/admin/setting/protect";
		$serviceHtml[6]['isblock'] = $cssblock;
		$serviceHtml[6]['servicetxt'] = $addService['ssl'];
		$serviceHtml[6]['expire'] = $popicon;
		$serviceHtml[6]['overtxt'] = '데이터<br/>암호화로<br/>보안강화';


		/* 다중 판매 */
		$this->load->model('openmarketmodel');
		$addService['multi'] = $this->openmarketmodel->chk_linkage_service();
		if($addService['multi']){
			$cssblock = 0;
			$serviceuse = '판매중';
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[7]['num'] = 8;
		$serviceHtml[7]['name'] = "다중판매";
		$serviceHtml[7]['link'] = "/admin/openmarket/regist";
		$serviceHtml[7]['isblock'] = $cssblock;
		$serviceHtml[7]['servicetxt'] = $serviceuse;
		$serviceHtml[7]['expire'] = "";
		$serviceHtml[7]['overtxt'] = '여러 쇼핑몰<br/>에서<br/>상품판매';


		/* 자동입금확인 무통장자동확인 사용여부 */
		$autodeposit_edate	= $this->config_system['autodeposit_edate'];
		$autodeposit_count	= $this->config_system['autodeposit_count'];
		$linkUrl			= '/admin/setting/bank';
		$serviceuse			= 0;
		$cssblock			= 1;
		$popicon			= '';
		if	($autodeposit_count){
			$addService['bankda']		= (!empty($autodeposit_edate)) ? 1 : 0;
			$addService['bankda_day']	= -1;
			if(!empty($autodeposit_edate))
				$addService['bankda_day'] = (strtotime($autodeposit_edate)-strtotime(date('Y-m-d')))/86400;
			if($addService['bankda']){
				$linkUrl = "/admin/order/autodeposit";
				$serviceuse = 1;
				$cssblock = 0;
				if($addService['bankda_day']>=0 && $addService['bankda_day']<11)	$popicon = "expire";
			}
		}

		$serviceHtml[8]['num'] = 9;
		$serviceHtml[8]['name'] = "자동입금";
		$serviceHtml[8]['link'] = $linkUrl;
		$serviceHtml[8]['isblock'] = $cssblock;
		$serviceHtml[8]['servicetxt'] = $serviceuse;
		$serviceHtml[8]['expire'] = $popicon;
		$serviceHtml[8]['overtxt'] = '무통장<br/>입금<br/>자동확인';


		/* 택배업무 자동화 - 택배자동 */
		$addService['postal'] = $this->config_system['invoice_use'];

		if($addService['postal']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[9]['num'] = 10;
		$serviceHtml[9]['name'] = "택배자동";
		$serviceHtml[9]['link'] = "/admin/setting/shipping";
		$serviceHtml[9]['isblock'] = $cssblock;
		$serviceHtml[9]['servicetxt'] = $serviceuse;
		$serviceHtml[9]['expire'] = "";
		$serviceHtml[9]['overtxt'] = '운송장<br/>택배업무<br/>자동화';


		/* 슬라이딩배너 */ //임시
		$slid = 1;
		$addService['sliding']	= $multi_sell['sliding']=='N' ? 0 : 1;
		if($addService['sliding']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[10]['num'] = 11;
		$serviceHtml[10]['name'] = "슬라이딩배너";
		$serviceHtml[10]['link'] = "#";
		$serviceHtml[10]['isblock'] = $cssblock;
		$serviceHtml[10]['servicetxt'] = $serviceuse;
		$serviceHtml[10]['expire'] = "";
		$serviceHtml[10]['overtxt'] = '움직이는<br/>배너<br/>만들기';


		/* 플래시매직 */ // 추후 추가
		$addService['flash_magic'] = 1;

		if($addService['flash_magic']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[11]['num'] = 12;
		$serviceHtml[11]['name'] = "플래시매직";
		$serviceHtml[11]['link'] = "#";
		$serviceHtml[11]['isblock'] = $cssblock;
		$serviceHtml[11]['servicetxt'] = $serviceuse;
		$serviceHtml[11]['expire'] = "";
		$serviceHtml[11]['overtxt'] = '플래시<br/>배너<br/>만들기';


		// 웹폰트
		if($this->config_system['font_service_check_date'] < date('Y-m-d') ){
			$this->load->helper('readurl');
			$requestUrl = "http://font.firstmall.kr/engine/font_list.php";
			$jsonfont = readurl($requestUrl,array('shop_no' => $this->config_system['shopSno']));
			$decode_arr = json_decode($jsonfont,true);
			$addService['font'] = 0;
			$addService['font_day'] = -1;

			if($decode_arr){
				$addService['font'] = 1;
				$font_day = $decode_arr[0]['end_date'];
				foreach($decode_arr as $key => $value){
					if($font_day > $value['end_date']) $font_day = $value['end_date'];
				}
				$addService['font_day'] = (strtotime($font_day)-strtotime(date('Y-m-d',time())))/86400;
				config_save('system',array('font_service_use'=>'y'));
			}else{
				config_save('system',array('font_service_use'=>'n'));
			}

			config_save('system',array('font_service_check_date'=>date('Y-m-d')));
		}

		if($this->config_system['font_service_use']=='y'){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		if($addService['font_day']>=0 && $addService['font_day']<11)
			$popicon = "expire";


		$serviceHtml[12]['num'] = 13;
		$serviceHtml[12]['name'] = "웹폰트";
		$serviceHtml[12]['link'] = "/admin/design/font";
		$serviceHtml[12]['isblock'] = $cssblock;
		$serviceHtml[12]['servicetxt'] = $serviceuse;
		$serviceHtml[12]['expire'] = $popicon;
		$serviceHtml[12]['overtxt'] = '예쁜<br/>폰트<br/>사용';


		/* 동영상 */
		$videoconfig = config_load('goods');
		$addService['video'] = ($videoconfig['ucc_domain'] && $videoconfig['ucc_key']) ? 1 : 0;

		if($addService['video']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[13]['num'] = 14;
		$serviceHtml[13]['name'] = "동영상";
		$serviceHtml[13]['link'] = "/admin/setting/video";
		$serviceHtml[13]['isblock'] = $cssblock;
		$serviceHtml[13]['servicetxt'] = $serviceuse;
		$serviceHtml[13]['expire'] = "";
		$serviceHtml[13]['overtxt'] = '동영상<br/>업로드 및<br/>플레이';


		/* 리마인드 */
		$remind = config_load('personal_use');
		if(in_array('y',$remind))		$remind_use = 'Y';
		else							$remind_use = 'N';

		$addService['personal_coupon_user_yn']	= ($remind_use=='N') ? 0 : 1;
		if($addService['personal_coupon_user_yn']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 7;
		}

		$serviceHtml[14]['num'] = 15;
		$serviceHtml[14]['name'] = "리마인드";
		$serviceHtml[14]['link'] = "/admin/member/curation";
		$serviceHtml[14]['isblock'] = $cssblock;
		$serviceHtml[14]['servicetxt'] = $serviceuse;
		$serviceHtml[14]['expire'] = "";
		$serviceHtml[14]['overtxt'] = '장바구니상품<br/>쿠폰,리뷰 등<br/>자동 알림';


		/* SNS회원 */
		$snsconfig = config_load('joinform');
		$addService['sns_use'] = ($snsconfig['use_f'] || $snsconfig['use_t'] || $snsconfig['use_c'] || $snsconfig['use_m']) ? 1 : 0;

		if($addService['sns_use']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[15]['num'] = 16;
		$serviceHtml[15]['name'] = "SNS회원";
		$serviceHtml[15]['link'] = "/admin/setting/member?gb=member";
		$serviceHtml[15]['isblock'] = $cssblock;
		$serviceHtml[15]['servicetxt'] = $serviceuse;
		$serviceHtml[15]['expire'] = "";
		$serviceHtml[15]['overtxt'] = '카카오톡<br/>페이스북<br/>회원가입';


		/* 회원등급별 혜택 - 등급혜택 */ // 추후 추가
		$addService['member_grade'] = 1;

		if($addService['member_grade']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[16]['num'] = 17;
		$serviceHtml[16]['name'] = "등급혜택";
		$serviceHtml[16]['link'] = "/admin/setting/member?gb=grade";
		$serviceHtml[16]['isblock'] = $cssblock;
		$serviceHtml[16]['servicetxt'] = $serviceuse;
		$serviceHtml[16]['expire'] = "";
		$serviceHtml[16]['overtxt'] = '등급별<br/>할인,적립<br/>혜택 설정';


		/* 휴대폰 인증 */
		$realname = config_load('realname');
		$addService['realphone'] = ($realname['useRealnamephone']=="Y") ? 1 : 0;
		if($addService['realphone']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[17]['num'] = 18;
		$serviceHtml[17]['name'] = "휴대폰인증";
		$serviceHtml[17]['link'] = "/admin/setting/member?gb=realname";
		$serviceHtml[17]['isblock'] = $cssblock;
		$serviceHtml[17]['servicetxt'] = $serviceuse;
		$serviceHtml[17]['expire'] = "";
		$serviceHtml[17]['overtxt'] = '회원가입<br/>휴대폰<br/>인증';


		/* 실명인증 ipin 사용여부 */
		$addService['ipin']	= $realname['useIpin']=='N' ? 0 : 1;
		if($addService['ipin']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[18]['num'] = 19;
		$serviceHtml[18]['name'] = "아이핀";
		$serviceHtml[18]['link'] = "/admin/setting/member?gb=realname";
		$serviceHtml[18]['isblock'] = $cssblock;
		$serviceHtml[18]['servicetxt'] = $serviceuse;
		$serviceHtml[18]['expire'] = "";
		$serviceHtml[18]['overtxt'] = '회원가입<br/>아이핀<br/>인증';


		/* 안심체크 사용여부 */
		$addService['realname']	= $realname['useRealname']=='N' ? 0 : 1;
		if($addService['realname']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[19]['num'] = 20;
		$serviceHtml[19]['name'] = "안심체크";
		$serviceHtml[19]['link'] = "/admin/setting/member?gb=realname";
		$serviceHtml[19]['isblock'] = $cssblock;
		$serviceHtml[19]['servicetxt'] = $serviceuse;
		$serviceHtml[19]['expire'] = "";
		$serviceHtml[19]['overtxt'] = '회원가입<br/>안심체크<br/>인증';


		/* 전자세금계산서 */
		$taxconfig = config_load('order');
		$addService['taxuse'] = ($taxconfig['taxuse']) ? 1 : 0;

		if($taxconfig['taxuse']>0){
			$cssblock = 0;
			$serviceuse = 6;
			if($this->config_system['webmail_key'] && $this->config_system['webmail_domain'] && $this->config_system['webmail_admin_id']){
				$cssblock = 0;
				$serviceuse = 1;
			}
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[20]['num'] = 21;
		$serviceHtml[20]['name'] = "전자세금계산";
		$serviceHtml[20]['link'] = "/admin/setting/sale";
		$serviceHtml[20]['isblock'] = $cssblock;
		$serviceHtml[20]['servicetxt'] = $serviceuse;
		$serviceHtml[20]['expire'] = "";
		$serviceHtml[20]['overtxt'] = '무료<br/>전자세금<br/>계산서';


		/* 외부통계분석 */
		// 추후 추가
		/*
		$addService['outstat'] = 1;

		if($addService['outstat']){
			$cssblock = 1;
			$serviceuse = "<span class='desc'>준비중</span>";
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[16]['num'] = 17;
		$serviceHtml[16]['name'] = "외부통계";
		$serviceHtml[16]['link'] = "";
		$serviceHtml[16]['isblock'] = $cssblock;
		$serviceHtml[16]['servicetxt'] = $serviceuse;
		$serviceHtml[16]['expire'] = "";
		*/



		/* 다음 쇼핑하우 */
		// 추후추가
		/*
		$addService['daumshop'] = 0;

		if($addService['daumshop']){
			$cssblock = 0;
			$serviceuse = 1;
		}else{
			$cssblock = 1;
			$serviceuse = 0;
		}

		$serviceHtml[17]['num'] = 18;
		$serviceHtml[17]['name'] = "쇼핑하우";
		$serviceHtml[17]['link'] = "/admin/marketing/marketplace";
		$serviceHtml[17]['isblock'] = $cssblock;
		$serviceHtml[17]['servicetxt'] = $serviceuse;
		$serviceHtml[17]['expire'] = "";
		*/


		/* 어바웃 고정값  삭제 */
		/*
		$serviceHtml[18]['num'] = 19;
		$serviceHtml[18]['name'] = "";
		$serviceHtml[18]['link'] = "/admin/marketing/marketplace";
		$serviceHtml[18]['isblock'] = "0";
		$serviceHtml[18]['servicetxt'] = "1544-3270";
		$serviceHtml[18]['expire'] = "";
		*/


		/* 나머지 빈칸 채우기 */
		if((sizeof($serviceHtml) % 7) != 0){
			$loop_td = 7 - (sizeof($serviceHtml) % 7);
			for($i=0;$i<$loop_td;$i++){
				$serviceHtml[]['nbsp_html'] = "
					<th>&nbsp;</th>
					<td>&nbsp;</td>
					";
			}
		}


		$this->template->assign('addService', $addService);
		$this->template->assign('serviceHtml', $serviceHtml);

		if (!$type) {
			$file_path	= $this->template_path();
			$file_path	= str_replace('get_main_myservice_bar', '_main_myservice_bar', $file_path);
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		} else {
			$this->template->define(array('main_myservice_bar'=>$this->skin."/main/_main_myservice_bar.html"));
		}
	}

	public function get_sms_info(){
		/* SMS 건수 */

		$auth = config_load('master');
		$limit	= commonCountSMS();

		$addService['sms'] = $limit;
		$int_sms = (int)str_replace(',','',$addService['sms']);

		if($int_sms<50){
			$popicon = "charge";
		}

		$return = array();
		if ($popicon=="charge") {
			$return['html'] = sprintf("<div class='myservice_area'><div class='myservice_%s'><img src='/admin/skin/default/images/main/icon_%s.gif' /></div>",$popicon,$popicon);
		} else {
			$return['html'] = "<div class='myservice_area'></div>";
		}

		$return['txt_cnt'] = $addService['sms']."통";

		echo json_encode($return);
	}

	/* 상단 카운터 바  :: 2014-10-21 lwh*/
	public function get_main_count_bar($type=""){

		####################### 오늘 ############################

		## 오늘 신규회원 ##
		$query = $this->db->query("select count(*) as cnt from fm_member where regist_date between ? and ?",array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')));
		list($todayCount['new_member'])		= array_values($query->row_array());
		$todayCount['new_member']			= number_format($todayCount['new_member']);
		for($i=0;$i<strlen($todayCount['new_member']);$i++)
			$todayCount['new_member_arr'][$i]	= substr($todayCount['new_member'], $i, 1);

		## 오늘 방문자 ##
		$query = $this->db->query("select count_sum from fm_stats_visitor_count where count_type = 'visit' and stats_date = ?",date('Y-m-d'));
		list($todayCount['visit'])			= array_values($query->row_array());
		$todayCount['visit']				= number_format($todayCount['visit']);
		for($i=0;$i<strlen($todayCount['visit']);$i++)
			$todayCount['visit_arr'][$i]	= substr($todayCount['visit'], $i, 1);

		## 오늘 결제 금액 ##  ## 오늘 결제 건수 ##
		$query = $this->db->query("select sum(settleprice) as total_price, count(*) as total_cnt from fm_order where deposit_yn = 'y' and step between '15' and '75' and deposit_date between '".date('Y-m-d')." 00:00:00' and '".date('Y-m-d')." 23:59:59'");

		list($todayCount['total_price'], $todayCount['total_cnt'])	= array_values($query->row_array());
		$todayCount['total_price']			= number_format($todayCount['total_price']);
		for($i=0;$i<strlen($todayCount['total_price']);$i++)
			$todayCount['total_price_arr'][$i]	= substr($todayCount['total_price'], $i, 1);
		$todayCount['total_cnt']			= number_format($todayCount['total_cnt']);
		for($i=0;$i<strlen($todayCount['total_cnt']);$i++)
			$todayCount['total_cnt_arr'][$i]	= substr($todayCount['total_cnt'], $i, 1);

		$this->template->assign('todayCount', $todayCount);

		####################### 누적 ############################

		## 누적회원 ##
		$query = $this->db->query("select count(*) as cnt from fm_member where `status` in ('done','hold')");
		list($totalCount['member']) = array_values($query->row_array());
		$totalCount['member']		= number_format($totalCount['member']);
		for($i=0;$i<strlen($totalCount['member']);$i++)
			$totalCount['member_arr'][$i]	= substr($totalCount['member'], $i, 1);

		## 누적적립금 ##
		$query = $this->db->query("select sum(emoney) as emoney_sum from fm_member where `status`='done'");
		list($totalCount['emoney']) = array_values($query->row_array());
		$totalCount['emoney']		= number_format($totalCount['emoney']);
		for($i=0;$i<strlen($totalCount['emoney']);$i++)
			$totalCount['emoney_arr'][$i]	= substr($totalCount['emoney'], $i, 1);

		## 누적포인트 ##
		$query = $this->db->query("select sum(point) as point_sum from fm_member where `status`='done'");
		list($totalCount['point'])	= array_values($query->row_array());
		$totalCount['point']		= number_format($totalCount['point']);
		for($i=0;$i<strlen($totalCount['point']);$i++)
			$totalCount['point_arr'][$i]	= substr($totalCount['point'], $i, 1);

		$this->template->assign('totalCount', $totalCount);

		###################### 타입별 분기 #######################

		if (!$type) {
			$file_path	= $this->template_path();
			$file_path	= str_replace('get_main_count_bar', '_main_count_bar', $file_path);
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		} else {
			$this->template->define(array('main_count_bar'=>$this->skin."/main/_main_count_bar.html"));
		}
	}


	/* 주문 요약 :: 2014-10-21 lwh 수정 */
	public function _print_main_order_summary($type=""){

		$orderSummary	= array();
		$step_arr		= array('15'=>'주문접수', '25'=>'결제확인', '35'=>'상품준비', '40'=>'부분출고준비', '45'=>'출고준비', '50'=>'부분출고완료', '55'=>'출고완료', '60'=>'부분배송중', '65'=>'배송중', '70'=>'부분배송완료');

		// 주문 3개월 정보를 보여줌으로 바꿈 :: 2015-01-02 lwh - 팀장님 의견 이사님 지시
		$date_range	= " and regist_date between '"
					. date('Y-m-d', strtotime('-100 day'))." 00:00:00' "
					. " and '".date('Y-m-d')." 23:59:59' ";

		$sql	= "
				SELECT count(*) as cnt , step
				FROM `fm_order`
				WHERE hidden = 'N'
				".$date_range."
				GROUP BY step
				";
		$query	= $this->db->query($sql);
		foreach ($query->result_array() as $row){
			$result[$row['step']]	= $row['cnt'];
		}

		foreach ($step_arr as $key => $val){
			$orderSummary[$key] = array(
			'count'			=> ($result[$key]) ? $result[$key] : 0,
			'name'			=> $val,
			'link'			=> "../order/catalog?chk_step[".$key."]=1"
			);

			if($key == '45' || $key == '55' || $key == '65'){
				$orderSummary[$key]['link_export'] = "../export/catalog?export_status[".$key."]=1";
			}
		}

		/* 반품 접수 */
		$query = $this->db->query("select count(*) as cnt from fm_order_return where `status` = 'request' ".$date_range);
		$result = $query->row_array();
		$orderSummary['101'] = array(
			'count'		=> ($result['cnt']) ? $result['cnt'] : 0,
			'name'		=> '반품접수',
			'link'		=> '../returns/catalog?return_status[]=request'
		);

		/* 환불 접수 */
		$query = $this->db->query("select count(*) as cnt from fm_order_refund where `status` = 'request' ".$date_range);
		$result = $query->row_array();
		$orderSummary['102'] = array(
			'count'		=> ($result['cnt']) ? $result['cnt'] : 0,
			'name'		=> '환불접수',
			'link'		=> '../refund/catalog?refund_status[]=request'
		);


		/* 반품 접수 */
		/*$query = $this->db->query("select count(*) as cnt from fm_order_return where `status` in ('request','ing')");
		$result = $query->row_array();
		$orderSummary['return'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../returns/catalog?return_status[]=request&return_status[]=ing'
		);*/

		/* 환불 접수 */
		/*$query = $this->db->query("select count(*) as cnt from fm_order_refund where `status` in ('request','ing')");
		$result = $query->row_array();
		$orderSummary['refund'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../refund/catalog?refund_status[]=request&refund_status[]=ing'
		);
		*/

		/* 입금 접수 */
		/*$query = $this->db->query("select sum(settleprice) as settleprice, count(*) as cnt from fm_order where step = '15' and hidden = 'N'");
		$result = $query->row_array();
		$orderSummary['deposit'] = array(
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'link'			=> '../order/catalog?chk_step[15]=1'
		);*/

		/* 발송 대기 */
		/*$query = $this->db->query("select count(*) as cnt from fm_order where step in ('25','35','40','50','60','70')");
		$result = $query->row_array();
		$orderSummary['send1'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../order/catalog?chk_step[25]=1&chk_step[35]=1&chk_step[40]=1&chk_step[50]=1&chk_step[60]=1&chk_step[70]=1'
		);*/

		/* 발송 준비 */
		/*$query = $this->db->query("select count(*) as cnt,'export' as 'type' from (select 1 as cnt from fm_goods_export exp LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq, fm_goods_export_item as item where exp.export_code = item.export_code and exp.status in ('45') and not(exp.status='45' and ord.step='85') group by exp.export_seq) as a");
		$result = $query->row_array();
		$orderSummary['send2'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../export/catalog?export_status[45]=1'
		);*/

		/* 배송 중 */
		/*$query = $this->db->query("select count(*) as cnt,'export' as 'type' from (select 1 as cnt from fm_goods_export exp, fm_goods_export_item as item where exp.export_code = item.export_code and exp.status in ('55','65') group by exp.export_seq) as a");
		$result = $query->row_array();
		$orderSummary['delivery'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../export/catalog?export_status[55]=1&export_status[65]=1'
		);*/

		/* 반품 접수 */
		/*$query = $this->db->query("select count(*) as cnt from fm_order_return where `status` in ('request','ing')");
		$result = $query->row_array();
		$orderSummary['return'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../returns/catalog?return_status[]=request&return_status[]=ing'
		);*/

		/* 환불 접수 */
		/*$query = $this->db->query("select count(*) as cnt from fm_order_refund where `status` in ('request','ing')");
		$result = $query->row_array();
		$orderSummary['refund'] = array(
			'count'		=> $result['cnt'],
			'link'		=> '../refund/catalog?refund_status[]=request&refund_status[]=ing'
		);
		*/

		$this->template->assign(array('orderSummary'=>$orderSummary));

		$this->template->define(array('main_order_summary'=>$this->skin."/main/_main_order_summary.html"));

		if (!$type) {
			$this->template->print_("main_order_summary");
		}
	}

	/* 상품현황 요약 :: 2014-10-21 lwh */
	public function _print_main_goods_summary($type=""){

		//## 판매중의 의미 : 내가 보유한 판매가 가능한 상품의 수 (노출의 여부는 중요 X)

		/* 판매중인 실물상품 */
		$query = $this->db->query("select count(*) as cnt from fm_goods where goods_status = 'normal' and goods_kind = 'goods' and goods_type = 'goods' ");
		$result = $query->row_array();
		$goodsSummary['goods'] = array(
			'count'			=> $result['cnt'],
			'link'			=> '/admin/goods/catalog?goodsStatus[]=normal'
		);

		/* 판매중인 티켓상품 */
		$query = $this->db->query("select count(*) as cnt from fm_goods where goods_status = 'normal' and goods_kind = 'coupon' and goods_type = 'goods' ");
		$result = $query->row_array();
		$goodsSummary['social'] = array(
			'count'			=> $result['cnt'],
			'link'			=> '/admin/goods/social_catalog?goodsStatus[]=normal'
		);

		/* 종료 5일전 실물상품 */
		$query = $this->db->query("select count(*) cnt from (select * from fm_event where end_date between '".date('Y-m-d H:i:s')."' and '".date('Y-m-d', strtotime('+5 day'))." 23:59:59' and event_type = 'solo' group by goods_seq )as evt, fm_goods as g where evt.goods_seq = g.goods_seq and g.goods_kind = 'goods' and g.goods_status = 'normal' ");
		$result = $query->row_array();
		$goodsSummary['endgoods'] = array(
			'count'			=> $result['cnt'],
			'link'			=> '/admin/event/catalog?date=end_date&sdate='.date('Y-m-d').'&edate='.date('Y-m-d', strtotime('+5 day')).'&event_status[]=ing&sc_event_type=solo&sc_goods_type=goods'
		);
		/* 종료 5일전 티켓상품 */
		$query = $this->db->query("select count(*) cnt from (select * from fm_event where end_date between '".date('Y-m-d H:i:s')."' and '".date('Y-m-d', strtotime('+5 day'))." 23:59:59' and event_type = 'solo' group by goods_seq )as evt, fm_goods as g where evt.goods_seq = g.goods_seq and g.goods_kind = 'coupon' and g.goods_status = 'normal' ");
		$result = $query->row_array();
		$goodsSummary['endsocial'] = array(
			'count'			=> $result['cnt'],
			'link'			=> '/admin/event/catalog?date=end_date&sdate='.date('Y-m-d').'&edate='.date('Y-m-d', strtotime('+5 day')).'&event_status[]=ing&sc_event_type=solo&sc_goods_type=coupon'
		);

		/* 재고10개이하 실물상품 */
		$query = $this->db->query("select count(*) as cnt from fm_goods where goods_kind = 'goods' and goods_type = 'goods' and tot_stock <= 10");
		$result = $query->row_array();
		$goodsSummary['stockgoods'] = array(
			'count'			=> $result['cnt'],
			'link'			=> '/admin/goods/catalog?estock=10'
		);
		/* 재고10개이하 티켓상품 */
		$query = $this->db->query("select count(*) as cnt from fm_goods where goods_kind = 'coupon' and goods_type = 'goods' and tot_stock <= 10");
		$result = $query->row_array();
		$goodsSummary['stocksocial'] = array(
			'count'			=> $result['cnt'],
			'link'			=> '/admin/goods/social_catalog?estock=10'
		);

		$this->template->assign(array('goodsSummary'=>$goodsSummary));

		$this->template->define(array('main_goods_summary'=>$this->skin."/main/_main_goods_summary.html"));

		if (!$type) {
			$this->template->print_("main_goods_summary");
		}
	}

	/* 1:1문의 요약 :: 2014-10-21 lwh */
	public function _print_main_qna_summary($type=""){
		$this->load->helper('board');//

		$this->load->model('Boardmanager');
		$this->load->model('membermodel');
		$this->load->model('boardmodel');
		$this->load->model('boardadmin');

		$this->template->assign('realboardurl',$this->Boardmanager->realboardurl);
		$this->template->assign('realboardwriteurl',$this->Boardmanager->realboardwriteurl);
		$this->template->assign('realboardviewurl',$this->Boardmanager->realboardviewurl);

		$limit = 6;

		unset($bdwidget, $widgetloop,$boardurl);
		$bdwidget['boardid']	= 'mbqna';
		$bdwidget['limit']		= $limit;//
		getAdminBoardWidgets($bdwidget, $widgetloop, $name, $totalcount);

		$this->template->assign(array('mbqnaname'=>$name,'mbqnatotalcount'=>$totalcount));
		if(isset($widgetloop)) {
			$tmp['seq'] = 0;
			for($i=0; $i<6; $i++){
				$widgetloop[$i] = ($widgetloop[$i]) ? $widgetloop[$i] : $tmp;
			}
			$this->template->assign('mbqnaloop',$widgetloop);

			$reply_sc	= array('boardid'=>$bdwidget['boardid'], 'searchreply'=>'y');
			$reply_cnt	= $this->boardmodel->reply_count($reply_sc);

			$this->template->assign('mbqna_reply_cnt',$reply_cnt);
		}

		if (!$type) {
			$file_path	= $this->template_path();
			$file_path	= str_replace('get_main_contents_pannel', '_main_qna_summary', $file_path);
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		} else {
			$this->template->define(array('main_qna_summary'=>$this->skin."/main/_main_qna_summary.html"));
		}
	}

	/* 상품문의 :: 2014-10-23 lwh 수정 */
	public function _print_main_goodsqna_summary($type=""){
		$this->load->helper('board');//

		$this->load->model('Boardmanager');
		$this->load->model('membermodel');
		$this->load->model('boardmodel');
		$this->load->model('boardadmin');

		$this->template->assign('realboardurl',$this->Boardmanager->realboardurl);
		$this->template->assign('realboardwriteurl',$this->Boardmanager->realboardwriteurl);
		$this->template->assign('realboardviewurl',$this->Boardmanager->realboardviewurl);

		$limit = 6;

		unset($bdwidget, $widgetloop,$boardurl);
		$bdwidget['boardid']	= 'goods_qna';
		$bdwidget['limit']		= $limit;//
		getAdminBoardWidgets($bdwidget, $widgetloop, $name, $totalcount);

		$this->template->assign(array('goodsqnaname'=>$name,'goodsqnatotalcount'=>$totalcount));
		if(isset($widgetloop)) {
			$tmp['seq'] = 0;
			for($i=0; $i<6; $i++){
				$widgetloop[$i] = ($widgetloop[$i]) ? $widgetloop[$i] : $tmp;
			}
			$this->template->assign('goodsqnaloop',$widgetloop);

			$reply_sc	= array('boardid'=>$bdwidget['boardid'], 'searchreply'=>'y');
			$reply_cnt	= $this->boardmodel->reply_count($reply_sc);

			$this->template->assign('goods_qna_reply_cnt',$reply_cnt);
		}

		if (!$type) {
			$file_path	= $this->template_path();
			$file_path	= str_replace('get_main_contents_pannel', '_main_goodsqna_summary', $file_path);
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		} else {
			$this->template->define(array('main_goodsqna_summary'=>$this->skin."/main/_main_goodsqna_summary.html"));
		}
	}

	/* 예약문의 :: 2014-10-23 lwh 수정 */
	public function _print_main_reserve_upgrade_area($type=""){
		$this->load->helper('board');//

		$this->load->model('Boardmanager');
		$this->load->model('membermodel');
		$this->load->model('boardadmin');

		$this->template->assign('realboardurl',$this->Boardmanager->realboardurl);
		$this->template->assign('realboardwriteurl',$this->Boardmanager->realboardwriteurl);
		$this->template->assign('realboardviewurl',$this->Boardmanager->realboardviewurl);

		$limit = 6;

		unset($bdwidget, $widgetloop,$boardurl);
		$bdwidget['boardid']	= 'store_reservation';
		$bdwidget['limit']		= $limit;//
		getAdminBoardWidgets($bdwidget, $widgetloop, $name, $totalcount);
		$this->template->assign(array('reservename'=>$name,'reservetotalcount'=>$totalcount));
		if(isset($widgetloop)) {
			$tmp['seq'] = 0;
			for($i=0; $i<6; $i++){
				$widgetloop[$i] = ($widgetloop[$i]) ? $widgetloop[$i] : $tmp;
			}
			$this->template->assign('reserveloop',$widgetloop);

			$reply_sc	= array('boardid'=>$bdwidget['boardid'], 'searchreply'=>'y');
			$reply_cnt	= $this->boardmodel->reply_count($reply_sc);

			$this->template->assign('reserve_reply_cnt',$reply_cnt);
		}

		if (!$type) {
			$file_path	= $this->template_path();
			$file_path	= str_replace('get_main_contents_pannel', '_main_reserve_summary', $file_path);
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		} else {
			$this->template->define(array('main_reserve_summary'=>$this->skin."/main/_main_reserve_summary.html"));
		}
	}

	/* 공지사항 영역 Define */
	public function _print_main_news_notice_area($load_type = ''){
		$this->load->helper('text');
		$this->load->library('SofeeXmlParser');

		$xmlParser = new SofeeXmlParser();
		$xmlParser->parseFile("http://firstmall.kr/ec_hosting/rss/_notice/rss.php?channel=notice&solution=firstmall_plus&limit=8");
		$tree = $xmlParser->getTree();

		$mainNewsNoticeList = $tree['rss']['channel']['item'];

		$this->template->assign(array('mainNewsNoticeList'=>$mainNewsNoticeList));

		$this->template->define(array('main_news_notice_area'=>$this->skin."/main/_main_news_notice_area.html"));
		if	(!$load_type){
			$this->template->print_("main_news_notice_area");
		}
	}

	/* 업그레이드 영역 Define */
	public function _print_main_news_upgrade_area($load_type = ''){
		$this->load->helper('text');
		$this->load->library('SofeeXmlParser');

		$xmlParser = new SofeeXmlParser();
		$xmlParser->parseFile("http://firstmall.kr/ec_hosting/rss/_notice/rss.php?channel=upgrade&solution=firstmall_plus&shopSno={$this->config_system['shopSno']}&service_type=".SERVICE_CODE."&limit=4");
		$tree = $xmlParser->getTree();

		$mainNewsUpgradeList = $tree['rss']['channel']['item'];

		$this->template->assign(array('mainNewsUpgradeList'=>$mainNewsUpgradeList));
		$this->template->define(array('main_news_upgrade_area'=>$this->skin."/main/_main_news_upgrade_area.html"));
		if	(!$load_type){
			$this->template->print_("main_news_upgrade_area");
		}
	}

	/* 관리자메모 영역 Define */
	public function _define_admin_memo_area(){
		$this->template->define(array('admin_memo_area'=>$this->skin."/main/_admin_memo_area.html"));
	}


	/* 게시판적립금지급 Define */
	public function _define_board_emoney_form(){
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		$this->template->assign('reserve_goods_review',$reserves['reserve_goods_review']);
		$this->template->define(array('emoneyform'=>$this->skin.'/board/_emoney.html'));
	}


	public function login(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 상단 메뉴얼 펜슬 배너 영역 Define */
	public function _define_main_manual_banner_area(){
		$this->template->define(array('main_manual_banner_area'=>$this->skin."/main/_main_manual_banner_area.html"));
	}

	public function main_demo()
	{
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 메인 페이지 캐쉬 생성 시간 체크 */
	// 현재 사용 안함(by 김동영과장) :: 2014-10-29 lwh
	public function chk_caching(){

		$cache_file_path	= $this->cach_file_path . $this->session->userdata['manager']['manager_id'] . '_' . $this->cach_file_name;
		$cache_file_url		= $this->cach_file_url . $this->session->userdata['manager']['manager_id'] . '_' . $this->cach_file_name;

		if	(file_exists($cache_file_path)){
			if	(strtotime('-4 hour') > filemtime($cache_file_path))	return false;
			else{
				$this->template->define(array('tpl'=>$cache_file_url));
				$this->template->print_("tpl");
//				echo $file_contents;
				exit;
			}
		}
	}

	/* 메인 페이지 전체 캐쉬 처리 */
	public function main_caching(){

		ob_start();
		$this->index('caching');
		$cach_index	= ob_get_contents();
		ob_end_clean();

		$cach_index	= str_replace('_main_caching();', '', $cach_index);
		$cach_index	= $cach_index . '<!-- End Cach Page ' . date('Y-m-d H:i:s') . ' -->';

		$cache_file_path	= $this->cach_file_path . $this->session->userdata['manager']['manager_id'] . '_' . $this->cach_file_name;

		$file_obj	= fopen($cache_file_path, 'w+');
		if	(!$file_obj){
			$dir_name	= dirname($cache_file_path);
			if( !is_dir($dir_name) )	@mkdir($dir_name);
			@chmod($dir_name,0777);
			$file_obj	= fopen($cache_file_path, 'w+');
		}

		fwrite($file_obj, $cach_index);
		fclose($file_obj);

		if	($_GET['abreload'] == 'y'){
			$callback = "parent.location.reload();";
			openDialogAlert("업데이트되었습니다.",400,140,'parent',$callback);
		}
	}

	/* 메인 페이지 전체 캐쉬 제거 */
	public function main_cach_delete(){

		$cache_file_path	= $this->cach_file_path . $this->session->userdata['manager']['manager_id'] . '_' . $this->cach_file_name;

		if	(file_exists($cache_file_path)){
			@unlink($cache_file_path);
		}

		$callback = "parent.location.reload();";
		openDialogAlert("업데이트되었습니다.",400,140,'parent',$callback);
	}

	/* 메인 페이지 통계 캐쉬 생성 시간 체크 */
	public function chk_stats_caching(){
		// 운영자별 페이지 생성
		$this->cach_stat_file	= 'admin_main_stats_'.$this->managerInfo['manager_id'].'.html';

		$cache_file_path	= $this->cach_file_path . $this->cach_stat_file;
		$cache_file_url		= $this->cach_file_url . $this->cach_stat_file;

		if	(!file_exists($cache_file_path) || strtotime('-4 hour') > filemtime($cache_file_path)){
			$this->main_stats_caching();
		}

		$this->template->define(array('main_statistic_area'=>$cache_file_url));

		return filemtime($cache_file_path);
	}

	/* 메인 페이지 통계 캐쉬 처리 */
	public function main_stats_caching(){

		ob_start();
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('advanced_statistic');
		if(!$result['type']){
			$this->advanced_statistic_limit	= 'y';
			$this->template->assign(array('advanced_statistic_limit' => 'y'));
		}
		$this->load->model('statsmodel');
		$this->statsmodel->get_main_statistic_data();
		$this->template->assign(array(
			'dataForChart'	=> $this->statsmodel->dataForChart,
			'maxValue'		=> $this->statsmodel->maxValue,
			'stat'			=> $this->statsmodel->stat,
			'rank_array'	=> $this->statsmodel->rank_array
		));

		// 매출 상위 유입경로 (최근 10일)
		$params['dateSel_type']	= '10days';
		$query					= $this->statsmodel->get_sales_referer_stats($params);
		$total_cnt				= 0;
		$rank					= 0;
		$old_cnt				= 0;
		foreach ($query->result_array() as $k => $row){
			$total_cnt				+= $row['cnt'];
			$refer_data['data'][]	= array($k.'번', (int)$row['cnt']);
			$refer_data['label'][]	= array('label', $row['referer_name']);
			$rank					= ($old_cnt == $row['cnt']) ? $rank : ++$rank;
			$old_cnt				= $row['cnt'];
			$row['rank']			= $rank;
			$tb_refer[]				= $row;
		}
		$refer_data['total']	= $total_cnt;
		$this->template->assign(array(
			'refer_data' => $refer_data,
			'refer_loop' => $tb_refer
		));

		$this->template->define(array('tpl'=>$this->skin."/statistic/statistic_main.html"));
		$this->template->print_("tpl");
		$cach_stats	= ob_get_contents();
		ob_end_clean();

		$cach_stats			= $cach_stats . '<!-- End Cach Page ' . date('Y-m-d H:i:s') . ' -->';
		$cache_file_path	= $this->cach_file_path . $this->cach_stat_file;

		$file_obj	= fopen($cache_file_path, 'w+');
		if	(!$file_obj){
			$dir_name	= dirname($cache_file_path);
			if( !is_dir($dir_name) )	@mkdir($dir_name);
			@chmod($dir_name,0777);
			$file_obj	= fopen($cache_file_path, 'w+');
		}

		fwrite($file_obj, $cach_stats);
		fclose($file_obj);
	}

	/* 메인 페이지 통계 캐쉬 제거 */
	public function main_stats_cach_delete(){

		// 운영자별 페이지 생성 체크
		$this->cach_stat_file	= 'admin_main_stats_'.$this->managerInfo['manager_id'].'.html';
		$cache_file_path	= $this->cach_file_path . $this->cach_stat_file;

		if	(file_exists($cache_file_path)){
			@unlink($cache_file_path);
		}

		$callback = "parent.location.reload();";
		openDialogAlert("업데이트되었습니다.",400,140,'parent',$callback);
	}

	public function popup_change_pass()
	{
		$this->template->define(array('tpl'=>$this->skin."/main/popup_change_pass.html"));
		$this->template->print_("tpl");
	}
}

/* End of file main.php */
/* Location: ./app/controllers/admin/main.php */