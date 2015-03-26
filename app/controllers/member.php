<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class member extends front_base {

	function __construct() {
		parent::__construct();
		$this->load->library('snssocial');
		$this->load->helper('member');
		$this->joinform = config_load('joinform');
		unset($joinform['use_y']);//폐지@2013-04-29
		unset($joinform['use_m']);//폐지@2014-07-01
	}

	public function main_index()
	{
		redirect("/member/index");
	}

	public function index()
	{

	}

	public function agreement()
	{
		if($this->userInfo){
			 pageRedirect($url='/main/index', $msg = '', $target = 'self');
			 exit;
		}
		//sns subdomain
		if ( $this->config_system['domain'] && $this->config_system['domain'] == $_SERVER['HTTP_HOST'] )
			$this->template->assign("isdomain",true);//정식도메인
		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));

		$joinform = ($this->joinform)?$this->joinform:config_load('joinform'); 
		if(!trim($this->arrSns['key_k'])){ $joinform['use_k'] = ""; }
		$use_sns = array();
		if($joinform['use_f']) $use_sns['facebook'] = array('nm'=>'페이스북','cd'=>'fb');
		if($joinform['use_t']) $use_sns['twitter']	= array('nm'=>'트위터','cd'=>'tw');
		if($joinform['use_m'] && date("Ymd") < "20140701") $use_sns['me2day']	= array('nm'=>'미투데이','cd'=>'m2');
		if($joinform['use_c']) $use_sns['cyworld']	= array('nm'=>'싸이월드','cd'=>'cy');
		if($joinform['use_n']) $use_sns['naver']	= array('nm'=>'네이버','cd'=>'nv');
		if($joinform['use_k']) $use_sns['kakao']	= array('nm'=>'카카오','cd'=>'kk');
		if($joinform['use_d']) $use_sns['daum']		= array('nm'=>'다음','cd'=>'dm');
		$joinform['use_sns'] = $use_sns;
		$this->template->assign('register',true);
		$join_type = isset($_GET['join_type']) ? $_GET['join_type'] : false;

		$emoneyapp = config_load('member');
		$this->template->assign('emoneyapp',$emoneyapp);


		$this->load->model('membermodel');
		if($this->session->userdata('fb_invite')){
			$fbinvitemdata = $this->membermodel->get_member_data($this->session->userdata('fb_invite'));//회원정보
			if($fbinvitemdata['member_seq']) {
				$this->session->set_userdata('fbinvitestr', $this->session->userdata('fb_invite'));
				$this->session->set_userdata('fb_invite', $fbinvitemdata['member_seq']);

				$this->template->assign('fb_invite',$fbinvitemdata);
				$this->template->assign('recommend',$fbinvitemdata);
			}else{
				$this->session->unset_userdata('fbinvitestr');
				$this->session->unset_userdata('fb_invite');
			}
		}

		//신규회원가입쿠폰발급
		$this->load->model('couponmodel');
		$sc['whereis'] = ' and (type="member" or type="member_shipping") and issue_stop = 0 ';//발급중지가 아닌경우 
		$coupon_multi_list = $this->couponmodel->get_coupon_multi_list($sc);
		foreach($coupon_multi_list as $coupon_multi){
			$couponmember 	= $this->couponmodel->get_coupon($coupon_multi['coupon_seq']);
			/* 사용제한 - 금액이 있을 경우 표시 leewh 2014-10-28 */
			if ($couponmember['limit_goods_price'] > 0) {
				$couponmember['limit_goods_price_title'] = sprintf("%s원 이상 구매시",number_format($couponmember['limit_goods_price']));
			}
			$couponmemberar[] = $couponmember;
			if( $num == 0 ) {//예전스킨용
				$num++;
				$this->template->assign('couponmember',$couponmember);
			}
		} 
		$this->template->assign('couponmemberarray',$couponmemberar);

		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');

		if( ($realname['useRealnamephone']=='Y' || $realname['useIpin']=='Y') && $auth['auth_yn']!='Y' ) {//
			$this->template->assign('realnameinfo',$realname);
			$this->print_layout($this->skin.'/member/auth_chk.html');
			exit;
		}


		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$this->template->assign('shopName',$arrBasic['shopName']);
		if( ($joinform['join_type']=='member_business' || $joinform['use_f'] 
				|| $joinform['use_t']  || $joinform['use_c']  || $joinform['use_m']  
				|| $joinform['use_y']  || $joinform['use_g']  || $joinform['use_p']  
				|| $joinform['use_n']  || $joinform['use_k'] 
			) && !$join_type) {
			if($joinform) $this->template->assign('joinform',$joinform);
			$this->print_layout($this->skin.'/member/join_gate.html');
		}else{
			$member = config_load('member');
			$member['agreement'] = str_replace("{shopName}",$arrBasic['shopName'],$member['agreement']);
			$member['privacy'] = str_replace("{shopName}",$arrBasic['shopName'],$member['privacy']);
			$member['privacy'] = str_replace("{domain}",$arrBasic['domain'],$member['privacy']);

			//개인정보 수집-이용
			$member['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));

			if($joinform['use_k'] && $joinform['use_sns']) $this->template->assign('use_sns', $joinform['use_sns']);
			$this->template->assign($member);
			$this->print_layout($this->template_path());
		}
	}

	public function register()
	{
		if($this->userInfo){
			 pageRedirect($url='/main/index', $msg = '', $target = 'self');
			 exit;
		}
		$email = code_load('email');
		$joinform = ($this->joinform)?$this->joinform:config_load('joinform'); 

		$emoneyapp = config_load('member');
		$this->template->assign('emoneyapp',$emoneyapp);


		$this->load->model('membermodel');
		if($this->session->userdata('fb_invite')){
			$fbinvitemdata = $this->membermodel->get_member_data($this->session->userdata('fb_invite'));//회원정보
			if($fbinvitemdata['member_seq']) {
				$this->session->set_userdata('fbinvitestr', $this->session->userdata('fb_invite'));
				$this->session->set_userdata('fb_invite', $fbinvitemdata['member_seq']);

				$this->template->assign('fb_invite',$fbinvitemdata);
				$this->template->assign('recommend',$fbinvitemdata);
			}else{
				$this->session->unset_userdata('fbinvitestr');
				$this->session->unset_userdata('fb_invite');
			}
		}

		$mtype = 'member';
		if($joinform['join_type']=='business_only' || ($joinform['join_type']=='member_business' && $_GET['join_type']=='business')){
			$mtype = 'business';
		}

		//가입 추가 정보 리스트
		$msubdata = '';
		$qry = "select * from fm_joinform where used='Y' order by sort_seq";
		$query = $this->db->query($qry);
		$form_arr = $query -> result_array();
		foreach ($form_arr as $k => $data){
		$data['label_view'] = $this -> membermodel-> get_labelitem_type($data,$msubdata);
		$sub_form[] = $data;

		}
		for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
		for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
		$this->template->assign('m_arr',$m_arr);
		$this->template->assign('d_arr',$d_arr);
		$this->template->assign('recommend',$this->session->userdata('recommend'));//추천회원자동등록됨
		$this->template->assign('form_sub',$sub_form);
		$auth = $this->session->userdata('auth');

		if($auth["namecheck_name"]) $this->template->assign('user_name',$auth["namecheck_name"]);
		if($auth["namecheck_key"]) $this->template->assign('safe_key',$auth["namecheck_key"]);
		if($auth["namecheck_birth"]) $this->template->assign('birthday', substr($auth["namecheck_birth"],0 ,4)."-".substr($auth["namecheck_birth"],4,2)."-".substr($auth["namecheck_birth"],-2));
		if($auth["namecheck_sex"]){
			if($auth["namecheck_sex"] == "M" || $auth["namecheck_sex"] == "A" || $auth["namecheck_sex"] == "1"){
				$sex = "male";
			}else{
				$sex = "female";
			}
			$this->template->assign('sex',$sex);
		}
		$this->template->assign('memberIcondata',memberIconConf());

		if($email) $this->template->assign('email_arr',$email);
		if($joinform) $this->template->assign('joinform',$joinform);
		if($mtype) $this->template->assign('mtype',$mtype);
		$this->template->define(array('form_member'=>$this->skin.'/member/register_form.html'));
		$this->template->assign('register',true);
		$this->print_layout($this->template_path());
	}

	//추천인 확인
	public function recommend_confirm()
	{
		$this->load->model('membermodel');
		$member	= $this->membermodel->get_member_data_id($_GET['recomid'],'done');
		$type = ($_GET['type']=='b') ? $_GET['type'] : '';
		$callback_txt = "<script type='text/javascript'>";
		if($member['member_seq']){
			// 추천인 ok
			$callback_txt .= "parent.document.getElementById('".$type."recommend_return_txt').innerHTML='가입되어 있는 회원입니다.';";
		}else{
			$callback_txt .= "parent.document.getElementById('".$type."recommend_return_txt').innerHTML='가입되어 있지 않은 회원입니다';";
			$callback_txt .= "parent.document.getElementById('".$type."recommend').value='';";
		}
		$callback_txt .= "</script>";

		echo $callback_txt;
	}

	//예전초대하기링크주소
	public function recommend()
	{
		$this->fbinvite();
	}

	//최종초대하기페이지
	public function fbinvite()
	{
		$this->load->model('membermodel');

		$this->snssocial->facebooklogin();
		if($_GET['fbinvitestr']){
			$this->session->unset_userdata('fbinvitestr');
			$this->session->unset_userdata('fb_invite');
			$fbinvitemdata = $this->membermodel->get_member_data($_GET['fbinvitestr']);//회원정보
			if($fbinvitemdata['member_seq']) {
				$this->session->set_userdata('fbinvitestr', $_GET['fbinvitestr']);
				$this->session->set_userdata('fb_invite', $fbinvitemdata['member_seq']);

				$this->template->assign('fb_invite',$fbinvitemdata);
				$this->template->assign('recommend',$fbinvitemdata);
			}
		}else{
			if($this->session->userdata('fb_invite')){
				$fbinvitemdata = $this->membermodel->get_member_data($this->session->userdata('fb_invite'));//회원정보
				if($fbinvitemdata['member_seq']) {
					$this->session->set_userdata('fbinvitestr', $this->session->userdata('fb_invite'));
					$this->session->set_userdata('fb_invite', $fbinvitemdata['member_seq']);

					$this->template->assign('fb_invite',$fbinvitemdata);
					$this->template->assign('recommend',$fbinvitemdata);
				}else{
					$this->session->unset_userdata('fbinvitestr');
					$this->session->unset_userdata('fb_invite');
				}
			}
		}
		$this->agreement();
	}

	public function register_ok()
	{
		$this->session->unset_userdata('auth');
		if($_GET['user_name']) $this->template->assign('name',urldecode($_GET['user_name']));
		if($_GET['layermode']=='layer'){
			$this->template->define(array('tpl'=>$this->skin.'/member/_layer_register_ok.html'));
			$this->template->print_('tpl');
		}else{
			$this->print_layout($this->template_path());
		}
	}

	public function login()
	{
		$layermode = !empty($_GET['layermode']) ? $_GET['layermode'] : 'normal';

		if ( $this->config_system['domain'] && $this->config_system['domain'] == $_SERVER['HTTP_HOST'] )
			$this->template->assign("isdomain",true);//정식도메인
		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));

		$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : "";
		$return_url = preg_replace("/mobileAjaxCall=[a-z0-9_-]*/","",$return_url);
		if(! (strstr(urldecode($return_url),"/board/write") || strstr(urldecode($return_url),"goods/review_write") || strstr(urldecode($return_url),"/mypage/mygdreview_write")) ) {
			if($_GET['order_auth'] ){
				$return_url = "/mypage/order_catalog";
			}
		}

		if( preg_match('/settle/',$return_url) ){
			$mode = "settle";
		}else if( preg_match('/cart/',$return_url) ){
			$mode = "cart";
		}

		if(!$return_url){
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			//if($referer['path']=='/order/settle'){
				$return_url = $referer['path'] . ($referer['query'] ? '?'.$referer['query'] : '');
			//}
		}

		if($mode) $this->template->assign('mode',$mode);
		if($return_url) $this->template->assign('return_url',$return_url);
		$this->template->assign('login',true);

		$joinform = ($this->joinform)?$this->joinform:config_load('joinform'); 
		if(!trim($this->arrSns['key_k'])){ $joinform['use_k'] = ""; }
		$use_sns = array();
		if($joinform['use_f']) $use_sns['facebook'] = array('nm'=>'페이스북','cd'=>'fb');
		if($joinform['use_t']) $use_sns['twitter']	= array('nm'=>'트위터','cd'=>'tw');
		if($joinform['use_m'] && date("Ymd") < "20140701") $use_sns['me2day']	= array('nm'=>'미투데이','cd'=>'m2');
		if($joinform['use_c']) $use_sns['cyworld']	= array('nm'=>'싸이월드','cd'=>'cy');
		if($joinform['use_n']) $use_sns['naver']	= array('nm'=>'네이버','cd'=>'nv');
		if($joinform['use_k']) $use_sns['kakao']	= array('nm'=>'카카오','cd'=>'kk');
		if($joinform['use_d']) $use_sns['daum']		= array('nm'=>'다음','cd'=>'dm');
		$joinform['use_sns'] = $use_sns;
		if($joinform) $this->template->assign('joinform',$joinform);

		$member = config_load('member');
		$member['agreement'] = str_replace("{shopName}",$arrBasic['shopName'],$member['agreement']);
		$member['privacy'] = str_replace("{shopName}",$arrBasic['shopName'],$member['privacy']);
		$member['privacy'] = str_replace("{domain}",$arrBasic['domain'],$member['privacy']);

		//개인정보 수집-이용
		$member['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));
		$this->template->assign($member);

		$this->load->helper('cookie');
		$this->template->assign('idsavechecked',get_cookie('userlogin'));

		if($layermode=='layer'){
			$this->template->define(array('tpl'=>$this->skin.'/member/_layer_login.html'));
			$this->template->print_('tpl');
		}else{
			$this->print_layout($this->template_path());
		}
	}


	public function find()
	{
		$auth = config_load('master');
		$this->template->assign('sms_auth',$auth['sms_auth']);

		$joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		if($joinform) $this->template->assign('joinform',$joinform);

		$realname = config_load('realname');
		$this->template->assign('realnameinfo',$realname);
		$this->print_layout($this->template_path());
	}


	public function register_sns_form()
	{
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		$referer['host'] = preg_replace("/^m\./","",$referer['host']);
		$referer['host'] = preg_replace("/^www\./","",$referer['host']);
		$this->config_system['domain'] = preg_replace("/^www\./","",$this->config_system['domain']);

		$display = ( $this->_is_mobile_agent === true)?"touch":"popup";
		if(!$this->session->userdata('fbuser')) {
			$login_info = array(
			'scope'			=> $this->snssocial->userauth,
			'display'		=> $display);
			$loginUrl = $this->snssocial->facebook->getLoginUrl($login_info);

			$this->template->assign('loginUrl',$loginUrl);
		}else{
			$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
			
			if($_GET['formtype'] == 'wishadd' && $_GET['stream'] && !(array_key_exists($_GET['stream'], $fbpermissions['data'][0]) || in_array($_GET['stream'], $fbpermissions) ) ) {
					$login_info = array(
					'scope'			=> $_GET['stream'],
					'display'		=> $display);
					$permissionloginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
					$this->template->assign('permissionloginUrl',$permissionloginUrl);
			}

			if(!$fbpermissions){
					$login_info = array(
					'scope'			=> $this->snssocial->userauth,
					'display'		=> $display);
					$permissionloginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
					$this->template->assign('permissionloginUrl',$permissionloginUrl);
			}
			$this->template->assign('fbuser',$this->session->userdata('fbuser'));
		}

		if($_GET['snsreferer']) $this->session->set_userdata('snsreferer', $_GET['snsreferer']);
		if($_GET['return_url']) $this->session->set_userdata('return_url', $_GET['return_url']);
		if( $_GET['firstmallcartid']!=$this->session->userdata('session_id')) {
			$this->session->set_userdata('session_id', $_GET['firstmallcartid']);//재설정
		}

		$this->template->assign('designMode',false);
		$this->template->assign('snsrefererurl',$this->session->userdata('snsreferer'));
		$this->template->assign('snsrefererdetailurl',$this->session->userdata('return_url'));
		$this->print_layout($this->template_path());
	}

	public function popup_change_pass(){
		$this->template->assign($privacy);
		$this->print_layout($this->template_path());
	}
}