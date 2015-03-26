<?php
class Membermodel extends CI_Model {

	var $group_benifit;

	/* ADMIN > MEMBER */
	public function find_group_list(){

		//$this->db->order_by("group_seq","asc");
		$this->db->order_by("order_sum_price","desc");
		$this->db->order_by("order_sum_ea","desc");
		$this->db->order_by("order_sum_cnt","desc");
		$this->db->order_by("use_type","asc");
		$query = $this->db->get("fm_member_group");
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		return $returnArr;
	}

	/* ADMIN > SETTING > GROUP */
	public function find_group_cnt_list(){

		//$this->db->order_by("group_seq","asc");
		$this->db->order_by("order_sum_price","desc");
		$this->db->order_by("order_sum_ea","desc");
		$this->db->order_by("order_sum_cnt","desc");
		$this->db->order_by("use_type","asc");
		$query = $this->db->get("fm_member_group");
		foreach ($query->result_array() as $row){
			$qry = "select count(member_seq) as count from fm_member where group_seq = '{$row['group_seq']}' and status != 'withdrawal'";
			$querys = $this->db->query($qry);
			$data = $querys->result_array();
			$row['count'] = $data[0]['count'];
			$returnArr[] = $row;
		}
		return $returnArr;
	}

	/* ADMIN > MEMBER */
	public function get_member_data($seq){

		if( defined('__ADMIN__') != true ) {//프론트인경우
			$sqlstatus = " AND A.status = 'done' ";//승인회원만
		}
		$key = get_shop_key();
		$sql = "SELECT
					A.*, B.*,
					A.member_order_cnt as  order_cnt,
					A.member_order_price as  order_sum,
					A.member_recommend_cnt ,
					A.member_invite_cnt,
					A.member_seq as member_seq,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
					CASE WHEN A.status = 'done' THEN '승인'
						WHEN A.status = 'hold' THEN '미승인'
						WHEN A.status = 'withdrawal' THEN '탈퇴'
					ELSE '' END AS status_nm,
					C.group_name,
					C.group_seq,
					C.icon,
					D.withdrawal_seq, D.reason, D.memo, D.regist_ip,
					D.regist_date as withdrawal_date,
					A.referer, A.referer_domain, E.referer_group_cd,
					IF(E.referer_group_no>0, E.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name
				FROM
					fm_member A
					LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
					LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
					LEFT JOIN fm_member_withdrawal D ON A.member_seq = D.member_seq
					LEFT JOIN fm_referer_group E ON A.referer_domain = E.referer_group_url
				WHERE
					A.member_seq = '{$seq}' {$sqlstatus} ";
		$query = $this->db->query($sql);
		foreach ($query->result_array() as $row){
			$data[] = $row;
		}

		// 사업자 회원일 경우 업체명->이름, 사업장주소->주소, 담당자전화번호->전화번호, 핸드폰->핸드폰
		if($data[0]['business_seq']){
			$data[0]['user_name'] = $data[0]['bname'];
			$data[0]['baddress_type'] = $data[0]['baddress_type'];
			$data[0]['address'] = $data[0]['baddress'];
			$data[0]['address_detail'] = $data[0]['baddress_detail'];
			$tmp = explode('-',$data[0]['bphone']);
			foreach($tmp as $k => $datas){
				$key = 'phone'.($k+1);
				$data[0][$key] = $datas;
			}

			$tmp = explode('-',$data[0]['bcellphone']);
			foreach($tmp as $k => $datas){
				$key = 'cellphone'.($k+1);
				$data[0][$key] = $datas;
			}

			$tmp = explode('-',$data[0]['bzipcode']);
			foreach($tmp as $k => $datas){
				$key = 'zipcode'.($k+1);
				$data[0][$key] = $datas;
			}
		}

		return (isset($data[0]))?$data[0]:'';
	}


	/* ADMIN > MEMBER */
	public function get_member_data_id($userid,$status=''){

		$key = get_shop_key();
		$sql = "SELECT
					A.*, B.*,
					A.member_seq as member_seq,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone
				FROM
					fm_member A LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				WHERE
					A.userid = ?";
		$bind[] = $userid;
		if(trim($status)){
			$sql .= " AND A.status=?";
			$bind[] = $status;
		}
		$query = $this->db->query($sql,$bind);
		foreach ($query->result_array() as $row){
			$data[] = $row;
		}
		return (isset($data[0]))?$data[0]:'';
	}

	public function get_member_data_only($userid){
		$key = get_shop_key();
		$sql = "SELECT
					A.member_seq as member_seq,C.group_name,
					A.user_name as user_name, A.nickname as nickname,
					A.birthday as birthday, A.anniversary as anniversary,
					A.emoney as emoney, A.point as point, A.cash as cash,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone
				FROM
					fm_member A
						LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
				WHERE
					A.userid = '{$userid}' limit 0, 1";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return (isset($data[0]))?$data[0]:'';
	}

	//userid -> member_seq 속도향상
	public function get_member_seq_only($seq){
		$key = get_shop_key();
		$sql = "SELECT
					A.member_seq as member_seq,C.group_name,
					A.user_name as user_name, A.nickname as nickname,
					A.userid,
					A.birthday as birthday, A.anniversary as anniversary,
					A.emoney as emoney, A.point as point, A.cash as cash,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone
				FROM
					fm_member A
						LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
				WHERE
					A.member_seq = '{$seq}' limit 0, 1";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return (isset($data[0]))?$data[0]:'';
	}

	## sms userid 노출 : 2014-06-23
	public function get_member_userid($seq){
		$sql		= "select userid from fm_member where member_seq='".$seq."'";
		$query		= $this->db->query($sql);
		$member_info= $query->result_array();
		return $member_info[0]['userid'];
	}

	## 고객리마인드서비스 : 이번주 만료 쿠폰, 메일/SMS 수신동의 회원 2014-07-22
	## 1일 1회 발송(발송로그 추적)
	public function get_member_receive_coupon($param){

		$startdt	= $param['startdt'];
		$enddt		= $param['enddt'];
		$key		= get_shop_key();
		$sql		= "
					select 
						 m.member_seq,
						 m.userid, m.user_name,
						 m.mailing,m.sms,
						 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
						 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
						 count(*) as coupon_count
					from 
						fm_member m 
						left join fm_download d on m.member_seq = d.member_seq 
					where 1  	
						and d.use_status='unused' 
						and d.issue_enddate BETWEEN '".$startdt."' and '".$enddt."'  
						and m.user_name != ''
						and (m.sms='y' and ifnull(m.sms,'') != '' and m.cellphone !='')
						and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='coupon' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
					group by 
						m.member_seq,m.user_name
					order by 
						m.member_seq asc
					";
		$query		= $this->db->query($sql);
		$member_list= $query->result_array();
		return $member_list;
	
	}

	## 고객리마인드서비스 : 다음달 소멸 적립금, 메일/SMS 수신동의 회원 2014-07-23
	public function get_member_receive_emoney($param){

		$startdt	= $param['startdt'];
		$enddt		= $param['enddt'];
		$key		= get_shop_key();

		$sql = "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
					 sum(e.remain) as mileage_rest,
					 date_format(e.limit_date,'%Y년 %m월') as limit_date
				from 
					fm_member as m 
					left join fm_emoney as e on m.member_seq = e.member_seq 
				where 1  	
					and e.limit_date BETWEEN '".$startdt."' and '".$enddt."'  
					and m.user_name != ''
					and (m.sms='y' and ifnull(m.sms,'') != '' and m.cellphone !='')
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='emoney' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
				group by 
					m.member_seq,m.user_name
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member_list= $query->result_array();

		return $member_list;

	}

	## 고객리마인드서비스 : 멤버쉽 서비스 메일/SMS 수신동의 회원 2014-07-23
	public function get_member_receive_membership($after_day){

		$key = get_shop_key();
		$sql = "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
					 m.group_seq,
					 g.group_name,
					 g.myicon
				from 
					fm_member as m 
					left join fm_member_group as g on g.group_seq=m.group_seq
				where 1  	
					and m.user_name != ''
					and g.use_type in('AUTO','AUTOPART')
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='membership' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
					and datediff(now(),m.group_set_date)='".$after_day."'
					and (m.sms='y' and ifnull(m.sms,'') != '' and m.cellphone !='')
				group by 
					m.member_seq,m.user_name
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member_list= $query->result_array();

		return $member_list;

	}

	## 고객리마인드서비스 : 장바구니/위시리스트에 담긴 상품 중 가장 마지막 날짜 기준 +O일 2014-07-24
	public function get_member_receive_cart($after_day){

		$key = get_shop_key();
		$sql = "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
					 count(*) as cart_cnt,
					 max(c.regist_date) as regdt
				from 
					fm_member as m , fm_cart as c 
				where
					c.member_seq=m.member_seq
					and m.user_name != ''
					and (m.sms='y' and ifnull(m.sms,'') != '' and AES_DECRYPT(UNHEX(m.cellphone), '".$key."') !='')
					and c.member_seq > 0
					and c.distribution='cart'
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='cart' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
				group by 
					m.member_seq,m.user_name
				having 
					datediff(now(),max(c.regist_date))='".$after_day."'
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member['cart'] = $query->result_array();
		$loop = array();
		foreach($member['cart'] as $item){
			$loop[$item['member_seq']] = $item;
			$loop[$item['member_seq']]['wish_cnt'] = 0;
		}

		$sql = "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
					 count(*) as wish_cnt,
					 max(w.regist_date) as regdt
				from 
					fm_member as m , fm_goods_wish as w 
				where
					w.member_seq=m.member_seq
					and m.user_name != ''
					and (m.sms='y' and ifnull(m.sms,'') != '' and AES_DECRYPT(UNHEX(m.cellphone), '".$key."') !='')
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='cart' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
				group by 
					m.member_seq,m.user_name
				having 
					datediff(now(),max(w.regist_date))='".$after_day."'
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member['wish'] = $query->result_array();
		foreach($member['wish'] as $item){
			if(array_key_exists($item['member_seq'],$loop)){
				$loop[$item['member_seq']]['wish_cnt'] = $item['wish_cnt'];
			}else{
				$loop[$item['member_seq']] = $item;
			}
		}
		return $loop;

	}

	## 고객리마인드서비스 : 장바구니/위시리스트 타임세일 메일/SMS 수신동의 회원 2014-07-24
	public function get_member_receive_timesale($cartdt){

		$key	= get_shop_key();
		$sql	= "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
					 count(*) as cart_cnt
				from 
					fm_cart as c, fm_event as e, fm_member as m
				where 
					c.goods_seq=e.goods_seq
					and c.member_seq=m.member_seq
					and e.event_type='solo'
					and c.distribution='cart'
					and (case when  (e.app_week = '' or e.app_week = '0' or  e.app_week is null) and date_format(e.end_date,'%Y%m%d') = date_format('".$cartdt['lastday']." 00:00:00','%Y%m%d') then
							1 
						else 
							(case when e.start_date <= '".$cartdt['lastday']." 23:59:59' and e.end_date >= '".$cartdt['lastday']." 00:00:00' and app_week like '%".$cartdt['appweek']."%' then 1 else 0 end)
						end ) = 1
					and m.user_name != ''
					and (m.sms='y' and ifnull(m.sms,'') != '' and AES_DECRYPT(UNHEX(m.cellphone), '".$key."') !='')
					and c.member_seq > 0
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='timesale' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
				group by 
					m.member_seq,m.user_name
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member['cart'] = $query->result_array();
		$loop = array();
		foreach($member['cart'] as $item){
			$loop[$item['member_seq']] = $item;
			$loop[$item['member_seq']]['wish_cnt'] = 0;
		}

		$sql = "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone,
					 count(*) as wish_cnt
				from 
					fm_goods_wish as w, fm_event as e, fm_member as m
				where
					w.goods_seq=e.goods_seq
					and w.member_seq=m.member_seq
					and e.event_type='solo'
					and (case when  (e.app_week = '' or e.app_week = '0' or  e.app_week is null) and date_format(e.end_date,'%Y%m%d') = date_format('".$cartdt['lastday']." 00:00:00','%Y%m%d') then
							1 
						else 
							(case when e.start_date <= '".$cartdt['lastday']." 23:59:59' and e.end_date >= '".$cartdt['lastday']." 00:00:00' and app_week like '%".$cartdt['appweek']."%' then 1 else 0 end)
						end ) = 1
					and m.user_name != ''
					and (m.sms='y' and ifnull(m.sms,'') != '' and AES_DECRYPT(UNHEX(m.cellphone), '".$key."') !='')
					and m.user_name != ''
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='timesale' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
				group by 
					m.member_seq,m.user_name
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member['wish'] = $query->result_array();
		foreach($member['wish'] as $item){
			if(array_key_exists($item['member_seq'],$loop)){
				$loop[$item['member_seq']]['wish_cnt'] = $item['wish_cnt'];
			}else{
				$loop[$item['member_seq']] = $item;
			}
		}
		return $loop;

	}

	## 고객리마인드서비스 : 상품리뷰 대상자 2014-07-28
	public function get_member_receive_review($after_day){
		
		$key = get_shop_key();
		$sql = "
				select 
					 m.member_seq,
					 m.userid, m.user_name,
					 m.mailing,m.sms,
					 AES_DECRYPT(UNHEX(m.email), '".$key."') as email,
					 AES_DECRYPT(UNHEX(m.cellphone), '".$key."') as cellphone
				from 
					fm_member as m 
					left join fm_order as o on o.member_seq=m.member_seq
					left join fm_order_item as oi on oi.order_seq=o.order_seq
					left join fm_goods_export as ge on ge.order_seq=o.order_seq
					left join fm_goods_export_item as gei on gei.export_code=ge.export_seq and gei.item_seq=oi.item_seq
				where 1  	
					and m.user_name != ''
					and (m.sms='y' and ifnull(m.sms,'') != '' and AES_DECRYPT(UNHEX(m.cellphone), '".$key."') !='')
					and ge.status='75'
					and datediff(now(),ge.shipping_date)=".$after_day."
					and (select count(*) from fm_goods_review where order_seq=o.order_seq and goods_seq=oi.goods_seq and mid=m.userid)=0
					and (select count(*) from fm_log_curation_sms where member_seq=m.member_seq and kind='review' and sendres='y' and reserve_date between date_format(now(),'%Y-%m-%d 00:00:00') and date_format(now(),'%Y-%m-%d 23:59:59'))=0
				group by 
					m.member_seq,m.user_name
				order by 
					m.member_seq asc
				";
		$query		= $this->db->query($sql);
		$member_list= $query->result_array();

		return $member_list;

	}

	## 고객리마인드서비스 : 유입통계
	public function curation_stat($sc){
		
		//$where = array();
		if( !empty($sc['start_date']) && !empty($sc['end_date']) ){
			$where = "send_date>='{$sc['start_date']}' and send_date<='{$sc['end_date']}' ";
		}
	
		if($where) $wheresub = " where ".$where;
		$sql = "select 
						inflow_kind
						,ifnull(sum(inflow_sms_total),0) as inflow_sms_total
						,ifnull(sum(inflow_email_total),0) as inflow_email_total
						,ifnull(sum(send_sms_total),0) as send_sms_total
						,ifnull(sum(send_email_total),0) as send_email_total
					from 
						fm_log_curation_summary 
					".$wheresub."
					group by inflow_kind
				";
		$query	= $this->db->query($sql);
		$data = array();
		if($where) $wheresub = " and ".$where;
		foreach($query->result_array() as $item){
			$sql2	= "select 
						ifnull(sum(login_cnt),0) as login_cnt
						,ifnull(sum(goodsview_cnt),0) as goodsview_cnt
						,ifnull(sum(cart_cnt),0) as cart_cnt
						,ifnull(sum(wish_cnt),0) as wish_cnt
						,ifnull(sum(order_cnt),0) as order_cnt
					from 
						fm_log_curation_info_summary
					where
						curation_kind='".$item['inflow_kind']."' ".$wheresub."
				";
			$query2	= $this->db->query($sql2);
			$item2	= $query2->result_array();
			$data[] = array_merge($item,$item2[0]);
		}

		return $data;
	}


	## 고객리마인드서비스 : 유입통계상세
	public function curation_stat_detail($sc){

		$params = array();
		$params[] = "c.inflow_type";
		$params[] = "c.inflow_kind";
		$params[] = "c.curation_seq";
		$params[] = "c.member_seq";
		$params[] = "c.userid";
		$params[] = "c.access_type";
		$params[] = "c.regist_date as inflow_date";
		$params[] = "c.to_reception";
		$params[] = "c.to_msg";
		$params[] = "c.send_date";
		$params[] = "ifnull(cis.login_cnt,0) as login_cnt";
		$params[] = "ifnull(cis.goodsview_cnt,0) as goodsview_cnt";
		$params[] = "ifnull(cis.cart_cnt,0) as cart_cnt";
		$params[] = "ifnull(cis.wish_cnt,0) as wish_cnt";
		$params[] = "ifnull(cis.order_cnt,0) as order_cnt";

		$dbtables[] = "fm_log_curation as c";
		$dbtables[] = " left join fm_log_curation_info_summary as cis on c.curation_seq=cis.curation_seq";

		$where = array();
		if(!empty($sc['sc_kind'])){
			$where[] = "c.inflow_kind='".$sc['sc_kind']."'";
		}
		if(!empty($sc['sc_type'])){
			$where[] = "c.inflow_type='".$sc['sc_type']."'";
		}
		if(!empty($sc['sc_keyword'])){
			$where[] = "(c.to_msg like '%".$sc['sc_keyword']."%' or c.to_reception like '%".$sc['sc_keyword']."%' or c.userid like '%".$sc['sc_keyword']."%')";
		}
		if( !empty($sc['start_date2']) && !empty($sc['end_date2']) ){
			$where[] = "cis.send_date between '{$sc['start_date2']}' and '{$sc['end_date2']}' ";
		}

		$sqlFieldClause = implode("\n,",$params);
		$sqlFromClause	= implode("\n ",$dbtables);
		if(count($where)>0){
			$sqlWhereClause = " where ".implode("\n and ",$where);
		}
		$sql			= "select ".$sqlFieldClause." from ".$sqlFromClause." ".$sqlWhereClause;

		if	($sc['nolimit'] != 'y')
			$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$cnt_query = 'select count(*) as cnt from '. $sqlFromClause . ' '. $sqlWhereClause;
		$cntquery = $this->db->query($cnt_query);
		$cntrow = $cntquery->result_array();
		$data['count'] = $cntrow[0]['cnt'];

		return $data;

	}

	## 고객리마인드서비스 : email 발송내역
	public function curtion_history_email($sc){

		$sql = "select * from fm_log_curation_email";
		$sqltotal = "select  count(*) as cnt from fm_log_curation_email ";

		$where = array();
		$where[] = " sendres='y'";
		if( !empty($sc['start_date']) && !empty($sc['end_date']) ){
			$where[] = " regist_date between '{$sc['start_date']} 00:00:00' and '{$sc['end_date']} 23:59:59' ";
		}

		if( !empty($sc['sc_kind']) ){
			$where[] = " kind='".$sc['sc_kind']."' ";
		}
		if( !empty($sc['sc_subject']) ){
			$where[] = " subject like '%{$sc['sc_subject']}%' ";
		}

		if($where) { $sql .= " where ".implode(" and ",$where); }
		$sql .= ($sc['orderby'])? " order by {$sc['orderby']} {$sc['sort']}":"";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		if($where) { $sqltotal .= " where ".implode(" and ",$where); }
		$query2 = $this->db->query($sqltotal);
		$cntrow = $query2->result_array();
		$data['count'] = $cntrow[0]['cnt'];

		return $data;
	}

	## 고객리마인드서비스 sms 발송내역
	public function curtion_history_sms($sc){
 
		$sql = "select * from fm_log_curation_sms";
		$sqltotal = "select  count(*) as cnt from fm_log_curation_sms ";

		$where = array();
		$where[] = " sendres='y'";
		if( !empty($sc['start_date']) && !empty($sc['end_date']) ){
			$where[] = " regist_date between '{$sc['start_date']} 00:00:00' and '{$sc['end_date']} 23:59:59' ";
		}

		if( !empty($sc['sc_kind']) ){
			$where[] = " kind='".$sc['sc_kind']."' ";
		}
		if( !empty($sc['sc_subject']) ){
			$where[] = " sms_msg like '%{$sc['sc_subject']}%' ";
		}

		if($where) { $sql .= " where ".implode(" and ",$where); }
		$sql .= ($sc['orderby'])? " order by {$sc['orderby']} {$sc['sort']}":"";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		if($where) { $sqltotal .= " where ".implode(" and ",$where); }
		$query2 = $this->db->query($sqltotal);
		$cntrow = $query2->result_array();
		$data['count'] = $cntrow[0]['cnt'];

		return $data;
	}

	//게시판에서 최소의 회원정보용
	public function get_member_data_only_seq($seq,$newmbinfo=null){
		$key = get_shop_key();
		$sql = "SELECT
					A.member_seq,A.userid,A.rute,C.group_name,C.icon,A.user_icon,{$newmbinfo}
					A.user_name as user_name, A.nickname as nickname,
					A.birthday as birthday, A.anniversary as anniversary,
					A.emoney as emoney, A.point as point, A.cash as cash,
					B.bname as bname,
					B.business_seq as mbinfo_business_seq,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone
				FROM
					fm_member A
						LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
						LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				WHERE
					A.member_seq = '{$seq}' limit 0, 1"; 
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return (isset($data[0]))?$data[0]:'';
	}

	/* ADMIN > MEMBER */
	public function admin_member_list($sc) {

		$key = get_shop_key();

		$sqlSelectClause = "
			select
				A.member_seq,A.userid,A.user_name,A.nickname,A.mailing,A.sms,A.emoney,A.point,A.cash,A.regist_date,A.lastlogin_date,A.review_cnt,A.login_cnt,A.birthday,A.zipcode,A.address_street,A.address_type,A.address,A.address_detail,A.sns_f,A.anniversary,A.recommend,A.sex,
				AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
				AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				CASE WHEN A.status = 'done' THEN '승인'
					 WHEN A.status = 'hold' THEN '미승인'
					 WHEN A.status = 'withdrawal' THEN '탈퇴'
				ELSE '' END AS status_nm,
				B.bname, B.bphone, B.bcellphone, B.business_seq, B.baddress_type, B.baddress, B.baddress_detail,
				B.bzipcode, B.bceo, B.bno, B.bitem,
				B.bstatus, B.bperson, B.bpart,
				A.member_order_cnt,A.member_order_price,A.member_recommend_cnt ,A.member_invite_cnt,
				A.referer, A.referer_domain,
				IF(C.referer_group_no>0, C.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name,
				D.group_name
		";
		$sqlFromClause = "
			from
				fm_member A
				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				LEFT JOIN fm_referer_group C ON A.referer_domain = C.referer_group_url
				LEFT JOIN fm_member_group D ON A.group_seq = D.group_seq
		";
		$sqlWhereClause = "
			where A.status in ('done','hold')
		";

		###
		if( !empty($sc['keyword'])){
			$sqlWhereClause .= " and ( A.userid like '%".$sc['keyword']."%' or A.user_name like '%".$sc['keyword']."%' or AES_DECRYPT(UNHEX(A.email), '{$key}') like '%".$sc['keyword']."%' or AES_DECRYPT(UNHEX(A.phone), '{$key}') like '%".$sc['keyword']."%' or AES_DECRYPT(UNHEX(A.cellphone), '{$key}') like '%".$sc['keyword']."%' or A.address like '%".$sc['keyword']."%' or A.address_detail like '%".$sc['keyword']."%' or A.nickname like '%".$sc['keyword']."%' or B.bname like '%".$sc['keyword']."%'  or B.baddress  like '%".$sc['keyword']."%' or B.bphone  like '%".$sc['keyword']."%' or B.bcellphone like '%".$sc['keyword']."%' or B.baddress_detail like '%".$sc['keyword']."%'  or B.bceo like '%".$sc['keyword']."%') ";
		}
		### add start, end time for search date
		$add_stime	= ' 00:00:00';
		$add_etime	= ' 23:59:59';
		### regist date
		if( !empty($sc['regist_sdate']) && !empty($sc['regist_edate']) ){
			$sqlWhereClause .= " AND A.regist_date between '{$sc['regist_sdate']}{$add_stime}' and '{$sc['regist_edate']}{$add_etime}' ";
		}else if( !empty($sc['regist_sdate']) && empty($sc['regist_edate']) ){
			$sqlWhereClause .= " AND A.regist_date >= '{$sc['regist_sdate']}{$add_stime}' ";
		}else if( empty($sc['regist_sdate']) && !empty($sc['regist_edate']) ){
			$sqlWhereClause .= " AND A.regist_date <= '{$sc['regist_edate']}{$add_etime}' ";
		}

		### lastlogin date
		if( !empty($sc['lastlogin_sdate']) && !empty($sc['lastlogin_edate']) ){
			if($sc['lastlogin_search_type'] == 'out'){
				$sqlWhereClause .= " AND (A.lastlogin_date < '{$sc['lastlogin_sdate']}{$add_stime}' or A.lastlogin_date > '{$sc['lastlogin_edate']}{$add_etime}') ";
			}else{
				$sqlWhereClause .= " AND A.lastlogin_date between '{$sc['lastlogin_sdate']}{$add_stime}' and '{$sc['lastlogin_edate']}{$add_etime}' ";
			}
		}else if( !empty($sc['lastlogin_sdate']) && empty($sc['lastlogin_edate']) ){
			if($sc['lastlogin_search_type'] == 'out'){
				$sqlWhereClause .= " AND A.lastlogin_date < '{$sc['lastlogin_sdate']}{$add_stime}' ";
			}else{
				$sqlWhereClause .= " AND A.lastlogin_date >= '{$sc['lastlogin_sdate']}{$add_stime}' ";
			}
		}else if( empty($sc['lastlogin_sdate']) && !empty($sc['lastlogin_edate']) ){
			if($sc['lastlogin_search_type'] == 'out'){
				$sqlWhereClause .= " AND A.lastlogin_date > '{$sc['lastlogin_edate']}{$add_etime}' ";
			}else{
				$sqlWhereClause .= " AND A.lastlogin_date <= '{$sc['lastlogin_edate']}{$add_etime}' ";
			}
		}

		### referer
		if	($sc['referer']){
			$sqlWhereClause	.= " AND (IF(C.referer_group_no>0, C.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력'))) = '" . $sc['referer'] . "' ";
		}

		### birthday date
		$birthday_fld	= "A.birthday";
		if	($sc['birthday_year_except'] == 'Y'){
			$birthday_fld	= "RIGHT(REPLACE(A.birthday, '-', ''), 4)";
			if	(!empty($sc['birthday_sdate']))
				$sc['birthday_sdate']	= str_replace('-', '', substr($sc['birthday_sdate'], 5));
			if	(!empty($sc['birthday_edate']))
				$sc['birthday_edate']	= str_replace('-', '', substr($sc['birthday_edate'], 5));
		}
		if( !empty($sc['birthday_sdate']) && !empty($sc['birthday_edate'])){
			$sqlWhereClause .= " AND ".$birthday_fld." between '{$sc['birthday_sdate']}' and '{$sc['birthday_edate']}' ";
		}else if( !empty($sc['birthday_sdate']) && empty($sc['birthday_edate']) ){
			$sqlWhereClause .= " AND ".$birthday_fld." >= '{$sc['birthday_sdate']}'";
		}else if( empty($sc['birthday_sdate']) && !empty($sc['birthday_edate']) ){
			$sqlWhereClause .= " AND ".$birthday_fld." <= '{$sc['birthday_edate']}' ";
		}

		### anniversary date
		if(!empty($sc['anniversary_sdate'][0]) && !empty($sc['anniversary_sdate'][1]))
				$sc['anniversary_sdate'] = implode("-",$sc['anniversary_sdate']);
		else	$sc['anniversary_sdate'] = null;
		if(!empty($sc['anniversary_edate'][0]) && !empty($sc['anniversary_edate'][1]))
				$sc['anniversary_edate'] = implode("-",$sc['anniversary_edate']);
		else	$sc['anniversary_edate'] = null;
		if( !empty($sc['anniversary_sdate']))
			$sc['anniversary_sdate']	= date('md', strtotime(date('Y-') . $sc['anniversary_sdate']));
		if( !empty($sc['anniversary_edate']))
			$sc['anniversary_edate']	= date('md', strtotime(date('Y-') . $sc['anniversary_edate']));
		if( !empty($sc['anniversary_sdate']) && !empty($sc['anniversary_edate'])){
			$sqlWhereClause .= " AND REPLACE(A.anniversary, '-', '') between '{$sc['anniversary_sdate']}' and '{$sc['anniversary_edate']}' ";
		}else if( !empty($sc['anniversary_sdate']) && empty($sc['anniversary_edate']) ){
			$sqlWhereClause .= " AND LENGTH(A.anniversary) > 0 AND REPLACE(A.anniversary, '-', '') >= '{$sc['anniversary_sdate']}' ";
		}else if( empty($sc['anniversary_sdate']) && !empty($sc['anniversary_edate']) ){
			$sqlWhereClause .= " AND LENGTH(A.anniversary) > 0 AND REPLACE(A.anniversary, '-', '') <= '{$sc['anniversary_edate']}' ";
		}

		### DATE promotion > coupon 발급시 회원검색
		if( !empty($sc['date_gb']) && !empty($sc['sdate']) && !empty($sc['edate'])){
			$sqlWhereClause .= " AND A.{$sc['date_gb']} between '{$sc['sdate']} 00:00:00' and '{$sc['edate']} 23:59:59' ";
		}

		### sms
		if( !empty($sc['sms']) ){
			$sqlWhereClause .= " AND A.sms = '{$sc[sms]}' ";
		}
		### mailing
		if( !empty($sc['mailing']) ){
			$sqlWhereClause .= " AND A.mailing = '{$sc[mailing]}' ";
		}
		### business_seq
		if( !empty($sc['business_seq']) ){
			$sqlWhereClause .= $sc['business_seq']=='n' ? " AND B.business_seq is null " : " AND B.business_seq != '' ";
		}
		### status
		if( !empty($sc['status']) ){
			$sqlWhereClause .= " AND A.status = '{$sc[status]}' ";
		}
		### grade
		if( !empty($sc['grade']) ){
			$sqlWhereClause .= " AND A.group_seq = '{$sc[grade]}' ";
		}

		### sitetype
		if( !empty($sc['sitetype']) ){
			$sqlWhereClause .= " AND A.sitetype in ('{$sc[sitetype]}') ";
		}

		### 가입양식start
		if( !empty($sc['none']) )$snssqlWhereClause[] = " (A.rute = 'none') ";
		if( !empty($sc['sns_f']) )$snssqlWhereClause[] = " (A.sns_f is not null  AND A.sns_f  <> '' ) ";
		if( !empty($sc['sns_t']) )$snssqlWhereClause[] = " (A.sns_t is not null AND A.sns_t  <>  '' ) ";
		if( !empty($sc['sns_y']) )$snssqlWhereClause[] = " (A.sns_y is not null AND A.sns_y <>'' ) ";
		if( !empty($sc['sns_c']) )$snssqlWhereClause[] = " (A.sns_c is not null AND A.sns_c <>'' ) ";
		if( !empty($sc['sns_m']) )$snssqlWhereClause[] = " (A.sns_m is not null AND A.sns_m <>'' ) ";
		if( !empty($sc['sns_g']) )$snssqlWhereClause[] = " (A.sns_g is not null  AND A.sns_g <>'' ) ";
		if( !empty($sc['sns_p']) )$snssqlWhereClause[] = " (A.sns_p is not null AND A.sns_p <>'' )";
		if( !empty($sc['sns_k']) )$snssqlWhereClause[] = " (A.sns_k is not null AND A.sns_k <>'' )";
		if( !empty($sc['sns_n']) )$snssqlWhereClause[] = " (A.sns_n is not null AND A.sns_n <>'' )";
		if( !empty($sc['sns_d']) )$snssqlWhereClause[] = " (A.sns_d is not null AND A.sns_d <>'' )";
		if($snssqlWhereClause) $sqlWhereClause .= " AND (".implode(" OR ", $snssqlWhereClause)." ) ";
		### 가입양식end

		### order_sum
		if( !empty($sc['sorder_sum']) && !empty($sc['eorder_sum']) ){
			$sqlWhereClause .= " AND A.member_order_price between '{$sc['sorder_sum']}' and '{$sc['eorder_sum']}' ";
		}else if( !empty($sc['sorder_sum']) && empty($sc['eorder_sum']) ){
			$sqlWhereClause .= " AND A.member_order_price >= '{$sc['sorder_sum']}' ";
		}else if( empty($sc['sorder_sum']) && !empty($sc['eorder_sum']) ){
			$sqlWhereClause .= " AND A.member_order_price <= '{$sc['eorder_sum']}' ";
		}

		### emoney
		if( !empty($sc['semoney']) && !empty($sc['eemoney']) ){
			$sqlWhereClause .= " AND A.emoney between '{$sc['semoney']}' and '{$sc['eemoney']}' ";
		}else if( !empty($sc['semoney']) && empty($sc['eemoney']) ){
			$sqlWhereClause .= " AND A.emoney >= '{$sc['semoney']}' ";
		}else if( empty($sc['semoney']) && !empty($sc['eemoney']) ){
			$sqlWhereClause .= " AND A.emoney <= '{$sc['eemoney']}' ";
		}
		if( !empty($sc['spoint']) && !empty($sc['epoint']) ){
			$sqlWhereClause .= " AND A.point between '{$sc['spoint']}' and '{$sc['epoint']}' ";
		}else if( !empty($sc['spoint']) && empty($sc['epoint']) ){
			$sqlWhereClause .= " AND A.point >= '{$sc['spoint']}' ";
		}else if( empty($sc['spoint']) && !empty($sc['epoint']) ){
			$sqlWhereClause .= " AND A.point <= '{$sc['epoint']}' ";
		}
		if( !empty($sc['scash']) && !empty($sc['ecash']) ){
			$sqlWhereClause .= " AND A.cash between '{$sc['scash']}' and '{$sc['ecash']}' ";
		}else if( !empty($sc['scash']) && empty($sc['ecash']) ){
			$sqlWhereClause .= " AND A.cash >= '{$sc['scash']}' ";
		}else if( empty($sc['scash']) && !empty($sc['ecash']) ){
			$sqlWhereClause .= " AND A.cash <= '{$sc['ecash']}' ";
		}

		### order_cnt
		if( !empty($sc['sorder_cnt']) && !empty($sc['eorder_cnt'])){
			$sqlWhereClause .= " AND A.member_order_cnt between '{$sc['sorder_cnt']}' and '{$sc['eorder_cnt']}' ";
		}else if( !empty($sc['sorder_cnt']) && empty($sc['eorder_cnt']) ){
			$sqlWhereClause .= " AND A.member_order_cnt >= '{$sc['sorder_cnt']}' ";
		}else if( empty($sc['sorder_cnt']) && !empty($sc['eorder_cnt']) ){
			$sqlWhereClause .= " AND A.member_order_cnt <= '{$sc['eorder_cnt']}' ";
		}

		### review_cnt
		if( !empty($sc['sreview_cnt']) && !empty($sc['ereview_cnt'])){
			$sqlWhereClause .= " AND A.review_cnt between '{$sc['sreview_cnt']}' and '{$sc['ereview_cnt']}' ";
		}else if( !empty($sc['sreview_cnt']) && empty($sc['ereview_cnt']) ){
			$sqlWhereClause .= " AND A.review_cnt >= '{$sc['sreview_cnt']}' ";
		}else if( empty($sc['sreview_cnt']) && !empty($sc['ereview_cnt']) ){
			$sqlWhereClause .= " AND A.review_cnt <= '{$sc['ereview_cnt']}' ";
		}

		### login_cnt
		if( !empty($sc['slogin_cnt']) && !empty($sc['elogin_cnt'])){
			$sqlWhereClause .= " AND A.login_cnt between '{$sc['slogin_cnt']}' and '{$sc['elogin_cnt']}' ";
		}else if( !empty($sc['slogin_cnt']) && empty($sc['elogin_cnt']) ){
			$sqlWhereClause .= " AND A.login_cnt >= '{$sc['slogin_cnt']}' ";
		}else if( empty($sc['slogin_cnt']) && !empty($sc['elogin_cnt']) ){
			$sqlWhereClause .= " AND A.login_cnt <= '{$sc['elogin_cnt']}' ";
		}

		if( !empty($sc['goods_seq']) && !empty($sc['goods_seq_cond'])){
			switch($sc['goods_seq_cond']){
				case "fblike":
					$sqlFromClause .= "
						inner join fm_goods_fblike on A.member_seq = fm_goods_fblike.member_seq and fm_goods_fblike.goods_seq = '{$sc['goods_seq']}'
					";
					$sqlWhereClause .=" group by fm_goods_fblike.member_seq ";
				break;
				case "cart":
					$sqlFromClause .= "
						inner join fm_cart on A.member_seq = fm_cart.member_seq and fm_cart.goods_seq = '{$sc['goods_seq']}'
					";
					$sqlWhereClause .=" group by fm_cart.member_seq ";
				break;
				case "wish":
					$sqlFromClause .= "
						inner join fm_goods_wish on A.member_seq = fm_goods_wish.member_seq and fm_goods_wish.goods_seq = '{$sc['goods_seq']}'
					";
					$sqlWhereClause .=" group by fm_goods_wish.member_seq ";
				break;
			}
		}

		$sqlOrderClause .=" order by {$sc['orderby']} {$sc['sort']}";

		if	($sc['nolimit'] != 'y')
			$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$sql = "
			{$sqlSelectClause}
			{$sqlFromClause}
			{$sqlWhereClause}
			{$sqlOrderClause}
		";

		//echo $sql;

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$cnt_query = 'select count(*) as cnt '. $sqlFromClause . $sqlWhereClause;
		$cntquery = $this->db->query($cnt_query);
		$cntrow = $cntquery->result_array();
		$data['count'] = $cntrow[0]['cnt'];

		return $data;
	}


	/* ADMIN > SETTING */
	public function admin_manager_list($sc,$all=null) {
		$key = get_shop_key();
		$sql = "select
				A.*
			from
				fm_manager A
			where manager_id!='gabia'";

		###
		if( !empty($sc['search_text'])){
			$sql .= ' and ( manager_id like "%'.$sc['search_text'].'%" or mname like "%'.$sc['search_text'].'%" ) ';
		}
		
		$sc['orderby']	= (isset($sc['orderby'])) ?	$sc['orderby']:'manager_seq';
		$sc['sort']			= (isset($sc['sort'])) ?		$sc['sort']:'desc';
		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		if(!$all) $limit =" limit {$sc['page']}, {$sc['perpage']} ";

		//echo $sql;
		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		if(!$all) {
			$query = $this->db->query($sql);
			$data['count'] = $query->num_rows();
		} 

		return $data;
	}

	public function manager_auth_list(){
		$auth_list = config_load("admin_auth");
		foreach($auth_list as $k=>$v){
			$auth_arr[] = $k;
		}

		$auth_text = "";
		foreach($auth_arr as $k){
			if($k=='setting_manager_view'){
				$value = 'Y';
			}else{
				$value = if_empty($_POST, $k, 'N');
			}
			$auth_text .= $k."=".$value."||";
		}

		return $auth_text;
	}

	/* ADMIN > MEMBER */
	public function popup_member_list($sc) {


		$key = get_shop_key();

		$sql = "select SQL_CALC_FOUND_ROWS *,
				A.*,
				AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
				AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				CASE WHEN A.status = 'done' THEN '승인'
					 WHEN A.status = 'hold' THEN '미승인'
					 WHEN A.status = 'withdrawal' THEN '탈퇴'
				ELSE '' END AS status_nm,
				B.business_seq,
				B.bname,
				C.group_name
			from
				fm_member A
				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
			where 1";

		###
		$sql .= " AND A.status != 'withdrawal' ";
		###
		if( !empty($sc['search_text'])){

			$sql .= " and ( A.userid like '%".$sc['search_text']."%' or A.user_name like '%".$sc['search_text']."%' or AES_DECRYPT(UNHEX(A.email), '{$key}') like '%".$sc['search_text']."%' or AES_DECRYPT(UNHEX(A.phone), '{$key}') like '%".$sc['search_text']."%' or AES_DECRYPT(UNHEX(A.cellphone), '{$key}') like '%".$sc['search_text']."%' or A.address like '%".$sc['search_text']."%'  or A.address_detail like '%".$sc['search_text']."%' or B.bname like '%".$sc['search_text']."%'  or B.baddress  like '%".$sc['search_text']."%' or B.bphone  like '%".$sc['search_text']."%' or B.bcellphone like '%".$sc['search_text']."%'  or B.baddress_detail like '%".$sc['search_text']."%' ) ";

		}
		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate'])){
			$sql .= " AND A.{$sc['date_gb']} between '{$sc['sdate']}' and '{$sc['edate']}' ";
		}
		### sms
		if( !empty($sc['sms']) ){
			$sql .= " AND A.sms = '{$sc[sms]}' ";
		}
		### mailing
		if( !empty($sc['mailing']) ){
			$sql .= " AND A.mailing = '{$sc[mailing]}' ";
		}
		### business_seq
		if( !empty($sc['business_seq']) ){
			$sql .= $sc['business_seq']=='n' ? " AND B.business_seq is null " : " AND B.business_seq != '' ";
		}
		### status
		if( !empty($sc['status']) ){
			$sql .= " AND A.status = '{$sc[status]}' ";
		}
		### grade
		if( !empty($sc['grade']) ){
			$sql .= " AND A.group_seq = '{$sc[grade]}' ";
		}

		### groups array()
		if( !empty($sc['groupsar']) ){
			$groups = implode("','",$sc['groupsar']);
			$sql .= " AND A.group_seq in ('".$groups."')";
		}

		### order_sum
		if( !empty($sc['sorder_sum']) && !empty($sc['eorder_sum'])){
			$sql .= " AND A.order_sum between '{$sc['sorder_sum']}' and '{$sc['eorder_sum']}' ";
		}
		### emoney
		if( !empty($sc['semoney']) && !empty($sc['eemoney'])){
			$sql .= " AND A.emoney between '{$sc['semoney']}' and '{$sc['eemoney']}' ";
		}
		### order_cnt
		if( !empty($sc['sorder_cnt']) && !empty($sc['eorder_cnt'])){
			$sql .= " AND A.order_cnt between '{$sc['sorder_cnt']}' and '{$sc['eorder_cnt']}' ";
		}
		### review_cnt

		### login_cnt
		if( !empty($sc['slogin_cnt']) && !empty($sc['elogin_cnt'])){
			$sql .= " AND A.login_cnt between '{$sc['slogin_cnt']}' and '{$sc['eordelogin_cnter_sum']}' ";
		}

		if($sc['orderby'] && $sc['sort']) {
			$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		}

		if(isset($sc['page']) && isset($sc['perpage'])) {
			$limit =" limit {$sc['page']}, {$sc['perpage']} ";
		}
		//echo $sql;exit;

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();


		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		return $data;
	}

	/* ADMIN > MEMBER */
	public function admin_withdrawal_list($sc) {


		$key = get_shop_key();

		$sql = "select
				A.*,
				B.userid,
				B.user_name,
				B.order_cnt,
				B.order_sum,
				B.review_cnt,
				B.login_cnt
			from
				fm_member_withdrawal A
				LEFT JOIN fm_member B ON A.member_seq = B.member_seq
			where 1";

		###
		$sql .= " AND B.status = 'withdrawal' ";

		###
		if( !empty($sc['keyword']))
		{
			$sql .= ' and userid like "%'.$sc['keyword'].'%" ';
		}

		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate']))
		{
			$sql .= " AND A.regist_date between '{$sc['sdate']}' and '{$sc['edate']}' ";
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$query = $this->db->query($sql);
		$data['count'] = $query->num_rows();

		return $data;
	}

	/* ADMIN > MEMBER */
	public function email_history_list($sc) {


		$sql = "select * from fm_log_email";
		$sqltotal = "select  count(*) as cnt from fm_log_email ";

		$where = array();
		if( !empty($sc['start_date']) && !empty($sc['end_date']) ){
			$where[] = " regdate between '{$sc['start_date']} 00:00:00' and '{$sc['end_date']} 23:59:59' ";
		}

		if( !empty($sc['sc_subject']) ){
			$where[] = " subject like '%{$sc['sc_subject']}%' ";
		}

		if( !empty($sc['sc_gb']) ){
			$where[] = " gb='".$sc['sc_gb']."'";
		}else{
			$where[] = " gb != 'PERSONAL'";
		}

		if (!empty($sc['order_seq'])) {
			$where[] = sprintf("order_seq = '%s' ", $sc['order_seq']);
		}

		if($where) { $sql .= " where ".implode(" and ",$where); }
		$sql .= ($sc['orderby'])? " order by {$sc['orderby']} {$sc['sort']}":"";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		if($where) { $sqltotal .= " where ".implode(" and ",$where); }
		$query2 = $this->db->query($sqltotal);
		$cntrow = $query2->result_array();
		$data['count'] = $cntrow[0]['cnt'];

		return $data;
	}


	/* ADMIN > MEMBER */
	public function admin_member_url($file_path) {
		$file_nm = end(explode("/",$file_path));
		$file_arr = explode(".",$file_nm);
		return $file_arr[0];
	}


	/* ADMIN > MEMBER */
	public function sms_form_list($sc) {


		$sql = "select * from fm_sms_album where 1";

		if( !empty($sc['category']) )
		{
			$sql .= " AND category = '{$sc['category']}' ";
		}


		$sql .=" order by seq desc";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$query = $this->db->query($sql);
		$data['count'] = $query->num_rows();

		return $data;
	}

	/* 회원 그룹 추가 적립금 */
	public function get_group_benifit($group_seq,$sale_seq)
	{
		$query = "select a.*,ifnull((select group_name from fm_member_group where group_seq=a.group_seq),'비회원') group_name from fm_member_group_sale_detail a where a.group_seq = ? and a.sale_seq = ?";
		$query = $this->db->query($query, array((int)$group_seq,(int)$sale_seq));
		list($data) = $query->result_array($query);
		return $data;
	}

	public function get_goods_group_benifits($sale_seq)
	{
		$query = "select a.*,ifnull((select group_name from fm_member_group where group_seq=a.group_seq),'비회원') group_name, (select use_type from fm_member_group where group_seq = a.group_seq) as use_type from fm_member_group_sale_detail a where a.sale_seq = ?";
		$query = $this->db->query($query, array((int)$sale_seq));
		$data = $query->result_array($query);
		return $data;
	}

	public function get_group_except_category($group_seq,$sale_seq,$category,$type)
	{
		$query = "select count(*) cnt from fm_member_group_issuecategory where type=? and sale_seq = ? and category_code in('".$category."')";
		$query = $this->db->query($query,array($type,(int)$sale_seq));
		$cnt = $query->row_array();
		return $cnt['cnt'];
	}

	public function get_group_except_goods_seq($group_seq,$sale_seq,$goods_seq,$type)
	{
		$query = "select count(*) cnt from fm_member_group_issuegoods where type=? and sale_seq=? and goods_seq=?";
		$query = $this->db->query($query,array($type,(int)$sale_seq,(int)$goods_seq));
		$cnt = $query->row_array();
		return $cnt['cnt'];
	}

	// $category array 카테고리 코드
	public function get_group_addreseve($member_seq,$goods_price,$order_price,$goods_seq='',$category='', $sale_seq='', $group_seq='', $benifit_type='reserve'){
		$reserve = 0;

		if(!$group_seq){
			$data_member = $this->get_member_data($member_seq);
			$group_seq = $data_member['group_seq'];
		}
		$data = $this->get_group_benifit($group_seq,$sale_seq);

		if( $category ){
			$category_in = implode("','",$category);
			$cnt = $this->get_group_except_category($data['group_seq'],$sale_seq,$category_in,'emoney');
			if( $cnt > 0 ){
				return 0;
			}
		}

		if( $goods_seq ){
			$cnt = $this->get_group_except_goods_seq($data['group_seq'],$sale_seq,$goods_seq,'emoney');
			if( $cnt > 0 ){
				return 0;
			}
		}

		if($benifit_type == 'reserve')
		{
			if( $data['reserve_price_type'] == 'PER' && $data['reserve_price'] && $goods_price ){
				$reserve = ($goods_price * $data['reserve_price'])/100;
			}
			if($data['point_use'] == 'Y' && $order_price && $order_price < $data['point_limit_price']){
				return 0;
			}
		}else{
			if( $data['point_price_type'] == 'PER' && $data['point_price'] && $goods_price ){
				$reserve = ($goods_price * $data['point_price'])/100;
			}
			if($data['point_use'] == 'Y' && $order_price && $order_price < $data['point_limit_price']){
				return 0;
			}
		}



		return $reserve;

	}

	/* 회원 그룹 할인계산 */
	public function get_member_group($group_seq,$goods_seq,$category,$goods_price,$tot_price=0, $sale_seq="", $benifit_type='option'){
		$member_sale = 0;
		if( ! $this->config_system['cutting_price'] ) $this->config_system['cutting_price'] = 10;
		$data = $this->group_benifit = $this->get_group_benifit($group_seq,$sale_seq);

		$category_in = "";
		if( $category ){
			if(is_array($category)){
				if(count($category) == 1){
					$category_in = "'".$category[0]."'";
				}else{
					$category_in = implode("','",$category);
					$category_in = "'".$category_in."'";
				}
			}else{
				if(strlen($category) >= 4){
					for($i=1; $i<=(strlen($category)/4); $i++){
						if($category_in == ""){
							$category_in = "'".substr($category, 0, $i*4)."'";
						}else{
							$category_in .= ",'".substr($category, 0, $i*4)."'";
						}
					}
				}
			}

			$query = "select count(*) cnt from fm_member_group_issuecategory where type='sale' and sale_seq = '".$sale_seq."' and category_code in (".$category_in.")";
			$query = $this->db->query($query);
			$cnt = $query->row_array();
			if( $cnt['cnt'] > 0 ){
				return 0;
			}
		}

		if( $goods_seq ){
			$query = "select count(*) cnt from fm_member_group_issuegoods where type='sale' and sale_seq='".$sale_seq."' and goods_seq = ?";
			$query = $this->db->query($query,array($goods_seq));
			$cnt = $query->row_array();
			if( $cnt['cnt'] > 0 ){
				return 0;
			}
		}

		$sale_type_field	= 'sale_price_type';
		$sale_price_field	= 'sale_price';
		if($benifit_type == 'suboption'){
			$sale_type_field	= 'sale_option_price_type';
			$sale_price_field	= 'sale_option_price';
		}

		if( $data[$sale_type_field] == 'PER' && $data[$sale_price_field] && $goods_price ){
			$member_sale = ( $goods_price * $data[$sale_price_field] );
			$is_calculate = true;
			if ($this->config_system['cutting_sale_use']=='none' || !empty($this->config_system['cutting_sale_price'])) $is_calculate = false;

			if( $is_calculate && $this->config_system['cutting_price'] != 'none' ){
				$member_sale = $member_sale / ( $this->config_system['cutting_price'] * 100);
				$member_sale = floor($member_sale);
				$member_sale = $member_sale * $this->config_system['cutting_price'];
			}else{
				$member_sale = $member_sale / 100;
				$member_sale = floor($member_sale);
			}
		}else if( $data[$sale_type_field] == 'WON' && $data[$sale_price_field] && $goods_price ){
			$member_sale = $data[$sale_price_field];
		}

		if($data['sale_use'] == 'Y' && $tot_price){
			if( $data['sale_limit_price'] > $tot_price  ){
				$member_sale = 0;
			}
		}

		// 등급할인가 절삭
		$member_sale = get_price_point($member_sale);

		return $member_sale;
	}

	/* 회원 적립금 차감 */
	public function set_member_emoney($emoney,$member_seq){
		$this->db->query('update fm_member set emoney=emoney-? where member_seq=?',array($emoney,$member_seq));
	}

	public function set_member_cash($emoney,$member_seq){
		$this->db->query('update fm_member set cash=cash-? where member_seq=?',array($emoney,$member_seq));
	}

	public function set_withdrawal_admin($params){
		$data = filter_keys($params, $this->db->list_fields('fm_member_withdrawal'));
		$result = $this->db->insert('fm_member_withdrawal', $data);

		### member_update
		$member['password']			= "";
		$member['user_name']		= "";
		$member['email']			= "";
		$member['phone']			= "";
		$member['cellphone']		= "";
		$member['zipcode']			= "";
		$member['address_type']		= "";
		$member['address']			= "";
		$member['address_street']	= "";
		$member['address_detail']	= "";
		$member['birthday']			= "";
		$member['auth_code']		= "";
		$member['auth_vno']			= "";
		$member['status']			= "withdrawal";
		$member['auth_vno']			= "";
		$member['auth_code']			= "";
		$member['auth_type']			= "";

		$mbdata = $this->get_member_data($params['member_seq']);
		if($mbdata['rute'] != 'none'){//SNS회원인 경우
			$member['userid']			= "withdrawal_".$mbdata['userid'];
		}

		$member['sns_f']		= "";
		$member['sns_t']		= "";
		$member['sns_m']		= "";
		$member['sns_y']		= "";
		$member['sns_c']		= "";
		$member['sns_g']		= "";
		$member['sns_p']		= "";
		$member['sns_n']		= "";
		$member['sns_k']		= "";
		$member['sns_d']		= "";

		$result = $this->db->update('fm_member', $member, array('member_seq'=>$params['member_seq']));

		$this->db->delete('fm_member_business', array('member_seq'=>$params['member_seq']));//기업회원 삭제
		$this->db->delete('fm_membersns', array('member_seq'=>$params['member_seq']));//SNS회원 정삭제
		//

		return $result;
	}


	public function admin_search_list($sc){
		$sc['nolimit']='y';
		return $this->admin_member_list($sc);
		/*

		$key = get_shop_key();

		$sql = "select
				A.*,
				AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
				AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				CASE WHEN A.status = 'done' THEN '승인'
					 WHEN A.status = 'hold' THEN '미승인'
					 WHEN A.status = 'withdrawal' THEN '탈퇴'
				ELSE '' END AS status_nm,
				B.business_seq,
				B.bname,
				C.group_name,
				IF(D.referer_group_no>0, D.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력')) as referer_name,
				B.bcellphone
			from
				fm_member A
				LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
				LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
				LEFT JOIN fm_referer_group D ON A.referer_domain = D.referer_group_url
			where 1";

		###
		$sql .= " AND A.status in ('done','hold') ";
		###
		### add start, end time for search date
		$add_stime	= ' 00:00:00';
		$add_etime	= ' 23:59:59';

		if( !empty($sc['search_text'])){
			if($sc['search_text']!='이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소'){
				$sql .= " and ( userid like '%".$sc['search_text']."%' or user_name like '%".$sc['search_text']."%' or AES_DECRYPT(UNHEX(A.email), '{$key}') like '%".$sc['search_text']."%' or AES_DECRYPT(UNHEX(A.phone), '{$key}') like '%".$sc['search_text']."%' or AES_DECRYPT(UNHEX(A.cellphone), '{$key}') like '%".$sc['search_text']."%' or A.address like '%".$sc['search_text']."%' ) ";
			}
		}
		###
		if( !empty($sc['keyword'])){
			if($sc['keyword']!='이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소'){
				$sql .= " and ( A.userid like '%".$sc['keyword']."%' or A.user_name like '%".$sc['keyword']."%' or AES_DECRYPT(UNHEX(A.email), '{$key}') like '%".$sc['keyword']."%' or AES_DECRYPT(UNHEX(A.phone), '{$key}') like '%".$sc['keyword']."%' or AES_DECRYPT(UNHEX(A.cellphone), '{$key}') like '%".$sc['keyword']."%' or A.address like '%".$sc['keyword']."%' or A.address_detail like '%".$sc['keyword']."%' or A.nickname like '%".$sc['keyword']."%' or B.bname like '%".$sc['keyword']."%'  or B.baddress  like '%".$sc['keyword']."%' or B.bphone  like '%".$sc['keyword']."%' or B.bcellphone like '%".$sc['keyword']."%' or B.baddress_detail like '%".$sc['keyword']."%'  or B.bceo like '%".$sc['keyword']."%') ";
			}
		}
		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate'])){
			$sql .= " AND A.{$sc['date_gb']} between '{$sc['sdate']}' and '{$sc['edate']}' ";
		}
		### regist date
		if( !empty($sc['regist_sdate']) && !empty($sc['regist_edate']) ){
			$sql .= " AND A.regist_date between '{$sc['regist_sdate']}{$add_stime}' and '{$sc['regist_edate']}{$add_etime}' ";
		}else if( !empty($sc['regist_sdate']) && empty($sc['regist_edate']) ){
			$sql .= " AND A.regist_date >= '{$sc['regist_sdate']}{$add_stime}' ";
		}else if( empty($sc['regist_sdate']) && !empty($sc['regist_edate']) ){
			$sql .= " AND A.regist_date <= '{$sc['regist_edate']}{$add_etime}' ";
		}
		### lastlogin date
		if( !empty($sc['lastlogin_sdate']) && !empty($sc['lastlogin_edate']) ){
			if($sc['lastlogin_search_type'] == 'out'){
				$sql .= " AND (A.lastlogin_date < '{$sc['lastlogin_sdate']}{$add_stime}' or A.lastlogin_date > '{$sc['lastlogin_edate']}{$add_etime}') ";
			}else{
				$sql .= " AND A.lastlogin_date between '{$sc['lastlogin_sdate']}{$add_stime}' and '{$sc['lastlogin_edate']}{$add_etime}' ";
			}
		}else if( !empty($sc['lastlogin_sdate']) && empty($sc['lastlogin_edate']) ){
			if($sc['lastlogin_search_type'] == 'out'){
				$sql .= " AND A.lastlogin_date < '{$sc['lastlogin_sdate']}{$add_stime}' ";
			}else{
				$sql .= " AND A.lastlogin_date >= '{$sc['lastlogin_sdate']}{$add_stime}' ";
			}
		}else if( empty($sc['lastlogin_sdate']) && !empty($sc['lastlogin_edate']) ){
			if($sc['lastlogin_search_type'] == 'out'){
				$sql .= " AND A.lastlogin_date > '{$sc['lastlogin_edate']}{$add_etime}' ";
			}else{
				$sql .= " AND A.lastlogin_date <= '{$sc['lastlogin_edate']}{$add_etime}' ";
			}
		}
		### referer
		if	($sc['referer']){
			$sql	.= " AND (IF(D.referer_group_no>0, D.referer_group_name, IF(LENGTH(A.referer)>0,'기타','직접입력'))) = '" . $sc['referer'] . "' ";
		}
		### birthday date
		$birthday_fld	= "A.birthday";
		if	($sc['birthday_year_except'] == 'Y'){
			$birthday_fld	= "RIGHT(REPLACE(A.birthday, '-', ''), 4)";
			if	(!empty($sc['birthday_sdate']))
				$sc['birthday_sdate']	= str_replace('-', '', substr($sc['birthday_sdate'], 5));
			if	(!empty($sc['birthday_edate']))
				$sc['birthday_edate']	= str_replace('-', '', substr($sc['birthday_edate'], 5));
		}
		if( !empty($sc['birthday_sdate']) && !empty($sc['birthday_edate'])){
			$sql .= " AND ".$birthday_fld." between '{$sc['birthday_sdate']}' and '{$sc['birthday_edate']}' ";
		}else if( !empty($sc['birthday_sdate']) && empty($sc['birthday_edate']) ){
			$sql .= " AND ".$birthday_fld." >= '{$sc['birthday_sdate']}'";
		}else if( empty($sc['birthday_sdate']) && !empty($sc['birthday_edate']) ){
			$sql .= " AND ".$birthday_fld." <= '{$sc['birthday_edate']}' ";
		}
		### anniversary date
		if(!empty($sc['anniversary_sdate'][0]) && !empty($sc['anniversary_sdate'][1]))
				$sc['anniversary_sdate'] = implode("-",$sc['anniversary_sdate']);
		else	$sc['anniversary_sdate'] = null;
		if(!empty($sc['anniversary_edate'][0]) && !empty($sc['anniversary_edate'][1]))
				$sc['anniversary_edate'] = implode("-",$sc['anniversary_edate']);
		else	$sc['anniversary_edate'] = null;
		if( !empty($sc['anniversary_sdate']))
			$sc['anniversary_sdate']	= date('md', strtotime(date('Y-') . $sc['anniversary_sdate']));
		if( !empty($sc['anniversary_edate']))
			$sc['anniversary_edate']	= date('md', strtotime(date('Y-') . $sc['anniversary_edate']));
		if( !empty($sc['anniversary_sdate']) && !empty($sc['anniversary_edate'])){
			$sql .= " AND REPLACE(A.anniversary, '-', '') between '{$sc['anniversary_sdate']}' and '{$sc['anniversary_edate']}' ";
		}else if( !empty($sc['anniversary_sdate']) && empty($sc['anniversary_edate']) ){
			$sql .= " AND LENGTH(A.anniversary) > 0 AND REPLACE(A.anniversary, '-', '') >= '{$sc['anniversary_sdate']}' ";
		}else if( empty($sc['anniversary_sdate']) && !empty($sc['anniversary_edate']) ){
			$sql .= " AND LENGTH(A.anniversary) > 0 AND REPLACE(A.anniversary, '-', '') <= '{$sc['anniversary_edate']}' ";
		}
		### sms
		if( !empty($sc['sms']) ){
			$sql .= " AND A.sms = '{$sc[sms]}' ";
		}
		### mailing
		if( !empty($sc['mailing']) ){
			$sql .= " AND A.mailing = '{$sc[mailing]}' ";
		}
		### business_seq
		if( !empty($sc['business_seq']) ){
			$sql .= $sc['business_seq']=='n' ? " AND B.business_seq is null " : " AND B.business_seq != '' ";
		}
		### status
		if( !empty($sc['status']) ){
			$sql .= " AND A.status = '{$sc[status]}' ";
		}
		### grade
		if( !empty($sc['grade']) ){
			$sql .= " AND A.group_seq = '{$sc[grade]}' ";
		}

		### sitetype
		if( !empty($sc['sitetype']) ){
			$sql .= " AND A.sitetype in ('{$sc[sitetype]}') ";
		}

		### 가입양식start
		if( !empty($sc['none']) )$snssqlWhereClause[] = " (A.rute = 'none') ";
		if( !empty($sc['sns_f']) )$snssqlWhereClause[] = " (A.sns_f is not null ) ";
		if( !empty($sc['sns_t']) )$snssqlWhereClause[] = " (A.sns_t is not null) ";
		if( !empty($sc['sns_y']) )$snssqlWhereClause[] = " (A.sns_y is not null) ";
		if( !empty($sc['sns_c']) )$snssqlWhereClause[] = " (A.sns_c is not null) ";
		if( !empty($sc['sns_m']) )$snssqlWhereClause[] = " (A.sns_m is not null) ";
		if( !empty($sc['sns_g']) )$snssqlWhereClause[] = " (A.sns_g is not null ) ";
		if( !empty($sc['sns_p']) )$snssqlWhereClause[] = " (A.sns_p is not null)";
		if($snssqlWhereClause) $sql .= " AND (".implode(" OR ", $snssqlWhereClause)." ) ";
		### 가입양식end

		### groups array()
		if( !empty($sc['groupsar']) ){
			$groups = implode("','",$sc['groupsar']);
			$sql .= " AND A.group_seq in ('".$groups."')";
		}

		### order_sum
		if( !empty($sc['sorder_sum']) && !empty($sc['eorder_sum']) ){
			$sql .= " AND A.member_order_price between '{$sc['sorder_sum']}' and '{$sc['eorder_sum']}' ";
		}else if( !empty($sc['sorder_sum']) && empty($sc['eorder_sum']) ){
			$sql .= " AND A.member_order_price >= '{$sc['sorder_sum']}' ";
		}else if( empty($sc['sorder_sum']) && !empty($sc['eorder_sum']) ){
			$sql .= " AND A.member_order_price <= '{$sc['eorder_sum']}' ";
		}

		### emoney
		if( !empty($sc['semoney']) && !empty($sc['eemoney']) ){
			$sql .= " AND A.emoney between '{$sc['semoney']}' and '{$sc['eemoney']}' ";
		}else if( !empty($sc['semoney']) && empty($sc['eemoney']) ){
			$sql .= " AND A.emoney >= '{$sc['semoney']}' ";
		}else if( empty($sc['semoney']) && !empty($sc['eemoney']) ){
			$sql .= " AND A.emoney <= '{$sc['eemoney']}' ";
		}
		if( !empty($sc['spoint']) && !empty($sc['epoint']) ){
			$sql .= " AND A.point between '{$sc['spoint']}' and '{$sc['epoint']}' ";
		}else if( !empty($sc['spoint']) && empty($sc['epoint']) ){
			$sql .= " AND A.point >= '{$sc['spoint']}' ";
		}else if( empty($sc['spoint']) && !empty($sc['epoint']) ){
			$sql .= " AND A.point <= '{$sc['epoint']}' ";
		}
		if( !empty($sc['scash']) && !empty($sc['ecash']) ){
			$sql .= " AND A.cash between '{$sc['scash']}' and '{$sc['ecash']}' ";
		}else if( !empty($sc['scash']) && empty($sc['ecash']) ){
			$sql .= " AND A.cash >= '{$sc['scash']}' ";
		}else if( empty($sc['scash']) && !empty($sc['ecash']) ){
			$sql .= " AND A.cash <= '{$sc['ecash']}' ";
		}

		### order_cnt
		if( !empty($sc['sorder_cnt']) && !empty($sc['eorder_cnt'])){
			$sql .= " AND A.member_order_cnt between '{$sc['sorder_cnt']}' and '{$sc['eorder_cnt']}' ";
		}else if( !empty($sc['sorder_cnt']) && empty($sc['eorder_cnt']) ){
			$sql .= " AND A.member_order_cnt >= '{$sc['sorder_cnt']}' ";
		}else if( empty($sc['sorder_cnt']) && !empty($sc['eorder_cnt']) ){
			$sql .= " AND A.member_order_cnt <= '{$sc['eorder_cnt']}' ";
		}

		### review_cnt
		if( !empty($sc['sreview_cnt']) && !empty($sc['ereview_cnt'])){
			$sql .= " AND A.review_cnt between '{$sc['sreview_cnt']}' and '{$sc['ereview_cnt']}' ";
		}else if( !empty($sc['sreview_cnt']) && empty($sc['ereview_cnt']) ){
			$sql .= " AND A.review_cnt >= '{$sc['sreview_cnt']}' ";
		}else if( empty($sc['sreview_cnt']) && !empty($sc['ereview_cnt']) ){
			$sql .= " AND A.review_cnt <= '{$sc['ereview_cnt']}' ";
		}

		### login_cnt
		if( !empty($sc['slogin_cnt']) && !empty($sc['elogin_cnt'])){
			$sql .= " AND A.login_cnt between '{$sc['slogin_cnt']}' and '{$sc['elogin_cnt']}' ";
		}else if( !empty($sc['slogin_cnt']) && empty($sc['elogin_cnt']) ){
			$sql .= " AND A.login_cnt >= '{$sc['slogin_cnt']}' ";
		}else if( empty($sc['slogin_cnt']) && !empty($sc['elogin_cnt']) ){
			$sql .= " AND A.login_cnt <= '{$sc['elogin_cnt']}' ";
		}

		if(!$sc['orderby']) $sc['orderby'] = "A.member_seq";
		$sql .=" order by {$sc['orderby']} {$sc['sort']}";

		//echo $sql;
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		$query = $this->db->query($sql);
		$data['count'] = $query->num_rows();

		return $data;
		*/
	}


	public function emoney_insert($params, $member_seq){


		$data = filter_keys($params, $this->db->list_fields('fm_emoney'));
		$data['member_seq']		= !empty($member_seq)?$member_seq:'';
		$data['regist_date']	= date("Y-m-d H:i:s");
		if($params['gb']=='plus'){
			$data['remain'] = $data['emoney'];
		}

		$tmp = array(
			'HTTP_HOST'=>$_SERVER['HTTP_HOST'],
			'REMOTE_ADDR'=>$_SERVER['REMOTE_ADDR'],
			'REQUEST_URI'=>$_SERVER['REQUEST_URI'],
			'HTTP_REFERER'=>$_SERVER['HTTP_REFERER'],
			'GET'=>$_GET,
			'POST'=>$_POST
		);

		$data['sys_memo'] = serialize($tmp);

		$result = $this->db->insert('fm_emoney', $data);
		$data['emoney_seq'] = $this->db->insert_id();


		$sql = "select emoney from fm_member where member_seq = '{$member_seq}'";
		$query = $this->db->query($sql);
		$info = $query->result_array();

		$emoney = ($params['gb']=='plus') ? $info[0]['emoney']+$params['emoney'] : $info[0]['emoney']-$params['emoney'];
		if($emoney<0) $emoney = 0;

		###
		if($params['gb']=='minus'){
			$this->minus_pocess('emoney', $data);
		}

		$this->db->query('update fm_member set emoney=? where member_seq=?',array($emoney,$member_seq));

	}

	public function cash_insert($params, $member_seq){

		$reserve = config_load('reserve');
		if($reserve['cash_use']=='N' && $params['gb']=='plus'){
			return;
		}

		$data = filter_keys($params, $this->db->list_fields('fm_cash'));
		$data['member_seq']		= !empty($member_seq)?$member_seq:'';
		$data['regist_date']	= date("Y-m-d H:i:s");
		if($params['gb']=='plus'){
			$data['remain'] = $data['cash'];
		}

		$tmp = array(
				'HTTP_HOST'=>$_SERVER['HTTP_HOST'],
				'REMOTE_ADDR'=>$_SERVER['REMOTE_ADDR'],
				'REQUEST_URI'=>$_SERVER['REQUEST_URI'],
				'HTTP_REFERER'=>$_SERVER['HTTP_REFERER'],
				'GET'=>$_GET,
				'POST'=>$_POST
		);

		$data['sys_memo'] = serialize($tmp);

		$result = $this->db->insert('fm_cash', $data);
		$data['cash_seq'] = $this->db->insert_id();

		$sql = "select cash from fm_member where member_seq = '{$member_seq}'";
		$query = $this->db->query($sql);
		$info = $query->result_array();

		$cash = ($params['gb']=='plus') ? $info[0]['cash']+$params['cash'] : $info[0]['cash']-$params['cash'];
		if($cash<0) $cash = 0;

		###
		/* 유효기간이 없으므로 사용 안함
 		if($params['gb']=='minus'){
			$this->minus_pocess('cash', $data);
		}
		*/
		$this->db->query('update fm_member set cash=? where member_seq=?',array($cash,$member_seq));
	}


	public function minus_pocess($type='emoney', $params){


		### DEFAULT
		$today		= date("Y-m-d");
		$money		= $params[$type];
		$table	= "fm_".$type;
		$seq	= $type."_seq";

		### INSERT DATA
		$used['used']		= $type;
		$used['parent_seq'] = $params[$seq];
		$used['type']		= $params['type'];
		$used['ordno']		= $params['ordno'];
		$used['memo']		= $params['memo'];
		$used['regist_date'] = date("Y-m-d H:i:s");

		### LIMITED EMONEY
		$sql = "select * from {$table} where member_seq = '{$params['member_seq']}' and gb = 'plus' and limit_date >= '{$today}' and remain > 0 order by limit_date asc";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $v){
			if($money > 0){
				if($money <= $v['remain']){
					$v['remain']	= $v['remain'] - $money;
					$used['remain']		= $v['remain'];
					$used['used_amt']	= $money;
					$money			= 0;
				}else{
					$money			= $money - $v['remain'];
					$used['remain']		= 0;
					$used['used_amt']	= $v['remain'];
				}
				$used['used_seq']	= $v[$seq];
				$result = $this->db->insert("fm_used_log", $used);

				$this->db->query("update {$table} set remain = '{$used['remain']}' where ".$seq." = '".$v[$seq]."' ");
			}
		}

		if($money){
			$sql = "select * from {$table} where member_seq = '{$params['member_seq']}' and gb = 'plus' and (limit_date = '' OR limit_date is null) and remain > 0 order by {$seq} asc";
			$query = $this->db->query($sql);
			foreach($query->result_array() as $v){
				if($money > 0){
					if($money <= $v['remain']){
						$v['remain']		= $v['remain'] - $money;
						$used['remain']		= $v['remain'];
						$used['used_amt']	= $money;
						$money				= 0;
					}else{
						$money				= $money - $v['remain'];
						$used['remain']		= 0;
						$used['used_amt']	= $v['remain'];
					}
					$used['used_seq']	= $v[$seq];
					$result = $this->db->insert("fm_used_log", $used);

					$this->db->query("update {$table} set remain = '{$used['remain']}' where ".$seq." = '".$v[$seq]."' ");
				}
			}
		}
	}

	public function point_insert($params, $member_seq){

		$reserve = config_load('reserve');
		if($reserve['point_use']=='N' && $params['gb']=='plus'){
			return;
		}

		$data = filter_keys($params, $this->db->list_fields('fm_point'));
		$data['member_seq']		= !empty($member_seq)?$member_seq:'';
		$data['regist_date']	= date("Y-m-d H:i:s");
		if($params['gb']=='plus'){
			$data['remain'] = $data['point'];
		}
		$result = $this->db->insert('fm_point', $data);
		$data['point_seq'] = $this->db->insert_id();

		$sql = "select point from fm_member where member_seq = '{$member_seq}'";
		$query = $this->db->query($sql);
		$info = $query->result_array();

		$emoney = ($params['gb']=='plus') ? $info[0]['point']+$params['point'] : $info[0]['point']-$params['point'];
		if($emoney<0) $emoney = 0;

		###
		if($params['gb']=='minus'){
			$this->minus_pocess('point', $data);
		}
		$this->db->query('update fm_member set point=? where member_seq=?',array($emoney,$member_seq));
	}



	public function emoney_list($sc) {


		$key = get_shop_key();

		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate'])){
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sqlWhereClause.=" and regist_date BETWEEN '{$start_date}' and '{$end_date}' ";

			$subWhere[] = "limit_date BETWEEN '{$start_date}' and '{$end_date}'";
		}
		## 유효기간 2014-07-23 (개인맞춤형알림에서 사용)
		if( !empty($sc['limit_sdate']) && !empty($sc['limit_edate'])){
			$start_date = $sc['limit_sdate'].' 00:00:00';
			$end_date	= $sc['limit_edate'].' 23:59:59';
			$sqlWhereClause.=" and limit_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		if( !empty($sc['gb']) ) {
			$sqlWhereClause .= " AND gb in ('".implode("','",$sc['gb'])."') ";
		}

		$sqlWhereClause .= " AND type != 'limitDate_minus'";
		$subWhere[] = "`type` != 'limitDate_minus' AND member_seq = '{$sc[member_seq]}'";

		$sql = "select
				emoney_seq,
				member_seq,
				type,
				ordno,
				gb,
				emoney,
				memo,
				regist_date,
				goods_review,
				manager_seq,
				limit_date,
				remain,
				goods_review_parent,
				sys_memo,
				emoney_use,
				CASE WHEN `type` = 'order' THEN concat('주문번호 ',ordno)
				 WHEN `type` = 'cancel' THEN concat('복원 ',ordno)
				 WHEN `type` = 'refund' THEN concat('환불 ',ordno)
				 WHEN `type` = 'join' THEN '회원가입'
				 WHEN `type` = 'bookmark' THEN '즐겨찾기'
				 WHEN `type` = 'joincheck' THEN '출석체크'
				 WHEN `type` = 'goods_review' THEN '상품후기'
				 WHEN `type` like 'goods_review_%' THEN '상품후기'
				 WHEN `type` like 'recommend_%' THEN '추천하기'
				 WHEN `type` like 'invite_%' THEN '초대하기'
				END AS contents
			from
				fm_emoney 
			where 1 ".$sqlWhereClause;		
		$sql .= " AND member_seq = '{$sc[member_seq]}' ";		
		
		if( in_array('minus',$sc['gb']) || empty($sc['gb']) ) {
			$today = date('Y-m-d');
		$sql2 = "
		select 
			'' as emoney_seq,
			member_seq,
			'limitDate_minus' as `type`,
			'' as ordno,
			'minus' as gb,
			remain as emoney,
			'기간만료로 적립금 차감' as memo,
			concat(limit_date,' 00:00:00')  as regist_date,
			'' as goods_review,
			'' as manager_seq,
			'' as limit_date,
			0 as remain,
			'' as goods_review_parent,
			'' as sys_memo,
			'none' as emoney_use,
			'기간만료' AS `contents`
			from 
			(
				select 					
					member_seq,
					limit_date,
					sum(remain) remain					
				from fm_emoney where remain>0 and limit_date<'$today' and limit_date AND ".implode(' AND ',$subWhere)."
				group by limit_date
			) B";
			$sql = $sql." UNION ".$sql2;
		}

		$query = "select * from (".$sql.") A";
		$query .= " order by A.regist_date desc";
		$limit = " limit {$sc['page']}, {$sc['perpage']} ";	
		
		$res = $this->db->query($query.$limit);
		$data['result'] = $res->result_array();

		$res = $this->db->query($query);
		$data['count'] = $res->num_rows();

		return $data;
	}



