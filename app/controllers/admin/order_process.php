<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class order_process extends admin_base {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');
		$this->load->model('exportmodel');
		$this->arr_step 	= config_load('step');
		$this->arr_payment 	= config_load('payment');
		$this->load->helper('order');
		// $this->load->helper('readurl');
		$this->load->model('goodsmodel');
	}
	// 배송정보 변경
	public function shipping()
	{
		/*
		$order_seq 		= $_GET['seq'];
		$international 	= $_GET['international'];

		$orders		= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['shipping_region']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 배송정보 변경을 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		$this->validation->set_rules('recipient_user_name','받는이','trim|required|xss_clean');
		//$this->validation->set_rules('recipient_phone[]','전화','trim|numeric|required|xss_clean');
		$this->validation->set_rules('recipient_cellphone[]','휴대폰','trim|numeric|required|xss_clean');
		$this->validation->set_rules('memo','요청사항','trim|xss_clean');

		if($international == 'domestic'){
			$this->validation->set_rules('recipient_zipcode[]','우편번호','trim|numeric|required|xss_clean');
			$this->validation->set_rules('recipient_address','주소','trim|required|xss_clean');
			$this->validation->set_rules('recipient_address_detail','주소','trim|required|xss_clean');
		}

		if($international == 'international'){
			$this->validation->set_rules('region','지역','trim|required|xss_clean');
			$this->validation->set_rules('international_address','주소','trim|required|xss_clean');
			$this->validation->set_rules('international_town_city','시도','trim|required|xss_clean');
			$this->validation->set_rules('international_county','주','trim|required|xss_clean');
			$this->validation->set_rules('international_postcode','우편번호','trim|required|xss_clean');
			$this->validation->set_rules('international_country','국가','trim|required|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$_POST['recipient_phone']		= implode('-',$_POST['recipient_phone']);
		$_POST['recipient_cellphone']	= implode('-',$_POST['recipient_cellphone']);
		$data['recipient_user_name'] = $_POST['recipient_user_name'];
		$data['recipient_phone'] 	 = $_POST['recipient_phone'];
		$data['recipient_cellphone'] = $_POST['recipient_cellphone'];
		$data['memo'] 				 = $_POST['memo'];

		if($international == 'domestic'){
			$_POST['recipient_zipcode'] = implode('-',$_POST['recipient_zipcode']);
			foreach($_POST as $k => $row) if($orders[$k]!=$data) $change = 1;
			if($change){
				$data['recipient_zipcode'] 			= $_POST['recipient_zipcode'];
				$data['recipient_address'] 			= $_POST['recipient_address'];
				$data['recipient_address_detail'] 	= $_POST['recipient_address_detail'];
			}
		}

		if($international == 'international'){
			foreach($_POST as $k => $row) if($orders[$k]!=$data) $change = 1;
			if($change){
				$data['region'] 					= $_POST['region'];
				$data['international_address'] 		= $_POST['international_address'];
				$data['international_town_city'] 	= $_POST['international_town_city'];
				$data['international_county'] 		= $_POST['international_county'];
				$data['international_postcode'] 	= $_POST['international_postcode'];
				$data['international_country'] 		= $_POST['international_country'];
			}
		}

		if($change){
			$this->db->where('order_seq', $order_seq);
			$this->db->update('fm_order', $data);
			$log = "배송지 정보 변경";
			$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],$log,serialize($data));
			openDialogAlert("배송지 정보가 변경 되었습니다.",400,140,'parent','');
		}
		*/
	}

	// 배송지별 정보 변경
	public function shipping_multi()
	{


		$order_seq 		= $_GET['order_seq'];
		$shipping_seq 		= $_GET['shipping_seq'];

		$orders		= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['shipping_region']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 배송정보 변경을 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		$international = $orders['international'];

		list($order_shipping) = $this->ordermodel->get_shipping($order_seq,$shipping_seq);

		$this->validation->set_rules('recipient_user_name','받는이','trim|required|xss_clean');
		$this->validation->set_rules('recipient_phone[]','전화','trim|numeric|required|xss_clean');
		$this->validation->set_rules('recipient_cellphone[]','휴대폰','trim|numeric|required|xss_clean');
		$this->validation->set_rules('memo','요청사항','trim|xss_clean');
		if($international == 'domestic'){
			$this->validation->set_rules('recipient_zipcode[]','우편번호','trim|numeric|required|xss_clean');
			$this->validation->set_rules('recipient_address','주소','trim|required|xss_clean');
			$this->validation->set_rules('recipient_address_detail','주소','trim|required|xss_clean');
		}
		if($international == 'international'){
			$this->validation->set_rules('region','지역','trim|required|xss_clean');
			$this->validation->set_rules('international_address','주소','trim|required|xss_clean');
			$this->validation->set_rules('international_town_city','시도','trim|required|xss_clean');
			$this->validation->set_rules('international_county','주','trim|required|xss_clean');
			$this->validation->set_rules('international_postcode','우편번호','trim|required|xss_clean');
			$this->validation->set_rules('international_country','국가','trim|required|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		foreach($_POST as $k => $row) if(in_array($k,array_keys($order_shipping)) && $order_shipping[$k]!=$_POST[$k]) $change = 1;

		$_POST['recipient_phone']		= implode('-',$_POST['recipient_phone']);
		$_POST['recipient_cellphone']	= implode('-',$_POST['recipient_cellphone']);

		$data['shipping_seq']				= $shipping_seq;
		$data['recipient_user_name']		= $_POST['recipient_user_name'];
		$data['recipient_phone'] 			= $_POST['recipient_phone'];
		$data['recipient_cellphone']		= $_POST['recipient_cellphone'];

		if($international == 'domestic'){
			$_POST['recipient_zipcode'] = implode('-',$_POST['recipient_zipcode']);
			if($change){
				$data['recipient_zipcode'] 				= $_POST['recipient_zipcode'];
				$data['recipient_address_type'] 		= $_POST['recipient_address_type'];
				$data['recipient_address'] 				= $_POST['recipient_address'];
				$data['recipient_address_street'] 	= $_POST['recipient_address_street'];
				$data['recipient_address_detail'] 	= $_POST['recipient_address_detail'];
			}
		}

		if($international == 'international'){
			if($change){
				$data['region'] 					= $_POST['region'];
				$data['international_address'] 		= $_POST['international_address'];
				$data['international_town_city'] 	= $_POST['international_town_city'];
				$data['international_county'] 		= $_POST['international_county'];
				$data['international_postcode'] 	= $_POST['international_postcode'];
				$data['international_country'] 		= $_POST['international_country'];
			}
		}

		$data['memo'] 						= $_POST['memo'];

		if($change){

			$this->db->where(array(
				'order_seq'=>$order_seq,
				'shipping_seq'=>$shipping_seq
			));
			$this->db->update('fm_order_shipping', $data);
			$log = "배송지 정보 변경";
			$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],$log,serialize($data));
			openDialogAlert("배송지 정보가 변경 되었습니다.",400,140,'parent','');
		}

	}

	// 배송지별 정보변경시 지역별 추가배송지 변동 체크
	public function shipping_multi_confirm(){
		$this->load->helper('shipping');
		$order_seq 		= $_POST['order_seq'];
		$shipping_seq 		= $_POST['shipping_seq'];
		$orders		= $this->ordermodel->get_order($order_seq);
		list($order_shipping) = $this->ordermodel->get_shipping($order_seq,$shipping_seq);

		$international 	= $orders['international']?$orders['international']:'domestic';

		// 지역별 추가배송비 변동 체크
		if($international == 'domestic' && $orders['shipping_method']=='delivery'){
			$shipping_policy = use_shipping_method();
			$door2door = $shipping_policy[0][0];
			$newAddDeliveryCost = 0;
			if($door2door['sigungu']) foreach($door2door['sigungu'] as $sigungu_key => $sigungu){
				if(preg_match('/'.$sigungu.'/',$_POST['recipient_address'])){
					$newAddDeliveryCost += $door2door['addDeliveryCost'][$sigungu_key];
				}
			}

			if($order_shipping['area_add_delivery_cost']<$newAddDeliveryCost){
				echo json_encode(array('msg' => "추가 배송비가 부과되는 지역입니다. 정말 변경하시겠습니까?"));
				exit;
			}

			if($order_shipping['area_add_delivery_cost']>$newAddDeliveryCost){
				echo json_encode(array('msg' => "추가 배송비가 제외되는 지역입니다. 정말 변경하시겠습니까?"));
				exit;
			}
		}

		echo json_encode(array('msg' => ''));
	}

	// 배송정보 변경
	public function bank()
	{
		$order_seq = $_GET['seq'];

		$orders		= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['change_bank']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 입금계좌 정보 변경을 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		$this->validation->set_rules('depositor',		'입금자명','trim|required|xss_clean');
		$this->validation->set_rules('bank_account',	'입금계좌','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		foreach($_POST as $k => $data) if($orders[$k]!=$data) $change = 1;
		if($change){
			$data = array();
			$data['depositor'] 		= $_POST['depositor'];
			$data['bank_account'] 	= $_POST['bank_account'];
			$this->db->where('order_seq', $order_seq);
			$this->db->update('fm_order', $data);
			$log = "입금계좌 정보 변경";
			$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],$log,serialize($data));
			openDialogAlert("입금계좌가 변경 되었습니다.",400,140,'parent','');
		}
	}

	// 배송정보 변경
	public function admin_memo()
	{
		$order_seq = $_GET['seq'];
		$this->validation->set_rules('admin_memo','관리자메모','trim|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		$data['admin_memo'] = $_POST['admin_memo'];
		$this->db->where('order_seq', $order_seq);
		$this->db->update('fm_order', $data);
		openDialogAlert("관리자 메모가 변경 되었습니다.",400,140,'parent','');
	}

	// 주문 무효
	public function cancel_order(){
		$order_seq = $_GET['seq'];

		$orders		= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['cancel_order']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 ".$this->arr_step[95]."를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		$this->ordermodel->set_step($order_seq,95);
		$options	= $this->ordermodel->get_item_option($order_seq);
		$suboptions	= $this->ordermodel->get_item_suboption($order_seq);
		if($options) foreach($options as $k => $option){
			$tot_ea		+= $option['ea'];
		}
		if($suboptions) foreach($suboptions as $k => $option){
			$tot_ea		+= $option['ea'];
		}

		if($orders['member_seq']){
			$this->load->model('membermodel');
			/* 적립금 환원 */
			if($orders['emoney_use']=='use' && $orders['emoney'])
			{
				$params = array(
					'gb'		=> 'plus',
					'type'		=> 'cancel',
					'emoney'	=> $orders['emoney'],
					'ordno'	=> $order_seq,
					'memo'		=> "[복원]".$this->arr_step[95]."(".$order_seq.")에 의한 적립금 환원",
				);
				$this->membermodel->emoney_insert($params, $orders['member_seq']);
				$this->ordermodel->set_emoney_use($order_seq,'return');
			}

			/* 이머니 환원 */
			if($orders['cash_use']=='use' && $orders['cash'])
			{
				$params = array(
					'gb'		=> 'plus',
					'type'		=> 'cancel',
					'cash'	=> $orders['cash'],
					'ordno'	=> $order_seq,
					'memo'		=> "[복원]".$this->arr_step[95]."(".$order_seq.")에 의한 이머니 환원",
				);
				$this->membermodel->cash_insert($params, $orders['member_seq']);
				$this->ordermodel->set_cash_use($order_seq,'return');
			}
		}

		/* 프로모션환원 */
		$this->load->model('couponmodel');
		$this->load->model('promotionmodel');

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		/* 해당 주문 상품의 출고예약량 업데이트 */
		if($options){
			foreach($options as $data_option){
				// 출고량 업데이트를 위한 변수정의
				if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
					$r_reservation_goods_seq[] = $data_option['goods_seq'];
				}

				//상품별 쿠폰/프로모션코드 복원
				if($data_option['download_seq'] && $data_option['coupon_sale']) $goodscoupon = $this->couponmodel->restore_used_coupon($data_option['download_seq']);
				if($data_option['promotion_code_seq'] && $data_option['promotion_code_sale']) $goodspromotioncode = $this->promotionmodel->restore_used_promotion($data_option['promotion_code_seq']);

			}
		}
		if($suboptions){
			foreach($suboptions as $data_suboption){
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

		/* 배송비쿠폰 복원*/
		if($orders['download_seq']){
			$shippingcoupon = $this->couponmodel->restore_used_coupon($orders['download_seq']);
		}

		/* 배송비프로모션코드 복원 개별코드만 */
		if( $orders['shipping_promotion_code_seq'] ){
			$shippingpromotioncode = $this->promotionmodel->restore_used_promotion($orders['shipping_promotion_code_seq']);
		}


		$log = "-";
		$caccel_arr = array(
			'ea'	=> $tot_ea,
			'price'	=> $orders['settleprice']
		);

		$this->ordermodel->set_log($order_seq,'cancel',$this->managerInfo['mname'],$this->arr_step[95],$log,$caccel_arr);
		openDialogAlert($this->arr_step[95]."가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	// 결제확인
	public function deposit(){

		$this->load->model('membermodel');

		$auth = $this->authmodel->manager_limit_act('order_deposit');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
		$order_seq = $_GET['seq'];
		$orders	= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['order_deposit']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 ".$this->arr_step[25]."를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}
		$this->coupon_reciver_sms	= array();
		$this->coupon_order_sms		= array();
		$this->ordermodel->set_step($order_seq,25);
        $log_str = "관리자가 ".$this->arr_step[25]."을 하였습니다.";
        $this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],$this->arr_step[25],$log_str);

		/* 해당 주문 상품의 출고예약량 업데이트 */
        $result_option = $this->ordermodel->get_item_option($order_seq);
	   	$result_suboption = $this->ordermodel->get_item_suboption($order_seq);

	   	// 출고량 업데이트를 위한 변수선언
	   	$r_reservation_goods_seq = array();

		if($result_option){
			foreach($result_option as $data_option){
				if	($data_option['goods_kind'] == 'coupon')	$coupon++;
				else											$goods++;

				// 출고량 업데이트를 위한 변수정의
				if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
					$r_reservation_goods_seq[] = $data_option['goods_seq'];
				}
			}
		}
		if($result_suboption){
			foreach($result_suboption as $data_suboption){
				if	($data_suboption['goods_kind'] == 'coupon')	$coupon++;
				else											$goods++;

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

		###
		$result = typereceipt_setting($order_seq);

		// 네이버 마일리지
		/*
		$navermileage_url = 'http://'.$_SERVER['HTTP_HOST'].'/naver_mileage/batch';
		$out = readurl($navermileage_url);
		*/

        // 결제확인 메일링
        send_mail_step25($order_seq);
		$order_count = 0;
		if( $orders['order_cellphone'] ){
			$items	= $this->ordermodel->get_item($order_seq);
			$params['goods_name']	= $items[0]['goods_name'];
			if	(count($items) > 1)
				$params['goods_name']	.= '외 '.(count($items) - 1).'건';

			if	($orders['payment'] == 'bank'){
				$bank_arr				= explode(' ', $orders['bank_account']);
				$params['settle_kind']	= $bank_arr[0] . ' 입금확인';
			}else{
				$params['settle_kind']	= $orders['mpayment'] . ' 입금확인';
			}

			$params['shopName']		= $this->config_basic['shopName'];
			$params['ordno']		= $order_seq;
			$params['user_name']	= $orders['order_user_name'];
			$params['member_seq']	= $orders['member_seq'];
			$arr_params[$order_count]		= $params;
			$order_no[$order_count]			= $order_seq;
			$order_cellphones[$order_count] = $orders['order_cellphone'];
			$order_count					=$order_count+1;

			if( $orders['sms_25_YN'] != 'Y' ) {
				$this->db->where('order_seq', $orders['order_seq']);
				$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
			}
		}

		//결제 확인 SMS 데이터 생성
		if(count($order_cellphones) > 0){
			$commonSmsData['settle']['phone'] = $order_cellphones;
			$commonSmsData['settle']['params'] = $arr_params;
			$commonSmsData['settle']['order_no'] = $order_no;
		}

		//받는 사람 쿠폰 SMS 데이터
		if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
			$order_count = 0;
			foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
				$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
				$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
				$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
				$order_count					=$order_count+1;
			}

			$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;
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

			$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;
			$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
			$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;
			
		}

		if(count($commonSmsData) > 0){
			commonSendSMS($commonSmsData);
		}

		$endMsg	= "<div><b>결제가 확인되었습니다.</b></div><br/>";
		if	($goods > 0){
			$endMsg	.= "<div style=\"text-align:left;\">▶ 실물상품 : 출고처리하여 상품을 발송하세요.</div><br/>";
		}
		if	($coupon > 0){
			$endMsg	.= "<div style=\"text-align:left;\">▶ 쿠폰상품 : 쿠폰번호가 발송되었습니다.</div><br/>";
		}

		openDialogAlert($endMsg,500,250,'parent',"parent.location.reload();");
	}


	public function receipt_process(){
		$order_seq	=  $_GET['order_seq'];
		$seq		=  $_GET['seq'];
		$result = typereceipt_setting($order_seq, $seq);
		if($result){
			$return["result"] = true;
		}else{
			$return["result"] = false;
		}
		echo json_encode($return);
		exit;
	}

	// 에누리
	public function enuri(){
		$order_seq = $_GET['seq'];
		$enuri = (int) $_POST['enuri'];
		$orders	= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['enuri']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 에누리 변경을 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		if( !$ordres['payment'] != 'bank' ){
			openDialogAlert("무통장 주문만 에누리를 적용할 수 있습니다.",400,140,'parent',"");
			exit;
		}

		if( $enuri > $orders['settleprice']+$orders['enuri']){
			openDialogAlert("에누리금액은 결제금액을 초과할 수 없습니다.",400,140,'parent',"");
			exit;
		}

		if($enuri != $orders['enuri']){
			$this->ordermodel->set_enuri($order_seq,$enuri, $orders['enuri']);

			// 세금계산서를 신청한 경우 증빙금액 업데이트 
			if ($orders['typereceipt'] == 1) {
				$this->ordermodel->update_tax_sales($order_seq);
			}
			$log_str = "에누리가 변경 되었습니다.";
        	$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'에누리 변경 ('.$orders['enuri'].'원 ->'.$enuri.'원 )',$log_str);
			openDialogAlert("에누리가 변경 되었습니다.",400,140,'parent',"parent.location.reload();");
		}

	}

	// 출고처리
	public function goods_export(){

		$this->coupon_reciver_sms = array();
		$this->coupon_order_sms = array();

		$this->load->model('membermodel');
		$this->load->model('exportmodel');

		$auth = $this->authmodel->manager_limit_act('order_goods_export');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
		$order_seq = $_POST['order_seq'];
		$orders	= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['goods_export']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 출고처리를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		// 쿠폰상품 출고가 있음.
		if	(count($_POST['export_coupon']) > 0){
			$export_coupon_ex	= 1;
			if	(!$_POST['coupon_mail'] || !$_POST['coupon_sms']){
				openDialogAlert("쿠폰 상품 출고 시 발송 이메일과 휴대폰번호는 필수입니다.",400,140,'parent',"");
				exit;
			}
		}

		$tot_ea = 0;
		foreach($_POST['option_export_ea'] as $item_seq => $tmp)
			foreach($tmp as $option_seq => $ea)
				$tot_ea += (int) $ea;

		if($_POST['suboption_export_ea'])
			foreach($_POST['suboption_export_ea'] as $item_seq => $tmp)
				foreach($tmp as $option_seq => $ea)
					$tot_ea += (int) $ea;

		if($tot_ea == 0 && !$export_coupon_ex){
			openDialogAlert("출고하실 상품이 없습니다.",400,140,'parent',"");
			exit;
		}

		// 출고 수량 및 재고 체크
		if(!$cfg_order) $cfg_order = config_load('order');
		if($_POST['option_export_ea']) foreach($_POST['option_export_ea'] as $item_seq => $tmp){
			foreach($tmp as $option_seq => $ea){
				$ea = (int) $ea;
				$option_remind_ea[$option_seq] = $this->ordermodel->check_option_remind_ea($ea,$option_seq);
				if($option_remind_ea[$option_seq] === false){
					openDialogAlert("출고수량이 주문수량을 초과하였습니다.",400,140,'parent',"");
					exit;
				}

				if($cfg_order['export_err_handling'] == 'error'){
					$data_option = $this->ordermodel->get_order_item_option($option_seq);
					$goods_seq = $data_option['goods_seq'];
					$option1 = $data_option['option1'];
					$option2 = $data_option['option2'];
					$option3 = $data_option['option3'];
					$option4 = $data_option['option4'];
					$option5 = $data_option['option5'];
					$goods_stock = (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
					if($goods_stock < $ea){
						openDialogAlert("‘출고수량’ 보다 ‘재고수량’이 부족합니다.<br/>출고가 가능한 수량으로 조정해 주세요",400,150,'parent',"");
						exit;
					}
				}
			}
		}
		if($_POST['suboption_export_ea']) foreach($_POST['suboption_export_ea'] as $item_seq => $tmp){
			foreach($tmp as $suboption_seq => $ea){
				$ea = (int) $ea;
				$suboption_remind_ea[$suboption_seq] = $this->ordermodel->check_suboption_remind_ea($ea,$suboption_seq);
				if($suboption_remind_ea[$suboption_seq] === false){
					openDialogAlert("출고수량이 주문수량을 초과하였습니다.",400,140,'parent',"");
					exit;
				}

				if($cfg_order['export_err_handling'] == 'error'){
					$data_option = $this->ordermodel->get_order_item_suboption($suboption_seq);
					$goods_seq = $data_option['goods_seq'];
					$title = $data_option['title'];
					$suboption = $data_option['suboption'];
					$goods_stock = (int) $this->goodsmodel->get_goods_suboption_stock($goods_seq,$title,$suboption);
					if($goods_stock < $ea){
						openDialogAlert("‘출고수량’ 보다 ‘재고수량’이 부족합니다.<br/>출고가 가능한 수량으로 조정해 주세요",400,150,'parent',"");
						exit;
					}
				}
			}
		}

		// 쿠폰상품 수량 및 재고 체크 ( 실물에서 이미 수량초과면 체크안함. )
		if	($export_coupon_ex){
			foreach($_POST['export_coupon'] as $item_seq => $opt){
				foreach($opt as $option_seq => $export){
					$ea	= 0;
					foreach($export as $k => $export_code){
						if	(!$export_code)	$ea++;
					}
					$chk_coupon	= $this->ordermodel->check_option_remind_ea($ea, $option_seq);
					if	($chk_coupon === false){
						openDialogAlert("출고수량이 주문수량을 초과하였습니다.",400,140,'parent',"");
						exit;
					}

					if($cfg_order['export_err_handling'] == 'error'){
						$data_option = $this->ordermodel->get_order_item_option($option_seq);
						$goods_seq = $data_option['goods_seq'];
						$option1 = $data_option['option1'];
						$option2 = $data_option['option2'];
						$option3 = $data_option['option3'];
						$option4 = $data_option['option4'];
						$option5 = $data_option['option5'];
						$goods_stock = (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
						if($goods_stock < $ea){
							openDialogAlert("‘출고수량’ 보다 ‘재고수량’이 부족합니다.<br/>출고가 가능한 수량으로 조정해 주세요",400,150,'parent',"");
							exit;
						}
					}
				}
			}
		}

		// 실물 상품 출고처리
		if($tot_ea > 0){
			$export_status	= true;
			$export_goods++;

			$data['status']						= $_POST['delivery_step'];
			$data['order_seq']					= $_POST['order_seq'];
			$data['shipping_seq']				= $_POST['shipping_seq'];
			$data['international']				= $_POST['international'];

			if($_POST['international'] == 'domestic'){
				$data['domestic_shipping_method']	= $_POST['domestic_shipping_method'];
				$data['delivery_company_code'] 		= $_POST['delivery_company'];
				$data['delivery_number']			= $_POST['delivery_number'];
			}else{
				$data['international_shipping_method']	= $_POST['international_shipping_method'];
				$data['international_delivery_no']		= $_POST['international_number'];
			}

			$data['export_date']				= $_POST['export_date'];
			$data['status'] 					= $_POST['delivery_step'];
			$data['regist_date']				= date('Y-m-d H:i:s');
			if($_POST['delivery_step'] == 55){//출고완료인경우
				$data['complete_date']	= date('Y-m-d H:i:s');
			}
			$export_code = $this->exportmodel->insert_export($data);

			unset($data);
			foreach($_POST['option_export_ea'] as $item_seq => $tmp){
				foreach($tmp as $option_seq => $ea){
					$ea = (int) $ea;
					$data['item_seq'] 		= $item_seq;
					$data['export_code'] 	= $export_code;
					$data['option_seq'] 	= $option_seq;
					$data['ea'] 			= $ea;
					if( $ea > 0 ){
						$this->db->insert('fm_goods_export_item', $data);
					}

					// 주문상태별 수량 변경
					$this->ordermodel->set_step_ea($_POST['delivery_step'],$ea,$option_seq,'option');

					// 주문 option 상태 변경
					$this->ordermodel->set_option_step($option_seq,'option');
				}
			}

			unset($data);
			if($_POST['suboption_export_ea']) foreach($_POST['suboption_export_ea'] as $item_seq => $tmp){
				foreach($tmp as $suboption_seq => $ea){
					$ea = (int) $ea;
					$data['item_seq'] 		= $item_seq;
					$data['export_code'] 	= $export_code;
					$data['suboption_seq'] 	= $suboption_seq;
					$data['ea'] 			= $ea;
					if( $ea > 0 ) $this->db->insert('fm_goods_export_item', $data);

					// 주문상태별 수량 변경
					$this->ordermodel->set_step_ea($_POST['delivery_step'],$ea,$suboption_seq,'suboption');

					// 주문 option 상태 변경
					$this->ordermodel->set_option_step($suboption_seq,'suboption');
				}
			}

			// 주문상태 변경
			$this->ordermodel->set_order_step($order_seq);

			// 출고량 업데이트를 위한 변수선언
			$r_reservation_goods_seq = array();

			// 출고 완료 시 재고 차감
			if($_POST['delivery_step'] == '55'){
				$export_item = $this->exportmodel->get_export_item($export_code);
				foreach($export_item as $item){
					if($item['opt_type'] == 'opt'){
						$this->goodsmodel->stock_option(
							'-',
							$item['ea'],
							$item['goods_seq'],
							$item['option1'],
							$item['option2'],
							$item['option3'],
							$item['option4'],
							$item['option5']
						);

						// 출고량 업데이트를 위한 변수정의
						if(!in_array($item['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $item['goods_seq'];
						}

					}else{
						$this->goodsmodel->stock_suboption(
							'-',
							$item['ea'],
							$item['goods_seq'],
							$item['title1'],
							$item['option1']
						);

						// 출고량 업데이트를 위한 변수정의
						if(!in_array($item['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $item['goods_seq'];
						}

					}
				}

				// 출고예약량 업데이트
				foreach($r_reservation_goods_seq as $goods_seq){
					$this->goodsmodel->modify_reservation_real($goods_seq);
				}

				/* 출고자동화 전송 */
				$this->load->model('invoiceapimodel');
				$invoiceExportResult = $this->invoiceapimodel->export($export_code);
				if($invoiceExportResult['resultDeliveryNumber'] && !$_POST['delivery_number']){
					$_POST['delivery_number'] = $invoiceExportResult['resultDeliveryNumber'][0];
				}

				if(!$orders['linkage_id']){
					// 출고완료 메일링
					send_mail_step55($export_code);

					// 출고완료시 sms
					if( $orders['order_cellphone'] ){
						$this->load->helper('shipping');
						$shipping_company_arr	= get_shipping_company($orders['international'],$orders['shipping_method']);
						$params['delivery_company']	= $shipping_company_arr[$_POST['delivery_company']]['company'];
						$params['delivery_number']	= $_POST['delivery_number'];

						$params['goods_name']	= $export_item[0]['goods_name'];
						if	(count($export_item) > 1)
							$params['goods_name']	.= '외 '.(count($export_item) - 1).'건';

						if	($orders['payment'] == 'bank'){
							$bank_arr				= explode(' ', $orders['bank_account']);
							$params['settle_kind']	= $bank_arr[0] . ' 입금확인';
						}else{
							$params['settle_kind']	= $orders['mpayment'] . ' 입금확인';
						}

						$params['shopName']		= $this->config_basic['shopName'];
						$params['ordno']		= $order_seq;
						$params['export_code']	= $export_code;
						$params['member_seq']	= $orders['member_seq'];
						$params['user_name']	= $orders['order_user_name'];

						//결제 확인 SMS 데이터 생성
						if(count($orders['order_cellphone']) > 0){
							$commonSmsData['released']['phone'][] = $orders['order_cellphone'];
							$commonSmsData['released']['params'][] = $params;
							$commonSmsData['released']['order_no'][] = $order_no;
						}

						
						# 주문자와 받는분이 다를때 받는분에게도 문자 전송
						if( $orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))) {
							$recipient_cellphones[0] = $orders['recipient_cellphone'];
							$commonSmsData['released2']['phone'][] = $orders['recipient_cellphone'];
							$commonSmsData['released2']['params'][] = $params;
							$commonSmsData['released2']['order_no'][] = $order_no;

						}

					}
				}

				// 네이버 마일리지
				/*
				$navermileage_url = 'http://'.$_SERVER['HTTP_HOST'].'/naver_mileage/batch';
				$out = readurl($navermileage_url);
				*/

			}

		}

		// 쿠폰상품 출고 및 재발송 처리
		if	($export_coupon_ex){
			foreach($_POST['export_coupon'] as $item_seq => $opt){
				foreach($opt as $option_seq => $export){
					$coupon_ea	= 0;
					foreach($export as $k => $exportCode){
						if	($exportCode){
							$resend_coupon	= true;
							// 쿠폰상품 출고 메시지 재발송 ( 쿠폰은 1/ea당 출고 1건이기에 code로 처리 )
							$this->exportmodel->coupon_export_send($exportCode, 'all', $_POST['coupon_mail'], $_POST['coupon_sms']);
						}else{
							$coupon_ea++;
						}
					}

					if	($coupon_ea > 0){
						$export_status	= true;
						$export_coupon++;

						// 쿠폰상품 출고처리
						$param['option_seq']	= $option_seq;
						$param['export_date']	= $_POST['coupon_export_date'];
						$param['coupon_mail']	= $_POST['coupon_mail'];
						$param['coupon_sms']	= $_POST['coupon_sms'];
						$this->exportmodel->coupon_export($param, $coupon_ea);

						// 주문상태별 수량 변경
						$this->ordermodel->set_step_ea(55, $coupon_ea, $option_seq, 'option');

						// 주문 option 상태 변경
						$this->ordermodel->set_option_step($option_seq, 'option');

						//받는 사람 쿠폰 SMS 데이터
						if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
							$order_count = 0;
							foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
								$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
								$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
								$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
								$order_count					=$order_count+1;
							}

							$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;
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

							$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;
							$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
							$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;
							
						}
					}
				}
			}

			// 주문상태 변경
			$this->ordermodel->set_order_step($order_seq);
		}

		if	($export_status){

			# 오픈마켓 송장등록 #
			$this->load->model('openmarketmodel');
			$this->openmarketmodel->request_send_export($export_code);

			$log_str = "관리자가 출고처리를 하였습니다.";
			$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'출고처리',$log_str);
		}

		// 처리 완료 메시지
		if	($resend_coupon && !$export_goods && !$export_coupon){
			$endMsg		= "재발송 되었습니다.";
		}else{
			if	($export_goods > 0){
				if	($_POST['delivery_step'] == '55')
					$endMsg[]	= "실물상품이 출고완료 처리 되었습니다.";
				else
					$endMsg[]	= "실물상품이 출고준비 처리 되었습니다.";
			}
			if	($export_coupon > 0)
				$endMsg[]	= "쿠폰상품의 쿠폰번호가 발송되었습니다.";

			$endMsg	= implode('<br/>', $endMsg);
		}


		if(count($commonSmsData) > 0){
			commonSendSMS($commonSmsData);
		}

		openDialogAlert($endMsg,400,140,'parent',"parent.location.reload();");
	}

	// 일괄 결제확인
	public function batch_deposit(){

		$this->coupon_reciver_sms = array();
		$this->coupon_order_sms = array();

		$this->load->model("membermodel");

		$auth = $this->authmodel->manager_limit_act('order_deposit');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
		$order_count=0;

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		foreach($_POST['seq'] as $order_seq){
			$orders	= $this->ordermodel->get_order($order_seq);
			if( !in_array($orders['step'],$this->ordermodel->able_step_action['order_deposit']) ){
				$result['error'][] = $order_seq;
				echo json_encode( $result );
				exit;
			}

			$this->ordermodel->set_step($order_seq,25);
	        $log_str = "관리자가 ".$this->arr_step[25]."을 하였습니다.";
	        $this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],$this->arr_step[25],$log_str);
			$result['ok'][] = $order_seq;

			/* 출고예약량 업데이트 */
	        $result_option = $this->ordermodel->get_item_option($order_seq);
		   	$result_suboption = $this->ordermodel->get_item_suboption($order_seq);
			if($result_option){
				foreach($result_option as $data_option){
					if	($data_option['goods_kind'] == 'coupon')	$coupon_cnt++;
					else											$goods_cnt++;

					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_option['goods_seq'];
					}
				}
			}
			if($result_suboption){
				foreach($result_suboption as $data_suboption){
					if	($data_suboption['goods_kind'] == 'coupon')	$coupon_cnt++;
					else											$goods_cnt++;

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

			$this->load->model('salesmodel');
			$result = typereceipt_setting($order_seq);

			// 결제 확인 메일링
        	send_mail_step25($order_seq);

			if( $orders['order_cellphone'] ){
				$params['shopName']				= $this->config_basic['shopName'];
				$params['ordno']				= $order_seq;
				$params['member_seq']			= $orders['member_seq'];
				$params['user_name']			= $orders['order_user_name'];
				$arr_params[$order_count]		= $params;
				$order_no[$order_count]			= $order_seq;
				$order_cellphones[$order_count] = $orders['order_cellphone'];
				$order_count					=$order_count+1;
				
				if( $orders['sms_25_YN'] != 'Y' ) {
					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
				}
			}
		}
		
		//결제 확인 SMS 데이터 생성
		if(count($order_cellphones) > 0){
			$commonSmsData['settle']['phone'] = $order_cellphones;
			$commonSmsData['settle']['params'] = $arr_params;
			$commonSmsData['settle']['order_no'] = $order_no;
		}

		//받는 사람 쿠폰 SMS 데이터
		if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
			$order_count = 0;
			foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
				$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
				$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
				$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
				$order_count					=$order_count+1;
			}

			$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;
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

			$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;
			$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
			$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;
			
		}
		
		if(count($commonSmsData) > 0){
			commonSendSMS($commonSmsData);
		}

		// 네이버 마일리지
		anchor('naver_mileage/batch');

		// 실물 + 쿠폰
		if		($coupon_cnt > 0 && $goods_cnt > 0)	$result	= 'all';
		elseif	($coupon_cnt > 0)					$result	= 'coupon';	// 쿠폰
		else										$result	= 'goods';	// 실물

		echo $result;
	}

	// 일괄 주문 무효
	public function batch_cancel_order(){

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		foreach($_POST['seq'] as $order_seq){
			$this->ordermodel->set_step($order_seq,95);
			$options	= $this->ordermodel->get_item_option($order_seq);
			$suboptions	= $this->ordermodel->get_item_suboption($order_seq);
			$orders		= $this->ordermodel->get_order($order_seq);

			if($options) foreach($options as $k => $option){
				$tot_ea		+= $option['ea'];
			}
			if($suboptions) foreach($suboptions as $k => $option){
				$tot_ea		+= $option['ea'];
			}
			if($orders['member_seq']){
				$this->load->model('membermodel');

				/* 적립금 환원 */
				if($orders['emoney_use']=='use' && $orders['emoney']>0 )
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'cancel',
						'emoney'	=> $orders['emoney'],
						'ordno'	=> $order_seq,
						'memo'		=> "[복원]".$this->arr_step[95]."(".$order_seq.")에 의한 적립금 환원",
					);
					$this->membermodel->emoney_insert($params, $orders['member_seq']);
					$this->ordermodel->set_emoney_use($order_seq,'return');
				}

				/* 이머니 환원 */
				if($orders['cash_use']=='use' && $orders['cash']>0)
				{
					$this->load->model('membermodel');
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'cancel',
						'cash'	=> $orders['cash'],
						'ordno'	=> $order_seq,
						'memo'		=> "[복원]".$this->arr_step[95]."(".$order_seq.")에 의한 이머니 환원",
					);
					$this->membermodel->cash_insert($params, $orders['member_seq']);
					$this->ordermodel->set_cash_use($order_seq,'return');
				}
			}

			/* 쿠폰 환원 */
			$this->load->model('couponmodel');
			$this->load->model('promotionmodel');
			/* 배송비쿠폰 복원*/
			if($orders['download_seq']){
				$shippingcoupon = $this->couponmodel->restore_used_coupon($orders['download_seq']);
			}

			/* 배송비프로모션코드 복원 개별코드만 */
			if($orders['shipping_promotion_code_seq']){
				$shippingpromotioncode = $this->promotionmodel->restore_used_promotion($orders['shipping_promotion_code_seq']);
			}

			//상품별 쿠폰/프로모션코드 복원
			foreach($options as $data_option){
				if($data_option['download_seq']) $this->couponmodel->restore_used_coupon($data_option['download_seq']);
				if($data_option['promotion_code_seq']) $this->promotionmodel->restore_used_promotion($data_option['promotion_code_seq']);
			}


			$log = "-";
			$caccel_arr = array(
				'ea'	=> $tot_ea,
				'price'	=> $orders['settleprice']
			);

			$this->ordermodel->set_log($order_seq,'cancel',$this->managerInfo['mname'],$this->arr_step[95],$log,$caccel_arr);
			/* 출고예약량 업데이트 */
			if($options){
				foreach($options as $data_option){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_option['goods_seq'];
					}
				}
			}
			if($suboptions){
				foreach($suboptions as $data_suboption){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_suboption['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_suboption['goods_seq'];
					}
				}
			}

		}

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		echo json_encode($result);
	}

	// 일괄 출고 확인
	public function batch_export(){
		//error_reporting(E_ALL);
		$auth = $this->authmodel->manager_limit_act('order_goods_export');

		$this->coupon_reciver_sms = array();
		$this->coupon_order_sms = array();

		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel'); //2014-07-21 추가
		$this->load->helper('shipping');

		##### 실물상품
		if	(count($_POST['ea']) > 0){

			$arr_export_code = array();

			// 상품재고 체크
			if(!$cfg_order) $cfg_order = config_load('order');

			$order_count = 0;
			$recipient_count = 0;
			foreach($_POST['ea'] as $shipping_seq => $ea){
				if(!$ea) continue;

				$order_seq		= $_POST['order_seq'][$shipping_seq];
				$shipping_items	= $this->ordermodel->get_shipping_item($order_seq,$shipping_seq, 'goods');

				// 주문 상품이 모두 출고  체크 및 상품 재고 체크
				$tot_remind = 0;
				$err_stock = false;
				foreach($shipping_items as $item){

					$goods_seq = $item['goods_seq'];

					unset($insert_param);

					foreach($item['shipping_item_option'] as $data){
						$step_complete = $this->ordermodel -> get_option_export_complete(
							$order_seq,
							$shipping_seq,
							$data['item_seq'],
							$data['item_option_seq']
						);
						$step_remind = $data['ea'] - $step_complete - $data['step85'];
						$tot_remind += $step_remind;

						// 상품 재고 체크
						if($cfg_order['export_err_handling'] == 'error' && $_POST['delivery_step'] == '55'){
							$option1 = $data['option1'];
							$option2 = $data['option2'];
							$option3 = $data['option3'];
							$option4 = $data['option4'];
							$option5 = $data['option5'];
							$goods_stock = (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
							if($goods_stock < $data['ea']){
								$stock_err_order_seq[] = $data['order_seq'];
								$err_stock = true;
							}
						}

						foreach($data['shipping_item_suboption'] as $data_sub){
							$step_complete = $this->ordermodel -> get_suboption_export_complete(
								$order_seq,
								$shipping_seq,
								$data_sub['item_seq'],
								$data_sub['item_suboption_seq']
							);
							$step_remind = $data_sub['ea'] - $step_complete - $data_sub['step85'];
							$tot_remind += $step_remind;

							// 상품 재고 체크
							if($cfg_order['export_err_handling'] == 'error' && $_POST['delivery_step'] == '55'){
								$title = $data_sub['title'];
								$suboption = $data_sub['suboption'];
								$goods_stock = (int) $this->goodsmodel->get_goods_suboption_stock($goods_seq,$title,$suboption);
								if($goods_stock < $data_sub['ea']){
									$stock_err_order_seq[] = $data_sub['order_seq'];
									$err_stock = true;
								}
							}
						}
					}
				}

				if( $tot_remind == 0 ) continue;
				if( $err_stock ) continue;

				unset($data);
				unset($data_sub);
				$data['order_seq']					= $order_seq;
				$data['shipping_seq']				= $shipping_seq;

				if($_POST['international'][$shipping_seq] == 'domestic'){
					if($_POST['domestic_shipping_method'][$shipping_seq])$data['domestic_shipping_method']	= $_POST['domestic_shipping_method'][$shipping_seq];
					if($_POST['delivery_company'][$shipping_seq])$data['delivery_company_code'] = $_POST['delivery_company'][$shipping_seq];
					if($_POST['delivery_number'][$shipping_seq])$data['delivery_number'] = $_POST['delivery_number'][$shipping_seq];
					$data['international']			= 'domestic';
				}else{
					$data['international_shipping_method']	= $_POST['international_shipping_method'][$shipping_seq];
					$data['international_delivery_no']		= $_POST['international_number'][$shipping_seq];
					$data['international']			= 'international';
				}

				$data['export_date']				= $_POST['export_date'];
				$data['status'] 					= $_POST['delivery_step'];

				if($_POST['delivery_step'] == 55){//출고완료인경우
					$data['complete_date']	= date('Y-m-d H:i:s');
				}
				$data['regist_date']				= date('Y-m-d H:i:s');

				// 출고테이블에 데이터 저장
				$export_code = $this->exportmodel->insert_export($data);

				foreach($shipping_items as $item){
					foreach($item['shipping_item_option'] as $data){
						$step_complete = $this->ordermodel -> get_option_export_complete(
							$order_seq,
							$shipping_seq,
							$data['item_seq'],
							$data['item_option_seq']
						);
						$step_remind = $data['ea'] - $step_complete - $data['step85'];

						if($step_remind > 0 ){
							unset($insert_param);
							$insert_param['item_seq'] 		= $data['item_seq'];
							$insert_param['export_code'] 	= $export_code;
							$insert_param['option_seq'] 	= $data['item_option_seq'];
							$insert_param['ea'] 			= $step_remind;

							$this->db->insert('fm_goods_export_item', $insert_param);

							// 주문상태별 수량 변경
							$this->ordermodel->set_step_ea($_POST['delivery_step'],$step_remind,$data['item_option_seq'],'option');

							// 주문 option 상태 변경
							$this->ordermodel->set_option_step($data['item_option_seq'],'option');
						}

						foreach($data['shipping_item_suboption'] as $data_sub){
							$step_complete = $this->ordermodel -> get_suboption_export_complete(
								$order_seq,
								$shipping_seq,
								$data_sub['item_seq'],
								$data_sub['item_suboption_seq']
							);
							$step_remind = $data_sub['ea'] - $step_complete - $data_sub['step85'];

							if($step_remind > 0 ){
								unset($insert_param);
								$insert_param['item_seq'] 		= $data_sub['item_seq'];
								$insert_param['export_code'] 	= $export_code;
								$insert_param['suboption_seq'] 	= $data_sub['item_suboption_seq'];
								$insert_param['ea'] 			= $step_remind;
								$this->db->insert('fm_goods_export_item', $insert_param);

								// 주문상태별 수량 변경
								$this->ordermodel->set_step_ea($_POST['delivery_step'],$step_remind,$data_sub['item_suboption_seq'],'suboption');
								// 주문 option 상태 변경
								$this->ordermodel->set_option_step($data_sub['item_suboption_seq'],'suboption');
							}
						}
					}
				}
				/*
				$items = $this->ordermodel->get_item($order_seq);
				foreach($items as $ik => $item){
					$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
					unset($insert_param);
					if($options)foreach($options as $k => $data){
						$step_complete = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
						$step_remind = $data['ea'] - $step_complete - $data['step85'];
						if($step_remind > 0 ){
							$insert_param['item_seq'] 		= $item['item_seq'];
							$insert_param['export_code'] 	= $export_code;
							$insert_param['option_seq'] 	= $data['item_option_seq'];
							$insert_param['ea'] 			= $step_remind;

							$this->db->insert('fm_goods_export_item', $insert_param);

							// 주문상태별 수량 변경
							$this->ordermodel->set_step_ea($_POST['delivery_step'],$step_remind,$data['item_option_seq'],'option');

							// 주문 option 상태 변경
							$this->ordermodel->set_option_step($data['item_option_seq'],'option');
						}
					}
					unset($insert_param);
					$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);
					if($suboptions)foreach($suboptions as $k => $data){
						$step_complete = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
						$step_remind = $data['ea'] - $step_complete - $data['step85'];
						if($step_remind > 0 ){
							$insert_param['item_seq'] 		= $item['item_seq'];
							$insert_param['export_code'] 	= $export_code;
							$insert_param['suboption_seq'] 	= $data['item_suboption_seq'];
							$insert_param['ea'] 			= $step_remind;
							$this->db->insert('fm_goods_export_item', $insert_param);
							// 주문상태별 수량 변경
							$this->ordermodel->set_step_ea($_POST['delivery_step'],$step_remind,$data['item_suboption_seq'],'suboption');
							// 주문 option 상태 변경
							$this->ordermodel->set_option_step($data['item_suboption_seq'],'suboption');
						}
					}
				}
				*/

				// 주문상태 변경
				$this->ordermodel->set_order_step($order_seq);
				$log_str = "관리자가 출고처리를 하였습니다.";
				$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'출고처리',$log_str);

				// 출고량 업데이트를 위한 변수선언
				$r_reservation_goods_seq = array();

				// 출고완료 메일링
				if( $_POST['delivery_step'] == '55' ){
					$export_item = $this->exportmodel->get_export_item($export_code);
					foreach($export_item as $item){
						if($item['opt_type'] == 'opt'){
							$this->goodsmodel->stock_option(
								'-',
								$item['ea'],
								$item['goods_seq'],
								$item['option1'],
								$item['option2'],
								$item['option3'],
								$item['option4'],
								$item['option5']
							);
						}else{
							$this->goodsmodel->stock_suboption(
								'-',
								$item['ea'],
								$item['goods_seq'],
								$item['title1'],
								$item['option1']
							);
						}

						// 출고량 업데이트를 위한 변수정의
						if(!in_array($item['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $item['goods_seq'];
						}
					}

					// 출고예약량 업데이트
					foreach($r_reservation_goods_seq as $goods_seq){
						$this->goodsmodel->modify_reservation_real($goods_seq);
					}

					/* 출고자동화 전송 */
					$this->load->model('invoiceapimodel');
					$this->invoiceapimodel->export($export_code);

					# 오픈마켓 송장등록 #
					$this->load->model('openmarketmodel');
					$this->openmarketmodel->request_send_export($export_code);

					$orders	= $this->ordermodel->get_order($order_seq);
					if(!$orders['linkage_id']){
						send_mail_step55($export_code);

						// 출고완료시 sms
						$orders	= $this->ordermodel->get_order($order_seq);
						if( $orders['order_cellphone'] ){

							$exports = get_data("fm_goods_export",array("export_code"=>$export_code));

							if(is_array($exports)){
								$delivery_number[$order_count]	 = $exports[0]['delivery_number'];
								$tmp = config_load('delivery_url',$exports[0]['delivery_company_code']);
								if(preg_match("/^auto_/",$exports[0]['delivery_company_code'])){
									foreach(get_invoice_company() as $k=>$data){
										$tmp[$k] = $data;
									}
								}
								$delivery_company[$order_count]	 = $tmp[$exports[0]['delivery_company_code']]['company'];
								$params['delivery_number'] = $delivery_number[$order_count];
								$params['delivery_company'] = $delivery_company[$order_count];
							}

							$params['shopName']		= $this->config_basic['shopName'];
							$params['ordno']		= $order_seq;
							$params['export_code']	= $export_code;
							$params['user_name']	= $orders['order_user_name'];
							$params['member_seq']	= $orders['member_seq'];
							$arr_params[$order_count] = $params;
							$order_no[$order_count] = $order_seq;
							$order_cellphones[$order_count]		= $orders['order_cellphone'];
							# 주문자와 받는분이 다를때 받는분에게도 문자 전송
							if( $orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))) {
								$recipient_cellphones[$recipient_count]	= $orders['recipient_cellphone'];	//받는분
								$recipient_arr_params[$recipient_count] = $params;
								$recipient_order_no[$recipient_count] = $order_seq;
								$recipient_count = $recipient_count+1;	

							}
							$order_count	= $order_count+1;
						}
					}
				}

				$arr_export_code[] = $export_code;
			}

			if($order_cellphones){
				$commonSmsData['released']['phone'] = $order_cellphones;
				$commonSmsData['released']['params'] = $arr_params;
				$commonSmsData['released']['order_no'] = $order_no;
			}
			# 주문자와 받는분이 다를때 받는분에게도 문자 전송
			if($recipient_cellphones){
				$commonSmsData['released2']['phone'] = $recipient_cellphones;
				$commonSmsData['released2']['params'] = $recipient_arr_params;
				$commonSmsData['released2']['order_no'] = $recipient_order_no;
			}

		}

		##### 쿠폰상품
		if	(count($_POST['coupon_export']) > 0){
			foreach($_POST['coupon_export'] as $shipping_seq => $order_seq){
				$orders			= $this->ordermodel->get_order($order_seq);
				$email			= $_POST['coupon_mail'][$shipping_seq];
				$sms			= $_POST['coupon_sms'][$shipping_seq];
				$coupon_export	= $_POST['coupon_export'][$shipping_seq];
				$coupon_cnt	= $this->exportmodel->coupon_payexport($order_seq, '', $email, $sms, $coupon_export);


				//받는 사람 쿠폰 SMS 데이터
				if(count($this->coupon_reciver_sms) > 0){
					$order_count = 0;
					foreach($this->coupon_reciver_sms as $smsData){
						$coupon_arr_params[$order_count]		= $smsData['params'];
						$coupon_order_no[$order_count]			= $smsData['order_no'];
						$coupon_order_cellphones[$order_count] = $smsData['order_cellphone'];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released2']['phone'] = $coupon_order_cellphones;
					$commonSmsData['coupon_released2']['params'] = $coupon_arr_params;
					$commonSmsData['coupon_released2']['order_no'] = $coupon_order_no;

				}
				
				//주문자 쿠폰 SMS 데이터
				if(count($this->coupon_order_sms) > 0){
					$order_count = 0;
					foreach($this->coupon_order_sms as $smsData){
						$reciver_arr_params[$order_count]		= $smsData['params'];
						$reciver_order_no[$order_count]			= $smsData['order_no'];
						$reciver_order_cellphones[$order_count] = $smsData['order_cellphone'];
						$order_count					=$order_count+1;
					}

					$commonSmsData['coupon_released']['phone'] = $reciver_order_cellphones;
					$commonSmsData['coupon_released']['params'] = $reciver_arr_params;
					$commonSmsData['coupon_released']['order_no'] = $reciver_order_no;
					
				}

				// 주문상태 변경
				$this->ordermodel->set_order_step($order_seq);
				$log_str = "관리자가 출고처리를 하였습니다.";
				$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'출고처리',$log_str);
			}
		}

		// 네이버 마일리지
		/*
		$navermileage_url = 'http://'.$_SERVER['HTTP_HOST'].'/naver_mileage/batch';
		$out = readurl($navermileage_url);
		*/

		if(count($commonSmsData) > 0){
			commonSendSMS($commonSmsData);
		}

		if( $stock_err_order_seq ){
			$msg = "출고처리  되었습니다.<br/>단, 재고수량이 부족한 상품이 있는 주문은 ".$this->arr_step[55]." 처리되지 않았습니다.";
			$stock_err_order_seq = array_unique($stock_err_order_seq);
			$msg .= '<br/>주문번호 : ' . implode('<br/>주문번호 : ', $stock_err_order_seq);
			$height = 150 + count($stock_err_order_seq) * 15;
			openDialogAlert($msg,500,$height,'parent',"parent.location.reload();");
		}else{
			if		(count($_POST['coupon_export']) > 0 && count($_POST['ea']) > 0){
				$endMsg	.= "실물상품이 출고완료 처리 되었습니다<br/><br/>쿠폰상품의 쿠폰번호가 발송되었습니다.<br/>";
			}elseif	(count($_POST['coupon_export']) > 0){
				$endMsg	.= "쿠폰번호가 발송되었습니다.";
			}else{
				$endMsg	.= "상품이 출고처리 되었습니다.";
			}
			openDialogAlert($endMsg,400,200,'parent',"parent.location.reload();");
		}
	}



	public function download_write(){
		## VALID
		$this->validation->set_rules('name', '이름', 'trim|required|xss_clean');		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		if(count($_POST['downloads_item_use'])<1){
			$callback = "parent.document.getElementsByName('name')[0].focus();";
			openDialogAlert("다운로드 항목을 1개 이상 설정해 주세요.",400,140,'parent',$callback);
			exit;
		}

		//print_r($_POST['downloads_item_use']);

		$item = implode("|",$_POST['downloads_item_use']);
		$params['name']			= $_POST['name'];
		$params['criteria']		= $_POST['criteria'];
		$params['item']			= $item;
		if($_POST['seq']){
			$this->db->where('seq', $_POST['seq']);
			$result = $this->db->update('fm_exceldownload', $params);
			$msg	= "수정 되었습니다.";
			$func	= "parent.location.reload();";
		}else{
			$params['regdate'] = date("Y-m-d H:i:s");
			$this->db->insert('fm_exceldownload', $params);
			$msg = "등록 되었습니다.";
			$func	= "parent.location.href = '/admin/order/download_list';";
		}
		openDialogAlert($msg,400,140,'parent',$func);

	}


	public function download_delete(){
		$seq = $_POST['seq'];
		$result = $this->db->delete('fm_exceldownload', array('seq' => $seq));
		openDialogAlert("삭제되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	public function excel_down(){

		if($_POST['order_seq']){
			$seq	= $_POST['seq'];
			$order_seq	= $_POST['order_seq'];
		}else{
			$seq	= $_GET['seq'];
			$order_seq	= $_GET['order_seq'];
		}
		$this->load->model('excelordermodel');
		$this->excelordermodel->create_excel_list($seq, $order_seq);
		exit;
	}

	public function excel_down_all(){
		$this->load->model('excelordermodel');
		$queryString		= $_REQUEST['order_seq'];
		$_PARAM				= unserialize(base64_decode($queryString));
		$_PARAM['seq']		= $_REQUEST['seq'];
		$_PARAM['isstep']	= $_REQUEST['step'];
		$this->excelordermodel->create_excel_list_for_all($_PARAM);
		exit;
	}

	//excel file down
	public function file_down(){
		$this->load->helper('download');
		if(is_file($_GET['realfiledir'])){
			$data = @file_get_contents($_GET['realfiledir']);
			force_download($_GET['filenames'], $data);
			exit;
		}
	}

	public function excel_upload(){
		###
		//error_reporting(E_ALL);
		$config['upload_path']		= $path = ROOTPATH."/data/tmp/";
		$config['overwrite']			= TRUE;
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
			$file_ext = end(explode('.', $_FILES['excel_file']['name']));//확장자추출
			$config['allowed_types']	= 'xls';
			$config['file_name']			= 'order_upload.'.$file_ext;
			$this->upload->initialize($config);
			if ($this->upload->do_upload('excel_file')) {
				$file_nm = $config['upload_path'].$config['file_name'];
				@chmod("{$file_nm}", 0777);
			}else{
				$callback = "";
				openDialogAlert("xls 파일만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}else{
			$callback = "";
			openDialogAlert("파일을 등록해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$this->load->model('excelordermodel');
		$result = $this->excelordermodel->excel_upload($file_nm, $_POST['step']);
		$result['height'] = ($result['height'])?$result['height']+150:'150';

		if($result['result_excel_url']){
			$callback = "document.location.href='{$result['result_excel_url']}'; setTimeout('parent.location.reload()',1000)";
		}
		else{
			$callback = "parent.location.reload();";
		}
		openDialogAlert($result['msg'],670,$result['height'],'parent',$callback);
		exit;
	}

	//결제취소 -> 환불
	public function order_refund(){

		$data_order = $this->ordermodel->get_order($_POST['order_seq']);
		$minfo = $this->session->userdata('manager');
		$manager_seq = $minfo['manager_seq'];

		if( !in_array($data_order['step'],array('25','35','40','45','50','60','70')) ){
			openDialogAlert($this->arr_step[$data_order['step']]."에서는 환불신청을 하실 수 없습니다.",400,140,'parent');
			exit;
		}

		if(!$_POST['chk_seq']){
			openDialogAlert("결제취소/환불 신청할 상품을 선택해주세요.",400,140,'parent');
			exit;
		}

		$order_total_ea = $this->ordermodel->get_order_total_ea($_POST['order_seq']);

		$cancel_total_ea = 0;
		foreach($_POST['chk_ea'] as $k=>$v){
			if(!$v){
				openDialogAlert("결제취소/환불 신청할 수량을 선택해주세요.",400,140,'parent');
				exit;
			}
			$cancel_total_ea += $v;
		}

		/* 신용카드 자동취소 */
		if($_POST['manual_refund_yn']=='y' && $data_order['payment']=='card' && $order_total_ea==$cancel_total_ea)
		{
			$pgCompany = $this->config_system['pgCompany'];

			$cancelFunction = "{$pgCompany}_cancel";
			$cancelResult = $this->refundmodel->$cancelFunction($data_order,array('refund_reason'=>$_POST['refund_reason'],'cancel_type'=>'full'));

			if(!$cancelResult['success']){
				openDialogAlert("{$pgCompany} 결제 취소 실패<br /><font color=red>{$cancelResult['result_code']} : {$cancelResult['result_msg']}</font>",400,160,'parent','');
				exit;
			}
			$_POST['cancel_type'] = 'full';
		}else if($order_total_ea==$cancel_total_ea){
			$_POST['cancel_type'] = 'full';
		}else{
			$_POST['cancel_type'] = 'partial';
		}

		$data = array(
			'order_seq' => $_POST['order_seq'],
			'bank_name' => $_POST['bank_name'],
			'bank_depositor' => $_POST['bank_depositor'],
			'bank_account' => $_POST['bank_account'],
			'refund_reason' => $_POST['refund_reason'],
			'refund_type' => 'cancel_payment',
			'cancel_type' => $_POST['cancel_type'],
			'regist_date' => date('Y-m-d H:i:s'),
			'manager_seq' => $manager_seq,
		);

		$items = array();

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		$this->db->trans_begin();
		$rollback = false;

		foreach($_POST['chk_seq'] as $k=>$v){

			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];
			$items[$k]['shipping_seq']	= $_POST['chk_shipping_seq'][$k];

			if($items[$k]['option_seq'] && !$items[$k]['suboption_seq']){
				$mode = 'option';

				$query = "select o.*, i.goods_seq from fm_order_item_option o, fm_order_item i  where o.item_seq=i.item_seq and o.item_option_seq=?";
				$query = $this->db->query($query,array($items[$k]['option_seq']));
				$optionData = $query->row_array();

				$rf_ea = $this->refundmodel->get_refund_option_ea($items[$k]['shipping_seq'],$items[$k]['item_seq'],$items[$k]['option_seq']);
				$step_complete = $this->ordermodel->get_option_export_complete($_POST['order_seq'],$items[$k]['shipping_seq'],$items[$k]['item_seq'],$items[$k]['option_seq']);
				$able_refund_ea = $optionData['ea'] - $rf_ea - $step_complete;
				if($able_refund_ea < $items[$k]['ea']){
					$rollback = true;
					break;
				}

				$this->ordermodel->set_step_ea(85,$items[$k]['ea'],$items[$k]['option_seq'],$mode);

				$query = "select o.*, i.goods_seq from fm_order_item_option o, fm_order_item i  where o.item_seq=i.item_seq and o.item_option_seq=?";
				$query = $this->db->query($query,array($items[$k]['option_seq']));
				$optionData = $query->row_array();

				if($optionData['ea']==$optionData['step85']){
					$this->db->set('step','85');
				}

				$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
				$this->db->where('item_option_seq',$items[$k]['option_seq']);
				$this->db->update('fm_order_item_option');

				// 주문 option 상태 변경
				$this->ordermodel->set_option_step($items[$k]['option_seq'],'option');

				// 출고량 업데이트를 위한 변수정의
				if(!in_array($optionData['goods_seq'],$r_reservation_goods_seq)){
					$r_reservation_goods_seq[] = $optionData['goods_seq'];
				}

			}else if($items[$k]['suboption_seq']){
				$mode = 'suboption';

				$query = "select o.*, i.goods_seq from fm_order_item_suboption o, fm_order_item i  where o.item_seq=i.item_seq and o.item_suboption_seq=?";
				$query = $this->db->query($query,array($items[$k]['suboption_seq']));
				$optionData = $query->row_array();

				$rf_ea = $this->refundmodel->get_refund_suboption_ea($items[$k]['shipping_seq'],$items[$k]['item_seq'],$items[$k]['suboption_seq']);
				$step_complete = $this->ordermodel->get_suboption_export_complete($_POST['order_seq'],$items[$k]['shipping_seq'],$items[$k]['item_seq'],$items[$k]['suboption_seq']);
				$able_refund_ea = $optionData['ea'] - $rf_ea - $step_complete;
				if($able_refund_ea < $items[$k]['ea']){
					$rollback = true;
					break;
				}

				$this->ordermodel->set_step_ea(85,$items[$k]['ea'],$items[$k]['suboption_seq'],$mode);

				$query = "select o.*, i.goods_seq from fm_order_item_suboption o, fm_order_item i  where o.item_seq=i.item_seq and o.item_suboption_seq=?";
				$query = $this->db->query($query,array($items[$k]['suboption_seq']));
				$optionData = $query->row_array();

				if($optionData['ea']==$optionData['step85']){
					$this->db->set('step','85');
				}

				$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
				$this->db->where('item_suboption_seq',$items[$k]['suboption_seq']);
				$this->db->update('fm_order_item_suboption');

				// 주문 option 상태 변경
				$this->ordermodel->set_option_step($items[$k]['suboption_seq'],'suboption');

				// 출고량 업데이트를 위한 변수정의
				if(!in_array($optionData['goods_seq'],$r_reservation_goods_seq)){
					$r_reservation_goods_seq[] = $optionData['goods_seq'];
				}
			}

		}

		if ($this->db->trans_status() === FALSE || $rollback == true)
		{
		    $this->db->trans_rollback();
		    openDialogAlert('처리 중 오류가 발생했습니다.',400,140,'parent','');
			exit;
		}
		else
		{
		    $this->db->trans_commit();
		}

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		$this->ordermodel->set_order_step($_POST['order_seq']);
		$refund_code = $this->refundmodel->insert_refund($data,$items);

		/* 신용카드 자동취소 */
		if($_POST['manual_refund_yn']=='y' && $data_order['payment']=='card' && $order_total_ea==$cancel_total_ea)
		{
			$this->load->model('emoneymodel');
			$this->load->model('membermodel');
			$this->load->model('couponmodel');
			$this->load->model('promotionmodel');
			$this->load->helper('text');

			$refund_emoney = 0;
			$refund_cash = 0;

			$data_refund_item 	= $this->refundmodel->get_refund_item($refund_code);
			$data_member		= $this->membermodel->get_member_data($data_order['member_seq']);

			//상품별 쿠폰/프로모션코드 복원
			foreach($_POST['chk_seq'] as $k=>$v){
				$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
				$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
				$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
				$items[$k]['ea']			= $_POST['chk_ea'][$k];

				if($items[$k]['option_seq'] && !$items[$k]['suboption_seq']){
					$query = "select * from fm_order_item_option where item_option_seq=?";
					$query = $this->db->query($query,array($items[$k]['option_seq']));
					$optionData = $query->row_array();

					/* 쿠폰 복원*/
					if($optionData['download_seq']){
						$optcoupon = $this->couponmodel->restore_used_coupon($optionData['download_seq']);
						if($optcoupon){
							$data_order['coupon_sale'] += $optionData['coupon_sale'];
						}
					}

					/* 프로모션코드 복원 개별코드만 */
					if($optionData['promotion_code_seq']){
						$optpromotioncode = $this->promotionmodel->restore_used_promotion($optionData['promotion_code_seq']);
						if($optpromotioncode){
							$data_order['shipping_promotion_code_sale'] += $optionData['promotion_code_sale'];
						}
					}

				}
			}

			/* 배송비쿠폰 복원*/
			if($data_order['download_seq']){
				$shippingcoupon = $this->couponmodel->restore_used_coupon($data_order['download_seq']);
			}

			/* 배송비프로모션코드 복원 개별코드만 */
			if($data_order['shipping_promotion_code_seq']){
				$shippingpromotioncode = $this->promotionmodel->restore_used_promotion($data_order['shipping_promotion_code_seq']);
			}

			if($data_order['member_seq']){
				/* 적립금 지급 */
				if($data_order['emoney_use']=='use' && $data_order['emoney'] > 0 )
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'cancel',
						'emoney'	=> $data_order['emoney'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[복원]결제취소({$refund_code})에 의한 적립금 환원",
					);
					$this->membermodel->emoney_insert($params, $data_order['member_seq']);
					$this->ordermodel->set_emoney_use($data_order['order_seq'],'return');

					$refund_emoney = $data_order['emoney'];
				}

				/* 이머니 지급 */
				if($data_order['cash_use']=='use' && $data_order['cash'] > 0 )
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'cancel',
						'cash'	=> $data_order['cash'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[복원]결제취소({$refund_code})에 의한 이머니 환원",
					);
					$this->membermodel->cash_insert($params, $data_order['member_seq']);
					$this->ordermodel->set_cash_use($data_order['order_seq'],'return');

					$refund_cash = $data_order['cash'];
				}
			}

			$saveData = array(
				'adjust_use_coupon'		=> $data_order['coupon_sale'],
				'adjust_use_promotion'		=> $data_order['shipping_promotion_code_sale'],
				'adjust_use_emoney'		=> $data_order['emoney'],
				'adjust_use_cash'		=> $data_order['cash'],
				'adjust_use_enuri'		=> $data_order['enuri'],
				'refund_method'			=> 'card',
				'refund_price'			=> $data_order['settleprice'],
				'refund_emoney'			=> $refund_emoney,
				'refund_cash'			=> $refund_cash,
				'status'				=> 'complete',
				'refund_date'			=> date('Y-m-d H:i:s')
			);
			$this->db->where('refund_code', $refund_code);
			$this->db->update("fm_order_refund",$saveData);

			// 추가옵션 관련 아이템 재배열
			$items_array	= array();
			if($data_refund_item)foreach($data_refund_item as $item){
				if($item['title1'])		$item['options_str']  = $item['title1'] .":".$item['option1'];
				if($item['title2'])		$item['options_str'] .= " / ".$item['title2'] .":".$item['option2'];
				if($item['title3'])		$item['options_str'] .= " / ".$item['title3'] .":".$item['option3'];
				if($item['title4'])		$item['options_str'] .= " / ".$item['title4'] .":".$item['option4'];

				if	($item['opt_type'] == 'sub'){
					$item['price']								= $item['price'] * $item['ea'];
					$item['sub_options']							= $item['options_str'];
					if	($first_option_seq)
						$items_array[$first_option_seq]['sub'][]		= $item;
					else
						$items_array[$item['option_seq']]['sub'][]		= $item;
				}else{
					$items_array[$item['option_seq']]['price']			+= $item['price'] * $row['ea'];
					$items_array[$item['option_seq']]['ea']			+= $item['ea'];
					$items_array[$item['option_seq']]['goods_name']	= $item['goods_name'];
					$items_array[$item['option_seq']]['options']		= $item['options_str'];
					$items_array[$item['option_seq']]['inputs']		= $this->ordermodel->get_input_for_option($item['item_seq'], $item['option_seq']);
				}
				if	(!$first_option_seq)	$first_option_seq	= $item['option_seq'];
			}

			$order_itemArr = array();
			$order_itemArr['order_seq'] = $data_order['order_seq'];
			$order_itemArr['mpayment'] = $data_order['mpayment'];
			$order_itemArr['deposit_date'] = $data_order['deposit_date'];
			$order_itemArr['pg_transaction_number'] = $data_order['pg_transaction_number'];

			/* 결제취소완료 안내메일 발송 */
			$params = array_merge($saveData,$_POST);
			$params	= array_merge($params,$data_member);
			$params['refund_reason']	= htmlspecialchars($_POST['refund_reason']);
			$params['refund_date']		= $saveData['refund_date'];
			$params['mstatus'] 			= $this->refundmodel->arr_refund_status['complete'];
			$params['refund_price']		= number_format($saveData['refund_price']);
			$params['mrefund_method']	= $this->arr_payment['card'].' '.$this->arr_step[85];
			$params['items'] 			= $items_array;
			$params['order']			= $order_itemArr;
			$result = sendMail($data_member['email'], 'cancel', $data_member['userid'], $params);

			/* 결제취소완료 SMS 발송 */
			$params					= array();
			$params['shopName']		= $this->config_basic['shopName'];
			$params['ordno']		= $data_order['order_seq'];
			$params['member_seq']	= $data_order['member_seq'];
			$params['user_name']	= $data_order['order_user_name'];

			//SMS 데이터 생성
			$commonSmsData['cancel']['phone'][] = $data_order['order_cellphone'];
			$commonSmsData['cancel']['params'][] = $params;
			$commonSmsData['cancel']['order_no'][] = $data_order['order_seq'];
			commonSendSMS($commonSmsData);

			$logTitle	= $this->arr_step[85];
			$logDetail	= "신용카드 전체취소처리하였습니다.";
			$logParams	= array('refund_code' => $refund_code);
			$this->ordermodel->set_log($_POST['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);

			$callback = "
			parent.closeDialog('order_refund_layer');
			parent.document.location.reload();
			";
			openDialogAlert("신용카드 ".$this->arr_step[85]."가 완료되었습니다.",400,140,'parent',$callback);
		}else{

			$logTitle	= "환불신청";
			$logDetail	= $this->arr_step[85]."/환불신청하였습니다.";
			$logParams	= array('refund_code' => $refund_code);
			$this->ordermodel->set_log($_POST['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);

			$callback = "
			parent.closeDialog('order_refund_layer');
			parent.document.location.reload();
			";
			openDialogAlert($this->arr_step[85]."/환불 신청이 완료되었습니다.",400,140,'parent',$callback);
		}



	}

	//결제 취소처리
	public function order_refund_etc()
	{
		$data_order = $this->ordermodel->get_order($_POST['order_seq']);
		$minfo = $this->session->userdata('manager');
		$manager_seq = $minfo['manager_seq'];

		if( !in_array($data_order['step'],array('25','35','40','45','50','60','70')) ){
			openDialogAlert($this->arr_step[$data_order['step']]."에서는 환불신청을 하실 수 없습니다.",400,140,'parent');
			exit;
		}

		$data = array(
				'order_seq' => $_POST['order_seq'],
				'bank_name' => $_POST['bank_name'],
				'bank_depositor' => $_POST['bank_depositor'],
				'bank_account' => $_POST['bank_account'],
				'refund_reason' => $_POST['refund_reason'],
				'refund_type' => 'cancel_payment',
				'cancel_type' => 'partial',
				'regist_date' => date('Y-m-d H:i:s'),
				'refund_price' => 0,
				'manager_seq' => $manager_seq
		);

		$refund_code = $this->refundmodel->insert_refund($data);

		$logTitle	= "환불신청";
		$logDetail	= "결제취소/환불(기타) 신청하였습니다.";
		$this->ordermodel->set_log($_POST['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail);

		$callback = "
		parent.closeDialog('order_refund_layer');
		parent.document.location.reload();
		";
		openDialogAlert("결제취소/환불(기타) 신청이 완료되었습니다.",400,140,'parent',$callback);

	}

	//실물상품 반품 or 맞교환 -> 환불
	public function order_return(){
		$this->load->model('returnmodel');
		$this->load->model('exportmodel');

		$cfg_order = config_load('order');

		$minfo = $this->session->userdata('manager');
		$manager_seq = $minfo['manager_seq'];

		if(!$_POST['chk_seq']){
			if($_POST['mode']=='exchange'){
				openDialogAlert("맞교환할 상품을 선택해주세요.",400,140,'parent');
			}else{
				openDialogAlert("반품 신청할 상품을 선택해주세요.",400,140,'parent');
			}
			exit;
		}

		$this->validation->set_rules('cellphone[]', '휴대폰','trim|required|numeric|max_length[4]|xss_clean');
		$this->validation->set_rules('phone[]', '연락처','trim|required|numeric|max_length[4]|xss_clean');
		if($_POST['return_method'] == 'shop'){
			$this->validation->set_rules('return_recipient_zipcode[]', '우편번호','trim|required|numeric|max_length[4]|xss_clean');
			$this->validation->set_rules('return_recipient_address', '주소','trim|required|xss_clean');
			$this->validation->set_rules('return_recipient_address_detail', '상세주소','trim|required|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$data_order = $this->ordermodel->get_order($_POST['order_seq']);
		if( !in_array($data_order['step'],array(55,60,65,70,75)) ){
			openDialogAlert($this->arr_step[$data_order['step']]."에서는 반품신청을 하실 수 없습니다.",400,140,'parent');
			exit;
		}

		foreach ($_POST['chk_ea'] as $k => $chk_ea ){
			if($chk_ea == 0){
			openDialogAlert("반품 수량을 0건으로 입력한 경우에는 신청되지 않습니다.",400,140,'parent');
			exit;
			}
		}

		$export_codes = array();
		foreach($_POST['chk_export_code'] as $k => $chk_export_code){
			$cancelquery = "select * from fm_order_item where item_seq=?";
			$cancelquery = $this->db->query($cancelquery,array($_POST['chk_item_seq'][$k]));
			$orditemData = $cancelquery->row_array();

			if($_POST['option_seq'][$k] && !$_POST['suboption_seq'][$k]){
				//쇼셜쿠폰상품의 취소(환불) 가능여부::반품
				if ( $orditemData['goods_kind'] == 'coupon'){
					continue;
				}
			}
			if(!in_array($chk_export_code,$export_codes)) $export_codes[] = $chk_export_code;
		}

		$shipping_seq = null;
		foreach($_POST['chk_shipping_seq'] as $k => $chk_shipping_seq){
			if($shipping_seq === null) $shipping_seq = $chk_shipping_seq;

			if($shipping_seq != $chk_shipping_seq){
				openDialogAlert("배송지가 서로 다른 상품을 함께 반품신청 할 수 없습니다.<br />따로 반품신청을 해주세요.",400,150,'parent');
				exit;
			}
		}

		foreach($export_codes as $export_code){
			$exports = $this->exportmodel->get_export($export_code);
			if(in_array($exports['status'],array(55,60,65,70))){

				$save = !$cfg_order['buy_confirm_use'] ? true : false;

				// 배송완료(수령확인)처리
				$this->exportmodel->exec_complete_delivery($export_code,$save);
			}
		}

		// 환불 등록
		if($_POST['bank']){
			$tmp = code_load('bankCode',$_POST['bank']);
			$bank = $tmp[0]['value'];
		}

		$account = "";
		if($_POST['account'][0]){
			$account = implode('-',$_POST['account']);
		}

		$_POST['refund_method'] = ($_POST['refund_method'])?$_POST['refund_method']:'bank';

		$items = array();
		foreach($_POST['chk_seq'] as $k=>$v){
			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];

			if($items[$k]['option_seq'] && !$items[$k]['suboption_seq']){
				$mode = 'option';

				// 반품으로 인한 원주문 추출 및 교체 :: 2014-11-27 lwh
				$query = $this->db->get_where('fm_order_item_option', 
					array(
					'item_option_seq'=>$items[$k]['option_seq'],
					'item_seq'=>$items[$k]['item_seq'])
				);
				$result = $query -> result_array();
				
				if($result[0]['top_item_option_seq'])
					$items[$k]['option_seq'] = $result[0]['top_item_option_seq'];

				if($result[0]['top_item_option_seq'])
					$items[$k]['item_seq'] = $result[0]['top_item_seq'];

				$query = "select * from fm_order_item_option where item_option_seq=?";
				$query = $this->db->query($query,array($items[$k]['option_seq']));
				$optionData = $query->row_array();

				if($_POST['mode']=='return'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_option_seq',$items[$k]['option_seq']);
					$this->db->update('fm_order_item_option');
				}
			}else if($items[$k]['suboption_seq']){
				$mode = 'suboption';

				// 반품으로 인한 원주문 추출 및 교체 :: 2014-11-27 lwh
				$query = $this->db->get_where('fm_order_item_suboption', 
					array(
					'item_suboption_seq'=>$items[$k]['suboption_seq'])
				);
				$result = $query -> result_array();
				
				if($result[0]['top_item_suboption_seq'])
					$items[$k]['suboption_seq'] = $result[0]['top_item_suboption_seq'];

				$query = "select * from fm_order_item_suboption where item_suboption_seq=?";
				$query = $this->db->query($query,array($items[$k]['suboption_seq']));
				$optionData = $query->row_array();

				if($_POST['mode']=='return'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_suboption_seq',$items[$k]['suboption_seq']);
					$this->db->update('fm_order_item_suboption');
				}
			}
		}

		if($_POST['mode']=='exchange'){
			$refund_code = '0';
			$return_type = 'exchange';
		}else{
			$data = array(
				'order_seq' => $_POST['order_seq'],
				'bank_name' => $bank,
				'bank_depositor' => $_POST['depositor'],
				'bank_account' => $account,
				'refund_reason' => '반품환불',
				'refund_type' => 'return',
				'regist_date' => date('Y-m-d H:i:s'),
				'manager_seq' => $manager_seq,
			);
			$refund_code = $this->refundmodel->insert_refund($data,$items);
			$return_type = 'return';

			$logTitle	= "환불신청";
			$logDetail	= "관리자 반품신청에 의한 환불신청이 접수되었습니다.";
			$logParams	= array('refund_code' => $refund_code);
			$this->ordermodel->set_log($_POST['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);
		}

		if($_POST['phone'][1] && $_POST['phone'][2]) $phone = implode('-',$_POST['phone']);
		if($_POST['cellphone'][1] && $_POST['cellphone'][2]) $cellphone = implode('-',$_POST['cellphone']);
		$zipcode = "";
		if($_POST['return_recipient_zipcode'][1]) $zipcode = implode('-',$_POST['return_recipient_zipcode']);

		// 반품 등록
		$insert_data['status'] 			= 'request';
		$insert_data['order_seq'] 		= $_POST['order_seq'];
		$insert_data['refund_code'] 	= $refund_code;
		$insert_data['return_type'] 	= $return_type;
		$insert_data['return_reason'] 	= $_POST['reason_detail'];
		$insert_data['cellphone'] 		= $cellphone;
		$insert_data['phone'] 			= $phone;
		$insert_data['return_method'] 	= $_POST['return_method'];
		$insert_data['sender_zipcode'] 	= $zipcode;
		$insert_data['sender_address_type'] 	= $_POST['return_recipient_address_type'];
		$insert_data['sender_address'] 				= $_POST['return_recipient_address']?$_POST['return_recipient_address']:'';
		$insert_data['sender_address_street'] 	= $_POST['return_recipient_address_street'];
		$insert_data['sender_address_detail']	= $_POST['return_recipient_address_detail']?$_POST['return_recipient_address_detail']:'';
		$insert_data['regist_date'] 	= date('Y-m-d H:i:s');
		$insert_data['important'] 		= 0;
		$insert_data['manager_seq'] 	= 1;

		$items = array();
		foreach($_POST['chk_seq'] as $k=>$v){
			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];
			$items[$k]['reason_code']	= $_POST['reason'][$k];
			$items[$k]['reason_desc']	= $_POST['reason_desc'][$k];
			$items[$k]['export_code']	= $_POST['chk_export_code'][$k];
		}

		$return_code = $this->returnmodel->insert_return($insert_data,$items);

		if($_POST['mode']=='exchange'){
			$title="맞교환 신청이 완료되었습니다.";
			$logTitle = "맞교환신청";
			$logDetail = "관리자가 맞교환신청을 하였습니다.";
		}else{
			$title="반품 신청이 완료되었습니다.";
			$logTitle = "반품신청";
			$logDetail = "관리자가 반품신청을 하였습니다.";
		}

		$logParams	= array('return_code' => $return_code);
		$this->ordermodel->set_log($_POST['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);

		$callback = "
		parent.closeDialog('order_return_layer');
		parent.document.location.reload();";
		openDialogAlert($title,400,140,'parent',$callback);

	}


	//쿠폰상품 반품 or 맞교환 -> 환불
	public function order_return_coupon(){
		//error_reporting(E_ALL);//0 E_ALL 
		//$this->output->enable_profiler(TRUE);
		$this->load->model('returnmodel');
		$this->load->model('exportmodel');

		$cfg_order = config_load('order');

		$minfo = $this->session->userdata('manager');
		$manager_seq = $minfo['manager_seq'];

		if(!$_POST['chk_seq']){
			if($_POST['mode']=='exchange'){
				openDialogAlert("맞교환할 상품을 선택해주세요.",400,140,'parent');
			}else{
				openDialogAlert("반품 신청할 상품을 선택해주세요.",400,140,'parent');
			}
			exit;
		}

		$this->validation->set_rules('cellphone[]', '휴대폰','trim|required|numeric|max_length[4]|xss_clean');
		//$this->validation->set_rules('phone[]', '연락처','trim|required|numeric|max_length[4]|xss_clean');
		if($_POST['return_method'] == 'shop'){
			$this->validation->set_rules('return_recipient_zipcode[]', '우편번호','trim|required|numeric|max_length[4]|xss_clean');
			$this->validation->set_rules('return_recipient_address', '주소','trim|required|xss_clean');
			$this->validation->set_rules('return_recipient_address_detail', '상세주소','trim|required|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$data_order = $this->ordermodel->get_order($_POST['order_seq']);
		//if( !in_array($data_order['step'],array(55,60,65,70,75)) ){
		if( !in_array($data_order['step'],array('40','45','50','55','60','65','70','75')) ){
			openDialogAlert("[쇼셜쿠폰] ".$this->arr_step[$data_order['step']]."에서는 반품신청을 하실 수 없습니다.",400,140,'parent');
			exit;
		}

		foreach ($_POST['chk_ea'] as $k => $chk_ea ){
			if($chk_ea == 0){
			openDialogAlert("환불금액이 0원인경우에는 신청되지 않습니다.",400,140,'parent');
			exit;
			}
		}

		$export_codes = array();
		foreach($_POST['chk_export_code'] as $k => $chk_export_code){
			if(!in_array($chk_export_code,$export_codes)) $export_codes[] = $chk_export_code;
		}

		$shipping_seq = null;
		foreach($_POST['chk_shipping_seq'] as $k => $chk_shipping_seq){
			if($shipping_seq === null) $shipping_seq = $chk_shipping_seq;

			if($shipping_seq != $chk_shipping_seq){
				openDialogAlert("배송지가 서로 다른 상품을 함께 반품신청 할 수 없습니다.<br />따로 반품신청을 해주세요.",400,150,'parent');
				exit;
			}
		}

		if($_POST['manual_refund_yn']=='y' && $data_order['payment']=='card' && $data_order['settleprice']==$_POST['cancel_total_price'])
		{ 
			$pgCompany = $this->config_system['pgCompany'];

			/* PG 전체취소 start */
			$cancelFunction = "{$pgCompany}_cancel";
			$cancelResult = $this->refundmodel->$cancelFunction($data_order,$data_refund);

			if(!$cancelResult['success']){
				openDialogAlert("{$pgCompany} 결제 취소 실패<br /><font color=red>{$cancelResult['result_code']} : {$cancelResult['result_msg']}</font>",400,160,'parent','');
				exit;
			}
			/* PG 전체취소 end */
		} 

		// 환불 등록
		if($_POST['bank']){
			$tmp = code_load('bankCode',$_POST['bank']);
			$bank = $tmp[0]['value'];
		}

		$account = "";
		if($_POST['account'][0]){
			$account = implode('-',$_POST['account']);
		}

		$_POST['refund_method'] = ($_POST['refund_method'])?$_POST['refund_method']:'bank';

		$realitems 		= $this->ordermodel->get_item($_POST['order_seq']);
		//주문상품의 실제 1건당 금액계산 @2014-11-27
		foreach($realitems as $key=>$item){
			if ( $item['goods_kind'] != 'coupon' ) continue;
			$reOption	= array();
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$rowspan	= 0;
			if($options) foreach($options as $k => $data){
				// 매입
				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				// 정산
				$data['out_commission_price'] = $data['commission_price']*$data['ea'];				
				
				// 상품금액
				$data['out_price'] = $data['price']*$data['ea'];

				// 할인
				$data['out_member_sale'] = $data['member_sale']*$data['ea'];
				$data['out_coupon_sale'] = ($data['download_seq'])?$data['coupon_sale']:0;
				$data['out_fblike_sale'] = $data['fblike_sale'];
				$data['out_mobile_sale'] = $data['mobile_sale'];
				$data['out_promotion_code_sale'] = $data['promotion_code_sale'];
				$data['out_referer_sale'] = $data['referer_sale'];
				
				// 할인 합계
				$data['out_tot_sale'] = $data['out_member_sale'];
				$data['out_tot_sale'] += $data['out_coupon_sale'];
				$data['out_tot_sale'] += $data['out_fblike_sale'];
				$data['out_tot_sale'] += $data['out_mobile_sale'];
				$data['out_tot_sale'] += $data['out_promotion_code_sale'];
				$data['out_tot_sale'] += $data['out_referer_sale'];
				
				// 할인가격
				$data['out_sale_price'] = $data['out_price'] - $data['out_tot_sale'];
				$data['sale_price'] = $data['out_sale_price'] / $data['ea'];
				$order_one_option_sale_price[$data['item_option_seq']] = $data['sale_price'];
				
				// 예상적립
				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];

				###
				unset($data['inputs']);
				$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'],$data['item_option_seq']);

				$options[$k] = $data;

				$tot['ea']					+= $data['ea'];
				$tot['ready_ea']			+= $data['ready_ea'];
				$tot['step_complete']		+= $data['step_complete'];
				$tot['step25']				+= $data['step25'];
				$tot['step85']				+= $data['step85'];				
				$tot['step45']				+= $data['step45'];
				$tot['step55']				+= $data['step55'];
				$tot['step65']				+= $data['step65'];
				$tot['step75']				+= $data['step75'];
				$tot['supply_price']		+= $data['out_supply_price'];
				$tot['commission_price']	+= $data['out_commission_price'];
				$tot['consumer_price']		+= $data['out_consumer_price'];
				$tot['price']				+= $data['out_price'];

				$tot['member_sale']			+= $data['out_member_sale'];
				$tot['coupon_sale']			+= $data['out_coupon_sale'];
				$tot['fblike_sale']			+= $data['out_fblike_sale'];
				$tot['mobile_sale']			+= $data['out_mobile_sale'];
				$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];
				$tot['referer_sale']		+= $data['out_referer_sale'];

				$tot['coupon_provider']		+= $data['coupon_provider'];
				$tot['promotion_provider']	+= $data['promotion_provider'];
				$tot['referer_provider']	+= $data['referer_provider'];

				$tot['reserve']				+= $data['out_reserve'];
				$tot['point']				+= $data['out_point'];
				$tot['real_stock']			+= $real_stock;
				$tot['stock']				+= $stock;

				$return_item = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['item_option_seq']);
				$able_return_ea += (int) $data['step75'] - (int) $return_item['ea'];

				$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $data['item_option_seq']);
				if($suboptions) foreach($suboptions as $k => $subdata){
					###
					$subdata['out_supply_price']		= $subdata['supply_price']*$subdata['ea'];
					$subdata['out_commission_price']	= $subdata['commission_price']*$subdata['ea'];
					$subdata['out_consumer_price']		= $subdata['consumer_price']*$subdata['ea'];
					$subdata['out_price']				= $subdata['price']*$subdata['ea'];					
					
					// 할인
					$subdata['out_member_sale'] = $subdata['member_sale']*$data['ea'];
					$subdata['out_coupon_sale'] = ($subdata['download_seq'])?$subdata['coupon_sale']:0;
					$subdata['out_fblike_sale'] = $subdata['fblike_sale'];
					$subdata['out_mobile_sale'] = $subdata['mobile_sale'];
					$subdata['out_promotion_code_sale'] = $subdata['promotion_code_sale'];
					$subdata['out_referer_sale'] = $subdata['referer_sale'];
					
					// 할인 합계
					$subdata['out_tot_sale'] = $subdata['out_member_sale'];
					$subdata['out_tot_sale'] += $subdata['out_coupon_sale'];
					$subdata['out_tot_sale'] += $subdata['out_fblike_sale'];
					$subdata['out_tot_sale'] += $subdata['out_mobile_sale'];
					$subdata['out_tot_sale'] += $subdata['out_promotion_code_sale'];
					$subdata['out_tot_sale'] += $subdata['out_referer_sale'];
					
					// 할인가격
					$subdata['out_sale_price'] = $subdata['out_price'] - $subdata['out_tot_sale'];
					$subdata['sale_price'] = $subdata['out_sale_price'] / $subdata['ea'];
					$order_one_option_sale_price[$data['item_option_seq']] += $subdata['sale_price'];

					$subdata['out_reserve']				= $subdata['reserve']*$subdata['ea'];
					$subdata['out_point']				= $subdata['point']*$subdata['ea'];
				}
			}
		}

		$items = array();
		$r_reservation_goods_seq = array();
		foreach($_POST['chk_seq'] as $k=>$v){
			$cancelquery = "select * from fm_order_item where item_seq=?";
			$cancelquery = $this->db->query($cancelquery,array($_POST['chk_item_seq'][$k]));
			$orditemData = $cancelquery->row_array();

			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];

			//쿠폰상품의 1개의 실제 결제금액 @2014-11-27
			$coupon_real_total_price = $order_one_option_sale_price[$items[$k]['option_seq']];

			if($items[$k]['option_seq'] && !$items[$k]['suboption_seq']){
				$mode = 'option';

				//쇼셜쿠폰상품의 취소(환불) 가능여부::반품
				if ( $orditemData['goods_kind'] == 'coupon') {

				$query = "select * from fm_order_item_option where item_option_seq=?";
				$query = $this->db->query($query,array($items[$k]['option_seq']));
				$optionData = $query->row_array();

					$export_itemquery = "select * from fm_goods_export_item where export_code=? limit 1";
					$export_itemquery = $this->db->query($export_itemquery,array($_POST['chk_export_code'][$k]));
					$export_item_Data = $export_itemquery->row_array();
					$export_item_Data['couponinfo'] = get_goods_coupon_view($_POST['chk_export_code'][$k]);

					$coupon_value					= 0;
					$socialcp_return_notuse		= 0;
					$coupon_refund_emoney = $coupon_remain_price = $coupon_deduction_price = 0;
					$coupon_remain_real_percent = $coupon_remain_real_price = 0;
					$coupon_remain_price = $coupon_deduction_price = 0; 
					$socialcoupon++;

					if( date("Ymd")>substr(str_replace("-","",$optionData['social_end_date']),0,8)) {//유효기간 종료 후 잔여값어치합계

						if( $export_item_Data['coupon_value'] == $export_item_Data['coupon_remain_value'] ) {//값어치 전체미사용
							$socialcp_status = '8';
						}else{//값어치 일부사용
							$socialcp_status = '9';
						}
						/**
						//관리자 : 미사용쿠폰 환불대상 불가 허용
						if( $orditemData['socialcp_use_return'] == 1 ) {//미사용쿠폰 환불대상
						}else{//불가
						} 
						**/ 
							if( order_socialcp_cancel_return($orditemData['socialcp_use_return'], $export_item_Data['coupon_value'], $export_item_Data['coupon_remain_value'], $optionData['social_start_date'], $optionData['social_end_date'] , $orditemData['socialcp_use_emoney_day'] ) === true ) {//미사용쿠폰여부 잔여값어치합계
								if ( $orditemData['socialcp_input_type'] == 'price' ) {//금액
								$coupon_remain_price_tmp			= (int) $export_item_Data['coupon_remain_value'];
								$coupon_deduction_price_tmp	= (int) $export_item_Data['coupon_value'];
								}else{//횟수
								$coupon_remain_price_tmp			= (int) (100 * ($optionData['coupon_input_one'] * $export_item_Data['coupon_remain_value']) / 100);
								$coupon_deduction_price_tmp	= (int) ($optionData['coupon_input_one'] * $export_item_Data['coupon_value']);
								}
							$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율 

							//실제결제금액
							$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);
							$coupon_remain_price			= (int) ($orditemData['socialcp_use_emoney_percent'] * ($coupon_remain_real_price) / 100);
							$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price; 
							//$cancel_total_price  += $coupon_remain_price;//취소총금액

							$items[$k]['coupon_refund_type']		= 'price';
							$coupon_valid_over++;//유효기가긴지난경우
								}
					}else{//유효기간 이전

						if( $export_item_Data['coupon_value'] == $export_item_Data['coupon_remain_value'] ) {//값어치 전체미사용
							$socialcp_status = '6';
						}else{//값어치 일부사용
							$socialcp_status = '7';
							}

						$items[$k]['coupon_refund_type']		= 'price';

						if( $export_item_Data['coupon_remain_value'] >0 ) {//잔여값어치가 남아있을때에만
							/**
							//관리자 : 부분 사용한 쿠폰은 취소(환불) 허용
							if(  $export_item_Data['coupon_value'] != $export_item_Data['coupon_remain_value']  && $orditemData['socialcp_cancel_use_refund'] == '1' ) {
								//부분 사용한 쿠폰은 취소(환불) 불가 @2014-10-07 
							}else{
							}
							**/
							list($export_item_Data['socialcp_refund_use'], $export_item_Data['socialcp_refund_cancel_percent']) = order_socialcp_cancel_refund(
								$_POST['order_seq'],
								$_POST['chk_item_seq'][$k],
								$data_order['deposit_date'],
								$optionData['social_start_date'],
								$optionData['social_end_date'],
								$orditemData['socialcp_cancel_payoption'],
								$orditemData['socialcp_cancel_payoption_percent']
							);

							if( $export_item_Data['coupon_value'] == $export_item_Data['coupon_remain_value'] ){//미사용 
								//실제결제금액
								$coupon_remain_price			= (int) ($export_item_Data['socialcp_refund_cancel_percent'] * $coupon_real_total_price / 100);
								$coupon_deduction_price	= (int) $coupon_real_total_price - $coupon_remain_price;
								$coupon_remain_real_percent = "100";
								$coupon_remain_real_price = $coupon_real_total_price; 
								$cancel_total_price  += $coupon_remain_price;//취소총금액
							}else{//사용
								if ( $orditemData['socialcp_input_type'] == 'price' ) {//금액
									$coupon_remain_price_tmp			= (int) $export_item_Data['coupon_remain_value'];
									$coupon_deduction_price_tmp	= (int) $export_item_Data['coupon_value'];
								}else{//횟수
									$coupon_remain_price_tmp			= (int) (100 * ($optionData['coupon_input_one'] * $export_item_Data['coupon_remain_value']) / 100);
									$coupon_deduction_price_tmp	= (int) ($optionData['coupon_input_one'] * $export_item_Data['coupon_value']);
								}
								$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율

								//실제결제금액
								$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);

								$coupon_remain_price			= (int) ($export_item_Data['socialcp_refund_cancel_percent'] * ($coupon_remain_real_price) / 100);
								$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price; 
								//$cancel_total_price  += $coupon_remain_price;//취소총금액
							}
						}
					}
					
					//취소(환불) 로그쌓기
					$cancel_memo = socialcp_cancel_memo($export_item_Data, $coupon_remain_real_percent, $coupon_real_total_price, $coupon_remain_real_price, $coupon_remain_price, $coupon_deduction_price);  
					$items[$k]['coupon_remain_price']			= $coupon_remain_price;//쿠폰 결제금액의 실제금액
					$items[$k]['coupon_deduction_price']		= $coupon_deduction_price;//쿠폰 결제금액의 공제금액
					$items[$k]['cancel_memo']						= $cancel_memo;//취소(환불) 상세내역
				}

				if($_POST['mode']=='return'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_option_seq',$items[$k]['option_seq']);
					$this->db->update('fm_order_item_option');
				}
				
				/* 신용카드 자동취소 > 재고차감 start */
				if( $data_order['payment']=='card' && $data_order['settleprice']==$_POST['cancel_total_price'])
				{ 
					//상품체크>청약철회/
					unset($cancel_goods);
					$cancel_goods = $this->goodsmodel->get_goods($orditemData['goods_seq']);
					//$orditemData['cancel_type']				= $cancel_goods['cancel_type'];
					//$orditemData['coupon_serial_type']		= $cancel_goods['coupon_serial_type']; 
					if( $cancel_goods['coupon_serial_type'] == 'a' ) {
						// 출고량 업데이트를 위한 변수정의
						if(!in_array($optionData['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $optionData['goods_seq'];
						}
					}
				}
			}else if($items[$k]['suboption_seq']){
				$mode = 'suboption';

				$query = "select * from fm_order_item_suboption where item_suboption_seq=?";
				$query = $this->db->query($query,array($items[$k]['suboption_seq']));
				$optionData = $query->row_array();

				if($_POST['mode']=='return'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_suboption_seq',$items[$k]['suboption_seq']);
					$this->db->update('fm_order_item_suboption');
				}

				/* 신용카드 자동취소 > 재고차감 start */
				if( $data_order['payment']=='card' && $data_order['settleprice']==$_POST['cancel_total_price'])
				{ 
					
					//상품체크
					unset($cancel_goods);
					$cancel_goods = $this->goodsmodel->get_goods($optionData['goods_seq']);
					//$optionData['cancel_type']				= $cancel_goods['cancel_type'];
					//$optionData['coupon_serial_type']		= $cancel_goods['coupon_serial_type'];
					if( $cancel_goods['coupon_serial_type'] == 'a' ) {
						// 출고량 업데이트를 위한 변수정의
						if(!in_array($optionData['goods_seq'],$r_reservation_goods_seq)){
							$r_reservation_goods_seq[] = $optionData['goods_seq'];
						}
					}
				}
			}
		}

		//$_POST['refund_method'] = ($coupon_valid_over)?'emoney':$_POST['refund_method'];//2014-10-13 사용안함

		if($_POST['mode']=='exchange'){
			$refund_code = '0';
			$return_type = 'exchange';
		}else{
			// 반품시 최상위 주문번호 저장 :: 2014-11-27 lwh
			if($data_order['top_orign_order_seq'])
				$orgin_order_seq = $data_order['top_orign_order_seq'];
			else
				$orgin_order_seq = $_POST['order_seq'];

			$data = array(
				'order_seq' => $orgin_order_seq,
				'bank_name' => $bank,
				'bank_depositor' => $_POST['depositor'],
				'coupon_refund_emoney' => $coupon_refund_emoney,
				'coupon_refund_price' => $coupon_remain_price,
				'bank_account' => $account,
				'refund_reason' => '반품환불',
				'refund_type' => 'return',
				'regist_date' => date('Y-m-d H:i:s'),
				'manager_seq' => $manager_seq,
				'refund_method' => $_POST['refund_method']
			);
			$refund_code = $this->refundmodel->insert_refund($data,$items);
			
			/* 신용카드 자동취소 > 재고차감 start */
			if( $data_order['payment']=='card' && $data_order['settleprice']==$_POST['cancel_total_price'])
			{ 
				// 출고예약량 업데이트
				foreach($r_reservation_goods_seq as $goods_seq){
					$this->goodsmodel->modify_reservation_real($goods_seq);
				}
			}

			$return_type = 'return';

			$logTitle	= "환불신청";
			$logDetail	= "관리자 반품신청에 의한 환불신청이 접수되었습니다.";
			$logParams	= array('refund_code' => $refund_code);
			$this->ordermodel->set_log($orgin_order_seq,'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);
		}

		/**
		* 쿠폰상품 반품처리 start
		**/
		if($_POST['phone'][1] && $_POST['phone'][2]) $phone = implode('-',$_POST['phone']);
		if($_POST['cellphone'][1] && $_POST['cellphone'][2]) $cellphone = implode('-',$_POST['cellphone']);
		$zipcode = "";
		if($_POST['return_recipient_zipcode'][1]) $zipcode = implode('-',$_POST['return_recipient_zipcode']);

		//쿠폰상품 반품등록
			$insert_data['status'] 				= 'complete';//쿠폰상품 반품완료
		$insert_data['order_seq'] 		= $_POST['order_seq'];
		$insert_data['refund_code'] 	= $refund_code;
		$insert_data['return_type'] 	= $return_type;
		$insert_data['return_reason'] 	= $_POST['reason_detail'];
		$insert_data['cellphone'] 		= $cellphone;
		$insert_data['phone'] 			= (!empty($phone)) ? $phone : '';
		$insert_data['return_method'] 	= $_POST['return_method'];
		$insert_data['sender_zipcode'] 	= $zipcode;
		$insert_data['sender_address_type'] 	= $_POST['return_recipient_address_type'];
		$insert_data['sender_address'] 				= $_POST['return_recipient_address']?$_POST['return_recipient_address']:'';
		$insert_data['sender_address_street'] 	= $_POST['return_recipient_address_street'];
		$insert_data['sender_address_detail']	= $_POST['return_recipient_address_detail']?$_POST['return_recipient_address_detail']:'';
		$insert_data['regist_date'] 	= date('Y-m-d H:i:s');
		$insert_data['return_date'] = date('Y-m-d H:i:s');//쿠폰상품 반품완료
		$insert_data['important'] 		= 0;
		$insert_data['manager_seq'] 	= 1;

		$items = array();
		foreach($_POST['chk_seq'] as $k=>$v){
			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];
			$items[$k]['reason_code']	= $_POST['reason'][$k];
			$items[$k]['reason_desc']	= $_POST['reason_desc'][$k];
			$items[$k]['export_code']	= $_POST['chk_export_code'][$k];
		}

		$return_code = $this->returnmodel->insert_return($insert_data,$items);
		/**
		* 쿠폰상품 반품처리 end
		**/

		/**
		* 쿠폰상품 배송완료 start
		**/
			$this->load->model('socialcpconfirmmodel');
			foreach($export_codes as $export_code){
				$data_export = $this->exportmodel->get_export($export_code);
				if(in_array($data_export['status'],array('40','45','50','55','60','65','70','75'))){
					unset($data_socialcp_confirm);
					$data_socialcp_confirm['order_seq']		= $data_export['order_seq'];
					$data_socialcp_confirm['export_seq']		= $data_export['export_seq'];
					$data_socialcp_confirm['manager_seq']	= $this->managerInfo['manager_seq'];
					$data_socialcp_confirm['doer']				=  $this->managerInfo['mname'];
					$this->socialcpconfirmmodel -> socialcp_confirm('admin',$socialcp_status,$export_code);//socialcp_status = 환불시 상태 6,7,8,9
					$this->socialcpconfirmmodel -> log_socialcp_confirm($data_socialcp_confirm);

					//티켓상품의 배송완료처리
					$this->exportmodel->socialcp_exec_complete_delivery($export_code, true, $coupon_remain_real_percent, $socialcp_confirm, "cancel");
				}
			}
		/**
		* 쿠폰상품 배송완료 end
		**/
		if($_POST['manual_refund_yn']=='y' && $data_order['payment']=='card' && $data_order['settleprice']==$cancel_total_price)
		{
			/* 신용카드 자동취소 @2014-10-13 */
			//debug_var("data_order['payment']:".$data_order['payment']."=data_order['settleprice']:".$data_order['settleprice']."=cancel_total_price:".$cancel_total_price);
			/**
			* 쿠폰상품 신용카드 자동취소 start
			**/
			$this->load->model('emoneymodel');
			$this->load->model('membermodel');
			$this->load->model('couponmodel');
			$this->load->model('promotionmodel');
			$this->load->helper('text');


			if($data_order['member_seq']){
				/* 적립금 지급 */
				if($data_order['emoney_use']=='use' && $data_order['emoney'] > 0 )
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'cancel',
						'emoney'	=> $data_order['emoney'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[복원]주문환불({$refund_code})에 의한 적립금 환원",
					);
					$this->membermodel->emoney_insert($params, $data_order['member_seq']);
					$this->ordermodel->set_emoney_use($data_order['order_seq'],'return');
				}

				/* 이머니 지급 */
				if($data_order['cash_use']=='use' && $data_order['cash'] > 0 )
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'cancel',
						'cash'	=> $data_order['cash'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[복원]주문환불({$refund_code})에 의한 이머니 환원",
					);
					$this->membermodel->cash_insert($params, $data_order['member_seq']);
					$this->ordermodel->set_cash_use($data_order['order_seq'],'return');
				}

				/* 적립금 회수 */
				if($_POST['return_reserve'] && $data_refund['refund_type']=='return'){
					$params = array(
						'gb'		=> 'minus',
						'type'		=> 'refund',
						'emoney'	=> $_POST['return_reserve'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[차감] 주문환불({$data_order['order_seq']})에 의하여 배송완료시 지급된 적립금 차감",
					);
					$this->membermodel->emoney_insert($params, $data_order['member_seq']);
				}

				/* 포인트 회수 */
				if($_POST['return_point'] && $data_refund['refund_type']=='return'){
					$params = array(
						'gb'		=> 'minus',
						'type'		=> 'refund',
						'point'	=> $_POST['return_point'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[차감] 주문환불({$data_order['order_seq']})에 의하여 배송완료시 지급된 포인트 차감",
					);
					$this->membermodel->point_insert($params, $data_order['member_seq']);
				}
			}

			$saveData = array(
				'adjust_use_coupon'		=> $data_order['coupon_sale'],
				'adjust_use_promotion'	=> $data_order['shipping_promotion_code_sale'],
				'adjust_use_emoney'		=> $data_order['emoney'],
				'adjust_use_cash'			=> $data_order['cash'],
				'adjust_use_enuri'			=> $data_order['enuri'],
				'refund_method'				=> 'card',
				'refund_price'					=> $data_order['settleprice'],
				'status'							=> 'complete',
				'cancel_type'					=> 'full',
				'refund_date'			=> date('Y-m-d H:i:s')
			);//status 환불완료처리
			$this->db->where('refund_code', $refund_code);
			$this->db->update("fm_order_refund",$saveData);

			/* 저장된 정보 로드 */
			$data_refund		= $this->refundmodel->get_refund($refund_code);
			$data_refund_item 	= $this->refundmodel->get_refund_item($refund_code); 
			$data_member		= $this->membermodel->get_member_data($data_order['member_seq']);

			// 추가옵션 관련 아이템 재배열
			$items_array	= array();
			if($data_refund_item)foreach($data_refund_item as $item){
				if( $item['goods_kind'] == 'coupon' ) {
					$refund_goods_coupon_ea++;
				}
				if($item['title1'])		$item['options_str']  = $item['title1'] .":".$item['option1'];
				if($item['title2'])		$item['options_str'] .= " / ".$item['title2'] .":".$item['option2'];
				if($item['title3'])		$item['options_str'] .= " / ".$item['title3'] .":".$item['option3'];
				if($item['title4'])		$item['options_str'] .= " / ".$item['title4'] .":".$item['option4'];

				if	($item['opt_type'] == 'sub'){
					$item['price']								= $item['price'] * $item['ea'];
					$item['sub_options']							= $item['options_str'];
					if	($first_option_seq)
						$items_array[$first_option_seq]['sub'][]		= $item;
					else
						$items_array[$item['option_seq']]['sub'][]		= $item;
				}else{
					$items_array[$item['option_seq']]['price']			+= $item['price'] * $row['ea'];
					$items_array[$item['option_seq']]['ea']			+= $item['ea'];
					$items_array[$item['option_seq']]['goods_name']	= $item['goods_name'];
					$items_array[$item['option_seq']]['options']		= $item['options_str'];
					$items_array[$item['option_seq']]['inputs']		= $this->ordermodel->get_input_for_option($item['item_seq'], $item['option_seq']);
				}
				if	(!$first_option_seq)	$first_option_seq	= $item['option_seq'];
			}

			$order_itemArr = array();
			$order_itemArr['order_seq'] = $data_order['order_seq'];
			$order_itemArr['mpayment'] = $data_order['mpayment'];
			$order_itemArr['deposit_date'] = $data_order['deposit_date'];
			$order_itemArr['pg_transaction_number'] = $data_order['pg_transaction_number'];

			/* 환불처리완료 안내메일 발송 */
			$params = array_merge($saveData,$data_refund);
			$params['refund_reason']		= htmlspecialchars($data_refund['refund_reason']);
			$params['refund_date']			= $saveData['refund_date'];
			$params['mstatus'] 				= $this->refundmodel->arr_refund_status[$_POST['status']];
			$params['refund_price']			= number_format($data_refund['refund_price']);
			$params['refund_emoney']		= number_format($data_refund['refund_emoney']);
			$params['mrefund_method']		= $this->arr_payment[$data_refund['refund_method']];
			$params['order']				= $order_itemArr;
			if($data_refund['refund_method']=='bank'){
				$params['mrefund_method']		.= " 환불";
			}elseif($data_refund['cancel_type']=='full'){
				$params['mrefund_method'] 		.= " 결제취소";
			}elseif($data_refund['cancel_type']=='partial'){
				$params['mrefund_method'] 		.= " 부분취소";
			}
			$params['items'] 			= $items_array;
			if( $data_order['order_email'] ) {
				$couponsms		 = ( $refund_goods_coupon_ea ) ? "coupon_":"";
				$smsemailtype = ($data_refund['refund_type']=='return') ? 'refund' : 'cancel';
				sendMail($data_order['order_email'], $couponsms.$smsemailtype, $data_member['userid'], $params);
			}

			// 주문이 환불완료 일경우 주문한 회원의 구매횟수 및 구매금액 업데이트
			if($data_order['member_seq']){
				//$refund_price = $data_refund['refund_price'] + $data_refund['refund_emoney'];
				$this->membermodel->member_order($data_order['member_seq']);
				//주문건/주문금액 필드추가 및 실시간업데이트 @2013-06-19
				$this->membermodel->member_order_batch($data_order['member_seq']);
			}

			//이벤트 판매건/주문건/주문금액 @2013-11-15
			if($data_refund['refund_type'] == 'return' && $data_refund_item){
				foreach($data_refund_item as $item) {
					if( $item['event_seq'] ) {
						$this->eventmodel->event_order($item['event_seq']);
						$this->eventmodel->event_order_batch($item['event_seq']);
					}
				}
			}

			$this->db->where('refund_code', $_POST['refund_code']);
			$this->db->update("fm_order_refund",$saveData);

			/* 로그저장 */ 
			$logTitle = "환불완료";
			$logDetail = "관리자가 환불완료처리를 하였습니다.";
			$logParams	= array('refund_code' => $_POST['refund_code']);
			$this->ordermodel->set_log($data_order['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);
			$data_return = $this->returnmodel->get_return_refund_code($_POST['refund_code']);
			$data_return_item 	= $this->returnmodel->get_return_item($data_return['return_code']);
			if($data_refund['refund_type']=='return') {
				coupon_send_sms_refund($data_return_item[0]['export_code'],$data_order);
			}else{
				coupon_send_sms_cancel($data_return_item[0]['export_code'],$data_order);
			} 

			$callback = "
			parent.closeDialog('order_refund_layer');
			parent.document.location.reload();
			";

			$title="신용카드 결제취소가 완료되었습니다.";
			openDialogAlert($title,400,140,'parent',$callback);

			/**
			* 쿠폰상품 신용카드 자동취소 end
			**/
		}else{ 
		if($_POST['mode']=='exchange'){
			$title="맞교환 신청이 완료되었습니다.";
			$logTitle = "맞교환신청";
			$logDetail = "관리자가 맞교환신청을 하였습니다.";
		}else{
			$title="반품이 완료되었습니다.";
			$logTitle = "반품완료";
			$logDetail = "관리자가 반품완료을 하였습니다.";
		}

		$logParams	= array('return_code' => $return_code);
		$this->ordermodel->set_log($_POST['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);
		}

		$callback = "
		parent.closeDialog('order_return_layer');
		parent.document.location.reload();";
		openDialogAlert($title,400,140,'parent',$callback);

	}

	###
	public function batch_temps_order(){
		$now = date("Y-m-d H:i:s");
		foreach($_POST['seq'] as $order_seq){
			$this->db->where('order_seq', $order_seq);
			$this->db->update('fm_order', array('hidden'=>'Y','hidden_date'=>$now));
		}
		echo json_encode($result);
	}
	public function batch_temps_orders(){
		$now = date("Y-m-d H:i:s");
		foreach($_POST['seq'] as $order_seq){
			$this->db->where('order_seq', $order_seq);
			$this->db->update('fm_order', array('hidden'=>'T','hidden_date'=>$now));
		}
		echo json_encode($result);
	}


	public function bank_search_set(){
		config_save("bank_set" ,array('sprice'=>$_POST['sprice']));
		config_save("bank_set" ,array('eprice'=>$_POST['eprice']));
		$callback = "parent.document.location.reload();";
		openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
	}


	public function auto_deposit_update(){
		###
		$this->load->model('usedmodel');
		$this->usedmodel->auto_desposit_check();
		return "[".json_encode($result)."]";
	}

	public function auto_deposit_update_plus(){
		###
		$setType		= $_GET['setType'];
		$this->load->model('usedmodel');
		$result['cnt']	= $this->usedmodel->auto_desposit_check_plus($setType);
		return "[".json_encode($result)."]";
	}

	public function auto_deposit_update_term(){
		###
		$this->load->model('usedmodel');
		$this->usedmodel->auto_desposit_check_term();
		return "[".json_encode($result)."]";
	}

	public function _exec_reverse($order_seq){
		$data_order = $this->ordermodel->get_order($order_seq);
		$source_step = (string) $data_order['step'];

		// 주문접수 상태일 경우
		if( $data_order['step'] <= 15 ){
			return false;
		}

		// 결제확인,상품준비중 일경우
		if( $data_order['step'] != '95' && $data_order['step'] > 15 ){
			$target_step = $data_order['step'] - 10;
			$mode = "normal";
		}else{
			$mode = "cancel";
			$target_step = 15;
		}

		$target_step = (string) $target_step;
		if( $data_order['step'] == 25 && $data_order['payment'] != 'bank' ){
			return false;
		}else{
			$return = $this->ordermodel->set_reverse_step($order_seq,$target_step,$arr,$mode);

			if($return){
				// 로그
				$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'되돌리기 ('.$this->arr_step[$data_order['step']].' => '.$this->arr_step[$target_step].')','-');
			}

			return $return;
		}
	}

	public function order_reverse(){
		$order_seq = $_GET['seq'];
		$result = $this-> _exec_reverse($order_seq);
		if(!$result){
			openDialogAlert("잔여 적립금이 없습니다.주문접수로 되돌릴 수 없습니다.",400,140,'parent','');
			exit;
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("주문상태가 변경되었습니다.",400,140,'parent',$callback);

	}

	public function batch_reverse()
	{
		foreach($_POST['seq'] as $order_seq){
			$result = $this-> _exec_reverse($order_seq);
		}
		echo("주문상태가 변경되었습니다.");
	}

	// 상품준비
	public function _exec_goods_ready($order_seq)
	{
		$this->ordermodel->set_step35_ea($order_seq);
		$this->ordermodel->set_step($order_seq,35);
		$log_str = "관리자가 상품준비를 하였습니다.";
	    $this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'상품준비',$log_str);
	}

	// 상품준비
	public function goods_ready(){

		$order_seq			= trim($_POST['order_seq']);
		$options			= $_POST['optionSeq'];
		$suboptions			= $_POST['suboptionSeq'];
		if	($order_seq){
			if	($options)foreach($options as $o => $optSeq){
				$this->ordermodel->set_step35_ea($order_seq, $optSeq, 'option');
				$this->ordermodel->set_option_step($optSeq, 'option');
			}
			if	($suboptions)foreach($suboptions as $o => $subSeq){
				$this->ordermodel->set_step35_ea($order_seq, $subSeq, 'suboption');
				$this->ordermodel->set_option_step($subSeq, 'suboption');
			}
		}
		$this->ordermodel->set_order_step($order_seq);
		$log_str = "관리자가 상품준비를 하였습니다.";
	    $this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'상품준비',$log_str);

		$callback = "parent.location.replace(parent.location.href);";
		openDialogAlert("해당 상품의 상태가 상품준비로 변경 되었습니다.",400,140,'parent',$callback);
	}

	public function batch_goods_ready(){
		$addWhere	= array("step = '25'");
		foreach($_POST['seq'] as $order_seq){
			$options	= $this->ordermodel->get_option_for_order($order_seq, $addWhere);
			if	($options)foreach($options as $o => $opt){
				$this->ordermodel->set_step35_ea($order_seq, $opt['item_option_seq'], 'option');
				$this->ordermodel->set_option_step($opt['item_option_seq'], 'option');
			}
			$suboptions	= $this->ordermodel->get_suboption_for_order($order_seq, $addWhere);
			if	($suboptions)foreach($suboptions as $s => $sub){
				$this->ordermodel->set_step35_ea($order_seq, $sub['item_suboption_seq'], 'suboption');
				$this->ordermodel->set_option_step($sub['item_suboption_seq'], 'suboption');
			}

			$this->ordermodel->set_order_step($order_seq);
		}

		echo "선택하신 주문들 중 결제확인 상태의 상품이 상품준비로 일괄 변경되었습니다.";
	}

	public function filedown(){
		$file = $_GET['file'];
		$path = ROOTPATH."data/order/".$file;
		get_file_down($path, $file);
	}


	//회원검색 전체인경우
	public function download_member_search_all()
	{

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('coupon_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->load->model('membermodel');
		### SEARCH
		$sc = $_POST;
		$sc['search_text']		= ($sc['search_text'] == '이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소') ? '':$sc['search_text'];
		$sc['orderby']		= 'A.member_seq';
		### MEMBER
		$i=0;
		$data = $this->membermodel->popup_member_list($sc);
		foreach($data['result'] as $datarow){
			//$download_coupons = $this->couponmodel->get_admin_download($datarow['member_seq'], $_POST['no']);
			if(!$download_coupons) {
				$searchallmember[$i]['user_name'] = $datarow['user_name'];
				$searchallmember[$i]['userid']			 = $datarow['userid'];
				$searchallmember[$i]['member_seq']			 = $datarow['member_seq'];
				$i++;
			}
		}

		$result = array('searchallmember'=>$searchallmember,'totalcnt'=>$i);
		echo json_encode($result);
		exit;
	}

	public function print_setting(){
		config_save("order" ,array('orderPrintOrderBarcode'=>$_POST['orderPrintOrderBarcode']));
		config_save("order" ,array('orderPrintGoodsCode'=>$_POST['orderPrintGoodsCode']));
		config_save("order" ,array('orderPrintGoodsBarcode'=>$_POST['orderPrintGoodsBarcode']));
		$callback = "parent.closeDialog('print_setting_dialog')";
		openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
	}

	// 오픈마켓 주문수집
	public function openmarket_order_receive(){

		$this->load->model('openmarketmodel');
		$arr_order_seq = $this->openmarketmodel->exec_order_receive();
		
		openDialogAlert(number_format(count($arr_order_seq))."건의 주문건이 수집되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	// 출고처리
	public function order_export(){

		$this->coupon_reciver_sms = array();
		$this->coupon_order_sms = array();

		$this->load->model('exportmodel');
		$this->load->model('ordermodel');

		$auth = $this->authmodel->manager_limit_act('order_goods_export');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}

		$cfg_order		= config_load('order');
		$shippingSeqArr	= $_POST['shipping_seq'];
		$orderCnt		= count($shippingSeqArr);
		$params			= $_POST['params'];
		$export_date	= $_POST['export_date'];
		$delivery_step	= $_POST['delivery_step'];
		

		if	($shippingSeqArr && is_array($shippingSeqArr) && count($shippingSeqArr) > 0){
			foreach($shippingSeqArr as $order_seq => $shippingArr){
				unset($orders);
				$orders			= $this->ordermodel->get_order($order_seq);
				$international	= $_POST['international'][$order_seq];
				if( !in_array($orders['step'], $this->ordermodel->able_step_action['goods_export']) ){
					$stock_err_order_seq[]	= $order_seq;
					continue;
				}

				if	($shippingArr && is_array($shippingArr) && count($shippingArr) > 0){
					foreach($shippingArr as $k => $shipping_seq){

						unset($export_coupon_ex);
						unset($export_status);
						unset($export_coupon_ex);
						$param		= $params[$shipping_seq];

						// 쿠폰상품 출고가 있음.
						if	(count($param['export_coupon']) > 0){
							$export_coupon_ex	= 1;
							if	(!$param['coupon_mail'] || !$param['coupon_sms']){
								$stock_err_order_seq[]	= $order_seq;
								continue;
							}
						}

						$tot_ea = 0;
						foreach($param['option_export_ea'] as $item_seq => $tmp)
							foreach($tmp as $option_seq => $ea)
								$tot_ea += (int) $ea;

						if($param['suboption_export_ea'])
							foreach($param['suboption_export_ea'] as $item_seq => $tmp)
								foreach($tmp as $option_seq => $ea)
									$tot_ea += (int) $ea;

						if($tot_ea == 0 && !$export_coupon_ex){
							$stock_err_order_seq[]	= $order_seq;
							continue;
						}


						// 출고 수량 및 재고 체크
						$chk_error_stock	= false;
						if($param['option_export_ea']) foreach($param['option_export_ea'] as $item_seq => $tmp){
							foreach($tmp as $option_seq => $ea){
								$ea								= (int) $ea;
								$option_remind_ea[$option_seq]	= $this->ordermodel->check_option_remind_ea($ea, $option_seq);
								if($option_remind_ea[$option_seq] === false){
									$chk_error_stock	= true;
									break;	// tmp break point
								}

								if($cfg_order['export_err_handling'] == 'error'){
									$data_option	= $this->ordermodel->get_order_item_option($option_seq);
									$goods_seq		= $data_option['goods_seq'];
									$option1		= $data_option['option1'];
									$option2		= $data_option['option2'];
									$option3		= $data_option['option3'];
									$option4		= $data_option['option4'];
									$option5		= $data_option['option5'];
									$goods_stock	= (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
									if($goods_stock < $ea){
										$chk_error_stock	= true;
										break;	// tmp break point
									}
								}
							}
							if	($chk_error_stock)	break;	// option_export_ea break point
						}
						if	($chk_error_stock){
							$stock_err_order_seq[]	= $order_seq;
							continue;
						}


						$chk_error_stock	= false;
						if($param['suboption_export_ea']) foreach($param['suboption_export_ea'] as $item_seq => $tmp){
							foreach($tmp as $suboption_seq => $ea){
								$ea										= (int) $ea;
								$suboption_remind_ea[$suboption_seq]	= $this->ordermodel->check_suboption_remind_ea($ea,$suboption_seq);
								if($suboption_remind_ea[$suboption_seq] === false){
									$chk_error_stock	= true;
									break;	// suboption_seq break point
								}

								if($cfg_order['export_err_handling'] == 'error'){
									$data_option	= $this->ordermodel->get_order_item_suboption($suboption_seq);
									$goods_seq		= $data_option['goods_seq'];
									$title			= $data_option['title'];
									$suboption		= $data_option['suboption'];
									$goods_stock	= (int) $this->goodsmodel->get_goods_suboption_stock($goods_seq,$title,$suboption);
									if($goods_stock < $ea){
										$chk_error_stock	= true;
										break;	// suboption_seq break point
									}
								}
							}
							if	($chk_error_stock)	break;	// suboption_export_ea break point
						}
						if	($chk_error_stock){
							$stock_err_order_seq[]	= $order_seq;
							continue;
						}

						// 쿠폰상품 수량 및 재고 체크 ( 실물에서 이미 수량초과면 체크안함. )
						if	($export_coupon_ex){
							$chk_error_stock	= false;
							foreach($param['export_coupon'] as $item_seq => $opt){
								foreach($opt as $option_seq => $export){
									$ea	= 0;
									foreach($export as $k => $export_code){
										if	(!$export_code)	$ea++;
									}
									$chk_coupon	= $this->ordermodel->check_option_remind_ea($ea, $option_seq);
									if	($chk_coupon === false){
										$chk_error_stock	= true;
										break;	// export break point
									}

									if($cfg_order['export_err_handling'] == 'error'){
										$data_option	= $this->ordermodel->get_order_item_option($option_seq);
										$goods_seq		= $data_option['goods_seq'];
										$option1		= $data_option['option1'];
										$option2		= $data_option['option2'];
										$option3		= $data_option['option3'];
										$option4		= $data_option['option4'];
										$option5		= $data_option['option5'];
										$goods_stock	= (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
										if($goods_stock < $ea){
											$chk_error_stock	= true;
											break;	// export break point
										}
									}
								}
								if	($chk_error_stock)	break;	// export_coupon break point
							}
							if	($chk_error_stock){
								$stock_err_order_seq[]	= $order_seq;
								continue;
							}
						}

						// 실물 상품 출고
						if($tot_ea > 0){
							$export_status	= true;
							$export_goods++;

							unset($data);
							$data['status']						= $delivery_step;
							$data['order_seq']					= $order_seq;
							$data['shipping_seq']				= $shipping_seq;
							$data['international']				= $international;
							$data['export_date']				= $export_date;

							if($international == 'domestic'){
								$data['domestic_shipping_method']	= $param['domestic_shipping_method'];
								$data['delivery_company_code'] 		= $param['delivery_company'];
								$data['delivery_number']			= $param['delivery_number'];
							}else{
								$data['international_shipping_method']	= $param['international_shipping_method'];
								$data['international_delivery_no']		= $param['international_number'];
							}

							$data['regist_date']				= date('Y-m-d H:i:s');
							if($delivery_step == 55){//출고완료인경우
								$data['complete_date']	= date('Y-m-d H:i:s');
							}
							$export_code	= $this->exportmodel->insert_export($data);
							
							if	($export_code){

								unset($data);
								foreach($param['option_export_ea'] as $item_seq => $tmp){
									foreach($tmp as $option_seq => $ea){
										$ea = (int) $ea;
										$data['item_seq'] 		= $item_seq;
										$data['export_code'] 	= $export_code;
										$data['option_seq'] 	= $option_seq;
										$data['ea'] 			= $ea;
										if( $ea > 0 ){
											$this->db->insert('fm_goods_export_item', $data);
										}

										// 주문상태별 수량 변경
										$this->ordermodel->set_step_ea($delivery_step,$ea,$option_seq,'option');

										// 주문 option 상태 변경
										$this->ordermodel->set_option_step($option_seq,'option');
									}
								}

								unset($data);
								if($param['suboption_export_ea']) foreach($param['suboption_export_ea'] as $item_seq => $tmp){
									foreach($tmp as $suboption_seq => $ea){
										$ea = (int) $ea;
										$data['item_seq'] 		= $item_seq;
										$data['export_code'] 	= $export_code;
										$data['suboption_seq'] 	= $suboption_seq;
										$data['ea'] 			= $ea;
										if( $ea > 0 ) $this->db->insert('fm_goods_export_item', $data);

										// 주문상태별 수량 변경
										$this->ordermodel->set_step_ea($delivery_step,$ea,$suboption_seq,'suboption');

										// 주문 option 상태 변경
										$this->ordermodel->set_option_step($suboption_seq,'suboption');
									}
								}

								// 출고량 업데이트를 위한 변수선언
								$r_reservation_goods_seq	= array();

								// 출고 완료 시 재고 차감
								if($delivery_step == '55'){
									$export_item	= $this->exportmodel->get_export_item($export_code);
									foreach($export_item as $item){
										if($item['opt_type'] == 'opt'){
											$this->goodsmodel->stock_option(
												'-',
												$item['ea'],
												$item['goods_seq'],
												$item['option1'],
												$item['option2'],
												$item['option3'],
												$item['option4'],
												$item['option5']
											);

											// 출고량 업데이트를 위한 변수정의
											if(!in_array($item['goods_seq'],$r_reservation_goods_seq)){
												$r_reservation_goods_seq[] = $item['goods_seq'];
											}

										}else{
											$this->goodsmodel->stock_suboption(
												'-',
												$item['ea'],
												$item['goods_seq'],
												$item['title1'],
												$item['option1']
											);

											// 출고량 업데이트를 위한 변수정의
											if(!in_array($item['goods_seq'],$r_reservation_goods_seq)){
												$r_reservation_goods_seq[] = $item['goods_seq'];
											}
										}
									}

									// 출고예약량 업데이트
									foreach($r_reservation_goods_seq as $goods_seq){
										$this->goodsmodel->modify_reservation_real($goods_seq);
									}

									/* 출고자동화 전송 */
									$this->load->model('invoiceapimodel');
									$invoiceExportResult = $this->invoiceapimodel->export($export_code);
									if($invoiceExportResult['resultDeliveryNumber'] && !$param['delivery_number']){
										$param['delivery_number'] = $invoiceExportResult['resultDeliveryNumber'][0];
									}

									if(!$orders['linkage_id']){
										$exportArr[]					= $export_code;
										$ordersArr[$export_code]		= $orders;
										$dcompanyArr[$export_code]		= $param['delivery_company'];
										$dnumberArr[$export_code]		= $param['delivery_number'];
										$goodsNameArr[$export_code]		= $export_item[0]['goods_name'];
										if	(count($export_item) > 1)
											$goodsNameArr[$export_code]	.= '외 '.(count($export_item) - 1).'건';
										
									}
								}
							}
						}	// End IF tot_ea

						// 쿠폰상품 출고 및 재발송 처리
						if	($export_coupon_ex){
							foreach($param['export_coupon'] as $item_seq => $opt){
								foreach($opt as $option_seq => $export){
									$coupon_ea	= 0;
									foreach($export as $k => $exportCode){
										if	($exportCode){
											$resend_coupon	= true;
											// 쿠폰상품 출고 메시지 재발송 ( 쿠폰은 1/ea당 출고 1건이기에 code로 처리 )
											$this->exportmodel->coupon_export_send($exportCode, 'all', $param['coupon_mail'], $param['coupon_sms']);
										}else{
											$coupon_ea++;
										}
									}

									if	($coupon_ea > 0){
										$export_status	= true;
										$export_coupon++;

										// 쿠폰상품 출고처리
										$coupon_param['option_seq']		= $option_seq;
										$coupon_param['export_date']	= $export_date;
										$coupon_param['coupon_mail']	= $param['coupon_mail'];
										$coupon_param['coupon_sms']		= $param['coupon_sms'];
										$this->exportmodel->coupon_export($coupon_param, $coupon_ea);

										// 주문상태별 수량 변경
										$this->ordermodel->set_step_ea(55, $coupon_ea, $option_seq, 'option');

										// 주문 option 상태 변경
										$this->ordermodel->set_option_step($option_seq, 'option');
									}
								}
							}

							//받는 사람 쿠폰 SMS 데이터
							if(count($this->coupon_reciver_sms['order_cellphone']) > 0){
								$order_count = 0;
								foreach($this->coupon_reciver_sms['order_cellphone'] as $key=>$value){
									$coupon_arr_params[$order_count]		= $this->coupon_reciver_sms['params'][$key];
									$coupon_order_no[$order_count]			= $this->coupon_reciver_sms['order_no'][$key];
									$coupon_order_cellphones[$order_count] = $this->coupon_reciver_sms['order_cellphone'][$key];
									$order_count					=$order_count+1;
								}

								$commonSmsData['coupon_released']['phone'] = $coupon_order_cellphones;
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

								$commonSmsData['coupon_released2']['phone'] = $reciver_order_cellphones;
								$commonSmsData['coupon_released2']['params'] = $reciver_arr_params;
								$commonSmsData['coupon_released2']['order_no'] = $reciver_order_no;
								
							}
						}	// End IF export_coupon_ex



						if	($export_status){
							# 오픈마켓 송장등록 #
							$this->load->model('openmarketmodel');
							if	($this->openmarketmodel->chk_linkage_service())
								$this->openmarketmodel->request_send_export($export_code);
						}	// End IF export_status

					}	// End foreach $shippingArr
				}

				if	($export_status){
					$export_sucess_order[]	= $order_seq;
					$log_str = "관리자가 출고처리를 하였습니다.";
					$this->ordermodel->set_log($order_seq,'process',$this->managerInfo['mname'],'출고처리',$log_str);
				}

				// 주문상태 변경
				$this->ordermodel->set_order_step($order_seq);
			}
		}

		// 출고완료 메일링
		if	($exportArr){
			$this->load->helper('shipping');
			$order_count = 0;
			$recipient_count = 0;
			foreach($exportArr as $k => $export_code){
				unset($smsParam);
				$orders				= $ordersArr[$export_code];
				$delivery_company	= $dcompanyArr[$export_code];
				$delivery_number	= $dnumberArr[$export_code];
				$goods_name			= $goodsNameArr[$export_code];

				// 출고완료 메일링
				send_mail_step55($export_code);

				// 출고완료시 sms
				if( $orders['order_cellphone'] ){
					$shipping_company_arr		= get_shipping_company($orders['international'],$orders['shipping_method']);
					$smsParam['delivery_company']	= $shipping_company_arr[$delivery_company]['company'];
					$smsParam['delivery_number']	= $delivery_number;
					$smsParam['goods_name']			= $goods_name;

					if	($orders['payment'] == 'bank'){
						$bank_arr					= explode(' ', $orders['bank_account']);
						$smsParam['settle_kind']	= $bank_arr[0] . ' 입금확인';
					}else{
						$smsParam['settle_kind']	= $orders['mpayment'] . ' 입금확인';
					}

					$smsParam['shopName']		= $this->config_basic['shopName'];
					$smsParam['ordno']			= $orders['order_seq'];
					$smsParam['export_code']	= $export_code;
					$smsParam['user_name']		= $orders['order_user_name'];
					#상품별 일괄 출고 일괄 발송 부분
					//sendSMS($orders['order_cellphone'], 'released', '', $smsParam);
					
					$params['shopName']		= $this->config_basic['shopName'];
					$params['ordno']		= $orders['order_seq'];
					$params['export_code']	= $export_code;
					$params['user_name']	= $orders['order_user_name'];
					$params['member_seq']	= $orders['member_seq'];
					$arr_params[$order_count] = $params;
					$order_no[$order_count] = $orders['order_seq'];
					$order_cellphones[$order_count]		= $orders['order_cellphone'];

					# 주문자와 받는분이 다를때 받는분에게도 문자 전송
					if( $orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))){
						$recipient_cellphones[$recipient_count]	= $orders['recipient_cellphone'];	//받는분
						$recipient_arr_params[$recipient_count] = $params;
						$recipient_order_no[$recipient_count] = $orders['order_seq'];
						$recipient_count = $recipient_count+1;	
					}
					$order_count	= $order_count+1;	
				}
			}

			if($order_cellphones){
				$commonSmsData['released']['phone'] = $order_cellphones;
				$commonSmsData['released']['params'] = $arr_params;
				$commonSmsData['released']['order_no'] = $order_no;
			}
			# 주문자와 받는분이 다를때 받는분에게도 문자 전송
			if($recipient_cellphones){
				$commonSmsData['released2']['phone'] = $recipient_cellphones;
				$commonSmsData['released2']['params'] = $recipient_arr_params;
				$commonSmsData['released2']['order_no'] = $recipient_order_no;
			}

		}

		if(count($commonSmsData) > 0){
			commonSendSMS($commonSmsData);
		}

		if( $stock_err_order_seq ){
			$msg = "출고처리  되었습니다.<br/>단, 재고수량이 부족한 상품이 있는 주문은 ".$this->arr_step[$delivery_step]." 처리되지 않았습니다.";
			$stock_err_order_seq = array_unique($stock_err_order_seq);
			$msg .= '<br/>주문번호 : ' . implode('<br/>주문번호 : ', $stock_err_order_seq);
			$height = 150 + count($stock_err_order_seq) * 15;
			openDialogAlert($msg,500,$height,'parent',"parent.location.reload();");
		}else{
			// 처리 완료 메시지
			if	($resend_coupon && !$export_goods && !$export_coupon){
				$endMsg		= "재발송 되었습니다.";
			}else{
				if	($export_goods > 0){
					if	($delivery_step == '55')
						$endMsg[]	= "실물상품이 출고완료 처리 되었습니다.";
					else
						$endMsg[]	= "실물상품이 출고준비 처리 되었습니다.";
				}
				if	($export_coupon > 0)
					$endMsg[]	= "쿠폰상품의 쿠폰번호가 발송되었습니다.";

				$endMsg	= implode('<br/>', $endMsg);
			}
			openDialogAlert($endMsg,400,200,'parent',"parent.location.reload();");
		}

	}	// End Function order_export

	public function modify_order_item_option(){
		
		$item_option_seq = $_POST['item_option_seq'];
		$goods_option_seq = $_POST['goods_option_seq'];

		if(!$item_option_seq || !$goods_option_seq) exit;

		$query = $this->db->query("select * from fm_goods_option where option_seq=?",$goods_option_seq);
		$data = $query->row_array();

		if($data){

			$option_title = explode(",",$data['option_title']);

			$setData = array();
			foreach($option_title as $k=>$title){
				$setData[] = "title".($k+1)."='{$title}'";
				$setData[] = "option".($k+1)."='".$data['option'.($k+1)]."'";
			}

			if($setData){
				$this->db->query("update fm_order_item_option set
				".implode(",",$setData)."
				where item_option_seq=?				
				",$item_option_seq);
			}

			$result = array('success'=>'1');
			echo json_encode($result);
			exit;
		}
	}
}

/* End of file order_process.php */
/* Location: ./app/controllers/admin/order_process.php */