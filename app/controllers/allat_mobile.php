<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class allat_mobile extends front_base {
	public function __construct(){
		parent::__construct();
		$this->load->helper('order');
		$this->load->helper('shipping');
	}

	public function allat()
	{
		header("Content-Type: text/html; charset=EUC-KR");
		$this->load->model('ordermodel');
		$pg = config_load($this->config_system['pgCompany']);
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['allat_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		/*
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}
		*/
		$data_order					= $this->ordermodel -> get_order($_POST['order_seq']);
		$goods_name					= $_POST['goods_name'];
		$goods_seq					= $_POST['goods_seq'];
		
		$payment = str_replace('escrow_','',$data_order['payment']);
		if(strstr($data_order['payment'],"escrow")){
			$pg_param['escorw']  = 1;
			$pg_param['payment'] = $payment;
		}else{
			$pg_param['escorw']  = 0;
			$pg_param['payment'] = $data_order['payment'];
		}

		$param['allat_shop_id']		= $pg['mallCode'];
		$param['allat_order_no']	= $data_order['order_seq'];
		$param['allat_amt']			= $data_order['settleprice'];
		$param['allat_pmember_id']	= "GUEST";
		if( $this->userInfo['userid'] && strlen($this->userInfo['userid']) < 20 ) $param['allat_pmember_id'] = $this->userInfo['userid'];
		$param['allat_product_cd']	= $goods_seq;
		$param['allat_product_nm']	= $goods_name;
		$param['allat_buyer_nm']	= $data_order['order_user_name'];
		$param['allat_recp_nm']		= $data_order['recipient_user_name'];
		$param['allat_recp_addr']	= $data_order['recipient_address']." ".$data_order['recipient_address_detail'];

		$param['allat_card_yn'] 	= 'N';		//신용카드결제
		$param['allat_bank_yn'] 	= 'N';		//계좌이체결제
		$param['allat_abank_yn'] 	= 'N';		//계좌이체결제(모듈확인 필요할듯)
		$param['allat_vbank_yn'] 	= 'N';		//무통장(가상계좌)결제
		$param['allat_hp_yn'] 		= 'N';		//휴대폰결제
		$param['allat_ticket_yn']  	= 'N';	//상품권결제
		if($data_order['payment'] == 'card') $param['allat_card_yn'] = 'Y';
		if($data_order['payment'] == 'account' || $data_order['payment'] == 'escrow_account'){ $param['allat_bank_yn'] = 'Y'; $param['allat_abank_yn'] = 'Y'; }
		if($data_order['payment'] == 'virtual' || $data_order['payment'] == 'escrow_virtual') $param['allat_vbank_yn'] = 'Y';
		if($data_order['payment'] == 'cellphone') $param['allat_hp_yn'] = 'Y';

		$param['allat_zerofee_yn'] = 'Y';
		$param['allat_cash_yn'] = 'N';
		$param['allat_email_addr'] = $data_order['order_email'];
		$param['allat_product_img'] = $pg_param['goods_image'];
		$param['allat_real_yn'] = 'Y';
		$param['allat_abankes_yn'] = 'N';
		$param['allat_vbankes_yn'] = 'N';
		if($pg_param['payment'] == 'account' && $pg_param['escorw']) $param['allat_abankes_yn'] = 'Y';
		if($pg_param['payment'] == 'virtual' && $pg_param['escorw']) $param['allat_vbankes_yn'] = 'Y';
		$param['allat_test_yn']  = 'N';
		if( $pg['mallCode'] == 'FM_pgfreete2' ) $param['allat_test_yn']  = 'Y';

		$param['mobilenew'] = $_POST['mobilenew'];
		foreach($param as $k => $data) $param[$k] = mb_convert_encoding($data,'EUC-KR','UTF-8');
		foreach($pg_param as $k => $data) $pg_param[$k] = mb_convert_encoding($data,'EUC-KR','UTF-8');

		$this->template->assign($param);
	    $this->template->assign($pg_param);
	    $this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir = BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_allat_mobile.html'));
		$this->template->print_('tpl');
	}

	public function receive()
	{
		// 결과값
		$result_cd  = $_POST["allat_result_cd"];
		$result_msg = $_POST["allat_result_msg"];
		$enc_data   = $_POST["allat_enc_data"];

		if(trim($result_cd) == "9999"){
			$result_msg = "사용자 결제 취소";
		}

		// 결과값 Return
		echo "<script>parent.approval_submit('".$result_cd."','".$result_msg."','".$enc_data."');</script>";
	}

	public function approval()
	{
		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');
		$pg = config_load($this->config_system['pgCompany']);
		$order_seq = $_POST['allat_order_no'];
		$orders = $this->ordermodel->get_order($order_seq);

		if(!$orders['order_user_name']) $orders['order_user_name'] = "주문자";
		//Request Value Define
		//----------------------

		/********************* Service Code *********************/
		$at_cross_key 	= $pg['merchantKey'];    //설정필요 [사이트 참조 - http://www.allatpay.com/servlet/AllatBiz/support/sp_install_guide_scriptapi.jsp#shop]
		$at_shop_id   	= $pg['mallCode'];       //설정필요
		$at_amt			= $orders['settleprice'];                             //결제 금액을 다시 계산해서 만들어야 함(해킹방지)
									 //( session, DB 사용 )
		/*********************************************************/

		// 올앳관련 함수 Include
		//----------------------
		include BASEPATH . "../pg/allat_mobile/allatutil.php";

		$at_data   = "allat_shop_id=".$at_shop_id.
			   "&allat_amt=".$at_amt.
			   "&allat_enc_data=".$_POST["allat_enc_data"].
			   "&allat_cross_key=".$at_cross_key;


		// 올앳 결제 서버와 통신 : ApprovalReq->통신함수, $at_txt->결과값
		//----------------------------------------------------------------
		$at_txt = ApprovalReq($at_data,"SSL");
		// 이 부분에서 로그를 남기는 것이 좋습니다.
		// (올앳 결제 서버와 통신 후에 로그를 남기면, 통신에러시 빠른 원인파악이 가능합니다.)

		// 결제 결과 값 확인
		//------------------
		$REPLYCD   =getValue("reply_cd",$at_txt);        //결과코드
		$REPLYMSG  =getValue("reply_msg",$at_txt);       //결과 메세지

		// 결과값 처리
		//--------------------------------------------------------------------------
		// 결과 값이 '0000'이면 정상임. 단, allat_test_yn=Y 일경우 '0001'이 정상임.
		// 실제 결제   : allat_test_yn=N 일 경우 reply_cd=0000 이면 정상
		// 테스트 결제 : allat_test_yn=Y 일 경우 reply_cd=0001 이면 정상
		//--------------------------------------------------------------------------

		if( $pg['mallCode'] == 'FM_pgfreete2' ) $sucess_code = "0001";
		else $sucess_code = "0000";

		if( !strcmp($REPLYCD,"0000") ){
			// reply_cd "0000" 일때만 성공
			$ORDER_NO         =getValue("order_no",$at_txt);
			$AMT              =getValue("amt",$at_txt);
			$PAY_TYPE         =getValue("pay_type",$at_txt);
			$APPROVAL_YMDHMS  =getValue("approval_ymdhms",$at_txt);
			$SEQ_NO           =getValue("seq_no",$at_txt);
			$APPROVAL_NO      =getValue("approval_no",$at_txt);
			$CARD_ID          =getValue("card_id",$at_txt);
			$CARD_NM          =getValue("card_nm",$at_txt);
			$SELL_MM          =getValue("sell_mm",$at_txt);
			$ZEROFEE_YN       =getValue("zerofee_yn",$at_txt);
			$CERT_YN          =getValue("cert_yn",$at_txt);
			$CONTRACT_YN      =getValue("contract_yn",$at_txt);
			$SAVE_AMT         =getValue("save_amt",$at_txt);
			$BANK_ID          =getValue("bank_id",$at_txt);
			$BANK_NM          =getValue("bank_nm",$at_txt);
			$CASH_BILL_NO     =getValue("cash_bill_no",$at_txt);
			$ESCROW_YN        =getValue("escrow_yn",$at_txt);
			$ACCOUNT_NO       =getValue("account_no",$at_txt);
			$ACCOUNT_NM       =getValue("account_nm",$at_txt);
			$INCOME_ACC_NM    =getValue("income_account_nm",$at_txt);
			$INCOME_LIMIT_YMD =getValue("income_limit_ymd",$at_txt);
			$INCOME_EXPECT_YMD=getValue("income_expect_ymd",$at_txt);
			$CASH_YN          =getValue("cash_yn",$at_txt);
			$HP_ID            =getValue("hp_id",$at_txt);
			$TICKET_ID        =getValue("ticket_id",$at_txt);
			$TICKET_PAY_TYPE  =getValue("ticket_pay_type",$at_txt);
			$TICKET_NAME      =getValue("ticket_nm",$at_txt);

			// 로그 저장
			$pg_log['pg']			= $this->config_system['pgCompany'];
			$pg_log['tno'] 			= $SEQ_NO;
			$pg_log['order_seq'] 	= $order_seq;
			$pg_log['amount'] 		= $AMT;
			$pg_log['app_time'] 	= $APPROVAL_YMDHMS;
			$pg_log['app_no'] 		= $APPROVAL_NO;
			$pg_log['card_cd'] 		= $CARD_ID;
			$pg_log['card_name'] 	= $CARD_NM;
			$pg_log['noinf'] 		= $ZEROFEE_YN;
			$pg_log['quota'] 		= $SELL_MM;
			$pg_log['bank_name'] 	= $BANK_NM;
			$pg_log['bank_code'] 	= $BANK_ID;
			$pg_log['depositor'] 	= $ACCOUNT_NM;
			$pg_log['biller'] 		= $INCOME_ACC_NM;
			$pg_log['account'] 		= $ACCOUNT_NO;
			$pg_log['commid'] 		= $HP_ID;
			$pg_log['va_date'] 		= $INCOME_LIMIT_YMD;
			$pg_log['escw_yn'] 		= $ESCROW_YN;
			$pg_log['res_cd'] 		= $REPLYCD;
			$pg_log['res_msg'] 		= $REPLYMSG;
			foreach($pg_log as $k => $v){
				$v = trim($v);
				$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
			}
			$pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);

			$cfg['order'] = config_load('order');

			$result_option		= $this->ordermodel->get_item_option($ordr_idxx);
			$result_suboption	= $this->ordermodel->get_item_suboption($ordr_idxx);
			$data_item_option	= $result_option;

			// 회원 적립금 차감
			if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
			{
				$params = array(
					'gb'		=> 'minus',
					'type'		=> 'order',
					'emoney'	=> $orders['emoney'],
					'ordno'	=> $order_seq,
					'memo'		=> "[차감]주문 ({$order_seq})에 의한 적립금 차감",
				);
				$this->membermodel->emoney_insert($params, $orders['member_seq']);
				$this->ordermodel->set_emoney_use($order_seq,'use');
			}

			// 회원 이머니 차감
			if( $orders['cash'] && $orders['member_seq'] && $orders['cash_use']=='none')
			{
				$params = array(
					'gb'		=> 'minus',
					'type'		=> 'order',
					'cash'	=> $orders['cash'],
					'ordno'	=> $order_seq,
					'memo'		=> "[차감]주문 ({$order_seq})에 의한 이머니 차감",
				);

				$this->membermodel->cash_insert($params, $orders['member_seq']);
				$this->ordermodel->set_cash_use($order_seq,'use');
			}

			//상품쿠폰사용
			if($data_item_option) foreach($data_item_option as $item_option){
				if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
			}
			//배송비쿠폰사용
			if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

			// 장바구니 비우기
			if( $orders['mode'] ) $this->cartmodel->delete_mode($orders['mode']);

	   		if( $orders['payment'] == 'virtual' || $orders['payment'] == 'escrow_virtual'){
				
				$BANK_NM	= mb_convert_encoding($BANK_NM,"UTF-8", "EUC-KR");
				$ACCOUNT_NM = mb_convert_encoding($ACCOUNT_NM,"UTF-8", "EUC-KR");

	   			$data = array(
					'virtual_account'	=> $BANK_NM . " " .$ACCOUNT_NO . " ".$ACCOUNT_NM,
	   				'virtual_date'		=> $INCOME_LIMIT_YMD,
					'pg_approval_number' => $SEQ_NO,
					'pg_transaction_number' => $APPROVAL_NO
				);

				$this->ordermodel->set_step($order_seq,15,$data);
				$log = "올엣 가상계좌 주문접수". chr(10)."[" .$REPLYCD . $REPLYMSG . "]" . chr(10). implode(chr(10),$data);
				$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],'주문접수',$log);

				// 출고량 업데이트를 위한 변수선언
				$r_reservation_goods_seq = array();
				
		   		if($result_option){
					foreach($result_option as $data_option){
						// 출고량 업데이트를 위한 변수정의
						if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $data_option['goods_seq'];
						}
					}
				}
				if($result_suboption){
					foreach($result_suboption as $data_suboption){
						// 출고량 업데이트를 위한 변수정의
						if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $data_option['goods_seq'];
						}
					}
				}

				// 출고예약량 업데이트
				foreach($r_reservation_goods_seq as $goods_seq){
					$this->goodsmodel->modify_reservation_real($goods_seq);
				}

				/* 주문접수 sms발송
				if( $orders['order_cellphone'] && $orders['sms_25_YN'] != 'Y'){
					$params['shopName'] = $this->config_basic['shopName'];
					$params['ordno']	= $orders['order_seq'];
					$commonSmsData = array();
					$commonSmsData['order']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['order']['params'][] = $params;
					$commonSmsData['order']['order_seq'][] = $orders['order_seq'];
					commonSendSMS($commonSmsData);

					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
				}
				*/

				// 발급 쿠폰 사용으로 상태 변경
				//$this->couponmodel->set_download_use_status($ordr_idxx,'used');

				//상품쿠폰사용
				if($result_option) foreach($result_option as $item_option){
					if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
				}
				//배송비쿠폰사용
				if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

	   		}

	   		if( $runout == false && $orders['payment'] != 'virtual' &&  $orders['payment'] != 'escrow_virtual' )
	   		{
	   			$data = array(
	   				'pg_transaction_number' => $SEQ_NO,
	   				'pg_approval_number' => $APPROVAL_NO
	   			);
				$this->ordermodel->set_step($order_seq,25,$data);
				$log = "올엣 결제 확인". chr(10)."[" .$REPLYCD . $REPLYMSG . "]" . chr(10). implode(chr(10),$data);
				if( $orders['payment'] == 'account' )
				{
					$log .= chr(10) . "계좌이체 은행:" . $BANK_NM . " " .$ACCOUNT_NO;
				}

				$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],'결제확인',$log);

				// 계좌이체 결제의 경우 현금영수증
				if( preg_match('/account/',$orders['payment']) && ($orders['step'] < '25' || $orders['step'] > '85') ){
					$result = typereceipt_setting($orders['order_seq']);
				}

				// 출고량 업데이트를 위한 변수선언
				$r_reservation_goods_seq = array();
				
		   		if($result_option){
					foreach($result_option as $data_option){
						// 출고량 업데이트를 위한 변수정의
						if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $data_option['goods_seq'];
						}
					}
				}
				if($result_suboption){
					foreach($result_suboption as $data_suboption){
						// 출고량 업데이트를 위한 변수정의
						if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $data_option['goods_seq'];
						}
					}
				}
				
				// 출고예약량 업데이트
				foreach($r_reservation_goods_seq as $goods_seq){
					$this->goodsmodel->modify_reservation_real($goods_seq);
				}

				// 결제확인 sms발송
				if( $orders['order_cellphone'] && $orders['sms_25_YN'] != 'Y')
				{
					$params['shopName'] = $this->config_basic['shopName'];
					$params['ordno']	= $orders['order_seq'];
					$commonSmsData = array();
					$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['settle']['params'][] = $params;
					$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
					commonSendSMS($commonSmsData);

					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
				}


	   		}
		}else{
			// reply_cd 가 "0000" 아닐때는 에러 (자세한 내용은 매뉴얼참조)
			// reply_msg 는 실패에 대한 메세지
			$this->ordermodel->set_step($order_seq,99);
			$log = "올엣 결제 실패". chr(10)."[" .$REPLYCD . $REPLYMSG . "]";
			$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],'결제실패['.$REPLYCD.']',$log);
		}

		if($_POST['mobilenew'] == "y"){
			pageRedirect('../order/complete?no='.$order_seq,'','parent');
		}else{
			pageRedirect('../order/complete?no='.$order_seq,'','opener');
			echo js("self.close();");
		}
	}

	public function cancel()
	{
		include BASEPATH . "../pg/allat_mobile/allatutil.php";
		$at_cross_key="";  //가맹점 CrossKey값 (치환필요)

		// Set Value
		// -------------------------------------------------------------------
		$at_shop_id="ShopId";         //ShopId값     (최대  20자리)
		$at_order_no="1234567890";    //주문번호     (최대  80자리)
		$at_amt="0";                  //취소금액     (최대  10자리)
		$at_pay_type="CARD";          //원거래건의 결제방식[카드:CARD,계좌이체:ABANK]
		$at_seq_no="12345";           //거래일련번호 (최대  10자리) : 옵션필드임
		$at_test_yn="N";              //테스트 여부
		$at_opt_pin="NOUSE";
		$at_opt_mod="APP";

		// set Enc Data
		// -------------------------------------------------------------------
		$at_enc=setValue($at_enc,"allat_shop_id",$at_shop_id);
		$at_enc=setValue($at_enc,"allat_order_no",$at_order_no);
		$at_enc=setValue($at_enc,"allat_amt",$at_amt);
		$at_enc=setValue($at_enc,"allat_pay_type",$at_pay_type);
		$at_enc=setValue($at_enc,"allat_seq_no",$at_seq_no);
		$at_enc=setValue($at_enc,"allat_test_yn",$at_test_yn);
		$at_enc=setValue($at_enc,"allat_opt_pin",$at_opt_pin);
		$at_enc=setValue($at_enc,"allat_opt_mod",$at_opt_mod);

		// Set Request Data
		//--------------------------------------------------------------------
		$at_data   = "allat_shop_id=".$at_shop_id.
					 "&allat_enc_data=".$at_enc.
					 "&allat_cross_key=".$at_cross_key;

		// 올앳과 통신 후 결과값 받기 : CancelReq->통신함수
		//-----------------------------------------------------------------
		$at_txt=CancelReq($at_data,"SSL");

		// 결과값
		//----------------------------------------------------------------
		$REPLYCD     = getValue("reply_cd",$at_txt);       //결과코드
		$REPLYMSG    = getValue("reply_msg",$at_txt);      //결과 메세지

		// 결과값 처리
		//------------------------------------------------------------------
		if( !strcmp($REPLYCD,"0000") ){
		  // reply_cd "0000" 일때만 성공
		  $CANCEL_YMDHMS=getValue("cancel_ymdhms",$at_txt);
		  $PART_CANCEL_FLAG=getValue("part_cancel_flag",$at_txt);
		  $REMAIN_AMT=getValue("remain_amt",$at_txt);
		  $PAY_TYPE=getValue("pay_type",$at_txt);
		  echo "결과코드: ".$REPLYCD."<br>";
		  echo "결과메세지: ".$REPLYMSG."<br>";
		  echo "취소날짜: ".$CANCEL_YMDHMS."<br>";
		  echo "취소구분: ".$PART_CANCEL_FLAG."<br>";
		  echo "잔액: ".$REMAIN_AMT."<br>";
		  echo "거래방식구분: ".$PAY_TYPE."<br>";
		} else {
		  // reply_cd 가 "0000" 아닐때는 에러 (자세한 내용은 매뉴얼참조)
		  // reply_msg 가 실패에 대한 메세지
		  echo "결과코드: ".$REPLYCD."<br>";
		  echo "결과메세지: ".$REPLYMSG."<br>";
		}

	}


}