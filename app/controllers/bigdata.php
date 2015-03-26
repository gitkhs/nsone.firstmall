<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class bigdata extends front_base {
	
	public function __construct() {
		parent::__construct();

		$this->load->model('bigdatamodel');
		$this->load->model('goodsmodel');
	}

	public function index(){
		redirect("/bigdata/catalog");
	}

	## 랜딩 페이지
	public function catalog(){
		$no						= (int) trim($_GET['no']);
		if	(!$no){
			$list	= $this->goodsmodel->goods_list(array());
			if(isset($list['record'][0]))	$no	= $list['record'][0]['goods_seq'];
		}

		$cfg_system	= ($this->config_system) ? $this->config_system : config_load('system');
		$result		= $this->goodsmodel->get_goods_view($no, true, true);
		if	($result['status'] == 'error'){
			switch($result['errType']){
				case 'echo':
					echo $result['msg'];
					exit;
				break;
				case 'back':
					pageBack($result['msg']);
					exit;
				break;
				case 'redirect':
					alert($result['msg']);
					pageRedirect($result['url'],'');
					exit;
				break;
			}
		}else{
			$goods			= $result['goods'];
			$category		= $result['category'];
			$alerts			= $result['alerts'];
			if	($result['assign'])foreach($result['assign'] as $key => $val){
				if	($key == 'goods')	$this->template->assign(array('goodsinfo'	=> $val));
				else					$this->template->assign(array($key			=> $val));
			}
		}

		// 현재 저장된 설정 불러오기
		$kinds				= $this->bigdatamodel->get_kind_array();
		foreach($kinds as $kind => $text){
			$cfg				= config_load('bigdata_'. $kind);
			$cfg['same_type']	= explode(',', $cfg['same_type']);

			// 대상 회원 정보 추출 ( IP )
			unset($sc, $members);
			$sc['src_month']	= $cfg['smonth'];
			$sc['src_kind']		= $kind;
			$sc['goods_seq']	= $no;
			$members			= $this->bigdatamodel->get_member_seq($sc);

			unset($exceptCnt, $category, $brand, $location, $sc, $goodsList, $display);
			if	(is_array($members) && count($members) > 0){
				// 빅데이터 상품 추출
				$limit				= 20;
				if	($cfg['except'] > 0){
					$exceptCnt		= $cfg['except'];
					$limit			= $cfg['except'];
				}
				if	(count($cfg['same_type']) > 0){
					foreach($cfg['same_type'] as $k => $type){
						if		($type == 'category'){
							$category	= $this->goodsmodel->get_goods_category_default($no);
						}elseif	($type == 'brand'){
							$brand		= $this->goodsmodel->get_goods_brand_default($no);
						}elseif	($type == 'location'){
							$location	= $this->goodsmodel->get_goods_location_default($no);
						}
					}
					$sc['category1']		= $category['category_code'];
					$sc['brands1']			= $brand['category_code'];
					$sc['location1']		= $location['category_code'];
				}
				$sc['src_month']	= $cfg['tmonth'];
				$sc['src_kind']		= $cfg['tkind'];
				$sc['members']		= implode(',', $members);
				$goodsList			= array();
				$goodsList			= $this->bigdatamodel->get_goods_seq($sc, $limit);

				// 제한 수량보다 적으면 미노출 처리
				if	( $exceptCnt > 0 && $exceptCnt > count($goodsList) )	$goodsList	= array();
			}

			if	(is_array($goodsList) && count($goodsList) > 0){
				if	($this->mobileMode)	$cfg['list_count_w']	= 3;

				$reKinds[$kind]['cfg']		= $cfg;
				if	($kind == 'review')	$reKinds[$kind]['textStr']	= '이 상품에 ' . $text . ' 고객들이';
				else					$reKinds[$kind]['textStr']	= '이 상품을 ' . $text . ' 고객들이';
				$reKinds[$kind]['textStr']	.= ' 가장 많이 '.$kinds[$cfg['tkind']].' 다른 상품';
				$reKinds[$kind]['display']	= $this->bigdatamodel->get_bigdata_goods_display($cfg['list_count_w'], $goodsList);
			}
		}

		$this->template->assign(array('kinds'	=> $reKinds));

		$file_path	= $this->template_path();
		$this->print_layout($file_path);
	}
}

/* End of file bigdata.php */
/* Location: ./app/controllers/admin/bigdata.php */