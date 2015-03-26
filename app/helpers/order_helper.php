<?php
/**
 * @author lgs
 * @version 1.0.0
 * @license copyright by GABIA_lgs
 * @since 12. 5. 31 11:19
 */

// 상품(옵션) 재고 체크
function check_stock_option($goods_seq,$option1,$option2,$option3,$option4,$option5,$ea,$cfg='',$mode='cart') {
	$CI =& get_instance();
	if(!$cfg ) $cfg = config_load('order');

	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');
	if(!$CI->ordermodel) $CI->load->model('ordermodel');

	$data_goods = $CI->goodsmodel->get_goods($goods_seq);
	if($data_goods['runout_policy']){//개별세팅
		$cfg['runout'] = $data_goods['runout_policy'];
		$cfg['ableStockLimit'] = $data_goods['able_stock_limit'];
	}

	if( $cfg['runout'] != 'unlimited' ){
		if( $cfg['runout'] == 'stock' ) $able_stock_limit = 0;
		else $able_stock_limit = (int) $cfg['ableStockLimit'];
	}else{
		return true;
	}

	$option_stock = $CI->goodsmodel->get_goods_option_stock(
		$goods_seq,
		$option1,
		$option2,
		$option3,
		$option4,
		$option5
	);

	// 출고예약량
	if( $cfg['runout'] == 'ableStock' ){
		$reservation = $CI->ordermodel->get_option_reservation(
			$cfg['ableStockStep'],
			$goods_seq,
			$option1,
			$option2,
			$option3,
			$option4,
			$option5
		);
	}

	$sale_able_stock = (int) $option_stock - (int) $reservation - (int) $able_stock_limit;
	$stock = $sale_able_stock  - (int) $ea;
	if($mode == 'cart'){
		if( $stock < 0 ) return false;
	}else if($mode == 'view'){
		if( $stock <= 0 ) return false;
	}else if($mode == 'view_stock'){
		$result_sale_able_stock = $sale_able_stock;
		if($sale_able_stock < 0) $result_sale_able_stock = 0;
		return array('stock'=>$stock,'able_stock'=>$stock,'runout'=>$cfg['runout'],'sale_able_stock'=>$result_sale_able_stock);
	}

	return true;
}

// 상품(추가옵션) 재고 체크
function check_stock_suboption($goods_seq,$title,$option,$ea,$cfg='',$mode='cart') {
	$CI =& get_instance();
	if(!$cfg ) $cfg = config_load('order');
	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');
	if(!$CI->ordermodel) $CI->load->model('ordermodel');

	$data_goods = $CI->goodsmodel->get_goods($goods_seq);
	if($data_goods['runout_policy']){//개별재고
		$cfg['runout'] = $data_goods['runout_policy'];
		$cfg['ableStockLimit'] = $data_goods['able_stock_limit'];
	}

	if( $cfg['runout'] != 'unlimited' ){
		if( $cfg['runout'] == 'stock' ) $able_stock_limit = 0;
		else $able_stock_limit = (int) $cfg['ableStockLimit'];
	}else{
		return true;
	}

	$option_stock = $CI->goodsmodel->get_goods_suboption_stock(
		$goods_seq,
		$title,
		$option
	);

	// 출고예약량
	if( $cfg['runout'] == 'ableStock' ){
		$reservation = $CI->ordermodel->get_suboption_reservation(
			$cfg['ableStockStep'],
			$goods_seq,
			$title,
			$option
		);
	}

	$sale_able_stock = (int) $option_stock - (int) $reservation - (int) $able_stock_limit;
	$stock = $sale_able_stock  - (int) $ea;

	if($mode == 'cart'){
		if( $stock < 0 ) return false;
	}else if($mode == 'view'){
		if( $stock <= 0 ) return false;
	}else if($mode == 'view_stock'){
		$result_sale_able_stock = $sale_able_stock;
		if($sale_able_stock < 0) $result_sale_able_stock = 0;
		return array('stock'=>$stock,'able_stock'=>$stock,'runout'=>$cfg['runout'],'sale_able_stock'=>$result_sale_able_stock);
	}

	return true;
}

// 주문접수 메일
function send_mail_step15($order_seq){
	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$arr_step 		= config_load('step');
	$arr_payment 	= config_load('payment');

	$order 	= $CI->ordermodel->get_order($order_seq);
	$order['mstep']		= $arr_step[$order['step']];
	$order['mpayment']	= $arr_payment[$order['payment']];

	$items 				= $CI->ordermodel->get_item($order_seq);
	$data_opt			= $CI->ordermodel->get_item_option($order_seq);
	$data_sub 			= $CI->ordermodel->get_item_suboption($order_seq);
	$order_shippings	= $CI->ordermodel->get_shipping($order_seq);
	$order['coupon_sale']					= $order_shippings['shipping_coupon_sale'];
	$order['shipping_promotion_code_sale']	= $order_shippings['shipping_promotion_code_sale'];

	if($order['international']=='international'){
		$order['shipping_cost'] = $order['international_cost'];
	}

	$order['goods_shipping_cost'] = 0;
	foreach($items as $key=>$item){
		$order['goods_shipping_cost']	+= $item['goods_shipping_cost'];
	}

	foreach($data_opt as $data){
		$goods[$data['item_option_seq']]['goods_name'] = $data['goods_name'];
		$goods[$data['item_option_seq']]['ea'] += $data['ea'];
		$goods[$data['item_option_seq']]['price'] += $data['price']*$data['ea'];

		$options_arr	= get_options_print_array($data, ':');
		if	($options_arr)
			$goods[$data['item_option_seq']]['options']	= implode(' / ', $options_arr);
		unset($options_arr);

		// 값어치
		if	($data['goods_kind'] == 'coupon' && $data['coupon_input'] > 0){
			if	($data['socialcp_input_type'] == 'pass')
				$goods[$data['item_option_seq']]['options']	.= ' ['.number_format($data['coupon_input']).'회]';
			else
				$goods[$data['item_option_seq']]['options']	.= ' ['.number_format($data['coupon_input']).'원]';
		}

		//promotion sale
		$order['goods_coupon_sale']			+= $data['coupon_sale'];
		$order['member_sale']				+= $data['member_sale']*$data['ea'];
		$order['fblike_sale']				+= $data['fblike_sale'];
		$order['referer_sale']				+= $data['referer_sale'];
		$order['goods_mobile_sale']			+= $data['mobile_sale'];
		$order['goods_fblike_sale']			+= $data['fblike_sale'];
		$order['goods_promotion_code_sale']	+= $data['promotion_code_sale'];

		$order['price'] += $data['price']*$data['ea'];

		//member use
		$order['reserve'] 	+= $data['reserve']*$data['ea'];
		$order['point'] 	+= $data['point']*$data['ea'];

		$goods[$data['item_option_seq']]['inputs']	= 	$CI->ordermodel->get_input_for_option($data['item_seq'], $data['item_option_seq']);
	}

	if($data_sub) foreach($data_sub as $data){
		//promotion sale
		$order['goods_coupon_sale']			+= $data['coupon_sale'];
		$order['member_sale']				+= $data['member_sale']*$data['ea'];
		$order['fblike_sale']				+= $data['fblike_sale'];
		$order['referer_sale']				+= $data['referer_sale'];
		$order['goods_mobile_sale']			+= $data['mobile_sale'];
		$order['goods_fblike_sale']			+= $data['fblike_sale'];
		$order['goods_promotion_code_sale']	+= $data['promotion_code_sale'];

		$order['price']						+= $data['price']*$data['ea'];
		$order['goods_price']				+= $data['price']*$data['ea'];
		$data['price']						= $data['price'] * $data['ea'];
		$goods[$data['item_option_seq']]['sub'][]	= $data;
	}

	if( $order['recipient_address_street'] && $order['recipient_address_type'] != 'zibun' ) $order['recipient_address'] = $order['recipient_address_street'];

	$file_path	= "../../data/email/order.html";
	$CI->template->assign(array('order'=>$order,'goods'=>$goods,'tot'=>$tot,'order_shippings'=>$order_shippings));
	$CI->template->compile_dir = ROOTPATH."data/email/";
	$CI->template->define(array('tpl'=>$file_path));
	$out = $CI->template->fetch("tpl");
	$email = config_load('email');
	$email['order_skin']	= $out;
	$email['member_seq']	= $order['member_seq'];
	$email['shopName']		= $CI->config_basic['shopName'];
	$email['ordno']			= $order['order_seq'];
	$email['user_name']	= $order['order_user_name'];

	sendMail($order['order_email'], 'order', '', $email);
}

