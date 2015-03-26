<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//현금영수증
class Cashtax
{
	var $conf	= array();
	var $datas	= array();
	var $infos	= array();
	var $payObj	= NULL;
	var $tax_cd	= NULL;

	function __construct() {
		$CI =& get_instance();
		if( $CI->config_system['pgCompany'] ){
			$this->pgconf = config_load($CI->config_system['pgCompany']);
			$this->pgconf['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');
			$this->orderconf = config_load('order');
			$this->pgconf['creceipt_type'] = ($this->orderconf['biztype'] == 'tax' )?1:0;
		}
	}


	function getCashTax ($taxParamMode, $data)
	{
		$CI =& get_instance();
		foreach($data as $k => $param_cash){
			//$param_cash = mb_covert_encoding($param_cash,'EUC-KR','UTF-8');
			if($CI->config_system['pgCompany'] != "kcp"  && $CI->config_system['pgCompany'] != "inicis" && $CI->config_system['pgCompany'] != "kspay"){
				$param_cash	= @iconv('EUC-KR','UTF-8',$param_cash);
			}
			$data[$k] = $param_cash;
		}

		switch ($CI->config_system['pgCompany'])
		{
			case 'kcp' :
				return $this->getKcp($taxParamMode, $data);
				break;
			case 'inicis' :
				return $this->getInicis($taxParamMode, $data);
				break;
			case 'lg' :
				return $this->getDacom($taxParamMode, $data);
				break;
			case 'allat' :
				return $this->getAllat($taxParamMode, $data);
				break;
			case 'kspay' :
				return $this->getKspay($taxParamMode, $data);
				break;
		}
	}

	function getInfo($name) { return $this->infos[$name]; }

