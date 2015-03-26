<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class brand_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	public function index()
	{
		redirect("/admin/brand/catalog");
	}

	public function brand_info()
	{
		$this->load->model('brandmodel');
		
		$this->validation->set_rules('categoryCode', '브랜드','trim|required|max_length[45]|xss_clean');
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

		$query = $this->db->query("select * from fm_brand where category_code = ?",$_POST['categoryCode']);
		$categoryData = $query->row_array();
		
		$updateData = array(
			'title_eng' => $_POST['title_eng'],
			'brand_goods_code' => $_POST['brand_goods_code'],
			'hide' => $_POST['hide'], 
			'hide_in_navigation' => $_POST['hide_in_navigation'], 
			'hide_in_gnb' => $_POST['hide_in_gnb'], 
			'hide_in_category' => $_POST['hide_in_category'], 
			'catalog_allow' => $_POST['catalog_allow'],
			'catalog_allow_sdate' => $_POST['catalog_allow_sdate'],
			'catalog_allow_edate' => $_POST['catalog_allow_edate'],
			'node_banner' => $_POST['node_banner'],
			'node_gnb_banner' => $_POST['node_gnb_banner'],
		);
		
		adjustEditorImages($updateData['node_banner']);	
		adjustEditorImages($updateData['node_gnb_banner']);	
		
		/* 브랜드 이미지1*/
		if($_POST['brand_image1_image']){
			if(!is_dir(ROOTPATH."data/brand")){
				mkdir(ROOTPATH."data/brand");
				chmod(ROOTPATH."data/brand",0777);
			}
			$updateData['brand_image1'] = adjustUploadImage($_POST['brand_image1_image'],'/data/brand/',$_POST['categoryCode'].'_image1_'.time());
			if(!preg_match("/^\//",$_POST['brand_image1_image'])){
				@unlink(ROOTPATH.$categoryData['brand_image1']);
			}			
		}

		/* 브랜드 이미지2*/
		if($_POST['brand_image2_image']){
			if(!is_dir(ROOTPATH."data/brand")){
				mkdir(ROOTPATH."data/brand");
				chmod(ROOTPATH."data/brand",0777);
			}
			$updateData['brand_image2'] = adjustUploadImage($_POST['brand_image2_image'],'/data/brand/',$_POST['categoryCode'].'_image2_'.time());
			if(!preg_match("/^\//",$_POST['brand_image2_image'])){
				@unlink(ROOTPATH.$categoryData['brand_image2']);
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
			
			if(!is_dir(ROOTPATH."data/brand")){
				mkdir(ROOTPATH."data/brand");
				chmod(ROOTPATH."data/brand",0777);
			}
			if($_POST['node_image_normal']){
				$updateData['node_image_normal'] = adjustUploadImage($_POST['node_image_normal'],'/data/brand/',$_POST['categoryCode'].'_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_image_normal'])){
					@unlink(ROOTPATH.$categoryData['node_image_normal']);
				}
			}
			if($_POST['node_image_over']){
				$updateData['node_image_over'] = adjustUploadImage($_POST['node_image_over'],'/data/brand/',$_POST['categoryCode'].'_over_'.time());
				if(!preg_match("/^\//",$_POST['node_image_over'])){
					@unlink(ROOTPATH.$categoryData['node_image_over']);
				}
			}			
		}else{
			$updateData['node_image_normal'] = '';
			$updateData['node_image_over'] = '';
		}
		
		/* 브랜드페이지 노드 꾸미기 */
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
			
			if(!is_dir(ROOTPATH."data/brand")){
				mkdir(ROOTPATH."data/brand");
				chmod(ROOTPATH."data/brand",0777);
			}
			if($_POST['node_catalog_image_normal']){
				$updateData['node_catalog_image_normal'] = adjustUploadImage($_POST['node_catalog_image_normal'],'/data/brand/',$_POST['categoryCode'].'_catalog_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_catalog_image_normal'])){
					@unlink(ROOTPATH.$categoryData['node_catalog_image_normal']);
				}
			}
			if($_POST['node_catalog_image_over']){
				$updateData['node_catalog_image_over'] = adjustUploadImage($_POST['node_catalog_image_over'],'/data/brand/',$_POST['categoryCode'].'_catalog_over_'.time());
				if(!preg_match("/^\//",$_POST['node_catalog_image_over'])){
					@unlink(ROOTPATH.$categoryData['node_catalog_image_over']);
				}
			}			
		}else{
			$updateData['node_catalog_image_normal'] = '';
			$updateData['node_catalog_image_over'] = '';
		}

		/* 브랜드 네비게이션 꾸미기 */
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
			
			if(!is_dir(ROOTPATH."data/brand")){
				mkdir(ROOTPATH."data/brand");
				chmod(ROOTPATH."data/brand",0777);
			}
			if($_POST['node_gnb_image_normal']){
				$updateData['node_gnb_image_normal'] = adjustUploadImage($_POST['node_gnb_image_normal'],'/data/brand/',$_POST['categoryCode'].'_gnb_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_gnb_image_normal'])){
					@unlink(ROOTPATH.$categoryData['node_gnb_image_normal']);
				}
			}
			if($_POST['node_gnb_image_over']){
				$updateData['node_gnb_image_over'] = adjustUploadImage($_POST['node_gnb_image_over'],'/data/brand/',$_POST['categoryCode'].'_gnb_over_'.time());
				if(!preg_match("/^\//",$_POST['node_gnb_image_over'])){
					@unlink(ROOTPATH.$categoryData['node_gnb_image_over']);
				}
			}			
		}else{
			$updateData['node_gnb_image_normal'] = '';
			$updateData['node_gnb_image_over'] = '';
		}

		### BEST
		$updateData['best'] = if_empty($_POST, 'best', 'N');	
		
		$updateData['update_date'] = date('Y-m-d H:i:s');
		
		// 국가, 그룹 추가 by Nexist & BahamuT 
		if($this->input->post("country_seq")) {
			$updateData["country_seq"] = $this->input->post("country_seq");
		}
		
		$classification["seq"] = $this->input->post("classification_seq");
		$classification["txt"] = $this->input->post("classification_txt");
		$updateData["classification"] = serialize($classification);
		$updateData["country_seq"] = $this->input->post("country_seq");
		
		$this->db->where('category_code', $_POST['categoryCode']);
		$this->db->update('fm_brand', $updateData); 

		$query = "delete from `fm_brand_group` where `category_code` = ?";
		$this->db->query( $query,$_POST['categoryCode']);

		if( isset($_POST['memberGroups']) ){
			foreach( $_POST['memberGroups'] as $group ){
				$query = "insert into `fm_brand_group` (`category_code`,`group_seq`, `user_type`,`regist_date`) values (?,?,null,now())";
				$this->db->query( $query,array($_POST['categoryCode'],$group));
			}
		}
		
		if($this->input->post("userType")) {
			$userType = $this->input->post("userType");
			if(is_array($userType)) {
				foreach($userType as $txt) {
					$query = "insert into `fm_brand_group` (`category_code`,`group_seq`, `user_type`,`regist_date`) values (?,null,?,now())";
					$this->db->query( $query,array($_POST['categoryCode'],$txt));
				}
			}
		}

		$this->db->query("delete from fm_brand_info where category_code=?",$_POST['categoryCode']);
		$x = 0;
		foreach($_POST['info_type'] as $i=>$info_type){
			$info_contents = trim($_POST['info_contents_'.$info_type][$x]);
			if($info_contents){
				if($info_type=='text') adjustEditorImages($info_contents);
				if($info_type=='image'){
					if(!is_dir(ROOTPATH."data/brand")){
						mkdir(ROOTPATH."data/brand");
						chmod(ROOTPATH."data/brand",0777);
					}
					$info_contents = adjustUploadImage($info_contents,'/data/brand/',$_POST['categoryCode'].'_info_'.$x.'_'.time());
				}
				$x++;
				$this->db->set("category_code",$_POST['categoryCode']);
				$this->db->set("info_type",$info_type);
				$this->db->set("info_contents",$info_contents);
				$this->db->insert("fm_brand_info");
			}
		}

		// 상품 요약 테이블에 반영
		$this->load->model('goodssummarymodel');
		$this->goodssummarymodel->save_goods_list_summary_brand($_POST['categoryCode']);

		$callback = "parent.document.location.reload();";
		$callback .= "parent.parent.$(\"#tree\").jstree(\"refresh\");";
		openDialogAlert("브랜드가 저장 되었습니다.",400,140,'parent.parent',$callback);
	}
	
	public function brand_design(){
		
		$this->load->model('brandmodel');
		$this->load->model('goodsdisplay');
		
		$params = $_POST;
		$categoryCode = $params['categoryCode'];
		$recommend_display_seq = $params['recommend_display_seq'];
		
		$query = $this->db->query("select * from fm_brand where level>0 and category_code = ?",$categoryCode);
		$categoryData = $query->row_array();
		
		$params['search_use'] = isset($params['use_search']) && $params['use_search'] ? 'y' : 'n';
		$params['navigation_use'] = $params['search_use'];
		$params['category_navigation_use'] = $params['search_use'];
		$params['top_html'] = isset($params['use_top_html']) && $params['use_top_html'] ? $params['top_html'] : '';
		$params['recommend_display_seq'] = isset($params['use_recommend']) && $params['recommend_display_seq'] ? $params['recommend_display_seq'] : '';
		$params['list_use'] = isset($params['use_list']) && $params['use_list'] ? 'y' : 'n';
		
		adjustEditorImages($params['top_html']);
/*
		$sort_diff = count($_POST['sorts'])-max($_POST['sorts']);
		if($sort_diff>0){
			$sort_diff = (int)$sort_diff;
			$this->db->query("update fm_brand_link set sort=sort+{$sort_diff} where category_code = ?",$categoryCode);
		}
		
		if(count($_POST['category_link_seqs'])==count($_POST['sorts']) && $_POST['category_link_seqs']!=$_POST['sorts']){
			foreach($_POST['category_link_seqs'] as $i=>$category_link_seq){
				//$sort = $_POST['sorts'][$i];
				$this->db->where('category_link_seq',$category_link_seq);
				$this->db->update('fm_brand_link',array('sort'=>$i+1));
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

		/* 브랜드 상품 리스트 설정 저장 : 시작 */
		$list_image_decorations = isset($params['list_image_decoration']) ? $params['list_image_decoration'] : array();
		if(isset($list_image_decorations)){
			$params['list_image_decorations'] = $list_image_decorations;
		}

		$list_info_setting = isset($params['list_info_setting']) ? $params['list_info_setting'] : array();
		if(isset($list_info_setting)){
			$params['list_info_settings'] = "[".implode(",",$list_info_setting)."]";
		}
		
		/* 브랜드 상품 상태설정값 */
		$params['list_goods_status'] = implode("|",$params['list_goods_status']);
		
		$params['update_date'] = date('Y-m-d H:i:s');
		$data = filter_keys($params, $this->db->list_fields('fm_brand'));
		$this->db->update('fm_brand', $data, "category_code = {$categoryCode}");
		/* 브랜드 상품 리스트 저장 : 끝 */

		$callback = "parent.document.location.replace('/admin/brand/ifrm_brand_design?categoryCode=".$categoryCode."&page=".$params['page']."&perpage=".$params['perpage']."');";
		//$callback .= "parent.parent.$(\"#tree\").jstree(\"refresh\");";
//		$callback = "parent.document.location.reload();";
		openDialogAlert("브랜드가 저장 되었습니다.",400,140,'parent.parent',$callback);
	}
	
	/* 하위카테고리 동일 적용 버튼 */
	public function childset_category_save(){
		$this->load->model('brandmodel');
		$this->brandmodel->childset_brand($_GET['div'],$_GET['category_code']);
		$callback = "";
		openDialogAlert("하위 카테고리에 동일하게 세팅 되었습니다.",400,140,'parent.parent',"");
	}
	
	/* 카테고리 한꺼번에 꾸미기 */
	public function batch_design_setting(){
		$this->load->model('brandmodel');
		$this->load->helper('design');
		
		$params = $_POST;
		
		switch($params['mode']){
			case "navigation":
				/* 쇼핑몰(최상위) 카테고리에 적용 */
				skin_configuration_save($this->designWorkingSkin,"brand_navigation_type",$_POST['navigation_depth']);
				skin_configuration_save($this->designWorkingSkin,"brand_navigation_count_w",implode("|",$_POST["navigation_{$_POST['navigation_depth']}_w"]));
				skin_configuration_save($this->designWorkingSkin,"brand_navigation_category_count_w",$_POST["naviation_brand_category_w"]);
				
				$callback = "";
				openDialogAlert("세팅된 브랜드 네비게이션 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
			case "design":
				/* 쇼핑몰(최상위) 카테고리에 적용 */
				adjustEditorImages($params['top_html']);
				$data['top_html'] = $params['top_html'];
				$data['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($data, $this->db->list_fields('fm_brand'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(category_code,'')","");
				$this->db->update('fm_brand', $data);
				
				/* 하위카테고리에 동일하게 적용 */
				$this->brandmodel->childset_brand('top_html','');
				
				$callback = "";
				openDialogAlert("세팅된 브랜드 디자인 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
			case "recommend":
				$data['recommend_display_seq'] = $this->brandmodel->set_brand_recommend('',$params);
				$data['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($data, $this->db->list_fields('fm_brand'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(category_code,'')","");
				$this->db->update('fm_brand', $data);
				
				/* 하위카테고리에 동일하게 적용 */
				$this->brandmodel->childset_brand('recommend','');
				
				$callback = "";
				openDialogAlert("세팅된 브랜드 추천상품 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
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
				
				/* 브랜드 상품 상태설정값 */
				$params['list_goods_status'] = implode("|",$params['list_goods_status']);
				
				$params['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($params, $this->db->list_fields('fm_brand'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(category_code,'')","");
				$this->db->update('fm_brand', $data);
				
				/* 하위카테고리에 동일하게 적용 */
				$this->brandmodel->childset_brand('category','');
				
				$callback = "";
				openDialogAlert("세팅된 브랜드 상품 영역이 전체카테고리에 적용되었습니다.",500,140,'parent',"");
			break;
		}
					
	}

	public function chgCategorySort(){

		$this->load->model('brandmodel');

		$params			= $_GET;
		$acttype		= $params['acttype'];
		$categoryCode	= $params['categoryCode'];
		$target			= $params['target'];
		$target_cnt		= count($target);
		$seq			= $params['seq'];
		$bsort			= $params['bsort'];
		$asort			= $params['asort'];

		switch($acttype){
			case 'resetAll':
				$this->brandmodel->reSortAll($categoryCode);
			break;
			case 'gotop':
				$minsort	= $this->brandmodel->getSortValue($categoryCode, 'min');
				$sort		= $minsort;
				$this->brandmodel->rangeUpdateSort($categoryCode, null, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->brandmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gobottom':
				$maxsort	= $this->brandmodel->getSortValue($categoryCode, 'max');
				$sort		= $maxsort - $target_cnt;
				if	($sort < 0){
					$maxsort	= $this->brandmodel->getSortValue($categoryCode, 'cnt');
					$sort		= $maxsort - $target_cnt;
				}
				$this->brandmodel->rangeUpdateSort($categoryCode, $target[($target_cnt-1)]['sortval'], null, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$sort++;
						$this->brandmodel->chgCategorySort($target[$t]['seq'], $sort);
					}
				}
			break;
			case 'goprev1':
				$minsort	= $this->brandmodel->getSortValue($categoryCode, 'min');
				$sort		= $target[0]['sortval'] - 1;
				if	($minsort > $sort)	$sort	= $minsort;
				$this->brandmodel->rangeUpdateSort($categoryCode, $sort-1, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->brandmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gonext1':
				$sort	= $target[0]['sortval'] + 1;
				$this->brandmodel->rangeUpdateSort($categoryCode, $target[($target_cnt-1)]['sortval'], $target[($target_cnt-1)]['sortval'] + 2, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->brandmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'goprev10':
				$minsort	= $this->brandmodel->getSortValue($categoryCode, 'min');
				$sort	= $target[0]['sortval'] - 10;
				if	($minsort > $sort)	$sort	= $minsort;
				$this->brandmodel->rangeUpdateSort($categoryCode, $sort-1, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq']){
						$this->brandmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gonext10':
				$sort		= $target[0]['sortval'] + 10;
				$this->brandmodel->rangeUpdateSort($categoryCode, $target[($target_cnt-1)]['sortval'], $sort + 1, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->brandmodel->chgCategorySort($target[$t]['seq'], $sort);
					}
				}
			break;
			case 'gomove':
				$bsort	= (($params['page'] * $params['perpage']) - $params['perpage']) + $bsort;
				$asort	= (($params['page'] * $params['perpage']) - $params['perpage']) + $asort;

				if	($seq){
					if	($bsort < $asort){
						$this->brandmodel->rangeUpdateSort($categoryCode, $bsort, $asort+1, '-1');
						$this->brandmodel->chgCategorySort($seq, $asort);
					}elseif	($bsort > $asort){
						$this->brandmodel->rangeUpdateSort($categoryCode, $asort-1, $bsort, '+1');
						$this->brandmodel->chgCategorySort($seq, $asort);
					}
				}
			break;
		}

		echo $params['page'];
	}
	
	// 브랜드 그룹(분류) 관련 처리단 14. 3. 28 오후 2:14
	public function classification($mode=null) {
		$this->load->model("brandclassificationmodel");
		if($mode === "list"){
			$params["not_in_seq"] =$this->input->post("not_in_seq");
			$result = $this->brandclassificationmodel->_select_list($params);
			echo json_encode($result);
			return true;
			exit;
		} else if ($mode === "insert") {
			$data["title"] = $this->input->post("title");
			$data["regist_date"] = date("Y-m-d H:i:s");
			$seq = $this->brandclassificationmodel->_insert($data);
			
			$result["seq"] = $seq;
			$result["title"] = $data["title"];
			echo json_encode($result);
			return true;
			exit;
		} else if ($mode === "delete") {
			$where["seq"] = $this->input->post("seq");
			$this->brandclassificationmodel->_delete($where);
			$result = array("result" => true);
			echo json_encode($result);
			return true;
			exit;
		} else {
			return false;
		}
	}
	
	// 브랜드 국가 업로드 단
	public function country($mode=null) {
		if ($mode === "insert") {
			$config["allowed_types"] = "jpeg|jpg|gif|png";
			$config["upload_path"] = "./data/brand_country";
			$config["encrypt_name"] = TRUE;
			$this->load->library("upload", $config);
			$this->upload->initialize($config);
			if($this->upload->do_upload("flagimg")) {
				$file_info = $this->upload->data();
				
				$this->load->model("brandcountrymodel");
				$result["flagimg"] = $file_info["file_name"];
				$result["name"] = $this->input->post("name");
				$result["seq"] = $this->brandcountrymodel->_insert($result);
				$result["result"] = true;
				//echo js("parent.setCountry(\"".  addslashes(json_encode($result))."\")");
				$where = array("seq"=>$result["seq"]);
				$country = $this->brandcountrymodel->_select_row($where);
				echo js("parent.setCountryView(\"".  addslashes(json_encode($country))."\")");
				return TRUE;
				exit;
			} else {
				openDialogAlert($this->upload->display_errors("",""),500,140,'parent',"");
				return FALSE;
				exit;
			}
		}
		// @todo 수정
		else if ($mode === "modify") {
			$this->load->model("brandcountrymodel");
			$result["name"] = $this->input->post("name");
			$result["seq"] = $this->input->post("country_seq");

			$where = array("seq"=>$this->input->post("country_seq"));
			$country = $this->brandcountrymodel->_select_row($where);  

			$config["allowed_types"] = "jpeg|jpg|gif|png";
			$config["upload_path"] = "./data/brand_country";
			$config["encrypt_name"] = TRUE;
			$this->load->library("upload", $config);
			$this->upload->initialize($config);
			if($this->upload->do_upload("flagimg")) {
				$file_info = $this->upload->data();
				$result["flagimg"] = $file_info["file_name"];
			}

			$result["result"] = $this->brandcountrymodel->_update($result,$where) ? true : false; 
			$country = $this->brandcountrymodel->_select_row($where);
			echo js("parent.setCountryView(\"".  addslashes(json_encode($country))."\")");
			return TRUE;
			exit;

		}
		// @todo 이미지삭제
		else if ($mode === "imgdelete") {
			
			/* 브랜드 국가 관련 */
			$this->load->model("brandcountrymodel");
			$upload_path = ROOTPATH."/data/brand_country/";
			$where = array("seq"=>$this->input->post("country_seq"));
			$country = $this->brandcountrymodel->_select_row($where);   
			if($country) {
				if( is_file($upload_path.$country["flagimg"]) ) {
					unlink($upload_path.$country["flagimg"]);
				}
				$where = array("seq"=>$this->input->post("country_seq"));
				$result["flagimg"] = "";
				$result["result"] = $this->brandcountrymodel->_update($result,$where) ? true : false; 
				$country = $this->brandcountrymodel->_select_row($where);   
				$country["result"] = true;
				echo json_encode($country); 
				exit;
			}else{
				echo json_encode(array("result"=>false,"msg"=>'이미지삭제가 실패되었습니다.'));
				exit; 
			}		
		}
		// @todo 삭제
		else if ($mode === "delete") {}
		else {
			
		}
	}
	// 가나다순 일괄 정렬, ABC정렬 
	public function batch_sort($type="title") {
		$this->load->model("brandmodel");
		$target = $this->brandmodel->get_sort_list($type);
		
		foreach($target as $idx=>$data) {
			$updateData["position"] = $idx+1;
			$this->db->where('id', $data['id']);
			$this->db->update('fm_brand', $updateData); 
			unset($updateData);
		}
		echo json_encode(array("result"=>true));
		return true;
	}

}

/* End of file brand.php */
/* Location: ./app/controllers/admin/brand.php */