<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class member_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('admin/setting');
		$this->load->helper('member');
		$this->load->model('membermodel');
	}

	### 실명확인
	public function realname(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if( $this->isdemo['isdemo'] ){
			//$callback = "parent.document.location.reload();";
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$useRealnamephone	= ($_POST['useRealnamephone'])?$_POST['useRealnamephone']:'N';
		$useIpin	= ($_POST['useIpin'])?$_POST['useIpin']:'N';

		if($arrBasic['operating'] == "adult"){
			if($useIpin == "N"){
				$callback = "";
				openDialogAlert("고객님께서는 현재 \"성인쇼핑몰\"을 운영중입니다.\n성인쇼핑몰은 반드시 \"아이핀\"서비스를 필수로 사용해야 합니다.\n[설정 > 운영방식]에서 일반 또는 회원전용 쇼핑몰로 변경하신 후 \n아이핀서비스 \"미사용\"으로 변경하시기 바랍니다.",400,140,'parent',$callback);
				exit;
			}
		}

		if($useIpin == 'Y' ){
			if( !($_POST['ipinSikey'] && $_POST['ipinKeyString'])  ) {
				$callback = "";
				openDialogAlert("아이핀 세팅정보를 정확히 입력해 주세요.",400,140,'parent',$callback);
				exit;
			}
		}

		if($useRealnamephone == 'Y' ) {
			if( !($_POST['realnamephoneSikey'] && $_POST['realnamePhoneSipwd']) ) {
				$callback = "";
				openDialogAlert("휴대폰인증 세팅정보를 정확히 입력해 주세요.",400,140,'parent',$callback);
				exit;
			}
		}

		//config_save('realname',array('useRealname'=>$useRealname));
		config_save('realname',array('useIpin'=>$useIpin));
		config_save('realname',array('useRealnamephone'=>$useRealnamephone));

		config_save('realname',array('ipinSikey'=>$_POST['ipinSikey']));
		config_save('realname',array('ipinKeyString'=>$_POST['ipinKeyString']));

		config_save('realname',array('realnamephoneSikey'=>$_POST['realnamephoneSikey']));
		config_save('realname',array('realnamePhoneSipwd'=>$_POST['realnamePhoneSipwd']));

		###
		$callback = "parent.set_member_html();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	### 가입
	public function agreement(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### 설정저장
		config_save('member',array('agreement'=>$_POST['agreement']));
		###
		$callback = "parent.set_member_html();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}
	public function privacy(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		###
		$config['upload_path']		= $path = ROOTPATH."/data/config/";
		$config['overwrite']			= TRUE;
		$this->load->library('upload');
		/*
		if (is_uploaded_file($_FILES['p3p_xml']['tmp_name'])) {
			$file_ext = end(explode('.', $_FILES['p3p_xml']['name']));//확장자추출
			$config['allowed_types']	= 'xml';
			$config['file_name']			= 'P3p.'.$file_ext;
			$this->upload->initialize($config);
			if ($this->upload->do_upload('p3p_xml')) {
				config_save('member',array('p3p_xml'=>$config['file_name']));
			}else{
				$callback = "";
				openDialogAlert("xml 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}
		if (is_uploaded_file($_FILES['p3policy_xml']['tmp_name'])) {
			$file_ext = end(explode('.', $_FILES['p3policy_xml']['name']));//확장자추출
			$config['allowed_types']	= 'xml';
			$config['file_name']			= 'P3policy.'.$file_ext;
			$this->upload->initialize($config);
			if ($this->upload->do_upload('p3policy_xml')) {
				config_save('member',array('p3policy_xml'=>$config['file_name']));
			}else{
				$callback = "";
				openDialogAlert("xml 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}
		*/
		if (is_uploaded_file($_FILES['privacy_html']['tmp_name'])) {
			$file_ext = end(explode('.', $_FILES['privacy_html']['name']));//확장자추출
			$config['allowed_types']	= 'html';
			$config['file_name']			= 'privacy_html.'.$file_ext;
			$this->upload->initialize($config);
			if ($this->upload->do_upload('privacy_html')) {
				config_save('member',array('privacy_html'=>$config['file_name']));
			}else{
				$callback = "";
				openDialogAlert("html 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}

		### 설정저장
		config_save('member',array('privacy'=>$_POST['privacy']));
		config_save('member',array('policy'=>$_POST['policy']));
		###
		$callback = "parent.set_member_html();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function joinform(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if( $_POST['Label_cnt'] > 0 ){
			 $this->db->empty_table('fm_joinform');
		}
		$user_sub_arr = $_POST['labelItem']['user'];
		$sort_user=0;
		foreach($user_sub_arr as $k => $sub_arr){
			if($sub_arr['use'] =='')$sub_arr['use'] ='N';
			if($sub_arr['required'] =='')$sub_arr['required'] ='N';
			$sort_user++;
			$data = array(
							'joinform_seq'=> $sub_arr['joinform_seq'],
							'join_type' => 'user',
							'label_title' => $sub_arr['name'],
							'label_desc' => $sub_arr['exp'],
							'label_type' => $sub_arr['type'],
							'label_value' => $sub_arr['value'],
							'required' => $sub_arr['required'],
							'used' => $sub_arr['use'],
							'sort_seq' => $sort_user,
							'regist_date' => date('Y-m-d H:i:s'),
						);
			$this->db->insert('fm_joinform', $data);

		}
		$sort_order=0;
		$order_sub_arr = $_POST['labelItem']['order'];
		foreach($order_sub_arr as $k => $sub_arr){
			if($sub_arr['use'] =='')$sub_arr['use'] ='N';
			if($sub_arr['required'] =='')$sub_arr['required'] ='N';
			$sort_order++;
			$data = array(
							'joinform_seq'=> $sub_arr['joinform_seq'],
							'join_type' => 'order',
							'label_title' => $sub_arr['name'],
							'label_desc' => $sub_arr['exp'],
							'label_type' => $sub_arr['type'],
							'label_value' => $sub_arr['value'],
							'required' => $sub_arr['required'],
							'used' => $sub_arr['use'],
							'sort_seq' => $sort_order,
							'regist_date' => date('Y-m-d H:i:s'),
					);
			$this->db->insert('fm_joinform', $data);

		}
		### 설정저장
		$joinformar['user_icon'] = $_POST['user_icon'];
		$joinformar['email_userid'] = $_POST['email_userid'];
		$joinformar['join_type'] = $_POST['join_type'];

		//snsmb
		if($_POST['join_type'] == 'member_only' ){
			unset($_POST['join_sns_bizonly'], $_POST['join_sns_mbbiz']);
		}

		if($_POST['join_type'] == 'member_business' ){
			unset($_POST['join_sns_mbonly'], $_POST['join_sns_bizonly']);
		}

		if($_POST['join_type'] == 'business_only' ){
			unset($_POST['join_sns_mbonly'], $_POST['join_sns_mbbiz']);
		}

		$joinformar['join_sns_mbonly'] = $_POST['join_sns_mbonly'];
		$joinformar['join_sns_bizonly'] = $_POST['join_sns_bizonly'];
		$joinformar['join_sns_mbbiz'] = $_POST['join_sns_mbbiz'];

		$joinformar['use_f']		= $_POST['use_f'];
		$joinformar['use_home']		= $_POST['use_home'];

		$snssocialar['use_f']		= $_POST['use_f'];
		//sns use
		if($_POST['key_f']){
		  $snssocialar['key_f']			= $_POST['key_f'];
		  $snssocialar['secret_f']		= $_POST['secret_f'];
		  $snssocialar['name_f']		= $_POST['name_f'];
		}else{
		  $snssocialar['key_f']			= '455616624457601';
		  $snssocialar['secret_f']		= 'a6c595c16e08c17802ab4e4d8ac0e70b';
		  $snssocialar['name_f']		= 'fammerce_plus';
		}

		if($_POST['use_t']){
		  $snssocialar['use_t']	= $_POST['use_t'];
		  $snssocialar['key_t']	= $_POST['key_t'];
		  $snssocialar['secret_t']	= $_POST['secret_t'];
		  $joinformar['use_t']		= $_POST['use_t'];
		}else{
		 $snssocialar['use_t']		= 0;
		 $snssocialar['key_t']		= 'ifHWJYpPA2ZGYDrdc5wQ';
		 $snssocialar['secret_t']	= 'cH5gWafZTZjY553zTqZ2YEd4pRPCsKjeHkB8TLficwI';
		 $joinformar['use_t']		= 0;
		}

		if( $_POST['use_m'] && (!$_POST['key_m'] ) ){
			openDialogAlert("미투데이의 [API Key] 값을 정확히 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}

		 $snssocialar['use_m'] = $_POST['use_m'];
		 $snssocialar['key_m'] = $_POST['key_m'];
		 $joinformar['use_m'] = $_POST['use_m'];

		if( $_POST['use_y'] && (!$_POST['key_y'] || !$_POST['secret_y'] ) ){
			openDialogAlert("요즘의 설정값을 정확히 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$snssocialar['use_y'] = $_POST['use_y'];
		$snssocialar['key_y'] = $_POST['key_y'];
		$snssocialar['secret_y'] = $_POST['secret_y'];
		$joinformar['use_y'] = $_POST['use_y'];

		if($_POST['use_c']){
		  $snssocialar['use_c'] = $_POST['use_c'];
		  $snssocialar['key_c'] = $_POST['key_c'];
		  $snssocialar['secret_c'] = $_POST['secret_c'];
		  $joinformar['use_c'] = $_POST['use_c'];
		}else{
		  $snssocialar['use_c'] = '0';
		  $snssocialar['key_c'] = '394d5f52e7654e216714d5ea074f242705063b910';
		 $snssocialar['secret_c'] = '35939c0a7c818488a5d4b268399c88db';
		  $joinformar['use_c'] = '0';
		}

		/* naver login key */
		if(!$_POST['use_n']) $_POST['use_n'] = '0';
		$snssocialar['use_n'] = $_POST['use_n'];
		$snssocialar['key_n'] = $_POST['key_n'];
		$snssocialar['secret_n'] = $_POST['secret_n'];
		$joinformar['use_n'] = $_POST['use_n'];

		/* kakao login key */
		if(!trim($_POST['use_k'])) $_POST['use_k'] = '0';
		$snssocialar['use_k'] = $_POST['use_k'];
		$snssocialar['key_k'] = $_POST['key_k'];
		$snssocialar['kakaotalk_app_javascript_key'] = $_POST['key_k'];
		$joinformar['use_k'] = $_POST['use_k'];

		/* daum login key */
		if(!$_POST['use_d']) $_POST['use_d'] = '0';
		$snssocialar['use_d'] = $_POST['use_d'];
		$snssocialar['key_d'] = $_POST['key_d'];
		$snssocialar['secret_d'] = $_POST['secret_d'];
		$joinformar['use_d'] = $_POST['use_d'];

		###
		if(isset($_POST['disabled_userid'])){
			$_POST['disabled_userid'] = str_replace(" ","",$_POST['disabled_userid']);
			$joinformar['disabled_userid'] = $_POST['disabled_userid'];
		}

		###
		$user_arr = array('userid', 'password', 'user_name', 'email', 'phone', 'cellphone', 'address', 'recommend', 'birthday', 'sex', 'anniversary', 'nickname');
		$buss_arr = array('bname', 'bceo', 'bno', 'bitem', 'badress', 'bperson', 'bpart', 'bphone','bemail', 'bcellphone');
		for($i=0;$i<count($user_arr);$i++){
			$use_name = $user_arr[$i]."_use";
			$required_name = $user_arr[$i]."_required";
			$joinformvalue = if_empty($_POST, $use_name, 'N');
			$joinformar[$use_name] = $joinformvalue;//$this->joinform_config_save($_POST, $use_name);
			
			$required_joinformvalue = if_empty($_POST, $required_name, 'N');
			$joinformar[$required_name] = $required_joinformvalue;//$this->joinform_config_save($_POST, $required_name);
		}
		###
		for($i=0;$i<count($buss_arr);$i++){
			$use_name = $buss_arr[$i]."_use";
			$required_name = $buss_arr[$i]."_required";
			
			$joinformvalue = if_empty($_POST, $use_name, 'N');
			$joinformar[$use_name] = $joinformvalue;//$this->joinform_config_save($_POST, $use_name);

			$required_joinformvalue = if_empty($_POST, $required_name, 'N');
			$joinformar[$required_name] = $required_joinformvalue;//$this->joinform_config_save($_POST, $required_name);
		}

		if(is_array($joinformar)) config_save_array('joinform',$joinformar);
		if(is_array($snssocialar)) config_save_array('snssocial',$snssocialar);

		###
		$callback = "parent.set_member_html();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	## 전용앱 설정 저장(카카오/네이버/다음/싸이월드)
	public function joinform_sns_update(){

		# me2day 설정
		if( $_POST['use_m'] && (!$_POST['key_m'] ) ){
			openDialogAlert("미투데이의 [API Key] 값을 정확히 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}
		
		$snssocialar['use_m'] = $_POST['use_m'];
		$snssocialar['key_m'] = $_POST['key_m'];
		$joinformar['use_m'] = $_POST['use_m'];

		if( $_POST['use_y'] && (!$_POST['key_y'] || !$_POST['secret_y'] ) ){
			openDialogAlert("요즘의 설정값을 정확히 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}

		# yozem 설정
		$snssocialar['use_y'] = $_POST['use_y'];
		$snssocialar['key_y'] = $_POST['key_y'];
		$snssocialar['secret_y'] = $_POST['secret_y'];
		$joinformar['use_y'] = $_POST['use_y'];

		# cyworld 설정
		if($_POST['use_c']){
		  $snssocialar['use_c'] = $_POST['use_c'];
		  $snssocialar['key_c'] = $_POST['key_c'];
		  $snssocialar['secret_c'] = $_POST['secret_c'];
		  $joinformar['use_c'] = $_POST['use_c'];
		}else{
		  $snssocialar['use_c'] = 0;
		  $snssocialar['key_c'] = '394d5f52e7654e216714d5ea074f242705063b910';
		  $snssocialar['secret_c'] = '35939c0a7c818488a5d4b268399c88db';
		  $joinformar['use_c'] = $_POST['use_c'];
		}

		/* naver login key */
		if($_POST['use_n']){
		  $snssocialar['use_n'] = $_POST['use_n'];
		  $snssocialar['key_n'] = $_POST['key_n'];
		  $snssocialar['secret_n'] = $_POST['secret_n'];
		  $joinformar['use_n'] = $_POST['use_n'];
		}else{
		  $snssocialar['use_n'] = 0;
		  $snssocialar['key_n'] = '';
		  $snssocialar['secret_n'] = '';
		  $joinformar['use_n'] = $_POST['use_n'];
		}

		/* kakao login key */
		if($_POST['use_k']){
		  $snssocialar['use_k'] = $_POST['use_k'];
		  $snssocialar['key_k'] = $_POST['key_k'];
		  $snssocialar['kakaotalk_app_javascript_key'] = $_POST['key_k'];
		  $joinformar['use_k'] = $_POST['use_k'];
		}else{
		  $snssocialar['use_k'] = 0;
		  $snssocialar['key_k'] = '';
		  $joinformar['use_k'] = $_POST['use_k'];
		}

		/* daum login key */
		if($_POST['use_d']){
		  $snssocialar['use_d'] = $_POST['use_d'];
		  $snssocialar['key_d'] = $_POST['key_d'];
		  $snssocialar['secret_d'] = $_POST['secret_d'];
		  $joinformar['use_d'] = $_POST['use_d'];
		}else{
		  $snssocialar['use_d'] = 0;
		  $snssocialar['key_d'] = '';
		  $snssocialar['secret_d'] = '';
		  $joinformar['use_d'] = $_POST['use_d'];
		}

		if(is_array($joinformar)) config_save_array('joinform',$joinformar);
		if(is_array($snssocialar)) config_save_array('snssocial',$snssocialar);

		echo json_encode(array("result"=>true,"use_k"=>$joinformar['use_k']));

	}

	public function joinform_snsconfig_save($params, $name){
		$value = if_empty($params, $name, 'N');
		//echo $name." : ".$value."<br>";
		config_save('joinform',array($name=>$value));
	}

	public function joinform_config_save($params, $name){
		$value = if_empty($params, $name, 'N');
		//echo $name." : ".$value."<br>";
		config_save('joinform',array($name=>$value));
	}


	### 승인/혜택
	public function approval(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if( $_POST['invitecount'] > $_POST['invitemaxcount'] ){
			openDialogAlert('누적기준 초대인원수는 페이스북 초대인원수보다 크게 설정해 주세요.',450,140,'parent','');
			exit;
		}

		if( $_POST['emoneyInvitedCnt']>0 && $_POST['emoneyInvitedCnt']<10){//10이상입력
			openDialogAlert('친구를 초대할 때마다 지급되는 적립금은 [10원]이상 설정해 주세요.',450,140,'parent','');
			exit;
		}

		### Validation
		$this->validation->set_rules('emoneyJoin', '회원 가입 시 적립금','trim|required|numeric|max_length[10]|xss_clean');
		$this->validation->set_rules('emoneyRecommend', '추천 받은 자 적립금','trim|required|numeric|max_length[10]|xss_clean');
		$this->validation->set_rules('emoneyLimit', '적립금 제한','trim|required|numeric|max_length[10]|xss_clean');
		$this->validation->set_rules('emoneyJoiner', '추천 한 자 적립금','trim|required|numeric|max_length[10]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		//페이스북 친구초대 타이틀/문구
		$snstitle			= ($_POST['snstitle'])?$_POST['snstitle']:'';
		$snsDescription		= ($_POST['snsDescription'])?$_POST['snsDescription']:'';

		### 설정저장
		config_save('snssocial',array('snstitle'=> $snstitle));
		config_save('snssocial',array('snsDescription'=> $snsDescription));

		$memberar['autoApproval'] = $_POST['autoApproval'];
		$memberar['emoneyJoin'] = $_POST['emoneyJoin'];
		$memberar['emoneyRecommend'] = $_POST['emoneyRecommend'];
		$memberar['emoneyTerm'] = $_POST['emoneyTerm'];
		$memberar['emoneyLimit'] = $_POST['emoneyLimit'];
		$memberar['emoneyJoiner'] = $_POST['emoneyJoiner'];

		$memberar['emoneyInvitees'] = $_POST['emoneyInvitees'];
		$memberar['emoneyInvited'] = $_POST['emoneyInvited'];

		$memberar['emoneyTerm_invited'] = $_POST['emoneyTerm_invited'];
		$memberar['emoneyLimit_invited'] = $_POST['emoneyLimit_invited'];

		$memberar['invitecount'] = $_POST['invitecount'];
		$memberar['emoneyInvitedCnt'] = $_POST['emoneyInvitedCnt'];
		$memberar['invitemaxcount'] = $_POST['invitemaxcount'];


		###	POINT
		$memberar['reserve_select'] = $_POST['reserve_select'];
		$memberar['reserve_year'] = $_POST['reserve_year'];
		$memberar['reserve_direct'] = $_POST['reserve_direct'];

		$memberar['joiner_reserve_select'] = $_POST['joiner_reserve_select'];
		$memberar['joiner_reserve_year'] = $_POST['joiner_reserve_year'];
		$memberar['joiner_reserve_direct'] = $_POST['joiner_reserve_direct'];


		$memberar['recomm_reserve_select'] = $_POST['recomm_reserve_select'];
		$memberar['recomm_reserve_year'] = $_POST['recomm_reserve_year'];
		$memberar['recomm_reserve_direct'] = $_POST['recomm_reserve_direct'];
		$memberar['start_date'] = $_POST['start_date'];
		$memberar['end_date'] = $_POST['end_date'];
		$memberar['emoneyJoin_limit'] = $_POST['emoneyJoin_limit'];

		###
		$memberar['invit_reserve_select'] = $_POST['invit_reserve_select'];
		$memberar['invit_reserve_year'] = $_POST['invit_reserve_year'];
		$memberar['invit_reserve_direct'] = $_POST['invit_reserve_direct'];

		$memberar['invited_reserve_select'] = $_POST['invited_reserve_select'];
		$memberar['invited_reserve_year'] = $_POST['invited_reserve_year'];
		$memberar['invited_reserve_direct'] = $_POST['invited_reserve_direct'];

		$memberar['cnt_reserve_select'] = $_POST['cnt_reserve_select'];
		$memberar['cnt_reserve_year'] = $_POST['cnt_reserve_year'];
		$memberar['cnt_reserve_direct'] = $_POST['cnt_reserve_direct'];



		if( $this->isplusfreenot) {//무료몰이아닌경우에만 적용 @2013-01-14
			$memberar['pointJoin'] = $_POST['pointJoin'];
			$memberar['pointJoiner'] = $_POST['pointJoiner'];

			$memberar['point_select'] = $_POST['point_select'];
			$memberar['point_year'] = $_POST['point_year'];
			$memberar['point_direct'] = $_POST['point_direct'];

			$memberar['joiner_point_select'] = $_POST['joiner_point_select'];
			$memberar['joiner_point_year'] = $_POST['joiner_point_year'];
			$memberar['joiner_point_direct'] = $_POST['joiner_point_direct'];
			$memberar['pointJoin_limit'] = $_POST['pointJoin_limit'];

			$memberar['pointRecommend'] = $_POST['pointRecommend'];
			$memberar['pointTerm'] = $_POST['pointTerm'];
			$memberar['pointLimit'] = $_POST['pointLimit'];

			$memberar['recomm_point_select'] = $_POST['recomm_point_select'];
			$memberar['recomm_point_year'] = $_POST['recomm_point_year'];
			$memberar['recomm_point_direct'] = $_POST['recomm_point_direct'];

			$memberar['invit_point_select'] = $_POST['invit_point_select'];
			$memberar['invit_point_year'] = $_POST['invit_point_year'];
			$memberar['invit_point_direct'] = $_POST['invit_point_direct'];

			$memberar['pointInvited'] = $_POST['pointInvited'];
			$memberar['pointTerm_invited'] = $_POST['pointTerm_invited'];
			$memberar['pointLimit_invited'] = $_POST['pointLimit_invited'];

			$memberar['pointInvitees'] = $_POST['pointInvitees'];
			$memberar['invited_point_select'] = $_POST['invited_point_select'];
			$memberar['invited_point_year'] = $_POST['invited_point_year'];
			$memberar['invited_point_direct'] = $_POST['invited_point_direct'];

			$memberar['pointInvitedCnt'] = $_POST['pointInvitedCnt'];

			$memberar['cnt_point_select'] = $_POST['cnt_point_select'];
			$memberar['cnt_point_year'] = $_POST['cnt_point_year'];
			$memberar['cnt_point_direct'] = $_POST['cnt_point_direct'];
		}
		if(is_array($memberar)) config_save_array('member',$memberar);

		###
		$callback = "parent.set_member_html();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	### 등급
	public function grade(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if($_POST['grade_mode']=='deleteGrade'){
			for ($i = 0 ; $i < count($_POST['group_seq']) ; $i++) {
				$group_seq = $_POST['group_seq'][$i];
				$result = $this->db->delete('fm_member_group', array('group_seq' => $group_seq));
				$result = $this->db->delete('fm_member_group_issuegoods', array('group_seq' => $group_seq));
				$result = $this->db->delete('fm_member_group_issuecategory', array('group_seq' => $group_seq));
			}
			if($result){
				$callback = "parent.set_member_html();";
				openDialogAlert("삭제되었습니다.",400,140,'parent',$callback);
			}
		}else{
			config_save('grade_clone',array('start_month'=>$_POST['start_month']));
			config_save('grade_clone',array('chg_term'	=>$_POST['chg_term']));
			config_save('grade_clone',array('chg_day'	=>$_POST['chg_day']));
			config_save('grade_clone',array('chk_term'	=>$_POST['chk_term']));
			config_save('grade_clone',array('keep_term'	=>$_POST['keep_term']));
			$callback = "parent.set_member_html();";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}
	public function grade_write(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### Validation
		$this->validation->set_rules('group_name', '명칭','trim|required|max_length[15]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		/* 자동관리 산정기준 저장 안됨으로 추가 leewh 2014-09-16 */
		if($_POST['use_type']=="AUTOPART"){
			$_POST['order_sum_use'] = $_POST['order_sum_use2'];
			$_POST['order_sum_price'] = $_POST['order_sum_price2'];
			$_POST['order_sum_ea'] = $_POST['order_sum_ea2'];
			$_POST['order_sum_cnt'] = $_POST['order_sum_cnt2'];
		}

		$params = $_POST;
		$params['regist_date'] = date('Y-m-d H:i:s');
		if(isset($_POST['order_sum_use'])) if(is_array($_POST['order_sum_use'])) $params['order_sum_use'] = serialize($_POST['order_sum_use']);
		$data = filter_keys($params, $this->db->list_fields('fm_member_group'));
		$result = $this->db->insert('fm_member_group', $data);
		$group_seq = $this->db->insert_id();

		### SALE
		for($i=0;$i<count($_POST['issueGoods']);$i++){
			if($_POST['issueGoods'][$i])
			$result = $this->db->insert('fm_member_group_issuegoods', array('group_seq'=>$group_seq,'goods_seq'=>$_POST['issueGoods'][$i],'type'=>'sale'));
		}
		for($i=0;$i<count($_POST['issueCategoryCode']);$i++){
			$result = $this->db->insert('fm_member_group_issuecategory', array('group_seq'=>$group_seq,'category_code'=>$_POST['issueCategoryCode'][$i],'type'=>'sale'));
		}

		### EMONEY
		for($i=0;$i<count($_POST['exceptIssueGoods']);$i++){
			if($_POST['exceptIssueGoods'][$i])
			$result = $this->db->insert('fm_member_group_issuegoods', array('group_seq'=>$group_seq,'goods_seq'=>$_POST['exceptIssueGoods'][$i],'type'=>'emoney'));
		}
		for($i=0;$i<count($_POST['issueCategoryCode']);$i++){
			$result = $this->db->insert('fm_member_group_issuecategory', array('group_seq'=>$group_seq,'category_code'=>$_POST['exceptIssueCategoryCode'][$i],'type'=>'emoney'));
		}

		###
		if($result){
			//$callback = "parent.set_member_html();parent.closeDialog('gradePopup');";
			$callback = "parent.formMove('grade',4);";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}
	public function grade_modify(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### Validation
		$this->validation->set_rules('group_name', '명칭','trim|required|max_length[15]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		if( !$this->isplusfreenot ) {//무료몰인경우초기화@2013-01-14
			$_POST['point_price2'] = '';
			$_POST['point_select'] = '';
			$_POST['point_price_type2'] = '';
			$_POST['point_year'] = '';
			$_POST['point_direct'] = '';
		}

		if($_POST['use_type']=="AUTOPART"){
			$_POST['order_sum_use'] = $_POST['order_sum_use2'];
			$_POST['order_sum_price'] = $_POST['order_sum_price2'];
			$_POST['order_sum_ea'] = $_POST['order_sum_ea2'];
			$_POST['order_sum_cnt'] = $_POST['order_sum_cnt2'];
		}

		$params = $_POST;
		//$params['sale_use'] = if_empty($params, 'sale_use', 'N');
		//$params['point_use'] = if_empty($params, 'point_use', 'N');
		$params['update_date'] = date('Y-m-d H:i:s');
		###
		$result = $this->db->delete('fm_member_group_issuegoods', array('group_seq' => $params['seq']));
		$result = $this->db->delete('fm_member_group_issuecategory', array('group_seq' => $params['seq']));


		### SALE
		$issueGoods = array_unique($_POST['issueGoods']);
		for($i=0;$i<count($issueGoods);$i++){
			if($issueGoods[$i])
			$result = $this->db->insert('fm_member_group_issuegoods', array('group_seq'=>$params['seq'],'goods_seq'=>$issueGoods[$i],'type'=>'sale'));
		}
		for($i=0;$i<count($_POST['issueCategoryCode']);$i++){
			$result = $this->db->insert('fm_member_group_issuecategory', array('group_seq'=>$params['seq'],'category_code'=>$_POST['issueCategoryCode'][$i],'type'=>'sale'));
		}

		### EMONEY
		$exceptIssueGoods = array_unique($_POST['exceptIssueGoods']);
		for($i=0;$i<count($exceptIssueGoods);$i++){
			if($exceptIssueGoods[$i])
			$result = $this->db->insert('fm_member_group_issuegoods', array('group_seq'=>$params['seq'],'goods_seq'=>$exceptIssueGoods[$i],'type'=>'emoney'));
		}
		for($i=0;$i<count($_POST['exceptIssueCategoryCode']);$i++){
			$result = $this->db->insert('fm_member_group_issuecategory', array('group_seq'=>$params['seq'],'category_code'=>$_POST['exceptIssueCategoryCode'][$i],'type'=>'emoney'));
		}

		if( is_array($_POST['order_sum_use']) ) $params['order_sum_use'] = serialize($_POST['order_sum_use']);
		$data = filter_keys($params, $this->db->list_fields('fm_member_group'));
		$this->db->where('group_seq', $params['seq']);
		$result = $this->db->update('fm_member_group', $data);

		###
		if($result){
			//$callback = "parent.set_member_html();parent.closeDialog('gradePopup');";
			$callback = "parent.formMove('grade',4);";
			openDialogAlert("설정이 수정 되었습니다.",400,140,'parent',$callback);
		}
	}


	### 로그아웃/탈퇴/재가입
	public function withdraw(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('setting_member_act');
		if(!$auth){
			$callback = "parent.set_member_html();";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### 설정저장
		config_save('member',array('sessLimit'=>$_POST['sessLimit']));
		config_save('member',array('sessLimitMin'=>$_POST['sessLimitMin']));
		config_save('member',array('modifyPW'=>$_POST['modifyPW']));
		config_save('member',array('modifyPWMin'=>$_POST['modifyPWMin']));
		###
		$callback = "parent.set_member_html();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}


	public function withdrawal_set(){

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('withdrawal_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		//$this->load->model('membermodel');
		$member_arr = $_GET['member_chk'];
		###
		$params['regist_date']	= date('Y-m-d H:i:s');
		$params['reason']		= "관리자 리스트 탈퇴처리";
		$params['regist_ip']	= $_SERVER['REMOTE_ADDR'];
		foreach($member_arr as $k){
			$params['member_seq']	= $k;

			$member = $this->membermodel->get_member_data($params['member_seq']);
			if( $this->isdemo['isdemo'] && $member['userid'] == $this->isdemo['isdemoid'] ){
				openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
				exit;
			}

			$result = $this->membermodel->set_withdrawal_admin($params);
		}
		$callback = "parent.location.reload();";
		openDialogAlert("탈퇴 처리 되었습니다.",400,140,'parent',$callback);
	}


	public function member_modify(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}


		###
		$params = $_POST;
		$seq	= $_POST['member_seq'];
		unset($params['member_seq']);
		$params['zipcode'] = implode("-",$_POST['Zipcode']);
		$params['address_type'] = $_POST['Address_type'];
		$params['address'] = $_POST['Address'];
		$params['address_street'] = $_POST['Address_street'];

		$label_pr = $_POST['label'];
		$label_sub_pr = $_POST['labelsub'];

		### COMMON
		if(!empty($params['anniversary'][0]) && !empty($params['anniversary'][1]))
			$params['anniversary'] = implode("-",$params['anniversary']);
		else
			$params['anniversary'] = '';

		$admin_log = "";
		if($params['passwd_chg'] || $params['busi_passwd_chg']) {

			### Validation
			$this->validation->set_rules('password', '비밀번호','trim|required|max_length[20]|xss_clean');
			$this->validation->set_rules('manager_password', '관리자 비밀번호','trim|required|max_length[32]|xss_clean');

			if($this->validation->exec()===false){
				$err = $this->validation->error_array;
				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
				openDialogAlert($err['value'],400,140,'parent',$callback);
				exit;
			}

			### 관리자 비밀번호 검증
			$str_md5 = md5($params['manager_password']);
			$str_sha256_md5 = hash('sha256',$str_md5);
			$query = "select * from fm_manager where manager_id=? and (mpasswd=? OR mpasswd=?)";
			$query = $this->db->query($query,array($this->managerInfo['manager_id'],$str_md5,$str_sha256_md5));
			$data = $query->row_array();
			if(!$data){
				$callback = "";
				openDialogAlert("관리자 정보가 일치하지 않습니다.",400,140,'parent',$callback);
				exit;
			}

			$params['password'] = hash('sha256',md5($params['password']));
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 비밀번호가 ".date("Y년m월d일 H시i분s초")."에 변경됨 (".$_SERVER['REMOTE_ADDR'].")</div>";
		}


		### 추가정보 저장
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
				}//debug_var($setdata);
			}

		}

		### LOG
		//$this->load->model('membermodel');
		$member = $this->membermodel->get_member_data($seq);

		if( $this->isdemo['isdemo'] && $member['userid'] == $this->isdemo['isdemoid'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		$p_address = $params['address'] . $params['address_detail'];
		$m_address = $member['address'] . $member['address_detail'];

		if($p_address != $m_address){
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 주소가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$m_address."]->[".$p_address."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}

		if($params['address_street'] != $member['address_street']){
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 도로명 주소가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$member['address_street']."]->[".$params['address_street']."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}

		if($params['status']!=$member['status']){
			$array_values = array(
				'done' => '승인',
				'hold' => '미승인'
			);
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원 승인여부가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$array_values[$member['status']]."]->[".$array_values[$params['status']]."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['email']!=$member['email']){
			$value1 = $member['email'] ? $member['email'] : '없음';
			$value2 = $params['email'] ? $params['email'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 이메일 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['cellphone']!=$member['cellphone']){
			$value1 = $member['cellphone'] ? $member['cellphone'] : '없음';
			$value2 = $params['cellphone'] ? $params['cellphone'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 핸드폰 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['phone']!=$member['phone']){
			$value1 = $member['phone'] ? $member['phone'] : '없음';
			$value2 = $params['phone'] ? $params['phone'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 전화번호 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if(($params['address'].$params['address_detail'])!=($member['address'].$member['address_detail'])){
			$value1 = $member['address'].$member['address_detail'] ? $member['address']." ".$member['address_detail'] : '없음';
			$value2 = $params['address'].$params['address_detail'] ? $params['address']." ".$params['address_detail'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원주소 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['group_seq']!=$member['group_seq']){
			$params['grade_update_date'] = date('Y-m-d H:i:s');
			$admin_log .= "<div>[수동] ".date('Y-m-d H:i:s')." ".$member['group_name']." → ".$params['group_name']." (".$this->managerInfo['manager_id'].", ".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['nickname']!=$member['nickname']){
			$value1 = $member['nickname'] ? $member['nickname'] : '없음';
			$value2 = $params['nickname'] ? $params['nickname'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원닉네임 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['fb_invite']!=$member['fb_invite']){
			$value1 = $member['fb_invite'] ? $member['fb_invite'] : '없음';
			$value2 = $params['fb_invite'] ? $params['fb_invite'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 Facebook 초대인 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['birthday']!=$member['birthday']){
			$value1 = $member['birthday'] ? $member['birthday'] : '없음';
			$value2 = $params['birthday'] ? $params['birthday'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원생일 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['sex']!=$member['sex']){
			$array_values = array(
				'none' => '없음',
				'male' => '남자',
				'female' => '여자'
			);
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원성별 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$array_values[$member['sex']]."]->[".$array_values[$params['sex']]."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['anniversary']!=$member['anniversary']){
			$value1 = $member['anniversary'] ? $member['anniversary'] : '없음';
			$value2 = $params['anniversary'] ? $params['anniversary'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원기념일 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['user_name']!=$member['user_name']){
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 회원이름 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$member['user_name']."]->[".$params['user_name']."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['mailing']!=$member['mailing']){
			$value1 = $member['mailing'] ? $member['mailing'] : '없음';
			$value2 = $params['mailing'] ? $params['mailing'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 이메일 수신여부가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['sms']!=$member['sms']){
			$value1 = $member['sms'] ? $member['sms'] : '없음';
			$value2 = $params['sms'] ? $params['sms'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 핸드폰 수신여부가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}
		if($params['recommend']!=$member['recommend']){
			$value1 = $member['recommend'] ? $member['recommend'] : '없음';
			$value2 = $params['recommend'] ? $params['recommend'] : '없음';
			$admin_log .= "<div>".$this->managerInfo['manager_id']."에 의해 추천인 정보가 ".date("Y년m월d일 H시i분s초")."에 변경됨 [".$value1."]->[".$value2."] (".$_SERVER['REMOTE_ADDR'].")</div>";
		}

		$params['admin_log'] = $admin_log.$_POST['admin_log'];

		###
		$data = filter_keys($params, $this->db->list_fields('fm_member'));
		$this->db->where('member_seq', $seq);
		$result = $this->db->update('fm_member', $data);
		//print_r($data);
		$email = get_encrypt_qry('email');
		$cellphone = get_encrypt_qry('cellphone');
		$phone = get_encrypt_qry('phone');
		$sql = "update fm_member set {$email}, {$cellphone}, {$phone}, update_date = now() where member_seq = '{$seq}'";
		$this->db->query($sql);

		### BUSINESS CHK
		$business_seq = $params['business_seq'];
		$params['bzipcode'] = implode("-",$_POST['companyZipcode']);
		$params['baddress'] = $_POST['companyAddress'];
		$params['baddress_street'] = $_POST['companyAddress_street'];
		if($_POST['user_type']=='business'){
			unset($params['business_seq']);
			$data = filter_keys($params, $this->db->list_fields('fm_member_business'));
			//print_r($data);
			if($business_seq){
				$this->db->where('business_seq', $business_seq);
				$result = $this->db->update('fm_member_business', $data);
			}else{
				$data['member_seq'] = $seq;
				$result = $this->db->insert('fm_member_business', $data);
			}
		}else{
			if($business_seq){
				$sql = "delete from fm_member_business where member_seq = '{$seq}'";
				$this->db->query($sql);
			}
		}

		###
		$app = config_load('member');
		//수동승인설정시 승인변경한 경우에만 체크
		if($app['autoApproval']=='N' && $params['status']!=$member['status'] && $params['status'] == 'done' ) {

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

			if( $app['emoneyJoin'] ) {
				$joinsc['whereis'] = ' and type = \'join\' and gb = \'plus\' and member_seq = \''.$seq.'\' ';
				$joinsc['select']	= ' emoney_seq ';
				$emjoinck = $this->emoneymodel->get_data_numrow($joinsc);//가입적립금 지급여부
				if(!$emjoinck){
					### EMONEY
					$emoney['type']		= 'join';
					$emoney['emoney']	= $app['emoneyJoin'];
					$emoney['gb']		= 'plus';
					$emoney['memo']		= '회원 가입 적립금';
					$emoney['limit_date'] = get_emoney_limitdate('join');
					$this->membermodel->emoney_insert($emoney, $seq);
				}
			}

			if( $app['pointJoin'] ) {
				$joinsc['whereis'] = ' and type = \'join\' and gb = \'plus\' and member_seq = \''.$seq.'\' ';
				$joinsc['select']	= ' point_seq ';
				$emjoinck = $this->pointmodel->get_data_numrow($joinsc);//가입포인트 지급여부
				if(!$emjoinck){
					### POINT
					$iparam['gb']			= "plus";
					$iparam['type']			= 'join';
					$iparam['point']		= $app['pointJoin'];
					$iparam['memo']			= '회원 가입 포인트';
					$iparam['limit_date']	= get_point_limitdate('join');
					$this->membermodel->point_insert($iparam, $seq);
				}
			}

			//추천시
			if($params['recommend']){
				$chk = get_data("fm_member",array("userid"=>$params['recommend'],"status"=>"done"));
				if($chk[0]['member_seq']) {

					//추천받은자의 추천받은건수 증가 @2013-06-19
					$this->membermodel->member_recommend_cnt($chk[0]['member_seq']);

					//추천 받은 자 -> 제한함
					$todaymonth = date("Y-m");
					if($app['emoneyRecommend']>0) {
						$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\' and ( member_seq_to = \''.$seq.'\' or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$recommendtosc['select']	 = ' emoney_seq ';
						$emrecommendtock = $this->emoneymodel->get_data_numrow($recommendtosc);//추천한 회원 적립금 지급여부
						if( !$emrecommendtock ) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$emrecommendtock = $this->emoneymodel->get_data($recommendtosc);//추천한 회원 적립금 지급여부
							$maxrecommend = ($app['emoneyLimit']*$app['emoneyRecommend']);

							if( $emrecommendtock['totalcnt'] < $app['emoneyLimit'] && $emrecommendtock['totalemoney'] <= $maxrecommend ) {
								unset($emoney);
								$emoney['type']							= 'recommend_to';
								$emoney['emoney']					= $app['emoneyRecommend'];
								$emoney['gb']							= 'plus';
								$emoney['memo']						= '추천 회원 적립금';
								$emoney['limit_date']					= get_emoney_limitdate('recomm');
								$emoney['member_seq_to']		= $seq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}
					}
					if($app['pointRecommend']>0) {
						$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\' and ( member_seq_to = \''.$seq.'\' or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$recommendtosc['select']	 = ' point_seq ';
						$emrecommendtock = $this->pointmodel->get_data_numrow($recommendtosc);//추천한 회원 포인트 지급여부
						if( !$emrecommendtock ) {//추천 받은 자 -> 제한함
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalepoint ';
							$pmrecommendtock = $this->pointmodel->get_data($recommendtosc);//추천한 회원 포인트 지급여부
							$maxrecommend = ($app['pointLimit']*$app['pointRecommend']);

							if( $pmrecommendtock['totalcnt'] < $app['pointLimit'] && $pmrecommendtock['totalepoint'] <= $maxrecommend ) {
								$point['type']							= 'recommend_to';
								$point['point']							= $app['pointRecommend'];
								$point['gb']							= 'plus';
								$point['memo']						= '추천 회원 포인트';
								$point['limit_date']					= get_point_limitdate('recomm');
								$point['member_seq_to']		= $seq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}
					}

					if($app['emoneyJoiner']>0){
						$recommendfromsc['whereis'] = ' and type = \'recommend_from\' and gb = \'plus\' and member_seq = \''.$seq.'\' and ( member_seq_to = \''.$chk[0]['member_seq'].'\'  or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) )  ';
						$recommendfromsc['select']	 = ' emoney_seq ';
						$emrecommendfromck = $this->emoneymodel->get_data_numrow($recommendfromsc);//추천받고가입한회원 적립금 지급여부
						if(!$emrecommendfromck) {//추천한자(가입자)
							unset($emoney);
							$emoney['type']							= 'recommend_from';
							$emoney['emoney']					= $app['emoneyJoiner'];
							$emoney['gb']							= 'plus';
							$emoney['memo']						= '추천 적립금';
							$emoney['limit_date']					= get_emoney_limitdate('joiner');
							$emoney['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $seq);
						}
					}

					if($app['pointJoiner']>0){
						$recommendfromsc['whereis'] = ' and type = \'recommend_from\' and gb = \'plus\' and member_seq = \''.$seq.'\' and ( member_seq_to = \''.$chk[0]['member_seq'].'\'  or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$recommendfromsc['select']	 = ' point_seq ';
						$pmrecommendfromck = $this->pointmodel->get_data_numrow($recommendfromsc);//추천받고가입한회원 포인트 지급여부
						if(!$pmrecommendfromck) {//추천한자(가입자)
							unset($point);
							$point['type']							= 'recommend_from';
							$point['point']							= $app['pointJoiner'];
							$point['gb']							= 'plus';
							$point['memo']						= '추천 포인트';
							$point['limit_date']					= get_point_limitdate('joiner');
							$point['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $seq);
						}
					}

				}
			}

			//초대시
			if($params['fb_invite']){
				$chk = get_data("fm_member",array("member_seq"=>$params['fb_invite']));
				if($chk[0]['member_seq']) {

					if($app['emoneyInvited']>0){
						$invitefromsc['whereis']	= ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and ( member_seq_to = \''.$seq.'\'  or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$invitefromsc['select']		= ' emoney_seq ';
						$eminvitefromck = $this->emoneymodel->get_data_numrow($invitefromsc);//초대받고가입한회원 적립금 지급여부
						if( !$eminvitefromck ) {//초대 한 자  -> 제한함
							$todaymonth = date("Y-m");
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
								$emoney['member_seq_to']		= $seq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
 							}
						}
					}
					if($app['pointInvited']>0){
						$invitefromsc['whereis']	= ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\' and ( member_seq_to = \''.$seq.'\'  or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$invitefromsc['select']		= ' point_seq ';
						$eminvitefromck = $this->pointmodel->get_data_numrow($invitefromsc);//초대받고가입한회원 적립금 지급여부
						if( !$eminvitefromck ) {//초대 한 자  -> 제한함
							$todaymonth = date("Y-m");
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
								$point['member_seq_to']		= $seq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}
					}

					if($app['emoneyInvitees']>0) {
						$invitetosc['whereis'] = ' and type = \'invite_to\' and gb = \'plus\' and member_seq = \''.$seq.'\'  and ( member_seq_to = \''.$chk[0]['member_seq'].'\'  or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$invitetosc['select']	 = ' emoney_seq ';
						$eminvitetock = $this->emoneymodel->get_data_numrow($invitetosc);//초대한 회원 적립금 지급여부
						if( !$eminvitetock){//초대 받은 자(가입자)
							unset($emoney);
							$emoney['type']							= 'invite_to';
							$emoney['emoney']					= $app['emoneyInvitees'];//추천받은자
							$emoney['gb']							= 'plus';
							$emoney['memo']						= '초대 회원 적립금';
							$emoney['limit_date']					= get_emoney_limitdate('invite_to');
							$emoney['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $seq);
						}
					}

					if($app['pointInvitees']>0) {
						$invitetosc['whereis'] = ' and type = \'invite_to\' and gb = \'plus\' and member_seq = \''.$seq.'\'  and ( member_seq_to = \''.$chk[0]['member_seq'].'\'  or (member_seq_to is null and regist_date between \''.substr($member['regist_date'],0,16).':00\' and \''.substr($member['regist_date'],0,16).':59\' ) ) ';
						$invitetosc['select']	 = ' point_seq ';
						$pminvitetock = $this->pointmodel->get_data_numrow($invitetosc);//초대한 회원 포인트 지급여부
						if( !$pminvitetock){//초대 받은 자(가입자)
							unset($point);
							$point['type']							= 'invite_to';
							$point['point']							= $app['pointInvitees'];
							$point['gb']							= 'plus';
							$point['memo']						= '초대 회원 포인트';
							$point['limit_date']					= get_point_limitdate('invite_to');
							$point['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $seq);
						}
					}
				}
			}
		}


		//수정된회원의 추천건수 수정
		if($seq && $params['recommend']!=$member['recommend']) {
			$this->membermodel->member_recommend_cnt($seq);
			if($params['recommend']){
				$chk = get_data("fm_member",array("userid"=>$params['recommend'],"status"=>"done"));
				if($chk && $chk[0]['member_seq']) {
					$this->membermodel->member_recommend_cnt($chk[0]['member_seq']);
				}
			}
			if($member['recommend']){
				$chkold = get_data("fm_member",array("userid"=>$member['recommend'],"status"=>"done"));
				if($chkold && $chkold[0]['member_seq']) {
					$this->membermodel->member_recommend_cnt($chkold[0]['member_seq']);
				}
			}
		}
		//수정된회원의 초대건수 수정
		if($seq && $params['fb_invite']!=$member['fb_invite']){
			$this->membermodel->member_invite_cnt($seq);
			if($params['fb_invite']){
				$chk = get_data("fm_member",array("member_seq"=>$params['fb_invite'],"status"=>"done"));
				if($chk && $chk[0]['member_seq']) {
					$this->membermodel->member_invite_cnt($chk[0]['member_seq']);
				}
			}
			if($member['fb_invite']){
				$chkold = get_data("fm_member",array("member_seq"=>$member['fb_invite'],"status"=>"done"));
				if($chkold && $chkold[0]['member_seq']) {
					$this->membermodel->member_invite_cnt($chkold[0]['member_seq']);
				}
			}
		}


		###
		$callback = "parent.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function member_withdrawal(){
		### Validation
		$this->validation->set_rules('reason', '탈퇴사유','trim|required|xss_clean');
		$this->validation->set_rules('memo', '내용','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		###
		$params	= $_POST;
		$params['regist_date']	= date('Y-m-d H:i:s');
		$params['regist_ip']	= $_SERVER['REMOTE_ADDR'];
		//$this->load->model('membermodel');

		$member = $this->membermodel->get_member_data($_POST['member_seq']);

		if( $this->isdemo['isdemo'] && $member['userid'] == $this->isdemo['isdemoid'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		$result = $this->membermodel->set_withdrawal_admin($params);
		$callback = "parent.location.href = '/admin/member/withdrawal';";
		openDialogAlert("정상적으로 회원 탈퇴가 이뤄졌습니다.",400,140,'parent',$callback);
	}


	public function sms(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if( $this->isdemo['isdemo'] ){
			openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		$list			= $_POST['group_list'];
		$admins_num1	= $_POST['admins_num1'];
		$admins_num2	= $_POST['admins_num2'];
		$admins_num3	= $_POST['admins_num3'];
		$admins_num_cnt	= count($_POST['admins_num1']);
		if	(!is_array($list) || count($list) < 1){
			$callback = "";
			openDialogAlert("저장할 목록이 없습니다.",400,140,'parent',$callback);
			exit;
		}

		### 메시지 및 수신여부
		foreach ($list as $k => $sms){
			if( in_array($this->config_system['service']['code'], array('P_FREE', 'P_PREM')) && strstr($sms,"coupon_") ) {
				continue;
			}else if( in_array($this->config_system['service']['code'], array('P_STOR')) && (in_array($sms, array('released2','delivery2')))) {
				continue;
			}else{
				$this->validation->set_rules($sms . '_user', '내용','trim|required|xss_clean');
			}

			if($this->validation->exec()===false){
				$err = $this->validation->error_array;

				$pos = strpos($err['key'], 'coupon_');
				if ($pos !== false) {
					$tab_no = 3; //쿠폰 tab
				} else {
					$tmp_str = str_replace("user","",$err['key']);
					$tab2_arr = array("released_","released2_","delivery_","delivery2_","cancel_","refund_");
					$tab_no = 1; //공통 tab
					if (in_array($tmp_str,$tab2_arr)) {
						$tab_no = 2; //실물 발송 상품 tab
					}
				}

				$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) {parent.tabmenu('{$tab_no}');parent.document.getElementsByName('{$err['key']}')[0].focus();}";
				openDialogAlert("SMS 메시지를 입력해 주세요.",400,140,'parent',$callback);
				exit;
			}
			$config_save['sms'][$sms.'_user']		= $_POST[$sms.'_user'];
			$config_save['sms'][$sms.'_admin']		= $_POST[$sms.'_admin'];

			## 문자 검증 :: 2015-01-26 lwh
			$chkString	= $config_save['sms'][$sms.'_user'] . " " . $config_save['sms'][$sms.'_admin'];
			if(preg_match('/\%[^\s]+/',$chkString)){
				openDialogAlert("%이후에는 문자가 올수 없습니다.<br/>예) 30%DC -> 30% DC",400,140,'parent',$callback);
				exit;
			}

			$config_save['sms'][$sms.'_user_yn']	= if_empty($_POST, $sms.'_user_yn', 'N');
			$idx	= 0;
			for($j = 0; $j < $admins_num_cnt; $j++){
				if(isset($admins_num1[$j]) && isset($admins_num2[$j]) && isset($admins_num3[$j])){
					$value	= $admins_num1[$j].'-'.$admins_num2[$j].'-'.$admins_num3[$j];
					if($value != '--' && !in_array($value, $saved_arr)){
						$codecd							= $sms.'_admins_yn_' . $idx;
						$config_save['sms'][$codecd]	= if_empty($_POST, $sms.'_admins_yn_'.$j, 'N');
						$saved_arr[]					= $value;
						$idx++;
					}
				}
			}
			unset($saved_arr);
		}

		## 관리자 발신번호
		$config_save['sms_info']['send_num']	= implode("-",$_POST['send_num']);

		## 관리자 수신번호
		$cnt	= 0;
		for($i = 0; $i < $admins_num_cnt; $i++){
			if(isset($admins_num1[$i]) && isset($admins_num2[$i]) && isset($admins_num3[$i])){
				$value	= $admins_num1[$i].'-'.$admins_num2[$i].'-'.$admins_num3[$i];
				if($value != '--' && !in_array($value, $saved_arr)){
					$codecd								= 'admins_num_' . $cnt;
					$config_save['sms_info'][$codecd]	= $value;
					$saved_arr[]						= $value;
					$cnt++;
				}
			}
		}
		if($cnt > 0)
			$config_save['sms_info']['admis_cnt']	= $cnt;


		## sms_info 정보 초기화
		$sql	= "delete from fm_config where groupcd = 'sms_info'";//_reply_user _write_admin
		$query	= $this->db->query($sql);

		## sms 정보 초기화
		$sql	= "delete from fm_config where groupcd = 'sms' and (codecd not like '%_write_admin%' and codecd not like '%_reply_user%') ";
		$query	= $this->db->query($sql);

		## sms 및 sms_info 정보 저장
		foreach($config_save as $groupcd => $data){
			foreach($data as $codecd => $value){
				config_save($groupcd, array($codecd	=> $value));
			}
		}

		## SMS 상품명 길이 제한
		config_delete('sms_goods_limit');
		$goods_limit = array('ord_item_use'=>$_POST['ord_item_use']
							,'repay_item_use'=>$_POST['repay_item_use']
							,'go_item_use'=>$_POST['go_item_use']
							,'goods_item_use'=>$_POST['goods_item_use']
							,'ord_item_limit'=>$_POST['ord_item_limit']
							,'repay_item_limit'=>$_POST['repay_item_limit']
							,'go_item_limit'=>$_POST['go_item_limit']
							,'goods_item_limit'=>$_POST['goods_item_limit']
						);
		config_save_array('sms_goods_limit', $goods_limit);

		###
		$callback = "parent.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function email(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		###
		$email_arr = array("join","order","settle","delivery","cancel","findid","confirm","cs");

		###
		/*
		for($i=0;$i<count($email_arr);$i++){
			$user_chk = $email_arr[$i]."_user_yn";
			config_save('email',array($user_chk=>if_empty($_POST, $user_chk, 'N')));
			$admin_chk = $email_arr[$i]."_admin_yn";
			config_save('email',array($admin_chk=>if_empty($_POST, $admin_chk, 'N')));
		}
		*/

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

		if(isset($_POST['mail_form'])){
			$id = $_POST['mail_form'];
			config_save('email',array($id."_title"=>$_POST['title']));
			//config_save('email',array($id."_skin"=>$_POST['contents']));
			config_save('email',array($id."_skin"=>adjustEditorImages($_POST['contents'])));

			$user_chk = $id."_user_yn";
			config_save('email',array($user_chk=>if_empty($_POST, $user_chk, 'N')));
			$admin_chk = $id."_admin_yn";
			config_save('email',array($admin_chk=>if_empty($_POST, $admin_chk, 'N')));
			$admin_email = $id."_admin_email";
			config_save('email',array($admin_email=>if_empty($_POST, $admin_email, $basic['companyEmail'])));
		}

		$path = ROOTPATH."/data/email/".$id.".html";
		setHtmlFile($path, adjustEditorImages($_POST['contents']), 1);

		###
		//$callback = "parent.location.reload();";
		$callback = "";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	## sms 발송시간 제한 설정
	public function sms_restriction(){

		$sms_board = array("board_reserve_time","board_time_s","board_time_e","board_toadmin","board_touser");
		$sms_restriction = config_load('sms_restriction');
		$params = array();
		foreach($_POST as $k=>$v){
			if($k != 'mode'){
				if(is_array($v)){
					foreach($v as $k2=>$v2){
						$params[$k."__".$k2] = $v2;
					}
				}else{
					$params[$k] = $v;
				}
			}
		}
		foreach($sms_restriction as $k=>$v){
			if($_POST['mode'] == "board"){
				if(in_array($k,$sms_board)){
					if($params[$k]){
						$sms_params[$k] = $params[$k];
					}else{
						$sms_params[$k] = 'off';
					}
				}else{
						$sms_params[$k] = $v;
				}
			}else{
				if(!in_array($k,$sms_board)){
					if($params[$k]){
						$sms_params[$k] = $params[$k];
					}else{
						$sms_params[$k] = 'off';
					}
				}else{
						$sms_params[$k] = $v;
				}

			}
		}
		if($params)		$sms_rest = $params;
		if($sms_params) $sms_rest = $sms_params;
		if($sms_params && $params){
			$sms_rest = array_merge($params,$sms_params);
		}
		config_save_array('sms_restriction',$sms_rest);

		if($_POST['mode'] == "board"){
			$callback = "parent.document.location.href='../board/main';";
		}else{
			$callback = "parent.document.location.href='../member/sms?no=4';";
		}
		openDialogAlert("저장되었습니다.",400,140,'parent',$callback);

	}

	## 고객 리마인드 서비스
	public function curation(){

		#### AUTH
		$auth = $this->authmodel->manager_limit_act('member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		//$this->load->helper('reservation');
		### 고객 리마인드 서비스 메뉴
		//$menu_loop	= curation_menu();

		### 고객 리마인드 서비스 구분
		$personal_gubun = array("personal_coupon","personal_emoney",
							"personal_membership","personal_cart",
							"personal_saleclose","personal_review");

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

		if(isset($_POST['mail_form'])){

			$id				= $_POST['mail_form'];
			$reserve_day	= "";
			$reserve_time	= "";

			## sms reservation 정보 초기화
			$sql	= "delete from fm_config where groupcd = 'sms_personal' and codecd like '".$id."%'";
			$query	= $this->db->query($sql);
			## email reservation 정보 초기화
			$sql	= "delete from fm_config where groupcd = 'email_personal' and codecd like '".$id."%'";
			$query	= $this->db->query($sql);

			config_save('personal_goods_limit',array("go_item_limit"=>$_POST['go_item_limit']));//알림 사용여부
			config_save('personal_goods_limit',array("go_item_use"=>$_POST['go_item_use']));//알림 사용여부

			config_save('personal_use',array($id."_use"=>$_POST['personal_use']));		//알림 사용여부
			config_save('sms_personal',array($id."_title"=>$_POST['title_sms']));
			config_save('sms_personal',array($id."_user_yn"=>if_empty($_POST, 'user_yn_sms', 'N')));
			config_save('email_personal',array($id."_title"=>$_POST['title_email']));
			config_save('email_personal',array($id."_skin"=>$_POST['contents']));
			config_save('email_personal',array($id."_skin"=>adjustEditorImages($_POST['contents'])));
			config_save('email_personal',array($id."_user_yn"=>if_empty($_POST, 'user_yn_email', 'N')));

			$shorturl_use		= ($_POST['shorturl_use'])?$_POST['shorturl_use']:'N';
			config_save('snssocial',array('shorturl_use'=> $shorturl_use));

			if($_POST['personal_day']){
				$reserve_day	= $_POST['personal_day'];
				config_save('sms_personal',array($id."_day"=>$reserve_day));			//예약시간
				config_save('email_personal',array($id."_day"=>$reserve_day));		//예약시간
			}
			if($_POST['personal_time']){
				$reserve_time	= $_POST['personal_time'];
				config_save('sms_personal',array($id."_time"=>$reserve_time));			//예약시간
				config_save('email_personal',array($id."_time"=>$reserve_time));		//예약시간
			}
		}

		$path = ROOTPATH."/data/email/".$id.".html";
		setHtmlFile($path, adjustEditorImages($_POST['contents']), 1);

		###
		//$callback = "parent.location.reload();";
		$callback = "";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	## 고객 리마인드 서비스 세팅값 불러오기
	public function getPersonalReservation(){

		$this->load->helper('reservation');

		### 큐레이션 발송 구분
		$menu_loop	= curation_menu();

		$id				= $_GET['id'];
		$personal_use	= config_load('personal_use');
		$email_config	= config_load('email_personal');
		$sms_config		= config_load('sms_personal');

		$user_yn		= $selected = array();

		$reserve_use				= trim($personal_use[$id."_use"]);
		$reserve_day				= trim($sms_config[$id."_day"]);
		$reserve_time				= trim($sms_config[$id."_time"]);
		$select_use[$reserve_use]	= "selected";
		$select_day[$reserve_day]	= "selected";
		$select_time[$reserve_time]	= "selected";

		$user_yn['sms']= "checked";
		if($email_config[$id."_user_yn"] == "Y") $user_yn['email'] = "checked";

		if($reserve_use != "y"){
			$disabled = "disabled";
		}else{
			$disabled = "";
		}

		## 전월 총 일수
		$month_t	= date("t",strtotime(date("Y-m-d H:i:s")." -1 month"));
		$loop_day	= array();
		for($i=1; $i<$month_t;$i++) $loop_day[] = ((int)$i<10) ? "0".(int)$i:$i;

		## 예약 시간대
		$loop_time = array();
		for($i=8; $i<=22;$i++) $loop_time[] = ((int)$i<10) ? "0".(int)$i:$i;
		## 등급 조정 안내일
		$loop_mgrp_day			= array('1','3','5','7','10','15','20','30');		
		$loop_timesale_day		= array('lastday'=>'타임세일 마지막날','before'=>'타임세일 종료 하루 전');
		$loop_delivconfirm_day	= array('1','2','3','4','5','6','7','8','9','10','12','15','20');
		## 발송 예약 시간
		$tmp_time				= array();
		$tmp_time[]				= "<select name='personal_time' ".$disabled.">";
		foreach($loop_time as $k) $tmp_time[] = "<option value='".$k."' ".$select_time[$k].">".$k."시경</option>";
		$tmp_time[]				= "</select>";
		## 회원등급 조정안내일
		$tmp_day				= array();
		$tmp_day[]				= "<select name='personal_day' ".$disabled.">";
		foreach($loop_day as $k) $tmp_day[] = "<option value='".$k."' ".$select_day[(int)$k].">".$k."일</option>";
		$tmp_day[]				= "</select>";

		$tmp_mgrp_day			= array();
		$tmp_mgrp_day[]			= "<select name='personal_day' ".$disabled.">";
		foreach($loop_mgrp_day as $k) $tmp_mgrp_day[] = "<option value='".$k."' ".$select_day[$k].">조정일 +".$k."일</option>";
		$tmp_mgrp_day[]			= "</select>";
		## 장바구니알림일
		$tmp_cart_day				= array();
		$tmp_cart_day[]				= "<select name='personal_day' ".$disabled.">";
		foreach($loop_day as $k) if($k < 15) $tmp_cart_day[] = "<option value='".(int)$k."' ".$select_day[(int)$k].">+".(int)$k."일 </option>";
		$tmp_cart_day[]				= "</select>";
		## 타임세일 안내일정
		$tmp_timesale_day		= array();
		$tmp_timesale_day[]		= "<select name='personal_day' ".$disabled.">";
		foreach($loop_timesale_day as $k=>$v) $tmp_timesale_day[] = "<option value='".$k."' ".$select_day[$k].">".$v."</option>";
		$tmp_timesale_day[]		= "</select>";
		## 구매확정 안내일정
		$tmp_delivconfirm_day	= array();
		$tmp_delivconfirm_day[]	= "<select name='personal_day' ".$disabled.">";
		foreach($loop_delivconfirm_day as $k) $tmp_delivconfirm_day[] = "<option value='".$k."' ".$select_day[$k].">출고완료일 +".$k."일</option>";
		$tmp_delivconfirm_day[]	= "</select>";
		## 리뷰작성 안내일정
		$tmp_review_day			= array();
		$tmp_review_day[]		= "<select name='personal_day' ".$disabled.">";
		foreach($loop_delivconfirm_day as $k) $tmp_review_day[] = "<option value='".$k."' ".$select_day[$k].">배송완료일 +".$k."일</option>";
		$tmp_review_day[]		= "</select>";
	
		$reserv_time			= implode("",$tmp_time);
		$reserv_day				= implode("",$tmp_day);
		$reserv_cart_day		= implode("",$tmp_cart_day);
		$reserv_mgrp_day		= implode("",$tmp_mgrp_day);
		$reserv_timesale_day	= implode("",$tmp_timesale_day);
		$reserv_delivconfirm_day = implode("",$tmp_delivconfirm_day);
		$reserv_review_day		= implode("",$tmp_review_day);

		## 서비스 타입별 안내메세지 및 타이틀
		$title_sms		= $sms_config[$id."_title"];
		$title_email	= $email_config[$id."_title"];

		$tmp_reserv_use = '<select name="personal_use" onchange="reserve_use(this.value)"><option value="n" '.$select_use['n'].' style="color: #FF0000">사용안함</option><option value="y" '.$select_use['y'].' style="color:#0000ff">사용함</option></select><br />';
		switch($id){
			case 'personal_coupon':
				$reserv_msg		= "이번주에 만료되는 쿠폰을 보유한 회원에게 → 해당 주 월요일 ".$reserv_time."에 만료 쿠폰 사용 안내";
			break;
			case 'personal_emoney':
				$reserv_msg		= "다음달에 소멸되는 적립금을 보유한 회원에게 → 전월 ".$reserv_day." ".$reserv_time."에 소멸 적립금 사용 안내";
			break;
			case 'personal_membership':
				$reserv_msg		= "회원등급 자동 조정(갱신)일에 등급이 변경된 회원에게 → ".$reserv_mgrp_day." ".$reserv_time."에 회원등급 혜택 안내";
			break;
			case 'personal_cart':
				$reserv_msg		= "장바구니 또는 위시리스트에 상품을 담은 회원에게 → 상품을 마지막 담은날짜 ".$reserv_cart_day." ".$reserv_time."에 담은 상품 안내";
			break;
			case 'personal_timesale':
				$reserv_msg		= "장바구니 또는 위시리스트에 타임세일 상품을 담은 회원에게 → ".$reserv_timesale_day." ".$reserv_time."에 담은 상품 안내";
			break;
			case 'personal_deliveryconfirm':
				$reserv_msg		= "상품을 배송 받은 회원에게 → ".$reserv_delivconfirm_day." ".$reserv_time."에 상품수령확인(구매확정) 안내";
			break;
			case 'personal_review':
				$reserv_msg		= "상품을 배송 받은 회원에게 → ".$reserv_review_day." ".$reserv_time."에 구매상품 리뷰 작성 안내";
			break;
		}

		/* db에 저장된 SMS/Email Title 이 없을 때 */
		if(!$title_sms){ 
			switch($id){
				case 'personal_coupon':
					$title_sms	= "[{shopName}] 이번주 만료되는 할인쿠폰이 {coupon_count}종 있습니다. 잊지말고 사용하세요! {mypage_short_url}";
				break;
				case 'personal_emoney':
					$title_sms	= "[{shopName}] 다음달에 보유하고 계신 적립금 {mileage_rest}원이 소멸될 예정입니다. 소멸되기 전에 꼭 사용하세요! {mypage_short_url}";
				break;
				case 'personal_membership':
					$title_sms	= "[{shopName}] {username} 님의 회원등급은 {userlevel} 등급입니다. 마이페이지에서 등급 혜택을 확인하세요! {mypage_short_url}";
				break;
				case 'personal_cart':
					$title_sms	= "[{shopName}] {go_item}의 상품이 장바구니/위시리스트에 담겨있습니다. {mypage_short_url}";
				break;
				case 'personal_timesale':
					$title_sms	= "[{shopName}] {username} {go_item}의 상품이 곧 판매 종료됩니다. {mypage_short_url}";
				break;
				case 'personal_deliveryconfirm':
					$title_sms	= "[{shopName}] {username} 회원님, 상품수령을 확인하시고 적립금을 받으세요. {mypage_short_url}";
				break;
				case 'personal_review':
					$title_sms	= "[{shopName}] {username} 회원님, 구매하신 상품 직접 사용 해 보니 어떠세요? 상품평 작성하시고 적립금을 받으세요. {mypage_short_url}";
				break;
			}
		}
		if(!$title_email){ 
			switch($id){
				case 'personal_coupon':
					$title_email	= "[{shopName}] 이번주 만료되는 할인쿠폰이 {coupon_count}종 있습니다. 잊지말고 사용하세요!";
				break;
				case 'personal_emoney':
					$title_email	= "[{shopName}] 다음달에 보유하고 계신 적립금 {mileage_rest}원이 소멸될 예정입니다. 소멸되기 전에 꼭 사용하세요! ";
				break;
				case 'personal_membership':
					$title_email	= "[{shopName}] {username} 님의 회원등급은 {userlevel} 등급입니다.";
				break;
				case 'personal_cart':
					$title_email	= "[{shopName}] {go_item}의 상품이 장바구니/위시리스트에 담겨있습니다.";
				break;
				case 'personal_timesale':
					$title_email	= "[{shopName}] {go_item}의 상품이 곧 판매 종료됩니다.";
				break;
				case 'personal_deliveryconfirm':
					$title_email	= "[{shopName}] {username} 회원님, 상품수령을 확인하시고 적립금을 받으세요.";
				break;
				case 'personal_review':
					$title_email	= "[{shopName}] {username} 회원님, 구매하신 상품 직접 사용 해 보니 어떠세요? 상품평 작성하시면 적립금을 드립니다.";
				break;
			}
		}

		$personal_sms = '
		<div class="use_sms">
			<label><input type="checkbox" name="user_yn_sms" id="user_yn_sms" value="Y" '.$user_yn['sms'].' '.$disabled.' onclick="smsRequire(this)"> <strong>수신동의 고객 → SMS</strong></label>  <span class="btn small cyanblue"><input type="button" value="치환코드" name="coupon" title="사용가능한 치환코드" onclick="info_code()"/></span>
		</div>
		<div class="title_sms">
			<input type="text" name="title_sms" id="title_sms" value="'.$title_sms.'" '.$disabled.' style="width:99%">
		</div>';

		$personal_email = '
		<div class="use_email">
			<label><input type="checkbox" name="user_yn_email" id="user_yn_email" value="Y" '.$user_yn['email'].' '.$disabled.'> <strong>수신동의 고객 → EMAIL</strong></label>  <span class="btn small cyanblue"><input type="button" value="치환코드" name="coupon" title="사용가능한 치환코드"  onclick="info_code()"/></span>
		</div>
		<div class="title_email">
			<input type="text" name="title_email" id="title_email" value="'.$title_email.'" '.$disabled.'>
		</div>';

		# title
		foreach($menu_loop as $k=>$v){
			if($v['name'] == $id) $personal_title = $v['title']." ".$v['etc'];
		}

		###
		$path = ROOTPATH."/data/email/".$id.".html";
		$data = getHtmlFile($path);

		$result = array("personal_title"	=>$personal_title,
						"personal_sms"		=>$personal_sms,
						"personal_email"	=>$personal_email,
						"personal_msg"		=>$tmp_reserv_use.$reserv_msg,
						"contents"			=>$data,
						"html"				=>$html
					);
		echo "[".json_encode($result)."]";
	}

	public function getmail(){
		$id		= $_GET['id'];

		$email = config_load('email');
		$title = isset($email[$id.'_title']) ? $email[$id.'_title'] : "";
		$contents = isset($email[$id.'_skin']) ? $email[$id.'_skin'] : " ";

		$user_chk	= $email[$id."_user_yn"]=='Y' ? "checked" : "";
		$admin_chk	= $email[$id."_admin_yn"]=='Y' ? "checked" : "";
		$admin_email = $email[$id."_admin_email"];

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		if(!$admin_email) $admin_email = $basic['companyEmail'];

		$html = "<label><input type='checkbox' name='".$id."_user_yn' value='Y' ".$user_chk."/> 고객</label> ";
		$html .= "<label><input type='checkbox' name='".$id."_admin_yn' value='Y' ".$admin_chk."/> 관리자</label> ";
		$html .= "<input type='text' name='".$id."_admin_email' value='{$admin_email}' />";

		###
		$path = ROOTPATH."/data/email/".$id.".html";
		$data = getHtmlFile($path);

		$result = array("title"=>$title,"contents"=>$data,"html"=>$html);
		echo "[".json_encode($result)."]";
	}

	public function logmail(){
		$seq	= $_GET['seq'];
		$query = $this->db->query("select * from fm_log_email where seq = '{$seq}'");
		$emailData = $query->result_array();
		$title = isset($emailData[0]['subject']) ? $emailData[0]['subject'] : "";
		$contents = isset($emailData[0]['contents']) ? $emailData[0]['contents'] : " ";
		$result = array("title"=>$title,"contents"=>$contents);
		echo "[".json_encode($result)."]";
	}

	public function getlogmail(){
		$seq	= $_GET['seq'];
		$query = $this->db->query("select * from fm_log_email where seq = '{$seq}'");
		$emailData = $query->result_array();
		$contents = isset($emailData[0]['contents']) ? $emailData[0]['contents'] : " ";
		$subject = isset($emailData[0]['subject']) ? $emailData[0]['subject'] : " ";
		$total = isset($emailData[0]['total']) ? $emailData[0]['total'] : " ";
		$regdate = isset($emailData[0]['regdate']) ? $emailData[0]['regdate'] : " ";
		$result = array("subject"=>$subject,"total"=>$total,"regdate"=>$regdate,"contents"=>$contents);
		//echo $result;
		echo "[".json_encode($result)."]";
	}

	## 고객리마인드서비스 메일발송내역 상세
	public function getlogcuration(){
		$seq			= $_GET['seq'];
		$query			= $this->db->query("select * from fm_log_curation_email where seq = '{$seq}'");
		$emailData		= $query->result_array();
		$contents		= isset($emailData[0]['contents']) ? $emailData[0]['contents'] : " ";
		$subject		= isset($emailData[0]['subject']) ? $emailData[0]['subject'] : " ";
		$to_email		= isset($emailData[0]['to_email']) ? $emailData[0]['to_email'] : " ";
		$regist_date	= isset($emailData[0]['regist_date']) ? $emailData[0]['regist_date'] : " ";
		$result			= array("subject"=>$subject,"to_email"=>$to_email,"regist_date"=>$regist_date,"contents"=>$contents);
		echo "[".json_encode($result)."]";
	}

	public function getSmsForm(){
		###
		$sc['page']				= (isset($_POST['page'])) ?		intval($_POST['page']):'0';
		$sc['perpage']			= (isset($_POST['perpage'])) ?	intval($_POST['perpage']):'4';
		$sc['category']			= (isset($_POST['category'])) ?	$_POST['category'] : null;

		//$this->load->model('membermodel');
		$data = $this->membermodel->sms_form_list($sc);

		###
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_sms_album');

		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],'sms_form?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay)) $paginlay = '<p><a class="on red">1</a><p>';

		//print_r($data);
		$result = "<table width=\"100%\" cellspacing=\"0\">";
		$result .= "<tr>";
		foreach($data['result'] as $datarow){
			$result .= "<td>";
			$result .= "	<div style='padding:2px;'>";
			$result .= "		<div class='sms-define-form'>";
			$result .= "			<div class='sdf-head clearbox'>";
			$result .= "				<div class='fl'><img src='/admin/skin/default/images/common/sms_i_antena.gif'></div>";
			$result .= "				<div class='fr'><img src='/admin/skin/default/images/common/sms_i_battery.gif'></div>";
			$result .= "			</div>";
			$result .= "			<div class='sdf-body-wrap'>";
			$result .= "				<div class='sdf-body'>";
			$result .= "					<textarea name='_user' readonly class='sms_contents' codecd='{$datarow['category']}' groupcd='sms_form' onclick=\"$('#send_message').val(this.value);send_byte_chk($('#send_message'));\">".htmlspecialchars($datarow['msg'])."</textarea>";
			$result .= "					<div class='sdf-body-foot clearbox'>";
			$result .= "						<div class='fl'><b class='send_byte'>0</b>byte</div>";
			$result .= "						<div class='fr'><img src='/admin/skin/default/images/common/sms_btn_send.gif' align='absmiddle' class='del_message' /></div>";
			$result .= "					</div>";
			$result .= "				</div>";
			$result .= "			</div>";
			$result .= "		</div>";
			$result .= "	</div>";
//			$result .= "<textarea name=\"_user\" class=\"sms_contents\" readonly codecd=\"".$datarow['category']."\" groupcd=\"sms_form\"  onmouseover=\"this.className='sms_contents_border';\" onmouseout=\"this.className='sms_contents';\" onclick=\"$('#send_message').val(this.value);send_byte_chk();\">".$datarow['msg']."</textarea>";
			$result .= "<div><span class=\"btn small gray\"><button type=\"button\" class='mod_form' id=\"mod_form\" seq=\"".$datarow['seq']."\">수정</button></span> <span class=\"btn small gray\"><button type=\"button\" id=\"del_form\" class='del_form' seq=\"".$datarow['seq']."\">삭제</button></span></div>";
			$result .= "</td>";
		}
		$result .= "</tr>";
		//$result .= "</table>";
		$result .= "<tr height='15'><td colspan='4'></td></tr>";
		$result .= "<tr><td colspan='4' align='center'>";
		$result .= "<span class=\"paging_navigation\" style=\"width:100%;text-align:center;\">".$paginlay."</span>";
		$result .= "</td></tr>";
		$result .= "</table>";

		echo json_encode($result);
	}

	public function delete_smsform(){
		if(isset($_GET['seq'])){
			$result = $this->db->delete('fm_sms_album', array('seq' => $_GET['seq']));
			$callback = "parent.document.getElementById('container').src='../member/sms_form';";
			openDialogAlert("삭제 되었습니다.",400,140,'parent',$callback);
		}
	}


	public function sms_process(){
		### Validation
		if(isset($_POST['sms_form_group'])){
			$this->validation->set_rules('sms_form_group', '그룹선택','trim|required|xss_clean');
		}else{
			$this->validation->set_rules('sms_form_name', '그룹명','trim|required|xss_clean');
		}
		$this->validation->set_rules('sms_form_text', '보관메세지','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$params['category'] = isset($_POST['sms_form_group']) ? $_POST['sms_form_group'] : $_POST['sms_form_name'];
		$params['msg'] = $_POST['sms_form_text'];

		if($_POST['album_seq']){
			$this->db->where('seq', $_POST['album_seq']);
			$result = $this->db->update('fm_sms_album', $params);
		}else{
			$result = $this->db->insert('fm_sms_album', $params);
		}

		###
		$callback = "parent.document.getElementById('container').src='../member/sms_form';";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}



	public function send_email(){
		### Validation
		if($_POST['send_num'] < 1){
			$callback = "";
			openDialogAlert('받는사람이 없습니다.',400,140,'parent',$callback);
			exit;
		}
		$this->validation->set_rules('title', '제목','trim|required|max_length[100]|xss_clean');
		//$this->validation->set_rules('contents', '내용','trim|required|xss_clean');
		$this->validation->set_rules('send_email', '보내는사람','trim|required|max_length[50]|valid_email|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		unset($mailArr);
		$mailArr = explode(",", $_POST["send_to"]);
		unset($mailArr[0]);
		if(isset($_POST['add_num_chk'])!='Y'){
			$key = get_shop_key();
			switch($_POST['member']){
				case "all":
					$query = $this->db->query("select AES_DECRYPT(UNHEX(email), '{$key}') as email from fm_member where status != 'withdrawal' and email<>'' and mailing = 'y'");
					$data = $query->result_array();
					if(count($data)>0){
						foreach($data as $k){
							array_push($mailArr,$k['email']);
						}
					}
					break;
				case "search":
					//echo urldecode($_POST["serialize"]);
					$tempArr = explode("&",urldecode($_POST["serialize"]));
					foreach($tempArr as $k){
						$tmp = explode("=",$k);
						if($tmp[1]){
							$sc[$tmp[0]] = $tmp[1];
						}
					}
					//$sc['mailing'] = 'y';
					if($sc["keyword"] == "이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소, 닉네임"){
						$sc["keyword"] = "";
					}
					//$this->load->model('membermodel');
					$data = $this->membermodel->admin_search_list($sc);
					if(count($data['result'])>0){
						foreach($data['result'] as $k){
							array_push($mailArr,$k['email']);
						}
					}
					break;
				case "select":
					$tempArr = explode(",", $_POST["serialize"]);
					unset($tempArr[0]);
					foreach($tempArr as $k){
						$query = $this->db->query("select AES_DECRYPT(UNHEX(email), '{$key}') as email from fm_member where member_seq = '{$k}' and email<>'' ");
						$data = $query->result_array();
						if($data[0]['email']) array_push($mailArr,$data[0]['email']);
					}
					break;
				case "excel":
					break;
			}

		}
		//print_r($mailArr);
		//exit;

		if (count($mailArr) < 1) {
			$callback = "";
			openDialogAlert('받는사람 이메일은 필수입니다.',400,140,'parent',$callback);
			exit;
		}

		###
		$this->load->model('usedmodel');
		$email_chk = $this->usedmodel->hosting_check();

		###
		$total = count($mailArr);
		$toMonth = date("Y-m");
		$sql = "select sum(total) as count from fm_log_email where regdate like '{$toMonth}%'";
		$query = $this->db->query($sql);
		$emailData = $query->result_array();
		$usedMail	= $emailData[0]['count'] + $total;
		if(3000 < $usedMail  && !$email_chk && !preg_match("/^F_SH_/",$this->config_system['service']['hosting_code'])){
			$callback = "";
			openDialogAlert('본 이메일 발송 기능은 월 3,000통 발송이 가능합니다.<br>더 많은 이메일 발송은 대량발송 서비스를 이용해 주십시오.',400,140,'parent',$callback);
			exit;
		}
		###
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		//sendDirectMail($mailArr, $_POST['send_email'], $_POST['title'], $_POST['contents']);
		require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/Email_send.class.php";
		$mail = new Mail(isset($params));
		$body = adjustEditorImages($_POST['contents']);
		foreach($mailArr as $k){
			if(filter_var($k,FILTER_VALIDATE_EMAIL)!=false){
				$headers['From']    = $_POST['send_email'];
				$headers['Name']	= !$basic['companyName'] ? 'http://'.$_SERVER['HTTP_HOST'] : $basic['companyName'];
				$headers['Subject'] = $_POST['title'];
				$headers['To'] = $k;
				$resSend = $mail->send($headers, $body);
			}
		}

		### LOG
		$params['regdate']		= date('Y-m-d H:i:s');
		$params['gb']			= 'MANUAL';
		$params['total']		= $total;
		$params['from_email']	= $_POST['send_email'];
		$params['subject']		= $_POST['title'];
		$params['contents']		= $body;
		$data = filter_keys($params, $this->db->list_fields('fm_log_email'));
		$result = $this->db->insert('fm_log_email', $data);

		### MASTER
		config_save('master',array('mail_count'=>(3000 - $usedMail)));

		$callback = "parent.document.getElementById('container').src='../member/email_form';";
		//$callback = "parent.location.reload();";
		$msg = "메일이 발송 되었습니다.";
		openDialogAlert($msg,400,140,'parent',$callback);
		exit;
	}


	public function send_sms(){
		
		### Validation
		if($_POST['send_num'] < 1){
			$callback = "parent.container.sms_loading_stop();";
			openDialogAlert('받는사람이 없습니다.',400,140,'parent',$callback);
			exit;
		}
		$this->validation->set_rules('send_message', '내용','trim|required|xss_clean');
		$this->validation->set_rules('send_sms', '보내는사람','trim|required|max_length[50]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus(); parent.container.sms_loading_stop();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$phoneNo = explode(",", $_POST["send_to"]);
		unset($phoneNo[0]);
		$key = get_shop_key();

		if(isset($_POST['add_num_chk'])!='Y'){
			switch($_POST['member']){
				case "all":
					$sql = "SELECT
									AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone ,
									B.bcellphone, B.business_seq
									 FROM fm_member A
									LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
									WHERE status != 'withdrawal' and sms = 'y' ";
					$query = $this->db->query($sql);
					$data = $query->result_array();
					if(count($data)>0){
						foreach($data as $k){
							if($k['business_seq'] ) { //기업회원
								if($k['bcellphone']) array_push($phoneNo, preg_replace("/[^0-9]*/s", "", $k['bcellphone']));
							}else{
								if($k['cellphone']) array_push($phoneNo, preg_replace("/[^0-9]*/s", "", $k['cellphone']));
							}
						}
					}
					break;
				case "search":
					//echo urldecode($_POST["serialize"]);
					$tempArr = explode("&",urldecode($_POST["serialize"]));
					foreach($tempArr as $k){
						$tmp = explode("=",$k);
						if($tmp[1]){
							$sc[$tmp[0]] = $tmp[1];
						}
					}
					//$sc['sms'] = 'y';
					if($sc["keyword"] == "이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소, 닉네임"){
						$sc["keyword"] = "";
					}

					//$this->load->model('membermodel');
					$data = $this->membermodel->admin_search_list($sc);
					if(count($data['result'])>0){
						foreach($data['result'] as $k){
							//array_push($phoneNo,$k['cellphone']);
							if($k['business_seq'] ) { //기업회원
								if($k['bcellphone']) array_push($phoneNo, preg_replace("/[^0-9]*/s", "", $k['bcellphone']));
							}else{
								if($k['cellphone']) array_push($phoneNo, preg_replace("/[^0-9]*/s", "", $k['cellphone']));
							}

						}
					}
					break;
				case "select":
					$tempArr = explode(",", $_POST["serialize"]);
					unset($tempArr[0]);
					foreach($tempArr as $k){
					$sql = "SELECT
									AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone ,
									B.bcellphone, B.business_seq
									 FROM fm_member A
									LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
									WHERE A.member_seq = '{$k}'  ";

						$query = $this->db->query($sql);
						$data = $query->result_array();

						if($data[0]['business_seq'] ) { //기업회원
							if($data[0]['bcellphone']) array_push($phoneNo, preg_replace("/[^0-9]*/s", "", $data[0]['bcellphone']));
						}else{
							if($data[0]['cellphone']) array_push($phoneNo, preg_replace("/[^0-9]*/s", "", $data[0]['cellphone']));
						}
					}
					break;
				case "excel":
					break;
			}
		}
		
		###
		$params['msg'] = trim($_POST["send_message"]);
		$euckr_str = mb_convert_encoding($params['msg'],'EUC-KR','UTF-8');
		$len = strlen($euckr_str);

		$from		= $_POST["send_sms"];

		if($len > 90){
			$sms_type = "LMS ";
		}else{
			$sms_type = "SMS ";
		}

		$commonSmsData['member']['phone'] = $phoneNo;;
		$commonSmsData['member']['params'] = $params;
		
		$result = commonSendSMS($commonSmsData);
		
		$callback = "parent.container.sms_loading_stop();";
		if($result['msg'] == "fail"){
			$result_msg = "%이후에는 문자가 올수 없습니다.<br>예) 30%DC -> 30% DC";
		}else{
			$result_code = $result['code'];
			if($result_code != "0000"){
				if($result_code == "E001"){
					$result_msg = "SMS 인증 정보가 잘못되었습니다.";
				}else{
					$result_msg = $sms_type."발송에 실패했습니다.";
				}
			}else{
				//$callback = "parent.location.reload();";
				$result_msg = $sms_type."발송에 성공하였습니다.";
			}
		}

		openDialogAlert($result_msg,400,140,'parent',$callback);
		exit;
	}


	public function set_emoney(){
		### Validation
		if($_POST['send_member'] < 1){
			$callback = "";
			openDialogAlert('선택된 회원이 없습니다.',400,140,'parent',$callback);
			exit;
		}
		### Validation
		$this->validation->set_rules('emoney', '적립금','trim|required|xss_clean');
		$this->validation->set_rules('memo', '사유','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		unset($memberArr);
		//$this->load->model('membermodel');
		switch($_POST['member']){
			case "all":
				$key = get_shop_key();
				$query = $this->db->query("select member_seq from fm_member where status != 'withdrawal'");
				foreach($query->result_array() as $v){
					$memberArr[] = $v['member_seq'];
				}
				break;
			case "search":
				//echo urldecode($_POST["serialize"]);
				$tempArr = explode("&",urldecode($_POST["serialize"]));
				foreach($tempArr as $k){
					$tmp = explode("=",$k);
					if($tmp[1]){
						$sc[$tmp[0]] = $tmp[1];
					}
				}
				$data = $this->membermodel->admin_search_list($sc);
				$memberArr = $data['result'];
				break;
			case "select":
				$memberArr = explode(",", $_POST["serialize"]);
				unset($memberArr[0]);
				break;
			case "excel":
				break;
		}

		$_POST['type'] = 'direct';
		$_POST['manager_seq'] = $this->managerInfo['manager_seq'];//적립금 수동지급시 관리자정보추가
		if($_POST['reserve_select']){
			if($_POST['reserve_select']=='year'){
				//$_POST['limit_date'] = $_POST['reserve_year']."-12-31";
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['reserve_year']));
			}else{
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['reserve_direct'][$i], date("d"), date("Y")));
			}
		}
		foreach($memberArr as $k){
			$this->membermodel->emoney_insert($_POST, $k);
		}

		$callback = "parent.location.reload(); parent.document.getElementById('container').src='../member/emoney_form';";
		openDialogAlert("적립금이 적용 되었습니다.",400,140,'parent',$callback);
		exit;
	}

	public function set_point(){
		### Validation
		if($_POST['send_member'] < 1){
			$callback = "";
			openDialogAlert('선택된 회원이 없습니다.',400,140,'parent',$callback);
			exit;
		}
		### Validation
		$this->validation->set_rules('point', '포인트','trim|required|xss_clean');
		$this->validation->set_rules('memo', '사유','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		unset($memberArr);
		//$this->load->model('membermodel');
		switch($_POST['member']){
			case "all":
				$key = get_shop_key();
				$query = $this->db->query("select member_seq from fm_member where status != 'withdrawal'");
				foreach($query->result_array() as $v){
					$memberArr[] = $v['member_seq'];
				}
				break;
			case "search":
				//echo urldecode($_POST["serialize"]);
				$tempArr = explode("&",urldecode($_POST["serialize"]));
				foreach($tempArr as $k){
					$tmp = explode("=",$k);
					if($tmp[1]){
						$sc[$tmp[0]] = $tmp[1];
					}
				}
				$data = $this->membermodel->admin_search_list($sc);
				$memberArr = $data['result'];
				break;
			case "select":
				$memberArr = explode(",", $_POST["serialize"]);
				unset($memberArr[0]);
				break;
			case "excel":
				break;
		}

		$_POST['type'] = 'direct';
		$_POST['manager_seq'] = $this->managerInfo['manager_seq'];//적립금 수동지급시 관리자정보추가
		if($_POST['reserve_select']){
			if($_POST['reserve_select']=='year'){
				//$_POST['limit_date'] = $_POST['reserve_year']."-12-31";
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['reserve_year']));
			}else{
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['reserve_direct'][$i], date("d"), date("Y")));
			}
		}
		foreach($memberArr as $k){
			$this->membermodel->point_insert($_POST, $k);
		}

		$callback = "parent.location.reload(); parent.document.getElementById('container').src='../member/point_form';";
		openDialogAlert("포인트가 적용 되었습니다.",400,140,'parent',$callback);
		exit;
	}

	public function emoney_detail(){
		### Validation
		$this->validation->set_rules('emoney', '적립금','trim|required|xss_clean');
		$_POST['memo'] = $_POST['memo_type']=='direct' ? $_POST['memo_direct'] : $_POST['memo_type'];
		$this->validation->set_rules('memo', '사유','trim|required|xss_clean');
		if($_POST['send_sms']=='Y'){
			$this->validation->set_rules('cellphone', '핸드폰번호','trim|required|xss_clean');
			$this->validation->set_rules('msg', '메세지','trim|required|xss_clean');
		}
		###
		if($_POST['reserve_select']=='year'){
			//$this->validation->set_rules('reserve_year', '지급연도','trim|required|max_length[4]|min_length[4]|numeric|xss_clean');

		}else if($_POST['reserve_select']=='direct'){
			$this->validation->set_rules('reserve_direct', '제한개월','trim|required|max_length[4]|min_length[1]|numeric|xss_clean');
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		$_POST['type'] = 'direct';
		//$this->load->model('membermodel');

		$_POST['manager_seq'] = $this->managerInfo['manager_seq'];
		if($_POST['reserve_select']){
			if($_POST['reserve_select']=='year'){
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['reserve_year']));// $_POST['reserve_year']."-12-31";
			}else{
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['reserve_direct'], date("d"), date("Y")));
			}
		}
		$this->membermodel->emoney_insert($_POST, $_POST['member_seq']);		

		$sms_result = "";
		if($_POST['send_sms']=='Y'){

		
			###
			$str = trim($_POST["msg"]);
			$euckr_str = mb_convert_encoding($str,'EUC-KR','UTF-8');
			$len = strlen($euckr_str);

			if($len > 90){
				$sms_type = "LMS ";
			}else{
				$sms_type = "SMS ";
			}
			$params['msg'] = $str;
			$commonSmsData['member']['phone'] = $_POST['cellphone'];
			$commonSmsData['member']['params'] = $params;
			
			$result = commonSendSMS($commonSmsData);

			if($result['msg'] == "fail"){
				$result_msg = "%이후에는 문자가 올수 없습니다.<br>예) 30%DC -> 30% DC";
			}else{
				$result_code = $result['code'];
				if($result_code != "0000"){
					if($result_code == "E001"){
						$result_msg = "SMS 인증 정보가 잘못되었습니다.";
					}else{
						$result_msg = $sms_type."발송에 실패했습니다.";
					}
				}else{
					$result_msg = $sms_type."발송에 성공하였습니다.";
				}
			}
			$sms_result = "<br> SMS : ".$result_msg;
		}

		$callback = "parent.emoney_pop();parent.location.reload();";
		openDialogAlert("적립금이 적용 되었습니다.".$sms_result,400,140,'parent',$callback);
		exit;
	}

	public function point_detail(){
		### Validation
		$this->validation->set_rules('point', '포인트','trim|required|xss_clean');
		$_POST['memo'] = $_POST['memo_type']=='direct' ? $_POST['memo_direct'] : $_POST['memo_type'];
		$this->validation->set_rules('memo', '사유','trim|required|xss_clean');
		if($_POST['send_sms']=='Y'){
			$this->validation->set_rules('cellphone', '핸드폰번호','trim|required|xss_clean');
			$this->validation->set_rules('msg', '메세지','trim|required|xss_clean');
		}

		###
		if($_POST['reserve_select']=='year'){
			//$this->validation->set_rules('reserve_year', '지급연도','trim|required|max_length[4]|min_length[4]|numeric|xss_clean');

		}else if($_POST['reserve_select']=='direct'){
			$this->validation->set_rules('reserve_direct', '제한개월','trim|required|max_length[4]|min_length[1]|numeric|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		$_POST['type'] = 'direct';
		//$this->load->model('membermodel');

		$_POST['manager_seq'] = $this->managerInfo['manager_seq'];
		if($_POST['reserve_select']){
			if($_POST['reserve_select']=='year'){
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['reserve_year']));//$_POST['reserve_year']."-12-31";
			}else{
				$_POST['limit_date'] = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['reserve_direct'], date("d"), date("Y")));
			}
		}
		$this->membermodel->point_insert($_POST, $_POST['member_seq']);

		$sms_result = "";
		if($_POST['send_sms']=='Y'){


			###
			$str = trim($_POST["msg"]);
			$euckr_str = mb_convert_encoding($str,'EUC-KR','UTF-8');
			$len = strlen($euckr_str);


			if($len > 90){
				$sms_type = "LMS ";
			}else{
				$sms_type = "SMS ";
			}

			$params['msg'] = $str;

			$commonSmsData['member']['phone'] = $_POST['cellphone'];
			$commonSmsData['member']['params'] = $params;
			
			$result = commonSendSMS($commonSmsData);


			if($result['msg'] == "fail"){
				$result_msg = "%이후에는 문자가 올수 없습니다.<br>예) 30%DC -> 30% DC";
			}else{
				$result_code = $result['code'];
				if($result_code != "0000"){
					if($result_code == "E001"){
						$result_msg = "SMS 인증 정보가 잘못되었습니다.";
					}else{
						$result_msg = $sms_type."발송에 실패했습니다.";
					}
				}else{
					$result_msg = $sms_type."발송에 성공하였습니다.";
				}
			}
			$sms_result = "<br> SMS : ".$result_msg;
		}

		$callback = "parent.emoney_pop();parent.location.reload();";
		openDialogAlert("포인트가 적용 되었습니다.".$sms_result,400,140,'parent',$callback);
		exit;
	}

	public function sms_pop(){
		
		### Validation
		$this->validation->set_rules('cellphone', '핸드폰번호','trim|required|xss_clean');
		$this->validation->set_rules('msg', '메세지','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['send_sms']=='Y'){

			if($_POST['board_id']) {
				$cellphone = explode(",",$_POST['cellphone']);
				$idx = 0;
				foreach($cellphone as $tophone){
					if(!$tophone)continue;
					$dataTo[$idx]["phone"] = $tophone;
					$idx++;
				}
			}else{
				$dataTo[0]["phone"] = $_POST['cellphone'];
			}

			if($_POST['board_id']) {
				$phone = explode(",",$_POST['cellphone']);
			}else{
				$phone		= $_POST['cellphone'];
			}
			
			$params['msg'] = trim($_POST["msg"]);

			$commonSmsData['member']['phone'] = $phone;
			$commonSmsData['member']['params'] = $params;
			
			$result = commonSendSMS($commonSmsData);

			$chk_popup_close = true;
			if($result['msg'] == "fail"){
				$result_msg = "%이후에는 문자가 올수 없습니다.<br>예) 30%DC -> 30% DC";
			}else{
				$result_code = $result['code'];
				if($result_code != "0000"){
					if($result_code == "E001"){
						$result_msg = "SMS 인증 정보가 잘못되었습니다.";
					}else{
						$result_msg = $sms_type."발송에 실패했습니다.";
					}
				}else{
					$result_msg = $sms_type."발송에 성공하였습니다.";
					$chk_popup_close = false;
				}
			}
		}

		$callback = ($chk_popup_close) ? "" :"parent.closeDialog('sendPopup');";
		openDialogAlert($result_msg,400,140,'parent',$callback);
		exit;
	}

	public function email_pop(){
		$this->validation->set_rules('title', '제목','trim|required|max_length[100]|xss_clean');
		/* xss_clean 삭제 : css 주석처리 적용문제, 인라인 css 적용이 잘리는 현상 발생으로 삭제 */
		$this->validation->set_rules('contents', '내용','trim|required');
		$this->validation->set_rules('email', '받는사람','trim|required|max_length[50]|valid_email|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$this->load->model('usedmodel');
		$email_chk = $this->usedmodel->hosting_check();

		###
		$total = 1;
		$toMonth = date("Y-m");
		$sql = "select sum(total) as count from fm_log_email where regdate like '{$toMonth}%'";
		$query = $this->db->query($sql);
		$emailData = $query->result_array();
		$usedMail	= $emailData[0]['count'] + $total;
		if(3000 < $usedMail  && !$email_chk && !preg_match("/^F_SH_/",$this->config_system['service']['hosting_code']) ){
			$callback = "";
			openDialogAlert('본 이메일 발송 기능은 월 3,000통 발송이 가능합니다.<br>더 많은 이메일 발송은 대량발송 서비스를 이용해 주십시오.',400,140,'parent',$callback);
			exit;
		}

		$_POST['contents'] = str_replace("&lt;","<",$_POST['contents']);

		###
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		//sendDirectMail($mailArr, $_POST['send_email'], $_POST['title'], $_POST['contents']);
		require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/Email_send.class.php";
		$mail = new Mail(isset($params));
		$body = adjustEditorImages($_POST['contents']);

		if(filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)!=false){
			$headers['From']		= !$basic['companyEmail'] ? 'gabia@gabia.com' : $basic['companyEmail'];
			$headers['Name']		= !$basic['shopName'] ? 'http://'.$_SERVER['HTTP_HOST'] : $basic['shopName'];
			$headers['Subject']		= $_POST['title'];
			$headers['To']			= $_POST['email'];
			$resSend				= $mail->send($headers, $body);
		}

		### LOG
		$params['regdate']		= date('Y-m-d H:i:s');
		$params['gb']			= 'MANUAL';
		$params['total']		= $total;
		$params['from_email']	= $basic['companyEmail'];
		$params['subject']		= $_POST['title'];
		$params['contents']		= $body;
		$data = filter_keys($params, $this->db->list_fields('fm_log_email'));
		$result = $this->db->insert('fm_log_email', $data);

		### MASTER
		config_save('master',array('mail_count'=>(3000 - $usedMail)));

		$callback = "parent.closeDialog('sendPopup');";
		$msg = "메일이 발송 되었습니다.";
		openDialogAlert($msg,400,140,'parent',$callback);
		exit;
	}


	public function sms_excel(){
		echo "EXCEL";
	}

	public function sms_auth(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### Validation
		$this->validation->set_rules('sms_auth', 'SMS 인증번호','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		config_save('master',array('sms_auth'=>$_POST['sms_auth']));

		###
		$callback = "parent.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}


	public function getAuthPopup(){
		$this->load->helper('admin');
		$result = getGabiaPannel('smsAuthInfo');		
		echo $result;		
	}

	public function getExcelPopup(){
		$result = "";
		$result .= "<form name='popForm' method='post' action='../member_process/sms_excel' target='actionFrame'>";
		$result .= "<table width='100%' cellspacing='0'>";
		$result .= "<tr><td>";
		$result .= "▶ 엑셀 데이터 발송 안내<br/>";
		$result .= "SMS 발송 대상을 엑셀 데이터로 등록할 수 있습니다.<br/>";
		$result .= "엑셀 양식을 다운로드 받아 형식에 맞게 데이터를 입력해 주세요. ";
		$result .= "</td></tr>";
		$result .= "<tr><td>";
		$result .= "▶ 엑셀 데이터 입력 방법<br/>";
		$result .= "1. 기본적으로 다운로드 받은 엑셀 약식을 유지한 후 데이터만 입력<br/>";
		$result .= "2. 반드시 A열에는 이름, B열에는 핸드폰번호를 입력<br/>";
		$result .= "3. 반드시 핸드폰번호는 '-'를 포함하여 입력<br/>";
		$result .= "예시) 010-123-4567<br/>";
		$result .= "4. 입력완료 후 엑셀파일 저장 형식을 *.xls로 저장 ";
		$result .= "</td></tr>";
		$result .= "<tr><td>";
		$result .= "▶ 엑셀 파일 업로드<br/>";
		$result .= "<input type='file' name=''>";
		$result .= "</td></tr>";
		$result .= "</table>";
		$result .= "<span class='btn small gray center'><button type='button' onclick='document.popForm.submit();'>확인</button></span>";
		$result .= "</form>";
		echo $result;
	}

	public function amail(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('member_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		### Validation
		$this->validation->set_rules('name', '이름','trim|required|max_length[100]|xss_clean');
		$this->validation->set_rules('email', '이메일','trim|required|max_length[64]|valid_email|xss_clean');
		$this->validation->set_rules('phoneArr[0]', '전화번호','trim|required|max_length[4]|numeric|xss_clean');
		$this->validation->set_rules('phoneArr[1]', '전화번호','trim|required|max_length[4]|numeric|xss_clean');
		$this->validation->set_rules('phoneArr[2]', '전화번호','trim|required|max_length[4]|numeric|xss_clean');
		$this->validation->set_rules('mobileArr[0]', '휴대폰','trim|required|max_length[4]|numeric|xss_clean');
		$this->validation->set_rules('mobileArr[1]', '휴대폰','trim|required|max_length[4]|numeric|xss_clean');
		$this->validation->set_rules('mobileArr[2]', '휴대폰','trim|required|max_length[4]|numeric|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		### SAVE
		$phone		= implode("-",$_POST['phoneArr']);
		$cellphone	= implode("-",$_POST['mobileArr']);
		config_save('email_mass',array('name'=>$_POST['name']));
		config_save('email_mass',array('email'=>$_POST['email']));
		config_save('email_mass',array('phone'=>$phone));
		config_save('email_mass',array('cellphone'=>$cellphone));

		$callback = "";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		exit;
	}


	public function amail_send_set(){
		ini_set("memory_limit",-1);
		set_time_limit(0);

		###
		$cid = preg_replace("/-/","", $this->config_system['service']['cid']);

		###
		$params = "";
		$anp = "";
		$first_yn = "Y";
		$cnt = 0;
		$subCnt = 0;
		$groupName = "SIMPLE MAIL";

		unset($mailArr);
		if	($_GET['send_to'])	$mailArr = explode(",", $_GET["send_to"]);
		else					$mailArr = explode(",", $_GET["no"]);
		unset($mailArr[0]);

		###
		if(isset($_GET['add_num_chk'])!='Y'){
			$key = get_shop_key();
			switch($_GET['member']){
				case "all":
					$query = $this->db->query("select AES_DECRYPT(UNHEX(email), '{$key}') as email, user_name, userid from fm_member where status != 'withdrawal' and email<>'' ");// and mailing = 'y'
					$data = $query->result_array();
					if(count($data)>0){
						foreach($data as $k){
							if($k['email']) {
								$arr['email'] = $k['email'];
								$arr['user_name'] = $k['user_name'];
								$arr['userid'] = $k['userid'];
								if($k['business_seq']){
									$arr['user_name'] = $k['bname'];
								}
								array_push($mailArr,$arr);
							}
						}
					}
					break;
				case "search":
					foreach($_GET as $keyval => $value){
							$sc[$keyval] = $value;
					}
					if($sc["keyword"] == "이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소, 닉네임"){
						$sc["keyword"] = "";
					}
					//$sc['mailing'] = 'y';
					$sc['nolimit']	= 'y';
					//$this->load->model('membermodel');
					$data = $this->membermodel->admin_member_list($sc);
					if(count($data['result'])>0){
						foreach($data['result'] as $k){
							if($k['email']) {
								$arr['email'] = $k['email'];
								$arr['user_name'] = $k['user_name'];
								$arr['userid'] = $k['userid'];
								if($k['business_seq']){
									$arr['user_name'] = $k['bname'];
								}
								array_push($mailArr,$arr);
							}
						}
					}
					break;
				case "select":
					foreach($_GET as $keyval => $value){
						if($keyval == 'member_chk'){
							foreach($value as $keyval2 => $member_seq){
								$sql = "select
								AES_DECRYPT(UNHEX(A.email), '{$key}') as email, A.user_name, A.userid, B.business_seq, B.bname from
								fm_member A
								LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
								where A.member_seq = '".$member_seq."' and A.email<>'' ";
								$query = $this->db->query($sql);
								$data = $query->result_array();
								if($data[0]['email']){
									$arr['email'] = $data[0]['email'];
									$arr['user_name'] = $data[0]['user_name'];
									$arr['userid'] = $data[0]['userid'];
									if($data[0]['business_seq']){
										$arr['user_name'] = $data[0]['bname'];
									}
									array_push($mailArr,$arr);
								}
							}
						}
					}
					break;
				case "excel":
					break;
			}
		}

		###
		if($mailArr){
			foreach($mailArr as $v){
				if(is_array($v)){
					$params .= $anp."mid[".$cnt."]=".addslashes($v['userid'])."&email[".$cnt."]=".addslashes($v['email'])."&name[".$cnt."]=".addslashes(iconv("utf-8", "euc-kr", $v['user_name']));
				}else{
					$params .= $anp."mid[".$cnt."]=".addslashes($v)."&email[".$cnt."]=".addslashes($v)."&name[".$cnt."]=".addslashes($v);
				}

				if($subCnt % 1000 != 0 || $subCnt == 0){
					$cnt++;
				} else {
					$params .= "&first_yn=".$first_yn."&domain=".addslashes($_SERVER["SERVER_NAME"])."&userid=".$cid."&groupName=".$groupName."\n";

					$this->setEmails($params);

					$params = "";
					$first_yn = "N";
					$anp = "";
					$cnt = 0;

				}
				$anp = "&";
				$subCnt++;
			}

			$params .= "&first_yn=".$first_yn."&domain=".addslashes($_SERVER["SERVER_NAME"])."&userid=".$cid."&groupName=".$groupName."\n";
			$this->setEmails($params);

			$mailEndCnt = $this->getEmailCnt($cid);

			if($cnt > 0) $result = array("result"=>TRUE, "msg"=>$mailEndCnt."개의 이메일이 세팅되어졌습니다.");
			else $result = array("result"=>FALSE, "msg"=>"이메일 셋팅에 실패하였습니다.");
		}else{
			$result = array("result"=>FALSE, "msg"=>"전송할 회원이 없습니다.");
		}
		echo "[".json_encode($result)."]";
	}

	public function setEmails($params) {

		$fp = fsockopen ("amail.firstmall.kr", 80);
		$strLen =  strlen($params);

		fputs($fp,"POST http://amail.firstmall.kr/new_amail.input.php HTTP/1.0\n");
		fputs($fp,"User-Agent: navyism\n");
		fputs($fp,"Content-type: application/x-www-form-urlencoded\n");
		fputs($fp,"Content-length: ".$strLen."\n");
		fputs($fp,"\n");
		fputs($fp,$params);
		fputs($fp,"\n");

		while(! feof ($fp))
		{
			$file = fgets ($fp, 1024);
		}
		fclose ($fp);

		return $file;
	}


	public function iconUpload(){
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['grade_icon']['tmp_name'])) {
			$config['upload_path']		= $path = ROOTPATH."/data/icon/common/";
			$file_ext = end(explode('.', $_FILES['grade_icon']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			//$config['file_name']			= substr(microtime(), 2, 6).'.'.$file_ext;
			$config['file_name']			= time().'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('grade_icon')) {
				@chmod($config['upload_path'].$config['file_name'], 0777);
				$callback = "parent.iconDisplay('{$config[file_name]}');parent.iconRegist.reset();";
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

	public function myiconUpload(){
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['my_grade_icon']['tmp_name'])) {

			$dir = ROOTPATH.'/data/icon/mypage';
			@mkdir($dir);
			@chmod($dir,0707);
			$config['upload_path']		= $path = ROOTPATH."/data/icon/mypage/";
			$file_ext = end(explode('.', $_FILES['my_grade_icon']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('my_grade_icon')) {
				@chmod($config['upload_path'].$config['file_name'], 0777);
				$callback = "parent.myiconDisplay('{$config[file_name]}');";
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


	public function getEmailCnt($cid) {
		$params = "userid=".$cid."";

		$fp = fsockopen ("121.78.114.22", 80);
		$strLen =  strlen($params);

		fputs($fp,"POST http://amail.firstmall.kr/getEmailCnt.php HTTP/1.0\n");
		fputs($fp,"User-Agent: navyism\n");
		fputs($fp,"Content-type: application/x-www-form-urlencoded\n");
		fputs($fp,"Content-length: ".$strLen."\n");
		fputs($fp,"\n");
		fputs($fp,$params);
		fputs($fp,"\n");

		while(! feof ($fp))
		{
			$file = fgets ($fp, 1024);
		}
		fclose ($fp);

		return $file;
	}

	// 실제 주문을 검색하여 회원의 주문수와 주문금액과 초대수, 추천수 업데이트합니다.
	public function all_update_orders($mseq=null)
	{
		set_time_limit(0);
		//$this->load->model('membermodel');
		$cfg_member_update_order				= config_load('member_update_order');
		$cfg_member_update_recommend		= config_load('member_update_recommend');
		$cfg_member_update_invite				= config_load('member_update_invite');

		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$sc['limitnum'] = 500;//500

		$loop = $this->membermodel->member_cnt_batch_list($sc);

		### PAGE & DATA
		$query = "select count(*) cnt from fm_member where rute != 'withdrawal' ";
		$query = $this->db->query($query);
		$data = $query->row_array();
		$all_count = $data['cnt'];

		$idx = 0;
		foreach($loop['record'] as $k => $datarow) {
			$this->membermodel->member_order($datarow['member_seq']);
			//주문건/주문금액 필드추가 및 실시간업데이트 @2013-06-19
			$this->membermodel->member_order_batch($datarow['member_seq']);

			/**
			// 예) 개월 이전주문건을 배송완료처리시 주문건/주문금액이 업데이트 수동처리시 이용
			$this->membermodel->member_order_old_gabia($datarow['member_seq'],'2013-12');
			$this->membermodel->member_order_old_gabia($datarow['member_seq'],'2014-01');
			$this->membermodel->member_order_batch($datarow['member_seq']);
			**/

			$idx++;
		}

		if( ($_GET['page']>1 && $all_count <= ( ($sc['limitnum']*$_GET['page'])) ) || ($_GET['page']==1 && $all_count == $idx ) ) {
			if( !$cfg_member_update_order['update_date'] ) {
				config_save('member_update_order',array('update_date'=>date('Y-m-d H:i:s')));
			}

			if( !$cfg_member_update_recommend['update_date'] ) {
				config_save('member_update_recommend',array('update_date'=>date('Y-m-d H:i:s')));
			}

			if( $cfg_member_update_invite['update_date'] ) {
				config_save('member_update_invite',array('update_date'=>date('Y-m-d H:i:s')));
			}
			$status = 'FINISH';
		}else{
			$status = 'NEXT';
		}

		$totalpage = @ceil($all_count/ $sc['limitnum']);

		$result = array('status' => $status,'totalcount'=>$all_count,'nextpage'=>($_GET['page']+1),'totalpage'=>$totalpage);
		echo json_encode($result);
		exit;
	}

	public function excel_down(){
		$this->load->model('excelmembermodel');
		if(is_array($_POST)){
			$this->excelmembermodel->create_excel_list($_POST);
		}else{
			$this->excelmembermodel->create_excel_list($_GET);
		}
		exit;
	}

	public function download_write(){

		if(count($_POST['downloads_item_use'])<1){
			$callback = "";
			openDialogAlert("다운로드 항목을 1개 이상 설정해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$item = implode("|",$_POST['downloads_item_use']);
		$params['item']			= $item;

		$this->db->where('gb', 'MEMBER');
		$result = $this->db->update('fm_exceldownload', $params);
		$msg	= "수정 되었습니다.";
		$func	= "parent.closeDialog('download_list_setting');";

		openDialogAlert($msg,400,140,'parent',$func);

	}

   	//회원아이콘 설정
	public function membericonsave(){  
		//error_reporting(E_ALL);//0 E_ALL
		$this->mdata = $this->membermodel->get_member_data($_GET['mseq']);//회원정보
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
				$file_name	= 'm_'.$_GET['mseq'].'.'.$file_ext;//'.str_replace(" ", "", (substr(microtime(), 2, 6))).'
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
				$this->db->where('member_seq', $_GET['mseq']);
				$result = $this->db->update('fm_member', array("user_icon"=>99, "user_icon_file"=>$file_name)); 
			}else{
				openDialogAlert($data_used['msg'],400,140,'parent','');
			}
		}

		$callback = "parent.membericonDisplay('{$user_icon}?".time()."');";
		openDialogAlert("등록하였습니다.",400,140,'parent',$callback);
	}
}

/* End of file setting_process.php */
/* Location: ./app/controllers/admin/setting_process.php */