	function getKspay($taxParamMode, $data){
		
		require_once dirname(__FILE__)."/../../pg/kspay/KSPayEncApprovalCancel.inc"; // library [수정불가]

        //$at_amt_tax	= ($this->pgconf['creceipt_type'] == 1) ? (int)($data['price']*(10/110)) : '0';
        //$at_amt_sup	= ($this->pgconf['creceipt_type'] == 1) ? ($data['price']-$at_amt_tax) : $data['price'];

		//$at_amt_tax	= $this->datas['surtax'];
        //$at_amt_sup	= $this->datas['supply'];

		$at_amt_tax	= $data['surtax'];
        $at_amt_sup	= $data['supply'];



		$KSPAY_IPADDR = "210.181.28.137";//운영:210.181.28.137
		$KSPAY_PORT   = 21001;

		// Default(수정항목이 아님)-------------------------------------------------------
			$EncType       = "2";			     	// 0: 암화안함, 1:openssl, 2: seed
			$Version       = "0603";				// 현금영수증은 전문버전을 0311이전으로 하면 안됩니다.
			$VersionType   = "00";					// 구분
			$Resend        = "0";					// 전송구분 : 0 : 처음,  2: 재전송
			// 요청일자 : yyyymmddhhmmss
			$RequestDate   = strftime("%Y%m%d%H%M%S");
			$KeyInType     = "K"	;				    // KeyInType 여부 : S : Swap, K: KeyInType
			$LineType      = "1";			            // lineType 0 : offline, 1:internet, 2:Mobile
			$ApprovalCount = "1";				        // 복합승인갯수
			$GoodType      = "0";	                    // 제품구분 0 : 실물, 1 : 디지털
			$HeadFiller    = "";				        // 예비
		//-------------------------------------------------------------------------------


		// Header (입력값 (*) 필수항목)--------------------------------------------------
			$StoreId     = $data["mallId"];     // *상점아이디
			$OrderNumber = $data["order_seq"]; // *주문번호
			$UserName    = $data["person"];   // *주문자명
			$IdNum       = $data["creceipt_number"];       // 주민번호 or 사업자번호 or 핸드폰번호
			$Email       = $data["email"];       // *email
			$GoodName    = $data["goodsname"];    // *제품명
			$PhoneNo     = $data["phone"];     // *휴대폰번호

		// Header end -------------------------------------------------------------------
                                
		// Data Default(수정항목이 아님)-------------------------------------------------
			$CallCode      = "0";										//통화코드  (0: 원화, 1: 미화)
			$Filler        = "0";										//예비
				
		// Data Default end -------------------------------------------------------------

		// Data (입력값 (*) 필수항목)----------------------------------------------------
			$ApprovalType  = "H000";						//H000:일반발급, H200:계좌이체, H600:가상계좌
			if		($data['payment'] == 'account'){
				$ApprovalType  = "H200";
			}elseif	($data['payment'] == 'virtual'){
				$ApprovalType  = "H600";
			}
			//중복체크 없이 발급희망시 "2"로 변경하면 됨
			$IssuSele      = "1";			//0:가상계좌, 계좌이체발급(PG원거래번호 중복체크), 1:일반발급(주문번호 중복체크 : PG원거래 없음), 2:강제발급(중복체크 안함)
			
			if ($IssuSele != "2" && substr($ApprovalType,0,2) != "H0")
			{
				$IssuSele      = "0";	//가상계좌, 계좌이체요청이므로 0으로 세팅
			}
			else if ($IssuSele != "2")
			{
				$IssuSele      = "1"; 	//일반요청이므로 이므로 1로 세팅
			}
			else 
			{
				$IssuSele      = "2";	//강제발급으로 세팅
			}

            $at_supply_amt			= $at_amt_sup;                                        //공급가액
            $at_vat_amt				= $at_amt_tax;                                           //VAT금액
            $at_apply_ymdhms		= date("YmdHis");                                   //거래요청일자
            $at_shop_member_id		= $data['mid'];                                   //회원ID
            $at_cert_no				= $data['creceipt_number'];                              //인증정보
            $at_product_nm			= $data['goodsname'];                                 //상품명
            $at_receipt_type		= $at_rep_type;                                     //현금영수증구분


			$TransactionNo = $data['pg_transaction_number'];//입금완료된 계좌이체, 가상계좌 거래번호
			$UserInfoSele  = (strlen($IdNum) == 10) ? "1" : "0";			//0:주민등록번호, 1:사업자번호, 2:카드번호, 3:휴대폰번호, 4:기타
			$UserInfo      = $IdNum;										//주민등록번호, 사업자번호, 카드번호, 휴대폰번호, 기타
			$TranSele      = $data["cuse"];;	//발행구분: 0:소득공제용(개인), 1: 지출증빙용(사업자)
			$SupplyAmt     = $at_amt_sup;					//공급가액
			$TaxAmt        = $at_amt_tax;						//세금
			$SvcAmt        = 0;						//봉사료
			$TotAmt        = $data["price"];						//현금영수증 발급금액
			
			if (strlen($IdNum) == 10 && substr($IdNum,0,1) != "0")  	// 사업자번호
			{
				$UserInfoSele = "1";
			}
			else if (strlen($IdNum) == 13)  	// 주민등록번호
			{
				$UserInfoSele = "0";
			}
			else 	// 휴대폰번호
			{
				$UserInfoSele = "3";
			}

		// Data end ---------------------------------------------------------------------

		// 승인거절 응답-----------------------------------------------------------------
		// Server로 부터 응답이 없을시 자체응답
			$rApprovalType		= (strlen(ApprovalType) == 4) ? substr(ApprovalType,0,3) . "1" : $ApprovalType;
			$rTransactionNo		= "";
			$rStatus			= "X";
			$rCashTransactionNo	= "";
			$rIncomeType		= "";
			$rTradeDate			= "";
			$rTradeTime			= "";
			$rMessage1			= "발급거절";
			$rMessage2			= "B잠시후재시도";
			$rCashMessage1		= "";
			$rCashMessage2		= "";
			$rFiller			= "";
		// --------------------------------------------------------------------------------
			
			$ipg = new KSPayEncApprovalCancel($KSPAY_IPADDR, $KSPAY_PORT);
			
			// 전문에서 한글이 차지하는 길이 문제로 euc-kr로 함.
			$ipg->HeadMessage(
				iconv('UTF-8', 'EUC-KR', $EncType),			// 0: 암화안함, 1:openssl, 2: seed
				iconv('UTF-8', 'EUC-KR', $Version),			// 전문버전
				iconv('UTF-8', 'EUC-KR', $VersionType),		// 구분
				iconv('UTF-8', 'EUC-KR', $Resend),			// 전송구분 : 0 : 처음,  2: 재전송
				iconv('UTF-8', 'EUC-KR', $RequestDate),		// 재사용구분
				iconv('UTF-8', 'EUC-KR', $StoreId),			// 상점아이디
				iconv('UTF-8', 'EUC-KR', $OrderNumber),		// 주문번호
				iconv('UTF-8', 'EUC-KR', $UserName),		// 주문자명
				iconv('UTF-8', 'EUC-KR', $IdNum),			// 주민번호 or 사업자번호
				iconv('UTF-8', 'EUC-KR', $Email),			// email
				iconv('UTF-8', 'EUC-KR', $GoodType),		// 제품구분 0 : 실물, 1 : 디지털
				iconv('UTF-8', 'EUC-KR', $GoodName),		// 제품명
				iconv('UTF-8', 'EUC-KR', $KeyInType),		// KeyInType 여부 : S : Swap, K: KeyInType
				iconv('UTF-8', 'EUC-KR', $LineType),		// lineType 0 : offline, 1:internet, 2:Mobile
				iconv('UTF-8', 'EUC-KR', $PhoneNo),			// 휴대폰번호
				iconv('UTF-8', 'EUC-KR', $ApprovalCount),	// 복합승인갯수
				iconv('UTF-8', 'EUC-KR', $HeadFiller) );	// 예비

		// ------------------------------------------------------------------------------
			$ipg->CashBillDataMessage(
				iconv('UTF-8', 'EUC-KR', $ApprovalType)  ,//H000:일반발급, H200:계좌이체, H600:가상계좌
				iconv('UTF-8', 'EUC-KR', $TransactionNo) ,//입금완료된 계좌이체, 가상계좌 거래번호
				iconv('UTF-8', 'EUC-KR', $IssuSele)      ,//0:일반발급(PG원거래번호 중복체크), 1:단독발급(주문번호 중복체크 : PG원거래 없음), 2:강제발급(중복체크 안함)
				iconv('UTF-8', 'EUC-KR', $UserInfoSele)  ,//0:주민등록번호, 1:사업자번호, 2:카드번호, 3:휴대폰번호, 4:기타
				iconv('UTF-8', 'EUC-KR', $UserInfo)      ,//주민등록번호, 사업자번호, 카드번호, 휴대폰번호, 기타
				iconv('UTF-8', 'EUC-KR', $TranSele)      ,//0: 개인, 1: 사업자
				iconv('UTF-8', 'EUC-KR', $CallCode)      ,//통화코드  (0: 원화, 1: 미화)
				iconv('UTF-8', 'EUC-KR', $SupplyAmt)     ,//공급가액
				iconv('UTF-8', 'EUC-KR', $TaxAmt)        ,//세금
				iconv('UTF-8', 'EUC-KR', $SvcAmt)        ,//봉사료
				iconv('UTF-8', 'EUC-KR', $TotAmt)        ,//현금영수증 발급금액
				iconv('UTF-8', 'EUC-KR', $Filler));		  //예비

			if ($ipg->SendEncSocket()) {
				
				$rApprovalType		= $ipg->ApprovalType			; // 승인구분 코드          
				$rTransactionNo		= $ipg->HTransactionNo	 		; // 거래번호               
				$rStatus			= $ipg->HStatus			 		; // 오류구분 O:정상 X:거절 
				$rCashTransactionNo	= $ipg->HCashTransactionNo		; // 현금영수증 거래번호    
				$rIncomeType		= $ipg->HIncomeType		 		; // 0: 소득      1: 비소득 
				$rTradeDate			= $ipg->HTradeDate		 		; // 거래 개시 일자         
				$rTradeTime			= $ipg->HTradeTime		 		; // 거래 개시 시간         
				$rMessage1			= $ipg->HMessage1		 		; // 응답 message1          
				$rMessage2			= $ipg->HMessage2		 		; // 응답 message2          
				$rCashMessage1		= $ipg->HCashMessage1	 		; // 국세청 메시지 1        
				$rCashMessage2		= $ipg->HCashMessage2	 		; // 국세청 메시지 2        
				$rFiller			= $ipg->HFiller			 		; // 예비                   
			}


			if($rStatus == "O"){
				$result['cash_no'	]	= $rTransactionNo;
				$result['receipt_no']	= $rCashTransactionNo;
				$result['app_time'	]	= $rTradeDate;
			}else{
				$result = "";
			}

			return $result;
	}

