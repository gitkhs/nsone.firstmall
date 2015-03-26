<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_visitor extends admin_base {
	
	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_visitor');
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
		$result = $this->usedmodel->used_service_check('statistic_visitor_detail');
		if(!$result['type']){
			$this->template->assign('statistic_visitor_detail_limit','Y');
		}

		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));
		
		/* 방문자통계 메뉴 */
		$this->template->define(array('visitor_menu'=>$this->skin."/statistic_visitor/_visitor_menu.html"));
		$visitor_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		if($visitor_menu=='visitor_hourly') $visitor_menu = 'visitor_hourly';
		$this->template->assign(array('selected_visitor_menu'=>$visitor_menu));

		$this->template->assign(array(
			'service_code' => $this->config_system['service']['code'], 
			'sitetype'=>$_GET['sitetype'],
			'sitetypeloop'=>$this->sitetypeloop
		));
	}

	public function index()
	{
		redirect("/admin/statistic_visitor/visitor_basic");		
	}

	/* 월/일/시간별 방문 통계 */
	public function visitor_basic(){

		if	(!$_GET['date_type'])	$_GET['date_type']	= 'month';
		$file_path	= $this->template_path();
		switch($_GET['date_type']){
			case 'daily':
				$this->visitor_daily();
				$file_path	= str_replace('visitor_basic.html', 'visitor_daily.html', $file_path);
			break;
			case 'hour':
				$this->visitor_hourly();
				$file_path	= str_replace('visitor_basic.html', 'visitor_hourly.html', $file_path);
			break;
			case 'month':
			default:
				$this->visitor_monthly();
				$file_path	= str_replace('visitor_basic.html', 'visitor_monthly.html', $file_path);
			break;
		}

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 시간대별 방문자 통계 */
	public function visitor_hourly()
	{
		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');
		$_GET['day'] = !empty($_GET['day']) ? $_GET['day'] : date('d');

		$stats_date = $_GET['year'] .'-'. $_GET['month'] .'-'. $_GET['day'];

		/* 데이터 추출 */
		$query = $this->db->get_where('fm_stats_visitor_count',array('count_type'=>'visit','stats_date'=>$stats_date));
		$statsData['방문자수'] = $query->row_array();
		
		$query = $this->db->get_where('fm_stats_visitor_count',array('count_type'=>'pv','stats_date'=>$stats_date));
		$statsData['페이지뷰'] = $query->row_array();
		
		/* 데이터 가공 */
		$maxValue = 0;
		$dataForChart = array();
		foreach($statsData as $k=>$v){
			for($i=0;$i<24;$i++){
				$value = (int)$statsData[$k]["h".sprintf("%02d",$i)];
				$dataForChart[$k][] = array($i.'시',$value); 
				$maxValue = $maxValue < $value ? $value : $maxValue;
			}
		}
		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));
		
		/* 시간별 데이터 테이블 추출 */
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<24;$i++){
			$pv = $statsData['페이지뷰']['h'.sprintf("%02d",$i)];
			$visit = $statsData['방문자수']['h'.sprintf("%02d",$i)];
			$pvPerVisit = $visit ? round($pv/$visit,1) : 0;
			
			$dataForTable[$i] = array(
				'pv'			=> $pv,
				'visit'			=> $visit,
				'pvPerVisit'	=> $pvPerVisit
			);
			
			$dataForTableSum['pv'] += $pv;
			$dataForTableSum['visit'] += $visit;
		}
		
		$dataForTableSum['pvPerVisit'] = $dataForTableSum['visit'] ? round($dataForTableSum['pv']/$dataForTableSum['visit'],1) : 0;
		
		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
		));
		$this->template->define(array('visitor_hourly_table'=>$this->skin."/statistic_visitor/_visitor_hourly_table.html"));
		
		/* 유입경로 데이터 추출 */
		$this->db->order_by("count desc");
		$query = $this->db->get_where('fm_stats_visitor_referer',array('stats_date'=>$stats_date));
		$refererData = $query->result_array();
		$this->template->assign(array('refererData'	=> $refererData));
		$this->template->define(array('visitor_referer_table'=>$this->skin."/statistic_visitor/_visitor_referer_table.html"));
	}
	
	/* 일별 방문자 통계 */
	public function visitor_daily(){

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$_GET['month'] = !empty($_GET['month']) ? $_GET['month'] : date('m');
		list($lastPrevYear,$lastPrevMonth,$lastPrevDay) = explode('-',date('Y-m-d',strtotime('-1 day',strtotime("{$_GET['year']}-{$_GET['month']}-01"))));
		
		/* 데이터 추출 */
		$this->db->select("stats_year,stats_month,stats_day,count_type,count_sum");
		$this->db->where(array('count_type'=>'visit','stats_year'=>$_GET['year'],'stats_month'=>$_GET['month']));
		$this->db->group_by("stats_day");
		$query = $this->db->get('fm_stats_visitor_count');
		$statsData['방문자수'] = $query->result_array();
		
		$this->db->select("stats_year,stats_month,stats_day,count_type,count_sum");
		$this->db->where(array('count_type'=>'pv','stats_year'=>$_GET['year'],'stats_month'=>$_GET['month']));
		$this->db->group_by("stats_day");
		$query = $this->db->get('fm_stats_visitor_count');
		$statsData['페이지뷰'] = $query->result_array();
		
		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		
		$dataForChart = array();
		foreach($statsData as $k=>$v){
			for($i=0;$i<$maxDay;$i++){
				$dataForChart[$k][$i][0] = ($i+1).'일';
				$dataForChart[$k][$i][1] = 0;
			}
		}
		
		foreach($statsData as $k=>$v){
			foreach($statsData[$k] as $row){
				$dataForChart[$k][$row['stats_day']-1][1] = $row['count_sum'];
				$maxValue = $maxValue < $row['count_sum'] ? $row['count_sum'] : $maxValue;
			}
		}
		
		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));

		/* 일별 데이터 달력용 */
		$c_start_idx = date('w',strtotime("{$_GET['year']}-{$_GET['month']}-01"));
		$c_end_idx = date('t',strtotime("{$_GET['year']}-{$_GET['month']}-01"));
		$c_row = ceil(($c_start_idx+$c_end_idx)/7);

		$this->template->assign(array(
			'c_start_idx'	=> $c_start_idx,
			'c_end_idx'		=> $c_end_idx,
			'c_row'			=> $c_row,
		));
		$this->template->define(array('visitor_daily_calendar'=>$this->skin."/statistic_visitor/_visitor_daily_calendar.html"));
		
		/* 일별 데이터 테이블 */
		$query = $this->db->query("select count_sum from fm_stats_visitor_count where count_type='pv' and stats_year=? and stats_month=? and stats_day=?",array($lastPrevYear,$lastPrevMonth,$lastPrevDay));
		list($prevPv) = array_values($query->row_array());
		$query = $this->db->query("select count_sum from fm_stats_visitor_count where count_type='visit' and stats_year=? and stats_month=? and stats_day=?",array($lastPrevYear,$lastPrevMonth,$lastPrevDay));
		list($prevVisit) = array_values($query->row_array());
		
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<$maxDay;$i++){
			$pv = $dataForChart['페이지뷰'][$i][1];
			$visit = $dataForChart['방문자수'][$i][1];
			$pvPerVisit = $visit ? round($pv/$visit,1) : 0;
			
			if($prevPv)	$pvGrowth = ($pv-$prevPv) / $prevPv * 100;
			else $pvGrowth = $pv ? 100 : 0;

			if($prevVisit) $visitGrowth = ($visit-$prevVisit) / $prevVisit * 100;
			else $visitGrowth = $visit ? 100 : 0;
			
			if(date('Ymd')<$_GET['year'].sprintf("%02d",$_GET['month']).sprintf("%02d",$i+1)){
				$pvGrowth=0;
				$visitGrowth=0;
			}
			
			$prevPv = $pv;
			$prevVisit = $visit;

			$dataForTable[$i] = array(
				'pv'			=> $pv,
				'pvGrowth'		=> $pvGrowth,
				'visit'			=> $visit,
				'visitGrowth'	=> $visitGrowth,
				'pvPerVisit'	=> $pvPerVisit
			);
			
			$dataForTableSum['pv'] += $pv;
			$dataForTableSum['visit'] += $visit;
		}
		
		$dataForTableSum['pvPerVisit'] = $dataForTableSum['visit'] ? round($dataForTableSum['pv']/$dataForTableSum['visit'],1) : 0;
		
		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
		));
		$this->template->define(array('visitor_daily_table'=>$this->skin."/statistic_visitor/_visitor_daily_table.html"));
	}
	
	/* 월별 방문자 통계 */
	public function visitor_monthly(){

		/* 날짜 파라미터 */
		$_GET['year'] = !empty($_GET['year']) ? $_GET['year'] : date('Y');
		list($lastPrevYear,$lastPrevMonth) = explode('-',date('Y-m',strtotime('-1 month',strtotime("{$_GET['year']}-01-01"))));
		
		/* 데이터 추출 */
		$this->db->select("stats_year,stats_month,stats_day,count_type,sum(count_sum) as month_count_sum");
		$this->db->where(array('count_type'=>'visit','stats_year'=>$_GET['year']));
		$this->db->group_by("stats_month");  
		$query = $this->db->get('fm_stats_visitor_count');
		$statsData['방문자수'] = $query->result_array();
		
		$this->db->select("stats_year,stats_month,stats_day,count_type,sum(count_sum) as month_count_sum");
		$this->db->where(array('count_type'=>'pv','stats_year'=>$_GET['year']));
		$this->db->group_by("stats_month");
		$query = $this->db->get('fm_stats_visitor_count');
		$statsData['페이지뷰'] = $query->result_array();
		
		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;
		
		$dataForChart = array();
		foreach($statsData as $k=>$v){
			for($i=0;$i<$maxMonth;$i++){
				$dataForChart[$k][$i][0] = ($i+1).'월';
				$dataForChart[$k][$i][1] = 0;
			}
		}
		
		foreach($statsData as $k=>$v){
			foreach($statsData[$k] as $row){
				$dataForChart[$k][$row['stats_month']-1][1] = $row['month_count_sum'];
				$maxValue = $maxValue < $row['month_count_sum'] ? $row['month_count_sum'] : $maxValue;
			}
		}

		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));
		
		/* 월별 데이터 테이블 */
		$query = $this->db->query("select sum(count_sum) from fm_stats_visitor_count where count_type='pv' and stats_year=? and stats_month=? group by stats_month",array($lastPrevYear,$lastPrevMonth));
		list($prevPv) = array_values($query->row_array());
		$query = $this->db->query("select sum(count_sum) from fm_stats_visitor_count where count_type='visit' and stats_year=? and stats_month=? group by stats_month",array($lastPrevYear,$lastPrevMonth));
		list($prevVisit) = array_values($query->row_array());
		
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<$maxMonth;$i++){
			$pv = $dataForChart['페이지뷰'][$i][1];
			$visit = $dataForChart['방문자수'][$i][1];
			$pvPerVisit = $visit ? round($pv/$visit,1) : 0;
			
			if($prevPv)	$pvGrowth = ($pv-$prevPv) / $prevPv * 100;
			else $pvGrowth = $pv ? 100 : 0;

			if($prevVisit) $visitGrowth = ($visit-$prevVisit) / $prevVisit * 100;
			else $visitGrowth = $visit ? 100 : 0;
			
			if(date('Ym')<$_GET['year'].sprintf("%02d",$i+1)){
				$pvGrowth=0;
				$visitGrowth=0;
			}
			
			$prevPv = $pv;
			$prevVisit = $visit;

			$dataForTable[$i] = array(
				'pv'			=> $pv,
				'pvGrowth'		=> $pvGrowth,
				'visit'			=> $visit,
				'visitGrowth'	=> $visitGrowth,
				'pvPerVisit'	=> $pvPerVisit
			);
			
			$dataForTableSum['pv'] += $pv;
			$dataForTableSum['visit'] += $visit;
		}
		
		$dataForTableSum['pvPerVisit'] = $dataForTableSum['visit'] ? round($dataForTableSum['pv']/$dataForTableSum['visit'],1) : 0;
		
		$this->template->assign(array(
			'dataForTable'	=> $dataForTable,
			'dataForTableSum' => $dataForTableSum,
		));
		$this->template->define(array('visitor_monthly_table'=>$this->skin."/statistic_visitor/_visitor_monthly_table.html"));

	}

	/* 방문자 유입경로 통계 설정 */
	public function visitor_referer(){

		$_GET['year']		= !empty($_GET['year'])			? $_GET['year']			: date('Y');
		$_GET['month']		= !empty($_GET['month'])		? $_GET['month']		: date('m');
		$_GET['date_type']	= !empty($_GET['date_type'])	? $_GET['date_type']	: 'month';

		if	($_GET['date_type'] == 'daily'){
			$sql	= "select stats_day as date, IF(rg.referer_group_no>0, rg.referer_group_name, 
							IF(LENGTH(vr.referer)>0,'기타','직접입력')) as referer_name, 
							sum(vr.count) as cnt 
						from fm_stats_visitor_referer	as vr 
						LEFT JOIN fm_referer_group		as rg 
							on ( vr.referer_domain = rg.referer_group_url or INSTR(vr.referer, rg.referer_group_url) ) 
						where vr.stats_year=? and vr.stats_month=?  
						group by referer_name, stats_day 
						order by cnt desc";
			$query = $this->db->query($sql, array($_GET['year'], $_GET['month']));
		}else{
			$sql	= "select stats_month as date, IF(rg.referer_group_no>0, rg.referer_group_name, 
							IF(LENGTH(vr.referer)>0,'기타','직접입력')) as referer_name, 
							sum(vr.count) as cnt 
						from fm_stats_visitor_referer	as vr 
						LEFT JOIN fm_referer_group		as rg 
							on ( vr.referer_domain = rg.referer_group_url or INSTR(vr.referer, rg.referer_group_url) ) 
						where vr.stats_year=? 
						group by referer_name, stats_month 
						order by cnt desc";
			$query = $this->db->query($sql, array($_GET['year']));
		}
		$statlist = $query->result_array();
		if($statlist) foreach($statlist as $k => $data){
			$stat[$data['referer_name']][$data['date']]	+= $data['cnt'];
		}

		$sitecdArr	= array_keys($stat);
		unset($statlist);
		if	($_GET['date_type'] == 'daily'){
			$end_day	= date('t', strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
			for	($d = 1; $d <= $end_day; $d++){
				$dk	= str_pad($d, 2, "0", STR_PAD_LEFT);
				foreach($sitecdArr as $k => $v){
					$cnt	= ($stat[$v][$dk])	? $stat[$v][$dk]		: '0';
					$statlist[$v]['list'][$d]['refer_name']		= $v;
					$statlist[$v]['list'][$d]['cnt']			= $cnt;
					$statlist[$v]['total_cnt']					+= $cnt;

					$total_referer[$v]							= $statlist[$v]['total_cnt'];

					if	($maxCnt < $cnt)		$maxCnt			= $cnt;
					$dataForChart[$v][]							= array($d.'일', $cnt);
				}

				$table_title[]	= $d.'일';
			}
		}else{
			for	($m = 1; $m <= 12; $m++){
				$mk	= str_pad($m, 2, "0", STR_PAD_LEFT);
				foreach($sitecdArr as $k => $v){
					$cnt	= ($stat[$v][$mk])	? $stat[$v][$mk]	: '0';
					$statlist[$v]['list'][$m]['refer_name']		= $v;
					$statlist[$v]['list'][$m]['cnt']			= $cnt;
					$statlist[$v]['total_cnt']					+= $cnt;

					$total_referer[$v]							= $statlist[$v]['total_cnt'];

					if	($maxCnt < $cnt)		$maxCnt			= $cnt;
					$dataForChart[$v][]							= array($m.'월', $cnt);
				}

				$table_title[]	= $m.'월';
			}
		}

		arsort($total_referer);

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('table_title'=>$table_title));
		$this->template->assign(array('dataForChart'=>$dataForChart));
		$this->template->assign(array('total_referer'=>$total_referer));
		$this->template->assign(array('statlist'=>$statlist));
		$this->template->assign(array('maxCnt'=>$maxCnt));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 방문자 유입경로 통계 설정 */
	public function visitor_platform(){

		$_GET['year']		= !empty($_GET['year'])			? $_GET['year']			: date('Y');

		//판매환경
		$sitetypeloop = sitetype('', 'image', 'array');

		if	($_GET['month']){
			$query = $this->db->query("select platform, sum(`count`) as cnt from fm_stats_visitor_platform  where  stats_year=? and stats_month=? group by platform", array($_GET['year'], $_GET['month']));
		}else{
			$query = $this->db->query("select platform, sum(`count`) as cnt from fm_stats_visitor_platform  where  stats_year=? group by platform", array($_GET['year']));
		}
		$stat = $query->result_array();
		if($stat) foreach($stat as $k => $data){
			$statlist[$sitetypeloop[$data['platform']]['name']]['cnt']	+= $data['cnt'];
			$total	+= $data['cnt'];
		}
		foreach($statlist as $k => $v){
			$statlist[$k]['percent']	= round(($v['cnt'] / $total) * 100);
			$dataForChart[]	= array($k, $v['cnt']);
		}

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('table_title'=>$table_title));
		$this->template->assign(array('dataForChart'=>$dataForChart));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}

	/* 방문자 통계 설정 */
	public function visitor_setting(){
		$statisticExcludeIp = !empty($this->config_system['statisticExcludeIp']) ? $this->config_system['statisticExcludeIp'] : "";
		$statisticExcludeIp = $statisticExcludeIp ? explode("\n",$statisticExcludeIp) : array();
		$this->template->assign(array('statisticExcludeIp'=>$statisticExcludeIp));
		
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file statistic_visitor.php */
/* Location: ./app/controllers/admin/statistic_visitor.php */