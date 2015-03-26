<?php
class goodssummarymodel extends CI_Model {

	var $today_event	= array();
	var $mobile_sale	= array();

	public function __construct(){
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('configsalemodel');
	}

	## 상품의 카테고리 목록 추출
	public function get_category_list($goods_seq){
		// 카테고리정보
		$tmparr2		= array();
		$categorys		= $this->goodsmodel->get_goods_category($goods_seq);
		foreach($categorys as $key => $val){
			$tmparr		= $this->categorymodel->split_category($val['category_code']);
			foreach($tmparr as $cate) $tmparr2[]	= $cate;
		}
		if($tmparr2){
			$tmparr2		= array_values(array_unique($tmparr2));
			$r_category		= $tmparr2;
		}

		return $r_category;
	}

	## 대표 브랜드 정보 추출
	public function get_goods_brand($goods_seq){
		$sql	= "select br.title as brand_title, br.title_eng as brand_title_eng,
					br.category_code as brand_code 
					from fm_brand br, fm_brand_link brl
					where br.category_code = brl.category_code and brl.link = '1' and
					brl.goods_seq = ? limit 1";
		$query	= $this->db->query($sql, array($goods_seq));
		$result	= $query->row_array();

		return $result;
	}

	## 상품 아이콘 정보 추출
	public function get_goods_icon($goods_seq){
		$sql	= "select group_concat(codecd) as icon from fm_goods_icon 
					where goods_seq = ? and (
					( ifnull( start_date, '0000-00-00' ) = '0000-00-00' and 
						ifnull( end_date, '0000-00-00' ) = '0000-00-00' )
					or	( curdate() between start_date and end_date )
					or	( start_date <= curdate() and ifnull( end_date, '0000-00-00' ) = '0000-00-00' )
					or	( end_date >= curdate() and ifnull( start_date, '0000-00-00' ) = '0000-00-00' ) )";
		$query	= $this->db->query($sql, array($goods_seq));
		$result	= $query->row_array();

		return $result['icon'];
	}