	public function point_list($sc) {


		$key = get_shop_key();

		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate'])){
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sqlWhereClause.=" and A.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		if( !empty($sc['gb']) ) {
			$sqlWhereClause .= " AND A.gb in ('".implode("','",$sc['gb'])."') ";
		}

		$sql = "select
				A.*,
		CASE WHEN type = 'order' THEN  concat('주문번호 ',ordno)
				 WHEN type = 'cancel' THEN concat('복원 ',ordno)
				 WHEN type = 'refund' THEN concat('환불 ',ordno)
				 WHEN type = 'join' THEN '회원가입'
				 WHEN type = 'goods_review' THEN '상품후기'
				 WHEN type like 'goods_review_%' THEN '상품후기'
				 WHEN type like 'recommend_%' THEN '추천하기'
				 WHEN type like 'invite_%' THEN '초대하기'
				 WHEN type = 'bookmark' THEN '즐겨찾기'
				 WHEN type = 'promotioncode' THEN  concat('프로모션코드 ', promotioncode)
				END AS contents
			from
				fm_point A
			where 1 ".$sqlWhereClause;

		###
		$sql .= " AND A.member_seq = '{$sc[member_seq]}' ";
		$sql .=" order by A.point_seq desc";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$query = $this->db->query($sql);
		$data['count'] = $query->num_rows();