// 결제확인 메일
function send_mail_step25($order_seq){
	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$arr_step 		= config_load('step');
	$arr_payment 	= config_load('payment');

	$order				= $CI->ordermodel->get_order($order_seq);
	$order_shippings	= $CI->ordermodel->get_shipping($order_seq);
	$order['coupon_sale']					= $order_shippings['shipping_coupon_sale'];
	$order['shipping_promotion_code_sale']	= $order_shippings['shipping_promotion_code_sale'];
	$order['mstep']							= $arr_step[$order['step']];
	$order['mpayment']						= $arr_payment[$order['payment']];

	$items 		= $CI->ordermodel->get_item($order_seq);
	$data_opt 	= $CI->ordermodel->get_item_option($order_seq);
	$data_sub 	= $CI->ordermodel->get_item_suboption($order_seq);

	if($order['international']=='international'){
		$order['shipping_cost'] = $order['international_cost'];
	}

	$order['goods_shipping_cost'] = 0;
	foreach($items as $key=>$item){
		$order['goods_shipping_cost']	+= $item['goods_shipping_cost'];
	}

	foreach($data_opt as $data){
		$goods[$data['item_option_seq']]['goods_name'] = $data['goods_name'];
		$goods[$data['item_option_seq']]['ea'] += $data['ea'];
		$goods[$data['item_option_seq']]['price'] += $data['price']*$data['ea'];

		$options_arr	= get_options_print_array($data, ':');
		if	($options_arr)
			$goods[$data['item_option_seq']]['options']	= implode(' / ', $options_arr);
		unset($options_arr);

		// 값어치
		if	($data['goods_kind'] == 'coupon' && $data['coupon_input'] > 0){
			if	($data['socialcp_input_type'] == 'pass')
				$goods[$data['item_option_seq']]['options']	.= ' ['.number_format($data['coupon_input']).'회]';
			else
				$goods[$data['item_option_seq']]['options']	.= ' ['.number_format($data['coupon_input']).'원]';
		}

		//promotion sale
		$order['goods_coupon_sale']			+= $data['coupon_sale'];
		$order['member_sale']				+= $data['member_sale']*$data['ea'];
		$order['fblike_sale']				+= $data['fblike_sale'];
		$order['referer_sale']				+= $data['referer_sale'];
		$order['goods_mobile_sale']			+= $data['mobile_sale'];
		$order['goods_fblike_sale']			+= $data['fblike_sale'];
		$order['goods_promotion_code_sale']	+= $data['promotion_code_sale'];

		$order['price'] += $data['price']*$data['ea'];
		$order['reserve'] 	+= $data['reserve']*$data['ea'];
		$order['point'] 	+= $data['point']*$data['ea'];

		$goods[$data['item_option_seq']]['inputs']	= 	$CI->ordermodel->get_input_for_option($data['item_seq'], $data['item_option_seq']);
	}

	if($data_sub) foreach($data_sub as $data){
		//promotion sale
		$order['goods_coupon_sale']			+= $data['coupon_sale'];
		$order['member_sale']				+= $data['member_sale']*$data['ea'];
		$order['fblike_sale']				+= $data['fblike_sale'];
		$order['referer_sale']				+= $data['referer_sale'];
		$order['goods_mobile_sale']			+= $data['mobile_sale'];
		$order['goods_fblike_sale']			+= $data['fblike_sale'];
		$order['goods_promotion_code_sale']	+= $data['promotion_code_sale'];

		$order['price']						+= $data['price']*$data['ea'];
		$order['goods_price']				+= $data['price']*$data['ea'];
		$data['price']						= $data['price'] * $data['ea'];
		$goods[$data['item_option_seq']]['sub'][]	= $data;
	}

	if( $order['recipient_address_street'] && $order['recipient_address_type'] != 'zibun' ) $order['recipient_address'] = $order['recipient_address_street'];

	$file_path	= "../../data/email/settle.html";
	$CI->template->assign(array('order'=>$order,'goods'=>$goods));
	$CI->template->compile_dir = ROOTPATH."data/email/";
	$CI->template->define(array('tpl'=>$file_path));
	$out = $CI->template->fetch("tpl");

	$email = config_load('email');
	$email['settle_skin'] = $out;
	$email['member_seq'] = $order['member_seq'];
	$email['shopName']		= $CI->config_basic['shopName'];
	$email['ordno']				= $order['order_seq'];
	$email['user_name']	= $order['order_user_name'];

	sendMail($order['order_email'], 'settle', '', $email);
}

// 출고완료 메일
function send_mail_step55($export_code){
	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');
	$arr_delivery = config_load('delivery_url');

	$export = $CI->exportmodel -> get_export($export_code);
	$order = $CI->ordermodel -> get_order($export['order_seq']);
	$item = $CI->exportmodel -> get_export_item($export_code);

	$order['international'] = $export['international'];
	$order['shipping_method'] = $export['domestic_shipping_method'];

	// 배송방법
	$order['mshipping'] = $CI->ordermodel->get_delivery_method($order);

	if($export['international'] == 'domestic'){
		if($export['domestic_shipping_method'] == 'delivery'){
			$tmp = config_load('delivery_url',$export['delivery_company_code']);
			$export['mdelivery'] = $arr_delivery[$export['delivery_company_code']]['company'];
			$export['mdelivery_number'] = $export['delivery_number'];
			$export['tracking_url'] = $arr_delivery[$export['delivery_company_code']]['url'].$export['delivery_number'];
		}
	}else{
		$export['mdelivery'] = $export['international_shipping_method'];
		$export['mdelivery_number'] = $export['international_delivery_no'];
	}

	foreach($item  as $row){
		$options_arr	= get_options_print_array($row, ':');
		if	($options_arr)		$row['options_str']	= implode(' / ', $options_arr);
		unset($options_arr);

		if	($row['opt_type'] == 'sub'){
			$row['price']								= $row['price'] * $row['ea'];
			$row['sub_options']							= $row['options_str'];
			if	($first_option_seq)
				$goods[$first_option_seq]['sub'][]		= $row;
			else
				$goods[$row['option_seq']]['sub'][]		= $row;
		}else{
			$goods[$row['option_seq']]['price']			+= $row['price'] * $row['ea'];
			$goods[$row['option_seq']]['ea']			+= $row['ea'];
			$goods[$row['option_seq']]['goods_name']	= $row['goods_name'];
			$goods[$row['option_seq']]['options']		= $row['options_str'];
			$goods[$row['option_seq']]['inputs']		= $CI->ordermodel->get_input_for_option($row['item_seq'], $row['option_seq']);
		}
		if	(!$first_option_seq)	$first_option_seq	= $row['option_seq'];
	}

	if( $order['recipient_address_street'] && $order['recipient_address_type'] != 'zibun' ) $order['recipient_address'] = $order['recipient_address_street'];

	$file_path	= "../../data/email/released.html";
	$CI->template->assign(array('order'=>$order,'goods'=>$goods,'export'=>$export));
	$CI->template->compile_dir = ROOTPATH."data/email/";
	$CI->template->define(array('tpl'=>$file_path));
	$out = $CI->template->fetch("tpl");

	$email = config_load('email');
	$email['released_skin'] = $out;
	$email['member_seq']	= $order['member_seq'];
	$email['shopName']		= $CI->config_basic['shopName'];
	$email['ordno']			= $order['order_seq'];
	$email['user_name']		= $order['order_user_name'];
	$email['export_code']	= $export_code;

	sendMail($order['order_email'], 'released', '', $email);
}

