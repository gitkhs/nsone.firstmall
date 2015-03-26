<?php if ( ! defined("BASEPATH")) exit("No direct script access allowed");
/**
 * @version 1.0.0
 * @license copyright by GABIA
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 * @property CI_Benchmark $benchmark
 * @property CI_Calendar $calendar
 * @property CI_Cart $cart
 * @property CI_Config $config
 * @property CI_Controller $controller
 * @property CI_Email $email
 * @property CI_Encrypt $encrypt
 * @property CI_Exceptions $exceptions
 * @property CI_Form_validation $form_validation
 * @property CI_Ftp $ftp
 * @property CI_Hooks $hooks
 * @property CI_Image_lib $image_lib
 * @property CI_Input $input
 * @property CI_Language $language
 * @property CI_Loader $load
 * @property CI_Log $log
 * @property CI_Model $model
 * @property CI_Output $output
 * @property CI_Pagination $pagination
 * @property CI_Parser $parser
 * @property CI_Profiler $profiler
 * @property CI_Router $router
 * @property CI_Session $session
 * @property CI_Sha1 $sha1
 * @property CI_Table $table
 * @property CI_Trackback $trackback
 * @property CI_Typography $typography
 * @property CI_Unit_test $unit_test
 * @property CI_Upload $upload
 * @property CI_URI $uri
 * @property CI_User_agent $user_agent
 * @property CI_Validation $validation
 * @property CI_Xmlrpc $xmlrpc
 * @property CI_Xmlrpcs $xmlrpcs
 * @property CI_Zip $zip
 * @property template $template
 * @property Javascript $javascript
 */

class common_base extends CI_Controller {
	var $config_system = array();

	public function __construct() {
		session_start();
		error_reporting(0);//0 E_ALL

		$this->chk_refesh_load();

		parent::__construct();		

		//$this->output->enable_profiler(TRUE);//system profiler
		$this->set_header();
		$this->load->helper('cookie');
		$this->load->helper("directory");
		$this->load->helper('basic');
		$this->load->helper('debug');
		$this->load->helper('javascript');
		$this->load->helper('sqlinjection');

		$this->get_config_system();
		$this->get_config_basic();
		//$this->get_config_goodsImageSize();//사용하지않는변수
		$this->redirect_domain();
		$this->chk_mobile_env();
		sql_injection_check();

		### 데모세션처리
		$this->set_demo();

		$this->managerInfo = $this->session->userdata('manager');
		$this->marketingManager = $this->session->userdata('marketing');

		if( (isset($_GET['facebook']) && $_GET['facebook']=='Y') ||  (isset($_GET['signed_request']) && $_GET['signed_request']) || $this->session->userdata('fammercemode')){
			$this->load->library('snssocial');
			//$this->snssocial->facebooklogin();
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
			if($_GET['signed_request']){
				$this->session->set_userdata('fammercemode', $_GET['signed_request']);
			}elseif($_GET['facebook-page']){
				$this->session->set_userdata('fammercemode', $_GET['facebook-page']);
			}else{
				$this->session->set_userdata('fammercemode', $this->session->userdata('fammercemode'));
			}
			$_GET['setMode']='fammerce';
		}

		$setMode = !empty($_GET['setMode']) ? $_GET['setMode'] : '';
		$setMode = $setMode ? $setMode : $this->session->userdata('setMode');

		/* PC/모바일/페이스북 모드로 보기 */
		if($setMode){
			//if($setMode!='mobile' && $setMode!='storemobile')
			{
				$this->session->set_userdata('setMode',			$setMode);
			}
			$this->session->set_userdata('fammercemode','');

			$this->mobileMode = false;
			$this->fammerceMode = false;
			$this->storeMode = false;
			$this->storemobileMode = false;
			$this->storefammerceMode = false;

			if($setMode=='mobile')		$this->mobileMode = true;
			if($setMode=='fammerce')	$this->fammerceMode = true;
			if($setMode=='store')		$this->storeMode = true;
			if($setMode=='storemobile')	$this->storemobileMode = true;
			if($setMode=='storefammerce')	$this->storefammerceMode = true;


		}else{
			$setMode = $this->session->userdata('setMode');
		}

		/* setMode가 페이머스이거나 페이스북 캔버스 내에서 호출할때 */
		if($setMode=='fammerce' || $this->session->userdata('fammercemode')){
			$this->fammerceMode = true;
			$this->mobileMode = false;
			$this->storemobileMode = false;
		}else{
			$this->fammerceMode = false;
		}

		/* 스토어 PLUS */
		if($this->config_system['service']['code']=='P_STOR'){
			define("IS_STORE", TRUE);
			$this->template->assign(array("IS_STORE"=>IS_STORE));

			if($this->mobileMode) {
				$this->mobileMode = false;
				$this->storemobileMode = true;
			}

			if($this->fammerceMode) {
				$this->fammerceMode = false;
				$this->storefammerceMode = true;
			}

			if(!$this->storemobileMode && !$this->storefammerceMode) {
				$this->storeMode = true;
			}
		}else{
			define("IS_STORE", false);
		}

		/* 페이머스플러스면 무조건 페이머스모드 */
		if($this->config_system['service']['code']=='P_FAMM'){
			$this->fammerceMode = true;
			$this->mobileMode = false;
			$this->storemobileMode = false;
			$this->storefammerceMode = false;
		}

		$this->checkfreecheck();
//		$this->imsi_function();
		$this->get_config_skin();
	}

