<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);


class Joincheck_process extends admin_base {
	public function __construct() {
		parent::__construct();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('joincheck_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
	}

	public function index()
	{

		$today = date("Y-m-d");
		$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];
		$joincheck_seq = (!empty($_POST['joincheck_seq']))?$_POST['joincheck_seq']:$_GET['joincheck_seq'];


		$isplusfreenot =  $this->isplusfreenot;
		if( !$isplusfreenot || !$isplusfreenot['ispoint'] ) {//무료몰인경우초기화@2013-01-14
			$_POST['point'] = '';
			$_POST['point_select'] = '';
			$_POST['point_year'] = '';
			$_POST['point_direct'] = '';
		}

		$params = $_POST;
		$params['ch_title']			=  $_POST['ch_title'];		//출석체크 명
		$params['sdate']			=  $_POST['sdate'];			//출석 시작 기간
		$params['edate']			=  $_POST['edate'];			//출석 종료 기간
		$staste_stop				=  $_POST['mode_stop'];			//출석 종료 기간

		$params['ck_type']			=  $_POST['ck_type'];		// 출석 타입
		$params['com_list']			=  $_POST['com_list'];		// 댓글수
		$params['cl_type']			=  $_POST['cl_type'];		// 달성 타입
		if($params['cl_type']=='count'){
			$params['cl_count']			=  $_POST['cl_count_c'];		// 달성조건
		}elseif($params['cl_type']=='straight'){
			$params['cl_count']			=  $_POST['cl_count_s'];		// 달성조건
		}
		$params['emoney']			=  $_POST['emoney'];		// 혜택

		$params['check_it']			=  $_POST['check_it']; 		// 출석 체크 맨트
		$params['check_already']	=  $_POST['check_already'];	// 이미 출석 했을때
		$params['check_complete']	=  $_POST['check_complete'];// 적립금 지급 시
		$params['check_SMS']		=  $_POST['check_SMS'];		// 적립금 지급 SMS 발송

		if($params['ck_type']=='comment'){
			$params['check_skin']		=  $_POST['chc_skin'];	// 스킨
		}else{
			$params['check_skin']		=  $_POST['ch_skin'];	// 스킨
		}
		$params['stamp_skin']		=  $_POST['stamp_skin'];	// 도장 스킨

		if($staste_stop == 'stop'){
			$params['check_state'] = $staste_stop;
		}else{
			if( $params['sdate'] < $today &&  $today <$params['edate']){
				$params['check_state'] = 'ing';
			}elseif( $params['edate'] < $today){
				$params['check_state'] = 'end';
			}else{
				$params['check_state'] = 'before';
			}
		}

	###	//폼 검색
	if($mode == 'joincheck_write' || $mode == 'joincheck_modify' ) {
		if(trim($params['ch_title'])=='')
		{	$c_msg='출석 체크 명을 입력해주세요';
			$callback = "parent.document.jcRegist.ch_title.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);
			//echo "<script> alert('".$c_msg."');</script>";
			//echo "<script> parent.document.jcRegist.ch_title.focus();</script>";
			exit;}
		if(trim($params['sdate'])=='')
		{	$c_msg='시작일을 입력해주세요';
			$callback = "parent.document.jcRegist.sdate.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);

			exit;}
		if(trim($params['edate'])=='')
		{	$c_msg='종료일을 입력해주세요';
			$callback = "parent.document.jcRegist.edate.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);

			exit;}
		if(trim($params['cl_count'])=='' || $params['cl_count'] <='0')
		{	$c_msg='달성조건을 입력해주세요';

			if($params['cl_type']=='count'){
			$callback = "parent.document.jcRegist.cl_count_c.focus();";
			}elseif ($params['cl_type']=='straight'){
			$callback = "parent.document.jcRegist.cl_count_s.focus();";
			}
			openDialogAlert($c_msg,400,140,'parent',$callback);

			exit;}
			/**
		if((trim($params['emoney'])=='' || $params['emoney']<='0') && (trim($params['point'])=='' || $params['point']<='0'))
		{	$c_msg='지급될 적립금을 입력해주세요';
			$callback = "parent.document.jcRegist.emoney.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);
			exit;}
			**/
		if(!$params['emoney']) $params['emoney'] = 0;
		if(!$params['point']) $params['point'] = 0;
		//적립금 숫자체크 
		if (!preg_match("/^[0-9]/",$params['emoney'])) {
			$c_msg = "적립금은 숫자만 입력 가능합니다.";
			$callback = "parent.document.jcRegist.emoney.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);
			exit;
		}
		//포인트 숫자체크 
		if (!preg_match("/^[0-9]/",$params['point'])) {
			$c_msg = "포인트는 숫자만 입력 가능합니다.";
			$callback = "parent.document.jcRegist.point.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);
			exit;
		}
		if(trim($params['check_it'])=='')
		{	$c_msg='출석 체크 시 멘트를 입력해주세요';
			$callback = "parent.document.jcRegist.check_it.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);
			exit;}
		if(trim($params['check_already'])=='')
		{	$c_msg='이미 출석 체크 했을 때 멘트를 입력해주세요';
			$callback = "parent.document.jcRegist.check_already.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);
			exit;}
		if(trim($params['check_complete'])=='')
		{	$c_msg='적립금 지급시  멘트 입력해주세요';
			$callback = "parent.document.jcRegist.check_complete.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);

			exit;}
		if(trim($params['check_SMS'])=='')
		{	$c_msg='SMS로 발송될 멘트를 입력해주세요';
			$callback = "parent.document.jcRegist.check_SMS.focus();";
			openDialogAlert($c_msg,400,140,'parent',$callback);

			exit;}
		if($params['sdate'] > $params['edate'] )
		{	$c_msg='종료일이 시작일보다 빠릅니다.<br/> 종료일을 변경해주세요';
			$callback = "parent.document.jcRegist.edate.focus();";
			openDialogAlert($c_msg,400,160,'parent',$callback);

			exit;}

		$ch_settime = intval((strtotime($params['edate']) - strtotime($params['sdate']))/86400);

		if($ch_settime + 1<  $params['cl_count'] )
		{	$c_msg='기간보다 조건이 더 큽니다.<br/> 조건을 변경해주세요';
			if($params['cl_type']=='count'){
			$callback = "parent.document.jcRegist.cl_count_c.focus();";
			}elseif ($params['cl_type']=='straight'){
			$callback = "parent.document.jcRegist.cl_count_s.focus();";
			}
			openDialogAlert($c_msg,400,160,'parent',$callback);

			exit;}

		### // 중복체크
		if($params['check_state'] == 'before'){
			$sql = "SELECT count(*) as '0'
			FROM fm_joincheck where
			  ((start_date between '".$params['sdate']."' and '".$params['edate']."'
			 or end_date between '".$params['sdate']."' and '".$params['edate']."')
			 or ( '".$params['sdate']."' between start_date and end_date
			 or '".$params['edate']."' between start_date and end_date)
			 )and joincheck_seq != '".$joincheck_seq."' ";

			$query = $this->db->query($sql);
			$result= $query->row_array();

			if($result[0] > '0' ){
				$c_msg='이벤트 기간은 중복될 수 없습니다.<br/> 시작일과 종료일을 변경해주세요';
				$callback = "parent.document.jcRegist.sdate.focus();";
				openDialogAlert($c_msg,400,160,'parent',$callback);
				exit;
			}
		}

		###
	}
	###



		if($mode == 'joincheck_write') {

			$data = array(
				'title' 		=> $params['ch_title'],
				'start_date'	=> $params['sdate'],
				'end_date'		=> $params['edate'],
				'regist_date'	=> date('Y-m-d H:i:s'),
				'check_state'	=> $params['check_state'],
				'check_type'	=> $params['ck_type'],
				'comment_list'	=> $params['com_list'],
				'check_clear_type'	=> $params['cl_type'],
				'check_clear_count'	=> $params['cl_count'],
				'emoney'		=> $params['emoney'],
				'reserve_select'	=> $params['reserve_select'],
				'reserve_year'		=> $params['reserve_year'],
				'reserve_direct'	=> $params['reserve_direct'],
				'point'				=> $params['point'],
				'point_select'		=> $params['point_select'],
				'point_year'		=> $params['point_year'],
				'point_direct'		=> $params['point_direct'],
				'skin'			=> $params['check_skin'],
				'stamp_skin'	=> $params['stamp_skin'],
				'check_it'		=> $params['check_it'],
				'check_already'	=> $params['check_already'],
				'check_complete'=> $params['check_complete'],
				'check_SMS'		=> $params['check_SMS'],
				'del_state'		=> 'N'
            );


			$result = $this->db->insert('fm_joincheck', $data);
			if($result){
				$callback = "parent.document.location.href='/admin/joincheck/catalog';";
				openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location.reloade()";
				openDialogAlert("저장이 실패하였습니다.",400,140,'parent',$callback);
			}

		}elseif ($mode == 'joincheck_modify'){

			$params['joincheck_seq']	=  $_POST['joincheck_seq'];	//이벤트번호

			if ($staste_stop == 'stop' || $staste_stop == 'ing'){
				$data = array(
				'check_state'	=> $params['check_state'],
				);
			}else{
			$data = array(
				'title' 		=> $params['ch_title'],
				'start_date'	=> $params['sdate'],
				'end_date'		=> $params['edate'],
				'update_date'	=> date('Y-m-d H:i:s'),
				'check_state'	=> $params['check_state'],
				'check_type'	=> $params['ck_type'],
				'comment_list'	=> $params['com_list'],
				'check_clear_type'	=> $params['cl_type'],
				'check_clear_count'	=> $params['cl_count'],
				'emoney'		=> $params['emoney'],
				'reserve_select'	=> $params['reserve_select'],
				'reserve_year'		=> $params['reserve_year'],
				'reserve_direct'	=> $params['reserve_direct'],
				'point'				=> $params['point'],
				'point_select'		=> $params['point_select'],
				'point_year'		=> $params['point_year'],
				'point_direct'		=> $params['point_direct'],
				'skin'			=> $params['check_skin'],
				'stamp_skin'	=> $params['stamp_skin'],
				'check_it'		=> $params['check_it'],
				'check_already'	=> $params['check_already'],
				'check_complete'=> $params['check_complete'],
				'check_SMS'		=> $params['check_SMS'],
				'del_state'		=> 'N'
            );
			}

			$this->db->where('joincheck_seq',$params['joincheck_seq']);
			$result = $this->db->update('fm_joincheck', $data);
			if($result){
				$callback = "parent.document.location.href='/admin/joincheck/catalog';";
				openDialogAlert("수정을 완료하였습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location.reloade()";
				openDialogAlert("수정이 실패하였습니다.",400,140,'parent',$callback);
			}


		}elseif ($mode == 'joincheck_delete'){

			$data = array(	'del_state'		=> 'Y');
			$this->db->where('joincheck_seq',$joincheck_seq);
			$this->db->delete('fm_joincheck');
			$callback = "parent.document.location.href='/admin/joincheck/catalog';";

			openDialogAlert("삭제 되었습니다.",400,140,'parent',$callback);

		}elseif ($mode == 'joincheck_copy'){

			$sql = "SELECT *,
			if(check_state = 'stop','중지',if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'진행완료','진행 전')))
			 as status
			FROM fm_joincheck where joincheck_seq='".$joincheck_seq."'";
			$query = $this->db->query($sql);
			$result= $query->row_array();

			$copy_title="[복사]".$result['title'];

			$data = array(
				'title' 		=> $copy_title,
				'regist_date'	=> date('Y-m-d H:i:s'),
				'check_state'	=> 'before',
				'check_type'	=> $result['check_type'],
				'comment_list'	=> $result['comment_list'],
				'check_clear_type'	=> $result['check_clear_type'],
				'check_clear_count'	=> $result['check_clear_count'],
				'emoney'		=> $result['emoney'],
				'skin'			=> $result['skin'],
				'stamp_skin'	=> $result['stamp_skin'],
				'check_it'		=> $result['check_it'],
				'check_already'	=> $result['check_already'],
				'check_complete'=> $result['check_complete'],
				'check_SMS'		=> $result['check_SMS'],
				'del_state'		=> 'N'
			);

			$result = $this->db->insert('fm_joincheck', $data);
			$callback = "parent.document.location.href='/admin/joincheck/catalog';";
			openDialogAlert("복사 되었습니다.",400,140,'parent',$callback);


		}

	}




	//적립금 등록
	public function emoney_pay()
	{
	$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];

 		/* 게시글 파일다운 */
		if($mode == 'emoney_pay') {
			if(empty($_POST['jcresult_seq'])) {
				$callback = "parent.emoneyclose();";
				openDialogAlert("회원을 선택해 주세요.",400,140,'parent',$callback);
				exit;
			}
		$this->load->model('membermodel');
		$sql = "select * from fm_joincheck_result where jcresult_seq in (".$_POST['jcresult_seq'].")";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $mc ){

			//회원정보체크
			$minfo = $this->membermodel->get_member_data($mc['member_seq']);
			if(!empty($minfo)) { //회원정보체크

			//emoney
			$upsql = "update fm_member set emoney = emoney+".$_POST['emoney_pay_emoney']." where member_seq = ".$minfo['member_seq'];
			$this->db->query($upsql);

			$uprsql = "update fm_joincheck_result set emoney_pay = 'Y', emoney_pay_date='".date('Y-m-d H:i:s')."', emoney = '".$_POST['emoney_pay_emoney']."' where member_seq = ".$minfo['member_seq']." and joincheck_seq = ".$_POST['joincheck_seq'] ;
			$this->db->query($uprsql);

			//emoney history
			$this->load->model('Emoneymodel');
			$params['member_seq']	= $minfo['member_seq'];
			$params['type']			= 'joincheck';
			$params['emoney']		= $_POST['emoney_pay_emoney'];
			$params['memo']			= '출석체크 이벤트';
			$params['regist_date']	= date("Y-m-d H:i:s");
			$this->Emoneymodel->emoney_write($params);

			$config_basic = ($this->config_basic)?$this->config_basic:config_load('basic');

			$sms_msg = str_replace("{shopName}",$config_basic['shopName'],$_POST['emoney_pay_sms']);

				###SMS 발송
				if (!empty($sms_msg) && !empty($minfo['cellphone'])) {

					require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/SMS_send.class.php";
					$sms_send	= new SMS_SEND();
					$to_sms[0]['phone']	= ereg_replace("[^0-9]", "", $minfo['cellphone']);
					$from_sms	= ereg_replace("[^0-9]", "", $config_basic['companyPhone']);

					$sms_send->to		= $to_sms;
					$sms_send->from		= $from_sms;

					$result = $sms_send->send($sms_msg);

					}
				###
				}

			}
			$callback = "parent.emoneyclose();";
			openDialogAlert("적립금을 지급하였습니다.",400,140,'parent',$callback);

			exit;
		}

	}


}
?>