// 배송완료 메일
function send_mail_step75($export_code){
	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');
	$arr_delivery = config_load('delivery_url');

	$export = $CI->exportmodel -> get_export($export_code);
	$order = $CI->ordermodel -> get_order($export['order_seq']);
	$item = $CI->exportmodel -> get_export_item($export_code);

	$order['international'] = $export['international'];
	$order['shipping_method'] = $export['domestic_shipping_method'];

	// 배송방법
	$order['mshipping'] = $CI->ordermodel->get_delivery_method($order);

	if($export['international'] == 'domestic'){
		if($export['domestic_shipping_method'] == 'delivery'){
			$tmp = config_load('delivery_url',$export['delivery_company_code']);
			$export['mdelivery'] = $arr_delivery[$export['delivery_company_code']]['company'];
			$export['mdelivery_number'] = $export['delivery_number'];
			$export['tracking_url'] = $arr_delivery[$export['delivery_company_code']]['url'].$export['delivery_number'];
		}
	}else{
		$export['mdelivery'] = $export['international_shipping_method'];
		$export['mdelivery_number'] = $export['international_delivery_no'];
	}

	foreach($item  as $row){
		$options_arr	= get_options_print_array($row, ':');
		if	($options_arr)		$row['options_str']	= implode(' / ', $options_arr);
		unset($options_arr);

		if	($row['opt_type'] == 'sub'){
			$row['price']								= $row['price'] * $row['ea'];
			$row['sub_options']							= $row['options_str'];
			if	($first_option_seq)
				$goods[$first_option_seq]['sub'][]		= $row;
			else
				$goods[$row['option_seq']]['sub'][]		= $row;
		}else{
			$goods[$row['option_seq']]['price']			+= $row['price'] * $row['ea'];
			$goods[$row['option_seq']]['ea']			+= $row['ea'];
			$goods[$row['option_seq']]['goods_name']	= $row['goods_name'];
			$goods[$row['option_seq']]['options']		= $row['options_str'];
			$goods[$row['option_seq']]['inputs']		= $CI->ordermodel->get_input_for_option($row['item_seq'], $row['option_seq']);
		}
		if	(!$first_option_seq)	$first_option_seq	= $row['option_seq'];
	}

	if( $order['recipient_address_street'] && $order['recipient_address_type'] != 'zibun' ) $order['recipient_address'] = $order['recipient_address_street'];

	$file_path	= "../../data/email/delivery.html";
	$CI->template->assign(array('order'=>$order,'goods'=>$goods,'export'=>$export));
	$CI->template->compile_dir = ROOTPATH."data/email/";
	$CI->template->define(array('tpl'=>$file_path));
	$out = $CI->template->fetch("tpl");

	$email = config_load('email');
	$email['delivery_skin'] = $out;
	$email['member_seq'] = $order['member_seq'];
	$email['shopName']		= $CI->config_basic['shopName'];
	$email['ordno']				= $order['order_seq'];
	$email['user_name']	= $order['order_user_name'];

	sendMail($order['order_email'], 'delivery', '', $email);
}

// 쿠폰상품 출고완료 메일
function coupon_send_mail_step55($export_code, $send_email){
	if	($send_email){
		$CI =& get_instance();
		$CI->load->model('ordermodel');
		$CI->load->model('exportmodel');

		$export	= $CI->exportmodel -> get_export($export_code);
		$order	= $CI->ordermodel -> get_order($export['order_seq']);
		$items	= $CI->exportmodel -> get_export_item($export_code);
		$item	= $items[0];

		// 옵션 문자열화
		$options_arr	= get_options_print_array($item, ':');
		if	($options_arr)		$item['options_str']	= implode(' / ', $options_arr);

		// 취소 정책
		$config_cancel	= $CI->ordermodel -> get_order($export['order_seq'], $item['item_seq']);

		// 쿠폰번호
		$export['coupon_serial']	= $item['coupon_serial'];
		// 회차
		$export['couponNum']		= $item['coupon_st'].'/'.$item['opt_ea'];
		// 값어치
		if	($item['coupon_input'] > 0){
			$export['exists_option']++;
			if	($item['socialcp_input_type'] == 'pass'){
				$export['coupon_input_count']	= $item['coupon_input'];
				$item['options_str']			.= '[' . number_format($item['coupon_input']) . '회]';
			}else{
				$export['coupon_input_price']	= $item['coupon_input'];
				$item['options_str']			.= '[' . number_format($item['coupon_input']) . '원]';
			}
		}
		// 옵션 목록화
		$export['optionlist']	= $options_arr;
		$export['cancel_rule']	= 'option';
		$export['cancel_day']	= $config_cancel['socialcp_cancel_day'];
		$export['refund_rule']	= $config_cancel['socialcp_use_return'];
		if	($config_cancel['socialcp_cancel_type'] == 'pay')	$export['cancel_rule']	= 'pay';

		if	($item['opt_type'] == 'sub'){
			$item['price']							= $item['price'] * $item['ea'];
			$item['sub_options']					= $item['options_str'];
			$goods[$item['option_seq']]['sub'][]	= $item;
		}else{
			$goods[$item['option_seq']]['price']		+= $item['price'] * $item['ea'];
			$goods[$item['option_seq']]['ea']			+= $item['ea'];
			$goods[$item['option_seq']]['goods_name']	= $item['goods_name'];
			$goods[$item['option_seq']]['options']		= $item['options_str'];
			$goods[$item['option_seq']]['inputs']		= $CI->ordermodel->get_input_for_option($item['item_seq'], $item['option_seq']);
		}

		if( $order['recipient_address_street'] && $order['recipient_address_type'] != 'zibun' ) $order['recipient_address'] = $order['recipient_address_street'];

		$file_path	= "../../data/email/coupon_released.html";
		$CI->template->assign(array('order'=>$order,'goods'=>$goods,'export'=>$export));
		$CI->template->compile_dir = ROOTPATH."data/email/";
		$CI->template->define(array('tpl'=>$file_path));
		$out = $CI->template->fetch("tpl");

		$email = config_load('email');
		$email['released_skin']	= $out;
		$email['member_seq']	= $order['member_seq'];
		$email['shopName']		= $CI->config_basic['shopName'];
		$email['ordno']				= $order['order_seq'];
		$email['user_name']	= $order['order_user_name'];

		$result	= sendMail($send_email, 'coupon_released', '', $email);
		return $result;
	}

	return false;
}

