<?php
class Refundmodel extends CI_Model {
	public function __construct()
	{
		$this->arr_refund_status = array(
			'request'	=> '환불 신청',
			'ing'		=> '환불 처리중',
			'complete'	=> '환불 완료'
		);

		$this->arr_refund_type = array(
			'cancel_payment'	=> '결제취소',
			'return'			=> '반품',
		);

		$this->arr_cancel_type = array(
			'full'		=> '전체취소',
			'partial'	=> '부분취소',
		);
	}

	public function insert_refund($data,$items='')
	{

		$this->db->insert('fm_order_refund', $data);

		$refund_seq = $this->db->insert_id();
		$update_data['refund_code'] = 'C'.date('ymdH').$refund_seq;

		$this->db->where('refund_seq',$refund_seq);
		$this->db->update('fm_order_refund',$update_data);

		if($items){
			foreach($items as $item_data){
				$item_data['refund_code'] = $update_data['refund_code'];
				$this->db->insert('fm_order_refund_item',$item_data);
			}
		}

		return $update_data['refund_code'];
	}

	public function get_refund_able_ea($order_seq){

		$ea = 0;

		$query = $this->db->query("select sum(if(step=25,ea-step85,step35)) as ea from fm_order_item_option where order_seq=? and step in (25,35,40,50,60,70)", $order_seq);
		$option_cancel_able_ea = $query->row_array();
		if($option_cancel_able_ea) $ea += $option_cancel_able_ea['ea'];

		$query = $this->db->query("select sum(if(step=25,ea-step85,step35)) as ea from fm_order_item_suboption where order_seq=? and step in (25,35,40,50,60,70)", $order_seq);
		$suboption_cancel_able_ea = $query->row_array();
		if($suboption_cancel_able_ea) $ea += $suboption_cancel_able_ea['ea'];

		return $ea;
	}

	public function get_refund_list($sc){

		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');

		$sc['page']		= !empty($sc['page'])		? intval($sc['page']):'1';
		$sc['perpage']	= !empty($sc['perpage'])	? intval($sc['perpage']):'10';

		$sqlWhereClause = "";
		$sqlLimitClause = "";

		if(!empty($sc['member_seq'])){
			$sqlWhereClause .= " and o.member_seq = '{$sc['member_seq']}'";
		}
		if(!empty($sc['order_seq'])){
			$sqlWhereClause .= " and o.order_seq = '{$sc['order_seq']}'";
		}

		/* 모바일에서 사용 */
		if(!empty($sc['step_type'])){
			switch($sc['step_type']){
				case "cancel":
					$sqlWhereClause .= " and (o.step in ('95') or
						(
							r.refund_type='cancel_payment'
							and
							r.cancel_type='full'
							and
							o.payment='card'
						)
					)
				";
				case "refund":
					$sqlWhereClause .= " and
					not(
						r.refund_type='cancel_payment'
						and
						r.cancel_type='full'
						and
						o.payment='cart'
					)";
				break;
				case "refund_ing":
					$sqlWhereClause .= " and
					not(
						r.refund_type='cancel_payment'
						and
						r.cancel_type='full'
						and
						o.payment='card'
					)
					and r.status!='complete'
					";
				break;
			}
		}

		if($sqlWhereClause) $sqlWhereClause = "where 1 " . $sqlWhereClause;

		$sql = "
		SELECT * FROM (
			select
			r.*,
			o.payment,
			o.download_seq,
			(
				SELECT goods_name FROM fm_order_item WHERE order_seq=o.order_seq ORDER BY item_seq LIMIT 1
			) goods_name,
			(
				SELECT image FROM fm_order_item WHERE order_seq=o.order_seq ORDER BY item_seq LIMIT 1
			) image,
			(
				SELECT count(item_seq) FROM fm_order_item WHERE order_seq=o.order_seq
			) item_cnt,
			m.userid,
			m.user_name,
			(
				SELECT sum(ea) FROM fm_order_item_option WHERE order_seq=o.order_seq
			) option_ea,
			(
				SELECT sum(ea) FROM fm_order_item_suboption WHERE order_seq=o.order_seq
			) suboption_ea,
			sum(ri.ea) as refund_ea_sum
			from
				fm_order_refund as r
				inner join fm_order as o on r.order_seq = o.order_seq
				inner join fm_member as m on o.member_seq = m.member_seq
				inner join fm_order_refund_item as ri on r.refund_code=ri.refund_code
			{$sqlWhereClause}
			group by r.refund_code
		) t
		ORDER BY regist_date DESC
		";

		$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		$result['page']['querystring'] = get_args_list();

