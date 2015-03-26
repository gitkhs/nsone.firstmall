<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class kcp_mobile extends front_base {
	public function __construct(){
		parent::__construct();
		$this->load->helper('order');
		$this->load->helper('shipping');
		$this->load->model('ordermodel');
	}

	public function _site_conf_inc()
	{
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
			$pg_param['kcp_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param['g_conf_home_dir']  = ROOTPATH."pg/kcp_mobile";
		$pg_param['g_conf_gw_url']    = $pg['mallCode']=='T0007' ? "testpaygw.kcp.co.kr" : "paygw.kcp.co.kr";
		$pg_param['g_conf_site_cd']   = $pg['mallCode'];
		$pg_param['g_conf_site_key']  = $pg['merchantKey'];
		$pg_param['g_conf_site_name'] = $this->config_basic['shopName'];
		$pg_param['g_conf_log_level'] = "3";
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}

		$pg_param['g_wsdl'] = ROOTPATH."pg/kcp_mobile/sample/common/real_KCPPaymentService.wsdl";

		$pg_param['g_conf_gw_port']   = "8090";        // 포트번호(변경불가)

		return $pg_param;
	}

	public function auth()
	{
		header("Content-Type: text/html; charset=UTF-8");
		$pg_param = $this->_site_conf_inc();
		$data_orders = $this->ordermodel->get_order($_POST['order_seq']);

		$g_conf_home_dir  = $pg_param['g_conf_home_dir'];       // BIN 절대경로 입력 (bin전까지)
		$g_conf_gw_url = $pg_param['g_conf_gw_url'];
		$g_conf_site_cd   = $pg_param['g_conf_site_cd'];
		$g_conf_site_key  = $pg_param['g_conf_site_key'];
		$g_conf_site_name = $pg_param['g_conf_site_name'];
		$g_wsdl = $pg_param['g_wsdl'];
		$g_conf_gw_port = $pg_param['g_conf_gw_port'];
		
		// 비과세금액
		$r_param['comm_free_mny'] = $data_orders['freeprice'];
		
		// 과세금액
		$comm_tax_mny = $data_orders['settleprice']-$data_orders['freeprice'];		
		$r_param['comm_tax_mny'] = $comm_tax_mny;
		
		// 부가세
		$surtax = $comm_tax_mny - round($comm_tax_mny / 1.1);
		$r_param['comm_vat_mny'] = $surtax;

		## 장바구니 상품정보
		$goods_info = unserialize($_POST["goods_info"]);
		if($goods_info){
			$good_info = '';
			foreach($goods_info as $k=>$item){
				if(!$item['good_cntx']) $item['good_cntx'] = 1;
				if(!$item['good_amtx']) $item['good_amtx'] = 0;
				$good_info .= "seq=".($k+1).chr(31);
				$good_info .= "ordr_numb=".$item['ordr_numb'].chr(31);
				$good_info .= "good_name=".addslashes(substr($item['good_name'],0,30)).chr(31);
				$good_info .= "good_cntx=".$item['good_cntx'].chr(31);
				$good_info .= "good_amtx=".$item['good_amtx'].chr(30);
			}
		}
		$r_param['g_conf_home_dir']		= $g_conf_home_dir;
	    $r_param['g_conf_gw_url']		= $g_conf_gw_url;
	    $r_param['g_conf_site_cd']		= $g_conf_site_cd;
	    $r_param['g_conf_site_key']		= $g_conf_site_key;
	    $r_param['g_conf_site_name']	= $g_conf_site_name;
	    $r_param['g_wsdl']				= $g_wsdl;
    	$r_param['g_conf_gw_port']		= $g_conf_gw_port;
	    $r_param['req_tx']				= $_POST["req_tx"]; // 요청 종류
	    $r_param['res_cd']				= $_POST["res_cd"]; // 응답 코드
	    $r_param['tran_cd']				= $_POST["tran_cd"]; // 트랜잭션 코드
	    $r_param['goods_name']			= $_POST["goods_name"]; // 상품명
	    $r_param['good_info']			= $good_info; // 장바구니 상세정보(에스크로)
	    $r_param['bask_cntx']			= count($goods_info); // 장바구니갯수(수량아님)(에스크로)
	    $r_param['use_pay_method']		= $_POST[ "use_pay_method" ]; // 결제 방법
	    $r_param['enc_info']			= $_POST["enc_info" ]; // 암호화 정보
	    $r_param['enc_data']			= $_POST["enc_data"]; // 암호화 데이터
	    $r_param['param_opt_1']			= $param_opt_1 = $_POST["param_opt_1"]; // 기타 파라메터 추가 부분
	    $r_param['param_opt_2']			= $param_opt_2 = $_POST["param_opt_2"]; // 기타 파라메터 추가 부분
	    $r_param['param_opt_3']			= $param_opt_3 = $_POST["param_opt_3"]; // 기타 파라메터 추가 부분
		$r_param['tablet_size']			= $tablet_size      = "1.0"; // 화면 사이즈 조정 - 기기화면에 맞게 수정(갤럭시탭,아이패드 - 1.85, 스마트폰 - 1.0)


		$this->template->assign($r_param);
		$this->template->assign($data_orders);
		$this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_kcp_auth.html'));
		$this->template->print_('tpl');
	}

	public function approval()
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, pre-check=0");
		header("Pragma: no-cache");
		ini_set("soap.wsdl_cache_enabled", 0);

		$pg_param = $this->_site_conf_inc();

		$g_conf_home_dir  = $pg_param['g_conf_home_dir'];       // BIN 절대경로 입력 (bin전까지)
		$g_conf_gw_url = $pg_param['g_conf_gw_url'];
		$g_conf_site_cd   = $pg_param['g_conf_site_cd'];
		$g_conf_site_key  = $pg_param['g_conf_site_key'];
		$g_conf_site_name = $pg_param['g_conf_site_name'];
		$g_wsdl = $pg_param['g_wsdl'];
		$g_conf_gw_port = $pg_param['g_conf_gw_port'];

		require ROOTPATH."pg/kcp_mobile/sample/common/KCPComLibrary.php";              // library [수정불가]

		// 쇼핑몰 페이지에 맞는 문자셋을 지정해 주세요.
	    $charSetType      = "utf-8";             // UTF-8인 경우 "utf-8"로 설정

	    $siteCode         = $_GET[ "site_cd"     ];
	    $orderID          = $_GET[ "ordr_idxx"   ];
	    $paymentMethod    = $_GET[ "pay_method"  ];
	    $escrow           = ( $_GET[ "escw_used"   ] == "Y" ) ? true : false;
	    $productName      = $_GET['good_name'];

	    // 아래 두값은 POST된 값을 사용하지 않고 서버에 SESSION에 저장된 값을 사용하여야 함.
	    $paymentAmount    = $_GET[ "good_mny"    ]; // 결제 금액
	    $returnUrl        = $_GET[ "Ret_URL"     ];

	    // Access Credential 설정
	    $accessLicense    = "";
	    $signature        = "";
	    $timestamp        = "";

	    // Base Request Type 설정
	    $detailLevel      = "0";
	    $requestApp       = "WEB";
	    $requestID        = $orderID;
	    $userAgent        = $_SERVER['HTTP_USER_AGENT'];
	    $version          = "0.1";

	    try
	    {
	        $payService = new PayService( $g_wsdl );

	        $payService->setCharSet( $charSetType );

	        $payService->setAccessCredentialType( $accessLicense, $signature, $timestamp );
	        $payService->setBaseRequestType( $detailLevel, $requestApp, $requestID, $userAgent, $version );
	        $payService->setApproveReq( $escrow, $orderID, $paymentAmount, $paymentMethod, $productName, $returnUrl, $siteCode );

	        $approveRes = $payService->approve();

	        printf( "%s,%s,%s,%s", $payService->resCD,  $approveRes->approvalKey,
	                               $approveRes->payUrl, $payService->resMsg );

	    }
	    catch (SoapFault $ex )
	    {
	        printf( "%s,%s,%s,%s", "95XX", "", "", iconv("EUC-KR","UTF-8","연동 오류 (PHP SOAP 모듈 설치 필요)" ) );
	    }
	}

	public function pp_ax_hub()
	{
		$this->load->model('cartmodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		$pg_param			= $this->_site_conf_inc();
		$g_conf_home_dir	= $pg_param['g_conf_home_dir'];       // BIN 절대경로 입력 (bin전까지)
		$g_conf_gw_url		= $pg_param['g_conf_gw_url'];
		$g_conf_site_cd		= $pg_param['g_conf_site_cd'];
		$g_conf_site_key	= $pg_param['g_conf_site_key'];
		$g_conf_site_name	= $pg_param['g_conf_site_name'];
		$g_wsdl				= $pg_param['g_wsdl'];
		$g_conf_gw_port		= $pg_param['g_conf_gw_port'];

		require BASEPATH."../pg/kcp_mobile/sample/common/pp_ax_hub_lib.php";              // library [수정불가]

		/* ============================================================================== */
	    /* =   01. 지불 요청 정보 설정                                                  = */
	    /* = -------------------------------------------------------------------------- = */
		$req_tx         = $_POST[ "req_tx"         ]; // 요청 종류
		$tran_cd        = $_POST[ "tran_cd"        ]; // 처리 종류
		/* = -------------------------------------------------------------------------- = */
		$cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
		$ordr_idxx      = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호
		$good_name      = $_POST[ "good_name"      ]; // 상품명
		$good_mny       = $_POST[ "good_mny"       ]; // 결제 총금액
		/* = -------------------------------------------------------------------------- = */
		$tax_flag		= "TG03";					   // 복합과세
		$comm_tax_mny	= $_POST[ "comm_tax_mny"	]; // 과세 승인금액
		$comm_free_mny	= $_POST[ "comm_free_mny"	]; // 비과세 승인금액
		$comm_vat_mny	= $_POST[ "comm_vat_mny"	]; // 부가가치세
		/* = -------------------------------------------------------------------------- = */
	    $res_cd         = "";                         // 응답코드
	    $res_msg        = "";                         // 응답메시지
	    $tno            = $_POST[ "tno"            ]; // KCP 거래 고유 번호
	    /* = -------------------------------------------------------------------------- = */
	    $buyr_name      = $_POST[ "buyr_name"      ]; // 주문자명
	    $buyr_tel1      = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호
	    $buyr_tel2      = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호
	    $buyr_mail      = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소
	    /* = -------------------------------------------------------------------------- = */
	    $mod_type       = $_POST[ "mod_type"       ]; // 변경TYPE VALUE 승인취소시 필요
	    $mod_desc       = $_POST[ "mod_desc"       ]; // 변경사유
	    /* = -------------------------------------------------------------------------- = */
	    $use_pay_method = $_POST[ "use_pay_method" ]; // 결제 방법
	    $bSucc          = "";                         // 업체 DB 처리 성공 여부
	    /* = -------------------------------------------------------------------------- = */
		$app_time       = "";                         // 승인시간 (모든 결제 수단 공통)
		$amount         = "";                         // KCP 실제 거래 금액
		$total_amount   = 0;                          // 복합결제시 총 거래금액
	    /* = -------------------------------------------------------------------------- = */
	    $card_cd        = "";                         // 신용카드 코드
	    $card_name      = "";                         // 신용카드 명
	    $app_no         = "";                         // 신용카드 승인번호
	    $noinf          = "";                         // 신용카드 무이자 여부
	    $quota          = "";                         // 신용카드 할부개월
		/* = -------------------------------------------------------------------------- = */
		$bank_name      = "";			              // 은행명
	    $bank_code      = "";                         // 은행코드
	    /* = -------------------------------------------------------------------------- = */
	    $bankname       = "";                         // 입금할 은행명
	    $depositor      = "";                         // 입금할 계좌 예금주 성명
	    $account        = "";                         // 입금할 계좌 번호
	    /* = -------------------------------------------------------------------------- = */
		$pnt_issue      = "";                         // 결제 포인트사 코드
		$pt_idno        = "";                         // 결제 및 인증 아이디
		$pnt_amount     = "";                         // 적립금액 or 사용금액
		$pnt_app_time   = "";                         // 승인시간
		$pnt_app_no     = "";                         // 승인번호
	    $add_pnt        = "";                         // 발생 포인트
		$use_pnt        = "";                         // 사용가능 포인트
		$rsv_pnt        = "";                         // 적립 포인트
	    /* = -------------------------------------------------------------------------- = */
		$commid         = "";                         // 통신사 코드
		$mobile_no      = "";                         // 휴대폰 번호
		/* = -------------------------------------------------------------------------- = */
		$tk_van_code    = "";                         // 발급사 코드
		$tk_app_no      = "";                         // 상품권 승인 번호
		/* = -------------------------------------------------------------------------- = */
	    $cash_yn        = $_POST[ "cash_yn"        ]; // 현금영수증 등록 여부
	    $cash_authno    = "";                         // 현금 영수증 승인 번호
	    $cash_tr_code   = $_POST[ "cash_tr_code"   ]; // 현금 영수증 발행 구분
	    $cash_id_info   = $_POST[ "cash_id_info"   ]; // 현금 영수증 등록 번호
	    /* ============================================================================== */

	    /* ============================================================================== */
	    /* =   02. 인스턴스 생성 및 초기화                                              = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =       결제에 필요한 인스턴스를 생성하고 초기화 합니다.                     = */
	    /* = -------------------------------------------------------------------------- = */
	    $c_PayPlus = new C_PP_CLI;

	    $c_PayPlus->mf_clear();
	    /* ------------------------------------------------------------------------------ */
		/* =   02. 인스턴스 생성 및 초기화 END											= */
		/* ============================================================================== */


	    /* ============================================================================== */
	    /* =   03. 처리 요청 정보 설정                                                  = */
	    /* = -------------------------------------------------------------------------- = */

	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-1. 승인 요청                                                          = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $req_tx == "pay" )
	    {
	            $c_PayPlus->mf_set_encx_data( $_POST[ "enc_data" ], $_POST[ "enc_info" ] );
	    }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-2. 취소/매입 요청                                                     = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $req_tx == "mod" )
	    {
	        $tran_cd = "00200000";

	        $c_PayPlus->mf_set_modx_data( "tno",      $tno      ); // KCP 원거래 거래번호
	        $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type ); // 원거래 변경 요청 종류
	        $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  ); // 변경 요청자 IP
	        $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc ); // 변경 사유
	    }
		/* ------------------------------------------------------------------------------ */
		/* =   03.  처리 요청 정보 설정 END  											= */
		/* ============================================================================== */


		/* ============================================================================== */
	    /* =   04. 실행                                                                 = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $tran_cd != "" )
	    {
	        $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
	                              $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
	                              $cust_ip, $g_conf_log_level, 0, 0 ); // 응답 전문 처리

			$res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
			$res_msg = iconv("euc-kr","utf-8",$c_PayPlus->m_res_msg); // 결과 메시지
	    }
	    else
	    {
	        $c_PayPlus->m_res_cd  = "9562";
	        $c_PayPlus->m_res_msg = "연동 오류|tran_cd값이 설정되지 않았습니다.";
			
			$res_cd				=  $c_PayPlus->m_res_cd;
			$res_msg			=  $c_PayPlus->m_res_msg;
	    }


	    /* = -------------------------------------------------------------------------- = */
	    /* =   04. 실행 END                                                             = */
	    /* ============================================================================== */


	    /* ============================================================================== */
	    /* =   05. 승인 결과 값 추출                                                    = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $req_tx == "pay" )
	    {
	        if( $res_cd == "0000" )
	        {
	            $tno       = $c_PayPlus->mf_get_res_data( "tno"       ); // KCP 거래 고유 번호
	            $amount    = $c_PayPlus->mf_get_res_data( "amount"    ); // KCP 실제 거래 금액
				$pnt_issue = $c_PayPlus->mf_get_res_data( "pnt_issue" ); // 결제 포인트사 코드

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-1. 신용카드 승인 결과 처리                                            = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "100000000000" )
	            {
	                $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드사 코드
	                $card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드 종류
	                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인 시간
	                $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인 번호
	                $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부 ( 'Y' : 무이자 )
	                $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월 수
	            }

		/* = -------------------------------------------------------------------------- = */
	    /* =   05-2. 계좌이체 승인 결과 처리                                            = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "010000000000" )
	            {
	                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"   ); // 승인시간
	                $bank_name = $c_PayPlus->mf_get_res_data( "bank_name"  ); // 은행명
	                $bank_code = $c_PayPlus->mf_get_res_data( "bank_code"  ); // 은행코드
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-3. 가상계좌 승인 결과 처리                                            = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "001000000000" )
	            {
	                $bankname  = $c_PayPlus->mf_get_res_data( "bankname"  ); // 입금할 은행 이름
	                $depositor = $c_PayPlus->mf_get_res_data( "depositor" ); // 입금할 계좌 예금주
	                $account   = $c_PayPlus->mf_get_res_data( "account"   ); // 입금할 계좌 번호
	                $va_date   = $c_PayPlus->mf_get_res_data( "va_date"   ); // 입금예정일
	                $va_name   = $c_PayPlus->mf_get_res_data( "va_name"   ); // 입금자명
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-4. 포인트 승인 결과 처리                                               = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "000100000000" )
	            {
					$pt_idno      = $c_PayPlus->mf_get_res_data( "pt_idno"      ); // 결제 및 인증 아이디
	                $pnt_amount   = $c_PayPlus->mf_get_res_data( "pnt_amount"   ); // 적립금액 or 사용금액
		            $pnt_app_time = $c_PayPlus->mf_get_res_data( "pnt_app_time" ); // 승인시간
		            $pnt_app_no   = $c_PayPlus->mf_get_res_data( "pnt_app_no"   ); // 승인번호
		            $add_pnt      = $c_PayPlus->mf_get_res_data( "add_pnt"      ); // 발생 포인트
	                $use_pnt      = $c_PayPlus->mf_get_res_data( "use_pnt"      ); // 사용가능 포인트
	                $rsv_pnt      = $c_PayPlus->mf_get_res_data( "rsv_pnt"      ); // 적립 포인트
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-5. 휴대폰 승인 결과 처리                                              = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "000010000000" )
	            {
					$app_time  = $c_PayPlus->mf_get_res_data( "hp_app_time"  ); // 승인 시간
					$commid    = $c_PayPlus->mf_get_res_data( "commid"	     ); // 통신사 코드
					$mobile_no = $c_PayPlus->mf_get_res_data( "mobile_no"	 ); // 휴대폰 번호
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-6. 상품권 승인 결과 처리                                              = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "000000001000" )
	            {
					$app_time    = $c_PayPlus->mf_get_res_data( "tk_app_time"  ); // 승인 시간
					$tk_van_code = $c_PayPlus->mf_get_res_data( "tk_van_code"  ); // 발급사 코드
					$tk_app_no   = $c_PayPlus->mf_get_res_data( "tk_app_no"    ); // 승인 번호
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-7. 현금영수증 결과 처리                                               = */
	    /* = -------------------------------------------------------------------------- = */
	            $cash_authno  = $c_PayPlus->mf_get_res_data( "cash_authno"  ); // 현금 영수증 승인 번호

			}
		}
		/* = -------------------------------------------------------------------------- = */
	    /* =   05. 승인 결과 처리 END                                                   = */
	    /* ============================================================================== */

		## 로그 저장 변수 세팅
	    $pg_log['pg']	= $this->config_system['pgCompany'];
	    $pg_log['res_cd']		= $res_cd;
	    $pg_log['res_msg']		= $res_msg;
	    $pg_log['order_seq']	= $ordr_idxx;
	    $pg_log['tno']			= $tno;
    	$pg_log['amount']		= $amount;
		$pg_log['card_cd'] 		= $card_cd;
		$pg_log['card_name'] 	= $card_name;
		$pg_log['noinf'] 		= $noinf;
		$pg_log['quota'] 		= $quota;
		$pg_log['app_time']		= $app_time;
		$pg_log['bank_name']	= iconv("euc-kr","utf-8",$bank_name);
		$pg_log['bank_code'] 	= $bank_code;
		$pg_log['depositor'] 	= iconv("euc-kr","utf-8",$depositor);
		$pg_log['account'] 		= $account;
		$pg_log['va_date'] 		= $va_date;
		$pg_log['biller'] 		= iconv("euc-kr","utf-8",$va_name);
		$pg_log['app_time']		= $app_time;
		$pg_log['commid'] 		= $commid;
		$pg_log['mobile_no']	= $mobile_no;

		$pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);

		/* ============================================================================== */
	    /* =   06. 승인 및 실패 결과 DB처리                                             = */
	    /* = -------------------------------------------------------------------------- = */
		/* =       결과를 업체 자체적으로 DB처리 작업하시는 부분입니다.                 = */
	    /* = -------------------------------------------------------------------------- = */
		
		// 주문서 정보 가져오기
		$orders = $this->ordermodel->get_order($ordr_idxx);
		if(!$orders['order_user_name']) $orders['order_user_name'] = "주문자";

		if ( $req_tx == "pay" )
	    {

			if( $res_cd == "0000" )
	        {
				// 주문 상품 재고 체크
				$runout				= false;
				$cfg['order']		= config_load('order');

				// 출고량 업데이트를 위한 변수선언 
				$r_reservation_goods_seq = array();
				$result_option		= $this->ordermodel->get_item_option($ordr_idxx);
				$result_suboption	= $this->ordermodel->get_item_suboption($ordr_idxx);
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

				// 주문 처리
				if($runout == false):

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

					// 06-1-3. 가상계좌
					if ( $use_pay_method == "001000000000" )
					{
						$virtual_account = $bankname . " " . $account . " " . $depositor;
						$virtual_account = mb_convert_encoding($virtual_account, "UTF-8", "EUC-KR");
						if($cash_authno){//현금영수증발급
							$data = array(
								'typereceipt'=>2,
								'cash_receipts_no' => $cash_authno,
								'virtual_account' => $virtual_account,
								'virtual_date' => $va_date,
								'pg_transaction_number' => $tno,
								'pg_approval_number' => $app_no
							);
						}else{
							$data = array(
								'virtual_account' => $virtual_account,
								'virtual_date' => $va_date,
								'pg_transaction_number' => $tno,
								'pg_approval_number' => $app_no
							);
						}

						$this->ordermodel->set_step($ordr_idxx,15,$data);

						$log = "KCP 가상계좌 주문접수". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);
						$add_log = "";		
						if($orders['orign_order_seq'])	$add_log = "[재주문]";
						if($orders['admin_order'])		$add_log = "[관리자주문]";
						if($orders['person_seq'])		$add_log = "[개인결제]";
						$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")";
						$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

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

					}
					else
					{
						if($cash_authno){//PG모듈에서 현금영수증발급시
							$data = array(
								'typereceipt'=>2,
								'cash_receipts_no' => $cash_authno,
								'pg_transaction_number' => $tno,
								'pg_approval_number' => $app_no
							);
						}else{
							$data = array(
								'pg_transaction_number' => $tno,
								'pg_approval_number' => $app_no
							);
						}
						$this->ordermodel->set_step($ordr_idxx,25,$data);
						$log = "KCP 결제 확인". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);
						$add_log = "";
						if($orders['orign_order_seq']) $add_log = "[재주문]";
						if($orders['admin_order']) $add_log = "[관리자주문]";
						if($orders['person_seq']) $add_log = "[개인결제]";
						$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
						$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

						// 계좌이체 결제의 경우 현금영수증
						if( preg_match('/account/',$orders['payment']) && ($orders['step'] < '25' || $orders['step'] > '85') ){
							$result = typereceipt_setting($orders['order_seq']);
						}

						}

					// 출고예약량 업데이트
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}
				endif;
			}

		/* = -------------------------------------------------------------------------- = */
	    /* =   06. 승인 및 실패 결과 DB처리                                             = */
	    /* ============================================================================== */
			else if ( $req_cd != "0000" )
			{
				$res_cd  = $c_PayPlus->m_res_cd;
				if($res_cd != '8094'){
				$res_msg = iconv("euc-kr","utf-8",$c_PayPlus->m_res_msg);
				$this->ordermodel->set_step($ordr_idxx,99);
				$log = "KCP 결제 실패". chr(10)."[" .$res_cd . $res_msg . "]";
					$log_title	=  '결제실패['.$res_cd.']';					

					$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);
				}
			}
		}

		/* ============================================================================== */
	    /* =   07. 승인 결과 DB처리 실패시 : 자동취소                                   = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =         승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해      = */
	    /* =         DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로       = */
	    /* =         승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                = */
		/* =                                                                            = */
	    /* =         DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"     = */
	    /* =         로 설정해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의 = */
	    /* =         값을 설정하시면 됩니다.)                                           = */
	    /* = -------------------------------------------------------------------------- = */

		$bSucc = ""; // DB 작업 실패 또는 금액 불일치의 경우 "false" 로 세팅

	    /* = -------------------------------------------------------------------------- = */
	    /* =   07-1. DB 작업 실패일 경우 자동 승인 취소                                 = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $req_tx == "pay" )
	    {
			if( $res_cd == "0000" )
	        {
				if ( $bSucc == "false" )
	            {
	                $c_PayPlus->mf_clear();

	                $tran_cd = "00200000";

	                $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
	                $c_PayPlus->mf_set_modx_data( "mod_type", "STSC"                       );  // 원거래 변경 요청 종류
	                $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
	                $c_PayPlus->mf_set_modx_data( "mod_desc", "결과 처리 오류 - 자동 취소" );  // 변경 사유

	                $c_PayPlus->mf_do_tx( "",  $g_conf_home_dir, $g_conf_site_cd,
	                                      $g_conf_site_key,  $tran_cd,    "",
	                                      $g_conf_gw_url,  $g_conf_gw_port,  "payplus_cli_slib",
	                                      $ordr_idxx, $cust_ip,    $g_conf_log_level,
	                                      0, 0 );

	                $res_cd  = $c_PayPlus->m_res_cd;
	                if($c_PayPlus->m_res_msg) $res_msg = iconv("euc-kr","utf-8",$c_PayPlus->m_res_msg);


					// 주문취소
					$this->ordermodel->set_step($ordr_idxx,99);
					$log_title =  '결제실패['.$res_cd.']';

					$log = "KCP 결제 실패". chr(10)."[" .$res_cd . $res_msg . "]";

					$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);
	            }
	        }
		} // End of [res_cd = "0000"]

		if($_POST["param_opt_1"] == "mobilenew"){
			echo("<script>document.location.href='../order/complete_replace?no=".$ordr_idxx."&res_cd=".$c_PayPlus->m_res_cd."';</script>");
		}else{
			echo("<script>opener.location.href='../order/complete?no=".$ordr_idxx."';self.close();</script>");
		}
	}
}