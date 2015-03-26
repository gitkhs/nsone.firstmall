<?php
class exportmodel extends CI_Model {
	public function __construct()
	{
		// 주문상태별 처리 가능한 액션 정의
		$action['complete_export'] 	= array('45'); // 출고완료
		$action['going_delivery'] = array('55'); // 배송중
		$action['complete_delivery'] = array('65','55'); // 배송완료
		$this->able_status_action = $action;

		/** 출고완료/배송완료 쇼셜쿠폰상품 확정 : 1/2 크론추가
	   * 1 : 값어치 미사용 
	   * 2 : 값어치 일부 사용
	   * 3 : 값어치 모두 사용
	   * 4 : 전체 값어치 모두 있고 환불가능기간 만료
	   * 5 : 잔여 값어치 남아 있고 환불가능기간 만료 
	   * 6 : 종료 전 전체 값어치 모두 있고 환불
	   * 7 : 종료 전 잔여 값어치 남아 있고 환불
	   * 8 : 종료 후 전체 값어치 모두 있고 환불
	   * 9 : 종료 후 잔여 값어치 남아 있고 환불
	  **/

	  $this->socialcp_status = array('1'=>array('사용대기','값어치 미사용','①'),'2'=>array('부분사용','값어치 일부 사용','②'),
		  '3'=>array('전체사용[종료]','값어치 모두 사용','③'),'4'=>array('전체낙장[종료]','값어치 미사용+환불기간만료','④'),
		  '5'=>array('부분낙장[종료]','값어치 일부 사용+환불기간만료','⑤'),
		  '6'=>array('환불[종료]','값어치 미사용+유효기간 전 환불','⑥'),'7'=>array('환불[종료]','값어치 일부 사용+유효기간 전 환불','⑦'),
		  '8'=>array('환불[종료]','값어치 미사용+유효기간 후 환불','⑧'),'9'=>array('환불[종료]','값어치 일부 사용+유효기간 후 환불','⑨'));

		$this->arr_status = config_load('export_status');
		$this->arr_coupon_status = config_load('coupon_export_status');
	}

	public function insert_export($data)
	{
		$this->db->insert('fm_goods_export', $data);
		$export_seq = $this->db->insert_id();
		$update_data['export_code'] = 'D'.date('ymdH').$export_seq;

		$this->db->where('export_seq',$export_seq);
		$this->db->update('fm_goods_export',$update_data);
		return $update_data['export_code'];
	}

	public function get_export_for_order($order_seq)
	{
		$this->load->helper('shipping');
		$arr_delivery = get_delivery_url();
		$query = "
		select exp.*,
		sum(item.ea) as ea,
		IFNULL(sum(opt.ea),0)+IFNULL(sum(sub.ea),0) as order_ea,
		IFNULL(sum(opt.reserve*opt.ea),0) as reserve,
		IFNULL(sum(opt.point*opt.ea),0) as point,
		IFNULL(sum(sub.reserve*sub.ea),0) as sub_reserve,
		IFNULL(sum(sub.point*sub.ea),0) as sub_point,
		IFNULL(sum(opt.price*item.ea),0) as price,
		IFNULL(sum(sub.price*item.ea),0) as sub_price,
		oitem.goods_kind
		from
		fm_goods_export exp
		left join fm_goods_export_item item on exp.export_code = item.export_code
		left join fm_order_item_option opt on opt.item_option_seq  = item.option_seq
		left join fm_order_item_suboption sub on sub.item_suboption_seq  = item.suboption_seq
		left join fm_order_item oitem on oitem.item_seq  = item.item_seq
		where
		exp.order_seq = ?
		group by exp.export_code
		order by exp.`status`,exp.export_code
		";
		$query = $this->db->query($query,array($order_seq));
		foreach($query -> result_array() as $data){
			if($data['international'] == 'domestic'){
				if($data['domestic_shipping_method']=='quick'){
					$data['mdelivery'] = '퀵서비스 (착불)';
				}elseif($data['delivery_number']){
					$tmp = config_load('delivery_url',$data['delivery_company_code']);
					$data['mdelivery'] = $arr_delivery[$data['delivery_company_code']]['company'];
					$data['mdelivery_number'] = $data['delivery_number'];

					if($data['delivery_number']) {
						$data['delivery_number'] = str_replace('-','',$data['delivery_number']);

						if($data['mdelivery']=='대신택배'){ // 대신택배일 경우 예외 2013-12-27 lwh
							$data['tracking_url'] = $arr_delivery[$data['delivery_company_code']]['url'] . substr($data['delivery_number'],0,4) . "&billno2=" . substr($data['delivery_number'],4,3) . "&billno3=" . substr($data['delivery_number'],7,strlen($data['delivery_number']));
						}else{
							$data['tracking_url'] = $arr_delivery[$data['delivery_company_code']]['url'].$data['delivery_number'];
						}
					}
				}
			}else{
				$data['mdelivery'] = $data['international_shipping_method'];
				$data['mdelivery_number'] = $data['international_delivery_no'];
				if($data['international_delivery_no']) $data['tracking_url'] = $arr_delivery[$data['international_shipping_method']]['url'].$data['international_delivery_no'];
			}

			if($data['goods_kind']=='coupon'){
				//$data['mstatus'] = $this->arr_coupon_status[$data['status']]; 
				if( defined('__ADMIN__') === true || defined('__SELLERADMIN__') === true) {
					$data['mstatus'] = "<span class='red underline' >".$this->socialcp_status[$data['socialcp_status']][2]." ".$this->socialcp_status[$data['socialcp_status']][0].'</span>';
					$data['mstatus'] .= "<br/>";
					$data['mstatus'] .= $this->arr_status[$data['status']];
				}else{
					$data['mstatus'] = $this->arr_status[$data['status']];
					$data['mstatus'] .= " (<span style='color:red' >".$this->socialcp_status[$data['socialcp_status']][2]." ".$this->socialcp_status[$data['socialcp_status']][0].'</span>)';
				}
				$data['confirm_date'] = $this->arr_status[$data['socialcp_confirm_date']];
			}else{
				$data['mstatus'] = $this->arr_status[$data['status']];
			}
			
			$result[] = $data;
		}
		return $result;
	}

	public function get_export($export_code)
	{
		$query = "select * from fm_goods_export where export_code=? limit 1";
		$query = $this->db->query($query,array($export_code));
		list($result) = $query -> result_array();

		if($result['international'] == 'domestic'){
			if($result['domestic_shipping_method'] == 'delivery'){
				$tmp = config_load('delivery_url',$result['delivery_company_code']);
				$result['mdelivery'] = $arr_delivery[$result['delivery_company_code']]['company'];
				$result['mdelivery_number'] = $result['delivery_number'];
				if($result['delivery_number']) $result['tracking_url'] = $arr_delivery[$result['delivery_company_code']]['url'].$result['delivery_number'];
			}
		}else{
			$result['mdelivery'] = $result['international_shipping_method'];
			$result['mdelivery_number'] = $result['international_delivery_no'];
			if($result['international_delivery_no']) $result['tracking_url'] = $arr_delivery[$result['international_shipping_method']]['url'].$result['international_delivery_no'];
		}

		return $result;
	}

