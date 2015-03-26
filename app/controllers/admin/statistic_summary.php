<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_summary extends admin_base {


	var $dataForChart	= array();
	var $tableData		= array();
	var $maxVisitor		= 0;
	var $maxMember		= 0;
	var $maxOrder		= 0;


	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('statsmodel');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_summary');
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
		$result = $this->usedmodel->used_service_check('statistic_summary_detail');
		if(!$result['type']){
			$this->template->assign('statistic_summary_detail_limit','Y');
		}

		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));
		$this->template->assign(array('service_code' => $this->config_system['service']['code']));

		$this->template->define(array('_summary_table'=>$this->skin."/statistic_summary/_summary_table.html"));
	}

	public function index()
	{
		redirect("/admin/statistic_summary/summary");		
	}

	public function summary(){

		// 지난 30일간 일별 통계 ( 방문, 가입, 매출의 일주일, 어제, 오늘 통계 포함 )
		$this->set_summary_30day();

		$this->set_visitor_stats();				// 전월 방문 통계
		$this->set_member_stats();				// 전월 가입 통계
		$this->set_order_stats();				// 전월 매출 통계

		$this->set_visitor_referer_stats();		// 방문 유입처 통계
		$this->set_member_referer_stats();		// 가입 유입처 통계
		$this->set_order_referer_stats();		// 매출 유입처 통계

		$this->set_goods_stats();				// 상품 매출 통계
		$this->set_category_stats();			// 상품 카테고리 통계
		$this->set_brand_stats();				// 상품 브랜드 통계

		$this->set_cart_stats();				// 장바구니 통계
		$this->set_wish_stats();				// 위시리스트 통계
		$this->set_keyword_stats();				// 검색어 통계


		$this->template->assign(array(
			'dataForChart'	=> $this->dataForChart, 
			'maxVisitor'	=> $this->maxVisitor, 
			'maxMember'		=> $this->maxMember, 
			'maxOrder'		=> $this->maxOrder, 
			'tableData'		=> $this->tableData, 
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function set_summary_30day(){

		$wday				= 7 + date('w');
		$wsTime				= strtotime(date('Y-m-d', strtotime('-'.$wday.' day')).' 00:00:00');
		$weTime				= strtotime(date('Y-m-d', strtotime('-'.($wday-6).' day')).' 23:59:59');

		## 최근 30일간 일별 방문 통계
		$this->tableData['visitor']['wTotal']	= 0;
		$this->tableData['visitor']['bTotal']	= 0;
		$this->tableData['visitor']['Total']	= 0;

		$params['sDate']	= date('Y-m-d', strtotime("+1 day", strtotime("-1 month")));
		$params['eDate']	= date('Y-m-d');
		$query				= $this->statsmodel->get_summary_visitor_stats($params);
		$visitor			= $query->result_array();
		if	($visitor){
			foreach($visitor as $k => $data){
				$nTime	= strtotime($data['stats_date']);
				$date	= date('d', $nTime);
				if	($wsTime <= $nTime && $nTime <= $weTime)
					$this->tableData['visitor']['wTotal']	+= $data['count_sum'];

				if	(date('Ymd', strtotime('-1 day')) == date('Ymd', $nTime))
					$this->tableData['visitor']['bTotal']	= $data['count_sum'];

				if	(date('Ymd') == date('Ymd', $nTime))
					$this->tableData['visitor']['Total']		= $data['count_sum'];

				$visitArr[$date]	= $data['count_sum'];
			}
		}

		## 최근 30일간 일별 가입 통계
		$this->tableData['member']['wTotal']	= 0;
		$this->tableData['member']['bTotal']	= 0;
		$this->tableData['member']['Total']		= 0;

		$query				= $this->statsmodel->get_summary_member_stats($params);
		$member				= $query->result_array();
		if	($member){
			foreach($member as $k => $data){
				$nTime	= strtotime($data['date']);
				$date	= date('d', $nTime);
				if	($wsTime <= $nTime && $nTime <= $weTime)
					$this->tableData['member']['wTotal']	+= $data['cnt'];

				if	(date('Ymd', strtotime('-1 day')) == date('Ymd', $nTime))
					$this->tableData['member']['bTotal']	= $data['cnt'];

				if	(date('Ymd') == date('Ymd', $nTime))
					$this->tableData['member']['Total']		= $data['cnt'];

				$memberArr[$date]		= $data['cnt'];
			}
		}

		## 최근 30일간 일별 매출 통계
		$this->tableData['order']['wTotal']	= 0;
		$this->tableData['order']['bTotal']	= 0;
		$this->tableData['order']['Total']	= 0;

		$query				= $this->statsmodel->get_summary_order_stats($params);
		$order				= $query->result_array();
		if	($order){
			foreach($order as $k => $data){
				$nTime	= strtotime($data['date']);
				$date	= date('d', $nTime);
				if	($wsTime <= $nTime && $nTime <= $weTime)
					$this->tableData['order']['wTotal']	+= $data['price'];

				if	(date('Ymd', strtotime('-1 day')) == date('Ymd', $nTime))
					$this->tableData['order']['bTotal']	= $data['price'];

				if	(date('Ymd') == date('Ymd', $nTime))
					$this->tableData['order']['Total']	= $data['price'];

				$orderArr[$date]		= $data['price'];
			}
		}

		$nDate	= $params['sDate'];
		while (date('Ymd', strtotime('-1 day', strtotime($nDate))) != date('Ymd')){
			$dk		= date('d', strtotime($nDate));
			$vcnt	= ($visitArr[$dk]) ? $visitArr[$dk] : 0;
			$mcnt	= ($memberArr[$dk]) ? $memberArr[$dk] : 0;
			$price	= ($orderArr[$dk]) ? $orderArr[$dk] : 0;

			$this->dataForChart['방문'][]		= array($dk.'일', $vcnt);
			$this->dataForChart['가입'][]		= array($dk.'일', $mcnt);
			$this->dataForChart['매출'][]		= array($dk.'일', $price);

			$this->maxVisitor	= ($this->maxVisitor < $vcnt)	? $vcnt		: $this->maxVisitor;
			$this->maxMember	= ($this->maxMember < $mcnt)	? $mcnt		: $this->maxMember;
			$this->maxOrder		= ($this->maxOrder < $price)	? $price	: $this->maxOrder;

			$nDate	= date('Y-m-d', strtotime("+1 day", strtotime($nDate)));
		}
	}

	public function set_visitor_stats(){

		$this->tableData['visitor']['title']	= '방문자';

		$bdate					= date('Y-m', strtotime('-1 month'));
		$params['sDate']		= $bdate . '-01';
		$params['eDate']		= $bdate . '-' . date('t', strtotime($params['sdate']));
		$params['stats_type']	= 'total';

		## 지난달 방문 통계
		$query					= $this->statsmodel->get_summary_visitor_stats($params);
		$visitor				= $query->result_array();
		$this->tableData['visitor']['mTotal']	= $visitor[0]['total'];
		$this->tableData['visitor']['mPer']		= round($visitor[0]['total'] / date('t', strtotime($params['sDate'])), 1);
		$this->tableData['visitor']['wPer']		= round($this->tableData['visitor']['wTotal'] / 7, 1);

		$this->set_line_best('visitor');
	}

	public function set_member_stats(){

		$this->tableData['member']['title']		= '회원가입';

		$bdate					= date('Y-m', strtotime('-1 month'));
		$params['sDate']		= $bdate . '-01';
		$params['eDate']		= $bdate . '-' . date('t', strtotime($params['sdate']));
		$params['stats_type']	= 'total';

		## 지난달 가입 통계
		$query					= $this->statsmodel->get_summary_member_stats($params);
		$member					= $query->result_array();
		$this->tableData['member']['mTotal']	= $member[0]['total'];
		$this->tableData['member']['mPer']		= round($member[0]['total'] / date('t', strtotime($params['sDate'])), 1);
		$this->tableData['member']['wPer']		= round($this->tableData['member']['wTotal'] / 7, 1);

		$this->set_line_best('member');
	}

	public function set_order_stats(){

		$this->tableData['order']['title']	= '매출';

		$bdate					= date('Y-m', strtotime('-1 month'));
		$params['sDate']		= $bdate . '-01';
		$params['eDate']		= $bdate . '-' . date('t', strtotime($params['sdate']));
		$params['stats_type']	= 'total';

		## 지난달 매출 통계
		$query					= $this->statsmodel->get_summary_order_stats($params);
		$order					= $query->result_array();
		$this->tableData['order']['mTotal']	= $order[0]['total'];
		$this->tableData['order']['mPer']	= round($order[0]['total'] / date('t', strtotime($params['sDate'])), 1);
		$this->tableData['order']['wPer']	= round($this->tableData['order']['wTotal'] / 7, 1);

		$this->set_line_best('order');
	}

	public function set_visitor_referer_stats(){

		$this->tableData['vreferer']['title']	= '방문 유입처';

		$params['addParam']		= array('stats_type' => 'referer');
		$params['functionName']	= 'get_summary_visitor_stats';
		$params['group_name']	= 'vreferer';
		$params['nameFld']		= 'referer_name';
		$params['cntFld']		= 'cnt';

		## 지난달 방문유입처
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 방문유입처
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 방문유입처
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 방문유입처
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('vreferer');
	}

	public function set_member_referer_stats(){

		$this->tableData['mreferer']['title']	= '회원가입 유입처';

		$params['addParam']		= array('stats_type' => 'referer');
		$params['functionName']	= 'get_summary_member_stats';
		$params['group_name']	= 'mreferer';
		$params['nameFld']		= 'referer_name';
		$params['cntFld']		= 'cnt';

		## 지난달 가입유입처
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 가입유입처
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 가입유입처
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 가입유입처
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('mreferer');
	}

	public function set_order_referer_stats(){

		$this->tableData['oreferer']['title']	= '매출 유입처';

		$params['addParam']		= array('stats_type' => 'referer');
		$params['functionName']	= 'get_summary_order_stats';
		$params['group_name']	= 'oreferer';
		$params['nameFld']		= 'referer_name';
		$params['cntFld']		= 'price';

		## 지난달 매출유입처
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 매출유입처
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 매출유입처
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 매출유입처
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('oreferer');
	}

	public function set_goods_stats(){

		$this->tableData['goods']['title']	= '상품 매출';

		$params['functionName']	= 'get_summary_goods_stats';
		$params['group_name']	= 'goods';
		$params['nameFld']		= 'goods_name';
		$params['cntFld']		= 'price';

		## 지난달 상품매출
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 상품매출
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 상품매출
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 상품매출
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('goods');
	}

	public function set_category_stats(){

		$this->tableData['catagory']['title']	= '카테고리 매출';

		$params['functionName']	= 'get_summary_category_stats';
		$params['group_name']	= 'catagory';
		$params['nameFld']		= 'title';
		$params['cntFld']		= 'price';

		## 지난달 카테고리매출
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 카테고리매출
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 카테고리매출
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 카테고리매출
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('catagory');
	}

	public function set_brand_stats(){

		$this->tableData['brand']['title']	= '브랜드 매출';

		$params['functionName']	= 'get_summary_brand_stats';
		$params['group_name']	= 'brand';
		$params['nameFld']		= 'title';
		$params['cntFld']		= 'price';

		## 지난달 브랜드매출
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 브랜드매출
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 브랜드매출
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 브랜드매출
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('brand');
	}

	public function set_cart_stats(){

		$this->tableData['cart']['title']	= '장바구니';

		$params['functionName']	= 'get_summary_cart_stats';
		$params['group_name']	= 'cart';
		$params['nameFld']		= 'goods_name';
		$params['cntFld']		= 'cnt';

		## 지난달 장바구니
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 장바구니
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 장바구니
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 장바구니
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('cart');
	}

	public function set_wish_stats(){

		$this->tableData['wish']['title']	= '위시리스트';

		$params['functionName']	= 'get_summary_wish_stats';
		$params['group_name']	= 'wish';
		$params['nameFld']		= 'goods_name';
		$params['cntFld']		= 'cnt';

		## 지난달 위시리스트
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 위시리스트
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 위시리스트
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 위시리스트
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('wish');
	}

	public function set_keyword_stats(){

		$this->tableData['keyword']['title']	= '검색어';

		$params['functionName']	= 'get_summary_keyword_stats';
		$params['group_name']	= 'keyword';
		$params['nameFld']		= 'keyword';
		$params['cntFld']		= 'cnt';

		## 지난달 위시리스트
		$params['dateType']		= 'm';
		$retArr	= $this->set_summary_table_data($params);

		## 지난주 위시리스트
		$params['dateType']		= 'w';
		$retArr	= $this->set_summary_table_data($params);

		## 어제 위시리스트
		$params['dateType']		= 'b';
		$retArr	= $this->set_summary_table_data($params);

		## 오늘 위시리스트
		$params['dateType']		= 'n';
		$retArr	= $this->set_summary_table_data($params);

		$this->set_line_best('keyword');
	}

	// 반복적인 Loop 작업을 함수로 처리
	public function set_summary_table_data($params){
		if($params) foreach($params as $k => $v){$$k	= $v;	}

		$retStr	= '';
		$retVal	= 0;
		$params	= $addParam;
		switch ($dateType){
			case 'm':
				$bdate				= date('Y-m', strtotime('-1 month'));
				$params['sDate']	= $bdate . '-01';
				$params['eDate']	= $bdate . '-' . date('t', strtotime($params['sdate']));
				$divVal				= date('t', strtotime($params['sDate']));
				$valKey				= 'mPer';
			break;

			case 'w':
				$wday				= 7 + date('w');
				$params['sDate']	= date('Y-m-d', strtotime('-'.$wday.' day'));
				$params['eDate']	= date('Y-m-d', strtotime('-'.($wday-6).' day'));
				$divVal				= 7;
				$valKey				= 'wPer';
			break;

			case 'b':
				$params['sDate']	= date('Y-m-d', strtotime('-1 day'));
				$params['eDate']	= date('Y-m-d', strtotime('-1 day'));
				$valKey				= 'bTotal';
			break;

			case 'n':
				$params['sDate']	= date('Y-m-d');
				$params['eDate']	= date('Y-m-d');
				$valKey				= 'Total';
			break;
		}

		$query				= $this->statsmodel->$functionName($params);
		$list				= $query->result_array();
		for ($k = 0; $k < 3; $k++){
			$data	= $list[$k];
			if	($data[$cntFld]){
				$val	= ($divVal > 0)	? round(($data[$cntFld] / $divVal), 1) : $data[$cntFld];

				if	($k == 0)	$retStr	.= '<div class="redFont">';
				else			$retStr	.= '<div>';

				$retStr	.= htmlspecialchars($data[$nameFld]);
				if	($divVal > 0)	$retStr	.= ' (' . number_format($val, 1) . ')';
				else				$retStr	.= ' (' . number_format($val) . ')';

				$retStr	.= '</div>';

				$retVal	= ($retVal < $val) ? $val : $retVal;
			}
		}

		$this->tableData[$group_name][$dateType.'Str']	= $retStr;
		$this->tableData[$group_name][$valKey]			= $retVal;
	}

	// 반복적인 best 선정작업 함수로 처리
	public function set_line_best($arr_name){
		$best							= 'mPer';
		if		($this->tableData[$arr_name][$best] <= $this->tableData[$arr_name]['wPer'])
			$best	= 'wPer';
		if	($this->tableData[$arr_name][$best] <= $this->tableData[$arr_name]['bTotal'])
			$best	= 'bTotal';
		if	($this->tableData[$arr_name][$best] <= $this->tableData[$arr_name]['Total'])
			$best	= 'Total';
		$this->tableData[$arr_name]['best']	= $best;
	}
}

/* End of file statistic_promotion.php */
/* Location: ./app/controllers/admin/statistic_promotion.php */