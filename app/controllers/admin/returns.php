<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class returns extends admin_base {

	public function __construct() {
		parent::__construct();
		$auth = $this->authmodel->manager_limit_act('refund_view');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
	}

	public function index()
	{
		redirect("/admin/order/catalog");
	}

	public function important()
	{
		$val = $_GET['val'];
		$no = str_replace('important_','',$_GET['no']);
		$query = "update fm_order_return set important=? where return_seq=?";
		$this->db->query($query,array($val,$no));
	}

	public function set_search_default(){
		foreach($_POST as $key => $data){
			if( is_array($data) ){
				foreach($data as $key2 => $data2){
					if($data2) $cookie_arr[] = $key."[".$key2."]"."=".$data2;
				}
			}else if($data){
				$cookie_arr[] = $key."=".$data;
			}
		}
		if($cookie_arr){
			$cookie_str = implode('&',$cookie_arr);
			$_COOKIE['return_list_search'] = $cookie_str;
			setcookie('return_list_search',$cookie_str,time()+86400*30);
		}
		$callback = "parent.closeDialog('search_detail_dialog');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_default(){
		$arr = explode('&',$_COOKIE['return_list_search']);
		foreach($arr as $data){
			$arr2 = explode("=",$data);
			$result[] = $arr2;
		}
		echo json_encode($result);
	}

	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();

		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');

		if( count($_GET) == 0 ){
			$_GET['sdate'] = date('Y-m-d');
			$_GET['edate'] = date('Y-m-d');
			$_GET['return_status'] = array('request','ing');
		}

		$where = array();

		// 검색어
		if( $_GET['keyword'] ){

			$keyword_type = preg_replace("/[^a-z_.]/i","",trim($_GET['keyword_type']));
			$keyword = str_replace("'","\'",trim($_GET['keyword']));

			if($keyword_type){
				$where[] = "{$keyword_type} = '" . $keyword . "'";
			// 검색어가 주문번호 일 경우
			}else if( preg_match('/^([0-9]{19})$/',$keyword) ){
				$where[] = "ref.order_seq = '" . $keyword . "'";
				
			// 검색어가 출고번호 일 경우
			}else if(preg_match('/^([D0-9]{9,11})$/',$keyword)){
				$where[] = "ref.order_seq = (SELECT order_seq FROM fm_goods_export WHERE export_code = '" . $keyword . "')";
			// 검색어가 반품번호 일 경우
			}else if(preg_match('/^([R0-9]{9,11})$/',$keyword)){
				$where[] = "ref.return_code = '" . $keyword . "'";
			// 검색어가 환불번호 일 경우
			}else if(preg_match('/^([C0-9]{9,11})$/',$keyword)){
				$where[] = "ref.order_seq = (SELECT order_seq FROM fm_order_refund WHERE refund_code = '" . $keyword . "')";
			}else{
				
				$where[] = "
				(										
					mem.user_name like '%" . $keyword . "%' OR
					bus.bname like '%" . $keyword . "%' OR
					ord.order_user_name  like '%" . $keyword . "%' OR
					ord.depositor like '%" . $keyword . "%' OR
					ord.order_email like '%" . $keyword . "%' OR
					ord.order_phone like '%" . $keyword . "%' OR
					ord.order_cellphone like '%" . $keyword . "%' OR										
					mem.userid like '%" . $keyword . "%' OR
					EXISTS (
						SELECT shipping_seq FROM fm_order_shipping WHERE order_seq = ord.order_seq and (
							recipient_phone LIKE '%" . $keyword . "%' OR 
							recipient_cellphone LIKE '%" . $keyword . "%' OR
							recipient_user_name LIKE '%" . $keyword . "%')
					) OR				
					EXISTS (
						SELECT 
							item_seq
						FROM fm_order_item WHERE order_seq = ord.order_seq and goods_name LIKE '%" . $keyword . "%'
					)
				)
				";				
			}

		}

		// 주문일
		$date_field = $_GET['date_field'] ? $_GET['date_field'] : 'ref.regist_date';
		if($_GET['sdate']){
			$where[] = $date_field." >= '".$_GET['sdate']." 00:00:00'";
		}
		if($_GET['edate']){
			$where[] = $date_field." <= '".$_GET['edate']." 24:00:00'";
		}

		// 주문상태
		if( $_GET['return_status'] ){
			$arr = array();
			foreach($_GET['return_status'] as $key => $data){
				$arr[] = "ref.status = '".$data."'";
			}
			$where[] = "(".implode(' OR ',$arr).")";
		}

		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";

		$query = "SELECT ord.*,ref.*,
		ord.payment,
		sum(item.return_ea) as return_ea,
		(
			SELECT userid FROM fm_member WHERE member_seq=ord.member_seq
		) userid,
		(
			SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq
		) group_name,
		(
			SELECT sum(ea) FROM fm_order_item_option WHERE order_seq=ord.order_seq
		) option_ea,
		(
			SELECT sum(ea) FROM fm_order_item_suboption WHERE order_seq=ord.order_seq
		) suboption_ea,
		sum(item.ea) as return_ea_sum,
		IF(reason_code>100 and reason_code<200,sum(item.ea),0) user_reason_cnt,
		IF(reason_code>200 and reason_code<300,sum(item.ea),0) shop_reason_cnt,
		IF(reason_code>300,sum(item.ea),0) goods_reason_cnt,
		(SELECT status FROM fm_order_refund WHERE refund_code=ref.refund_code) refund_status,
		(SELECT mname FROM fm_manager WHERE manager_seq = ref.manager_seq) mname,
		mem.rute as mbinfo_rute,
		mem.user_name as mbinfo_user_name,
		bus.business_seq as mbinfo_business_seq,
		bus.bname as mbinfo_bname
		FROM
			fm_order_return as ref
			LEFT JOIN fm_order as ord on ref.order_seq = ord.order_seq
			LEFT JOIN fm_order_return_item as item on ref.return_code=item.return_code
			LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
			LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
		{$sqlWhereClause}
		GROUP BY ref.return_code
		ORDER BY ref.status asc, ref.return_seq DESC";
		$query = $this->db->query($query);
		foreach($query->result_array() as $k => $data)
		{
			$no++;
			$data['price'] = (int) $data['opt_price'] + (int) $data['sub_price'];
			$data['mstatus'] = $this->returnmodel->arr_return_status[$data['status']];
			$data['mrefund_status'] = $this->refundmodel->arr_refund_status[$data['refund_status']];
			$data['mpayment'] = $this->arr_payment[$data['payment']];

			$tot[$data['status']]['order_ea'] += $data['option_ea']+$data['suboption_ea'];
			$tot[$data['status']]['user_reason_cnt'] += $data['user_reason_cnt'];
			$tot[$data['status']]['shop_reason_cnt'] += $data['shop_reason_cnt'];
			$tot[$data['status']]['goods_reason_cnt'] += $data['goods_reason_cnt'];
			$tot[$data['status']][$data['return_type']] += $data['return_ea_sum'];
			$tot[$data['status']]['return_ea'] += $data['return_ea'];

			$status_cnt[$data['status']]++;

			$tot[$data['status']][$data['important']] += $data['price'];
			$data['status_cnt'] = $status_cnt;
			$data['tot'][$data['important']] = $tot[$data['status']][$data['important']];

			if($data['member_seq']){
				$data['member_type']	= $data['mbinfo_business_seq'] ? '기업' : '개인';
			}

			$record[$k] = $data;
			if($status_cnt[$data['status']] == 1)
			{
				$record[$k]['start'] = true;
				$ek = $k-1;
				if($ek >= 0 ){
					$record[$ek]['end'] = true;
				}
			}
		}

		if($record)
		{
			$record[$k]['end'] = true;
			foreach($record as $k => $data){
				$record[$k]['no'] = $no;
				$no--;
			}
		}

		// 현재의 처리 프로세스
		$orders = config_load('order');
		$this->template->assign($orders);
		$this->template->define(array('order_process'=>$this->skin."/setting/_order_process.html"));

		$this->template->define(array('tpl' => $this->template_path()));
		$this->template->assign(array('record' => $record));
		$this->template->assign(array('tot' => $tot));
		$this->template->assign(array('arr_return_status' => $this->returnmodel->arr_return_status));
		$this->template->print_("tpl");
	}

	public function view()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$return_code = $_GET['no'];

		// 사유코드
		$reasons = code_load('return_reason');
		$qry = "select * from fm_return_reason where return_type='coupon' order by idx asc";
		$query = $this->db->query($qry);
		$reasoncouponLoop = $query -> result_array();
		$qry = "select * from fm_return_reason where return_type!='coupon' order by idx asc";
		$query = $this->db->query($qry);
		$reasonLoop = $query -> result_array();

		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->helper('order');

		$data_return 		= $this->returnmodel->get_return($return_code);
		$data_return_item 	= $this->returnmodel->get_return_item($return_code);
		$data_order			= $this->ordermodel->get_order($data_return['order_seq']);
		$process_log 		= $this->ordermodel->get_log($data_return['order_seq'],'process',array('return_code'=>$return_code));

		// 개인정보 조회 로그			
		//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderexcel', 'exportexcel'
		$this->load->model('logPersonalInformation');
		$this->logPersonalInformation->insert('return',$this->managerInfo['manager_seq'],$data_return['return_seq']);

		$tmp = $this->refundmodel->get_refund($data_return['refund_code']);
		$data_return['mrefund_status']	= $this->refundmodel->arr_refund_status[$tmp['status']];
		$data_return['mstatus'] 		= $this->returnmodel->arr_return_status[$tmp['status']];

		if( $data_return['phone'] )$data_return['phone'] = explode('-',$data_return['phone']);
		if( $data_return['cellphone'] )$data_return['cellphone'] = explode('-',$data_return['cellphone']);
		if( $data_return['sender_zipcode'] )$data_return['sender_zipcode'] = explode('-',$data_return['sender_zipcode']);
		
		foreach($data_return_item as $key => $item){
			$goods_cnt[$item['goods_seq']]++;
			$tot['ea']  		+= $item['ea'];
			$tot['return_ea']	+= $item['return_ea'];

			if( $item['reason_code'] > 100 && $item['reason_code'] < 200 ) $tot['user_reason_cnt'] += $item['ea'];
			if( $item['reason_code'] > 200 && $item['reason_code'] < 300 ) $tot['shop_reason_cnt'] += $item['ea'];
			if( $item['reason_code'] > 300 ) $tot['goods_reason_cnt'] += $item['ea'];

			if	($item['goods_kind'] == 'coupon'){ 
				$data_return_item[$key]['couponinfo'] = get_goods_coupon_view($item['export_code']);
			}

			//청약철회상품체크
			$ctgoods = $this->goodsmodel->get_goods($item['goods_seq']);
			$data_return_item[$key]['cancel_type'] = $ctgoods['cancel_type'];

			$data_return_item[$key]['reasons'] = $reasons;
			$data_return_item[$key]['reasonLoop'] = ($item['goods_kind'] == 'coupon' )?$reasoncouponLoop:$reasonLoop;

			$data_return_item[$key]['refunditem'] = $this->refundmodel->get_refund_item_data($data_return['refund_code'],$item['item_seq'], $item['option_seq']);
			$data_return_item[$key]['inputs'] = $this->ordermodel->get_input_for_option($item['item_seq'], $item['option_seq']);
		}
		if($goods_ea) $tot['goods_cnt'] = array_sum($goods_cnt);
		$data_return['mstatus'] = $this->returnmodel->arr_return_status[$data_return['status']];
		$data_return['mreturn_type'] = $this->returnmodel->arr_return_type[$data_return['return_type']];

		$this->template->assign(
			array(
				'process_log'=>$process_log,
				'data_return'=>$data_return,
				'data_return_item'=>$data_return_item,
				'tot'=>$tot,
				'data_order'=>$data_order
			)
		);

		$this->template->define(array('tpl' => $this->template_path()));
		$this->template->print_("tpl");
	}

}

/* End of file return.php */
/* Location: ./app/controllers/admin/return.php */