// 쿠폰상품 배송완료 메일
function coupon_send_mail_step75($export_code){
	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');

	$export			= $CI->exportmodel -> get_export($export_code);
	$order			= $CI->ordermodel -> get_order($export['order_seq']);
	$items			= $CI->exportmodel -> get_export_item($export_code);
	$uselog			= $CI->exportmodel -> get_coupon_use_history($items[0]['coupon_serial']);
	$item			= $items[0];
	$send_email		= $item['recipient_email'];

	// 쿠폰 사용 내역
	$export['coupon_remain']		= $item['coupon_remain_value'];
	$export['used_time']			= date('Y년 m월 d일 H시 i분', strtotime($uselog[0]['regist_date']));
	$export['coupon_used']			= $uselog[0]['coupon_use_value'];
	$export['used_location']		= $uselog[0]['coupon_use_area'];
	$export['confirm_person']		= $uselog[0]['confirm_user'];

	// 옵션 문자열화
	$options_arr	= get_options_print_array($item, ':');
	if	($options_arr)		$item['options_str']	= implode(' / ', $options_arr);

	// 취소 정책
	$config_cancel	= $CI->ordermodel -> get_order($export['order_seq'], $item['item_seq']);

	// 쿠폰번호
	$export['coupon_serial']	= $item['coupon_serial'];
	// 회차
	$export['couponNum']		= $item['coupon_st'].'/'.$item['opt_ea'];
	// 값어치
	if	($item['coupon_input'] > 0){
		$export['exists_option']++;
		if	($item['socialcp_input_type'] == 'pass'){
			$export['coupon_input_count']	= $item['coupon_input'];
			$item['options_str']			.= '[' . number_format($item['coupon_input']) . '회]';
		}else{
			$export['coupon_input_price']	= $item['coupon_input'];
			$item['options_str']			.= '[' . number_format($item['coupon_input']) . '원]';
		}
	}
	// 옵션 목록화
	$export['optionlist']	= $options_arr;
	$export['cancel_rule']	= 'option';
	$export['cancel_day']	= $config_cancel['socialcp_cancel_day'];
	$export['refund_rule']	= $config_cancel['socialcp_use_return'];
	if	($config_cancel['socialcp_cancel_type'] == 'pay')	$export['cancel_rule']	= 'pay';

	if	($item['opt_type'] == 'sub'){
		$item['price']							= $item['price'] * $item['ea'];
		$item['sub_options']					= $item['options_str'];
		$goods[$item['option_seq']]['sub'][]	= $item;
	}else{
		$goods[$item['option_seq']]['price']		+= $item['price'] * $item['ea'];
		$goods[$item['option_seq']]['ea']			+= $item['ea'];
		$goods[$item['option_seq']]['goods_name']	= $item['goods_name'];
		$goods[$item['option_seq']]['options']		= $item['options_str'];
		$goods[$item['option_seq']]['inputs']		= $CI->ordermodel->get_input_for_option($item['item_seq'], $item['option_seq']);
	}

	if( $order['recipient_address_street'] && $order['recipient_address_type'] != 'zibun' ) $order['recipient_address'] = $order['recipient_address_street'];

	$file_path	= "../../data/email/coupon_delivery.html";
	$CI->template->assign(array('order'=>$order,'goods'=>$goods,'export'=>$export));
	$CI->template->compile_dir = ROOTPATH."data/email/";
	$CI->template->define(array('tpl'=>$file_path));
	$out = $CI->template->fetch("tpl");

	$email = config_load('email');
	$email['released_skin']	= $out;
	$email['member_seq']	= $order['member_seq'];
	$email['shopName']		= $CI->config_basic['shopName'];
	$email['ordno']				= $order['order_seq'];
	$email['user_name']	= $order['order_user_name'];

	$result	= sendMail($send_email, 'coupon_delivery', '', $email);
	return $result;
}

// 쿠폰상품 출고완료 SMS
function coupon_send_sms_step55($export_code, $send_sms, $send_sms2=''){

	if	($send_sms){
		$CI =& get_instance();
		$CI->load->model('ordermodel');
		$CI->load->model('exportmodel');

		$config_system	= config_load('system');
		$export			= $CI->exportmodel -> get_export($export_code);
		$order			= $CI->ordermodel -> get_order($export['order_seq']);
		$items			= $CI->exportmodel -> get_export_item($export_code);
		$item			= $items[0];
		$providerList[]	= $item['provider_seq'];

		$params['shopName']		= $CI->config_basic['shopName'];
		$params['ordno']		= $order['order_seq'];
		$params['user_name']	= $order['order_user_name'];
		$params['member_seq']	= $order['member_seq'];						// 회원seq

		// 치환코드 시작
		$params['goods_name']	= $item['goods_name'];						// 상품명
		$params['coupon_serial']= $item['coupon_serial'];					// 쿠폰번호
		$params['couponNum']	= $item['coupon_st'].'/'.$item['opt_ea'];	// 회차
		// 치환코드 끝

		// 값어치
		if	($item['coupon_input'] > 0){
			if	($item['socialcp_input_type'] == 'pass')
				$params['coupon_value']	= '사용가능횟수 : '.number_format($item['coupon_input']);
			else
				$params['coupon_value']	= '사용가능금액 : '.number_format($item['coupon_input']);
		}
		// 옵션
		$options_arr	= get_options_print_array($item, ':');
		if	($options_arr)		$params['options']	= implode(chr(10), $options_arr);
		

		$CI->coupon_reciver_sms['order_cellphone'][] = $send_sms;
		$CI->coupon_reciver_sms['params'][] = $params;
		$CI->coupon_reciver_sms['order_no'][] = $order['order_seq'];

		// 재발송용 추가
		$CI->resend_sms_common_data['coupon_released']['phone'][]		= $send_sms;
		$CI->resend_sms_common_data['coupon_released']['params'][]		= $params;
		$CI->resend_sms_common_data['coupon_released']['order_no'][]	= $order['order_seq'];

		# 주문자와 받는분이 다를때 주문자에게도 문자 전송
		if( $send_sms2 && (ereg_replace("[^0-9]", "", $send_sms) !=  ereg_replace("[^0-9]", "", $send_sms2))){
			$CI->coupon_order_sms['order_cellphone'][] = $send_sms2;
			$CI->coupon_order_sms['params'][] = $params;
			$CI->coupon_order_sms['order_no'][] = $order['order_seq'];
		}

		return true;
	}

	return false;
}


// 쿠폰상품 배송완료 SMS
function coupon_send_sms_step75($export_code){

	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');

	$config_system	= config_load('system');
	$export			= $CI->exportmodel -> get_export($export_code);
	$order			= $CI->ordermodel -> get_order($export['order_seq']);
	$items			= $CI->exportmodel -> get_export_item($export_code);
	$uselog			= $CI->exportmodel -> get_coupon_use_history($items[0]['coupon_serial']);
	$item			= $items[0];
	$send_sms		= $item['recipient_cellphone'];
	$send_sms2		= $order['order_cellphone'];        //주문자
	$providerList[]	= $item['provider_seq'];
	$params['shopName']		= $CI->config_basic['shopName'];
	$params['ordno']		= $order['order_seq'];
	$params['user_name']	= $order['order_user_name'];
	$params['member_seq']	= $order['member_seq'];

	// 쿠폰 사용 내역
	$params['used_time']			= date('Y년 m월 d일 H시 i분', strtotime($uselog[0]['regist_date']));
	$params['used_location']		= $uselog[0]['coupon_use_area'];
	$params['confirm_person']		= $uselog[0]['confirm_user'];

	// 치환코드
	$params['goods_name']			= $item['goods_name'];						// 상품명
	$params['coupon_serial']		= $item['coupon_serial'];					// 쿠폰번호
	$params['couponNum']			= $item['coupon_st'].'/'.$item['opt_ea'];	// 회차

	//회원일경우 userid 불러오기
	if(trim($order['member_seq'])){
		$CI->load->model('membermodel');
		$userid		= $CI->membermodel->get_member_userid(trim($order['member_seq']));
	}

	if($userid)	$params['userid']	= $userid;									// 회원id(존재할 경우에만)
	$params['order_user']			= $order['order_user_name'];				//주문자명
	$params['recipient_user']		= $order['recipient_user_name'];			//수취인명
	// 치환코드 끝

	// 값어치
	if	($item['coupon_input'] > 0){
		if	($item['socialcp_input_type'] == 'pass'){
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'회';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'회 사용';
			$params['coupon_value']		= '사용가능횟수 : '.number_format($item['coupon_input']);
		}else{
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'원';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'원 사용';
			$params['coupon_value']		= '사용가능금액 : '.number_format($item['coupon_input']);
		}
	}
	// 옵션
	$options_arr	= get_options_print_array($item, ':');
	if	($options_arr)		$params['options']	= implode(chr(10), $options_arr);


	$CI->exportSmsData['coupon_delivery']['phone'][] = $send_sms;
	$CI->exportSmsData['coupon_delivery']['params'][] = $params;
	$CI->exportSmsData['coupon_delivery']['order_no'][] = $order['order_seq'];


	//$result	= sendSMS($send_sms, 'coupon_delivery', '', $params);
	# 주문자와 받는분이 다를때 주문자에게도 문자 전송
	if( $send_sms2 && (ereg_replace("[^0-9]", "", $send_sms) !=  ereg_replace("[^0-9]", "", $send_sms2))){
		$CI->exportSmsData['coupon_delivery2']['phone'][] = $send_sms2;
		$CI->exportSmsData['coupon_delivery2']['params'][] = $params;
		$CI->exportSmsData['coupon_delivery2']['order_no'][] = $order['order_seq'];

		//sendSMS($send_sms2, 'coupon_delivery2', '', $params);          //주문자
	}

	return $result;
}

