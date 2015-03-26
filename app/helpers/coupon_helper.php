<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* 관리자/프론트 쿠폰 다운목록/다운가능목록
**/
function down_coupon_list($page = 'mypage', &$sc, &$dataloop)
{
	$CI =& get_instance(); 
	$today				= date('Y-m-d H:i:s');

	if($_GET['tab'] ==2 ) {//다운가능한쿠폰
		### SEARCH 
		$sc['today']		= $today;
		$sc['year']			= date('Y',time());
		$sc['month']		= date('Y-m',time());
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']			= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
		
		$data = $CI->couponmodel->get_my_download($sc,$CI->mdata);
		/**
		 * count setting
		**/
		$sc['searchcount'] = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1; 
			$datarow = downloadable_tab2($today, $datarow);
			$dataloop[] = $datarow;
		}//
	}else{//보유쿠폰 
		### SEARCH
		$sc						= $_GET;
		$sc['today']		= $today;
		$sc['year']			= date('Y',time());
		$sc['month']		= date('Y-m',time());
		$sc['orderby']		= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'download_seq';
		$sc['sort']				= (!empty($_GET['sort'])) ?			$_GET['sort']:'desc';
		$sc['page']			= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
		if ( $page == 'mypage' ) {
			$sc['member_seq']	= $CI->userInfo['member_seq'];
		}else{
			if( $_GET['member_seq'] ) $sc['member_seq']	= $_GET['member_seq'];
		} 

		if ($sc['keyword'])
		{
			$sc['keyword'] = trim($sc['keyword']);
			$sc['keyword']= stripslashes(htmlspecialchars($sc['keyword']));
		}

		$data = $CI->couponmodel->my_download_list($sc); 

		/**
		 * count setting
		**/
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$coupons = $CI->couponmodel->get_coupon($datarow['coupon_seq']);
			
			if( $datarow['type'] == 'mobile' && $datarow['sale_agent'] != 'm' ) {//기존 모바일쿠폰제외 
				$datarow['sale_agent']	= 'm';//사용환경 모바일로 대체
			}
			$datarow = downloadlist_tab1($today, $datarow, $coupons); 
			$dataloop[] = $datarow;
		}//
	} 

}

