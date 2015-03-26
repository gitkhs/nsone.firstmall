<?php
class visitorlog extends CI_Model {

	var $current_date;
	var $current_year;
	var $current_month;
	var $current_day;
	var $current_hour;
	var $current_ip;
	var $referer_flag;

	var $arr_referer_sitecd = array(
		'basket.co.kr'	=> '바스켓',
		'yozm.daum.net'	=> 'yozm',
		'daum.net'		=> 'daum',
		'c.cyworld.com'	=> 'clog',
		'naver.com'		=> 'naver',
		'facebook.com'	=> 'facebook',
		'twitter.com'	=> 'twitter',
		'me2day.net'	=> 'me2day',
		'google.com'	=> 'google',
		'google.co.kr'	=> 'google',
		'nate.com'		=> 'nate',
	);

	var $arr_referer_sitecd_name = array(
		'about'			=> '어바웃',
		'yozm'			=> '요즘',
		'daum'			=> '다음',
		'daum_shopping' => '다음쇼핑하우',
		'clog'			=> 'C로그',
		'naver'			=> '네이버',
		'naver_shopping' => '네이버지식쇼핑',
		'facebook'		=> '페이스북',
		'twitter'		=> '트위터',
		'me2day'		=> '미투데이',
		'google'		=> '구글',
		'nate'			=> '네이트',
		'nate_keyword'	=> '네이트키워드',
		'naver_keyword'	=> '네이버키워드',
		'daum_keyword'	=> '다음키워드',
		'google_keyword'=> '구글키워드',
		'etc'			=> '기타사이트',
		'direct'		=> '직접유입',
	);

	function __construct() {
		parent::__construct();

		$this->load->helper('cookie');

		$this->current_ip = $_SERVER['REMOTE_ADDR'];
		$this->current_date = date('Y-m-d');
		$this->current_hour = date('H');

		list(
			$this->current_year,
			$this->current_month,
			$this->current_day
		) = explode('-',$this->current_date);
	}

	function get_arr_referer_sitecd_name(){
		$data = $this->arr_referer_sitecd_name;

		/* 추가광고매체 타이틀 */
		$query = $this->db->query("select * from fm_inflow");
		$result = $query->result_array();
		foreach($result as $row){
			$data[$row['inflow_code']] = $row['title'];
		}

		return $data;
	}

	function execute(){

		/* 통계수집 제외 아이피 체크 */
		if($this->is_exclude_ip()) return;

		/* robots 체크 */
		if($this->is_robots_agent()) return;
		if($this->is_robots_page()) return;

		/* 페이지뷰 증가 */
		$this->add_pv_count();

		/* 리퍼러 저장 */
		$this->set_referer();

		/* 오늘 첫접속 여부 체크 */
		if($this->first_connection()){

			/* 첫접속시 방문자수 증가 */
			$this->add_visit_count();

			/* 첫접속시 접속 환경 저장 */
			$this->add_platform();

			/* 첫접속시 접속자정보 쿠키 생성 */
			$this->set_visitor_cookie();
		}
	}

	/* BOT 접속 페이지들 체크 */
	public function is_robots_page(){
		$robotsPages	= array('payment', 'partner');
		if($robotsPages && preg_match("/^\/(".implode("|",$robotsPages).")/i",$_SERVER['REQUEST_URI'])) return true;

		return false;
	}

	/* robots 체크 */
	public function is_robots_agent(){
		/* v20130408 */
		$robotsAgents = array('yeti','naverbot','googlebot','msnbot','slurp','yandexbot','gigabot','teoma','twiceler','scrubby','robozilla','nutch','baiduspider','webauto','mmcrawler','yahoo-blog','psbot','cowbot','daumos','daumoa','mj12bot','bingbot','blexbot','ahrefsbot');
		if($robotsAgents && preg_match("/(".implode("|",$robotsAgents).")/i",$_SERVER['HTTP_USER_AGENT'])) return true;
		return false;
	}

	/* 통계수집 제외 아이피 체크 */
	public function is_exclude_ip(){
		$statisticExcludeIps = explode("\n",$this->config_system['statisticExcludeIp']);		
		foreach($statisticExcludeIps as $statisticExcludeIp){
			if($statisticExcludeIp && preg_match("/^".$statisticExcludeIp."/",$this->current_ip)){
				return true;
			}
		}
		return false;
	}

	/* 페이지뷰 증가 */
	function add_pv_count(){

		$hourField = "h".$this->current_hour;

		$query = $this->db->query("select * from fm_stats_visitor_count where count_type='pv' and stats_date=?",$this->current_date);
		$result = $query->row_array();

		if($query->num_rows){
			$countSum = 0;
			for($i=0;$i<24;$i++) $countSum += $result['h'.sprintf("%02d",$i)];
			$countSum += 1;

			$this->db->query("update fm_stats_visitor_count set `{$hourField}`=ifnull(`{$hourField}`,0)+1, count_sum=? where stats_date=? and count_type=?",array($countSum,$this->current_date,'pv'));
		}else{
			$data = array(
				'stats_date'	=> $this->current_date,
				'stats_year'	=> $this->current_year,
				'stats_month'	=> $this->current_month,
				'stats_day'		=> $this->current_day,
				'count_type'	=> 'pv',
				'count_sum'		=> 1,
				$hourField		=> 1
			);
			$this->db->insert('fm_stats_visitor_count', $data);
		}

	}

