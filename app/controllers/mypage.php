<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/board".EXT);

class mypage extends board {
	function __construct() {
		parent::__construct();
		$this->load->library('snssocial');
		$this->load->model('membermodel');
		$this->load->helper('member');
		$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보
		$this->template->assign('mypoint',$this->mdata['point']);

		$this->template->assign('myinfo_sns_f',$this->mdata['sns_f']);
		$this->joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		unset($joinform['use_y']);//폐지@2013-04-29
		unset($joinform['use_m']);//폐지@2014-07-01
		$this->template->assign('joinform',$this->joinform);

		$this->load->helper('order');

		// 상.하위 등급 정보 추출
		$sc['group_seq']	= $this->mdata["group_seq"];
		$group				= $this->membermodel->get_member_group_flow($sc);
		$currentGroup		= $group['currentGroup'];
		$nextGroup			= $group['nextGroup'];

		// 소멸 예정 쿠폰
		$sc['member_seq']	= $this->userInfo['member_seq'];
		$extinction			= $this->membermodel->get_extinction($sc);

		$configReserve_menu = config_load('reserve');
		$this->template->assign(array('emoney_exchange_use'=>$configReserve_menu["emoney_exchange_use"]));
		$this->template->assign(array('myicon'=>$currentGroup["myicon"]));
		$this->template->assign(array('member_group'=>$currentGroup));
		$this->template->assign(array('next_group'=>$nextGroup));
		$this->template->assign(array('extinction'=>$extinction));

		//포인트교환사용여부
		$this->template->assign(array('emoney_exchange_use'=>$this->isplusfreenot["isemoney_exchange"]));

		/**
		** @ board start
		**/
		$this->myqnatbl					= 'mbqna';//1:1문의
		$this->mygdqnatbl				= 'goods_qna';//상품문의
		$this->mygdreviewtbl			= 'goods_review';//상품후기

		$this->myqna->boardurl->resets		= '/mypage/myqna_catalog?';
		$this->myqna->boardurl->lists			= '/mypage/myqna_catalog?';
		$this->myqna->boardurl->view			= '/mypage/myqna_view?seq=';
		$this->myqna->boardurl->write			= '/mypage/myqna_write?';
		$this->myqna->boardurl->modify		= $this->myqna->boardurl->write.'&seq=';
		$this->myqna->boardurl->reply		= $this->myqna->boardurl->write.'&reply=y&seq=';
		$this->myqna->boardurl->goodsview		= '/goods/view?no=';						//상품접근

		$this->mygdqna->boardurl->resets	= '/mypage/mygdqna_catalog?';
		$this->mygdqna->boardurl->lists		= '/mypage/mygdqna_catalog?';
		$this->mygdqna->boardurl->view		= '/mypage/mygdqna_view?seq=';
		$this->mygdqna->boardurl->write		= '/mypage/mygdqna_write?';
		$this->mygdqna->boardurl->modify	= $this->mygdqna->boardurl->write.'&seq=';
		$this->mygdqna->boardurl->reply	= $this->mygdqna->boardurl->write.'&reply=y&seq=';
		$this->mygdqna->boardurl->goodsview		= '/goods/view?no=';						//상품접근

		$this->mygdreview->boardurl->resets	= '/mypage/mygdreview_catalog?';
		$this->mygdreview->boardurl->lists		= '/mypage/mygdreview_catalog?';
		$this->mygdreview->boardurl->view		= '/mypage/mygdreview_view?seq=';
		$this->mygdreview->boardurl->write		= '/mypage/mygdreview_write?';
		$this->mygdreview->boardurl->modify	= $this->mygdreview->boardurl->write.'&seq=';
		$this->mygdreview->boardurl->reply		= $this->mygdreview->boardurl->write.'&reply=y&seq=';
		$this->mygdreview->boardurl->goodsview		= '/goods/view?no=';						//상품접근


		// reserve review 예약게시판/리뷰게시판 관련 추가 2013-11-20 이원희
		$this->myreservetbl						= 'store_reservation';//예약문의

		$this->myreserve->boardurl->resets		= '/mypage/myreserve_catalog?';
		$this->myreserve->boardurl->lists		= '/mypage/myreserve_catalog?';
		$this->myreserve->boardurl->view		= '/mypage/myreserve_view?seq=';
		$this->myreserve->boardurl->write		= '/mypage/myreserve_write?';
		$this->myreserve->boardurl->modify		= $this->myreserve->boardurl->write.'&seq=';
		$this->myreserve->boardurl->reply		= $this->myreserve->boardurl->write.'&reply=y&seq=';
		$this->myreserve->boardurl->goodsview	= '/board/view?no=';						//상품접근

		$this->myreviewtbl						= 'store_review';//상품후기

		$this->myreview->boardurl->resets		= '/mypage/myreview_catalog?';
		$this->myreview->boardurl->lists		= '/mypage/myreview_catalog?';
		$this->myreview->boardurl->view			= '/mypage/myreview_view?seq=';
		$this->myreview->boardurl->write		= '/mypage/myreview_write?';
		$this->myreview->boardurl->modify		= $this->myreserve->boardurl->write.'&seq=';
		$this->myreview->boardurl->reply		= $this->myreserve->boardurl->write.'&reply=y&seq=';
		$this->myreview->boardurl->goodsview	= '/board/view?no=';						//상품접근
		// 추가 End


		$this->template->define(array('catalog_top'=>$this->skin.'/mypage/catalog_top.html'));
		/**
		** @ board end
		**/

		if($this->userInfo['member_seq']){

			$this->template->include_('assignMypageSummaryData');
			assignMypageSummaryData();

		}
	}

	public function main_index()
	{
		redirect("/mypage/index");
	}

