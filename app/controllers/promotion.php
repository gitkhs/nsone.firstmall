<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class promotion extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('membermodel');
	}
	
	/** 프로모션 > 쿠폰
	** @ 방식1 : 페이지
	** @ 배송비 쿠폰/신규가입 쿠폰/신규가입 쿠폰 (배송비)/컴백회원 쿠폰/컴백회원 쿠폰 (배송비)/이달의 등급 쿠폰/이달의 등급 쿠폰 (배송비)/첫 구매 쿠폰
	** @ 방식2 : 새창
	* 생일자/기념일/회원 등급 조정 쿠폰/회원 등급 조정 쿠폰 (배송비)
	* /promotion/coupon?type=구분자
	**/
	public function coupon()
	{
		$this->_getcoupon();
	}  

	//프로모션 > 쿠폰 : 기념일쿠폰 다운페이지1
	public function coupon_anniversary()
	{
		$_GET['type'] = 'anniversary';
		$this->_getcoupon();
	} 	
	//프로모션 > 쿠폰 : 생일쿠폰 다운페이지2
	public function coupon_birthday()
	{
		$_GET['type'] = 'birthday';
		$this->_getcoupon();
	} 
	//프로모션 > 쿠폰 : 신규가입쿠폰 다운3
	public function coupon_member()
	{
		$_GET['type'] = 'member';
		$this->_getcoupon();
	} 	
	//프로모션 > 쿠폰 : 회원등급쿠폰 다운페이지4
	public function coupon_membergroup()
	{
		$_GET['type'] = 'membergroup';
		$this->_getcoupon();
	} 
	//프로모션 > 쿠폰 : 컴백회원쿠폰 다운페이지5
	public function coupon_memberlogin()
	{
		$_GET['type'] = 'memberlogin';
		$this->_getcoupon();
	}
	//프로모션 > 쿠폰 : 이달의 등급쿠폰 다운페이지6
	public function coupon_membermonths()
	{
		$_GET['type'] = 'membermonths';
		$this->_getcoupon();
	} 
	// 프로모션 > 쿠폰 : 첫구매쿠폰 다운페이지7
	public function coupon_order()
	{
		$_GET['type'] = 'order';
		$this->_getcoupon();
	} 
	//배송비쿠폰 다운페이지8
	public function coupon_shipping()
	{
		$_GET['type'] = 'shipping';
		$this->_getcoupon();
	} 

	/** 프로모션 > 쿠폰
	** @ 
	**/
	function _getcoupon()
	{
		$this->load->model('couponmodel');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->helper('coupon'); 

		$type = ($_GET['type'] == 'membergroup' ) ? "memberGroup":$_GET['type'];
		if( !in_array($type, $this->couponmodel->coupontotaltype) ) {//이벤트페이지에서 다운가능쿠폰(포인트/직접발급 등 불가)
			$msg = "잘못된 접근입니다."; 
			alert($msg);
			$url = "/main/";
			if( $this->fammerceMode  || $this->storefammerceMode ) {
				pageRedirect($url,'','self');
			}else{
				pageRedirect($url,'','parent');
			}
			exit;
		}
		$tpl = 'promotion/coupon_'.$_GET['type'].'.html'; 
		
		if( $_GET['layer'] ) {
			if($this->designMode) {
				$this->template->compile_dir	= BASEPATH."../_compile/design";
				$this->template->prefilter		= "addImageAttributesBefore | ".$this->template->prefilter." | addImageAttributes";
			}
			$this->template->define(array('LAYOUT'=>$this->skin."/".$tpl)); 
			$this->template->print_('LAYOUT');
		}else{
			$this->template->assign(array("template_path"=>$tpl));
			$this->print_layout($this->skin.'/'.$tpl);
		} 
	}

	/** 프로모션 > 코드
	** @ 회원 > 포인트교환 > 프로모션코드 신청하기
	**/
	public function download_member()
	{
		$this->load->model('promotionmodel');
		$this->load->helper('order');
		$this->load->helper('shipping');
		$promotionSeq = (int) $_POST['promotion_seq'];
		if(empty($_POST['promotion_seq'])){
			$result = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
			echo json_encode($result);
			exit;
		}

		if(empty($this->userInfo['member_seq'])){
			$result = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
			echo json_encode($result);
			exit;
		}

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);
		$memberSeq = $this->userInfo['member_seq'];
		$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보

		// 로그인 체크
		if(!isset( $_GET['return_url'])) $_GET['return_url'] = "/main/index";
		$_SERVER["REQUEST_URI"] = $_GET['return_url'];
		login_check();

		// 프로모션코드 정보 확인
		$promotioncode = $this->promotionmodel->get_admin_download($memberSeq, $promotionSeq);
		if($promotioncode) {
			//$result = array('result'=>false, 'msg'=>"이미 신청한 프로모션코드 입니다.");
			//echo json_encode($result);
			//exit;
		}

		$promotionData 	= $this->promotionmodel->get_promotion($promotionSeq);
		if(!$promotionData) $result = array('result'=>false, 'msg'=>"프로모션코드신청이 실패하였습니다.");
		if($promotionData['type'] == 'point' || $promotionData['type'] == 'point_shipping' ) {//point 전환조건체크
			$this->load->model('membermodel');
			if( $this->mdata['point']<1 || $this->mdata['point'] < $promotionData['promotion_point'] ) {//포인트가 작거나 없는 경우
				if( $this->mdata['point']<1 ) {//포인트가 작거나 없는 경우
					$result = array('result'=>false, 'msg'=>" 보유포인트가 없습니다.");
					echo json_encode($result);
					exit;
				}else{
					$result = array('result'=>false, 'msg'=>"전환포인트 금액이 보유포인트보다 작습니다.");
					echo json_encode($result);
					exit;
				}
			}
		}

		if( $promotionData['promotion_type'] == 'random') {//자동생성 >  -> 발급시자동생성 4-4-4-4
			$paramoffline["code_serialnumber"]		= strtoupper(substr(md5( uniqid('') ), 0, 4)).'-'.strtoupper(substr(md5( uniqid('') ), 0, 4)).'-'.strtoupper(substr(md5( uniqid('') ), 0, 4)).'-'.strtoupper(substr(md5( uniqid('') ), 0, 4));//영문+숫자
		}elseif( $promotionData['promotion_type'] == 'file' ) {//수동생성2 > 파일
			$inputsc['whereis'] = ' and down_use = 0 ';
			$promotioninput = $this->promotionmodel->get_promotioncode_input_item($promotionSeq, $inputsc);
			if($promotioninput['code_serialnumber']){
				$paramoffline["code_serialnumber"] = $promotioninput['code_serialnumber'];
			}
		}
		if($paramoffline["code_serialnumber"]) {
			$return = $this->promotionmodel->_members_point_downlod($promotionSeq, $memberSeq, $paramoffline["code_serialnumber"]);
			if( $return ) {
				if( $promotionData['promotion_type'] == 'random') {//자동생성 > 발급시자동생성 4-4-4-4
					$paramoffline["use_count"]				= 1;
					$paramoffline["code_number"]		= mt_rand();//생성
					$paramoffline["promotion_seq"]		= $promotionSeq;
					$paramoffline["regist_date"]			= date("Y-m-d H:i:s");
					$this->db->insert('fm_promotion_code', $paramoffline);
				}elseif( $promotionData['promotion_type'] == 'file' ) {//수동생성2 > 파일
					$this->promotionmodel->set_promotioncode_down_use($paramoffline["code_serialnumber"]);
				}

				if( $this->mdata['email'] ) {
					/**
					$emailparams['email']= $this->mdata['email'];
					$emailparams['title']	= '프로모션코드가 발급되었습니다.';
					$data['contents']	= '프로모션코드가 발급되었습니다. <br/>프로모션코드 : '.$paramoffline["code_serialnumber"].'';
					getSendMail($data);
					**/
					if($promotionData["sale_type"] == 'shipping_free'){
						$promotionsale = "기본배송비 무료 (최대 " .number_format($promotionData["max_percent_shipping_sale"])."원)";
					}else if($promotionData["sale_type"] =='shipping_won'){
						$promotionsale = number_format($promotionData["won_shipping_sale"]);
					}else if($promotionData["sale_type"] =='won'){
						$promotionsale = number_format($promotionData["won_goods_sale"])."원 할인";
					}else{
						$promotionsale = number_format($promotionData["percent_goods_sale"])."% 할인";
					}
					if ($promotionData['issue_priod_type'] == 'day') {
						$promotionlimitdate = ($promotionData['after_issue_day']>0) ? '다운로드 후 '.$promotionData['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
					}else{
						$promotionlimitdate = substr($promotionData['issue_enddate'], 5,2).'월 '. substr($promotionData['issue_enddate'],8,2).'일 까지 사용가능';
					}

					$emailparams['promotioncode']		= $paramoffline["code_serialnumber"];
					$emailparams['promotionsale']		= $promotionsale;
					$emailparams['promotionlimitdate']	= $promotionlimitdate;

					if($this->mdata['rute']!='none'){
						$this->load->helper('email');
						if (valid_email($this->mdata['email']))
						{
							sendMail($this->mdata['email'], 'promotion', $this->mdata['userid'] , $emailparams);
						}
						elseif (valid_email($this->mdata['userid']))
						{
							sendMail($this->mdata['userid'], 'promotion', $this->mdata['userid'] , $emailparams);
						}
					}else{
						sendMail($this->mdata['email'], 'promotion', $this->mdata['userid'] , $emailparams);
					}
				}

				$result = array('result'=>true, 'msg'=>"프로모션코드 신청되었습니다.<br/>포인트현황 또는 이메일로 프로모션코드를 확인하실 수 있습니다.");
			}else{
				$result = array('result'=>false, 'msg'=>"프로모션코드 신청이 실패되었습니다.");
			}
		}else{
			$result = array('result'=>false, 'msg'=>"프로모션코드 발급이 실패되었습니다.");
		}
		echo json_encode($result);
		exit;
	}

	/** 프로모션 > 코드
	** @ 인증하기
	**/
	public function getPromotionJson()
	{
		$this->load->model('promotionmodel');
		$this->load->helper('order');
		$this->load->helper('shipping');
		$_GET['mode'] = ($_POST['mode'])?$_POST['mode']:$_GET['mode'];
		$session_id = $this->session->userdata('session_id');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('cartmodel');
		$this->load->model('brandmodel');



		//초기화
		$this->session->unset_userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
		$this->session->unset_userdata('cart_promotioncode_'.$this->session->userdata('session_id'));


		$cartpromotioncode = $_POST['cartpromotioncode'];
		if(empty($_POST['cartpromotioncode'])){
			$result = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
			echo json_encode($result);
			exit;
		}

		if( !empty($this->userInfo['member_seq']) ){
			$memberSeq = $this->userInfo['member_seq'];
			$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보
		}

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		//$cart_promotioncode = $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
		if( $cart_promotioncode == $cartpromotioncode){
			$result = array('result'=>false, 'msg'=>"이미 인증된 코드입니다.");
			echo json_encode($result);
			exit;
		}

		$promotioncodeData 	= $this->promotionmodel->get_promotioncode_serialnumber($cartpromotioncode);
		if( empty($promotioncodeData) ) {
			$promotioncodeData 	= $this->promotionmodel->get_promotioncode_input_serialnumber($cartpromotioncode);
		}

		if($promotioncodeData) {//프로모션코드 인증1
			$sc['whereis'] = " and promotion_input_serialnumber ='".$cartpromotioncode."'";
			$promotioncode = $this->promotionmodel->get_data($sc);
			$promotioncode = $promotioncode[0];

			if( strstr($promotioncode['type'],'promotion') ){//일반코드인경우

				if ($promotioncode['issue_priod_type'] == 'day') {
					$promotioncode['issue_enddatetitle'] = ($promotioncode['after_issue_day']>0) ? '다운로드 후 '.$promotioncode['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
				}else{
					$promotioncode['issue_enddatetitle'] = substr($promotioncode['issue_enddate'], 5,2).'월 '. substr($promotioncode['issue_enddate'],8,2).'일 까지 사용가능';
				}

				if($promotioncode['issue_type'] == 'all' ){
					$promotioncode['categoryhtml'] = '전체 사용 가능';
				}else{

					$issuegoods 		= $this->promotionmodel->get_promotion_issuegoods($promotioncode['promotion_seq']);
					$issuebrand 		= $this->promotionmodel->get_promotion_issuebrand($promotioncode['promotion_seq']);
					$issuecategorys	= $this->promotionmodel->get_promotion_issuecategory($promotioncode['promotion_seq']);

					if(($issuegoods)){
						foreach($issuegoods as $key => $tmp) $arrGoodsSeq[] =  $tmp['goods_seq'];
						$goods = $this->goodsmodel->get_goods_list($arrGoodsSeq,'thumbView');
						foreach($issuegoods as $key => $data) {
							$issuegoodsar[$key] = array_merge($issuegoods[$key],$goods[$data['goods_seq']]);
							$issuegoods[$key] = $issuegoodsar[$key]['goods_name'];
							$issuegoodscodes[$key] = $issuegoodsar[$key]['goods_seq'];
						}
						$promotioncode['goodshtml'] = implode(", ",$issuegoods);
						$promotioncode['goodshtmlcode'] = implode(",",$issuegoodscodes);
					}

					if(($issuebrand)) {
						foreach($issuebrand as $key =>$data) {
							$issuebrand[$key] = $this->brandmodel -> get_brand_name($data['brand_code']);
							$issuebrandcodes[$key] = $data['brand_code'];
						}
						$promotioncode['brandhtml'] = implode(", ",$issuebrand);
						$promotioncode['brandhtmlcode'] = implode(",",$issuebrandcodes);
					}

					if($issuecategorys){
						foreach($issuecategorys as $key =>$data) {
							$issuecategorys[$key] = $this->categorymodel -> get_category_name($data['category_code']);
							$issuecategorycodes[$key] = $data['category_code'];
						}
						$promotioncode['categoryhtml'] = implode(", ",$issuecategorys);
						$promotioncode['categoryhtmlcode'] = implode(",",$issuecategorycodes);
					}
				}

				if( $promotioncode['downloadLimit_member'] == 1 &&  empty($this->userInfo['member_seq']) ) {//회원여부 (포인트전환은 회원전용) 인증3
					//$result = array('result'=>false, 'msg'=>"해당 프로모션코드는 회원전용 프로모션코드입니다.\n로그인 후 이용해 주세요.");
					$promotioncode['result'] = false;
					$promotioncode['msg'] = "해당 프로모션코드는 회원전용 프로모션코드입니다.<br/>로그인 후 이용해 주세요.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}

				//유효기간체크인증4
				if( !($promotioncode['issue_startdate']<=$today && $promotioncode['issue_enddate']>=$today) ) {
					//$result = array('result'=>false, 'msg'=>"해당 프로모션코드 유효기간이 아닙니다.");
					$promotioncode['result'] = false;
					$promotioncode['msg'] = "해당 프로모션코드 유효기간이 아닙니다.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}

			}else{//개별코드인경우 -> 발급후 이용가능

				//발급 또는 구매 프로모션코드 인증2
				$promotioncode_down = $this->promotionmodel->get_download_serialnumber($promotioncodeData['promotion_seq'], $cartpromotioncode);
				if(!$promotioncode_down){
					//$result = array('result'=>false, 'msg'=>"해당 프로모션코드는 발급후 이용가능한 프로모션코드입니다.");
					$promotioncode_down['result'] = false;
					$promotioncode_down['msg'] = "해당 프로모션코드는 발급후 이용가능한 프로모션코드입니다.";
					$result = $promotioncode_down;
					echo json_encode($result);
					exit;
				}
				$promotioncode = $promotioncode_down;


				if ($promotioncode['issue_priod_type'] == 'day') {
					$promotioncode['issue_enddatetitle'] = ($promotioncode['after_issue_day']>0) ? '다운로드 후 '.$promotioncode['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
				}else{
					$promotioncode['issue_enddatetitle'] = substr($promotioncode['issue_enddate'], 5,2).'월 '. substr($promotioncode['issue_enddate'],8,2).'일 까지 사용가능';
				}

				if($promotioncode['issue_type'] == 'all' ){
					$promotioncode['categoryhtml'] = '전체 사용 가능';
				}else{

					$issuegoods 		= $this->promotionmodel->get_promotion_download_issuegoods($promotioncode['download_seq']);
					$issuebrand 		= $this->promotionmodel->get_promotion_download_issuebrand($promotioncode['download_seq']);
					$issuecategorys	= $this->promotionmodel->get_promotion_download_issuecategory($promotioncode['download_seq']);

					if(($issuegoods)){
						foreach($issuegoods as $key => $tmp) $arrGoodsSeq[] =  $tmp['goods_seq'];
						$goods = $this->goodsmodel->get_goods_list($arrGoodsSeq,'thumbView');
						foreach($issuegoods as $key => $data) {
							$issuegoodsar[$key] = array_merge($issuegoods[$key],$goods[$data['goods_seq']]);
							$issuegoods[$key] = $issuegoodsar[$key]['goods_name'];
							$issuegoodscodes[$key] = $issuegoodsar[$key]['goods_seq'];
						}
						$promotioncode['goodshtml'] = implode(", ",$issuegoods);
						$promotioncode['goodshtmlcode'] = implode(",",$issuegoodscodes);
					}

					if(($issuebrand)) {
						foreach($issuebrand as $key =>$data) {
							$issuebrand[$key] = $this->brandmodel -> get_brand_name($data['brand_code']);
							$issuebrandcodes[$key] = $data['brand_code'];
						}
						$promotioncode['brandhtml'] = implode(", ",$issuebrand);
						$promotioncode['brandhtmlcode'] = implode(",",$issuebrandcodes);
					}

					if($issuecategorys){
						foreach($issuecategorys as $key =>$data) {
							$issuecategorys[$key] = $this->categorymodel -> get_category_name($data['category_code']);
							$issuecategorycodes[$key] = $data['category_code'];
						}
						$promotioncode['categoryhtml'] = implode(", ",$issuecategorys);
						$promotioncode['categoryhtmlcode'] = implode(",",$issuecategorycodes);
					}
				}


				//1회성코드 인증7
				if($promotioncodeData['use_count'] == 0){
					//$result = array('result'=>false, 'msg'=>"해당 프로모션코드는 이미 사용한 코드입니다.");

					$promotioncode['result'] = false;
					$promotioncode['msg'] = "해당 프로모션코드는 이미 사용한 코드입니다.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}

				//회원여부 (포인트전환은 회원전용) 인증3
				if( $promotioncode['downloadLimit_member'] == 1 &&  empty($this->userInfo['member_seq']) ) {
					//$result = array('result'=>false, 'msg'=>"해당 프로모션코드는 회원전용 프로모션코드입니다.\n로그인 후 이용해 주세요.");

					$promotioncode['result'] = false;
					$promotioncode['msg'] = "해당 프로모션코드는 회원전용 프로모션코드입니다.<br/>로그인 후 이용해 주세요.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}

				//유효기간체크인증4
				if( !($promotioncode['issue_startdate']<=$today && $promotioncode['issue_enddate']>=$today) ) {
					//$result = array('result'=>false, 'msg'=>"해당 프로모션코드 유효기간이 아닙니다.");

					$promotioncode['result'] = false;
					$promotioncode['msg'] = "해당 프로모션코드 유효기간이 아닙니다.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}
			}
			//사용제한-상품/카테고리/브랜드 인증5
			$cart = $this->cartmodel->cart_list();
			foreach($cart['list'] as $key => $data){
				$category = array();
				$tmp = $this->goodsmodel->get_goods_category($data['goods_seq']);
				foreach($tmp as $row) $category[] = $row['category_code'];
				$cart_sale = (int) $data['tot_price'];
				$sum_goods_price += (int) $data['tot_price'];


				$cart_options = $data['cart_options'];
				$cart_suboptions = $data['cart_suboptions'];
				$cart_inputs = $data['cart_inputs'];

				$coupon_goods_sale = 0;
				$member_sale = 0;
				$reserve = 0;
				foreach($cart_options as $k => $cart_option){
					list($price,$cart_option['reserve']) = $this->goodsmodel->get_goods_option_price(
						$data['goods_seq'],
						$cart_option['option1'],
						$cart_option['option2'],
						$cart_option['option3'],
						$cart_option['option4'],
						$cart_option['option5']
					);

					// 재고 체크
					$chk = check_stock_option(
						$data['goods_seq'],
						$cart_option['option1'],
						$cart_option['option2'],
						$cart_option['option3'],
						$cart_option['option4'],
						$cart_option['option5'],
						$cart_option['ea'],
						$cfg['order']
					);
				}
				if($cart_suboptions){
					foreach($cart_suboptions as $k => $cart_suboption){
						// 재고 체크
						$chk = check_stock_suboption(
							$data['goods_seq'],
							$cart_suboption['suboption_title'],
							$cart_suboption['suboption'],
							$cart_suboption['ea'],
							$cfg['order']
						);

						if( $members ){
							/* 회원할인계산 */
							$cart_option['member_sale'] = $this->membermodel->get_member_group($members['group_seq'],$data['goods_seq'],$category,$cart_option['price'],$cart['total']);
							$member_sale += $cart_option['member_sale'] * $cart_option['ea'];
							$cart_options[$k] = $cart_option;
						}
					}
				}

				$cart_sale -= $member_sale;

				$cart['list'][$key]['options'] = $cart_options;
				$cart['list'][$key]['suboptions'] = $cart_suboptions;
				$cart['list'][$key]['inputs'] = $cart_inputs;

				if( $data['shipping_weight_policy'] == "shop" ){
					$goods_weight += $international_shipping['defaultGoodsWeight'];
				}else{
					$goods_weight += $data['goods_weight'];
				}

				/* 상품 가격 합계  */
				$total_goods_price += (int) $cart_sale;
			}

			if( strstr($promotioncode['type'],'promotion') ){//일반코드인경우
				//사용제한금액1
				if( ($promotioncode['limit_goods_price']>$sum_goods_price) ) {
					//$result = array('result'=>false, 'msg'=>"총구매금액이 ".number_format($promotioncode['limit_goods_price'])."원이상만 사용가능합니다.");

					$promotioncode['result'] = false;
					$promotioncode['msg'] = "총구매금액이 ".number_format($promotioncode['limit_goods_price'])."원이상만 사용가능합니다.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}

			}else{//개별코드
				//사용제한금액1
				if( ($promotioncode['limit_goods_price']>$sum_goods_price) ) {
					//$result = array('result'=>false, 'msg'=>"총구매금액이 ".number_format($promotioncode['limit_goods_price'])."원이상만 사용가능합니다.");

					$promotioncode['result'] = false;
					$promotioncode['msg'] = "총구매금액이 ".number_format($promotioncode['limit_goods_price'])."원이상만 사용가능합니다.";
					$result = $promotioncode;
					echo json_encode($result);
					exit;
				}
			}


			foreach($cart['list'] as $key => $data){
				$cart_seq = $data['cart_seq'];

				$category = array();
				$catetmp = $this->goodsmodel->get_goods_category($data['goods_seq']);
				foreach($catetmp as $caterow) {
					if( strlen($caterow['category_code']) > 4) {
						if(strlen($caterow['category_code']) == 16) {
							$category[] = substr($caterow['category_code'], 0, 16);
							$category[] = substr($caterow['category_code'], 0, 12);
							$category[] = substr($caterow['category_code'], 0, 8);
							$category[] = substr($caterow['category_code'], 0, 4);
						}elseif(strlen($caterow['category_code']) == 12) {
							$category[] = substr($caterow['category_code'], 0, 12);
							$category[] = substr($caterow['category_code'], 0, 8);
							$category[] = substr($caterow['category_code'], 0, 4);
						}elseif(strlen($caterow['category_code']) == 8) {
							$category[] = substr($caterow['category_code'], 0, 8);
							$category[] = substr($caterow['category_code'], 0, 4);
						}else{
							$category[] = substr($caterow['category_code'], 0, 4);
						}
					}else{
						$category[] = $caterow['category_code'];
					}
				}

				$brands = $this->goodsmodel->get_goods_brand($data['goods_seq']);
				unset($brand_code);
				if($brands) foreach($brands as $bkey => $branddata){
					if( $branddata['link'] == 1 ){
						$brand_codear= $this->brandmodel->split_brand($branddata['category_code']);
						$brand_code[] = $brand_codear[0];
					}
				}


				unset($promotions);

				$cart['list'][$key]['cart_options'] = array_reverse($cart['list'][$key]['cart_options']);
				foreach($cart['list'][$key]['cart_options'] as $k1 => $cart_option) {
					$cart_option_seq = $cart_option['cart_option_seq'];
					if( strstr($promotioncode['type'],'promotion') ){//일반코드인경우
						$promotions = $this->promotionmodel->get_able_promotion_list($data['goods_seq'], $category, $brand_code, $sum_goods_price, $cartpromotioncode, $cart_option['price'], $cart_option['ea'] );
					}else{//개별코드
						$promotions = $this->promotionmodel->get_able_download_list($data['goods_seq'], $category, $brand_code, $sum_goods_price, $cartpromotioncode, $cart_option['price'], $cart_option['ea'] );
					}

					if( $promotions) {
						if($promotions['duplication_use'] == 1) {//중복할인은 무조건추가
							$this->db->where('cart_option_seq', $cart_option_seq);
							$this->db->update('fm_cart_option', array('promotion_code_seq'=>$promotions['promotion_seq'],'promotion_code_serialnumber'=>$promotions['promotion_input_serialnumber']));
						}else{//중복할인이 아니면서  상품 할인가(판매가)가 최대값인 상품으로 처리함
							//debug_var($cart_option_seq."==>".$max ." < ". $cart_option['price']);
							//if( ($max[$data['goods_seq']] && $max[$data['goods_seq']] < $cart_option['price']) || !$max[$data['goods_seq']]){
							if( ($max && $max < $cart_option['price']) || !$max){
								$ok_cart_option_seq = $cart_option_seq;
								//debug_var(" ok->".$cart_option_seq."==>".$max);
								$max = $cart_option['price'];
								$this->db->where('cart_option_seq', $cart_option_seq);
								$this->db->update('fm_cart_option', array('promotion_code_seq'=>$promotions['promotion_seq'],'promotion_code_serialnumber'=>$promotions['promotion_input_serialnumber']));

								$upsql = "update fm_cart_option set promotion_code_seq=null,promotion_code_serialnumber=null where cart_seq = {$cart_option['cart_seq']} and cart_option_seq!={$cart_option_seq}";
								$this->db->query($upsql);
							}
						}
					}else{
						$this->db->where('cart_option_seq', $cart_option_seq);
						$this->db->update('fm_cart_option', array('promotion_code_seq'=>'','promotion_code_serialnumber'=>''));
					}
				}
			}

			if($ok_cart_option_seq) {
				//중복할인이아니면서 프로모션코드가 최초적용된 주문옵션 제외한 주문상품 초기화
				$upsql = "update fm_cart_option set promotion_code_seq=null,promotion_code_serialnumber=null where cart_option_seq!={$ok_cart_option_seq}";//
				$this->db->query($upsql);
			}

			//사용제한금액
			$this->session->set_userdata('cart_promotioncodeseq_'.$session_id, $promotioncodeData['promotion_seq'] );
			$this->session->set_userdata('cart_promotioncode_'.$session_id, $cartpromotioncode );


			if( strstr($promotioncode['type'],'promotion') ){//일반코드인경우
				$promotioncode['result'] = true;
				$promotioncode['msg'] = "프로모션코드가 인증완료되었습니다.";
				$result = $promotioncode;
			}else{//개별코드인경우 -> 발급후 이용가능
				$promotioncode['result'] = true;
				$promotioncode['msg'] = "프로모션코드가 인증완료되었습니다.";
				$result = $promotioncode;
			}

		}else{
			$result = array('result'=>false, 'msg'=>"프로모션코드가 인증되지 않았습니다.<br/>정확히 입력해 주세요.");
		}
		echo json_encode($result);
		exit;
	}

	/** 프로모션 > 코드
	** @  상품상세 최대할인코드
	**/
	public function goods_coupon_max()
	{
		$this->load->model('promotionmodel');
		$this->load->helper('order');
		$this->load->helper('shipping');
		$this->load->model('goodsmodel');
		$this->load->model('brandmodel');

		$goodsSeq = $_GET['no'];
		$goodprice = $_GET['price'];

		$catetmp = $this->goodsmodel->get_goods_category($goodsSeq);
		foreach($catetmp as $caterow) {
			if( strlen($caterow['category_code']) > 4) {
				if(strlen($caterow['category_code']) == 16) {
					$category[] = substr($caterow['category_code'], 0, 16);
					$category[] = substr($caterow['category_code'], 0, 12);
					$category[] = substr($caterow['category_code'], 0, 8);
					$category[] = substr($caterow['category_code'], 0, 4);
				}elseif(strlen($caterow['category_code']) == 12) {
					$category[] = substr($caterow['category_code'], 0, 12);
					$category[] = substr($caterow['category_code'], 0, 8);
					$category[] = substr($caterow['category_code'], 0, 4);
				}elseif(strlen($caterow['category_code']) == 8) {
					$category[] = substr($caterow['category_code'], 0, 8);
					$category[] = substr($caterow['category_code'], 0, 4);
				}else{
					$category[] = substr($caterow['category_code'], 0, 4);
				}
			}else{
				$category[] = $caterow['category_code'];
			}
		}

		$brands = $this->goodsmodel->get_goods_brand($goodsSeq);
		if($brands) foreach($brands as $bkey => $branddata){
			if( $branddata['link'] == 1 ){
				$brand_codear = $this->brandmodel->split_brand($branddata['category_code']);
				$brand_code[] = $brand_codear[0];
			}
		}

		$max = 0;
		$result = $this->promotionmodel->get_able_promotion_max($goodsSeq, $category, $brand_code,0,'',0,1);
		foreach($result as $promotioncode){
				if(strstr($promotioncode['type'],'shipping')){
					if($promotioncode['sale_type']=='shipping_free') {
						$promotioncode['promotioncode_sale'] = $promotioncode['max_percent_shipping_sale'];
					}elseif($parampromotion['sale_type']=='shipping_won') {
						$promotioncode['promotioncode_sale'] = $promotioncode['won_shipping_sale'];
					}

				}elseif( $promotioncode['sale_type'] == 'percent' && $promotioncode['percent_goods_sale'] && $goodprice ){
				if( $this->config_system['cutting_price'] != 'none' ){
					$promotioncode['promotioncode_sale'] = $promotioncode['percent_goods_sale'] * $goodprice / ( $this->config_system['cutting_price'] * 100);
					$promotioncode['promotioncode_sale'] = floor($promotioncode['promotioncode_sale']);
					$promotioncode['promotioncode_sale'] = $promotioncode['promotioncode_sale'] * $this->config_system['cutting_price'];
				}else{
					$promotioncode['promotioncode_sale'] = $promotioncode['percent_goods_sale'] * $goodprice / 100;
					$promotioncode['promotioncode_sale'] = floor($promotioncode['promotioncode_sale']);
				}

				if($promotioncode['max_percent_goods_sale'] < $promotioncode['promotioncode_sale']){
					$promotioncode['promotioncode_sale'] = $promotioncode['max_percent_goods_sale'];
				}

			}else if( $promotioncode['sale_type'] == 'won' && $promotioncode['won_goods_sale'] && $goodprice ){
				$promotioncode['promotioncode_sale'] = $promotioncode['won_goods_sale'];
			}

			if($max < $promotioncode['promotioncode_sale']){
				$result_max = $promotioncode;
				$max = $promotioncode['promotioncode_sale'];
			}
		}
		if(strstr($result_max['type'],'shipping')){
			if($result_max['sale_type']=='shipping_free') {
				$result=array(
						'benifit'=>'기본배송비 무료(최대 '.number_format($result_max['promotioncode_sale']).'원)',
						'codenumber'=>$result_max['promotion_input_serialnumber']
				);
			}elseif($result_max['sale_type']=='shipping_won') {
				$result=array(
						'benifit'=>'배송비 '.number_format($result_max['promotioncode_sale']).'원 할인',
						'codenumber'=>$result_max['promotion_input_serialnumber']
				);
			}
		}else{
			if($result_max['sale_type'] == 'percent'){
				$result=array(
						'benifit'=>number_format($result_max['percent_goods_sale']).'% 할인',
						'codenumber'=>$result_max['promotion_input_serialnumber']
				);
				if($result_max['max_percent_goods_sale']){
					$result['benifit'] .= "(최대 ".number_format($result_max['max_percent_goods_sale'])."원)";
				}
			}else{
				$result=array(
						'benifit'=>number_format($result_max['promotioncode_sale']).'원 할인',
						'codenumber'=>$result_max['promotion_input_serialnumber']
				);
			}
		}

		echo json_encode($result);
	}

	/** 프로모션 > 코드
	** @ 초기화하기
	**/
	public function getPromotionCartDel()
	{
		$this->load->model('promotionmodel');
		$this->load->helper('order');
		$this->load->helper('shipping');
		$this->load->model('cartmodel');
		$cart = $this->cartmodel->cart_list();
		foreach($cart['list'] as $key => $data){
			foreach($cart['list'][$key]['cart_options'] as $k1 => $cart_option){
				$cart_option_seq = $cart_option['cart_option_seq'];
				$this->db->where('cart_option_seq', $cart_option_seq);
				$this->db->update('fm_cart_option', array('promotion_code_seq'=>'','promotion_code_serialnumber'=>''));
			}
		}
		$this->session->unset_userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
		$this->session->unset_userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
	}

}

/* End of file promotion.php */
/* Location: ./app/controllers/promotion.php */