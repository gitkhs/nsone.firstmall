<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class login_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	public function login(){

		$this->load->model('ssl');
		$this->ssl->decode();

		### required
		if($_POST['manager_yn']!='Y'){
			$this->validation->set_rules('admin_id', '대표 관리자 아이디','trim|required|max_length[20]|xss_clean');
			$this->validation->set_rules('sub_id', '부 관리자 아이디','trim|required|max_length[32]|xss_clean');
			$this->validation->set_rules('sub_pwd', '부 관리자 비밀번호','trim|required|max_length[32]|xss_clean');
			if($_POST['hp_auth_check']=='Y'){
				$auth_number = $_POST['sub_hp_auth'];
				$this->validation->set_rules('sub_hp_auth', '휴대폰 인증번호','trim|required|max_length[32]|xss_clean');	
			}
		}else{
			$this->validation->set_rules('main_id', '아이디','trim|required|max_length[20]|xss_clean');
			$this->validation->set_rules('main_pwd', '비밀번호','trim|required|max_length[32]|xss_clean');
			if($_POST['hp_auth_check']=='Y'){
				$auth_number = $_POST['main_hp_auth'];
				$this->validation->set_rules('main_hp_auth', '휴대폰 인증번호','trim|required|max_length[32]|xss_clean');	
			}

		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$out_login = !empty($_POST['out_login']) ? 1 : 0;
		$return_url = !empty($_POST['return_url']) ? $_POST['return_url'] : null; 

		###
		$params['manager_yn']	= $_POST['manager_yn'];
		$params['admin_id']		= $_POST['admin_id'];
		if($_POST['manager_yn']!='Y'){
			$params['manager_id']	= $_POST['sub_id'];
			$params['mpasswd']		= $_POST['sub_pwd'];
		}else{
			$params['manager_id']	= $_POST['main_id'];
			$params['mpasswd']		= $_POST['main_pwd'];
		}

		###
		$str_md5 = md5($params['mpasswd']);
		$str_sha256_md5 = hash('sha256',$str_md5);
		$query = "select * from fm_manager where manager_id=? and manager_yn=? and (mpasswd=? OR mpasswd=?)";
		$query = $this->db->query($query,array($params['manager_id'],$params['manager_yn'],$str_md5,$str_sha256_md5));
		$data = $query->row_array();

		
		if($data && $params['manager_yn']!='Y'){
			$where_arr = array('manager_id'=>$params['admin_id'], 'manager_yn'=>'Y');
			$admin_data = get_data('fm_manager', $where_arr);
			if(!$admin_data) unset($data);
		}
		if(!$data){
			if($out_login){
				alert("일치하는 정보가 없습니다.");
				exit;
			}else{
				$callback = "";
				openDialogAlert("일치하는 정보가 없습니다.",400,140,'parent',$callback);
				exit;
			}
		}

		### IP CHECK
		if($data['limit_ip']){
			$limit_ip = explode("|",$data['limit_ip']);
			$ip_cnt = 0;
			foreach($limit_ip as $v){
				$limit_row = explode(".", $v);
				if(count($limit_row) == 3){
					$detail_ip = explode(".", $_SERVER['REMOTE_ADDR']);
					if($limit_row[0] == $detail_ip[0] && $limit_row[1] == $detail_ip[1] && $limit_row[2] == $detail_ip[2]){
						$ip_cnt++;
					}
				}else{
					if($v == $_SERVER['REMOTE_ADDR']){
						$ip_cnt++;
					}
				}
			}
			if($ip_cnt < 1){
				if($out_login){
					alert("허용된 IP대역이 아닙니다.");
					exit;
				}else{
					$callback = "";
					openDialogAlert("허용된 IP대역이 아닙니다.",400,140,'parent',$callback);
					exit;
				}
			}
		}

		/*휴대폰 인증*/

		if($_POST['auth_hp']!=''){
			if($_POST['auth_hp'] != $_SESSION['auth_number']){
				openDialogAlert("인증번호가 올바르지 않습니다.",400,140,'parent','parent.auth_hp_clear("N");');
				exit;
			}
			//로그저장
			$sql = "select manager_log, auth_hp from fm_manager where manager_id = '".$params['manager_id']."'";
			$query = $this->db->query($sql);
			$result = $query->row_array();

			$logData['manager_log'] = "<div>".date("Y-m-d H:i:s")." 핸드폰 (".$result['auth_hp'].")인증을 성공 하였습니다. (".$_SERVER['REMOTE_ADDR'].")</div>".$result['manager_log'];

			$this->db->where('manager_id', $params['manager_id']);
			$this->db->update('fm_manager', $logData);


			//인증 정보 저장
			setcookie($params['manager_id']."HpAuth","Y",time()+86400); //쿠키 생성

		}else{

			if($data['auth_hp']){
				$limit	= commonCountSMS();

				if($limit){
					if($_COOKIE[$params['manager_id'].'HpAuth'] != "Y"){
						echo "<script>parent.openAuthHp('".$params['manager_id']."');</script>";
						exit;
					}
				}
			}
		}


		### 무료몰 마지막접속 30일 경과 경고메시지 세션 처리
		$this->load->model('usedmodel');
		$periodData = $this->usedmodel->get_period_status();
		$code = $periodData['code'];
		$intval = $periodData['intval'];
		if($code=='2003' || $code=='2004') {
			$this->session->set_userdata(array('showFreeMallExpireCode'=>$code));
			$this->session->set_userdata(array('showFreeMallExpireIntval'=>$intval));
		}

		### LOGIN DATE
		$this->db->where('manager_seq', $data['manager_seq']);
		$result = $this->db->update('fm_manager', array('lastlogin_date'=>date("Y-m-d H:i:s")));

		### SESSION
		$this->create_manager_session($data);

		sleep(1);

		if($return_url){
			pageRedirect($return_url,'','top');
		}else{
			pageRedirect('/admin/main/index','','top');	
		}
	}

	public function logout(){
		$this->session->unset_userdata('manager');
		if($_GET['mode'] != "autoLogout"){
			pageRedirect('/admin/main/index','','parent');
		}
	}



	public function create_manager_session($param=array()){
		$manager_data = array(
			'manager_seq'		=> $param['manager_seq'],
			'manager_id'		=> $param['manager_id'],
			'mname'				=> $param['mname'],
			'mphoto'			=> $param['mphoto'],
			'manager_yn'		=> $param['manager_yn'],
			'manager_auth'		=> $param['manager_auth'],
			'gnb_icon_view'		=> $param['gnb_icon_view']
		);
		$tmp = config_load('autoLogout');
		if($tmp['auto_logout']=='Y'){
			$limit = 60 * ($tmp['until_time'] * 60);
		}else{
			$limit = (60*60*12);
		}

		$this->session->sess_expiration = $limit;
		$this->session->set_userdata(array('manager'=>$manager_data));
	}

	public function init(){

		$sql = "select count(*) as cnt from fm_manager";
		$query = $this->db->query($sql);
		$result = $query->row_array();
		if($result['cnt']){
			openDialogAlert("ERROR",400,140,'parent','parent.document.location.href="/admin/login/index"');
			exit;
		}

		$this->validation->set_rules('main_id', '아이디','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('main_pwd', '비밀번호','trim|required|max_length[32]|xss_clean');
		$this->validation->set_rules('main_pwd_confirm', '비밀번호','trim|required|max_length[32]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$main_id = $_POST['main_id'];
		$main_pwd = $_POST['main_pwd'];
		$main_pwd_confirm = $_POST['main_pwd_confirm'];

		if($main_pwd!=$main_pwd_confirm){
			openDialogAlert("비밀번호가 서로 일치하지 않습니다.",400,140,'parent',"");
			exit;
		}

		$sql = "
		INSERT INTO `fm_manager` (`manager_seq`, `manager_id`, `mpasswd`, `mname`, `mphoto`, `memail`, `mphone`, `mcellphone`, `limit_ip`, `manager_log`, `lastlogin_date`, `mregdate`, `manager_yn`, `manager_auth`, `passwordChange`, `passwordUpdateTime`, `backup_pass`) VALUES (1, ?, ?, ?, '', '', '', '', '', '', '2012-07-01 22:24:46', '2013-05-13 14:00:12', 'Y', 'order_view=Y||order_deposit=Y||order_goods_export=Y||refund_view=Y||refund_act=Y||order_excel=N||goods_view=Y||goods_act=Y||goods_excel=N||member_view=Y||member_act=Y||member_promotion=Y||member_send=Y||member_excel=N||event_view=Y||event_act=N||coupon_view=Y||coupon_act=Y||board_view=Y||board_act=Y||board_manger=Y||statistic_order=N||statistic_visit=Y||statistic_promotion=N||statistic_etc=N||design_act=Y||setting_basic_view=Y||setting_basic_act=Y||setting_operating_view=Y||setting_operating_act=Y||setting_pg_view=Y||setting_pg_act=Y||setting_bank_view=Y||setting_bank_act=Y||setting_member_view=Y||setting_member_act=Y||setting_order_view=Y||setting_order_act=Y||setting_shipping_view=Y||setting_shipping_act=Y||setting_reserve_view=Y||setting_reserve_act=Y||setting_protect_view=Y||setting_protect_act=Y||setting_manager_view=Y||setting_manager_act=Y||', 'N', '0000-00-00 00:00:00', '');
		";
		$this->db->query($sql,array($main_id,md5($main_pwd),"관리자"));

		$sql = "select count(*) as cnt from fm_manager";
		$query = $this->db->query($sql);
		$result = $query->row_array();
		if($result['cnt']){
			openDialogAlert("관리자 아이디가 생성되었습니다.",400,140,'parent','parent.document.location.href="/admin/login/index"');
		}else{
			openDialogAlert("ERROR",400,140,'parent','parent.document.location.href="/admin/login/init"');
		}

	}

	public function change_pass()
	{
		$this->validation->set_rules('now_passwd', '현재 비밀번호','trim|required|max_length[32]|xss_clean');
		$this->validation->set_rules('new_passwd', '새 비밀번호','trim|required|min_length[10]|max_length[32]|xss_clean');
		$this->validation->set_rules('re_passwd', '새 비밀번호 확인','trim|required|max_length[32]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$this->load->model('managermodel');
		
		$now_passwd = $_POST['now_passwd'];
		$new_passwd = $_POST['new_passwd'];
		$re_passwd = $_POST['re_passwd'];

		if(!$this->managerInfo['manager_seq']){
			openDialogAlert("인증 오류 입니다.",400,140,'parent',"");
			exit;
		}
		
		$str_md5 = md5($now_passwd);
		$str_sha256_md5 = hash('sha256',$str_md5);
		$data = $this->managermodel->get_manager($this->managerInfo['manager_seq']);
		
		if($data['mpasswd']!= $str_sha256_md5&&$data['mpasswd']!= $str_md5){
			openDialogAlert("현재 비밀번호가 다릅니다.",400,140,'parent',"");
			exit;
		}
		
		if($now_passwd==$new_passwd){
			openDialogAlert("현재 비밀번호와 새 비밀번호는 같을 수 없습니다.",400,140,'parent',"");
			exit;
		}

		if($new_passwd!=$re_passwd){
			openDialogAlert("새 비밀번호가 일치하지 않습니다.",400,140,'parent',"");
			exit;
		}
		
		if	(preg_match('/[a-zA-Z]/', $new_passwd)) $useChar++;
		if	(preg_match('/[0-9]/', $new_passwd)) $useChar++;
		if	(preg_match('/[^a-zA-Z0-9]/', $new_passwd))	$useChar++;
		if	(preg_match("/[!#$%^&*()?+=\/]/",$new_passwd))	$useChar++;

		if	($useChar < 2){
			$callback = "parent.document.getElementsByName('mpasswd_re')[0].focus();";
			openDialogAlert("비밀번호는 영문 대소문자 또는 숫자, 특수문자 중 2가지 이상 조합이어야 합니다.",400,140,'parent',$callback);
			exit;
		}

		$this->managermodel->update_passwd($this->managerInfo['manager_seq'],$new_passwd);

		openDialogAlert("관리자 비밀번호가 변경 되었습니다.",400,140,'parent','parent.location.reload();');
	}

	public function change_pass_date()
	{
		$this->load->model('managermodel');
		$this->managermodel->update_date($this->managerInfo['manager_seq']);
		echo("<script>parent.closeDialog('popup_change_pass');</script>");
	}


	public function auth_sms_send(){
		
		$limit	= commonCountSMS();
		
		if($limit < 1){
			openDialogAlert("핸드폰 인증을 진행할 수 없습니다.<br>[사유 : 문자 잔여건수 부족]",400,140,'parent','parent.location.reload();');
			exit;
		}
		
		$auth_number = mt_rand(10000, 99999);

		$manager_id = $_POST['manager_id'];
		$sql = "select * from fm_manager where manager_id = '".$manager_id."'";
		$query = $this->db->query($sql);
		$result = $query->row_array();
		
		if(!$result['auth_hp']){
			openDialogAlert("인증 번호 발송중 오류가 발행하였습니다.",400,140,'parent','parent.location.reload();');
			exit;
		}

		$phone = $result['auth_hp'];
		
		$params['msg'] = "관리자 로그인 인증번호는 ".$auth_number."입니다.";
		
		$commonSmsData['member']['phone'] = $phone;;
		$commonSmsData['member']['params'] = $params;
		
		$result = commonSendSMS($commonSmsData);
		
		if($result['code'] != "0000"){
			if($result['code'] == "E001"){
				openDialogAlert("인증 번호 발송중 오류가 발행하였습니다.",400,140,'parent','parent.location.reload();');
				exit;
			}else{
				openDialogAlert("인증 번호 발송중 오류가 발행하였습니다.",400,140,'parent','parent.location.reload();');
				exit;
			}
		}else{
			if($_POST['mode'] == "modify"){
				openDialogAlert("인증번호가 재전송되었습니다.",400,140,'parent','parent.auth_hp_clear("Y");');
			}
			$_SESSION['auth_number'] = $auth_number;
			echo "<script>parent.openAuthHpInput('".$phone."');</script>";
		}
		exit;
	}
}

/* End of file setting_process.php */
/* Location: ./app/controllers/admin/setting_process.php */