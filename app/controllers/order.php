<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class order extends front_base {

	public function __construct(){
		parent::__construct();
		$this->load->helper('order');
		$this->load->helper('shipping');
		$this->load->library('snssocial');
		$this->load->library('sale');

		$this->load->model('promotionmodel');
	}


	public function index()
	{
		redirect("/order/cart");
	}

	// 장바구니 목록
	public function cart(){

		$this->load->model('cartmodel');
		$this->load->model('goodsmodel');

		$applypage			= 'cart';
		$total_ea			= 0;
		$goodscancellation	= false;
		$is_coupon			= false;
		$is_goods			= false;

		// 회원정보 추출
		if	($this->userInfo['member_seq']){
			$this->load->model('membermodel');
			$members	= $this->membermodel->get_member_data($this->userInfo['member_seq']);
			$this->template->assign('members',$members);
		}

		// 선택 상품을 장바구니에 합쳐줌
		$this->cartmodel->merge_for_choice();

		if	($admin != ''){
			$where_arr[]	= 'admin';
		}else{
			if	(!isset($_GET['mode']))	$mode	= 'cart';
			else						$mode	= $_GET['mode'];
			$where_arr[]	= $mode;

			if	($this->userInfo['member_seq']){
				$this->load->model('membermodel');
				$member_seq		= $this->userInfo['member_seq'];
				$where_query[]	= "cart.member_seq = ? ";
				$where_arr[]	= $member_seq;
			}else{
				$where_query[]	= "cart.session_id = ? ";
				$where_arr[]	= $session_id;
			}
		}

		// 장바구니 및 주문설정, 적립금설정 정보 추출
		$cart			= $this->cartmodel->catalog();
		$cfg['order']	= config_load('order');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');

		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['total_price']			= $cart['total'];
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용


		// 장바구니에서 구한 데이터 초기화
		$cart['total']						= 0;
		$cart['total_ea']					= 0;
		$cart['total_sale']					= 0;
		$cart['total_price']				= 0;
		$cart['total_reserve']				= 0;
		$cart['total_point']				= 0;

		// 장바구니 목록
		foreach($cart['list'] as $key => $data){

			// 실물, 쿠폰 상품의 존재여부
			if	( $data['goods_kind'] == 'coupon' )	$is_coupon			= true;
			else									$is_goods			= true;

			// 청약철회상품
			$goodscancellation	= false;
			if	($data['cancel_type'] == 1)			$goodscancellation	= true;

			// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
			if	( $data['event']['event_goodsStatus'] === true ){
				$err_msg	= '↓아래 상품은 단독이벤트 기간에만 구매가 가능합니다.';
				$err_msg	.= '\\n'.$data['goods_name'];
				alert($err_msg);
			}

			// 쿠폰상품 구매가능 여부 체크
			if	($data['goods_kind'] == 'coupon'){
				if	($data['cart_option_seq']){
					// 쿠폰상품 기간체크
					$chkcouponexpire	= check_coupon_date_option($data['goods_seq'], $data['option1'], $data['option2'], $data['option3'], $data['option4'], $data['option5']);
					if	( $chkcouponexpire['couponexpire'] === false ){
						// 해당상품의 옵션제거
						$this->cartmodel->delete_option($data['cart_option_seq'],'');
						$err_msg	= '↓아래 쿠폰상품의 유효기간은 '.$chkcouponexpire['social_start_date']
									. ' ~ '.$chkcouponexpire['social_end_date'].' 입니다.';
						$err_msg	.= '\\n'.$data['goods_name'];
						if	($opttitle)	$err_msg	.= '('.$opttitle.')';
						alert($err_msg);
						$check_ea++;
						continue;	// check_ea가 있는 경우 어차피 페이지 리로드함
					}
				}

				if	($data['cart_suboption_seq']){
					// 쿠폰상품 기간체크
					$chkcouponexpire	= check_coupon_date_suboption($data['goods_seq'], $data['suboption_title'], $data['suboption']);
					if	( $chkcouponexpire['couponexpire'] === false ){
						// 해당상품의 옵션제거
						$this->cartmodel->delete_option($data['cart_suboption_seq'],'');
						$err_msg	= '↓아래 쿠폰상품의 유효기간은 '.$chkcouponexpire['social_start_date']
									. ' ~ '.$chkcouponexpire['social_end_date'].' 입니다.';
						$err_msg	.= '\\n'.$data['goods_name'];
						if	($opttitle)	$err_msg	.= '('.$opttitle.')';
						alert($err_msg);
						continue;
					}
				}
			}

			// 필수 옵션 재고 체크
			$chk	= check_stock_option($data['goods_seq'], $data['option1'], $data['option2'],
											$data['option3'], $data['option4'], $data['option5'],
											$data['ea'], $cfg['order'], 'view_stock');
			if	( $chk['stock'] < 0 ){
				// 해당상품의 옵션제거
				$this->cartmodel->delete_cart_option($data['cart_option_seq'],'');

				$opttitle	= '';
				if	($data['option1'])	$opttitle	= $data['option1'];
				if	($data['option2'])	$opttitle	.= ' '.$data['option2'];
				if	($data['option3'])	$opttitle	.= ' '.$data['option3'];
				if	($data['option4'])	$opttitle	.= ' '.$data['option4'];
				if	($data['option5'])	$opttitle	.= ' '.$data['option5'];

				$err_msg	= '↓아래 상품의 재고는 '.$chk['sale_able_stock'].'개 입니다.';
				$err_msg	.= '\\n'.$data['goods_name'];
				if	($opttitle)	$err_msg	.= '('.$opttitle.')';
				alert($err_msg);
				$check_ea++;
				continue;
			}

			// 추가옵션 재고 체크
			if	($data['cart_suboptions']){
				$stock_sub_status	= false;
				foreach($data['cart_suboptions'] as $k => $cart_suboption){
					$chk	= check_stock_suboption($data['goods_seq'], $cart_suboption['suboption_title'],
													$cart_suboption['suboption'], $cart_suboption['ea'],
													$cfg['order'], 'view_stock');
					if	( $chk['stock'] < 0 ){
						// 해당상품의 옵션제거
						$this->cartmodel->delete_option('',$cart_suboption['cart_suboption_seq']);
						$err_msg	= '↓아래 상품의 재고는 '.$chk['sale_able_stock'].'개 입니다.';
						$err_msg	.= '\\n'.$data['goods_name'];
						if	($cart_suboption['suboption'])
							$err_msg	.= '('.$cart_suboption['suboption'].')';

						alert($err_msg);
						$check_ea++;
						$stock_sub_status	= true;
						continue;
					}
				}
				if	($stock_sub_status)	continue;
			}

			// 해당 상품의 전체 카테고리
			$category	= array();
			$tmp		= $this->goodsmodel->get_goods_category($data['goods_seq']);
			foreach($tmp as $row)	$category[]		= $row['category_code'];

			//----> sale library 적용 ( 정가기준 목록에서는 sale_price를 넘기지 않음 )
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $data['consumer_price'];
			$param['price']						= $data['org_price'];
			$param['sale_price']				= $data['price'];
			$param['ea']						= $data['ea'];
			$param['goods_ea']					= $cart['data_goods'][$data['goods_seq']]['ea'];
			$param['category_code']				= $category;
			$param['goods_seq']					= $data['goods_seq'];
			$param['goods']						= $data;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);

			$data['org_price']					= ($data['consumer_price']) ? $data['consumer_price'] : $data['org_price'];
			$data['sales']						= $sales;
			$data['tot_org_price']				= $data['org_price'] * $data['ea'];
			$data['tot_sale_price']				= $sales['total_sale_price'];
			$data['tot_result_price']			= $sales['result_price'];

			// sale_price가 없을 시 여기서 event까지 계산함
			if	(!$data['event'] && $this->sale->cfgs['event']){
				$data['event']					= $this->sale->cfgs['event'];
				$data['eventEnd']				= $sales['eventEnd'];
			}

			// 적립금 / 포인트 ( 실결제 금액 기준이기에 여기서 계산 )
			// 상품의 적립금 / 포인트 계산
			$data['reserve']	= $this->goodsmodel->get_reserve_with_policy($data['reserve_policy'],
									$sales['result_price'], $cfg_reserve['default_reserve_percent'],
									$data['reserve_rate'], $data['reserve_unit'], $data['reserve']);
			$data['point']		= (int) $this->goodsmodel->get_point_with_policy($sales['result_price']);
			// 이벤트 적립금 / 포인트 계산 ( 장바구니에서 혜택적용할인가를 받아서 쓸 경우만 )
			if	($param['sale_price']){
				$this->sale->cfgs['event']		= $data['event'];
				$data['reserve']				+= $this->sale->event_sale_reserve($sales['result_price']);
				$data['point']					+= $this->sale->event_sale_point($sales['result_price']);
			}
			$data['reserve']					= $data['reserve'] + $sales['tot_reserve'];
			$data['point']						= $data['point'] + $sales['tot_point'];

			// 총 합계
			$cart['total']						+= $data['price']*$data['ea'];
			$cart['total_ea']					+= $data['ea'];
			$cart['total_sale']					+= $sales['total_sale_price'];
			$cart['total_price']				+= $sales['result_price'];
			$cart['total_reserve']				+= $data['reserve'];
			$cart['total_point']				+= $data['point'];

			// 총 할인 목록 노출을 위한 배열 생성
			if	($sales['title_list'])foreach($sales['title_list'] as $sale_type => $sale_title){
				$data['tsales']['sale_list'][$sale_type]		= $sales['sale_list'][$sale_type];
				$cart['total_sale_list'][$sale_type]['title']	= $sale_title;
				$cart['total_sale_list'][$sale_type]['price']	+= $sales['sale_list'][$sale_type];
			}
			$this->sale->reset_init();
			//<---- sale library 적용

			// 추가구성 옵션
			if	($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $k => $subdata){
					//----> sale library 적용 ( 정가기준 목록에서는 sale_price를 넘기지 않음 )
					unset($param, $sales);
					$param['option_type']				= 'suboption';
					$param['sub_sale']					= $subdata['sub_sale'];
					$param['consumer_price']			= $subdata['consumer_price'];
					$param['price']						= $subdata['price'];
					$param['sale_price']				= $subdata['price'];
					$param['ea']						= $subdata['ea'];
					$param['goods_ea']					= $cart['data_goods'][$data['goods_seq']]['ea'];
					$param['category_code']				= $category;
					$param['goods_seq']					= $data['goods_seq'];
					$param['goods']						= $data;
					$this->sale->set_init($param);
					$sales								= $this->sale->calculate_sale_price($applypage);

					$subdata['sales']					= $sales;
					$subdata['org_price']				= ($subdata['consumer_price']) ? $subdata['consumer_price'] : $subdata['price'];

					// 적립금 / 포인트
					$subdata['reserve']					= $this->goodsmodel->get_reserve_with_policy($data['reserve_policy'], $sales['result_price'], $cfg_reserve['default_reserve_percent'], $subdata['reserve_rate'], $subdata['reserve_unit'], $subdata['reserve']);
					$subdata['point']					= $this->goodsmodel->get_point_with_policy($sales['result_price']);
					$subdata['reserve']					= $subdata['reserve'] + $sales['tot_reserve'];
					$subdata['point']					= $subdata['point'] + $sales['tot_point'];

					$data['tot_org_price']				+= $subdata['org_price'] * $subdata['ea'];
					$data['tot_sale_price']				+= $sales['total_sale_price'];
					$data['tot_result_price']			+= $sales['result_price'];

					$cart['total']						+= $subdata['price']*$subdata['ea'];
					$cart['total_ea']					+= $subdata['ea'];
					$cart['total_sale']					+= $sales['total_sale_price'];
					$cart['total_price']				+= $sales['result_price'];
					$cart['total_reserve']				+= $subdata['reserve'];
					$cart['total_point']				+= $subdata['point'];
					if	($sales['title_list'])foreach($sales['title_list'] as $sale_type => $sale_title){
						$data['tsales']['sale_list'][$sale_type]		+= $sales['sale_list'][$sale_type];
						$cart['total_sale_list'][$sale_type]['title']	= $sale_title;
						$cart['total_sale_list'][$sale_type]['price']	+= $sales['sale_list'][$sale_type];
					}

					$this->sale->reset_init();
					//<---- sale library 적용

					$data['cart_suboptions'][$k]	= $subdata;
				}
			}

			$cart['list'][$key]		= $data;
			if	($data['shipping_policy'] == 'goods')
				$data['shipping_policy']	.= '_'.$data['cart_seq'];
			$group_key				= $data['goods_type'].'|'.$data['shipping_policy'];
			$shipping_cart_list[$group_key][]				= $data;
			$shipping_cart_list[$group_key][0]['rowspan']	+= count($data['cart_suboptions']) + 1;
		}

		// 삭제된 장바구니 상품이 있을시 페이지 새로고침
		if	( $check_ea > 0)	pageRedirect('/order/cart');

		// 장바구니 오기전 마지막 위치 셰션값 ( referer_address인 것 같다 )
		if($_SESSION["refer_adress"] != '')	$cart_history	= $_SESSION['refer_adress'];
		else								$cart_history	= "../main/index";

		//좋아요 할인 혜택구분 $cfg['order']['fblike_ordertype'] 0 : 회원/비회원, 1 : 회원만
		$session_arr	= ( $this->session->userdata('user') ) ? $this->session->userdata('user') : $_SESSION['user'];
		// 설정값을 적용여부 값으로 변경
		$cfg['order']['fblike_ordertype']	= ( ($cfg['order']['fblike_ordertype'] == 1 && $session_arr['member_seq'] ) || ($cfg['order']['fblike_ordertype'] != 1) ) ? 1 : 0;

		// 실 결제금액 기준으로 배송비 재계산
		$shipping						= use_shipping_method();
		if(!$shipping){
			echo "<script>alert('배송방법이 없습니다.\\n관리자에게 문의해 주세요.');history.back(-1);</script>";
			exit;
		}
		// 해외배송불가카테고리 체크
		foreach($shipping[1] as $i=>$row){
			foreach($row['exceptCategory'] as $exceptCategory){
				if(in_array($exceptCategory,$category)){
					unset($shipping[1][$i]);
				}
			}
		}
		if(!count($shipping[1])) unset($shipping[1]);
		if( is_array($shipping) ) {
			foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
		}
		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;
		if($shipping_policy['count']==0 && $this->config_system['service']['code']!='P_STOR'){
			pageBack("배송방법이 존재하지 않습니다.\\n\\n쇼핑몰 고객센터에 문의 해 주세요.");
			exit;
		}

		$this->shipping_order			= $shipping;
		if	(is_array($shipping))
			$international_shipping		= $shipping[1][$_POST['shipping_method_international']];
		$total_shipping_price = $this->_calculate_single_shipping_price(&$scripts,&$cart,$cart['total_price'],&$shipping,&$international_shipping);
		$cart['shipping_price']['shop']		= $this->total_cart_shop_shipping_price;
		$cart['shipping_price']['goods']	= $this->total_cart_goods_shipping_price;
		$cart['total_price']	+= (int)array_sum($cart['shipping_price']);//배송비계산식아래쪽으로 이동 ($cart['shipping_price']) 위치변경하지 말아주세요.

		$cart_promotioncode = $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));

		// 스킨 로드
		$template_dir	= $this->template->template_dir;
		$compile_dir	= $this->template->compile_dir;
		$this->template->assign('firstmallcartid', $this->session->userdata('session_id'));
		$this->template->assign('list', $cart['list']);
		$this->template->assign('shipping_cart_list', $shipping_cart_list);
		$this->template->assign('data_goods', $cart['data_goods']);
		$this->template->assign('is_coupon',$is_coupon);
		$this->template->assign('is_goods',$is_goods);
		$this->template->assign('cfg',$cfg);
		$this->template->assign('promocodeSale',$cart['promocodeSale']);
		$this->template->assign('total',$cart['total']);
		$this->template->assign('total_ea',$cart['total_ea']);
		$this->template->assign('total_reserve',$cart['total_reserve']);
		$this->template->assign('total_point',$cart['total_point']);
		$this->template->assign('total_sale',$cart['total_sale']);
		$this->template->assign('total_sale_list',$cart['total_sale_list']);
		$this->template->assign('total_price',$cart['total_price']);
		$this->template->assign('shipping_price',$cart['shipping_price']);
		$this->template->assign('shipping_company_cnt',$cart['shipping_company_cnt']);
		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('cart_history',$cart_history);
		$this->template->assign('member_seq',$this->userInfo['member_seq']);
		$this->template->assign('cart_promotioncode' , $cart_promotioncode);
		$this->template->assign('cartpage',true);//현재페이지정보 넘겨줌

		if	($cart['list'] && $cart['total_price']){
			// 네이버 체크아웃
			$navercheckout		= config_load('navercheckout');
			$marketing_admin	= $this->session->userdata('marketing');
			if	(
					$navercheckout['use'] == 'y'											// "사용모드"일때
				||	($navercheckout['use'] == 'test' && $this->managerInfo)					// "테스트모드"이고 관리자아이디일때
				||	($navercheckout['use'] == 'test' && $marketing_admin == 'nbp' ) // "테스트모드"이고 회원아이디 gabia일때
			){
				// 예외카테고리 체크, 예외상품 체크
				$expectCategoryChk	= false;
				$expectGoodsChk		= false;
				foreach($cart['list'] as $key => $data){
					$categorys	= $this->goodsmodel->get_goods_category($data['goods_seq']);
					foreach($navercheckout['except_category_code'] as $v1){
						foreach($categorys as $v2){
							if	($v1['category_code'] == $v2['category_code'] ||
								preg_match("/^".$v1['category_code']."/",$v2['category_code'])){
								$expectCategoryChk	= true;
							}
						}
					}

					foreach($navercheckout['except_goods'] as $v1){
						if	($v1['goods_seq'] == $data['goods_seq']){
							$expectGoodsChk	= true;
						}
					}

					// 비과세 상품 버튼 비노출
					// if($data['tax'] == 'exempt') $expectGoodsChk = true;
				}

				// 착불배송 사용여부
				$this->load->helper('shipping');
				$shipping		= use_shipping_method();
				$use_postpaid	= false;
				if	( $shipping[0] ){
					foreach($shipping[0] as $key => $data){
						if	($data['code']=='postpaid' && $data['useYn']=='y'){
							$this->template->assign(array('use_postpaid'=>1));
						}
					}
				}

				if	(!$expectGoodsChk && !$expectCategoryChk && !($this->fammerceMode || $this->storefammerceMode) ){
					$this->template->template_dir	= BASEPATH."../partner";
					$this->template->compile_dir	= BASEPATH."../_compile/";
					$this->template->assign(array('navercheckout'=>$navercheckout));
					$this->template->define(array('navercheckout'=>'navercheckout_cart.html'));
					$navercheckout_tpl				= $this->template->fetch('navercheckout');
					$this->template->assign(array('navercheckout_tpl'=>$navercheckout_tpl));
				}
			}
		}

		$this->template->template_dir	= $template_dir;
		$this->template->compile_dir	= $compile_dir;
		$this->print_layout($this->template_path());
	}

	public function fblike_opengraph_firstmallplus()
	{
		//$this->snssocial->facebooklogin();
		$this->fbuser = $this->snssocial->facebookuserid();
		//debug_var($this->fbuser);
		if ( $this->fbuser < 1 ) {
			$this->facebook = new Facebook(array(
			  'appId'  => $this->__APP_ID__,
			  'secret' => $this->__APP_SECRET__,
			  "cookie" => true
			));
			// Get User ID
			$this->fbuser = $this->facebook->getUser();
			//debug_var($this->fbuser);
			if( $this->session->userdata('fbuser') < 1 ) {
				$this->session->set_userdata('fbuser', $this->fbuser);
			}
		}else{
			if( $this->session->userdata('fbuser') < 1 ) {
				$this->session->set_userdata('fbuser', $this->fbuser);
			}
		}

		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if(strstr($referer['host'], $this->config_system['domain']) ) {
			$this->fblike_opengraph('firstmallplus');
			$this->fbopengraph('firstmallplus');
			if( $_GET['firstmallcartid']!=$this->session->userdata('session_id')) {
				$session_id = $_GET['firstmallcartid'];
				$this->session->set_userdata('session_id', $session_id);//재설정
			}
			if( $_GET['files']=='settle') {
				if( $this->session->userdata('fbuser')  > 0 ) {
					echo '$("#fbloginlay").hide();';//앱동의창 숨김
				}else{
					echo '$("#fbloginlay").show();';//앱동의창 숨김//
				}
			}
		}
	}

	public function fblike_opengraph($f=null)
	{
		$this->load->helper('cookie');
		$fbuserprofile = $this->snssocial->facebookuserid();
		if ( !$fbuserprofile ) {
			$this->facebook = new Facebook(array(
			  'appId'  => $this->__APP_ID__,
			  'secret' => $this->__APP_SECRET__,
			  "cookie" => true
			));
			// Get User ID
			$fbuserprofile = $this->facebook->getUser();
			if($fbuserprofile && !$this->session->userdata('fbuser')){
				$this->session->set_userdata('fbuser', $fbuserprofile);
			}else{
				$fbuserprofile = $this->snssocial->facebooklogin();
				if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}
		}else{
			if( !$this->session->userdata('fbuser') ) {
				$this->session->set_userdata('fbuser', $fbuserprofile);
			}
		}

		/**
		* facebook like 체크
		**/
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if( strstr($referer['query'], 'mode=direct') ) $_GET['mode'] = "direct";

		if($_GET['firstmallcartid'] &&  $_GET['firstmallcartid']!=$this->session->userdata('session_id')) {
			$session_id = $_GET['firstmallcartid'];
			$this->session->set_userdata('session_id', $session_id);
		}

		$this->load->model('cartmodel');
		$this->load->model('goodsfblike');

		$ss_fblike_name = 'goods_fblike';
		$goodsfblikess = $this->session->userdata($ss_fblike_name);
		$session_id = $this->session->userdata('session_id');


		if( $this->session->userdata('fbuser') ) {
			$sns_id = $this->session->userdata('fbuser');
		}elseif(get_cookie('fbuser')){
			$sns_id = get_cookie('fbuser');
		}

		if($this->userInfo['member_seq']){
			$this->load->model('membermodel');
			$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보
			$sns_id = $this->mdata['sns_f'];
			if($sns_id){
				$addwhereis = " and (session_id='".$session_id."' or sns_id = '".$sns_id."'  or member_seq = '".$this->userInfo['member_seq']."' ) ";
			}else{
				$addwhereis = " and (session_id='".$session_id."' or member_seq = '".$this->userInfo['member_seq']."' ) ";
			}
		}else{
			if($sns_id){
				$addwhereis = " and (session_id='".$session_id."' or sns_id = '".$sns_id."' ) ";
			}else{
				$addwhereis = " and (session_id='".$session_id."' ) ";
			}
		}

		$cfg['order'] = config_load('order');

		/**$this->load->model('configsalemodel');
		$sc['type'] = 'fblike';
		$systemfblike = $this->configsalemodel->lists($sc);//like 할인시 추가할인 추가적립
		**/

		$cart = $this->cartmodel->cart_list();
		foreach($cart['list'] as $data){

			if($this->systemfblike['result']) {//할인설정이된경우
				$cart_seq = $data['cart_seq'];
				$fblike = 'N';
				$goodslikeurl = $this->likeurl.'&no='.$data['goods_seq'];

				$sc['select']  = " like_seq ";
				$whereis = " and goods_seq='".$data['goods_seq']."' ".$addwhereis;
				$sc['whereis'] = $whereis;
				$ckfblike = $this->goodsfblike->get_data($sc);//like 한경우 DB 화처리
				if($cfg['order']['fblike_ordertype'] == 1 ){//회원만 할인제공
					if($this->userInfo['member_seq']){
						if ( (strstr($goodsfblikess,'['.$goodslikeurl.']') && $goodsfblikess) || $ckfblike ) {//좋아요세션이 있거나 로그에 남은경우
							$fblike = 'Y';
							if($ckfblike) {
								$insdata = array(
								'like_seq' => $ckfblike['like_seq'],
								'goods_seq' => $data['goods_seq'],
								'member_seq' => $this->userInfo['member_seq'],
								'sns_id' => $sns_id,
								'session_id' => $session_id,
								'date' => date('Y-m-d H:i:s'),
								'ip' => $this->input->ip_address(),
								'agent' => $_SERVER['HTTP_USER_AGENT']
								);
								$this->goodsfblike->fblike_modify($insdata);
							}
						}else{
							if($this->session->userdata('fbuser')) {//페이스북 로그인한 경우 실시간체크함
								if ( $this->snssocial->facebook_goodsLike($goodslikeurl) ) {

									$fblike = 'Y';

									$insdata = array(
									'goods_seq' => $data['goods_seq'],
									'member_seq' => $this->userInfo['member_seq'],
									'sns_id' => $sns_id,
									'session_id' => $session_id,
									'date' => date('Y-m-d H:i:s'),
									'ip' => $this->input->ip_address(),
									'agent' => $_SERVER['HTTP_USER_AGENT']
									);
									$this->goodsfblike->fblike_write($insdata);

								}
							}
						}
					}
				}else{//회원/비회원 모두 할인제공
					if ( (strstr($goodsfblikess,'['.$goodslikeurl.']') && $goodsfblikess) || $ckfblike ) {//좋아요세션이 있거나 로그에 남은경우
						$fblike = 'Y';
						if($ckfblike) {
							$insdata = array(
							'like_seq' => $ckfblike['like_seq'],
							'goods_seq' => $data['goods_seq'],
							'member_seq' => $this->userInfo['member_seq'],
							'sns_id' => $sns_id,
							'session_id' => $session_id,
							'date' => date('Y-m-d H:i:s'),
							'ip' => $this->input->ip_address(),
							'agent' => $_SERVER['HTTP_USER_AGENT']
							);
							$this->goodsfblike->fblike_modify($insdata);
						}
					}else{
						if($this->session->userdata('fbuser')) {//페이스북 로그인한 경우 실시간체크함

							if ( $this->snssocial->facebook_goodsLike($goodslikeurl) ) {
								$fblike = 'Y';

								$insdata = array(
								'goods_seq' => $data['goods_seq'],
								'member_seq' => $this->userInfo['member_seq'],
								'sns_id' => $sns_id,
								'session_id' => $session_id,
								'date' => date('Y-m-d H:i:s'),
								'ip' => $this->input->ip_address(),
								'agent' => $_SERVER['HTTP_USER_AGENT']
								);

								$this->goodsfblike->fblike_write($insdata);

							}
						}
					}
				}

				$this->db->where('cart_seq', $cart_seq);
				$this->db->update('fm_cart', array('fblike'=>$fblike));
			}//endif
		}//endforeach
		echo '$("#facebook_mgs").html("");';
		if($_GET['files']=='settle' && !$f)echo 'getfblikeopengraph();';
	}

	public function fbopengraph($f=null)
	{
		/**
		* facebook opengraph > love item
		**/
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if($_GET['firstmallcartid'] &&  $_GET['firstmallcartid']!=$this->session->userdata('session_id')) {
			$session_id = $_GET['firstmallcartid'];
			$this->session->set_userdata('session_id', $session_id);
		}

		$this->load->model('cartmodel');
		$this->load->model('goodsfblike');
		$session_id = $this->session->userdata('session_id');

		$cart = $this->cartmodel->cart_list();
		foreach($cart['list'] as $data){
			if($this->session->userdata('fbuser')) {//회원로그인한 경우
				$this->snssocial->publishCustomAction($this->domainurl.'/goods/view?no='.$data['goods_seq'],'buy');//.'&buy=1'
			}
		}//endforeach
	}

	// 장바구니 담기
	public function add()
	{

		$member_seq = "";
		$pre_cart_seqs = "";
		if(! isset($_GET['mode'])) $mode = "cart";
		else $mode = "direct";

		// 상품 추가입력사항 파일 업로드 시 저장 폴더 생성
		$path = ROOTPATH."data/order/";
		if(!is_dir($path)){
			@mkdir($path);
			@chmod($path,0777);
		}

		$this->load->model('cartmodel');
		$this->load->model('goodsfblike');

		if( !isset($_POST['option']) && !isset($_POST['optionEa']) ){
			openDialogAlert("장바구니에 담을 상품이 없습니다.",400,140,'parent',"");
			exit;
		}

		$goods_seq = (int) $_POST['goodsSeq'];
		$this->load->model('goodsmodel');
		$inputs = $this->goodsmodel->get_goods_input($goods_seq);

		if( isset($_POST['inputsValue']) && is_array($_POST['inputsValue'][0])){
			// 2014-12-18 옵션 개편 후 (ocw)
			foreach($inputs as $key_input => $data_input){
				foreach($_POST['inputsValue'][0] as $k=>$v){
					if($data_input['input_require'] == 1 && !$_POST['inputsValue'][$key_input][$k]){
						openDialogAlert(addslashes($_POST['inputsTitle'][$key_input][$k]) . " 옵션은 필수입니다.",400,140,'parent',"");
						exit;
					}elseif($data_input['input_require'] == 1){
						$inputs_required = true;
					}
				}
			}
		}else{
			// 2014-12-18 옵션 개편 전 (ocw)
			$inputs_required = false;
			$file_num = 0;
			$input_num = 0;
			foreach($inputs as $key_input => $data_input){

				$_POST['inputsValue'][$input_num] = trim( $_POST['inputsValue'][$input_num] );
				if( $data_input['input_require'] == 1 && !$_POST['inputsValue'][$input_num] && $data_input['input_form'] != 'file' ){
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
				} else {
					$input_num++;
				}
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

		/*
		if( count($_POST['optionEa']) > 1 && ($inputs_required || $suboption_required) ){
			if( $suboption_required ) $r_require_msg[] = "추가옵션이 필수 입니다.<br/> 추가옵션이 ";
			if( $inputs_required ) $r_require_msg[] = "추가입력사항이 필수 입니다.<br/> 추가입력사항이 ";
			$require_msg = implode(',',$r_require_msg);

			if	($mode == 'direct'){
				$msg = $require_msg." 없는 상품을 제외하고 <br/><strong>바로구매하시겠습니까?</strong>";
			}else{
				$msg = $require_msg." 없는 상품을 제외하고 장바구니에 담겼습니다.<br/><strong>지금 확인하시겠습니까?</strong>";
			}
			foreach($_POST['optionEa'] as $k1 => $ea){
				if($k1 > 0 && $_POST['optionEa'][$k1]) unset($_POST['optionEa'][$k1]);
			}
		}
		*/

		## 장바구니 통계 데이터 추가
		$this->load->model('statsmodel');
		$goodsinfo = $this->goodsmodel->get_goods($goods_seq);
		$stats_param['goods_seq']	= $goods_seq;
		$stats_param['goods_name']	= $goodsinfo['goods_name'];
		foreach($_POST['optionEa'] as $k1 => $ea){
			for($i=0;$i<5;$i++){
				$opt_idx	= $i + 1;
				$stats_param['option'.$opt_idx]	= '';
				if(!isset($_POST['option'][$i][$k1])){
					$_POST['option'][$i][$k1] = "";
					$_POST['optionTitle'][$i][$k1] = "";
				}else{
					if	($_POST['option'][$i][$k1])
						$stats_param['option'.$opt_idx]	= $_POST['option'][$i][$k1];
				}
			}
			$stats_param['ea']	= $ea;

			$this->statsmodel->insert_cart_stats($stats_param);
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

		$ckfblike = $this->goodsfblike->getgoodsfblike($goods_seq, '', $session_id);
		if($ckfblike){
			$insert_data['fblike'] = 'Y';
		}else{
			$insert_data['fblike'] = 'N';
		}

		$this->load->model('brandmodel');
		$category = array();
		$catetmp = $this->goodsmodel->get_goods_category($goods_seq);
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

		$brands = $this->goodsmodel->get_goods_brand($goods_seq);
		unset($brand_code);
		if($brands) foreach($brands as $bkey => $branddata){
			if( $branddata['link'] == 1 ){
				$brand_codear= $this->brandmodel->split_brand($branddata['category_code']);
				$brand_code[] = $brand_codear[0];
			}
		}

		$this->db->insert('fm_cart', $insert_data);
		$cart_seq = $this->db->insert_id();
		$this->cartmodel->insert_cart_alloption($cart_seq,$inputs);

		if($mode == "cart"){

			/* 상품분석 수집 */
			$this->load->model('goodslog');
			$this->goodslog->add('cart',$goods_seq);

			/* 고객리마인드서비스 알림 상세유입로그 */
			$this->load->helper('reservation');
			$curation = array("action_kind"=>"cart","cart_seq"=>$cart_seq,"goods_seq"=>$goods_seq);
			curation_log($curation);

			if(!$msg) $msg = "상품이 장바구니에 담겼습니다.<br/><strong>지금 확인하시겠습니까?</strong>";

			if( $this->fammerceMode  || $this->storefammerceMode ) {
				$yescallback = "parent.location.href='../order/cart';";
				openDialogConfirm($msg,400,160,'parent',$yescallback,'');
			}
			else{
				$yescallback = "top.location.href='../order/cart';";
				openDialogConfirm($msg,400,160,'parent',$yescallback,'');
			}

			// 우측 퀵메뉴 장바구니 카운트 증가 추가 leewh 2014-06-19
			echo("<script>top.getRightItemTotal('right_item_cart');</script>");

			// 통계데이터(cart) 전송
			$arr_goods_seq[] = $goods_seq;
			$str_goods_seq = implode('|',$arr_goods_seq);
			echo "<script type='text/javascript'>parent.statistics_firstmall('cart','".$str_goods_seq."','','');</script>";

		}else{
			$url	= "/order/settle?mode=".$mode;
			if(!isset($_GET['guest']) && !$member_seq){
				$url	= "/member/login?return_url=" . urlencode($url);
			}

			if	($msg){
				$yescallback = "parent.location.href='".$url."';";
				openDialogConfirm($msg,400,160,'parent',$yescallback,'');
			}else{
				pageLocation($url,'','parent');
				echo("<script>parent.location.href='".$url."';</script>");
			}
		}
	}

	public function addcart()
	{
		if(!$this->userInfo['member_seq']){
			$return_url = "/order/cart";
			$url = "/member/login?return_url=" . urlencode($return_url);
			if( $this->fammerceMode  || $this->storefammerceMode ) {
				pageLocation($url,'','parent');
			}else{
				pageLocation($url,'','top');
			}
			exit;
		}else{
			if( $this->fammerceMode  || $this->storefammerceMode ) {
				pageLocation('../order/cart','','parent');exit;
				echo("<script>parent.location.href='../order/cart?mode=".$mode."';</script>");
			}
			else{
				pageLocation('../order/cart','','top');exit;
				echo("<script>top.location.href='../order/cart?mode=".$mode."';</script>");
			}
		}
	}

	public function addsettle()
	{
		if( isset($_GET['mode']) ) $mode = $_GET['mode'];

		if($mode == 'choice' && $_POST['cart_option_seq'] ){
			$str_cart_option_seq = implode(',',$_POST['cart_option_seq']);
			$query = "update fm_cart set distribution='choice' where cart_seq in (select cart_seq from fm_cart_option where cart_option_seq in (".$str_cart_option_seq."))";
			$this->db->query($query);

			$query = "select cart_seq from fm_cart_option where cart_option_seq in (".$str_cart_option_seq.")";
			$query = $this->db->query($query);
			foreach($query->result_array() as $cart_option_data){
				$r_cart_seq[] = $cart_option_data['cart_seq'];
			}

			$str_cart_seq = implode(',',$r_cart_seq);
			if($str_cart_seq){
				$query = "update fm_cart_option set choice='n' where cart_seq in (".$str_cart_seq.")";
				$this->db->query($query);

				$query = "update fm_cart_option set choice='y' where cart_option_seq in (".$str_cart_option_seq.")";
				$this->db->query($query);
			}

			if(!$this->userInfo['member_seq']){
				$return_url = "/order/settle?mode=choice";
				$url = "/member/login?return_url=" . urlencode($return_url);
				if( $this->fammerceMode  || $this->storefammerceMode ) {
					pageLocation($url,'','parent');
				}
				else{
					pageLocation($url,'','top');
				}
				exit;
			}else{
				if( $this->fammerceMode  || $this->storefammerceMode ) {
					pageLocation('../order/settle?mode=choice','','parent');
				}
				else{
					pageLocation('../order/settle?mode=choice','','top');
				}
				exit;
			}

		}


		if(!$this->userInfo['member_seq']){
			$return_url = "/order/settle";
			$url = "/member/login?return_url=" . urlencode($return_url);

			if( $this->fammerceMode  || $this->storefammerceMode ) {
				pageLocation($url,'','parent');
			}
			else{
				pageLocation($url,'','top');
			}
			exit;
		}else{
			if( $this->fammerceMode  || $this->storefammerceMode ) {
				pageLocation('../order/settle','','parent');exit;
				echo("<script>parent.location.href='../order/settle?mode=".$mode."';</script>");
			}
			else{
				pageLocation('../order/settle','','top');exit;
				echo("<script>top.location.href='../order/settle?mode=".$mode."';</script>");
			}
		}
	}


	public function modify()
	{
		$seq = $_GET['seq'];
		$where[] = "cart_seq=?";
		$where_val[] = $seq;
		if(!($_POST['ea'][$seq]>=1)) $_POST['ea'][$seq] = 1;
		$query = "update fm_cart_option set ea='".$_POST['ea'][$seq]."' where ".implode(' and ',$where);
		$this->db->query($query,$where_val);
		pageReload('','parent');
	}

	public function del()
	{
		$this->load->model('cartmodel');
		if( !isset($_POST['cart_option_seq']) ){
			openDialogAlert("삭제할 상품이 없습니다.",400,140,'parent',"");
			exit;
		}

		foreach($_POST['cart_option_seq'] as $cart_option_seq){
			$this->cartmodel->delete_cart_option($cart_option_seq,'del');
		}

		openDialogAlert("장바구니 상품을 삭제하였습니다.",400,140,'parent',"parent.location.reload();");
	}

	/* 우측퀵메뉴 장바구니 삭제 추가 leewh 2014-06-09 */
	public function quickCartDel() {
		$this->load->model('cartmodel');
		$cart_option_seq = $_POST['cart_option_seq'];
		$msg="fail";

		if ($cart_option_seq) {
			$this->cartmodel->delete_cart_option($cart_option_seq,'del');
			$msg="ok";
		}

		echo $msg;
	}

	public function optional_changes()
	{
		$this->load->model('goodsmodel');
		$this->load->model('cartmodel');
		$this->load->model('categorymodel');
		$this->load->model('membermodel');
		$this->load->library('sale');

		$applypage					= 'view';
		$cart_option_seq			= (int) $_GET['no'];
		$data_member['group_seq']	= 0;
		$admin						= false;
		if	($cart['distribution'] == 'admin')	$admin = true;

		// 회원정보 가져오기
		if($this->userInfo){
			$data_member				= $this->membermodel->get_member_data($this->userInfo['member_seq']);
			$data_member['group_seq']	= (int) $data_member['group_seq'];
		}

		// 장바구니 정보
		$cart				= $this->cartmodel->get_cart_by_cart_option($cart_option_seq);
		// 장바구니 옵션
		$cart_options		= $this->cartmodel->get_cart_option_by_cart_option($cart_option_seq);
		// 전체 장바구니 조회
		$cart_list			= $this->cartmodel->catalog($admin);
		$goods_seq			= $cart['goods_seq'];
		// 상품정보
		$goods				= $this->goodsmodel->get_goods($goods_seq);
		// 상품옵션
		$options			= $this->goodsmodel->get_goods_option($goods_seq);
		// 상품추가옵션
		$suboptions			= $this->goodsmodel->get_goods_suboption($goods_seq);
		// 카테고리정보
		$categorys			= $this->goodsmodel->get_goods_category($goods_seq);
		if	($categorys) foreach($categorys as $key => $data_category){
			if	( $data_category['link'] == 1 ){
				$category_code	= $this->categorymodel->split_category($data_category['category_code']);
			}
		}
		// 상품이미지
		$images				= $this->goodsmodel->get_goods_image($goods_seq);
		$goods['image']		= $images[1]['thumbView']['image'];
		// 상품추가입력사항
		$inputs				= $this->goodsmodel->get_goods_input($goods_seq);
		// 장바구니 추가옵션
		$cart_suboptions	= $this->cartmodel->get_cart_suboption_by_cart_option($cart_option_seq);
		// 장바구니 추가입력사항
		$cart_inputs		= $this->cartmodel->get_cart_input_by_cart_option($cart_option_seq);
		// 적립금 설정
		$cfg_reserve		= ($this->reserves) ? $this->reserves : config_load('reserve');
		if	(!$this->config_system)	$this->config_system	= config_load('system');

		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['total_price']			= $cart_list['total'];
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		// 옵션 목록
		$cart_options	= array($cart_options);
		foreach($cart_options as $k => $cart_opt){

			//----> sale library 적용
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $cart_opt['consumer_price'];
			$param['price']						= $cart_opt['price'];
			$param['ea']						= 1;
			$param['category_code']				= $category_code;
			$param['goods_seq']					= $goods['goods_seq'];
			$param['goods']						= $goods;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);

			$cart_opt['price']					= $sales['result_price'];
			$this->sale->reset_init();
			//<---- sale library 적용

			$cart_options[$k] = $cart_opt;
		}

		foreach($options as $k => $opt){

			//----> sale library 적용
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $opt['consumer_price'];
			$param['price']						= $opt['price'];
			$param['ea']						= 1;
			$param['category_code']				= $category_code;
			$param['goods_seq']					= $goods['goods_seq'];
			$param['goods']						= $goods;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);
			$opt['price']						= $sales['result_price'];
			$options[$k]						= $opt;
			$this->sale->reset_init();
			//<---- sale library 적용

			/* 대표가격 */
			if	($opt['default_option'] == 'y'){
				$goods['price'] 			= $opt['price'];
				$goods['consumer_price'] 	= $opt['consumer_price'];
				$goods['reserve'] 			= $opt['reserve'];
				if	( $opt['option_title'] )
					$goods['option_divide_title']	= explode(',',$opt['option_title']);
				if	( $opt['newtype'] )
					$goods['divide_newtype']		= explode(',',$opt['newtype']);
			}
			$options[$k]['opt_join']				= implode('/',$optJoin);

			// 재고 체크
			$opt['chk_stock']	= check_stock_option($goods['goods_seq'],$opt['option1'],$opt['option2'],$opt['option3'],$opt['option4'],$opt['option5'],$opt['ea'],$cfg_order);
			if	( $opt['chk_stock'] )	$runout		= false;
			$options[$k]['chk_stock']	= $opt['chk_stock'];
		}

		// 추가구성옵션
		if($suboptions) foreach($suboptions as $key => $tmp){
			foreach($tmp as $k => $opt){
				$opt['chk_stock'] = check_stock_suboption($goods['goods_seq'],$opt['suboption_title'],$opt['suboption'],$ea,$cfg_order);
				if( $opt['chk_stock'] ){
					$sub_runout = true;
				}

				//----> sale library 적용
				unset($param, $sales);
				$param['option_type']				= 'suboption';
				$param['sub_sale']					= $opt['sub_sale'];
				$param['consumer_price']			= $opt['consumer_price'];
				$param['price']						= $opt['price'];
				$param['ea']						= 1;
				$param['category_code']				= $category_code;
				$param['goods_seq']					= $goods['goods_seq'];
				$param['goods']						= $goods;
				$this->sale->set_init($param);
				$sales								= $this->sale->calculate_sale_price($applypage);

				$opt['price']						= $sales['result_price'];
				$this->sale->reset_init();
				//<---- sale library 적용

				$suboptions[$key][$k] = $opt;
			}
		}

		$this->template->assign(array('config_system'=>$this->config_system));
		$this->template->assign(array('options'=>$options));
		$file = str_replace('optional_changes','_optional_changes',$this->template_path());
		$this->template->assign(array('cart'=>$cart));
		$this->template->assign(array('goods'=>$goods));
		$this->template->assign(array('suboptions'=>$suboptions));
		$this->template->assign(array('inputs'=>$inputs));
		$this->template->assign(array('cart_options'=>$cart_options));
		$this->template->assign(array('cart_suboptions'=>$cart_suboptions));
		$this->template->assign(array('cart_inputs'=>$cart_inputs));
		$this->template->define(array('LAYOUT'=>$file));
		$this->template->print_('LAYOUT');
	}

	public function optional_modify(){

		$this->load->model('cartmodel');
		$this->load->model('goodsmodel');

		if(!$_POST['suboptionTitle']) $_POST['suboptionTitle'] = array();
		foreach($_POST['suboption_title_required'] as $required_title){
			if( !in_array($required_title,$_POST['suboptionTitle']) ){
				openDialogAlert($required_title . " 옵션은 필수입니다.",400,140,'parent',"");
				exit;
			}
		}

		$cart_option_seq = (int) $_POST['cart_option_seq'];
		$data_cart = $this->cartmodel->get_cart_by_cart_option($cart_option_seq);

		$goods_seq = (int) $data_cart['goods_seq'];
		$inputs = $this->goodsmodel->get_goods_input($goods_seq);

		if( isset($_POST['inputsValue']) && is_array($_POST['inputsValue'][0])){
			// 2014-12-18 옵션 개편 후 (ocw)
			foreach($inputs as $key_input => $data_input){
				foreach($_POST['inputsValue'][0] as $k=>$v){
					if($data_input['input_require'] == 1 && !$_POST['inputsValue'][$key_input][$k]){
						openDialogAlert(addslashes($_POST['inputsTitle'][$key_input][$k]) . " 옵션은 필수입니다.",400,140,'parent',"");
						exit;
					}elseif($data_input['input_require'] == 1){
						$inputs_required = true;
					}
				}
			}
		}else{
			// 2014-12-18 옵션 개편 전 (ocw)
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
		}

		$this->cartmodel->delete_cart_option($cart_option_seq,'modify');
		$this->cartmodel->insert_cart_alloption($data_cart['cart_seq'],$inputs);
		openDialogAlert("장바구니 상품을 변경하였습니다.",400,140,'parent',"parent.location.reload();");
	}

	// 결제하기
	public function settle(){

		// 기본값 세팅
		$applypage		= 'order';
		$gift_categorys	= array();
		$gift_goods		= array();
		$members		= "";
		$mode			= "cart";
		$person_seq		= "";
		$mem_seq		= $_GET['member_seq'];
		if	( isset($_GET['mode']) )		$mode		= $_GET['mode'];
		if	( isset($_GET['person_seq']) )	$person_seq	= $_GET['person_seq'];

		// 기본 로드
		$this->load->model('couponmodel');
		$this->load->model('membermodel');
		if	($person_seq != "")	$this->load->model('personcartmodel');
		else					$this->load->model('cartmodel');
		$cfg['order']	= config_load('order');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');

		// 장바구니 선택 주문 시
		if	($mode == 'choice' && $_POST['cart_option_seq'] ){
			$str_cart_option_seq	= implode(',',$_POST['cart_option_seq']);

			$query		= "update fm_cart set distribution = 'choice' where cart_seq in (select cart_seq from fm_cart_option where cart_option_seq in (".$str_cart_option_seq."))";
			$this->db->query($query);
			$query		= "select cart_seq from fm_cart_option where cart_option_seq in (".$str_cart_option_seq.") ";
			$query		= $this->db->query($query);
			foreach($query->result_array() as $cart_option_data){
				$r_cart_seq[]	= $cart_option_data['cart_seq'];
			}

			$str_cart_seq	= implode(',',$r_cart_seq);
			if	($str_cart_seq){
				$query		= "update fm_cart_option set choice='n' where cart_seq in (".$str_cart_seq.") ";
				$this->db->query($query);
				$query		= "update fm_cart_option set choice='y' where cart_option_seq in (".$str_cart_option_seq.")";
				$this->db->query($query);
			}
		}

		// 바로구매 시 장바구니 정리
		if	($mode == "direct")		$this->cartmodel->delete_for_settle();
		$cart_promotioncode		= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
		$this->template->assign('cart_promotioncode' , $cart_promotioncode);

		if	($this->userInfo['member_seq']){
			$members			= $this->membermodel->get_member_data($this->userInfo['member_seq']);
			$tmp				= explode('-',$members['phone']);
			foreach($tmp as $k => $data){
				$key			= 'phone'.($k+1);
				$members[$key]	= $data;
			}

			$tmp				= explode('-',$members['cellphone']);
			foreach($tmp as $k => $data){
				$key			= 'cellphone'.($k+1);
				$members[$key]	= $data;
			}

			$tmp				= explode('-',$members['zipcode']);
			foreach($tmp as $k => $data){
				$key			= 'zipcode'.($k+1);
				$members[$key]	= $data;
			}
		}

		if	($person_seq != "")		$cart	= $this->personcartmodel->catalog($mem_seq, $person_seq);
		else						$cart	= $this->cartmodel->catalog();
		if	( !$cart['list'] ){
			pageLocation('../main/index','구매할 상품이 없습니다.');
			exit;
		}

		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['total_price']			= $cart['total'];
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		$cart['total']			= 0;
		$cart['total_ea']		= 0;
		$cart['total_sale']		= 0;
		$cart['total_price']	= 0;
		$cart['total_reserve']	= 0;
		$cart['total_point']	= 0;
		$category				= array();
		$goodscancellation		= false;
		$possible_pay			= array();
		$is_coupon				= false;
		$is_goods				= false;
		foreach($cart['list'] as $key => $data){

			// 실물, 쿠폰 상품의 존재여부
			if	( $data['goods_kind'] == 'coupon' )	$is_coupon			= true;
			else									$is_goods			= true;

			// 청약철회상품
			if	($data['cancel_type'] == 1)			$goodscancellation	= true;

			// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
			if( $data['event']['event_goodsStatus'] === true ){
				$err_msg	= '↓아래 상품은 단독이벤트 기간에만 구매가 가능합니다.';
				$err_msg	.= '\\n'.$data['goods_name'];
				pageBack($err_msg);
				exit;
			}

			// 쿠폰상품 구매가능 여부 체크
			if	($data['goods_kind'] == 'coupon') {
				if	($data['cart_option_seq']){
					// 쿠폰상품 기간체크
					$chkcouponexpire	= check_coupon_date_option($data['goods_seq'], $data['option1'], $data['option2'], $data['option3'], $data['option4'], $data['option5']);
					if	( $chkcouponexpire['couponexpire'] === false ){
						$this->cartmodel->delete_option($data['cart_option_seq'],'');	//해당상품의 옵션제거
						$err_msg	= "↓아래 쿠폰상품의 유효기간은 ".$chkcouponexpire['social_start_date']." ~ ".$chkcouponexpire['social_end_date']." 입니다.";
						$err_msg	.= "\\n".$data['goods_name'];
						if	($opttitle) $err_msg .= "(".$opttitle.")";
						pageBack($err_msg);
						exit;
					}
				}

				if	($data['cart_suboption_seq']){
					// 쿠폰상품 기간체크
					$chkcouponexpire	= check_coupon_date_suboption($data['goods_seq'], $data['suboption_title'], $data['suboption']);
					if	( $chkcouponexpire['couponexpire'] === false ){
						$this->cartmodel->delete_option($data['cart_suboption_seq'],'');//해당상품의 옵션제거
						$err_msg	= "↓아래 쿠폰상품의 유효기간은 ".$chkcouponexpire['social_start_date']." ~ ".$chkcouponexpire['social_end_date']." 입니다.";
						$err_msg	.= "\\n".$data['goods_name'];
						if	($opttitle) $err_msg .= "(".$opttitle.")";
						pageBack($err_msg);
						exit;
					}
				}
			}

			list($price,$data['reserve']) = $this->goodsmodel->get_goods_option_price($data['goods_seq'], $data['option1'], $data['option2'], $data['option3'], $data['option4'], $data['option5']);

			// 재고 체크
			$chk	= check_stock_option($data['goods_seq'], $data['option1'], $data['option2'], $data['option3'], $data['option4'], $data['option5'], $data['ea'], $cfg['order'], 'view_stock');
			if	( $chk['stock'] < 0 ){
				$opttitle	= '';
				if	($data['option1'])	$opttitle	.= $data['option1'];
				if	($data['option2'])	$opttitle	.= ' '.$data['option2'];
				if	($data['option3'])	$opttitle	.= ' '.$data['option3'];
				if	($data['option4'])	$opttitle	.= ' '.$data['option4'];
				if	($data['option5'])	$opttitle	.= ' '.$data['option5'];

				$err_msg	= "↓아래 상품의 재고는 ".$chk['sale_able_stock']."개 입니다.";
				$err_msg	.= "\\n".$data['goods_name'];
				if	($opttitle)	$err_msg	.= "(".$opttitle.")";
				pageBack($err_msg);
				exit;
			}

			// 추가옵션 재고 체크
			if($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $k => $cart_suboption){
					$chk	= check_stock_suboption($data['goods_seq'], $cart_suboption['suboption_title'], $cart_suboption['suboption'], $cart_suboption['ea'], $cfg['order'], 'view_stock');
					if	( $chk['stock'] < 0 ){
						$opttitle	= '';
						if	($cart_suboption['suboption'])	$opttitle	.= $cart_suboption['suboption'];
						$err_msg	= "↓아래 상품의 재고는 ".$chk['sale_able_stock']."개 입니다.";
						$err_msg	.= "\\n".$data['goods_name'];
						if	($opttitle)	$err_msg	.= "(".$opttitle.")";
						pageBack($err_msg);
						exit;
					}
				}
			}

			if	( $this->_is_mobile_agent) {	// $this->mobileMode  ||
				if	($data['possible_mobile_pay']){
					$possible_pay[]	= explode(",", $data['possible_mobile_pay']);
				}
			}else{
				if	($data['possible_pay']){
					$possible_pay[]	= explode(",", $data['possible_pay']);
				}
			}


			// 해당 상품의 전체 카테고리
			$category	= array();
			$tmp		= $this->goodsmodel->get_goods_category($data['goods_seq']);
			foreach($tmp as $row)	$category[]		= $row['category_code'];

			//----> sale library 적용
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $data['consumer_price'];
			$param['price']						= $data['org_price'];
			$param['sale_price']				= $data['price'];
			$param['ea']						= $data['ea'];
			$param['goods_ea']					= $cart['data_goods'][$data['goods_seq']]['ea'];
			$param['category_code']				= $category;
			$param['goods_seq']					= $data['goods_seq'];
			$param['goods']						= $data;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);

			$data['org_price']					= ($data['consumer_price']) ? $data['consumer_price'] : $data['org_price'];
			$data['sales']						= $sales;
			$data['event_order_cnt']			= $data['event']['event_order_cnt'];
			$data['tot_org_price']				= $data['org_price'] * $data['ea'];
			$data['tot_sale_price']				= $sales['total_sale_price'];
			$data['tot_result_price']			= $sales['result_price'];

			// sale_price가 없을 시 여기서 event까지 계산함
			if	(!$data['event'] && $this->sale->cfgs['event']){
				$data['event']					= $this->sale->cfgs['event'];
				$data['eventEnd']				= $sales['eventEnd'];
			}

			// 적립금 / 포인트 ( 실결제 금액 기준이기에 여기서 계산 )
			// 상품의 적립금 / 포인트 계산
			$data['reserve']	= $this->goodsmodel->get_reserve_with_policy($data['reserve_policy'],
									$sales['result_price'], $cfg_reserve['default_reserve_percent'],
									$data['reserve_rate'], $data['reserve_unit'], $data['reserve']);
			$data['point']		= (int) $this->goodsmodel->get_point_with_policy($sales['result_price']);
			// 이벤트 적립금 / 포인트 계산 ( 장바구니에서 혜택적용할인가를 받아서 쓸 경우만 )
			if	($param['sale_price']){
				$this->sale->cfgs['event']		= $data['event'];
				$data['reserve']				+= $this->sale->event_sale_reserve($sales['result_price']);
				$data['point']					+= $this->sale->event_sale_point($sales['result_price']);
			}
			$data['reserve']					= $data['reserve'] + $sales['tot_reserve'];
			$data['point']						= $data['point'] + $sales['tot_point'];

			// 총 합계
			$cart['total']						+= $data['price']*$data['ea'];
			$cart['total_ea']					+= $data['ea'];
			$cart['total_price']				+= $sales['result_price'];
			$cart['total_reserve']				+= $data['reserve'];
			$cart['total_point']				+= $data['point'];
			$cart['total_sale']					+= $sales['total_sale_price'];

			// 총 할인 목록 노출을 위한 배열 생성
			if	($sales['title_list'])foreach($sales['title_list'] as $sale_type => $sale_title){
				$data['tsales']['sale_list'][$sale_type]		= $sales['sale_list'][$sale_type];
				$cart['total_sale_list'][$sale_type]['title']	= $sale_title;
				$cart['total_sale_list'][$sale_type]['price']	+= $sales['sale_list'][$sale_type];
			}
			$cart['total_sale_list']['shippingcoupon']['title']	= '배송비쿠폰';
			$cart['total_sale_list']['shippingcoupon']['price']	= 0;
			$cart['total_sale_list']['shippingcode']['title']	= '배송비코드';
			$cart['total_sale_list']['shippingcode']['price']	= 0;

			$this->sale->reset_init();
			//<---- sale library 적용

			// 추가구성 옵션
			if	($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $k => $subdata){
					//----> sale library 적용
					unset($param, $sales);
					$param['option_type']				= 'suboption';
					$param['sub_sale']					= $subdata['sub_sale'];
					$param['consumer_price']			= $subdata['consumer_price'];
					$param['price']						= $subdata['price'];
					$param['sale_price']				= $subdata['price'];
					$param['ea']						= $subdata['ea'];
					$param['goods_ea']					= $cart['data_goods'][$data['goods_seq']]['ea'];
					$param['category_code']				= $category;
					$param['goods_seq']					= $data['goods_seq'];
					$param['goods']						= $data;
					$this->sale->set_init($param);
					$sales								= $this->sale->calculate_sale_price($applypage);

					$subdata['sales']					= $sales;
					$subdata['org_price']				= ($subdata['consumer_price']) ? $subdata['consumer_price'] : $subdata['price'];

					// 적립금 / 포인트
					$subdata['reserve']					= $this->goodsmodel->get_reserve_with_policy($subdata['reserve_policy'], $sales['result_price'], $cfg_reserve['default_reserve_percent'], $subdata['reserve_rate'], $subdata['reserve_unit'], $subdata['reserve']);
					$subdata['point']					= $this->goodsmodel->get_point_with_policy($sales['result_price']);
					$subdata['reserve']					= $subdata['reserve'] + $sales['tot_reserve'];
					$subdata['point']					= $subdata['point'] + $sales['tot_point'];

					$data['tot_org_price']				+= $subdata['org_price'] * $subdata['ea'];
					$data['tot_sale_price']				+= $sales['total_sale_price'];
					$data['tot_result_price']			+= $sales['result_price'];

					$cart['total']						+= $subdata['price']*$subdata['ea'];
					$cart['total_ea']					+= $subdata['ea'];
					$cart['total_price']				+= $sales['result_price'];
					$cart['total_reserve']				+= $subdata['reserve'];
					$cart['total_point']				+= $subdata['point'];
					$cart['total_sale']					+= $sales['total_sale_price'];
					if	($sales['title_list'])foreach($sales['title_list'] as $sale_type => $sale_title){
						$data['tsales']['sale_list'][$sale_type]		+= $sales['sale_list'][$sale_type];
						$cart['total_sale_list'][$sale_type]['title']	= $sale_title;
						$cart['total_sale_list'][$sale_type]['price']	+= $sales['sale_list'][$sale_type];
					}
					$this->sale->reset_init();
					//<---- sale library 적용

					$data['cart_suboptions'][$k]	= $subdata;
				}
			}

			$cart['list'][$key]		= $data;
			if	($data['shipping_policy'] == 'goods')
				$data['shipping_policy']	.= '_'.$data['cart_seq'];
			$group_key				= $data['goods_type'].'|'.$data['shipping_policy'];
			$shipping_cart_list[$group_key][]				= $data;
			$shipping_cart_list[$group_key][0]['rowspan']	+= count($data['cart_suboptions']) + 1;
		}

		$cart['total_price']	+= (int)array_sum($cart['shipping_price']);

		### GIFT
		foreach($cart['data_goods'] as $goods_seq => $data){
			$gift_goods[] 		= $goods_seq;
			$gift['goods_seq']	= $goods_seq;
			$gift['ea']			= $data['ea'];
			$gift['tot_price']	= $data['price'];
			$gift_loop[]		= $gift;
			foreach($data['r_category'] as $category_code){
				$gift_categorys[] = $category_code;
				$category[] = $category_code;
			}
		}

		$shipping = use_shipping_method();

		// 해외배송불가카테고리 체크
		foreach($shipping[1] as $i=>$row){
			foreach($row['exceptCategory'] as $exceptCategory){
				if(in_array($exceptCategory,$category)){
					unset($shipping[1][$i]);
				}
			}
		}
		if(!count($shipping[1])) unset($shipping[1]);

		if( is_array($shipping) ) {
			foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
		}

		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;

		if($shipping_policy['count']==0 && $this->config_system['service']['code']!='P_STOR'){
			pageBack("배송방법이 존재하지 않습니다.\\n\\n쇼핑몰 고객센터에 문의 해 주세요.");
			exit;
		}

		$bank = $payment = $escrow = "";
		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
			if( $v['accountUse'] == 'y' ){
				$payment['bank'] = true;
				if(count($possible_pay) > 0){
					foreach($possible_pay as $payData){
						if(!in_array('bank', $payData)){
							$payment['bank'] = false;
						}
					}
				}

			}
		}
		if( $this->config_system['pgCompany'] ){
			$payment_gateway = config_load($this->config_system['pgCompany']);
			$payment_gateway['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');

			foreach($payment_gateway['arrKcpCardCompany'] as $k => $v){
				$payment_gateway['arrCardCompany'][$v['codecd']]=$v['value'];
			}
			/*
			if(isset($payment_gateway['payment'])) foreach($payment_gateway['payment'] as $k => $v){
				$payment[$v] = true;
				if(count($possible_pay) > 0){
					foreach($possible_pay as $payData){
						if(!in_array($v, $payData)){
							$payment[$v] = false;
						}
					}
				}
			}
			*/

			if( $this->_is_mobile_agent) { // 20130104 $this->mobileMode  ||
				$pg_var = "mobilePayment";
				$escrowpg_var = "mobileEscrow";
				$escrowAccountLimit ='mobileEscrowAccountLimit';
				$escrowVirtualLimit ='mobileEscrowVirtualLimit';
			}else{
				$pg_var = "payment";
				$escrowpg_var = "escrow";
				$escrowAccountLimit ='escrowAccountLimit';
				$escrowVirtualLimit ='escrowVirtualLimit';
			}

			if(isset($payment_gateway[$pg_var])) foreach($payment_gateway[$pg_var] as $k => $v){
				$payment[$v] = true;
				if(count($possible_pay) > 0){
					foreach($possible_pay as $payData){
						if(!in_array($v, $payData)){
							$payment[$v] = false;
						}
					}
				}
			}

			if(isset($payment_gateway[$escrowpg_var])) foreach($payment_gateway[$escrowpg_var] as $k => $v){
				if($v == 'account'){
					if( $cart['total_price'] >= $payment_gateway[$escrowAccountLimit] ) {
						$escrow[$v] = true;
						$escrow_view = true;
						if(count($possible_pay) > 0){
							foreach($possible_pay as $payData){
								if(!in_array("escrow_".$v, $payData)){
									$escrow[$v] = false;
									$escrow_view = false;
								}
							}
						}

					}else {
						$escrow[$v] = false;
					}
				}

				if($v == 'virtual'){
					if( $cart['total_price'] >= $payment_gateway[$escrowVirtualLimit] ) {
						$escrow[$v] = true;
						$escrow_view = true;
						if(count($possible_pay) > 0){
							foreach($possible_pay as $payData){
								if(!in_array("escrow_".$v, $payData)){
									$escrow[$v] = false;
									$escrow_view = false;
								}
							}
						}
					}else {
						$escrow[$v] = false;
					}
				}
			}
		}

		$payment_count = 0;

		// 카카오페이 추가 :: 2015-02-11 lwh
		if( $this->config_system['not_use_kakao'] == 'n' ){
			$payment['kakaopay']	= true;
			require("./pg/kakaopay/conf_inc.php");

			$kakaopay_config['CNSPAY_WEB_SERVER_URL']	= $CNSPAY_WEB_SERVER_URL;
			$kakaopay_config['targetUrl']				= $targetUrl;
			$kakaopay_config['msgName']					= $msgName;
			$kakaopay_config['CnsPayDealRequestUrl']	= $CnsPayDealRequestUrl;

			$this->template->assign($kakaopay_config);

			// 자주 변경 될수 있으므로 스킨에서 제외 요청 :: 2015-03-11 lwh
			$kakaopay_html	= "<div style='border:1px solid #CBCBCB; padding:5px; width:95%;'>카카오톡 앱에서 카카오페이 가입(최초 1회 본인명의휴대폰에서 본의명의 카드등록) 후 비밀번호 입력만으로 간편하고 안전하게 결제하실 수 있는 모바일 결제수단입니다.</div>";
			
			$this->template->assign('kakaopay_html',$kakaopay_html);

			$payment_count++;
		}
		
		foreach($payment as $v) if($v) $payment_count++;
		foreach($escrow as $v)  if($v) $payment_count++;

		if($payment_count==0){
			pageBack("결제방법이 존재하지 않습니다.\\n\\n쇼핑몰 고객센터에 문의 해 주세요.");
			exit;
		}

		if( $this->_is_mobile_agent) {//$this->mobileMode  ||
			$this->template->assign('mobile',1);
		}

		// 사업자 회원일 경우 업체명->이름, 사업장주소->주소, 담당자전화번호->전화번호, 핸드폰->핸드폰
		if($members['business_seq']){
			//$members['user_name'] = $members['bname'];
			$members['address_type']		= $members['baddress_type'];
			$members['address']				= $members['baddress'];
			$members['address_street']	= $members['baddress_street'];
			$members['address_detail']	= $members['baddress_detail'];
			$tmp = explode('-',$members['bphone']);
			foreach($tmp as $k => $data){
				$key = 'phone'.($k+1);
				$members[$key] = $data;
			}

			$tmp = explode('-',$members['bcellphone']);
			foreach($tmp as $k => $data){
				$key = 'cellphone'.($k+1);
				$members[$key] = $data;
			}

			$tmp = explode('-',$members['bzipcode']);
			foreach($tmp as $k => $data){
				$key = 'zipcode'.($k+1);
				$members[$key] = $data;
			}
		}

		if($person_seq == ""){
			if( defined('__ISUSER__') != true ) {
				$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
				$member = config_load('member');
				$privacy['privacy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['privacy']));

				//비회원 개인정보 수집-이용 약관동의 추가
				$privacy['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));
			}
		}
		if( $goodscancellation  === true ) { //청약철회상품이 있는경우
			$arrOrder = config_load('order');
			$privacy['cancellation'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$arrOrder['cancellation']));
		}

		$this->template->assign($privacy);

		//개인결제시
		if($person_seq != ""){

			$query = $this->db->query("select * from fm_person where person_seq='".$person_seq."'");
			$res = $query->row_array();

			if($this->userInfo['member_seq'] != $res['member_seq']){
				echo "<script>alert('결제 권한이 없습니다.'); history.back();</script>";
				exit;
			}

			$members['user_name'] = $res['order_user_name'];
			$members['email'] = $res['order_email'];
			$tmp = explode('-',$res['order_phone']);
			foreach($tmp as $k => $data){
				$key = 'phone'.($k+1);
				$members[$key] = $data;
			}

			$tmp = explode('-',$res['order_cellphone']);
			foreach($tmp as $k => $data){
				$key = 'cellphone'.($k+1);
				$members[$key] = $data;
			}

			if(strpos($res["pay_type"], 'bank') === false){
				$payment["bank"] = '';
			}else{
				$payment["bank"] = 'bank';
			}

			if(strpos($res["pay_type"], 'card') === false){
				$payment["card"] = '';
			}else{
				$payment["card"] = 'card';
			}

			// 카카오 페이로 인한 추가 :: 2015-02-11 lwh
			if(strpos($res["pay_type"], 'kakaopay') === false){
				$payment["kakaopay"] = '';
			}else{
				$payment["kakaopay"] = 'kakaopay';
			}

			if(strpos($res["pay_type"], 'account') === false){
				$payment["account"] = '';
			}else{
				$payment["account"] = 'account';
			}

			if(strpos($res["pay_type"], 'cellphone') === false){
				$payment["cellphone"] = '';
			}else{
				$payment["cellphone"] = 'cellphone';
			}

			if(strpos($res["pay_type"], 'virtual') === false){
				$payment["virtual"] = '';
			}else{
				$payment["virtual"] = 'virtual';
			}
			$cart['total_price']	= $cart['total_price'] - $res['enuri'];
			$cart['total_sale']		+= (int) $res['enuri'];

			$this->template->assign('enuri',$res['enuri']);
			$this->template->assign('personData',$res);
		}else{

			### GIFT

			$gift = $this->ordermodel->get_gift_event($gift_categorys, $gift_goods, $gift_loop, $cart['total']);
			$this->template->assign(array('gift_cnt'=>$gift['gift_cnt'],'gloop'=>$gift['gloop']));


		}

		//$business_info = $this->membermodel->get_business_info($this->userInfo['member_seq']);
		//print_r($business_info);
		if($members["bzipcode"]) $business_info["co_zipcode"] = explode("-",$members["bzipcode"]);

		$business_info["bname"] = $members["bname"];
		$business_info["bno"] = $members["bno"];
		$business_info["bCEO"] = $members["bceo"];
		// 거꿀로 저장되어 업태/업종 변경
		$business_info["bstatus"] = $members["bitem"];
		$business_info["bitem"] = $members["bstatus"];

		$business_info["bperson"] = $members["bperson"];
		$business_info["email"] = $members["email"];
		$business_info["bphone"] = ($members["bphone"])? str_replace("-","",$members["bphone"]) : "";
		$business_info["baddress1"] = ($members["baddress_type"] == 'street')?$members["baddress_street"]:$members["baddress"];
		$business_info["baddress2"] = $members["baddress_detail"];
		$business_info["baddress_type"] = $members["baddress_type"];
		$business_info["baddress_street"]			= $members["baddress_street"];

		$cfg['order'] = config_load('order');


		//좋아요 할인 혜택구분 $cfg['order']['fblike_ordertype'] ->0 회원/비회원, 1 회원만 할인제공
		$session_arr = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];

		$cfg['order']['fblike_ordertype'] = ( ($cfg['order']['fblike_ordertype'] == 1 && $session_arr['member_seq'] ) || ($cfg['order']['fblike_ordertype'] != 1) ) ?1:0;

		if($cfg['order']['biztype'] == 'tax_exempt'){
			$cfg['order']['taxuse'] = "0";
		}else{
			$cfg['order']['taxuse'] = 1;
		}

		if(!$this->config_system['pgCompany']){
			$cfg['order']['cashreceiptuse'] = 0;
		}


		$this->template->assign('cfg',$cfg);
		$this->template->assign('pg_company',$this->config_system['pgCompany']);
		$this->template->assign('mode',$mode);
		$this->template->assign('members',$members);
		$this->template->assign('business_info',$business_info);
		$this->template->assign('cart_list',$cart['list']);
		$this->template->assign('shipping_cart_list',$shipping_cart_list);
		$this->template->assign('promocodeSale',$cart['promocodeSale']);
		$this->template->assign('shipping_price',$cart['shipping_price']);
		$this->template->assign('shipping_company_cnt',$cart['shipping_company_cnt']);
		$this->template->assign('provider_shipping_policy',$cart['provider_shipping_policy']);
		$this->template->assign('provider_shipping_price',$cart['provider_shipping_price']);
		$this->template->assign('total',$cart['total']);
		$this->template->assign('total_ea',$cart['total_ea']);
		$this->template->assign('total_reserve',$cart['total_reserve']);
		$this->template->assign('total_point',$cart['total_point']);
		$this->template->assign('total_sale',$cart['total_sale']);
		$this->template->assign('total_sale_list',$cart['total_sale_list']);
		$this->template->assign('total_price',$cart['total_price']);
		$this->template->assign('shipping_policy',$shipping_policy);
		$this->template->assign('bank',$bank);
		$this->template->assign('payment',$payment);
		$this->template->assign('escrow',$escrow);
		$this->template->assign('escrow_view',$escrow_view);
		$this->template->assign('settle',true);//현재페이지정보 넘겨줌
		$this->template->assign('settlepage',true);//현재페이지정보 넘겨줌
		$this->template->assign('total_ea',$cart['total_ea']);
		$this->template->assign('data_goods',$cart['data_goods']);

		$this->template->assign('is_coupon',$is_coupon);
		$this->template->assign('is_goods',$is_goods);

		//네이버 마일리지 사용여부 가져오기
		$this->load->model('navermileagemodel');
		$this->template->assign('naver_mileage_yn',$this->navermileagemodel->cfg_naver_mileage['naver_mileage_yn']);


		// 상품상태별 아이콘
		$tmp = code_load('goodsStatusImage');
		$goodsStatusImage = array();
		foreach($tmp as $row){
			$goodsStatusImage[$row['codecd']] = $row['value'];
		}
		$this->template->assign(array('goodsStatusImage'=>$goodsStatusImage));

		if($this->userInfo['member_seq']){

			// 최근배송지 5개 로딩
			$lately_delivery_address = $this->membermodel->get_delivery_address($this->userInfo['member_seq'],'lately',0,5);
			$this->template->assign('lately_delivery_address',$lately_delivery_address);

			//쿠폰보유건
			unset($sc);
			$sc['today']			= date('Y-m-d',time());
			$dsc['whereis'] = " and member_seq=".$this->userInfo['member_seq']." and use_status='unused' AND ( (issue_startdate is null  AND issue_enddate is null ) OR (issue_startdate <='".$sc['today']."' AND issue_enddate >='".$sc['today']."') )";//사용가능한
			$member_usable_coupons = $this->couponmodel->get_download_total_count($dsc);
			$this->template->assign('member_usable_coupons',$member_usable_coupons);
		}

		//네이버 마일리지 출력용 스크립트
		$this->navermileagemodel->display_view($checkadd);

		$this->print_layout($this->template_path());

		## IE가 아닐때 결제모듈 설치 확인
		if($this->config_system['pgCompany'] && !$this->_is_mobile_agent){

			## 접속 브라우저 확인 IE/기타.
			$userAgenr = getBrowser();
			if( $userAgenr['nickname'] != "MSIE"){
				$this->pg_install_check($this->config_system['pgCompany']);
			}
		}
	}

	## pg사별 크로스브라우징 플러그인 설치여부 확인.
	public function pg_install_check($pgCompany){

		$pg			= config_load($pgCompany);
		$CST_PLATFORM = "service";
		$CST_MID	  = $pg['mallCode'];
		$LGD_MID	  = $pg['mallCode'];
		$LGD_OID	  = $pg['mallCode'];

		switch($pgCompany){
			case "lg":
				$url = "http";
				if($_SERVER['HTTPS'] == 'on') $url .= "s";
				$url .= "://xpay.uplus.co.kr/xpay/js/xpay_install_utf-8.js";
				echo '<script type="text/javascript" src="'.$url.'" type="text/javascript"></script>';
				echo '
				<script language = "javascript" type="text/javascript">
				function doPay_ActiveX(){
					https_flag = true;
					if(hasXpayObject() == false) { xpayShowInstall(); }
				}
				doPay_ActiveX();
				</script>';
			break;
			case "allat";
				echo '<script language=JavaScript charset="euc-kr" src="https://tx.allatpay.com/common/AllatPayRE.js?dummy=131018"></script>';
				echo '
				<script language = "javascript" type="text/javascript">
				$(function() { $("#AllatLayer1").css("position","fixed");});
				</script>';
			break;
		}
	}

	public function settle_coupon()
	{
		if( isset($_GET['mode']) ) $mode = $_GET['mode'];
		else $mode = "cart";
		$this->displaymode = 'coupon';
		$this->calculate();
	}

	// 각종 할인 할인 금액 계산, 배송배 계산 및 주문금액 계산
	public function calculate($adminOrder="", $person_seq=''){

		$this->load->helper('coupon');
		$this->load->model('cartmodel');
		$this->load->model('couponmodel');
		$this->load->model('membermodel');
		$this->load->model('configsalemodel');

		// 기본값 정의
		$applypage						= 'order';
		if(!$adminOrder){
			$adminOrder						= ($_GET["adminOrder"]) ? $_GET["adminOrder"] : '';
		}
		$person_seq						= ($_GET["person_seq"]) ? $_GET["person_seq"] : '';
		if	(!$person_seq && $_POST['person_seq'])	$person_seq	= $_POST['person_seq'];
		## 모바일결제 : 체크값 오류시 callback으로 결제창 layer 숨김 처리
		$pg_cancel_script				= ($_POST['mobilenew'] == "y") ? $this->pg_cancel_script() : '';
		$members						= '';		//회원정보
		$err_reserve					= '';		//에러메세지
		$total_price					= 0;
		$total_reserve					= 0;
		$total_point					= 0;
		$goods_weight					= 0;
		$sum_goods_price				= 0;
		$total_coupon_sale				= 0;
		$total_fblike_sale				= 0;
		$total_mobile_sale				= 0;
		$total_goods_price				= 0;
		$total_member_sale				= 0;
		$total_real_sale_price			= 0;
		$international_shipping_price	= 0;
		$total_price_for_shop_delivery	= 0;		// 기본배송 상품 합계금액
		$scripts						= array();
		$cfg_reserve					= ($this->reserves)?$this->reserves:config_load('reserve');
		$pg								= config_load($this->config_system['pgCompany']);
		$cfg['order']					= config_load('order');
		$shipping						= use_shipping_method();
		$this->shipping_order			= $shipping;
		if	(is_array($shipping))
			$international_shipping		= $shipping[1][$_POST['shipping_method_international']];

		// 회원정보 추출
		if	($adminOrder == "admin" && $_POST['member_seq']){
			$members					= $this->membermodel->get_member_data($_POST['member_seq']);
		}elseif	($this->userInfo['member_seq']){
			$members					= $this->membermodel->get_member_data($this->userInfo['member_seq']);
		}

		// 장바구니 정보 추출
		if($person_seq == ""){
			$this->load->model('cartmodel');
			$cart	= $this->cartmodel->catalog($adminOrder);
		}else{
			$this->load->model('personcartmodel');
			$cart	= $this->personcartmodel->catalog($this->userInfo['member_seq'], $person_seq);
		}

		// 구매 시 적립금액 제한 조건 추가 leewh 2014-06-25
		if	($cfg_reserve['default_reserve_limit'] >= 2){
			if	(isset($_POST['appointed_reserve'])){
				$appointed_reserve	= $_POST['appointed_reserve'];
			}
			if	($cfg_reserve['default_reserve_limit'] == 3){
				unset($cal_total_real_sale_price);
				if	(isset($_POST['total_real_sale_price'])){
					$cal_total_real_sale_price	= $_POST['total_real_sale_price'];
				}
				$tot_using_reserve	= 0; // 상품 사용적립금
				if	($_POST['emoney'] > 0) {
					// 총 사용 적립금 재정의 총 상품실결제금액보다 총 결제금액이 클 경우
					if	($cal_total_real_sale_price < $_POST['emoney']){
						$tot_using_reserve	= $cal_total_real_sale_price;
					}else{
						$tot_using_reserve	= $_POST['emoney'];
					}
				}
			}
		}

		/**** 재고 체크 및 최대/최소 구매수량 체크 ****/
		foreach($cart['data_goods'] as $goods_seq => $data){

			// 구매수량 체크
			if($data['min_purchase_ea'] && $data['min_purchase_ea'] >  $data['ea']){
				openDialogAlert($data['goods_name'].'은 '.$data['min_purchase_ea'].'개 이상 구매하셔야 합니다.',400,140,'parent',$pg_cancel_script);
				exit;
			}
			if($data['max_purchase_ea'] && $data['max_purchase_ea'] < $data['ea']){
				openDialogAlert($data['goods_name'].'은 '.$data['max_purchase_ea'].'개 이상 구매하실 수 없습니다.',400,140,'parent',$pg_cancel_script);
				exit;
			}

			// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
			if( $data['event']['event_goodsStatus'] === true ){
				$err_msg = "↓아래 상품은 단독이벤트 기간에만 구매가 가능합니다.";
				$err_msg .= "\\n".$data['goods_name'];
				openDialogAlert($err_msg,400,140,'parent',$pg_cancel_script);
				exit;
			}

			if($data['ea_for_option'])foreach($data['ea_for_option'] as $option_key => $option_ea){
				$option_r = explode(';',$option_key);
				// 재고 체크
				$chk = check_stock_option(
					$goods_seq,
					$option_r[0],
					$option_r[1],
					$option_r[2],
					$option_r[3],
					$option_r[4],
					$option_ea,
					$cfg['order'],
					'view_stock'
				);

				if( $chk['stock'] < 0 ){
					$opttitle = '';
					if($option_r[0]) $opttitle .= $option_r[0];
					if($option_r[1]) $opttitle .= ' '.$option_r[1];
					if($option_r[2]) $opttitle .= ' '.$option_r[2];
					if($option_r[3]) $opttitle .= ' '.$option_r[3];
					if($option_r[4]) $opttitle .= ' '.$option_r[4];

					$err_msg = "↓아래 상품의 재고는 ".$chk['sale_able_stock']."개 입니다.";
					$err_msg .= "<br/>".$data['goods_name'];
					if($opttitle) $err_msg .= "(".$opttitle.")";
					openDialogAlert($err_msg,400,140,'parent',$pg_cancel_script);
					exit;
				}
			}

			if($data['ea_for_suboption']) foreach($data['ea_for_suboption'] as $option_key => $option_ea){
				$option_r = explode(';',$option_key);
				// 재고 체크
				$chk = check_stock_suboption(
					$goods_seq,
					$option_r[0],
					$option_r[1],
					$option_ea,
					$cfg['order'],
					'view_stock'
				);

				if( $chk['stock'] < 0 ){
					$opttitle = '';
					if($option_r[1]) $opttitle .= $option_r[1];
					$err_msg = "↓아래 상품의 재고는 ".$chk['sale_able_stock']."개 입니다.";
					$err_msg .= "<br/>".$data['goods_name'];
					if($opttitle) $err_msg .= "(".$opttitle.")";
					openDialogAlert($err_msg,400,140,'parent',$pg_cancel_script);
					exit;
				}
			}
		}
		/* **************************************************** */


		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['total_price']			= $cart['total'];
		$param['reserve_cfg']			= $cfg_reserve;
		$param['tot_use_emoney']		= $tot_using_reserve;

		/* 관리자 수동 비회원 주문인데 할인 적용 오류로 추가 leewh 2015-01-16*/
		$param['member_seq']			= ($adminOrder != "admin") ? $this->userInfo['member_seq'] : '';
		$param['group_seq']				= ($adminOrder != "admin") ? $this->userInfo['group_seq'] : '';
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		if	($this->displaymode != 'coupon')
			echo '<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>';
		$scripts[]	= "<script type='text/javascript'>";
		$scripts[]	= "$(function() {";

		// 총 판매가
		foreach($cart['list'] as $key => $data){

			// 초기값
			$category				= ($data['r_category']) ? $data['r_category'] : array();
			$data['ori_price']		= $data['price'];
			$cart_suboptions		= $data['cart_suboptions'];
			$cart_inputs			= $data['cart_inputs'];
			$coupon_download_seq	= $_POST['coupon_download'][$data['cart_seq']][$data['cart_option_seq']];

			// 쿠폰 사용 팝업에서 전체 쿠폰 추출
			if	( $members && $person_seq == "" && $this->displaymode == 'coupon'){
				$coupons			= $this->couponmodel->get_able_use_list($members['member_seq'],$data['goods_seq'],$category, $cart['total'], $data['price'], $data['ea']);
				$data['coupons']	= $coupons;
			}

			//----> sale library 적용
			unset($param, $sales, $optsalelist);
			$param['option_type']					= 'option';
			$param['consumer_price']				= $data['consumer_price'];
			$param['price']							= $data['org_price'];
			$param['sale_price']					= $data['price'];
			$param['ea']							= $data['ea'];
			$param['goods_ea']						= $cart['data_goods'][$data['goods_seq']]['ea'];
			$param['category_code']					= $category;
			$param['goods_seq']						= $data['goods_seq'];
			$param['goods']							= $data;
			if	($coupon_download_seq)
				$param['coupon_download_seq']		= $coupon_download_seq;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);

			// 기본 정보
			$opt_price								= $sales['one_result_price'];
			$data['sale_price']						= $sales['one_result_price'];
			if	(!$param['sale_price']){
				$data['basic_sale']					= $sales['one_sale_list']['basic'];
				$data['event_sale_target']			= ($sales['target_list']['event'] == 1) ? 'consumer_price' : 'price';
				$data['event_sale']					= $sales['one_sale_list']['event'];
				$data['multi_sale']					= $sales['one_sale_list']['multi'];
				$data['event_reserve']				= $sales['one_reserve_list']['event'];
				$data['event_point']				= $sales['one_point_list']['event'];
			}

			// 쿠폰할인 정보
			$data['coupon_sale']					= $sales['sale_list']['coupon'];
			$data['download_seq']					= $coupon_download_seq;
			if	($coupon_download_seq){
				$coupon_same_time_n					= $this->sale->coupon_same_time_n;
				$coupon_same_time_n_duplication_n	= $this->sale->coupon_duplication_n;
				$coupon_same_time_y					= $this->sale->coupon_same_time_y;
				$coupon_sale_payment_b				= $this->sale->coupon_sale_payment_b;
				$coupon_sale_agent_m				= $this->sale->coupon_sale_agent_m;
			}

			// 회원할인 정보
			$member_sale							+= $data['member_sale'];
			$data['member_sale_unit']				= $sales['one_sale_list']['member'];
			$data['member_sale']					= $sales['sale_list']['member'];

			// 코드할인 정보
			$data['promotion_code_seq']				= $this->sale->code_seq;
			$data['promotion_code_sale']			= $sales['sale_list']['code'];

			// 좋아요 할인 정보
			$data['fblike_sale_unit']				= $sales['one_sale_list']['like'];
			$data['fblike_sale']					= $sales['sale_list']['like'];

			// 모바일할인 정보
			$data['mobile_sale_unit']				= $sales['one_sale_list']['mobile'];
			$data['mobile_sale']					= $sales['sale_list']['mobile'];

			// 유입경로 할인 정보
			$data['referersale_seq']				= $this->sale->referer_seq;
			$data['referer_sale']					= $sales['sale_list']['referer'];

			// sale_price가 없을 시 여기서 event까지 계산함
			if	(!$data['event'] && $this->sale->cfgs['event']){
				$data['event']					= $this->sale->cfgs['event'];
				$data['eventEnd']				= $sales['eventEnd'];
			}

			// 적립금 / 포인트 ( 실결제 금액 기준이기에 여기서 계산 )
			// 이벤트 적립금 / 포인트 계산 ( 장바구니에서 혜택적용할인가를 받아서 쓸 경우만 )
			if	($param['sale_price']){
				$this->sale->cfgs['event']		= $data['event'];
				$data['event_reserve']			= $this->sale->event_sale_reserve($sales['one_result_price']);
				$data['event_point']			= $this->sale->event_sale_point($sales['one_result_price']);
			}
			$data['member_reserve']					= $sales['one_reserve_list']['member'];
			$data['member_point']					= $sales['one_point_list']['member'];
			$data['fb_reserve']						= $sales['one_reserve_list']['like'];
			$data['fb_point']						= $sales['one_point_list']['like'];
			$data['mobile_reserve']					= $sales['one_reserve_list']['mobile'];
			$data['mobile_point']					= $sales['one_point_list']['mobile'];

			$total_real_sale_price					+= $sales['result_price'];
			$data['tot_org_price']					= $data['org_price'] * $data['ea'];
			$data['tot_sale_price']					= $sales['total_sale_price'];
			$data['tot_result_price']				= $sales['result_price'];
			if	($sales['title_list'])foreach($sales['title_list'] as $sale_type => $tmptitle){
				$optsalelist[$sale_type]						= $sales['sale_list'][$sale_type];
				$moptsalelist[$sale_type]						= $sales['sale_list'][$sale_type];
				$cart['total_sale_list'][$sale_type]['title']	= $sale_title;
				$cart['total_sale_list'][$sale_type]['price']	+= $sales['sale_list'][$sale_type];
			}

			$this->sale->reset_init();
			//<---- sale library 적용

			// 기본배송 상품 합계금액
			if($data['shipping_policy']=='shop')
				$total_price_for_shop_delivery	+= $data['sale_price'] * $data['ea'];

			// 구매적립(적립금 제한 조건 설정에 따른 분기)
			$new_opt_price	= 0;
			if ($cfg_reserve['default_reserve_limit'] == 3 && $_POST['emoney'] > 0) {
				// 적립 제한 조건 B설정 추가 leewh 2014-07-04
				$each_using_reserve		= 0;
				unset($reserve_log_btype);

				// 필수 옵션 1개 사용적립금 계산
				$each_using_reserve		= $this->goodsmodel->get_reserve_standard_pay($opt_price, $data['ea'], $cal_total_real_sale_price, $tot_using_reserve);

				$new_opt_price			= $opt_price - $each_using_reserve;
				$reserve_log_btype		= '[제한조건B (실결제금액-사용적립금):'
										. ($opt_price - $each_using_reserve).':'.$new_opt_price.'] ';

				// 결제금액 0원 일경우 적립금 0원 처리
				if ($new_opt_price < 1) {
					$data['reserve'] = 0;
				} else {
					if ($data['reserve_unit'] == 'won') {
						$data['reserve'] = (int) (($data['reserve']/$data['price'])*$new_opt_price);
					}
				}
			} else {
				// 적립금 계산용 가격 분리 leewh 2014-07-09
				$new_opt_price = $opt_price;
			}

			// 포인트
			$data['point']		= $this->goodsmodel->get_point_with_policy($opt_price);
			$data['reserve']	= $this->goodsmodel->get_reserve_with_policy($data['reserve_policy'], $new_opt_price, $cfg_reserve['default_reserve_percent'], $data['reserve_rate'], $data['reserve_unit'], $data['reserve']);

			// 적립금,포인트 로그
			$log	= '';
			if	( $reserve_log_btype )			$log	.= $reserve_log_btype;
			if	( $data['reserve'] > 0 )		$log	.= ($log?' / ':'').'구매  : '.(is_numeric($data['reserve'])?number_format($data['reserve']):$data['reserve']);
			if	( $data['event_reserve'] > 0 )	$log	.= ($log?' / ':'').'이벤트  : '.(is_numeric($data['event_reserve'])?number_format($data['event_reserve']):$data['event_reserve']);
			if	( $data['member_reserve'] > 0 )	$log	.= ($log?' / ':'').'회원  : '.(is_numeric($data['member_reserve'])?number_format($data['member_reserve']):$data['member_reserve']);
			if	( $data['fb_reserve'] > 0 )		$log	.= ($log?' / ':'').'좋아요  : '.(is_numeric($data['fb_reserve'])?number_format($data['fb_reserve']):$data['fb_reserve']);
			if	( $data['mobile_reserve'] > 0 )	$log	.= ($log?' / ':'').'모바일  : '.(is_numeric($data['mobile_reserve'])?number_format($data['mobile_reserve']):$data['mobile_reserve']);
			$data['reserve_log']	= $log;
			$log	= '';
			if	( $data['point'] > 0 )			$log	.= ($log?' / ':'').'구매  : '.(is_numeric($data['point'])?number_format($data['point']):$data['point']);
			if	( $data['event_point'] > 0 )	$log	.= ($log?' / ':'').'이벤트  : '.(is_numeric($data['event_point'])?number_format($data['event_point']):$data['event_point']);
			if	( $data['member_point'] > 0 )	$log	.= ($log?' / ':'').'회원  : '.(is_numeric($data['member_point'])?number_format($data['member_point']):$data['member_point']);
			if	( $data['fb_point'] > 0 )		$log	.= ($log?' / ':'').'좋아요  : '.(is_numeric($data['fb_point'])?number_format($data['fb_point']):$data['fb_point']);
			if	( $data['mobile_point'] > 0 )	$log	.= ($log?' / ':'').'모바일  : '.(is_numeric($data['mobile_point'])?number_format($data['mobile_point']):$data['mobile_point']);
			$data['point_log']		= $log;

			// 옵션의 적립금 포인트
			$data['reserve_one']	= (int) $data['reserve'] + (int) $data['event_reserve'] + (int) $data['member_reserve'] + (int) $data['fb_reserve'] + (int) $data['mobile_reserve'];
			$data['point_one']		= (int) $data['point'] +  (int) $data['event_point'] + (int) $data['member_point'] + (int) $data['fb_point'] + (int) $data['mobile_point'];

			// 구매 시 적립금액 제한 조건 추가 leewh 2014-06-25
			if	($cfg_reserve['default_reserve_limit'] == 1 && $_POST['emoney'] > 0) {
				$data['reserve_one']	= 0;
				$data['reserve_log']	.= ' [제한조건D 사용:-'.$_POST['emoney'].']';
			}elseif	($cfg_reserve['default_reserve_limit'] == 2 && $_POST['emoney'] > 0) {
				$minus_reserve			= 0;
				$reserve_subtract		= $appointed_reserve - $_POST['emoney'];

				if ($reserve_subtract > 0) {
					// 필수 옵션 차감할 1개 사용 적립금 계산
					$tmp_tot_reserve		= $data['reserve_one'] * $data['ea'];
					$minus_reserve			= $this->goodsmodel->get_reserve_limit($tmp_tot_reserve, $data['ea'], $appointed_reserve, $_POST['emoney']);

					$data['reserve_one']	= $data['reserve_one'] - $minus_reserve;
					$data['reserve_log']	.= ' [제한조건C 사용 : -'.$minus_reserve.']';
				} else {
					$minus_reserve			= $_POST['emoney'];
					$data['reserve_one']	= 0;
					$data['reserve_log']	.= ' [제한조건C 전액사용 : -'.$minus_reserve.']';
				}
			}

			$data['tot_reserve']						= $data['reserve_one'] * $data['ea'];
			$data['tot_point']							= $data['point_one'] * $data['ea'];
			$data['option_suboption_price_sum']			= $data['sale_price'] * $data['ea'];
			$data['option_suboption_price_sum_origin']	= $data['price'] * $data['ea'];

			// 추가구성옵션 계산
			if	($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $k => $cart_suboption){
					if	($cart_suboption['reserve_unit'] == 'won') {
						$cart_suboption['reserve']		= $cart_suboption['reserve'] / $cart_suboption['ea'];
					}

					//----> sale library 적용
					unset($param, $sales, $subsalelist);
					$param['option_type']			= 'suboption';
					$param['sub_sale']				= $cart_suboption['sub_sale'];
					$param['consumer_price']		= $cart_suboption['consumer_price'];
					$param['price']					= $cart_suboption['price'];
					$param['sale_price']			= $cart_suboption['price'];
					$param['ea']					= $cart_suboption['ea'];
					$param['category_code']			= $category;
					$param['goods_seq']				= $data['goods_seq'];
					$param['goods']					= $data;
					$this->sale->set_init($param);
					$sales	= $this->sale->calculate_sale_price($applypage);
					if	(!$param['sale_price']){
						$cart_suboption['basic_sale']			= $sales['one_sale_list']['basic'];
						$cart_suboption['event_sale_target']	= ($sales['target_list']['event'] == 1) ? 'consumer_price' : 'price';
						$cart_suboption['event_sale']			= $sales['one_sale_list']['event'];
						$cart_suboption['multi_sale']			= $sales['one_sale_list']['multi'];
					}

					$cart_suboption['member_sale_unit']	= $sales['one_sale_list']['member'];
					$cart_suboption['member_sale']		= $sales['sale_list']['member'];
					$member_sale						+= $cart_suboption['member_sale'];
					$cart_suboption['member_reserve']	= $sales['one_reserve_list']['member'];
					$cart_suboption['member_point']		= $sales['one_point_list']['member'];
					$sale_suboption_price				= $sales['one_result_price'];
					$cart_suboption['sale_price']		= $sales['one_result_price'];
					$total_sale_suboption				+= $cart_suboption['member_sale'];
					$subsaletotalprice					= $sales['total_sale_price'];
					$data['tot_org_price']				+= $cart_suboption['org_price'] * $cart_suboption['ea'];
					$data['tot_sale_price']				+= $sales['total_sale_price'];
					$data['tot_result_price']			+= $sales['result_price'];

					if	($sales['title_list'])foreach($sales['title_list'] as $sale_type => $tmptitle){
						$subsalelist[$sale_type]						= $sales['sale_list'][$sale_type];
						$moptsalelist[$sale_type]						+= $sales['sale_list'][$sale_type];
						$cart['total_sale_list'][$sale_type]['title']	= $sale_title;
						$cart['total_sale_list'][$sale_type]['price']	+= $sales['sale_list'][$sale_type];
					}
					$this->sale->reset_init();
					//<---- sale library 적용

					// 구매적립금(적립금 제한 조건 설정에 따른 분기)
					$new_sale_suboption_price	= 0;
					if	($cfg_reserve['default_reserve_limit'] == 3 && $_POST['emoney'] > 0) {

						/* 적립 제한 조건 B설정 추가 leewh 2014-07-04 */
						$each_sub_using_reserve = 0;
						unset($reserve_sub_log_btype);

						// 서브옵션 1개 사용적립금 계산
						$each_sub_using_reserve	= $this->goodsmodel->get_reserve_standard_pay($sale_suboption_price, $cart_suboption['ea'], $cal_total_real_sale_price, $tot_using_reserve);

						$new_sale_suboption_price	= $sale_suboption_price - $each_sub_using_reserve;
						$reserve_sub_log_btype		= "[제한조건B (실결제금액-사용적립금):$sale_suboption_price-$each_sub_using_reserve:$new_sale_suboption_price] ";

						// 결제금액 0원 일경우 적립금 0원 처리
						if	($new_sale_suboption_price < 1) {
							$cart_suboption['reserve']	= 0;
						}else{
							if	($cart_suboption['reserve_unit'] == "won") {
								$cart_suboption['reserve'] = (int) (($cart_suboption['reserve'] / $cart_suboption['price']) * $new_sale_suboption_price);
							}
						}
					}else{
						// 적립금 계산용 가격 분리 leewh 2014-07-09
						$new_sale_suboption_price	= $sale_suboption_price;
					}

					// 구매 적립금 및 포인트
					$cart_suboption['reserve']	= (int) $this->goodsmodel->get_reserve_with_policy($data['reserve_policy'], $new_sale_suboption_price, $cfg_reserve['default_reserve_percent'], $cart_suboption['reserve_rate'], $cart_suboption['reserve_unit'], $cart_suboption['reserve']);
					$cart_suboption['point']	= (int) $this->goodsmodel->get_point_with_policy($sale_suboption_price) * $cart_suboption['ea'] ;

					$cart_suboption['reserve']	+= $cart_suboption['member_reserve']*$cart_suboption['ea'];

					/* 구매 시 적립금액 제한 조건 추가 leewh 2014-06-25 */
					if	($cfg_reserve['default_reserve_limit'] == 1 && $_POST['emoney'] > 0) {
						$cart_suboption['reserve_log']	= '총적립 : '. (int) ($cart_suboption['reserve'] / $cart_suboption['ea']);
						$cart_suboption['reserve_log']	.= ' [제한조건D 사용:-'.$_POST['emoney'].']';
						$cart_suboption['reserve']		= 0;
					}elseif	($cfg_reserve['default_reserve_limit'] == 2 && $_POST['emoney'] > 0){
						$minus_sub_reserve		= 0;
						$reserve_sub_subtract	= $appointed_reserve - $_POST['emoney'];

						if ($reserve_sub_subtract > 0) {
							/* 서브옵션 차감할 1개 사용 적립금 계산 */
							$minus_sub_reserve = $this->goodsmodel->get_reserve_limit($cart_suboption['reserve'], $cart_suboption['ea'], $appointed_reserve, $_POST['emoney']);

							$cart_suboption['reserve_log'] = '총적립 : '. (int) ($cart_suboption['reserve'] / $cart_suboption['ea']);
							$cart_suboption['reserve_log'] .= ' [제한조건C 사용:-'.$minus_sub_reserve.']';
							$cart_suboption['reserve'] = $cart_suboption['reserve'] - (int) ($minus_sub_reserve*$cart_suboption['ea']);

						} else {
							$minus_sub_reserve				= $_POST['emoney'];
							$cart_suboption['reserve_log']	= '총적립 : '.(int) ($cart_suboption['reserve'] / $cart_suboption['ea']);
							$cart_suboption['reserve_log']	.= ' [제한조건C 전액사용:-'.$minus_sub_reserve.']';
							$cart_suboption['reserve'] = 0;
						}
					} else if ($cfg_reserve['default_reserve_limit'] == 3 && $_POST['emoney'] > 0) {
						if ($reserve_sub_log_btype) {
							$cart_suboption['reserve_log'] = $reserve_sub_log_btype.' 총적립 : '. (int) ($cart_suboption['reserve'] / $cart_suboption['ea']);
						}
					}

					$total_reserve				+= $cart_suboption['reserve'];
					$cart_suboption['point']	+= $cart_suboption['member_point'] * $cart_suboption['ea'];
					$total_point				+= $cart_suboption['point'];
					$total_real_sale_price		+= $cart_suboption['sale_price'] * $cart_suboption['ea'];

					$scripts[] = '$("span#suboption_reserve_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("'.number_format($cart_suboption['reserve']).'");';
					$scripts[] = '$("span#suboption_point_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("'.number_format($cart_suboption['point']).'");';
					$scripts[] = '$("span#member_sale_suboption_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("'.number_format($cart_suboption['member_sale']).'");';
					$scripts[] = '$("span#cart_suboption_price_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("'.number_format($sale_suboption_price*$cart_suboption['ea']).'");';

					if($cart_suboption['member_sale']){
						$scripts[] = '$("#suboption_member_sale_tr_'.$cart_suboption['cart_suboption_seq'].'",parent.document).show();';
						$scripts[] = '$("#sale_suboption_'.$cart_suboption['cart_suboption_seq'].'",parent.document).hide();';
					}else{
						$scripts[] = '$("#suboption_member_sale_tr_'.$cart_suboption['cart_suboption_seq'].'",parent.document).hide();';
						$scripts[] = '$("#sale_suboption_'.$cart_suboption['cart_suboption_seq'].'",parent.document).show();';
					}

					################# 2014-10-31 변경된 장바구니 모양으로 인해 추가
					// 추가구성옵션 전체 할인금액
					if	($subsaletotalprice > 0){
						$scripts[] = '$("#cart_suboption_sale_total_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("'.number_format($subsaletotalprice).'원");';
						$scripts[] = '$("#cart_suboption_sale_detail_'.$cart_suboption['cart_suboption_seq'].'",parent.document).show();';
					}else{
						$scripts[] = '$("#cart_suboption_sale_total_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("-");';
						$scripts[] = '$("#cart_suboption_sale_detail_'.$cart_suboption['cart_suboption_seq'].'",parent.document).hide();';
					}
					// 추가구성옵션 할인내역
					if	($subsalelist)foreach($subsalelist as $tmp_type => $tmp_price){
						if	($tmp_price > 0){
							$scripts[] = '$("#cart_suboption_'.$tmp_type.'_saletr_'.$cart_suboption['cart_suboption_seq'].'",parent.document).show();';
							$scripts[] = '$("span#cart_suboption_'.$tmp_type.'_saleprice_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html("'.number_format($tmp_price).'");';
						}else{
							$scripts[] = '$("#cart_suboption_'.$tmp_type.'_saletr_'.$cart_suboption['cart_suboption_seq'].'",parent.document).hide();';
							$scripts[] = '$("span#cart_suboption_'.$tmp_type.'_saleprice_'.$cart_suboption['cart_suboption_seq'].'",parent.document).html(0);';
						}
					}
					################# 2014-10-31 변경된 장바구니 모양으로 인해 추가

					// 기본배송 상품 합계금액
					if($data['shipping_policy']=='shop')
						$total_price_for_shop_delivery += $cart_suboption['sale_price']*$cart_suboption['ea'];

					$data['cart_suboptions'][$k] = $cart_suboption;
					$data['option_suboption_price_sum'] += $cart_suboption['sale_price']*$cart_suboption['ea'];
					$data['option_suboption_price_sum_origin'] += $cart_suboption['price']*$cart_suboption['ea'];
				}
			}

			$data['cart_sale'] = $data['member_sale']+$data['mobile_sale'] + $data['fblike_sale'] + $data['promotion_code_sale'] + $data['coupon_sale'] + $data['referer_sale'];

			################# 2014-10-31 변경된 장바구니 모양으로 인해 추가

			// 필수옵션 할인내역
			if	($optsalelist){
				$tmp_price_sum	= 0;
				foreach($optsalelist as $tmp_type => $tmp_price){
					if	($tmp_price > 0){
						$tmp_price_sum	+= $tmp_price;
						$scripts[]		= '$("#cart_option_'.$tmp_type.'_saletr_'.$data['cart_option_seq'].'",parent.document).show();';
						$scripts[]		= '$("span#cart_option_'.$tmp_type.'_saleprice_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($tmp_price).'");';
					}else{
						$scripts[]		= '$("#cart_option_'.$tmp_type.'_saletr_'.$data['cart_option_seq'].'",parent.document).hide();';
						$scripts[]		= '$("span#cart_option_'.$tmp_type.'_saleprice_'.$data['cart_option_seq'].'",parent.document).html(0);';
					}
				}
				if	($tmp_price_sum > 0){
					$scripts[]		= '$("#cart_option_sale_detail_'.$data['cart_option_seq'].'", parent.document).show();';
					$scripts[]		= '$("#cart_option_sale_total_'.$data['cart_option_seq'].'", parent.document).html("'.number_format($tmp_price_sum).'원");';
				}else{
					$scripts[]		= '$("#cart_option_sale_detail_'.$data['cart_option_seq'].'", parent.document).hide();';
					$scripts[]		= '$("#cart_option_sale_total_'.$data['cart_option_seq'].'", parent.document).html("-");';
				}
			}
			// 필수옵션+추가구성옵션 할인내역
			if	($moptsalelist){
				$tmp_price_sum	= 0;
				foreach($moptsalelist as $tmp_type => $tmp_price){
					if	($tmp_price > 0){
						$tmp_price_sum	+= $tmp_price;
						$scripts[]		= '$("#cart_sum_'.$tmp_type.'_saletr_'.$data['cart_option_seq'].'",parent.document).show();';
						$scripts[]		= '$("span#cart_sum_'.$tmp_type.'_saleprice_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($tmp_price).'");';
					}else{
						$scripts[]		 = '$("#cart_sum_'.$tmp_type.'_saletr_'.$data['cart_option_seq'].'",parent.document).hide();';
						$scripts[]		= '$("span#cart_sum_'.$tmp_type.'_saleprice_'.$data['cart_option_seq'].'",parent.document).html(0);';
					}
				}
				if	($tmp_price_sum > 0){
					$scripts[]		= '$("#cart_sum_sale_tr_'.$data['cart_option_seq'].'", parent.document).show();';
					$scripts[]		= '$("span#cart_sum_sale_'.$data['cart_option_seq'].'", parent.document).html("'.number_format($tmp_price_sum).'");';
				}else{
					$scripts[]		= '$("#cart_sum_sale_tr_'.$data['cart_option_seq'].'", parent.document).hide();';
					$scripts[]		= '$("span#cart_sum_sale_'.$data['cart_option_seq'].'", parent.document).html("0");';
				}
			}

			$scripts[] = '$("span.cart_option_price_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['sale_price']*$data['ea']).'");';

			################# 2014-10-31 변경된 장바구니 모양으로 인해 추가



			if( $this->_is_mobile_agent) {//mobile 인 경우에만적용 $this->mobileMode ||
				$scripts[] = '$("#mobile_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
				$scripts[] = '$("span#mobile_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['mobile_sale']).'");';
			}

			$scripts[] = '$("span#cart_option_price_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['sale_price']*$data['ea']).'");';

			$scripts[] = '$("span#cart_origin_price_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['price']).'");';

			if($data['fblike_sale']){
				$scripts[] = '$("#fblike_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
				$scripts[] = '$("span#fblike_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['fblike_sale']).'");';
			}else{
				$scripts[] = '$("#fblike_sale_tr_'.$data['cart_option_seq'].'",parent.document).hide();';
			}

			if($data['promotion_code_sale']){
				$scripts[] = '$("#promotioncode_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
				$scripts[] = '$("span#promotioncode_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['promotion_code_sale']).'");';
			}else{
				$scripts[]	= '$("#promotioncode_sale_tr_'.$data['cart_option_seq'].'",parent.document).hide();';
			}
			if($data['coupon_sale']){
				$scripts[] = '$("#coupon_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
				$scripts[] = '$("span#coupon_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['coupon_sale']).'");';
			}else{
				$scripts[] = '$("#coupon_sale_tr_'.$data['cart_option_seq'].'",parent.document).hide();';
			}
			if($data['member_sale']){
				$scripts[] = '$("#option_member_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
				$scripts[] = '$("span#member_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['member_sale']).'");';
			}else{
				$scripts[] = '$("#option_member_sale_tr_'.$data['cart_option_seq'].'",parent.document).hide();';
			}
			if($data['referer_sale']){
				$scripts[] = '$("#referer_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
				$scripts[] = '$("span#referer_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['referer_sale']).'");';
			}else{
				$scripts[] = '$("#referer_sale_tr_'.$data['cart_option_seq'].'",parent.document).hide();';
			}

			if($data['cart_sale']) $scripts[] = '$("#cart_sale_tr_'.$data['cart_option_seq'].'",parent.document).show();';
			$scripts[] = '$("span#cart_sale_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['cart_sale']).'");';

			$scripts[] = '$("span#option_reserve_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['tot_reserve']).'");';
			$scripts[] = '$("span#option_point_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['tot_point']).'");';

			if($data['cart_sale']){
				$scripts[] = '$("#sale_option_'.$data['cart_option_seq'].'",parent.document).hide();';
			}else{
				$scripts[] = '$("#sale_option_'.$data['cart_option_seq'].'",parent.document).show();';
			}

			$scripts[] = '$("span#option_suboption_price_sum_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['option_suboption_price_sum']).'");';

			$scripts[] = '$("span#option_suboption_price_sum_origin_'.$data['cart_option_seq'].'",parent.document).html("'.number_format($data['option_suboption_price_sum_origin']).'");';

			/*
			if	($exception_sale){
				// 프로모션코드 세일이 제외됬을 때
				if	(in_array('promotion_code_sale', array_keys($exception_sale))){
					$scripts[]	= 'parent.getPromotionCartDel();';
				}
				$scripts[]	= 'parent.exception_saleprice(\''.array_sum($exception_sale).'\');';
			}
			*/
			// 상품 무게 계산
			if( $data['shipping_weight_policy'] == "shop" ){
				$goods_weight = $international_shipping['defaultGoodsWeight'];
			}else{
				$goods_weight = $data['goods_weight'];
			}

			$data['goods_weight'] = $goods_weight * $data['ea'];
			$cart['list'][$key] = $data;

			$total_sales_price	+= (int) $data['tot_sale_price'];
			$total_mobile_sale += (int) $data['mobile_sale'];
			$total_fblike_sale += (int) $data['fblike_sale'];
			$total_promotion_code_sale += (int) $data['promotion_code_sale'];
			$total_coupon_sale += (int) $data['coupon_sale'];
			$total_member_sale += (int) $data['member_sale'];
			$total_referer_sale += (int) $data['referer_sale'];
			$total_reserve += $data['tot_reserve'];
			$total_point += $data['tot_point'];
			$total_sale_price += $data['cart_sale'];
			$total_goods_weight += $data['goods_weight'];
		}

		if( is_array($coupon_same_time_n) && count($coupon_same_time_n) > 0 ){//단독쿠폰체크
			if( count($coupon_same_time_n) != 1 || ( count($coupon_same_time_n) > 0 && count($coupon_same_time_y) > 0) ) {
				$err_coupon = "단독쿠폰은 다른쿠폰과 동시에 사용하실 수 없습니다. <br/>쿠폰을 다시한번 선택해 주세요.";
				$err_coupon_callback = 'parent.sametime_coupon_dialog();'.$pg_cancel_script;
				openDialogAlert($err_coupon,400,140,'parent',$err_coupon_callback);
				exit;
			}elseif( count($coupon_same_time_n) == 1 && !$coupon_same_time_n_duplication[$coupon_same_time_n[0]] && $coupon_same_time_n_duplication[$coupon_same_time_n[0]]>0 ){//단독이면서 중복쿠폰이 아닌경우
				$err_coupon = "해당 단독쿠폰은 중복으로 사용하실 수 없습니다. <br/>다시한번 선택해 주세요.";
				$err_coupon_callback = 'parent.sametime_coupon_dialog();'.$pg_cancel_script;
				openDialogAlert($err_coupon,400,140,'parent',$err_coupon_callback);
				exit;
			}
		}

		$total_sale_price += $total_sale_suboption;
		$cart['total_mobile_sale'] = $total_mobile_sale;
		$cart['total_fblike_sale'] = $total_fblike_sale;
		$cart['total_promotion_code_sale'] = $total_promotion_code_sale;
		$cart['total_coupon_sale'] = $total_coupon_sale;
		$cart['total_member_sale'] = $total_member_sale;
		$cart['total_referer_sale'] = $total_referer_sale;
		$cart['total_reserve'] = $total_reserve;
		$cart['total_real_sale_price'] = $total_real_sale_price; //총실결제금액합계@2014-07-04
		$cart['total_point'] = $total_point;
		$cart['total_sale_price'] = $total_sale_price;
		$cart['total_goods_weight'] = $total_goods_weight;

		if(isset($_POST['enuri']) && $_POST['enuri'] > 0){
			$enuri = $_POST['enuri'];
		}else{
			$enuri = 0;
		}

		// 할인적용가 기준 배송비 계산

		$total_goods_price = $cart['total'] - $cart['total_sale_price'];
		/*
		if($cart['shop_shipping_policy']['free']){
			$cart['shipping_price']['shop'] = (int) $cart['shop_shipping_policy']['price'];
			if($cart['shop_shipping_policy']['free'] <= $total_goods_price){
				$cart['shipping_price']['shop'] = 0;
			}
		}
		*/

		/* 오리지널 기본배송비 */
		$this->shipping_cost			= (int) $cart['shipping_price']['shop'];

		/* 복수배송지 실결제배송비 */
		if($shipping){
			if($_POST['multiShippingChk'])
			{
				$total_shipping_price = $this->_calculate_multi_shipping_price(&$scripts,&$cart,&$shipping);
				$this->shipping_cost = array_sum($this->arr_multi_shop_shipping_price) + array_sum($this->arr_multi_add_shipping_price);
			}
			/* 단일배송지 실결제배송비 */
			else
			{
				$total_shipping_price = $this->_calculate_single_shipping_price(&$scripts,&$cart,$total_price_for_shop_delivery,&$shipping,&$international_shipping);
			}
		}

		//쿠폰>사용제한>무통장만가능
		if( is_array($coupon_sale_payment_b) || $this->shipping_coupon_payment_b ){
			$cart['coupon_sale_payment_b'] = count($coupon_sale_payment_b);
			if( $this->shipping_coupon_payment_b === true ) $cart['coupon_sale_payment_b'] = (int) ($cart['coupon_sale_payment_b'] + 1);
		}

		if($cart['coupon_sale_payment_b']  && $_POST['payment'] != 'bank' && !$this->displaymode ) {
			openDialogAlert("현재 무통장 전용 쿠폰을 사용하셨습니다.<br />결제수단을 무통장으로 변경해 주세요!",400,140,'parent',"$('div#couponDownloadDialog','parent').dialog('close');".$pg_cancel_script);
			exit;
		}

		//배송비쿠폰은 개별배송비 상품이 있을 경우 제외안내
		if( $this->displaymode != 'coupon' && $_POST['download_seq'] && $this->arr_goods_shipping_ck ) {//$this->shipping_coupon_down_seq &&
			openDialogAlert("배송비 쿠폰은 개별배송 상품에는 반영되지 않습니다.",400,140,'parent',$pg_cancel_script);
		}

		//쿠폰>사용제한>모바일/테블릿기기만가능
		if( is_array($coupon_sale_agent_m)){
			$cart['coupon_sale_agent_m'] = count($coupon_sale_agent_m);
		}

		//alert($this->shipping_cost);

		// 에누리
		if($person_seq != ""){
			$query = $this->db->query("select * from fm_person where person_seq='".$person_seq."'");
			$res = $query->row_array();
			$enuri = $res['enuri'];
		}else{
			$enuri = (int) $_POST['enuri'];
		}

		/* 총 결제금액 */
		$cart['total_sale_price'] = $cart['total_sale_price'] + $enuri;
		if( $_POST['shipping_method'] && $_POST['shipping_method'] != 'delivery') $total_shipping_price = 0;
		$settle_price	= $cart['total'] - $cart['total_sale_price'] + $total_shipping_price;
		if($settle_price<0) $settle_price=0;

		/* 캐쉬 사용여부 체크*/
		$err_reserve = '';
		if($cfg_reserve['cash_use']=='N' && ($_POST['cash'] > 0 || $_POST['cash_all'])){
			echo '<script>
			$("input[name=\'cash\']",parent.document).val(0);
			$("input[name=\'cash_view\']",parent.document).val(0);
			$("input[name=\'cash_all\']",parent.document).val("");
			$(".cash_cancel_button",parent.document).hide();
			$(".cash_input_button",parent.document).show();
			$(".cash_all_input_button",parent.document).show();
			</script>';
			$err_reserve = "이머니 사용이 불가한 상태 입니다.";
			openDialogAlert($err_reserve,400,140,'parent',$pg_cancel_script);
			exit;
		}

		/* 캐쉬 사용할 수 있는 금액 계산*/
		if( $members && ($_POST['cash'] > 0 || $_POST['cash_all']) ){

			$reserve_use = true;

			// 적립금 전액사용
			if($_POST['cash_all']){
				$_POST['cash'] = $this->ordermodel->get_usable_cash($cart['total'],$settle_price-$_POST['emoney'],$members['cash']);
				if($_POST['cash']){
					echo '<script>
					$("input[name=\'cash\']",parent.document).val("'.$_POST['cash'].'");
					$("input[name=\'cash_view\']",parent.document).val("'.$_POST['cash'].'");
					</script>';
				}else{
					$reserve_use = false;
					$err_reserve = "이머니를 사용하실 수 없습니다.";
				}
			}

			if( $_POST['cash'] > $settle_price ){
				$reserve_use	= false;
				$err_reserve	= "최대 ".number_format($settle_price)."원까지 사용가능 합니다.";
			}

			if( $_POST['cash'] > $members['cash'] ){
				$reserve_use	= false;
				$err_reserve	= '보유 금액 이상 사용은 불가능합니다.';
//				$err_reserve	= number_format( $members['cash'] )."원 이상 사용하실 수 없습니다.";
			}

			if($err_reserve){
				echo '<script>
				$("input[name=\'cash\']",parent.document).val(0);
				$("input[name=\'cash_view\']",parent.document).val(0);
				$("input[name=\'cash_all\']",parent.document).val("");
				$(".cash_cancel_button",parent.document).hide();
				$(".cash_input_button",parent.document).show();
				$(".cash_all_input_button",parent.document).show();
				</script>';
				openDialogAlert($err_reserve,400,140,'parent',$pg_cancel_script);
				exit;
			}else{
				echo '<script>
				$("input[name=\'cash_all\']",parent.document).val("");
				</script>';
			}
			if($_POST['cash'] > 0){
				echo '<script>
				$("#priceCashTd").show();
				$("#total_cash").html("'.number_format($_POST['cash']).'");
				</script>';
			}
			$cart['cash'] = (int) $_POST['cash'];
			$settle_price -= (int) $cart['cash'];
		}

		$err_reserve = '';
		if( $members && ($_POST['emoney'] > 0 || $_POST['emoney_all'])){

			$reserve_use = true;

			$members['emoney'] = $this->membermodel->get_emoney($members['member_seq']);

			// 적립금 전액사용
			if($_POST['emoney_all']){

				/* 적립금 전액사용 클릭시 사용금액을 0원 처리할 경우 에러메세지를 받기 위해 리턴값을 배열로 받음 leewh 2014-11-12 */
				$returnInfo = $this->ordermodel->get_usable_emoney($cart['total'],$settle_price-$_POST['cash'],$members['emoney']);
				$_POST['emoney'] = $returnInfo['emoney'];

				if($_POST['emoney']){
					echo '<script>
					$("input[name=\'emoney\']",parent.document).val("'.$_POST['emoney'].'");
					$("input[name=\'emoney_view\']",parent.document).val("'.$_POST['emoney'].'");
					</script>';
				}else{
					$reserve_use = false;
					if ($returnInfo['err_reserve']) {
						$err_reserve = $returnInfo['err_reserve'];
					} else {
						$err_reserve = "적립금을 사용하실 수 없습니다.";
					}
				}
			}

			$reserves = ($this->reserves)?$this->reserves:config_load('reserve');

			/* 적립금 사용 단위 추가 leewh 2014-06-24 */
			if (($reserves['emoney_using_unit'] > 0) && $reserve_use===true) {
				if ($reserves['emoney_using_unit']==1) {
					$using_unit = $_POST['emoney']%10;
					$using_unit_msg = "10원";
				} else if ($reserves['emoney_using_unit']==2) {
					$using_unit = $_POST['emoney']%100;
					$using_unit_msg = "100원";
				} else if ($reserves['emoney_using_unit']==3) {
					$using_unit =$_POST['emoney']%1000;
					$using_unit_msg = "1,000원";
				}

				if ($using_unit != 0) {
					$reserve_use = false;
					$err_reserve = "적립금은 ".$using_unit_msg." 단위로 사용가능 합니다.";
				}
			}

			if( ($_POST['emoney'] > $settle_price) && $reserve_use===true ){
				$reserve_use = false;
				$err_reserve = "최대 ".number_format($settle_price)."원까지 사용가능 합니다.";
			}

			if( ($_POST['emoney'] > $members['emoney']) && $reserve_use===true ){
				$reserve_use = false;
				$err_reserve = number_format( $members['emoney'] )."원 이상 사용하실 수 없습니다.";
			}

			if(($reserves['emoney_use_limit'] > $members['emoney']) && $reserve_use===true){
				$reserve_use = false;
				$err_reserve = number_format($reserves['emoney_use_limit'])."원 이상 적립하여야 합니다.";
			}

			if(($reserves['emoney_price_limit'] > $cart['total']) && $reserve_use===true){
				$reserve_use = false;
				$err_reserve = "상품을 ".number_format($reserves['emoney_price_limit'])."원 이상 사야 합니다.";
			}

			if(($members['emoney'] >= $reserves['emoney_use_limit']) && $reserve_use===true){
				if($reserves['max_emoney_policy'] == 'percent_limit' && $reserves['max_emoney_percent']){
					$max_emoney = (int) ($cart['total'] * $reserves['max_emoney_percent'] / 100);
				}else if($reserves['max_emoney_policy'] == 'price_limit' && $reserves['max_emoney']){
					$max_emoney = (int) $reserves['max_emoney'];
				}

				if($max_emoney > $settle_price) $max_emoney = $settle_price;

				if($_POST['emoney'] < $reserves['min_emoney']){
					$reserve_use = false;
					$err_reserve = "적립금은  최소 ".number_format($reserves['min_emoney'])."원부터 사용가능 합니다.";
				}
				if($_POST['emoney'] > $max_emoney && $reserves['max_emoney_policy'] != 'unlimit'){
					$reserve_use = false;
					$err_reserve = "적립금은  최대 ".number_format($max_emoney)."원까지 사용가능 합니다.";
				}
			}

			if($err_reserve){
				echo '<script>
						$("input[name=\'emoney\']",parent.document).val(0);
						$("input[name=\'emoney_view\']",parent.document).val(0);
						$("input[name=\'emoney_all\']",parent.document).val("");
						$(".emoney_cancel_button",parent.document).hide();
						$(".emoney_input_button",parent.document).show();
						$(".emoney_all_input_button",parent.document).show();
						$("#priceEmoneyTd",parent.document).hide();
					</script>';
				openDialogAlert($err_reserve,400,140,'parent',$pg_cancel_script);
				exit;
			}else{
				if	($this->displaymode != 'coupon') {
					echo '<script>
					$("input[name=\'emoney_all\']",parent.document).val("");
					</script>';
				}
			}

			$cart['emoney'] = (int) $_POST['emoney'];
			$settle_price -= (int) $cart['emoney'];
		}

		$this->amount = $settle_price;

		// 네이버 마일리지
		$this->load->model('navermileagemodel');
		$settle_price = $this->navermileagemodel->check_mileage($settle_price);


		/* 상품결제가합 */
		$this->sum_goods_price = (int) $cart['total'];
		$this->settle_price = (int) $settle_price;
		$cart['total_price'] = $settle_price;
		$this->cart = $cart;

		// 적립금 합계 출력
		if($tot_reserve){
			$scripts[] = '$("#tot_reserve",parent.document).html("'.number_format($tot_reserve).'");';
		}

		/* 구매 시 적립금액 제한 조건 추가 leewh 2014-06-25 */
		if ($cfg_reserve['default_reserve_limit']>0) {
			$scripts[] = 'if (!$("#default_reserve_limit", parent.document).length) $("form#orderFrm", parent.document).append("<input type=\'hidden\' name=\'default_reserve_limit\' id=\'default_reserve_limit\' value=\''.$cfg_reserve['default_reserve_limit'].'\' />");';

			if ($cfg_reserve['default_reserve_limit']>=2) {
				/* 적립금액 제한 조건 C설정 */
				$scripts[] = 'if (!$("#appointed_reserve", parent.document).length) $("form#orderFrm", parent.document).append("<input type=\'hidden\' name=\'appointed_reserve\' id=\'appointed_reserve\' value=\''.$cart['total_reserve'].'\' />");';

				/* 적립금액 제한 조건 B설정 */
				$scripts[] = 'if (!$("#total_real_sale_price", parent.document).length) {$("form#orderFrm", parent.document).append("<input type=\'hidden\' name=\'total_real_sale_price\' id=\'total_real_sale_price\' value=\''.$cart['total_real_sale_price'].'\' />");} else {$("form#orderFrm #total_real_sale_price", parent.document).val('.$cart['total_real_sale_price'].');}';
			}
		}

		/* 쿠폰이 무통장만 사용가능함 @2014-07-09 */
		if( $cart['coupon_sale_payment_b'] ) {
			$scripts[] = 'if (!$("#coupon_sale_payment_b", parent.document).length) {$("form#orderFrm", parent.document).append("<input type=\'hidden\' name=\'coupon_sale_payment_b\' id=\'coupon_sale_payment_b\' value=\''.$cart['coupon_sale_payment_b'].'\' />");} else {$("form#orderFrm #coupon_sale_payment_b", parent.document).val('.$cart['coupon_sale_payment_b'].');}';
		}


		$scripts[] = '$("#total_sale, .total_sale",parent.document).html("'.number_format($cart['total_sale_price']).'");';
		$scripts[] = '$(".settle_price",parent.document).html("'.number_format($cart['total_price']).'");';
		$scripts[] = '$(".totalprice",parent.document).html("'.number_format($cart['total_price']).'");';
		$scripts[] = '$("#total_reserve",parent.document).html("'.number_format($cart['total_reserve']).'");';
		$scripts[] = '$("#total_point",parent.document).html("'.number_format($cart['total_point']).'");';
		$scripts[] = '$("#total_goods_price, span.total_goods_price",parent.document).html("'.number_format($cart['total']).'");';
		$scripts[] = '$("#total_coupon_sale_tr",parent.document).'.($cart['total_coupon_sale']?'show()':'hide()').';';
		$scripts[] = '$("#total_coupon_sale, .total_coupon_sale",parent.document).html("'.number_format($cart['total_coupon_sale']).'");';

		$session_arr = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
		if( count($this->systemfblike['result'])) {//할인혜택이 있으면 '좋아요 혜택' 문구노출
			if( ($cfg['order']['fblike_ordertype'] == 1 && $session_arr['member_seq'] ) || ($cfg['order']['fblike_ordertype'] != 1) ) {//비회원혜택여부
				$scripts[] = '$(".fblikelay",parent.document).show();';
			}
		}
		$scripts[] = '$("#total_fblike_sale_tr",parent.document).'.($cart['total_fblike_sale']?'show()':'hide()').';';

		$scripts[] = '$("#total_fblike_sale",parent.document).html("'.number_format($cart['total_fblike_sale']).'");';
		$scripts[] = '$("#total_mobile_sale_tr",parent.document).'.($cart['total_mobile_sale']?'show()':'hide()').';';
		$scripts[] = '$("#total_mobile_sale",parent.document).html("'.number_format($cart['total_mobile_sale']).'");';
		$scripts[] = '$("#total_member_sale_tr",parent.document).'.($cart['total_member_sale']?'show()':'hide()').';';
		$scripts[] = '$("#total_member_sale",parent.document).html("'.number_format($cart['total_member_sale']).'");';
		$scripts[] = '$("#total_referer_sale_tr",parent.document).'.($cart['total_referer_sale']?'show()':'hide()').';';
		$scripts[] = '$("#total_referer_sale",parent.document).html("'.number_format($cart['total_referer_sale']).'");';
		$scripts[] = '$("#total_shipping_price, .total_shipping_price",parent.document).html("'.number_format($total_shipping_price).'");';

		$scripts[] = '$("#use_emoney, .use_emoney",parent.document).html("'.number_format($cart['emoney']).'");';
		$scripts[] = '$("#use_cash, .use_cash",parent.document).html("'.number_format($cart['cash']).'");';


		################# 2014-10-31 변경된 장바구니 모양으로 인해 추가
		$cart['total_sale_list']['shippingcoupon']['title']	= '배송비쿠폰';
		$cart['total_sale_list']['shippingcoupon']['price']	= 0;
		$cart['total_sale_list']['shippingcode']['title']	= '배송비코드';
		$cart['total_sale_list']['shippingcode']['price']	= 0;
		if	($this->shipping_coupon_sale){
			$total_sales_price	+= $this->shipping_coupon_sale;
			$cart['total_sale_list']['shippingcoupon']['title']	= '배송비쿠폰';
			$cart['total_sale_list']['shippingcoupon']['price']	= $this->shipping_coupon_sale;
		}
		if	(isset($this->shipping_promotion_code_sale) && $this->shipping_promotion_code_sale != 0){
			$total_sales_price	+= $this->shipping_promotion_code_sale;
			$cart['total_sale_list']['shippingcode']['title']	= '배송비코드';
			$cart['total_sale_list']['shippingcode']['price']	= $this->shipping_promotion_code_sale;
		}
		// 에누리
		if	($enuri > 0)	$total_sales_price	+= (int) $enuri;

		// 총할인 정보
		if	($total_sales_price > 0){
			$scripts[] = '$(".total_sale_price_btn",parent.document).show();';
			$scripts[] = '$(".total_sales_price",parent.document).html("'.number_format($total_sales_price).'");';

			if	($cart['total_sale_list'])foreach($cart['total_sale_list'] as $sale_type => $saleArr){
				if	($saleArr['price'] > 0){
					$scripts[] = '$("#total_'.$sale_type.'_sale",parent.document).html("'.number_format($saleArr['price']).'");';
					$scripts[] = '$("#total_'.$sale_type.'_sale_tr, " ,parent.document).show();';
				}else{
					$scripts[] = '$("#total_'.$sale_type.'_sale",parent.document).html("0");';
					$scripts[] = '$("#total_'.$sale_type.'_sale_tr, " ,parent.document).hide();';
				}
			}

			// 배송비 쿠폰
			if(isset($this->shipping_coupon_sale)){//배송비쿠폰선택시
				$scripts[]	= '$("span#shipping_coupon_sale",parent.document).html("<span style=\"padding-left:20px;\">배송비쿠폰할인 : (-)'.number_format($this->shipping_coupon_sale).'원</span>");';
			}else{
				$scripts[]	= '$("span#shipping_coupon_sale",parent.document).html("");';
			}

			// 배송비 코드
			if(isset($this->shipping_promotion_code_sale) && $this->shipping_promotion_code_sale != 0){
				$scripts[]	= '$("span#shipping_code_sale",parent.document).html("<span style=\"padding-left:20px;\">배송비코드할인 : (-)'.number_format($this->shipping_promotion_code_sale).'</span>");';
			}else{
				$scripts[]	= '$("span#shipping_code_sale",parent.document).html("");';
			}
		}else{
			$scripts[] = '$(".total_sale_price_btn",parent.document).hide();';
		}

		// 배송비 합계
		$total_org_shipping_price	= $total_shipping_price + $cart['total_sale_list']['shippingcoupon']['price'] + $cart['total_sale_list']['shippingcode']['price'];
		if	($total_org_shipping_price > 0){
			$scripts[] = '$(".total_org_shipping_price_btn",parent.document).show();';
			$scripts[] = '$("span.total_org_shipping_price",parent.document).html("'.number_format($total_org_shipping_price).'");';

			// 기본배송비
			$basic_delivery	= $this->shipping_cost - $this->area_add_delivery_cost;
			$scripts[]		= '$("span.basic_delivery", parent.document).html("'.number_format($basic_delivery).'");';

			// 개별배송비
			if	($this->arr_goods_shipping_ck > 0){
				$scripts[]		= '$("span.goods_delivery", parent.document).html("'.number_format($this->arr_goods_shipping_ck).'");';
			}

			// 추가배송비
			if	($this->area_add_delivery_cost + $this->total_goods_add_shipping_price > 0){
				$total_add_delivery	= $this->area_add_delivery_cost + $this->total_goods_add_shipping_price;
				$scripts[] = '$("span.add_delivery",parent.document).html("'.number_format($total_add_delivery).'");';
				$scripts[] = '$(".total_add_delivery_lay",parent.document).show();';
			}else{
				$scripts[] = '$("span.add_delivery",parent.document).html("");';
				$scripts[] = '$(".total_add_delivery_lay",parent.document).hide();';
			}
		}else{
			$scripts[] = '$(".total_org_shipping_price_btn",parent.document).hide();';
			$scripts[] = '$("span.total_org_shipping_price",parent.document).html("0");';
			$scripts[] = '$("span.basic_delivery", parent.document).html("0");';
			$scripts[] = '$("span.goods_delivery", parent.document).html("0");';
			$scripts[] = '$("span.add_delivery",parent.document).html("");';
			$scripts[] = '$(".total_add_delivery_lay",parent.document).hide();';
		}

		################# 2014-10-31 변경된 장바구니 모양으로 인해 추가


		/* 배송비 표기 */
		if(!$_POST['shipping_method'] || $_POST['shipping_method'] == 'delivery')
		{
			foreach($cart['data_goods'] as $goods_seq => $data_goods){
				if(  $data_goods['shipping_policy'] == 'shop'  ){
					$shipping_cost = $this->shipping_cost - $this->area_add_delivery_cost;
					if($shipping_cost > 0){
						$scripts[] = '$("span#basic_delivery",parent.document).html("'.number_format($shipping_cost).'원");';
					}else{
						$scripts[] = '$("span#basic_delivery",parent.document).html("택배무료");';
					}

					if($this->area_add_delivery_cost > 0){
						$scripts[] = '$("span#add_basic_delivery",parent.document).html("<span style=\"padding-left:20px;\">추가배송비 : '.number_format($this->area_add_delivery_cost).'원</span>");';
					}else{
						$scripts[] = '$("span#add_basic_delivery",parent.document).html("");';
					}
				}else{
					$goods_shipping = $data_goods['goods_shipping']+$data_goods['add_goods_shipping'];
					if($data_goods['goods_shipping'] > 0){
						$scripts[] = '$("div#goods_each_delivery_'.$goods_seq.'",parent.document).html("'.number_format($data_goods['goods_shipping']).'원");';
					}else{
						$scripts[] = '$("div#goods_each_delivery_'.$goods_seq.'",parent.document).html("택배무료");';
					}

					if($data_goods['add_goods_shipping'] > 0){
						$scripts[] = '$("div#add_goods_each_delivery_'.$goods_seq.'",parent.document).html("추가배송비<br/><span class=\"desc\">'.number_format($data_goods['add_goods_shipping']).'원</span>");';
					}else{
						$scripts[] = '$("div#add_goods_each_delivery_'.$goods_seq.'",parent.document).html("");';
					}
				}
			}
		}

		//@프로모션코드
		$scripts[] = '$("#cartpromotioncode",parent.document).val("'.$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')).'");';
		if($this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))){
			$scripts[] = '$(".cartpromotioncodeinputlay",parent.document).hide();';
			$scripts[] = '$(".cartpromotioncodedellay",parent.document).show();';
		}else{
			$scripts[] = '$(".cartpromotioncodeinputlay",parent.document).show();';
			$scripts[] = '$(".cartpromotioncodedellay",parent.document).hide();';
		}

		$scripts[] = '$("#total_promotion_goods_sale_tr",parent.document).'.($cart['total_promotion_code_sale']?'show()':'hide()').';';
		$scripts[] = '$("#total_promotion_goods_sale, .total_promotion_goods_sale",parent.document).html("'.number_format($cart['total_promotion_code_sale']).'");';

		if(isset($this->shipping_promotion_code_sale) && $this->shipping_promotion_code_sale != 0) {//배송비프로모션선택시
			$scripts[] = '$(".shipping_promotioncode_salelay",parent.document).show();';
			$scripts[] = '$("span#shipping_promotioncode_sale",parent.document).html("'.number_format($this->shipping_promotion_code_sale).'");';

			$scripts[] = '$("#shipping_promotion_code_sale",parent.document).val("'.($this->shipping_promotion_code_sale).'");';

			$scripts[] = '$("span#promotion_shipping_salse, span.promotion_shipping_salse",parent.document).html("<span class=\"desc\" >(- 코드 '.number_format($this->shipping_promotion_code_sale).')</span>");';
			$scripts[] = '$("#shipping_promotion_code_seq",parent.document).val("'.$this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id')).'");';
		}else{
			$scripts[] = '$(".shipping_promotioncode_salelay",parent.document).hide();';
			$scripts[] = '$("span#shipping_promotioncode_sale",parent.document).html("");';

			$scripts[] = '$("#shipping_promotion_code_sale",parent.document).val("");';
			$scripts[] = '$("#shipping_promotion_code_seq",parent.document).val("");';
		}

		if(isset($this->shipping_coupon_sale)){//배송비쿠폰선택시
			$scripts[] = '$("span#coupon_shipping_price, span.coupon_shipping_price",parent.document).html("<span class=\"desc\" >(- 쿠폰 '.number_format($this->shipping_coupon_sale).'</span>)");';
		}else{
			$scripts[] = '$("span#coupon_shipping_price, span.coupon_shipping_price",parent.document).html("");';
			$scripts[] = '$("#download_seq",parent.document).val("");';
			$scripts[] = '$("#shipping_coupon_sale",parent.document).val("");';
		}

		/* 결제금액이 0원일 경우 결제수단을 무통장만 활성화 */
		if($settle_price==0){
			$scripts[] = '$("input[name=\'payment\'][value=\'bank\']",parent.document).attr("checked","checked").click();';
			$scripts[] = '$("input[name=\'payment\'][value!=\'bank\']",parent.document).attr("disabled","disabled");';

			$scripts[] = '$("#payment_type",parent.document).hide();';
			$scripts[] = '$("#payment_type_zero",parent.document).show();';
			$scripts[] = '$(".bank",parent.document).hide();';
			$scripts[] = 'if($("input[name=\'depositor\']",parent.document).val() == ""){';
			$scripts[] = '$("input[name=\'depositor\']",parent.document).val("전액할인");}';
			$scripts[] = 'if(!$("select[name=\'bank\']",parent.document).find("option:selected").val()){';
			$scripts[] = '$("select[name=\'bank\']",parent.document).find("option").eq(1).attr("selected",true);}';
			$scripts[] = '$("#typereceiptlay", parent.document).hide();';
			$scripts[] = '$(".typereceiptlay", parent.document).hide();';
			$scripts[] = '$("#escrow",parent.document).hide();';
		}else{
			$scripts[] = '$("input[name=\'payment\'][value!=\'bank\']",parent.document).removeAttr("disabled");';
			$scripts[] = '$("#payment_type",parent.document).show();';
			$scripts[] = '$("#payment_type_zero",parent.document).hide();';
			$scripts[] = 'if($("input[name=\'payment\']:checked",parent.document).val()=="bank"){';
			$scripts[] = '$(".bank",parent.document).show();}';
			$scripts[] = 'if($("input[name=\'depositor\']",parent.document).val() == "전액할인"){';
			$scripts[] = '$("input[name=\'depositor\']",parent.document).val("");}';
			if		($this->_is_mobile_agent){//$this->mobileMode  ||
				if	(is_array($pg['mobileEscrow']) && count($pg['mobileEscrow']) > 0){
					if		(in_array('account', $pg['mobileEscrow']) && $pg['mobileEscrowAccountLimit'] <= $settle_price) {
						$scripts[] = '$("#escrow",parent.document).show();';
					}elseif	(in_array('virtual', $pg['mobileEscrow']) && $pg['mobileEscrowVirtualLimit'] <= $settle_price) {
						$scripts[] = '$("#escrow",parent.document).show();';
					}else{
						$scripts[] = '$("#escrow",parent.document).hide();';
					}
				}else{
					$scripts[] = '$("#escrow",parent.document).hide();';
				}
			}else{
				if	(is_array($pg['escrow']) && count($pg['escrow']) > 0){
					if		(in_array('account', $pg['escrow']) && $pg['escrowAccountLimit'] <= $settle_price) {
						$scripts[] = '$("#escrow",parent.document).show();';
					}elseif	(in_array('virtual', $pg['escrow']) && $pg['escrowVirtualLimit'] <= $settle_price) {
						$scripts[] = '$("#escrow",parent.document).show();';
					}else{
						$scripts[] = '$("#escrow",parent.document).hide();';
					}
				}else{
					$scripts[] = '$("#escrow",parent.document).hide();';
				}
			}
			$scripts[] = 'if($("input[name=\'payment\']:checked",parent.document).val()=="bank"){';
			$scripts[] = '$("#typereceiptlay", parent.document).show();}';
		}

		$scripts[] = "});";
		$scripts[] = "</script>";

		if($this->displaymode == 'coupon'){
			if( $members && ($total_shipping_price || $this->shipping_coupon_down_seq ) ) {//$_POST['download_seq']
				$shippingcoupons = $this->couponmodel->get_shipping_use_list($this->userInfo['member_seq'], $cart['total'], $total_shipping_price,$this->shipping_coupon_down_seq,$this->shipping_coupon_sale);
			}

			$html = '';
			$shippinghtml = '';

			$this->template->assign(array('cart_list'=>$cart['list'],'shippingcoupons'=>$shippingcoupons));
			$r_template_file_path = explode('/',$this->template_path());
			$r_template_file_path[2] = "_coupon.html";
			$template_file_path = implode('/',$r_template_file_path);
			$this->template->define('*', $template_file_path."?cartlist");

			if($this->shipping_coupon_sale)$this->template->assign('shipping_coupon_sale',$this->shipping_coupon_sale);
			$html = $this->template->fetch('*');
			if($shippingcoupons){
				$this->template->define('*', $template_file_path."?shippincoupon");
				$shippinghtml = $this->template->fetch('*');
			}

			$checkcoupons = 0;
			foreach($cart['list'] as $cart_key => $cart_data) {
				if($cart['list'][$cart_key]['coupons']) $checkcoupons += count($cart['list'][$cart_key]['coupons']);
			}
			if($shippingcoupons)$checkshippingcoupons = count($shippingcoupons);
			if( $checkcoupons<1 && $checkshippingcoupons<1){
				$return = array('coupon_error'=>true,'checkcoupons'=>$checkcoupons,'checkshippingcoupons'=>$checkshippingcoupons, 'coupongoods'=>'','couponshipping'=>'');
			}else{
				$return = array('coupon_error'=>false,'checkcoupons'=>$checkcoupons,'checkshippingcoupons'=>$checkshippingcoupons, 'coupongoods'=>$html,'couponshipping'=>$shippinghtml);
			}
			echo json_encode($return);
		}else{
			foreach($scripts as $script){
				echo $script."\n";
			}
		}
	}

	/* 단일배송지 실결제배송비 계산 */
	public function _calculate_single_shipping_price(&$scripts,&$cart,$total_goods_price,&$shipping,&$international_shipping){

		/* 해외 배송비 */
		$start = 0;
		if($international_shipping['goodsWeight']) foreach($international_shipping['goodsWeight'] as $key => $weight){
			$end = $weight;
			if($start < $cart['total_goods_weight'] && $end >= $cart['total_goods_weight'] ){
				$goods_row = $key;
			}elseif($start < $cart['total_goods_weight'] && $end < $cart['total_goods_weight']){//그이상의무게인경우 가장큰무게로설정
				$goods_row = $key;
			}
			$start = $weight;
		}
		$cost_key = $_POST['region'] + (count($international_shipping['region'])*$goods_row);
		$international_shipping_price = (int) $international_shipping['deliveryCost'][$cost_key];

		$total_shop_shipping_price		= (int) $cart['shop_shipping_policy']['price']; // 결제할 기본배송비
		$this->shipping_cost			= $total_shop_shipping_price;
		$total_add_shipping_price 		= 0; // 결제할 지역별추가배송비
		$total_goods_shipping_price 	= (int) $cart['shipping_price']['goods']; // 결제할 개별배송비

		// 지역별 추가 배송비
		if($shipping[0][0]['code'] == 'delivery'){
			$door2door = $shipping[0][0];

			$addDeliveryCost = 0;

			if($door2door['sigungu']) foreach($door2door['sigungu'] as $sigungu_key => $sigungu){
				if(preg_match('/'.$sigungu.'/',$_POST['recipient_address'])){
					$addDeliveryCost += $door2door['addDeliveryCost'][$sigungu_key];
				}
			}
		}

		if( $_POST['international'] == 0 ){
			if( $_POST['shipping_method'] == 'delivery' || !$_POST['shipping_method'] ){



				// 조건부 무료배송 금액차감
				if($shipping[0][0]['deliveryCostPolicy'] == 'ifpay'){
					if($total_goods_price && $shipping[0][0]['ifpayFreePrice']){
						if($shipping[0][0]['ifpayFreePrice'] <= $total_goods_price){
							$this->shipping_cost = $total_shop_shipping_price = 0;
						}
					}
				}


				// 특정상품 구매시 무료
				$orderDeliveryFree = false;
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

					// 개별 배송 상품 지역별 추가 배송비
					if($data_goods['shipping_policy']=='goods' && $data_goods['goods_kind']=='goods'){
						if($data_goods['limit_shipping_ea']){
							$cart['data_goods'][$goods_seq]['add_goods_shipping'] = (int) $addDeliveryCost * ceil($data_goods['ea'] / $data_goods['limit_shipping_ea']);
						}else{
							$cart['data_goods'][$goods_seq]['add_goods_shipping'] = (int) $addDeliveryCost;
						}

						$total_goods_add_shipping_price += $cart['data_goods'][$goods_seq]['add_goods_shipping'];
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
					$this->shipping_cost = $total_shop_shipping_price = 0;
				}


				// 지역별 추가 배송비
				if($shipping[0][0]['code'] == 'delivery'){

					if(count($cart["list"]) == 1 && $cart['box_ea'] > 1){
						$cart['box_ea'] = 1;
					}

					//$cart['shipping_price']['shop'] += (int) $addDeliveryCost * $cart['box_ea'];
					if( $order_basic_delivery ){
						$total_add_shipping_price += (int) $addDeliveryCost;
					}
				}


				$this->total_goods_add_shipping_price += $total_goods_add_shipping_price;
				$this->area_add_delivery_cost += $total_add_shipping_price;
				$this->shipping_cost += $total_add_shipping_price;


			}elseif($_POST['shipping_method'] == 'postpaid'){
				$total_goods_add_shipping_price = 0;
				$total_shop_shipping_price	= 0;
				$this->shipping_cost	= 0;
				$this->postpaid = $total_goods_shipping_price;

				if($shipping[0][0]['deliveryCostPolicy'] == 'ifpay'){
					if(!$total_goods_price || !$shipping[0][0]['ifpayFreePrice'] || $shipping[0][0]['ifpayFreePrice'] > $total_goods_price){
						$this->postpaid += (int) $shipping[0][0]['ifpostpaidDeliveryCost'];
					}
				}else{
					$this->postpaid += $shipping[0][0]['postpaidDeliveryCost'];
				}

				foreach($cart['data_goods'] as $goods_seq => $data_goods){
					if($data_goods['limit_shipping_ea']){
						$cart['data_goods'][$goods_seq]['add_goods_shipping'] = (int) $addDeliveryCost * ceil($data_goods['ea'] / $data_goods['limit_shipping_ea']);
					}else{
						$cart['data_goods'][$goods_seq]['add_goods_shipping'] = (int) $addDeliveryCost;
					}
					$total_goods_add_shipping_price += $data_goods['add_goods_shipping'];
				}
				$this->postpaid += (int) $total_goods_add_shipping_price;

			}else{
				$total_shop_shipping_price	= 0;
				$this->shipping_cost	= 0;
			}
		}else{
			$total_shop_shipping_price = $international_shipping_price;
			$this->shipping_cost = (int) $total_shop_shipping_price;
		}

		//프로모션코드 배송비할인
		$shipping_promotion_code_sale = $this->_get_shipping_promotion_code_sale($total_shop_shipping_price,$cart['total']);
		$total_shop_shipping_price = zerobase($total_shop_shipping_price-$shipping_promotion_code_sale);

		//배송비쿠폰 선택시 - 배송비 할인 본사배송상품만할인
		$shipping_coupon_sale = $this->_get_shipping_coupon_sale($total_shop_shipping_price);
		$total_shop_shipping_price = zerobase($total_shop_shipping_price-$shipping_coupon_sale);

		/* 결제할 배송비 = 기본배송비 + 추가배송비 + 개별배송비 */
		if($total_goods_shipping_price) $this->arr_goods_shipping_ck = $total_goods_shipping_price;//개별배송비 안내문구추가

		$this->total_cart_shop_shipping_price	= $total_shop_shipping_price;
		$this->total_cart_goods_shipping_price	= $total_goods_shipping_price;

		$total_shipping_price = $total_shop_shipping_price + $total_add_shipping_price + $total_goods_shipping_price + $total_goods_add_shipping_price;

		return $total_shipping_price;
	}

	// 프로모션코드 배송비할인
	public function _get_shipping_promotion_code_sale($total_shop_shipping_price, $sum_goods_price){
		if($this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))) {
			$shipping_promotions = $this->promotionmodel->get_able_download_saleprice($this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id')),$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')), $sum_goods_price, '','');
		}

		//프로모션코드 본사배송상품 배송비할인
		$this->shipping_promotion_code_sale=0;
		if($total_shop_shipping_price > 0 && $shipping_promotions){//본사배송상품
			if($shipping_promotions['sale_type'] == 'shipping_free' &&  $shipping_promotions['promotioncode_shipping_sale_max']>0) {//기본배송비무료
				if($total_shop_shipping_price < $shipping_promotions['promotioncode_shipping_sale_max']) {
					$this->shipping_promotion_code_sale = $total_shop_shipping_price;//기본배송비무료
				}else{
					$this->shipping_promotion_code_sale = $shipping_promotions['promotioncode_shipping_sale_max'];
				}
			}elseif($shipping_promotions['sale_type'] == 'shipping_won' && $shipping_promotions['promotioncode_shipping_sale']>0) {//배송비할인가
				if($total_shop_shipping_price < $shipping_promotions['promotioncode_shipping_sale']) {
					$this->shipping_promotion_code_sale = $total_shop_shipping_price;//기본배송비무료
				}else{
					$this->shipping_promotion_code_sale = $shipping_promotions['promotioncode_shipping_sale'];
				}
			}
		}

		return $this->shipping_promotion_code_sale;
	}

	// 배송비쿠폰 선택시 - 배송비 할인 본사배송상품만할인
	public function _get_shipping_coupon_sale($total_shop_shipping_price){
		$this->shipping_coupon_payment_b = false;
		if($_POST['download_seq'] && $_POST['coupon_sale']>0 && $total_shop_shipping_price ) {
			$this->shipping_coupon_down_seq = $_POST['download_seq'];
			$shippingcoupon = $this->couponmodel->get_download_coupon($this->shipping_coupon_down_seq);
			//무통장만 사용가능
			if($shippingcoupon['sale_payment'] == 'b' && $this->shipping_coupon_payment_b != true ) $this->shipping_coupon_payment_b = true;

			if($total_shop_shipping_price < $_POST['coupon_sale'] ){
				$this->shipping_coupon_sale = $total_shop_shipping_price;
			}else{
				$this->shipping_coupon_sale = $_POST['coupon_sale'];
			}
		}

		return $this->shipping_coupon_sale;
	}

	/* 복수배송지 실결제배송비 계산 */
	public function _calculate_multi_shipping_price(&$scripts,&$cart,&$shipping){

		$total_shop_goods_price = 0; // 기본배송 상품금액 합계

		$total_shop_shipping_price		= 0; // 결제할 기본배송비
		$total_add_shipping_price 		= 0; // 결제할 지역별추가배송비
		$total_goods_shipping_price 	= 0; // 결제할 개별배송비

		$this->arr_multi_shop_shipping_price = array();				// 배송지별 기본배송비
		$this->arr_multi_add_shipping_price = array();				// 배송지별 추가배송비
		$this->arr_multi_goods_shipping_price = array(); 			// 배송지별 개별배송비
		$this->arr_multi_shipping_promition_code_sale = array();	// 배송지별 프로모션코드 배송비할인
		$this->arr_multi_shipping_coupon_sale = array();			// 배송지별 쿠폰 배송비할인
		$this->arr_goods_shipping_price = array(); 					// 상품별 개별배송비

		/* 복수배송지 설정 POST값을 토대로 $cart['list']와 유사한 형태의 데이터를 생성 */
		$multiShippingItems = $this->_get_multi_shipping_item_list(&$cart);

		// 배송지별  지역추가배송비
		if($shipping[0][0]['code'] == 'delivery'){
			$door2door = $shipping[0][0];
			$addDeliveryCost = 0;
			if($door2door['sigungu']) foreach($door2door['sigungu'] as $sigungu_key => $sigungu){
				if(preg_match('/'.$sigungu.'/',$_POST['multi_recipient_address'][$multiIdx])){
					$addDeliveryCost += $door2door['addDeliveryCost'][$sigungu_key];
				}
			}
		}


		foreach($multiShippingItems as $multiIdx => $items){
			$box_ea = 0;
			$default_box_ea = false;
			$order_basic_delivery = false;
			$shipping_price['goods'] = 0;

			// 배송지별 상품개별배송비
			foreach($items as $itemIdx=>$row){
				$goods_shipping = $row['goods_shipping_policy'];

				$row['goods_shipping'] = 0;
				if($row['shipping_policy'] == 'shop'){
					$total_shop_goods_price += $row['price'];
					$default_box_ea = true;
					$shop_shipping_policy = $goods_shipping;
				}else{
					$this->arr_multi_goods_shipping_price[$multiIdx] += $goods_shipping['price'];
					$this->arr_goods_shipping_price[$row['goods_seq']] += $goods_shipping['price'];
					$box_ea += $goods_shipping['box_ea'];
				}

				// 개별 배송 상품 지역별 추가 배송비
				if($row['shipping_policy']=='goods'){
					if($row['limit_shipping_ea']){
						$row['add_goods_shipping'] = (int) $addDeliveryCost * ceil($row['ea'] / $row['limit_shipping_ea']);
					}else{
						$row['add_goods_shipping'] = (int) $addDeliveryCost;
					}
					$this->arr_add_goods_shipping[$multiIdx][$itemIdx] = $row['add_goods_shipping'];
					$this->arr_multi_add_shipping_price[$multiIdx] += (int) $row['add_goods_shipping'];
				}else{
					$order_basic_delivery = true;
				}
			}

			if($default_box_ea) $box_ea += 1;


			if($shipping[0][0]['code'] == 'delivery'){
				if($order_basic_delivery) $this->arr_multi_add_shipping_price[$multiIdx] += (int) $addDeliveryCost;
			}

			// 배송지별 기본배송비
			$this->arr_multi_shop_shipping_price[$multiIdx] = $default_box_ea ? $shop_shipping_policy['price'] : 0;

		}

		// 조건부 무료배송 금액차감
		if($shipping[0][0]['deliveryCostPolicy'] == 'ifpay'){
			if($total_shop_goods_price && $shop_shipping_policy['free']){
				if($shop_shipping_policy['free'] <= $total_shop_goods_price){
					$free_cnt = floor($total_shop_goods_price / $shop_shipping_policy['free']);
					foreach($this->arr_multi_shop_shipping_price as $multiIdx => $v){
						if($free_cnt-- > 0){
							$this->arr_multi_shop_shipping_price[$multiIdx] = 0;
						}
					}
				}
			}
		}

		// 특정상품 구매시 무료
		$orderDeliveryFree = false;
		foreach($cart['data_goods'] as $goods_seq => $data_goods){
			if($shipping[0][0]['issueGoods'] && in_array($goods_seq,$shipping[0][0]['issueGoods'])){
				$orderDeliveryFree = true;
			}
			if($shipping[0][0]['issueCategoryCode'] && in_array($data_goods['r_category'],$shipping[0][0]['issueCategoryCode'])){
				$orderDeliveryFree = true;
			}
			if($shipping[0][0]['issueBrandCode'] && in_array($data_goods['r_brand'],$shipping[0][0]['issueBrandCode'])){
				$orderDeliveryFree = true;
			}

		}
		foreach($cart['data_goods'] as $goods_seq => $data_goods){
			if(in_array($goods_seq,$shipping[0][0]['exceptIssueGoods'])){
				$orderDeliveryFree = false;
			}
		}
		if( $shipping[0][0]['orderDeliveryFree'] == 'free' && $orderDeliveryFree){
			foreach($this->arr_multi_shop_shipping_price as $multiIdx => $v){
				if($free_cnt-- == 0){
					$this->arr_multi_shop_shipping_price[$multiIdx] = 0;
				}
			}
		}

		//프로모션코드 배송비할인
		$shipping_promotion_code_sale = $this->_get_shipping_promotion_code_sale(array_sum($this->arr_multi_shop_shipping_price, $cart['total']));
		if($shipping_promotion_code_sale){
			foreach($this->arr_multi_shop_shipping_price as $multiIdx=>$v){
				if($v>=$shipping_promotion_code_sale){
					$this->arr_multi_shipping_promition_code_sale[$multiIdx] = $shipping_promotion_code_sale;
					$this->arr_multi_shop_shipping_price[$multiIdx] -= $shipping_promotion_code_sale;
					break;
				}
			}
		}

		//배송비쿠폰 선택시 - 배송비 할인 본사배송상품만할인
		$shipping_coupon_sale = $this->_get_shipping_coupon_sale(array_sum($this->arr_multi_shop_shipping_price));
		if($shipping_coupon_sale){
			foreach($this->arr_multi_shop_shipping_price as $multiIdx=>$v){
				if($v>=$shipping_coupon_sale){
					$this->arr_multi_shipping_coupon_sale[$multiIdx] = $shipping_coupon_sale;
					$this->arr_multi_shop_shipping_price[$multiIdx] -= $shipping_coupon_sale;
					break;
				}
			}
		}

		$total_shop_shipping_price = array_sum($this->arr_multi_shop_shipping_price);
		$total_add_shipping_price = array_sum($this->arr_multi_add_shipping_price);
		$total_goods_shipping_price = array_sum($this->arr_multi_goods_shipping_price);

		/* 결제할 배송비 = 기본배송비 + 추가배송비 + 개별배송비 */
		if($total_goods_shipping_price) $this->arr_goods_shipping_ck = $total_goods_shipping_price;//개별배송비 안내문구추가
		$total_shipping_price = $total_shop_shipping_price + $total_add_shipping_price + $total_goods_shipping_price;

		return $total_shipping_price;
	}

	/* 복수배송지 설정 POST값을 토대로 $cart['list']와 유사한 형태의 데이터를 생성 */
	public function _get_multi_shipping_item_list(&$cart){

		$multiShippingItems = array();

		$_POST['multiCartGoodsSeq']		= array_values($_POST['multiCartGoodsSeq']);
		$_POST['multiCartOptionSeq']	= array_values($_POST['multiCartOptionSeq']);
		$_POST['multiCartSuboptionSeq']	= array_values($_POST['multiCartSuboptionSeq']);
		$_POST['multiEaInput']			= array_values($_POST['multiEaInput']);

		foreach($_POST['multiCartGoodsSeq'] as $multiIdx=>$goodsSeqs){

			foreach($goodsSeqs as $itemIdx=>$goodsSeq){

				// 카트에서 상품정보
				foreach($cart['list'] as $item){
					if($item['goods_seq']==$goodsSeq){

						if(!$multiShippingItems[$multiIdx][$goodsSeq]){
							$multiShippingItems[$multiIdx][$goodsSeq]  = $item;
							$multiShippingItems[$multiIdx][$goodsSeq]['ea'] = 0;
							$multiShippingItems[$multiIdx][$goodsSeq]['cart_options'] = array();
							$multiShippingItems[$multiIdx][$goodsSeq]['cart_suboptions'] = array();
						}

						if($item['cart_option_seq'] == $_POST['multiCartOptionSeq'][$multiIdx][$itemIdx] && !$_POST['multiCartSuboptionSeq'][$multiIdx][$itemIdx]){
							$item['ea'] = $_POST['multiEaInput'][$multiIdx][$itemIdx];
							if($item['ea']){
								$cart_options = $item;
								unset($cart_options['cart_suboptions']);
								$multiShippingItems[$multiIdx][$goodsSeq]['ea'] += $cart_options['ea'];
								$multiShippingItems[$multiIdx][$goodsSeq]['cart_options'][] = $cart_options;
							}
						}

						foreach($item['cart_suboptions'] as $cart_suboption){
							if($cart_suboption['cart_suboption_seq'] == $_POST['multiCartSuboptionSeq'][$multiIdx][$itemIdx]){
								$cart_suboption['ea'] = $_POST['multiEaInput'][$multiIdx][$itemIdx];
								if($cart_suboption['ea']){
									$multiShippingItems[$multiIdx][$goodsSeq]['ea'] += $cart_suboption['ea'];
									$multiShippingItems[$multiIdx][$goodsSeq]['cart_suboptions'][] = $cart_suboption;
								}
							}
						}

						if(!$multiShippingItems[$multiIdx][$goodsSeq]['ea']){
							unset($multiShippingItems[$multiIdx][$goodsSeq]);
						}else {
							// 배송지별 상품개별배송비
							$goods_shipping = $this->goodsmodel->get_goods_delivery($item,$multiShippingItems[$multiIdx][$goodsSeq]['ea']);
							$multiShippingItems[$multiIdx][$goodsSeq]['goods_shipping_policy'] = $goods_shipping;
							$multiShippingItems[$multiIdx][$goodsSeq]['goods_shipping'] = 0;
							if($goods_shipping['policy'] != 'shop'){
								$multiShippingItems[$multiIdx][$goodsSeq]['goods_shipping'] = $goods_shipping['price'];
							}

						}

					}

				}

			}

		}

		return $multiShippingItems;
	}

	public function pay(){
		$this->load->model('ssl');
		$this->ssl->decode();

		$adminOrder=$_POST['adminOrder'];
		$person_seq=$_POST['person_seq'];
		$this->calculate($adminOrder, $person_seq);

		## 모바일결제 : 체크값 오류시 callback으로 결제창 layer 숨김 처리
		if($_POST['mobilenew'] == "y"){
			$pg_cancel_script = $this->pg_cancel_script();
		}else{
			$pg_cancel_script = "";
		}

		if($_POST['gift_use'] == "Y"){
			### GIFT
			$gift_categorys = array();
			$gift_goods = array();
			foreach($this->cart['data_goods'] as $goods_seq => $data){
				$gift_goods[] 		= $goods_seq;
				$gift['goods_seq']	= $goods_seq;
				$gift['ea']			= $data['ea'];
				$gift['tot_price']	= $data['price'];
				$gift_loop[]		= $gift;
				foreach($data['r_category'] as $category_code){
					$gift_categorys[] = $category_code;
					$category[] = $category_code;
				}
			}

			$gift = $this->ordermodel->get_gift_event($gift_categorys, $gift_goods, $gift_loop, $this->cart['total']);

			foreach($gift['gloop'] as $v){
				if(count($_POST['gift_'.$v['gift_seq']]) > $_POST['gift_'.$v['gift_seq'].'_limit']){
					$callback = "";
					$callback = $pg_cancel_script;
					openDialogAlert($v['title']."이벤트의 사은품은 최대 ".$_POST['gift_'.$v['gift_seq'].'_limit']."개까지 선택하실 수 있습니다.",400,140,'parent',$callback);
					exit;
				}

			}
		}

		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$this->load->library('validation');
		$this->load->model('statsmodel');

		/* 결제금액이 0원인 경우 무통장으로 처리 */
		if( $this->settle_price==0 ){
			$_POST['payment'] = "bank";
		}

		//쿠폰>사용제한>무통장만가능
		if( $this->cart['coupon_sale_payment_b'] > 0 && $_POST['payment'] != 'bank'){
			//$callback = ($this->siteType == 'mobile')?"if(parent.document.getElementsByName('payment')[0]) parent.document.getElementsByName('payment')[0].focus();":"";
			$callback = $pg_cancel_script;
			openDialogAlert("현재 무통장 전용 쿠폰을 사용하셨습니다.<br />결제수단을 무통장으로 변경해 주세요!",400,140,'parent',$callback);
			exit;
		}

		//쿠폰>사용제한>모바일 화면에서만 가능
		if( $this->cart['coupon_sale_agent_m'] > 0 && !$this->_is_mobile_agent ){
			//$callback = "";
			//openDialogAlert("현재 모바일/태블릿 전용 쿠폰을 사용하셨습니다.<br />모바일/태블릿기기에서 이용해 주세요!",400,140,'parent',$callback);
			//exit;
		}

		// 카드, 휴대폰일경우 매출증빙 관련 초기화 // 카카오페이 추가 :: 2015-02-17 lwh
		if($_POST['payment'] == 'card' || $_POST['payment'] == 'cellphone' || $_POST['payment'] == 'kakaopay')
			$_POST['typereceipt'] = 0;

		$_POST["recipient_email"] = (isset($_POST["recipient_email"]))?$_POST["recipient_email"]:$_POST["order_email"];

		$is_coupon = false;
		$is_goods = false;
		foreach($this->cart['data_goods']  as $key => $data){
			if( $data['goods_kind'] == 'coupon' )	$is_coupon = true;
			if( $data['goods_kind'] == 'goods' )	$is_goods = true;
		}

		$this->validation->set_rules('order_user_name', '주문자','trim|required|max_length[20]|xss_clean');
		if($_POST['order_phone'][0]||$_POST['order_phone'][1]||$_POST['order_phone'][2]) $this->validation->set_rules('order_phone[]', '주문자 유선전화','trim|numeric|max_length[4]|xss_clean');
		$this->validation->set_rules('order_cellphone[]', '주문자 휴대폰','trim|required|max_length[4]|xss_clean');
		$this->validation->set_rules('order_email', '이메일','trim|required|valid_email|max_length[100]|xss_clean');

		// 다중배송일경우
		if($this->shipping_order){
			if($_POST['multiShippingChk']){
				$this->validation->set_rules('multi_recipient_user_name[]', '받는 분','trim|required|max_length[15]|xss_clean');
				if($_POST['multi_is_goods']){
					foreach($_POST['multi_is_goods'] as $k => $v){
						if	($v > 0){
							$this->validation->set_rules('multi_recipient_zipcode[0]['.$k.']', '우편번호','trim|required|max_length[3]|xss_clean');
							$this->validation->set_rules('multi_recipient_zipcode[1]['.$k.']', '우편번호','trim|required|max_length[3]|xss_clean');
							$this->validation->set_rules('multi_recipient_address['.$k.']', '주소','trim|max_length[255]|required|xss_clean');
							$this->validation->set_rules('multi_recipient_address_detail['.$k.']', '나머지주소','trim|max_length[255]|xss_clean');
						}
					}
				}
				$this->validation->set_rules('multi_recipient_phone[0][]', '받는이 유선전화','trim|required|numeric|max_length[4]|xss_clean');
				$this->validation->set_rules('multi_recipient_phone[1][]', '받는이 유선전화','trim|required|numeric|max_length[4]|xss_clean');
				$this->validation->set_rules('multi_recipient_phone[2][]', '받는이 유선전화','trim|required|numeric|max_length[4]|xss_clean');
				$this->validation->set_rules('multi_recipient_cellphone[0][]', '받는이 핸드폰','trim|required|numeric|max_length[4]|xss_clean');
				$this->validation->set_rules('multi_recipient_cellphone[1][]', '받는이 핸드폰','trim|required|numeric|max_length[4]|xss_clean');
				$this->validation->set_rules('multi_recipient_cellphone[2][]', '받는이 핸드폰','trim|required|numeric|max_length[4]|xss_clean');
				$this->validation->set_rules('multi_recipient_email[]', '이메일','trim|required|valid_email|max_length[100]|xss_clean');
			}
			// 일반배송일경우
			else{
				if($is_goods){
					if($_POST['international'] == 1){
						$this->validation->set_rules('international_address', '주소','trim|max_length[255]|required|xss_clean');
						$this->validation->set_rules('recipient_user_name[]', '받는 분','trim|required|max_length[15]|xss_clean');
						$this->validation->set_rules('international_recipient_phone[]', '받는이 유선전화','trim|required|numeric|xss_clean');
						$this->validation->set_rules('international_recipient_cellphone[]', '받는이 핸드폰','trim|required|numeric|xss_clean');
						/**
						$this->validation->set_rules('international_town_city', '시도','trim|max_length[255]|required|xss_clean');
						$this->validation->set_rules('international_county', '주','trim|max_length[255]|required|xss_clean');
						$this->validation->set_rules('international_postcode', '우편번호','trim|required|max_length[3]|xss_clean');
						$this->validation->set_rules('international_country', '국가','trim|max_length[255]|required|xss_clean');
						**/
					}else{
						$this->validation->set_rules('recipient_zipcode[]', '우편번호','trim|required|max_length[3]|xss_clean');
						$this->validation->set_rules('recipient_address', '주소','trim|max_length[255]|required|xss_clean');
						$this->validation->set_rules('recipient_address_detail', '나머지주소','trim|max_length[255]|xss_clean');
						$this->validation->set_rules('recipient_user_name[]', '받는 분','trim|required|max_length[15]|xss_clean');
						$this->validation->set_rules('recipient_phone[]', '받는이 유선전화','trim|required|numeric|max_length[4]|xss_clean');
						$this->validation->set_rules('recipient_cellphone[]', '받는이 핸드폰','trim|required|numeric|max_length[4]|xss_clean');
					}
				}

				if($is_coupon){
					$_POST["recipient_email"] = $_POST["recipient_email"] ? $_POST["recipient_email"] : '';
					$this->validation->set_rules('recipient_email', '받는분 이메일','trim|required|valid_email|max_length[100]|xss_clean');
					$this->validation->set_rules('recipient_user_name', '받는분','trim|required|max_length[15]|xss_clean');
					$this->validation->set_rules('recipient_phone[]', '받는분 전화','trim|required|numeric|max_length[4]|xss_clean');
					$this->validation->set_rules('recipient_cellphone[]', '받는분 휴대폰','trim|required|numeric|max_length[4]|xss_clean');
				}
			}
		}

		$this->validation->set_rules('payment', '결제방법','trim|required|xss_clean');
		if($_POST['payment'] == 'bank' && $this->settle_price>0 ){
			$this->validation->set_rules('depositor', '입금자명','trim|required|xss_clean');
			$this->validation->set_rules('bank', '입금은행','trim|required|xss_clean');
		}
		$this->validation->set_rules('emoney', '적립금','trim|numeric|xss_clean');
		$this->validation->set_rules('cash', '이머니','trim|numeric|xss_clean');

		if($_POST["email"] == ""){
			$_POST["email"] = $_POST["order_email"];
		}

		if($_POST["person"] == ""){
			$_POST["person"] = $_POST["order_user_name"];
		}

		if($_POST["phone"] == ""){
			$_POST["phone"] = join("", $_POST["order_cellphone"]);
		}

		if($_POST["typereceipt"] == "2"){
			if($_POST["cuse"] == "0"){
				$this->validation->set_rules('creceipt_number[0]', '인증번호','trim|numeric|xss_clean');
			}else{
				$this->validation->set_rules('creceipt_number[1]', '사업자번호','trim|numeric|xss_clean');
			}
			if(isset($_POST["sales_email"])){
				$this->validation->set_rules('sales_email', '매출증빙 이메일','trim|required|valid_email|xss_clean');
			}
		}else if($_POST["typereceipt"] == "1"){
			$_POST["validation_biz_no"] = str_replace("-", "", $_POST["busi_no"]);
			$this->validation->set_rules('co_name', '상호명','trim|required|xss_clean');
			$this->validation->set_rules('validation_biz_no', '사업자번호','trim|required|numeric|xss_clean');
			$this->validation->set_rules('co_ceo', '대표자명','trim|required|xss_clean');
			$this->validation->set_rules('co_status', '업태','trim|required|xss_clean');
			$this->validation->set_rules('co_type', '업종','trim|required|xss_clean');
			$this->validation->set_rules('co_zipcode[]', '우편번호','trim|numeric|xss_clean');
			$this->validation->set_rules('co_address', '주소','trim|required|xss_clean');
			$this->validation->set_rules('person', '담당자명','trim|required|xss_clean');
			$this->validation->set_rules('email', '이메일','trim|required|xss_clean');
			$this->validation->set_rules('phone', '연락처','trim|required|numeric|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();".$pg_cancel_script;
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		//프로모션코드3 -> 구매시
		if($this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))) {
			$promotioncode = $this->promotionmodel->get_able_download_saleprice_pay($this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id')),$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')));
		}

		/* 배송비 정책 체크 */
		if(!$this->shipping_order && $this->config_system['service']['code']!='P_STOR'){
			openDialogAlert("택배/배송비 정책이 설정되지 않았습니다.",400,100,'parent',$pg_cancel_script);
			exit;
		}

		if($adminOrder != "admin"){
			/* 결제중 버튼으로 변경*/
			echo '<script>
			$("div.pay_layer",parent.document).eq(0).hide();
			$("div.pay_layer",parent.document).eq(1).show();
			</script>';
		}

		if($person_seq != ""){
			$query = $this->db->query("select * from fm_person where person_seq='".$person_seq."'");
			$res = $query->row_array();
			$_POST['enuri'] = $res['enuri'];
			$_POST['admin_memo'] = $res['admin_memo'];
		}

		/* 주문 저장 */
		$this->db->trans_begin();
		$rollback = false;
		$order_seq = $this->ordermodel->insert_order($this->settle_price, $this->shipping_cost, $this->shipping_order, $this->freeprice,$this->postpaid);

		$cart_option_real_seq = array();
		$cart_suboption_real_seq = array();

		// 주문 통계 저장
		$this->statsmodel->insert_order_stats($order_seq);

		// 장바구니 수량 체크
		if( count($this->cart['list'])==0 || !$this->cart['list']) $rollback = true;

		$r_except_item	= array();
		$total_ea		= 0;
		$goods_info	= array();

		foreach($this->cart['list'] as $k => $data){

			if( ! $r_except_item[$data['goods_seq']] ){

				$goods_infos	= array();

	  			$insert_params = array();
				$insert_params['order_seq'] 	= $order_seq;
				$insert_params['goods_seq'] 	= $data['goods_seq'];
				$insert_params['goods_code'] 	= $data['goods_code'];
				$insert_params['image'] 		= $data['image'];
				$insert_params['goods_name'] 	= $data['goods_name'];
				$insert_params['multi_discount_ea']	= $data['multi_discount_ea'];
				$insert_params['tax']				= $data['tax'];
				$insert_params['goods_type']		= $data['goods_type'];

				$insert_params['individual_refund']	= $data['individual_refund']?$data['individual_refund']:'0';
				$insert_params['individual_refund_inherit']	= $data['individual_refund_inherit']?$data['individual_refund_inherit']:'0';
				$insert_params['individual_export']	= $data['individual_export']?$data['individual_export']:'0';
				$insert_params['individual_return']	= $data['individual_return']?$data['individual_return']:'0';

				// 개별배송비 계산
				if($_POST['international'] == 0){
					if($_POST['multiShippingChk']){
						$insert_params['goods_shipping_cost'] 	= (int) $this->arr_goods_shipping_price[$data['goods_seq']];
					}else{
						$insert_params['goods_shipping_cost'] = $this->cart['data_goods'][$data['goods_seq']]['goods_shipping'];
						$insert_params['goods_shipping_cost'] += $this->cart['data_goods'][$data['goods_seq']]['add_goods_shipping'];
					}
					$insert_params['shipping_policy'] 		= $this->cart['data_goods'][$data['goods_seq']]['shipping_policy']; // 배송정책
					$insert_params['shipping_unit'] 		= $this->cart['data_goods'][$data['goods_seq']]['limit_shipping_ea']; // 합포장단위
					$insert_params['basic_shipping_cost'] 	=  $this->cart['data_goods'][$data['goods_seq']]['limit_shipping_price']; // 기본포장 배송비
					$insert_params['add_shipping_cost'] 	=  $this->cart['data_goods'][$data['goods_seq']]['limit_shipping_subprice']; // 추가포장배송비
				}else if($_POST['international'] == 1){
					$insert_params['shipping_policy'] = 'shop';
				}

				//쇼셜쿠폰상품저장@2013-10-22
				$insert_params['goods_kind']		= ($data['goods_kind'])?$data['goods_kind']:'goods';
				$insert_params['socialcp_input_type']= $this->cart['data_goods'][$data['goods_seq']]['socialcp_input_type'];
				$insert_params['socialcp_use_return']= $this->cart['data_goods'][$data['goods_seq']]['socialcp_use_return'];
				$insert_params['socialcp_use_emoney_day']= $this->cart['data_goods'][$data['goods_seq']]['socialcp_use_emoney_day'];
				$insert_params['socialcp_use_emoney_percent']= $this->cart['data_goods'][$data['goods_seq']]['socialcp_use_emoney_percent'];

				$insert_params['social_goods_group'] 	= $this->cart['data_goods'][$data['goods_seq']]['social_goods_group'];//쿠폰상품그룹

				$insert_params['socialcp_cancel_use_refund'] 				= $this->cart['data_goods'][$data['goods_seq']]['socialcp_cancel_use_refund'];
				$insert_params['socialcp_cancel_payoption'] 				= $this->cart['data_goods'][$data['goods_seq']]['socialcp_cancel_payoption'];
				$insert_params['socialcp_cancel_payoption_percent'] 	= $this->cart['data_goods'][$data['goods_seq']]['socialcp_cancel_payoption_percent'];

				if( $this->cart['data_goods'][$data['goods_seq']]['event']['event_seq']) {//이벤트 고유번호 추가
					$insert_params['event_seq'] = $this->cart['data_goods'][$data['goods_seq']]['event']['event_seq'];
				}
				$this->db->insert('fm_order_item', $insert_params);
				$item_seq = $this->db->insert_id();

				/* 상품 대표카테고리 정보 */
				$insert_params = array();
				$insert_params['item_seq'] = $item_seq;
				foreach($data['r_category'] as $i=>$category_code){
					$query = $this->db->query("select title from fm_category where category_code='{$category_code}'");
					$res = $query->row_array();
					if($res['title'] && $i<4 ){
						$insert_params['title'.($i+1)] = $res['title'];
						$insert_params['depth']++;
					}
				}
				$this->db->insert('fm_order_item_category', $insert_params);

				if( $data['goods_kind'] == 'coupon' ) {
					//쇼셜쿠폰상품 주문취소설정 @2013-10-22
					$this->ordermodel->order_insert_socialcp_cancel($data['goods_seq'], $order_seq, $item_seq);
				}
				//$r_except_item[$data['goods_seq']] = $item_seq;
			}
			$r_except_item[$data['goods_seq']] = $item_seq;

			if($goods_name == "") {
				$goods_name = $data['goods_name'];
				$pg_goods_seq = $data['goods_seq'];
				$pg_goods_image = $data['image'];
			}

			//if	($r_except_item[$data['goods_seq']])
			//	$item_seq	= $r_except_item[$data['goods_seq']];

			$insert_params = array();
			$insert_params['order_seq'] 	= $order_seq;
			$insert_params['item_seq'] 		= $r_except_item[$data['goods_seq']];
			$insert_params['step'] 			= "0";
			$insert_params['price'] 		= $data['price'];
			$insert_params['ori_price'] 	= $data['ori_price'];
			$insert_params['org_price'] 	= $data['org_price'];
			//$insert_params['sale_price'] 	= (int) $data['sale_price'];
			$insert_params['member_sale'] 	= $data['member_sale_unit'];

			// 기본할인 내역
			$insert_params['basic_sale']		= $data['basic_sale'];
			$insert_params['event_sale_target']	= $data['event_sale_target'];
			$insert_params['event_sale']		= $data['event_sale'];
			$insert_params['multi_sale']		= $data['multi_sale'];

			###
			$insert_params['reserve']		= (int) ($data['reserve_one']);
			$insert_params['reserve_log'] 	= $data['reserve_log'];
			$insert_params['point']			= (int) ($data['point_one']);
			$insert_params['point_log'] 	= $data['point_log'];

			//상품할인쿠폰
			$insert_params['download_seq']	= $data['download_seq'];
			$insert_params['coupon_sale']	= $data['coupon_sale'];

			//상품할인프로모션코드
			$insert_params['promotion_code_seq']	= $data['promotion_code_seq'];
			$insert_params['promotion_code_sale']	= $data['promotion_code_sale'];

			$insert_params['fblike_sale']	= $data['fblike_sale'];
			$insert_params['mobile_sale']	= $data['mobile_sale'];

			// 유입경로 할인
			$insert_params['referersale_seq']	= $data['referersale_seq'];
			$insert_params['referer_sale']		= $data['referer_sale'];

			$insert_params['consumer_price']	= (int) $data['consumer_price'];
			$insert_params['supply_price'] 		= (int) $data['supply_price'];
			$insert_params['ea'] 			= $data['ea'];
			$insert_params['title1'] 		= $data['title1'];
			$insert_params['option1'] 		= $data['option1'];
			$insert_params['title2'] 		= $data['title2'];
			$insert_params['option2'] 		= $data['option2'];
			$insert_params['title3'] 		= $data['title3'];
			$insert_params['option3'] 		= $data['option3'];
			$insert_params['title4'] 		= $data['title4'];
			$insert_params['option4'] 		= $data['option4'];
			$insert_params['title5'] 		= $data['title5'];
			$insert_params['option5'] 		= $data['option5'];
			$insert_params['reserve_log'] 	= $data['reserve_log'];

			//list($data['optioncode1'],$data['optioncode2'],$data['optioncode3'],$data['optioncode4'],$data['optioncode5']) = $this->goodsmodel->get_goods_option_code(

			//특수정보 관련 추가@2013-10-22
			list($data['optioncode1'],$data['optioncode2'],$data['optioncode3'],$data['optioncode4'],$data['optioncode5'],$data['color'],$data['zipcode'],$data['address_type'],$data['address'],$data['address_street'],$data['addressdetail'],$data['biztel'],$data['coupon_input'],$data['codedate'],$data['sdayinput'],$data['fdayinput'],$data['dayauto_type'],$data['sdayauto'],$data['fdayauto'],$data['dayauto_day'],$data['newtype'],$data['address_commission']) = $this->goodsmodel->get_goods_option_code(
				$data['goods_seq'],
				$data['option1'],
				$data['option2'],
				$data['option3'],
				$data['option4'],
				$data['option5']
			);

			$insert_params['newtype']							= $data['newtype'];
			$insert_params['color']								= $data['color'];
			$insert_params['zipcode']							= $data['zipcode'];
			$insert_params['address_type']					= $data['address_type'];
			$insert_params['address']							= $data['address'];
			$insert_params['address_street']					= $data['address_street'];
			$insert_params['addressdetail']					= $data['addressdetail'];
			$insert_params['biztel']								= $data['biztel'];
			$insert_params['address_commission']		= $data['address_commission'];

			//쇼셜쿠폰상품의 1장값어치와 유효기간 계산하기
			//if( $data['goods_kind'] == 'coupon' ) {//}
			$insert_params['coupon_input']			= $data['coupon_input'];//쇼셜쿠폰의 1장값어치 횟수-금액
			$insert_params['coupon_input_one']		= ( $this->cart['data_goods'][$data['goods_seq']]['socialcp_input_type'] == 'pass' )?$data['price']/$data['coupon_input']:$data['coupon_input'];//쇼셜쿠폰의 1회 금액
			$insert_params['codedate']			= $data['codedate'];
			$insert_params['sdayinput']			= $data['sdayinput'];
			$insert_params['fdayinput']			= $data['fdayinput'];
			$insert_params['dayauto_type']		= $data['dayauto_type'];
			$insert_params['sdayauto']			= $data['sdayauto'];
			$insert_params['fdayauto']			= $data['fdayauto'];
			$insert_params['dayauto_day']		= $data['dayauto_day'];

			$insert_params['optioncode1'] = $data['optioncode1'];
			$insert_params['optioncode2'] = $data['optioncode2'];
			$insert_params['optioncode3'] = $data['optioncode3'];
			$insert_params['optioncode4'] = $data['optioncode4'];
			$insert_params['optioncode5'] = $data['optioncode5'];
			$insert_params['goods_code'] = $data['goods_code'].$data['optioncode1'].$data['optioncode2'].$data['optioncode3'].$data['optioncode4'].$data['optioncode5'];//조합된상품코드

			if($data['price'] && $data['commission_rate'])
			$insert_params['commission_price'] = $data['price']*(100-$data['commission_rate'])/100;

			$this->db->insert('fm_order_item_option', $insert_params);
			$item_option_seq = $this->db->insert_id();
			if($item_option_seq){
				$cart_option_real_seq[$data['cart_option_seq']] = array(
					'order_item_seq' => $r_except_item[$data['goods_seq']],
					'order_item_option_seq' => $item_option_seq
				);
			}

			## kcp escrow시 사용
			$goods_infos['seq']			= $item_option_seq;
			$goods_infos['ordr_numb']	= $order_seq;
			$goods_infos['good_name']	= preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",$data['goods_name']);
			$goods_infos['good_cntx']	= $insert_params['ea'];
			$goods_infos['good_amtx']	= $insert_params['price'];
			$goods_info[] = $goods_infos;

  			if($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $data_suboptions){
					if($data_suboptions['suboption_title'] && $data_suboptions['suboption']){
						$insert_params = array();
						$insert_params['order_seq']	= $order_seq;
						$insert_params['item_seq'] 	= $r_except_item[$data['goods_seq']];
						$insert_params['item_option_seq'] 	= $item_option_seq;
						$insert_params['step'] 		= "0";
						$insert_params['price'] 	= (int) $data_suboptions['price'];
						$insert_params['org_price'] 	= $data_suboptions['price'];
						//$insert_params['sale_price'] 	= (int) $data_suboptions['sale_price'];
						$insert_params['member_sale'] 	= (int) $data_suboptions['member_sale_unit'];

						// 기본할인 내역
						$insert_params['basic_sale']		= $data_suboptions['basic_sale'];
						$insert_params['event_sale_target']	= $data_suboptions['event_sale_target'];
						$insert_params['event_sale']		= $data_suboptions['event_sale'];
						$insert_params['multi_sale']		= $data_suboptions['multi_sale'];

						$insert_params['point']			= (int) ($data_suboptions['point']/$data_suboptions['ea']);
						$insert_params['reserve'] = (int) ($data_suboptions['reserve']/$data_suboptions['ea']);

						/* 구매 시 적립금액 제한 조건 추가 leewh 2014-06-25 */
						if ($data_suboptions['reserve_log']) {
							$insert_params['reserve_log'] = $data_suboptions['reserve_log'];
						}

						$insert_params['consumer_price']	= (int) $data_suboptions['consumer_price'];
						$insert_params['supply_price'] 		= (int) $data_suboptions['supply_price'];
						$insert_params['ea'] 		= $data_suboptions['ea'];
						$insert_params['title'] 	= $data_suboptions['suboption_title'];
						$insert_params['suboption'] = $data_suboptions['suboption'];

						//특수정보 관련 추가@2013-10-22
						list($data_suboptions['suboption_code'],$data_suboptions['color'],$data_suboptions['zipcode'],$data_suboptions['address_type'],$data_suboptions['address'],$data_suboptions['address_street'],$data_suboptions['addressdetail'],$data_suboptions['biztel'],$data_suboptions['coupon_input'],$data_suboptions['codedate'],$data_suboptions['sdayinput'],$data_suboptions['fdayinput'],$data_suboptions['dayauto_type'],$data_suboptions['sdayauto'],$data_suboptions['fdayauto'],$data_suboptions['dayauto_day'],$data_suboptions['newtype']) = $this->goodsmodel->get_goods_suboption_code($data['goods_seq'],$data_suboptions['suboption_title'],$data_suboptions['suboption']);
						//$data_suboptions['suboption_code'] = $this->goodsmodel->get_goods_suboption_code($data['goods_seq'],$data_suboptions['suboption_title'],$data_suboptions['suboption']);

						$insert_params['newtype']			= $data_suboptions['newtype'];
						$insert_params['color']				= $data_suboptions['color'];
						$insert_params['zipcode']			= $data_suboptions['zipcode'];
						$insert_params['address_type']	= $data_suboptions['address_type'];
						$insert_params['address']			= $data_suboptions['address'];
						$insert_params['address_street']	= $data_suboptions['address_street'];
						$insert_params['addressdetail']	= $data_suboptions['addressdetail'];
						$insert_params['biztel']				= $data_suboptions['biztel'];
						$insert_params['coupon_input']	= $data_suboptions['coupon_input'];//쇼셜쿠폰의 1장값어치 횟수-금액
						$insert_params['coupon_input_one']	= ( $this->cart['data_goods'][$data['goods_seq']]['socialcp_input_type'] == 'pass' )?($data['price']/$data['coupon_input']):$data['coupon_input'];//쇼셜쿠폰의 1회 금액

						$insert_params['codedate']			= $data_suboptions['codedate'];
						$insert_params['sdayinput']		= $data_suboptions['sdayinput'];
						$insert_params['fdayinput']			= $data_suboptions['fdayinput'];
						$insert_params['dayauto_type']	= $data_suboptions['dayauto_type'];
						$insert_params['sdayauto']			= $data_suboptions['sdayauto'];
						$insert_params['fdayauto']			= $data_suboptions['fdayauto'];
						$insert_params['dayauto_day']	= $data_suboptions['dayauto_day'];

						$insert_params['suboption_code'] = $data_suboptions['suboption_code'];
						$insert_params['goods_code'] 		= $data['goods_code'].$data_suboptions['suboption_code'];//조합된상품코드

						if($data_suboptions['price'] && $data_suboptions['commission_rate'])
						$insert_params['commission_price'] = $data_suboptions['price']*(100-$data_suboptions['commission_rate'])/100;

						$this->db->insert('fm_order_item_suboption', $insert_params);
						$item_suboption_seq = $this->db->insert_id();
						if($item_suboption_seq){
							$cart_suboption_real_seq[$data_suboptions['cart_suboption_seq']] = array(
								'order_item_seq' => $item_seq,
								'order_item_suboption_seq' => $item_suboption_seq
							);
						}
					}
				}
			}
			if(!$cart_option_real_seq && !$cart_suboption_real_seq){
				$rollback = true;
			}

			/* 추가입력옵션 */
			if($data['cart_inputs']){
				foreach($data['cart_inputs'] as $data_inputs){
					$insert_params = array();
					if( $data_inputs['input_value'] ){
						$insert_params['order_seq'] = $order_seq;
						$insert_params['item_seq'] 	= $r_except_item[$data['goods_seq']];
						$insert_params['item_option_seq'] 	= $item_option_seq;
						$insert_params['type'] 		= $data_inputs['type'];
						$insert_params['title'] 	= $data_inputs['input_title'];
						$insert_params['value'] 	= $data_inputs['input_value'];
						$this->db->insert('fm_order_item_input', $insert_params);
					}

				}
			}

		}

		$gift_option_real_seq = array();

		### GIFT
		if($_POST['gift_use']=='Y'){

			foreach($_POST['lot_gifts'] as $k){
				$gifts_params['gift_seq'] 	= $k;
				$gifts_params['order_seq'] 		= $order_seq;
				$gifts_params['regdate'] 		= date("Y-m-d H:i:s");
				$this->db->insert('fm_gift_lot', $gifts_params);
			}

			foreach($_POST['gifts'] as $k){
				$nm = "gift_".$k;
				if(is_array($_POST[$nm])){
					for($i=0; $i<count($_POST[$nm]); $i++){
						$gift_seq = $_POST[$nm][$i];
						if($gift_seq){
							unset($gift_params);
							$gift_params['order_seq'] 	= $order_seq;
							$gift_params['goods_seq']	= $gift_seq;
							$gift_params['image']		= get_gift_image($gift_seq,'thumbCart');
							$gift_params['goods_name']	= get_gift_name($gift_seq);
							$gift_params['goods_type']	= 'gift';
							$this->db->insert('fm_order_item', $gift_params);
							$item_seq = $this->db->insert_id();

							unset($gift_params);
							$gift_params['order_seq'] 	= $order_seq;
							$gift_params['item_seq'] 	= $item_seq;
							$gift_params['step'] 		= "0";
							$gift_params['price'] 		= "0";
							$gift_params['ori_price'] 	= "0";
							$gift_params['ea'] 			= "1";
							$this->db->insert('fm_order_item_option', $gift_params);

							$gift_option_real_seq[$gift_seq] = array(
								'order_item_seq' => $item_seq,
								'order_item_option_seq' => $this->db->insert_id()
							);
						}

					}
				}else{
					$gift_seq = $_POST[$nm];

					if($gift_seq){
						unset($gift_params);
						$gift_params['order_seq'] 	= $order_seq;
						$gift_params['goods_seq']	= $gift_seq;
						$gift_params['image']		= get_gift_image($gift_seq,'thumbCart');
						$gift_params['goods_name']	= get_gift_name($gift_seq);
						$gift_params['goods_type']	= 'gift';
						$this->db->insert('fm_order_item', $gift_params);
						$item_seq = $this->db->insert_id();

						unset($gift_params);
						$gift_params['order_seq'] 	= $order_seq;
						$gift_params['item_seq'] 	= $item_seq;
						$gift_params['step'] 		= "0";
						$gift_params['price'] 		= "0";
						$gift_params['ori_price'] 	= "0";
						$gift_params['ea'] 			= "1";
						$this->db->insert('fm_order_item_option', $gift_params);

						$gift_option_real_seq[$gift_seq] = array(
							'order_item_seq' => $item_seq,
							'order_item_option_seq' => $this->db->insert_id()
						);
					}
				}
			}
		}

		// 다중배송지 저장
		if($_POST['multiShippingChk']){
			$this->db->query("delete from fm_order_shipping where order_seq=?",$order_seq);
			$this->db->query("delete from fm_order_shipping_item where order_seq=?",$order_seq);
			$this->db->query("delete from fm_order_shipping_item_option where order_seq=?",$order_seq);

			$multiShippingItems = $this->_get_multi_shipping_item_list(&$this->cart);

			foreach($multiShippingItems as $multiIdx => $items){

				// 배송지별 배송비
				$shipping_cost = $this->arr_multi_shop_shipping_price[$multiIdx]
				+ $this->arr_multi_add_shipping_price[$multiIdx];

				// 배송지별 프로모션코드 배송비할인
				$shipping_promotion_code_sale = $this->arr_multi_shipping_promition_code_sale[$multiIdx];

				// 배송지별 쿠폰 배송비할인
				$shipping_coupon_sale = $this->arr_multi_shipping_coupon_sale[$multiIdx];

				$multi_insert_params = array();
				$multi_insert_params['order_seq']					= $order_seq;
				$multi_insert_params['regist_date']					= date('Y-m-d H:i:s');
				$multi_insert_params['recipient_user_name']			= $_POST['multi_recipient_user_name'][$multiIdx];
				$multi_insert_params['recipient_phone'] 			= $_POST['multi_recipient_phone'][0][$multiIdx].'-'.$_POST['multi_recipient_phone'][1][$multiIdx].'-'.$_POST['multi_recipient_phone'][2][$multiIdx];
				$multi_insert_params['recipient_cellphone'] 		= $_POST['multi_recipient_cellphone'][0][$multiIdx].'-'.$_POST['multi_recipient_cellphone'][1][$multiIdx].'-'.$_POST['multi_recipient_cellphone'][2][$multiIdx];
				$multi_insert_params['recipient_zipcode'] 			= $_POST['multi_recipient_zipcode'][0][$multiIdx].'-'.$_POST['multi_recipient_zipcode'][1][$multiIdx];
				$multi_insert_params['recipient_address_type'] 	= (($_POST['multi_recipient_address_type'][$multiIdx]))?$_POST['multi_recipient_address_type'][$multiIdx]:"zibun";
				$multi_insert_params['recipient_address'] 				= $_POST['multi_recipient_address'][$multiIdx];
				$multi_insert_params['recipient_address_street'] 	= $_POST['multi_recipient_address_street'][$multiIdx];
				$multi_insert_params['recipient_address_detail'] 	= $_POST['multi_recipient_address_detail'][$multiIdx];
				$multi_insert_params['memo'] = $_POST['multi_memo'][$multiIdx];
				$multi_insert_params['shipping_cost']				= $shipping_cost;
				$multi_insert_params['shipping_promotion_code_sale']= $shipping_promotion_code_sale;
				$multi_insert_params['shipping_coupon_sale']		= $shipping_coupon_sale;
				$multi_insert_params['area_add_delivery_cost']		= $this->arr_multi_add_shipping_price[$multiIdx];
				$multi_insert_params['recipient_email']			= $_POST['multi_recipient_email'][$multiIdx];

				$this->db->insert('fm_order_shipping', $multi_insert_params);
				$shipping_seq = $this->db->insert_id();

				//최근배송지 저장 (로그인한 경우만)
				$this->ordermodel->insert_multi_delivery_address($multi_insert_params);

				foreach($items as $itemIdx=>$row){

					$item_insert_params = array();
					$item_insert_params['shipping_seq'] = $shipping_seq;
					$item_insert_params['order_seq'] = $order_seq;
					if($row['cart_options']){
						$item_insert_params['order_item_seq']			= $cart_option_real_seq[$row['cart_options'][0]['cart_option_seq']]['order_item_seq'];
					}
					if($row['cart_suboptions']){
						$item_insert_params['order_item_seq']			= $cart_suboption_real_seq[$row['cart_suboptions'][0]['cart_suboption_seq']]['order_item_seq'];
					}
					$item_insert_params['goods_shipping_cost'] = $row['goods_shipping'];
					$item_insert_params['add_goods_shipping'] = $this->arr_add_goods_shipping[$multiIdx][$itemIdx];

					$this->db->insert('fm_order_shipping_item', $item_insert_params);
					$shipping_item_seq = $this->db->insert_id();

					foreach($row['cart_options'] as $cart_option){
						$multi_insert_params = array();
						$multi_insert_params['shipping_seq']				= $shipping_seq;
						$multi_insert_params['shipping_item_seq']			= $shipping_item_seq;
						$multi_insert_params['order_seq']					= $order_seq;
						$multi_insert_params['ea']							= $cart_option['ea'];
						$multi_insert_params['order_item_seq']				= $cart_option_real_seq[$cart_option['cart_option_seq']]['order_item_seq'];
						$multi_insert_params['order_item_option_seq']		= $cart_option_real_seq[$cart_option['cart_option_seq']]['order_item_option_seq'];
						$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);
					}

					foreach($row['cart_suboptions'] as $cart_suboption){
						$multi_insert_params = array();
						$multi_insert_params['shipping_seq']				= $shipping_seq;
						$multi_insert_params['shipping_item_seq']			= $shipping_item_seq;
						$multi_insert_params['order_seq']					= $order_seq;
						$multi_insert_params['ea']							= $cart_suboption['ea'];
						$multi_insert_params['order_item_seq']				= $cart_suboption_real_seq[$cart_suboption['cart_suboption_seq']]['order_item_seq'];
						$multi_insert_params['order_item_suboption_seq']	= $cart_suboption_real_seq[$cart_suboption['cart_suboption_seq']]['order_item_suboption_seq'];
						$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);
					}

				}

				// 선택된 배송지에 사은품 저장
				if($_POST['gift_use']=='Y' && $_POST['giftReceiveShippingIdx']==$multiIdx){
					foreach($gift_option_real_seq as $k=>$v){

						$item_insert_params = array();
						$item_insert_params['shipping_seq'] 		= $shipping_seq;
						$item_insert_params['order_seq'] 			= $order_seq;
						$item_insert_params['order_item_seq']		= $v['order_item_seq'];
						$item_insert_params['goods_shipping_cost']	= 0;
						$this->db->insert('fm_order_shipping_item', $item_insert_params);
						$shipping_item_seq = $this->db->insert_id();

						$multi_insert_params = array();
						$multi_insert_params['shipping_seq']				= $shipping_seq;
						$multi_insert_params['shipping_item_seq']			= $shipping_item_seq;
						$multi_insert_params['order_seq']					= $order_seq;
						$multi_insert_params['ea']							= 1;
						$multi_insert_params['order_item_seq']				= $v['order_item_seq'];
						$multi_insert_params['order_item_option_seq']		= $v['order_item_option_seq'];
						$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);
					}
				}
			}
		}else{

			//배송지 저장 (로그인한 경우만)
			if($_POST['save_delivery_address']){
				$this->ordermodel->insert_delivery_address('order');
			}else{
				$this->ordermodel->insert_delivery_address();
			}

			$shipping_seq = $this->ordermodel->insert_single_shipping($order_seq, $this->shipping_cost, $this->area_add_delivery_cost);

			// 단일 배송지 상품 저장
			$r_except_item = array();
			foreach($this->cart['list'] as $k => $data){
				if( !$r_except_item[$data['goods_seq']] ){
					$item_insert_params = array();
					$item_insert_params['shipping_seq'] = $shipping_seq;
					$item_insert_params['order_seq']	= $order_seq;
					$item_insert_params['order_item_seq']	= $cart_option_real_seq[$data['cart_option_seq']]['order_item_seq'];
					if($data['cart_suboptions']){
						$item_insert_params['order_item_seq']			= $cart_suboption_real_seq[$data['cart_suboptions'][0]['cart_suboption_seq']]['order_item_seq'];
					}

					$item_insert_params['goods_shipping_cost'] = 0;
					$item_insert_params['add_goods_shipping'] = 0;
					if(!$_POST['shipping_method'] || $_POST['shipping_method'] == 'delivery'){
						$item_insert_params['goods_shipping_cost'] = $this->cart['data_goods'][$data['goods_seq']]['goods_shipping'];
						$item_insert_params['add_goods_shipping'] = $this->cart['data_goods'][$data['goods_seq']]['add_goods_shipping'];
					}

					$this->db->insert('fm_order_shipping_item', $item_insert_params);
					$shipping_item_seq = $this->db->insert_id();

					$r_except_item[$data['goods_seq']] = $shipping_item_seq;
				}

				//if	($r_except_item[$data['goods_seq']])
					//$shipping_item_seq	= $r_except_item[$data['goods_seq']];

				$multi_insert_params = array();
				$multi_insert_params['shipping_seq']				= $shipping_seq;
				$multi_insert_params['shipping_item_seq']			= $r_except_item[$data['goods_seq']];
				$multi_insert_params['order_seq']					= $order_seq;
				$multi_insert_params['ea']							= $data['ea'];
				$multi_insert_params['order_item_seq']				= $cart_option_real_seq[$data['cart_option_seq']]['order_item_seq'];
				$multi_insert_params['order_item_option_seq']		= $cart_option_real_seq[$data['cart_option_seq']]['order_item_option_seq'];
				$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);

				if($data['cart_suboptions']){
					foreach($data['cart_suboptions'] as $data_suboptions){
						if($data_suboptions['suboption_title'] && $data_suboptions['suboption']){
							$multi_insert_params = array();
							$multi_insert_params['shipping_seq']				= $shipping_seq;
							$multi_insert_params['shipping_item_seq']				= $r_except_item[$data['goods_seq']];
							$multi_insert_params['order_seq']					= $order_seq;
							$multi_insert_params['ea']							= $data_suboptions['ea'];
							$multi_insert_params['order_item_seq']				= $cart_suboption_real_seq[$data_suboptions['cart_suboption_seq']]['order_item_seq'];
							$multi_insert_params['order_item_suboption_seq']	= $cart_suboption_real_seq[$data_suboptions['cart_suboption_seq']]['order_item_suboption_seq'];
							$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);
						}
					}
				}
			}

			// 단일 배송지에 사은품 저장
			if($_POST['gift_use']=='Y'){
				foreach($gift_option_real_seq as $k=>$v){

					$item_insert_params = array();
					$item_insert_params['shipping_seq'] 		= $shipping_seq;
					$item_insert_params['order_seq'] 			= $order_seq;
					$item_insert_params['order_item_seq']		= $v['order_item_seq'];
					$item_insert_params['goods_shipping_cost']	= 0;
					$this->db->insert('fm_order_shipping_item', $item_insert_params);
					$shipping_item_seq = $this->db->insert_id();

					$multi_insert_params = array();
					$multi_insert_params['shipping_seq']				= $shipping_seq;
					$multi_insert_params['shipping_item_seq']			= $shipping_item_seq;
					$multi_insert_params['order_seq']					= $order_seq;
					$multi_insert_params['ea']							= 1;
					$multi_insert_params['order_item_seq']				= $v['order_item_seq'];
					$multi_insert_params['order_item_option_seq']		= $v['order_item_option_seq'];
					$this->db->insert('fm_order_shipping_item_option', $multi_insert_params);
				}
			}

		}

		if ($this->db->trans_status() === FALSE || $rollback == true)
		{
		    $this->db->trans_rollback();
		    openDialogAlert('주문서 생성중 오류가 발생했습니다.',400,140,'parent',$pg_cancel_script);
			exit;
		}
		else
		{
		    $this->db->trans_commit();
		}

		// 주문 총주문수량 / 총상품종류 업데이트 leewh 2014-08-01
		$this->ordermodel->update_order_total_info($order_seq);

		// 적립금/에누리/이머니 사용 상품옵션,추가옵션 별로 나누기
		$this->ordermodel->update_unit_emoney_cash_enuri($order_seq);

		// 매출증빙
		$this->_tax($goods_name,$order_seq);

		//  회원정보 가져오기(최초저장)
		if( $this->userInfo['member_seq'] ){
			$this->load->model('membermodel');
			$data_member = $this->membermodel->get_member_data($this->userInfo['member_seq']);

			$member_phone = str_replace('-','',$data_member['phone']);
			$member_cellphone = str_replace('-','',$data_member['cellphone']);
			$member_zipcode = str_replace('-','',$data_member['zipcode']);
			$member_address = str_replace('-','',$data_member['address']);
			$member_address_detail = str_replace('-','',$data_member['address_detail']);
/*
			$this->membermodel->first_member_insert($member_phone,$member_cellphone,$member_zipcode,$member_address,
				$member_address_detail
			);
*/
		}


		// 네이버 마일리지 주문번호 업데이트
		$this->load->model('navermileagemodel');
		$this->navermileagemodel->update_order($order_seq);

		// 결제수단에 따른 PG모듈 선택 :: 2015-02-23 lwh
		if($_POST['payment'] == 'kakaopay')
					$pgCompany	= $_POST['payment'];
		else		$pgCompany	= $this->config_system['pgCompany'];


		// pg 모듈 로드
		if($pgCompany && $_POST['payment'] != "bank"){
			// pg사로 전달할 상품명 생성
			$cart_cnt = count($this->cart['list']) - 1;
			if($cart_cnt > 0) $goods_name .= " 외 " . $cart_cnt . "건";

			$this->pg_param['payment'] = $_POST['payment'];
			$this->pg_param['order_seq']	= $order_seq;
			$goods_name						= preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $goods_name);
			$this->pg_param['goods_name']	= $goods_name;
			$this->pg_param['total_ea']		= $total_ea;		//장바구니 총 수량(에스크로시 사용) 2014.09-22
			$this->pg_param['goods_info']	= serialize($goods_info);		//장바구니 상세정보(에스크로시 사용) 2014.09-22
			$this->pg_param['goods_seq']	= $pg_goods_seq;
			$this->pg_param['goods_image']	= $pg_goods_image;
			$this->pg_param['settle_price'] = $this->settle_price;
			$this->pg_param['order_user_name'] = $_POST['order_user_name'];
			$this->pg_param['order_email'] = $_POST['order_email'];
			$this->pg_param['order_phone'] = implode('-',$_POST['order_phone']);
			$this->pg_param['order_cellphone'] = implode('-',$_POST['order_cellphone']);
			$this->pg_param['mobilenew'] = $_POST['mobilenew'];
			$this -> {$pgCompany}();
		}else{
			$this->order_seq = $order_seq;
			$this -> bank($adminOrder);
		}

	}

	public function _tax($goods_name,$order_seq)
	{
		$sales_config = config_load('order');

		$enuri = (int) $_POST['enuri'];
		$cash = (int) $_POST['cash'];
		$emoney = (int) $_POST['emoney'];
		$settle_price = (int) $this->settle_price;
		$shipping_price = 0;

		//상품명 생성
		if(( count($this->cart['list']) - 1) > 0){
			$item_name = $goods_name." 외 " . ( count($this->cart['list']) - 1) . "건";
		}

		// 할인
		$tax_sale = 0; // 과세 할인
		$sale = $enuri;	// 비과세 할인

		// 이머니 적립금 미포함
		$etc_sale += $emoney;
		$etc_sale += $cash;

		// 세금계산서 이머니 적립금 포함 설정
		if($sales_config["sale_reserve_yn"] != "Y"){
			$tax_sale += $emoney;
		}
		if($sales_config["sale_emoney_yn"] != "Y"){
			$tax_sale += $cash;
		}


		// 카드, 휴대폰일경우 매출전표만 출력가능하게 수정 :: 카카오페이 추가 :: 2015-02-27 lwh
		if($_POST['payment'] == 'card' || $_POST['payment'] == 'cellphone' || $_POST['payment'] == 'kakaopay' ) { $_POST['typereceipt'] = 0; }
		$total_goods_shipping_price = $this->cart['shipping_price']['goods'];

		$tax["exempt"] = 0;
		$tax["tax"] = 0;
		foreach($this->cart['list'] as $k => $data){
			$suboptionstax["exempt"] = 0;
			$suboptionstax["tax"] = 0;
			if($data['cart_suboptions']){
				foreach($data['cart_suboptions'] as $data_suboptions){
					if($data["tax"] == "exempt"){
						$suboptionstax["exempt"] = $suboptionstax["exempt"]+($data_suboptions["sale_price"]*$data_suboptions['ea']);
					}else{
						$suboptionstax["tax"] = $suboptionstax["tax"]+($data_suboptions["sale_price"]*$data_suboptions['ea']);
					}
				}
			}

			if($data["tax"] == "exempt"){
				$tax["exempt"] = $tax["exempt"]+($data["sale_price"]*$data['ea'])+$suboptionstax["exempt"];
			}else{
				$tax["tax"] = $tax["tax"]+($data["sale_price"]*$data['ea'])+$suboptionstax["tax"];
			}
		}

		$this->load->model('salesmodel');

		if(!$_POST['shipping_method'] || $_POST['shipping_method'] == 'delivery'){
			$shipping_price = $this->shipping_cost + $total_goods_shipping_price; // 배송비
		}

		$data_tax = $this->salesmodel->tax_calulate($tax["tax"],$tax["exempt"],$shipping_price,$sale,$tax_sale);
		$data_etc = $this->salesmodel->tax_calulate($tax["tax"],$tax["exempt"],$shipping_price,$sale,$etc_sale);

		if($_POST['typereceipt'] > 0) {

			// 세금계산서 신청일 경우
			if($_POST['typereceipt'] == 1) {

				$taxparams['typereceipt'	]	= $_POST['typereceipt'];
				$taxparams['type']				= 0;
				$taxparams['order_seq']		= $order_seq;
				$taxparams['member_seq']	= $this->userInfo['member_seq'];
				if($_POST['adminOrder'] == 'admin'){
					$taxparams['member_seq']=$_POST['member_seq'];
				}
				$taxparams['co_name']		= $_POST['co_name'];
				$taxparams['co_ceo']		= $_POST['co_ceo'];
				$taxparams['co_status']		= $_POST['co_status'];
				$taxparams['co_type']			= $_POST['co_type'];
				$taxparams['busi_no']			= $_POST['busi_no'];
				$taxparams['order_name']		= $_POST['order_user_name'];
				$taxparams['person']			= $_POST['person'];
				$taxparams['order_name']		= $_POST['order_user_name'];
				$taxparams['zipcode']			= @implode('-',$_POST['co_zipcode']);
				$taxparams['address_type']		= ($_POST['co_address_type'])?$_POST['co_address_type']:"zibun";
				$taxparams['address']					= $_POST['co_address'];
				$taxparams['address_street']		= $_POST['co_address_street'];
				$taxparams['address_detail']		= $_POST['co_address_detail'];
				$taxparams['email']			= $_POST['email'];
				$taxparams['phone']			= $_POST['phone'];
				$taxparams['order_date']	= date('Y-m-d H:i:s');

				// 과세 매출증빙 저장
				$taxparams['price']			= (int) $data_etc['supply'] + (int) $data_etc['surtax'];
				$taxparams['supply']		= (int) $data_etc['supply'];
				$taxparams['surtax']		= (int) $data_etc['surtax'];
				$taxparams['tax_price']		= (int) $data_tax['supply'] + (int) $data_tax['surtax'];
				$taxparams['tax_supply']	= (int) $data_tax['supply'];
				$taxparams['tax_surtax']	= (int) $data_tax['surtax'];
				if(  $data_etc['surtax'] > 0 ){
					$this->salesmodel->sales_write($taxparams);
				}

				// 비과세 매출증빙 저장

				$taxparams['price']			= (int) $data_etc['supply_free'];
				$taxparams['supply']		= (int) $data_etc['supply_free'];
				$taxparams['surtax']		= 0;
				$taxparams['tax_price']		= (int) $data_tax['supply_free'];
				$taxparams['tax_supply']	= (int) $data_tax['supply_free'];
				$taxparams['tax_surtax']	= 0;
				if(  $data_etc['supply_free'] > 0 ){
					$this->salesmodel->sales_write($taxparams);
				}
			}
			// 현금영수증 신청일 경우
			else if($_POST['typereceipt'] == 2) {
				$this->load->library('cashtax');

				$creceipt_number				= str_replace("-", "", $_POST['creceipt_number'][$_POST['cuse']]);
				$cashparams['creceipt_number']	= $creceipt_number;
				$cashparams['typereceipt']		= $_POST['typereceipt'];
				$cashparams['type']				= 0;
				$cashparams['order_seq']		= $order_seq;
				$cashparams['order_date']		= date("Y-m-d H:i:s");
				$cashparams['member_seq']		= $this->userInfo['member_seq'];
				$cashparams['email']			= $_POST['sales_email'] ? $_POST['sales_email'] : $_POST['order_email'];
				$cashparams['phone']			= implode("", $_POST['order_phone']);

				if($_POST['adminOrder'] == 'admin'){
					$cashparams['member_seq']	= $_POST['member_seq'];
				}

				###
				$cashparams['price']		= (int) $data_etc['supply'] + (int) $data_etc['supply_free'] + (int) $data_etc['surtax'];
				$cashparams['supply']		= (int) $data_etc['supply'] + (int) $data_etc['supply_free'];
				$cashparams['surtax']		= (int) $data_etc['surtax'];
				$cashparams['tax_price']	= (int) $data_tax['supply'] + (int) $data_tax['supply_free'] + (int) $data_tax['surtax'];
				$cashparams['tax_supply']	= (int) $data_tax['supply'] + (int) $data_tax['supply_free'];
				$cashparams['tax_surtax']	= (int) $data_tax['surtax'];



				$cashparams['person']		= $_POST['order_user_name'];
				$cashparams['order_name']	= $_POST['order_user_name'];
				$cashparams['cuse']			= $_POST['cuse'];
				$cashparams['goodsname']	= ($item_name)?$item_name:$goods_name;
				$cashparams['tstep']		= 1;
				$result_id = $this->salesmodel->sales_write($cashparams);
				$cashparams['paydt'] = $cashparams['regdate'];
			}
		}else{
			
			if( $_POST['payment'] == 'card' ||  $_POST['payment'] == 'cellphone' ||  $_POST['payment'] == 'kakaopay' ) {//매출전표를위해
				$salesparams['typereceipt']		= $_POST['typereceipt'];
				$salesparams['type']			= 0;
				$salesparams['order_seq']		= $order_seq;
				$salesparams['member_seq']		= $this->userInfo['member_seq'];
				$salesparams['order_date']		= date("Y-m-d H:i:s");
				$salesparams['order_name']		= $_POST['order_user_name'];
				$salesparams['person']			= $_POST['order_user_name'];
				
				// 카카오 페이인 경우 매출증빙 PG사 저장 :: 2015-02-25 lwh
				if($_POST['payment'] == 'kakaopay')
					$salesparams['pg_kind']		= $_POST['payment'];

				###
				$salesparams['price']		= (int) $data_etc['supply'] + (int) $data_etc['supply_free'] + (int) $data_etc['surtax'];
				$salesparams['supply']		= (int) $data_etc['supply'] + (int) $data_etc['supply_free'];
				$salesparams['surtax']		= (int) $data_etc['surtax'];

				$salesparams['tax_price']	= (int) $data_tax['supply'] + (int) $data_tax['supply_free'] + (int) $data_tax['surtax'];
				$salesparams['tax_supply']	= (int) $data_tax['supply'] + (int) $data_tax['supply_free'];
				$salesparams['tax_surtax']	= (int) $data_tax['surtax'];

				$salesparams['goodsname']	= ($item_name)?$item_name:$goods_name;

				$this->salesmodel->sales_write($salesparams);
			}
		}

		if( $data_etc['supply_free'] > 0 ){
			$this->freeprice		= (int) $data_etc['supply_free'];
			$this->comm_tax_mny		= (int) $data_etc['supply'];
			$this->comm_vat_mny		= (int) $data_etc['surtax'];
		}else{
			$this->comm_tax_mny		= (int) $data_etc['supply'];
			$this->comm_vat_mny		= (int) $data_etc['surtax'];
		}

		// 비과세 금액 주문에 저장
		$query = "update fm_order set freeprice=? where order_seq=?";
		$this->db->query($query,array($this->freeprice,$order_seq));

	}

	public function bank($adminOrder){
		$this->template->assign(array('order_seq'=>$this->order_seq));
		$this->template->assign(array('adminOrder'=>$adminOrder));
		$this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_bank.html'));
		$this->template->print_('tpl');
	}

	public function kcp(){
		// 모바일 일경우 모바일 결제창
		if( $this->_is_mobile_agent)
		{
			if($this->pg_param['mobilenew'] == 'y') $this->pg_open_script();

			echo("<form name='kcp_settle_form' method='post' target='tar_opener' action='../kcp_mobile/auth'>");
			echo("<input type='hidden' name='order_seq' value='".$this->pg_param['order_seq']."' />");
			echo("<input type='hidden' name='goods_name' value='".$this->pg_param['goods_name']."' />");
			echo("<input type='hidden' name='goods_seq' value='".$this->pg_param['goods_seq']."' />");
			echo("<input type='hidden' name='payment' value='".$this->pg_param['payment']."' />");
			echo("<input type='hidden' name='mobilenew' value='".$this->pg_param['mobilenew']."' />");
			echo("<input type='hidden' name='goods_info' value='".$this->pg_param['goods_info']."' />");
			echo("</form>");
			echo("<script>document.kcp_settle_form.submit();</script>");
			exit;
		}

		$pg = config_load($this->config_system['pgCompany']);

		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['kcp_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		/* bin 디렉토리 전까지의 경로를 입력,절대경로 입력 */
		$pg_param['g_conf_home_dir']  = dirname(__FILE__)."/../../pg/kcp/";
		/* 테스트  : testpaygw.kcp.co.kr
		 * 실결제  : paygw.kcp.co.kr */
		$pg_param['g_conf_gw_url']    = $pg['mallCode']=='T0007' ? "testpaygw.kcp.co.kr" : "paygw.kcp.co.kr";
		/* 테스트  : https://pay.kcp.co.kr/plugin/payplus_test.js
		 * 실결제  : https://pay.kcp.co.kr/plugin/payplus.js */
		$pg_param['g_conf_js_url']	  = $pg['mallCode']=='T0007' ? "https://pay.kcp.co.kr/plugin/payplus_test_un.js" : "https://pay.kcp.co.kr/plugin/payplus_un.js";
		/* 테스트 T0000 */
		$pg_param['g_conf_site_cd']   = $pg['mallCode'];
		/* 테스트 3grptw1.zW0GSo4PQdaGvsF__ */
		$pg_param['g_conf_site_key']  = $pg['merchantKey'];
		$pg_param['g_conf_site_name'] = $this->config_basic['shopName'];
		$pg_param['g_conf_log_level'] = "3";           // 변경불가
		$pg_param['g_conf_gw_port']   = "8090";        // 포트번호(변경불가)

		###
		$pg_param['kcp_logo_type']		= $pg['kcp_logo_type'];
		$pg_param['kcp_skin_color']		= $pg['kcp_skin_color'];
		if($pg_param['kcp_logo_type'] == 'img' && !is_null($pg['kcp_logo_val_img'])){
			$pg_param['kcp_logo_val_img']	= $pg['kcp_logo_val_img'];
			$pg_param['kcp_logo_img']		= 'http://'.$_SERVER['HTTP_HOST'].str_replace(ROOTPATH, '/', $pg['kcp_logo_val_img']);
		}elseif	($pg_param['kcp_logo_type'] == 'text' && !is_null($pg['kcp_logo_val_text'])){
			$pg_param['g_conf_site_name']	= $pg['kcp_logo_val_text'];
		}

		###
		$pg_param['comm_free_mny']	= $this->freeprice;
		$pg_param['comm_tax_mny']	= $this->comm_tax_mny;
		$pg_param['comm_vat_mny']	= $this->comm_vat_mny;

		$pg_param = array_merge($pg_param,$this->pg_param);
		$pg_param['goods_name'] = str_replace(array("(",")","<",">","[","]","{","}","-","'","\""), "", $pg_param['goods_name']);
		$pg_param['goods_name'] = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $pg_param['goods_name']);

		$data_order = $this->ordermodel->get_order($this->pg_param['order_seq']);
		$pg_param['good_info'] = "seq=1" . chr(31) . "ordr_numb=0001".chr(31)."good_name=".$pg_param['goods_name'].chr(31)."good_cntx=1".chr(31)."good_amtx=".$data_order['settleprice'];

		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}

		$this->template->assign($pg_param);
		$this->template->assign(array('data_order'=>$data_order));
		$this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_kcp.html'));
		$this->template->print_('tpl');
	}

	public function lg()
	{

		// 모바일 일경우 모바일 결제창
		if( $this->_is_mobile_agent)
		{
			if($this->pg_param['mobilenew'] == 'y') $this->pg_open_script();

			echo("<form name='lg_settle_form' method='post' target='tar_opener' action='../lg_mobile/auth'>");
			echo("<input type='hidden' name='order_seq' value='".$this->pg_param['order_seq']."' />");
			echo("<input type='hidden' name='goods_name' value='".$this->pg_param['goods_name']."' />");
			echo("<input type='hidden' name='goods_seq' value='".$this->pg_param['goods_seq']."' />");
			echo("<input type='hidden' name='mobilenew' value='".$this->pg_param['mobilenew']."' />");
			echo("</form>");
			echo("<script>document.lg_settle_form.submit();</script>");
			exit;
		}

		 /*
	     * [결제 인증요청 페이지(STEP2-1)]
	     *
	     * 샘플페이지에서는 기본 파라미터만 예시되어 있으며, 별도로 필요하신 파라미터는 연동메뉴얼을 참고하시어 추가 하시기 바랍니다.
	     */

	    /*
	     * 1. 기본결제 인증요청 정보 변경
	     *
	     * 기본정보를 변경하여 주시기 바랍니다.(파라미터 전달시 POST를 사용하세요)	     *
	     */
		global $pg;
		$pg = config_load($this->config_system['pgCompany']);

		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['lg_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param = array_merge($pg_param,$this->pg_param);
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}

		$pg['platform'] = "service";
		if( $pg['mallCode'] == 'gb_gabiatest01' )	$pg['mallCode'] = "gabiatest01";	//gabia test
		if( $pg['mallCode'] == 'tgabiatest01' )		$pg['platform'] = "test"; //LG유플러스 결제 서비스 선택(test:테스트, service:서비스)

	    $param['CST_PLATFORM'] = $CST_PLATFORM = $pg['platform'];		//LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
	    $param['CST_MID'] = $CST_MID = $pg['mallCode'];					//상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)

	                                                                        //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
	    $param['LGD_MID']		= $LGD_MID = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;  //상점아이디(자동생성)
	    $param['LGD_OID']		= $LGD_OID = $this->pg_param['order_seq'];           //주문번호(상점정의 유니크한 주문번호를 입력하세요)
	    $param['LGD_AMOUNT']	= $LGD_AMOUNT = $this->pg_param['settle_price'];        //결제금액("," 를 제외한 결제금액을 입력하세요)
	    $param['LGD_BUYER']		= $LGD_BUYER = $this->pg_param['order_user_name'];         //구매자명
	    $param['LGD_PRODUCTINFO'] = $LGD_PRODUCTINFO = $this->pg_param['goods_name'];   //상품명
	    $param['LGD_BUYEREMAIL'] = $LGD_BUYEREMAIL = $this->pg_param['order_email'];    //구매자 이메일
	    $param['LGD_TIMESTAMP'] = $LGD_TIMESTAMP = date(YmdHms);                         //타임스탬프
	    $param['LGD_CUSTOM_SKIN'] = $LGD_CUSTOM_SKIN = "blue";                               //상점정의 결제창 스킨 (red, blue, cyan, green, yellow)
	    $param['LGD_MERTKEY'] = $LGD_MERTKEY = $pg['merchantKey'];									//상점MertKey(mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
		$configPath = dirname(__FILE__)."/../../pg/lgdacom/"; //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.
	    $param['LGD_BUYERID'] = $LGD_BUYERID = $this->userInfo['userid'];       //구매자 아이디
	    $param['LGD_BUYERIP'] = $LGD_BUYERIP = $_SERVER['REMOTE_ADDR'];       //구매자IP

		###
		$param['LGD_TAXFREEAMOUNT'] = $this->freeprice;

	    /*
	     * 가상계좌(무통장) 결제 연동을 하시는 경우 아래 LGD_CASNOTEURL 을 설정하여 주시기 바랍니다.
	     */
	    $param['LGD_CASNOTEURL'] =  $LGD_CASNOTEURL	= "http://".$_SERVER['HTTP_HOST']."/payment/lg_return";

	    /*
	     *************************************************
	     * 2. MD5 해쉬암호화 (수정하지 마세요) - BEGIN
	     *
	     * MD5 해쉬암호화는 거래 위변조를 막기위한 방법입니다.
	     *************************************************
	     *
	     * 해쉬 암호화 적용( LGD_MID + LGD_OID + LGD_AMOUNT + LGD_TIMESTAMP + LGD_MERTKEY )
	     * LGD_MID          : 상점아이디
	     * LGD_OID          : 주문번호
	     * LGD_AMOUNT       : 금액
	     * LGD_TIMESTAMP    : 타임스탬프
	     * LGD_MERTKEY      : 상점MertKey (mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
	     *
	     * MD5 해쉬데이터 암호화 검증을 위해
	     * LG유플러스에서 발급한 상점키(MertKey)를 환경설정 파일(lgdacom/conf/mall.conf)에 반드시 입력하여 주시기 바랍니다.
	     */
	    require_once($configPath."XPayClient.php");
	    $xpay = new XPayClient($configPath, $CST_PLATFORM);
	   	$xpay->Init_TX($LGD_MID);

	    $param['LGD_HASHDATA'] = $LGD_HASHDATA = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_TIMESTAMP.$xpay->config[$LGD_MID]);
	    $param['LGD_CUSTOM_PROCESSTYPE'] = $LGD_CUSTOM_PROCESSTYPE = "TWOTR";
	    /*
	     *************************************************
	     * 2. MD5 해쉬암호화 (수정하지 마세요) - END
	     *************************************************
	     */

		## 접속 브라우저 확인 IE/기타.
		$userAgenr = getBrowser();
		if($userAgenr['nickname'] == "MSIE"){
			$browser = "IE";
		}else{
			$browser = "etc";
		}

	    $this->template->assign("browser",$browser);
	    $this->template->assign($param);
	    $this->template->assign($pg_param);
	    $this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir = BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_lg.html'));
		$this->template->print_('tpl');
	}

	public function allat()
	{
		// 모바일 일경우 모바일 결제창
		if( $this->_is_mobile_agent)
		{
			if($this->pg_param['mobilenew'] == 'y') $this->pg_open_script();
			echo("<form name='all_settle_form' method='post' target='tar_opener' action='../allat_mobile/allat'>");
			echo("<input type='hidden' name='order_seq' value='".$this->pg_param['order_seq']."' />");
			echo("<input type='hidden' name='goods_name' value='".$this->pg_param['goods_name']."' />");
			echo("<input type='hidden' name='goods_seq' value='".$this->pg_param['goods_seq']."' />");
			echo("<input type='hidden' name='mobilenew' value='".$this->pg_param['mobilenew']."' />");
			echo("</form>");
			echo("<script>document.all_settle_form.submit();</script>");
			exit;
		}

		$this->load->model('ordermodel');
		$pg = config_load($this->config_system['pgCompany']);
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['allat_noint_quota'] = implode(',',$codes);
		}
		$pg_param['quotaopt']  = $pg['interestTerms'];
		$pg_param = array_merge($pg_param,$this->pg_param);
		$payment = str_replace('escrow_','',$pg_param['payment']);
		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
		}

		$data_order = $this->ordermodel -> get_order($this->pg_param['order_seq']);
		$param['allat_shop_id'] = $pg['mallCode'];
		$param['allat_order_no'] = $pg_param['order_seq'];
		$param['allat_amt'] = $pg_param['settle_price'];
		$param['allat_pmember_id'] = "GUEST";
		if( $this->userInfo['userid'] && strlen($this->userInfo['userid']) < 20 ) $param['allat_pmember_id'] = $this->userInfo['userid'];

		$param['allat_product_cd'] = $pg_param['goods_seq'];
		$param['allat_product_nm'] = $pg_param['goods_name'];
		$param['allat_buyer_nm'] = $pg_param['order_user_name'];
		$param['allat_recp_nm'] = $data_order['recipient_user_name'];
		$param['allat_recp_addr'] = $data_order['recipient_address']." ".$data_order['recipient_address_detail'];

		$param['allat_card_yn'] = 'N';
		$param['allat_bank_yn'] = 'N';
		$param['allat_vbank_yn'] = 'N';
		$param['allat_hp_yn'] = 'N';
		$param['allat_ticket_yn']  = 'N';
		if($pg_param['payment'] == 'card') $param['allat_card_yn'] = 'Y';
		if($pg_param['payment'] == 'account') $param['allat_bank_yn'] = 'Y';
		if($pg_param['payment'] == 'virtual') $param['allat_vbank_yn'] = 'Y';
		if($pg_param['payment'] == 'cellphone') $param['allat_hp_yn'] = 'Y';

		$param['allat_zerofee_yn'] = 'Y';
		$param['allat_cash_yn'] = 'N';
		$param['allat_email_addr'] = $data_order['order_email'];
		$param['allat_product_img'] = $pg_param['goods_image'];
		$param['allat_real_yn'] = 'Y';
		$param['allat_bankes_yn'] = 'N';
		$param['allat_vbankes_yn'] = 'N';
		if($pg_param['payment'] == 'account' && $pg_param['escorw']) $param['allat_bankes_yn'] = 'Y';
		if($pg_param['payment'] == 'virtual' && $pg_param['escorw']) $param['allat_vbankes_yn'] = 'Y';
		$param['allat_test_yn']  = 'N';
		// if( $pg['mallCode'] == 'FM_pgfreete2' ) $param['allat_test_yn']  = 'Y';

		###
		$param['comm_free_mny']		= $this->freeprice;
		$param['comm_tax_mny']		= $this->comm_tax_mny;
		$param['comm_vat_mny']		= $this->comm_vat_mny;
		$param['allat_tax_yn']		= $this->comm_tax_mny ? 'Y':'N';



		$this->template->assign($param);
	    $this->template->assign($pg_param);
	    $this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir = BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_allat.html'));
		$this->template->print_('tpl');
	}

	public function inicis()
	{
		$pg_param = array();

		// 모바일 일경우 모바일 결제창
		if( $this->_is_mobile_agent)
		{
			echo("<form name='mobile_settle_form' method='post' action='../inicis_mobile/inicis'>");
			echo("<input type='hidden' name='order_seq' value='".$this->pg_param['order_seq']."' />");
			echo("<input type='hidden' name='goods_name' value='".$this->pg_param['goods_name']."' />");
			echo("<input type='hidden' name='goods_seq' value='".$this->pg_param['goods_seq']."' />");
			echo("</form>");
			echo("<script>document.mobile_settle_form.submit();</script>");
			exit;
		}

		$this->load->model('ordermodel');
		$pg = config_load($this->config_system['pgCompany']);
		if( isset($pg['pcCardCompanyCode']) ){
			foreach($pg['pcCardCompanyCode'] as $key => $code){
				$arr = explode(',',$pg['pcCardCompanyTerms'][$key]);
				$terms = array();
				foreach($arr as $term){
					$terms[] = sprintf('%02d',$term);
				}
				$codes[] = $code . '-' . implode(':',$terms);
			}
			$pg_param['inicis_noint_quota'] = implode(',',$codes);
		}

		$pg_param				= array_merge($pg_param,$this->pg_param);
		$data_order				= $this->ordermodel -> get_order($pg_param['order_seq']);
		$param['buyername']		= $data_order['order_user_name'];
		$param['buyeremail']	= $data_order['order_email'];
		$param['buyertel']		= $data_order['order_cellphone'];
		$pg_param['quotaopt']	= $pg['interestTerms'];

		$payment				= str_replace('escrow_','',$pg_param['payment']);

		if($payment != $pg_param['payment']){
			$pg_param['escorw'] = 1;
			$pg_param['payment'] = $payment;
			$pg['mallCode'] = $pg['escrowMallCode'];
			$pg['merchantKey'] =  $pg['escrowMerchantKey'];
		}

		$param['mallCode'] = $pg['mallCode'];

		/**************************
	     * 1. 라이브러리 인클루드 *
	     **************************/
	    require("./pg/inicis/libs/INILib.php");

	    /***************************************
	     * 2. INIpay50 클래스의 인스턴스 생성  *
	     ***************************************/
	    $inipay = new INIpay50;

	    /**************************
	     * 3. 암호화 대상/값 설정 *
	     **************************/
	    $inipay->SetField("inipayhome", dirname(__FILE__)."/../../pg/inicis");       // 이니페이 홈디렉터리(상점수정 필요)
	    $inipay->SetField("type", "chkfake");      // 고정 (절대 수정 불가)
	    $inipay->SetField("debug", "true");        // 로그모드("true"로 설정하면 상세로그가 생성됨.)
	    $inipay->SetField("enctype","asym"); 			//asym:비대칭, symm:대칭(현재 asym으로 고정)
	    /**************************************************************************************************
	     * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
	     * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
	     * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
	     * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
	     **************************************************************************************************/
		$inipay->SetField("admin", $pg['merchantKey']); 				// 키패스워드(키발급시 생성, 상점관리자 패스워드와 상관없음)
	    $inipay->SetField("checkopt", "false"); 		//base64함:false, base64안함:true(현재 false로 고정)

		//필수항목 : mid, price, nointerest, quotabase
		//추가가능 : INIregno, oid
		//*주의* : 	추가가능한 항목중 암호화 대상항목에 추가한 필드는 반드시 hidden 필드에선 제거하고
		//          SESSION이나 DB를 이용해 다음페이지(INIsecureresult.php)로 전달/셋팅되어야 합니다.
	    $inipay->SetField("mid", $pg['mallCode']);            // 상점아이디
	    $inipay->SetField("price", $pg_param['settle_price']);                // 가격

	    $quotabase = "선택:일시불";
	    $terms = "";

	    if($pg['interestTerms']){
	    	for($inter_i=2;$inter_i <= $pg['interestTerms'];$inter_i++){
	    		$arr_terms[] = $inter_i;
	    	}
	    	$terms = implode('개월:',$arr_terms)."개월";
	    }
	    $quotabase .= ":".$terms;//할부기간

	 	if($pg['nonInterestTerms'] == 'manual'){
	    	$inipay->SetField("nointerest", "yes");             //무이자여부(no:일반, yes:무이자)
	    	if($pg['pcCardCompanyCode']){
		    	foreach($pg['pcCardCompanyCode'] as $k_cardCompanyCode => $data_cardCompanyCode){
		    		if($data_cardCompanyCode && $pg['pcCardCompanyTerms'][$k_cardCompanyCode]){
		    			$r_cardCompanyCode[] = $data_cardCompanyCode."-".str_replace(",",":",$pg['pcCardCompanyTerms'][$k_cardCompanyCode]);
		    		}
		    	}
		    	if($r_cardCompanyCode){
		    		$quotabase .= "(".implode(',',$r_cardCompanyCode).")";
		    	}
	    	}
	    }else{
	    	$inipay->SetField("nointerest", "automatic");
	    }

		$quotabase = mb_convert_encoding($quotabase, "EUC-KR", "UTF-8");
	    $inipay->SetField("quotabase", $quotabase);


	    /********************************
	     * 4. 암호화 대상/값을 암호화함 *
	     ********************************/
	    $inipay->startAction();

	    /*********************
	     * 5. 암호화 결과  *
	     *********************/
 		if( $inipay->GetResult("ResultCode") != "00" )
		{
			echo $inipay->GetResult("ResultMsg");
			exit(0);
		}

	    /*********************
	     * 6. 세션정보 저장  *
	     *********************/
		$_SESSION['INI_MID']		= $pg['mallCode'];	//상점ID
		$_SESSION['INI_ADMIN']		= $pg['merchantKey'];			// 키패스워드(키발급시 생성, 상점관리자 패스워드와 상관없음)
		$_SESSION['INI_PRICE']		= $pg_param['settle_price'];     //가격
		$_SESSION['INI_RN']			= $inipay->GetResult("rn"); //고정 (절대 수정 불가)
		$_SESSION['INI_ENCTYPE']	= $inipay->GetResult("enctype"); //고정 (절대 수정 불가)

		###
		$param['comm_free_mny']		= $this->freeprice;
		$param['comm_tax_mny']		= $this->comm_tax_mny;
		$param['comm_vat_mny']		= $this->comm_vat_mny;

		$param['encfield'] = $inipay->GetResult("encfield");
		$param['certid'] = $inipay->GetResult("certid");

		$this->template->assign($param);
	    $this->template->assign($pg_param);
		$this->template->template_dir = BASEPATH."../order";
		$this->template->compile_dir = BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'_inicis.html'));
		$this->template->print_('tpl');
	}

	public function kspay(){

		$this->load->model('ordermodel');
		$pg							= config_load($this->config_system['pgCompany']);
		$pg_param					= array_merge($pg, $this->pg_param);

		// 결제수단별 코드
		$pgCodeArr					= array('card'=>'1000000000', 'virtual'=>'0100000000',
											'account'=>'0010000000','cellphone'=>'0000010000',
											'escrow_virtual'=>'0100000000');
		$pg_param['paymentCode']	= $pgCodeArr[$this->pg_param['payment']];
		$pg_param['shopName']		= $this->config_basic['shopName'];
		$pg_param['escrow']			= '0';
		if	($this->pg_param['payment'] == 'escrow_virtual')
			$pg_param['escrow']			= '1';

		$tplpath					= '_kspay.html';
		$interestTerms				= $pg_param['interestTerms'];
		$cardCompanyCode			= $pg_param['pcCardCompanyCode'];
		$cardCompanyTerms			= $pg_param['pcCardCompanyTerms'];
		if( $this->_is_mobile_agent){
			$tplpath			= '_kspay_mobile.html';
			$interestTerms		= $pg_param['mobileInterestTerms'];
			$cardCompanyCode	= $pg_param['mobileCardCompanyCode'];
			$cardCompanyTerms	= $pg_param['mobileCardCompanyTerms'];
		}

		// 할부개월수 설정
		for ($i = 0; $i <= $interestTerms; $i++){
			if	($i == 1)	continue;
			if	($i > 0)	$interestTermsStr	.= ':';
			$interestTermsStr	.= $i;
		}

		// 무이자 할부 설정
		$pg_param['kspay_noint_quota']	= 'NONE';
		if( is_array($cardCompanyCode) && count($cardCompanyCode) > 0 ){
			foreach($cardCompanyCode as $key => $code){
				$arr	= explode(',',$cardCompanyTerms[$key]);
				$terms	= array();
				foreach($arr as $term){
					$terms[]	= $term;
				}
				$codes[]	= $code . '(' . implode(':',$terms) . ')';
			}
			$pg_param['kspay_noint_quota'] = implode(',',$codes);
		}

		$pg_param['interestTermsStr']	= $interestTermsStr;
		$pg_param['order_cellphone']	= preg_replace('/[^0-9]/', '', $pg_param['order_cellphone']);
		$pg_param['goods_name']			= preg_replace('/[\'\"\`]/', '', $pg_param['goods_name']);
		$pg_param['domain']				= $this->config_system['domain'];

		$pg_param['comm_free_mny']		= $this->freeprice;
		$pg_param['comm_tax_mny']		= $this->comm_tax_mny;
		$pg_param['comm_vat_mny']		= $this->comm_vat_mny;

		$this->template->assign($pg_param);
		$this->template->template_dir	= BASEPATH."../order";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>$tplpath));
		$this->template->print_('tpl');
	}

	public function kspay_wh_rcv(){

		$rcid       = $_POST["reCommConId"];
		$rctype     = $_POST["reCommType"];
		$rhash      = $_POST["reHash"];

		$p_protocol	= "http";
		if (strlen($_SERVER['SERVER_PROTOCOL'])>4 && "https" == substr($_SERVER['SERVER_PROTOCOL'],0,5) )
		{
			$p_protocol = "https";
		}

		if (!empty($rcid) && 10 > strlen($rcid))	$script_display = "y";
		else										$script_display = "n";
		if( $this->_is_mobile_agent)				$tplpath		= '_kspay_wh_rcv_mobile.html';
		else										$tplpath		= '_kspay_wh_rcv.html';

		$this->template->assign(array('script_display'=>$script_display));
		$this->template->assign(array('rcid'=>$rcid));
		$this->template->assign(array('rctype'=>$rctype));
		$this->template->assign(array('rhash'=>$rhash));
		$this->template->assign(array('p_protocol'=>$p_protocol));
		$this->template->template_dir	= BASEPATH."../order";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>$tplpath));
		$this->template->print_('tpl');
	}

	/* 카카오 페이 :: 2015-01-20 lwh */
	public function kakaopay(){
		echo '<script type="text/javascript">function kakao_cancel_script(){'.$this->pg_cancel_script().'}</script>';
		// 모바일 일경우 플래그값 전달
		if( $this->_is_mobile_agent){
			$prType = "MPM";
		}else{
			$prType = "WPM";
		}

		// 인증 관련 TXN_ID 호출 전용 값 호출
		$pg_param = config_load('kakaopay');

		if($pg_param['mid'] || $pg_param['merchantKey'] || $pg_param['merchantEncKey'] || $pg_param['merchantHashKey']){

			$this->load->model('ordermodel');
			$data_order = $this->ordermodel->get_order($this->pg_param['order_seq']);

			$pg_param['merchantTxnNumIn']= $this->pg_param['order_seq'];// 주문번호
			$pg_param['ediDate']		= date("YmdHis");				// 전문생성일시
			$pg_param['settleprice']	= $data_order['settleprice'];	// 총 금액
			$pg_param['free_mny']		= (int)$this->freeprice;		// 비과세 합계
			$pg_param['tax_mny']		= (int)$this->comm_tax_mny;		// 공급가액
			$pg_param['vat_mny']		= (int)$this->comm_vat_mny;		// 부가가치세
			$pg_param['total_tax_mny']	= $pg_param['free_mny'] + $pg_param['tax_mny'];
			$pg_param['goods_name']		= $this->pg_param['goods_name'];// 상품명

			//## 위변조 처리 - 결제요청용 키값
			$md_src = $pg_param['ediDate']
				.$pg_param['mid']
				.$pg_param['settleprice']
				.$pg_param['merchantKey'];
			$hash_String = base64_encode(hash("sha256", $md_src, false));

			//############### getTxnId START #############//
			//## 1. 라이브러리 인클루드
			require("./pg/kakaopay/conf_inc.php");
			require("./pg/kakaopay/libs/lgcns_KMpay.php");

			//## 인증,결제 및 웹 경로
			$pg_param['CNSPAY_WEB_SERVER_URL']	= $CNSPAY_WEB_SERVER_URL;
			$pg_param['targetUrl']				= $targetUrl;
			$pg_param['msgName']				= $msgName;
			$pg_param['CnsPayDealRequestUrl']	= $CnsPayDealRequestUrl;

			//## 로그 경로
			$pg_param['LogDir'] = $LogDir; //"C:/KMPay/Log";

			//## 2. TxnID 얻기
			$kmFunc = new kmpayFunc($pg_param['LogDir']);
			$kmFunc->setPhpVersion($phpVersion);

			// TXN_ID를 요청하기 위한 PARAMETERR
			$pgVal['REQUESTDEALAPPROVEURL'] = "https://".$targetUrl.$msgName; //인증 요청 경로
			$pgVal['PR_TYPE'] = $prType;	//결제 요청 타입 (함수 상단에 위치)
			$pgVal['MERCHANT_ID'] = $pg_param['mid'];		//가맹점 ID
			$pgVal['MERCHANT_TXN_NUM'] = $pg_param['merchantTxnNumIn'];	//가맹점 거래번호
			$pgVal['channelType'] = '4'; // 모바일웹결제 : 2 || TMS 방식 : 4  -> 기본 4
			$pgVal['PRODUCT_NAME'] = $pg_param['goods_name'];	//상품명 (샘플 외 몇건)
			$pgVal['AMOUNT'] = $pg_param['settleprice'];	//상품금액(총거래금액) (총거래금액 = 공급가액 + 부가세 + 봉사료)
			$pgVal['CURRENCY'] = 'KRW';	//거래통화(KRW/USD/JPY 등) ->html도 같이 수정요망
			$pgVal['RETURN_URL'] = "";	//결제승인결과전송URL --#확인
			$pgVal['RETURN_URL2'] = ""; // --#확인
			$pgVal['CERTIFIED_FLAG'] = "CN";// CN : 웹결제, N : 인앱결제 ->html도 같이 수정요망
			$pgVal['requestorName'] = ""; // --#확인
			$pgVal['requestorTel'] = ""; // --#확인
			//무이자옵션
			$pgVal['NOINTYN'] = ($pg_param['nonInterestTerms']=='manual') ? 'Y' : ''; // 무이자 설정 Y || N --#확인
			if($pg_param['nonInterestTerms'] == 'manual'){
				$nointopt_str	= "";
				foreach($pg_param['CardCompanyCode'] as $k => $val){
					if($k > 0)	$nointopt_str .= ",";
					$terms_str	= "";
					$terms_arr	= explode(",",$pg_param['CardCompanyTerms'][$k]);
					foreach($terms_arr as $z => $mon){
						if($z > 0)	$terms_str .= ":";
						$terms_str	.= str_pad($mon,2,"0",STR_PAD_LEFT);
					}
					$nointopt_str	.= "CC".$val."-".$terms_str;
				}
			}else{
				$nointopt_str	= "";
			}
			$pgVal['NOINTOPT'] = $nointopt_str;	// 무이자 옵션 --#확인
			$pgVal['MAX_INT'] = str_pad($pg_param['interestTerms'],2,"0",STR_PAD_LEFT);	// 최대할부개월 --#확인
			$pgVal['FIXEDINT'] = "";	// 고정할부개월 (할부개월 고정시 사용 비우면 나중 00은 일시불) --#확인
			$pgVal['POINT_USE_YN'] = "N"; // 카드사포인트사용여부 Y || N
			$pgVal['POSSICARD'] = ""; // 결제가능카드설정 (비우면 나중선택)
			$pgVal['BLOCK_CARD'] = ""; // 금지카드설정

			// ENC KEY와 HASH KEY는 가맹점에서 생성한 KEY 로 SETTING 한다.
			$pgVal['merchantEncKey'] = $pg_param['merchantEncKey'];
			$pgVal['merchantHashKey'] = $pg_param['merchantHashKey'];
			$pgVal['hashTarget'] = $pgVal['MERCHANT_ID'].$pgVal['MERCHANT_TXN_NUM'].str_pad($pgVal['AMOUNT'],7,"0",STR_PAD_LEFT);

			// payHash 생성
			$pgVal['payHash'] = strtoupper(hash("sha256", $pgVal['hashTarget'].$pgVal['merchantHashKey'], false));
			
			//json string 생성
			$strJsonString = new JsonString($pg_param['LogDir']);

			$strJsonString->setValue("PR_TYPE", $pgVal['PR_TYPE']);
			$strJsonString->setValue("channelType", $pgVal['channelType']);
			$strJsonString->setValue("requestorName", $pgVal['requestorName']);
			$strJsonString->setValue("requestorTel", $pgVal['requestorTel']);
			$strJsonString->setValue("MERCHANT_ID", $pgVal['MERCHANT_ID']);
			$strJsonString->setValue("MERCHANT_TXN_NUM", $pgVal['MERCHANT_TXN_NUM']);
			$strJsonString->setValue("PRODUCT_NAME", $pgVal['PRODUCT_NAME']);

			$strJsonString->setValue("AMOUNT", $pgVal['AMOUNT']);
			
			$strJsonString->setValue("CURRENCY", $pgVal['CURRENCY']);
			$strJsonString->setValue("CERTIFIED_FLAG", $pgVal['CERTIFIED_FLAG']);
			$strJsonString->setValue("RETURN_URL", $pgVal['RETURN_URL']);
			$strJsonString->setValue("RETURN_URL2", $pgVal['RETURN_URL2']);


			$strJsonString->setValue("NO_INT_YN", $pgVal['NOINTYN']);
			$strJsonString->setValue("NO_INT_OPT", $pgVal['NOINTOPT']);
			$strJsonString->setValue("MAX_INT", $pgVal['MAX_INT']);
			$strJsonString->setValue("FIXED_INT", $pgVal['FIXEDINT']);
			
			$strJsonString->setValue("POINT_USE_YN", $pgVal['POINT_USE_YN']);
			$strJsonString->setValue("POSSI_CARD", $pgVal['POSSICARD']);
			$strJsonString->setValue("BLOCK_CARD", $pgVal['BLOCK_CARD']);
			
			$strJsonString->setValue("PAYMENT_HASH", $pgVal['payHash']);
			
			// 결과값을 담는 부분
			$resultCode = "";
			$resultMsg = "";
			$txnId = "";
			$merchantTxnNum = "";
			$prDt = "";
			$strValid = "";

			// Data 검증
			$dataValidator = new KMPayDataValidator($strJsonString->getArrayValue());
			$strValid = $dataValidator->resultValid;
			if (strlen($strValid) > 0) {
				$arrVal = explode(",", $strValid);
				if (count($arrVal) == 3) {
					$resultCode = $arrVal[1];
					$resultMsg = $arrVal[2];
				} else {
					$resultCode = $strValid;
					$resultMsg = $strValid;
				}
			}
	
			// Data에 이상 없는 경우
			if (strlen($strValid) == 0) {
				// CBC 암호화
				$paramStr = $strJsonString->getJsonString();

				$kmFunc->writeLog("Request");
				$kmFunc->writeLog($paramStr);
				$kmFunc->writeLog($strJsonString->getArrayValue());

				$encryptStr = $kmFunc->parameterEncrypt($pgVal['merchantEncKey'], $paramStr);
				$payReqResult = $kmFunc->connMPayDLP($pgVal['REQUESTDEALAPPROVEURL'], $pgVal['MERCHANT_ID'], $encryptStr);
				$resultString = $kmFunc->parameterDecrypt($pgVal['merchantEncKey'], $payReqResult);
				$resultJSONObject = new JsonString($this->kakao_config['LogDir']);
				if (substr($resultString, 0, 1) == "{") {
					$resultJSONObject->setJsonString($resultString);
					$resultCode = $resultJSONObject->getValue("RESULT_CODE");
					$resultMsg = $resultJSONObject->getValue("RESULT_MSG");
					if ($resultCode == "00") {
						$txnId = $resultJSONObject->getValue("TXN_ID");
						$merchantTxnNum = $resultJSONObject->getValue("MERCHANT_TXN_NUM");
						$prDt = $resultJSONObject->getValue("PR_DT");
					}
				}
				$kmFunc->writeLog("Result");
				$kmFunc->writeLog($resultString);
				$kmFunc->writeLog($resultJSONObject->getArrayValue());


				// 성공시....
				$txnResult['resultCode']		= $resultCode;
				$txnResult['resultMsg']			= $resultMsg;
				$txnResult['txnId']				= $txnId;
				$txnResult['merchantTxnNum']	= $merchantTxnNum;
				$txnResult['prDt']				= $prDt;
			}else{
				// 오류 시 경고창 띄우기
				echo '<script type="text/javascript">'.$this->pg_cancel_script().'alert("결제 인증 모듈오류\\n관리자에게 문의해주세요.");</script>';
				exit;
				//$res_data	= explode(",",$strValid);				
				//echo "<script>alert('".$res_data[0]." : [".$res_data[1]."] \\n".$res_data[2]."');</script>";
			}

			//############### getTxnId END #############//
			$this->template->assign($pgVal);
			$this->template->assign($pg_param);
			$this->template->assign($txnResult);
			$this->template->assign(array(
				'pgVal'=>$pgVal,
				'data_order'=>$data_order,
				'hash_String'=>$hash_String
			));

			$this->template->template_dir = BASEPATH."../order";
			$this->template->compile_dir = BASEPATH."../_compile/";
			$this->template->define(array('tpl'=>'_kakaopay.html'));
			$this->template->print_('tpl');
		}else{
			// 필수값 부족일때..
			echo '<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>';
			echo '<script type="text/javascript">'.$this->pg_cancel_script().'</script>';
			exit;
		}
	}

	public function pg_cancel_script(){
		return '$("#wrap",parent.document).show();$("div.pay_layer",parent.document).eq(0).show();$("div.pay_layer",parent.document).eq(1).hide();$("#layer_pay",parent.document).hide();';
	}
	public function pg_open_script(){
		echo '<script type="text/javascript">';
		echo '$("#wrap",parent.document).css("display","none");';
		echo '$("#payprocessing",parent.document).css("display","block");';
		echo '</script>';
	}

	/* 결재 취소 및 실패시 iframe 부모창 제어를 위해 */
	public function complete_replace()
	{
		if( trim($_GET['res_cd']) == "" || in_array(trim($_GET['res_cd']),array("3001","9562"))){  //사용자 취소
			echo '<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>';
			echo '<script type="text/javascript">'.$this->pg_cancel_script().'</script>';
		}else{
			echo '<script type="text/javascript">';
			echo 'parent.location.href="../order/complete?no='.$_GET['no'].'";';
			echo '</script>';
		}
	}

	public function complete()
	{
		$this->load->model('ordermodel');
		$this->load->model('membermodel');

		$order_seq			= $_GET['no'];
		$session_id			= $this->session->userdata('session_id');
		$orders				= $this->ordermodel->get_order($order_seq);
		$items				= $this->ordermodel->get_item($order_seq);
		$tmp				= config_load('payment',$orders['payment']);
		$orders['mpayment']	= $orders['payment'];
		$orders['payment']	= $tmp[$orders['payment']];

		if	($this->userInfo['member_seq']){
			$members		= $this->membermodel->get_member_data($this->userInfo['member_seq']);
		}

		// 배송 정보 추출 및 배송비 계산
		$order_shippings	= $this->ordermodel->get_shipping($order_seq);
		foreach($order_shippings as $order_shipping){
			$shipping_items	= $order_shipping['shipping_items'];
			if	($shipping_items)foreach($shipping_items as $itemShip){
				$itemShipping[$itemShip['item_seq']]	= $itemShip;
				$tot['add_goods_shipping']				+= $itemShip['add_goods_shipping'];
			}

			// 기본배송 지역별 추가배송비
			$tot['area_add_delivery_cost']	+= $order_shipping['area_add_delivery_cost'];
		}
		// 기본배송비
		$tot['basic_cost']				+= $order_shippings[0]['shipping_cost'] - $tot['area_add_delivery_cost'];
		$tot['shop_shipping_cost']		+= $order_shippings[0]['shipping_cost'];
		$orders['basic_delivery']		= $order_shippings[0]['shipping_cost'] - $order_shippings[0]['area_add_delivery_cost'];
		$orders['add_basic_delivery']	= $order_shippings[0]['area_add_delivery_cost'];

		$order_config	= config_load('order');
		if	($orders["person_seq"] && ($orders['step'] == "15" || $orders['step'] == "25")){
			$sql	= "update fm_person set order_seq = '".$order_seq."' where person_seq = '".$orders["person_seq"]."'";
			$query	= $this->db->query($sql);
		}

		// 배송비 합산
		if	($orders['international'] == 'domestic')
			$orders['tot_shipping_cost']	= $orders['shipping_cost'];
		else
			$orders['tot_shipping_cost']	= $orders['international_cost'];

		// 배송비쿠폰 할인금액이 있을 경우 차감 leewh 2014-08-29
		if ($orders['coupon_sale'] > 0)
			$orders['tot_shipping_cost']	-= (int) $orders['coupon_sale'];

		$sale_list['basic']['title']			= '기본할인';
		$sale_list['event']['title']			= '이벤트';
		$sale_list['multi']['title']			= '복수구매';
		$sale_list['member']['title']			= '등급할인';
		$sale_list['mobile']['title']			= '모바일';
		$sale_list['like']['title']				= '좋아요';
		$sale_list['coupon']['title']			= '쿠폰할인';
		$sale_list['code']['title']				= '할인코드';
		$sale_list['referer']['title']			= '유입경로';
		$sale_list['shippingcoupon']['title']	= '배송비쿠폰';
		$sale_list['shippingcode']['title']		= '배송비코드';
		if($items){
			foreach($items as $key=>$item){

				if	($item['goods_kind'] == 'coupon')	$is_coupon	= true;
				else									$is_goods	= true;

				$item['add_goods_shipping']	= 0;
				foreach($order_shippings[0]['shipping_items'] as $shipping_item){
					if($shipping_item['item_seq'] == $item['item_seq']){
						$item['add_goods_shipping'] = $shipping_item['add_goods_shipping'];
					}
				}

				// 국내배송일 경우 배송비 합산
				if($orders['international'] == 'domestic' && $orders['international'] == 'domestic' ) {
					$orders['tot_shipping_cost']	+= (int) $item['goods_shipping_cost'];
				}
				$item['goods_shipping_cost']	-= $item['add_goods_shipping'];

				$reOptions			= array();
				$options 			= $this->ordermodel->get_option_for_item($item['item_seq']);
				$item['tot_ea']		= 0;
				$item['tot_price']	= 0;
				if($options) foreach($options as $data){
					$item['goods_row_cnt']++;
					$item['tot_ea'] 			+= $data['ea'];
					$item['tot_price'] 			+= $data['ea'] * $data['price'];
					$data['tot_result_price']	= $sales['price'];
					$data['tot_price']			= $data['ea'] * $data['price'];
					$data['tot_ori_price']		= $data['ea'] * $data['ori_price'];
					$data['tot_reserve']		= $data['ea'] * $data['reserve'];
					$data['tot_point']			= $data['ea'] * $data['point'];
					$data['tot_member_sale']	= $data['ea'] * $data['member_sale'];
					$data['tot_sale_price']		= $data['tot_price'] - $data['tot_member_sale'] - $data['coupon_sale'] - $data['promotion_code_sale'] - $data['fblike_sale'] - $data['mobile_sale'] - $data['referer_sale'];
					$tot_goods_price += $data['tot_sale_price'];

					// 새로 추가 @2014-11-06
					$total_reserve					+= $data['tot_reserve'];
					$total_point					+= $data['tot_point'];
					$total_sale_price				+= $data['tot_member_sale'] + $data['coupon_sale'] + $data['promotion_code_sale'] + $data['fblike_sale'] + $data['mobile_sale'] + $data['referer_sale'];
					$data['org_price']				= ($data['consumer_price']) ? $data['consumer_price'] : $data['org_price'];
					$data['tot_org_price']			= $data['org_price'] * $data['ea'];
					$data['tot_basic_sale']			= $data['ea'] * $data['basic_sale'];
					$data['tot_event_sale']			= $data['ea'] * $data['event_sale'];
					$data['tot_multi_sale']			= $data['ea'] * $data['multi_sale'];
					// 정가 기준 시 기본할인들 추가
					//$total_sale_price				+= $data['tot_basic_sale'] + $data['tot_event_sale'] + $data['tot_multi_sale'];
					//$sale_list['basic']['price']	+= $data['tot_basic_sale'];
					//$sale_list['event']['price']	+= $data['tot_event_sale'];
					//$sale_list['multi']['price']	+= $data['tot_multi_sale'];
					$sale_list['member']['price']	+= $data['tot_member_sale'];
					$sale_list['mobile']['price']	+= $data['mobile_sale'];
					$sale_list['like']['price']		+= $data['fblike_sale'];
					$sale_list['coupon']['price']	+= $data['coupon_sale'];
					$sale_list['code']['price']		+= $data['promotion_code_sale'];
					$sale_list['referer']['price']	+= $data['referer_sale'];

					$inputs	= $this->ordermodel->get_input_for_option($item['item_seq'], $data['item_option_seq']);
					$suboptions = $this->ordermodel->get_suboption_for_option($item['item_seq'], $data['item_option_seq']);
					foreach($suboptions as $k => $data_suboption){
						$item['goods_row_cnt']++;
						$item['tot_ea'] 			+= $data_suboption['ea'];
						$item['tot_price'] 			+= $data_suboption['ea'] * $data_suboption['price'];

						$data_suboption['tot_price'] = $data_suboption['ea'] * $data_suboption['price'];
						$data_suboption['tot_member_sale'] = $data_suboption['ea'] * $data_suboption['member_sale'];
						$data_suboption['tot_sale_price'] = $data_suboption['tot_price'] - $data_suboption['tot_member_sale'];
						$data_suboption['tot_reserve'] = $data_suboption['ea'] * $data_suboption['reserve'];
						$data_suboption['tot_point'] = $data_suboption['ea'] * $data_suboption['point'];

						// 새로 추가 @2014-11-06
						$total_reserve						+= $data_suboption['tot_reserve'];
						$total_point						+= $data_suboption['tot_point'];
						$total_sale_price					+= $data_suboption['tot_member_sale'];
						$data_suboption['org_price']		= ($data_suboption['consumer_price']) ? $data_suboption['consumer_price'] : $data_suboption['org_price'];
						$data_suboption['tot_org_price']	= $data_suboption['org_price'] * $data_suboption['ea'];
						$data_suboption['tot_basic_sale']	= $data_suboption['ea'] * $data_suboption['basic_sale'];
						$data_suboption['tot_event_sale']	= $data_suboption['ea'] * $data_suboption['event_sale'];
						$data_suboption['tot_multi_sale']	= $data_suboption['ea'] * $data_suboption['multi_sale'];
						// 정가 기준 시 기본할인들 추가
						//$total_sale_price				+= $data_suboption['tot_basic_sale'] + $data_suboption['tot_event_sale'] + $data_suboption['tot_multi_sale'];
						//$sale_list['basic']['price']	+= $data_suboption['tot_basic_sale'];
						//$sale_list['event']['price']	+= $data_suboption['tot_event_sale'];
						//$sale_list['multi']['price']	+= $data_suboption['tot_multi_sale'];
						$sale_list['member']['price']	+= $data_suboption['tot_member_sale'];
						$sale_list['mobile']['price']	+= $data_suboption['mobile_sale'];
						$sale_list['like']['price']		+= $data_suboption['fblike_sale'];
						$sale_list['coupon']['price']	+= $data_suboption['coupon_sale'];
						$sale_list['code']['price']		+= $data_suboption['promotion_code_sale'];
						$sale_list['referer']['price']	+= $data_suboption['referer_sale'];

						$tot_goods_price += $data_suboption['tot_sale_price'];
						$suboptions[$k] = $data_suboption;
					}

					//$tot_price += $item['tot_price'];

					$data['inputs']			= $inputs;
					$data['suboptions']		= $suboptions;
					$reOptions[]			= $data;


					$tot_sale_price += $data['tot_sale_price'];

					$item['tot_goods_cnt']		+= count($suboptions) + 1;
				}

				// 배송비
				$item['shippings']	= '';
				$shipping_policy	= 'shop';
				$itemShip			= $itemShipping[$item['item_seq']];
				if	($item['goods_kind'] == 'goods' && $itemShip){
					$shipping_policy						= $itemShip['shipping_policy'];
					$item['shippings']['shipping_policy']	= $itemShip['shipping_policy'];
					$item['shippings']['goods_cost']		= $itemShip['goods_shipping_cost'];
					$item['shippings']['goods_add_cost']	= $itemShip['add_goods_shipping'];
					$item['shippings']['basic_cost']		= $tot['basic_cost'];
					$item['shippings']['basic_add_cost']	= $tot['area_add_delivery_cost'];
				}

				if		($item['goods_kind'] == 'coupon')	$shipping_policy	= 'coupon';
				elseif	($item['goods_kind'] == 'gift')		$shipping_policy	= 'gift';

				if	($shipping_policy == 'goods')	$shipping_policy	= 'goods_'.$item['item_seq'];

				$tot_price += $item['tot_price'];
				$tot_ea += $item['tot_ea'];

				$item['international']		= $orders['international'];
				$item['options']			= $reOptions;
				$items[$key] 				= $item;
				$Ritems[$shipping_policy][]	= $item;
				$Ritems[$shipping_policy][0]['shipping_row_cnt']	+= $item['goods_row_cnt'];
				$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
			}
		}

		// 새로 추가 @2014-11-06
		$orders['tot_org_shipping_cost']		= $tot['shop_shipping_cost'] + $tot['goods_shipping_cost'] + $tot['area_add_delivery_cost'] + $tot['add_goods_shipping'];
		$sale_list['shippingcoupon']['price']	= $orders['coupon_sale'];
		$sale_list['shippingcode']['price']		= $orders['shipping_promotion_code_sale'];
		$orders['goods_delivery']	= $tot['goods_shipping_cost'];
		$orders['add_delivery']		= $tot['area_add_delivery_cost'] + $tot['add_goods_shipping'];
		$orders['sale_list']		= $sale_list;
		$orders['total_sale_price']	= $total_sale_price + $orders['coupon_sale'] + $orders['shipping_promotion_code_sale'];
		$orders['total_reserve']	= $total_reserve;
		$orders['total_point']		= $total_point;



		$orders['tot_price']		= $tot_price;
		$orders['tot_ea']			= $tot_ea;
		$orders['tot_goods_price']	= $tot_goods_price;
		$orders['mshipping']		= $this->ordermodel->get_delivery_method($orders);

		## 결제실패로그 상세뿌리기
		$logs = $this->ordermodel->get_log($order_seq,'pay');
		$pg_logs = $this->ordermodel->get_pg_log($order_seq,'pay');
		if( (preg_match('/virtual/',$orders['mpayment']) || $orders['mpayment']=="card") && $pg_logs[0]['res_msg']){
			$logs[0]['title'] .= "(".$pg_logs[0]['res_msg'].")";
		}
		if(!$logs[0]['title']) $logs[0]['title'] = "결제실패";

		/*virtual account*/
		switch($orders['mpayment']){
			case "card":
				if($pg_logs[0]['card_name']) $orders['payment'] .= "(".$pg_logs[0]['card_name'].")";
			break;
			case "account":
				if($pg_logs[0]['bank_name']) $orders['payment'] .= "(".$pg_logs[0]['bank_name'].")";
			break;
			case "virtual":
				if($pg_logs[0]['bank_name']) $orders['payment'] .= "(".$pg_logs[0]['bank_name'].")";
			break;
		}

		// 카카오페이 표시 추가 :: 2015-02-26 lwh
		if($orders['pg'] == 'kakaopay')
			$orders['payment'] = '카카오페이 - ' . $orders['payment'];

		if($this->userInfo['member_seq']){
			$member_seq = $this->userInfo['member_seq'];
			$sql = "select address_seq from fm_delivery_address where member_seq=".$member_seq." order by address_seq desc";
			$query = $this->db->query($sql);
			$delivery_address = $query -> row_array();
			$address_seq = $delivery_address['address_seq'];
		}

		if	($order_shippings){
			foreach($order_shippings as $k1 => $data1){
				if	($data1['shipping_items']){
					foreach($data1['shipping_items'] as $k2 => $data2){
						if	($data2['goods_kind'] == 'coupon')
							$order_shippings[$k1]['is_coupon']		= true;
						else
							$order_shippings[$k1]['is_goods']		= true;
					}
				}
			}
		}

		$this->template->assign(array('members'=>$members));
		$this->template->assign(array('is_coupon'=>$is_coupon));
		$this->template->assign(array('is_goods'=>$is_goods));
		$this->template->assign(array('order_shippings'=>$order_shippings));
		$this->template->assign(array('order_config'=>$order_config));
		$this->template->assign(array('address_seq'=>$address_seq));
		$this->template->assign(array('orders'=>$orders));
		$this->template->assign(array('items'=>$items));
		$this->template->assign(array('Ritems'=>$Ritems));
		$this->template->assign(array('logs'=>$logs));
		$this->print_layout($this->template_path());

		// 네이버 지식쇼핑 CPA 스크립트
		if($this->config_basic['naver_wcs_use']=='y'){
			foreach($items as $item){
				$r_naver_cpa[]= '{"oid":'.$orders['order_seq'].', "poid":'.$item['item_seq'].', "pid":'.$item['goods_seq'].', "parpid":'.$item['goods_seq'].', "name":"'.$item['goods_name'].'", "cnt":'.$item['tot_ea'].', "price":'.$item['tot_price'].'}';
			}
			echo "
			<script type=\"text/javascript\">
			var _nao={};
			_nao[\"chn\"] = \"AD\";
			_nao[\"order\"]=[".implode(',',$r_naver_cpa)."];
			wcs.CPAOrder(_nao);
			</script>";
		}

		//네이버 마일리지
		echo "<script type='text/javascript'>$.get('../naver_mileage/complete?order_seq=".$order_seq."', function(data) {});</script>";

		// 주문메일 sms발송
		echo "<script type='text/javascript'>$.get('mail_sms?order_seq=".$order_seq."', function(data) {});</script>";

		// 고객리마인드 로그
		echo "<script type='text/javascript'>$.get('log_curation?order_seq=".$order_seq."', function(data) {});</script>";

		// 통계데이터(order) 전송
		foreach($items as $item){
			$arr_goods_seq[] = $item['goods_seq'];
		}
		$str_goods_seq = implode('|',$arr_goods_seq);
		echo "<script type='text/javascript'>statistics_firstmall('order','".$str_goods_seq."','".$order_seq."','');</script>";
	}

	function log_curation(){
		$this->load->helper('reservation');
		$order_seq = $_GET['order_seq'];
		$curation  = array("action_kind"=>"order","order_seq"=>$order_seq);
		curation_log($curation);
	}

	function mail_sms()
	{
		$this->load->model('ordermodel');
		$order_seq = $_GET['order_seq'];
		$orders = $this->ordermodel->get_order($order_seq);
		$items	= $this->ordermodel->get_item($order_seq);
		$params['goods_name']	= $items[0]['goods_name'];
		if	(count($items) > 1)
			$params['goods_name']	.= '외 '.(count($items) - 1).'건';

		$complete_id = $this->session->userdata('complete');
		$sess_user	 = $this->session->userdata("user");

		if($complete_id != $order_seq){
			if($orders['step'] == 15 && $orders['sms_15_YN'] != 'Y') {
				// 주문접수 sms발송
				if( $orders['order_cellphone'] ){
					$params['shopName']			= $this->config_basic['shopName'];
					$params['ordno']			= $order_seq;
					if($sess_user['userid']) $params['userid'] = $sess_user['userid'];
					$params['order_user']		= $orders['order_user_name'];
					$params['recipient_user']	= $orders['recipient_user_name'];
					$params['bank_account']		= ($orders['payment'] == 'bank')? $orders['bank_account'] : $orders['virtual_account'];

					$commonSmsData = array();
					$commonSmsData['order']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['order']['params'][] = $params;
					$commonSmsData['order']['order_seq'][] = $order_seq;
					commonSendSMS($commonSmsData);


					//sendSMS($orders['order_cellphone'], 'order', '', $params);

					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_15_YN'=>'Y'));
				}

				// 주문접수메일발송
				send_mail_step15($order_seq);
			}


			if($orders['step'] == 25 && $orders['sms_25_YN'] != 'Y') {
				// 결제확인메일/sms 발송
				send_mail_step25($orders['order_seq']);

				if( $orders['order_cellphone'] ){
					if	($orders['payment'] == 'bank'){
						$bank_arr				= explode(' ', $orders['bank_account']);
						$params['settle_kind']	= $bank_arr[0] . ' 입금확인';
					}else{
						$params['settle_kind']	= $orders['mpayment'] . ' 입금확인';
					}

					$params['shopName'] = $this->config_basic['shopName'];
					$params['ordno']	= $orders['order_seq'];
					if($sess_user['userid']) $params['userid'] = $sess_user['userid'];
					$params['order_user']		= $orders['order_user_name'];
					$params['recipient_user']	= $orders['recipient_user_name'];

					$commonSmsData = array();
					$commonSmsData['settle']['phone'][] = $orders['order_cellphone'];
					$commonSmsData['settle']['params'][] = $params;
					$commonSmsData['settle']['order_seq'][] = $orders['order_seq'];
					commonSendSMS($commonSmsData);

					//sendSMS($orders['order_cellphone'], 'settle', '', $params);

					$this->db->where('order_seq', $orders['order_seq']);
					$this->db->update('fm_order', array('sms_25_YN'=>'Y'));
				}
			}
			$this->session->set_userdata('complete',$order_seq);
		}
	}

	//장바구니 프로모션코드입력
	public function promotion()
	{
		$mode = !empty($_GET['mode']) ? $_GET['mode'] : 'normal';
		$cart_promotioncode = $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
		$this->template->assign('cart_promotioncode' , $cart_promotioncode);
		if($mode=='layer'){
			$this->template->define(array('tpl'=>$this->skin.'/order/_promotion.html'));
			$this->template->print_('tpl');
		}else{
			$this->print_layout($this->template_path());
		}
	}


	/**
	* 결제페이지 결제하기/주문하기 아이콘
	**/
	public function settle_order_images()
	{
		// 상품상태별 아이콘
		$tmp = code_load('goodsStatusImage');
		$goodsStatusImage = array();
		foreach($tmp as $row){
			$goodsStatusImage[$row['codecd']] = $row['value'];
		}
		$return = '';
		if( $goodsStatusImage['btn_order_pay1'] ){//결제하기 아이콘
			$btn_order_pay1 = '/data/icon/goods_status/'.$goodsStatusImage['btn_order_pay1'];
		}

		if( $goodsStatusImage['btn_order_pay2'] ){//무통장입금 주문하기 아이콘
			$btn_order_pay2 = '/data/icon/goods_status/'.$goodsStatusImage['btn_order_pay2'];
		}

		$result = array('btn_order_pay1'=>$btn_order_pay1, 'btn_order_pay2'=>$btn_order_pay2);
		echo json_encode($result);
		exit;
	}

	public function ajax_get_delivery_address(){
		if($this->userInfo['member_seq']){
			$this->load->model('membermodel');
			$type = $_GET['type'];

			switch($type){
				case "often":
					$result = $this->membermodel->get_delivery_address($this->userInfo['member_seq'],'often');
					if(!$result){
						$result = $this->membermodel->get_delivery_address($this->userInfo['member_seq'],'lately');
					}
					$return = $result[0];
				break;
				case "lately":
					$idx = is_numeric($_GET['idx']) ? (int)$_GET['idx'] : 0;
					$result = $this->membermodel->get_delivery_address($this->userInfo['member_seq'],'lately',$idx);
					$return = $result[0];
				break;
				case "member":
					$result = $this->membermodel->get_member_data($this->userInfo['member_seq']);
					$return = array(
						'recipient_zipcode' => $result['zipcode'],
						'recipient_address_type' => $result['address_type'],
						'recipient_address' => $result['address'],
						'recipient_address_street' => $result['address_street'],
						'recipient_address_detail' => $result['address_detail'],
						'recipient_user_name' => $result['user_name'],
						'recipient_phone' => $result['phone'],
						'recipient_cellphone' => $result['cellphone'],
						'recipient_email' => $result['email'],
					);
				break;
			}

			if($return) echo json_encode($return);
		}
	}
}