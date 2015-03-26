<?php

/**
 * @상품의 할인금액이 가장좋은 쿠폰추출
 */

function dataCouponMax($goodsSeq,$page = 'order', $result=null)
{
	$CI =& get_instance();
	$CI->load->model('couponmodel');
	$CI->load->helper('coupon');
	if(!$goodsSeq) return false;

	$maxCoupon = goods_coupon_max($goodsSeq);
	if($result) {
		return ($maxCoupon)?true:false;
	}
	$couponmaxTag = '';
	if($maxCoupon) {
		if( $page == 'goods_view' ) {
			if($maxCoupon['sale_type'] == 'won') {
				$couponmaxTag .= number_format($maxCoupon['won_goods_sale']).'원';
			}else{
				$couponmaxTag .= number_format($maxCoupon['percent_goods_sale']).'% '; 
			}
		}else{
			$couponmaxTag .= $maxCoupon['coupon_name'];
			if($maxCoupon['sale_type'] == 'won') {
				$couponmaxTag .= '<br/>('.number_format($maxCoupon['won_goods_sale']).'원)';
			}else{
				$couponmaxTag .= '<br/>('.number_format($maxCoupon['percent_goods_sale']).'%)'; 
			}
			$couponmaxTag .= ' <span class="btn small black"><button type="button" >쿠폰받기</button></span>';
		}
		echo $couponmaxTag;
	}
}
?>