	protected function chk_mobile_env(){

		// 모바일 기기 접속여부
		$this->_is_mobile_agent = isMobilecheck($_SERVER['HTTP_USER_AGENT']);
		$this->template->assign("__ISMOBILE_AGENT__",$this->_is_mobile_agent);

		if($this->_is_mobile_agent == "iphone"){
			// iphone 8.1 버전 확인
			$this->_is_iphone_version = isIPhoneVercheck($_SERVER['HTTP_USER_AGENT']);
			$this->template->assign("__ISIPHONE_VERSION__",$this->_is_iphone_version);
		}

		// 모바일 도메인 접속여부
		$this->_is_mobile_domain = preg_match("/^m\./",$_SERVER['HTTP_HOST']);
		$this->template->assign("__ISMOBILE_DOMAIN__",$this->_is_mobile_domain);

		/* 모바일 쇼핑몰 모드 */
		if($this->config_system['service']['code']=='P_STOR'){
			$this->storemobileMode = $this->_is_mobile_domain ? true : false;
			if($this->_is_mobile_domain && !$_GET['setMode']) $_GET['setMode'] = 'storemobile';
		}else{
			$this->mobileMode = $this->_is_mobile_domain ? true : false;
			if($this->_is_mobile_domain && !$_GET['setMode']) $_GET['setMode'] = 'mobile';
		}
	}

