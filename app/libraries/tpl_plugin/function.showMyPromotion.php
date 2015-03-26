<?php

/**
 * @author ysm
 * param : limit data, limit point
 */

function showMyPromotion($limit =null, $maxpoint = null)
{
	$CI =& get_instance();
	$CI->load->model('promotionmodel');
	$today = date("Y-m-d",time());

	if($limit){
		$sc['page'] = 0;
		$sc['perpage'] = $limit;
	}
	$sc['whereis'] = " and type in ('point', 'point_shipping')";//전환포인트
	$sc['whereis'] .= " and ( ( issue_priod_type = 'day' ) OR (issue_priod_type = 'date'  AND issue_enddate >='".$today."') ) ";//유효기간체크
	$sc['whereis'] .= ($maxpoint>0) ? " and promotion_point <= '".$maxpoint."' ":" and promotion_point >= '0' ";
	//선착순
	$promotioncode = $CI->promotionmodel->get_data($sc);
	foreach($promotioncode as $widget) {
		$widget['downpromotionseq'] = $CI->promotionmodel->get_admin_download($CI->userInfo['member_seq'],$widget['promotion_seq']);
		if( strstr($widget['sale_type'],'shipping') ){//배송비
			$widget['percent_goods_sale_show'] = ($widget['sale_type'] == 'shipping_free')?number_format($widget['max_percent_shipping_sale'])."원":number_format($widget['won_shipping_sale'])."원";
		}else{
			$widget['percent_goods_sale_show'] = ($widget['sale_type'] == 'percent')?number_format($widget['percent_goods_sale'])."%":number_format($widget['won_goods_sale'])."원";
		}
		if($promotions['promotion_type'] == 'file' ){//직접발급시
		}elseif( $promotions['promotion_type'] == 'random' ) {
		}

		$dsc['whereis'] = ' and promotion_seq='.$widget['promotion_seq'];
		$downloadtotal = $CI->promotionmodel->get_download_total_count($dsc);//발급수

		$usc['whereis'] = ' and promotion_seq='.$widget['promotion_seq'].' and use_status = \'used\' ';
		$usetotal = $CI->promotionmodel->get_download_total_count($usc);

		if($widget['promotion_type'] == 'random'){ //자동
			if($widget['download_limit'] == 'limit'){ //선착순
				$widget['limitnumber'] = number_format($widget['download_limit_ea']-$usetotal).'개';
			}else{//무제한
				$widget['limitnumber'] = '무제한';
			}
		}else{//수동
			if($widget['download_limit']  == 'limit'){ //선착순
				$widget['limitnumber'] = number_format($widget['download_limit_ea']-$usetotal).'개';
			}else{//무제한
				$filepromotiontotal = $CI->promotionmodel->get_promotioncode_input_item_total_count($widget['promotion_seq']);//수동등록총건수
				$widget['limitnumber'] = number_format($filepromotiontotal-$usetotal).'개';
			}
		}
		$widgetloop[] = $widget;
	}
	return $widgetloop;
}
?>