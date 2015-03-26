<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class mypage_process extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('snssocial');
		$this->load->library('validation');
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');
		$this->load->model('goodsmodel');
		$this->arr_step = config_load('step');
	}

	//결제취소 -> 환불
	public function order_refund(){
		$this->load->model('exportmodel');
		$this->load->helper('order');

		if(!$this->cfg_order)	$this->cfg_order = config_load('order');

		$data_order = $this->ordermodel->get_order($_POST['order_seq']);
		$data_order_items 		= $this->ordermodel->get_item($_POST['order_seq']);

		if( !in_array($data_order['step'],array('25','35','40','50','60')) ){
			openDialogAlert($this->arr_step[$data_order['step']]."에서는 환불신청을 하실 수 없습니다.",400,140,'parent');
			exit;
		}

		if($this->cfg_order['cancelDisabledStep35'] && $data_order['step']>=35){
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

		$refund_status = 'request';

		/* 신용카드 자동취소 */
		if($data_order['payment']=='card' && $order_total_ea==$cancel_total_ea)
		{
			$pgCompany		= $this->config_system['pgCompany'];

			// 카카오 페이의 PG사를 추출하기 위한 데이터 :: 2015-02-25 lwh
			if($data_order['pg']=='kakaopay'){
				$pglog_tmp				= $this->ordermodel->get_pg_log($_POST['order_seq']);
				$pg_log_data			= $pglog_tmp[0];
				$data_order['pg_log']	= $pg_log_data;
				$pgCompany				= $data_order['pg'];
			}

			if(!($pgCompany=='allat' && $this->mobileMode)) // 모바일모드에서 올앳취소시에는 바로취소 안하도록 수정(activeX때문)
			{
				$cancelFunction = "{$pgCompany}_cancel";
				$cancelResult = $this->refundmodel->$cancelFunction($data_order,array('refund_reason'=>$_POST['refund_reason'],'cancel_type'=>'full'));

				if(!$cancelResult['success']){
					openDialogAlert("{$pgCompany} 결제 취소 실패<br /><font color=red>{$cancelResult['result_code']} : {$cancelResult['result_msg']}</font>",400,160,'parent','');
					exit;
				}

				$refund_status = 'complete';
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
		);

		$this->db->trans_begin();
		$rollback = false;

		$items = array();
		$r_reservation_goods_seq = array();
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

		$this->ordermodel->set_order_step($_POST['order_seq']);
		$refund_code = $this->refundmodel->insert_refund($data,$items);

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		/* 신용카드 자동취소 */
		if($data_order['payment']=='card' && $order_total_ea==$cancel_total_ea)
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

					// 기본 적립금 유효기간 계산
					$reserve_str_ts = '';
					$reserve_limit_date = '';
					$cfg_reserves = config_load('reserve');
					if( $cfg_reserves['reserve_select'] == 'direct' ){
						$reserve_str_ts = "+".$cfg_reserves['reserve_direct']." month";
					}
					if( $cfg_reserves['reserve_select'] == 'year' ){
						$reserve_str_ts = "+".$cfg_reserves['reserve_year']." year";
					}
					if($reserve_str_ts){
						$reserve_limit_date = date('Y-m-d',strtotime($reserve_str_ts));
					}
					if( $reserve_limit_date ){
						$params['limit_date'] = $reserve_limit_date;
					}

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
				'status'				=> $refund_status,
				'refund_emoney_limit_date' => $reserve_limit_date,
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

			/* 결제취소완료 안내메일 발송 */
			$params = array_merge($saveData,$_POST);
			$params['refund_reason']	= htmlspecialchars($_POST['refund_reason']);
			$params['refund_date']		= $saveData['refund_date'];
			$params['mstatus'] 			= $this->refundmodel->arr_refund_status[$refund_status];
			$params['refund_price']		= number_format($saveData['refund_price']);
			$params['mrefund_method']	= $this->arr_payment['card'].' '.$this->arr_step[85];
			$params['items'] 			= $items_array;
			$result = sendMail($data_member['email'], 'cancel', $data_member['userid'], $params);

			/* 결제취소완료 SMS 발송 */
			$params = array();
			$params['shopName'] = $this->config_basic['shopName'];
			$params['ordno']	= $data_order['order_seq'];
			$params['user_name'] = $data_order['order_user_name'];

			$commonSmsData['cancel']['phone'][] = $data_order['order_cellphone'];
			$commonSmsData['cancel']['params'][] = $params;
			$commonSmsData['cancel']['order_no'][] = $data_order['order_seq'];

			if(count($commonSmsData) > 0){
				commonSendSMS($commonSmsData);
			}

			//sendSMS($data_order['order_cellphone'], 'cancel', '', $params);

			$logTitle	= $this->arr_step[85];
			$logDetail	= "신용카드 전체취소처리하였습니다.";
			$logParams	= array('refund_code' => $refund_code);
			$this->ordermodel->set_log($_POST['order_seq'],'process','주문자',$logTitle,$logDetail,$logParams);

			if($_POST['use_layout']){
				$callback = "
				parent.document.location.replace('../mypage/order_view?no={$_POST['order_seq']}');
				";
			}else{
				$callback = "
				parent.closeDialog('order_refund_layer');
				parent.document.location.reload();
				";
			}
			openDialogAlert("신용카드 결제취소가 완료되었습니다.",400,140,'parent',$callback);
		}else{

			$logTitle	= "결제취소 신청";
			$logDetail	= "결제취소/환불신청하였습니다.";
			$logParams	= array('refund_code' => $refund_code);
			$this->ordermodel->set_log($_POST['order_seq'],'process','주문자',$logTitle,$logDetail,$logParams);

			if($_POST['use_layout']){
				$callback = "
				parent.document.location.replace('../mypage/order_view?no={$_POST['order_seq']}');
				";
			}else{
				$callback = "
				parent.closeDialog('order_refund_layer');
				parent.document.location.reload();
				";
			}
			openDialogAlert("결제취소/환불 신청이 완료되었습니다.",400,140,'parent',$callback);
		}

	}

	//실물상품 반품 or 맞교환 -> 환불
	public function order_return(){
		$this->load->model('returnmodel');
		$this->load->model('exportmodel');
		$this->load->helper('order');

		$cfg_order = config_load('order');

		if(!$_POST['chk_seq']){
			openDialogAlert("반품 신청할 상품을 선택해주세요.",400,140,'parent');
			exit;
		}

		if(is_array($_POST['phone'])) $this->validation->set_rules('phone[]', '연락처','trim|required|numeric|max_length[4]|xss_clean');
		else  $this->validation->set_rules('phone', '연락처','trim|required|max_length[14]|xss_clean');

		if(is_array($_POST['cellphone'])) $this->validation->set_rules('cellphone[]', '휴대폰','trim|required|numeric|max_length[4]|xss_clean');
		else $this->validation->set_rules('cellphone', '휴대폰','trim|required|max_length[14]|xss_clean');

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
		$data_order_items 		= $this->ordermodel->get_item($_POST['order_seq']);
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

			if($_POST['chk_option_seq'][$k] && !$_POST['chk_suboption_seq'][$k]){
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

		$account = is_array($_POST['account']) ? implode('-',$_POST['account']) : $_POST['account'];

		$_POST['refund_method'] = ($_POST['refund_method'])?$_POST['refund_method']:'bank';

		$items = array();

		$cancel_type=0;//청약철회상품체크
		$socialcp_return_use=0;//쇼셜쿠폰상품
		foreach($_POST['chk_seq'] as $k=>$v) {
			$cancelquery = "select * from fm_order_item where item_seq=?";
			$cancelquery = $this->db->query($cancelquery,array($_POST['chk_item_seq'][$k]));
			$orditemData = $cancelquery->row_array();

			//청약철회상품체크
			$goodscanceltype = $this->goodsmodel->get_goods($orditemData['goods_seq']);
			if( $goodscanceltype['cancel_type']) {//청약철회상품 반품불가
				$cancel_type++;
				continue;
			}

			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];

			if($items[$k]['option_seq'] && !$items[$k]['suboption_seq']){

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

				//쇼셜쿠폰상품의 취소(환불) 가능여부::반품
				if ( $orditemData['goods_kind'] == 'coupon'){
					$socialcp_return_use++;
					unset($items[$k]);
					continue;
				}

				$query = "select * from fm_order_item_option where item_option_seq=?";
				$query = $this->db->query($query,array($items[$k]['option_seq']));
				$optionData = $query->row_array();

				if($_POST['mode']!='exchange'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_option_seq',$items[$k]['option_seq']);
					$this->db->update('fm_order_item_option');
				}
			}else if($items[$k]['suboption_seq']){

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
				$suboptionData = $query->row_array();

				if($_POST['mode']!='exchange'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_suboption_seq',$items[$k]['suboption_seq']);
					$this->db->update('fm_order_item_suboption');
				}
			}
		}

		if($_POST['mode']=='exchange'){//맞교환
			$refund_code = '0';
			$return_type = 'exchange';
		}else{//반품
			if( !( ( $cancel_type == count($_POST['chk_seq']) ) || ( $socialcp_return_use == count($_POST['chk_seq']) ) ) ) {
				// 반품시 최상위 주문번호 저장 :: 2014-11-27 lwh
				if($data_order['top_orign_order_seq'])
					$orgin_order_seq = $data_order['top_orign_order_seq'];
				else
					$orgin_order_seq = $_POST['order_seq'];
				$data = array(
					'order_seq' => $orgin_order_seq,
					'bank_name' => $bank,
					'bank_depositor' => $_POST['depositor'],
					'bank_account' => $account,
					'refund_reason' => '반품환불',
					'refund_type' => 'return',
					'regist_date' => date('Y-m-d H:i:s'),
					'refund_method' => $_POST['refund_method']
				);
				$refund_code = $this->refundmodel->insert_refund($data,$items);
				$return_type = 'return';

				$logTitle	= "환불신청";
				$logDetail	= "주문자 반품신청에 의한 환불신청이 접수되었습니다.";
				$logParams	= array('refund_code' => $refund_code);
				$this->ordermodel->set_log($orgin_order_seq,'process','주문자',$logTitle,$logDetail,$logParams);
			}
		}

		if(is_array($_POST['phone']))  $phone = implode('-',$_POST['phone']);
		else  $phone = $_POST['phone'];

		if(is_array($_POST['cellphone'])) $cellphone = implode('-',$_POST['cellphone']);
		else  $cellphone = $_POST['cellphone'];

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
		$insert_data['sender_address_type']		= (($_POST['return_recipient_address_type']))?$_POST['return_recipient_address_type']:"zibun";
		$insert_data['sender_address'] 				= $_POST['return_recipient_address']?$_POST['return_recipient_address']:'';
		$insert_data['sender_address_street']	= $_POST['return_recipient_address_street'];
		$insert_data['sender_address_detail']	= $_POST['return_recipient_address_detail']?$_POST['return_recipient_address_detail']:'';
		$insert_data['regist_date'] 	= date('Y-m-d H:i:s');
		$insert_data['important'] 		= 0;
		$insert_data['shipping_price_depositor'] 	= $_POST['shipping_price_depositor'];
		$insert_data['shipping_price_bank_account'] = $_POST['shipping_price_bank_account'];

		$items = array();
		foreach($_POST['chk_seq'] as $k=>$v){
			$query = "select * from fm_order_item where item_seq=?";
			$query = $this->db->query($query,array($_POST['chk_item_seq'][$k]));
			$orditemData = $query->row_array();

			//청약철회상품체크
			$goodscanceltype = $this->goodsmodel->get_goods($orditemData['goods_seq']);
			if( $goodscanceltype['cancel_type']) {//청약철회상품 반품불가
				continue;
			}

			if($_POST['chk_option_seq'][$k] && !$_POST['chk_suboption_seq'][$k]) {

				//쇼셜쿠폰상품의 취소(환불) 가능여부
				if ( $orditemData['goods_kind'] == 'coupon' ) {
					continue;
				}

			}

			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= $_POST['chk_ea'][$k];
			$items[$k]['reason_code']	= $_POST['reason'][$k];
			$items[$k]['reason_desc']	= $_POST['reason_desc'][$k];
			$items[$k]['export_code']	= $_POST['chk_export_code'][$k];

		}

		if( $cancel_type == count($_POST['chk_seq']) ) {

			if($_POST['mode']=='exchange'){
				$title="청약철회 상품으로 맞교환이 [불가능]합니다.";
				$logTitle = "맞교환신청";
			}else{
				$title="청약철회 상품으로 반품신청이 [불가능] 합니다.";
				$logTitle = "반품신청";
			}

			$script = "
				<script>
				alert('{$title}');
				</script>
			";
			echo $script;

		}elseif( ($socialcp_return_use > 0 && $_POST['mode']=='exchange' ) || ($socialcp_return_use == count($_POST['chk_seq'])) ) {

			if($_POST['mode']=='exchange'){
				$title="쿠폰상품은 맞교환이 [불가능]합니다.";
				$logTitle = "맞교환신청";
			}else{
				$title="쿠폰상품은 반품신청이 [불가능] 합니다.";
				$logTitle = "반품신청";
			}

			$script = "
				<script>
				alert('{$title}');
				</script>
			";
			echo $script;

		}else{

			$return_code = $this->returnmodel->insert_return($insert_data,$items);

			if($_POST['mode']=='exchange'){
				$title="맞교환 신청이 완료되었습니다.";
				$logTitle = "맞교환신청";
				$logDetail = "주문자가 맞교환신청을 하였습니다.";
			}else{
				$title="반품 신청이 완료되었습니다.";
				$logTitle = "반품신청";
				$logDetail = "주문자가 반품신청을 하였습니다.";
			}

			if($cancel_type){//1개이상 청약철회상품
				$logDetail .= ", ".number_format($cancel_type)."건의 청약철회 상품은 제외되었습니다.";
				$title .= "<br/> ".number_format($cancel_type)."건의 청약철회 상품은 제외되었습니다.";
			}

			if($socialcp_return_use){//1개이상 쿠폰상품
				$logDetail .= ", ".number_format($socialcp_return_use)."건의 쿠폰상품은 제외되었습니다.";
				$title .= "<br/> ".number_format($socialcp_return_use)."건의 쿠폰상품은 제외되었습니다.";
			}


			$logParams	= array('return_code' => $return_code);
			$this->ordermodel->set_log($_POST['order_seq'],'process','주문자',$logTitle,$logDetail,$logParams);

			$_POST['shipping_price_bank_account']	= str_replace("'",'',$_POST['shipping_price_bank_account']);
			$_POST['shipping_price_depositor']		= str_replace("'",'',$_POST['shipping_price_depositor']);

			if($_POST['use_layout']){
				$script = "
					<script>
						alert('{$title}');
						parent.document.location.replace('../mypage/order_view?no={$_POST['order_seq']}');
					</script>
				";
			}else if($this->mobileMode){
				$script = "
					<script>
					alert('{$title}');
					parent.document.location.reload();
					</script>
				";
			}else{
				$script = "
				<script>
				parent.$('#order_return_msg .shipping_price_bank_account').html('".$_POST['shipping_price_bank_account']."');
				parent.$('#order_return_msg .shipping_price_depositor').html('".$_POST['shipping_price_depositor']."');
				parent.closeDialog('order_refund_layer');
				parent.openDialog('{$title}', 'order_return_msg', {width:340});
				</script>
				";
			}
			echo $script;
		}
	}

	//쿠폰상품 반품 or 맞교환 -> 환불
	public function order_return_coupon(){
		//error_reporting(E_ALL);//0 E_ALL 
		//$this->output->enable_profiler(TRUE);
		$this->load->model('returnmodel');
		$this->load->model('exportmodel');
		$this->load->helper('order');

		$cfg_order = config_load('order');

		if(!$_POST['chk_seq']){
			openDialogAlert("환불 신청할 상품을 선택해주세요.",400,140,'parent');
			exit;
		}

		/*
		if(is_array($_POST['phone'])) $this->validation->set_rules('phone[]', '연락처','trim|required|numeric|max_length[4]|xss_clean');
		else  $this->validation->set_rules('phone', '연락처','trim|required|max_length[14]|xss_clean');
		*/

		if(is_array($_POST['cellphone'])) $this->validation->set_rules('cellphone[]', '휴대폰','trim|required|numeric|max_length[4]|xss_clean');
		else $this->validation->set_rules('cellphone', '휴대폰','trim|required|max_length[14]|xss_clean');

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
		$data_order_items 		= $this->ordermodel->get_item($_POST['order_seq']);
		if( !in_array($data_order['step'],array('40','45','50','55','60','65','70','75')) ){
			openDialogAlert("[쇼셜쿠폰] ".$this->arr_step[$data_order['step']]."에서는 환불신청을 하실 수 없습니다.",400,140,'parent');
			exit;
		}

		foreach ($_POST['chk_ea'] as $k => $chk_ea ){
			if($chk_ea == 0){
			openDialogAlert("환불 수량을 0건으로 입력한 경우에는 신청되지 않습니다.",400,140,'parent');
			exit;
			}
		}

		/* 신용카드 자동취소 전체취소 start */
		if( $data_order['payment']=='card' && $data_order['settleprice']==$_POST['cancel_total_price'])
		{ 
			$pgCompany = $this->config_system['pgCompany'];
			/* PG */
			$cancelFunction = "{$pgCompany}_cancel";
			$cancelResult = $this->refundmodel->$cancelFunction($data_order,array('refund_reason'=>$_POST['reason_detail'],'cancel_type'=>'full'));

			if(!$cancelResult['success']){
				openDialogAlert("{$pgCompany} 결제 취소 실패<br /><font color=red>{$cancelResult['result_code']} : {$cancelResult['result_msg']}</font>",400,160,'parent','');
				exit;
			}
			$_POST['cancel_type'] = 'full';
		}
		/* 신용카드 자동취소 전체취소 end */

		$export_codes = array();
		foreach($_POST['chk_export_code'] as $k => $chk_export_code) {

			$cancelquery = "select * from fm_order_item where item_seq=?";
			$cancelquery = $this->db->query($cancelquery,array($_POST['chk_item_seq'][$k]));
			$orditemData = $cancelquery->row_array();

			if($_POST['chk_option_seq'][$k] && !$_POST['chk_suboption_seq'][$k]) {

				//쇼셜쿠폰상품의 취소(환불) 가능여부::환불
				if ( $orditemData['goods_kind'] == 'coupon') {
					$query = "select * from fm_order_item_option where item_option_seq=?";
					$query = $this->db->query($query,array($_POST['chk_option_seq'][$k])); 
					$optionData = $query->row_array(); 

					$export_itemquery = "select * from fm_goods_export_item where export_code=? limit 1";
					$export_itemquery = $this->db->query($export_itemquery,array($_POST['chk_export_code'][$k]));
					$export_item_Data = $export_itemquery->row_array();

					if( date("Ymd")>substr(str_replace("-","",$optionData['social_end_date']),0,8) ) {//유효기간 종료 후 적립금환불 신청가능여부

						if( $orditemData['socialcp_use_return'] == 1 ) {//미사용쿠폰 환불대상
							if( order_socialcp_cancel_return($orditemData['socialcp_use_return'], $export_item_Data['coupon_value'], $export_item_Data['coupon_remain_value'], $optionData['social_start_date'], $optionData['social_end_date'] , $orditemData['socialcp_use_emoney_day'] ) === true ) {//미사용쿠폰여부 잔여값어치합계
								if(!in_array($chk_export_code,$export_codes)) $export_codes[] = $chk_export_code;
							}
						}
					}else{//유효기간 이전
						if( $export_item_Data['coupon_remain_value'] >0 ) {//잔여값어치가 남아있을때에만..
							if( $export_item_Data['coupon_value'] != $export_item_Data['coupon_remain_value']  && $orditemData['socialcp_cancel_use_refund'] == '1' ) {
								//부분 사용한 쿠폰은 취소(환불) 불가 @2014-10-07
								continue;
							}else{
								list($data['socialcp_refund_use'], $data['socialcp_refund_cancel_percent']) = order_socialcp_cancel_refund(
									$_POST['order_seq'],
									$orditemData['item_seq'],
									$data_order['deposit_date'],
									$optionData['social_start_date'],
										$optionData['social_end_date'],
										$orditemData['socialcp_cancel_payoption'],
										$orditemData['socialcp_cancel_payoption_percent']
								);//취소(환불) 가능여부

								if( $data['socialcp_refund_use'] === true ) {//취소(환불) 100% 또는 XX% 공제
									if(!in_array($chk_export_code,$export_codes)) $export_codes[] = $chk_export_code;
								}
							}
						}
					}
				}
			}
		}

		$shipping_seq = null;
		foreach($_POST['chk_shipping_seq'] as $k => $chk_shipping_seq){
			if($shipping_seq === null) $shipping_seq = $chk_shipping_seq;

			if($shipping_seq != $chk_shipping_seq){
				openDialogAlert("배송지가 서로 다른 상품을 함께 환불신청 할 수 없습니다.<br />따로 환불신청을 해주세요.",400,150,'parent');
				exit;
			}
		} 

		// 환불 등록
		if($_POST['bank']){
			$tmp = code_load('bankCode',$_POST['bank']);
			$bank = $tmp[0]['value'];
		}

		$account = is_array($_POST['account']) ? implode('-',$_POST['account']) : $_POST['account'];

		$_POST['refund_method'] = ($_POST['refund_method'])?$_POST['refund_method']:'bank';

		$items = array();

		$cancel_type=0;//청약철회상품체크
		$socialcp_return_use=0;//쇼셜쿠폰상품
		$realitems 		= $this->ordermodel->get_item($_POST['order_seq']);
		//주문상품의 실제 1건당 금액계산
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

		foreach($_POST['chk_seq'] as $k=>$v) {
			$cancelquery = "select * from fm_order_item where item_seq=?";
			$cancelquery = $this->db->query($cancelquery,array($_POST['chk_item_seq'][$k]));
			$orditemData = $cancelquery->row_array();

			//청약철회상품체크
			$goodscanceltype = $this->goodsmodel->get_goods($orditemData['goods_seq']);
			if( $goodscanceltype['cancel_type']) {//청약철회상품 반품불가
				$cancel_type++;
				continue;
			}

			$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
			$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
			$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
			$items[$k]['ea']			= 1;//$_POST['chk_ea'][$k];

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
					$coupon_remain_real_percent = $coupon_remain_real_price = $coupon_remain_price = $coupon_deduction_price = 0; 

					$socialcoupon++;

					if( date("Ymd")>substr(str_replace("-","",$optionData['social_end_date']),0,8)) {//유효기간 종료 후 구매금액 % 환불 @2014-10-07 

						if( $export_item_Data['coupon_value'] == $export_item_Data['coupon_remain_value'] ) {//값어치 전체미사용
							$socialcp_status = '8';
						}else{//값어치 일부사용
							$socialcp_status = '9';
						}

						if( $orditemData['socialcp_use_return'] == 1 ) {//미사용쿠폰 환불대상

							if( order_socialcp_cancel_return($orditemData['socialcp_use_return'], $export_item_Data['coupon_value'], $export_item_Data['coupon_remain_value'], $optionData['social_start_date'], $optionData['social_end_date'] , $orditemData['socialcp_use_emoney_day'] ) === true ) {//미사용쿠폰여부 구매금액 % 환불 @2014-10-07 
								$items[$k]['coupon_refund_type']		= 'price';
								if ( $orditemData['socialcp_input_type'] == 'price' ) {//금액
									$coupon_remain_price_tmp			= (int) $export_item_Data['coupon_remain_value'];
									$coupon_deduction_price_tmp	= (int) $export_item_Data['coupon_value'];
								}else{//횟수
									$coupon_remain_price_tmp			= (int) (100 * ($optionData['coupon_input_one'] * $export_item_Data['coupon_remain_value']) / 100);
									$coupon_deduction_price_tmp	= (int) ($optionData['coupon_input_one'] * $export_item_Data['coupon_value']);
								}
								$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율 

								$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);

								$coupon_remain_price			= (int) ($orditemData['socialcp_use_emoney_percent'] * ($coupon_remain_real_price) / 100);
								$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price; 
								//$cancel_total_price  += $coupon_remain_price;//취소총금액
								$coupon_refund_emoney		= $coupon_remain_price;//이전스킨적용

								$coupon_valid_over++;//유효기가긴지난
							}else{//불가
								$socialcp_return_use++;
								unset($items[$k]);
								continue;
							}

						}else{//불가
							$socialcp_return_use++;
							unset($items[$k]);
							continue;
						}
					}else{//유효기간 이전

						if( $export_item_Data['coupon_value'] == $export_item_Data['coupon_remain_value'] ) {//값어치 전체미사용
							$socialcp_status = '6';
						}else{//값어치 일부사용
							$socialcp_status = '7';
						}

						if( $export_item_Data['coupon_remain_value'] >0 ) {//구매금액 % 환불 @2014-10-07
							if( $export_item_Data['coupon_value'] != $export_item_Data['coupon_remain_value']    && $orditemData['socialcp_cancel_use_refund'] == '1' ) {
								//부분 사용한 쿠폰은 취소(환불) 불가 @2014-10-07 
								$socialcp_return_use++;
								unset($items[$k]);
								continue;
							}else{
								list($export_item_Data['socialcp_refund_use'], $export_item_Data['socialcp_refund_cancel_percent']) = order_socialcp_cancel_refund(
								$data_order['order_seq'],
								$_POST['chk_item_seq'][$k],
								$data_order['deposit_date'],
								$optionData['social_start_date'],
									$optionData['social_end_date'],
									$orditemData['socialcp_cancel_payoption'],
									$orditemData['socialcp_cancel_payoption_percent']
							);

								if( $export_item_Data['socialcp_refund_use'] === true ) {//취소(환불)
									if( $export_item_Data['coupon_value'] == $export_item_Data['coupon_remain_value'] ){//전체체크 미사용 
										$coupon_remain_price			= (int) ($export_item_Data['socialcp_refund_cancel_percent'] * $coupon_real_total_price / 100);
										$coupon_deduction_price	= (int) $coupon_real_total_price - $coupon_remain_price; 
										$coupon_remain_real_percent = "100";
										$coupon_remain_real_price = $coupon_real_total_price; 
										$cancel_total_price  += $coupon_remain_price;//취소총금액
								}else{
									if ( $orditemData['socialcp_input_type'] == 'price' ) {//금액
										$coupon_remain_price_tmp			= (int) $export_item_Data['coupon_remain_value'];
										$coupon_deduction_price_tmp	= (int) $export_item_Data['coupon_value'];
									}else{//횟수
											$coupon_remain_price_tmp			= (int) (100 * ($optionData['coupon_input_one'] * $export_item_Data['coupon_remain_value']) / 100);
										$coupon_deduction_price_tmp	= (int) ($optionData['coupon_input_one'] * $export_item_Data['coupon_value']);
									}
									$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율 
									$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);

									$coupon_remain_price			= (int) ($export_item_Data['socialcp_refund_cancel_percent'] * ($coupon_remain_real_price) / 100);
									$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price;
									//$cancel_total_price  += $coupon_remain_price;//취소총금액
								}

								$items[$k]['coupon_refund_type']		= 'price';
							}else{//불가
								$socialcp_return_use++;
								unset($items[$k]);
								continue;
							}
							}
						}else{
							$socialcp_return_use++;
							unset($items[$k]);
							continue;
						}
					}

					$cancel_memo = socialcp_cancel_memo($export_item_Data, $coupon_remain_real_percent, $coupon_real_total_price, $coupon_remain_real_price, $coupon_remain_price, $coupon_deduction_price);
					//debug_var($coupon_remain_price.' , '.$coupon_deduction_price.' , '.$coupon_refund_emoney);

					$items[$k]['coupon_refund_emoney']		= $coupon_refund_emoney;//쿠폰 잔여 값어치의 실제금액
					$items[$k]['coupon_remain_price']			= $coupon_remain_price;//쿠폰 결제금액의 실제금액
					$items[$k]['coupon_deduction_price']		= $coupon_deduction_price;//쿠폰 결제금액의 공제금액
					$items[$k]['cancel_memo']						= $cancel_memo;//취소(환불) 상세내역
				}
				//debug_var($items[$k]);exit;

				if($_POST['mode']!='exchange'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_option_seq',$items[$k]['option_seq']);
					$this->db->update('fm_order_item_option');
				}
			}else if($items[$k]['suboption_seq']){
				$mode = 'suboption';

				$query = "select * from fm_order_item_suboption where item_suboption_seq=?";
				$query = $this->db->query($query,array($items[$k]['suboption_seq']));
				$suboptionData = $query->row_array();

				if($_POST['mode']!='exchange'){
					$this->db->set('refund_ea','refund_ea+'.$items[$k]['ea'],false);
					$this->db->where('item_suboption_seq',$items[$k]['suboption_seq']);
					$this->db->update('fm_order_item_suboption');
				}
			}
		}

		//$_POST['refund_method'] = ($coupon_valid_over)?'emoney':$_POST['refund_method'];

		if($_POST['mode']=='exchange'){//맞교환
			$refund_code = '0';
			$return_type = 'exchange';
		}else{//반품
			if( !( ( $cancel_type == count($_POST['chk_seq']) ) || ( $socialcp_return_use == count($_POST['chk_seq']) ) ) ) {
				
				$data = array(
					'order_seq' => $_POST['order_seq'],
					'bank_name' => $bank,
					'bank_depositor' => $_POST['depositor'],
					'coupon_refund_emoney' => $coupon_refund_emoney,
					'coupon_refund_price' => $coupon_remain_price,
					'bank_account' => $account,
					'refund_reason' => '반품환불',
					'refund_type' => 'return',
					'regist_date' => date('Y-m-d H:i:s'),
					'refund_method' => $_POST['refund_method']
				);
				//debug_var($data);
				//debug_var($items);
				$refund_code = $this->refundmodel->insert_refund($data,$items);
				$return_type = 'return';

				$logTitle	= "환불신청";
				$logDetail	= "주문자 반품신청에 의한 환불신청이 접수되었습니다.";
				$logParams	= array('refund_code' => $refund_code);
				$this->ordermodel->set_log($_POST['order_seq'],'process','주문자',$logTitle,$logDetail,$logParams);
			}
		}//환불타입(cancel_payment:결제취소,return:반품환불)


		if( $cancel_type == count($_POST['chk_seq']) ) {

			if($_POST['mode']=='exchange'){
				$title="청약철회 상품으로 맞교환이 [불가능]합니다.";
				$logTitle = "맞교환신청";
			}else{
				$title="청약철회 상품으로 반품신청이 [불가능] 합니다.";
				$logTitle = "반품신청";
			}

			$script = "
				<script>
				alert('{$title}');
				</script>
			";
			echo $script;
		}elseif( ($socialcp_return_use > 0 && $_POST['mode']=='exchange' ) || ( $socialcp_return_use == count($_POST['chk_seq']) ) ) {

			if($_POST['mode']=='exchange'){
				$title="쿠폰상품은 맞교환이 [불가능]합니다.";
				$logTitle = "맞교환신청";
			}else{
				$title="쿠폰상품은 반품신청이 [불가능] 합니다.";
				$logTitle = "반품신청";
			}

			$script = "
				<script>
				alert('{$title}');
				</script>
			";
			echo $script;

		}else{
			if(is_array($_POST['phone']))  $phone = implode('-',$_POST['phone']);
			else  $phone = $_POST['phone'];

			if(is_array($_POST['cellphone'])) $cellphone = implode('-',$_POST['cellphone']);
			else  $cellphone = $_POST['cellphone'];

			$zipcode = "";
			if($_POST['return_recipient_zipcode'][1]) $zipcode = implode('-',$_POST['return_recipient_zipcode']);

			//쿠폰상품 반품등록
			$insert_data['status'] 				= 'complete';// 쿠폰상품은 반품완료처리'request';
			$insert_data['order_seq'] 		= $_POST['order_seq'];
			$insert_data['refund_code'] 	= $refund_code;
			$insert_data['return_type'] 	= $return_type;
			$insert_data['return_reason'] 	= $_POST['reason_detail'];
			$insert_data['cellphone'] 		= $cellphone;
			$insert_data['phone'] 			= (!empty($phone)) ? $phone : '';
			$insert_data['return_method'] 	= $_POST['return_method'];
			$insert_data['sender_zipcode'] 	= $zipcode;
			$insert_data['sender_address_type']		= (($_POST['return_recipient_address_type']))?$_POST['return_recipient_address_type']:"zibun";
			$insert_data['sender_address'] 	= $_POST['return_recipient_address']?$_POST['return_recipient_address']:'';
			$insert_data['sender_address_street']	= $_POST['return_recipient_address_street'];
			$insert_data['sender_address_detail'] = $_POST['return_recipient_address_detail']?$_POST['return_recipient_address_detail']:'';
			$insert_data['regist_date'] 	= date('Y-m-d H:i:s');
			$insert_data['return_date'] = date('Y-m-d H:i:s');
			$insert_data['important'] 		= 0;
			$insert_data['shipping_price_depositor'] 	= $_POST['shipping_price_depositor'];
			$insert_data['shipping_price_bank_account'] = $_POST['shipping_price_bank_account'];

			$items = array();
			foreach($_POST['chk_seq'] as $k=>$v){
				$query = "select * from fm_order_item where item_seq=?";
				$query = $this->db->query($query,array($_POST['chk_item_seq'][$k]));
				$orditemData = $query->row_array();

				//청약철회상품체크
				$goodscanceltype = $this->goodsmodel->get_goods($orditemData['goods_seq']);
				if( $goodscanceltype['cancel_type']) {//청약철회상품 반품불가
					continue;
				}

				if($_POST['chk_option_seq'][$k] && !$_POST['chk_suboption_seq'][$k]) {

					//쇼셜쿠폰상품의 취소(환불) 가능여부
					if ( $orditemData['goods_kind'] == 'coupon') {
						$query = "select * from fm_order_item_option where item_option_seq=?";
						$query = $this->db->query($query,array($_POST['chk_option_seq'][$k]));
						$optionData = $query->row_array();

						$export_itemquery = "select * from fm_goods_export_item where export_code=? limit 1";
						$export_itemquery = $this->db->query($export_itemquery,array($_POST['chk_export_code'][$k]));
						$export_item_Data = $export_itemquery->row_array();

						if( date("Ymd")>substr(str_replace("-","",$optionData['social_end_date']),0,8)) {//유효기간 종료 후 잔여값어치합계 적립금

							if( $orditemData['socialcp_use_return'] == 1 ) {//미사용쿠폰 환불대상
								if( order_socialcp_cancel_return($orditemData['socialcp_use_return'], $export_item_Data['coupon_value'], $export_item_Data['coupon_remain_value'], $optionData['social_start_date'], $optionData['social_end_date'] , $orditemData['socialcp_use_emoney_day'] ) === false ) {//미사용쿠폰여부 잔여값어치합계
									continue;
								}
							}else{//불가
								continue;
							}
						}else{//유효기간 이전
							if( $export_item_Data['coupon_remain_value'] >0 ) {//잔여값어치가 남아있을때에만..
									if( $export_item_Data['coupon_value'] != $export_item_Data['coupon_remain_value'] && $orditemData['socialcp_cancel_use_refund'] == '1' ) {
											//부분 사용한 쿠폰은 취소(환불) 불가 @2014-10-07 
											continue;
									}else{
								list($optionData['socialcp_refund_use'], $optionData['socialcp_refund_cancel_percent']) = order_socialcp_cancel_refund(
									$orditemData['order_seq'],
									$_POST['chk_item_seq'][$k],
									$data_order['deposit_date'],
									$optionData['social_start_date'],
											$optionData['social_end_date'],
											$orditemData['socialcp_cancel_payoption'],
											$orditemData['socialcp_cancel_payoption_percent']
								);

								if( $optionData['socialcp_refund_use'] === false ) {//취소(환불)
									continue;
								}
									}
							}else{
								continue;
							}
						}
					}
				}

				$items[$k]['item_seq']		= $_POST['chk_item_seq'][$k];
				$items[$k]['option_seq']	= $_POST['chk_suboption_seq'][$k] ? '' : $_POST['chk_option_seq'][$k];
				$items[$k]['suboption_seq']	= $_POST['chk_suboption_seq'][$k];
				$items[$k]['ea']			= $_POST['chk_ea'][$k];
				$items[$k]['reason_code']	= $_POST['reason'][$k];
				$items[$k]['reason_desc']	= $_POST['reason_desc'][$k];
				$items[$k]['export_code']	= $_POST['chk_export_code'][$k];

			}

			$return_code = $this->returnmodel->insert_return($insert_data,$items);
			
			$this->load->model('socialcpconfirmmodel');
			foreach($export_codes as $export_code){
				$data_export = $this->exportmodel->get_export($export_code);
				if(in_array($data_export['status'],array('40','45','50','55','60','65','70','75'))){ 
					unset($data_socialcp_confirm);
					$data_socialcp_confirm['order_seq'] = $data_export['order_seq'];
					$data_socialcp_confirm['export_seq'] = $data_export['export_seq'];
					if($this->userInfo['member_seq']){
						$data_socialcp_confirm['member_seq'] = $this->userInfo['member_seq'];
					}else{
						$data_socialcp_confirm['doer'] = '구매자';
					} 
					$this->socialcpconfirmmodel->socialcp_confirm('user',$socialcp_status,$export_code);//socialcp_status = 환불시 상태 6,7,8,9
					$this->socialcpconfirmmodel->log_socialcp_confirm($data_socialcp_confirm);

					// 배송완료(수령확인)처리
					$this->exportmodel->socialcp_exec_complete_delivery($export_code, true, $coupon_remain_real_percent, "", "cancel");
				}
			}
			
			/* 신용카드 자동취소 */
			if($data_order['payment']=='card' && $data_order['settleprice']==$cancel_total_price) {
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
					
					/**
					* 쿠폰상품 신용카드 자동취소 end
					**/
			}else{
				if($_POST['mode']=='exchange'){
					$title="맞교환 신청이 완료되었습니다.";
					$logTitle = "맞교환신청";
					$logDetail = "주문자가 맞교환신청을 하였습니다.";
				}else{
					$title="반품이 완료되었습니다.";
					$logTitle = "반품완료";
					$logDetail = "주문자가 반품완료을 하였습니다.";
				}

				if($cancel_type){//1개이상 청약철회상품
					$logDetail .= ", ".number_format($cancel_type)."건의 청약철회 상품은 제외되었습니다.";
					$title .= "<br/> ".number_format($cancel_type)."건의 청약철회 상품은 제외되었습니다.";
				}

				if($socialcp_return_use){//1개이상 쿠폰상품
					$logDetail .= ", ".number_format($socialcp_return_use)."건의 쿠폰상품은 제외되었습니다.";
					$title .= "<br/> ".number_format($socialcp_return_use)."건의 쿠폰상품은 제외되었습니다.";
				}


				$logParams	= array('return_code' => $return_code);
				$this->ordermodel->set_log($_POST['order_seq'],'process','주문자',$logTitle,$logDetail,$logParams);

				$_POST['shipping_price_bank_account']	= str_replace("'",'',$_POST['shipping_price_bank_account']);
				$_POST['shipping_price_depositor']		= str_replace("'",'',$_POST['shipping_price_depositor']);
			}

			if($_POST['use_layout']){
				$script = "
					<script>
						alert('{$title}');
						parent.document.location.replace('../mypage/order_view?no={$_POST['order_seq']}');
					</script>
				";
			}else if($this->mobileMode){
				$script = "
					<script>
					alert('{$title}');
					parent.document.location.reload();
					</script>
				";
			}else{
				$script = "
				<script>
				parent.$('#order_return_msg .shipping_price_bank_account').html('".$_POST['shipping_price_bank_account']."');
				parent.$('#order_return_msg .shipping_price_depositor').html('".$_POST['shipping_price_depositor']."');
				parent.closeDialog('order_refund_layer');
				parent.openDialog('{$title}', 'order_return_msg', {width:340});
				</script>
				";
			}
			echo $script;
		}
	}


	public function order_pg_info()
	{

		$pg = config_load($this->config_system['pgCompany']);
		$order 			= $this->ordermodel->get_order($_POST['order_seq']);
		if( $this->config_system['pgCompany'] == 'lg' ) {
			$tax_bank	= 'cr';
			if ($order['payment'] == 'bank')
			{
				$tax_bank	= 'cr';
			}
			else if ($order['payment'] == 'virtual')
			{
				$tax_bank	= 'cas';
			}else if ($order['payment'] == 'account')
			{
				$tax_bank	= 'cr';
			}

			$authdata	= md5($pg['mallCode'] . $order['pg_transaction_number'] . $pg['merchantKey']);
			$cst_platform	= 'service';//'service';
			$return = array('result'=>true,'tax_bank'=>$tax_bank,'authdata'=>$authdata,'cst_platform'=>$cst_platform);

			echo json_encode($return);
			exit;
		}else{
			$return = array('result'=>true);
			echo json_encode($return);
			exit;
		}
	}

	public function order_tax_info()
	{
		$this->load->model('salesmodel');
		$order_seq 	= $_POST['order_seq'];
		$sc['whereis']	= ' and typereceipt = 1 and order_seq="'.$order_seq.'" ';
		$sc['select']		= ' * ';
		$taxitems 		= $this->salesmodel->get_data($sc);

		if($taxitems){

			if($taxitems['tstep']=='1')
			{
				$cash_msg = "발급신청";
			}
			else if($taxitems['tstep']=='2')
			{
				$cash_msg = "발급완료";
			} else if($taxitems['tstep']=='3')
			{
				$cash_msg = "발급취소";
			} else if($taxitems['tstep']=='4')
			{
				$cash_msg = "발급실패";
			}

			$co_name		= $taxitems['co_name'];
			$co_status		= $taxitems['co_status'];
			$co_type			= $taxitems['co_type'];
			$busi_no			= $taxitems['busi_no'];
			$address			= '['.$taxitems['zipcode'].'] '.$taxitems['address'];
			$price				= $taxitems['price'];
			$tax_supplylay= round($price/1.1);
			$tax_surtaxlay= round($price*1/11);

			$return = array('result'=>true,'co_name'=>$co_name,'co_status'=>$co_status,'co_type'=>$co_type,'busi_no'=>$busi_no,'address'=>$address,'price'=>number_format($price),'tax_supplylay'=>number_format($tax_supplylay),'tax_surtaxlay'=>number_format($tax_surtaxlay),'tax_tstep'=>$cash_msg);
		}else{
			$return = array('result'=>false);
		}
		echo json_encode($return);
		exit;
	}

	public function order_auth()
	{
		$this->load->model('ssl');
		$this->ssl->decode();

		### Validation
		$this->validation->set_rules('order_seq', '주문번호','trim|required|xss_clean');
		$this->validation->set_rules('order_email', '주문 메일','trim|required|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		### QUERY
		$where_arr = array('order_seq'=>$_POST['order_seq'], 'order_email'=>$_POST['order_email']);
		$data = get_data('fm_order', $where_arr);

		if( (strstr(urldecode($_POST['return_url']),"/board/write") || strstr(urldecode($_POST['return_url']),"goods/review_write") || strstr(urldecode($_POST['return_url']),"/mypage/mygdreview_write") ) && $data) {
			$goods_seq = @explode("goodsseq=",urldecode($_POST['return_url']));
			$itemwhere_arr = array('order_seq'=>$_POST['order_seq'], 'goods_seq'=>$goods_seq[1]);
			$itemdata = get_data('fm_order_item', $itemwhere_arr);
			if(!$itemdata) unset($data);
		}
		if(!$data){
			$callback = "if(parent.document.getElementsByName('order_seq')[0]) parent.document.getElementsByName('order_seq')[0].focus();";
			openDialogAlert("일치하는 주문 정보가 없습니다.",400,140,'parent',$callback);
			exit;
		}

		### SESSION
		$this->session->set_userdata(array('sess_order'=>$data[0]['order_seq']));
		if(strstr(urldecode($_POST['return_url']),"/board/write") || strstr(urldecode($_POST['return_url']),"goods/review_write") || strstr(urldecode($_POST['return_url']),"/mypage/mygdreview_write") ) {
			echo js("parent.opener.gdordersearch();parent.self.close();");//상품후기 >> 비회원 주문검색시 새창접근
		}else{
			### PAGE MOVE
			pageRedirect('/mypage/order_view','','parent');
		}




	}

	public function cancel(){
		$order_seq = $_GET['order_seq'];
		$this->load->model('ordermodel');
		$orders		= $this->ordermodel->get_order($order_seq);
		if( $orders['member_seq'] != $this->userInfo['member_seq']  ){
			openDialogAlert("자신의 주문만 주문무효를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		if( !in_array($orders['step'],$this->ordermodel->able_step_action['cancel_order']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 주문무효를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		/* 프로모션환원 */
		$this->load->model('couponmodel');
		$this->load->model('promotionmodel');

		$options	= $this->ordermodel->get_item_option($order_seq);
		$suboptions	= $this->ordermodel->get_item_suboption($order_seq);
		$r_reservation_goods_seq = array();

		if($options) foreach($options as $k => $option){

			//청약철회상품체크
			$goods = $this->goodsmodel->get_goods($option['goods_seq']);
			if($goods['cancel_type'] && !in_array($option['step'],$this->ordermodel->able_step_action['canceltype_cancel_order']) ) {
				$cancel_type_cnt++;
				continue;//청약철회상품 주문취소불가
			}

			$tot_ea		+= $option['ea'];

			// 출고량 업데이트를 위한 변수정의
			if(!in_array($option['goods_seq'],$r_reservation_goods_seq)){
				$r_reservation_goods_seq[] = $option['goods_seq'];
			}


			//상품별 쿠폰/프로모션코드 복원
			if($option['download_seq'] && $option['coupon_sale']) $goodscoupon = $this->couponmodel->restore_used_coupon($option['download_seq']);
			if($option['promotion_code_seq'] && $option['promotion_code_sale']) $goodspromotioncode = $this->promotionmodel->restore_used_promotion($option['promotion_code_seq']);
		}
		if($suboptions) foreach($suboptions as $k => $option){

			//청약철회상품체크
			$goods = $this->goodsmodel->get_goods($option['goods_seq']);
			if($goods['cancel_type']  && !in_array($option['step'],$this->ordermodel->able_step_action['canceltype_cancel_order']) ) {
				$cancel_type_cnt++;
				continue;//청약철회상품 주문취소불가
			}

			$tot_ea		+= $option['ea'];
		}

		if($cancel_type_cnt && $cancel_type_cnt == (count($options)+count($options))  ) {//청약철회상품있으면서 주문상품수와 동일한 경우
			openDialogAlert("[청약철회불가]상품으로 주문무효가 불가능합니다.",400,140,'parent',"parent.location.reload();");
		}else{
			///청약철회상품없는경우
			$this->ordermodel->set_step($order_seq,95);
			/* 배송비쿠폰 복원*/
			if($orders['download_seq']){
				$shippingcoupon = $this->couponmodel->restore_used_coupon($orders['download_seq']);
			}

			/* 배송비프로모션코드 복원 개별코드만 */
			if( $orders['shipping_promotion_code_seq'] ){
				$shippingpromotioncode = $this->promotionmodel->restore_used_promotion($orders['shipping_promotion_code_seq']);
			}



			$this->load->model('membermodel');
			/* 적립금 환원 */
			if($orders['emoney_use']=='use' && $orders['emoney'])
			{
				$params = array(
					'gb'		=> 'plus',
					'type'		=> 'cancel',
					'emoney'	=> $orders['emoney'],
					'ordno'	=> $order_seq,
					'memo'		=> "[복원]주문무효({$order_seq})에 의한 적립금 환원",
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

			// 출고예약량 업데이트
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}

			$log = "-";
			$caccel_arr = array(
				'ea'	=> $tot_ea,
				'price'	=> $orders['settleprice']
			);
			$this->ordermodel->set_log($order_seq,'cancel','주문자','주문무효',$log,$caccel_arr);
			openDialogAlert("[청약철회불가] 상품을 제외한 주문무효가 완료되었습니다.",400,140,'parent',"parent.location.reload();");
		}
	}

	public function recipient(){
		$this->load->model('ordermodel');
		$this->load->helper('shipping');
		$order_seq 		= $_POST['order_seq'];
		$shipping_seq 	= $_POST['shipping_seq'];

		if(!$this->userInfo['member_seq']){//비회원주문확인 후 배송지 정보저장시@2013-12-06
			$order_seq = $this->session->userdata('sess_order');
			if(!$order_seq) {
				redirect("/member/login?order_auth=1");
				exit;
			}
			$orders 			= $this->ordermodel->get_order($order_seq);
			$order_shippings = $this->ordermodel->get_shipping($order_seq,$shipping_seq);
			$international = $orders['international'];

		}else{
			$orders		= $this->ordermodel->get_order($order_seq);
			$order_shippings = $this->ordermodel->get_shipping($order_seq,$shipping_seq);
			$international = $orders['international'];

			if( $orders['member_seq'] != $this->userInfo['member_seq']  ){
				openDialogAlert("자신의 주문만 정보를 변경 하실 수 없습니다.",400,140,'parent',"");
				exit;
			}
		}
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['shipping_region']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 배송정보 변경을 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

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

			if($order_shippings[0]['area_add_delivery_cost']<$newAddDeliveryCost){
				$msg = "추가 배송비가 부과되는 지역입니다. 고객센터에 문의 해 주세요.";
				$callback = "";
				openDialogAlert($msg,450,140,'parent',$callback);
				exit;
			}

			if($order_shippings[0]['area_add_delivery_cost']>$newAddDeliveryCost){
				$msg = "추가 배송비가 제외되는 지역입니다. 고객센터에 문의 해 주세요.";
				$callback = "";
				openDialogAlert($msg,450,140,'parent',$callback);
				exit;
			}
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
				$data['recipient_zipcode'] 				= $_POST['recipient_zipcode'];
				$data['recipient_address_type'] 		= (($_POST['recipient_address_type']))?$_POST['recipient_address_type']:"zibun";
				$data['recipient_address'] 				= $_POST['recipient_address'];
				$data['recipient_address_street'] 	= $_POST['recipient_address_street'];
				$data['recipient_address_detail'] 	= $_POST['recipient_address_detail'];
			}
		}

		if($international == 'international'){
			foreach($_POST as $k => $row) if($orders[$k]!=$data) $change = 1;
			if($change){
				$data['international_address'] 	= $_POST['international_address'];
				$data['international_town_city'] 	= $_POST['international_town_city'];
				$data['international_county'] 		= $_POST['international_county'];
				$data['international_postcode'] 	= $_POST['international_postcode'];
				$data['international_country'] 	= $_POST['international_country'];
			}
		}

		$data['recipient_email'] 	 = $_POST['recipient_email'];

		if($change){

			if($shipping_seq){
				$this->db->where('order_seq', $order_seq);
				$this->db->where('shipping_seq', $shipping_seq);
				$this->db->update('fm_order_shipping', $data);
			}

			if(!$shipping_seq){
				$this->db->where('order_seq', $order_seq);
				$this->db->update('fm_order_shipping', $data);
			}

			$log = "배송지 정보 변경";
			$this->ordermodel->set_log($order_seq,'process','주문자',$log,serialize($data));
			openDialogAlert("배송지 정보가 변경 되었습니다.",400,140,'parent','');
		}
	}

	public function delivery_address(){

		$this->load->model('ordermodel');
		$this->validation->set_rules('address_description', '배송지 설명','trim|required|max_length[20]|xss_clean');
		$mode = $_POST['insert_mode'];
		if( isset($_POST['international']) ){
			$this->validation->set_rules('recipient_user_name', '받는이','trim|required|max_length[20]|xss_clean');
			$this->validation->set_rules('international', '해외배송 여부','trim|required|max_length[1]|xss_clean');
			// 국내 배송일 경우
			if($_POST['international'] == 0){
				$this->validation->set_rules('recipient_zipcode[]', '우편번호','trim|required|max_length[3]|xss_clean');
				$this->validation->set_rules('recipient_address', '주소','trim|max_length[255]|required|xss_clean');
				$this->validation->set_rules('recipient_address_detail', '나머지주소','trim|max_length[255]|required|xss_clean');
				$this->validation->set_rules('recipient_phone[]', '받는이 유선전화','trim|max_length[4]|xss_clean');
				$this->validation->set_rules('recipient_cellphone[]', '받는이 핸드폰','trim|numeric|max_length[4]|required|xss_clean');
			}else if($_POST['international'] == 1){
				$this->validation->set_rules('region', '지역','trim|numeric|required|xss_clean');
				$this->validation->set_rules('international_address', '주소','trim|max_length[255]|required|xss_clean');
				$this->validation->set_rules('international_town_city', '시도','trim|max_length[45]|required|xss_clean');
				$this->validation->set_rules('international_county', '주','trim|max_length[20]|required|xss_clean');
				$this->validation->set_rules('international_postcode', '우편번호','trim|max_length[20]|required|xss_clean');
				$this->validation->set_rules('international_country', '국가','trim|max_length[45]|required|xss_clean');
				$this->validation->set_rules('international_recipient_phone[]', '받는이 유선전화','trim|max_length[10]|xss_clean');
				$this->validation->set_rules('international_recipient_cellphone[]', '받는이 핸드폰','trim|numeric|max_length[10]|xss_clean');
			}
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		if($mode == 'insert'){
		//배송지 저장 (로그인한 경우만)
		if($_POST['save_delivery_address']){
			$this->ordermodel->insert_delivery_address('insert');
		}else{
			$this->ordermodel->insert_delivery_address();
		}
		$callback = "parent.document.location.reload();";
		openDialogAlert("자주쓰는 배송지가 등록 되었습니다.",400,140,'parent',$callback);

		}elseif($mode == 'update'){
			$address_seq=$_POST['address_seq'];
			$this->ordermodel->update_delivery_address($address_seq);
			$callback = "parent.document.location.reload();";
			openDialogAlert("자주쓰는 배송지가 수정 되었습니다.",400,140,'parent',$callback);
		}

	}


	public function delete_address(){
		$addres_seq = $_GET['address_seq'];
		$this->db->delete('fm_delivery_address', 'address_seq = '.$addres_seq);
		$callback = "parent.document.location.reload();";
		openDialogAlert(" 배송지가 삭제 되었습니다.",400,140,'parent',$callback);
	}

	public function change_address(){
		$addres_seq = $_GET['address_seq'];
		$popup_seq = $_GET['popup'];
		$key = get_shop_key();
		$sql="select *,
				AES_DECRYPT(UNHEX(recipient_phone), '{$key}') as recipient_phone,
				AES_DECRYPT(UNHEX(recipient_cellphone), '{$key}') as recipient_cellphone
				from fm_delivery_address where address_seq=".$addres_seq;
		$query = $this->db->query($sql);
		$params = $query->row_array();
		$params['address_seq']='';
		$params['address_description']='-';
		$params['often']='Y';
		$params['lately']='';
		$params['regist_date']				= date('Y-m-d H:i:s');
		$this->db->insert('fm_delivery_address', $params);

		### Private Encrypt
			$cellphone = get_encrypt_qry('recipient_cellphone');
			$phone = get_encrypt_qry('recipient_phone');
			$sql = "update fm_delivery_address set  {$cellphone}, {$phone}, update_date = now() where address_seq = {$this->db->insert_id()}";
			$this->db->query($sql);
		###

		if($_GET['complete']){
			$callback="";
		}else{
		$callback = "parent.location.href = '/mypage/delivery_address?tab=1&popup=".$popup_seq."'";//$callback = "parent.delivery_address('1');";
		}
		openDialogAlert("자주쓰는 배송지에 등록 되었습니다.",400,140,'parent',$callback);
	}

	public function refund_modify(){
		$refund_code = $_POST['refund_code'];

		$data = array();
		$data['bank_name'] = $_POST['bank_name'];
		$data['bank_depositor'] = $_POST['bank_depositor'];
		$data['bank_account'] = $_POST['bank_account'];
		$data['refund_reason'] = $_POST['refund_reason'];

		$this->db->where('refund_code', $refund_code);
		$this->db->update('fm_order_refund', $data);
		openDialogAlert("환불정보가 변경 되었습니다.",400,140,'parent',"parent.document.location.replace('../mypage/refund_view?refund_code={$refund_code}')");
	}

	public function return_modify(){
		$return_code = $_POST['return_code'];

		$data = array();
		$data['return_method']			= $_POST['return_method'];
		$data['cellphone']				= implode('-',$_POST['cellphone']);
		$data['phone']					= implode('-',$_POST['phone']);
		$data['sender_zipcode']			= implode('-',$_POST['senderZipcode']);
		$data['sender_address_type']	= (($_POST['senderAddress_type']))?$_POST['senderAddress_type']:"zibun";
		$data['sender_address']				= $_POST['senderAddress'];
		$data['sender_address_street']	= $_POST['senderAddress_street'];
		$data['sender_address_detail']	= $_POST['senderAddressDetail'];
		$data['return_reason']			= $_POST['return_reason'];

		$this->db->where('return_code', $return_code);
		$this->db->update('fm_order_return', $data);
		openDialogAlert("반품정보가 변경 되었습니다.",400,140,'parent',"parent.document.location.replace('../mypage/return_view?return_code={$return_code}')");
	}

	public function buy_confirm()
	{
		$this->load->model('returnmodel');
		$export_code = str_replace('code_','',$_GET['export_code']);

		$this->load->model('exportmodel');
		$data_export_item = $this->exportmodel->get_export_item($export_code);
		if ($data_export_item[0]['goods_kind'] == 'coupon') {
			$msg = "쇼셜쿠폰상품에서는 구매확정을 하실 수 없습니다.";
			$result = array('result'=>false, 'msg'=>$msg);
			echo json_encode($result);
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
				//$data_export_item = $this->exportmodel->get_export_item($export_code);

				foreach($data_export_item as $k => $item)
				{
					//쇼셜쿠폰상품의 취소(환불) 구매확정제외
					if ( $item['goods_kind'] == 'coupon') continue;

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

					if( $tot_reserve ){
						$params_reserve['gb'] = "plus";
						$params_reserve['emoney'] 	= $tot_reserve;
						$params_reserve['memo'] 	= "[".$export_code."] 구매확정";
						$params_reserve['ordno']	= $data_order['order_seq'];
						$params_reserve['type'] 	= "order";
						$params_reserve['limit_date'] 	= get_emoney_limitdate('order');
						$this->membermodel -> emoney_insert($params_reserve, $data_order['member_seq']);
					}

					if( $tot_point ){
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
		if($this->userInfo['member_seq']){
			$data_buy_confirm['member_seq'] = $this->userInfo['member_seq'];
		}else{
			$data_buy_confirm['doer'] = '구매자';
		}
		$this->load->model('buyconfirmmodel');
		$this->buyconfirmmodel -> buy_confirm('user',$export_code);
		$this->buyconfirmmodel -> log_buy_confirm($data_buy_confirm);

		// 배송완료 처리
		if( $data_export['status'] < 75 ){
			if( in_array($data_export['status'],$this->exportmodel->able_status_action['complete_delivery']) ){
				$this->exportmodel->exec_complete_delivery($export_code);
			}else{
				$msg = $this->exportmodel->arr_step[$data_export['status']]."에서는 배송완료를 하실 수 없습니다.";
				$result = array('result'=>false, 'msg'=>$msg);
				echo json_encode($result);
				exit;
			}
		}

		$msg = "구매확정이 완료 되었습니다.";
		if( $tot_reserve > 0 && $tot_point > 0 ) {
			$msg = "구매확정 및 ";
			if($tot_reserve > 0){
				$msg .= "적립금 지급(".$tot_reserve."원)";
			}
			if($tot_point > 0){
				$msg .= ($tot_reserve > 0)?", 포인트 지급(".$tot_point."p)":"포인트 지급(".$tot_point."p)";
			}
			$msg .= "완료 되었습니다.";
		}

		if($cfg_reserve[autoemoney] == 1 ) {//적립금 자동지급사용함
			$this->load->model('Boardmanager');
			$sql['whereis']	= ' and id= "goods_review" ';
			$sql['select']		= ' * ';
			$manager = $this->Boardmanager->managerdataidck($sql);//게시판정보

			$msg .= "<span ><div style=\"margin-top:5px;margin-left:18px;\"><br/>상품평을 작성 하시면 아래와같이 추가로 지급됩니다.<br/> ";
			if($cfg_reserve[autoemoney_photo] > 0 ||  $cfg_reserve[autopoint_photo] > 0 ) {
				$msg .= "<b>포토 ".$manager['name']."</b>는";
				if($cfg_reserve[autoemoneytype] != 1 && ( $cfg_reserve[autoemoneystrcut1]>0 || $cfg_reserve[autoemoneystrcut2]>0 )) {
				$msg .= "<span >(";
				if($cfg_reserve[autoemoneytype] == 2 && $cfg_reserve[autoemoneystrcut1]>0 ){
					$msg .= number_format($cfg_reserve[autoemoneystrcut1]);
				}elseif($cfg_reserve[autoemoneytype] == 3 && $cfg_reserve[autoemoneystrcut2]>0) {
					$msg .= number_format($cfg_reserve[autoemoneystrcut2]);
				}
					$msg .= "자 이상)</span>";
				}
				if($cfg_reserve[autoemoney_photo] > 0 ){
					$msg .= "적립금 <span style=\"color:#c40000;\" >".number_format($cfg_reserve[autoemoney_photo])."</span>원, ";
				}
				if($cfg_reserve[autopoint_photo] > 0) {
					$msg .= "포인트 <span style=\"color:#c40000;\" >".number_format($cfg_reserve[autopoint_photo])."</span>P";
				}
			$msg .= "지급,";
			}
			$msg .= "<br/>";
			if($cfg_reserve[autoemoney_video] > 0 ||  $cfg_reserve[autopoint_video] > 0 ) {
				$msg .= "<b>동영상 ".$manager['name']."</b>는";
				if($cfg_reserve[autoemoneytype] != 1 && ( $cfg_reserve[autoemoneystrcut1]>0 || $cfg_reserve[autoemoneystrcut2]>0 )) {
				$msg .= "<span >(";
				if($cfg_reserve[autoemoneytype] == 2 && $cfg_reserve[autoemoneystrcut1]>0 ){
					$msg .= number_format($cfg_reserve[autoemoneystrcut1]);
				}elseif($cfg_reserve[autoemoneytype] == 3 && $cfg_reserve[autoemoneystrcut2]>0) {
					$msg .= number_format($cfg_reserve[autoemoneystrcut2]);
				}
					$msg .= "자 이상)</span>";
				}
				if($cfg_reserve[autoemoney_video] > 0 ){
					$msg .= "적립금 <span style=\"color:#c40000;\" >".number_format($cfg_reserve[autoemoney_video])."</span>원, ";
				}
				if($cfg_reserve[autopoint_video] > 0) {
					$msg .= "포인트 <span style=\"color:#c40000;\" >".number_format($cfg_reserve[autopoint_video])."</span>P";
				}
			$msg .= "지급,";
			}
			$msg .= "<br/>";
			if( $cfg_reserve[autoemoney_review] > 0 ||  $cfg_reserve[autopoint_review] > 0 ) {
			$msg .= "<b>일반 ".$manager['name']."</b>는";
			if( $cfg_reserve[autoemoneytype] != 1 && ( $cfg_reserve[autoemoneystrcut1]>0 || $cfg_reserve[autoemoneystrcut2]>0 ) ){
				$msg .= "	<span >(";
				if( $cfg_reserve[autoemoneytype] == 2 && $cfg_reserve[autoemoneystrcut1]>0 ) {
					$msg .= number_format($cfg_reserve[autoemoneystrcut1]);
				}elseif( $cfg_reserve[autoemoneytype] == 3 && $cfg_reserve[autoemoneystrcut2]>0) {
					$msg .= number_format($cfg_reserve[autoemoneystrcut2]);
				}
				$msg .= "자 이상)</span>";
			}
				if($cfg_reserve[autoemoney_review] > 0 ){
				$msg .= "적립금 <span style=\"color:#c40000;\">".number_format($cfg_reserve[autoemoney_review])."</span>원, ";
				}
				if( $cfg_reserve[autopoint_review] > 0 ) {
				$msg .= "포인트 <span style=\"color:#c40000;\">".number_format($cfg_reserve[autopoint_review])."</span>P";
				}
			}
			$msg .= "</div>";

			$msg .= "<div id='openDialogLayerBtns' align='center' style='padding-top:15px'><span class='btn medium'><input type='button' value='상품평' onclick=\"location.href='mypage/mygdreview_catalog';\" /></span></div>";
		}

		$result = array('result'=>true, 'msg'=>$msg);
		echo json_encode($result);
		exit;
		//$callback = "parent.location.reload();";
		//openDialogAlert('구매확정이 완료 되었습니다.',400,140,'parent',$callback);
	}


	public function buy_gift(){

		### Validation
		$this->validation->set_rules('recipient_address', '기본주소','trim|required|max_length[40]|xss_clean');
		$this->validation->set_rules('recipient_address_detail', '나머지주소','trim|required|max_length[40]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();
			parent.document.getElementById('btn_buy_gift').disabled = false;";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$_POST['payment']		= 'bank';
		$_POST['emoney_use']	= 'use';
		$_POST['emoney']		= $_POST['point'];

		$this->load->model('ordermodel');
		$order_seq = $this->ordermodel->insert_order(0, 0, null, $this->freeprice);
		$this->ordermodel->insert_delivery_address();
		$shipping_seq = $this->ordermodel->insert_single_shipping($order_seq, 0, 0);

		unset($gift_params);
		$gift_params['order_seq'] 	= $order_seq;
		$gift_params['goods_seq']	= $_POST['goods_seq'];
		$gift_params['image']		= get_gift_image($_POST['goods_seq'],'thumbCart');
		$gift_params['goods_name']	= get_gift_name($_POST['goods_seq']);
		$this->db->insert('fm_order_item', $gift_params);
		$item_seq = $this->db->insert_id();

		$item_insert_params = array();
		$item_insert_params['shipping_seq'] 		= $shipping_seq;
		$item_insert_params['order_seq'] 			= $order_seq;
		$item_insert_params['order_item_seq']		= $item_seq;
		$item_insert_params['goods_shipping_cost']	= 0;
		$this->db->insert('fm_order_shipping_item', $item_insert_params);
		$shipping_item_seq = $this->db->insert_id();



		unset($gift_params);
		$gift_params['order_seq'] 	= $order_seq;
		$gift_params['item_seq'] 	= $item_seq;
		$gift_params['step'] 		= "0";
		$gift_params['price'] 		= "0";
		$gift_params['ori_price'] 	= $_POST['point'];
		$gift_params['ea'] 			= "1";
		$this->db->insert('fm_order_item_option', $gift_params);
		$item_option_seq = $this->db->insert_id();


		$multi_insert_params = array();
		$multi_insert_params['shipping_seq']				= $shipping_seq;
		$multi_insert_params['shipping_item_seq']			= $shipping_item_seq;
		$multi_insert_params['order_seq']					= $order_seq;
		$multi_insert_params['ea']							= 1;
		$multi_insert_params['order_item_seq']				= $item_seq;
		$multi_insert_params['order_item_option_seq']		= $item_option_seq;
		$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);


		$result = $this->ordermodel->check_shipping_data($order_seq,true);

		$this->ordermodel->set_step($order_seq, '25');

		if($_POST['goods_rule'] == "reserve"){

			/* 적립금 사용 */
			$this->load->model('membermodel');
			$params = array(
				'gb'		=> 'minus',
				'type'		=> 'order_gift',
				'emoney'	=> $_POST['point'],
				'memo'		=> "[".$_POST["goods_name"]."] 적립금 교환 사은품",
				'ordno'		=> $order_seq
			);

			$this->membermodel->emoney_insert($params, $this->userInfo['member_seq']);
			$this->ordermodel->set_emoney_use($order_seq,'return');

		}else{
			###
			$this->load->model('membermodel');
			$iparam['gb']			= "minus";
			$iparam['type']			= 'order_gift';
			$iparam['point']		= $_POST['point'];
			$iparam['memo']			= '['.$_POST["goods_name"].'] 포인트 교환 사은품';
			$iparam['ordno']		= $order_seq;
			$this->membermodel->point_insert($iparam, $this->userInfo['member_seq']);
		}

		$callback = "parent.top.document.location.reload();";
		openDialogAlert('사은품 신청이 정상적으로 완료 되었습니다.',400,140,'parent',$callback);

	}

	public function add_delivery_address(){

		if(!$this->userInfo['member_seq']) {
			echo json_encode(array(
				'msg' => '로그인 후에 가능합니다.'
			));
			exit;
		}

		$this->load->model('ordermodel');

		$this->validation->set_rules('recipient_user_name', '받는이','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('recipient_zipcode[]', '우편번호','trim|required|max_length[3]|xss_clean');
		$this->validation->set_rules('recipient_address', '주소','trim|max_length[255]|required|xss_clean');
		$this->validation->set_rules('recipient_address_detail', '나머지주소','trim|max_length[255]|required|xss_clean');
		$this->validation->set_rules('recipient_phone[]', '받는이 유선전화','trim|max_length[4]|xss_clean');
		$this->validation->set_rules('recipient_cellphone[]', '받는이 핸드폰','trim|numeric|max_length[4]|required|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$_POST['address_description'] = $_POST['recipient_user_name'];
		$_POST['insert_mode'] = 'insert';
		$_POST['international'] = '0';

		$this->ordermodel->insert_delivery_address();

		echo json_encode(array(
			'msg' => '자주쓰는 배송지가 등록 되었습니다'
		));

	}

	public function cancel_tax(){

		$order_seq = $_POST["order_seq"];

		$sql = "UPDATE fm_sales SET tstep = 3, approach = 'unlink', up_date = '".date("Y-m-d H:i:s")."' WHERE order_seq = '{$order_seq}'";
		$result = $this->db->query($sql);
		$return = array('result'=>true, 'msg'=>"처리되었습니다.");
		echo json_encode($return);
		exit;

	}

	public function filedown(){
		$file = $_GET['file'];
		$path = ROOTPATH."data/order/".$file;
		get_file_down($path, $file);
	}

	public function point_exchagne(){

		$configReserve = ($this->reserves)?$this->reserves:config_load('reserve');
		if($configReserve['point_use'] != 'Y') {
			openDialogAlert('잘못된 접근입니다.',400,140,'parent',$callback);
			exit;
		}

		$exchange_point = $_POST['exchange_point'];
		$exchange_emoney = $_POST['exchange_emoney'];

		$this->load->model('membermodel');
		$mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보

		if($mdata['point'] < $exchange_point){
			openDialogAlert('보유한 포인트보다 더 많이 입력 하셨습니다.',400,140,'parent',$callback);
			exit;
		}

		if($configReserve['emoney_minum_point'] > $exchange_point){
			openDialogAlert('최소 교환 포인트보다 더 작게 입력 하셨습니다.',400,140,'parent',$callback);
			exit;
		}

		if($exchange_emoney < 1){
			openDialogAlert('전환될 적립금이 없습니다.',400,140,'parent',$callback);
			exit;
		}

		$mod = (int)$exchange_point % (int)$configReserve['emoney_point_rate'];

		if($mod > 0){
			$exchange_point = (int)$exchange_point - (int)$mod;
		}

		$exchange_emoney = (int)$exchange_point / (int)$configReserve['emoney_point_rate'];

		/* 적립금 지급 */
		if($exchange_emoney > 0 ){
			$params = array(
				'gb'		=> 'plus',
				'type'		=> 'exchange',
				'emoney'	=> $exchange_emoney,
				'ordno'	=> '',
				'limit_date' => get_emoney_limitdate('exchange_emoney'),
				'memo'		=> "교환포인트",
			);
			$this->membermodel->emoney_insert($params, $this->userInfo['member_seq']);

			$iparam['gb']			= "minus";
			$iparam['type']			= 'exchange';
			$iparam['point']		= $exchange_point;
			$iparam['memo']			= '적립금('.number_format($exchange_emoney).') 교환';
			$this->membermodel->point_insert($iparam, $this->userInfo['member_seq']);
			$callback = "parent.top.document.location.reload();";
			openDialogAlert('포인트 교환이 정상적으로 처리되었습니다.<br>교환 포인트 : '.$exchange_point.'P<br>지급 적립금 : '.$exchange_emoney.'원',400,150,'parent',$callback);
		}


		exit;
	}

	// 쿠폰 사용 처리
	public function usecoupon(){
		$this->exportSmsData = array();
		$this->load->model("exportmodel");
		$this->load->model("returnmodel");

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
		if	($this->mobileMode)
			$callback = "parent.window.close();";

		openDialogAlert('쿠폰 사용확인이 완료되었습니다.',400,140,'parent',$callback);
	}

	// 나의 할인쿠폰 사용 처리
	public function usemycoupon(){

		$this->load->model('membermodel');
		$this->load->model('couponmodel');

		if($_POST['manager_code']) {
				$param['provider_seq']	= 1; // 입점몰일 때만
				$param['certify_code']	= $_POST['manager_code'];
				$certify				= $this->membermodel->get_certify_manager($param);
				$manager_id				= $certify[0]['manager_id'];
				$manager_code			= $certify[0]['certify_code'];
				$manager_name			= $certify[0]['manager_name'];
				if	(!$manager_code){
					openDialogAlert("유효하지 않은 직원코드입니다.",400,140,'parent',$callback);
					exit;
				}

			// 쿠폰 사용가능 여부
			$download_seq	= trim($_POST['download_seq']);
			$coupon			= $this->couponmodel->get_download_coupon($download_seq);

			if($_POST['popup']){
				$callback = "parent.close();";
			}else{
				$callback = "parent.location.reload();";
			}

			if(!$coupon){
				openDialogAlert('유효한 쿠폰이 아닙니다.',400,140,'parent',$callback);
			}

			if($coupon['use_status'] == 'unused'){
				// 쿠폰사용 저장처리
				$this->couponmodel->set_download_use_status($download_seq,'used',$manager_name,$manager_code);
				openDialogAlert('쿠폰 사용확인이 완료되었습니다.',400,140,'parent',$callback);
			}else{
				openDialogAlert('이미 사용한 쿠폰입니다.',400,140,'parent',$callback);
			}
		}else{
			openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
			exit;
		}
	}
}

/* End of file mypage_process.php */
/* Location: ./app/controllers/mypage_process.php */