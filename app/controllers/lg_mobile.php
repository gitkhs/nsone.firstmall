<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class lg_mobile extends front_base {

	public function __construct(){
		parent::__construct();
		$this->load->helper('order');
		$this->load->helper('shipping');
		$this->load->model('ordermodel');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
	}

	public function auth()
	{
		global $pg;
		header("Content-Type: text/html; charset=EUC-KR");
		$pg = get_pg_config($this->config_system['pgCompany'],'mobile');
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['lg_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param	= array_merge($pg_param,$this->pg_param);
		$payment	= str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}

		$data_orders = $this->ordermodel->get_order($_POST['order_seq']);

		$CST_PLATFORM = "service";

		if( $pg['mallCode'] == 'gb_gabiatest01' ) $pg['mallCode'] = "gabiatest01";	//gabia test 
		if( $pg['mallCode'] == 'tgabiatest01' ) $CST_PLATFORM = "test"; //LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
	    $CST_MID                    = $pg['mallCode'];   //상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
	    $LGD_MID                    = $CST_MID;  //상점아이디(자동생성)
	    $LGD_OID                    = $data_orders['order_seq'];           //주문번호(상점정의 유니크한 주문번호를 입력하세요)
	    $LGD_AMOUNT                 = $data_orders['settleprice'];        //결제금액("," 를 제외한 결제금액을 입력하세요)
	    $LGD_BUYER                  = $data_orders['order_user_name'];    //구매자명
	    $LGD_PRODUCTINFO            = $_POST['goods_name'];   			//상품명
	    $LGD_BUYEREMAIL             = $data_orders['order_email'];    	//구매자 이메일
	    $LGD_TIMESTAMP              = date(YmdHms);                         //타임스탬프
	    $LGD_CUSTOM_SKIN            = "blue";                               //상점정의 결제창 스킨 (red, blue, cyan, green, yellow)
		$configPath 				= BASEPATH."../pg/lgdacom"; 		//LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.

	    /*
	     * 가상계좌(무통장) 결제 연동을 하시는 경우 아래 LGD_CASNOTEURL 을 설정하여 주시기 바랍니다.
	     */
	    $LGD_CASNOTEURL				=  "http://".$_SERVER['HTTP_HOST']."/lg_mobile/cas_noteurl";

	    /*
	     * LGD_RETURNURL 을 설정하여 주시기 바랍니다. 반드시 현재 페이지와 동일한 프로트콜 및  호스트이어야 합니다. 아래 부분을 반드시 수정하십시요.
	     */
	    $LGD_RETURNURL				= "http://".$_SERVER['HTTP_HOST']."/pg/lgdacom_mobile/returnurl.php";

	    /*
	     * ISP 카드결제 연동중 모바일ISP방식(고객세션을 유지하지않는 비동기방식)의 경우, LGD_KVPMISPNOTEURL/LGD_KVPMISPWAPURL/LGD_KVPMISPCANCELURL를 설정하여 주시기 바랍니다.
	     */
	    $LGD_KVPMISPNOTEURL       	= "http://".$_SERVER['HTTP_HOST']."/lg_mobile/note_url";
	    $LGD_KVPMISPWAPURL			= "http://".$_SERVER['HTTP_HOST']."/order/complete?no=".$LGD_OID;
	    $LGD_KVPMISPCANCELURL     	= "http://".$_SERVER['HTTP_HOST']."/order/complete?no=".$LGD_OID;


	    require_once(BASEPATH."../pg/lgdacom/XPayClient.php");
	    $xpay = new XPayClient($configPath, $LGD_PLATFORM);
	   	$xpay->Init_TX($LGD_MID);
	    $LGD_HASHDATA = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_TIMESTAMP.$xpay->config[$LGD_MID]);
	    $LGD_CUSTOM_PROCESSTYPE = "TWOTR";
	    /*
	     *************************************************
	     * 2. MD5 해쉬암호화 (수정하지 마세요) - END
	     *************************************************
	     */
		$tpl_param = array(
		    'CST_PLATFORM'=>$CST_PLATFORM,
			'LGD_BUYER'=>$LGD_BUYER,
			'LGD_PRODUCTINFO'=>$LGD_PRODUCTINFO,
			'LGD_AMOUNT'=>$LGD_AMOUNT,
			'LGD_BUYEREMAIL'=>$LGD_BUYEREMAIL,
			'LGD_OID'=>$LGD_OID,
			'CST_MID'=>$CST_MID,
			'LGD_MID'=>$LGD_MID,
			'LGD_CUSTOM_SKIN'=>$LGD_CUSTOM_SKIN,
			'LGD_CUSTOM_PROCESSTYPE'=>$LGD_CUSTOM_PROCESSTYPE,
			'LGD_TIMESTAMP'=>$LGD_TIMESTAMP,
			'LGD_HASHDATA'=>$LGD_HASHDATA,
			'LGD_RETURNURL'=>$LGD_RETURNURL,
			'LGD_CASNOTEURL'=>$LGD_CASNOTEURL,
			'LGD_KVPMISPNOTEURL'=>$LGD_KVPMISPNOTEURL,
			'LGD_KVPMISPWAPURL'=>$LGD_KVPMISPWAPURL,
			'LGD_KVPMISPCANCELURL'=>$LGD_KVPMISPCANCELURL,
			'payment'=>$data_orders['payment']
	    );

		foreach($tpl_param as $k => $data){
			$tpl_param[$k] = mb_convert_encoding($data,'EUC-KR','UTF-8');
		}

	    $this->template->assign($tpl_param);
		$this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_lg_auth.html'));
		$this->template->print_('tpl');
	}

	public function payres()
	{
		$this->load->model('cartmodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		$pg = get_pg_config($this->config_system['pgCompany'],'mobile');
		/*
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['lg_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param = array_merge($pg_param,$this->pg_param);
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}
		*/

		/*
		 * [최종결제요청 페이지(STEP2-2)]
		 *
		 * LG유플러스으로 부터 내려받은 LGD_PAYKEY(인증Key)를 가지고 최종 결제요청.(파라미터 전달시 POST를 사용하세요)
		 */

		$configPath 				= BASEPATH."../pg/lgdacom"; 	//LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf,/conf/mall.conf") 위치 지정.

		/*
		 *************************************************
		 * 1.최종결제 요청 - BEGIN
		 *  (단, 최종 금액체크를 원하시는 경우 금액체크 부분 주석을 제거 하시면 됩니다.)
		 *************************************************
		 */
		$CST_PLATFORM               = $_POST["CST_PLATFORM"];
		$CST_MID                    = $_POST["CST_MID"];
		$LGD_MID                    = $CST_MID;
		$LGD_PAYKEY                 = $_POST["LGD_PAYKEY"];
		$LGD_OID					= $_POST['LGD_OID'];

		require_once(BASEPATH."../pg/lgdacom/XPayClient.php");
		$xpay = new XPayClient($configPath, $CST_PLATFORM);
		$xpay->Init_TX($LGD_MID);

		$xpay->Set("LGD_TXNAME", "PaymentByKey");
		$xpay->Set("LGD_PAYKEY", $LGD_PAYKEY);

		$orders = $this->ordermodel->get_order($LGD_OID);
		if(!$orders['order_user_name']) $orders['order_user_name'] = "주문자";

		if ($xpay->TX()) {

			$LGD_RESPCODE	= $xpay->Response_Code();
			$LGD_RESPMSG	= $xpay->Response("LGD_RESPMSG",0);
			$LGD_TID		= $xpay->Response("LGD_TID",0);
			$LGD_AMOUNT		= $xpay->Response("LGD_AMOUNT",0);

			## 로그저장
	        $pg_log['pg']			= $this->config_system['pgCompany'];
	        $pg_log['res_cd'] 		= $LGD_RESPCODE;
	        $pg_log['res_msg'] 		= $LGD_RESPMSG;
	        $pg_log['order_seq'] 	= $LGD_OID;
	        $pg_log['amount'] 		= $LGD_AMOUNT;
	        $pg_log['tno'] 			= $LGD_TID;
	        $pg_log['app_time'] 	= $xpay->Response("LGD_PAYDATE",0);
	        $pg_log['bank_code'] 	= $xpay->Response("LGD_FINANCECODE",0);
	        $pg_log['bank_name'] 	= $xpay->Response("LGD_FINANCENAME",0);
	        $pg_log['escw_yn'] 		= $xpay->Response("LGD_ESCROWYN",0);
	        $pg_log['quota'] 		= $xpay->Response("LGD_CARDINSTALLMONTH",0);
	        $pg_log['noinf'] 		= $xpay->Response("LGD_CARDNOINTYN",0);
	        $pg_log['card_cd'] 		= $xpay->Response("LGD_FINANCEAUTHNUM",0);
	        $pg_log['biller'] 		= $xpay->Response("LGD_BUYER",0);
	    	foreach($pg_log as $k => $v){
				$v = trim($v);
				$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
			}
	        $pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);

			// 주문서 정보 가져오기
			$orders['mpayment'] = $this->arr_payment[$orders['payment']];

			if( "0000" == $LGD_RESPCODE ) {
				// 주문 상품 재고 체크
				$runout = false;
				$cfg['order'] = config_load('order');

				// 출고량 업데이트를 위한 변수선언 
				$r_reservation_goods_seq = array();
				$result_option		= $this->ordermodel->get_item_option($LGD_OID);
				$result_suboption	= $this->ordermodel->get_item_suboption($LGD_OID);
				$data_item_option	= $result_option;

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

		   		if( $runout == false ){

					// 회원 적립금 차감
					if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
					{
						$params = array(
							'gb'		=> 'minus',
							'type'		=> 'order',
							'emoney'	=> $orders['emoney'],
							'ordno'	=> $LGD_OID,
							'memo'		=> "[차감]주문 ({$LGD_OID})에 의한 적립금 차감",
						);
						$this->membermodel->emoney_insert($params, $orders['member_seq']);
						$this->ordermodel->set_emoney_use($LGD_OID,'use');
					}

					// 회원 이머니 차감
					if( $orders['cash'] && $orders['member_seq'] && $orders['cash_use']=='none')
					{
						$params = array(
							'gb'		=> 'minus',
							'type'		=> 'order',
							'cash'	=> $orders['cash'],
							'ordno'	=> $LGD_OID,
							'memo'		=> "[차감]주문 ({$LGD_OID})에 의한 이머니 차감",
						);

						$this->membermodel->cash_insert($params, $orders['member_seq']);
						$this->ordermodel->set_cash_use($LGD_OID,'use');
					}

					//상품쿠폰사용
					if($data_item_option) foreach($data_item_option as $item_option){
						if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
					}
					//배송비쿠폰사용
					if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

		   			// 장바구니 비우기
					if( $orders['mode'] ) $this->cartmodel->delete_mode($orders['mode']);
		   		}else{
		   			$isDBOK = false;
		   		}

		        if( $LGD_RESPCODE == "0000" && preg_match('/virtual/',$orders['payment']) )
		        {
					$FINANCENAME = mb_convert_encoding($xpay->Response("LGD_FINANCENAME",0), "UTF-8", "EUC-KR");
					$ACCOUNT 	 = $xpay->Response("LGD_ACCOUNTNUM",0);
					$data = array(
						'virtual_account'	=> $FINANCENAME." ".$ACCOUNT,
						'pg_transaction_number' => $tid
					);

					$this->ordermodel->set_step($LGD_OID,15,$data);
					$log = "LGU+ 가상계좌 주문접수". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);
					$add_log = "";
					if($orders['orign_order_seq']) $add_log = "[재주문]";
					if($orders['admin_order']) $add_log = "[관리자주문]";
					if($orders['person_seq']) $add_log = "[개인결제]";
					$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")";
					$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);

					$isDBOK = true;

		        }else if($LGD_RESPCODE == "0000"){

					$data = array('pg_transaction_number' => $LGD_TID);
					$this->ordermodel->set_step($LGD_OID,25,$data);
					$log = "LGU+ 결제 확인". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);
					if( preg_match('/account/',$orders['payment']))
					{
						$LGD_FINANCENAME = $xpay->Response("LGD_FINANCENAME",0);
						$log .= chr(10) . "계좌이체 은행:" . $LGD_FINANCENAME;
					}

					// 계좌이체 결제의 경우 현금영수증
					if( preg_match('/account/',$orders['payment']) && ($orders['step'] < '25' || $orders['step'] > '85') ){
						$result = typereceipt_setting($orders['order_seq']);
					}

					$add_log = "";
					if($orders['orign_order_seq']) $add_log = "[재주문]";
					if($orders['admin_order']) $add_log = "[관리자주문]";
					if($orders['person_seq']) $add_log = "[개인결제]";
					$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
					$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);

	          		$isDBOK = true;
		        }

				// 주문 금액 검증
				if($LGD_AMOUNT != $orders['settleprice']) $isDBOK = false;

				// 해당 주문 상품의 출고예약량 업데이트
				if($LGD_RESPCODE == "0000" && $isDBOK ){
					// 출고예약량 업데이트
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}
				}

			}
		}

		if($LGD_RESPCODE == "S007"){ //중복 결제 값인경우
			if($orders['step'] =='25'){ //이미 결제 확인이 되었다면
				$isDBOK = true;
			}
		}

		if( !$isDBOK ) {
			// 주문취소
			$this->ordermodel->set_step($LGD_OID,99);
			$log = "[" .$LGD_RESPCODE . $LGD_RESPMSG . "] 결제실패";
			$log_title =  '결제실패['.$LGD_RESPCODE.']';
			$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);
		}

		if($_POST['mobilenew'] == 'y'){
			echo("<script>parent.parent.location.href='../order/complete?no=".$LGD_OID."';</script>");
		}else{
			echo("<script>opener.location.href='../order/complete?no=".$LGD_OID."';self.close();</script>");
		}

	}

	## 신용카드, 계좌이체  처리
	public function note_url()
	{
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		$pg = get_pg_config($this->config_system['pgCompany'],'mobile');
		/*
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['lg_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param = array_merge($pg_param,$this->pg_param);
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}
		*/

		/*
	     * 공통결제결과 정보
	     */
	    $LGD_RESPCODE = "";           			// 응답코드: 0000(성공) 그외 실패
	    $LGD_RESPMSG = "";            			// 응답메세지
	    $LGD_MID = "";                			// 상점아이디
	    $LGD_OID = "";                			// 주문번호
	    $LGD_AMOUNT = "";             			// 거래금액
	    $LGD_TID = "";                			// LG유플러스에서 부여한 거래번호
	    $LGD_PAYTYPE = "";            			// 결제수단코드
	    $LGD_PAYDATE = "";            			// 거래일시(승인일시/이체일시)
	    $LGD_HASHDATA = "";           			// 해쉬값
	    $LGD_FINANCECODE = "";        			// 결제기관코드(카드종류/은행코드/이통사코드)
	    $LGD_FINANCENAME = "";        			// 결제기관이름(카드이름/은행이름/이통사이름)
	    $LGD_ESCROWYN = "";           			// 에스크로 적용여부
	    $LGD_TIMESTAMP = "";          			// 타임스탬프
	    $LGD_FINANCEAUTHNUM = "";     			// 결제기관 승인번호(신용카드, 계좌이체, 상품권)

	    /*
	     * 신용카드 결제결과 정보
	     */
	    $LGD_CARDNUM = "";            			// 카드번호(신용카드)
	    $LGD_CARDINSTALLMONTH = "";   			// 할부개월수(신용카드)
	    $LGD_CARDNOINTYN = "";        			// 무이자할부여부(신용카드) - '1'이면 무이자할부 '0'이면 일반할부
	    $LGD_TRANSAMOUNT = "";        			// 환율적용금액(신용카드)
	    $LGD_EXCHANGERATE = "";       			// 환율(신용카드)

	    /*
	     * 휴대폰
	     */
	    $LGD_PAYTELNUM = "";          			// 결제에 이용된전화번호

	    /*
	     * 계좌이체, 무통장
	     */
	    $LGD_ACCOUNTNUM = "";         			// 계좌번호(계좌이체, 무통장입금)
	    $LGD_CASTAMOUNT = "";         			// 입금총액(무통장입금)
	    $LGD_CASCAMOUNT = "";         			// 현입금액(무통장입금)
	    $LGD_CASFLAG = "";            			// 무통장입금 플래그(무통장입금) - 'R':계좌할당, 'I':입금, 'C':입금취소
	    $LGD_CASSEQNO = "";           			// 입금순서(무통장입금)
	    $LGD_CASHRECEIPTNUM = "";     			// 현금영수증 승인번호
	    $LGD_CASHRECEIPTSELFYN = "";  			// 현금영수증자진발급제유무 Y: 자진발급제 적용, 그외 : 미적용
	    $LGD_CASHRECEIPTKIND = "";    			// 현금영수증 종류 0: 소득공제용 , 1: 지출증빙용

	    /*
	     * OK캐쉬백
	     */
	    $LGD_OCBSAVEPOINT = "";       			// OK캐쉬백 적립포인트
	    $LGD_OCBTOTALPOINT = "";      			// OK캐쉬백 누적포인트
	    $LGD_OCBUSABLEPOINT = "";     			// OK캐쉬백 사용가능 포인트

	    /*
	     * 구매정보
	     */
	    $LGD_BUYER = "";              			// 구매자
	    $LGD_PRODUCTINFO = "";        			// 상품명
	    $LGD_BUYERID = "";            			// 구매자 ID
	    $LGD_BUYERADDRESS = "";       			// 구매자 주소
	    $LGD_BUYERPHONE = "";         			// 구매자 전화번호
	    $LGD_BUYEREMAIL = "";         			// 구매자 이메일
	    $LGD_BUYERSSN = "";           			// 구매자 주민번호
	    $LGD_PRODUCTCODE = "";        			// 상품코드
	    $LGD_RECEIVER = "";           			// 수취인
	    $LGD_RECEIVERPHONE = "";      			// 수취인 전화번호
	    $LGD_DELIVERYINFO = "";       			// 배송지

	    $LGD_RESPCODE            = $_POST["LGD_RESPCODE"];
	    $LGD_RESPMSG             = $_POST["LGD_RESPMSG"];
	    $LGD_MID                 = $_POST["LGD_MID"];
	    $LGD_OID                 = $_POST["LGD_OID"];
	    $LGD_AMOUNT              = $_POST["LGD_AMOUNT"];
	    $LGD_TID                 = $_POST["LGD_TID"];
	    $LGD_PAYTYPE             = $_POST["LGD_PAYTYPE"];
	    $LGD_PAYDATE             = $_POST["LGD_PAYDATE"];
	    $LGD_HASHDATA            = $_POST["LGD_HASHDATA"];
	    $LGD_FINANCECODE         = $_POST["LGD_FINANCECODE"];
	    $LGD_FINANCENAME         = $_POST["LGD_FINANCENAME"];
	    $LGD_ESCROWYN            = $_POST["LGD_ESCROWYN"];
	    $LGD_TRANSAMOUNT         = $_POST["LGD_TRANSAMOUNT"];
	    $LGD_EXCHANGERATE        = $_POST["LGD_EXCHANGERATE"];
	    $LGD_CARDNUM             = $_POST["LGD_CARDNUM"];
	    $LGD_CARDINSTALLMONTH    = $_POST["LGD_CARDINSTALLMONTH"];
	    $LGD_CARDNOINTYN         = $_POST["LGD_CARDNOINTYN"];
	    $LGD_TIMESTAMP           = $_POST["LGD_TIMESTAMP"];
	    $LGD_FINANCEAUTHNUM      = $_POST["LGD_FINANCEAUTHNUM"];
	    $LGD_PAYTELNUM           = $_POST["LGD_PAYTELNUM"];
	    $LGD_ACCOUNTNUM          = $_POST["LGD_ACCOUNTNUM"];
	    $LGD_CASTAMOUNT          = $_POST["LGD_CASTAMOUNT"];
	    $LGD_CASCAMOUNT          = $_POST["LGD_CASCAMOUNT"];
	    $LGD_CASFLAG             = $_POST["LGD_CASFLAG"];
	    $LGD_CASSEQNO            = $_POST["LGD_CASSEQNO"];
	    $LGD_CASHRECEIPTNUM      = $_POST["LGD_CASHRECEIPTNUM"];
	    $LGD_CASHRECEIPTSELFYN   = $_POST["LGD_CASHRECEIPTSELFYN"];
	    $LGD_CASHRECEIPTKIND     = $_POST["LGD_CASHRECEIPTKIND"];
	    $LGD_OCBSAVEPOINT        = $_POST["LGD_OCBSAVEPOINT"];
	    $LGD_OCBTOTALPOINT       = $_POST["LGD_OCBTOTALPOINT"];
	    $LGD_OCBUSABLEPOINT      = $_POST["LGD_OCBUSABLEPOINT"];

	    $LGD_BUYER               = $_POST["LGD_BUYER"];
	    $LGD_PRODUCTINFO         = $_POST["LGD_PRODUCTINFO"];
	    $LGD_BUYERID             = $_POST["LGD_BUYERID"];
	    $LGD_BUYERADDRESS        = $_POST["LGD_BUYERADDRESS"];
	    $LGD_BUYERPHONE          = $_POST["LGD_BUYERPHONE"];
	    $LGD_BUYEREMAIL          = $_POST["LGD_BUYEREMAIL"];
	    $LGD_BUYERSSN            = $_POST["LGD_BUYERSSN"];
	    $LGD_PRODUCTCODE         = $_POST["LGD_PRODUCTCODE"];
	    $LGD_RECEIVER            = $_POST["LGD_RECEIVER"];
	    $LGD_RECEIVERPHONE       = $_POST["LGD_RECEIVERPHONE"];
	    $LGD_DELIVERYINFO        = $_POST["LGD_DELIVERYINFO"];

	    $LGD_MERTKEY 			= $pg['merchantKey'];  //LG유플러스에서 발급한 상점키로 변경해 주시기 바랍니다.
	    $LGD_HASHDATA2 			= md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$LGD_MERTKEY);

		if($LGD_RESPCODE == "0000"){
			$res_msg = mb_convert_encoding("성공", "EUC-KR","UTF-8");
		}else{
			$res_msg = mb_convert_encoding("실패", "EUC-KR","UTF-8");
		}

			## 로그저장
	        $pg_log['pg']			= $this->config_system['pgCompany'];
	        $pg_log['res_cd'] 		= $LGD_RESPCODE;
	        $pg_log['res_msg'] 		= $LGD_RESPMSG." ".$res_msg;
	        $pg_log['order_seq'] 	= $LGD_OID;
	        $pg_log['amount'] 		= $LGD_AMOUNT;
	        $pg_log['tno'] 			= $LGD_TID;
	        $pg_log['app_time'] 	= $LGD_PAYDATE;
	        $pg_log['bank_code'] 	= $LGD_FINANCECODE;
	        $pg_log['bank_name'] 	= $LGD_FINANCENAME;
	        $pg_log['escw_yn'] 		= $LGD_ESCROWYN;
	        $pg_log['quota'] 		= $LGD_CARDINSTALLMONTH;
	        $pg_log['noinf'] 		= $LGD_CARDNOINTYN;
	        $pg_log['card_cd'] 		= $LGD_FINANCEAUTHNUM;
	        $pg_log['biller'] 		= $LGD_BUYER;
	    	foreach($pg_log as $k => $v){
				$v = trim($v);
				$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
			}
	        $pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);


	    /*
	     * 상점 처리결과 리턴메세지
	     *
	     * OK   : 상점 처리결과 성공
	     * 그외 : 상점 처리결과 실패
	     *
	     * ※ 주의사항 : 성공시 'OK' 문자이외의 다른문자열이 포함되면 실패처리 되오니 주의하시기 바랍니다.
	     */
	    $resultMSG =  mb_convert_encoding("해쉬값 불일치",'EUC-KR','UTF-8');

	    if ($LGD_HASHDATA2 == $LGD_HASHDATA) {      //해쉬값 검증이 성공하면

			$resultMSG = mb_convert_encoding("결제실패",'EUC-KR','UTF-8');

	        if($LGD_RESPCODE == "0000"){            //결제가 성공이면

				$resultMSG = "OK";

		        // 주문 상품 재고 체크
				$runout = false;
				$cfg['order'] = config_load('order');
				
				// 출고량 업데이트를 위한 변수선언 
				$r_reservation_goods_seq = array();
				$result_option		= $this->ordermodel->get_item_option($LGD_OID);
				$result_suboption	= $this->ordermodel->get_item_suboption($LGD_OID);
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

				// 주문서 정보 가져오기
				$orders = $this->ordermodel->get_order($LGD_OID);

				// 회원 적립금 차감
				if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
				{
					$params = array(
							'gb'		=> 'minus',
							'type'		=> 'order',
							'emoney'	=> $orders['emoney'],
							'ordno'	=> $LGD_OID,
							'memo'		=> "[차감]주문 ({$LGD_OID})에 의한 적립금 차감",
					);
					$this->membermodel->emoney_insert($params, $orders['member_seq']);
					$this->ordermodel->set_emoney_use($LGD_OID,'use');
				}

				// 회원 이머니 차감
				if( $orders['cash'] && $orders['member_seq'] && $orders['cash_use']=='none')
				{
					$params = array(
							'gb'		=> 'minus',
							'type'		=> 'order',
							'cash'	=> $orders['cash'],
							'ordno'	=> $LGD_OID,
							'memo'		=> "[차감]주문 ({$LGD_OID})에 의한 이머니 차감",
					);

					$this->membermodel->cash_insert($params, $orders['member_seq']);
					$this->ordermodel->set_cash_use($LGD_OID,'use');
				}

				//상품쿠폰사용
				if($result_option) foreach($result_option as $item_option){
					if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
				}
				
				//배송비쿠폰사용
				if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

				$data = array('pg_transaction_number' => $LGD_TID);
				$this->ordermodel->set_step($LGD_OID,25,$data);
				$log = "LGU+ 결제 확인". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);
				if( preg_match('/account/',$orders['payment']))
				{
					$log .= chr(10) . "계좌이체 은행:" . $LGD_FINANCENAME;
				}
				
				$add_log = "";
				if($orders['orign_order_seq']) $add_log = "[재주문]";
				if($orders['admin_order']) $add_log = "[관리자주문]";
				if($orders['person_seq']) $add_log = "[개인결제]";
				$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
				$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);

				// 출고예약량 업데이트
				foreach($r_reservation_goods_seq as $goods_seq){
					$this->goodsmodel->modify_reservation_real($goods_seq);
				}

				// 현금영수증
				if( $orders['step'] < '25' || $orders['step'] > '85' ){
					$result = typereceipt_setting($orders['order_seq']);
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
	    }

	    echo $resultMSG;
	}

	# 무통장(가상계좌) : 계좌할당, 입금, 취소 처리
	public function cas_noteurl()
	{
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		$pg = get_pg_config($this->config_system['pgCompany'],'mobile');
		/*
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['lg_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param = array_merge($pg_param,$this->pg_param);
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}
		*/

		/*
	     * [상점 결제결과처리(DB) 페이지]
	     *
	     * 1) 위변조 방지를 위한 hashdata값 검증은 반드시 적용하셔야 합니다.
	     *
	     */
	    $LGD_RESPCODE            = $_POST["LGD_RESPCODE"];             // 응답코드: 0000(성공) 그외 실패
	    $LGD_RESPMSG             = $_POST["LGD_RESPMSG"];              // 응답메세지
	    $LGD_MID                 = $_POST["LGD_MID"];                  // 상점아이디
	    $LGD_OID                 = $_POST["LGD_OID"];                  // 주문번호
	    $LGD_AMOUNT              = $_POST["LGD_AMOUNT"];               // 거래금액
	    $LGD_TID                 = $_POST["LGD_TID"];                  // LG유플러스에서 부여한 거래번호
	    $LGD_PAYTYPE             = $_POST["LGD_PAYTYPE"];              // 결제수단코드
	    $LGD_PAYDATE             = $_POST["LGD_PAYDATE"];              // 거래일시(승인일시/이체일시)
	    $LGD_HASHDATA            = $_POST["LGD_HASHDATA"];             // 해쉬값
	    $LGD_FINANCECODE         = $_POST["LGD_FINANCECODE"];          // 결제기관코드(은행코드)
	    $LGD_FINANCENAME         = $_POST["LGD_FINANCENAME"];          // 결제기관이름(은행이름)
	    $LGD_ESCROWYN            = $_POST["LGD_ESCROWYN"];             // 에스크로 적용여부
	    $LGD_TIMESTAMP           = $_POST["LGD_TIMESTAMP"];            // 타임스탬프
	    $LGD_ACCOUNTNUM          = $_POST["LGD_ACCOUNTNUM"];           // 계좌번호(무통장입금)
	    $LGD_CASTAMOUNT          = $_POST["LGD_CASTAMOUNT"];           // 입금총액(무통장입금)
	    $LGD_CASCAMOUNT          = $_POST["LGD_CASCAMOUNT"];           // 현입금액(무통장입금)
	    $LGD_CASFLAG             = $_POST["LGD_CASFLAG"];              // 무통장입금 플래그(무통장입금) - 'R':계좌할당, 'I':입금, 'C':입금취소
	    $LGD_CASSEQNO            = $_POST["LGD_CASSEQNO"];             // 입금순서(무통장입금)
	    $LGD_CASHRECEIPTNUM      = $_POST["LGD_CASHRECEIPTNUM"];       // 현금영수증 승인번호
	    $LGD_CASHRECEIPTSELFYN   = $_POST["LGD_CASHRECEIPTSELFYN"];    // 현금영수증자진발급제유무 Y: 자진발급제 적용, 그외 : 미적용
	    $LGD_CASHRECEIPTKIND     = $_POST["LGD_CASHRECEIPTKIND"];      // 현금영수증 종류 0: 소득공제용 , 1: 지출증빙용
		$LGD_PAYER     			 = $_POST["LGD_PAYER"];      			// 입금자명

	    /*
	     * 구매정보
	     */
	    $LGD_BUYER               = $_POST["LGD_BUYER"];                // 구매자
	    $LGD_PRODUCTINFO         = $_POST["LGD_PRODUCTINFO"];          // 상품명
	    $LGD_BUYERID             = $_POST["LGD_BUYERID"];              // 구매자 ID
	    $LGD_BUYERADDRESS        = $_POST["LGD_BUYERADDRESS"];         // 구매자 주소
	    $LGD_BUYERPHONE          = $_POST["LGD_BUYERPHONE"];           // 구매자 전화번호
	    $LGD_BUYEREMAIL          = $_POST["LGD_BUYEREMAIL"];           // 구매자 이메일
	    $LGD_BUYERSSN            = $_POST["LGD_BUYERSSN"];             // 구매자 주민번호
	    $LGD_PRODUCTCODE         = $_POST["LGD_PRODUCTCODE"];          // 상품코드
	    $LGD_RECEIVER            = $_POST["LGD_RECEIVER"];             // 수취인
	    $LGD_RECEIVERPHONE       = $_POST["LGD_RECEIVERPHONE"];        // 수취인 전화번호
	    $LGD_DELIVERYINFO        = $_POST["LGD_DELIVERYINFO"];         // 배송지

		$LGD_MERTKEY 			= $pg['merchantKey'];  //LG유플러스에서 발급한 상점키로 변경해 주시기 바랍니다.
	    $LGD_HASHDATA2 			= md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$LGD_MERTKEY);


	    /*
	     * 상점 처리결과 리턴메세지
	     *
	     * OK  : 상점 처리결과 성공
	     * 그외 : 상점 처리결과 실패
	     *
	     * ※ 주의사항 : 성공시 'OK' 문자이외의 다른문자열이 포함되면 실패처리 되오니 주의하시기 바랍니다.
	     */
	    $resultMSG				=  mb_convert_encoding("해쉬값 불일치",'EUC-KR','UTF-8');

	    if ( $LGD_HASHDATA2 == $LGD_HASHDATA ) { //해쉬값 검증이 성공이면

	        if ( "0000" == $LGD_RESPCODE ){ //결제가 성공이면

				$orders				= $this->ordermodel->get_order($LGD_OID);
				$orders['mpayment'] = $this->arr_payment[$orders['payment']];

				$r_reservation_goods_seq = array();
				$result_option		= $this->ordermodel->get_item_option($LGD_OID);
				$result_suboption	= $this->ordermodel->get_item_suboption($LGD_OID);

				## 출고예약량 업데이트 대상 상품번호
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

				if( "R" == $LGD_CASFLAG ) {

	                /*
	                 * 무통장 할당 성공 결과 상점 처리(DB) 부분
	                 * 상점 결과 처리가 정상이면 "OK"
	                 */
	                //if( 무통장 할당 성공 상점처리결과 성공 )
					if(  preg_match('/virtual/',$orders['payment'])){

						$data = array(
							'virtual_account'	=> $LGD_FINANCENAME." ".$LGD_ACCOUNTNUM,
							'pg_approval_number' => $LGD_TID,
							'pg_transaction_number' => $LGD_TID
						);

						$this->ordermodel->set_step($ordr_idxx,15,$data);
						/*
						$log = "LGU+ 가상계좌 주문접수". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);
						$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],'주문접수',$log);
						*/

						// 출고예약량 업데이트
						foreach($r_reservation_goods_seq as $goods_seq){
							$this->goodsmodel->modify_reservation_real($goods_seq);
						}

						// 회원 적립금 차감
						if( $orders['emoney'] && $orders['member_seq'] ){
							$this->ordermodel->set_emoney_use($LGD_OID,'use');
							$this->membermodel->set_member_emoney($orders['emoney'],$orders['member_seq']);
						}

						//상품쿠폰사용
						if($result_option) foreach($result_option as $item_option){
							if($item_option['download_seq']) $this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
						}

						//배송비쿠폰사용
						if($orders['download_seq']) $this->couponmodel->set_download_use_status($orders['download_seq'],'used');

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

						// 주문접수메일발송
						if( $orders['sms_25_YN'] != 'Y' ){
							send_mail_step15($orders['order_seq']);
						}
						*/
						$isDBOK = true;
					}

	                $resultMSG = "OK";

	        	}else if( "I" == $LGD_CASFLAG ) {
	 	            /*
	    	         * 무통장 입금 성공 결과 상점 처리(DB) 부분
	        	     * 상점 결과 처리가 정상이면 "OK"
	            	 */
	            	//if( 무통장 입금 성공 상점처리결과 성공 )
	 	            $this->ordermodel->set_step($LGD_OID,25,$data);
					$log		= "LGU+ 결제 확인". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);
					$log_title	= "결제확인"."(".$orders['mpayment'].")";
					$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);

					// 출고예약량 업데이트
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}

					// 결제확인 sms발송
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

					// 결제확인메일발송
					if( $orders['sms_25_YN'] != 'Y' ){
						send_mail_step25($orders['order_seq']);
					}

	            	$resultMSG = "OK";

	        	}else if( "C" == $LGD_CASFLAG ) {
	 	            /*
	    	         * 무통장 입금취소 성공 결과 상점 처리(DB) 부분
	        	     * 상점 결과 처리가 정상이면 "OK"
	            	 */
	            	//if( 무통장 입금취소 성공 상점처리결과 성공 )
					// 주문취소
					$this->ordermodel->set_step($LGD_OID,85);
					$log				= "[" .$LGD_RESPCODE . $LGD_RESPMSG . "] 입금취소";
					$log_title			= "입금취소"."(".$orders['mpayment'].")";
					$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);

	            	$resultMSG = "OK";
	        	}

	        } else { //결제가 실패이면
	            /*
	             * 거래실패 결과 상점 처리(DB) 부분
	             * 상점결과 처리가 정상이면 "OK"
	             */
	            //if( 결제실패 상점처리결과 성공 )
	            $resultMSG = "OK";
	        }
	    } else { //해쉬값이 검증이 실패이면
	        /*
	         * hashdata검증 실패 로그를 처리하시기 바랍니다.
	         */
	        $resultMSG =  mb_convert_encoding("결제결과 상점 DB처리(LGD_CASNOTEURL) 해쉬값 검증이 실패하였습니다.",'EUC-KR','UTF-8');
	    }

	    echo $resultMSG;
	}
}