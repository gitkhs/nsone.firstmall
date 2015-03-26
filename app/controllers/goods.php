<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/board".EXT);
class goods extends board {

	function __construct() { 
		parent::__construct(); 

		/**
		** @ board start
		**/
		$this->mygdqnatbl				= 'goods_qna';//상품문의
		$this->mygdreviewtbl			= 'goods_review';//상품후기dd

		$this->mygdqna->boardurl->resets	= '/goods/qna_catalog?goods_seq='.$_GET['goods_seq'];
		$this->mygdqna->boardurl->lists		= '/goods/qna_catalog?goods_seq='.$_GET['goods_seq'];
		$this->mygdqna->boardurl->view		= '/goods/qna_view?goods_seq='.$_GET['goods_seq'].'&seq=';
		$this->mygdqna->boardurl->write		= '/goods/qna_write?goods_seq='.$_GET['goods_seq'];
		$this->mygdqna->boardurl->modify	= $this->mygdqna->boardurl->write.'&seq=';
		$this->mygdqna->boardurl->reply		= $this->mygdqna->boardurl->write.'&reply=y&seq=';
		$this->mygdqna->boardurl->goodsview		= '/goods/view?no=';						//상품접근

		$this->mygdreview->boardurl->resets	= '/goods/review_catalog?goods_seq='.$_GET['goods_seq'];
		$this->mygdreview->boardurl->lists		= '/goods/review_catalog?goods_seq='.$_GET['goods_seq'];
		$this->mygdreview->boardurl->view		= '/goods/review_view?goods_seq='.$_GET['goods_seq'].'&seq=';
		$this->mygdreview->boardurl->write		= '/goods/review_write?goods_seq='.$_GET['goods_seq'];
		$this->mygdreview->boardurl->modify	= $this->mygdreview->boardurl->write.'&seq=';
		$this->mygdreview->boardurl->reply		= $this->mygdreview->boardurl->write.'&reply=y&seq=';
		$this->mygdreview->boardurl->goodsview		= '/goods/view?no=';						//상품접근

		/**
		** @ board end
		**/

		$this->load->library('snssocial');
		$this->load->helper('goods');

	}

	public function main_index()
	{
		redirect("/goods/index");
	}

	public function index()
	{
		redirect("/goods/catalog");
	}

	//카테고리별
	public function catalog()
	{
		$this->load->model('goodsdisplay');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');

		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

		if($_GET['designMode'] && !$code){
			$query = $this->db->query("select category_code from fm_category where level>1 order by category_code asc limit 1");
			$tmp = $query->row_array();
			$code = $_GET['code'] = $tmp['category_code'];
		}

		/* 카테고리 정보 */
		$this->categoryData = $categoryData = $this->categorymodel->get_category_data($code);
		if(!$categoryData['category_code']) {
			pageRedirect('/main/index', '카테고리가 올바르지 않습니다.', 'self');
			exit;
		}

		/* 동영상/플래시매직 치환 */
		$categoryData['top_html'] = showdesignEditor($categoryData['top_html']);

		$code = $categoryData['category_code'];

		$childCategoryData = $this->categorymodel->get_list($code,array(
			"hide_in_navigation = '0'",
			"level >= 2"
		));
		if(strlen($code)>4 && !$childCategoryData){
			$childCategoryData = $this->categorymodel->get_list(substr($code,0,strlen($code)-4),array(
				"hide_in_navigation = '0'",
				"level >= 2"
			));
		}


		/* 카테고리에 속한 브랜드 네비게이션 */
		if($categoryData['brand_navigation_use']=='y'){
			$sqlInner = "
				select b.category_code from fm_category_link as a
					inner join fm_brand_link as b on (a.category_code=? and a.goods_seq = b.goods_seq and b.link=1)
					inner join fm_goods as c on (b.goods_seq=c.goods_seq and c.goods_view='look')

			";
			if(!empty($categoryData['list_goods_status'])) $sqlInner .= " where c.goods_status in ('".str_replace('|',"','",$categoryData['list_goods_status'])."')";
			$sql = "
				select * from (
					{$sqlInner}
					group by b.category_code
				) a
				inner join fm_brand as b on a.category_code = b.category_code
			";
			$query = $this->db->query($sql,$code);
			$goodsBrands = $query->result_array();
			$this->template->assign(array('goodsBrands' => $goodsBrands));
		}

		/* 접근제한 */
		$categoryGroup = array();
		for($i=4;$i<=strlen($code);$i+=4){
			$tmpCode = substr($code,0,$i);
			$categoryGroupTmp = $this->categorymodel->get_category_groups($tmpCode);
			if($categoryGroupTmp) $categoryGroup = $categoryGroupTmp;
			//else break;
		}

		if(!$this->managerInfo){
			// 금지
			if($categoryData['catalog_allow']=='none'){
				pageBack("접속할 수 없는 카테고리페이지입니다.");
				exit;
			}
			// 기간제한
			if($categoryData['catalog_allow']=='period' && $categoryData['catalog_allow_sdate'] && $categoryData['catalog_allow_edate']){
				if(date('Y-m-d') < $categoryData['catalog_allow_sdate'] || $categoryData['catalog_allow_edate'] < date('Y-m-d')){
					pageBack("접속할 수 없는 카테고리페이지입니다.");
					exit;
				}
			}
			// 등급제한
			if($categoryGroup){
				if($this->userInfo){
					$this->load->model('membermodel');
					$memberData = $this->membermodel->get_member_data($this->userInfo['member_seq']);

					$groupPms = array();
					$typePms = array();
					foreach($categoryGroup as $data) {
						if($data["group_seq"]) {
							$groupPms[] = $data;
						}
						if($data["user_type"]) {
							$typePms[] = $data;
						}
					}

					$allowGroup = true;
					if(count($groupPms) > 0) {
						$allowGroup = false;
						foreach($groupPms as $data) {
							if($data['group_seq'] == $memberData['group_seq']){
								$allowGroup = true;
								break;
							}
						}
					}

					$allowType = true;
					if(count($typePms) > 0) {
						$allowType = false;
						foreach($typePms as $data) {
							if($data['user_type'] == 'default' && ! $memberData['business_seq']){
								$allowType = true;
								break;
							}
							if($data['user_type'] == 'business' && $memberData['business_seq']){
								$allowType = true;
								break;
							}
						}
					}

					if(!$allowType || !$allowGroup){
						$this->load->helper('javascript');
						pageBack("해당 브랜드에 접근권한이 없습니다.");
					}
				}else{
					$this->load->helper('javascript');
					alert("해당 카테고리에 접근권한이 없습니다");
					$url = "/member/login?return_url=".$_SERVER["REQUEST_URI"];
					pageRedirect($url,'');
					exit;
				}
			}
		}

		/* 쇼핑몰 타이틀 */
		if($this->config_basic['shopCategoryTitleTag'] && $categoryData['title']){
			$title = str_replace("{카테고리명}",$categoryData['title'],$this->config_basic['shopCategoryTitleTag']);
			$this->template->assign(array('shopTitle'=>$title));
		}

		$perpage = $_GET['perpage'] ? $_GET['perpage'] : $categoryData['list_count_w'] * $categoryData['list_count_h'];
		$perpage = $perpage ? $perpage : 10;
		$list_default_sort = $categoryData['list_default_sort'] ? $categoryData['list_default_sort'] : 'popular';

		$perpage_min = $categoryData['list_count_w']*$categoryData['list_count_h'];
		if($perpage != $categoryData['list_count_w']*$categoryData['list_count_h']){
			$categoryData['list_count_h'] = ceil($perpage/$categoryData['list_count_w']);
		}

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		if($categoryData['list_paging_use']=='n'){
			$sc['limit']			= $perpage;
		}else{
			$sc['perpage']			= $perpage;
		}
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : $categoryData['list_style'];
		$sc['list_goods_status']= $categoryData['list_goods_status'];

		$sc['category_code']	= $code;
		$sc['brands']			= !empty($_GET['brands'])		? $_GET['brands'] : array();
		$sc['brand_code']		= !empty($_GET['brand_code'])	? $_GET['brand_code'] : '';
		$sc['search_text']		= !empty($_GET['search_text'])	? $_GET['search_text'] : '';
		$sc['old_search_text']	= !empty($_GET['old_search_text'])	? $_GET['old_search_text'] : '';
		$sc['start_price']		= !empty($_GET['start_price'])	? $_GET['start_price'] : '';
		$sc['end_price']		= !empty($_GET['end_price'])	? $_GET['end_price'] : '';
		$sc['color']			= !empty($_GET['color'])		? $_GET['color'] : '';

		$list	= $this->goodsmodel->goods_list($sc);
		$this->template->assign($list);
		//print_r($list['record']);
		if($categoryData['list_paging_use']=='n'){
			$this->template->assign(array('page'=>array('totalcount'=>count($list['record']))));
		}

		$this->template->assign(array(
			'categoryCode'			=> $code,
			'categoryTitle'			=> $categoryData['title'],
			'categoryData'			=> $categoryData,
			'childCategoryData'		=> $childCategoryData,
		));
		$this->getboardcatalogcode = $code;//상품후기 : 게시판추가시 이용됨

		/**
		 * display
		**/
		$display_key = $this->goodsdisplay->make_display_key();
		$this->goodsdisplay->set('display_key',$display_key);
		$this->goodsdisplay->set('style',$sc['list_style']);
		$this->goodsdisplay->set('count_w',$categoryData['list_count_w']);
		$this->goodsdisplay->set('count_w_lattice_b',$categoryData['list_count_w_lattice_b']);
		$this->goodsdisplay->set('count_h',$categoryData['list_count_h']);
		$this->goodsdisplay->set('image_decorations',$categoryData['list_image_decorations']);
		$this->goodsdisplay->set('image_size',$categoryData['list_image_size']);
		$this->goodsdisplay->set('text_align',$categoryData['list_text_align']);
		$this->goodsdisplay->set('info_settings',$categoryData['list_info_settings']);
		$this->goodsdisplay->set('displayTabsList',array($list));
		$this->goodsdisplay->set('displayGoodsList',$list['record']);
		if( strstr($categoryData['list_info_settings'],"fblike") && ( !$this->__APP_LIKE_TYPE__ || $this->__APP_LIKE_TYPE__ == 'API')) {//라이크포함시
			$goodsDisplayHTML = $this->is_file_facebook_tag;
			define('FACEBOOK_TAG_PRINTED','YES');
			$goodsDisplayHTML .= "<div id='{$display_key}' class='designCategoryGoodsDisplay' designElement='categoryGoodsDisplay'>";
		}else{
			$goodsDisplayHTML = "<div id='{$display_key}' class='designCategoryGoodsDisplay' designElement='categoryGoodsDisplay'>";
		}
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

		if($categoryData["plan"] == "y"){
			$this->print_layout($this->skin.'/goods/_catalog_plan.html');
		}else{
			$this->print_layout($this->template_path());
		}

	}

	public function category(){
		$code = $_GET['code'];
		$this->load->model('categorymodel');
		$result = $this->categorymodel->get_list($code);
		echo json_encode($result);
	}

	public function child_brand(){
		$code = $_GET['code'];
		$this->load->model('brandmodel');
		$result = $this->brandmodel->get_list($code);
		echo json_encode($result);
	}

	public function brand_main() {
		$this->load->model("brandmodel");
		$this->load->model("brandclassificationmodel");
		$data["classification"] = $this->brandclassificationmodel->_select_list();
		$this->template->assign($data);
		$this->print_layout($this->template_path());
	}

