<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class sale
{
	## 전달받을 기본 정보
	var $cal_type				= 'each';	// 계산 방식 ( each : 상품당 계산, list : 할인목록->할인계산 )
	var $option_type			= 'option';	// 할인 적용할 옵션 ( option : 필수옵션, suboption : 추가옵션 )
	var $sub_sale				= 'n';		// 추가옵션 혜택 적용 여부
	var $total_price			= 0;		// 현재 장바구니의 혜택적용할인가 합계금액
	var $consumer_price			= 0;		// 현재 상품의 정가
	var $sale_price				= 0;		// 혜택적용 할인가
	var $price					= 0;		// 현재 상품의 판매가
	var $ea						= 0;		// 현재 상품의 구매수량
	var $goods_ea				= 0;		// 현재 상품의 총 구매수량
	var $member_seq				= 0;		// 로그인한 회원번호
	var $group_seq				= 0;		// 현재 회원의 등급
	var $category_code			= array();	// 현재 상품의 카테고리 전체 목록
	var $brand_code				= array();	// 현재 상품의 브랜드 전체 목록
	var $goods_seq				= 0;		// 현재 상품의 상품 번호
	var $coupon_download_seq	= 0;		// 다운로드 쿠폰 고유번호
	var $goods					= array();	// 현재 상품 정보
	var $reserve_cfg			= array();	// 적립금 설정 정보
	var $tot_use_emoney			= 0;		// 사용 적립금
	var $marketing_sale			= array();		// 입점마케팅 추가할인 적용 ( 예) array('member'=>'Y','referer'=>'Y','coupon'=>'Y','mobile'=>'Y') )

	## 계산된 결과에서 추가로 필요한 정보
	var $code_seq				= '';		// 할인 적용된 코드할인 고유번호
	var $referer_seq			= '';		// 할인 적용된 유입경로할인 고유번호
	var $coupon_use_array		= array();	// 사용된 쿠폰 목록
	var $coupon_same_time_y		= array();	// 사용된 쿠폰 중 단독쿠폰이 아닌 쿠폰 목록
	var $coupon_same_time_n		= array();	// 사용된 쿠폰 중 단독쿠폰 쿠폰인 목록
	var $coupon_duplication_n	= array();	// 단독쿠폰이며, 중복할인 비허용 쿠폰인 목록
	var $coupon_sale_payment_b	= array();	// 사용된 쿠폰 중 무통장일때만 사용 가능한 쿠폰 목록
	var $coupon_sale_agent_m	= array();	// 사용된 쿠폰 중 모바일에서만 사용 가능한 쿠폰 목록

	## 계산에 필요한 설정 정보
	var $suboption_sale_target	= array('basic', 'member');
	var $except_reset_vars		= array('except_reset_vars', 'suboption_sale_target', 'ci', 
										'cfg_cutting_sale', 'sale_group', 'basic_sale_array', 
										'cal_type', 'cancel_ea_array', 'eventSales', 'groupSales', 
										'mobileSales', 'likeSales', 'couponSales', 'codeSales', 
										'refererSales', 'member_seq', 'group_seq','total_price', 
										'reserve_cfg', 'tot_use_emoney', 'coupon_same_time_y', 
										'coupon_same_time_n', 'coupon_sale_payment_b', 
										'coupon_duplication_n', 'coupon_sale_agent_m');	// 초기화 시 초기화 되지 않는 값들
	var $sale_group				= array('basic', 'event', 'multi', 'member', 'mobile', 
										'like', 'coupon', 'code', 'referer');	// 할인 계산 순서 배열
	var $basic_sale_array		= array('basic', 'event', 'multi');	// 할인의 기준이될 할인가에 반영될 할인들
	var $cancel_ea_array		= array('coupon', 'code', 'referer');	// ea가 적용된 할인들
	var $cfg_cutting_sale		= array();	// 절사 설정
	var $pass_basic_sale		= false;	// 기본할인 패스 여부
	var $cfgs					= array();	// 할인이 적용된 각 할인의 설정값들
	var $eventSales				= array();	// 적용될 수 있는 이벤트 할인 설정들
	var $groupSales				= array();	// 적용될 수 있는 등급 할인 설정들
	var $mobileSales			= array();	// 적용될 수 있는 모바일 할인 설정들
	var $likeSales				= array();	// 적용될 수 있는 좋아요 할인 설정들
	var $couponSales			= array();	// 적용될 수 있는 쿠폰 할인 설정들
	var $codeSales				= array();	// 적용될 수 있는 코드 할인 설정들
	var $refererSales			= array();	// 적용될 수 있는 유입경로 할인 설정들


	public function __construct() {
		$this->ci =& get_instance();

		$this->ci->load->model('goodsmodel');
		$this->ci->load->model('categorymodel');
		$this->ci->load->model('brandmodel');
		$this->ci->load->model('goodsfblike');
		$this->ci->load->model('membermodel');
		$this->ci->load->model('couponmodel');
		$this->ci->load->model('configsalemodel');
		$this->ci->load->model('referermodel');
		$this->ci->load->model('promotionmodel');
		$this->ci->load->model('eventmodel');
		$this->ci->load->helper('common');
		$this->ci->load->helper('coupon');

		$this->set_cutting_sale();
	}

	## 절사설정
	public function set_cutting_sale(){
		// 절사 설정 저장
		if	($this->ci->config_system)	$cfg	= $this->ci->config_system;
		else							$cfg	= config_load('system');

		if	($cfg['cutting_sale_use'] != 'none'){
			$this->cfg_cutting_sale	= array('action'	=> $cfg['cutting_sale_action'], 
											'price'		=> $cfg['cutting_sale_price']);
		}
	}

	## 전역변수에 정의된 this->ci를 제외한 값들을 모두 초기화
	public function reset_init(){
		$class_vars			= get_class_vars(get_class($this));
		if	($class_vars)foreach($class_vars as $var_name => $var_value){
			if	(!in_array($var_name, $this->except_reset_vars)){
				if		(is_array($this->$var_name))	$this->$var_name	= array();
				elseif	(is_numeric($this->$var_name))	$this->$var_name	= 0;
				else									$this->$var_name	= '';
			}
		}
	}

	## 카테고리 목록 추출
	public function set_category_array(){
		// 카테고리정보
		$tmparr2	= array();
		$categorys	= $this->ci->goodsmodel->get_goods_category($this->goods_seq);
		if	($categorys)foreach($categorys as $key => $val){
			$tmparr	= $this->ci->categorymodel->split_category($val['category_code']);
			foreach($tmparr as $cate) $tmparr2[]	= $cate;
		}
		if	($tmparr2)	$this->category_code		= array_values(array_unique($tmparr2));
	}

	## 브랜드 목록 추출
	public function set_brand_array(){
		$brands		= $this->ci->goodsmodel->get_goods_brand($this->goods_seq);
		if($brands) foreach($brands as $bkey => $branddata){
			if( $branddata['link'] == 1 ){
				$brand_codear = $this->ci->brandmodel->split_brand($branddata['category_code']);
				$brand_code[] = $brand_codear[0];
			}
		}

		$this->brand_code	= $brand_code;
	}

	## 적립금 설정 정보 추출
	public function set_reserve_config(){
		$this->reserve_cfg		= config_load('reserve');
	}

	## 기존 페이지를 위한 추가함수 ( 좋아요 혜택 전체목록 )
	public function get_fblikesale_config_list(){
		$sc['type']	= 'fblike';
		$fblike		= $this->ci->configsalemodel->lists($sc);
		if	($fblike['result'])foreach($fblike['result'] as $k => $data){
			if	($data['price1']<= $this->sale_price && $data['price2'] >= $this->sale_price){
				$fblike['sale_price']		= $data['sale_price'];
				$fblike['sale_emoney']	= $data['sale_emoney'];
				$fblike['sale_point']		= $data['sale_point'];
				break;
			}
		}
		return $fblike;
	}

	## 기존 페이지를 위한 추가함수 ( 등급 혜택 전체목록 )
	public function get_groupsale_config(){
		$group_list	= $this->ci->membermodel->get_goods_group_benifits($this->goods['sale_seq']);
		if	($group_list)foreach($group_list as $k => $data){
			if	(in_array($data['use_type'], array('AUTO', 'AUTOPART'))){
				// 기존 스킨용
				if	($data['sale_price_type'] == 'WON')		$data['sale']			= $data['sale_price'];
				else										$data['sale_rate']		= $data['sale_price'];
				if	($data['reserve_price_type'] == 'WON')	$data['reserve']		= $data['reserve_price'];
				else										$data['reserve_rate']	= $data['reserve_price'];
				if	($data['point_price_type'] == 'WON')	$data['point']			= $data['point_price'];
				else										$data['point_rate']		= $data['point_price'];

				$group_benifit_list[$k]	= $data;
			}
		}

		return $group_benifit_list;
	}

	## 상품, 카테고리, 브랜드 Issue 설정 체크
	public function chk_issues($issue_type, $category, $goods, $brands = false){
		switch($issue_type){
			case 'issue':
				$result		= false;
				if		(!$result && $category)foreach($category as $cate){
					if		(in_array($cate, $this->category_code)){
						$result		= true;
						break;
					}
				}
				if		(!$result && $brands)foreach($brands as $brand){
					if		(in_array($brand, $this->brand_code)){
						$result		= true;
						break;
					}
				}
				if		(!$result && in_array($this->goods_seq, $goods)){
					$result			= true;
				}
			break;
			case 'except':
				$result		= true;
				if		($result && $category)foreach($category as $cate){
					if		(in_array($cate, $this->category_code)){
						$result	= false;
						break;
					}
				}
				if		($result && $brands)foreach($brands as $brand){
					if		(in_array($brand, $this->brand_code)){
						$result		= false;
						break;
					}
				}
				if		($result && in_array($this->goods_seq, $goods)){
					$result		= false;
				}
			break;
			case 'all':
			default:
				$result		= true;
			break;
		}

		return $result;
	}

	## 전달받을 기본 정보 설정
	public function set_init($param = array()){
		if	(isset($param['cal_type']))				$this->cal_type				= $param['cal_type'];
		if	(isset($param['option_type']))			$this->option_type			= $param['option_type'];
		if	(isset($param['sub_sale']))				$this->sub_sale				= $param['sub_sale'];
		if	(isset($param['reserve_cfg']))			$this->reserve_cfg			= $param['reserve_cfg'];
		if	(isset($param['tot_use_emoney']))		$this->tot_use_emoney		= $param['tot_use_emoney'];
		if	(isset($param['total_price']))			$this->total_price			= $param['total_price'];
		if	(isset($param['consumer_price']))		$this->consumer_price		= $param['consumer_price'];
		if	(isset($param['sale_price']))			$this->sale_price			= $param['sale_price'];
		if	(isset($param['price']))				$this->price				= $param['price'];
		if	(isset($param['ea']))					$this->ea					= $param['ea'];
		if	(isset($param['goods_ea']))				$this->goods_ea				= $param['goods_ea'];
		if	(isset($param['member_seq']))			$this->member_seq			= $param['member_seq'];
		if	(isset($param['group_seq']))			$this->group_seq			= $param['group_seq'];
		if	(isset($param['category_code']))		$this->category_code		= $param['category_code'];
		if	(isset($param['brand_code']))			$this->brand_code			= $param['brand_code'];
		if	(isset($param['goods_seq']))			$this->goods_seq			= $param['goods_seq'];
		if	(isset($param['coupon_download_seq']))	$this->coupon_download_seq	= $param['coupon_download_seq'];
		if	(isset($param['marketing_sale']))	$this->marketing_sale	= $param['marketing_sale'];
		if	(isset($param['goods'])){
			$this->goods				= $param['goods'];
			if	(!$param['goods_seq'] && $param['goods']['goods_seq'])
				$this->goods_seq		= $param['goods']['goods_seq'];
			if	(!$param['category_code'] && $param['goods']['category_code'])
				$this->category_code	= $param['goods']['category_code'];
			if	($this->option_type != 'suboption'){
				if	(!$param['price'] && $param['goods']['price'])
					$this->price			= $param['goods']['price'];
				if	(!$param['consumer_price'] && $param['goods']['consumer_price'])
					$this->consumer_price	= $param['goods']['consumer_price'];
				if	(!$param['consumer_price'] && $this->price)
					$this->consumer_price	= $this->price;
				if	(!$param['ea'] && $param['goods']['ea'])
					$this->ea				= $param['goods']['ea'];
			}
		}

		if	(!$this->goods_ea && $this->ea)	$this->goods_ea		= $this->ea;
		if	($this->goods_seq && (!is_array($this->category_code) || count($this->category_code) < 1))
			$this->set_category_array();
		if	($this->goods_seq && (!is_array($this->brand_code) || count($this->brand_code) < 1))
			$this->set_brand_array();
	}

	## 페이지별 적용 할인 확인 ( all : 금액계산 + 할인내용, textonly : 할인내용만, none : 적용안함 )
	public function apply_sale_per_page($apply_page, $sale_type, $pass_basic_sale = false){

		$saleStatue		= 'none';
		switch($apply_page){
			case 'list':
			case 'wish':
			case 'search':
			case 'search_auto':
			case 'lately':
			case 'lately_scroll':
			case 'relation':
			case 'option':
				if		($sale_type == 'basic')		$saleStatue		= 'all';
				elseif	($sale_type == 'event')		$saleStatue		= 'all';
//				elseif	($sale_type == 'multi')		$saleStatue		= 'all';
				elseif	($sale_type == 'member')	$saleStatue		= 'all';
				elseif	($sale_type == 'mobile')	$saleStatue		= 'all';
			break;

			case 'view':
				if		($sale_type == 'basic')		$saleStatue		= 'all';
				elseif	($sale_type == 'event')		$saleStatue		= 'all';
//				elseif	($sale_type == 'multi')		$saleStatue		= 'all';
				elseif	($sale_type == 'member')	$saleStatue		= 'all';
				elseif	($sale_type == 'mobile')	$saleStatue		= 'all';
				else								$saleStatue		= 'textonly';
			break;

			case 'cart':
				if		($sale_type == 'coupon' || $sale_type == 'code')	$saleStatue		= 'none';
				else														$saleStatue		= 'all';
			break;

			case 'saleprice':
				if	(in_array($sale_type, $this->basic_sale_array))			$saleStatue		= 'all';
			break;

			case 'order':
				$saleStatue		= 'all';
			break;
		}

		if($_GET['personal']){
			$saleStatue		= 'none';
		}

		// 기본할인가가 넘어온 경우 기본 할인은 할인에서 제외
		if	($pass_basic_sale && in_array($sale_type, $this->basic_sale_array))
			$saleStatue		= 'none';
		// 추가구성옵션이고 추가구성옵션 할인 대상이 아닌 경우 할인 제외
		if	($this->option_type == 'suboption' && !in_array($sale_type, $this->suboption_sale_target))
			$saleStatue		= 'none';
		// 추가구성옵션이고 추가구성옵션 할인 대상이나, 추가할인 적용여부가 y가 아닌 경우 할인 제외
		if	($this->option_type == 'suboption' && $this->sub_sale != 'y' && !in_array($sale_type, $this->basic_sale_array)){
			$saleStatue		= 'none';
		}

		// 목록형에서는 설명전용에 대한 부분은 pass!!
		if	($this->cal_type == 'list' && $saleStatue == 'textonly')	$saleStatue		= 'none';
		// 모바일할인은 모바일일때만 적용됨
		if	($sale_type == 'mobile' && !$this->ci->_is_mobile_agent)	$saleStatue		= 'none';
		// 프로모션 코드 사용여부가 Y가 아닌 경우 적용안함
		if	($sale_type == 'code' && $this->ci->reserves['promotioncode_use']!='Y') $saleStatue = 'none';
		// 입점마케팅 전달 데이터 통합 설정시 추가할인 적용 leewh 2015-02-02
		if ($sale_type == 'mobile' && $this->marketing_sale['mobile']=='Y') $saleStatue = 'all';
		if ($sale_type == 'coupon' && $this->marketing_sale['coupon']=='Y') $saleStatue = 'all';
		if ($sale_type == 'referer' && $this->marketing_sale['referer']=='Y') $saleStatue = 'all';


		return $saleStatue;
	}

	## 최종할인 금액 절사 처리
	public function cut_sale_price($price){
		$action		= $this->cfg_cutting_sale['action'];
		$unit		= $this->cfg_cutting_sale['price'];

		if	($action && $unit > 0){
			switch($action){
				case 'dscending':
					$price = floor($price / $unit) * $unit;
				break;
				case 'rounding':
					$price = round($price / $unit) * $unit;
				break;
				case 'ascending':
					$price = ceil($price / $unit) * $unit;
				break;
			}
		}

		return $price;
	}

	## 할인 내역을 미리 읽어옴
	public function preload_set_config($apply_page = 'order'){
		foreach($this->sale_group as $sale_type){
			if	($saleStatue != 'none'){
				$funcName					= 'set_'.$sale_type.'_sale';
				$this->$funcName();
			}
		}
	}

	## 할인 계산
	public function calculate_sale_price($apply_page = 'order'){

		if	($this->sale_price){
			$pass_basic_sale				= true;
			$result_price					= $this->sale_price * $this->ea;
			$one_result_price				= $this->sale_price;
		}else{
			$pass_basic_sale				= false;
			$result_price					= $this->consumer_price * $this->ea;
			$one_result_price				= $this->consumer_price;
			if	($this->option_type == 'suboption')	$this->sale_price	= $this->price;
		}

		$after_price['original']		= $result_price;
		$one_after_price['original']	= $one_result_price;
		foreach($this->sale_group as $sale_type){

			$saleStatue		= $this->apply_sale_per_page($apply_page, $sale_type, $pass_basic_sale);

			if	($saleStatue != 'none'){
				if	($this->cal_type == 'list')	$funcName	= 'list_'.$sale_type.'_sale';
				else if ($sale_type=='coupon' && $this->marketing_sale['coupon']=='Y') $funcName	= $sale_type.'_marketing_sale';
				else							$funcName	= $sale_type.'_sale';

				$sale_result				= $this->$funcName();
				$sale_price					= floor($sale_result['sale_price']);
				if	($sale_type != 'basic')	$sale_price	= $this->cut_sale_price(floor($sale_price));
				$target_sale				= 0;
				if	($sale_type == 'event')	{
					$eventEnd		= $sale_result['eventEnd'];
					$event_order_ea		= $sale_result['event_order_ea'];
				}
				if	($sale_result['target_sale'] == 1)	$target_sale	= 1;
				if	($saleStatue != 'textonly'){
					// 할인 기준 할인가 계산.
					if	(in_array($sale_type, $this->basic_sale_array)){
						if	($target_sale == 1){
							unset($sale_list,$one_sale_list,$text_list,$total_sale_price,$one_total_sale_price,$after_price,$one_after_price);

							// 기본할인 시 정가가 있으나, 정가가 판매가 보다 작을 경우 판매가에서 할인
							if	($sale_type == 'basic' && $this->consumer_price > 0 && 
								$this->consumer_price < $this->price){
								$this->sale_price				= $this->price - $sale_price;
								$after_price['original']		= $this->price * $this->ea;
								$one_after_price['original']	= $this->price;
							// 정가 기준 시 정가가 있으면 정가에서 할인
							}elseif	($this->consumer_price > 0){
								$this->sale_price				= $this->consumer_price - $sale_price;
								$after_price['original']		= $this->consumer_price * $this->ea;
								$one_after_price['original']	= $this->consumer_price;
							}else{
								$this->sale_price				= $this->price - $sale_price;
								$after_price['original']		= $this->price * $this->ea;
								$one_after_price['original']	= $this->price;
							}
						}else{
							$this->sale_price	-= $sale_price;
						}
						$sale_list[$sale_type]			= $sale_price * $this->ea;
						$one_sale_list[$sale_type]		= $sale_price;
						$result_price					= $this->sale_price * $this->ea;
						$one_result_price				= $this->sale_price;
						$total_sale_price				+= $sale_price * $this->ea;
						$one_total_sale_price			+= $sale_price;

					// 혜택적용할인가로 계산되는 할인들
					}else{
						// ea가 적용된 할인과 아닌 할인 분리
						if	(in_array($sale_type, $this->cancel_ea_array)){
							$sale_list[$sale_type]			= $sale_price;
							$one_sale_list[$sale_type]		= $sale_price / $this->ea;
							$result_price					-= $sale_price;
							$one_result_price				-= $sale_price / $this->ea;
							$total_sale_price				+= $sale_price;
							$one_total_sale_price			+= $sale_price / $this->ea;
						}else{
							$sale_list[$sale_type]			= $sale_price * $this->ea;
							$one_sale_list[$sale_type]		= $sale_price;
							$result_price					-= $sale_price * $this->ea;
							$one_result_price				-= $sale_price;
							$total_sale_price				+= $sale_price * $this->ea;
							$one_total_sale_price			+= $sale_price;
						}
					}

					// 최종 금액은 0원 미만이 될 수 없다.
					if	($result_price < 0)		$result_price		= 0;
					if	($one_result_price < 0)	$one_result_price	= 0;

					$after_price[$sale_type]		= $result_price;
					$one_after_price[$sale_type]	= $one_result_price;
					$addSale[]						= $sale_type;
					$target_list[$sale_type]		= $target_sale;
				}
				$title_list[$sale_type]		= $sale_result['sale_title'];
				$text_list[$sale_type]		= $sale_result['sale_txt'];
				$seq_list[$sale_type]		= $sale_result['sale_seq'];
				$subject_list[$sale_type]	= $sale_result['sale_subject'];
				if	($sale_result['sale_mtxt'])	$mtext_list[$sale_type]	= $sale_result['sale_mtxt'];
				else							$mtext_list[$sale_type]	= $sale_result['sale_txt'];
			}
		}

		// 할인이 모두 반영된 할인가로 적립금 포인트를 계산
		if	($result_price > 0){
			if	(!$this->reserve_cfg)	$this->set_reserve_config();

			$result_one_price			= $result_price / $this->ea;
			if	(in_array('event', $addSale)){
				$reserve					= $this->event_sale_reserve($result_one_price);
				$one_tot_reserve			+= $reserve;
				$tot_reserve				+= $reserve * $this->ea;
				$reserve_list['event']		= $reserve * $this->ea;
				$one_reserve_list['event']	= $reserve;
				$point						= $this->event_sale_point($result_one_price);
				$one_tot_point				+= $point;
				$tot_point					+= $point * $this->ea;
				$point_list['event']		= $point * $this->ea;
				$one_point_list['event']	= $point;
			}
			if	(in_array('member', $addSale)){
				$reserve					= $this->member_sale_reserve($result_one_price);
				$one_tot_reserve			+= $reserve;
				$tot_reserve				+= $reserve * $this->ea;
				$reserve_list['member']		= $reserve * $this->ea;
				$one_reserve_list['member']	= $reserve;
				$point						= $this->member_sale_point($result_one_price);
				$one_tot_point				+= $point;
				$tot_point					+= $point * $this->ea;
				$point_list['member']		= $point * $this->ea;
				$one_point_list['member']	= $point;
			}
			if	(in_array('mobile', $addSale)){
				$reserve					= $this->mobile_sale_reserve($result_one_price);
				$one_tot_reserve			+= $reserve;
				$tot_reserve				+= $reserve * $this->ea;
				$reserve_list['mobile']		= $reserve * $this->ea;
				$one_reserve_list['mobile']	= $reserve;
				$point						= $this->mobile_sale_point($result_one_price);
				$one_tot_point				+= $point;
				$tot_point					+= $point * $this->ea;
				$point_list['mobile']		= $point * $this->ea;
				$one_point_list['mobile']	= $point;
			}
			if	(in_array('like', $addSale)){
				$reserve					= $this->like_sale_reserve($result_one_price);
				$one_tot_reserve			+= $reserve;
				$tot_reserve				+= $reserve * $this->ea;
				$reserve_list['like']		= $reserve * $this->ea;
				$one_reserve_list['like']	= $reserve;
				$point						= $this->like_sale_point($result_one_price);
				$one_tot_point				+= $point;
				$tot_point					+= $point * $this->ea;
				$point_list['like']			= $point * $this->ea;
				$one_point_list['like']		= $point;
			}

			// 최종 할인율 역계산
			if	($this->consumer_price > 0)
				$sale_per	= 100 - floor(( $result_price / ($this->consumer_price * $this->ea) ) * 100);
			else
				$sale_per	= 100 - floor(( $result_price / ($this->price * $this->ea) ) * 100);
		}else{
			$sale_per		= 100;
		}

		$return	= array('apply_page'			=> $apply_page, 
						'goods_seq'				=> $this->goods_seq,
						'consumer_price'		=> $this->consumer_price,
						'price'					=> $this->price,
						'ea'					=> $this->ea,
						'sale_per'				=> ($sale_per < 0) ? 0 : $sale_per,
						'sale_price'			=> $this->sale_price * $this->ea,
						'one_sale_price'		=> $this->sale_price,
						'result_price'			=> $result_price, 
						'one_result_price'		=> $one_result_price, 
						'total_sale_price'		=> $total_sale_price, 
						'one_total_sale_price'	=> $one_total_sale_price, 
						'one_tot_reserve'		=> $one_tot_reserve, 
						'tot_reserve'			=> $tot_reserve, 
						'one_tot_point'			=> $one_tot_point, 
						'tot_point'				=> $tot_point, 
						'text_list'				=> $text_list, 
						'mtext_list'			=> $mtext_list, 
						'title_list'			=> $title_list, 
						'subject_list'			=> $subject_list, 
						'seq_list'				=> $seq_list, 
						'sale_list'				=> $sale_list, 
						'one_sale_list'			=> $one_sale_list, 
						'target_list'			=> $target_list, 
						'reserve_list'			=> $reserve_list, 
						'one_reserve_list'		=> $one_reserve_list, 
						'point_list'			=> $point_list, 
						'one_point_list'		=> $one_point_list,
						'after_price'			=> $after_price,
						'one_after_price'		=> $one_after_price,
						'event_order_ea'		=> $event_order_ea,
						'eventEnd'				=> $eventEnd);
		return $return;
	}


	##↓↓↓↓↓↓↓↓↓↓	1:1 할인 계산 함수들		↓↓↓↓↓↓↓↓↓↓##


	## 기본할인
	public function basic_sale(){
		$return['sale_price']	= 0;
		$return['sale_seq']		= 0;
		$return['target_sale']	= 1;
		$return['sale_subject']	= '';
		$return['sale_title']	= '기본할인';

		if	($this->consumer_price > $this->price){
			$return['target_sale']	= 1;
			$return['sale_price']	= $this->consumer_price - $this->price;
			$sale_per				= round(($return['sale_price'] / $this->consumer_price) * 100);
			$return['sale_txt']		= $sale_per.'% 할인';
		}else{
			$return['target_sale']	= 1;
			$return['sale_price']	= 0;
			$sale_per				= 0;
			$return['sale_txt']		= '0% 할인';
		}

		return $return;
	}

	## 이벤트 할인
	public function event_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '이벤트';
		$event					= $this->ci->goodsmodel->get_event_price($this->price, $this->goods_seq, $this->category_code, $this->consumer_price, $this->goods);
		$this->cfgs['event']	= $event;

		if($event['event_seq']) {
			$return['sale_subject']	= $event['title'];
			$return['sale_seq']		= $event['event_seq'];
			$return['target_sale']	= $event['target_sale'];
			if	($event['target_sale'] == 1)
				$return['sale_price']	= ($this->consumer_price > $event['event_sale_unit']) ? $event['event_sale_unit'] : 0;
			else
				$return['sale_price']	= ($this->price > $event['event_sale_unit']) ? $event['event_sale_unit'] : 0;

			// 할인 설명 문구
			if	($event['event_sale'] > 0)		$sale_txt	= $event['event_sale'].'%추가할인 ';
												$sale_mtxt	= $sale_txt;
			if	($event['event_reserve'] > 0)	$sale_txt	.= $event['event_reserve'].'%추가적립 ';
			if	($event['event_point'] > 0)		$sale_txt	.= $event['event_point'].'%추가포인트 ';
			if	($event['app_week_title']){
				$return['sale_txt']		= $sale_txt . '<br/>' . $event['app_week_title'] . ' '
										. substr($event['app_start_time_title'],0,2) . '시 ~ '
										. substr($event['app_end_time_title'],0,2) . '시 '
										. '(~' . date('Y-m-d', strtotime($event['end_date'])) . '까지)';
				$return['sale_mtxt']	= $sale_mtxt . '(~' . date('Y-m-d', strtotime($event['end_date'])) . '까지)';
			}else{
				$return['sale_txt']		= $sale_txt.'(~'.date('Y-m-d', strtotime($event['end_date'])).'까지)';
				$return['sale_mtxt']	= $sale_mtxt.'(~'.date('Y-m-d', strtotime($event['end_date'])).'까지)';
			}

			if	($event['event_type'] == 'solo'){
				if	($event['app_end_time']){
					$eventEnd['year']	= date("Y");
					$eventEnd['month']	= date("m");
					$eventEnd['day']	= date("d");
					$eventEnd['hour']	= substr($event['app_end_time'], 0, 2);
					$eventEnd['min']	= substr($event['app_end_time'], -2);
					$eventEnd['second']	= '00';
				}else{
					$eventEndDateTime	= explode(" ", $event['end_date']);
					$eventEndDate		= explode("-", $eventEndDateTime[0]);
					$eventEnd['year']	= $eventEndDate[0];
					$eventEnd['month']	= $eventEndDate[1];
					$eventEnd['day']	= $eventEndDate[2];
					$eventEndTime		= explode(":", $eventEndDateTime[1]);
					$eventEnd['hour']	= $eventEndTime[0];
					$eventEnd['min']	= $eventEndTime[1];
					$eventEnd['second']	= $eventEndTime[2];
				}
				$return['eventEnd']		= $eventEnd;
			}
		}

		return $return;
	}

	## 복수구매 할인
	public function multi_sale(){
		$return['sale_price']	= 0;
		$return['sale_seq']		= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '복수구매';

		if	($this->goods['multi_discount_use']){
			if	($this->goods['multi_discount_ea'] &&
				$this->goods['multi_discount'] && $this->goods['multi_discount_unit'] && 
				$this->goods_ea >= $this->goods['multi_discount_ea']){

				if( $this->goods['multi_discount_unit'] == 'percent' && $this->goods['multi_discount'] < 100 ){
					$return['sale_price']	= ( $this->sale_price * $this->goods['multi_discount'] / 100 );
				}else if($this->sale_price > $this->goods['multi_discount'] ) {
					$return['sale_price']	= $this->goods['multi_discount'];
				}
			}

			$return['sale_txt']		= $this->goods['multi_discount_ea'].'개 이상 구매 시 개당 ';
			$return['sale_mtxt']	= '1개당 ';
			if	($this->goods['multi_discount_unit'] == 'won'){
				$return['sale_txt']		.= $this->goods['multi_discount'].'원 할인';
				$return['sale_mtxt']	.= $this->goods['multi_discount'].'원 할인';
			}else{
				$return['sale_txt']		.= $this->goods['multi_discount'].'% 할인';
				$return['sale_mtxt']	.= $this->goods['multi_discount'].'% 할인';
			}
		}

		return $return;
	}

	## 등급 할인
	public function member_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '등급할인';
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;

		// 입점마케팅 전달 데이터 회원등급별 할인 적용(1 : 일반 등급 회원) leewh 2015-02-03
		if ($this->marketing_sale['member']=='Y') {
			$this->group_seq = 1;
		}

		$return['sale_price']	= (int) $this->ci->membermodel->get_member_group($this->group_seq, $this->goods_seq, $this->category_code, $this->sale_price, $total_price, $this->goods['sale_seq'], $this->option_type);
		$benefit				= $this->ci->membermodel->group_benifit;

		if	(!$this->group_seq){

			$this->cfgs['no_member']	= $benefit; // 비회원용 혜택
			$return['sale_txt']			= '최대 ';
			unset($benefit);

			// 비회원의 경우 가장 높은 등급의 혜택을 보여줌.
			$benefitList	= $this->ci->membermodel->get_goods_group_benifits($this->goods['sale_seq']);
			$bfGroupSeq	= 0;

			if	($benefitList)foreach($benefitList as $data){
				if	(in_array($data['use_type'], array('AUTO', 'AUTOPART'))){
					// 예상 할인가
					if	($data['sale_price_type'] == 'WON'){
						$data['sale']		= $data['sale_price'];	// 기존 스킨용
						$tmp_expect_price	= $data['sale_price'];
					}else{
						$data['sale_rate']	= $data['sale_price'];	// 기존 스킨용
						$tmp_expect_price	= floor($this->sale_price * ($data['sale_price'] / 100));
					}

					// 기존 스킨용
					if	($data['reserve_price_type'] == 'WON')	$data['reserve']		= $data['reserve_price'];
					else										$data['reserve_rate']	= $data['reserve_price'];
					if	($data['point_price_type'] == 'WON')	$data['point']			= $data['point_price'];
					else										$data['point_rate']		= $data['point_price'];

					// 1개 구매 시 기준으로 가장 높은 할인을 안내함
					if	($expect_price < $tmp_expect_price){
						$expect_price	= $tmp_expect_price;
						$benefit		= $data;
					}
				}
			}
		}else{
			// 기존 스킨용
			if	($benefit['sale_price_type'] == 'WON')		$benefit['sale']		= $benefit['sale_price'];
			else											$benefit['sale_rate']	= $benefit['sale_price'];
			if	($benefit['reserve_price_type'] == 'WON')	$benefit['reserve']		= $benefit['reserve_price'];
			else											$benefit['reserve_rate']	= $benefit['reserve_price'];
			if	($benefit['point_price_type'] == 'WON')		$benefit['point']		= $benefit['point_price'];
			else											$benefit['point_rate']	= $benefit['point_price'];

			$this->cfgs['member']	= $benefit;
			$return['sale_txt']		= $benefit['group_name'] . ' ';
		}

		if	($benefit){
			$return['sale_seq']		= $benefit['sale_seq'];

			// 할인 정보
			$return['sale_mtxt']		= $return['sale_txt'];
			if	($benefit['sale_price_type'] == 'WON'){
				$return['sale_txt']		.= $benefit['sale_price'].'원추가할인 ';
				$return['sale_mtxt']	.= $benefit['sale_price'].'원추가할인 ';
			}else{
				$return['sale_txt']		.= $benefit['sale_price'].'%추가할인 ';
				$return['sale_mtxt']	.= $benefit['sale_price'].'%추가할인 ';
			}

			// 적립 정보
			if	($benefit['reserve_price_type'] == 'WON')
				$return['sale_txt']		.= $benefit['reserve_price'].'원추가적립 ';
			else
				$return['sale_txt']		.= $benefit['reserve_price'].'%추가적립 ';

			// 포인트 정보
			if	($benefit['point_price_type'] == 'WON')
				$return['sale_txt']		.= $benefit['point_price'].'원추가포인트 ';
			else
				$return['sale_txt']		.= $benefit['point_price'].'%추가포인트 ';
		}else{
			$return['sale_txt']			= '';
		}

		return $return;
	}

	## 모바일 할인
	public function mobile_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '모바일';

		// 모바일 할인 설정
		$sc['type']			= 'mobile';
		if	($this->mobileSales)	$mobile_cfg_list	= $this->mobileSales;
		else						$mobile_cfg_list	= $this->ci->configsalemodel->lists($sc);

		$tmp_sum_sale_price	= $this->sale_price;
		if	($this->ea > 1)	$tmp_sum_sale_price	= $this->sale_price * $this->ea;
		foreach($mobile_cfg_list['result'] as $k => $cfg) {
			$m++;
			$basic_cfg	= $cfg;
			if	($cfg['price1'] <= $tmp_sum_sale_price && $tmp_sum_sale_price <= $cfg['price2']){
				// 가장 높은 할인을 반영
				if	($cfg['sale_price'] > $current_cfg['sale_price']){
					$current_cfg	= $cfg;
				}
			}

			if	($m > 1 && $best_cfg['sale_price'] < $cfg['sale_price']){
				$best_cfg	= $cfg;
			}
		}
		if	($current_cfg)	$this->cfgs['mobile']		= $current_cfg;

		// 해당 할인에 대한 정보로 할인 및 노출
		$return['sale_txt']		= '';
		if		($current_cfg){
			$return['sale_price']	= $this->sale_price * ($current_cfg['sale_price'] / 100);
		}
		// 가장 높은 할인에 대한 정보로 노출
		if		($best_cfg){
			$current_cfg				= $best_cfg;
			$return['sale_txt']		= '최대 ';
		// 기본 할인 정보로 노출
		}else{
			$current_cfg				= $basic_cfg;
		}

		// 할인혜택 정보
		if		($current_cfg){
			$return['sale_seq']		= $current_cfg['seq'];
			$return['sale_txt']		.= $current_cfg['sale_price'].'% 추가할인 ';
			$return['sale_mtxt']	= $return['sale_txt'];
			if	($current_cfg['sale_emoney'] > 0)
				$return['sale_txt']		.= $current_cfg['sale_emoney'].'% 추가적립 ';
			if	($current_cfg['sale_point'] > 0)
				$return['sale_txt']		.= $current_cfg['sale_point'].'% 추가포인트 ';
		}

		return $return;
	}

	## 좋아요 할인
	public function like_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '좋아요';

		// 좋아요 여부 체크
		if	(!$this->member_seq){
			if( $this->ci->session->userdata('fbuser') ) {
				$sns_id	= $this->ci->session->userdata('fbuser');
			}elseif(get_cookie('fbuser')){
				$sns_id	= get_cookie('fbuser');
			}
		}
		if	($this->member_seq > 0 || $sns_id){
			$sc['whereis']	= " and goods_seq = '".$this->goods_seq."' ";
			if	($this->member_seq)	$sc['whereis']	.= " and member_seq = '".$this->member_seq."' ";
			else					$sc['whereis']	.= " and sns_id = '".$sns_id."' ";
			$fbstatus		= $this->ci->goodsfblike->get_data($sc);
		}

		// 할인 여부 및 할인금액 계산
		$sc['type']	= 'fblike';
		$fblike_cfg_list	= $this->ci->configsalemodel->lists($sc);
		if	($fblike_cfg_list){
			if	( $fblike_cfg_list['result'] ){
				$tmp_sum_sale_price	= $this->sale_price;
				if	($this->ea > 1)	$tmp_sum_sale_price	= $this->sale_price * $this->ea;
				foreach($fblike_cfg_list['result'] as $fblike => $cfg) {
					$f++;
					$basic_cfg	= $cfg;
					if	($fbstatus['like_seq'] > 0 && 
						$cfg['price1'] <= $tmp_sum_sale_price && $cfg['price2'] >= $tmp_sum_sale_price){
						// 가장 높은 할인을 반영
						if	($cfg['sale_price'] > $current_cfg['sale_price']){
							$current_cfg	= $cfg;
						}
					}

					if	($f > 1 && $best_cfg['sale_price'] < $cfg['sale_price']){
						$best_cfg	= $cfg;
					}
				}
			}
			if	($current_cfg)	$this->cfgs['like']		= $current_cfg;

			// 해당 할인에 대한 정보로 할인 및 노출
			$return['sale_txt']		= '';
			if		($current_cfg){
				$return['sale_price']	= $this->sale_price * ($current_cfg['sale_price'] / 100);
			}
			// 가장 높은 할인에 대한 정보로 노출
			if		($best_cfg){
				$current_cfg				= $best_cfg;
				$return['sale_txt']		= '최대 ';
			// 기본 할인 정보로 노출
			}else{
				$current_cfg				= $basic_cfg;
			}

			// 할인혜택 정보
			if		($current_cfg){
				$return['sale_seq']		= $current_cfg['seq'];
				$return['sale_txt']		.= $current_cfg['sale_price'].'% 추가할인 ';
				$return['sale_mtxt']	= $return['sale_txt'];
				if	($current_cfg['sale_emoney'] > 0)
					$return['sale_txt']		.= $current_cfg['sale_emoney'].'% 추가적립 ';
				if	($current_cfg['sale_point'] > 0)
					$return['sale_txt']		.= $current_cfg['sale_point'].'% 추가포인트 ';
			}
		}

		return $return;
	}

	## 쿠폰 할인 ( 회원전용임 )
	public function coupon_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '쿠폰할인';
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;
		if	($this->member_seq > 0){
			$coupons	= $this->ci->couponmodel->get_able_use_list($this->member_seq, $this->goods_seq, $this->category_code, $total_price, $this->sale_price, $this->goods_ea);
			if	($coupons){
				foreach($coupons as $downloads){
					if	($this->coupon_download_seq == $downloads['download_seq']){
						$this->coupon_use_array[]	= $this->coupon_download_seq;

						if	($downloads['duplication_use'] == 1){
							$return['sale_price'] = (int) $downloads['goods_sale'] * $this->ea;
						}else{
							$return['sale_price'] = (int) $downloads['goods_sale'];
						}

						// 단독쿠폰체크
						if	($downloads['coupon_same_time'] == 'N' ) {
							if	( !in_array($this->coupon_download_seq, $this->coupon_same_time_n) ) {
								$this->coupon_same_time_n[]		= $this->coupon_download_seq;
							}
							if	( $downloads['duplication_use'] != 1) {
								$this->coupon_duplication_n[$this->coupon_download_seq]++;
							}
						}else{
							if	( !in_array($this->coupon_download_seq, $this->coupon_same_time_y) ) {
								$this->coupon_same_time_y[]		= $this->coupon_download_seq;
							}
						}

						//무통장만 사용가능
						if	($downloads['sale_payment'] == 'b' ) {
							if	( !in_array($this->coupon_download_seq, $this->coupon_sale_payment_b) ) 
								$this->coupon_sale_payment_b[]	= $this->coupon_download_seq;
						}

						//모바일만 사용가능> 모바일기기체크
						if	($downloads['sale_agent'] == 'm') {// && !$this->_is_mobile_agent
							if	( !in_array($this->coupon_download_seq, $this->coupon_sale_agent_m) ) 
								$this->coupon_sale_agent_m[]	= $this->coupon_download_seq;
						}

						$return['sale_seq']			= $downloads['coupon_seq'];
						$return['sale_subject']		= $downloads['coupon_name'];
						$return['sale_txt']			= $downloads['coupon_name'] . ' (';
						if	($downloads['limit_goods_price'] > 0){
							$return['sale_txt']		.= number_format($downloads['limit_goods_price']).'원 이상 구매 시 ';
						}
						if	($downloads['sale_type'] == 'percent'){
							if	($downloads['duplication_use'] == '1'){
								$return['sale_txt']	.= '상품 1개당 '.$downloads['percent_goods_sale'].'% 추가할인';
							}else{
								$return['sale_txt']	.= '상품 1개 '.$downloads['percent_goods_sale'].'% 추가할인';
							}
							if	($downloads['max_percent_goods_sale'] > 0){
								$return['sale_txt']	.= ' 단, 최대 '.number_format($downloads['max_percent_goods_sale']).'원 할인';
							}
						}else{
							if	($downloads['duplication_use'] == '1'){
								$return['sale_txt']	.= '상품 1개당 '.number_format($downloads['won_goods_sale']).'원 추가할인';
							}else{
								$return['sale_txt']	.= '상품 1개 '.number_format($downloads['won_goods_sale']).'원 추가할인';
							}
						}

						$return['sale_txt']	.= ')';
					}
				}
			}
		}

		if	(!$return['sale_txt']){
			$coupon	= goods_coupon_max($this->goods_seq);
			if	($coupon){
				$return['sale_subject']		= $coupon['coupon_name'];
				$return['sale_seq']			= $coupon['coupon_seq'];
				if($coupon['sale_type'] == 'won') {
					$return['sale_txt']		= number_format($coupon['won_goods_sale']).'원 할인';
				}else{
					$return['sale_txt']		= number_format($coupon['percent_goods_sale']).'% 할인'; 
				}
			}
		}

		return $return;
	}

	## 입점 마케팅(지식쇼핑/쇼핑하우) 쿠폰 할인
	public function coupon_marketing_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '쿠폰할인';
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;
		$today = date('Y-m-d',time());
		$coupon	= $this->ci->couponmodel->get_marketing_feed_coupon_max($today,'',$this->goods_seq,$this->category_code,$total_price);
		if	($coupon){
			$return['sale_subject']		= $coupon['coupon_name'];
			$return['sale_seq']			= $coupon['coupon_seq'];
			$return['sale_price'] = (int) $coupon['goods_sale'];
			if($coupon['sale_type'] == 'won') {
				$return['sale_txt']		= number_format($coupon['won_goods_sale']).'원 할인';
			}else{
				$return['sale_txt']		= number_format($coupon['percent_goods_sale']).'% 할인'; 
			}
		}

		return $return;
	}

	## 코드 할인
	public function code_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '할인코드';
		$sessid					= $this->ci->session->userdata('session_id');
		$promotion_seq			= $this->ci->session->userdata('cart_promotioncodeseq_'.$sessid);
		$promotion_code			= $this->ci->session->userdata('cart_promotioncode_'.$sessid);
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;
		if	($promotion_seq && $promotion_code){
			$code	= $this->ci->promotionmodel->get_able_download_saleprice($promotion_seq,$promotion_code, $total_price, $this->sale_price,$this->ea);
			$this->code_seq	= $code['promotion_seq'];
			if($code['promotion_seq'] && ($code['sale_type'] == 'percent' || $code['sale_type'] == 'won' )){
				$return['sale_title']	= $promotion_seq;
				$return['sale_price']	= (int) $code['promotioncode_sale'];
				if	($code['sale_type'] == 'won'){
					$return['sale_txt']		= '['.$promotion_code.'] '.number_format($code['won_goods_sale']).'원 할인';
				}else{
					$return['sale_txt']		= '['.$promotion_code.'] '.number_format($code['percent_goods_sale']).'% 할인';
					if	($code['max_percent_goods_sale'] > 0){
						$return['sale_txt']	.= '(최대 '.number_format($code['max_percent_goods_sale']).'원)';
					}
				}
			}
		}else{
			$code	= $this->ci->promotionmodel->get_able_promotion_max($this->goods_seq, $this->category_code, $this->brand_code);
			if	($code)foreach($code as $cfg){
				if	($cfg['type'] == 'promotion'){
					if	($cfg['sale_type'] == 'percent'){
						$tmp_expect_price	= floor($this->sale_price * ($cfg['percent_goods_sale'] / 100));
					}else{
						$tmp_expect_price	= $cfg['won_goods_sale'];
					}

					if	($expect_price < $tmp_expect_price){
						$expect_price	= $tmp_expect_price;
						$current_cfg	= $cfg;
					}
				}
			}

			if	($current_cfg){
				$return['sale_subject']	= $current_cfg['promotion_name'];
				$return['sale_seq']		= $current_cfg['promotion_seq'];
				$return['sale_txt']		= '['.$current_cfg['promotion_input_serialnumber'].'] ';
				if	($current_cfg['sale_type'] == 'percent'){
					$return['sale_txt']	.= $current_cfg['percent_goods_sale'] . '% 할인';
					if	($current_cfg['max_percent_goods_sale'] > 0){
						$return['sale_txt']	.= '(최대 '.number_format($current_cfg['max_percent_goods_sale']).'원)';
					}
				}else{
					$return['sale_txt']	.= number_format($current_cfg['won_goods_sale']) . '원 할인';
				}
			}
		}

		return $return;
	}

	## 유입경로 할인
	public function referer_sale(){
		$return['sale_price']	= 0;
		$return['sale_title']	= '유입경로';

		$shop_referer = $this->ci->session->userdata('shopReferer');

		// 입점마케팅 전달 데이터 할인 유입경로 적용 leewh 2015-02-03
		if ($this->marketing_sale['referer']=='Y' && $this->marketing_sale['referer_url']) {
			$shop_referer = $this->marketing_sale['referer_url'];
		}

		if	($shop_referer){

			$referer	= $this->ci->referermodel->sales_referersale($shop_referer, $this->goods_seq, $this->sale_price, $this->ea);
			$this->refererSales		= $referer;
			$this->referer_seq		= $referer['referer_seq'];
			$return['sale_seq']		= $referer['referer_seq'];
			if	($referer){
				$return['sale_subject']	= $referer['referersale_name'];
				$return['sale_price']	= $referer['sales_price'];
				$return['sale_txt']		= (substr($referer['referersale_url'],-1) == '/')?'['.substr($referer['referersale_url'],0,strlen($referer['referersale_url'])-1).'] ':'['.$referer['referersale_url'].'] ';//'['.$referer['referersale_url'].'] '
				if	($referer['sale_type'] == 'won'){
					$return['sale_txt']	.= ' '.number_format($referer['won_goods_sale']).'원 추가할인';
				}else{
					$return['sale_txt']	.= ' '.$referer['percent_goods_sale'].'% 추가할인';
					if	($referer['max_percent_goods_sale'] > 0){
						$return['sale_txt']	.= ' (최대 '.number_format($referer['max_percent_goods_sale']).'원)';
					}
				}
			}
		}else{
			$referer	= $this->ci->referermodel->get_goods_referersale($this->goods_seq, $this->category_code);
			if	($referer)foreach($referer as $cfg){
				if	($cfg['sale_type'] == 'percent'){
					$tmp_expect_price	= floor($this->sale_price * ($cfg['percent_goods_sale'] / 100));
				}else{
					$tmp_expect_price	= $cfg['won_goods_sale'];
				}

				if	($expect_price < $tmp_expect_price){
					$expect_price	= $tmp_expect_price;
					$current_cfg	= $cfg;
				}
			}

			if	($current_cfg){
				$return['sale_subject']	= $current_cfg['referersale_name'];
				$return['sale_seq']		= $current_cfg['referer_seq'];				
				$return['sale_txt']		= (substr($current_cfg['referersale_url'],-1) == '/')?'['.substr($current_cfg['referersale_url'],0,strlen($current_cfg['referersale_url'])-1).'] ':'['.$current_cfg['referersale_url'].'] ';//'['.$current_cfg['referersale_url'].'] ';
				if	($current_cfg['sale_type'] == 'percent'){
					$return['sale_txt']	.= $current_cfg['percent_goods_sale'].'% 추가할인';
					if	($current_cfg['max_percent_goods_sale'] > 0){
						$return['sale_txt']	.= ' (최대 '.number_format($current_cfg['max_percent_goods_sale']).'원)';
					}
				}else{
					$return['sale_txt']		.= number_format($current_cfg['won_goods_sale']).'원 추가할인';
				}
			}
		}

		return $return;
	}

	##↑↑↑↑↑↑↑↑↑↑	1:1 할인 계산 함수들		↑↑↑↑↑↑↑↑↑↑##
	##↓↓↓↓↓↓↓↓↓↓	1:N 할인 계산 함수들 ( Query는 최대한 날리지 않는다. )	↓↓↓↓↓↓↓↓↓↓##

	## 기본할인
	public function list_basic_sale(){
		return $this->basic_sale();
	}

	## 이벤트 할인
	public function list_event_sale(){

		$return['sale_price']	= 0;
		$return['sale_title']	= '이벤트';
		$price					= $this->price;
		$consumer_price			= $this->consumer_price;
		if	(!$consumer_price)	$consumer_price	= $price;

		// 이벤트 조회
		$eventList				= $this->eventSales;
		if	($eventList){
			foreach($eventList as $k => $evt){
				if	($evt['goods'])					$gSeqArr	= explode(',', $evt['goods']);
				if	($evt['category'])				$gCateArr	= explode(',', $evt['category']);
				if	($evt['exception_goods'])		$geSeqArr	= explode(',', $evt['exception_goods']);
				if	($evt['exception_category'])	$geCateArr	= explode(',', $evt['exception_category']);

				// 카테고리 선정
				if	($evt['goods_rule'] == 'category'){
					$set_sale	= false;
					if	($this->category_code)foreach($this->category_code as $c => $code){
						if	($gCateArr && in_array($code, $gCateArr)){
							$set_sale	= true;
						}
						if	($geCateArr && in_array($code, $geCateArr)){
							$set_sale	= false;
							break;
						}
					}
					if	(in_array($this->goods_seq, $geSeqArr))	$set_sale	= false;

				// 상품으로 선정일때
				}elseif	($evt['goods_rule'] == 'goods_view'){
					$set_sale	= false;
					if	(in_array($this->goods_seq, $gSeqArr))	$set_sale	= true;

				// 전체 상품일 때
				}else{
					$set_sale	= true;
					if	($evt['goods_kind'] == 'coupon' && $goods_kind == 'goods')	$set_sale	= false;
					if	($evt['goods_kind'] == 'goods' && $goods_kind == 'coupon')	$set_sale	= false;
					if	($set_sale){
						if	($this->category_code)foreach($this->category_code as $c => $code){
							if	($geCateArr && in_array($code, $geCateArr)){
								$set_sale	= false;
								break;
							}
						}
					}
					if	($set_sale){
						if	(in_array($this->goods_seq, $geSeqArr))	$set_sale	= false;
					}
				}

				if	($set_sale){
					$nTime	= date('H');
					if	($evt['sTime'] <= $nTime && $evt['eTime'] >= $nTime && !$solos[$nTime]){
						// 단독이벤트 우선 처리
						if	($evt['event_type'] == 'solo')	$solos[$nTime]	= true;

						$cprice		= $price;
						if	($evt['target_sale'] == 1)	$cprice		= $consumer_price;
						$nprice		= floor($cprice * ($evt['event_sale'] / 100));

						if	($nprice > $sale_price || !$sale_price){
							$event			= $evt;
							$sale_price		= $nprice;
							// 단독이벤트 시간 표시
							if	($evt['event_type'] == 'solo'){
								$solo_start		= $evt['solo_start'];
								$solo_end		= $evt['solo_end'];
							}
						}
					}
				}
			}

			// 단독이벤트 기간에만 판매하는 상품일때 품절 표시를 위해 필요
			if	(!$solos && $this->goods['socialcp_event'] == 1 && !define('__ADMIN__')){
				$event['event_goodsStatus']		= true;	// "unsold";
			}
		}

		if	($event){
			$this->cfgs['event']	= $event;
			$return['sale_seq']		= $event['event_seq'];
			$return['sale_subject']	= $event['title'];
			$return['target_sale']	= $event['target_sale'];
			$return['sale_price']	= $sale_price;

			// 할인 설명 문구
			if	($event['event_sale'] > 0)		$sale_txt	= $event['event_sale'].'%추가할인 ';
												$sale_mtxt	= $sale_txt;
			if	($event['event_reserve'] > 0)	$sale_txt	.= $event['event_reserve'].'%추가적립 ';
			if	($event['event_point'] > 0)		$sale_txt	.= $event['event_point'].'%추가포인트 ';
			if	($event['app_week_title']){
				$return['sale_txt']		= $sale_txt . $event['app_week_title'] . ' '
										. $event['sTime'] . '시 ~ '
										. $event['eTime'] . '시 '
										. '(~' . date('Y-m-d', strtotime($event['end_date'])) . '까지)';
				$return['sale_mtxt']	= $sale_mtxt.'(~' . date('Y-m-d', strtotime($event['end_date'])) . '까지)';
			}else{
				$return['sale_txt']		= $sale_txt.'(~'.date('Y-m-d', strtotime($event['end_date'])).'까지)';
				$return['sale_mtxt']	= $sale_mtxt.'(~'.date('Y-m-d', strtotime($event['end_date'])).'까지)';
			}

			// 이벤트 판매수량 추가
			$return['event_order_ea'] = $event['event_order_ea'];

			if	($event['event_type'] == 'solo'){
				if	($event['app_end_time']){
					$eventEnd['year']	= date("Y");
					$eventEnd['month']	= date("m");
					$eventEnd['day']	= date("d");
					$eventEnd['hour']	= substr($event['app_end_time'], 0, 2);
					$eventEnd['min']	= substr($event['app_end_time'], -2);
					$eventEnd['second']	= '00';
				}else{
					$eventEndDateTime	= explode(" ", $event['end_date']);
					$eventEndDate		= explode("-", $eventEndDateTime[0]);
					$eventEnd['year']	= $eventEndDate[0];
					$eventEnd['month']	= $eventEndDate[1];
					$eventEnd['day']	= $eventEndDate[2];
					$eventEndTime		= explode(":", $eventEndDateTime[1]);
					$eventEnd['hour']	= $eventEndTime[0];
					$eventEnd['min']	= $eventEndTime[1];
					$eventEnd['second']	= $eventEndTime[2];
				}
				$return['eventEnd']		= $eventEnd;
			}
		}

		return $return;
	}

	## 복수구매 할인
	public function list_multi_sale(){
		return $this->multi_sale();
	}

	## 등급 할인
	public function list_member_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '등급할인';

		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;
		$group_seq				= 0;
		if	($this->group_seq)	$group_seq	= $this->group_seq;
		$groupSales				= $this->groupSales[$this->goods['sale_seq']];
		$benefit				= $groupSales['benefit'][$group_seq];
		$this->cfgs['member']	= $benefit;
		$issuecategory			= $groupSales['category']['sale'];
		$issuegoods				= $groupSales['goods']['sale'];
		if	(!$issuecategory)	$issuecategory	= array();
		if	(!$issuegoods)		$issuegoods		= array();

		// 제외 카테고리 체크
		$sale_possible		= true;
		if	( $this->category_code && is_array($groupSales['category']['sale']) ){
			foreach($this->category_code as $category_code){
				if	(in_array($category_code, $issuecategory)){
					$sale_possible		= false;
					break;
				}
			}
		}
		// 제외 상품 체크
		if	($sale_possible && in_array($this->goods_seq, $issuegoods))	$sale_possible	= false;

		if	($sale_possible){
			$type_fld	= 'sale_price_type';
			$price_fld	= 'sale_price';
			if($this->option_type == 'suboption'){
				$type_fld	= 'sale_option_price_type';
				$price_fld	= 'sale_option_price';
			}

			if( $benefit[$type_fld] == 'PER' && $benefit[$price_fld] && $this->sale_price ){
				$return['sale_price']	= $this->sale_price * ($benefit[$price_fld]/100);
			}else if( $benefit[$type_fld] == 'WON' && $benefit[$price_fld] && $this->sale_price ){
				$return['sale_price']	= $benefit[$price_fld];
			}

			if($benefit['sale_use'] == 'Y' && $total_price){
				if( $benefit['sale_limit_price'] > $total_price ){
					$return['sale_price'] = 0;
				}
			}
		}

		if	(!$group_seq){
			// 비회원의 경우 가장 높은 등급의 혜택을 보여줌.
			$benefitList	= $groupSales['benefit'];
			if	($benefitList)foreach($benefitList as $data){
				if	($bfGroupSeq < $data['group_seq']){
					$bfGroupSeq	= $data['group_seq'];
					$benefit	= $data;
				}
			}
			$return['sale_txt']	= '최대 ';
		}else{
			$return['sale_txt']	= $benefit['group_name'] . ' ';
		}

		if	($benefit){
			$return['sale_seq']		= $benefit['sale_seq'];

			// 할인 정보
			$return['sale_mtxt']		= $return['sale_txt'];
			if	($benefit['sale_price_type'] == 'WON'){
				$return['sale_txt']		.= $benefit['sale_price'].'원추가할인 ';
				$return['sale_mtxt']	.= $benefit['sale_price'].'원추가할인 ';
			}else{
				$return['sale_txt']		.= $benefit['sale_price'].'%추가할인 ';
				$return['sale_mtxt']	.= $benefit['sale_price'].'%추가할인 ';
			}

			// 적립 정보
			if	($benefit['reserve_price_type'] == 'WON')
				$return['sale_txt']		.= $benefit['reserve_price'].'원추가적립 ';
			else
				$return['sale_txt']		.= $benefit['reserve_price'].'%추가적립 ';

			// 포인트 정보
			if	($benefit['point_price_type'] == 'WON')
				$return['sale_txt']		.= $benefit['point_price'].'원추가포인트 ';
			else
				$return['sale_txt']		.= $benefit['point_price'].'%추가포인트 ';
		}

		return $return;
	}

	## 모바일 할인
	public function list_mobile_sale(){
		return $this->mobile_sale();
	}

	## 좋아요 할인
	public function list_like_sale(){
		$return['sale_price']	= 0;
		$return['sale_subject']	= '';
		$return['sale_title']	= '좋아요';

		$likeSales			= $this->likeSales;
		$likeGoods			= $likeSales['goods'];
		if	(!$likeGoods)	$likeGoods	= array();
		$fblike_cfg_list	= $likeSales['config'];
		$saleStatus			= false;
		if	(in_array($this->goods_seq, $likeGoods))	$saleStatus	= true;

		// 할인 여부 및 할인금액 계산
		if	($fblike_cfg_list){
			$tmp_sum_sale_price	= $this->sale_price;
			if	($this->ea > 1)	$tmp_sum_sale_price	= $this->sale_price * $this->ea;
			foreach($fblike_cfg_list as $fblike => $cfg) {
				$f++;
				$basic_cfg	= $cfg;
				if	($saleStatus && $cfg['price1'] <= $tmp_sum_sale_price && $cfg['price2'] >= $tmp_sum_sale_price){
					// 가장 높은 할인을 반영
					if	($cfg['sale_price'] > $current_cfg['sale_price']){
						$current_cfg	= $cfg;
					}
				}

				if	($f > 1 && $best_cfg['sale_price'] < $cfg['sale_price']){
					$best_cfg	= $cfg;
				}
			}
			if	($current_cfg)	$this->cfgs['like']		= $current_cfg;

			// 해당 할인에 대한 정보로 할인 및 노출
			$return['sale_txt']		= '';
			if		($current_cfg){
				$return['sale_price']	= $this->sale_price * ($current_cfg['sale_price'] / 100);
			}
			// 가장 높은 할인에 대한 정보로 노출
			if		($best_cfg){
				$current_cfg				= $best_cfg;
				$return['sale_txt']		= '최대 ';
			// 기본 할인 정보로 노출
			}else{
				$current_cfg				= $basic_cfg;
			}

			// 할인혜택 정보
			if		($current_cfg){
				$return['sale_seq']		= $current_cfg['seq'];
				$return['sale_txt']		.= $current_cfg['sale_price'].'% 추가할인 ';
				if	($current_cfg['sale_emoney'] > 0)
					$return['sale_txt']		.= $current_cfg['sale_emoney'].'% 추가적립 ';
				if	($current_cfg['sale_point'] > 0)
					$return['sale_txt']		.= $current_cfg['sale_point'].'% 추가포인트 ';
			}
		}

		return $return;
	}

	## 쿠폰 할인 ( 회원전용임 )
	public function list_coupon_sale(){

		$return['sale_price']	= 0;
		$return['sale_title']	= '쿠폰할인';
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;

		if	($this->member_seq > 0 && $this->coupon_download_seq){
			$downloads	= $this->couponSales[$this->coupon_download_seq];

			if	($downloads){
				$sale_status	= true;

				// 사용제한 확인
				$sale_status	= $this->chk_issues($downloads['issue_type'], $downloads['category'], $downloads['goods']);

				if	( $sale_status && $this->ci->session->userdata('shopReferer') ){
					if	( couponordercheck(&$downloads, $this->goods_seq, $this->sale_price, $this->goods_ea) !== true ) {
						$sale_status	= false;
					}
				}

				if	($sale_status){
					if	( $downloads['limit_goods_price'] <= $total_price ) {//사용제한 원이상인경우만
						if	( $downloads['sale_type'] == 'percent' && $downloads['percent_goods_sale'] && $this->sale_price ){
							$return['sale_price']	= $downloads['percent_goods_sale'] * $this->sale_price / 100;
							
							if	($downloads['max_percent_goods_sale'] < $return['sale_price']){
								$return['sale_price']	= $downloads['max_percent_goods_sale'];
							}
						}elseif	( $downloads['sale_type'] == 'won' && $downloads['won_goods_sale'] && $this->sale_price ){
							$return['sale_price']	= $downloads['won_goods_sale'];
						}

						//상품의 총할인금액보다 쿠폰할인금액이 큰경우 상품할인금액으로 대체
						if	($this->sale_price*$this->ea < $return['sale_price']*$this->ea){
							$return['sale_price']	= $this->sale_price;
						}

						$return['sale_price']		= floor($return['sale_price']);
						if	($downloads['duplication_use'] == 1){
							$return['sale_price']	= (int) $return['sale_price'] * $this->ea;
						}else{
							$return['sale_price']	= (int) $return['sale_price'];
						}
					}

					$this->coupon_use_array[]	= $this->coupon_download_seq;

					// 단독쿠폰체크
					if	($downloads['coupon_same_time'] == 'N' ) {
						if( !in_array($this->coupon_download_seq, $this->coupon_same_time_n) ) {
							$this->coupon_same_time_n[]		= $this->coupon_download_seq;
						}
						if	( $downloads['duplication_use'] != 1) {
							$this->coupon_duplication_n[$this->coupon_download_seq]++;
						}
					}else{
						if( !in_array($this->coupon_download_seq, $this->coupon_same_time_y) ) {
							$this->coupon_same_time_y[]		= $this->coupon_download_seq;
						}
					}

					//무통장만 사용가능
					if	($downloads['sale_payment'] == 'b' ) {
						if( !in_array($this->coupon_download_seq, $this->coupon_sale_payment_b) ) 
							$this->coupon_sale_payment_b[]	= $this->coupon_download_seq;
					}

					//모바일만 사용가능> 모바일기기체크
					if	($downloads['sale_agent'] == 'm') {// && !$this->_is_mobile_agent
						if( !in_array($this->coupon_download_seq, $this->coupon_sale_agent_m) ) 
							$this->coupon_sale_agent_m[]	= $this->coupon_download_seq;
					}

					$return['sale_seq']			= $downloads['coupon_seq'];
					$return['sale_subject']		= $downloads['coupon_name'];
					$return['sale_txt']			= $downloads['coupon_name'] . ' (';
					if	($downloads['limit_goods_price'] > 0){
						$return['sale_txt']		.= number_format($downloads['limit_goods_price']).'원 이상 구매 시 ';
					}
					if	($downloads['sale_type'] == 'percent'){
						if	($downloads['duplication_use'] == '1'){
							$return['sale_txt']	.= '상품 1개당 '.$downloads['percent_goods_sale'].'% 추가할인';
						}else{
							$return['sale_txt']	.= '상품 1개 '.$downloads['percent_goods_sale'].'% 추가할인';
						}
						if	($downloads['max_percent_goods_sale'] > 0){
							$return['sale_txt']	.= ' 단, 최대 '.number_format($downloads['max_percent_goods_sale']).'원 할인';
						}
					}else{
						if	($downloads['duplication_use'] == '1'){
							$return['sale_txt']	.= '상품 1개당 '.number_format($downloads['won_goods_sale']).'원 추가할인';
						}else{
							$return['sale_txt']	.= '상품 1개 '.number_format($downloads['won_goods_sale']).'원 추가할인';
						}
					}

					$return['sale_txt']	.= ')';
				}
			}
		}

		return $return;
	}

	## 코드 할인
	public function list_code_sale(){

		$return['sale_price']	= 0;
		$return['sale_title']	= '할인코드';
		$sessid					= $this->ci->session->userdata('session_id');
		$promotion_seq			= $this->ci->session->userdata('cart_promotioncodeseq_'.$sessid);
		$promotion_code			= $this->ci->session->userdata('cart_promotioncode_'.$sessid);
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;
		if	($promotion_seq && $promotion_code){
			$code			= $this->codeSales;
			$sale_status	= true;

			// 사용제한 확인
			$sale_status	= $this->chk_issues($code['issue_type'], $code['category'], $code['goods'], $code['brand']);
			if	($sale_status){
				if( $code['limit_goods_price'] <= $total_price ) {
					if( $code['sale_type'] == 'percent' && $code['percent_goods_sale'] && $this->sale_price ){
						$return['sale_price']		= $code['percent_goods_sale'] * $this->sale_price / 100;
						if($code['max_percent_goods_sale'] < $return['sale_price']){
							$return['sale_price']	= $code['max_percent_goods_sale'];
						}
					}elseif( $code['sale_type'] == 'won' && $code['won_goods_sale'] && $this->sale_price ){
						$return['sale_price']		= $code['won_goods_sale'];
					}
				}

				//상품의 총할인금액보다 코드할인금액이 큰경우 상품금액대체
				if	( $this->sale_price < $return['sale_price']){
					$return['sale_price']			= $this->sale_price;
				}

				$return['sale_price']				= floor($return['sale_price']);
				if	( strstr($code['type'], 'promotion') && in_array($code['sale_type'], array('percent', 'won')) && $code['duplication_use'] == 1) {
					$return['sale_price']			= $return['sale_price'] * $this->ea;
				}

				$this->code_seq	= $code['promotion_seq'];
				if($code['promotion_seq'] && ($code['sale_type'] == 'percent' || $code['sale_type'] == 'won' )){
					$return['sale_seq']			= $code['promotion_seq'];
					$return['sale_subject']		= $code['promotion_name'];

					if	($code['sale_type'] == 'won'){
						$return['sale_txt']		= '['.$promotion_code.'] '.number_format($code['won_goods_sale']).'원 할인';
					}else{
						$return['sale_txt']		= '['.$promotion_code.'] '.number_format($code['percent_goods_sale']).'% 할인';
						if	($code['max_percent_goods_sale'] > 0){
							$return['sale_txt']	.= '(최대 '.number_format($code['max_percent_goods_sale']).'원)';
						}
					}
				}
			}
		}

		return $return;
	}

	## 유입경로 할인
	public function list_referer_sale(){
		$return['sale_price']	= 0;
		$return['sale_title']	= '유입경로';

		if	( $this->ci->session->userdata('shopReferer') ){

			$refererlist	= $this->refererSales;
			if	($refererlist)foreach($refererlist as $ref){
				$sale_status	= false;

				// 사용제한 확인
				$sale_status	= $this->chk_issues($ref['issue_type'], $ref['category'], $ref['goods']);
				if	($sale_status){
					// 개당 할인 금액 계산 ( 고객에게 유리한 할인을 적용하기 위한 할인금액 비교 )
					if		($ref['sale_type'] == 'percent' && $ref['percent_goods_sale']){
						$sale_price			= $this->sale_price * ($ref['percent_goods_sale'] / 100);
						if	($sale_price > $ref['max_percent_goods_sale'])
							$sale_price		= $ref['max_percent_goods_sale'];
					}elseif	($ref['sale_type'] == 'won' && $ref['won_goods_sale']){
						$sale_price			= $ref['won_goods_sale'];
					}

					if	($return['sale_price'] < $sale_price){
						$current_cfg			= $ref;
						$return['sale_price']	= floor($sale_price);
					}
				}
			}

			if	($current_cfg){
					$return['sale_seq']		= $current_cfg['referer_seq'];
					$this->referer_seq		= $current_cfg['referer_seq'];
					$return['sale_price']	= $return['sale_price'] * $this->ea;
					$return['sale_subject']	= $current_cfg['referersale_name'];
					$return['sale_txt']		= $current_cfg['referersale_url'];
				if	($current_cfg['sale_type'] == 'won'){
					$return['sale_txt']	.= ' '.number_format($current_cfg['won_goods_sale']).'원 추가할인';
				}else{
					$return['sale_txt']	.= ' '.$current_cfg['percent_goods_sale'].'% 추가할인';
					if	($current_cfg['max_percent_goods_sale'] > 0){
						$return['sale_txt']	.= ' (최대 '.number_format($current_cfg['max_percent_goods_sale']).'원)';
					}
				}
			}
		}

		return $return;
	}


	##↑↑↑↑↑↑↑↑↑↑	1:N 할인 계산 함수들		↑↑↑↑↑↑↑↑↑↑##
	##↓↓↓↓↓↓↓↓↓↓	현재 목록에서 적용될 수 있는 할인들을 배열화		↓↓↓↓↓↓↓↓↓↓##

	## 기본 할인
	public function set_basic_sale(){
		//-----> interface adjust function -----//
		//----- nothing done -----//
		//----- but never delete this function <-----//
	}

	## 이벤트 할인
	public function set_event_sale(){

		$app_week_arr		= array("1"	=> "월요일", "2"	=> "화요일", "3"	=> "수요일",
									"4"	=> "목요일", "5"	=> "금요일", "6"	=> "토요일", "7"	=> "일요일");
		$eventSales			= array();
		$result				= $this->ci->eventmodel->get_today_event();
		if	($result)foreach($result as $k => $event){

			unset($data);
			$data			= $event;

			// 2. 이벤트 시간
			$start_time		= strtotime($event['start_date']);
			$end_time		= strtotime($event['end_date']);
			if	($event['app_start_time']){
				if	(date('Ymd', $start_time) == date('Ymd')){
					if	(substr($event['app_start_time'], 0, 2) >= date('H', $start_time)){
						$data['sTime']		= substr($event['app_start_time'], 0, 2);
					}else{
						$data['sTime']		= date('H', $start_time);
					}
				}else{
						$data['sTime']		= substr($event['app_start_time'], 0, 2);
				}
			}else{
				if	(date('Ymd', $start_time) == date('Ymd'))
					$data['sTime']			= date('H', $start_time);
				else
					$data['sTime']			= '00';
			}
			if	($event['app_end_time']){
				if	(date('Ymd', $end_time) == date('Ymd')){
					if	(substr($event['app_end_time'], 0, 2) <= date('H', $end_time))
						$data['eTime']		= substr($event['app_end_time'], 0, 2);
					else
						$data['eTime']		= date('H', $end_time);
				}else{
						$data['eTime']		= substr($event['app_end_time'], 0, 2);
				}
			}else{
				if	(date('Ymd', $end_time) == date('Ymd'))
					$data['eTime']			= date('H', $end_time);
				else
					$data['eTime']			= '23';
			}

			if	($event['event_type'] == 'solo'){
				$data['solo_start']			= date('Y-m-d', $start_time).' '.$data['sTime'];
				$data['solo_end']			= date('Y-m-d', $end_time).' '.$data['eTime'];
			}

			if($event['daily_event'] && $event['app_week']){
				for($i = 0; $i < strlen($event['app_week']); $i++){
					$app_week	= substr($event['app_week'],$i,1);
					if($app_week_arr[$app_week])	$app_week_title[]	= $app_week_arr[$app_week];
				}
				$data['app_week_title'] = implode(', ',$app_week_title);
			}

			// 3. 상품 선택 기준
			$data['goods_kind']				= $event['apply_goods_kind'];
			$data['goods_rule']				= $event['goods_rule'];

			// 혜택정보
			$benefit		= $this->ci->eventmodel->get_event_benefit($event['event_seq']);
			if	($benefit)foreach($benefit as $b => $bnf){
				$goods						= '';
				$category					= '';
				$except_goods				= '';
				$except_category			= '';

				// 상품/카테고리
				$choice		= $this->ci->eventmodel->get_event_choice($event['event_seq'], $bnf['event_benefits_seq']);
				if	($choice)foreach($choice as $i => $chc){
					if			($chc['choice_type'] == 'goods'){
						if		($goods)			$goods				.= ','.$chc['goods_seq'];
						else						$goods				.= $chc['goods_seq'];
					}elseif		($chc['choice_type'] == 'category'){
						if		($category)			$category			.= ','.$chc['category_code'];
						else						$category			.= $chc['category_code'];
					}elseif		($chc['choice_type'] == 'except_goods'){
						if		($except_goods)		$except_goods		.= ','.$chc['goods_seq'];
						else						$except_goods		.= $chc['goods_seq'];
					}elseif	($chc['choice_type'] == 'except_category'){
						if		($except_category)	$except_category	.= ','.$chc['category_code'];
						else						$except_category	.= $chc['category_code'];
					}
				}

				$data['target_sale']		= $bnf['target_sale'];
				$data['event_sale']			= $bnf['event_sale'];
				$data['event_reserve']		= $bnf['event_reserve'];
				$data['event_point']		= $bnf['event_point'];
				$data['goods']				= $goods;
				$data['category']			= $category;
				$data['exception_goods']	= $except_goods;
				$data['exception_category']	= $except_category;

				$eventSales[]				= $data;
			}
		}

		$this->eventSales	= $eventSales;
	}

	## 복수구매할인 할인
	public function set_multi_sale(){
		//-----> interface adjust function -----//
		//----- nothing done -----//
		//----- but never delete this function <-----//
	}

	## 등급 할인
	public function set_member_sale(){
		$groupsale		= $this->ci->membermodel->get_group_sale_list();
		if	($groupsale)foreach($groupsale as $g => $group){
			unset($data, $goods, $category);
			$data				= $group;
			$groupdetail		= $this->ci->membermodel->get_group_sale_detail($group['sale_seq']);
			if	($groupdetail)foreach($groupdetail as $detail){
				$benefit[$detail['group_seq']]	= $detail;
			}
			$groupcategory		= $this->ci->membermodel->get_group_sale_issuecategory($group['sale_seq']);
			if	($groupcategory)foreach($groupcategory as $cate){
				if		($cate['type'] == 'emoney')	$category['emoney'][]	= $cate['category_code'];
				else								$category['sale'][]		= $cate['category_code'];
			}
			$groupgoods			= $this->ci->membermodel->get_group_sale_issuegoods($group['sale_seq']);
			if	($groupgoods)foreach($groupgoods as $goodsinfo){
				if		($goodsinfo['type'] == 'emoney')	$goods['emoney'][]	= $goodsinfo['goods_seq'];
				else										$goods['sale'][]	= $goodsinfo['goods_seq'];
			}

			$data['benefit']	= $benefit;
			$data['category']	= $category;
			$data['goods']		= $goods;

			$groupSales[$group['sale_seq']]	= $data;
		}

		$this->groupSales	= $groupSales;
	}

	## 모바일 할인
	public function set_mobile_sale(){
		$sc['type']			= 'mobile';
		$mobileSales		= $this->ci->configsalemodel->lists($sc);
		$this->mobileSales	= $mobileSales;
	}

	## 좋아요 할인
	public function set_like_sale(){
		// 좋아요 여부 체크 
		if( $this->ci->session->userdata('fbuser') ) {
			$sns_id	= $this->ci->session->userdata('fbuser');
		}elseif(get_cookie('fbuser')){
			$sns_id	= get_cookie('fbuser');
		} 

		if	($this->member_seq > 0 || $sns_id){
			$sc['whereis']	= " and ( member_seq = '".$this->member_seq."' or sns_id = '".$sns_id."' ) ";
			$fbgoodslist	= $this->ci->goodsfblike->fblike_list_search($sc);
			if	($fbgoodslist['result'])foreach($fbgoodslist['result'] as $fb){
				if	(!in_array($fb['goods_seq'], $fbgoods)) $fbgoods[]	= $fb['goods_seq'];
			}
		}else{//회원또는 페이스북정보가 없을때
			$sc['whereis']	= " and session_id = '".$this->ci->session->userdata('session_id')."' ";
			$fbgoodslist	= $this->ci->goodsfblike->fblike_list_search($sc);
			if	($fbgoodslist['result'])foreach($fbgoodslist['result'] as $fb){
				if	(!in_array($fb['goods_seq'], $fbgoods)) $fbgoods[]	= $fb['goods_seq'];
			}
		}

		if	(count($fbgoods) > 0){
			$sc['type']	= 'fblike';
			$fblike_cfg_list			= $this->ci->configsalemodel->lists($sc);
			$this->likeSales['goods']	= $fbgoods;
			$this->likeSales['config']	= $fblike_cfg_list['result'];
		}
	}

	## 쿠폰 할인 ( 회원전용임 )
	public function set_coupon_sale(){
		if	($this->member_seq > 0){
			$sc['only_cart_goods']	= 'y';
			$sc['member_seq']		= $this->member_seq;
			$sc['use_status']		= 'unused';
			$sc['couponDate']		= array('available');
			$mycoupons				= $this->ci->couponmodel->my_download_list($sc, true);
			if	($mycoupons['result'])foreach($mycoupons['result'] as $k => $coupon){
				unset($data);

				$data				= $coupon;
				$issuecategory		= $this->ci->couponmodel->get_coupon_download_issuecategory($coupon['download_seq']);
				if	($issuecategory)foreach($issuecategory as $cate){
					$category[]		= $cate['category_code'];
				}
				$issuegoods			= $this->ci->couponmodel->get_coupon_download_issuegoods($coupon['download_seq']);
				if	($issuegoods)foreach($issuegoods as $goodsinfo){
					$goods[]		= $goodsinfo['goods_seq'];
				}
				$data['category']	= $category;
				$data['goods']		= $goods;


				$couponSales[$coupon['download_seq']]		= $data;
			}

			$this->couponSales		= $couponSales;
		}
	}

	## 코드 할인
	public function set_code_sale(){
		$sessid			= $this->ci->session->userdata('session_id');
		$promotion_seq	= $this->ci->session->userdata('cart_promotioncodeseq_'.$sessid);
		$promotion_code	= $this->ci->session->userdata('cart_promotioncode_'.$sessid);
		if	($promotion_seq && $promotion_code){
			$promotion				= $this->ci->promotionmodel->get_promotion($promotion_seq);
			$issuegoods				= $this->ci->promotionmodel->get_promotion_issuegoods($promotion_seq);
			if	($issuegoods)foreach($issuegoods as $goodsinfo){
				$goods[]			= $goodsinfo['goods_seq'];
			}
			$issuecategory			= $this->ci->promotionmodel->get_promotion_issuecategory($promotion_seq);
			if	($issuecategory)foreach($issuecategory as $categoryinfo){
				$category[]			= $categoryinfo['category_code'];
			}
			$issuebrand				= $this->ci->promotionmodel->get_promotion_issuebrand($promotion_seq);
			if	($issuebrand)foreach($issuebrand as $brandinfo){
				$brand[]			= $brandinfo['brand_code'];
			}
			$promotion['goods']		= $goods;
			$promotion['category']	= $category;
			$promotion['brand']		= $brand;

			$this->codeSales	= $promotion;
		}
	}

	## 유입경로 할인
	public function set_referer_sale(){
		if	( $this->ci->session->userdata('shopReferer') ){
			$nowreferer	= $this->ci->referermodel->get_referersale_target_list($this->ci->session->userdata('shopReferer'));
			if	($nowreferer)foreach($nowreferer as $referer){
				if	($referer['issue_type'] != 'all'){
					$issuegoods		= $this->ci->referermodel->get_referersale_issuegoods($referer['referersale_seq']);
					if	($issuegoods)foreach($issuegoods as $goodsinfo){
						$goods[]	= $goodsinfo['goods_seq'];
					}
					$issuecategory	= $this->ci->referermodel->get_referersale_issuecategory($referer['referersale_seq']);
					if	($issuecategory)foreach($issuecategory as $categoryinfo){
						$category[]	= $categoryinfo['category_code'];
					}
				}

				## 허용인데 허용 대상 상품, 카테고리가 없는 경우 ( 전체 사용불가와 같음 )
				if	($referer['issue_type'] == 'issue' && !$issuegoods && !$issuecategory) continue;

				$referer['goods']		= $goods;
				$referer['category']	= $category;
				$refererSales[]			= $referer;
			}
			$this->refererSales			= $refererSales;
		}
	}

	##↑↑↑↑↑↑↑↑↑↑	현재 목록에서 적용될 수 있는 할인들을 배열화		↑↑↑↑↑↑↑↑↑↑##
	##↓↓↓↓↓↓↓↓↓↓	적립금 계산				↓↓↓↓↓↓↓↓↓↓##

	## 적립금 계산의 기준이 되는 할인가 추가 계산
	public function price_for_reserve($type, $price, $reserve_unit){
		$total_price			= $this->total_price;
		if	(!$total_price)		$total_price	= $this->sale_price * $this->ea;
		if	($this->reserve_cfg['default_reserve_limit'] == 3 && $this->tot_use_emoney > 0){
			$this_use_emoney	= $this->ci->goodsmodel->get_reserve_standard_pay($this->sale_price, $this->ea, $total_price, $this->tot_use_emoney);
			$price				= $price - $this_use_emoney;
		}

		if	($type == 'PER'){
			$reserve		= (int)($reserve_unit * $price / 100);
		}else{
			if	($this->reserve_cfg['default_reserve_limit'] == 3 && $this->tot_use_emoney > 0){
				$reserve	= (int) (($reserve_unit/$this->sale_price) * $price);
			}else{
				$reserve	= (int) $reserve_unit;
			}
		}

		return $reserve;
	}

	## 이벤트 적립금
	public function event_sale_reserve($result_price){
		$cfg		= $this->cfgs['event'];
		$reserve	= 0;
		if	($cfg){
			$reserve	= $this->price_for_reserve('PER', $result_price, $cfg['event_reserve']);
		}

		return $reserve;
	}

	## 회원 적립금
	public function member_sale_reserve($result_price){
		$cfg		= $this->cfgs['member'];
		$reserve	= 0;
		if	($cfg){
			if	($cfg['point_use'] == "N" || $cfg['point_limit_price'] <= $result_price){
				$reserve	= $this->price_for_reserve($cfg['reserve_price_type'], $result_price, $cfg['reserve_price']);
			}
		}

		return $reserve;
	}

	## 모바일 적립금
	public function mobile_sale_reserve($result_price){
		$cfg		= $this->cfgs['mobile'];
		$reserve	= 0;
		if	($cfg){
			$reserve	= $this->price_for_reserve('PER', $result_price, $cfg['sale_emoney']);
		}

		return $reserve;
	}

	## 좋아요 적립금
	public function like_sale_reserve($result_price){
		$cfg		= $this->cfgs['like'];
		$reserve	= 0;
		if	($cfg){
			$reserve	= $this->price_for_reserve('PER', $result_price, $cfg['sale_emoney']);
		}

		return $reserve;
	}

	##↑↑↑↑↑↑↑↑↑↑	적립금 계산				↑↑↑↑↑↑↑↑↑↑##
	##↓↓↓↓↓↓↓↓↓↓	포인트 계산				↓↓↓↓↓↓↓↓↓↓##

	## 이벤트 적립금
	public function event_sale_point($result_price){
		$cfg		= $this->cfgs['event'];
		$point	= 0;
		if	($cfg){
			$point = (int) ($cfg['event_point'] * $result_price / 100);
		}

		return $point;
	}

	## 회원 포인트
	public function member_sale_point($result_price){
		$cfg		= $this->cfgs['member'];
		$point	= 0;
		if	($cfg){
			if	($cfg['point_use'] == "N" || $cfg['point_limit_price'] <= $result_price){
				if($cfg['point_price_type'] == 'PER'){
					$point	= (int) ($cfg['point_price'] * $result_price / 100);
				}else{
					$point	= (int) $cfg['point_price'];
				}
			}
		}

		return $point;
	}

	## 모바일 포인트
	public function mobile_sale_point($result_price){
		$cfg		= $this->cfgs['mobile'];
		$point	= 0;
		if	($cfg){
			$point = (int) ($cfg['sale_point'] * $result_price / 100);
		}

		return $point;
	}

	## 좋아요 포인트
	public function like_sale_point($result_price){
		$cfg		= $this->cfgs['like'];
		$point	= 0;
		if	($cfg){
			$point	= (int) ($cfg['sale_point'] * $result_price / 100);
		}

		return $point;
	}

	##↑↑↑↑↑↑↑↑↑↑	포인트 계산				↑↑↑↑↑↑↑↑↑↑##
}
