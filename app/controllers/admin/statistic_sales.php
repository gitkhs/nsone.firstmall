<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_sales extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('statsmodel');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_sales');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('statistic_sales_detail');
		if(!$result['type']){
			$this->template->assign('statistic_sales_detail_limit','Y');
		}

		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));

		/* 매출통계 메뉴 */
		$this->template->define(array('sales_menu'=>$this->skin."/statistic_sales/_sales_menu.html"));
		$sales_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		$this->template->assign(array('selected_sales_menu'=>$sales_menu));

		//판매환경
		$this->sitetypeloop = sitetype($_GET['sitetype'], 'image', 'array');
		if(!count($_GET)) $_GET['sitetype'] = array_keys($this->sitetypeloop);

		$this->template->assign(array(
			'service_code' => $this->config_system['service']['code'], 
			'sitetype'=>$_GET['sitetype'],
			'sitetypeloop'=>$this->sitetypeloop
		));
	}

	public function index()
	{
		redirect("/admin/statistic_sales/sales_sales");
	}

	public function sales_sales(){

		if	(!$_GET['date_type'])	$_GET['date_type']	= 'month';
		$file_path	= $this->template_path();
		switch($_GET['date_type']){
			case 'daily':
				$this->sales_daily();
				$file_path	= str_replace('sales_sales.html', 'sales_daily.html', $file_path);
			break;
			case 'hour':
				$this->sales_hour();
				$file_path	= str_replace('sales_sales.html', 'sales_hour.html', $file_path);
			break;
			case 'month':
			default:
				$this->sales_monthly();
				$file_path	= str_replace('sales_sales.html', 'sales_monthly.html', $file_path);
			break;
		}

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sales_goods(){

		// 오늘일자 포함 시 오늘일자 데이터 갱신 :: 2014-08-20 lwh
		if($_GET['sdate'] <= date('Y-m-d') && $_GET['edate'] >= date('Y-m-d')){
			$renewal_res = $this->renewal_goods(date('Y-m-d'));
		}

		if	(!$_GET['sc_type'])	$_GET['sc_type']	= 'goods';
		$file_path		= $this->template_path();
		switch($_GET['sc_type']){
			case 'daily':
				$this->goods_daily();
				$file_path	= str_replace('sales_goods.html', 'goods_daily.html', $file_path);
			break;
			case 'goods':
			default:
				$this->goods_goods();
				$file_path	= str_replace('sales_goods.html', 'goods_goods.html', $file_path);
			break;
		}

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 월별  매출 통계 */
	public function sales_monthly()
	{
		$params['year']		= (trim($_GET['year'])) ? trim($_GET['year'])	: date('Y');

		// 오늘일자 포함 시 오늘일자 데이터 갱신 :: 2014-08-20 lwh
		if($params['year'] == date('Y')){
			$renewal_res = $this->renewal_sales(date('Y-m-d'));
		}
		$params['sitetype']	= ($_GET['sitetype'])	? $_GET['sitetype']		: array();
		$statsData			= array();

		$params['q_type']	= 'order';
		$query	= $this->statsmodel->get_sales_sales_monthly_stats($params);
		foreach($query->result_array() as $row)
			$statsData[$row['stats_month']-1]	= is_array($statsData[$row['stats_month']-1]) ? array_merge($statsData[$row['stats_month']-1],$row) : $row;

		$params['q_type']	= 'refund';
		$query	= $this->statsmodel->get_sales_sales_monthly_stats($params);
		foreach($query->result_array() as $row)
			$statsData[$row['stats_month']-1]	= is_array($statsData[$row['stats_month']-1]) ? array_merge($statsData[$row['stats_month']-1],$row) : $row;

		/* 매출액, 매입금액평균, 순이익 계산 */
		foreach($statsData as $i => $row){
			// 주문금액
			$statsData[$i]['order_price'] = $row['month_settleprice_sum']+$row['month_emoney_use_sum']+$row['month_cash_use_sum'];
			// 할인합계
			$statsData[$i]['discount_price'] = $row['month_enuri_sum']+$row['month_coupon_sale_sum']+$row['month_promotion_code_sale_sum']+$row['month_fblike_sale_sum']+$row['month_mobile_sale_sum']+$row['month_member_sale_sum']+$row['month_referer_sale_sum'];
			// 매출액
			$statsData[$i]['sales_price'] = $row['month_settleprice_sum']+$row['month_emoney_use_sum']+$row['month_cash_use_sum']-$row['month_refund_price_sum'];
			// 매입원가
			$statsData[$i]['month_supply_price']	= $statsData[$i]['month_supply_price_sum']-$row['month_refund_supply_price_sum'];
			// 순이익
			$statsData[$i]['interests'] = $statsData[$i]['sales_price']-$statsData[$i]['month_supply_price'];
		}


		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

		$dataForChart = array();

		for($i=0;$i<$maxMonth;$i++){
			//$dataForChart['매출액'][$i] = ($i+1).'월';
			$dataForChart['매출액'][$i] = $statsData[$i]['sales_price']?$statsData[$i]['sales_price']:0;
		}

		for($i=0;$i<$maxMonth;$i++){
			//$dataForChart['순이익'][$i] = ($i+1).'월';
			$dataForChart['순이익'][$i] = $statsData[$i]['interests']?$statsData[$i]['interests']:0;
		}

		foreach($dataForChart as $k=>$v){
			foreach($dataForChart[$k] as $row){
				$maxValue = $maxValue < $row ? $row : $maxValue;
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		/* 월별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxMonth;$i++){
			$dataForTable[$i] = $statsData[$i];
		}
		foreach($dataForTable as $stats_month=>$row){
			foreach($row as $k=>$v){
				$dataForTableSum[$k] += $v;
			}
		}

		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
		));
	}

	/* 일별 매출 통계 */
	public function sales_daily()
	{
		$_GET['year']		= (trim($_GET['year']))		? trim($_GET['year'])	: date('Y');
		$_GET['month']		= (trim($_GET['month']))	? trim($_GET['month'])	: date('m');
		$params['year']		= $_GET['year'];
		$params['month']	= $_GET['month'];
		$params['sitetype']	= ($_GET['sitetype'])		? $_GET['sitetype']		: array();
		$statsData			= array();

		// 오늘일자 포함 시 오늘일자 데이터 갱신 :: 2014-08-20 lwh
		if($params['year'].$params['month'] == date('Yn')){
			$renewal_res = $this->renewal_sales(date('Y-m-d'));
		}

		$params['q_type']	= 'order';
		$query	= $this->statsmodel->get_sales_sales_daily_stats($params);
		foreach($query->result_array() as $row)
			$statsData[$row['stats_day']-1] = is_array($statsData[$row['stats_day']-1]) ? array_merge($statsData[$row['stats_day']-1],$row) : $row;

		$params['q_type']	= 'refund';
		$query	= $this->statsmodel->get_sales_sales_daily_stats($params);
		foreach($query->result_array() as $row)
			$statsData[$row['stats_day']-1] = is_array($statsData[$row['stats_day']-1]) ? array_merge($statsData[$row['stats_day']-1],$row) : $row;

		/* 매출액, 매입금액평균, 순이익 계산 */
		foreach($statsData as $i => $row){

			// 주문금액
			$statsData[$i]['order_price'] = $row['day_settleprice_sum']+$row['day_emoney_use_sum']+$row['day_cash_use_sum'];
			// 할인합계
			$statsData[$i]['discount_price'] = $row['day_enuri_sum']+$row['day_coupon_sale_sum']+$row['day_fblike_sale_sum']+$row['day_mobile_sale_sum']+$row['day_promotion_code_sale_sum']+$row['day_member_sale_sum']+$row['day_referer_sale_sum'];
			// 매출액
			$statsData[$i]['sales_price'] = $row['day_settleprice_sum']+$row['day_emoney_use_sum']+$row['day_cash_use_sum']-$row['day_refund_price_sum'];
			// 배송비
			$statsData[$i]['shipping_cost'] = $row['day_shipping_cost_sum'];
			// 총 배송비
			$statsData[$i]['shipping_cost_sum'] += $row['day_shipping_cost_sum'];
			// 매입원가
			$statsData[$i]['day_supply_price']	= $statsData[$i]['day_supply_price_sum']-$row['day_refund_supply_price_sum'];
			// 순이익
			$statsData[$i]['interests'] = $statsData[$i]['sales_price']-$statsData[$i]['day_supply_price'];
		}


		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

		$dataForChart = array();

		for($i=0;$i<$maxDay;$i++){
			//$dataForChart['매출액'][$i] = ($i+1).'월';
			$dataForChart['매출액'][$i] = $statsData[$i]['sales_price']?$statsData[$i]['sales_price']:0;
		}

		for($i=0;$i<$maxDay;$i++){
			//$dataForChart['순이익'][$i] = ($i+1).'월';
			$dataForChart['순이익'][$i] = $statsData[$i]['interests']?$statsData[$i]['interests']:0;
		}

		foreach($dataForChart as $k=>$v){
			foreach($dataForChart[$k] as $row){
				$maxValue = $maxValue < $row ? $row : $maxValue;
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue,
			'maxDay'		=> $maxDay
		));

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxDay;$i++){
			$dataForTable[$i] = $statsData[$i];
			foreach($dataForTable[$i] as $k=>$v){
				$dataForTableSum[$k] += $v;
			}
		}

		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
		));

		/* 일별 데이터 달력용 */
		$c_start_idx = date('w',strtotime("{$params['year']}-{$params['month']}-01"));
		$c_end_idx = date('t',strtotime("{$params['year']}-{$params['month']}-01"));
		$c_row = ceil(($c_start_idx+$c_end_idx)/7);

		$this->template->assign(array(
			'c_start_idx'	=> $c_start_idx,
			'c_end_idx'		=> $c_end_idx,
			'c_row'			=> $c_row,
		));
		$this->template->define(array('sales_daily_calendar'=>$this->skin."/statistic_sales/_sales_daily_calendar.html"));
	}

	/* 시간대별  매출 통계 */
	public function sales_hour(){

		$params['year']		= (trim($_GET['year']))		? trim($_GET['year'])	: date('Y');
		$params['month']	= (trim($_GET['month']))	? trim($_GET['month'])	: date('m');
		$params['sitetype']	= ($_GET['sitetype'])		? $_GET['sitetype']		: array();
		$statsData			= array();

		$query	= $this->statsmodel->get_sales_sales_hour_stats($params);
		foreach($query->result_array() as $row)
			$statsData[$row['stats_hour']] = $row;

		/* 데이터 가공 */
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		$count_total_sum = 0;

		$dataForChart = array();
		for($i=0;$i<24;$i++){
			$count_total_sum += $statsData[$i]['month_count_sum'];
			$dataForChart['건수'][$i][0] = $i;
			$dataForChart['건수'][$i][1] = $statsData[$i]['month_count_sum']?$statsData[$i]['month_count_sum']:0;
			$dataForChart['금액'][$i][0] = $i;
			$dataForChart['금액'][$i][1] = $statsData[$i]['month_settleprice_sum']?$statsData[$i]['month_settleprice_sum']:0;
		}

		$maxValue['건수'] = 0;
		$maxValue['금액'] = 0;
		foreach($dataForChart as $k=>$v){
			foreach($dataForChart[$k] as $row){
				$maxValue[$k] = $maxValue[$k] < $row[1] ? $row[1] : $maxValue[$k];
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue,
			'maxDay'		=> $maxDay
		));

		/* 테이블 */
		$dataForTable = array();
		for($i=0;$i<24;$i++){
			$dataForTable[$i] = $statsData[$i];
			$dataForTable[$i]['month_count_percent'] = $dataForTable[$i]['month_count_sum']?round($dataForTable[$i]['month_count_sum']/$count_total_sum*100):0;
		}

		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'arr_weekday'	=> $this->arr_weekday,
		));
	}

	/* 상품 일별 매출 통계 */
	public function goods_daily(){

		$_GET['sdate']			= (trim($_GET['sdate']))	? trim($_GET['sdate'])	: date('Y-m-01');
		$_GET['edate']			= (trim($_GET['edate']))	? trim($_GET['edate'])	: date('Y-m-d');
		$params['sdate']		= $_GET['sdate'];
		$params['edate']		= $_GET['edate'];
		$params['sort']			= (trim($_GET['sort']))		? trim($_GET['sort'])	: "deposit_ymd desc";
		$params['sitetype']		= ($_GET['sitetype'])		? $_GET['sitetype']		: array();
		$params['category1']	= trim($_GET['category1']);
		$params['category2']	= trim($_GET['category2']);
		$params['category3']	= trim($_GET['category3']);
		$params['category4']	= trim($_GET['category4']);
		$params['brands1']		= trim($_GET['brands1']);
		$params['brands2']		= trim($_GET['brands2']);
		$params['brands3']		= trim($_GET['brands3']);
		$params['brands4']		= trim($_GET['brands4']);
		$params['keyword']		= trim($_GET['keyword']);
		$statsData				= array();
		$statsDataSum			= array();
		$category_code			= max(array($_GET['category1'],$_GET['category2'],$_GET['category3'],$_GET['category4']));
		$brands_code				= max(array($_GET['brands1'],$_GET['brands2'],$_GET['brands3'],$_GET['brands4']));
		$params['category_code']	= $category_code;
		$params['brands_code']		= $brands_code;
		$search_mode				= 'order';
		if($category_code || $brands_code || $_GET['keyword'] )
			$search_mode = 'item';

		$params['q_type']	= 'list';
		$query	= $this->statsmodel->get_sales_goods_daily_stats($params);
		foreach($query->result_array() as $row) {
			$statsDataSum['goods_price']			+= $row['goods_price'];
			$statsDataSum['coupon_sale']			+= $row['coupon_sale'];
			$statsDataSum['member_sale']			+= $row['member_sale'];
			$statsDataSum['fblike_sale']			+= $row['fblike_sale'];
			$statsDataSum['mobile_sale']			+= $row['mobile_sale'];
			$statsDataSum['promotion_code_sale']	+= $row['promotion_code_sale'];
			$statsDataSum['referer_sale']			+= $row['referer_sale'];
			
			$statsData[] = $row;
		}

		// 전체 갯수 :: 2014-08-20 lwh
		$cnt_query	= $this->statsmodel->get_sales_goods_daily_stats($params,'cnt');
		$cntData	= $cnt_query->result_array();
		$listCnt	= count($cntData);

		$params['q_type']	= 'order';
		$query	= $this->statsmodel->get_sales_goods_daily_stats($params);
		list($orderData) = $query->result_array();
		$orderData['sales_price_sum'] = $orderData['ori_price_sum']
			+$orderData['shipping_cost_sum']
			+$orderData['goods_shipping_cost_sum']
			-$orderData['coupon_sale_sum']
			-$orderData['promotion_code_sale_sum']
			-$orderData['referer_sale_sum']
			-$orderData['emoney_use_sum']
			-$orderData['cash_use_sum']
			-$orderData['enuri_sum'];

		$params['q_type']	= 'refund';
		$query	= $this->statsmodel->get_sales_goods_daily_stats($params);
		list($refundData) = $query->result_array();
		$this->template->assign(array(
			'statsData'		=> $statsData,
			'statsDataSum'	=> $statsDataSum,
			'orderData'		=> $orderData,
			'refundData'	=> $refundData,
			'sc'			=> $_GET,
			'search_mode'	=> $search_mode,
			'listCnt'		=> $listCnt - 1
		));
	}

	/* 상품 상품별 매출 통계 */
	public function goods_goods(){

		$cfg_order = config_load('order');
		$statlist	= array();

		if	(!trim($_GET['sdate']) && !trim($_GET['edate'])){
			$_GET['sdate']	= date('Y-m').'-01';
			$_GET['edate']	= date('Y-m-d');
		}

		$params['sdate']		= trim($_GET['sdate']);
		$params['edate']		= trim($_GET['edate']);
		$params['keyword']		= trim($_GET['keyword']);
		$params['category1']	= trim($_GET['category1']);
		$params['category2']	= trim($_GET['category2']);
		$params['category3']	= trim($_GET['category3']);
		$params['category4']	= trim($_GET['category4']);
		$params['brands1']		= trim($_GET['brands1']);
		$params['brands2']		= trim($_GET['brands2']);
		$params['brands3']		= trim($_GET['brands3']);
		$params['brands4']		= trim($_GET['brands4']);
		$params['order_by']		= trim($_GET['order_by']);

		$statSql	= $this->statsmodel->get_sales_goods_stats($params);
		if	($statSql){
			foreach($statSql->result_array() as $k => $data){
				$lk = $k;
				$statlist[$k]							= $data;				
				
				$lank++;
				$statlist[$k]['goods_first']		= 'y';
				$statlist[$k]['lank']				= $lank;
				$statlist[$lk]['tstock']			+= $data['stock'];
				$statlist[$lk]['tbadstock']			+= $data['badstock'];
				$statlist[$lk]['treservation15']	+= $data['reservation15'];
				$statlist[$lk]['treservation25']	+= $data['reservation25'];
			}
		}
		
		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));
	}

	public function sales_etc(){

		$this->load->helper('zipcode');

		$_GET['year']		= (trim($_GET['year']))		? trim($_GET['year'])	: date('Y');
		$_GET['month']		= (trim($_GET['month']))	? trim($_GET['month'])	: date('m');
		$params['year']		= $_GET['year'];
		$params['month']	= $_GET['month'];
		$params['sitetype']	= ($_GET['sitetype'])		? $_GET['sitetype']		: array();
		$statsData			= array();
		$this->arr_age		= array('10대 이하','20대','30대','40대','50대','60대 이상');
		$this->arr_sex		= array('남','여');
	    $ZIP_DB				= get_zipcode_db();
		$this->arr_location = array();
		$query = $ZIP_DB->query("SELECT substring(SIDO,1,2) as SIDO FROM `zipcode` GROUP BY SIDO");
		foreach($query->result_array() as $row){
			$this->arr_location[] = $row['SIDO'];
		}

		$params['q_type']	= 'sexage';
		$query	= $this->statsmodel->get_sales_etc_stats($params);
		foreach($query->result_array() as $row)
			$statsData[$row['buyer_sex']][$row['buyer_age']] = $row;

		/* 데이터 가공 */
		$maxDay				= date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		$count_total_sum	= 0;

		$idx = 0;
		foreach($this->arr_sex as $sex){
			foreach($this->arr_age as $age){
				$count_total_sum	+= $statsData[$sex][$age]['month_count_sum'];
				$idx++;
			}
		}

		/* 일별 데이터 테이블 */
		$count_total_sum;
		$dataForTable1 = array();
		$dataForTableSum = array();
		foreach($this->arr_sex as $sex){
			foreach($this->arr_age as $age){
				$dataForTable1[$sex][$age] = $statsData[$sex][$age];
				$dataForTableSum[$age]['month_count_sum'] += $statsData[$sex][$age]['month_count_sum'];
				$dataForTableSum[$age]['month_settleprice_sum'] += $statsData[$sex][$age]['month_settleprice_sum'];
				$dataForTableSum[$age]['month_count_percent'] = $dataForTableSum[$age]['month_count_sum']?round($dataForTableSum[$age]['month_count_sum']/$count_total_sum*100):0;
			}
		}

		$dataForChart1	= array();
		$idx = 0;
		foreach($this->arr_age as $age){
			$dataForChart1['건수'][$idx][0] = $age;
			$dataForChart1['건수'][$idx][1] = $dataForTableSum[$age]['month_count_sum']?$dataForTableSum[$age]['month_count_sum']:0;
			$dataForChart1['금액'][$idx][0] = $age;
			$dataForChart1['금액'][$idx][1] = $dataForTableSum[$age]['month_settleprice_sum']?$dataForTableSum[$age]['month_settleprice_sum']:0;
			$idx++;
		}

		$params['q_type']	= 'location';
		$query	= $this->statsmodel->get_sales_etc_stats($params);
		foreach($query->result_array() as $row) $statsData[$row['location']] = $row;

		/* 데이터 가공 */
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		$count_total_sum = 0;

		$dataForChart2 = array();
		$idx = 0;
		foreach($this->arr_location as $v){
			$count_total_sum += $statsData[$v]['month_count_sum'];
			$dataForChart2['건수'][$idx][0] = $v;
			$dataForChart2['건수'][$idx][1] = $statsData[$v]['month_count_sum']?$statsData[$v]['month_count_sum']:0;
			$dataForChart2['금액'][$idx][0] = $v;
			$dataForChart2['금액'][$idx][1] = $statsData[$v]['month_settleprice_sum']?$statsData[$v]['month_settleprice_sum']:0;
			$idx++;
		}

		$maxValue['건수'] = 0;
		$maxValue['금액'] = 0;
		foreach($dataForChart2 as $k=>$v){
			foreach($dataForChart2[$k] as $row){
				$maxValue[$k] = $maxValue[$k] < $row[1] ? $row[1] : $maxValue[$k];
			}
		}

		/* 일별 데이터 테이블 */
		$dataForTable2 = array();
		foreach($this->arr_location as $v){
			$dataForTable2[$v] = $statsData[$v];
			$dataForTable2[$v]['month_count_percent'] = $dataForTable2[$v]['month_count_sum']?round($dataForTable2[$v]['month_count_sum']/$count_total_sum*100):0;
		}

		// 성별/연령
		$this->template->assign(array(
			'dataForChart1'		=> $dataForChart1,
			'maxDay1'			=> $maxDay,
			'dataForTable1'		=> $dataForTable1,
			'dataForTableSum'	=> $dataForTableSum,
			'arr_sex'			=> $this->arr_sex,
			'arr_age'			=> $this->arr_age,
		));


		$this->template->assign(array(
			'dataForChart2'	=> $dataForChart2,
			'maxDay'		=> $maxDay,
			'maxValue'		=> $maxValue,
			'dataForTable2'	=> $dataForTable2,
			'arr_location'	=> $this->arr_location,
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 결제수단별  통계 */
	public function sales_payment(){

		$this->arr_payment = config_load('payment');

		/* 날짜 파라미터 */
		$_GET['year']		= (trim($_GET['year']))		? trim($_GET['year'])	: date('Y');
		$_GET['month']		= (trim($_GET['month']))	? trim($_GET['month'])	: date('m');
		$params['year']		= $_GET['year'];
		$params['month']	= $_GET['month'];
		$params['sitetype'] = !empty($_GET['sitetype']) ? $_GET['sitetype'] : array();
		$statsData			= array();

		$query	= $this->statsmodel->get_sales_payment_stats($params);
		foreach($query->result_array() as $row){
			if($row['pgs'] == 'kakaopay'){
				$statsData[$row['pgs']] = $row;
			}else{
				$statsData[$row['payment']] = $row;
			}
		}

		/* 데이터 가공 */
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		$count_total_sum = 0;

		$dataForChart = array();
		$idx = 0;
		foreach($this->arr_payment as $k=>$v){
			$count_total_sum += $statsData[$k]['month_count_sum'];
			$dataForChart['건수'][$idx][0] = $v;
			$dataForChart['건수'][$idx][1] = $statsData[$k]['month_count_sum']?$statsData[$k]['month_count_sum']:0;
			$dataForChart['금액'][$idx][0] = $v;
			$dataForChart['금액'][$idx][1] = $statsData[$k]['month_settleprice_sum']?$statsData[$k]['month_settleprice_sum']:0;
			$idx++;
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxDay'		=> $maxDay
		));

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		foreach($this->arr_payment as $k=>$v){
			$dataForTable[$k] = $statsData[$k];
			$dataForTable[$k]['month_count_percent'] = $dataForTable[$k]['month_count_sum']?round($dataForTable[$k]['month_count_sum']/$count_total_sum*100):0;
		}

		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'arr_payment'	=> $this->arr_payment,
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 판매환경별  매출 통계 */
	public function sales_platform(){

		/* 날짜 파라미터 */
		$_GET['year']		= (trim($_GET['year']))		? trim($_GET['year'])	: date('Y');
		$_GET['month']		= (trim($_GET['month']))	? trim($_GET['month'])	: date('m');
		if($_GET['month']=='all')	$_GET['month'] = '';
		$params['year']		= $_GET['year'];
		$params['month']	= $_GET['month'];
		$params['sitetype'] = !empty($_GET['sitetype']) ? $_GET['sitetype'] : array();
		$statsData			= array();

		$query	= $this->statsmodel->get_sales_platform_stats($params);
		foreach($query->result_array() as $row) {
			$statsData[$row['sitetype']] = $row;
			$statsData[$row['sitetype']]['sitetype_name'] = $this->sitetypeloop[$row['sitetype']]['name'];
		}

		/* 데이터 가공 */
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		$count_total_sum = 0;

		$idx = 0;
		foreach($this->sitetypeloop as $sitetype=>$v){
			$count_total_sum += $statsData[$sitetype]['count_sum'];
			$idx++;
		}

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		foreach($this->sitetypeloop as $sitetype=>$v){
			$dataForTable[$sitetype] = $statsData[$sitetype];
			$dataForTable[$sitetype]['count_percent'] = $dataForTable[$sitetype]['count_sum']?round($dataForTable[$sitetype]['count_sum']/$count_total_sum*100):0;
		}

		$dataForChart = array();
		$idx=0;
		foreach($this->sitetypeloop as $sitetype=>$v){
			$dataForChart['금액'][$idx][0] = $this->sitetypeloop[$sitetype]['name'];
			$dataForChart['금액'][$idx][1] = $statsData[$sitetype]['settleprice_sum'] ? $statsData[$sitetype]['settleprice_sum'] : 0;
			$dataForChart['건수'][$idx][0] = $this->sitetypeloop[$sitetype]['name'];
			$dataForChart['건수'][$idx][1] = $statsData[$sitetype]['count_sum'] ? $statsData[$sitetype]['count_sum'] : 0;
			$idx++;
		}

		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'dataForChart' => $dataForChart,
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sales_referer(){

		$cfg_order		= config_load('order');

		$_GET['dateSel_type']	= !empty($_GET['dateSel_type'])	? $_GET['dateSel_type']	: 'month';
		$_GET['year']			= !empty($_GET['year'])			? $_GET['year']			: date('Y');
		$_GET['month']			= !empty($_GET['month'])		? $_GET['month']		: date('m');
		$params['year']			= $_GET['year'];
		$params['month']		= $_GET['month'];
		$params['dateSel_type']	= $_GET['dateSel_type'];
		$statlist				= array();

		$query		= $this->statsmodel->get_sales_referer_stats($params);
		$statlist	= $query->result_array();
		if	($statlist)	foreach($statlist as $key => $data){
			$stat[$data['referer_name']][$data['date']]	= $data;
		}

		$sitecdArr	= array_keys($stat);
		unset($statlist);
		if	($_GET['dateSel_type'] == 'daily'){
			$end_day	= date('t', strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
			for	($d = 1; $d <= $end_day; $d++){
				$dk	= str_pad($d, 2, "0", STR_PAD_LEFT);
				foreach($sitecdArr as $k => $v){
					$cnt	= ($stat[$v][$dk]['cnt'])	? $stat[$v][$dk]['cnt']		: '0';
					$price	= ($stat[$v][$dk]['price'])	? floor($stat[$v][$dk]['price']/1000)	: '0';
					$statlist[$v]['list'][$d]['referer_name']	= $v;
					$statlist[$v]['list'][$d]['cnt']			= $cnt;
					$statlist[$v]['list'][$d]['price']			= $price;
					$statlist[$v]['total_cnt']					+= $cnt;
					$statlist[$v]['total_price']				+= $price;

					if	($maxCnt < $cnt)		$maxCnt			= $cnt;
					if	($maxPrice < $price)	$maxPrice		= $price;

					$dataForChart['cnt'][$v][]					= array($d.'일', $cnt);
					$dataForChart['price'][$v][]				= array($d.'일', $price);
				}

				$table_title[]	= $d.'일';
			}

		}else{
			for	($m = 1; $m <= 12; $m++){
				$mk	= str_pad($m, 2, "0", STR_PAD_LEFT);
				foreach($sitecdArr as $k => $v){
					$cnt	= ($stat[$v][$mk]['cnt'])	? $stat[$v][$mk]['cnt']		: '0';
					$price	= ($stat[$v][$mk]['price'])	? floor($stat[$v][$mk]['price']/1000)	: '0';
					$statlist[$v]['list'][$m]['referer_name']	= $v;
					$statlist[$v]['list'][$m]['cnt']			= $cnt;
					$statlist[$v]['list'][$m]['price']			= $price;
					$statlist[$v]['total_cnt']					+= $cnt;
					$statlist[$v]['total_price']				+= $price;

					if	($maxCnt < $cnt)		$maxCnt			= $cnt;
					if	($maxPrice < $price)	$maxPrice		= $price;

					$dataForChart['cnt'][$v][]					= array($m.'월', $cnt);
					$dataForChart['price'][$v][]				= array($m.'월', $price);
				}

				$table_title[]	= $m.'월';
			}
		}

		$this->template->define(array('referer_cnt_table'=>$this->skin."/statistic_sales/_referer_cnt_table.html"));
		$this->template->define(array('referer_price_table'=>$this->skin."/statistic_sales/_referer_price_table.html"));
		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('table_title'=>$table_title));
		$this->template->assign(array('marketplace'=>$marketplace));
		$this->template->assign(array('maxCnt'=>$maxCnt));
		$this->template->assign(array('maxPrice'=>$maxPrice));
		$this->template->assign(array('dataForChart'=>$dataForChart));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('marketplace'=>$marketplace));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sales_category(){

		$_GET['sc_type']		= !empty($_GET['sc_type'])		? $_GET['sc_type']		: 'category';
		$_GET['dateSel_type']	= !empty($_GET['dateSel_type'])	? $_GET['dateSel_type']	: 'month';
		$_GET['year']			= !empty($_GET['year'])			? $_GET['year']			: date('Y');
		$params['year']			= $_GET['year'];
		$params['month']		= $_GET['month'];
		$params['sc_type']		= $_GET['sc_type'];
		$params['dateSel_type']	= $_GET['dateSel_type'];

		// 오늘일자 포함 시 오늘일자 데이터 갱신 :: 2014-08-20 lwh
		if($params['dateSel_type'] == 'daily'){
			if($params['year'].$params['month'] == date('Yn')){
				$renewal_res = $this->renewal_cate(date('Y-m-d'));
			}
		}else{ // month
			if($params['year'] == date('Y')){
				$renewal_res = $this->renewal_cate(date('Y-m-d'));
			}
		}

		$query		= $this->statsmodel->get_sales_category_stats($params);
		$statlist	= $query->result_array();
		if	($statlist)	foreach($statlist as $key => $data){
			$stat[$data['category_name']][$data['date']]	= $data;
		}

		$codeArr	= array_keys($stat);
		unset($statlist);
		if	($_GET['dateSel_type'] == 'daily'){
			$end_day	= date('t', strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
			for	($d = 1; $d <= $end_day; $d++){
				$dk	= str_pad($d, 2, "0", STR_PAD_LEFT);
				foreach($codeArr as $k => $v){
					$cnt	= ($stat[$v][$dk]['cnt'])	? $stat[$v][$dk]['cnt']		: '0';
					$price	= ($stat[$v][$dk]['price'])	? floor($stat[$v][$dk]['price']/1000)	: '0';

					$statlist[$v]['list'][$d]['category_name']	= $v;
					$statlist[$v]['list'][$d]['cnt']			= $cnt;
					$statlist[$v]['total_cnt']					+= $cnt;
					if	($maxPrice < $price)	$maxPrice		= $price;
					$dataForChart[$v][]							= array($d.'일', $price);
				}

				$table_title[]	= $d.'일';
			}

		}else{
			for	($m = 1; $m <= 12; $m++){
				$mk	= str_pad($m, 2, "0", STR_PAD_LEFT);
				foreach($codeArr as $k => $v){
					$cnt	= ($stat[$v][$mk]['cnt'])	? $stat[$v][$mk]['cnt']		: '0';
					$price	= ($stat[$v][$mk]['price'])	? floor($stat[$v][$mk]['price']/1000)	: '0';

					$statlist[$v]['list'][$m]['category_name']	= $v;
					$statlist[$v]['list'][$m]['cnt']			= $cnt;
					$statlist[$v]['total_cnt']					+= $cnt;
					if	($maxPrice < $price)	$maxPrice		= $price;
					$dataForChart[$v][]							= array($m.'월', $price);
				}

				$table_title[]	= $m.'월';
			}
		}

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('table_title'=>$table_title));
		$this->template->assign(array('maxPrice'=>$maxPrice));
		$this->template->assign(array('dataForChart'=>$dataForChart));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}

	/* 구매통계-상품 상품별 데이터통계 옵션 호출 :: 2014-08-04 lwh */
	public function sales_option_ajax(){

		$params['sdate']		= trim($_POST['sdate']);
		$params['edate']		= trim($_POST['edate']);
		$params['goods_seq']	= trim($_POST['goods_seq']);
		$params['order_by']		= trim($_POST['order_by']);

		$opt_data	= $this->statsmodel->get_sales_option_stats($params);

		echo json_encode($opt_data);
	}

	/* 구매통계-상품 일별 데이터 통계 데이터 갱신하기 :: 2014-08-05 lwh */
	public function renewal_goods($seldate){
		$result['flag'] = false;

		// 데이터 설정
		$sdate = $seldate;
		$edate = $seldate;

		$daily_data	= $this->statsmodel->get_daily_sales_stats($sdate,$edate);

		// 넣을 데이터 삭제
		$this->statsmodel->delete_accumul_stats_sales($sdate,$edate);

		foreach($daily_data as $k => $sel_data){
			$result['flag'] = true;
			$this->statsmodel->set_accumul_stats_sales($sel_data);
		}

		return $result;
	}

	/* 구매통계-상품 일별 데이터 페이징 :: 2014-08-05 lwh */
	public function goods_daily_pagin(){

		parse_str($_POST['queryString']);
		$params['sdate']		= $sdate	? trim($sdate) : date('Y-m-01');
		$params['edate']		= $edate	? trim($edate) : date('Y-m-d');
		$params['sort']			= ($sort)	? trim($sort) : "deposit_ymd desc";
		$params['sitetype']		= ($sitetype)	? $sitetype	: array();
		$params['category1']	= trim($category1);
		$params['category2']	= trim($category2);
		$params['category3']	= trim($category3);
		$params['category4']	= trim($category4);
		$params['brands1']		= trim($brands1);
		$params['brands2']		= trim($brands2);
		$params['brands3']		= trim($brands3);
		$params['brands4']		= trim($brands4);
		$params['keyword']		= trim($keyword);
		$statsData				= array();
		$statsDataSum			= array();
		$category_code			= max(array($category1,$category2,$category3,$category4));
		$brands_code			= max(array($brands1,$brands2,$brands3,$brands4));
		$params['category_code']	= $category_code;
		$params['brands_code']		= $brands_code;

		// 페이징 
		$params['start_page']	= ($_POST['npage'] - 1) * $_POST['nnum'] + 1;
		$params['end_page']		= $_POST['nnum'];

		$listData	= $this->statsmodel->get_sales_goods_daily_pagin($params);

		$this->template->assign(array('st_index'=>$params['start_page']));
		$this->template->assign(array('list_loop'=>$listData));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 구매통계-매출 데이터 통계 데이터 갱신하기 :: 2014-08-08 lwh */
	public function renewal_sales($seldate){
		$result['flag1'] = false;
		$result['flag2'] = false;

		// 데이터 설정
		$sdate = $seldate;
		$edate = $seldate;

		/* 매출 데이터 갱신 */
		$daily_data	= $this->statsmodel->get_sales_mdstats($sdate,$edate);

		// 넣을 데이터 삭제
		$this->statsmodel->delete_accumul_sales_mdstats($sdate,$edate);

		foreach($daily_data as $k => $sel_data){
			$result['flag1'] = true;
			$this->statsmodel->set_accumul_sales_mdstats($sel_data);
		}

		unset($daily_data);
		unset($sel_data);

		/* 환불 데이터 갱신 */
		$daily_data	= $this->statsmodel->get_sales_refund($sdate,$edate);

		// 넣을 데이터 삭제
		$this->statsmodel->delete_accumul_sales_refund($sdate,$edate);

		foreach($daily_data as $k => $sel_data){
			$result['flag2'] = true;
			$this->statsmodel->set_accumul_sales_refund($sel_data);
		}

		return $result;
	}

	/* 구매통계-카테고리/브랜드 통계 데이터 갱신하기 :: 2014-08-11 lwh */
	public function renewal_cate($seldate){
		$result['flag1'] = false;
		$result['flag2'] = false;

		// 데이터 설정
		$sdate = $seldate;
		$edate = $seldate;

		$daily_data_C	= $this->statsmodel->get_sales_category('C',$sdate,$edate);

		$daily_data_B	= $this->statsmodel->get_sales_category('B',$sdate,$edate);

		// 넣을 데이터 삭제
		$this->statsmodel->delete_accumul_sales_category($sdate,$edate);

		foreach($daily_data_C as $k => $sel_data){
			$sel_data['t_type'] = 'C';
			$result['flag1'] = true;
			$this->statsmodel->set_accumul_sales_category($sel_data);
		}

		foreach($daily_data_B as $k => $sel_data){
			$sel_data['t_type'] = 'B';
			$result['flag2'] = true;
			$this->statsmodel->set_accumul_sales_category($sel_data);
		}

		return $result;
	}
}

/* End of file statistic_sales.php */
/* Location: ./app/controllers/admin/statistic_sales.php */