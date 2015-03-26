<?php
class statsmodel extends CI_Model {

	public function get_referer_url($referer){
		$result['referer']			= '';
		$result['referer_domain']	= '';
		if	($referer && preg_match('/^http[s]*\:\/\//', $referer)){
			$tmp	= parse_url($referer);
			if	($tmp['host']){
				$domain						= $tmp['host'];
				$domain						= preg_replace('/^(www\.|m\.)/', '', $domain);
				$result['referer_domain']	= $domain;
			}
			$result['referer']				= $referer;
		}

		return $result;
	}

	public function insert_member_stats($member_seq,$birthday,$address,$sex)
	{
		$platform	= 'P';
		if		($this->fammerceMode || $this->storefammerceMode)	$platform	= 'F';
		elseif	($this->mobileMode || $this->storemobileMode)		$platform	= 'M';
		if($address) $r_address = explode(" ",$address);
		$refererArr	= $this->get_referer_url($this->session->userdata('shopReferer'));
		$insert_member_stats_params = array(
				'member_seq' 		=> $member_seq,
				'member_age' 		=> $birthday && $birthday!='0000-00-00' ? date('Y') - substr($birthday,0,4) + 1 : 0,
				'member_area'		=> $r_address[0],
				'member_sex' 		=> $sex && $sex!='none' ? $sex : 'none',
				'referer_domain'	=> $refererArr['referer_domain'],
				'referer'			=> $refererArr['referer'],
				'ip'				=> $_SERVER['REMOTE_ADDR'],
				'platform'			=> $platform,
				'regist_date'		=> date('Y-m-d H:i:s')
		);
		$this->db->insert('fm_member_stats', $insert_member_stats_params);
	}

	public function insert_order_stats($order_seq)
	{
		$refererArr	= $this->get_referer_url($this->session->userdata('shopReferer'));
		$insert_order_stats_params = array(
				'order_seq' 		=> $order_seq,
				'buyer_age' 		=> $this->userInfo['birthday'] && $this->userInfo['birthday']!='0000-00-00' ? date('Y') - substr($this->userInfo['birthday'],0,4) + 1 : 0,
				'buyer_area'		=> null,
				'buyer_sex' 		=> $this->userInfo['sex'] && $this->userInfo['sex']!='none' ? $this->userInfo['sex'] : 'none',
				'referer_domain'	=> $refererArr['referer_domain'],
				'referer'			=> $refererArr['referer'],
				'ip'				=> $_SERVER['REMOTE_ADDR']
		);
		$this->db->insert('fm_order_stats', $insert_order_stats_params);
	}

	public function insert_cart_stats($params)
	{
		$refererArr	= $this->get_referer_url($this->session->userdata('shopReferer'));
		$insert_stats_params = array(
				'regist_date'		=> date('Y-m-d H:i:s'),
				'goods_seq' 		=> $params['goods_seq'],
				'goods_name' 		=> $params['goods_name'],
				'option1' 			=> $params['option1'],
				'option2' 			=> $params['option2'],
				'option3' 			=> $params['option3'],
				'option4' 			=> $params['option4'],
				'option5' 			=> $params['option5'],
				'ea' 				=> $params['ea'],
				'age' 				=> $this->userInfo['birthday'] && $this->userInfo['birthday']!='0000-00-00' ? date('Y') - substr($this->userInfo['birthday'],0,4) + 1 : 0,
				'userid' 			=> $this->userInfo['userid'],
				'sex' 				=> $this->userInfo['sex'] && $this->userInfo['sex']!='none' ? $this->userInfo['sex'] : 'none',
				'referer_domain'	=> $refererArr['referer_domain'],
				'referer'			=> $refererArr['referer'],
				'ip'				=> $_SERVER['REMOTE_ADDR']
		);
		$this->db->insert('fm_cart_stats', $insert_stats_params);
	}

	public function insert_wish_stats($params)
	{
		$refererArr	= $this->get_referer_url($this->session->userdata('shopReferer'));
		$insert_stats_params = array(
				'regist_date'		=> date('Y-m-d H:i:s'),
				'goods_seq' 		=> $params['goods_seq'],
				'goods_name' 		=> $params['goods_name'],
				'age' 				=> $this->userInfo['birthday'] && $this->userInfo['birthday']!='0000-00-00' ? date('Y') - substr($this->userInfo['birthday'],0,4) + 1 : 0,
				'userid' 			=> $this->userInfo['userid'],
				'sex' 				=> $this->userInfo['sex'] && $this->userInfo['sex']!='none' ? $this->userInfo['sex'] : 'none',
				'referer_domain'	=> $refererArr['referer_domain'],
				'referer'			=> $refererArr['referer'],
				'ip'				=> $_SERVER['REMOTE_ADDR']
		);
		$this->db->insert('fm_wish_stats', $insert_stats_params);
	}

	public function insert_search_stats($keyword)
	{
		$today = date('Y-m-d',time());
		$list_seq = $this->check_search_keyword($keyword,$today);
		if($list_seq){
			$query = "update fm_search_list set cnt=cnt+1 where list_seq=?";
			$this->db->query($query,array($list_seq));
		}else{
			$query = "insert into fm_search_list set `cnt`=1,`keyword`=?,regist_date=?";
			$this->db->query($query,array($keyword,$today));
		}

		$refererArr	= $this->get_referer_url($this->session->userdata('shopReferer'));

		$insert_stats_params = array(
				'regist_date'		=> date('Y-m-d H:i:s'),
				'keyword' 			=> $keyword,
				'age' 				=> $this->userInfo['birthday'] && $this->userInfo['birthday']!='0000-00-00' ? date('Y') - substr($this->userInfo['birthday'],0,4) + 1 : 0,
				'userid' 			=> $this->userInfo['userid'],
				'sex' 				=> $this->userInfo['sex'] && $this->userInfo['sex']!='none' ? $this->userInfo['sex'] : 'none',
				'referer_domain'	=> $refererArr['referer_domain'],
				'referer'			=> $refererArr['referer'],
				'ip'				=> $_SERVER['REMOTE_ADDR']
		);
		$this->db->insert('fm_search_stats', $insert_stats_params);
	}

	public function check_search_keyword($keyword,$today){
		$query = "select list_seq from fm_search_list where keyword = ? and regist_date=?";
		$bind[] =  $keyword;
		$bind[] =  $today;
		$query = $this->db->query($query,$bind);
		$row = $query->row_array();
		return $row['list_seq'];
	}

	public function last_year_delete()
	{
		$timestamp = strtotime('-1 year');
		$bind_date[] = date('Y-m-t 00:00:00',$timestamp);
		/*
		$query = "delete from fm_order_stats where regist_date < ?";
		$this->db->query($query,$bind_date);
		*/
		$query = "delete from fm_cart_stats where regist_date < ?";
		$this->db->query($query,$bind_date);
		$query = "delete from fm_wish_stats where regist_date < ?";
		$this->db->query($query,$bind_date);
		$query = "delete from fm_search_stats where regist_date < ?";
		$this->db->query($query,$bind_date);
		//debug_var($this->db->queries);
	}

