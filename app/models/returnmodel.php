<?php
class returnmodel extends CI_Model {
	public function __construct()
	{
		$this->arr_return_status = array(
			'request'	=> '반품 신청',
			'ing'		=> '반품 처리중',
			'complete'	=> '반품 완료'
		);

		$this->arr_return_type = array(
			'exchange'	=> '맞교환',
			'return'	=> '반품'
		);

		$this->arr_return_method = array(
			'user'	=> '자가반품',
			'shop'	=> '택배회수'
		);
	}

	public function insert_return($data,$items)
	{

		$this->db->insert('fm_order_return', $data);
		$return_seq = $this->db->insert_id();
		$update_data['return_code'] = 'R'.date('ymdH').$return_seq;

		$this->db->where('return_seq',$return_seq);
		$this->db->update('fm_order_return',$update_data);

		foreach($items as $item_data){
			$item_data['return_code'] = $update_data['return_code'];
			$this->db->insert('fm_order_return_item',$item_data);
		}

		return $update_data['return_code'];
	}

	public function get_return_list($sc){

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

		if(!empty($sc['step_type'])){
			switch($sc['step_type']){
				case "return":
					$sqlWhereClause .= " ";
				break;
				case "return_ing":
					$sqlWhereClause .= " and r.status!='complete' ";
				break;
			}
		}

		if($sqlWhereClause) $sqlWhereClause = "where 1 " . $sqlWhereClause;

		$sql = "
		SELECT * FROM (
			select
			r.*,
			o.payment,
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
			sum(ri.ea) as return_ea_sum
			from
				fm_order_return as r
				inner join fm_order as o on r.order_seq = o.order_seq
				inner join fm_member as m on o.member_seq = m.member_seq
				inner join fm_order_return_item as ri on r.return_code=ri.return_code
			{$sqlWhereClause}
			group by r.return_code
		) t
		ORDER BY regist_date DESC
		";

		$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		$result['page']['querystring'] = get_args_list();

		foreach($result['record'] as $k => $data)
		{
			$no++;

			$result['record'][$k]['mpayment'] = $this->arr_payment[$result['record'][$k]['payment']];
			$result['record'][$k]['mstatus'] = $this->arr_return_status[$result['record'][$k]['status']];
			$result['record'][$k]['mtype'] = $this->arr_return_type[$result['record'][$k]['return_type']];
			$result['record'][$k]['mreturn_date'] = $result['record'][$k]['return_date']=='0000-00-00 00:00:00' ? '' : substr($result['record'][$k]['return_date'],0,10);

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

	public function get_return($return_code)
	{
		$query = "select * from fm_order_return where return_code=? limit 1";
		$query = $this->db->query($query,array($return_code));
		list($result) = $query -> result_array();
		return $result;
	}

	public function get_return_refund_code($refund_code)
	{
		$query = "select * from fm_order_return where refund_code=? limit 1";
		$query = $this->db->query($query,array($refund_code));
		list($result) = $query -> result_array();
		return $result;
	}

	public function get_return_for_order($order_seq,$type=null)
	{
		$query = "
		select r.*,
		sum(i.ea) ea,
		sum(ifnull(sub.price,0)*i.ea) sub_price,
		sum(ifnull(opt.price,0)*i.ea) opt_price,
		(select mname from fm_manager where manager_seq=r.manager_seq) admin
		from
		fm_order_return r,
		fm_order_return_item i
		left join fm_order_item_option opt on opt.item_option_seq=i.option_seq
		left join fm_order_item_suboption sub on sub.item_suboption_seq=i.suboption_seq
		where r.return_code=i.return_code and r.order_seq=? ";

		if($type){
			$query .= " and return_type='{$type}' ";
		}

		$query .= " group by r.return_code";
		$query = $this->db->query($query,array($order_seq));

		foreach($query -> result_array() as $data) $result[] = $data;

		return $result;
	}

	public function get_return_item($return_code)
	{
		$query1 = "
		SELECT
		'opt' opt_type,
		opt.item_option_seq option_seq,
		opt.supply_price,
		opt.consumer_price,
		opt.price,
		opt.goods_code,
		item.goods_shipping_cost,
		opt.download_seq,
		opt.coupon_sale,
		opt.member_sale,
		opt.fblike_sale,
		opt.mobile_sale,
		opt.promotion_code_sale,
		opt.referer_sale,
		opt.reserve,
		opt.point as point,
		item.goods_name,
		item.image,
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
		item.event_seq,
		item.goods_shipping_cost,
		ref.return_item_seq,
		ref.reason_code,
		ref.reason_desc,
		ref.return_ea,
		ref.ea,
		ref.export_code
		FROM
		fm_order_return_item ref,fm_order_item_option opt,fm_order_item item
		WHERE
		ref.option_seq is not null
		AND ref.option_seq = opt.item_option_seq
		AND opt.item_seq = item.item_seq
		AND ref.return_code = ?
		";
		$query2 = "
		SELECT
		'sub' opt_type,
		sub.item_suboption_seq option_seq,
		sub.supply_price,
		sub.consumer_price,
		sub.price,
		sub.goods_code,
		0 goods_shipping_cost,
		'' download_seq,
		0 coupon_sale,
		sub.member_sale as member_sale,
		0 fblike_sale,
		0 mobile_sale,
		0 promotion_code_sale,
		0 referer_sale,
		sub.reserve as reserve,
		sub.point as point,
		item.goods_name,
		item.image,
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
		item.event_seq,
		item.goods_shipping_cost,
		ref.return_item_seq,
		ref.reason_code,
		ref.reason_desc,
		ref.return_ea,
		ref.ea,
		ref.export_code
		FROM
		fm_order_return_item ref,fm_order_item_suboption sub,fm_order_item item
		WHERE
		ref.suboption_seq is not null
		AND ref.suboption_seq = sub.item_suboption_seq
		AND sub.item_seq = item.item_seq
		AND ref.return_code = ?
		";

		$query = "(".$query1.") union (".$query2.")";
		$query = $this->db->query($query,array($return_code,$return_code));
		foreach($query->result_array() as $data){
			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !(is_file($data['image'])) ) {
				$data['image'] = viewImg($data['goods_seq'],'thumbCart');
			}
			$result[] = $data;
		}
		return $result;
	}

	//반품 체크
	public function check_return($order_seq)
	{
		$query = "select * from fm_order_return where order_seq=? ";
		$query = $this->db->query($query,array($order_seq));
		list($result) = $query -> result_array();
		return $result;
	}

	//반품 상품 체크
	public function check_return_item($return_code)
	{
		$query = "select * from fm_order_return_item where return_code=? ";
		$query = $this->db->query($query,array($return_code));
		foreach($query->result_array() as $data) $result[] = $data;
		return $result;
	}
	
	//반품 상품 반품코드와 반품신청날짜 체크
	public function get_return_item_return_code($item_seq,$option_seq,$export_code=null,$return_type=null)
	{
		$query = "select sum(a.ea) as 'ea', a.return_code from fm_order_return_item a 
		where a.item_seq=? and a.option_seq=?";
		$values = array($item_seq,$option_seq);
		if($export_code){
			$query .= " and a.export_code=?";
			$values[] = $export_code;
		}
		if($return_type){
			$query .= " and b.return_type=?";
			$values[] = $return_type;
		} 
		$query = $this->db->query($query,$values); 
		$result = $query->row_array();
		return $result;
	}

	//반품 상품 수량 체크
	public function get_return_item_ea($item_seq,$option_seq,$export_code=null,$return_type=null)
	{
		$query = "select sum(a.ea) as 'ea' from fm_order_return_item a
		left join fm_order_return b on a.return_code=b.return_code
		where a.item_seq=? and a.option_seq=?";
		$values = array($item_seq,$option_seq);
		if($export_code){
			$query .= " and a.export_code=?";
			$values[] = $export_code;
		}
		if($return_type){
			$query .= " and b.return_type=?";
			$values[] = $return_type;
		}
		$query = $this->db->query($query,$values);

		$result = $query->row_array();
		return $result;
	}

	//반품 서브상품 수량 체크
	public function get_return_subitem_ea($item_seq,$suboption_seq,$export_code=null,$return_type=null)
	{
		$query = "select sum(a.ea) as 'ea' from fm_order_return_item a
		left join fm_order_return b on a.return_code=b.return_code
		where a.item_seq=? and a.suboption_seq=?";
		$values = array($item_seq,$suboption_seq);
		if($export_code){
			$query .= " and a.export_code=?";
			$values[] = $export_code;
		}
		if($return_type){
			$query .= " and b.return_type=?";
			$values[] = $return_type;
		}
		$query = $this->db->query($query,$values);
		foreach($query->result_array() as $data) $result = $data;
		return $result;
	}
}