	/* 오늘 첫접속 여부 체크 */
	function first_connection(){
		$visitorInfo = unserialize(get_cookie('visitorInfo'));

		// 쿠키가 있고, 오늘날짜와 같으면 첫접속아님
		if(!empty($visitorInfo) && $visitorInfo['date']==date('Y-m-d')) return false;

		// IP테이블에 데이터가 있으면 첫접속 아님
		if(get_data("fm_stats_visitor_ip",array("ip_address"=>$this->current_ip,"stats_date"=>$this->current_date))) return false;

		// 첫접속
		return true;
	}

	/* 첫접속시 접속자정보 쿠키 생성 */
	function set_visitor_cookie(){
		$visitorInfo = array(
			'date'=>date('Y-m-d'),
			'referer'=>$_SERVER['HTTP_REFERER']
		);
		set_cookie('visitorInfo',serialize($visitorInfo),86400);

		// IP테이블에 데이터 저장
		$data = array(
			'stats_date'	=> $this->current_date,
			'ip_address'	=> $this->current_ip,
			'user_agent'	=> $_SERVER['HTTP_USER_AGENT'],
			'referer'		=> $_SERVER['HTTP_REFERER'],
		);
		$this->db->insert('fm_stats_visitor_ip', $data);

	}

	/* 첫접속시 방문자수 증가 */
	function add_visit_count(){
		$hourField = "h".$this->current_hour;

		$query = $this->db->query("select * from fm_stats_visitor_count where count_type='visit' and stats_date=?",$this->current_date);
		$result = $query->row_array();

		if($query->num_rows){
			$countSum = 0;
			for($i=0;$i<24;$i++) $countSum += $result['h'.sprintf("%02d",$i)];
			$countSum += 1;

			$this->db->query("update fm_stats_visitor_count set `{$hourField}`=ifnull(`{$hourField}`,0)+1, count_sum=? where stats_date=? and count_type=?",array($countSum,$this->current_date,'visit'));
		}else{

			$data = array(
				'stats_date'	=> $this->current_date,
				'stats_year'	=> $this->current_year,
				'stats_month'	=> $this->current_month,
				'stats_day'		=> $this->current_day,
				'count_type'	=> 'visit',
				'count_sum'		=> 1,
				$hourField		=> 1
			);
			$this->db->insert('fm_stats_visitor_count', $data);
		}
	}

	/* 접속시 리퍼러 저장 */
	function set_referer(){

		// 통계 제외 대상 체크
		if($this->is_exclude_ip())		return;
		if($this->is_robots_agent())	return;
		if($this->is_robots_page())		return;

		if	($this->referer_flag != 'y'){
			$this->referer_flag	= 'y';

			$referer		= !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
			$referer_sitecd	= '';
			$save_flag		= false;
			$old_referer	= $this->session->userdata('shopReferer');
			if	(!$old_referer && $_SESSION['shopReferer']){
				$old_referer	= $_SESSION['shopReferer'];
				$this->session->set_userdata('shopReferer',$old_referer);	// 셰션유지용
			}

			// 1. referer가 있나?
			if	($referer){
				$referer_bak		= $referer;
				$refererParsed		= parse_url($referer);
				$chk_now_host		= preg_replace("/^(www|m)\./i","",$_SERVER['HTTP_HOST']);
				$chk_referer_host	= preg_replace("/^(www|m)\./i","",$refererParsed['host']);
				$chk_config_host	= preg_replace("/^(www|m)\./i","",$this->config_system['domain']);
				if($chk_now_host == $chk_referer_host)
					$referer=''; // 쇼핑몰 내에서 이동시에는 referer 직접입력으로 처리
				if($chk_config_host && $chk_config_host==$chk_referer_host)
					$referer=''; // 쇼핑몰 내에서 이동시에는 referer 직접입력으로 처리
				if(preg_match('/^http[s]*\:\/\/(m\.)*[^\.]*\.firstmall\.kr/', $referer) || preg_match('/^http[s]*\:\/\/(m\.)*[^\.]*\.marketshop\.kr/', $referer))
					$referer=''; // 임시 도메인 직접입력으로 처리

				// 2. host와 같나?
				if	($referer){

					// 3. session이 있나?
					if		(!$old_referer)				$save_flag	= true;

					// 3-3. session의 referer와 현재 referer가 같나?
					elseif	($referer != $old_referer)	$save_flag	= true;

				}//elseif	(!$old_referer)					$save_flag	= false;

			// 1-1. session이 있나?
			}elseif	(!$old_referer && !isset($_SESSION['shopReferer']))	$save_flag	= true;

			// 아이프레임 예외처리
			if($_GET['iframe'] || $_GET['firstmallcartid'])
				$save_flag = false;

			// 새로운 session 생성 및 유입경로 log 저장
			if	($save_flag){
				$this->save_referer_log($referer);
			}
		}
	}

