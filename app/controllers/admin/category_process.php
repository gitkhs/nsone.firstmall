<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class category_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	public function index()
	{
		redirect("/admin/category/catalog");
	}

	public function category_info()
	{
		
		$this->validation->set_rules('categoryCode', '카테고리','trim|required|max_length[45]|xss_clean');
		if($_POST['catalog_allow']=='period'){
			$this->validation->set_rules('catalog_allow_sdate', '접속 허용 기간','trim|required|max_length[10]|xss_clean');
			$this->validation->set_rules('catalog_allow_edate', '접속 허용 기간','trim|required|max_length[10]|xss_clean');
			
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		
		$query = $this->db->query("select * from fm_category where category_code = ?",$_POST['categoryCode']);
		$categoryData = $query->row_array();

		$plan_main_display = 'n';
		if($_POST['plan_main_display'] == "y"){
			$plan_main_display = 'y';
		}

		// 태그를 제외한 데이터 검증 :: 2015-01-22 lwh
		if(strlen(trim(strip_tags($_POST['node_banner']))) === 0){
			$_POST['node_banner'] = '';
		}
		
		$updateData = array(
			'hide' => $_POST['hide'],
			'category_goods_code' => $_POST['category_goods_code'],
			'hide_in_navigation' => $_POST['hide_in_navigation'],
			'hide_in_gnb' => $_POST['hide_in_gnb'], 
			'hide_in_brand' => $_POST['hide_in_brand'], 
			'catalog_allow' => $_POST['catalog_allow'],
			'catalog_allow_sdate' => $_POST['catalog_allow_sdate'],
			'catalog_allow_edate' => $_POST['catalog_allow_edate'],
			'plan' => $_POST['plan'],
			'plan_title' => $_POST['plan_title'],
			'plan_desc' => $_POST['plan_desc'],
			'plan_main_title' => $_POST['plan_main_title'],
			'plan_main_desc' => $_POST['plan_main_desc'],
			'plan_main_display' => $plan_main_display,
			'node_banner' => $_POST['node_banner'],
			'node_gnb_banner' => $_POST['node_gnb_banner'],
		);
		
		adjustEditorImages($updateData['node_banner']);	
		adjustEditorImages($updateData['node_gnb_banner']);	
		
		if($_POST['banner_del'] == "y"){
			$updateData['category_plan_banner'] = "";
			@unlink(ROOTPATH.$categoryData['category_plan_banner']);
		}

		/* 배너 이미지 등록 */
		if($_POST['category_plan_banner']){
			$updateData['category_plan_banner'] = adjustUploadImage($_POST['category_plan_banner'],'/data/category/',$_POST['categoryCode'].'_plan_'.time());
			if(!preg_match("/^\//",$_POST['category_plan_banner'])){
				@unlink(ROOTPATH.$categoryData['category_plan_banner']);
			}
		}			

		/* 메인 배너 이미지 등록 */
		if($_POST['category_plan_main']){
			$updateData['category_plan_main'] = adjustUploadImage($_POST['category_plan_main'],'/data/category/',$_POST['categoryCode'].'_plan_main_'.time());
			if(!preg_match("/^\//",$_POST['category_plan_main'])){
				@unlink(ROOTPATH.$categoryData['category_plan_main']);
			}
		}			
		

		/* 노드 꾸미기 */
		$updateData['node_type'] = '';
		$updateData['node_text_normal'] = '';
		$updateData['node_text_over'] = ''; 
		
		if($_POST['node_type'] == 'text'){
			$updateData['node_type'] = 'text';
			$updateData['node_text_normal'] = $_POST['node_text_normal'];
			$updateData['node_text_over'] = $_POST['node_text_over']; 
		}

		if($_POST['node_type'] == 'image' ){
			$updateData['node_type'] = 'image';
			
			if(!is_dir(ROOTPATH."data/category")){
				mkdir(ROOTPATH."data/category");
				chmod(ROOTPATH."data/category",0777);
			}
			if($_POST['node_image_normal']){
				$updateData['node_image_normal'] = adjustUploadImage($_POST['node_image_normal'],'/data/category/',$_POST['categoryCode'].'_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_image_normal'])){
					@unlink(ROOTPATH.$categoryData['node_image_normal']);
				}
			}
			if($_POST['node_image_over']){
				$updateData['node_image_over'] = adjustUploadImage($_POST['node_image_over'],'/data/category/',$_POST['categoryCode'].'_over_'.time());
				if(!preg_match("/^\//",$_POST['node_image_over'])){
					@unlink(ROOTPATH.$categoryData['node_image_over']);
				}
			}			
		}else{
			$updateData['node_image_normal'] = '';
			$updateData['node_image_over'] = '';
		}
		
		/* 카테고리페이지 노드 꾸미기 */
		$updateData['node_catalog_type'] = '';
		$updateData['node_catalog_text_normal'] = '';
		$updateData['node_catalog_text_over'] = ''; 
		
		if($_POST['node_catalog_type'] == 'text'){
			$updateData['node_catalog_type'] = 'text';
			$updateData['node_catalog_text_normal'] = $_POST['node_catalog_text_normal'];
			$updateData['node_catalog_text_over'] = $_POST['node_catalog_text_over']; 
		}

		if($_POST['node_catalog_type'] == 'image' ){
			$updateData['node_catalog_type'] = 'image';
			
			if(!is_dir(ROOTPATH."data/category")){
				mkdir(ROOTPATH."data/category");
				chmod(ROOTPATH."data/category",0777);
			}
			if($_POST['node_catalog_image_normal']){
				$updateData['node_catalog_image_normal'] = adjustUploadImage($_POST['node_catalog_image_normal'],'/data/category/',$_POST['categoryCode'].'_catalog_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_catalog_image_normal'])){
					@unlink(ROOTPATH.$categoryData['node_catalog_image_normal']);
				}
			}
			if($_POST['node_catalog_image_over']){
				$updateData['node_catalog_image_over'] = adjustUploadImage($_POST['node_catalog_image_over'],'/data/category/',$_POST['categoryCode'].'_catalog_over_'.time());
				if(!preg_match("/^\//",$_POST['node_catalog_image_over'])){
					@unlink(ROOTPATH.$categoryData['node_catalog_image_over']);
				}
			}			
		}else{
			$updateData['node_catalog_image_normal'] = '';
			$updateData['node_catalog_image_over'] = '';
		}

		/* 전체 카테고리 네비게이션 꾸미기 */
		$updateData['node_gnb_type'] = '';
		$updateData['node_gnb_text_normal'] = '';
		$updateData['node_gnb_text_over'] = ''; 
		
		if($_POST['node_gnb_type'] == 'text'){
			$updateData['node_gnb_type'] = 'text';
			$updateData['node_gnb_text_normal'] = $_POST['node_gnb_text_normal'];
			$updateData['node_gnb_text_over'] = $_POST['node_gnb_text_over']; 
		}

		if($_POST['node_gnb_type'] == 'image' ){
			$updateData['node_gnb_type'] = 'image';
			
			if(!is_dir(ROOTPATH."data/category")){
				mkdir(ROOTPATH."data/category");
				chmod(ROOTPATH."data/category",0777);
			}
			if($_POST['node_gnb_image_normal']){
				$updateData['node_gnb_image_normal'] = adjustUploadImage($_POST['node_gnb_image_normal'],'/data/category/',$_POST['categoryCode'].'_gnb_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_gnb_image_normal'])){
					@unlink(ROOTPATH.$categoryData['node_gnb_image_normal']);
				}
			}
			if($_POST['node_gnb_image_over']){
				$updateData['node_gnb_image_over'] = adjustUploadImage($_POST['node_gnb_image_over'],'/data/category/',$_POST['categoryCode'].'_gnb_over_'.time());
				if(!preg_match("/^\//",$_POST['node_gnb_image_over'])){
					@unlink(ROOTPATH.$categoryData['node_gnb_image_over']);
				}
			}			
		}else{
			$updateData['node_gnb_image_normal'] = '';
			$updateData['node_gnb_image_over'] = '';
		}
		
		$updateData['update_date'] = date('Y-m-d H:i:s');

		$this->db->where('category_code', $_POST['categoryCode']);
		$this->db->update('fm_category', $updateData); 

		$query = "delete from `fm_category_group` where `category_code` = ?";
		$this->db->query( $query,$_POST['categoryCode']);

		if( isset($_POST['memberGroups']) ){
			foreach( $_POST['memberGroups'] as $group ){
				$query = "insert into `fm_category_group` (`category_code`,`group_seq`, `user_type`,`regist_date`) values (?,?,null,now())";
				$this->db->query( $query,array($_POST['categoryCode'],$group));
			}
		}
		
		if($this->input->post("userType")) {
			$userType = $this->input->post("userType");
			if(is_array($userType)) {
				foreach($userType as $txt) {
					$query = "insert into `fm_category_group` (`category_code`,`group_seq`, `user_type`,`regist_date`) values (?,null,?,now())";
					$this->db->query( $query,array($_POST['categoryCode'],$txt));
				}
			}
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("카테고리가 저장 되었습니다.",400,140,'parent.parent',$callback);
	}

	public function catalog_design(){

		$this->load->model('categorymodel');
		$this->load->model('goodsdisplay');
		
		$params = $_POST;
		$categoryCode = $params['categoryCode'];
		$recommend_display_seq = $params['recommend_display_seq'];
		
		$query = $this->db->query("select * from fm_category where level>0 and category_code = ?",$categoryCode);
		$categoryData = $query->row_array();
		
		$params['search_use'] = isset($params['use_search']) && $params['use_search'] ? 'y' : 'n';
		$params['navigation_use'] = $params['search_use'];
		$params['brand_navigation_use'] = $params['search_use'];
		$params['top_html'] = isset($params['use_top_html']) && $params['use_top_html'] ? $params['top_html'] : '';
		$params['recommend_display_seq'] = isset($params['use_recommend']) && $params['recommend_display_seq'] ? $params['recommend_display_seq'] : '';
		$params['list_use'] = isset($params['use_list']) && $params['use_list'] ? 'y' : 'n';
		
		adjustEditorImages($params['top_html']);		
/*
		$sort_diff = count($_POST['sorts'])-max($_POST['sorts']);
		if($sort_diff>0){
			$sort_diff = (int)$sort_diff;
			$this->db->query("update fm_category_link set sort=sort+{$sort_diff} where category_code = ?",$categoryCode);
		}
				
		if(count($_POST['category_link_seqs'])==count($_POST['sorts']) && $_POST['category_link_seqs']!=$_POST['sorts']){
			foreach($_POST['category_link_seqs'] as $i=>$category_link_seq){
				//$sort = $_POST['sorts'][$i];
				$this->db->where('category_link_seq',$category_link_seq);
				$this->db->update('fm_category_link',array('sort'=>$i+1));
			}
		}
*/
		/* 추천상품 설정 저장 : 시작 */
		if(!isset($params['use_recommend'])){
			/* 추천상품 사용안함일경우 추천상품디스플레이삭제 */
			$this->db->query("delete from fm_design_display where display_seq=?",$recommend_display_seq);
			$this->db->query("delete from fm_design_display_tab where display_seq=?",$recommend_display_seq);
			$this->db->query("delete from fm_design_display_tab_item where display_seq=?",$recommend_display_seq);
		}
		/* 추천상품 설정 저장 : 끝 */

		/* 카테고리 상품 리스트 설정 저장 : 시작 */
		$list_image_decorations = isset($params['list_image_decoration']) ? $params['list_image_decoration'] : array();
		if(isset($list_image_decorations)){
			$params['list_image_decorations'] = $list_image_decorations;
		}
		
		$list_info_setting = isset($params['list_info_setting']) ? $params['list_info_setting'] : array();
		if(isset($list_info_setting)){
			$params['list_info_settings'] = "[".implode(",",$list_info_setting)."]";
		}
		
		/* 카테고리 상품 상태설정값 */
		$params['list_goods_status'] = implode("|",$params['list_goods_status']);
		
		$params['update_date'] = date('Y-m-d H:i:s');
		
		$data = filter_keys($params, $this->db->list_fields('fm_category'));
		$this->db->update('fm_category', $data, "category_code = {$categoryCode}");
		/* 카테고리 상품 리스트 저장 : 끝 */
		
		$callback = "parent.document.location.reload();";
		openDialogAlert("카테고리가 저장 되었습니다.",400,140,'parent.parent',$callback);
	}

	/* 하위카테고리 동일 적용 버튼 */
	public function childset_category_save(){
		$this->load->model('categorymodel');
		$this->categorymodel->childset_category($_GET['div'],$_GET['category_code']);
		$callback = "";
		openDialogAlert("하위 카테고리에 동일하게 세팅 되었습니다.",400,140,'parent.parent',"");
	}

	/* 카테고리 한꺼번에 꾸미기 */
	public function batch_design_setting(){
		$this->load->model('categorymodel');
		$this->load->helper('design');

		$params = $_POST;

		switch($params['mode']){
			case "navigation":
				/* 쇼핑몰(최상위) 카테고리에 적용 */
				skin_configuration_save($this->designWorkingSkin,"category_navigation_type",$_POST['navigation_depth']);
				skin_configuration_save($this->designWorkingSkin,"category_navigation_count_w",implode("|",$_POST["navigation_{$_POST['navigation_depth']}_w"]));
				skin_configuration_save($this->designWorkingSkin,"category_navigation_brand_count_w",$_POST["naviation_category_brand_w"]);
				
				$callback = "";
				openDialogAlert("세팅된 카테고리 네비게이션 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
			case "design":
				/* 쇼핑몰(최상위) 카테고리에 적용 */
				adjustEditorImages($params['top_html']);
				$data['top_html'] = $params['top_html'];
				$data['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($data, $this->db->list_fields('fm_category'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(category_code,'')","");
				$this->db->update('fm_category', $data);

				/* 하위카테고리에 동일하게 적용 */
				$this->categorymodel->childset_category('top_html','');

				$callback = "";
				openDialogAlert("세팅된 카테고리 디자인 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
			case "recommend":
				$data['recommend_display_seq'] = $this->categorymodel->set_category_recommend('',$params);
				$data['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($data, $this->db->list_fields('fm_category'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(category_code,'')","");
				$this->db->update('fm_category', $data);

				/* 하위카테고리에 동일하게 적용 */
				$this->categorymodel->childset_category('recommend','');

				$callback = "";
				openDialogAlert("세팅된 카테고리 추천상품 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
			case "category":
				$list_image_decorations = isset($params['list_image_decoration']) ? $params['list_image_decoration'] : array();
				if(isset($list_image_decorations)){
					$params['list_image_decorations'] = $list_image_decorations;
				}

				$list_info_setting = isset($params['list_info_setting']) ? $params['list_info_setting'] : array();
				if(isset($list_info_setting)){
					$params['list_info_settings'] = "[".implode(",",$list_info_setting)."]";
				}

				/* 카테고리 상품 상태설정값 */
				$params['list_goods_status'] = implode("|",$params['list_goods_status']);

				$params['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($params, $this->db->list_fields('fm_category'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(category_code,'')","");
				$this->db->update('fm_category', $data);

				/* 하위카테고리에 동일하게 적용 */
				$this->categorymodel->childset_category('category','');

				$callback = "";
				openDialogAlert("세팅된 카테고리 상품 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
		}

	}

	public function chgCategorySort(){

		$this->load->model('categorymodel');

		$params			= $_GET;
		$acttype		= $params['acttype'];
		$categoryCode	= $params['categoryCode'];
		$target			= $params['target'];
		$target_cnt		= count($target);
		$seq			= $params['seq'];
		$bsort			= $params['bsort'];
		$asort			= $params['asort'];

		// 정렬이 비정상일 경우 선 재정렬
/*
		if ($this->categorymodel->chkDupleSort($categoryCode)){
			$this->categorymodel->reSortAll($categoryCode);
		}
*/
		switch($acttype){
			case 'resetAll':
				$this->categorymodel->reSortAll($categoryCode);
			break;
			case 'gotop':
				$minsort	= $this->categorymodel->getSortValue($categoryCode, 'min');
				$sort		= $minsort;
				$this->categorymodel->rangeUpdateSort($categoryCode, null, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->categorymodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gobottom':
				$maxsort	= $this->categorymodel->getSortValue($categoryCode, 'max');
				$sort		= $maxsort - $target_cnt;
				if	($sort < 0){
					$maxsort	= $this->categorymodel->getSortValue($categoryCode, 'cnt');
					$sort		= $maxsort - $target_cnt;
				}
				$this->categorymodel->rangeUpdateSort($categoryCode, $target[($target_cnt-1)]['sortval'], null, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$sort++;
						$this->categorymodel->chgCategorySort($target[$t]['seq'], $sort);
					}
				}
			break;
			case 'goprev1':
				$minsort	= $this->categorymodel->getSortValue($categoryCode, 'min');
				$sort		= $target[0]['sortval'] - 1;
				if	($minsort > $sort)	$sort	= $minsort;
				$this->categorymodel->rangeUpdateSort($categoryCode, $sort-1, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->categorymodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gonext1':
				$sort	= $target[0]['sortval'] + 1;
				$this->categorymodel->rangeUpdateSort($categoryCode, $target[($target_cnt-1)]['sortval'], $target[($target_cnt-1)]['sortval'] + 2, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->categorymodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'goprev10':
				$minsort	= $this->categorymodel->getSortValue($categoryCode, 'min');
				$sort	= $target[0]['sortval'] - 10;
				if	($minsort > $sort)	$sort	= $minsort;
				$this->categorymodel->rangeUpdateSort($categoryCode, $sort-1, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq']){
						$this->categorymodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gonext10':
				$sort		= $target[0]['sortval'] + 10;
				$this->categorymodel->rangeUpdateSort($categoryCode, $target[($target_cnt-1)]['sortval'], $sort + 1, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->categorymodel->chgCategorySort($target[$t]['seq'], $sort);
					}
				}
			break;
			case 'gomove':
				$bsort	= (($params['page'] * $params['perpage']) - $params['perpage']) + $bsort;
				$asort	= (($params['page'] * $params['perpage']) - $params['perpage']) + $asort;

				if	($seq){
					if	($bsort < $asort){
						$this->categorymodel->rangeUpdateSort($categoryCode, $bsort, $asort+1, '-1');
						$this->categorymodel->chgCategorySort($seq, $asort);
					}elseif	($bsort > $asort){
						$this->categorymodel->rangeUpdateSort($categoryCode, $asort-1, $bsort, '+1');
						$this->categorymodel->chgCategorySort($seq, $asort);
					}
				}
			break;
		}

		echo $params['page'];
	}
}

/* End of file category.php */
/* Location: ./app/controllers/admin/category.php */