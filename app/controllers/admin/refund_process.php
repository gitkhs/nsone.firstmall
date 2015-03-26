<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class refund_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->arr_step 	= config_load('step');

	}

	public function save(){
		
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');
		$this->load->model('emoneymodel');
		$this->load->model('membermodel');
		$this->load->model('eventmodel');
		$this->load->helper('text');
		$this->load->helper('order');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('refund_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$cfg_order = config_load('order');

		/* 적립금/이머니 환경 로드 */
		$cfg_reserve = config_load('reserve');

		/* 이머니 미사용 시 */
		if($cfg_reserve['cash_use']=='N' && $data_refund['refund_cash'] > 0){
			openDialogAlert("이머니 사용이 불가능 합니다.",400,140,'parent');
			exit;
		}

		/* 환불 정보 저장 */
		$saveData = array(
			'adjust_use_coupon'		=> $_POST['adjust_use_coupon'],
			'adjust_use_promotion'	=> $_POST['adjust_use_promotion'],
			'adjust_use_emoney'		=> $_POST['adjust_use_emoney'],
			'adjust_use_cash'		=> $_POST['adjust_use_cash'],
			'adjust_use_enuri'		=> $_POST['adjust_use_enuri'],
			'adjust_refund_price'	=> $_POST['adjust_refund_price'],
			'refund_method'			=> $_POST['refund_method'],
			'refund_price'			=> $_POST['refund_price'],
			'refund_emoney'			=> $_POST['refund_emoney'],
			'refund_emoney_limit_date'			=> $_POST['refund_emoney_limit_date'],
			'refund_cash'			=> $_POST['refund_cash'],
		);

		$this->db->where('refund_code', $_POST['refund_code']);
		$this->db->update("fm_order_refund",$saveData);

		/* 저장된 정보 로드 */
		$data_refund		= $this->refundmodel->get_refund($_POST['refund_code']);
		$data_refund_item 	= $this->refundmodel->get_refund_item($_POST['refund_code']);
		$data_order			= $this->ordermodel->get_order($data_refund['order_seq']);
		$data_member		= $this->membermodel->get_member_data($data_order['member_seq']);

		$order_total_ea = $this->ordermodel->get_order_total_ea($data_refund['order_seq']);
		if($data_refund_item) foreach($data_refund_item as $item) $refund_ea += $item['ea'];

		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');

		$saveData = array();
		$saveData['status'] = $_POST['status'];

		/* 환불신청 또는 환불처리중에서 환불완료로 변경될때 */
		if($data_refund['status']!='complete' && $_POST['status']=='complete')
		{
			$saveData['refund_date'] = date('Y-m-d H:i:s');
			$saveData['manager_seq'] = $this->managerInfo['manager_seq'];

			/* 무통장 환불 처리 */
			if($_POST['refund_method']=='bank' || $_POST['refund_method']=='manual' || $_POST['refund_method']=='cash' || $_POST['refund_method']=='emoney')
			{
				// 별다른 처리 없음
			}
			/* PG 결제취소 처리 */
			else
			{
				if($data_order['settleprice']<$data_refund['refund_price']){
					openDialogAlert("환불금액이 실결제금액보다 클 수 없습니다.",400,140,'parent');
					exit;
				}

				$pgCompany = $this->config_system['pgCompany'];

				// 카카오 페이의 PG사를 추출하기 위한 데이터 :: 2015-02-25 lwh
				if($data_order['pg']=='kakaopay'){
					$pglog_tmp				= $this->ordermodel->get_pg_log($data_order['order_seq']);
					$pg_log_data			= $pglog_tmp[0];
					$data_order['pg_log']	= $pg_log_data;
					$pgCompany				= $data_order['pg'];
				}

				$pgCancelType = $data_refund['cancel_type'];

				/* 카드일땐 금액에 따라 전체취소할지 부분취소할지 결정함 */
				if($data_order['settleprice']==$data_refund['refund_price'] && $order_total_ea==$refund_ea){
					// 전체금액일땐 전체취소
					$data_refund['cancel_type'] = 'full';
				}else{
					// 부분금액일땐 부분취소
					$data_refund['cancel_type'] = 'partial';
				}

				/* PG 부분취소 */
				if($data_refund['cancel_type']=='partial')
				{

					$cancelFunction = "{$pgCompany}_cancel";
					$cancelResult = $this->refundmodel->$cancelFunction($data_order,$data_refund);

					if(!$cancelResult['success']){
						openDialogAlert("{$pgCompany} 부분매입취소 실패<br /><font color=red>{$cancelResult['result_code']} : {$cancelResult['result_msg']}</font>",400,160,'parent','');
						exit;
					}

					$data_refund['cancel_type'] = 'partial';

				}
				/* PG 전체취소 */
				else
				{
					if($data_order['settleprice']!=$data_refund['refund_price']){
						openDialogAlert("PG 전체취소시에는 결제금액과 환불금액이 동일해야합니다.",400,140,'parent');
						exit;
					}

					$cancelFunction = "{$pgCompany}_cancel";
					$cancelResult = $this->refundmodel->$cancelFunction($data_order,$data_refund);

					if(!$cancelResult['success']){
						openDialogAlert("{$pgCompany} 결제 취소 실패<br /><font color=red>{$cancelResult['result_code']} : {$cancelResult['result_msg']}</font>",400,160,'parent','');
						exit;
					}
				}

			}

			// 추가옵션 관련 아이템 재배열
			$items_array	= array();
			if($data_refund_item)foreach($data_refund_item as $item) {

				if( $item['goods_kind'] == 'coupon' ) {//쿠폰상품 적립금/포인트, 재고, 할인쿠폰 반환없음
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
					$items_array[$item['option_seq']]['price']			+= $item['price'] * $item['ea'];
					$items_array[$item['option_seq']]['ea']			+= $item['ea'];
					$items_array[$item['option_seq']]['option_ea']	+= $item['option_ea'];
					$items_array[$item['option_seq']]['goods_name']	= $item['goods_name'];
					$items_array[$item['option_seq']]['options']		= $item['options_str'];
					$items_array[$item['option_seq']]['inputs']		= $this->ordermodel->get_input_for_option($item['item_seq'], $item['option_seq']);
				}
				if	(!$first_option_seq)	$first_option_seq	= $item['option_seq'];
			}



				/* 적립금 지급 */
				if($data_refund['refund_emoney'])
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'refund',
						'limit_date'=>$_POST['refund_emoney_limit_date'],
						'emoney'	=> $data_refund['refund_emoney'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[복원] 주문환불({$data_refund['refund_code']})에 의한 적립금 환원",
					);
					$this->membermodel->emoney_insert($params, $data_order['member_seq']);
				}

				/* 이머니 지급 */
				if($data_refund['refund_cash'] )
				{
					$params = array(
						'gb'		=> 'plus',
						'type'		=> 'refund',
						'cash'		=> $data_refund['refund_cash'],
						'ordno'	=> $data_order['order_seq'],
						'memo'		=> "[복원] 주문환불({$data_refund['refund_code']})에 의한 이머니 환원",
					);
					$this->membermodel->cash_insert($params, $data_order['member_seq']);
				}

			if( !$refund_goods_coupon_ea ) {
				// 구매확정 사용하지 않는 경우에만 회수
				// if(!$cfg_order['buy_confirm_use'])
				{
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
				$refund_price = $data_refund['refund_price'] + $data_refund['refund_emoney'];
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
		}

		$this->db->where('refund_code', $_POST['refund_code']);
		$this->db->update("fm_order_refund",$saveData);

		/* 환불신청 또는 환불처리중에서 환불완료로 변경될때 */
		if($data_refund['status']!='complete' && $_POST['status']=='complete')
		{
			/* 반품->환불완료시 SMS 발송 */
			//if($data_refund['refund_type']=='return'){
			//}

			/* 로그저장 */
			$logTitle = "환불완료";
			$logDetail = "관리자가 환불완료처리를 하였습니다.";
			$logParams	= array('refund_code' => $_POST['refund_code']);
			$this->ordermodel->set_log($data_order['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);

			//회원일경우 id 불러오기
			if(trim($data_order['member_seq'])){
				$userid		= $this->membermodel->get_member_userid(trim($data_order['member_seq']));
			}

			$params = array();
			$params['shopName']		= $this->config_basic['shopName'];
			$params['ordno']		= $data_order['order_seq'];
			$params['user_name']	= $data_order['order_user_name'];
			$params['member_seq']	= $data_order['member_seq'];
			if( $data_order['order_cellphone'] ) {
				if($refund_goods_coupon_ea){
					$this->load->model('returnmodel');
					$data_return = $this->returnmodel->get_return_refund_code($_POST['refund_code']);
					$data_return_item 	= $this->returnmodel->get_return_item($data_return['return_code']);
					if($data_refund['refund_type']=='return') {
						coupon_send_sms_refund($data_return_item[0]['export_code'],$data_order);
					}else{
						coupon_send_sms_cancel($data_return_item[0]['export_code'],$data_order);
					}
				}else{
					$smsemailtype = ($data_refund['refund_type']=='return') ? 'refund' : 'cancel';

					//SMS 데이터 생성
					$commonSmsData[$smsemailtype]['phone'][] = $data_order['order_cellphone'];
					$commonSmsData[$smsemailtype]['params'][] = $params;
					$commonSmsData[$smsemailtype]['order_no'][] = $data_order['order_seq'];
					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}
					//sendSMS($data_order['order_cellphone'], $smsemailtype, '', $params);
				}
			}

			$callback = "parent.document.location.reload();";
			openDialogAlert("환불처리가 완료되었습니다.",400,140,'parent',$callback);
		}else{
			$callback = "parent.document.location.reload();";
			openDialogAlert("환불정보가 저장되었습니다.",400,140,'parent',$callback);
		}
	}

	// 관리자메모 변경
	public function admin_memo()
	{
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('refund_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$refund_seq = $_GET['seq'];
		$this->validation->set_rules('admin_memo','관리자메모','trim|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		$data['admin_memo'] = $_POST['admin_memo'];
		$this->db->where('refund_seq', $refund_seq);
		$this->db->update('fm_order_refund', $data);
		openDialogAlert("관리자 메모가 변경 되었습니다.",400,140,'parent','');
	}

	public function batch_reverse_refund(){
		$result = array();
		foreach($_POST['code'] as $refund_code){
			$result[] = $this->exec_reverse_refund($refund_code);
		}
		echo implode("<br />",$result);
	}

	public function exec_reverse_refund($refund_code){

		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');
		$this->load->model('returnmodel');

		$data_refund 		= $this->refundmodel->get_refund($refund_code);
		$data_refund_item 	= $this->refundmodel->get_refund_item($refund_code);
		$data_order			= $this->ordermodel->get_order($data_refund['order_seq']);

		if($data_refund['refund_type'] == 'return'){
			return "{$refund_code} - 반품에 의한 환불건은 반품리스트에서 삭제하실 수 있습니다.";
		}

		if($data_refund['status'] == 'complete'){
			return "{$refund_code} - 환불 완료된 건은 삭제하실 수 없습니다.";
		}

		$this->db->trans_begin();
		$rollback = false;

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		foreach($data_refund_item as $refund_item){
			if($refund_item['opt_type']=='opt'){
				$option_seq = $refund_item['option_seq'];

				$query = "select * from fm_order_item_option where item_option_seq=?";
				$query = $this->db->query($query,array($option_seq));
				$optionData = $query->row_array();

				if($optionData['step']==85){
					$this->db->set('step','25');
				}

				$this->db->set('refund_ea','refund_ea-'.$refund_item['ea'],false);
				$this->db->where('item_option_seq',$option_seq);
				$this->db->update('fm_order_item_option');

				$mode = 'option';
				$this->ordermodel->set_step_ea(85,-$refund_item['ea'],$option_seq,$mode);

				if($data_refund['refund_type'] != 'return'){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($optionData['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $optionData['goods_seq'];
					}
				}
			}else if($refund_item['opt_type']=='sub'){
				$option_seq = $refund_item['option_seq'];

				$query = "select * from fm_order_item_suboption where item_suboption_seq=?";
				$query = $this->db->query($query,array($option_seq));
				$optionData = $query->row_array();

				if($optionData['step']==85){
					$this->db->set('step','25');
				}

				$this->db->set('refund_ea','refund_ea-'.$refund_item['ea'],false);
				$this->db->where('item_suboption_seq',$option_seq);
				$this->db->update('fm_order_item_suboption');

				$mode = 'suboption';
				$this->ordermodel->set_step_ea(85,-$refund_item['ea'],$option_seq,$mode);

				if($data_refund['refund_type'] != 'return'){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($optionData['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $optionData['goods_seq'];
					}
				}
			}

			// 출고예약량 업데이트
			$this->goodsmodel->modify_reservation_real($refund_item['goods_seq']);
		}

		$logTitle	= "환불삭제 {$refund_code}";
		$logDetail	= "{$refund_code} 환불건을 삭제처리했습니다.";
		$this->ordermodel->set_log($data_order['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail);

		if($data_order['step'] == '85'){
			$prev_step = $data_order['step'];
			$this->ordermodel->set_order_step($data_order['order_seq']);
			$data_order	= $this->ordermodel->get_order($data_order['order_seq']);
			$target_step = $data_order['step'];

			if($prev_step != $target_step){
				$this->ordermodel->set_log($data_order['order_seq'],'process',$this->managerInfo['mname'],'되돌리기 ('.$this->arr_step[$prev_step].' => '.$this->arr_step[$target_step].')','-');
			}
		}

		$sql = "delete from fm_order_refund where refund_code=?";
		$this->db->query($sql, $refund_code);

		$sql = "delete from fm_order_refund_item where refund_code=?";
		$this->db->query($sql, $refund_code);

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		if ($this->db->trans_status() === FALSE || $rollback == true)
		{
		    $this->db->trans_rollback();
		    echo "환불삭제 처리중 오류가 발생했습니다.";
			exit;
		}
		else
		{
		    $this->db->trans_commit();
		}

		return "{$refund_code} - 삭제완료";
	}

}
/* End of file refund_process.php */
/* Location: ./app/controllers/admin/refund_process.php */