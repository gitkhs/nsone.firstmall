<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class refund extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->helper('order');
	}

	public function index()
	{
		redirect("/admin/order/catalog");
	}

	public function important()
	{
		$val = $_GET['val'];
		$no = str_replace('important_','',$_GET['no']);
		$query = "update fm_order_refund set important=? where refund_seq=?";
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
			$_COOKIE['refund_list_search'] = $cookie_str;
			setcookie('refund_list_search',$cookie_str,time()+86400*30);
		}
		$callback = "parent.closeDialog('search_detail_dialog');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_default(){
		$arr = explode('&',$_COOKIE['refund_list_search']);
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

		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');

		if( count($_GET) == 0 ){
			$_GET['sdate'] = date('Y-m-d');
			$_GET['edate'] = date('Y-m-d');
			$_GET['refund_status'] = array('request','ing');
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
				$where[] = "ref.order_seq = (SELECT order_seq FROM fm_order_return WHERE return_code = '" . $keyword . "')";
			// 검색어가 환불번호 일 경우
			}else if(preg_match('/^([C0-9]{9,11})$/',$keyword)){
				$where[] = "ref.refund_code = '" . $keyword . "'";
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
		if( $_GET['refund_status'] ){
			$arr = array();
			foreach($_GET['refund_status'] as $key => $data){
				$arr[] = "ref.status = '".$data."'";
			}
			$where[] = "(".implode(' OR ',$arr).")";
		}


		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";

		$query = "SELECT ord.*,ref.*,
		ord.payment,
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
		(SELECT status FROM fm_order_return WHERE refund_code=ref.refund_code) return_status,
		sum(item.ea) as refund_ea_sum,
		mem.rute as mbinfo_rute,
		mem.user_name as mbinfo_user_name,
		bus.business_seq as mbinfo_business_seq,
		bus.bname as mbinfo_bname
		FROM
			fm_order_refund as ref
			left join fm_order as ord on ref.order_seq = ord.order_seq
			left join fm_order_refund_item as item on ref.refund_code=item.refund_code
			left join fm_member mem ON mem.member_seq=ord.member_seq
			left join fm_member_business bus ON bus.member_seq=mem.member_seq
		{$sqlWhereClause}
		GROUP BY ref.refund_code
		ORDER BY ref.status asc, ref.refund_seq DESC";
		$query = $this->db->query($query);
		foreach($query->result_array() as $k => $data)
		{
			$no++;

			$data['mstatus'] = $this->refundmodel->arr_refund_status[$data['status']];
			$data['returns_status'] = $this->returnmodel->arr_return_status[$data['return_status']];
			$data['mpayment'] = $this->arr_payment[$data['payment']];

			$status_cnt[$data['status']]++;
			$tot_price[$data['status']]+=$data['refund_price'];

			$tot[$data['status']][$data['important']] += $data['price'];
			$data['status_cnt'] = $status_cnt;
			$data['tot_price'] = $tot_price;
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
		$this->template->assign(array('arr_refund_status' => $this->refundmodel->arr_refund_status));
		$this->template->print_("tpl");
	}

	public function view()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$refund_code = $_GET['no'];

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

		$this->load->helper('text');
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->model('returnmodel');
		$this->load->model('membermodel');

		$cfg_order = config_load('order');

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		if(!$reserves['cash_use']) $reserves['cash_use'] = "N";
		$this->template->assign($reserves);

		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');

		$data_refund 		= $this->refundmodel->get_refund($refund_code);
		$data_refund_item 	= $this->refundmodel->get_refund_item($refund_code);
		$data_order			= $this->ordermodel->get_order($data_refund['order_seq']);
		$data_order_item	= $this->ordermodel->get_item($data_refund['order_seq']);
		$process_log 		= $this->ordermodel->get_log($data_refund['order_seq'],'process',array('refund_code'=>$refund_code));
		$data_member		= $this->membermodel->get_member_data($data_refund['member_seq']);
		$data_shippings		= $this->ordermodel->get_shipping($data_refund['order_seq']);
		foreach($data_shippings as $k1 => $order_shipping){
			$shipping_items	= $order_shipping['shipping_items'];
			if	($shipping_items)foreach($shipping_items as $itemShip){
				$itemShipping[$itemShip['item_seq']]	= $itemShip;
			}

			// 기본배송 지역별 추가배송비
			$itemShipping['area_add_delivery_cost']	+= $order_shipping['area_add_delivery_cost'];
		}
		// 기본배송비
		$itemShipping['basic_cost']			+= $data_shippings[0]['shipping_cost'] - $itemShipping['area_add_delivery_cost'];
		$itemShipping['shop_shipping_cost']	+= $data_shippings[0]['shipping_cost'];

		// 개인정보 조회 로그			
		//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderexcel', 'exportexcel'
		$this->load->model('logPersonalInformation');
		$this->logPersonalInformation->insert('refund',$this->managerInfo['manager_seq'],$data_refund['refund_seq']);

		$this->load->model('couponmodel');
		$this->load->model('promotionmodel');
		//복원된 배송비쿠폰 여부
		if($data_order['download_seq']){
			$data_order['restore_used_coupon_refund'] = $this->couponmodel->restore_used_coupon_refund($data_order['download_seq']);
		}
		//복원된 배송비프로모션코드 여부
		if($data_order['shipping_promotion_code_seq']){
			$data_order['restore_used_promotioncode_refund'] = $this->promotionmodel->restore_used_promotioncode_refund($data_order['shipping_promotion_code_seq']);
		}

		/* 반품에 의한 환불일경우 주문시 지급 적립금합계 표시 */
		if($data_refund['refund_type']=='return'/* && !$cfg_order['buy_confirm_use']*/)
		{
			$optquery = "select sum(reserve*step75) as reserve_sum, sum(point*step75) as point_sum from fm_order_item_option where order_seq=?";
			$optquery = $this->db->query($optquery,$data_refund['order_seq']);
			$optres = $optquery->row_array();

			$suboptquery = "select sum(reserve*step75) as reserve_sum, sum(point*step75) as point_sum from fm_order_item_suboption where order_seq=?";
			$suboptquery = $this->db->query($suboptquery,$data_refund['order_seq']);
			$suboptres = $suboptquery->row_array();

			$tot['reserve_sum'] = $optres['reserve_sum']+$suboptres['reserve_sum'];
			$tot['point_sum'] = $optres['point_sum']+$suboptres['point_sum'];
		}

		$query = "SELECT status FROM fm_order_return WHERE refund_code=?";
		$query = $this->db->query($query,$data_refund['refund_code']);
		$res = $query->row_array();
		$data_refund['returns_status'] = $this->returnmodel->arr_return_status[$res['status']];


		$data_refund['mstatus'] = $this->refundmodel->arr_refund_status[$data_refund['status']];
		$data_refund['mrefund_type'] = $this->refundmodel->arr_refund_type[$data_refund['refund_type']];
		$data_refund['mcancel_type'] = $this->refundmodel->arr_cancel_type[$data_refund['cancel_type']];

		// 기본 적립금 유효기간 계산
		if(!$data_refund['refund_emoney_limit_date']){
			$reserve_str_ts = '';
			$reserve_limit_date = '';
			$cfg_reserves = config_load('reserve');
			if( $cfg_reserves['reserve_select'] == 'direct' ){
				$reserve_str_ts = "+".$cfg_reserves['reserve_direct']." month";
			}
			if( $cfg_reserves['reserve_select'] == 'year' ){
				$reserve_str_ts = "+".$cfg_reserves['reserve_year']." year";
			}
			if($reserve_str_ts){
				$reserve_limit_date = date('Y-m-d',strtotime($reserve_str_ts));
			}
			$data_refund['refund_emoney_limit_date'] = $reserve_limit_date;
		}

		$data_order['mpayment'] = $this->arr_payment[$data_order['payment']];

		if($data_order['international']=='international'){
			$data_order['real_shipping_cost'] = $data_order['international_cost'];
		}else{
			$data_order['real_shipping_cost'] = $data_order['shipping_cost'];
		}

		$goods_exist = 0;
		$refund_items = array();
		foreach($data_refund_item as $k => $data){
			$tot['ea'] += $data['ea'];

			//쇼셜쿠폰상품
			if ( $data['goods_kind'] == 'coupon' ) {//

				$data_return = $this->returnmodel->get_return_refund_code($refund_code);
				$data_return_item 	= $this->returnmodel->get_return_item($data_return['return_code']);
				$data_refund_item[$k]['couponinfo'] = get_goods_coupon_view($data_return_item[0]['export_code']);
				$data_refund_item[$k]['coupon_use_return'] = $data_refund_item[$k]['couponinfo']['coupon_use_return'];

				//debug_var($data['coupon_refund_type']);
				//debug_var($data['coupon_remain_price']);
				//debug_var($data['coupon_refund_emoney']);
				if ( $data['coupon_refund_type'] == 'emoney' ) {//유효기간지나면
					$tot['coupon_valid_over']++;
					$tot['price'] += $data['coupon_refund_emoney'];//적립금으로 추가
					$data_order['emoney'] += $data['coupon_refund_emoney'];//적립금으로 추가
				}else{
					$tot['price'] += $data['coupon_remain_price'];
				}
				
				if ( !in_array($data['item_seq'],$itemCoupontot) ) {
					$itemCoupontot[] = $data['item_seq']; 

					//promotion sale
					$tot['member_sale'] += $data['member_sale']*$data['ea'];
					$tot['coupon_sale'] += $data['coupon_sale'];
					$tot['fblike_sale'] += $data['fblike_sale'];
					$tot['mobile_sale'] += $data['mobile_sale'];
					$tot['referer_sale'] += $data['referer_sale'];
					$tot['promotion_code_sale'] += $data['promotion_code_sale'];

					if($data_refund['refund_type']=='return'/* && !$cfg_order['buy_confirm_use']*/){
						$tot['return_reserve'] += $data['reserve']*$data['ea'];
						$tot['return_point'] += $data['point']*$data['ea'];
					}
				}

			}else{
				$tot['price'] += $data['price']*$data['ea'];
				
				//promotion sale
				$tot['member_sale'] += $data['member_sale']*$data['ea'];
				$tot['coupon_sale'] += $data['coupon_sale'];
				$tot['fblike_sale'] += $data['fblike_sale'];
				$tot['mobile_sale'] += $data['mobile_sale'];
				$tot['referer_sale'] += $data['referer_sale'];
				$tot['promotion_code_sale'] += $data['promotion_code_sale'];
	
				if($data_refund['refund_type']=='return'/* && !$cfg_order['buy_confirm_use']*/){
					$tot['return_reserve'] += $data['reserve']*$data['ea'];
					$tot['return_point'] += $data['point']*$data['ea'];
				}
			}

			//복원된 쿠폰 여부
			if($data['download_seq']){
				$data['restore_used_coupon_refund'] = $this->couponmodel->restore_used_coupon_refund($data['download_seq']);
			}

			//복원된 프로모션코드 여부
			if($data['promotion_code_seq']){
				$data['restore_used_promotioncode_refund'] = $this->promotionmodel->restore_used_promotioncode_refund($data['promotion_code_seq']);
			}

			//청약철회상품체크
			unset($ctgoods);
			$ctgoods = $this->goodsmodel->get_goods($data['goods_seq']);
			$data['cancel_type'] = $ctgoods['cancel_type'];
			$data_refund_item[$k]['cancel_type'] = $ctgoods['cancel_type'];
			$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'], $data['option_seq']);

			unset($data['inputs']);
			if( $data['opt_type']  == 'opt' ) {
				$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'], $data['option_seq']);
			}

			// 배송비
			$data['shippings']	= '';
			$shipping_policy	= 'shop';
			$itemShip			= $itemShipping[$data['item_seq']];
			if	($data['goods_kind'] == 'goods' && $itemShip){
				$shipping_policy						= $itemShip['shipping_policy'];
				$data['shippings']['shipping_policy']	= $itemShip['shipping_policy'];
				$data['shippings']['goods_cost']		= $itemShip['goods_shipping_cost'];
				$data['shippings']['goods_add_cost']	= $itemShip['add_goods_shipping'];
				$data['shippings']['basic_cost']		= $itemShipping['basic_cost'];
				$data['shippings']['basic_add_cost']	= $itemShipping['area_add_delivery_cost'];
			}

			if		($data['goods_kind'] == 'coupon')	$shipping_policy	= 'coupon';
			elseif	($data['goods_kind'] == 'gift')		$shipping_policy	= 'gift';

			if	($shipping_policy == 'goods')	$shipping_policy	= 'goods_'.$data['item_seq'];

			$refund_items[$data['item_seq']]['items'][] = $data;
			$refund_items[$data['item_seq']]['refund_ea'] += $data['ea'];
			$refund_items[$data['item_seq']]['shipping_policy'] = $data['shipping_policy'];
			$refund_items[$data['item_seq']]['goods_shipping_policy'] = $data['shipping_unit']?'limited':'unlimited';
			$refund_items[$data['item_seq']]['unlimit_shipping_price'] = $data['goods_shipping_cost'];
			$refund_items[$data['item_seq']]['limit_shipping_price'] = $data['basic_shipping_cost'];
			$refund_items[$data['item_seq']]['limit_shipping_ea'] = $data['shipping_unit'];
			$refund_items[$data['item_seq']]['limit_shipping_subprice'] = $data['add_shipping_cost'];

			if($data['goods_type'] == "goods") $goods_exist++;

			$data_refund_item[$k]['inputs']	= $data['inputs'];

			$refund_shipping_items[$shipping_policy][]	= $data;
			$refund_shipping_items[$shipping_policy][0]['shipping_row_cnt']++;

			$refund_total_rows++;
		}

		foreach($refund_items as $item_seq => $data){

			$goods[$data['goods_seq']]++;

			// order_item의 ea합
			$query = $this->db->query("select sum(ea) as ea from fm_order_item_option where order_seq=? and item_seq=?", array($order_seq,$item_seq));
			$order_item_option_ea = $query->row_array();
			$query = $this->db->query("select sum(ea) as ea from fm_order_item_suboption where order_seq=? and item_seq=?", array($order_seq,$item_seq));
			$order_item_suboption_ea = $query->row_array();
			$order_item_ea = $order_item_ea['ea'] + $order_item_suboption_ea['ea'];

			if($data['unlimit_shipping_price']){
				$remain_item_shipping_cost = $this->goodsmodel->get_goods_delivery(array(
					'shipping_policy'			=> $data['shipping_policy'],
					'goods_shipping_policy'		=> $data['goods_shipping_policy'],
					'unlimit_shipping_price'	=> $data['unlimit_shipping_price'],
					'limit_shipping_price'		=> $data['limit_shipping_price'],
					'limit_shipping_ea'			=> $data['limit_shipping_ea'],
					'limit_shipping_subprice'	=> $data['limit_shipping_subprice'],
				),$order_item_ea-$data['refund_ea']);

				$refund_items[$item_seq]['refund_goods_shipping_cost'] = $data['unlimit_shipping_price']-$remain_item_shipping_cost['price'];

				$tot['refund_goods_shipping_cost'] += $refund_items[$item_seq]['refund_goods_shipping_cost'];

				$tot['goods_shipping_cnt']++;
			}else{
				$refund_items[$item_seq]['refund_goods_shipping_cost'] = 0;
			}

		}

		$tot['goods_cnt'] = array_sum($goods);

		$tot['refund_shipping_cost'] = $this->refundmodel->get_refund_shipping_cost(
			$data_order,
			$data_order_item,
			$data_refund,
			$data_refund_item
		);

		$pg = config_load($this->config_system['pgCompany']);
		$this->template->assign(array('pg'	=> $pg));

		$data_order['kspay_authty']	= '1010';	// KSPAY - 신용카드
		if		($data_order['payment'] == 'account')
			$data_order['kspay_authty']	= '2010';	// KSPAY - 계좌이체
		elseif	($data_order['payment'] == 'cellphone')
			$data_order['kspay_authty']	= 'M110';	// KSPAY - 휴대폰

		$gift_order = 'y';
		if($goods_exist) $gift_order = 'n';

		$this->template->assign(
			array(
			'refund_shipping_items'=>$refund_shipping_items,
			'refund_total_rows'=>$refund_total_rows,
			'process_log'=>$process_log,
			'data_refund'=>$data_refund,
			'data_refund_item'=>$data_refund_item,
			'refund_items'=>$refund_items,
			'tot'=>$tot,
			'gift_order'=>$gift_order,
			'data_order'=>$data_order,
			'members'=>$data_member)
		);		

		$this->template->define(array('tpl' => $this->template_path()));
		$this->template->print_("tpl");
	}



	/* 쿠폰 복원*/
	public function restore_used_coupon(){
		$this->load->model('couponmodel');

		if($this->couponmodel->restore_used_coupon($_GET['download_seq'])){
			$msg = "쿠폰이 복원되었습니다.";
		}else{
			$msg = "쿠폰 사용 내역을 찾을 수 없습니다.";
		}

		echo $msg;

	}


	/* 프로모션코드 복원*/
	public function restore_used_promotioncode(){
		$this->load->model('promotionmodel');

		if($this->promotionmodel->restore_used_promotion($_GET['download_seq'])){
			$msg = "프로모션코드가 복원되었습니다.";
		}else{
			$msg = "프로모션코드 사용 내역을 찾을 수 없습니다.";
		}

		echo $msg;

	}
}

/* End of file refund.php */
/* Location: ./app/controllers/admin/refund.php */