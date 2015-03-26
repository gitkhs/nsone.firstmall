<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_promotion extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));

		/* 프로모션통계 메뉴 */
		$this->template->define(array('promotion_menu'=>$this->skin."/statistic_promotion/_promotion_menu.html"));
		$promotion_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		if($promotion_menu=='promotion_hourly') $promotion_menu = 'promotion_hourly';
		$this->template->assign(array('selected_promotion_menu'=>$promotion_menu));

		//판매환경
		$this->sitetypeloop = sitetype($_GET['sitetype'], 'image', 'array');
		if(!count($_GET)) $_GET['sitetype'] = array_keys($this->sitetypeloop);

		$this->template->assign(array(
			'sitetype'=>$_GET['sitetype'],
			'sitetypeloop'=>$this->sitetypeloop
		));

		if( !$this->isplusfreenot ){
			pageBack('무료몰Plus+에서는 프로모션통계를 지원하지 않는 기능입니다.\n프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 프로모션통계을 이용 가능합니다.');
			exit;
		}
	}

	public function index()
	{
		redirect("/admin/statistic_promotion/promotion_coupon_monthly");
	}

	/* 프로모션통계 - 쿠폰 월별 분석 */
	public function promotion_coupon_monthly()
	{
		$this->admin_menu();
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');

		/* 데이터 추출 */
		$sql = "
			select
				year(use_date) as use_year,
				month(use_date) as use_month,
				day(use_date) as use_day,
				type,
				sum(if(type='download',1,0)) as count_download,
				sum(if(type='member',1,0)) as count_member,
				sum(if(type='offline_coupon',1,0)) as count_offline,
				sum(if(type!='download' and type!='member' and type!='offline_coupon',1,0)) as count_etc
			from fm_download
			where use_status='used' and year(use_date)='{$_GET['year']}'
			group by use_month
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) $statsData[$row['use_month']-1] = is_array($statsData[$row['use_month']-1]) ? array_merge($statsData[$row['stats_month']-1],$row) : $row;

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

		$dataForChart = array();
		for($i=0;$i<$maxMonth;$i++){
			$dataForChart['count_download'][$i][0]	= ($i+1).'월';
			$dataForChart['count_download'][$i][1]	= (integer)$statsData[$i]['count_download'];
			$maxValue = $maxValue < $dataForChart['count_download'][$i][1] ? $dataForChart['count_download'][$i][1] : $maxValue;

			$dataForChart['count_member'][$i][0]	= ($i+1).'월';
			$dataForChart['count_member'][$i][1]	= (integer)$statsData[$i]['count_member'];
			$maxValue = $maxValue < $dataForChart['count_member'][$i][1] ? $dataForChart['count_member'][$i][1] : $maxValue;

			$dataForChart['count_offline'][$i][0]	= ($i+1).'월';
			$dataForChart['count_offline'][$i][1]	= (integer)$statsData[$i]['count_offline'];
			$maxValue = $maxValue < $dataForChart['count_offline'][$i][1] ? $dataForChart['count_offline'][$i][1] : $maxValue;

			$dataForChart['count_etc'][$i][0]		= ($i+1).'월';
			$dataForChart['count_etc'][$i][1]		= (integer)$statsData[$i]['count_etc'];
			$maxValue = $maxValue < $dataForChart['count_etc'][$i][1] ? $dataForChart['count_etc'][$i][1] : $maxValue;

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

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션통계 - 쿠폰 일별 분석 */
	public function promotion_coupon_daily()
	{
		$this->admin_menu();
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');

		/* 데이터 추출 */
		$sql = "
			select
				year(use_date) as use_year,
				month(use_date) as use_month,
				day(use_date) as use_day,
				type,
				sum(if(type='download',1,0)) as count_download,
				sum(if(type='member',1,0)) as count_member,
				sum(if(type='offline_coupon',1,0)) as count_offline,
				sum(if(type!='download' and type!='member' and type!='offline_coupon',1,0)) as count_etc
			from fm_download
			where use_status='used' and year(use_date)='{$_GET['year']}' and month(use_date)='{$_GET['month']}'
			group by use_day
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) $statsData[$row['use_day']-1] = is_array($statsData[$row['use_day']-1]) ? array_merge($statsData[$row['use_day']-1],$row) : $row;

		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

		$dataForChart = array();
		for($i=0;$i<$maxDay;$i++){
			$dataForChart['count_download'][$i][0]	= ($i+1).'일';
			$dataForChart['count_download'][$i][1]	= (integer)$statsData[$i]['count_download'];
			$maxValue = $maxValue < $dataForChart['count_download'][$i][1] ? $dataForChart['count_download'][$i][1] : $maxValue;

			$dataForChart['count_member'][$i][0]	= ($i+1).'일';
			$dataForChart['count_member'][$i][1]	= (integer)$statsData[$i]['count_member'];
			$maxValue = $maxValue < $dataForChart['count_member'][$i][1] ? $dataForChart['count_member'][$i][1] : $maxValue;

			$dataForChart['count_offline'][$i][0]	= ($i+1).'일';
			$dataForChart['count_offline'][$i][1]	= (integer)$statsData[$i]['count_offline'];
			$maxValue = $maxValue < $dataForChart['count_offline'][$i][1] ? $dataForChart['count_offline'][$i][1] : $maxValue;

			$dataForChart['count_etc'][$i][0]		= ($i+1).'일';
			$dataForChart['count_etc'][$i][1]		= (integer)$statsData[$i]['count_etc'];
			$maxValue = $maxValue < $dataForChart['count_etc'][$i][1] ? $dataForChart['count_etc'][$i][1] : $maxValue;

		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		/* 월별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxDay;$i++){
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

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 월별 유입(광고포함) */
	public function promotion_inflow_monthly(){
		$this->admin_menu();
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 월별 유입(광고포함) - 방문자 */
	public function promotion_inflow_monthly_visitor(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$arr_view_sitecd = !empty($_GET['chkVisitorSitecd']) ? $_GET['chkVisitorSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				stats_year,
				stats_month,
				referer_sitecd,
				sum(`count`) as `count`
			from fm_stats_visitor_referer
			where referer_sitecd!='' and stats_year='{$_GET['year']}'
			group by stats_month, referer_sitecd
			order by `count` desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_month']-1][$row['referer_sitecd']] = $row['count'];
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

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

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxMonth;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'월';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 월별 유입(광고포함) - 회원가입 */
	public function promotion_inflow_monthly_join(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$arr_view_sitecd = !empty($_GET['chkJoinSitecd']) ? $_GET['chkJoinSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				marketplace,
				count(*) as cnt
			from fm_member
			where marketplace!='' and year(regist_date)='{$_GET['year']}'
			group by stats_month, marketplace
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_month']-1][$row['marketplace']] = $row['cnt'];
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

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

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxMonth;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'월';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 월별 유입(광고포함) - 구매건수 */
	public function promotion_inflow_monthly_ordercnt(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$arr_view_sitecd = !empty($_GET['chkOrdercntSitecd']) ? $_GET['chkOrdercntSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				marketplace,
				count(*) as cnt
			from fm_order
			where deposit_yn='y' and marketplace!='' and marketplace!='NO' and year(regist_date)='{$_GET['year']}'
			group by stats_month, marketplace
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_month']-1][$row['marketplace']] = $row['cnt'];
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

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

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxMonth;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'월';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 월별 유입(광고포함) - 구매금액 */
	public function promotion_inflow_monthly_orderprice(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$arr_view_sitecd = !empty($_GET['chkOrderpriceSitecd']) ? $_GET['chkOrderpriceSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				marketplace,
				sum(settleprice) as settleprice_sum
			from fm_order
			where deposit_yn='y' and marketplace!='' and marketplace!='NO' and year(regist_date)='{$_GET['year']}'
			group by stats_month, marketplace
			order by settleprice_sum desc
		";

		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_month']-1][$row['marketplace']] = $row['settleprice_sum'] ? $row['settleprice_sum']/1000 : 0;
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

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

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxMonth;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'월';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 일별 유입(광고포함) */
	public function promotion_inflow_daily(){
		$this->admin_menu();
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 일별 유입(광고포함) - 방문자 */
	public function promotion_inflow_daily_visitor(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');
		$arr_view_sitecd = !empty($_GET['chkVisitorSitecd']) ? $_GET['chkVisitorSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				stats_year,
				stats_month,
				stats_day,
				referer_sitecd,
				sum(`count`) as `count`
			from fm_stats_visitor_referer
			where referer_sitecd!='' and stats_year='{$_GET['year']}' and stats_month='{$_GET['month']}'
			group by stats_day, referer_sitecd
			order by `count` desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_day']-1][$row['referer_sitecd']] = $row['count'];
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxDay;$i++){
			$dataForTable[$i] = $statsData[$i];
		}
		foreach($dataForTable as $stats_month=>$row){
			foreach($row as $k=>$v){
				$dataForTableSum[$k] += $v;

			}
		}

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxDay;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'일';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 일별 유입(광고포함) - 회원가입 */
	public function promotion_inflow_daily_join(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');
		$arr_view_sitecd = !empty($_GET['chkJoinSitecd']) ? $_GET['chkJoinSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				day(regist_date) as stats_day,
				marketplace,
				count(*) as cnt
			from fm_member
			where marketplace!='' and year(regist_date)='{$_GET['year']}' and month(regist_date)='{$_GET['month']}'
			group by stats_day, marketplace
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_day']-1][$row['marketplace']] = $row['cnt'];
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxDay;$i++){
			$dataForTable[$i] = $statsData[$i];
		}
		foreach($dataForTable as $stats_month=>$row){
			foreach($row as $k=>$v){
				$dataForTableSum[$k] += $v;

			}
		}

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxDay;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'일';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 일별 유입(광고포함) - 구매건수 */
	public function promotion_inflow_daily_ordercnt(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');
		$arr_view_sitecd = !empty($_GET['chkOrdercntSitecd']) ? $_GET['chkOrdercntSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				day(regist_date) as stats_day,
				marketplace,
				count(*) as cnt
			from fm_order
			where deposit_yn='y' and marketplace!='' and marketplace!='NO' and year(regist_date)='{$_GET['year']}' and month(regist_date)='{$_GET['month']}'
			group by stats_day, marketplace
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_day']-1][$row['marketplace']] = $row['cnt'];
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxDay;$i++){
			$dataForTable[$i] = $statsData[$i];
		}
		foreach($dataForTable as $stats_month=>$row){
			foreach($row as $k=>$v){
				$dataForTableSum[$k] += $v;

			}
		}

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxDay;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'일';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 일별 유입(광고포함) - 구매금액 */
	public function promotion_inflow_daily_orderprice(){

		$this->tempate_modules();
		$this->load->model('visitorlog');
		$arr_referer_sitecd_name = $this->visitorlog->get_arr_referer_sitecd_name();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_promotion');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');
		$arr_view_sitecd = !empty($_GET['chkOrderpriceSitecd']) ? $_GET['chkOrderpriceSitecd'] : array();

		/* 데이터 추출 */
		$sql = "
			select
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				day(regist_date) as stats_day,
				marketplace,
				sum(settleprice) as settleprice_sum
			from fm_order
			where deposit_yn='y' and marketplace!='' and marketplace!='NO' and year(regist_date)='{$_GET['year']}' and month(regist_date)='{$_GET['month']}'
			group by stats_day, marketplace
			order by settleprice_sum desc
		";

		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$statsData[$row['stats_day']-1][$row['marketplace']] = $row['settleprice_sum'] ? $row['settleprice_sum']/1000 : 0;
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

		/* 일별 데이터 테이블 */
		$dataForTable = array();
		$dataForTableSum = array();
		for($i=0;$i<$maxDay;$i++){
			$dataForTable[$i] = $statsData[$i];
		}
		foreach($dataForTable as $stats_month=>$row){
			foreach($row as $k=>$v){
				$dataForTableSum[$k] += $v;

			}
		}

		/* 정렬 */
		$sort = array_keys($dataForTableSum);
		krsort($sort);
		foreach($sort as $v){
			$name = $arr_referer_sitecd_name[$v];
			unset($arr_referer_sitecd_name[$v]);
			if($name) $arr_referer_sitecd_name = array_merge(array($v=>$name),$arr_referer_sitecd_name);

		}

		/* 그래프에 출력할 사이트코드 */
		if(!$arr_view_sitecd){
			$idx = 0;
			foreach($dataForTableSum as $k=>$v){
				$arr_view_sitecd[] = $k;
				$idx++;
				if($idx>=5) break;
			}
		}
		if(!$arr_view_sitecd){
			$arr_view_sitecd = array('naver','nate','daum','direct');
		}

		$dataForChart = array();

		for($i=0;$i<$maxDay;$i++){
			foreach($arr_referer_sitecd_name as $k=>$v){
				if(!in_array($k,$arr_view_sitecd)) continue;
				$dataForChart[$k][$i][0]	= ($i+1).'일';
				$dataForChart[$k][$i][1]	= (integer)$statsData[$i][$k];
				$maxValue = $maxValue < $dataForChart[$k][$i][1] ? $dataForChart[$k][$i][1] : $maxValue;

			}
		}

		$this->template->assign(array(
			'arr_view_sitecd' => $arr_view_sitecd,
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
			'arr_referer_sitecd_name' => $arr_referer_sitecd_name,
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 프로모션 통계 - 광고 매체 추가 */
	public function promotion_inflow_add(){
		$this->tempate_modules();
		$query = $this->db->query("select * from fm_inflow order by seq desc");
		$data = $query->result_array();
		foreach($data as $k=>$v){
			$domain = !empty($this->config_system['domain']) ? $this->config_system['domain'] : $this->config_system['subDomain'];
			$data[$k]['inflow_url'] = "http://{$domain}/ad?code={$data[$k]['inflow_code']}";
		}
		$this->template->assign(array('data'=>$data));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


}

/* End of file statistic_promotion.php */
/* Location: ./app/controllers/admin/statistic_promotion.php */