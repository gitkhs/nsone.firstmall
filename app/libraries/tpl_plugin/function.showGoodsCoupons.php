<?php
/* 쿠폰 출력*/
function showGoodsCoupons($goodsSeq='')
{
	$CI =& get_instance();

	$max = 0;
	$goodsSeq = (int) $goodsSeq;
	$today = date('Y-m-d',time());
	$CI->load->model('goodsmodel');
	$CI->load->model('couponmodel');
	$tmp = $CI->goodsmodel -> get_goods_category($goodsSeq);
	if($tmp) foreach($tmp as $data) $category[] = $data['category_code'];
	$goods = $CI->goodsmodel -> get_default_option($goodsSeq);
	$goods_info = $CI->goodsmodel -> get_goods($goodsSeq);

	$result = $CI->couponmodel->get_able_download_list($today,'',$goodsSeq,$category,$goods['price']);
	if($result) {
		foreach($result as $key => $data){

			## 할인부담금 관련 부담자의 상품에만 적용.
			if	($goods_info['provider_seq'] == 1 && $data['provider_list'])	continue;
			if	($data['provider_list'] && !strstr($data['provider_list'], '|'.$goods_info['provider_seq'].'|'))	continue;

			if($data['type'] == 'download' || $data['type'] == 'shipping' || $data['type'] == 'offline_coupon' || $data['type'] == 'offline_emoney' ){//다운로드/배송비
				$data['downloaddate']	= ($data['download_startdate'] && $data['download_enddate'])?substr($data['download_startdate'],2,10).' ~ '.substr($data['download_enddate'],2,10):'기간제한없음';
			}elseif($data['type'] == 'birthday'){//생일자
				$data['downloaddate']	= '생일 '.$data['before_birthday'].'일전 ~ '.$data['after_birthday'].'일이후까지';
			}elseif($data['type'] == 'anniversary'){//기념일
				$data['downloaddate']	= '기념일 '.$data['before_anniversary'].'일전 ~ '.$data['after_anniversary'].'일이후까지';
			}elseif($data['type'] == 'memberGroup'){//회원등급
				$data['downloaddate']	= '등급조정일로부터 '. ($data['after_upgrade']).'일';
			}elseif($data['type'] == 'point'){//전환포인트
				$data['downloaddate']	= '포인트 '.number_format($data['coupon_point']).'원 지급';
			}else{
				$data['downloaddate']	= '-';
			}

			if($data['type'] == 'birthday' || $data['type'] == 'anniversary' || $data['type'] == 'memberGroup' || $data['type'] == 'member' ){//직접발급시
				$data['issuedate']	= '발급일로부터 '.number_format($data['after_issue_day']).'일';
			}else{
				if( $data['issue_priod_type'] == 'date' ) {
					$data['issuedate']	= substr($data['issue_startdate'],2,10).' ~ '.substr($data['issue_enddate'],2,10);
				}else{
					$data['issuedate']	= '발급일로부터 '.number_format($data['after_issue_day']).'일';
				}
			}

			//다운로드/모바일 쿠폰중에서 소멸된 쿠폰체크하여 중복다운로드 가능쿠폰 체크
			if( ($data['type'] == "download" || $data['type'] == "mobile") && $data['duplication_use'] == 1 && $data['unused_cnt'] == $data['cancel_cnt']) {
				$data['unused_cnt'] = 0;
			}

			$data['valid_priod_msg'] = "";
			if($data['issue_priod_type'] == 'date'){
				if($data['issue_startdate']) $data['valid_priod_msg'] = " ".$data['issue_startdate'] . "부터";
				if($data['issue_enddate']) $data['valid_priod_msg'] .= " ". $data['issue_enddate'] . "까지";
			}
			if($data['issue_priod_type'] == 'day'){
				if($data['after_issue_day']) $data['valid_priod_msg'] = " 발급 후 ". $data['after_issue_day'] . "일";
			}

			$data['use_limit_msg'] = "-";
			if($data['limit_goods_price']){
				$data['use_limit_msg'] = number_format($data['limit_goods_price']) . "원 이상 구매 시";
			}

			if( $data['coupon_same_time'] == 'N' ) {//단독쿠폰
				$data['use_limit_msg'] = '[단독]';
			}

			if( empty($data['use_limit_msg']) ) $data['use_limit_msg'] = '-';

			if($max < $data['goods_sale']){
				$max = $data['goods_sale'];
				$maxCoupon = $data;
				$result['max_coupon'] = $data;
			}

			$widgetloop[] = $data;
		}
	}
	return $widgetloop;
}
?>