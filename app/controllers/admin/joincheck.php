<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class joincheck extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->stateNames = array(
		'before' 	=> '진행 전',
		'ing' 		=> '진행 중',
		'end'		=> '진행완료',
		'stop'		=> '중단'
		);

		$this->typeNames = array(
		'stamp'		=> '스탬프형 ',
		'comment'   => '댓글형',
		'login'		=> '로그인형'
		);
		$this->clear_typeNames = array(
		'count'		=> '횟수',
		'straight'   => '연속'
		);

		$this->clear_successNames = array(
		'Y'		=> '달성',
		'N'   => '미달성'
		);

	}

	public function index()
	{
		redirect("/admin/joincheck/catalog");
	}

	public function catalog()
	{
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('joincheck_view');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$today['today']= date('Ymd');
		$this->template->assign('date',$today);

		if( count($_GET) == 0 ){
			//$_GET['event_status'] = array('before','ing','end','stop');
			//$_GET['event_type'] = array('stamp','comment','login');
			//$_GET['event_clear_type'] = array('count','straight');
		}

		### SEARCH
		$sc						= $_GET;
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

		$where = array();

		// 검색어
		if( $_GET['keyword'] ){
			$where[] = "
				CONCAT(
					title
				) LIKE '%" . addslashes($_GET['keyword']) . "%'
			";
		}

		// 일자검색
		if( $_GET['date'] ){
			$date_field = $_GET['date'];
			if($_GET['sdate'] && $_GET['edate']) $where[] = "date_format({$date_field},'%Y-%m-%d') between '{$_GET['sdate']}' and '{$_GET['edate']}'";
			else if($_GET['sdate']) $where[] = "'{$_GET['sdate']}' < date_format({$date_field},'%Y-%m-%d')";
			else if($_GET['edate']) $where[] = "date_format({$date_field},'%Y-%m-%d') < '{$_GET['edate']}'";
		}

		// 이벤트진행상태
		if( $_GET['event_status'] ){
			$arr = array();
			foreach($_GET['event_status'] as $key => $data){
				switch($data){
					case "before":
						$arr[] = "start_date > current_date()";
					break;
					case "ing":
						$arr[] = "current_date() between start_date and end_date and check_state <> 'stop'";
					break;
					case "end":
						$arr[] = "end_date < current_date()";
					break;
					case "stop":
						$arr[] = "check_state = 'stop'";
					break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

	// 출석체크 방법
		if( $_GET['event_type'] ){
			$arr = array();
			foreach($_GET['event_type'] as $key => $data){

				switch($data){
					case "stamp":
						$arr[] = "check_type = 'stamp'";
					break;
					case "comment":
						$arr[] = "check_type = 'comment'";
					break;
					case "login":
						$arr[] = "check_type = 'login'";
					break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

	// 달성조건
		if( $_GET['event_clear_type'] ){
			$arr = array();
			foreach($_GET['event_clear_type'] as $key => $data){

				switch($data){
					case "count":
						$arr[] = "check_clear_type = 'count'";
					break;
					case "straight":
						$arr[] = "check_clear_type = 'straight'";
					break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

		// 삭제되지 않은것
		$arr=array();
		$arr[]= "del_state='N'";
		$where[] = "(".implode(' and ', $arr).")";

		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";


		//이벤트 진행 상황
		$query = $this->db->query("select
			sum(if(current_date() between start_date and end_date,1,0)) as '0',
			sum(if(end_date < current_date(),1,0)) as '1',
			sum(if(current_date() < start_date,1,0)) as '2'
		from fm_joincheck {$sqlWhereClause}");
		list($count['ing'],$count['end'],$count['before']) = $query->row_array();

		//리스트 쿼리
		$sql = "SELECT SQL_CALC_FOUND_ROWS *	,
		( select count(jcresult_seq)AS 'cnt' from fm_joincheck_result where joincheck_seq=evt.joincheck_seq
			) as sum_count,
			if(check_state = 'stop','중지',if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'진행완료','진행 전')))
			 as status
		FROM fm_joincheck as evt
		{$sqlWhereClause}
		ORDER BY joincheck_seq DESC";

		$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		$redata = $result['record'];

		$query = $this->db->query("select count(*) as '0',
			sum(if(current_date() between start_date and end_date,1,0)) as '1',
			sum(if(end_date < current_date(),1,0)) as '2'
		from fm_joincheck ");
		list($count['total'],$count['ing'],$count['end']) = $query->row_array();


		$sc['searchcount']	 = $result['totalcount'];
		$sc['totalcount']	 = get_rows('fm_joincheck');
		$idx = 0;
		foreach($redata as $datarow){
			$idx++;

			//$datarow['catename']	= $this->categorymodel->get_category_name($datarow['category_code']);
			$datarow['mcheck_state'] = $this->stateNames[$datarow['check_state']];
			$datarow['mcheck_type'] = $this->typeNames[$datarow['check_type']];
			$datarow['mcheck_clear_type'] = $this->clear_typeNames[$datarow['check_clear_type']];

			//스킨별 팝업사이즈 지정
			if($datarow['check_type'] == 'comment')
			{$datarow['sz1']='680'; $datarow['sz2']='700';}
			else{$datarow['sz1']='545'; $datarow['sz2']='670';}

			//달성현황
			$suc_q="select count(*)	as sum_clear
			 from fm_joincheck_result where joincheck_seq='".$datarow['joincheck_seq']."'
			 and clear_success='Y'";
			$query = $this->db->query($suc_q);
			$suc_count = $query ->row_array();
			$sum_clear = $suc_count['sum_clear'];

			//적립금 현황
			$emny_q="select count(*) as sum_emoney
			 from fm_joincheck_result where joincheck_seq='".$datarow['joincheck_seq']."'
			 and emoney_pay='Y'";
			$query = $this->db->query($emny_q);
			$emny_count = $query ->row_array();
			$sum_emoney = $emny_count['sum_emoney'];

			$datarow['sum_clear'] = $sum_clear;
			$datarow['sum_emoney'] = $sum_emoney;

			$dataloop[] = $datarow;



		}
		if(isset($redata)) $this->template->assign('loop',$dataloop);
		$this->template->assign($result);
			$this->template->assign(array(
			'count'=>$count,
			'sc'=>$sc
		));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");

	}


	public function regist()
	{
		$this->admin_menu();
		$this->tempate_modules();

		$this->file_path	= $this->template_path();
		$joincheck_seq=$_GET['joincheck_seq'];


		$sql = "SELECT *,
		if(check_state = 'stop','중지',if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'진행완료','진행 전')))
		 as status
		FROM fm_joincheck where joincheck_seq='".$joincheck_seq."'";
		$query = $this->db->query($sql);
		$result= $query->row_array();

		$config_basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$result['shopName'] = $config_basic['shopName'];

	//스킨별 팝업사이즈 지정
			if($result['check_type'] == 'comment')
			{$result['sz1']='680'; $result['sz2']='700';}
			else{$result['sz1']='545'; $result['sz2']='670';}

		$this->template->assign(array('joincheck'=>$result));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}



	public function memberlist()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if( count($_GET) == 0 ){
			$_GET['emoney_pay '] = array('N','Y');
			$_GET['clear_success'] = array('N','Y');
		}


		$joincheck_seq = (!empty($_POST['joincheck_seq']))?$_POST['joincheck_seq']:$_GET['joincheck_seq'];

		### SEARCH
		$sc						= $_GET;
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';


		$where = array();

		// 검색어
		if( $_GET['keyword'] ){
			$where[] = "
				CONCAT(
					mem.userid,mem.user_name
				) LIKE '%" . addslashes($_GET['keyword']) . "%'
			";
		}

		// 이벤트진행상태
		if( $_GET['clear_success'] ){
			$arr = array();
			foreach($_GET['clear_success'] as $key => $data){
				switch($data){
				case "Y":
					$arr[] = "clear_success = 'Y'";
				break;
				case "N":
					$arr[] = "clear_success = 'N'";
				break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

		// 적립금 지급 여부
		if( $_GET['emoney_pay'] ){
			$arr = array();
			foreach($_GET['emoney_pay'] as $key => $data){

				switch($data){
					case "Y":
						$arr[] = "emoney_pay = 'Y'";
					break;
					case "N":
						$arr[] = "emoney_pay = 'N'";
					break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

		//
		if( $joincheck_seq ){
			$arr = array();
			$arr[] = "joincheck_seq = '".$joincheck_seq."'";
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";


		//이벤트 회원 진행 상황
		$sql = "select SQL_CALC_FOUND_ROWS fjr.*	,
		mem.userid,
		mem.user_name
		from fm_joincheck_result fjr
		left join fm_member as mem on mem.member_seq=fjr.member_seq
		{$sqlWhereClause} order by fjr.jcresult_seq desc";

		$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		$redata = $result['record'];


		//달성현황
		$suc_q="select count(*)	as sum_clear
		 from fm_joincheck_result where joincheck_seq='".$joincheck_seq."'
		 and clear_success='Y'";
		$query = $this->db->query($suc_q);
		$suc_count = $query ->row_array();
		$rc['sum_clear'] = $suc_count['sum_clear'];

		//적립금 현황
		$emny_q="select count(*) as sum_emoney
		 from fm_joincheck_result where joincheck_seq='".$joincheck_seq."'
		 and emoney_pay='Y'";
		$query = $this->db->query($emny_q);
		$emny_count = $query ->row_array();
		$rc['sum_emoney'] = $emny_count['sum_emoney'];

		//이벤트 정보 가져오기
		$event_q="select * from fm_joincheck where joincheck_seq='".$joincheck_seq."'";
		$query = $this->db->query($event_q);
		$jc_event = $query ->row_array();
		$rc['title'] = $jc_event['title'];
		$rc['check_SMS'] = $jc_event['check_SMS'];



		foreach($redata as $datarow){

			$datarow['mclear_success'] = $this->clear_successNames[$datarow['clear_success']];

			//적립금 지급여부에 따라 금액/미지급 표시
			if($datarow['emoney_pay'] == 'Y' ){
				$datarow['memoney'] = $datarow['emoney'];
			}else{
				$datarow['memoney'] = '미지급';
			}
			$mbtel = (isset($minfo['cellphone']))?$minfo['cellphone']:$minfo['phone'];

			$datarow['allcount'] =  $jc_event['check_clear_count'];
			// 연속일때, 횟수 일때
			if($jc_event['check_clear_type']== 'count'){
				$datarow['usercount'] =	"총 ".$datarow['count_cnt'];
			}elseif($jc_event['check_clear_type']=='straight'){
				$datarow['usercount'] = "연속 ".$datarow['straight_cnt'];
			}

			$dataloop[] = $datarow;
		}



		if(isset($redata)) $this->template->assign('loop',$dataloop);
		$this->template->assign($result);
		$this->template->assign(array('rc'=>$rc, 'sc'=>$sc));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
}

/* End of file event.php */
/* Location: ./app/controllers/admin/event.php */