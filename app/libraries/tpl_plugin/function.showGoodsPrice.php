<?
// 상품 리스트 할인가 출력
// view 에서 {=dataGoodsContents(goods.goods_seq,goods.goods_seq, goods.category_code)} 치환 코드 사용
function showGoodsPrice($goods_seq, $goods_price, $category, $sale_seq)
{
	$CI =& get_instance();
	$CI->load->model('membermodel');
	$CI->load->model('goodsmodel');

	$sale_price = 0;
	$event_price = 0;

	//회원할인
	if($CI->userInfo['member_seq']){
		$members = $CI->membermodel->get_member_data($CI->userInfo['member_seq']);
		$sale_price = $CI->membermodel->get_member_group($members['group_seq'],$goods_seq,$category,$goods_price,$tot_price, $sale_seq);
	}else{
		$sale_price = $CI->membermodel->get_member_group("0",$goods_seq,$category,$goods_price,$tot_price, $sale_seq);
	}

	//이벤트 할인
	$eventData = $CI->goodsmodel->get_event_price($goods_price, $goods_seq, $category);
	$event_price = $eventData["event_sale_unit"];

	//mobile 할인 $CI->mobileMode || $CI->storemobileMode ||
	if($CI->_is_mobile_agent) {
		$CI->load->model('configsalemodel');
		$sc['type'] = 'mobile';
		$systemmobiles = $CI->configsalemodel->lists($sc);

		foreach($systemmobiles['result'] as $fblike => $systemmobiles_price) {
			if($systemmobiles_price['price1']<= $goods_price && $systemmobiles_price['price2'] >= $goods_price){
				$opt_mobile_goods_sale = $systemmobiles_price['sale_price'] * $goods_price / 100; // 모바일 할인
				break;
			}//endif
		}//end foreach
	}

	if(!$opt_mobile_goods_sale){
		$opt_mobile_goods_sale = 0;
	}

	$price = $goods_price-$sale_price-$event_price-$opt_mobile_goods_sale;

	return $price;
}
?>