// 쿠폰상품 결제취소완료 SMS
function coupon_send_sms_cancel($export_code,$order){

	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');

	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');
	$export			= $CI->exportmodel -> get_export($export_code);
	//$order			= $CI->ordermodel -> get_order($export['order_seq']);
	$items			= $CI->exportmodel -> get_export_item($export_code);
	$uselog			= $CI->exportmodel -> get_coupon_use_history($items[0]['coupon_serial']);
	$item			= $items[0];
	$send_sms		= $item['recipient_cellphone'];
	$providerList[]	= $item['provider_seq'];
	$params['shopName']		= $CI->config_basic['shopName'];
	$params['ordno']			= $order['order_seq'];
	$params['user_name']		= $order['order_user_name'];
	$params['member_seq']		= $order['member_seq'];

	// 쿠폰 사용 내역
	$params['used_time']			= date('Y년 m월 d일 H시 i분', strtotime($uselog[0]['regist_date']));
	$params['used_location']		= $uselog[0]['coupon_use_area'];
	$params['confirm_person']		= $uselog[0]['confirm_user'];

	// 치환코드
	$params['goods_name']			= $item['goods_name'];						// 상품명
	$params['coupon_serial']		= $item['coupon_serial'];					// 쿠폰번호
	$params['couponNum']			= $item['coupon_st'].'/'.$item['opt_ea'];	// 회차

	// 치환코드 끝

	// 값어치
	if	($item['coupon_input'] > 0){
		if	($item['socialcp_input_type'] == 'pass'){
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'회';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'회 사용';
			$params['coupon_value']		= '사용가능횟수 : '.number_format($item['coupon_input']);
		}else{
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'원';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'원 사용';
			$params['coupon_value']		= '사용가능금액 : '.number_format($item['coupon_input']);
		}
	}

	// 옵션
	$options_arr	= get_options_print_array($item, ':');
	if	($options_arr)		$params['options']	= implode(chr(10), $options_arr);

	//SMS 데이터 생성
	$commonSmsData['coupon_cancel']['phone'][] = $send_sms;
	$commonSmsData['coupon_cancel']['params'][] = $params;
	$commonSmsData['coupon_cancel']['order_no'][] = $order['order_seq'];
	$result = commonSendSMS($commonSmsData);

	//$result	= sendSMS($send_sms, 'coupon_cancel', '', $params);
	return $result;
}

// 쿠폰상품 환불완료 SMS
function coupon_send_sms_refund($export_code,$order){

	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');

	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');
	$export			= $CI->exportmodel -> get_export($export_code);
	//$order			= $CI->ordermodel -> get_order($export['order_seq']);
	$items			= $CI->exportmodel -> get_export_item($export_code);
	$uselog			= $CI->exportmodel -> get_coupon_use_history($items[0]['coupon_serial']);
	$item			= $items[0];
	$send_sms		= $item['recipient_cellphone'];
	$providerList[]	= $item['provider_seq'];
	$params['shopName']		= $CI->config_basic['shopName'];
	$params['ordno']			= $order['order_seq'];
	$params['user_name']		= $order['order_user_name'];
	$params['member_seq']		= $order['member_seq'];

	// 쿠폰 사용 내역
	$params['used_time']			= date('Y년 m월 d일 H시 i분', strtotime($uselog[0]['regist_date']));
	$params['used_location']		= $uselog[0]['coupon_use_area'];
	$params['confirm_person']		= $uselog[0]['confirm_user'];

	// 치환코드
	$params['goods_name']			= $item['goods_name'];						// 상품명
	$params['coupon_serial']		= $item['coupon_serial'];					// 쿠폰번호
	$params['couponNum']			= $item['coupon_st'].'/'.$item['opt_ea'];	// 회차

	// 치환코드 끝

	// 값어치
	if	($item['coupon_input'] > 0){
		if	($item['socialcp_input_type'] == 'pass'){
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'회';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'회 사용';
			$params['coupon_value']		= '사용가능횟수 : '.number_format($item['coupon_input']);
		}else{
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'원';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'원 사용';
			$params['coupon_value']		= '사용가능금액 : '.number_format($item['coupon_input']);
		}
	}
	// 옵션
	$options_arr	= get_options_print_array($item, ':');
	if	($options_arr)		$params['options']	= implode(chr(10), $options_arr);

	//SMS 데이터 생성
	$commonSmsData['coupon_refund']['phone'][] = $send_sms;
	$commonSmsData['coupon_refund']['params'][] = $params;
	$commonSmsData['coupon_refund']['order_no'][] = $order['order_seq'];
	$result = commonSendSMS($commonSmsData);

	//$result	= sendSMS($send_sms, 'coupon_refund', '', $params);
	return $result;
}


// 필수옵션 title + option형태로 배열화
function get_options_print_array($param, $division) {
	$CI =& get_instance();
	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');

	// 특수옵션 처리
	if	($param['newtype']){
		// 특수옵션 유효기간 ( 유효기간 날짜로 변경 )
		if	($param['social_start_date'] && $param['social_end_date']){
			$expire_arr				= array('dayauto', 'date', 'dayinput');
			$sp_option['expire']	= true;
		}
		// 특수옵션 주소 ( 연락처 추가 )
		if	($param['biztel']){
			$address_arr			= array('address');
			$sp_option['address']	= true;
		}

		$dayauto_type = ($param['dayauto_type']  == 'day')?"이후":"";
		$newtype	= explode(',', $param['newtype']);
		foreach($newtype as $k => $types){
			if	($sp_option['expire'] && in_array($types, $expire_arr)){
				$key					= $k + 1;
				if( $types == 'date' ) {
					$param['option'.$key.'_datetitle'] = '';
					//$param['option'.$key]	= $param['codedate'];
				}elseif( $types == 'dayauto' ) {
					$param['option'.$key.'_dayautotitle'] = '"결제확인" 후 '.$CI->goodsmodel->dayautotype[$param['dayauto_type']].' '.$param['sdayauto'].'일 '.$dayauto_type.'부터 +'.$param['fdayauto'].'일 '.$CI->goodsmodel->dayautoday[$param['dayauto_day']];
					$param['option'.$key]	= $param['social_start_date'] . ' ~ ' . $param['social_end_date'];
				}else{
					$param['option'.$key.'_dayinputtitle'] = $param['sdayinput'] . ' ~ ' . $param['fdayinput'];
					$param['option'.$key]	= $param['social_start_date'] . ' ~ ' . $param['social_end_date'];
				}
			}
			if	($sp_option['address'] && in_array($types, $address_arr)){
				$param['option'.$key]	= $param['option'.$key] . ' (' . $param['biztel'] . ')';
			}
		}
	}

	if	($param['option1'])	$result[]	= $param['title1'].$division.$param['option1'];
	if	($param['option2'])	$result[]	= $param['title2'].$division.$param['option2'];
	if	($param['option3'])	$result[]	= $param['title3'].$division.$param['option3'];
	if	($param['option4'])	$result[]	= $param['title4'].$division.$param['option4'];
	if	($param['option5'])	$result[]	= $param['title5'].$division.$param['option5'];

	return $result;
}

