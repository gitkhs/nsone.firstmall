<?php
/* 다운로드 쿠폰 출력 */
function showCoupons($use_type, $style = "front", $view_cnt="2")
{
	$max		= 0;
	$memberSeq	= "";
	$goodsSeq	= (int) $_GET['no'];
	
	if($use_type=='all')
		$use_type	= "";

	$CI =& get_instance();
	
	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');
	if(!$CI->membermodel) $CI->load->model('membermodel');
	if(!$CI->couponmodel) $CI->load->model('couponmodel');
	if(!$CI->categorymodel) $CI->load->model('categorymodel');
	if(!$CI->coupon) $CI->load->helper('coupon');

	$today = date('Y-m-d',time());

	$tmp = $CI->goodsmodel->get_goods_category($goodsSeq);
	foreach($tmp as $datarow) $category[] = $datarow['category_code'];
	$goods = $CI->goodsmodel->get_default_option($goodsSeq);
	$result = $CI->couponmodel->get_able_download_list($today,$CI->userInfo['member_seq'],$goodsSeq,$category,$goods['price'],$use_type);

	if($view_cnt > 2)	$view_cnt = "2";
	if($CI->mobileMode)	$view_cnt = "1";

	foreach($result as $key => $datarow){
		if ($datarow['issue_priod_type'] == 'day') {
			$datarow['issue_enddatetitle'] = ($datarow['after_issue_day']>0) ? '다운로드 후 '.$datarow['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
		}else{
			$datarow['issue_enddatetitle'] = substr($datarow['issue_enddate'], 5,2).'월 '. substr($datarow['issue_enddate'],8,2).'일 까지 사용가능';
		}

		$issuecategorys	= $CI->couponmodel->get_coupon_issuecategory($datarow['coupon_seq']);

		if($issuecategorys){
			$categoryhtml = array();
			foreach($issuecategorys as $catekey =>$catedata) {
				$categoryhtml[$catekey] = $CI->categorymodel -> get_category_name($catedata['category_code']);
			}
			$datarow['categoryhtml'] = implode(", ",$categoryhtml);
		}else{
			if($datarow['issue_type'] != "issue" ) {
				$datarow['categoryhtml'] = '전체 상품 사용 가능';
			}
		}

		if( $datarow['coupon_same_time'] == 'N' ) {//단독쿠폰이면
			$datarow['couponsametimeimg'] = 'sametime';
		}else{
			$datarow['couponsametimeimg'] = '';
		}
		$datarow['couponsametimeimg'] .= ( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) )?"_shipping":"";

		if ($datarow['download_enddate']) {
			$datarow['download_enddatetitle'] = substr($datarow['download_enddate'], 5,2).'월 '. substr($datarow['download_enddate'],8,2).'일 까지 다운가능';
		}

		if( !($datarow['download_starttime'] == "00:00" && $datarow['download_endtime'] == "23:59") ){
			$datarow['download_enddatetitle_time'] = $datarow['download_starttime'] . " 부터 " . $datarow['download_endtime'] . " 까지 다운가능";
		}

		if($datarow['download_week'] != '1234567'){
			$datarow['download_week'] = "|" . $datarow['download_week'];
			$downweek = "";

			if(strpos($datarow['download_week'],'1') > 0)	$downweek .= ",월";
			if(strpos($datarow['download_week'],'2') > 0)	$downweek .= ",화";
			if(strpos($datarow['download_week'],'3') > 0)	$downweek .= ",수";
			if(strpos($datarow['download_week'],'4') > 0)	$downweek .= ",목";
			if(strpos($datarow['download_week'],'5') > 0)	$downweek .= ",금";
			if(strpos($datarow['download_week'],'6') > 0)	$downweek .= ",토";
			if(strpos($datarow['download_week'],'7') > 0)	$downweek .= ",일";

			$downweek = substr($downweek,1,strlen($downweek));
			$datarow['download_enddatetitle_week'] = $downweek . " 요일 다운가능";
		}

		//쿠폰이미지
		getcouponimage($datarow);

		/* 1개만 구할시
		if($offmax < $datarow['coupon_seq'] && $datarow['use_type'] == 'offline') {
			$offmax = $datarow['coupon_seq'];
			$maxCoupon['offline'] = $datarow;
		}

		if($max < $datarow['goods_sale'] && $datarow['use_type'] == 'online') {
			$max = $datarow['goods_sale'];
			$maxCoupon['online'] = $datarow;
		}*/

		/* 여러개 온라인 오프라인 구분
		if($datarow['use_type'] == 'offline'){
			$maxCoupon['offline'][] = $datarow;
		}else{
			$maxCoupon['online'][] = $datarow;
		}*/

		$maxCoupon[] = $datarow;
	}

	$CI->template->assign(array('userInfo' => $CI->userInfo['member_seq']));
	$CI->template->assign(array('couponloop' => $maxCoupon));
	$CI->template->assign(array('view_cnt' => $view_cnt));

	if( strstr($CI->template_path,'goods/view') ) {
		$CI->template->assign(array('goods_view' => true));
	}

	$CI->template->define(array('tpl'=>$CI->skin."/_modules/display/coupon_display_".$style.".html"));

	if($return){
		return $CI->template->fetch('tpl');
	}else{

		$CI->template->print_('tpl');
	}
}
?>