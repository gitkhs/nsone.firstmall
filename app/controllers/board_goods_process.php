<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class Board_goods_process extends front_base {

	public function __construct() {
		parent::__construct();

		$boardid = (!empty($_POST['board_id'])) ? $_POST['board_id']:$_GET['board_id'];
		define('BOARDID',$boardid);

		$this->load->library('validation');
		$this->load->library('upload');
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
			//$callback = "parent.document.location.href='".$this->Boardmanager->managerurl."';";
			openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','');
			exit;
		}
 		/* 게시글 파일다운 */
		if($mode == 'goods_review_emoney') {
			//회원정보체크
			$this->load->model('membermodel');
			$minfo = $this->membermodel->get_member_data($_POST['mseq']);
			if(!empty($minfo)) { //회원정보체크
				//emoney
				//emoney history
			}
		}

		/* 상품후기 삭제시 적립금/포인트 회수창 */
		elseif($mode == 'goods_review_less_view') {

			$session_arr = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
			if(!$session_arr['member_seq']){
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			get_auth($manager, '', 'write' , $isperm);
			if ( $isperm['isperm_write'] === false ) {
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['delseq'].'" ';
			$parentsql['select']		= ' * ';
			$datarow = $this->Boardmodel->get_data($parentsql);//게시판목록
			if(empty($datarow)) {
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"존재하지 않는 게시물입니다.");
				echo json_encode($return);
				exit;
			}

			if( $session_arr['member_seq'] != $datarow['mseq']){
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}


			$msg = '<div style="text-align:left">'.$manager['name'].'  삭제 시 지급된 적립 금액을 ';

			$autoemoneylay		=  getBoardEmoneyAutotxt($datarow, $reviewless);//상품후기 삭제시 회수정보
			$auto_emoney			= ($reviewless['emoney'])?$reviewless['emoney']:0;//자동지급 총적립금
			$auto_point				= ($reviewless['point'])?$reviewless['point']:0;//자동지급 총포인트
			//$input_emoney		= ($datarow['emoney'])?$datarow['emoney']:0;//수동지급
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

			$msg.= ' <span style="color:red;" class="red">회수합니다.</span>';
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
			$msg4.= ' 회수 적립금 '.$less_emoney.'원';
			if($less_emoney>$mb_emoney) $msg4.= ' (<span style="color:red;" class="red">보유 적립금 부족</span>)';
			$msg4.= '</div>';
			$msg.=$msg4;

			if( $ispointuse || $less_point>0 ) {
				$msg5= '<div style="padding:3px;" >';
				$msg5.= ' 회수 포인트 '.$less_point.'P';
				if($less_point>$mb_point) $msg5.= ' &nbsp;(<span style="color:red;" class="red">보유 포인트 부족</span>)';
				$msg5.= '</div>';
				$msg.=$msg5;
			}

			$msg.= '<br/>';//$msg.= '<br/>';

			if( $less_emoney>$mb_emoney || $less_point>$mb_point ){
				$msg.= '<div style="padding:3px 0px;" > 회수할 적립 금액이 부족합니다.<br/> 고객센터로 문의해 주십시오. </div>';
				$return = array('result'=>'lees_none', 'name'=>$manager['name'], 'msg'=>$msg);
			}else{
				$msg.= '<div style="padding:3px 0px;" > 삭제된 게시글은 복구할 수 없습니다.<br/> 정말로 삭제하시겠습니까? </div>';

				if( $less_emoney || $less_point ){
					$return = array('result'=>'lees', 'name'=>$manager['name'], 'msg'=>$msg);
				}else{
					$return = array('result'=>'delete', 'name'=>$manager['name'], 'msg'=>$msg);
				}
			}

			echo json_encode($return);
			exit;
		}



		/* 상품후기외 게시글 삭제시 적립금/포인트 회수창 */
		elseif($mode == 'board_less_view') {

			$session_arr = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
			if(!$session_arr['member_seq']){
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			get_auth($manager, '', 'write' , $isperm);
			if ( $isperm['isperm_write'] === false ) {
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['delseq'].'" ';
			$parentsql['select']		= ' * ';
			$datarow = $this->Boardmodel->get_data($parentsql);//게시판목록
			if(empty($datarow)) {
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"존재하지 않는 게시물입니다.");
				echo json_encode($return);
				exit;
			}

			if( $session_arr['member_seq'] != $datarow['mseq']){
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>"잘못된 접근입니다.");
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
			$less_emoney = 0;$less_point = 0;
			$less_emoney = $input_emoney;

			$msg.= ' <span style="color:red;" class="red">회수합니다.</span>';
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
			$msg4.= ' 회수 적립금 '.$less_emoney.'원';
			if($less_emoney>$mb_emoney) $msg4.= ' (<span style="color:red;" class="red">보유 적립금 부족</span>)';
			$msg4.= '</div>';
			$msg.=$msg4;

			$msg.= '<br/>';//$msg.= '<br/>';

			if( $less_emoney>$mb_emoney){
				$msg.= '<div style="padding:3px 0px;" > 회수할 적립 금액이 부족합니다.<br/> 고객센터로 문의해 주십시오. </div>';
				$return = array('result'=>'lees_none', 'name'=>$manager['name'], 'msg'=>$msg);
			}else{
				$msg.= '<div style="padding:3px 0px;" > 삭제된 게시글은 복구할 수 없습니다.<br/> 정말로 삭제하시겠습니까? </div>';

				if( $less_emoney){
					$return = array('result'=>'lees', 'name'=>$manager['name'], 'msg'=>$msg);
				}else{
					$return = array('result'=>'delete', 'name'=>$manager['name'], 'msg'=>$msg);
				}
			}

			echo json_encode($return);
			exit;
		}

	}
}

/* End of file board_process.php */
/* Location: ./app/controllers/admin/board_goods_process.php */