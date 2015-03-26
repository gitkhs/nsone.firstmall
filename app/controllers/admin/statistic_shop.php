<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_shop extends admin_base {
	
	public function __construct() {
		parent::__construct();
		
		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));

		/* 쇼핑몰분석통계 메뉴 */
		$this->template->define(array('shop_menu'=>$this->skin."/statistic_shop/_shop_menu.html"));
		$shop_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		$shop_menu = str_replace(array("_monthly","_daily"),"",$shop_menu);
		$this->template->assign(array('selected_shop_menu'=>$shop_menu));
		
	}

	public function index()
	{
		redirect("/admin/statistic_shop/shop_member_monthly");		
	}
	
	public function shop_member()
	{
		redirect("/admin/statistic_shop/shop_member_monthly");		
	}

	/* 쇼핑몰분석 - 회원가입현황 */
	public function shop_member_monthly(){
		$this->admin_menu();
		$this->tempate_modules();
		
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_shop');
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
	
	/* 쇼핑몰분석 - 회원가입현황 - 성별 */
	public function shop_member_monthly_sex(){
		
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
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				sex,
				count(*) as cnt
			from fm_member
			where year(regist_date)='{$_GET['year']}' and `status` != 'withdrawal'
			group by stats_month, sex
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {			
			if($row['sex']=='male')		$statsData['남성'][$row['stats_month']-1] = $row['cnt'];
			if($row['sex']=='female')	$statsData['여성'][$row['stats_month']-1] = $row['cnt'];
			$statsData['합계'][$row['stats_month']-1] += $row['cnt'];	
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

		$dataForChart = array();
	
		for($i=0;$i<$maxMonth;$i++){
			
			$dataForChart['남성'][$i][0]	= ($i+1).'월';
			$dataForChart['남성'][$i][1]	= (integer)$statsData['남성'][$i];
			$maxValue = $maxValue < $dataForChart['남성'][$i][1] ? $dataForChart['남성'][$i][1] : $maxValue;
			
			$dataForChart['여성'][$i][0]	= ($i+1).'월';
			$dataForChart['여성'][$i][1]	= (integer)$statsData['여성'][$i];
			$maxValue = $maxValue < $dataForChart['여성'][$i][1] ? $dataForChart['여성'][$i][1] : $maxValue;
			
			$dataForChart['합계'][$i][0]	= ($i+1).'월';
			$dataForChart['합계'][$i][1]	= (integer)$statsData['합계'][$i];
			$maxValue = $maxValue < $dataForChart['합계'][$i][1] ? $dataForChart['합계'][$i][1] : $maxValue;

		}
		
		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));
				
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 회원가입현황 - 연령별 */
	public function shop_member_monthly_age(){
		
		$this->tempate_modules();
		
		$this->arr_age = array('10대 이하','20대','30대','40대','50대','60대 이상');
		$this->arr_sex = array('남','여','합계');
		
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
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				sex,
				year(now())-year(birthday)+1 as age,
				count(*) as cnt
			from fm_member
			where birthday!='0000-00-00' and year(regist_date)='{$_GET['year']}'
			group by stats_month, sex,age
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			
			if($row['age'] < 20)			$age = '10대 이하';
			else if($row['age'] < 30)		$age = '20대';
			else if($row['age'] < 40)		$age = '30대';
			else if($row['age'] < 50)		$age = '40대';
			else if($row['age'] < 60)		$age = '50대';
			else if($row['age'] >= 60)		$age = '60대 이상';
			else continue;
			
			if($row['sex']=='male')			$sex = "남";		
			else if($row['sex']=='female')	$sex = "여";
			else continue;
			
			$statsData[$sex][$age][$row['stats_month']-1] = $row['cnt'];
			$statsData['합계'][$age][$row['stats_month']-1] += $row['cnt'];

		}
		
		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;
		foreach($this->arr_age as $age){
			foreach($this->arr_sex as $sex){
				for($i=0;$i<$maxMonth;$i++){
					$statsData[$sex][$age][$i]	= (integer)$statsData[$sex][$age][$i];
				}
			}
		}

		$dataForChart = array();
		foreach($this->arr_sex as $sex){
			$idx = 0;
			foreach($this->arr_age as $age){
				$dataForChart[$sex][$idx][0]	= $age;
				$dataForChart[$sex][$idx][1]	= array_sum($statsData[$sex][$age]);
				$idx++;
			}
			
		}
		
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<$maxMonth;$i++){
			foreach($this->arr_age as $age){
				foreach($this->arr_sex as $sex){
					$dataForTable[$i][$age][$sex] = $statsData[$sex][$age][$i];
					$dataForTableSum[$age][$sex] += $statsData[$sex][$age][$i];
				}
			}
		}
		
		$this->template->assign(array(
			'dataForChart'		=> $dataForChart,
			'dataForTable'		=> $dataForTable,
			'dataForTableSum'	=> $dataForTableSum,
			'statsData'			=> $statsData,
			'arr_sex'			=> $this->arr_sex,
			'arr_age'			=> $this->arr_age,
		));
				
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 회원가입현황 - 지역별 */
	public function shop_member_monthly_location(){
		
		$this->tempate_modules();
		
		$this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();
		
		$this->arr_location = array();
		$query = $ZIP_DB->query("SELECT substring(SIDO,1,2) as location FROM `zipcode` GROUP BY location");
		foreach($query->result_array() as $row){
			if(!in_array($row['location'], $this->arr_location))	$this->arr_location[] = $row['location'];
		}
		
		$this->arr_sex = array('남','여','합계');
		
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
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				sex,
				substring(address,1,2) as location,
				count(*) as cnt
			from fm_member as a
			where zipcode is not null and zipcode!='' and year(regist_date)='{$_GET['year']}'
			group by stats_month, sex, location
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			
			$location = $row['location'];
			
			if($row['sex']=='male')			{
				$statsData['남'][$location][$row['stats_month']-1] = $row['cnt'];
			}		
			else if($row['sex']=='female')	{
				$statsData['여'][$location][$row['stats_month']-1] = $row['cnt'];
			}
			
			$statsData['합계'][$location][$row['stats_month']-1] += $row['cnt'];

		}
		
		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;
		foreach($this->arr_location as $location){
			foreach($this->arr_sex as $sex){
				for($i=0;$i<$maxMonth;$i++){
					$statsData[$sex][$location][$i]	= (integer)$statsData[$sex][$location][$i];
				}
			}
		}

		$dataForChart = array();
		foreach($this->arr_sex as $sex){
			$idx = 0;
			foreach($this->arr_location as $location){
				$dataForChart[$sex][$idx]	= array_sum($statsData[$sex][$location]);
				$idx++;
			}
			
		}
		
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<$maxMonth;$i++){
			foreach($this->arr_location as $location){
				foreach($this->arr_sex as $sex){
					$dataForTable[$i][$location][$sex] = $statsData[$sex][$location][$i];
					$dataForTableSum[$location][$sex] += $statsData[$sex][$location][$i];
				}
			}
		}

		foreach($this->arr_location as $location){
			foreach($this->arr_sex as $sex){
				$maxValue = $maxValue < $dataForTableSum[$location][$sex] ? $dataForTableSum[$location][$sex] : $maxValue;
			
			}
		}
		
		$this->template->assign(array(
			'dataForChart'		=> $dataForChart,
			'dataForTable'		=> $dataForTable,
			'dataForTableSum'	=> $dataForTableSum,
			'statsData'			=> $statsData,
			'maxValue'			=> $maxValue,
			'arr_sex'			=> $this->arr_sex,
			'arr_location'		=> $this->arr_location,
		));
				
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 회원가입현황 */
	public function shop_member_daily(){
		$this->admin_menu();
		$this->tempate_modules();
		
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_shop');
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
	
	/* 쇼핑몰분석 - 회원가입현황 - 성별 */
	public function shop_member_daily_sex(){
		
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
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				day(regist_date) as stats_day,
				sex,
				count(*) as cnt
			from fm_member
			where year(regist_date)='{$_GET['year']}' and month(regist_date)='{$_GET['month']}' and `status` != 'withdrawal'
			group by stats_day, sex
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {			
			if($row['sex']=='male')		$statsData['남성'][$row['stats_day']-1] = $row['cnt'];
			if($row['sex']=='female')	$statsData['여성'][$row['stats_day']-1] = $row['cnt'];
			$statsData['합계'][$row['stats_day']-1] += $row['cnt'];	
		}

		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		

		$dataForChart = array();
	
		for($i=0;$i<$maxDay;$i++){
			
			$dataForChart['남성'][$i][0]	= ($i+1).'일';
			$dataForChart['남성'][$i][1]	= (integer)$statsData['남성'][$i];
			$maxValue = $maxValue < $dataForChart['남성'][$i][1] ? $dataForChart['남성'][$i][1] : $maxValue;
			
			$dataForChart['여성'][$i][0]	= ($i+1).'일';
			$dataForChart['여성'][$i][1]	= (integer)$statsData['여성'][$i];
			$maxValue = $maxValue < $dataForChart['여성'][$i][1] ? $dataForChart['여성'][$i][1] : $maxValue;
			
			$dataForChart['합계'][$i][0]	= ($i+1).'일';
			$dataForChart['합계'][$i][1]	= (integer)$statsData['합계'][$i];
			$maxValue = $maxValue < $dataForChart['합계'][$i][1] ? $dataForChart['합계'][$i][1] : $maxValue;

		}
		
		$this->template->assign(array(
			'dataForChart'	=> $dataForChart,
			'maxValue'		=> $maxValue
		));
				
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 회원가입현황 - 연령별 */
	public function shop_member_daily_age(){
		
		$this->tempate_modules();
		
		$this->arr_age = array('10대 이하','20대','30대','40대','50대','60대 이상');
		$this->arr_sex = array('남','여','합계');
		
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
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				day(regist_date) as stats_day,
				sex,
				year(now())-year(birthday)+1 as age,
				count(*) as cnt
			from fm_member
			where birthday!='0000-00-00' and year(regist_date)='{$_GET['year']}' and month(regist_date)='{$_GET['month']}'
			group by stats_day, sex,age
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			
			if($row['age'] < 20)			$age = '10대 이하';
			else if($row['age'] < 30)		$age = '20대';
			else if($row['age'] < 40)		$age = '30대';
			else if($row['age'] < 50)		$age = '40대';
			else if($row['age'] < 60)		$age = '50대';
			else if($row['age'] >= 60)		$age = '60대 이상';
			else continue;
			
			if($row['sex']=='male')			$sex = "남";		
			else if($row['sex']=='female')	$sex = "여";
			else continue;
			
			$statsData[$sex][$age][$row['stats_day']-1] = $row['cnt'];
			$statsData['합계'][$age][$row['stats_day']-1] += $row['cnt'];

		}
		
		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		foreach($this->arr_age as $age){
			foreach($this->arr_sex as $sex){
				for($i=0;$i<$maxDay;$i++){
					$statsData[$sex][$age][$i]	= (integer)$statsData[$sex][$age][$i];
				}
			}
		}

		$dataForChart = array();
		foreach($this->arr_sex as $sex){
			$idx = 0;
			foreach($this->arr_age as $age){
				$dataForChart[$sex][$idx][0]	= $age;
				$dataForChart[$sex][$idx][1]	= array_sum($statsData[$sex][$age]);
				$idx++;
			}
			
		}
		
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<$maxDay;$i++){
			foreach($this->arr_age as $age){
				foreach($this->arr_sex as $sex){
					$dataForTable[$i][$age][$sex] = $statsData[$sex][$age][$i];
					$dataForTableSum[$age][$sex] += $statsData[$sex][$age][$i];
				}
			}
		}
		
		$this->template->assign(array(
			'dataForChart'		=> $dataForChart,
			'dataForTable'		=> $dataForTable,
			'dataForTableSum'	=> $dataForTableSum,
			'statsData'			=> $statsData,
			'arr_sex'			=> $this->arr_sex,
			'arr_age'			=> $this->arr_age,
		));
				
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 회원가입현황 - 지역별 */
	public function shop_member_daily_location(){
		
		$this->tempate_modules();
		
		$this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();
		
		$this->arr_location = array();
		$query = $ZIP_DB->query("SELECT substring(SIDO,1,2) as location FROM `zipcode` GROUP BY location");
		foreach($query->result_array() as $row){
			if(!in_array($row['location'], $this->arr_location))	$this->arr_location[] = $row['location'];
		}
		
		$this->arr_sex = array('남','여','합계');
		
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
				year(regist_date) as stats_year,
				month(regist_date) as stats_month,
				day(regist_date) as stats_day,
				sex,
				substring(address,1,2) as location,
				count(*) as cnt
			from fm_member as a
			where zipcode is not null and zipcode!='' and year(regist_date)='{$_GET['year']}' and month(regist_date)='{$_GET['month']}'
			group by stats_day, sex, location
			order by cnt desc
		";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			
			$location = $row['location'];
			
			if($row['sex']=='male')			{
				$statsData['남'][$location][$row['stats_day']-1] = $row['cnt'];
			}		
			else if($row['sex']=='female')	{
				$statsData['여'][$location][$row['stats_day']-1] = $row['cnt'];
			}
			
			$statsData['합계'][$location][$row['stats_day']-1] += $row['cnt'];

		}
		
		/* 데이터 가공 */
		$maxValue = 0;
		$maxDay = date('t',strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
		foreach($this->arr_location as $location){
			foreach($this->arr_sex as $sex){
				for($i=0;$i<$maxDay;$i++){
					$statsData[$sex][$location][$i]	= (integer)$statsData[$sex][$location][$i];
				}
			}
		}

		$dataForChart = array();
		foreach($this->arr_sex as $sex){
			$idx = 0;
			foreach($this->arr_location as $location){
				$dataForChart[$sex][$idx]	= array_sum($statsData[$sex][$location]);
				$idx++;
			}
			
		}
		
		$dataForTable = array();
		$dataForTableSum = array();		
		for($i=0;$i<$maxDay;$i++){
			foreach($this->arr_location as $location){
				foreach($this->arr_sex as $sex){
					$dataForTable[$i][$location][$sex] = $statsData[$sex][$location][$i];
					$dataForTableSum[$location][$sex] += $statsData[$sex][$location][$i];
				}
			}
		}
		
		foreach($this->arr_location as $location){
			foreach($this->arr_sex as $sex){
				$maxValue = $maxValue < $dataForTableSum[$location][$sex] ? $dataForTableSum[$location][$sex] : $maxValue;
			
			}
		}
		
		$this->template->assign(array(
			'dataForChart'		=> $dataForChart,
			'dataForTable'		=> $dataForTable,
			'dataForTableSum'	=> $dataForTableSum,
			'statsData'			=> $statsData,
			'maxValue'			=> $maxValue,
			'arr_sex'			=> $this->arr_sex,
			'arr_location'		=> $this->arr_location,
		));
				
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 구매횟수현황 */
	public function shop_sales_cnt(){
		$this->admin_menu();
		$this->tempate_modules();
		
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_shop');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
		
		/* 날짜 파라미터 */
		$s_date = !empty($_GET['s_date']) && is_numeric($_GET['s_date']) && strlen($_GET['s_date'])==6 ? $_GET['s_date'] : date('Ym',strtotime('-1 month'));
		$e_date = !empty($_GET['e_date'])  && is_numeric($_GET['e_date']) &&  strlen($_GET['e_date'])==6 ? $_GET['e_date'] : date('Ym');
		
		$this->template->assign(array(
			's_date'=>$s_date,
			'e_date'=>$e_date
		));
		
		$s_date = substr($s_date,0,4).'-'.substr($s_date,4,2).'-01 00:00:00';
		$e_date = substr($e_date,0,4).'-'.substr($e_date,4,2).'-31 23:59:59';

		//$this->db->query("update fm_member as a set order_cnt=(select count(*) as cnt from fm_order where member_seq=a.member_seq) ");
	
		$query = $this->db->query("select a.sex as sex,
			sum(if(a.real_order_cnt = 1,1,0)) as cnt1,
			sum(if(a.real_order_cnt = 2,1,0)) as cnt2,
			sum(if(a.real_order_cnt = 3,1,0)) as cnt3,
			sum(if(a.real_order_cnt >= 4 and a.real_order_cnt <= 5,1,0)) as cnt4,
			sum(if(a.real_order_cnt >= 6 and a.real_order_cnt <= 9,1,0)) as cnt6,
			sum(if(a.real_order_cnt >= 10 and a.real_order_cnt <= 29,1,0)) as cnt10,
			sum(if(a.real_order_cnt >= 30 and a.real_order_cnt <= 49,1,0)) as cnt30,
			sum(if(a.real_order_cnt >= 50,1,0)) as cnt50
			from (
				select b.sex, count(*) as real_order_cnt from fm_order a left join fm_member b on a.member_seq=b.member_seq where deposit_yn='y' and a.regist_date between ? and ? group by a.member_seq
			) as a
			where a.sex is not null
			group by a.sex
		",array($s_date,$e_date));
		$result = $query->result_array();
		
		foreach($query->result_array() as $row) {			
			foreach($row as $k=>$v){
				if(preg_match("/^cnt[0-9]+$/",$k)){
					$key = str_replace('cnt','',$k);
					if($row['sex']=='male')		$statsData['남성'][$key] = $v;
					if($row['sex']=='female')	$statsData['여성'][$key] = $v;
					$statsData['합계'][$key] += $v;	
				}
			}
		}
		
		$maxValue = 0;
		
		$dataForTable = array();
		foreach($statsData['합계'] as $key=>$row){
			$keyName = $key >= 4 ? $key.'회 이상' : $key.'회'; 
			
			$dataForTable[$key]['keyName'] = $keyName;
			$dataForTable[$key]['합계'] = $statsData['합계'][$key];
			$dataForTable[$key]['남성'] = $statsData['남성'][$key];
			$dataForTable[$key]['여성'] = $statsData['여성'][$key];

		}
		krsort($dataForTable);
		
		$dataForChart = array();
		foreach($statsData['합계'] as $key=>$row){
			
			$keyName = $key >= 4 ? $key.'회 이상' : $key.'회'; 
			
			$dataForChart['합계'][] = array($statsData['합계'][$key],$keyName);
			$dataForChart['남성'][] = array($statsData['남성'][$key],$keyName);
			$dataForChart['여성'][] = array($statsData['여성'][$key],$keyName);

			$maxValue	=	$maxValue < $statsData['남성'][$key] ? $statsData['남성'][$key] : $maxValue;
			$maxValue	=	$maxValue < $statsData['여성'][$key] ? $statsData['여성'][$key] : $maxValue;
		}
		
		$this->template->assign(array(
			'dataForChart'		=> $dataForChart,
			'dataForTable'		=> $dataForTable,
			'statsData'			=> $statsData,
			'maxValue'			=> $maxValue
		));
		
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	/* 쇼핑몰분석 - 구매횟수현황 */
	public function shop_sales_price(){
		$this->admin_menu();
		$this->tempate_modules();
		
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_shop');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
		
		/* 날짜 파라미터 */
		$s_date = !empty($_GET['s_date']) && is_numeric($_GET['s_date']) && strlen($_GET['s_date'])==6 ? $_GET['s_date'] : date('Ym',strtotime('-1 month'));
		$e_date = !empty($_GET['e_date'])  && is_numeric($_GET['e_date']) &&  strlen($_GET['e_date'])==6 ? $_GET['e_date'] : date('Ym');
		
		$this->template->assign(array(
			's_date'=>$s_date,
			'e_date'=>$e_date
		));
		
		$s_date = substr($s_date,0,4).'-'.substr($s_date,4,2).'-01 00:00:00';
		$e_date = substr($e_date,0,4).'-'.substr($e_date,4,2).'-31 23:59:59';
		
		if(strtotime('-1 year',strtotime($e_date)) > strtotime($s_date)) {
			$s_date_time = strtotime('-12 month',strtotime($e_date));
			$s_date = date('Y-m-d 00:00:00',$s_date_time);
			$this->template->assign(array('s_date'=>date('Ym',$s_date_time)));
		}

		//$this->db->query("update fm_member as a set order_cnt=(select count(*) as cnt from fm_order where member_seq=a.member_seq) ");
	
		$query = $this->db->query("select 
			a.sex as sex,
			sum(if(a.real_order_price < 10000,1,0)) as cnt0,
			sum(if(a.real_order_price >= 10000 and a.real_order_price < 30000,1,0)) as cnt1,
			sum(if(a.real_order_price >= 30000 and a.real_order_price < 50000,1,0)) as cnt3,
			sum(if(a.real_order_price >= 50000 and a.real_order_price < 100000,1,0)) as cnt5,
			sum(if(a.real_order_price >= 100000 and a.real_order_price < 200000,1,0)) as cnt10,
			sum(if(a.real_order_price >= 200000 and a.real_order_price < 300000,1,0)) as cnt20,
			sum(if(a.real_order_price >= 300000,1,0)) as cnt30
			from (
				select b.sex, sum(settleprice) as real_order_price  from fm_order a left join fm_member b on a.member_seq=b.member_seq where deposit_yn='y' and a.regist_date between ? and ? group by a.member_seq
			) as a
			where a.sex is not null
			group by a.sex
		",array($s_date,$e_date));
		$result = $query->result_array();
		
		foreach($query->result_array() as $row) {			
			foreach($row as $k=>$v){
				if(preg_match("/^cnt[0-9]+$/",$k)){
					$key = str_replace('cnt','',$k);
					if($row['sex']=='male')		$statsData['남성'][$key] = $v;
					if($row['sex']=='female')	$statsData['여성'][$key] = $v;
					$statsData['합계'][$key] += $v;	
				}
			}
		}
		$maxValue = 0;
		
		$dataForTable = array();
		foreach($statsData['합계'] as $key=>$row){
			$keyName = $key == 0 ? '1만원 미만' : number_format($key).'만원 이상'; 
			
			$dataForTable[$key]['keyName'] = $keyName;
			$dataForTable[$key]['합계'] = $statsData['합계'][$key];
			$dataForTable[$key]['남성'] = $statsData['남성'][$key];
			$dataForTable[$key]['여성'] = $statsData['여성'][$key];

		}
		krsort($dataForTable);
		
		$dataForChart = array();
		foreach($statsData['합계'] as $key=>$row){
			
			$keyName = $key == 0 ? '1만원 미만' : number_format($key).'만원 이상';
			
			$dataForChart['합계'][] = array($statsData['합계'][$key],$keyName);
			$dataForChart['남성'][] = array($statsData['남성'][$key],$keyName);
			$dataForChart['여성'][] = array($statsData['여성'][$key],$keyName);

			$maxValue	=	$maxValue < $statsData['남성'][$key] ? $statsData['남성'][$key] : $maxValue;
			$maxValue	=	$maxValue < $statsData['여성'][$key] ? $statsData['여성'][$key] : $maxValue;
		}
		
		$this->template->assign(array(
			'dataForChart'		=> $dataForChart,
			'dataForTable'		=> $dataForTable,
			'statsData'			=> $statsData,
			'maxValue'			=> $maxValue
		));
		
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file statistic_promotion.php */
/* Location: ./app/controllers/admin/statistic_promotion.php */