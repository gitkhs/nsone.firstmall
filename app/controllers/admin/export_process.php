<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class export_process extends admin_base {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->helper('order');

		$this->arr_step 	= config_load('step');

		$auth = $this->authmodel->manager_limit_act('order_goods_export');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}

	}

	public function exec_complete_export($export_code,$cfg_order=''){
		return $this->exportmodel->exec_complete_export($export_code,$cfg_order);
	}

	// 배송중 처리
	public function exec_going_delivery($export_code){
		$this->exportmodel->exec_going_delivery($export_code);
	}

	public function exec_complete_delivery($export_code){
		$this->exportmodel->exec_complete_delivery($export_code);
	}

	public function going_delivery(){
		$export_code = $_POST['export_code'];
		$this->exec_going_delivery($export_code);
		openDialogAlert("배송중처리가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	public function complete_delivery(){
		$this->exportSmsData = array();
		$export_code = $_POST['export_code'];
		$this->exec_complete_delivery($export_code);

		if(count($this->exportSmsData) > 0){
			commonSendSMS($this->exportSmsData);
		}

		openDialogAlert("배송완료가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	public function complete_export(){

		$this->exportSmsData = array();

		$export_code = $_POST['export_code'];
		$result = $this->exec_complete_export($export_code);

		if(count($this->exportSmsData) > 0){
			commonSendSMS($this->exportSmsData);
		}

		if(!$result){
			openDialogAlert("출고완료가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
		}else{
			openDialogAlert("‘출고수량’ 보다 ‘재고수량’이 부족합니다.<br/>출고가 가능한 수량으로 조정해 주세요!",400,150,'parent',"parent.location.reload();");
		}
	}

	// 일괄처리
	public function batch_status(){
		$this->exportSmsData = array();
		$cfg_order = config_load('order');
		foreach($_POST['international'] as $export_seq => $inter_type){
			if($inter_type == 'domestic'){ // 국내 배송일 경우
				if( $_POST['domestic_shipping_method'][$export_seq] == 'delivery' || $_POST['domestic_shipping_method'][$export_seq] == '' ){
					$query = "
					update fm_goods_export set
					export_date = ?,
					domestic_shipping_method = 'delivery',
					delivery_company_code = ?,
					delivery_number = ?
					where export_seq = ?";
					$this->db->query($query,array(
							$_POST['export_date'][$export_seq],
							$_POST['delivery_company'][$export_seq],
							$_POST['delivery_number'][$export_seq],$export_seq
						)
					);
				}
			}else{
				$query = "
				update fm_goods_export set
				export_date = ?,
				international_shipping_method = ?,
				international_delivery_no = ?
				where export_seq = ?";
				$this->db->query($query,array(
						$_POST['export_date'][$export_seq],
						$_POST['international_shipping_method'][$export_seq],
						$_POST['international_number'][$export_seq],$export_seq
					)
				);
			}
		}

		$mode 	 = $_POST['mode'];
		foreach($_POST['export_code'] as $code){
			if( $mode != 'save' ){
				if( $mode == 'complete_export' ){
					$err_export_code = $this->exec_complete_export($code,$cfg_order);
					if($err_export_code)
					{
						$r_err_export_code[] = $err_export_code;
					}
				}else{
					$this->{'exec_'.$mode}($code);
				}
			}
		}
		
		if(count($this->exportSmsData) > 0){
			commonSendSMS($this->exportSmsData);
		}

		if($r_err_export_code){
			$err_msg = "출고처리  되었습니다.<br/>단, 재고수량이 부족한 상품이 있는 출고는 출고완료 처리되지 않았습니다!";
			$r_err_export_code = array_unique($r_err_export_code);
			$err_msg .= '<br/>출고번호 : ' . implode('<br/>출고번호 : ', $r_err_export_code);
			$height = 150 + count($r_err_export_code) * 15;
			openDialogAlert($err_msg,500,$height,'parent',"parent.location.reload();");
			exit;
		}

		if( $mode == 'save'){
			openDialogAlert("변경 정보가 저장 되었습니다.",400,140,'parent',"parent.location.reload();");
		}else if( $mode == 'going_delivery'){
			openDialogAlert("배송중 처리가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
		}else if( $mode == 'complete_delivery'){
			openDialogAlert("배송완료 처리가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
		}else if( $mode == 'complete_export'){
			openDialogAlert("출고완료가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
		}

	}

	public function export_modify(){
		if($_POST['delivery_company_code']){
			foreach($_POST['delivery_company_code'] as $export_code => $delivery_company_code)
			{
				foreach($_POST['delivery_company_code'][$export_code] as $k => $delivery_company_code)
				{
					$update_param = array();
					$update_param[] = $_POST['export_date'][$export_code][$k];
					$update_param[] = $_POST['delivery_company_code'][$export_code][$k];
					$update_param[] = $_POST['delivery_number'][$export_code][$k];
					$update_param[] = $export_code;
					$query = "update fm_goods_export set export_date=?,delivery_company_code=?,delivery_number=? where export_code=?";
					$this->db->query($query,$update_param);
				}
			}
		}
		if($_POST['international_shipping_method']){
			foreach($_POST['international_shipping_method'] as $export_code => $international_company_code)
			{
				foreach($_POST['international_shipping_method'][$export_code] as $k => $international_company_code)
				{
					$update_param = array();
					$update_param[] = $_POST['export_date'][$export_code][$k];
					$update_param[] = $_POST['international_shipping_method'][$export_code][$k];
					$update_param[] = $_POST['international_delivery_no'][$export_code][$k];
					$update_param[] = $export_code;
					$query = "update fm_goods_export set export_date=?,international_shipping_method=?,international_delivery_no=? where export_code=?";
					$this->db->query($query,$update_param);
				}
			}
		}
		openDialogAlert("출고정보가 변경되었습니다.",400,140,'parent',"");
	}

	public function ea_modify(){
		$export = $this->exportmodel->get_export($_GET['export_code']);

		if($_POST['ea']){
			foreach($_POST['ea'] as $export_item_seq => $export_ea){
				$export_item = $this->exportmodel->get_export_item_by_item_seq($export_item_seq);
				if($export_item['option_seq']){
					$item = $this->ordermodel->get_order_item_option($export_item['option_seq']);
					$option_mode='option';
					$option_seq = $export_item['option_seq'];

					$shipping_item = $this->ordermodel->get_shipping_item_option($export['shipping_seq'], $option_seq);

				}else{
					$item = $this->ordermodel->get_order_item_suboption($export_item['suboption_seq']);
					$option_mode='suboption';
					$option_seq = $export_item['suboption_seq'];

					$shipping_item = $this->ordermodel->get_shipping_item_suboption($export['shipping_seq'], $option_seq);
				}
				$order_seq = $item['order_seq'];

				// 수량 변경 가능 수량
				$limit_ea = $item['step35']+ $export_item['ea'];

				if($limit_ea > $shipping_item['ea']) $limit_ea = $shipping_item['ea'];

				if($limit_ea < $export_ea){
					openDialogAlert("출고가능 최대 수량은 ".$limit_ea."개 입니다.",400,140,'parent',"parent.location.reload();");
					exit;
				}

				if($export_ea == 0){
					$export_items = $this->exportmodel->get_export_item($export_item['export_code']);
					if(count($export_items) == 1){
						openDialogAlert("마지막 출고 상품은 수량을 0으로 변경하실 수 없습니다.",400,140,'parent',"parent.location.reload();");
						exit;
					}
					$this->exportmodel->delete_export_item_by_item_seq($export_item_seq);
				}else{
					$this->exportmodel->update_ea_export_item($export_item_seq,$export_ea);
				}

				$this->ordermodel->set_step_ea(45,$export_ea-$export_item['ea'],$option_seq,$option_mode);
				$this->ordermodel->set_option_step($option_seq,$option_mode);
				$this->ordermodel->set_order_step($order_seq);
			}
		}
		openDialogAlert("수량이 변경되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	public function reverse_export()
	{
		$export_code = $_POST['export_code'];
		$result = $this->exec_reverse_export($export_code);
		if($result == 35){
			openDialogAlert("출고한 상품의 주문상태가 상품준비중으로 변경되었습니다.",400,140,'parent',"parent.location.href='../export/catalog';");
		}else{
			openDialogAlert("출고상태가 변경되었습니다.",400,140,'parent',"parent.location.reload();");
		}
	}

	public function batch_reverse_export()
	{

		foreach($_POST['code'] as $export_code){
			$reverse	= $this->exec_reverse_export($export_code);
			if	($reverse == 'coupon')	$except[]	= $export_code;
			else						$result		= $reverse;
		}
		if($result == 35){
			echo "출고한 상품의 주문상태가 상품준비중으로 변경되었습니다.";
		}else{
			echo("출고상태가 변경되었습니다.");
		}

		if	($except && count($except) > 0 )
			echo "<br/>쿠폰상품의 출고(".implode(', ', $except).")는 되돌릴 수 없습니다.";
	}

	public function exec_reverse_export($export_code)
	{
		$this->load->model('goodsmodel');
		$r_reservation_goods_seq = array();

		$data_export = $this->exportmodel->get_export($export_code);
		$data_export_item = $this->exportmodel->get_export_item($export_code);

		// 쿠폰상품 예외처리
		if	($data_export_item[0]['goods_kind'] == 'coupon')	return 'coupon';

		$source_step = (string) $data_export['status'];
		$target_step = $data_export['status']-10;
		$target_step = (string) $target_step;

		// 상품수량 환원
		$r_reservation_goods_seq = array();
		foreach($data_export_item as $item){
			$option_mode = "option";
			if($item['opt_type'] == 'sub') $option_mode = "suboption";

			// 상품 재고 환원
			if($target_step == '45'){

				// 출고량 업데이트를 위한 변수정의
				if(!in_array($item['goods_seq'],$r_reservation_goods_seq)){
					$r_reservation_goods_seq[] = $item['goods_seq'];
				}

				if($item['opt_type'] == 'opt'){
					$this->goodsmodel->stock_option('+',$item['ea'],$item['goods_seq'],$item['option1'],$item['option2'],$item['option3'],$item['option4'],$item['option5']);
				}else{
					$this->goodsmodel->stock_suboption('+',$item['ea'],$item['goods_seq'],$item['title1'],$item['option1']);
				}
			}

			// 옵션 상태 수량 변경
			$plus = $item['ea'];
			$minus = -1*$item['ea'];
			$this->ordermodel->set_step_ea($source_step,$minus,$item['option_seq'],$option_mode);
			$this->ordermodel->set_step_ea($target_step,$plus,$item['option_seq'],$option_mode);
			$this->ordermodel->set_option_step($item['option_seq'],$option_mode);

			// 환원될 적립금 합산
			if($target_step == '65' && $data_export['reserve_save'] == 'save'){
				$reserve = 0;
				if($item['opt_type'] == 'opt') $reserve = $this->ordermodel->get_option_reserve($item['option_seq']);
				else $reserve = $this->ordermodel->get_suboption_reserve($item['option_seq']);
				$tot_reserve += $reserve * $item['ea'];

				$point = 0;
				if($item['opt_type'] == 'opt') $point = $this->ordermodel->get_option_reserve($item['option_seq'],'point');
				else $point = $this->ordermodel->get_suboption_reserve($item['option_seq'],'point');
				$tot_point += $point * $item['ea'];

			}
		}

		// 회원 적립금 환원
		if($target_step == '65' && $data_export['reserve_save'] == 'save'){
			$data_order = $this->ordermodel->get_order($data_export['order_seq']);
			if($data_order['member_seq']){
				$this->load->model('membermodel');
				if($tot_reserve){
					$params_reserve['gb'] = "minus";
					$params_reserve['emoney'] 	= $tot_reserve;
					$params_reserve['memo'] 	= "[".$export_code."] 배송완료 취소";
					$params_reserve['ordno']	= $data_order['order_seq'];
					$params_reserve['type'] 	= "order";
					$this->membermodel -> emoney_insert($params_reserve, $data_order['member_seq']);
				}

				if($tot_point){
					$params_point['gb'] = "minus";
					$params_point['point'] 	= $tot_point;
					$params_point['memo'] 	= "[".$export_code."] 배송완료 취소";
					$params_point['ordno']	= $data_order['order_seq'];
					$params_point['type'] 	= "order";
					$this->membermodel -> point_insert($params_point, $data_order['member_seq']);
				}


				$query = "update fm_goods_export set reserve_save = 'none' where export_code = ?";
				$this->db->query($query,array($data_export['export_code']));
			}
		}

		// 주문상태 업데이트
		$this->ordermodel->set_order_step($data_export['order_seq']);

		// 출고상태 업데이트
		$this->exportmodel->set_status($data_export['export_code'],$target_step);

		// 상품준비로 돌아갈경우 출고 목록 삭제
		if($target_step == 35){
			$this->exportmodel->delete_export($data_export['export_code']);
		}

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		// 로그
		$this->ordermodel->set_log($data_export['order_seq'],'process',$this->managerInfo['mname'],'되돌리기 ('.$this->arr_step[$data_export['status']].' => '.$this->arr_step[$target_step].')','-');

		return $target_step;
	}

	public function batch_waybill_number()
	{

		if($_POST['export_date']) foreach($_POST['export_date'] as $export_code => $export_date){


			$delivery_company_code = $_POST['delivery_company_code'][$export_code];
			$delivery_number = $_POST['delivery_number'][$export_code];
			$international_shipping_method = $_POST['international_shipping_method'][$export_code];
			$international_delivery_no = $_POST['international_delivery_no'][$export_code];

			$update_array['export_date'] = $export_date;
			$update_array['delivery_company_code'] = $delivery_company_code;
			$update_array['delivery_number'] = $delivery_number;
			$update_array['international_shipping_method'] = $international_shipping_method;
			$update_array['international_delivery_no'] = $international_delivery_no;
			$query = $this->db->update_string('fm_goods_export', $update_array, 'export_code=?');
			$this->db->query($query,array($export_code));

		}

		openDialogAlert("출고정보가 변경되었습니다.",400,140,'parent',"parent.location.reload();");
	}

	public function download_write(){

		if(count($_POST['downloads_item_use'])<1){
			$callback = "";
			openDialogAlert("다운로드 항목을 1개 이상 설정해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$item = implode("|",$_POST['downloads_item_use']);
		$params['item']			= $item;

		$this->db->where('gb', 'EXPORT');
		$result = $this->db->update('fm_exceldownload', $params);
		$msg	= "수정 되었습니다.";
		$func	= "parent.closeDialog('download_list_setting');";

		openDialogAlert($msg,400,140,'parent',$func);

	}

	public function excel_down(){
		if($_POST['export_code']){
			$criteria	= $_POST['criteria'];
			$export_code	= $_POST['export_code'];
		}else{
			$criteria	= $_GET['criteria'];
			$export_code	= $_GET['export_code'];
		}
		$this->load->model('excelexportmodel');
		$this->excelexportmodel->create_excel_list($criteria, $export_code);
		exit;
	}

	//쿠폰상품 > 사용내역 엑셀추출
	public function coupon_use_excel(){
		$this->load->model('excelexportmodel');
		$this->excelexportmodel->create_excel_coupon_use();
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

		$this->load->model('excelexportmodel');
		$result = $this->excelexportmodel->excel_upload($file_nm);

		
		if($result['result_excel_url']){
			$callback = "document.location.href='{$result['result_excel_url']}'; setTimeout('parent.location.reload()',1000)";
		}
		else{
			$callback = "parent.location.reload();";
		}
		openDialogAlert($result['msg'],600,140,'parent',$callback);
		exit;
	}

	public function buy_confirm()
	{
		$this->load->model('returnmodel');

		$export_code = $_POST['export_code'];
		
		$data_export_item = $this->exportmodel->get_export_item($export_code);
		if ($data_export_item[0]['goods_kind'] == 'coupon') {
			$callback = "parent.location.reload();";
			openDialogAlert('쇼셜쿠폰상품에서는 구매확정을 하실 수 없습니다.',400,140,'parent',$callback); 
			exit;
		}

		$cfg_order = config_load('order');
		$cfg_reserve = ($this->reserves)?$this->reserves:config_load('reserve');

		$data_export = $this->exportmodel->get_export($export_code);
		if($cfg_order['buy_confirm_use'] && $data_export['reserve_save'] == 'none')
		{
			$edate = date('Y-m-d',strtotime("-".$cfg_order['save_term']." day"));
			if( $data_export['complete_date'] >= $edate || $cfg_order['save_type'] == 'give'){
				$data_order = $this->ordermodel->get_order($data_export['order_seq']);
				$data_export_item = $this->exportmodel->get_export_item($export_code);

				foreach($data_export_item as $k => $item)
				{

					$it_s = $item['item_seq'];
					$it_ops = $item['option_seq'];
					$return_item = 0;

					if($item['opt_type']=='opt'){
						$return_item = $this->returnmodel->get_return_item_ea($it_s,$it_ops,$export_code);
					}
					if($item['opt_type']=='sub'){
						$return_item = $this->returnmodel->get_return_subitem_ea($it_s,$it_ops,$export_code);
					}

					$confirm_ea = $item['ea'] - $return_item['ea'];

					$reserve = 0;
					if($item['opt_type'] == 'opt') $reserve = $this->ordermodel->get_option_reserve($item['option_seq']);
					else $reserve = $this->ordermodel->get_suboption_reserve($item['option_seq']);
					$tot_reserve += $reserve * $confirm_ea;

					$point = 0;
					if($item['opt_type'] == 'opt') $point = $this->ordermodel->get_option_reserve($item['option_seq'],'point');
					else $point = $this->ordermodel->get_suboption_reserve($item['option_seq'],'point');
					$tot_point += $point * $confirm_ea;
				}

				if( $data_order['member_seq'] ){
					$this->load->model('membermodel');
					if($tot_reserve){
						$params_reserve['gb']		= "plus";
						$params_reserve['emoney'] 	= $tot_reserve;
						$params_reserve['memo'] 	= "[".$export_code."] 구매확정";
						$params_reserve['ordno']	= $data_order['order_seq'];
						$params_reserve['type'] 	= "order";
						$params_reserve['limit_date'] 	= get_emoney_limitdate('order');
						$this->membermodel->emoney_insert($params_reserve, $data_order['member_seq']);
					}
					if($tot_point){
						$params_point['gb']		= "plus";
						$params_point['point'] 	= $tot_point;
						$params_point['memo'] 	= "[".$export_code."] 구매확정";
						$params_point['ordno']	= $data_order['order_seq'];
						$params_point['type'] 	= "order";
						$params_point['limit_date'] 	= get_point_limitdate('order');
						$this->membermodel->point_insert($params_point, $data_order['member_seq']);
					}
					$query = "update fm_goods_export set reserve_save = 'save' where export_code = ?";
					$this->db->query($query,array($export_code));
				}
			}
		}

		$data_buy_confirm['order_seq'] = $data_export['order_seq'];
		$data_buy_confirm['export_seq'] = $data_export['export_seq'];
		$data_buy_confirm['manager_seq'] = $this->managerInfo['manager_seq'];
		$this->load->model('buyconfirmmodel');
		$this->buyconfirmmodel -> buy_confirm('admin',$export_code);
		$this->buyconfirmmodel -> log_buy_confirm($data_buy_confirm);

		


		// 배송완료 처리
		if( $data_export['status'] < 75 ){
			$this->exportSmsData = array();

			$this->exportmodel->exec_complete_delivery($export_code,$cfg_order);

			if(count($this->exportSmsData) > 0){
				commonSendSMS($this->exportSmsData);
			}
		}

		$callback = "parent.location.reload();";
		openDialogAlert('구매확정이 완료 되었습니다.',400,140,'parent',$callback);
	}


	// 쿠폰 사용 처리
	public function usecoupon(){
		$this->exportSmsData = array();
		$this->load->model("exportmodel");
		$this->load->model("returnmodel");
		$this->exportSmsData = array();
		$export_code		= trim($_POST['export_code']);
		$coupon_serial		= trim($_POST['coupon_serial']);
		$use_coupon_value	= trim($_POST['use_coupon_value']);
		if	(!$export_code || !$coupon_serial || !$use_coupon_value || !is_numeric($use_coupon_value)){
			openDialogAlert('쿠폰사용 인증에 실패하였습니다.',400,140,'parent',$callback);
			exit;
		}

		// 쿠폰 인증 확인
		$chkcoupon	= $this->exportmodel->chk_coupon(array('export_code'=>$export_code));
		if	($chkcoupon['result'] != 'success'){
			if		($chkcoupon['result'] == 'refund')		$msg	= "환불된 쿠폰입니다.";
			elseif	($chkcoupon['result'] == 'noremain')	$msg	= "이미 모두 사용된 쿠폰입니다.";
			elseif	($chkcoupon['result'] == 'notyet')		$msg	= "사용 가능한 기간이 아닙니다.";
			elseif	($chkcoupon['result'] == 'expire')		$msg	= "만료된 쿠폰입니다.";
			else											$msg	= "쿠폰사용 인증에 실패하였습니다.";

			$callback = "";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		// 쿠폰사용 내역 저장 및 배송완료 처리
		$this->load->model('exportmodel');
		$this->exportmodel->coupon_use_save($_POST);

		if(count($this->exportSmsData) > 0){
			commonSendSMS($this->exportSmsData);
		}

		$callback = "parent.location.reload();";
		openDialogAlert('쿠폰 사용확인이 완료되었습니다.',400,140,'parent',$callback);
	}

	// 이메일, SMS 개별 재발송
	public function resend_coupon_info(){

		$this->load->model('exportmodel');

		$type			= trim($_GET['type']);
		$email			= trim($_GET['email']);
		$sms			= trim($_GET['sms']);
		$export_code	= trim($_GET['export_code']);

		if	($type == 'mail'){
			if		(!$email)
				$result	= array('code'=>'error_1', 'msg' => '이메일 주소를 입력해주세요.');
			elseif	(!preg_match('/^[0-9a-zA-Z\_\-]+@[0-9a-zA-Z\_\-]+\.[a-zA-Z\.]+$/', $email))
				$result	= array('code'=>'error_2', 'msg' => '올바르지 않은 이메일 주소입니다.');
			else{
				$result	= $this->exportmodel->coupon_export_send($export_code, 'mail', $email);
				if	($result['mail_status'] == 'y')
					$result	= array('result'=>'success');
				else
					$result	= array('result'=>'fail','code'=>'error_3', 'msg' => '발송에 실패하였습니다.');
			}
		}
		if	($type == 'sms'){
			if		(!$sms)
				$result	= array('code'=>'error_1', 'msg' => '휴대폰번호를 입력해주세요.');
			elseif	(!preg_match('/^01[0-9]\-{0,1}[0-9]{3,4}\-{0,1}[0-9]{4}$/', $sms))
				$result	= array('code'=>'error_2', 'msg' => '올바르지 않은 휴대폰번호입니다.');
			else{
				// 휴대폰 번호가 - 없이 오면 -을 추가해 준다. ( 자릿수 맞춤을 위해 )
				if	(preg_match('/^[0-9]*$/', $sms)){
					if	(strlen($sms) == 10)
						$sms	= substr($sms, 0, 3).'-'.substr($sms, 3, 3).'-'.substr($sms, 6);
					else
						$sms	= substr($sms, 0, 3).'-'.substr($sms, 3, 4).'-'.substr($sms, 7);
				}
				$result	= $this->exportmodel->coupon_export_send($export_code, 'sms', '', $sms);

				if	($result['sms_status'] == 'y'){
					$result	= array('result'=>'success');
					if(count($this->resend_sms_common_data) > 0){
						commonSendSMS($this->resend_sms_common_data);
					}
				}else{
					$result	= array('result'=>'fail','code'=>'error_3', 'msg' => '발송에 실패하였습니다.');
				}
			}
		}

		echo json_encode($result);
	}

	public function print_setting(){
		config_save("export" ,array('exportPrintExportcodeBarcode'=>$_POST['exportPrintExportcodeBarcode']));
		config_save("export" ,array('exportPrintGoodsCode'=>$_POST['exportPrintGoodsCode']));
		config_save("export" ,array('exportPrintGoodsBarcode'=>$_POST['exportPrintGoodsBarcode']));
		$callback = "parent.closeDialog('print_setting_dialog')";
		openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 출고자동화 송장번호발급실패시 재전송 */
	public function invoice_export_resend(){
		$export_code = $_GET['export_code'];
		
		$this->load->model('invoiceapimodel');
		if($export_code) {
			$result = $this->invoiceapimodel->export($export_code,true);
		}

		if($result['code']=='success'){
			echo json_encode($result);
		}else{
			echo json_encode(array(
				'code' => 'fail',
				'msg' => $result['msg']
			));
		}
	}
}

/* End of file export_process.php */
/* Location: ./app/controllers/admin/export_process.php */