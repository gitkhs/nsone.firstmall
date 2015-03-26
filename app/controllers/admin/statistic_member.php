<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_member extends admin_base {
	
	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('statsmodel');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_member');
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
		$result = $this->usedmodel->used_service_check('statistic_member_detail');
		if(!$result['type']){
			$this->template->assign('statistic_member_detail_limit','Y');
		}

		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));

		/* 쇼핑몰분석통계 메뉴 */
		$this->template->define(array('member_menu'=>$this->skin."/statistic_member/_member_menu.html"));
		$member_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		$member_menu = str_replace(array("_monthly","_daily"),"",$member_menu);
		$this->template->assign(array('selected_member_menu'=>$member_menu));
		$this->template->assign(array('service_code' => $this->config_system['service']['code']));
	}

	public function index()
	{
		redirect("/admin/statistic_member/member_basic");		
	}

	/* 기본 통계 */
	public function member_basic(){

		if	(!$_GET['date_type'])	$_GET['date_type']	= 'month';
		$file_path	= $this->template_path();
		switch($_GET['date_type']){
			case 'daily':
				$this->member_daily();
				$file_path	= str_replace('member_basic.html', 'member_daily.html', $file_path);
			break;
			case 'hour':
				$this->member_hourly();
				$file_path	= str_replace('member_basic.html', 'member_hourly.html', $file_path);
			break;
			case 'month':
			default:
				$this->member_monthly();
				$file_path	= str_replace('member_basic.html', 'member_monthly.html', $file_path);
			break;
		}

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 월별 기본 통계 */
	public function member_monthly(){

		/* 날짜 파라미터 */
		$_GET['year']	= !empty($_GET['year']) ? $_GET['year'] : date('Y');
		$params			= $_GET;

		## 전월 가입자수 추출
		$params['year']	= date('Y-m', strtotime('-1 month', strtotime($_GET['year'].'-01-01')));
		$query	= $this->statsmodel->get_member_basic_stats($params);
		$prev	= $query->result_array();
		$prev	= $prev[0]['cnt'];

		## 전월 방문자수 추출
		$query = $this->db->query("select sum(count_sum) as cnt from fm_stats_visitor_count where count_type='visit' and stats_date like '".$params['year']."%' group by stats_month");
		$vprev	= $query->result_array();
		$vprev	= $vprev[0]['cnt'];

		## 현재 가입자 데이터 추출
		$params['year']	= $_GET['year'];
		$query	= $this->statsmodel->get_member_basic_stats($params);
		$member	= $query->result_array();

		## 현재 방문자 데이터 추출
		$this->db->select("stats_year,stats_month,stats_day,count_type,sum(count_sum) as month_count_sum");
		$this->db->where(array('count_type'=>'visit','stats_year'=>$_GET['year']));
		$this->db->group_by("stats_month");  
		$query		= $this->db->get('fm_stats_visitor_count');
		$visitor	= $query->result_array();

		## 데이터 가공
		$maxValue = 0;
		$dataForChart = array();
		if	($member)	foreach($member as $k=>$data){
			$stat[$data['date']]['cnt']	= $data['cnt'];
		}
		if	($visitor)	foreach($visitor as $k=>$data){
			$stat[$data['stats_month']]['visit_cnt']	= $data['month_count_sum'];
		}

		for ($m = 1; $m <= 12; $m++){
			$mon	= str_pad($m, 2, '0', STR_PAD_LEFT);
			$cnt	= ($stat[$mon]['cnt'])			? $stat[$mon]['cnt']		: 0;
			$vcnt	= ($stat[$mon]['visit_cnt'])	? $stat[$mon]['visit_cnt']	: 0;

			if	(!$prev)	$per	= ($cnt > 0) ? 100 : 0;
			else			$per	= round((($cnt - $prev) / $prev) * 100, 1);
			if	(!$vprev)	$vper	= ($vcnt > 0) ? 100 : 0;
			else			$vper	= round((($vcnt - $vprev) / $vprev) * 100, 1);
			if	(!$cnt)		$jper	= 0;
			else			$jper	= round(($cnt / $vcnt) * 100, 1);

			$statlist[$mon]['cnt']			= $cnt;
			$statlist[$mon]['vcnt']			= $vcnt;
			$statlist[$mon]['per']			= $per;
			$statlist[$mon]['vper']			= $vper;
			$statlist[$mon]['jper']			= $jper;
			$dataForChart['방문자수'][]		= array($mon.'월', $vcnt);
			$dataForChart['가입자수'][]		= array($mon.'월', $cnt);

			$total['cnt']	+= $cnt;
			$total['vcnt']	+= $vcnt;
			$maxValue		= ($maxValue < $vcnt)	? $vcnt	: $maxValue;
			$vprev			= $vcnt;
			$prev			= $cnt;
		}

		$total['jper']	= round(($total['cnt'] / $total['vcnt']) * 100);;

		$this->template->assign(array(
			'total'			=> $total, 
			'statlist'		=> $statlist, 
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));
	}

	/* 일별 기본 통계 */
	public function member_daily(){

		## 날짜 파라미터
		$_GET['year']	= !empty($_GET['year'])		? $_GET['year']		: date('Y');
		$_GET['month']	= !empty($_GET['month'])	? $_GET['month']	: date('m');
		$start_time		= strtotime($_GET['year'].'-'.$_GET['month'].'-01');
		$params	= $_GET;

		## 전일 가입자수 추출
		$params['month']	= date('m-d', strtotime('-1 day', $start_time));
		$query	= $this->statsmodel->get_member_basic_stats($params);
		$prev	= $query->result_array();
		$prev	= $prev[0]['cnt'];

		## 전일 방문자수 추출
		$query = $this->db->query("select sum(count_sum) as cnt from fm_stats_visitor_count where count_type='visit' and stats_date like '".$params['year']."-".$params['month']."%' group by stats_day");
		$vprev	= $query->result_array();
		$vprev	= $vprev[0]['cnt'];

		## 현재 가입자 데이터 추출
		$params['month']	= $_GET['month'];
		$query	= $this->statsmodel->get_member_basic_stats($params);
		$member	= $query->result_array();

		## 현재 방문자 데이터 추출
		$this->db->select("stats_year,stats_month,stats_day,count_type,sum(count_sum) as month_count_sum");
		$this->db->where(array('count_type'=>'visit','stats_year'=>$_GET['year'],'stats_month'=>$_GET['month']));
		$this->db->group_by("stats_day");  
		$query		= $this->db->get('fm_stats_visitor_count');
		$visitor	= $query->result_array();

		## 데이터 가공
		$maxValue = 0;
		$dataForChart = array();
		if	($member)	foreach($member as $k=>$data){
			$stat[$data['date']]['cnt']	= $data['cnt'];
		}
		if	($visitor)	foreach($visitor as $k=>$data){
			$stat[$data['stats_day']]['visit_cnt']	= $data['month_count_sum'];
		}

		$end_day	= date('t', $start_time);
		$w			= date('w', $start_time);
		$start_week	= $w;
		for ($d = 1; $d <= $end_day; $d++){
			$day	= str_pad($d, 2, '0', STR_PAD_LEFT);
			$cnt	= ($stat[$day]['cnt'])			? $stat[$day]['cnt']		: 0;
			$vcnt	= ($stat[$day]['visit_cnt'])	? $stat[$day]['visit_cnt']	: 0;

			if	(!$prev)	$per	= ($cnt > 0) ? 100 : 0;
			else			$per	= round((($cnt - $prev) / $prev) * 100, 1);
			if	(!$vprev)	$vper	= ($vcnt > 0) ? 100 : 0;
			else			$vper	= round((($vcnt - $vprev) / $vprev) * 100, 1);
			if	(!$cnt)		$jper	= 0;
			else			$jper	= round(($cnt / $vcnt) * 100, 1);

			$statlist[$d]['cnt']			= $cnt;
			$statlist[$d]['vcnt']			= $vcnt;
			$statlist[$d]['per']			= $per;
			$statlist[$d]['vper']			= $vper;
			$statlist[$d]['jper']			= $jper;
			$dataForChart['가입자수'][]		= array($d.'일', $cnt);
			$dataForChart['방문자수'][]		= array($d.'일', $vcnt);

			$weekSumCnt		+= $cnt;
			$weekSumVCnt	+= $vcnt;

			$total['cnt']	+= $cnt;
			$total['vcnt']	+= $vcnt;
			$maxValue		= ($maxValue < $vcnt)	? $vcnt	: $maxValue;
			$vprev			= $vcnt;
			$prev			= $cnt;


			if	($w == 6){
				$statlist[$d]['week']			= 1;
				$statlist[$d]['week_cnt']		= $weekSumCnt;
				$statlist[$d]['week_vcnt']	= $weekSumVCnt;
				$weekSumCnt		= 0;
				$weekSumVCnt	= 0;
			}

			$w		= date('w', strtotime('+'.$d.' day', $start_time));
		}
		$statlist[($d - 1)]['end_day']		= $d - 1;
		$statlist[($d - 1)]['week_cnt']		= $weekSumCnt;
		$statlist[($d - 1)]['week_vcnt']	= $weekSumVCnt;
		$end_week	= date('w', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$end_day));

		$total['jper']	= round(($total['cnt'] / $total['vcnt']) * 100);

		$this->template->assign(array(
			'total'			=> $total, 
			'statlist'		=> $statlist, 
			'start_week'	=> $start_week, 
			'end_week'		=> $end_week, 
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));
	}

	/* 시간별 기본 통계 */
	public function member_hourly(){

		## 날짜 파라미터
		$_GET['year']	= !empty($_GET['year'])		? $_GET['year']		: date('Y');
		$_GET['month']	= !empty($_GET['month'])	? $_GET['month']	: date('m');
		$_GET['day']	= !empty($_GET['day'])		? $_GET['day']		: date('d');
		$params			= $_GET;

		## 현재 가입자 데이터 추출
		$query	= $this->statsmodel->get_member_basic_stats($params);
		$member	= $query->result_array();

		## 현재 방문자 데이터 추출
		$this->db->where(array('count_type'=>'visit','stats_year'=>$_GET['year'],'stats_month'=>$_GET['month'],'stats_day'=>$_GET['day']));
		$query		= $this->db->get('fm_stats_visitor_count');
		$visitor	= $query->result_array();
		$visitor	= $visitor[0];

		## 데이터 가공
		$maxValue = 0;
		$dataForChart = array();
		if	($member)	foreach($member as $k=>$data){
			$stat[$data['date']]['cnt']	= $data['cnt'];
		}

		for ($h = 0; $h <= 23; $h++){
			$hour	= str_pad($h, 2, '0', STR_PAD_LEFT);
			$cnt	= ($stat[$hour]['cnt'])	? $stat[$hour]['cnt']	: 0;
			$vcnt	= ($visitor['h'.$hour])	? $visitor['h'.$hour]	: 0;

			$statlist[$h]['cnt']			= $cnt;
			$statlist[$h]['vcnt']			= $vcnt;
			$dataForChart['가입자수'][]		= array($h.'시', $cnt);
			$dataForChart['방문자수'][]		= array($h.'시', $vcnt);

			$total['cnt']	+= $cnt;
			$total['vcnt']	+= $vcnt;
			$maxValue		= ($maxValue < $vcnt)	? $vcnt	: $maxValue;
		}

		## 현재 가입자 데이터 추출
		$params['date']	= $_GET['year'].'-'.$_GET['month'].'-'.$_GET['day'];
		$query		= $this->statsmodel->get_member_referer_stats($params);
		$referer	= $query->result_array();
		if	($referer) foreach($referer as $k => $data){
			if	(!trim($data['referer']))	$data['referer']	= '직접입력';
			$refererlist[$data['referer']]['cnt']	+= $data['cnt'];
			$total_referer	+= $data['cnt'];
		}

		$this->template->assign(array(
			'total'			=> $total, 
			'statlist'		=> $statlist, 
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue,
			'refererlist'	=> $refererlist, 
			'total_referer'	=> $total_referer 
		));
	}

	/* 유입경로 통계 */
	public function member_referer(){
		$_GET['year']		= !empty($_GET['year'])			? $_GET['year']			: date('Y');
		$_GET['month']		= !empty($_GET['month'])		? $_GET['month']		: date('m');
		$_GET['date_type']	= !empty($_GET['date_type'])	? $_GET['date_type']	: 'month';
		$params				= $_GET;

		$query		= $this->statsmodel->get_member_referer_stats($params);
		$referer	= $query->result_array();
		if	($referer) foreach($referer as $k => $data){
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

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 환경 통계 */
	public function member_platform(){

		$_GET['year']		= !empty($_GET['year'])			? $_GET['year']			: date('Y');
		$params				= $_GET;

		//판매환경
		$sitetypeloop = sitetype('', 'image', 'array');

		$query		= $this->statsmodel->get_member_platform_stats($params);
		$stat = $query->result_array();
		if($stat) foreach($stat as $k => $data){
			if	(!$data['platform'])	$data['platform']	= 'P';

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

	/* 가입수단 통계 */
	public function member_rute(){
		$_GET['year']		= !empty($_GET['year'])			? $_GET['year']			: date('Y');
		$params				= $_GET;

		$memberrute = memberrute('', 'image', 'array', 'old');

		$query		= $this->statsmodel->get_member_rute_stats($params);
		$stat		= $query->result_array();
		if($stat) foreach($stat as $k => $data){
			if	(!$data['rute'])				$data['rute']	= 'none';
			if	($data['rute'] != 'none')		$data['rute']	= 'sns_'.substr($data['rute'],0,1);
			if	(!$memberrute[$data['rute']])	$data['rute']	= 'none';

			$statlist[$memberrute[$data['rute']]['name']]['cnt']	+= $data['cnt'];
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

	/* 성별/연력/지역 통계 */
	public function member_etc(){

		$arr_age		= array('10대 이하','20대','30대','40대','50대','60대 이상');
		$arr_sex		= array('남','여');
		$this->load->helper('zipcode');
	    $ZIP_DB				= get_zipcode_db();
		$arr_location		= array();
		$query = $ZIP_DB->query("SELECT substring(SIDO,1,4) as SIDO FROM `zipcode_street` GROUP BY substring(SIDO,1,4)");
		foreach($query->result_array() as $row){
			$arr_location[] = $row['SIDO'];
		}

		$_GET['year']		= !empty($_GET['year'])			? $_GET['year']			: date('Y');


		// 성별 데이터
		$query = $this->db->query("select SUBSTRING(regist_date, 6, 2) as date, member_sex, count(*) as cnt from fm_member_stats where member_sex in ('male', 'female') and regist_date like '".$_GET['year']."%' group by member_sex, date");
		$sex			= $query->result_array();

		$sexMaxValue	= 0;
		$dataForChart	= array();
		if	($sex)
			foreach($sex as $k => $data)
				$sexlist[$data['member_sex']][$data['date']]	+= $data['cnt'];

		for ($m = 1; $m <= 12; $m++){
			$sumCnt	= 0;
			$mk		= str_pad($m, 2, "0", STR_PAD_LEFT);
			$mcnt	= ($sexlist['male'][$mk])	? $sexlist['male'][$mk]		: 0;
			$fcnt	= ($sexlist['female'][$mk])	? $sexlist['female'][$mk]	: 0;

			$dataForChart['성별']['남성'][]	= array($m.'월', $mcnt);
			$dataForChart['성별']['여성'][]	= array($m.'월', $fcnt);

			$sumCnt	+= $mcnt + $fcnt;
			$sexMaxValue	= ($sexMaxValue < $sumCnt) ? $sumCnt : $sexMaxValue;
			$dataForChart['성별']['합계'][]	= array($m.'월', $sumCnt);
		}


		// 연령별 데이터
		$query = $this->db->query("select SUBSTRING(regist_date, 6, 2) as date, member_age, member_sex, count(*) as cnt from fm_member_stats where member_sex in ('male', 'female') and regist_date like '".$_GET['year']."%' group by member_sex, member_age, date");
		$age			= $query->result_array();

		if	($age){
			foreach($age as $k => $data){
				$ageGroup	= floor($data['member_age'] / 10);
				if		($ageGroup < 2)	$ageGroup	= 1;
				elseif	($ageGroup > 6)	$ageGroup	= 6;

				$ageMonth[$data['date']][$ageGroup][$data['member_sex']]	+= $data['cnt'];
				$agelist[$data['member_sex']][$ageGroup]					+= $data['cnt'];
			}
		}

		for ($a = 1; $a <= 6; $a++){
			$mCnt		= ($agelist['male'][$a])	? $agelist['male'][$a]		: 0;
			$fCnt		= ($agelist['female'][$a])	? $agelist['female'][$a]	: 0;
			$tCnt		= $mCnt + $fCnt;

			$ageName	= $a * 10;
			if		($ageName == 10)	$ageName .= '대 이하';
			elseif	($ageName == 60)	$ageName .= '대 이상';
			else						$ageName .= '대 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

			$dataForChart['연령']['남자'][]	= array($ageName, $mCnt);
			$dataForChart['연령']['여자'][]	= array($ageName, $fCnt);
			$dataForChart['연령']['총합'][]	= array($ageName, $tCnt);

			for ($m = 1; $m <= 12; $m++){
				$mk		= str_pad($m, 2, "0", STR_PAD_LEFT);
				$mCnt	= ($ageMonth[$mk][$a]['male'])		? $ageMonth[$mk][$a]['male']	: 0;
				$fCnt	= ($ageMonth[$mk][$a]['female'])	? $ageMonth[$mk][$a]['female']	: 0;
				$tCnt	= $mCnt + $fCnt;

				$ageStatList[$m]['age'][$a]['sex']['m']	= $mCnt;
				$ageStatList[$m]['age'][$a]['sex']['f']	= $fCnt;
				$ageStatList[$m]['age'][$a]['sex']['t']	= $tCnt;
				$ageStatTotal[$a]['sex']['m']			+= $mCnt;
				$ageStatTotal[$a]['sex']['f']			+= $fCnt;
				$ageStatTotal[$a]['sex']['t']			+= $tCnt;
			}
		}

		// 지역별 데이터
		$query = $this->db->query("select substring(member_area,1,4) as member_area, member_sex, count(*) as cnt from fm_member_stats where member_sex in ('male', 'female') and regist_date like '".$_GET['year']."%' group by member_area, member_sex");
		$location	= $query->result_array();
		if	($location){
			foreach($location as $k => $data){
				$locate[$data['member_area']][$data['member_sex']]	+= $data['cnt'];
			}
		}

		$locateCnt	= count($arr_location);
		for ($l = 0; $l < $locateCnt; $l++){
			$nLocate	= $arr_location[$l];
			$mCnt	= ($locate[$nLocate]['male'])	? $locate[$nLocate]['male']		: 0;
			$fCnt	= ($locate[$nLocate]['female'])	? $locate[$nLocate]['female']	: 0;
			$tCnt	= $mCnt + $fCnt;

			$locationList[$nLocate]['sex']['m']	+= $mCnt;
			$locationList[$nLocate]['sex']['f']	+= $fCnt;
			$locationList[$nLocate]['sex']['t']	+= $tCnt;

			$dataForChart['지역']['남'][]		= array($nLocate, $mCnt);
			$dataForChart['지역']['여'][]		= array($nLocate, $fCnt);
			$dataForChart['지역']['합계'][]		= array($nLocate, $tCnt);

			$locationMaxValue	= ($locationMaxValue < $tCnt) ? $tCnt : $locationMaxValue;
		}

		$this->template->assign(array(
			'sc'=>$_GET, 
			'arr_age'=>$arr_age,
			'arr_sex'=>$arr_sex,
			'arr_location'=>$arr_location,
			'sexMaxValue'=>$sexMaxValue,
			'locationMaxValue'=>$locationMaxValue,
			'ageStatList'=>$ageStatList,
			'ageStatTotal'=>$ageStatTotal,
			'locationList'=>$locationList,
			'dataForChart'=>$dataForChart,
			'statlist'=>$statlist,
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file statistic_promotion.php */
/* Location: ./app/controllers/admin/statistic_promotion.php */