		return $data;
	}


	public function cash_list($sc) {


		$key = get_shop_key();

		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate'])){
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sqlWhereClause.=" and A.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		if( !empty($sc['gb']) ) {
			$sqlWhereClause .= " AND A.gb in ('".implode("','",$sc['gb'])."') ";
		}

		$sql = "select
				A.*,
		CASE WHEN type = 'order' THEN concat('주문번호 ',ordno)
				 WHEN type = 'cancel' THEN concat('복원 ',ordno)
				 WHEN type = 'refund' THEN concat('환불 ',ordno)
				 WHEN type = 'join' THEN '회원가입'
				 WHEN type = 'bookmark' THEN '즐겨찾기'
				END AS contents
			from
				fm_cash A
			where 1 ".$sqlWhereClause;

		###
		$sql .= " AND A.member_seq = '{$sc[member_seq]}' ";
		$sql .=" order by A.cash_seq desc";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$query = $this->db->query($sql);
		$data['count'] = $query->num_rows();

		return $data;
	}

	//초대내역
	public function recommend_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS * from fm_member where 1";

		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate'])){
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		if ( $sc['recommend'] ) $sql .= " and recommend = '".$sc['recommend']."' ";
		//if ( $sc['member_seq'] ) $sql .= " and member_seq = '".$sc['member_seq']."' ";

		$sql .=" order by member_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		return $data;
	}

	//추천하기 총건수
	public function recommend_total_count($sc)
	{
		$sql = "select member_seq from fm_member where 1  and recommend = '".$sc['recommend']."' ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}




	// 총건수 탈퇴회원제외
	public function get_item_total_count($sc = null)
	{
		$sql = 'select  SQL_CALC_FOUND_ROWS member_seq from fm_member  where status != "withdrawal" ';
		if($sc){
			### groups array()
			if( !empty($sc['groupsar']) ){
				$groups = implode("','",$sc['groupsar']);
				$sql .= " and group_seq in ('".$groups."')";
			}
		}
 		$this->db->query($sql);
		return mysql_affected_rows();
	}

	public function get_group_for_goods($price=0,$goods_seq,$r_category_code,$group_seq='')
	{
		if(!$price) $price = 0;
		// 이 상품의 최고 할인율을 가진 회원등급
		$where_category = "'".implode("','",$r_category_code)."'";
		$sale_query = "
		select
			if(sale_price_type='PER',floor({$price}*sale_price/100),sale_price) sale, if(sale_price_type='PER',sale_price,0) sale_rate,group_seq
		from fm_member_group
		where
			( select count(*) from fm_member_group_issuecategory where group_seq = fm_member_group.group_seq and type='emoney' and type='sale' and category_code in($where_category) )=0
			and ( select count(*) from fm_member_group_issuegoods where group_seq = fm_member_group.group_seq and type='emoney' and type='sale' and goods_seq = '$goods_seq' )=0";

		$reserve_query = "
		select
		if(point_price_type='PER',floor({$price}*point_price/100),point_price) reserve, if(point_price_type='PER',point_price,0) reserve_rate,group_seq
		from fm_member_group
		where
			( select count(*) from fm_member_group_issuecategory where group_seq = fm_member_group.group_seq and type='emoney' and category_code in($where_category) )=0
			and ( select count(*) from fm_member_group_issuegoods where group_seq = fm_member_group.group_seq and type='emoney' and type='emoney' and goods_seq = '$goods_seq' )=0";

		if($group_seq){
			$sale_query .=  " and group_seq='$group_seq'";
			$reserve_query .=  " and group_seq='$group_seq'";
		}

		$query = "
		select sale,sale_rate,reserve,reserve_rate,g.* from fm_member_group g
		left join ($sale_query) s on g.group_seq=s.group_seq
		left join ($reserve_query) r on g.group_seq=r.group_seq
		order by sale desc,reserve desc,group_seq desc limit 1";
		$query = $this->db->query($query);
		$data_member_group = $query->row_array();
		return $data_member_group;
	}
	//가입형식 추가 타입별 속성값 가져오기
	public function get_labelitem_type($data, $msdata){

		switch($data['label_type'])
			{

				case "text" :

					for ($j=0; $j<$data['label_value']; $j++) {
						if ($j > 0) $inputBox .= "<br/>";
						$label_value = ($msdata[$j]) ? $msdata[$j]['label_value'] : '';
						$size = ( $this->mobileMode || $this->storemobileMode )?" ":"size='70' ";
						$inputBox .= '<input type="text" name="label['.$data['joinform_seq'].'][value][]" class="text_'.$data['joinform_seq'].'" value="'.$label_value.'" '.$size.' style="width:100%;border:1px solid #dbdbdb; margin:1px 0; padding:2px;">';
					}
				break;

				case "select" :
					$inputBox .= "<table class='selectLabelSet'><tr><td>";
					$labelArray = explode("|", $data['label_value']);
					$labelCount = count($labelArray)-1;
					$labelindexBox = '';
					$label_value = ($msdata[0]) ? $msdata[0]['label_value'] : '';
					$labelindexBox .= '<option value=""   childs="">선택해주세요.</option>';
					for ($j=0; $j<$labelCount; $j++)
					{
						$labelsubArray = explode(";", $labelArray[$j]);
						$selected = ($labelsubArray[0] == $label_value) ? "selected" : "";
						$labelindexBox .= '<option value="'. $labelsubArray[0] .'" '. $selected .' childs="'.implode(";",array_slice($labelsubArray,1)).'">'. $labelsubArray[0] .'</option>';
					}
					if($msdata[0]){
						$labelsubBox = '<input type="hidden" name="subselect['.$data['joinform_seq'].'] id="subselect_'.$data['joinform_seq'].'" value="'.$msdata[0]['label_sub_value'].'" joinform_seq="'.$data['joinform_seq'].'" class="hiddenLabelDepth">';
					}

					$inputBox .= '<select name="label['.$data['joinform_seq'].'][value][]" id="label_'.$data['joinform_seq'].'" joinform_seq="'.$data['joinform_seq'].'" style="height:18px; line-height:16px;" class="selectLabelDepth1">';
					$inputBox .= $labelindexBox;
					$inputBox .= '</select>';
					$inputBox .= '</td><td><div class="selectsubDepth hide">';
					$inputBox .= '<select name="labelsub['.$data['joinform_seq'].'][value][]" id="labelsub_'.$data['joinform_seq'].'" joinform_seq="'.$data['joinform_seq'].'" style="height:18px; line-height:16px;" class="selectLabelDepth2">';
					$inputBox .= '</select></div></td></tr></table>';
					$inputBox .= $labelsubBox;

				break;

				case "textarea" :

						switch($data['label_value'])
						{
							case "large" :		$height = "300px";	break;
							case "medium" :		$height = "200px";	break;
							case "small" :		$height = "100px";	break;
						}
						$label_value = ($msdata[0]) ? $msdata[0]['label_value'] : '';
						$inputBox .= '<textarea name="label['.$data['joinform_seq'].'][value][]" id="label_'.$data['joinform_seq'].'" style="width:90%; height:'. $height .';">'.$label_value.'</textarea>';

				break;

				case "checkbox" :
					$labelArray = explode("|", $data['label_value']);
					$labelCount = count($labelArray)-1;

					if($msdata[0])$cmsdata=count($msdata);
					for ($k=0; $k<$cmsdata; $k++) {
						$ckdata[] = $msdata[$k]['label_value'];
					}

					for ($j=0; $j<$labelCount; $j++) {
						if (is_array($msdata)) {
							$checked = (in_array($labelArray[$j], $ckdata )) ? "checked" : "";
						}
						if ($j > 0) $inputBox .= " ";
						$inputBox .= '<input type="checkbox" name="label['.$data['joinform_seq'].'][value][]" class="null labelCheckbox_'.$data['joinform_seq'].'" value="'. $labelArray[$j] .'" '. $checked .'>'. $labelArray[$j];
					}
				break;

				case "radio" :
					$labelArray = explode("|", $data['label_value']);
					$labelCount = count($labelArray)-1;

					for ($j=0; $j<$labelCount; $j++) {

						if (is_array($msdata[0])) {
							$checked = ($labelArray[$j] == $msdata[0]['label_value']) ? "checked" : "";
						}
						if ($j > 0) $inputBox .= " ";

						$inputBox .= '<input type="radio" name="label['.$data['joinform_seq'].'][value][]" class="null" value="'. $labelArray[$j] .'" '. $checked .'>'. $labelArray[$j];
					}
				break;
			}

		return $inputBox;
	}

	//회원 추가가입정보가져오기
	public function get_subinfo($mseq,$form_seq){

		$query = $this->db->get_where('fm_member_subinfo', array('member_seq'=>$mseq, 'joinform_seq'=>$form_seq));
		$result = $query -> result_array();
		return $result;
	}

	###
	public function get_order_count($member_seq){
		$sql = "SELECT count(order_seq) as cnt, sum(settleprice) as sums FROM fm_order WHERE step>=25 and step<=75 and member_seq={$member_seq}";
		$query = $this->db->query($sql);
		$order = $query->result_array();

		$data['cnt'] =  $order[0]['cnt'];
		$data['sum'] =  $order[0]['sums'];
		return $data;
	}

	public function get_emoney($member_seq,$type='emoney')
	{
		$today = date('Y-m-d');
		$table = 'fm_'.$type;
		$query = "select sum(ifnull(remain,0)) emoney from {$table} where member_seq=? and gb='plus' and (limit_date >= ? OR limit_date is null OR limit_date='')";
		$query = $this->db->query($query,array($member_seq,$today));
		$row = $query->row_array();
		return $row['emoney'];
	}

	public function get_replacetext($m=null)
	{
		$arr_replace1 = parse_ini_file(APPPATH."config/_replace_text.ini", true);
		if($m == "curation"){
			$arr_replace2	= parse_ini_file(APPPATH."config/_replace_curation_text.ini", true);
			$arr_replace	= array_merge($arr_replace1,$arr_replace2);
		}else{
			$arr_replace	= $arr_replace1;
		}

		return $arr_replace;
	}

	public function get_curation_replacetext()
	{
		$arr_replace = parse_ini_file(APPPATH."config/_replace_curation_coupon.ini", true);

		return $arr_replace;
	}




	/* 회원 그룹 추가 포인트 */
	// $category array 카테고리 코드
	public function get_group_addpoint($member_seq,$goods_price,$order_price,$goods_seq='',$category='', $sale_seq){
		$reserve = 0;
		$query = "
		select group_seq,point_use,point_limit_price,reserve_price as point_price,reserve_price_type as point_price_type from fm_member_group_sale_detail where sale_seq = '".$sale_seq."' and group_seq in
		(
			select group_seq from fm_member where member_seq = ?
		)";

		$query = $this->db->query($query, array($member_seq) );
		list($data) = $query->result_array($query);

		if( $category ){
			$category_in = implode("','",$category);
			$query = "select count(*) cnt from fm_member_group_issuecategory where type='emoney' and sale_seq = '".$sale_seq."' and group_seq=? and category_code in('".$category_in."')";
			$query = $this->db->query($query,$data['group_seq']);
			$cnt = $query->row_array();
			if( $cnt['cnt'] > 0 ){
				return 0;
			}
		}

		if( $goods_seq ){
			$query = "select count(*) cnt from fm_member_group_issuegoods where type='emoney' and sale_seq = '".$sale_seq."' and group_seq=? and goods_seq = ?";
			$query = $this->db->query($query,array($data['group_seq'],$goods_seq));
			$cnt = $query->row_array();
			if( $cnt['cnt'] > 0 ){
				return 0;
			}
		}

		if( $data['point_price_type'] == 'PER' && $data['point_price'] && $goods_price ){
			$point = ($goods_price * $data['point_price'])/100;

		}

		if($data['point_use'] == 'Y' && $order_price && $order_price < $data['point_limit_price']){
			return 0;
		}

		return $point;

	}

	public function get_member_sale($where="", $field="*"){

		if($where){
			$where = " WHERE ".$where;
		}
		$sql = "SELECT ".$field." FROM fm_member_group_sale ".$where ." order by sale_seq desc";

		$query = $this->db->query($sql);
		$row = $query->result_array();
		return $row;
	}

	public function member_sale_group_list(){
		//$this->db->order_by("group_seq","asc");
		$this->db->order_by("use_type","asc");
		$this->db->order_by("order_sum_price","asc");
		$this->db->order_by("order_sum_ea","asc");
		$this->db->order_by("order_sum_cnt","asc");

		$query = $this->db->get("fm_member_group");

		$returnArr[] = array('group_seq'=>"0", "group_name"=>"비회원");
		foreach ($query->result_array() as $row){
			$qry = "select count(member_seq) as count from fm_member where group_seq = '{$row['group_seq']}' and status != 'withdrawal'";
			$querys = $this->db->query($qry);
			$data = $querys->result_array();
			$row['count'] = $data[0]['count'];
			$returnArr[] = $row;
		}
		return $returnArr;
	}

	public function member_group_max(){
		$query = "select max(group_seq) max from fm_member_group";
		$query = $this->db->query($query);
		$data = $query->result_array();
		return (int) $data[0]['max'];
	}

	/* 주문건수/주문금액 일괄 업데이트 리스트 */
	public function member_cnt_batch_list($sc) {
		if(!isset($_GET['page']))$_GET['page'] = 1;
		$sql = "select member_seq from fm_member where rute != 'withdrawal' ";
		$sql .=" order by member_seq desc ";
		$result = select_page($sc['limitnum'],$_GET['page'],10,$sql,'');
		$result['page']['querystring'] = get_args_list();
		return $result;
	}



	/* 주문건수/주문금액/추천받은건수/초대한건수 일괄 업데이트 업데이트 */
	public function member_cnt_batch($member_seq) {
		if(!$member_seq) return;

		$query = "select
		( select sum( CONVERT(step75_count * 1, SIGNED)) from fm_member_order where member_seq=A.member_seq ) member_order_cnt,
		( select sum( CONVERT(step75_ea * 1, SIGNED) - CONVERT(refund_ea * 1, SIGNED) ) from fm_member_order where member_seq=A.member_seq ) member_order_goods_cnt,
		( select sum( CONVERT(step75_price * 1, SIGNED) - CONVERT(refund_price * 1, SIGNED) ) from fm_member_order where member_seq=A.member_seq ) member_order_price,
		( select count(member_seq) from fm_member where recommend=A.userid ) member_recommend_cnt,
		( select count(member_seq) from fm_memberinvite where member_seq=A.member_seq ) member_invite_cnt
		from fm_member A
		where A.member_seq =?";
		$query = $this->db->query($query,array($member_seq));
		$member_cnt = $query->row_array();

		$member_cnt['member_order_cnt']				= ($member_cnt['member_order_cnt']>0)?$member_cnt['member_order_cnt']:0;
		$member_cnt['member_order_goods_cnt']	= ($member_cnt['member_order_goods_cnt']>0)?$member_cnt['member_order_goods_cnt']:0;

		$member_cnt['member_order_price']			= ($member_cnt['member_order_price']>0)?$member_cnt['member_order_price']:0;
		$member_cnt['member_recommend_cnt']	= ($member_cnt['member_recommend_cnt']>0)?$member_cnt['member_recommend_cnt']:0;
		$member_cnt['member_invite_cnt']				= ($member_cnt['member_invite_cnt']>0)?$member_cnt['member_invite_cnt']:0;

		$this->db->where('member_seq', $member_seq);
		$result = $this->db->update('fm_member', array('member_order_cnt'=>$member_cnt['member_order_cnt'],'member_order_goods_cnt'=>$member_cnt['member_order_goods_cnt'],'member_order_price'=>$member_cnt['member_order_price'],'member_recommend_cnt'=>$member_cnt['member_recommend_cnt'],'member_invite_cnt'=>$member_cnt['member_invite_cnt']));

		return $result;
	}

	/* 주문건수/주문금액 일괄 업데이트 업데이트 @2013-06-19 */
	public function member_order_batch($member_seq) {
		if(!$member_seq) return;

		$mbupquery = "select
		( select sum( CONVERT(step75_count * 1, SIGNED)) from fm_member_order where member_seq=A.member_seq ) member_order_cnt,
		( select sum( CONVERT(step75_ea * 1, SIGNED) - CONVERT(refund_ea * 1, SIGNED) ) from fm_member_order where member_seq=A.member_seq ) member_order_goods_cnt,
		( select sum( CONVERT(step75_price * 1, SIGNED) - CONVERT(refund_price * 1, SIGNED) ) from fm_member_order where member_seq=A.member_seq ) member_order_price
		from fm_member A
		where A.member_seq =?";
		$mbup = $this->db->query($mbupquery,array($member_seq));
		$member_cnt = $mbup->row_array();

		$member_cnt['member_order_cnt']				= ($member_cnt['member_order_cnt']>0)?$member_cnt['member_order_cnt']:0;
		$member_cnt['member_order_goods_cnt']	= ($member_cnt['member_order_goods_cnt']>0)?$member_cnt['member_order_goods_cnt']:0;

		$member_cnt['member_order_price']			= ($member_cnt['member_order_price']>0)?$member_cnt['member_order_price']:0;

		$this->db->where('member_seq', $member_seq);
		$result = $this->db->update('fm_member', array('member_order_cnt'=>$member_cnt['member_order_cnt'],'member_order_goods_cnt'=>$member_cnt['member_order_goods_cnt'],'member_order_price'=>$member_cnt['member_order_price']));

		return $result;
	}


	/*  추천받은건수 업데이트 업데이트 */
	public function member_recommend_cnt($member_seq) {
		if(!$member_seq) return;
		$query = "select
			( select count(member_seq) from fm_member where recommend=A.userid ) member_recommend_cnt
			from fm_member A
			where A.member_seq =?";
			$query = $this->db->query($query,array($member_seq));
			$member_cnt = $query->row_array();
			$member_cnt['member_recommend_cnt']	= ($member_cnt['member_recommend_cnt']>0)?$member_cnt['member_recommend_cnt']:0;
			$this->db->where('member_seq', $member_seq);
			$result = $this->db->update('fm_member', array('member_recommend_cnt'=>$member_cnt['member_recommend_cnt']));

		return $result;
	}


	/* 초대한건수 업데이트 업데이트 */
	public function member_invite_cnt($member_seq) {
		if(!$member_seq) return;
		$query = "select
		( select count(member_seq) from fm_memberinvite where member_seq=A.member_seq ) member_invite_cnt
		from fm_member A
		where A.member_seq =?";
		$query = $this->db->query($query,array($member_seq));
		$member_cnt = $query->row_array();
		$member_cnt['member_invite_cnt']				= ($member_cnt['member_invite_cnt']>0)?$member_cnt['member_invite_cnt']:0;
		$this->db->where('member_seq', $member_seq);
		$result = $this->db->update('fm_member', array('member_invite_cnt'=>$member_cnt['member_invite_cnt']));

		return $result;
	}

	public function member_order($member_seq)
	{		
		$query = "select sum(settleprice) step75_price,count(order_seq) step75_count,sum(opt_ea) opt_ea,
					sum(sub_ea) sub_ea,mon,
					sum(refund_price) refund_price,sum(refund_count) refund_count,sum(refund_ea) refund_ea
				from (
					select
					order_seq,
					settleprice,
					(select sum(step75) from fm_order_item_option so left join fm_order_item si on so.item_seq=si.item_seq where so.order_seq = o.order_seq and si.goods_type!='gift') opt_ea,
					ifnull((select sum(step75) from fm_order_item_suboption where order_seq = o.order_seq),0) sub_ea,
					substring((select shipping_date from fm_goods_export where order_seq=o.order_seq and shipping_date!='0000-00-00' and shipping_date is not null order by export_seq asc limit 1),1,7) mon,
					ifnull((select sum(refund_price+refund_emoney) from fm_order_refund where order_seq = o.order_seq),0) refund_price,
					ifnull((select count(*) from fm_order_refund where order_seq = o.order_seq),0) refund_count,
					ifnull((select sum(ea) from fm_order_refund_item a,fm_order_refund b where a.refund_code=b.refund_code and b.order_seq = o.order_seq),0) refund_ea
					from fm_order o
					where step='75' and member_seq=? and (select shipping_date from fm_goods_export where order_seq=o.order_seq and shipping_date!='0000-00-00' and shipping_date is not null order by export_seq asc limit 1)>=?
				) t
				group by t.mon";
		$start = date('Y-m', strtotime('-1 month'))."-01 00:00:00";
		$query = $this->db->query($query,array($member_seq,$start));				
		foreach($query->result_array() as $row){
			
			$row['mon'] = str_replace("-","",$row['mon']);
			$param = array();
			$query = "delete from fm_member_order where member_seq=? and month=?";
			$query = $this->db->query($query,array($member_seq,$row['mon']));
			$query = "insert into fm_member_order set step75_count=?,step75_price=?,step75_ea=?,
			refund_count=?,refund_price=?,refund_ea=?,
			member_seq=?,month=?";
			$param[] = $row['step75_count'];
			$param[] = $row['step75_price'];
			$param[] = $row['opt_ea']+$row['sub_ea'];
			$param[] = $row['refund_count'];
			$param[] = $row['refund_price'];
			$param[] = $row['refund_ea'];
			$param[] = $member_seq;
			$param[] = $row['mon'];			
			$query = $this->db->query($query,$param);
		}
	}

	//2개월이전 주문건을 오늘일자로 배송완료처리시 주문건/주문금액이 업데이트 수동처리
	public function member_order_old_gabia($member_seq,$yearmonth)
	{
		if( strlen($yearmonth) != 7) return;
		$query = "select sum(settleprice) step75_price,count(order_seq) step75_count,sum(opt_ea) opt_ea,
					sum(sub_ea) sub_ea,mon,
					sum(refund_price) refund_price,sum(refund_count) refund_count,sum(refund_ea) refund_ea
				from (
					select
					order_seq,
					settleprice,
					(select sum(step75) from fm_order_item_option where order_seq = o.order_seq) opt_ea,
					ifnull((select sum(step75) from fm_order_item_suboption where order_seq = o.order_seq),0) sub_ea,
					substring((select shipping_date from fm_goods_export where order_seq=o.order_seq and shipping_date!='0000-00-00' and shipping_date is not null order by export_seq asc limit 1),1,7) mon,
					ifnull((select sum(refund_price+refund_emoney) from fm_order_refund where order_seq = o.order_seq),0) refund_price,
					ifnull((select count(*) from fm_order_refund where order_seq = o.order_seq),0) refund_count,
					ifnull((select sum(ea) from fm_order_refund_item a,fm_order_refund b where a.refund_code=b.refund_code and b.order_seq = o.order_seq),0) refund_ea
					from fm_order o
					where step='75' and member_seq=? and (select shipping_date from fm_goods_export where order_seq=o.order_seq and shipping_date!='0000-00-00' and shipping_date is not null order by export_seq asc limit 1)>=?
				) t
				group by t.mon";

		$start = date('Y-m', strtotime('-1 month '.  $yearmonth))."-01 00:00:00";
		$query = $this->db->query($query,array($member_seq,$start,$end));
		//debug_var($this->db->last_query());
		foreach($query->result_array() as $row){
			$row['mon'] = str_replace("-","",$row['mon']);
			$param = array();
			$query = "delete from fm_member_order where member_seq=? and month=?";
			$query = $this->db->query($query,array($member_seq,$row['mon']));
			$query = "insert into fm_member_order set step75_count=?,step75_price=?,step75_ea=?,
			refund_count=?,refund_price=?,refund_ea=?,
			member_seq=?,month=?";
			$param[] = $row['step75_count'];
			$param[] = $row['step75_price'];
			$param[] = $row['opt_ea']+$row['sub_ea'];
			$param[] = $row['refund_count'];
			$param[] = $row['refund_price'];
			$param[] = $row['refund_ea'];
			$param[] = $member_seq;
			$param[] = $row['mon'];
			$query = $this->db->query($query,$param);
			//debug_var($this->db->last_query());
		}
	}

	// 주문생성 시 회원 최초저장
	public function first_member_insert($member_phone,$member_cellphone,$member_zipcode,$member_address,$member_address_detail)
	{

		if(!$member_phone){
			$update_param['phone'] = implode('-',$_POST['recipient_phone']);
			$r_enc[] = get_encrypt_qry('phone');
		}
		if(!$member_cellphone){
			$update_param['cellphone'] =  implode('-',$_POST['recipient_cellphone']);
			$r_enc[] = get_encrypt_qry('cellphone');
		}
		if(!$member_zipcode){
			$update_param['zipcode'] =  implode('-',$_POST['recipient_zipcode']);
		}
		if(!$member_address){
			$update_param['address'] =  $_POST['recipient_address'];
		}
		if(!$member_address_detail){
			$update_param['address_detail'] =  $_POST['recipient_address_detail'];
		}
		if($update_param){
			$this->db->update('fm_member', $update_param, "member_seq = ".$this->userInfo['member_seq']);

			if($r_enc){
				$sql = "update fm_member set ".implode(',',$r_enc)." where member_seq = ?";
				$this->db->query($sql,$this->userInfo['member_seq']);
			}
		}

	}

	// 확인코드 유효성 체크 (@return bloom)
	public function check_certify_code($code){
		// 6자리 이상, 16자리 이하, 영문, 숫자로만 가능
		if	((strlen($code) < 6 || strlen($code) > 16) || (preg_match('/[^0-9a-zA-Z]/', $code))) {
			return false;
		}else{
			return true;
		}
	}

	// 확인자 추출
	public function get_certify_manager($param){
		if	($param['certify_code'])
			$addWhere	 .= " and certify_code = '".$param['certify_code']."' ";
		if	($param['out_seq'])
			$addWhere	 .= " and seq != '".$param['out_seq']."' ";
		if	($param['provider_seq'])
			$addWhere	 .= " and provider_seq = '".$param['provider_seq']."' ";
		if	($param['manager_id'])
			$addWhere	 .= " and manager_id = '".$param['manager_id']."' ";

		$sql	= "select * from fm_certify_user where seq > 0 ".$addWhere;
		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	// 확인자 정보 추가
	public function insert_certify($param){
		//unset($param['provider_seq']);
		$param['regist_date']		= date('Y-m-d H:i:s');
		$result = $this->db->insert('fm_certify_user', $param);
		return $result;
	}

	// 확인자 정보 삭제
	public function delete_certify($param){
		if	($param['certify_code'])
			$addWhere	 .= " and certify_code = '".$param['certify_code']."' ";
		if	($param['out_seq'])
			$addWhere	 .= " and seq != '".$param['out_seq']."' ";
		if	($param['provider_seq'])
			$addWhere	 .= " and provider_seq = '".$param['provider_seq']."' ";
		if	($param['manager_id'])
			$addWhere	 .= " and manager_id = '".$param['manager_id']."' ";

		if	($addWhere){
			$sql	= "delete from fm_certify_user where seq > 0 ".$addWhere;
			$this->db->query($sql);
		}
	}

	// 자동 등급조정일 계산
	public function calculate_date($start_month,$chg_day,$chg_term,$chk_term,$keep_term){

		$this_year = date('Y');
		$now_st_time = time();
		if( $chg_term == 1 ){
			for($i=1;$i<=12;$i+=$chg_term) $tmp_arr_period[] = $i;
		}else if( $chg_term < 6 ){
			$to_month = $start_month+12;
			for($i=$start_month;$i<$to_month;$i+=$chg_term) $tmp_arr_period[] = $i%12;
			sort($tmp_arr_period);
		}else if( $chg_term >= 6 ){
			$to_month = $start_month + ($chg_term * 4);
			for($i=$start_month;$i<$to_month;$i+=$chg_term){
				$tmp_arr_period[] = $i%12;
				$tmp_arr_year_period[] = (int) ($i/12);
			}
		}

		foreach($tmp_arr_period as $k=>$i){
			$cal_year = $this_year;
			if( $chg_term >= 6 ) $cal_year = $this_year + $tmp_arr_year_period[$k];

			$change_ts = mktime(0,0,0,$i,$chg_day,$cal_year);
			$change_date = date('Y-m-d',$change_ts);
			$result['chg_text'][] = $change_date;


			$cal_ts = strtotime('-1 month',$change_ts);
			$cal_start_ts = strtotime('-'.($chk_term-1).' month',$cal_ts);
			$cal_start_date = date('Y-m-01',$cal_start_ts);
			$result['chk_text_start'][] = $cal_start_date;
			$result['chk_text_end'][] = date('Y-m-t',$cal_ts);

			$cal_end_ts = strtotime('+'.$keep_term.' month',$change_ts);
			$result['keep_text_start'][] = $change_date;
			$result['keep_text_end'][] = date('Y-m-d',$cal_end_ts-24*3600);

			// 등급 말료일
			$result['keep_end'][] = date('Y-m-d',$cal_end_ts);

			if(!$next_grade_date && $now_st_time < $change_ts ){
				$next_grade_date = $change_date;
			}
		}

		$result['next_grade_date'] = $next_grade_date;
		return $result;
	}

	// 상위 등급 추출
	public function get_member_group_flow($sc){

		if	((empty($sc['order_sum_price']) || empty($sc['use_type']) ) && !empty($sc['group_seq'])){
			$sql					= "select * from fm_member_group "
									. "where group_seq = '".$sc['group_seq']."' ";
			$query					= $this->db->query($sql);
			$currentGroup			= $query->row_array();
			$result['currentGroup']	= $currentGroup;
			$sc["use_type"]			= 'NORMAL';
			if(in_array($currentGroup["use_type"], array('AUTO', 'AUTOPART'))){
				$sc["use_type"]			= $currentGroup["use_type"];
				$sc['order_sum_price']	= $currentGroup['order_sum_price'];
			}
		}

		if	(in_array($sc["use_type"], array('AUTO', 'AUTOPART'))){

			// 구매금액 조건
			if	(!empty($sc['order_sum_price'])){
				$addWhere	.= " and order_sum_price > '".$sc["order_sum_price"]."' ";
			}

			$sql					= "select * from fm_member_group "
									. "where use_type in ('AUTO', 'AUTOPART') "
									. $addWhere
									. "order by order_sum_price, order_sum_ea, order_sum_cnt "
									. "limit 1 ";
			$query					= $this->db->query($sql);
			$result['nextGroup']	= $query->row_array();
		}

		return $result;
	}

	// 소멸 예정 혜택
	public function get_extinction($sc){

		// 회원검색
		if	(!empty($sc['member_seq'])){
			$addWhere		.= " and member_seq = '".$sc["member_seq"]."' ";
		}

		// 소멸 예정 쿠폰 ( 금주내 월(0) ~ 일(6) )
		if	(!$sc['extinction_type'] || in_array('coupon', $sc['extinction_type'])){
			$week				= (!date('w')) ? 6 : date('w') - 1;
			$sDate				= date('Y-m-d');
			$eDate				= date('Y-m-d', strtotime('+'.(6-$week).' day'));

			$sql				= "select count(*) cnt from fm_download "
								. "where issue_enddate >= '".$sDate."' "
								. "and issue_enddate <= '".$eDate."' "
								. $addWhere;
			$query				= $this->db->query($sql);
			$coupon				= $query->row_array();
			$result['coupon']	= $coupon['cnt'];
		}

		// 소멸 예정 적립금 (익월 1일 ~ 익월 말일 )
		if	(!$sc['extinction_type'] || in_array('reserve', $sc['extinction_type'])){
			$sDate				= date('Y-m-d');
			$eDate				= date('Y-m') . '-' . date('t');

			$sql				= "select sum(emoney) emoney from fm_emoney "
								. "where limit_date >= '".$sDate."' "
								. "and limit_date <= '".$eDate."' "
								. $addWhere;
			$query				= $this->db->query($sql);
			$emoney				= $query->row_array();
			$result['reserve']	= $emoney['emoney'];
		}

		// 구매확정 대기 건수
		if	(!$sc['extinction_type'] || in_array('buyconfirm', $sc['extinction_type'])){
			$config	= config_load('order');
			if	($config['buy_confirm_use']){
				$sql					= "select count(*) cnt from fm_order_item_option "
										. "where step >= 55 and step <= 75 "
										. "and order_seq in ( "
										. "	select order_seq from fm_order where order_seq > 0 "
										. $addWhere . " ) ";
				$query					= $this->db->query($sql);
				$buyconfirm				= $query->row_array();
				$result['buyconfirm']	= $buyconfirm['cnt'];
			}
		}

		return $result;
 	}

	// 주문시 주소록 배송지 가져오기
	public function get_delivery_address($member_seq,$type,$idx=0,$limit=1){
		$key = get_shop_key();

		$sql = "select *,
		AES_DECRYPT(UNHEX(recipient_phone), '{$key}') as recipient_phone,
		AES_DECRYPT(UNHEX(recipient_cellphone), '{$key}') as recipient_cellphone
		from fm_delivery_address where member_seq=? ";

		if($type=='often') $sql .= " and often='Y' and `default`='Y' ";
		$sql .= " order by address_seq desc limit ?,?";

		//if($type=='lately') $sql .= " and lately='Y' and recipient_address <> '' group by recipient_address ";

		/* 주소 group by 처리 하면서 주소록 > 최근 배송지 탭에 나오는 순서 대로 5개 표시하기 위해 sql 추가. leewh 2014-10-10 */
		if ($type=='lately') {
			$sql = "SELECT d.*,
			AES_DECRYPT(UNHEX(d.recipient_phone), '{$key}') as recipient_phone,
			AES_DECRYPT(UNHEX(d.recipient_cellphone), '{$key}') as recipient_cellphone
			FROM fm_delivery_address d 
				INNER JOIN (
				SELECT recipient_address, MAX( address_seq ) AS address_seq
				FROM fm_delivery_address
				WHERE member_seq=? AND lately='Y' AND recipient_address <> '' 
				GROUP BY recipient_address
				ORDER BY address_seq desc limit ?,?
			) a ON d.recipient_address = a.recipient_address AND d.address_seq = a.address_seq
			";
		}

		$query = $this->db->query($sql,array($member_seq,$idx,$limit));
		$result = $query->result_array();
		return $result;
	}

	//회원정보엑셀 기타 정보 가져오기
	public function get_member_only_excel($seq){
		$key = get_shop_key();
		$sql = "SELECT
					A.member_seq as member_seq,C.group_name,
					A.user_name as user_name, A.nickname as nickname,
					A.userid, A.sex, A.sns_f, A.recommend, A.zipcode, A.address, A.address_street, A.address_detail,
					B.business_seq, B.baddress_type, B.baddress, B.baddress_street, B.baddress_detail,
					B.bzipcode, B.bceo, B.bno, B.bitem,
					B.bstatus, B.bperson, B.bpart,
					A.birthday as birthday, A.anniversary as anniversary,
					A.emoney as emoney, A.point as point, A.cash as cash,
					AES_DECRYPT(UNHEX(A.email), '{$key}') as email,
					AES_DECRYPT(UNHEX(A.phone), '{$key}') as phone,
					AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone
				FROM
					fm_member A
						LEFT JOIN fm_member_business B ON A.member_seq = B.member_seq
						LEFT JOIN fm_member_group C ON A.group_seq = C.group_seq
				WHERE
					A.member_seq = '{$seq}' limit 0, 1";
		$query = $this->db->query($sql);

		$data = array();
		foreach ($query->result_array() as $row){
			$data[] = $row;
		}

		// 사업자 회원일 경우 사업장주소->주소
		if($data[0]['business_seq']){
			$data[0]['baddress_type'] = $data[0]['baddress_type'];
			$data[0]['address'] = $data[0]['baddress'];
			$data[0]['address_street'] = $data[0]['baddress_street'];
			$data[0]['address_detail'] = $data[0]['baddress_detail'];

			$tmp = explode('-',$data[0]['bzipcode']);
			foreach($tmp as $k => $datas){
				$key = 'zipcode'.($k+1);
				$data[0][$key] = $datas;
			}
		}

		return (isset($data[0]))?$data[0]:'';
	}

	## 회원등급 혜택 defualt_yn = 'y' 2014-07-24
	public function get_member_sale_default(){

		$qry		= "select sale_seq from fm_member_group_sale where defualt_yn = 'y'";
		$query		= $this->db->query($qry);
		$sale_res	= $query -> result_array();
		$sale_seq	= $sale_res[0]['sale_seq'];

		$qry = "select * from fm_member_group_sale_detail where sale_seq = '".$sale_seq."' order by sale_limit_price desc";
		$query = $this->db->query($qry);
		$detail_tmp = $query -> result_array();
		foreach($detail_tmp as $detail){
			$detail_list[$detail['group_seq']] = $detail;
		}

		return $detail_list;
	}


	public function get_member_sale_array($sale_seq){
		
		$list = $this->member_sale_group_list();
		$select_sale_list = $this->get_member_sale();
		

		if($sale_seq){
			//일반가입 정보
			$qry = "select * from fm_member_group_sale where sale_seq = '".$sale_seq."'";
			$query = $this->db->query($qry);
			$sale_list = $query -> result_array();

			foreach ($sale_list as $datarow){

				foreach($list as $group){

					$qry = "select * from fm_member_group_sale_detail where sale_seq = '".$datarow["sale_seq"]."' and group_seq = '".$group["group_seq"]."'";
					$query = $this->db->query($qry);
					$detail_list = $query -> result_array();

					foreach($detail_list as $subdatarow){


						$subdata[$group["group_seq"]]["sale_use"]				= $subdatarow["sale_use"];
						$subdata[$group["group_seq"]]["sale_limit_price"]		= $subdatarow["sale_limit_price"];
						$subdata[$group["group_seq"]]["sale_price"]				= $subdatarow["sale_price"];

						$subdata[$group["group_seq"]]["sale_price_type"]		= $subdatarow["sale_price_type"];
						$subdata[$group["group_seq"]]["sale_option_price"] 		= $subdatarow["sale_option_price"];

						$subdata[$group["group_seq"]]["sale_option_price_type"]	= $subdatarow["sale_option_price_type"];
						$subdata[$group["group_seq"]]["point_use"]				= $subdatarow["point_use"];

						$subdata[$group["group_seq"]]["point_use"]				= $subdatarow["point_use"];
						$subdata[$group["group_seq"]]["point_limit_price"]		= $subdatarow["point_limit_price"];
						$subdata[$group["group_seq"]]["point_price"]			= $subdatarow["point_price"];

						$subdata[$group["group_seq"]]["point_price_type"]		= $subdatarow["point_price_type"];

						$subdata[$group["group_seq"]]["reserve_price"]			= $subdatarow["reserve_price"];

						$subdata[$group["group_seq"]]["reserve_price_type"]		= $subdatarow["reserve_price_type"];
						$subdata[$group["group_seq"]]["reserve_select"]			= $subdatarow["reserve_select"];
						$subdata[$group["group_seq"]]["reserve_year"]			= $subdatarow["reserve_year"];
						$subdata[$group["group_seq"]]["reserve_direct"]			= $subdatarow["reserve_direct"];
						$subdata[$group["group_seq"]]["point_select"]			= $subdatarow["point_select"];
						$subdata[$group["group_seq"]]["point_year"]				= $subdatarow["point_year"];
						$subdata[$group["group_seq"]]["point_direct"]			= $subdatarow["point_direct"];
					}


				}

				$data[$datarow["sale_seq"]] = $subdata;
				$data[$datarow["sale_seq"]]["sale_seq"] = $datarow["sale_seq"];
				$data[$datarow["sale_seq"]]["sale_title"] = $datarow["sale_title"];				
			}			

		}

		$data[$datarow["sale_seq"]]["sale_list"] = $select_sale_list;
		$data[$datarow["sale_seq"]]["loop"] = $list;
		$data[$datarow["sale_seq"]]["gcount"] = count($list);

		return array('sale_list'=>$select_sale_list,'data'=>$data,'loop'=>$list,'gcount'=>count($list));


	}
	

	## SMS 발송제한 설정 타이틀
	public function get_sms_restriction(){

		### 발송시간 제한
		$restriction_title = array("comm"			=> "공통"
									,"goods"		=> "실물<br />상품"
									,"coupon"		=> "쿠폰<br />(티켓상품)"
									,"join"			=> "회원가입 시"
									,"findid"		=> "아이디 찾기"
									,"withdrawal"	=> "회원탈퇴 시"
									,"findpwd"		=> "비밀번호 찾기"
									,"order"		=> "주문접수 시"
									,"settle"		=> "결제확인 시"
									,"released"		=> "출고완료 시"
									,"released2"	=> "출고완료 시 받는분(≠주문자)"
									,"delivery"		=> "배송완료 시"
									,"delivery2"	=> "배송완료 실 받는분(≠주문자)"
									,"cancel"=> "결제취소 → 환불완료 시"
									,"refund"=> "반품 → 환불완료 시"
									,"coupon_released"		=> "출고완료 시 주문자(≠받는분)"
									,"coupon_released2"	=> "출고완료 시"
									,"coupon_delivery"		=> "배송완료 시 주문자(≠받는분)"
									,"coupon_delivery2"	=> "배송완료 시"
									,"coupon_cancel"=> "결제취소 → 환불 완료"
									,"coupon_refund"=> "반품 → 환불완료"
								);
		### 발송시간 제한
		$restriction_item = $rest_comm = array();
		$rest_comm['join']				= array('ac_customer'	=> "가입 시 발송"
											,'ac_admin'		=> ""
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'n'
										);
		$rest_comm['findid']			= array('ac_customer'	=> "찾기 시 발송"
											,'ac_admin'		=> ""
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> ''
											,'tg_seller'	=> ''
											,'use'		=> 'n'
										);
		$rest_comm['withdrawal']		= array('ac_customer'	=> "탈퇴 시 발송"
											,'ac_admin'		=> ""
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> ''
											,'tg_seller'	=> ''
											,'use'		=> 'n'
										);
		$rest_comm['findpwd']			= array('ac_customer'	=> "찾기 시 발송"
											,'ac_admin'		=> ""
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> ''
											,'tg_seller'	=> ''
											,'use'		=> 'n'
										);
		$rest_comm['order']				= array('ac_customer'	=> "접수 시 발송"
											,'ac_admin'		=> "접수 시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'y'
										);
		$rest_comm['settle']			= array('ac_customer'	=> "확인 시 발송"
											,'ac_admin'		=> "확인 시 발송"
											,'ac_system'	=> "자동 확인 시 발송"
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> '○'
											,'use'		=> 'y'
										);
		$rest_goods['released']			= array('ac_customer'	=> ""
											,'ac_admin'		=> "완료시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'y'
										);
		$rest_goods['released2']			= array('ac_customer'	=> ""
											,'ac_admin'		=> "완료시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'y'
										);
		$rest_goods['delivery']			= array('ac_customer'	=> "구매확정 시 발송"
											,'ac_admin'		=> "완료시 발송"
											,'ac_system'	=> "자동 완료시 발송"
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> '○'
											,'use'		=> 'y'
										);
		$rest_goods['delivery2']		= array('ac_customer'	=> "구매확정 시 발송"
											,'ac_admin'		=> "완료시 발송"
											,'ac_system'	=> "자동 완료시 발송"
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> '○'
											,'use'		=> 'y'
										);
		$rest_goods['cancel']			= array('ac_customer'	=> "취소 시 발송"
											,'ac_admin'		=> "완료시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'y'
										);
		$rest_goods['refund']			= array('ac_customer'	=> ""
											,'ac_admin'		=> "완료시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'y'
										);
		$rest_coupon['coupon_released2']			= array('ac_customer'	=> ""
											,'ac_admin'		=> "결제 시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'n'
										);
		$rest_coupon['coupon_released']			= array('ac_customer'	=> ""
											,'ac_admin'		=> "결제 시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> ''
											,'use'		=> 'n'
										);
		$rest_coupon['coupon_delivery2']		= array('ac_customer'	=> ""
											,'ac_admin'		=> "사용확인 시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> '○'
											,'use'		=> 'n'
										);
		$rest_coupon['coupon_delivery']		= array('ac_customer'	=> ""
											,'ac_admin'		=> "사용확인 시 발송"
											,'ac_system'	=> ""
											,'tg_customer'	=> '○'
											,'tg_admin'		=> '○'
											,'tg_seller'	=> '○'
											,'use'		=> 'n'
										);
		$rest_coupon['coupon_cancel']			= array('ac_customer'	=> ""
										,'ac_admin'		=> "완료시 발송"
										,'ac_system'	=> ""
										,'tg_customer'	=> '○'
										,'tg_admin'		=> '○'
										,'tg_seller'	=> ''
										,'use'		=> 'y'
										);
		$rest_coupon['coupon_refund']			= array('ac_customer'	=> ""
										,'ac_admin'		=> "완료시 발송"
										,'ac_system'	=> ""
										,'tg_customer'	=> '○'
										,'tg_admin'		=> '○'
										,'tg_seller'	=> ''
										,'use'		=> 'y'
										);

		foreach($rest_comm as $item){ if($item['use'] == 'y') $commusecnt++; }
		foreach($rest_goods as $item){ if($item['use'] == 'y') $goodsusecnt++; }
		foreach($rest_coupon as $item){ if($item['use'] == 'y') $couponusecnt++; }
		$rest_comm['usecnt']	= $commusecnt;
		$rest_goods['usecnt']	= $goodsusecnt;
		$rest_coupon['usecnt']	= $couponusecnt;

		$restriction_item['comm']	= $rest_comm;
		$restriction_item['goods']	= $rest_goods;
		$restriction_item['coupon']	= $rest_coupon;

		return array($restriction_title,$restriction_item);
	}

	// 등급할인 목록 추출
	public function get_group_sale_list(){
		$sql	= "select * from fm_member_group_sale ";
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}
 
	// 등급할인 상세 목록 추출
	public function get_group_sale_detail($sale_seq = '', $group_seq = ''){
		if	($sale_seq > 0)				$addWhere[]	= " sale_seq = '".$sale_seq."' ";
		if	(is_numeric($group_seq))	$addWhere[]	= " group_seq = '".$group_seq."' ";
		if	(is_array($addWhere) && count($addWhere) > 0){
			$where	= "where ".implode(' and ', $addWhere);
		}
		$sql	= "select * from fm_member_group_sale_detail ".$where;
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}

	// 등급할인 제외 카테고리 목록 추출
	public function get_group_sale_issuecategory($sale_seq = ''){
		$sql	= "select * from fm_member_group_issuecategory ";
		if	($sale_seq > 0)	$sql	.= " where sale_seq = '".$sale_seq."' ";
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}

	// 등급할인 제외 카테고리 목록 추출
	public function get_group_sale_issuegoods($sale_seq = ''){
		$sql	= "select * from fm_member_group_issuegoods ";
		if	($sale_seq > 0)	$sql	.= " where sale_seq = '".$sale_seq."' ";
		$query	= $this->db->query($sql);
		$result	= $query->result_array();

		return $result;
	}
}

/* End of file membermodel.php */
/* Location: ./app/models/membermodel */