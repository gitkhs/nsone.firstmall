<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 2.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class Board_goods_process extends admin_base {

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
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$boardid = (!empty($_POST['board_id'])) ? $_POST['board_id']:$_GET['board_id'];
		define('BOARDID',$boardid);

		$this->load->library('validation');
		$this->load->helper('download');
		$this->load->helper('board');//

		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');

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
 		/* 상품후기 적립금 지급 */
		if($mode == 'goods_review_emoney_save') {
			if(empty($_POST['mseq'])) {
				openDialogAlert("잘못된 접근입니다.",400,140,'parent','');
				exit;
			}

			if( BOARDID != 'goods_review' ) {
				openDialogAlert("잘못된 접근입니다.",400,140,'parent','');
				exit;
			}

			//회원정보체크
			$this->load->model('membermodel');
			$minfo = $this->membermodel->get_member_data($_POST['mseq']);
			if(!empty($minfo)) { //회원정보체크

				$emoney['gb']									= 'plus';
				$emoney['type']								= 'goods_review';
				$emoney['goods_review']				= $_POST['seq'];
				$emoney['goods_review_parent']	= $_POST['seq'];
				$emoney['emoney']							= $_POST['goods_review_emoney'];
				$emoney['memo']							= $_POST['goods_review_memo'];

				if( defined('__SELLERADMIN__') === true ) {
					$emoney['manager_seq']	= $this->providerInfo['provider_seq'];
				}else{
					$emoney['manager_seq']	= $this->managerInfo['manager_seq'];
				}
				if($_POST['goods_review_reserve_select']=='year'){
					$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['goods_review_reserve_year']));
				}else if($_POST['goods_review_reserve_select']=='direct'){
					$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['goods_review_reserve_direct'], date("d"), date("Y")));
				}
				$emoney['limit_date']	= $limit_date;
				$this->membermodel->emoney_insert($emoney, $minfo['member_seq']);

				$boardupparams['emoney'] = $_POST['goods_review_emoney'];
				$result = $this->Boardmodel->data_modify($boardupparams);//상품후기

				###
				if (!empty($_POST['goods_review_sms']) && !empty($_POST['mbtel'])) {//답변시
					$_POST['mbtel']	= ereg_replace("[^0-9]", "", $_POST['mbtel']);
					$smsparams['msg']		= $_POST['goods_review_sms'];

					$commonSmsData['member']['phone'] = $_POST['mbtel'];
					$commonSmsData['member']['params'] = $smsparams;
					$commonSmsData['member']['mid'] = $minfo['userid'];
					
					$result = commonSendSMS($commonSmsData);

					//sendSMS($_POST['mbtel'],'goods_review',$minfo['userid'],$smsparams);
					###
				}

				$callback = "parent.emoneyclose();";
				openDialogAlert("적립금을 지급하였습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert("적립금지급이 실패하였습니다.",400,140,'parent',$callback);
			}
			exit;
		}

		/* 상품후기외 게시판의 적립금 지급 */
		elseif($mode == 'board_emoney_save') {
			if(empty($_POST['mseq'])) {
				openDialogAlert("잘못된 접근입니다.",400,140,'parent','');
				exit;
			}

			//회원정보체크
			$this->load->model('membermodel');
			$minfo = $this->membermodel->get_member_data($_POST['mseq']);
			if(!empty($minfo)) { //회원정보체크
				$emoney['gb']									= 'plus';
				$emoney['type']								= 'board_'.BOARDID;//table
				$emoney['goods_review']				= $_POST['seq'];
				$emoney['goods_review_parent']	= $_POST['seq'];
				$emoney['emoney']							= $_POST['board_emoney'];
				if( defined('__SELLERADMIN__') === true ) {
					$emoney['manager_seq']	= $this->providerInfo['provider_seq'];
				}else{
					$emoney['manager_seq']	= $this->managerInfo['manager_seq'];
				}
				$emoney['memo']		= $_POST['board_memo'];
				if($_POST['board_reserve_select']=='year'){
					$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['board_reserve_year']));
				}else if($_POST['board_reserve_select']=='direct'){
					$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['board_reserve_direct'], date("d"), date("Y")));
				}
				$emoney['limit_date']	= $limit_date;
				$this->membermodel->emoney_insert($emoney, $minfo['member_seq']);

				###
				if (!empty($_POST['board_sms']) && !empty($_POST['mbtel'])) {//답변시
					$_POST['mbtel']	= ereg_replace("[^0-9]", "", $_POST['mbtel']);
					$smsparams['msg']		= $_POST['goods_board_sms'];
					$commonSmsData['member']['phone'] = $_POST['mbtel'];
					$commonSmsData['member']['params'] = $smsparams;
					$commonSmsData['member']['mid'] = $minfo['userid'];
					
					$result = commonSendSMS($commonSmsData);

					//sendSMS($_POST['mbtel'],'goods_review',$minfo['userid'],$smsparams);
					###
				}

				$callback = "parent.boardemoneyclose();";
				openDialogAlert("적립금을 지급하였습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert("적립금지급이 실패하였습니다.",400,140,'parent',$callback);
			}
			exit;
		}

		/* Best설정 */
		elseif($mode == 'goods_review_best') {
			$params['best'] = $_POST['best'];
			$result = $this->Boardmodel->data_modify($params);
			echo $result;
			exit;
		}

		/* 상품후기 삭제시 적립금/포인트 회수창 */
		elseif($mode == 'goods_review_less_view') {
			$parentsql['whereis']	= ' and seq= "'.$_POST['delseq'].'" ';
			$parentsql['select']		= ' * ';
			$datarow = $this->Boardmodel->get_data($parentsql);//게시판목록
			if(empty($datarow)) {
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"존재하지 않는 게시물입니다.");
				echo json_encode($return);
				exit;
			}

			$msg = '<div style="text-align:left">'.$manager['name'].' 삭제 시 지급된 적립 금액을 ';

			$autoemoneylay		=  getBoardEmoneyAutotxt($datarow, $reviewless);//상품후기 삭제시 회수정보
			$auto_emoney			= ($reviewless['emoney'])?$reviewless['emoney']:0;//자동지급 총적립금
			$auto_point				= ($reviewless['point'])?$reviewless['point']:0;//자동지급 총포인트

			$emoneyview = getBoardEmoneybtn($datarow, $manager, 'viewdelete');
			$input_emoney		= ($emoneyview)?$emoneyview:0;//수동지급
			getminfo($this->manager, $datarow, $minfo, $boardname);//회원정보
			$datarow['name'] = $boardname;
			if($minfo){
				$mb_emoney	= ($minfo['emoney'])?$minfo['emoney']:0;
				$mb_point		= ($minfo['point'])?$minfo['point']:0;
			}

			$ispointuse			= $this->isplusfreenot['ispoint'];
			$less_emoney = 0;$less_point = 0;
			$less_emoney =($auto_emoney)+($input_emoney);
			$less_point = $auto_point;

			$msg.= ' <span class="red">회수할 수 있습니다.</span>';
			$msg.= '<br/>';

			if( $auto_emoney>0 || $auto_point>0 ) {
				$msg1= '<div style="padding:3px; 0px" > - 자동 지급 : ';
				$msg1.= ' 적립금 '.number_format($auto_emoney).'원';
				if( $ispointuse || $auto_point>0 ) $msg1.= ' / 포인트 '.number_format($auto_point).'P';
				$msg1.= '</div>';
				$msg.=$msg1;
			}

			if( $input_emoney > 0 ) {
				$msg2= '<div style="padding:3px; 0px" > - 수동 지급 : ';
				$msg2.= ' 적립금 '.number_format($input_emoney).'원';
				$msg2.= '</div>';
				$msg.=$msg2;
			}

			$msg3= '<div style="padding:3px; 0px" > - 현재 보유 : ';
			$msg3.= ' 적립금 '.number_format($mb_emoney).'원';
			if( $ispointuse || $mb_point>0 ) $msg3.= ' / 포인트 '.number_format($mb_point).'P';
			$msg3.= '</div>';
			$msg.=$msg3;

			$msg.= '<br/>';

			$msg4= '<div style="padding:3px; 0px" >';
			$msg4.= ' 회수 적립금 <input type="text" name="board_less_emoney" id="board_less_emoney" style="text-align: right;" class="line onlynumber" size="8" value="'.$less_emoney.'" /> 원';
			if($less_emoney>$mb_emoney) $msg4.= ' (<span class="red">보유 적립금 부족</span>)';
			$msg4.= '</div>';
			$msg.=$msg4;

			if( $ispointuse || $less_point>0 ) {
				$msg5= '<div style="padding:3px;" >';
				$msg5.= ' 회수 포인트  <input type="text" name="board_less_point" id="board_less_point" style="text-align: right;" class="line onlynumber"  size="8"  value="'.$less_point.'" />  P';
				if($less_point>$mb_point) $msg5.= ' &nbsp;(<span class="red">보유 포인트 부족</span>)';
				$msg5.= '</div>';
				$msg.=$msg5;
			}

			$msg.= '<br/>';//$msg.= '<br/>';

			if( $less_emoney>$mb_emoney || $less_point>$mb_point ){
				//$msg.= '<div style="padding:3px 0px;" > 회수할 적립 금액이 부족합니다.</div>';
			}
			$msg.= '<div style="padding:3px 0px;" > 삭제된 게시글은 복구할 수 없습니다. 정말로 삭제하시겠습니까? </div>';

			if( $less_emoney || $less_point ){
				$return = array('result'=>'lees', 'name'=>$manager['name'], 'msg'=>$msg);
			}else{
				$return = array('result'=>'delete', 'name'=>$manager['name'], 'msg'=>$msg);
			}
			echo json_encode($return);
			exit;
		}

		/* 상품후기외 게시글 삭제시 적립금/포인트 회수창 */
		elseif($mode == 'board_less_view') {

			$parentsql['whereis']	= ' and seq= "'.$_POST['delseq'].'" ';
			$parentsql['select']		= ' * ';
			$datarow = $this->Boardmodel->get_data($parentsql);//게시판목록
			if(empty($datarow)) {
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"존재하지 않는 게시물입니다.");
				echo json_encode($return);
				exit;
			}

			$msg = '<div style="text-align:left">'.$manager['name'].'  삭제 시 지급된 적립 금액을 ';
			$emoneyview = getBoardEmoneybtn($datarow, $manager, 'viewdelete');
			$input_emoney		= ($emoneyview)?$emoneyview:0;//수동지급
			getminfo($this->manager, $datarow, $minfo, $boardname);//회원정보
			$datarow['name'] = $boardname;
			if($minfo){
				$mb_emoney	= ($minfo['emoney'])?$minfo['emoney']:0;
				$mb_point		= ($minfo['point'])?$minfo['point']:0;
			}

			$ispointuse			= $this->isplusfreenot['ispoint'];
			$less_emoney = 0;$less_point = 0;
			$less_emoney = $input_emoney;

			$msg.= ' <span class="red">회수할 수 있습니다.</span>';
			$msg.= '<br/>';

			if( $input_emoney > 0 ) {
				$msg2= '<div style="padding:3px; 0px" > - 수동 지급 : ';
				$msg2.= ' 적립금 '.number_format($input_emoney).'원';
				$msg2.= '</div>';
				$msg.=$msg2;
			}

			$msg3= '<div style="padding:3px; 0px" > - 현재 보유 : ';
			$msg3.= ' 적립금 '.number_format($mb_emoney).'원';
			$msg3.= '</div>';
			$msg.=$msg3;

			$msg.= '<br/>';

			$msg4= '<div style="padding:3px; 0px" >';
			$msg4.= ' 회수 적립금 <input type="text" name="board_less_emoney" id="board_less_emoney" style="text-align: right;" class="line onlynumber" size="8" value="'.$less_emoney.'" /> 원';
			if($less_emoney>$mb_emoney) $msg4.= ' (<span class="red">보유 적립금 부족</span>)';
			$msg4.= '</div>';
			$msg.=$msg4;

			$msg.= '<br/>';//$msg.= '<br/>';

			if( $less_emoney>$mb_emoney ){
				//$msg.= '<div style="padding:3px 0px;" > 회수할 적립 금액이 부족합니다.</div>';
			}
			$msg.= '<div style="padding:3px 0px;" > 삭제된 게시글은 복구할 수 없습니다. 정말로 삭제하시겠습니까? </div>';

			if( $less_emoney){
				$return = array('result'=>'lees', 'name'=>$manager['name'], 'msg'=>$msg);
			}else{
				$return = array('result'=>'delete', 'name'=>$manager['name'], 'msg'=>$msg);
			}
			echo json_encode($return);
			exit;
		}
	}



	public function icon(){
		/* 이미지파일 확장자 */
		$this->arrImageExtensions = array('jpg','jpeg','png','gif','bmp','tif','pic');
		$this->arrImageExtensions = array_merge($this->arrImageExtensions,array_map('strtoupper',$this->arrImageExtensions));

		$file_ext		= @end(explode('.', $_FILES['goodsReviewIconImg']['name']));//확장자추출

		$config['upload_path'] = $this->Boardmanager->goodsreviewicondir;
		$config['allowed_types'] = implode('|',$this->arrImageExtensions);
		$config['max_size']	= $this->config_system['uploadLimit'];
		$config['file_name'] = 'emotion_'.date('YmdHis').rand(0,9).'.'.$file_ext;
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('goodsReviewIconImg') ) {
			$err = $this->upload->display_errors();
			openDialogAlert($err,400,140,'parent',$callback);
		}else{
			$fileInfo = $this->upload->data();
			code_save('goodsReviewIcon',array($config['file_name']=>'사용자'));
			$callback = "parent.set_goods_icon();";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

}

/* End of file board_process.php */
/* Location: ./app/controllers/admin/board_goods_process.php */