<?php
class Eventmodel extends CI_Model {
	function __construct() {
		parent::__construct();

	}

	function is_event_template_file($template_path){
		return preg_match("/(.*)\/event[0-9]{7}.html$/",$template_path) ? true : false;
	}

	function is_gift_template_file($template_path){
		return preg_match("/(.*)\/gift[0-9]{7}.html$/",$template_path) ? true : false;
	}

	// 상품의 사은품이벤트 정보 가져오기
	public function get_gift_event_all($goods_seq)
	{
		$today = date('Y-m-d');
		$r_query[] = "
		select b.*,e.title,e.start_date,e.end_date from fm_gift_benefit b left join fm_gift e on b.gift_seq=e.gift_seq where
		e.gift_seq = b.gift_seq and e.goods_rule='all' and e.display='y' and e.start_date <= '$today' and e.end_date >= '$today' ";
		$r_query[] = "
		select b.*,e.title,e.start_date,e.end_date from fm_gift_benefit b left join fm_gift e on b.gift_seq=e.gift_seq where
		e.gift_seq = b.gift_seq and e.goods_rule='category' and e.display='y' and e.start_date <= '$today' and e.end_date >= '$today'
		and	(select count(*) from fm_gift_choice where gift_seq = b.gift_seq and choice_type = 'category' and goods_seq = '$goods_seq') > 0";
		$r_query[] = "
		select b.*,e.title,e.start_date,e.end_date from fm_gift_benefit b left join fm_gift e on b.gift_seq=e.gift_seq where
		e.gift_seq = b.gift_seq and e.goods_rule='goods_view' and e.display='y' and e.start_date <= '$today' and e.end_date >= '$today'
		and	(select count(*) from fm_gift_choice where gift_seq = b.gift_seq and choice_type = 'goods' and goods_seq = '$goods_seq') > 0";
		$query = 'select * from (('.implode(') union (',$r_query).')) t order by t.end_date asc';
		$query = $this->db->query($query);
		$result = $query->result_array();
		foreach($query->result_array() as $row){
			$groups[] = $row;
		}
		return $result;
	}



	public function get_event($eventSeq){

		$sql	= "select * from fm_event where event_seq = '".$eventSeq."' ";
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		return $result;
	}

	public function get_solo_event_goods($eventSeq){

		$sql	= "select g.* from
						fm_event_choice as ec
						inner join fm_goods as g on ec.goods_seq = g.goods_seq
					where
						ec.event_seq = '".$eventSeq."' and
						ec.choice_type = 'goods'";
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		return $result;
	}

	public function update_solo_event_stnum($goods_seq, $st_num){
		if	($goods_seq > 0 && $st_num > 0){
			$sql	= "update fm_goods set event_st_num = '".$st_num."' where goods_seq = '".$goods_seq."' ";
			$this->db->query($sql);
		}
	}

	public function get_event_order_result($event_seq){
		$sql	= "select
					count(*)														 as cnt,
					sum(ifnull(opt.ea,0) + ifnull(sub.ea,0))						as ea,
					sum(ifnull((opt.price*opt.ea),0)+ifnull((sub.price*sub.ea),0))	as price
				from
					fm_order_item						as item
					inner join fm_order					as ord on item.order_seq = ord.order_seq
					inner join fm_order_item_option		as opt on ( item.item_seq = opt.item_seq and opt.step > 15 and opt.step < 80 )
					left join fm_order_item_suboption	as sub on ( opt.item_option_seq = sub.item_option_seq and sub.step > 15 and sub.step < 80 )
				where
					item.event_seq = '".$event_seq."'
				group by item.event_seq ";
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		return $result;

	}

	public function chk_solo_event_duple($param){
		if	($param['event_seq'])
			$addWhere	= " and evt.event_seq != '".$param['event_seq']."' ";

		$sql	= "select evt.event_seq
					from fm_event as evt
					inner join fm_event_choice	as chc on evt.event_seq = chc.event_seq
					where evt.event_type = 'solo' and ((evt.start_date between '".$param['start_date']."' and '".$param['end_date']."') or (evt.end_date between '".$param['start_date']."' and '".$param['end_date']."')) and chc.goods_seq = '".$param['goods_seq']."' ".$addWhere;
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		if	($result['event_seq'])	return true;
		else						return false;
	}

