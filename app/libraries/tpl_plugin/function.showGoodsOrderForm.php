<?php

/* 하단 고정 구매버튼 */
function showGoodsOrderForm($goods_seq){

	$CI						=& get_instance();
	$CI->load->model('goodsmodel');
	$CI->load->model('categorymodel');
	$CI->load->model('brandmodel');
	$CI->load->model('membermodel');
	$CI->load->model('wishmodel');
	$CI->load->helper('order');	
	$CI->load->library('sale');
	$CI->load->model('configsalemodel');

	if(!$goods_seq)	return '';

	$applypage					= 'view';
	$cfg_order					= config_load('order');
	$cfg_goods					= config_load('goods');
	$goods						= $CI->goodsmodel->get_goods($goods_seq);
	$goods['string_price']		= get_string_price($goods);
	$goods['string_price_use']	= 0;
	if($goods['string_price'] != '')		$goods['string_price_use']	= 1;
	if($goods['goods_type'] == 'gift')		return '';
	if( $goods['goods_view'] == 'notLook')	return '';

	// 회원정보 가져오기
	if($CI->userInfo){
		$data_member = $CI->membermodel->get_member_data($CI->userInfo['member_seq']);
	}

	// 등급 체크
	$arr_group_seq			= $CI->categorymodel->get_category_group_for_goods($goods_seq);
	if($arr_group_seq && !$CI->userInfo)						return '';

	// 브랜드 등급체크
	$arr_brand_group_seq	= $CI->brandmodel->get_brand_group_for_goods($goods_seq);
	if($arr_brand_group_seq && !$CI->userInfo['member_seq'])	return '';
	if(is_array($arr_brand_group_seq))
		if($data_member['group_seq'] && !in_array($data_member['group_seq'],$arr_brand_group_seq))	return '';
	$sessionMember			= $data_member;
	$options	= $CI->goodsmodel->get_goods_option($goods_seq);
	$suboptions	= $CI->goodsmodel->get_goods_suboption($goods_seq);
	$inputs		= $CI->goodsmodel->get_goods_input($goods_seq);
	if($options)foreach($options as $k => $opt){
		/* 대표가격 */
		if($opt['default_option'] == 'y'){
			$goods['price'] 			= $opt['price'];
			$goods['consumer_price'] 	= $opt['consumer_price'];
			$opt['reserve']				= $CI->goodsmodel->get_reserve_with_policy(
													$goods['reserve_policy'],
													$opt['price'],
													$cfg_reserve['default_reserve_percent'],
													$opt['reserve_rate'],
													$opt['reserve_unit'],
													$opt['reserve']	);
			$add_reserve				= (int) $CI->membermodel->get_group_addreseve(
													$sessionMember['member_seq'],
													$goods['price'],
													$goods['price'],
													$goods['goods_seq'],
													$goods['category_code']	);
			$goods['reserve']			= $opt['reserve'] + $add_reserve;
		}

		// 재고 체크
		$opt['chk_stock']				= check_stock_option(
													$goods['goods_seq'],
													$opt['option1'],
													$opt['option2'],
													$opt['option3'],
													$opt['option4'],
													$opt['option5'],
													0,
													$cfg_order,
													'view'	);
		if( $opt['chk_stock'] ) $runout	= false;

		$opt['opspecial_location']		= get_goods_options_print_array($opt);
		if($data['newtype']) {
			$data['infomation']			= get_goods_special_option_print($data);
			if	($data['infomation'])
				$data['infomation']		.= '<br />'.get_goods_special_option_print($data);
		}

		$options[$k] = $opt;
	}

	unset($opt);

	$sub_runout = false;
	if($suboptions) foreach($suboptions as $key => $tmp){
		foreach($tmp as $k => $opt){
			$opt['chk_stock'] = check_stock_suboption($goods['goods_seq'],$opt['suboption_title'],$opt['suboption'],0,$cfg_order,'view');
			if( $opt['chk_stock'] ){
				$sub_runout = true;
			}

			//----> sale library 적용
			unset($param);
			$param['cal_type']				= 'each';
			$param['option_type']			= 'suboption';
			$param['sub_sale']				= $opt['sub_sale'];
			$param['member_seq']			= $data_member['member_seq'];
			$param['group_seq']				= $data_member['group_seq'];
			$param['consumer_price']		= $opt['consumer_price'];
			$param['price']					= $opt['price'];
			$param['total_price']			= $opt['price'];
			$param['ea']					= 1;
			$param['category_code']			= $goods['r_category'];
			$param['goods_seq']				= $goods['goods_seq'];
			$param['goods']					= $goods;
			$CI->sale->set_init($param);
			$sales							= $CI->sale->calculate_sale_price($applypage);
			$opt['price']					= $sales['result_price'];
			$CI->sale->reset_init();
			unset($sales);
			//<---- sale library 적용

			$suboptions[$key][$k] = $opt;
		}
	}

	if(isset($options[0]['option_divide_title'])) $goods['option_divide_title'] = $options[0]['option_divide_title'];
	if(isset($options[0]['divide_newtype'])) $goods['divide_newtype'] = $options[0]['divide_newtype'];


	// 배송정보 가져오기
	$delivery = $CI->goodsmodel->get_goods_delivery($goods);


	//----> sale library 적용
	unset($param);
	$CI->sale->set_mobile_sale();
	$param['cal_type']				= 'each';
	$param['option_type']			= 'option';
	$param['member_seq']			= $data_member['member_seq'];
	$param['group_seq']				= $data_member['group_seq'];
	$param['consumer_price']		= $goods['consumer_price'];
	$param['price']					= $goods['price'];
	$param['total_price']			= $goods['price'];
	$param['ea']					= 1;
	$param['category_code']			= $goods['r_category'];
	$param['goods_seq']				= $goods['goods_seq'];
	$param['goods']					= $goods;
	$CI->sale->set_init($param);
	$sales							= $CI->sale->calculate_sale_price($applypage);

	$goods['basic_sale']			= $sales['sale_list']['basic_sale'];
	$goods['event_sale_unit']		= $sales['sale_list']['event_sale'];
	$goods['referer_sale_unit']		= $sales['sale_list']['referer_sale'];
	$goods['mobile_sale_unit']		= $sales['sale_list']['mobile_sale'];
	$goods['fblike_sale_unit']		= $sales['sale_list']['like_sale'];
	$goods['member_sale_unit']		= $sales['sale_list']['member_sale'];
	$goods['sum_sale_price']		= $sales['total_sale_price'];
	$goods['sale_price']			= $sales['result_price'];
	$goods['price']					= $sales['result_price'];
	$goods['event']					= $CI->sale->cfgs['event'];
	$eventEnd						= $sales['eventEnd'];
	### sale 라이브러리 사용 ocw 2014-08-11 : START


	// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
	if( $goods['socialcp_event'] == 1 && $goods['event']['event_goodsStatus'] === true ){
		$goods['goods_status'] = 'unsold';
	}

	if($goods['event']['end_date'] && $goods['event']['event_type'] == "solo"){
		$CI->template->assign('eventEnd',	$eventEnd);
	}


	// 재고가 없을 시 품절로 상태 표기
	if( $goods['goods_status'] == 'normal' &&  $runout ){
		$goods['goods_status'] = 'runout';
	}

	// 위시여부 2014-01-10 lwh
	$wish_seq = $CI->wishmodel->confirm_wish($_GET['no']);

	$CI->template->assign(array(
		'sub_runout'	=> $sub_runout,
		'sessionMember'	=> $sessionMember,
		'goods'			=> $goods,
		'options'		=> $options,
		'additions'		=> $additions,
		'suboptions'	=> $suboptions,
		'inputs'		=> $inputs,
		'images'		=> $images,
		'icons'			=> $icons,
		'delivery'		=> $delivery,
		'view_brand'	=> $view_brand,
		'cfg_reserve'	=> $cfg_reserve,
		'wish_seq'		=> $wish_seq
	));

	$orderform_skin_path	= $CI->skin.'/'.'_modules/common/goods_order_form.html';
	$CI->template->define(array('orderform'=>$orderform_skin_path));
	$html					= $CI->template->fetch("orderform");
	return $html;
}
?>