function get_pg_config($company,$mode){
	$pg = config_load($company);
	if($mode == 'mobile'){
		unset($pg['interestTerms'],$pg['payment'],$pg['escrow'],$pg['pcCardCompanyCode']);
		$pg['interestTerms'] = $pg['mobileInterestTerms'];
		$pg['payment'] = $pg['mobilePayment'];
		$pg['escrow'] = $pg['mobileEscrow'];
		$pg['pcCardCompanyCode'] = $pg['mobileCardCompanyCode'];
		foreach($pg['pcCardCompanyCode'] as $key => $code) $pg['pcCardCompanyTerms'][$key] = $tmp['mobileCardCompanyTerms'][$key];
	}
	return $pg;
}

function item_step_msg($data_options,$data_suboptions){
	$r_step = array();
	if($data_options) foreach($data_options as $data){
		if(!in_array($data['step'],$r_step)){
			$r_step[] = $data['step'];
		}
	}
	if($data_suboptions) foreach($data_suboptions as $data){
		if(!in_array($data['step'],$r_step)){
			$r_step[] = $data['step'];
		}
	}
	return $r_step;
}

function get_usable_invoice_vendor(){
	$this->load->model('invoiceapimodel');
	return $this->invoiceapimodel->get_usable_invoice_vendor();
}


/**
* 쿠폰상품 미사용쿠폰 환불여부
* false 환불불가로 disabled 처리
* true 환불가능로 disabled 없음
**/
function order_socialcp_cancel_return($socialcp_use_return, $export_coupon_value, $export_coupon_remain_value, $social_start_date, $social_end_date , $socialcp_use_emoney_day, $type='order') {

	//debug_var($socialcp_use_return.', '.$export_coupon_value.', '.$export_coupon_remain_value.', '.$social_start_date.', '.$social_end_date.', '.$socialcp_use_emoney_day.', '.$type);

	$socialcp_use_day = ($socialcp_use_emoney_day>0)?date("Ymd",strtotime('+'.$socialcp_use_emoney_day.' day '.substr(str_replace("-","",$social_end_date),0,8))):date("Ymd",strtotime(substr(str_replace("-","",$social_end_date),0,8)));
	if( $type =='viewer' ){
		return $socialcp_use_day;
	}else{
		if(  $export_coupon_remain_value < 1 ) {//잔여값어치가 없음
				return false;
		}else{
			if( $socialcp_use_return != 1 || (($social_start_date == '1970-01-01' || !$social_start_date) && ($social_end_date == '1970-01-01' || !$social_end_date)) ) {//유효기간이 잘못되었거나 없으면
					return false;
			}elseif ( $socialcp_use_return == 1 ) {//미사용쿠폰 환불가능
				$today = date("Ymd");
				//if($today<=$social_end_date) {//유효기간이전이면 불가
				if( $socialcp_use_day < $today ) {//설정기간이후면 불가
					return false;
				}
			}
		}
		return true;//환불가능
	}
}

/**
* 쿠폰상품 취소(환불)
* false 환불불가로 disabled 처리
* true 환불가능로 disabled 없음
* return cancel_percent 실제 적용율
**/
function order_socialcp_cancel_refund($order_seq, $item_seq, $deposit_date, $social_start_date, $social_end_date, $socialcp_cancel_payoption, $socialcp_cancel_payoption_percent, $type='order') {
	$CI =& get_instance();
	if(!$CI->ordermodel) $CI->load->model('ordermodel');
	$today = date("Ymd");
	//debug_var($order_seq.', '.$item_seq.', '.$deposit_date.', '.$social_start_date.', '.$social_end_date.', '.$socialcp_cancel_payoption.', '.$socialcp_cancel_payoption_percent.', '.$type);

	//유효기간이 잘못되었거나 없으면
	if( (($social_start_date == '1970-01-01' || !$social_start_date) || ($social_end_date == '1970-01-01' || !$social_end_date)) && $type =='order'  )  {
		return array(false,0);
	}else{
		$socialcp_cancel = $CI->ordermodel->get_item_socialcp_cancel($order_seq, $item_seq);
		if($socialcp_cancel) {

			if( $socialcp_cancel[0]['socialcp_cancel_type'] == 'pay' ) {//결제확인 후 몇일 이내에 취소(환불) 가능
				$socialcp_cancel_refund_day = ($socialcp_cancel[0]['socialcp_cancel_day']>0)?date("Ymd",strtotime('+'.$socialcp_cancel[0]['socialcp_cancel_day'].' day '.substr(str_replace("-","",$deposit_date),0,8))):date("Ymd",strtotime(substr(str_replace("-","",$deposit_date),0,8)));
				if( $type =='viewer' ){
					return array('pay',$socialcp_cancel_refund_day,$socialcp_cancel[0]['socialcp_cancel_day']);
				}else{
					if( $today <= $socialcp_cancel_refund_day ){
						return array(true,100);
					}
				}

			}elseif( $socialcp_cancel[0]['socialcp_cancel_type'] == 'option' ) {//유효기간 이내에만 취소(환불) 가능
				if( $type =='viewer' ){
					return array('option',$social_end_date);
				}else{
					if($today<=str_replace("-","",$social_end_date) ) {// $today>=str_replace("-","",$social_start_date)  &&
						return array(true,100);
					}
				}

			}elseif( $socialcp_cancel[0]['socialcp_cancel_type'] == 'payoption' ) {//유효기간 설정

				$scnt=0;
				if( $socialcp_cancel_payoption == 1 && $today>=str_replace("-","",$social_start_date) && $today<=str_replace("-","",$social_end_date)  ) {
					//유효기간내 취소(환불) 가능
					$view_socialcp_cancel_refund_day			= $social_start_date;
					$view_socialcp_cancel_refund_prevday	= $social_end_date;
					$max_percent										= $socialcp_cancel_payoption_percent; 
					
					if( $type =='viewer' ){
						return array('payoption',$view_socialcp_cancel_refund_day,$view_socialcp_cancel_refund_prevday,$max_percent,'social_date');
					}else{ 
						return array(true,$max_percent);
					}
				}else{
				foreach($socialcp_cancel as $k=>$canceldata) {
						$socialcp_cancel_refund_day = ($canceldata['socialcp_cancel_day'])?date("Ymd",strtotime('-'.$canceldata['socialcp_cancel_day'].' day '.substr(str_replace("-","",$social_start_date),0,8))):date("Ymd",strtotime(substr(str_replace("-","",$social_start_date),0,8)));//유효기간전일
						$socialcp_cancel_refund_prevday = ($canceldata['socialcp_cancel_day'])?date("Ymd",strtotime('-'.($canceldata['socialcp_cancel_day']-1).' day '.substr(str_replace("-","",$social_start_date),0,8))):date("Ymd",strtotime(substr(str_replace("-","",$social_start_date),0,8)));//유효기간전 이후일

					$idx++;
					if( ($max_percent && ($max_socialcp_cancel_refund_day<=$today && $socialcp_cancel_refund_day<$today) ) || (!$max_percent  && ( $socialcp_cancel_refund_day >= $today || $socialcp_cancel_refund_prevday <= $today ) ) ) {
							//debug_var("1->".$canceldata['socialcp_cancel_day'].' day ==> '.$socialcp_cancel_refund_day."<".$today." :: ".(100-$canceldata['socialcp_cancel_percent']));
						$max_percent	= ($idx == 1)?$canceldata['socialcp_cancel_percent']:(100-$canceldata['socialcp_cancel_percent']);
						$max_socialcp_cancel_refund_day	= $socialcp_cancel_refund_prevday;//유효기간전의 전날(-1day)
							//debug_var("2->".$socialcp_cancel_refund_prevday);
						$scnt++;
					}
					$view_socialcp_cancel_refund_day	= $socialcp_cancel_refund_day;
					$view_socialcp_cancel_refund_prevday	= $socialcp_cancel_refund_prevday;
				}
					if( date("Ymd")>substr(str_replace("-","",$social_end_date),0,8)) {//유효기간 종료 후
						if ( $socialcp_cancel_payoption == 1 ){
							$view_socialcp_cancel_refund_day			= $social_start_date;
							$view_socialcp_cancel_refund_prevday	= $social_end_date;
							$max_percent										= $socialcp_cancel_payoption_percent; 
						}else{
							$view_socialcp_cancel_refund_day			= $social_start_date;
							$view_socialcp_cancel_refund_prevday	= $social_end_date;
							$max_percent										= ($idx == 1)?$socialcp_cancel[$k]['socialcp_cancel_percent']:(100-$socialcp_cancel[$k]['socialcp_cancel_percent']);
						}
					}
				if( $type =='viewer' ){
						return array('payoption',$view_socialcp_cancel_refund_day,$view_socialcp_cancel_refund_prevday,$max_percent,$canceldata['socialcp_cancel_day']);
				}else{
					if( $scnt ){
						return array(true,$max_percent);
					}
				}
				}
			}//endif;
		}//endif;
	}

	if( $type =='viewer' ){
		return array(false);
	}else{
		return array(false,$max_percent);//취소(환불) 불가
	}
}


