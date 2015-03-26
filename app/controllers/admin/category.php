<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class category extends admin_base {

	public function __construct() {
		parent::__construct();
	}

	public function index()
	{
		redirect("/admin/category/catalog");
	}

	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$design_ex = "1. EYE-DESIGN 환경으로 이동하세요.<br/>2. EYE-DESIGN을 ON하세요.<br/>3. 카테고리 영역을 클릭하세요.<br/>4. 카테고리 디자인 스타일(가로형, 세로형)을 선택하세요.<br/>※ HTML편집으로 세부 디자인을 수정할 수도 있습니다.";
		$this->template->assign(array('category_design_ex'=>$design_ex));
		$this->template->define(array('tpl'=>$file_path));
		
		// 웹FTP 템플릿 define
		$this->template->define(array('webftp'=>$this->skin.'/webftp/_webftp.html'));
		$this->template->define(array('mini_webftp'=>$this->skin.'/webftp/_mini_webftp.html'));
		
		
		$this->template->print_("tpl");
	}
	
	public function ifrm_category_info(){
		$this->tempate_modules();
		$this->load->model('categorymodel');
		
		$categoryCode = $_GET['categoryCode'];
		
		$categoryData = $this->categorymodel->get_category_data($categoryCode);
		
		$categoryData['parentcodetext'] = $this->categorymodel->get_category_goods_code($categoryCode,'modify');

		
		$parentCategoryCode = $this->categorymodel->get_category_code($categoryData["parent_id"]);
		$parentCategoryData = $this->categorymodel->get_category_data($parentCategoryCode);
		
	
		/* 회원 그룹 개발시 변경*/
		$groups = "";
		$query = $this->db->query("select group_seq,group_name from fm_member_group");
		foreach($query->result_array() as $row){
			$groups[] = $row;
		}
		/******************/
		
		$this->template->assign(array('groups'=>$groups));
		$this->template->assign(array('parentCategoryData'=>$parentCategoryData));
		$this->template->assign(array('categoryCode'=>$categoryCode));
		$this->template->assign(array('categoryData'=>$categoryData));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
	
	public function ifrm_category_design(){
		$this->tempate_modules();
		$this->load->model('goodsdisplay');
		$this->load->model('categorymodel');
		$categoryCode = $_GET['categoryCode'];
		$page = $_GET['page'];
		$perpage = $_GET['perpage'];
		$recommendData = array();
		$recommendDisplayItem = array();
		$recommend_image_decorations = array();
		
		$categoryData = $this->categorymodel->get_category_data($categoryCode);
		
		/* 추천상품 정보 */
		if($categoryData['recommend_display_seq']){
			
			$recommendData = $this->goodsdisplay->get_display($categoryData['recommend_display_seq']);

			/* 추천상품 디스플레이 상품 목록 */	
			if($recommendData && $recommendData['display_seq']){
				
				$recommendDisplayItem = $this->goodsdisplay->get_display_item($recommendData['display_seq']);
				
				
				/* 이미지 꾸미기 값 파싱 */
				$recommend_image_decorations = $this->goodsdisplay->decode_image_decorations($recommendData['image_decorations']);

			}
		
		}

		/* 샘플 상품 정보 */
		$sampleGoodsInfo = array(
			'goods_seq' => '',
			'goods_name' => '샘플 상품',
			'price' => '19800',
			'consumer_price' => '24800',
			'image_cnt' => 2,
			'image2' => '/admin/skin/default/images/design/img_effect_sample2.gif',
		);
		
		$styles = $this->goodsdisplay->styles;
				
		/* 리스트 이미지 꾸미기 값 파싱 */
		$list_image_decorations = $this->goodsdisplay->decode_image_decorations($categoryData['list_image_decorations']);
		
		/* 카테고리 상품 상태설정값 */
		$categoryData['list_goods_status'] = explode("|",$categoryData['list_goods_status']);
		
		/* 카테고리 sort를 초기 한번만 migration해주기 위한 config 값*/
		$chkmig		= 'N';
		$migsort	= config_load('mig_sort_category');
		if	($migsort[$categoryCode] == 'Y')	$chkmig	= 'Y';

		$this->template->assign(array(
			'chkmig'						=> $chkmig, 
			'page'							=> $page,
			'perpage'						=> $perpage,
			'styles'						=> $styles,
			'auto_orders'					=> $this->goodsdisplay->auto_orders,
			'orders'						=> $this->goodsdisplay->orders,
			'goodsImageSizes'				=> config_load('goodsImageSize'),	
			'categoryData'					=> $categoryData,
			'categoryCode'					=> $categoryCode,
			'list_image_decorations'		=> $list_image_decorations,
			'recommend_image_decorations'	=> $recommend_image_decorations,
			'recommendData'					=> $recommendData,
			'recommendDisplayItem'			=> $recommendDisplayItem,
			'sampleGoodsInfo'				=> $sampleGoodsInfo
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function tree(){

		$db_config = array(
			"servername"=> $this->db->hostname,
			"username"	=> $this->db->username,
			"password"	=> $this->db->password,
			"database"	=> $this->db->database
		);

		require_once(APPPATH."/javascript/plugin/jstree/_tree/_inc/class._database.php");
		require_once(APPPATH."/javascript/plugin/jstree/_tree/_inc/class.tree.php");

		$jstree = new json_tree('fm_category');
		$jstree->jstreedb = new _database($db_config);

		if(isset($_GET["reconstruct"])) {
			$jstree->_reconstruct();
			die();
		}
		if(isset($_GET["analyze"])) {
			echo $jstree->_analyze();
			die();
		}

		if(isset($_POST['operation'])){
			$_REQUEST = $_POST;
		}else if(isset($_GET['operation'])){
			$_REQUEST = $_GET;
		}

		if( isset($_REQUEST['operation']) && strpos($_REQUEST['operation'], "_") !== 0 && method_exists($jstree, $_REQUEST['operation']) ) {
			header("HTTP/1.0 200 OK");
			header('Content-type: application/json; charset=utf-8');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Pragma: no-cache");

			echo $jstree->{$_REQUEST['operation']}($_REQUEST);
			die();
		}
		header("HTTP/1.0 404 Not Found");
	}

	public function view(){
		
		// $category = $_GET['category'];
		$category = $_POST['category'];
		for($i=4;$i<=strlen($category);$i++){
			$arr[] = substr($category,0,$i);
		}
		$inStr = "'".implode("','",$arr)."'";

		$query = "select a.group_seq,b.group_name from fm_category_group a,fm_member_group b where a.group_seq=b.group_seq and  a.category_code='$category'";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$groups[] = $row;
		}
		
		$types = array();
		$query = "select * from fm_category_group where group_seq is null and  category_code='$category' and user_type != ''";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$types[] = $row;
		}

		$query = "select count(*) cnt from (
			select a.* from fm_category_link as a inner join fm_goods as b on a.goods_seq = b.goods_seq
			where a.category_code like '$category%' group by a.goods_seq
		) as a";
		$query = $this->db->query($query);
		$tmp = $query->result_array();
		$cnt = $tmp[0]['cnt'];

		//$query = "select title,category_code,hide,node_type,node_text_normal,node_text_over,node_image_normal,node_image_over from fm_category where category_code in ($inStr)";
		$query = "select * from fm_category where category_code ='{$category}'";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['goodsCnt'] = $cnt;
			$row['types'] = $types;
			if($row['category_code'] == $category && isset($groups)){
				$row['groups'] = $groups;
			}
			$result[] = $row;
		}

		echo json_encode($result);
	}
	
	/* 카테고리에 속한 상품 목록(순서정렬에 사용) */
	public function get_category_goods_list(){
		
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		
		$categoryCode = $_GET['categoryCode'];
		$page = $_GET['page'] ? $_GET['page'] : 1;
		$perpage = $_GET['perpage'] ? (int)$_GET['perpage'] : 10;
		
		$categoryData = $this->categorymodel->get_category_data($categoryCode);
		
		$result = array(
			'totalCnt' => 0,
			'goods' => array()
		);
		
		$sc=array();
		$sc['admin_category']	= true;
		$sc['sort']				= 'popular';
		$sc['page']				= $page;
		$sc['search_text']		= '';
		$sc['category']			= $categoryCode;
		$sc['perpage']			= $perpage;
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['get_goods_stock']	= true;
		$list					= $this->goodsmodel->goods_list($sc);
		$result['goods']		= $list['record'];
		$result['page']			= $page;
		$result['perpage']		= $perpage;
		$result['totalCnt']		= $list['page']['totalcount'];
		$result['paging']		= pagingtagjs($page, $list['page']['page'], $list['page']['totalpage'], 'show_next_sortgoods([:PAGE:])');

		echo json_encode($result);
	}
	
	/* 카테고리페이지 한꺼번에 꾸미기 */
	public function batch_design_setting(){
		$this->admin_menu();
		$this->tempate_modules();
		
		$this->load->model('goodsdisplay');
		$this->load->model('categorymodel');
		$this->load->helper('design');

		$skinConfiguration = skin_configuration($this->designWorkingSkin);
	
		/* 샘플 상품 정보 */
		$sampleGoodsInfo = array(
			'goods_seq' => '',
			'goods_name' => '샘플 상품',
			'price' => '19800',
			'consumer_price' => '24800',
			'image_cnt' => 2,
			'image2' => '/admin/skin/default/images/design/img_effect_sample2.gif',
		);
		
		$styles = $this->goodsdisplay->styles;
		
		$this->template->assign(array(
			'styles'						=> $styles,
			'auto_orders'					=> $this->goodsdisplay->auto_orders,
			'orders'						=> $this->goodsdisplay->orders,
			'goodsImageSizes'				=> config_load('goodsImageSize'),	
			'sampleGoodsInfo'				=> $sampleGoodsInfo,
			'skinConfiguration'				=> $skinConfiguration
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
}

/* End of file category.php */
/* Location: ./app/controllers/admin/category.php */