	public function get_event_list(){

		$sc=array();
		$sc['sort']				= $_GET['sort'] ? $_GET['sort'] : 'evt.event_seq desc';
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['keyword']			= $_GET['keyword'];
		$sc['perpage']			= $_GET['perpage'] ? $_GET['perpage'] : 10;

		if( count($_GET) == 0 ){
			$_GET['event_status'] = array('before','ing','end');
		}

		$where = array();

		// 검색어
		if( $_GET['keyword'] ){
			$where[] = "
				CONCAT(
					title
				) LIKE '%" . addslashes($_GET['keyword']) . "%'
			";
		}

		// 일자검색
		if( $_GET['date'] ){
			$date_field = $_GET['date'];
			if($_GET['sdate'] && $_GET['edate']) $where[] = "date_format({$date_field},'%Y-%m-%d') between '{$_GET['sdate']}' and '{$_GET['edate']}'";
			else if($_GET['sdate']) $where[] = "'{$_GET['sdate']}' < date_format({$date_field},'%Y-%m-%d')";
			else if($_GET['edate']) $where[] = "date_format({$date_field},'%Y-%m-%d') < '{$_GET['edate']}'";
		}

		// 이벤트진행상태
		if( $_GET['event_status'] ){
			$arr = array();
			foreach($_GET['event_status'] as $key => $data){

				switch($data){
					case "before":
						$arr[] = "start_date > CURRENT_TIMESTAMP()";
					break;
					case "ing":
						$arr[] = "CURRENT_TIMESTAMP() between start_date and end_date";
					break;
					case "end":
						$arr[] = "end_date < CURRENT_TIMESTAMP()";
					break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

		// 단독이벤트 관련 검색
		if	($_GET['sc_event_type'] == 'solo'){
			$where[]	= " evt.event_type = 'solo' ";
			if		(!empty($_GET['sc_start_st']) && !empty($_GET['sc_end_st'])){
				$where[]	= " evt.st_num between '".$_GET['sc_start_st']."' and '".$_GET['sc_end_st']."' ";
			}elseif	(!empty($_GET['sc_start_st']) && empty($_GET['sc_end_st'])){
				$where[]	= " evt.st_num >= '".$_GET['sc_start_st']."' ";
			}elseif	(empty($_GET['sc_start_st']) && !empty($_GET['sc_end_st'])){
				$where[]	= " evt.st_num <= '".$_GET['sc_end_st']."' ";
			}
			if		(!empty($_GET['sc_goods_type'])){
				$where[]	= " g.goods_kind = '".$_GET['sc_goods_type']."' ";
			}
			if		(!empty($_GET['sc_goods_name'])){
				$where[]	= " g.goods_name like '%".$_GET['sc_goods_name']."%' ";
			}
		}

		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";

		$query	= "select SQL_CALC_FOUND_ROWS
					evt.*, g.*,
					if(CURRENT_TIMESTAMP() between start_date and end_date,'진행 중',if(end_date < CURRENT_TIMESTAMP(),'종료','시작 전')) as status,
					bft.target_sale as target_sale,
					bft.event_sale as event_sale,
					evt.update_date as update_date,
					evt.regist_date as regist_date,
					IFNULL(event_order_cnt,0)		as order_cnt,
					IFNULL(event_order_ea,0)		as order_ea,
					IFNULL(event_order_price,0)		as order_price
				FROM fm_event as evt
					left join fm_event_benefits as bft on ( evt.event_seq = bft.event_seq and bft.event_benefits_seq  = concat(bft.event_seq,'_1') )
					left join fm_goods as g on evt.goods_seq = g.goods_seq
				{$sqlWhereClause}
				ORDER BY {$sc['sort']}";
		$result = select_page($sc['perpage'],$sc['page'],10,$query,array());
		$count['total']	 = get_rows('fm_event');

		$ingeventsql = 'select event_seq from fm_event where CURRENT_TIMESTAMP() between start_date and end_date';
		$ingeventquery = $this->db->query($ingeventsql);
		$count['ing'] = $ingeventquery->num_rows();

		$endeventsql = 'select event_seq from fm_event where end_date < CURRENT_TIMESTAMP()';
		$endeventquery = $this->db->query($endeventsql);
		$count['end'] = $endeventquery->num_rows();

		return array('result' => $result, 'count' => $count, 'sc' => $sc);
	}

	//이벤트 주문통계
	public function event_order($event_seq) {
		if(!$event_seq) return;
		$query = "select sum(settleprice) step75_price, step75_count,sum(opt_ea) opt_ea,
					sum(sub_ea) sub_ea, mon,
					sum(refund_price) refund_price,sum(refund_count) refund_count,sum(refund_ea) refund_ea
				from (
					select
					count(item.item_seq) step75_count,
					sum(ifnull((opt.price*opt.step75),0)+ifnull((sub.price*sub.step75),0))	as settleprice,
					ifnull(sum(opt.step75),0) opt_ea,
					ifnull(sum(sub.step75),0) sub_ea,
					ifnull((substring(ord.regist_date,1,7)),'') as mon,
					ifnull((select sum(refund_price+refund_emoney) from fm_order_refund_item a,fm_order_refund b where a.refund_code=b.refund_code and a.item_seq = item.item_seq),0) refund_price,
					ifnull((select count(*) from fm_order_refund as  b inner join fm_order_refund_item as a on a.refund_code=b.refund_code  where a.item_seq = item.item_seq),0) refund_count,
					ifnull((select sum(ea) from fm_order_refund_item a,fm_order_refund b where a.refund_code=b.refund_code and a.item_seq = item.item_seq),0) refund_ea
				from
					fm_order_item										as item
					inner join fm_order								as ord on item.order_seq = ord.order_seq
					inner join fm_order_item_option			as opt on item.item_seq = opt.item_seq
						left join fm_order_item_suboption	as sub on opt.item_option_seq = sub.item_option_seq
					where item.event_seq=? and opt.step='75' and ord.regist_date>=?  and ord.regist_date<=?
				) t
				group by t.mon";

		$start = date('Y-').str_pad((date('m')-1),2 ,"0", STR_PAD_LEFT)."-01 00:00:00";
		$end = date('Y-m')."-31 24:00:00";
		$query = $this->db->query($query,array($event_seq,$start,$end));
		//debug_var($this->db->last_query());
		if( $query ) {
			foreach($query->result_array() as $row){
				if(!$row['mon']) continue;
				$row['mon'] = str_replace("-","",$row['mon']);
				$param = array();
				$query = "delete from fm_event_order where event_seq=? and month=?";
				$this->db->query($query,array($event_seq,$row['mon']));
				$query = "insert into fm_event_order set step75_count=?,step75_price=?,step75_ea=?,
				refund_count=?,refund_price=?,refund_ea=?,
				event_seq=?,month=?";
				$param[] = $row['step75_count'];
				$param[] = $row['step75_price'];
				$param[] = $row['opt_ea']+$row['sub_ea'];
				$param[] = $row['refund_count'];
				$param[] = $row['refund_price'];
				$param[] = $row['refund_ea'];
				$param[] = $event_seq;
				$param[] = $row['mon'];
				//debug_var($row);
				$this->db->query($query,$param);
			//debug_var($this->db->last_query());
			}
		}
	}

	/* 단독이벤트의 판매수량/주문건수/주문금액 일괄 업데이트 업데이트 @2013-11-15 */
	public function event_order_batch($event_seq) {
		if(!$event_seq) return;

		$eventupquery = "select
		( select sum( CONVERT(step75_ea * 1, SIGNED) - CONVERT(refund_ea * 1, SIGNED)  ) from fm_event_order where event_seq=A.event_seq ) event_order_ea,
		( select sum( step75_count ) from fm_event_order where event_seq=A.event_seq ) event_order_cnt,
		( select sum( CONVERT(step75_price * 1, SIGNED) - CONVERT(refund_price * 1, SIGNED) ) from fm_event_order where event_seq=A.event_seq ) event_order_price
		from fm_event A
		where A.event_seq =?";
		$eventup = $this->db->query($eventupquery,array($event_seq));
		$member_cnt = $eventup->row_array();

		$member_cnt['event_order_cnt']				= ($member_cnt['event_order_cnt']>0)?$member_cnt['event_order_cnt']:0;//주문건수
		$member_cnt['event_order_ea']				= ($member_cnt['event_order_ea']>0)?$member_cnt['event_order_ea']:0;//판매수량
		$member_cnt['event_order_price']			= ($member_cnt['event_order_price']>0)?$member_cnt['event_order_price']:0;//주문금액

		$this->db->where('event_seq', $event_seq);
		$result = $this->db->update('fm_event', array('event_order_ea'=>$member_cnt['event_order_ea'],'event_order_cnt'=>$member_cnt['event_order_cnt'],'event_order_price'=>$member_cnt['event_order_price']));
		//debug_var($this->db->last_query());
		return $result;
	}

	// fm_event 정보 추출
	public function get_today_event(){
		$ndate			= date('Ymd');
		$nweek			= date('w');
		$sql			= "select * from fm_event 
							where DATE_FORMAT(start_date, '%Y%m%d') <= '".$ndate."' 
							and DATE_FORMAT(end_date, '%Y%m%d') >= '".$ndate."' 
							and (app_week is null or app_week = '' or app_week like '%".$nweek."%') order by event_type='solo' desc";
		$query			= $this->db->query($sql);
		$result			= $query->result_array();

		return $result;
	}

	// fm_event_benefit 정보 추출
	public function get_event_benefit($event_seq){
		$sql		= "select * from fm_event_benefits where event_seq = '".$event_seq."' ";
		$query		= $this->db->query($sql);
		$result		= $query->result_array();

		return $result;
	}

	// fm_event_choice 정보 추출
	public function get_event_choice($event_seq, $event_benefits_seq = ''){

		if	($event_benefits_seq)
			$addWhere	= " and event_benefits_seq = '".$event_benefits_seq."'";

		$sql	= "select * from fm_event_choice 
					where event_seq = '".$event_seq."' ".$addWhere;
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}
}

/* End of file eventmodel.php */
/* Location: ./app/models/eventmodel.php */