	//브랜드별
	public function brand(){
		$this->load->model('goodsdisplay');
		$this->load->model('goodsmodel');
		$this->load->model('brandmodel');

		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

		if($_GET['designMode'] && !$code){
			$query = $this->db->query("select category_code from fm_brand where level>1 order by category_code asc limit 1");
			$tmp = $query->row_array();
			$code = $_GET['code'] = $tmp['category_code'];
		}

		/* 브랜드 정보 */
		$categoryData = $this->brandmodel->get_brand_data($code);
		$childCategoryData = $this->brandmodel->get_list($code,array(
			"hide = '0'",
			"level >= 2"
		));
		if(strlen($code)>4 && !$childCategoryData){
			$childCategoryData = $this->brandmodel->get_list(substr($code,0,strlen($code)-4),array(
				"hide = '0'",
				"level >= 2"
			));
		}

		/* 브랜드에 속한 카테고리 네비게이션 */
		if($categoryData['category_navigation_use']=='y'){
			$sqlInner = "
				select b.category_code from fm_brand_link as a
				inner join fm_category_link as b on (a.category_code=? and a.goods_seq = b.goods_seq and b.link=1)
				inner join fm_goods as c on (b.goods_seq=c.goods_seq and c.goods_view='look')
			";
			if(!empty($categoryData['list_goods_status'])) $sqlInner .= " where c.goods_status in ('".str_replace('|',"','",$categoryData['list_goods_status'])."')";
			$sql = "
				select * from (
					{$sqlInner}
					group by b.category_code
				) a
				inner join fm_category as b on a.category_code = b.category_code
			";
			$query = $this->db->query($sql,$code);
			$goodsCategories = $query->result_array();
			$this->template->assign(array('goodsCategories' => $goodsCategories));
		}

		/* 접근제한 */
		$categoryGroup = array();
		for($i=4;$i<=strlen($code);$i+=4){
			$tmpCode = substr($code,0,$i);
			$categoryGroupTmp = $this->brandmodel->get_brand_groups($tmpCode);
			if($categoryGroupTmp) $categoryGroup = $categoryGroupTmp;
			//else break;
		}

		if(!$this->managerInfo){
			// 금지
			if($categoryData['catalog_allow']=='none'){
				pageBack("접속할 수 없는 브랜드페이지입니다.");
				exit;
			}
			// 기간제한
			if($categoryData['catalog_allow']=='period' && $categoryData['catalog_allow_sdate'] && $categoryData['catalog_allow_edate']){
				if(date('Y-m-d') < $categoryData['catalog_allow_sdate'] || $categoryData['catalog_allow_edate'] < date('Y-m-d')){
					pageBack("접속할 수 없는 브랜드페이지입니다.");
					exit;
				}
			}
			// 등급제한
			if($categoryGroup){
				if($this->userInfo){
					$this->load->model('membermodel');
					$memberData = $this->membermodel->get_member_data($this->userInfo['member_seq']);

					$groupPms = array();
					$typePms = array();
					foreach($categoryGroup as $data) {
						if($data["group_seq"]) {
							$groupPms[] = $data;
						}
						if($data["user_type"]) {
							$typePms[] = $data;
						}
					}

					$allowGroup = true;
					if(count($groupPms) > 0) {
						$allowGroup = false;
						foreach($groupPms as $data) {
							if($data['group_seq'] == $memberData['group_seq']){
								$allowGroup = true;
								break;
							}
						}
					}

					$allowType = true;
					if(count($typePms) > 0) {
						$allowType = false;
						foreach($typePms as $data) {
							if($data['user_type'] == 'default' && ! $memberData['business_seq']){
								$allowType = true;
								break;
							}
							if($data['user_type'] == 'business' && $memberData['business_seq']){
								$allowType = true;
								break;
							}
						}
					}

					if(!$allowType || !$allowGroup){
						$this->load->helper('javascript');
						pageBack("해당 브랜드에 접근권한이 없습니다.");
					}
				}else{
					$this->load->helper('javascript');
					alert("해당 브랜드에 접근권한이 없습니다.");
					$url = "/member/login?return_url=".$_SERVER["REQUEST_URI"];
					pageRedirect($url,'');
					exit;
				}
			}
		}

		/* 쇼핑몰 타이틀 */
		if($this->config_basic['shopCategoryTitleTag'] && $categoryData['title']){
			$title = str_replace("{카테고리명}",$categoryData['title'],$this->config_basic['shopCategoryTitleTag']);
			$this->template->assign(array('shopTitle'=>$title));
		}

		$perpage = $_GET['perpage'] ? $_GET['perpage'] : $categoryData['list_count_w'] * $categoryData['list_count_h'];
		$perpage = $perpage ? $perpage : 10;
		$list_default_sort = $categoryData['list_default_sort'] ? $categoryData['list_default_sort'] : 'popular';

		$perpage_min = $categoryData['list_count_w']*$categoryData['list_count_h'];
		if($perpage != $categoryData['list_count_w']*$categoryData['list_count_h']){
			$categoryData['list_count_h'] = ceil($perpage/$categoryData['list_count_w']);
		}

		/* 동영상/플래시매직 치환 */
		$categoryData['top_html'] = showdesignEditor($categoryData['top_html']);

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		if($categoryData['list_paging_use']=='n'){
			$sc['limit']			= $perpage;
		}else{
			$sc['perpage']			= $perpage;
		}
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : $categoryData['list_style'];
		$sc['list_goods_status']= $categoryData['list_goods_status'];

		$sc['category_code']	= !empty($_GET['category_code'])	? $_GET['category_code'] : '';
		$sc['brand_code']		= $code;
		$sc['search_text']		= !empty($_GET['search_text'])	? $_GET['search_text'] : '';
		$sc['old_search_text']	= !empty($_GET['old_search_text'])	? $_GET['old_search_text'] : '';
		$sc['start_price']		= !empty($_GET['start_price'])	? $_GET['start_price'] : '';
		$sc['end_price']		= !empty($_GET['end_price'])	? $_GET['end_price'] : '';
		$sc['color']			= !empty($_GET['color'])		? $_GET['color'] : '';

		$list	= $this->goodsmodel->goods_list($sc);
		$this->template->assign($list);

		if($categoryData['list_paging_use']=='n'){
			$this->template->assign(array('page'=>array('totalcount'=>count($list['record']))));
		}


		for($i=0; $i<count($list['record']); $i++){
			$eventData = $this->goodsmodel->get_event_price($list['record'][$i]['price'], $list['record'][$i]['goods_seq'], $list['record'][$i]['category_code']);
			$eventEnd="";
			$list['record'][$i]['event_order_cnt'] = $eventData['event_order_cnt'];

			if($eventData['end_date'] && $eventData['event_type'] == "solo" && $eventData['event_sale'] > 0){//할인율점검
				if($eventData['app_end_time']){
					$eventEndDate = explode("-", $eventEndDateTime[0]);
					$eventEnd['year'] = date("Y");
					$eventEnd['month'] = date("m");
					$eventEnd['day'] = date("d");

					$eventEnd['hour'] = substr($eventData['app_end_time'], 0, 2);
					$eventEnd['min'] = substr($eventData['app_end_time'], -2);
					$eventEnd['second'] = "00";

				}else{
					$eventEndDateTime = explode(" ", $eventData['end_date']);

					$eventEndDate = explode("-", $eventEndDateTime[0]);
					$eventEnd['year'] = $eventEndDate[0];
					$eventEnd['month'] = $eventEndDate[1];
					$eventEnd['day'] = $eventEndDate[2];

					$eventEndTime = explode(":", $eventEndDateTime[1]);
					$eventEnd['hour'] = $eventEndTime[0];
					$eventEnd['min'] = $eventEndTime[1];
					$eventEnd['second'] = $eventEndTime[2];
				}
			}

			$list['record'][$i]['eventEnd'] = $eventEnd;
		}

		$this->template->assign(array(
			'categoryCode'			=> $code,
			'categoryTitle'			=> $categoryData['title'],
			'categoryData'			=> $categoryData,
			'childCategoryData'		=> $childCategoryData,
		));
		$this->getboardcatalogcode = $code;//상품후기 : 게시판추가시 이용됨

		/**
		 * display
		**/
		$display_key = $this->goodsdisplay->make_display_key();
		$this->goodsdisplay->set('display_key',$display_key);
		$this->goodsdisplay->set('style',$sc['list_style']);
		$this->goodsdisplay->set('count_w',$categoryData['list_count_w']);
		$this->goodsdisplay->set('count_w_lattice_b',$categoryData['list_count_w_lattice_b']);
		$this->goodsdisplay->set('count_h',$categoryData['list_count_h']);
		$this->goodsdisplay->set('image_decorations',$categoryData['list_image_decorations']);
		$this->goodsdisplay->set('image_size',$categoryData['list_image_size']);
		$this->goodsdisplay->set('text_align',$categoryData['list_text_align']);
		$this->goodsdisplay->set('info_settings',$categoryData['list_info_settings']);
		$this->goodsdisplay->set('displayTabsList',array($list));
		$this->goodsdisplay->set('displayGoodsList',$list['record']);
		if(strstr($categoryData['list_info_settings'],"fblike")  && ( !$this->__APP_LIKE_TYPE__ || $this->__APP_LIKE_TYPE__ == 'API') ) {//라이크포함시
			$goodsDisplayHTML = $this->is_file_facebook_tag;
			define('FACEBOOK_TAG_PRINTED','YES');
			$goodsDisplayHTML .= "<div id='{$display_key}' class='designBrandGoodsDisplay' designElement='brandGoodsDisplay'>";
		}else{
			$goodsDisplayHTML = "<div id='{$display_key}' class='designBrandGoodsDisplay' designElement='brandGoodsDisplay'>";
		}
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


	public function category_list(){
		$this->load->model('categorymodel');

		$category_code = $_GET['code'];
		$result = $this->categorymodel->get_list($category_code,array("hide='0'"));
		$this->template->assign('loop',$result);

		if(!$result){
			header("location:../goods/catalog?code=" . $category_code);
			exit;
		}

		$categorys['category_code'] = $this->categorymodel->split_category($category_code);
		if($categorys['category_code']){
			foreach($categorys['category_code'] as $code){
				$categorys['category'][] = $this->categorymodel->one_category_name($code);

			}
			$this->template->assign('categorys',$categorys);
		}

		$this->print_layout($this->template_path());
	}

	public function option_stock(){
		$this->load->helper('order');
		$cfg = config_load('order');

		$goods_seq = $_GET['no'];

		$option1 = $_GET['option1'];
		$option2 = $_GET['option2'];
		$option3 = $_GET['option3'];
		$option4 = $_GET['option4'];
		$option5 = $_GET['option5'];

		$check_result = check_stock_option($goods_seq,$option1,$option2,$option3,$option4,$option5,0,$cfg,'view_stock');
		echo json_encode($check_result);
	}

	public function suboption_stock(){
		$this->load->helper('order');
		$cfg = config_load('order');

		$goods_seq = $_GET['no'];

		$title = $_GET['title'];
		$option = $_GET['option'];

		$check_result = check_stock_suboption($goods_seq,$title,$option,$ea,$cfg,'view_stock');

		echo json_encode($check_result);
	}

	public function option_join($goods_seq=null){

		if($goods_seq){
			$no = (int) $goods_seq;
			$return = true;
		}else{
			$no = (int) $_GET['no'];
			$return = false;
		}

		$applypage	= 'option';

		$this->load->helper('order');
		$cfg_order = config_load('order');
		$this->load->model('categorymodel');
		$this->load->model('goodsmodel');
		$this->load->model('membermodel');
		$this->load->library('sale');

		$goods			= $this->goodsmodel->get_goods($no);
		$default_option	= $this->goodsmodel->get_goods_default_option($no);

		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['total_price']			= $default_option[0]['price'];
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		// 카테고리정보
		$categorys = $this->goodsmodel->get_goods_category($no);
		$tmparr2 = array();
		foreach($categorys as $key => $val){
			if( $val['link'] == 1 ){
				$category_code = $this->categorymodel->split_category($val['category_code']);
			}
			$tmparr = $this->categorymodel->split_category($val['category_code']);
			foreach($tmparr as $cate) $tmparr2[] = $cate;
		}
		if($tmparr2){
			$tmparr2 = array_values(array_unique($tmparr2));
			$goods['r_category'] = $tmparr2;
		}

		$whereVal[] = $no;
		$where[] = 'o.goods_seq'.'=?';

		$reservation_field = "s.reservation15";
		if($cfg_order['ableStockStep']){
			$reservation_field = "s.reservation".$cfg_order['ableStockStep'];
		}

		$query = "select s.stock,o.price,o.consumer_price,".$reservation_field." as reservation, o.infomation as infomation,option1,option2,option3,option4,option5, color, zipcode, address_type, address, addressdetail, address_street,  newtype, codedate, sdayinput, fdayinput, dayauto_type, sdayauto, fdayauto, dayauto_day, biztel, coupon_input  from fm_goods_option o, fm_goods_supply s where o.option_seq=s.option_seq and ".implode(' and ',$where)." order by o.option_seq asc";

		$query = $this->db->query($query,$whereVal);
		$result = array();
		foreach($query -> result_array() as $data){
			$data['chk_stock'] = true;

			if($cfg_order['runout'] != 'ableStock') $data['reservation'] = 0;
			$data['color'] = trim($data['color']);

			//----> sale library 적용
			unset($param);
			$param['consumer_price']		= $data['consumer_price'];
			$param['price']					= $data['price'];
			$param['total_price']			= $data['price'];
			$param['ea']					= 1;
			$param['category_code']			= $goods['r_category'];
			$param['goods_seq']				= $goods['goods_seq'];
			$param['goods']					= $goods;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);
			$this->sale->reset_init();
			$data['price']	= $sales['result_price'];
			//<---- sale library 적용

			for($i=1;$i<=5;$i++) ${'option'.$i} = $data['option'.$i];
			$data['chk_stock'] = check_stock_option($no,$option1,$option2,$option3,$option4,$option5,0,$cfg_order,'view');

			if( $goods['goods_kind'] == 'coupon' ) {
				// 쿠폰상품 기간체크
				$chkcouponexpire = check_coupon_date_option($no,$option1,$option2,$option3,$option4,$option5);
				if( $chkcouponexpire['couponexpire'] === false ){
					$data['chk_stock'] = 0;//재고품절
					$data['social_start_date'] = $chkcouponexpire['social_start_date'];
					$data['social_end_date'] = $chkcouponexpire['social_end_date'];
				}
			}

			$data['opspecial_location'] = get_goods_options_print_array($data);

			if($data['newtype']) {
				$data['infomation'] = ($data['infomation'])?$data['infomation'].'<br/>'.get_goods_special_option_print($data):get_goods_special_option_print($data);
			}

			if( $this->mobileMode || $this->storemobileMode ) $data['ismobile'] = true;

			$result[] = $data;
		}

		if($return){
			return $result;
		}else{
			echo json_encode($result);
		}
	}