	## 이벤트 수정 시 상품 이벤트 할인가 재계산
	public function set_event_price($accept = array(), $except = array()){

		$target_date	= date('Y-m-d', strtotime('-2 day')) . ' 00:00:00';
		$addWhereDate = " and goods_seq in (select goods_seq from fm_goods where update_date > '".$target_date."' )";
		$addWhereDate2 = " and g.update_date > '".$target_date."'";

		if	(is_array($accept['goods']) && count($accept['goods']) > 0){
			$addWhere	.= " and g.goods_seq in ('".implode("', '", $accept['goods'])."') ";
			$addWhere2	.= " and goods_seq in ('".implode("', '", $accept['goods'])."') ";
		}
		if	(is_array($accept['category']) && count($accept['category']) > 0){
			$addWhere	.= " and g.goods_seq in (
								select goods_seq from fm_category_link where
								category_code in ('".implode("', '", $accept['category'])."') ) ";
			$addWhere2	.= " and goods_seq in (
								select goods_seq from fm_category_link where
								category_code in ('".implode("', '", $accept['category'])."') ) ";
		}
		if	(is_array($except['goods']) && count($except['goods']) > 0){
			$addWhere	.= " and g.goods_seq not in ('".implode("', '", $accept['goods'])."') ";
			$addWhere2	.= " and goods_seq not in ('".implode("', '", $accept['goods'])."') ";
		}
		if	(is_array($except['category']) && count($except['category']) > 0){
			$addWhere	.= " and g.goods_seq not in (
								select goods_seq from fm_category_link where
								category_code in ('".implode("', '", $accept['category'])."') ) ";
			$addWhere2	.= " and goods_seq not in (
								select goods_seq from fm_category_link where
								category_code in ('".implode("', '", $accept['category'])."') ) ";
		}

		if ($addWhere || $addWhere2) {
			$addWhereDate = "";
			$addWhereDate2 = "";
		}

		// 해당 데이터 삭제
		$sql			= "delete from fm_goods_list_summary
							where 1 ".$addWhereDate." and platform in ('P', 'M') ".$addWhere2;
		mysql_query($sql);

		// 아이콘 추출 쿼리
		$icon_sql	= "select group_concat(codecd) as icon from fm_goods_icon 
						where (
						( ifnull( start_date, '0000-00-00' ) = '0000-00-00' and 
							ifnull( end_date, '0000-00-00' ) = '0000-00-00' )
						or	( curdate() between start_date and end_date )
						or	( start_date <= curdate() and ifnull( end_date, '0000-00-00' ) = '0000-00-00' )
						or	( end_date >= curdate() and ifnull( start_date, '0000-00-00' ) = '0000-00-00' ) )";
		// 브랜드 추출 쿼리
		$brand_sql	= "select br.title as brand_title, br.title_eng as brand_title_eng,
						br.category_code as brand_code 
						from fm_brand br, fm_brand_link brl
						where br.category_code = brl.category_code and brl.link = '1' ";

		// 해당 상품 추출
		$sql			= "select g.goods_seq, g.goods_kind, o.price, o.consumer_price
							from fm_goods g, fm_goods_option o
							where g.goods_seq = o.goods_seq and g.goods_type = 'goods'
							and o.default_option = 'y' ".$addWhere ." and  g.goods_type='goods' and  g.goods_view='look' ".$addWhereDate2." GROUP BY o.goods_seq";
		$result			= mysql_query($sql);
		if( $result ) {
			while($data = mysql_fetch_assoc($result)){

				unset($iconsql, $icon_rs, $icon_data, $brand_arr, $priceArr, $param);
				$iconsql	= $icon_sql . " and goods_seq = '".$data['goods_seq']."' ";
				$icon_rs	= mysql_query($iconsql);
				if($icon_rs) $icon_data	= mysql_fetch_assoc($icon_rs);

				$brandsql	= $brand_sql . " and brl.goods_seq = '".$data['goods_seq']."' limit 1 ";
				$brand_rs	= mysql_query($brandsql);
				if($brand_rs) $brand_data	= mysql_fetch_assoc($brand_rs);

				$ins_sql ="INSERT INTO `fm_goods_list_summary` (`goods_seq`, `platform`, `brand_title`, `brand_title_eng`, `brand_code`, `today_icon`, `today_solo_start`, `today_solo_end`, `consumer_price`, `price`, `regist_date`) VALUES ('".$data['goods_seq']."', 'P', '".$brand_data['brand_title']."', '".$brand_data['brand_title_eng'] ."', '".$brand_data['brand_code']."', '".$icon_data['icon']."', NULL, NULL, '0', '', now())";
				mysql_query($ins_sql);

				$ins_sql ="INSERT INTO `fm_goods_list_summary` (`goods_seq`, `platform`, `brand_title`, `brand_title_eng`, `brand_code`, `today_icon`, `today_solo_start`, `today_solo_end`, `consumer_price`, `price`, `regist_date`) VALUES ('".$data['goods_seq']."', 'M', '".$brand_data['brand_title']."', '".$brand_data['brand_title_eng'] ."', '".$brand_data['brand_code']."', '".$icon_data['icon']."', NULL, NULL, '0', '', now())";
				mysql_query($ins_sql);
			}
		}
	}

