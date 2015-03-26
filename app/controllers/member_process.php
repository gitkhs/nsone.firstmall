<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class member_process extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->library('snssocial');
		$this->load->model('membermodel');
		$this->load->helper('member');
	}


	###
	public function register(){
		if($_POST['agree']!='Y' || $_POST['agree2']!='Y'){
			$key	= $_POST['agree']!='Y' ? "agree" : "agree2";
			$name	= $_POST['agree']!='Y' ? "이용약관에 동의하셔야합니다." : "개인정보취급방침에 동의하셔야합니다.";
			$callback = "if(parent.document.getElementsByName('{$key}')[0]) parent.document.getElementsByName('{$key}')[0].focus();";
			openDialogAlert($name,400,140,'parent',$callback);
			exit;
		}
		$url = isset($_POST['join_type']) ? '../member/register?join_type='.$_POST['join_type'] : '../member/register';
		pageRedirect($url,'','parent');
	}

	###
	public function id_chk($chk_key = null){

		$conf = config_load('joinform');

		if ( $conf['email_userid'] == 'Y' ) {
			if($_POST['userid'] == '@' ) $_POST['userid'] = '';
		}

		$userid = $_POST['userid'];
		if(!$userid) die();
		$userid = strtolower($userid);

		if ( $conf['email_userid'] == 'Y' ) {
			$this->validation->set_rules('userid', '이메일','trim|required|valid_email|xss_clean');
			if($this->validation->exec()===false){
				$err = $this->validation->error_array;
				$text = "유효하지 않는 이메일 형식입니다.";//$err['value'];
				$result = array("return_result" => $text, "userid" => $_POST['userid'], "return" => false, "returns" => false);
				echo json_encode($result);
				exit;
			}
		}

		###
		$count = get_rows('fm_member',array('userid'=>$userid));

		###
		$disabled_userid = explode(",",$conf['disabled_userid']);

		$return = true;
		if ( $conf['email_userid'] == 'Y' ) {
			$text = "OK";
			if(in_array($userid, $disabled_userid)) {
				$text = "금지 이메일 입니다.";
				$return = false;
			}else if($count > 0){
				$text = "이미 등록된 이메일 입니다.";
				$return = 'duplicate';
			}
		}else{
			$text = "사용할 수 있는 아이디 입니다.";
			if(strlen($userid)<6 || strlen($userid)>20){
				$text = "아이디 글자 제한 수를 맞춰주세요.";
				$return = false;
			}else if(preg_match("/[^a-z0-9\-_]/i", $userid)) {
				$text = "사용할 수 없는 아이디 입니다.";
				$return = false;
			}else if(in_array($userid, $disabled_userid)) {
				$text = "금지 아이디 입니다.";
				$return = false;
			}else if($count > 0){
				$text = "이미 사용중인 아이디 입니다.";
				$return = false;
			}
		}
		$result = array("return_result" => $text, "userid" => $userid, "return" => $return, "returns" => $return);

		if($chk_key){
			return $result;
		}else{
			echo json_encode($result);
		}
	}

	### 비밀번호 유효성체크
	public function pw_chk(){

		$conf = config_load('joinform');

		$password = $_POST['password'];
		if(!$password) die();

		$this->validation->set_rules('password', '비밀번호','trim|required|max_length[20]|min_length[6]|xss_clean');
		if($this->validation->exec()===false){
			$text = "유효하지 않은 비밀번호 형식입니다.";
			$result = array("return_result" => $text, "password" => $password, "return" => false, "returns" => false);
			echo json_encode($result);
			exit;
		}

		###
		$return = true;
		$text = "OK";
		if(strlen($password)<6 || strlen($password)>20){
			$text = "비밀번호 글자 제한 수를 맞춰주세요.";
			$return = false;
		}else if(preg_match("/[^a-z0-9\-_]/i", $password)) {
			$text = "사용할 수 없는 비밀번호 입니다.";
			$return = false;
		}
		$result = array("return_result" => $text, "password" => $password, "return" => $return, "returns" => $return);
		echo json_encode($result);
	}

	###
	public function bno_chk($chk_key = null){
		$joinform = config_load('joinform');

		$bno = trim($_POST['bno']);
		$bno = str_replace('-','',$bno);

		###
		$count = get_rows('fm_member_business',array('bno'=>$bno));

		$text = "";
		$return = true;
		if($joinform['bno_use']=='Y' && ($joinform['bno_required']=='Y' || $_POST['bno']) ) {//사용중이면서 필수 또는 입력된 경우에만
			if(!preg_match("/^[0-9]{10}$/", $bno)) {
				$text = "올바르지 않은 사업자등록번호 입니다.";
				$return = false;
			}
			//규칙에 올바른지 체크
			$weight = '137137135';
			$sum = 0;
			for ($i = 0; $i < 9; $i++) {
			 	$sum += (substr($bno,$i,1) * substr($weight , $i , 1)) %10;
			}

			$sum += (substr($bno,8,1)*5)/10 + substr($bno,9,1);

			if ($sum %10 !=0) {
				$text = "올바르지 않은 사업자등록번호 입니다.";
				$return = false;
			}

			if($count > 0){
				$text = "이미 가입된 사업자등록번호 입니다.";
				$return = false;
			}
		}
		$result = array("return_result" => $text, "bno" => $bno, "return" => $return, "returns" => $return);

		if($chk_key){
			return $result;
		}else{
			echo json_encode($result);
		}
	}

	###
	public function register_ok(){
		$this->load->model('ssl');
		$this->ssl->decode();

		$joinform = config_load('joinform');

		$label_pr = $_POST['label'];
		$label_sub_pr = $_POST['labelsub'];
		$label_required = $_POST['required'];

		### Validation
		if ( $joinform['email_userid'] == 'Y' ) {
			$this->validation->set_rules('userid', '아이디','trim|required|valid_email|xss_clean');
			$this->validation->set_rules('re_userid', '아이디확인','trim|required|valid_email|xss_clean');
		}else{
			$this->validation->set_rules('userid', '아이디','trim|required|min_length[6]|max_length[20]|xss_clean');
		}

		$this->validation->set_rules('password', '비밀번호','trim|required|min_length[6]|max_length[32]|xss_clean');
		$this->validation->set_rules('re_password', '비밀번호확인','trim|required|min_length[6]|max_length[32]|xss_clean');

		### COMMON
		if(!empty($_POST['anniversary'][0]) && !empty($_POST['anniversary'][1]))
			$_POST['anniversary'] = implode("-",$_POST['anniversary']);
		else
			$_POST['anniversary'] = '';

		### COMMON
		if(isset($_POST['email'])) $_POST['email'] = implode("@",$_POST['email']);
		if($_POST['email'] == '@' ) $_POST['email'] = '';

		### COMMON
		if ( $joinform['email_userid'] == 'Y' ) {//&& !$_POST['email']
			$_POST['email'] = $_POST['userid'];
		}

		if( is_array($_POST['births']) ) {
			$_POST['birthday'] =  $_POST['births'][0].'-'.str_pad($_POST['births'][1],2 ,"0", STR_PAD_LEFT).'-'.str_pad($_POST['births'][2],2 ,"0", STR_PAD_LEFT);
		}else{
			if($_POST['births']){
				$_POST['birthday'] = $_POST['births'];
			}else{
				$_POST['birthday'] = $_POST['birthday'] ? $_POST['birthday'] : '';
			}
		}

		if($joinform['recommend_use']=='Y'){
			if($joinform['recommend_required']=='Y') $this->validation->set_rules('recommend', '추천인','trim|required|max_length[100]|xss_clean');
			else $this->validation->set_rules('recommend', '추천인','trim|max_length[100]|xss_clean');

		}

		### MEMBER
		if(isset($_POST['mtype']) && $_POST['mtype']=='member'){

			if($joinform['email_use']=='Y'){
				if($joinform['email_required']=='Y') {
					$this->validation->set_rules('email', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email'])) {
					$this->validation->set_rules('email', '이메일','trim|max_length[64]|valid_email|xss_clean');
				}
			}

			if($joinform['user_name_use']=='Y'){
				if($joinform['user_name_required']=='Y') $this->validation->set_rules('user_name', '이름','trim|required|max_length[32]|xss_clean');
				else $this->validation->set_rules('user_name', '이름','trim|max_length[32]|xss_clean');
			}
			if($joinform['phone_use']=='Y'){
				if($joinform['phone_required']=='Y') $this->validation->set_rules('phone[]', '연락처','trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('phone[]', '연락처','trim|max_length[4]|xss_clean');
			}
			if($joinform['cellphone_use']=='Y'){
				if($joinform['cellphone_required']=='Y') $this->validation->set_rules('cellphone[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('cellphone[]', '휴대폰번호','trim|max_length[4]|xss_clean');
			}
			if($joinform['address_use']=='Y'){
				if($joinform['address_required']=='Y'){
					$this->validation->set_rules('zipcode[]', '우편번호','trim|required|max_length[3]|numeric|xss_clean');
					$this->validation->set_rules('address', '주소','trim|required|max_length[100]|xss_clean');
					$this->validation->set_rules('address_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
				else{
					if( !empty($_POST['zipcode[]']) ) {
						$this->validation->set_rules('zipcode[]', '우편번호','trim|max_length[3]|numeric|xss_clean');
					}
					$this->validation->set_rules('address', '주소','trim|max_length[100]|xss_clean');
					$this->validation->set_rules('address_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['birthday_use']=='Y'){
				if($joinform['birthday_required']=='Y') $this->validation->set_rules('birthday', '생일','trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('birthday', '생일','trim|max_length[10]|xss_clean');
			}
			if($joinform['anniversary_use']=='Y'){
				if($joinform['anniversary_required']=='Y') $this->validation->set_rules('anniversary', '기념일','trim|required|max_length[5]|xss_clean');
				else  $this->validation->set_rules('anniversary', '기념일','trim|max_length[5]|xss_clean');
			}
			if($joinform['nickname_use']=='Y'){
				if($joinform['nickname_required']=='Y') $this->validation->set_rules('nickname', '닉네임','trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('nickname', '닉네임','trim|max_length[10]|xss_clean');
			}
			if($joinform['sex_use']=='Y'){
				if($joinform['sex_required']=='Y') $this->validation->set_rules('sex', '성별','trim|required|max_length[6]|xss_clean');
				else  $this->validation->set_rules('sex', '성별','trim|max_length[6]|xss_clean');
			}
		}

		### BUSINESS
		if(isset($_POST['mtype']) && $_POST['mtype']=='business'){
			if($joinform['bemail_use']=='Y'){
				if($joinform['bemail_required']=='Y') {
					$this->validation->set_rules('email', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email']) ) {
					$this->validation->set_rules('email', '이메일','trim|max_length[64]|valid_email|xss_clean');
				}
			}


			if($joinform['bname_use']=='Y'){
				if($joinform['bname_required']=='Y') $this->validation->set_rules('bname', '업체명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bname', '업체명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bceo_use']=='Y'){
				if($joinform['bceo_required']=='Y') $this->validation->set_rules('bceo', '대표자명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bceo', '대표자명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bno_use']=='Y'){
				if($joinform['bno_required']=='Y') $this->validation->set_rules('bno', '사업자 등록번호','trim|required|max_length[12]|xss_clean');
				else  $this->validation->set_rules('bno', '사업자 등록번호','trim|max_length[12]|xss_clean');

				###
				$return_result = $this->bno_chk('re_chk');
				if(!$return_result['return']){
					$callback = "if(parent.document.getElementsByName('bno')[0]) parent.document.getElementsByName('bno')[0].focus();";
					openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
					exit;
				}
			}
			if($joinform['bitem_use']=='Y'){
				if($joinform['bitem_required']=='Y') {
					$this->validation->set_rules('bitem', '업태','trim|required|max_length[40]|xss_clean');
					$this->validation->set_rules('bstatus', '종목','trim|required|max_length[40]|xss_clean');
				}
				else{
					$this->validation->set_rules('bitem', '업태','trim|max_length[40]|xss_clean');
					$this->validation->set_rules('bstatus', '종목','trim|max_length[40]|xss_clean');
				}
			}
			if($joinform['badress_use']=='Y'){
				if($joinform['badress_required']=='Y'){
					$this->validation->set_rules('bzipcode[]', '우편번호','trim|required|max_length[3]|numeric|xss_clean');
					$this->validation->set_rules('baddress', '주소','trim|required|max_length[100]|xss_clean');
					$this->validation->set_rules('baddress_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
				else{
					if( !empty($_POST['bzipcode[]']) ) {
						$this->validation->set_rules('bzipcode[]', '우편번호','trim|max_length[3]|numeric|xss_clean');
					}
						$this->validation->set_rules('baddress', '주소','trim|max_length[100]|xss_clean');
						$this->validation->set_rules('baddress_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['bperson_use']=='Y'){
				if($joinform['bperson_required']=='Y') $this->validation->set_rules('bperson', '담당자 명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bperson', '담당자 명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bpart_use']=='Y'){
				if($joinform['bpart_required']=='Y') $this->validation->set_rules('bpart', '담당자 부서명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bpart', '담당자 부서명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bphone_use']=='Y'){
				if($joinform['bphone_required']=='Y') $this->validation->set_rules('bphone[]', '전화번호','trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('bphone[]', '전화번호','trim|max_length[4]|xss_clean');
			}

			if($joinform['bcellphone_use']=='Y'){
				if($joinform['bcellphone_required']=='Y') $this->validation->set_rules('bcellphone[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('bcellphone[]', '휴대폰번호','trim|max_length[4]|xss_clean');
			}
		}

		### //넘어온 추가항목 seq
		foreach($label_pr as $l => $data){$label_arr[]=$l;}
		//추가항목 공백체크
		foreach($label_required as $v){
			if(!in_array($v,$label_arr)){
				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
				openDialogAlert('체크된 항목은 필수항목입니다.',400,140,'parent',$callback);
				exit;
			}else{
				$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $v));
				$form_result = $query -> row_array();
				$label_title = $form_result['label_title'];
				$this->validation->set_rules('label['.$v.'][value][]', $label_title,'trim|required|xss_clean');
			}
		}
		###

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($joinform['recommend_use']=='Y'){
			if($_POST['recommend'] == $_POST['userid']){
				$callback = "if(parent.document.getElementsByName('recommend')[0]) parent.document.getElementsByName('recommend')[0].focus();";
				openDialogAlert("본인아이디를 추천할 수 없습니다.",400,140,'parent',$callback);
				exit;
			}
		}


		###
		$return_result = $this->id_chk('re_chk');
		if(!$return_result['return']){
			$callback = "if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
			openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
			exit;
		}

		###
		$this->db->where('userid', $_POST['userid']);
		$query = $this->db->get("fm_member");
		$mem_chk = $query->result_array();
		if($mem_chk){
			$callback = "if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
			openDialogAlert("이미 등록된 아이디 입니다.",400,140,'parent',$callback);
			exit;
		}
		###
		if(strlen($_POST['password'])<6 || strlen($_POST['password'])>20){
			$callback = "if(parent.document.getElementsByName('required')[0]) parent.document.getElementsByName('required')[0].focus();";
			openDialogAlert("비밀번호 글자 제한 수를 맞춰주세요.",400,140,'parent',$callback);
			exit;
		}
		###
		if($_POST['password'] != $_POST['re_password']){
			$callback = "if(parent.document.getElementsByName('required')[0]) parent.document.getElementsByName('required')[0].focus();";
			openDialogAlert("비밀번호 확인이 일치하지 않습니다.",400,140,'parent',$callback);
			exit;
		}

		###
		$params = $_POST;
		$params['regist_date']	= date('Y-m-d H:i:s');
		$params['group_seq']	= '1';
		if(isset($_POST['phone']))  $params['phone'] = implode("-",$_POST['phone']);
		if(isset($_POST['cellphone']))  $params['cellphone'] = implode("-",$_POST['cellphone']);
		if(isset($_POST['zipcode']))  $params['zipcode'] = implode("-",$_POST['zipcode']);
		$params['password']	= hash('sha256',md5($_POST['password']));
		$params['marketplace'] = !empty($_COOKIE['marketplace']) ? $_COOKIE['marketplace'] : '';//유입매체
		$params['referer']			= $this->session->userdata('shopReferer');
		$params['referer_domain']	= $this->session->userdata('refererDomain');
		$platform	= 'P';
		if		($this->fammerceMode || $this->storefammerceMode)	$platform	= 'F';
		elseif	($this->mobileMode || $this->storemobileMode)		$platform	= 'M';
		$params['platform']	= $platform;

		###
		$auth = $this->session->userdata('auth');
		if(isset($auth) && $auth['auth_yn']){
			$params['auth_type']	= $auth['namecheck_type'];
			$params['auth_code']	= $auth['namecheck_check'];
			if($params['auth_type'] != "safe"){//"ipin", "phone"

				/* 실명인증 중복 가입 체크 추가 leewh 2014-12-24 */
				$qry = "select count(*) as cnt from fm_member where auth_code='".$auth["namecheck_check"]."'";
				$query = $this->db->query($qry);
				$member = $query -> row_array();

				if($member["cnt"] > 0) {
					$callback = "parent.location.href = '/member/login?return_url=/mypage/myinfo'";
					$msg = "이미 가입된정보입니다. 로그인해주세요.";
					$this->session->unset_userdata('auth');
					if ($_SESSION['auth']) $_SESSION['auth']= '';
					openDialogAlert($msg,400,140,'parent',$callback);
					exit;
				}

				$params['auth_vno']		= $auth['namecheck_vno'];
			}else{
				$params['auth_vno']		= $auth['namecheck_key'];
			}
		}

		//초대
		$params['fb_invite']	= $this->session->userdata('fb_invite');

		$params['user_icon']	= ($_POST['user_icon'])?$_POST['user_icon']:1;//@2014-08-06 icon 

		$data = filter_keys($params, $this->db->list_fields('fm_member'));
		$result = $this->db->insert('fm_member', $data);
		$memberseq = $this->db->insert_id();
		###
		if(isset($_POST['mtype']) && $_POST['mtype']=='business'){
			$params['member_seq'] = $this->db->insert_id();
			if(isset($_POST['bphone']))  $params['bphone'] = implode("-",$_POST['bphone']);
			if(isset($_POST['bcellphone']))  $params['bcellphone'] = implode("-",$_POST['bcellphone']);
			if(isset($_POST['bzipcode']))  $params['bzipcode'] = implode("-",$_POST['bzipcode']);
			$bdata = filter_keys($params, $this->db->list_fields('fm_member_business'));
			$result = $this->db->insert('fm_member_business', $bdata);
		}

		### //추가정보 저장
		foreach ($label_pr as $k => $data){
			foreach ($data['value'] as $j => $subdata){
				$setdata['label_value']= $subdata;
				$setdata['label_sub_value']= $label_sub_pr[$k]['value'][$j];
				$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $k));
				$form_result = $query -> row_array();
				$setdata['label_title'] = $form_result['label_title'];
				$setdata['joinform_seq'] = $form_result['joinform_seq'];
				$setdata['member_seq'] = $memberseq;
				$setdata['regist_date'] = date('Y-m-d H:i:s');
			$result = $this->db->insert('fm_member_subinfo', $setdata);
			}
		}
		###

		### Private Encrypt
		$email = get_encrypt_qry('email');
		$cellphone = get_encrypt_qry('cellphone');
		$phone = get_encrypt_qry('phone');
		$sql = "update fm_member set {$email}, {$cellphone}, {$phone}, update_date = now() where member_seq = {$memberseq}";
		$this->db->query($sql);

		###
		if($result){

			###
			$app = config_load('member');

			//직접추천시
			$params['recommend']	= $params['recommend'];

			if($app['autoApproval']=='Y') {//자동승인인 경우

				$this->load->model('emoneymodel');
				$this->load->model('pointmodel');

				### 특정기간
				if($app['start_date'] && $app['end_date']){
					$today = date("Y-m-d");
					if($today>=$app['start_date'] && $today<=$app['end_date']){
						$app['emoneyJoin']	= $app['emoneyJoin_limit'];
						$app['pointJoin']	= $app['pointJoin_limit'];
					}
				}

				if($app['emoneyJoin']>0){
					$emoney['type']		= 'join';
					$emoney['emoney']	= $app['emoneyJoin'];
					$emoney['gb']		= 'plus';
					$emoney['memo']		= '회원 가입 적립금';
					$emoney['limit_date'] = get_emoney_limitdate('join');
					$this->membermodel->emoney_insert($emoney, $memberseq);
				}

				if($app['pointJoin']>0){
					$point['type']		= 'join';
					$point['point']		= $app['pointJoin'];
					$point['gb']		= 'plus';
					$point['memo']		= '회원 가입 포인트';
					$point['limit_date'] = get_point_limitdate('join');
					$this->membermodel->point_insert($point, $memberseq);
				}

				//추천시
				if($params['recommend'] &&  $params['recommend'] != $params['userid']){//본인추천체크
					$chk = get_data("fm_member",array("userid"=>$params['recommend'],"status"=>"done"));
					if(is_array($chk) && $chk[0]['member_seq']) {

						//추천받은자의 추천받은건수 증가 @2013-06-19
						$this->membermodel->member_recommend_cnt($chk[0]['member_seq']);

						//추천 받은 자 -> 제한함
						$todaymonth = date("Y-m");
						if($app['emoneyRecommend']>0) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$emrecommendtock = $this->emoneymodel->get_data($recommendtosc);//추천한 회원 적립금 지급여부

							$maxrecommend = ($app['emoneyLimit']*$app['emoneyRecommend']);

							if( $emrecommendtock['totalcnt'] < $app['emoneyLimit'] && $emrecommendtock['totalemoney'] <= $maxrecommend ) {
								$emoney['type']						= 'recommend_to';
								$emoney['emoney']				= $app['emoneyRecommend'];
								$emoney['gb']						= 'plus';
								$emoney['memo']					= '('.$params['userid'].') 추천 회원 적립금';
								$emoney['limit_date']				= get_emoney_limitdate('recomm');
								$emoney['member_seq_to']	= $memberseq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}

						if($app['pointRecommend']>0) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalepoint ';
							$pmrecommendtock = $this->pointmodel->get_data($recommendtosc);//추천한 회원 적립금 지급여부
							$maxrecommend = ($app['pointLimit']*$app['pointRecommend']);

							if( $pmrecommendtock['totalcnt'] < $app['pointLimit'] && $pmrecommendtock['totalepoint'] <= $maxrecommend ) {
								$point['type']							= 'recommend_to';
								$point['point']							= $app['pointRecommend'];
								$point['gb']							= 'plus';
								$point['memo']						= '('.$params['userid'].') 추천 회원 포인트';
								$point['limit_date']					= get_point_limitdate('recomm');
								$point['member_seq_to']		= $memberseq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}

						//추천한자(가입자)
						if($app['emoneyJoiner']>0) {
							unset($emoney);
							$emoney['type']							= 'recommend_from';
							$emoney['emoney']					= $app['emoneyJoiner'];
							$emoney['gb']							= 'plus';
							$emoney['memo']						= '['.$params['recommend'].'] 추천 적립금';
							$emoney['limit_date']					= get_emoney_limitdate('joiner');
							$emoney['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $memberseq);
						}
						if($app['pointJoiner']>0) {
							unset($point);
							$point['type']								= 'recommend_from';
							$point['point']								= $app['pointJoiner'];
							$point['gb']								= 'plus';
							$point['memo']							= '['.$params['recommend'].'] 추천 포인트';
							$point['limit_date']						= get_point_limitdate('joiner');
							$point['member_seq_to']			= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $memberseq);
						}
					}
				}

				//초대시
				if($params['fb_invite']) {
					$chk = get_data("fm_member",array("member_seq"=>$params['fb_invite']));
					if($chk[0]['member_seq']) {

						$fbuserprofile = $this->snssocial->facebooklogin();
						if($fbuserprofile['id']){
							$this->db->where('sns_f', $fbuserprofile['id']);
							$result = $this->db->update('fm_memberinvite', array("joinck"=>'1'));//가입여부 업데이트
						}

						//초대 한 자  -> 제한함
						$todaymonth = date("Y-m");
						if($app['emoneyInvited']>0) {
							$invitedtosc['whereis'] = ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$invitedtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$eminvitedtock = $this->emoneymodel->get_data($invitedtosc);//추천한 회원 적립금 지급여부
							$maxinvited = ($app['emoneyLimit_invited']*$app['emoneyInvited']);

							if( $eminvitedtock['totalcnt'] <= $app['emoneyLimit_invited'] && $eminvitedtock['totalemoney'] <= $maxinvited ) {
								unset($emoney);
								$emoney['type']							= 'invite_from';
								$emoney['emoney']					= $app['emoneyInvited'];
								$emoney['gb']							= 'plus';
								$emoney['memo']						= '초대 적립금';
								$emoney['limit_date']					= get_emoney_limitdate('invite_from');
								$emoney['member_seq_to']		= $memberseq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}
						if($app['pointInvited']>0){
							$invitedtosc['whereis'] = ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$invitedtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalpoint ';
							$pminvitedtock = $this->pointmodel->get_data($invitedtosc);//추천한 회원 적립금 지급여부
							$maxinvited = ($app['pointLimit_invited']*$app['pointInvited']);

							if( $pminvitedtock['totalcnt'] <= $app['pointLimit_invited'] && $pminvitedtock['totalpoint'] <= $maxinvited ) {
								unset($point);
								$point['type']							= 'invite_from';
								$point['point']							= $app['pointInvited'];
								$point['gb']							= 'plus';
								$point['memo']						= '초대 포인트';
								$point['limit_date']					= get_point_limitdate('invite_from');
								$point['member_seq_to']		= $memberseq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}

						//초대 받은 자(가입자)
						if($app['emoneyInvitees']>0){
							$emoney['type']							= 'invite_to';
							$emoney['emoney']					= $app['emoneyInvitees'];
							$emoney['gb']							= 'plus';
							$emoney['memo']						= '초대 회원 적립금';
							$emoney['limit_date']					= get_emoney_limitdate('invite_to');
							$emoney['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $memberseq);
						}

						if($app['pointInvitees']>0){
							unset($point);
							$point['type']								= 'invite_to';
							$point['point']								= $app['pointInvitees'];
							$point['gb']								= 'plus';
							$point['memo']							= '초대 회원 포인트';
							$point['limit_date']						= get_point_limitdate('invite_to');
							$point['member_seq_to']			= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $memberseq);
						}
					}
				}

			}else{
				$this->db->where('member_seq', $memberseq);
				$result = $this->db->update('fm_member', array("status"=>'hold'));
			}

			// 회원 가입 통계 저장
			$this->load->model('statsmodel');
			$this->statsmodel->insert_member_stats($memberseq,$_POST['birthday'],$_POST['address'],$_POST['sex']);


			//신규회원가입쿠폰발급
			$this->load->model('couponmodel');
			$sc['whereis'] = ' and (type="member" or type="member_shipping")  and issue_stop != 1 ';//발급중지가 아닌경우
			$coupon_multi_list = $this->couponmodel->get_coupon_multi_list($sc);
			$coupon_multicnt = 0;
			foreach($coupon_multi_list as $coupon_multi){  $coupon_multicnt++;
				$this->couponmodel->_members_downlod( $coupon_multi['coupon_seq'], $memberseq);
			}
			if($coupon_multicnt) $coupon_msg ="<br/>회원가입 쿠폰이 발행 되었습니다.";

			###
			$commonSmsData = array();
			if	($params['mtype'] == 'business' && $params['bcellphone']){
				$commonSmsData['join']['phone'][] = $params['bcellphone'];
				$commonSmsData['join']['params'][] = $params;
				$commonSmsData['join']['mid'][] = $params['userid'];

				//sendSMS($params['bcellphone'], 'join', $params['userid'], $params);
			}else{
				$commonSmsData['join']['phone'][] = $params['cellphone'];
				$commonSmsData['join']['params'][] = $params;
				$commonSmsData['join']['mid'][] = $params['userid'];

				//sendSMS($params['cellphone'], 'join', $params['userid'], $params);
			}
			commonSendSMS($commonSmsData);
			sendMail($params['email'], 'join', $params['userid'], $params);

			$this->session->unset_userdata('fb_invite');//초대회원초기화

			if(isset($_POST['mtype']) && $_POST['mtype']=='business'){
				$params['user_name'] = $params['bname']; // 기업회원일경우 이름 전달
			}

			if($app['autoApproval']=='Y') {//자동승인인 경우

				### LOG
				$qry = "update fm_member set login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$memberseq}'";
				$result = $this->db->query($qry);

				## 가입된 회원정보 세션용 재검색 :: 2015-01-26 lwh
				$query = "select A.*,B.business_seq,B.bname,C.group_name from fm_member A LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq left join fm_member_group C on C.group_seq=A.group_seq where A.member_seq = '".$memberseq."'";
				$query			= $this->db->query($query);
				$member_data	= $query->result_array();

				### SESSION
				$params					= $member_data[0];
				$params['member_seq']	= $memberseq;
				$this->create_member_session($params);
				//unset($_POST);
				//unset($params);
				if($_POST['layermode'] == 'layer' ){
					echo js("parent.openjoinokLayer('{$params['user_name']}');");
				}else{
					$params['user_name'] = urlencode($params['user_name']);
					$callback = "parent.location.href = '/member/register_ok?user_name={$params['user_name']}'";
					$msg = "가입 되었습니다.";
					$msg .= $coupon_msg;
					openDialogAlert($msg,400,140,'parent',$callback);
				}
			}else{
				if($_POST['layermode'] == 'layer' ){
					//echo js("parent.openjoinokLayer('{$params['user_name']}');");
					echo js("parent.location.href = '/main/index';");
				}else{
					$params['user_name'] = urlencode($params['user_name']);
					$callback = "parent.location.href = '/member/register_ok?user_name={$params['user_name']}'";
					$msg = "가입 되었습니다.";
					$msg .= $coupon_msg;
					openDialogAlert($msg,400,140,'parent',$callback);
				}
			}
		}
	}

	/**
	*
	* @
	*/
	public function create_member_session($data=array()){

		$this->load->helper('member');
		create_member_session($data);
		/**
		$data['rute'] = ($data['rute']!='f' && $data['sns_f'])?'facebook':$data['rute'];

		// 사업자 회원일 경우 업체명->이름
		if($data['business_seq']){
			$data['user_name'] = $data['bname'];
		}
		$member_data = array(
			'member_seq'		=> $data['member_seq'],
			'userid'			=> $data['userid'],
			'user_name'			=> $data['user_name'],
			'birthday'			=> $data['birthday'],
			'sex'				=> $data['sex'],
			'rute'				=> substr($data['rute'],0,1)
		);
		$tmp = config_load('member');
		if(isset($tmp['sessLimit']) && $tmp['sessLimit']=='Y'){
			$limit = 60 * $tmp['sessLimitMin'];
			$this->session->sess_expiration = $limit;
		}
		$this->session->set_userdata(array('user'=>$member_data));
		**/
	}


	###
	public function myinfo_modify(){

		$this->load->model('ssl');
		$this->ssl->decode();


		$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보


		if( $this->isdemo['isdemo'] && $this->mdata['userid'] == $this->isdemo['isdemoid'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		$joinform = config_load('joinform');
		###
		$mtype = 'member';
		if($this->mdata['business_seq']){
			$mtype = 'business';
		}
		###

		$label_pr = $_POST['label'];
		$label_sub_pr = $_POST['labelsub'];
		$label_required = $_POST['required'];

		### Validation
		if( $mtype == 'member' ) {
			//$this->validation->set_rules('user_name', '이름','trim|required|max_length[32]|xss_clean');
		}

		if( $_POST['rute'] == 'none' ) {
			$this->validation->set_rules('old_password', '비밀번호','trim|required|max_length[32]|xss_clean');
		}
		if(!empty($_POST['anniversary'][0]) && !empty($_POST['anniversary'][1]))
			$_POST['anniversary'] = implode("-",$_POST['anniversary']);
		else
			$_POST['anniversary'] = '';

		if(isset($_POST['email'])) $_POST['email'] = implode("@",$_POST['email']);
		if($_POST['email'] == '@' ) $_POST['email'] = '';

		if ( $joinform['email_userid'] == 'Y' && !$_POST['email'] ) {
			$_POST['email'] = $_POST['userid'];
		}

		### MEMBER
		if($mtype=='member'){

			if($joinform['email_use']=='Y'){
				if($joinform['bmail_required']=='Y') {
					$this->validation->set_rules('email', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email'])) {
					$this->validation->set_rules('email', '이메일','trim|max_length[64]|valid_email|xss_clean');
				}
			}

			if($joinform['user_name_use']=='Y'){
				if($joinform['user_name_required']=='Y') $this->validation->set_rules('user_name', '이름','trim|required|max_length[32]|xss_clean');
				else $this->validation->set_rules('user_name', '이름','trim|max_length[32]|xss_clean');
			}
			if($joinform['phone_use']=='Y'){
				if($joinform['phone_required']=='Y') $this->validation->set_rules('phone[]', '연락처','trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('phone[]', '연락처','trim|max_length[4]|xss_clean');
			}
			if($joinform['cellphone_use']=='Y'){
				if($joinform['cellphone_required']=='Y') $this->validation->set_rules('cellphone[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('cellphone[]', '휴대폰번호','trim|max_length[4]|xss_clean');
			}
			if($joinform['address_use']=='Y'){
				if($joinform['address_required']=='Y'){
					$this->validation->set_rules('zipcode[]', '우편번호','trim|required|max_length[3]|numeric|xss_clean');
					$this->validation->set_rules('address', '주소','trim|required|max_length[100]|xss_clean');
					$this->validation->set_rules('address_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
				else{
					$this->validation->set_rules('zipcode[]', '우편번호','trim|max_length[3]|xss_clean');
					$this->validation->set_rules('address', '주소','trim|max_length[100]|xss_clean');
					$this->validation->set_rules('address_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['birthday_use']=='Y'){
				if($joinform['birthday_required']=='Y') $this->validation->set_rules('birthday', '생일','trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('birthday', '생일','trim|max_length[10]|xss_clean');
			}
			if($joinform['anniversary_use']=='Y'){
				if($joinform['anniversary_required']=='Y') $this->validation->set_rules('anniversary', '기념일','trim|required|max_length[5]|xss_clean');
				else  $this->validation->set_rules('anniversary', '기념일','trim|max_length[5]|xss_clean');
			}
			if($joinform['nickname_use']=='Y'){
				if($joinform['nickname_required']=='Y') $this->validation->set_rules('nickname', '닉네임','trim|required|max_length[10]|xss_clean');
				else  $this->validation->set_rules('nickname', '닉네임','trim|max_length[10]|xss_clean');
			}
			if($joinform['sex_use']=='Y'){
				if($joinform['sex_required']=='Y') $this->validation->set_rules('sex', '성별','trim|required|max_length[6]|xss_clean');
				else  $this->validation->set_rules('sex', '성별','trim|max_length[6]|xss_clean');
			}
		}

		### BUSINESS
		if($mtype=='business'){
			if($joinform['bemail_use']=='Y'){
				if($joinform['bemail_required']=='Y') {
					$this->validation->set_rules('email', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
				}elseif( !empty($_POST['email']) ) {
					$this->validation->set_rules('email', '이메일','trim|max_length[64]|valid_email|xss_clean');
				}
			}

			if($joinform['bname_use']=='Y'){
				if($joinform['bname_required']=='Y') $this->validation->set_rules('bname', '업체명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bname', '업체명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bceo_use']=='Y'){
				if($joinform['bceo_required']=='Y') $this->validation->set_rules('bceo', '대표자명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bceo', '대표자명','trim|max_length[32]|xss_clean');
			}

			if($joinform['bno_use']=='Y'){
				if($joinform['bno_required']=='Y') $this->validation->set_rules('bno', '사업자 등록번호','trim|required|max_length[12]|xss_clean');
				else  $this->validation->set_rules('bno', '사업자 등록번호','trim|max_length[12]|xss_clean');

				###
				$return_result = $this->bno_chk('re_chk');
				if(!$return_result['return'] && $this->mdata['bno'] != $_POST['bno']){
					$callback = "if(parent.document.getElementsByName('bno')[0]) parent.document.getElementsByName('bno')[0].focus();";
					openDialogAlert($return_result['return_result'],400,140,'parent',$callback);
					exit;
				}
			}
			if($joinform['bitem_use']=='Y'){
				if($joinform['bitem_required']=='Y') {
					$this->validation->set_rules('bitem', '업태','trim|required|max_length[40]|xss_clean');
					$this->validation->set_rules('bstatus', '종목','trim|required|max_length[40]|xss_clean');
				}
				else{
					$this->validation->set_rules('bitem', '업태','trim|max_length[40]|xss_clean');
					$this->validation->set_rules('bstatus', '종목','trim|max_length[40]|xss_clean');
				}
			}
			if($joinform['badress_use']=='Y'){
				if($joinform['badress_required']=='Y'){
					$this->validation->set_rules('bzipcode[]', '우편번호','trim|required|max_length[3]|numeric|xss_clean');
					$this->validation->set_rules('baddress', '주소','trim|required|max_length[100]|xss_clean');
					$this->validation->set_rules('baddress_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
				else{
					$this->validation->set_rules('bzipcode[]', '우편번호','trim|max_length[3]|numeric|xss_clean');
					$this->validation->set_rules('baddress', '주소','trim|max_length[100]|xss_clean');
					$this->validation->set_rules('baddress_detail', '상세 주소','trim|max_length[100]|xss_clean');
				}
			}
			if($joinform['bperson_use']=='Y'){
				if($joinform['bperson_required']=='Y') $this->validation->set_rules('bperson', '담당자 명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bperson', '담당자 명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bpart_use']=='Y'){
				if($joinform['bpart_required']=='Y') $this->validation->set_rules('bpart', '담당자 부서명','trim|required|max_length[32]|xss_clean');
				else  $this->validation->set_rules('bpart', '담당자 부서명','trim|max_length[32]|xss_clean');
			}
			if($joinform['bphone_use']=='Y'){
				if($joinform['bphone_required']=='Y') $this->validation->set_rules('bphone[]', '전화번호','trim|required|max_length[4]|numeric|xss_clean');
				else $this->validation->set_rules('bphone[]', '전화번호','trim|max_length[4]|xss_clean');
			}
			if($joinform['bcellphone_use']=='Y'){
				if($joinform['bcellphone_required']=='Y') $this->validation->set_rules('bcellphone[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');
				else  $this->validation->set_rules('bcellphone[]', '휴대폰번호','trim|max_length[4]|xss_clean');
			}
		}

		//넘어온 추가항목 seq
		foreach($label_pr as $l => $data){$label_arr[]=$l;}
		//추가항목 공백체크
		foreach($label_required as $v){
			if(!in_array($v,$label_arr)){
				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
				openDialogAlert('체크된 항목은 필수항목입니다.',400,140,'parent',$callback);
				exit;
			}else{
				$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $v));
				$form_result = $query -> row_array();
				$label_title = $form_result['label_title'];
				$this->validation->set_rules('label['.$v.'][value][]', $label_title,'trim|required|xss_clean');
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$params = $_POST;
		$seq	= $_POST['seq'];
		if( $_POST['rute'] == 'none' ) {

			$query = "select password(?) pass,old_password(?) old_pass";
			$query = $this->db->query($query,array($_POST['old_password'],$_POST['old_password']));
			$data = $query->row_array();
			$str_md5 = md5($_POST['old_password']);
			$str_password = $data['pass'];
			$str_oldpassword = $data['old_pass'];
			$str_sha_md5 = hash('sha256',$str_md5);
			$str_sha_password = hash('sha256',$data['pass']);
			$str_sha_oldpassword = hash('sha256',$data['old_pass']);
			$query = "select count(*) cnt from fm_member where member_seq=? and (`password`=? or `password`=? or `password`=? or `password`=? or `password`=? or `password`=?)";
			$query = $this->db->query($query,array($seq,$str_md5,$str_password,$str_oldpassword,$str_sha_md5,$str_sha_password,$str_sha_oldpassword));
			
			$data = $query->row_array();
			$count = $data['cnt'];

			if($count<1){
				$callback = "if(parent.document.getElementsByName('old_password')[0]) parent.document.getElementsByName('old_password')[0].focus();";
				openDialogAlert('기존 비밀번호가 올바르지 않습니다.',400,140,'parent',$callback);
				exit;
			}
		}
		###
		if(isset($_POST['new_password']) && $_POST['new_password']){
			if(strlen($_POST['new_password'])<6 || strlen($_POST['new_password'])>20){
				$callback = "if(parent.document.getElementsByName('required')[0]) parent.document.getElementsByName('required')[0].focus();";
				openDialogAlert("비밀번호 글자 제한 수를 맞춰주세요.",400,140,'parent',$callback);
				exit;
			}
		}
		if(isset($_POST['phone']))  $params['phone'] = implode("-",$_POST['phone']);
		if(isset($_POST['cellphone']))  $params['cellphone'] = implode("-",$_POST['cellphone']);
		if(isset($_POST['zipcode']))  $params['zipcode'] = implode("-",$_POST['zipcode']);
		if(isset($_POST['new_password']) && $_POST['new_password'])  $params['password'] = hash('sha256',md5($_POST['new_password']));
		$params['mailing'] = if_empty($params, 'mailing', 'n');
		$params['sms'] = if_empty($params, 'sms', 'n');

		$params['user_icon']	= if_empty($params, 'user_icon', '1');;//@2014-08-06 icon
		$data = filter_keys($params, $this->db->list_fields('fm_member'));
		//print_r($data);
		$result = $this->db->update('fm_member',$data,array('member_seq'=>$seq));
		###

		### BUSINESS CHK
		if($mtype=='business') {
			if(isset($_POST['bphone']))  $params['bphone'] = implode("-",$_POST['bphone']);
			if(isset($_POST['bcellphone']))  $params['bcellphone'] = implode("-",$_POST['bcellphone']);
			if(isset($_POST['bzipcode']))  $params['bzipcode'] = implode("-",$_POST['bzipcode']);
			$params['address_type'] = $_POST['baddress_type'];
			$params['address'] = $_POST['baddress'];
			$params['address_street'] = $_POST['baddress_street'];
			$data = filter_keys($params, $this->db->list_fields('fm_member_business'));
			//print_r($data);
			if($this->mdata['business_seq']){
				$this->db->where('business_seq', $this->mdata['business_seq']);
				$result = $this->db->update('fm_member_business', $data);
			}else{
				$data['member_seq'] = $seq;
				$result = $this->db->insert('fm_member_business', $data);
			}
		}

		### //추가정보 저장
		if($label_pr){
			$this->db->delete('fm_member_subinfo', array('member_seq'=>$seq));
			foreach ($label_pr as $k => $data){
				foreach ($data['value'] as $j => $subdata){
					$setdata['label_value']= $subdata;
					$setdata['label_sub_value']= $label_sub_pr[$k]['value'][$j];
					$query = $this->db->get_where('fm_joinform',array('joinform_seq'=> $k));
					$form_result = $query -> row_array();
					$setdata['label_title'] = $form_result['label_title'];
					$setdata['joinform_seq'] = $form_result['joinform_seq'];
					$setdata['member_seq'] = $seq;
					$setdata['regist_date'] = date('Y-m-d H:i:s');
				$result = $this->db->insert('fm_member_subinfo', $setdata);
				}
			}
		}
		###

		###
		$email = get_encrypt_qry('email');
		$cellphone = get_encrypt_qry('cellphone');
		$phone = get_encrypt_qry('phone');
		$sql = "update fm_member set {$email}, {$cellphone}, {$phone}, update_date = now() where member_seq = {$seq}";
		//echo $sql;
		$result = $this->db->query($sql);

		//echo $result;

		###
		if($result){
			unset($_POST);
			$callback = "parent.location.href = '../mypage'";
			openDialogAlert("수정 되었습니다.",400,140,'parent',$callback);
		}
	}


	###
	public function withdrawal(){
		$this->validation->set_rules('reason', '탈퇴사유','trim|required|max_length[30]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$data = $this->membermodel->get_member_data($this->userInfo['member_seq']);

		if( $this->isdemo['isdemo'] && $data['userid'] == $this->isdemo['isdemoid'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		### withdrawal_insert
		$params = $_POST;
		$params['member_seq']	= $this->userInfo['member_seq'];
		$params['regist_date']	= date('Y-m-d H:i:s');
		$params['regist_ip']	= $_SERVER['REMOTE_ADDR'];
		$result = $this->membermodel->set_withdrawal_admin($params);//탈퇴

		###
		$commonSmsData = array();
		$commonSmsData['withdrawal']['phone'][] = $data['cellphone'];
		$commonSmsData['withdrawal']['params'][] = $params;
		$commonSmsData['withdrawal']['mid'][] = $data['userid'];
		commonSendSMS($commonSmsData);

		//sendSMS($data['cellphone'], 'withdrawal', $data['userid'], $params);
		sendMail($data['email'], 'withdrawal', $data['userid'], $params);


		### logout
		$callback = "parent.location.href = '../login_process/logout'";
		openDialogAlert("정상적으로 회원 탈퇴가 이뤄졌습니다.<br>\\n그 동안 이용해 주셔서 감사합니다.",400,140,'parent',$callback);
	}

/**
** 본인인증/안심체크/아이핀 실명인증 체크 관련
**/
	public function niceid2_return(){

		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');
		$findtypess = $this->session->userdata('findtypess');
		$findidss = $this->session->userdata('findidss');

		if(!extension_loaded('CPClient')) {
			dl('CPClient.' . PHP_SHLIB_SUFFIX);
		}
		$module = 'CPClient';

		//**************************************** 필수 수정값 ***************************************************************************
		$sSiteCode 	   = $realname['realnameId'];							// 안심체크 사이트 코드
		$sSitePassword = $realname['realnamePwd'];							// 안심체크 사이트 패스워드
		$sIPINSiteCode = $realname['ipinSikey'];							// 아이핀사이트 코드
		$sIPINPassword = $realname['ipinKeyString'];						// 아이핀사이트 패스워드
		

		//***********************************************************************8******************************************************

		$enc_data = $_POST["enc_data"];								// NICE신용평가정보로부터 받은 사용자 암호화된 결과 데이타

		///////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
		if(preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match)) {echo "입력 값 확인이 필요합니다"; exit;}
		if(base64_encode(base64_decode($enc_data))!=$enc_data) {echo "입력 값 확인이 필요합니다"; exit;}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////

		if ($enc_data != "") {

			$function = 'get_decode_data';
			if (extension_loaded($module)) {
				$plaindata = $function($sSiteCode,$sSitePassword, $enc_data);
			} else {
				$plaindata = "Module get_response_data is not compiled into PHP";
			}

			if ($plaindata == -1){
				$returnMsg  = "암/복호화 시스템 오류";
			}else if ($plaindata == -4){
				$returnMsg  = "복호화 처리 오류";
			}else if ($plaindata == -5){
				$returnMsg  = "HASH값 불일치 - 복호화 데이터는 리턴됨";
			}else if ($plaindata == -6){
				$returnMsg  = "복호화 데이터 오류";
			}else if ($plaindata == -9){
				$returnMsg  = "입력값 오류";
			}else if ($plaindata == -12){
				$returnMsg  = "사이트 비밀번호 오류";
			}else{

				// 복호화가 정상적일 경우 데이터를 파싱합니다.
				$returnMsg  = "본인인증이 확인되었습니다.";
				//$ciphertime = `$cb_encode_path CTS $sSiteCode $sSitePassword $enc_data`;	// 암호화된 결과 데이터 검증 (복호화한 시간획득)
				 $function = 'get_cipher_datetime';// 암호화된 결과 데이터 검증 (복호화한 시간획득)
				if (extension_loaded($module)) {
					$ciphertime = $function($sSiteCode,$sSitePassword,$enc_data);
				} else {
					$ciphertime = "Module get_cipher_datetime is not compiled into PHP";
				}


				$sRequestNO = GetValueNameCheck($plaindata , "REQ_SEQ");
				$sResult = GetValueNameCheck($plaindata , "NC_RESULT");

				if(strcmp($_SESSION["REQ_SEQ"] , $sRequestNO) )
				{
					$sRequestNO = "";
					$err_msg = "세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.";
					pageClose($err_msg);
					exit;
				}else{

					$auth_data["auth_yn"] = "Y";
					$auth_data["namecheck_type"] = "safe";
					$auth_data["namecheck_name"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "NAME"));
					$auth_data["namecheck_sex"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "GENDER"));
					$auth_data["namecheck_birth"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "BIRTHDATE"));
					$auth_data["namecheck_key"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "SAFEID"));
					$auth_data["namecheck_check"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "IPIN_DI"));
					$auth_data["namecheck_vno"] = iconv("euc-kr", "utf-8", GetValueNameCheck($plaindata , "VNO_NUM"));


					if(isset($_GET['intro']) && $_GET['intro']=='Y') {//성인인증페이지
						if($auth_data["namecheck_birth"]){
							$adult = date("Y") - substr($auth_data["namecheck_birth"], 0, 4) + 1;
						}
						if($adult>19){
							$auth_intro_data = array('auth_intro_type'=>'auth', 'auth_intro_yn'=>'Y');
							$this->session->sess_expiration = (60 * 5);
							$this->session->set_userdata(array('auth_intro'=>$auth_intro_data));
							$msg = "성인인증이 성공적으로 완료되었습니다.";
						}else{
							$err_msg = "미성년자는 이용할 수 없습니다.";
							pageClose($err_msg);
							exit;
						}

						pageLocation('/main', $msg, 'opener');
						pageClose();
						exit;

					}elseif(isset($_GET['findidpw']) && $_GET['findidpw']=='Y') {//아이디/패스워드 찾기
						$this->_findidpwresult($auth_data, $plaindata);
					}else{//가입페이지

						$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
						$query = $this->db->query($qry);
						$member = $query -> row_array();

						if($member["cnt"] > 0){
							$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
							$msg = "이미 가입된 정보입니다.";
							pageLocation($url, $msg, 'opener');
							pageClose();
							exit;
						}

						$this->session->sess_expiration = (60 * 5);
						$this->session->set_userdata(array('auth'=>$auth_data));

						pageLocation('/member/agreement?authok=1', "", 'opener');
						pageClose();
						exit;
					}
				}
			}

			$msg = "잠시 후 다시 시도하여주십시오.<br/>오류가 계속 될 경우 고객센터로 문의하세요.";
			pageClose($msg);
			exit;
		}
	}

	public function niceid_phone_return(){

		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');
		$findtypess = $this->session->userdata('findtypess');
		$findidss = $this->session->userdata('findidss');

		if(!extension_loaded('CPClient')) {
			dl('CPClient.' . PHP_SHLIB_SUFFIX);
		}
		$module = 'CPClient';


		//**************************************** 필수 수정값 ***************************************************************************
		$sSiteCode 				= $realname['realnamephoneSikey'];			// 본인인증 사이트 코드
		$sSitePassword		= $realname['realnamePhoneSipwd'];			// 본인인증 사이트 패스워드
		$authtype = "M";      	// 없으면 기본 선택화면, X: 공인인증서, M: 핸드폰, C: 카드
		$popgubun 	= "Y";		//Y : 취소버튼 있음 / N : 취소버튼 없음
		$customize 	= "";			//없으면 기본 웹페이지 / Mobile : 모바일페이지

		//$cb_encode_path	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/CPClient";	// 암호화 프로그램의 위치 (절대경로+모듈명)_Linux ..
		//$sType			= "REQ";
		//$reqseq = `$cb_encode_path SEQ $sSiteCode`;

		//$returnurl		= "http://".$_SERVER["HTTP_HOST"]."/member_process/niceid_phone_return";	// 성공시 이동될 URL
		//$errorurl		= "http://".$_SERVER["HTTP_HOST"]."/member_process/niceid_phone_return";		// 실패시 이동될 URL

		//*************************************************************************8******************************************************

		$enc_data = $_POST["EncodeData"];		// 암호화된 결과 데이타
		$sReserved1 = $_POST['param_r1'];
		$sReserved2 = $_POST['param_r2'];
		$sReserved3 = $_POST['param_r3'];

		//////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
		if(preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match)) {echo "입력 값 확인이 필요합니다 : ".$match[0]; exit;} // 문자열 점검 추가. 
		if(base64_encode(base64_decode($enc_data))!=$enc_data) {echo "입력 값 확인이 필요합니다"; exit;}
		
		if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved1, $match)) {echo "문자열 점검 : ".$match[0]; exit;}
		if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved2, $match)) {echo "문자열 점검 : ".$match[0]; exit;}
		if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved3, $match)) {echo "문자열 점검 : ".$match[0]; exit;}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////


		if ($enc_data != "") {

			//$plaindata = `$cb_encode_path DEC $sSiteCode $sSitePassword $enc_data`;		// 암호화된 결과 데이터의 복호화

			$function = 'get_decode_data';// 암호화된 결과 데이터의 복호화
			if (extension_loaded($module)) {
				$plaindata = $function($sSiteCode, $sSitePassword, $enc_data);
			} else {
				$plaindata = "Module get_response_data is not compiled into PHP";
			}


			if ($plaindata == -1){
				$returnMsg  = "암/복호화 시스템 오류";
			}else if ($plaindata == -4){
				$returnMsg  = "복호화 처리 오류";
			}else if ($plaindata == -5){
				$returnMsg  = "HASH값 불일치 - 복호화 데이터는 리턴됨";
			}else if ($plaindata == -6){
				$returnMsg  = "복호화 데이터 오류";
			}else if ($plaindata == -9){
				$returnMsg  = "입력값 오류";
			}else if ($plaindata == -12){
				$returnMsg  = "사이트 비밀번호 오류";
			}else{
				$returnMsg  = "본인인증이 확인되었습니다.";

				// 복호화가 정상적일 경우 데이터를 파싱합니다.
 				//$ciphertime = `$cb_encode_path CTS $sSiteCode $sSitePassword $enc_data`;	// 암호화된 결과 데이터 검증 (복호화한 시간획득)

				$function = 'get_cipher_datetime';// 암호화된 결과 데이터 검증 (복호화한 시간획득)
				if (extension_loaded($module)) {
					$ciphertime = $function($sitecode,$sitepasswd,$enc_data);
				} else {
					$ciphertime = "Module get_cipher_datetime is not compiled into PHP";
				}


				$requestnumber = GetValueNameCheck($plaindata , "REQ_SEQ");
				$responsenumber = GetValueNameCheck($plaindata , "RES_SEQ");
				$authtype = GetValueNameCheck($plaindata , "AUTH_TYPE");
				$name = GetValueNameCheck($plaindata , "NAME");
				$birthdate = GetValueNameCheck($plaindata , "BIRTHDATE");
				$gender = GetValueNameCheck($plaindata , "GENDER");
				$nationalinfo = GetValueNameCheck($plaindata , "NATIONALINFO");	//내/외국인정보(사용자 매뉴얼 참조)
				$dupinfo = GetValueNameCheck($plaindata , "DI");
				$conninfo = GetValueNameCheck($plaindata , "CI");
				$errcode = GetValueNameCheck($plaindata , "ERR_CODE");

				if(strcmp($_SESSION["REQ_SEQ_P"], $requestnumber) != 0  || !$dupinfo)
				{

					$requestnumber = "";
					$responsenumber = "";
					$authtype = "";
					$name = "";
					$birthdate = "";
					$gender = "";
					$nationalinfo = "";
					$dupinfo = "";
					$conninfo = "";

					$msg = "세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.";
					pageClose($msg);
					exit;
				/**}elseif($errcode){
					$msg = "잠시 후 다시 시도하여주십시오.<br/>오류가 계속 될 경우 고객센터로 문의하세요.";
					pageClose($msg);
					exit;
				**/
				}else{
					/**
					echo "[실명확인결과 : ".$sResult."]<br>";
					echo "[이름 : ".iconv("euc-kr", "utf-8", $name)."]<br>";
					echo "[성별 : ".$gender."]<br>";
					echo "[생년월일 : ".$birthdate."]<br>";
					echo "[내/외국인정보 : ".$nationalinfo."]<br>";

					echo "[DI(64 byte) : ".$dupinfo."]<br>";
					echo "[CI(88 byte) : ".$conninfo."]<br>";

					echo "[요청고유번호 : ".$requestnumber."]<br>";
					echo "[RESERVED1 : ".GetValueNameCheck($plaindata , "RESERVED1")."]<br>";
					echo "[RESERVED2 : ".GetValueNameCheck($plaindata , "RESERVED2")."]<br>";
					echo "[RESERVED3 : ".GetValueNameCheck($plaindata , "RESERVED3")."]<br>";
					**/

					$auth_data["auth_yn"] = "Y";
					$auth_data["namecheck_type"] = "phone";
					$auth_data["namecheck_name"] = iconv("euc-kr", "utf-8", $name);
					$auth_data["namecheck_sex"] = iconv("euc-kr", "utf-8", $gender);
					$auth_data["namecheck_birth"] = iconv("euc-kr", "utf-8", $birthdate);

					$auth_data["namecheck_check"] = iconv("euc-kr", "utf-8", $dupinfo);//중복체크용
					$auth_data["namecheck_vno"] = iconv("euc-kr", "utf-8", $conninfo);//주민등록번호와고유키

					if(isset($_GET['intro']) && $_GET['intro']=='Y') {
						if($auth_data["namecheck_birth"]){
							$adult = date("Y") - substr($auth_data["namecheck_birth"], 0, 4) + 1;
						}
						if($adult>19){
							$auth_intro_data = array('auth_intro_type'=>'auth', 'auth_intro_yn'=>'Y');
							$this->session->sess_expiration = (60 * 5);
							$this->session->set_userdata(array('auth_intro'=>$auth_intro_data));
							$msg = "성인인증이 성공적으로 완료되었습니다.";
						}else{
							$msg = "미성년자는 이용할 수 없습니다.";
							pageClose($msg);
							exit;
						}

						pageLocation('/main', $msg, 'opener');
						pageClose();
						exit;
					}elseif(isset($_GET['findidpw']) && $_GET['findidpw']=='Y') {//아이디/패스워드 찾기
						$this->_findidpwresult($auth_data);
					}else{//가입페이지
						if( !$auth_data["namecheck_check"] ){
							$msg = "세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다.";
							pageClose($msg);
							exit;
						}
						$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
						$query = $this->db->query($qry);
						$member = $query -> row_array();

						if($member["cnt"] > 0) {

							$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
							$msg = "이미 가입된 정보입니다.";
							pageLocation($url, $msg, 'opener');
							pageClose();
							exit;
						}

						$this->session->sess_expiration = (60 * 5);
						$this->session->set_userdata(array('auth'=>$auth_data));
						pageLocation('/member/agreement?authok=1', "", 'opener');
						pageClose();
						exit;
					}
				}


			}
			$msg = "잠시 후 다시 시도하여주십시오.<br/>오류가 계속 될 경우 고객센터로 문의하세요.";
			pageClose($msg);
			exit;

		} else {
			$sRtnMsg = "처리할 암호화 데이타가 없습니다.";
		}

		pageClose($sRtnMsg);
		exit;
	}

	public function ipin_chk(){
		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');
		$findtypess = $this->session->userdata('findtypess');
		$findidss = $this->session->userdata('findidss');

		if(!extension_loaded('IPINClient')) {
			dl('IPINClient.' . PHP_SHLIB_SUFFIX);
		}
		$module = 'IPINClient';

		$sSiteCode		= $realname['ipinSikey'];
		$sSitePw		= $realname['ipinKeyString'];

		$sEncData					= "";			// 암호화 된 사용자 인증 정보
		$sDecData					= "";			// 복호화 된 사용자 인증 정보

		$sRtnMsg					= "";			// 처리결과 메세지
		$sModulePath	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/IPINClient";

		$sEncData = $_POST['enc_data'];

		//////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
		if(preg_match('~[^0-9a-zA-Z+/=]~', $sEncData, $match)) {echo "입력 값 확인이 필요합니다"; exit;}
		if(base64_encode(base64_decode($sEncData))!=$sEncData) {echo "입력 값 확인이 필요합니다!"; exit;}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////

		$sCPRequest = $_SESSION['CPREQUEST'];

		if ($sEncData != "") {

			//$sDecData = `$sModulePath RES $sSiteCode $sSitePw $sEncData`;

			// 사용자 정보를 복호화 합니다.
			$function = 'get_response_data';
				if (extension_loaded($module)) {
					$sDecData = $function($sSiteCode, $sSitePw, $sEncData);
				} else {
					$sDecData = "Module get_response_data is not compiled into PHP";
				}


			if ($sDecData == -9) {
				$sRtnMsg = "입력값 오류 : 복호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.";
			} else if ($sDecData == -12) {
				$sRtnMsg = "NICE신용평가정보에서 발급한 개발정보가 정확한지 확인해 보세요.";
			} else {

				$arrData = split("\^", $sDecData);
				$iCount = count($arrData);

				if ($iCount >= 5) {

					$strResultCode	= $arrData[0];			// 결과코드
					if ($strResultCode == 1) {
						$strCPRequest	= $arrData[8];			// CP 요청번호

						if ($sCPRequest == $strCPRequest) {

							$sRtnMsg = "사용자 인증 성공";

							$strVno      		= $arrData[1];	// 가상주민번호 (13자리이며, 숫자 또는 문자 포함)
							$strUserName		= $arrData[2];	// 이름
							$strDupInfo			= $arrData[3];	// 중복가입 확인값 (64Byte 고유값)
							$strAgeInfo			= $arrData[4];	// 연령대 코드 (개발 가이드 참조)
							$strGender			= $arrData[5];	// 성별 코드 (개발 가이드 참조)
							$strBirthDate		= $arrData[6];	// 생년월일 (YYYYMMDD)
							$strNationalInfo	= $arrData[7];	// 내/외국인 정보 (개발 가이드 참조)

							$auth_data["auth_yn"] = "Y";
							$auth_data["namecheck_type"] = "ipin";
							$auth_data["namecheck_name"] = iconv("euc-kr", "utf-8", $strUserName);
							$auth_data["namecheck_sex"] = iconv("euc-kr", "utf-8", $strGender);
							$auth_data["namecheck_birth"] = iconv("euc-kr", "utf-8", $strBirthDate);
							$auth_data["namecheck_check"] = iconv("euc-kr", "utf-8", $strDupInfo);
							$auth_data["namecheck_vno"] = iconv("euc-kr", "utf-8", $strVno);

							if(isset($_GET['intro']) && $_GET['intro']=='Y'){
								if($strAgeInfo==7){
									$auth_intro_data = array('auth_intro_type'=>'auth', 'auth_intro_yn'=>'Y');
									$this->session->sess_expiration = (60 * 5);
									$this->session->set_userdata(array('auth_intro'=>$auth_intro_data));
									$msg = "성인인증이 성공적으로 완료되었습니다.";
								}else{
									$msg = "미성년자는 이용할 수 없습니다.";
									pageClose($msg);
									exit;
								}

								pageLocation('/main', $msg, 'opener');
								pageClose();
								exit;
							}elseif(isset($_GET['findidpw']) && $_GET['findidpw']=='Y') {//아이디/패스워드 찾기
								$this->_findidpwresult($auth_data,  $arrData);
							}else{//가입페이지
								$qry = "select count(*) as cnt from fm_member where auth_code='".$auth_data["namecheck_check"]."'";
								$query = $this->db->query($qry);
								$member = $query -> row_array();

								if($member["cnt"] > 0){
									$url = "/member/login?return_url=" . urlencode("/mypage/myinfo");
									$msg = "이미 가입된 정보입니다.";
									pageLocation($url, $msg, 'opener');
									pageClose();
									exit;
								}

								$this->session->sess_expiration = (60 * 5);
								$this->session->set_userdata(array('auth'=>$auth_data));

								pageLocation('/member/agreement?authok=1', "", 'opener');
								pageClose();
								exit;
							}
						} else {
							$sRtnMsg = "CP 요청번호 불일치 : 세션에 넣은 $sCPRequest 데이타를 확인해 주시기 바랍니다.";
						}
					} else {
						$sRtnMsg = "리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요. [$strResultCode]";
					}

				} else {
					$sRtnMsg = "리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요.";
				}

			}
		} else {
			$sRtnMsg = "처리할 암호화 데이타가 없습니다.";
		}

		pageClose($sRtnMsg);
		exit;
	}


	//아이디/패스워드찾기 완료화면 구성
	public function _findidpwresult($auth_data, $arrData = null) {

		$smsauth = config_load('master');//SMS사용시

		if( $auth_data ) {
			$qry = "select count(*) as cnt, userid, member_seq, rute from fm_member where ";
			if( $this->session->userdata('findtypess') == 'pw'){//비밀번호찾기
				$qry .= " rute = 'none' and   auth_code='".$auth_data["namecheck_check"]."' ";
			}else{
				$qry .= " auth_code='".$auth_data["namecheck_check"]."' ";
			}
			$qry .= " and auth_type != 'none' ";
			$query = $this->db->query($qry);
			$success = $query -> row_array();
			$success['error'] = false;
			$success['errorid'] = false;
			if($success["cnt"] > 0) {

				if( $this->session->userdata('findidss') ) {
					if( $this->session->userdata('findidss') != $success["userid"] ) {
						$success['error']		= true;
						$success['errorid']	= true;
					}
				}
			}else{
				$success['error'] = true;
			}
		}

		$scdocument = "top.opener.document";
		$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
		$scripts[] = "<script type='text/javascript'  charset='utf-8'>";
		$scripts[] = "$(function() {";


		if( $this->session->userdata('findtypess') == 'pw'){//비밀번호찾기

			$scripts[] = '$("#findidfromlay",'.$scdocument.').show();';
			$scripts[] = '$("#findidresultlay",'.$scdocument.').hide();';

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
				if( $success['error'] === false ){
					unset($params['password']);
					$this->findpw = chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).substr(mktime()*2,1,4);
					$scripts[] = '$(".findpwresultok1",'.$scdocument.').show();';
					$scripts[] = '$("#findpwlay1",'.$scdocument.').text("'.($this->findpw).'");';

					$this->findpw = hash('sha256',md5($this->findpw));
					$sql = "update fm_member set password = ?, update_date = now() where member_seq = ?";
					$this->db->query($sql,array($this->findpw,$success["member_seq"]));
				}elseif( $success['errorid'] ) {
					$scripts[] = '$(".findpwresultfalse2",'.$scdocument.').show();';
				}else{
					$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').show();';
				}
			}else{
				$scripts[] = '$(".findpwresultfalse1",'.$scdocument.').show();';
			}

		}else{

			$scripts[] = '$("#findpwfromlay",'.$scdocument.').show();';
			$scripts[] = '$("#findpwresultlay",'.$scdocument.').hide();';

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
				if( $success['error'] === false ) {
					$scripts[] = '$(".findidresultok1",'.$scdocument.').show();';
					$scripts[] = '$("#findidlay1",'.$scdocument.').text("'.($success["userid"]).'");';
				}else{
					$scripts[] = '$(".findidresultfalse",'.$scdocument.').show();';
				}
			}else{
				$scripts[] = '$(".findidresultfalse",'.$scdocument.').show();';
			}
		}
		$scripts[] = 'self.close();';

		$scripts[] = "});";
		$scripts[] = "</script>";
echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
foreach($scripts as $script){
	echo $script."\n";
}
echo '</head><body></body></html>';
exit;
	}

	###
	public function auth_chk(){
		###
		$this->validation->set_rules('name', '이름','trim|required|max_length[30]|xss_clean');
		if(isset($_POST['regno'])){
			$this->validation->set_rules('regno', '주민등록번호','trim|required|max_length[13]|numeric|xss_clean');
		}else{
			$this->validation->set_rules('regno1', '주민등록번호','trim|required|max_length[6]|numeric|xss_clean');
			$this->validation->set_rules('regno2', '주민등록번호','trim|required|max_length[7]|numeric|xss_clean');
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$realname = config_load('realname');
		$sSiteID = $realname['realnameId'];
		$sSitePW =  $realname['realnamePwd'];

		$cb_encode_path = "/usr/bin/cb_namecheck";

		$strJumin= isset($_POST['regno']) ? $_POST['regno'] : $_POST["regno1"].$_POST["regno2"];		// 주민번호
		$strName = $_POST["name"];							//이름
		$strName = iconv('utf-8', 'euc-kr', $strName);

		$iReturnCode  = "";
		$iReturnCode = `$cb_encode_path $sSiteID $sSitePW $strJumin $strName`;
		switch($iReturnCode){
			case 1: // 성공
				$msg = "실명인증이 성공적으로 완료되었습니다.<br>회원가입정보를 입력해 주시기 바랍니다.";
				break;
			case 2:
				$msg = "www.namecheck.co.kr 의 실명등록확인 또는 02-1600-1522 콜센터로 문의주시기 바랍니다.";
				break;
			case 3:
				$msg = "www.namecheck.co.kr 의 실명등록확인 또는 02-1600-1522 콜센터로 문의주시기 바랍니다.";
				break;
			case 50:
				$msg = "명의도용차단 서비스 가입자";
				break;
			default:
				$msg = "인증실패";
				break;
		}

		$callback = "";
		if(isset($_POST['intro']) && $_POST['intro']=='Y'){
			if($iReturnCode==1){
				$auth_data = array('auth_type'=>'auth', 'auth_yn'=>'Y');
				$this->session->sess_expiration = (60 * 5);
				$this->session->set_userdata(array('auth'=>$auth_data));
				$callback = "parent.document.location = '/main';";
				$msg = "성인인증이 성공적으로 완료되었습니다.";
			}
			openDialogAlert($msg,400,140,'parent',$callback);
		}else{
			if($iReturnCode==1){
				$auth_data = array('auth_type'=>'auth', 'auth_yn'=>'Y');
				$this->session->sess_expiration = (60 * 5);
				$this->session->set_userdata(array('auth'=>$auth_data));
				$img = "/data/skin/".$this->skin."/images/design/btn_ok.gif";
				$callback = "parent.$('#name').attr('readonly',true);
					parent.$('#regno1').attr('readonly',true);
					parent.$('#regno2').attr('readonly',true);
					parent.$('#submit_btn_area').html(\"<img src='{$img}' id='auth_ok_btn' class='hand'>\");
					parent.$('#r_ipin').html('');
				";
			}
			openDialogAlert($msg,400,140,'parent',$callback);
		}
		exit;

	}
	###

	/**
	** 가입, 아이디/패스워드찾기, 성인인증시 : 본인인증/안심체크/아이핀 실명인증 기본 코드생성
	**/
	public function realnamecheck() {
		$realnametype = ($_POST['realnametype'])?$_POST['realnametype']:$_GET['realnametype'];
		$findidpw = ($_POST['findidpw'])?$_POST['findidpw']:$_GET['findidpw'];
		$intro = ($_POST['intro'])?$_POST['intro']:$_GET['intro'];
		$realname = config_load('realname');

		$this->session->unset_userdata('auth');// $auth['auth_yn']!='Y' &&
		unset($auth);

		$sReserved1 = ($_POST['sReserved1'])?$_POST['sReserved1']:$_GET['sReserved1'];
		$sReserved2 = ($_POST['sReserved2'])?$_POST['sReserved2']:$_GET['sReserved2'];
		$sReserved3 = ($_POST['sReserved3'])?$_POST['sReserved3']:$_GET['sReserved3'];

		$this->session->unset_userdata('findtypess');
		$this->session->unset_userdata('findidss');
		if($findidpw){
			$returnurl_intro = "?findidpw=Y";
		}elseif($intro){
			$returnurl_intro = "?intro=Y";
		}

		if($findidpw){
			$this->session->sess_expiration = (60 * 5);
			if($sReserved1){
				$this->session->set_userdata(array('findtypess'=>$sReserved1));
			}

			if($sReserved2){
				$this->session->set_userdata(array('findidss'=>$sReserved2));
			}
		}


		if( $realnametype && ($realname['useRealnamephone']=='Y' || $realname['useRealname']=='Y' || $realname['useIpin']=='Y') ) {

			if ($_SERVER['HTTPS'] == "on") {
				$HTTP_HOST = "https://".$_SERVER['HTTP_HOST'];
			}else{
				$HTTP_HOST = "http://".$_SERVER['HTTP_HOST'];
			}

			if( $realnametype == 'phone' ) {//본인인증
				//**************************************** 본인인증 : 휴대폰 필수 수정값***************************************************************************
				if(!extension_loaded('CPClient')) {
					dl('CPClient.' . PHP_SHLIB_SUFFIX);
				}
				$module = 'CPClient';

				$sSiteCode 				= $realname['realnamephoneSikey'];			// 본인인증 사이트 코드
				$sSitePassword		= $realname['realnamePhoneSipwd'];			// 본인인증 사이트 패스워드
				$authtype = "M";      	// 없으면 기본 선택화면, X: 공인인증서, M: 핸드폰, C: 카드
				$popgubun 	= "Y";		//Y : 취소버튼 있음 / N : 취소버튼 없음

				if( $this->_is_mobile_agent) {//$this->mobileMode  ||
					$customize 	= "Mobile";			//없으면 기본 웹페이지 / Mobile : 모바일페이지
				}else{
					$customize 	= "";			//없으면 기본 웹페이지 / Mobile : 모바일페이지
				}

//				$cb_encode_path	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/CPClient";	// 암호화 프로그램의 위치 (절대경로+모듈명)_Linux ..
//				$sType			= "REQ";
//				$reqseq = `$cb_encode_path SEQ $sSiteCode`;


				$reqseq = "REQ_0123456789";     // 요청 번호, 이는 성공/실패후에 같은 값으로 되돌려주게 되므로
				
				// 업체에서 적절하게 변경하여 쓰거나, 아래와 같이 생성한다.
				$function = 'get_cprequest_no';
				if (extension_loaded($module)) {
					$reqseq = $function($sitecode);
				} else {
					$reqseq = "Module get_request_no is not compiled into PHP";
				}
    
				$returnurl		= $HTTP_HOST."/member_process/niceid_phone_return".$returnurl_intro;	// 성공시 이동될 URL
				$errorurl		= $HTTP_HOST."/member_process/niceid_phone_return".$returnurl_intro;		// 실패시 이동될 URL

				// reqseq값은 성공페이지로 갈 경우 검증을 위하여 세션에 담아둔다.

				$this->session->set_userdata(array('REQ_SEQ_P'=>$reqseq));//$_SESSION["REQ_SEQ"] = $reqseq;
				$_SESSION["REQ_SEQ_P"] = $reqseq;


				// 입력될 plain 데이타를 만든다.1
				$plaindata =  "7:REQ_SEQ" . strlen($reqseq) . ":" . $reqseq .
										  "8:SITECODE" . strlen($sSiteCode) . ":" . $sSiteCode .
										  "9:AUTH_TYPE" . strlen($authtype) . ":". $authtype .
										  "7:RTN_URL" . strlen($returnurl) . ":" . $returnurl .
										  "7:ERR_URL" . strlen($errorurl) . ":" . $errorurl .
										  "11:POPUP_GUBUN" . strlen($popgubun) . ":" . $popgubun .
										  "9:CUSTOMIZE" . strlen($customize) . ":" . $customize.
										  "9:RESERVED1" . strlen($sReserved1) . ":" . $sReserved1.
										  "9:RESERVED2" . strlen($sReserved2) . ":" . $sReserved2.
										  "9:RESERVED3" . strlen($sReserved3) . ":" . $sReserved3;

				//$enc_data = `$cb_encode_path ENC $sSiteCode $sSitePassword $plaindata`;

				$function = 'get_encode_data';
				if (extension_loaded($module)) {
					$enc_data = $function($sSiteCode, $sSitePassword, $plaindata);
				} else {
					$enc_data = "Module get_request_data is not compiled into PHP";
				}

				if( $enc_data == -1 )
				{
					$returnMsg = "암/복호화 시스템 오류입니다.";
					//$enc_data = "";
				}
				else if( $enc_data== -2 )
				{
					$returnMsg = "암호화 처리 오류입니다.";
					//$enc_data = "";
				}
				else if( $enc_data== -3 )
				{
					$returnMsg = "암호화 데이터 오류 입니다.";
					//$enc_data = "";
				}
				else if( $enc_data== -9 )
				{
					$returnMsg = "입력값 오류 입니다.";
					//$enc_data = "";
				}
				$sEncData = $enc_data;
			}
			elseif( $realnametype == 'ipin' ) {//아이핀체크

					if(!extension_loaded('IPINClient')) {
						dl('IPINClient.' . PHP_SHLIB_SUFFIX);
					}
					$module = 'IPINClient';

					###
					$sSiteCode		= $realname['ipinSikey'];
					$sSitePw			= $realname['ipinKeyString'];

					$sModulePath	= $_SERVER["DOCUMENT_ROOT"]."/namecheck/IPINClient";
					$sReturnURL		= "http://".$_SERVER['HTTP_HOST']."/member_process/ipin_chk".$returnurl_intro;

					##
					$sType			= "SEQ";
					//$sCPRequest = `$sModulePath $sType $sSiteCode`;

					$function = 'get_request_no';
					if (extension_loaded($module)) {
						$sCPRequest = $function($sSiteCode);
					} else {
						$sCPRequest = "Module get_request_no is not compiled into PHP";
					}


					$this->session->set_userdata(array('CPREQUEST'=>$sCPRequest));
					$_SESSION['CPREQUEST'] = $sCPRequest;

					##
					$sType			= "REQ";
					$sEncData		= "";
					$sRtnMsg		= "";

					//$sEncData	= `$sModulePath $sType $sSiteCode $sSitePw $sCPRequest $sReturnURL`;//$sCPRequest $sReturnURL

					$function = 'get_request_data';
					if (extension_loaded($module)) {
						$sEncData = $function($sSiteCode, $sSitePw, $sCPRequest, $sReturnURL);
					} else {
						$sEncData = "Module get_request_data is not compiled into PHP";
					}


					if ($sEncData == -9){
						$sRtnMsg = "입력값 오류 : 암호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.";
					}
			}
			else{//안심체크
				//**************************************** 필수 수정값 ***************************************************************************

				if(!extension_loaded('CPClient')) {
					dl('CPClient.' . PHP_SHLIB_SUFFIX);
				}
				$module = 'CPClient';


				$sSiteCode 				= $realname['realnameId'];							// 안심체크 사이트 코드
				$sSitePassword		= $realname['realnamePwd'];						// 안심체크 사이트 패스워드

				$sIPINSiteCode		= $realname['ipinSikey'];								// 아이핀사이트 코드
				$sIPINPassword		= $realname['ipinKeyString'];						// 아이핀사이트 패스워드
				$sReturnURL			= $HTTP_HOST."/member_process/niceid2_return".$returnurl_intro;		//결과 수신 : full URL 입력
				
				//*******************************************************************************************************************************

				$sRequestNO = "";						//요청고유번호, 이는 성공/실패후에 같은 값으로 되돌려주게 되므로 필요시 사용
				$sClientImg		= "";					//서비스 화면 로고 선택(full 도메인 입력): 사이즈 100*25(px)

				$sReserved1 = "";
				$sReserved2 = "";
				$sReserved3 = "";


				$function = 'get_cprequest_no';//요청고유번호 / 비정상적인 접속 차단을 위해 필요.
					if (extension_loaded($module)) {
						$sRequestNO = $function($sSiteCode);
					} else {
						$sRequestNO = "Module get_request_no is not compiled into PHP";
					}
				$_SESSION["REQ_SEQ"] = $sRequestNO;					//해킹등의 방지를 위하여 세션을 쓴다면, 세션에 요청번호를 넣는다.
				$this->session->set_userdata(array('REQ_SEQ'=>$sRequestNO));


				// 입력될 plain 데이타를 만든다.2
				$plaindata =  "7:RTN_URL" . strlen($sReturnURL) . ":" . $sReturnURL.
							  "7:REQ_SEQ" . strlen($sRequestNO) . ":" . $sRequestNO.
							  "7:IMG_URL" . strlen($sClientImg) . ":" . $sClientImg.
							  "13:IPIN_SITECODE" . strlen($sIPINSiteCode) . ":" . $sIPINSiteCode.
							  "17:IPIN_SITEPASSWORD" . strlen($sIPINPassword) . ":" . $sIPINPassword.
							  "9:RESERVED1" . strlen($sReserved1) . ":" . $sReserved1.
							  "9:RESERVED2" . strlen($sReserved2) . ":" . $sReserved2.
							  "9:RESERVED3" . strlen($sReserved3) . ":" . $sReserved3;

				$function = 'get_encode_data';

				if (extension_loaded($module)) {
					$sEncData = $function($sSiteCode, $sSitePassword, $plaindata);
				} else {
					$sEncData = "Module get_request_data is not compiled into PHP";
				}	


				if( $sEncData == -1 )
				{
					$returnMsg = "암/복호화 시스템 오류입니다.";
				}
				else if( $sEncData== -2 )
				{
					$returnMsg = "암호화 처리 오류입니다.";
				}
				else if( $sEncData== -3 )
				{
					$returnMsg = "암호화 데이터 오류 입니다.";
				}
				else if( $sEncData== -9 )
				{
					$returnMsg = "입력값 오류 입니다.";
				}
			}

			if(empty($sEncData)) {//실패시
				$returnMsg = '잘못된 접근입니다.';
				pageClose($returnMsg);
				exit;
			}

			if($returnMsg) {//실패시
				pageClose($returnMsg);
				exit;
			}

			$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
			$scripts[] = "<script type='text/javascript'>";
			$scripts[] = "$(function() {";
			if( $realnametype == 'phone' ) {//본인인증
				$encodedataform = '<input type="hidden" name="m" value="checkplusSerivce" >';
				$encodedataform .= '<input type="hidden" name="EncodeData" value="'.$sEncData.'" >';
				$action= 'https://nice.checkplus.co.kr/CheckPlusSafeModel/checkplus.cb';
				$scripts[] = 'document.form_chk.submit();';
			}else{
				if( $realnametype == 'ipin' ) {//ipin
					$encodedataform = '<input type="hidden" name="m" value="pubmain" >';
					$action= 'https://cert.vno.co.kr/ipin.cb';
				}else{
					$encodedataform = '<input type="hidden" name="m" value="" >';
					$action = 'https://cert.namecheck.co.kr/NiceID2/certpass_input.asp';
				}
				$encodedataform .= '<input type="hidden" name="enc_data" value="'.$sEncData.'" >';
				$scripts[] = 'document.form_chk.submit();';
			}


			$scripts[] = "});";
			$scripts[] = "</script>";

echo '<html><head>';
foreach($scripts as $script){
	echo $script."\n";
}
echo '</head><body>
<form method="post" name="form_chk" action="'.$action.'">
'.$encodedataform.'
<input type="hidden" name="param_r1" value="'.trim($sReserved1).'">
<input type="hidden" name="param_r2" value="'.trim($sReserved2).'">
<input type="hidden" name="param_r3" value="'.trim($sReserved3).'">
</form>
</body>
</html>
';
			exit;
		}else{
			$returnMsg ="잘못된 접근입니다.";
			pageClose($returnMsg);
			exit;
		}
	}
 /**
  ** 본인인증/안심체크/아이핀 실명인증 체크 관련
  **/

   	//회원아이콘 설정
	public function membericonsave(){  
		$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보//$this->userInfo['member_seq']
		$user_icon = $this->mdata['user_icon'];
		$user_icon_file = $this->mdata['user_icon_file'];
		if($_FILES['membericonFile']['tmp_name']){
			$this->load->model('usedmodel');
			$data_used = $this->usedmodel->used_limit_check();
			if( $data_used['type'] ){ 
				$config['upload_path'] = './data/icon/member';
				$config['max_size']	= $this->config_system['uploadLimit'];
				$tmp = @getimagesize($_FILES['membericonFile']['tmp_name']);  
				if( $tmp[0] > 30 && $tmp[1] > 30 ){ 
					$msg = '가로*세로 사이즈가 30*30 이하이어야 합니다.';
					openDialogAlert($msg,400,150,'parent');
					exit;
				}
				
				if($user_icon_file){
					@unlink($_SERVER['DOCUMENT_ROOT'].$config['upload_path'].'/'.$user_icon_file); 
				}
				$_FILES['membericonFile']['type'] = $tmp['mime'];

				$file_ext		= end(explode('.', $_FILES['membericonFile']['name']));//확장자추출
				$file_name	= 'm_'.$this->userInfo['member_seq'].'.'.$file_ext;//'.str_replace(" ", "", (substr(microtime(), 2, 6))).'
				$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
				$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
				$config['file_name'] = $file_name;
				$config['allowed_types'] = 'jpg|gif|jpeg|png';
				$config['overwrite'] = true;
				$this->load->library('upload', $config);
				if ( ! $this->upload->do_upload('membericonFile'))
				{
					$error = $this->upload->display_errors();
					openDialogAlert($error,400,150,'parent');
					exit;
				}
				$uploadData = $this->upload->data();
				$user_icon = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext']; 
				$this->db->where('member_seq', $this->userInfo['member_seq']);
				$result = $this->db->update('fm_member', array("user_icon"=>99, "user_icon_file"=>$file_name)); 
			}else{
				openDialogAlert($data_used['msg'],400,140,'parent','');
			}
		}

		$callback = "parent.membericonDisplay('{$user_icon}?".time()."');";
		openDialogAlert("등록하였습니다.",400,140,'parent',$callback);
	}
}

/* End of file member_process.php */
/* Location: ./app/controllers/member_process.php */