		foreach($result['record'] as $k => $data)
		{
			$no++;

			$result['record'][$k]['mpayment'] = $this->arr_payment[$result['record'][$k]['payment']];
			$result['record'][$k]['mstatus'] = $this->arr_refund_status[$result['record'][$k]['status']];
			$result['record'][$k]['mtype'] = $this->arr_refund_type[$result['record'][$k]['refund_type']] . " 환불";
			$result['record'][$k]['mrefund_date'] = $result['record'][$k]['refund_date']=='0000-00-00' ? '' : $result['record'][$k]['refund_date'];

			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !(is_file($data['image'])) ) {
				$result['record'][$k]['image'] = viewImg($data['goods_seq'],'thumbCart');
			}

		}

		if($result['record'])
		{
			$result['record'][$k]['end'] = true;
			foreach($result['record'] as $k => $data){
				$result['record'][$k]['no'] = $no;
				$no--;
			}
		}

		return $result;
	}

	public function get_refund($refund_code)
	{
		$query = "select ref.*,ord.member_seq,mem.userid,mem.user_name,mem.emoney,mgp.group_name
		from fm_order_refund as ref
		left join fm_order as ord on ref.order_seq = ord.order_seq
		left join fm_member as mem on ord.member_seq = mem.member_seq
		left join fm_member_group mgp on mem.group_seq = mgp.group_seq
		where refund_code=? limit 1";
		$query = $this->db->query($query,array($refund_code));
		list($result) = $query -> result_array();
		return $result;
	}

	public function get_refund_price_for_order($order_seq,$type,$status)
	{
		$query = "select sum(refund_price + refund_emoney) refund_price from fm_order_refund where order_seq=? and refund_type=? and status=?";
		$query = $this->db->query($query,array($order_seq,$type,$status));
		list($result) = $query -> result_array();
		return $result['refund_price'];
	}

	public function get_refund_ea_for_order($order_seq,$type,$status)
	{
		$query = "select sum(ea) as refund_ea from fm_order_refund_item where refund_code in (select refund_code from fm_order_refund where order_seq=? and refund_type=? and status=?)";
		$query = $this->db->query($query,array($order_seq,$type,$status));
		list($result) = $query -> result_array();
		return $result['refund_ea'];
	}

	public function get_refund_option_ea($shipping_seq,$item_seq,$item_option_seq){
		$query = "select sum(ea) as 'ea' from fm_order_refund_item where item_seq=? and option_seq=?";
		$values = array($item_seq,$item_option_seq);

		if($shipping_seq){
			$query .= " and shipping_seq=?";
			$values[] = $shipping_seq;
		}
		$query = $this->db->query($query,$values);

		$result = $query->row_array();

		return $result['ea'];
	}

	public function get_refund_suboption_ea($shipping_seq,$item_seq,$item_suboption_seq){
		$query = "select sum(ea) as 'ea' from fm_order_refund_item where item_seq=? and suboption_seq=?";
		$values = array($item_seq,$item_suboption_seq);
		if($shipping_seq){
			$query .= " and shipping_seq=?";
			$values[] = $shipping_seq;
		}
		$query = $this->db->query($query,$values);

		$result = $query->row_array();
		return $result['ea'];
	}

	
	public function get_refund_item_data($refund_code,$item_seq,$item_option_seq){
		$query = "select * from fm_order_refund_item where refund_code=? and item_seq=? and option_seq=?";
		$values = array($refund_code,$item_seq,$item_option_seq);
		$query = $this->db->query($query,$values);

		$result = $query->row_array();

		return $result;
	}

	public function get_refund_item($refund_code)
	{

		$query1 = "
		SELECT
		'opt' opt_type,
		opt.item_option_seq option_seq,
		opt.supply_price,
		opt.consumer_price,
		opt.price as price,
		opt.download_seq,
		opt.coupon_sale,
		opt.member_sale as member_sale,
		opt.fblike_sale,
		opt.mobile_sale,
		opt.promotion_code_sale,
		opt.referer_sale,
		opt.reserve as reserve,
		opt.point as point,
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
		opt.ea option_ea,
		opt.refund_ea,
		opt.fblike_sale,
		opt.mobile_sale,
		opt.referer_sale,
		opt.promotion_code_sale,
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
		item.goods_seq,
		item.goods_kind,
		item.socialcp_input_type,
		item.socialcp_use_return,
		item.socialcp_use_emoney_day,
		item.socialcp_use_emoney_percent,
		item.item_seq,
		item.goods_shipping_cost,
		item.shipping_policy,
		item.shipping_unit,
		item.basic_shipping_cost,
		item.add_shipping_cost,
		item.goods_name,
		item.image,
		item.event_seq,
		item.goods_kind, 
		(select goods_type from fm_goods where goods_seq = item.goods_seq) as goods_type,
		ref.coupon_refund_type,
		ref.coupon_refund_emoney,
		ref.coupon_remain_price,
		ref.coupon_deduction_price,
		ref.cancel_memo,
		ref.ea,
		IFNULL(opt.unit_emoney,0) unit_emoney,
		IFNULL(opt.unit_cash,0) unit_cash,
		IFNULL(opt.unit_enuri,0) unit_enuri
		FROM
		fm_order_refund_item ref,fm_order_item_option opt,fm_order_item item
		WHERE
		ref.option_seq is not null
		AND ref.option_seq = opt.item_option_seq
		AND opt.item_seq = item.item_seq
		AND ref.refund_code = ?
		";
		$query2 = "
		SELECT
		'sub' opt_type,
		sub.item_suboption_seq option_seq,
		sub.supply_price,
		sub.consumer_price,
		sub.price as price,
		'' download_seq,
		0 coupon_sale,
		sub.member_sale as member_sale,
		0 fblike_sale,
		0 mobile_sale,
		0 promotion_code_sale,
		0 referer_sale,
		sub.reserve as reserve,
		sub.point as point,
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
		sub.ea option_ea,
		sub.refund_ea,
		0 fblike_sale,
		0 mobile_sale,
		0 referer_sale,
		0 promotion_code_sale,
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
		item.goods_seq,
		item.goods_kind,
		item.socialcp_input_type,
		item.socialcp_use_return,
		item.socialcp_use_emoney_day,
		item.socialcp_use_emoney_percent,
		item.item_seq,
		item.goods_shipping_cost,
		item.shipping_policy,
		item.shipping_unit,
		item.basic_shipping_cost,
		item.add_shipping_cost,
		item.goods_name,
		item.image,
		item.event_seq,
		item.goods_kind, 
		(select goods_type from fm_goods where goods_seq = item.goods_seq) as goods_type,
		ref.coupon_refund_type,
		ref.coupon_refund_emoney,
		ref.coupon_remain_price,
		ref.coupon_deduction_price,
		ref.cancel_memo,
		ref.ea,
		IFNULL(sub.unit_emoney,0) unit_emoney,
		IFNULL(sub.unit_cash,0) unit_cash,
		IFNULL(sub.unit_enuri,0) unit_enuri
		FROM
		fm_order_refund_item ref,fm_order_item_suboption sub,fm_order_item item
		WHERE
		ref.suboption_seq is not null
		AND ref.suboption_seq = sub.item_suboption_seq
		AND sub.item_seq = item.item_seq
		AND ref.refund_code = ?
		";

		$query = "(".$query1.") union all (".$query2.")";
		$query = $this->db->query($query,array($refund_code,$refund_code));
		foreach($query->result_array() as $data){
			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !(is_file($data['image'])) ) {
				$data['image'] = viewImg($data['goods_seq'],'thumbCart');
			}
			$result[] = $data;
		}

		return $result;
	}

	public function get_refund_shipping_cost($data_order,$data_order_item,$data_refund,$data_refund_item){

		$isFullCancel = $this->isFullCancel($data_refund['cancel_type'],$data_order['order_seq']) ? true : false; // 부분취소를 여러번해서 결국 전체취소가 될 경우도 "FullCancel에 해당"

		if($data_order['international']=='international'){
			$data_order['real_shipping_cost'] = $data_order['international_cost'];
		}else{
			$data_order['real_shipping_cost'] = $data_order['shipping_cost'];
		}

		/* 유료배송정책 */
		if($data_order['delivery_if']==0 && $data_order['delivery_cost']>0)
		{
			/* 결제취소에 의한 환불일 경우 */
			if($data_refund['refund_type']=='cancel_payment')
			{
				/*
				 전체취소이거나,
				 부분취소시 더이상 기본배송상품이 없게 될 경우
				*/
				if($isFullCancel || $this->willBeAllGoodsShipping($data_order,$data_order_item,$data_refund,$data_refund_item))
				{
					return $data_order['real_shipping_cost'];
				}
				else
				{
					return 0;
				}
			}
			/* 반품에 의한 환불일 경우 */
			else
			{
				return 0;
			}
		}
		/* 조건부무료배송정책 */
		else if($data_order['delivery_if']>0)
		{
			/* 결제취소에 의한 환불일 경우 */
			if($data_refund['refund_type']=='cancel_payment')
			{
				/* 부분취소이며, 취소시 무료배송조건이 안맞게 될 경우  */
				if(!$isFullCancel && $this->willBeConditionsInvalid($data_order,$data_order_item,$data_refund,$data_refund_item))
				{
					return -$data_order['delivery_cost'];
				}
				elseif($data_order['delivery_cost']>0 && $isFullCancel || $this->willBeAllGoodsShipping($data_order,$data_order_item,$data_refund,$data_refund_item))
				{
					return $data_order['real_shipping_cost'];
				}
				else
				{
					return 0;
				}
			}else{
				return 0;
			}
		}
		/* 무료배송정책 */
		else
		{
			return 0;
		}
	}

	// 전체취소 여부 반환
	public function isFullCancel($cancel_type,$order_seq){
		if($cancel_type=='full' || $this->get_refund_able_ea($order_seq)==0) return true;
		else return false;
	}

	/* 취소후 더이상 기본배송상품이 없게 될지 여부 체크 */
	public function willBeAllGoodsShipping($data_order,$data_order_item,$data_refund,$data_refund_item,$data_order_shipping){
		$total_ea = 0;		//총 기본 배송상품개수(기취소된개수 제외)
		$cancel_ea = 0;		//취소후 총 기본배송상품 취소수
		$cancel_ea_before = 0;		//기취소된 기본배송상품개수

		foreach($data_order_item as $k=>$item)
		{
			if($item['shipping_policy']=='shop')
			{
				$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
				$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);

				if($options) foreach($options as $option){
					$total_ea += $option['ea'];
				}

				if($suboptions) foreach($suboptions as $suboption){
					$total_ea += $suboption['ea'];
				}
			}
		}

		foreach($data_refund_item as $k=>$data)
		{
			if($data['shipping_policy']=='shop')
			{
				$cancel_ea_before	+= $data['refund_ea']-$data['ea'];
				$cancel_ea			+= $data['refund_ea'];
			}
		}

		$remain_ea_before = $total_ea-$cancel_ea_before; // 취소처리 전 기본배송상품 수
		$remain_ea_after = $total_ea-$cancel_ea; // 취소처리 후 총 기본배송상품 수

		return $remain_ea_before && $remain_ea_after==0 ? true : false;
	}

	/* 취소후 무료배송조건이 안맞게 되는지 여부 체크 */
	public function willBeConditionsInvalid($data_order,$data_order_item,$data_refund,$data_refund_item){
		$total_price = 0; // 총 상품가격
		$cancel_price = 0; // 취소후 총 취소상품가격
		$cancel_price_before = 0; // 기취소된 상품가격

		foreach($data_order_item as $k=>$item)
		{
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);

			if($options) foreach($options as $option){
				$total_price += ($option['price']*$option['ea']);
			}

			if($suboptions) foreach($suboptions as $suboption){
				$total_price += ($suboption['price']*$suboption['ea']);
			}
		}

		foreach($data_refund_item as $k=>$data)
		{
			if($data['shipping_policy']=='shop')
			{
				$cancel_price_before	+= $data['price']*($data['refund_ea']-$data['ea']);
				$cancel_price			+= $data['price']*$data['refund_ea'];
			}
		}

		$result_price_before	= $total_price-$cancel_price_before; // 취소처리 전 기취소 조건금액
		$result_price_after		= $total_price-$cancel_price; // 취소처리 후 총 취소 조건금액

		if($data_order['real_shipping_cost']==0){
			if($result_price_before >= $data_order['delivery_if'] && $result_price_after < $data_order['delivery_if']){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function get_refund_for_order($order_seq)
	{
		if( defined('__SELLERADMIN__') === true ){
			$query = "
			select r.*,
			sum(i.ea) ea,
			(select mname from fm_manager where manager_seq=r.manager_seq) admin
			from
			fm_order_refund r,
			fm_order_refund_item i
			left join fm_order_item_option opt on opt.item_option_seq=i.option_seq
			left join fm_order_item_suboption sub on sub.item_suboption_seq=i.suboption_seq
			LEFT JOIN fm_order_item orditem ON orditem.item_seq = i.item_seq
			where r.refund_code=i.refund_code and r.order_seq=? and orditem.provider_seq=? group by r.refund_code";
			$query = $this->db->query($query,array($order_seq, $this->providerInfo['provider_seq']));
		}else{
			$query = "
			select r.*,
			sum(i.ea) ea,
			(select mname from fm_manager where manager_seq=r.manager_seq) admin
			from
			fm_order_refund r,
			fm_order_refund_item i
			left join fm_order_item_option opt on opt.item_option_seq=i.option_seq
			left join fm_order_item_suboption sub on sub.item_suboption_seq=i.suboption_seq
			where r.refund_code=i.refund_code and r.order_seq=? group by r.refund_code";
			$query = $this->db->query($query,array($order_seq));
		}
		foreach($query -> result_array() as $data) $result[] = $data;
		return $result;
	}


	/* KCP 결제 취소 */
	public function kcp_cancel($data_order,$data_refund){
		$cancel_params = array();
		$cancel_params['req_tx']	= 'mod';
		$cancel_params['mod_type']	= $data_refund['cancel_type']=='partial' ? 'STPC' : 'STSC'; // PG 전체취소 : STSC, 신용카드 부분취소 : STPC
		$cancel_params['tno']		= $data_order['pg_transaction_number'];
		$cancel_params['mod_desc']	= $data_refund['cancel_type']=='partial' ? '부분결제취소' : '전체취소';

		// 에스크로 취소 시 타입 변경
		if($cancel_params['mod_type'] == 'STSC' && preg_match('/escrow/', $data_refund['refund_method']))
			$cancel_params['mod_type']	= 'STE2';	// 즉시취소 (배송전취소)

		if($data_refund['cancel_type']=='partial'){
			$cancel_params['mod_mny']	= $data_refund['refund_price'];
			$cancel_params['rem_mny']	= $data_order['settleprice'];

			/* 기 부분매입취소된 금액 제외 */
			$query = "select sum(refund_price) as sum_refund_price from fm_order_refund where `status`='complete' and order_seq=?";
			$query = $this->db->query($query,array($data_order['order_seq']));
			$res = $query->row_array();
			if($res['sum_refund_price']){
				$cancel_params['rem_mny'] -= $res['sum_refund_price'];
			}
		}

		$pg = config_load($this->config_system['pgCompany']);

		/* bin 디렉토리 전까지의 경로를 입력,절대경로 입력 */
		$g_conf_home_dir  = dirname(__FILE__)."/../../pg/kcp/";
		/* 테스트  : testpaygw.kcp.co.kr
		 * 실결제  : paygw.kcp.co.kr */
		$g_conf_gw_url    = $pg['mallCode']=='T0000' ? "testpaygw.kcp.co.kr" : "paygw.kcp.co.kr";
		/* 테스트  : https://pay.kcp.co.kr/plugin/payplus_test.js
		 * 실결제  : https://pay.kcp.co.kr/plugin/payplus.js */
		$g_conf_js_url	  = $pg['mallCode']=='T0000' ? "https://pay.kcp.co.kr/plugin/payplus_test_un.js" : "https://pay.kcp.co.kr/plugin/payplus_un.js";
		/* 테스트 T0000 */
		$g_conf_site_cd   = $pg['mallCode'];
		/* 테스트 3grptw1.zW0GSo4PQdaGvsF__ */
		$g_conf_site_key  = $pg['merchantKey'];
		$g_conf_site_name = $this->config_basic['shopName'];
		$g_conf_log_level = "3";           // 변경불가
		$g_conf_gw_port   = "8090";        // 포트번호(변경불가)

		require dirname(__FILE__)."/../../pg/kcp/sample/pp_ax_hub_lib.php"; // library [수정불가]

		/* ============================================================================== */
		/* =   01. 취소 요청 정보 설정                                                  = */
		/* = -------------------------------------------------------------------------- = */
		$req_tx         = $cancel_params['req_tx'];					  // 요청 종류
		$cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
		/* = -------------------------------------------------------------------------- = */
		$res_cd         = "";                         // 응답코드
		$res_msg        = "";                         // 응답메시지
		$res_en_msg     = "";                         // 응답 영문 메세지
		$tno            = $cancel_params['tno']; // KCP 거래 고유 번호
		/* = -------------------------------------------------------------------------- = */
		$mod_type       = $cancel_params['mod_type']; 						 // 변경TYPE VALUE 승인취소시 필요
		$mod_desc       = $cancel_params['mod_desc']; // 변경사유
		$mod_mny		= $cancel_params['mod_mny']; // 취소 요청 금액
		$rem_mny		= $cancel_params['rem_mny']; //취소 가능 잔액
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
		if ( $req_tx == "mod" )
		{
			$tran_cd = "00200000";

			$c_PayPlus->mf_set_modx_data( "tno",      $tno      ); // KCP 원거래 거래번호
			$c_PayPlus->mf_set_modx_data( "mod_type", $mod_type ); // 원거래 변경 요청 종류
			$c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  ); // 변경 요청자 IP
			$c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc ); // 변경 사유

			if ( $mod_type == "STPC" ) // 부분취소의 경우
            {
                $c_PayPlus->mf_set_modx_data( "mod_mny", $mod_mny ); // 취소요청금액
                $c_PayPlus->mf_set_modx_data( "rem_mny", $rem_mny ); // 취소가능잔액
            }
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
								  $cust_ip, "3" , 0, 0, $g_conf_key_dir, $g_conf_log_dir); // 응답 전문 처리

			$success = $c_PayPlus->m_res_cd=='0000' ? true : false;
			$res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
			$res_msg = $c_PayPlus->m_res_msg; // 결과 메시지
			/* $res_en_msg = $c_PayPlus->mf_get_res_data( "res_en_msg" );  // 결과 영문 메세지 */
		}
		else
		{
			$success = false;
			$res_cd = $c_PayPlus->m_res_cd  = "9562";
			$res_msg = $c_PayPlus->m_res_msg = "연동 오류|Payplus Plugin이 설치되지 않았거나 tran_cd값이 설정되지 않았습니다.";
		}


		/* = -------------------------------------------------------------------------- = */
		/* =   04. 실행 END                                                             = */
		/* ============================================================================== */

		/* ============================================================================== */
	    /* =   05. 취소 결과 처리                                                       = */
	    /* = -------------------------------------------------------------------------- = */
	    if ( $req_tx == "mod" )
	    {
			if ( $res_cd == "0000" )
			{
				$tno = $c_PayPlus->mf_get_res_data( "tno" );  // KCP 거래 고유 번호

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-1. 부분취소 결과 처리                                                 = */
	    /* = -------------------------------------------------------------------------- = */
				if ( $mod_type == "STPC" ) // 부분취소의 경우
				{
					$amount  = $c_PayPlus->mf_get_res_data( "amount"       ); // 원 거래금액
					$mod_mny = $c_PayPlus->mf_get_res_data( "panc_mod_mny" ); // 취소요청된 금액
					$rem_mny = $c_PayPlus->mf_get_res_data( "panc_rem_mny" ); // 취소요청후 잔액
				}
			} // End of [res_cd = "0000"]

	    /* = -------------------------------------------------------------------------- = */
	    /* =   05-2. 취소 실패 결과 처리                                                = */
	    /* = -------------------------------------------------------------------------- = */
			else
			{
			}
		} // End of Process

		return array(
			'success'=>$success,
			'result_code'=>$res_cd,
			'result_msg'=>iconv('euc-kr','utf-8',$res_msg)
		);
	}

	/* LG 결제 취소 */
	public function lg_cancel($data_order,$data_refund){

		$cancel_params = array();
		$cancel_params['LGD_TXNAME']		= $data_refund['cancel_type']=='partial' ? 'PartialCancel' : 'Cancel'; // PG 전체취소 : STSC, 신용카드 부분취소 : RN07
		$cancel_params['LGD_TID']			= $data_order['pg_transaction_number'];
		$cancel_params['LGD_CANCELREASON']	= $data_refund['cancel_type']=='partial' ? '부분결제취소' : '전체취소';

		if($data_refund['cancel_type']=='partial'){
			$cancel_params['LGD_CANCELAMOUNT']	= $data_refund['refund_price'];
			$cancel_params['LGD_REMAINAMOUNT']	= $data_order['settleprice']; // 취소전남은금액

			/* 기 부분매입취소된 금액 제외 */
			$query = "select sum(refund_price) as sum_refund_price from fm_order_refund where `status`='complete' and order_seq=?";
			$query = $this->db->query($query,array($data_order['order_seq']));
			$res = $query->row_array();
			if($res['sum_refund_price']){
				$cancel_params['LGD_REMAINAMOUNT'] -= $res['sum_refund_price'];
			}
		}

		$pg = config_load($this->config_system['pgCompany']);

		/*
	     * [결제취소 요청 페이지]
	     *
	     * LG유플러스으로 부터 내려받은 거래번호(LGD_TID)를 가지고 취소 요청을 합니다.(파라미터 전달시 POST를 사용하세요)
	     * (승인시 LG유플러스으로 부터 내려받은 PAYKEY와 혼동하지 마세요.)
	     */
	    $CST_PLATFORM               = 'service';       //LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
	    $CST_MID                    = $pg['mallCode'];            //상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
	                                                                         //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
	    $LGD_MID                    = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;  //상점아이디(자동생성)
	    $LGD_TID                	= $cancel_params["LGD_TID"];			 //LG유플러스으로 부터 내려받은 거래번호(LGD_TID)

	 	$configPath 				= dirname(__FILE__)."/../../pg/lgdacom/";	 //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.

	    require_once($configPath."XPayClient.php");

	    $xpay = &new XPayClient($configPath, $CST_PLATFORM);
	    $xpay->Init_TX($LGD_MID);

	    $xpay->Set("LGD_TXNAME", $cancel_params['LGD_TXNAME']);
	    $xpay->Set("LGD_TID", $LGD_TID);

	    /* 부분취소 파라미터 */
	    if($data_refund['cancel_type']=='partial'){
			$xpay->Set("LGD_CANCELAMOUNT", $cancel_params['LGD_CANCELAMOUNT']);
			$xpay->Set("LGD_REMAINAMOUNT", $cancel_params['LGD_REMAINAMOUNT']);
			//$xpay->Set("LGD_CANCELTAXFREEAMOUNT", $LGD_CANCELTAXFREEAMOUNT);
			$xpay->Set("LGD_CANCELREASON", $cancel_params['LGD_CANCELREASON']);
			//$xpay->Set("LGD_RFACCOUNTNUM", $LGD_RFACCOUNTNUM);		//환불계좌 번호(가상계좌 환불인경우만 필수)
			//$xpay->Set("LGD_RFBANKCODE", $LGD_RFBANKCODE);			//환불계좌 은행코드(가상계좌 환불인경우만 필수)
			//$xpay->Set("LGD_RFCUSTOMERNAME", $LGD_RFCUSTOMERNAME);	//환불계좌 예금주(가상계좌 환불인경우만 필수)
			//$xpay->Set("LGD_RFPHONE", $LGD_RFPHONE);					//요청자 연락처(가상계좌 환불인경우만 필수)
		}

		if($xpay->TX())
		{
			$res_cd  = $xpay->Response_Code();  // 결과 코드
			$res_msg = $xpay->Response_Msg(); // 결과 메시지

			$success = $res_cd=='0000' ? true : false;
		}
		else
		{
			$success = false;
		}

		return array(
			'success'=>$success,
			'result_code'=>$res_cd,
			'result_msg'=>iconv('euc-kr','utf-8',$res_msg)
		);
	}

	//public function allat_

	/* Allat 결제 취소 */
	public function allat_cancel($data_order,$data_refund){

		if(!$_POST["allat_enc_data"]) {
			openDialogAlert('allat_enc_data 누락',400,140,'top','');
			exit;
		}

		$cancel_params = array();

		$pg = config_load($this->config_system['pgCompany']);

		// 올앳관련 함수 Include
		//----------------------
		include  dirname(__FILE__)."/../../pg/allat/allatutil.php";
		//Request Value Define
		//----------------------

		/********************* Service Code *********************/
		$at_cross_key 	= $pg['merchantKey'];    //설정필요 [사이트 참조 - http://www.allatpay.com/servlet/AllatBiz/support/sp_install_guide_scriptapi.jsp#shop]
		$at_shop_id   	= $pg['mallCode'];       //설정필요
		/*********************************************************/

		// 요청 데이터 설정
		//----------------------

		$cancel_params['allat_shop_id']		= $at_shop_id;
		$cancel_params['allat_enc_data']	= $_POST["allat_enc_data"];
		$cancel_params['allat_cross_key']	= $at_cross_key;

		$at_data_array = array();
		foreach($cancel_params as $k=>$v) $at_data_array[] = "{$k}={$v}";

		$at_data   = implode("&",$at_data_array);

		// 올앳 결제 서버와 통신 : ApprovalReq->통신함수, $at_txt->결과값
		//----------------------------------------------------------------
		$at_txt = CancelReq($at_data,"SSL");

		// 결제 결과 값 확인
		//------------------
		$REPLYCD   =getValue("reply_cd",$at_txt);
		$REPLYMSG  =getValue("reply_msg",$at_txt);

		// 결과 값이 '0000'이면 정상임. 단, allat_test_yn=Y 일경우 '0001'이 정상임.
		// 실제 취소   : allat_test_yn=N 일 경우 reply_cd=0000 이면 정상
		// 테스트 취소 : allat_test_yn=Y 일 경우 reply_cd=0001 이면 정상
		//----------------------------------------------------------------------------------------

		if( $pg['mallCode'] == 'FM_pgfreete2' ) $sucess_code = "0001";
		else $sucess_code = "0000";

		if( !strcmp($REPLYCD,$sucess_code) ){
			$success = true;
		}
		else
		{
			$success = false;
		}

		$res_cd  = $REPLYCD;  // 결과 코드
		$res_msg = $REPLYMSG; // 결과 메시지

		return array(
			'success'=>$success,
			'result_code'=>$res_cd,
			'result_msg'=>iconv('euc-kr','utf-8',$res_msg)
		);
	}

	/* INICIS 결제 취소 */
	public function inicis_cancel($data_order,$data_refund){

		$cancel_params = array();
		$cancel_params['type']		= $data_refund['cancel_type']=='partial' ? 'repay' : 'cancel'; // PG 전체취소 : STSC, 신용카드 부분취소 : RN07
		$cancel_params['tid']		= $data_order['pg_transaction_number'];
		$cancel_params['cancelmsg']	= $data_refund['cancel_type']=='partial' ? '부분결제취소' : '전체취소';

		if($data_refund['cancel_type']=='partial'){
			$cancel_params['price']	= $data_refund['refund_price'];
			$cancel_params['confirm_price']	= $data_order['settleprice']-$data_refund['refund_price'];

			/* 기 부분매입취소된 금액 제외 */
			$query = "select sum(refund_price) as sum_refund_price from fm_order_refund where `status`='complete' and order_seq=?";
			$query = $this->db->query($query,array($data_order['order_seq']));
			$res = $query->row_array();
			if($res['sum_refund_price']){
				$cancel_params['confirm_price'] -= $res['sum_refund_price'];
			}
		}

		$pg = config_load($this->config_system['pgCompany']);

		if(preg_match("/^escrow/",$data_order['payment'])){
			$mallCode = $pg['escrowMallCode'];
			$merchantKey = $pg['escrowMerchantKey'];
		}
		else{
			$mallCode = $pg['mallCode'];
			$merchantKey = $pg['merchantKey'];
		}

		/**************************
		 * 1. 라이브러리 인클루드 *
		 **************************/
		require(dirname(__FILE__)."/../../pg/inicis/libs/INILib.php");
		$HTTP_SESSION_VARS = $_SESSION;

		/***************************************
		 * 2. INIpay41 클래스의 인스턴스 생성 *
		 ***************************************/
		$inipay = new INIpay50;

		/***********************
		 * 3. 재승인 정보 설정 *
		 ***********************/
	  	$inipay->SetField("inipayhome", dirname(__FILE__)."/../../pg/inicis");  	// 이니페이 홈디렉터리(상점수정 필요)
	  	$inipay->SetField("type", $cancel_params['type']);                          // 고정 (절대 수정 불가)

		$inipay->SetField("subpgip","203.238.3.10");                    			// 고정
	  	$inipay->SetField("debug", "true");                             			// 로그모드("true"로 설정하면 상세로그가 생성됨.)
	  	$inipay->SetField("mid", $mallCode);                                 		// 상점아이디
	  	$inipay->SetField("admin", $merchantKey);    								// 키패스워드(상점아이디에 따라 변경)

	  	if($cancel_params['type']=='repay'){
		  	$inipay->SetField("pgid", "INIphpRPAY");                      	 	 	// 고정 (절대 수정 불가)
		  	$inipay->SetField("oldtid", $cancel_params['tid']);                     // 취소할 거래의 거래아이디
			$inipay->SetField("currency", 'WON');                      			 	// 화폐단위
			$inipay->SetField("price", $cancel_params['price']);                 	// 취소금액
			$inipay->SetField("confirm_price", $cancel_params['confirm_price']); 	// 승인요청금액
			$inipay->SetField("buyeremail",$data_order['order_email']);          	// 구매자 이메일 주소
	  	}else{
	  		$inipay->SetField("tid", $cancel_params['tid']);                		// 취소할 거래의 거래아이디
			$inipay->SetField("cancelmsg", $cancel_params['cancelmsg']);    		// 취소사유
	  	}

		//$inipay->SetField("no_acct",$no_acct); //국민은행 부분취소 환불계좌번호
		//$inipay->SetField("nm_acct",$nm_acct); //국민은행 부분취소 환불계좌주명

		/******************
		 * 4. 재승인 요청 *
		 ******************/
		$inipay->startAction();


		/*********************************************************************
		 * 5. 재승인 결과                                                    *
		 *                                                                   *
		 * 신거래번호 : $inipay->getResult('TID')                            *
		 * 결과코드 : $inipay->getResult('ResultCode') ("00"이면 재승인 성공)*
		 * 결과내용 : $inipay->getResult('ResultMsg') (결과에 대한 설명)     *
		 * 원거래 번호 : $inipay->getResult('PRTC_TID')                      *
		 * 최종결제 금액 : $inipay->getResult('PRTC_Remains')                *
		 * 부분취소 금액 : $inipay->getResult('PRTC_Price')                  *
		 * 부분취소,재승인 구분값 : $inipay->getResult('PRTC_Type')          *
		 *                          ("0" : 재승인, "1" : 부분취소)           *
		 * 부분취소 요청횟수 : $inipay->getResult('PRTC_Cnt')                *
		 *********************************************************************/

		// 결제 결과 값 확인
		//------------------
		$REPLYCD   = $inipay->getResult('ResultCode');
		$REPLYMSG  = $inipay->getResult('ResultMsg');

		// 결과 값이 '0000'이면 정상임. 단, allat_test_yn=Y 일경우 '0001'이 정상임.
		// 실제 취소   : allat_test_yn=N 일 경우 reply_cd=0000 이면 정상
		// 테스트 취소 : allat_test_yn=Y 일 경우 reply_cd=0001 이면 정상
		//----------------------------------------------------------------------------------------

		if( !strcmp($REPLYCD,'00') ){
			$success = true;
		}
		else
		{
			$success = false;
		}

		$res_cd  = $REPLYCD;  // 결과 코드
		$res_msg = $REPLYMSG; // 결과 메시지

		return array(
			'success'=>$success,
			'result_code'=>$res_cd,
			'result_msg'=>iconv('euc-kr','utf-8',$res_msg)
		);
	}

	public function kspay_cancel($data_order,$data_refund){

		require dirname(__FILE__)."/../../pg/kspay/KSPayEncApprovalCancel4.inc"; // library [수정불가]

		//ipgExec 실행파일 절대경로설정: 권한777, other에 실행권한이 필요
		//$EXEC_DIR = "/home/semuplus/public_html/pg/php_host/sample/kspay_client/ipgExec";

		//로그파일 디렉토리 절대경로설정: 권한777, other에 read,write권한이 필요
		//$LOG_DIR = "/web/okgiro/install/pg_apache/htdocs/test/log";

		$KSPAY_IPADDR	= "210.181.28.137";//운영:210.181.28.137, 테스트:210.181.28.116
		$KSPAY_PORT		= 21001;
		$cancel_type	= "0";
		$filler			= "";
		if($data_refund['cancel_type']=='partial'){
			$cancel_params['price']			= $data_refund['refund_price'];
			$cancel_params['confirm_price']	= $data_order['settleprice'];

			/* 기 취소된 횟수 */
			$query		= "select count(*) as cnt from fm_order_refund where `status`='complete' and order_seq=?";
			$query		= $this->db->query($query,array($data_order['order_seq']));
			$res		= $query->row_array();
			$cancel_cnt	= $res['cnt']+1;

			$filler		= substr("00000000".$data_refund['refund_price'],-9).substr("00".$cancel_cnt,-2);
			$cancel_type	= "3";
		}

		// Default-------------------------------------------------------
		$EncType		= "2";     // 0: 암화안함, 1:openssl, 2: seed
		$Version		= "0210";  // 전문버전
		$VersionType	= "00";    // 구분
		$Resend			= "0";     // 전송구분 : 0 : 처음,  2: 재전송

		$RequestDate	= strftime("%Y%m%d%H%M%S");
		$KeyInType		= "K";   // KeyInType 여부 : S : Swap, K: KeyInType
		$LineType		= "1";   // lineType 0 : offline, 1:internet, 2:Mobile
		$ApprovalCount	= "1";   // 복합승인갯수
		$GoodType		= "0";   // 제품구분 0 : 실물, 1 : 디지털
		$HeadFiller		= "";    // 예비
		//-------------------------------------------------------------------------------

		// Header (입력값 (*) 필수항목)--------------------------------------------------
		$StoreId		= $_POST["storeid"];     	// *상점아이디
		$OrderNumber	= ""; 						// *주문번호
		$UserName		= "";   					// *주문자명
		$IdNum			= "";       				// 주민번호 or 사업자번호
		$Email			= "";       				// *email
		$GoodName		= "";    					// *제품명
		$PhoneNo		= "";     					// *휴대폰번호
		// Header end -------------------------------------------------------------------

		// Data Default(수정항목이 아님)-------------------------------------------------
		$ApprovalType	= $_POST["authty"]; // 승인구분
		$TransactionNo	= $_POST["trno"];   // 거래번호
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
			$cancel_type,       // CancelType,	: 취소처리구분 1:거래번호, 2:주문번호, 3.부분취소
			$TransactionNo,     // TransactionNo,: 거래번호
			"",                 // TradeDate,	: 거래일자
			"",                 // OrderNumber,	: 주문번호
			$filler);           // Filler)		: 기타

		$rStatus	= "X";
		$rMessage	= "C취소거절/잠시후재시도하세요";
		$success	= false;

		if ($ipg->SendEncSocket()){
		//if ($ipg->SendExecSocket($EXEC_DIR ,$LOG_DIR)){
			if (substr($ApprovalType,0,1) == "1" || substr($ApprovalType,0,1) == "I"){
				$rStatus	= $ipg->Status;//성공여부(O/X/S)
				$rMessage	= iconv('euc-kr','utf-8',$ipg->Message1) . "/"
							. iconv('euc-kr','utf-8',$ipg->Message2);
			}elseif (substr($ApprovalType,0,1) == "6"){
				$rStatus	= $ipg->VAStatus;//성공여부(O/X/S)
				$rMessage	= iconv('euc-kr','utf-8',$ipg->VAMessage1) . "/"
							. iconv('euc-kr','utf-8',$ipg->VAMessage2);
			}elseif (substr($ApprovalType,0,1) == "2"){
				$rStatus	= $ipg->ACStatus;//성공여부(O/X/S)
				$rMessage	= iconv('euc-kr','utf-8',$ipg->ACMessage1) . "/"
							. iconv('euc-kr','utf-8',$ipg->ACMessage2);
			}elseif (substr($ApprovalType,0,1) == "H"){
				$rStatus	= $ipg->HStatus;//성공여부(O/X/S)
				$rMessage	= iconv('euc-kr','utf-8',$ipg->HMessage1) . "/"
							. iconv('euc-kr','utf-8',$ipg->HMessage2);
			}elseif (substr($ApprovalType,0,1) == "M"){
				$rStatus	= $ipg->MStatus;//성공여부(O/X/S)
				$rMessage	= iconv('euc-kr','utf-8',$ipg->MRespMsg);
			}else{
				$rStatus = "X";
				$rMessage = "C취소거절/승인구분오류";
			}

			if($rStatus == "O"){
				$success = true;
			}
		}
		$resultArr	= array(
			'success'=>$success,
			'result_code'=>$rAuthNo,
			'result_msg'=>$rMessage
		);

		return array(
			'success'=>$success,
			'result_code'=>$rAuthNo,
			'result_msg'=>$rMessage
		);

	}

	/* 카카오페이 결제 취소 :: 2015-02-25 lwh */
	public function kakaopay_cancel($data_order,$data_refund){

		//## 1. 라이브러리 인클루드
		require("./pg/kakaopay/conf_inc.php");
		require("./pg/kakaopay/libs/lgcns_CNSpay.php");

		// 취소 금액 계산
		if($data_refund['cancel_type']=='full'){
			$CancelAmt	= $data_order['settleprice'];
			$CancelCnt	= '';
		}else{
			$CancelAmt	= $data_refund['refund_price'];

			/* 기 부분매입취소된 금액 제외 */
			$query = "select sum(refund_price) as sum_refund_price, count(*) as cancle_cnt from fm_order_refund where `status`='complete' and order_seq=?";
			$query = $this->db->query($query,array($data_order['order_seq']));
			$res = $query->row_array();
			if($res['sum_refund_price']){
				$TotalAmt -= $res['sum_refund_price'];
			}
			$CancelCnt	= (int)$res['cancle_cnt'] + 1;

			// 취소금액의 최대값 설정
			//if($CancelAmt > $TotalAmt)	$CancelAmt = $TotalAmt;
		}

		//## 2. 취소 요청 파라미터 구성
		$cancleParam['MID']					= $MID;			// 가맹점 ID
		$cancleParam['TID']					= $data_order['pg_log']['tno'];
															// 카카오페이 거래번호
		$cancleParam['CancelAmt']			= $CancelAmt;	// 취소 금액
		$cancleParam['SupplyAmt']			= $data_order['freeprice'];
															// 공급가액
		$cancleParam['GoodsVat']			= $CancelAmt - $data_order['freeprice'];
															// 부가세
		$cancleParam['ServiceAmt']			= 0;			// 봉사료
		$cancleParam['CancelMsg']			= ($data_refund['refund_reason']) ? $data_refund['refund_reason'] : '사유없음';
															// 취소사유
		$cancleParam['PartialCancelCode']	= 
			($data_refund['cancel_type']=='full') ? '0' : 1;
															// 취소단위 : 0-전체, 1-부분
		$cancleParam['CancelIP']			= $_SERVER['REMOTE_ADDR'];	
															// 취소요청자IP
		$cancleParam['CancelNo']			= $CancelCnt;	// 취소번호
		$cancleParam['PayMethod']			= 'CARD';		// 결제타입

		//## 3. 데이터 요청
		$connector = new CnsPayWebConnector($LogDir);
		$connector->CnsActionUrl("https://".$CnsPayDealRequestUrl);
		$connector->CnsPayVersion($phpVersion);
		
		$connector->setRequestData($cancleParam);

		$connector->addRequestData("actionType", "CL0");
		$connector->addRequestData("CancelPwd", $cancelPwd);
		$connector->addRequestData("CancelIP", $_SERVER['REMOTE_ADDR']);

		//가맹점키 셋팅 (MID 별로 틀림) 
		$connector->addRequestData("EncodeKey", $merchantKey);

		//## 4. CNSPAY Lite 서버 접속하여 처리
		$connector->requestAction();

		//## 5. 결과 처리
		$resultCode = $connector->getResultData("ResultCode"); 	
		// 결과코드 (정상 :2001(취소성공), 2002(취소진행중), 그 외 에러)
		$resultMsg	= $connector->getResultData("ResultMsg");		// 결과메시지
		$cancelAmt	= $connector->getResultData("CancelAmt");		// 취소금액
		$cancelDate = $connector->getResultData("CancelDate");		// 취소일
		$cancelTime = $connector->getResultData("CancelTime");		// 취소시간
		$payMethod	= $connector->getResultData("PayMethod");		// 취소 결제수단
		$mid		= $connector->getResultData("MID");				// 가맹점 ID
		$tid		= $connector->getResultData("TID");				// TID
		$errorCD	= $connector->getResultData("ErrorCD");        	// 상세 에러코드
		$errorMsg	= $connector->getResultData("ErrorMsg");      	// 상세 에러메시지
		$authDate	= $cancelDate . $cancelTime;					// 취소거래시간
		$ccPartCl	= $connector->getResultData("CcPartCl");		// 부분취소 가능여부 (0:부분취소불가, 1:부분취소가능)
		$stateCD = $connector->getResultData("StateCD");			// 거래상태코드 (0: 승인, 1:전취소, 2:후취소)
		$authDate = $connector->makeDateString($authDate);
		$errorMsg = iconv("euc-kr", "utf-8", $errorMsg);
		$resultMsg = iconv("euc-kr", "utf-8", $resultMsg);

		if($resultCode == '2001'){
			$success	= true;
		}else{
			$success	= false;
		}

		return array(
			'success'		=> $success,
			'result_code'	=> $resultCode,
			'result_msg'	=> $resultMsg
		);
	}
}