	public function index()
	{
		login_check();
		$this->load->model('ordermodel');
		$this->load->model('exportmodel');

		$orders = $this->ordermodel->get_order_list(array('perpage'=>'5','member_seq'=>$this->userInfo['member_seq'],'hidden'=>'N'));
		foreach($orders['record'] as $k => $data_order){
			if($data_order['step'] > 40){
				$data_order['exports'] = $this->exportmodel->get_export_for_order($data_order['order_seq']);
			}
			$orders['record'][$k] = $data_order;
		}

		$this->template->assign(array(
			'orders' => $orders['record']
		));

		/*
		$query = $this->db->query("select filename from fm_member_image where filetype = 'image' and member_seq = '".$this->userInfo['member_seq']."' limit 1");
		$user_image = $query->row_array();
		$this->template->assign(array('user_image'=>$user_image["filename"]));
		*/

		$this->load->model('wishmodel');
		$this->load->model('goodsmodel');
		$this->load->library('sale');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');

		//--> sale library 할인 적용 사전값 전달
		$applypage						= 'wish';
		$param['cal_type']				= 'list';
		$param['total_price']			= 0;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<-- //sale library 할인 적용 사전값 전달

		$result = $this->wishmodel->get_list( $this->userInfo['member_seq'],'list2' );
		foreach($result['record'] as $key => $goods){

			// 카테고리정보
			$tmparr2 = array();
			$categorys = $this->goodsmodel->get_goods_category($goods['goods_seq']);
			foreach($categorys as $val){
				$tmparr = $this->categorymodel->split_category($val['category_code']);
				foreach($tmparr as $cate) $tmparr2[] = $cate;
			}
			if($tmparr2){
				$tmparr2 = array_values(array_unique($tmparr2));
				$goods['r_category'] = $tmparr2;
			}

			$goods['string_price'] = get_string_price($goods);
			$goods['string_price_use'] = 0;
			if($goods['string_price']!='') $goods['string_price_use'] = 1;

			// 배송정보 가져오기
			$goods['delivery']	= $this->goodsmodel->get_goods_delivery($goods);

			//----> sale library 적용
			unset($param, $sales);
			$param['consumer_price']		= $goods['consumer_price'];
			$param['total_price']			= $goods['price'];
			$param['price']					= $goods['price'];
			$param['ea']					= 1;
			$param['category_code']			= $goods['r_category'];
			$param['goods_seq']				= $goods['goods_seq'];
			$param['goods']					= $goods;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);

			$goods['org_price']				= ($goods['consumer_price']) ? $goods['consumer_price'] : $goods['price'];
			$goods['sale_price']			= $sales['result_price'];
			// 포인트
			$goods['point']		= (int) $this->goodsmodel->get_point_with_policy($sales['result_price']) + $sales['tot_point'];
			// 적립금
			$goods['reserve']	= (int) $this->goodsmodel->get_reserve_with_policy($goods['reserve_policy'],$sales['result_price'],$cfg_reserve['default_reserve_percent'],$goods['reserve_rate'],$goods['reserve_unit'],$goods['reserve']) + $sales['tot_reserve'];

			$this->sale->reset_init();
			unset($sales);
			//<---- sale library 적용

			$result['record'][$key] = $goods;
		}
		$this->template->assign(array('wish'=>$result));
		$this->template->assign(array('mypage_index'=>true));
 		$this->print_layout($this->template_path());
	}

	public function order_catalog()
	{
		if( !$this->userInfo['member_seq'] ){
			redirect("/member/login?order_auth=1");
			exit;
		}

		$this->load->model('ordermodel');
		$this->load->model('exportmodel');
		$this->load->model('buyconfirmmodel');


		if(!$this->cfg_order)	$this->cfg_order = config_load('order');
		$this->template->assign(array('cfg_order'	=> $this->cfg_order));

		/**
		 * list setting
		**/
		$sc=array();
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage ? $perpage : 10;
		$sc['member_seq']		= $this->userInfo['member_seq'];
		$sc['keyword']			= $_GET['keyword'];
		$sc['regist_date'][0]	= $_GET['regist_date'][0];
		$sc['regist_date'][1]	= $_GET['regist_date'][1];
		$sc['hidden']			= 'N';
		$sc['step_type']		= $_GET['step_type'];
		$sc['step_type']		= $_GET['step_type']=='export' ? 'export_and_complete' : $_GET['step_type'];

		$arr_step = config_load('step');

		$orders = $this->ordermodel->get_order_list($sc);
		foreach($orders['record'] as $k => $data_order){

			$data_options = $this->ordermodel->get_option_for_order($data_order['order_seq']);
			$data_suboptions = $this->ordermodel->get_suboption_for_order($data_order['order_seq']);

			$item_step = item_step_msg($data_options,$data_suboptions);
			$istep_msg = array();
			if(count($item_step) >= 1){
				foreach($item_step as $istep){
					$istep_msg[] = $arr_step[$istep];
				}
			}
			$data_order['mstep'] = @implode(',',$istep_msg);

			if($data_order['step'] > 40){

				$data_order['exports'] = $this->exportmodel->get_export_for_order($data_order['order_seq']);

				foreach( $data_order['exports'] as $exk => $data_export ){
					$shipping_arr['international'] = $data_export['international'];
					if($data_export['international'] == 'domestic'){
						$shipping_arr['shipping_method'] = $shipping_arr['domestic_shipping_method'];
					}else{
						$shipping_arr['shipping_method_international'] = $shipping_arr['international_shipping_method'];
					}
					$data_export['out_shipping_method'] = $this->ordermodel->get_delivery_method($orders);

					$data_export['item'] =  $this->exportmodel->get_export_item($data_export['export_code']);

					$data_export['data_buy_confirm']		= $this->buyconfirmmodel->get_log_buy_confirm($data_export['export_seq']);

					$data_order['exports'][$exk] = $data_export;
				}
			}
			$orders['record'][$k] = $data_order;
		}

		$this->template->assign($orders);

		$this->print_layout($this->template_path());
	}


	public function order_view()
	{
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->model('exportmodel');
		$this->load->model('refundmodel');
		$this->load->model('returnmodel');
		$this->load->model('buyconfirmmodel');
		$this->load->helper('order');



		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');
		$this->template->assign(array('cfg_order'	=> $this->cfg_order));

		$file_path	= $this->template_path();

		if(!$this->userInfo['member_seq']){
			$order_seq = $this->session->userdata('sess_order');
			if(!$order_seq) {
				redirect("/member/login?order_auth=1");
				exit;
			}

			$orders 			= $this->ordermodel->get_order($order_seq);
		}else{
			$order_seq 	= $_GET['no'];
			$member_seq = $this->userInfo['member_seq'];

			$orders 			= $this->ordermodel->get_order($order_seq, array("member_seq"=>$member_seq));
		}


		if($orders['step'] == 0){
			pageBack('올바른 주문이 아닙니다.');
			exit;
		}

		$order_shippings = $this->ordermodel->get_shipping($order_seq);
		foreach($order_shippings as $k1 => $order_shipping){
			$shipping_items	= $order_shipping['shipping_items'];
			if	($shipping_items)foreach($shipping_items as $itemShip){
				if	($items_shipping['goods_kind'] == 'coupon')
					$order_shippings[$k1]['is_coupon']	= true;
				else
					$order_shippings[$k1]['is_goods']	= true;

				$itemShipping[$itemShip['item_seq']]	= $itemShip;
				$tot['add_goods_shipping']			+= $itemShip['add_goods_shipping'];
			}

			// 기본배송 지역별 추가배송비
			$tot['area_add_delivery_cost']			+= $order_shipping['area_add_delivery_cost'];
		}
		// 기본배송비
		$tot['basic_cost']			+= $order_shippings[0]['shipping_cost'] - $tot['area_add_delivery_cost'];
		$tot['shop_shipping_cost']	+= $order_shippings[0]['shipping_cost'];

		$this->template->assign(array('order_shippings'=>$order_shippings));

		$pay_log 		= $this->ordermodel->get_log($order_seq,'pay');
		$process_log 	= $this->ordermodel->get_log($order_seq,'process');


		$items 				= $this->ordermodel->get_item($order_seq);
		$giftorder 			= $this->ordermodel->get_gift_item($order_seq);

		// 카카오 페이 결제 수단 View 변경 :: 2015-02-26 lwh
		if($orders['pg'] == 'kakaopay'){
			$orders['mpayment']	= "카카오페이 (".$this->arr_payment[$orders['payment']].")";
		}else{
			$orders['mpayment'] = $this->arr_payment[$orders['payment']];
		}
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

		$able_return_ea = $tot['coupontotal'] =$tot['goodstotal'] = 0;
		foreach($items as $key=>$item){

			if ( $item['goods_kind'] == 'coupon' ) {
				$tot['coupontotal']++;//쿠폰상품@2013-11-06
			}else{
				$tot['goodstotal']++;
			}

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
				$data['mstep']		= $this->arr_step[$data['step']];
				$data['real_stock'] = $real_stock;
				$data['stock'] = $stock;

				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
				$data['out_price'] = $data['price']*$data['ea'];

				//sale 5가지
				$data['out_member_sale']			= $data['member_sale']*$data['ea'];//1
				$data['out_coupon_sale']			= ($data['download_seq'])?$data['coupon_sale']:0;//2
				$data['out_fblike_sale']			= $data['fblike_sale'];//3
				$data['out_mobile_sale']			= $data['mobile_sale'];//4
				$data['out_promotion_code_sale']	= $data['promotion_code_sale'];//5
				$data['out_referer_sale']			= $data['referer_sale'];//6
				$data['out_tot_sale']				= $data['out_member_sale']+$data['out_coupon_sale']+$data['out_fblike_sale']+$data['out_mobile_sale']+$data['out_promotion_code_sale']+$data['out_referer_sale'];

				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];
				$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];

				###
				$data['inputs']	= $this->ordermodel->get_input_for_option($data['item_seq'], $data['item_option_seq']);

				$tot['ea'] += $data['ea'];
				$tot['supply_price'] += $data['out_supply_price'];
				$tot['consumer_price'] += $data['out_consumer_price'];
				$tot['price'] += $data['out_price'];

				//sale 6가지
				$tot['member_sale']			+= $data['out_member_sale'];
				$tot['coupon_sale']			+= $data['out_coupon_sale'];
				$tot['fblike_sale']			+= $data['out_fblike_sale'];
				$tot['mobile_sale']			+= $data['out_mobile_sale'];
				$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];
				$tot['referer_sale']		+= $data['out_referer_sale'];

				$tot['reserve'] += $data['out_reserve'];
				$tot['point'] += $data['out_point'];
				$tot['real_stock'] += $real_stock;
				$tot['stock'] += $stock;

				$return_item = $this->returnmodel->get_return_item_ea($data['item_seq'],$data['item_option_seq']);
				$able_return_ea += ($data['step55']+$data['step65']+$data['step75']) - (int) $return_item['ea'];

				$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $data['item_option_seq']);

				if($suboptions) foreach($suboptions as $z => $data_sub){

					$real_stock = $this->goodsmodel -> get_goods_suboption_stock(
							$data_sub['goods_seq'],
							$title,
							$suboption
					);
					$rstock = $this->ordermodel -> get_suboption_reservation(
							$this->cfg_order['ableStockStep'],
							$item['goods_seq'],
							$data_sub['title'],
							$data_sub['suboption']
					);

					$stock = (int) $real_stock - (int) $rstock;
					$data_sub['real_stock'] = (int) $real_stock;
					$data_sub['stock'] = (int) $stock;

					$data_sub['out_supply_price'] = $data_sub['supply_price']*$data_sub['ea'];
					$data_sub['out_consumer_price'] = $data_sub['consumer_price']*$data_sub['ea'];
					$data_sub['out_price'] = $data_sub['price']*$data_sub['ea'];

					$data_sub['out_member_sale']	= $data_sub['member_sale']*$data_sub['ea'];
					$data_sub['out_tot_sale']		= $data_sub['out_member_sale'];

					$data_sub['out_reserve'] = $data_sub['reserve']*$data_sub['ea'];
					$data_sub['out_point'] = $data_sub['point']*$data_sub['ea'];

					$data_sub['mstep']	= $this->arr_step[$data_sub['step']];
					$data_sub['step_complete'] = $data_sub['step45']+$data_sub['step55']+$data_sub['step65']+$data_sub['step75'];

					$suboptions[$z] = $data_sub;

					$tot['ea'] += $data_sub['ea'];
					$tot['supply_price'] 	+= $data_sub['out_supply_price'];
					$tot['consumer_price'] 	+= $data_sub['out_consumer_price'];
					$tot['price'] 			+= $data_sub['out_price'];

					$tot['member_sale'] += $data_sub['out_member_sale'];

					$tot['reserve'] += $data_sub['out_reserve'];
					$tot['point'] += $data_sub['out_point'];

					$tot['real_stock'] 		+= $real_stock;
					$tot['stock'] 			+= $stock;

					$return_item = $this->returnmodel->get_return_item_ea($data_sub['item_seq'],$data_sub['item_suboption_seq']);
					$able_return_ea += ($data_sub['step55']+$data_sub['step65']+$data_sub['step75']) - (int) $return_item['ea'];
				}

				$data['suboptions']	= $suboptions;
				$options[$k] = $data;

				$item['tot_goods_cnt']		+= count($suboptions) + 1;
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
			$item['options']			= $options;
			$item['totaloptitems']		= count($options) + count($suboptions);
			$items[$key] 				= $item;
			$Ritems[$shipping_policy][]	= $item;
			$Ritems[$shipping_policy][0]['shipping_row_cnt']	+= $item['tot_goods_cnt'];
			$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
			$tot['goods_cost']			+= $item['goods_shipping_cost'] - $itemShip['add_goods_shipping'];
			$tot['add_delivery']		+= $itemShip['add_goods_shipping'];
		}

		$tot['add_delivery']		+= $tot['area_add_delivery_cost'];
 		$tot['tot_shipping_cost']	= $order_shippings[0]['shipping_cost'] + $tot['goods_shipping_cost'];

		// 반품가능한 주문상품수량
		$orders['able_return_ea'] = $able_return_ea;
		$this->template->assign(array('return_able_ea'=>$able_return_ea));

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


		// 출고정보
		$exports = $this->exportmodel->get_export_for_order($order_seq);

		//$exports['international']
		//$exports['shipping_method']

		$export_cnt = $buy_confirm_cnt = 0;
		foreach( $exports as $k => $data_export ){
			$export_cnt ++;
			$shipping_arr['international'] = $data_export['international'];
			if($data_export['international'] == 'domestic'){
				$shipping_arr['shipping_method'] = $shipping_arr['domestic_shipping_method'];
			}else{
				$shipping_arr['shipping_method_international'] = $shipping_arr['international_shipping_method'];
			}
			$data_export['out_shipping_method'] = $this->ordermodel->get_delivery_method($orders);

			$data_export['item'] =  $this->exportmodel->get_export_item($data_export['export_code']);
			$data_export['data_buy_confirm']		= $this->buyconfirmmodel->get_log_buy_confirm($data_export['export_seq']);

			if($data_export['international_shipping_method']){
				$data_export['mdelivery'] = $data_export['international_shipping_method'];
				$data_export['mdelivery_number'] = $data_export['international_delivery_no'];
				$data_export['tracking_url'] = "#";
				if($data_export['international_shipping_method']!='ups'){
					$data_export['tracking_url'] = get_delivery_company(get_international_method_code(strtoupper($data_export['international_shipping_method'])),'url').$data_export['international_delivery_no'];
				}
			}

			if($data_export['buy_confirm'] != 'none') {
				$buy_confirm_cnt++;
			}

			foreach( $data_export['item'] as $i=>$data){

				// 쿠폰상품 출고일 경우
				if	($data['goods_kind'] == 'coupon'){
					$coupon_export[$data['export_code']]['coupon_serial']		= $data['coupon_serial'];
					$coupon_export[$data['export_code']]['coupon_st']			= $data['coupon_st'];
					$coupon_export[$data['export_code']]['recipient_email']		= $data['recipient_email'];
					$coupon_export[$data['export_code']]['recipient_cellphone']	= $data['recipient_cellphone'];
					$coupon_export[$data['export_code']]['mail_status']			= $data['mail_status'];
					$coupon_export[$data['export_code']]['sms_status']			= $data['sms_status'];
					$coupon_export[$data['export_code']]['coupon_value']		= $data['coupon_value'];
					$coupon_export[$data['export_code']]['coupon_value_type']	= $data['coupon_value_type'];
					$coupon_export[$data['export_code']]['coupon_remain_value']	= $data['coupon_remain_value'];

					$coupon_export[$data['export_code']]['couponinfo'] = get_goods_coupon_view($data['export_code']);

					$coupon_export[$data['export_code']]['coupon_check_use']	= $this->exportmodel->chk_coupon(array('export_code' => $data_export['export_code']));
				}

				$it_s = $data['item_seq'];
				$it_ops = $data['option_seq'];

				if($data['opt_type']=='opt'){
					$return_item = $this->returnmodel->get_return_item_ea($it_s,$it_ops,$data_export['export_code']);
				}
				if($data['opt_type']=='sub'){
					$return_item = $this->returnmodel->get_return_subitem_ea($it_s,$it_ops,$data_export['export_code']);
				}

				$data_export['item'][$i]['inputs'] = $this->ordermodel->get_input_for_option($data['item_seq'], $data['option_seq']);

				$data_export['item'][$i]['rt_ea']=$data['ea'] - $return_item['ea'];
				$data_export['rt_ea']+=$data_export['item'][$i]['rt_ea'];
			}

			/* 반품신청 가능 기간 체크 */
			if($this->cfg_order['buy_confirm_use']){
				// 구매확정 사용시 출고완료일 후 n일 내에만 반품신청 가능
				if(date('Ymd')<date('Ymd',strtotime('+'.$this->cfg_order['save_term'].' day',strtotime($data_export['export_date'])))){
					$data_export['return_able_term'] = 1;
				}else{
					$data_export['return_able_term'] = 0;
				}
			}else{
				// 구매확정미 사용시 배송완료일 후 n일 내에만 반품신청 가능
				if(date('Ymd')<date('Ymd',strtotime('+'.$this->cfg_order['save_term'].' day',strtotime($data_export['complete_date'])))){
					$data_export['return_able_term'] = 1;
				}else{
					$data_export['return_able_term'] = 0;
				}
			}

			$exports[$k] = $data_export;
		}

		if( $buy_confirm_cnt  == $export_cnt ){
			$orders['buy_confirm'] = true;
		}

		// 결제취소 가능수량
		$refund_able_ea = $this->refundmodel->get_refund_able_ea($order_seq);
		if($this->cfg_order['cancelDisabledStep35'] && $orders['step']>=35){
			$refund_able_ea = 0;
		}

		$this->load->model('salesmodel');
		//세금계산서
		$sc['whereis']	= ' and typereceipt = 1 and order_seq="'.$order_seq.'" ';
		$sc['select']		= ' * ';
		$taxs 		= $this->salesmodel->get_data($sc);
		if( $taxs ) {
			$zipcodear = explode("-",$taxs['zipcode']);
			$taxs['zipcode0'] = $zipcodear[0];
			$taxs['zipcode1'] = $zipcodear[1];
			$this->template->assign(array('tax'	=> $taxs));
		}

		$qry = "select * from fm_sales where order_seq = '".$order_seq."'";
		$query = $this->db->query($qry);
		$tax_array = $query -> result_array();
		$this->template->assign(array('tax_array'	=> $tax_array));

		//현금영수증
		$sc['whereis']	= ' and typereceipt = 2 and order_seq="'.$order_seq.'" ';
		$sc['select']		= ' * ';
		$creceipts 		= $this->salesmodel->get_data($sc);
		if( $creceipts ) {
			$creceipts['goods_name'] = ( count($items) > 1 ) ? $items[0]['goods_name'] ."외".(count($items)-1)."건":$items[0]['goods_name'];
			$creceipts['cash_receipts_no'] = ($creceipts['cash_no'])?$creceipts['cash_no']:$order['cash_receipts_no'];
			$this->template->assign(array('creceipt'	=> $creceipts));
		}

		### 카드결제
		$sc['whereis']	= ' and typereceipt = 0 and order_seq="'.$order_seq.'" ';
		$sc['select']		= ' * ';
		$cards 		= $this->salesmodel->get_data($sc);
		if($cards){
			$this->template->assign(array('cards'=> $cards));
		}

		###
		$pg = config_load($this->config_system['pgCompany']);
		$this->template->assign('pg',$pg);
		if( $this->config_system['pgCompany'] == 'lg' && $orders['pg_transaction_number']) {
			$orders['authdata'] = md5($pg['mallCode'] . $orders['pg_transaction_number'] . $pg['merchantKey']);
		}else{
			$orders['authdata'] = '';
		}

		$cancel_log 	= $this->ordermodel->get_log($order_seq,'cancel');
		foreach($cancel_log as $k=>$row){
			$cancel_log[$k]['detail']='';
		}

		//반품정보 가져오기
		$orders['return_list_ea'] = 0;
		$this->load->model('returnmodel');
		$data_return = $this->returnmodel->get_return_for_order($order_seq);
		if( $data_return )foreach($data_return as $k=>$data){
			$data['mstatus'] = $this->returnmodel->arr_return_status[$data['status']];
			//관리자가 처리했을경우 ID가져오기
			if($data['manager_seq']){
				$rtsql = "select * from fm_manager where manager_seq=?";
				$m_seq=$data['manager_seq'];
				$query = $this->db->query($rtsql,array($m_seq));
				$m_data = $query->row_array();
				$data['manager_id']=$m_data['manager_id'];
			}

			$data_return[$k] = $data;
			$orders['return_list_ea'] += $data['ea'];
		}

		//환불정보 가져오기
		$orders['cancel_list_ea'] = 0;
		$orders['refund_list_ea'] = 0;
		$this->load->model('refundmodel');
		$data_refund = $this->refundmodel->get_refund_for_order($order_seq);
		if( $data_refund )foreach($data_refund as $k=>$data){
			$data['mstatus'] = $this->refundmodel->arr_refund_status[$data['status']];
			if($data['manager_seq']){
				$rtsql = "select * from fm_manager where manager_seq=?";
				//관리자가 처리했을경우 ID가져오기
				$m_seq=$data['manager_seq'];
				$query = $this->db->query($rtsql,array($m_seq));
				$m_data = $query->row_array();
				$data['manager_id']=$m_data['manager_id'];
			}

			$data_refund[$k] = $data;

			if( $data['refund_type'] == 'cancel_payment' ){
				$orders['cancel_list_ea'] += $data['ea'];
			}else{
				$orders['refund_list_ea'] += $data['ea'];
			}
		}

		/* 배송지별 출고내역 */
		foreach($order_shippings as $k=>$order_shipping){
			foreach($order_shipping['shipping_items'] as $item){
				$order_shippings[$k]['shipping_items_cnt'] += count($item['shipping_item_option'])+count($item['shipping_item_suboption']);
			}

			foreach($exports as $export){
				if($export['shipping_seq']==$order_shipping['shipping_seq']){
					$order_shippings[$k]['exports'][] = $export;
				}
			}
		}

		/* 모바일 입금정보 표시 leewh 2014-09-04 */
		if (in_array($orders['payment'], array("bank","virtual","escrow_account"))) {
			$num_account = ($orders['payment']=='bank')? $orders['bank_account'] : $orders['virtual_account'];
			$depositor = ($orders['payment']=='bank')? sprintf("<div style='margin-top:5px;'>입금자명 : %s</div>",$orders['depositor']) : '';
			$deposit_info = sprintf("%s %s", $num_account, $depositor);
			$deposit_css = sprintf('style="height:%s"', ($depositor)? "90px" : "70px");
			$this->template->assign(array('deposit_css' => $deposit_css));
			$this->template->assign(array('deposit_info' => $deposit_info));
		}

		$this->template->assign(array('coupon_export'		=> $coupon_export));
		$this->template->assign(array('order_shippings'		=> $order_shippings));
		$this->template->assign(array('giftorder'			=> $giftorder));
		$this->template->assign(array('orders'				=> $orders));
		$this->template->assign(array('Ritems'				=> $Ritems));
		$this->template->assign(array('items'				=> $items));
		$this->template->assign(array('items_tot'			=> $tot));
		$this->template->assign(array('bank'				=> $bank));
		$this->template->assign(array('pay_log'				=> $pay_log));
		$this->template->assign(array('process_log'			=> $process_log));
		$this->template->assign(array('cancel_log'			=> $cancel_log));
		$this->template->assign(array('data_return'			=> $data_return));
		$this->template->assign(array('data_refund'			=> $data_refund));
		$this->template->assign(array('shipping_policy'		=> $shipping_policy));
		$this->template->assign(array('able_step_action'	=> $this->ordermodel->able_step_action));
		$this->template->assign(array('refund_able_ea'		=> $refund_able_ea));
		$this->template->assign(array('exports'				=> $exports));

		if($_GET['mode']=='summary'){
			$file_path = str_replace('order_view','order_view_summary',$this->template_path());
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		}else{
			$this->print_layout($this->template_path());
		}
	}

	//결제취소 -> 환불
	public function order_refund(){
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');
		$this->load->helper('order');

		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');

		$pg = config_load($this->config_system['pgCompany']);

		$order_seq	= $_POST['order_seq'] ? $_POST['order_seq'] : $_GET['order_seq'];
		$able_steps	= $this->ordermodel->able_step_action['cancel_payment'];

		$orders		= $this->ordermodel->get_order($order_seq, array("member_seq='{$member_seq}'"));
		$items 		= $this->ordermodel->get_item_by_shipping($order_seq);
		$tot		= array();
		$order_total_ea = $this->ordermodel->get_order_total_ea($order_seq);

		$loop = array();

		if($this->cfg_order['cancelDisabledStep35'] && $orders['step']>=35){
			echo "<div class='center'>".$this->arr_step[$orders['step']]."에서는 결제취소가 불가능합니다.</div>";
			exit;
		}

		foreach($items as $key=>$item){
			$shipping_seq = $item['shipping_seq'];
			$shipping	= $this->ordermodel->get_order_shipping($shipping_seq);
			$options 	= $this->ordermodel->get_option_for_item_by_shipping($item['item_seq'], $item['shipping_seq']);
			//$suboptions = $this->ordermodel->get_suboption_for_item_by_shipping($item['item_seq'], $item['shipping_seq']);

			if($options) foreach($options as $k=>$option){
				//$this->db->select("sum(ea) as ea");
				$options[$k]['mstep']	= $this->arr_step[$options[$k]['step']];


				$options[$k]['out_supply_price'] = $option['supply_price']*$option['ea'];
				$options[$k]['out_consumer_price'] = $option['consumer_price']*$option['ea'];
				$options[$k]['out_price'] = $option['price']*$option['ea'];


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


					$suboptions[$k_sub]['out_supply_price'] = $suboption['supply_price']*$suboption['ea'];
					$suboptions[$k_sub]['out_consumer_price'] = $suboption['consumer_price']*$suboption['ea'];
					$suboptions[$k_sub]['out_price'] = $suboption['price']*$suboption['ea'];


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

		if($_GET['use_layout']){
			$this->print_layout($this->template_path());
		}else{
			$this->template->define(array('tpl'=>$this->template_path()));
			$this->template->print_("tpl");
		}
	}

	//반품 or 맞교환 -> 환불
	public function order_return(){
		$this->load->model('ordermodel');
		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('goodsmodel');
		$this->load->model('exportmodel');

		$this->config_shipping = config_load('shipping');
		$this->template->assign("config_shipping",$this->config_shipping);

		$member_seq = $this->userInfo['member_seq'];

		$order_seq	= $_POST['order_seq'] ? $_POST['order_seq'] : $_GET['order_seq'];
		$type		= $_POST['type'] ? $_POST['type'] : $_GET['type'];
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
		
		//주문상품의 실제 1건당 금액계산
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

				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
				$data['out_price'] = $data['price']*$data['ea'];


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
						$data['socialcp_return_disabled'] = true;
					}else{
						if( date("Ymd")>substr(str_replace("-","",$data['social_end_date']),0,8) ) {//유효기간 종료 후 적립금환불 신청가능여부
							$orders['socialcp_valid_coupons'] = true;
							if( $data['socialcp_use_return'] == 1) {//미사용쿠폰 환불대상
								if( order_socialcp_cancel_return($data['socialcp_use_return'], $data['coupon_value'], $data['coupon_remain_value'], $data['social_start_date'], $data['social_end_date'] , $data['socialcp_use_emoney_day'] ) === true ) {
									//미사용쿠폰여부 잔여값어치합계 ==>> 구매금액 % 환불 @2014-10-07
									$data['coupon_refund_type']		= 'price';
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
									$coupon_deduction_price		= (int) ($coupon_real_total_price) - $coupon_remain_price; 
									$coupon_refund_emoney		= $coupon_remain_price;//이전스킨적용
									//$cancel_total_price  += $coupon_remain_price;//취소총금액
								}else{
									$data['rt_ea'] = 0;
									$data['socialcp_return_disabled'] = true;
								}
							}else{
								$data['rt_ea'] = 0;
								$data['socialcp_return_disabled'] = true;
							}
						}else{//유효기간 이전
							if( $data['coupon_remain_value'] >0 ) {//잔여값어치가 남아있을때에만 =>> 구매금액 % 환불 @2014-10-07 
								if( $data['coupon_value'] != $data['coupon_remain_value'] && $data['socialcp_cancel_use_refund'] == '1' ) {
									//부분 사용한 쿠폰은 취소(환불) 불가 @2014-10-07
									$data['rt_ea'] = 0;
									$data['socialcp_return_disabled'] = true;
								}else{
								list($data['socialcp_refund_use'], $data['socialcp_refund_cancel_percent']) = order_socialcp_cancel_refund(
									$order_seq,
									$data['item_seq'],
									$orders['deposit_date'],
									$data['social_start_date'],
										$data['social_end_date'],
										$data['socialcp_cancel_payoption'],
										$data['socialcp_cancel_payoption_percent']
								);//취소(환불) 가능여부

								if( $data['socialcp_refund_use'] === true ) {//취소(환불) 100% 또는 XX% 공제
									if( $data['coupon_value'] == $data['coupon_remain_value'] ) {//전체체크 
											$coupon_remain_price			= (int) ($data['socialcp_refund_cancel_percent'] * $coupon_real_total_price / 100);
											$coupon_deduction_price	= (int) $coupon_real_total_price - $coupon_remain_price;
											$coupon_remain_real_percent = "100";
											$coupon_remain_real_price = $coupon_real_total_price; 
											$data['coupon_refund_type']	= 'price';
											$cancel_total_price  += $coupon_remain_price;//취소총금액 
									}else{
										if ( $data['socialcp_input_type'] == 'price' ) {//금액
											$coupon_remain_price_tmp			= (int) $data['coupon_remain_value'];
											$coupon_deduction_price_tmp	= (int) $data['coupon_value'];
										}else{//횟수
											$coupon_remain_price_tmp			= (int) (100 * ($data['coupon_input_one'] * $data['coupon_remain_value']) / 100);
											$coupon_deduction_price_tmp	= (int) ($data['coupon_input_one'] * $data['coupon_value']);
										}
											$coupon_remain_real_percent = 100 * ($coupon_remain_price_tmp / $coupon_deduction_price_tmp);//잔여값어치율
											$coupon_remain_real_price			= (int) ($coupon_remain_real_percent * ($coupon_real_total_price) / 100);

											$coupon_remain_price			= (int) ($data['socialcp_refund_cancel_percent'] * ($coupon_remain_real_price) / 100);
											$coupon_deduction_price	= (int) ($coupon_real_total_price) - $coupon_remain_price; 
											//$cancel_total_price  += $coupon_remain_price;//취소총금액 
									}
									$data['coupon_refund_type']		= 'price';
								}else{
									$data['rt_ea'] = 0;
									$data['socialcp_return_disabled'] = true;
								}
								}
							}else{
								$data['rt_ea'] = 0;
								$data['socialcp_return_disabled'] = true;
							}
						}
					}

					$cancel_memo = socialcp_cancel_memo($data, $coupon_remain_real_percent, $coupon_real_total_price, $coupon_remain_real_price, $coupon_remain_price, $coupon_deduction_price); 

					$data['coupon_refund_emoney']		= $coupon_refund_emoney;//쿠폰 잔여 값어치의 실제금액
					$data['coupon_remain_price']			= $coupon_remain_price;//쿠폰 결제금액의 실제금액
					$data['coupon_deduction_price']		= $coupon_deduction_price;//쿠폰 결제금액의 공제금액
					$data['cancel_memo']						= $cancel_memo;//취소(환불) 상세내역
				}else{
					$goodstotal++;
				}

				if($cfg_order['buy_confirm_use'] && $data_export['buy_confirm']!='none') $data['rt_ea'] = 0;

				//청약철회상품체크
				unset($goods);
				$goods = $this->goodsmodel->get_goods($data['goods_seq']);
				$data['cancel_type'] = $goods['cancel_type'];
				if( $data['cancel_type'] == 1 )$data['rt_ea'] = 0;

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
				if($_GET['use_layout']){
					pageBack($msg);exit;
				}else{
					echo js('alert("'.$msg.'");setTimeout(function(){closeDialog("order_refund_layer");},0);');//exit;
				}
			}
		}elseif( !$goodstotal || empty($goodstotal) ) {
				$this->template->assign('backalert',true);
				if($_GET['mode'] == 'exchange') {
					$msg = "맞교환신청 상품이 없습니다!";
				}else{
					$msg = "반품신청 상품이 없습니다!";
				}
				if($_GET['use_layout']){
					pageBack($msg);exit;
				}else{
					echo js('alert("'.$msg.'");setTimeout(function(){closeDialog("order_refund_layer");},0);');exit;
				}
		}

		/*
		foreach($items as $key=>$item){
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq'], array("step in ('".implode("','",$able_steps)."')","step85<ea"));
			$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq'], array("step in ('".implode("','",$able_steps)."')","step85<ea"));

			if($options) foreach($options as $k=>$data){
				 $data['reasons'] = $reasons;
				 $data['mstep'] = $this->arr_step[$data[step]];
				 $options[$k] = $data;
				 $it_s = $options[$k]['item_seq'];
				 $it_ops = $options[$k]['item_option_seq'];
				 $return_item = $this->returnmodel->get_return_item_ea($it_s,$it_ops);
				 $options[$k]['rt_ea']=$options[$k]['step75'] - $return_item['ea'];
			}
		}
		*/

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

		if($_GET['use_layout']){
			$this->print_layout($file_path);
		}else{
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		}
	}

	//board controller list
	public function _board_list($boardid)
	{
		define('BOARDID',$boardid);
		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}
		$sql['whereis']	= ' and id= "'.$boardid.'" ';
		$sql['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
		getboardicon();//icon setting
		$this->template->assign('manager',$this->manager);
		$this->lists('mypage');
	}

	//board controller view
	private function _board_view($boardid)
	{
		define('BOARDID',$boardid);
		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}

		$sql['whereis']	= ' and id= "'.$boardid.'" ';
		$sql['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
		getboardicon();//icon setting
		$this->template->assign('manager',$this->manager);
		$this->view('mypage');
	}

	//board controller write
	private function _board_write($boardid)
	{
		define('BOARDID',$boardid);
		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}

		$sql['whereis']	= ' and id= "'.$boardid.'" ';
		$sql['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
		getboardicon();//icon setting
		$this->template->assign('manager',$this->manager);
		$this->write('mypage');
	}

	public function myqna_catalog()
	{
		login_check();
		$this->boardurl = $this->myqna->boardurl;
		$this->_board_list($this->myqnatbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
		$this->print_layout($this->template_path());
	}

	public function myqna_view()
	{
		login_check();
		$this->boardurl = $this->myqna->boardurl;
		$this->_board_view($this->myqnatbl);
	}

	public function myqna_write()
	{
		login_check();
		$this->boardurl = $this->myqna->boardurl;
		$this->_board_write($this->myqnatbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}

	public function mygdqna_catalog()
	{
		login_check();
		$this->boardurl = $this->mygdqna->boardurl;
		$this->_board_list($this->mygdqnatbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
		$this->print_layout($this->template_path());
	}

	public function mygdqna_view()
	{
		login_check();
		$this->boardurl = $this->mygdqna->boardurl;
		$this->_board_view($this->mygdqnatbl);
	}

	public function mygdqna_write()
	{
		login_check();
		$this->boardurl = $this->mygdqna->boardurl;
		$this->_board_write($this->mygdqnatbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}

	/* 매장용 스킨 추가 작업본 2013-11-20 이원희 */
	public function myreview_catalog()
	{
		login_check();
		$this->boardurl = $this->myreview->boardurl;
		$this->_board_list($this->myreviewtbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
		$this->print_layout($this->template_path());
	}

	public function myreview_view()
	{
		login_check();
		$this->boardurl = $this->myreview->boardurl;
		$this->_board_view($this->myreviewtbl);
	}

	public function myreview_write()
	{
		login_check();
		$this->boardurl = $this->myreview->boardurl;
		$this->_board_write($this->myreviewtbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}

	public function myreserve_catalog()
	{
		login_check();
		$this->boardurl = $this->myreserve->boardurl;
		$this->_board_list($this->myreservetbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
		$this->print_layout($this->template_path());
	}

	public function myreserve_view()
	{
		login_check();
		$this->boardurl = $this->myreserve->boardurl;
		$this->_board_view($this->myreservetbl);
	}

	public function myreserve_write()
	{
		login_check();
		$this->boardurl = $this->myreserve->boardurl;
		$this->_board_write($this->myreservetbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}
	/* 매장용 스킨 추가 작업본 End */

	public function mygdreview_catalog()
	{
		login_check();
		$this->boardurl = $this->mygdreview->boardurl;
		//$this->_board_list($this->mygdreviewtbl);
		$this->template->assign('boardurl',$this->boardurl);//link url

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');//적립금자동지급관련
		if( $reserves['autoemoney'] == 1 &&  $reserves['autoemoneytype'] != 3 ){
			if( $reserves['autoemoney_video'] > 0 || $reserves['autoemoney_photo'] > 0  || $reserves['autoemoney_review'] > 0 || $reserves['autopoint_video'] > 0 || $reserves['autopoint_photo'] > 0  || $reserves['autopoint_review'] > 0 ) {
				$reserves['autoemoneytitle'] = true; 
			}
		} 

		$this->template->assign('reserves',$reserves);
		$this->_board_list($this->mygdreviewtbl);
		$this->print_layout($this->template_path());
	}

	public function mygdreview_view()
	{
		login_check();
		$this->boardurl = $this->mygdreview->boardurl;
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');//적립금자동지급관련
		if( $reserves['autoemoney'] == 1 &&  $reserves['autoemoneytype'] != 3 ){
			if( $reserves['autoemoney_video'] > 0 || $reserves['autoemoney_photo'] > 0  || $reserves['autoemoney_review'] > 0 || $reserves['autopoint_video'] > 0 || $reserves['autopoint_photo'] > 0  || $reserves['autopoint_review'] > 0 ) {
				$reserves['autoemoneytitle'] = true; 
			}
		} 

		$this->template->assign('reserves',$reserves);
		$this->template->assign('boardurl',$this->boardurl);//link url
		$this->_board_view($this->mygdreviewtbl);
	}


	public function mygdreview_write()
	{
		if(!$this->userInfo['member_seq']){ 
			if($this->session->userdata('sess_order')) {//비회원 주문조회후 상품평 등록
				redirect("/board/write?id=goods_review&goods_seq=".$_GET['goods_seq']."&order_seq=".$_GET['order_seq']);
				exit;
			}else{
				login_check();
			}
		}
		$this->boardurl = $this->mygdreview->boardurl;
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');//적립금자동지급관련
		if( $reserves['autoemoney'] == 1 &&  $reserves['autoemoneytype'] != 3 ){
			if( $reserves['autoemoney_video'] > 0 || $reserves['autoemoney_photo'] > 0  || $reserves['autoemoney_review'] > 0 || $reserves['autopoint_video'] > 0 || $reserves['autopoint_photo'] > 0  || $reserves['autopoint_review'] > 0 ) {
				$reserves['autoemoneytitle'] = true; 
			}
		} 
		$this->template->assign('reserves',$reserves);
		$this->template->assign('boardurl',$this->boardurl);//link url
		$this->_board_write($this->mygdreviewtbl);
	}

	//세금계산서내역
	public function taxinvoice()
	{
		login_check();

		$this->load->model('salesmodel');
		$this->load->model('ordermodel');
		/**
		 * list setting
		**/
		$sc							= $_GET;
		$sc['orderby']			= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'seq desc';
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):'10';
		$sc['member_seq']	= $this->userInfo['member_seq'];

		$data = $this->salesmodel->sales_tax_list($sc);//게시글목록

		/**
		 * count setting
		**/
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = $this->salesmodel->get_item_total_count($sc);

		$idx = 0;
		foreach($data['result'] as $datarow){$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
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
			//deposit_date 10일 +10 day
			$datarow['taxwriteuse'] = ( date("Ymd",strtotime("+10 day ".$datarow['deposit_date'])) < date("Ymd") ) ? false:true;//입금일로부터 10일까지만

			$items = $this->ordermodel->get_item($datarow['order_seq']);
			$datarow['goods_name'] = ( count($items) > 1 ) ? $items[0]['goods_name'] ."외".(count($items)-1)."건":$items[0]['goods_name'];
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);
		$this->print_layout($this->template_path());
	}

	public function taxwrite()
	{

		$this->load->model('salesmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');
		$this->template->assign(array('cfg_order'	=> $this->cfg_order));
		$orders		= $this->ordermodel->get_order($_POST['order_seq']);
		$items 		= $this->ordermodel->get_item($_POST['order_seq']);

		foreach($items as $key=>$item){
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);

			if($options) foreach($options as $k=>$data){
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

				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
				$data['out_price'] = $data['price']*$data['ea'];

				//sale 5가지
				$data['out_member_sale'] = $data['member_sale']*$data['ea'];
				$data['out_coupon_sale']	= ($data['download_seq'])?$data['coupon_sale']:0;
				$data['out_fblike_sale']		= $data['fblike_sale'];
				$data['out_mobile_sale']		= $data['mobile_sale'];
				$data['out_referer_sale']		= $data['referer_sale'];
				$data['out_promotion_code_sale']		= $data['promotion_code_sale'];

				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];

				$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
				$options[$k] = $data;

				$tot['ea'] += $data['ea'];
				$tot['supply_price'] += $data['out_supply_price'];
				$tot['consumer_price'] += $data['out_consumer_price'];
				$tot['price'] += $data['out_price'];

				$tot['member_sale'] += $data['out_member_sale'];
				$tot['coupon_sale'] += $data['out_coupon_sale'];
				$tot['fblike_sale'] += $data['out_fblike_sale'];
				$tot['mobile_sale'] += $data['out_mobile_sale'];
				$tot['referer_sale'] += $data['out_referer_sale'];
				$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];

				$tot['reserve'] += $data['out_reserve'];
				$tot['point'] += $data['out_point'];

				$tot['real_stock'] += $real_stock;
				$tot['stock'] += $stock;

			}

			if($suboptions) foreach($suboptions as $k=>$data){
				$real_stock = $this->goodsmodel -> get_goods_suboption_stock(
					$data['goods_seq'],
					$title,
					$suboption
				);
				$rstock = $this->ordermodel -> get_suboption_reservation(
					$this->cfg_order['ableStockStep'],
					$item['goods_seq'],
					$data['title'],
					$data['suboption']
				);

				$stock = (int) $real_stock - (int) $rstock;
				$data['real_stock'] = (int) $real_stock;
				$data['stock'] = (int) $stock;

				$data['mstep']	= $this->arr_step[$data['step']];
				$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
				$data['reasons'] = $reasons;


				//mb sale
				$data['out_member_sale'] = $data['member_sale']*$data['ea'];
				$tot['ea'] += $data['ea'];
				$tot['member_sale'] += $data['out_member_sale'];

				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];

				$tot['reserve'] += $data['out_reserve'];
				$tot['point'] += $data['out_point'];

				$suboptions[$k] = $data;
			}

			$items[$key]['options'] = $options;
			$items[$key]['suboptions'] = $suboptions;
			$item['suboptions']	= $suboptions;
			$item['options']	= $options;
			$items[$key] 		= $item;
			$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
		}
		$orders['mpayment'] = $this->arr_payment[$orders['payment']];
		$orders['mstep'] 	= $this->arr_step[$orders['step']];

		$this->template->assign(array('orders'		=> $orders));
		$this->template->assign(array('items'		=> $items));
		$this->template->assign(array('items_tot'	=> $tot));

		$sc['whereis']	= ' and typereceipt = 1 and seq="'.$_POST['tax_seq'].'" ';
		$sc['select']		= ' * ';
		$taxs 		= $this->salesmodel->get_data($sc);
		$zipcodear = explode("-",$taxs['zipcode']);
		$taxs['zipcode0'] = $zipcodear[0];
		$taxs['zipcode1'] = $zipcodear[1];
		if(!$taxs['bperson']) $taxs['bperson'] = $taxs['person'];
		if(!$taxs['bphone']) $taxs['bphone'] = $taxs['phone'];

		$this->template->assign(array('tax'		=> $taxs));

		$thisfile = str_replace('settle_coupon','_coupon',$this->template_path());
		$this->template->define('*', $this->template_path());
		$html = '';
		$html = $this->template->fetch('*');
		$return = array('taxwrite'=>$html);
		echo json_encode($return);
		exit;
	}

	public function coupon()
	{ 
		login_check();
		$this->load->model('couponmodel');
		$this->load->model('ordermodel'); 
		$this->load->helper('coupon');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		
		if( !empty($this->mdata['birthday']) && $this->mdata['birthday'] != '0000-00-00' ) {
			$this->mdata['thisyear_birthday'] = date("Y").substr($this->mdata['birthday'],4,6);
			if(checkdate(substr($this->mdata['thisyear_birthday'],5,2),substr($this->mdata['thisyear_birthday'],8,2),substr($this->mdata['thisyear_birthday'],0,4)) != true) {
				$this->mdata['thisyear_birthday'] = date("Y-m-d",strtotime('-1 day', strtotime($this->mdata['thisyear_birthday'])));
			} 
		}

		if ( !empty($this->mdata['anniversary']) ) {
			$this->mdata['thisyear_anniversary'] = date("Y").'-'.$this->mdata['anniversary'];//기념일(mm-dd) 추가
			if(checkdate(substr($this->mdata['thisyear_anniversary'],5,2),substr($this->mdata['thisyear_anniversary'],8,2),substr($this->mdata['thisyear_anniversary'],0,4)) != true) {
				$this->mdata['thisyear_anniversary'] = date("Y-m-d",strtotime('-1 day', strtotime($this->mdata['thisyear_anniversary'])));
			}
		}
		
		//등급조정쿠폰의 등업된 경우에만 다운가능
		if ($this->mdata['grade_update_date'] != '0000-00-00 00:00:00') {
			$fm_member_group_logsql = "select * from fm_member_group_log where member_seq = '".$this->userInfo['member_seq']."' order by regist_date desc limit 0,1";
			$fm_member_group_logquery = $this->db->query($fm_member_group_logsql);  
			$fm_member_group_log =  $fm_member_group_logquery->row_array(); 
			if( ($fm_member_group_log['prev_group_seq'] >= $fm_member_group_log['chg_group_seq']) || ($this->userInfo['group_seq'] == 1) ) {
				$this->mdata['grade_update_date'] = '';
			}
		}else{
			$this->mdata['grade_update_date'] = substr($this->mdata['regist_date'],0,10);
		}

		###
		//쿠폰 다운내역/다운가능내역
		$sc['member_seq']	= $this->userInfo['member_seq'];
		down_coupon_list('mypage', $sc , $dataloop);//helper('coupon');

		$svcount = $this->couponmodel->get_download_have_total_count($sc,$this->mdata);
		$this->template->assign($svcount);
		###

		if(isset($dataloop)) $this->template->assign('loop',$dataloop); 
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?tab='.$_GET['tab'], getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>'; 
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);

		$this->template->define(array('coupon_top'=>$this->skin.'/mypage/coupon_top.html'));

		$this->print_layout($this->template_path());
	}

	//오프라인쿠폰 > 인증받기
	public function offlinecoupon()
	{
		login_check();
		$this->print_layout($this->template_path());
	}

	//적립금내역
	public function emoney()
	{
		login_check();
		###
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['member_seq']	= $this->userInfo['member_seq'];
		$sc['perpage']			= '10';

		$data = $this->membermodel->emoney_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_emoney',array('member_seq'=>$member_seq));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			if( !$this->isplusfreenot ) {//무료몰인경우 @2013-01-14
				$datarow['limit_date'] = '';
			}
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);

		$this->template->assign('tab', "emoney");

		$this->template->assign('userid', $this->mdata['userid'] );
		$this->template->assign('user_name', $this->mdata['user_name'] );
		$this->template->assign('emoney', $this->mdata['emoney']);
		$this->print_layout($this->template_path());
	}


	//적립금내역
	public function cash()
	{
		login_check();
		if( !$this->isplusfreenot ){//무료몰인경우
			pageBack('잘못된 접근입니다.');
			exit;
		}
		###
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['member_seq']	= $this->userInfo['member_seq'];
		$sc['perpage']			= '10';

		$data = $this->membermodel->cash_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_cash',array('member_seq'=>$member_seq));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			/**
			$datarow['contents'] = "";
			if($datarow['type']=='order'){
				$datarow['contents'] = "주문번호 ".$datarow['ordno'];
			}else if($datarow['type']=='join'){
				$datarow['contents'] = "회원가입";
			}else if($datarow['type']=='bookmark'){
				$datarow['contents'] = "즐겨찾기";
			}else if($datarow['type']=='refund'){
				$datarow['contents'] = "환불 ".$datarow['ordno'];
			}
			**/
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);

		$this->template->assign('tab', "cash");

		$this->template->assign('userid', $this->mdata['userid'] );
		$this->template->assign('user_name', $this->mdata['user_name'] );
		$this->template->assign('cash', $this->mdata['cash']);
		$this->print_layout($this->template_path());
	}

	public function point()
	{
		login_check();

		if( !$this->isplusfreenot ){//무료몰인경우
			pageBack('잘못된 접근입니다.');
			exit;
		}

		###
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['member_seq']	= $this->userInfo['member_seq'];
		$sc['perpage']			= '10';

		$data = $this->membermodel->point_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_point',array('member_seq'=>$member_seq));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			/**
			$datarow['contents'] = "";
			if($datarow['type']=='order'){
				$datarow['contents'] = "주문번호 ".$datarow['ordno'];
			}else if($datarow['type']=='join'){
				$datarow['contents'] = "회원가입";
			}else if($datarow['type']=='bookmark'){
				$datarow['contents'] = "즐겨찾기";
			}else if($datarow['type']=='refund'){
				$datarow['contents'] = "환불 ".$datarow['ordno'];
			}
			**/
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);

		$this->template->assign('userid', $this->mdata['userid'] );
		$this->template->assign('user_name', $this->mdata['user_name'] );
		$this->template->assign('point', $this->mdata['point']);
		$this->print_layout($this->template_path());
	}


	/**
	* @ sns sns회원가입추가
	**/
	function Snsmyinfojoindb($memberseq) {
		$snswhere_arr = array('session_id' =>$this->session->userdata('session_id'));
		$snsjoinmbdata = get_data('fm_membersns_join', $snswhere_arr);
		$snsjoinck = $snsjoinmbdata[0];
		if($snsjoinck) {//있는 경우 업데이트
			$this->db->where('session_id',$this->session->userdata('session_id'));//"session_id"=>$this->session->userdata('session_id'),
			$this->db->update('fm_membersns_join', array("member_seq"=>$memberseq,"update_date"=>date('Y-m-d H:i:s')));
		}else{
			$snsjoinparams['member_seq']	= $memberseq;
			$snsjoinparams['session_id']		= $this->session->userdata('session_id');
			$snsjoinparams['regist_date']		= date('Y-m-d H:i:s');
			$snsjoinparams['update_date']	= date('Y-m-d H:i:s');
			$data = filter_keys($snsjoinparams, $this->db->list_fields('fm_membersns_join'));
			$this->db->insert('fm_membersns_join', $data);
		}
	}


	public function myinfo()
	{
		login_check();

		if( $this->isdemo['isdemo'] && $this->mdata['userid'] == $this->isdemo['isdemoid'] ){
			echo "<script>alert('".$this->isdemo['msg']."'); history.back();</script>";
			//openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		###
		$mtype = 'member';
		if($this->mdata['business_seq']){
			$mtype = 'business';
		}
		###
		$email			= code_load('email');
		$joinform		= ($this->joinform)?$this->joinform:config_load('joinform');
		$memberapproval = config_load('member');

		for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
		for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
		$this->template->assign('m_arr',$m_arr);
		$this->template->assign('d_arr',$d_arr);

		//sns subdomain
		if ( $this->config_system['domain'] && $this->config_system['domain'] == $_SERVER['HTTP_HOST'] )
			$this->template->assign("isdomain",true);//정식도메인
		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));
		
		###

		//SNS계정사용을 위해 미리 세션굽기
		$this->Snsmyinfojoindb($this->mdata['member_seq']);

		//가입 추가 정보 리스트
		$mdata = $this->mdata;
		$qry = "select * from fm_joinform where used='Y' order by sort_seq";
		$query = $this->db->query($qry);
		$form_arr = $query -> result_array();
		foreach ($form_arr as $k => $data){
		$msubdata=$this->membermodel->get_subinfo($mdata['member_seq'],$data['joinform_seq']);
		$data['label_view'] = $this -> membermodel-> get_labelitem_type($data,$msubdata);
		$sub_form[] = $data;
		}
		$this->template->assign('form_sub',$sub_form);

		if($memberapproval) $this->template->assign('memberapproval',$memberapproval);
		if($email) $this->template->assign('email_arr',$email);
		if($mtype) $this->template->assign('mtype',$mtype);
		if($this->mdata['birthday'] == '0000-00-00') $this->mdata['birthday'] ='';
		if($this->mdata) $this->template->assign($this->mdata);

		$this->load->model('snsmember');
		$snsmbsc['select'] = ' * ';
		$snsmbsc['whereis'] = ' and member_seq = \''.$mdata['member_seq'].'\' ';
		$snslist = $this->snsmember->snsmb_list($snsmbsc);
		if($snslist['result'][0]) $this->template->assign(array('snslist'=>$snslist['result']));
		$this->template->assign('snstype',$this->snssocial->snstype);

		$member = config_load('member');
		$member['agreement'] = str_replace("{shopName}",$arrBasic['shopName'],$member['agreement']);
		$member['privacy'] = str_replace("{shopName}",$arrBasic['shopName'],$member['privacy']);
		$member['privacy'] = str_replace("{domain}",$arrBasic['domain'],$member['privacy']);

		//개인정보 수집-이용
		$member['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));
		$this->template->assign($member);

		if(!trim($this->arrSns['key_k'])){ $joinform['use_k'] = ""; }
		$use_sns = array();
		if($joinform['use_f']) $use_sns['facebook'] = array('nm'=>'페이스북','key'=>$this->mdata['sns_f']);
		if($joinform['use_t']) $use_sns['twitter']	= array('nm'=>'트위터','key'=>$this->mdata['sns_t']);
		if($joinform['use_m'] && date("Ymd") < "20140701") $use_sns['me2day']	= array('nm'=>'미투데이','key'=>$this->mdata['sns_m']);
		if($joinform['use_c']) $use_sns['cyworld']	= array('nm'=>'싸이월드','key'=>$this->mdata['sns_c']);
		if($joinform['use_n']) $use_sns['naver']	= array('nm'=>'네이버','key'=>$this->mdata['sns_n']);
		if($joinform['use_k']) $use_sns['kakao']	= array('nm'=>'카카오','key'=>$this->mdata['sns_k']);
		if($joinform['use_d']) $use_sns['daum']		= array('nm'=>'다음','key'=>$this->mdata['sns_d']);
		$joinform['use_sns'] = $use_sns;

		if($joinform) $this->template->assign('joinform',$joinform); 

		
		$this->template->assign('memberIcondata',memberIconConf());//회원아이콘

		$this->template->define(array('form_member'=>$this->skin.'/member/register_form.html'));
		$this->print_layout($this->template_path());
	}

	//초대하기
	public function myfbrecommend()
	{
		if( $this->isdemo['isdemo'] && $this->mdata['userid'] == $this->isdemo['isdemoid'] ){
			echo "<script>alert('".$this->isdemo['msg']."'); history.back();</script>";
			//openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		if($this->mdata['sns_f']){
			$fbuser_profile = $this->snssocial->facebooklogin();
		}

		if($this->session->userdata('fbuser')) {
			$this->template->assign('fbuser',$this->session->userdata('fbuser'));
			$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
			if( !(array_key_exists('publish_actions', $fbpermissions['data'][0]) || in_array('publish_actions', $fbpermissions) ) ) {
				$this->template->assign('publish_stream',"publish_stream, publish_actions");
			}
		}

		$this->template->assign('fbuser',$this->session->userdata('fbuser'));
		login_check();

		if ( $this->config_system['domain'] && $this->config_system['domain'] == $_SERVER['HTTP_HOST'] )
			$this->template->assign("isdomain",true);//정식도메인

		$this->template->assign('firstmallcartid',$this->session->userdata('session_id'));

		$memberapproval = config_load('member');
		$memberapproval['emoneyTerm_invited_title'] = ( $memberapproval['emoneyTerm_invited'] == 'month' ) ? '월':'년';
		$memberapproval['emoneyLimit_invited_title'] = ( $memberapproval['emoneyLimit_invited']*$memberapproval['emoneyInvited'] );

		$this->template->assign('memberapproval',$memberapproval);

		$this->load->model('snsfbinvite');
		$totalinvitesc['whereis']	= ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
		$totalinvitesc['select']		= ' seq ';
		$totalinviteck = $this->snsfbinvite->get_data_numrow($totalinvitesc);
		$this->template->assign('totalinviteck', $totalinviteck );

		if($this->mdata) $this->template->assign($this->mdata);
		$this->print_layout($this->template_path());
	}

	public function withdrawal()
	{
		login_check();

		if( $this->isdemo['isdemo'] && $this->mdata['userid'] == $this->isdemo['isdemoid'] ){
			echo "<script>alert('".$this->isdemo['msg']."'); history.back();</script>";
			//openDialogAlert($this->isdemo['msg'],500,140,'parent',$callback);
			exit;
		}

		###
		$joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		if($joinform) $this->template->assign('joinform',$joinform);

		$this->load->model('snsmember');
		$snsmbsc['select'] = ' * ';
		$snsmbsc['whereis'] = ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
		$snslist[] = $this->snsmember->get_data($snsmbsc);
		if($snslist[0]) $this->template->assign(array('snslist'=>$snslist));

		$withdrawal = code_load('withdrawal');
		if($withdrawal) $this->template->assign('withdrawal_arr',$withdrawal);
 		$this->print_layout($this->template_path());
	}
	// 위시리스트 담기
	public function wish_add(){

		// 로그인 체크
		$session_arr = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
		if(!$session_arr['member_seq']){
			$url = "/member/login?return_url=".urlencode($_SERVER["HTTP_REFERER"]);
			echo("<script>
			parent.openDialogConfirm('회원만 사용가능합니다.\\n로그인하시겠습니까?</strong>',400,140,function(){
				top.document.location.href='".$url."';
			},function(){});
			</script>");
			exit;
		}

		$this->load->model('wishmodel');
		$this->load->model('statsmodel');
		$this->load->model('goodsmodel');

		if($_GET['seqs']){

			/**
			* facebook  opengraph > love item
			**/
			if( $this->arrSns['facebook_interest'] == 'Y' ) {
				if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {
					foreach($_GET['seqs'] as $goods_seq){
						if($goods_seq){
							echo("<script>
							parent.getfbopengraph('{$goods_seq}', 'interests', '{$_SERVER[HTTP_HOST]}','');
							</script>");
							//exit;

							## 위시리스트 통계 저장
							$goodsinfo = $this->goodsmodel->get_goods($goods_seq);
							$params['goods_seq']	= $goods_seq;
							$params['goods_name']	= $goodsinfo['goods_name'];
							$this->statsmodel->insert_wish_stats($params);
						}
					}
				}else{//전용앱이거나 앱적용도메인과 현재 접근도메인이 다른경우
					//if($this->session->userdata('fbuser')) {//페이스북회원인경우
						foreach($_GET['seqs'] as $goods_seq){
							if($goods_seq){
								echo("<script>
								parent.getfbopengraph('{$goods_seq}', 'interests', '{$this->config_system[subDomain]}','');
								</script>");
								//exit;

								## 위시리스트 통계 저장
								$goodsinfo = $this->goodsmodel->get_goods($goods_seq);
								$params['goods_seq']	= $goods_seq;
								$params['goods_name']	= $goodsinfo['goods_name'];
								$this->statsmodel->insert_wish_stats($params);
							}
						}
					//}
				}
			}
			$this->wishmodel->add($_GET['seqs']);
			if( $_GET['seqs'][0]['goods_seq'] ){				
				$str_goods_seq = implode('|',$_GET['seqs']);
				echo "<script>";
				echo "parent.statistics_firstmall('wish','".$str_goods_seq."','','');";
				echo "</script>";
			}
		}

		if($_GET['mode'] == 'cart'){
			if(!$_POST['cart_option_seq']){
				openDialogAlert('상품을 선택해주세요.',400,140,'parent','');
				exit;
			}

			$this->load->model('cartmodel');
			foreach($_POST['cart_option_seq'] as $cart_option_seq){
				$data_cart = $this->cartmodel->get_cart_by_cart_option($cart_option_seq);
				$goods_seq[] = $data_cart['goods_seq'];

				/**
				* facebook  opengraph > love item
				**/
				if( $this->arrSns['facebook_interest'] == 'Y' ) {
					if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {
						echo("<script>
						parent.getfbopengraph('{$data_cart[goods_seq]}', 'interests', '{$_SERVER[HTTP_HOST]}','');
						</script>");
							//exit;
					}else{//전용앱이거나 앱적용도메인과 현재 접근도메인이 다른경우
						echo("<script>
						parent.getfbopengraph('{$data_cart[goods_seq]}', 'interests', '{$this->config_system[subDomain]}','');
						</script>");
						//exit;
					}
				}

				## 위시리스트 통계 저장
				$goodsinfo = $this->goodsmodel->get_goods($data_cart['goods_seq']);
				$params['goods_seq']	= $data_cart['goods_seq'];
				$params['goods_name']	= $goodsinfo['goods_name'];
				$this->statsmodel->insert_wish_stats($params);
			}


			$this->wishmodel->add($goods_seq);
		}

		echo("<script>
		parent.openDialogConfirm('상품이 wishlist에 담겼습니다.<br/><strong>지금 확인하시겠습니까?</strong>',400,140,function(){
			top.document.location.href='/mypage/wish';
		},function(){top.getRightItemTotal('right_item_wish');history.back();});
		</script>");

	}

	public function wish_add_ajax_toggle(){

		if(!$this->userInfo['member_seq']){
			echo json_encode(array(
				'result' => 'not_login',
				'url' => "/member/login?return_url=".$_SERVER["HTTP_REFERER"]
			));
			exit;
		}else if($_GET['goods_seq']){

			$goods_seq = $_GET['goods_seq'];

			$this->load->model('wishmodel');
			$this->load->model('statsmodel');
			$this->load->model('goodsmodel');

			$query = "select * from fm_goods_wish where goods_seq=? and member_seq=?";
			$query = $this->db->query($query,array($goods_seq,$this->userInfo['member_seq']));
			$data = $query->row_array();

			if(!$data){
				$this->wishmodel->add(array($_GET['goods_seq']));
				echo json_encode(array(
					'result' => 'add'
				));
				exit;
			}else{
				$this->wishmodel->del(array($data['wish_seq']));
				echo json_encode(array(
					'result' => 'del'
				));
				exit;
			}
		}

		echo json_encode(array());

	}

	// 위시리스트 삭제
	public function wish_del(){
		login_check();
		$this->load->model('wishmodel');
		if($_GET['seqs']){ // mobile_ver2 의 상품상세 위시 취소 버튼 2014-01-11 lwh
			$wish_seq = $this->wishmodel->confirm_wish($_GET['seqs']);
			if($wish_seq){
				$seqs[] = $wish_seq;
				$this->wishmodel->del($seqs);
				openDialogAlert('취소되었습니다.',400,140,'parent','history.back();');
			}
			exit;
		}
		if(!$_POST['wish_seq']){
			openDialogAlert('상품을 선택해주세요.',400,140,'parent','history.back();');
			exit;
		}
		if($_POST['wish_seq']){
			$this->wishmodel->del($_POST['wish_seq']);
		}
		if($_GET['return_url']) pageRedirect($_GET['return_url'],'','parent');
		else pageRedirect('/mypage/wish','','parent');
	}

	// 위시리스트
	public function wish(){
		login_check();
		$this->load->model('wishmodel');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->library('sale');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');

		//--> sale library 할인 적용 사전값 전달
		$applypage						= 'wish';
		$param['cal_type']				= 'list';
		$param['total_price']			= 0;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<-- //sale library 할인 적용 사전값 전달

		$result = $this->wishmodel->get_list( $this->userInfo['member_seq'],'list2' );
		foreach($result['record'] as $key => $goods){

			// 카테고리정보
			$tmparr2 = array();
			$categorys = $this->goodsmodel->get_goods_category($goods['goods_seq']);
			foreach($categorys as $val){
				$tmparr = $this->categorymodel->split_category($val['category_code']);
				foreach($tmparr as $cate) $tmparr2[] = $cate;
			}
			if($tmparr2){
				$tmparr2 = array_values(array_unique($tmparr2));
				$goods['r_category'] = $tmparr2;
			}

			$goods['string_price'] = get_string_price($goods);
			$goods['string_price_use'] = 0;
			if($goods['string_price']!='') $goods['string_price_use'] = 1;

			// 배송정보 가져오기
			$goods['delivery']	= $this->goodsmodel->get_goods_delivery($goods);

			//----> sale library 적용
			unset($param, $sales);
			$param['consumer_price']		= $goods['consumer_price'];
			$param['total_price']			= $goods['price'];
			$param['price']					= $goods['price'];
			$param['ea']					= 1;
			$param['category_code']			= $goods['r_category'];
			$param['goods_seq']				= $goods['goods_seq'];
			$param['goods']					= $goods;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);

			$goods['org_price']				= ($goods['consumer_price']) ? $goods['consumer_price'] : $goods['price'];
			$goods['sale_price']			= $sales['result_price'];
			// 포인트
			$goods['point']		= (int) $this->goodsmodel->get_point_with_policy($sales['result_price']) + $sales['tot_point'];
			// 적립금
			$goods['reserve']	= (int) $this->goodsmodel->get_reserve_with_policy($goods['reserve_policy'],$sales['result_price'],$cfg_reserve['default_reserve_percent'],$goods['reserve_rate'],$goods['reserve_unit'],$goods['reserve']) + $sales['tot_reserve'];

			$this->sale->reset_init();
			unset($sales);
			//<---- sale library 적용

			$result['record'][$key] = $goods;
		}
		$this->template->assign($result);
		$this->print_layout($this->template_path());
	}

	// 위시리스트 담기
	public function wish2cart(){
		login_check();

		$this->load->helper('order');
		$this->load->model('goodsmodel');
		$this->load->model('wishmodel');
		$this->load->model('membermodel');
		$this->load->model('categorymodel');
		$this->load->library('sale');

		// 절사 설정 저장
		if	(!$this->config_system)	$this->config_system	= config_load('system');
		if	($this->config_system['cutting_sale_use'] != 'none'){
			$cfg_cutting['action']	= $this->config_system['cutting_sale_action'];
			$cfg_cutting['price']	= $this->config_system['cutting_sale_price'];
		}
		$cfg_order		= config_load('order');
		$wish_seq		= (int) $_GET['no'];
		$wish			= $this->wishmodel->get_wish($wish_seq);
		$goods_seq		= $wish['goods_seq'];
		$images			= $this->goodsmodel->get_goods_image($goods_seq);

		$result	= $this->goodsmodel->get_goods_view($goods_seq);

		if	($result['status'] == 'error'){
			switch($result['errType']){
				case 'echo':
					echo $result['msg'];
					exit;
				break;
				case 'back':
					alert($result['msg']);
					pageReload();
					exit;
				break;
				case 'redirect':
					alert($result['msg']);
					pageRedirect($result['url'],'');
					exit;
				break;
			}
		}else{
			$goods			= $result['goods'];
			$category		= $result['category'];
			$alerts			= $result['alerts'];

			$goods['image']	= $images[1]['thumbView']['image'];

			if	($result['assign'])foreach($result['assign'] as $key => $val){
				$this->template->assign(array($key	=> $val));
			}

			// 옵션 분리형
			if($goods['option_view_type']=='divide' && $options){
				$options_n0 = $this->option($goods['goods_seq']);
				$this->template->assign(array('options_n0'	=> $options_n0));
			}

			// 옵션 조합형
			if($goods['option_view_type']=='join' && $options){
				$options_join = $this->option_join($goods['goods_seq']);
				$this->template->assign(array('options_join'	=> $options_join));
			}
		}

		/*
		// 회원정보 가져오기
		if($this->userInfo){
			$goods_member = $this->membermodel->get_member_data($this->userInfo['member_seq']);
		}
		$goods_member['group_seq'] = (int) $goods_member['group_seq'];

		$categorys = $this->goodsmodel->get_goods_category($goods_seq);
		if($categorys) foreach($categorys as $key => $data_category){
			if( $data_category['link'] == 1 ){
				$category_code = $this->categorymodel->split_category($data_category['category_code']);
			}
		}

		//----> sale library 적용
		$applypage						= 'wish';
		$param['cal_type']				= 'list';
		$param['total_price']			= $goods['price'];
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		foreach($options as $k => $opt){

			//----> sale library 적용
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $opt['consumer_price'];
			$param['price']						= $opt['price'];
			$param['total_price']				= $opt['price'];
			$param['ea']						= 1;
			$param['category_code']				= $category_code;
			$param['goods_seq']					= $goods['goods_seq'];
			$param['goods']						= $goods;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);

			$opt['price']						= $sales['result_price'];
			$goods['reserve']	= (int) $this->goodsmodel->get_reserve_with_policy($goods['reserve_policy'],$sales['result_price'],$cfg_reserve['default_reserve_percent'],$goods['reserve_rate'],$goods['reserve_unit'],$goods['reserve']) + $sales['tot_reserve'];
			$this->sale->reset_init();
			//<---- sale library 적용

			// 대표가격
			if($opt['default_option'] == 'y'){
				$goods['price'] 			= $opt['price'];
				$goods['consumer_price'] 	= $opt['consumer_price'];
				$goods['reserve'] 			= $opt['reserve'];
			}

			$opt['chk_stock'] = check_stock_option($goods['goods_seq'],$opt['option1'],$opt['option2'],$opt['option3'],$opt['option4'],$opt['option5'],$opt['ea'],$cfg_order);
			if( $opt['chk_stock'] ) $runout = false;
			$options[$k] = $opt;
		}

		if($suboptions) foreach($suboptions as $key => $tmp){
			foreach($tmp as $k => $opt){
				$opt['chk_stock'] = check_stock_suboption($goods['goods_seq'],$opt['suboption_title'],$opt['suboption'],$ea,$cfg_order);
				//----> sale library 적용
				unset($param, $sales);
				$param['option_type']				= 'suboption';
				$param['sub_sale']					= $opt['sub_sale'];
				$param['consumer_price']			= $opt['consumer_price'];
				$param['price']						= $opt['price'];
				$param['total_price']				= $opt['price'];
				$param['ea']						= 1;
				$param['category_code']				= $category_code;
				$param['goods_seq']					= $goods['goods_seq'];
				$param['goods']						= $goods;
				$this->sale->set_init($param);
				$sales								= $this->sale->calculate_sale_price($applypage);
				$opt['price']						= $sales['result_price'];
				$opt['reserve']						+= $sales['tot_reserve'];
				$this->sale->reset_init();
				//<---- sale library 적용

				$suboptions[$key][$k] = $opt;
			}
		}

		if(isset($options[0]['option_divide_title'])) $goods['option_divide_title'] = $options[0]['option_divide_title'];
		*/

		$file = str_replace('optional_changes','_optional_changes',$this->template_path());
		$this->template->assign(array('cfg_cutting'=>$cfg_cutting));
		$this->template->assign(array('wish'=>$wish));
		$this->template->assign(array('goods'=>$goods));
		//$this->template->assign(array('options'=>$options));
		//$this->template->assign(array('suboptions'=>$suboptions));
		//$this->template->assign(array('inputs'=>$inputs));
		$file = str_replace('wish2cart','_wish2cart',$this->template_path());
		$this->template->define(array('LAYOUT'=>$file));
		$this->template->print_('LAYOUT');

		// 가격대체문구 사용여부
		echo "<script>var gl_string_price_use = 0;</script>";
		if( $goods['string_price_use'] ){
			echo "<script>var gl_string_price_use = ".$goods['string_price_use'].";</script>";
		}

		// 관리자 표시용 메시지 출력
		foreach($alerts as $msg){
			alert($msg);
		}
	}


	public function delivery_address(){
		login_check();
		$this->load->helper('shipping');

		$sc=array();
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage ? $perpage : 10;
		$list_order=$_GET['order'];

		switch($list_order){
			case 'desc_up' :
				$orderby='address_description asc';
				break;
			case 'desc_dn' :
				$orderby='address_description desc';
				break;
			case 'name_up' :
				$orderby='recipient_user_name asc';
				break;
			case 'name_dn' :
				$orderby='recipient_user_name desc';
				break;
			case 'name_dn' :
				$orderby='address_seq desc';
				break;
			default :
				$orderby='address_seq desc';
				break;
		}

		$shipping = use_shipping_method();
		if( $shipping ){
			foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
		}
		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;

		$deli_cnt = count($shipping_policy['policy']);

		$member_seq = $this->userInfo['member_seq'];

		$tab=$_GET['tab'];
		$key = get_shop_key();

		$popup=$_GET['popup'];
		$international=$_GET['view_international'];
		$address_group=$_GET['group'];
		$mobileAjaxCall=$_GET['mobileAjaxCall'];

		$sql="select *,
				AES_DECRYPT(UNHEX(recipient_phone), '{$key}') as recipient_phone,
				AES_DECRYPT(UNHEX(recipient_cellphone), '{$key}') as recipient_cellphone
			from fm_delivery_address where member_seq=".$member_seq;

		if($tab=='2'){
			$sql .= " and lately='Y' ";
		}else{
			$sql .= " and often='Y' ";
		}

		if($deli_cnt < 2){
			$sql .= " and international ='domestic' ";
		}elseif($international){
			$sql .= " and international='".$international."' ";
		}

		if($address_group){
			$sql .= " and address_group='".$address_group."' ";
		}

		$sql .= " order by ".$orderby." ";

		if($popup == '1' || $mobileAjaxCall){
			$sql .= " limit 30 ";
			$query = $this->db->query($sql);
			$result['record'] = $query -> result_array();
		}else{
			$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		}

		foreach($result['record'] as $data){
			if($data['international'] == 'domestic'){
				$international_show = '국내';
			}elseif($data['international'] == 'international'){
				$international_show = '해외';
			}
			$data['international_show'] = $international_show;
			$loop[] = $data;
		}

		$query = $this->db->query("select address_group from fm_delivery_address where member_seq=? and address_group is not null and address_group !='' group by address_group order by address_group asc",$member_seq);
		$arr_address_group = $query->result_array();
		if(!$arr_address_group){
			$arr_address_group[] = array('기본 그룹');
		}
		$this->template->assign('arr_address_group',$arr_address_group);

		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('shipping_policy',$shipping_policy);
		$this->template->assign('loop',$loop);
		$this->template->assign($result);
		$this->print_layout($this->template_path());
	}

	public function delivery_address_ajax(){
		$key = get_shop_key();
		$query = $this->db->query("select *,
				AES_DECRYPT(UNHEX(recipient_phone), '{$key}') as recipient_phone,
				AES_DECRYPT(UNHEX(recipient_cellphone), '{$key}') as recipient_cellphone
			from fm_delivery_address where address_seq=?",$_GET['address_seq']);
		$result = $query->row_array();
		foreach($result as $k=>$v){
			if(is_null($v)) $result[$k] = '';
			if($k == 'default' ) $result['defaults'] = $v;
		}
		echo json_encode($result);
	}

	public function refund_catalog()
	{
		if( !$this->userInfo['member_seq'] ){
			redirect("/member/login?order_auth=1");
			exit;
		}

		$this->load->model('refundmodel');

		/**
		 * list setting
		**/
		$sc=array();
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage ? $perpage : 10;
		$sc['member_seq']		= $this->userInfo['member_seq'];
		$sc['order_seq']		= $_GET['order_seq'];
		$sc['step_type']		= $_GET['step_type'];

		$refunds = $this->refundmodel->get_refund_list($sc);

		$this->template->assign($refunds);

		$this->print_layout($this->template_path());
	}

	public function refund_view()
	{
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->model('refundmodel');
		$this->load->model('returnmodel');

		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');
		$this->template->assign(array('cfg_order'	=> $this->cfg_order));

		$refund_code 	= $_GET['refund_code'];

		$data_refund 		= $this->refundmodel->get_refund($refund_code);
		$data_refund_item 	= $this->refundmodel->get_refund_item($refund_code);
		$data_order			= $this->ordermodel->get_order($data_refund['order_seq']);
		$data_order_item	= $this->ordermodel->get_item($data_refund['order_seq']);
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

		/* 반품에 의한 환불일경우 주문시 지급 적립금/포인트 합계 표시 */
		if($data_refund['refund_type']=='return')
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

		$data_refund['mstatus'] = $this->refundmodel->arr_refund_status[$data_refund['status']];
		$data_refund['mrefund_type'] = $this->refundmodel->arr_refund_type[$data_refund['refund_type']];
		$data_refund['mcancel_type'] = $this->refundmodel->arr_cancel_type[$data_refund['cancel_type']];
		$data_order['mpayment'] = $this->arr_payment[$data_order['payment']];

		if($data_order['international']=='international'){
			$data_order['real_shipping_cost'] = $data_order['international_cost'];
		}else{
			$data_order['real_shipping_cost'] = $data_order['shipping_cost'];
		}

		$refund_items = array();
		foreach($data_refund_item as $k => $data){

			if( $data['goods_kind'] == 'coupon' ) {//
				$data_return = $this->returnmodel->get_return_refund_code($refund_code);
				$data_return_item 	= $this->returnmodel->get_return_item($data_return['return_code']);
				$data['couponinfo'] = get_goods_coupon_view($data_return_item[0]['export_code']);
				$tot['coupon_use_return'] = $data['couponinfo']['coupon_use_return'];
				$tot['coupontotal']++;
			}else{
				$tot['goodstotal']++;
			}

			$tot['ea'] += $data['ea'];
			$tot['price'] += $data['price']*$data['ea'];

			$tot['out_supply_price']		+= $data['supply_price']*$data['ea'];
			$tot['out_consumer_price']	+= $data['consumer_price']*$data['ea'];
			$tot['out_price']					+= $data['price']*$data['ea'];

			$tot['member_sale'] += $data['member_sale']*$data['ea'];
			$tot['coupon_sale'] += $data['coupon_sale'];
			$tot['fblike_sale'] += $data['fblike_sale'];
			$tot['mobile_sale'] += $data['mobile_sale'];
			$tot['promotion_code_sale'] += $data['promotion_code_sale'];

			if($data_refund['refund_type']=='return'){
				$tot['return_reserve'] += $data['reserve']*$data['ea'];
				$tot['return_point'] += $data['point']*$data['ea'];
			}

			//복원된 쿠폰 여부
			if($data['download_seq']){
				$data['restore_used_coupon_refund'] = $this->couponmodel->restore_used_coupon_refund($data['download_seq']);
			}

			//복원된 프로모션코드 여부
			if($data['promotion_code_seq']){
				$data['restore_used_promotioncode_refund'] = $this->promotionmodel->restore_used_promotioncode_refund($data['promotion_code_seq']);
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
			$refund_items[$data['item_seq']]['shipping'][]	= $data['goods_shipping_cost'];

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

		// 시스템 계산금액
		$tot['system_price'] = 0;
		$tot['system_price'] += $tot['price'];
		$tot['system_price'] += $tot['refund_goods_shipping_cost'];
		$tot['system_price'] -= $tot['member_sale'];
		$tot['system_price'] -= $tot['coupon_sale'];
		$tot['system_price'] -= $tot['fblike_sale'];
		$tot['system_price'] -= $tot['mobile_sale'];
		$tot['system_price'] -= $tot['promotion_code_sale'];

		$tot['system_price'] += $tot['refund_shipping_cost'];

		// 총 조정금액
		$tot['adjust_price'] = 0;
		$tot['adjust_price'] += $data_refund['adjust_use_coupon'];
		$tot['adjust_price'] += $data_refund['adjust_use_promotion'];
		$tot['adjust_price'] += $data_refund['adjust_use_emoney'];//적립금
		$tot['adjust_price'] += $data_refund['adjust_use_cash'];//이머니(캐쉬)
		$tot['adjust_price'] += $data_refund['adjust_use_enuri'];

		// 환불금액
		$tot['refund_expected_price'] = $tot['system_price']-$tot['adjust_price'];

		// 최종환불금액
		$tot['final_refund_price'] = $tot['refund_expected_price']-$data_refund['adjust_refund_price'];

		$this->template->assign(
			array(
			'refund_shipping_items'=>$refund_shipping_items,
			'refund_total_rows'=>$refund_total_rows,
			'data_refund'=>$data_refund,
			'data_refund_item'=>$data_refund_item,
			'refund_items'=>$refund_items,
			'tot'=>$tot,
			'data_order'=>$data_order)
		);

		$this->print_layout($this->template_path());
	}

	public function return_catalog()
	{
		if( !$this->userInfo['member_seq'] ){
			redirect("/member/login?order_auth=1");
			exit;
		}

		$this->load->model('returnmodel');

		/**
		 * list setting
		**/
		$sc=array();
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage ? $perpage : 10;
		$sc['member_seq']		= $this->userInfo['member_seq'];
		$sc['order_seq']		= $_GET['order_seq'];
		$sc['step_type']		= $_GET['step_type'];

		$refunds = $this->returnmodel->get_return_list($sc);

		$this->template->assign($refunds);

		$this->print_layout($this->template_path());
	}

	public function return_view()
	{
		$return_code = $_GET['return_code'];

		// 사유코드
		$reasons = code_load('return_reason');

		$this->load->model('returnmodel');
		$this->load->model('refundmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');

		$data_return 		= $this->returnmodel->get_return($return_code);
		$data_return_item 	= $this->returnmodel->get_return_item($return_code);
		$data_order			= $this->ordermodel->get_order($data_return['order_seq']);

		$tmp = $this->refundmodel->get_refund($data_return['refund_code']);
		$data_return['mrefund_status']	= $this->refundmodel->arr_refund_status[$tmp['status']];
		$data_return['mstatus'] 		= $this->returnmodel->arr_return_status[$tmp['status']];

		if( $data_return['phone'] )$data_return['phone'] = explode('-',$data_return['phone']);
		if( $data_return['cellphone'] )$data_return['cellphone'] = explode('-',$data_return['cellphone']);
		if( $data_return['sender_zipcode'] )$data_return['sender_zipcode'] = explode('-',$data_return['sender_zipcode']);

		foreach($data_return_item as $key => $item){
			if( $item['goods_kind'] == 'coupon' ) {//
				$data_return_item[$key]['couponinfo'] = get_goods_coupon_view($item['export_code']);
			}

			$goods_cnt[$item['goods_seq']]++;
			$tot['ea']  		+= $item['ea'];
			$tot['return_ea']	+= $item['return_ea'];

			$tot['out_supply_price']		+= $item['supply_price']*$item['ea'];
			$tot['out_consumer_price']	+= $item['consumer_price']*$item['ea'];
			$tot['out_price']					+= $item['price']*$item['ea'];

			if( $item['reason_code'] > 100 && $item['reason_code'] < 200 ) $tot['user_reason_cnt'] += $item['ea'];
			if( $item['reason_code'] > 200 && $item['reason_code'] < 300 ) $tot['shop_reason_cnt'] += $item['ea'];
			if( $item['reason_code'] > 300 ) $tot['goods_reason_cnt'] += $item['ea'];
			$data_return_item[$key]['reasons'] = $reasons;

			$query = $this->db->query("select refund_code from fm_order_refund_item where item_seq=?",$item['item_seq']);
			$tmp = $query->row_array();
			$data_return_item[$key]['refund_code'] = $tmp['refund_code'];
		}
		if($goods_ea) $tot['goods_cnt'] = array_sum($goods_cnt);
		$data_return['mstatus'] = $this->returnmodel->arr_return_status[$data_return['status']];
		$data_return['mreturn_type'] = $this->returnmodel->arr_return_type[$data_return['return_type']];
		$data_return['mreturn_method'] = $this->returnmodel->arr_return_method[$data_return['return_method']];

		$this->template->assign(
			array(
				'data_return'=>$data_return,
				'data_return_item'=>$data_return_item,
				'tot'=>$tot,
				'data_order'=>$data_order
			)
		);

		$this->print_layout($this->template_path());
	}


	//포인트교환
	public function point_exchange(){
		login_check();
		if( !$this->isplusfreenot ){//무료몰인경우
			pageBack('잘못된 접근입니다.');
			exit;
		}

		if( !$this->isplusfreenot['ispoint'] ){//포인트 사용안함
			//pageBack('잘못된 접근입니다.');
			//exit;
		}

		if( !$this->isplusfreenot["isemoney_exchange"] ){//포인트교환 사용안함
			pageBack('잘못된 접근입니다.');
			exit;
		}

		### GIFT
		$today = date("Y-m-d");
		$sql = "SELECT * FROM fm_gift WHERE gift_gb = 'buy' AND start_date <= '{$today}' AND end_date >= '{$today}' AND display = 'y' ORDER BY gift_seq DESC limit 1";
		$query = $this->db->query($sql);
		$gift = $tmp = $query->row_array();

		if($gift['gift_seq']){
			$sql = "SELECT A.* FROM fm_gift_benefit A LEFT JOIN fm_goods B ON A.gift_goods_seq = B.goods_seq WHERE gift_seq = '{$gift['gift_seq']}' and B.goods_view = 'look' and B.goods_status = 'normal' ORDER BY sprice";
			$query = $this->db->query($sql);
			foreach($query->result_array() as $k){

				$k['goods']		= explode("|",$k['gift_goods_seq']);

				//사은품 노출 체크
				for($i=0; $i<count($k['goods']); $i++){
					$sql	= "SELECT count(*) as cnt FROM fm_goods WHERE goods_seq = '".$k['goods'][$i]."' and  goods_view = 'look'";
					$query	= $this->db->query($sql);
					$info	= $query->result_array();
					$cnt	= $info[0]['cnt'];
					if($cnt < 1){
						unset($k['goods'][$i]);
					}
				}

				$gift_loop[]	= $k;
			}


			$this->template->assign('gift_loop',$gift_loop);


		}
		$this->template->assign('gift_info',$gift);
		$configReserve = config_load('reserve');
		$this->template->assign('configReserve',$configReserve);
		$this->template->assign('myemoney',$this->mdata['emoney']);
		$this->print_layout($this->template_path());
	}


	public function buy_gift(){
		$goods_seq		= $_GET['seq'];
		$point			= $_GET['point'];
		$goods_rule			= $_GET['goods_rule'];
		$goods_name			= unescape($_GET['goods_name']);

		$this->template->assign(array('goods_seq'=>$goods_seq,'point'=>$point,'goods_name'=>$goods_name));

		if($this->userInfo['member_seq']){
			$this->load->model('membermodel');
			$members = $this->membermodel->get_member_data($this->userInfo['member_seq']);
			$tmp = explode('-',$members['phone']);
			foreach($tmp as $k => $data){
				$key = 'phone'.($k+1);
				$members[$key] = $data;
			}

			$tmp = explode('-',$members['cellphone']);
			foreach($tmp as $k => $data){
				$key = 'cellphone'.($k+1);
				$members[$key] = $data;
			}

			$tmp = explode('-',$members['zipcode']);
			foreach($tmp as $k => $data){
				$key = 'zipcode'.($k+1);
				$members[$key] = $data;
			}
		}
		$this->template->assign('members',$members);

		$this->load->helper('shipping');
		$shipping = use_shipping_method();
		if( $shipping ){
			foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
		}
		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;
		$this->template->assign('shipping_policy',$shipping_policy);
		$this->template->assign('goods_rule',$goods_rule);

		$this->template->define(array('LAYOUT'=>$this->template_path()));
		$this->template->print_('LAYOUT');
	}

	//개인결제
	public function personal()
	{

		login_check();

		$this->load->model('goodsdisplay');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');

		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

		/* 카테고리 정보 */
		$categoryData = $this->categorymodel->get_category_data($code);

		$code = $categoryData['category_code'];

		$childCategoryData = $this->categorymodel->get_list($code,array(
			"hide = '0'",
			"level >= 2"
		));
		//print_r($childCategoryData);
		if(strlen($code)>4 && !$childCategoryData){
			$childCategoryData = $this->categorymodel->get_list(substr($code,0,strlen($code)-4),array(
				"hide = '0'",
				"level >= 2"
			));
		}

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['search_text']		= (!empty($_GET['search_text']))?str_replace(array('"',"'"),"",$_GET['search_text']):'';
		$sc['category']			= $code;
		$sc['perpage']			= $_GET['perpage'] ? $_GET['perpage'] : 10;
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : '';
		$sc['list_goods_status']= $categoryData['list_goods_status'];

		if($_GET['so_brand'])	$sc['so_brand']		= $_GET['so_brand'];
		if($_GET['so_option1'])	$sc['so_option1']	= $_GET['so_option1'];
		if($_GET['so_option2'])	$sc['so_option2']	= $_GET['so_option2'];


		/* 쇼핑몰 타이틀 */
		if($this->config_basic['shopCategoryTitleTag'] && $categoryData['title']){
			$title = str_replace("{카테고리명}",$categoryData['title'],$this->config_basic['shopCategoryTitleTag']);
			$this->template->assign(array('shopTitle'=>$title));
		}

		$str_where_order = " AND regist_date >= '".date('Y-m-d',strtotime("-5 day"))." 00:00:00'";

		$key = get_shop_key();
		$query = "
				select * from (
				SELECT title, order_user_name, total_price, order_seq, enuri, member_seq, order_email, order_phone, order_cellphone, person_seq,
					regist_date,
					(SELECT userid FROM fm_member WHERE member_seq=pr.member_seq) userid,
					(SELECT AES_DECRYPT(UNHEX(email), '{$key}') as email FROM fm_member WHERE member_seq=pr.member_seq) mbinfo_email,
					(SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=pr.member_seq) group_name,
					(select goods_name from fm_goods where goods_seq
						in (select goods_seq from fm_person_cart where person_seq = pr.person_seq) limit 1) goods_name,
					(select count(goods_seq) from fm_person_cart where person_seq = pr.person_seq) item_cnt
				FROM fm_person pr where order_seq is null ".$str_where_order." AND pr.member_seq = '".$this->userInfo['member_seq']."') t ".$str_where. " order by person_seq desc
		";

		$list = select_page($sc['perpage'],$sc['page'],10,$query,'');
		$list['page']['querystring'] = get_args_list();
		$list['search_yn'] = $search_yn;

		$this->template->assign($list);

		$this->template->assign(array(
			'categoryCode'			=> $code,
			'categoryTitle'			=> $categoryData['title'],
			'categoryData'			=> $categoryData,
			'childCategoryData'		=> $childCategoryData,
		));
		$this->getboardcatalogcode = $code;//상품후기 : 게시판추가시 이용됨


		/**
		 * display
		**/
		$sc['list_style'] = "person";
		$this->goodsdisplay->set('style',$sc['list_style'] ? $sc['list_style'] : $categoryData['list_style']);
		$this->goodsdisplay->set('count_w',$categoryData['list_count_w']);
		$this->goodsdisplay->set('count_h',$categoryData['list_count_h']);
		$this->goodsdisplay->set('perpage',$perpage);
		$this->goodsdisplay->set('image_decorations',$categoryData['list_image_decorations']);
		$this->goodsdisplay->set('image_size',$categoryData['list_image_size']);
		$this->goodsdisplay->set('text_align',$categoryData['list_text_align']);
		$this->goodsdisplay->set('info_settings',$categoryData['list_info_settings']);

		$this->goodsdisplay->set('displayGoodsList',$list['record']);
		$goodsDisplayHTML = "<div class='designPersonalGoodsDisplay' designElement='personalGoodsDisplay'>";
		$goodsDisplayHTML .= $this->goodsdisplay->print_(true);
		$goodsDisplayHTML .= "</div>";

		unset($_GET['sort']);
		unset($_GET['perpage']);
		$sortUrlQuerystring = getLinkFilter('',array_keys($_GET));

		$this->template->assign(array(
			'goodsDisplayHTML'		=> $goodsDisplayHTML,
			'sortUrlQuerystring'	=> $sortUrlQuerystring,
			'sort'					=> $sc['sort'],
			'sc'					=> $sc,
			'orders'				=> $this->goodsdisplay->orders,
		));


		$this->print_layout($this->template_path());

	}

	// 2013-11-26 이원희 프로모션 코드 수정
	function promotion(){
		login_check();
		if( !$this->isplusfreenot ){//무료몰인경우
			pageBack('잘못된 접근입니다.');
			exit;
		}

		$this->template->define(array('coupon_top'=>$this->skin.'/mypage/coupon_top.html'));
		$this->template->assign('myemoney',$this->mdata['emoney']);
		$this->print_layout($this->template_path());

	}

	function emoney_exchange(){
		login_check();
		if( !$this->isplusfreenot ){//무료몰인경우
			pageBack('잘못된 접근입니다.');
			exit;
		}

		### GIFT
		$today = date("Y-m-d");
		$sql = "SELECT * FROM fm_gift WHERE gift_gb = 'buy' AND start_date <= '{$today}' AND end_date >= '{$today}' AND display = 'y' ORDER BY gift_seq DESC";
		$query = $this->db->query($sql);
		$giftArray = $tmp = $query->result_array();
		$before_title = "";
		foreach($giftArray as $gift){
			if($gift['gift_seq']){
				$sql = "SELECT A.* FROM fm_gift_benefit A LEFT JOIN fm_goods B ON A.gift_goods_seq = B.goods_seq WHERE gift_seq = '{$gift['gift_seq']}' and B.goods_view = 'look' and B.goods_status = 'normal' ORDER BY sprice";
				$query = $this->db->query($sql);
				foreach($query->result_array() as $k){

					$k['goods']		= explode("|",$k['gift_goods_seq']);
					if($before_title == $k['title']){
						$k['title']		= $gift['title'];
						$k['gift_contents']		= $gift['gift_contents'];
					}

					$k['start_date']	= $gift['start_date'];
					$k['end_date']		= $gift['end_date'];
					//사은품 노출 체크
					for($i=0; $i<count($k['goods']); $i++){
						$sql	= "SELECT count(*) as cnt FROM fm_goods WHERE goods_seq = '".$k['goods'][$i]."' and  goods_view = 'look'";
						$query	= $this->db->query($sql);
						$info	= $query->result_array();
						$cnt	= $info[0]['cnt'];
						if($cnt < 1){
							unset($k['goods'][$i]);
						}
					}
					$before_title = $k['title'];
					$gift_loop[]	= $k;
				}
			}
		}
		$this->template->assign('gift_loop',$gift_loop);
		$this->template->assign('myemoney',$this->mdata['emoney']);
		$this->template->assign('gift_info',$gift);
		$this->print_layout($this->template_path());
	}

	public function export_view(){

		$this->load->model('ordermodel');
		$this->load->model('exportmodel');
		$this->load->model('buyconfirmmodel');
		$this->load->model('returnmodel');

		$file_path	= $this->template_path();

		if	(!$this->arr_step)		$this->arr_step		= config_load('step');
		if	(!$this->arr_payment)	$this->arr_payment	= config_load('payment');
		if	(!$this->cfg_order)		$this->cfg_order	= config_load('order');
		$this->template->assign(array('cfg_order'	=> $this->cfg_order));

		if(!$this->userInfo['member_seq'])
			$order_seq = $this->session->userdata('sess_order');
		else
			$order_seq	= trim($_GET['no']);

		if	(!$order_seq){
			if	($this->userInfo['member_seq'])	redirect("./order_catalog?step_type=order");
			else								redirect("/member/login?order_auth=1");
			exit;
		}

		if	($this->userInfo['member_seq'])
			$order_param	= array("member_seq"	=> $this->userInfo['member_seq']);

		$orders 			= $this->ordermodel->get_order($order_seq, $order_param);
		if($orders['step'] == 0){
			pageBack('올바른 주문이 아닙니다.');
			exit;
		}

		// 회원 정보 가져오기
		if($orders['member_seq']){
			$members = $this->membermodel->get_member_data($orders['member_seq']);
			$this->template->assign(array('members'=>$members));
		}

		$order_shippings	= $this->ordermodel->get_shipping($order_seq);
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

		// 출고 및 배송 정보
		$exports			= $this->exportmodel->get_export_for_order($order_seq);

		$export_cnt	= $buy_confirm_cnt = 0;
		foreach( $exports as $k => $data_export ){
			$export_cnt ++;
			$shipping_arr['international']		= $data_export['international'];
			if($data_export['international'] == 'domestic'){
				$shipping_arr['shipping_method']				= $shipping_arr['domestic_shipping_method'];
			}else{
				$shipping_arr['shipping_method_international']	= $shipping_arr['international_shipping_method'];
			}
			$data_export['out_shipping_method']	= $this->ordermodel->get_delivery_method($orders);
			$data_export['item']				=  $this->exportmodel->get_export_item($data_export['export_code']);
			$data_export['data_buy_confirm']	= $this->buyconfirmmodel->get_log_buy_confirm($data_export['export_seq']);

			if($data_export['international_shipping_method']){
				$data_export['mdelivery']			= $data_export['international_shipping_method'];
				$data_export['mdelivery_number']	= $data_export['international_delivery_no'];
				$data_export['tracking_url']		= "#";
				if($data_export['international_shipping_method'] != 'ups'){
					$data_export['tracking_url']	= get_delivery_company(get_international_method_code(strtoupper($data_export['international_shipping_method'])), 'url') . $data_export['international_delivery_no'];
				}
			}

			if($data_export['buy_confirm'] != 'none') {
				$buy_confirm_cnt++;
			}

			foreach( $data_export['item'] as $i => $data ){
				$it_s	= $data['item_seq'];
				$it_ops	= $data['option_seq'];

				if($data['opt_type'] == 'opt'){
					$return_item	= $this->returnmodel->get_return_item_ea($it_s, $it_ops, $data_export['export_code']);
				}
				if($data['opt_type'] == 'sub'){
					$return_item	= $this->returnmodel->get_return_subitem_ea($it_s, $it_ops, $data_export['export_code']);
				}

				$data_export['item'][$i]['inputs']	= $this->ordermodel->get_input_for_option($data['item_seq'], $data['option_seq']);

				$data_export['item'][$i]['rt_ea']	= $data['ea'] - $return_item['ea'];
				$data_export['rt_ea']				+= $data_export['item'][$i]['rt_ea'];
			}

			/* 반품신청 가능 기간 체크 */
			if($this->cfg_order['buy_confirm_use']){
				// 구매확정 사용시 출고완료일 후 n일 내에만 반품신청 가능
				if(date('Ymd') < date('Ymd',strtotime('+'.$this->cfg_order['save_term'].' day', strtotime($data_export['export_date'])))){
					$data_export['return_able_term']	= 1;
				}else{
					$data_export['return_able_term']	= 0;
				}
			}else{
				// 구매확정미 사용시 배송완료일 후 n일 내에만 반품신청 가능
				if(date('Ymd') < date('Ymd',strtotime('+'.$this->cfg_order['save_term'].' day', strtotime($data_export['complete_date'])))){
					$data_export['return_able_term']	= 1;
				}else{
					$data_export['return_able_term']	= 0;
				}
			}

			$exports[$k]	= $data_export;
		}

		if( $buy_confirm_cnt  == $export_cnt ){
			$orders['buy_confirm']	= true;
		}

		/* 배송지별 출고내역 */
		foreach($order_shippings as $k => $order_shipping){
			foreach($order_shipping['shipping_items'] as $item){
				$order_shippings[$k]['shipping_items_cnt']	+= count($item['shipping_item_option']) + count($item['shipping_item_suboption']);
			}

			foreach($exports as $export){
				if($export['shipping_seq'] == $order_shipping['shipping_seq']){
					$order_shippings[$k]['exports'][]	= $export;
				}
			}
		}

		$this->template->assign(array('order_shippings'	=> $order_shippings));
		$this->template->assign(array('shipping_policy'	=> $shipping_policy));
		$this->template->assign(array('orders'			=> $orders));
		$this->template->assign(array('exports'			=> $exports));

		$this->print_layout($this->template_path());
	}

	// 쿠폰상품 상세 화면
	public function coupon_view(){

		login_check();
		$this->load->model("exportmodel");
		$export_code		= trim($_GET['code']);

		if	(!$export_code){
			pageBack('잘못된 접근입니다.');
			exit;
		}

		// 쿠폰 상세 정보
		$coupon				= $this->exportmodel->get_coupon_info(array('export_code' => $export_code));
		if	(!$coupon['coupon_serial']){
			pageBack('쿠폰정보가 정보가 없습니다.');
			exit;
		}
		// 쿠폰 사용 내역
		$use_history		= $this->exportmodel->get_coupon_use_history($coupon['coupon_serial']);

		// 해당 상품 주문 정보
		$items				= $this->exportmodel->get_export_item($export_code);
		$item				= $items[0];

		// 쿠폰 사용 가능여부 체크
		$chk_coupon			= $this->exportmodel->chk_coupon(array('export_code' => $export_code));

		// 특수옵션 치환처리
		$coupon['option']	= get_options_print_array($item, ':');
		if	($coupon['coupon_value_type'] == 'price')	$coupon['coupon_unit']	= '원';
		else											$coupon['coupon_unit']	= '회';

		$this->template->assign(array('coupon'		=> $coupon));
		$this->template->assign(array('use_history'	=> $use_history));
		$this->template->assign(array('item'		=> $item));
		$this->template->assign(array('chk'			=> $chk_coupon));
		$this->print_layout($this->template_path());
	}

	// 쿠폰 사용하기
	public function coupon_use(){

		$this->load->model("exportmodel");
		$coupon_serial	= trim($_GET['scode']);
		$export_code	= trim($_GET['code']);

		if	(!$export_code && !$coupon_serial){
			$err_msg	= '잘못된 접근입니다.';
		}else{
			// 쿠폰 상세 정보
			if	($export_code)	$param['export_code']	= $export_code;
			else				$param['coupon_serial']	= $coupon_serial;
			$coupon			= $this->exportmodel->chk_coupon($param);
			if	($coupon['result'] == 'success'){
				// 해당 상품 주문 정보
				$address			= $coupon['address'];
				$items				= $this->exportmodel->get_export_item($export_code);
				$item				= $items[0];

				// 특수옵션 치환처리
				$coupon['option']	= get_options_print_array($item, ':');
				if	($coupon['coupon_value_type'] == 'price')	$coupon['coupon_unit']	= '원';
				else											$coupon['coupon_unit']	= '회';
			}else{
				if		($coupon['result'] == 'fail')	$err_msg = "쿠폰정보가 정보가 없습니다.";
				elseif	($coupon['result'] == 'refund')	$err_msg = "환불된 쿠폰입니다.";
				elseif	($coupon['result'] == 'notyet')	$err_msg = "사용가능한 기간이 아닙니다.";
				elseif	($coupon['result'] == 'expire')	$err_msg = "만료된 쿠폰입니다.";
				else									$err_msg = "사용할 수 없는 쿠폰입니다.";
			}
		}

		$COMMON_HEADER	= $this->skin.'/_modules/common/html_header.html';
		$this->template->assign(array('err_msg'			=> $err_msg));
		$this->template->assign(array('address'			=> $address));
		$this->template->assign(array('coupon'			=> $coupon));
		$this->template->assign(array('item'			=> $item));
		$this->template->define(array('COMMON_HEADER'	=> $COMMON_HEADER));
		$this->template->define(array('LAYOUT'			=> $this->template_path()));
		$this->template->print_('LAYOUT');
	}

	// 쿠폰상품 상세 화면
	public function my_coupon_detail(){
		$this->load->model('couponmodel');
		$download_seq	= trim($_GET['download_seq']);
		$coupon_seq		= trim($_GET['coupon_seq']);

		$data = $this->couponmodel->get_coupon($coupon_seq);
		$coupon = $this->couponmodel->get_download_coupon($download_seq);

		if ($data['issue_priod_type'] == 'day') {
			$data['issue_enddatetitle'] = ($data['after_issue_day']>0) ? '다운로드 후 '.$data['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
		}else{
			$data['issue_enddatetitle'] = substr($data['issue_enddate'], 5,2).'월 '. substr($data['issue_enddate'],8,2).'일 까지 사용가능';
		}

		$issuecategorys	= $this->couponmodel->get_coupon_issuecategory($data['coupon_seq']);

		if($issuecategorys){
			$categoryhtml = array();
			foreach($issuecategorys as $catekey =>$catedata) {
				$categoryhtml[$catekey] = $this->categorymodel -> get_category_name($catedata['category_code']);
			}
			$data['categoryhtml'] = implode(", ",$categoryhtml);
		}else{
			if($data['issue_type'] != "issue" ) {
				$data['categoryhtml'] = '전체 상품 사용 가능';
			}
		}

		if( $data['coupon_same_time'] == 'N' ) {//단독쿠폰이면
			$data['couponsametimeimg'] = 'sametime';
		}else{
			$data['couponsametimeimg'] = '';
		}

		if ($data['download_enddate']) {
			$data['download_enddatetitle'] = substr($data['download_enddate'], 5,2).'월 '. substr($data['download_enddate'],8,2).'일 까지 다운가능';
		}else{
			$data['download_enddatetitle'] = '다운로드 기간 제한 없음';
		}

		$this->template->assign(array('down_coupon'	=> $coupon));
		$this->template->assign(array('coupon'		=> $data));
		$this->print_layout($this->template_path());
	}


	// 내 할인쿠폰 사용하기
	public function my_coupon_use(){

		$download_seq	= trim($_GET['download_seq']);
		$coupon_seq	= trim($_GET['coupon_seq']);

		if	(!$download_seq || !$coupon_seq){
			$err_msg	= '잘못된 접근입니다.';
		}else{
			// 쿠폰 상세 정보
			if	($download_seq)	$param['download_seq']	= $download_seq;
			if	($coupon_seq)	$param['coupon_seq']	= $coupon_seq;

			$coupon = $this->couponmodel->get_download_coupon($download_seq);
			$data = $this->couponmodel->get_coupon($coupon_seq);

			if	(!$coupon['coupon_seq']){
				$err_msg	= '쿠폰정보가 없습니다.';
			}else{
				if		($coupon['use_status'] != 'unused')	$err_msg = "이미 사용한 쿠폰입니다.";
			}
		}

		$COMMON_HEADER	= $this->skin.'/_modules/common/html_header.html';
		$this->template->assign(array('err_msg'			=> $err_msg));
		$this->template->assign(array('coupon'			=> $data));
		$this->template->define(array('COMMON_HEADER'	=> $COMMON_HEADER));
		$this->template->define(array('LAYOUT'			=> $this->template_path()));
		$this->template->print_('LAYOUT');
	}

	/* 우측 퀵메뉴 wish 리스트 삭제 */
	public function quickWishDel() {
		$msg="fail";
		if($this->userInfo['member_seq']){
			$this->load->model('wishmodel');
			$wish_seq = $_POST['wish_seq'];
			$this->wishmodel->del(array($wish_seq));
			$msg="ok";
		}
		echo $msg;
	}
}