	public function get_goods_cart_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){
			$addWhere	.= " and cs.regist_date >= '".$sdate." 00:00:00' "
						. " and cs.regist_date <= '".$edate." 23:59:59' ";
			$addinWhere	.= " and regist_date >= '".$sdate." 00:00:00' "
						. " and regist_date <= '".$edate." 23:59:59' ";
		}elseif	(empty($sdate) && !empty($edate)){
			$addWhere	.= " and cs.regist_date <= '".$edate." 23:59:59' ";
			$addinWhere	.= " and regist_date <= '".$edate." 23:59:59' ";
		}elseif	(!empty($sdate) && empty($edate)){
			$addWhere	.= " and cs.regist_date >= '".$sdate." 00:00:00' ";
			$addinWhere	.= " and regist_date >= '".$sdate." 00:00:00' ";
		}

		##
		if		(!empty($keyword)){
			$addWhere	.= " and cs.goods_name like '%".addslashes($keyword)."%' ";

			$addinWhere	.= " and goods_name like '%".addslashes($keyword)."%' ";
		}

		##
		if		(!empty($category1)){
			$category_code	= max(array($category1,$category2,$category3,$category4));
			$addWhere		.= " and gcl.category_code = '".$category_code."' ";
			$addFrom		.= "LEFT JOIN fm_category_link as gcl on cs.goods_seq = gcl.goods_seq ";
		}

		##
		if		(!empty($brands1)){
			$brands_code	= max(array($brands1,$brands2,$brands3,$brands4));
			$addWhere		.= " and gbl.category_code = '".$brands_code."' ";
			$addFrom		.= "LEFT JOIN fm_brand_link as gbl on cs.goods_seq = gbl.goods_seq ";
		}

		##
		$orderBy	= " order by css.goods_cnt desc, cs.goods_name, cs.option1, cs.option2, cs.option3, cs.option4, cs.option5";
		if		($order_by == 'users'){
			$orderBy	= " order by css2.user_cnt desc, cs.goods_name, cs.option1, cs.option2, cs.option3, cs.option4, cs.option5";
		}


		$query	= "select
					cs.goods_seq			as goods_seq,
					cs.goods_name			as stat_goods_name,
					cs.option1				as option1,
					cs.option2				as option2,
					cs.option3				as option3,
					cs.option4				as option4,
					cs.option5				as option5,
					css.goods_cnt			as goods_cnt,
					count(*)				as option_cnt,
					css2.user_cnt			as user_cnt,
					css3.user_option_cnt	as user_option_cnt,
					gs.stock				as stock,
					gs.badstock				as badstock,
					gs.reservation15		as reservation15,
					gs.reservation25		as reservation25,
					g.page_view				as page_view,
					g.review_count			as now_review_cnt,
					IF(cs.goods_seq,(select count(*) from fm_cart where goods_seq = cs.goods_seq),0) as now_cart_cnt,
					IF(cs.goods_seq,(select count(*) from fm_goods_wish where goods_seq = cs.goods_seq),0) as now_wish_cnt,
					IF(cs.goods_seq,(select count(*) from (select count(*), goods_seq from fm_goods_fblike where member_seq>0 group by goods_seq, member_seq) tmp where goods_seq = cs.goods_seq group by goods_seq),0) as now_like_cnt,
					IF(cs.goods_seq,(select count(*) as cnt from fm_goods_restock_notify  where goods_seq=cs.goods_seq and notify_status='none' group by goods_seq),0) as now_restock_cnt
				from
					fm_cart_stats						as cs
					INNER JOIN (select count( * ) as goods_cnt, sum( ea ) goods_ea, goods_name FROM fm_cart_stats WHERE 1 ".$addinWhere." group by goods_name)	as css
					on cs.goods_name = css.goods_name
					LEFT JOIN (select count(*) as user_cnt, goods_name from (select  goods_name, option1, option2, option3, option4, option5, userid from fm_cart_stats where 1 ".$addinWhere." and userid is not null group by goods_name, option1, option2, option3, option4, option5, userid) as tmp group by goods_name)	as css2
					on cs.goods_name = css2.goods_name
					LEFT JOIN (select count(*) as user_option_cnt, goods_name, option1, option2, option3, option4, option5 from (select goods_name, option1, option2, option3, option4, option5 from fm_cart_stats where userid is not null group by goods_name, option1, option2, option3, option4, option5, userid) as tmp group by goods_name, option1, option2, option3, option4, option5)					as css3
					on (cs.goods_name = css3.goods_name and cs.option1 = css3.option1 and cs.option2 = css3.option2 and cs.option3 = css3.option3 and cs.option4 = css3.option4 and cs.option5 = css3.option5  )
					LEFT JOIN fm_goods					as g
					on cs.goods_seq = g.goods_seq
					LEFT JOIN fm_goods_option			as go
					on ( cs.goods_seq = go.goods_seq and cs.option1 = go.option1 and cs.option2 = go.option2 and cs.option3 = go.option3 and cs.option4 = go.option4 and cs.option5 = go.option5 )
					LEFT JOIN fm_goods_supply			as gs
					on go.option_seq = gs.option_seq
					".$addFrom."
				where
					cs.cart_stats_seq > 0
					".$addWhere."
				group by
					cs.goods_name, option1, option2, option3, option4, option5
				".$orderBy."
				limit 0, 100";

		return $this->db->query($query);
	}

	public function get_goods_wish_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){
			$addWhere	.= " and ws.regist_date >= '".$sdate." 00:00:00' "
						. " and ws.regist_date <= '".$edate." 23:59:59' ";
		}elseif	(empty($sdate) && !empty($edate)){
			$addWhere	.= " and ws.regist_date <= '".$edate." 23:59:59' ";
		}elseif	(!empty($sdate) && empty($edate)){
			$addWhere	.= " and ws.regist_date >= '".$sdate." 00:00:00' ";
		}

		##
		if		(!empty($keyword)){
			$addWhere	.= " and ws.goods_name like '%".addslashes($keyword)."%' ";
		}

		##
		if		(!empty($category1)){
			$category_code	= max(array($category1,$category2,$category3,$category4));
			$addWhere		.= " and gcl.category_code = '".$category_code."' ";
			$addFrom		.= "LEFT JOIN fm_category_link as gcl on ws.goods_seq = gcl.goods_seq ";
		}

		##
		if		(!empty($brands1)){
			$brands_code	= max(array($brands1,$brands2,$brands3,$brands4));
			$addWhere		.= " and gbl.category_code = '".$brands_code."' ";
			$addFrom		.= "LEFT JOIN fm_brand_link as gbl on ws.goods_seq = gbl.goods_seq ";
		}

		##
		$orderBy	= " order by wss.goods_cnt desc, ws.goods_name";
		if		($order_by == 'users'){
			$orderBy	= " order by wss2.user_cnt desc, ws.goods_name";
		}


		$query	= "select
					ws.goods_seq			as goods_seq,
					ws.goods_name			as stat_goods_name,
					wss.goods_cnt			as goods_cnt,
					wss2.user_cnt			as user_cnt,
					gs.tstock				as tstock,
					gs.tbadstock			as tbadstock,
					gs.treservation15		as treservation15,
					gs.treservation25		as treservation25,
					g.page_view				as page_view,
					g.review_count			as now_review_cnt,
					IF(ws.goods_seq,(select count(*) from fm_cart where goods_seq = ws.goods_seq),0) as now_cart_cnt,
					IF(ws.goods_seq,(select count(*) from fm_goods_wish where goods_seq = ws.goods_seq),0) as now_wish_cnt,
					IF(ws.goods_seq,(select count(*) from (select count(*), goods_seq from fm_goods_fblike where member_seq>0 group by goods_seq, member_seq) tmp where goods_seq = ws.goods_seq group by goods_seq),0) as now_like_cnt,
					IF(ws.goods_seq,(select count(*) as cnt from fm_goods_restock_notify  where goods_seq=ws.goods_seq and member_seq>0 and notify_status='none' group by goods_seq),0) as now_restock_cnt
				from
					fm_wish_stats						as ws
					INNER JOIN (select count( * ) as goods_cnt, goods_name FROM fm_wish_stats group by goods_name)	as wss
					on ws.goods_name = wss.goods_name
					LEFT JOIN (select count(*) as user_cnt, goods_name from (select  goods_name, userid from fm_wish_stats where userid is not null group by goods_name, userid) as tmp group by goods_name)			as wss2
					on ws.goods_name = wss2.goods_name
					LEFT JOIN fm_goods					as g
					on ws.goods_seq = g.goods_seq
					LEFT JOIN (select goods_seq, sum(stock) as tstock, sum(badstock) as tbadstock, sum(reservation15) as treservation15, sum(reservation25) as treservation25 FROM fm_goods_supply group by goods_seq)	as gs
					on g.goods_seq = gs.goods_seq
					".$addFrom."
				where
					ws.wish_stats_seq > 0
					".$addWhere."
				group by
					ws.goods_name
				".$orderBy."
				limit 0, 100";

		return $this->db->query($query);
	}

	public function get_goods_search_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){
			$addWhere	.= " and regist_date >= '".$sdate." 00:00:00' "
						. " and regist_date <= '".$edate." 23:59:59' ";
		}elseif	(empty($sdate) && !empty($edate)){
			$addWhere	.= " and regist_date <= '".$edate." 23:59:59' ";
		}elseif	(!empty($sdate) && empty($edate)){
			$addWhere	.= " and regist_date >= '".$sdate." 00:00:00' ";
		}

		##
		if		(!empty($keyword)){
			$addWhere	.= " and keyword like '%".addslashes($keyword)."%' ";
		}

		$query	= "select
					keyword,
					count(*) keyword_cnt
				from
					fm_search_stats
				where
					search_stats_seq > 0
					".$addWhere."
				group by keyword
				order by keyword_cnt desc
				limit 0, 100";
		return $this->db->query($query);
	}

	public function get_goods_search_by_age($keyword,$period)
	{
		$start = date('Y-m-d',strtotime('-'.$period.' days'))." 00:00:00";		
		$query = "select age,count(*) cnt  from fm_search_stats where keyword=? and regist_date>=? group by age";
		return $this->db->query($query,array($keyword,$start));
	}

	public function get_goods_search_by_sex($keyword,$period)
	{
		$start = date('Y-m-d',strtotime('-'.$period.' days'))." 00:00:00";		
		$query = "select sex,count(*) cnt  from fm_search_stats where keyword=? and regist_date>=? group by sex";
		return $this->db->query($query,array($keyword,$start));
	}

	public function get_goods_search_by_date($keyword,$period)
	{
		$start = date('Y-m-d',strtotime('-'.$period.' days'))." 00:00:00";		
		$query = "
		select * from (
			select substring(regist_date,1,10) regist_date,count(*) cnt  from fm_search_stats where keyword=? and regist_date>=? group by substring(regist_date,1,10)
		) t order by t.cnt desc limit 5
		";
		return $this->db->query($query,array($keyword,$start));
	}

	public function get_goods_search_paging_by_date($keyword,$start,$end,$page)
	{
		
		$where[] = "keyword = ?";
		$bind[] = $keyword;

		if( $start ){
			$start = $start." 00:00:00";
			$where[] = "regist_date >= ?";
			$bind[] = $start;
		}
		if( $end ){ 
			$end = $end." 24:00:00";
			$where[] = "regist_date <= ?";
			$bind[] = $end;
		}
		$query = "select * from fm_search_stats where ".implode(" and ",$where);
		$bind = array($keyword,$start,$end);
		// paging (페이지당출력수,현재페이지넘버,페이지숫자링크갯수,쿼리,인자)
		$result = select_page(10,$page,10,$query,$bind);
		return $result;
	}

	
	public function get_goods_review_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){
			$addWhere2	.= " and r_date >= '".$sdate." 00:00:00' "
						. " and r_date <= '".$edate." 23:59:59' ";
		}elseif	(empty($sdate) && !empty($edate)){
			$addWhere2	.= " and r_date <= '".$edate." 23:59:59' ";
		}elseif	(!empty($sdate) && empty($edate)){
			$addWhere2	.= " and r_date >= '".$sdate." 00:00:00' ";
		}

		##
		if		(!empty($keyword)){
			$addWhere	.= " and g.goods_name like '%".addslashes($keyword)."%' ";
		}

		##
		if		(!empty($category1)){
			$category_code	= max(array($category1,$category2,$category3,$category4));
			$addWhere		.= " and gcl.category_code = '".$category_code."' ";
			$addFrom		.= "LEFT JOIN fm_category_link as gcl on g.goods_seq = gcl.goods_seq ";
		}

		##
		if		(!empty($brands1)){
			$brands_code	= max(array($brands1,$brands2,$brands3,$brands4));
			$addWhere		.= " and gbl.category_code = '".$brands_code."' ";
			$addFrom		.= "LEFT JOIN fm_brand_link as gbl on g.goods_seq = gbl.goods_seq ";
		}

		$query	= "select
					g.goods_seq				as goods_seq,
					g.goods_name			as stat_goods_name,
					gr.reviewCnt		as review_cnt,
					gs.tstock				as tstock,
					gs.tbadstock			as tbadstock,
					gs.treservation15		as treservation15,
					gs.treservation25		as treservation25,
					g.page_view				as page_view,
					g.review_count			as now_review_cnt,
					(select count(*) from fm_cart where goods_seq = g.goods_seq)	as now_cart_cnt,
					(select count(*) from fm_goods_wish where goods_seq = g.goods_seq)	as now_wish_cnt,
					(select count(*) from (select count(*), goods_seq from fm_goods_fblike where member_seq>0 group by goods_seq, member_seq) tmp where goods_seq = g.goods_seq group by goods_seq) as now_like_cnt,
					(select count(*) as cnt from fm_goods_restock_notify  where goods_seq=g.goods_seq and member_seq>0 and notify_status='none' group by goods_seq) as now_restock_cnt
				from
					fm_goods							as g
					INNER JOIN (select count(*) reviewCnt, goods_seq from fm_goods_review
								where length(goods_seq) > 0 ".$addWhere2." group by goods_seq )	as gr
						on (INSTR(gr.goods_seq, CONCAT(g.goods_seq, ',')) or 
							INSTR(gr.goods_seq, CONCAT(',', g.goods_seq)) or 
							gr.goods_seq = g.goods_seq)
					LEFT JOIN (select goods_seq, sum(stock) as tstock, sum(badstock) as tbadstock, sum(reservation15) as treservation15, sum(reservation25) as treservation25 FROM fm_goods_supply group by goods_seq)	as gs
					on g.goods_seq = gs.goods_seq
					".$addFrom."
				where
					g.goods_seq > 0
					".$addWhere."
				group by g.goods_seq
				order by gr.reviewCnt desc
				limit 0, 100";

		return $this->db->query($query);
	}

	public function get_goods_restock_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){
			$addWhere	.= " and grn.regist_date >= '".$sdate." 00:00:00' "
						. " and grn.regist_date <= '".$edate." 23:59:59' ";
		}elseif	(empty($sdate) && !empty($edate)){
			$addWhere	.= " and grn.regist_date <= '".$edate." 23:59:59' ";
		}elseif	(!empty($sdate) && empty($edate)){
			$addWhere	.= " and grn.regist_date >= '".$sdate." 00:00:00' ";
		}

		##
		if		(!empty($keyword)){
			$addWhere	.= " and g.goods_name like '%".addslashes($keyword)."%' ";
		}

		##
		if		(!empty($category1)){
			$category_code	= max(array($category1,$category2,$category3,$category4));
			$addWhere		.= " and gcl.category_code = '".$category_code."' ";
			$addFrom		.= "LEFT JOIN fm_category_link as gcl on g.goods_seq = gcl.goods_seq ";
		}

		##
		if		(!empty($brands1)){
			$brands_code	= max(array($brands1,$brands2,$brands3,$brands4));
			$addWhere		.= " and gbl.category_code = '".$brands_code."' ";
			$addFrom		.= "LEFT JOIN fm_brand_link as gbl on g.goods_seq = gbl.goods_seq ";
		}

		$query	= "select
					g.goods_seq				as goods_seq,
					g.goods_name			as stat_goods_name,
					count(grn.goods_seq)	as restock_cnt,
					gs.tstock				as tstock,
					gs.tbadstock			as tbadstock,
					gs.treservation15		as treservation15,
					gs.treservation25		as treservation25,
					g.page_view				as page_view,
					g.review_count			as now_review_cnt,
					(select count(*) from fm_cart where goods_seq = g.goods_seq)	as now_cart_cnt,
					(select count(*) from fm_goods_wish where goods_seq = g.goods_seq)	as now_wish_cnt,
					(select count(*) from (select count(*), goods_seq from fm_goods_fblike where member_seq>0 group by goods_seq, member_seq) tmp where goods_seq = g.goods_seq group by goods_seq) as now_like_cnt,
					(select count(*) as cnt from fm_goods_restock_notify  where goods_seq=g.goods_seq and member_seq>0 and notify_status='none' group by goods_seq) as now_restock_cnt
				from
					fm_goods							as g
					INNER JOIN fm_goods_restock_notify	as grn
					on grn.goods_seq = g.goods_seq
					LEFT JOIN (select goods_seq, sum(stock) as tstock, sum(badstock) as tbadstock, sum(reservation15) as treservation15, sum(reservation25) as treservation25 FROM fm_goods_supply group by goods_seq)	as gs
					on g.goods_seq = gs.goods_seq
					".$addFrom."
				where
					grn.notify_status='none'
					".$addWhere."
				group by grn.goods_seq
				order by restock_cnt desc
				limit 0, 100";

		return $this->db->query($query);
	}

	public function get_sales_sales_monthly_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($q_type == 'order'){

			$addWhere	.= " and year(deposit_date) = '".$year."' ";
			if	(is_array($sitetype) && count($sitetype) > 0){
				$addWhere	.= " and sitetype in ('".implode("','",$sitetype)."') ";
			}

			/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
			$query	= "select
						stats_year
						,stats_month
						,sum(settleprice_sum) as month_settleprice_sum
						,sum(enuri_sum) as month_enuri_sum
						,sum(emoney_use_sum) as month_emoney_use_sum
						,sum(cash_use_sum) as month_cash_use_sum
						,sum(count_sum) as month_count_sum
						,sum(shipping_cost_sum+goods_shipping_cost_sum) as month_shipping_cost_sum
						,sum(option_ori_price_sum) as month_ori_price_sum
						,sum(shipping_coupon_sale_sum+option_coupon_sale_sum) as month_coupon_sale_sum
						,sum(shipping_promotion_code_sale_sum+option_promotion_code_sale_sum) as month_promotion_code_sale_sum
						,sum(option_fblike_sale_sum) as month_fblike_sale_sum
						,sum(option_mobile_sale_sum) as month_mobile_sale_sum
						,sum(ifnull(option_member_sale_sum,0)+ifnull(suboption_member_sale_sum,0)) as month_member_sale_sum
						,sum(ifnull(option_referer_sale_sum,0)) as month_referer_sale_sum
						,sum(ifnull(option_supply_price_sum,0)+ifnull(suboption_supply_price_sum,0)) as month_supply_price_sum
					from
					(
						select *
						from fm_accumul_sales_mdstats
						where 1=1
						".$addWhere."
					) as b
					group by b.stats_month";

		}elseif	($q_type == 'refund'){

			$addWhere	.= " and year(refund_date) = '".$year."' ";
			if	(is_array($sitetype) && count($sitetype) > 0){
				$addWhere	.= " and sitetype in ('".implode("','",$sitetype)."') ";
			}

			/* 데이터 추출 : 환불금액, 환불건수 */
			$query	= "
						select
							stats_year
							,stats_month
							,sum(refund_price_sum) as month_refund_price_sum
							,sum(refund_count_sum) as month_refund_count_sum
							,sum(ifnull(option_supply_price_sum,0)+ifnull(suboption_supply_price_sum,0)) as month_refund_supply_price_sum
						from
						(
							select *
							from fm_accumul_sales_refund 
							where 1=1
							".$addWhere."
						) as b
						group by b.stats_month";
		}

		return $this->db->query($query);
	}

	public function get_sales_sales_daily_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($q_type == 'order'){

			$addWhere	= "and year(deposit_date)='".$year."' "
						. "and month(deposit_date)='".$month."' ";
			if	(is_array($sitetype) && count($sitetype) > 0){
				$addWhere	.= " and sitetype in ('".implode("','",$sitetype)."') ";
			}

			/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
			$sql = "
					select
						stats_year
						,stats_month
						,stats_day
						,sum(settleprice_sum) as day_settleprice_sum
						,sum(enuri_sum) as day_enuri_sum
						,sum(emoney_use_sum) as day_emoney_use_sum
						,sum(cash_use_sum) as day_cash_use_sum
						,sum(count_sum) as day_count_sum
						,sum(shipping_cost_sum+goods_shipping_cost_sum) as day_shipping_cost_sum
						,sum(option_ori_price_sum) as day_ori_price_sum
						,sum(shipping_coupon_sale_sum+option_coupon_sale_sum) as day_coupon_sale_sum
						,sum(shipping_promotion_code_sale_sum+option_promotion_code_sale_sum) as day_promotion_code_sale_sum
						,sum(option_fblike_sale_sum) as day_fblike_sale_sum
						,sum(option_mobile_sale_sum) as day_mobile_sale_sum
						,sum(ifnull(option_member_sale_sum,0)+ifnull(suboption_member_sale_sum,0)) as day_member_sale_sum
						,sum(ifnull(option_referer_sale_sum,0)) as day_referer_sale_sum
						,sum(ifnull(option_supply_price_sum,0)+ifnull(suboption_supply_price_sum,0)) as day_supply_price_sum
						from
						(
							select *
							from fm_accumul_sales_mdstats
							where 1=1
							".$addWhere."
						) as b
						group by b.stats_day";

		}elseif($q_type == 'refund'){

			$addWhere	= "and year(refund_date)='".$year."' "
						. "and month(refund_date)='".$month."' ";
			if	(is_array($sitetype) && count($sitetype) > 0){
				$addWhere	.= " and sitetype in ('".implode("','",$sitetype)."') ";
			}

			/* 데이터 추출 : 환불금액, 환불건수 */
			$sql = "
				select
					stats_year
					,stats_month
					,stats_day
					,sum(refund_price_sum+refund_emoney_sum+refund_cash_sum) as day_refund_price_sum
					,sum(refund_count_sum) as day_refund_count_sum
					,sum(cancel_price_sum) as day_cancel_price_sum
					,sum(cancel_count_sum) as day_cancel_count_sum
					,sum(return_price_sum) as day_return_price_sum
					,sum(return_count_sum) as day_return_count_sum
					,sum(ifnull(option_supply_price_sum,0)+ifnull(suboption_supply_price_sum,0)) as day_refund_supply_price_sum
					from
					(
						select *
						from fm_accumul_sales_refund 
						where 1=1
						".$addWhere."
					) as b
					group by b.stats_day
			";
		}

		return $this->db->query($sql);
	}

	public function get_sales_sales_hour_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}


		/* 데이터 추출 : 결제금액, 건수 */
		$addWhere	= " and a.deposit_yn='y' and a.step between '15' and '75'"
					. " and year(a.deposit_date) = '".$year."' ";
		if(!empty($month))
			$addWhere	.= " and month(a.deposit_date) = '".$month."' ";
		if	(is_array($sitetype) && count($sitetype) > 0){
			$addWhere	.= " and a.sitetype in ('".implode("','",$sitetype)."') ";
		}

		$query = "
				select
					a.order_seq as order_seq
					,year(a.deposit_date) as stats_year
					,month(a.deposit_date) as stats_month
					,hour(a.deposit_date) as stats_hour
					,day(a.deposit_date) as stats_day
					,sum(a.settleprice) as month_settleprice_sum
					,count(*) as month_count_sum
				from fm_order as a
				where a.order_seq > 0 ".$addWhere."
				group by stats_hour";

		return $this->db->query($query);
	}

	public function get_sales_goods_daily_stats($params,$type='web'){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$addWhere	= " and a.deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";
		if	($category_code)
			$addWhere2	.= " and find_in_set('".$category_code."', category_codes)";

		if	($brands_code)
			$addWhere2	.= " and find_in_set('".$brands_code."', brand_codes)";

		if	($keyword)
			$addWhere2	.= " and concat(goods_seq,ifnull(goods_code,''),order_goods_name) like '%".addslashes($keyword)."%'";

		$orderBy	= " order by ".$sort.", goods_seq asc";

		if	($q_type == 'list'){
			if($type == 'web'){
				$pagein = " limit 0, 300 ";
			}
			$query	= "select *, price*ea as goods_price
					from fm_accumul_stats_sales 
					where goods_seq > 0 
					and deposit_ymd between '".$sdate."'  and '".$edate."' ".$addWhere2."
					".$orderBy." ".$pagein;

		}elseif($q_type == 'order'){

			/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
			$query	= "select
						stats_year
						,stats_month
						,stats_day
						,sum(settleprice_sum) as settleprice_sum
						,sum(enuri_sum) as enuri_sum
						,sum(emoney_use_sum) as emoney_use_sum
						,sum(cash_use_sum) as cash_use_sum
						,sum(shipping_cost_sum) as shipping_cost_sum
						,sum(ifnull(option_ori_price_sum,0)+ifnull(suboption_ori_price_sum,0)) as ori_price_sum
						,sum(ifnull(option_supply_price_sum,0)+ifnull(suboption_supply_price_sum,0)) as supply_price_sum
						,sum(goods_shipping_cost_sum) as goods_shipping_cost_sum
					from
					(
						select
							a.order_seq as order_seq
							,year(a.deposit_date) as stats_year
							,month(a.deposit_date) as stats_month
							,day(a.deposit_date) as stats_day
							,(a.settleprice) as settleprice_sum
							,ifnull(a.enuri,0) as enuri_sum
							,ifnull(a.emoney,0) as emoney_use_sum
							,ifnull(a.cash,0) as cash_use_sum
							,ifnull(a.international_cost,0)+ifnull(a.shipping_cost,0) as shipping_cost_sum
							,(select sum(price*ea) from fm_order_item_option where order_seq=a.order_seq) as option_ori_price_sum
							,(select sum(price*ea) from fm_order_item_suboption where order_seq=a.order_seq) as suboption_ori_price_sum
							,(select sum(goods_shipping_cost) from fm_order_item where order_seq=a.order_seq) as goods_shipping_cost_sum
							,(select sum(supply_price*ea) from fm_order_item_option where order_seq=a.order_seq) as option_supply_price_sum
							,(select sum(supply_price*ea) from fm_order_item_suboption where order_seq=a.order_seq) as suboption_supply_price_sum
						from fm_order as a
						where a.deposit_yn='y' and
							a.step between '15' and '85'
							".$addWhere."
					) as b";
		}elseif($q_type == 'refund'){

			$addWhere	= " and a.refund_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";

			/* 데이터 추출 : 환불금액, 환불건수 */
			$query	= "select
						stats_year
						,stats_month
						,stats_day
						,sum(refund_price_sum) as refund_price_sum
						,sum(refund_count_sum) as refund_count_sum
						,sum(cancel_price_sum) as cancel_price_sum
						,sum(cancel_count_sum) as cancel_count_sum
						,sum(return_price_sum) as return_price_sum
						,sum(return_count_sum) as return_count_sum
						,sum(ifnull(option_supply_price_sum,0)+ifnull(suboption_supply_price_sum,0)) as refund_supply_price_sum
					from
						(
							select
								a.order_seq as order_seq
								,year(a.refund_date) as stats_year
								,month(a.refund_date) as stats_month
								,day(a.refund_date) as stats_day
								,sum(a.refund_price) as refund_price_sum
								,count(*) as refund_count_sum
								,sum(if(a.refund_type='cancel_payment',a.refund_price,0)) as cancel_price_sum
								,sum(if(a.refund_type='cancel_payment',1,0)) as cancel_count_sum
								,sum(if(a.refund_type='return',a.refund_price,0)) as return_price_sum
								,sum(if(a.refund_type='return',1,0)) as return_count_sum
								,(select sum(sb.supply_price*sa.ea) from fm_order_refund_item sa, fm_order_item_option sb where sa.refund_code=a.refund_code and sa.option_seq > 0 and sa.option_seq=sb.item_option_seq) as option_supply_price_sum
								,(select sum(sb.supply_price*sa.ea) from fm_order_refund_item sa, fm_order_item_suboption sb where sa.refund_code=a.refund_code and sa.suboption_seq > 0 and sa.suboption_seq=sb.item_suboption_seq) as suboption_supply_price_sum
							from fm_order_refund as a
								left join fm_order as b on a.order_seq = b.order_seq
							where a.order_seq > 0 and
								b.step between '15' and '85'
								".$addWhere."
							group by a.refund_code
						) as b";
		}

		$query = $this->db->query($query);		
		return $query;
	}

	public function get_sales_goods_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){			
			$accumul_where	.= " and deposit_ymd between '".$sdate."'  and '".$edate."' ";			
		}elseif	(empty($sdate) && !empty($edate)){			
			$accumul_where	.= " and deposit_ymd <= '".$edate."' ";
		}elseif	(!empty($sdate) && empty($edate)){			
			$accumul_where	.= " and deposit_ymd >= '".$sdate."' ";
		}

		##
		if		(!empty($keyword)){
			$addWhere	.= " and (goods_seq like '".$keyword."%' OR ifnull(goods_code,'') like '%".$keyword."%' OR order_goods_name like '%".$keyword."%') ";
		}

		##
		if		(!empty($category1)){
			$category_code	= max(array($category1,$category2,$category3,$category4));
			if	($category_code){
				$addWhere	.= " and find_in_set('".$category_code."', category_codes)";
				/*$addFrom	.= "INNER JOIN fm_category_link		as cl
								on g.goods_seq = cl.goods_seq  ";*/
			}
		}

		##
		if		(!empty($brands1)){
			$brands_code	= max(array($brands1,$brands2,$brands3,$brands4));
			if	($brands_code){
				$addWhere	.= " and find_in_set('".$brands_code."', brand_codes)";
				/*$addFrom	.= "INNER JOIN fm_brand_link		as bl
								on g.goods_seq = bl.goods_seq  ";*/
			}
		}

		$orderBy1	= "goods_cnt desc";
		$orderBy2	= "option_cnt desc";
		if	($order_by == 'price'){
			$orderBy1	= "goods_price desc";
			$orderBy2	= "option_price desc";
		}

		$query	= "select
					ord.goods_seq,
					ord.goods_name			as stat_goods_name,
					ord.goods_price,
					ord.goods_cnt,					
					ord.option1,
					ord.option2,
					ord.option3,
					ord.option4,
					ord.option5,
					ord.title1,
					ord.title2,
					ord.title3,
					ord.title4,
					ord.title5,	
					gs.stock				as tot_stock,
					g.page_view				as page_view,
					g.review_count			as now_review_cnt,
					(select count(*) cart_cnt from fm_cart_option co,fm_cart ca where ca.cart_seq=co.cart_seq and ca.goods_seq=ord.goods_seq and ca.distribution='cart' and ifnull(co.option1,'')=ord.option1 and ifnull(co.option2,'')=ord.option2 and ifnull(co.option3,'')=ord.option3 and ifnull(option4,'')=ord.option4 and ifnull(option5,'')=ord.option5) as now_cart_cnt,
					(select count(*) from fm_goods_wish where goods_seq = ord.goods_seq) as now_wish_cnt,
					(select count(*) from fm_goods_fblike where member_seq>0 and goods_seq = ord.goods_seq) as now_like_cnt,
					(select count(*) as cnt from fm_goods_restock_notify  where goods_seq=ord.goods_seq and member_seq>0 and notify_status='none') as now_restock_cnt
				from
					( 
					 select 
						goods_seq,
						sum(ea) as goods_cnt,
						sum(price*t.ea) as goods_price,
						order_goods_name  as goods_name,
						option1,
						option2,
						option3,
						option4,
						option5,
						title1,title2,title3,title4,title5						
					 from 
					 (
					  select
						goods_seq, 
						ea,
						price,
						order_goods_name,
						ifnull(option1,'') option1,
						ifnull(option2,'') option2,
						ifnull(option3,'') option3,
						ifnull(option4,'') option4,
						ifnull(option5,'') option5,
						title1,title2,title3,title4,title5
					  from 
						fm_accumul_stats_sales
					  where
						goods_seq > 0
						".$accumul_where." 
						".$addWhere."
					) t
					group by goods_seq,option1,option2,option3,option4,option5
					order by ".$orderBy1.", goods_seq
					limit 0, 100)	as ord
					LEFT JOIN fm_goods_option   as go on ord.goods_seq = go.goods_seq
						and ord.option1=ifnull(go.option1,'') and ord.option2=ifnull(go.option2,'') and ord.option3=ifnull(go.option3,'')
						and ord.option4=ifnull(go.option4,'') and ord.option5=ifnull(go.option5,'')
					LEFT JOIN fm_goods_supply	as gs on go.option_seq = gs.option_seq
					LEFT JOIN fm_goods	as g on g.goods_seq = ord.goods_seq
					where g.goods_type = 'goods'
				order by ".$orderBy1.", purchase_ea desc ";
		$query =  $this->db->query($query);		
		return $query;
	}

	/* 추가 옵션 상품에 따른 쿼리 :: 2014-08-01 lwh */
	public function get_sales_option_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		(!empty($sdate) && !empty($edate)){
			$addWhere	.= " and o.deposit_date >= '".$sdate." 00:00:00' "
						. " and o.deposit_date <= '".$edate." 23:59:59' ";
		}elseif	(empty($sdate) && !empty($edate)){
			$addWhere	.= " and o.deposit_date <= '".$edate." 23:59:59' ";
		}elseif	(!empty($sdate) && empty($edate)){
			$addWhere	.= " and o.deposit_date >= '".$sdate." 00:00:00' ";
		}

		$orderBy1	= "goods_cnt desc";
		$orderBy2	= "option_cnt desc";
		if	($order_by == 'price'){
			$orderBy1	= "goods_price desc";
			$orderBy2	= "option_price desc";
		}

		$sql	= "select
						oi.goods_seq		as goods_seq,
						sum(oio.ea)			as option_cnt,
						sum(oio.price*oio.ea)as option_price,
						oio.option1			as option1,
						oio.option2			as option2,
						oio.option3			as option3,
						oio.option4			as option4,
						oio.option5			as option5,
						gs.stock			as stock,
						gs.badstock			as badstock,
						gs.reservation15	as reservation15,
						gs.reservation25	as reservation25
					from
						fm_order					as o,
						fm_order_item				as oi,
						fm_order_item_option		as oio, 
						fm_goods					as g,
						fm_goods_option				as go
						LEFT JOIN fm_goods_supply	as gs
						on go.option_seq = gs.option_seq
					where
						oio.item_seq = oi.item_seq and
						oi.order_seq = o.order_seq and
						oi.goods_seq = g.goods_seq and
						g.goods_seq = go.goods_seq and
						oio.option1 = go.option1 and 
						oio.option2 = go.option2 and 
						oio.option3 = go.option3 and 
						oio.option4 = go.option4 and 
						oio.option5 = go.option5 and
						o.deposit_yn = 'y' and
						o.step between '15' and '75'
						and g.goods_seq	= '".$goods_seq."'
						".$addWhere."
					group by
						oi.goods_seq, oio.option1, oio.option2, oio.option3, oio.option4, oio.option5
					order by ".$orderBy2;

		$query	= $this->db->query($sql);
		$data	= $query->result_array();

		return $data;		
	}

	public function get_sales_payment_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
		$addWhere	= " and year(a.deposit_date)='".$year."' ";
		if(!empty($month))
			$addWhere	.= " and month(a.deposit_date)='".$month."' ";
		if	(is_array($sitetype) && count($sitetype) > 0){
			$addWhere	.= " and a.sitetype in ('".implode("','",$sitetype)."') ";
		}

		$query	= "select
					a.order_seq as order_seq
					,year(a.deposit_date) as stats_year
					,month(a.deposit_date) as stats_month
					,a.payment as payment
					,IF(a.pg='kakaopay','kakaopay','') as pgs
					,day(a.deposit_date) as stats_day
					,sum(a.settleprice) as month_settleprice_sum
					,count(order_seq) as month_count_sum
				from fm_order as a
				where a.deposit_yn='y' and a.step between '15' and '85'
				".$addWhere."
				group by a.payment, pgs";

		return $this->db->query($query);
	}

	public function get_sales_platform_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
		$addWhere	= " and year(a.deposit_date)='".$year."' ";
		if(!empty($month))
			$addWhere	.= " and month(a.deposit_date)='".$month."' ";
		if	(is_array($sitetype) && count($sitetype) > 0){
			$addWhere	.= " and a.sitetype in ('".implode("','",$sitetype)."') ";
		}

		$query = "select
					sitetype,
					sum(settleprice) as settleprice_sum,
					count(*) as count_sum
				from fm_order as a
				where a.deposit_yn='y' and a.step between '15' and '75'
				".$addWhere."
				group by sitetype";

		return $this->db->query($query);
	}

	public function get_sales_etc_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$addWhere	.= " and year(a.deposit_date) = '".$year."' ";
		if(!empty($month))
			$addWhere	.= " and month(a.deposit_date)='".$month."' ";
		if	(is_array($sitetype) && count($sitetype) > 0){
			$addWhere	.= " and a.sitetype in ('".implode("','",$sitetype)."') ";
		}

		if	($q_type == 'sexage'){

			$addWhere	.= " and b.buyer_age is not null ";
			$addWhere	.= " and b.buyer_sex is not null ";

			/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
			$query	= "select
						a.order_seq as order_seq
						,year(a.deposit_date) as stats_year
						,month(a.deposit_date) as stats_month
						,day(a.deposit_date) as stats_day
						,case
							when b.buyer_age < 20 then '10대 이하'
							when b.buyer_age < 30 then '20대'
							when b.buyer_age < 40 then '30대'
							when b.buyer_age < 50 then '40대'
							when b.buyer_age < 60 then '50대'
							when b.buyer_age >= 60 then '60대 이상'
						end as buyer_age
						,case
							when b.buyer_sex = 'male' then '남'
							when b.buyer_sex = 'female' then '여'
						end as buyer_sex
						,sum(a.settleprice) as month_settleprice_sum
						,count(*) as month_count_sum
					from fm_order as a
						inner join fm_order_stats as b on a.order_seq=b.order_seq
					where a.deposit_yn='y' and a.step between '15' and '75'
					".$addWhere."
					group by buyer_age,buyer_sex";
		}elseif($q_type == 'location'){
			/* 데이터 추출 : 주문금액, 결제금액, 적립금사용, 할인금액, 배송비, 매입금액 */
			$query = "select
						a.order_seq as order_seq
						,year(a.deposit_date) as stats_year
						,month(a.deposit_date) as stats_month
						,day(a.deposit_date) as stats_day
						,substring(o.recipient_address,1,2) as location
						,sum(a.settleprice) as month_settleprice_sum
						,count(*) as month_count_sum
					from fm_order as a, fm_order_shipping as o
					where a.order_seq = o.order_seq and a.deposit_yn='y' and a.step between '15' and '75'
					".$addWhere."
					group by location";
		}

		return $this->db->query($query);
	}

	public function get_sales_referer_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($dateSel_type == 'daily'){
			$selVal			= "SUBSTRING(o.deposit_date, 9, 2) as `date`, ";
			$scDate			= $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT);
			$addBy			= " `date` ";
		}else{
			$selVal			= "SUBSTRING(o.deposit_date, 6, 2) as `date`, ";
			$scDate			= $year;
			$addBy			= " `date` ";
		}

		if	($dateSel_type == '30days'){
			$selVal			= "SUBSTRING(o.deposit_date, 9, 2) as `date`, ";
			$addBy			= " `date` ";
			$sdate			= date('Y-m-d', strtotime('-29 day')) . ' 00:00:00';
			$edate			= date('Y-m-d') . ' 23:59:59';
			$addWhere		= " and o.deposit_date between '".$sdate."' and '".$edate."' ";
		}else if($dateSel_type == '10days'){
			$selVal			= 'os.referer as referer_url, ';
			$addBy			= '';
			$ordBy			= " `cnt` desc ";
			$sdate			= date('Y-m-d', strtotime('-10 day')) . ' 00:00:00';
			$edate			= date('Y-m-d') . ' 23:59:59';
			$addWhere		= " and o.deposit_date between '".$sdate."' and '".$edate."' ";
		}else{
			$addWhere		= " and o.deposit_date like '".$scDate."%' ";
		}

		if	($referer_name){
			$addWhere .= " and IF(rg.referer_group_no>0, rg.referer_group_name,
							IF(LENGTH(os.referer)>0,'기타','직접입력')) = '" . $referer_name . "' ";
		}

		if($addBy!=''){
			$addBy	= ", ".$addBy;
			$ordBy	= " `date` ";
		}

		$query	= "select
					".$selVal."
					count(*)			as cnt,
					sum(settleprice)	as price,
					IF(rg.referer_group_no>0, rg.referer_group_name,
						IF(LENGTH(os.referer)>0,'기타','직접입력')) as referer_name
				from
					fm_order					as o
					LEFT JOIN fm_order_stats	as os
						on o.order_seq = os.order_seq
					LEFT JOIN fm_referer_group	as rg
						on os.referer_domain = rg.referer_group_url 
				where
					o.order_seq > 0 and
					o.deposit_yn = 'y' and o.step between '15' and '75'
					".$addWhere."
				group by referer_name " . $addBy . "
				order by " . $ordBy;

		return $this->db->query($query);
	}

	public function get_sales_category_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		$tb_type	= "C";
		if	($sc_type == 'brand'){
			$tb_type	= "B";
		}

		##
		$dates		= "month_date	as date, ";
		$addWhere	.= " and deposit_date like '".$year."%' ";
		if	($dateSel_type == 'daily'){
			$dates		= "day_date	as date, ";
			$addWhere	.= " and deposit_date like '".$year."-".str_pad($month, 2, "0", STR_PAD_LEFT)."%' ";
		}

		$query	= "select t1.total_cnt, t1.total_price,
						t2.category_code, t2.category_name, t2.date, t2.cnt, t2.price
					from
						(
							select 
								category_code, 
								sum(cnt) as total_cnt, 
								sum(price) as total_price
							from 
								fm_accumul_stats_category
							where t_type = '".$tb_type."' 
								".$addWhere."
							group by category_code
						)	as t1,
						(
							select 
								category_name, 
								category_code,
								".$dates." 
								sum(cnt) as cnt, 
								sum(price) as price
							from
								fm_accumul_stats_category
							where t_type = '".$tb_type."' 
								".$addWhere."
							group by category_code, date
						)	as t2
					where t1.category_code = t2.category_code
					order by t1.total_cnt desc, t2.date ";

		return $this->db->query($query);
	}

	public function get_member_basic_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if		($date_type == 'hour'){
			$regDate	= $year.'-'.$month.'-'.$day;
			$regDate	= date('Y-m-d', strtotime($regDate));
			$dateFld	= "SUBSTRING(regist_date, 12, 2)";
		}elseif	($date_type == 'daily'){
			$regDate	= $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT);
			$dateFld	= "SUBSTRING(regist_date, 9, 2)";
		}else{
			$regDate	= $year;
			$dateFld	= "SUBSTR(regist_date, 6, 2)";
		}

		$query	= "select ".$dateFld." as date, count(*) as cnt
					from fm_member_stats
					where regist_date like '".$regDate."%'
					group by date";

		return $this->db->query($query);
	}

	public function get_member_referer_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		##
		if		($date_type == 'month'){
			$sfld		= "SUBSTRING(ms.regist_date, 6, 2) as date ";
			$addWhere	.= " and ms.regist_date like '".$year."%' ";
			$fld		= "date, referer_name";
			$orderby	= " order by cnt desc, date";
		}elseif	($date_type == 'daily'){
			$date		= $year.'-'.$month.'-01';
			$date		= date('Y-m', strtotime($date));
			$sfld		= "SUBSTRING(ms.regist_date, 9, 2) as date ";
			$addWhere	.= " and ms.regist_date like '".$date."%' ";
			$fld		= "date, referer_name";
			$orderby	= " order by cnt desc, date";
		}elseif ($date_type == '30days'){
			$sdate		= date('Y-m-d', strtotime('-29 day'));
			$edate		= date('Y-m-d');
			$addWhere	.= " and ms.regist_date between '".$sdate."' and '".$edate."' ";
			$sfld		= "SUBSTRING(ms.regist_date, 9, 2) as date ";
			$fld		= "date";
			$orderby	= " order by ms.regist_date ";
		}elseif	($sDate && $eDate){
			$sfld		= "SUBSTRING(ms.regist_date, 9, 2) as date ";
			$addWhere	.= " and ms.regist_date between '".$sDate."' and '".$eDate."' ";
			$fld		= "date, referer_name";
			$orderby	= " order by cnt desc, date";
		}else{
			if	($date){
				$date		= date('Y-m-d', strtotime($date));
				$addWhere	.= " and ms.regist_date like '".$date."%' ";
				$fld = $sfld = "referer";
			}
		}

		if	($referer_name){
			$addWhere .= " and IF(rg.referer_group_no>0, rg.referer_group_name,
							IF(LENGTH(ms.referer)>0,'기타','직접입력')) = '" . $referer_name . "' ";
		}

		$query	= "select ".$sfld.", ms.regist_date as regist_date, count(*) as cnt,
						IF(rg.referer_group_no>0, rg.referer_group_name,
							IF(LENGTH(ms.referer)>0,'기타','직접입력')) as referer_name
					from fm_member_stats	as ms
					LEFT JOIN fm_referer_group	as rg
						on ms.referer_domain = rg.referer_group_url 
					where ms.member_stats_seq > 0
					".$addWhere."
					group by ".$fld.$orderby;

		return $this->db->query($query);
	}

	public function get_member_platform_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($month){
			$date	= date('Y-m', strtotime($year.'-'.$month.'-01'));
			$query	= "select platform, count(*) as cnt
						from fm_member_stats
						where regist_date like '".$date."%'
						group by platform";
		}else{
			$query	= "select platform, count(*) as cnt
						from fm_member_stats
						where regist_date like '".$year."%'
						group by platform";
		}

		return $this->db->query($query);
	}

	public function get_member_rute_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($month){
			$date	= date('Y-m', strtotime($year.'-'.$month.'-01'));
			$query	= "select rute, count(*) as cnt
						from fm_member
						where regist_date like '".$date."%'
						group by rute";
		}else{
			$query	= "select rute, count(*) as cnt
						from fm_member
						where regist_date like '".$year."%'
						group by rute";
		}

		return $this->db->query($query);
	}

	public function get_summary_visitor_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if		($stats_type == 'total'){
			$query	= "select sum(count_sum) as total from fm_stats_visitor_count
						where count_type = 'visit'
						and stats_date between '".$sDate."' and '".$eDate."'
						group by stats_year";
		}elseif	($stats_type == 'referer'){
			$query	= "select IF(rg.referer_group_no>0, rg.referer_group_name,
						IF(LENGTH(vr.referer)>0,'기타','직접입력')) as referer_name,
						sum(vr.count) as cnt
						from fm_stats_visitor_referer	as vr
						LEFT JOIN fm_referer_group		as rg
							on vr.referer_domain = rg.referer_group_url 
						where vr.stats_date between '".$sDate."' and '".$eDate."'
						group by referer_name
						order by cnt desc
						LIMIT 3";
		}else{
			$query	= "select stats_date, stats_month, stats_day, count_sum from fm_stats_visitor_count
						where count_type = 'visit'
						and stats_date between '".$sDate."' and '".$eDate."'
						order by stats_date";
		}

		return $this->db->query($query);
	}

	public function get_summary_member_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if		($stats_type == 'total'){
			$query	= "select count(*) total from fm_member_stats
						where regist_date between '".$sDate." 00:00:00'
						and '".$eDate." 23:59:59'";
		}elseif	($stats_type == 'referer'){
			$query	= "select IF(rg.referer_group_no>0, rg.referer_group_name,
						IF(LENGTH(ms.referer)>0,'기타','직접입력')) as referer_name,
						count(*) as cnt
						from fm_member_stats 			as ms
						LEFT JOIN fm_referer_group		as rg
							on ms.referer_domain = rg.referer_group_url 
						where ms.regist_date between '".$sDate." 00:00:00' and '".$eDate." 23:59:59'
						group by referer_name
						order by cnt desc
						LIMIT 3";
		}else{
			$query	= "select LEFT(regist_date, 10) as date, count(*) cnt from fm_member_stats
						where regist_date between '".$sDate." 00:00:00'
						and '".$eDate." 23:59:59'
						group by date order by regist_date";
		}

		return $this->db->query($query);
	}

	public function get_summary_order_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if		($stats_type == 'total'){
			$query	= "select sum(settleprice) as total
						from fm_order
						where deposit_yn = 'y' and
						step between '15' and '75' and
						deposit_date between '".$sDate." 00:00:00'
						and '".$eDate." 23:59:59' ";
		}elseif	($stats_type == 'referer'){
			$query	= "select IF(rg.referer_group_no>0, rg.referer_group_name,
						IF(LENGTH(os.referer)>0,'기타','직접입력')) as referer_name,
						sum(settleprice) as price
						from fm_order_stats 			as os
						INNER JOIN fm_order				as o
							on os.order_seq = o.order_seq
						LEFT JOIN fm_referer_group		as rg
							on os.referer_domain = rg.referer_group_url 
						where o.deposit_yn = 'y' and
						o.step between '15' and '75' and
						o.deposit_date between '".$sDate." 00:00:00' and '".$eDate." 23:59:59'
						group by referer_name
						order by price desc
						LIMIT 3";
		}else{
			$query	= "select LEFT(deposit_date, 10) as date, sum(settleprice) as price
						from fm_order
						where deposit_yn = 'y' and
						step between '25' and '75' and
						deposit_date between '".$sDate." 00:00:00'
						and '".$eDate." 23:59:59'
						group by date order by deposit_date";
		}

		return $this->db->query($query);
	}

	public function get_summary_goods_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select 
						sum((ifnull(opt.price, 0)*ifnull(opt.ea, 0)) + (ifnull(sub.price, 0)*ifnull(sub.ea, 0))) as price, 
						(select goods_name from fm_goods where goods_seq = oi.goods_seq limit 1 ) as goods_name 
					from 
						fm_order				as o 
						inner join fm_order_item		as oi	on o.order_seq = oi.order_seq 
						inner join fm_order_item_option		as opt	on oi.item_seq = opt.item_seq 
						left join fm_order_item_suboption	as sub	on oi.item_seq = sub.item_seq 
					where 
						o.deposit_date between '".$sDate." 00:00:00' and '".$eDate." 23:59:59' 
					group by oi.goods_seq order by price desc
					limit 3";
		return $this->db->query($query);
	}

	public function get_summary_category_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select 
						sum(ifnull(( select sum(price * ea) from fm_order_item_option where item_seq = oi.item_seq ), 0) + 
						ifnull(( select sum(price * ea) from fm_order_item_suboption where item_seq = oi.item_seq ), 0) ) as price, 
						(select title from fm_category where category_code = cl.category_code limit 1) as title 
					from 
						fm_order					as o
						INNER JOIN fm_order_item	as oi 	on o.order_seq = oi.order_seq 
						INNER JOIN fm_category_link	as cl 	on ( oi.goods_seq = cl.goods_seq and cl.link = 1 )
					where o.deposit_yn = 'y' and o.step between '15' and '75' and 
						o.deposit_date between '".$sDate." 00:00:00' and '".$eDate." 23:59:59' 
					group by cl.category_code order by price desc
					limit 3";
		return $this->db->query($query);
	}

	public function get_summary_brand_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select 
					(ifnull(( select sum(price * ea) from fm_order_item_option where item_seq = oi.item_seq ), 0) + 
					ifnull(( select sum(price * ea) from fm_order_item_suboption where item_seq = oi.item_seq ), 0) ) as price, 
					(select title from fm_brand where category_code = bl.category_code limit 1) as title 
				from 
					fm_order					as o
					INNER JOIN fm_order_item	as oi 	on o.order_seq = oi.order_seq 
					INNER JOIN fm_brand_link	as bl 	on ( oi.goods_seq = bl.goods_seq and bl.link = 1 )
				where o.deposit_yn = 'y' and o.step between '15' and '75' and 
					o.deposit_date between '".$sDate." 00:00:00' and '".$eDate." 23:59:59' 
				group by bl.category_code order by price desc
				limit 3";
		return $this->db->query($query);
	}

	public function get_summary_cart_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select count(*) as cnt, goods_name
					from fm_cart_stats
					where regist_date between '".$sDate." 00:00:00'
					and '".$eDate." 23:59:59'
					group by goods_name order by cnt desc
					limit 3";

		return $this->db->query($query);
	}

	public function get_summary_wish_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select count(*) as cnt, goods_name
					from fm_wish_stats
					where regist_date between '".$sDate." 00:00:00'
					and '".$eDate." 23:59:59'
					group by goods_name order by cnt desc
					limit 3";

		return $this->db->query($query);
	}

	public function get_summary_keyword_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select count(*) as cnt, keyword
					from fm_search_stats
					where regist_date between '".$sDate." 00:00:00'
					and '".$eDate." 23:59:59'
					group by keyword order by cnt desc
					limit 3";

		return $this->db->query($query);
	}

	public function get_current_cart_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($type == 'brand'){
			$addFrom	= "INNER JOIN fm_brand_link		as bl
							on ( c.goods_seq = bl.goods_seq
								and bl.category_code = '".$category_code."' ) ";
		}elseif	($type == 'category'){
			$addFrom	= "INNER JOIN fm_category_link		as cl
							on ( c.goods_seq = cl.goods_seq
								and cl.category_code = '".$category_code."' ) ";
		}

		if	($get_type == 'total'){
			$query	= "select count(*) as cnt
						from fm_cart	as c
							INNER JOIN fm_goods	as g
								on c.goods_seq = g.goods_seq
							".$addFrom."
						order by cnt desc ";
		}else{
			$query	= "select count(*) as cnt, g.goods_name, g.goods_seq
						from fm_cart	as c
							INNER JOIN fm_goods	as g
								on c.goods_seq = g.goods_seq
							".$addFrom."
						group by c.goods_seq
						order by cnt desc
						limit 3";
		}

		return $this->db->query($query);
	}

	public function get_current_wish_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($type == 'brand'){
			$addFrom	= "INNER JOIN fm_brand_link		as bl
							on ( gw.goods_seq = bl.goods_seq
								and bl.category_code = '".$category_code."' ) ";
		}elseif	($type == 'category'){
			$addFrom	= "INNER JOIN fm_category_link		as cl
							on ( gw.goods_seq = cl.goods_seq
								and cl.category_code = '".$category_code."' ) ";
		}

		if	($get_type == 'total'){
			$query	= "select count(*) as cnt
						from fm_goods_wish	as gw
							INNER JOIN fm_goods	as g
								on gw.goods_seq = g.goods_seq
							".$addFrom."
						order by cnt desc ";
		}else{
			$query	= "select count(*) as cnt, g.goods_name, g.goods_seq
						from fm_goods_wish	as gw
							INNER JOIN fm_goods	as g
								on gw.goods_seq = g.goods_seq
							".$addFrom."
						group by gw.goods_seq
						order by cnt desc
						limit 3";
		}

		return $this->db->query($query);
	}

	public function get_current_like_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($type == 'brand'){
			$addFrom	= "INNER JOIN fm_brand_link		as bl
							on ( fbl.goods_seq = bl.goods_seq
								and bl.category_code = '".$category_code."' ) ";
		}elseif	($type == 'category'){
			$addFrom	= "INNER JOIN fm_category_link		as cl
							on ( fbl.goods_seq = cl.goods_seq
								and cl.category_code = '".$category_code."' ) ";
		}

		if	($get_type == 'total'){
			$query	= "select count(*) as cnt
						from fm_goods_fblike	as fbl
							INNER JOIN fm_goods	as g
								on fbl.goods_seq = g.goods_seq
							".$addFrom."
						order by cnt desc ";
		}else{
			$query	= "select count(*) as cnt, g.goods_name, g.goods_seq
						from fm_goods_fblike	as fbl
							INNER JOIN fm_goods	as g
								on fbl.goods_seq = g.goods_seq
							".$addFrom."
						group by fbl.goods_seq
						order by cnt desc
						limit 3";
		}

		return $this->db->query($query);
	}

	public function get_current_restock_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		if	($catenbrand == 'brand'){
			$addFrom	= "INNER JOIN fm_brand_link		as bl
							on ( rsn.goods_seq = bl.goods_seq
								and bl.category_code = '".$category_code."' ) ";
		}elseif	($type == 'category'){
			$addFrom	= "INNER JOIN fm_category_link		as cl
							on ( rsn.goods_seq = cl.goods_seq
								and cl.category_code = '".$category_code."' ) ";
		}

		if	($get_type == 'total'){
			$query	= "select count(*) as cnt
						from fm_goods_restock_notify 	as rsn
							INNER JOIN fm_goods	as g
								on rsn.goods_seq = g.goods_seq
							".$addFrom."
						where rsn.notify_status = 'none' ";
		}else{
			$query	= "select count(*) as cnt, g.goods_name, g.goods_seq
						from fm_goods_restock_notify 	as rsn
							INNER JOIN fm_goods	as g
								on rsn.goods_seq = g.goods_seq
							".$addFrom."
						where rsn.notify_status = 'none'
						group by rsn.goods_seq
						order by cnt desc
						limit 3";
		}

		return $this->db->query($query);
	}

	public function get_statistic_order_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		switch($date_term){
			case 'today':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d')." 00:00:00'
								and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '7days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-6 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '10days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-9 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '30days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-29 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			default:
			case 'yesterday':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-1 day'))."
								00:00:00' and '".date('Y-m-d', strtotime('-1 day'))." 23:59:59' ";
			break;
		}

		if	($goods_seq){
			$addOn	= " and oi.goods_seq = '".$goods_seq."' ";
		}


		if	($q_type == 'rank'){
			$query	= "select o.order_seq, (oio.price + oiso.price) as price, 
							g.goods_seq, g.goods_name
						from fm_order as o
							INNER JOIN fm_order_item as oi
								on ( o.order_seq = oi.order_seq )
							INNER JOIN fm_goods as g
								on ( oi.goods_seq = g.goods_seq )
							INNER JOIN (select item_seq, item_option_seq as option_seq, sum(price*ea) as price from fm_order_item_option group by item_seq)  as oio on ( oi.item_seq = oio.item_seq )
							LEFT JOIN (select option_seq, sum(price*ea) price
									from fm_order_item_suboption group by option_seq)  as oiso
								on ( oio.option_seq = oiso.option_seq )
						where
							o.deposit_yn = 'y' and
							o.step between '15' and '75'
							".$addWhere."
						group by oi.goods_seq
						order by price desc
						limit 2";
		}else{
			$query	= "select ".$keyFld." as dates, o.order_seq, o.settleprice as settleprice,
							oio.price as price, g.goods_seq, g.goods_name,
							IF(rg.referer_group_no>0, rg.referer_group_name,
								IF(LENGTH(os.referer)>0,'기타','직접입력')) as referer_name
						from fm_order as o
							INNER JOIN fm_order_item as oi
								on ( o.order_seq = oi.order_seq ".$addOn." )
							INNER JOIN (select item_seq, sum(price*ea) as price from fm_order_item_option group by item_seq)  as oio on ( oi.item_seq = oio.item_seq )
							INNER JOIN fm_goods as g
								on ( oi.goods_seq = g.goods_seq )
							LEFT JOIN fm_order_stats as os
								on ( o.order_seq = os.order_seq )
							LEFT JOIN fm_referer_group as rg
								on os.referer_domain = rg.referer_group_url 
						where
							o.deposit_yn = 'y' and
							o.step between '15' and '75'
							".$addWhere."
						group by o.order_seq, g.goods_seq, dates";
		}

		return $this->db->query($query);
	}


	public function get_statistic_category_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		switch($date_term){
			case 'today':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d')." 00:00:00'
								and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '7days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-6 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '30days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-29 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			default:
			case 'yesterday':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-1 day'))."
								00:00:00' and '".date('Y-m-d', strtotime('-1 day'))." 23:59:59' ";
			break;
		}

		if	($catenbrand == 'brand'){
			$addFrom	= "INNER JOIN fm_brand_link		as lk
								on ( oi.goods_seq = lk.goods_seq and lk.link = 1 )
							INNER JOIN fm_brand			as c
								on lk.category_code = c.category_code ";

			$addFrom2	= "INNER JOIN fm_brand_link		as lk
							on ( oi.goods_seq = lk.goods_seq
								and lk.category_code in ('".$category_code."', '".$first."' ) )
							INNER JOIN fm_brand			as c
								on lk.category_code = c.category_code ";
		}else{
			$addFrom	= "INNER JOIN fm_category_link	as lk
								on ( oi.goods_seq = lk.goods_seq and lk.link = 1 )
							INNER JOIN fm_category		as c
								on lk.category_code = c.category_code ";

			$addFrom2	= "INNER JOIN fm_category_link	as lk
							on ( oi.goods_seq = lk.goods_seq
								and lk.category_code in ('".$category_code."', '".$first."' ) )
							INNER JOIN fm_category		as c
								on lk.category_code = c.category_code ";
		}

		if	($q_type == 'first'){
			$addWhere	.= " and lk.category_code != '".$category_code."' ";
			$query		= "select c.category_code
							from fm_order as o
								INNER JOIN fm_order_item as oi
									on  o.order_seq = oi.order_seq
								INNER JOIN fm_order_item_option as oio
									on oi.item_seq = oio.item_seq
								".$addFrom."
							where
								o.deposit_yn = 'y' and
								o.step between '15' and '75'
								".$addWhere."
							group by c.category_code
							order by price desc
							limit 1";
		}else{
			$query	= "select ".$keyFld." as dates, c.title, sum(oio.price) as price
						from fm_order as o
							INNER JOIN fm_order_item as oi
								on  o.order_seq = oi.order_seq
							INNER JOIN fm_order_item_option as oio
								on ( oi.item_seq = oio.item_seq )
							".$addFrom2."
						where
							o.deposit_yn = 'y' and
							o.step between '15' and '75'
							".$addWhere."
						group by c.category_code, dates
						order by price desc, title";
		}

		return $this->db->query($query);
	}

	public function get_statistic_referer_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		switch($date_term){
			case 'today':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d')." 00:00:00'
								and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '7days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-6 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '30days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-29 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			default:
			case 'yesterday':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-1 day'))."
								00:00:00' and '".date('Y-m-d', strtotime('-1 day'))." 23:59:59' ";
			break;
		}

		$query	= "select ".$keyFld." as dates, sum(oio.price) as price,
						IF(rg.referer_group_no>0, rg.referer_group_name,
						IF(LENGTH(os.referer)>0,'기타','직접입력')) as referer_name
					from fm_order as o
						INNER JOIN fm_order_item as oi
							on ( o.order_seq = oi.order_seq and oi.goods_seq = '".$goods_seq."' )
						INNER JOIN fm_order_item_option as oio
							on ( oi.item_seq = oio.item_seq )
						INNER JOIN fm_order_stats as os
							on ( o.order_seq = os.order_seq )
						LEFT JOIN fm_referer_group as rg
							on os.referer_domain = rg.referer_group_url 
					where
						o.deposit_yn = 'y' and
						o.step between '15' and '75'
						".$addWhere."
					group by rg.referer_group_cd , dates";

		return $this->db->query($query);
	}


	public function get_statistic_etc_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		switch($date_term){
			case 'today':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d')." 00:00:00'
								and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '7days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-6 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '30days':
				$keyFld		= "SUBSTRING(o.deposit_date, 9, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-29 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			default:
			case 'yesterday':
				$keyFld		= "SUBSTRING(o.deposit_date, 12, 2)";
				$addWhere	= " and o.deposit_date between '".date('Y-m-d', strtotime('-1 day'))."
								00:00:00' and '".date('Y-m-d', strtotime('-1 day'))." 23:59:59' ";
			break;
		}

		$query	= "select
						case
							when os.buyer_age < 20 then '10대 이하'
							when os.buyer_age < 30 then '20대'
							when os.buyer_age < 40 then '30대'
							when os.buyer_age < 50 then '40대'
							when os.buyer_age < 60 then '50대'
							when os.buyer_age >= 60 then '60대 이상'
						end as buyer_age,
						case
							when os.buyer_sex = 'male' then '남'
							when os.buyer_sex = 'female' then '여'
						end as buyer_sex,
						os.buyer_area,
						count(*) as cnt
					from fm_order as o
						INNER JOIN fm_order_item as oi
							on ( o.order_seq = oi.order_seq and oi.goods_seq = '".$goods_seq."' )
						INNER JOIN fm_order_stats as os
							on ( o.order_seq = os.order_seq )
					where
						o.deposit_yn = 'y' and
						o.step between '15' and '75'
						".$addWhere."
					group by buyer_area, buyer_age, buyer_sex";

		return $this->db->query($query);
	}

	public function get_statistic_cart_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		switch($date_term){
			case 'today':
				$keyFld		= "SUBSTRING(regist_date, 12, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d')." 00:00:00'
								and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '7days':
				$keyFld		= "SUBSTRING(regist_date, 9, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-6 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '10days':
				$keyFld		= "SUBSTRING(regist_date, 9, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-9 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '30days':
				$keyFld		= "SUBSTRING(regist_date, 9, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-29 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			default:
			case 'yesterday':
				$keyFld		= "SUBSTRING(regist_date, 12, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-1 day'))."
								00:00:00' and '".date('Y-m-d', strtotime('-1 day'))." 23:59:59' ";
			break;
		}

		if	($q_type == 'rank'){
			$query	= "select ".$keyFld." as dates, count(*) cnt,
						goods_seq, goods_name
						from fm_cart_stats
						where goods_seq > 0 ".$addWhere."
						group by goods_seq
						order by cnt desc
						limit 2";
		}else{
			$query	= "select ".$keyFld." as dates, count(*) cnt
						from fm_cart_stats
						where goods_seq = '".$goods_seq."' ".$addWhere."
						group by dates
						order by regist_date";
		}

		return $this->db->query($query);
	}

	public function get_statistic_wish_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		switch($date_term){
			case 'today':
				$keyFld		= "SUBSTRING(regist_date, 12, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d')." 00:00:00'
								and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '7days':
				$keyFld		= "SUBSTRING(regist_date, 9, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-6 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '10days':
				$keyFld		= "SUBSTRING(regist_date, 9, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-9 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			case '30days':
				$keyFld		= "SUBSTRING(regist_date, 9, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-29 day'))."
								00:00:00' and '".date('Y-m-d')." 23:59:59' ";
			break;
			default:
			case 'yesterday':
				$keyFld		= "SUBSTRING(regist_date, 12, 2)";
				$addWhere	= " and regist_date between '".date('Y-m-d', strtotime('-1 day'))."
								00:00:00' and '".date('Y-m-d', strtotime('-1 day'))." 23:59:59' ";
			break;
		}

		if	($q_type == 'rank'){
			$query	= "select ".$keyFld." as dates, count(*) cnt,
						goods_seq, goods_name
						from fm_wish_stats
						where goods_seq > 0 ".$addWhere."
						group by goods_seq
						order by cnt desc
						limit 2";
		}else{
			$query	= "select ".$keyFld." as dates, count(*) cnt
						from fm_wish_stats
						where goods_seq = '".$goods_seq."' ".$addWhere."
						group by dates
						order by regist_date";
		}

		return $this->db->query($query);
	}

	public function get_statistic_visitor_stats($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$query	= "select stats_year, stats_month, stats_day,
						IF(rg.referer_group_no>0, rg.referer_group_name,
							IF(LENGTH(vr.referer)>0,'기타','직접입력')) as referer_name,
						sum(vr.count) as cnt,
						(select count_sum from fm_stats_visitor_count where stats_date = vr.stats_date and count_type = 'visit' limit 1 ) as vcnt
					from
						fm_stats_visitor_referer		as vr
						LEFT JOIN fm_referer_group		as rg
							on vr.referer_domain = rg.referer_group_url 
					where vr.stats_date between '".$sDate."' and '".$eDate."'
					group by referer_name, stats_day
					order by stats_date";

		return $this->db->query($query);
	}

	public function get_main_statistic_data(){

		$rank_array				= array('first', 'second', 'third');

		## 매출추이
		$params['date_term']	= '10days';
		$query					= $this->get_statistic_order_stats($params);
		$order					= $query->result_array();

		if	($order){
			$useOrderSeq	= array();
			foreach($order as $k => $data){

				$prices[$data['goods_seq']]			+= $data['price'];
				$names[$data['goods_seq']]			= $data['goods_name'];
				if	(!in_array($data['order_seq'], $useOrderSeq)){
					$orderChart[$data['dates']]		+= $data['settleprice'];
					$useOrderSeq[]					= $data['order_seq'];
				}

				// 오늘 데이터
				if	($data['dates'] == date('d')){
					$Tprices[$data['goods_seq']]	+= $data['price'];
					$Tnames[$data['goods_seq']]		= $data['goods_name'];
				}

				$oRefererChart[$data['referer_name']][$data['dates']]	+= $data['settleprice'];
			}
		}

		## 판매상품 순위 데이터
		arsort($prices);
		$r		= 0;
		$stat['rank'][0]['order']		= array();
		$stat['rank'][1]['order']		= array();
		foreach($prices as $goods_seq => $price){
			if	($r < 2){
				$stat['rank'][$r]['order']['price']			= $price;
				$stat['rank'][$r]['order']['goods_name']	= $names[$goods_seq];
				$stat['rank'][$r]['order']['goods_seq']		= $goods_seq;
			}
			$r++;
		}
		if	($Tprices){
			arsort($Tprices);
			$seq_arr	= array_keys($Tprices);
			$Tgoods_seq	= $seq_arr[0];
		}
		$stat['rank'][2]['order']['price']			= $Tprices[$Tgoods_seq];
		$stat['rank'][2]['order']['goods_name']		= $Tnames[$Tgoods_seq];
		$stat['rank'][2]['order']['goods_seq']		= $Tgoods_seq;


		## 장바구니
		$params['q_type']		= 'rank';
		$params['date_term']	= '10days';
		$query		= $this->get_statistic_cart_stats($params);
		$cart		= $query->result_array();
		$stat['rank'][0]['cart']		= array();
		$stat['rank'][1]['cart']		= array();
		for ($r = 0; $r < 2; $r++){
			$stat['rank'][$r]['cart']['cnt']		= $cart[$r]['cnt'];
			$stat['rank'][$r]['cart']['goods_name']	= $cart[$r]['goods_name'];
			$stat['rank'][$r]['cart']['goods_seq']	= $cart[$r]['goods_seq'];
		}
		$params['q_type']		= 'rank';
		$params['date_term']	= 'today';
		$query		= $this->get_statistic_cart_stats($params);
		$cart		= $query->result_array();
		$stat['rank'][2]['cart']['cnt']			= $cart[0]['cnt'];
		$stat['rank'][2]['cart']['goods_name']	= $cart[0]['goods_name'];
		$stat['rank'][2]['cart']['goods_seq']	= $cart[0]['goods_seq'];



		## 위시리스트
		$params['get_type']		= 'rank';
		$params['date_term']	= '10days';
		$query		= $this->get_statistic_wish_stats($params);
		$wish		= $query->result_array();
		$stat['rank'][0]['wish']		= array();
		$stat['rank'][1]['wish']		= array();
		for ($r = 0; $r < 2; $r++){
			$stat['rank'][$r]['wish']['cnt']		= $wish[$r]['cnt'];
			$stat['rank'][$r]['wish']['goods_name']	= $wish[$r]['goods_name'];
			$stat['rank'][$r]['wish']['goods_seq']	= $wish[$r]['goods_seq'];
		}
		$params['q_type']		= 'rank';
		$params['date_term']	= 'today';
		$query		= $this->get_statistic_wish_stats($params);
		$wish		= $query->result_array();
		$stat['rank'][2]['wish']['cnt']			= $wish[0]['cnt'];
		$stat['rank'][2]['wish']['goods_name']	= $wish[0]['goods_name'];
		$stat['rank'][2]['wish']['goods_seq']	= $wish[0]['goods_seq'];



		## 검색어
		$params['sdate']	= date('Y-m-d', strtotime('-9 day'));
		$params['edate']	= date('Y-m-d');
		$query		= $this->get_goods_search_stats($params);
		$keyword	= $query->result_array();
		$stat['rank'][0]['keyword']		= array();
		$stat['rank'][1]['keyword']		= array();
		for ($r = 0; $r < 2; $r++){
			$stat['rank'][$r]['keyword']['cnt']		= $keyword[$r]['keyword_cnt'];
			$stat['rank'][$r]['keyword']['keyword']	= $keyword[$r]['keyword'];
		}
		$params['sdate']	= date('Y-m-d');
		$params['edate']	= date('Y-m-d');
		$query		= $this->get_goods_search_stats($params);
		$keyword	= $query->result_array();
		$stat['rank'][2]['keyword']['cnt']			= $keyword[0]['keyword_cnt'];
		$stat['rank'][2]['keyword']['keyword']		= $keyword[0]['keyword'];


		## 회원
		$params['sDate']		= date('Y-m-d', strtotime('-9 day'));
		$params['eDate']		= date('Y-m-d');
		$query					= $this->get_member_referer_stats($params);
		$member					= $query->result_array();
		if	($member){
			foreach($member as $k => $data){
				if	(date('Ymd', strtotime($data['regist_date'])) >= date('Ymd', strtotime('-9 day'))){
					$memberChart[$data['date']]								+= $data['cnt'];
					$mRefererChart[$data['referer_name']][$data['date']]	+= $data['cnt'];
				}
			}
		}

		## 방문
		$params['sDate']	= date('Y-m-d', strtotime('-9 day'));
		$params['eDate']	= date('Y-m-d');
		$query				= $this->get_statistic_visitor_stats($params);
		$visitor			= $query->result_array();
		if	($visitor){
			foreach($visitor as $k => $data){
				$visitorChart[$data['stats_day']]							= $data['vcnt'];
				$vRefererChart[$data['referer_name']][$data['stats_day']]	+= $data['cnt'];
			}
		}



		## Chart 데이터
		$oReferer_arr	= array_keys($oRefererChart);
		$oReferer_cnt	= count($oReferer_arr);
		$mReferer_arr	= array_keys($mRefererChart);
		$mReferer_cnt	= count($mReferer_arr);
		$vReferer_arr	= array_keys($vRefererChart);
		$vReferer_cnt	= count($vReferer_arr);

		$start_time		= strtotime('-9 day');
		$nDate			= date('Y-m-d', $start_time);
		while (date('Y-m-d', strtotime('+1 day')) != $nDate){
			$addDay++;
			$day			= date('d', strtotime($nDate));
			$orderPrice		= ($orderChart[$day]) ? floor($orderChart[$day]/1000) : 0;
			$memberCnt		= ($memberChart[$day]) ? $memberChart[$day] : 0;
			$visitorCnt		= ($visitorChart[$day]) ? $visitorChart[$day] : 0;

			// 매출 유입처
			if	($oReferer_cnt > 0){
				for ($or = 0; $or < $oReferer_cnt; $or++){
					$referer		= $oReferer_arr[$or];
					$oRefererPrice	= ($oRefererChart[$referer][$day]) ? floor($oRefererChart[$referer][$day]/1000) : 0;

					$dataForChart['매출유입경로'][$referer][]	= array($day.'일', $oRefererPrice);

					$maxValue['매출유입경로']	= ($maxValue['매출유입경로'] < $oRefererPrice)	? $oRefererPrice	: $maxValue['매출유입경로'];
				}
			}else{
				$dataForChart['매출유입경로']['no_data'][]	= array($day.'일', 0);
			}

				// 회원 유입처
			if	($mReferer_cnt > 0){
				for ($mr = 0; $mr < $mReferer_cnt; $mr++){
					$referer		= $mReferer_arr[$mr];
					$mRefererCnt	= ($mRefererChart[$referer][$day]) ? $mRefererChart[$referer][$day] : 0;

					$dataForChart['회원유입경로'][$referer][]	= array($day.'일', $mRefererCnt);

					$maxValue['회원유입경로']	= ($maxValue['회원유입경로'] < $mRefererCnt)	? $mRefererCnt	: $maxValue['회원유입경로'];
				}
			}else{
				$dataForChart['회원유입경로']['no_data'][]	= array($day.'일', 0);
			}

			// 방문 유입처
			if	($vReferer_cnt > 0){
				for ($vr = 0; $vr < $vReferer_cnt; $vr++){
					$referer		= $vReferer_arr[$vr];
					$vRefererCnt	= ($vRefererChart[$referer][$day]) ? $vRefererChart[$referer][$day] : 0;

					$dataForChart['방문유입경로'][$referer][]	= array($day.'일', $vRefererCnt);

					$maxValue['방문유입경로']	= ($maxValue['방문유입경로'] < $vRefererCnt)	? $vRefererCnt	: $maxValue['방문유입경로'];
				}
			}else{
				$dataForChart['방문유입경로']['no_data'][]	= array($day.'일', 0);
			}

			$dataForChart['매출'][]	= array($day.'일', $orderPrice);
			$dataForChart['회원'][]	= array($day.'일', $memberCnt);
			$dataForChart['방문'][]	= array($day.'일', $visitorCnt);

			$maxValue['매출']	= ($maxValue['매출'] < $orderPrice)	? $orderPrice	: $maxValue['매출'];
			$maxValue['회원']	= ($maxValue['회원'] < $memberCnt)	? $memberCnt	: $maxValue['회원'];
			$maxValue['방문']	= ($maxValue['방문'] < $visitorCnt)	? $visitorCnt	: $maxValue['방문'];

			$nDate		= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
		}

		$this->dataForChart	= $dataForChart;
		$this->maxValue		= $maxValue;
		$this->stat			= $stat;
		$this->rank_array	= $rank_array;
	}

	public function get_referer_grouplist(){
		$query	= "select referer_group_cd, referer_group_name
					from fm_referer_group group by referer_group_cd";
		$query	= $this->db->query($query);

		$return = $query->result_array();
		if ($return) {
			$cnt = count($return);
			for ($i=0;$i<$cnt;$i++) {
				if ($return[$i]['referer_group_name']=="다움검색_광고") {
					$return[$i]['referer_group_name'] = "다음검색_광고";
				}
			}
		}
		return $return;
	}

	/* 상품 일별 구매 통계 데이터 :: 2014-08-04 lwh */
	public function get_daily_sales_stats($sdate='', $edate=''){
		
		if($sdate=='' || $edate==''){
			$sdate	= date('Y-m-d');
			$edate	= date('Y-m-d');
		}

		$addWhere	= " and a.deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";

		$sql = "
			select
				b.goods_seq,
				b.goods_code,
				b.goods_name as order_goods_name,
				c.supply_price as supply_price,
				c.consumer_price as consumer_price,
				c.price as price,
				sum(c.ea) as ea,
				a.shipping_cost,
				a.emoney,
				a.cash,
				a.enuri,
				b.shipping_policy as shipping_policy,
				sum(c.coupon_sale) as coupon_sale,
				sum(c.member_sale*c.ea) as member_sale,
				sum(c.fblike_sale) as fblike_sale,
				sum(c.mobile_sale) as mobile_sale,
				sum(c.promotion_code_sale) as promotion_code_sale,
				sum(c.referer_sale) as referer_sale,
				c.title1 as title1,
				c.option1 as option1,
				c.title2 as title2,
				c.option2 as option2,
				c.title3 as title3,
				c.option3 as option3,
				c.title4 as title4,
				c.option4 as option4,
				c.title5 as title5,
				c.option5 as option5,
				date_format(a.deposit_date,'%Y-%m-%d') deposit_ymd,
				(select group_concat(category_code) from fm_category_link where goods_seq=b.goods_seq group by goods_seq) as category_codes,
				(select group_concat(category_code) from fm_brand_link where goods_seq=b.goods_seq group by goods_seq) as brand_codes
			from fm_order as a
				inner join fm_order_item b on a.order_seq=b.order_seq
				inner join fm_order_item_option c on c.item_seq = b.item_seq
			where a.deposit_yn='y' and
				a.step between '15' and '85'
				".$addWhere."
			group by deposit_ymd, b.goods_seq, c.price,
				concat_ws('',c.title1,c.option1,c.title2,c.option2,c.title3,c.option3,c.title4,c.option4,c.title5,c.option5)
		union
			select
				b.goods_seq,
				b.goods_code,
				b.goods_name as order_goods_name,
				c.supply_price as supply_price,
				c.consumer_price as consumer_price,
				c.price as price,
				sum(c.ea) as ea,
				a.shipping_cost,
				a.emoney,
				a.cash,
				a.enuri,
				b.shipping_policy as shipping_policy,
				0 as coupon_sale,
				sum(c.member_sale*c.ea) as member_sale,
				0 as fblike_sale,
				0 as mobile_sale,
				0 as promotion_code_sale,
				0 as referer_sale,
				c.title as title1,
				c.suboption as option1,
				'' as title2,
				'' as option2,
				'' as title3,
				'' as option3,
				'' as title4,
				'' as option4,
				'' as title5,
				'' as option5,
				date_format(a.deposit_date,'%Y-%m-%d') deposit_ymd,
				(select group_concat(category_code) from fm_category_link where goods_seq=b.goods_seq group by goods_seq) as category_codes,
				(select group_concat(category_code) from fm_brand_link where goods_seq=b.goods_seq group by goods_seq) as brand_codes
			from fm_order as a
				inner join fm_order_item b on a.order_seq=b.order_seq
				inner join fm_order_item_suboption c on c.item_seq = b.item_seq
			where a.deposit_yn='y' and
				a.step between '15' and '85'
				".$addWhere."
			group by deposit_ymd,b.goods_seq,c.price,
				concat_ws('',c.title,c.suboption)
			";

		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	/* 상품 일별 구매 집계 데이터 삽입 :: 2014-08-04 lwh */
	public function set_accumul_stats_sales($data){
		$this->db->insert("fm_accumul_stats_sales",$data);
	}

	/* 상품 일별 구매 집계 데이터 삭제 :: 2014-08-04 lwh */
	public function delete_accumul_stats_sales($sdate='', $edate=''){
		if($sdate && $edate){
			$sql = "delete from fm_accumul_stats_sales where deposit_ymd between '".$sdate."' and '".$edate."'";

			$query	= $this->db->query($sql);
		}
	}

	/* 상품 일별 구매 집계 데이터 페이징 :: 2014-08-06 lwh */
	public function get_sales_goods_daily_pagin($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$addWhere	= " and a.deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";
		if	($category_code)
			$addWhere2	.= " and find_in_set('".$category_code."', category_codes)";

		if	($brands_code)
			$addWhere2	.= " and find_in_set('".$brands_code."', brand_codes)";

		if	($keyword)
			$addWhere2	.= " and concat(goods_seq,ifnull(goods_code,''),order_goods_name) like '%".addslashes($keyword)."%'";

		$orderBy	= " order by ".$sort.", goods_seq asc";
		$pagein		= " limit ".$start_page.", ".$end_page." ";

		$sql	= "select *, price*ea as goods_price
				from fm_accumul_stats_sales 
				where goods_seq > 0 
				and deposit_ymd between '".$sdate."'  and '".$edate."' ".$addWhere2."
				".$orderBy." ".$pagein;
		$query	= $this->db->query($sql);
		return $query->result_array();
	}

	/* 구매통계 매출 통계 데이터 :: 2014-08-07 lwh */
	public function get_sales_mdstats($sdate='', $edate=''){
		
		if($sdate=='' || $edate==''){
			$sdate	= date('Y-m-d');
			$edate	= date('Y-m-d');
		}

		$addWhere	= " and a.deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";

		$sql = "
		select
			a.order_seq as order_seq
			,year(a.deposit_date) as stats_year
			,month(a.deposit_date) as stats_month
			,day(a.deposit_date) as stats_day
			,sum(a.settleprice) as settleprice_sum
			,sum(a.enuri) as enuri_sum
			,sum(a.emoney) as emoney_use_sum
			,sum(a.cash) as cash_use_sum
			,sum(a.coupon_sale) as shipping_coupon_sale_sum
			,(select sum(shipping_promotion_code_sale) from fm_order_shipping where order_seq = a.order_seq) as shipping_promotion_code_sale_sum
			,sum(a.international_cost+a.shipping_cost) as shipping_cost_sum
			,count(*) as count_sum
			,(select sum(ori_price*ea) from fm_order_item_option where order_seq=a.order_seq) as option_ori_price_sum
			,(select sum(ifnull(coupon_sale,0)) from fm_order_item_option where order_seq=a.order_seq) as option_coupon_sale_sum
			,(select sum(ifnull(fblike_sale,0)) from fm_order_item_option where order_seq=a.order_seq) as option_fblike_sale_sum
			,(select sum(ifnull(mobile_sale,0)) from fm_order_item_option where order_seq=a.order_seq) as option_mobile_sale_sum
			,(select sum(ifnull(promotion_code_sale,0)) from fm_order_item_option where order_seq=a.order_seq) as option_promotion_code_sale_sum
			,(select sum(ifnull(member_sale,0)*ea) from fm_order_item_option where order_seq=a.order_seq) as option_member_sale_sum
			,(select sum(ifnull(member_sale,0)*ea) from fm_order_item_suboption where order_seq=a.order_seq) as suboption_member_sale_sum
			,(select sum(ifnull(referer_sale,0)*ea) from fm_order_item_option where order_seq=a.order_seq) as option_referer_sale_sum
			,(select sum(goods_shipping_cost) from fm_order_item where order_seq=a.order_seq) as goods_shipping_cost_sum
			,(select sum(supply_price*ea) from fm_order_item_option where order_seq=a.order_seq) as option_supply_price_sum
			,(select sum(supply_price*ea) from fm_order_item_suboption where order_seq=a.order_seq) as suboption_supply_price_sum
			,a.deposit_date
			,a.sitetype
		from fm_order as a
		where a.deposit_yn='y' and
			a.step between '15' and '85'
			" . $addWhere . "
		group by a.order_seq
		";

		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	/* 구매통계 매출 집계 데이터 삽입 :: 2014-08-07 lwh */
	public function set_accumul_sales_mdstats($data){
		$this->db->insert("fm_accumul_sales_mdstats",$data);
	}

	/* 구매통계 매출 집계 데이터 삭제 :: 2014-08-07 lwh */
	public function delete_accumul_sales_mdstats($sdate='', $edate=''){
		if($sdate && $edate){
			$sql = "delete from fm_accumul_sales_mdstats where deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59'";

			$query	= $this->db->query($sql);
		}
	}

	/* 구매통계 환불 통계 데이터 :: 2014-08-08 lwh */
	public function get_sales_refund($sdate='', $edate=''){
		
		if($sdate=='' || $edate==''){
			$sdate	= date('Y-m-d');
			$edate	= date('Y-m-d');
		}

		$addWhere	= " and a.refund_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";

		$sql = "
		SELECT 
			a.order_seq AS order_seq, 
			year( a.refund_date ) AS stats_year, 
			month( a.refund_date ) AS stats_month, 
			day( a.refund_date ) AS stats_day, 
			sum( a.refund_price ) AS refund_price_sum, 
			sum( a.refund_emoney ) AS refund_emoney_sum, 
			sum( a.refund_cash ) AS refund_cash_sum, 
			count( * ) AS refund_count_sum, 
			(
				SELECT sum( sb.supply_price * sa.ea ) 
				FROM fm_order_refund_item sa, fm_order_item_option sb
				WHERE sa.refund_code = a.refund_code
				AND sa.option_seq >0
				AND sa.option_seq = sb.item_option_seq
			) AS option_supply_price_sum, 
			(
				SELECT sum( sb.supply_price * sa.ea ) 
				FROM fm_order_refund_item sa, fm_order_item_suboption sb
				WHERE sa.refund_code = a.refund_code
				AND sa.suboption_seq >0
				AND sa.suboption_seq = sb.item_suboption_seq
			) AS suboption_supply_price_sum,
			sum( if( a.refund_type = 'cancel_payment', a.refund_price, 0 ) ) AS cancel_price_sum, 
			sum( if( a.refund_type = 'cancel_payment', 1, 0 ) ) AS cancel_count_sum, 
			sum( if( a.refund_type = 'return', a.refund_price, 0 ) ) AS return_price_sum, 
			sum( if( a.refund_type = 'return', 1, 0 ) ) AS return_count_sum, 
			a.refund_date,
			b.sitetype
		FROM fm_order_refund AS a
		LEFT JOIN fm_order AS b ON a.order_seq = b.order_seq
		WHERE a.order_seq >0
			AND b.step BETWEEN '15'	AND '85'
			" . $addWhere . "
		GROUP BY a.refund_code
		";

		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	/* 구매통계 환불 집계 데이터 삽입 :: 2014-08-08 lwh */
	public function set_accumul_sales_refund($data){
		$this->db->insert("fm_accumul_sales_refund",$data);
	}

	/* 구매통계 환불 집계 데이터 삭제 :: 2014-08-08 lwh */
	public function delete_accumul_sales_refund($sdate='', $edate=''){
		if($sdate && $edate){
			$sql = "delete from fm_accumul_sales_refund where refund_date between '".$sdate."' and '".$edate."'";

			$query	= $this->db->query($sql);
		}
	}

	/* 구매통계 카테고리/브랜드 통계 데이터 :: 2014-08-11 lwh */
	public function get_sales_category($type='C', $sdate='', $edate=''){
		
		if($sdate=='' || $edate==''){
			$sdate	= date('Y-m-d');
			$edate	= date('Y-m-d');
		}

		if($type == 'C'){
			$tb_cl_name	= 'fm_category_link';
			$tb_c_name	= 'fm_category';
		}else{
			$tb_cl_name	= 'fm_brand_link';
			$tb_c_name	= 'fm_brand';
		}

		$addWhere	= " and o.deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59' ";

		$sql = "
		select 
			c.title as category_name, 
			c.category_code as category_code,
			SUBSTRING(o.deposit_date, 6, 2)	as month_date, 
			SUBSTRING(o.deposit_date, 9, 2) as day_date,  
			count(*) as cnt, 
			sum(oio.price) as price,
			o.deposit_date
		from 
			fm_order as o, 
			fm_order_item as oi INNER JOIN fm_order_item_option as oio
			on ( oi.item_seq = oio.item_seq ),
			".$tb_cl_name."	as cl, 
			".$tb_c_name."	as c
		where 
			o.order_seq = oi.order_seq and
			oi.item_seq = oio.item_seq and
			oi.goods_seq = cl.goods_seq and
			cl.category_code = c.category_code and
			cl.link = 1 and o.deposit_yn = 'y' and
			o.step between '15' and '75' 
			" . $addWhere . "
		group by cl.category_code, month_date, day_date
		order by month_date, day_date
		";

		$query	= $this->db->query($sql);

		return $query->result_array();
	}

	/* 구매통계 환불 집계 데이터 삽입 :: 2014-08-08 lwh */
	public function set_accumul_sales_category($data){
		$this->db->insert("fm_accumul_stats_category",$data);
	}

	/* 구매통계 환불 집계 데이터 삭제 :: 2014-08-08 lwh */
	public function delete_accumul_sales_category($sdate='', $edate=''){
		if($sdate && $edate){
			$sql = "delete from fm_accumul_stats_category where deposit_date between '".$sdate." 00:00:00' and '".$edate." 23:59:59'";

			$query	= $this->db->query($sql);
		}
	}
}
