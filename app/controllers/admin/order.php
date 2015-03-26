<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class order extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('ordermodel');
		$this->load->helper('order');
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
			$_COOKIE['order_list_search'] = $cookie_str;
			setcookie('order_list_search',$cookie_str,time()+86400*30);
		}
		$callback = "parent.closeDialog('search_detail_dialog');parent.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_default(){
		$arr = explode('&',$_COOKIE['order_list_search']);
		foreach($arr as $data){
			$arr2 = explode("=",$data);
			$result[] = $arr2;
		}
		echo json_encode($result);
	}



	public function set_search_autodeposit(){
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
			$_COOKIE['autodeposit_list_search'] = $cookie_str;
			setcookie('autodeposit_list_search',$cookie_str,time()+86400*30);
		}
		$callback = "parent.location.reload();parent.closeDialog('search_detail_dialog');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_autodeposit(){
		$arr = explode('&',$_COOKIE['autodeposit_list_search']);
		foreach($arr as $data){
			$arr2 = explode("=",$data);
			$result[] = $arr2;
		}
		echo json_encode($result);
	}



	public function important()
	{
		$val = $_GET['val'];
		$no = str_replace('important_','',$_GET['no']);
		$query = "update fm_order set important=? where order_seq=?";
		$this->db->query($query,array($val,$no));
	}

	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('membermodel');
		$this->load->helper('shipping');
		$this->load->model('openmarketmodel');

		//유입매체
		$sitemarketplaceloop = sitemarketplace($_GET['sitemarketplace'], 'image', 'array');

		//오픈마켓연동정보
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

		$record = "";

		if($_GET['header_search_keyword']) {
			$_GET['keyword'] = $_GET['header_search_keyword'];
		}

		// 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다.
		if( count($_GET) == 0 ){
			if($_COOKIE['order_list_search']){
				$arr = explode('&',$_COOKIE['order_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);
					if($arr2[0]!='regist_date' ){
						$key = explode('[',$arr2[0]);
						$_GET[$key[0]][ str_replace(']','',$key[1]) ] = $arr2[1];
					}else{
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
				$_GET['regist_date'][0] = date('Y-m-d');
				$_GET['regist_date'][1] = date('Y-m-d');
				$_GET['chk_step'][15] = 1;
				$_GET['chk_step'][25] = 1;
			}
		}

		## 유입경로 그룹
		$this->load->model('statsmodel');
		$referer_list	= $this->statsmodel->get_referer_grouplist();
		$this->template->assign('referer_list',$referer_list);

		// 현재의 처리 프로세스
		$orders = config_load('order');
		$this->template->assign($orders);
		$this->template->define(array('order_process'=>$this->skin."/setting/_order_process.html"));

		//판매환경
		$sitetypeloop = sitetype($_GET['sitetype'], 'image', 'array');
		$this->template->assign('sitetypeloop',$sitetypeloop);
		$this->template->assign('order_list_search',$_COOKIE['order_list_search']);
		$this->template->assign('old_list',$_GET['old_list']);

		$this->template->assign('sitemarketplaceloop',$sitemarketplaceloop);
		$this->template->assign(array('able_step_action' => $this->ordermodel->able_step_action));

		$invoice_guide_path = dirname($this->template_path()).'/_invoice_guide.html';
		$this->template->define(array('invoice_guide'=>$invoice_guide_path));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign(array('record' => $record));
		$this->template->print_("tpl");
	}

	public function view()
	{

		$this->load->model('membermodel');
		$this->load->model('goodsmodel');
		$this->load->model('exportmodel');
		$this->load->model('returnmodel');
		$this->load->model('eventmodel');

		$socialcp_status_loop = get_socialcp_status($this->exportmodel->socialcp_status);
		$this->template->assign(array('socialcp_status_loop'	=> $socialcp_status_loop));
		$socialcp_status_path = dirname($this->template_path()).'/../order/_socialcp_status_guide.html';
		$this->template->define(array('socialcp_status_guide'=>$socialcp_status_path));

		$file_path	= $this->template_path();
		$order_seq 	= $_GET['no'];

		$process_log 	= $this->ordermodel->get_log($order_seq,'all');
		$cancel_log 	= $this->ordermodel->get_log($order_seq,'cancel');

		$orders 			= $this->ordermodel->get_order($order_seq);
		$items 				= $this->ordermodel->get_item($order_seq);
		$child_order_seq	= $this->ordermodel->get_child_order_seq($order_seq);

		// 배송방법
		$orders['mshipping'] = $this->ordermodel->get_delivery_method($orders);

		// 다중배송지
		$order_shippings = $this->ordermodel->get_shipping($order_seq);
		$this->template->assign(array('order_shippings'=>$order_shippings));
		foreach($order_shippings as $order_shipping){
			$shipping_items	= $order_shipping['shipping_items'];
			if	($shipping_items)foreach($shipping_items as $itemShip){
				$itemShipping[$itemShip['item_seq']]	= $itemShip;
				$tot['add_goods_shipping']				+= $itemShip['add_goods_shipping'];
			}

			// 기본배송 지역별 추가배송비
			$tot['area_add_delivery_cost']	+= $order_shipping['area_add_delivery_cost'];
		}
		// 기본배송비
		$tot['basic_cost']			+= $order_shippings[0]['shipping_cost'] - $tot['area_add_delivery_cost'];
		$tot['shop_shipping_cost']	+= $order_shippings[0]['shipping_cost'];

		$orders['mpayment'] = $this->arr_payment[$orders['payment']];
		$orders['mstep'] 	= $this->arr_step[$orders['step']];
		if($orders['recipient_zipcode']) $orders['recipient_zipcode'] 	= explode('-',$orders['recipient_zipcode']);
		if($orders['recipient_phone']) $orders['recipient_phone'] 	= explode('-',$orders['recipient_phone']);
		if($orders['recipient_cellphone']) $orders['recipient_cellphone'] 	= explode('-',$orders['recipient_cellphone']);

		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
		}

		$tot['goods_ready_cnt'] =  $tot['coupontotal'] = $tot['coupontotal'] = 0;
		foreach($items as $key=>$item){

			$item['goods_row_cnt'] = 0;

			if ( $item['goods_kind'] == 'coupon' ) {
				$tot['coupontotal']++;//쇼셜쿠폰상품@2013-11-06
			}else{
				$tot['goodstotal']++;
			}

			$reOption	= array();
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);

			if($item['event_seq']) {
				$events = $this->eventmodel->get_event($item['event_seq']);
				if($events['title']) $item['event_title'] = $events['title'];
				if($events['event_type']) $item['event_type'] = $events['event_type'];
			}

			if($options) foreach($options as $k => $data){
				$item['goods_row_cnt']++;
				if	($data['step'] > $goods_kind_arr[$item['goods_kind']])
					$goods_kind_arr[$item['goods_kind']]	= $data['step'];
				
				if( $data['step'] < 35 ) $tot['goods_ready_cnt']++;//상품별 상품준비 버튼 노출여부

				$real_stock = $this->goodsmodel -> get_goods_option_stock(
					$item['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5']
				);

				$rstock = $this->ordermodel -> get_option_reservation(
					$this->cfg_order['ableStockStep'],
					$item['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5']
				);

				$stock = (int) $real_stock - (int) $rstock;
				$data['mstep']		= $this->arr_step[$data['step']];
				$data['real_stock'] = $real_stock;
				$data['stock'] = $stock;

				if($orders['international'] != 'international'){
					if($item['shipping_policy'] == 'shop'){
						$data['out_shipping_method'] = $orders['mshipping'];
					}else{
						$data['out_shipping_method'] = "개별배송";
					}
				}

				// 옵션 배송방법별 출고 수량
				$tmp_option_export_item = $this->exportmodel->get_export_item_by_option_seq($data['item_option_seq']);
				foreach($tmp_option_export_item as $option_export_item){
					if($option_export_item['domestic_shipping_method']){
						$data['export_sum_ea'][$option_export_item['domestic_shipping_method']] += $option_export_item['ea'];
					}
				}

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
				$data['out_referer_sale'] = $data['referer_sale'];

				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];

				$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];

				if($data['step'] >= 25 && $data['step'] < 75){
					$data['ready_ea'] = $data['ea'] - $data['step_complete'] - $data['step85'];
				}

				###
				unset($data['inputs']);
				$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'],$data['item_option_seq']);

				$tmp = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['item_option_seq'],null,'return');
				$data['return_list_ea'] = $tmp['ea'];
				$tmp = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['item_option_seq'],null,'exchange');
				$data['exchange_list_ea'] = $tmp['ea'];

				$options[$k] = $data;

				$tot['ea'] += $data['ea'];
				$tot['ready_ea'] += $data['ready_ea'];
				$tot['step_complete'] += $data['step_complete'];
				$tot['step85']+= $data['step85'];
				$tot['supply_price'] += $data['out_supply_price'];
				$tot['commission_price'] += $data['out_commission_price'];
				$tot['consumer_price'] += $data['out_consumer_price'];
				$tot['price'] += $data['out_price'];

				$tot['member_sale'] += $data['out_member_sale'];
				$tot['coupon_sale'] += $data['out_coupon_sale'];
				$tot['fblike_sale'] += $data['out_fblike_sale'];
				$tot['mobile_sale'] += $data['out_mobile_sale'];
				$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];
				$tot['referer_sale']		+= $data['out_referer_sale'];

				$tot['reserve'] += $data['out_reserve'];
				$tot['point'] += $data['out_point'];
				$tot['real_stock'] += $real_stock;
				$tot['stock'] += $stock;

				$return_item = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['item_option_seq']);
				$able_return_ea += (int) $data['step75'] - (int) $return_item['ea'];

				$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $data['item_option_seq']);
				if($suboptions) foreach($suboptions as $k => $subdata){
					$item['goods_row_cnt']++;
					if	($subdata['step'] > $goods_kind_arr[$item['goods_kind']])
						$goods_kind_arr[$item['goods_kind']]	= $subdata['step'];

					if( $subdata['step'] < 35 ) $tot['goods_ready_cnt']++;//상품별 상품준비 버튼 노출여부

					$real_stock		= $this->goodsmodel -> get_goods_suboption_stock(
						$item['goods_seq'],
						$subdata['title'],
						$subdata['suboption']
					);
					$rstock		= $this->ordermodel -> get_suboption_reservation(
						$this->cfg_order['ableStockStep'],
						$item['goods_seq'],
						$subdata['title'],
						$subdata['suboption']
					);

					$stock					= (int) $real_stock - (int) $rstock;
					$subdata['real_stock']	= (int) $real_stock;
					$subdata['stock']		= (int) $stock;

					// 추가옵션 배송방법별 출고 수량
					$tmp_option_export_item = $this->exportmodel->get_export_item_by_suboption_seq($subdata['item_suboption_seq']);
					foreach($tmp_option_export_item as $option_export_item){
						if($option_export_item['domestic_shipping_method']){
							$subdata['export_sum_ea'][$option_export_item['domestic_shipping_method']] += $option_export_item['ea'];
						}
					}

					###
					$subdata['out_supply_price']		= $subdata['supply_price']*$subdata['ea'];
					$subdata['out_commission_price']	= $subdata['commission_price']*$subdata['ea'];
					$subdata['out_consumer_price']		= $subdata['consumer_price']*$subdata['ea'];
					$subdata['out_price']				= $subdata['price']*$subdata['ea'];

					$subdata['out_member_sale']			= $subdata['member_sale']*$subdata['ea'];
					$subdata['out_fblike_sale']			= $subdata['fblike_sale'];
					$subdata['out_mobile_sale']			= $subdata['mobile_sale'];
					$subdata['out_promotion_code_sale']	= $subdata['promotion_code_sale'];
					if($subdata['download_seq'])
						$subdata['out_coupon_sale']		= $subdata['coupon_sale'];
					$subdata['out_referer_sale']		= $subdata['referer_sale'];

					$subdata['out_reserve']				= $subdata['reserve']*$subdata['ea'];
					$subdata['out_point']				= $subdata['point']*$subdata['ea'];

					$subdata['mstep']					= $this->arr_step[$subdata['step']];
					$subdata['step_complete']			= $subdata['step45'] + $subdata['step55'] + $subdata['step65'] + $subdata['step75'];

					if($subdata['step'] >= 25 && $subdata['step'] < 75){
						$subdata['ready_ea'] = $subdata['ea'] - $subdata['step_complete'] - $subdata['step85'];
					}

					$tmp = $this->returnmodel->get_return_subitem_ea($subdata['item_seq'],$subdata['item_suboption_seq'],null,'return');
					$subdata['return_list_ea'] = $tmp['ea'];
					$tmp = $this->returnmodel->get_return_subitem_ea($subdata['item_seq'],$subdata['item_suboption_seq'],null,'exchange');
					$subdata['exchange_list_ea'] = $tmp['ea'];

					$suboptions[$k]						= $subdata;

					$tot['ea']					+= $subdata['ea'];
					$tot['ready_ea']			+= $subdata['ready_ea'];
					$tot['step85']				+= $subdata['step85'];
					$tot['step_complete']		+= $subdata['step_complete'];
					$tot['supply_price'] 		+= $subdata['out_supply_price'];
					$tot['commission_price']	+= $subdata['out_commission_price'];
					$tot['consumer_price'] 		+= $subdata['out_consumer_price'];

					$tot['member_sale']			+= $subdata['out_member_sale'];
					$tot['coupon_sale']			+= $subdata['out_coupon_sale'];
					$tot['fblike_sale']			+= $subdata['out_fblike_sale'];
					$tot['mobile_sale']			+= $subdata['out_mobile_sale'];
					$tot['promotion_code_sale']	+= $subdata['out_promotion_code_sale'];
					$tot['referer_sale']		+= $subdata['out_referer_sale'];
					$tot['price'] 				+= $subdata['out_price'];

					$tot['reserve']				+= $subdata['out_reserve'];
					$tot['point']				+= $subdata['out_point'];

					$tot['real_stock'] 			+= $real_stock;
					$tot['stock'] 				+= $stock;

					$return_item = $this->returnmodel->get_return_item_ea($subdata['item_seq'],$subdata['item_suboption_seq']);
					$able_return_ea += (int) $subdata['step75'] - (int) $return_item['ea'];
				}

				$data['suboptions']	= $suboptions;
				$reOption[]			= $data;
			}

			// 배송비
			$item['shippings']	= '';
			$shipping_policy	= 'shop';
			$itemShip			= $itemShipping[$item['item_seq']];
			if	($item['goods_kind'] == 'goods' && $itemShip){
				$shipping_policy						= $itemShip['shipping_policy'];
				$item['shippings']['shipping_policy']	= $itemShip['shipping_policy'];
				$item['shippings']['goods_cost']		= $itemShip['goods_shipping_cost'];
				$item['shippings']['goods_add_cost']	= $itemShip['add_goods_shipping'];
				$item['shippings']['basic_cost']		= $tot['basic_cost'];
				$item['shippings']['basic_add_cost']	= $tot['area_add_delivery_cost'];
			}

			if		($item['goods_kind'] == 'coupon')	$shipping_policy	= 'coupon';
			elseif	($item['goods_kind'] == 'gift')		$shipping_policy	= 'gift';

			if	($shipping_policy == 'goods')	$shipping_policy	= 'goods_'.$item['item_seq'];

			$item['international']		= $orders['international'];
			$item['options']			= $reOption;
			$Ritems[$shipping_policy][]	= $item;
			$Ritems[$shipping_policy][0]['shipping_row_cnt']	+= $item['goods_row_cnt'];
			$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
		}
		$items	= $Ritems;

		$tot['tot_shipping_cost']	= $order_shippings[0]['shipping_cost'] + $tot['goods_shipping_cost'];


		$this->template->assign(array('goods_kind_arr'=> $goods_kind_arr));

		$orders['able_return_ea'] = $able_return_ea;

		// 회원 정보 가져오기
		if($orders['member_seq']){
			$members = $this->membermodel->get_member_data($orders['member_seq']);
			$members['type'] = $members['business_seq'] ? '기업' : '개인';
			$this->template->assign(array('members'=>$members));
		}

		$this->load->helper('shipping');
		$shipping = use_shipping_method();
		if( $shipping ) foreach($shipping as $key => $data){
			if($data) $shipping_cnt[$key][] = count($data);
		}
		$shipping_policy['policy'] 	= $shipping;

		$data_export = $this->exportmodel->get_export_for_order($order_seq);
		foreach($data_export as $k=>$row_export){
			$data_export_item 		= $this->exportmodel->get_export_item($row_export['export_code']);
			if	($data_export_item[0]['goods_kind'] == 'coupon'){
				$params['export_code']	= $row_export['export_code'];

				$row_export['couponinfo'] = get_goods_coupon_view($row_export['export_code']);

				$params['send_kind']	= 'mail';
				$mail_send_log			= $this->exportmodel->get_coupon_export_send_log($params, 2);
				$params['send_kind']	= 'sms';
				$sms_send_log			= $this->exportmodel->get_coupon_export_send_log($params, 2);
				$params['send_kind']	= 'sms';
				$sms_send_log			= $this->exportmodel->get_coupon_export_send_log($params, 2);
				$coupon_use_log			= $this->exportmodel->get_coupon_use_history($data_export_item[0]['coupon_serial']);
			}

			if($row_export['status'] == '45'){
				$orders['export_ready_ea'] += $row_export['ea'];
			}else{
				$orders['export_complete_ea'] += $row_export['ea'];
			}

			$row_export['price'] = $row_export['price'] + $row_export['sub_price'];
			$tot_export['price'] += $row_export['price'];
			$tot_export['reserve'] += $row_export['reserve'];
			$tot_export['point'] += $row_export['point'];

			$row_export['mail_send_log']		= $mail_send_log;
			$row_export['sms_send_log']			= $sms_send_log;
			$row_export['coupon_use_log']		= $coupon_use_log;
			$row_export['goods_kind']			= $data_export_item[0]['goods_kind'];
			$row_export['coupon_serial']		= $data_export_item[0]['coupon_serial'];
			$row_export['coupon_input']			= $data_export_item[0]['coupon_input'];
			$row_export['coupon_input_type']	= $data_export_item[0]['socialcp_input_type'];
			$row_export['coupon_remain_value']	= $data_export_item[0]['coupon_remain_value'];
			//$row_export['mstatus'] = $this->exportmodel->arr_step[$row_export['status']];

			$export[]	= $row_export;
		}

		//반품정보 가져오기
		$orders['return_list_ea'] = 0;
		$this->load->model('returnmodel');
		$data_return = $this->returnmodel->get_return_for_order($order_seq,"return");
		$r_refund_code = array();
		if( $data_return )foreach($data_return as $k=>$data){

			if($data['refund_code']){
				$r_refund_code[] = $data['refund_code'];
				$return_field = "return";

				$orders['return_list'][$data['refund_code']] = $data;

			}else{

				$return_field = "exchange";
			}

			$data['mstatus'] = $this->returnmodel->arr_return_status[$data['status']];
			//관리자가 처리했을경우 ID가져오기
			if($data['manager_seq']){
				$rtsql = "select * from fm_manager where manager_seq=?";
				$m_seq=$data['manager_seq'];
				$query = $this->db->query($rtsql,array($m_seq));
				$m_data = $query->row_array();
				$data['manager_id']=$m_data['manager_id'];
				$data['mname']=$m_data['mname'];
			}

			$data_return[$k] = $data;
			$orders['return_list_ea'] += $data['ea'];

			if($data['status']=='complete'){
				$orders[$return_field.'_complete_ea'] += $data['ea'];
			}else if($data['status']=='request'){
				$orders[$return_field.'_request_ea'] += $data['ea'];
			}
		}

		//교환정보 가져오기
		$orders['exchange_list_ea'] = 0;
		$this->load->model('returnmodel');
		$data_exchange = $this->returnmodel->get_return_for_order($order_seq,"exchange");
		if( $data_exchange )foreach($data_exchange as $k=>$data){
			$data['mstatus'] = $this->returnmodel->arr_return_status[$data['status']];
			//관리자가 처리했을경우 ID가져오기
			if($data['manager_seq']){
				$rtsql = "select * from fm_manager where manager_seq=?";
				$m_seq=$data['manager_seq'];
				$query = $this->db->query($rtsql,array($m_seq));
				$m_data = $query->row_array();
				$data['manager_id']=$m_data['manager_id'];
				$data['mname']=$m_data['mname'];
			}
			$orders['exchange_list'][] = $data;
			$data_exchange[$k] = $data;
			$orders['exchange_list_ea'] += $data['ea'];
		}

		//환불정보 가져오기
		$orders['cancel_list_ea'] = 0;
		$orders['refund_list_ea'] = 0;
		$this->load->model('refundmodel');
		$data_refund = $this->refundmodel->get_refund_for_order($order_seq);
		if( $data_refund )foreach($data_refund as $k=>$data){

			$data['is_return'] = 0;
			$refund_field = 'refund';
			if( in_array($data['refund_code'],$r_refund_code) ){
				$data['is_return'] = 1;
				$refund_field = 'return_refund';
			}

			$data['mstatus'] = $this->refundmodel->arr_refund_status[$data['status']];
			if($data['manager_seq']){
				$rtsql = "select * from fm_manager where manager_seq=?";
				//관리자가 처리했을경우 ID가져오기
				$m_seq=$data['manager_seq'];
				$query = $this->db->query($rtsql,array($m_seq));
				$m_data = $query->row_array();
				$data['manager_id']=$m_data['manager_id'];
				$data['mname']=$m_data['mname'];
			}

			if ($data['refund_date']=="0000-00-00") {
				$data['refund_date'] = "";
			}

			$data_refund[$k] = $data;

			if( $data['refund_type'] == 'cancel_payment' ){
				$orders['cancel_list_ea'] += $data['ea'];
				$orders['cancel_list_count'] += 1;

				$orders['cancel_list'][] = $data;
			}else{
				$orders['refund_list_ea'] += $data['ea'];
				if($data['status']=='complete'){
					$orders[$refund_field.'_complete_ea'] += $data['ea'];
				}else if($data['status']=='request'){
					$orders[$refund_field.'_request_ea'] += $data['ea'];
				}

				$orders['return_list'][$data['refund_code']]['refund_status'] = $data['status'];
			}
		}



		$this->load->model('salesmodel');
		//세금계산서 or 현금영수증
		$sc['whereis']	= ' and typereceipt = "'.$orders['typereceipt'].'" and order_seq="'.$order_seq.'" ';
		$sc['select']		= '  cash_no, tstep, seq  ';
		$sales 		= $this->salesmodel->get_data($sc);
		if( $sales ) {
			if($sales['tstep']=='1')
			{
				$cash_msg = "발급신청";
			}
			else if($sales['tstep']=='2')
			{
				$cash_msg = "발급완료";
			} else if($sales['tstep']=='3')
			{
				$cash_msg = "발급취소";
			} else if($sales['tstep']=='4')
			{
				$cash_msg = "발급실패";
			}

			if(!($orders['payment'] =='card' && $orders['payment'] =='cellphone') ) {
				 if( $orders['typereceipt'] == 2 ) {
					 $cash_receipts_no = ($sales['cash_no'])?$sales['cash_no']:$orders['cash_receipts_no'];
					if(!$cash_receipts_no) {
						$cash_msg = "발급실패";
					}
				}
			}
			$this->template->assign(array('sales_cash_msg'	=> $cash_msg));
		}
		$orders['sitetypetitle']	= sitetype($orders['sitetype'], 'image', '');//판매환경
		$orders['marketplacetitle'] = sitemarketplace($orders['marketplace'], 'image', '');//유입매체
		$pg_log = $this->ordermodel->get_pg_log($order_seq);
		if( preg_match('/virtual/',$orders['payment']) && $pg_log[1]){		//가상계좌
			$orders['pg_log'][0] = $pg_log[1];
		}else{
			$orders['pg_log'][0] = $pg_log[0];
		}

		if ($orders['deposit_date']=="0000-00-00 00:00:00") $orders['deposit_date'] = "";

		// 수량 / 종 추가 leewh 2014-08-01
		if (!$orders['total_ea']) {
			$orders['total_ea'] = $tot['ea'];
		}

		if (!$orders['total_type']) {
			$orders['total_type'] = count($items);
		} 

		$config_order	= config_load('order');
		$this->template->assign(array('config_order'	=> $config_order));
		$this->template->assign(array('orders'	=> $orders));
		$this->template->assign(array('data_export'	=> $export));
		$this->template->assign(array('tot_export'	=> $tot_export));
		$this->template->assign(array('items'	=> $items));
		$this->template->assign(array('items_tot'	=> $tot));
		$this->template->assign(array('shipping_tot'	=> $shipping_tot));
		$this->template->assign(array('bank'	=> $bank));
		$this->template->assign(array('pay_log'	=> $pay_log));
		$this->template->assign(array('process_log'	=> $process_log));
		$this->template->assign(array('cancel_log'	=> $cancel_log));
		$this->template->assign(array('data_return'	=> $data_return));
		$this->template->assign(array('data_exchange'	=> $data_exchange));
		$this->template->assign(array('data_refund'	=> $data_refund));
		$this->template->assign(array('shipping_policy'	=> $shipping_policy));
		$this->template->assign(array('able_step_action' => $this->ordermodel->able_step_action));
		$this->template->assign(array('child_order_seq' => $child_order_seq));

		//오픈마켓연동정보
		if($orders['linkage_id']){
			$this->load->model('openmarketmodel');
			$linkage = $this->openmarketmodel->get_linkage_config();
			if($linkage){
				// 설정된 판매마켓 정보
				$linkage_mallnames = array();
				$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
				foreach($linkage_malldata as $k => $data){
					if	($data['default_yn'] == 'Y'){
						$linkage_mallnames[$data['mall_code']]	= $data['mall_name'];
					}
				}
				$this->template->assign('linkage',$linkage);
				$this->template->assign('linkage_mallnames',$linkage_mallnames);
			}
		}

		if( $_GET['mode'] ){ // 출고상세에서 출력용
			$file_path = str_replace('view.html','view_summary.html',$file_path);
		}else{
			$this->admin_menu();
			$this->tempate_modules();
		}

		$this->template->define(array('tpl'	=> $file_path));
		$this->template->print_("tpl");
	}

	public function goods_export(){

		$order_seq = $_GET['seq'];
		$cfg_order = config_load('order');

		$orders	= $this->ordermodel->get_order($order_seq);
		if( !in_array($orders['step'],$this->ordermodel->able_step_action['goods_export']) ){
			openDialogAlert($this->arr_step[$orders['step']]."에서는 출고처리를 하실 수 없습니다.",400,140,'parent',"");
			exit;
		}

		$this->load->model('goodsmodel');
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');
		$this->load->model('exportmodel');

		$order_shippings = $this->ordermodel->get_shipping($order_seq);

		foreach($order_shippings as $key=>$order_shipping){

			$coupon_cnt = $goods_cnt = $shipping_step_remind = 0;

			foreach($order_shipping['shipping_items'] as $itemKey => $item){

				$order_shipping['shipping_items'][$itemKey]['shipping_item_option'] = array();
				$order_shipping['shipping_items'][$itemKey]['shipping_item_suboption'] = array();
				
				foreach($item['shipping_item_option'] as $k => $data){
					$real_stock = $this->goodsmodel -> get_goods_option_stock(
						$item['goods_seq'],
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
					$step_complete = $this->ordermodel -> get_option_export_complete(
						$order_seq,
						$order_shipping['shipping_seq'],
						$data['order_item_seq'],
						$data['order_item_option_seq']
					);

					$rf_ea = $this->refundmodel->get_refund_option_ea($order_shipping['shipping_seq'],$data['item_seq'],$data['order_item_option_seq']);

					$data['step_refund'] = $rf_ea;

					$stock = (int) $real_stock - (int) $rstock;
					$data['real_stock'] = $real_stock;
					$data['stock'] = $stock;
					$data['step_complete'] = $step_complete;//$data['step45']+$data['step55']+$data['step65']+$data['step75'];
					$data['step_remind'] = $data['ea'] - $data['step_refund']/*$data['step85']*/ -  $data['step_complete'];
					$order_shipping['shipping_items'][$itemKey]['shipping_item_option'][$k]	= $data;;
					$shipping_step_remind+=$data['step_remind'];

					foreach($data['shipping_item_suboption'] as $k_sub => $data_sub){
						$real_stock = $this->goodsmodel -> get_goods_suboption_stock(
							$item['goods_seq'],
							$data_sub['title'],
							$data_sub['suboption']
						);
						$rstock = $this->ordermodel -> get_suboption_reservation(
							$this->cfg_order['ableStockStep'],
							$data_sub['goods_seq'],
							$data_sub['title'],
							$data_sub['suboption']
						);
						$step_complete = $this->ordermodel -> get_suboption_export_complete(
							$order_seq,
							$order_shipping['shipping_seq'],
							$data_sub['item_seq'],
							$data_sub['item_suboption_seq']
						);

						$rf_ea = $this->refundmodel->get_refund_suboption_ea($order_shipping['shipping_seq'],$data_sub['item_seq'],$data_sub['item_suboption_seq']);
						$data_sub['step_refund'] = $rf_ea;
						$stock = (int) $real_stock - (int) $rstock;
						$data_sub['real_stock'] = (int) $real_stock;
						$data_sub['stock'] = (int) $stock;
						$data_sub['step_complete'] = $step_complete;//$data_sub['step45']+$data_sub['step55']+$data_sub['step65']+$data_sub['step75'];
						$data_sub['step_remind'] = $data_sub['ea'] - $data_sub['step_refund']/*$data_sub['step85']*/ -  $data_sub['step_complete'];
						$order_shipping['shipping_items'][$itemKey]['shipping_item_option'][$k]['shipping_item_suboption'][$k_sub]	= $data_sub;
						$shipping_step_remind+=$data_sub['step_remind'];
					}

					// 쿠폰상품 발송 결과
					if	($item['goods_kind'] == 'coupon'){
						$export	= $this->exportmodel->get_export_item_for_order($item['item_seq'], $data['item_option_seq']);
						unset($export_send);
						for ($z = 0; $z < $data['ea']; $z++){
							if	($export[$z]){
								$export_send[$z]['export_code']		= $export[$z]['export_code'];
								$export_send[$z]['email']			= $export[$z]['recipient_email'];
								$export_send[$z]['sms']				= $export[$z]['recipient_cellphone'];
								$export_send[$z]['mail_status']		= $export[$z]['mail_status'];
								$export_send[$z]['sms_status']		= $export[$z]['sms_status'];
							}else{
								$export_send[$z]['export_code']		= '';
								$export_send[$z]['email']			= $orders['recipient_email'];
								$export_send[$z]['sms']				= $orders['recipient_cellphone'];
								$export_send[$z]['mail_status']		= 'x';
								$export_send[$z]['sms_status']		= 'x';
							}
						}
						$order_shipping['shipping_items'][$itemKey]['shipping_item_option'][$k]['export_send']	= $export_send;
					}
				}

				if	($item['goods_kind'] == 'coupon')	$coupon_cnt++;
				else									$goods_cnt++;
			}
			$order_shipping['coupon_cnt']	= $coupon_cnt;
			$order_shipping['goods_cnt']	= $goods_cnt;
			$order_shippings[$key]			= $order_shipping;

			//if($shipping_step_remind==0) unset($order_shippings[$key]);
		}

		/*
		$items = $this->ordermodel->get_item($order_seq);
		foreach($items as $key=>$item){
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);

			if($options) foreach($options as $k => $data){
				$real_stock = $this->goodsmodel -> get_goods_option_stock(
					$item['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5']
				);

				$rstock = $this->ordermodel -> get_option_reservation(
					$this->cfg_order['ableStockStep'],
					$item['goods_seq'],
					$data['option1'],
					$data['option2'],
					$data['option3'],
					$data['option4'],
					$data['option5']
				);

				$stock = (int) $real_stock - (int) $rstock;
				$data['real_stock'] = $real_stock;
				$data['stock'] = $stock;
				$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
				$data['step_remind'] = $data['ea'] - $data['step85'] -  $data['step_complete'];


				$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $data['item_option_seq']);
				if($suboptions) foreach($suboptions as $s => $subdata){
					$real_stock = $this->goodsmodel -> get_goods_suboption_stock(
						$item['goods_seq'],
						$subdata['title'],
						$subdata['suboption']
					);
					$rstock = $this->ordermodel -> get_suboption_reservation(
						$this->cfg_order['ableStockStep'],
						$item['goods_seq'],
						$subdata['title'],
						$subdata['suboption']
					);

					$stock = (int) $real_stock - (int) $rstock;
					$subdata['real_stock'] = (int) $real_stock;
					$subdata['stock'] = (int) $stock;
					$subdata['step_complete'] = $subdata['step45']+$subdata['step55']+$subdata['step65']+$subdata['step75'];
					$subdata['step_remind'] = $subdata['ea'] - $subdata['step85'] -  $subdata['step_complete'];
					$suboptions[$s] = $subdata;
				}

				$data['suboptions']	= $suboptions;
				$options[$k]		= $data;
			}

			$item['options']	= $options;
			$items[$key] 		= $item;
		}
		*/

		$this->load->helper('shipping');
		$shipping = use_shipping_method();
		if( $shipping ) foreach($shipping as $key => $data){
			if($data) $shipping_cnt[$key] = count($data);
		}
		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;

		$invoice_guide_path = dirname($this->template_path()).'/_invoice_guide.html';
		$this->template->define(array('invoice_guide'=>$invoice_guide_path));

		//오픈마켓연동정보
		if($orders['linkage_mall_code']){
			$this->load->model('openmarketmodel');
			// 설정된 판매마켓 정보
			$linkage_mallnames = array();
			$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
			
			foreach($linkage_malldata as $k => $data){
				if	($data['mall_code'] == $orders['linkage_mall_code']){
					$orders['linkage_mall_name'] = $data['mall_name'];
					break;
				}
			}
		}

		$smsinfo	= get_sms_remind_count();
		$this->template->assign(array('smsinfo'	=> $smsinfo));
		$this->template->assign(array('order_shippings'=>$order_shippings));
		$this->template->assign(array('cfg_order'	=> $cfg_order));
		$this->template->assign(array('orders'	=> $orders));
		//$this->template->assign(array('items'	=> $items));
		$this->template->assign(array('shipping_policy'	=> $shipping_policy));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	// 일괄 주문 출고처리
	public function order_export(){

		// check parameter order_seq
		if	($_POST['seq'] && count($_POST['seq']) > 0){

			// load
			$this->load->model('goodsmodel');
			$this->load->model('refundmodel');
			$this->load->model('ordermodel');
			$this->load->model('exportmodel');
			$this->load->helper('shipping');

			$cfg_order = config_load('order');

			$this->load->helper('shipping');
			$shipping = use_shipping_method();
			if( $shipping ) foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
			$shipping_policy['policy'] 	= $shipping;
			$shipping_policy['count'] 	= $shipping_cnt;

			// order_seq loop
			foreach($_POST['seq'] as $k => $order_seq){
				unset($orders);
				$orders			= $this->ordermodel->get_order($order_seq);
				if( !in_array($orders['step'],$this->ordermodel->able_step_action['goods_export']) ){
					openDialogAlert($this->arr_step[$orders['step']]."에서는 출고처리를 하실 수 없습니다.",400,140,'parent',"");
					exit;
				}

				unset($order_shippings);
				$order_shippings	= $this->ordermodel->get_shipping($order_seq);
				foreach($order_shippings as $key=>$order_shipping){
					$rowspan = $coupon_cnt = $goods_cnt = $shipping_step_remind = 0;
					$default_email = $default_sms = '';
					foreach($order_shipping['shipping_items'] as $itemKey => $item){
						$order_shipping['shipping_items'][$itemKey]['shipping_item_option']		= array();
						$order_shipping['shipping_items'][$itemKey]['shipping_item_suboption']	= array();

						foreach($item['shipping_item_option'] as $k => $data){
							$rowspan++;
							$real_stock = $this->goodsmodel -> get_goods_option_stock(
								$item['goods_seq'],
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
							$step_complete = $this->ordermodel -> get_option_export_complete(
								$order_seq,
								$order_shipping['shipping_seq'],
								$data['order_item_seq'],
								$data['order_item_option_seq']
							);

							$rf_ea = $this->refundmodel->get_refund_option_ea($order_shipping['shipping_seq'],$data['item_seq'],$data['order_item_option_seq']);

							$data['step_refund'] = $rf_ea;

							$stock = (int) $real_stock - (int) $rstock;
							$data['real_stock'] = $real_stock;
							$data['stock'] = $stock;
							$data['step_complete'] = $step_complete;//$data['step45']+$data['step55']+$data['step65']+$data['step75'];
							$data['step_remind'] = $data['ea'] - $data['step_refund']/*$data['step85']*/ -  $data['step_complete'];
							$order_shipping['shipping_items'][$itemKey]['shipping_item_option'][$k]	= $data;;
							$shipping_step_remind+=$data['step_remind'];

							foreach($data['shipping_item_suboption'] as $k_sub => $data_sub){
								$rowspan++;
								$real_stock = $this->goodsmodel -> get_goods_suboption_stock(
									$item['goods_seq'],
									$data_sub['title'],
									$data_sub['suboption']
								);
								$rstock = $this->ordermodel -> get_suboption_reservation(
									$this->cfg_order['ableStockStep'],
									$data_sub['goods_seq'],
									$data_sub['title'],
									$data_sub['suboption']
								);
								$step_complete = $this->ordermodel -> get_suboption_export_complete(
									$order_seq,
									$order_shipping['shipping_seq'],
									$data_sub['item_seq'],
									$data_sub['item_suboption_seq']
								);

								$rf_ea = $this->refundmodel->get_refund_suboption_ea($order_shipping['shipping_seq'],$data_sub['item_seq'],$data_sub['item_suboption_seq']);
								$data_sub['step_refund'] = $rf_ea;
								$stock = (int) $real_stock - (int) $rstock;
								$data_sub['real_stock'] = (int) $real_stock;
								$data_sub['stock'] = (int) $stock;
								$data_sub['step_complete'] = $step_complete;//$data_sub['step45']+$data_sub['step55']+$data_sub['step65']+$data_sub['step75'];
								$data_sub['step_remind'] = $data_sub['ea'] - $data_sub['step_refund']/*$data_sub['step85']*/ -  $data_sub['step_complete'];
								$order_shipping['shipping_items'][$itemKey]['shipping_item_option'][$k]['shipping_item_suboption'][$k_sub]	= $data_sub;
								$shipping_step_remind+=$data_sub['step_remind'];
							}

							// 쿠폰상품 발송 결과
							if	($item['goods_kind'] == 'coupon'){
								$export	= $this->exportmodel->get_export_item_for_order($item['item_seq'], $data['item_option_seq']);
								unset($export_send);
								for ($z = 0; $z < $data['ea']; $z++){
									if	($export[$z]){
										$export_send[$z]['export_code']		= $export[$z]['export_code'];
										$export_send[$z]['email']			= $export[$z]['recipient_email'];
										$export_send[$z]['sms']				= $export[$z]['recipient_cellphone'];
										$export_send[$z]['mail_status']		= $export[$z]['mail_status'];
										$export_send[$z]['sms_status']		= $export[$z]['sms_status'];
									}else{
										$export_send[$z]['export_code']		= '';
										$export_send[$z]['email']			= $orders['recipient_email'];
										$export_send[$z]['sms']				= $orders['recipient_cellphone'];
										$export_send[$z]['mail_status']		= 'x';
										$export_send[$z]['sms_status']		= 'x';
									}

									if	(trim($export_send[$z]['email']))
										$default_email	= $export_send[$z]['email'];
									if	(trim($export_send[$z]['sms']))
										$default_sms	= $export_send[$z]['sms'];
								}
								$order_shipping['shipping_items'][$itemKey]['shipping_item_option'][$k]['export_send']	= $export_send;
							}
						}

						if	($item['goods_kind'] == 'coupon')	$coupon_cnt++;
						else									$goods_cnt++;

						$order_shipping['default_email']		= $default_email;
						$order_shipping['default_sms']			= $default_sms;
					}

					$order_shipping['coupon_cnt']				= $coupon_cnt;
					$order_shipping['goods_cnt']				= $goods_cnt;
					$order_shipping['rowspan']					= $rowspan;
					$order_shippings[$key]						= $order_shipping;

					//if($shipping_step_remind==0) unset($order_shippings[$key]);
				}

				//오픈마켓연동정보
				if($orders['linkage_mall_code']){
					$this->load->model('openmarketmodel');
					// 설정된 판매마켓 정보
					$linkage_mallnames = array();
					$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
					
					foreach($linkage_malldata as $k => $data){
						if	($data['mall_code'] == $orders['linkage_mall_code']){
							$orders['linkage_mall_name'] = $data['mall_name'];
							break;
						}
					}
				}

				$orderexport[$order_seq]['orders']			= $orders;
				$orderexport[$order_seq]['order_shippings']	= $order_shippings;
				$orderexport[$order_seq]['shipping_policy']	= $shipping_policy;
			}
		}else{
			openDialogAlert("선택된 주문이 없습니다.",400,140,'parent',"");
			exit;
		}

		$invoice_guide_path = dirname($this->template_path()).'/_invoice_guide.html';
		$this->template->define(array('invoice_guide'=>$invoice_guide_path));

		$this->template->assign(array('cfg_order'	=> $cfg_order));
		$this->template->assign(array('orderexport'	=> $orderexport));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function batch_export(){

		$this->load->model('goodsmodel');
		$cfg_order = config_load('order');

		$this->load->helper('shipping');
		$shipping = use_shipping_method();
		if( $shipping ) foreach($shipping as $key => $data){
			if($data) $shipping_cnt[$key] = count($data);
		}
		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;

		foreach($_POST['seq'] as $k => $order_seq) $orders[$k]	= $this->ordermodel->get_order($order_seq);

		foreach($orders as $key => $order){
			if( in_array($order['step'],$this->ordermodel->able_step_action['goods_export']) ){

				$order_shippings = $this->ordermodel->get_shipping($order['order_seq']);

				foreach($order_shippings as $i=>$order_shipping){
					foreach($order_shipping['shipping_items'] as $ik=>$item){
						unset($total);
						foreach($item['shipping_item_option'] as $opt){
							$step_complete	= $this->ordermodel -> get_option_export_complete(
								$order['order_seq'],
								$order_shipping['shipping_seq'],
								$opt['order_item_seq'],
								$opt['order_item_option_seq']
							);
							$total['ea']			+= $opt['ea'];
							$total['step85']		+= $opt['step85'];
							$total['step_complete']	+= $step_complete;
							$total['step_remind']	+= $opt['ea'] - $step_complete - $opt['step85'];

							foreach($data['shipping_item_suboption'] as $sub){
								$step_complete = $this->ordermodel -> get_suboption_export_complete(
									$order['order_seq'],
									$order_shipping['shipping_seq'],
									$sub['order_item_seq'],
									$sub['order_item_suboption_seq']
								);
								$total['ea']			+= $sub['ea'];
								$total['step85']		+= $sub['step85'];
								$total['step_complete']	+= $step_complete;
								$total['step_remind']	+= $sub['ea'] - $step_complete - $sub['step85'];
							}
						}

						if	($item['goods_kind'] == 'coupon'){
							$order_shipping['kind']['coupon']['shipping_seq']	= $order_shipping['shipping_seq'];
							$order_shipping['kind']['coupon']['ea']				+= $total['ea'];
							$order_shipping['kind']['coupon']['step85']			+= $total['step85'];
							$order_shipping['kind']['coupon']['step_complete']	+= $total['step_complete'];
							$order_shipping['kind']['coupon']['step_remind']	+= $total['step_remind'];
							$order_shipping['kind']['coupon']['item_cnt']++;
							if	(!$order_shipping['kind']['coupon']['goods_name'])
								$order_shipping['kind']['coupon']['goods_name']	= $item['goods_name'];
						}else{
							$order_shipping['kind']['goods']['shipping_seq']	= $order_shipping['shipping_seq'];
							$order_shipping['kind']['goods']['ea']				+= $total['ea'];
							$order_shipping['kind']['goods']['step85']			+= $total['step85'];
							$order_shipping['kind']['goods']['step_complete']	+= $total['step_complete'];
							$order_shipping['kind']['goods']['step_remind']		+= $total['step_remind'];
							$order_shipping['kind']['goods']['item_cnt']++;
							if	(!$order_shipping['kind']['goods']['goods_name'])
								$order_shipping['kind']['goods']['goods_name']	= $item['goods_name'];
						}
					}
					if	($order_shipping['kind']['coupon']['item_cnt'] > 0){
						$coupon_cnt++;
						$order_shipping['kind_cnt']++;
						if	($order_shipping['kind']['coupon']['item_cnt'] > 1)
							$order_shipping['kind']['coupon']['goods_name']	.= '외 '.($order_shipping['kind']['coupon']['item_cnt']-1).'건';
					}
					if	($order_shipping['kind']['goods']['item_cnt'] > 0){
						$goods_cnt++;
						$order_shipping['kind_cnt']++;
						if	($order_shipping['kind']['goods']['item_cnt'] > 1)
							$order_shipping['kind']['goods']['goods_name']	.= '외 '.($order_shipping['kind']['goods']['item_cnt']-1).'건';
					}
					$order_shippings[$i] = $order_shipping;

					$exports[] = array(
						'order' => $order,
						'order_shipping' => $order_shipping
					);
				}

				/*
				$items = $this->ordermodel->get_item($order['order_seq']);
				foreach($items as $ik => $item){
					if($ik == 0) $order['goods_name'] = $item['goods_name'];
					$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
					if($options)foreach($options as $k => $data){
						$order['ea'] += $data['ea'];
						$order['step85'] += $data['step85'];
						$step_complete = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
						$order['step_complete'] += $step_complete;
						$order['step_remind'] += $data['ea'] - $step_complete - $data['step85'];
					}

					$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);
					if($suboptions)foreach($suboptions as $k => $data){
						$order['ea'] += $data['ea'];
						$order['step85'] += $data['step85'];
						$step_complete = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
						$order['step_complete'] += $step_complete;
						$order['step_remind'] += $data['ea'] - $step_complete - $data['step85'];
					}
				}
				$goods_cnt = count($items)-1;
				if( $goods_cnt > 0 ) $order['goods_name'] .= "외".$goods_cnt."건";
				$orders[$key] = $order;
				*/
			}
		}

		$smsinfo	= get_sms_remind_count();
		$this->template->assign(array('smsinfo'	=> $smsinfo));
		$this->template->assign(array('cfg_order'	=> $cfg_order));
		$this->template->assign(array('coupon_cnt'	=> $coupon_cnt));
		$this->template->assign(array('goods_cnt'	=> $goods_cnt));
		//$this->template->assign(array('orders'	=> $orders));
		$this->template->assign(array('exports'	=> $exports));
		$this->template->assign(array('shipping_policy'	=> $shipping_policy));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	//매출증빙내역
	public function sales()
	{

		$this->admin_menu();
		$this->tempate_modules();
		$this->file_path	= $this->template_path();
		$pg = config_load($this->config_system['pgCompany']);
		$pg['pgSet']	= 'no';
		if	( ($pg['mallCode'] && $pg['merchantKey']) || ($pg['mallId'] && $pg['mallPass']) )
			$pg['pgSet']	= 'ok';
		$this->template->assign('pg',$pg);

		$this->load->model('ordermodel');
		$this->load->model('salesmodel');//세금계산서/현금영수증

		if($this->config_system['webmail_admin_id'] && $this->config_system['webmail_key']){
			$this->template->assign('webmail_admin_id', $this->config_system['webmail_admin_id']);
		}else{
			$this->template->assign('webmail_admin_id', '');
		}
/*
		if($this->config_system['hiworks_request']=="Y"){
			if(isset($this->config_system['webmail_admin_id']) && isset($this->config_system['webmail_domain'])){
				$this->template->assign('webmail_admin_id', $this->config_system['webmail_admin_id']);
				$this->template->assign('webmail_domain', $this->config_system['webmail_domain']);
			}else{
				$this->load->helper("environment");
				callSetEnvironment(false);
			}
		}
*/
		/**
		 * list setting
		**/
		$sc							= $_GET;
		$sc['isAll']			= 'y';
		$sc['orderby']			= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'seq desc';
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']			= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
		if(!empty($_GET['member_seq'])) $sc['member_seq'] = $_GET['member_seq'];
		if(!empty($_GET['keyword'])) $sc['keyword'] = $_GET['keyword'];


		if (!empty($sc['typereceipt'])){
			if(gettype($sc['typereceipt']) == 'string' ) $sc['typereceipt'] = unserialize(urldecode($sc['typereceipt']));
			foreach ($sc['typereceipt'] as $v) {
				$checked['typereceipt'][$v] = "checked";
			}
		}

		if (!empty($sc['admin_type'])){
			if(gettype($sc['admin_type']) == 'string' ) $sc['admin_type'] = unserialize(urldecode($sc['admin_type']));
			foreach ($sc['admin_type'] as $v) {
				$checked['admin_type'][$v] = "checked";
			}
		}

		if (!empty($sc['ostep'])){
			if(gettype($sc['ostep']) == 'string' ) $sc['ostep'] = unserialize(urldecode($sc['ostep']));
			foreach ($sc['ostep'] as $v) {
				$checked['ostep'][$v] = "checked";
			}
		}

		if (!empty($sc['gb'])){
			if(gettype($sc['gb']) == 'string' ) $sc['gb'] = unserialize(urldecode($sc['gb']));
			foreach ($sc['gb'] as $v) {
				$checked['gb'][$v] = "checked";
			}
		}

		if (!empty($sc['type'])){
			if(gettype($sc['type']) == 'string' ) $sc['type'] = unserialize(urldecode($sc['type']));
			foreach ($sc['type'] as $v) {
				$checked['type'][$v] = "checked";
			}
		}

		if (!empty($sc['tstep'])){
			if(gettype($sc['tstep']) == 'string' ) $sc['tstep'] = unserialize(urldecode($sc['tstep']));
			foreach ($sc['tstep'] as $v) {
				$checked['tstep'][$v] = "checked";
			}
		}

		if (!empty($sc['orefund'])){
			if(gettype($sc['orefund']) == 'string' ) $sc['orefund'] = unserialize(urldecode($sc['orefund']));
			foreach ($sc['orefund'] as $v) {
				$checked['orefund'][$v] = "checked";
			}
		}


		$this->template->assign('checked',$checked);


		$data = $this->salesmodel->sales_list($sc);//게시글목록
		if(gettype($sc['type']) == 'array'){
			$_GET['type'] = urlencode(serialize($sc['type']));
		}
		if(gettype($sc['typereceipt']) == 'array'){
			$_GET['typereceipt'] = urlencode(serialize($sc['typereceipt']));
		}

		###
		$orders = config_load('order');
		$this->template->assign('orders',$orders);
		//print_r($orders);

		/**
		 * count setting
		**/
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		//$sc['totalcount']	 = $this->salesmodel->get_item_total_count($sc);

		$idx = 0;
		foreach($data['result'] as $datarow){$idx++;

			$order 			= $this->ordermodel->get_order($datarow['order_seq']);

			if($order){
				$datarow['settleprice'] = $order['settleprice'];
				$datarow['payment'] = $order['payment'];
				$datarow['order_user_name'] = $order['order_user_name'];
				$datarow['mstep']= $this->arr_step[$order['step']];
				$datarow['mpayment'] = $this->arr_payment[$order['payment']];
				$datarow['pg_transaction_number'] = $order['pg_transaction_number'];
				$datarow['cash_receipts_no'] = ($datarow['cash_no'])?$datarow['cash_no']:$order['cash_receipts_no'];
			}else{
				if($datarow['type'] == 1){//현금영수증 수동발급시
					$datarow['cash_receipts_no'] = $datarow['cash_no'];
				}
			}

			$sql = "select refund_code from fm_order_refund where order_seq = '".$datarow['order_seq']."'";
			$query = $this->db->query($sql);
			$refund = $query->result_array();
			$datarow["refund"] = $refund;

			### TAX
			$datarow['tax_msg'] = "";
			if($datarow['typereceipt'] == 1 && $datarow['hiworks_no']){
				$datarow['tax_msg'] = $this->salesmodel->hiworks_status_msg($datarow['hiworks_status']);
			}


			$datarow['tstep'] = $datarow['tstep'];
			if($datarow['tstep']=='1')
			{
				$datarow['cash_msg'] = "발급신청";
			}
			else if($datarow['tstep']=='2')
			{
				$datarow['cash_msg'] = "발급완료";
			} else if($datarow['tstep']=='3')
			{
				$datarow['cash_msg'] = "발급취소";
			} else if($datarow['tstep']=='4')
			{
				$datarow['cash_msg'] = "발급실패";
			}

			if(!($order['payment'] =='card' && $order['payment'] =='cellphone') && $order['typereceipt']>0 ) {

				if($order['typereceipt'] == 1 ) {//세금계산서
					$datarow['tax_seq'] = $datarow['seq'];
				}elseif($order['typereceipt'] == 2) {//현금영수증
					$datarow['cashreceipt_seq'] = $datarow['seq'];
					if(!$datarow['cash_receipts_no']) {
						$datarow['cash_msg'] = "발급실패";
					}
				}
			}

			if( $this->config_system['pgCompany'] == 'lg' && $order['pg_transaction_number']) {
				$datarow['authdata'] = md5($pg['mallCode'] . $order['pg_transaction_number'] . $pg['merchantKey']);
			}else{
				$datarow['authdata'] = '';
			}

			$datarow['pgPayStatus']	= false;
			if	(($datarow['payment'] =='card' || $datarow['payment'] =='cellphone') &&
					$datarow['pg_transaction_number'])
				$datarow['pgPayStatus']	= true;

			$datarow['compTaxStatus']	= false;
			if	($datarow['tstep'] == 2 && $datarow['typereceipt'] != 2)
				$datarow['compTaxStatus']	= true;

			$datarow['grayStatus']		= false;
			if	($datarow['compTaxStatus'] || $datarow['pgPayStatus'])
				$datarow['grayStatus']	= true;

			$datarow['number'] =  $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			if(filter_var($datarow['email'], FILTER_VALIDATE_EMAIL) == ""){
				$datarow['email_chk'] = 1;
			}else{
				$datarow['email_chk'] = 0;
			}

			if($before_order_seq == $datarow['order_seq']){
				if($arr_row_number[$datarow['order_seq']]){
					$arr_row_number[$datarow['order_seq']]++;
				}else{
					$arr_row_number[$datarow['order_seq']] = 2;
				}
				$datarow['row_number'] = $arr_row_number[$datarow['order_seq']];
			}

			$before_order_seq = $datarow['order_seq'];
			$salesloop[] = $datarow;
		}

		/**
		 * pagin setting
		**/
		$paginlay =  pagingtag($sc['searchcount']	,$sc['perpage'],'./sales?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('sc',$sc);
		$this->template->assign('salesloop',$salesloop);
		$this->template->assign('arr_row_number',$arr_row_number);
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function sales_favorite(){
		$this->db->where('seq', $_GET['seq']);
		$result = $this->db->update('fm_sales', array("favorite_chk"=>$_GET['status']));
		echo $result;
	}


	public function order_pg_info()
	{
		$pg = config_load($this->config_system['pgCompany']);
		$order 			= $this->ordermodel->get_order($_POST['order_seq']);
		if( $this->config_system['pgCompany'] == 'lg' ) {
			$tax_bank	= 'cr';
			/*
			if ($order['payment'] == 'bank')
			{
				$tax_bank	= 'cr';
			}
			else if ($order['payment'] == 'virtual')
			{
				$tax_bank	= 'cas';
			}else if ($order['payment'] == 'account')
			{
				$tax_bank	= 'cr';
			}
			*/

			$authdata	= md5($pg['mallCode'] . $order['pg_transaction_number'] . $pg['merchantKey']);
			$cst_platform	= 'service';//'service';
			$return = array('result'=>true,'tax_bank'=>$tax_bank,'authdata'=>$authdata,'cst_platform'=>$cst_platform);

			echo json_encode($return);
			exit;
		}else{
			$return = array('result'=>true);
			echo json_encode($return);
			exit;
		}
	}

	public function order_tax_info()
	{
		$this->load->model('salesmodel');
		if	($_POST['seq'])
			$sc['whereis']	= ' and typereceipt = 1 and seq="'.$_POST['seq'].'" ';
		else
			$sc['whereis']	= ' and typereceipt = 1 and order_seq="'.$_POST['order_seq'].'" ';
		$sc['select']		= ' * ';
		$taxitems 		= $this->salesmodel->get_data($sc);

		if($taxitems){
			if($taxitems['tstep']=='1')
			{
				$cash_msg = "발급신청";
			}
			else if($taxitems['tstep']=='2')
			{
				$cash_msg = "발급완료";
			} else if($taxitems['tstep']=='3')
			{
				$cash_msg = "발급취소";
			} else if($taxitems['tstep']=='4')
			{
				$cash_msg = "발급실패";
			} else if($taxitems['tstep']=='5')
			{
				$cash_msg = "전송완료";
			}

			if($taxitems['vat_type'] == '1'){
				$vat_msg	= '과세 세금계산서';
			}else if($taxitems['vat_type'] == '2'){
				$vat_msg	= '비과세 세금계산서';
			}

			$vat_type		= $taxitems['vat_type'];
			$cus_no			= $taxitems['cuse'];
			$order_seq		= $taxitems['order_seq'];
			$co_name		= $taxitems['co_name'];
			$co_ceo			= $taxitems['co_ceo'];
			$co_status		= $taxitems['co_status'];
			$person			= $taxitems['person'];
			$email			= $taxitems['email'];
			$phone			= $taxitems['phone'];
			$co_type		= $taxitems['co_type'];
			$busi_no		= $taxitems['busi_no'];
			$address_type	= $taxitems['address_type'];
			$address		= $taxitems['address'];
			$address_street	= $taxitems['address_street'];
			$address_detail	= $taxitems['address_detail'];
			$price			= $taxitems['tax_price'];
			$supply			= $taxitems['tax_supply'];
			$surtax			= $taxitems['tax_surtax'];
			$zipcode		= explode('-', $taxitems['zipcode']);
			$address		= $taxitems['address'];


			$return = array('result'=>true,'seq'=>$taxitems['seq'],'type'=>$taxitems['type'],'co_name'=>$co_name,'co_ceo'=>$co_ceo,'co_status'=>$co_status,'co_type'=>$co_type,'busi_no'=>$busi_no,'tax_tstep'=>$cash_msg,'tstep'=>$taxitems['tstep'],'cus_no'=>$cus_no,'order_seq'=>$order_seq,'vat_type'=>$vat_type,'address_type'=>$address_type,'address'=>$address,'address_street'=>$address_street,'address_detail'=>$address_detail,'zipcode'=>$zipcode,'person'=>$person,'email'=>$email,'phone'=>$phone,'vat_msg'=>$vat_msg,'price'=>$price,'supply'=>$supply,'surtax'=>$surtax,'view_price'=>number_format($price),'view_supply'=>number_format($supply),'view_surtax'=>number_format($surtax));
		}else{
			$return = array('result'=>false);
		}
		echo json_encode($return);
		exit;
	}

	public function order_cash_info()
	{
		$this->load->model('salesmodel');
		$seq 	= $_POST['seq'];
		$sc['whereis']	= ' and seq="'.$seq.'" ';
		$sc['select']		= ' * ';
		$taxitems 		= $this->salesmodel->get_data($sc);

		if($taxitems){
			if($taxitems['tstep']=='1')
			{
				$cash_msg = "발급신청";
			}
			else if($taxitems['tstep']=='2')
			{
				$cash_msg = "발급완료";
			} else if($taxitems['tstep']=='3')
			{
				$cash_msg = "발급취소";
			} else if($taxitems['tstep']=='4')
			{
				$cash_msg = "발급실패";
			}

			if($taxitems['cuse']=='0'){
				$taxitems['cuse'] = "개인 소득공제용";
			}else{
				$taxitems['cuse'] = "사업자지출 증빙용";
			}

			if($taxitems['order_date'] == "1970-01-01 09:00:00"){
				$taxitems['order_date'] = $taxitems['regdate'];
			}

			$dates	= date('YmdHis', strtotime($taxitems['order_date']));

			$return = array('result'=>true,'seq'=>$taxitems['seq'],'type'=>$taxitems['type'],'cuse'=>$taxitems['cuse'],'creceipt_number'=>$taxitems['creceipt_number'],'person'=>$taxitems['person'],'order_date'=>$taxitems['order_date'],'email'=>$taxitems['email'],'phone'=>$taxitems['phone'],'goodsname'=>$taxitems['goodsname'],'cash_msg'=>$cash_msg,'tstep'=>$taxitems['tstep'],'cus_no'=>$cus_no,'odates'=>$dates,'vat_type'=>$taxitems['vat_type'],'price'=>$taxitems['price'],'supply'=>$taxitems['supply'],'surtax'=>$taxitems['surtax'],'view_price'=>number_format($taxitems['price']),'view_supply'=>number_format($taxitems['supply']),'view_surtax'=>number_format($taxitems['surtax']));
		}else{
			$return = array('result'=>false);
		}
		echo json_encode($return);
		exit;
	}

	public function order_print(){
		redirect(uri_string()."s?ordarr={$_GET['ordno']}|");
	}

	public function order_prints(){
		$this->tempate_modules();

		$this->load->model('returnmodel');
		$this->load->model('membermodel');
		$this->load->model('goodsmodel');
		$this->load->model('exportmodel');

		$order_cfg = config_load('order');
		$this->template->assign($order_cfg);

		if($_POST) $_GET = $_POST;

		$file_path	= $this->template_path();

		$ordarr 	= is_array($_GET['ordarr']) ? $_GET['ordarr'] : explode("|",$_GET['ordarr']);
		//if(count($ordarr)<=1) exit;
		$ordarr = array_notnull($ordarr);

		for($i=0;$i<count($ordarr);$i++){
			$tot = array();
			$order_seq = $ordarr[$i];

			if(!$order_seq) continue;

			$orders 			= $this->ordermodel->get_order($order_seq);
			$items 				= $this->ordermodel->get_item($order_seq);

			// 다중배송지
			$order_shippings	= $this->ordermodel->get_shipping($order_seq);
			foreach($order_shippings as $order_shipping){
				$shipping_items	= $order_shipping['shipping_items'];
				if	($shipping_items)foreach($shipping_items as $itemShip){
					$itemShipping[$itemShip['item_seq']]	= $itemShip;
					$tot['add_goods_shipping']				+= $itemShip['add_goods_shipping'];
				}
				// 기본배송 지역별 추가배송비
				$tot['area_add_delivery_cost']	+= $order_shipping['area_add_delivery_cost'];
			}
			// 기본배송비
			$tot['basic_cost']			+= $order_shippings[0]['shipping_cost'] - $tot['area_add_delivery_cost'];
			$tot['shop_shipping_cost']	+= $order_shippings[0]['shipping_cost'];

			$orders['mpayment'] = $this->arr_payment[$orders['payment']];
			$orders['mstep'] 	= $this->arr_step[$orders['step']];

			$arr = config_load('bank');
			if($arr) foreach(config_load('bank') as $k => $v){
				list($tmp) = code_load('bankCode',$v['bank']);
				$v['bank'] = $tmp['value'];
				$bank[] = $v;
			}

			$Ritems = array();
			foreach($items as $key=>$item){
				
				$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
				$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);

//				$item['goods_row_cnt'] = count($options) + count($suboptions);

				if($options) foreach($options as $k => $data){
					$item['goods_row_cnt']++;
					$data['mstep']						= $this->arr_step[$data['step']];

					$data['out_supply_price']			= $data['supply_price'] * $data['ea'];
					$data['out_commission_price']		= $data['commission_price'] * $data['ea'];
					$data['out_consumer_price']			= $data['consumer_price'] * $data['ea'];
					$data['out_price']					= $data['price'] * $data['ea'];
					$data['out_refund_price']			= $data['price'] * $data['refund_ea'];

					//promotion sale
					$data['out_member_sale']			= $data['member_sale'] * $data['ea'];
					$data['out_coupon_sale']			= ($data['download_seq']) ? $data['coupon_sale'] : 0;
					$data['out_fblike_sale']			= $data['fblike_sale'];
					$data['out_mobile_sale']			= $data['mobile_sale'];
					$data['out_referer_sale']			= $data['referer_sale'];
					$data['out_promotion_code_sale']	= $data['promotion_code_sale'];

					//use
					$data['out_reserve']				= $data['reserve'] * $data['ea'];
					$data['out_point']					= $data['point'] * $data['ea'];

					$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];

					###
					$input = array();
					$sql = "SELECT * FROM fm_order_item_input WHERE order_seq = '{$order_seq}' and item_seq = '{$data[item_seq]}' and item_option_seq='{$data[item_option_seq]}'";
					$query = $this->db->query($sql);
					foreach($query->result_array() as $rows){
						$input[] = $rows;
					}
					$data['inputs'] = $input;

					$options[$k] = $data;

					$tot['ea'] += $data['ea'];
					$tot['refund_ea'] += $data['refund_ea'];
					$tot['supply_price'] += $data['out_supply_price'];
					$tot['commission_price'] += $data['out_commission_price'];
					$tot['consumer_price'] += $data['out_consumer_price'];
					$tot['oprice']	+= $data['price'];
					$tot['price'] += $data['out_price'];

					//promotion sale
					$tot['member_sale'] += $data['out_member_sale'];
					$tot['coupon_sale'] += $data['out_coupon_sale'];
					$tot['fblike_sale'] += $data['out_fblike_sale'];
					$tot['mobile_sale'] += $data['out_mobile_sale'];
					$tot['referer_sale'] += $data['referer_sale'];
					$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];

					//use sale
					$tot['reserve'] += $data['out_reserve'];
					$tot['point'] += $data['out_point'];
				}

				if($suboptions) foreach($suboptions as $k => $data){
					$item['goods_row_cnt']++;

					###
					$data['out_supply_price'] = $data['supply_price']*$data['ea'];
					$data['out_commission_price'] = $data['commission_price']*$data['ea'];
					$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
					$data['out_price'] = $data['price']*$data['ea'];
					$data['out_refund_price']			= $data['price'] * $data['refund_ea'];

					//promotion sale
					$data['out_member_sale'] = $data['member_sale']*$data['ea'];
					$data['out_coupon_sale'] = ($data['download_seq'])?$data['coupon_sale']:0;
					$data['out_fblike_sale'] = $data['fblike_sale'];
					$data['out_mobile_sale'] = $data['mobile_sale'];
					$data['out_referer_sale'] = $data['referer_sale'];
					$data['out_promotion_code_sale'] = $data['promotion_code_sale'];

					//member use
					$data['out_reserve'] = $data['reserve']*$data['ea'];
					$data['out_point'] = $data['point']*$data['ea'];

					$data['mstep']	= $this->arr_step[$data['step']];
					$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
					$suboptions[$k] = $data;

					$tot['ea'] += $data['ea'];
					$tot['refund_ea'] += $data['refund_ea'];
					$tot['supply_price'] 	+= $data['out_supply_price'];
					$tot['commission_price'] 	+= $data['out_commission_price'];
					$tot['consumer_price'] 	+= $data['out_consumer_price'];

					//promotion sale
					$tot['member_sale'] += $data['out_member_sale'];
					$tot['coupon_sale'] += $data['out_coupon_sale'];
					$tot['fblike_sale'] += $data['out_fblike_sale'];
					$tot['mobile_sale'] += $data['out_mobile_sale'];
					$tot['referer_sale'] += $data['referer_sale'];
					$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];

					//member use
					$tot['reserve'] += $data['out_reserve'];
					$tot['point']	+= $data['out_point'];

					$tot['oprice']	+= $data['price'];
					$tot['price'] 	+= $data['out_price'];
				}

				// 배송비
				$item['shippings']	= '';
				$shipping_policy	= 'shop';
				$itemShip			= $itemShipping[$item['item_seq']];
				if	($item['goods_kind'] == 'goods' && $itemShip){
					$shipping_policy						= $itemShip['shipping_policy'];
					$item['shippings']['shipping_policy']	= $itemShip['shipping_policy'];
					$item['shippings']['goods_cost']		= $itemShip['goods_shipping_cost'];
					$item['shippings']['goods_add_cost']	= $itemShip['add_goods_shipping'];
					$item['shippings']['basic_cost']		= $tot['basic_cost'];
					$item['shippings']['basic_add_cost']	= $tot['area_add_delivery_cost'];
				}

				if		($item['goods_kind'] == 'coupon')	$shipping_policy	= 'coupon';
				elseif	($item['goods_kind'] == 'gift')		$shipping_policy	= 'gift';

				if	($shipping_policy == 'goods')	$shipping_policy	= 'goods_'.$item['item_seq'];

				$item['suboptions']	= $suboptions;
				$item['options']	= $options;
				$Ritems[$shipping_policy][]	= $item;
				$Ritems[$shipping_policy][0]['shipping_row_cnt']	+= $item['goods_row_cnt'];
				$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
			}
			$items	= $Ritems;

			// 회원 정보 가져오기
			if($orders['member_seq']){
				$members = $this->membermodel->get_member_data($orders['member_seq']);
				$this->template->assign(array('members'=>$members));
			}

			// 배송방법
			$orders['mshipping'] = $this->ordermodel->get_delivery_method($orders);

			$this->load->helper('shipping');
			$shipping = use_shipping_method();
			if( $shipping ) foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
			$shipping_policy['policy'] 	= $shipping;

			/*
			$data_export = $this->exportmodel->get_export_for_order($order_seq);

			//반품정보 가져오기
			$this->load->model('returnmodel');
			$data_return = $this->returnmodel->get_return_for_order($order_seq,"return");
			if( $data_return )foreach($data_return as $k=>$data){
				$data['mstatus'] = $this->returnmodel->arr_return_status[$data['status']];
				$data_return[$k] = $data;
			}

			//교환정보 가져오기
			$data['exchange_list_ea'] = 0;
			$data_exchange = $this->returnmodel->get_return_for_order($order_seq,"exchange");
			if($data_exchange) foreach($data_exchange as $data){
				$data['mstatus'] = $this->returnmodel->arr_return_status[$data['status']];
				$data_exchange[$k] = $data;
			}

			//환불정보 가져오기
			$this->load->model('refundmodel');
			$data_refund = $this->refundmodel->get_refund_for_order($order_seq);
			if( $data_refund )foreach($data_refund as $k=>$data){
				$data['mstatus'] = $this->refundmodel->arr_refund_status[$data['status']];
				$data_refund[$k] = $data;
			}
			*/
			/*
			$this->load->model('salesmodel');
			//세금계산서 or 현금영수증
			$sc['whereis']	= ' and typereceipt = "'.$orders['typereceipt'].'" and order_seq="'.$order_seq.'" ';
			$sc['select']		= '  cash_no, tstep, seq  ';
			$sales 		= $this->salesmodel->get_data($sc);
			if( $sales ) {
				if($sales['tstep']=='1')
				{
					$cash_msg = "발급신청";
				}
				else if($sales['tstep']=='2')
				{
					$cash_msg = "발급완료";
				} else if($sales['tstep']=='3')
				{
					$cash_msg = "발급취소";
				} else if($sales['tstep']=='4')
				{
					$cash_msg = "발급실패";
				}

				if(!($orders['payment'] =='card' && $orders['payment'] =='cellphone') ) {
					 if( $orders['typereceipt'] == 2 ) {
						 $cash_receipts_no = ($sales['cash_no'])?$sales['cash_no']:$orders['cash_receipts_no'];
						if(!$cash_receipts_no) {
							$cash_msg = "발급실패";
						}
					}
				}
				//$this->template->assign(array('sales_cash_msg'	=> $cash_msg));
				$data_arr['sales_cash_msg']  = $cash_msg;
			}
			*/
			$data_arr['order']			= $orders;
			//$data_arr['data_export']	= $data_export;
			$data_arr['items']			= $items;
			$data_arr['items_tot']		= $tot;
			$data_arr['bank']			= $bank;
			/*
			$data_arr['data_return']	= $data_return;
			$data_arr['data_exchange']	= $data_exchange;
			$data_arr['data_refund']	= $data_refund;
			*/
			$data_arr['shipping_policy']= $shipping_policy;
			$data_arr['able_step_action']= $this->ordermodel->able_step_action;

			// 다중배송지
			$data_arr['order_shippings'] = $this->ordermodel->get_shipping($order_seq);

			$loop[] = $data_arr;
			/*
			$this->template->assign(array('order'	=> $orders));
			$this->template->assign(array('data_export'	=> $data_export));
			$this->template->assign(array('items'	=> $items));
			$this->template->assign(array('items_tot'	=> $tot));
			$this->template->assign(array('bank'	=> $bank));
			$this->template->assign(array('pay_log'	=> $pay_log));
			$this->template->assign(array('process_log'	=> $process_log));
			$this->template->assign(array('cancel_log'	=> $cancel_log));
			$this->template->assign(array('data_return'	=> $data_return));
			$this->template->assign(array('data_refund'	=> $data_refund));
			$this->template->assign(array('shipping_policy'	=> $shipping_policy));
			$this->template->assign(array('able_step_action' => $this->ordermodel->able_step_action));
			*/

		}
		$this->template->assign(array('loop' => $loop));
		$this->template->define(array('tpl'	=> $file_path));
		$this->template->print_("tpl");
	}


	public function download_list(){
		$this->admin_menu();
		$this->tempate_modules();

		$this->db->order_by("seq","desc");
		$this->db->where('gb', 'ORDER');
		$query = $this->db->get("fm_exceldownload");
		foreach ($query->result_array() as $row){
			$row['count'] = count(explode("|",$row['item']));
			$loop[] = $row;
		}
		$this->template->assign('loop',$loop);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function download_write(){
		$this->admin_menu();
		$this->tempate_modules();

		$this->load->model('excelordermodel');
		$itemList 	= $this->excelordermodel->itemList;
		$this->template->assign('itemList',$itemList);
		$requireds 	= $this->excelordermodel->requireds;
		$this->template->assign('requireds',$requireds);

		$items 	= $this->excelordermodel->items;
		$this->template->assign('chk_items',$items);

		$orders = $this->excelordermodel->orders;
		$this->template->assign('chk_orders',$orders);

		if(isset($_GET['seq'])){
			$seq = $_GET['seq'];
			$data = get_data("fm_exceldownload",array("seq"=>$seq));
			$item = explode("|",$data[0]['item']);
			$this->template->assign('items',$item);
		}else{
			$this->template->assign('items',$requireds);
		}
		if(!$data[0]['criteria']) $data[0]['criteria'] = "ORDER";

		$this->template->assign($data[0]);
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//결제취소 -> 환불
	public function order_refund(){
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');

		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');

		$pg = config_load($this->config_system['pgCompany']);

		$order_seq	= $_POST['order_seq'];
		$able_steps	= $this->ordermodel->able_step_action['cancel_payment'];

		$orders		= $this->ordermodel->get_order($order_seq);
		$items 		= $this->ordermodel->get_item_by_shipping($order_seq);
		$tot		= array();
		$order_total_ea = $this->ordermodel->get_order_total_ea($order_seq);

		$loop = array();

		foreach($items as $key=>$item){

			$shipping_seq = $item['shipping_seq'];
			$shipping	= $this->ordermodel->get_order_shipping($shipping_seq);
			$options 	= $this->ordermodel->get_option_for_item_by_shipping($item['item_seq'], $item['shipping_seq']);
			//$suboptions = $this->ordermodel->get_suboption_for_item_by_shipping($item['item_seq'], $item['shipping_seq']);

			if($options) foreach($options as $k=>$option){
				//$this->db->select("sum(ea) as ea");
				$options[$k]['mstep']	= $this->arr_step[$options[$k]['step']];

				$rf_ea = $this->refundmodel->get_refund_option_ea($shipping_seq,$item['item_seq'],$option['item_option_seq']);
				$step_complete = $this->ordermodel->get_option_export_complete($order_seq,$shipping_seq,$item['item_seq'],$option['item_option_seq']);

				$options[$k]['able_refund_ea'] = $option['ea'] - $rf_ea - $step_complete;

				$tot['ea'] += $option['ea'];

				//$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $option['item_option_seq']);
				$suboptions = $this->ordermodel->get_suboption_for_option_by_shipping($item['item_seq'], $option['item_option_seq'], $item['shipping_seq']);

				if($suboptions) foreach($suboptions as $k_sub=>$suboption){
					//$this->db->select("sum(ea) as ea");
					$suboptions[$k_sub]['mstep']	= $this->arr_step[$suboptions[$k_sub]['step']];

					$rf_ea = $this->refundmodel->get_refund_suboption_ea($shipping_seq,$item['item_seq'],$suboption['item_suboption_seq']);
					$step_complete = $this->ordermodel->get_suboption_export_complete($order_seq,$shipping_seq,$item['item_seq'],$suboption['item_suboption_seq']);

					$suboptions[$k_sub]['able_refund_ea'] = $suboption['ea'] - $rf_ea - $step_complete;

					$tot['ea'] += $suboption['ea'];
				}
				if($suboptions) $options[$k]['suboptions'] = $suboptions;

				$options[$k]['inputs']	= $this->ordermodel->get_input_for_option($options[$k]['item_seq'], $options[$k]['item_option_seq']);
			}

			$loop[$shipping_seq]['shipping'] = $shipping;
			$loop[$shipping_seq]['items'][$item['item_seq']] = $item;
			$loop[$shipping_seq]['items'][$item['item_seq']]['options'] = $options;
			$loop[$shipping_seq]['items'][$item['item_seq']]['suboptions'] = $suboptions;

		}

		$orders['kspay_authty']	= '1010';	// KSPAY - 신용카드
		if		($orders['payment'] == 'account')
			$orders['kspay_authty']	= '2010';	// KSPAY - 계좌이체
		elseif	($orders['payment'] == 'cellphone')
			$orders['kspay_authty']	= 'M110';	// KSPAY - 휴대폰

		$this->template->assign(array('pg'	=> $pg));
		$this->template->assign(array('orders'	=> $orders));
		$this->template->assign(array('loop'	=> $loop));
		$this->template->assign(array('items_tot'	=> $tot));
		$this->template->assign(array('order_total_ea'	=> $order_total_ea));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	// 결제취소 기타
	public function order_refund_etc(){
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');

		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');

		$pg = config_load($this->config_system['pgCompany']);

		$order_seq	= $_POST['order_seq'];
		$able_steps	= $this->ordermodel->able_step_action['cancel_payment'];

		$data_order		= $this->ordermodel->get_order($order_seq);
		$data_order['refund_price']	= (int) $this->refundmodel->get_refund_price_for_order($order_seq);

		$this->template->assign(array('pg'	=> $pg));
		$this->template->assign(array('data_order'	=> $data_order));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	//반품 or 맞교환 -> 환불
	public function order_return(){
		$this->load->model('ordermodel');
		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('goodsmodel');
		$this->load->model('exportmodel');

		$order_seq	= $_POST['order_seq'];
		$type		= $_POST['type'];
		if(!$this->arr_step)	$this->arr_step = config_load('step');
		$able_steps	= $this->ordermodel->able_step_action['return_list'];

		$orders		= $this->ordermodel->get_order($order_seq);
		$items 		= $this->ordermodel->get_item($order_seq);

		if($orders['order_phone']) $orders['order_phone'] = explode('-',$orders['order_phone']);
		if($orders['order_cellphone']) $orders['order_cellphone'] = explode('-',$orders['order_cellphone']);

		// 사유코드
		$reasons = code_load('return_reason');

		if( $_GET['mode'] == 'return_coupon' ) {
			$qry = "select * from fm_return_reason where return_type='coupon' order by idx asc";
			$query = $this->db->query($qry);
			$reasonLoop = $query -> result_array();
		}else{
			$qry = "select * from fm_return_reason where return_type!='coupon' order by idx asc";
			$query = $this->db->query($qry);
			$reasonLoop = $query -> result_array();
		}

		// 계좌설정 정보
		$bank = $payment = $escrow = "";
		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
			if( $v['accountUse'] == 'y' ) $payment['bank'] = true;
		}
		$this->template->assign(array('bank'		=> $bank));

		// 반품배송비 입금 계좌설정 정보
		$bankReturn = array();
		$arr = config_load('bank_return');
		if($arr)foreach($arr as $k=>$v){
			list($tmp) = code_load('bankCode',$v['bankReturn']);
			$v['bank'] = $tmp['value'];
			$bankReturn[] = $v;
		}
		$this->template->assign(array('bankReturn'		=> $bankReturn));

		// 출력데이터
		$loop = array();

		$cfg_order = config_load('order');

		// 출고정보
		$exports = $this->exportmodel->get_export_for_order($order_seq);
		
		//주문상품의 실제 1건당 금액계산 @2014-11-27
		foreach($items as $key=>$item){
			if ( $item['goods_kind'] != 'coupon' ) continue;
			$reOption	= array();
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$rowspan	= 0;
			if($options) foreach($options as $k => $data){
				// 매입
				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				// 정산
				$data['out_commission_price'] = $data['commission_price']*$data['ea'];				
				
				// 상품금액
				$data['out_price'] = $data['price']*$data['ea'];

				// 할인
				$data['out_member_sale'] = $data['member_sale']*$data['ea'];
				$data['out_coupon_sale'] = ($data['download_seq'])?$data['coupon_sale']:0;
				$data['out_fblike_sale'] = $data['fblike_sale'];
				$data['out_mobile_sale'] = $data['mobile_sale'];
				$data['out_promotion_code_sale'] = $data['promotion_code_sale'];
				$data['out_referer_sale'] = $data['referer_sale'];
				
				// 할인 합계
				$data['out_tot_sale'] = $data['out_member_sale'];
				$data['out_tot_sale'] += $data['out_coupon_sale'];
				$data['out_tot_sale'] += $data['out_fblike_sale'];
				$data['out_tot_sale'] += $data['out_mobile_sale'];
				$data['out_tot_sale'] += $data['out_promotion_code_sale'];
				$data['out_tot_sale'] += $data['out_referer_sale'];
				
				// 할인가격
				$data['out_sale_price'] = $data['out_price'] - $data['out_tot_sale'];
				$data['sale_price'] = $data['out_sale_price'] / $data['ea'];
				$order_one_option_sale_price[$data['item_option_seq']] = $data['sale_price'];
				
				// 예상적립
				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];

				###
				unset($data['inputs']);
				$data['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'],$data['item_option_seq']);

				$options[$k] = $data;

				$tot['ea']					+= $data['ea'];
				$tot['ready_ea']			+= $data['ready_ea'];
				$tot['step_complete']		+= $data['step_complete'];
				$tot['step25']				+= $data['step25'];
				$tot['step85']				+= $data['step85'];				
				$tot['step45']				+= $data['step45'];
				$tot['step55']				+= $data['step55'];
				$tot['step65']				+= $data['step65'];
				$tot['step75']				+= $data['step75'];
				$tot['supply_price']		+= $data['out_supply_price'];
				$tot['commission_price']	+= $data['out_commission_price'];
				$tot['consumer_price']		+= $data['out_consumer_price'];
				$tot['price']				+= $data['out_price'];

				$tot['member_sale']			+= $data['out_member_sale'];
				$tot['coupon_sale']			+= $data['out_coupon_sale'];
				$tot['fblike_sale']			+= $data['out_fblike_sale'];
				$tot['mobile_sale']			+= $data['out_mobile_sale'];
				$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];
				$tot['referer_sale']		+= $data['out_referer_sale'];

				$tot['coupon_provider']		+= $data['coupon_provider'];
				$tot['promotion_provider']	+= $data['promotion_provider'];
				$tot['referer_provider']	+= $data['referer_provider'];

				$tot['reserve']				+= $data['out_reserve'];
				$tot['point']				+= $data['out_point'];
				$tot['real_stock']			+= $real_stock;
				$tot['stock']				+= $stock;

				$return_item = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['item_option_seq']);
				$able_return_ea += (int) $data['step75'] - (int) $return_item['ea'];

				$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $data['item_option_seq']);
				if($suboptions) foreach($suboptions as $k => $subdata){
					###
					$subdata['out_supply_price']		= $subdata['supply_price']*$subdata['ea'];
					$subdata['out_commission_price']	= $subdata['commission_price']*$subdata['ea'];
					$subdata['out_consumer_price']		= $subdata['consumer_price']*$subdata['ea'];
					$subdata['out_price']				= $subdata['price']*$subdata['ea'];					
					
					// 할인
					$subdata['out_member_sale'] = $subdata['member_sale']*$data['ea'];
					$subdata['out_coupon_sale'] = ($subdata['download_seq'])?$subdata['coupon_sale']:0;
					$subdata['out_fblike_sale'] = $subdata['fblike_sale'];
					$subdata['out_mobile_sale'] = $subdata['mobile_sale'];
					$subdata['out_promotion_code_sale'] = $subdata['promotion_code_sale'];
					$subdata['out_referer_sale'] = $subdata['referer_sale'];
					
					// 할인 합계
					$subdata['out_tot_sale'] = $subdata['out_member_sale'];
					$subdata['out_tot_sale'] += $subdata['out_coupon_sale'];
					$subdata['out_tot_sale'] += $subdata['out_fblike_sale'];
					$subdata['out_tot_sale'] += $subdata['out_mobile_sale'];
					$subdata['out_tot_sale'] += $subdata['out_promotion_code_sale'];
					$subdata['out_tot_sale'] += $subdata['out_referer_sale'];
					
					// 할인가격
					$subdata['out_sale_price'] = $subdata['out_price'] - $subdata['out_tot_sale'];
					$subdata['sale_price'] = $subdata['out_sale_price'] / $subdata['ea'];
					$order_one_option_sale_price[$data['item_option_seq']] += $subdata['sale_price'];

					$subdata['out_reserve']				= $subdata['reserve']*$subdata['ea'];
					$subdata['out_point']				= $subdata['point']*$subdata['ea'];
				}
			}
		}

		foreach( $exports as $k => $data_export ){

			$loop[$k]['export'] = $data_export;
			$loop[$k]['shipping'] = $this->ordermodel->get_order_shipping($data_export['shipping_seq']);

			$data_export['item'] =  $this->exportmodel->get_export_item($data_export['export_code']);

			foreach($data_export['item'] as $i=>$data){

				if ( ($data['goods_kind'] != 'coupon' && $_GET['mode'] == 'return_coupon') || ($data['goods_kind'] == 'coupon' && $_GET['mode'] != 'return_coupon')  ) continue;//쿠폰상품 반품/맞교환 제외@2013-11-12

				$data['export_code'] = $data_export['export_code'];
				$data['reasons'] = $reasons;
				$data['reasonLoop'] = $reasonLoop;
				$data['mstep'] = $this->arr_step[$data['step']];

				//쿠폰상품의 1개의 실제 결제금액 @2014-11-27
				$coupon_real_total_price = $order_one_option_sale_price[$data['option_seq']];

				$it_s = $data['item_seq'];
				$it_ops = $data['option_seq'];

				if($data['opt_type']=='opt'){
					$return_item = $this->returnmodel->get_return_item_ea($it_s,$it_ops,$data_export['export_code']);
				}
				if($data['opt_type']=='sub'){
					$return_item = $this->returnmodel->get_return_subitem_ea($it_s,$it_ops,$data_export['export_code']);
				}

				$data['rt_ea']=$data['ea'] - $return_item['ea'];

				//쿠폰상품의 취소(환불) 가능여부
				if ( $data['goods_kind'] == 'coupon' ) {
					$coupontotal++;//쿠폰상품@2013-11-06

					$data['couponinfo'] = get_goods_coupon_view($data_export['export_code']);
					$orders['coupon_use_return'] = $data['couponinfo']['coupon_use_return'];
					$orders['order_socialcp_cancel_return_title'] = $data['couponinfo']['order_socialcp_cancel_return_title'];
					$data['socialcp_return_disabled'] = false;
					$coupon_refund_emoney = $coupon_remain_price = $coupon_deduction_price = 0;
					$coupon_remain_real_percent = $coupon_remain_real_price = $coupon_remain_price = $coupon_deduction_price = 0;

					if( $return_item['ea'] ) {//환불접수된 경우
						$data['rt_ea'] = 0;
						$data['coupon_refund_type']		= 'price';
						$data['socialcp_return_disabled'] = true;
					}else{
						if( date("Ymd")>substr(str_replace("-","",$data['social_end_date']),0,8) ) {//유효기간 종료 후 적립금환불 신청가능여부
							//$orders['socialcp_valid_coupons'] = true; 
							/**
							//관리자 : 미사용쿠폰 환불대상 불가 허용
							if( $data['socialcp_use_return'] == 1) {//미사용쿠폰 환불대상
							}else{//불가
							} 
							**/
								if( order_socialcp_cancel_return($data['socialcp_use_return'], $data['coupon_value'], $data['coupon_remain_value'], $data['social_start_date'], $data['social_end_date'] , $data['socialcp_use_emoney_day'] ) === true ) {//미사용쿠폰여부 잔여값어치합계
									if ( $data['socialcp_input_type'] == 'price' ) {//금액
									$coupon_remain_price_tmp			= (int) $data['coupon_remain_value'];
									$coupon_deduction_price_tmp	= (int) $data['coupon_value'];
									}else{//횟수
									$coupon_remain_price_tmp			= (int) (100 * ($data['coupon_input_one'] * $data['coupon_remain_value']) / 100);
									$coupon_deduction_price_tmp	= (int) ($data['coupon_input_one'] * $data['coupon_value']);
									}
									$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율
									$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);

									$coupon_remain_price			= (int) ($data['socialcp_use_emoney_percent'] * ($coupon_remain_real_price) / 100);
									$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price;
									//$cancel_total_price  += $coupon_remain_price;//취소총금액 
								}else{
									$data['socialcp_return_disabled'] = true;
								}
						}else{//유효기간 이전 
							if( $data['coupon_remain_value'] >0) {//잔여값어치가 남아있으면
								/**
								if( $data['coupon_value'] != $data['coupon_remain_value'] && $data['socialcp_cancel_use_refund'] == '1' ) {
									//부분 사용한 쿠폰은 취소(환불) 불가 @2014-10-07
									$data['rt_ea'] = 0;
									$data['coupon_refund_type']		= 'price';
									$data['socialcp_return_disabled'] = true;
							}else{
								}
								***/
									list($data['socialcp_refund_use'], $data['socialcp_refund_cancel_percent']) = order_socialcp_cancel_refund(
										$order_seq,
										$data['item_seq'],
										$orders['deposit_date'],
										$data['social_start_date'],
									$data['social_end_date'],
									$data['socialcp_cancel_payoption'],
									$data['socialcp_cancel_payoption_percent']
									);//취소(환불) 가능여부

								if( $data['coupon_value'] == $data['coupon_remain_value'] ) {//전체체크 미사용 
									$coupon_remain_price			= (int) ($data['socialcp_refund_cancel_percent'] * $coupon_real_total_price / 100);
									$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price;
									$coupon_remain_real_percent = "100";
									$coupon_remain_real_price = $coupon_real_total_price; 
									$data['coupon_refund_type']	= 'price';
									$cancel_total_price  += $coupon_remain_price;//취소총금액
								}else{//사용
									$data['coupon_refund_type']		= 'price';
									$data['socialcp_return_disabled'] = true;
									$data['rt_ea'] = 1;
									if ( $data['socialcp_input_type'] == 'price' ) {//금액
										$coupon_remain_price_tmp			= (int) $data['coupon_remain_value'];
										$coupon_deduction_price_tmp	= (int) $data['coupon_value'];
									}else{//횟수
										$coupon_remain_price_tmp			= (int) (100 * ($data['coupon_input_one'] * $data['coupon_remain_value']) / 100);
										$coupon_deduction_price_tmp	= (int) ($data['coupon_input_one'] * $data['coupon_value']);
									}
									$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율 
									//실제결제금액
									$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);

									$coupon_remain_price			= (int) ($data['socialcp_refund_cancel_percent'] * ($coupon_remain_real_price) / 100);
									$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price;  
									//$cancel_total_price  += $coupon_remain_price;//취소총금액
								}
							}else{
								$data['rt_ea'] = 0;
								$data['coupon_refund_type']		= 'price';
								$data['socialcp_return_disabled'] = true;
							}

						}

						$cancel_memo = socialcp_cancel_memo($data, $coupon_remain_real_percent, $coupon_real_total_price, $coupon_remain_real_price, $coupon_remain_price, $coupon_deduction_price); 
						//echo "유효기간전";
						//debug_var($data['socialcp_return_disabled']);
						//debug_var($data['socialcp_refund_use']);
						//debug_var($data['socialcp_refund_cancel_percent']);
						//debug_var($coupon_refund_emoney);
						//debug_var("coupon_remain_price_tmp=>".$coupon_remain_price_tmp);
						//debug_var("coupon_deduction_price_tmp=>".$coupon_deduction_price_tmp);
						//debug_var("coupon_remain_real_percent=>".$coupon_remain_real_percent);
						//debug_var("coupon_remain_real_price=>".$coupon_remain_real_price);
						//debug_var("coupon_remain_price=>".$coupon_remain_price);
						//debug_var("coupon_deduction_price=>".$coupon_deduction_price);
						//debug_var("cancel_memo=>".$cancel_memo);//exit;
					}

					//$data['coupon_refund_emoney']		= $coupon_refund_emoney;//쿠폰 잔여 값어치의 실제금액
					$data['coupon_remain_price']			= $coupon_remain_price;//쿠폰 결제금액의 실제금액
					$data['coupon_deduction_price']		= $coupon_deduction_price;//쿠폰 결제금액의 공제금액
					$data['cancel_memo']		= $cancel_memo;//쿠폰 결제금액의 공제금액
				}else{
					$goodstotal++;
				}
				//if($cfg_order['buy_confirm_use'] && $data_export['buy_confirm']!='none') $data['rt_ea'] = 0;

				//청약철회상품체크
				unset($goods);
				$goods = $this->goodsmodel->get_goods($data['goods_seq']);
				$data['cancel_type'] = $goods['cancel_type'];

				unset($data['inputs']);
				$data['inputs']	= $this->ordermodel->get_input_for_option($data['item_seq'], $data['option_seq']);

				$loop[$k]['export_item'][] = $data;
				$loop[$k]['tot_rt_ea'] += $data['rt_ea'];
			}
		}

		if ( $_GET['mode'] == 'return_coupon' ) {
			if (!$coupontotal || empty($coupontotal) ){
				$this->template->assign('backalert',true);
				$msg = "환불신청 쿠폰상품이 없습니다.!";
				echo js('alert("'.$msg.'");setTimeout(function(){closeDialog("order_refund_layer");},300);');//exit;
			}
		}elseif( !$goodstotal || empty($goodstotal) ) {
				$this->template->assign('backalert',true);
				if($_GET['mode'] == 'exchange') {
					$msg = "맞교환신청 상품이 없습니다!";
				}else{
					$msg = "반품신청 상품이 없습니다!";
				}
				echo js('alert("'.$msg.'");setTimeout(function(){closeDialog("order_refund_layer");},300);');//exit;
		}

		$this->template->assign(array('orders'		=> $orders));
		$this->template->assign(array('loop'		=> $loop));
		$this->template->assign(array('items'		=> $items));
		$this->template->assign(array('cancel_total_price'	=> $cancel_total_price));
		$file_path = $this->template_path();

		if($_GET['mode'] == 'return_coupon') {//쿠폰 환불
			$file_path = str_replace('order_return','order_return_coupon',$file_path);
		}elseif($_GET['mode'] == 'exchange') {
			$file_path = str_replace('order_return','order_exchange',$file_path);
		}

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


		###
	public function temporary(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$record = "";
		if($_GET['header_search_keyword']) {
			$_GET['keyfield'][] = 'order_seq';
			$_GET['keyword'][] = $_GET['header_search_keyword'];
		}

		// 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다.
		if( count($_GET) == 0 ){
			if($_COOKIE['order_list_search']){
				$arr = explode('&',$_COOKIE['order_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);
					if($arr2[0]!='regist_date' ){
						$key = explode('[',$arr2[0]);
						$_GET[$key[0]][ str_replace(']','',$key[1]) ] = $arr2[1];
					}else{
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
				$_GET['regist_date'][0] = date('Y-m-d');
				$_GET['regist_date'][1] = date('Y-m-d');
				$_GET['chk_step'][15] = 1;
				//$_GET['chk_step'][25] = 1;//입금확인제외
			}
		}


		### 2012-08-10
		if($_GET['mode']=='bank'){
			$_GET['regist_date'][0] = date("Y-m-d", mktime(0,0,0,date("m")-1, date("d"), date("Y")));
			$_GET['regist_date'][1] = date('Y-m-d');
			$_GET['chk_step'][15] = 1;
			$where_order[] = " ord.settleprice >= '".$_GET['sprice']."' ";
			$where_order[] = " ord.settleprice <= '".$_GET['eprice']."' ";
		}


		// 검색어
		if( $_GET['keyword'] ){
			$keyword = str_replace("'","\'",$_GET['keyword']);
			$where[] = "
			(
				(
					CONCAT_WS(' ',
						order_user_name,
						recipient_user_name,
						ifnull(depositor,' '),
						order_email,
						order_phone,
						order_cellphone,
						recipient_phone,
						recipient_cellphone,
						order_seq,
						IFNULL(userid,' ')
					) LIKE '%" . $keyword . "%'
				) OR (
					order_seq IN
					(
						SELECT order_seq FROM fm_order_item WHERE goods_name LIKE '%".$keyword."%'
					)
				)
			)
			";

		}

		// 주문일
		$date_field = $_GET['date_field'] ? $_GET['date_field'] : 'regist_date';
		if($_GET['regist_date'][0]){
			$where_order[] = "ord.".$date_field." >= '".$_GET['regist_date'][0]." 00:00:00'";
		}
		if($_GET['regist_date'][1]){
			$where_order[] = "ord.".$date_field." <= '".$_GET['regist_date'][1]." 24:00:00'";
		}

		// 주문상태
		if( $_GET['chk_step'] ){
			unset($arr);
			foreach($_GET['chk_step'] as $key => $data){
				$arr[] = "step = '".$key."'";
				if( $key == 25 ) $settle_yn = 'y';
			}
			$where_order[] = "order_seq in (select order_seq from fm_order_item_option where ".implode(' OR ',$arr).")";
		}

		//상품에서 조회
		if($_GET['goods_seq']){
			$goods_seq = str_replace("'","\'",$_GET['goods_seq']);
			$_GET['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
			$_GET['regist_date'][1] = date('Y-m-d');
			$_GET['chk_step'][75] = 1;
			$arr[] = "ord.step = '75'";
			$where_order[] = "(".implode(' OR ',$arr).")";
			$goods_seq_field = "";
			$goodsviewjoin = "LEFT JOIN fm_order_item orditm ON orditm.order_seq=ord.order_seq ";
			$where_order[]  = " orditm.goods_seq = '".$goods_seq."' ";

		}else{
			$goodsviewjoin = "";
			$goods_seq_field = "";
		}



		// 결제수단
		if( $_GET['payment'] ){
			unset($arr);
			foreach($_GET['payment'] as $key => $data){
				$arr[] = "ord.payment = '".$key."'";
				if( in_array($key,array('virtual','account')) ){
					$arr[] = "ord.payment = 'escrow_".$key."'";
				}
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}


		// 주문유형
		if( $_GET['ordertype'] ){
			unset($arr);
			foreach($_GET['ordertype'] as $key => $data){

				if($key == "personal"){
					$arr[] = " (person_seq is not null and person_seq <> '') ";
				}
				if($key == "admin"){
					$arr[] = " (admin_order is not null and admin_order <> '') ";
				}
				if($key == "change"){
					$arr[] = " (orign_order_seq is not null and orign_order_seq <> '') ";
				}
				if($key == "gift"){
					//$arr[] = "gift_cnt > 0 ";
					$arr[] = " EXISTS (select 'o' from fm_order_item where order_seq = ord.order_seq and goods_type = 'gift' limit 1) ";
				}
			}
			$where[] = "(".implode(' OR ',$arr).")";
		}

		###
		$where[] = "hidden = 'Y'";

		// 판매환경
		if( $_GET['sitetype'] ){
			unset($arr);
			foreach($_GET['sitetype'] as $key => $data){
				$arr[] = "ord.sitetype = '".$key."'";
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}

		// 유입매체
		if( $_GET['marketplace'] ){
			unset($arr);
			foreach($_GET['marketplace'] as $key => $data){
				if($key == 'etc'){
					foreach($sitemarketplaceloop as $marketplace => $tmp){
						if($marketplace != 'etc') $where_marketplace[] = "ord.marketplace != '$marketplace'";
					}
					if($where_marketplace){
						$arr[] = "(".implode(' AND ',$where_marketplace).")";
					}
					$arr[] = "ord.marketplace is null";
				}else{
					$arr[] = "ord.marketplace = '".$key."'";
				}
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}

		// 오더함
		if( $_GET['search_ordered'] ){
			$where_order[] = "(order_seq in (select order_seq from fm_order_item_option where ordered=1) OR order_seq in (select order_seq from fm_order_item_suboption where ordered=1))";
		}

		// 품절
		if( $_GET['search_runout'] ){
			$where_order[] = "(order_seq in (select order_seq from fm_order_item_option where runout=1) OR order_seq in (select order_seq from fm_order_item_suboption where runout=1))";
		}

		// 맞교환
		if( $_GET['search_change'] ){
			$where_order[] = "orign_order_seq !=''";
		}

		$this->db->order_by("seq","desc");
		$this->db->where('gb', 'ORDER');
		$query = $this->db->get("fm_exceldownload");
		foreach ($query->result_array() as $row){
			$row['count'] = count(explode("|",$row['item']));
			$loop[] = $row;
		}


		if($where_order){
			$str_where_order = " AND " . implode(' AND ',$where_order) ;
		}


		if($where){
			$str_where_order .= " and " .implode(' AND ',$where);
		}
		$sort = "ORDER BY step ASC, regist_date DESC";
		if( $where_order || $where ){
			$key = get_shop_key();
			$query = "
			SELECT
			ord.*,
			(SELECT goods_name FROM fm_order_item WHERE order_seq=ord.order_seq ORDER BY item_seq LIMIT 1) goods_name,
			(SELECT count(item_seq) FROM fm_order_item WHERE order_seq=ord.order_seq) item_cnt,
			(
				SELECT userid FROM fm_member WHERE member_seq=ord.member_seq
			) userid,
			(
				SELECT AES_DECRYPT(UNHEX(email), '{$key}') as email FROM fm_member WHERE member_seq=ord.member_seq
			) mbinfo_email,
			(
				SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq
			) group_name,
			mem.rute as mbinfo_rute,
			mem.user_name as mbinfo_user_name,
			bus.business_seq as mbinfo_business_seq,
			bus.bname as mbinfo_bname
			FROM
			fm_order ord
			".$goodsviewjoin."
			LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
			LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
			WHERE ord.step!=0  {$str_where_order} {$sort}";
			$query = $this->db->query($query,$bind);

			foreach($query->result_array() as $k => $data)
			{
				$no++;
				$data['mstep'] = $this->arr_step[$data['step']];
				$data['mpayment'] = $this->arr_payment[$data['payment']];
				$step_cnt[$data['step']]++;
				$tot_settleprice[$data['step']] += $data['settleprice'];
				$tot[$data['step']][$data['important']] += $data['settleprice'];

				$data['step_cnt'] = $step_cnt;
				$data['tot_settleprice'] = $tot_settleprice;
				$data['tot'][$data['important']] = $tot[$data['step']][$data['important']];

				$data['loop'] = $loop;

				$record[$k] = $data;
				if($step_cnt[$data['step']] == 1)
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
		}

		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign(array('record' => $record));
		$this->template->print_("tpl");
	}


	###
	public function autodeposit(){
		$this->admin_menu();
		$this->tempate_modules();

		###
		$sc = $_GET;
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'20';

		//$wtoday = date("Ymd", mktime(0,0,0,date("m")-1, date("d"), date("Y")));
		$wtoday = date("Ymd");
		$sc['sdate']			= (isset($_GET['sdate'])) ?	str_replace("-","",$_GET['sdate']) : date("Ymd");
		$sc['edate']			= (isset($_GET['edate'])) ?	str_replace("-","",$_GET['edate']) : $wtoday;

		###
		$this->load->model('usedmodel');

		$chks = $this->usedmodel->autodeposit_check();
		$this->template->assign(array('bankChk'=>$chks['chk'],'bankCount'=>$chks['count']));

		if(!$_GET){
			$temp_arr	= $_COOKIE['autodeposit_list_search'];
			parse_str($temp_arr, $cookie_arr);
			if($cookie_arr){
				$sc = array_merge($sc,$cookie_arr);
				if($cookie_arr['regist_date'] == 'today'){
					$sc['sdate'] = date('Ymd');
					$sc['edate'] = date('Ymd');
				}else if($cookie_arr['regist_date'] == '3day'){
					$sc['sdate'] = date('Ymd',strtotime("-3 day"));
					$sc['edate'] = date('Ymd');
				}else if($cookie_arr['regist_date'] == '7day'){
					$sc['sdate'] = date('Ymd',strtotime("-7 day"));
					$sc['edate'] = date('Ymd');
				}else if($cookie_arr['regist_date'] == '1mon'){
					$sc['sdate'] = date('Ymd',strtotime("-1 month"));
					$sc['edate'] = date('Ymd');
				}
			}
		}else $cookie_arr = $_GET;

		if($chks['chk']=='Y'){
			$data = $this->usedmodel->get_bank_list($sc);

			$sc['searchcount']	= $data['total'];
			$sc['total_page']	= ceil($sc['searchcount'] / $sc['perpage']);
			$sc['totalcount']	= $data['total'];

			$file_nm = end(explode("/",$file_path));
			$file_arr = explode(".",$file_nm);
			$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$file_arr[0].'?', getLinkFilter('',array_keys($sc)) );

			if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';

			$this->template->assign(array('loop' => $data['list']));
			$this->template->assign('pagin',$paginlay);
			$this->template->assign('perpage',$sc['perpage']);
			$this->template->assign('sc',$sc);
			$this->template->assign('cookie_arr',$cookie_arr);

			###
			$banks = config_load('bank_set');
			$this->template->assign('banks',$banks);
		}

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function order_settle_admin(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$template_dir = $this->template->template_dir;
		$compile_dir = $this->template->compile_dir;

		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
			if( $v['accountUse'] == 'y' ) $payment['bank'] = true;
		}

		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));
		$this->template->assign('list',$cart['list']);
		$this->template->assign('total',$cart['total']);
		$this->template->assign('bank',$bank);
		$this->template->assign('shipping_price',$cart['shipping_price']);
		$this->template->assign('shipping_company_cnt',$cart['shipping_company_cnt']);
		$this->template->assign('provider_shipping_policy',$cart['provider_shipping_policy']);
		$this->template->assign('provider_shipping_price',$cart['provider_shipping_price']);
		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('total_price',$cart['total_price']);
		$this->template->assign('member_seq',$this->userInfo['member_seq']);

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));


		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


public function order_settle_person(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$template_dir = $this->template->template_dir;
		$compile_dir = $this->template->compile_dir;


		$query = "
				select cart_seq from fm_person_cart where person_seq = '0'
		";
		$query = $this->db->query($query,$bind);

		foreach($query->result_array() as $k => $data)
		{
			//주문 안된 장바구니 삭제
			$this->db->query("delete from fm_person_cart_option where cart_seq = '".$data['cart_seq']."'");
			$this->db->query("delete from fm_person_cart_input where cart_seq = '".$data['cart_seq']."'");
			$this->db->query("delete from fm_person_cart_suboption where cart_seq = '".$data['cart_seq']."'");
			$this->db->query("delete from fm_person_cart where cart_seq = '".$data['cart_seq']."'");
		}





		$bank = $payment = $escrow = "";
		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
			if( $v['accountUse'] == 'y' ) $payment['bank'] = true;
		}
		if( $this->config_system['pgCompany'] ){
			$payment_gateway = config_load($this->config_system['pgCompany']);
			$payment_gateway['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');

			foreach($payment_gateway['arrKcpCardCompany'] as $k => $v){
				$payment_gateway['arrCardCompany'][$v['codecd']]=$v['value'];
			}

			if(isset($payment_gateway['payment'])) foreach($payment_gateway['payment'] as $k => $v){
				$payment[$v] = true;
			}

			if(isset($payment_gateway['escrow'])) foreach($payment_gateway['escrow'] as $k => $v){
				$escrow[$v] = true;
			}

			$escrow_view = true;
		}
		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));
		$this->template->assign('list',$cart['list']);
		$this->template->assign('total',$cart['total']);
		$this->template->assign('bank',$bank);
		$this->template->assign('payment',$payment);
		$this->template->assign('shipping_price',$cart['shipping_price']);
		$this->template->assign('shipping_company_cnt',$cart['shipping_company_cnt']);
		$this->template->assign('provider_shipping_policy',$cart['provider_shipping_policy']);
		$this->template->assign('provider_shipping_price',$cart['provider_shipping_price']);
		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('total_price',$cart['total_price']);
		$this->template->assign('member_seq',$this->userInfo['member_seq']);

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));


		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	public function order_admin_option(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));


		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	public function goods_select(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	public function cart()
	{
		$this->admin_menu();

		if($_GET["cart_table"] == "person"){
			$this->load->model('personcartmodel');
			$cart = $this->personcartmodel->cart_list($_GET["member_seq"]);
			// 선택 상품을 장바구니에 합쳐줌
			//$this->personcartmodel->merge_for_choice($_GET["member_seq"]);
		}else{
			$this->load->model('cartmodel');
			$cart = $this->cartmodel->cart_list("admin");
			// 선택 상품을 장바구니에 합쳐줌
			$this->cartmodel->merge_for_choice();
		}

		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));
		$this->template->assign(array('cart_table'=>$_GET["cart_table"]));
		$this->template->assign('list',$cart['list']);
		$this->template->assign('total',$cart['total']);
		$this->template->assign('shipping_price',$cart['shipping_price']);
		$this->template->assign('shipping_company_cnt',$cart['shipping_company_cnt']);
		$this->template->assign('provider_shipping_policy',$cart['provider_shipping_policy']);
		$this->template->assign('provider_shipping_price',$cart['provider_shipping_price']);
		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('total_price',$cart['total_price']);
		$this->template->assign('member_seq',$this->userInfo['member_seq']);
		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function optional_changes(){

		$cart_seq = (int) $_GET['no'];
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');

		$this->load->helper('order');

		if($_GET['cart_table'] == "person"){
			$this->load->model('personcartmodel');
			$cart = $this->personcartmodel->get_cart($cart_seq);
			$cart_options = $this->personcartmodel->get_cart_option($cart_seq);
			$cart_suboptions = $this->personcartmodel->get_cart_suboption($cart_seq);
		}else{
			$this->load->model('cartmodel');
			$cart = $this->cartmodel->get_cart($cart_seq);
			$cart_options = $this->cartmodel->get_cart_option($cart_seq);
			$cart_suboptions = $this->cartmodel->get_cart_suboption($cart_seq);
		}

		$goods_seq = $cart['goods_seq'];



		$categorys = $this->goodsmodel->get_goods_category($goods_seq);
		if($categorys) foreach($categorys as $key => $data_category){
			if( $data_category['link'] == 1 ){
				$category_code = $this->categorymodel->split_category($data_category['category_code']);
			}
		}

		$goods = $this->goodsmodel->get_goods($goods_seq);
		$options = $this->goodsmodel->get_goods_option($goods_seq);
		$suboptions = $this->goodsmodel->get_goods_suboption($goods_seq);

		foreach($cart_options as $k => $cart_opt){
			/* 이벤트 할인 * /
			$cart_opt['event'] = $this->goodsmodel->get_event_price($cart_opt['price'], $goods_seq, $category_code, $cart_opt['consumer_price'], $goods);
			if($cart_opt['event']['event_seq']) {
				if($cart_opt['event']['target_sale'] == 1 && $cart_opt['consumer_price'] > 0 ){//정가기준 할인시
					$cart_opt['price'] = ($cart_opt['consumer_price'] > $cart_opt['event']['event_sale_unit'])?$cart_opt['consumer_price'] - (int) $cart_opt['event']['event_sale_unit']:0;
				}else{
					$cart_opt['price'] = ($cart_opt['price'] > $cart_opt['event']['event_sale_unit'])?$cart_opt['price'] - (int) $cart_opt['event']['event_sale_unit']:0;
				}
			}
			*/

			$cart_options[$k] = $cart_opt;
		}

		foreach($options as $k => $opt){
			/* 이벤트 할인 * /
			$opt['event'] = $this->goodsmodel->get_event_price($opt['price'], $goods_seq, $category_code, $goods);
			if($opt['event']['event_seq']) {
				if($opt['event']['target_sale'] == 1 && $opt['consumer_price'] > 0 ){//정가기준 할인시
					$opt['price'] = ($opt['consumer_price'] > $opt['event']['event_sale_unit'])?$opt['consumer_price'] - (int) $opt['event']['event_sale_unit']:0;
				}else{
					$opt['price'] = ($opt['price'] > $opt['event']['event_sale_unit'])?$opt['price'] - (int) $opt['event']['event_sale_unit']:0;
				}
			}
			*/

			/* 대표가격 */
			if($opt['default_option'] == 'y'){
				$goods['price'] 			= $opt['price'];
				$goods['consumer_price'] 	= $opt['consumer_price'];
				$goods['reserve'] 			= $opt['reserve'];
				$goods['point'] 			= $opt['point'];
			}

			// 재고 체크
			$opt['chk_stock'] = check_stock_option($goods['goods_seq'],$opt['option1'],$opt['option2'],$opt['option3'],$opt['option4'],$opt['option5'],$opt['ea'],$cfg_order);
			$options[$k] = $opt;
		}

		if(isset($options[0]['option_divide_title'])) $goods['option_divide_title'] = $options[0]['option_divide_title'];
		$file = str_replace('optional_changes','_optional_changes',$this->template_path());
		$this->template->assign(array('cart'=>$cart));
		$this->template->assign(array('goods'=>$goods));
		$this->template->assign(array('options'=>$options));
		$this->template->assign(array('suboptions'=>$suboptions));
		$this->template->assign(array('cart_options'=>$cart_options));
		$this->template->assign(array('cart_suboptions'=>$cart_suboptions));
		$this->template->define(array('LAYOUT'=>$file));
		$this->template->print_('LAYOUT');
	}


	public function modify()
	{
		$person_table = "";
		if($_GET['cart_table'] == "person"){
			$person_table="_person";
		}
		$seq = $_GET['seq'];
		$where[] = "cart_seq=?";
		$where_val[] = $seq;
		$query = "update fm".$person_table."_cart_option set ea='".$_POST['ea'][$seq]."' where ".implode(' and ',$where);
		$this->db->query($query,$where_val);
		//pageReload('','parent');
		openDialogAlert("상품을 변경하였습니다.",400,140,'parent',"parent.cart('".$_GET['cart_table']."');");
	}


	public function optional_modify(){
		if($_POST['cart_table'] == "person"){
			$this->load->model('personcartmodel');
			$table_name = "_person";
		}else{
			$this->load->model('cartmodel');
			$table_name = "";
		}

		$cart_seq = (int) $_POST['cart_seq'];
		$this->db->where('cart_seq', $cart_seq);
		$this->db->delete('fm'.$table_name.'_cart_option');
		foreach($_POST['option_seq'] as $k => $option_seq){
			unset($insert_data);
			for($i=0;$i<5;$i++){
				if( !isset($_POST['option'][$i][$k]) || !$_POST['option'][$i][$k] ) $_POST['option'][$i][$k] = "";
				if( !isset($_POST['optionTitle'][$i][$k]) || !$_POST['optionTitle'][$i][$k] ) $_POST['optionTitle'][$i][$k] = null;
			}
			$insert_data['option1']		= $_POST['option'][0][$k];
			$insert_data['title1']		= $_POST['optionTitle'][0][$k];
			$insert_data['option2']		= $_POST['option'][1][$k];
			$insert_data['title2']		= $_POST['optionTitle'][1][$k];
			$insert_data['option3']		= $_POST['option'][2][$k];
			$insert_data['title3']		= $_POST['optionTitle'][2][$k];
			$insert_data['option4']		= $_POST['option'][3][$k];
			$insert_data['title4']		= $_POST['optionTitle'][3][$k];
			$insert_data['option5']		= $_POST['option'][4][$k];
			$insert_data['title5']		= $_POST['optionTitle'][4][$k];
			$insert_data['ea'] 			= $_POST['optionEa'][$k];
			$insert_data['cart_seq']	= $cart_seq;
			$this->db->insert('fm'.$table_name.'_cart_option', $insert_data);
			$cart_option_seq = $this->db->insert_id();
		}

		$this->db->where('cart_option_seq', $option_seq);
		$this->db->delete('fm'.$table_name.'_cart_suboption');
		foreach($_POST['suboption_seq'] as $k => $suboption_seq){
			unset($insert_data);
			$insert_data['ea']				= $_POST['suboptionEa'][$k];
			$insert_data['suboption_title']	= $_POST['suboptionTitle'][$k];
			$insert_data['suboption']		= $_POST['suboption'][$k];
			$insert_data['cart_seq'] 		= $cart_seq;
			$insert_data['cart_option_seq'] = $cart_option_seq;
			$this->db->insert('fm'.$table_name.'_cart_suboption', $insert_data);
		}
		//openDialogAlert("상품을 변경하였습니다.",400,140,'parent',"parent.location.reload();");
		openDialogAlert("상품을 변경하였습니다.",400,140,'parent',"parent.cart('".$_POST['cart_table']."'); parent.option_close();");

	}


	public function add_cart()
	{
		$member_seq = "";
		$pre_cart_seqs = "";
		$mode = "admin";
		$this->load->model('cartmodel');

		$goods_seq = (int) $_GET['goodsSeq'];
		$this->load->model('goodsmodel');
		$goods		= $this->goodsmodel->get_goods($goods_seq);
		$inputs		= $this->goodsmodel->get_goods_input($goods_seq);
		$options	= $this->goodsmodel->get_goods_default_option($goods_seq);
		if($goods['goods_status'] != 'normal'){
			if		($goods['goods_status'] == 'unsold')	$msg	= '은 판매중지 상품입니다.';
			else											$msg	= '은 품절된 상품입니다.';
			openDialogAlert($goods['goods_name'].$msg,400,140,'parent','');
			exit;
		}

		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']) $member_seq = $this->userInfo['member_seq'];

		$insert_data['goods_seq'] 	= $goods_seq;
		$insert_data['session_id'] 	= $session_id;
		$insert_data['member_seq'] 	= $member_seq;
		$insert_data['distribution'] = $mode;
		$insert_data['regist_date '] = $insert_data['update_date'] = date('Y-m-d H:i:s',time());
		$insert_data['fblike'] = 'N';

		$this->db->insert('fm_cart', $insert_data);
		$cart_seq = $this->db->insert_id();

		unset($insert_data);

		for($i=0;$i<5;$i++){
			if( !isset($options[0]["opts"][$i]) || !$options[0]["option_divide_title"][$i] ) $options[0]["opts"][$i] = "";
			if( !isset($options[0]["opts"][$i]) || !$options[0]["option_divide_title"][$i] ) $options[0]["option_divide_title"][$i] = null;
		}

		$insert_data['option1']		= $options[0]["opts"][0];
		$insert_data['title1']		= $options[0]["option_divide_title"][0];
		$insert_data['option2']		= $options[0]["opts"][1];
		$insert_data['title2']		= $options[0]["option_divide_title"][1];
		$insert_data['option3']		= $options[0]["opts"][2];
		$insert_data['title3']		= $options[0]["option_divide_title"][2];
		$insert_data['option4']		= $options[0]["opts"][3];
		$insert_data['title4']		= $options[0]["option_divide_title"][3];
		$insert_data['option5']		= $options[0]["opts"][4];
		$insert_data['title5']		= $options[0]["option_divide_title"][4];
		$insert_data['ea'] 			= 1;
		$insert_data['cart_seq']	= $cart_seq;
		$insert_data['promotion_code_seq']	= $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
		$insert_data['promotion_code_serialnumber']	= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));

		$this->db->insert('fm_cart_option', $insert_data);
		$cart_option_seq = $this->db->insert_id();

		unset($insert_data);
		if( isset($_GET['inputsValue']) ){
			$i = 0;
			foreach($inputs as $key_input => $data_input){
				if($data_input['input_form']=='file'){
					$path = ROOTPATH."data/order/";
					move_uploaded_file($_FILES['inputsValue']['tmp_name'][$i], $path.$_FILES['inputsValue']['name'][$i]);

					$insert_data['type'] 		= 'file';
					$insert_data['input_title'] = $data_input['input_name'];
					$insert_data['input_value'] = $_FILES['inputsValue']['name'][$i];
				}else{
					$insert_data['type'] 		= 'text';
					$insert_data['input_title'] = $data_input['input_name'];
					$insert_data['input_value'] = $_GET['inputsValue'][$i];
				}
				$insert_data['cart_seq'] 	= $cart_seq;
				$insert_data['cart_option_seq'] 	= $cart_option_seq;
				$this->db->insert('fm_cart_input', $insert_data);
			}
		}

		unset($insert_data);
		if( isset($_GET['suboption']) ){
			foreach($_GET['suboption'] as $k1 => $suboption){
				$insert_data['ea']				= $_GET['suboptionEa'][$k1];
				$insert_data['suboption_title']	= $_GET['suboptionTitle'][$k1];
				$insert_data['suboption']		= $suboption;
				$insert_data['cart_seq'] 		= $cart_seq;
				$insert_data['cart_option_seq'] 	= $cart_option_seq;
				$this->db->insert('fm_cart_suboption', $insert_data);
			}
		}

		// 이전에 담긴 같은상품 합치기
		$this->cartmodel->merge_for_goods($goods_seq,$cart_seq);
		/*
		if(!isset($_GET['guest']) && !$member_seq && $mode != "cart"){
			if($mode == "cart") $return_url = "/order/cart";
			else $return_url = "/order/settle?mode=".$mode;
			$url = "/member/login?return_url=" . urlencode($return_url);
			pageRedirect($url,'','parent');
			exit;
		}
		*/

		/* 상품분석 수집 */
		$this->load->model('goodslog');
		$this->goodslog->add('admin',$goods_seq);

		echo("<script>top.cart('admin');</script>");


	}


	public function cart_del()
	{
		$this->load->model('cartmodel');
		if( !isset($_GET['cart_seq']) ){
			openDialogAlert("삭제할 상품이 없습니다.",400,140,'parent',"");
			exit;
		}
		$this->db->query("delete from fm_cart_option where cart_seq = '".$_GET['cart_seq']."'");
		$this->db->query("delete from fm_cart_input where cart_seq = '".$_GET['cart_seq']."'");
		$this->db->query("delete from fm_cart_suboption where cart_seq = '".$_GET['cart_seq']."'");
		$this->db->query("delete from fm_cart where cart_seq = '".$_GET['cart_seq']."'");
		openDialogAlert("상품을 삭제하였습니다.",400,140,'parent',"parent.cart();");
	}

	public function person_cart_del()
	{
		$this->load->model('cartmodel');
		if( !isset($_GET['cart_seq']) ){
			openDialogAlert("선택된 주문이 없습니다.",400,140,'parent',"");
			exit;
		}


		$this->db->query("delete from fm_person_cart_option where cart_seq = '".$_GET['cart_seq']."'");
		$this->db->query("delete from fm_person_cart_input where cart_seq = '".$_GET['cart_seq']."'");
		$this->db->query("delete from fm_person_cart_suboption where cart_seq = '".$_GET['cart_seq']."'");
		$this->db->query("delete from fm_person_cart where cart_seq = '".$_GET['cart_seq']."'");
		openDialogAlert("상품을 삭제하였습니다.",400,140,'parent',"parent.cart('person');");
	}


	public function download_member(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$ordertype = $_GET["ordertype"];

		// 선택 상품을 장바구니에 합쳐줌
		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));
		$this->template->assign(array('ordertype'=>$ordertype));


		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");


	}


	// 회원검색리스트
	public function download_member_list()
	{
		$this->load->model('membermodel');

		### SEARCH
		$sc = $_POST;
		$sc['search_text']		= ($sc['search_text'] == '이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소') ? '':$sc['search_text'];
		$sc['orderby']		= 'A.member_seq';
		$sc['sort']				= (isset($_POST['sort'])) ?		$_POST['sort']:'desc';
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):10;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;
		$sc['groupsar']		= $coupongroupsar;

		### MEMBER
		$data = $this->membermodel->popup_member_list($sc);
		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount =  get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);
		$sc['totalcount']		= $this->membermodel->get_item_total_count();

		$idx = 0;


		foreach($data['result'] as $datarow){

			$class = ($download_coupons)?" class='bg-gray' ":"";

             if($datarow['business_seq'] != ""){
                $datarow['user_name'] = $datarow['bname'];

                $datarow['address_type'] 	= $datarow['baddress_type'];
                $datarow['address'] 		= $datarow['baddress'];
                $datarow['address_street'] 	= $datarow['baddress_street'];
                $datarow['address_detail'] = $datarow['baddress_detail'];
                $datarow['phone'] = $datarow['bphone'];
                $datarow['cellphone'] = $datarow['bcellphone'];
                $datarow['zipcode'] = $datarow['bzipcode'];
            }

			$datarow['number'] = $data['count'] - ( ( $page -1 ) * $sc['perpage'] + $i + 1 ) + 1;
			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';
			$html .= '<tr  '.$class.' >';
			if($download_coupons) {
				$html .= '	<td  class="its-td-align center"> </td>';
			}else{
				$html .= '	<td  class="its-td-align center"><span class="btn small gray"><button onclick="chkmember(this);" name="member_chk[]" seq="'.$datarow['member_seq'].'" cellphone="'.$datarow['cellphone'].'" email="'.$datarow['email'].'"  userid="'.$datarow['userid'].'"  user_name="'.$datarow['user_name'].'" phone="'.$datarow['phone'].'" class="member_chk" '.$disabled.' zipcode="'.$datarow['zipcode'].'" address_type="'.$datarow['address_type'].'" address="'.$datarow['address'].'" address_street="'.$datarow['address_street'].'" address_detail="'.$datarow['address_detail'].' "  group_name="'.$datarow['group_name'].'">선택</button></span></td>';
			}
			$html .= '	<td class="its-td-align center">'.$datarow['number'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['type'].'</td>';
			$html .= '	<td class="its-td-align center bold"><div class="bold">'.$datarow['userid'].'</div></td>';
			$html .= '	<td class="its-td-align center">'.$datarow['user_name'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['email'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['cellphone'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['phone'].'</td>';
			$html .= '</tr>';
			$i++;
		}//foreach end

		if($i==0){
			if($sc['search_text']){
				$html .= '<tr ><td class="its-td-align center" colspan="8" >"'.$sc['search_text'].'"로(으로) 검색된 회원이 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" colspan="8" >회원이 없습니다.</td></tr>';
			}
		}

		if(!empty($html)) {
			$result = array( 'content'=>$html, 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"", 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		echo json_encode($result);
		exit;
	}


	//개인결제리스트
	public function personal(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		//유입매체
		$sitemarketplaceloop = sitemarketplace($_GET['sitemarketplace'], 'image', 'array');

		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		$sc=array();
		$sc['sort']				= $_GET['sort'] ? $_GET['sort'] : 'evt.gift_seq desc';
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['keyword']			= $_GET['keyword'];
		$sc['perpage']			= $_GET['perpage'] ? $_GET['perpage'] : 10;

		// 검색어
		if( $_GET['keyword'] ){
			$keyword = str_replace("'","\'",$_GET['keyword']);
			$where[] = "
			(
				(
					CONCAT_WS(' ',
						order_user_name,
						order_email,
						order_phone,
						order_cellphone,
						order_seq,
						IFNULL(userid,' ')
					) LIKE '%" . $keyword . "%'
				) OR (
					person_seq IN
					(
						SELECT person_seq FROM fm_person_cart WHERE goods_seq IN
							(
								select goods_seq from fm_goods where goods_name like '%".$keyword."%'
							)
					)
				)
			)
			";

		}



		// 주문일
		if($_GET['regist_date'][0]){
			$where_order[] = "regist_date >= '".$_GET['regist_date'][0]." 00:00:00'";
		}

		if($_GET['regist_date'][1]){
			$where_order[] = "regist_date <= '".$_GET['regist_date'][1]." 24:00:00'";
		}


		//$this->db->order_by("person_seq","desc");

		if($where_order){
			$str_where_order = " AND " . implode(' AND ',$where_order) ;
		}

		if($where){
			$str_where = " WHERE " . implode(' AND ',$where) ;
		}


			$key = get_shop_key();
			$query = "
					select * from (
					SELECT title, order_user_name, total_price, order_seq, enuri, member_seq, order_email, order_phone, order_cellphone, person_seq, regist_date,
						(SELECT userid FROM fm_member WHERE member_seq=pr.member_seq) userid,
						(SELECT AES_DECRYPT(UNHEX(email), '{$key}') as email FROM fm_member WHERE member_seq=pr.member_seq) mbinfo_email,
						(SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=pr.member_seq) group_name,
						(select goods_name from fm_goods where goods_seq
							in (select goods_seq from fm_person_cart where person_seq = pr.person_seq) limit 1) goods_name,
						(select count(goods_seq) from fm_person_cart where person_seq = pr.person_seq) item_cnt
					FROM fm_person pr where 1=1 ".$str_where_order.") t ".$str_where. " order by person_seq desc";


			$result = select_page($sc['perpage'],$sc['page'],10,$query,array());

			foreach($result as $k => $data)
			{
				$no++;
				$record[$k] = $data;
				if($step_cnt[$data['step']] == 1)
				{
					$record[$k]['start'] = true;
					$ek = $k-1;
					if($ek >= 0 ){
						$record[$ek]['end'] = true;
					}
				}
			}


		$this->template->assign('page',$record['page']);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign(array('record' => $record['record']));
		$this->template->print_("tpl");
	}


	public function pay(){

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('personal_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$this->load->library('validation');
		$this->validation->set_rules('title', '개인 결제 타이틀','trim|required|max_length[200]|xss_clean');
		$this->validation->set_rules('order_user_name', '주문자','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('order_cellphone[]', '주문자 휴대폰','trim|required|max_length[4]|xss_clean');
		$this->validation->set_rules('order_cellphone[]', '주문자 유선전화','trim|numeric|max_length[4]|xss_clean');
		$this->validation->set_rules('order_email', '이메일','trim|required|valid_email|max_length[100]|xss_clean');
		$this->validation->set_rules('payment[]', '결제수단','trim|required|xss_clean');


		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$total_price = (int)(str_replace(",", "", $_POST['total_price_temp'])) - $_POST['enuri'];
		if($total_price < 0){
			openDialogAlert("에누리가 주문 금액보다 클 수 없습니다.",400,140,'parent','');
			exit;
		}


		$this->load->model('personcartmodel');
		$cart = $this->personcartmodel->cart_list($_POST["member_seq"], '0');

		if($cart["total"] == 0){
			openDialogAlert("주문 상품이 없습니다.",400,140,'parent','');
			exit;
		}

		foreach($cart['list'] as $key => $data) {
			$cart_options = $data['cart_options'];
			$cart_suboptions  = $data['cart_suboptions'];
			$cart_inputs  = $data['cart_inputs'];

			foreach($cart_options as $k => $cart_option){
				list($price,$opt_reserve) = $this->goodsmodel->get_goods_option_price(
					$data['goods_seq'],
					$cart_option['option1'],
					$cart_option['option2'],
					$cart_option['option3'],
					$cart_option['option4'],
					$cart_option['option5']
				);

				// 재고 체크
				$chk = check_stock_option(
					$data['goods_seq'],
					$cart_option['option1'],
					$cart_option['option2'],
					$cart_option['option3'],
					$cart_option['option4'],
					$cart_option['option5'],
					$cart_option['ea'],
					$cfg['order']
				);


				if( !$chk ){
					openDialogAlert($data['goods_name'].'의 재고가 없습니다.',400,140,'parent','');
					exit;
				}
			}

			/* 개인결제 추가구성 필수 옵션 체크 추가 2014-12-29 leewh */
			if ($data['cnt_sub_required'] > 0 && count($cart_suboptions) < 1) {
				openDialogAlert($data['goods_name'].' : '.$data['suboption_title_required'].' 옵션은 필수입니다.',400,140,'parent','');
				exit;
			}
		}

		/* 개인결제 저장 */
		//$this->db->trans_begin();
		$person_seq = $this->ordermodel->insert_order_person();


		for($i=0; $i<count($cart["list"]); $i++){
			echo "update fm_person_cart set person_seq = '".$person_seq."' where cart_seq = '".$cart["list"][$i]["cart_seq"]."'"."<br>";
			$this->db->query("update fm_person_cart set person_seq = '".$person_seq."' where cart_seq = '".$cart["list"][$i]["cart_seq"]."'");
		}

		//개인결제 SMS 발송
		if($_POST['send_sms']=='Y'){
			$cellphone	= preg_replace('/[^0-9]/', '', $_POST['cellphone']);
			if	($cellphone)
			{
				$str = trim($_POST["msg"]);
				$str = str_replace("[주문자]", $_POST["order_user_name"], $str);
				$params['msg'] = $str;
				$commonSmsData['member']['phone'] = $_POST['cellphone'];
				$commonSmsData['member']['params'] = $params;
			
				$result = commonSendSMS($commonSmsData);

				//sendSMS_Msg($str, $cellphone);
			}
		}

		openDialogAlert("개인결제가 등록되었습니다.",400,140,'parent',"top.location.replace('/admin/order/personal');");

	}

	public function person_cart()
	{
		// error_reporting(E_ALL);

		$member_seq = $_GET['member_seq'];
		$pre_cart_seqs = "";
		$mode = "cart";

		// $this->load->model('cartmodel');
		// $this->cartmodel->change_cart('person');

		$goods_seq = (int) $_GET['goodsSeq'];
		$this->load->model('goodsmodel');
		$goods		= $this->goodsmodel->get_goods($goods_seq);
		$inputs		= $this->goodsmodel->get_goods_input($goods_seq);
		$options	= $this->goodsmodel->get_goods_default_option($goods_seq);
		if($goods['goods_status'] != 'normal'){
			if		($goods['goods_status'] == 'unsold')	$msg	= '은 판매중지 상품입니다.';
			else											$msg	= '은 품절된 상품입니다.';
			openDialogAlert($goods['goods_name'].$msg,400,140,'parent','');
			exit;
		}

		$session_id = $this->session->userdata('session_id');

		$insert_data['goods_seq'] 	= $goods_seq;
		$insert_data['session_id'] 	= $session_id;
		$insert_data['member_seq'] 	= $member_seq;
		$insert_data['distribution'] = $mode;
		$insert_data['regist_date '] = $insert_data['update_date'] = date('Y-m-d H:i:s',time());
		$insert_data['fblike'] = 'N';

		$this->db->insert('fm_person_cart', $insert_data);
		$cart_seq = $this->db->insert_id();

		unset($insert_data);

		for($i=0;$i<5;$i++){
			if( !isset($options[0]["opts"][$i]) || !$options[0]["option_divide_title"][$i] ) $options[0]["opts"][$i] = "";
			if( !isset($options[0]["opts"][$i]) || !$options[0]["option_divide_title"][$i] ) $options[0]["option_divide_title"][$i] = null;
		}

		$insert_data['option1']		= $options[0]["opts"][0];
		$insert_data['title1']		= $options[0]["option_divide_title"][0];
		$insert_data['option2']		= $options[0]["opts"][1];
		$insert_data['title2']		= $options[0]["option_divide_title"][1];
		$insert_data['option3']		= $options[0]["opts"][2];
		$insert_data['title3']		= $options[0]["option_divide_title"][2];
		$insert_data['option4']		= $options[0]["opts"][3];
		$insert_data['title4']		= $options[0]["option_divide_title"][3];
		$insert_data['option5']		= $options[0]["opts"][4];
		$insert_data['title5']		= $options[0]["option_divide_title"][4];
		$insert_data['ea'] 			= 1;
		$insert_data['cart_seq']	= $cart_seq;
		$insert_data['promotion_code_seq']	= $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
		$insert_data['promotion_code_serialnumber']	= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));

		$this->db->insert('fm_person_cart_option', $insert_data);
		$cart_option_seq = $this->db->insert_id();

		unset($insert_data);
		if( isset($_GET['inputsValue']) ){
			$i = 0;
			foreach($inputs as $key_input => $data_input){
				if($data_input['input_form']=='file'){
					$path = ROOTPATH."data/order/";
					move_uploaded_file($_FILES['inputsValue']['tmp_name'][$i], $path.$_FILES['inputsValue']['name'][$i]);

					$insert_data['type'] 		= 'file';
					$insert_data['input_title'] = $data_input['input_name'];
					$insert_data['input_value'] = $_FILES['inputsValue']['name'][$i];
				}else{
					$insert_data['type'] 		= 'text';
					$insert_data['input_title'] = $data_input['input_name'];
					$insert_data['input_value'] = $_GET['inputsValue'][$i];
				}
				$insert_data['cart_seq'] 		= $cart_seq;
				$insert_data['cart_option_seq'] = $cart_option_seq;
				$this->db->insert('fm_person_cart_input', $insert_data);
			}
		}

		unset($insert_data);
		if( isset($_GET['suboption']) ){
			foreach($_GET['suboption'] as $k1 => $suboption){
				$insert_data['ea']				= $_GET['suboptionEa'][$k1];
				$insert_data['suboption_title']	= $_GET['suboptionTitle'][$k1];
				$insert_data['suboption']		= $suboption;
				$insert_data['cart_seq'] 		= $cart_seq;
				$insert_data['cart_option_seq'] = $cart_option_seq;
				$this->db->insert('fm_person_cart_suboption', $insert_data);
			}
		}

		// 이전에 담긴 같은상품 합치기
		// $this->personcartmodel->merge_for_goods($goods_seq,$cart_seq,$member_seq);

		/* 상품분석 수집 */
		$this->load->model('goodslog');
		$this->goodslog->add('admin',$goods_seq);

		echo("<script>top.cart('person');</script>");


	}


	public function calculate(){
		$this->load->helper('shipping');
		$this->load->helper('order');

		if($_GET["enuri"] != ""){
			$enuri = $_GET["enuri"];
		}

		if($_GET["member_seq"] != ""){
			$member_seq = $_GET["member_seq"];
		}

		$this->load->model('personcartmodel');
		$this->load->model('couponmodel');
		$this->load->model('membermodel');

		$this->load->model('configsalemodel');
		$cfg_reserve = ($this->reserves)?$this->reserves:config_load('reserve');

		$sc['type'] = 'mobile';
		$systemmobiles = $this->configsalemodel->lists($sc);
		$sc['type'] = 'fblike';
		$systemfblike = $this->configsalemodel->lists($sc);

		$members = "";
		$err_reserve = "";
		$total_price = 0;
		$total_reserve = 0;
		$total_point = 0;
		$goods_weight = 0;
		$sum_goods_price = 0;
		$total_coupon_sale = 0;
		$total_fblike_sale = 0;
		$total_mobile_sale = 0;
		$total_goods_price = 0;
		$total_member_sale = 0;
		$cfg['order'] = config_load('order');
		$international_shipping_price = 0;

		echo "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
		$scripts[] = "<script type='text/javascript'>";
		$scripts[] = "$(function() {";

		$shipping = use_shipping_method();
		$this->shipping_order = $shipping;

		if($shipping) $international_shipping = $shipping[1][$_POST['shipping_method_international']];


		$cart = $this->personcartmodel->cart_list($member_seq);

		### TAX : EXEMPT
		$exempt_price	= $cart['exempt_shipping'] + $cart['exempt_price'];
		if($cart['taxtype']=='mix'){
			$sum_price		= $cart['total_price']-$exempt_price;
			$tax_price		= round($sum_price/1.1);
			$cart['comm_tax_mny']	= $tax_price;
			$cart['comm_vat_mny']	= $sum_price - $tax_price;
			$cart['comm_free_mny']	= $exempt_price;
		}else if($cart['taxtype']=='exempt'){
			$cart['comm_tax_mny']	= 0;
			$cart['comm_vat_mny']	= 0;
			$cart['comm_free_mny']	= $cart['total_price'];
		}else{
			$tax_price		= round($cart['total_price']/1.1);
			$cart['comm_tax_mny']	= $tax_price;
			$cart['comm_vat_mny']	= $cart['total_price'] - $tax_price;
			$cart['comm_free_mny']	= 0;
		}
		$this->freeprice		= $cart['comm_free_mny'];
		$this->comm_tax_mny		= $cart['comm_tax_mny'];
		$this->comm_vat_mny		= $cart['comm_vat_mny'];
		//echo $cart['comm_tax_mny']." : ".$cart['comm_vat_mny']." : ".$cart['comm_free_mny'];

		// 총 판매가
		foreach($cart['list'] as $key => $data) {
			$category = array();
			$category = $data['r_category'];

			$data['ori_price'] = $data['price'];
			$cart_suboptions  = $data['cart_suboptions'];
			$cart_inputs  = $data['cart_inputs'];

			// 구매수량 체크
			$sum_opt_ea = $cart['r_goods'][$data['goods_seq']]['option_ea'];
			if($data['min_purchase_ea'] && $data['min_purchase_ea'] > $sum_opt_ea){
				openDialogAlert($data['goods_name'].'은 '.$data_goods['min_purchase_ea'].'개 이상 구매하셔야 합니다.',400,140,'parent','');
				exit;
			}
			if($data['max_purchase_ea'] && $data['max_purchase_ea'] < $sum_opt_ea){
				openDialogAlert($data['goods_name'].'은 '.$data_goods['max_purchase_ea'].'개 이상 구매하실 수 없습니다.',400,140,'parent','');
				exit;
			}

			foreach($data['cart_options'] as $k => $v){
				// 재고 체크
				$chk = check_stock_option(
						$v['goods_seq'],
						$v['option1'],
						$v['option2'],
						$v['option3'],
						$v['option4'],
						$v['option5'],
						$v['ea'],
						$cfg['order']
				);

				if( !$chk ){
					openDialogAlert($data['goods_name'].'의 재고가 없습니다.',400,140,'parent','');
					exit;
				}
			}

			if( $members && $person_seq == "") {
				/* 쿠폰할인가 계산 */
				$coupons = $this->couponmodel->get_able_use_list($members['member_seq'],$data['goods_seq'],$category, $cart['total'], $data['price'], $data['ea']);
				//debug_var(array($members['member_seq'],$data['goods_seq'],$category, $cart['total'], $data['price'], $data['ea']));
				if($coupons){
					$goods_sale = 0;
					foreach($coupons as $downloads) foreach($_POST['coupon_download'] as $cart_seq => $tmp) foreach($tmp as $cart_option_seq => $download_seq){
						if(
								$downloads['download_seq'] == $download_seq
								&& $data['cart_seq'] == $cart_seq
								&& $data['cart_option_seq'] == $cart_option_seq
						){

							if($downloads['duplication_use'] == 1){
								$goods_sale = (int) $downloads['goods_sale'] * $data['ea'];
							}else{
								$goods_sale = (int) $downloads['goods_sale'];
							}

							$goods_sale = get_price_point($goods_sale,$this->config_system);
							$data['download_seq'] = $download_seq;
						}
					}
					$data['coupon_sale'] = $goods_sale;
					$data['coupons'] = $coupons;
				}

				/* 회원할인계산 */
				if($adminOrder != "admin" && $person_seq == ""){
					$data['member_sale_unit'] = $this->membermodel->get_member_group($members['group_seq'],$data['goods_seq'],$category,$data['price'],$cart['total']);
					$data['member_sale'] = $data['member_sale_unit'] * $data['ea'];
				}
			}

			//프로모션코드 상품할인1
			if($this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')) &&  $data['promotion_code_seq'] && $data['promotion_code_serialnumber'] ) {
				$promotions = $this->promotionmodel->get_able_download_saleprice($data['promotion_code_seq'],$data['promotion_code_serialnumber'], $cart['total'], $data['price'],$data['ea']);
				$promotion_goods_sale_ck = 0;
				if($promotions['promotion_seq'] && ($promotions['sale_type'] == 'percent' || $promotions['sale_type'] == 'won' ) ) {
					$promotion_goods_sale_ck = (int) $promotions['promotioncode_sale'];
					$promotion_goods_sale_ck = get_price_point($promotion_goods_sale_ck,$this->config_system);

					$data['promotion_code_seq'] = $promotions['promotion_seq'];
					$promotion_goods_sale += $promotion_goods_sale_ck;
					$data['promotion_code_sale'] = $promotion_goods_sale_ck;
				}else{
					$data['promotion_code_seq'] = '';
					$data['promotion_code_sale'] = '';
				}
			}

			$cart_option['fblike_sale'] = 0;
			if($data['fblike'] == 'Y'){//facebook like %할인, 추가적립
				foreach($systemfblike['result'] as $fblike => $systemfblike_price) {
					if($systemfblike_price['price1']<= $cart['total'] && $systemfblike_price['price2'] >= $cart['total']){
						$opt_fblike_goods_sale = $systemfblike_price['sale_price'] * $cart_option['price'] / 100; // 좋아요 할인
						$opt_fblike_goods_sale = get_price_point($opt_fblike_goods_sale,$this->config_system);
						$data['fblike_sale_unit'] = $opt_fblike_goods_sale;
						$data['fblike_sale'] = ($opt_fblike_goods_sale * $data['ea']);
						break;
					}//endif
				}//end foreach
			}

			if($this->_is_mobile_agent) {//mobile 접속시  %할인, 추가적립 $this->mobileMode  ||
				$data['mobile_sale'] = 0;
				foreach($systemmobiles['result'] as $fblike => $systemmobiles_price) {
					if($systemmobiles_price['price1']<= $cart['total'] && $systemmobiles_price['price2'] >= $cart['total']){
						$opt_mobile_goods_sale = $systemmobiles_price['sale_price'] * $data['price'] / 100; // 모바일 할인
						$opt_mobile_goods_sale = get_price_point($opt_mobile_goods_sale,$this->config_system);
						$data['mobile_sale_unit'] = $opt_mobile_goods_sale;
						$data['mobile_sale'] = ($opt_mobile_goods_sale * $data['ea']);
						break;
					}//endif
				}//end foreach
			}

			// 적립금 계산
			$opt_price = $data['price']*$data['ea'] - (int) $data['member_sale'] - (int) $data['coupon_sale'] - (int) $data['promotion_code_sale'] - (int) $data['fblike_sale'] - (int) $data['mobile_sale'];
			$opt_price = (int) ($opt_price / $data['ea']);
			$data['sale_price'] = $opt_price;

			$data['reserve'] = 0;
			$data['point'] = 0;
			if($this->userInfo['member_seq']){
				// 구매적립,포인트
				$data['reserve'] = $this->goodsmodel->get_reserve_with_policy($data['reserve_policy'],$opt_price,$cfg_reserve['default_reserve_percent'],$data['reserve_rate'],$data['reserve_unit'],$data['reserve']);
				$data['point'] = $this->goodsmodel->get_point_with_policy($opt_price);
			}

			// 이벤트 적립금
			$data['event_reserve'] = $data['r_event']['event_reserve_unit'];
			$data['event_point'] = $data['r_event']['event_point_unit'];

			// 회원추가적립
			$data['member_reserve'] = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$opt_price,$cart['total'],$data['goods_seq'],$category);
			$data['member_point'] = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$opt_price,$cart['total'],$data['goods_seq'],$category,'point');

			// 좋아요 적립,포인트
			$opt_fblike_sale_emoney = 0;
			$opt_fblike_sale_point = 0;
			if($data['fblike'] == 'Y'){//facebook like %할인, 추가적립
				foreach($systemfblike['result'] as $fblike => $systemfblike_price) {
					if($systemfblike_price['price1']<= $cart['total'] && $systemfblike_price['price2'] >= $cart['total']){
						$data['fb_reserve'] = (int) ($systemfblike_price['sale_emoney'] * $opt_price / 100);  // 좋아요 적립
						$data['fb_point']  = (int) ($systemfblike_price['sale_point'] * $opt_price / 100);  // 좋아요 적립
						break;
					}//endif
				}//end foreach
			}

			// 모바일 적립,포인트
			$opt_mobile_sale_emoney = 0;
			$opt_mobile_sale_point = 0;
			if($this->_is_mobile_agent) {//mobile 접속시  %할인, 추가적립 $this->mobileMode  ||
				foreach($systemmobiles['result'] as $fblike => $systemmobiles_price) {
					if($systemmobiles_price['price1']<= $cart['total'] && $systemmobiles_price['price2'] >= $cart['total']){
						$data['mobile_reserve'] = (int) ($systemmobiles_price['sale_emoney'] * $opt_price / 100); // 모바일 적립
						$data['mobile_point'] = (int) ($systemmobiles_price['sale_point'] * $opt_price / 100); // 모바일 적립
						break;
					}//endif
				}//end foreach
			}

			// 적립금,포인트 로그
			$log = '';
			if( $data['reserve'] > 0 ) $log .= ($log?' / ':'').'구매  : '.(is_numeric($data['reserve'])?number_format($data['reserve']):$data['reserve']);
			if( $data['event_reserve'] > 0 ) $log .= ($log?' / ':'').'이벤트  : '.(is_numeric($data['event_reserve'])?number_format($data['event_reserve']):$data['event_reserve']);
			if( $data['member_reserve'] > 0 ) $log .= ($log?' / ':'').'회원  : '.(is_numeric($data['member_reserve'])?number_format($data['member_reserve']):$data['member_reserve']);
			if( $data['fb_reserve'] > 0 ) $log .= ($log?' / ':'').'좋아요  : '.(is_numeric($data['fb_reserve'])?number_format($data['fb_reserve']):$data['fb_reserve']);
			if( $data['mobile_reserve'] > 0 ) $log .= ($log?' / ':'').'모바일  : '.(is_numeric($data['mobile_reserve'])?number_format($data['mobile_reserve']):$data['mobile_reserve']);
			$data['reserve_log'] = $log;
			$log = '';
			if( $data['point'] > 0 ) $log .= ($log?' / ':'').'구매  : '.(is_numeric($data['point'])?number_format($data['point']):$data['point']);
			if( $data['event_point'] > 0 ) $log .= ($log?' / ':'').'이벤트  : '.(is_numeric($data['event_point'])?number_format($data['event_point']):$data['event_point']);
			if( $data['member_point'] > 0 ) $log .= ($log?' / ':'').'회원  : '.(is_numeric($data['member_point'])?number_format($data['member_point']):$data['member_point']);
			if( $data['fb_point'] > 0 ) $log .= ($log?' / ':'').'좋아요  : '.(is_numeric($data['fb_point'])?number_format($data['fb_point']):$data['fb_point']);
			if( $data['mobile_point'] > 0 ) $log .= ($log?' / ':'').'모바일  : '.(is_numeric($data['mobile_point'])?number_format($data['mobile_point']):$data['mobile_point']);
			$data['point_log'] = $log;


			// 옵션의 포인트,적립
			$data['reserve_one'] = (int) $data['reserve'] + (int) $data['event_reserve'] + (int) $data['member_reserve'] + (int) $data['fb_reserve'] + (int) $data['mobile_reserve'];
			$data['tot_reserve'] = $data['reserve_one']*$data['ea'];
			$data['point_one'] = (int) $data['point'] +  (int) $data['event_point'] + (int) $data['member_point'] + (int) $data['fb_point'] + (int) $data['mobile_point'];
			$data['tot_point'] = $data['point_one']*$data['ea'];

			if($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $k => $cart_suboption){
					// 재고 체크
					$chk = check_stock_suboption(
							$data['goods_seq'],
							$cart_suboption['suboption_title'],
							$cart_suboption['suboption'],
							$cart_suboption['ea'],
							$cfg['order']
					);
					if( !$chk ){
						openDialogAlert($data['goods_name'].'의 재고가 없습니다.',400,140,'parent','');
						exit;
					}
				}
			}

			$data['cart_sale'] = $data['mobile_sale'] + $data['fblike_sale'] + $data['promotion_code_sale'] + $data['coupon_sale'] + $data['member_sale'];
			if($this->_is_mobile_agent) {//mobile 인 경우에만적용  $this->mobileMode ||
				$scripts[] = '$("span#mobile_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['mobile_sale']).'");';
			}
			$scripts[] = '$("span#cart_option_price_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['sale_price']*$data['ea']).'");';
			$scripts[] = '$("span#fblike_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['fblike_sale']).'");';
			$scripts[] = '$("span#promotioncode_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['promotion_code_sale']).'");';
			$scripts[] = '$("span#coupon_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['coupon_sale']).'");';
			$scripts[] = '$("span#member_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['member_sale']).'");';
			$scripts[] = '$("span#cart_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['cart_sale']).'");';
			$scripts[] = '$("span#cart_reserve_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['reserve']).'");';
			$scripts[] = '$("span#cart_point_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['point']).'");';

			// 상품 무게 계산
			if( $data['shipping_weight_policy'] == "shop" ){
				$goods_weight += $international_shipping['defaultGoodsWeight'];
			}else{
				$goods_weight += $data['goods_weight'];
			}

			$data['goods_weight'] = $goods_weight * $data['ea'];
			$cart['list'][$key] = $data;

			$total_mobile_sale += (int) $data['mobile_sale'];
			$total_fblike_sale += (int) $data['fblike_sale'];
			$total_promotion_code_sale += (int) $data['promotion_code_sale'];
			$total_coupon_sale += (int) $data['coupon_sale'];
			$total_member_sale += (int) $data['member_sale'];
			$total_reserve += $data['tot_reserve'] + $data['suboption_reserve'];
			$total_point += $data['tot_point'] + $data['suboption_point'];
			$total_sale_price += $data['cart_sale'];
		}

		$cart['total_mobile_sale'] = $total_mobile_sale;
		$cart['total_fblike_sale'] = $total_fblike_sale;
		$cart['total_promotion_code_sale'] = $total_promotion_code_sale;
		$cart['total_coupon_sale'] = $total_coupon_sale;
		$cart['total_member_sale'] = $total_member_sale;
		$cart['total_reserve'] = $total_reserve;
		$cart['total_point'] = $total_point;
		$cart['total_sale_price'] = $total_sale_price;

		/* 해외 배송비 */
		$start = 0;
		if($international_shipping['goodsWeight']) foreach($international_shipping['goodsWeight'] as $key => $weight){
			$end = $weight;
			if($start < $data['goods_weight'] && $end >= $data['goods_weight'] ){
				$goods_row = $key;
			}elseif($start < $data['goods_weight'] && $end < $data['goods_weight']){//그이상의무게인경우 가장큰무게로설정
				$goods_row = $key;
			}
			$start = $weight;
		}
		$cost_key = $_POST['region'] + (count($international_shipping['region'])*$goods_row);
		$international_shipping_price = (int) $international_shipping['deliveryCost'][$cost_key];

		foreach($this->provider_goods_weight as $provider_seq => $value){
			$start = 0;
			if($international_shipping['goodsWeight']){
				foreach($international_shipping['goodsWeight'] as $key => $weight){
					if($start < $this->provider_goods_weight[$provider_seq]){
						$goods_row = $key;
						$start = $weight;
					}else{
						break;
					}
				}
			}
			$cost_key = $_POST['region'] + (count($international_shipping['region'])*$goods_row);
			$this->provider_international_shipping_price[$provider_seq] = (int) $international_shipping['deliveryCost'][$cost_key];
		}

		// 할인적용가 기준 배송비 계산
		if($cart['shop_shipping_policy']['free']){
			$cart['shipping_price']['shop'] = (int) $cart['shop_shipping_policy']['price'];
			if($cart['shop_shipping_policy']['free'] <= $total_goods_price){
				$cart['shipping_price']['shop'] = 0;
			}
		}


		/* 배송비 */
		$_POST['shipping_method'] = "delivery";
		$this->shipping_cost = 0;//기본배송비체크
		if( $_POST['international'] == 0 ){
			if( $_POST['shipping_method'] == 'delivery' ){
				// 지역별 추가 배송비
				if($shipping[0][0]['code'] == 'delivery'){
					$door2door = $shipping[0][0];
					$addDeliveryCost = 0;
					if($door2door['sigungu']) foreach($door2door['sigungu'] as $sigungu_key => $sigungu){
						if(preg_match('/'.$sigungu.'/',$_POST['recipient_address'])){
							if($addDeliveryCost == 0){
								$addDeliveryCost += $door2door['addDeliveryCost'][$sigungu_key];
							}
						}
					}

					if(count($cart["list"]) == 1 && $cart['box_ea'] > 1){
						$cart['box_ea'] = 1;
					}

					$cart['shipping_price']['shop'] += (int) $addDeliveryCost * $cart['box_ea'];
				}
				$total_shipping_price = array_sum($cart['shipping_price']);
				$this->shipping_cost = (int) $cart['shipping_price']['shop'];
			}else{
				$total_shipping_price = 0;
			}
		}else{
			$total_shipping_price = $international_shipping_price;
			$this->shipping_cost = (int) $total_shipping_price;
		}

		$shipping = use_shipping_method();



		//프로모션코드 배송비할인2
		if($this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))) {
			$shipping_promotions = $this->promotionmodel->get_able_download_saleprice($this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id')),$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')), $sum_goods_price, '','');
		}

		//프로모션코드 본사배송상품 배송비할인
		$this->shipping_promotion_code_sale=0;
		if($total_shipping_price > 0 && $shipping_promotions){//본사배송상품
			if($shipping_promotions['sale_type'] == 'shipping_free' &&  $shipping_promotions['promotioncode_shipping_sale_max']>0) {//기본배송비무료
				if($total_shipping_price < $shipping_promotions['promotioncode_shipping_sale_max']) {
					$this->shipping_promotion_code_sale = $total_shipping_price;//기본배송비무료
					$total_shipping_price = 0;
				}else{
					$this->shipping_promotion_code_sale = $shipping_promotions['promotioncode_shipping_sale_max'];
					$total_shipping_price = $total_shipping_price-$this->shipping_promotion_code_sale;
				}
			}elseif($shipping_promotions['sale_type'] == 'shipping_won' && $shipping_promotions['promotioncode_shipping_sale']>0) {//배송비할인가
				if($total_shipping_price < $shipping_promotions['promotioncode_shipping_sale']) {
					$this->shipping_promotion_code_sale = 0;//기본배송비무료
					$total_shipping_price = 0;
				}else{
					$this->shipping_promotion_code_sale = $shipping_promotions['promotioncode_shipping_sale'];
					$total_shipping_price = $total_shipping_price-$this->shipping_promotion_code_sale;
				}
			}
		}

		//배송비쿠폰 선택시 - 배송비 할인 본사배송상품만할인
		if($_POST['download_seq'] && $_POST['coupon_sale']>0 && $total_shipping_price ) {
			$this->shipping_coupon_down_seq = $_POST['download_seq'];
			if($total_shipping_price < $_POST['coupon_sale'] ){
				$this->shipping_coupon_sale = 0;
				$total_shipping_price = 0;
			}else{
				$total_shipping_price = $total_shipping_price - $_POST['coupon_sale'];
				$this->shipping_coupon_sale = $_POST['coupon_sale'];
			}
		}

		// 조건부 무료배송 금액차감
		if($shipping[0][0]['deliveryCostPolicy'] == 'ifpay'){
			if($cart['total'] && $shipping[0][0]['ifpayFreePrice']){
				if((int)$shipping[0][0]['ifpayFreePrice'] <= (int)$cart['total']){
					$this->shipping_cost = $total_shop_shipping_price = 0;
					$total_shipping_price = 0;
				}
			}
		}


		/* 총 결제금액 */
		$settle_price = $cart['total'] - $cart['total_sale_price'] + $total_shipping_price - (int)$enuri;


		/* 캐쉬 사용할 수 있는 금액 계산*/
		if( $members && isset($_POST['cash']) && $_POST['cash'] > 0 ){
			$reserve_use = true;

			if( $_POST['cash'] > $settle_price ){
				$reserve_use = false;
				$err_reserve = "최대 ".number_format($settle_price)."원까지 사용가능 합니다.";
			}

			if( $_POST['cash'] > $members['cash'] ){
				$reserve_use = false;
				$err_reserve = number_format( $members['cash'] )."원 이상 사용하실 수 없습니다.";
			}

			if($err_reserve){
				echo '<script>
				$("input[name=\'cash\']",parent.document).val(0);
				$("input[name=\'cash_view\']",parent.document).val(0);
				$(".cash_cancel_button",parent.document).addClass("hide");
				$(".cash_input_button",parent.document).removeClass("hide");
				</script>';
				openDialogAlert($err_reserve,400,140,'parent',"");
				exit;
			}
			if($_POST['cash'] > 0){
				echo '<script>
				$("#priceCashTd").removeClass("hide");
				$("#total_cash").html("'.number_format($_POST['cash']).'");
				</script>';
			}
			$cart['cash'] = (int) $_POST['cash'];
			$settle_price -= (int) $cart['cash'];
		}
		if( $members && isset($_POST['emoney']) && $_POST['emoney'] > 0 ){
			$reserve_use = true;
			$reserves = ($this->reserves)?$this->reserves:config_load('reserve');

			if( $_POST['emoney'] > $settle_price ){
				$reserve_use = false;
				$err_reserve = "최대 ".number_format($settle_price)."원까지 사용가능 합니다.";
			}

			if( $_POST['emoney'] > $members['emoney'] ){
				$reserve_use = false;
				$err_reserve = number_format( $members['emoney'] )."원 이상 사용하실 수 없습니다.";
			}

			if($reserves['emoney_use_limit'] > $members['emoney']){
				$reserve_use = false;
				$err_reserve = number_format($reserves['emoney_use_limit'])."원 이상 적립하여야 합니다.";
			}

			if($reserves['emoney_price_limit'] > $sum_goods_price){
				$reserve_use = false;
				$err_reserve = "상품을 ".number_format($reserves['emoney_price_limit'])."원 이상 사야 합니다.";
			}

			if($members['emoney'] >= $reserves['emoney_use_limit'] ){
				if($reserves['max_emoney_policy'] == 'percent_limit' && $reserves['max_emoney_percent']){
					$max_emoney = (int) ($sum_goods_price * $reserves['max_emoney_percent'] / 100);
				}else if($reserves['max_emoney_policy'] == 'price_limit' && $reserves['max_emoney']){
					$max_emoney = (int) $reserves['max_emoney'];
				}

				if($max_emoney > $settle_price) $max_emoney = $settle_price;

				if($_POST['emoney'] < $reserves['min_emoney']){
					$reserve_use = false;
					$err_reserve = "적립금은  최소 ".number_format($reserves['min_emoney'])."원부터 사용가능 합니다.";
				}
				if($_POST['emoney'] > $max_emoney && $reserves['max_emoney_policy'] != 'unlimit'){
					$reserve_use = false;
					$err_reserve = "적립금은  최대 ".number_format($max_emoney)."원까지 사용가능 합니다.";
				}
			}

			if($err_reserve){
				echo '<script>$("input[name=\'emoney\']",parent.document).val(0);</script>';
				openDialogAlert($err_reserve,400,140,'parent',"");
				exit;
			}

			$cart['emoney'] = (int) $_POST['emoney'];
			$settle_price -= (int) $cart['emoney'];
		}

		$this->amount = $settle_price;

		// 네이버 마일리지
		$this->load->model('navermileagemodel');
		$settle_price = $this->navermileagemodel->check_mileage($settle_price);

		//	- 비과세 or 복합과세 결제일 경우
		//	- 과세금액+비과세금액보다 실결제금액이 작을경우
		//	- 적립금,캐시 등의 할인이 있을경우 차액을 비과세금액부터 차감
		if( $this->freeprice && $this->comm_tax_mny+$this->comm_vat_mny+$this->freeprice > $settle_price ){
			$diff_price = abs(($this->comm_tax_mny+$this->comm_vat_mny+$this->freeprice) - $settle_price);
			if($this->freeprice > $diff_price){
				$this->freeprice = $this->freeprice - $diff_price;
			}else{
				$remain_price = ($this->comm_tax_mny+$this->comm_vat_mny) - abs($this->freeprice - $diff_price);
				$tax_price		= round($remain_price/1.1);
				$this->comm_tax_mny = $tax_price;
				$this->comm_vat_mny = $remain_price - $tax_price;
				$this->freeprice = 0;
			}
		}

		/* 상품결제가합 */

		$this->sum_goods_price = (int) $cart['total'];
		$this->settle_price = (int) $settle_price;
		$cart['total_price'] = $settle_price;
		$this->cart = $cart;

		// 적립금 합계 출력
		if($tot_reserve){
			$scripts[] = '$("#tot_reserve",parent.document).html("'.number_format($tot_reserve).'");';
		}

		$scripts[] = '$("#total_sale",parent.document).html("'.number_format($cart['total_sale_price']).'");';
		$scripts[] = '$(".settle_price",parent.document).html("'.number_format($cart['total_price']).'");';

		$scripts[] = '$("span#total_reserve",parent.document).html("'.number_format($cart['total_reserve']).'");';
		$scripts[] = '$("span#total_point",parent.document).html("'.number_format($cart['total_point']).'");';

		$scripts[] = '$("span#total_goods_price",parent.document).html("'.number_format($cart['total']).'");';

		$scripts[] = '$("span#total_coupon_sale",parent.document).html("'.number_format($cart['total_coupon_sale']).'");';

		$scripts[] = '$("span#total_fblike_sale",parent.document).html("'.number_format($cart['total_fblike_sale']).'");';
		$scripts[] = '$("span#total_mobile_sale",parent.document).html("'.number_format($cart['total_mobile_sale']).'");';
		$scripts[] = '$("span#total_shipping_price",top.document).html("'.number_format($total_shipping_price).'");';

		//@프로모션코드
		if($this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))){

			$scripts[] = '$("#cartpromotioncode",parent.document).val("'.$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')).'");';
			$scripts[] = '$(".cartpromotioncodedellay",parent.document).show();';
		}else{
			$scripts[] = '$("#cartpromotioncode",parent.document).val("");';
			$scripts[] = '$(".cartpromotioncodedellay",parent.document).hide();';
		}
		$scripts[] = '$("span#total_promotion_goods_sale",parent.document).html("'.number_format($cart['total_promotion_code_sale']).'");';

		if(isset($this->shipping_promotion_code_sale) && $this->shipping_promotion_code_sale != 0) {//배송비프로모션선택시
			$scripts[] = '$(".shipping_promotioncode_salelay",parent.document).show();';
			$scripts[] = '$("span#shipping_promotioncode_sale",parent.document).html("'.number_format($this->shipping_promotion_code_sale).'");';

			$scripts[] = '$("#shipping_promotion_code_sale",parent.document).val("'.($this->shipping_promotion_code_sale).'");';

			$scripts[] = '$("span#promotion_shipping_salse",parent.document).html("<span class=\"desc\" >(- 배송비할인 코드 '.number_format($this->shipping_promotion_code_sale).')</span>");';
			$scripts[] = '$("#shipping_promotion_code_seq",parent.document).val("'.$this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id')).'");';
		}else{
			$scripts[] = '$(".shipping_promotioncode_salelay",parent.document).hide();';
			$scripts[] = '$("span#shipping_promotioncode_sale",parent.document).html("");';

			$scripts[] = '$("#shipping_promotion_code_sale",parent.document).val("");';
			$scripts[] = '$("#shipping_promotion_code_seq",parent.document).val("");';
		}

		if(isset($this->shipping_coupon_sale)){//배송비쿠폰선택시
			$scripts[] = '$("span#coupon_shipping_price",parent.document).html("<span class=\"desc\" >(- 배송비할인 쿠폰 '.number_format($this->shipping_coupon_sale).'</span>)");';
		}else{
			$scripts[] = '$("span#coupon_shipping_price",parent.document).html("");';
			$scripts[] = '$("#download_seq",parent.document).val("");';
			$scripts[] = '$("#shipping_coupon_sale",parent.document).val("");';
		}

		/* 결제금액이 0원일 경우 결제수단을 무통장만 활성화 */
		if($settle_price==0){
			$scripts[] = '$("input[name=\'payment\'][value=\'bank\']",parent.document).attr("checked","checked").click();';
			$scripts[] = '$("input[name=\'payment\'][value!=\'bank\']",parent.document).attr("disabled","disabled");';
		}else{
			$scripts[] = '$("input[name=\'payment\'][value!=\'bank\']",parent.document).removeAttr("disabled");';
		}

		$scripts[] = "});";
		$scripts[] = "</script>";

		foreach($scripts as $script){
			echo $script."\n";
		}

	}

	public function person_view(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$person_seq = $_GET["person_seq"];

		$key = get_shop_key();
		$query = "
				select * from (
				SELECT
					title, order_user_name, total_price, order_seq, enuri, member_seq, order_email, order_phone, order_cellphone, person_seq,
					pay_type,
					(SELECT userid FROM fm_member WHERE member_seq=pr.member_seq) userid,
					(SELECT AES_DECRYPT(UNHEX(email), '{$key}') as email FROM fm_member WHERE member_seq=pr.member_seq) mbinfo_email,
					(SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=pr.member_seq) group_name,
					(select goods_name from fm_goods where goods_seq
						in (select goods_seq from fm_person_cart where person_seq = pr.person_seq) limit 1) goods_name,
					(select count(goods_seq) from fm_person_cart where person_seq = pr.person_seq) item_cnt
				FROM fm_person pr where person_seq = '".$person_seq."') t order by person_seq desc
		";

		$query = $this->db->query($query);
		$list = $query->row_array();


		$this->load->model('personcartmodel');
		$cart = $this->personcartmodel->cart_list($list["member_seq"], $list["person_seq"]);


		if($list["order_seq"] != ""){

			$query = "select B.* from fm_order A INNER JOIN fm_order_shipping B ON A.order_seq = B.order_seq where A.order_seq = '".$list["order_seq"]."'";

			$query = $this->db->query($query);
			$orderData = $query->row_array();

		}


		$orderData['mpayment'] = $this->arr_payment[$orderData['payment']];
		$arr_pay_type = explode('|',$list[pay_type]);
		foreach($arr_pay_type as $pay_type){
			$pay_types[] = $this->arr_payment[$pay_type];
		}


		if($orderData['settleprice'] == ""){
			$orderData['settleprice'] = $list["total_price"] - $list["enuri"];
		}

		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));
		$this->template->assign(array('cart_table'=>$_GET["cart_table"]));
		$this->template->assign('total',$cart['total']);
		$this->template->assign('shipping_price',$cart['shipping_price']);
		$this->template->assign('shipping_company_cnt',$cart['shipping_company_cnt']);
		$this->template->assign('provider_shipping_policy',$cart['provider_shipping_policy']);
		$this->template->assign('provider_shipping_price',$cart['provider_shipping_price']);
		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('total_price',$cart['total_price']);
		$this->template->assign('member_seq',$this->userInfo['member_seq']);
		$this->template->assign('pay_types',$pay_types);

		$this->template->assign('list',$cart['list']);
		$this->template->assign(array('record' => $list));
		$this->template->assign(array('orderData' => $orderData));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	public function personal_del(){

		if( !isset($_POST['person_seq']) ){
			openDialogAlert("삭제할 상품이 없습니다.",400,140,'parent',"");
			exit;
		}

		for($i=0; $i<count($_POST['person_seq']); $i++){

			$query = "
					select cart_seq from fm_person_cart where person_seq = '".$_POST['person_seq'][$i]."'
			";
			$query = $this->db->query($query);
			$cart = $query->row_array();

			$this->db->query("delete from fm_person_cart_option where cart_seq = '".$cart['cart_seq']."'");
			$this->db->query("delete from fm_person_cart_input where cart_seq = '".$cart['cart_seq']."'");
			$this->db->query("delete from fm_person_cart_suboption where cart_seq = '".$cart['cart_seq']."'");
			$this->db->query("delete from fm_person_cart where cart_seq = '".$cart['cart_seq']."'");
			$this->db->query("delete from fm_person_cart where cart_seq = '".$cart['cart_seq']."'");
			$this->db->query("delete from fm_person where person_seq = '".$_POST['person_seq'][$i]."'");

		}

		openDialogAlert("삭제되었습니다.",400,140,'parent',"parent.document.location.reload();");
	}

	public function catalog_ajax(){

		$this->load->model('refundmodel');
		$this->load->model('returnmodel');
		$this->load->model('ordermodel');
		$this->load->model('openmarketmodel');
		$_PARAM			= $_POST;//$_GET//$_POST

		// 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다.
		// page, bfStep, nnum이 기본으로 넘어온다.
		if( count($_PARAM) < 4 ){
			if($_COOKIE['order_list_search']){
				$arr = explode('&',$_COOKIE['order_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);
					if($arr2[0]!='regist_date' ){
						$key = explode('[',$arr2[0]);
						$_PARAM[$key[0]][ str_replace(']','',$key[1]) ] = $arr2[1];
					}else{
						if($arr2[1] == 'today'){
							$_PARAM['regist_date'][0] = date('Y-m-d');
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3day'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-3 day"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '7day'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-7 day"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '1mon'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3mon'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-3 month"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == 'all'){
							$_PARAM['regist_date'][0] = '';
							$_PARAM['regist_date'][1] = '';
						}
						$_PARAM['regist_date_type'] = $arr2[1];
					}
				}
			}else{
				// 기본세팅이 없는경우 오늘의 입금확인 주문접수 주문을 검색조건으로 합니다.
				$_PARAM['regist_date'][0] = date('Y-m-d');
				$_PARAM['regist_date'][1] = date('Y-m-d');
				$_PARAM['chk_step'][15] = 1;
				$_PARAM['chk_step'][25] = 1;
			}
		}

		$page			= (trim($_PARAM['page'])) ? trim($_PARAM['page']) : 1;
		$bfStep			= trim($_PARAM['bfStep']);
		$no				= trim($_PARAM['nnum']);

		// 양식 데이터
		$this->db->order_by("seq","desc");
		$this->db->where('gb', 'ORDER');
		$query = $this->db->get("fm_exceldownload");
		foreach ($query->result_array() as $row){
			$row['count'] = count(explode("|",$row['item']));
			$loop[] = $row;
		}

		$query	= $this->ordermodel->get_order_catalog_query($_PARAM);
		if	($query){

			if	($page == 1){
				$_PARAM['query_type']	= 'total_record';
				$totalQuery				= $this->ordermodel->get_order_catalog_query($_PARAM);
				$totalData				= $totalQuery->result_array();
				$no						= $totalData[0]['cnt'];
			}

			foreach($query->result_array() as $k => $data){

				$data['mstep'] = $this->arr_step[$data['step']];

				$data['sitetypetitle']		= sitetype($data['sitetype'], 'image', '');//판매환경
				$data['marketplacetitle']	= sitemarketplace($data['marketplace'], 'image', '');//유입매체

				$tmp = explode(' ',$data['bank_account']);
				$data['bankname'] = $tmp[0];

				###
				//$data['opt_cnt']	= $this->ordermodel->get_option_count('opt', $data['order_seq']);
				//$data['gift_cnt']	= $this->ordermodel->get_option_count('gift', $data['order_seq']);
				$data['gift_nm']	= $this->ordermodel->get_gift_name($data['order_seq']);
				$data['tot_ea']	= $data['opt_ea']+$data['sub_ea'];

				//반품정보 가져오기
				$data['return_list_ea'] = 0;
				$data_return = $this->returnmodel->get_return_for_order($data['order_seq'],"return");
				if($data_return) foreach($data_return as $row_return){
					$data['return_list_ea'] += $row_return['ea'];
				}

				//교환정보 가져오기
				$data['exchange_list_ea'] = 0;
				$data_exchange = $this->returnmodel->get_return_for_order($data['order_seq'],"exchange");
				if($data_exchange) foreach($data_exchange as $row_exchange){
					$data['exchange_list_ea'] += $row_exchange['ea'];
				}

				//환불정보 가져오기
				$data['refund_list_ea'] = 0;
				$data['cancel_list_ea'] = 0;
				$data_refund = $this->refundmodel->get_refund_for_order($data['order_seq']);
				if($data_refund) foreach($data_refund as $row_refund){
					if( $row_refund['refund_type'] == 'cancel_payment' ){
						$data['cancel_list_ea'] += $row_refund['ea'];
					}else{
						$data['refund_list_ea'] += $row_refund['ea'];
					}
				}

				$data['mpayment'] = $this->arr_payment[$data['payment']];
				$step_cnt[$data['step']]++;
				$tot_settleprice[$data['step']] += $data['settleprice'];
				$tot[$data['step']][$data['important']] += $data['settleprice'];

				$data['step_cnt'] = $step_cnt;
				$data['tot_settleprice'] = $tot_settleprice;
				$data['tot'][$data['important']] = $tot[$data['step']][$data['important']];

				if($data['member_seq']){
					$data['member_type']	= $data['mbinfo_business_seq'] ? '기업' : '개인';
				}

				$data['loop']	= $loop;
				$data['no']		= $no;

				if	($data['payment'] == 'bank' && $data['bank_account']){
					$bank_tmp			= explode(' ', $data['bank_account']);
					$bank_name			= str_replace('은행', '', $bank_tmp[0]);
					$data['bank_name']	= $bank_name;
				}

				if ($data['deposit_date']=="0000-00-00 00:00:00") $data['deposit_date'] = "";

				if	($_PARAM['stepBox'][$data['step']]){
					if		($_PARAM['stepBox'][$data['step']] == 'select'){
						$data['thischeck']	= true;
					}elseif	($_PARAM['stepBox'][$data['step']] == 'important'){
						if	($data['important'])
							$data['thischeck']	= true;
					}elseif	($_PARAM['stepBox'][$data['step']] == 'not-important'){
						if	(!$data['important'])
							$data['thischeck']	= true;
					}
				}

				## 시작점과 종료점
				if	($bfStep != $data['step']){
					$data['start_step']	= $data['step'];
					if	($bfStep){
						$record[$k]['end_step']			= $bfStep;
						$_PARAM['query_type']			= 'summary';
						$_PARAM['end_step']				= $bfStep;
						$summary_query					= $this->ordermodel->get_order_catalog_query($_PARAM);
						$endData						= $summary_query->result_array();
						$data['end_mstep']				= $this->arr_step[$bfStep];
						$data['end_step']				= $bfStep;
						$data['end_step_cnt']			= $endData[0]['cnt'];
						$data['end_step_settleprice']	= $endData[0]['total_settleprice'];
					}
					$bfStep	= $data['step'];
				}

				if	($no == 1){
					$_PARAM['query_type']			= 'summary';
					$_PARAM['end_step']				= $data['step'];
					$summary_query					= $this->ordermodel->get_order_catalog_query($_PARAM);
					$endData						= $summary_query->result_array();
					$data['last_step']				= $data['step'];
					$data['last_step_cnt']			= $endData[0]['cnt'];
					$data['last_step_settleprice']	= $endData[0]['total_settleprice'];
				}

				$record[$k] = $data;
				$final_step	= $data['step'];

				$no--;
			}
		}

		//오픈마켓연동정보
		if	($this->openmarketmodel->chk_linkage_service()){
			$linkage = $this->openmarketmodel->get_linkage_config();
			if($linkage){
				// 설정된 판매마켓 정보
				$linkage_mallnames = array();
				$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
				foreach($linkage_malldata as $k => $data){
					if	($data['default_yn'] == 'Y'){
						$linkage_mallnames[$data['mall_code']]	= preg_replace("/\(.*\)/","",$data['mall_name']);
					}
				}
				$this->template->assign('linkage_mallnames',$linkage_mallnames);
			}
		}

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign(array('stepBox' => $_PARAM['stepBox']));
		$this->template->assign(array('page' => $page));
		$this->template->assign(array('final_no' => $no));
		$this->template->assign(array('final_step' => $final_step));
		$this->template->assign(array('record' => $record));
		$this->template->assign(array('able_step_action' => $this->ordermodel->able_step_action));
		$this->template->print_("tpl");
	}

	public function order_barcode_image(){
		$order_seq = $_GET['order_seq'];

		$this->load->library('Barcode39');
		$bc = new Barcode39("A{$order_seq}A");
		$bc->barcode_text = true;
		$bc->barcode_bar_thick = 3;
		$bc->barcode_bar_thin = 1;
		$bc->barcode_height = 45;
		$bc->barcode_padding = 0;
		$bc->draw();

	}

	public function order_goods_barcode_image(){
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

	public function order_seq_chk(){
		$order_seq = preg_replace("/[^0-9]/i","",$_GET['order_seq']);
		$query = $this->db->query("select count(*) as cnt from fm_order where order_seq=?",$order_seq);
		$result = $query->row_array();
		echo $result['cnt'] ? '1' : '0';
	}

	// 상품준비 처리 팝업
	public function goods_ready(){
		$order_seq	= trim($_GET['seq']);

		$orders 	= $this->ordermodel->get_order($order_seq);
		$items 		= $this->ordermodel->get_item($order_seq);
		if	($items)foreach($items as $i => $item){
			$loop[$i]	= $item;

			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			if	($options)foreach($options as $o => $opt){
				$opt['mstep']				= $this->arr_step[$opt['step']];
				$loop[$i]['options'][$o]	= $opt;

				$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $opt['item_option_seq']);
				if	($suboptions)foreach($suboptions as $s => $sub){
					$sub['mstep']				= $this->arr_step[$sub['step']];
					$loop[$i]['options'][$o]['suboptions'][$s]	= $sub;
				}
			}
		}

		$file_path	= $this->template_path();
		$this->template->assign(array('orders' => $orders));
		$this->template->assign(array('loop' => $loop));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function person_order(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file order.php */
/* Location: ./app/controllers/admin/order.php */