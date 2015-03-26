<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic extends admin_base {
	
	public function __construct() {
		parent::__construct();

		$this->load->model('statsmodel');
	}

	public function index()
	{
		redirect("/admin/statistic_visitor");		
	}

	public function advanced_statistics(){
		$filePath	= $this->template_path();
		$this->template->assign(array('service_code' => $this->config_system['service']['code']));
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function advanced_statistic_sub(){

		$filePath	= $this->template_path();

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('advanced_statistic');
/*
		if(!$result['type'] && !in_array($_GET['pageType'], array('popular', 'main'))){
			$filePath		= str_replace('advanced_statistic_sub.html', 'statistic_pfree.html', $filePath);
		}else{
*/
			if(!$result['type']){
				$this->advanced_statistic_limit	= 'y';
				$this->template->assign(array('advanced_statistic_limit' => 'y'));
			}

			$sub_function	= 'statistic_' . $_GET['pageType'];
			$this->$sub_function();
			$filePath		= str_replace('advanced_statistic_sub.html', $sub_function.'.html', $filePath);
//		}
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function statistic_main(){

		$this->statsmodel->get_main_statistic_data();


		$this->template->assign(array(
			'dataForChart'	=> $this->statsmodel->dataForChart,
			'maxValue'		=> $this->statsmodel->maxValue,
			'stat'			=> $this->statsmodel->stat,
			'rank_array'	=> $this->statsmodel->rank_array 
		));
	}

	public function statistic_popular(){

		$_GET['catenbrand']		= ($_GET['catenbrand']) ? $_GET['catenbrand'] : 'category';
		$params					= $_GET;
		$rank_array				= array('first', 'second', 'third');

		if	($_GET['catenbrand'] == 'brand'){
			$this->load->model('brandmodel');
			$brand		= $this->brandmodel->get_represent_brand_for_goods($_GET['goods_seq']);
			$params['type']				= 'brand';
			$params['category_code']	= $brand['category_code'];
		}else{
			$this->load->model('categorymodel');
			$category	= $this->categorymodel->get_represent_category_for_goods($_GET['goods_seq']);
			$params['type']				= 'category';
			$params['category_code']	= $category['category_code'];
		}

		## 장바구니
		$params['get_type']		= 'total';
		$query					= $this->statsmodel->get_current_cart_stats($params);
		$cart					= $query->result_array();
		$stat['total']['cart']	= $cart[0]['cnt'];

		$params['get_type']	= 'rank';
		$query		= $this->statsmodel->get_current_cart_stats($params);
		$cart		= $query->result_array();
		for ($r = 0; $r < 3; $r++){
			$stat['rank'][$r]['cart']['cnt']		= $cart[$r]['cnt'];
			$stat['rank'][$r]['cart']['goods_name']	= $cart[$r]['goods_name'];
			$stat['rank'][$r]['cart']['goods_seq']	= $cart[$r]['goods_seq'];
		}


		## 위시리스트
		$params['get_type']		= 'total';
		$query					= $this->statsmodel->get_current_wish_stats($params);
		$wish					= $query->result_array();
		$stat['total']['wish']	= $wish[0]['cnt'];

		$params['get_type']	= 'rank';
		$query		= $this->statsmodel->get_current_wish_stats($params);
		$wish		= $query->result_array();
		for ($r = 0; $r < 3; $r++){
			$stat['rank'][$r]['wish']['cnt']		= $wish[$r]['cnt'];
			$stat['rank'][$r]['wish']['goods_name']	= $wish[$r]['goods_name'];
			$stat['rank'][$r]['wish']['goods_seq']	= $wish[$r]['goods_seq'];
		}


		## 좋아요
		$params['get_type']		= 'total';
		$query					= $this->statsmodel->get_current_like_stats($params);
		$like					= $query->result_array();
		$stat['total']['like']	= $like[0]['cnt'];

		$params['get_type']		= 'rank';
		$query					= $this->statsmodel->get_current_like_stats($params);
		$like					= $query->result_array();
		for ($r = 0; $r < 3; $r++){
			$stat['rank'][$r]['like']['cnt']		= $like[$r]['cnt'];
			$stat['rank'][$r]['like']['goods_name']	= $like[$r]['goods_name'];
			$stat['rank'][$r]['like']['goods_seq']	= $like[$r]['goods_seq'];
		}

		## 재입고알림
		$params['get_type']			= 'total';
		$query						= $this->statsmodel->get_current_restock_stats($params);
		$restock					= $query->result_array();
		$stat['total']['restock']	= $restock[0]['cnt'];

		$params['get_type']			= 'rank';
		$query						= $this->statsmodel->get_current_restock_stats($params);
		$restock					= $query->result_array();
		for ($r = 0; $r < 3; $r++){
			$stat['rank'][$r]['restock']['cnt']			= $restock[$r]['cnt'];
			$stat['rank'][$r]['restock']['goods_name']	= $restock[$r]['goods_name'];
			$stat['rank'][$r]['restock']['goods_seq']	= $restock[$r]['goods_seq'];
		}

		$this->template->assign(array(
			'stat'			=> $stat,
			'rank_array'	=> $rank_array 
		));
	}

	public function statistic_order(){

		$_GET['date_term']		= ($_GET['date_term']) ? $_GET['date_term'] : 'yesterday';
		$params					= $_GET;

		$query					= $this->statsmodel->get_statistic_order_stats($params);
		$order					= $query->result_array();
		if	($order){
			foreach($order as $k => $data){
				$stat[$data['dates']]	= $data['price'];
			}
		}

		if		($_GET['date_term'] == 'yesterday' || $_GET['date_term'] == 'today'){
			for ($h = 0; $h <= 23; $h++){
				$hour	= str_pad($h, 2, '0', STR_PAD_LEFT);
				$price	= ($stat[$hour]) ? $stat[$hour] : 0;

				$maxValue	=  ($maxValue < $price) ? $price : $maxValue;
				$dataForChart[]	= array($hour.'시', $price);
			}
		}else{
			$start_time	= strtotime('-'.(str_replace('days', '', $_GET['date_term'])-1).' day');
			$nDate		= date('Y-m-d', $start_time);
			while (date('Y-m-d', strtotime('+1 day')) != $nDate){
				$addDay++;
				$day			= date('d', strtotime($nDate));
				$price			= ($stat[$day]) ? $stat[$day] : 0;
				$dataForChart[]	= array($day.'일', $price);

				$maxValue	=  ($maxValue < $price) ? $price : $maxValue;

				$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));
	}

	public function statistic_category(){

		$_GET['catenbrand']		= ($_GET['catenbrand']) ? $_GET['catenbrand'] : 'category';
		$_GET['date_term']		= ($_GET['date_term']) ? $_GET['date_term'] : 'yesterday';
		$params					= $_GET;

		## 해당 상품의 대표 카테고리 및 브랜드 코드 추출
		if	($_GET['catenbrand'] == 'brand'){
			$this->load->model('brandmodel');
			$brand		= $this->brandmodel->get_represent_brand_for_goods($_GET['goods_seq']);
			$params['category_code']	= $brand['category_code'];
		}else{
			$this->load->model('categorymodel');
			$category	= $this->categorymodel->get_represent_category_for_goods($_GET['goods_seq']);
			$params['category_code']	= $category['category_code'];
		}

		## 해당 상품의 대표 카테고리 및 브랜드를 제외한 상위 1개의 코드 추출
		$params['q_type']		= 'first';
		$query					= $this->statsmodel->get_statistic_category_stats($params);
		$first					= $query->result_array();

		## 목록 추출
		$params['first']		= $first[0]['category_code'];
		$params['q_type']		= 'list';
		$query					= $this->statsmodel->get_statistic_category_stats($params);
		$order					= $query->result_array();
		if	($order){
			$titles	= array();
			foreach($order as $k => $data){
				$price	= floor($data['price']/1000);

				$total[$data['title']]					+= $price;
				$stat[$data['title']][$data['dates']]	= $price;
			}
		}

		arsort($total);
		$cate_array	= array_keys($total);
		$cate_cnt	= count($cate_array);
		if		($_GET['date_term'] == 'yesterday' || $_GET['date_term'] == 'today'){
			for ($h = 0; $h <= 23; $h++){
				$hour			= str_pad($h, 2, '0', STR_PAD_LEFT);
				$table_title[]	= $hour.'시';
				for ($c = 0; $c < $cate_cnt; $c++){
					$tit	= $cate_array[$c];
					$price	= ($stat[$tit][$hour]) ? $stat[$tit][$hour] : 0;

					$statlist[$tit]['date'][$hour]	= $price;
					$dataForChart[$tit][]			= array($hour.'시', $price);
					$maxValue						=  ($maxValue < $price) ? $price : $maxValue;
				}
			}
		}else{
			$start_time	= strtotime('-'.(str_replace('days', '', $_GET['date_term'])-1).' day');
			$nDate		= date('Y-m-d', $start_time);
			while (date('Y-m-d', strtotime('+1 day')) != $nDate){
				$addDay++;
				$day			= date('d', strtotime($nDate));
				$table_title[]	= $day.'일';
				for ($c = 0; $c < $cate_cnt; $c++){
					$tit	= $cate_array[$c];
					$price	= ($stat[$tit][$day]) ? $stat[$tit][$day] : 0;

					$statlist[$tit]['date'][$day]	= $price;
					$dataForChart[$tit][]			= array($day.'일', $price);
					$maxValue						=  ($maxValue < $price) ? $price : $maxValue;
				}

				$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
			}
		}

		$this->template->assign(array(
			'total'			=> $total,
			'table_title'	=> $table_title,
			'statlist'		=> $statlist, 
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));
	}

	public function statistic_referer(){

		$_GET['date_term']		= ($_GET['date_term']) ? $_GET['date_term'] : 'yesterday';
		$params					= $_GET;

		$query					= $this->statsmodel->get_statistic_referer_stats($params);
		$referer				= $query->result_array();
		if	($referer){
			$titles	= array();
			foreach($referer as $k => $data){
				$price	= floor($data['price']/1000);
				$total[$data['referer_name']]					+= $price;
				$stat[$data['referer_name']][$data['dates']]	= $price;
			}
		}

		arsort($total);
		$referer_array	= array_keys($total);
		$referer_cnt	= count($referer_array);
		if		($_GET['date_term'] == 'yesterday' || $_GET['date_term'] == 'today'){
			for ($h = 0; $h <= 23; $h++){
				$hour			= str_pad($h, 2, '0', STR_PAD_LEFT);
				$table_title[]	= $hour.'시';
				for ($r = 0; $r < $referer_cnt; $r++){
					$tit	= $referer_array[$r];
					$price	= ($stat[$tit][$hour]) ? $stat[$tit][$hour] : 0;

					$statlist[$tit]['date'][$hour]	= $price;
					$dataForChart[$tit][]			= array($hour.'시', $price);
					$maxValue						=  ($maxValue < $price) ? $price : $maxValue;
				}
			}
		}else{
			$start_time	= strtotime('-'.(str_replace('days', '', $_GET['date_term'])-1).' day');
			$nDate		= date('Y-m-d', $start_time);
			while (date('Y-m-d', strtotime('+1 day')) != $nDate){
				$addDay++;
				$day			= date('d', strtotime($nDate));
				$table_title[]	= $day.'일';
				for ($r = 0; $r < $referer_cnt; $r++){
					$tit	= $referer_array[$r];
					$price	= ($stat[$tit][$day]) ? $stat[$tit][$day] : 0;

					$statlist[$tit]['date'][$day]	= $price;
					$dataForChart[$tit][]			= array($day.'일', $price);
					$maxValue						=  ($maxValue < $price) ? $price : $maxValue;
				}

				$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
			}
		}

		$this->template->assign(array(
			'total'			=> $total,
			'table_title'	=> $table_title,
			'statlist'		=> $statlist, 
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));
	}

	public function statistic_etc(){

		$this->load->helper('zipcode');
		$arr_age			= array('10대 이하','20대','30대','40대','50대','60대 이상');
		$arr_sex			= array('남','여');
		$arr_location		= array();
		$ZIP_DB				= get_zipcode_db();
		$query = $ZIP_DB->query("SELECT substring(SIDO,1,2) as SIDO FROM `zipcode` GROUP BY SIDO");
		foreach($query->result_array() as $row){
			$arr_location[] = $row['SIDO'];
		}

		$_GET['date_term']		= ($_GET['date_term']) ? $_GET['date_term'] : 'yesterday';
		$params					= $_GET;

		$query					= $this->statsmodel->get_statistic_etc_stats($params);
		$etc					= $query->result_array();
		if	($etc){
			$titles	= array();
			foreach($etc as $k => $data){
				$age[$data['buyer_age']]		+= $data['cnt'];
				$sex[$data['buyer_sex']]		+= $data['cnt'];
				$area[$data['buyer_area']]		+= $data['cnt'];
			}
		}

		##
		foreach($arr_sex as $k => $v){
			$cnt	=  ($sex[$v]) ? $sex[$v] : 0;

			$dataForChart['성별'][]	= array($v, $cnt);
		}

		##
		foreach($arr_age as $k => $v){
			$cnt	=  ($age[$v]) ? $age[$v] : 0;

			$dataForChart['연령'][]	= array($v, $cnt);
		}

		##
		foreach($arr_location as $k => $v){
			$cnt	=  ($area[$v]) ? $area[$v] : 0;

			$dataForChart['지역'][]	= array($v, $cnt);

			$maxValue	=  ($maxValue < $cnt) ? $cnt : $maxValue;
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));
	}

	public function statistic_cart(){

		$_GET['date_term']		= ($_GET['date_term']) ? $_GET['date_term'] : 'yesterday';
		$params					= $_GET;

		$query					= $this->statsmodel->get_statistic_cart_stats($params);
		$cart					= $query->result_array();
		if	($cart){
			foreach($cart as $k => $data){
				$stat[$data['dates']]	= $data['cnt'];
			}
		}

		if		($_GET['date_term'] == 'yesterday' || $_GET['date_term'] == 'today'){
			for ($h = 0; $h <= 23; $h++){
				$hour			= str_pad($h, 2, '0', STR_PAD_LEFT);
				$table_title[]	= $hour.'시';
				$cnt			= ($stat[$hour]) ? $stat[$hour] : 0;

				$dataForChart[]	= array($hour.'시', $cnt);
				$maxValue		=  ($maxValue < $cnt) ? $cnt : $maxValue;
			}
		}else{
			$start_time	= strtotime('-'.(str_replace('days', '', $_GET['date_term'])-1).' day');
			$nDate		= date('Y-m-d', $start_time);
			while (date('Y-m-d', strtotime('+1 day')) != $nDate){
				$addDay++;
				$day			= date('d', strtotime($nDate));
				$table_title[]	= $day.'일';
				$cnt			= ($stat[$day]) ? $stat[$day] : 0;

				$dataForChart[]	= array($day.'일', $cnt);
				$maxValue		=  ($maxValue < $cnt) ? $cnt : $maxValue;

				$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));
	}

	public function statistic_wish(){

		$_GET['date_term']		= ($_GET['date_term']) ? $_GET['date_term'] : 'yesterday';
		$params					= $_GET;

		$query					= $this->statsmodel->get_statistic_wish_stats($params);
		$cart					= $query->result_array();
		if	($cart){
			foreach($cart as $k => $data){
				$stat[$data['dates']]	= $data['cnt'];
			}
		}

		if		($_GET['date_term'] == 'yesterday' || $_GET['date_term'] == 'today'){
			for ($h = 0; $h <= 23; $h++){
				$hour			= str_pad($h, 2, '0', STR_PAD_LEFT);
				$table_title[]	= $hour.'시';
				$cnt			= ($stat[$hour]) ? $stat[$hour] : 0;

				$dataForChart[]	= array($hour.'시', $cnt);
				$maxValue		=  ($maxValue < $cnt) ? $cnt : $maxValue;
			}
		}else{
			$start_time	= strtotime('-'.(str_replace('days', '', $_GET['date_term'])-1).' day');
			$nDate		= date('Y-m-d', $start_time);
			while (date('Y-m-d', strtotime('+1 day')) != $nDate){
				$addDay++;
				$day			= date('d', strtotime($nDate));
				$table_title[]	= $day.'일';
				$cnt			= ($stat[$day]) ? $stat[$day] : 0;

				$dataForChart[]	= array($day.'일', $cnt);
				$maxValue		=  ($maxValue < $cnt) ? $cnt : $maxValue;

				$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));
	}

	public function member_statistics(){
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function member_referer(){

		$_GET['referer_name']	= $_GET['referer_name'];
		$params					= $_GET;
		$params['date_type']	= '30days';
		if	($_GET['referer_name']){
			$query					= $this->statsmodel->get_member_referer_stats($params);
			$referer				= $query->result_array();
			if	($referer){
				foreach($referer as $k => $data){
					$stat[$data['date']]	= $data['cnt'];
				}
			}
		}

		$start_time	= strtotime('-29 day');
		$nDate		= date('Y-m-d', $start_time);
		while (date('Y-m-d', strtotime('+1 day')) != $nDate){
			$addDay++;
			$day			= date('d', strtotime($nDate));
			$cnt			= ($stat[$day]) ? $stat[$day] : 0;

			$dataForChart[]	= array($day.'일', $cnt);
			$maxValue		=  ($maxValue < $cnt) ? $cnt : $maxValue;

			$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function order_statistics(){
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function order_referer(){

		$_GET['referer_name']	= $_GET['referer_name'];
		$params					= $_GET;
		$params['dateSel_type']	= '30days';
		if	($_GET['referer_name']){
			$query					= $this->statsmodel->get_sales_referer_stats($params);
			$referer				= $query->result_array();
			if	($referer){
				foreach($referer as $k => $data){
					$stat[$data['date']]	= $data['cnt'];
				}
			}
		}

		$start_time	= strtotime('-29 day');
		$nDate		= date('Y-m-d', $start_time);
		while (date('Y-m-d', strtotime('+1 day')) != $nDate){
			$addDay++;
			$day			= date('d', strtotime($nDate));
			$cnt			= ($stat[$day]) ? $stat[$day] : 0;

			$dataForChart[]	= array($day.'일', $cnt);
			$maxValue		=  ($maxValue < $cnt) ? $cnt : $maxValue;

			$nDate	= date('Y-m-d', strtotime('+'.$addDay.' day', $start_time));
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart, 
			'maxValue'		=> $maxValue, 
		));

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}
}

/* End of file statistic.php */
/* Location: ./app/controllers/admin/statistic.php */