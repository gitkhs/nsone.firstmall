<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class Board_comment_process extends admin_base {

	public function __construct() {
		parent::__construct();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('board_act');
		if(!$auth){
			if($_POST['mode']) { 
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>$this->auth_msg);
				echo json_encode($return); 
			}else{
				$this->admin_menu();
				$this->tempate_modules();
				$callback = "history.go(-1);";
				$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
				$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
				$this->template->print_("denined");
			}
		}
		/* 관리자 권한 체크 : 끝 */

		$boardid = (!empty($_POST['board_id'])) ? $_POST['board_id']:$_GET['board_id'];
		define('BOARDID',$boardid);

		$this->load->library('validation');
		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$this->load->helper('captcha');
		$this->load->helper('board');
		$this->load->model('Boardscorelog');

		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}
		$this->load->model('Boardindex');
		$this->load->model('Boardcomment');
		if(!empty($_POST['seq'])) {
			$this->boardurl->view			= $this->Boardmanager->realboardviewurl.BOARDID.'&seq='.$_POST['seq'];	//게시물보기
		}else{
			$this->boardurl->view			= $this->Boardmanager->realboardviewurl.BOARDID.'&seq=';	//게시물보기
		}
	}

	/* 기본 */
	public function index()
	{

		$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];

		$sc['whereis']	= ' and id= "'.BOARDID.'" ';
		$sc['select']		= ' * ';
		$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
		if (!isset($manager['id'])) {
			openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','');
			exit;
		}

		/**
		 * icon setting
		**/
		$this->icon_new_img			= ($manager['icon_new_img'] && @is_file($this->Boardmodel->upload_path.$manager['icon_new_img']) ) ? $this->Boardmanager->board_data_src.BOARDID.'/'.$manager['icon_new_img'].'?'.time():$this->Boardmanager->new_icon_src;//newicon
		$this->icon_hot_img			= ($manager['icon_hot_img'] && @is_file($this->Boardmodel->upload_path.$manager['icon_hot_img']) ) ? $this->Boardmanager->board_data_src.BOARDID.'/'.$manager['icon_hot_img'].'?'.time():$this->Boardmanager->hot_icon_src;//hoticon


		$this->re_img						= $this->Boardmanager->re_icon_src;//답변글icon
		$this->blank_img					= $this->Boardmanager->blank_icon_src;//blank
		$this->cmt_reply_del_img	= $this->Boardmanager->cmt_reply_icon_src;//cmtreplydel

		$boardsc['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
		$boardsc['select']		= ' * ';
		$boarddata = $this->Boardmodel->get_data($boardsc);//원본게시글
		if(empty($boarddata)) {
			$callback = "parent.document.location.href='".$this->Boardmanager->realboardurl.BOARDID."';";
			openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
			exit;
		}

		/* 댓글수정시 정보보여주기 */
		if( $mode == 'board_comment_item' ) {

			$parentsc['whereis']	= ' and seq= "'.$_POST['cmtseq'].'" ';
			$parentsc['select']		= ' name, seq, subject, content, mseq, hidden, parent   ';
			$parentdata = $this->Boardcomment->get_data($parentsc);//댓글정보
			if(empty($parentdata)) {
				$return = array('result'=>false, 'msg'=>"존재하지 않는 댓글입니다.");
				echo json_encode($return);
				exit;
			}

			$result = array('result'=>true,'content'=>$parentdata['content'], 'name'=>$parentdata['name'], 'subject'=>$parentdata['subject'], 'seq'=>$parentdata['seq'], 'mseq'=>$parentdata['mseq'], 'hidden'=>$parentdata['hidden']);
			echo json_encode($result);
			exit;
		}

		/* 댓글 등록 */
		elseif($mode == 'board_comment_write') {

			if( $_POST['content'] == '입력해 주세요.' ) {
				$_POST['content'] = '';
			}

			//$this->validation->set_rules('name', '이름','trim|required|xss_clean');
			$this->validation->set_rules('content', '내용','trim|required|xss_clean');
			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}


			$params['boardid']		=  BOARDID;
			$params['parent']		=  $_POST['seq'];
			$params['subject']		=  (isset($_POST['subject'])) ? $_POST['subject']:'';
			$params['name']			=  (isset($_POST['name'])) ? $_POST['name']:'';
			$params['content']		=  $_POST['content'];

 			$params['r_date']		= date("Y-m-d H:i:s");
			$params['m_date']		= date("Y-m-d H:i:s");
			$params["ip"]				= $this->input->ip_address();
			if( defined('__SELLERADMIN__') === true ) {
				$params['mseq']			= '-'.$this->providerInfo['provider_seq'];//입점사
				$params['mid']				= $this->providerInfo['provider_id'];
			}else{
			$params['mseq']			= '-1';
			$params['mid']				= $this->managerInfo['manager_id'];
			}

			//회원정보
			if( isset($this->userInfo['member_seq']) ) {
				$params['mseq']		= $this->userInfo['member_seq'];
				$params['mid']			= $this->userInfo['userid'];
				$params['name']		= (isset($params['name'])) ? $params['name']:$this->userInfo['user_name'];
			}

			$params['cmtparent']	= 0;
			$params['depth']			= 0;

			if( $this->Boardmanager->cmthidden == "Y" ) {//비밀댓글
				$params['hidden']		= if_empty($_POST, 'hidden', '0');
			}else{
				$params['hidden']		= 0;
			}

			$result = $this->Boardcomment->data_write($params);

			if($result) {
				//댓글증가
				$upboarddata['comment']		= $boarddata['comment']+1;
				$upboarddata['cmt_date']		= date("Y-m-d H:i:s");
				$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글증가

				if(BOARDID == 'goods_review' && $_POST['board_sms_hand'] && !empty($_POST['board_sms']) ) {//SMS발송 댓글 등록시2013-04-24
					$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
					$parentsql['select']		= '  seq, mseq ';
					$parentdata = $this->Boardmodel->get_data($parentsql);
					$smsparams['msg']		= $_POST["sms_content1"];
					$this->load->model('membermodel');
					$smsparams = $this->membermodel->get_member_data($parentdata['mseq']);
					$smsparams['userid'] = ($smsparams['userid'])?$smsparams['userid']:$boarddata['name'];//비회원은 작성자명
					$smsparams['user_name']	 = ($smsparams['userid'])?$smsparams['user_name']:$boarddata['name'];//작성자명

					$commonSmsData[BOARDID."_reply"]['phone'][] = $_POST['board_sms_hand'];
					$commonSmsData[BOARDID."_reply"]['mid'][] = $smsparams['userid'];
					$commonSmsData[BOARDID."_reply"]['params'][] = $smsparams;

					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}
					
					//sendSMS($_POST['board_sms_hand'], BOARDID."_reply", $smsparams['userid'] , $smsparams);
				}

				$callback = (!empty($_POST['returnurl']))?"<script type='text/javascript'>parent.boardaddFormDialog('".$_POST['returnurl']."#cwriteform', '90%', '800', '게시글 보기','false');</script>":"<script type='text/javascript'>parent.boardaddFormDialog('".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$boarddata['seq']."#cwriteform', '90%', '800', '게시글 보기','false');</script>";//alert('댓글을 등록하였습니다.');
				echo $callback;
			}else{
				echo "<script type='text/javascript'>alert('댓글 등록에 실패되었습니다.');</script>";
			}
			exit;
		}

		/* 댓글의 답글 등록 */
		elseif($mode == 'board_comment_reply') {

			if( $_POST['content'] == '입력해 주세요.' ) {
				$_POST['content'] = '';
			}

			//회원정보
			if( defined('__ISUSER__') === true ) {
				$_POST['name']		= $this->userInfo['user_name'];
			}

			//$this->validation->set_rules('name', '이름','trim|required|xss_clean');
			$this->validation->set_rules('content', '내용','trim|required|xss_clean');
			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
				$return = array('result'=>false, 'msg'=>$err['value']);
				echo json_encode($return);
				exit;
			}

			$params['boardid']		=  BOARDID;
			$params['parent']		=  $_POST['seq'];
			$params['subject']		=  (isset($_POST['subject'])) ? $_POST['subject']:'';
			$params['name']			=  (isset($_POST['name'])) ? $_POST['name']:'';
			$params['content']		=  $_POST['content'];

 			$params['r_date']		= date("Y-m-d H:i:s");
			$params['m_date']		= date("Y-m-d H:i:s");
			$params["ip"]				= $this->input->ip_address();
			if( defined('__SELLERADMIN__') === true ) {
				$params['mseq']			= '-'.$this->providerInfo['provider_seq'];
				$params['mid']				= $this->providerInfo['provider_id'];
			}else{
			$params['mseq']			= '-1';
			$params['mid']				= $this->managerInfo['manager_id'];
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['cmtseq'].'" ';
			$parentsql['select']		= ' seq, depth ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(empty($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"존재하지 않는 댓글입니다.");
				echo json_encode($return);
				exit;
			}

			$params['cmtparent']	= $_POST['cmtseq'];
			$params['depth']			= $parentdata['depth']+1;

			if( $this->Boardmanager->cmthidden == "Y" ) {//비밀댓글
				$params['hidden']		= if_empty($_POST, 'hidden', '0');
			}else{
				$params['hidden']		= 0;
			}

			$result = $this->Boardcomment->data_write($params);

			if($result) {

				//댓글증가
				$upboarddata['comment']		= $boarddata['comment']+1;
				$upboarddata['cmt_date']		= date("Y-m-d H:i:s");
				$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글증가

				$return = array('result'=>true,'msg'=>"답글을 등록하였습니다.");
				echo json_encode($return);
			}else{
				$return = array('result'=>false, 'msg'=>"답글등록에 실패되었습니다.");
				echo json_encode($return);
			}
			exit;
		}

		/* 댓글 수정 */
		elseif($mode == 'board_comment_modify') {
			if( $_POST['content'] == '입력해 주세요.' ) {
				$_POST['content'] = '';
			}

			//$this->validation->set_rules('name', '이름','trim|required|xss_clean');
			$this->validation->set_rules('content', '내용','trim|required|xss_clean');
			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['cmtseq'].'" ';
			$parentsql['select']		= ' seq, pw, mseq ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(empty($parentdata['seq'])) {
				alert("존재하지 않는 댓글입니다.");
				exit;
			}

			$params['subject']		=  $_POST['subject'];
			$params['name']			=  $_POST['name'];
			$params['content']		=  $_POST['content'];
			$params['m_date']		= date("Y-m-d H:i:s");
			$params["ip"]				= $this->input->ip_address();

			//회원정보
			if( defined('__ISUSER__') === true ) {

				$this->load->model('membermodel');
				$this->minfo = $this->membermodel->get_member_data($this->userInfo['member_seq']);
				$params['mseq']		= $this->userInfo['member_seq'];
				$params['mid']			= $this->userInfo['userid'];
				$params['pw']			= $this->minfo['password'];
			}

			if( $this->Boardmanager->cmthidden == "Y" ) {//비밀댓글
				$params['hidden']		= if_empty($_POST, 'hidden', '0');
			}else{
				$params['hidden']		= 0;
			}

			$result = $this->Boardcomment->data_modify($params);
			if($result) {
				//댓글등록일업데이트
				$upboarddata['cmt_date']		= date("Y-m-d H:i:s");
				$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//댓글등록일업데이트

				$callback = (!empty($_POST['returnurl']))?"<script type='text/javascript'>parent.boardaddFormDialog('".$_POST['returnurl']."#cwriteform', '90%', '800', '게시글 보기','false');alert('댓글을 수정하였습니다.');</script>":"<script type='text/javascript'>parent.boardaddFormDialog('".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$boarddata['seq']."#cwriteform', '90%', '800', '게시글 보기','false');alert('댓글을 수정하였습니다.');</script>";
				echo $callback;
			}else{
				echo "<script type='text/javascript'>alert('댓글 수정이 실패 되었습니다.');</script>";
			}
			exit;
		}

		/* 비회원 > 댓글 수정 */
		elseif($mode == 'board_comment_modify_pwcheck') {


			if( $_POST['content'] == '입력해 주세요.' ) {
				$_POST['content'] = '';
			}

			if(empty($_POST['cmtseq'])) {
				alert("잘못된 접근입니다.");
				exit;
			}


			$parentsql['whereis']	= ' and seq= "'.$_POST['cmtseq'].'" ';
			$parentsql['select']		= ' seq, pw, mseq ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(empty($parentdata['seq'])) {
				alert("존재하지 않는 댓글입니다.");
				exit;
			}
				$params['subject']		=  $_POST['subject'];
				$params['name']			=  $_POST['name'];
				$params['content']		=  $_POST['content'];
				$params['m_date']		= date("Y-m-d H:i:s");
				$params["ip"]				= $this->input->ip_address();

				//회원정보
				if( defined('__ISUSER__') === true ) {
					$params['mseq']		= $this->userInfo['member_seq'];
					$params['mid']			= $this->userInfo['userid'];
				}

				if( $this->Boardmanager->cmthidden == "Y" ) {//비밀댓글
					$params['hidden']		= if_empty($_POST, 'hidden', '0');
				}else{
					$params['hidden']		= 0;
				}

				$result = $this->Boardcomment->data_modify($params);
				if($result) {

					//댓글등록일업데이트
					$upboarddata['cmt_date']		= date("Y-m-d H:i:s");
					$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//댓글등록일업데이트

					$callback = (!empty($_POST['returnurl'])) ?"parent.document.location.href='".$_POST['returnurl']."';":"parent.document.location.href='".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$_POST['seq']."';";
				openDialogAlert("댓글을 수정하였습니다.",400,140,'parent',$callback);
				}else{
					alert("댓글 수정이 실패 되었습니다.");
				}
			exit;
		}

		/* 비회원 > 댓글 > 답글 수정 */
		elseif($mode == 'board_comment_reply_modify_pwcheck') {


			if( $_POST['content'] == '입력해 주세요.' ) {
				$_POST['content'] = '';
			}

			if(empty($_POST['cmtseq'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}


			$parentsql['whereis']	= ' and seq= "'.$_POST['cmtseq'].'" ';
			$parentsql['select']		= ' seq, pw, mseq ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(empty($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"존재하지 않는 댓글입니다.");
				echo json_encode($return);
				exit;
			}

			$params['subject']		=  $_POST['subject'];
			$params['name']			=  $_POST['name'];
			$params['content']		=  $_POST['content'];
			$params['m_date']		= date("Y-m-d H:i:s");
			$params["ip"]				= $this->input->ip_address();

			//회원정보
			if( defined('__ISUSER__') === true ) {
				$params['mseq']		= $this->userInfo['member_seq'];
				$params['mid']			= $this->userInfo['userid'];
			}

			if( $this->Boardmanager->cmthidden == "Y" ) {//비밀댓글
				$params['hidden']		= if_empty($_POST, 'hidden', '0');
			}else{
				$params['hidden']		= 0;
			}

			$result = $this->Boardcomment->data_modify($params);
			if($result) {

				//댓글등록일업데이트
				$upboarddata['cmt_date']		= date("Y-m-d H:i:s");
				$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//댓글등록일업데이트

				$return = array('result'=>true, 'msg'=>"댓글을 수정하였습니다.");
				echo json_encode($return);
			}else{
				$return = array('result'=>true, 'msg'=>"댓글 수정이 실패 되었습니다.");
				echo json_encode($return);
			}
			exit;
		}

		/* 댓글 삭제 */
		elseif($mode == 'board_comment_delete') {

			$parentsql['whereis']	= ' and seq= "'.$_POST['delcmtseq'].'" ';
			$parentsql['select']		= ' seq, depth ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(empty($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$replyor = 0;
			if($parentdata['depth'] == 0 ) {
				$replysc['whereis']	= ' and cmtparent = '.$parentdata['seq'].' and depth = 1 ';
				$replysc['select']		= " seq ";
				$replyor = $this->Boardcomment->get_data_numrow($replysc);//
			}

			if($replyor==0 || $parentdata['depth'] == 1 ) {//답글이 없거나 답글인 경우 real 삭제
				$result = $this->Boardcomment->data_delete($_POST['delcmtseq']);
			}else{
				$params['display']			= '1';//삭제글여부1
				$params['subject']			= '';//초기화함
				$params['content']			= '';//초기화함
				$params['r_date']			= date("Y-m-d H:i:s");
				$result = $this->Boardcomment->data_delete_modify($params,$_POST['delcmtseq']);
			}

			if($result) {

				//댓글평가제거
				$this->Boardscorelog->data_cparent_delete($boarddata['seq'],$_POST['delcmtseq']);

				if($replyor==0) {//답글이 없는 경우 real 삭제
					//댓글감소
					$upboarddata['comment']		= $boarddata['comment']-1;
					$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글감소
				}

				$return = array('result'=>true, 'msg'=>"정상적으로 삭제되었습니다.");
				echo json_encode($return);
				exit;
			}else{
				$return = array('result'=>false, 'msg'=>"댓글 삭제가 실패 되었습니다.");
				echo json_encode($return);
			}
			exit;
		}


		/* 댓글 삭제 */
		elseif($mode == 'board_comment_delete_reply') {

			$parentsql['whereis']	= ' and seq= "'.$_POST['delcmtseq'].'" ';
			$parentsql['select']		= ' seq, depth ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(empty($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$result = $this->Boardcomment->data_delete($_POST['delcmtseq']);//답글은 무조건 삭제

			if($result) {
				//댓글평가제거
				$this->Boardscorelog->data_cparent_delete($boarddata['seq'],$_POST['delcmtseq']);

				//댓글감소
				$upboarddata['comment']		= $boarddata['comment']-1;
				$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글감소
				$return = array('result'=>true, 'msg'=>"정상적으로 삭제되었습니다.");
				echo json_encode($return);
				exit;
			}else{
				$return = array('result'=>false, 'msg'=>"답변 삭제가 실패 되었습니다.");
				echo json_encode($return);
			}
			exit;
		}

		/* 비밀번호 확인 후 > 댓글 삭제 */
		elseif($mode == 'board_comment_delete_pwcheck') {

			if(empty($_POST['delcmtseq'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['delcmtseq'].'" ';
			$parentsql['select']		= ' seq, depth, pw ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(!isset($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"존재하지 않는 댓글입니다.");
				echo json_encode($return);
				exit;
			}

			$replyor = 0;
			if($parentdata['depth'] == 0 ) {
				$replysc['whereis']	= ' and cmtparent = '.$parentdata['seq'].' and depth = 1 ';
				$replysc['select']		= " seq ";
				$replyor = $this->Boardcomment->get_data_numrow($replysc);//
			}

			if($replyor==0 || $parentdata['depth'] == 1 ) {//답글이 없거나 답글인 경우 real 삭제
				$result = $this->Boardcomment->data_delete($_POST['delcmtseq']);
			}else{
				$params['display']			= '1';//삭제글여부1
				$params['subject']			= '';//초기화함
				$params['content']			= '';//초기화함
				$params['r_date']			= date("Y-m-d H:i:s");
				$result = $this->Boardcomment->data_delete_modify($params,$_POST['delcmtseq']);
			}

			if($result) {

				//댓글평가제거
				$this->Boardscorelog->data_cparent_delete($boarddata['seq'],$_POST['delcmtseq']);

				if($replyor==0) {//답글이 없는 경우 real 삭제
					//댓글감소
					$upboarddata['comment']		= $boarddata['comment']-1;
					$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글감소
				}
				$return = array('result'=>true, 'msg'=>"정상적으로 삭제되었습니다.");
				echo json_encode($return);
				exit;
			}else{
				//alert("댓글 삭제가 실패 되었습니다.");
				$return = array('result'=>false, 'msg'=>"댓글 삭제가 실패 되었습니다.");
				echo json_encode($return);
			}
			exit;
		}

		/* 댓글 일괄삭제 */
		elseif($mode == 'board_comment_alldelete') {
			$result = $this->Boardcomment->data_parent_delete($boarddata,$boarddata['seq']);//해당게시글의 모든 댓글 삭제
			if($result) {
				//댓글감소
				$upboarddata['comment']		=0;
				$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글감소

				$return = array('result'=>true, 'msg'=>'댓글을 일괄삭제하였습니다.');
				echo json_encode($return);
			}else{
				$return = array('result'=>false, 'msg'=>"댓글삭제가 실패되었습니다.");
				echo json_encode($return);
			}
			exit;
		}

		/* 댓글 선택삭제 */
		elseif($mode == 'board_comment_seldelete') {
			$delseqar = @explode(",",$_POST['delcmtseq']);//
			$num = 0;
			for($i=0;$i<sizeof($delseqar);$i++){ if(empty($delseqar[$i]))continue;
				$delseq = $delseqar[$i];

				$parentsql['whereis']	= ' and seq= "'.$delseq.'" ';
				$parentsql['select']		= ' seq, depth ';
				$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
				if($parentdata['seq']) {
					$replyor = 0;
					if($parentdata['depth'] == 0 ) {
						$replysc['whereis']	= ' and cmtparent = '.$parentdata['seq'].' and depth = 1 ';
						$replysc['select']		= " seq ";
						$replyor = $this->Boardcomment->get_data_numrow($replysc);//
					}

					if($replyor==0 || $parentdata['depth'] == 1 ) {//답글이 없거나 답글인 경우 real 삭제
						$num++;
						$result = $this->Boardcomment->data_delete($delseq);
					}else{
						$params['display']			= '1';//삭제글여부1
						$params['subject']			= '';//초기화함
						$params['content']			= '';//초기화함
						$params['r_date']			= date("Y-m-d H:i:s");
						$result = $this->Boardcomment->data_delete_modify($params,$delseq);
					}

					//댓글평가제거
					$this->Boardscorelog->data_cparent_delete($delseq,$_POST['delcmtseq']);
				}
			}

			//댓글감소
			$upboarddata['comment']		= $boarddata['comment']-$num;
			$result = $this->Boardmodel->data_item_save($upboarddata,$boarddata['seq']);//게시글의 댓글감소

			$return = array('result'=>true, 'msg'=>$num.'건의 댓글을 삭제하였습니다.');
			echo json_encode($return);
			exit;
		}

		/* 댓글 비밀글 > 비밀번호 체크 */
		elseif($mode == 'board_hidden_cmt_pwcheck') {

			if(empty($_POST['cmtseq'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['cmtseq'].'" ';
			$parentsql['select']		= ' seq, depth ';
			$parentdata = $this->Boardcomment->get_data($parentsql);//댓글정보
			if(!isset($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"존재하지 않는 댓글입니다.");
				echo json_encode($return);
				exit;
			}

			$replyor = '';
			$replysc['whereis']	= ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1) . ' ';
			//$replysc['select']		= " gid ";
			$replyor = $this->Boardindex->get_data_numrow($replysc);//답글여부...

			// 비번입력후 브라우저를 닫기전까지는 접근가능함
			$ss_cmtpwhidden_name = 'board_cmtpwhidden_'.BOARDID;
			$boardcmtpwhiddenss = $this->session->userdata($ss_cmtpwhidden_name);
			if ( ( !strstr($boardcmtpwhiddenss,'['.$_POST['seq'].']') && isset($boardcmtpwhiddenss)) || empty($boardcmtpwhiddenss)) {
				$boardcmtpwhiddenssadd = (isset($boardcmtpwhiddenss)) ? $boardcmtpwhiddenss.'['.$_POST['seq'].']':'['.$_POST['seq'].']';
				$this->session->set_userdata($ss_cmtpwhidden_name, $boardcmtpwhiddenssadd );
				$return = array('result'=>true);
				echo json_encode($return);
			}
			exit;
		}
	}
 	
	//댓글평가하기
	public function board_score_save()
	{ 
		$sc['whereis']	= ' and id= "'.BOARDID.'" ';
		$sc['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sc);//게시판정보  
		if (!isset($this->manager['id'])) { 
			$result = array('result'=>false, 'msg'=>"존재하지 않는 게시판입니다."); 
			echo json_encode($result);
			exit;
		}

		if( $this->manager['auth_cmt_recommend_use'] == 'Y' ) { 

			$sc['whereis']	= ' and seq= "'.$_POST['cparent'].'" ';
			$sc['select']	= ' seq, pw, mseq, parent ';
			$cdata = $this->Boardcomment->get_data($sc);//댓글정보
			if(empty($cdata['seq'])) {
				$result = array('result'=>false, 'msg'=>"존재하지 않는 댓글입니다."); 
				echo json_encode($result);
				exit;
			} 

			$parentsql['whereis']	= ' and boardid= "'.$_POST['board_id'].'" '; 
			$parentsql['whereis']	.= ' and type= "comment" ';
			$parentsql['whereis']	.= ' and parent= "'.$_POST['parent'].'" ';//게시글 
			$parentsql['whereis']	.= ' and cparent= "'.$_POST['cparent'].'" ';//댓글 
			$parentsql['whereis']	.= ' and mseq= "-'.$this->managerInfo['manager_seq'].'" ';
			$getscore = $this->Boardscorelog->get_data($parentsql);

			if(!$getscore) {
				//recommend/none_rec/recommend1/recommend2/recommend3/recommend4/recommend5
				$scoreid=  $_POST['scoreid'];
				$result = $this->Boardcomment->board_score_update($cdata['seq'], $scoreid,' + '); 
				
				 if( $result ) {
					$params['type']				= 'comment';
					$params['boardid']			= $_POST['board_id'];
					$params['scoreid']			= $scoreid;
					$params['parent']			= $_POST['parent'];
					$params['cparent']			= $_POST['cparent'];
					$params['mseq']			= '-'.$this->managerInfo['manager_seq'];//$this->userInfo['member_seq']; 
					$params['regist_date']	= date("Y-m-d H:i:s");
					$this->Boardscorelog->data_write($params); 

					$sc['whereis']	= ' and seq= "'.$_POST['cparent'].'" ';
					$sc['select']	= ' seq, pw, mseq, parent ,'.$scoreid;
					$getscoredata = $this->Boardcomment->get_data($sc);//댓글정보

					 $msg = "회원님의 평가가 반영되었습니다.";
				 }else{
					 $msg = "회원님의 평가가 실패되었습니다.";
				 }
			}else{
				 $msg = "이미 평가하신 댓글입니다.";
			}
		}else{
			 $msg = "잘못된 접근입니다.";
		} 

		if( $result ) {
			$result = array('result'=>true, 'msg'=>$msg, 'scoreid'=>$getscoredata[$scoreid]);
		}else{
			$result = array('result'=>false, 'msg'=>$msg);
		}

		echo json_encode($result);
		exit;
	}
}

/* End of file board_comment_process.php */
/* Location: ./app/controllers/admin/board_comment_process.php */