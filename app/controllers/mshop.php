<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class mshop extends front_base {

	function __construct() {
		parent::__construct();

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('minishop');
		if(!$result['type']){
			pageRedirect("/main/index","입점몰+ Lite에서는 미니샵을 이용하실 수 없습니다.");
			exit;
		}

	}

	public function index() {

		$this->load->model('membermodel');
		$this->load->model('goodsmodel');
		$this->load->model('goodsdisplay');
		$this->load->model('providermodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');

		// 로그인 정보
		$ss					= $this->userInfo;
		$ss['return_url']	= $_SERVER['REQUEST_URI'];
		$this->template->assign(array('ss'=>$ss));

		if(!$_GET['code']) $_GET['code'] = $_GET['m'];
		if(!$_GET['m']) $_GET['m'] = $_GET['code'];

		// PARAM 및 입점사 정보
		$m				= isset($_GET['m'])		? $_GET['m'] : 1;
		$sort			= isset($_GET['sort'])	? $_GET['sort'] : '';

		$provider		= $this->providermodel->get_provider($m);
		if	($provider['main_visual'] && !file_exists(ROOTPATH . $provider['main_visual']))
			$provider['main_visual']	= '';
		$this->template->assign(array('pv'=>$provider));

		if($provider['provider_status']!='Y') pageLocation('/main/index','접근권한이 없습니다');

		//
		if	($this->userInfo['member_seq']){
			$chk_this_shop	= $this->membermodel->chk_myminishop($this->userInfo['member_seq'], $m);
			$myshop			= $this->membermodel->get_myminishop($this->userInfo['member_seq']);
			$this->template->assign(array('thisshop'=>$chk_this_shop));
			$this->template->assign(array('my'=>$myshop));
		}

		/* 카테고리 브랜드 정보 */
		$mainCategory	= $this->categorymodel->get_category_data('');

		$perpage = $_GET['perpage'] ? $_GET['perpage'] : $mainCategory['list_count_w'] * $mainCategory['list_count_h'];
		$perpage = $perpage ? $perpage : 10;
		$list_default_sort = $mainCategory['list_default_sort'] ? $mainCategory['list_default_sort'] : 'popular';

		$perpage_min = $mainCategory['list_count_w']*$mainCategory['list_count_h'];
		if($perpage != $mainCategory['list_count_w']*$mainCategory['list_count_h']){
			$mainCategory['list_count_h'] = ceil($perpage/$mainCategory['list_count_w']);
		}

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage;
		$sc['image_size']		= $mainCategory['list_image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : $mainCategory['list_style'];
		$sc['list_goods_status']= $mainCategory['list_goods_status'];

		$sc['provider_seq']		= $m;
		$sc['category_code']	= !empty($_GET['category_code'])		? $_GET['category_code'] : $code;
		$sc['brands']			= !empty($_GET['brands'])		? $_GET['brands'] : array();
		$sc['brand_code']		= !empty($_GET['brand_code'])	? $_GET['brand_code'] : '';
		$sc['search_text']		= !empty($_GET['search_text'])	? $_GET['search_text'] : '';
		$sc['old_search_text']	= !empty($_GET['old_search_text'])	? $_GET['old_search_text'] : '';
		$sc['start_price']		= !empty($_GET['start_price'])	? $_GET['start_price'] : '';
		$sc['end_price']		= !empty($_GET['end_price'])	? $_GET['end_price'] : '';
		$sc['color']			= !empty($_GET['color'])		? $_GET['color'] : '';

		$list	= $this->goodsmodel->goods_list($sc);
		$this->template->assign($list);

		$this->goodsdisplay->set('style',$sc['list_style']);
		$this->goodsdisplay->set('count_w',$mainCategory['list_count_w']);
		$this->goodsdisplay->set('count_h',$mainCategory['list_count_h']);
		$this->goodsdisplay->set('image_decorations',$mainCategory['list_image_decorations']);
		$this->goodsdisplay->set('image_size',$mainCategory['list_image_size']);
		$this->goodsdisplay->set('text_align',$mainCategory['list_text_align']);
		$this->goodsdisplay->set('info_settings',$mainCategory['list_info_settings']);
		$this->goodsdisplay->set('displayTabsList',array($list));
		$this->goodsdisplay->set('displayGoodsList',$list['record']);
		$goodsDisplayHTML = "<div class='designCategoryGoodsDisplay' designElement='categoryGoodsDisplay'>";
		$goodsDisplayHTML .= $this->goodsdisplay->print_(true);
		$goodsDisplayHTML .= "</div>";

		$tmpGET = $_GET;
		unset($tmpGET['sort']);
		unset($tmpGET['page']);
		$sortUrlQuerystring = getLinkFilter('',array_keys($tmpGET));

		$this->template->assign(array(
			'goodsDisplayHTML'		=> $goodsDisplayHTML,
			'sortUrlQuerystring'	=> $sortUrlQuerystring,
			'sort'					=> $sc['sort'],
			'sc'					=> $sc,
			'orders'				=> $this->goodsdisplay->orders,
			'perpage_min'			=> $perpage_min,
			'list_style'			=> $sc['list_style'],
		));

		$this->print_layout($this->template_path());
	}

}