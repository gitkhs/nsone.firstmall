<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class setting extends admin_base {

	public function __construct() {
		parent::__construct();

		//$this->load->library('snssocial');
		$this->template->assign('APP_USE',   $this->__APP_USE__);
		$this->template->assign('APP_ID',    $this->__APP_ID__);
		$this->template->assign('APP_SECRET',  $this->__APP_SECRET__);
		$this->template->assign('APP_PAGE',   $this->__APP_PAGE__);

		$this->template->define(array('require_info'=>$this->skin."/setting/_require_info.html"));
		$this->template->define(array('setting_menu'=>$this->_setting_menu_template_path()));

		$setting_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		if($setting_menu=='manager_reg') $setting_menu = 'manager';
		if($setting_menu=='provider_reg') $setting_menu = 'provider';
		$this->template->assign(array('selected_setting_menu'=>$setting_menu));

	}

	public function index()
	{
		redirect('admin/setting/config');
	}

	private function _setting_menu_template_path(){
		return $this->skin."/setting/_setting_menu.html";
	}

	/* 판매환경 설정 */
	public function config()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();//

		$this->load->model('configsalemodel');

		$sc['type'] = 'mobile';
		$systemmobiles = $this->configsalemodel->lists($sc);
		$this->template->assign('systemmobiles',$systemmobiles['result']);

		/* 기능설정 위치 이동 -> SNS 및 외부연동 2014.07.18
		$sc['type'] = 'fblike';
		$systemfblike = $this->configsalemodel->lists($sc);
		$this->template->assign('systemfblike',$systemfblike['result']);
		*/

		$goodssql = "select goods_seq,goods_name  from fm_goods order by goods_seq desc limit 0,1";
		$goodsquery = $this->db->query($goodssql);
		$goodsdata = $goodsquery->row_array();
		$this->template->assign('goods_seq',$goodsdata['goods_seq']);
		$this->template->assign('goods_name',$goodsdata['goods_name']);

		$arrSystem	= ($this->config_system)?$this->config_system:config_load('system');
		$page_id_f_ar				= explode(",",$this->arrSns['page_id_f']);
		$page_name_ar			= explode(",",$this->arrSns['page_name_f']);
		$page_url_ar				= explode(",",$this->arrSns['page_url_f']);
		$page_app_link_f_ar	= explode(",",$this->arrSns['page_app_link_f']);
		foreach($page_id_f_ar as $pagen=>$v) {
			if(intval(str_replace("[","",str_replace("]","",$page_id_f_ar[$pagen])))){
				$pageloop['page_id_f']			= str_replace("[","",str_replace("]","",$page_id_f_ar[$pagen]));
				$pageloop['page_name_f']		= str_replace("[","",str_replace("]","",$page_name_ar[$pagen]));
				$pageloop['page_url_f']			= str_replace("[","",str_replace("]","",$page_url_ar[$pagen]));
				$pageloop['page_app_link_f'] = str_replace("[","",str_replace("]","",$page_app_link_f_ar[$pagen]));
				$this->arrSns['pageloop'][] = $pageloop;
			}
		}
		//pagelist session 삭제
		$this->session->unset_userdata('access_token');
		$this->session->unset_userdata('fbuser');


		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		switch($reserves['reserve_select']){
			case "year":
				$reserves['reservetitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['reserve_year']));
				break;
			case "direct":
				$reserves['reservetitle'] = $reserves['reserve_direct'].'개월';
				break;
			default:
				$reserves['reservetitle'] = '제한하지 않음';
				break;
		}

		switch($reserves['point_select']){
			case "year":
				$reserves['pointtitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['point_year']));
				break;
			case "direct":
				$reserves['pointtitle'] = $reserves['point_direct'].'개월';
				break;
			default:
				$reserves['pointtitle'] = '제한하지 않음';
				break;
		}
		$this->template->assign($reserves);

		/* 기능설정 위치 이동 -> SNS 및 외부연동 2014.07.18
		$orders = config_load('order');
		$this->template->assign('fblike_ordertype',$orders['fblike_ordertype']);
		*/
		$this->template->assign('redirect_uri_new','http://'.$_SERVER['HTTP_HOST'].'/admin/sns/config_facebook');

		$this->template->assign($this->arrSns);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign($arrSystem);
		$this->template->print_("tpl");
	}

	/* 일반 설정 */
	public function basic()
	{

		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');

		if(isset($arrBasic['businessLicense']))$arrBasic['businessLicense'] = explode('-',$arrBasic['businessLicense']);
		if(isset($arrBasic['companyPhone']))$arrBasic['companyPhone'] = explode('-',$arrBasic['companyPhone']);
		if(isset($arrBasic['companyFax']))$arrBasic['companyFax'] = explode('-',$arrBasic['companyFax']);
		if(isset($arrBasic['companyZipcode']))$arrBasic['companyZipcode'] = explode('-',$arrBasic['companyZipcode']);
		if(isset($arrBasic['companyEmail']))$arrBasic['companyEmail'] = explode('@',$arrBasic['companyEmail']);
		if(isset($arrBasic['partnershipEmail']))$arrBasic['partnershipEmail'] = explode('@',$arrBasic['partnershipEmail']);
		if(isset($arrBasic['shopBranch'])){
			if(is_array($arrBasic['shopBranch']))foreach($arrBasic['shopBranch'] as $codecd2){
				$codecd1 = substr($codecd2,0,3);
				list($groupcd1) = code_load('shopBranch',$codecd1);
				list($groupcd2) = code_load('shopBranch'.$codecd1,$codecd2);
				$ret[] = array(
					'groupcd1'=>$groupcd1['value'],
					'groupcd2'=>$groupcd2['value'],
					'codecd'=>$codecd2
				);
			}
			$arrBasic['shopBranch'] = $ret;
		}

		$reserve = ($this->reserves)?$this->reserves:config_load('reserve');
		$this->template->assign('reserve',$reserve);

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign($arrBasic);
		$this->template->print_("tpl");
	}

	/* 마케팅설정 */
	public function admin_marketing_conf()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();
		$marketing = ($this->marketing)?$this->marketing:config_load('marketing');

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign('marketing',$marketing);
		$this->template->print_("tpl");
	}

	/* SNS마케팅 설정 */
	public function snsconf()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		$this->template->assign('snsgoods', 'goods');
		$this->template->assign('snsevent', 'event');
		$this->template->assign('snsboard', 'board');
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');

		## 페이스북 좋아요 설정 2014.07.18
		$sc['type'] = 'fblike';
		$this->load->model('configsalemodel');
		$systemfblike = $this->configsalemodel->lists($sc);
		$this->template->assign('systemfblike',$systemfblike['result']);

		$this->template->define(array('tpl'=>$filePath,'sns_setting'=>$this->skin."/setting/joinform_sns.html"));

		## 좋아요 설정값
		$orders = config_load('order');
		$this->template->assign('fblike_ordertype',$orders['fblike_ordertype']);

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		switch($reserves['reserve_select']){
			case "year":
				$reserves['reservetitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['reserve_year']));
				break;
			case "direct":
				$reserves['reservetitle'] = $reserves['reserve_direct'].'개월';
				break;
			default:
				$reserves['reservetitle'] = '제한하지 않음';
				break;
		}
		switch($reserves['point_select']){
			case "year":
				$reserves['pointtitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['point_year']));
				break;
			case "direct":
				$reserves['pointtitle'] = $reserves['point_direct'].'개월';
				break;
			default:
				$reserves['pointtitle'] = '제한하지 않음';
				break;
		}
		$this->template->assign($reserves);

		## sns 로그인&가입 관련
		$this->joinform_sns();

		/* 짧은 url 설정에 따른 안내 문구 추가 leewh 2014-12-04 */
		$set_url = true;
		$set_string = "";
		if (empty($this->arrSns['shorturl_app_id']) && empty($this->arrSns['shorturl_app_key'])) {
			$set_url = false;
			$set_string = "설정이 필요";
		}

		$shorturl_test = 'http://'.$this->config_system['subDomain'].'/goods/view?no=1';
		$shorturl		= get_shortURL($shorturl_test);

		if (in_array($shorturl, array("INVALID_LOGIN","INVALID_APIKEY" ))) {
			$shorturl = "http://bit.ly/xxxxxxxx";
			if ($set_url) {
				$set_string = "제대로 설정되지 않았습니다. ‘설정’ 을 확인해 주세요";
			}
		}

		$this->template->assign('shorturl_test',$shorturl_test);
		$this->template->assign('shorturl',$shorturl);
		$this->template->assign('set_url',$set_url);
		$this->template->assign('set_string',$set_string);
		$this->template->define(array('shorturl_setting'=>$this->skin."/setting/snsconf_shorturl_setting.html"));

		$this->template->assign($arrBasic);
		$this->template->print_("tpl");
	}

	## SNS 및 외부 연동 기능 안내 : 자세히 팝업
	public function snsconf_detail(){

		$this->load->helper('admin');
		if($_GET['mode']) $snsconf_detail		= getGabiaPannel('snsconf_'.$_GET['mode']);
		echo $snsconf_detail;
	}


	/* 운영 설정 */
	public function operating(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('operating');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		$realname = config_load('realname');
		$realname['adult_chk'] = "N";
		if( $realname['useRealname'] == 'Y' && $realname['realnameId'] && $realname['realnamePwd']) $realname['adult_chk'] = "Y";
		if( $realname['useRealnamephone'] == 'Y' && $realname['realnamephoneSikey'] && $realname['realnamePhoneSipwd']) $realname['adult_chk'] = "Y";
		if( $realname['useIpin'] == 'Y' && $realname['ipinSikey'] && $realname['ipinKeyString']) $realname['adult_chk'] = "Y";
		$this->template->assign('realname',$realname);

		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$this->template->assign($arrBasic);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}
	/* PG 설정 */
	public function pg(){
		$this->admin_menu();
		$this->tempate_modules();

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");

		$result = checkPgEnvironment($this->config_system['pgCompany']);
		if(!$result[0]){
			echo "<script type='text/javascript'>$.get('../../_firstmallplus/env_pg_interface', function(data){});</script>";
		}
	}
	/* 카카오페이 설정 :: 2015-02-09 lwh */
	public function kakaopay(){
		$filePath	= $this->template_path();
		$tmp = config_load('kakaopay');
		$tmp['arrKakaoCardCompany'] = code_load('kakaoCardCompanyCode');

		// 카카오 페이 추가로 인한 임시 코드값 삽입 :: 추후 삭제 요망
		if(!$tmp['arrKakaoCardCompany']){
			$arrKakaoCode['01'] = '비씨';
			$arrKakaoCode['07'] = '현대';
			$arrKakaoCode['13'] = '수협';
			$arrKakaoCode['21'] = '광주';
			$arrKakaoCode['27'] = '해외다이너스';
			$arrKakaoCode['02'] = '국민';
			$arrKakaoCode['08'] = '롯데자사';
			$arrKakaoCode['15'] = '우리';
			$arrKakaoCode['22'] = '전북';
			$arrKakaoCode['28'] = '해외AMX';
			$arrKakaoCode['03'] = '외환';
			$arrKakaoCode['11'] = '씨티';
			$arrKakaoCode['16'] = '하나SK';
			$arrKakaoCode['23'] = '제주';
			$arrKakaoCode['29'] = '해외JCB';
			$arrKakaoCode['04'] = '삼성';
			$arrKakaoCode['11'] = '한미';
			$arrKakaoCode['18'] = '주택';
			$arrKakaoCode['25'] = '해외비자';
			$arrKakaoCode['30'] = '해외디스커버';
			$arrKakaoCode['06'] = '신한';
			$arrKakaoCode['12'] = 'NH채움';
			$arrKakaoCode['19'] = '조흥(강원';
			$arrKakaoCode['26'] = '해외마스터';
			$arrKakaoCode['34'] = '은련';
			$arrKakaoCode['06'] = '신한(구LG)';
			code_save('kakaoCardCompanyCode',$arrKakaoCode);

			$tmp['arrKakaoCardCompany'] = code_load('kakaoCardCompanyCode');
		}

		foreach($tmp['arrKakaoCardCompany'] as $k=>$v){
			$tmp['arrCardCompany'][$v['codecd']]=$v['value'];
		}

		$this->template->define(array('tpl'=>$filePath));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}
	/* KCP 설정 */
	public function kcp(){
		$filePath	= $this->template_path();
		$tmp = config_load('kcp');
		$tmp['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');
		foreach($tmp['arrKcpCardCompany'] as $k=>$v){
			$tmp['arrCardCompany'][$v['codecd']]=$v['value'];
		}
		if	($tmp['kcp_logo_val_img'])
			$tmp['kcp_logo_val_img_url']	= 'http://'.$_SERVER['HTTP_HOST'].str_replace(ROOTPATH, '/', $tmp['kcp_logo_val_img']);
		$tmp['shopName']	= $this->config_basic['shopName'];
		$this->template->define(array('tpl'=>$filePath));

		if($tmp) {
			if($this->isdemo['isdemo']){
				$tmp['mallCode'] = getstrcut($tmp['mallCode'],0,'*********');
				$tmp['merchantKey'] = getstrcut($tmp['merchantKey'],0,'******************');
			}
			$this->template->assign($tmp);
		}
		$this->template->print_("tpl");
	}
	/* LG유플러스 설정 */
	public function lg(){
		$filePath	= $this->template_path();
		$tmp = config_load('lg');
		$tmp['arrLgCardCompany'] = code_load('lgCardCompanyCode');
		foreach($tmp['arrLgCardCompany'] as $k=>$v){
			$tmp['arrCardCompany'][$v['codecd']]=$v['value'];
		}
		$this->template->define(array('tpl'=>$filePath));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}
	/* 이니시스 설정 */
	public function inicis(){
		$filePath	= $this->template_path();
		$tmp = config_load('inicis');
		$tmp['arrInicisCardCompany'] = code_load('inicisCardCompanyCode');

		$key_dir = './pg/inicis/key/'.$tmp['mallCode'];
		$arr = array(
			'keypass'=>'keypass.enc',
			'mcert'=>'mcert.pem',
			'mpriv'=>'mpriv.pem'
		);
		foreach($arr as $keyword => $keyfile){
			if(!file_exists($key_dir.'/'.$keyfile)){
				unset($arr[$keyword]);
			}
		}
		$this->template->assign($arr);

		$key_dir = './pg/inicis/key/'.$tmp['escrowMallCode'];
		$arr = array(
			'escrowKeypass'=>'keypass.enc',
			'escrowMcert'=>'mcert.pem',
			'escrowMpriv'=>'mpriv.pem'
		);
		foreach($arr as $keyword => $keyfile){
			if(!file_exists($key_dir.'/'.$keyfile)){
				unset($arr[$keyword]);
			}
		}
		$this->template->assign($arr);


		foreach($tmp['arrInicisCardCompany'] as $k=>$v){
			$tmp['arrCardCompany'][$v['codecd']]=$v['value'];
		}

		$this->template->define(array('tpl'=>$filePath));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}

	/* 이니시스 에스크로 인증마크 안내 셈플 */
	public function inics_escrow_info(){
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	/* 올엣페이 설정 */
	public function allat(){
		$filePath	= $this->template_path();
		$tmp = config_load('allat');
		/*
		$tmp['arrAllatCardCompany'] = code_load('allatCardCompanyCode');
		foreach($tmp['arrAllatCardCompany'] as $k=>$v){
			$tmp['arrCardCompany'][$v['codecd']]=$v['value'];
		}
		*/

		$this->template->define(array('tpl'=>$filePath));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}

	/* KSPAY 설정 */
	public function kspay(){
		$arrKspayCompany	= array(	'01'=>'비씨카드', '02'=>'국민카드', '03'=>'외환카드',
										'04'=>'삼성카드', '05'=>'신한카드', '08'=>'현대카드',
										'09'=>'롯데카드', '11'=>'한미은행', '12'=>'수협',
										'14'=>'우리은행', '15'=>'농협', '16'=>'제주은행',
										'17'=>'광주은행', '18'=>'전북은행', '19'=>'조흥은행',
										'23'=>'주택은행', '24'=>'하나은행', '26'=>'씨티은행',
										'25'=>'해외카드사', '99'=>'기타'	);
		$params						= config_load('kspay');
		$params['shopName']			= $this->config_basic['shopName'];
		$params['arrKspayCompany']	= $arrKspayCompany;

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign($params);
		$this->template->print_("tpl");
	}

	/* 무통장설정 */
	public function bank(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		###
		$this->load->model('usedmodel');
		$banks = $this->usedmodel->autodeposit_check();
		$this->template->assign(array('bankChk'=>$banks['chk'],'bankCount'=>$banks['count']));

		/* 계좌설정 정보 */
		$loop = config_load('bank');
		if(!$loop)$loop[0]['account'] = '';

		/* 반품배송비 입금계좌설정 정보 */
		$loop2 = config_load('bank_return');
		if(!$loop2)$loop2[0]['account'] = '';

		###
		$cid = $this->usedmodel->getEncodeBankda();
		$this->template->assign(array('cid' => $cid));

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign('loop',$loop);
		$this->template->assign('loop2',$loop2);
		$this->template->print_("tpl");
	}

	public function bank_payment(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}
		$sms_call = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=BANK&req_url=/myhg/mylist/spec/firstmall/bank/index.php";
		$sms_call = makeEncriptParam($sms_call);

		$this->template->assign('param',$sms_call);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function bank_history(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}
		$sms_call = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=BANK&req_url=/myhg/mylist/spec/firstmall/bank/index.php";
		$sms_call = makeEncriptParam($sms_call);

		$this->template->assign('param',$sms_call);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 회원설정 */
	public function member(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		if(isset($_GET['grade']) && $_GET['grade']=='modify'){
			$this->template->assign('grade',$_GET['grade']);
			$this->template->assign('seq',$_GET['seq']);
		}

		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}
	### 회원설정 - 실명확인
	public function realname(){
		$filePath	= $this->template_path();
		$realname = config_load('realname');

		$status = $realname['useIpin'] == "Y" ? "아이핀 사용" : "아이핀 미사용";

		if($status) $status .= ",";
		$status .= $realname['useRealnamephone'] == "Y" ? "휴대폰본인인증 사용" : "휴대폰본인인증 미사용";

		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');

		$this->template->assign('operating',$arrBasic['operating']);

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign('status',$status);

		//
		if($realname) {
			if($this->isdemo['isdemo']) {
				$realname['ipinSikey'] = getstrcut($realname['ipinSikey'],0,'*********');
				$realname['realnameId'] = getstrcut($realname['realnameId'],0,'*********');
				$realname['ipinKeyString'] = getstrcut($realname['ipinKeyString'],0,'*********');
				$realname['realnamePwd'] = getstrcut($realname['realnamePwd'],0,'*********');
			}
			$this->template->assign($realname);
		}
		$this->template->print_("tpl");
	}

	### 회원설정 - 이용약관
	public function agreement(){
		$filePath	= $this->template_path();
		$member = config_load('member');

		$this->template->define(array('tpl'=>$filePath));
		if($member) $this->template->assign($member);
		$this->template->print_("tpl");
	}
	### 회원설정 - 개인정보취급
	public function privacy(){
		$filePath	= $this->template_path();
		$member = config_load('member');

		$url = "http://".$_SERVER['HTTP_HOST'];

		$this->template->assign(array('member_url'=>$url."/mypage/myinfo",'privacy_url'=>$url."/service/"));
		$this->template->define(array('tpl'=>$filePath));
		if($member) $this->template->assign($member);
		$this->template->print_("tpl");
	}
	### 회원설정 - 가입
	public function joinform(){
		$filePath	= $this->template_path();

		$this->typeNames = array(
		'text'		=> '텍스트박스',
		'select'   	=> '셀렉트박스',
		'radio'		=> '여러개 중 택1',
		'checkbox'	=> '체크박스',
		'textarea'	=> '에디트박스'
		);

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('member');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		$surveyFilePath = dirname($filePath)."/_survey.htm";
		$this->template->define(array('surveyForm'=>$surveyFilePath));

		$tmp = config_load('joinform');


		//추가 조건 있는지 확인
		$qry		= "select count(*) as cnt, max(joinform_seq) as maxid from fm_joinform";
		$query		= $this->db->query($qry);
		$sub_row	= $query -> row_array();
		$this->template->assign('sub_cnt',$sub_row);

		//일반가입 정보
		$qry		= "select * from fm_joinform where join_type = 'user' order by sort_seq";
		$query		= $this->db->query($qry);
		$user_arr	= $query -> result_array();
		foreach ($user_arr as $datarow){
			$datarow['label_ctype'] = $this->typeNames[$datarow['label_type']];
			$user_sub[] = $datarow;
		}
		$this->template->assign('user_sub', $user_sub);

		//사업자가입 정보
		$qry		= "select * from fm_joinform where join_type = 'order' order by sort_seq";
		$query		= $this->db->query($qry);
		$order_arr	= $query -> result_array();
		foreach ($order_arr as $datarow){
			$datarow['label_ctype'] = $this->typeNames[$datarow['label_type']];
			$order_sub[] = $datarow;
		}
		$this->template->assign('order_sub',$order_sub);
		$this->load->model('snsmember');

		$this->snsinfo = array();
		$this->snsinfo['페이스북']= array("email"=>1,"name"=>1,"sex"=>1,"birthday"=>1,"nickname"=>0);
		$this->snsinfo['트위터'] = array("email"=>1,"name"=>1,"sex"=>0,"birthday"=>0,"nickname"=>0);
		$this->snsinfo['싸이월드'] = array("email"=>1,"name"=>1,"sex"=>1,"birthday"=>0,"nickname"=>0);
		$this->snsinfo['네이버']	= array("email"=>1,"name"=>0,"sex"=>1,"birthday"=>0,"nickname"=>1);
		$this->snsinfo['카카오']	= array("email"=>0,"name"=>0,"sex"=>0,"birthday"=>0,"nickname"=>1);
		$this->snsinfo['다음']	= array("email"=>0,"name"=>0,"sex"=>0,"birthday"=>0,"nickname"=>1);

		## sns 로그인&가입 관련
		$this->joinform_sns();

		$this->template->assign(array('snsinfo'=>$this->snsinfo));

		if( (str_replace("-","",$this->config_system['service']['setting_date']) < '20121009') ){
			$this->template->assign('service_setting_date_ck', true);
		}
		$this->template->define(array('tpl'=>$filePath,'sns_setting'=>$this->skin."/setting/joinform_sns.html"));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}

	public function joinform_sns(){
		
		$fquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'facebook' and B.status = 'done' ");
		$snsftotal = $fquery->row_array();

		$tquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'twitter' and B.status = 'done' ");
		$snsttotal = $tquery->row_array();

		$mquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'me2day'  and B.status = 'done' ");
		$snsmtotal = $mquery->row_array();
		$cquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'cyworld'  and B.status = 'done' ");
		$snsctotal = $cquery->row_array();

		$yquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'yozm'  and B.status = 'done' ");
		$snsytotal = $yquery->row_array();

		$nquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'naver'  and B.status = 'done' ");
		$snsntotal = $nquery->row_array();

		$kquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'kakao'  and B.status = 'done' ");
		$snsktotal = $kquery->row_array();

		$dquery = $this->db->query("select count(A.member_seq) as total from fm_membersns A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.rute = 'daum'  and B.status = 'done' ");
		$snsdtotal = $dquery->row_array();

		$this->arrSns['total_f']  = ($snsftotal['total']);
		$this->arrSns['total_t']  = ($snsttotal['total']);
		$this->arrSns['total_c']  = ($snsctotal['total']);
		$this->arrSns['total_m']  = ($snsmtotal['total']);
		$this->arrSns['total_y']  = ($snsytotal['total']);
		$this->arrSns['total_n']  = ($snsntotal['total']);		//네이버 가입회원
		$this->arrSns['total_k']  = ($snsktotal['total']);		//카카오 로그인
		$this->arrSns['total_d']  = ($snsdtotal['total']);		//다음 로그인

		if($this->isdemo['isdemo']) {
			$this->arrSns['key_m'] = getstrcut($this->arrSns['key_m'],0,'*********');
			$this->arrSns['key_c'] = getstrcut($this->arrSns['key_c'],0,'*********');
			$this->arrSns['secret_c'] = getstrcut($this->arrSns['secret_c'],0,'*********');
		}

		$this->template->assign(array('sns'=>$this->arrSns));

	}


	### 회원설정 - 승인/혜택
	public function approval(){
		$filePath	= $this->template_path();
		$tmp = config_load('member');

		$this->template->assign(array('sns'=>$this->arrSns));

		$this->template->define(array('tpl'=>$filePath));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}

	public function split_date($date)
	{
		return substr($date,0,4)."년 ".substr($date,5,2)."월 ".substr($date,8,2)."일";
	}

	public function calculate_date($start_month,$chg_day,$chg_term,$chk_term,$keep_term){

		$this->load->model('membermodel');
		$data = $this->membermodel->calculate_date($start_month,$chg_day,$chg_term,$chk_term,$keep_term);

		$tmp_date = array();
		foreach($data['chg_text'] as $date)
			$tmp_date[] = $this->split_date($date);
		$result['chg_text'] = implode("<br/>",$tmp_date);

		$tmp_date = array();
		foreach($data['chk_text_start'] as $k=> $date)
			$tmp_date[] = $this->split_date($date)." ~ ".$this->split_date($data['chk_text_end'][$k]);
		$result['chk_text'] = implode("<br/>",$tmp_date);

		$tmp_date = array();
		foreach($data['keep_text_start'] as $k=> $date)
			$tmp_date[] = $this->split_date($date)." ~ ".$this->split_date($data['keep_text_end'][$k]);
		$result['keep_text'] = implode("<br/>",$tmp_date);

		$result['next_grade_date'] = $this->split_date($data['next_grade_date']);

		return $result;
	}

	public function grade_ajax()
	{
		$start_month = $_GET['start_month'];
		$chg_day = $_GET['chg_day'];
		$chg_term = $_GET['chg_term'];
		$chk_term = $_GET['chk_term'];
		$keep_term = $_GET['keep_term'];

		$result = $this-> calculate_date($start_month,$chg_day,$chg_term,$chk_term,$keep_term);
		echo json_encode($result);
	}

	### 회원설정 - 등급
	public function grade(){

		$filePath	= $this->template_path();

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('grade');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		###
		$this->load->model('membermodel');
		$list = $this->membermodel->find_group_cnt_list();
		$totalcount	 = get_rows('fm_member',array('status !='=>'withdrawal'));

		###
		$grade_clone = config_load('grade_clone');

		$grade_clone['chg_text'] = "";
		$grade_clone['chk_text'] = "";
		$grade_clone['keep_text'] = "";
		$next_grade_date = "";
		$month = $grade_clone['start_month'] ? $grade_clone['start_month'] : '1';

		if($grade_clone['chg_day']){
			$result = $this->calculate_date($month,$grade_clone['chg_day'],$grade_clone['chg_term'],$grade_clone['chk_term'],$grade_clone['keep_term']);
		}

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign('result',$result);
		$this->template->assign('clone',$grade_clone);
		$this->template->assign('tot',$totalcount);
		if($list) $this->template->assign(array('loop'=>$list,'gcount'=>count($list)));
		$this->template->print_("tpl");
	}
	public function calcu_month($case, $month, $alpha, $prv=0){
		switch($case){
			case "add":
				$month = $month + $alpha;
				$month = $month - 1;
				break;
			case "chk":
				$month = $month - $alpha - 1;
				$month = $month + $prv;
				if($month<1) $month = 36 + ($month);
				break;
			case "calcu":
				$month += $alpha;
				break;
		}
		$month = $month % 12;
		if($month == 0) $month  = 12;
		return $month;
	}

	public function grade_write(){
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}
	public function grade_modify(){
		$filePath	= $this->template_path();

		### SERVICE CHECK
		if(!$_GET['group_seq']){
			$this->load->model('usedmodel');
			$result = $this->usedmodel->used_service_check('grade');
			if(!$result['type']){
				###
				$this->load->model('membermodel');
				$list = $this->membermodel->find_group_list();
				if(count($list)>3){
					$callback = "parent.formMove('grade',4);";
					openDialogAlert("더 이상 생성하실 수 없습니다.",400,140,'parent',$callback);
					exit;
				}
			}
		}

		$icons = find_icons();
		//print_r($icons);

		###
		$this->db->where('group_seq', $_GET['group_seq']);
		$query = $this->db->get('fm_member_group');
		foreach ($query->result_array() as $row){
			if(preg_match('/a:/',$row['order_sum_use'])) $row['order_sum_arr'] = unserialize($row['order_sum_use']);
			$returnArr[] = $row;
		}

		###
		$sql = "SELECT
						distinct A.*, B.*
					FROM
						fm_member_group_issuegoods A
						LEFT JOIN
						(SELECT
							g.goods_seq, g.goods_name, o.price
						FROM
							fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y') B ON A.goods_seq = B.goods_seq
					WHERE
						A.group_seq = '{$returnArr[0]['group_seq']}'";
		$query = $this->db->query($sql);
		foreach ($query->result_array() as $row){
			$limit_goods[] = $row;
		}
		if($limit_goods) $this->template->assign('issuegoods',$limit_goods);

		###
		$this->load->model('categorymodel');
		$this->db->where('group_seq', $returnArr[0]['group_seq']);
		$query = $this->db->get('fm_member_group_issuecategory');
		foreach ($query->result_array() as $row){
			$row['title'] =  $this->categorymodel->get_category_name($row['category_code']);
			$limit_cate[] = $row;
		}
		if($limit_cate) $this->template->assign('issuecategorys',$limit_cate);
		//print_r($limit_cate);

		$this->template->define(array('tpl'=>$filePath));
		if($icons) $this->template->assign('icons',$icons);
		if($returnArr) $this->template->assign('data',$returnArr[0]);
		$this->template->print_("tpl");
	}
	### 회원설정 - 로그아웃/탈퇴/재가입
	public function withdraw(){
		$filePath	= $this->template_path();
		$tmp = config_load('member');
		
		if(!$tmp['modifyPW']) $tmp['modifyPW']= "N";
		if(!$tmp['modifyPWMin']) $tmp['modifyPWMin']= "180";
		
		$this->template->define(array('tpl'=>$filePath));
		if($tmp) $this->template->assign($tmp);
		$this->template->print_("tpl");
	}

	/* 주문설정 */
	public function order(){
		$this->admin_menu();
		$this->tempate_modules();
		$orders = config_load('order');

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('buy_confirm');
		if(!$result['type']){
			$this->template->assign('buy_confirm_service_limit','Y');
			//$orders['buy_confirm_use'] = 0;
		}
		$result = $this->usedmodel->used_service_check('multi_shipping');
		if(!$result['type']){
			$this->template->assign('multi_shipping_service_limit','Y');
		}

		###
		$domain = str_replace("www.","",$_SERVER["SERVER_NAME"]);
		$pattern = array(".com",".net",".org",".biz",".info",".name",".kr",".co.kr",".or.kr",".pe.kr",".asia",".me",".cc",".cn",".tv",".in",".tw",".mobi");
		preg_match('/[a-z\d\-]+('.implode("|",$pattern).')/i',$domain, $match);
		$resDomain = $match[0];
		$this->template->assign('domain',$resDomain);

		if($this->config_system['hiworks_request']=="Y"){
			if(isset($this->config_system['webmail_admin_id']) && isset($this->config_system['webmail_domain'])){
				$this->template->assign('webmail_admin_id', $this->config_system['webmail_admin_id']);
				$this->template->assign('webmail_domain', $this->config_system['webmail_domain']);
			}else{
				$this->load->helper("environment");
				callSetEnvironment(false);
			}
		}

		$qry = "select * from fm_return_reason where return_type='coupon' order by idx asc";
		$query = $this->db->query($qry);
		$reasoncouponLoop = $query -> result_array();
		$this->template->assign('reasoncouponLoop',$reasoncouponLoop);

		$qry = "select * from fm_return_reason where return_type!='coupon' order by idx asc";
		$query = $this->db->query($qry);
		$reasonLoop = $query -> result_array();
		$this->template->assign('reasonLoop',$reasonLoop);

		$this->template->define(array('order_process'=>$this->skin."/setting/_order_process.html"));

		$this->template->assign('hiworks_request', $this->config_system['hiworks_request']);
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign($orders);
		$this->template->print_("tpl");
	}

	/* 주문설정 */
	public function sale(){
		$this->admin_menu();
		$this->tempate_modules();
		$orders = config_load('order');

		###
		$domain = str_replace("www.","",$_SERVER["SERVER_NAME"]);
		$pattern = array(".com",".net",".org",".biz",".info",".name",".kr",".co.kr",".or.kr",".pe.kr",".asia",".me",".cc",".cn",".tv",".in",".tw",".mobi");
		preg_match('/[a-z\d\-]+('.implode("|",$pattern).')/i',$domain, $match);
		$resDomain = $match[0];
		$this->template->assign('domain',$resDomain);

		if($this->isdemo['isdemo']){
			$this->config_system['webmail_domain'] = getstrcut($this->config_system['webmail_domain'],0,'*********');
			$this->config_system['webmail_admin_id'] = getstrcut($this->config_system['webmail_admin_id'],0,'*********');
			$this->config_system['webmail_key'] = getstrcut($this->config_system['webmail_key'],0,'*********');
		}

		$this->template->assign('webmail_admin_id', $this->config_system['webmail_admin_id']);
		$this->template->assign('webmail_domain', $this->config_system['webmail_domain']);
		$this->template->assign('webmail_key', $this->config_system['webmail_key']);

		$this->template->assign('hiworks_request', $this->config_system['hiworks_request']);
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));

		$this->template->assign($orders);
		$this->template->print_("tpl");
	}

	/* 주문설정 */
	public function reserve(){
		$this->admin_menu();
		$this->tempate_modules();
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');

		$filePath	= $this->template_path();
		if(!$reserves['point_use']) $reserves['point_use'] = "N";
		if(!$reserves['cash_use']) $reserves['cash_use'] = "N";
		if(!$reserves['default_point_type']) $reserves['default_point_type'] = "per";
		//if(!$reserves['save_step']) $reserves['save_step'] = "75";
		if($reserves['reserve_year']=='') $reserves['reserve_year'] = 0;
		if($reserves['point_year']=='') $reserves['point_year'] = 0;
		if(!$reserves['reserve_direct']) $reserves['reserve_direct'] = "24";
		if(!$reserves['point_direct']) $reserves['point_direct'] = "24";

		$orders = config_load('order');

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign($reserves);
		$this->template->assign($orders);
		$this->template->print_("tpl");


	}

	/* 배송설정 */
	public function shipping(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('shipping');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		## 택배업무자동화서비스 세팅값
		if($this->config_system['invoice_use'])
		{
			$this->config_invoice = config_load('invoice');
			$this->template->assign("config_invoice",$this->config_invoice);
		}

		## 보내는 곳 주소 및 반송 주소
		$this->config_shipping = config_load('shipping');
		$this->template->assign("config_shipping",$this->config_shipping);

		$arr = array('delivery','quick','direct');
		foreach($arr as $code){
			$scode = "shipping".$code;
			$data = config_load($scode);
		 	if( isset($data['deliveryCompanyCode']) ){
				foreach( $data['deliveryCompanyCode'] as $deliveryCompanyCode ){
					$tmp = config_load('delivery_url',$deliveryCompanyCode);
					$deliveryCompany[] = $tmp[$deliveryCompanyCode]['company'];
				}
			}else{
				unset($deliveryCompany);
			}
			if(isset($deliveryCompany) && $deliveryCompany){
				$result['deliveryCompany'] = implode(',',$deliveryCompany);
			}else{
				$result['deliveryCompany'] = '';
			}

			$result['summary'] = $data['summary'];

			if(isset($data['deliveryCostPolicy'])){
				switch($data['deliveryCostPolicy']){
					case "free" : $result['price'] = "무료"; break;
					case "pay" : $result['price'] = "유료 " . number_format($data['payDeliveryCost'])." 원"; break;
					case "ifpay" : $result['price'] = "조건부 " . number_format($data['ifpayDeliveryCost']) ."원"; break;
				}
			}else{
				$result['price'] = "-";
			}
			$result['useYnMsg'] = ($data['useYn']=='y') ? "사용" : "미사용";
			if( isset($data['sigungu']) && $data['sigungu'][0]  ) $result['addpriceMsg'] = "설정함";
			else if( $code == 'delivery')  $result['addpriceMsg'] = "미설정";
			else $result['addpriceMsg'] = "-";
			$loop[$code] = $result;

			if($data['useYn'] == 'y'){
				$cnt++;
			}

			$data_providershipping[$code] = $data;
		}

		$data_providershipping[delivery_cnt] = $cnt;

		$this->template->assign("data_providershipping",$data_providershipping);


		$arr = $result = "";
		$codes = code_load('internationalShipping');

		foreach($codes as $code){
			$arr = config_load('internationalShipping'.$code['codecd']);

			if($arr['company']){
				if($arr['useYn']=='y')$arr['useYnMsg'] = "사용";
				else $arr['useYnMsg'] = "미사용";
				$arr['companyMsg'] = str_replace('선불 > ','',$code['value']);
				$result[] = $arr;
			}
		}

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign("loop",$loop);
		$this->template->assign("internationalShipping",$result);
		$this->template->print_("tpl");
	}
	/* 국내 배송 수정 */
	public function shipping_modify(){

		$this->load->helper('shipping');
		$this->load->model('goodsmodel');
		$this->load->model('brandmodel');
		$this->load->model('categorymodel');

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('multi_shipping');
		if(!$result['type']){
			$this->template->assign('multi_shipping_service_limit','Y');
		}

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		if( isset($_GET['code']) ){
			$code = $_GET['code'];
			if($_GET['code']=='add_delivery'){
				$code = "delivery";
			}

			$data = config_load('shipping'.$code);

			if( $data['issueGoods'] ){
				$data['data_issue_goods'] = $this->goodsmodel->get_goods_list($data['issueGoods'],'thumbView');
			}

			if( $data['exceptIssueGoods'] ){
				$data['data_except_goods'] = $this->goodsmodel->get_goods_list($data['exceptIssueGoods'],'thumbView');
			}

			if( $data['issueBrandCode'] ){
				foreach($data['issueBrandCode'] as $code){
					$data['data_issue_brand'][$code]['name'] = $this->brandmodel -> get_brand_name($code);
				}
			}

			if( $data['issueCategoryCode'] ){
				foreach($data['issueCategoryCode'] as $code){
					$data['data_issue_category'][$code]['name'] = $this->categorymodel -> get_category_name($code);
				}
			}

			$this->template->assign($data);
		}
		$this->template->print_("tpl");
	}
	/* 해외 배송 추가/수정 */
	public function international_shipping(){

		$this->load->model('categorymodel');
		$code = $_GET['code'];
		$filePath	= $this->template_path();
		if($code != 'regist'){
			$data = config_load('internationalShipping'.$code);
			$rownum = count($data['region']);
			$rp = 0;
			foreach($data['deliveryCost'] as $k => $deliveryCost){
				$num = $k + 1;
				$data['arrDeliveryCost'][$rp][] = $deliveryCost;
				if($num%$rownum == 0) $rp += 1;
			}
			if($data['exceptCategory']){
				foreach($data['exceptCategory'] as $k => $exceptCategory){
					$data['exceptCategoryName'][] = $this->categorymodel->get_category_name($exceptCategory);
				}
			}
		}
		if(isset($data)){
			$this->template->assign($data);
		}

		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	/* 보안 설정 */
	public function protect(){
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('ssl');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('setting_protect_view');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$protectIp = !empty($this->config_system['protectIp']) ? $this->config_system['protectIp'] : "";
		$protectIp = $protectIp ? explode("\n",$protectIp) : array();

		$this->template->assign(array(
			'protectIp'=>$protectIp,
			'ssl'=>$this->ssl
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}


	/* 관리자 */
	public function manager(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();


		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('manager');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}


		$this->load->model('membermodel');

		### SEARCH
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'manager_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

		###
		$data = $this->membermodel->admin_manager_list($sc);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_manager');
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			if ($datarow['lastlogin_date']=="0000-00-00 00:00:00") {
				$datarow['lastlogin_date'] = "";
			}
			$dataloop[] = $datarow;
		}
		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );

		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('group_arr',$group_arr);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$auth = $this->authmodel->manager_limit_act('setting_manager_act');
		if(!$auth){
			pageBack($this->auth_msg);
			exit;
		}


		$auto_logout = config_load('autoLogout');
		$this->template->assign($auto_logout);

		$this->template->assign('use_manager_cnt',$data['count']);

		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	/* 관리자 휴대폰 인증 */
	public function manager_hp_auth(){

		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		if($this->managerInfo['manager_yn'] != "Y"){
			echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
			exit;
		}

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('manager');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}
		
		$auth = config_load('manager_auth');
		$this->template->assign($auth);

		$this->load->model('membermodel');

		### SEARCH
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'manager_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

		###
		$data = $this->membermodel->admin_manager_auth_list($sc);

		if(isset($data)) $this->template->assign('loop',$data);

		$limit	= commonCountSMS();
		
		$this->template->assign('smsCnt',$limit);

		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function manager_reg(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		$noti_acount_priod = config_load('noti_count');
		if(!$noti_acount_priod['order']) $noti_acount_priod['order'] = "6개월";
		if(!$noti_acount_priod['board']) $noti_acount_priod['board'] = "6개월";

		$auto_logout = config_load('autoLogout');
		$this->template->assign('autoLogout', $auto_logout);

		$icons = find_icons('manager');

		if(isset($_GET['manager_seq'])){
			$this->db->where('manager_seq', $_GET['manager_seq']);
			$query = $this->db->get('fm_manager');
			$data = $query->result_array();
			
			if($data[0]['limit_ip']){
				$limit_row = explode("|", $data[0]['limit_ip']);
				$count = count($limit_row)-1;
				for($i=0; $i<$count; $i++){
					$limit_ip[] = explode(".", $limit_row[$i]);
				}
				$data[0]['limit_ip'] = $limit_ip;
			}

			if ($data['0']['lastlogin_date'] == "0000-00-00 00:00:00") {
				$data['0']['lastlogin_date'] = "";
			}

			$auth_arr = explode("||",$data[0]['manager_auth']);
			foreach($auth_arr as $k){
				$tmp_arr = explode("=",$k);
				$auth[$tmp_arr[0]] = $tmp_arr[1];
			}

			$this->template->assign('auth',$auth);
			$this->template->assign($data[0]);

			###
			$auth_list = config_load("admin_auth");
			$auth_arr = $this->authmodel->manager_auth_arr();

			foreach($auth_list as $k => $v){
				if($auth_arr[$k]=='Y') $loop[] = $v;
			}
			$this->template->assign('auth_loop',$loop);

			// 확인코드 추출
			$this->load->model('membermodel');
			$param['provider_seq']	= '1';
			$param['manager_id']	= $data[0]['manager_id'];
			$certify				= $this->membermodel->get_certify_manager($param);
			$this->template->assign('certify',$certify);

			// 회원정보 다운로드 로그
			$query = $this->db->from('fm_log_member_download')->select("manager_log")->where('manager_seq', $_GET['manager_seq'])->order_by('seq', 'DESC')->get();
			$down_data = $query->result_array();
			$log_down_data = array();
			foreach($down_data as $key => $val) {
				$log_down_data[] = $down_data[$key]['manager_log'];
			}
			$log_down_data = join("<br/>",$log_down_data);
			$this->template->assign('log_member_download',$log_down_data);
		}

		###
		$auth_limit = $this->authmodel->manager_limit_act('setting_manager_act');		
		$this->template->assign('auth_limit',$auth_limit);
		
		### board
		$this->load->helper(array('board'));
		$this->load->model('Boardmanager'); 
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		boardalllist($bsc);//게시판전체리스트

		if($icons) $this->template->assign('icons',$icons);
		$this->template->assign('ip',$_SERVER['REMOTE_ADDR']);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	/* 관리자 계정 추가 신청 */
	public function manager_payment(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}

		$req_type = 'MANAGER';
		$param = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=".$req_type."&req_url=/myshop";
		$param = makeEncriptParam($param);

		$this->template->assign('param',$param);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function hiworks_request(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}
		$sms_call = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=HIWORKS&req_url=/myhg/mylist/spec/firstmall/hiworks/index.php";
		$sms_call = makeEncriptParam($sms_call);

		$this->template->assign('param',$sms_call);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 상품 설정 */
	public function goods(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		$cfg_goods = config_load("goods");

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('stock_history');
		if(!$result['type']){
			$this->template->assign('stock_history_limit','Y');

			if($cfg_goods['stock_history_use']){
				$cfg_goods['stock_history_use'] = 0;
				config_save('goods',array('stock_history_use'=>0));
			}
		}

		$this->template->assign('optcoloraddruse','1');//색상/주소 사용여부

		$surveyFilePath = dirname($this->template_path())."/_survey.htm";
		$this->template->define(array('surveyForm'=>$surveyFilePath));

		//상품추가양식 정보
		$this->load->helper("goods");
		$gdtypearray = array("goodsaddinfo","goodsoption","goodssuboption");
		$goodscodesettingview='';
		foreach($gdtypearray as $gdtype){
			unset($goodscode);
			$qry = "select * from fm_goods_code_form  where label_type ='".$gdtype."'  order by sort_seq";
			$query = $this->db->query($qry);
			$user_arr = $query -> result_array();
			foreach ($user_arr as $datarow){
				$datarow['label_view'] = get_labelitem_type($datarow,'','setting');
				if($datarow['codesetting']==1){
					$goodscodesettingview .= $datarow['label_title'].' + ';
					$datarow['label_codesetting'] = ' checked ';
				}else{
					$datarow['label_codesetting'] = '';
				}
				$goodscode[] = $datarow;
			}
			$this->template->assign($gdtype.'loop', $goodscode);
		}
		$this->template->assign('goodscodesettingview',substr($goodscodesettingview,0,strlen($goodscodesettingview)-3));
		$qry = "select codeform_seq as maxseq from fm_goods_code_form order by codeform_seq desc limit 1";
		$query = $this->db->query($qry);
		$maxseq = $query -> result_array();
		$this->template->assign('maxseq',$maxseq[0]['maxseq']);

		### PAGE & DATA
		$gdquery = "select count(*) cnt from fm_goods where goods_type = 'goods' ";
		$gdquery = $this->db->query($gdquery);
		$gddata = $gdquery->row_array();
		$this->template->assign('totalcount',$gddata['cnt']);
		$this->template->assign('totalpage',@ceil($gddata['cnt']/500));

		$this->template->assign('cfg_goods',$cfg_goods);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}


	/* 동영상 설정 */
	public function video(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		$cfg_goods = config_load("goods");

		if($this->isdemo['isdemo']) {
			$cfg_goods['ucc_id'] = getstrcut($cfg_goods['ucc_id'],0,'*********');
			$cfg_goods['ucc_domain'] = getstrcut($cfg_goods['ucc_domain'],0,'*********');
			$cfg_goods['ucc_key'] = getstrcut($cfg_goods['ucc_key'],0,'*********');
		}
		$this->template->assign('cfg_goods',$cfg_goods);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function shipping_international(){

		if( isset($_GET['seq']) ){
			$sql = "select * from fm_provider_shipping where provider_seq = '{$_GET['seq']}'";
			$query = $this->db->query($sql);
			$temp = $query->result_array();
			$international = unserialize($temp[0]['international']);

			if($international['deliveryCost']) $international['deliveryCost'] = explode("|",$international['deliveryCost']);
			if($international['exceptCategory']) $international['exceptCategory'] = explode("|",$international['exceptCategory']);
			if($international['goodsWeight']) $international['goodsWeight'] = explode("|",$international['goodsWeight']);
			if($international['region']) $international['region'] = explode("|",$international['region']);
			if($international['regionSummary']) $international['regionSummary'] = explode("|",$international['regionSummary']);
			if($international['arrDeliveryCost']) $international['arrDeliveryCost'] = explode("|",$international['arrDeliveryCost']);
			$data = $international;
			if($data['exceptCategory']){
				$this->load->model('categorymodel');
				foreach($data['exceptCategory'] as $k => $exceptCategory){
					$data['exceptCategoryName'][] = $this->categorymodel->get_category_name($exceptCategory);
				}
			}
			$this->template->assign($data);
		}

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function popup_image(){
		$file_path	= $this->template_path();
		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function watermark_setting(){
		$config_watermark = config_load('watermark');

		if($config_watermark['watermark_position']!=''){
			$config_watermark['watermark_position'] = explode('|',$config_watermark['watermark_position']);
		}

		$file_path	= $this->template_path();
		$this->template->assign(array('config_watermark'=>$config_watermark));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	/* 등급별 할인율 설정 */
	public function member_sale(){

		$this->load->model('membermodel');
		$list = $this->membermodel->member_sale_group_list();

		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'sale_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'asc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= '10';


		//할인율 MASTER 정보
		$qry = "select * from fm_member_group_sale";
		$qry .=" order by {$sc['orderby']} {$sc['sort']}";
		$sale_list = select_page($sc['perpage'],$sc['page'],10,$qry,'');

		$this->template->assign('page',$sale_list['page']);

		foreach ($sale_list["record"] as $datarow){

			foreach($list as $group){

				$qry = "select * from fm_member_group_sale_detail where sale_seq = '".$datarow["sale_seq"]."' and group_seq = '".$group["group_seq"]."'";
				$query = $this->db->query($qry);
				$detail_list = $query -> result_array();

				foreach($detail_list as $subdatarow){
					if($subdatarow["sale_use"] == "Y"){
						$subdata[$group["group_seq"]]["sale_use"]				= $subdatarow["sale_limit_price"]."원 이상 구매";
					}else{
						$subdata[$group["group_seq"]]["sale_use"]				= "조건없음";
					}

					$subdata[$group["group_seq"]]["sale_price"]				= $subdatarow["sale_price"];

					if($subdatarow["sale_price_type"] == "WON"){
						$subdata[$group["group_seq"]]["sale_price_type"]		= "원 할인";
					}else{
						$subdata[$group["group_seq"]]["sale_price_type"]		= "% 할인";
					}

					$subdata[$group["group_seq"]]["sale_option_price"] 		= $subdatarow["sale_option_price"];

					if($subdatarow["sale_option_price_type"] == "WON"){
						$subdata[$group["group_seq"]]["sale_option_price_type"]		= "원 할인";
					}else{
						$subdata[$group["group_seq"]]["sale_option_price_type"]		= "% 할인";
					}

					$subdata[$group["group_seq"]]["point_use"]				= $subdatarow["point_use"];

					if($subdatarow["point_use"] == "Y"){
						$subdata[$group["group_seq"]]["point_use"]				= $subdatarow["point_limit_price"]."원 이상 구매";
					}else{
						$subdata[$group["group_seq"]]["point_use"]				= "조건없음";
					}

					$subdata[$group["group_seq"]]["point_price"]			= $subdatarow["point_price"];

					if($subdatarow["point_price_type"] == "WON"){
						$subdata[$group["group_seq"]]["point_price_type"]		= "원 적립";
					}else{
						$subdata[$group["group_seq"]]["point_price_type"]		= "% 적립";
					}


					$subdata[$group["group_seq"]]["reserve_price"]			= $subdatarow["reserve_price"];

					if($subdatarow["reserve_price_type"] == "WON"){
						$subdata[$group["group_seq"]]["reserve_price_type"]		= "원 적립";
					}else{
						$subdata[$group["group_seq"]]["reserve_price_type"]		= "% 적립";
					}

					$subdata[$group["group_seq"]]["reserve_select"]			= $subdatarow["reserve_select"];
					$subdata[$group["group_seq"]]["reserve_year"]			= $subdatarow["reserve_year"];
					$subdata[$group["group_seq"]]["reserve_direct"]			= $subdatarow["reserve_direct"];
					$subdata[$group["group_seq"]]["point_select"]			= $subdatarow["point_select"];
					$subdata[$group["group_seq"]]["point_year"]				= $subdatarow["point_year"];
					$subdata[$group["group_seq"]]["point_direct"]			= $subdatarow["point_direct"];
				}


			}

			$data[$datarow["sale_seq"]] = $subdata;
			$data[$datarow["sale_seq"]]["sale_seq"] = $datarow["sale_seq"];
			$data[$datarow["sale_seq"]]["sale_title"] = $datarow["sale_title"];
			$data[$datarow["sale_seq"]]["_no"] = $datarow["_no"];
			$data[$datarow["sale_seq"]]["totalcount"] = $sale_list['page']['totalcount'];
			$data[$datarow["sale_seq"]]["loop"] = $list;
			$data[$datarow["sale_seq"]]["gcount"] = count($list);
			unset($limit_goods);
			unset($limit_cate);
			###
			$sql = "SELECT
							distinct A.*, B.*
						FROM
							fm_member_group_issuegoods A
							LEFT JOIN
							(SELECT
								g.goods_seq, g.goods_name, o.price
							FROM
								fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y') B ON A.goods_seq = B.goods_seq
						WHERE
							A.sale_seq = '{$datarow["sale_seq"]}'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$limit_goods[] = $row;
			}

			$data[$datarow["sale_seq"]]["issuegoods"] = $limit_goods;


			###
			$this->load->model('categorymodel');
			$this->db->where('sale_seq', $datarow["sale_seq"]);
			$query = $this->db->get('fm_member_group_issuecategory');
			foreach ($query->result_array() as $row){
				$row['title'] =  $this->categorymodel->get_category_name($row['category_code']);
				$limit_cate[] = $row;
			}

			$data[$datarow["sale_seq"]]["issuecategorys"] = $limit_cate;


		}

		$service_code = $this->config_system['service']['code'];
		if($service_code == "P_ADVA" || $service_code == "P_EXPA"){
			$default_member_sale_cnt = 5;
		}else if($service_code == "P_FAMM" || $service_code == "P_PREM" || $service_code == "P_STOR"){
			$default_member_sale_cnt = 3;
		}else{
			$default_member_sale_cnt = 1;
		}

		$this->config_system['service']['max_member_sale_cnt'] += $default_member_sale_cnt;

		$this->template->assign(array('use_member_sale_cnt'=>$sale_list['page']['totalcount']));
		$this->template->assign(array('service_code'=>$service_code));
		$this->template->assign(array('config_system'=>$this->config_system));
		$this->template->assign(array('default_member_sale_cnt'=>$default_member_sale_cnt));
		$this->template->assign(array('data'=>$data));
		$this->template->assign(array('loop'=>$list,'gcount'=>count($list)));

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}


	public function member_sale_write(){

		$this->load->model('membermodel');
		$list = $this->membermodel->member_sale_group_list();


		if($_GET["sale_seq"]){
			//일반가입 정보
			$qry = "select * from fm_member_group_sale where sale_seq = '".$_GET["sale_seq"]."'";
			$query = $this->db->query($qry);
			$sale_list = $query -> result_array();
			$this->template->assign(array('sale_title'=>$sale_list[0]["sale_title"]));
			$this->template->assign(array('defualt_yn'=>$sale_list[0]["defualt_yn"]));

			foreach ($sale_list as $datarow){

				foreach($list as $group){

					$qry = "select * from fm_member_group_sale_detail where sale_seq = '".$datarow["sale_seq"]."' and group_seq = '".$group["group_seq"]."'";
					$query = $this->db->query($qry);
					$detail_list = $query -> result_array();

					foreach($detail_list as $subdatarow){

						$subdata[$group["group_seq"]]["sale_use"]				= $subdatarow["sale_use"];
						$subdata[$group["group_seq"]]["sale_limit_price"]		= $subdatarow["sale_limit_price"];
						$subdata[$group["group_seq"]]["sale_price"]				= $subdatarow["sale_price"];

						$subdata[$group["group_seq"]]["sale_price_type"]		= $subdatarow["sale_price_type"];
						$subdata[$group["group_seq"]]["sale_option_price"] 		= $subdatarow["sale_option_price"];

						$subdata[$group["group_seq"]]["sale_option_price_type"]	= $subdatarow["sale_option_price_type"];
						$subdata[$group["group_seq"]]["point_use"]				= $subdatarow["point_use"];

						$subdata[$group["group_seq"]]["point_use"]				= $subdatarow["point_use"];
						$subdata[$group["group_seq"]]["point_limit_price"]		= $subdatarow["point_limit_price"];
						$subdata[$group["group_seq"]]["point_price"]			= $subdatarow["point_price"];

						$subdata[$group["group_seq"]]["point_price_type"]		= $subdatarow["point_price_type"];

						$subdata[$group["group_seq"]]["reserve_price"]			= $subdatarow["reserve_price"];

						$subdata[$group["group_seq"]]["reserve_price_type"]		= $subdatarow["reserve_price_type"];
						$subdata[$group["group_seq"]]["reserve_select"]			= $subdatarow["reserve_select"];
						$subdata[$group["group_seq"]]["reserve_year"]			= $subdatarow["reserve_year"];
						$subdata[$group["group_seq"]]["reserve_direct"]			= $subdatarow["reserve_direct"];
						$subdata[$group["group_seq"]]["point_select"]			= $subdatarow["point_select"];
						$subdata[$group["group_seq"]]["point_year"]				= $subdatarow["point_year"];
						$subdata[$group["group_seq"]]["point_direct"]			= $subdatarow["point_direct"];
					}


				}

				$data[$datarow["sale_seq"]] = $subdata;
				$data[$datarow["sale_seq"]]["sale_seq"] = $datarow["sale_seq"];
				$data[$datarow["sale_seq"]]["sale_title"] = $datarow["sale_title"];
				$data[$datarow["sale_seq"]]["loop"] = $list;
				$data[$datarow["sale_seq"]]["gcount"] = count($list);
				unset($limit_goods);
				unset($limit_cate);
				###
				$sql = "SELECT
								distinct A.*, B.*
							FROM
								fm_member_group_issuegoods A
								LEFT JOIN
								(SELECT
									g.goods_seq, g.goods_name, o.price
								FROM
									fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y') B ON A.goods_seq = B.goods_seq
							WHERE
								A.sale_seq = '{$datarow["sale_seq"]}'";
				$query = $this->db->query($sql);
				foreach ($query->result_array() as $row){
					$limit_goods[] = $row;
				}

				$data[$datarow["sale_seq"]]["issuegoods"] = $limit_goods;


				###
				$this->load->model('categorymodel');
				$this->db->where('sale_seq', $datarow["sale_seq"]);
				$query = $this->db->get('fm_member_group_issuecategory');
				foreach ($query->result_array() as $row){
					$row['title'] =  $this->categorymodel->get_category_name($row['category_code']);
					$limit_cate[] = $row;
				}


				$data[$datarow["sale_seq"]]["issuecategorys"] = $limit_cate;


			}

		}
		$this->template->assign(array('data'=>$data));
		$this->template->assign(array('loop'=>$list,'gcount'=>count($list)));

		$reserve = config_load('reserve');
		$this->template->assign(array('reserve'=>$reserve));




		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}


	function member_sale_delete(){

		$this->tempate_modules();
		$filePath	= $this->template_path();

		$this->load->model('membermodel');

		$where_str = "sale_seq <> '".$_GET["sale_seq"]."'";
		$sale_list = $this->membermodel->get_member_sale($where_str);

		$where_str = "sale_seq = '".$_GET["sale_seq"]."'";
		$sale_title = $this->membermodel->get_member_sale($where_str, "sale_title");

		$this->template->assign(array('list'=>$sale_list));
		$this->template->assign(array('sale_title'=>$sale_title[0]["sale_title"]));

		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");

	}

	function search(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();

		$cfg_tmp = config_load("search");

		$cfg_search['popular_search'] = $cfg_tmp['popular_search']?$cfg_tmp['popular_search']:'n';
		$cfg_search['popular_search_limit_day'] = $cfg_tmp['popular_search_limit_day']?$cfg_tmp['popular_search_limit_day']:30;
		$cfg_search['popular_search_recomm_limit_day'] = $cfg_tmp['popular_search_recomm_limit_day']?$cfg_tmp['popular_search_recomm_limit_day']:30;

		$cfg_search['auto_search'] = $cfg_tmp['auto_search']?$cfg_tmp['auto_search']:'n';
		$cfg_search['auto_search_limit_day'] = $cfg_tmp['auto_search_limit_day']?$cfg_tmp['auto_search_limit_day']:30;
		$cfg_search['auto_search_recomm_limit_day'] = $cfg_tmp['auto_search_recomm_limit_day']?$cfg_tmp['auto_search_recomm_limit_day']:30;

		$cfg_search_word['main'] = "메인 페이지 및 그 외 페이지";
		$cfg_search_word['good_view'] = "상품상세 페이지";
		$cfg_search_word['goods_search'] = "검색결과 페이지";
		$cfg_search_word['category'] = "카테고리 페이지";
		$cfg_search_word['brand'] = "브랜드 페이지";
		$cfg_search_word['location'] = "지역 페이지";
		$cfg_search_word['board'] = "게시판 페이지";
		$cfg_search_word['mypage'] = "MY 페이지";
		$cfg_search_word['event'] = "이벤트(사은품) 페이지";

		// 입점사버전 일 경우
		if(in_array($this->config_system['service']['code'],array('P_ADVA','P_ADVL'))){
			$cfg_search_word['mshop'] = "미니샵 페이지";
		}

		$query = "select * from fm_search_word";
		$query = $this->db->query($query);
		foreach($query->result_array() as $data){
			$result[$data['page']][] = $data;
		}

		// 우편번호 설정
		$cfg_zipcode = config_load("zipcode");

		$this->template->assign('cfg_zipcode',$cfg_zipcode);
		$this->template->assign('cfg_search',$cfg_search);
		$this->template->assign('cfg_search_word',$cfg_search_word);
		$this->template->assign('result',$result);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}


	/* 등급별 구매혜택 세트 추가 신청 */
	public function member_payment(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}

		$req_type = 'MEMBER_SALE';
		$param = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=".$req_type."&req_url=/myshop&payment_type=".$_GET['type']."&totalCnt=".$_GET['totalCnt'];
		$param = makeEncriptParam($param);

		$this->template->assign('param',$param);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}



	/* 등급별 구매혜택 결제 로그 */
	public function member_account_log(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}

		$req_type = 'MEMBER_SALE_ACCOUNT';
		$param = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=".$req_type."&req_url=/myshop";
		$param = makeEncriptParam($param);

		$this->template->assign('param',$param);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 확인코드 중복체크 및 유효성 체크
	public function chk_certify_code($cerfify_code = ''){

		$return	= 'ok';

		if	($_GET['certify_code'])
			$certify_code	= trim($_GET['certify_code']);

		if	($_GET['certify_seq'])
			$param['out_seq']	= trim($_GET['certify_seq']);

		if		(!$certify_code)											$return	= 'error_1';
		elseif	(strlen($certify_code) < 6 || strlen($certify_code) > 16)	$return	= 'error_2';
		elseif	(preg_match('/[^0-9a-zA-Z]/', $certify_code))				$return	= 'error_3';

		// providermodel --> membermodel
		$this->load->model('membermodel');
		$param['certify_code']	= $certify_code;
		$certify				= $this->membermodel->get_certify_manager($param);
		if	($certify ) {
			$return	= 'duple';
		}

		if	($_GET['certify_code'])	echo $return;
		else						return $return;
	}

	public function default_add_delivery(){

		$query = "select * from fm_default_addshipping";

		$query = $this->db->query($query);
		$result = $query->result_array();

		$this->template->assign('loop',$result);
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function pop_manager_member_log(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function manager_member_downloadlist() {
		### SEARCH
		$sc						= $_POST;
		$sc['search_text']		= ($sc['search_text'] == '관리자 아이디') ? '':$sc['search_text'];
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):10;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;

		$data = array();
		$dataloop = array();

		$sqlWhereClause = "";
		if (!empty($sc['sdate']) && !empty($sc['edate'])) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sqlWhereClause.=" AND A.reg_date BETWEEN '{$start_date}' AND '{$end_date}' ";
		}

		if (!empty($sc['search_text'])) {
			$sqlWhereClause .= " AND A.manager_id LIKE '".$sc['search_text']."%'";
		}

		$sql = "SELECT 
				A.*
			FROM
				fm_log_member_download A
			WHERE 1 ".$sqlWhereClause;

		$sql .=" ORDER BY A.seq desc";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$query = $this->db->query($sql);
		$data['count'] = $query->num_rows();
		$data['html'] = "";

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);

		if ($data['result']) {
			foreach($data['result'] as $datarow){
				$str_down_count = "";
				if ($datarow['down_count']) {
					$str_down_count = sprintf("(%s명)",number_format($datarow['down_count']));
				}
				$datarow['content'] = sprintf("회원정보%s를 다운로드 하였습니다. (%s) %s", $str_down_count, $datarow['ip'], $datarow['file_name']);
				$data['html'] .= sprintf("<tr><td class='its-td-align center'>%s(%s)</td><td class='its-td-align center'>%s</td><td class='its-td-align center'>%s</td></tr>", get_manager_name($datarow['manager_seq']), $datarow['manager_id'], $datarow['content'], $datarow['reg_date']);
			}
		} else {
			$data['html'] .= sprintf("<tr><td class='its-td-align center' colspan='3'>%s</td></tr>","로그 내역이 없습니다.");
		}

		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount = get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_log_member_download');

		$result = array( 'content'=>$data['html'], 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);

		echo json_encode($result);
		exit;
	}
}
/* End of file setting.php */
/* Location: ./app/controllers/admin/setting.php */