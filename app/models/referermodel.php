<?php
/**
 * 매출증빙 서류 : 현금영수증/매출증빙 내역
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class referermodel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	public function get_referersale_list($sc){

		$page		= (!trim($sc['page']))		? 0		: trim($sc['page']);
		$nperpage	= (!trim($sc['nperpage']))	? 10	: trim($sc['nperpage']);
		$perpage	= ($page / $nperpage) + 1;

		## 키워드 검색
		if	(!empty($sc['search_text'])){
			$addWhere	.= " and (referersale_name like '%".$sc['search_text']."%' 
								or referersale_url like '%".$sc['search_text']."%' ) ";
		}

		## 생성일 검색
		if	(!empty($sc['sdate']) && !empty($sc['edate'])){
			$addWhere	.= " and regist_date between '".$sc['sdate']."' and '".$sc['edate']."' ";
		}elseif	(!empty($sc['sdate'])){
			$addWhere	.= " and regist_date >= '".$sc['sdate']."' ";
		}elseif	(!empty($sc['edate'])){
			$addWhere	.= " and regist_date <= '".$sc['edate']."' ";
		}

		## 통신판매중계자 부담율 검색
		if	(!empty($sc['search_cost'])){
			if	($sc['cost_type'] == 'provider')
				$addWhere	.= " and salescost_provider = '".$sc['search_cost']."' ";
			else
				$addWhere	.= " and salescost_admin = '".$sc['search_cost']."' ";
		}

		## 입점사 검색
		if	(!empty($sc['provider_seq'])){
			$addWhere	.= " and provider_list like '%|".$sc['provider_seq']."|%' ";
		}

		## 입점사 부담율 검색
		if	(!empty($sc['salescost_provider'])){
			$addWhere	.= " and salescost_provider > '".$sc['salescost_provider']."' ";
		}

		$sql	= "select count(*) as total from fm_referersale ";
		$query	= $this->db->query($sql);
		$result	= $query->row_array();
		$total	= $result['total'];

		$sql	= "select *, 
					(select order_seq from fm_order_item_option where referersale_seq > 0 and referersale_seq = ref.referersale_seq limit 1) as order_seq 
					from fm_referersale as ref 
					where referersale_seq > 0 " . $addWhere
				. " order by referersale_seq desc ";
		$result = select_page($nperpage, $perpage, 10, $sql, '');

		$result['page']['querystring']	= get_args_list();
		$result['page']['total']		= $total;

		return $result;
	}
	
	//프로모션 > 쿠폰 > 사용제한 - 유입경로
	public function get_referersale_all($sc){ 

		## 키워드 검색
		if	(!empty($sc['search_text'])){
			$addWhere	.= " and (referersale_name like '%".$sc['search_text']."%' 
								or referersale_url like '%".$sc['search_text']."%' ) ";
		}

		## 생성일 검색
		if	(!empty($sc['sdate']) && !empty($sc['edate'])){
			$addWhere	.= " and regist_date between '".$sc['sdate']."' and '".$sc['edate']."' ";
		}elseif	(!empty($sc['sdate'])){
			$addWhere	.= " and regist_date >= '".$sc['sdate']."' ";
		}elseif	(!empty($sc['edate'])){
			$addWhere	.= " and regist_date <= '".$sc['edate']."' ";
		}

		## 통신판매중계자 부담율 검색
		if	(!empty($sc['search_cost'])){
			if	($sc['cost_type'] == 'provider')
				$addWhere	.= " and salescost_provider = '".$sc['search_cost']."' ";
			else
				$addWhere	.= " and salescost_admin = '".$sc['search_cost']."' ";
		}

		## 입점사 검색
		if	(!empty($sc['provider_seq'])){
			$addWhere	.= " and provider_list like '%|".$sc['provider_seq']."|%' ";
		}

		## 입점사 부담율 검색
		if	(!empty($sc['salescost_provider'])){
			$addWhere	.= " and salescost_provider > '".$sc['salescost_provider']."' ";
		}

		$sql	= "select * from fm_referersale";
		if( $addWhere ) $sql.= " where 1 ".$addWhere;
		$query	= $this->db->query($sql);
		$result	= $query->result_array(); 
		return $result;
	}

	public function get_referersale_info($referersale_seq){
		$sql	= "select * from fm_referersale where referersale_seq = ? ";
		$query	= $this->db->query($sql, array($referersale_seq));
		$result	= $query->result_array();

		return $result[0];
	}

	public function get_referersale_issuecategory($no)
	{
		$result = false;
		$this->db->where('referersale_seq', $no);
		$query = $this->db->get('fm_referersale_issuecategory');
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_referersale_issuegoods($no)
	{
		$result = false;
		$this->db->where('referersale_seq', $no);
		$query = $this->db->get('fm_referersale_issuegoods');
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function sales_referersale($referer_url, $goods_seq, $price, $ea,$couponreferear=null){

		if	($this->config_system['service']['code'] != 'P_FREE' && $this->isplusfreenot){

			// http나 https 제거
			if	(preg_match('/^http/', $referer_url)){
				$referer_url	= preg_replace('/^https*\:\/\//', '', $referer_url);
			}

			$date			= date('Y-m-d');
			$sql			= "select * from fm_referersale where '".$date."' between issue_startdate and issue_enddate and ( (url_type = 'like' and INSTR('".$referer_url."', referersale_url) ) or (url_type = 'equal' and referersale_url = '".$referer_url."') ) ";
			
			//프로모션 쿠폰 > 사용한- 유입경로 @2014-07-07
			if ( $couponreferear ) { 
				$couponreferein = implode("','",$couponreferear); 
				$sql.= " and referersale_seq in ('".$couponreferein."') "; 
			}

			$query			= $this->db->query($sql);
			$referersale	= $query->result_array();

			if($referersale){
				$this->load->model('goodsmodel');
				$goods_info			= $this->goodsmodel->get_goods($goods_seq);
				$goods_category		= $this->goodsmodel->get_goods_category($goods_seq);
				$category_code_arr	= array();
				if	($goods_category){
					foreach( $goods_category as $k => $category ) {
						$category_code_arr[]	= $category['category_code'];
					}
				}

				foreach( $referersale as $k => $row ) {

					if	($row['issue_type'] != 'all'){
						$issuegoods		= $this->get_referersale_issuegoods($row['referersale_seq']);
						$issuecategory	= $this->get_referersale_issuecategory($row['referersale_seq']);
					}

					## 허용인데 허용 대상 상품, 카테고리가 없는 경우 ( 전체 사용불가와 같음 )
					if	($row['issue_type'] == 'issue' && !$issuegoods && !$issuecategory) continue;

					## 허용/예외 상품 체크
					if	($issuegoods){
						foreach( $issuegoods as $k => $goods ){
							if	($row['issue_type'] == $goods['type'])
								$issuegoods_arr[]	= $goods['goods_seq'];
						}

						if	(($row['issue_type'] == 'issue' && !in_array($goods_seq, $issuegoods_arr))
							|| ($row['issue_type'] == 'except' && in_array($goods_seq, $issuegoods_arr))){
							continue;
						}
					}

					## 허용/예외 카테고리 체크
					if	($issuecategory){
						foreach($issuecategory as $k => $category){
							if		($row['issue_type'] == 'issue' && !in_array($category['category_code'], $category_code_arr)){
								$is_continue	= true;
								break;
							}elseif	($row['issue_type'] == 'except' && in_array($category['category_code'], $category_code_arr)){
								$is_continue	= true;
								break;
							}
						}
						if	($is_continue)	continue;
					}

					## 개당 할인 금액 계산 ( 고객에게 유리한 할인을 적용하기 위한 할인금액 비교 )
					$row['sales_price']	= $row['won_goods_sale'];
					if	($row['sale_type'] == 'percent')
						$row['sales_price']	= $price * ($row['percent_goods_sale'] / 100);

					// 가격 절사
					$row['sales_price']	= get_price_point($row['sales_price']);

//					if	($row['duplication_use'] == 1)
						$row['sales_price']	= $row['sales_price'] * $ea;

					if	($row['sale_type'] == 'percent' && $row['sales_price'] > $row['max_percent_goods_sale'])
						$row['sales_price']	= $row['max_percent_goods_sale'];

					if	($result['sales_price']	> 0 && $result['sales_price'] > $row['sales_price'])
						continue;

					## 할인 적용
					$result				= $row;
				}
			}
		}

		return $result;
	}

	public function get_referersale_for_url($referer_url, $url_type = 'equal'){
		$referer_url	= addslashes($referer_url);

		if	($url_type == 'like')
			$addWhere	= " or INSTR(referersale_url, '".$referer_url."') ";

		$sql			= "select * from fm_referersale where 
							(url_type = 'like' and INSTR('".$referer_url."', referersale_url)) or 
							(url_type = 'equal' and referersale_url = '".$referer_url."') "
							.$addWhere;
		$query			= $this->db->query($sql);
		$referersale	= $query->row_array();

		return $referersale;
	}

	// 유입경로 중복 체크 ( URL + 유효기간 + 입점사 + 제외 seq )
	public function chk_referersale_duple($referer_url, $url_type, $sdate, $edate, $referer_seq = ''){

		$url_type		= ($url_type) ? $url_type : 'equal';

		// 유효기간 중복 확인
		$addDate			= " '".$sdate."' between issue_startdate and issue_enddate or
								'".$edate."' between issue_startdate and issue_enddate ";

		// 유입경로 중복 확인
		$referer_url		= addslashes($referer_url);
		$addReferer			= " (url_type = 'like' and INSTR('".$referer_url."', referersale_url)) or 
								(url_type = 'equal' and referersale_url = '".$referer_url."') ";
		if	($url_type == 'like')
			$addReferer		.= " or INSTR(referersale_url, '".$referer_url."') ";

		// 제외 seq
		if	($referer_seq)
			$addSeq			= " and referersale_seq != '".$referer_seq."' ";

		$sql				= "select * from fm_referersale where 
								( ".$addDate." ) and 
								( ".$addReferer." ) "
								.$addSeq;
		$query				= $this->db->query($sql);
		$referersale		= $query->row_array();

		return $referersale;
	}

	// 할인 대상 유입경로 할인 목록
	public function get_referersale_target_list($referer_url){
		if	($this->config_system['service']['code'] != 'P_FREE' && $this->isplusfreenot){

			// http나 https 제거
			if	(preg_match('/^http/', $referer_url)){
				$referer_url	= preg_replace('/^https*\:\/\//', '', $referer_url);
			}

			$date			= date('Y-m-d');
			$sql			= "select * from fm_referersale where '".$date."' between issue_startdate and issue_enddate and ( (url_type = 'like' and INSTR('".$referer_url."', referersale_url) ) or (url_type = 'equal' and referersale_url = '".$referer_url."') ) ";
			$query			= $this->db->query($sql);
			$referersale	= $query->result_array();
		}

		return $referersale;
	}

	// 해당 상품에 적용 가능한 유입경로 할인 목록
	public function get_goods_referersale($goods_seq, $category_code_arr = array()){

		if	($this->config_system['service']['code'] != 'P_FREE' && $this->isplusfreenot){

			$date			= date('Y-m-d');
			$sql			= "select * from fm_referersale where '".$date."' between issue_startdate and issue_enddate ";
			$query			= $this->db->query($sql);
			$referersale	= $query->result_array();

			if($referersale){
				$this->load->model('goodsmodel');
				$goods_info			= $this->goodsmodel->get_goods($goods_seq);
				if	(!is_array($category_code_arr) || count($category_code_arr) < 1 ){
					$goods_category		= $this->goodsmodel->get_goods_category($goods_seq);
					$category_code_arr	= array();
					if	($goods_category){
						foreach( $goods_category as $k => $category ) {
							$category_code_arr[]	= $category['category_code'];
						}
					}
				}

				foreach( $referersale as $k => $row ) {

					if	($row['issue_type'] != 'all'){
						$issuegoods		= $this->get_referersale_issuegoods($row['referersale_seq']);
						$issuecategory	= $this->get_referersale_issuecategory($row['referersale_seq']);
					}

					## 허용인데 허용 대상 상품, 카테고리가 없는 경우 ( 전체 사용불가와 같음 )
					if	($row['issue_type'] == 'issue' && !$issuegoods && !$issuecategory) continue;

					## 허용/예외 상품 체크
					if	($issuegoods){
						foreach( $issuegoods as $k => $goods ){
							if	($row['issue_type'] == $goods['type'])
								$issuegoods_arr[]	= $goods['goods_seq'];
						}

						if	(($row['issue_type'] == 'issue' && !in_array($goods_seq, $issuegoods_arr))
							|| ($row['issue_type'] == 'except' && in_array($goods_seq, $issuegoods_arr))){
							continue;
						}
					}

					## 허용/예외 카테고리 체크
					if	($issuecategory){
						foreach($issuecategory as $k => $category){
							if		($row['issue_type'] == 'issue' && !in_array($category['category_code'], $category_code_arr)){
								$is_continue	= true;
								break;
							}elseif	($row['issue_type'] == 'except' && in_array($category['category_code'], $category_code_arr)){
								$is_continue	= true;
								break;
							}
						}
						if	($is_continue)	continue;
					}

					## 할인 적용
					$result[]		= $row;
				}
			}
		}

		return $result;
	}
}
?>