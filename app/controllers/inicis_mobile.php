<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class inicis_mobile extends front_base {
	public function __construct(){
		parent::__construct();
		$this->load->helper('order');
		$this->load->helper('shipping');
	}

	public function inicis()
	{
		Header('Content-Type: text/html; charset=EUC-KR');
		$this->load->model('ordermodel');
		$pg = config_load($this->config_system['pgCompany']);
		
		if( isset($pg['mobileCardCompanyCode']) ){
			foreach($pg['mobileCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['mobileCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = $term;
				}
				$codes[] = $code . '-' . implode(':',$terms);
				if($code == 'ALL'){
					$all_noint = implode(':',$terms);
				}
			}			
			$param['inicis_noint_quota'] = implode('^',$codes);			
		}

		if( $all_noint ){
			$codes = array();					
			$arr_tmp =  code_load('inicisCardCompanyCode');
			foreach($arr_tmp as $tmp_code) $arr[] = $tmp_code['codecd'];
			foreach($arr as $code){
				$codes[$code] = $code.'-'.$all_noint;
			}
			$param['inicis_noint_quota'] = implode('^',$codes);
		}
		
		if($pg['mobileInterestTerms']){
			for($i=1;$i<=$pg['mobileInterestTerms'];$i++){
				$arr_max_quota[] = sprintf('%02d',$i);
			}
			$param['inicis_max_quota'] = implode(':',$arr_max_quota);
		}

		$data_order = $this->ordermodel -> get_order($_POST['order_seq']);
		$param['goods_name'] = $_POST['goods_name'];
		$pg_param['quotaopt']  = $pg['mobileInterestTerms'];

		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}
		$param['mallCode'] = $pg['mallCode'];

		foreach($param as $k => $data){
			if(!is_array($data)) $param[$k] = mb_convert_encoding($data, "EUC-KR", "UTF-8");
		}

		foreach($data_order as $k => $data){
			if(!is_array($data)) $data_order[$k] = mb_convert_encoding($data, "EUC-KR", "UTF-8");
		}
		$shopName = mb_convert_encoding($this->config_basic['shopName'], "EUC-KR", "UTF-8");

		$this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir = BASEPATH."../_compile/";
		$this->template->assign("shopName",$shopName);
		$this->template->assign($param);
	    $this->template->assign($data_order);
	    $this->template->define(array('tpl'=>'_inicis_mobile.html'));
	    $this->template->print_('tpl');
	}

	public function _inicis_mobile_writeLog($gubun,$msg,$path)
	{
	    $file = $gubun."_input_".date("Ymd").".log";
		$path = "pg/inicis/log/";
	    if(!($fp = fopen($path.$file, "a+"))) return 0;

	    ob_start();
	    print_r($msg);
	    $ob_msg = ob_get_contents();
	    ob_clean();

	    if(fwrite($fp, " ".$ob_msg."\n") === FALSE)
	    {
	        fclose($fp);
	        return 0;
	    }
	    fclose($fp);
	    return 1;
	}

	## ISP, 계좌이체, 가상계좌를 제외한 모든 지불 수단 사용
	## 가상계좌 채번(계좌발급)일때는 이곳에서 주문 처리
	## ISP일 경우 새창방지 설정으로 inicis_next 호출됨.
	## 단, GET으로  P_TID,P_REQ_URL,P_SATAUS 넘어옴.
	public function inicis_next()
	{
		$this->load->helper('readurl');
		$pg				= config_load($this->config_system['pgCompany']);
		$data['P_TID']	= ($_POST['P_TID'])? $_POST['P_TID']: $_GET['P_TID'];
		$data['P_MID']	= $pg['mallCode'];
		$P_STATUS_R		= ($_POST['P_STATUS'])? $_POST['P_STATUS']: $_GET['P_STATUS'];	// 00 성공, 그외 실패
		$P_REQ_URL		= ($_POST['P_REQ_URL'])? $_POST['P_REQ_URL']: $_GET['P_REQ_URL'];

		$PGIP = $_SERVER['REMOTE_ADDR'];

		$r_out_tmp = array();
		## 결제요청 URL 에서 실결제 정보 읽어오기
		$out = readurl($P_REQ_URL,$data,$binary=false);
		$r_out = explode('&',trim($out));
		foreach($r_out as $tmp){
			$r_tmp					= explode('=',$tmp);
			${$r_tmp[0]}			= $r_tmp[1];
			$r_out_tmp[$r_tmp[0]]	= $r_tmp[1];
		}

		$ordr_idxx		= $P_OID;	//주문번호(위치 옮기지 말것)
		$this->load->model('ordermodel');

		// file log start
		$r_out_tmp['PageCall_time'] = date("H:i:s");
		if($_POST) {
			foreach($_POST as $k=>$v){
				$_POST[$k] = iconv("EUC-KR","UTF-8",$v);
			}
			$this -> _inicis_mobile_writeLog('next',$_POST);
		}
		if($_GET) {
			foreach($_GET as $k=>$v){
				$_GET[$k] = iconv("EUC-KR","UTF-8",$v);
			}
			$this -> _inicis_mobile_writeLog('next',$_POST);
		}
		$this -> _inicis_mobile_writeLog('next',$r_out_tmp);
		## file log end


		// db log start ---------
			$pg_log					= array();
			$pg_log['pg']			= $this->config_system['pgCompany'];
			$pg_log['tno'] 			= $P_TID;
			$pg_log['order_seq'] 	= $P_OID;
			$pg_log['amount'] 		= $P_AMT;
			$pg_log['app_time'] 	= $P_AUTH_DT;
			$pg_log['app_no'] 		= $P_AUTH_NO;
			$pg_log['card_cd'] 		= $P_FN_CD1;
			$pg_log['card_name'] 	= $P_FN_NM;
			$pg_log['biller'] 		= $P_UNAME;

			if ( $P_TYPE == "BANK" )
			{
				$pg_log['bank_name'] 	= $P_FN_NM;
				$pg_log['bank_code'] 	= $P_FN_CD1;

			}elseif ( $P_TYPE == "VBANK" ){

				$arr_bank	= code_load('inicisBankCode',$P_VACT_BANK_CODE);
				$bank_name	= $arr_bank[0]['value'];
				$pg_log['bank_name'] 	= $bank_name;
				$pg_log['bank_code'] 	= $P_VACT_BANK_CODE;	//가상계좌번호
				$pg_log['account'] 		= $P_VACT_NUM;	//가상계좌번호
				$pg_log['depositor'] 	= $P_VACT_NAME;	//계좌주명

			}elseif($P_TYPE == "CARD")
			{
				$pg_log['card_cd'] 		= $P_FN_CD1;
				if(!$P_CARD_PURCHASE_NAME){
					$arr_card	= code_load('inicisCardCompanyCode',$P_FN_CD1);
					$P_CARD_PURCHASE_NAME	= $arr_card[0]['value'];
				}
				$pg_log['card_name'] 	= $P_CARD_PURCHASE_NAME;
				if($P_CARD_ISSUER_NAME) $pg_log['card_name'] .= "(".$P_CARD_ISSUER_NAME.")";
			}

			$pg_log['res_cd'] 		= $P_STATUS;
			$pg_log['res_msg'] 		= $P_RMESG1." :: ".$P_RMESG2;

			foreach($pg_log as $k => $v){
				if($k == "bank_name" || $k == "card_name"){ 
					$pg_log[$k] = trim($v);
				}else{
					$pg_log[$k] = mb_convert_encoding(trim($v),"UTF-8", "EUC-KR");
				}
			}
			$pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);
		// db log end ---------


		// 주문서 정보 가져오기
		if($ordr_idxx){
			$orders = $this->ordermodel->get_order($ordr_idxx);			// 주문서 정보 가져오기
		}
		if(!$orders['order_user_name']) $orders['order_user_name'] = "주문자";

		// 주문 처리
		if( $P_STATUS_R === "00" && $P_STATUS === "00" && $ordr_idxx){

			$this->load->model('cartmodel');
			$this->load->model('membermodel');
			$this->load->model('couponmodel');
			$this->load->model('goodsmodel');

			$runout					= false;	//재고체크 사용안함.

			$result_option			= $this->ordermodel->get_item_option($ordr_idxx);
			$result_suboption		= $this->ordermodel->get_item_suboption($ordr_idxx);
			$data_item_option		= $result_option;

			// 회원 적립금 차감
			if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
			{
				$params = array(
					'gb'		=> 'minus',
					'type'		=> 'order',
					'emoney'	=> $orders['emoney'],
					'ordno'	=> $ordr_idxx,
					'memo'		=> "[차감]주문 ({$ordr_idxx})에 의한 적립금 차감",
				);
				$this->membermodel->emoney_insert($params, $orders['member_seq']);
				$this->ordermodel->set_emoney_use($ordr_idxx,'use');
			}

			// 회원 이머니 차감
			if( $orders['cash'] && $orders['member_seq'] && $orders['cash_use']=='none')
			{
				$params = array(
					'gb'		=> 'minus',
					'type'		=> 'order',
					'cash'	=> $orders['cash'],
					'ordno'	=> $ordr_idxx,
					'memo'		=> "[차감]주문 ({$ordr_idxx})에 의한 이머니 차감",
				);

				$this->membermodel->cash_insert($params, $orders['member_seq']);
				$this->ordermodel->set_cash_use($ordr_idxx,'use');
			}

			//상품쿠폰사용
			if($data_item_option) foreach($data_item_option as $item_option){
				if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
			}

			//배송비쿠폰사용
			if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

			// 장바구니 비우기
			if( $orders['mode'] ){
				$this->cartmodel->delete_mode($orders['mode']);
			}

			// 출고량 업데이트를 위한 변수선언
			$r_reservation_goods_seq = array();
			// 옵션 가용재고 차감
			if($result_option){
				foreach($result_option as $data_option){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_option['goods_seq'];
					}
				}
			}
			// 서브 옵션 가용재고 차감
			if($result_suboption){
				foreach($result_suboption as $data_suboption){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_suboption['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_suboption['goods_seq'];
					}
				}
			}

			//가상계좌일때(주문접수)
			if ( $P_TYPE == "VBANK"){
			
				$virtual_account	=  $bank_name . ' ' . $P_VACT_NUM;
				$virtual_date		=  $P_VACT_DATE;
				$data = array(
						'virtual_account' => $virtual_account,
						'virtual_date' => $va_date,
						'pg_transaction_number' => $tno
				);
			
				$this->ordermodel->set_step($ordr_idxx,15,$data);
				$log = "이니시스 가상계좌 주문접수". chr(10). implode(chr(10),$data);
				$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],'주문접수',$log);
				$mail_step = 15;

			}else{
			//기타 결제 일때(결재완료)

				$data = array(
					'pg_transaction_number' => $P_TID,
					'pg_approval_number' => $P_AUTH_NO
				);
				$this->ordermodel->set_step($ordr_idxx,25,$data);

				$log = "이니시스 결제 확인" . chr(10). implode(chr(10),$data);
				$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],'결제확인',$log);
				$mail_step = 25;

				// 계좌이체 결제의 경우 현금영수증
				if( preg_match('/account/',$orders['payment']) ){
					$result = typereceipt_setting($orders['order_seq']);
				}
			}

			// 출고예약량 업데이트
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}

			$err_msg = "결제 성공하였습니다.";

		}else{

			## 결제 실패
			if($ordr_idxx){
				if ( $P_TYPE == "VBANK"){
					$log		= "이니시스 주문 실패";
					$log_stat	= "주문실패";
				}else{
					$log		= "이니시스 결제 실패";
					$log_stat	= "결제실패";
				}
				$data = array();
				$data['log'] = implode(chr(10),$r_out_tmp);
				$this->ordermodel->set_step($ordr_idxx,99,$data);
				$log .= chr(10). implode(chr(10),$r_out_tmp);
				$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_stat,$log);
			}

			if($P_RMESG1){
				$err_msg .= "결제 실패(취소)하였습니다.(".mb_convert_encoding(trim($P_RMESG1),"UTF-8", "EUC-KR").")";
			}else{
				$err_msg = "결제 취소 하였습니다.";
			}
		}

		pageRedirect('../order/complete?no='.$ordr_idxx,$err_msg,'self');

	}

	# ISP, 계좌이체, 가상계좌(입금통보)만 사용

	public function inicis_rnoti()
	{
		//*******************************************************************************
		// FILE DESCRIPTION :
		// 이니시스 smart phone 결제 결과 수신 페이지 샘플
		// 기술문의 : ts@inicis.com
		// HISTORY
		// 2010. 02. 25 최초작성
		// 2010  06. 23 WEB 방식의 가상계좌 사용시 가상계좌 채번 결과 무시 처리 추가(APP 방식은 해당 없음!!)
		// WEB 방식일 경우 이미 P_NEXT_URL 에서 채번 결과를 전달 하였으므로,
		// 이니시스에서 전달하는 가상계좌 채번 결과 내용을 무시 하시기 바랍니다.
		//*******************************************************************************

		$PGIP = $_SERVER['REMOTE_ADDR'];

		foreach($_POST as $k=>$v){
			$_POST[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
		}

		## file log start
		$_POST['PageCall_time'] = date("H:i:s");
		$_POST['PGIP']			= $PGIP;
		$this -> _inicis_mobile_writeLog('noti',$_POST);
		## file log end


		if($PGIP == "211.219.96.165" || $PGIP == "118.129.210.25")	//PG에서 보냈는지 IP로 체크
		{
			// 이니시스 NOTI 서버에서 받은 Value
			$P_TID;				// 거래번호
			$P_MID;				// 상점아이디
			$P_AUTH_DT;			// 승인일자
			$P_STATUS;			// 거래상태 (00:성공, 01:실패)
			$P_TYPE;			// 지불수단
			$P_OID;				// 상점주문번호
			$P_FN_CD1;			// 금융사코드1
			$P_FN_CD2;			// 금융사코드2
			$P_FN_NM;			// 금융사명 (은행명, 카드사명, 이통사명)
			$P_AMT;				// 거래금액
			$P_UNAME;			// 결제고객성명
			$P_RMESG1;			// 결과코드
			$P_RMESG2;			// 결과메시지
			$P_NOTI;			// 노티메시지(상점에서 올린 메시지)
			$P_AUTH_NO;			// 승인번호

			$P_TID			= $_POST['P_TID'];
			$P_MID			= $_POST['P_MID'];
			$P_AUTH_DT		= $_POST['P_AUTH_DT'];
			$P_STATUS		= $_POST['P_STATUS'];
			$P_TYPE			= $_POST['P_TYPE'];
			$P_OID			= $_POST['P_OID'];
			$P_FN_CD1		= $_POST['P_FN_CD1'];
			$P_FN_CD2		= $_POST['P_FN_CD2'];
			$P_FN_NM		= $_POST['P_FN_NM'];
			$P_AMT			= $_POST['P_AMT'];
			$P_UNAME		= $_POST['P_UNAME'];
			$P_RMESG1		= $_POST['P_RMESG1'];
			$P_RMESG2		= $_POST['P_RMESG2'];
			$P_NOTI			= $_POST['P_NOTI'];
			$P_AUTH_NO		= $_POST['P_AUTH_NO'];


			$ordr_idxx		= $P_OID;
			$this->load->model('ordermodel');
			$this->load->model('cartmodel');
			$this->load->model('membermodel');
			$this->load->model('couponmodel');
			$this->load->model('goodsmodel');
			$pg					= config_load($this->config_system['pgCompany']);
			$orders				= $this->ordermodel->get_order($ordr_idxx);
			$result_option		= $this->ordermodel->get_item_option($ordr_idxx);
			$result_suboption	= $this->ordermodel->get_item_suboption($ordr_idxx);

			if(!$orders['order_user_name']) $orders['order_user_name'] = "주문자";

			if($P_STATUS == "00" || $P_STATUS == "02"){
				$P_RMESG1 = mb_convert_encoding("성공","EUC-KR","UTF-8");
			}else{
				$P_RMESG1 = mb_convert_encoding("실패","EUC-KR","UTF-8");

				## 결제완료 이후 결제실패로 재 호출시 주문상태 변경되지 않도록 처리
				## 결제이후 프로세스 진행 이후에는 결제확인 처리 하지 않음
				if($orders['step'] >= '25' && $orders['step'] <= '85'){ 
					echo "OK";
					exit;
				}
			}

			## ISP는 새창열림 방지 추가로 next 로 리턴 받음 2014-12-31 pjm
			## ISP 결재가 아니거나, ISP 결제이며 결제시도상태일떄 정상주문건으로 돌림.
			if($P_TYPE != "ISP" || ($P_TYPE == "ISP" && $orders['step'] == "0")){

				// db log start -----
				if( ($P_TYPE == "VBANK" && $P_STATUS == "02") || ($P_TYPE != "VBANK")  ){

					$pg_log['pg']			= $this->config_system['pgCompany'];
					$pg_log['tno'] 			= $P_TID;
					$pg_log['order_seq'] 	= $P_OID;
					$pg_log['amount'] 		= $P_AMT;
					$pg_log['app_time'] 	= $P_AUTH_DT;
					$pg_log['app_no'] 		= $P_AUTH_NO;
					$pg_log['biller'] 		= $P_UNAME;
					if ( $P_TYPE == "VBANK" || $P_TYPE == "BANK" )
					{
						$arr_bank	= code_load('inicisBankCode',$P_FN_CD1);
						$bank_name	= $arr_bank[0]['value'];

						$pg_log['bank_name'] 	= $bank_name;
						$pg_log['bank_code'] 	= $P_FN_CD1;
					}
					if($P_TYPE == "ISP")
					{
						$arr_card	= code_load('inicisCardCompanyCode',$P_FN_CD1);
						$card_name	= $arr_card[0]['value'];
						$pg_log['card_cd'] 		= $P_FN_CD1;
						$pg_log['card_name'] 	= $card_name;
					}
					$pg_log['res_cd'] 		= $P_STATUS;
					$pg_log['res_msg'] 		= $P_RMESG1;
					foreach($pg_log as $k => $v){
						if($k == "bank_name" || $k == "card_name"){ 
							$pg_log[$k] = trim($v);
						}else{
							$pg_log[$k] = mb_convert_encoding(trim($v),"UTF-8", "EUC-KR");
						}
					}
					$pg_log['regist_date'] = date('Y-m-d H:i:s');
					$this->db->insert('fm_order_pg_log', $pg_log);
				}
				// db log end -----

				// 주문 처리(가상계좌는 접수시 처리)
				if($P_STATUS == "00" ){

					// 주문서 정보 가져오기
					$orders = $this->ordermodel->get_order($ordr_idxx);

					// 회원 적립금 차감
					if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
					{
						$params = array(
							'gb'		=> 'minus',
							'type'		=> 'order',
							'emoney'	=> $orders['emoney'],
							'memo'		=> "[차감]주문 ({$ordr_idxx})에 의한 적립금 차감",
						);
						$this->membermodel->emoney_insert($params, $orders['member_seq']);
						$this->ordermodel->set_emoney_use($ordr_idxx,'use');
					}

					// 회원 이머니 차감
					if( $orders['cash'] && $orders['member_seq'] && $orders['cash_use']=='none')
					{
						$params = array(
							'gb'		=> 'minus',
							'type'		=> 'order',
							'cash'	=> $orders['cash'],
							'ordno'	=> $ordr_idxx,
							'memo'		=> "[차감]주문 ({$ordr_idxx})에 의한 이머니 차감",
						);

						$this->membermodel->cash_insert($params, $orders['member_seq']);
						$this->ordermodel->set_cash_use($ordr_idxx,'use');
					}

					//상품쿠폰사용
					if($result_option) foreach($result_option as $item_option){
						if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
					}
					//배송비쿠폰사용
					if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

					// 장바구니 비우기
					if( $orders['mode'] ){
						$this->cartmodel->delete_mode($orders['mode']);
					}

				}

				//결제 step 변경(성공코드 : 가상계좌 02, 그외는 00)
				if( ($P_TYPE == "VBANK" && $P_STATUS == "02") || ($P_TYPE != "VBANK" && $P_STATUS == "00")  ){

					$data = array(
						'pg_transaction_number' => $P_TID,
						'pg_approval_number' => $P_AUTH_NO
					);

					if($P_TYPE == "VBANK"){ $orders['order_user_name'] .= "(자동)"; }

					$this->ordermodel->set_step($ordr_idxx,25,$data);
					$log = "이니시스 결제 확인" . chr(10). implode(chr(10),$data);
					$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],'결제확인',$log);
					$mail_step = 25;
					
					// 가상계좌 결제의 경우 현금영수증
					if( $orders['step'] < '25' || $orders['step'] > '85' ){
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
							if(!in_array($data_suboption['goods_seq'],$r_reservation_goods_seq)){
								$r_reservation_goods_seq[] = $data_suboption['goods_seq'];
							}
						}
					}
					
					// 출고예약량 업데이트
					if($r_reservation_goods_seq) foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}

					/***********************************************************************************
					' 위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "OK"를 이니시스로 실패시는 "FAIL" 을
					' 리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
					' (주의) OK를 리턴하지 않으시면 이니시스 지불 서버는 "OK"를 수신할때까지 계속 재전송을 시도합니다
					' 기타 다른 형태의 echo "" 는 하지 않으시기 바랍니다
					'***********************************************************************************/

					if($mail_step == 25){
						// 결제확인메일/sms 발송
						if(  $orders['sms_25_YN'] != 'Y'){
							send_mail_step25($orders['order_seq']);
						}					
						if( $orders['order_cellphone'] && $orders['sms_25_YN'] != 'Y'){
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

					echo "OK"; //절대로 지우지 마세요


				}else{

					//가상계좌 주문접수일때
					if($P_TYPE == "VBANK" && $P_STATUS == "00"){

						echo "OK";

					}else{

						$r_out_tmp['P_TID']		= $P_TID;
						$r_out_tmp['P_MID']		= $P_MID;
						$r_out_tmp['P_TYPE']	= $P_TYPE;
						$r_out_tmp['P_FN_CD1']	= $P_FN_CD1;
						$r_out_tmp['P_FN_CD2']	= $P_FN_CD2;
						$r_out_tmp['P_OID']		= $P_OID;
						$r_out_tmp['P_STATUS']	= $P_STATUS;
						$r_out_tmp['P_RMESG1']	= $P_RMESG1;
						$r_out_tmp['P_RMESG2']	= $P_RMESG2;
						$r_out_tmp['PGIP']		= $PGIP;
						$r_out_tmp['PageCall_time']	= date("H:i:s");
						$r_out_tmp['res']		=  mb_convert_encoding("이니시스 결제 실패1","EUC-KR","UTF-8");
						$r_out_tmp['ordr_idxx'] = $ordr_idxx;
						
						$this -> _inicis_mobile_writeLog('fail_',$r_out_tmp);
						if($ordr_idxx){
							$data			= array();
							$data['log']	= implode(chr(10),$r_out_tmp);
							$this->ordermodel->set_step($ordr_idxx,99,$data);
							$log = $data['log'];
							$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],'결제실패',$log);
						}

						echo "FAIL";	// P_TYPE : ".$P_TYPE." / P_STATUS : ".$P_STATUS;
					}
				}
			}

		}
	}


	## ISP , 계좌이체 등 돌아오는 페이지
	public function popup_return()
	{
		## file log start
		$_GET['PageCall_time']	= date("H:i:s");
		$_GET['PGIP']			= $_SERVER['REMOTE_ADDR'];
		//$this -> _inicis_mobile_writeLog('return',$_GET);
		## file log end
		
		pageRedirect('../order/complete?no='.$_GET['order_seq'],'','self');

	}
}