	function setKcpInfo()
	{

		$info['ip'		]	= getenv('REMOTE_ADDR');
		$info['dir'		]	= $_SERVER['DOCUMENT_ROOT'] . '/pg/kcp';
		$info['url'		]	= 'paygw.kcp.co.kr';				// ※ 테스트: testpaygw.kcp.co.kr, 리얼: paygw.kcp.co.kr
		$info['cdPay'	]	= '07010000';
		$info['cdCan']	= '07020000';
		$info['cdInq'	]	= '07030000';
		$info['port'	]	= '8080';							// ※ 테스트: 8090,	리얼: 8080
		$info['type'	]	= 'PGNW';
		$info['level'	]	= 3;
		$info['mode'	]	= 0;
		$info['id'		]	= $this->pgconf['mallCode'];
		$info['key'	]	= $this->pgconf['merchantKey'];
		$this->infos		= $info;
	}


	function getKcp($taxParamMode, $data)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/pg/kcp/class.pp_ax_hub_lib.php';

		$this->payObj	= new C_PAYPLUS_CLI;
		$this->datas	= $data;
		$this->payObj->mf_clear();
		$this->setKcpInfo();

		switch($taxParamMode)
		{
			case 'pay' :
				$return	= $this->setKcpInsertTax();
				break;
			case 'mod' :
				$return	= $this->setKcpCancelTax();
				break;
			default :
				$return	= 3;
				break;
		}
		$this->payObj->mf_clear();