	// 새로운 session 생성 및 유입경로 log 저장
	public function save_referer_log($referer){

		$this->session->set_userdata('shopReferer',$referer);

		// 코드이그나이터 셰션이 불안정해서 추가로 굽는다.
		$_SESSION['shopReferer']	= $this->session->userdata('shopReferer');

		// 유입도메인
		$refererDomain	= $this->get_referer_domain($referer);
		$this->session->set_userdata('refererDomain',$refererDomain);

		//유입매체
		$referer_sitecd		= $this->get_referer_sitecd($referer);
		if	(!$this->session->userdata('marketplace') && $referer_sitecd)
			$this->session->set_userdata('marketplace',$referer_sitecd);

		$query = $this->db->query("select * from fm_stats_visitor_referer where stats_date=? and referer=? and referer_sitecd=?",array($this->current_date,$referer,$referer_sitecd));
		$result = $query->row_array();

		$tmp	= parse_url($referer);
		$domain	= $tmp['host'];
		$domain	= preg_replace('/^(www\.|m\.)/', '', $domain);

		if($query->num_rows){
			$this->db->query("update fm_stats_visitor_referer set count=count+1 where stats_date=? and referer=? and referer_sitecd=?",array($this->current_date,$referer,$referer_sitecd));
		}else{
			$data = array(
				'stats_date'		=> $this->current_date,
				'stats_year'		=> $this->current_year,
				'stats_month'		=> $this->current_month,
				'stats_day'			=> $this->current_day,
				'referer_domain'	=> $domain, 
				'referer'			=> $referer,
				'referer_sitecd'	=> $referer_sitecd,
				'count'				=> 1
			);
			$this->db->insert('fm_stats_visitor_referer', $data);
		}
	}

	/* 리퍼러 사이트코드 반환 */
	function get_referer_sitecd($referer){

		/* 추가광고매체를 통한 접속일 경우 */
		if($this->uri->segment(1)=='ad' && !empty($_GET['code'])){
			return $_GET['code'];
		}

		if( $_GET['market'] ){
			$market = $_GET['market'];
			switch($market){
				case "naver" : $market = "naver_shopping"; return $market; break;
				case "about" : $market = "about"; return $market; break;
				case "daum" : $market = "daum_shopping"; return $market; break;
			}
		}

		$bits = parse_url($referer);
		$bits['host'] = preg_replace("/^www\./","",$bits['host']);
		$bits['host'] = preg_replace("/^m\./","",$bits['host']);

		if(empty($bits['host'])) $sitecd = "direct";
		else{
			$sitecd = "etc";
			foreach($this->arr_referer_sitecd as $domain=>$cd){

				$domain = preg_replace("/^www\./","",$domain);
				$domain = preg_replace("/^m\./","",$domain);

				$regexp = addslashes($domain);
				$regexp = str_replace("/","\\/",$regexp);
				$regexp = str_replace(".","\\.",$regexp);

				if($bits['host']==$domain || preg_match("/^([a-z0-9-_]+\.){0,1}".$regexp."$/",$bits['host']))	{
					$sitecd = $cd;
					break;
				}
			}
		}

		if($sitecd=='naver'		&& !empty($_GET['NVADKWD']))	$sitecd = "naver_keyword";
		if($sitecd=='nate'		&& !empty($_GET['DMSKW']))		$sitecd = "nate_keyword";
		if($sitecd=='google'	&& $bits['path']=='/aclk')		$sitecd = "google_keyword";
		if($sitecd=='daum'		&& !empty($_GET['OVRAW']))		$sitecd = "daum_keyword";

		return $sitecd;

	}

	/* 접속 환경 로그 기록 */
	function add_platform(){

		$platform	= 'P';
		if		($this->fammerceMode || $this->storefammerceMode)
			$platform	= 'F';
		elseif	($this->mobileMode || $this->storemobileMode)
			$platform	= 'M';

		$query = $this->db->query("select * from fm_stats_visitor_platform where stats_date=? and platform=?",array($this->current_date,$platform));

		if($query->num_rows){
			$this->db->query("update fm_stats_visitor_platform set count=count+1 where stats_date=? and platform=? ",array($this->current_date,$platform));
		}else{
			$data = array(
				'stats_date'		=> $this->current_date,
				'stats_year'		=> $this->current_year,
				'stats_month'		=> $this->current_month,
				'stats_day'			=> $this->current_day,
				'platform'			=> $platform,
				'count'				=> 1
			);
			$this->db->insert('fm_stats_visitor_platform', $data);
		}
	}

	public function get_referer_domain($referer){

		$tmp	= parse_url($referer);
		//@2015-01-08 한글도메인일 때 처리
		$referer_domain	= (preg_match("/[\xA1-\xFE\xA1-\xFE]/",$tmp['host']))?$_SERVER['HTTP_HOST']:$tmp['host'];
		$referer_domain	= preg_replace('/^(www\.|m\.)/', '', $referer_domain);

		return $referer_domain;
	}

	public function ip_delete(){
		$query = "delete from fm_stats_visitor_ip where stats_date < ?";
		$this->db->query($query,date('Y-m-d',strtotime('-30 day')));
	}
}
?>