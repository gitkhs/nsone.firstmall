<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class bigdata extends admin_base {
	
	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('bigdatamodel');
		$this->load->model('goodsmodel');
		$this->load->model('usedmodel');
		if	(!$this->config_system)		$this->config_system	= config_load('system');
		
		$chks = $this->usedmodel->used_service_check('bigdata');
		$this->template->assign(array('chkBigdata'=>$chks['type']));

		$this->template->assign(array('kinds' => $this->bigdatamodel->get_kind_array()));
		$this->template->define(array('SEARCH_FORM' => $this->skin."/bigdata/search_form.html"));
	}

	public function index(){
		redirect("/admin/bigdata/catalog");		
	}

	// 빅데이터 추천 설정 페이지
	public function catalog(){

		// 현재 저장된 설정 불러오기
		$kinds				= $this->bigdatamodel->get_kind_array();
		foreach($kinds as $kind => $text){
			$cfg_bigdata[$kind]		= config_load('bigdata_'. $kind);
		}
		$this->template->assign(array('cfg_bigdata'	=> $cfg_bigdata));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 빅데이터 추천 검색 페이지
	public function preview(){

		if	($_GET['no']){
			$_GET['src_type']	= 'seq';
			$_GET['goods_seq']	= $_GET['no'];
		}else{
			if	(!$_GET['src_type'])	$_GET['src_type']	= 'detail';
			if	(!$_GET['src_month'])	$_GET['src_month']	= '3';
			if	(!$_GET['src_kind'])	$_GET['src_kind']	= 'order';
		}

		// 현재 저장된 설정 불러오기
		$kinds				= $this->bigdatamodel->get_kind_array();
		foreach($kinds as $kind => $text){
			$cfg_bigdata[$kind]				= config_load('bigdata_'. $kind);
			$cfg_bigdata[$kind]['ttitle']	= $kinds[$cfg_bigdata[$kind]['tkind']];
			$cfg_bigdata[$kind]['base64']	= base64_encode(serialize($cfg_bigdata[$kind]));
		}
		$this->template->assign(array('cfg_bigdata'	=> $cfg_bigdata));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 상품정보 추출 페이지
	public function get_goods(){

		$src_type	= (trim($_POST['src_type']))	? trim($_POST['src_type'])	: 'detail';
		$goods_seq	= trim($_POST['goods_seq']);

		// local db 검색
		if	(is_array($_POST['goodsStatus']) && count($_POST['goodsStatus']) > 0)
			$sc['goods_status']	= $_POST['goodsStatus'];
		if	(is_array($_POST['goodsView']) && count($_POST['goodsView']) > 0)
			$sc['goods_view']	= $_POST['goodsView'];

		if	($src_type == 'detail'){
			$goods_seq	= '';
			$goods_arr1	= $this->bigdatamodel->get_goods_seq($_POST, 0);
			$goods_arr2	= $this->bigdatamodel->chk_goods_seq($goods_arr1, $sc);
			$goods_seq	= $goods_arr2[0];
		}elseif	($src_type == 'seq'){
			if	($goods_seq > 0)		$goods		= $this->goodsmodel->get_goods($goods_seq);
			if	(!$goods['goods_seq'])	$goods_seq	= 0;
		}

		$result['status']			= false;
		$result['goods_seq']		= $goods_seq;
		if	($goods_seq > 0){
			$html					= $this->get_goods_view($goods_seq);
			if	($html){
				$result['status']	= true;
				$result['html']		= $html;
			}
		}

		echo json_encode($result);
	}

	// 상품 상세정보 HTML
	public function get_goods_view($goods_seq){

		$this->tempate_modules();
		$result		= $this->goodsmodel->get_goods_view($goods_seq, true, true);
		if	($result['status'] == 'error'){
			echo $result['msg'];
		}else{
			$goods			= $result['goods'];
			$category		= $result['category'];
			$alerts			= $result['alerts'];
			if	($result['assign'])foreach($result['assign'] as $key => $val){
				$this->template->assign(array($key	=> $val));
			}
			$this->template->assign(array('skin'	=> $this->config_system['skin']));

			$file_path	= $this->template_path();
			$this->template->define(array('tpl'=>$file_path));
			$html		= $this->template->fetch("tpl");

			return $html;
		}
	}

	// 빅데이터 상품 추출
	public function get_bigdata_goods(){

		$goods_seq		= trim($_POST['goods_seq']);
		$skind			= trim($_POST['skind']);
		$base64_params	= trim($_POST['base64_params']);
		if	($base64_params)	$_POST	= unserialize(base64_decode($base64_params));
		$tkind			= trim($_POST['tkind']);
		$smonth			= trim($_POST['smonth']);
		$tmonth			= trim($_POST['tmonth']);
		$list_count_w	= trim($_POST['list_count_w']);
		$except			= trim($_POST['except']);
		$use_except		= trim($_POST['use_except']);
		$same_type		= $_POST['same_type'];
		$same_type		= explode(',', $_POST['same_type']);

		if	($goods_seq > 0){
			$sc['src_month']	= $smonth;
			$sc['goods_seq']	= $goods_seq;
			$sc['src_kind']		= $skind;
			$members			= $this->bigdatamodel->get_member_seq($sc);
			if	(is_array($members) && count($members) > 0){
				unset($sc);
				$sc['src_month']	= $tmonth;
				$sc['src_kind']		= $tkind;
				$sc['members']		= implode(',', $members);
				if	(count($same_type) > 0){
					foreach($same_type as $k => $type){
						if		($type == 'category'){
							$category	= $this->goodsmodel->get_goods_category_default($goods_seq);
						}elseif	($type == 'brand'){
							$brand		= $this->goodsmodel->get_goods_brand_default($goods_seq);
						}elseif	($type == 'location'){
							$location	= $this->goodsmodel->get_goods_location_default($goods_seq);
						}
					}
					$sc['category1']	= $category['category_code'];
					$sc['brands1']		= $brand['category_code'];
					$sc['location1']	= $location['category_code'];
				}

				$goods_arr	= $this->bigdatamodel->get_goods_seq($sc, 0);
			}
		}

		$result['status']		= false;
		$result['kind']			= $skind;
		if	($use_except == 'y' && $except > 0 && count($goods_arr) < $except){
			$result['status']	= false;
		}elseif	(is_array($goods_arr) && count($goods_arr) > 0){
			$html				= $this->get_goods_list($goods_arr, $skind, $list_count_w);
			if	($html){
				$result['status']		= true;
				$result['html']			= $html;
			}
		}

		echo json_encode($result);
	}

	// 상품 상세정보 HTML
	public function get_goods_list($goods_seq, $kind, $count_w = 5){

		$this->tempate_modules();
		$goods_seq[]	= 13;
		$goods_seq[]	= 14;
		$goods_seq[]	= 16;
		$goods_seq[]	= 17;
		$sc['src_seq']	= $goods_seq;
		$list			= $this->goodsmodel->goods_list($sc);

		$this->template->assign(array('kind'	=> $kind));
		$this->template->assign(array('count_w'	=> $count_w));
		$this->template->assign(array('goods'	=> $list['record']));
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$html		= $this->template->fetch("tpl");

		return $html;
	}

}

/* End of file bigdata.php */
/* Location: ./app/controllers/admin/bigdata.php */