/*
	## 이벤트 수정 시 상품 이벤트 할인가 재계산
	public function set_event_price($accept = array(), $except = array()){

//		$this->set_today_event();

		if	(is_array($accept['goods']) && count($accept['goods']) > 0){
			$addWhere	.= " and g.goods_seq in ('".implode("', '", $accept['goods'])."') ";
			$addWhere2	.= " and goods_seq in ('".implode("', '", $accept['goods'])."') ";
		}
		if	(is_array($accept['category']) && count($accept['category']) > 0){
			$addWhere	.= " and g.goods_seq in (
								select goods_seq from fm_category_link where 
								category_code in ('".implode("', '", $accept['category'])."') ) ";
			$addWhere2	.= " and goods_seq in (
								select goods_seq from fm_category_link where 
								category_code in ('".implode("', '", $accept['category'])."') ) ";
		}
		if	(is_array($except['goods']) && count($except['goods']) > 0){
			$addWhere	.= " and g.goods_seq not in ('".implode("', '", $accept['goods'])."') ";
			$addWhere2	.= " and goods_seq not in ('".implode("', '", $accept['goods'])."') ";
		}
		if	(is_array($except['category']) && count($except['category']) > 0){
			$addWhere	.= " and g.goods_seq not in (
								select goods_seq from fm_category_link where 
								category_code in ('".implode("', '", $accept['category'])."') ) ";
			$addWhere2	.= " and goods_seq not in (
								select goods_seq from fm_category_link where 
								category_code in ('".implode("', '", $accept['category'])."') ) ";
		}

		// 해당 데이터 삭제
		$sql			= "delete from fm_goods_list_summary 
							where goods_seq > 0 and platform = 'P' ".$addWhere2;
		$this->db->query($sql);
		$sql			= "delete from fm_goods_list_summary 
							where goods_seq > 0 and platform = 'M' ".$addWhere2;
		$this->db->query($sql);

		// 해당 상품 추출
		$sql			= "select g.goods_seq, g.goods_kind, o.price, o.consumer_price 
							from fm_goods g, fm_goods_option o 
							where g.goods_seq = o.goods_seq and g.goods_type = 'goods' 
							and o.default_option = 'y' ".$addWhere;
		$query			= $this->db->query($sql);
		$result			= $query->result_array();
		$goods_arr		= array();
		foreach($result as $k => $data){
			if	(!in_array($data['goods_seq'], $goods_arr)){
				unset($category_arr, $icon_arr, $brand_arr, $priceArr, $param);
				$category_arr	= $this->get_category_list($data['goods_seq']);
				$icon_arr		= $this->get_goods_icon($data['goods_seq']);
				$brand_arr		= $this->get_goods_brand($data['goods_seq']);

//				$priceArr	= $this->calculate_time_price($data['goods_seq'], $data['goods_kind'], $category_arr, $data['price'], $data['consumer_price']);

				$param['price']				= $data['price'];
				$param['consumer_price']	= $data['consumer_price'];
				$param['icon']				= $icon_arr;
				$param['sale']				= $priceArr;
				$param['brand']				= $brand_arr;
				$this->save_goods_list_summary($data['goods_seq'], 'P', $param);
				$this->save_goods_list_summary($data['goods_seq'], 'M', $param);

				$goods_arr[]	 = $data['goods_seq'];
			}
		}
	}
*/
	## 상품 목록용 요약 정보 저장
	public function save_goods_list_summary($goods_seq, $platform, $param){

		// 데이터 저장
		$insParam['goods_seq']				= $goods_seq;
		$insParam['platform']				= $platform;
		$insParam['brand_title']			= $param['brand']['brand_title'];
		$insParam['brand_title_eng']		= $param['brand']['brand_title_eng'];
		$insParam['brand_code']				= $param['brand']['brand_code'];
		$insParam['today_icon']				= $param['icon'];
		$insParam['today_solo_start']		= $param['sale']['start'];
		$insParam['today_solo_end']			= $param['sale']['end'];
		$insParam['consumer_price']			= $param['consumer_price'];
		$insParam['price']					= $param['price'];
		$insParam['regist_date']			= date('Y-m-d H:i:s');
/*
		for ($t = 0; $t < 24; $t++){
			$nTime							= str_pad($t, 2, '0', STR_PAD_LEFT);
			if	($platform == 'M')
				$insParam['price_'.$nTime]	= $this->calculate_mobile_sale($param['price'], $param['sale'][$nTime]);
			else
				$insParam['price_'.$nTime]	= $param['sale'][$nTime];
		}
*/
		$this->db->insert('fm_goods_list_summary', $insParam);
	}

	## 시간대별 할인가 계산
	public function calculate_time_price($goods_seq, $goods_kind, $category, $price, $consumer_price){

		// 초기값 세팅
		if	(!$consumer_price)		$consumer_price	= $price;
		$result['start']	= '';
		$result['end']		= '';
		for ( $t = 0; $t < 24; $t++ ){
			$nTime	= str_pad($t, 2, '0', STR_PAD_LEFT);
			$result[$nTime]	= $price;
			$solos[$nTime]	= false;
		}

		// 이벤트 조회
		$event	= $this->today_event;

		if	($event)foreach($event as $k => $evt){
			if	($evt['goods'])					$gSeqArr	= explode(',', $evt['goods']);
			if	($evt['category'])				$gCateArr	= explode(',', $evt['category']);
			if	($evt['exception_goods'])		$geSeqArr	= explode(',', $evt['exception_goods']);
			if	($evt['exception_category'])	$geCateArr	= explode(',', $evt['exception_category']);

			// 카테고리 선정
			if	($evt['goods_rule'] == 'category'){
				$set_sale	= false;
				if	($category)foreach($category as $c => $code){
					if	($gCateArr && in_array($code, $gCateArr)){
						$set_sale	= true;
					}
					if	($geCateArr && in_array($code, $geCateArr)){
						$set_sale	= false;
						break;
					}
				}
				if	(in_array($goods_seq, $geSeqArr))	$set_sale	= false;

			// 상품으로 선정일때
			}elseif	($evt['goods_rule'] == 'goods_view'){
				$set_sale	= false;
				if	(in_array($goods_seq, $gSeqArr))	$set_sale	= true;

			// 전체 상품일 때
			}else{
				$set_sale	= true;
				if	($evt['goods_kind'] == 'coupon' && $goods_kind == 'goods')	$set_sale	= false;
				if	($evt['goods_kind'] == 'goods' && $goods_kind == 'coupon')	$set_sale	= false;
				if	($set_sale){
					if	($category)foreach($category as $c => $code){
						if	($geCateArr && in_array($code, $geCateArr)){
							$set_sale	= false;
							break;
						}
					}
				}
				if	($set_sale){
					if	(in_array($goods_seq, $geSeqArr))	$set_sale	= false;
				}
			}

			if	($set_sale){
				// 단독이벤트 시간 표시
				if	($evt['event_type'] == 'solo'){
					$result['start']	= $evt['solo_start'];
					$result['end']		= $evt['solo_end'];
				}

				// 시간대별 이벤트 금액 계산 ( 시간별 더 높은 할인금액이 최종금액이 된다. )
				for ( $t = 0; $t < 24; $t++ ){
					$nTime	= str_pad($t, 2, '0', STR_PAD_LEFT);
					if	($evt['sTime'] <= $nTime && $evt['eTime'] >= $nTime && !$solos[$nTime]){
						// 단독이벤트 우선 처리
						if	($evt['event_type'] == 'solo')	$solos[$nTime]	= true;

						$cprice		= $price;
						if	($evt['target'] == 1)	$cprice		= $consumer_price;

						$nprice	= floor($cprice - ($cprice * ($evt['sale'] / 100)));
						if	($result[$nTime] > $nprice)		$result[$nTime]		= $nprice;
					}
				}
			}
		}

		return $result;
	}


	## 오늘날짜에 해당하는 시간대별 이벤트 추출
	public function set_today_event(){
		$today_event	= array();

		$ndate			= date('Ymd');
		$nweek			= date('w');
		$sql			= "select * from fm_event 
							where DATE_FORMAT(start_date, '%Y%m%d') <= '".$ndate."' 
							and DATE_FORMAT(end_date, '%Y%m%d') >= '".$ndate."' 
							and (app_week is null or app_week = '' or app_week like '%".$nweek."%') ";
		$query			= $this->db->query($sql);
		$result			= $query->result_array();
		if	($result)foreach($result as $k => $event){

			unset($data);
			$data['event_seq']		= $event['event_seq'];


			// 1. 이벤트 종류 ( 일반 or 단독 )
			$data['event_type']		= $event['event_type'];

			// 2. 이벤트 시간
			$start_time	= strtotime($event['start_date']);
			$end_time	= strtotime($event['end_date']);
			if	($event['app_start_time']){
				if	(date('Ymd', $start_time) == date('Ymd')){
					if	(substr($event['app_start_time'], 0, 2) >= date('H', $start_time)){
						$data['sTime']		= substr($event['app_start_time'], 0, 2);
					}else{
						$data['sTime']	= date('H', $start_time);
					}
				}else{
						$data['sTime']	= substr($event['app_start_time'], 0, 2);
				}
			}else{
				if	(date('Ymd', $start_time) == date('Ymd'))
					$data['sTime']	= date('H', $start_time);
				else
					$data['sTime']	= '00';
			}
			if	($event['app_end_time']){
				if	(date('Ymd', $end_time) == date('Ymd')){
					if	(substr($event['app_end_time'], 0, 2) <= date('H', $end_time))
						$data['eTime']	= substr($event['app_end_time'], 0, 2);
					else
						$data['eTime']	= date('H', $end_time);
				}else{
						$data['eTime']	= substr($event['app_end_time'], 0, 2);
				}
			}else{
				if	(date('Ymd', $end_time) == date('Ymd'))
					$data['eTime']	= date('H', $end_time);
				else
					$data['eTime']	= '23';
			}

			if	($event['event_type'] == 'solo'){
				$data['solo_start']	= date('Y-m-d', $start_time).' '.$data['sTime'];
				$data['solo_end']	= date('Y-m-d', $end_time).' '.$data['eTime'];
			}

			// 3. 상품 선택 기준
			$data['goods_kind']		= $event['apply_goods_kind'];
			$data['goods_rule']		= $event['goods_rule'];

			// 혜택정보
			$sql		= "select * from fm_event_benefits where event_seq = '".$event['event_seq']."' ";
			$query		= $this->db->query($sql);
			$benefit	= $query->result_array();
			if	($benefit)foreach($benefit as $b => $bnf){
				$goods				= '';
				$category			= '';
				$except_goods		= '';
				$except_category	= '';

				// 상품/카테고리
				$sql				= "select * from fm_event_choice 
										where event_benefits_seq = '".$bnf['event_benefits_seq']."' ";
				$query				= $this->db->query($sql);
				$choice				= $query->result_array();
				if	($choice)foreach($choice as $i => $chc){
					if			($chc['choice_type'] == 'goods'){
						if		($goods)			$goods				.= ','.$chc['goods_seq'];
						else						$goods				.= $chc['goods_seq'];
					}elseif		($chc['choice_type'] == 'category'){
						if		($category)			$category			.= ','.$chc['category_code'];
						else						$category			.= $chc['category_code'];
					}elseif		($chc['choice_type'] == 'except_goods'){
						if		($except_goods)		$except_goods		.= ','.$chc['goods_seq'];
						else						$except_goods		.= $chc['goods_seq'];
					}elseif	($chc['choice_type'] == 'except_category'){
						if		($except_category)	$except_category	.= ','.$chc['category_code'];
						else						$except_category	.= $chc['category_code'];
					}
				}

				$data['target']				= $bnf['target_sale'];
				$data['sale']				= $bnf['event_sale'];
				$data['goods']				= $goods;
				$data['category']			= $category;
				$data['exception_goods']	= $except_goods;
				$data['exception_category']	= $except_category;

				$today_event[]				= $data;
			}
		}

		// 모바일 할인
		$mobilesale	= $this->configsalemodel->lists($sc);

		$this->mobile_sale	= $mobilesale;
		$this->today_event	= $today_event;
	}

	## 모바일 할인 금액 계산
	public function calculate_mobile_sale($goods_price, $price){
		$result		= $goods_price;
		$sc['type']	= 'mobile';
		$mobilesale	= $this->mobile_sale;
		foreach($mobilesale['result'] as $k => $mobile) {
			if($mobile['price1'] <= $goods_price && $mobile['price2'] >= $goods_price){
				$tmp_price	= $price - ($goods_price * ($mobile['sale_price'] / 100)); // 모바일 할인
				if	($result > $tmp_price)	$result	= $tmp_price;
			}//endif
		}//end foreach

		return $result;
	}

	## 브랜드 정보 변경 시 상품요약 테이블에 반영 ( brand_code 기준임 )
	public function save_goods_list_summary_brand($brand_code){

		$sql	= "select goods_seq from fm_goods_list_summary 
					where platform = 'P' and brand_code = ? group by goods_seq ";
		$query	= $this->db->query($sql, array($brand_code));
		$result	= $query->result_array();

		// 대표브랜드를 다시 가져와서 설정한다.
		if	($result)foreach($result as $k => $goods_seq){

			if (is_array($goods_seq)) {
				$goods_seq = $goods_seq['goods_seq'];
			}

			$brand	= $this->get_goods_brand($goods_seq);

			unset($upParam);
			$upParam['brand_title']			= $brand['brand_title'];
			$upParam['brand_title_eng']		= $brand['brand_title_eng'];
			$upParam['brand_code']			= $brand['brand_code'];
			$this->db->where(array('goods_seq'=>$goods_seq));
			$this->db->update('fm_goods_list_summary', $upParam);
		}
	}
}

/* End of file goodssummarymodel.php */
/* Location: ./app/models/goodssummarymodel.php */