	public function get_export_item($export_code)
	{
		$export =$this->get_export($export_code);

		$query1 = "
		SELECT
		'opt' opt_type,
		opt.item_seq item_seq,
		opt.item_option_seq option_seq,
		opt.item_option_seq item_option_seq,
		opt.supply_price,
		opt.consumer_price,
		opt.price,
		item.goods_shipping_cost,
		opt.download_seq,
		opt.coupon_sale,
		opt.member_sale,
		opt.fblike_sale,
		opt.mobile_sale,
		opt.promotion_code_sale,
		opt.referer_sale,
		opt.reserve,
		opt.reserve*exp.ea as out_reserve,
		opt.point,
		opt.point*exp.ea as out_point,
		opt.step,
		item.goods_name,
		item.goods_kind,
		item.socialcp_input_type,
		item.socialcp_use_return,
		item.socialcp_use_emoney_day,
		item.socialcp_use_emoney_percent,
		item.image,
		item.event_seq,
		opt.title1,
		opt.title2,
		opt.title3,
		opt.title4,
		opt.title5,
		opt.option1,
		opt.option2,
		opt.option3,
		opt.option4,
		opt.option5,
		opt.goods_code,
		opt.step85,
		opt.step35,
		opt.step45,
		opt.step55,
		opt.step65,
		opt.step75,
		opt.newtype,
		opt.color,
		opt.zipcode,
		opt.address,
		opt.addressdetail,
		opt.biztel,
		opt.address_commission,
		opt.codedate,
		opt.sdayinput,
		opt.fdayinput,
		opt.dayauto_type,
		opt.sdayauto,
		opt.fdayauto,
		opt.dayauto_day,
		opt.social_start_date,
		opt.social_end_date,
		opt.coupon_input,
		opt.coupon_input_one,
		exp.coupon_remain_value,
		item.goods_seq,
		item.goods_shipping_cost,
		opt.ea opt_ea,
		shipping_item_option.ea shipping_ea,
		exp.ea,
		exp.export_item_seq,
		exp.coupon_value,
		exp.coupon_value_type,
		exp.coupon_remain_value,
		(select goods_type from fm_goods where goods_seq = item.goods_seq) as goods_type,
		(select cancel_type from fm_goods where goods_seq = item.goods_seq) as cancel_type, 
		item.individual_refund,
		item.individual_refund_inherit,
		item.individual_export,
		item.individual_return,
		item.socialcp_input_type,
		item.socialcp_use_return,
		item.socialcp_use_emoney_day,
		item.socialcp_use_emoney_percent,
		item.socialcp_cancel_use_refund,
		item.socialcp_cancel_payoption,
		item.socialcp_cancel_payoption_percent, 
		exp.export_code,
		exp.coupon_serial,
		exp.coupon_st,
		exp.recipient_email,
		exp.recipient_cellphone,
		exp.mail_status,
		exp.sms_status
		FROM
		fm_goods_export_item exp,fm_order_item_option opt,fm_order_item item,fm_order_shipping_item_option shipping_item_option
		WHERE
		exp.option_seq is not null
		AND exp.option_seq = opt.item_option_seq
		AND opt.item_seq = item.item_seq
		AND (shipping_item_option.shipping_seq='{$export['shipping_seq']}' and opt.item_seq=shipping_item_option.order_item_seq and opt.item_option_seq=shipping_item_option.order_item_option_seq)
		AND exp.export_code = ?
		ORDER BY opt.item_seq, opt.item_option_seq
		";
		$query2 = "
		SELECT
		'sub' opt_type,
		sub.item_seq item_seq,
		sub.item_suboption_seq option_seq,
		sub.item_option_seq item_option_seq,
		sub.supply_price,
		sub.consumer_price,
		sub.price,
		0 goods_shipping_cost,
		0 download_seq,
		0 coupon_sale,
		sub.member_sale,
		0 fblike_sale,
		0 mobile_sale,
		0 promotion_code_sale,
		0 referer_sale,
		sub.reserve,
		sub.reserve*exp.ea as out_reserve,
		sub.point,
		sub.point*exp.ea as out_point,
		sub.step,
		item.goods_name,
		item.goods_kind,
		item.socialcp_input_type,
		item.socialcp_use_return,
		item.socialcp_use_emoney_day,
		item.socialcp_use_emoney_percent,
		item.image,
		item.event_seq,
		sub.title title1,
		'' title2,
		'' title3,
		'' title4,
		'' title5,
		sub.suboption option1,
		'' option2,
		'' option3,
		'' option4,
		'' option5,
		sub.goods_code,
		sub.step85,
		sub.step35,
		sub.step45,
		sub.step55,
		sub.step65,
		sub.step75,
		sub.newtype,
		sub.color,
		sub.zipcode,
		sub.address,
		sub.addressdetail,
		sub.biztel,
		'' address_commission,
		sub.codedate,
		sub.sdayinput,
		sub.fdayinput,
		sub.dayauto_type,
		sub.sdayauto,
		sub.fdayauto,
		sub.dayauto_day,
		sub.social_start_date,
		sub.social_end_date,
		sub.coupon_input,
		sub.coupon_input_one,
		exp.coupon_remain_value,
		item.goods_seq,
		item.goods_shipping_cost,
		sub.ea opt_ea,
		shipping_item_option.ea shipping_ea,
		exp.ea,
		exp.export_item_seq,
		exp.coupon_value,
		exp.coupon_value_type,
		exp.coupon_remain_value,
		(select goods_type from fm_goods where goods_seq = item.goods_seq) as goods_type,
		(select cancel_type from fm_goods where goods_seq = item.goods_seq) as cancel_type,
		item.individual_refund,
		item.individual_refund_inherit,
		item.individual_export,
		item.individual_return,
		item.socialcp_input_type,
		item.socialcp_use_return,
		item.socialcp_use_emoney_day,
		item.socialcp_use_emoney_percent,
		item.socialcp_cancel_use_refund,
		item.socialcp_cancel_payoption,
		item.socialcp_cancel_payoption_percent, 
		exp.export_code,
		exp.coupon_serial,
		exp.coupon_st,
		exp.recipient_email,
		exp.recipient_cellphone,
		exp.mail_status,
		exp.sms_status
		FROM
		fm_goods_export_item exp,fm_order_item_suboption sub,fm_order_item item,fm_order_shipping_item_option shipping_item_option
		WHERE
		exp.suboption_seq is not null
		AND exp.suboption_seq = sub.item_suboption_seq
		AND sub.item_seq = item.item_seq
		AND (shipping_item_option.shipping_seq='{$export['shipping_seq']}' and sub.item_seq=shipping_item_option.order_item_seq and sub.item_suboption_seq=shipping_item_option.order_item_suboption_seq)
		AND exp.export_code = ?
		ORDER BY sub.item_seq, sub.item_suboption_seq desc
		";
		$query = "(".$query1.") union (".$query2.") order by opt_type='opt' desc, option_seq desc";
		$query = $this->db->query($query,array($export_code,$export_code));
		if( $query ) {
			foreach($query->result_array() as $data){
				//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
				if(!preg_match("/(http|https|ftp):\/\//i",trim($data['image'])) && !(is_file($data['image'])) ) {
					$data['image'] = viewImg($data['goods_seq'],'thumbCart');
				}
				$result[] = $data;
			}
		}

		return $result;
	}

	public function get_export_item_by_item_seq($export_item_seq)
	{
		$query = "select * from fm_goods_export_item where export_item_seq=?";
		$query = $this->db->query($query,array($export_item_seq));
		$result = $query->row_array();
		return $result;
	}