//쿠폰정보만 추출하기(결제확인이후)
function get_goods_coupon_view($export_code){

	$CI =& get_instance();
	$CI->load->model('ordermodel');
	$CI->load->model('exportmodel');

	$config_system	= config_load('system');
	$export			= $CI->exportmodel -> get_export($export_code);
	$order			= $CI->ordermodel -> get_order($export['order_seq']);
	$items			= $CI->exportmodel -> get_export_item($export_code);
	$uselog			= $CI->exportmodel -> get_coupon_use_history($items[0]['coupon_serial']);
	$item			= $items[0];
	$send_sms		= $item['recipient_cellphone'];
	$providerList[]	= $item['provider_seq'];


	// 쿠폰 사용 내역
	if($uselog[0]) {
	$params['used_time']			= date('Y년 m월 d일 H시 i분', strtotime($uselog[0]['regist_date']));
	$params['used_location']		= $uselog[0]['coupon_use_area'];
	$params['confirm_person']		= $uselog[0]['confirm_user'];
		$params['coupon_used_log']	= $uselog[0];
	}

	$params['social_start_date']		= $item['social_start_date'];
	$params['social_end_date']			= $item['social_end_date'];

	// 치환코드
	$params['order_user']			= $order['order_user_name'];				// 주문자명
	$params['goods_name']			= $item['goods_name'];						// 상품명
	$params['coupon_serial']		= $item['coupon_serial'];					// 쿠폰번호
	$params['couponNum']			= $item['coupon_st'].'/'.$item['opt_ea'];	// 회차
	$params['coupontotalprice']	= $item['price']*$item['ea'];	// 구매금액
	// 값어치
	if	($item['coupon_input'] > 0){
		if	($item['socialcp_input_type'] == 'pass'){
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'회';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'회 사용';
			$params['coupon_value']		= '사용가능횟수: '.number_format($item['coupon_input']).'회';
		}else{
			$params['coupon_remain']	= '잔여:'.number_format($item['coupon_remain_value']).'원';
			$params['coupon_used']		= number_format($uselog[0]['coupon_use_value']).'원 사용';
			$params['coupon_value']		= '사용가능금액: '.number_format($item['coupon_input']).'원';
		}
	}


	if( $item['socialcp_use_return'] == 1 ){
		$params['order_socialcp_cancel_return'] = order_socialcp_cancel_return($item['socialcp_use_return'], $item['coupon_remain_value'], $item['coupon_remain_value'], $item['social_start_date'], $item['social_end_date'] , $item['socialcp_use_emoney_day'], 'viewer');
		//$params['order_socialcp_cancel_return_title'] = '유효기간 종료 후 '.date("Y년 m월 d일", strtotime($params['order_socialcp_cancel_return'])).'이내 잔여값어치에 '.$item['socialcp_use_emoney_percent'].'% 적립금환불';
		$params['order_socialcp_cancel_return_title'] = '유효기간 종료 후 '.$item['socialcp_use_emoney_day'].'일 이내('.date("Y년 m월 d일", strtotime($params['order_socialcp_cancel_return'])).'까지) 구매금액에 '.$item['socialcp_use_emoney_percent'].'% 취소(환불) ';

		$params['coupon_use_return']		= '미사용쿠폰환불 대상 ';
		$params['coupon_use_return']		.= '<br/>('.$params['order_socialcp_cancel_return_title'].')';
		$params['coupon_use_return_status']		= '미사용쿠폰환불 대상은 '.$params['order_socialcp_cancel_return_title'].'';
	}else{
		$params['coupon_use_return']		= '미사용쿠폰환불 대상아님';
		$params['coupon_use_return_status']		= '미사용쿠폰환불 대상아님';
	}

	$socialcp_cancel_refund_day = order_socialcp_cancel_refund($export['order_seq'], $item['item_seq'], $order['deposit_date'], $item['social_start_date'], $item['social_end_date'], $item['socialcp_cancel_payoption'], $item['socialcp_cancel_payoption_percent'], 'viewer');
	if( $socialcp_cancel_refund_day[0] == 'payoption' ){//array('','','','','')
		$params['socialcp_cancel_refund_day'] = "  ".date("Y년 m월 d일 ", strtotime($socialcp_cancel_refund_day[1]))."~".date("Y년 m월 d일 ", strtotime($socialcp_cancel_refund_day[2]))." ".$socialcp_cancel_refund_day[3]."%";
		if( $socialcp_cancel_refund_day[4] == 'social_date' ) {//유효기간내 취소시
			$params['socialcp_cancel_refund_day_status'] = "유효기간 동안 ".$socialcp_cancel_refund_day[3]."%";
		}else{
			$params['socialcp_cancel_refund_day_status'] = "유효기간 ".$socialcp_cancel_refund_day[4]."일 전까지( ".date("Y년 m월 d일 ", strtotime($socialcp_cancel_refund_day[1]))."~".date("Y년 m월 d일 ", strtotime($socialcp_cancel_refund_day[2])).") ".$socialcp_cancel_refund_day[3]."%";
		}
	}elseif( $socialcp_cancel_refund_day[0] == 'pay' ){//array('','','')
		$params['socialcp_cancel_refund_day'] = "결제확인 이후 ".date("Y년 m월 d일", strtotime($socialcp_cancel_refund_day[1]))."이내에만";
		$params['socialcp_cancel_refund_day_status'] = "결제확인 후 ".$socialcp_cancel_refund_day[2]."일 이내(".date("Y년 m월 d일", strtotime($socialcp_cancel_refund_day[1]))."까지)";
	}elseif( $socialcp_cancel_refund_day[0] == 'option' ){//array('','')
		$params['socialcp_cancel_refund_day'] = "유효기간 ".date("Y년 m월 d일", strtotime($socialcp_cancel_refund_day[1]))."이내에만";
		$params['socialcp_cancel_refund_day_status'] = "유효기간까지";
	}

	// 옵션
	$options_arr	= get_options_print_array($item, ':');
	if	($options_arr)		$params['options']	= implode("<br/>", $options_arr);
	return $params;
}



