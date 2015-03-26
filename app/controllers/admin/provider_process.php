<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class provider_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	public function provider_reg(){

		/* 관리자 권한 체크 : 시작 */
		$this->load->model('authmodel');
		$auth = $this->authmodel->manager_limit_act('joincheck_view');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
		
		
		// 확인코드 유효성 및 중복확인
		if	(count($_POST['certify_code']) > 0){
			$this->load->model('providermodel');
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$certify_code	= trim($certify_code);
				if	(!$this->providermodel->check_certify_code($certify_code)){
					$callback = "";
					openDialogAlert("잘못된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				
				//입력된 확인코드 체크
				if ( $certify_code_arry && in_array(trim($certify_code) ,$certify_code_arry) ) {
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				$certify_code_arry[] = $certify_code;

				// 중복확인
				$param['certify_code']	= $certify_code;
				$certify				= $this->providermodel->get_certify_manager($param);
				if	($certify ) {
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				unset($param);
			}//endforeach
		}//endif


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



		$params = $this->provider_check('regist');
		if($_POST['ip_chk']!='Y') $params['limit_ip'] = "";
		if($_POST['hp_chk']!='Y') $params['auth_hp'] = "";

		if	($params['main_visual']){
			$this->load->model('providermodel');
			$params['main_visual']	= $this->providermodel->upload_minishop_image($params['provider_id'], $params['main_visual']);
		}
		
		/* 입점사 등급 로그 */
		$grplist		= $this->providermodel->provider_group_name();
		$provider_log	= "<div>[수동] ".date("Y-m-d H:i:s")." 신규 -> ".$grplist[$params['pgroup_seq']]."(".$params['pgroup_seq'].")"." (".$this->managerInfo['manager_id'].", ".$_SERVER['REMOTE_ADDR'].")</div>";
		/* 입점사 등급 로그 */

		$data = filter_keys($params, $this->db->list_fields('fm_provider'));
		$data['pgroup_date']	= date("Y-m-d H:i:s");
		$data['regdate']		= date("Y-m-d H:i:s");
		$data['provider_log']	= $provider_log;
		$result = $this->db->insert('fm_provider', $data);
		$provider_seq = $this->db->insert_id();

		### BRAND
		$oparams['provider_seq']	= $provider_seq;
		$oparams['link']			= 1;
		$oparams['charge']			= $params['charge'];
		$result = $this->db->insert('fm_provider_charge', $oparams);
		unset($oparams);
		$oparams['provider_seq']	= $provider_seq;
		$oparams['link']			= 0;
		$cnt = 0;
		foreach($params['brand_ch'] as $k){
			$temp_arr = explode("|",$k);
			$oparams['category_code']	= $temp_arr[0];
			$oparams['title']			= $temp_arr[1];
			$oparams['charge']			= $params['brand_per'][$cnt];
			$result = $this->db->insert('fm_provider_charge', $oparams);
			$cnt++;
		}

		### PERSON
		$person = array('ds1', 'ds2', 'cs', 'calcu', 'md', 'wcalcu');
		foreach($person as $k){
			$gb	= $k=="calcu" ? "calcus" : $k;
			if($params[$gb."_name"]){
				unset($eparams);
				$eparams['provider_seq'] = $provider_seq;
				$eparams['gb']		= $k;
				$eparams['name']	= $params[$gb."_name"];
				$eparams['email']	= $params[$gb."_email"];
				$eparams['phone']	= $params[$gb."_phone"];
				$eparams['mobile']	= $params[$gb."_mobile"];
				$result = $this->db->insert('fm_provider_person', $eparams);
			}
		}

		// 확인코드 저장
		if	(count($_POST['certify_code']) > 0){
			$this->load->model('providermodel');
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$cparams['provider_seq']	= $provider_seq;
				$cparams['manager_id']		= $_POST['provider_id'];
				$cparams['manager_name']	= $_POST['manager_name'][$k];
				$cparams['certify_code']	= trim($certify_code);

				$this->providermodel->insert_certify($cparams);
				unset($cparams);
			}
		}

		if($result){
			$callback = "parent.document.location = '/admin/provider';";
			openDialogAlert("등록 되었습니다.",400,140,'parent',$callback);
		}
	}

	public function provider_modify(){

		/* 관리자 권한 체크 : 시작 */
		$this->load->model('authmodel');
		$auth = $this->authmodel->manager_limit_act('joincheck_view');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */


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


		$this->load->model('providermodel');
		$this->load->model('providershipping');
		$params			= $this->provider_check('modify');
		if($_POST['ip_chk']!='Y') $params['limit_ip'] = "";
		if($_POST['hp_chk']!='Y') $params['auth_hp'] = "";

		$provider_info	= $this->providermodel->get_provider($params['provider_seq']);
		$data_providershipping = $this->providershipping->get_provider_shipping($params['provider_seq']);

		if($data_providershipping['delivery_cnt'] == 0 && $params['deli_group'] == 'provider'){
			openDialogAlert("입점사의 배송정책을 입력하여 주세요.",400,140,'parent','');
			exit;
		}
		
		
		// 확인코드 유효성 및 중복확인
		if	(count($_POST['certify_code']) > 0){
			//$this->load->model('providermodel');
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$certify_code	= trim($certify_code);
				if	(!$this->providermodel->check_certify_code($certify_code)){
					$callback = "";
					openDialogAlert("잘못된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				
				//입력된 확인코드 체크
				if ( $certify_code_arry && in_array(trim($certify_code) ,$certify_code_arry) ) {
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				$certify_code_arry[] = $certify_code;

				// 중복확인
				$param['certify_code']		= $certify_code;
				$param['not_manager_id']	= $provider_info['provider_id'];//본인꺼는 제외
				$certify				= $this->providermodel->get_certify_manager($param);
				if	($certify ) {
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				unset($param);
			}//endforeach
		}//endif

		## add minishop visual image
		if	($params['main_visual']){
			$params['main_visual']	= $this->providermodel->upload_minishop_image($provider_info['provider_id'], $params['main_visual']);
		}else{
			if	($params['del_main_visual'] == 'y'){
				$this->providermodel->delete_minishop_image($params['org_main_visual']);
				$params['org_main_visual']	= '';
			}
			$params['main_visual']	= $params['org_main_visual'];
		}

		## add update date
		if	($provider_info['provider_status'] != $params['provider_status']){
			$params['update_date']	= date('Y-m-d H:i:s');

			// 미승인 처리시 판매중인 상품 상태 변경 미승인,판매중지,미노출 처리
			if($params['provider_status'] == 0){
				$this->load->model('goodsmodel');
				$provider_seq = $params['provider_seq'];
				$provider_status = 0;
				$goods_status = 'unsold';
				$goods_view = 'notLook';
				$this->goodsmodel->change_all_provider_status($provider_seq,$provider_status,$goods_status,$goods_view);
			}

		}

		$data = filter_keys($params, $this->db->list_fields('fm_provider'));

		/* 수정내역 추출 */
		$grplist = $this->providermodel->provider_group_name();

		$query = $this->db->query("SHOW FULL COLUMNS FROM fm_provider");
		$columns_result = $query->result_array();
		$columns = array();
		foreach($columns_result as $v) $columns[$v['Field']] = $v['Comment'];

		$provider_log = $provider_info['provider_log'];
		foreach($data as $key=>$value){
			if($provider_info[$key]!=$value && $columns[$key] && !in_array($key,array('admin_memo,selleradmin_memo'))){
				if($key == "pgroup_seq"){
					$data['pgroup_date'] = date("Y-m-d H:i:s",mktime());
					$value1 = $provider_info[$key] ? $grplist[$provider_info[$key]]."(".$provider_info[$key].")" : '없음';
					$value2 = $value ? $grplist[$value]."(".$value.")" : '없음';
				}else{
					$value1 = $provider_info[$key] ? $provider_info[$key] : '없음';
					$value2 = $value ? $value : '없음';
				}
				$provider_log .= "<div>[수동] ".date("Y-m-d H:i:s")." ".$value1." -> ".$value2." (".$this->managerInfo['manager_id'].", ".$_SERVER['REMOTE_ADDR'].")</div>";
			}
		}
		$data['provider_log'] = $provider_log;
		//debug($data);
		//exit;
		/* 수정내용 로그 */

		$result = $this->db->update('fm_provider', $data, array('provider_seq'=>$params['provider_seq']));

		### BRAND
		$this->db->delete('fm_provider_charge', array('provider_seq' => $params['provider_seq']));
		$oparams['provider_seq']	= $params['provider_seq'];
		$oparams['link']			= 1;
		$oparams['charge']			= $params['charge'];
		$result = $this->db->insert('fm_provider_charge', $oparams);
		unset($oparams);
		$oparams['provider_seq']	= $params['provider_seq'];
		$oparams['link']			= 0;
		$cnt = 0;
		foreach($params['brand_ch'] as $k){
			$temp_arr = explode("|",$k);
			$oparams['category_code']	= $temp_arr[0];
			$oparams['title']			= $temp_arr[1];
			$oparams['charge']			= $params['brand_per'][$cnt];
			$result = $this->db->insert('fm_provider_charge', $oparams);
			$cnt++;
		}


		### PERSON
		$this->db->delete('fm_provider_person', array('provider_seq' => $params['provider_seq']));
		$person = array('ds1', 'ds2', 'cs', 'calcu', 'md', 'wcalcu');
		foreach($person as $k){
			$gb	= $k=="calcu" ? "calcus" : $k;
			if($params[$gb."_name"]){
				unset($eparams);
				$eparams['provider_seq'] = $params['provider_seq'];
				$eparams['gb']		= $k;
				$eparams['name']	= $params[$gb."_name"];
				$eparams['email']	= $params[$gb."_email"];
				$eparams['phone']	= $params[$gb."_phone"];
				$eparams['mobile']	= $params[$gb."_mobile"];
				$result = $this->db->insert('fm_provider_person', $eparams);
			}
		}

		// 확인코드 저장
		if	(count($_POST['certify_code']) > 0){
			$this->load->model('providermodel');
			$this->providermodel->delete_certify(array('provider_seq' => $params['provider_seq']));
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;
				$cparams['provider_seq']	= $params['provider_seq'];
				$cparams['manager_id']		= $provider_info['provider_id'];
				$cparams['manager_name']	= $_POST['manager_name'][$k];
				$cparams['certify_code']	= trim($certify_code);
				$this->providermodel->insert_certify($cparams);
				unset($cparams);
			}
		}

		//입점사 등급 변경로그
		//$provider_info	= $this->providermodel->provider_group_change_log($params['provider_seq'],$params['pgroup_seq']);

		if($result){
			$callback = "parent.document.location.reload();";
			openDialogAlert("수정 되었습니다.",400,140,'parent',$callback);
		}
	}



	public function provider_check($type){


		if($this->config_system['service']['code']=='P_ADVL'){
			$_POST['calcu_count'] = 1;
			$limit = $this->usedmodel->get_provider_limit();
			$sql = "select count(*) as cnt from fm_provider where provider_id!='base' and provider_status='Y' and provider_seq!=?";
			$query = $this->db->query($sql,$_POST['provider_seq']);
			$data = $query->row_array();
			if($type=='regist'){
				if($data['cnt']>=$limit){
					$callback = "";
					openDialogAlert("<font color=red>입점사는 총 {$limit}개까지 등록하실 수 있습니다. (현재{$data['cnt']}개)</font>",400,140,'parent',$callback);
					exit;
				}
			}
			if($type=='modify'){
				$provider_info	= $this->providermodel->get_provider($_POST['provider_seq']);
				if(
					$data['cnt']>=$limit && $_POST['provider_status']=='Y'
				){
					$callback = "";
					openDialogAlert("<font color=red>입점사는 총 {$limit}개까지만 활성화 가능합니다. (현재{$data['cnt']}개)</font>",400,140,'parent',$callback);
					exit;
				}
			}
		}

		if($_POST['provider_passwd'] != $_POST['re_provider_passwd']){
			$callback = "parent.document.getElementsByName('provider_passwd')[0].focus();";
			openDialogAlert("입력한 비밀번호와 확인이 올바르지 않습니다.",400,160,'parent',$callback);
			exit;
		}

		$this->validation->set_rules('provider_gb', '구분','trim|required|xss_clean');
		$this->validation->set_rules('provider_name', '입점사(업체)명','trim|max_length[20]|required|xss_clean');
		if($type=="regist"){
			$this->validation->set_rules('provider_id', '입점사 ID','trim|max_length[32]|required|xss_clean');
			$this->db->where('provider_id', $_POST['provider_id']);
			$query = $this->db->get("fm_provider");
			$mem_chk = $query->result_array();
			if($mem_chk){
				$callback = "if(parent.document.getElementsByName('provider_id')[0]) parent.document.getElementsByName('provider_id')[0].focus();";
				openDialogAlert("이미 등록된 아이디 입니다.",400,140,'parent',$callback);
				exit;
			}
			$this->validation->set_rules('provider_passwd', '입점사 비밀번호','trim|min_length[10]|required|xss_clean');
		
		}else{
			if($_POST['passwd_chg']){
				$this->validation->set_rules('provider_passwd', '입점사 비밀번호','trim|min_length[10]|max_length[32]|required|xss_clean');
				$this->validation->set_rules('manager_password', '현재 관리자 비밀번호','trim|required|max_length[32]|xss_clean');
			}
		}

		if(strlen($_POST['provider_passwd']) < 10 || strlen($_POST['provider_passwd']) > 20){
			$callback = "parent.document.getElementsByName('provider_passwd')[0].focus();";
			openDialogAlert("비밀번호는 10~20자 영문, 숫자, 특수문자 중<br> 2가지 이상 조합하여 주십시오.",400,170,'parent',$callback);
			exit;
		}

		if($type=="regist" || $_POST['passwd_chg']){
			$useChar = 0;
			if	(preg_match('/[a-zA-Z]/', $_POST['provider_passwd']))		$useChar++;
			if	(preg_match('/[0-9]/', $_POST['provider_passwd']))			$useChar++;
			if	(preg_match('/[^a-zA-Z0-9]/', $_POST['provider_passwd']))	$useChar++;
			if	($useChar < 2){
				$callback = "parent.document.getElementsByName('mpasswd_re')[0].focus();";
				openDialogAlert("비밀번호는 영문 대소문자 또는 숫자, 특수문자 중<br> 2가지 이상 조합이어야 합니다.",400,170,'parent',$callback);
				exit;
			}
		}

		if($_POST['provider_passwd']){
			$_POST['provider_passwd']	= md5($_POST['provider_passwd']);
		}else{
			unset($_POST['provider_passwd']);
		}
		$this->validation->set_rules('charge', '기본 수수료','trim|numeric|required|xss_clean');
		$this->validation->set_rules('calcu_day', '지불주기','trim|required|xss_clean');
		// $this->validation->set_rules('account_period_type', '정산주기','trim|required|xss_clean');

		$_POST['limit_use']		= if_empty($_POST, "limit_use", 'N');
		if($_POST['calcu_day']){
			foreach($_POST['calcu_day'] as $key_calcu_day => $data_calcu_day){
				$num = $key_calcu_day+1;
				$_POST['calcu_day'.$num] = $data_calcu_day;
			}
		}
		$_POST['calcu_day'] = $_POST['calcu_day'][0];

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}


		### 관리자 비밀번호 검증
		if($_POST['passwd_chg']){
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

		// 확인코드 유효성 및 중복확인
		if	(count($_POST['certify_code']) > 0){
			$this->load->model('providermodel');
			foreach($_POST['certify_code'] as $k => $certify_code){
				if(!$_POST['manager_name'][$k] || !$certify_code) continue;

				$certify_code	= trim($certify_code);
				if	(!$this->providermodel->check_certify_code($certify_code)){
					$callback = "";
					openDialogAlert("잘못된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}

				// 중복확인
				if	($_POST['certify_seq'][$k])	$param['out_seq']	= $_POST['certify_seq'][$k];
				$param['certify_code']	= $certify_code;
				$certify				= $this->providermodel->get_certify_manager($param);
				if	($certify){
					$callback = "";
					openDialogAlert("중복된 확인코드가 있습니다.",400,140,'parent',$callback);
					exit;
				}
				unset($param);
			}
		}

		if($_POST['deli_zipcode']) $_POST['deli_zipcode']		= implode("-",$_POST['deli_zipcode']);
		if($_POST['deli_zipcode']=='-') unset($_POST['deli_zipcode']);

		if($_POST['info_zipcode']) $_POST['info_zipcode']		= implode("-",$_POST['info_zipcode']);
		if($_POST['info_zipcode']=='-') unset($_POST['info_zipcode']);

		$_POST['info_address1_type']	= $_POST['info_address_type'];
		$_POST['info_address1']	= $_POST['info_address'];
		$_POST['info_address1_street']	= $_POST['info_address_street'];

		$_POST['calcu_file']	= $_POST['calcu_file_hidden'];
		$_POST['info_file']		= $_POST['info_file_hidden'];

		if($_POST['calcu_count'] < 7){
			$_POST['account_period_type'] = 'mon_account';
		}else{
			$_POST['account_period_type'] = 'week_account';
		}

		return $_POST;
	}

	public function provider_chk($chk_key = null){
		$manager_id = $_REQUEST['provider_id'];
		if(!$manager_id) die();
		//$manager_id = strtolower($manager_id);

		###
		$count = get_rows('fm_provider',array('provider_id'=>$manager_id));

		$text = "사용할 수 있는 아이디 입니다.";
		$return = true;
		if(strlen($manager_id)<4 || strlen($manager_id)>16){
			$text = "아이디는 최소 4자 이상, 최대 16자 이하로 입력해주세요.";
			$return = false;
		}else if(preg_match("/[^a-z0-9\-_]/i", $manager_id)) {
			$text = "아이디는 영문,숫자, -, _외에는 사용할 수 없습니다.";
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

	public function bankUpload(){
		$type = $_GET['type'] ? $_GET['type'] : "bank";
		if($type=="bank"){
			$filenm = "calcu_file";
		}else{
			$filenm = "busi_file";
		}

		$this->load->library('upload');
		if (is_uploaded_file($_FILES[$filenm]['tmp_name'])) {
			$config['upload_path']		= $path = ROOTPATH."/data/provider/";
			$file_ext = end(explode('.', $_FILES[$filenm]['name']));//확장자추출
			$arrImageExtensions = array('jpg','jpeg','png','gif');
			$arrImageExtensions = array_merge($arrImageExtensions,array_map('strtoupper',$arrImageExtensions));
			$config['allowed_types'] = implode('|',$arrImageExtensions);
			$config['overwrite']			= TRUE;
			$config['file_name']			= substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload($filenm)) {
				@chmod($config['upload_path'].$config['file_name'], 0777);
				if($type=="bank"){
					$callback = "parent.bankHidden('{$config[file_name]}');";
				}else{
					$callback = "parent.busiHidden('{$config[file_name]}');";
				}
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

	public function upload_file(){
		$this->load->model('providermodel');
		$error		= array('status' => 0,'msg' => '업로드 실패하였습니다.','desc' => '업로드 실패');
		$folder		= "data/tmp/";
		$pid		= $_POST['provider_id'];
		$filename	= date('dHis').'_'.$pid;
		$result		= $this->providermodel->upload_minishop_tempimage($filename,$folder);
		if(!$result['status']){
			echo "[".json_encode($error)."]";
			exit;
		}
		$source		= $result['fileInfo']['full_path'];
		$target		= $result['fileInfo']['full_path'];
		$result		= array('status' => 1,'newFile' => "/".$folder.$filename,
							'ext' => $result['fileInfo']['file_ext']);
		echo "[".json_encode($result)."]";
	}

	public function iconUpload(){
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['pgrade_icon']['tmp_name'])) {
			$config['upload_path']		= $path = ROOTPATH."/data/icon/provider/";
			$file_ext = end(explode('.', $_FILES['pgrade_icon']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('pgrade_icon')) {
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

	/* 입점사 등급 추가/수정 */
	public function provider_group_write(){

		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### Validation
		$this->validation->set_rules('pgroup_name', '명칭','trim|required|max_length[15]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		if($_POST['pgroup_seq'] > 1 || !$_POST['pgroup_seq']){
			if($_POST['use_type'] == 1){
				$_POST['order_sum_use']		= $_POST['order_sum_use1'];
				$_POST['order_sum_price']	= str_replace(",","",$_POST['order_sum_price1']);
				$_POST['order_sum_ea']		= str_replace(",","",$_POST['order_sum_ea1']);
				$_POST['order_sum_cnt']		= str_replace(",","",$_POST['order_sum_cnt1']);
				$_POST['use_type']			= "auto1";
			}elseif($_POST['use_type'] == 2){
				$_POST['order_sum_use']		= $_POST['order_sum_use2'];
				$_POST['order_sum_price']	= str_replace(",","",$_POST['order_sum_price2']);
				$_POST['order_sum_ea']		= str_replace(",","",$_POST['order_sum_ea2']);
				$_POST['order_sum_cnt']		= str_replace(",","",$_POST['order_sum_cnt2']);
				$_POST['use_type']			= "auto2";
			}else{
				$_POST['use_type']			= "manual";
			}
		}else{
			## 기준등급 : 자동관리1
			$_POST['use_type']			= "auto1";
			$_POST['order_sum_use']		= array("price1");
			$_POST['order_sum_price']	= 0;
			$_POST['order_sum_ea']		= 0;
			$_POST['order_sum_cnt']		= 0;
		}

		$params = $_POST;

		if(isset($_POST['order_sum_use'])) if(is_array($_POST['order_sum_use'])) $params['order_sum_use'] = serialize($_POST['order_sum_use']);

		if($_POST['mode'] == "modify"){

			$pgroup_seq				= $_POST['pgroup_seq'];
			$data					= filter_keys($params, $this->db->list_fields('fm_provider_group'));

			$this->db->where('pgroup_seq', $pgroup_seq);
			$result					= $this->db->update('fm_provider_group', $data);

		}else{

			$params['regist_date']	= date('Y-m-d H:i:s');
			$data					= filter_keys($params, $this->db->list_fields('fm_provider_group'));
			$result					= $this->db->insert('fm_provider_group', $data);
			$pgroup_seq				= $this->db->insert_id();

		}

		###
		if($result){
			$callback = "parent.document.location.href='/admin/provider/provider_group_reg?pgroup_seq=".$pgroup_seq."'" ;
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	/* 입점사 등급 삭제/갱신설정 */
	public function provider_group_modify(){

		#### AUTH
		$auth = $this->authmodel->manager_limit_act('joincheck_view');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if($_GET['pgrade_mode'] == "manual_group_update"){ $_POST['pgrade_mode'] = $_GET['pgrade_mode']; }

		$this->load->model('providermodel');
		if($_POST['pgrade_mode']=='deleteGrade'){

			$delCnt = 0;
			foreach($_POST['pgroup_seq'] as $pgroup_seq){
				$provider_cnt	= $this->providermodel->find_group_provider_cnt($pgroup_seq);
				if(!$provider_cnt){
					$result	= $this->db->delete('fm_provider_group', array('pgroup_seq' => $pgroup_seq));
					$delCnt++;
				}
			}

			if($delCnt > 0){
				$callback = "parent.document.location.reload();";
				openDialogAlert("삭제되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "";
				openDialogAlert("입점사가 지정된 등급이 있어 삭제 실패 하였습니다.",400,140,'parent',$callback);
			}
		}elseif($_POST['pgrade_mode']=='autoGradeUpdate'){
			
			config_save('provider_grade_clone',array('start_month'=>$_POST['start_month']));
			config_save('provider_grade_clone',array('chg_term'	=>$_POST['chg_term']));
			config_save('provider_grade_clone',array('chg_day'	=>$_POST['chg_day']));
			config_save('provider_grade_clone',array('chk_term'	=>$_POST['chk_term']));
			config_save('provider_grade_clone',array('keep_term'=>$_POST['keep_term']));
			$callback = "parent.document.location.reload();";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);

		}elseif($_POST['pgrade_mode'] == "manual_group_update"){

			## 등급갱신(수동) -> 다음 등급조정일 기준 적용
			$result	= $this->providermodel->provider_group_update("upt");
		}

	}

}