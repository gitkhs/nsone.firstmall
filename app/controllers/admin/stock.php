<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class stock extends admin_base {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('stockmodel');
	}

	public function index()
	{
		redirect("/admin/statistic_visitor");		
	}
	
	public function history_catalog(){
		$this->admin_menu();
		$this->tempate_modules();
		
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('refund_view');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
		
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');

		if( count($_GET) == 0 ){
			$_GET['sdate_key'] = "regist_date";
			$_GET['sdate'] = date('Y-m-d',strtotime('-1 week'));
			$_GET['edate'] = date('Y-m-d');
		}
		
		### SEARCH
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):'20';

		$sqlSelectClause = "
			SELECT stk.*,spl.supplier_seq,spl.supplier_name,
			(
				SELECT sum(ifnull(ea,0)) as input_count FROM fm_stock_history_item WHERE stock_code=stk.stock_code
			) input_count,
			(
				SELECT sum(ifnull(supply_price,0)*ea) as supply_price_sum FROM fm_stock_history_item WHERE stock_code=stk.stock_code
			) supply_price_sum,
			mng.mname as manager_name
		";
		$sqlFromClause = "
			FROM
			fm_stock_history as stk
			left join fm_supplier as spl on stk.supplier_seq = spl.supplier_seq
			left join fm_manager as mng on stk.manager_id = mng.manager_id
		";
		$sqlWhereClause = "";
		$sqlGroupbyClause = "group by stk.stock_code";

		$where = array();

		// 검색어
		if( $_GET['keyword'] ){
			$where[] = "
			CONCAT(
					ifnull(stk.stock_code,''),
					ifnull(spl.supplier_seq,''),
					ifnull(spl.supplier_name,''),
					ifnull(mng.manager_id,''),
					ifnull(mng.mname,'')
				) LIKE '%" . $_GET['keyword'] . "%'
			";
		}

		// 입고일
		$date_field = "stk.".$_GET['sdate_key'];
		if($_GET['sdate']){
			$where[] = $date_field." >= '".$_GET['sdate']." 00:00:00'";
		}
		if($_GET['edate']){
			$where[] = $date_field." <= '".$_GET['edate']." 24:00:00'";
		}

		// 주문상태
		if( $_GET['reason'] ){
			$where[] = "stk.reason = '".$_GET['reason']."'";
		}
		
		// 상품코드
		if( $_GET['goods_seq'] ){
			$sqlFromClause .= "
				inner join fm_stock_history_item stkitem on (stk.stock_code = stkitem.stock_code and stkitem.goods_seq='{$_GET['goods_seq']}')
			";
		}

		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";
		$query = "
		{$sqlSelectClause}
		{$sqlFromClause}
		{$sqlWhereClause}
		ORDER BY stk.stock_history_seq DESC";
		
		$loop = select_page($_GET['perpage'],$_GET['page'],10,$query,'');
		$loop['page']['querystring'] = get_args_list();
		
		foreach($loop['record'] as $k => $data)
		{
			$no++;
			
			$loop['record'][$k]['mreason'] = $this->stockmodel->arr_reason[$data['reason']];
			
			$query = $this->db->query("SELECT distinct goods_seq FROM fm_stock_history_item WHERE stock_code=?",$data['stock_code']);
			$loop['record'][$k]['goods_count'] = count($query->result_array());
		
		}


		$this->template->define(array('tpl' => $this->template_path()));
		$this->template->assign('loop',$loop['record']);
		$this->template->assign('page',$loop['page']);
		
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
	
	public function history_view(){
		$this->admin_menu();
		$this->tempate_modules();
		
		$stock_history_seq = $_GET['no'];
		
		$query = $this->db->query("
		select a.*, b.supplier_name, c.mname as manager_name
		from fm_stock_history as a
		left join fm_supplier as b on a.supplier_seq = b.supplier_seq
		left join fm_manager as c on a.manager_id = c.manager_id
		where a.stock_history_seq=?",$stock_history_seq);
		$data = $query->row_array();
		$data['mreason'] = $this->stockmodel->arr_reason[$data['reason']];
		
		$query = $this->db->query("select * from fm_stock_history_item where stock_code=?",$data['stock_code']);
		$list = $query->result_array();
		
		$list_sum = array();
		foreach($list as $row){
			$list_sum['supply_price'] += $row['supply_price'];
			$list_sum['ea'] += $row['ea'];
			$list_sum['total'] += $row['supply_price'] * $row['ea'];
		}
		
		$this->template->assign(array(
			'data'=>$data,
			'list'=>$list,
			'list_sum'=>$list_sum
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
}

/* End of file statistic.php */
/* Location: ./app/controllers/admin/statistic.php */