// 쿠폰상품(옵션) 사용기간 체크
function check_coupon_date_option($goods_seq,$option1,$option2,$option3,$option4,$option5, $optionssel=null, $data = null) {
	$CI =& get_instance();
	$today = date("Y-m-d");
	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');

	//특수정보 관련 추가@2013-10-22
	list($data_goods_opt['optioncode1'],$data_goods_opt['optioncode2'],$data_goods_opt['optioncode3'],$data_goods_opt['optioncode4'],$data_goods_opt['optioncode5'],$data_goods_opt['color'],$data_goods_opt['zipcode'],$data_goods_opt[0]['address_type'],$data_goods_opt['address'],$data_goods_opt[0]['address_street'],$data_goods_opt['addressdetail'],$data_goods_opt['biztel'],$data_goods_opt['coupon_input'],$data_goods_opt['codedate'],$data_goods_opt['sdayinput'],$data_goods_opt['fdayinput'],$data_goods_opt['dayauto_type'],$data_goods_opt['sdayauto'],$data_goods_opt['fdayauto'],$data_goods_opt['dayauto_day'],$data_goods_opt['newtype'],$data_goods_opt['address_commission']) = $CI->goodsmodel->get_goods_option_code(
		$goods_seq,
		$option1,
		$option2,
		$option3,
		$option4,
		$option5
	);
	$types = explode(",",$data_goods_opt['newtype']);
	$couponexpire =  true;
	if( $optionssel ) {
		$optionssel = ($optionssel == 'detail' ) ? 0:($optionssel);
		if( $types[$optionssel] == 'date' ) {
			$social_start_date = $data['codedate'];
			$social_end_date = $data['codedate'];
			if( $social_end_date < $today ) $couponexpire = false;
		}elseif( $types[$optionssel] == 'dayauto' ) {
		}elseif( $types[$optionssel] == 'dayinput' ) {
			$social_start_date = $data['sdayinput'];
			$social_end_date = $data['fdayinput'];
			if( $social_end_date < $today ) $couponexpire = false;
		}
	}else{
		if( in_array('date', $types) ) {
			$social_start_date = $data_goods_opt['codedate'];
			$social_end_date = $data_goods_opt['codedate'];
			if( $social_end_date < $today ) $couponexpire = false;
		}elseif( in_array('dayauto', $types) ) {
		}elseif( in_array('dayinput', $types) ) {
			$social_start_date = $data_goods_opt['sdayinput'];
			$social_end_date = $data_goods_opt['fdayinput'];
			if( $social_end_date < $today ) $couponexpire = false;
		}
	}

	return array('couponexpire'=>$couponexpire,'social_start_date'=>$social_start_date,'social_end_date'=>$social_end_date);
}

// 쿠폰상품(추가옵션) 사용기간 체크
function check_coupon_date_suboption($goods_seq,$suboption_title,$suboption) {
	$CI =& get_instance();
	$today = date("Y-m-d");
	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');
//특수정보 관련 추가@2013-10-22
	list($data_goods_opt['suboption_code'],$data_goods_opt['color'],$data_goods_opt['zipcode'],$data_goods_opt['address'],$data_goods_opt['addressdetail'],$data_goods_opt['biztel'],$data_goods_opt['coupon_input'],$data_goods_opt['codedate'],$data_goods_opt['sdayinput'],$data_goods_opt['fdayinput'],$data_goods_opt['dayauto_type'],$data_goods_opt['sdayauto'],$data_goods_opt['fdayauto'],$data_goods_opt['dayauto_day'],$data_goods_opt['newtype']) = $CI->goodsmodel->get_goods_suboption_code($goods_seq,$suboption_title,$suboption);
	//debug_var($data_goods_opt);

	$types = explode(",",$data_goods_opt['newtype']);
	$couponexpire =  true;
	if( in_array('date', $types) ) {
		$social_start_date = $data_goods_opt['codedate'];
		$social_end_date = $data_goods_opt['codedate'];
		if( $social_end_date < $today ) $couponexpire = false;
	}elseif( in_array('dayauto', $types) ) {
	}elseif( in_array('dayinput', $types) ) {
		$social_start_date = $data_goods_opt['sdayinput'];
		$social_end_date = $data_goods_opt['fdayinput'];
		if( $social_end_date < $today ) $couponexpire = false;
	}

	return array('couponexpire'=>$couponexpire,'social_start_date'=>$social_start_date,'social_end_date'=>$social_end_date);

}

/**
** 환불시 로그쌓기
* 신청상태
- $socialcp_refund_help_11 : 유효기간(0000년 00월 00일~ 0000년 00월 00일)
- $socialcp_refund_help_12 : 유효기간 종료 전 or 후
- $socialcp_refund_help_13 : 전체미사용 or 부분미사용
* 취소조건 $socialcp_refund_help_21 
* 환불계산
- $socialcp_refund_help_31 : 값어치
- $socialcp_refund_help_32 : 구매금액
- $socialcp_refund_help_33 : 잔여값어치
- $socialcp_refund_help_34 : 본래환불금액
- $socialcp_refund_help_35 : 실제환불
**/
function socialcp_cancel_memo($data, $coupon_remain_real_percent, $coupon_real_total_price, $coupon_remain_real_price, $coupon_remain_price, $coupon_deduction_price) {
		$socialcp_refund_help_11 = date("Y년 m월 d일", strtotime($data['couponinfo']['social_start_date']))."~".date("Y년 m월 d일", strtotime($data['couponinfo']['social_end_date']));
		if( date("Ymd")>substr(str_replace("-","",$data['couponinfo']['social_end_date']),0,8) ) {
			$socialcp_refund_help_12 = "후";
			$socialcp_refund_help_21 = $data['couponinfo']['coupon_use_return_status']."";
		}else{
			$socialcp_refund_help_12 = "전";
			$socialcp_refund_help_21 = $data['couponinfo']['socialcp_cancel_refund_day_status']." 취소(환불)";
		}

		if( $data['coupon_value'] == $data['coupon_remain_value'] ) {//전체체크 미사용
			$socialcp_refund_help_13 = "전체미사용";
		}else{
			$socialcp_refund_help_13 = "부분미사용";
		}

		$socialcp_refund_help_1 = "유효기간(".$socialcp_refund_help_11.") 종료 " .$socialcp_refund_help_12. " & " . $socialcp_refund_help_13;
		$socialcp_refund_help_2 = $socialcp_refund_help_21."";//

		$socialcp_refund_help_31 = end(explode(":",$data['couponinfo']['coupon_value']));
		$socialcp_refund_help_32 = number_format($coupon_real_total_price);
		$socialcp_refund_help_33 = end(explode(":",$data['couponinfo']['coupon_remain']));
		$socialcp_refund_help_34 = number_format($coupon_remain_real_price);
		if( $coupon_remain_price != $coupon_real_total_price ) $socialcp_refund_help_35 = "▶".number_format($coupon_remain_price)."원";

		$socialcp_refund_help_3 = "값어치 : ".trim($socialcp_refund_help_31)."/ 구매금액 : ".$socialcp_refund_help_32." → 잔여값어치 : ".trim($socialcp_refund_help_33)." / 환불 : ".$socialcp_refund_help_34."원".$socialcp_refund_help_35;

		$cancel_memo = "신청상태 : {$socialcp_refund_help_1}
		취소조건 : {$socialcp_refund_help_2}
		환불계산 : {$socialcp_refund_help_3}";
	return $cancel_memo;
}

//티켓상품 : 주문상태 
function get_socialcp_status($socialcp_status) {
	foreach($socialcp_status as $key=>$val){
		$idx++;
		/**if( $key == 3 ) {
			$socialcp_status_loop[$idx]['title'] = $val[0];
			$socialcp_status_loop[$idx]['desc'] = $val[1];
			$socialcp_status_loop[$idx]['number'] = $val[2];
			$socialcp_status_loop[$idx]['key'] = $key;
			$idx++;
		}**/
		$socialcp_status_loop[$idx]['title'] = $val[0];
		$socialcp_status_loop[$idx]['desc'] = $val[1];
		$socialcp_status_loop[$idx]['number'] = $val[2];
		$socialcp_status_loop[$idx]['key'] = $key;
	}
	return $socialcp_status_loop;
}


// END
/* End of file order_helper.php */
/* Location: ./app/helpers/order_helper.php */