//다운리스트>항목
function downloadlist_tab1($today, $datarow, $coupons) {
	$CI =& get_instance();  
	$datarow['date']			= substr($datarow['regist_date'],2,8); //발급일 
	$datarow['use_status_title'] = ($datarow['use_status'] == 'used') ? '사용함':'미사용';
	if(str_replace("-","",$datarow['issue_enddate']) < date("Ymd") && $datarow['use_status'] != 'used') {
		$datarow['use_status_title'] = '<span class="gray" >소멸함</span>';//미사용중 기간지남
		$datarow['use_period'] = 'n';
	}else{
		$datarow['use_period'] = 'y';
	}

	$datarow['use_date']			  = ($datarow['use_status'] == 'used') ? substr($datarow['use_date'],2,14):'';
	

	if($datarow['use_status'] == 'used') {
		if ( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ) {
			$order_coupon = $CI->ordermodel->get_order_shipping_coupon($datarow['member_seq'], $datarow['download_seq']);
			$datarow['goodsview']			  = ($order_coupon) ? substr($datarow['regist_date'],2,8):'-'; 
			$datarow['coupon_order_saleprice'] = number_format($order_coupon['coupon_order_saleprice']); 
		} else {
			if ($datarow['type'] == 'offline_emoney') {
				$datarow['goodsview'] = '적립금 '.number_format($datarow['offline_emoney']).'원 지급';
			}else{
				$order_coupon = $CI->ordermodel->get_option_coupon_item($datarow['member_seq'], $datarow['download_seq']);
				if($datarow['use_type']=='offline'){
					$datarow['goodsview'] = '-';
				}else{
					$datarow['goodsview']			  = ($order_coupon) ? substr($datarow['use_date'],0,8):'-';
				}
				$datarow['coupon_order_saleprice'] = number_format($order_coupon['coupon_order_saleprice']);
			}
		}
	}
 
	$datarow['limit_goods_price'] = number_format($datarow['limit_goods_price']);
	if($datarow['type'] == 'offline_emoney' ) {
		$datarow['issuedate']			= '-';
		$datarow['issuedaylimit']		= '-';
	}else{
		$datarow['issuedate'] = ($datarow['issue_startdate'] && $datarow['issue_enddate'])?substr($datarow['issue_startdate'],2,10).' <br/> '.substr($datarow['issue_enddate'],2,10):'제한없음'; 
		$todayck = date("Y-m-d",time()); 
		if( $datarow['issue_enddate'] >= date("Y-m-d") ) { 
			$issuedaylimit = intval((strtotime($datarow['issue_enddate'])-strtotime($todayck)) / 86400); 
			$datarow['issuedaylimit'] = number_format($issuedaylimit)."일 남음"; 
		}else{  
			$issuedaylimit = intval((strtotime($todayck)-strtotime($datarow['issue_enddate'])) / 86400); 
			$datarow['issuedaylimit'] = number_format($issuedaylimit)."일 지남";
		}
	}

	$datarow['issueimg'] = (strstr($datarow['type'],'offline'))?'오프라인':'온라인';
	$datarow['downloaddate']	.='<br>'.substr($datarow['issue_startdate'],2,10).' ~ '.substr($datarow['issue_enddate'],2,10);

	if( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ){//배송비
		$datarow['salepricetitle']	= ($datarow['shipping_type'] == 'free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
		$datarow['issuetype'] = '배송비';
	}elseif($datarow['type'] == 'offline_emoney' ){//오프라인 적립금쿠폰
		$datarow['salepricetitle']	='적립금 '.number_format($datarow['offline_emoney']).'원 지급';
	}elseif($datarow['type'] == 'point' ){//
		$datarow['salepricetitle'] = '포인트 '.number_format($datarow['coupon_point']).'P 차감';
	}else{
		if($datarow['use_type']=='offline'){
			$datarow['salepricetitle']	= "-";
		}else{
			## 2014-10-08, pjm 수정 : 최대할인가 0원일때 노출 안하기
			if($datarow['sale_type'] == 'percent'){
				$datarow['salepricetitle'] = $datarow['percent_goods_sale'].'% 할인 ';
				if($datarow['max_percent_goods_sale'] > 0){
					$datarow['salepricetitle'] .= ', 최대'.number_format($datarow['max_percent_goods_sale']).'원';
				}
			}else{
				$datarow['salepricetitle'] = '판매가격의 '.number_format($datarow['won_goods_sale']).'원';
			}
		}

		if($datarow['issue_type']=='all'){
			if($datarow['use_type']=='offline')	$datarow['issuetype'] = '매장';
			else								$datarow['issuetype'] = '전상품';
		}elseif ($datarow['issue_type']=='issue'){
			$datarow['issuetype'] = '<a onclick="issue_list('.$datarow['coupon_seq'].')" style="cursor:pointer">적용상품</a>';
		}elseif ($datarow['issue_type']=='except'){
			$datarow['issuetype'] = '<a onclick="issue_list('.$datarow['coupon_seq'].')" style="cursor:pointer">제외상품</a>';
		}
	}
	$datarow['issuebtn']	= $CI->couponmodel->couponTypeShortTitle[$datarow['type']];
	if($datarow['use_type']=='offline')	$datarow['issuebtn']	.= " [매장용]";

	if( $datarow['issue_enddate'] < date("Y-m-d") ){
		$datarow['cp_name'] = "<font color='#DCDCDC'>".$datarow['coupon_name']."</font>";
	}else{
		if($datarow['use_status'] == 'used')
		{	$datarow['cp_name'] = "<font color='#DCDCDC'>".$datarow['coupon_name']."</font>";
			}else{
			$datarow['cp_name'] = "<b>".$datarow['coupon_name']."</b>";
		}
	}
	$datarow['issuebtn']	= $CI->couponmodel->couponTypeTitle[$datarow['type']];
	if($datarow['use_type']=='offline')	$datarow['issuebtn']	.= " [매장용]"; 

	if(!($datarow['type'] == 'offline_emoney' || $datarow['use_type'] == 'offline')) {
		$datarow['limit_price'] = number_format($datarow['limit_goods_price'])."원 이상 구매 시";
	}else{
		$datarow['limit_price'] = "없음";
	}

	//쿠폰이미지
	getcouponimage($datarow);

	return $datarow;
}

//다운가능리스트>항목
function downloadable_tab2($todaytime, $datarow) {
	$CI =& get_instance();  
	$today = date('Y-m-d'); 
	//기존 모바일쿠폰제외>사용환경 모바일로 대체
	if($datarow['type']=='mobile' && $datarow['sale_agent']!='m')$datarow['sale_agent']='m';
	$datarow['use_status_title'] = ($datarow['use_status'] == 'used') ? '사용함':'미사용';
	$datarow['date']			= substr($datarow['regist_date'],2,8); //발급일 
	$datarow['limit_goods_price_title'] = ( $datarow['type'] == 'offline_emoney' || $datarow['type'] == 'point')?"-":number_format($datarow['limit_goods_price']);
	$datarow['issue_stop_title']	= ($datarow['issue_stop']=='1') ? "<span class='red bold'>중지</span>" : "발급";

	if( $datarow['type'] == 'offline_emoney' ||  $datarow['use_type'] == 'offline'  ){ 
		$datarow['coupon_same_time_title']	= " - ";
		$datarow['issue_type_title']				= " - ";
		$datarow['sale_payment_title']			= " - ";
		$datarow['sale_referer_title']				= " - ";
	}else{
		$datarow['coupon_same_time_title']	= ($datarow['coupon_same_time']=='N') ? "단독" : "동시";
		$datarow['issue_type_title']	= ($datarow['issue_type']=='issue' || $datarow['issue_type']=='except') ? "제한" : "전체";
		$datarow['sale_payment_title']	= ($datarow['sale_payment']=='b') ? "무통장" : "X";
		$datarow['sale_referer_title']	= ($datarow['sale_referer']=='n' || $datarow['sale_referer']=='y') ? "제한" : "무관";
	}

	if(str_replace("-","",$datarow['issue_enddate']) < date("Ymd") && $datarow['use_status'] != 'used') {
		$datarow['use_status_title'] = '<span class="gray" >소멸함</span>';//미사용중 기간지남
		$datarow['use_period'] = 'n';
	}else{
		$datarow['use_period'] = 'y';
	}

	$datarow['limit_goods_price'] = number_format($datarow['limit_goods_price']);
	
	/**
	if($datarow['type'] == 'birthday' || $datarow['type'] == 'anniversary' || strstr($datarow['type'],'memberGroup') || $datarow['type'] == 'member' ){//직접발급시
		$datarow['downloaddate']	= '발급일로부터 '.number_format($datarow['after_issue_day']).'일';
	}else{
	}
	**/
	if( $datarow['issue_priod_type'] == 'date' ) {
		$datarow['downloaddate']	= substr($datarow['issue_startdate'],2,10).' ~ '.substr($datarow['issue_enddate'],2,10);
	}elseif( $datarow['issue_priod_type'] == 'months' ) { 
		$datarow['downloaddate'] = " 발급 당월 말일까지 (~".date("m.t").")";
		$datarow['downloaddate_endday'] = date("m.t");//당월의 말일 
	}else{
		$datarow['downloaddate']	= '발급일로부터 '.number_format($datarow['after_issue_day']).'일';
	}

	switch ($datarow['type'])
	{
		case 'birthday' : 
			if( !trim($CI->mdata['birthday']) || strstr($CI->mdata['birthday'],"0000-00-00") ) { 
				$datarow['downdate'] = "생일일 입력해 주세요.";  
				break;  
			}
			if($today > $datarow['birthday_beforeday'] && $today > $datarow['birthday_afterday']) {
				$datarow['birthday_beforeday'] = date("Y-m-d",strtotime('+1 year', strtotime($datarow['birthday_beforeday'])));
				$datarow['birthday_afterday'] = date("Y-m-d",strtotime('+1 year', strtotime($datarow['birthday_afterday'])));
			}

			if($today >= $datarow['birthday_beforeday'] && $today <= $datarow['birthday_afterday'])
			{
				$datarow['downdate']	= '생일 '.($datarow['birthday_beforeday']).'일부터~'.($datarow['birthday_afterday']).'일까지';
				$datarow['downckbtn']= 'down';
			}elseif( $today < $datarow['birthday_beforeday'] && $today > $datarow['birthday_afterday'] ){
				$datarow['downdate'] = '기간 만료';
				$datarow['downckbtn']= 'down';
			}else{
				$datarow['downdate']	= '생일 '.($datarow['birthday_beforeday']).'일부터~'.($datarow['birthday_afterday']).'일까지';
				$datarow['downckbtn']= 'down';
			}
		break;

		case 'anniversary' :  
			if ( !trim($CI->mdata['anniversary']) ) { 
				$datarow['downdate'] = "기념일 입력해 주세요."; 
				break;
			}

			if($today > $datarow['anniversary_beforeday'] && $today > $datarow['anniversary_afterday']) {
				$datarow['anniversary_beforeday'] = date("Y-m-d",strtotime('+1 year', strtotime($datarow['anniversary_beforeday'])));
				$datarow['anniversary_afterday'] = date("Y-m-d",strtotime('+1 year', strtotime($datarow['anniversary_afterday'])));
			}

			if($today >= $datarow['anniversary_beforeday'] && $today <= $datarow['anniversary_afterday'])
			{
				$datarow['downdate']	= '기념일  '.($datarow['anniversary_beforeday']).'일부터~'.($datarow['anniversary_afterday']).'일까지';
				$datarow['downckbtn']= 'down';
			}elseif( $today < $datarow['anniversary_beforeday'] &&  $today > $datarow['anniversary_afterday'] ){
				$datarow['downdate'] = '기간 만료';
				$datarow['downckbtn']= 'down';
			}else{
				$datarow['downdate']	= '기념일  '.($datarow['anniversary_beforeday']).'일부터~'.($datarow['anniversary_afterday']).'일까지';
				$datarow['downckbtn']= 'down';
			}
		break;

		case 'memberGroup' :
		case 'memberGroup_shipping' :
			if($today <= $datarow['upgrade_groupday']){
				$datarow['downdate']	= '등급조정일로부터 '.($datarow['upgrade_groupday']).'일 까지';
				$datarow['downckbtn']= 'down';
			}elseif($today > $datarow['upgrade_groupday']){
				$datarow['downdate'] = '기간 만료';
			}
		break;

		case 'member' :
		case 'member_shipping' :
			$datarow['downloaddate']	= '가입일로부터 '.number_format($datarow['after_issue_day']).'일'; 
			$datarow['downckbtn']= 'down';
		break;

		case 'memberlogin' :
		case 'memberlogin_shipping' :
			//$datarow['downdate']	= '최근  '.number_format($datarow['memberlogin_terms']).'개월 동안 미구매';
			//$datarow['downloaddate']	= '가입일로부터 '.number_format($datarow['after_issue_day']).'일'; 
			$datarow['downdate']						= date("m").'월중 1회만다운'; 
			$datarow['downloaddate']				= " 발급 당월 말일까지 (~".date("m.t").")";
			$datarow['downloaddate_endday'] = date("m.t");//당월의 말일 
			$datarow['downckbtn']= 'down';
		break;

		case 'membermonths' :
		case 'membermonths_shipping' :
			$datarow['downdate']			= date("m").'월중 1회만다운'; 
			$datarow['downloaddate']	= " 발급 당월 말일까지 (~".date("m.t").")";
			$datarow['downloaddate_endday'] = date("m.t");//당월의 말일 
			$datarow['downckbtn']= 'down';
		break;

		case 'order' :
			$datarow['downdate']						= date("m").'월중 1회만다운'; 
			$datarow['downloaddate']				= " 발급 당월 말일까지 (~".date("m.t").")";
			$datarow['downloaddate_endday']	= date("m.t");//당월의 말일 
			//'가입후 '.number_format($datarow['order_terms']).'일 동안 미구매';
			$datarow['downckbtn']= 'down';
		break;

		case 'download' :
			$datarow['downdate']	= substr($datarow['download_startdate'],2,8).' ~ '.substr($datarow['download_enddate'],2,8);
			if($todaytime >= $datarow['download_startdate'] && $todaytime <= $datarow['download_enddate'] &&  $datarow['unused_cnt']==0)
			{ $datarow['downckbtn']= 'down';}
		break;

		case 'shipping' :
			if( empty($datarow['download_startdate']) || empty($datarow['download_enddate']) ){
				$datarow['downdate']	= '기간제한없음';
				$datarow['downckbtn']= 'down';
			}elseif($todaytime >= $datarow['download_startdate'] && $todaytime <= $datarow['download_enddate']){
				$datarow['downdate']	= substr($datarow['download_startdate'],2,8).' ~ '.substr($datarow['download_enddate'],2,8);
				$datarow['downckbtn']= 'down';
			}elseif($today > $datarow['download_enddate'] && $datarow['download_enddate'] ){
				$datarow['downdate'] = '기간 만료';			}

		break;

		case 'point' :
			$datarow['downckbtn']= 'down';
			$datarow['downdate'] = '포인트 '.number_format($datarow['coupon_point']).'P 차감';
		break;
	}

	if( $datarow['issue_priod_type'] == 'date') { 
		$datarow['issuedaylimit'] = 0;
		if( $datarow['issue_enddate'] >= date("Y-m-d") ) {
			$issuedaylimit = intval((strtotime($datarow['issue_enddate'])-strtotime($today)) / 86400); 
			$datarow['issuedaylimit'] = $issuedaylimit;
			$datarow['issuedaylimituse'] = true; 
		}else{
			$issuedaylimit = intval((strtotime($today)-strtotime($datarow['issue_enddate'])) / 86400); 
			$datarow['issuedaylimit'] = $issuedaylimit;
		}
	}

	if( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ){//배송비
		$datarow['salepricetitle']	= ($datarow['shipping_type'] == 'free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
	}else{
		$datarow['salepricetitle']	= ($datarow['sale_type'] == 'percent' ) ? $datarow['percent_goods_sale'].'%, 최대 '.number_format($datarow['max_percent_goods_sale']).'원': '판매가격의 '.number_format($datarow['won_goods_sale']).'원';
	}

	$datarow['issuebtn']	= $CI->couponmodel->couponTypeTitle[$datarow['type']];
	if($datarow['use_type']=='offline')	$datarow['issuebtn']	.= " [매장용]";

	if($datarow['coupon_img'] == '4' && @is_file($CI->couponmodel->copuonupload_dir.$datarow['coupon_image4'])){
		$datarow['downloadbtn']	= $CI->couponmodel->copuonupload_src.$datarow['coupon_image4'];
	}else{
		$datarow['downloadbtn']	= $CI->couponmodel->copuonupload_src.'coupon_0'.$datarow['coupon_img'].'.gif';
	}

	if($datarow['coupon_mobile_img'] == '4' && @is_file($CI->couponmodel->copuonupload_dir.$datarow['coupon_mobile_image4'])){
		$datarow['mobiledownloadbtn']	= $CI->couponmodel->copuonupload_src.$datarow['coupon_mobile_image4'];
	}else{
		$datarow['mobiledownloadbtn']	= $CI->couponmodel->copuonupload_src.'coupon_0'.$datarow['coupon_mobile_img'].'.gif';
	}
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
			$categoryonehtml = $CI->categorymodel -> get_category_name($catedata['category_code']);
			$categorycodeonehtml = $catedata['category_code'];
		}
		$datarow['categoryhtml'] = implode(", ",$categoryonehtml);
		$datarow['categoryonehtml'] = end(explode(">",$categoryonehtml));
		$datarow['categorycodeone'] = $categorycodeonehtml;
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

	if ($datarow['download_startdate']) {
		$datarow['download_startdatetitle'] = substr($datarow['download_startdate'], 0,4).'월 '.substr($datarow['download_startdate'], 5,2).'월 '. substr($datarow['download_startdate'],8,2).'일';
	}
	if ($datarow['download_enddate']) {
		$datarow['download_enddatetitle'] = substr($datarow['download_enddate'], 0,4).'월 '.substr($datarow['download_enddate'], 5,2).'월 '. substr($datarow['download_enddate'],8,2).'일 까지 다운가능';
	}

	if( !($datarow['download_starttime'] == "00:00" && $datarow['download_endtime'] == "23:59")){
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
		$datarow['download_enddatetitle_week'] = $downweek . " 요일";
	}

	//쿠폰이미지
	getcouponimage(&$datarow);
	return $datarow;
}



//할인쿠폰 상품상세
function goods_coupon_max($goodsSeq)
{
	$CI =& get_instance(); 
	$max = 0;
	$today = date('Y-m-d',time());
	$CI->load->helper('coupon');
	$CI->load->model('goodsmodel');
	//$CI->load->model('membermodel');
	$CI->load->model('categorymodel');
	$tmp = $CI->goodsmodel -> get_goods_category($goodsSeq);
	foreach($tmp as $data) $category[] = $data['category_code'];
	$goods = $CI->goodsmodel -> get_default_option($goodsSeq);
	$result = $CI->couponmodel->get_able_download_list($today,$CI->userInfo['member_seq'],$goodsSeq,$category,$goods['price']);
	foreach($result as $key => $data){
		if ($data['issue_priod_type'] == 'day') {
			$data['issue_enddatetitle'] = ($data['after_issue_day']>0) ? '다운로드 후 '.$data['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
		}else{
			$data['issue_enddatetitle'] = substr($data['issue_enddate'], 5,2).'월 '. substr($data['issue_enddate'],8,2).'일 까지 사용가능';
		}

		$issuecategorys	= $CI->couponmodel->get_coupon_issuecategory($data['coupon_seq']);

		if($issuecategorys){
			$categoryhtml = array();
			foreach($issuecategorys as $catekey =>$catedata) {
				$categoryhtml[$catekey] = $CI->categorymodel -> get_category_name($catedata['category_code']);
			}
			$data['categoryhtml'] = implode(", ",$categoryhtml);
			$data['categoryonehtml'] = end($categoryhtml);
		}else{
			if( $data['issue_type'] != "issue" && $data['issue_type'] != "except" ) {
				$data['categoryhtml'] = '전체 상품 사용 가능';
			}
		}

		if( $data['coupon_same_time'] == 'N' ) {//단독쿠폰이면
			$data['couponsametimeimg'] = 'sametime';
		}else{
			$data['couponsametimeimg'] = '';
		}
		
		$data['couponsametimeimg'] .= ( ( $data['type'] == 'shipping' || strstr($data['type'],'_shipping') ) )?"_shipping":"";
		
		//쿠폰이미지
		getcouponimage(&$datarow);

		if ($data['download_enddate']) {
			$data['download_enddatetitle'] = substr($data['download_enddate'], 5,2).'월 '. substr($data['download_enddate'],8,2).'일 까지 다운가능';
		}else{
			$data['download_enddatetitle'] = '다운로드 기간 제한 없음';
		}
		
		//사용제한 - 유입경로 체크
		/**if( couponordercheck(&$data, $goodsSeq, $goods['price'], 1) != true ) {
			//continue;
		}**/

		if($max < $data['goods_sale']) {
			$max = $data['goods_sale'];
			$maxCoupon = $data;
		}
	}

	return $maxCoupon;
}

/**
* 사용제한 - 유입경로
**/
function couponordercheck(&$couponinfo, $goodsSeq, $price, $ea) {
	$CI =& get_instance(); 
	
	if ( $couponinfo['sale_referer'] == 'a' ) return true;//유입경로와 무관

	if( $couponinfo['sale_referer'] == 'y' ) {
		//유입경로 할인이 있을 때만 사용가능
		if($CI->session->userdata('shopReferer')) {
			if( $couponinfo['sale_referer_type'] == 'a' ) {//모든유입경로
				return true;
			}else{
				$referersale_seq_ar = @explode(',',$couponinfo['sale_referer_item']);
				$CI->load->model('referermodel');
				$referersale = $CI->referermodel->sales_referersale($CI->session->userdata('shopReferer'), $goodsSeq, $price, $ea, $referersale_seq_ar);  
				if($referersale){
					$couponinfo['referersale_seq']	= $referersale['referersale_seq'];
					$couponinfo['referer_sale']			= $referersale['sales_price'];
					return true;//유입경로와 무관
				}
			}
		}
	}else{
		//유입경로 할인이 없을 때만 사용가능
		if($CI->session->userdata('shopReferer')) {
			$CI->load->model('referermodel');
			$referersale = $CI->referermodel->sales_referersale($CI->session->userdata('shopReferer'), $goodsSeq, $price, $ea);
			if(!$referersale) return true;//유입경로와 무관
		}else{
			return true;
		}
	}
	return false;
}

/**
* 다운로드/모바일/등급 쿠폰
* 전체수량제한 누적제한 수량보다 큰경우 제한
* 2014-02-13
**/
function downloadcouponcheck($couponSeq, $result=null){
	$CI =& get_instance(); 

	$cptypech = array("download","mobile","shipping");
	$CI->couponinfo 		= $CI->couponmodel->get_coupon($couponSeq);
	if( in_array($CI->couponinfo['type'],$cptypech)) {
		$sc['whereis'] = ' and coupon_seq='.$couponSeq;
		$downloadtotal = $CI->couponmodel->get_download_total_count($sc);
		if($CI->couponinfo['download_limit'] == 'limit' && $CI->couponinfo['download_limit_ea'] <= $downloadtotal ) { 
			if( $CI->coupontypeall ) return false;
			$msg = "다운로드 수량이 모두 소진되었습니다.";
			if($result == "mypage"){
				echo json_encode(array('result'=>false, 'msg'=>$msg));
				exit;
			}elseif($result== "goodslist"){
				return true;
			}else{
				openDialogAlert($msg,400,140,'parent',"parent.$('#couponDownloadDialog').dialog('close');");
				exit;
			}
		}
	}
	if($result == "mypage"){
	}elseif($result== "goodslist"){
		return false;
	}
}

//쿠폰다운가능시간 체크
function chkCouponAbleDownDate($coupons) 
{ 
	$CI =& get_instance(); 
	$today = date('Y-m-d');
	//회원가입/직접발급쿠폰제외
	if ( $coupons['type'] == 'member' ||  $coupons['type'] == 'member_shipping'  ||  $coupons['type'] == 'admin'  ||  $coupons['type'] == 'admin_shipping' ) {
		$result['result']	= false;
		$result['msg']		= '잘못된 접근입니다.';  
		return $result;
	} 
	
	//유효기간체크
	if ( ( $coupons['type'] == 'download' || $coupons['type'] == 'shipping' || $coupons['type'] == 'point' || $coupons['type'] == 'offline_coupon'  ) && $coupons['issue_priod_type'] == 'date' ) { 
		$stime	= ($coupons['issue_startdate']);
		$etime	= ($coupons['issue_enddate']); 
		if( !($today >= $stime && $today <= $etime) ) {
			$result['result']	= false;
			$result['msg']		= '유효기간이 만료되었습니다.';  
			return $result;
		}
	} 

	if ( ($coupons['allgroup_issue_cnt']==0 && $coupons['mbgroup_issue_cnt']==0) || ($coupons['allgroup_issue_cnt']>0 && $coupons['mbgroup_issue_cnt']>0) ) {//다운등급체크
		switch ($coupons['type']){
			case 'birthday' :

				if($today > $coupons['birthday_beforeday'] && $today > $coupons['birthday_afterday']) {
					$coupons['birthday_beforeday'] = date("Y-m-d",strtotime('+1 year', strtotime($coupons['birthday_beforeday'])));
					$coupons['birthday_afterday'] = date("Y-m-d",strtotime('+1 year', strtotime($coupons['birthday_afterday'])));
				}

				$result['result']	= false;
				$result['msg']		= '다운로드 가능기간이 아닙니다.';
				if($today >= $coupons['birthday_beforeday'] && $today <= $coupons['birthday_afterday'])
				{
					$result['result']	= true;
					$result['msg']		= '';
				}
			break;

			case 'anniversary' :

				if($today > $coupons['anniversary_beforeday'] && $today > $coupons['anniversary_afterday']) {
					$coupons['anniversary_beforeday'] = date("Y-m-d",strtotime('+1 year', strtotime($coupons['anniversary_beforeday'])));
					$coupons['anniversary_afterday'] = date("Y-m-d",strtotime('+1 year', strtotime($coupons['anniversary_afterday'])));
				}
				$result['result']	= false;
				$result['msg']		= '다운로드 가능기간이 아닙니다.';

				if($today >= $coupons['anniversary_beforeday'] && $today <= $coupons['anniversary_afterday'])
				{
					$result['result']	= true;
					$result['msg']		= '';
				}
			break;

			default:
				$result['result']	= false;
				$result['msg']		= '다운로드 기간이 만료되었습니다.'; 

				$ntime	= time();
				$stime	= strtotime($coupons['download_startdate']);
				$etime	= strtotime($coupons['download_enddate']);
				if( (!$coupons['download_startdate'] && !$coupons['download_enddate']) || ($ntime >= $stime && $ntime <= $etime)){
					if(date('H:i') >= $coupons['download_starttime'] && date('H:i') <= $coupons['download_endtime']){
						if	(strstr($coupons['download_week'], date('w'))){
							$result['result']	= true;
							$result['msg']		= '';
						}
					}
				}
			break;
		}
	}else{
		$result['result']	= false;
		$result['msg']		= '다운로드 가능 등급이 아닙니다.';
	} 
	
	//신규가입제외 모든쿠폰 등급제한 체크 
	if ( $result['result'] === true ) {
		$couponGroups 	= $CI->couponmodel->get_coupon_group($coupons['coupon_seq']);
		if($couponGroups) {
			$memberjoincoupon = false;
			foreach($couponGroups as $key => $group){  
				if($CI->mdata['group_seq'] == $group['group_seq']) {
					$memberjoincoupon = true;
					continue;
				}
			}
		}
		if( $memberjoincoupon === false ) {
			$result['result']	= false;
			$result['msg']		= '다운로드 가능 등급이 아닙니다.';
		}
	}

	return $result;
}

//2차원배열의 쿠폰할인금액 기준 내림차순
function goods_sale_desc($x, $y) {
	if ($x['goods_sale'] == $y['goods_sale']){
		return 0;
	} else if ($x['goods_sale'] < $y['goods_sale']) {
		return 1;
	} else {
		return -1;
	}
}

//2차원배열의 배송비쿠폰할인금액 기준 내림차순
function shipping_sale_desc($x, $y) {
	if ($x['shipping_sale'] == $y['shipping_sale']){
		return 0;
	} else if ($x['shipping_sale'] < $y['shipping_sale']) {
		return 1;
	} else {
		return -1;
	}
}

//쿠폰다운로드연결 주소
function getCouponDownloadUrl($form, $coupon_type, $coupon_seq)
{
	$downloadurl = ""; 
	if( $form == 'all' ){//전체
		if( $coupon_type ) $downloadurl = htmlspecialchars("<a href='/coupon/coupon?type=".$coupon_type."' target='actionFrame' />다운</a>"); 
	}else{
		if( $coupon_seq ) $downloadurl = htmlspecialchars("<a href='/coupon/coupon?no=".$coupon_seq."' target='actionFrame' />다운</a>");
	}
	return $downloadurl;
}

//쿠폰소스 페이지정의
function getcouponpagepopup($coupons,$ftype=null) 
{
	$CI =& get_instance(); 
	$couponfilename = ""; 
	$coupons['type'] = str_replace("_shipping","",$coupons['type']);
	if( in_array($coupons['type'],$CI->couponmodel->couponpagetype['mypage']) ) {
		$couponfilename = "coupon_".$coupons['type'];
	}elseif ( in_array($coupons['type'],$CI->couponmodel->couponpagetype['promotionpage']) ) {
		$couponfilename = "coupon_".$coupons['type'];
	}else{
		$couponfilename = "coupon_".$coupons['type'];
	} 
	return ($ftype == "url" )?strtolower($couponfilename):$couponfilename.".html";
}

//이벤트페이지의 이달의 쿠폰 등급별로 가져오기
function getcoupongrouparray($datarow, &$datagroup)
{
	$CI =& get_instance();
	$couponGroups 	= $CI->couponmodel->get_coupon_group($datarow['coupon_seq']);
	if($couponGroups){
		foreach($CI->eventgroups as $tmp){
			foreach($couponGroups as $key => $group){ 
				if($tmp['group_seq'] == $group['group_seq']) {
					$datagroup[$group['group_seq']]['loop'][] = $datarow; 

					if(!$datagroup[$group['group_seq']]['group_icon']){
						$groupiconsc['group_seq']	= $group['group_seq'];
						$member_group_flow				= $CI->membermodel->get_member_group_flow($groupiconsc); 
						if($member_group_flow['currentGroup']["myicon"]){
							$datagroup[$group['group_seq']]['group_myicon'] = $member_group_flow['currentGroup']["myicon"];
						}
						$datagroup[$group['group_seq']]['group_icon'] = $member_group_flow['currentGroup']["icon"];
					}
					if(!$datagroup[$group['group_seq']]['group_name'])$datagroup[$group['group_seq']]['group_name'] = $tmp['group_name'];
					break;
				}
			}
		}
	}
}

//쿠폰 다운방식 치환코드
function coupondowntargethtml($type)
{ 
	switch($type){
	case 'birthday':
	$coupondowntarget = '
			<tr>
				<td height="30"><img src="../images/promotion/item_down.gif" alt="다운 기간" /></td>
			</tr>
			<tr>
				<td>생일 <b>{.before_birthday}일 전 ~ {.after_birthday}일 이후</b>까지</td>
			</tr>';
	break;

	case 'anniversary':
	$coupondowntarget = '
			<tr>
				<td height="30"><img src="../images/promotion/item_down.gif" alt="다운 기간" /></td>
			</tr>
			<tr>
				<td>기념일 <b>{.before_anniversary}일 전 ~ {.after_anniversary}일 이후</b>까지</td>
			</tr>';
	break;

	case 'member':
	$coupondowntarget = '
			<tr>
				<td height="30"><img src="../images/promotion/item_target.gif" alt="다운 대상" /></td>
			</tr>
			<tr>
				<td>모든 신규가입 회원에게 <b>자동 제공</b></td>
			</tr>';
	break;

	case 'memberGroup':
	$coupondowntarget = '
			<tr>
				<td height="30"><img src="../images/promotion/item_down.gif" alt="다운 기간" /></td>
			</tr>
			<tr>
				<td>
					등급 조정일로부터 <b>{.after_upgrade}일</b>까지
				</td>
			</tr>';
	break;

	case 'memberlogin':
	$coupondowntarget = '
			<tr>
				<td height="30"><img src="../images/promotion/item_target.gif" alt="다운 대상" /></td>
			</tr>
			<tr>
				<td>1회 이상 구매 내역 있으나 최근 <b>{.memberlogin_terms}개월</b>동안 미구매한 회원</td>
			</tr>  ';
	break;

	case 'order':
	$coupondowntarget = '
			<tr>
				<td height="30"><img src="../images/promotion/item_target.gif" alt="다운 대상" /></td>
			</tr>
			<tr>
				<td>한 번도 구매하지 않은 회원에게 <b>제공</b></td>
			</tr>';
	break;

	default:
	$coupondowntarget = '{? .download_startdatetitle && .download_enddatetitle || .download_enddatetitle_week || .download_enddatetitle_time}
			<tr>
				<td height="30"><img src="../images/promotion/item_down.gif" alt="다운 기간" /></td>
			</tr>
			<tr>
				<td>
				{? .download_startdatetitle && .download_enddatetitle }{.download_startdatetitle} ~ {.download_enddatetitle}
				{:} <b> </b> {/}
				{? .download_enddatetitle_week }
				<b>매주 {.download_enddatetitle_week}</b>
				{ / }
				{? .download_enddatetitle_time }
				<b> {.download_enddatetitle_time}</b>
				{ / }
				</td>
			</tr>
				{ / }
			';
		break;
	}
	return $coupondowntarget;
}

//쿠폰이미지노출
function getcouponimage(&$datarow) 
{
	$CI =& get_instance(); 
	if($datarow['use_type'] == 'offline'){
		$datarow['view_coupon_html']	= $datarow['benefit'];
	}else{
		$couponDetailpricelayhtml	= "";

		if( $CI->siteType == 'mobile' ) {
			$datarow['coupon_img']			= $datarow['coupon_mobile_img'];
			$datarow['coupon_image4']	= $datarow['coupon_mobile_image4'];
		}

		if($datarow['coupon_img']!='4') {

			$datarow['sametime_shipping_img'] = "";
			$datarow['sametime_shipping_img'] = ( ( $datarow['coupon_same_time'] == 'N' ) )?"sametime":"";
			$datarow['sametime_shipping_img'] .= ( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) )?"_shipping":"";

			if($datarow['sale_type']=='won' || ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') )) {
				$realprice	= ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ?number_format($datarow['won_shipping_sale']):number_format($datarow['won_goods_sale']);
				$woncnt		= mb_strlen($realprice, 'UTF-8');
				$datarow['coupon_sale_strlen']= ($woncnt);
				for ($i = 0; $i < $woncnt ; $i++) {
					$number	= substr($realprice,$i,1);
					if($number == ','){
						$couponDetailpricelayhtml .= "<img src='/data/coupon/coupon_i_comma.png' >";
					}else{
						$couponDetailpricelayhtml .= "<img src='/data/coupon/coupon_i_no".$number.".png' >";
					}
				}
				$couponDetailpricelayhtml .= "<img src='/data/coupon/coupon_i_won.png' >"; 
			}else{
				$realpercent = ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ?number_format($datarow['max_percent_shipping_sale']):$datarow['percent_goods_sale'];
				$percnt		= mb_strlen($realpercent, 'UTF-8');
				$datarow['coupon_sale_strlen']= ($percnt); 
				for ($i = 0; $i < $percnt ; $i++) {
					$number	= substr($realpercent,$i,1);
					$couponDetailpricelayhtml .= "<img src='/data/coupon/coupon_i_no".$number.".png' >";
				}
				$couponDetailpricelayhtml .= "<img src='/data/coupon/coupon_i_per.png' >";
				$couponDetailpricelayhtml .= "<img src='/data/coupon/coupon_i_dc.png' >";
			}
		}
		$datarow['view_coupon_html'] = $couponDetailpricelayhtml;
	}
}

//이벤트랜딩페이지 > 조건대상과 다운여부 체크
function getcouponcodewonloadcheck(&$datarow, $mycouponcode,$mdata)
{ 
	
	$today			= date('Y-m-d'); 
	$todaytime	= date('Y-m-d H:i:s');
	if( $mycouponcode['result'] ) {
		foreach($mycouponcode['result'] as $key=>$mycoupon) {
			if( $mycoupon['coupon_seq'] == $datarow['coupon_seq'] ){//조건대상인것
				$coupondownyesdata = $mycoupon;
				$coupondownyes = true;
				break;
			}
		}
	}
	$datarow['coupondownno'] = ($coupondownyes)?false:true;//조건대상에서 제외인경우 
	if( $coupondownyesdata ) { 
		switch ($datarow['type'])
		{
			case 'birthday' : 
				if( !trim($mdata['birthday']) || strstr($mdata['birthday'],"0000-00-00") ) { 
					$datarow['coupondownno'] = true;
					break;  
				}  
				if($today >= $coupondownyesdata['birthday_beforeday'] && $today <= $coupondownyesdata['birthday_afterday'])
				{
					$datarow['coupondownno'] = false;
				}elseif( $today < $coupondownyesdata['birthday_beforeday'] && $coupondownyesdata > $datarow['birthday_afterday'] ){
					$datarow['downdate'] = '기간 만료';
					$datarow['coupondownno'] = true;
				}else{
					$datarow['coupondownno'] = true;
				} 
			break;

			case 'anniversary' :  
				if ( !trim($mdata['anniversary']) ) { 
					$datarow['coupondownno'] = true; 
					break;
				}

				if($today > $coupondownyesdata['anniversary_beforeday'] && $today > $coupondownyesdata['anniversary_afterday']) {
					$coupondownyesdata['anniversary_beforeday'] = date("Y-m-d",strtotime('+1 year', strtotime($coupondownyesdata['anniversary_beforeday'])));
					$coupondownyesdata['anniversary_afterday'] = date("Y-m-d",strtotime('+1 year', strtotime($coupondownyesdata['anniversary_afterday'])));
				}

				if($today >= $coupondownyesdata['anniversary_beforeday'] && $today <= $coupondownyesdata['anniversary_afterday'])
				{ 
					$datarow['coupondownno'] = false;
				}elseif( $today < $coupondownyesdata['anniversary_beforeday'] &&  $today > $coupondownyesdata['anniversary_afterday'] ){
					$datarow['downdate'] = '기간 만료'; 
					$datarow['coupondownno'] = true;
				}else{ 
					$datarow['coupondownno'] = true;
				}
			break;

			case 'memberGroup' :
			case 'memberGroup_shipping' :
				if($today <= $coupondownyesdata['upgrade_groupday']){
					//$datarow['downdate']	= '등급조정일로부터 '.($datarow['upgrade_groupday']).'일 까지';
					$datarow['coupondownno'] = false;
				}elseif($today > $coupondownyesdata['upgrade_groupday']){
					//$datarow['downdate'] = '기간 만료';
					$datarow['coupondownno'] = true;
				}
			break; 
	   
			case 'download' :
				$datarow['downdate']	= substr($coupondownyesdata['download_startdate'],2,8).' ~ '.substr($coupondownyesdata['download_enddate'],2,8);
				if($todaytime >= $coupondownyesdata['download_startdate'] && $todaytime <= $coupondownyesdata['download_enddate'] &&  $coupondownyesdata['unused_cnt']==0)
				{  
					$datarow['coupondownno'] = false;
				}else{
					$datarow['coupondownno'] = true;
				}
			break;

			case 'shipping' : 
				if( empty($coupondownyesdata['download_startdate']) || empty($coupondownyesdata['download_enddate']) ){ //제한없음
					$datarow['coupondownno'] = false; 
				}elseif($todaytime >= $coupondownyesdata['download_startdate'] && $todaytime <= $coupondownyesdata['download_enddate']){ //기간내
					$datarow['coupondownno'] = false; 
				}elseif($todaytime > $coupondownyesdata['download_enddate'] && $coupondownyesdata['download_enddate'] ){ //기간완료
					$datarow['coupondownno'] = true; 
				} 
			break;
		}
	}

	//배송비쿠폰, 월 1회 다운가능쿠폰 체크
	$onemonth = array('shipping','membermonths','membermonths_shipping','memberlogin','memberlogin_shipping','order');
	if ( in_array($datarow['coupondowninfo']['type'],$onemonth)) {// && $coupondownyes
		
		if( $datarow['coupondowninfo']['type'] == 'shipping' && ($datarow['duplication_use'] != 1  || ( $datarow['duplication_use'] == 1 && $datarow['coupondowninfo'] && !$coupondownyesdata )) ) {
			if($datarow['coupondowninfo']['download_seq']) $datarow['coupondownfinish'] = $datarow['coupondowninfo']['download_seq'];
		} 
		if(($datarow['coupondowninfo']['type'] == 'membermonths' || $datarow['coupondowninfo']['type'] == 'membermonths_shipping' || $datarow['coupondowninfo']['type'] == 'memberlogin' || $datarow['coupondowninfo']['type'] == 'memberlogin_shipping' || $datarow['coupondowninfo']['type'] == 'order' ) && substr($datarow['coupondowninfo']['regist_date'],0,7) == date('Y-m',time()) ) {
			if($datarow['coupondowninfo']['download_seq']) $datarow['coupondownfinish'] = $datarow['coupondowninfo']['download_seq'];
		} 
		if( ($datarow['coupondowninfo']['type'] == 'birthday' || $datarow['coupondowninfo']['type'] == 'anniversary') && ( $datarow['coupondowninfo']['down_year'] == date('Y') || $datarow['coupondowninfo']['down_year'] == date('Y')+1) ) {
			if($datarow['coupondowninfo']['download_seq']) $datarow['coupondownfinish'] = $datarow['coupondowninfo']['download_seq'];
		}
	}else{
		if($datarow['coupondowninfo']['download_seq']) $datarow['coupondownfinish'] = $datarow['coupondowninfo']['download_seq'];
	} 
}


// END
/* End of file coupon_helper.php */
/* Location: ./app/helpers/coupon_helper.php */