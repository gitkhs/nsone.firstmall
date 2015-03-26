<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class partner extends front_base {
	public function __construct(){
		parent::__construct();
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('membermodel');
		$this->load->model('configsalemodel');

		set_time_limit(0);
		ini_set("memory_limit",-1);

		if( $this->config_system['pgCompany'] ){
			$payment_gateway = config_load($this->config_system['pgCompany']);
			$payment_gateway['rCardCompany'] = code_load($this->config_system['pgCompany'].'CardCompanyCode');

			foreach($payment_gateway['rCardCompany'] as $k => $v){
				$payment_gateway['arrCardCompany'][$v['codecd']]=$v['value'];
			}
		}

		// naver
		if($payment_gateway['pcCardCompanyCode']) foreach($payment_gateway['pcCardCompanyCode'] as $k => $code)
		{
			$tmp = explode(',',$payment_gateway['pcCardCompanyTerms'][$k]);
			if( in_array($code,array('ALL')) ) $str_noint = $tmp[count($tmp)-1]."개월";

			if(count($tmp) > 1){
				$r_tmp_noint[] = $payment_gateway['arrCardCompany'][$code].$tmp[0].'~'.$tmp[count($tmp)-1];
			}else{
				$r_tmp_noint[] = $payment_gateway['arrCardCompany'][$code].$tmp[0];
			}
		}
		if(!$str_noint && $r_tmp_noint){
			$str_noint = implode('/',$r_tmp_noint);
		}
		$this->noint = $str_noint;

		// about
		$max = 0;
		if($payment_gateway['pcCardCompanyCode']) foreach($payment_gateway['pcCardCompanyCode'] as $k => $code)
		{
			$tmp = explode(',',$payment_gateway['pcCardCompanyTerms'][$k]);

			if($tmp[count($tmp)-1] >  $max){
				if( in_array($code,array('ALL')) ){
					$this->noint_about = "전체카드,".$tmp[count($tmp)-1];
				}else{
					$this->noint_about = $payment_gateway['arrCardCompany'][$code]."카드,".$tmp[count($tmp)-1];
				}
			}
		}


	}

	//할인쿠폰 상품상세
	public function _goods_coupon_max($goods)
	{
		$max = 0;
		$memberSeq = "";
		$today = date('Y-m-d',time());
		$this->load->model('couponmodel');
		$tmp = $this->goodsmodel -> get_goods_category($goods['goods_seq']);
		foreach($tmp as $data) $category[] = $data['category_code'];
		$result = $this->couponmodel->get_able_download_list($today,$memberSeq,$goods['goods_seq'],$category,$goods['price']);
		foreach($result as $key => $data){
			if($max < $data['goods_sale']) {
				$max = $data['goods_sale'];
				$maxCoupon = $data;
			}
		}
		return $maxCoupon;
	}

	public function apply_sale(&$data_goods)
	{		
		$this->load->library('sale');

		$applypage		= 'list';
		if	(!$this->reserves)	$this->reserves	= config_load('reserve');

		//----> sale library 적용 ( 정가기준 목록에서는 sale_price를 넘기지 않음 )
		unset($param, $sales);
		$this->sale->reset_init();
		$param['cal_type']			= 'each';
		$param['option_type']		= 'option';
		$param['reserve_cfg']		= $this->reserves;
		$param['member_seq']		= 0;
		$param['group_seq']			= 0;
		$param['consumer_price']	= $data_goods['consumer_price'];
		$param['price']				= $data_goods['price'];
		$param['total_price']		= $data_goods['price'];
		$param['ea']				= 1;
		$param['goods_ea']			= 1;
		$param['category_code']		= $data_goods['r_category'];
		$param['goods_seq']			= $data_goods['goods_seq'];
		if ($data_goods['marketing_sale']) $param['marketing_sale']	 = $data_goods['marketing_sale'];
		$param['goods']				= $data_goods;
		$this->sale->set_init($param);
		$sales						= $this->sale->calculate_sale_price($applypage);
		if ($data_goods['marketing_sale'] && $sales['sale_list']['coupon'] > 0) {
			$data_goods['coupon_won'] = iconv("UTF-8","euc-kr",$sales['sale_list']['coupon'].'원');
		}
		$data_goods['price']		= $sales['result_price'];

		return $data_goods['price'];
	}

	public function danawa()
	{
		header("Content-Type: text/html; charset=EUC-KR");
		$marketset = config_load('marketing');

		$last_update_date = '';
		if($_GET['mode'] == 'summary'){
			$mode = 'summary';
		}else{
			$mode = 'all';
		}

		$all_category = $this->categorymodel->get_all();
		foreach ($all_category as $row){
			$data['category_code'];
			$cate[$row['category_code']] = $row['title'];
		}

		// 마케팅 전달 이미지 lwh 2014-02-28
		$market_image	= config_load('marketing_image');
		if($market_image['naverImage']=='B' || !$market_image['naverImage']){
			$view_type	= "view";
		}else if($market_image['naverImage']=='C'){
			$view_type	= "large";
		}

		$query = $this->goodsmodel->get_goods_all_partner($last_update_date,$view_type,true); // 20130325
		$result = mysql_query($query);
		while ($data_goods = mysql_fetch_array($result)){ // 20130325

			$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);

			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=danawa';
			$mourl = str_replace('www.','',$data_goods['goods_url']);
			$data_goods['mourl'] = str_replace('http://','http://m.',$mourl);

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}

			// 카테고리
			$page_code ='';
			$page_name ='';
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){

					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  ($this->categorymodel->one_category_name($arr_category_code[$i]));

					$page_name .=iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i])."|";
				}else{

					$page_name .="";
				}
			}
			$page_name =substr($page_name,0,-1);
			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$data_goods['delivery'] = "0";
			}else{
			$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
			if( $delivery['type'] == 'delivery' ){
				if( $delivery['free'] && $delivery['price'] ){
					if($delivery['free'] > $data_goods['price']){
						$data_goods['delivery'] = $delivery['price'];
					}else{
						$data_goods['delivery'] = "0";
					}
				}else{
					$data_goods['delivery'] = $delivery['price'];
				}
			}else{
				$data_goods['delivery'] = "-1";
			}
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];

			// 구분
			if( $data_goods['regist_date'] > $last_update_date ){
				$data_goods['class'] = "I";
			}
			if( $data_goods['update_date'] > $last_update_date ){
				$data_goods['class'] = $arr_status[$data_goods['goods_status']];
			}
			// 2014-01-16 이벤트 문구 미노출 추가
			if($data_goods['feed_evt_sdate'] == '0000-00-00') $data_goods['feed_evt_sdate'] = '';
			if($data_goods['feed_evt_edate'] == '0000-00-00') $data_goods['feed_evt_edate'] = '';
			$data_goods['event']	= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint));

			// 모바일가
			$systemmobiles = $this->configsalemodel->get_mobile_sale_for_goods($data_goods['price']);
			$mobile_price = 0;
			if($systemmobiles['sale_price'] && $data_goods['price']){
				$mobile_sale = $systemmobiles['sale_price'] * $data_goods['price'] / 100;
				$mobile_price = $data_goods['price'] - $mobile_sale;
			}

			unset($loop);
			$loop[] = $data_goods['goods_seq']; //1.상품ID
			$loop[] = $page_name; // 2. 카테고리
			$loop[] = $data_goods['goods_name']; //3. 상품명
			$loop[] = $data_goods['manufacture']; //4. 제조사
			$loop[] = $data_goods['image_url']; //5. 이미지 url
			$loop[] = $data_goods['goods_url']; //6. 상품 url
			$loop[] = $data_goods['price']; //7. 가격
			$loop[] = $data_goods['reserve']; //8. 적립금
			$loop[] = $data_goods['coupo']; // 9. 할인쿠폰
			$loop[] = $noint; //10. 무이자할부
			$loop[] = '';//11. 사은품
			$loop[] = $data_goods['model']; //12. 모델명
			$loop[] = '';//13. 추가정보
			$loop[] = '';//14. 출시일 
			$loop[] = $data_goods['delivery'];// 15. 배송료 : 필수
			$loop[] = '';// 16. 카드프로모션명
			$loop[] = '';//17. 카드프로모션가
			$loop[] = 'null';//18. 쿠폰다운로드필요여부
			$loop[] = $mobile_price == 0 ? '' : $mobile_price;//19. 모바일상품가격
			$loop[] = '';//20. 차등배송비여부
			$loop[] = '';//21. 차등배송비내용
			$loop[] = '';//22. 별도설치비유무
			$loop[] = '';//23. 재고유무

			echo implode("^",$loop);
			echo "\r\n";

		}

	}

	public function naver()
	{
		header("Content-Type: text/html; charset=EUC-KR");
		$marketset = config_load('marketing');
		if($marketset['marketnaver']=='y'){
			if($_GET['mode'] == 'summary')
					$marketFiledaum	= ROOTPATH."/data/marketFile/naver_summary.txt";
			else	$marketFiledaum	= ROOTPATH."/data/marketFile/naver.txt";

			if(file_exists($marketFiledaum)){
				$fp = fopen($marketFiledaum,"r");
				while(!feof($fp)) echo fgetc($fp);
				exit;
			}
		}
		$last_update_date = '';
		if($_GET['mode'] == 'summary'){
			$mode = 'summary';
			$tmp = config_load('partner','naver_update');
			if($tmp['naver_update']) $last_update_date = $tmp['naver_update'];
		}else{
			$mode = 'all';
		}

		$all_category = $this->categorymodel->get_all();
		foreach ($all_category as $row){
			$data['category_code'];
			$cate[$row['category_code']] = $row['title'];
		}


		$arr_status['normal'] = "U";
		$arr_status['runout'] = "D";
		$arr_status['unsold'] = "D";

		// 마케팅 전달 이미지 lwh 2014-02-28
		$market_image	= config_load('marketing_image');
		if($market_image['naverImage']=='B' || !$market_image['naverImage']){
			$view_type	= "view";
		}else if($market_image['naverImage']=='C'){
			$view_type	= "large";
		}

		// 입점마케팅 상품 추가할인
		$marketing_sale = config_load('marketing_sale');
		$arr_marketing_sale = array();
		if (isset($marketing_sale)) {
			// 회원 등급별 할인 적용 [member_sale_type => 0 : 비회원 , 1 : 일반 등급 회원]
			if ($marketing_sale['member']=='Y' && $marketing_sale['member_sale_type']==1) $arr_marketing_sale['member'] = 'Y';

			// 할인 유입경로 적용
			if ($marketing_sale['referer']=='Y') 	{
				$arr_marketing_sale['referer'] = 'Y';
				$arr_marketing_sale['referer_url'] = 'shopping.naver.com';
			}

			// 할인 쿠폰 적용
			if ($marketing_sale['coupon']=='Y') $arr_marketing_sale['coupon'] = 'Y';

			// 모바일 할인 적용 : 지식쇼핑 모바일가격 별도 계산해서 전달
			//if ($marketing_sale['mobile']=='Y') $arr_marketing_sale['mobile'] = 'Y';
		}

		### 입점마케팅 전달 데이터 통합 설정 - 입점마케팅 상품명,카드무이자할부 leewh 2015-01-29
		$marketing_feed = config_load('marketing_feed');

		$query = $this->goodsmodel->get_goods_all_partner($last_update_date,$view_type,true); // 20130325
		$result = mysql_query($query);
		while ($data_goods = mysql_fetch_array($result)){ // 20130325
			if ($data_goods['feed_goods_use']=='Y' && !empty($data_goods['feed_goods_name']) 
				|| $data_goods['feed_goods_use']=='N' && !empty($marketing_feed['goods_name'])) {

				## 상품명 치환코드
				$replaceArr = array();
				$replaceArr['{product_name}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
				$replaceArr['{product_category}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['category_title']));
				$replaceArr['{product_brand}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['brand_title']));
				$replaceArr['{product_tag}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['keyword']));

				if ($data_goods['feed_goods_use']=='Y') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($data_goods['feed_goods_name'],$replaceArr);
				} else if($data_goods['feed_goods_use']=='N') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($marketing_feed['goods_name'],$replaceArr);
				}
			} else {
				$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			}

			$data_goods['brand']		= iconv('UTF-8', 'euc-kr',$data_goods['brand']);
			$data_goods['manufacture']	= iconv('UTF-8', 'euc-kr',$data_goods['manufacture']);
			$data_goods['orgin']		= iconv('UTF-8', 'euc-kr',$data_goods['orgin']);
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);

			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=naver';
			$mourl = str_replace('www.','',$data_goods['goods_url']);
			$data_goods['mourl'] = str_replace('http://','http://m.',$mourl);

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}

			// 카테고리
			$page_code ='';
			$page_name ='';
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){

					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  ($this->categorymodel->one_category_name($arr_category_code[$i]));

					$page_code .="<<<caid".($i+1).">>>".$data_goods['arr_category_code'][$i]."\n";
					$page_name .="<<<cate".($i+1).">>>".iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i])."\n";
				}else{
					$page_code .="<<<caid".($i+1).">>>\n";
					$page_name .="<<<cate".($i+1).">>>\n";
				}
			}

			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$data_goods['delivery'] = "0";
			}else{
				$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
				if( $delivery['type'] == 'delivery' ){
					if( $delivery['free'] && $delivery['price'] ){
						if($delivery['free'] > $data_goods['price']){
							$data_goods['delivery'] = $delivery['price'];
						}else{
							$data_goods['delivery'] = "0";
						}
					}else{
						$data_goods['delivery'] = $delivery['price'];
					}
				}else{
					$data_goods['delivery'] = "-1";
				}
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];

			// 구분
			if( $data_goods['regist_date'] > $last_update_date ){
				$data_goods['class'] = "I";
			}
			if( $data_goods['update_date'] > $last_update_date ){
				$data_goods['class'] = $arr_status[$data_goods['goods_status']];
			}
			// 2014-01-16 이벤트 문구 미노출 추가
			if($data_goods['feed_evt_sdate'] == '0000-00-00') $data_goods['feed_evt_sdate'] = '';
			if($data_goods['feed_evt_edate'] == '0000-00-00') $data_goods['feed_evt_edate'] = '';
			$data_goods['event']	= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 상품가격 추가할인 설정
			if ($arr_marketing_sale) $data_goods['marketing_sale'] = $arr_marketing_sale;

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			if (!empty($marketing_feed['cfg_card_free'])) {
				$noint = trim(iconv('UTF-8', 'euc-kr',$marketing_feed['cfg_card_free']));
			} else {
				$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint));
			}

			// 모바일가
			$systemmobiles = $this->configsalemodel->get_mobile_sale_for_goods($data_goods['price']);
			$mobile_price = 0;
			if($systemmobiles['sale_price'] && $data_goods['price']){
				$mobile_sale = $systemmobiles['sale_price'] * $data_goods['price'] / 100;
				$mobile_price = $data_goods['price'] - $mobile_sale;
				$mobile_price = $this->sale->cut_sale_price($mobile_price);
			}

			unset($loop);
			$loop[] = "<<<begin>>>";
			$loop[] = "<<<mapid>>>".$data_goods['goods_seq'];
			$loop[] = "<<<pname>>>".$data_goods['goods_name'];
			$loop[] = "<<<price>>>".$data_goods['price'];
			$loop[] = "<<<pgurl>>>".$data_goods['goods_url'];
			$loop[] = "<<<igurl>>>".$data_goods['image_url'];
			$loop[] = ${page_code}.${page_name}."<<<brand>>>".$data_goods['brand'];
			$loop[] = "<<<maker>>>".$data_goods['manufacture'];
			$loop[] = "<<<origi>>>".$data_goods['orgin'];
			$loop[] = "<<<deliv>>>".$data_goods['delivery'];
			//$loop[] = "<<<coupo>>>".$data_goods['coupo'];
			if ($data_goods['coupon_won'])$loop[] = "<<<coupo>>>".$data_goods['coupon_won'];
			if($noint)$loop[] = "<<<pcard>>>".$noint;
			$loop[] = "<<<point>>>".$data_goods['reserve'];
			if( $mode == 'summary' ){
				$loop[] = "<<<class>>>".$data_goods['class'];
				if	(date('Y-m-d') == '2014-12-12')	
					$loop[] = "<<<utime>>>".date('Y-m-d H:i:s');
				else
					$loop[] = "<<<utime>>>".$data_goods['update_date'];
			}
			$loop[] = "<<<event>>>".$data_goods['event'];
			if($mobile_price) $loop[] = "<<<mpric>>>".$mobile_price;
			$loop[] = "<<<revct>>>".$data_goods['review_count'];
			$loop[] = "<<<mourl>>>".$data_goods['mourl'];
			$loop[] = "<<<ftend>>>";

			echo implode("\r\n",$loop)."\r\n";
		}

	}

	public function about()
	{
		header("Content-Type: text/html; charset=EUC-KR");
		$marketset = config_load('marketing');
		if($marketset['marketabout']=='y'){
			$marketFiledaum	= ROOTPATH."/data/marketFile/about.txt";
			if(file_exists($marketFiledaum)){
				$fp = fopen($marketFiledaum,"r");
				while(!feof($fp)) echo fgetc($fp);
				exit;
			}
		}
		$last_update_date = '';
		if($_GET['mode'] == 'summary'){
			$mode = 'summary';
			$tmp = config_load('partner','about_update');
			if($tmp['about_update']) $last_update_date = $tmp['about_update'];
		}else{
			$mode = 'all';
		}

		$arr_status['normal'] = "C";
		$arr_status['runout'] = "D";
		$arr_status['unsold'] = "D";

		//$result = $this->goodsmodel->get_goods_all($last_update_date, '', true);
		$query = $this->goodsmodel->get_goods_all_partner($last_update_date,'view',true);
		$result = mysql_query($query);
		while ($data_goods = mysql_fetch_array($result)){

			$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			$data_goods['brand']		= iconv('UTF-8', 'euc-kr',$data_goods['brand']);
			$data_goods['manufacture']	= iconv('UTF-8', 'euc-kr',$data_goods['manufacture']);
			$data_goods['orgin']		= iconv('UTF-8', 'euc-kr',$data_goods['orgin']);
			$data_goods['model']		= iconv('UTF-8', 'euc-kr',$data_goods['model']);
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);
			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=about';

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}

			// 카테고리
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){
					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  $this->categorymodel->one_category_name($arr_category_code[$i]);
					$data_goods['arr_category'][$i]	= iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i]);
				}
			}

			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$data_goods['delivery'] = "0";
			}else{
			$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
			if( $delivery['type'] == 'delivery' ){
				$data_goods['delivery'] = (int) $delivery['price'];
			}else{
				$data_goods['delivery'] = "-1";
			}
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];

			// 수정일
			if( $data_goods['update_date'] == '0000-00-00 00:00:00' ){
				$data_goods['update_date'] = $data_goods['regist_date'];
			}

			// 구분
			if( $data_goods['regist_date'] > $last_update_date ){
				$data_goods['class'] = "C";
			}
			if( $data_goods['update_date'] > $last_update_date ){
				$data_goods['class'] = $arr_status[$data_goods['goods_status']];
			}
			// 2014-01-16 이벤트 문구 미노출 추가
			if($data_goods['feed_evt_sdate'] == '0000-00-00') $data_goods['feed_evt_sdate'] = '';
			if($data_goods['feed_evt_edate'] == '0000-00-00') $data_goods['feed_evt_edate'] = '';
			$data_goods['event']	= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint_about));

			$r_item = array();
			$r_item[] = $data_goods['goods_seq'];
			$r_item[] = 'C';
			$r_item[] = $data_goods['goods_name'];
			$r_item[] = $data_goods['price'];
			$r_item[] = $data_goods['goods_url'];
			$r_item[] = $data_goods['image_url'];
			$r_item[] = $data_goods['arr_category_code'][0];
			$r_item[] = $data_goods['arr_category_code'][1];
			$r_item[] = $data_goods['arr_category_code'][2];
			$r_item[] = $data_goods['arr_category_code'][3];
			$r_item[] = $data_goods['arr_category'][0];
			$r_item[] = $data_goods['arr_category'][1];
			$r_item[] = $data_goods['arr_category'][2];
			$r_item[] = $data_goods['arr_category'][3];
			$r_item[] = $data_goods['model'];
			$r_item[] = $data_goods['brand'];
			$r_item[] = $data_goods['manufacture'];
			$r_item[] = $data_goods['orgin'];
			$r_item[] = '';
			$r_item[] = $data_goods['delivery'];
			$r_item[] = $data_goods['event'];
			$r_item[] = $data_goods['coupon'];
			$r_item[] = $noint;
			$r_item[] = $data_goods['reserve'];
			$r_item[] = '';
			$r_item[] = '';
			$r_item[] = '';
			$r_item[] = $data_goods['update_date'];

			/*{.goods_seq}<!>C<!>{.goods_name}<!>{.price}<!>{.goods_url}<!>{.image_url}<!>{.arr_category_code[0]}<!>{.arr_category_code[1]}<!>{.arr_category_code[2]}<!>{.arr_category_code[3]}<!>{.arr_category[0]}<!>{.arr_category[1]}<!>{.arr_category[2]}<!>{.arr_category[3]}<!>{.model}<!>{.brand}<!>{.manufacture}<!>{.orgin}<!><!>{.delivery}<!>{.event}<!>{.coupon}<!><!>{.reserve}<!><!><!><!>{.update_date}*/

			echo implode('<!>',$r_item)."\r\n";
		}

		config_save('partner',array('about_update'=>date('Y-m-d H:i:s')));
		/*
		$this->template->template_dir = BASEPATH."../partner";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>"about.html"));
		$this->template->assign('result',$result);
		$this->template->assign('mode',$mode);
		$this->template->print_("tpl");
		*/
	}

	public function daum()
	{
		header("Content-Type: text/html; charset=EUC-KR");
		$marketset = config_load('marketing');
		if($marketset['marketdaum']=='y'){
			$marketFiledaum	= ROOTPATH."/data/marketFile/daum.txt";
			if(file_exists($marketFiledaum)){
				$fp = fopen($marketFiledaum,"r");
				while(!feof($fp)) echo fgetc($fp);
				exit;
			}
		}

		$last_update_date = '';
		if($_GET['mode'] == 'summary'){
			$mode = 'summary';
			$tmp = config_load('partner','daum_update');
			if($tmp['naver_update']) $last_update_date = $tmp['daum_update'];
		}else{
			$mode = 'all';
		}

		// 마케팅 전달 이미지 lwh 2014-02-28
		$market_image	= config_load('marketing_image');
		if($market_image['daumImage']=='B' || !$market_image['daumImage']){
			$view_type	= "view";
		}else if($market_image['daumImage']=='C'){
			$view_type	= "large";
		}

		// 입점마케팅 상품 추가할인
		$marketing_sale = config_load('marketing_sale');
		$arr_marketing_sale = array();
		if (isset($marketing_sale)) {
			// 회원 등급별 할인 적용 [member_sale_type => 0 : 비회원 , 1 : 일반 등급 회원]
			if ($marketing_sale['member']=='Y' && $marketing_sale['member_sale_type']==1) $arr_marketing_sale['member'] = 'Y';

			// 할인 유입경로 적용
			if ($marketing_sale['referer']=='Y') 	{
				$arr_marketing_sale['referer'] = 'Y';
				$arr_marketing_sale['referer_url'] = 'shopping.daum.net';
			}

			// 할인 쿠폰 적용
			if ($marketing_sale['coupon']=='Y') $arr_marketing_sale['coupon'] = 'Y';

			// 모바일 할인 적용 : 쇼핑하우 미적용(쇼핑하우 전달용 모바일 할인 필드 없음)
			//if ($marketing_sale['mobile']=='Y') $arr_marketing_sale['mobile'] = 'Y';
		}

		### 입점마케팅 전달 데이터 통합 설정 - 입점마케팅 상품명,카드무이자할부 leewh 2015-01-29
		$marketing_feed = config_load('marketing_feed');

		$query = $this->goodsmodel->get_goods_all_partner($last_update_date,$view_type,true);
		$result = mysql_query($query);
		while ($data_goods = mysql_fetch_array($result)){ // 20130325
			if ($data_goods['feed_goods_use']=='Y' && !empty($data_goods['feed_goods_name']) 
				|| $data_goods['feed_goods_use']=='N' && !empty($marketing_feed['goods_name'])) {

				## 상품명 치환코드
				$replaceArr = array();
				$replaceArr['{product_name}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
				$replaceArr['{product_category}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['category_title']));
				$replaceArr['{product_brand}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['brand_title']));
				$replaceArr['{product_tag}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['keyword']));

				if ($data_goods['feed_goods_use']=='Y') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($data_goods['feed_goods_name'],$replaceArr);
				} else if($data_goods['feed_goods_use']=='N') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($marketing_feed['goods_name'],$replaceArr);
				}
			} else {
				$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			}
			$data_goods['brand']		= iconv('UTF-8', 'euc-kr',$data_goods['brand']);
			$data_goods['manufacture']	= iconv('UTF-8', 'euc-kr',$data_goods['manufacture']);
			$data_goods['orgin']		= iconv('UTF-8', 'euc-kr',$data_goods['orgin']);
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);

			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=daum';

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}


			// 카테고리
			$page_code ='';
			$page_name ='';
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){

					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  ($this->categorymodel->one_category_name($arr_category_code[$i]));

					$page_code .="<<<cate".($i+1).">>>".$data_goods['arr_category_code'][$i]."\n";
					$page_name .="<<<catename".($i+1).">>>".iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i])."\n";
				}
			}


			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$data_goods['deliv1'] = "0";
				$data_goods['deliv2'] = "";
				$data_goods['deliv2'] = iconv('UTF-8', 'euc-kr',"무료");
			}else{
				$data_goods['deliv1'] = "1";
				$data_goods['deliv2'] = "유료";
				$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
				if( $delivery['type'] == 'delivery' ){
					if( $delivery['free'] && $delivery['price'] ){
						$data_goods['deliv1'] = "2";
						$data_goods['deliv2'] = $delivery['free']."원 이상무료 or ".$delivery['price']."원";
					}else if( ! $delivery['price'] ){
						$data_goods['deliv1'] = "0";
						$data_goods['deliv2'] = "";
					}else{
						$data_goods['deliv1'] = "1";
						$data_goods['deliv2'] = $delivery['price'];
					}
				}
				$data_goods['deliv2'] = iconv('UTF-8', 'euc-kr',$data_goods['deliv2']);
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];
			$data_goods['shop_name'] = $this->config_basic['shopName'];
			$data_goods['shop_name'] = iconv('UTF-8', 'euc-kr',$data_goods['shop_name']);
			$data_goods['event']		= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 상품가격 추가할인 설정
			if ($arr_marketing_sale) $data_goods['marketing_sale'] = $arr_marketing_sale;

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			if (!empty($marketing_feed['cfg_card_free'])) {
				$noint = trim(iconv('UTF-8', 'euc-kr',$marketing_feed['cfg_card_free']));
			} else {
				$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint));
			}

			unset($loop);
			$loop[] = "<<<begin>>>";
			$loop[] = "<<<pid>>>".$data_goods['goods_seq'];
			$loop[] = "<<<price>>>".$data_goods['price'];
			$loop[] = "<<<pname>>>".$data_goods['goods_name'];
			$loop[] = "<<<pgurl>>>".$data_goods['goods_url'];
			$loop[] = "<<<igurl>>>".$data_goods['image_url'];
			$loop[] = ${page_code}.${page_name}."<<<model>>>".iconv('UTF-8', 'euc-kr',$data_goods['model']);
			$loop[] = "<<<brand>>>".$data_goods['brand'];
			$loop[] = "<<<maker>>>".$data_goods['manufacture'];
			$loop[] = "<<<pdate>>>";
			//$loop[] = "<<<coupon>>>".$data_goods['coupon'];
			if ($data_goods['coupon_won'])$loop[] = "<<<coupon>>>".$data_goods['coupon_won'];
			if($noint)$loop[] = "<<<pcard>>>".$noint;
			$loop[] = "<<<point>>>".$data_goods['reserve'];
			$loop[] = "<<<deliv>>>".$data_goods['deliv1'];
			$loop[] = "<<<deliv2>>>".$data_goods['deliv2'];
			$loop[] = "<<<sellername>>>".$data_goods['shop_name'];
			$loop[] = "<<<event>>>".$data_goods['event'];

			$loop[] = "<<<end>>>";

			echo implode("\r\n",$loop)."\r\n";

		}
	}

	public function navercheckout_add()
	{
		$member_seq = "";
		$pre_cart_seqs = "";
		$mode = "direct";

		$this->load->model('cartmodel');
		$this->load->model('Goodsfblike');

		if( !isset($_POST['option']) ){
			openDialogAlert("옵션을 선택해 주세요.",400,140,'parent',"");
			exit;
		}

		$goods_seq = (int) $_POST['goodsSeq'];
		$this->load->model('goodsmodel');
		$inputs = $this->goodsmodel->get_goods_input($goods_seq);
		$inputs_required = false;
		$file_num = 0;
		foreach($inputs as $key_input => $data_input){

			$_POST['inputsValue'][$key_input] = trim( $_POST['inputsValue'][$key_input] );
			if( $data_input['input_require'] == 1 && !$_POST['inputsValue'][$key_input] && $data_input['input_form'] != 'file' ){
				openDialogAlert(addslashes($data_input['input_name']) . " 옵션은 필수입니다.",400,140,'parent',"");
				exit;
			}else if( $data_input['input_require'] == 1 && $data_input['input_form'] == 'file' && !$_FILES['inputsValue']['tmp_name'][$file_num] ){
				openDialogAlert(addslashes($data_input['input_name']) . " 옵션은 필수입니다.",400,140,'parent',"");
				exit;
			}elseif($data_input['input_require'] == 1){
				$inputs_required = true;
			}

			if( $data_input['input_form'] == 'file' ){
				$file_num++;
			}
		}

		if(!$_POST['suboptionTitle']) $_POST['suboptionTitle'] = array();
		$suboption_required = false;
		foreach($_POST['suboption_title_required'] as $required_title){
			if( !in_array($required_title,$_POST['suboptionTitle']) ){
				openDialogAlert(addslashes($required_title) . " 옵션은 필수입니다.",400,140,'parent',"");
				exit;
			}
			$suboption_required = true;
		}

		foreach($_POST['optionEa'] as $k1 => $ea){
			for($i=1;$i<=5;$i++){
				if(!isset($_POST['option'][$i][$k1])){
					$_POST['option'][$i][$k1] = "";
					$_POST['optionTitle'][$i][$k1] = "";
				}
			}
		}

		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']) $member_seq = $this->userInfo['member_seq'];

		if($mode != "cart"){
			// 바로구매 시
			$this->cartmodel->delete_mode($mode);
		}

		$insert_data['goods_seq'] 	= $goods_seq;
		$insert_data['session_id'] 	= $session_id;
		$insert_data['member_seq'] 	= $member_seq;
		$insert_data['distribution'] = $mode;
		$insert_data['regist_date '] = $insert_data['update_date'] = date('Y-m-d H:i:s',time());

		$fb_data = $this->Goodsfblike->get_data(array("select"=>"like_seq","whereis"=>"AND (session_id='$session_id' OR member_seq='$member_seq')"));
		$insert_data['fblike'] = 'N';
		if(  $fb_data['like_seq'] ) $insert_data['fblike'] = 'Y';


		$this->db->insert('fm_cart', $insert_data);
		$cart_seq = $this->db->insert_id();

		$path = ROOTPATH."data/order/";
		if(!is_dir($path)){
			@mkdir($path);
			@chmod($path,0777);
		}

		unset($insert_data);
		foreach($_POST['optionEa'] as $k1 => $ea){
			for($i=0;$i<5;$i++){
				if( !isset($_POST['option'][$i][$k1]) || !$_POST['option'][$i][$k1] ) $_POST['option'][$i][$k1] = "";
				if( !isset($_POST['optionTitle'][$i][$k1]) || !$_POST['optionTitle'][$i][$k1] ) $_POST['optionTitle'][$i][$k1] = null;
			}
			$insert_data['option1']		= $_POST['option'][0][$k1];
			$insert_data['title1']		= $_POST['optionTitle'][0][$k1];
			$insert_data['option2']		= $_POST['option'][1][$k1];
			$insert_data['title2']		= $_POST['optionTitle'][1][$k1];
			$insert_data['option3']		= $_POST['option'][2][$k1];
			$insert_data['title3']		= $_POST['optionTitle'][2][$k1];
			$insert_data['option4']		= $_POST['option'][3][$k1];
			$insert_data['title4']		= $_POST['optionTitle'][3][$k1];
			$insert_data['option5']		= $_POST['option'][4][$k1];
			$insert_data['title5']		= $_POST['optionTitle'][4][$k1];
			$insert_data['ea'] 			= $ea;
			$insert_data['cart_seq']	= $cart_seq;
			$this->db->insert('fm_cart_option', $insert_data);
			if($k1 == 0 )	$cart_option_seq = $this->db->insert_id();
		}

		unset($insert_data);
		if( isset($_POST['suboption']) ){
			foreach($_POST['suboption'] as $k1 => $suboption){
				if($_POST['suboptionEa'][$k1]){
					$insert_data['ea']				= $_POST['suboptionEa'][$k1];
					$insert_data['suboption_title']	= $_POST['suboptionTitle'][$k1];
					$insert_data['suboption']		= $suboption;
					$insert_data['cart_seq'] 		= $cart_seq;
					$insert_data['cart_option_seq'] 		= $cart_option_seq;
					$this->db->insert('fm_cart_suboption', $insert_data);
				}
			}
		}

		unset($insert_data);
		if( isset($_POST['inputsValue']) ){
			$i = 0;
			$j = 0;
			foreach($inputs as $key_input => $data_input){
				unset($insert_data);
				if($data_input['input_form']=='file'){
					if($_FILES['inputsValue']['name'][$j]){
						$tmp = explode(".",$_FILES['inputsValue']['name'][$j]);
						$fileName = "input_".$cart_seq."_".date('YmdHis')."_".$j.".".$tmp[count($tmp)-1];
						move_uploaded_file($_FILES['inputsValue']['tmp_name'][$j], $path.$fileName);
						$insert_data['type'] 		= 'file';
						$insert_data['input_title'] = $data_input['input_name'];
						$insert_data['input_value'] = $fileName;
					}
					$j++;
				}else{
					$insert_data['type'] 		= 'text';
					$insert_data['input_title'] = $data_input['input_name'];
					$insert_data['input_value'] = $_POST['inputsValue'][$i];
					$i++;
				}
				if($insert_data){
					$insert_data['cart_seq'] 	= $cart_seq;
					$insert_data['cart_option_seq'] = $cart_option_seq;
					$this->db->insert('fm_cart_input', $insert_data);
				}

			}
		}
	}

	public function navercheckout_makeQueryString($shopId,$id,$name,$count,$option,$tprice,$uprice,$option_code='') {
			$ret .= 'ITEM_ID=' . urlencode($id);
			$ret .= '&EC_MALL_PID=' . urlencode($id);
			$ret .= '&ITEM_NAME=' . urlencode($name);
			$ret .= '&ITEM_COUNT=' . $count;
			$ret .= '&ITEM_OPTION=' . urlencode($option);
			if($option_code){
				$ret .= '&ITEM_OPTION_CODE=' . urlencode($option_code);
			}
			$ret .= '&ITEM_TPRICE=' . $tprice;
			$ret .= '&ITEM_UPRICE=' . $uprice;
			return $ret;
	}

	public function navercheckout()
	{ 
		$this->load->helper('shipping');
		$this->load->model('cartmodel');
		if($_GET['mode']=='direct'){
			$this->navercheckout_add();
		}
		$cart = $this->cartmodel->catalog();

		// 배송정책이 택배가 없을 경우
		$etc_delivery_msg = false;		
		foreach($cart['list'] as $data_option){			
			$delivery_policy = $this->goodsmodel->get_goods_delivery($data_option);			
			if( !preg_match('/postpaid|delivery/',$delivery_policy['type']) ){
				if( preg_match('/quick/',$delivery_policy['type']) )
				{
					$etc_delivery_msg = "퀵서비스배송";
				}else if( preg_match('/direct/',$delivery_policy['type']) ) {
					$etc_delivery_msg = "직접수령배송";
				}
			}			
		}		

		if( $etc_delivery_msg ){
			$_GET['shippingType'] = "ONDELIVERY";
		}		

		$totalMoney = 0;
		$navercheckout = config_load('navercheckout');
		$shopId = $navercheckout['shop_id'];
		$certiKey = $navercheckout['certi_key'];


		// 모바일/like 할인/적립
		$this->load->model('configsalemodel');
		$sc['type'] = 'mobile';
		$systemmobiles = $this->configsalemodel->lists($sc);
		$sc['type'] = 'fblike';
		$systemfblike = $this->configsalemodel->lists($sc);

		$queryString_sub = "";
		foreach($cart['list'] as $data_option){
			$num++;
			$sub_price = 0;
			$sub_option = "";
			$arr_option = array();
			$item_total_ea = 0;

			$arr_option = array();
			for($i=1;$i<6;$i++){
				$title_field = 'title'.$i;
				$option_field = 'option'.$i;
				if($data_option[$title_field] && $data_option[$option_field]){
					$arr_option[] = $data_option[$title_field].":".$data_option[$option_field];
				}
			}
			$option_str = $arr_option[0];
			if($etc_delivery_msg){
				$arr_option[] = $etc_delivery_msg;
			}

			if( $data_option['price'] < 1 ) continue;

			foreach($data_option['cart_inputs'] as $k2=>$data_input){
				$inputValue = $data_input['input_value'];
				if($data_input['type']=='file'){
					$inputValue = "http://".$_SERVER['HTTP_HOST']."/data/order/".$inputValue;
				}
				if(trim($data_input['input_value'])) $arr_option[] = $data_input['input_title'].":".strip_tags($inputValue);
			}

			/* 회원할인계산 */
			$member_sale = 0;
			$members['group_seq'] = 0;
			$data_option['member_sale_unit'] = $this->membermodel->get_member_group($members['group_seq'],$data_option['goods_seq'],$category,$data_option['price'],$cart['total'], $data_option["sale_seq"]);

			// 모바일 할인
			if($this->_is_mobile_agent) {//mobile 접속시  %할인, 추가적립 $this->mobileMode  ||
				$data_option['mobile_sale'] = 0;
				foreach($systemmobiles['result'] as $fblike => $systemmobiles_price) {
					if($systemmobiles_price['price1']<= $cart['total'] && $systemmobiles_price['price2'] >= $cart['total']){
						$opt_mobile_goods_sale = $systemmobiles_price['sale_price'] * $data_option['price'] / 100; // 모바일 할인
						$opt_mobile_goods_sale = get_price_point($opt_mobile_goods_sale,$this->config_system);
						$data_option['mobile_sale_unit'] = $opt_mobile_goods_sale;
						$data_option['mobile_sale'] = ($opt_mobile_goods_sale * $data_option['ea']);
						break;
					}//endif
				}//end foreach
			}

			// like 할인
			$data_option['fblike_sale'] = 0;
			if($data_option['fblike'] == 'Y'){//facebook like %할인, 추가적립
				foreach($systemfblike['result'] as $fblike => $systemfblike_price) {
					if($systemfblike_price['price1']<= $cart['total'] && $systemfblike_price['price2'] >= $cart['total']){
						$opt_fblike_goods_sale = $systemfblike_price['sale_price'] * $data_option['price'] / 100; // 좋아요 할인
						$opt_fblike_goods_sale = get_price_point($opt_fblike_goods_sale,$this->config_system);
						$data_option['fblike_sale_unit'] = $opt_fblike_goods_sale;
						$data_option['fblike_sale'] = ($opt_fblike_goods_sale * $data_option['ea']);
						break;
					}//endif
				}//end foreach
			}

			## 유입경로 할인
			if($this->session->userdata('shopReferer')){
				$this->load->model('referermodel');
				$referersale	= $this->referermodel->sales_referersale($this->session->userdata('shopReferer'), $data_option['goods_seq'], $data_option['price'], 1);
				$data_option['referersale_seq'] = $referersale['referersale_seq'];
				$data_option['referer_sale_unit'] = $referersale['sales_price'];
			}

			$id = $data_option['goods_seq'];
			$name = $data_option['goods_name'];
			$uprice = $data_option['price'] - (int) $data_option['member_sale_unit'] - (int) $data_option['mobile_sale_unit']  - (int) $data_option['fblike_sale_unit'] - (int) $data_option['referer_sale_unit'];
			$count = $data_option['ea'];
			$tprice = $uprice * $count;
			$item_total_ea += $count;

			$option = implode(' / ',$arr_option);

			if( strlen($option) > 4000 ){
				alert('옵션의 길이가 너무 깁니다.');
				exit;
			}

			//옵션상품코드
			list($data_option['optioncode1'],$data_option['optioncode2'],$data_option['optioncode3'],$data_option['optioncode4'],$data_option['optioncode5'],$data_option['color'],$data_option['zipcode'],$data_option[0]['address_type'],$data_option['address'],$data_option[0]['address_street'],$data_option['addressdetail'],$data_option['biztel'],$data_option['coupon_input'],$data_option['codedate'],$data_option['sdayinput'],$data_option['fdayinput'],$data_option['dayauto_type'],$data_option['sdayauto'],$data_option['fdayauto'],$data_option['dayauto_day'],$data_option['newtype'],$data_option['address_commission']) = $this->goodsmodel->get_goods_option_code(
				$data_option['goods_seq'],
				$data_option['option1'],
				$data_option['option2'],
				$data_option['option3'],
				$data_option['option4'],
				$data_option['option5']
			);
			$opt_goods_code = $data_option['goods_code'].$data_option['optioncode1'].$data_option['optioncode2'].$data_option['optioncode3'].$data_option['optioncode4'].$data_option['optioncode5'];//조합된상품코드

			$totalMoney += $tprice;
			$queryString_sub .= '&'.$this->navercheckout_makeQueryString($shopId,$id,$name,$count,$option,$tprice,$uprice,$opt_goods_code);

			foreach($data_option['cart_suboptions'] as $data_sub){
				$arr_option = array();
				if( $data_option['price'] ){
					$arr_option[] =  $data_sub['suboption_title'].":".$data_sub['suboption'];
					if($option_str){
						$option = $option_str ."의 추가옵션 - ". implode(' / ',$arr_option);
					}else{
						$option = "추가옵션 - ". implode(' / ',$arr_option);
					}
					$id = $data_sub['goods_seq'];
					$name = $name;
					$uprice = $data_sub['price'];
					$count = $data_sub['ea'];
					$tprice = $uprice * $count;
					$totalMoney += $tprice;
					
					$subopt_goods_code = $data_option['goods_code'].$data_sub['suboption_code'];//조합된상품코드
					$queryString_sub .= '&'.$this->navercheckout_makeQueryString($shopId,$id,$name,$count,$option,$tprice,$uprice,$subopt_goods_code);
				}else{
					alert('가격이 없는 추가옵션은 체크아웃으로 구매하실 수 없습니다.');
					exit;
				}
			}

			$goods = $this->goodsmodel->get_goods($data_option['goods_seq']);

			if( $goods['min_purchase_limit'] == 'limit' && $item_total_ea < $goods['min_purchase_ea']){
				openDialogAlert(strip_tags($goods['goods_name'])." 상품은 최소 {$goods['min_purchase_ea']}개 이상 구매하실 수 있습니다.",400,140,'parent',"");
				exit;
			}
			if( $goods['max_purchase_limit'] == 'limit' && $item_total_ea > $goods['max_purchase_ea']){
				openDialogAlert(strip_tags($goods['goods_name'])." 상품은 최대 {$goods['max_purchase_ea']}개 이하로 구매하실 수 있습니다.",400,140,'parent',"");
				exit;
			}

			// 예외카테고리 체크
			$categorys = $this->goodsmodel->get_goods_category($goods['goods_seq']);
			foreach($navercheckout['except_category_code'] as $v1){
				foreach($categorys as $v2){
					if($v1['category_code']==$v2 || preg_match("/^".$v1['category_code']."/",$v2)){
						openDialogAlert("{$goods['goods_name']} 상품은 네이버 체크아웃 예외카테고리에 속해있습니다.",400,140,'parent',"");
					exit;
					}
				}
			}

			// 예외상품 체크
			foreach($navercheckout['except_goods'] as $v1){
				if($v1['goods_seq']==$goods['goods_seq']){
					openDialogAlert("{$goods['goods_name']} 상품은 네이버 체크아웃 예외상품입니다.",400,140,'parent',"");
					exit;
				}
			}

		}

		$shippingPrice = array_sum($cart['shipping_price']);
		$orderDeliveryFree = false;
		$shopDeliveryFree = false;

		// 조건부 무료배송 금액차감
		$this->load->helper('shipping');
		$shipping = use_shipping_method();
		if($shipping[0][0]['deliveryCostPolicy'] == 'ifpay'){
			if($totalMoney && $shipping[0][0]['ifpayFreePrice']){
				if($shipping[0][0]['ifpayFreePrice'] <= $totalMoney){
					$shopDeliveryFree = true;
				}
			}
		}

		// 특정상품 구매시 무료
		foreach($cart['data_goods'] as $goods_seq => $data_goods){
			if($shipping[0][0]['issueGoods'] && in_array($goods_seq,$shipping[0][0]['issueGoods'])){
				$orderDeliveryFree = true;
			}

			if( $data_goods['r_category'] ) foreach($data_goods['r_category'] as $catecd){
				if($shipping[0][0]['issueCategoryCode'] && in_array($catecd,$shipping[0][0]['issueCategoryCode'])){
					$orderDeliveryFree = true;
				}
			}

			if( $data_goods['r_brand'] ) foreach($data_goods['r_brand'] as $brandcd){
				if($shipping[0][0]['issueBrandCode'] && in_array($brandcd,$shipping[0][0]['issueBrandCode'])){
					$orderDeliveryFree = true;
				}
			}
			if($data_goods['shipping_policy']=='shop'){
				$order_basic_delivery = true;
			}
		}
		foreach($cart['data_goods'] as $goods_seq => $data_goods){
			if(in_array($goods_seq,$shipping[0][0]['exceptIssueGoods'])){
				$orderDeliveryFree = false;
			}
		}

		if( $shipping[0][0]['orderDeliveryFree'] == 'free' && $orderDeliveryFree){
			$shopDeliveryFree = true;
		}

		if( $shopDeliveryFree ){
			$shippingPrice -= (int) $cart['shipping_price']['shop'];
		}


		if( $_GET['shippingType']  == 'ONDELIVERY') $shippingType = "ONDELIVERY";
		else{	
			if ($shippingPrice > 0) {
				$shippingType = "PAYED";
				if($_GET['shippingType']) $shippingType = $_GET['shippingType'];
			} else {
				$shippingType = "FREE";
			}
		}

		$backUrl = $_SERVER['HTTP_REFERER'];
		$queryString = 'SHOP_ID='.urlencode($shopId);
		$queryString .= '&CERTI_KEY='.urlencode($certiKey);
		$queryString .= '&SHIPPING_TYPE='.$shippingType;
		$queryString .= '&SHIPPING_PRICE='.$shippingPrice;
		$queryString .= '&RESERVE1=&RESERVE2=&RESERVE3=&RESERVE4=&RESERVE5=';
		$queryString .= '&BACK_URL='.$backUrl;
		$queryString .= '&SA_CLICK_ID='.$_COOKIE['NVADID']; //CTS 네이버검색광고 이용가맹점 중 전환데이터를 원할경우 SA URL파라미터중 NVADID를 입력
		$queryString .= '&CPA_INFLOW_CODE='.urlencode($_COOKIE["CPAValidator"]);// CPA 스크립트 가이드 설치업체는 해당 값 전달
		$queryString .= '&NAVER_INFLOW_CODE='.urlencode($_COOKIE["NA_CO"]); // 네이버 서비스 유입 경로 코드
		$queryString .= '&NMILEAGE_INFLOW_CODE='; // 네이버마일리지 유입 경로 코드
		$queryString .= $queryString_sub;

		if( !$totalMoney ){
			openDialogAlert("주문금액이 0원입니다.",400,140,'parent',"");
			exit;
		}

		if($_GET['shippingType']=='ONDELIVERY'){
			$totalPrice = (int)$totalMoney;	// 착불배송시
		}else{
			$totalPrice = (int)$totalMoney + (int)$shippingPrice; // 선불배송시
		}
		$queryString .= '&TOTAL_PRICE='.$totalPrice;

		if($navercheckout['use']=='test'){
			$orderUrl = 'https://test-checkout.naver.com/customer/api/order.nhn';
		}else{
			$orderUrl = 'https://checkout.naver.com/customer/api/order.nhn';
		}
		
		$cu = curl_init();
		curl_setopt($cu, CURLOPT_URL,$orderUrl); // 데이터를 보낼 URL 설정
		curl_setopt($cu, CURLOPT_HEADER, FALSE);
		curl_setopt($cu, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($cu, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
		curl_setopt($cu, CURLOPT_POST, 1); // 데이터를 get/post 로 보낼지 설정.
		curl_setopt($cu, CURLOPT_POSTFIELDS, $queryString); // 보낼 데이터를 설정. 형식은 GET 방식으로설정
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1); // REQUEST 에 대한 결과값을 받을 것인지 체크.#Resource ID 형태로 넘어옴 :: 내장 함수 curl_errno 로 체크
		curl_setopt($cu, CURLOPT_TIMEOUT,60); // REQUEST 에 대한 결과값을 받는 시간 설정.
		curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, 0); //
		curl_setopt($cu, CURLOPT_SSL_VERIFYHOST, 0); //

		$orderId = curl_exec($cu); // 실행

		if (curl_getinfo($cu, CURLINFO_HTTP_CODE) == 200) {
			$resultCode = 200;
			curl_close($cu);
		} else {
			alert('동시에 접속하는 이용자 수가 많거나 인터넷 네트워크 상태가 불안정하여 현재 체크아웃 서비스 접속이 불가합니다.이용에 불편을 드린 점 진심으로 사과드리며, 잠시 후 다시 접속해 주시기 바랍니다.');
			curl_close($cu);
			exit(-1);
		}

		if( strlen(trim($orderId)) > 5000){
			alert('동시에 접속하는 이용자 수가 많거나 인터넷 네트워크 상태가 불안정하여 현재 체크아웃 서비스 접속이 불가합니다.이용에 불편을 드린 점 진심으로 사과드리며, 잠시 후 다시 접속해 주시기 바랍니다.');
			exit;
		}

		if($navercheckout['use']=='test'){
			if($this->mobileMode)
				$orderUrl = 'https://test-m.checkout.naver.com/mobile/customer/order.nhn';
			else
				$orderUrl = 'https://test-checkout.naver.com/customer/order.nhn';
		}else{
			if($this->mobileMode)
				$orderUrl = 'https://m.checkout.naver.com/mobile/customer/order.nhn';
			else
				$orderUrl = 'https://checkout.naver.com/customer/order.nhn';
		}

		//여기서 받은 orderId로 주문서 page를 호출한다.
		echo ($orderId."\r\n");

		echo("<html>
		<body>
		<form name='frm' method='get' action='".$orderUrl."'>
		<input type='hidden' name='ORDER_ID' value='".$orderId."'>
		<input type='hidden' name='SHOP_ID' value='".$shopId."'>
		<input type='hidden' name='TOTAL_PRICE' value='".$totalPrice."'>
		</form>
		</body>
		<script>");
		if ($resultCode == 200) {
			echo("document.frm.target = '_top';
			document.frm.submit();");
		}
		echo("
		</script>
		</html>");

	}

	public function navercheckout_zzim_makeQueryString($id, $name, $uprice, $image, $thumb, $url) {
		$ret .= 'ITEM_ID=' . urlencode($id);
		$ret .= '&ITEM_NAME=' . urlencode($name);
		$ret .= '&ITEM_UPRICE=' . $uprice;
		$ret .= '&ITEM_IMAGE=' . urlencode($image);
		$ret .= '&ITEM_THUMB=' . urlencode($thumb);
		$ret .= '&ITEM_URL=' . urlencode($url);
		return $ret;
	}

	public function navercheckout_zzim()
	{
		$goods_seq = $_POST['goodsSeq'];
		$navercheckout = config_load('navercheckout');
		$shopId = $navercheckout['shop_id'];
		$certiKey = $navercheckout['certi_key'];
		$queryString = 'SHOP_ID='.urlencode($shopId);
		$queryString .= '&CERTI_KEY='.urlencode($certiKey);
		$queryString .= '&RESERVE1=&RESERVE2=&RESERVE3=&RESERVE4=&RESERVE5=';

		$data_goods = $this->goodsmodel->get_goods($goods_seq);
		$data_images = $this->goodsmodel->get_goods_image($goods_seq);
		$data_options = $this->goodsmodel->get_goods_option($goods_seq);
		if($data_options)foreach($data_options as $k => $opt){
			if($k == 0) $uprice = $opt['price'];
			if($opt['default_option'] == 'y'){
				$data_goods['price'] = $opt['price'];
			}
		}

		$id = $data_goods["goods_seq"];
		$data_goods['goods_name']	= strip_tags($data_goods['goods_name']);
		$name = $data_goods["goods_name"];
		if($data_goods['price']) $uprice = $data_goods['price'];

		$domain = preg_replace("/^m\./","",$_SERVER['HTTP_HOST']);

		$image = $data_images[1]['view']['image'];
		$thumb = $data_images[1]['list1']['image'];

		if(!preg_match("/http/",$data_images[1]['view']['image']))$image = "http://".$domain.$data_images[1]['view']['image'];
		if(!preg_match("/http/",$data_images[1]['list1']['image']))$thumb = "http://".$domain.$data_images[1]['list1']['image'];
		$url = "http://".$domain."/goods/view?no=".$id;
		$queryString .= '&'.$this->navercheckout_zzim_makeQueryString($id,$name,$uprice,$image,$thumb,$url);

		if($navercheckout['use']=='test'){
			$zzimUrl = 'https://test-checkout.naver.com/customer/api/wishlist.nhn';
		}else{
			$zzimUrl = 'https://checkout.naver.com/customer/api/wishlist.nhn';
		}


		$cu = curl_init();
		curl_setopt($cu, CURLOPT_URL,$zzimUrl); // 데이터를 보낼 URL 설정
		curl_setopt($cu, CURLOPT_HEADER, FALSE);
		curl_setopt($cu, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($cu, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
		curl_setopt($cu, CURLOPT_POST, 1); // 데이터를 get/post 로 보낼지 설정.
		curl_setopt($cu, CURLOPT_POSTFIELDS, $queryString); // 보낼 데이터를 설정. 형식은 GET 방식으로설정
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1); // REQUEST 에 대한 결과값을 받을 것인지 체크.#Resource ID 형태로 넘어옴 :: 내장 함수 curl_errno 로 체크
		curl_setopt($cu, CURLOPT_TIMEOUT,60); // REQUEST 에 대한 결과값을 받는 시간 설정.
		curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, 0); //
		curl_setopt($cu, CURLOPT_SSL_VERIFYHOST, 1); //
		$itemId = curl_exec($cu); // 실행

		if (curl_getinfo($cu, CURLINFO_HTTP_CODE) == 200) {
			$resultCode = 200;
			curl_close($cu);
		} else {
			echo('Response = '.curl_error($cu)."\n");
			curl_close($cu);
			exit(-1);
		}

		if($navercheckout['use']=='test'){
			if($this->mobileMode)
				$wishlistPopupUrl = "https://test-m.checkout.naver.com/mobile/customer/wishList.nhn";
			else
				$wishlistPopupUrl = "https://test-checkout.naver.com/customer/wishlistPopup.nhn";
		}else{
			if($this->mobileMode)
				$wishlistPopupUrl = "https://m.checkout.naver.com/mobile/customer/wishList.nhn";
			else
				$wishlistPopupUrl = "https://checkout.naver.com/customer/wishlistPopup.nhn";
		}

		echo("<html>
		<body>
		<form name='frm' method='get' action='".$wishlistPopupUrl."'>
		<input type='hidden' name='SHOP_ID' value='".$shopId."'>
		<input type='hidden' name='ITEM_ID' value='".$itemId."'>
		</form>
		</body>
		");
		if ($resultCode == 200) {
			echo("<script>document.frm.target = '_top'; document.frm.submit();</script>
			");
		}
		echo("</html>");

	}

	public function navercheckout_item()
	{
		$cfg_order = config_load('order');

		$query = $_SERVER['QUERY_STRING'];
		
		$vars = array();
		foreach(explode('&', $query) as $pair) {
			list($key, $value) = explode('=', $pair);
			$key = urldecode($key);
			$value = urldecode($value);
			$vars[$key][] = $value;
		}
		
		$itemIds = $vars['ITEM_ID'];
		foreach($itemIds as  $goods_seq){
			$data_goods = $this->goodsmodel->get_goods($goods_seq);
			$categorys = $this->goodsmodel->get_goods_category($goods_seq);
			$options = $this->goodsmodel->get_goods_option($goods_seq);
			$data_goods['tot_stock'] = 0;
			if($options)foreach($options as $k => $opt){
				/* 대표가격 */
				if($opt['default_option'] == 'y'){
					$data_goods['price'] = $opt['price'];
				}

				if($cfg_order['runout'] == 'ableStock'){
					$reservation_field = 'reservation25';
					if($cfg_order['ableStockStep'] == 15) $reservation_field = 'reservation15';
					$data_goods['tot_stock'] += $opt['stock'] - $opt[$reservation_field];
				}else{
					$data_goods['tot_stock'] += $opt['stock'];
				}
			}

			if($cfg_order['runout'] == 'unlimited') $data_goods['tot_stock'] = 10000;

			if($categorys) foreach($categorys as $key => $data){
				if( $data['link'] == 1 ){
					list($data_goods['category_code']) = $this->categorymodel->split_category($data['category_code']);
				}
			}
			
			// 카테고리			
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				if( $arr_category_code[$i] ){
					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  htmlspecialchars($this->categorymodel->one_category_name($arr_category_code[$i]));
				}
			}

			for($i=1;$i<6;$i++){
				$option_field = "option".$i;
				$query = "select option_title,".$option_field." from fm_goods_option where goods_seq=? and $option_field != '' group by ".$option_field;
				$query = $this->db->query($query,array($goods_seq));
				foreach($query->result_array() as $data){
					$titles = explode(',',$data['option_title']);
					$data_goods['options'][$titles[$i-1]][] =  $data[$option_field];
				}
			}

			//이미지 상세/ list1
			$data_goods['list1img'] = viewImg($goods_seq,'list1','N');
			if(!preg_match('/http/', $data_goods['list1img'])){
				$data_goods['list1img'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['list1img']);
			}

			$data_goods['viewimg'] = viewImg($goods_seq,'view','N');
			if(!preg_match('/http/', $data_goods['viewimg'])){
				$data_goods['viewimg'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['viewimg']);
			}

			$result[] = $data_goods;
		}

		header("Content-Type: application/xml;charset=utf-8");
		echo ('<?xml version="1.0" encoding="utf-8"?>');
		$this->template->template_dir	= BASEPATH."../partner";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->assign('result',$result);
		$this->template->define(array('tpl'=>"navercheckout_item.html"));
		$this->template->print_("tpl");

	}

	/* 입점마케팅 전체 행 갯수 */
	function file_rows($filemode){
		$last_update_date = '';
		if($mode == 'summary'){
			$tmp = config_load('partner',$filemode.'_update');
			if($tmp[$filemode.'_update']) $last_update_date = $tmp[$filemode.'_update'];
		}
		$query = $this->goodsmodel->get_goods_all_partner_count($last_update_date,'view',true);
		$result = mysql_query($query);
		$data = mysql_fetch_array($result);
		$rows = $data['cnt'];

		return $rows;
	}

	/* 입점 마케팅 파일생성 */
	function file_write(){
		
		if($_GET['filemode']){ $filemode = $_GET['filemode']; }
		else { echo "잘못된 접근입니다."; exit; }

		if($_GET['rows'])	$rows = $_GET['rows'];
		else				$rows = $this->file_rows($filemode);
		$mode = $_GET['mode'];
		$page = ($_GET['page']) ? $_GET['page'] : 1;
		$pageline	= 3000;

		if($filemode == 'naver' && $mode == 'summary')
				$file_path	= ROOTPATH."/data/marketFile/".$filemode."_summary.txt";
		else	$file_path	= ROOTPATH."/data/marketFile/".$filemode.".txt";
		
		$dir_name	= dirname($file_path);
		if( !is_dir($dir_name) )	@mkdir($dir_name);
		@chmod($dir_name,0777);
		@chmod($file_path,0777);
		if($page==1)	unlink($file_path);

		if($filemode=='daum')		$npage	 = $this->daumFile($file_path,$mode,$page,$pageline);
		elseif($filemode=='about')	$npage	 = $this->aboutFile($file_path,$mode,$page,$pageline);
		elseif($filemode=='naver')	$npage	 = $this->naverFile($file_path,$mode,$page,$pageline);

		if($rows > ($page*$pageline))	redirect("./partner/file_write?page=".$npage."&filemode=".$filemode."&mode=".$mode."&rows=".$rows);

		$fileExt = file_exists($file_path);

		header("Content-Type: text/html; charset=UTF-8");
		if($fileExt) openDialogAlert("파일이 생성되었습니다.",400,140,'parent',$callback);
		//echo $filemode."File succ : " .$rows. "ea & page:".$npage;
	}
	/* 입점 마케팅 다음 파일 생성 */
	function daumFile($file_path, $mode='all',$page,$pageline){
		header("Content-Type: text/html; charset=EUC-KR");

		$fileExt = '';
		$last_update_date = '';
		if($mode == 'summary'){
			$tmp = config_load('partner','daum_update');
			if($tmp['daum_update']) $last_update_date = $tmp['daum_update'];
		}

		$fp = fopen($file_path,"a+");

		// 마케팅 전달 이미지
		$market_image	= config_load('marketing_image');
		if($market_image['daumImage']=='B' || !$market_image['daumImage']){
			$view_type	= "view";
		}else if($market_image['daumImage']=='C'){
			$view_type	= "large";
		}

		// 입점마케팅 상품 추가할인
		$marketing_sale = config_load('marketing_sale');
		$arr_marketing_sale = array();
		if (isset($marketing_sale)) {
			// 회원 등급별 할인 적용 [member_sale_type => 0 : 비회원 , 1 : 일반 등급 회원]
			if ($marketing_sale['member']=='Y' && $marketing_sale['member_sale_type']==1) $arr_marketing_sale['member'] = 'Y';

			// 할인 유입경로 적용
			if ($marketing_sale['referer']=='Y') 	{
				$arr_marketing_sale['referer'] = 'Y';
				$arr_marketing_sale['referer_url'] = 'shopping.daum.net';
			}

			// 할인 쿠폰 적용
			if ($marketing_sale['coupon']=='Y') $arr_marketing_sale['coupon'] = 'Y';

			// 모바일 할인 적용 : 쇼핑하우 미적용(쇼핑하우 전달용 모바일 할인 필드 없음)
			//if ($marketing_sale['mobile']=='Y') $arr_marketing_sale['mobile'] = 'Y';
		}

		### 입점마케팅 전달 데이터 통합 설정 - 입점마케팅 상품명,카드무이자할부 leewh 2015-01-29
		$marketing_feed = config_load('marketing_feed');

		$query = $this->goodsmodel->get_goods_all_partner($last_update_date,$view_type,true);
		$slimit = (($page-1)*$pageline);
		$query .= " limit ".$slimit.",".$pageline;
		$result = mysql_query($query);

		while ($data_goods = mysql_fetch_array($result)){ // 20130325
			if ($data_goods['feed_goods_use']=='Y' && !empty($data_goods['feed_goods_name']) 
				|| $data_goods['feed_goods_use']=='N' && !empty($marketing_feed['goods_name'])) {

				## 상품명 치환코드
				$replaceArr = array();
				$replaceArr['{product_name}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
				$replaceArr['{product_category}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['category_title']));
				$replaceArr['{product_brand}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['brand_title']));
				$replaceArr['{product_tag}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['keyword']));

				if ($data_goods['feed_goods_use']=='Y') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($data_goods['feed_goods_name'],$replaceArr);
				} else if($data_goods['feed_goods_use']=='N') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($marketing_feed['goods_name'],$replaceArr);
				}
			} else {
				$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			}
			$data_goods['brand']		= iconv('UTF-8', 'euc-kr',$data_goods['brand']);
			$data_goods['manufacture']	= iconv('UTF-8', 'euc-kr',$data_goods['manufacture']);
			$data_goods['orgin']		= iconv('UTF-8', 'euc-kr',$data_goods['orgin']);
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);

			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=daum';

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}


			// 카테고리
			$page_code ='';
			$page_name ='';
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){

					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  ($this->categorymodel->one_category_name($arr_category_code[$i]));

					$page_code .="<<<cate".($i+1).">>>".$data_goods['arr_category_code'][$i]."\n";
					$page_name .="<<<catename".($i+1).">>>".iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i])."\n";
				}
			}


			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$data_goods['deliv1'] = "0";
				$data_goods['deliv2'] = "";
				$data_goods['deliv2'] = iconv('UTF-8', 'euc-kr',"무료");
			}else{
			$data_goods['deliv1'] = "1";
			$data_goods['deliv2'] = "유료";
			$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
			if( $delivery['type'] == 'delivery' ){
				if( $delivery['free'] && $delivery['price'] ){
					$data_goods['deliv1'] = "2";
					$data_goods['deliv2'] = $delivery['free']."원 이상무료 or ".$delivery['price']."원";
				}else if( ! $delivery['price'] ){
					$data_goods['deliv1'] = "0";
					$data_goods['deliv2'] = "";
				}else{
					$data_goods['deliv1'] = "1";
					$data_goods['deliv2'] = $delivery['price'];
				}
			}
			$data_goods['deliv2'] = iconv('UTF-8', 'euc-kr',$data_goods['deliv2']);
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];
			$data_goods['shop_name'] = $this->config_basic['shopName'];
			$data_goods['shop_name'] = iconv('UTF-8', 'euc-kr',$data_goods['shop_name']);
			// 2014-01-16 이벤트 문구 미노출 추가
			if($data_goods['feed_evt_sdate'] == '0000-00-00') $data_goods['feed_evt_sdate'] = '';
			if($data_goods['feed_evt_edate'] == '0000-00-00') $data_goods['feed_evt_edate'] = '';
			$data_goods['event']	= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 상품가격 추가할인 설정
			if ($arr_marketing_sale) $data_goods['marketing_sale'] = $arr_marketing_sale;

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			if (!empty($marketing_feed['cfg_card_free'])) {
				$noint = trim(iconv('UTF-8', 'euc-kr',$marketing_feed['cfg_card_free']));
			} else {
				$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint));
			}

			unset($loop);
			$loop[] = "<<<begin>>>";
			$loop[] = "<<<pid>>>".$data_goods['goods_seq'];
			$loop[] = "<<<price>>>".$data_goods['price'];
			$loop[] = "<<<pname>>>".$data_goods['goods_name'];
			$loop[] = "<<<pgurl>>>".$data_goods['goods_url'];
			$loop[] = "<<<igurl>>>".$data_goods['image_url'];
			$loop[] = ${page_code}.${page_name}."<<<model>>>".iconv('UTF-8', 'euc-kr',$data_goods['model']);
			$loop[] = "<<<brand>>>".$data_goods['brand'];
			$loop[] = "<<<maker>>>".$data_goods['manufacture'];
			$loop[] = "<<<pdate>>>";
			//$loop[] = "<<<coupon>>>".$data_goods['coupon'];
			if ($data_goods['coupon_won'])$loop[] = "<<<coupon>>>".$data_goods['coupon_won'];
			if($noint)$loop[] = "<<<pcard>>>".$noint;
			$loop[] = "<<<point>>>".$data_goods['reserve'];
			$loop[] = "<<<deliv>>>".$data_goods['deliv1'];
			$loop[] = "<<<deliv2>>>".$data_goods['deliv2'];
			$loop[] = "<<<sellername>>>".$data_goods['shop_name'];
			$loop[] = "<<<event>>>".$data_goods['event'];

			$loop[] = "<<<end>>>";

			fwrite($fp,implode("\r\n",$loop)."\r\n");
		}
		fclose($fp);

		return $page+1;
	}
	/* 입점 마케팅 어바웃 파일 생성 */
	function aboutFile($file_path, $mode='all',$page,$pageline)
	{
		header("Content-Type: text/html; charset=EUC-KR");
		$fileExt = '';
		$last_update_date = '';
		if($mode == 'summary'){
			$tmp = config_load('partner','about_update');
			if($tmp['about_update']) $last_update_date = $tmp['about_update'];
		}

		$arr_status['normal'] = "C";
		$arr_status['runout'] = "D";
		$arr_status['unsold'] = "D";

		$fp = fopen($file_path,"a+");

		$query = $this->goodsmodel->get_goods_all_partner($last_update_date,'view',true);
		$slimit = (($page-1)*$pageline);
		$query .= " limit ".$slimit.",".$pageline;
		$result = mysql_query($query);

		while ($data_goods = mysql_fetch_array($result)){
			$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			$data_goods['brand']		= iconv('UTF-8', 'euc-kr',$data_goods['brand']);
			$data_goods['manufacture']	= iconv('UTF-8', 'euc-kr',$data_goods['manufacture']);
			$data_goods['orgin']		= iconv('UTF-8', 'euc-kr',$data_goods['orgin']);
			$data_goods['model']		= iconv('UTF-8', 'euc-kr',$data_goods['model']);
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);
			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=about';

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}

			// 카테고리
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){
					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  $this->categorymodel->one_category_name($arr_category_code[$i]);
					$data_goods['arr_category'][$i]	= iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i]);
				}
			}

			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
					$data_goods['delivery'] = "0";
			}else{
			$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
			if( $delivery['type'] == 'delivery' ){
				$data_goods['delivery'] = (int) $delivery['price'];
			}else{
				$data_goods['delivery'] = "-1";
			}
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];

			// 수정일
			if( $data_goods['update_date'] == '0000-00-00 00:00:00' ){
				$data_goods['update_date'] = $data_goods['regist_date'];
			}

			// 구분
			if( $data_goods['regist_date'] > $last_update_date ){
				$data_goods['class'] = "C";
			}
			if( $data_goods['update_date'] > $last_update_date ){
				$data_goods['class'] = $arr_status[$data_goods['goods_status']];
			}
			// 2014-01-16 이벤트 문구 미노출 추가
			if($data_goods['feed_evt_sdate'] == '0000-00-00') $data_goods['feed_evt_sdate'] = '';
			if($data_goods['feed_evt_edate'] == '0000-00-00') $data_goods['feed_evt_edate'] = '';
			$data_goods['event']	= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint_about));

			$r_item = array();
			$r_item[] = $data_goods['goods_seq'];
			$r_item[] = 'C';
			$r_item[] = $data_goods['goods_name'];
			$r_item[] = $data_goods['price'];
			$r_item[] = $data_goods['goods_url'];
			$r_item[] = $data_goods['image_url'];
			$r_item[] = $data_goods['arr_category_code'][0];
			$r_item[] = $data_goods['arr_category_code'][1];
			$r_item[] = $data_goods['arr_category_code'][2];
			$r_item[] = $data_goods['arr_category_code'][3];
			$r_item[] = $data_goods['arr_category'][0];
			$r_item[] = $data_goods['arr_category'][1];
			$r_item[] = $data_goods['arr_category'][2];
			$r_item[] = $data_goods['arr_category'][3];
			$r_item[] = $data_goods['model'];
			$r_item[] = $data_goods['brand'];
			$r_item[] = $data_goods['manufacture'];
			$r_item[] = $data_goods['orgin'];
			$r_item[] = '';
			$r_item[] = $data_goods['delivery'];
			$r_item[] = $data_goods['event'];
			$r_item[] = $data_goods['coupon'];
			$r_item[] = $noint;
			$r_item[] = $data_goods['reserve'];
			$r_item[] = '';
			$r_item[] = '';
			$r_item[] = '';
			$r_item[] = $data_goods['update_date'];

			fwrite($fp,implode('<!>',$r_item)."\r\n");
		}
		fclose($fp);

		config_save('partner',array('about_update'=>date('Y-m-d H:i:s')));
		return $page+1;
	}
	/* 입점 마케팅 네이버 파일 생성 */
	function naverFile($file_path, $mode='all',$page,$pageline)
	{
		header("Content-Type: text/html; charset=EUC-KR");

		$fileExt = '';
		$last_update_date = '';
		if($mode == 'summary'){
			$tmp = config_load('partner','naver_update');
			if($tmp['naver_update']) $last_update_date = $tmp['naver_update'];
		}

		$all_category = $this->categorymodel->get_all();
		foreach ($all_category as $row){
			$data['category_code'];
			$cate[$row['category_code']] = $row['title'];
		}


		$arr_status['normal'] = "U";
		$arr_status['runout'] = "D";
		$arr_status['unsold'] = "D";

		$fp = fopen($file_path,"a+");

		$market_image	= config_load('marketing_image');
		if($market_image['naverImage']=='B' || !$market_image['naverImage']){
			$view_type	= "view";
		}else if($market_image['naverImage']=='C'){
			$view_type	= "large";
		}

		// 입점마케팅 상품 추가할인
		$marketing_sale = config_load('marketing_sale');
		$arr_marketing_sale = array();
		if (isset($marketing_sale)) {
			// 회원 등급별 할인 적용 [member_sale_type => 0 : 비회원 , 1 : 일반 등급 회원]
			if ($marketing_sale['member']=='Y' && $marketing_sale['member_sale_type']==1) $arr_marketing_sale['member'] = 'Y';

			// 할인 유입경로 적용
			if ($marketing_sale['referer']=='Y') 	{
				$arr_marketing_sale['referer'] = 'Y';
				$arr_marketing_sale['referer_url'] = 'shopping.naver.com';
			}

			// 할인 쿠폰 적용
			if ($marketing_sale['coupon']=='Y') $arr_marketing_sale['coupon'] = 'Y';

			// 모바일 할인 적용 : 지식쇼핑 모바일가격 별도 계산해서 전달
			//if ($marketing_sale['mobile']=='Y') $arr_marketing_sale['mobile'] = 'Y';
		}

		### 입점마케팅 전달 데이터 통합 설정 - 입점마케팅 상품명,카드무이자할부 leewh 2015-01-29
		$marketing_feed = config_load('marketing_feed');

		$query	= $this->goodsmodel->get_goods_all_partner($last_update_date,$view_type,true); // 20130325
		$slimit = (($page-1)*$pageline);
		$query .= " limit ".$slimit.",".$pageline;
		$result = mysql_query($query);

		while ($data_goods = mysql_fetch_array($result)){ // 20130325
			if ($data_goods['feed_goods_use']=='Y' && !empty($data_goods['feed_goods_name']) 
				|| $data_goods['feed_goods_use']=='N' && !empty($marketing_feed['goods_name'])) {

				## 상품명 치환코드
				$replaceArr = array();
				$replaceArr['{product_name}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
				$replaceArr['{product_category}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['category_title']));
				$replaceArr['{product_brand}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['brand_title']));
				$replaceArr['{product_tag}'] = strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['keyword']));

				if ($data_goods['feed_goods_use']=='Y') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($data_goods['feed_goods_name'],$replaceArr);
				} else if($data_goods['feed_goods_use']=='N') {
					$data_goods['goods_name'] = $this->get_replace_goods_name($marketing_feed['goods_name'],$replaceArr);
				}
			} else {
				$data_goods['goods_name']	= strip_tags(iconv('UTF-8', 'euc-kr',$data_goods['goods_name']));
			}
			$data_goods['brand']		= iconv('UTF-8', 'euc-kr',$data_goods['brand']);
			$data_goods['manufacture']	= iconv('UTF-8', 'euc-kr',$data_goods['manufacture']);
			$data_goods['orgin']		= iconv('UTF-8', 'euc-kr',$data_goods['orgin']);
			$data_goods['feed_evt_text']	= iconv('UTF-8', 'euc-kr',$data_goods['feed_evt_text']);

			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=naver';
			$mourl = str_replace('www.','',$data_goods['goods_url']);
			$data_goods['mourl'] = str_replace('http://','http://m.',$mourl);

			if(preg_match('/http/', $data_goods['image'])){
				$data_goods['image_url'] = $data_goods['image'];
			}else{
				$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].iconv('UTF-8', 'euc-kr',$data_goods['image']);
			}

			// 카테고리
			$page_code ='';
			$page_name ='';
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){

					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  ($this->categorymodel->one_category_name($arr_category_code[$i]));

					$page_code .="<<<caid".($i+1).">>>".$data_goods['arr_category_code'][$i]."\n";
					$page_name .="<<<cate".($i+1).">>>".iconv('UTF-8', 'euc-kr',$data_goods['arr_category'][$i])."\n";
				}else{
					$page_code .="<<<caid".($i+1).">>>\n";
					$page_name .="<<<cate".($i+1).">>>\n";
				}
			}

			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$data_goods['delivery'] = "0";
			}else{
			$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
			if( $delivery['type'] == 'delivery' ){
				if( $delivery['free'] && $delivery['price'] ){
					if($delivery['free'] > $data_goods['price']){
						$data_goods['delivery'] = $delivery['price'];
					}else{
						$data_goods['delivery'] = "0";
					}
				}else{
					$data_goods['delivery'] = $delivery['price'];
				}
			}else{
				$data_goods['delivery'] = "-1";
			}
			}

			// 쿠폰
			//$coupon = $this -> _goods_coupon_max($data_goods['goods_seq']);
			//$data_goods['coupon'] = (int) $coupon['goods_sale'];

			// 구분
			if( $data_goods['regist_date'] > $last_update_date ){
				$data_goods['class'] = "I";
			}
			if( $data_goods['update_date'] > $last_update_date ){
				$data_goods['class'] = $arr_status[$data_goods['goods_status']];
			}
			// 2014-01-16 이벤트 문구 미노출 추가
			if($data_goods['feed_evt_sdate'] == '0000-00-00') $data_goods['feed_evt_sdate'] = '';
			if($data_goods['feed_evt_edate'] == '0000-00-00') $data_goods['feed_evt_edate'] = '';
			$data_goods['event']	= $data_goods['feed_evt_text'];
			if( time() < strtotime($data_goods['feed_evt_sdate'].' 00:00:00') || strtotime($data_goods['feed_evt_edate'].' 23:59:59') < time() ){
				$data_goods['event']	= '';
			}

			// 상품가격 추가할인 설정
			if ($arr_marketing_sale) $data_goods['marketing_sale'] = $arr_marketing_sale;

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			// 무이자
			if (!empty($marketing_feed['cfg_card_free'])) {
				$noint = trim(iconv('UTF-8', 'euc-kr',$marketing_feed['cfg_card_free']));
			} else {
				$noint = trim(iconv('UTF-8', 'euc-kr',$this->noint));
			}

			$systemmobiles = $this->configsalemodel->get_mobile_sale_for_goods($data_goods['price']);
			$mobile_price = 0;
			if($systemmobiles['sale_price'] && $data_goods['price']){
				$mobile_sale = $systemmobiles['sale_price'] * $data_goods['price'] / 100;
				$mobile_price = $data_goods['price'] - $mobile_sale;
				$mobile_price = $this->sale->cut_sale_price($mobile_price);
			}

			unset($loop);
			$loop[] = "<<<begin>>>";
			$loop[] = "<<<mapid>>>".$data_goods['goods_seq'];
			$loop[] = "<<<pname>>>".$data_goods['goods_name'];
			$loop[] = "<<<price>>>".$data_goods['price'];
			$loop[] = "<<<pgurl>>>".$data_goods['goods_url'];
			$loop[] = "<<<igurl>>>".$data_goods['image_url'];
			$loop[] = ${page_code}.${page_name}."<<<brand>>>".$data_goods['brand'];
			$loop[] = "<<<maker>>>".$data_goods['manufacture'];
			$loop[] = "<<<origi>>>".$data_goods['orgin'];
			$loop[] = "<<<deliv>>>".$data_goods['delivery'];
			//$loop[] = "<<<coupo>>>".$data_goods['coupo'];
			if ($data_goods['coupon_won'])$loop[] = "<<<coupo>>>".$data_goods['coupon_won'];
			if($noint)$loop[] = "<<<pcard>>>".$noint;
			$loop[] = "<<<point>>>".$data_goods['reserve'];
			if( $mode == 'summary' ){
				$loop[] = "<<<class>>>".$data_goods['class'];
				$loop[] = "<<<utime>>>".$data_goods['update_date'];
			}
			$loop[] = "<<<event>>>".$data_goods['event'];
			if($mobile_price) $loop[] = "<<<mpric>>>".$mobile_price;
			$loop[] = "<<<revct>>>".$data_goods['review_count'];
			$loop[] = "<<<mourl>>>".$data_goods['mourl'];
			$loop[] = "<<<ftend>>>";

			fwrite($fp,implode("\r\n",$loop)."\r\n");
		}
		fclose($fp);

		return $page+1;
	}

	public function _enuri_category()
	{
		$all_category = $this->categorymodel->get_list();
		foreach ($all_category as $row){
			$cate[$row['category_code']]['title'] = $row['title'];
			$all_category2 = $this->categorymodel->get_list($row['category_code']);
			foreach ($all_category2 as $row2){
				$cate[$row['category_code']]['sub'][$row2['category_code']]['title'] = $row2['title'];
			}
		}

		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" bgcolor=\"black\" width=\"91%\" align='center'>";
		echo "<tr bgcolor=\"#ededed\">";
		echo "<th width=60 align=center>대분류</th>";
		echo "<th>중분류</th>";
		echo "</tr>";
		foreach($cate as $code1 => $step1){
			$url = "http://" . $_SERVER['HTTP_HOST'] . "/partner/enuri/".$code1;
			echo "<tr bgcolor='white'>";
			echo "<td align=center><a href='".$url."'>".$step1['title']."</a></td>";
			echo "<td>";
			foreach($step1['sub'] as $code2 => $step2){
				$url = "http://" . $_SERVER['HTTP_HOST'] . "/partner/enuri/".$code2;
				echo "<a href='".$url."'>".$step2['title']."</a> |";
			}
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";

	}

	public function _enuri_goods($categorycode)
	{
		$cfg_order = config_load('order');

		$page = $_GET['page'];
		if(!$page) $page = 1;
		$query	= $this->goodsmodel->get_goods_all_partner('','view',true); // 20130325
		$query .= " and l.category_code like '".$categorycode."%'";

		// paging (페이지당출력수,현재페이지넘버,페이지숫자링크갯수,쿼리,인자)
		$result = select_page(1000,$page,10,$query);
		echo "<center>상품수 : ".$result['page']['totalcount']." 개</center>";
		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"1\" bgcolor=\"black\" width=\"600\" align='center'>";
		echo "<tr align=\"center\" bgcolor=\"EDEDED\">";
		echo "<td width=\"25\" height=\"24\" align=\"center\">번호</td>";
		echo "<td width=\"180\" height=\"24\" align=\"center\">제품명</td>";
		echo "<td width=\"40\" height=\"24\" align=\"center\">가격</td>";
		echo "<td width=\"35\" height=\"24\" align=\"center\">재고<br>유무</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">배송</td>";
		echo "<td width=\"90\" height=\"24\" align=\"center\">웹상품이미지</td>";
		echo "<td width=\"30\" height=\"24\" align=\"center\">할인<br>쿠폰 <br></td>";
		echo "<td width=\"30\" height=\"24\" align=\"center\">계산서</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">제조사</td>";
		echo "<td width=\"30\" height=\"24\" align=\"center\">상품코드</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">무이자<br>할부</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">카드할인가</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">모바일가격</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">차등배송비</td>";
		echo "<td width=\"50\" height=\"24\" align=\"center\">설치비</td>";
		echo "</tr>";
		foreach($result['record'] as $data){

			// 품절이면
			$stock_yn = "재고<br>있음";
			if($data['goods_status']!='normal') $stock_yn = "재고<br>없음";
			$tax = ($cfg_order['biztype']=='tax')? "Y" : "N";
			$coupon = $this -> _goods_coupon_max($data['goods_seq']);

			// 배송비
			if($data['goods_kind'] == "coupon"){
				$data['delivery'] = "무료배송";
			}else{
			$delivery = $this->goodsmodel->get_goods_delivery($data);
			if( $delivery['type'] == 'delivery' ){
				if( $delivery['free'] && $delivery['price'] ){
					if($delivery['free'] > $data_goods['price']){
						$data['delivery'] =  $delivery['free']."원 미만 ".$delivery['price']."원";
					}else{
						$data['delivery'] = 0;
					}
				}else{
					$data['delivery'] = $delivery['price'];
				}
			}
			if($data['delivery'] == 0) $data['delivery'] = "무료배송";

			}
			$url = "http://" . $_SERVER['HTTP_HOST'] . "/goods/view?no=".$data['goods_seq'];
			if(preg_match('/http/', $data['image'])){
				$image_url = $data['image'];
			}else{
				$image_url = 'http://'.$_SERVER['HTTP_HOST'].$data['image'];
			}
			
			// 할인가 적용
			$data['price'] = $this->apply_sale($data);

			echo "<tr align=\"center\" bgcolor=\"#FFFFFF\">";
			echo "<td height=\"24\">".$data['_no']."</td>";
			echo "<td height=\"24\" style=\"padding-top:3px;padding-bottom:3px\">";
			echo "<a href='".$url."' class=\"link_category1\">".$data['goods_name']."</a>";
			echo "</td>";
			echo "<td height=\"24\">".number_format($data['price'])."</td>";
			echo "<td height=\"24\">".$stock_yn."</td>";
			echo "<td height=\"24\">".$data['delivery']."</td>";
			echo "<td height=\"24\">".$image_url."</td>";
			echo "<td height=\"24\">".$coupon['percent_goods_sale']."</td>";
			echo "<td height=\"24\">".$tax."</td>";
			echo "<td height=\"24\">".$data['manufacture']."</td>";
			echo "<td height=\"24\">".$data['goods_code']."</td>";
			echo "<td height=\"24\">".$this->noint."</td>";
			echo "<td height=\"24\"></td>";
			echo "<td height=\"24\">".$data['price']."</td>";
			echo "<td height=\"24\">도서지역마다 배송비다름</td>";
			echo "<td height=\"24\"></td>";
			echo "</tr>";
		}
		echo "</table>";

		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"10\" bgcolor=\"white\" width=\"95%\" align='center'>";
		echo "<tr>";
		echo "<td align='center'>◀ ";
		foreach($result['page']['page'] as $data){
			echo "<a href='?page=".$data."'>".$data."</a>";
		}
		echo " ▶</td>";
		echo "</tr>";
		echo "</table>";

	}

	public function enuri()
	{

		if(!$this->uri->rsegments[3]) exit;
		if( $this->uri->rsegments[3] == 'category' ){
			$this->_enuri_category();
		}else{
			$this->_enuri_goods( $this->uri->rsegments[3] );
		}

	}

	public function styletag()
	{
		$now = date("Y-m-d H:i:s");
		//header("Content-type: text/xml;charset=utf-8");

		$cfg_order = config_load('order');
		$tmp = config_load('partner_styletag');
		if($tmp['styletag_update']) $last_update_date = $tmp['styletag_update'];

		$prdcard = str_replace('개월','',$this->noint);

		echo "<?xmlversion=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<response>\n";
		echo "<createtime>".date("Y-m-d H:i:s")."</createtime>\n";
		echo "<products>\n";

		$query = $this->goodsmodel->get_goods_all_partner('','view',true); // 20130325
		$result = mysql_query($query);
		while ($data_goods = mysql_fetch_array($result)){

			$options = $this->goodsmodel->get_goods_option($data_goods['goods_seq']);
			$data_goods['tot_stock'] = 0;
			$r_option = array();
			$r_option_stock = array();
			$r_option_price = array();
			if($options)foreach($options as $k => $opt){
				/* 대표가격 */
				if($opt['default_option'] == 'y'){
					$data_goods['price'] = $opt['price'];
				}

				if($cfg_order['runout'] == 'ableStock'){
					$reservation_field = 'reservation25';
					if($cfg_order['ableStockStep'] == 15) $reservation_field = 'reservation15';
					$option_stock = $opt['stock'] - $opt[$reservation_field];
					$data_goods['tot_stock'] += $option_stock;
				}else{
					$option_stock = $opt['stock'];
					$data_goods['tot_stock'] += $option_stock;
				}

				$arr_option = array();
				if($opt['option1']) $arr_option[] = $opt['option1'];
				if($opt['option2']) $arr_option[] = $opt['option2'];
				if($opt['option3']) $arr_option[] = $opt['option3'];
				if($opt['option4']) $arr_option[] = $opt['option4'];
				if($opt['option5']) $arr_option[] = $opt['option5'];

				$r_option[] = implode(',',$arr_option);
				$r_option_stock[] = $option_stock;
				$r_option_price[] = $opt['price'];
			}

			$str_option = implode('^',$r_option);
			$str_option_stock = implode('^',$r_option_stock);
			$str_option_price = implode('^',$r_option_price);

			if( $data_goods['regist_date'] >= $last_update_date ) $prdcud = "C";
			else $prdcud = "U";

			// 품절이면
			if($data_goods['goods_status']!='normal') $prdcud = "D";

			$data_goods['mobile_goods_url'] = 'http://m.'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=about';
			$data_goods['goods_url'] = 'http://'.$_SERVER['HTTP_HOST']."/goods/view?no=".$data_goods['goods_seq'].'&market=about';
			$data_goods['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].$data_goods['image'];

			// 할인가 적용
			$data_goods['price'] = $this->apply_sale($data_goods);

			$xml = array();
			$xml[] = "<product>";
			$xml[] = "<prdcud>".$prdcud."</prdcud>";
			$xml[] = "<prdmallcode>".$tmp['prdmallcode']."</prdmallcode>";
			$xml[] = "<prdcode>".$data_goods['goods_seq']."</Prdcode>";
			$xml[] = "<prdname><![CDATA[".$data_goods['goods_name']."]]></prdname>";

			$xml[] = "<prdprice>".$data_goods['price']."</prdprice>";
			$xml[] = "<prdmprice>".$data_goods['price']."</prdmprice>";
			$xml[] = "<prdsaleprice>".$data_goods['price']."</prdsaleprice>";

			$xml[] = "<prdurl><![CDATA[".$data_goods['goods_url']."]]></prdurl>";
			$xml[] = "<prdmurl><![CDATA[".$data_goods['mobile_goods_url']."]]></prdmurl>";

			$xml[] = "<prdimgurl><![CDATA[".$data_goods['image_url']."]]></prdimgurl>";
			$xml[] = "<prdmimgurl><![CDATA[".$data_goods['image_url']."]]></prdmimgurl>";

			// 카테고리
			$page_code ='';
			$page_name ='';
			$arr_category_code = array();
			$arr_category_code = $this->categorymodel->split_category($data_goods['category_code']);
			for($i=0;$i<4;$i++) {
				$data_goods['arr_category_code'][$i] = "";
				$data_goods['arr_category'][$i] = "";
				if( $arr_category_code[$i] ){
					$data_goods['arr_category_code'][$i] = $arr_category_code[$i];
					$data_goods['arr_category'][$i] =  ($this->categorymodel->one_category_name($arr_category_code[$i]));
				}
			}
			for($i=0;$i<4;$i++){
				if( $data_goods['arr_category'][$i] ) $xml[] = "<prdcate".($i+1)."><![CDATA[".$data_goods['arr_category'][$i]."]]></prdcate".($i+1).">";
				else $xml[] = "<prdcate".($i+1)."></prdcate".($i+1).">";
			}

			for($i=0;$i<4;$i++){
				if($data_goods['arr_category_code'][$i]) $xml[] = "<prdcid".($i+1)."><![CDATA[".$data_goods['arr_category_code'][$i]."]]></prdcid".($i+1).">";
				else $xml[] = "<prdcid".($i+1)."></prdcid".($i+1).">";
			}

			$prdtax = ($data_goods['tax']=='tax')?"Y":"N";
			$prddisp = ($data_goods['goods_view']=='look')?"Y":"N";
			$prdstoc = ($data_goods['goods_status']=='normal')?"Y":"N";

			// 배송비
			if($data_goods['goods_kind'] == "coupon"){
				$prddelivery = 0;
			}else{
			$delivery = $this->goodsmodel->get_goods_delivery($data_goods);
			if( $delivery['type'] == 'delivery' ){
				if( ! $delivery['price'] || $delivery['free'] <= $data_goods['price'] ){
					$prddelivery = 0;
				}else if( $delivery['price'] ){
					$prddelivery = $delivery['price'];
				}
				}
			}


			if( $data_goods['brand'] )$xml[] = "<prdbrand><![CDATA[".$data_goods['brand']."]]></prdbrand>";
			else $xml[] = "<prdbrand></prdbrand>";

			$xml[] = "<prdmaker>".$data_goods['manufacture']."</prdmaker>";

			if( $data_goods['orgin'] ) $xml[] = "<prdsupply><![CDATA[".$data_goods['orgin']."]]></prdsupply>";
			else $xml[] = "<prdsupply></prdsupply>";

			$xml[] = "<prdcreate>".$data_goods['regist_date']."</prdcreate>";
			$xml[] = "<prdupdate>".$data_goods['update_date']."</prdupdate>";
			$xml[] = "<prddelivery>".$prddelivery."</prddelivery>";

			if( $prdcard ) $xml[] = "<prdcard><![CDATA[".$prdcard."]]></prdcard>";

			$xml[] = "<prdpoint>".$data_goods['reserve']."</prdpoint>";
			$xml[] = "<prdtax>".$prdtax."</prdtax>";

			if( $data_goods['model'] ) $xml[] = "<prdmodel><![CDATA[".$data_goods['model']."]]></prdmodel>";
			else $xml[] = "<prdmodel></prdmodel>";

			$xml[] = "<prddisp>".$prddisp."</prddisp>";
			$xml[] = "<prdstoc>".$prdstoc."</prdstoc>";
			$xml[] = "<prdstocknum>".$data_goods['tot_stock']."</prdstocknum>";

			if( $data_goods['summary'] ) $xml[] = "<prddec><![CDATA[".strip_tags($data_goods['summary'])."]]></prddec>";
			else $xml[] = "<prddec></prddec>";

			if( $str_option ) $xml[] = "<prdopt><![CDATA[".$str_option."]]></prdopt>";
			else  $xml[] = "<prdopt></prdopt>";

			if($str_option_price) $xml[] = "<prdoptprice><![CDATA[".$str_option_price."]]></prdoptprice>";
			else $xml[] = "<prdoptprice></prdoptprice>";

			if($str_option_stock) $xml[] = "<prdoptstock><![CDATA[".$str_option_stock."]]></prdoptstock>";
			else $xml[] = "<prdoptstock></prdoptstock>";

			$xml[] = "<prdgender>unisex</prdgender>";
			$xml[] = "<prdtag><![CDATA[".str_replace(',','^',$data_goods['keyword'])."]]></prdtag>";
			$xml[] = "</product>";
			foreach($xml as $tag){
				echo $tag."\n";
			}
		}

		echo "</products>\n";
		echo "</response>";

		config_save('partner_styletag',array('styletag_update'=>$now));
		//config_save('partner_styletag',array('styletag_update'=>''));
		//config_save('partner_styletag',array('prdmallcode'=>'firstmall_1'));
	}

	// 샵링커
	public function shoplinker(){
		$this->load->model('openmarket/shoplinkermodel','shoplinker');
		$this->shoplinker->print_xml();
	}

	## 전송 결과 저장
	public function setLinkageResult(){
		$params		= unserialize(base64_decode($_POST['param']));
		$goodsSeq	= $params['goodsSeq'];
		$result		= $params['result'];
		$resMsg		= $params['resMsg'];

		if	($result)
			$upParam['suc_send_date']	= date('Y-m-d H:i:s');
		$upParam['lst_send_status']		= ($result) ? 'Y' : 'N';
		$upParam['lst_send_msg']		= addslashes($resMsg);
		$upParam['lst_send_date']		= date('Y-m-d H:i:s');
		if	(is_array($goodsSeq) && count($goodsSeq) > 0){
			foreach($goodsSeq as $g => $seq){
				if	($seq){
					$this->db->where(array('goods_seq'=>$seq));
					$this->db->update('fm_goods', $upParam);
				}
			}
		}
	}

	## 상품명 치환코드
	public function get_replace_goods_name($goods_name, $replaceArr) {
		$goods_name = strip_tags(iconv('UTF-8', 'euc-kr',$goods_name));
		foreach ($replaceArr as $key => $val){
			$patterns[]		= "/".$key."/";
			$replacements[]	= $val;
		}
		$gname	= preg_replace($patterns, $replacements, $goods_name);
		return $gname;
	}
}

/* End of file partner.php */
/* Location: ./app/controllers/partner.php */