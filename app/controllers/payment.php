<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class payment extends front_base {
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('order');
		$this->load->model('promotionmodel');
	}

	// 무통장 결제시
	public function bank()
	{
		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		$order_seq = $_POST['order_seq'];
		$adminOrder = $_POST['adminOrder'];
		// 주문 상품 재고 체크
		$cfg['order'] = config_load('order');
		if( $cfg['order']['runout'] != 'unlimited' ){
			if( $cfg['order'] == 'stock' ) $able_stock_limit = 0;
			else $able_stock_limit = (int) $cfg['order']['ableStockLimit'];
		}
		$result = $this->ordermodel->get_item_option($order_seq);
		$result_option = $result;
		if($result){
			foreach($result as $data){

				// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
				$goodsinfo = $this->goodsmodel->get_goods($data['goods_seq']);
				$data['event'] = $this->goodsmodel->get_event_price($goodsinfo['ori_price'], $data['goods_seq'], '', $goodsinfo['consumer_price'],$goodsinfo);
				if( $data['event']['event_goodsStatus'] === true ){
					openDialogAlert($data['goods_name'].'의 단독이벤트 기간에만 구매가 가능합니다.',400,140,'parent','');
					exit;
				}

				$chk_stock = check_stock_option(
					$data['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5'],
					$data['ea'],
					$cfg['order']
				);

				if( ! $chk_stock ){
					openDialogAlert($data['goods_name'].'의 재고가 없습니다.',400,140,'parent','');
					exit;
				}

				if($data['download_seq']){//배송비외 쿠폰
					// 발급 쿠폰 사용으로 상태 변경
					$this->couponmodel->set_download_use_status($data['download_seq'],'used');
				}
			}
		}

		$result = $this->ordermodel->get_item_suboption($order_seq);
		$result_suboption = $result;
		if($result){
			foreach($result as $data){

				// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
				$goodsinfo = $this->goodsmodel->get_goods($data['goods_seq']);
				$data['event'] = $this->goodsmodel->get_event_price($goodsinfo['ori_price'], $data['goods_seq'], '', $goodsinfo['consumer_price'],$goodsinfo);
				if( $data['event']['event_goodsStatus'] === true ){
					openDialogAlert($data['goods_name'].'의 단독이벤트 기간에만 구매가 가능합니다.',400,140,'parent','');
					exit;
				}

				$chk_stock = check_stock_suboption(
					$data['goods_seq'],
					$data['title'],
					$data['suboption'],
					$data['ea'],
					$cfg['order']
				);

				if( ! $chk_stock ){
					openDialogAlert($data['goods_name'].'의 재고가 없습니다.',400,140,'parent','');
					exit;
				}
			}
		}

		// 주문 처리, 주문서 정보 가져오기
		$orders = $this->ordermodel->get_order($order_seq);


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

		if($orders['download_seq']){//배송비쿠폰인경우
			// 발급 쿠폰 사용으로 상태 변경
			$this->couponmodel->set_download_use_status($orders['download_seq'],'used');
		}

		//프로모션코드 상품/배송비 할인 사용처리
		$this->promotionmodel->setPromotionpayment($orders);

		if($adminOrder == "admin"){

			$session_id = $this->session->userdata('session_id');
			$cartlist = $this->db->query("select cart_seq from fm_cart where distribution = 'admin' and session_id = '".$session_id."'");
			foreach ($cartlist->result_array() as $row)	{
				$this->db->query("delete from fm_cart_option where cart_seq = '".$row['cart_seq']."'");
				$this->db->query("delete from fm_cart_input where cart_seq = '".$row['cart_seq']."'");
				$this->db->query("delete from fm_cart_suboption where cart_seq = '".$row['cart_seq']."'");
				$this->db->query("delete from fm_cart where cart_seq = '".$row['cart_seq']."'");
			}

		}else{
			// 장바구니 비우기
			if( $orders['mode'] ){
				$this->cartmodel->delete_mode($orders['mode']);
			}
		}

		$add_log = "";
		$etc_log = "";
		if($orders['orign_order_seq']) $add_log = "[재주문]";
		if($orders['admin_order']) $add_log = "[관리자주문]";
		if($orders['person_seq']) $add_log = "[개인결제]";
		if($orders['settleprice'] == 0 ){
			if($orders['settleprice']) $etc_log = "[전액할인]";
			$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")".$etc_log;
		}else{
			$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")".$etc_log;
		}

		// 로그 생성
		$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],$log_title,'');

		if($orders['settleprice'] > 0 ){
			$this->ordermodel->set_step($order_seq,15);
		}else{
			$this->coupon_reciver_sms = array();
			$this->coupon_order_sms = array();
			$order_count = 0;
			$this->ordermodel->set_step($order_seq,25);

			//받는 사람 쿠폰 SMS 데이터
			if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
				$order_count = 0;
				foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
					$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
					$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
					$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
					$order_count					=$order_count+1;
				}

				$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
				$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
				$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

			}

			//주문자 쿠폰 SMS 데이터
			if(count($this->coupon_order_sms['order_cellphone']) > 0){
				$order_count = 0;
				foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
					$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
					$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
					$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
					$order_count					=$order_count+1;
				}

				$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
				$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
				$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

			}
			//debug_var(array("payment",$commonSmsData));
			if(count($commonSmsData) > 0){
				commonSendSMS($commonSmsData);
			}

		}

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		// 해당 주문 상품의 출고예약량 업데이트
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
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}



		if($adminOrder == "admin"){
			$this->admin_mail_sms($order_seq);
			//echo "<script>alert('주문이 완료되었습니다.'); top.location.reload();</script>";
		}else{
			pageRedirect('../order/complete?no='.$order_seq,'','parent');
		}
	}

	// KCP 결제시
	public function kcp()
	{
		 /* ============================================================================== */
	    /* =   PAGE : 지불 요청 및 결과 처리 PAGE                                       = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
	    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =   Copyright (c)  2010.02   KCP Inc.   All Rights Reserved.                 = */
	    /* ============================================================================== */


	    /* ============================================================================== */
	    /* =   환경 설정 파일 Include                                                   = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =   ※ 필수                                                                  = */
	    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
	    /* = -------------------------------------------------------------------------- = */

		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');
		$pg = config_load($this->config_system['pgCompany']);

		/* bin 디렉토리 전까지의 경로를 입력,절대경로 입력 */
		$g_conf_home_dir  = dirname(__FILE__)."/../../pg/kcp/";
		/* 테스트  : testpaygw.kcp.co.kr
		 * 실결제  : paygw.kcp.co.kr */
		$g_conf_gw_url    = $pg['mallCode']=='T0007' ? "testpaygw.kcp.co.kr" : "paygw.kcp.co.kr";
		/* 테스트  : https://pay.kcp.co.kr/plugin/payplus_test.js
		 * 실결제  : https://pay.kcp.co.kr/plugin/payplus.js */
		$g_conf_js_url	  = $pg['mallCode']=='T0007' ? "https://pay.kcp.co.kr/plugin/payplus_test_un.js" : "https://pay.kcp.co.kr/plugin/payplus_un.js";
		/* 테스트 T0000 */
		$g_conf_site_cd   = $pg['mallCode'];
		/* 테스트 3grptw1.zW0GSo4PQdaGvsF__ */
		$g_conf_site_key  = $pg['merchantKey'];
		$g_conf_site_name = $this->config_basic['shopName'];
		$g_conf_log_level = "3";           // 변경불가
		$g_conf_gw_port   = "8090";        // 포트번호(변경불가)

		require dirname(__FILE__)."/../../pg/kcp/sample/pp_ax_hub_lib.php"; // library [수정불가]

	    /* = -------------------------------------------------------------------------- = */
	    /* =   환경 설정 파일 Include END                                               = */
	    /* ============================================================================== */

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
		$tax_flag			= "TG03"; // 복합과세
		$comm_tax_mny		= $_POST[ "comm_tax_mny"	]; // 과세 승인금액
		$comm_free_mny		= $_POST[ "comm_free_mny"	]; // 비과세 승인금액
		$comm_vat_mny		= $_POST[ "comm_vat_mny"	]; // 부가가치세
		/* = -------------------------------------------------------------------------- = */
	    $res_cd         = "";                         // 응답코드
	    $res_msg        = "";                         // 응답메시지
		$res_en_msg     = "";                         // 응답 영문 메세지
	    $tno            = $_POST[ "tno"            ]; // KCP 거래 고유 번호
		$vcnt_yn        = $_POST[ "vcnt_yn"        ]; // 가상계좌 에스크로 사용 유무
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
		$total_amount   = 0;                          // 복합결제시 총 거래금액
	    $amount         = "";                         // KCP 실제 거래 금액
	    /* = -------------------------------------------------------------------------- = */
	    $card_cd        = "";                         // 신용카드 코드
	    $card_name      = "";                         // 신용카드 명
	    $app_no         = "";                         // 신용카드 승인번호
	    $noinf          = "";                         // 신용카드 무이자 여부
	    $quota          = "";                         // 신용카드 할부개월
	    /* = -------------------------------------------------------------------------- = */
		$bank_name      = "";                         // 은행명
		$bank_code      = "";						  // 은행코드
		/* = -------------------------------------------------------------------------- = */
	    $bankname       = "";                         // 입금할 은행명
	    $depositor      = "";                         // 입금할 계좌 예금주 성명
	    $account        = "";                         // 입금할 계좌 번호
		$va_date		= "";						  // 가상계좌 입금마감시간
	    /* = -------------------------------------------------------------------------- = */
		$pnt_issue      = "";					      // 결제 포인트사 코드
		$pt_idno        = "";                         // 결제 및 인증 아이디
		$pnt_amount     = "";                         // 적립금액 or 사용금액
		$pnt_app_time   = "";                         // 승인시간
		$pnt_app_no     = "";                         // 승인번호
	    $add_pnt        = "";                         // 발생 포인트
		$use_pnt        = "";                         // 사용가능 포인트
		$rsv_pnt        = "";                         // 총 누적 포인트
	    /* = -------------------------------------------------------------------------- = */
		$commid         = "";                         // 통신사 코드
		$mobile_no      = "";                         // 휴대폰 코드
		/* = -------------------------------------------------------------------------- = */
		$tk_shop_id		= $_POST[ "tk_shop_id"     ]; // 가맹점 고객 아이디
		$tk_van_code    = "";                         // 발급사 코드
		$tk_app_no      = "";                         // 상품권 승인 번호
		/* = -------------------------------------------------------------------------- = */
	    $cash_yn        = $_POST[ "cash_yn"        ]; // 현금영수증 등록 여부
	    $cash_authno    = "";                         // 현금 영수증 승인 번호
	    $cash_tr_code   = $_POST[ "cash_tr_code"   ]; // 현금 영수증 발행 구분
	    $cash_id_info   = $_POST[ "cash_id_info"   ]; // 현금 영수증 등록 번호
		/* ============================================================================== */
	    /* =   01-1. 에스크로 지불 요청 정보 설정                                       = */
	    /* = -------------------------------------------------------------------------- = */
	    $escw_used      = $_POST[  "escw_used"     ]; // 에스크로 사용 여부
	    $pay_mod        = $_POST[  "pay_mod"       ]; // 에스크로 결제처리 모드
	    $deli_term      = $_POST[  "deli_term"     ]; // 배송 소요일
	    $bask_cntx      = $_POST[  "bask_cntx"     ]; // 장바구니 상품 개수
	    $good_info      = $_POST[  "good_info"     ]; // 장바구니 상품 상세 정보
	    $rcvr_name      = $_POST[  "rcvr_name"     ]; // 수취인 이름
	    $rcvr_tel1      = $_POST[  "rcvr_tel1"     ]; // 수취인 전화번호
	    $rcvr_tel2      = $_POST[  "rcvr_tel2"     ]; // 수취인 휴대폰번호
	    $rcvr_mail      = $_POST[  "rcvr_mail"     ]; // 수취인 E-Mail
	    $rcvr_zipx      = $_POST[  "rcvr_zipx"     ]; // 수취인 우편번호
	    $rcvr_add1      = $_POST[  "rcvr_add1"     ]; // 수취인 주소
	    $rcvr_add2      = $_POST[  "rcvr_add2"     ]; // 수취인 상세주소
		$escw_yn		= "";						  // 에스크로 여부
	    /* = -------------------------------------------------------------------------- = */
	    /* =   01. 지불 요청 정보 설정 END                                              = */
	    /* ============================================================================== */

	    /* ============================================================================== */
	    /* =   02. 인스턴스 생성 및 초기화(변경 불가)                                   = */
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
	    /* =   03-1. 승인 요청 정보 설정                                                = */
	    /* = -------------------------------------------------------------------------- = */

		if ( $req_tx == "pay" )
	    {
			    /* 1004원은 실제로 업체에서 결제하셔야 될 원 금액을 넣어주셔야 합니다. 결제금액 유효성 검증 */
	            /* $c_PayPlus->mf_set_ordr_data( "ordr_mony",  "1004" );                                    */

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
		/* = -------------------------------------------------------------------------- = */
	    /* =   03-3. 에스크로 상태변경 요청                                             = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ($req_tx = "mod_escrow")
		{
			$tran_cd = "00200000";

	        $c_PayPlus->mf_set_modx_data( "tno",      $tno      );						// KCP 원거래 거래번호
	        $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type );						// 원거래 변경 요청 종류
	        $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  );						// 변경 요청자 IP
	        $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc );						// 변경 사유

			if ($mod_type == "STE1")													// 상태변경 타입이 [배송요청]인 경우
	        {
	            $c_PayPlus->mf_set_modx_data( "deli_numb",   $_POST[ "deli_numb" ] );   // 운송장 번호
	            $c_PayPlus->mf_set_modx_data( "deli_corp",   $_POST[ "deli_corp" ] );   // 택배 업체명
	        }
	        else if ($mod_type == "STE2" || $mod_type == "STE4") // 상태변경 타입이 [즉시취소] 또는 [취소]인 계좌이체, 가상계좌의 경우
	        {
	            if ($vcnt_yn == "Y")
	            {
	                $c_PayPlus->mf_set_modx_data( "refund_account",   $_POST[ "refund_account" ] );      // 환불수취계좌번호
	                $c_PayPlus->mf_set_modx_data( "refund_nm",        $_POST[ "refund_nm"      ] );      // 환불수취계좌주명
	                $c_PayPlus->mf_set_modx_data( "bank_code",        $_POST[ "bank_code"      ] );      // 환불수취은행코드
	            }
	        }
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-3. 에스크로 상태변경 요청 END                                         = */
	    /* = -------------------------------------------------------------------------- = */

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
	                              $cust_ip, "3" , 0, 0, $g_conf_key_dir, $g_conf_log_dir);

			$res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
			$res_msg = $c_PayPlus->m_res_msg; // 결과 메시지
			/* $res_en_msg = $c_PayPlus->mf_get_res_data( "res_en_msg" );  // 결과 영문 메세지 */
	    }
	    else
	    {
	        $c_PayPlus->m_res_cd  = "9562";
	        $c_PayPlus->m_res_msg = "연동 오류|Payplus Plugin이 설치되지 않았거나 tran_cd값이 설정되지 않았습니다.";
	    }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   04. 실행 END                                                             = */
	    /* ============================================================================== */


	    /* ============================================================================== */
	    /* =   05. 승인 결과 값 추출                                                    = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =   수정하지 마시기 바랍니다.                                                = */
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
	                $card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드사 명
	                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인시간
	                $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인번호
	                $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부
	                $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월 수

	                /* = -------------------------------------------------------------- = */
	                /* =   05-1.1. 복합결제(포인트+신용카드) 승인 결과 처리             = */
	                /* = -------------------------------------------------------------- = */
	                if ( $pnt_issue == "SCSK" || $pnt_issue == "SCWB" )
	                {
						$pt_idno      = $c_PayPlus->mf_get_res_data ( "pt_idno"      ); // 결제 및 인증 아이디
	                    $pnt_amount   = $c_PayPlus->mf_get_res_data ( "pnt_amount"   ); // 적립금액 or 사용금액
		                $pnt_app_time = $c_PayPlus->mf_get_res_data ( "pnt_app_time" ); // 승인시간
		                $pnt_app_no   = $c_PayPlus->mf_get_res_data ( "pnt_app_no"   ); // 승인번호
		                $add_pnt      = $c_PayPlus->mf_get_res_data ( "add_pnt"      ); // 발생 포인트
	                    $use_pnt      = $c_PayPlus->mf_get_res_data ( "use_pnt"      ); // 사용가능 포인트
	                    $rsv_pnt      = $c_PayPlus->mf_get_res_data ( "rsv_pnt"      ); // 총 누적 포인트
						$total_amount = $amount + $pnt_amount;                          // 복합결제시 총 거래금액
	                }
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-2. 계좌이체 승인 결과 처리                                            = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "010000000000" )
	            {
					$app_time  = $c_PayPlus->mf_get_res_data( "app_time"   );  // 승인 시간
	                $bank_name = $c_PayPlus->mf_get_res_data( "bank_name"  );  // 은행명
	                $bank_code = $c_PayPlus->mf_get_res_data( "bank_code"  );  // 은행코드
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-3. 가상계좌 승인 결과 처리                                            = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "001000000000" )
	            {
	                $bankname  = $c_PayPlus->mf_get_res_data( "bankname"  ); // 입금할 은행 이름
	                $depositor = $c_PayPlus->mf_get_res_data( "depositor" ); // 입금할 계좌 예금주
	                $account   = $c_PayPlus->mf_get_res_data( "account"   ); // 입금할 계좌 번호
					$va_date   = $c_PayPlus->mf_get_res_data( "va_date"   ); // 가상계좌 입금마감시간
	            }

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-4. 포인트 승인 결과 처리                                              = */
	    /* = -------------------------------------------------------------------------- = */
	            if ( $use_pay_method == "000100000000" )
	            {
					$pt_idno      = $c_PayPlus->mf_get_res_data( "pt_idno"      ); // 결제 및 인증 아이디
	                $pnt_amount   = $c_PayPlus->mf_get_res_data( "pnt_amount"   ); // 적립금액 or 사용금액
		            $pnt_app_time = $c_PayPlus->mf_get_res_data( "pnt_app_time" ); // 승인시간
		            $pnt_app_no   = $c_PayPlus->mf_get_res_data( "pnt_app_no"   ); // 승인번호
		            $add_pnt      = $c_PayPlus->mf_get_res_data( "add_pnt"      ); // 발생 포인트
	                $use_pnt      = $c_PayPlus->mf_get_res_data( "use_pnt"      ); // 사용가능 포인트
	                $rsv_pnt      = $c_PayPlus->mf_get_res_data( "rsv_pnt"      ); // 총 누적 포인트
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
		/* = -------------------------------------------------------------------------- = */
	    /* =   05-8. 에스크로 여부 결과 처리                                            = */
	    /* = -------------------------------------------------------------------------- = */
			$escw_yn = $c_PayPlus->mf_get_res_data( "escw_yn"  ); // 에스크로 여부

		}

		// 주문 결제수단 업데이트
		$r_use_pay_method = array(
			'100000000000'=>'card',
			'010000000000'=>'account',
			'001000000000'=>'virtual',
			'000010000000'=>'cellphone'
		);
		if($use_pay_method) $order_payment = $r_use_pay_method[$use_pay_method];
		if($escw_yn == 'Y' && $order_payment) $order_payment = 'escrow_'.$order_payment;
		if($order_payment){
			$query = "update fm_order set payment=? where order_seq=?";
			$this->db->query($query,array($order_payment,$ordr_idxx));
		}

		/* = -------------------------------------------------------------------------- = */
	    /* =   05. 승인 결과 처리 END                                                   = */
	    /* ============================================================================== */

		/* ============================================================================== */
	    /* =   06. 승인 및 실패 결과 DB처리                                             = */
	    /* = -------------------------------------------------------------------------- = */
		/* =       결과를 업체 자체적으로 DB처리 작업하시는 부분입니다.                 = */
	    /* = -------------------------------------------------------------------------- = */

		// 주문서 정보 가져오기
		$orders = $this->ordermodel->get_order($ordr_idxx);

		if ( $req_tx == "pay" )
	    {

		/* = -------------------------------------------------------------------------- = */
	    /* =   06-1. 승인 결과 DB 처리(res_cd == "0000")                                = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =        각 결제수단을 구분하시어 DB 처리를 하시기 바랍니다.                 = */
	    /* = -------------------------------------------------------------------------- = */
			if( $res_cd == "0000" )
	        {
				// 주문 상품 재고 체크
				$runout = false;
				$cfg['order'] = config_load('order');
				$result = $this->ordermodel->get_item_option($ordr_idxx);
				$data_item_option = $result;
				$result_option = $result;
				$result = $this->ordermodel->get_item_suboption($ordr_idxx);
				$result_suboption = $result;

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

					//프로모션코드 상품/배송비 할인 사용처리
					$this->promotionmodel->setPromotionpayment($orders);


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

						$add_log = "";
						$etc_log = "";
						if($orders['orign_order_seq']) $add_log = "[재주문]";
						if($orders['admin_order']) $add_log = "[관리자주문]";
						if($orders['person_seq']) $add_log = "[개인결제]";
						$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")".$etc_log;

						$log = "KCP 가상계좌 주문접수". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);
						$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

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
						$this->coupon_reciver_sms = array();
						$this->coupon_order_sms = array();
						$order_count = 0;

						$this->ordermodel->set_step($ordr_idxx,25,$data);

						//받는 사람 쿠폰 SMS 데이터
						if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
								$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
								$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
								$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
							$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
							$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

						}

						//주문자 쿠폰 SMS 데이터
						if(count($this->coupon_order_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
								$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
								$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
								$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
							$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
							$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

						}
						//debug_var(array("payment",$commonSmsData));
						if(count($commonSmsData) > 0){
							commonSendSMS($commonSmsData);
						}


						$add_log = "";
						if($orders['orign_order_seq']) $add_log = "[재주문]";
						if($orders['admin_order']) $add_log = "[관리자주문]";
						if($orders['person_seq']) $add_log = "[개인결제]";
						$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";

						$log = "KCP 결제 확인". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);
						$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

						// 계좌이체 결제의 경우 현금영수증
						if( preg_match('/account/',$orders['payment']) ){
							$result = typereceipt_setting($orders['order_seq']);
						}
					}

					// 출고량 업데이트를 위한 변수선언
					$r_reservation_goods_seq = array();

					// 해당 주문 상품의 출고예약량 업데이트
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
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}

				endif;
			}
		/* = -------------------------------------------------------------------------- = */
	    /* =   06.-2 승인 및 실패 결과 DB처리                                             = */
	    /* ============================================================================== */

			else if ( $req_cd != "0000" )
			{
				$res_cd  = $c_PayPlus->m_res_cd;
				if($res_cd != '8094'){
					$res_msg = $c_PayPlus->m_res_msg;
					$this->ordermodel->set_step($ordr_idxx,99);


					$log_title =  '결제실패['.$res_cd.']';
					$log = "KCP 결제 실패". chr(10)."[" .$res_cd . $res_msg . "]";
					$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);
				}
			}
		}
		/* = -------------------------------------------------------------------------- = */
	    /* =   06. 승인 및 실패 결과 DB 처리 END                                        = */
	    /* = ========================================================================== = */


		/* = ========================================================================== = */
	    /* =   07. 승인 결과 DB 처리 실패시 : 자동취소                                  = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =      승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해         = */
	    /* =      DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로          = */
	    /* =      승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                   = */
	    /* =                                                                            = */
	    /* =      DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"        = */
	    /* =      로 설정해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의    = */
	    /* =      값을 설정하시면 됩니다.)                                              = */
	    /* = -------------------------------------------------------------------------- = */

		// 승인 결과 DB 처리 에러시 bSucc값을 false로 설정하여 거래건을 취소 요청
		if($runout) $bSucc = "false";

	    if ( $req_tx == "pay" )
	    {
			if( $res_cd == "0000" )
	        {
				if ( $bSucc == "false" )
	            {
	                $c_PayPlus->mf_clear();

	                $tran_cd = "00200000";

		/* ============================================================================== */
	    /* =   07-1.자동취소시 에스크로 거래인 경우                                     = */
	    /* = -------------------------------------------------------------------------- = */
					// 취소시 사용하는 mod_type
	                $bSucc_mod_type = "";

	                // 에스크로 가상계좌 건의 경우 가상계좌 발급취소(STE5)
	                if ( $escw_yn == "Y" && $use_pay_method == "001000000000" )
					{
	                    $bSucc_mod_type = "STE5";
					}
	                // 에스크로 가상계좌 이외 건은 즉시취소(STE2)
	                else if ( $escw_yn == "Y" )
					{
	                    $bSucc_mod_type = "STE2";
					}
	                // 에스크로 거래 건이 아닌 경우(일반건)(STSC)
	                else
					{
	                    $bSucc_mod_type = "STSC";
					}
		/* = -------------------------------------------------------------------------- = */
		/* =   07-1. 자동취소시 에스크로 거래인 경우 처리 END                           = */
	    /* = ========================================================================== = */

	                $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
	                $c_PayPlus->mf_set_modx_data( "mod_type", $bSucc_mod_type              );  // 원거래 변경 요청 종류
	                $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
	                $c_PayPlus->mf_set_modx_data( "mod_desc", "가맹점 결과 처리 오류 - 가맹점에서 취소 요청" );  // 변경 사유

	                $c_PayPlus->mf_do_tx( $tno,  $g_conf_home_dir, $g_conf_site_cd,
	                                      "",  $tran_cd,    "",
	                                      $g_conf_gw_url,  $g_conf_gw_port,  "payplus_cli_slib",
	                                      $ordr_idxx, $cust_ip, "3" ,
	                                      0, 0, $g_conf_key_dir, $g_conf_log_dir);

	                $res_cd  = $c_PayPlus->m_res_cd;
	                $res_msg = $c_PayPlus->m_res_msg;

					$this->ordermodel->set_step($ordr_idxx,99);
					$log = "[" .$res_cd . $res_msg . "] 결제취소";
					$this->ordermodel->set_log($ordr_idxx,'pay','시스템','결제취소',$log);
	            }
	        }
		}

		## 로그 저장 변수 세팅
	    $pg_log['pg']	= $this->config_system['pgCompany'];
	    $pg_log['res_cd']		= $res_cd;
	    $pg_log['res_msg']		= $res_msg;
	    $pg_log['order_seq']	= $ordr_idxx;
	    $pg_log['tno']			= $tno;
    	$pg_log['amount']		= $amount;
		$pg_log['card_cd'] 		= $card_cd;
		$pg_log['card_name'] 	= $card_name;
		$pg_log['app_no'] 		= $app_no;
		$pg_log['app_time'] 	= $app_time;
		$pg_log['noinf'] 		= $noinf;
		$pg_log['quota'] 		= $quota;
		$pg_log['bank_code'] 	= $bank_code;
		$pg_log['bank_name']	= $bank_name;
		$pg_log['depositor'] 	= $depositor;
		$pg_log['account'] 		= $account;
		$pg_log['va_date'] 		= $va_date;
		$pg_log['app_time']		= $app_time;
		$pg_log['commid'] 		= $commid;
		$pg_log['mobile_no']	= $mobile_no;
		$pg_log['escw_yn']		= $escw_yn;
		foreach($pg_log as $k => $v){
			$v = trim($v);
			$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
		}
		$pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);

		// End of [res_cd = "0000"]
		pageRedirect('../order/complete?no='.$ordr_idxx,'','parent');
	}

	public function kcp_return(){
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');

	     /* ============================================================================== */
	    /* =   02. 공통 통보 데이터 받기                                                = */
	    /* = -------------------------------------------------------------------------- = */
	    $site_cd      = $_POST [ "site_cd"  ];                 // 사이트 코드
	    $tno          = $_POST [ "tno"      ];                 // KCP 거래번호
	    $order_no     = $_POST [ "order_no" ];                 // 주문번호
	    $tx_cd        = $_POST [ "tx_cd"    ];                 // 업무처리 구분 코드
	    $tx_tm        = $_POST [ "tx_tm"    ];                 // 업무처리 완료 시간
	    /* = -------------------------------------------------------------------------- = */
	    $ipgm_name    = "";                                    // 주문자명
	    $remitter     = "";                                    // 입금자명
	    $ipgm_mnyx    = "";                                    // 입금 금액
	    $bank_code    = "";                                    // 은행코드
	    $account      = "";                                    // 가상계좌 입금계좌번호
	    $op_cd        = "";                                    // 처리구분 코드
	    $noti_id      = "";                                    // 통보 아이디
	    /* = -------------------------------------------------------------------------- = */
		$refund_nm    = "";                                    // 환불계좌주명
	    $refund_mny   = "";                                    // 환불금액
	    $bank_code    = "";                                    // 은행코드
	    /* = -------------------------------------------------------------------------- = */
	    $st_cd        = "";                                    // 구매확인 코드
	    $can_msg      = "";                                    // 구매취소 사유
	    /* = -------------------------------------------------------------------------- = */
	    $waybill_no   = "";                                    // 운송장 번호
	    $waybill_corp = "";                                    // 택배 업체명
	    /* = -------------------------------------------------------------------------- = */
	    $cash_a_no    = "";                                    // 현금영수증 승인번호

	    /* = -------------------------------------------------------------------------- = */
	    /* =   02-1. 가상계좌 입금 통보 데이터 받기                                     = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $tx_cd == "TX00" )
	    {
	        $ipgm_name = $_POST[ "ipgm_name" ];                // 주문자명
	        $remitter  = $_POST[ "remitter"  ];                // 입금자명
	        $ipgm_mnyx = $_POST[ "ipgm_mnyx" ];                // 입금 금액
	        $bank_code = $_POST[ "bank_code" ];                // 은행코드
	        $account   = $_POST[ "account"   ];                // 가상계좌 입금계좌번호
	        $op_cd     = $_POST[ "op_cd"     ];                // 처리구분 코드
	        $noti_id   = $_POST[ "noti_id"   ];                // 통보 아이디
	        $cash_a_no = $_POST[ "cash_a_no" ];                // 현금영수증 승인번호
	    }

		/* = -------------------------------------------------------------------------- = */
	    /* =   02-2. 가상계좌 환불 통보 데이터 받기                                     = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX01" )
		{
	        $refund_nm  = $_POST[ "refund_nm"  ];               // 환불계좌주명
	        $refund_mny = $_POST[ "refund_mny" ];               // 환불금액
	        $bank_code  = $_POST[ "bank_code"  ];               // 은행코드
		}
	    /* = -------------------------------------------------------------------------- = */
	    /* =   02-3. 구매확인/구매취소 통보 데이터 받기                                  = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX02" )
	    {
	        $st_cd = $_POST[ "st_cd"]; 						    // 구매확인 코드

	        if ( $st_cd = "N"  )								// 구매확인 상태가 구매취소인 경우
			{
	            $can_msg = $_POST[ "can_msg"   ];               // 구매취소 사유
			}
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   02-4. 배송시작 통보 데이터 받기                                           = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX03" )
		{

	        $waybill_no   = $_POST[ "waybill_no"   ];           // 운송장 번호
	        $waybill_corp = $_POST[ "waybill_corp" ];           // 택배 업체명
		}

	    /* = -------------------------------------------------------------------------- = */
	    /* =   02-5. 모바일안심결제 통보 데이터 받기                                    = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX08" )
	    {
	        $ipgm_mnyx = $_POST[ "ipgm_mnyx" ];                // 입금 금액
	        $bank_code = $_POST[ "bank_code" ];                // 은행코드
	    }
	    /* ============================================================================== */

		 ## 로그 변수 세팅
	    $pg_log['pg']		= $this->config_system['pgCompany'];
		$pg_log['tno']			= $tno;
		$pg_log['order_seq'] 	= $order_no;
		$pg_log['depositor']	= $remitter;
		$pg_log['biller']		= $ipgm_name;
		$pg_log['amount']		= $ipgm_mnyx;
		$pg_log['bank_code']	= $bank_code;
		$pg_log['account']	= $account;
		$pg_log['res_cd']	= $tx_cd;
		$pg_log['res_msg']	= $can_msg;
		foreach($pg_log as $k => $v){
			$v = trim($v);
			$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
		}

		## 로그저장
		$pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);


	    /* ============================================================================== */
	    /* =   03. 공통 통보 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.      = */
	    /* = -------------------------------------------------------------------------- = */
	    /* =   통보 결과를 DB 작업 하는 과정에서 정상적으로 통보된 건에 대해 DB 작업에  = */
	    /* =   실패하여 DB update 가 완료되지 않은 경우, 결과를 재통보 받을 수 있는     = */
	    /* =   프로세스가 구성되어 있습니다.                                            = */
	    /* =                                                                            = */
	    /* =   * DB update가 정상적으로 완료된 경우                                     = */
	    /* =   하단의 [04. result 값 세팅 하기] 에서 result 값의 value값을 0000으로     = */
	    /* =   설정해 주시기 바랍니다.                                                  = */
	    /* =                                                                            = */
	    /* =   * DB update가 실패한 경우                                                = */
	    /* =   하단의 [04. result 값 세팅 하기] 에서 result 값의 value값을 0000이외의   = */
	    /* =   값으로 설정해 주시기 바랍니다.                                           = */
	    /* = -------------------------------------------------------------------------- = */

	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-1. 가상계좌 입금 통보 데이터 DB 처리 작업 부분                        = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $tx_cd == "TX00" )
	    {
			$data = array(
				'typereceipt'=>2,
				'cash_receipts_no' => $cash_a_no
			);

			$data_order = $this->ordermodel->get_order($order_no);

			if($data_order['step'] < '25' || $data_order['step'] > '85'){ // 결제이후 프로세스 진행 이후에는 결제확인 처리 하지 않음
				$this->coupon_reciver_sms = array();
				$this->coupon_order_sms = array();
				$order_count = 0;

				$this->ordermodel->set_step($order_no,25,$data);

				//받는 사람 쿠폰 SMS 데이터
				if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
						$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
						$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
						$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
					$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
					$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

				}

				//주문자 쿠폰 SMS 데이터
				if(count($this->coupon_order_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
						$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
						$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
						$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
					$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
					$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

				}
				//debug_var(array("payment",$commonSmsData));
				if(count($commonSmsData) > 0){
					commonSendSMS($commonSmsData);
				}

			}

			$log[] = date("Y-m-d H:i:s");
			$log[] = "주문자명:".$ipgm_name;
			$log[] = "입금자명:".$remitter;
			$log[] = "입금금액:".$ipgm_mnyx;
			$log[] = "은행코드:".$bank_code;
			$log[] = "가상계좌 입금계좌번호:".$account;
			$log[] = "주문자명:".$op_cd;
			$log[] = "주문자명:".$noti_id;
			$log[] = "현금영수증 승인번호:".$cash_a_no;

			$add_log = "";
			if($data_order['orign_order_seq']) $add_log = "[재주문]";
			if($data_order['admin_order']) $add_log = "[관리자주문]";
			if($data_order['person_seq']) $add_log = "[개인결제]";
			$log_title = $add_log."결제확인"."(".$data_order['mpayment'].")";
			$this->ordermodel->set_log($order_no,'pay','시스템',$log_title,'');

			// 가상계좌 결제의 경우 현금영수증
			if( $data_order['step'] < '25' || $data_order['step'] > '85' ){
				$result = typereceipt_setting($data_order['order_seq']);
			}

			// 출고량 업데이트를 위한 변수선언
			$r_reservation_goods_seq = array();

	   		$result_option = $this->ordermodel->get_item_option($order_no);
	   		$result_suboption = $this->ordermodel->get_item_suboption($order_no);
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
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}

			// 결제확인 sms발송
			$orders = $this->ordermodel->get_order($order_no);
			$result = typereceipt_setting($orders['order_seq']);
			if( $orders['sms_25_YN'] != 'Y'){
				$params['shopName'] = $this->config_basic['shopName'];
				$params['ordno']	= $orders['order_seq'];
				$params['user_name'] = $orders['order_user_name'];
				if($orders['order_cellphone']){

					//$commonSmsData = array();
					$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['settle']['params'][] = $params;
					$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
					commonSendSMS($commonSmsData);

					//sendSMS($orders['order_cellphone'], 'settle', '', $params,'','admin');
				}
				send_mail_step25($orders['order_seq']);

				$this->db->where('order_seq', $orders['order_seq']);
				$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
			}


	    }
		/* = -------------------------------------------------------------------------- = */
	    /* =   03-2. 가상계좌 환불 통보 데이터 DB 처리 작업 부분                        = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX01" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-3. 구매확인/구매취소 통보 데이터 DB 처리 작업 부분                    = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX02" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-4. 배송시작 통보 데이터 DB 처리 작업 부분                             = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX03" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-5. 정산보류 통보 데이터 DB 처리 작업 부분                             = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX04" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-6. 즉시취소 통보 데이터 DB 처리 작업 부분                             = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX05" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-7. 취소 통보 데이터 DB 처리 작업 부분                                 = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX06" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-8. 발급계좌해지 통보 데이터 DB 처리 작업 부분                         = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX07" )
	    {
	    }
	    /* = -------------------------------------------------------------------------- = */
	    /* =   03-9. 모바일안심결제 통보 데이터 DB 처리 작업 부분                       = */
	    /* = -------------------------------------------------------------------------- = */
	    else if ( $tx_cd == "TX08" )
	    {
	    }
	    /* ============================================================================== */




	    /* ============================================================================== */
	    /* =   04. result 값 세팅 하기                                                  = */
	    /* ============================================================================== */
	    echo "<html><body><form><input type=\"hidden\" name=\"result\" value=\"0000\"></form></body></html>";
	}

	public function lg()
	{
		 /*
	     * [최종결제요청 페이지(STEP2-2)]
	     *
	     * LG유플러스으로 부터 내려받은 LGD_PAYKEY(인증Key)를 가지고 최종 결제요청.(파라미터 전달시 POST를 사용하세요)
	     */

		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		global $pg;
		$pg = config_load($this->config_system['pgCompany']);

		$configPath = dirname(__FILE__)."/../../pg/lgdacom/"; //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf,/conf/mall.conf") 위치 지정.

	    /*
	     *************************************************
	     * 1.최종결제 요청 - BEGIN
	     *  (단, 최종 금액체크를 원하시는 경우 금액체크 부분 주석을 제거 하시면 됩니다.)
	     *************************************************
	     */
		$HTTP_POST_VARS = $_POST;
	    $CST_PLATFORM               = $HTTP_POST_VARS["CST_PLATFORM"];
	    $CST_MID                    = $HTTP_POST_VARS["CST_MID"];
	    $LGD_MID                    = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;
	    $LGD_PAYKEY                 = $HTTP_POST_VARS["LGD_PAYKEY"];

	    require_once( $configPath. "XPayClient.php");
	    $xpay = new XPayClient($configPath, $CST_PLATFORM);
	    $xpay->Init_TX($LGD_MID);

	    $xpay->Set("LGD_TXNAME", "PaymentByKey");
	    $xpay->Set("LGD_PAYKEY", $LGD_PAYKEY);


	    //금액을 체크하시기 원하는 경우 아래 주석을 풀어서 이용하십시요.
		//$DB_AMOUNT = "DB나 세션에서 가져온 금액"; //반드시 위변조가 불가능한 곳(DB나 세션)에서 금액을 가져오십시요.
		//$xpay->Set("LGD_AMOUNTCHECKYN", "Y");
		//$xpay->Set("LGD_AMOUNT", $DB_AMOUNT);

	    /*
	     *************************************************
	     * 1.최종결제 요청(수정하지 마세요) - END
	     *************************************************
	     */

	    /*
	     * 2. 최종결제 요청 결과처리
	     *
	     * 최종 결제요청 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
	     */
	    if ($xpay->TX()) {
	    	$tid			= $xpay->Response("LGD_TID",0); // 거래번호
	    	$mid			= $xpay->Response("LGD_MID",0); // 상점아이디
	    	$ordr_idxx		= $xpay->Response("LGD_OID",0); // 상점주문번호
	    	$res_code		= $xpay->Response("LGD_RESPCODE",0); // 결과코드
	    	$res_msg		= $xpay->Response("LGD_RESPMSG",0); // 결과메세지
	    	$use_pay_method = $xpay->Response("LGD_PAYTYPE",0); // 결과메세지
	    	$escw_yn		= $xpay->Response("LGD_ESCROWYN",0); // 결과메세지
	        $keys			= $xpay->Response_Names();

	        // 주문 결제수단 업데이트
	        $r_use_pay_method = array(
	        		'SC0010'=>'card',
	        		'SC0030'=>'account',
	        		'SC0040'=>'virtual',
	        		'SC0060'=>'cellphone'
	        );

	        if($use_pay_method) $order_payment = $r_use_pay_method[$use_pay_method];

			if($escw_yn == 'Y' && $order_payment=='virtual'){
				$order_payment = 'escrow_'.$order_payment;
			}
			if($escw_yn == 'Y' && $order_payment=='account'){
				$order_payment = 'escrow_'.$order_payment;
			}

			if($order_payment){
	        	$query = "update fm_order set payment=? where order_seq=?";
	        	$this->db->query($query,array($order_payment,$ordr_idxx));
	        }

	    	// 주문 상품 재고 체크
			$runout = false;
			$cfg['order'] = config_load('order');

			$result = $this->ordermodel->get_item_option($ordr_idxx);
			$data_item_option = $result;
			$result_option  = $result;
			$result = $this->ordermodel->get_item_suboption($ordr_idxx);
			$result_suboption  = $result;
	   			// 주문서 정보 가져오기
				$orders = $this->ordermodel->get_order($ordr_idxx);

	   		if( $runout == false && $res_code == '0000' ){

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
						'cash'		=> $orders['cash'],
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

				//프로모션코드 상품/배송비 할인 사용처리
				$this->promotionmodel->setPromotionpayment($orders);

	   			// 장바구니 비우기
				if( $orders['mode'] ) $this->cartmodel->delete_mode($orders['mode']);

				$isDBOK = true;
	   		}else{
	   			$isDBOK = false;
	   		}

	   		if( $res_code == '0000' )
			{
				if( !$isDBOK ) {
					$xpay->Rollback("상점 DB처리 실패로 인하여 Rollback 처리 [TID:" . $res_code . ",MID:" . $mid . ",OID:" . $ordr_idxx . "]");

					$this->ordermodel->set_step($ordr_idxx,99);
					$log = "[" .$res_code . $res_msg . "] 결제취소";
					$this->ordermodel->set_log($ordr_idxx,'pay','시스템','결제취소',$log);
				}

				if( preg_match('/virtual/',$orders['payment']) && $isDBOK ){
					$FINANCENAME = mb_convert_encoding($xpay->Response("LGD_FINANCENAME",0), "UTF-8", "EUC-KR");
					$ACCOUNT 	 = $xpay->Response("LGD_ACCOUNTNUM",0);
					$data = array(
						'virtual_account'	=> $FINANCENAME." ".$ACCOUNT,
						'pg_transaction_number' => $tid
					);

					$this->ordermodel->set_step($ordr_idxx,15,$data);

					$add_log = "";
					$etc_log = "";
					if($orders['orign_order_seq']) $add_log = "[재주문]";
					if($orders['admin_order']) $add_log = "[관리자주문]";
					if($orders['person_seq']) $add_log = "[개인결제]";
					$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")".$etc_log;
					$log = "LGU+ 가상계좌 주문접수". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);
					$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

					// 출고량 업데이트를 위한 변수선언
					$r_reservation_goods_seq = array();

					// 해당 주문 상품의 출고예약량 업데이트
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

				}

		        if( !preg_match('/virtual/',$orders['payment']) && $isDBOK )
		        {
					$data = array('pg_transaction_number' => $tid);
					$this->coupon_reciver_sms = array();
					$this->coupon_order_sms = array();
					$order_count = 0;

					$this->ordermodel->set_step($ordr_idxx,25,$data);

					//받는 사람 쿠폰 SMS 데이터
					if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
						$order_count = 0;
						foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
							$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
							$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
							$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
							$order_count					=$order_count+1;
						}

						$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
						$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
						$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

					}

					//주문자 쿠폰 SMS 데이터
					if(count($this->coupon_order_sms['order_cellphone']) > 0){
						$order_count = 0;
						foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
							$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
							$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
							$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
							$order_count					=$order_count+1;
						}

						$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
						$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
						$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

					}
					//debug_var(array("payment",$commonSmsData));
					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}


					$log = "LGU+ 결제 확인". chr(10)."[" .$res_code . $res_msg . "]" . chr(10). implode(chr(10),$data);

					if( preg_match('/account/',$orders['payment']) ){
						$FINANCENAME = mb_convert_encoding($xpay->Response("LGD_FINANCENAME",0), "UTF-8", "EUC-KR");
						$log .= chr(10) . "계좌이체 은행:" . $FINANCENAME;
					}

					$add_log = "";
					if($orders['orign_order_seq']) $add_log = "[재주문]";
					if($orders['admin_order']) $add_log = "[관리자주문]";
					if($orders['person_seq']) $add_log = "[개인결제]";
					$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
					$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

					// 계좌이체 결제의 경우 현금영수증
					if( preg_match('/account/',$orders['payment']) ){
						$result = typereceipt_setting($orders['order_seq']);
					}

			        // 해당 주문 상품의 출고예약량 업데이트
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
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}
		        }
			}else if($res_code != 'S007'){
				if($res_code == "XC01"){
					$log = "LG 결제 실패". chr(10)."[" . $xpay->Response("LGD_RESPMSG",0) . "]";
					$log_title =  '중복결제['.$xpay->Response("LGD_RESPMSG",0).']';
				}else{
					// 결제실패
					$this->ordermodel->set_step($ordr_idxx,99);
					$log = "LG 결제 실패". chr(10)."[" . $xpay->Response("LGD_RESPMSG",0) . "]";
					$log_title =  '결제실패['.$xpay->Response("LGD_RESPMSG",0).']';
				}
				$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

			}

			## 로그저장
			$pg_log['pg']			= $this->config_system['pgCompany'];
			$pg_log['res_cd'] 		= $xpay->Response("LGD_RESPCODE",0);
			$pg_log['res_msg'] 		= $xpay->Response("LGD_RESPMSG",0);
			$pg_log['order_seq'] 	= $xpay->Response("LGD_OID",0);
			$pg_log['amount'] 		= $xpay->Response("LGD_TRANSAMOUNT",0);
			$pg_log['tno'] 			= $xpay->Response("LGD_TID",0);
			$pg_log['app_time'] 	= $xpay->Response("LGD_PAYDATE",0);
			$pg_log['bank_code'] 	= $xpay->Response("LGD_FINANCECODE",0);
			$pg_log['bank_name'] 	= $xpay->Response("LGD_FINANCENAME",0);
			$pg_log['escw_yn'] 		= $xpay->Response("LGD_ESCROWYN",0);
			$pg_log['quota'] 		= $xpay->Response("LGD_CARDINSTALLMONTH",0);
			$pg_log['noinf'] 		= $xpay->Response("LGD_CARDNOINTYN",0);
			$pg_log['card_cd'] 		= $xpay->Response("LGD_FINANCEAUTHNUM",0);
			$pg_log['depositor'] 	= $xpay->Response("LGD_PAYER",0);					//예금주
			$pg_log['account']		= $xpay->Response("LGD_ACCOUNTNUM",0);				//가상계좌번호
			$pg_log['biller'] 		= $xpay->Response("LGD_BUYER",0);					//구매자
	    	foreach($pg_log as $k => $v){
				$v = trim($v);
				$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
			}
	        $pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);

	        pageRedirect('../order/complete?no='.$ordr_idxx,'','parent');
	    }
	}

	public function lg_return()
	{
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$pg = config_load($this->config_system['pgCompany']);

		$HTTP_POST_VARS 		 = $_POST;
	    $LGD_RESPCODE            = $HTTP_POST_VARS["LGD_RESPCODE"];             // 응답코드: 0000(성공) 그외 실패
	    $LGD_RESPMSG             = $HTTP_POST_VARS["LGD_RESPMSG"];              // 응답메세지
	    $LGD_MID                 = $HTTP_POST_VARS["LGD_MID"];                  // 상점아이디
	    $LGD_OID                 = $HTTP_POST_VARS["LGD_OID"];                  // 주문번호
	    $LGD_AMOUNT              = $HTTP_POST_VARS["LGD_AMOUNT"];               // 거래금액
	    $LGD_TID                 = $HTTP_POST_VARS["LGD_TID"];                  // LG유플러스에서 부여한 거래번호
	    $LGD_PAYTYPE             = $HTTP_POST_VARS["LGD_PAYTYPE"];              // 결제수단코드
	    $LGD_PAYDATE             = $HTTP_POST_VARS["LGD_PAYDATE"];              // 거래일시(승인일시/이체일시)
	    $LGD_HASHDATA            = $HTTP_POST_VARS["LGD_HASHDATA"];             // 해쉬값
	    $LGD_FINANCECODE         = $HTTP_POST_VARS["LGD_FINANCECODE"];          // 결제기관코드(은행코드)
	    $LGD_FINANCENAME         = $HTTP_POST_VARS["LGD_FINANCENAME"];          // 결제기관이름(은행이름)
	    $LGD_ESCROWYN            = $HTTP_POST_VARS["LGD_ESCROWYN"];             // 에스크로 적용여부
	    $LGD_TIMESTAMP           = $HTTP_POST_VARS["LGD_TIMESTAMP"];            // 타임스탬프
	    $LGD_ACCOUNTNUM          = $HTTP_POST_VARS["LGD_ACCOUNTNUM"];           // 계좌번호(무통장입금)
	    $LGD_CASTAMOUNT          = $HTTP_POST_VARS["LGD_CASTAMOUNT"];           // 입금총액(무통장입금)
	    $LGD_CASCAMOUNT          = $HTTP_POST_VARS["LGD_CASCAMOUNT"];           // 현입금액(무통장입금)
	    $LGD_CASFLAG             = $HTTP_POST_VARS["LGD_CASFLAG"];              // 무통장입금 플래그(무통장입금) - 'R':계좌할당, 'I':입금, 'C':입금취소
	    $LGD_CASSEQNO            = $HTTP_POST_VARS["LGD_CASSEQNO"];             // 입금순서(무통장입금)
	    $LGD_CASHRECEIPTNUM      = $HTTP_POST_VARS["LGD_CASHRECEIPTNUM"];       // 현금영수증 승인번호
	    $LGD_CASHRECEIPTSELFYN   = $HTTP_POST_VARS["LGD_CASHRECEIPTSELFYN"];    // 현금영수증자진발급제유무 Y: 자진발급제 적용, 그외 : 미적용
	    $LGD_CASHRECEIPTKIND     = $HTTP_POST_VARS["LGD_CASHRECEIPTKIND"];      // 현금영수증 종류 0: 소득공제용 , 1: 지출증빙용
		$LGD_PAYER     			 = $HTTP_POST_VARS["LGD_PAYER"];      			// 입금자명
	    $LGD_BUYER               = $HTTP_POST_VARS["LGD_BUYER"];                // 구매자
	    $LGD_PRODUCTINFO         = $HTTP_POST_VARS["LGD_PRODUCTINFO"];          // 상품명
	    $LGD_BUYERID             = $HTTP_POST_VARS["LGD_BUYERID"];              // 구매자 ID
	    $LGD_BUYERADDRESS        = $HTTP_POST_VARS["LGD_BUYERADDRESS"];         // 구매자 주소
	    $LGD_BUYERPHONE          = $HTTP_POST_VARS["LGD_BUYERPHONE"];           // 구매자 전화번호
	    $LGD_BUYEREMAIL          = $HTTP_POST_VARS["LGD_BUYEREMAIL"];           // 구매자 이메일
	    $LGD_BUYERSSN            = $HTTP_POST_VARS["LGD_BUYERSSN"];             // 구매자 주민번호
	    $LGD_PRODUCTCODE         = $HTTP_POST_VARS["LGD_PRODUCTCODE"];          // 상품코드
	    $LGD_RECEIVER            = $HTTP_POST_VARS["LGD_RECEIVER"];             // 수취인
	    $LGD_RECEIVERPHONE       = $HTTP_POST_VARS["LGD_RECEIVERPHONE"];        // 수취인 전화번호
	    $LGD_DELIVERYINFO        = $HTTP_POST_VARS["LGD_DELIVERYINFO"];         // 배송지

		$LGD_MERTKEY = $pg['merchantKey']; //LG유플러스에서 발급한 상점키로 변경해 주시기 바랍니다.

	    $LGD_HASHDATA2 = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$LGD_MERTKEY);

	    /*
	     * 상점 처리결과 리턴메세지
	     *
	     * OK  : 상점 처리결과 성공
	     * 그외 : 상점 처리결과 실패
	     *
	     * ※ 주의사항 : 성공시 'OK' 문자이외의 다른문자열이 포함되면 실패처리 되오니 주의하시기 바랍니다.
	     */
	    //$resultMSG = "결제결과 상점 DB처리(LGD_CASNOTEURL) 결과값을 입력해 주시기 바랍니다.";
	    $resultMSG =  mb_convert_encoding("해쉬값 불일치",'EUC-KR','UTF-8');


	    if ( $LGD_HASHDATA2 == $LGD_HASHDATA ) { //해쉬값 검증이 성공이면
	        if ( "0000" == $LGD_RESPCODE ){ //결제가 성공이면
	        	if( "R" == $LGD_CASFLAG ) {
					$resultMSG = "OK";
	        	}else if( "I" == $LGD_CASFLAG ) {
	        		$orders = $this->ordermodel->get_order($LGD_OID);
					if($orders['step'] < '25' || $orders['step'] > '85'){ // 결제이후 프로세스 진행 이후에는 결제확인 처리 하지 않음
						$this->coupon_reciver_sms = array();
						$this->coupon_order_sms = array();
						$order_count = 0;

	 					$this->ordermodel->set_step($LGD_OID,25,$data);

						//받는 사람 쿠폰 SMS 데이터
						if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
								$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
								$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
								$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
							$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
							$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

						}

						//주문자 쿠폰 SMS 데이터
						if(count($this->coupon_order_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
								$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
								$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
								$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
							$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
							$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

						}
						//debug_var(array("payment",$commonSmsData));
						if(count($commonSmsData) > 0){
							commonSendSMS($commonSmsData);
						}

					}
					$log = "LGU+ 결제 확인". chr(10)."[" .$LGD_RESPCODE . $LGD_RESPMSG . "]" . chr(10). implode(chr(10),$data);

					$add_log = "";
					if($orders['orign_order_seq']) $add_log = "[재주문]";
					if($orders['admin_order']) $add_log = "[관리자주문]";
					if($orders['person_seq']) $add_log = "[개인결제]";
					$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
					$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,$log);

					// 가상계좌 결제의 경우 현금영수증
					if( $orders['step'] < '25' || $orders['step'] > '85' ){
						$result = typereceipt_setting($orders['order_seq']);
					}

		        	// 출고량 업데이트를 위한 변수선언
					$r_reservation_goods_seq = array();

			   		$result_option = $this->ordermodel->get_item_option($LGD_OID);
			   		$result_suboption = $this->ordermodel->get_item_suboption($LGD_OID);
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
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}

					// 결제확인 sms발송
					if( $orders['sms_25_YN'] != 'Y'){

						$params['shopName'] = $this->config_basic['shopName'];
						$params['ordno']	= $orders['order_seq'];
						$params['user_name'] = $orders['order_user_name'];

						if($orders['order_cellphone']){
							//$commonSmsData = array();
							$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
							$commonSmsData['settle']['params'][] = $params;
							$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
							commonSendSMS($commonSmsData);

							//sendSMS($orders['order_cellphone'], 'settle', '', $params,'','admin');
						}

						// 결제확인메일발송
						send_mail_step25($orders['order_seq']);

						$this->db->where('order_seq', $orders['order_seq']);
						$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
					}



	            	$resultMSG = "OK";
	        	}else if( "C" == $LGD_CASFLAG ) {

					$log_title =  '결제실패['.$LGD_RESPCODE.']';
					$this->ordermodel->set_log($LGD_OID,'pay',$orders['order_user_name'],$log_title,'');

	            	//if( 무통장 입금취소 성공 상점처리결과 성공 )
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

	    ## 로그저장
        $pg_log['pg']			= $this->config_system['pgCompany'];
        $pg_log['res_cd'] 		= $LGD_RESPCODE;
        $pg_log['res_msg'] 		= $LGD_RESPMSG;
        $pg_log['order_seq'] 	= $LGD_OID;
        $pg_log['amount'] 		= $LGD_CASCAMOUNT;
        $pg_log['tno'] 			= $LGD_TID;
        $pg_log['app_time'] 	= $LGD_PAYDATE;
        $pg_log['bank_code'] 	= $LGD_FINANCECODE;
        $pg_log['bank_name'] 	= $LGD_FINANCENAME;
        $pg_log['escw_yn'] 		= $LGD_ESCROWYN;
        $pg_log['account'] 		= $LGD_ACCOUNTNUM;
        $pg_log['depositor'] 	= $LGD_PAYER;
        $pg_log['biller'] 		= $LGD_BUYER;
		foreach($pg_log as $k => $v){
			$v = trim($v);
			$pg_log[$k] = mb_convert_encoding($v,"UTF-8","EUC-KR");
		}
        $pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);

	    echo $resultMSG;

	}

	public function allat()
	{
		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');
		$pg = config_load($this->config_system['pgCompany']);
		$order_seq = $_POST['allat_order_no'];
		$orders = $this->ordermodel->get_order($order_seq);

		// 올앳관련 함수 Include
		//----------------------
		include  dirname(__FILE__)."/../../pg/allat/allatutil.php";
		//Request Value Define
		//----------------------

		/********************* Service Code *********************/
		$at_cross_key 	= $pg['merchantKey'];    //설정필요 [사이트 참조 - http://www.allatpay.com/servlet/AllatBiz/support/sp_install_guide_scriptapi.jsp#shop]
		$at_shop_id   	= $pg['mallCode'];       //설정필요
		$at_amt			= $orders['settleprice'];                             //결제 금액을 다시 계산해서 만들어야 함(해킹방지)
									 //( session, DB 사용 )
		/*********************************************************/

		// 요청 데이터 설정
		//----------------------

		$at_data   = "allat_shop_id=".$at_shop_id.
		   "&allat_amt=".$at_amt.
		   "&allat_enc_data=".$_POST["allat_enc_data"].
		   "&allat_cross_key=".$at_cross_key;
		// 올앳 결제 서버와 통신 : ApprovalReq->통신함수, $at_txt->결과값
		//----------------------------------------------------------------
		$at_txt = ApprovalReq($at_data,"SSL");
		$at_txt = mb_convert_encoding($at_txt, "UTF-8", "EUC-KR");

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
		/*
		if( $pg['mallCode'] == 'FM_pgfreete2' ) $sucess_code = "0001";
		else $sucess_code = "0000";
		*/

		$sucess_code = "0000";

		if( !strcmp($REPLYCD,$sucess_code) ){
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

			// 주문 결제수단 업데이트
			$use_pay_method = $PAY_TYPE;
			$escw_yn 		= $ESCROW_YN;
			$order_seq 		= $ORDER_NO;
			$r_use_pay_method = array(
					'CARD'=>'card',
					'ABANK'=>'account',
					'VBANK'=>'virtual',
					'HP'=>'cellphone'
			);
			if($use_pay_method) $order_payment = $r_use_pay_method[$use_pay_method];
			if($escw_yn == 'Y' && $order_payment) $order_payment = 'escrow_'.$order_payment;
			if($order_payment){
				$query = "update fm_order set payment=? where order_seq=?";
				$this->db->query($query,array($order_payment,$order_seq));
			}

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
			$pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);

			// 주문 상품 재고 체크
			$runout = false;
			$cfg['order'] = config_load('order');

			$result = $this->ordermodel->get_item_option($order_seq);
			$data_item_option = $result;
			$result_option = $result;
			$result = $this->ordermodel->get_item_suboption($order_seq);
			$result_suboption = $result;

			// 출고량 업데이트를 위한 변수선언
			$r_reservation_goods_seq = array();

			if( $runout == false ){
	   			// 주문서 정보 가져오기
				$orders = $this->ordermodel->get_order($order_seq);

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

				//프로모션코드 상품/배송비 할인 사용처리
				$this->promotionmodel->setPromotionpayment($orders);

	   			// 장바구니 비우기
				if( $orders['mode'] ) $this->cartmodel->delete_mode($orders['mode']);

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
	   		}

	   		if( ($orders['payment'] == 'virtual' ||  $orders['payment'] == 'escrow_virtual') && $runout == false ){
	   			$data = array(
					'virtual_account'	=> $BANK_NM . " " .$ACCOUNT_NO . " ".$ACCOUNT_NM,
	   				'virtual_date'		=> $INCOME_LIMIT_YMD,
					'pg_approval_number' => $SEQ_NO,
					'pg_transaction_number' => $APPROVAL_NO
				);

				$this->ordermodel->set_step($order_seq,15,$data);
				$log = "올엣 가상계좌 주문접수". chr(10)."[" .$REPLYCD . $REPLYMSG . "]" . chr(10). implode(chr(10),$data);


				$add_log = "";
				$etc_log = "";
				if($orders['orign_order_seq']) $add_log = "[재주문]";
				if($orders['admin_order']) $add_log = "[관리자주문]";
				if($orders['person_seq']) $add_log = "[개인결제]";
				$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")".$etc_log;
				$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],$log_title,$log);

	   		}

	   		if( $runout == false && $orders['payment'] != 'virtual' &&  $orders['payment'] != 'escrow_virtual' )
	   		{
	   			$data = array(
	   				'pg_transaction_number' => $SEQ_NO,
	   				'pg_approval_number' => $APPROVAL_NO
	   			);

				$this->coupon_reciver_sms = array();
				$this->coupon_order_sms = array();
				$order_count = 0;

				$this->ordermodel->set_step($order_seq,25,$data);

				//받는 사람 쿠폰 SMS 데이터
				if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
						$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
						$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
						$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
					$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
					$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

				}

				//주문자 쿠폰 SMS 데이터
				if(count($this->coupon_order_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
						$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
						$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
						$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
					$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
					$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

				}
				//debug_var(array("payment",$commonSmsData));
				if(count($commonSmsData) > 0){
					commonSendSMS($commonSmsData);
				}

				$log = "올엣 결제 확인". chr(10)."[" .$REPLYCD . $REPLYMSG . "]" . chr(10). implode(chr(10),$data);
				if( $orders['payment'] == 'account' )
				{
					$log .= chr(10) . "계좌이체 은행:" . $BANK_NM . " " .$ACCOUNT_NO;
				}

				$add_log = "";
				if($orders['orign_order_seq']) $add_log = "[재주문]";
				if($orders['admin_order']) $add_log = "[관리자주문]";
				if($orders['person_seq']) $add_log = "[개인결제]";
				$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
				$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],$log_title,$log);

				// 계좌이체 결제의 경우 현금영수증
				if( preg_match('/account/',$orders['payment']) ){
					$result = typereceipt_setting($orders['order_seq']);
				}
	   		}

	   		// 출고예약량 업데이트
	   		foreach($r_reservation_goods_seq as $goods_seq){
	   			$this->goodsmodel->modify_reservation_real($goods_seq);
	   		}

		}else{
			// reply_cd 가 "0000" 아닐때는 에러 (자세한 내용은 매뉴얼참조)
			// reply_msg 는 실패에 대한 메세지
			$this->ordermodel->set_step($order_seq,99);

			$log_title =  '결제실패['.$REPLYCD.']';
			$log = "올엣 결제 실패". chr(10)."[" .$REPLYCD . $REPLYMSG . "]";
			$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],$log_title,$log);
		}

		pageRedirect('../order/complete?no='.$order_seq,'','parent');
	}

	public function allat_return()
	{
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');

		$pg = config_load($this->config_system['pgCompany']);
		$at_cross_key 	= $pg['merchantKey'];

		$shopid = $_POST['shop_id']; // 상점ID
		$order_seq = $_POST['order_no']; // 주문번호
		$tx_seq_no = $_POST['tx_seq_no']; // 거래일련번호
		$account_no = $_POST['account_no']; // 가상계좌 계좌번호
		$bank_cd = $_POST['bank_cd']; // 가상계좌 은행코드
		$common_bank_cd = $_POST['common_bank_cd']; // 가상계좌 공동은행코드
		$apply_ymdhms = $_POST['apply_ymdhms']; // 승인요청일
		$approval_ymdhms = $_POST['approval_ymdhms']; // 가상계좌 채번일
		$income_ymdhms = $_POST['income_ymdhms']; // 가상계좌 입금일
		$apply_amt = $_POST['apply_amt']; // 채번금액
		$income_amt = $_POST['income_amt']; // 입금금액
		$income_account_nm = mb_convert_encoding($_POST['income_account_nm'],'UTF-8','EUC-KR'); // 입금자명
		$receipt_seq_no = $_POST['receipt_seq_no']; // 현금영수증 일련번호
		$cash_approval_no = $_POST['cash_approval_no']; // 현금영수증 승인번호
		$noti_currenttimemillis = $_POST['noti_currenttimemillis']; // 입금통보일
		$hash_value = $_POST['hash_value']; // 해쉬 Data

		## 로그 변수 세팅
	    $pg_log['pg']		= $this->config_system['pgCompany'];
		$pg_log['tno']			= $tx_seq_no;
		$pg_log['order_seq'] 	= $order_seq;
		$pg_log['depositor']	= $income_account_nm;
		$pg_log['amount']		= $account_no;
		$pg_log['bank_code']	= $bank_cd;
		$pg_log['account']	= $account_no;
		foreach($pg_log as $k => $v){
			$v = trim($v);
			$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
		}

		## 로그저장
		$pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);


		// 해쉬데이터 검증
		$hash = md5($shopid . $at_cross_key . $order_seq . $noti_currenttimemillis);
		if($hash != $hash_value)
		{
			echo "9999";
			exit;
		}

		// 쇼핑몰 디비 처리
		$data = array(
			'typereceipt'=>2,
			'cash_receipts_no' => $cash_approval_no
		);

		$data_order = $this->ordermodel->get_order($order_seq);

		if($data_order['step'] < '25' || $data_order['step'] > '85'){ // 결제이후 프로세스 진행 이후에는 결제확인 처리 하지 않음
			$this->coupon_reciver_sms = array();
			$this->coupon_order_sms = array();
			$order_count = 0;

			$this->ordermodel->set_step($order_seq,25,$data);

			//받는 사람 쿠폰 SMS 데이터
			if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
				$order_count = 0;
				foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
					$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
					$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
					$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
					$order_count					=$order_count+1;
				}

				$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
				$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
				$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

			}

			//주문자 쿠폰 SMS 데이터
			if(count($this->coupon_order_sms['order_cellphone']) > 0){
				$order_count = 0;
				foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
					$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
					$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
					$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
					$order_count					=$order_count+1;
				}

				$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
				$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
				$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

			}
		}

		$log[] = date("Y-m-d H:i:s");
		$log[] = "입금자명:".$income_account_nm;
		$log[] = "입금금액:".$income_amt;
		$log[] = "은행코드:".$bank_cd;
		$log[] = "가상계좌 입금계좌번호:".$account_no;
		$log[] = "현금영수증 승인번호:".$cash_approval_no;

		$add_log = "";
		if($data_order['orign_order_seq']) $add_log = "[재주문]";
		if($data_order['admin_order']) $add_log = "[관리자주문]";
		if($data_order['person_seq']) $add_log = "[개인결제]";
		$log_title = $add_log."결제확인"."(".$data_order['mpayment'].")";
		$this->ordermodel->set_log($order_seq,'pay','시스템',$log_title,'');

		// 가상계좌 결제의 경우 현금영수증
		if( $data_order['step'] < '25' || $data_order['step'] > '85' ){
			$result = typereceipt_setting($data_order['order_seq']);
		}

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		$result_option = $this->ordermodel->get_item_option($order_seq);
		$result_suboption = $this->ordermodel->get_item_suboption($order_seq);
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
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		// 결제확인 sms발송
		$result = typereceipt_setting($order_seq);
		if( $orders['sms_25_YN'] != 'Y'){
			$params['shopName'] = $this->config_basic['shopName'];
			$params['ordno']	= $data_order['order_seq'];
			$params['user_name'] = $data_order['order_user_name'];
			if($orders['order_cellphone']){
				//$commonSmsData = array();
				$commonSmsData['settle']['phone'][] = $data_order['order_cellphone'];
				$commonSmsData['settle']['params'][] = $params;
				$commonSmsData['settle']['order_seq'][] = $data_order['order_seq'];
				//commonSendSMS($commonSmsData);

				//sendSMS($data_order['order_cellphone'], 'settle', '', $params,'','admin');
			}
			send_mail_step25($data_order['order_seq']);

			//debug_var(array("payment",$commonSmsData));
			if(count($commonSmsData) > 0){
				commonSendSMS($commonSmsData);
			}


			$this->db->where('order_seq', $data_order['order_seq']);
			$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
		}

		echo "0000";
	}

	public function inicis()
	{

		/**************************
		 * 1. 라이브러리 인클루드 *
		 **************************/
		require(dirname(__FILE__)."/../../pg/inicis/libs/INILib.php");
		$HTTP_SESSION_VARS = $_SESSION;

		/***************************************
		 * 2. INIpay50 클래스의 인스턴스 생성 *
		 ***************************************/
		$inipay = new INIpay50;

		/*********************
		 * 3. 지불 정보 설정 *
		 *********************/
		$inipay->SetField("inipayhome", dirname(__FILE__)."/../../pg/inicis"); // 이니페이 홈디렉터리(상점수정 필요)
		$inipay->SetField("type", "securepay");                         // 고정 (절대 수정 불가)
		$inipay->SetField("pgid", "INIphp".$pgid);                      // 고정 (절대 수정 불가)
		$inipay->SetField("subpgip","203.238.3.10");                    // 고정 (절대 수정 불가)
		$inipay->SetField("admin", $HTTP_SESSION_VARS['INI_ADMIN']);    // 키패스워드(상점아이디에 따라 변경)
		$inipay->SetField("debug", "true");                             // 로그모드("true"로 설정하면 상세로그가 생성됨.)
		$inipay->SetField("uid", $uid);                                 // INIpay User ID (절대 수정 불가)
	  	$inipay->SetField("goodname", $goodname);                       // 상품명
		$inipay->SetField("currency", $currency);                       // 화폐단위
		$inipay->SetField("mid", $HTTP_SESSION_VARS['INI_MID']);        // 상점아이디
		$inipay->SetField("rn",  $HTTP_SESSION_VARS['INI_RN']);          // 웹페이지 위변조용 RN값
		$inipay->SetField("price",   $HTTP_SESSION_VARS['INI_PRICE']);		// 가격
		$inipay->SetField("enctype",  $HTTP_SESSION_VARS['INI_ENCTYPE']);// 고정 (절대 수정 불가)

	     /*----------------------------------------------------------------------------------------
	       price 등의 중요데이터는
	       브라우저상의 위변조여부를 반드시 확인하셔야 합니다.

	       결제 요청페이지에서 요청된 금액과
	       실제 결제가 이루어질 금액을 반드시 비교하여 처리하십시오.

	       설치 메뉴얼 2장의 결제 처리페이지 작성부분의 보안경고 부분을 확인하시기 바랍니다.
	       적용참조문서: 이니시스홈페이지->가맹점기술지원자료실->기타자료실 의
	                      '결제 처리 페이지 상에 결제 금액 변조 유무에 대한 체크' 문서를 참조하시기 바랍니다.
	       예제)
	       원 상품 가격 변수를 OriginalPrice 하고  원 가격 정보를 리턴하는 함수를 Return_OrgPrice()라 가정하면
	       다음 같이 적용하여 원가격과 웹브라우저에서 Post되어 넘어온 가격을 비교 한다.

			$OriginalPrice = Return_OrgPrice();
			$PostPrice = $HTTP_SESSION_VARS['INI_PRICE'];
			if ( $OriginalPrice != $PostPrice )
			{
				//결제 진행을 중단하고  금액 변경 가능성에 대한 메시지 출력 처리
				//처리 종료
			}

	      ----------------------------------------------------------------------------------------*/
		$buyername =  mb_convert_encoding($buyername,"EUC-KR","UTF-8");
		$recvname  =  mb_convert_encoding($recvname,"EUC-KR","UTF-8");
		$recvaddr  =  mb_convert_encoding($recvaddr,"EUC-KR","UTF-8");
		$goodname  =  mb_convert_encoding($goodname,"EUC-KR","UTF-8");

		if($quotabase){
			$quotabase  =  mb_convert_encoding($quotabase,"EUC-KR","UTF-8");
			$inipay->SetField("quotabase", $quotabase);
		}

		$inipay->SetField("goodname", $goodname);       // 상품명
		$inipay->SetField("buyername", $buyername);       // 구매자 명
		$inipay->SetField("buyertel",  $buyertel);        // 구매자 연락처(휴대폰 번호 또는 유선전화번호)
		$inipay->SetField("buyeremail",$buyeremail);      // 구매자 이메일 주소
		$inipay->SetField("paymethod", $paymethod);       // 지불방법 (절대 수정 불가)
		$inipay->SetField("encrypted", $encrypted);       // 암호문
		$inipay->SetField("sessionkey",$sessionkey);      // 암호문
		$inipay->SetField("url", "http://".$_SERVER['HTTP_HOST']); // 실제 서비스되는 상점 SITE URL로 변경할것
		$inipay->SetField("cardcode", $cardcode);         // 카드코드 리턴
		$inipay->SetField("parentemail", $parentemail);   // 보호자 이메일 주소(핸드폰 , 전화결제시에 14세 미만의 고객이 결제하면  부모 이메일로 결제 내용통보 의무, 다른결제 수단 사용시에 삭제 가능)

		/*-----------------------------------------------------------------*
		 * 수취인 정보 *                                                   *
		 *-----------------------------------------------------------------*
		 * 실물배송을 하는 상점의 경우에 사용되는 필드들이며               *
		 * 아래의 값들은 INIsecurepay.html 페이지에서 포스트 되도록        *
		 * 필드를 만들어 주도록 하십시요.                                  *
		 * 컨텐츠 제공업체의 경우 삭제하셔도 무방합니다.                   *
		 *-----------------------------------------------------------------*/
		$inipay->SetField("recvname",$recvname);	// 수취인 명
		$inipay->SetField("recvtel",$recvtel);		// 수취인 연락처
		$inipay->SetField("recvaddr",$recvaddr);	// 수취인 주소
		$inipay->SetField("recvpostnum",$recvpostnum);  // 수취인 우편번호
		$inipay->SetField("recvmsg",$recvmsg);		// 전달 메세지

		$inipay->SetField("joincard",$joincard);  // 제휴카드코드
		$inipay->SetField("joinexpire",$joinexpire);    // 제휴카드유효기간
		$inipay->SetField("id_customer",$id_customer);    //user_id

		$inipay->SetXPath("INIpay/GoodsInfo/Tax",$comm_vat_mny);
		$inipay->SetXPath("INIpay/GoodsInfo/TaxFree",$comm_free_mny);

		/****************
		 * 4. 지불 요청 *
		 ****************/
		$inipay->startAction();

		/****************************************************************************************************************
		 * 5. 결제  결과
		 *
		 *  1 모든 결제 수단에 공통되는 결제 결과 데이터
		 * 	거래번호 : $inipay->GetResult('TID')
		 * 	결과코드 : $inipay->GetResult('ResultCode') ("00"이면 지불 성공)
		 * 	결과내용 : $inipay->GetResult('ResultMsg') (지불결과에 대한 설명)
		 * 	지불방법 : $inipay->GetResult('PayMethod') (매뉴얼 참조)
		 * 	상점주문번호 : $inipay->GetResult('MOID')
		 *	결제완료금액 : $inipay->GetResult('TotPrice')
		 *
		 * 결제 되는 금액 =>원상품가격과  결제결과금액과 비교하여 금액이 동일하지 않다면
		 * 결제 금액의 위변조가 의심됨으로 정상적인 처리가 되지않도록 처리 바랍니다. (해당 거래 취소 처리)
		 *
		 *
		 *  2. 신용카드,ISP,핸드폰, 전화 결제, 은행계좌이체, OK CASH BAG Point 결제 결과 데이터
		 *      (무통장입금 , 문화 상품권 포함)
		 * 	이니시스 승인날짜 : $inipay->GetResult('ApplDate') (YYYYMMDD)
		 * 	이니시스 승인시각 : $inipay->GetResult('ApplTime') (HHMMSS)
		 *
		 *  3. 신용카드 결제 결과 데이터
	         *
		 * 	신용카드 승인번호 : $inipay->GetResult('ApplNum')
		 * 	할부기간 : $inipay->GetResult('CARD_Quota')
		 * 	무이자할부 여부 : $inipay->GetResult('CARD_Interest') ("1"이면 무이자할부)
		 * 	신용카드사 코드 : $inipay->GetResult('CARD_Code') (매뉴얼 참조)
		 * 	카드발급사 코드 : $inipay->GetResult('CARD_BankCode') (매뉴얼 참조)
		 * 	본인인증 수행여부 : $inipay->GetResult('CARD_AuthType') ("00"이면 수행)
		 *      각종 이벤트 적용 여부 : $inipay->GetResult('EventCode')
		 *
		 *      ** 달러결제 시 통화코드와  환률 정보 **
		 *	해당 통화코드 : $inipay->GetResult('OrgCurrency')
		 *	환율 : $inipay->GetResult('ExchangeRate')
		 *
		 *      아래는 "신용카드 및 OK CASH BAG 복합결제" 또는"신용카드 지불시에 OK CASH BAG적립"시에 추가되는 데이터
		 * 	OK Cashbag 적립 승인번호 : $inipay->GetResult('OCB_SaveApplNum')
		 * 	OK Cashbag 사용 승인번호 : $inipay->GetResult('OCB_PayApplNum')
		 * 	OK Cashbag 승인일시 : $inipay->GetResult('OCB_ApplDate') (YYYYMMDDHHMMSS)
		 * 	OCB 카드번호 : $inipay->GetResult('OCB_Num')
		 * 	OK Cashbag 복합결재시 신용카드 지불금액 : $inipay->GetResult('CARD_ApplPrice')
		 * 	OK Cashbag 복합결재시 포인트 지불금액 : $inipay->GetResult('OCB_PayPrice')
		 *
		 * 4. 실시간 계좌이체 결제 결과 데이터
		 *
		 * 	은행코드 : $inipay->GetResult('ACCT_BankCode')
		 *	현금영수증 발행결과코드 : $inipay->GetResult('CSHR_ResultCode')
		 *	현금영수증 발행구분코드 : $inipay->GetResult('CSHR_Type')
		 *														*
		 * 5. OK CASH BAG 결제수단을 이용시에만  결제 결과 데이터
		 * 	OK Cashbag 적립 승인번호 : $inipay->GetResult('OCB_SaveApplNum')
		 * 	OK Cashbag 사용 승인번호 : $inipay->GetResult('OCB_PayApplNum')
		 * 	OK Cashbag 승인일시 : $inipay->GetResult('OCB_ApplDate') (YYYYMMDDHHMMSS)
		 * 	OCB 카드번호 : $inipay->GetResult('OCB_Num')
		 *
	         * 6. 무통장 입금 결제 결과 데이터							                        *
		 * 	가상계좌 채번에 사용된 주민번호 : $inipay->GetResult('VACT_RegNum')              					*
		 * 	가상계좌 번호 : $inipay->GetResult('VACT_Num')                                					*
		 * 	입금할 은행 코드 : $inipay->GetResult('VACT_BankCode')                           					*
		 * 	입금예정일 : $inipay->GetResult('VACT_Date') (YYYYMMDD)                      					*
		 * 	송금자 명 : $inipay->GetResult('VACT_InputName')                                  					*
		 * 	예금주 명 : $inipay->GetResult('VACT_Name')                                  					*
		 *														*
		 * 7. 핸드폰, 전화 결제 결과 데이터( "실패 내역 자세히 보기"에서 필요 , 상점에서는 필요없는 정보임)             *
	         * 	전화결제 사업자 코드 : $inipay->GetResult('HPP_GWCode')                        					*
		 *														*
		 * 8. 핸드폰 결제 결과 데이터								                        *
		 * 	휴대폰 번호 : $inipay->GetResult('HPP_Num') (핸드폰 결제에 사용된 휴대폰번호)       					*
		 *														*
		 * 9. 전화 결제 결과 데이터								                        *
	   * 	전화번호 : $inipay->GetResult('ARSB_Num') (전화결제에  사용된 전화번호)      						*
	   * 														*
	   * 10. 문화 상품권 결제 결과 데이터							                        *
	   * 	컬쳐 랜드 ID : $inipay->GetResult('CULT_UserID')	                           					*
	   *														*
	   * 11. K-merce 상품권 결제 결과 데이터 (K-merce ID, 틴캐시 아이디 공통사용)                                     *
	   *      K-merce ID : $inipay->GetResult('CULT_UserID')                                                                       *
	   *                                                                                                              *
	   * 12. 모든 결제 수단에 대해 결제 실패시에만 결제 결과 데이터 							*
	   * 	에러코드 : $inipay->GetResult('ResultErrorCode')                             					*
	   * 														*
	   * 13.현금영수증 발급 결과코드 (은행계좌이체시에만 리턴)							*
	   *    $inipay->GetResult('CSHR_ResultCode')                                                                                     *
	   *                                                                                                              *
	   * 14.틴캐시 잔액 데이터                                							*
	   *    $inipay->GetResult('TEEN_Remains')                                           	                                *
	   *	틴캐시 ID : $inipay->GetResult('CULT_UserID')													*
	   * 15.게임문화 상품권							*
	   *	사용 카드 갯수 : $inipay->GetResult('GAMG_Cnt')                 					        *
	   *														*
	   ****************************************************************************************************************/

		$tno			= $inipay->GetResult('TID');
		$res_cd			= $inipay->GetResult('ResultCode');
		$res_msg		= $inipay->GetResult('ResultMsg');
		$pay_method		= $inipay->GetResult('PayMethod');
		$ordr_idxx		= $inipay->GetResult('MOID');
		$settleprice	= $inipay->GetResult('TotPrice');
		$app_time		= $inipay->GetResult('ApplDate') . " " . $inipay->GetResult('ApplTime');
		$app_no			= $inipay->GetResult('ApplNum');
		$noinf			= $inipay->GetResult('CARD_Interest');
		$card_cd		= $inipay->GetResult('CARD_Code');
		$account		= $inipay->GetResult('VACT_Num');
		$bank_code		= $inipay->GetResult('VACT_BankCode');
		$va_date		= $inipay->GetResult('VACT_Date');
		$depositor		= $inipay->GetResult('VACT_Name');
		$commid			= $inipay->GetResult('HPP_GWCode');
		$mobile_no		= $inipay->GetResult('HPP_Num');
		$quota			= $inipay->GetResult('CARD_Quota');

		// 주문서 정보 가져오기
		$this->load->model('ordermodel');
		$orders = $this->ordermodel->get_order($ordr_idxx);

		// 주문 결제수단 업데이트
		$escw_yn = 'N';
		if( preg_match('/escrow/',$orders['payment']) ) $escw_yn = 'Y';
		$use_pay_method = $pay_method;
		$order_seq 		= $ordr_idxx;
		$r_use_pay_method = array(
				'VCard'=>'card',
				'Card'=>'card',
				'DirectBank'=>'account',
				'VBank'=>'virtual',
				'HPP'=>'cellphone'
		);
		if($use_pay_method) $order_payment = $r_use_pay_method[$use_pay_method];
		if($escw_yn == 'Y' && $order_payment) $order_payment = 'escrow_'.$order_payment;
		if($order_payment){
			$query = "update fm_order set payment=? where order_seq=?";
			$this->db->query($query,array($order_payment,$order_seq));
		}

		// 로그 저장
		$pg_log['pg']			= $this->config_system['pgCompany'];
		$pg_log['tno'] 			= $tno;
		$pg_log['order_seq'] 	= $ordr_idxx;
		$pg_log['amount'] 		= $settleprice;
		$pg_log['app_time'] 	= $app_time;
		$pg_log['app_no'] 		= $app_no;
		$pg_log['card_cd'] 		= $card_cd;
		$pg_log['card_name'] 	= $CARD_NM;
		$pg_log['noinf'] 		= $noinf;
		$pg_log['quota'] 		= $quota;
		$pg_log['bank_name'] 	= $BANK_NM;
		$pg_log['bank_code'] 	= $bank_code;
		$pg_log['depositor'] 	= $depositor;
		$pg_log['account'] 		= $account;
		$pg_log['commid'] 		= $mobile_no;
		$pg_log['va_date'] 		= $va_date;
		$pg_log['res_cd'] 		= $res_cd;
		$pg_log['res_msg'] 		= $res_msg;
		foreach($pg_log as $k => $v){
			$v = trim($v);
			$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
		}
		$pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);

		$this->load->model('cartmodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');
		$pg = config_load($this->config_system['pgCompany']);

		// 주문 상품 재고 체크
		$runout = false;
		$cfg['order'] = config_load('order');
		$result = $this->ordermodel->get_item_option($ordr_idxx);
		$data_item_option = $result;
		$result_option = $result;
		$result = $this->ordermodel->get_item_suboption($ordr_idxx);
		$result_suboption = $result;

		// 주문 처리
		if($runout == false && $res_cd == "00" ):


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

			//프로모션코드 상품/배송비 할인 사용처리
			$this->promotionmodel->setPromotionpayment($orders);

			// 장바구니 비우기
			if( $orders['mode'] ){
				$this->cartmodel->delete_mode($orders['mode']);
			}

			if ( $pay_method == "VBank")
			{
				$va_date = $inipay->GetResult('VACT_Date');
				$arr = code_load('inicisBankCode',$inipay->GetResult('VACT_BankCode'));
				$bank_name = $arr[0]['value'];
		 		$virtual_account =  $bank_name." ".$inipay->GetResult('VACT_Num').' '.$inipay->GetResult('VACT_InputName');

				$data = array(
					'virtual_account' => $virtual_account,
					'virtual_date' => $va_date,
					'pg_transaction_number' => $tno
				);

				$this->ordermodel->set_step($ordr_idxx,15,$data);
				$log = "이니시스 가상계좌 주문접수". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);

				$add_log = "";
				$etc_log = "";
				if($orders['orign_order_seq']) $add_log = "[재주문]";
				if($orders['admin_order']) $add_log = "[관리자주문]";
				if($orders['person_seq']) $add_log = "[개인결제]";
				$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")".$etc_log;
				$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

				$mail_step = 15;
			}
			else
			{
				$app_no = $inipay->GetResult('ApplNum');
				$data = array(
					'pg_transaction_number' => $tno,
					'pg_approval_number' => $app_no
				);

				$this->coupon_reciver_sms = array();
				$this->coupon_order_sms = array();
				$order_count = 0;

				$this->ordermodel->set_step($ordr_idxx,25,$data);
				$log = "이니시스 결제 확인". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);


				//받는 사람 쿠폰 SMS 데이터
				if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
						$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
						$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
						$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
					$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
					$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

				}

				//주문자 쿠폰 SMS 데이터
				if(count($this->coupon_order_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
						$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
						$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
						$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
					$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
					$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

				}
				//debug_var(array("payment",$commonSmsData));
				if(count($commonSmsData) > 0){
					commonSendSMS($commonSmsData);
				}


				// 계좌이체 결제의 경우 현금영수증
				if( preg_match('/account/',$orders['payment']) ){
					$result = typereceipt_setting($orders['order_seq']);
				}

				$add_log = "";
				if($orders['orign_order_seq']) $add_log = "[재주문]";
				if($orders['admin_order']) $add_log = "[관리자주문]";
				if($orders['person_seq']) $add_log = "[개인결제]";
				$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
				$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

				$mail_step = 25;
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
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}

		endif;

		/*******************************************************************
		 * 7. DB연동 실패 시 강제취소                                      *
		 *                                                                 *
		 * 지불 결과를 DB 등에 저장하거나 기타 작업을 수행하다가 실패하는  *
		 * 경우, 아래의 코드를 참조하여 이미 지불된 거래를 취소하는 코드를 *
		 * 작성합니다.                                                     *
		 *******************************************************************/
		$cancelFlag = "false";
		if($res_cd != "00"):

			$log = "이니시스 결제 실패". chr(10)."[" .$res_cd . $res_msg . "]";
			$log_title =  '결제실패['.$res_cd.']';
			$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

			$cancelFlag = "true";
		endif;

		// $cancelFlag를 "ture"로 변경하는 condition 판단은 개별적으로
		// 수행하여 주십시오.
		if($cancelFlag == "true")
		{
			$TID = $inipay->GetResult("TID");
			$inipay->SetField("type", "cancel"); // 고정
			$inipay->SetField("tid", $TID); // 고정
			$inipay->SetField("cancelmsg", "DB FAIL"); // 취소사유
			$inipay->startAction();
			if($inipay->GetResult('ResultCode') == "00")
			{
	      		$inipay->MakeTXErrMsg(MERCHANT_DB_ERR,"Merchant DB FAIL");
			}
		}

		if($orders['order_seq']){
			pageRedirect('../order/complete?no='.$orders['order_seq'],'','parent');
		}else{
			pageBack('결제가 실패하였습니다.');
		}
	}

	public function inicis_return()
	{
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		@extract($_GET);
		@extract($_POST);
		@extract($_SERVER);

		$INIpayHome = dirname(__FILE__)."/../../pg/inicis";      // 이니페이 홈디렉터리

		$TEMP_IP = getenv("REMOTE_ADDR");
		$PG_IP  = substr($TEMP_IP,0, 10);

		if( $PG_IP == "203.238.37" || $PG_IP == "210.98.138" )  //PG에서 보냈는지 IP로 체크
		{
			$msg_id = $msg_id;             //메세지 타입
			$no_tid = $no_tid;             //거래번호
			$no_oid = $no_oid;             //상점 주문번호
			$id_merchant = $id_merchant;   //상점 아이디
			$cd_bank = $cd_bank;           //거래 발생 기관 코드
			$cd_deal = $cd_deal;           //취급 기관 코드
			$dt_trans = $dt_trans;         //거래 일자
			$tm_trans = $tm_trans;         //거래 시간
			$no_msgseq = $no_msgseq;       //전문 일련 번호
			$cd_joinorg = $cd_joinorg;     //제휴 기관 코드

			$dt_transbase = $dt_transbase; //거래 기준 일자
			$no_transeq = $no_transeq;     //거래 일련 번호
			$type_msg = $type_msg;         //거래 구분 코드
			$cl_close = $cl_close;         //마감 구분코드
			$cl_kor = $cl_kor;             //한글 구분 코드
			$no_msgmanage = $no_msgmanage; //전문 관리 번호
			$no_vacct = $no_vacct;         //가상계좌번호
			$amt_input = $amt_input;       //입금금액
			$amt_check = $amt_check;       //미결제 타점권 금액
			$nm_inputbank = $nm_inputbank; //입금 금융기관명
			$nm_input = $nm_input;         //입금 의뢰인
			$dt_inputstd = $dt_inputstd;   //입금 기준 일자
			$dt_calculstd = $dt_calculstd; //정산 기준 일자
			$flg_close = $flg_close;       //마감 전화

			//가상계좌채번시 현금영수증 자동발급신청시에만 전달
			$dt_cshr      = $dt_cshr;       //현금영수증 발급일자
			$tm_cshr      = $tm_cshr;       //현금영수증 발급시간
			$no_cshr_appl = $no_cshr_appl;  //현금영수증 발급번호
			$no_cshr_tid  = $no_cshr_tid;   //현금영수증 발급TID

			// 로그 저장
			$pg_log['pg']			= $this->config_system['pgCompany'];
			$pg_log['tno'] 			= $no_tid;
			$pg_log['app_time'] 	= $dt_trans.' '.$tm_trans;
			$pg_log['app_no'] 		= $no_msgseq;
			$pg_log['order_seq'] 	= $no_oid;
			$pg_log['amount'] 		= $amt_input;
			$pg_log['bank_name'] 	= $nm_inputbank;
			$pg_log['bank_code'] 	= $cd_bank;
			$pg_log['depositor'] 	= $nm_input;
			$pg_log['account'] 		= $account;
			$pg_log['va_date'] 		= $dt_inputstd;
			$pg_log['res_cd'] 		= $res_cd;
			$pg_log['res_msg'] 		= $res_msg;
			foreach($pg_log as $k => $v){
				$v = trim($v);
				$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
			}
			$pg_log['regist_date'] = date('Y-m-d H:i:s');
			$this->db->insert('fm_order_pg_log', $pg_log);

			$logfile = fopen( $INIpayHome . "/log/result.log", "a+" );
			fwrite( $logfile,"************************************************");
			fwrite( $logfile,"ID_MERCHANT : ".$id_merchant."\r\n");
			fwrite( $logfile,"NO_TID : ".$no_tid."\r\n");
			fwrite( $logfile,"NO_OID : ".$no_oid."\r\n");
			fwrite( $logfile,"NO_VACCT : ".$no_vacct."\r\n");
			fwrite( $logfile,"AMT_INPUT : ".$amt_input."\r\n");
			fwrite( $logfile,"NM_INPUTBANK : ".$nm_inputbank."\r\n");
			fwrite( $logfile,"NM_INPUT : ".$nm_input."\r\n");
			fwrite( $logfile,"전체 결과값"."\r\n");
			fwrite( $logfile, $msg_id."\r\n");
			fwrite( $logfile, $no_tid."\r\n");
			fwrite( $logfile, $no_oid."\r\n");
			fwrite( $logfile, $id_merchant."\r\n");
			fwrite( $logfile, $cd_bank."\r\n");
			fwrite( $logfile, $dt_trans."\r\n");
			fwrite( $logfile, $tm_trans."\r\n");
			fwrite( $logfile, $no_msgseq."\r\n");
			fwrite( $logfile, $type_msg."\r\n");
			fwrite( $logfile, $cl_close."\r\n");
			fwrite( $logfile, $cl_kor."\r\n");
			fwrite( $logfile, $no_msgmanage."\r\n");
			fwrite( $logfile, $no_vacct."\r\n");
			fwrite( $logfile, $amt_input."\r\n");
			fwrite( $logfile, $amt_check."\r\n");
			fwrite( $logfile, $nm_inputbank."\r\n");
			fwrite( $logfile, $nm_input."\r\n");
			fwrite( $logfile, $dt_inputstd."\r\n");
			fwrite( $logfile, $dt_calculstd."\r\n");
			fwrite( $logfile, $flg_close."\r\n");
			fwrite( $logfile, "\r\n");
			fwrite( $logfile,"************************************************");
			fclose( $logfile );

			// 주문 상품 재고 체크
			$order_seq = $no_oid;
			$orders = $this->ordermodel->get_order($order_seq);
			$runout = false;
			$cfg['order'] = config_load('order');
			$result = $this->ordermodel->get_item_option($order_seq);
			$result_option = $result;
			$data_item_option = $result;
			$result = $this->ordermodel->get_item_suboption($order_seq);
			$result_suboption = $result;

			if($runout) exit;
			$data = array();
			$data = array(
				'pg_transaction_number' => $no_tid
			);

			if($orders['step'] < '25' || $orders['step'] > '85'){ // 결제이후 프로세스 진행 이후에는 결제확인 처리 하지 않음
				$this->coupon_reciver_sms = array();
				$this->coupon_order_sms = array();
				$order_count = 0;

				$this->ordermodel->set_step($order_seq,25,$data);
				//받는 사람 쿠폰 SMS 데이터
				if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
						$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
						$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
						$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
					$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
					$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

				}

				//주문자 쿠폰 SMS 데이터
				if(count($this->coupon_order_sms['order_cellphone']) > 0){
					$order_count = 0;
					foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
						$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
						$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
						$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
					$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
					$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

				}

			}

			$add_log = "";
			if($orders['orign_order_seq']) $add_log = "[재주문]";
			if($orders['admin_order']) $add_log = "[관리자주문]";
			if($orders['person_seq']) $add_log = "[개인결제]";
			$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
			$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],$log_title,'');

			// 가상계좌 결제의 경우 현금영수증
			if( $orders['step'] < '25' || $orders['step'] > '85' ){
				$result = typereceipt_setting($orders['order_seq']);
			}

			$mail_step = 25;

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
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}


			echo "OK";                        // 절대로 지우지마세요

			// 결제확인메일/sms 발송
			send_mail_step25($orders['order_seq']);
			if( $orders['sms_25_YN'] != 'Y'){
				$params['shopName'] = $this->config_basic['shopName'];
				$params['ordno']	= $orders['order_seq'];
				$params['user_name'] = $orders['order_user_name'];
				if($orders['order_cellphone']){

					//$commonSmsData = array();
					$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['settle']['params'][] = $params;
					$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
					//sendSMS($orders['order_cellphone'], 'settle', '', $params,'','admin');
				}

				//debug_var(array("payment",$commonSmsData));
				if(count($commonSmsData) > 0){
					commonSendSMS($commonSmsData);
				}

				$this->db->where('order_seq', $orders['order_seq']);
				$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
			}
		}
	}

	function admin_mail_sms($order_seq)
	{
		$this->load->model('ordermodel');
		$orders = $this->ordermodel->get_order($order_seq);

		$complete_id = $this->session->userdata('complete');
		if($complete_id != $order_seq){
			if($orders['step'] == 15 && $orders['sms_25_YN'] != 'Y') {
				// 주문접수 sms발송
				if( $orders['order_cellphone'] ){
					$params['shopName']		= $this->config_basic['shopName'];
					$params['ordno']		= $order_seq;
					$params['user_name']	 = $orders['order_user_name'];
					$params['bank_account']		= ($orders['payment'] == 'bank')? $orders['bank_account'] : $orders['virtual_account'];

					$commonSmsData = array();
					$commonSmsData['order']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['order']['params'][] = $params;
					$commonSmsData['order']['order_seq'][] = $order_seq;
					commonSendSMS($commonSmsData);

					//sendSMS($orders['order_cellphone'], 'order', '', $params,'','admin');

					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
				}

				// 주문접수메일발송
				send_mail_step15($order_seq);
			}

			if($orders['step'] == 25 && $orders['sms_25_YN'] != 'Y') {
				// 결제확인메일/sms 발송
				send_mail_step25($orders['order_seq']);
				if( $orders['order_cellphone'] ){
					$params['shopName'] = $this->config_basic['shopName'];
					$params['ordno']	= $orders['order_seq'];
					$params['user_name'] = $orders['order_user_name'];

					$commonSmsData = array();
					$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['settle']['params'][] = $params;
					$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
					commonSendSMS($commonSmsData);

					//sendSMS($orders['order_cellphone'], 'settle', '', $params,'','admin');

					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
				}
			}
			$this->session->set_userdata('complete',$order_seq);
		}

		echo "<script>alert('주문이 완료되었습니다.'); top.location.reload();</script>";
	}

	public function kspay()
	{
		require dirname(__FILE__)."/../../pg/kspay/KSPayWebHost.inc"; // library [수정불가]

		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		$pg				= config_load($this->config_system['pgCompany']);
		$rcid			= $_POST['reWHCid'];
		$rctype			= $_POST['reWHCtype'];
		$rhash			= $_POST['reWHHash'];
		$authyn			= '';
		$trno			= '';
		$trddt			= '';
		$trdtm			= '';
		$amt			= '';
		$authno			= '';
		$msg1			= '';
		$msg2			= '';
		$ordno			= '';
		$isscd			= '';
		$aqucd			= '';
		$temp_v			= '';
		$kspay_result	= '';
		$resultcd		= '';

		//업체에서 추가하신 인자값을 받는 부분입니다
		$a				=  $_POST['a'];
		$b				=  $_POST['b'];
		$c				=  $_POST['c'];
		$d				=  $_POST['d'];

		$ipg			= new KSPayWebHost($rcid, null);
		if	($this->_is_mobile_agent)	$ipg->kspay_set_mobile();

		if ($ipg->kspay_send_msg('1'))
		{
			$temp_v			= '';
			$authyn			= $ipg->kspay_get_value('authyn');
			$trno			= $ipg->kspay_get_value('trno'  );
			$trddt			= $ipg->kspay_get_value('trddt' );
			$trdtm			= $ipg->kspay_get_value('trdtm' );
			$amt			= $ipg->kspay_get_value('amt'   );
			$authno			= $ipg->kspay_get_value('authno');
			$msg1			= $ipg->kspay_get_value('msg1'  );
			$msg2			= $ipg->kspay_get_value('msg2'  );
			$ordr_idxx		= $ipg->kspay_get_value('ordno' );
			$isscd			= $ipg->kspay_get_value('isscd' );
			$aqucd			= $ipg->kspay_get_value('aqucd' );
			$kspay_result	= $ipg->kspay_get_value('result');

			if (!empty($msg1)) $msg1	= iconv('EUC-KR','UTF-8', $msg1);
			if (!empty($msg2)) $msg2	= iconv('EUC-KR','UTF-8', $msg2);

			if (!empty($authyn) && 1 == strlen($authyn)){
				if ($authyn == 'O'){
					/* = -------------------------------------------------------------------------- = */
					/* =   06-1. 승인 결과 DB 처리(res_cd == '0000')                                = */
					/* = -------------------------------------------------------------------------- = */
					/* =        각 결제수단을 구분하시어 DB 처리를 하시기 바랍니다.                 = */
					/* = -------------------------------------------------------------------------- = */
					// 주문 상품 재고 체크
					$runout				= false;
					$cfg['order']		= config_load('order');
					$result				= $this->ordermodel->get_item_option($ordr_idxx);
					$data_item_option	= $result;
					$result_option		= $result;
					$result				= $this->ordermodel->get_item_suboption($ordr_idxx);
					$result_suboption	= $result;

					// 주문서 정보 가져오기
					$orders	= $this->ordermodel->get_order($ordr_idxx);
					if(!$orders['order_user_name']) $orders['order_user_name'] = "주문자";

					// 주문 처리
					if($runout == false){

						// 회원 적립금 차감
						if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
						{
							$params	= array(
								'gb'		=> 'minus',
								'type'		=> 'order',
								'emoney'	=> $orders['emoney'],
								'ordno'	=> $ordr_idxx,
								'memo'		=> "[차감]주문 ({$ordr_idxx})에 의한 적립금 차감"
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
							if($item_option['download_seq'])
								$this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
						}

						//배송비쿠폰사용
						if($orders['download_seq'])
							$this->couponmodel->set_download_use_status($orders['download_seq'],'used');

						// 장바구니 비우기
						if( $orders['mode'] )
							$this->cartmodel->delete_mode($orders['mode']);

						// 06-1-3. 가상계좌
						if ( $kspay_result == "6" || $kspay_result == "6001" ){
							switch($authno){
								case "04":	$bank_name = "국민은행";		break;
								case "11":	$bank_name = "농협중앙회";	break;
								case "26":	$bank_name = "신한은행";		break;
								case "20":	$bank_name = "우리은행";		break;
								case "23":	$bank_name = "SC제일은행";	break;
								case "03":	$bank_name = "기업은행";		break;
								case "71":	$bank_name = "우체국";		break;
								case "27":	$bank_name = "시티은행";		break;
								case "32":	$bank_name = "부산은행";		break;
								case "39":	$bank_name = "경남은행";		break;
								case "37":	$bank_name = "전북은행";		break;
								case "34":	$bank_name = "광주은행";		break;
								case "31":	$bank_name = "대구은행";		break;
								case "05":	$bank_name = "외환은행";		break;
								case "E0":	$bank_name = "삼성증권";		break;
							}
							$virtual_account	= $bank_name . " " . $isscd . " " . $depositor;

							//현금영수증발급
							if($cash_authno){
								$data	= array(	'typereceipt'			=> 2,
													'cash_receipts_no'		=> $cash_authno,
													'virtual_account'		=> $virtual_account,
													'virtual_date'			=> $va_date,
													'pg_transaction_number'	=> $trno,
													'pg_approval_number'	=> $authno);
							}else{
								$data	= array(	'virtual_account'		=> $virtual_account,
													'virtual_date'			=> $va_date,
													'pg_transaction_number'	=> $trno,
													'pg_approval_number'	=> $authno);
							}
							$this->ordermodel->set_step($ordr_idxx, 15, $data);

							// 주문 로그저장
							$log	= "KSNET 가상계좌 주문접수". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);
							$add_log = "";
							if($orders['orign_order_seq']) $add_log = "[재주문]";
							if($orders['admin_order']) $add_log = "[관리자주문]";
							if($orders['person_seq']) $add_log = "[개인결제]";
							$log_title =  $add_log."주문접수"."(".$orders['mpayment'].")";
							$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

						}else{

							//PG모듈에서 현금영수증발급시
							if($cash_authno){
								$data	= array(	'typereceipt'			=> 2,
													'cash_receipts_no'		=> $cash_authno,
													'pg_transaction_number'	=> $trno,
													'pg_approval_number'	=> $authno);
							}else{
								$data	= array(	'pg_transaction_number'	=> $trno,
													'pg_approval_number'	=> $authno);
							}
							$this->coupon_reciver_sms = array();
							$this->coupon_order_sms = array();
							$order_count = 0;

							$this->ordermodel->set_step($ordr_idxx,25,$data);

							//받는 사람 쿠폰 SMS 데이터
							if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
								$order_count = 0;
								foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
									$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
									$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
									$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
									$order_count					=$order_count+1;
								}

								$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
								$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
								$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

							}

							//주문자 쿠폰 SMS 데이터
							if(count($this->coupon_order_sms['order_cellphone']) > 0){
								$order_count = 0;
								foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
									$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
									$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
									$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
									$order_count					=$order_count+1;
								}

								$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
								$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
								$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

							}
							//debug_var(array("payment",$commonSmsData));
							if(count($commonSmsData) > 0){
								commonSendSMS($commonSmsData);
							}


							// 결제 로그저장
							$log	= "KSNET 결제 확인". chr(10)."[" .$res_cd . $res_msg . "]" . chr(10). implode(chr(10),$data);
							$add_log = "";
							if($orders['orign_order_seq']) $add_log = "[재주문]";
							if($orders['admin_order']) $add_log = "[관리자주문]";
							if($orders['person_seq']) $add_log = "[개인결제]";
							$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
							$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

							// 계좌이체 결제의 경우 현금영수증
							if( preg_match('/account/',$orders['payment']) ){
								$result = typereceipt_setting($orders['order_seq']);
							}
						}

						// 출고량 업데이트를 위한 변수선언
						$r_reservation_goods_seq = array();

						// 해당 주문 상품의 출고예약량 업데이트
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
						foreach($r_reservation_goods_seq as $goods_seq){
							$this->goodsmodel->modify_reservation_real($goods_seq);
						}

					}else{

						/* = -------------------------------------------------------------------------- = */
						/* =   06.-2 승인 및 실패 결과 DB처리                                             = */
						/* ============================================================================== */
							$res_cd  = $c_PayPlus->m_res_cd;
							$res_msg = $c_PayPlus->m_res_msg;
							$this->ordermodel->set_step($ordr_idxx,99);

							$log_title =  '결제실패['.$res_cd.']';
							$log = "결제 실패". chr(10)."[" .$res_cd . $res_msg . "]";
							$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

						/* = -------------------------------------------------------------------------- = */
						/* =   06. 승인 및 실패 결과 DB 처리 END                                        = */
						/* = ========================================================================== = */

						/* ============================================================================== */
						/* =   실패 자동취소                                                            = */
						/* = -------------------------------------------------------------------------- = */
							require dirname(__FILE__)."/../../pg/kspay/KSPayEncApprovalCancel4.inc"; // library [수정불가]


							$KSPAY_IPADDR = "210.181.28.137";//운영:210.181.28.137, 테스트:210.181.28.116
							$KSPAY_PORT   = 21001;
							$cancel_type  = "0";

							// Default-------------------------------------------------------
							$EncType	  = "2";     // 0: 암화안함, 1:openssl, 2: seed
							$Version      = "0210";  // 전문버전
							$VersionType  = "00";    // 구분
							$Resend       = "0";     // 전송구분 : 0 : 처음,  2: 재전송

							$RequestDate   = strftime("%Y%m%d%H%M%S");
							$KeyInType     = "K";   // KeyInType 여부 : S : Swap, K: KeyInType
							$LineType      = "1";   // lineType 0 : offline, 1:internet, 2:Mobile
							$ApprovalCount = "1";   // 복합승인갯수
							$GoodType      = "0";   // 제품구분 0 : 실물, 1 : 디지털
							$HeadFiller    = "";    // 예비
							//-------------------------------------------------------------------------------

							// Header (입력값 (*) 필수항목)--------------------------------------------------
							$StoreId     = $_POST["storeid"];     	// *상점아이디
							$OrderNumber = ""; 								// *주문번호
							$UserName    = "";   							// *주문자명
							$IdNum       = "";       						// 주민번호 or 사업자번호
							$Email       = "";       						// *email
							$GoodName    = "";    							// *제품명
							$PhoneNo     = "";     							// *휴대폰번호
							// Header end -------------------------------------------------------------------

							// Data Default(수정항목이 아님)-------------------------------------------------
							$ApprovalType    = $authno; // 승인구분
							$TransactionNo   = $trno;   // 거래번호
							// Data Default end -------------------------------------------------------------

							// --------------------------------------------------------------------------------
							$ipg = new KSPayEncApprovalCancel($KSPAY_IPADDR, $KSPAY_PORT);

							$ipg->HeadMessage(
								$EncType       ,                  // 0: 암화안함, 1:openssl, 2: seed
								$Version       ,                  // 전문버전
								$VersionType   ,                  // 구분
								$Resend        ,                  // 전송구분 : 0 : 처음,  2: 재전송
								$RequestDate   ,                  // 재사용구분
								$StoreId       ,                  // 상점아이디
								$OrderNumber   ,                  // 주문번호
								$UserName      ,                  // 주문자명
								$IdNum         ,                  // 주민번호 or 사업자번호
								$Email         ,                  // email
								$GoodType      ,                  // 제품구분 0 : 실물, 1 : 디지털
								$GoodName      ,                  // 제품명
								$KeyInType     ,                  // KeyInType 여부 : S : Swap, K: KeyInType
								$LineType      ,                  // lineType 0 : offline, 1:internet, 2:Mobile
								$PhoneNo       ,                  // 휴대폰번호
								$ApprovalCount ,                  // 복합승인갯수
								$HeadFiller    );                 // 예비


							// ------------------------------------------------------------------------------
							$ipg->CancelDataMessage(
								$ApprovalType,      // ApprovalType,	: 승인구분
								$cancel_type,       			// CancelType,	: 취소처리구분 1:거래번호, 2:주문번호, 3.부분취소
								$TransactionNo,     // TransactionNo,: 거래번호
								"",                 // TradeDate,	: 거래일자
								"",                 // OrderNumber,	: 주문번호
								$filler);                // Filler)		: 기타

							$rStatus = "X";
							$rMessage = "C취소거절/잠시후재시도하세요";
							$success = false;

							if ($ipg->SendEncSocket())
							//if ($ipg->SendExecSocket($EXEC_DIR ,$LOG_DIR))
							{
								if (substr($ApprovalType,0,1) == "1" || substr($ApprovalType,0,1) == "I")
								{
									$rStatus       = $ipg->Status												;//성공여부(O/X/S)
									$rMessage      = $ipg->Message1	. "/" . $ipg->Message2						;
								}else
								if (substr($ApprovalType,0,1) == "6")
								{
									$rStatus       = $ipg->VAStatus												;//성공여부(O/X/S)
									$rMessage      = $ipg->VAMessage1	. "/" . $ipg->VAMessage2				;
								}else
								if (substr($ApprovalType,0,1) == "2")
								{
									$rStatus       = $ipg->ACStatus												;//성공여부(O/X/S)
									$rMessage      = $ipg->ACMessage1	. "/" . $ipg->ACMessage2				;
								}else
								if (substr($ApprovalType,0,1) == "H")
								{
									$rStatus       = $ipg->HStatus												;//성공여부(O/X/S)
									$rMessage      = $ipg->HMessage1	. "/" . $ipg->HMessage2					;
								}else
								if (substr($ApprovalType,0,1) == "M")
								{
									$rStatus       = $ipg->MStatus												;//성공여부(O/X/S)
									$rMessage      = $ipg->MRespMsg												;
								}else
								{
									$rStatus = "X";
									$rMessage = "C취소거절/승인구분오류";
								}
								if($rStatus == "O"){
									$success = true;
								}
							}

							return array(
								'success'=>$success,
								'result_code'=>$rAuthNo,
								'result_msg'=>iconv('euc-kr','utf-8',$rMessage)
							);
					}

				}else{

					/* = -------------------------------------------------------------------------- = */
					/* =   06.-2 승인 및 실패 결과 DB처리                                             = */
					/* ============================================================================== */
							$this->ordermodel->set_step($ordr_idxx,99);

							// 결제 실패 로그 저장
							$res_cd		= $c_PayPlus->m_res_cd;
							$res_msg	= $c_PayPlus->m_res_msg;

							$log_title =  '결제실패['.$res_cd.']';
							$log = "KSNET 결제 실패". chr(10)."[" .$res_cd . $res_msg . "]";
							$this->ordermodel->set_log($ordr_idxx,'pay',$orders['order_user_name'],$log_title,$log);

					/* = -------------------------------------------------------------------------- = */
					/* =   06. 승인 및 실패 결과 DB 처리 END                                        = */
					/* = ========================================================================== = */
				}

				## 로그 저장 변수 세팅
				$pg_log['pg']	= $this->config_system['pgCompany'];
				$pg_log['res_cd']		= $res_cd;
				$pg_log['res_msg']		= $res_msg;
				$pg_log['order_seq']	= $ordr_idxx;
				$pg_log['tno']			= $trno;
				$pg_log['amount']		= $amt;
				$pg_log['card_cd'] 		= $isscd;
				$pg_log['card_name'] 	= $card_name;
				$pg_log['app_no'] 		= $authno;
				$pg_log['app_time'] 	= $trdtm;
				$pg_log['noinf'] 		= $noinf;
				$pg_log['quota'] 		= $quota;
				$pg_log['bank_code'] 	= $authno;
				$pg_log['bank_name']	= $bank_name;
				$pg_log['depositor'] 	= $depositor;
				$pg_log['account'] 		= $isscd;
				$pg_log['va_date'] 		= $va_date;
				$pg_log['commid'] 		= $commid;
				$pg_log['mobile_no']	= $mobile_no;
				$pg_log['escw_yn']		= $escw_yn;
				foreach($pg_log as $k => $v){
					$v = trim($v);
					$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
				}
				$pg_log['regist_date'] = date('Y-m-d H:i:s');
				$this->db->insert('fm_order_pg_log', $pg_log);

				// End of [res_cd = "0000"]
				pageRedirect('../order/complete?no='.$ordr_idxx,'','parent');

				//$ipg->kspay_send_msg("3"); // 정상처리가 완료되었을 경우 호출합니다.(이 과정이 없으면 일시적으로 kspay_send_msg("1")을 호출하여 거래내역 조회가 가능합니다.)
			}
		}else{
				pageRedirect('../order/complete_replace?no='.$ordr_idxx.'&res_cd=3001','','self');
		}
	}

	public function kspay_return()
	{

		$data = preg_replace("/^data=/i","",$_SERVER['QUERY_STRING']);
		$data = urldecode($data);
		$data	= mb_convert_encoding($data, "UTF-8", "EUC-KR");

		ob_start();
		ob_implicit_flush(0);
		$ret		= 'false';
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');

		//PG에서 보냈는지 IP로 체크
		$IN_IP	= getenv("REMOTE_ADDR");
		if	( in_array( $IN_IP, array('106.246.242.226', '210.181.28.116', '210.181.29.130')) ){
			$ipos	= 0;
			$ret	= "false";

			if (strlen($data) >= 300){

				if	(substr($data,$ipos  ,1) >= "0" && substr($data,$ipos  ,1) <= "9" &&
					substr($data,$ipos+1,1) >= "0" && substr($data,$ipos+1,1) <= "9" &&
					substr($data,$ipos+2,1) >= "0" && substr($data,$ipos+2,1) <= "9" &&
					substr($data,$ipos+3,1) >= "0" && substr($data,$ipos+3,1) <= "9"){

					// 신PG 헤더
					$ipos		+= 36;
					$order_seq	= trim(substr($data, $ipos, 50)); $ipos += 50; //  주문번호

					// 한글이 깨지는 문제로 인해 걸러냄.
					$rdata		= substr($data, $ipos);
					preg_match('/[^\s]+\s+/', $rdata, $match_array);
					$UserName	= trim($match_array[0]); //  주문자명
					$data		= preg_replace('/'.$match_array[0].'/', '', $rdata);
					$ipos		= 0;
					if	(preg_match('/^[0-9]/', $data)){
						 //  주민번호 or 사업자번호
						$IdNum	= substr($data, $ipos, 13); $ipos += 13;
					}
					$ipos		+= 51;

					// 한글이 깨지는 문제로 인해 걸러냄.
					$rdata		= substr($data, $ipos);
					preg_match('/[^\s]+\s+/', $rdata, $match_array);
					$GoodName	= trim($match_array[0]); //  제품명
					$data		= preg_replace('/'.$match_array[0].'/', '', $rdata);
					$ipos		= 50;
					$InOut		= substr($data, 50, 2);
					if($InOut == "50" || $InOut == "61"){
						$deal_sele	= substr($data, 187, 2);
						$ret		= "OK";
					}
				}

				// DB 처리
				if	($ret == 'OK' && $order_seq && $deal_sele == '20'){
					// 주문 상품 재고 체크
					$orders				= $this->ordermodel->get_order($order_seq);
					if	($orders['step'] == '15'){
						$runout				= false;
						$cfg['order']		= config_load('order');
						$result				= $this->ordermodel->get_item_option($order_seq);
						$result_option		= $result;
						$data_item_option	= $result;
						$result				= $this->ordermodel->get_item_suboption($order_seq);
						$result_suboption	= $result;

						if	($log_use == 'Y'){
							if($runout)	fwrite($fobj, 'runout : true' . "\r\n");
							else		fwrite($fobj, 'runout : false' . "\r\n");
						}

						if($runout) exit;
						$data	= array('pg_transaction_number' => $rVTransactionNo);
						$this->coupon_reciver_sms = array();
						$this->coupon_order_sms = array();
						$order_count = 0;

						$this->ordermodel->set_step($order_seq,25,$data);

						//받는 사람 쿠폰 SMS 데이터
						if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
								$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
								$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
								$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;;
							$commonSmsData['coupon_released']['params'] = $coupon_arr_params;
							$commonSmsData['coupon_released']['order_no'] = $coupon_order_no;

						}

						//주문자 쿠폰 SMS 데이터
						if(count($this->coupon_order_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
								$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
								$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
								$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;;
							$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
							$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;

						}


						$log	= "KSNET 결제 확인". chr(10)."[" .$stan_resp_code . $mess_code . "]" . chr(10). implode(chr(10),$data);
						$add_log = "";
						if($orders['orign_order_seq']) $add_log = "[재주문]";
						if($orders['admin_order']) $add_log = "[관리자주문]";
						if($orders['person_seq']) $add_log = "[개인결제]";
						$log_title =  $add_log."결제확인"."(".$orders['mpayment'].")";
						$this->ordermodel->set_log($order_seq,'pay',$orders['order_user_name'],$log_title,$log);

						// 가상계좌 결제의 경우 현금영수증
						if( $orders['step'] < '25' || $orders['step'] > '85' ){
							$result = typereceipt_setting($orders['order_seq']);
						}

						$mail_step = 25;

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
						foreach($r_reservation_goods_seq as $goods_seq){
							$this->goodsmodel->modify_reservation_real($goods_seq);
						}

						// 결제확인메일/sms 발송
						send_mail_step25($orders['order_seq']);
						if( $orders['order_cellphone'] && $orders['sms_25_YN'] != 'Y'){
							$params['shopName'] = $this->config_basic['shopName'];
							$params['ordno']	= $orders['order_seq'];
							$params['user_name'] = $orders['order_user_name'];

							//$commonSmsData = array();
							$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
							$commonSmsData['settle']['params'][] = $params;
							$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
							//commonSendSMS($commonSmsData);

							//sendSMS($orders['order_cellphone'], 'settle', '', $params,'','admin');

							$this->db->where('order_seq', $orders['order_seq']);
							$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
						}
					}

					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}

				}
			}
		}

		// kspay 로 결과 전달 ( 단, 현재 charset 문제로 KSNET에서 결과를 못 받는 문제가 있음 )
		$msg	= 'result='.$ret;

		header("Content-Type: text/html; charset=EUC-KR");
		Header("Content-Length: " . strlen($msg));
		echo($msg);
		ob_end_flush();
	}

	// 카카오페이 결제 완료 :: 2015-02-24 lwh
	public function kakaopay(){

		$this->load->model('cartmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');

		/* ============================================================================== */
		/* =   카카오 페이 승인요청 로직 :: START                                       = */
		/* = -------------------------------------------------------------------------- = */
		/* =   수정하지 마시기 바랍니다.                                                = */
	    /* = -------------------------------------------------------------------------- = */
		
		//## 라이브러리 인클루드
		require("./pg/kakaopay/conf_inc.php");
		require("./pg/kakaopay/libs/lgcns_CNSpay.php");

		$pg_param = config_load('kakaopay');

		// 로그 저장 위치 지정
		$connector = new CnsPayWebConnector($LogDir);
		$connector->CnsActionUrl("https://".$CnsPayDealRequestUrl);
		$connector->CnsPayVersion($phpVersion);

		// #1. 요청 페이지 파라메터 셋팅
		$connector->setRequestData($_REQUEST);

		// #2. 추가 파라메터 셋팅
		$connector->addRequestData("actionType", "PY0");  // actionType : CL0 취소, PY0 승인, CI0 조회
		$connector->addRequestData("MallIP", $_SERVER['REMOTE_ADDR']);	// 가맹점 고유 ip
		$connector->addRequestData("CancelPwd", $pg_param['cancelPwd']);

		// #3. 가맹점키 셋팅 (MID 별로 틀림)
		$connector->addRequestData("EncodeKey", $pg_param['merchantKey']);

		// #4. CNSPAY Lite 서버 접속하여 처리
		$connector->requestAction();

		// #5. 결과 처리
		$buyerName		= $_REQUEST["BuyerName"];   				// 구매자명
		$goodsName		= $_REQUEST["GoodsName"]; 					// 상품명
		$buyerName		= iconv("euc-kr", "utf-8", $connector->getResultData("BuyerName"));	
		$goodsName		= iconv("euc-kr", "utf-8", $connector->getResultData("GoodsName"));	

		$resultCode		= $connector->getResultData("ResultCode");	// 결과코드 (정상 :3001 , 그 외 에러)
		$resultMsg		= $connector->getResultData("ResultMsg");	// 결과메시지
		$authDate		= $connector->getResultData("AuthDate"); 	// 승인일시YYMMDDHH24mmss
		$authCode		= $connector->getResultData("AuthCode");   	// 승인번호
		$payMethod		= $connector->getResultData("PayMethod");  	// 결제수단
		$mid			= $connector->getResultData("MID");  		// 가맹점ID
		$tid			= $connector->getResultData("TID");  		// 거래ID
		$moid			= $connector->getResultData("Moid");  		// 주문번호
		$amt			= $connector->getResultData("Amt");  		// 금액
		$cardCode		= $connector->getResultData("CardCode");	// 카드사 코드
		$cardName		= $connector->getResultData("CardName");  	// 결제카드사명
		$cardQuota		= $connector->getResultData("CardQuota"); 	// 00:일시불,02:2개월
		$cardInterest	= $connector->getResultData("CardInterest");// 무이자 여부 (0:일반, 1:무이자)
		$cardCl			= $connector->getResultData("CardCl");		// 체크카드여부 (0:일반, 1:체크카드)
		$cardBin		= $connector->getResultData("CardBin");		// 카드BIN번호
		$cardPoint		= $connector->getResultData("CardPoint");	// 카드사포인트사용여부 (0:미사용, 1:포인트사용, 2:세이브포인트사용)
		$paySuccess		= false;									// 결제 성공 여부
		$nonRepToken	= $connector->getResultData("NON_REP_TOKEN");//부인방지토큰값

		$resultMsg		= iconv("euc-kr", "utf-8", $resultMsg);
		//$cardName		= iconv("euc-kr", "utf-8", $cardName);

		/** 위의 응답 데이터 외에도 전문 Header와 개별부 데이터 Get 가능 */
		if($payMethod == "CARD"){									// 신용카드
			if($resultCode == "3001") $paySuccess = true;			// 결과코드 (정상 :3001 , 그 외 에러)
		}
		/* = -------------------------------------------------------------------------- = */
		/* =   카카오 페이 승인요청 로직 :: END                                         = */
		/* ============================================================================== */
		

		$res = array(
			'resultCode'	=> $resultCode,		// 결제 결과코드 : 정상 - 3001
			'authDate'		=> $authDate,		// 승인 날짜 : YYMMDDHHMMSS
			'authCode'		=> $authCode,		// 신용카드 승인번호
			'payMethod'		=> $payMethod,		// 결제 수단 : 현재는 CARD
			'mid'			=> $mid,			// 가맹점ID
			'goodsName'		=> $goodsName,		// 상품명
			'buyerName'		=> $buyerName,		// 구매자명
			'amt'			=> $amt,			// 상품가격
			'tid'			=> $tid,			// 거래번호
			'moid'			=> $moid,			// 주문번호 (퍼스트몰)
			'cardName'		=> $cardName,		// 선택카드이름
			'cardQuota'		=> $cardQuota,		// 할부개월수 : 00-일시불
			'cardCode'		=> $cardCode,		// 카드코드
			'cardInterest'	=> $cardInterest,	// 무이자여부 : 0-일반, 1:무이자
			'cardCl'		=> $cardCl,			// 체크카드여부 : 0-일반, 1-체크카드
			'cardBin'		=> $cardBin,		// 카드BIN번호
			'cardPoint'		=> $cardPoint,		// 카드사 포인트 : 0-미사용, 1-포인트사용, 2세이브포인트사용
			'nonRepToken'	=> $nonRepToken		// 부인방지 토큰값
		);

		/* = -------------------------------------------------------------------------- = */
		/* =   결제 성공 DB 업데이트 로직                                               = */
	    /* = -------------------------------------------------------------------------- = */
		
		// 주문서 정보 가져오기
		$orders = $this->ordermodel->get_order($moid);
		if(!$orders['order_seq']){
			// 주문서 주문번호 오류시 처리...
		}

		if($paySuccess) {
			// 결제 성공 시 처리 - 퍼스트몰 로직
			if($payMethod){
				$query = "update fm_order set payment=?, pg='kakaopay' where order_seq=?";
				$this->db->query($query,array($payMethod,$orders['order_seq']));
			}

			// 주문 상품 재고 체크
			$runout = false;
			$cfg['order'] = config_load('order');
			$result = $this->ordermodel->get_item_option($orders['order_seq']);
			$data_item_option = $result;
			$result_option = $result;
			$result = $this->ordermodel->get_item_suboption($orders['order_seq']);
			$result_suboption = $result;

			// 회원 적립금 차감
			if( $orders['emoney'] && $orders['member_seq'] && $orders['emoney_use']=='none')
			{
				$params = array(
					'gb'		=> 'minus',
					'type'		=> 'order',
					'emoney'	=> $orders['emoney'],
					'ordno'		=> $orders['order_seq'],
					'memo'		=> "[차감]주문 ({$orders['order_seq']})에 의한 적립금 차감",
				);
				$this->membermodel->emoney_insert($params, $orders['member_seq']);
				$this->ordermodel->set_emoney_use($orders['order_seq'],'use');
			}

			// 회원 이머니 차감
			if( $orders['cash'] && $orders['member_seq'] && $orders['cash_use']=='none')
			{
				$params = array(
					'gb'		=> 'minus',
					'type'		=> 'order',
					'cash'	=> $orders['cash'],
					'ordno'	=> $orders['order_seq'],
					'memo'		=> "[차감]주문 ({$orders['order_seq']})에 의한 이머니 차감",
				);
				$this->membermodel->cash_insert($params, $orders['member_seq']);
				$this->ordermodel->set_cash_use($orders['order_seq'],'use');
			}

			//상품쿠폰사용
			if($data_item_option) foreach($data_item_option as $item_option){
				if($item_option['download_seq']){
					$this->couponmodel->set_download_use_status($item_option['download_seq'],'used');
				}
			}
			//배송비쿠폰사용
			if($orders['download_seq']){
				$this->couponmodel->set_download_use_status($orders['download_seq'],'used');
			}

			//프로모션코드 상품/배송비 할인 사용처리
			$this->promotionmodel->setPromotionpayment($orders);

			// 장바구니 비우기
			if( $orders['mode'] ){
				$this->cartmodel->delete_mode($orders['mode']);
			}

			// 주문 상태 업데이트
			$data = array(
				'pg_transaction_number' => $tid,		// 카카오페이 거래고유번호
				'pg_approval_number'	=> $authCode	// 승인번호
			);
			$this->coupon_reciver_sms = array();
			$this->coupon_order_sms = array();
			$order_count = 0;

			$this->ordermodel->set_step($orders['order_seq'],25,$data);

			//받는 사람 쿠폰 SMS 데이터
			if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
				$order_count = 0;
				foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
					$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
					$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
					$coupon_order_cellphones[$order_count]	= $this->coupon_reciver_sms['order_cellphone'][$key];
					$order_count					=$order_count+1;
				}

				$commonSmsData['coupon_released']['phone']		= $coupon_order_cellphones;;
				$commonSmsData['coupon_released']['params']		= $coupon_arr_params;
				$commonSmsData['coupon_released']['order_no']	= $coupon_order_no;

			}

			//주문자 쿠폰 SMS 데이터
			if(count($this->coupon_order_sms['order_cellphone']) > 0){
				$order_count = 0;
				foreach($this->coupon_order_sms['order_cellphone'] as $key=>$value){
					$reciver_arr_params[$order_count]		= $this->coupon_order_sms['params'][$key];
					$reciver_order_no[$order_count]			= $this->coupon_order_sms['order_no'][$key];
					$reciver_order_cellphones[$order_count] = $this->coupon_order_sms['order_cellphone'][$key];
					$order_count					=$order_count+1;
				}

				$commonSmsData['coupon_released2']['phone']		= $reciver_order_cellphones;;
				$commonSmsData['coupon_released2']['params']	= $reciver_arr_params;
				$commonSmsData['coupon_released2']['order_no']	= $reciver_order_no;
				
			}

			if(count($commonSmsData) > 0){
				commonSendSMS($commonSmsData);
			}


			// DB 로그 기록
			$add_log = "";
			if($orders['orign_order_seq'])	$add_log = "[재주문]";
			if($orders['admin_order'])		$add_log = "[관리자주문]";
			if($orders['person_seq'])		$add_log = "[개인결제]";

			$log_title =  $add_log."결제확인"."(카카오페이)";

			$log = "카카오페이 결제 확인". chr(10)."[" . $resultCode . ":" . $_kakao_result_code[$resultCode] ."]" . chr(10). implode(chr(10),$data);
			$this->ordermodel->set_log($orders['order_seq'],'pay',$orders['order_user_name'],$log_title,$log);


			// 출고량 업데이트를 위한 변수선언
			$r_reservation_goods_seq = array();

			// 해당 주문 상품의 출고예약량 업데이트
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
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}		
		}
		else
		{

		/* = -------------------------------------------------------------------------- = */
		/* =   결제 실패 DB 업데이트 로직                                               = */
	    /* = -------------------------------------------------------------------------- = */
			
			$this->ordermodel->set_step($orders['order_seq'],99);
			$log_title =  '결제실패['.$resultCode.']';
			$log = "카카오페이 결제 실패". chr(10)."[" .$resultCode ." : " . $_kakao_result_code[$resultCode] ."(".$resultMsg.")]";
			$this->ordermodel->set_log($orders['order_seq'],'pay',$orders['order_user_name'],$log_title,$log);

			openDialogAlert("카카오페이 결제 실패<br /><font color=red>{$resultCode} : {$_kakao_result_code[$resultCode]}({$resultMsg})</font>",400,160,'parent','');
			exit;
		}

		## 로그 저장 변수 세팅
		$pg_log['pg']			= 'kakaopay';
		$pg_log['res_cd']		= $resultCode;	// 결과값
		$pg_log['res_msg']		= $_kakao_result_code[$resultCode]."(".$resultMsg.")"; 
												// 결과메세지
		$pg_log['order_seq']	= $orders['order_seq'];	// 주문번호
		$pg_log['tno']			= $tid;			// 카카오페이 거래고유번호
		$pg_log['amount']		= $amt;			// 결제 승인 요청 금액
		$pg_log['card_cd'] 		= $cardCode;	// 카드사 코드
		$pg_log['card_name'] 	= $cardName;	// 카드사 이름
		$pg_log['app_no'] 		= $authCode;	// 결제카드 승인번호
		$pg_log['app_time'] 	= date('YmdHis',strtotime('20'.$authDate)); // 승인일
		$pg_log['noinf'] 		= ($cardInterest == '1') ? 'Y' : 'N';
												// 무이자 할수여부 : Y | N
		$pg_log['quota'] 		= $cardQuota;	// 할부개월수 : 00-일시불
		$pg_log['bank_code'] 	= '';			// 은행코드
		$pg_log['bank_name']	= '';			// 은행이름
		$pg_log['depositor'] 	= '';			// 예금주
		$pg_log['account'] 		= '';			// 계좌번호
		$pg_log['va_date'] 		= '';			// 입금예정일
		$pg_log['commid'] 		= '';			// 이동통신사 코드
		$pg_log['mobile_no']	= '';			// 휴대폰번호
		$pg_log['escw_yn']		= 'N';			// 에스크로여부

		foreach($pg_log as $k => $v){
			$v = trim($v);
			if($k!='res_msg')
				$pg_log[$k] = mb_convert_encoding($v,"UTF-8", "EUC-KR");
		}
		$pg_log['regist_date'] = date('Y-m-d H:i:s');
		$this->db->insert('fm_order_pg_log', $pg_log);

		pageRedirect('../order/complete?no='.$orders['order_seq'],'','parent');
	}
}