		return $return;
	}

    function getAllat($taxParamMode, $data)
    {
		$configPath				= $_SERVER['DOCUMENT_ROOT'] . "/pg/allat";
		require_once $configPath . "/allatutil.php";

        //$at_amt_tax	= ($this->pgconf['creceipt_type'] == 1) ? (int)($data['price']*(10/110)) : '0';
        //$at_amt_sup	= ($this->pgconf['creceipt_type'] == 1) ? ($data['price']-$at_amt_tax) : $data['price'];
        $at_amt_tax	= $data['surtax'];
        $at_amt_sup	= $data['supply'];

        $at_shop_id		= $this->pgconf['mallCode'];                                //상점ID
        $at_cross_key	= $this->pgconf['merchantKey'];                       //상점CrossKey
        $at_test_yn		= 'N';                                                       //테스트 여부

        $at_rep_type	= "";
        switch ($data['settlekind']) {
            case 'b': $at_rep_type = "NBANK"; break;							// 무통장
            case 'o': $at_rep_type = "ABANK"; break;							// 실시간 계좌이체
            case 'v': $at_rep_type = "VBANK"; break;							// 가상계좌
        }

        if($taxParamMode == "pay") {
            $at_supply_amt			= $at_amt_sup;                                        //공급가액
            $at_vat_amt				= $at_amt_tax;                                           //VAT금액
            $at_apply_ymdhms		= date("YmdHis");                                   //거래요청일자
            $at_shop_member_id	= $data['mid'];                                   //회원ID
            $at_cert_no					= $data['creceipt_number'];                              //인증정보
            $at_product_nm			= $data['goodsname'];         //상품명
            $at_receipt_type			= $at_rep_type;                                     //현금영수증구분
            $at_seq_no					= $data['pgcode'];                                        //거래일련번호(계좌이체 일 때 필수 필드)

            //set Enc Data
            $at_enc=setValue($at_enc,"allat_shop_id",$at_shop_id);
            $at_enc=setValue($at_enc,"allat_supply_amt",$at_supply_amt);
            $at_enc=setValue($at_enc,"allat_vat_amt",$at_vat_amt);
            $at_enc=setValue($at_enc,"allat_apply_ymdhms",$at_apply_ymdhms);
            $at_enc=setValue($at_enc,"allat_shop_member_id",$at_shop_member_id);
            $at_enc=setValue($at_enc,"allat_cert_no",$at_cert_no);
            $at_enc=setValue($at_enc,"allat_product_nm", @iconv('utf-8','euc-kr', $at_product_nm));
            $at_enc=setValue($at_enc,"allat_receipt_type",$at_receipt_type);
            $at_enc=setValue($at_enc,"allat_seq_no",$at_seq_no);
            $at_enc=setValue($at_enc,"allat_test_yn",$at_test_yn);

            //set Request Data
            $at_data   = "allat_shop_id=".$at_shop_id .
                         "&allat_supply_amt=".$at_supply_amt.
                         "&allat_vat_amt=".$at_vat_amt.
                         "&allat_enc_data=".$at_enc.
                         "&allat_cross_key=".$at_cross_key;

            //올앳 결제 서버와 통신 : CashAppReq->통신함수, $at_txt->결과값
            $at_txt = CashAppReq($at_data,"SSL");

            //현금영수증 신청 결과 값 확인
            $REPLYCD		=getValue("reply_cd",$at_txt);                           //결과코드
            $REPLYMSG		=getValue("reply_msg",$at_txt);                          //결과메세지

            if( !strcmp($REPLYCD,"0000") ) {
                $APPROVAL_NO  =getValue("approval_no",$at_txt);                 //승인번호
                $CASH_BILL_NO =getValue("cash_bill_no",$at_txt);                //현금영수증 일련번호

                $return['cash_no'	]		= $CASH_BILL_NO;
                $return['receipt_no']	= $APPROVAL_NO;
                $return['reg_stat']		= $REPLYCD;
                $return['reg_desc ']		= $REPLYMSG;
            }
        } else if($taxParamMode == "mod") {
            $at_cash_bill_no		= $data['cash_no'];                             //현금영수증 일련 번호
            $at_supply_amt		= $at_amt_sup;                                        //공급가액
            $at_vat_amt			= $at_amt_tax;                                           //VAT금액
            $at_opt_pin				= 'NOVIEW';                                             //올앳참조필드
            $at_opt_mod			= 'WEB';                                                //올앳참조필드

            //set Enc Data
            $at_enc=setValue($at_enc,"allat_shop_id",$at_shop_id);
            $at_enc=setValue($at_enc,"allat_cash_bill_no",$at_cash_bill_no);
            $at_enc=setValue($at_enc,"allat_supply_amt",$at_supply_amt);
            $at_enc=setValue($at_enc,"allat_vat_amt",$at_vat_amt);
            $at_enc=setValue($at_enc,"allat_opt_pin",$at_opt_pin);
            $at_enc=setValue($at_enc,"allat_opt_mod",$at_opt_mod);
            $at_enc=setValue($at_enc,"allat_test_yn",$at_test_yn);

            //set Request Data
            $at_data   = "allat_shop_id=".$at_shop_id .
                         "&allat_enc_data=".$at_enc.
                         "&allat_cross_key=".$at_cross_key;

            //올앳 결제 서버와 통신 : CashCanReq->통신함수, $at_txt->결과값
            $at_txt = CashCanReq($at_data,"SSL");

            //현금영수증 취소 결과 값 확인
            $REPLYCD   =getValue("reply_cd",$at_txt);                           //결과코드
            $REPLYMSG  =getValue("reply_msg",$at_txt);                          //결과메세지

            if( !strcmp($REPLYCD,"0000") ) {
                $CANCEL_YMDHMS		=getValue("cancel_ymdhms",$at_txt);           //취소일시
                $PART_CANCEL_FLAG	=getValue("part_cancel_flag",$at_txt);        //취소여부
                $REMAIN_AMT				=getValue("remain_amt",$at_txt);              //잔액

                $return['reg_stat']		= $REPLYCD;
                $return['reg_desc ']		= $REPLYMSG."취소";
            }
        } else {
            $return = 3;
        }

        return $return;
    }

	function getDacom($mode, $data)
	{
		GLOBAL $cfg;

		$trad_time		= ereg_replace("[^0-9]", "", $data['paydt']);
//		$amt_tax		= ($this->pgconf['creceipt_type'] == 1) ? ($data['price']*(10/110)) : '0';
//		$amt_sup		= ($this->pgconf['creceipt_type'] == 1) ? ($data['price']-$amt_tax) : $data['price'];
        $amt_tax	= $data['surtax'];
        $amt_sup	= $data['supply'];

		switch ($mode)
		{
			case 'pay' :
				$LGD_METHOD	= 'AUTH';
				break;
			case 'mod' :
				$LGD_METHOD	= 'CANCEL';
				break;
		}

		$CST_PLATFORM			= 'service';//'service';
		$LGD_MID				= (("test" == $CST_PLATFORM)?"t":"").$this->pgconf['mallCode'];

		$LGD_OID				= $data['order_seq'];				//주문번호(상점정의 유니크한 주문번호를 입력하세요)
		
		/*
		switch($data['payment']) {
			case 'virtual' : $LGD_PAYTYPE = 'SC0040'; $LGD_CASSEQNO = '001'; break;
			case 'escrow_virtual' : $LGD_PAYTYPE = 'SC0040'; $LGD_CASSEQNO = '001'; break;
			case 'account' : $LGD_PAYTYPE = 'SC0030'; break;
			case 'escrow_account' : $LGD_PAYTYPE = 'SC0030'; break;			
			default : $LGD_PAYTYPE = 'SC0100'; break;
		}
		*/
		$LGD_PAYTYPE = 'SC0100';

		//$LGD_PAYTYPE								= ($data["pgcode"]) ? 'SC0030' : 'SC0100';
		$LGD_AMOUNT	= $data["price"];				//금액("," 를 제외한 금액을 입력하세요)
		$LGD_VAT	= $amt_tax;
		
		$LGD_TAXFREEAMOUNT = 0;
		$free_supply = $data['supply'] - $data['surtax']*11;
		if($free_supply > 0 ) $LGD_TAXFREEAMOUNT =  $free_supply;

		$LGD_CASHCARDNUM					= $data["creceipt_number"];		//발급번호(주민등록번호,현금영수증카드번호,휴대폰번호 등등)
		$LGD_CUSTOM_BUSINESSNUM	= ereg_replace("[^0-9]", "", $cfg['company']['co_regno']);	//사업자등록번호
		$LGD_CASHRECEIPTUSE				= ($data['cuse']+1);			//현금영수증발급용도('1':소득공제, '2':지출증빙)
		$LGD_PRODUCTINFO					= $data["goodsname"];			//상품명
		$LGD_TID										= $data["pgcode"];				//데이콤 거래번호
		$configPath									= $_SERVER['DOCUMENT_ROOT'] . "/pg/lgdacom";

		require_once $configPath . "/XPayClient.php";
		$xpay	= new XPayClient($configPath, $this->pgconf['merchantKey'], $LGD_MID, $CST_PLATFORM);
		$xpay->Init_TX($LGD_MID);
		$xpay->Set("LGD_TXNAME", "CashReceipt");
		$xpay->Set("LGD_METHOD", $LGD_METHOD);
		$xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);

		// 현금영수증 발급 요청
		if ($LGD_METHOD == "AUTH")
		{
			$xpay->Set("LGD_OID", $LGD_OID);
			$xpay->Set("LGD_AMOUNT", $LGD_AMOUNT);
			$xpay->Set("LGD_TAXFREEAMOUNT", $LGD_TAXFREEAMOUNT);
			$xpay->Set("LGD_VAT", $LGD_VAT);			
			$xpay->Set("LGD_CASHCARDNUM", $LGD_CASHCARDNUM);
			$xpay->Set("LGD_CUSTOM_BUSINESSNUM", $LGD_CUSTOM_BUSINESSNUM);
			$xpay->Set("LGD_CASHRECEIPTUSE", $LGD_CASHRECEIPTUSE);

			if ($LGD_PAYTYPE == "SC0030"){				//기결제된 계좌이체건 현금영수증 발급요청시 필수
				$xpay->Set("LGD_TID", $LGD_TID);
			}
			else if ($LGD_PAYTYPE == "SC0040"){			//기결제된 가상계좌건 현금영수증 발급요청시 필수
				$xpay->Set("LGD_TID", $LGD_TID);
				$xpay->Set("LGD_SEQNO", "001");
			}
			else {										//무통장입금 단독건 발급요청
				$xpay->Set("LGD_PRODUCTINFO", $LGD_PRODUCTINFO);
			}
		}
		else
		{
			// 현금영수증 취소 요청
			$xpay->Set("LGD_TID", $data["cash_no"]);
			if ($LGD_PAYTYPE == "SC0040"){				//가상계좌건 현금영수증 발급취소시 필수
				$xpay->Set("LGD_SEQNO", "001");
			}
		}

		if ($xpay->TX())
		{
			if ($xpay->Response("LGD_RESPCODE", 0) == '0000')
			{
				$return['cash_no'	]		= $xpay->Response("LGD_TID",0);
				$return['receipt_no']	= $xpay->Response_Code();
				$return['app_time'	]	= $xpay->Response("LGD_RESPDATE",0);
				return $return;
			}
			else if ($xpay->Response("LGD_RESPCODE", 0) == '1111')
			{
				$return['cash_no'	]		= $LGD_TID;
				$return['receipt_no']	= '0000';
				$return['app_time'	]	= $xpay->Response("LGD_RESPDATE",0);
				return $return;
			}
			return $xpay->Response_Msg();
		}
	}

	function setKcpInsertTax()
	{
		setlocale(LC_CTYPE, 'ko_KR.euc-kr');
		$trad_time	= ereg_replace("[^0-9]", "", date("Y-m-d H:i:s"));
		//$amt_tax	= ($this->pgconf['creceipt_type'] == 1) ? (int)($this->datas['price']*(10/110)) : '0';
		//$amt_sup	= ($this->pgconf['creceipt_type'] == 1) ? ($this->datas['price']-$amt_tax) : $this->datas['price'];
        $amt_tax	= $this->datas['surtax'];
        $amt_sup	= $this->datas['supply'];

		$rcpt_set[]	= $this->payObj->mf_set_data_us("user_type",$this->getInfo('type')				);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("trad_time",$trad_time							);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("tr_code",	$this->datas['cuse'				]	);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("id_info",	$this->datas['creceipt_number'	]	);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("amt_tot",	$this->datas['price'			]	);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("amt_sup",	$amt_sup							);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("amt_svc",	'0'									);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("amt_tax",	$amt_tax							);
		$rcpt_set[]	= $this->payObj->mf_set_data_us("pay_type",	"PAXX"								);
		$rcpt_set	= implode('', $rcpt_set);
		$corp_set	= $this->payObj->mf_set_data_us("corp_type", '0');
		$this->datas['goodsname'] = str_replace(",", "", $this->datas['goodsname']);
		$this->payObj->mf_set_ordr_data("ordr_idxx",	$this->datas['order_seq'		]		);
		$this->payObj->mf_set_ordr_data("good_name",	mb_convert_encoding($this->datas['goodsname'], 'EUC-KR', "UTF-8"));
		if($this->datas['person']){
			$this->payObj->mf_set_ordr_data("buyr_name",	mb_convert_encoding($this->datas['person'], 'EUC-KR', "UTF-8")	);
		}else{
			$this->payObj->mf_set_ordr_data("buyr_name",	mb_convert_encoding($this->datas['name'], 'EUC-KR', "UTF-8")		);
		}
		$this->payObj->mf_set_ordr_data("buyr_tel1",	$this->datas['phone'	]		);
		$this->payObj->mf_set_ordr_data("buyr_mail",	$this->datas['email'	]		);
		$this->payObj->mf_set_ordr_data("comment",		NULL								);

		$this->payObj->mf_set_ordr_data("rcpt_data",	$rcpt_set);
		$this->payObj->mf_set_ordr_data("corp_data",	$corp_set);
		return $this->setKcpCashTaxGlobal($this->getInfo('cdPay'));
	}

	function setKcpCancelTax()
	{
		$trad_time	= ereg_replace("[^0-9]", "", $this->datas['issue_date']);
		$this->payObj->mf_set_modx_data("mod_type",		'STSC'					);
		$this->payObj->mf_set_modx_data("mod_value",	$this->datas['receipt_no']	);
		$this->payObj->mf_set_modx_data("mod_gubn",		'MG02'					);
		$this->payObj->mf_set_modx_data("trad_time",	$trad_time				);

		$this->datas['order_seq']	= '';

		return $this->setKcpCashTaxGlobal($this->getInfo('cdCan'));
	}

	function setKcpCashTaxGlobal($taxCd)
	{
		$this->payObj->mf_do_tx('', $this->getInfo('dir'), $this->getInfo('id'), '', $taxCd, '',
								$this->getInfo('url'), $this->getInfo('port'), "payplus_cli_slib", $this->datas['order_seq'],
								$this->getInfo('ip'), $this->getInfo('level'), '', $this->getInfo('mode'));

		$re		= $this->payObj->m_res_cd;
		if ($re == '0000')
		{
			unset($re);
			$re['cash_no'	]	= $this->payObj->mf_get_res_data("cash_no"		);
			$re['receipt_no']	= $this->payObj->mf_get_res_data("receipt_no"	);
			$re['app_time'	]	= $this->payObj->mf_get_res_data("app_time"		);
			$re['reg_stat'	]	= $this->payObj->mf_get_res_data("reg_stat"		);
			$re['reg_desc'	]	= $this->payObj->mf_get_res_data("reg_desc"		);
		}
		return $re;
	}


	function getInicis($taxParamMode, $data)
	{
		/**************************
		 * 1. 라이브러리 인클루드 *
		 **************************/
		require_once $_SERVER['DOCUMENT_ROOT'].'/pg/inicis/libs/INILib.php';

		/***************************************
		 * 2. INIpay 클래스의 인스턴스 생성 *
		 ***************************************/
		$this->payObj = new INIpay50;

       /*********************
		 * 3. 발급 정보 설정 *
		 *********************/
		$this->payObj->SetField("inipayhome"		,$_SERVER['DOCUMENT_ROOT'].'/pg/inicis'); 	// 이니페이 홈디렉터리

		/**************************************************************************************************
     * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
     * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
     * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
     * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
     **************************************************************************************************/
		$admin = ($this->pgconf['merchantKey'])?$this->pgconf['merchantKey']:'1111';
		$this->payObj->SetField("admin"				,$admin); 					  // 키패스워드(상점아이디에 따라 변경)
		$this->payObj->SetField("debug"				,"true"); 					  // 로그모드("true"로 설정하면 상세로그가 생성됨.)
		$this->payObj->SetField("mid"					,$this->pgconf["mallCode"]); 					// 상점아이디

		$this->datas	= $data;

        switch ($taxParamMode)
		{
			case 'pay' :
				$return	= $this->setInicisInsertTax();
				break;
			case 'mod' :
				$return	= $this->setInicisCancelTax();
				break;
			default :
				$return	= 3;
				break;
		}

        return $return;
	}

	function setInicisCancelTax()
	{

		$this->payObj->SetField("type"					,"cancel"); 									// 고정
		$this->payObj->SetField("tid"						, $this->datas['cash_no']);			// 취소할 거래의 거래아이디
		$this->payObj->SetField("cancelmsg"			, "관리자취소");								// 취소사유

		$this->payObj->startAction();

		$re		= $this->payObj->GetResult('ResultCode');

		if ($re != '01') {
			unset($re);
			$re['cash_no'	]	= $this->payObj->GetResult('TID');
			$re['receipt_no']	= $this->payObj->GetResult('ApplNum');
			$re['app_time'	]	= $this->payObj->GetResult('ApplDate').$this->payObj->GetResult('ApplTime');
		}else{
			$re		= iconv("euc-kr","utf-8", $this->payObj->GetResult('ResultMsg'));
		}
		return $re;
	}

	function setInicisInsertTax()
	{
		$trad_time	= ereg_replace("[^0-9]", "", $this->datas['paydt']);
		//$amt_tax	= ($this->pgconf['creceipt_type'] == 1) ? (int)($this->datas['price']*(10/110)) : '0';
		//$amt_sup	= ($this->pgconf['creceipt_type'] == 1) ? ($this->datas['price']-$amt_tax) : $this->datas['price'];
        $amt_tax	= $this->datas['surtax'];
        $amt_sup	= $this->datas['supply'];

		$this->payObj->SetField("type"					,"receipt"); 												// 고정
		$this->payObj->SetField("pgid"					,"INIphpRECP"); 										// 고정
		$this->payObj->SetField("paymethod"		,"CASH");													// 고정 (요청분류)
		$this->payObj->SetField("currency"			,"WON");													// 화폐단위 (고정)
		$this->payObj->SetField("goodname"			,iconv("utf-8","euc-kr",$this->datas['goodsname']));					// 상품명
		$this->payObj->SetField("cr_price"				,$this->datas['price']);								// 총 현금결제 금액
		$this->payObj->SetField("sup_price"			,$amt_sup);												// 공급가액
		$this->payObj->SetField("tax"						,$amt_tax);												// 부가세
		$this->payObj->SetField("srvc_price"			,0);															// 봉사료
		$this->payObj->SetField("buyername"		,iconv("utf-8","euc-kr",$this->datas['person']));							// 구매자 성명
		$this->payObj->SetField("buyeremail"		,$this->datas['email']);							// 구매자 이메일 주소
		$this->payObj->SetField("buyertel"				,$this->datas['phone']);							// 구매자 전화번호
		$this->payObj->SetField("reg_num"			,$this->datas['creceipt_number']);			// 현금결제자 주민등록번호
		$this->payObj->SetField("useopt"				,$this->datas['cuse']);								// 현금영수증 발행용도 ("0" - 소비자 소득공제용, "1" - 사업자 지출증빙용)
		//$this->payObj->SetField("companynumber" ,$this->datas['creceipt_number']);


		/****************
		 * 4. 발급 요청 *
		 ****************/
		$this->payObj->startAction();

		$re		= $this->payObj->GetResult('ResultCode');
		if ($re == '00')
		{
			unset($re);
			$re['cash_no'	]	= $this->payObj->GetResult('TID');
			$re['receipt_no']	= $this->payObj->GetResult('ApplNum');
			$re['app_time'	]	= $this->payObj->GetResult('ApplDate').$this->payObj->GetResult('ApplTime');
		}else{
			$re		= iconv("euc-kr","utf-8", $this->payObj->GetResult('ResultMsg'));
		}
		return $re;
	}

}
?>