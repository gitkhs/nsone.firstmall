<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class location_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	public function index()
	{
		redirect("/admin/location/catalog");
	}

	public function location_info()
	{
		$this->load->model('locationmodel');
		
		$this->validation->set_rules('locationCode', '지역','trim|required|max_length[45]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$query = $this->db->query("select * from fm_location where location_code = ?",$_POST['locationCode']);
		$locationData = $query->row_array();

		$updateData = array(
			'hide' => $_POST['hide'], 
			'hide_in_navigation' => $_POST['hide_in_navigation'], 
			'hide_in_gnb' => $_POST['hide_in_gnb'], 
			'node_banner' => $_POST['node_banner'],
			'node_gnb_banner' => $_POST['node_gnb_banner'],
		);
		
		adjustEditorImages($updateData['node_banner']);	
		adjustEditorImages($updateData['node_gnb_banner']);	
		
		/* 지역 이미지1*/
		if($_POST['location_image1_image']){
			if(!is_dir(ROOTPATH."data/location")){
				mkdir(ROOTPATH."data/location");
				chmod(ROOTPATH."data/location",0777);
			}
			$updateData['location_image1'] = adjustUploadImage($_POST['location_image1_image'],'/data/location/',$_POST['locationCode'].'_image1_'.time());
			if(!preg_match("/^\//",$_POST['location_image1_image'])){
				@unlink(ROOTPATH.$locationData['location_image1']);
			}			
		}

		/* 지역 이미지2*/
		if($_POST['location_image2_image']){
			if(!is_dir(ROOTPATH."data/location")){
				mkdir(ROOTPATH."data/location");
				chmod(ROOTPATH."data/location",0777);
			}
			$updateData['location_image2'] = adjustUploadImage($_POST['location_image2_image'],'/data/location/',$_POST['locationCode'].'_image2_'.time());
			if(!preg_match("/^\//",$_POST['location_image2_image'])){
				@unlink(ROOTPATH.$locationData['location_image2']);
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
			
			if(!is_dir(ROOTPATH."data/location")){
				mkdir(ROOTPATH."data/location");
				chmod(ROOTPATH."data/location",0777);
			}
			if($_POST['node_image_normal']){
				$updateData['node_image_normal'] = adjustUploadImage($_POST['node_image_normal'],'/data/location/',$_POST['locationCode'].'_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_image_normal'])){
					@unlink(ROOTPATH.$locationData['node_image_normal']);
				}
			}
			if($_POST['node_image_over']){
				$updateData['node_image_over'] = adjustUploadImage($_POST['node_image_over'],'/data/location/',$_POST['locationCode'].'_over_'.time());
				if(!preg_match("/^\//",$_POST['node_image_over'])){
					@unlink(ROOTPATH.$locationData['node_image_over']);
				}
			}			
		}else{
			$updateData['node_image_normal'] = '';
			$updateData['node_image_over'] = '';
		}
		
		/* 지역페이지 노드 꾸미기 */
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
			
			if(!is_dir(ROOTPATH."data/location")){
				mkdir(ROOTPATH."data/location");
				chmod(ROOTPATH."data/location",0777);
			}
			if($_POST['node_catalog_image_normal']){
				$updateData['node_catalog_image_normal'] = adjustUploadImage($_POST['node_catalog_image_normal'],'/data/location/',$_POST['locationCode'].'_catalog_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_catalog_image_normal'])){
					@unlink(ROOTPATH.$locationData['node_catalog_image_normal']);
				}
			}
			if($_POST['node_catalog_image_over']){
				$updateData['node_catalog_image_over'] = adjustUploadImage($_POST['node_catalog_image_over'],'/data/location/',$_POST['locationCode'].'_catalog_over_'.time());
				if(!preg_match("/^\//",$_POST['node_catalog_image_over'])){
					@unlink(ROOTPATH.$locationData['node_catalog_image_over']);
				}
			}			
		}else{
			$updateData['node_catalog_image_normal'] = '';
			$updateData['node_catalog_image_over'] = '';
		}

		/* 지역 네비게이션 꾸미기 */
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
			
			if(!is_dir(ROOTPATH."data/location")){
				mkdir(ROOTPATH."data/location");
				chmod(ROOTPATH."data/location",0777);
			}
			if($_POST['node_gnb_image_normal']){
				$updateData['node_gnb_image_normal'] = adjustUploadImage($_POST['node_gnb_image_normal'],'/data/location/',$_POST['locationCode'].'_gnb_normal_'.time());
				if(!preg_match("/^\//",$_POST['node_gnb_image_normal'])){
					@unlink(ROOTPATH.$locationData['node_gnb_image_normal']);
				}
			}
			if($_POST['node_gnb_image_over']){
				$updateData['node_gnb_image_over'] = adjustUploadImage($_POST['node_gnb_image_over'],'/data/location/',$_POST['locationCode'].'_gnb_over_'.time());
				if(!preg_match("/^\//",$_POST['node_gnb_image_over'])){
					@unlink(ROOTPATH.$locationData['node_gnb_image_over']);
				}
			}			
		}else{
			$updateData['node_gnb_image_normal'] = '';
			$updateData['node_gnb_image_over'] = '';
		}

		$updateData['update_date'] = date('Y-m-d H:i:s');
		
		$this->db->where('location_code', $_POST['locationCode']);
		$this->db->update('fm_location', $updateData); 

		$callback = "parent.document.location.reload();";
		openDialogAlert("지역이 저장 되었습니다.",400,140,'parent.parent',$callback);
	}
	
	public function location_design(){
		
		$this->load->model('locationmodel');
		$this->load->model('goodsdisplay');
		
		$params = $_POST;
		$locationCode = $params['locationCode'];
		$recommend_display_seq = $params['recommend_display_seq'];
		
		$query = $this->db->query("select * from fm_location where level>0 and location_code = ?",$locationCode);
		$locationData = $query->row_array();
		
		$params['search_use'] = isset($params['use_search']) && $params['use_search'] ? 'y' : 'n';
		$params['top_html'] = isset($params['use_top_html']) && $params['use_top_html'] ? $params['top_html'] : '';
		$params['recommend_display_seq'] = isset($params['use_recommend']) && $params['recommend_display_seq'] ? $params['recommend_display_seq'] : '';
		$params['list_use'] = isset($params['use_list']) && $params['use_list'] ? 'y' : 'n';
		
		adjustEditorImages($params['top_html']);
/*
		$sort_diff = count($_POST['sorts'])-max($_POST['sorts']);
		if($sort_diff>0){
			$sort_diff = (int)$sort_diff;
			$this->db->query("update fm_location_link set sort=sort+{$sort_diff} where location_code = ?",$locationCode);
		}
		
		if(count($_POST['category_link_seqs'])==count($_POST['sorts']) && $_POST['category_link_seqs']!=$_POST['sorts']){
			foreach($_POST['category_link_seqs'] as $i=>$category_link_seq){
				//$sort = $_POST['sorts'][$i];
				$this->db->where('category_link_seq',$category_link_seq);
				$this->db->update('fm_location_link',array('sort'=>$i+1));
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

		/* 지역 상품 리스트 설정 저장 : 시작 */
		$list_image_decorations = isset($params['list_image_decoration']) ? $params['list_image_decoration'] : array();
		if(isset($list_image_decorations)){
			$params['list_image_decorations'] = $list_image_decorations;
		}

		$list_info_setting = isset($params['list_info_setting']) ? $params['list_info_setting'] : array();
		if(isset($list_info_setting)){
			$params['list_info_settings'] = "[".implode(",",$list_info_setting)."]";
		}
		
		/* 지역 상품 상태설정값 */
		$params['list_goods_status'] = implode("|",$params['list_goods_status']);
		
		$params['update_date'] = date('Y-m-d H:i:s');
		$data = filter_keys($params, $this->db->list_fields('fm_location'));
		$this->db->update('fm_location', $data, "location_code = {$locationCode}");
		/* 지역 상품 리스트 저장 : 끝 */

		$callback = "parent.document.location.replace('/admin/location/ifrm_location_design?locationCode=".$locationCode."&page=".$params['page']."&perpage=".$params['perpage']."');";
//		$callback = "parent.document.location.reload();";
		openDialogAlert("지역이 저장 되었습니다.",400,140,'parent.parent',$callback);
	}
	
	/* 하위카테고리 동일 적용 버튼 */
	public function childset_location_save(){
		$this->load->model('locationmodel');
		$this->locationmodel->childset_location($_GET['div'],$_GET['location_code']);
		$callback = "";
		openDialogAlert("하위 카테고리에 동일하게 세팅 되었습니다.",400,140,'parent.parent',"");
	}
	
	/* 카테고리 한꺼번에 꾸미기 */
	public function batch_design_setting(){
		$this->load->model('locationmodel');
		$this->load->helper('design');
		
		$params = $_POST;
		
		switch($params['mode']){
			case "navigation":
				/* 쇼핑몰(최상위) 카테고리에 적용 */
				skin_configuration_save($this->designWorkingSkin,"location_navigation_type",$_POST['navigation_depth']);
				skin_configuration_save($this->designWorkingSkin,"location_navigation_count_w",implode("|",$_POST["navigation_{$_POST['navigation_depth']}_w"]));
				skin_configuration_save($this->designWorkingSkin,"location_navigation_category_count_w",$_POST["naviation_location_category_w"]);
				
				$callback = "";
				openDialogAlert("세팅된 지역 네비게이션 영역이 전체 지역에 적용되었습니다.",500,140,'parent',"");
			break;
			case "design":
				/* 쇼핑몰(최상위) 카테고리에 적용 */
				adjustEditorImages($params['top_html']);
				$data['top_html'] = $params['top_html'];
				$data['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($data, $this->db->list_fields('fm_location'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(location_code,'')","");
				$this->db->update('fm_location', $data);
				
				/* 하위카테고리에 동일하게 적용 */
				$this->locationmodel->childset_location('top_html','');
				
				$callback = "";
				openDialogAlert("세팅된 지역 디자인 영역이 전체 지역에 적용되었습니다.",500,140,'parent',"");
			break;
			case "recommend":
				$data['recommend_display_seq'] = $this->locationmodel->set_location_recommend('',$params);
				$data['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($data, $this->db->list_fields('fm_location'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(location_code,'')","");
				$this->db->update('fm_location', $data);
				
				/* 하위카테고리에 동일하게 적용 */
				$this->locationmodel->childset_location('recommend','');
				
				$callback = "";
				openDialogAlert("세팅된 지역 추천상품 영역이 전체 지역에 적용되었습니다.",500,140,'parent',"");
			break;
			case "location":
				$list_image_decorations = isset($params['list_image_decoration']) ? $params['list_image_decoration'] : array();
				if(isset($list_image_decorations)){
					$params['list_image_decorations'] = $list_image_decorations;
				}
				
				$list_info_setting = isset($params['list_info_setting']) ? $params['list_info_setting'] : array();
				if(isset($list_info_setting)){
					$params['list_info_settings'] = "[".implode(",",$list_info_setting)."]";
				}
				
				/* 지역 상품 상태설정값 */
				$params['list_goods_status'] = implode("|",$params['list_goods_status']);
				
				$params['location_code'] = '';
				$params['update_date'] = date('Y-m-d H:i:s');
				$data = filter_keys($params, $this->db->list_fields('fm_location'));
				$this->db->where("level >",0);
				$this->db->where("ifnull(location_code,'')","");
				$this->db->update('fm_location', $data);
				
				/* 하위카테고리에 동일하게 적용 */
				$this->locationmodel->childset_location('location','');
				
				$callback = "";
				openDialogAlert("세팅된 지역 상품 영역이 전체 지역에 적용되었습니다.",500,140,'parent',"");
			break;
		}
					
	}

	public function chgCategorySort(){

		$this->load->model('locationmodel');

		$params			= $_GET;
		$acttype		= $params['acttype'];
		$locationCode	= $params['locationCode'];
		$target			= $params['target'];
		$target_cnt		= count($target);
		$seq			= $params['seq'];
		$bsort			= $params['bsort'];
		$asort			= $params['asort'];

		switch($acttype){
			case 'resetAll':
				$this->locationmodel->reSortAll($locationCode);
			break;
			case 'gotop':
				$minsort	= $this->locationmodel->getSortValue($locationCode, 'min');
				$sort		= $minsort;
				$this->locationmodel->rangeUpdateSort($locationCode, null, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->locationmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gobottom':
				$maxsort	= $this->locationmodel->getSortValue($locationCode, 'max');
				$sort		= $maxsort - $target_cnt;
				if	($sort < 0){
					$maxsort	= $this->locationmodel->getSortValue($locationCode, 'cnt');
					$sort		= $maxsort - $target_cnt;
				}
				$this->locationmodel->rangeUpdateSort($locationCode, $target[($target_cnt-1)]['sortval'], null, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$sort++;
						$this->locationmodel->chgCategorySort($target[$t]['seq'], $sort);
					}
				}
			break;
			case 'goprev1':
				$minsort	= $this->locationmodel->getSortValue($locationCode, 'min');
				$sort		= $target[0]['sortval'] - 1;
				if	($minsort > $sort)	$sort	= $minsort;
				$this->locationmodel->rangeUpdateSort($locationCode, $sort-1, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->locationmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gonext1':
				$sort	= $target[0]['sortval'] + 1;
				$this->locationmodel->rangeUpdateSort($locationCode, $target[($target_cnt-1)]['sortval'], $target[($target_cnt-1)]['sortval'] + 2, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->locationmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'goprev10':
				$minsort	= $this->locationmodel->getSortValue($locationCode, 'min');
				$sort		= $target[0]['sortval'] - 10;
				if	($minsort > $sort)	$sort	= $minsort;
				$this->locationmodel->rangeUpdateSort($locationCode, $sort-1, $target[0]['sortval'], '+'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq']){
						$this->locationmodel->chgCategorySort($target[$t]['seq'], $sort);
						$sort++;
					}
				}
			break;
			case 'gonext10':
				$sort		= $target[0]['sortval'] + 10;
				$this->locationmodel->rangeUpdateSort($locationCode, $target[($target_cnt-1)]['sortval'], $sort + 1, '-'.$target_cnt);
				for ($t = 0; $t < $target_cnt; $t++){
					if	($target[$t]['seq'] && !is_null($target[$t]['sortval'])){
						$this->locationmodel->chgCategorySort($target[$t]['seq'], $sort);
					}
				}
			break;
			case 'gomove':
				$bsort	= (($params['page'] * $params['perpage']) - $params['perpage']) + $bsort;
				$asort	= (($params['page'] * $params['perpage']) - $params['perpage']) + $asort;

				if	($seq){
					if	($bsort < $asort){
						$this->locationmodel->rangeUpdateSort($locationCode, $bsort, $asort+1, '-1');
						$this->locationmodel->chgCategorySort($seq, $asort);
					}elseif	($bsort > $asort){
						$this->locationmodel->rangeUpdateSort($locationCode, $asort-1, $bsort, '+1');
						$this->locationmodel->chgCategorySort($seq, $asort);
					}
				}
			break;
		}

		echo $params['page'];
	}

}

/* End of file location.php */
/* Location: ./app/controllers/admin/location.php */