	public function option($goods_seq=null){

		if($goods_seq){
			$no = (int) $goods_seq;
			$return = true;
		}else{
			$no = (int) $_GET['no'];
			$return = false;
		}

		$options	= array();
		$option1	= "";
		$option2	= "";
		$option3	= "";
		$option4	= "";
		$option5	= "";
		$applypage	= 'option';

		$this->load->helper('order');
		$cfg_order = config_load('order');
		$this->load->model('categorymodel');
		$this->load->model('goodsmodel');
		$this->load->model('membermodel');
		$this->load->library('sale');

		$goods			= $this->goodsmodel->get_goods($no);
		$default_option	= $this->goodsmodel->get_goods_default_option($no);
		
		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['total_price']			= $default_option[0]['price'];
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		if(isset($_GET['options'])) $options  = $_GET['options'];
		$max = $_GET['max'];
		if(!$max) $max = "1";

		// 카테고리정보
		$categorys = $this->goodsmodel->get_goods_category($no);
		$tmparr2 = array();
		foreach($categorys as $key => $val){
			if( $val['link'] == 1 ){
				$category_code = $this->categorymodel->split_category($val['category_code']);
			}
			$tmparr = $this->categorymodel->split_category($val['category_code']);
			foreach($tmparr as $cate) $tmparr2[] = $cate;
		}
		if($tmparr2){
			$tmparr2 = array_values(array_unique($tmparr2));
			$goods['r_category'] = $tmparr2;
		}

		$whereVal[] = $no;
		$where[] = 'o.goods_seq'.'=?';
		$where[] = 'o.option_seq=s.option_seq';
		$field = 'o.option1';
		$optionssel = 'detail';
		foreach($options as $key => $option){
			$whereVal[] = $option;
			$optionssel = ($key+1);
			$field = 'o.option'.($key+2);
			$where[] = 'o.option'.($key+1) .'=?';
			${'option'.($key+1)} = $option;
		}

		$reservation_field = "s.reservation15";
		if($cfg_order['ableStockStep']){
			$reservation_field = "s.reservation".$cfg_order['ableStockStep'];
		}

		$query = "select ".$field." as opt,sum(s.stock) as stock,o.price,o.consumer_price,sum(".$reservation_field.") as reservation, ifnull(o.infomation,'') as infomation,option1,option2,option3,option4,option5, color, zipcode, address_type, address, addressdetail, address_street,  newtype, codedate, sdayinput, fdayinput, dayauto_type, sdayauto, fdayauto, dayauto_day, biztel, coupon_input  from fm_goods_option o, fm_goods_supply s where ".implode(' and ',$where)." group by ".$field." order by o.option_seq asc";
		$query = $this->db->query($query,$whereVal);
		$result = array();
		foreach($query -> result_array() as $data){
			$data['chk_stock'] = true;

			if($cfg_order['runout'] != 'ableStock') $data['reservation'] = 0;
			$data['color'] = trim($data['color']);

			//----> sale library 적용
			unset($param);
			$param['option_type']			= 'option';
			$param['consumer_price']		= $data['consumer_price'];
			$param['price']					= $data['price'];
			$param['total_price']			= $data['price'];
			$param['ea']					= 1;
			$param['category_code']			= $goods['r_category'];
			$param['goods_seq']				= $goods['goods_seq'];
			$param['goods']					= $goods;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);
			$this->sale->reset_init();
			$data['price']	= $sales['result_price'];
			//<---- sale library 적용

			if( $field == 'o.option'.$max )	${'option'.$max} = $data['opt'];

			for($i=1;$i<=5;$i++) if($i>$max) unset($data['option'.$i]);
			$data['chk_stock'] = check_stock_option($no,$option1,$option2,$option3,$option4,$option5,0,$cfg_order,'view');

			if( $goods['goods_kind'] == 'coupon' ) {
				// 쿠폰상품 기간체크
				$chkcouponexpire = check_coupon_date_option($no,$option1,$option2,$option3,$option4,$option5, $optionssel, $data);
				if( $chkcouponexpire['couponexpire'] === false ){
					$data['chk_stock'] = 0;//재고품절
					$data['social_start_date'] = $chkcouponexpire['social_start_date'];
					$data['social_end_date'] = $chkcouponexpire['social_end_date'];
				}
			}

			for($i=1;$i<=5;$i++) unset($data['option'.$i]);

			$data['opspecial_location'] = get_goods_options_print_array($data);

			if($data['newtype']) {
				$data['infomation'] = ($data['infomation'])?$data['infomation'].'<br/>'.get_goods_special_option_print($data):get_goods_special_option_print($data);
			}

			if( $this->mobileMode || $this->storemobileMode ) $data['ismobile'] = true;

			$result[] = $data;
		}