	protected function set_header(){
		ini_set("default_charset", 'utf-8');
		ini_set('zlib.output_compression_level', 3 );

		$phpver		= phpversion();
		$useragent	= (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;
		if ($phpver >= '4.0.4pl1' && (strstr($useragent,'compatible') || strstr($useragent,'Gecko')))
		{
			//if (extension_loaded('zlib')) { ob_start('ob_gzhandler'); }
		}
		else if ( $phpver > '4.0' )
		{
			if (strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip'))
			{
				if (extension_loaded('zlib'))
				{
					ob_start();
					ob_implicit_flush(0);
					header('Content-Encoding: gzip');
				}
			}
		}
		else
		{
			header('Content-Length: ' . ob_get_length());
		}

	    header("Content-Type: text/html; charset=UTF-8");
	    header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	    header("Cache-Control: no-store, no-cache, must-revalidate");
	    header("Cache-Control: post-check=0, pre-check=0", false);
	    header("Pragma: no-cache");

		/* 서버 환경체크 */
		$required_extensions = array('iconv','json','gd','curl','mysql','mbstring','mcrypt','soap'/*,'zlib'*/);
		$loaded_extensions = array_map('strtolower',get_loaded_extensions());
		$error = array();
		if(phpversion()<'5.2'){
			$error[] = ('Firstmall needs PHP version 5.2.');
		}
		foreach($required_extensions as $val){
			if(!in_array(strtolower($val),$loaded_extensions)){
				$error[] = ('Firstmall needs the ['.$val.'] PHP extension.');
			}
		}
		if($error) {
			foreach($error as $row){
				echo $row.'<br />';
			}
			exit;
		}
	}

	function get_config_system(){
		$this->config_system = config_load('system');

		/* facebook 체크 */
		isfacebook();

		if	($this->config_system['service']['code'] == 'P_STOR')
			$this->config_system['service']['max_coupon_use']	= 1;

		$this->config_system['time_split'] = explode(' ',microtime());
		$this->config_system['time_start'] = $this->config_system['time_split'][0]+$this->config_system['time_split'][1];
		//$this->config_system['service']['code'] = "P_FREE";
		$this->template->assign("config_system",$this->config_system);
		define("SERVICE_CODE",$this->config_system['service']['code']);
		define("SERVICE_NAME",$this->config_system['service']['name']);

		$this->template->assign(array("service_code"=>SERVICE_CODE,"service_name"=>SERVICE_NAME));
	}

	/* 기본 설정 로드 */
	public function get_config_basic(){
		$this->config_basic = config_load('basic');
		$this->template->assign("config_basic",$this->config_basic);
	}

	public function get_config_shipping(){
		$this->config_shipping = config_load('shipping');
		$this->template->assign("config_shipping",$this->config_shipping);
	}

	function get_config_goodsImageSize(){
		$this->config_goodsImageSize = config_load('goodsImageSize');
		$this->template->assign("config_goodsImageSize",$this->config_goodsImageSize);
	}

	function redirect_domain(){
		/*
		if($_SERVER['HTTP_HOST'] == $this->config_system['domain']) return true;
		$url = prep_url($this->config_system['domain'])."/".uri_string();
		header("Location: ".$url);
		*/
	}


	function volume_check($ajax=null){
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_limit_check();
		if(!$result['type']){
			if(!$ajax){
				$callback = "";
				openDialogAlert($result['msg'],700,340,'parent',$callback,array('hideButton'=>true));
				exit;
			}else{
				echo $result['msg'];
			}
		}
	}



	/**
	* 무료몰 제한체크
	* 적립금유효기간
	* 포인트(유효기간포함)
	* 이머니(유효기간포함)
	* 프로모션코드
	* 관리자 수기주문
	* 사은품
	* 개인결제
	* 구매확정
	* 상품후기 적립금
	* 대량구매
	**/
	public function checkfreecheck(){
		if( SERVICE_CODE != 'P_FREE' ){//무료몰인경우 제한
			$this->isplusfreenot['code'] = SERVICE_CODE;
			$this->reserves = config_load('reserve');
			//적립금유효기간
			//관리자 수기주문
			//사은품
			//개인결제
			//구매확정
			//상품후기 적립금, //대량구매

			//포인트(유효기간포함) : 사용여부에 따라
			$this->isplusfreenot['ispoint']		= ($this->reserves['point_use'] == 'Y')?true:false;
			//포인트교환
			$this->isplusfreenot['isemoney_exchange']		= ($this->reserves['emoney_exchange_use'] == 'y')?true:false;

			//이머니(유효기간포함) : 사용여부에 따라
			$this->isplusfreenot['iscash']		= ($this->reserves['cash_use'] == 'Y')?true:false;
			//프로모션코드 : 사용여부에 따라
			$this->isplusfreenot['ispromotioncode']		= ($this->reserves['promotioncode_use'] == 'Y')?true:false;

			$this->template->assign('isplusfreenot',$this->isplusfreenot);
		}
		//오픈마켓 사용시 이미지 호스팅연결
		$openmarketuse = true;
		$this->template->assign('openmarketuse',$openmarketuse);

		// 데모몰에서는 차단
		if( $this->demo ) {
			$this->isdemo['isdemo']				= true;
			$this->isdemo['isdemoid']			= 'firstmall';//'firstmall';
			$this->isdemo['isdemopw']			= 'test1234';//'firstmall';
			$this->isdemo['isdemodisabled']	= "disabled";
			$this->isdemo['msg']					= "체험 사이트에서는 해당 기능을 제공하지 않습니다.";
			$this->isdemo['isdemojs1']			= ' onclick="servicedemoalert(this);" ';
			$this->isdemo['isdemojs2']			= 'servicedemoalert(this);';
			$this->template->assign('isdemo',$this->isdemo);//isdemo.isdemo isdemo.isdemojs1 isdemo.isdemojs2
		}

	}

	### 데모세션처리
	public function set_demo(){
		$filename = ROOTPATH.APPPATH."helpers/demo_helper".EXT;
		if(file_exists($filename)){
			$this->load->helper('demo');
		}
	}

	public function imsi_function(){
		for ($z = 1; $z <= 300; $z++){
			$target	= 'gwmart-child'.$z;
			echo 'cp -rf ./gwmart-child1/* ./'.$target.'/'."\n";
			echo 'cp ./gwmart-child1/.htaccess ./'.$target.'/'."\n";
			echo 'chown -R gwmart:'.$target.' ./'.$target.''."\n";
			echo 'chmod -R 777 ./'.$target.'/_compile'."\n";
			echo 'chmod -R 777 ./'.$target.'/data'."\n";
			echo "\n";
		}
		exit;
	}

	public function get_config_skin(){
		/* 일반스킨 */
		$this->realSkin = $this->config_system['skin'];
		$this->workingSkin = $this->config_system['workingSkin'];

		/* 모바일 쇼핑몰 스킨*/
		$this->realMobileSkin = $this->config_system['mobileSkin'];
		$this->workingMobileSkin = $this->config_system['workingMobileSkin'];

		/* 페이머스용 스킨 */
		$this->realFammerceSkin = $this->config_system['fammerceSkin'];
		$this->workingFammerceSkin = $this->config_system['workingFammerceSkin'];

		/* 매장용 PC 스킨 */
		$this->realStoreSkin = $this->config_system['storeSkin'];
		$this->workingStoreSkin = $this->config_system['workingStoreSkin'];

		/* 매장용 모바일 스킨 */
		$this->realStoremobileSkin = $this->config_system['storemobileSkin'];
		$this->workingStoremobileSkin = $this->config_system['workingStoremobileSkin'];

		/* 매장용 페이머스 스킨 */
		$this->realStorefammerceSkin = $this->config_system['storefammerceSkin'];
		$this->workingStorefammerceSkin = $this->config_system['workingStorefammerceSkin'];

		//페이머스 또는 매장 전용이면 해당 스킨으로 적용
		if( SERVICE_CODE=='P_FAMM') {//
			$this->workingSkin = $this->workingFammerceSkin;
		}elseif( SERVICE_CODE=='P_STOR') {//
			$this->workingSkin = $this->workingStoreSkin;
		}

		/* 아이디자인에서 처리할 스킨 */
		if		($this->mobileMode)			$this->designWorkingSkin = $this->workingMobileSkin;
		elseif	($this->fammerceMode)		$this->designWorkingSkin = $this->workingFammerceSkin;
		elseif	($this->storeMode)			$this->designWorkingSkin = $this->workingStoreSkin;
		elseif	($this->storemobileMode)	$this->designWorkingSkin = $this->workingStoremobileSkin;
		elseif	($this->storefammerceMode)	$this->designWorkingSkin = $this->workingStorefammerceSkin;
		else 								$this->designWorkingSkin = $this->workingSkin;

	}

	// 새로고침 부하 체크
	public function chk_refesh_load(){

		$chk_sec = 2; // n초
		$chk_cnt = 10; // n회
		$block_sec = 10; // 차단될경우 n초동안 접속불가

		$sess_name = 'fm_load_check';
		$sess_block_name = 'fm_load_block';
		$now = time();

		if(in_array($_SERVER['argv'][0],array('/common/autocomplete','/font/json_font','/goods/option_stock','/coupon/goods_coupon_max','/favicon.ico'))) return;
		if(preg_match("/^\/admin\/|^\/cosmos\/|^\/errdoc\//i",$_SERVER['REQUEST_URI'])) return;
		if(get_class($this)=='errdoc') return;

		$sess = (array)$_SESSION[$sess_name];

		$exists = false;
		$alert = false;

		foreach($sess as $i=>$row){
			if($row['uri']==$_SERVER['REQUEST_URI']){
				$exists = true;

				if(!is_array($row['times'])) $row['times'] = array();
				$row['times'][] = $now;

				$cnt = 0;
				foreach($row['times'] as $j=>$time){
					if($time >= $now-$chk_sec){
						$cnt++;
					}else{
						unset($row['times'][$j]);
					}
				}
				$sess[$i]['times']=array_values($row['times']);
				if($cnt>=$chk_cnt){
					$alert = true;
					break;
				}

			}else{
				$cnt = 0;
				foreach($row['times'] as $j=>$time){
					if($time < $now-$chk_sec){
						unset($row['times'][$j]);
					}
				}
				$sess[$i]['times']=array_values($row['times']);
			}

			if(!$row['times'] || !count($row['times'])) {unset($sess[$i]);}
		}

		if(!$exists){
			$sess[] = array('uri'=>$_SERVER['REQUEST_URI'],'times'=>array($now));
		}


		if($alert){
			$_SESSION[$sess_block_name] = $now;
			$remain_sec = $block_sec - ($now-$_SESSION[$sess_block_name]);
			$this->set_header();
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$msg = "과도한 접속으로 인한 차단 - {$chk_sec}초 동안 {$cnt}회 이상 접속 시도";
			echo "<script>alert('{$msg}');setTimeout(function(){document.location.reload();},'".($remain_sec*1000)."');</script>";
			echo "{$remain_sec}초동안 접속차단";
			exit;
		}

		if(!empty($_SESSION[$sess_block_name]) && $_SESSION[$sess_block_name]>$now-$block_sec){
			$remain_sec = $block_sec - ($now-$_SESSION[$sess_block_name]);
			$this->set_header();
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script>setTimeout(function(){document.location.reload();},'".($remain_sec*1000)."');</script>";
			echo "{$remain_sec}초동안 접속차단";
			exit;
		}

		$_SESSION[$sess_name] = array_values($sess);

	}
}
?>