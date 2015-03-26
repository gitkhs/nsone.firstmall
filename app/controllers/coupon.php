<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class coupon extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('couponmodel');
		$this->load->helper('coupon');
		$this->load->helper('member');
	}

	public function main_index()
	{
		redirect("/coupon/index");
	}

	public function index()
	{  
		if( strstr($_SERVER["REQUEST_URI"],"popup=1") || strstr($_SERVER["HTTP_REFERER"],"popup=1") ) $this->returnpopup = true;
		if(!isset( $_GET['return_url'])) $_GET['return_url'] = "/main/index";
		// 로그인 체크
		$_SERVER["REQUEST_URI"] = $_GET['return_url']; 
		login_check_confirm();

		if( gettype($_GET['type']) == 'string' && number_format($_GET['type']) == 0) {//쿠폰종류 전체다운시 : 이달의쿠폰과 상품쿠폰다운 
				$this->template->include_('getCouponCode'); 
				getCouponCode($_GET['type']); 
				if( $_GET['type'] == 'membermonths' ||  $_GET['type'] == 'membermonths_shipping') {
					$this->coupontypeall = true;
					$this->coupontypeall_count = 0;
					if( $_GET['type'] == 'membermonths' ||  $_GET['type'] == 'membermonths_shipping'  ) {
						foreach($this->dataloopcouponcode[$this->mdata['group_seq']]['loop'] as $datacoupon){$idx++; 
							$_GET['no'] = $datacoupon['coupon_seq'];
							$this->download_member(); 
						}
					}elseif( $_GET['type'] == 'download' ) {
						foreach($this->dataloopcouponcode['loop'] as $datacoupon){$idx++;
							$_GET['no'] = $datacoupon['coupon_seq'];
							$this->download(); 
						}
					}else{						
						foreach($this->dataloopcouponcode['loop'] as $datacoupon){$idx++;
							$_GET['no'] = $datacoupon['coupon_seq'];
							$this->download_member(); 
						}
					} 
					if( $this->coupontypeall_count > 0 ) {
						$msg = "총 [".number_format($this->coupontypeall_count)."]건의 쿠폰이 다운로드 되었습니다."; 
						if( $_POST ) {
							$result = array('result'=>true, 'msg'=>$msg);
							echo json_encode($result);
						}else{				
							openDialogAlert($msg,400,140,'parent','top.document.location.reload();');
						}  
					}else{
						$msg = "이미 다운받은 쿠폰이 있습니다.";
						if( $_POST ) {
							$result = array('result'=>false, 'msg'=>$msg);
							echo json_encode($result);
						}else{				
							openDialogAlert($msg,400,140,'parent');
							exit;
						}
					}
				} 
		}elseif( $_GET['no']) {//개별다운시
			downloadcouponcheck($_GET['no'],'goodslist');//다운로드/모바일/등급 쿠폰 제한수량체크 @2014-02-13 
			if( $this->couponinfo['type'] == "download" || $this->couponinfo['type'] == "mobile" ) { 
				$_GET['coupon'] = (int) $_GET['no']; 
				$this->download(); 
			}else{
				$this->download_member();
			}
		}else{
			$msg = "잘못된 접근입니다.";
			if( $_POST ) {
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}
			exit;
		}
		//$this->download_member();//팝업용다운(생일자/기념일/회원 등급 조정 쿠폰/회원 등급 조정 쿠폰 (배송비))
		//페이지다운(배송비 쿠폰/신규가입 쿠폰/신규가입 쿠폰 (배송비)/컴백회원 쿠폰/컴백회원 쿠폰 (배송비)/이달의 등급 쿠폰/이달의 등급 쿠폰 (배송비)/첫 구매 쿠폰)
	}

	//상품쿠폰다운로드받기
	public function download()
	{ 
		// 로그인 체크
		$memberSeq = $this->userInfo['member_seq'];
		if(!isset( $_GET['return_url'])) $_GET['return_url'] = "/main/index";
		$_SERVER["REQUEST_URI"] = $_GET['return_url'];
		login_check();
		
		$couponSeq = (int) $_GET['coupon'];
		$goodsSeq = (int) $_GET['goods']; 
		$this->load->model('goodsmodel');

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);
		$tmp = $this->goodsmodel -> get_goods_category($goodsSeq);
		foreach($tmp as $data) $category[] = $data['category_code'];
		downloadcouponcheck($couponSeq);//다운로드/모바일/등급 쿠폰 제한수량체크@2014-02-13

		// 쿠폰 정보 확인
		$couponData = $this->couponmodel->get_able_download($today,$memberSeq,$goodsSeq,$category,$couponSeq);
		if(!$couponData) {
			openDialogAlert("쿠폰이 올바르지 않습니다.",400,140,'parent','');
			exit;
		} 
		
		if( $couponData['issue_stop'] == '1' ) {//발급중지된 쿠폰
			openDialogAlert("쿠폰이 올바르지 않습니다.",400,140,'parent','');
			exit;
		}

		if( $couponData['type'] == "download" || $couponData['type'] == "mobile" ) {
			//다운로드/모바일 쿠폰중에서 중복다운로드가 아닌 경우체크 
			if( $couponData['duplication_use'] != 1 && $couponData['unused_cnt']>0 && ($couponData['unused_cnt'] != $couponData['cancel_cnt']) ) {
				openDialogAlert("이미 다운받은 쿠폰이 있습니다.",400,140,'parent','');
				exit;
			}
		}elseif( $couponData['unused_cnt']>0 ) {
			openDialogAlert("이미 다운받은 쿠폰이 있습니다.",400,140,'parent','');
			exit;
		}

		$return = $this->couponmodel->_members_downlod($couponSeq,$memberSeq);//_goods_downlod()=>_members_downlod()
		if( $return ) { 
			openDialogAlert("쿠폰이 다운로드 되었습니다.",400,140,"parent","parent.coupondownlist('".$goodsSeq."','".$_GET[return_url]."');");
		}else{ 
			openDialogAlert("쿠폰다운로드가 실패 되었습니다.",400,140,"parent","parent.coupondownlist('".$goodsSeq."','".$_GET[return_url]."');");
		}
	}

	//마이페이지의 회원쿠폰 다운받기
	public function download_member()
	{ 
		// 로그인 체크
		$memberSeq = $this->userInfo['member_seq']; 
		if(!isset( $_GET['return_url'])) $_GET['return_url'] = "/main/index";
		$_SERVER["REQUEST_URI"] = $_GET['return_url'];
		login_check();

		$couponSeq = ($_POST['coupon_seq'])?(int) $_POST['coupon_seq']:(int) $_GET['no'];
		if(empty($couponSeq)){
			$msg = "잘못된 접근입니다.";
			if( $_POST ) {
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}
			exit;
		}

		if(empty($this->userInfo['member_seq'])){
			$msg = "잘못된 접근입니다.";
			if( $_POST ) {
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}
		}

		$this->load->model('membermodel');
		$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보
		if($this->mdata){ 

			$this->mdata['thisyear_birthday'] = date("Y").substr($this->mdata['birthday'],4,6);
			if(checkdate(substr($this->mdata['thisyear_birthday'],5,2),substr($this->mdata['thisyear_birthday'],8,2),substr($this->mdata['thisyear_birthday'],0,4)) != true) {
				$this->mdata['thisyear_birthday'] = date("Y-m-d",strtotime('-1 day', strtotime($this->mdata['thisyear_birthday'])));
			} 

			$this->mdata['thisyear_anniversary'] = date("Y").'-'.$this->mdata['anniversary'];//기념일(mm-dd) 추가
			if(checkdate(substr($this->mdata['thisyear_anniversary'],5,2),substr($this->mdata['thisyear_anniversary'],8,2),substr($this->mdata['thisyear_anniversary'],0,4)) != true) {
				$this->mdata['thisyear_anniversary'] = date("Y-m-d",strtotime('-1 day', strtotime($this->mdata['thisyear_anniversary'])));
			}

			//등급조정쿠폰의 등업된 경우에만 다운가능
			if ($this->mdata['grade_update_date'] != '0000-00-00 00:00:00') {
				$fm_member_group_logsql = "select * from fm_member_group_log where member_seq = '".$this->userInfo['member_seq']."' order by regist_date desc limit 0,1";
				$fm_member_group_logquery = $this->db->query($fm_member_group_logsql);  
				$fm_member_group_log =  $fm_member_group_logquery->row_array(); 
				if( ($fm_member_group_log['prev_group_seq'] >= $fm_member_group_log['chg_group_seq']) || ($this->userInfo['group_seq'] == 1) ) {
					$this->mdata['grade_update_date'] = '';
				}
			}else{
				$this->mdata['grade_update_date'] = substr($this->mdata['regist_date'],0,10);
			}
		}

		// 쿠폰 정보 확인
		$couponData 	= $this->couponmodel->get_my_download_member($couponSeq,$this->mdata);
		if(!$couponData) {
			if( $this->coupontypeall ) return false;
				$msg = "쿠폰이 올바르지 않습니다.";
			if( $_POST ) {
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}  
			exit;
		}

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);
		downloadcouponcheck($couponSeq,'mypage');//다운로드/모바일/등급 쿠폰 제한수량체크 @2014-02-13 

		if( $this->couponinfo['issue_stop'] == '1' ) {//발급중지된 쿠폰
			openDialogAlert("쿠폰이 올바르지 않습니다.",400,140,'parent','');
			exit;
		}

		// 쿠폰 정보 확인 
		if( $this->couponinfo['type'] == "download" || $this->couponinfo['type'] == "mobile" ) {//개별쿠폰
			$goodsdowncoupons = $this->couponmodel->get_able_download($today,$memberSeq,$goodsSeq,$category,$couponSeq);
			if(!$goodsdowncoupons) { 
				if( $this->coupontypeall ) return false;
				$msg = "쿠폰이 올바르지 않습니다.";
				if( $_POST ) {
					$result = array('result'=>false, 'msg'=>$msg);
					echo json_encode($result);
				}else{				
					openDialogAlert($msg,400,140,'parent');
				} 
				exit;
			}

			if( $goodsdowncoupons['duplication_use'] != 1 && $goodsdowncoupons['unused_cnt']>0 ) {//다운로드/모바일 쿠폰중에서 중복다운로드가 아닌 경우체크
				if( $this->coupontypeall ) return false;
				$msg = "이미 다운받은 쿠폰이 있습니다.";
				if( $_POST ) {
					$result = array('result'=>false, 'msg'=>$msg);
					echo json_encode($result);
				}else{				
					openDialogAlert($msg,400,140,'parent');
				} 
				exit;
			}
		}else{
			$downcoupons = $this->couponmodel->get_admin_download($memberSeq, $couponSeq);//다운쿠폰체크  
		
			//배송비쿠폰, 월 1회 다운가능쿠폰체크
			if( ($downcoupons['type'] == 'shipping' && $couponData['duplication_use'] != 1) || ( ($downcoupons['type'] == 'membermonths' || $downcoupons['type'] == 'membermonths_shipping' || $downcoupons['type'] == 'memberlogin' || $downcoupons['type'] == 'memberlogin_shipping' || $downcoupons['type'] == 'order' ) && substr($downcoupons['regist_date'],0,7) == date('Y-m',time()) ) ) {
				if( $this->coupontypeall ) return false;
				$msg = "이미 다운받은 쿠폰이 있습니다.";
				if( $_POST ) {
					$result = array('result'=>false, 'msg'=>$msg);
					echo json_encode($result);
				}else{				
					openDialogAlert($msg,400,140,'parent');
				} 
				exit;
			}

		
			if( ($downcoupons['type'] == 'birthday' || $downcoupons['type'] == 'anniversary') && ( $downcoupons['down_year'] == date('Y') || $downcoupons['down_year'] == date('Y')+1) ) {
				if( $this->coupontypeall ) return false;
				//올해 이미 다운받았을 경우
				$msg = "이미 다운받은 쿠폰이 있습니다.";
				if( $_POST ) {
					$result = array('result'=>false, 'msg'=>$msg);
					echo json_encode($result);
				}else{				
					openDialogAlert($msg,400,140,'parent');
				} 
				exit;
			}
			
			if($downcoupons['type'] == 'memberGroup' || $downcoupons['type'] == 'memberGroup_shipping') { 

				// 등급 조정 쿠폰 재 다운로드 가능 하게 처리 leewh 2015-03-13
				if ($this->userInfo['group_seq'] > $downcoupons['down_year']) { 
					// 등급이 오른 경우에만 재다운 가능
					unset($downcoupons);
				}

				$fm_member_group_logsql = "select * from fm_member_group_log where member_seq = '".$this->userInfo['member_seq']."' order by regist_date desc limit 0,1";
				$fm_member_group_logquery = $this->db->query($fm_member_group_logsql);  
				$fm_member_group_log =  $fm_member_group_logquery->row_array(); 
				//등급쿠폰의 등업된 경우에만 다운가능
				if( ($fm_member_group_log['prev_group_seq'] >= $fm_member_group_log['chg_group_seq']) || ($this->userInfo['group_seq'] == 1) ) {
					if( $this->coupontypeall ) return false;
				$msg = "잘못된 접근입니다.";
					if( $_POST ) {
						$result = array('result'=>false, 'msg'=>$msg);
						echo json_encode($result);
					}else{				
						openDialogAlert($msg,400,140,'parent');
					} 
					exit;
				}

			}
		}

		if($couponData['type'] == 'birthday' && ( empty($this->mdata['birthday']) || $this->mdata['birthday'] == '0000-00-00' ) ) { 
			if( $this->coupontypeall ) return false;
			$msg = "생일을 정확히 입력해 주세요.";
			if( $_POST ) {
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}  
			exit;
		}

		if ( $couponData['type'] == 'anniversary' && empty($this->mdata['anniversary']) ) { 
			
			if( $this->coupontypeall ) return false;
			$msg = "기념일을 정확히 입력해 주세요.";
			if( $_POST ) {
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}
			exit;
		} 

		$chkResult		= chkCouponAbleDownDate($couponData);//기간체크, 등급체크  
		if(!($couponData['type'] == 'member' || $couponData['type'] == 'member_shipping') && $this->userInfo['member_seq'] ) {// $chkResult['result'] && 
			$couponData['coupondowninfo'] = $downcoupons;
			
			### SEARCH 
			$cksc['today']		= $today;
			$cksc['coupon_type'][]		= $couponData['type'];
			if( in_array($couponData['type'], $CI->couponmodel->couponshipping) ) {//배송비포함된 쿠폰
				$cksc['coupon_type'][]		= $type."_shipping";
			}
			$cksc['year']			= date('Y',time());
			$cksc['month']		= date('Y-m',time());
			if($this->mdata) $mycouponcode = $this->couponmodel->get_my_download($cksc,$this->mdata, 'couponall'); 
			getcouponcodewonloadcheck(&$couponData,$mycouponcode,$this->mdata);//다운받았을 경우이거나 조건대상이 아닌것  
			if($couponData['coupondownfinish']) {//이미받은쿠폰
				$chkResult['result']	= false;
				$chkResult['msg']	= '고객님은 이미 쿠폰을 받으셨습니다.';
			}elseif($couponData['coupondownno']) {//대상이아닌경우
				$chkResult['result']	= false;
				$chkResult['msg']	= '죄송합니다. 회원님은 대상이 아닙니다.';
			}
		} 
		if( $chkResult['result'] != true ){ 
			if( $this->coupontypeall ) return false;
			$msg = $chkResult['msg'];
			if( $_POST ) {
				$result = array('result'=>$chkResult['result'], 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}
			exit;
		}
		//debug_var($chkResult);exit;
		if	(!$chkResult['result']){ 
			if( $this->coupontypeall ) return false;
			$msg = $chkResult['msg'];
			if( $_POST ) {
				$result = array('result'=>$chkResult['result'], 'msg'=>$msg);
				echo json_encode($result);
			}else{				
				openDialogAlert($msg,400,140,'parent');
			}
			exit;
		}

		if($couponData['type'] == 'point'){//point 전환조건체크
			$this->load->model('membermodel');
			$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보
			if( $this->mdata['point']<1 || $this->mdata['point'] < $couponData['coupon_point'] ) {//포인트가 작거나 없는 경우
				if( $this->mdata['point']<1 ) {//포인트가 작거나 없는 경우 
					$msg = " 보유포인트가 없습니다.";
					if( $_POST ) {
						$result = array('result'=>false, 'msg'=>$msg);
						echo json_encode($result);
					}else{				
						openDialogAlert($msg,400,140,'parent');
					} 
					exit;
				}else{
					$msg = "전환포인트 금액이 보유포인트보다 작습니다.";
					if( $_POST ) {
						$result = array('result'=>false, 'msg'=>$msg);
						echo json_encode($result);
					}else{				
						openDialogAlert($msg,400,140,'parent');
					}  
					exit;
				}
			}
		}

		$return = $this->couponmodel->_members_downlod( $couponSeq, $memberSeq);
		if( $return ) {
			couponsave_member_session($couponData);
			$msg = "쿠폰이 다운로드 되었습니다.";
			if( $this->coupontypeall ) { 
				$this->coupontypeall_count++;
			}else{
				if( $_POST ) {
					$result = array('result'=>true, 'msg'=>$msg);
					echo json_encode($result);
				}else{				
					openDialogAlert($msg,400,140,'parent','top.document.location.reload();');
				}
				exit;
			}
		}else{
			if( $this->coupontypeall ) { 
			}else{
				$msg = "쿠폰다운로드가 실패 되었습니다.";
				if( $_POST ) {
					$result = array('result'=>false, 'msg'=>$msg);
					echo json_encode($result); 
				}else{				
					openDialogAlert($msg,400,140,'parent');
				}
				exit;
			}
		}
		
		if( $this->coupontypeall ) { 
		}else{
			echo json_encode($result);
			exit;
		}
	}

	//마이페이지의 오프라인 쿠폰 인증하기
	public function offlinecoupon_member()
	{
		$offline_serialnumber =  trim($_POST['offline_serialnumber']);
		if(empty($offline_serialnumber)){
			$result = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
			echo json_encode($result);
			exit;
		}

		if(empty($this->userInfo['member_seq'])){
			$result = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
			echo json_encode($result);
			exit;
		}

		// 로그인 체크
		$memberSeq = $this->userInfo['member_seq'];
		if(!isset( $_GET['return_url'])) $_GET['return_url'] = "/main/index";
		$_SERVER["REQUEST_URI"] = $_GET['return_url'];
		login_check();

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);
		// offline쿠폰 인증번호 체크
		$sc['whereis'] = ' and offline_serialnumber = "'.$offline_serialnumber.'" ';
		$offlienresult = $this->couponmodel->get_offlinecoupon_total_count($sc);
		if(!$offlienresult){//자동등록에 없는경우
			$offlienresult = $this->couponmodel->get_offlinecoupon_input_total_count($sc);
			if($offlienresult){
				$offlinecouponinput = $this->couponmodel->get_offlinecoupon_input_serialnumber($offline_serialnumber);
				$offlinecoupon = $offlinecouponinput;//공통이용을위해
			}
		}else{//자동인경우
			$offlinecoupon = $this->couponmodel->get_offlinecoupon_serialnumber($offline_serialnumber);
		}

		if($offlienresult) {
			$couponSeq = $offlinecoupon['coupon_seq'];
			$coupons 			= $this->couponmodel->get_coupon($couponSeq);
			
			if( $coupons['issue_stop'] == '1' ) {//발급중지된 쿠폰
				$result = array('result'=>false, 'msg'=>"쿠폰이 올바르지 않습니다.");
				echo json_encode($result);
				//openDialogAlert("쿠폰이 올바르지 않습니다.",400,140,'parent','');
				exit;
			}

			// 쿠폰 정보 확인
			$offlinecoupon_downcnt = $this->couponmodel->get_offlinecoupon_download_cnt($memberSeq, $couponSeq);

			//자동생성의 랜덤 또는 수동생성의 수동등록인 경우 사용여부체크(선착순없음)와 인증번호 중복체크
			if( $coupons['offline_type'] == 'random' || $coupons['offline_type'] == 'file' ) {
				if( $offlinecoupon['use_count'] == 0 ) {
					$result = array('result'=>false, 'msg'=>"이미 사용된 인증번호입니다.");
					echo json_encode($result);
					exit;
				}

				$serialnumber_downcnt = $this->couponmodel->get_offlinecoupon_serialnumber_download_cnt($couponSeq, $offline_serialnumber);
				if( $serialnumber_downcnt ) {
					$result = array('result'=>false, 'msg'=>"이미 다운받은 인증번호입니다.");
					echo json_encode($result);
					exit;
				}
			}

			$chk_start_date = substr($coupons['download_startdate'],0,10);
			$chk_end_date = substr($coupons['download_enddate'],0,10);
			$chk_now_date = substr($now,0,10);

			if($coupons['download_limit_ea'] <= $offlinecoupon_downcnt && $offlinecoupon_downcnt ){//인증 횟수가 제한되었을 경우
				$result = array('result'=>false, 'msg'=>"해당 쿠폰의 인증 횟수가 초과되었습니다.");
			}elseif($offlinecoupon['use_count'] == 0 && $coupons['offline_limit'] == 'limit' ){//선착순 허용수를 넘었을 경우
				$result = array('result'=>false, 'msg'=>"해당 쿠폰의 선착순 등록이 종료되었습니다.");
			}elseif($coupons['download_startdate'] && $coupons['download_enddate'] &&  !($chk_start_date<=$chk_now_date && $chk_end_date>=$chk_now_date) ){//인증기간이 아닐 경우
				if($chk_end_date<$chk_now_date){
					$result = array('result'=>false, 'msg'=>"해당 쿠폰 등록 기간이 지났습니다.");
				}else{
					$result = array('result'=>false, 'msg'=>"해당 쿠폰 등록 기간이 아닙니다.");
				}
			}else{//정상처리인 경우
				if( $coupons['type'] == 'offline_emoney' ) {//오프라인 > 적립금인경우
					$this->load->model('membermodel');
					$emoney['type']			= 'offline';
					$emoney['emoney']		= $coupons['offline_emoney'];
					$emoney['gb']				= 'plus';
					$emoney['memo']		= '['.$coupons['coupon_name'].'] 오프라인 쿠폰 적립';
					if($coupons['offline_reserve_select']=='year'){
						$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$coupons['offline_reserve_year']));
					}else if($coupons['offline_reserve_select']=='direct'){
						$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$coupons['offline_reserve_direct'], date("d"), date("Y")));
					}
					$emoney['limit_date']	= $limit_date;
					$this->membermodel->emoney_insert($emoney, $memberSeq);

					$return = $this->couponmodel->_offlinecoupon_members_emoney_downlod( $couponSeq, $memberSeq, $offline_serialnumber);//발급 및 사용함으로 처리됨
					if( $return ) {

						if($coupons['offline_limit'] == 'limit'){//선착순인 경우 선착순카운트 마이너스
							if($offlinecouponinput){
								$this->couponmodel->set_offlinecoupon_input_use_count($offline_serialnumber);
							}else{
								$this->couponmodel->set_offlinecoupon_use_count($offline_serialnumber);
							}
						}

						$result = array('result'=>true, 'msg'=>"정상적으로 쿠폰이 등록되어, <br>적립금 ".number_format($coupons['offline_emoney'])."원이 적립되었습니다.<br>적립금을 지금 확인하시겠습니까? ",'returnurl'=>"../mypage/emoney");
					}else{
						$result = array('result'=>false, 'msg'=>"쿠폰다운로드가 실패 되었습니다.");
					}
				}elseif( $coupons['type'] == 'offline_coupon' ) {//오프라인 > 기본쿠폰인경우
					$return = $this->couponmodel->_offlinecoupon_members_downlod( $couponSeq, $memberSeq, $offline_serialnumber);
					if( $return ) {

						if($coupons['offline_limit'] == 'limit'){//선착순인 경우 선착순카운트 마이너스
							if($offlinecouponinput){
								$this->couponmodel->set_offlinecoupon_input_use_count($offline_serialnumber);
							}else{
								$this->couponmodel->set_offlinecoupon_use_count($offline_serialnumber);
							}
						}

						$result = array('result'=>true, 'msg'=>"정상적으로 쿠폰이 등록되었습니다.<br>보유 쿠폰을 지금 확인하시겠습니까?",'returnurl'=>"../mypage/coupon");
					}else{
						$result = array('result'=>false, 'msg'=>"쿠폰다운로드가 실패 되었습니다.");
					}
				}
			}
		}else{
			//해당쿠폰 인증번호가 없는경우
			$result = array('result'=>false, 'msg'=>"해당 쿠폰 인증번호가 일치하지 않습니다.");
		}
		echo json_encode($result);
		exit;
	}

	//할인쿠폰 상품상세 할인이 가장큰 쿠폰추출
	public function goods_coupon_max()
	{
		$goodsSeq = (int) $_GET['no'];
		$maxCoupon = goods_coupon_max($goodsSeq); 
		echo json_encode($maxCoupon);
	}
	
	//상품쿠폰 가져오기
	public function goods_coupon_list()
	{ 
		$max = 0; 
		if( !$_GET['no'] ) { 
			$return_url = explode("?",urldecode($_GET['return_url']));
			$tempArr = explode("&",$return_url[1]);
			foreach($tempArr as $k){
				$tmp = explode("=",$k);
				if($tmp[1]){
					$_GET[$tmp[0]] = $tmp[1];
				}
			}
		}

		$goodsSeq = (int) $_GET['no'];
		$today = date('Y-m-d',time());
		$this->load->model('goodsmodel');
		$this->load->model('membermodel');
		
		$tmp = $this->goodsmodel -> get_goods_category($goodsSeq);
		if($tmp) foreach($tmp as $data) $category[] = $data['category_code'];
		$goods = $this->goodsmodel -> get_default_option($goodsSeq);
		$goods_info = $this->goodsmodel -> get_goods($goodsSeq);

		if(! $this->userInfo['member_seq'] ) return false;

		$result = $this->couponmodel->get_able_download_list($today,$this->userInfo['member_seq'],$goodsSeq,$category,$goods['price']);

		if($result) {
			foreach($result as $key => $data){

				//다운로드/모바일/등급 쿠폰 제한수량체크@2014-02-13
				if( downloadcouponcheck($data['coupon_seq'],'goodslist') ) {
					continue;
				}
		
				//사용제한 - 유입경로 체크
				/**if( couponordercheck(&$data, $goodsSeq, $goods['price'], 1) != true ) {
					continue;
				} **/

				//다운로드/모바일 쿠폰중에서 소멸된 쿠폰체크하여 중복다운로드 가능쿠폰 체크
				if( ($data['type'] == "download" || $data['type'] == "mobile") && $data['duplication_use'] == 1 && $data['unused_cnt'] == $data['cancel_cnt']) {
					$data['unused_cnt'] = 0;
				}

				$data['valid_priod_msg'] = "";
				if($data['issue_priod_type'] == 'date'){
					if($data['issue_startdate']) $data['valid_priod_msg'] = " ".$data['issue_startdate'] . "부터";
					if($data['issue_enddate']) $data['valid_priod_msg'] .= " ". $data['issue_enddate'] . "까지";
				}elseif($data['issue_priod_type'] == 'day'){
					if($data['after_issue_day']) $data['valid_priod_msg'] = " 발급 후 ". $data['after_issue_day'] . "일";
				}elseif($data['issue_priod_type'] == 'months'){
					$data['valid_priod_msg'] = " 발급 당월 말일까지 ";
				}
				
				if( $data['issue_priod_type'] == 'date') { 
					$data['issuedaylimit'] = 0; 
					$todayck = date("Y-m-d",time()); 
					if( $data['issue_enddate'] >= date("Y-m-d") ) { 
						$issuedaylimit = intval((strtotime($data['issue_enddate'])-strtotime($todayck)) / 86400); 
						$data['issuedaylimit'] = $issuedaylimit;
						$data['issuedaylimituse'] = true;
					}else{  
						$issuedaylimit = intval((strtotime($todayck)-strtotime($data['issue_enddate'])) / 86400); 
						$data['issuedaylimit'] = $issuedaylimit;
					} 
				}

				$data['use_limit_msg'] = "-";
				if($data['limit_goods_price']){
					$data['use_limit_msg'] = number_format($data['limit_goods_price']) . "원 이상 구매 시";
				}

				if( $data['coupon_same_time'] == 'N' ) {//단독쿠폰
					$data['use_limit_msg'] .= ($data['use_limit_msg'])?',단독':'단독';
				}

				if( $data['sale_payment'] == 'b' ) {//무통장만가능
					$data['use_limit_msg'] .= ($data['use_limit_msg'])?',무통장':'무통장';
				}

				if( empty($data['use_limit_msg']) ) $data['use_limit_msg'] = '-';

				if($max < $data['goods_sale']){
					$max = $data['goods_sale'];
					$maxCoupon = $data;
				}
				$data['download_regist_date'] = ($data['download_regist_date']) ? substr($data['download_regist_date'],2,8):'';

				$list[] = $data;
			}
		}

		$this->template->define(array('LAYOUT'=>$this->template_path()));
		$this->template->assign('list',$list);
		$this->template->print_('LAYOUT');
		// echo json_encode($result);
	}

	//상품쿠폰찾기
	public function coupongoodssearch()
	{  
		$goodsSeq = (int) $_POST['goods'];
		$couponSeq = (int) $_POST['coupon']; 
 
		$today = date('Y-m-d',time());
		$this->load->model('goodsmodel'); 
		
		$tmp = $this->goodsmodel -> get_goods_category($goodsSeq);
		if($tmp) foreach($tmp as $data) $category[] = $data['category_code'];
		$goods = $this->goodsmodel -> get_default_option($goodsSeq);
		if( !$goods ) {
			echo json_encode(array('result'=>false));
			exit;
		} 
		$resultgoods = '';
		$goodsinfo	= $this->goodsmodel->get_goods($goodsSeq);
		$images		= $this->goodsmodel->get_goods_image($goodsSeq);
		$resultgoods['name']	= $goodsinfo['goods_name']; 
		$resultgoods['price']	= number_format($goods['price'])."원";
		if($images){
			foreach($images as $image){
				if($image['thumbCart']){
					$resultgoods['src'] = $image['thumbCart']['image'];break;
				}elseif($image['thumbScroll']){
					$resultgoods['src'] = $image['thumbScroll']['image']; break;
				}elseif($image['list1']){
					$resultgoods['src'] = $image['list1']['image']; break;
				}elseif($image['list2']){
					$resultgoods['src'] = $image['list2']['image']; break;
				}elseif($image['thumbView']){
					$resultgoods['src'] = $image['thumbView']['image']; break;
				}
			}
		}
 
		$issuegoods 	= $this->couponmodel->get_coupon_issuegoods($couponSeq);
		$this->couponinfo 	= $this->couponmodel->get_coupon($couponSeq);
		if( $this->couponinfo['issue_stop'] == '1' ) {//발급중지된 쿠폰
			//pageBack('잘못된 접근입니다.');
			//exit;
		}
		if($this->couponinfo['issue_type'] == 'issue') {
			if($issuegoods) {
				foreach($issuegoods as $key => $tmp) { 
					if( $tmp['goods_seq'] == $goodsSeq ) { 
						$resultck = 'goodsyes';
						break;
					}
				}
				if(!$resultck) $resultck = 'goodsno';
			}else{
				$resultck = 'goodsno';
			}
		}else{
			if($issuegoods) {
				foreach($issuegoods as $key => $tmp) { 
					if( $tmp['goods_seq'] == $goodsSeq ) {
						$resultck = 'goodsno';
						break;
					}
				}
				if(!$resultck) $resultck = 'goodsyes';
			}else{
				$resultck = 'goodsyes';
			}
		}
		$result = array('result'=>$resultck,"goods"=>$resultgoods);
		echo json_encode($result);
	}

	
	//쿠폰적용 상품조회
	public function coupongoodsreviewer()
	{
		$_GET['popup'] = true;
		if( $_GET['download_seq'] ){
			$this->coupondown = true;
		}
		
		$no = (int) $_GET['no'];
		if($this->coupondown) {
			$this->couponinfo 	= $this->couponmodel->get_download_coupon($no);
		}else{
			$this->couponinfo 	= $this->couponmodel->get_coupon($no);
			if( $this->couponinfo['issue_stop'] == '1' ) {//발급중지된 쿠폰
				pageBack('잘못된 접근입니다.');
				exit;
			}
		}

		if( $this->couponinfo['coupon_type'] == 'offline' ) {
			$this->offline();
		}else{
			$this->online();
		}

		$this->template->define(array('LAYOUT'=>$this->template_path())); 
		$this->template->print_('LAYOUT');
	}

	//온라인 배포용 쿠폰정보
	public function online()
	{
		if(!$_GET['no']) { 
			exit;
		}
		if(isset($_GET['no'])) {
			$no = (int) $_GET['no'];

			$this->load->model('goodsmodel');
			$this->load->model('categorymodel');

			if($this->coupondown) {
				$coupons 		= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_download_coupon($no); 
				$coupons['coupondown'] = $this->coupondown;
			}else{
				$coupons 			= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_coupon($no);
			}

			if( $coupons['type'] == 'mobile' && $coupons['sale_agent'] != 'm' ) {//기존 모바일쿠폰제외 
				//$coupons['sale_agent']	= 'm';//사용환경 모바일로 대체
			}

			if (!isset($coupons['coupon_seq'])) pageBack('잘못된 접근입니다.');
			$couponGroups 	= $this->couponmodel->get_coupon_group($no);
			if($this->coupondown) {
				$issuegoods 	= $this->couponmodel->get_coupon_download_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_download_issuecategory($no);
			}else{
				$issuegoods 	= $this->couponmodel->get_coupon_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_issuecategory($no);
			}
			if($couponGroups){
				foreach($couponGroups as $key => $group){
					foreach($this->groups as $tmp){
						if($tmp['group_seq'] == $group['group_seq']){
							$couponGroups[$key]['group_name'] = $tmp['group_name'];
						}
					}
				}
				$this->template->assign(array('couponGroups'=>$couponGroups));
			}

			if(($issuegoods)){
				foreach($issuegoods as $key => $tmp) $arrGoodsSeq[] =  $tmp['goods_seq'];
				$goods = $this->goodsmodel->get_goods_list($arrGoodsSeq,'thumbView');
				foreach($issuegoods as $key => $data) $issuegoods[$key] = @array_merge($issuegoods[$key],$goods[$data['goods_seq']]);
				$this->template->assign(array('issuegoods'=>$issuegoods));
			}

			if($issuecategorys){
				foreach($issuecategorys as $key =>$data) $issuecategorys[$key]['category'] = $this->categorymodel -> get_category_name($data['category_code']);
				$this->template->assign(array('issuecategorys'=>$issuecategorys));
			}

			$dsc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'];
			$downloadtotal = $this->couponmodel->get_download_total_count($dsc);
			$coupons['downloadtotal']	= 0;//발급수-> 수정가능하도록 수정@2012-06-08
			$coupons['downloadtotalbtn']	= number_format($downloadtotal);

			$usc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'].' and use_status = \'used\' ';
			$usetotal = $this->couponmodel->get_download_total_count($usc);
			$coupons['usetotalbtn']	= number_format($usetotal);//사용건수

			// 기간이 있을경우 -> 시간 가공 후 처리
			if($coupons['download_startdate'])	$coupons['download_starthour']	= date('H', strtotime($coupons['download_startdate']));
			if($coupons['download_startdate'])	$coupons['download_startmin']	= date('i', strtotime($coupons['download_startdate']));
			if($coupons['download_startdate'])	$coupons['download_startdate']	= date('Y-m-d', strtotime($coupons['download_startdate']));
			if($coupons['download_enddate'])	$coupons['download_endhour']	= date('H', strtotime($coupons['download_enddate']));
			if($coupons['download_enddate'])	$coupons['download_endmin']		= date('i', strtotime($coupons['download_enddate']));
			if($coupons['download_enddate'])	$coupons['download_enddate']	= date('Y-m-d', strtotime($coupons['download_enddate']));

			if($coupons['download_starttime'])	$coupons['download_starttime_h']= date('H', strtotime($coupons['download_starttime']));
			if($coupons['download_starttime'])	$coupons['download_starttime_m']= date('i', strtotime($coupons['download_starttime']));
			if($coupons['download_endtime'])	$coupons['download_endtime_h']	= date('H', strtotime($coupons['download_endtime']));
			if($coupons['download_endtime'])	$coupons['download_endtime_m']	= date('i', strtotime($coupons['download_endtime']));
			
			if($this->coupondown ) {
				$todayck = date("Y-m-d",time()); 
				if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
					$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
					$coupons['issuedaylimituse'] = true;
				}else{  
					$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
				} 
			}else{
				if( $coupons['issue_priod_type'] == 'date') { 
					$todayck = date("Y-m-d",time()); 
					$coupons['issuedaylimit'] = 0; 
					if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
						$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
						$coupons['issuedaylimituse'] = true;
					}else{  
						$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
					} 
				}
			}
			$this->template->assign(array('coupons'=>$coupons));
		}
		//debug_var($coupons);

		if( $coupons['type'] == 'admin' ||  $coupons['type'] == 'admin_shipping' ){//직접발급시
			$adminissuebtn	= (( $coupons['issue_priod_type'] == 'date' && str_replace("-","", substr($coupons['issue_enddate'],0,10)) < date("Ymd"))) ? false:true;
			$this->template->assign(array('adminissuebtn'=>$adminissuebtn));
		}

		$this->load->model('referermodel');
		$referersaleloop			= $this->referermodel->get_referersale_all();  
		$this->template->assign(array('referersaleloop'=>$referersaleloop));
		$salerefereritem = explode(",",$coupons['sale_referer_item']); 
		unset($salserefereritemloop);
		foreach($salerefereritem as $key=>$sale_referer_item_val ) {  
			if(!$sale_referer_item_val)continue;
			foreach($referersaleloop as $referersale ) {
				if( !in_array($salserefereritemloopa,$sale_referer_item_val) && $referersale['referersale_seq'] == $sale_referer_item_val ) { 
					$salserefereritemloopa[] = $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_seq']		= $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_name']	= $referersale['referersale_name'];
				}
			}
		} 
		if($salserefereritemloop) $this->template->assign(array('salserefereritemloop'=>$salserefereritemloop)); 
	}

	//인쇄용쿠폰정보 보기
	public function offline()
	{
		if(!$_GET['no']) { 
			exit;
		}
		if(isset($_GET['no'])) {
			$no = (int) $_GET['no'];
			$this->load->model('goodsmodel');
			$this->load->model('categorymodel');
			
			if($this->coupondown) {
				$coupons 		= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_download_coupon($no); 
				$coupons['coupondown'] = $this->coupondown;
			}else{
				$coupons 			= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_coupon($no);
			}
			
			if (!isset($coupons['coupon_seq'])) pageBack('잘못된 접근입니다.');
			$couponGroups 		= $this->couponmodel->get_coupon_group($no);
			if($this->coupondown) {
				$issuegoods 	= $this->couponmodel->get_coupon_download_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_download_issuecategory($no);
			}else{
				$issuegoods 	= $this->couponmodel->get_coupon_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_issuecategory($no);
			}
			if($couponGroups){
				foreach($couponGroups as $key => $group){
					foreach($this->groups as $tmp){
						if($tmp['group_seq'] == $group['group_seq']){
							$couponGroups[$key]['group_name'] = $tmp['group_name'];
						}
					}
				}
				$this->template->assign(array('couponGroups'=>$couponGroups));
			}

			if(($issuegoods)){
				foreach($issuegoods as $key => $tmp) $arrGoodsSeq[] =  $tmp['goods_seq'];
				$goods = $this->goodsmodel->get_goods_list($arrGoodsSeq,'thumbView');
				foreach($issuegoods as $key => $data) $issuegoods[$key] = @array_merge($issuegoods[$key],$goods[$data['goods_seq']]);
				$this->template->assign(array('issuegoods'=>$issuegoods));
			}

			if($issuecategorys){
				foreach($issuecategorys as $key =>$data) $issuecategorys[$key]['category'] = $this->categorymodel -> get_category_name($data['category_code']);
				$this->template->assign(array('issuecategorys'=>$issuecategorys));
			}

			$coupons['download_startdate']	= substr($coupons['download_startdate'], 0, 10);
			$coupons['download_enddate']	= substr($coupons['download_enddate'], 0, 10);

			$dsc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'];
			$downloadtotal = $this->couponmodel->get_download_total_count($dsc);
			$coupons['downloadtotal']	= 0;//발급수-> 수정가능하도록 수정@2012-06-08
			$coupons['downloadtotalbtn']	= number_format($downloadtotal);

			$usc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'].' and use_status = \'used\' ';
			$usetotal = $this->couponmodel->get_download_total_count($usc);
			$coupons['usetotalbtn']	= number_format($usetotal);//사용건수

			if($coupons['offline_type'] == 'file'){//엑셀등록인 경우
				$coupons['offlinecoupontotal'] = $this->couponmodel->get_offlinecoupon_input_item_total_count($coupons['coupon_seq']);
			}else{
				$coupons['offlinecoupontotal'] = $this->couponmodel->get_offlinecoupon_item_total_count($coupons['coupon_seq']);
			}

			if($this->coupondown ) {
				$todayck = date("Y-m-d",time()); 
				if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
					$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
					$coupons['issuedaylimituse'] = true;
				}else{  
					$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
				} 
			}else{
				if( $coupons['issue_priod_type'] == 'date') { 
					$todayck = date("Y-m-d",time()); 
					$coupons['issuedaylimit'] = 0; 
					if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
						$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
						$coupons['issuedaylimituse'] = true;
					}else{  
						$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
					} 
				}
			}
			$this->template->assign(array('coupons'=>$coupons));
		}
		
		$this->load->model('referermodel');
		$referersaleloop			= $this->referermodel->get_referersale_all();  
		$this->template->assign(array('referersaleloop'=>$referersaleloop));
		$salerefereritem = explode(",",$coupons['sale_referer_item']); 
		unset($salserefereritemloop);
		foreach($salerefereritem as $key=>$sale_referer_item_val ) {  
			if(!$sale_referer_item_val)continue;
			foreach($referersaleloop as $referersale ) {
				if( !in_array($salserefereritemloopa,$sale_referer_item_val) && $referersale['referersale_seq'] == $sale_referer_item_val ) { 
					$salserefereritemloopa[] = $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_seq']		= $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_name']	= $referersale['referersale_name'];
				}
			}
		} 
		if($salserefereritemloop) $this->template->assign(array('salserefereritemloop'=>$salserefereritemloop));
	}
}

/* End of file coupon.php */
/* Location: ./app/controllers/coupon.php */