		if($return){
			return $result;
		}else{
			echo json_encode($result);
		}
	}

	// 제품 상세 페이지
	public function view(){

		$this->load->model('goodsmodel');
		$no		= (int) $_GET['no'];
		if	(!$no){
			$list	= $this->goodsmodel->goods_list(array());
			if(isset($list['record'][0]))	$no		= $list['record'][0]['goods_seq'];
		}

		$result	= $this->goodsmodel->get_goods_view($no);
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
			$template_dir	= $this->template->template_dir;
			$compile_dir	= $this->template->compile_dir;
			$goods			= $result['goods'];
			$category		= $result['category'];
			$alerts			= $result['alerts'];

			if	($result['assign'])foreach($result['assign'] as $key => $val){
				$this->template->assign(array($key	=> $val));
			}

			// 옵션 분리형
			if($goods['option_view_type']=='divide' && $options){
				$options_n0 = $this->option($goods['goods_seq']);
				$this->template->assign(array('options_n0'	=> $options_n0));
			}

			// 옵션 조합형
			if($goods['option_view_type']=='join' && $options){
				$options_join = $this->option_join($goods['goods_seq']);
				$this->template->assign(array('options_join'	=> $options_join));
			}


			// 네이버 체크아웃
			if( $goods['goods_status'] == 'normal' &&  !$goods['string_price_use'] ){
				$navercheckout = config_load('navercheckout');
				$marketing_admin = $this->session->userdata('marketing');
				if(
						$navercheckout['use'] == 'y'											// "사용모드"일때
					||	($navercheckout['use'] == 'test' && $this->managerInfo)					// "테스트모드"이고 관리자아이디일때
					||	($navercheckout['use'] == 'test' && $marketing_admin=='nbp') // "테스트모드"이고 회원아이디 gabia일때
				){

					// 예외카테고리 체크
					$expectCategoryChk = false;
					if($navercheckout['except_category_code']) $navercategorys    = $this->goodsmodel->get_goods_category($goods['goods_seq']); // 추가 
					foreach($navercheckout['except_category_code'] as $v1){ 
						foreach($navercategorys as $v2){
							if($v1['category_code']==$v2['category_code'] || preg_match("/^".$v1['category_code']."/",$v2['category_code'])){
								$expectCategoryChk = true;
							}
						}
					}

					// 예외상품 체크
					$expectGoodsChk = false;
					foreach($navercheckout['except_goods'] as $v1){
						if($v1['goods_seq']==$goods['goods_seq']){
							$expectGoodsChk = true;
						}
					}

					// 비과세 상품 버튼 비노출
					// if($goods['tax'] == 'exempt') $expectGoodsChk = true;

					// 착불배송 사용여부
					$this->load->helper('shipping');
					$shipping = use_shipping_method();
					$use_postpaid = false;
					if( $shipping[0] ){
						foreach($shipping[0] as $key => $data){
							if($data['code']=='postpaid' && $data['useYn']=='y'){
								$this->template->assign(array('use_postpaid'=>1));
							}
						}
					}

					if(!$expectGoodsChk && !$expectCategoryChk && !($this->fammerceMode || $this->storefammerceMode) ){
						$this->template->template_dir = BASEPATH."../partner";
						$this->template->compile_dir	= BASEPATH."../_compile/";
						$this->template->assign(array('navercheckout'=>$navercheckout));
						$this->template->define(array('navercheckout'=>'navercheckout.html'));
						$navercheckout_tpl = $this->template->fetch('navercheckout');
						$this->template->assign(array('navercheckout_tpl'=>$navercheckout_tpl));
					}
				}
			}

			//네이버 마일리지 사용여부 가져오기
			$this->load->model('navermileagemodel');
			$cfg_naver_mileage = $this->navermileagemodel->naver_mileage_display();
			$this->template->assign('naver_mileage_yn',$cfg_naver_mileage['naver_mileage_yn']);

			$this->template->template_dir = $template_dir;
			$this->template->compile_dir = $compile_dir;
			$this->print_layout($this->template_path());

			//네이버 마일리지 출력용 스크립트
			if(in_array($cfg_naver_mileage['naver_mileage_yn'],array('y','t'))){
				$this->navermileagemodel->create_cooke();
				$this->navermileagemodel->display_view();
			}

			// 가격대체문구 사용여부
			echo "<script>var gl_string_price_use = 0;</script>";
			if( $goods['string_price_use'] ){
				echo "<script>var gl_string_price_use = ".$goods['string_price_use'].";</script>";
			}

			// 관리자 표시용 메시지 출력
			foreach($alerts as $msg){
				alert($msg);
			}
			
			// 통계서버로 통계데이터 전달
			echo "<script>statistics_firstmall('view','".$goods['goods_seq']."','','');</script>";

			/* 고객리마인드서비스 상세유입로그 */
			$this->load->helper('reservation');
			$curation = array("action_kind"=>"goodsview","goods_seq"=>$goods['goods_seq']);
			curation_log($curation);
		}

	}

	/* 모바일용 상세설명 */
	public function view_contents(){
		$no = (int) $_GET['no'];
		$this->load->model('goodsmodel');

		if(!$no){
			$list	= $this->goodsmodel->goods_list(array());
			if(isset($list['record'][0])){
				$no = $list['record'][0]['goods_seq'];
			}
		}

		$goods = $this->goodsmodel->get_goods($no);

		// 모바일 상세 설명 생성
		if( !$goods['mobile_contents'] )
		{
			$goods['mobile_contents'] = $this->goodsmodel->set_mobile_contents($goods['contents'],$goods['goods_seq']);
		}

		$cfg_goods = config_load('goods');
		if($goods['video_use'] == 'Y' ) {
			$video_size = explode("X" , $goods['video_size']);
			$goods['video_size0'] = $video_size[0];
			$goods['video_size1'] = $video_size[1];

			$video_size_mobile = explode("X" , $goods['video_size_mobile']);
			$goods['video_size_mobile0'] = $video_size_mobile[0];
			$goods['video_size_mobile1'] = $video_size_mobile[1];
		}else{
			unset($goods['file_key_w'],$goods['file_key_i'],$goods['video_size']);
			$goods['video_use']	= 'N';
		}
		//동영상리스트
		$this->load->model('videofiles');
		$videosc['tmpcode']= $goods['videotmpcode'];
		$videosc['upkind']= 'goods';
		$videosc['type']= 'contents';
		$videosc['viewer_use']= 'Y';
		$videosc['orderby']= 'sort ';
		$videosc['sort']= 'asc, seq desc ';
		$goodsvideofiles = $this->videofiles->videofiles_list_all($videosc);
		if($goodsvideofiles['result']) foreach($goodsvideofiles['result']as $k => $data){
			//동영상
			if( $this->session->userdata('setMode')=='mobile' && $data['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
				$goodsvideofiles['result'][$k]['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_i']);
				$goodsvideofiles['result'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_i']);
				$goodsvideofiles['result'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_i']);
			}elseif( uccdomain('thumbnail',$data['file_key_w']) && $data['file_key_w'] ) {
				$goodsvideofiles['result'][$k]['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_w']);
				$goodsvideofiles['result'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_w']);
				$goodsvideofiles['result'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_w']);
			}
		}

		if($goodsvideofiles['result']) $this->template->assign('goodsvideofiles',$goodsvideofiles['result']);

		if(!defined('__ISADMIN__')) {
			$this->template->assign('designMode',false);
		}


		/* 동영상/플래시매직 치환 */
		$goods['contents'] = showdesignEditor($goods['contents']);
		$goods['mobile_contents'] = showdesignEditor($goods['mobile_contents']);

		//동영상
		if( $this->session->userdata('setMode')=='mobile' && $goods['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
			$goods['uccdomain_thumbnail']		= uccdomain('thumbnail',$goods['file_key_i']);
			$goods['uccdomain_fileswf']			= uccdomain('fileswf',$goods['file_key_i']);
			$goods['uccdomain_fileurl']			= uccdomain('fileurl',$goods['file_key_i']);
		}elseif( uccdomain('thumbnail',$goods['file_key_w']) && $goods['file_key_w'] ) {
			$goods['uccdomain_thumbnail']		= uccdomain('thumbnail',$goods['file_key_w']);
			$goods['uccdomain_fileswf']			= uccdomain('fileswf',$goods['file_key_w']);
			$goods['uccdomain_fileurl']			= uccdomain('fileurl',$goods['file_key_w']);
		}

		$this->template->assign(array('goods'=>$goods));
		$this->print_layout($this->template_path());
	}

	/* 모바일용 쿠폰상품 위치 */
	public function view_location(){
		$no = (int) $_GET['no'];
		$this->load->model('goodsmodel');

		$goods = $this->goodsmodel->get_goods($no);

		// 쿠폰 위치서비스 사용여부 lwh 2014-04-01
		if($this->mobileMode)	$mapview_use	= $goods['pc_mapview'];
		else					$mapview_use	= $goods['m_mapview'];

		$options = $this->goodsmodel->get_goods_option($no);

		if($options)foreach($options as $k => $opt){

			$opt['opspecial_location'] = get_goods_options_print_array($opt);

			/* 쿠폰 위치서비스 사용시 배열 추가 lwh 2014-04-01 */
			if($mapview_use=='Y'){
				$mapArr[$k]['o_seq']			= $opt['option_seq'];
				$mapArr[$k]['option']			= $opt['option'.$opt['opspecial_location']['address']];
				$mapArr[$k]['address']			= $opt['address']. " " .$opt['addressdetail'];
				$mapArr[$k]['address_street']	= $opt['address_street'];
				$mapArr[$k]['biztel']			= $opt['biztel'];
			}
		}

		if($mapview_use=='Y'){
			$this->template->assign('mapArr', $mapArr);
		}

		$this->print_layout($this->template_path());
	}

	public function view_review(){
		$no = (int) $_GET['no'];
		$this->load->model('goodsmodel');

		if(!$no){
			$list	= $this->goodsmodel->goods_list(array());
			if(isset($list['record'][0])){
				$no = $list['record'][0]['goods_seq'];
			}
		}

		$goods = $this->goodsmodel->get_goods($no);

		// 모바일 상세 설명 생성
		if( !$goods['mobile_contents'] )
		{
			$goods['mobile_contents'] = $this->goodsmodel->set_mobile_contents($goods['contents'],$goods['goods_seq']);
		}

		$this->template->assign(array('goods'=>$goods));
		$this->print_layout($this->template_path());
	}

	public function contents(){
		$no = (int) $_GET['no'];

		$this->load->model('goodsmodel');
		$goods = $this->goodsmodel->get_goods($no);

		// 모바일 상세 설명 생성
		if( !$goods['mobile_contents'] )
		{
			$goods['mobile_contents'] = $this->goodsmodel->set_mobile_contents($goods['contents'],$goods['goods_seq']);
		}

		$cfg_goods = config_load('goods');
		if($goods['video_use'] == 'Y' ) {
			$video_size = explode("X" , $goods['video_size']);
			$goods['video_size0'] = $video_size[0];
			$goods['video_size1'] = $video_size[1];

			$video_size_mobile = explode("X" , $goods['video_size_mobile']);
			$goods['video_size_mobile0'] = $video_size_mobile[0];
			$goods['video_size_mobile1'] = $video_size_mobile[1];
		}else{
			unset($goods['file_key_w'],$goods['file_key_i'],$goods['video_size']);
			$goods['video_use']	= 'N';
		}
		//동영상리스트
		$this->load->model('videofiles');
		$videosc['tmpcode']= $goods['videotmpcode'];
		$videosc['upkind']= 'goods';
		$videosc['type']= 'contents';
		$videosc['viewer_use']= 'Y';
		$videosc['orderby']= 'sort ';
		$videosc['sort']= 'asc, seq desc ';
		$goodsvideofiles = $this->videofiles->videofiles_list_all($videosc);
		if($goodsvideofiles['result']) foreach($goodsvideofiles['result']as $k => $data){
			//동영상
			if( $this->session->userdata('setMode')=='mobile' && $data['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
				$goodsvideofiles['result'][$k]['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_i']);
				$goodsvideofiles['result'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_i']);
				$goodsvideofiles['result'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_i']);
			}elseif( uccdomain('thumbnail',$data['file_key_w']) && $data['file_key_w'] ) {
				$goodsvideofiles['result'][$k]['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_w']);
				$goodsvideofiles['result'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_w']);
				$goodsvideofiles['result'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_w']);
			}
		}

		if($goodsvideofiles['result']) $this->template->assign('goodsvideofiles',$goodsvideofiles['result']);

		$this->template->assign('designMode',false);

		/* 동영상/플래시매직 치환 */
		$goods['contents'] = showdesignEditor($goods['contents']);
		$goods['mobile_contents'] = showdesignEditor($goods['mobile_contents']);

		//동영상
		if( $this->session->userdata('setMode')=='mobile' && $goods['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
			$goods['uccdomain_thumbnail']		= uccdomain('thumbnail',$goods['file_key_i']);
			$goods['uccdomain_fileswf']			= uccdomain('fileswf',$goods['file_key_i']);
			$goods['uccdomain_fileurl']			= uccdomain('fileurl',$goods['file_key_i']);
		}elseif( uccdomain('thumbnail',$goods['file_key_w']) && $goods['file_key_w'] ) {
			$goods['uccdomain_thumbnail']		= uccdomain('thumbnail',$goods['file_key_w']);
			$goods['uccdomain_fileswf']			= uccdomain('fileswf',$goods['file_key_w']);
			$goods['uccdomain_fileurl']			= uccdomain('fileurl',$goods['file_key_w']);
		}

		$this->template->assign(array('goods'=>$goods));
		$this->print_layout($this->template_path());
	}


	public function zoom()
	{
		$no = (int) $_GET['no'];
		$this->load->model('goodsmodel');

		$sessionMember = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));

		$goods = $this->goodsmodel->get_goods($no);
		$goods['title']			= strip_tags($goods['goods_name']);
		$images = $this->goodsmodel->get_goods_image($no);

		$this->template->assign(array('goods'=>$goods));
		$this->template->assign(array('images'=>$images));
		$this->print_layout($this->template_path());
	}

	public function view2()
	{
		$no = (int) $_GET['no'];
		$this->load->model('goodsmodel');

		$goods = $this->goodsmodel->get_goods($no);
		$images = $this->goodsmodel->get_goods_image($no);
		$additions = $this->goodsmodel->get_goods_addition($no);
		$options = $this->goodsmodel->get_goods_option($no);
		$suboptions = $this->goodsmodel->get_goods_suboption($no);
		$inputs = $this->goodsmodel->get_goods_input($no);

		foreach($options as $k => $opt){
			/* 대표가격 */
			if($opt['default_option'] == 'y'){
				$goods['price'] 			= $opt['price'];
				$goods['consumer_price'] 	= $opt['consumer_price'];
				$goods['reserve'] 			= $opt['reserve'];
				if( $opt['option_title'] ) $goods['option_divide_title'] = explode(',',$opt['option_title']);
				if( $opt['newtype'] ) $goods['divide_newtype'] = explode(',',$opt['newtype']);
			}
			$options[$k]['opt_join'] = implode('/',$optJoin);
		}


		$this->template->assign(array('goods'=>$goods));
		$this->template->assign(array('options'=>$options));
		$this->template->assign(array('additions'=>$additions));
		$this->template->assign(array('images'=>$images));
		$this->template->assign(array('images'=>$images));

		$this->print_layout($this->template_path());
	}

	public function cart()
	{
		$this->print_layout($this->template_path());
	}

	public function review()
	{
		$this->print_layout($this->template_path());
	}


	public function qna()
	{
		$this->print_layout($this->template_path());
	}

	public function search()
	{
		$this->load->model('goodsdisplay');
		$this->load->model('goodsmodel');
		$this->load->model('statsmodel');

		// 디스플레이 임시 코드명
		$display_key = $this->goodsdisplay->make_display_key();

		/*
		$result =$this->db->query("SHOW TABLES LIKE 'fm_search_".date("Y")."'");
		$row = $result->row_array();

		if($row == false){
			$query = "
				CREATE TABLE `fm_search_".date("Y")."` (
				 `seq` INT NOT NULL AUTO_INCREMENT,
				 `search_word` VARCHAR( 100 ) NOT NULL ,
				 `m_01` INT NOT NULL ,
				 `m_02` INT NOT NULL ,
				 `m_03` INT NOT NULL ,
				 `m_04` INT NOT NULL ,
				 `m_05` INT NOT NULL ,
				 `m_06` INT NOT NULL ,
				 `m_07` INT NOT NULL ,
				 `m_08` INT NOT NULL ,
				 `m_09` INT NOT NULL ,
				 `m_10` INT NOT NULL ,
				 `m_11` INT NOT NULL ,
				 `m_12` INT NOT NULL ,
				 PRIMARY KEY ( `seq` ) ,
				 INDEX ( `search_word` )
				) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_general_ci;
			";
			$this->db->query($query);
		}


		$query = "select count(search_word) as cnt from fm_search_".date("Y")." where search_word = '".trim(addslashes($_GET["search_text"]))."'";
		$result =$this->db->query($query);
		$row = $result->row_array();

		if($row["cnt"] > 0){
			$query = "update fm_search_".date("Y")." set m_".date("m")." = m_".date("m")." + 1 where search_word = '".trim(addslashes($_GET["search_text"]))."'";
			$this->db->query($query);

		}else{
			$query = "insert into fm_search_".date("Y")." (search_word, m_".date("m").") values ('".trim(addslashes($_GET["search_text"]))."', '1')";
			$this->db->query($query);
		}
		*/


		$category = !empty($_GET['category1']) ? $_GET['category1'] : '';
		$category = !empty($_GET['category2']) ? $_GET['category2'] : $category;
		$category = !empty($_GET['category3']) ? $_GET['category3'] : $category;
		$category = !empty($_GET['category4']) ? $_GET['category4'] : $category;
		$sort = !empty($_GET['sort']) ? $_GET['sort'] : '';

		/* 카테고리 정보 */
		if($_GET['category_code']){
			$this->load->model('categorymodel');
			$this->categoryData = $categoryData = $this->categorymodel->get_category_data($code);
		}

		// 디스플레이 설정 데이터
		$query  = $this->db->query("select * from fm_design_display where kind='search'");
		$display = $query->row_array();

		// 설정값이 없으면 생성
		if(!$display){
			$this->db->insert("fm_design_display",array(
				'admin_comment'	=> '상품검색리스트',
				'kind'			=> 'search',
				'count_w'		=> 2,
				'count_h'		=> 5,
				'style'			=> 'list',
				'image_size'	=> 'list2',
				'text_align'	=> 'left',
				'info_settings' => '[{"kind":"goods_name", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}"},{"kind":"summary", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}"},{"kind":"icon"},{"kind":"consumer_price", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}", "postfix":"won"},{"kind":"price", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}", "postfix":"won"},{"kind":"fblike"},{"kind":"status_icon"}]',
			));
			$query  = $this->db->query("select * from fm_design_display where kind='search'");
			$display = $query->row_array();
		}

		// 검색페이지는 기본 리스팅스타일이 list형이므로, lattice_a로 변경했을 때 최소 가로개수를 4개로 지정해줌
		if($display['style']!='lattice_a' && $_GET['display_style']=='lattice_a' && $display['count_w']<3){
			$display['count_w'] = 4;
		}

		$perpage = $_GET['perpage'] ? $_GET['perpage'] : $display['count_w'] * $display['count_h'];
		$perpage = $perpage ? $perpage : 10;
		$list_default_sort = $display['default_sort'] ? $display['default_sort'] : 'newly';

		$perpage_min = $display['count_w']*$display['count_h'];
		if($perpage != $display['count_w']*$display['count_h']){
			$display['count_h'] = ceil($perpage/$display['count_w']);
		}

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage ? $perpage : 10;
		$sc['image_size']		= $display['image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : $display['style'];
		$sc['list_goods_status']= $display['goods_status'];

		$sc['category_code']	= !empty($_GET['category_code'])	? $_GET['category_code'] : $category;
		$sc['brands']			= !empty($_GET['brands'])		? $_GET['brands'] : array();
		$sc['brand_code']		= !empty($_GET['brand_code'])	? $_GET['brand_code'] : '';
		$sc['search_text']		= !empty($_GET['search_text'])	? $_GET['search_text'] : '';
		$sc['old_search_text']	= !empty($_GET['old_search_text'])	? $_GET['old_search_text'] : '';
		$sc['start_price']		= !empty($_GET['start_price'])	? $_GET['start_price'] : '';
		$sc['end_price']		= !empty($_GET['end_price'])	? $_GET['end_price'] : '';
		$sc['color']			= !empty($_GET['color'])		? $_GET['color'] : '';

		$_GET['category1'] = $sc['category_code'];

		// 검색어 저장
		if($sc['search_text'] && $_GET['keyword_log_flag'] == 'Y'){
			$this->statsmodel->insert_search_stats($sc['search_text']);
			unset($_GET['keyword_log_flag']);
		}

		/* 카테고리 접근제한 조건 */
		$this->load->model('membermodel');
		$memberData = $this->membermodel->get_member_data($this->userInfo['member_seq']);
		if(!empty($this->userInfo['member_seq']) && $memberData['group_seq']) {
			$sc['member_group_seq']	= $memberData['group_seq'];
		}

		$list	= array();

		if($sc['search_text']||$sc['category_code']) {
			/*
			if($_GET['insearch']){
				$arr_search_text = explode("\n",$_GET['old_search_text']);

				if(!in_array($sc['search_text'],$arr_search_text)) $arr_search_text[] = $sc['search_text'];

				$sc['search_text'] = array();
				foreach($arr_search_text as $search_text){
					if(trim($search_text)){
						$sc['search_text'][] = trim($search_text);
					}
				}

				$_GET['old_search_text'] = implode("\n",$sc['search_text']);
			}else{
				$_GET['old_category1'] = $_GET['category1'];
				$_GET['old_category2'] = $_GET['category2'];
				$_GET['old_category3'] = $_GET['category3'];
				$_GET['old_category4'] = $_GET['category4'];
				$_GET['old_search_key'] = $sc['search_key'];
				$_GET['old_search_text'] = $sc['search_text'];
			}
			*/
			$list = $this->goodsmodel->goods_list($sc);
		}
		$this->template->assign($list);

		/**
		 * display
		**/
		$this->goodsdisplay->set('style',$sc['list_style']);
		$this->goodsdisplay->set('count_w',$display['count_w']);
		$this->goodsdisplay->set('count_w_lattice_b',$display['count_w_lattice_b']);
		$this->goodsdisplay->set('count_h',$display['count_h']);
		$this->goodsdisplay->set('image_decorations',$display['image_decorations']);
		$this->goodsdisplay->set('image_size',$display['image_size']);
		$this->goodsdisplay->set('text_align',$display['text_align']);
		$this->goodsdisplay->set('info_settings',$display['info_settings']);
		$this->goodsdisplay->set('display_key',$display_key);
		$this->goodsdisplay->set('displayTabsList',array($list));
		$this->goodsdisplay->set('displayGoodsList',!empty($list['record'])?$list['record']:array());
		if(!$this->fammerceMode){
			$this->goodsdisplay->set('target','_blank');
		}
		$goodsDisplayHTML = "<div id='{$display_key}' class='designSearchGoodsDisplay' designElement='searchGoodsDisplay' displaySeq='{$display['display_seq']}'>";
		$goodsDisplayHTML .= $this->goodsdisplay->print_(true);
		$goodsDisplayHTML .= "</div>";

		$tmpGET = $_GET;
		unset($tmpGET['sort']);
		unset($tmpGET['page']);
		$sortUrlQuerystring = getLinkFilter('',array_keys($tmpGET));

		$orders = $this->goodsdisplay->orders;
		unset($orders['popular']);

		$this->template->assign(array(
			'categoryData'			=> $categoryData,
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



	public function user_select(){
		$file_path	= $this->template_path();


		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if( strstr($referer['path'],'/mypage/mygdreview_') || $_GET['order3month'] ) {
			$order3month = true;
		}
		$this->template->assign('order3month',$order3month);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function user_select_list(){

		$this->tempate_modules();
		$file_path	= $this->template_path();

		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if( strstr($referer['path'],'/mypage/mygdreview_') || $_GET['order3month'] ) {
			//$order3month = true;
		}
		$this->template->assign('order3month',$order3month);
		if(isset($_GET['mborder']))$mborder = true;
		if(!isset($_GET['goodsStatus']))$_GET['goodsStatus'] = "";
		if(!isset($_GET['goodsView']))$_GET['goodsView'] = "";
		if(!isset($_GET['sort']))$_GET['sort'] = 0;
		if(!isset($_GET['page']))$_GET['page'] = 1;
		$page = $_GET['page'];

		$where = $subWhere = $whereStr = "";
		$bind = array();

		$arg_list = func_get_args();

		if( isset($_GET['selectCategory4']) &&  $_GET['selectCategory4'] ){
			$subWhere = " and l.category_code = ?";
			$bind[] = $_GET['selectCategory4'];
		}else if( isset($_GET['selectCategory3']) && $_GET['selectCategory3'] ){
			$subWhere = " and l.category_code = ?";
			$bind[] = $_GET['selectCategory3'];
		}else if( isset($_GET['selectCategory2']) && $_GET['selectCategory2'] ){
			$subWhere = " and l.category_code = ?";
			$bind[] = $_GET['selectCategory2'];
		}else if( isset($_GET['selectCategory1']) && $_GET['selectCategory1'] ){
			$subWhere = " and l.category_code = ?";
			$bind[] = $_GET['selectCategory1'];
		}

		/* 카테고리 접근제한 조건 */
		$this->load->model('membermodel');
		$memberData = $this->membermodel->get_member_data($this->userInfo['member_seq']);
		if(!empty($this->userInfo['member_seq']) && $memberData['group_seq']){
			$subWhere .= " and ( cg.group_seq is null or find_in_set('".$memberData['group_seq']."',concat_ws(',',cg.group_seq) ) )";
		}else{
			$subWhere .= " and ( cg.group_seq is null ) ";
		}

		if($subWhere){
			$where[] = "g.goods_seq in
			(
				select l.goods_seq from fm_category_link l
				left join fm_category_group as cg on l.category_code = cg.category_code
				where 1 ".$subWhere."
			)
			";
		}

		if( isset($_GET['selectGoodsName']) && $_GET['selectGoodsName'] ){
			$where[] = "g.goods_name like ?";
			$bind[] = '%'.$_GET['selectGoodsName'].'%';
		}
		if( isset($_GET['selectStartPrice']) && $_GET['selectStartPrice'] ){
			$where[] = "o.price >= ?";
			$bind[] = $_GET['selectStartPrice'];
		}
		if( isset($_GET['selectEndPrice']) && $_GET['selectEndPrice'] ){
			$where[] = "o.price <= ?";
			$bind[] = $_GET['selectEndPrice'];
		}

		if( $_GET['goodsStatus'] ){
			$where[] = "g.goods_status = ?";
			$bind[] = $_GET['goodsStatus'];
		}

		if( $_GET['goodsView'] ){
			$where[] = "g.goods_view = ?";
			$bind[] = $_GET['goodsView'];
		}


		if( $_GET['order_seq'] ){
			$where[] = "ord.order_seq = ?";
			$bind[] = $_GET['order_seq'];
		}


		if($where){
			$whereStr = ' and '.implode(' and ',$where);
		}

		$arrSort = array('g.goods_seq desc','g.goods_seq asc','g.purchase_ea desc','g.purchase_ea asc','g.page_view desc','g.page_view asc','g.review_count desc','g.review_count asc');
		$sortStr = " order by " .$arrSort[$_GET['sort']];

		if($order3month || $mborder){
			if(!$this->arr_step)	$this->arr_step = config_load('step');
			if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
			if(!$this->cfg_order)	$this->cfg_order = config_load('order');
			$endday3 = date("Y-m-d 23:59:59");
			$startday3 = date("Y-m-d 00:00:00", strtotime("-3 month"));
			$query = "select g.goods_seq,g.goods_name,o.price, ord.order_seq,g.string_price_use,g.string_price
			from fm_order_item orditm
			left join fm_order ord on orditm.order_seq=ord.order_seq
			left join fm_goods g on g.goods_seq=orditm.goods_seq
			left join fm_goods_option o on o.goods_seq=g.goods_seq
			";
			$query .= "
			where
				o.default_option ='y'
				AND g.goods_view = 'Look'
				AND ord.member_seq = '{$this->userInfo[member_seq]}'
				AND (ord.step = '70' OR ord.step = '75')
				AND g.goods_type != 'gift'
				".$whereStr.$sortStr;
				//group by orditm.order_seq AND ord.regist_date between '".$startday3."' and '".$endday3."'
		}else{
			$query = "select g.goods_seq,g.goods_name,o.price,g.string_price_use,g.string_price
			from fm_goods g
			inner join fm_goods_option o on o.goods_seq=g.goods_seq";
			$query .= "
			where
				o.default_option ='y' AND g.goods_view = 'Look' AND g.goods_type != 'gift' ".$whereStr.$sortStr;
		}
		$result = select_page(10,$page,10,$query,$bind);
		$result['page']['querystring'] = get_args_list();
		foreach($result['record'] as $recorddata){
			$recorddata['image'] = viewImg($recorddata['goods_seq'],'thumbView');

			/* 회원 대체 가격 추가 leewh 2014-12-30 */
			$recorddata['string_price']		= get_string_price($recorddata);
			$recorddata['string_price_use']	= 0;
			if	($recorddata['string_price'] != '')	$recorddata['string_price_use']	= 1;

			$record[] = $recorddata;
		}
		unset($result['record']);
		$result['record'] = $record;

		$this->template->assign($result);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function qna_catalog()
	{
		$this->boardurl = $this->mygdqna->boardurl;
		$this->_board_list($this->mygdqnatbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
		if(isset($_GET['goods_seq'])) $this->template->assign('designMode',false);
		$this->print_layout($this->template_path());
	}

	public function qna_view()
	{
		$this->boardurl = $this->mygdqna->boardurl;
		$this->_board_view($this->mygdqnatbl);
	}

	public function qna_write()
	{
		$this->boardurl = $this->mygdqna->boardurl;
		$this->_board_write($this->mygdqnatbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}

	public function review_catalog()
	{
		$this->boardurl = $this->mygdreview->boardurl;
		$this->_board_list($this->mygdreviewtbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
		if(isset($_GET['goods_seq'])) $this->template->assign('designMode',false);
		$this->print_layout($this->template_path());
	}

	public function review_view()
	{
		$this->boardurl = $this->mygdreview->boardurl;
		$this->_board_view($this->mygdreviewtbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}


	public function review_write()
	{
		$this->boardurl = $this->mygdreview->boardurl;
		$this->_board_write($this->mygdreviewtbl);
		$this->template->assign('boardurl',$this->boardurl);//link url
	}
	//board controller list
	private function _board_list($boardid)
	{
		define('BOARDID',$boardid);
		$_GET['iframe'] = 1;
		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}
		$sql['whereis']	= ' and id= "'.$boardid.'" ';
		$sql['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
		getboardicon();//icon setting

		if (!isset($this->manager['id'])) pageBack('존재하지 않는 게시판입니다.');
		get_auth($this->manager, '', 'read', $isperm);//접근권한체크
		$this->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';
		$this->manager['fileperm_read']= (isset($isperm['fileperm_read']))?$isperm['fileperm_read']:'';
		if ( $isperm['isperm_read'] === false ) {
			if(!defined('__ISADMIN__')) {
				//$this->boardurl->perm = $this->Boardmanager->realboardpermurl.BOARDID.'&popup=1&returnurl=';						//접근권한
				//if(!empty($_GET['popup']) ) {pageClose('접근권한이 없습니다!');}else{pageRedirect($this->boardurl->perm,'');}
			}
		}

		get_auth($this->manager, '', 'write', $isperm);//접근권한체크
		$this->manager['isperm_write'] = ($isperm['isperm_write'] === true)?'':'_no';//'.$this->manager['isperm_write'].'

		$this->template->assign('manager',$this->manager);
		$this->template->assign('designMode',false);
		$this->lists('goods');
	}

	//board controller view
	private function _board_view($boardid)
	{
		define('BOARDID',$boardid);
		$_GET['iframe'] = 1;
		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}

		$sql['whereis']	= ' and id= "'.$boardid.'" ';
		$sql['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
		getboardicon();//icon setting

		get_auth($this->manager, $data, 'read', $isperm);//접근권한체크
		if(!defined('__ISADMIN__')) {
			//if ( $isperm['isperm_read'] === false ) pageRedirect($isperm['fileperm_read'],'');
		}
		$this->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';

		get_auth($this->manager, $data, 'write', $isperm);//접근권한체크
		$this->manager['isperm_write']	= ($isperm['isperm_write'] === true)?'':'_no';//등록권한
		$this->manager['isperm_moddel'] = ( $isperm['isperm_moddel'] === true)?'':'_no';//수정/삭제권한

		if( $this->manager['isperm_moddel'] == '_no' )  {

			if( ($data['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || ( !empty($data['mseq']) && !defined('__ISUSER__') ) ) {
				$this->manager['isperm_moddel'] = '_mbno';//버튼숨김(회원 > 본인만 가능함
			}else{
				// 비번입력후 브라우저를 닫기전까지는 등록/삭제가능함
				$ss_pwwrite_name = 'board_pwwrite_'.BOARDID;
				$boardpwwritess = $this->session->userdata($ss_pwwrite_name);
				if ( strstr($boardpwwritess,'['.$data['seq'].']') && !empty($boardpwwritess)) {
					$this->manager['isperm_moddel'] = '';//비회원 > 접근권한있음
				}
			}
		}
		if( BOARDID == 'goods_review' ) {
			$reserves = ($this->reserves)?$this->reserves:config_load('reserve');//적립금자동지급관련
			### 특정기간
			/**
			if($reserves['bbs_start_date'] && $reserves['bbs_end_date']){
				$today = date("Y-m-d");
				if($today>=$reserves['bbs_start_date'] && $today<=$reserves['bbs_end_date']){
					$reserves['autoemoney_photo']	= $reserves['emoneyBbs_limit'];
					$reserves['autoemoney_review']	= $reserves['emoneyBbs_limit'];

					$reserves['autopoint_photo']	= $reserves['pointBbs_limit'];
					$reserves['autopoint_review']	= $reserves['pointBbs_limit'];
				}
			}
			**/
			$this->template->assign('reserves',$reserves);
			$this->session->unset_userdata('sess_order');//비회원주문번호세션제거
		}

		$this->template->assign('manager',$this->manager);
		$this->template->assign('designMode',false);
		$this->goods_board_view('goods');
	}

	//board controller write
	private function _board_write($boardid)
	{
		define('BOARDID',$boardid);
		$_GET['iframe'] = 1;
		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}

		if( BOARDID == 'goods_review' ) {
			$reserves = ($this->reserves)?$this->reserves:config_load('reserve');//적립금자동지급관련
			### 특정기간
			/**
			if($reserves['bbs_start_date'] && $reserves['bbs_end_date']){
				$today = date("Y-m-d");
				if($today>=$reserves['bbs_start_date'] && $today<=$reserves['bbs_end_date']){
					$reserves['autoemoney_photo']	= $reserves['emoneyBbs_limit'];
					$reserves['autoemoney_review']	= $reserves['emoneyBbs_limit'];

					$reserves['autopoint_photo']	= $reserves['pointBbs_limit'];
					$reserves['autopoint_review']	= $reserves['pointBbs_limit'];
				}
			}
			**/
			$this->template->assign('reserves',$reserves);
			$this->session->unset_userdata('sess_order');//비회원주문번호세션제거
		}

		$sql['whereis']	= ' and id= "'.$boardid.'" ';
		$sql['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
		getboardicon();//icon setting
		$this->template->assign('manager',$this->manager);
		$this->template->assign('designMode',false);
		$this->write('goods');
	}

	public function goods_display_all(){

		$display_seq = $_GET['display_seq'];

		// 디스플레이 설정 데이터
		$query  = $this->db->query("select * from fm_design_display where display_seq = ?",$display_seq);
		$display = $query->row_array();

		$this->template->assign('title',$display['title']);
		$this->template->assign('perpage',20);
		$this->template->assign('display_seq',$display_seq);
		$this->print_layout($this->template_path());
	}

	/* 상품 재입고알림 신청화면 */
	public function restock_notify_apply(){
		$this->load->model('goodsmodel');
		$this->load->model('membermodel');

		$no = $_GET['goods_seq'];
		$goods = $this->goodsmodel->get_goods($no);
		$memberData = $this->membermodel->get_member_data($this->userInfo['member_seq']);
		$options = $this->goodsmodel->get_goods_option($no);
		$suboptions = $this->goodsmodel->get_goods_suboption($no);
		$inputs = $this->goodsmodel->get_goods_input($no);

		if(isset($options[0]['option_divide_title'])) $goods['option_divide_title'] = $options[0]['option_divide_title'];
		if(isset($options[0]['divide_newtype'])) $goods['divide_newtype'] = $options[0]['divide_newtype'];

		$this->template->assign(array(
			'no'			=> $no,
			'goods'			=> $goods,
			'memberData'	=> $memberData,
			'options'	=> $options,
			'suboptions'	=> $suboptions,
			'inputs'	=> $inputs,
		));
		$this->template->define('tpl',$this->template_path());
		$this->template->print_('tpl');
	}

	//	브랜드 목록
	public function brand_list(){
		$this->load->model('brandmodel');

		$category_code = $_GET['code'];
		$result = $this->brandmodel->get_list($category_code);
		$this->template->assign('loop',$result);

		if(!$result){
			header("location:../goods/brand?code=" . $category_code);
			exit;
		}

		$categorys['category_code'] = $this->brandmodel->split_brand($category_code);
		if($categorys['category_code']){
			foreach($categorys['category_code'] as $code){
				$categorys['category'][] = $this->brandmodel->one_brand_name($code);
			}
			$this->template->assign('categorys',$categorys);
		}

		$this->print_layout($this->template_path());
	}

	//개인결제
	public function personal()
	{

		if($this->userInfo['member_seq']){

			redirect("/mypage/personal");

		}


		$this->load->model('goodsdisplay');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');

		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

		/* 카테고리 정보 */
		$categoryData = $this->categorymodel->get_category_data($code);

		$code = $categoryData['category_code'];

		$childCategoryData = $this->categorymodel->get_list($code,array(
			"hide = '0'",
			"level >= 2"
		));
		//print_r($childCategoryData);
		if(strlen($code)>4 && !$childCategoryData){
			$childCategoryData = $this->categorymodel->get_list(substr($code,0,strlen($code)-4),array(
				"hide = '0'",
				"level >= 2"
			));
		}

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['search_text']		= (!empty($_GET['search_text']))?str_replace(array('"',"'"),"",$_GET['search_text']):'';
		$sc['category']			= $code;
		$sc['perpage']			= $_GET['perpage'] ? $_GET['perpage'] : 10;
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : '';
		$sc['list_goods_status']= $categoryData['list_goods_status'];

		if($_GET['so_brand'])	$sc['so_brand']		= $_GET['so_brand'];
		if($_GET['so_option1'])	$sc['so_option1']	= $_GET['so_option1'];
		if($_GET['so_option2'])	$sc['so_option2']	= $_GET['so_option2'];


		/* 쇼핑몰 타이틀 */
		if($this->config_basic['shopCategoryTitleTag'] && $categoryData['title']){
			$title = str_replace("{카테고리명}",$categoryData['title'],$this->config_basic['shopCategoryTitleTag']);
			$this->template->assign(array('shopTitle'=>$title));
		}

		$str_where_order = " AND regist_date >= '".date('Y-m-d',strtotime("-5 day"))." 00:00:00'";

		$key = get_shop_key();
		$query = "
				select * from (
				SELECT title, order_user_name, total_price, order_seq, enuri, member_seq, order_email, order_phone, order_cellphone, person_seq,
					regist_date,
					(SELECT userid FROM fm_member WHERE member_seq=pr.member_seq) userid,
					(SELECT AES_DECRYPT(UNHEX(email), '{$key}') as email FROM fm_member WHERE member_seq=pr.member_seq) mbinfo_email,
					(SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=pr.member_seq) group_name,
					(select goods_name from fm_goods where goods_seq
						in (select goods_seq from fm_person_cart where person_seq = pr.person_seq) limit 1) goods_name,
					(select count(goods_seq) from fm_person_cart where person_seq = pr.person_seq) item_cnt
				FROM fm_person pr where order_seq is null ".$str_where_order.") t ".$str_where. " order by person_seq desc
		";

		$list = select_page($sc['perpage'],$sc['page'],10,$query,'');
		$list['page']['querystring'] = get_args_list();
		$list['search_yn'] = $search_yn;

		$this->template->assign($list);

		$this->template->assign(array(
			'categoryCode'			=> $code,
			'categoryTitle'			=> $categoryData['title'],
			'categoryData'			=> $categoryData,
			'childCategoryData'		=> $childCategoryData,
		));
		$this->getboardcatalogcode = $code;//상품후기 : 게시판추가시 이용됨


		/**
		 * display
		**/
		$sc['list_style'] = "person";
		$this->goodsdisplay->set('style',$sc['list_style'] ? $sc['list_style'] : $categoryData['list_style']);
		$this->goodsdisplay->set('count_w',$categoryData['list_count_w']);
		$this->goodsdisplay->set('count_w_lattice_b',$categoryData['list_count_w_lattice_b']);
		$this->goodsdisplay->set('count_h',$categoryData['list_count_h']);
		$this->goodsdisplay->set('perpage',$perpage);
		$this->goodsdisplay->set('image_decorations',$categoryData['list_image_decorations']);
		$this->goodsdisplay->set('image_size',$categoryData['list_image_size']);
		$this->goodsdisplay->set('text_align',$categoryData['list_text_align']);
		$this->goodsdisplay->set('info_settings',$categoryData['list_info_settings']);
		$this->goodsdisplay->set('displayTabsList',array($list));
		$this->goodsdisplay->set('displayGoodsList',$list['record']);
		$goodsDisplayHTML = "<div class='designPersonalGoodsDisplay' designElement='personalGoodsDisplay'>";
		$goodsDisplayHTML .= $this->goodsdisplay->print_(true);
		$goodsDisplayHTML .= "</div>";

		unset($_GET['sort']);
		unset($_GET['perpage']);
		$sortUrlQuerystring = getLinkFilter('',array_keys($_GET));

		$this->template->assign(array(
			'goodsDisplayHTML'		=> $goodsDisplayHTML,
			'sortUrlQuerystring'	=> $sortUrlQuerystring,
			'sort'					=> $sc['sort'],
			'sc'					=> $sc,
			'orders'				=> $this->goodsdisplay->orders,
		));


		$this->print_layout($this->template_path());

	}



	public function sizeguide()
	{
		$this->load->model('categorymodel');
		$code = isset($_GET['code']) ? $_GET['code'] : '';
		/* 카테고리 정보 */
		$categoryData = $this->categorymodel->get_category_data($code);

		$tabtype = array();
		$type = ($_GET['code'])?1:0;
		$this->template->assign('code',$type);
		$this->template->define(array('tpl'=>$this->skin.'/goods/_sizeguide.html'));
		$this->template->print_('tpl');
	}

	public function display_related_goods(){

		$goods_seq = $_GET['goods_seq'];

		$this->load->model('goodsmodel');

		// 기존 소스
		$goods = $this->goodsmodel->get_goods($goods_seq);
		$loop = get_related_goods($goods_seq, $goods['relation_type'], ($goods['relation_count_w']*$goods['relation_count_h']));
		$this->template->assign('goods',$goods);
		$this->template->assign('loop',$loop);

		// 신규 소스
		$this->load->model('goodsdisplay');
		$sql = "select * from fm_design_display where kind = 'relation'";
		$query = $this->db->query($sql);
		$display = $query->row_array();
		if(!$display){
			$this->goodsmodel->get_goods_relation_display_seq();
			$sql = "select * from fm_design_display where kind = 'relation'";
			$query = $this->db->query($sql);
			$display = $query->row_array();
		}

		if($goods['relation_count_w']==0 && $goods['relation_count_h']==0){
			$display['count_w'] = 4;
			$display['count_h'] = 1;
		}else{
			$display['count_w'] = $goods['relation_count_w'];
			$display['count_h'] = $goods['relation_count_h'];
		}

		// 페이머스에서는 가로 3개만 노출
		if(($this->fammerceMode || $this->storefammerceMode) && $display['count_w'] > 3){
			$display['count_w'] = 3;
		}

		$display['image_size'] = $goods['relation_image_size'];
		$display['auto_criteria'] = $goods['relation_criteria'];

		if($goods['relation_type']=='AUTO'){
			$sc = $this->goodsdisplay->search_condition($display['auto_criteria'], array(),'relation');
			if(!$sc['category'] && $sc['selectGoodsRelationCategory']) {
				$category_code = $this->goodsmodel->get_goods_category_default($goods_seq);
				$category_code = $category_code['category_code'];
				$sc['category'] = $category_code;
			}

			$sc['sort']		= $sc['auto_order'];
			$sc['display_seq']		= $display['display_seq'];
			$sc['display_tab_index']= 0;
			$sc['page']				= 1;
			$sc['perpage']			= $display['count_w']*$display['count_h'];
			$sc['image_size']		= $display['image_size'];
			$sc['goods_seq_exclude']= $goods['goods_seq'];

			if($this->goodsdisplay->info_settings_have_eventprice($display['info_settings'])){
				$sc['join_event']	= true;
			}

			$list = $this->goodsmodel->goods_list($sc);
		}else{
			$sc['relation'] = $goods['goods_seq'];
			$list = $this->goodsmodel->goods_list($sc);
		}
		if($list['record']){
			$display_key = $this->goodsdisplay->make_display_key();
			$this->goodsdisplay->set('display_key',$display_key);
//			$this->goodsdisplay->set('title',$display['title']);
			$this->goodsdisplay->set('style',$display['style']);
			$this->goodsdisplay->set('count_w',$display['count_w']);
			$this->goodsdisplay->set('count_h',$display['count_h']);
			$this->goodsdisplay->set('image_decorations',$display['image_decorations']);
			$this->goodsdisplay->set('image_size',$display['image_size']);
			$this->goodsdisplay->set('text_align',$display['text_align']);
			$this->goodsdisplay->set('info_settings',$display['info_settings']);
			$this->goodsdisplay->set('display_key',$display_key);
			$this->goodsdisplay->set('displayGoodsList',$list['record']);
			$this->goodsdisplay->set('displayTabsList',array($list));
			$this->goodsdisplay->set('tab_design_type',$display['tab_design_type']);

			$goodsRelationDisplayHTML = "<div id='{$display_key}' class='designGoodsRelationDisplay' designElement='goodsRelationDisplay' displaySeq='{$display['display_seq']}'>";
			$goodsRelationDisplayHTML .= $this->goodsdisplay->print_(true);
			$goodsRelationDisplayHTML .= "</div>";

			$layout_config = layout_config_autoload($this->skin,'basic');

			$this->template->assign(array('layout_config'=>$layout_config['basic'],'record'=>$list['record'],'goodsRelationDisplayHTML'=>$goodsRelationDisplayHTML));
		}

		$this->template->define(array('tpl'=>$this->skin.'/goods/_display_related_goods.html'));
		$this->template->print_('tpl');

	}

	public function display_goods_images(){

		$goods_seq = $_GET['goods_seq'];
		$image_slide_type = $_GET['image_slide_type'];

		$this->load->model('goodsmodel');

		$images = $this->goodsmodel->get_goods_image($goods_seq);

		$this->template->assign(array(
			'images' => $images,
			'goods_seq' => $goods_seq
		));

		$this->template->define(array('tpl'=>$this->skin.'/goods/_display_goods_images_'.$image_slide_type.'.html'));
		$this->template->print_('tpl');

	}

	//지역별
	public function location(){
		$this->load->model('goodsdisplay');
		$this->load->model('goodsmodel');
		$this->load->model('locationmodel');

		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

		if($_GET['designMode'] && !$code){
			$query = $this->db->query("select location_code from fm_location where level>1 order by location_code asc limit 1");
			$tmp = $query->row_array();
			$code = $_GET['code'] = $tmp['location_code'];
		}

		/* 브랜드 정보 */
		$categoryData = $this->locationmodel->get_location_data($code);
		$childCategoryData = $this->locationmodel->get_list($code,array(
			"hide = '0'",
			"level >= 2"
		));
		if(strlen($code)>4 && !$childCategoryData){
			$childCategoryData = $this->locationmodel->get_list(substr($code,0,strlen($code)-4),array(
				"hide = '0'",
				"level >= 2"
			));
		}

		/* 쇼핑몰 타이틀 */
		if($this->config_basic['shopCategoryTitleTag'] && $categoryData['title']){
			$title = str_replace("{카테고리명}",$categoryData['title'],$this->config_basic['shopCategoryTitleTag']);
			$this->template->assign(array('shopTitle'=>$title));
		}

		$perpage = $_GET['perpage'] ? $_GET['perpage'] : $categoryData['list_count_w'] * $categoryData['list_count_h'];
		$perpage = $perpage ? $perpage : 10;
		$list_default_sort = $categoryData['list_default_sort'] ? $categoryData['list_default_sort'] : 'popular';

		$perpage_min = $categoryData['list_count_w']*$categoryData['list_count_h'];
		if($perpage != $categoryData['list_count_w']*$categoryData['list_count_h']){
			$categoryData['list_count_h'] = ceil($perpage/$categoryData['list_count_w']);
		}

		/* 동영상/플래시매직 치환 */
		$categoryData['top_html'] = showdesignEditor($categoryData['top_html']);

		/**
		 * list setting
		**/
		$sc=array();
		$sc['sort']				= $sort ? $sort : $list_default_sort;
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		if($categoryData['list_paging_use']=='n'){
			$sc['limit']			= $perpage;
		}else{
			$sc['perpage']			= $perpage;
		}
		$sc['image_size']		= $categoryData['list_image_size'];
		$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : $categoryData['list_style'];
		$sc['list_goods_status']= $categoryData['list_goods_status'];

		$sc['category_code']	= !empty($_GET['category_code'])	? $_GET['category_code'] : '';
		$sc['brands']			= !empty($_GET['brands'])		? $_GET['brands'] : array();
		$sc['brand_code']		= !empty($_GET['brand_code'])	? $_GET['brand_code'] : '';
		$sc['location_code']	= $code;
		$sc['search_text']		= !empty($_GET['search_text'])	? $_GET['search_text'] : '';
		$sc['old_search_text']	= !empty($_GET['old_search_text'])	? $_GET['old_search_text'] : '';
		$sc['start_price']		= !empty($_GET['start_price'])	? $_GET['start_price'] : '';
		$sc['end_price']		= !empty($_GET['end_price'])	? $_GET['end_price'] : '';
		$sc['color']			= !empty($_GET['color'])		? $_GET['color'] : '';

		$list	= $this->goodsmodel->goods_list($sc);
		$this->template->assign($list);

		if($categoryData['list_paging_use']=='n'){
			$this->template->assign(array('page'=>array('totalcount'=>count($list['record']))));
		}

		$this->template->assign(array(
			'categoryCode'			=> $code,
			'categoryTitle'			=> $categoryData['title'],
			'categoryData'			=> $categoryData,
			'childCategoryData'		=> $childCategoryData,
		));
		$this->getboardcatalogcode = $code;//상품후기 : 게시판추가시 이용됨

		/**
		 * display
		**/
		$display_key = $this->goodsdisplay->make_display_key();
		$this->goodsdisplay->set('display_key',$display_key);
		$this->goodsdisplay->set('style',$sc['list_style']);
		$this->goodsdisplay->set('count_w',$categoryData['list_count_w']);
		$this->goodsdisplay->set('count_w_lattice_b',$categoryData['list_count_w_lattice_b']);
		$this->goodsdisplay->set('count_h',$categoryData['list_count_h']);
		$this->goodsdisplay->set('image_decorations',$categoryData['list_image_decorations']);
		$this->goodsdisplay->set('image_size',$categoryData['list_image_size']);
		$this->goodsdisplay->set('text_align',$categoryData['list_text_align']);
		$this->goodsdisplay->set('info_settings',$categoryData['list_info_settings']);
		$this->goodsdisplay->set('displayTabsList',array($list));
		$this->goodsdisplay->set('displayGoodsList',$list['record']);
		if(strstr($categoryData['list_info_settings'],"fblike") && ( !$this->__APP_LIKE_TYPE__ || $this->__APP_LIKE_TYPE__ == 'API') ) {//라이크포함시
			$goodsDisplayHTML = $this->is_file_facebook_tag;
			define('FACEBOOK_TAG_PRINTED','YES');
			$goodsDisplayHTML .= "<div id='{$display_key}' class='designLocationGoodsDisplay' designElement='locationGoodsDisplay'>";
		}else{
			$goodsDisplayHTML = "<div id='{$display_key}' class='designLocationGoodsDisplay' designElement='locationGoodsDisplay'>";
		}
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

	public function recently(){
		$result = array();
		$today_view = $_COOKIE['today_view'];
		if( $today_view ) {
			$today_view = unserialize($today_view);
			krsort($today_view);

			$this->load->model('goodsmodel');
			$result = $this->goodsmodel->get_goods_list($today_view,'thumbScroll');

			// 로그인 후 비회원 가격대체문구 노출 오류 수정 leewh 2014-12-05
			if ($result) {
				foreach ($result as $key => $value) {
					if	($result[$key]['sale_price'])	$result[$key]['price']	= $result[$key]['sale_price'];
					$result[$key]['string_price'] = get_string_price($result[$key]);
					$result[$key]['string_price_use']	= 0;
					if	($result[$key]['string_price'] != '')	$result[$key]['string_price_use']	= 1;
				}
			}
		}

		$this->template->assign('record',$result);
		$this->print_layout($this->template_path());
	}

	public function recently_option(){

		$this->load->helper('order');
		$this->load->model('goodsmodel');
		$this->load->model('wishmodel');
		$this->load->model('categorymodel');
		$this->load->library('sale');

		$applypage		= 'option';
		$cfg_order		= config_load('order');
		$goods_seq		= (int) $_GET['no'];
		$goods			= $this->goodsmodel->get_goods($goods_seq);
		$suboptions		= $this->goodsmodel->get_goods_suboption($goods_seq);
		$inputs			= $this->goodsmodel->get_goods_input($goods_seq);
		$options		= $this->goodsmodel->get_goods_option($goods_seq);
		$images			= $this->goodsmodel->get_goods_image($goods_seq);
		$goods['image']	= $images[1]['thumbView']['image'];
		$categorys		= $this->goodsmodel->get_goods_category($goods_seq);
		if($categorys) foreach($categorys as $key => $data_category){
			if( $data_category['link'] == 1 ){
				$category_code	= $this->categorymodel->split_category($data_category['category_code']);
			}
		}

		// 절사 설정 저장
		if	(!$this->config_system)	$this->config_system	= config_load('system');
		if	($this->config_system['cutting_sale_use'] != 'none'){
			$cfg_cutting['action']	= $this->config_system['cutting_sale_action'];
			$cfg_cutting['price']	= $this->config_system['cutting_sale_price'];
		}

		//----> sale library 적용
		$param['cal_type']				= 'list';
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		foreach($options as $k => $opt){

			//----> sale library 적용
			unset($param);
			$param['option_type']			= 'option';
			$param['consumer_price']		= $opt['consumer_price'];
			$param['price']					= $opt['price'];
			$param['total_price']			= $opt['price'];
			$param['ea']					= 1;
			$param['category_code']			= $category_code;
			$param['goods_seq']				= $goods['goods_seq'];
			$param['goods']					= $goods;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);
			$this->sale->reset_init();
			$opt['price']			= $sales['result_price'];
			$options[$k]['price']	= $opt['price'];
			//<---- sale library 적용

			/* 대표가격 */
			if($opt['default_option'] == 'y'){
				$goods['price'] 			= $opt['price'];
				$goods['consumer_price'] 	= $opt['consumer_price'];
				$goods['reserve'] 			= $opt['reserve'];
				if( $opt['option_title'] ) $goods['option_divide_title'] = explode(',',$opt['option_title']);
			}
			$options[$k]['opt_join'] = implode('/',$optJoin);

			// 재고 체크
			$opt['chk_stock'] = check_stock_option($goods['goods_seq'],$opt['option1'],$opt['option2'],$opt['option3'],$opt['option4'],$opt['option5'],$opt['ea'],$cfg_order);
			if( $opt['chk_stock'] ) $runout = false;
			$options[$k]['chk_stock'] = $opt['chk_stock'];
		}

		if($suboptions) foreach($suboptions as $key => $tmp){
			foreach($tmp as $k => $opt){

				//----> sale library 적용
				unset($param);
				$param['option_type']			= 'suboption';
				$param['sub_sale']				= $opt['sub_sale'];
				$param['consumer_price']		= $opt['consumer_price'];
				$param['price']					= $opt['price'];
				$param['total_price']			= $opt['price'];
				$param['ea']					= 1;
				$param['category_code']			= $category_code;
				$param['goods_seq']				= $goods['goods_seq'];
				$param['goods']					= $goods;
				$this->sale->set_init($param);
				$sales	= $this->sale->calculate_sale_price($applypage);
				$this->sale->reset_init();
				$opt['price']	= $sales['result_price'];
				//<---- sale library 적용

				$opt['chk_stock'] = check_stock_suboption($goods['goods_seq'],$opt['suboption_title'],$opt['suboption'],$ea,$cfg_order);
				if( $opt['chk_stock'] ){
					$sub_runout = true;
				}

				$suboptions[$key][$k]	= $opt;
			}
		}

		$file = str_replace('recently_option','_recently_option',$this->template_path());
		$this->template->assign(array('cfg_cutting'=>$cfg_cutting));
		$this->template->assign(array('options'=>$options));
		$this->template->assign(array('goods'=>$goods));
		$this->template->assign(array('suboptions'=>$suboptions));
		$this->template->assign(array('inputs'=>$inputs));
		$this->template->define(array('LAYOUT'=>$file));
		$this->template->print_('LAYOUT');
	}

	public function design_display_tab(){
		$display_seq = $_POST['display_seq'];
		$tab_index = $_POST['tab_index'];
		$_GET['page'] = !empty($_POST['page']) ? $_POST['page'] : null;

		$this->designDisplayTabAjaxIdx=$tab_index;

		if($_POST['category']){
			$this->template->include_('showCategoryRecommendDisplay');
			showCategoryRecommendDisplay($_POST['category']);
		}else if($_POST['brand']){
			$this->template->include_('showLocationRecommendDisplay');
			showLocationRecommendDisplay($_POST['brand']);
		}else if($_POST['location']){
			$this->template->include_('showBrandRecommendDisplay');
			showBrandRecommendDisplay($_POST['location']);
		}else {
			$this->template->include_('showDesignDisplay');
			showDesignDisplay($display_seq,null,null,'cach');
		}

	}

	/* 쿠폰상품 위치 서비스 Ajax용 :: 2014-04-02 lwh */
	public function coupon_location_ajax(){
		$goods_seq	= $_POST['goods_seq'];
		$option_seq	= $_POST['option_seq'];
		$width		= $_POST['width'];

		$this->load->model('goodsmodel');
		$res	= $this->goodsmodel->get_goods_option($goods_seq);

		if($res)foreach($res as $key => $opt){
			if($opt['option_seq']==$option_seq){
				$opt['opspecial_location'] = get_goods_options_print_array($opt);
				$option			= $opt['option'.$opt['opspecial_location']['address']];
				$address		= $opt['address'] . " " . $opt['addressdetail'];
				$address_street	= $opt['address_street'];
				$biztel			= $opt['biztel'];
			}
		}

		/* 위치 구하기 */
		$this->load->library('SofeeXmlParser');
		$maparr = $this->config_basic;

		$view_name	= $option;
		$addr = urlencode($address);

		$xmlParser = new SofeeXmlParser();
		$key = $maparr['mapKey'];
		$url = "http://openapi.map.naver.com/api/geocode.php?key=".$key."&encoding=utf-8&coord=latlng&query=".$addr;
		$xmlParser->parseFile($url);
		$tree = $xmlParser->getTree();

		if($tree['geocode']['item'][0]['point']['x']['value']){
			$point = array('y'=>$tree['geocode']['item'][0]['point']['x']['value'], 'x'=>$tree['geocode']['item'][0]['point']['y']['value']);
		}else{
			$point = array('y'=>$tree['geocode']['item']['point']['x']['value'], 'x'=>$tree['geocode']['item']['point']['y']['value']);
		}

		$this->template->assign(array('option'=>$option));
		$this->template->assign(array('address'=>$address));
		$this->template->assign(array('street'=>$address_street));
		$this->template->assign(array('biztel'=>$biztel));
		$this->template->assign(array('width'=>$width));
		$this->template->assign(array('x_lang'=>$point['x']));
		$this->template->assign(array('y_lang'=>$point['y']));

		$this->template->define(array('LAYOUT'=>$this->template_path()));
		$this->template->print_('LAYOUT');
	}

	//모바일스킨>최근 본상품 삭제
	public function goods_del()
	{
		$goods_seq_ar = $_POST['goods_seq'];

		$today_num = 0;
		$today_view = $_COOKIE['today_view'];
		if( $today_view ) $today_view = unserialize($today_view);
		if( $today_view ) foreach($today_view as $v){
			$today_num++;
			if( count($today_view) > 50 && $today_num == 1 ) continue;
			if( in_array($v,$goods_seq_ar)) continue;
			$data_today_view[] = $v;
		}

		if( $data_today_view ) $data_today_view = serialize($data_today_view);
		setcookie('today_view',$data_today_view,time()+86400,'/');
		$callback = "parent.document.location.reload();";
		openDialogAlert("최근 본상품을 삭제하였습니다.",400,140,'parent',$callback);

	}

	/* 우측퀵메뉴 기능개선 선택한 최근본상품 삭제 leewh 2014-06-05 */
	public function goods_recent_del() {
		$goods_seq = $_POST['goods_seq'];
		$msg = "fail";

		$today_view = unserialize($_COOKIE['today_view']);
		if (in_array($goods_seq, $today_view)) {
			$del_key = array_keys($today_view,$goods_seq);
			unset($today_view[$del_key[0]]);
			$tmp_data = array_values($today_view);
			if ($tmp_data) {
				$today_view = serialize($tmp_data);
				setcookie('today_view',$today_view,time()+86400,'/');
			} else {
				setcookie('today_view','',time()-3600,'/');
			}
			$msg="ok";
		}
		echo $msg;
	}
}

/* End of file goods.php */
/* Location: ./app/controllers/goods.php */