	// 옵션별 출고
	public function get_export_item_by_option_seq($option_seq)
	{
		$query = "select a.*,b.domestic_shipping_method from fm_goods_export_item a,fm_goods_export b where a.export_code=b.export_code and a.option_seq=?";
		$query = $this->db->query($query,array($option_seq));
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	// 추가옵션별 출고
	public function get_export_item_by_suboption_seq($suboption_seq)
	{
		$query = "select a.*,b.domestic_shipping_method from fm_goods_export_item a,fm_goods_export b where a.export_code=b.export_code and a.suboption_seq=?";
		$query = $this->db->query($query,array($suboption_seq));
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	public function delete_export_item_by_item_seq($export_item_seq)
	{
		$query = "delete from fm_goods_export_item where export_item_seq=?";
		$this->db->query($query,array($export_item_seq));
	}

	public function update_ea_export_item($export_item_seq,$ea)
	{
		$query = "update fm_goods_export_item set ea=? where export_item_seq=?";
		$this->db->query($query,array($ea,$export_item_seq));
	}

	// 출고 상태 변경
	public function set_status($code,$status){
		if($status=='75'){
			$query = "update `fm_goods_export` set `status`=? , `shipping_date`=? where `export_code`=?";
			$this->db->query($query,array($status, date("Y-m-d"), $code));
		}else if($status=='55'){
			$query = "update `fm_goods_export` set `status`=?, `complete_date`=? where `export_code`=?";
			$this->db->query($query,array($status, date("Y-m-d"), $code));
		}else{
			$query = "update `fm_goods_export` set `status`=? where `export_code`=?";
			$this->db->query($query,array($status,$code));
		}
	}

	public function delete_export($code){
		$query = "delete from fm_goods_export_item where export_code=?";
		$this->db->query($query,array($code));
		$query = "delete from fm_goods_export where export_code=?";
		$this->db->query($query,array($code));
	}

	public function exec_complete_export($export_code,$cfg_order,$echo=true){

		$CI =& get_instance();

		$this->load->model('goodsmodel');
		$this->load->model('membermodel');  //2014-07-21 추가
		$data_export = $this->get_export($export_code);
		if( !in_array($data_export['status'],$this->able_status_action['complete_export']) ){
			if($echo){
				openDialogAlert($this->arr_step[$data_export['status']]."에서는 출고완료를 하실 수 없습니다.",400,140,'parent',"");
				exit;
			}else{
				return $this->arr_step[$data_export['status']]."에서는 출고완료를 하실 수 없습니다.";
			}
		}

		$data_export_item = $this->get_export_item($export_code);
		if(!$cfg_order)$cfg_order = config_load('order');

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		// 상품 재고 차감  및 상품 재고 체크
		foreach($data_export_item as $item){
			if($item['opt_type'] == 'opt'){
				if($cfg_order['export_err_handling'] == 'error'){
					$goods_seq = $item['goods_seq'];
					$option1 = $item['option1'];
					$option2 = $item['option2'];
					$option3 = $item['option3'];
					$option4 = $item['option4'];
					$option5 = $item['option5'];
					$goods_stock = (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
					if($goods_stock < $item['ea']){
						return $export_code;
					}
				}

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
				if($cfg_order['export_err_handling'] == 'error'){
					$goods_seq = $item['goods_seq'];
					$title = $item['title1'];
					$suboption = $item['option1'];
					$goods_stock = (int) $this->goodsmodel->get_goods_suboption_stock($goods_seq,$title,$suboption);
					if($goods_stock < $item['ea']){
						return $export_code;
					}
				}
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

		// 상태별 수량 업데이트 및 주문 상태 변경
		foreach($data_export_item as $k => $item){
			if($item['opt_type'] == 'opt') $mode = 'option';
			else $mode = 'suboption';

			$minus_ea = $item['ea'] * -1;
			$this->ordermodel->set_step_ea($data_export['status'],$minus_ea,$item['option_seq'],$mode);
			$this->ordermodel->set_step_ea(55,$item['ea'],$item['option_seq'],$mode);
			$this->ordermodel->set_option_step($item['option_seq'],$mode);
		}
		$this->ordermodel->set_order_step($data_export['order_seq']);

		$this->set_status($export_code,'55');

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		$actor = $this->managerInfo['mname'];
		if(!$actor) $actor = "관리자";

		// 출고로그
		$this->ordermodel->set_log($data_export['order_seq'],'export',$actor,'출고완료',$this->managerInfo['mname'].'가 출고완료를 하였습니다.','',$data_export['export_code']);

		/* 출고자동화 전송 */
		$this->load->model('invoiceapimodel');
		$invoiceExportResult = $this->invoiceapimodel->export($export_code);
		if($invoiceExportResult['resultDeliveryNumber'] && !$data_export['delivery_number']){
			$data_export['delivery_number'] = $invoiceExportResult['resultDeliveryNumber'][0];
		}

		# 오픈마켓 송장등록 #
		$this->load->model('openmarketmodel');
		$this->openmarketmodel->request_send_export($export_code);

		$orders	= $this->ordermodel->get_order($data_export['order_seq']);
		if(!$orders['linkage_id']){
			// 출고완료 메일링
			send_mail_step55($export_code);

			$orders	= $this->ordermodel->get_order($data_export['order_seq']);
			if( $orders['order_cellphone'] ){
				$this->load->helper('shipping');
				$shipping_company_arr	= get_shipping_company($orders['international'],$orders['shipping_method']);
				$params['delivery_company']	= $shipping_company_arr[$data_export['delivery_company_code']]['company'];
				$params['delivery_number']	= $data_export['delivery_number'];

				$params['goods_name']	= $data_export_item[0]['goods_name'];
				if	(count($data_export_item) > 1)
					$params['goods_name']	.= '외 '.(count($data_export_item) - 1).'건';

				if	($orders['payment'] == 'bank'){
					$bank_arr				= explode(' ', $orders['bank_account']);
					$params['settle_kind']	= $bank_arr[0] . ' 입금확인';
				}else{
					$params['settle_kind']	= $orders['mpayment'] . ' 입금확인';
				}

				$params['shopName']		= $this->config_basic['shopName'];
				$params['ordno']		= $data_export['order_seq'];
				$params['user_name']	= $orders['order_user_name'];
				$params['member_seq']	= $orders['member_seq'];	//2014-07-21 추가
				$CI->exportSmsData['released']['phone'][] = $orders['order_cellphone'];
				$CI->exportSmsData['released']['params'][] = $params;
				$CI->exportSmsData['released']['order_no'][] = $data_export['order_seq'];

				//sendSMS($orders['order_cellphone'], 'released', '', $params);
				# 주문자와 받는분이 다를때 받는분에게도 문자 전송
				if( $orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))){
					$CI->exportSmsData['released2']['phone'][] = $orders['recipient_cellphone'];
					$CI->exportSmsData['released2']['params'][] = $params;
					$CI->exportSmsData['released2']['order_no'][] = $data_export['order_seq'];

					//sendSMS($orders['recipient_cellphone'], 'released2', '', $params);          //받는자
				}

			}
		}

		return false;
	}

	// 배송중 처리
	public function exec_going_delivery($export_code){

		$data_export = $this->get_export($export_code);
		if( !in_array($data_export['status'],$this->able_status_action['going_delivery']) ){
			openDialogAlert($this->arr_step[$data_export['status']]."에서는 배송중처리를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		// 상태별 수량 업데이트 및 주문 상태 변경
		$data_export_item = $this->get_export_item($export_code);

		foreach($data_export_item as $k => $item){
			if($item['opt_type'] == 'opt') $mode = 'option';
			else $mode = 'suboption';

			$minus_ea = $item['ea'] * -1;

			$this->ordermodel->set_step_ea(65,$item['ea'],$item['option_seq'],$mode);
			$this->ordermodel->set_step_ea($data_export['status'],$minus_ea,$item['option_seq'],$mode);

			$this->ordermodel->set_option_step($item['option_seq'],$mode);
		}
		$this->ordermodel->set_order_step($data_export['order_seq']);
		$this->set_status($export_code,'65');

		// 출고로그
		$this->ordermodel->set_log($data_export['order_seq'],'export',$this->managerInfo['mname'],'배송중',$this->managerInfo['mname'].'가 배송중 처리를 하였습니다.','',$data_export['export_code']);
	}

	//실물상품의 구매확정시처리
	public function exec_complete_delivery($export_code,$save=true,$echo=true,$system=''){
		
		$CI =& get_instance();

		$this->load->helper('order');
		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel');
		/*
			반품을 위한 수령확인 시 $save값 false
		*/
		$data_export = $this->get_export($export_code);
		if( !in_array($data_export['status'],$this->able_status_action['complete_delivery']) ){
			if($echo){
				openDialogAlert($this->arr_step[$data_export['status']]."에서는 배송완료를 하실 수 없습니다.",400,140,'parent',"");
				exit;
			}else{
				return $this->arr_step[$data_export['status']]."에서는 배송완료를 하실 수 없습니다.";
			}
		}

		$this->set_status($export_code,'75');

		// $data_order = $this->ordermodel->get_order($data_export['order_seq']);

		if ($system=='system') {
			$actor = "자동";
		} else {
			$actor = $this->managerInfo['mname'];
			if(!$actor) $actor = "주문자";
		}

		// 출고로그
		$this->ordermodel->set_log($data_export['order_seq'],'export',$actor,'배송완료',$actor.'가 배송완료를 하였습니다.','',$data_export['export_code']);

		// 상태별 수량 업데이트 및 주문 상태 변경
		$tot_reserve	= 0;
		$tot_point		= 0;
		$data_export_item = $this->get_export_item($export_code);
		if($data_export_item[0]['goods_kind'] == 'coupon') $this->coupon_goods = true;//쇼셜쿠폰상품

		foreach($data_export_item as $k => $item){
			if($item['opt_type'] == 'opt'){
				$mode = 'option';
			}else{
				$mode = 'suboption';
			}

			$minus_ea = $item['ea'] * -1;
			$this->ordermodel->set_step_ea(75,$item['ea'],$item['option_seq'],$mode);
			$this->ordermodel->set_step_ea($data_export['status'],$minus_ea,$item['option_seq'],$mode);

			// 상품에 구매수량 업데이트
			$this->goodsmodel->get_purchase_ea($item['ea'],$item['goods_seq']);

			$this->ordermodel->set_option_step($item['option_seq'],$mode);
			if($data_export['reserve_save'] == 'none'){
				$reserve = 0;
				if($mode == 'option') $reserve = $this->ordermodel->get_option_reserve($item['option_seq']);
				if($mode == 'suboption') $reserve = $this->ordermodel->get_suboption_reserve($item['option_seq']);
				$tot_reserve += $reserve * $item['ea'];

				$point = 0;
				if($mode == 'option') $point = $this->ordermodel->get_option_reserve($item['option_seq'],'point');
				if($mode == 'suboption') $point = $this->ordermodel->get_suboption_reserve($item['option_seq'],'point');
				$tot_point += $point * $item['ea'];
			}
		}

		$this->ordermodel->set_order_step($data_export['order_seq']);

		$cfg_order = config_load('order');

		if($save){
			$data_order = $this->ordermodel->get_order($data_export['order_seq']);
			if(!$cfg_order['buy_confirm_use']){
				if($data_export['reserve_save'] == 'none'){
					// 회원 적립금 적립
					if($data_order['member_seq']){
						if($tot_reserve){
							$params_reserve['gb'] = "plus";
							$params_reserve['emoney'] 	= $tot_reserve;
							$params_reserve['memo'] 	= "[".$export_code."] 배송완료";
							$params_reserve['ordno']	= $data_order['order_seq'];
							$params_reserve['type'] 	= "order";
							$params_reserve['limit_date'] 	= get_emoney_limitdate('order');
							$this->membermodel->emoney_insert($params_reserve, $data_order['member_seq']);
						}

						if($tot_point){
							$params_point['gb']		= "plus";
							$params_point['point'] 	= $tot_point;
							$params_point['memo'] 	= "[".$export_code."] 배송완료";
							$params_point['ordno']	= $data_order['order_seq'];
							$params_point['type'] 	= "order";
							$params_point['limit_date'] 	= get_point_limitdate('order');
							$this->membermodel->point_insert($params_point, $data_order['member_seq']);
						}

						if($tot_reserve || $tot_point){
							$query = "update fm_goods_export set reserve_save = 'save' where export_code = ?";
							$this->db->query($query,array($export_code));
						}
					}
				}
			}

			$orders	= $this->ordermodel->get_order($data_export['order_seq']);

			if(!$orders['linkage_id']){
				// 배송완료시 email/sms
				if ( $this->coupon_goods ) {//쇼셜쿠폰상품
					coupon_send_mail_step75($export_code);
					coupon_send_sms_step75($export_code);
				}else{
					send_mail_step75($export_code);

					// 배송완료시 sms
					if( $data_order['order_cellphone'] ){
						$this->load->helper('shipping');
						$shipping_company_arr	= get_shipping_company($data_order['international'],$data_order['shipping_method']);
						$params['delivery_company']	= $shipping_company_arr[$data_export['delivery_company_code']]['company'];
						$params['delivery_number']	= $data_export['delivery_number'];

						$params['goods_name']	= $data_export_item[0]['goods_name'];
						if	(count($data_export_item) > 1)
							$params['goods_name']	.= '외 '.(count($data_export_item) - 1).'건';

						if	($orders['payment'] == 'bank'){
							$bank_arr				= explode(' ', $orders['bank_account']);
							$params['settle_kind']	= $bank_arr[0] . ' 입금확인';
						}else{
							$params['settle_kind']	= $orders['mpayment'] . ' 입금확인';
						}

						$params['shopName']			= $this->config_basic['shopName'];
						$params['ordno']			= $data_order['order_seq'];
						$params['member_seq']		= $orders['member_seq'];
						$params['user_name']		= $orders['order_user_name'];

						$CI->exportSmsData['delivery']['phone'][] = $data_order['order_cellphone'];
						$CI->exportSmsData['delivery']['params'][] = $params;
						$CI->exportSmsData['delivery']['order_no'][] = $order['order_seq'];

						//sendSMS($data_order['order_cellphone'], 'delivery', '', $params);
						# 주문자와 받는분이 다를때 받는분에게도 문자 전송
						if($orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))){
							$CI->exportSmsData['delivery2']['phone'][] = $orders['recipient_cellphone'];
							$CI->exportSmsData['delivery2']['params'][] = $params;
							$CI->exportSmsData['delivery2']['order_no'][] = $order['order_seq'];

						   //sendSMS($orders['recipient_cellphone'], 'delivery2', '', $params);          //받는자
						}
					}
				}
			}
		}
	}

	/**
	** 쿠폰상품의 주문상태처리
	* statustype :  cancel(취소(환불)), expired_prev(환불가능기간전 완료), expired_next(환불가능기간후 완료)
	* export_code : 출고번호
	* pointsave : 포인트/적립금 지급여부 
	* cancelpercent : 취소(환불)시 환불금액 제외율
	* socialcp_confirm : system, mname, 주문자 상태변경 작업자
	**/
	public function socialcp_exec_complete_delivery($export_code,$pointsave=true,$cancelpercent,$socialcp_confirm,$statustype='cancel'){
		$this->load->helper('order');
		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$this->load->model('membermodel'); 
		$this->load->model('accountmodel');

		$data_export	= $this->get_export($export_code);
		$orders			= $this->ordermodel->get_order($data_export['order_seq']);
		if($data_export['status'] < 75)  $this->set_status($export_code,'75');
		
		if( $socialcp_confirm == "system" ) {
			$actor = "자동";
		}else{
			$actor = $this->managerInfo['mname'];
			if(!$actor) $actor = "주문자";
		}

		// 출고로그
		$this->ordermodel->set_log($data_export['order_seq'],'export',$actor,'배송완료',$actor.'가 배송완료를 하였습니다.','',$data_export['export_code']);

		// 상태별 수량 업데이트 및 주문 상태 변경
		$tot_reserve	= 0;
		$tot_point		= 0;
		$data_export_item = $this->get_export_item($export_code);
		if($data_export_item[0]['goods_kind'] == 'coupon') $this->coupon_goods = true;//쇼셜쿠폰상품

		## 출고 및 주문 배송완료 처리 
		foreach($data_export_item as $k => $item){
			$providerList[$item['provider_seq']]	= 1;

			if($item['opt_type'] == 'opt'){
				$mode = 'option';
			}else{
				$mode = 'suboption';
			}

			$minus_ea = $item['ea'] * -1;
			$this->ordermodel->set_step_ea(75,$item['ea'],$item['option_seq'],$mode);
			$this->ordermodel->set_step_ea($data_export['status'],$minus_ea,$item['option_seq'],$mode);

			// 상품에 구매수량 업데이트
			$this->goodsmodel->get_purchase_ea($item['ea'],$item['goods_seq']);

			$this->ordermodel->set_option_step($item['option_seq'],$mode);
			if($data_export['reserve_save'] == 'none'){
				$reserve = 0;
				if($mode == 'option') $reserve = $this->ordermodel->get_option_reserve($item['option_seq']);
				if($mode == 'suboption') $reserve = $this->ordermodel->get_suboption_reserve($item['option_seq']);
				$tot_reserve += $reserve * $item['ea'];

				$point = 0;
				if($mode == 'option') $point = $this->ordermodel->get_option_reserve($item['option_seq'],'point');
				if($mode == 'suboption') $point = $this->ordermodel->get_suboption_reserve($item['option_seq'],'point');
				$tot_point += $point * $item['ea'];
			}
		}
		$this->ordermodel->set_order_step($data_export['order_seq']); 

		if( $pointsave ) {
			if($data_export['reserve_save'] == 'none'){
				// 회원 적립금 적립
				if($orders['member_seq']){
					if($tot_reserve) {
						if( $statustype == 'cancel' ) {//취소(환불)시 환불금액만큼 제외
							$coupon_remain_reserve			= (int) ($cancelpercent * ($tot_reserve) / 100);
							$tot_reserve	= (int) ($tot_reserve) - $coupon_remain_reserve;  
						}
						$params_reserve['gb'] = "plus";
						$params_reserve['emoney'] 	= $tot_reserve;
						$params_reserve['memo'] 	= "[".$export_code."] 배송완료";
						$params_reserve['ordno']	= $orders['order_seq'];
						$params_reserve['type'] 	= "order";
						$params_reserve['limit_date'] 	= get_emoney_limitdate('order');
						$this->membermodel->emoney_insert($params_reserve, $orders['member_seq']);
					}

					if($tot_point){
						if( $statustype == 'cancel' ) {//취소(환불)시 환불금액만큼 제외
							$coupon_remain_point			= (int) ($cancelpercent * ($tot_point) / 100);
							$tot_point	= (int) ($tot_point) - $coupon_remain_point;  
						}
						$params_point['gb']		= "plus";
						$params_point['point'] 	= $tot_point;
						$params_point['memo'] 	= "[".$export_code."] 배송완료";
						$params_point['ordno']	= $orders['order_seq'];
						$params_point['type'] 	= "order";
						$params_point['limit_date'] 	= get_point_limitdate('order');
						$this->membermodel->point_insert($params_point, $orders['member_seq']);
					}

					if($tot_reserve || $tot_point){
						$query = "update fm_goods_export set reserve_save = 'save' where export_code = ?";
						$this->db->query($query,array($export_code));
					}
				}
			}
		}//endif; 
	}

	public function get_export_item_for_order($item_seq, $optSeq, $optType = 'option'){
		$addWhere	= " and option_seq = '".$optSeq."' ";
		if	($optType == 'sub')
			$addWhere	= " and suboption_seq = '".$optSeq."' ";

		$sql	= "select * from fm_goods_export_item where item_seq = '".$item_seq."' ".$addWhere;
		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	// 출고목록 중 쿠폰 상품만 추출
	public function get_coupon_export($order_seq, $provider_seq = ''){

		if	($provider_seq)
			$addWhere	= " and item.provider_seq = '".$provider_seq."' ";

		$sql	= "select *
					from
						fm_goods_export	exp,
						fm_goods_export_item exp_item,
						fm_order_item item
					where
						exp.order_seq = '".$order_seq."' and
						exp.export_code = exp_item.export_code and
						exp_item.item_seq = item.item_seq and
						item.goods_kind = 'coupon' ".$addWhere;
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}

	// 결제확인될 때 쿠폰 상품 출고처리
	public function coupon_payexport($order_seq, $provider_seq = '', $setemail = array(), $setsms = array(), $export_date = '' ) {

		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		if(!$cfg_order)		$cfg_order		= config_load('order');
		if(!$export_date)	$export_date	= date('Y-m-d');

		$order	= $this->ordermodel->get_order($order_seq);
		if	($order['order_seq'] && $order['step'] < 55){
			$option	= $this->ordermodel->get_item_option($order_seq);
			if	($option){
				foreach($option as $k => $opt){
					if	(!$provider_seq || $opt['provider_seq'] == $provider_seq){
						if	($opt['goods_kind'] == 'coupon'){
							$chk_opt_stock	= true;
							$export_cnt++;
							// 재고체크
							if($cfg_order['export_err_handling'] == 'error'){
								$goods_stock = (int) $this->goodsmodel->get_goods_option_stock($opt['goods_seq'],$opt['option1'],$opt['option2'],$opt['option3'],$opt['option4'],$opt['option5']);
								if($goods_stock < $opt['ea'])
									$chk_opt_stock	= false;
							}
							if	($chk_opt_stock){
								$export_cnt++;
								// 쿠폰상품 옵션 출고처리
								$param['export_date']	= $export_date;
								$param['option_seq']	= $opt['item_option_seq'];
								$param['coupon_mail']	= $order['recipient_email'];
								$param['coupon_sms']	= $order['recipient_cellphone'];
								// email, sms 지정발송
								if	($setemail[$opt['item_seq']])
									$param['coupon_mail']	= $setemail[$opt['item_seq']];
								if	($setsms[$opt['item_seq']])
									$param['coupon_sms']	= $setsms[$opt['item_seq']];
								$export_ea	= $this->coupon_export($param);
								unset($param);
								if	($export_ea > 0){
									// 주문상태별 수량 변경
									$this->ordermodel->set_step_ea(55, $export_ea, $opt['item_option_seq'], 'option');
									// 주문 option 상태 변경
									$this->ordermodel->set_option_step($opt['item_option_seq'], 'option');
								}
							}

							##### 추가옵션 #####
							$suboption	= $this->ordermodel->get_suboption_for_option($opt['item_seq'], $opt['item_option_seq']);
							if	($suboption){
								foreach($suboption as $s => $sub){
									$chk_sub_stock	= true;
									$export_cnt++;

									// 재고체크
									if($cfg_order['export_err_handling'] == 'error'){
										$goods_stock = (int) $this->goodsmodel->get_goods_suboption_stock($opt['goods_seq'],$sub['title'],$sub['suboption']);
										if($goods_stock < $sub['ea'])
											$chk_sub_stock	= false;
									}
									if	($chk_sub_stock){
										$export_cnt++;
										// 쿠폰상품 추가옵션 출고처리
										$param['export_date']	= date('Y-m-d');
										$param['suboption_seq']	= $sub['item_suboption_seq'];
										$param['coupon_mail']	= $order['recipient_email'];
										$param['coupon_sms']	= $order['recipient_cellphone'];
										// email, sms 지정발송
										if	($setemail[$sub['item_seq']])
											$param['coupon_mail']	= $setemail[$sub['item_seq']];
										if	($setsms[$sub['item_seq']])
											$param['coupon_sms']	= $setsms[$sub['item_seq']];
										$export_ea	= $this->coupon_export($param);
										unset($param);

										if	($export_ea > 0){
											// 주문상태별 수량 변경
											$this->ordermodel->set_step_ea(55,$export_ea,$sub['item_suboption_seq'],'suboption');
											// 주문 option 상태 변경
											$this->ordermodel->set_option_step($sub['item_suboption_seq'],'suboption');
										}
									}
								}
							}
						}
					}
				}
			}

			// 주문상태 변경
			$this->ordermodel->set_order_step($order_seq);
		}

		return $export_cnt;
	}

	// 쿠폰상품 출고 처리 ( option당 처리 )
	public function coupon_export($param, $export_ea = 'ALL'){

		$this->load->model('goodsmodel');

		// 주문 정보 추출 ( 현재 쿠폰 상품은 suboption이 없으나 나중을 위해 넣어둠. )
		if	($param['suboption_seq'] > 0){
			$suboption_seq	= $param['suboption_seq'];
			$sql	= "select item.goods_seq, item.socialcp_input_type, item.socialcp_use_return, item.socialcp_use_emoney_day, shp.shipping_seq, sub.*
						from fm_order_item item, fm_order_item_suboption sub,
						fm_order_shipping_item_option as shp
						where item.item_seq = sub.item_seq
						and sub.item_suboption_seq = shp.order_item_suboption_seq
						and sub.item_suboption_seq = '".$suboption_seq."'
						and sub.step >= 25 and sub.step < 55 ";
		}else{
			$option_seq		= $param['option_seq'];
			$sql	= "select item.goods_seq, item.socialcp_input_type, item.socialcp_use_return, item.socialcp_use_emoney_day, shp.shipping_seq, opt.*
						from fm_order_item item, fm_order_item_option opt,
						fm_order_shipping_item_option as shp
						where item.item_seq = opt.item_seq
						and opt.item_option_seq = shp.order_item_option_seq
						and opt.item_option_seq = '".$option_seq."'
						and opt.step >= '25' and opt.step < '55' ";
		}
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		if	($result['order_seq']){
			// ea당 출고 처리
			$goods_seq		= $result['goods_seq'];
			$export_date	= $param['export_date'];
			if	(!$export_date)			$export_date	= date('Y-m-d');
			if	($export_ea == 'ALL')	$export_ea		= $result['ea'];
			$coupon_st	= $result['step55'];
			for ($o = 1; $o <= $export_ea; $o++){

				if	($goods_seq)
					$coupon_serial_code	= $this->goodsmodel->get_out_coupon_serial_code($goods_seq);

				if	($coupon_serial_code){
					$result_export_ea++;

					$coupon_st++;
					$insertExport['status']						= '55';
					$insertExport['order_seq']				= $result['order_seq'];
					$insertExport['shipping_seq']			= $result['shipping_seq'];
					$insertExport['buy_confirm']			= 'none';
					$insertExport['reserve_save']			= 'none';		// 배송완료에서 적립금 지급
					$insertExport['international']			= 'domestic';	// NOT NULL 이라서....
					$insertExport['export_date']			= $export_date;
					$insertExport['regist_date']				= date('Y-m-d H:i:s');
					$insertExport['complete_date']		= date('Y-m-d H:i:s');
					 
					//미사용쿠폰환불기간
					if( $result['socialcp_use_return']  == 1 ) {//유예기간 설정시
						$insertExport['socialcp_refund_day'] = date("Ymd",strtotime('+'.$result['socialcp_use_emoney_day'].' day '.substr(str_replace("-","",$result['social_end_date']),0,8)));
					}else{//유예기간없으면 종료유효기간과 동일
						$insertExport['socialcp_refund_day'] = date("Ymd",strtotime($result['social_end_date']));
					}

					$export_code = $this->insert_export($insertExport);
					unset($insertExport);

					// 외부쿠폰일 경우 출고처리 자동일 경우 쿠폰번호 추출
					if	($coupon_serial_code == 'a')
						$coupon_serial_code	= get_coupon_serialnumber($export_code);
					else
						$this->goodsmodel->use_out_coupon_serial_code($coupon_serial_code, $result['goods_seq'], $export_code);

					$insertExportItem['export_code']			= $export_code;
					$insertExportItem['item_seq']				= $result['item_seq'];
					$insertExportItem['coupon_serial']			= $coupon_serial_code;
					$insertExportItem['coupon_st']				= $coupon_st;
					$insertExportItem['coupon_value_type']		= $result['socialcp_input_type'];
					$insertExportItem['coupon_value']			= $result['coupon_input'];
					$insertExportItem['coupon_remain_value']	= $result['coupon_input'];
					$insertExportItem['recipient_email']		= $param['coupon_mail'];
					$insertExportItem['recipient_cellphone']	= $param['coupon_sms'];
					$insertExportItem['ea']						= 1;
					$insertExportItem['mail_status']			= 'n';
					$insertExportItem['sms_status']				= 'n';
					if	($suboption_seq > 0)	$insertExportItem['suboption_seq']	= $suboption_seq;
					else						$insertExportItem['option_seq']		= $option_seq;
					$this->db->insert('fm_goods_export_item', $insertExportItem);

					// 쿠폰번호 저장
					$this->save_coupon_serial($insertExportItem['coupon_serial'], $export_code);
					unset($insertExportItem);

					$this->coupon_export_send($export_code, 'all', $param['coupon_mail'], $param['coupon_sms']);
				}
			}

			$export_ea	= $result_export_ea;

			// 재고 차감
			if	($suboption_seq > 0){
				$this->goodsmodel->stock_suboption(
					'-',
					$export_ea,
					$result['goods_seq'],
					$result['title1'],
					$result['option1']
				);

				// 출고예약량 업데이트
				$reservation	= $export_ea;
				$goods_seq		= $result['goods_seq'];
				$title			= $result['title'];
				$option			= $result['suboption'];
				$this->goodsmodel->modify_reservation_suboption($reservation,$goods_seq,$title,$option,15,'minus');
				$this->goodsmodel->modify_reservation_suboption($reservation,$goods_seq,$title,$option,25,'minus');
			}else{
				$this->goodsmodel->stock_option(
					'-',
					$export_ea,
					$result['goods_seq'],
					$result['option1'],
					$result['option2'],
					$result['option3'],
					$result['option4'],
					$result['option5']
				);

				// 출고예약량 업데이트
				$reservation	= $export_ea;
				$goods_seq		= $result['goods_seq'];
				$option1		= $result['option1'];
				$option2		= $result['option2'];
				$option3		= $result['option3'];
				$option4		= $result['option4'];
				$option5		= $result['option5'];
				$this->goodsmodel->modify_reservation_option($reservation,$goods_seq,$option1,$option2,$option3,$option4,$option5,15,'minus');
				$this->goodsmodel->modify_reservation_option($reservation,$goods_seq,$option1,$option2,$option3,$option4,$option5,25,'minus');
			}

			return $export_ea;
		}

		return false;
	}

	// 쿠폰상품 출고 메일, SMS발송 (@param 출고코드, 받는이 이메일, 받는이 핸드폰 번호 )
	public function coupon_export_send($export_code, $sendType = 'all', $email = '', $sms = ''){

		$export	= $this->get_export($export_code);
		$items	= $this->get_export_item($export_code);
		$item	= $items[0];
		if	(!$email)	$email	= $item['recipient_email'];
		if	(!$sms)		$sms	= $item['recipient_cellphone'];

		// 출고완료 메일 및 SMS 발송
		$mail_result = $sms_result = false;
		$param['export_code']	= $export_code;
		$param['order_seq']		= $export['order_seq'];
		$param['regist_date']	= date('Y-m-d H:i:s');
		if	($email && in_array($sendType, array('all', 'mail'))){
			$mail_result	= coupon_send_mail_step55($export_code, $email);

			// mail send log
			$param['order_seq']		= $export['order_seq'];
			$param['export_code']	= $export_code;
			$param['send_kind']		= 'mail';
			$param['status']		= ($mail_result === false) ? 'n' : 'y';
			$param['send_val']		= $email;
			$param['regist_date']	= date('Y-m-d H:i:s');
			$this->db->insert('fm_goods_export_send_log', $param);
		}
		if	($sms && in_array($sendType, array('all', 'sms'))){

			## 주문자와 받는분이 다를 때. 주문자 핸드폰 번호 불러오기
			$sql				= "select order_cellphone from fm_order where order_seq='".$export['order_seq']."'";
			$query				= $this->db->query($sql);
			$res				= $query->row_array();
			$order_cellphone	= $res['order_cellphone'];

			$sms_result		= coupon_send_sms_step55($export_code, $sms, $order_cellphone);

			// sms send log
			$param['order_seq']		= $export['order_seq'];
			$param['export_code']	= $export_code;
			$param['send_kind']		= 'sms';
			$param['status']		= ($sms_result === false) ? 'n' : 'y';
			$param['send_val']		= $sms;
			$param['regist_date']	= date('Y-m-d H:i:s');
			$this->db->insert('fm_goods_export_send_log', $param);
		}

		// 발송결과 저장
		if	($item['mail_status'] == 'y'){
			if	($mail_result){
				$addUpdate['recipient_email']		= $email;
				$addUpdate['mail_status']			= 'y';
			}
		}elseif	(in_array($sendType, array('all', 'mail'))){
			$addUpdate['recipient_email']		= $email;
			$addUpdate['mail_status']			= ($mail_result === false) ? 'n' : 'y';
		}

		if	($item['sms_status'] == 'y'){
			if	($sms_result){
				$addUpdate['recipient_cellphone']	= $sms;
				$addUpdate['sms_status']			= 'y';
			}
		}elseif	(in_array($sendType, array('all', 'sms'))){
			$addUpdate['recipient_cellphone']	= $sms;
			$addUpdate['sms_status']			= ($sms_result === false) ? 'n' : 'y';
		}

		if	($item['export_item_seq'] > 0 && count($addUpdate) > 0){
			$this->db->where(array("export_item_seq"=>$item['export_item_seq']));
			$result = $this->db->update('fm_goods_export_item', $addUpdate);
		}

		return $addUpdate;
	}

	// 쿠폰인증코드 출고 정보 추출
	public function get_coupon_info($param){

		if	($param['order_seq'])
			$addWhere	.= " and exp.order_seq = '".$param['order_seq']."' ";
		if	($param['export_code'])
			$addWhere	.= " and exp.export_code = '".$param['export_code']."' ";
		if	($param['coupon_serial'])
			$addWhere	.= " and exp_item.coupon_serial = '".$param['coupon_serial']."' ";

		$sql	= "select
						exp.order_seq,
						exp_item.export_code,
						exp_item.export_item_seq,
						exp.status,
						exp_item.coupon_serial,
						exp_item.coupon_value_type,
						exp_item.coupon_value,
						exp_item.coupon_remain_value,
						exp_item.item_seq,
						exp_item.option_seq,
						exp_item.suboption_seq,
						ord_item.goods_seq,
						IF(exp_item.option_seq > 0,(select address_commission from fm_order_item_option where item_option_seq = exp_item.option_seq limit 1), 0) as address_commission,
						IF(exp_item.option_seq > 0,(select social_start_date from fm_order_item_option where item_option_seq = exp_item.option_seq limit 1), (select social_start_date from fm_order_item_suboption where item_suboption_seq = exp_item.suboption_seq limit 1)) as coupon_start_date,
						IF(exp_item.option_seq > 0,(select social_end_date from fm_order_item_option where item_option_seq = exp_item.option_seq limit 1), (select social_end_date from fm_order_item_suboption where item_suboption_seq = exp_item.suboption_seq limit 1)) as coupon_end_date
					from
						fm_goods_export exp,
						fm_goods_export_item exp_item,
						fm_order_item ord_item
					where
						exp.export_code = exp_item.export_code and
						exp_item.item_seq = ord_item.item_seq ".$addWhere;
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		return $result;
	}

	// 쿠폰 사용내역 저장 및 배송완료 처리
	public function coupon_use_save($param){

		$this->load->helper('order');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->model('membermodel'); 
		$this->load->model('socialcpconfirmmodel');

		$export	= $this->get_coupon_info($param);
		if	(!$export){
			openDialogAlert('쿠폰사용 인증에 실패하였습니다.',400,140,'parent',$callback);
			exit;
		}

		// 가용횟수(금액) 체크
		$chg_coupon_remain_value	= $export['coupon_remain_value'] - $param['use_coupon_value'];
		if	($chg_coupon_remain_value < 0){
			$msg	= '쿠폰 사용횟수를 초과하였습니다.';
			if	($export['coupon_value_type'] == 'price')
				$msg	= '쿠폰 사용금액을 초과하였습니다.';
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		// 사용처
		if	($param['use_coupon_area'] == 'direct')
			$param['use_coupon_area']	= addslashes($param['use_coupon_area_direct']);
		else
			$param['use_coupon_area']	= addslashes($param['use_coupon_area']);

		// 직원정보 추출
		if	($_POST['manager_code']){
			$param['certify_code']	= $_POST['manager_code'];
			$certify				= $this->membermodel->get_certify_manager($param);
			$manager_id				= $certify[0]['manager_id'];
			if	(!$certify[0]['certify_code']){
				openDialogAlert("유효하지 않은 직원코드입니다.",400,140,'parent',$callback);
				exit;
			}
		}elseif($this->managerInfo['manager_id']){
			$param['certify_code']	= $_POST['manager_code'];
			$param['manager_id']	= $this->managerInfo['manager_id'];
			$certify				= $this->membermodel->get_certify_manager($param);
		}else{
			openDialogAlert("직원코드를 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$manager_id					= $certify[0]['manager_id'];
		$manager_name				= $certify[0]['manager_name'];
		$certify_code				= $certify[0]['certify_code'];
		if	(!$certify_code || !$manager_id){
			openDialogAlert("유효하지 않은 확인코드입니다.",400,140,'parent',$callback);
			exit;
		}

		// 값어치 종류가 없는 경우
		if	(!$export['coupon_value_type']){
			$export['coupon_value_type']	= 'pass';
			if	($param['use_coupon_value'] >= 100)	$export['coupon_value_type']	= 'price';
		}

		//쿠푼상품의 필수옵션에서 선택된매장의 수수료가져오기
		$address_commission = $this->goodsmodel->get_option_address_commission($export['goods_seq'],$param['use_coupon_area']);

		// 사용내역 로그 저장
		$insertLogParam['order_seq']			= $export['order_seq'];
		$insertLogParam['export_code']			= $export['export_code'];
		$insertLogParam['coupon_serial']		= $export['coupon_serial'];
		$insertLogParam['coupon_value_type']	= $export['coupon_value_type'];
		$insertLogParam['coupon_use_value']		= $param['use_coupon_value'];
		$insertLogParam['coupon_use_area']		= $param['use_coupon_area'];
		$insertLogParam['coupon_use_memo']		= addslashes($param['use_coupon_memo']);
		$insertLogParam['manager_id']			= $manager_id;
		$insertLogParam['confirm_user']			= $manager_name;
		$insertLogParam['confirm_user_serial']	= $certify_code;
		$insertLogParam['address_commission']	= $address_commission;//지역(주소) 수수료
		$insertLogParam['regist_date']			= date('Y-m-d H:i:s');
		$this->db->insert('fm_goods_coupon_use_log', $insertLogParam);

		// 잔여 값어치 차감
		$updateExportItem['coupon_remain_value']	= $chg_coupon_remain_value;
		$this->db->where('export_item_seq',$export['export_item_seq']);
		$this->db->update('fm_goods_export_item',$updateExportItem);

		$export_code		= $export['export_code'];

		## 출고 및 주문 배송완료 처리
		$data_export	= $this->get_export($export_code);
		if	($export['status'] < 75){

			// 출고 배송완료 처리
			$this->set_status($export_code,'75');

			// 상태별 수량 업데이트 및 주문 상태 변경
			$data_export_item	= $this->get_export_item($export_code);
			foreach($data_export_item as $k => $item){
				if($item['opt_type'] == 'opt')	$mode = 'option';
				else							$mode = 'suboption';

				$minus_ea	= $item['ea'] * -1;
				$this->ordermodel->set_step_ea(75, $item['ea'], $item['option_seq'], $mode);
				$this->ordermodel->set_step_ea($data_export['status'], $minus_ea, $item['option_seq'], $mode);

				// 상품에 구매수량 업데이트
				$this->goodsmodel->get_purchase_ea($item['ea'], $item['goods_seq']);
				$this->ordermodel->set_option_step($item['option_seq'], $mode);
			}

			$this->ordermodel->set_order_step($data_export['order_seq']);

		}

		$data_socialcp_confirm['order_seq'] = $data_export['order_seq'];
		$data_socialcp_confirm['export_seq'] = $data_export['export_seq'];
		$data_socialcp_confirm['doer'] = '자동';
		$socialcp_status = ($chg_coupon_remain_value == 0)?'3':'2';//모두사용 3, 일부사용 2
		$this->socialcpconfirmmodel -> socialcp_confirm('system',$socialcp_status,$export_code);
		$this->socialcpconfirmmodel -> log_socialcp_confirm($data_socialcp_confirm);

		// 잔여 값어치 모두 소진 시 적립금 및 포인트 지급
		if	($chg_coupon_remain_value == 0){
			$tot_reserve		= 0;
			$tot_point			= 0;
			//$data_export		= $this->get_export($export_code);
			$data_export_item	= $this->get_export_item($export_code);
			foreach($data_export_item as $k => $item){
				if($item['opt_type'] == 'opt')	$mode = 'option';
				else							$mode = 'suboption';

				if($data_export['reserve_save'] == 'none'){
					$reserve = 0;
					if($mode == 'option') $reserve = $this->ordermodel->get_option_reserve($item['option_seq']);
					if($mode == 'suboption') $reserve = $this->ordermodel->get_suboption_reserve($item['option_seq']);
					$tot_reserve += $reserve * $item['ea'];

					$point = 0;
					if($mode == 'option') $point = $this->ordermodel->get_option_reserve($item['option_seq'],'point');
					if($mode == 'suboption') $point = $this->ordermodel->get_suboption_reserve($item['option_seq'],'point');
					$tot_point += $point * $item['ea'];
				}
			}

			if($data_export['reserve_save'] == 'none'){
				// 회원 적립금 적립
				$data_order = $this->ordermodel->get_order($data_export['order_seq']);
				if($data_order['member_seq']){
					if($tot_reserve){
						$params_reserve['gb'] = "plus";
						$params_reserve['emoney'] 	= $tot_reserve;
						$params_reserve['memo'] 	= "[".$export_code."] 배송완료";
						$params_reserve['ordno']	= $data_order['order_seq'];
						$params_reserve['type'] 	= "order";
						$params_reserve['limit_date'] 	= get_emoney_limitdate('order');
						$this->membermodel->emoney_insert($params_reserve, $data_order['member_seq']);
					}

					if($tot_point){
						$params_point['gb']		= "plus";
						$params_point['point'] 	= $tot_point;
						$params_point['memo'] 	= "[".$export_code."] 배송완료";
						$params_point['ordno']	= $data_order['order_seq'];
						$params_point['type'] 	= "order";
						$params_point['limit_date'] 	= get_point_limitdate('order');
						$this->membermodel->point_insert($params_point, $data_order['member_seq']);
					}

					if($tot_reserve || $tot_point){
						$query = "update fm_goods_export set reserve_save = 'save' where export_code = ?";
						$this->db->query($query,array($export_code));
					}
				}
			}
		}

		// 쿠폰 상품 배송완료 메일 및 SMS 발송 ( = 사용내역 발송 )
		coupon_send_mail_step75($export_code);
		coupon_send_sms_step75($export_code);
	}

	// 쿠폰사용 내역
	public function get_coupon_use_history($coupon_serial){
		$sql	= "select * from fm_goods_coupon_use_log where coupon_serial = '".$coupon_serial."' order by regist_date desc";
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}

	// 쿠폰상품 발송내역
	public function get_coupon_export_send_log($params, $limit = ''){
		if	($params['order_seq'])
			$addWhere	.= " and log.order_seq = '".$params['order_seq']."' ";
		if	($params['export_code'])
			$addWhere	.= " and log.export_code = '".$params['export_code']."' ";
		if	($params['send_kind'])
			$addWhere	.= " and log.send_kind = '".$params['send_kind']."' ";
		if	($params['status'])
			$addWhere	.= " and log.status = '".$params['status']."' ";
		if	($params['email'])
			$addWhere	.= " and (log.send_kind = 'mail' and log.send_val like '%".$params['email']."%') ";
		if	($params['sms'])
			$addWhere	.= " and (log.send_kind = 'sms' and log.send_val like '%".$params['sms']."%') ";

		// 입점사 검색
		if	($params['provider_seq'])
			$addWhere	.= " and item.provider_seq = '".$params['provider_seq']."' ";

		if	($limit > 0)
			$addLimit	= " LIMIT ".$limit." ";

		$sql	= " select log.* from
						fm_goods_export_send_log log,
						fm_goods_export_item exp,
						fm_order_item item
					where
						log.export_code = exp.export_code and
						exp.item_seq = item.item_seq
					".$addWhere
				." order by log.regist_date desc ".$addLimit;
		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	// 쿠폰 사용가능여부 체크
	public function chk_coupon($param){
		$this->load->model("goodsmodel");
		$this->load->model("returnmodel");
		$coupon		= $this->get_coupon_info($param);
		if	($coupon['coupon_start_date'])
			$start_time	= strtotime($coupon['coupon_start_date'].' 00:00:00');
		if	($coupon['coupon_end_date'])
			$end_time	= strtotime($coupon['coupon_end_date'].' 23:59:59');

		// 해당 쿠폰 정보가 있는지 체크
		if	(!$coupon['export_code'])
			return array('result' => 'fail');

		// 값어치가 남아 있는지 확인
		if	(!$coupon['coupon_remain_value'])
			return array('result' => 'noremain');

		// 유효기간 체크
		if	(!($start_time <= time() && time() <= $end_time)){
			if	(time() > $end_time)	return array('result' => 'expire');
			else						return array('result' => 'notyet');
		}

		// 반품 정보 추출
		if	($coupon['suboption_seq'])
			$returns	= $this->returnmodel->get_return_subitem_ea($coupon['item_seq'], $coupon['suboption_seq'], $coupon['export_code']);
		else
			$returns	= $this->returnmodel->get_return_item_ea($coupon['item_seq'], $coupon['option_seq'], $coupon['export_code']);

		// 환불된 쿠폰인지 확인
		if	($returns['ea'])
			return array('result' => 'refund');

		// 원 상품에서 장소 정보 추출
		$address	= $this->goodsmodel->get_option_address($coupon['goods_seq']);

		$result				= $coupon;
		$result['address']	= $address;
		$result['result']	= 'success';

		return $result;
	}

	// 쿠폰 인증번호 저장
	public function save_coupon_serial($coupon_serial, $export_code){
		$this->load->model('goodsmodel');
		if	($this->goodsmodel->chkDuple_coupon_serial($coupon_serial)){
			$update['export_code']		= $export_code;
			$update['export_date']		= date('Y-m-d H:i:s');
			$this->db->where('coupon_serial',$coupon_serial);
			$this->db->update('fm_goods_coupon_serial',$update);
		}else{
			$insert['coupon_serial']	= $coupon_serial;
			$insert['export_code']		= $export_code;
			$insert['regist_date']		= date('Y-m-d H:i:s');
			$insert['export_date']		= date('Y-m-d H:i:s');
			$this->db->insert('fm_goods_coupon_serial', $insert);
		}
	}
}
/* End of file exportmodel.php */
/* Location: ./app/models/exportmodel.php */
