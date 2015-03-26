<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class login_process extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	/**
	*
	* @
	*/
	public function login(){
		$this->load->model('ssl');
		$this->ssl->decode();

		### Validation
		$this->validation->set_rules('userid', '아이디','trim|required|max_length[60]|xss_clean');
		$this->validation->set_rules('password', '비밀번호','trim|required|max_length[32]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "parent.setDefaultText();if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		### QUERY
		$query = "select password(?) pass,old_password(?) old_pass";
		$query = $this->db->query($query,array($_POST['password'],$_POST['password']));
		$data = $query->row_array();

		$str_md5 = md5($_POST['password']);
		$str_password = $data['pass'];
		$str_oldpassword = $data['old_pass'];
		$str_sha_md5 = hash('sha256',$str_md5);
		$str_sha_password = hash('sha256',$data['pass']);
		$str_sha_oldpassword = hash('sha256',$data['old_pass']);

		$query = "select A.*,B.business_seq,B.bname,C.group_name from fm_member A LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq left join fm_member_group C on C.group_seq=A.group_seq where A.userid=? and (A.password=? or A.password=? or A.password=? or A.password=? or A.password=? or A.password=?)";
		$query = $this->db->query($query,array($_POST['userid'],$str_md5,$str_password,$str_oldpassword,$str_sha_md5,$str_sha_password,$str_sha_oldpassword));
		$data = $query->result_array();

		if(!$data){
			$callback = "parent.setDefaultText();if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
			openDialogAlert("일치하는 회원정보가 없습니다.",400,140,'parent',$callback);
			exit;
		}

		if($data[0]['status']=='hold'){
			$callback = "parent.setDefaultText();if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
			openDialogAlert("<b>{$data[0]['user_name']}</b>님은 아직 가입승인되지 않았습니다.",400,140,'parent',$callback);
			exit;
		}

		### LOG
		$params = $data[0];
		$qry = "update fm_member set login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$params['member_seq']}'";
		$result = $this->db->query($qry);

		### SESSION
		$this->create_member_session($params);

		### 장바구니 MERGE
		$this->load->model('cartmodel');
		$this->cartmodel->merge_for_member($params['member_seq']);

		//fblike 할인 MERGE
		$this->db->where('session_id',$this->session->userdata('session_id'));
		$this->db->update('fm_goods_fblike', array('member_seq' => $params['member_seq']));

		### 로그인 이벤트
		$this->load->model('joincheckmodel');
		$jcresult = $this->joincheckmodel->login_joincheck($params['member_seq']);

		/* 고객리마인드서비스 상세유입로그 */
		$this->load->helper('reservation');
		$curation = array("action_kind"=>"login");
		curation_log($curation);

		switch($jcresult['code']){
			case 'success' :
				alert($jcresult['msg']);
			break;
			case 'emoney_pay' :
				alert($jcresult['msg']);
			break;
		}

		$this->load->helper('cookie');

		if($_POST['idsave'] == 'checked' ){
			setcookie('userlogin',$_POST['userid'],time()+(86400*365),'/');	//1년간 저장
		}else{
			delete_cookie('userlogin');
		}

		### PAGE MOVE
		if(!empty($_POST['order_auth'])){
			if(strstr(urldecode($_POST['return_url']),"/board/write") || strstr(urldecode($_POST['return_url']),"goods/review_write") || strstr(urldecode($_POST['return_url']),"/mypage/mygdreview_write") ) {
				//echo js("parent.opener.location.reload();parent.self.close();");//상품후기 >> 비회원 주문검색시 새창처리
				if ( $this->mobileMode  || $this->storemobileMode || $this->_is_mobile_agent) {
					echo js("parent.opener.document.writeform.action='';parent.opener.document.writeform.target='';parent.opener.document.writeform.submit();parent.self.close();");//상품후기 >> 비회원 주문검색시 새창처리
				}else{
				echo js("parent.opener.document.writeform.action='';parent.opener.document.writeform.target='';if(parent.opener.readyEditorForm(parent.opener.document.writeform)){parent.opener.document.writeform.submit();}parent.self.close();");//상품후기 >> 비회원 주문검색시 새창처리
				}
			}else{
				pageRedirect('/mypage/order_catalog','','parent');
			}
		}elseif(!empty($_POST['return_url'])){
			pageRedirect(urldecode($_POST['return_url']),'','parent');
		}else{
			pageRedirect('/main/index','','parent');
		}

	}

###
	public function loginid_chk() {
		$conf = config_load('joinform');
		if($_POST['userid'] == '@' ) $_POST['userid'] = '';

		$userid = $_POST['userid'];
		if(!$userid) die();
		//$userid = strtolower($userid);

		$this->validation->set_rules('userid', '이메일','trim|required|valid_email|xss_clean');
		if($this->validation->exec()===false){
			$text = "유효하지 않는 이메일 형식입니다.";
			$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);
			echo json_encode($result);
			exit;
		}

		###
		$count = get_rows('fm_member',array('userid'=>$userid));

		$return = true;
		$text = "OK";
		if($count > 0) {
			$return = 'duplicate';
		}else{
			$text = "등록된 이메일이 아닙니다.";
			$return = 'no_duplicate';
		}
		$result = array("return_result" => $text, "userid" => $userid, "return" => $return, "returns" => $return);
		echo json_encode($result);
	}


	public function loginpw_chk() {
		$conf = config_load('joinform');
		if($_POST['userid'] == '@' ) $_POST['userid'] = '';

		$userid = $_POST['userid'];
		$password = $_POST['password'];
		if(!$userid) die();
		if(!$password) die();

		$this->validation->set_rules('userid', '이메일','trim|required|valid_email|xss_clean');
		if($this->validation->exec()===false){
			$text = "유효하지 않는 이메일 형식입니다.";
			$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);
			echo json_encode($result);
			exit;
		}
		$this->validation->set_rules('password', '비밀번호','trim|required|max_length[32]|xss_clean');

		if($this->validation->exec()===false){
			$text = "유효하지 않는 비밀번호 형식입니다.";
			$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);
			echo json_encode($result);
			exit;
		}

		### QUERY
		$query = "select password(?) pass,old_password(?) old_pass";
		$query = $this->db->query($query,array($_POST['password'],$_POST['password']));
		$data = $query->row_array();

		$str_md5 = md5($_POST['password']);
		$str_password = $data['pass'];
		$str_oldpassword = $data['old_pass'];
		$str_sha_md5 = hash('sha256',$str_md5);
		$str_sha_password = hash('sha256',$data['pass']);
		$str_sha_oldpassword = hash('sha256',$data['old_pass']);

		$query = "select * from fm_member where userid=? and (`password`=? or `password`=? or `password`=? or `password`=? or `password`=? or `password`=?)";
		$query = $this->db->query($query,array($_POST['userid'],$str_md5,$str_password,$str_oldpassword,$str_sha_md5,$str_sha_password,$str_sha_oldpassword));
		$data = $query->result_array();

		if(!$data){
			$text = "비밀번호가 일치하지 않습니다.";
			$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);
			echo json_encode($result);
			exit;
		}

		if($data[0]['status']=='hold'){
			$text = "아직 가입승인되지 않았습니다.";
			$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);
			echo json_encode($result);
			exit;
		}

		$return = true;
		$text = "OK";
		$result = array("return_result" => $text, "userid" => $userid, "return" => $return, "returns" => $return);
		echo json_encode($result);
		exit;
	}

	/**
	*
	* @
	*/
	public function logout(){
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('fbuser');
		$this->session->unset_userdata('accesstoken');
		$this->session->unset_userdata('signedrequest');
		$this->session->unset_userdata('nvuser');
		$this->session->unset_userdata('mtype');
		$this->session->unset_userdata('naver_state');
		$this->session->unset_userdata('naver_access_token');
		$this->session->unset_userdata('kkouser');
		$this->session->unset_userdata('dmuser');
		$this->session->unset_userdata('daum_access_token');
		$this->session->unset_userdata('http_host');
		$this->session->unset_userdata('snslogn');
		$_SESSION['user']			= ''; $_SESSION['fbuser']				= '';
		$_SESSION['accesstoken']	= ''; $_SESSION['signedrequest']		= '';
		$_SESSION['nvuser']			= ''; $_SESSION['naver_access_token']	= '';
		$_SESSION['naver_state']	= ''; $_SESSION['mtype']				= '';
		$_SESSION['kkouser']		= ''; 
		$_SESSION['dmuser']			= ''; $_SESSION['daum_access_token']	= '';
		$_SESSION['http_host']		= ''; $_SESSION['snslogn']				= '';
		unset($this->userInfo, $_SESSION['user'], $_SESSION['fbuser'], $_SESSION['naver_state'], $_SESSION['naver_access_token'], $_SESSION['nvuser'], $_SESSION['accesstoken'], $_SESSION['signedrequest'],$_SESSION['kkouser'],$_SESSION['dmuser'],$_SESSION['daum_access_token']);

		if(strstr($_SERVER[HTTP_REFERER], "/order") !== false && strstr($_SERVER[HTTP_REFERER], "/mypage") !== false){
			pageReload('','parent');
		}else{
			pageRedirect('/main/index','','parent');
		}
	}
	/**
	*
	* @
	*/
	public function create_member_session($data=array()){
		$this->load->helper('member');
		create_member_session($data);
	}


	public function findid(){
		$this->load->model('ssl');
		$this->ssl->decode();

		### Validation
		$this->validation->set_rules('user_name', '이름','trim|required|max_length[20]|xss_clean');
		if($_POST['find_gb']=='email'){
			$this->validation->set_rules('email', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
		}else{
			$this->validation->set_rules('cellphone[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');
			$cellphone = implode("-",$_POST['cellphone']);
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		### QUERY
		if($_POST['find_gb']=='email'){
			$enc_qry	= get_encrypt_where('email', $_POST['email']);
		}else{
			$enc_qry	= "(" . get_encrypt_where('cellphone', $cellphone)
						. " or B.bcellphone = '".$cellphone."')";
		}

		//실명인증사용시 기본가입회원만가능@2013-08-08
		$realname = config_load('realname');
		if( $realname['useRealname']=='Y' || $realname['useRealnamephone']=='Y' || $realname['useIpin']=='Y' ){
			//$enc_qry .= ' AND A.auth_type = "none" ';
		}

		$key = get_shop_key();
		$sql = "SELECT A.*, AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				B.business_seq, B.bcellphone FROM fm_member A
				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				WHERE (A.user_name = '{$_POST[user_name]}' OR B.bname = '{$_POST[user_name]}') AND {$enc_qry} ";
		$query	= $this->db->query($sql);
		$data	= $query->result_array();

		$success=0;
		foreach($data as $mbrow) {
			if($_POST['find_gb']=='email'){
				$result = sendMail($mbrow['email'], 'findid', $mbrow['userid'], $mbrow);
				if($result) $success++;
			}else{
				$cellphone	= $mbrow['cellphone'];
				if	($mbrow['business_seq'])	$cellphone	= $mbrow['bcellphone'];
				
				$commonSmsData = array();
				$commonSmsData['findid']['phone'][] = $cellphone;
				$commonSmsData['findid']['params'][] = $mbrow;
				$commonSmsData['findid']['mid'][] = $mbrow['userid'];
				$result = commonSendSMS($commonSmsData);

				if($result) $success++;
			}
		}
		$scdocument = "parent.document";
		$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
		$scripts[] = "<script type='text/javascript'>";
		$scripts[] = "$(function() {";

		$scripts[] = '$("#findidfromlay",'.$scdocument.').hide();';
		$scripts[] = '$("#findidresultlay",'.$scdocument.').show();';
		$scripts[] = '$("#findidlay1",'.$scdocument.').text("");';
		$scripts[] = '$("#findidlay2",'.$scdocument.').text("");';
		$scripts[] = '$("#findidlay3",'.$scdocument.').text("");';
		$scripts[] = '$(".findidresultok1",'.$scdocument.').hide();';
		$scripts[] = '$(".findidresultok2",'.$scdocument.').hide();';
		$scripts[] = '$(".findidresultok3",'.$scdocument.').hide();';
		$scripts[] = '$(".findidresultfalse",'.$scdocument.').hide();';
		if( $success ) {
			if($_POST['find_gb']=='email'){
				$scripts[] = '$(".findidresultok2",'.$scdocument.').show();';
				$scripts[] = '$("#findidlay2",'.$scdocument.').text("'.get_return_data($data[0]['email'],5,"*").'");';
			}else{
				$scripts[] = '$(".findidresultok3",'.$scdocument.').show();';
				$scripts[] = '$("#findidlay3",'.$scdocument.').text("'.get_return_data($cellphone,5,"*").'");';
			}
		}else{
			$scripts[] = '$(".findidresultfalse",'.$scdocument.').show();';
		}

		$scripts[] = "});";
		$scripts[] = "</script>";
		foreach($scripts as $script){
			echo $script."\n";
		}
		exit;
	}




	public function findpwd(){
		$this->load->model('ssl');
		$this->ssl->decode();

		### Validation
		$this->validation->set_rules('user_names', '이름', 'trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('userids', '아이디', 'trim|required|xss_clean');
		if($_POST['finds_gb']=='emails'){
			$this->validation->set_rules('emails', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
		}else{
			$this->validation->set_rules('cellphones[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');
			$cellphone = implode("-",$_POST['cellphones']);
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		### QUERY
		if($_POST['finds_gb']=='emails'){
			$enc_qry	= get_encrypt_where('email', $_POST['emails']);
		}else{
			$enc_qry	= "(" . get_encrypt_where('cellphone', $cellphone)
						. " or B.bcellphone = '".$cellphone."')";
		}

		//실명인증사용시 기본가입회원만가능 @2013-08-08
		$realname = config_load('realname');
		if( $realname['useRealname']=='Y' || $realname['useRealnamephone']=='Y' || $realname['useIpin']=='Y' ){
			//$enc_qry .= ' AND A.auth_type = "none" ';
		}

		$key = get_shop_key();
		$sql = "SELECT A.*, AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone  FROM fm_member A
				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				WHERE A.rute = 'none' and  (A.user_name = '{$_POST[user_names]}' OR B.bname = '{$_POST[user_names]}') AND A.userid = '{$_POST[userids]}' AND  {$enc_qry} ";
		$query	= $this->db->query($sql);
		$data	= $query->result_array();
		###
		$success=0;
		foreach($data as $mbrow) {
			$params = $data[0];
			unset($mbrow['password']);
			$mbrow['password'] = chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).substr(mktime()*2,1,4);
			$mbrow['passwd'] = $mbrow['password'];
			if($_POST['finds_gb']=='emails'){
				$mbrow['regist_date'] = date('Y-m-d H:i:s');
				$email = $mbrow['email'];
				$result = sendMail($mbrow['email'], 'findpwd', $mbrow['userid'], $mbrow);
				if($result) $success++;
			}else{
				
				$commonSmsData = array();
				$commonSmsData['findpwd']['phone'][] = $cellphone;
				$commonSmsData['findpwd']['params'][] = $mbrow;
				$commonSmsData['findpwd']['mid'][] = $mbrow['userid'];
				$result = commonSendSMS($commonSmsData);

				if($result) $success++;
			}
			$mbrow['passwd'] = hash('sha256',md5($mbrow['passwd']));
			$sql = "update fm_member set password = ?, update_date = now() where member_seq = ?";
			$this->db->query($sql,array($mbrow['passwd'],$mbrow['member_seq']));
		}

		$scdocument = "parent.document";
		$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
		$scripts[] = "<script type='text/javascript'>";
		$scripts[] = "$(function() {";

		$scripts[] = '$("#findpwfromlay",'.$scdocument.').hide();';
		$scripts[] = '$("#findpwresultlay",'.$scdocument.').show();';
		$scripts[] = '$("#findpwlay1",'.$scdocument.').text("");';
		$scripts[] = '$("#findpwlay2",'.$scdocument.').text("");';
		$scripts[] = '$("#findpwlay3",'.$scdocument.').text("");';
		$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').hide();';
		$scripts[] = '$(".findpwresultfalse2",'.$scdocument.').hide();';
		$scripts[] = '$(".findpwresultok1",'.$scdocument.').hide();';
		$scripts[] = '$(".findpwresultok2",'.$scdocument.').hide();';
		$scripts[] = '$(".findpwresultok3",'.$scdocument.').hide();';

		if( $success ) {
			if($_POST['finds_gb']=='emails'){
				$scripts[] = '$(".findpwresultok2",'.$scdocument.').show();';
				$scripts[] = '$("#findpwlay2",'.$scdocument.').text("'.get_return_data($email,5,"*").'");';
			}else{
				$scripts[] = '$(".findpwresultok3",'.$scdocument.').show();';
				$scripts[] = '$("#findpwlay3",'.$scdocument.').text("'.get_return_data($cellphone,5,"*").'");';
			}
		}else{
			$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').show();';
		}
		$scripts[] = "});";
		$scripts[] = "</script>";
		foreach($scripts as $script){
			echo $script."\n";
		}
		exit;

	}

	public function popup_change_pass(){

		if($_POST['password_mode'] == "update"){

			if($_POST['update_rate'] != "Y"){
				$this->validation->set_rules('old_password', '기존 비밀번호','trim|required|min_length[6]|max_length[32]|xss_clean');
				$this->validation->set_rules('new_password', '신규 비밀번호','trim|required|min_length[6]|max_length[20]|xss_clean');
				$this->validation->set_rules('re_new_password', '신규 비밀번호확인','trim|required|min_length[6]|max_length[20]|xss_clean');

				if($this->validation->exec()===false){
					$err = $this->validation->error_array;
					$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
					openDialogAlert($err['value'],400,140,'parent',$callback);
					exit;
				}
				
				if($_POST['old_password'] == $_POST['new_password']){
					$text = "신규 비밀번호와 신규 비밀번호 확인 값이 다릅니다..";
					$callback = "if(parent.document.getElementsByName('new_password')[0]) parent.document.getElementsByName('new_password')[0].focus();";
					openDialogAlert($text,400,140,'parent',$callback);
					exit;

				}

				### QUERY
				$query = "select password(?) pass,old_password(?) old_pass";
				$query = $this->db->query($query,array($_POST['old_password'],$_POST['old_password']));
				$data = $query->row_array();

				$str_md5 = md5($_POST['old_password']);
				$str_password = $data['pass'];
				$str_oldpassword = $data['old_pass'];
				$str_sha_md5 = hash('sha256',$str_md5);
				$str_sha_password = hash('sha256',$data['pass']);
				$str_sha_oldpassword = hash('sha256',$data['old_pass']);

				$query = "select count(*) as cnt from fm_member A LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq left join fm_member_group C on C.group_seq=A.group_seq where A.member_seq=? and (A.password=? or A.password=? or A.password=? or A.password=? or A.password=? or A.password=?)";
				$query = $this->db->query($query,array($this->userInfo['member_seq'],$str_md5,$str_password,$str_oldpassword,$str_sha_md5,$str_sha_password,$str_sha_oldpassword));
				$data = $query->row_array();
				
				if($data['cnt'] < 1){
					$text = "기존 비밀번호가 맞지 않습니다.";
					$callback = "if(parent.document.getElementsByName('new_password')[0]) parent.document.getElementsByName('new_password')[0].focus();";
					openDialogAlert($text,400,140,'parent',$callback);
					exit;

				}

				if($_POST['old_password'] == $_POST['new_password']){
					$text = "기존 비밀번호와 동일한 비밀번호로 변경할 수 없습니다.";
					$callback = "if(parent.document.getElementsByName('new_password')[0]) parent.document.getElementsByName('new_password')[0].focus();";
					openDialogAlert($text,400,140,'parent',$callback);
					exit;

				}


				$mix_check = 0;
				//소문자영문체크 
				if(preg_match("/[a-z]/",$_POST['new_password'])){
					$mix_check += 1; 
				}

				//대문자영문체크 
				if(preg_match("/[A-Z]/",$_POST['new_password'])){
					$mix_check += 1; 
				}

				//숫자체크 
				if(preg_match("/[0-9]/",$_POST['new_password'])){
					$mix_check += 1; 
				}
				
				//특수문자체크 
				if(preg_match("/[!#$%^&*()?+=\/]/",$_POST['new_password'])){
					$mix_check += 1;
				}

				if($mix_check < 2){
					$text = "비밀번호는 6~20자 영문 대소문자 또는 숫자, 특수문자 중<br> 2가지 이상 조합여야 합니다.";
					$callback = "if(parent.document.getElementsByName('password')[0]) parent.document.getElementsByName('password')[0].focus();";
					openDialogAlert($text,400,140,'parent',$callback);
					exit;

				}

				$params['password_update_date'] = date("Y-m-d");
				$params['password']	= hash('sha256',md5($_POST['new_password']));

				$this->db->where('member_seq',$this->userInfo['member_seq']);
				$this->db->update('fm_member', $params);

			}else{

				$params['password_update_date'] = date("Y-m-d");

				$this->db->where('member_seq',$this->userInfo['member_seq']);
				$this->db->update('fm_member', $params);

			}
		}

		$usnet_item = array('user' => array('password_update_date' => ''));
		$this->session->unset_userdata($usnet_item);
		unset($_SESSION['user']['password_update_date']);
		if($this->mobileMode || $this->storemobileMode){
			echo "<script>parent.location.replace('/main/index');</script>";
		}else{
			echo "<script>parent.close_popup_change(); parent.location.reload();</script>";
		}
	}




}
/* End of file login_process.php */
/* Location: ./app/controllers/login_process.php */