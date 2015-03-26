<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class export extends admin_base {
	public function __construct(){
		parent::__construct();
		$this->arr_status = config_load('export_status');
		$this->arr_step = config_load('step');
		$this->arr_payment = config_load('payment');
		$this->cfg_order = config_load('order');

		$auth = $this->authmodel->manager_limit_act('order_view');
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
		$query = "update fm_goods_export set important=? where export_seq=?";
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
			$_COOKIE['export_list_search'] = $cookie_str;
			setcookie('export_list_search',$cookie_str,time()+86400*30);
		}
		$callback = "parent.location.reload();parent.closeDialog('search_detail_dialog');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_default(){
		$arr = explode('&',$_COOKIE['export_list_search']);
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

		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->helper('shipping');

		//오픈마켓연동정보
		$this->load->model('openmarketmodel');
		if	($this->openmarketmodel->chk_linkage_service()){
			$linkage = $this->openmarketmodel->get_linkage_config();
			if($linkage){
				// 설정된 판매마켓 정보
				$linkage_mallnames = array();
				$linkage_mallnames_for_search = array();
				$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
				$linkage_malldata		= $this->openmarketmodel->sort_linkage_mall($linkage_malldata);

				foreach($linkage_malldata as $k => $data){
					if	($data['default_yn'] == 'Y'){
						$linkage_mallnames[$data['mall_code']]	= preg_replace("/\(.*\)/","",$data['mall_name']);
						if(count($linkage_mallnames_for_search)<10){
						$linkage_mallnames_for_search[]	= array(
							'mall_code' => $data['mall_code'],
							'mall_name' => preg_replace("/\(.*\)/","",$data['mall_name'])
						);
						}
					}
				}
				$this->template->assign('linkage_mallnames_for_search',$linkage_mallnames_for_search);
				$this->template->assign('linkage_mallnames',$linkage_mallnames);
			}
		}

		$arr_delivery = get_delivery_url();


		// 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다.
		if( count($_GET) == 0 ){
			if($_COOKIE['export_list_search']){
				$arr = explode('&',$_COOKIE['export_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);

					if( preg_match('/\[/',$arr2[0]) ){
						$key = explode('[',$arr2[0]);
						$_GET[$key[0]][ str_replace(']','',$key[1]) ] = $arr2[1];
					}else{
						if( $arr2[0]!='regist_date') $_GET[$arr2[0]] = $arr2[1];
					}
					if( $arr2[0]=='regist_date'){
						if($arr2[1] == 'today'){
							$_GET['regist_date'][0] = date('Y-m-d');
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3day'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-3 day"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '7day'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-7 day"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '1mon'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3mon'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-3 month"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == 'all'){
							$_GET['regist_date'][0] = '';
							$_GET['regist_date'][1] = '';
						}
						$_GET['regist_date_type'] = $arr2[1];
					}
				}
			}else{
				// 기본세팅이 없는경우 오늘의 입금확인 주문접수 주문을 검색조건으로 합니다.
				$_GET['date'] = 'export';
				$_GET['regist_date'][0] = date('Y-m-d');
				$_GET['regist_date'][1] = date('Y-m-d');
				$_GET['export_status'][45] = 1;
				$_GET['export_status'][55] = 1;
				$_GET['export_status'][65] = 1;
			}
		}

		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		$_GET['keyword'] = trim($_GET['keyword']);

		// 검색어
		if( $_GET['keyword'] ){

			$keyword_type = preg_replace("/[^a-z_.]/i","",trim($_GET['keyword_type']));
			$keyword = str_replace("'","\'",trim($_GET['keyword']));

			if($keyword_type){
				$where[] = "{$keyword_type} = '" . $keyword . "'";
			// 검색어가 주문번호 일 경우
			}/*else if( preg_match('/^([0-9]{19})$/',$keyword) ){
				$where[] = "exp.order_seq = '" . $keyword . "'";

			// 검색어가 출고번호 일 경우
			}*/else if(preg_match('/^(D[0-9]{10,11})$/',$keyword)){
				$where[] = "exp.export_code = '" . $keyword . "'";
			// 검색어가 반품번호 일 경우
			}else if(preg_match('/^(R[0-9]{11})$/',$keyword)){
				$where[] = "exp.order_seq = (SELECT order_seq FROM fm_order_return WHERE return_code = '" . $keyword . "')";
			// 검색어가 환불번호 일 경우
			}else if(preg_match('/^(C[0-9]{11})$/',$keyword)){
				$where[] = "exp.order_seq = (SELECT order_seq FROM fm_order_refund WHERE refund_code = '" . $keyword . "')";
			}else{

				$where[] = "
				(
					exp.order_seq = '" . $keyword . "' OR
					exp.export_code = '" . $_GET['keyword'] . "' OR
					mem.userid LIKE '%" . $_GET['keyword'] . "%' OR
					mem.user_name LIKE '%" . $_GET['keyword'] . "%' OR
					ord.order_user_name LIKE '%" . $_GET['keyword'] . "%' OR
					ord.order_email like '%" . $keyword . "%' OR
					ord.order_phone like '%" . $keyword . "%' OR
					ord.order_cellphone like '%" . $keyword . "%' OR
					ord.depositor LIKE '%" . $_GET['keyword'] . "%' OR
					exp.delivery_number LIKE '%" . $_GET['keyword'] . "%' OR
					exp.international_delivery_no LIKE '%" . $_GET['keyword'] . "%' OR
					shi.recipient_phone  LIKE '%" . $keyword . "%' OR
					shi.recipient_cellphone  LIKE '%" . $keyword . "%' OR
					shi.recipient_user_name  LIKE '%" . $keyword . "%' OR
					item.coupon_serial LIKE '%" . $keyword . "%' OR
					delivery_number LIKE '%" . $keyword . "%' OR
					international_delivery_no LIKE '%" . $keyword . "%' OR
					ifnull(ord.linkage_mall_order_id,'') LIKE '%" . $keyword . "%' OR
					EXISTS (
						SELECT
							item_seq
						FROM fm_order_item WHERE order_seq = exp.order_seq and (
							goods_name LIKE '%" . $keyword . "%' OR
							goods_code = '" . $keyword . "' OR
							goods_seq = '" . $keyword . "')
					)
				)
				";
			}

		}

		// 주문일
		if( $_GET['date']=='shipping' ){
			$date_field = "exp.shipping_date";
		}else if( $_GET['date']=='export' ){
			$date_field = "exp.export_date";
		}else if( $_GET['date']=='regist_date' ){
			$date_field = "exp.regist_date";
		}else if( $_GET['date']=='confirm_date' ){
			$date_field = "exp.confirm_date";
		}else if( $_GET['date']=='export' ){
			$date_field = "exp.export_date";
		}else{
			$date_field = "ord.regist_date";
		}
		if($_GET['regist_date'][0]){
			$where[] = $date_field." >= '".$_GET['regist_date'][0]." 00:00:00'";
		}
		if($_GET['regist_date'][1]){
			$where[] = $date_field." <= '".$_GET['regist_date'][1]." 24:00:00'";
		}

		// 출고준비중에 결제취소된 건 제외
		$where[] = "not(exp.status='45' and ord.step='85')";

		// 주문상태
		if( $_GET['export_status'] ){
			unset($arr);
			foreach($_GET['export_status'] as $key => $data){
				$arr[] = "exp.status = '".$key."'";
			}
			$where[] = "(".implode(' OR ',$arr).")";
		}

		// 출고방법
		if($_GET['search_shipping_method']){
			$r_export_method[] = "domestic_shipping_method in ('".implode("','",$_GET['search_shipping_method'])."')";
		}
		if($_GET['search_international']){
			$r_export_method[] = "exp.international = '".$_GET['search_international']."'";
		}
		if($r_export_method){
			$where[] = "(".implode(' OR ',$r_export_method).")";
		}

		// 출고 정보
		if($_GET['search_delivery_company_code'] && preg_match('/code/',$_GET['search_delivery_company_code'])){ // 택배사
			$where_edn[] = "delivery_company_code = '".$_GET['search_delivery_company_code']."'";
			if($_GET['search_delivery_number']){
				$where_edn[] = "delivery_number like '".$_GET['search_delivery_number']."%'";
			}
		}else if($_GET['search_delivery_company_code']){ //해외택배사
			$where_edn[] = "international_shipping_method = '".$_GET['search_delivery_company_code']."'";
			if($_GET['search_delivery_number']){
				$where_edn[] = "international_delivery_no like '".$_GET['search_delivery_number']."%'";
			}
		}else if($_GET['search_delivery_number']){
			$where_edn[] = "(delivery_number like '".$_GET['search_delivery_number']."%' OR international_delivery_no like '".$_GET['search_delivery_number']."%')";
		}
		if($where_edn){
			$where_ei[] = "(".implode(' AND ',$where_edn).")";
		}
		if($_GET['null_delivery_number']){
		 	$where_ei[] = "((exp.international='domestic' and shipping_method in ('delivery','postpaid') and (delivery_number is null OR delivery_number=''))
		 	OR (exp.international='international' and (international_delivery_no is null OR international_delivery_no='')))";
		}
		if($where_ei){
			$where[] = "(".implode(' OR ',$where_ei).")";
		}

		// 구매 확정
		if($_GET['buy_confirm']){
			foreach( $_GET['buy_confirm'] as $key => $val){
				$in_where_buy_confirm[] = "'".$key."'";
			}
			if($in_where_buy_confirm){
				$where[] = "exp.buy_confirm in (".implode(',',$in_where_buy_confirm).")";
			}
		}

		$delivery_company_array = get_shipping_company('domestic','delivery');
		$international_company_array = get_international_company();

		### 2014-05-29
		if($_GET['linkage_mall_code'] || $_GET['not_linkage_order'] || $_GET['etc_linkage_order']){
			$arr = array();

			if($_GET['not_linkage_order']){
				$arr[] = "(ord.linkage_mall_code is null or ord.linkage_mall_code = '')";
			}
			if($_GET['linkage_mall_code']){
				$arr[] = "ord.linkage_mall_code in ('".implode("','",$_GET['linkage_mall_code'])."')";
			}
			if($_GET['etc_linkage_order']){
				if(!$linkage_malldata){
					$this->load->model('openmarketmodel');
					$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
					$linkage_malldata		= $this->openmarketmodel->sort_linkage_mall($linkage_malldata);
				}
				$search_mall_code = array();
				foreach($linkage_malldata as $k => $data){
					if	($data['default_yn'] == 'Y'){
						$search_mall_code[] = $data['mall_code'];
					}
				}
				for($i=0;$i<count($search_mall_code);$i++){
					if($i<10) unset($search_mall_code[$i]);
				}
				$arr[] = "ord.linkage_mall_code in ('".implode("','",$search_mall_code)."')";
			}

			$where[] = "(".implode(' OR ',$arr).")";
		}

		$shopkey = get_shop_key();
		$query = "
		SELECT ord.*, ord.regist_date as order_date,exp.*,sum(opt.price*item.ea) opt_price,sum(sub.price*item.ea) sub_price,sum(item.ea) ea,
		(select sum(ea) from fm_order_item_option where order_seq = exp.order_seq and step >= '40' and step <= '75') opt_ea,
		(select sum(ea) from fm_order_item_suboption where order_seq = exp.order_seq and step >= '40' and step <= '75') sub_ea,
		(
			SELECT userid FROM fm_member WHERE member_seq=ord.member_seq
		) userid,
		(
			SELECT AES_DECRYPT(UNHEX(email), '{$shopkey}') as email FROM fm_member WHERE member_seq=ord.member_seq
		) mbinfo_email,
		(
			SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq
		) group_name,
		shi.recipient_user_name as shipping_recipient_user_name,
		mem.rute as mbinfo_rute,
		mem.user_name as mbinfo_user_name,
		bus.business_seq as mbinfo_business_seq,
		bus.bname as mbinfo_bname,
		ord.order_user_name
		FROM
		fm_goods_export exp
			LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq
			LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
			LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
			LEFT JOIN fm_order_shipping shi ON shi.shipping_seq=exp.shipping_seq
		,fm_goods_export_item item
			LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
			LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
		WHERE exp.export_code=item.export_code and exp.status!='' " . ($where ? " AND " . implode(' AND ',$where) : '') . "
		GROUP BY exp.export_seq
		ORDER BY exp.status asc,exp.export_seq DESC";
		$query = $this->db->query($query);		
		foreach($query->result_array() as $k => $data)
		{
			$no++;
			$data['price'] = (int) $data['opt_price'] + (int) $data['sub_price'];
			$data['mstatus'] = $this->arr_status[$data['status']];
			$status_cnt[$data['status']]++;
			$tot_price[$data['status']] += $data['price'];
			$data['tot_price'] = $tot_price;
			$tot[$data['status']][$data['important']] += $data['price'];
			$data['status_cnt'] = $status_cnt;
			$data['tot'][$data['important']] = $tot[$data['status']][$data['important']];
			$data['mpayment'] = $this->arr_payment[$data['payment']];
			$data['mstep'] = $this->arr_step[$data['step']];
			$data['delivery_company_array'] = $delivery_company_array;
			$data['international_company_array'] = $international_company_array;
			if($data['international'] == 'domestic'){
				if($data['domestic_shipping_method'] == 'delivery'||$data['domestic_shipping_method'] == 'postpaid'){
					$tmp = config_load('delivery_url',$data['delivery_company_code']);
					$data['mdelivery'] = $arr_delivery[$data['delivery_company_code']]['company'];
					$data['mdelivery_number'] = $data['delivery_number'];
					$data['tracking_url'] = $arr_delivery[$data['delivery_company_code']]['url'].$data['delivery_number'];
				}else{
					$data['mdelivery'] = get_domestic_method($data['domestic_shipping_method']);
				}
			}else{
				$data['mdelivery'] = get_international_method($data['international_shipping_method']);
				$data['mdelivery_number'] = $data['international_delivery_no'];
			}

			if($data['invoice_send_yn']=='y'){
				$status_invoice_cnt[$data['status']]++;
			}

			if($data['member_seq']){
				$data['member_type']	= $data['mbinfo_business_seq'] ? '기업' : '개인';
			}

			$data_export_items = $this->exportmodel->get_export_item($data['export_code']);

			if( $data_export_items[0]['opt_type'] == 'sub'){
				if($data_export_items[0]['option1']){
					$data['item_title'] .= $data_export_items[0]['title1'].':'.$data_export_items[0]['option1'];
				}
				if($data_export_items[0]['option2']){
					$data['item_title'] .= ' '. $data_export_items[0]['title2'].':'.$data_export_items[0]['option2'];
				}
				if($data_export_items[0]['option3']){
					$data['item_title'] .= ' '.$data_export_items[0]['title3'].':'.$data_export_items[0]['option3'];
				}
				if($data_export_items[0]['option4']){
					$data['item_title'] .= ' '.$data_export_items[0]['title4'].':'.$data_export_items[0]['option4'];
				}
				if($data_export_items[0]['option5']){
					$data['item_title'] .= ' '.$data_export_items[0]['title5'].':'.$data_export_items[0]['option5'];
				}

			}

			$data['goods_kind'] = $data_export_items[0]['goods_kind'];
			$data['opt_type'] = $data_export_items[0]['opt_type'];
			$data['goods_name'] = $data_export_items[0]['goods_name'];
			$data['goods_type'] = $data_export_items[0]['goods_type'];
			$data['item_count'] .= count($data_export_items);

			// 쿠폰상품일때 쿠폰 정보 추가
			if ($data['goods_kind'] == 'coupon'){
				$data['coupon_serial']	= $data_export_items[0]['coupon_serial'];
				$data['email']			= $data_export_items[0]['recipient_email'];
				$data['cellphone']		= $data_export_items[0]['recipient_cellphone'];
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

		$export_cfg = config_load('export');
		$this->template->assign($export_cfg);

		$this->load->model('invoiceapimodel');
		$invoice_vendor = $this->invoiceapimodel->get_usable_invoice_vendor();
		$this->template->assign(array('invoice_vendor'	=> $invoice_vendor));

		$this->template->assign(array(
			'record' => $record,
			'delivery_company_array' => $delivery_company_array,
			'international_company_array' => $international_company_array,
			'status_invoice_cnt' => $status_invoice_cnt,
		));

		$this->template->define(array('tpl' => $this->template_path()));
		$this->template->print_("tpl");
	}

	public function goods_export()
	{
		$this->load->model('exportmodel');
		$cfg_order = config_load('order');
		$export_code = $_GET['no'];
		$this->load->helper('shipping');
		$arr_delivery = get_delivery_url();
		$data_export 			= $this->exportmodel->get_export($export_code);
		if($data_export['international'] == 'domestic'){
			if($data_export['domestic_shipping_method'] == 'delivery'||$data_export['domestic_shipping_method'] == 'postpaid'){
				$data_export['mdelivery'] = $arr_delivery[$data_export['delivery_company_code']]['company'];
				$data_export['mdelivery_number'] = $data_export['delivery_number'];
				if($data_export['delivery_number']) $data_export['tracking_url'] = $arr_delivery[$data_export['delivery_company_code']]['url'].$data_export['delivery_number'];
			}
		}else{
			$data_export['mdelivery'] = $data_export['international_shipping_method'];
			$data_export['mdelivery_number'] = $data_export['international_delivery_no'];
			if($data_export['international_delivery_no']) $data_export['tracking_url'] = $arr_delivery[$data_export['international_shipping_method']]['url'].$data_export['international_delivery_no'];
		}
		$data_export['mstatus'] = $this->exportmodel->arr_step[$data_export['status']];
		$data_export_item 		= $this->exportmodel->get_export_item($export_code);

		$this->template->assign(array('cfg_order'	=> $cfg_order));
		$this->template->assign('data_export',$data_export);
		$this->template->assign('data_export_item',$data_export_item);
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function view()
	{
		$cfg_order = config_load('order');
		$cfg_reserve = ($this->reserves)?$this->reserves:config_load('reserve');
		$this->admin_menu();
		$this->tempate_modules();
		$export_code = $_GET['no'];
		$this->load->helper('shipping');
		$arr_delivery = get_delivery_url();
		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->model('buyconfirmmodel');
		$this->load->model('goodsmodel');
		$this->load->model('eventmodel');

		$this->load->helper('order');

		$data_export 			= $this->exportmodel->get_export($export_code);
		$data_buy_confirm		= $this->buyconfirmmodel->get_log_buy_confirm($data_export['export_seq']);
		$export_shipping		= $this->ordermodel->get_shipping($data_export['order_seq'],$data_export['shipping_seq']);
		$export_shipping		= $export_shipping[0];

		// 개인정보 조회 로그
		//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderprint' 'orderexcel', 'exportexcel'
		$this->load->model('logPersonalInformation');
		$this->logPersonalInformation->insert('export',$this->managerInfo['manager_seq'],$data_export['export_seq']);

		if($data_export['international'] == 'domestic'){
			if($data_export['domestic_shipping_method'] == 'delivery'||$data_export['domestic_shipping_method'] == 'postpaid'){
				$tmp = config_load('delivery_url',$data['delivery_company_code']);
				$data_export['mdelivery'] = $arr_delivery[$data_export['delivery_company_code']]['company'];
				$data_export['mdelivery_number'] = $data_export['delivery_number'];
				if($data_export['delivery_number']) {
					if($data_export['mdelivery']=='대신택배'){ // 대신택배일 경우 예외 2013-12-27 lwh
						$data_export['tracking_url'] = $arr_delivery[$data_export['delivery_company_code']]['url'] . substr($data_export['delivery_number'],0,4) . "&billno2=" . substr($data_export['delivery_number'],4,3) . "&billno3=" . substr($data_export['delivery_number'],7,strlen($data_export['delivery_number']));
					}else{
						$data_export['tracking_url'] = $arr_delivery[$data_export['delivery_company_code']]['url'].$data_export['delivery_number'];
					}
				}
			}
		}else{
			$data_export['mdelivery'] = $data_export['international_shipping_method'];
			$data_export['mdelivery_number'] = $data_export['international_delivery_no'];
			if($data_export['international_delivery_no']) $data_export['tracking_url'] = $arr_delivery[$data_export['international_shipping_method']]['url'].$data_export['international_delivery_no'];
		}

		$data_export['mstatus'] = $this->exportmodel->arr_step[$data_export['status']];
		$data_export_item 		= $this->exportmodel->get_export_item($export_code);

		foreach($data_export_item as $k => $data){

			if	($data['goods_kind'] == 'coupon')	$coupon_cnt++;

			if($data['event_seq']) {
				$events = $this->eventmodel->get_event($data['event_seq']);
				if($events['title']) $data['event_title'] = $events['title'];
				if($events['event_type']) $data['event_type'] = $events['event_type'];
			}

			$goods[$data['goods_seq']]++;
			unset($data['inputs']);
			if( $data['opt_type'] == 'opt' ){
				$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'],$data['option_seq']);

				$real_stock = (int) $this->goodsmodel -> get_goods_option_stock(
					$data['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5']
				);

				$rstock = $this->ordermodel -> get_option_reservation(
					$this->cfg_order['ableStockStep'],
					$data['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5']
				);
			}else{
				$real_stock = (int) $this->goodsmodel -> get_goods_suboption_stock(
					$data['goods_seq'],
					$data['title1'],
					$data['option1']
				);
				$rstock = $this->ordermodel -> get_suboption_reservation(
					$this->cfg_order['ableStockStep'],
					$data['goods_seq'],
					$data['title1'],
					$data['option1']
				);
			}

			//청약철회상품체크
			$ctgoods = $this->goodsmodel->get_goods($data['goods_seq']);
			$data['cancel_type'] = $ctgoods['cancel_type'];

			//$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
			if($data['opt_type']=='opt'){
				$data['step_complete'] = $this->ordermodel -> get_option_export_complete($data_export['order_seq'],$data_export['shipping_seq'],$data['item_seq'],$data['option_seq']);
				$data['step85'] = $this->refundmodel->get_refund_option_ea($data_export['shipping_seq'],$data['item_seq'],$data['option_seq']);
			}
			if($data['opt_type']=='sub'){
				$data['step_complete'] = $this->ordermodel -> get_suboption_export_complete($data_export['order_seq'],$data_export['shipping_seq'],$data['item_seq'],$data['option_seq']);
				$data['step85'] = $this->refundmodel->get_refund_suboption_ea($data_export['shipping_seq'],$data['item_seq'],$data['option_seq']);
			}

			$stock = (int) $real_stock - (int) $rstock;
			$data['real_stock'] = $real_stock;
			$data['stock'] = $stock;

			$data['out_supply_price'] = $data['supply_price']*$data['ea'];
			$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
			$data['out_price'] = $data['price']*$data['ea'];

			$data['out_member_sale']					= $data['member_sale']*$data['ea'];
			$data['out_coupon_sale']					= ($data['download_seq'])?$data['coupon_sale']:0;
			$data['out_fblike_sale']						= $data['fblike_sale'];//*$data['ea']
			$data['out_mobile_sale']						= $data['mobile_sale'];//*$data['ea']
			$data['out_promotion_code_sale']		= $data['promotion_code_sale'];

			$data['out_reserve'] = $data['reserve']*$data['ea'];
			$data['out_point'] = $data['point']*$data['ea'];

			$data_export_item[$k] = $data;

			$tot['ea'] += $data['ea'];
			$tot['opt_ea'] += $data['opt_ea'];
			$tot['shipping_ea'] += $data['shipping_ea'];
			$tot['supply_price'] += $data['out_supply_price'];
			$tot['consumer_price'] += $data['out_consumer_price'];
			$tot['price'] += $data['out_price'];

			$tot['member_sale'] += $data['out_member_sale'];
			$tot['coupon_sale'] += $data['out_coupon_sale'];
			$tot['fblike_sale'] += $data['out_fblike_sale'];
			$tot['mobile_sale'] += $data['out_mobile_sale'];
			$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];

			$tot['goods_shipping_cost'] += $data['goods_shipping_cost'];

			$tot['reserve'] += $data['out_reserve'];
			$tot['point'] += $data['out_point'];

			$tot['real_stock'] += $real_stock;
			$tot['stock'] += $stock;
		}
		$tot['goods_cnt'] = array_sum($goods);

		$shipping = use_shipping_method();
		if( $shipping ) foreach($shipping as $key => $data){
			if($data) $shipping_cnt[$key] = count($data);
		}
		$shipping_policy['policy'] 	= $shipping;
		foreach($shipping_policy['policy'][0] as $k => $method) $domestic_method[$method['code']] = $method['method'];

		$this->template->assign(array('shipping_policy'	=> $shipping_policy));
		$this->template->assign(array('coupon_cnt'	=> $coupon_cnt));
		$this->template->assign(array('cfg_order'	=> $cfg_order));
		$this->template->assign(array('cfg_reserve'	=> $cfg_reserve));
		$this->template->assign(array('data_buy_confirm' => $data_buy_confirm));
		$this->template->assign(array('export_shipping'	=> $export_shipping));
		$this->template->assign(
			array('data_export'=>$data_export,'data_export_item'=>$data_export_item,'tot'=>$tot)
		);

		if($_GET['mode'] == 'export_list'){
			$file_path = str_replace('view.html','view_list.html',$this->template_path());
			$this->template->define(array('tpl' => $file_path));
		}else{
			$this->template->define(array('tpl' => $this->template_path()));
		}

		$this->template->print_("tpl");
	}

	public function batch_status(){

		$this->load->model('ordermodel');
		$cfg_order = config_load('order');
		$where = "";

		if(!empty($_POST['code'])){
			$codes = "'" . implode("','",$_POST['code']) . "'";
			$where = "AND exp.export_code in(".$codes.")";
		}

		if(!empty($_POST['seq'])){
			$codes = "'" . implode("','",$_POST['seq']) . "'";
			$where = "AND exp.order_seq in(".$codes.")";
		}

		$query = "
		select
			exp.*,
			sum(item.ea) ea,
			group_concat(distinct oitem.goods_name SEPARATOR ', ') goods_name
		from
		fm_goods_export exp,
		fm_goods_export_item item,
		fm_order_item oitem
		where
		exp.export_code=item.export_code
		and item.item_seq = oitem.item_seq
		and oitem.goods_kind = 'goods'
		{$where}
		group by exp.export_code";
		$query = $this->db->query($query);
		foreach($query->result_array() as $data){

			list($data['order_shipping']) = $this->ordermodel->get_shipping($data['order_seq'],$data['shipping_seq']);

			$result[] = $data;
		}

		$this->load->helper('shipping');
		$shipping = use_shipping_method();
		if( $shipping ) foreach($shipping as $key => $data){
			if($data) $shipping_cnt[$key] = count($data);
		}
		$shipping_policy['policy'] 	= $shipping;
		foreach($shipping_policy['policy'][0] as $k => $method) $domestic_method[$method['code']] = $method['method'];

		$invoice_guide_path = dirname($this->template_path()).'/../order/_invoice_guide.html';
		$this->template->define(array('invoice_guide'=>$invoice_guide_path));

		$this->template->assign(array('cfg_order'	=> $cfg_order));
		$this->template->assign(array('shipping_policy'	=> $shipping_policy));
		$this->template->assign(array('domestic_method'	=> $domestic_method));
		$this->template->assign('data_export',$result);
		$this->template->define(array('tpl' => $this->template_path()));
		$this->template->print_("tpl");
	}


	public function export_print(){
		redirect(uri_string()."s?export={$_GET['export']}|&order={$_GET['ordno']}|");
	}

	public function export_prints(){
		$this->tempate_modules();

		//$export_code	= $_GET['export'];
		//$order_seq		= $_GET['ordno'];

		if($_POST) $_GET = $_POST;

		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');

		$export_cfg = config_load('export');
		$this->template->assign($export_cfg);

		$export = is_array($_GET['export']) ? $_GET['export'] : explode("|",$_GET['export']);
		$order 	= is_array($_GET['order']) ? $_GET['order'] : explode("|",$_GET['order']);
		$export = array_notnull($export);

		// 개인정보 조회 로그 모델 로드
		$this->load->model('logPersonalInformation');

		for($i=0;$i<count($export);$i++){
			$export_code	= $export[$i];
			if(!$export_code)continue;

			$data_export 			= $this->exportmodel->get_export($export_code);
			$data_export['mstatus'] = $this->exportmodel->arr_step[$data_export['status']];
			$data_export_item 		= $this->exportmodel->get_export_item($export_code);

			// 개인정보 조회 로그
			//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderprint' 'orderexcel', 'exportexcel'
			$this->logPersonalInformation->insert('exportprint',$this->managerInfo['manager_seq'],$data_export['export_seq']);

			$export_shipping		= $this->ordermodel->get_shipping($data_export['order_seq'],$data_export['shipping_seq']);
			$export_shipping		= $export_shipping[0];

			$order_seq		= $data_export['order_seq'];
			$items 				= $this->ordermodel->get_item($order_seq);
			$orders 			= $this->ordermodel->get_order($order_seq);
			$orders['mpayment'] = $this->arr_payment[$orders['payment']];

			unset($tot, $goods, $extot);
			foreach($data_export_item as $k => $data){
				$goods[$data['goods_seq']]++;

				unset($data['inputs']);
				if( $data['opt_type'] == 'opt' ){

					$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'],$data['option_seq']);

					$real_stock = (int) $this->goodsmodel -> get_goods_option_stock(
						$data['goods_seq'],
						$data['option1'],
						$data['option2'],
						$data['option3'],
						$data['option4'],
						$data['option5']
					);

					$rstock = $this->ordermodel -> get_option_reservation(
						$this->cfg_order['ableStockStep'],
						$data['goods_seq'],
						$data['option1'],
						$data['option2'],
						$data['option3'],
						$data['option4'],
						$data['option5']
					);

				}else{
					$real_stock = (int) $this->goodsmodel -> get_goods_suboption_stock(
						$data['goods_seq'],
						$data['title1'],
						$data['option1']
					);
					$rstock = $this->ordermodel -> get_suboption_reservation(
						$this->cfg_order['ableStockStep'],
						$data['goods_seq'],
						$data['title1'],
						$data['option1']
					);
				}

				//청약철회상품체크
				$ctgoods = $this->goodsmodel->get_goods($data['goods_seq']);
				$data['cancel_type'] = $ctgoods['cancel_type'];

				$stock = (int) $real_stock - (int) $rstock;
				$data['real_stock'] = $real_stock;
				$data['stock'] = $stock;

				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
				$data['out_price'] = $data['price']*$data['ea'];

				$data['out_member_sale']			= $data['member_sale']*$data['ea'];
				$data['out_coupon_sale']			= ($data['download_seq'])?$data['coupon_sale']:0;
				$data['out_fblike_sale']			= $data['fblike_sale'];//*$data['ea']
				$data['out_mobile_sale']			= $data['mobile_sale'];//*$data['ea']
				$data['out_referer_sale']			= $data['referer_sale'];//*$data['ea']
				$data['out_promotion_code_sale']	= $data['promotion_code_sale'];
				$data['out_reserve']				= $data['reserve']*$data['ea'];
				$data['out_point']					= $data['point']*$data['ea'];

				$extot['opt_ea']					+= $data['opt_ea'];
				$extot['ea']						+= $data['ea'];
				$extot['oprice']					+= $data['price'];
				$extot['price']						+= $data['out_price'];
				$extot['member_sale']				+= $data['out_member_sale'];
				$extot['coupon_sale']				+= $data['out_coupon_sale'];
				$extot['fblike_sale']				+= $data['out_fblike_sale'];
				$extot['mobile_sale']				+= $data['out_mobile_sale'];
				$extot['referer_sale']				+= $data['out_referer_sale'];
				$extot['promotion_code_sale']		+= $data['out_promotion_code_sale'];
				$extot['reserve']					+= $data['out_reserve'];
				$extot['point']						+= $data['out_point'];

				$data_export_item[$k] = $data;
			}

			foreach($items as $key=>$item){
				$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
				$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);
				if($options) foreach($options as $k => $data){
					$data['out_supply_price'] = $data['supply_price']*$data['ea'];
					$data['out_commission_price'] = $data['commission_price']*$data['ea'];
					$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
					$data['out_price'] = $data['price']*$data['ea'];

					//promotion sale
					$data['out_member_sale'] = $data['member_sale']*$data['ea'];
					$data['out_coupon_sale'] = ($data['download_seq'])?$data['coupon_sale']:0;
					$data['out_fblike_sale'] = $data['fblike_sale'];
					$data['out_mobile_sale'] = $data['mobile_sale'];
					$data['out_promotion_code_sale'] = $data['promotion_code_sale'];

					//use
					$data['out_reserve'] = $data['reserve']*$data['ea'];
					$data['out_point'] = $data['point']*$data['ea'];

					$tot['ea'] += $data['ea'];
					$tot['supply_price'] += $data['out_supply_price'];
					$tot['commission_price'] += $data['out_commission_price'];
					$tot['consumer_price'] += $data['out_consumer_price'];
					$tot['price'] += $data['out_price'];

					//promotion sale
					$tot['member_sale'] += $data['out_member_sale'];
					$tot['coupon_sale'] += $data['out_coupon_sale'];
					$tot['fblike_sale'] += $data['out_fblike_sale'];
					$tot['mobile_sale'] += $data['out_mobile_sale'];
					$tot['referer_sale'] += $data['out_referer_sale'];
					$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];

					//use sale
					$tot['reserve'] += $data['out_reserve'];
					$tot['point'] += $data['out_point'];
				}

				if($suboptions) foreach($suboptions as $k => $data){
					$data['out_supply_price'] = $data['supply_price']*$data['ea'];
					$data['out_commission_price'] = $data['commission_price']*$data['ea'];
					$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
					$data['out_price'] = $data['price']*$data['ea'];

					//promotion sale
					$data['out_member_sale'] = $data['member_sale']*$data['ea'];

					//member use
					$data['out_reserve'] = $data['reserve']*$data['ea'];
					$data['out_point'] = $data['point']*$data['ea'];

					$tot['ea'] += $data['ea'];
					$tot['supply_price'] 	+= $data['out_supply_price'];
					$tot['commission_price'] 	+= $data['out_commission_price'];
					$tot['consumer_price'] 	+= $data['out_consumer_price'];

					//promotion sale
					$tot['member_sale'] += $data['out_member_sale'];

					//member use
					$tot['reserve'] += $data['out_reserve'];
					$tot['point'] += $data['out_point'];

					$tot['price'] 			+= $data['out_price'];
				}
				$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
			}

			$data_arr['order']				= $orders;
			$data_arr['data_export_item']	= $data_export_item;
			$data_arr['items_tot']				= $tot;
			$data_arr['export_tot']				= $extot;
			$data_arr['data_export']		= $data_export;
			$data_arr['export_shipping']	= $export_shipping;
			$loop[] = $data_arr;
		}

		$this->template->assign(array('loop' => $loop));
		$this->file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 엑셀 다운로드 항목설정 */
	public function download_write(){

		$this->load->model('excelexportmodel');
		$itemList 	= $this->excelexportmodel->itemList;

		$this->template->assign('itemList',$itemList);
		$requireds 	= $this->excelexportmodel->requireds;
		$this->template->assign('requireds',$requireds);

		$data = get_data("fm_exceldownload",array("gb"=>'EXPORT'));
		$item = explode("|",$data[0]['item']);
		$this->template->assign('items',$item);

		if(!$data[0]['criteria']) $data[0]['criteria'] = "EXPORT";

		$this->template->assign($data[0]);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 운송장 출력
	public function invoice_prints(){
		$this->tempate_modules();

		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->model('invoiceapimodel');

		if($_POST) $_GET = $_POST;

		$export = is_array($_GET['export']) ? $_GET['export'] : explode("|",$_GET['export']);
		$order 	= is_array($_GET['order']) ? $_GET['order'] : explode("|",$_GET['order']);

		$export = array_notnull($export);
		$order = array_notnull($order);

		$arr_export_code	= array();
		$arr_order_seq		= array();

		foreach($export as $v)	if($v) $arr_export_code[]	= $v;

		$loop = array();

		$invoice_vendor = null;

		// 개인정보 조회 로그 모델 로드
		$this->load->model('logPersonalInformation');

		foreach($arr_export_code as $export_code){
			$data_export 			= $this->exportmodel->get_export($export_code);

			// 개인정보 조회 로그
			//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderprint' 'orderexcel', 'exportexcel'
			$this->logPersonalInformation->insert('invoiceprint',$this->managerInfo['manager_seq'],$data_export['export_seq']);

			if($data_export['invoice_send_yn']!='y') continue;
			if(!preg_match("/^auto_/",$data_export['delivery_company_code'])) continue;

			if($invoice_vendor && $invoice_vendor!=$data_export['delivery_company_code']){
				$msg = "서로 다른 택배사의 운송장을 동시에 출력할 수 없습니다!";
				pageClose($msg);
				exit;
			}else{
				$invoice_vendor = $data_export['delivery_company_code'];
			}

			$data_export_item 		= $this->exportmodel->get_export_item($export_code);
			$orders 				= $this->ordermodel->get_order($data_export['order_seq']);
			$export_shipping		= $this->ordermodel->get_shipping($data_export['order_seq'],$data_export['shipping_seq']);

			$data_arr['data_export']		= $data_export;
			$data_arr['data_export_item']	= $data_export_item;
			$data_arr['export_shipping']	= $export_shipping[0];
			$data_arr['order']				= $orders;

			$loop[] = $data_arr;
		}



		if(!$loop){
			$msg = "출력할 운송장이 없습니다.";
			pageClose($msg);
			exit;
		}

		$invoice_vendor = preg_replace("/^auto_/","",$invoice_vendor);

		if(!$this->invoiceapimodel->config_invoice[$invoice_vendor]['use']){
			$company = $this->invoiceapimodel->invoice_vendor_cfg[$invoice_vendor]['company'];
			$msg = "설정 > 택배/배송비 > 택배업무자동화서비스({$company}) 세팅을 해주세요.";
			pageClose($msg);
			exit;
		}

		$method = "_invoice_prints_".$invoice_vendor;

		$this->$method($loop);
	}

	// 현대택배 운송장 출력
	public function _invoice_prints_hlc($loop){
		$vendor = 'hlc';

		switch($this->invoiceapimodel->config_invoice['hlc']['print_type']){
			case "label_a":
				$invoiceWidth=339;
				$invoiceHeight=670;
			case "label_b":
				$invoiceWidth=339;
				$invoiceHeight=376;
			break;
			case "a4":
				$invoiceWidth=760;
				$invoiceHeight=339;
			break;
		}
/*
		$this->template->assign(array(
			'invoiceWidth' => $invoiceWidth,
			'invoiceHeight' => $invoiceHeight
		));
*/

		$result = $this->invoiceapimodel->hlc_invoice_print($loop);

		if($_POST['gap']){
			for($i=0;$i<$_POST['gap'];$i++){
				$result['list'] = array_merge(array(null),$result['list']);
			}
		}

		$file_path = str_replace("invoice_prints","invoice_prints_".$vendor,$this->template_path());
		$this->template->assign(array('loop'=>$loop));
		$this->template->assign($result);
		$this->template->assign(array('print_type'=>$this->invoiceapimodel->config_invoice[$vendor]['print_type']));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	// 쿠폰 사용 확인 팝업
	public function coupon_use(){
		if	($_POST['order_seq']){
			$this->load->model("ordermodel");
			$this->load->model("exportmodel");
			$this->load->model("returnmodel");
			$refund_able_ea=0;
			$result	= $this->exportmodel->get_coupon_export($_POST['order_seq']);
			if	($result){
				foreach($result as $key => $data){
					if	($data['suboption_seq']) {
						$data['suboption']	= $this->ordermodel->get_order_item_suboption($data['suboption_seq']);
					}
					else{
						$data['option'] = $this->ordermodel->get_order_item_option($data['option_seq']);

						$return_item = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['option_seq']);
						$refund_able_ea += (int) $data['ea'] - (int) $return_item['ea'];
					}
					$export[]	= $data;
				}
			}
			$this->template->assign(array('refund_able_ea'	=> $refund_able_ea));

			$this->template->assign(array('order_seq'=>$_POST['order_seq']));
			$this->template->assign(array('export'=>$export));

			$smsinfo	= get_sms_remind_count();
			$this->template->assign(array('smsinfo'	=> $smsinfo));

			$file_path = $this->template_path();
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		}
	}

	// 쿠폰 번호 인증
	public function get_coupon_info(){

		$this->load->model("exportmodel");
		$result	= $this->exportmodel->chk_coupon($_GET);

		echo json_encode($result);
	}

	// 쿠폰 사용내역
	public function coupon_use_list(){
		$this->load->model("exportmodel");
		$export		= $this->exportmodel->get_coupon_info($_GET);
		$history	= $this->exportmodel->get_coupon_use_history($export['coupon_serial']);
		$export['history']	= $history;
		$export['coupon_value_unit']	= '회';
		if	($export['coupon_value_type'] == 'price')
			$export['coupon_value_unit']	= '원';

		$this->template->assign(array('export'=>$export));

		$file_path = $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 쿠폰 발송내역
	public function coupon_send_list(){
		$this->load->model("exportmodel");
		$param['send_kind']		= (trim($_GET['type']))			? trim($_GET['type'])			: '';
		$param['order_seq']		= (trim($_GET['order_seq']))	? trim($_GET['order_seq'])		: '';
		$param['export_code']	= (trim($_GET['export_code']))	? trim($_GET['export_code'])	: '';
		$history	= $this->exportmodel->get_coupon_export_send_log($param);
		if	($history){
			$sms_key = $mail_key = 0;
			foreach($history as $k => $data){
				$tmp_param['export_code']	= $data['export_code'];
				$export						= $this->exportmodel->get_coupon_info($tmp_param);
				unset($tmp_param);
				if	($data['send_kind'] == 'sms'){
					$history_tmp[$sms_key]['regist_date']	= $data['regist_date'];
					$history_tmp[$sms_key]['sms']			= $data['send_val'];
					$history_tmp[$sms_key]['sms_status']	= $data['status'];
					$history_tmp[$sms_key]['export']		= $export;
					$sms_key++;
				}else{
					$history_tmp[$mail_key]['regist_date']	= $data['regist_date'];
					$history_tmp[$mail_key]['email']		= $data['send_val'];
					$history_tmp[$mail_key]['email_status']	= $data['status'];
					$history_tmp[$mail_key]['export']		= $export;
					$mail_key++;
				}
				unset($export);
			}
			unset($history);
			$history	= $history_tmp;
		}

		$export['send_type']	= $param['send_kind'];
		$export['history']		= $history;
		$this->template->assign(array('export'=>$export));
		$file_path = $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function export_barcode_image(){
		$export_code = $_GET['export_code'];

		$this->load->library('Barcode39');
		$bc = new Barcode39("A{$export_code}A");
		$bc->barcode_text = true;
		$bc->barcode_bar_thick = 3;
		$bc->barcode_bar_thin = 1;
		$bc->barcode_height = 45;
		$bc->barcode_padding = 0;
		$bc->draw();

	}

	public function export_goods_barcode_image(){
		$goods_code = $_GET['goods_code'];

		$this->load->library('Barcode39');
		$bc = new Barcode39("{$order_seq}");
		$bc->barcode_text = true;
		$bc->barcode_bar_thick = 3;
		$bc->barcode_bar_thin = 1;
		$bc->barcode_height = 45;
		$bc->barcode_padding = 0;
		$bc->draw();

	}

}

/* End of file export.php */
/* Location: ./app/controllers/admin/export.php */