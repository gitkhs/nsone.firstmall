<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class goods_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->helper('goods');
		$this->load->library('validation');
		$this->load->model('goodsmodel');
	}

	public function category_connect(){

		$this->load->model('categorymodel');
		$this->load->helper('readurl');

		$this->validation->set_rules('categoryInputMethod', '카테고리입력방법','trim|required|max_length[10]|xss_clean');
		if($_POST['categoryInputMethod'] == "select"){
			$this->validation->set_rules('category1', '카테고리','trim|required|max_length[4]|xss_clean');
			$this->validation->set_rules('category2', '카테고리','trim|max_length[8]|xss_clean');
			$this->validation->set_rules('category3', '카테고리','trim|max_length[12]|xss_clean');
			$this->validation->set_rules('category4', '카테고리','trim|max_length[16]|xss_clean');
		}else if($_POST['categoryInputMethod'] == "lastSelect"){
			$this->validation->set_rules('categoryLastRegist', '카테고리','trim|required|max_length[16]|xss_clean');
		}else if($_POST['categoryInputMethod'] == "input"){
			$this->validation->set_rules('category_input[]', '카테고리','trim|xss_clean');
			$max_key = 0;
			foreach($_POST['category_input'] as $k => $data) if($data) $max_key = $k;
			for($i=0;$i<=$max_key;$i++){
				if(!$_POST['category_input'][$i]){
					$callback = "if(parent.document.getElementsByName('category_input[]')[".$i."]) parent.document.getElementsByName('category_input[]')[".$i."].focus();";
					openDialogAlert('카테고리를 입력해주세요!',400,140,'parent',$callback);
					exit;
				}
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['categoryInputMethod'] == "select"){
			for($i=1;$i<=4;$i++){
				if( !isset($_POST['category'.$i]) ) continue;
				$catecode = $_POST['category'.$i];
				if( $catecode ){
					$catename = $this->categorymodel->get_category_name($catecode);
					echo("<script type='text/javascript'>parent.add_category('".$catecode."','".addslashes($catename)."');</script>");
				}
			}
		}else if($_POST['categoryInputMethod'] == "lastSelect"){
			if( $_POST['categoryLastRegist'] ){
				$catecode = $_POST['categoryLastRegist'];
				for($i=0;$i<(strlen($catecode)/4);$i++){
					$t_catecode = substr($catecode,0,($i+1)*4);
					$catename = $this->categorymodel->get_category_name($t_catecode);
					echo("<script type='text/javascript'>parent.add_category('".$t_catecode."','".addslashes($catename)."');</script>");
				}
			}
		}else if($_POST['categoryInputMethod'] == "input"){
			/*
			$parent_id = 2;
			$position = $this->categorymodel->get_next_positon($parent_id);
			$category = $this->categorymodel->get_next_category();
			$requestUrl = "http://".$_SERVER['HTTP_HOST']."/admin/category/tree";

			for($i=0;$i<=$max_key;$i++){
				// 카테고리 등록
				$data = array (
				  'operation' => 'create_node',
				  'id' => $parent_id,
				  'position' => $position,
				  'title' => $_POST['category_input'][$i],
				  'type' => 'folder',
				);
				$out = json_decode(readurl($requestUrl, $data));
				$parent_id = $out->id;
				$position = 0;
				$catecode = $this->categorymodel->get_category_code($parent_id);
				$catename = $this->categorymodel->get_category_name($catecode);
				echo("<script type='text/javascript'>parent.add_category('".$catecode."','".$catename."');</script>");
			}
			*/
			$parent_id	= 2;
			$position	= $this->categorymodel->get_next_positon($parent_id);
			$position = ($position)?$position:0;
			$category	= $this->categorymodel->get_next_category();
			$left		= $this->categorymodel->get_next_left();
			$depth_chk	= $max_key+1;
			$right		= $left + ($depth_chk + ($depth_chk-1));

			$this->db->where('level','1');
			$this->db->limit(1);
			$query = $this->db->get('fm_category');
			$defaultCategoryData = $query->row_array();

			for($i=0;$i<=$max_key;$i++){
				$level = (strlen($category)/4) + 1;
				$data = array (
					'parent_id'					=> $parent_id,
					'position'					=> $position,
					'title'						=> $_POST['category_input'][$i],
					'type'						=> 'folder',
					'left'						=> $left,
					'right'						=> $right,
					'level'						=> $level,
					'category_code'				=> $category,
					'list_default_sort'			=> $defaultCategoryData['list_default_sort'],
					'list_style'				=> $defaultCategoryData['list_style'],
					'list_count_w'				=> $defaultCategoryData['list_count_w'],
					'list_count_h'				=> $defaultCategoryData['list_count_h'],
					'list_paging_use'			=> $defaultCategoryData['list_paging_use'],
					'list_image_size '			=> $defaultCategoryData['list_image_size'],
					'list_text_align'			=> $defaultCategoryData['list_text_align'],
					'list_image_decorations'	=> $defaultCategoryData['list_image_decorations'],
					'list_info_settings'		=> $defaultCategoryData['list_info_settings'],
					'list_goods_status'			=> $defaultCategoryData['list_goods_status'],
					'search_use'				=> $defaultCategoryData['search_use'],
					'regist_date'				=> date("Y-m-d H:i:s")
				);
				$result		= $this->db->insert('fm_category', $data);
				$parent_id	= $this->db->insert_id();
				$catecode = $this->categorymodel->get_category_code($parent_id);
				$catename = $this->categorymodel->get_category_name($catecode);
				###
				$category .= "0001";
				$position = 0;
				$left++;
				$right--;
				echo("<script type='text/javascript'>parent.add_category('".$catecode."','".addslashes($catename)."');</script>");
			}
			echo ("<script type='text/javascript'>parent.$('.pcate_input').val('');</script>");
		}
		//echo "<script>parent.closeDialog('categoryPopup');</script>";
	}

	//브랜드 연결
	public function brand_connect(){

		$this->load->model('brandmodel');
		$this->load->helper('readurl');

		$this->validation->set_rules('brandInputMethod', '브랜드입력방법','trim|required|max_length[20]|xss_clean');
		if($_POST['brandInputMethod'] == "select"){

			$this->validation->set_rules('brands1', '브랜드','trim|required|max_length[4]|xss_clean');
			$this->validation->set_rules('brands2', '브랜드','trim|max_length[8]|xss_clean');
			$this->validation->set_rules('brands3', '브랜드','trim|max_length[12]|xss_clean');
			$this->validation->set_rules('brands4', '브랜드','trim|max_length[16]|xss_clean');
		}
		else if($_POST['brandInputMethod'] == "lastSelect"){
			$this->validation->set_rules('brandLastRegist', '브랜드','trim|required|max_length[16]|xss_clean');
		}
		else if($_POST['brandInputMethod'] == "input"){
			$this->validation->set_rules('brand_input[]', '브랜드','trim|xss_clean');
			$max_key = 0;
			foreach($_POST['brand_input'] as $k => $data) if($data) $max_key = $k;
			for($i=0;$i<=$max_key;$i++){
				if(!$_POST['brand_input'][$i]){
					$callback = "if(parent.document.getElementsByName('brand_input[]')[".$i."]) parent.document.getElementsByName('brand_input[]')[".$i."].focus();";
					openDialogAlert('브랜드를 입력해주세요!',400,140,'parent',$callback);
					exit;
				}
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['brandInputMethod'] == "select"){
			for($i=1;$i<=4;$i++){
				if( !isset($_POST['brands'.$i]) ) continue;
				$catecode = $_POST['brands'.$i];
				if( $catecode ){
					$catename = $this->brandmodel->get_brand_name($catecode);
					echo("<script type='text/javascript'>parent.add_brand('".$catecode."','".addslashes($catename)."');</script>");
				}
			}
		}else if($_POST['brandInputMethod'] == "lastSelect"){
			if( $_POST['brandLastRegist'] ){
				$catecode = $_POST['brandLastRegist'];
				for($i=0;$i<(strlen($catecode)/4);$i++){
					$t_catecode = substr($catecode,0,($i+1)*4);
					$catename = $this->brandmodel->get_brand_name($t_catecode);
					echo("<script type='text/javascript'>parent.add_brand('".$t_catecode."','".addslashes($catename)."');</script>");
				}
			}
		}else if($_POST['brandInputMethod'] == "input"){
			/*
			$parent_id = 2;
			$position = $this->categorymodel->get_next_positon($parent_id);
			$category = $this->categorymodel->get_next_category();
			$requestUrl = "http://".$_SERVER['HTTP_HOST']."/admin/category/tree";

			for($i=0;$i<=$max_key;$i++){
				// 카테고리 등록
				$data = array (
				  'operation' => 'create_node',
				  'id' => $parent_id,
				  'position' => $position,
				  'title' => $_POST['category_input'][$i],
				  'type' => 'folder',
				);
				$out = json_decode(readurl($requestUrl, $data));
				$parent_id = $out->id;
				$position = 0;
				$catecode = $this->categorymodel->get_category_code($parent_id);
				$catename = $this->categorymodel->get_category_name($catecode);
				echo("<script type='text/javascript'>parent.add_category('".$catecode."','".$catename."');</script>");
			}
			*/
			$parent_id	= 2;
			$position	= $this->brandmodel->get_next_positon($parent_id);
			$position = ($position)?$position:0;
			$category	= $this->brandmodel->get_next_brand();
			$left		= $this->brandmodel->get_next_left();
			$depth_chk	= $max_key+1;
			$right		= $left + ($depth_chk + ($depth_chk-1));

			$this->db->where('level','1');
			$this->db->limit(1);
			$query = $this->db->get('fm_brand');
			$defaultCategoryData = $query->row_array();

			for($i=0;$i<=$max_key;$i++){
				$level = (strlen($category)/4) + 1;
				$data = array (
					'parent_id'		=> $parent_id,
					'position'		=> $position,
					'title'			=> $_POST['brand_input'][$i],
					'type'			=> 'folder',
					'left'			=> $left,
					'right'			=> $right,
					'level'			=> $level,
					'category_code' => $category,
					'list_default_sort'			=> $defaultCategoryData['list_default_sort'],
					'list_style'				=> $defaultCategoryData['list_style'],
					'list_count_w'				=> $defaultCategoryData['list_count_w'],
					'list_count_h'				=> $defaultCategoryData['list_count_h'],
					'list_paging_use'			=> $defaultCategoryData['list_paging_use'],
					'list_image_size '			=> $defaultCategoryData['list_image_size'],
					'list_text_align'			=> $defaultCategoryData['list_text_align'],
					'list_image_decorations'	=> $defaultCategoryData['list_image_decorations'],
					'list_info_settings'		=> $defaultCategoryData['list_info_settings'],
					'list_goods_status'			=> $defaultCategoryData['list_goods_status'],
					'search_use'				=> $defaultCategoryData['search_use'],
					'regist_date'	=> date("Y-m-d H:i:s")
				);
				$result		= $this->db->insert('fm_brand', $data);
				$parent_id	= $this->db->insert_id();
				$catecode = $this->brandmodel->get_brand_code($parent_id);
				$catename = $this->brandmodel->get_brand_name($catecode);
				###
				$category .= "0001";
				$position = 0;
				$left++;
				$right--;
				echo("<script type='text/javascript'>parent.add_brand('".$catecode."','".addslashes($catename)."');</script>");
			}
			echo ("<script type='text/javascript'>parent.$('.pcate_input').val('');</script>");
		}else if($_POST['brandInputMethod'] == "providerBrand"){

			if( $_POST['providerBrandCode'] ){

				$catecode = $_POST['providerBrandCode'];

				$query = $this->db->query("
					select charge from fm_provider as p
					left join fm_provider_charge as c on p.provider_seq = c.provider_seq
					where p.provider_seq=? and c.category_code=?
				",array($_POST['provider_seq'],$catecode));
				$res = $query->row_array();
				$charge = $res['charge'];

				for($i=0;$i<(strlen($catecode)/4);$i++){
					$t_catecode = substr($catecode,0,($i+1)*4);
					$catename = $this->brandmodel->get_brand_name($t_catecode);
					echo("<script type='text/javascript'>parent.add_brand('".$t_catecode."','".addslashes($catename)."','".$charge."');</script>");
				}
				echo ("<script type='text/javascript'>parent.$('.pcate_input').val('');</script>");
			}
		}
		//echo "<script>parent.closeDialog('brandPopup');</script>";
	}

	//지역 연결
	public function location_connect(){

		$this->load->model('locationmodel');
		$this->load->helper('readurl');

		$this->validation->set_rules('locationInputMethod', '지역입력방법','trim|required|max_length[20]|xss_clean');
		if($_POST['locationInputMethod'] == "select"){

			$this->validation->set_rules('location1', '지역','trim|required|max_length[4]|xss_clean');
			$this->validation->set_rules('location2', '지역','trim|max_length[8]|xss_clean');
			$this->validation->set_rules('location3', '지역','trim|max_length[12]|xss_clean');
			$this->validation->set_rules('location4', '지역','trim|max_length[16]|xss_clean');
		}
		else if($_POST['locationInputMethod'] == "lastSelect"){
			$this->validation->set_rules('locationLastRegist', '지역','trim|required|max_length[16]|xss_clean');
		}
		else if($_POST['locationInputMethod'] == "input"){
			$this->validation->set_rules('location_input[]', '지역','trim|xss_clean');
			$max_key = 0;
			foreach($_POST['location_input'] as $k => $data) if($data) $max_key = $k;
			for($i=0;$i<=$max_key;$i++){
				if(!$_POST['location_input'][$i]){
					$callback = "if(parent.document.getElementsByName('location_input[]')[".$i."]) parent.document.getElementsByName('location_input[]')[".$i."].focus();";
					openDialogAlert('지역를 입력해주세요!',400,140,'parent',$callback);
					exit;
				}
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['locationInputMethod'] == "select"){
			for($i=1;$i<=4;$i++){
				if( !isset($_POST['location'.$i]) ) continue;
				$catecode = $_POST['location'.$i];
				if( $catecode ){
					$catename = $this->locationmodel->get_location_name($catecode);
					echo("<script type='text/javascript'>parent.add_location('".$catecode."','".addslashes($catename)."');</script>");
				}
			}
		}else if($_POST['locationInputMethod'] == "lastSelect"){
			if( $_POST['locationLastRegist'] ){
				$catecode = $_POST['locationLastRegist'];
				for($i=0;$i<(strlen($catecode)/4);$i++){
					$t_catecode = substr($catecode,0,($i+1)*4);
					$catename = $this->locationmodel->get_location_name($t_catecode);
					echo("<script type='text/javascript'>parent.add_location('".$t_catecode."','".addslashes($catename)."');</script>");
				}
			}
		}else if($_POST['locationInputMethod'] == "input"){
			$parent_id	= 2;
			$position	= $this->locationmodel->get_next_positon($parent_id);
			$position = ($position)?$position:0;
			$category	= $this->locationmodel->get_next_location();
			$left		= $this->locationmodel->get_next_left();
			$depth_chk	= $max_key+1;
			$right		= $left + ($depth_chk + ($depth_chk-1));

			$this->db->where('level','1');
			$this->db->limit(1);
			$query = $this->db->get('fm_location');
			$defaultCategoryData = $query->row_array();

			for($i=0;$i<=$max_key;$i++){
				$level = (strlen($category)/4) + 1;
				$data = array (
					'parent_id'		=> $parent_id,
					'position'		=> $position,
					'title'			=> $_POST['location_input'][$i],
					'type'			=> 'folder',
					'left'			=> $left,
					'right'			=> $right,
					'level'			=> $level,
					'location_code' => $category,
					'list_default_sort'			=> $defaultCategoryData['list_default_sort'],
					'list_style'				=> $defaultCategoryData['list_style'],
					'list_count_w'				=> $defaultCategoryData['list_count_w'],
					'list_count_h'				=> $defaultCategoryData['list_count_h'],
					'list_paging_use'			=> $defaultCategoryData['list_paging_use'],
					'list_image_size '			=> $defaultCategoryData['list_image_size'],
					'list_text_align'			=> $defaultCategoryData['list_text_align'],
					'list_image_decorations'	=> $defaultCategoryData['list_image_decorations'],
					'list_info_settings'		=> $defaultCategoryData['list_info_settings'],
					'list_goods_status'			=> $defaultCategoryData['list_goods_status'],
					'search_use'				=> $defaultCategoryData['search_use'],
					'regist_date'	=> date("Y-m-d H:i:s")
				);
				$result		= $this->db->insert('fm_location', $data);
				$parent_id	= $this->db->insert_id();
				$catecode = $this->locationmodel->get_location_code($parent_id);
				$catename = $this->locationmodel->get_location_name($catecode);
				###
				$category .= "0001";
				$position = 0;
				$left++;
				$right--;
				echo("<script type='text/javascript'>parent.add_location('".$catecode."','".addslashes($catename)."');</script>");
			}
			echo ("<script type='text/javascript'>parent.$('.pcate_input').val('');</script>");

		}else if($_POST['locationInputMethod'] == "providerLocation"){

			if( $_POST['providerLocationCode'] ){

				$catecode = $_POST['providerLocationCode'];

				$query = $this->db->query("
					select charge from fm_provider as p
					left join fm_provider_charge as c on p.provider_seq = c.provider_seq
					where p.provider_seq=? and c.location_code=?
				",array($_POST['provider_seq'],$catecode));
				$res = $query->row_array();
				$charge = $res['charge'];

				for($i=0;$i<(strlen($catecode)/4);$i++){
					$t_catecode = substr($catecode,0,($i+1)*4);
					$catename = $this->locationmodel->get_location_name($t_catecode);
					echo("<script type='text/javascript'>parent.add_location('".$t_catecode."','".addslashes($catename)."','".$charge."');</script>");
				}
				echo ("<script type='text/javascript'>parent.$('.pcate_input').val('');</script>");
			}
		}
		//echo "<script>parent.closeDialog('locationPopup');</script>";

	}

	public function upload_file(){
		$error = array('status' => 0,'msg' => '업로드 실패하였습니다.','desc' => '업로드 실패');
		$folder = "data/tmp/";
		$idx = $_POST['idx'];
		$div = $division = $_POST['division'];
		$arrDiv = config_load('goodsImageSize');
		$newFile = date('dHis');
		if( in_array($div,array_keys($arrDiv)) ){
			$filename = $newFile.$div;
			$result = $this->goodsmodel->goods_temp_image_upload($filename,$folder);
			if(!$result['status']){
				if($result['error']) $error['msg'] = $result['error'];
				echo "[".json_encode($error)."]";
				exit;
			}
			$source = $result['fileInfo']['full_path'];
			$target = $result['fileInfo']['full_path'];
			/*
			$resizeResult = $this->goodsmodel->goods_temp_image_resize($source,$target,$arrDiv[$div]['width'],$arrDiv[$div]['height']);
			if(!$resizeResult['status']){
				echo "[".json_encode($error)."]";
				exit;
			}
			*/
		}else if($div == 'all'){
			$div = "large";
			$filename = $newFile.$div;
			$result = $this->goodsmodel->goods_temp_image_upload($filename,$folder);
			if(!$result['status']){
				if($result['error']) $error['msg'] = $result['error'];
				echo "[".json_encode($error)."]";
				exit;
			}
			$source = $result['fileInfo']['full_path'];

			foreach($arrDiv as $tmp => $size){
				// if( $idx > 1 && in_array($tmp,array('list1','list2')) ) continue;
				$target = $result['fileInfo']['file_path'].$newFile.$tmp.$result['fileInfo']['file_ext'];
				$resizeResult = $this->goodsmodel->goods_temp_image_resize($source,$target,$arrDiv[$tmp]['width'],$arrDiv[$tmp]['height']);
				if(!$resizeResult['status']){
					if($result['error']) $error['msg'] = $result['error'];
					echo "[".json_encode($error)."]";
					exit;
				}
			}
		}
		$result = array('status' => 1,'newFile' => "/".$folder.$newFile,'idx' => $idx,'division'=>$division,'ext' => $result['fileInfo']['file_ext']);
		echo "[".json_encode($result)."]";
	}

	//여러컷 일괄등록시@2015-02-10 
	public function upload_file_multi() {
		$error = array('status' => 0,'msg' => '업로드 실패하였습니다.','desc' => '업로드 실패');
		$folder = "data/tmp/";
		$div = $division = "all";
		$arrDiv = config_load('goodsImageSize');
		$newFile = date('dHis'); 
		$div = "large";
		$filename = $newFile.$div;
		$result = $this->goodsmodel->goods_temp_image_upload($filename,$folder);
		if(!$result['status']){
			if($result['error']) $error['msg'] = $result['error'];
			echo "[".json_encode($error)."]";
			exit;
		}
		$source = $result['fileInfo']['full_path'];

		foreach($arrDiv as $tmp => $size){
			$target = $result['fileInfo']['file_path'].$newFile.$tmp.$result['fileInfo']['file_ext'];
			$resizeResult = $this->goodsmodel->goods_temp_image_resize($source,$target,$arrDiv[$tmp]['width'],$arrDiv[$tmp]['height']); 
		} 
		$result = array('status' => 1,'newFile' => "/".$folder.$newFile,'ext' => $result['fileInfo']['file_ext']);
		echo "[".json_encode($result)."]";
	}

	public function regist(){

		#### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		/* 다량옵션오류방지를 위하여 인코딩된 옵션폼값을 디코딩 */
		decodeFormValue($_POST['encodedFormValue'],'POST');

		/* 상품등록 파라미터 검증*/
		if(in_array(strtolower($_POST['mobile_contents']),array("<p>&nbsp;</p>","<p><br></p>"))) $_POST['mobile_contents'] = "";
		$_POST['shippingPolicy'] = !$_POST['shippingPolicy'] ? "shop" : $_POST['shippingPolicy'];
		$goods = $this->goodsmodel->check_param_regist();
		$goods['regist_date'] = $goods['update_date'] = date("Y-m-d H:i:s",time());
		if($_POST['goodsSubInfo']!=='') $goods['goods_sub_info'] = $_POST['goodsSubInfo'];
		if($_POST['subInfoTitle']) {
			for($i=0; $i<count($_POST['subInfoTitle']); $i++){
				$subInfo[$_POST['subInfoTitle'][$i]] = $_POST['subInfoDesc'][$i];
			}
		}

		if( $_POST['optionUse'] == '1' && !$_POST['tmp_option_seq']){
			openDialogAlert("필수옵션을 추가해 주세요",400,140,'parent',$callback);
			exit;
		}

		if($_POST['largeGoodsImage'][0] || $_POST['viewGoodsImage'][0] || $_POST['list1GoodsImage'][0] || $_POST['list2GoodsImage'][0] || $_POST['thumbViewGoodsImage'][0] || $_POST['thumbCartGoodsImage'][0] || $_POST['thumbScrollGoodsImage'][0]){
			/* 디스크 용량 체크 */
			$this->load->model('usedmodel');
			$data_used = $this->usedmodel->used_limit_check();
			if( !$data_used['type'] ){
				openDialogAlert($data_used['msg'],600,340,'parent','');
				exit;
			}
		}

		//쿠폰상품 값어치/유효기간 체크
		if( $_POST['goods_kind'] == 'coupon' ) {
			
			//유효기간 종료 전
			if( $_POST['socialcp_cancel_type'] == 'payoption' ) {
				$socialcp_cancel_result = true;
				foreach( $_POST['socialcp_cancel_day'] as $key => $socialcp_cancel) {
					if( !($_POST['socialcp_cancel_day'][$key]>='0') ) { 
						$socialcp_cancel_result = false;
						$socialcp_cancel_key = $key;
						break;
					}
				}

				if( $socialcp_cancel_result === false ) {
					$callback = "parent.document.getElementsByName('socialcp_cancel_day[]')[".($socialcp_cancel_key+1)."].focus();";
					$msg = "유효기간 종료 전 취소(환불) 가능날짜를 정확히 입력해 주세요.";
					openDialogAlert($msg,520,160,'parent',$callback);
					exit;
				} 
				if( $_POST['socialcp_cancel_payoption'] ) {
					$socialcp_cancel_result		= ( ($_POST['socialcp_cancel_payoption_percent']>='0') )?true:false;
					if( $socialcp_cancel_result === false ) {
						$callback = "parent.document.getElementsByName('socialcp_cancel_payoption_percent[]')[".($key+1)."].focus();";
						$msg = "유효기간 종료 전 유효기간의 취소(환불)율을 정확히 입력해 주세요."; 
						openDialogAlert($msg,520,160,'parent',$callback);
						exit;
					}
				} 
			}elseif( $_POST['socialcp_cancel_type'] == 'pay' ) {
				$socialcp_cancel_result		= (($_POST['socialcp_cancel_day'][0]>='0') )?true:false;
				if( $socialcp_cancel_result === false ) {
					$callback = "parent.document.getElementsByName('socialcp_cancel_day[]')[0].focus();";
					$msg = "유효기간 종료 전 결제확인 후 취소(환불) 가능날짜를 정확히 입력해 주세요."; 
					openDialogAlert($msg,520,160,'parent',$callback);
					exit;
				}
			}
			
			//유효기간 종료 후 미사용 쿠폰
			if( $_POST['socialcp_use_return'] == '1' ) {
				$socialcp_cancel_result		= ($_POST['socialcp_use_emoney_day']>='0')?true:false;
				if( $socialcp_cancel_result === false ) {
					$msg = "유효기간 종료 후 미사용 쿠폰의 취소(환불) 가능날짜를 정확히 입력해 주세요."; 
					$callback = "parent.document.getElementsByName('socialcp_use_emoney_day')[0].focus();";
					openDialogAlert($msg,520,160,'parent',$callback);
					exit;
				} 
			} 
			//openDialogAlert("test",500,160,'parent',$callback);exit;

			if( $_POST['optionUse'] != '1') {
				openDialogAlert("[쿠폰상품]의 유효기간(또는 날짜) 필수옵션을 추가해 주세요",450,140,'parent',$callback);
				exit;
			}

			$today = date("Y-m-d");
			foreach($_POST as $k => $v){
				if( $k == 'coupon_input' &&  in_array(0,$v) ) {
					$msg = "[쿠폰상품]의 쿠폰1장의 값어치를 정확히 입력해 주세요.";
					openDialogAlert($msg,450,140,'parent',$callback);
					exit;
				}

				if( $k == 'optnewtype') {
					$couponexpire =  false;
					if( !( in_array('date',$v) || in_array('dayinput',$v) || in_array('dayauto',$v) ) ){//coupon goods
						$msg = "[쿠폰상품]의 유효기간(날짜, 자동기간, 수동기간)옵션을 추가해 주세요.";
						openDialogAlert($msg,470,150,'parent',$callback);
						exit;
					}

					if( in_array('date', $v) ) {
						foreach($_POST['codedate'] as $key => $codedate){
							if( $codedate >= $today ) {
								$couponexpire = true;
								break;
							}else{
								$social_start_date	= $codedate;
								$social_end_date	= $codedate;
							}
						}

								if( $couponexpire === false ) {
									$msg = "[쿠폰상품]의 유효기간을 정확히 입력해 주세요.";
									if( strstr($social_start_date,'0000-00-00') || strstr($social_end_date,'0000-00-00') || !$social_start_date  || !$social_end_date){
										$msg .= "<br/>유효기간이 없습니다.";
									}else{
										$msg .= "<br/>유효기간이 ".$codedate." 입니다.";
									}
									openDialogAlert($msg,450,160,'parent',$callback);
									exit;
								}
					}elseif( in_array('dayinput', $v) ) {
						foreach($_POST['fdayinput'] as $key => $fdayinput){
							if( $fdayinput >= $today ) {
								$couponexpire = true;
								break;
							}else{
								$social_start_date = $_POST['sdayinput'][$key];
								$social_end_date = $fdayinput;
							}
						}

								if( $couponexpire === false ) {
									$msg = "[쿠폰상품]의 유효기간을 정확히 입력해 주세요.";
									if( strstr($social_start_date,'0000-00-00') || strstr($social_end_date,'0000-00-00') || !$social_start_date  || !$social_end_date ){
										$msg .= "<br/>유효기간이 없습니다.";
									}else{
										$msg .= "<br/>유효기간이 ".$social_start_date." ~ ".$social_end_date." 입니다.";
									}
									openDialogAlert($msg,450,160,'parent',$callback);
									exit;
								}
					}
				}//endif
			}//endfor
		}

		/* 아이콘 */
		foreach($_POST['goodsIcon'] as $key => $goodsIcon){
			if($key == 0){
				$start = 0;
				$end = 1;
			}else{
				$start = $key * 2;
				$end = $start + 1;
			}

			if($_POST['iconDate'][$start] > $_POST['iconDate'][$end]) {
				$callback = "parent.document.getElementsByName('iconDate[]')[".$start."].focus();";
				openDialogAlert('아이콘 노출 시작일이 종료일보다 클 수 없습니다.',500,160,'parent',$callback);
				exit;
			}
		}

		$goods['sub_info_desc'] = json_encode($subInfo);
		$goods['sale_seq'] = $_POST["sale_seq"];

		if($_POST["possible_pay_type_hidden"] == 'goods'){
			$goods['possible_pay_type'] = "goods";
			$goods['possible_pay'] = $_POST["possible_pay_hidden"];
			$goods['possible_mobile_pay'] = $_POST["possible_mobile_pay_hidden"];
		}else{
			$goods['possible_pay_type'] = "shop";
			$goods['possible_pay'] = "";
			$goods['possible_mobile_pay'] = "";
		}

		/* 공용정보 입력시 공용정보명 체크 추가 leewh 2014-08-20 */
		if ($_POST['commonContents']=="<p><br></p>") $_POST['commonContents'] = "";
		if (!empty($_POST['commonContents']) && empty($_POST['info_name'])) {
			$callback = "if(parent.document.getElementsByName('info_name')[0]) parent.document.getElementsByName('info_name')[0].focus();";
			openDialogAlert("공용정보명을 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$result = $this->db->insert('fm_goods', $goods);
		$goodsSeq = $this->db->insert_id();

		// 판매마켓 전송 대상 몰 저장
		$this->load->model('openmarketmodel');
		$this->openmarketmodel->save_goods_send_mall($goodsSeq, $_POST['openmarket_send_mall_id']);

		//동영상관리
		$this->load->model('videofiles');
		foreach($_POST['videofiles']['image'] as $videoseq) {
			$videoseq = ($videoseq)?$videoseq:0;
			$videotype = 'image';
			$video_del		= $_POST['video_del'][$videotype][$videoseq];
			//$videoseq		= $_POST['videofiles'][$videotype][$videoseq];
			if($video_del == 1 ){//연결해제(삭제)
				$this->videofiles->videofiles_delete($videoseq);
			}else{
				$viewer_use		= ($_POST['viewer_use'][$videotype][$videoseq])?$_POST['viewer_use'][$videotype][$videoseq]:'N';
				$viewer_position= ($_POST['viewer_position'][$videotype][$videoseq])?$_POST['viewer_position'][$videotype][$videoseq]:'first';
				$pc_width			= $_POST['pc_width'][$videotype][$videoseq];
				$pc_height			= $_POST['pc_height'][$videotype][$videoseq];
				$mobile_width		= $_POST['mobile_width'][$videotype][$videoseq];
				$mobile_height	= $_POST['mobile_height'][$videotype][$videoseq];
				unset($videofiles);
				$videofiles['parentseq']			= $goodsSeq;
				$videofiles['viewer_use']			= $viewer_use;//
				$videofiles['viewer_position']		= $viewer_position;//
				$videofiles['pc_width']				= $pc_width;//
				$videofiles['pc_height']			= $pc_height;//
				$videofiles['mobile_width']		= $mobile_width;//
				$videofiles['mobile_height']		= $mobile_height;//
				$videofiles['seq']						= $videoseq;//
				$this->videofiles->videofiles_modify($videofiles);
			}
		}

		$sort = 0;
		foreach($_POST['videofiles']['contents'] as $videoseq) {$sort++;
			if(!$videoseq) continue;
			$videotype = 'contents';
			/**
			$video_del		= $_POST['video_del'][$videotype][$videoseq];
			if($video_del == 1 ){//연결해제(삭제)
				$this->videofiles->videofiles_delete($videoseq);
			}
			**/
			$viewer_use		= ($_POST['viewer_use'][$videotype][$videoseq])?$_POST['viewer_use'][$videotype][$videoseq]:'N';
			$pc_width			= $_POST['pc_width'][$videotype][$videoseq];
			$pc_height			= $_POST['pc_height'][$videotype][$videoseq];
			$mobile_width		= $_POST['mobile_width'][$videotype][$videoseq];
			$mobile_height	= $_POST['mobile_height'][$videotype][$videoseq];
			unset($videofiles);
			$videofiles['parentseq']			= $goodsSeq;
			$videofiles['viewer_use']			= $viewer_use;//
			$videofiles['pc_width']				= $pc_width;//
			$videofiles['pc_height']			= $pc_height;//
			$videofiles['mobile_width']		= $mobile_width;//
			$videofiles['mobile_height']		= $mobile_height;//
			$videofiles['seq']						= $videoseq;//
			$videofiles['sort']						= $sort;//
			$this->videofiles->videofiles_modify($videofiles);
		}

		/* 상품이미지 설정 체크 -> 새창페이지로 save_image_config() 변경 @2015-03-03 */

		/* 카테고리 연결 */
		$this->load->model('categorymodel');
		foreach($_POST['connectCategory'] as $code){
			$categorys['link'] = 0;
			if($code == $_POST['firstCategory']) $categorys['link'] = 1;
			$categorys['category_code'] = $code;
			$categorys['goods_seq'] = $goodsSeq;
			$categorys['regist_date'] = date('Y-m-d H:i:s',time());
			$result = $this->db->insert('fm_category_link', $categorys);

			$minsort	= $this->categorymodel->getSortValue($code, 'min');
			$category_link_seq = $this->db->insert_id();
			$this->db->where('category_link_seq', $category_link_seq);
			$this->db->update('fm_category_link',array('sort'=>$minsort-1));
		}

		/* 브랜드 연결 */
		$this->load->model('brandmodel');
		foreach($_POST['connectBrand'] as $code){
			$brands['link'] = 0;
			if($code == $_POST['firstBrand']) $brands['link'] = 1;
			$brands['category_code'] = $code;
			$brands['goods_seq'] = $goodsSeq;
			$brands['regist_date'] = date('Y-m-d H:i:s',time());
			$result = $this->db->insert('fm_brand_link', $brands);

			$minsort	= $this->brandmodel->getSortValue($code, 'min');
			$category_link_seq = $this->db->insert_id();
			$this->db->where('category_link_seq', $category_link_seq);
			$this->db->update('fm_brand_link',array('sort'=>$minsort-1));
		}

		/* 지역 연결 */
		$this->load->model('locationmodel');
		foreach($_POST['connectLocation'] as $code){
			$locations['link'] = 0;
			if($code == $_POST['firstLocation']) $locations['link'] = 1;
			$locations['location_code'] = $code;
			$locations['goods_seq'] = $goodsSeq;
			$locations['regist_date'] = date('Y-m-d H:i:s',time());
			$result = $this->db->insert('fm_location_link', $locations);

			$minsort	= $this->locationmodel->getSortValue($code, 'min');
			$location_link_seq = $this->db->insert_id();
			$this->db->where('location_link_seq', $location_link_seq);
			$this->db->update('fm_location_link',array('sort'=>$minsort-1));
		}

		/* 추가 정보 */
		foreach($_POST['selectEtcTitle'] as $key => $type){
			$cnt = 0;
			if($type != 'direct'){
				$query = "SELECT count(*) as cnt FROM `fm_goods_addition` WHERE `goods_seq`=? and `type`=?";
				$query = $this->db->query($query,array($goodsSeq,$type));
				$row = $query->row();
				$cnt = $row->cnt;
			}
			if($cnt == 0){
				$additions['goods_seq']	= $goodsSeq;
				$additions['code_seq']	= str_replace("goodsaddinfo_","",$type);
				$additions['type']		= $type;

				if( $_POST['etcTitle'][$key]) {
					$additions['title']		= ( strstr($type,"goodsaddinfo_") && strstr($type," [코드]"))?str_replace(" [코드]","",$_POST['etcTitle'][$key]):$_POST['etcTitle'][$key];
				}else{
					$query = "SELECT label_title FROM `fm_goods_code_form` WHERE `codeform_seq`=?";
					$code_formquery = $this->db->query($query,array($additions['code_seq']));
					$code_formdata = $code_formquery->row();
					$additions['title']		= $code_formdata->label_title;
				}
				$additions['contents_title']	= $_POST['etcContents_title'][$key];
				$additions['contents']			= $_POST['etcContents'][$key];
				$additions['linkage_val']		= $_POST['linkageOrigin'][$key];
				$result = $this->db->insert('fm_goods_addition', $additions);
			}
		}

		/* 아이콘 */
		foreach($_POST['goodsIcon'] as $key => $goodsIcon){
			if($key == 0){
				$start = 0;
				$end = 1;
			}else{
				$start = $key * 2;
				$end = $start + 1;
			}
			$icons['end_date']		= "";
			$icons['start_date']	= "";
			if($_POST['iconDate'][$end]) $icons['end_date']	= $_POST['iconDate'][$end];
			if($_POST['iconDate'][$start]) $icons['start_date']	= $_POST['iconDate'][$start];
			$icons['goods_seq']		= $goodsSeq;
			$icons['codecd']		= $goodsIcon;
			$result = $this->db->insert('fm_goods_icon', $icons);
		}

		//쇼셜쿠폰 상품이면
		if($_POST['goods_kind'] == 'coupon' ){
			if( $_POST['socialcp_cancel_type'] == 'payoption' ) {
				unset($socialcpcancels);
				foreach( $_POST['socialcp_cancel_day'] as $key => $socialcp_cancel) {
					$socialcpcancels['goods_seq']						= $goodsSeq;
					$socialcpcancels['socialcp_cancel_type']		= $_POST['socialcp_cancel_type'];
					$socialcpcancels['socialcp_cancel_day']		= $_POST['socialcp_cancel_day'][$key];
					$socialcpcancels['socialcp_cancel_percent']	= $_POST['socialcp_cancel_percent'][$key];
					$socialcpcancels['regist_date'] = date('Y-m-d H:i:s',time());
					$result = $this->db->insert('fm_goods_socialcp_cancel', $socialcpcancels);
				}
			}else{
				unset($socialcpcancels);
				$socialcpcancels['goods_seq']						= $goodsSeq;
				$socialcpcancels['socialcp_cancel_type']		= $_POST['socialcp_cancel_type'];
				$socialcpcancels['socialcp_cancel_day']		= ($_POST['socialcp_cancel_day'][0])?$_POST['socialcp_cancel_day'][0]:0;
				$socialcpcancels['socialcp_cancel_percent']	= 100;//percent
				$socialcpcancels['regist_date'] = date('Y-m-d H:i:s',time());
				$result = $this->db->insert('fm_goods_socialcp_cancel', $socialcpcancels);
			}
		}

		/* 필수옵션 */
		if	($_POST['optionUse'] == '1' && $_POST['tmp_option_seq']) {
			$this->goodsmodel->moveTmpToOption($goodsSeq, $_POST['tmp_option_seq']);
		}else{
			$defaultOptions = explode(',',$_POST['defaultOption']);
			for($i=0;$i<5;$i++){
				for($j=0;$j<count($_POST['price']);$j++){
					$tmp = 'opt'.$i;
					${$tmp}[$j] = "";
					$tmpcode = 'optcode'.$i;
					${$tmpcode}[$j] = "";
					if( isset($_POST['opt'][$i][$j]) ){
						${$tmpcode}[$j] = $_POST['optcode'][$i][$j];
						${$tmp}[$j] = $_POST['opt'][$i][$j];
						$comp_opt[$j][] = $_POST['opt'][$i][$j];
					}
				}
			}

			$i=0;
			foreach($_POST['price'] as $key => $price){
				$defaultOption = 'n';
				if( $_POST['defaultOption'] == implode(',',$comp_opt[$key]) ) $defaultOption = 'y';
				if( !$_POST['reserveUnit'][$key] ) $_POST['reserveUnit'][$key] = "percent";
				$options['goods_seq'] 		= $goodsSeq;
				$options['default_option'] 	= $defaultOption;
				$options['option_title'] 	= implode(',',$_POST['optionTitle']);

				$options['code_seq']	= ($_POST['optionType'])?str_replace("goodsoption_","",implode(',',$_POST['optionType'])):'';
				$options['option_type'] = ($_POST['optionType'])?implode(',',$_POST['optionType']):'direct';

				$options['option1'] 		= $opt0[$key];
				$options['option2'] 		= $opt1[$key];
				$options['option3'] 		= $opt2[$key];
				$options['option4'] 		= $opt3[$key];
				$options['option5'] 		= $opt4[$key];

				$options['optioncode1'] 		= $optcode0[$key];
				$options['optioncode2'] 		= $optcode1[$key];
				$options['optioncode3'] 		= $optcode2[$key];
				$options['optioncode4'] 		= $optcode3[$key];
				$options['optioncode5'] 		= $optcode4[$key];

				$options['infomation'] 		= $_POST['infomation'][$key];
				$options['coupon_input'] 	= (int) $_POST['coupon_input'][$key];
				$options['consumer_price'] 	= (int) $_POST['consumerPrice'][$key];
				$options['price'] 			= (int) $_POST['price'][$key];
				$options['reserve_rate'] 	= (int) $_POST['reserveRate'][$key];
				$options['reserve_unit'] 	= $_POST['reserveUnit'][$key];
				$options['reserve'] 		= (int) $_POST['reserve'][$key];
				$options['commission_rate'] = (int) $_POST['commissionRate'][$key];
				$result = $this->db->insert( 'fm_goods_option', $options );
				$supplys 					= array();
				$supplys['goods_seq'] 		= $goodsSeq;
				$supplys['supply_price'] 	= $_POST['supplyPrice'][$key];
				$supplys['option_seq'] 		= $this->db->insert_id();
				$supplys['stock'] 			= $_POST['stock'][$key];
				$supplys['badstock'] 		= $_POST['badstock'][$key];
				$supplys['reservation15'] 	= $_POST['reservation15'][$key];
				$supplys['reservation25'] 	= $_POST['reservation25'][$key];

				$this->db->insert( 'fm_goods_supply', $supplys );
				$i++;

			}
		}


		/* 총재고 수량 입력 */
		$this->goodsmodel->total_stock($goodsSeq);
		// 오픈마켓 옵션 가격 조정 정보 저장
		$this->openmarketmodel->save_openmarket_option($goodsSeq, $_POST['market_tmp_seq']);

		/* 추가옵션 */
		if	($_POST['subOptionUse']){
			if	($_POST['tmp_suboption_seq']){
				$this->goodsmodel->moveTmpToSubOption($goodsSeq, $_POST['tmp_suboption_seq']);
			}
		}

		/* 가용재고 재계산 */
		$this->goodsmodel->modify_reservation_real($goodsSeq,'manual');

		/* 구매자입력사항 */
		foreach($_POST['memberInputName'] as $i => $input){
			if( ! isset($_POST['memberInputRequire'][$i]) ) $_POST['memberInputRequire'][$i] = '0';
			else $_POST['memberInputRequire'][$i] = '1';
			$inputs['goods_seq'] 		= $goodsSeq;
			$inputs['input_name'] 		= $input;
			$inputs['input_form']		= $_POST['memberInputForm'][$i];
			$inputs['input_limit'] 		= $_POST['memberInputLimit'][$i];
			$inputs['input_require']	= $_POST['memberInputRequire'][$i];
			$result = $this->db->insert('fm_goods_input', $inputs);
		}

		/* 상품이미지 설정 저장 -> 새창페이지로 save_image_config() 변경 @2015-03-03 */

		/* 상품이미지 저장 */
		$this->goodsmodel->upload_goodsImage($_POST['largeGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['viewGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['list1GoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['list2GoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['thumbViewGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['thumbCartGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['thumbScrollGoodsImage']);

		/* 워터마크 이미지  갱신 */
		/*
		$r_img_type = array('larget','view','list1','list2');
		foreach($r_img_type as $img_type)
		{
			$field = $img_type.'GoodsImage';
			foreach($_POST[$field] as $image)
			{
				if( substr_count($image,'/data/tmp/') > 0 )
				{
					$from = $image;
					$to = $this->goodsmodel->get_target_goodsImage($image);

					$from = str_replace('//','/',ROOTPATH.$from);
					$to = str_replace('//','/',ROOTPATH.$to);
					debug_var(array($from,$to));
					$this->watermarkmodel->move_target_image($from,$to);
				}
			}
		}
		*/

		/* 상품이미지 db저장 */
		$this->goodsmodel->insert_goodsImage('largeGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('viewGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('thumbViewGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('thumbCartGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('thumbScrollGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('list1GoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('list2GoodsImage',$goodsSeq);

		/* 워터마크 이미지명 갱신 */
		// $this->goodsmodel->get_target_goodsImage();

		/* INFO */
		###
		$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';

		$common_contents	= adjustEditorImages($_POST['commonContents'], "/data/editor/");
		$params['info_value']	= $common_contents;
		$params['info_name']	= $_POST['info_name'];
		$params['regist_date']	= date("Y-m-d H:i:s");
		if($_POST['info_select']){	// UPDATE
			$data = filter_keys($params, $this->db->list_fields('fm_goods_info'));
			$this->db->where('info_seq', $_POST['info_select']);
			$result = $this->db->update('fm_goods_info', $data);
		}else{						// INSERT
			if($params['info_name'] && $params['info_value']){
				$result = $this->db->insert('fm_goods_info', $params);
				$info_seq = $this->db->insert_id();
				$this->db->where('goods_seq', $goodsSeq);
				$result = $this->db->update('fm_goods', array('info_seq'=>$info_seq));
			}
		}
		//debug_var($params);
		/* RELATION */
		if($_POST['relation_type']=='MANUAL'){
			for($i=0;$i<count($_POST['relationGoods']);$i++){
				$result = $this->db->insert('fm_goods_relation', array('goods_seq'=>$goodsSeq,'relation_goods_seq'=>$_POST['relationGoods'][$i]));
			}
		}

		### RE OPTION CHECK
		$this->goodsmodel->option_check($goodsSeq);

		// 외부 쿠폰 저장
		if	($goods['coupon_serial_type'] == 'n' && $_POST['coupon_serial_upload']){
			$coupon_serial_list	= explode(',', $_POST['coupon_serial_upload']);
			foreach($coupon_serial_list as $k => $list){
				$data	= explode('|', $list);
				if	($data[0] && $data[1] == 'y'){
					if	(!$this->goodsmodel->chkDuple_coupon_serial($data[0])){
						$this->db->insert('fm_goods_coupon_serial', array('coupon_serial'=>$data[0],'goods_seq'=>$goodsSeq,'regist_date'=>date('Y-m-d H:i:s')));
					}
				}
			}
		}

		// 판매마켓 전송 요청
		if($_POST['goods_type']!='gift'){
			$this->openmarketmodel->request_send_goods($goodsSeq);

			// 디스플레이 캐시 삭제
			$this->load->model('goodsdisplay');
			$this->goodsdisplay->delete_display_cach();

			// 할인혜택 금액 저장
			$this->load->model('goodssummarymodel');
			$this->goodssummarymodel->set_event_price(array('goods'=>array($goodsSeq)));
		}

		if($result){
			if($_POST['goods_type']=='gift'){
				$callback = "parent.document.location = '/admin/goods/gift_catalog';";
				openDialogAlert("사은품이 저장 되었습니다.",400,140,'parent',$callback);
			}elseif($_POST['goods_kind'] == 'coupon'){
				$callback = "parent.document.location = '/admin/goods/social_catalog';";
				openDialogAlert("쿠폰상품이 저장 되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location = '/admin/goods/catalog';";
				openDialogAlert("상품이 저장 되었습니다.",400,140,'parent',$callback);
			}
		}
	}

	public function modify(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}

		if( $_POST['optionUse'] == '1' && $_POST['optionAddPopup'] == 'y' && !$_POST['tmp_option_seq']){
			openDialogAlert("필수옵션을 추가해 주세요",400,140,'parent',$callback);
			exit;
		}

		//$_POST['socialcp_input_type'] == 'price' || $_POST['socialcp_input_type'] == 'pass'
		if( $_POST['goods_kind'] == 'coupon' ) {
			//유효기간 종료 전
			if( $_POST['socialcp_cancel_type'] == 'payoption' ) {
				$socialcp_cancel_result = true;
				foreach( $_POST['socialcp_cancel_day'] as $key => $socialcp_cancel) {
					if( !($_POST['socialcp_cancel_day'][$key]>='0') ) { 
						$socialcp_cancel_result = false;
						$socialcp_cancel_key = $key;
						break;
					}
				}

				if( $socialcp_cancel_result === false ) {
					$callback = "parent.document.getElementsByName('socialcp_cancel_day[]')[".($socialcp_cancel_key+1)."].focus();";
					$msg = "유효기간 종료 전 취소(환불) 가능날짜를 정확히 입력해 주세요.";
					openDialogAlert($msg,520,160,'parent',$callback);
					exit;
				} 
				if( $_POST['socialcp_cancel_payoption'] ) {
					$socialcp_cancel_result		= ( ($_POST['socialcp_cancel_payoption_percent']>='0') )?true:false;
					if( $socialcp_cancel_result === false ) {
						$callback = "parent.document.getElementsByName('socialcp_cancel_payoption_percent[]')[".($key+1)."].focus();";
						$msg = "유효기간 종료 전 유효기간의 취소(환불)율을 정확히 입력해 주세요."; 
						openDialogAlert($msg,520,160,'parent',$callback);
						exit;
					}
				} 
			}elseif( $_POST['socialcp_cancel_type'] == 'pay' ) {
				$socialcp_cancel_result		= (($_POST['socialcp_cancel_day'][0]>='0') )?true:false;
				if( $socialcp_cancel_result === false ) {
					$callback = "parent.document.getElementsByName('socialcp_cancel_day[]')[0].focus();";
					$msg = "유효기간 종료 전 결제확인 후 취소(환불) 가능날짜를 정확히 입력해 주세요."; 
					openDialogAlert($msg,520,160,'parent',$callback);
					exit;
				}
			}
			
			//유효기간 종료 후 미사용 쿠폰
			if( $_POST['socialcp_use_return'] == '1' ) {
				$socialcp_cancel_result		= ($_POST['socialcp_use_emoney_day']>='0')?true:false;
				if( $socialcp_cancel_result === false ) {
					$msg = "유효기간 종료 후 미사용 쿠폰의 취소(환불) 가능날짜를 정확히 입력해 주세요."; 
					$callback = "parent.document.getElementsByName('socialcp_use_emoney_day')[0].focus();";
					openDialogAlert($msg,520,160,'parent',$callback);
					exit;
				} 
			} 
			//openDialogAlert("test",500,160,'parent',$callback);exit;

			if( $_POST['optionUse'] != '1') {
				openDialogAlert("[쿠폰상품]의 유효기간(또는 날짜) 필수옵션을 추가해 주세요",470,150,'parent',$callback);
				exit;
			}

		}

		/* 아이콘 */
		foreach($_POST['goodsIcon'] as $key => $goodsIcon){
			if($key == 0){
				$start = 0;
				$end = 1;
			}else{
				$start = $key * 2;
				$end = $start + 1;
			}

			if($_POST['iconDate'][$start] > $_POST['iconDate'][$end]) {
				$callback = "parent.document.getElementsByName('iconDate[]')[".$start."].focus();";
				openDialogAlert('아이콘 노출 시작일이 종료일보다 클 수 없습니다.',500,160,'parent',$callback);
				exit;
			}
		}

		/* 다량옵션오류방지를 위하여 인코딩된 옵션폼값을 디코딩 */
		decodeFormValue($_POST['encodedFormValue'],'POST');

		//쿠폰상품 값어치/유효기간 체크
		if( $_POST['goods_kind'] == 'coupon' ) {
			$today = date("Y-m-d");
			foreach($_POST as $k => $v){
				if( $_POST['socialcp_input_type'] == 'price' || $_POST['socialcp_input_type'] == 'pass' ) {
					if( $k == 'coupon_input' &&  in_array(0,$v) ) {
						$msg = "[쿠폰상품]의 쿠폰1장의 값어치를 정확히 입력해 주세요.";
						openDialogAlert($msg,450,140,'parent',$callback);
						exit;
					}
				}

				if( $k == 'optnewtype') {
					$couponexpire =  false;
					if( !( in_array('date',$v) || in_array('dayinput',$v) || in_array('dayauto',$v) ) ){//coupon goods
						$msg = "[쿠폰상품]의 유효기간(날짜, 자동기간, 수동기간)옵션을 추가해 주세요.";
						openDialogAlert($msg,470,150,'parent',$callback);
						exit;
					}

					if( in_array('date', $v) ) {
						foreach($_POST['codedate'] as $key =>$codedate){//
							if( $codedate >= $today ) {
								$couponexpire = true;
								break;
							}else{
								$social_start_date	= $codedate;
								$social_end_date	= $codedate;
							}
						}
								if( $couponexpire === false ) {
									$msg = "[쿠폰상품]의 유효기간을 정확히 입력해 주세요.";
									if( strstr($social_start_date,'0000-00-00') || strstr($social_end_date,'0000-00-00') || !$social_start_date  || !$social_end_date ){
										$msg .= "<br/>유효기간이 없습니다.";
									}else{
										$msg .= "<br/>유효기간이 ".$codedate." 입니다.";
									}
									openDialogAlert($msg,450,160,'parent',$callback);
									exit;
								}
					}elseif( in_array('dayinput', $v) ) {
						foreach($_POST['fdayinput'] as $key => $fdayinput){
							if( $fdayinput >= $today ) {
								$couponexpire = true;
								break;
							}else{
								$social_start_date = $_POST['sdayinput'][$key];
								$social_end_date = $fdayinput;
							}
						}
								if( $couponexpire === false ) {
									$msg = "[쿠폰상품]의 유효기간을 정확히 입력해 주세요.";
									if( strstr($social_start_date,'0000-00-00') || strstr($social_end_date,'0000-00-00') || !$social_start_date  || !$social_end_date ){
										$msg .= "<br/>유효기간이 없습니다.";
									}else{
										$msg .= "<br/>유효기간이 ".$social_start_date." ~ ".$social_end_date." 입니다.";
									}
									openDialogAlert($msg,450,160,'parent',$callback);
									exit;
								}
					}
				}//endif
			}//endfor
		}

		/* 상품이미지 설정 체크 -> 새창페이지로 save_image_config() 변경 @2015-03-03 */

		/* 상품상세(기본) 컷 필수체크 
		foreach($_POST['viewGoodsImage'] as $key => $image) {
			if ($key !== 0) {
				$imgNo = $key+1;
			} else {
				$imgNo = $key;
			}

			if (empty($image)) {
				unset($imgMsg);
				if ($imgNo==0) {
					$imgMsg = "상품 사진 대표 상품상세(기본) 이미지를 등록해 주세요.";
				} else {
					$imgMsg = "상품 사진 {$imgNo}번째 컷 상품상세(기본) 이미지를 등록해 주세요.";
				}

				openDialogAlert("{$imgMsg}",400,140,'parent',$callback);
				exit;
			}
		}*/

		/* 썸네일(상품상세) 컷 필수체크 
		foreach($_POST['thumbViewGoodsImage'] as $key => $image) {
			if ($key !== 0) {
				$imgNo = $key+1;
			} else {
				$imgNo = $key;
			}

			if (empty($image)) {
				unset($imgMsg);
				if ($imgNo==0) {
					$imgMsg = "상품 사진 대표 썸네일(상품상세) 이미지를 등록해 주세요.";
				} else {
					$imgMsg = "상품 사진 {$imgNo}번째 컷 썸네일(상품상세) 이미지를 등록해 주세요.";
				}

				openDialogAlert("{$imgMsg}",400,140,'parent',$callback);
				exit;
			}
		}*/

		$this->volume_check();

		/* 상품등록 파라미터 검증*/
		if( $_POST['mobile_contents'] == "<P>&nbsp;</P>" ) $_POST['mobile_contents']='';
		$goodsSeq = (int) $_POST['goodsSeq'];
		$_POST['shippingPolicy'] = !$_POST['shippingPolicy'] ? "shop" : $_POST['shippingPolicy'];
		$goods = $this->goodsmodel->check_param_regist();

		$goods['update_date'] = date("Y-m-d H:i:s",time());
		$goods['admin_log'] = "<div>".date("Y-m-d H:i:s")." 관리자(".$this->managerInfo['mname'].")가 상품의 정보를 수정하였습니다. (".$_SERVER['REMOTE_ADDR'].")</div>".$_POST['admin_log'];
        $this->db->where('goods_seq', $goodsSeq);
        if($_POST['mobile_contents']) $goods['mobile_contents'] = $_POST['mobile_contents'];
        if($_POST['goodsSubInfo']!=='') $goods['goods_sub_info'] = $_POST['goodsSubInfo'];
		if($_POST['subInfoTitle']) {
			for($i=0; $i<count($_POST['subInfoTitle']); $i++){
				$subInfo[$_POST['subInfoTitle'][$i]] = $_POST['subInfoDesc'][$i];
			}
		}

		if($_POST["possible_pay_type_hidden"] == 'goods'){
			$goods['possible_pay_type'] = "goods";
			$goods['possible_pay'] = $_POST["possible_pay_hidden"];
			$goods['possible_mobile_pay'] = $_POST["possible_mobile_pay_hidden"];
		}else{
			$goods['possible_pay_type'] = "shop";
			$goods['possible_pay'] = "";
			$goods['possible_mobile_pay'] = "";
		}

		/* 공용정보 입력시 공용정보명 체크 추가 leewh 2014-08-20 */
		if ($_POST['commonContents']=="<p><br></p>") $_POST['commonContents'] = "";
		if (!empty($_POST['commonContents']) && empty($_POST['info_name'])) {
			$callback = "if(parent.document.getElementsByName('info_name')[0]) parent.document.getElementsByName('info_name')[0].focus();";
			openDialogAlert("공용정보명을 입력해 주세요.",400,140,'parent',$callback);
			exit;
		}

		$goods['sub_info_desc'] = json_encode($subInfo);
		$goods['sale_seq'] = $_POST["sale_seq"];
		$result	= $this->db->update('fm_goods', $goods);

		// 판매마켓 전송 대상 몰 저장
		$this->load->model('openmarketmodel');
		$this->openmarketmodel->save_goods_send_mall($goodsSeq, $_POST['openmarket_send_mall_id']);

		//동영상관리
		$this->load->model('videofiles');

		foreach($_POST['videofiles']['image'] as $videoseq) {
			$videoseq = ($videoseq)?$videoseq:0;
			$videotype = 'image';
			$video_del		= $_POST['video_del'][$videotype][$videoseq];
			//$videoseq		= $_POST['videofiles'][$videotype][$videoseq];
			if($videoseq){
				if($video_del == 1 ){//연결해제(삭제)
					$this->videofiles->videofiles_delete($videoseq);
				}else{
					$viewer_use		= ($_POST['viewer_use'][$videotype][$videoseq])?$_POST['viewer_use'][$videotype][$videoseq]:'N';
					$viewer_position= ($_POST['viewer_position'][$videotype][$videoseq])?$_POST['viewer_position'][$videotype][$videoseq]:'first';
					$pc_width			= $_POST['pc_width'][$videotype][$videoseq];
					$pc_height			= $_POST['pc_height'][$videotype][$videoseq];
					$mobile_width		= $_POST['mobile_width'][$videotype][$videoseq];
					$mobile_height	= $_POST['mobile_height'][$videotype][$videoseq];
					unset($videofiles);
					$videofiles['parentseq']			= $goodsSeq;
					$videofiles['viewer_use']			= $viewer_use;//
					$videofiles['viewer_position']		= $viewer_position;//
					$videofiles['pc_width']				= $pc_width;//
					$videofiles['pc_height']			= $pc_height;//
					$videofiles['mobile_width']		= $mobile_width;//
					$videofiles['mobile_height']		= $mobile_height;//
					$videofiles['seq']						= $videoseq;//
					$this->videofiles->videofiles_modify($videofiles);
				}
			}
		}


		$sort = 0;
		foreach($_POST['videofiles']['contents'] as $videoseq) {$sort++;
			if(!$videoseq) continue;
			$videotype = 'contents';
			$video_del		= $_POST['video_del'][$videotype][$videoseq];
			if($video_del == 1 ){//연결해제(삭제)
				$this->videofiles->videofiles_delete($videoseq);
			}else{
				$viewer_use		= ($_POST['viewer_use'][$videotype][$videoseq])?$_POST['viewer_use'][$videotype][$videoseq]:'N';
				$pc_width			= $_POST['pc_width'][$videotype][$videoseq];
				$pc_height			= $_POST['pc_height'][$videotype][$videoseq];
				$mobile_width		= $_POST['mobile_width'][$videotype][$videoseq];
				$mobile_height	= $_POST['mobile_height'][$videotype][$videoseq];
				unset($videofiles);
				$videofiles['parentseq']			= $goodsSeq;
				$videofiles['viewer_use']			= $viewer_use;//
				$videofiles['pc_width']				= $pc_width;//
				$videofiles['pc_height']			= $pc_height;//
				$videofiles['mobile_width']		= $mobile_width;//
				$videofiles['mobile_height']		= $mobile_height;//
				$videofiles['seq']						= $videoseq;//
				$videofiles['sort']						= $sort;//
				$this->videofiles->videofiles_modify($videofiles);
			}
		}

		/* 카테고리 연결 */
		$category_sorts = array();

		$query = "select * from fm_category_link where goods_seq=?";
		$query = $this->db->query($query,array($goodsSeq));
		$data = $query->result_array();
		foreach($data as $row){
			$category_sorts[$row['category_code']] = $row['sort'];
		}

		$this->db->delete('fm_category_link', array('goods_seq' => $goodsSeq));
		unset($categorys);
		$this->load->model('categorymodel');
		foreach($_POST['connectCategory'] as $key => $code){
			$categoryLinkSeq = (int) $_POST['categoryLinkSeq'][$key];
			$categorys['link'] = 0;
			if($code == $_POST['firstCategory']) $categorys['link'] = 1;
			$categorys['category_link_seq'] = $categoryLinkSeq;
			$categorys['category_code'] = $code;
			$categorys['goods_seq'] = $goodsSeq;
			$categorys['regist_date'] = date('Y-m-d H:i:s',time());
			$minsort	= $this->categorymodel->getSortValue($code, 'min');
			$categorys['sort'] = $category_sorts[$code] ? $category_sorts[$code] : $minsort - 1;
			$result = $this->db->insert('fm_category_link', $categorys);
		}

		/* 브랜드 연결 */
		$brand_sorts = array();

		$query = "select * from fm_brand_link where goods_seq=?";
		$query = $this->db->query($query,array($goodsSeq));
		$data = $query->result_array();
		foreach($data as $row){
			$brand_sorts[$row['category_code']] = $row['sort'];
		}

		$this->db->delete('fm_brand_link', array('goods_seq' => $goodsSeq));
		$this->load->model('brandmodel');
		unset($categorys);
		foreach($_POST['connectBrand'] as $key => $code){
			$brandLinkSeq = (int) $_POST['brandLinkSeq'][$key];
			$categorys['link'] = 0;
			if($code == $_POST['firstBrand']) $categorys['link'] = 1;
			$categorys['category_link_seq'] = $brandLinkSeq;
			$categorys['category_code'] = $code;
			$categorys['goods_seq'] = $goodsSeq;
			$categorys['regist_date'] = date('Y-m-d H:i:s',time());
			$minsort	= $this->brandmodel->getSortValue($code, 'min');
			$categorys['sort'] = $brand_sorts[$code] ? $brand_sorts[$code] : $minsort - 1;
			$result = $this->db->insert('fm_brand_link', $categorys);
		}

		/* 지역 연결 */
		$location_sorts = array();

		$query = "select * from fm_location_link where goods_seq=?";
		$query = $this->db->query($query,array($goodsSeq));
		$data = $query->result_array();
		foreach($data as $row){
			$location_sorts[$row['location_code']] = $row['sort'];
		}

		$this->db->delete('fm_location_link', array('goods_seq' => $goodsSeq));
		$this->load->model('locationmodel');
		unset($categorys);
		foreach($_POST['connectLocation'] as $key => $code){
			$locationLinkSeq = (int) $_POST['locationLinkSeq'][$key];
			$categorys['link'] = 0;
			if($code == $_POST['firstLocation']) $categorys['link'] = 1;
			$categorys['location_link_seq'] = $locationLinkSeq;
			$categorys['location_code'] = $code;
			$categorys['goods_seq'] = $goodsSeq;
			$categorys['regist_date'] = date('Y-m-d H:i:s',time());
			$minsort	= $this->locationmodel->getSortValue($code, 'min');
			$categorys['sort'] = $location_sorts[$code] ? $location_sorts[$code] : $minsort - 1;
			$result = $this->db->insert('fm_location_link', $categorys);
		}


		//쇼셜쿠폰 상품이면
		if($_POST['goods_kind'] == 'coupon' ){
			$this->db->delete('fm_goods_socialcp_cancel', array('goods_seq' => $goodsSeq));
			if( $_POST['socialcp_cancel_type'] == 'payoption' ) {
				unset($socialcpcancels);
				foreach( $_POST['socialcp_cancel_day'] as $key => $socialcp_cancel) {
					$socialcpcancels['goods_seq']						= $goodsSeq;
					$socialcpcancels['socialcp_cancel_type']		= $_POST['socialcp_cancel_type'];
					$socialcpcancels['socialcp_cancel_day']		= $_POST['socialcp_cancel_day'][$key];
					$socialcpcancels['socialcp_cancel_percent']	= $_POST['socialcp_cancel_percent'][$key];
					$socialcpcancels['regist_date']					= date('Y-m-d H:i:s',time());
					$result = $this->db->insert('fm_goods_socialcp_cancel', $socialcpcancels);
				}
			}else{
				unset($socialcpcancels);
				$socialcpcancels['goods_seq']						= $goodsSeq;
				$socialcpcancels['socialcp_cancel_type']		= $_POST['socialcp_cancel_type'];
				$socialcpcancels['socialcp_cancel_day']		= ( $_POST['socialcp_cancel_type'] == 'pay'  && $_POST['socialcp_cancel_day'][0])?$_POST['socialcp_cancel_day'][0]:0;
				$socialcpcancels['socialcp_cancel_percent']	= 100;//percent
				$socialcpcancels['regist_date']					= date('Y-m-d H:i:s',time());
				$result = $this->db->insert('fm_goods_socialcp_cancel', $socialcpcancels);
			}
		}

		/* 추가 정보 */
		$this->db->delete('fm_goods_addition', array('goods_seq' => $goodsSeq));
		foreach($_POST['selectEtcTitle'] as $key => $type){
			$cnt = 0;
			if($type != 'direct'){
				$query = "SELECT count(*) as cnt FROM `fm_goods_addition` WHERE `goods_seq`=? and `type`=?";
				$query = $this->db->query($query,array($goodsSeq,$type));
				$row = $query->row();
				$cnt = $row->cnt;
			}
			if($cnt == 0){
				$additionSeq = (int) $_POST['additionSeq'][$key];
				$additions['addition_seq']	= $additionSeq;
				$additions['goods_seq']	= $goodsSeq;
				$additions['code_seq']	= str_replace("goodsaddinfo_","",$type);
				$additions['type']		= $type;

				if( $_POST['etcTitle'][$key]) {
				$additions['title']		= ( strstr($type,"goodsaddinfo_") && strstr($type," [코드]"))?str_replace(" [코드]","",$_POST['etcTitle'][$key]):$_POST['etcTitle'][$key];
				}else{
					$query = "SELECT label_title FROM `fm_goods_code_form` WHERE `codeform_seq`=?";
					$code_formquery = $this->db->query($query,array($additions['code_seq']));
					$code_formdata = $code_formquery->row();
					$additions['title']		= $code_formdata->label_title;
				}
				$additions['contents_title']	= $_POST['etcContents_title'][$key];
				$additions['contents']	= $_POST['etcContents'][$key];
				$additions['linkage_val']		= $_POST['linkageOrigin'][$key];
				$result = $this->db->insert('fm_goods_addition', $additions);
			}
		}

		/* 아이콘 */
		$this->db->delete('fm_goods_icon', array('goods_seq' => $goodsSeq));
		foreach($_POST['goodsIcon'] as $key => $goodsIcon){
			if($key == 0){
				$start = 0;
				$end = 1;
			}else{
				$start = $key * 2;
				$end = $start + 1;
			}
			unset($icons['end_date']);
			unset($icons['start_date']);
			if($_POST['iconDate'][$end]) $icons['end_date']	= $_POST['iconDate'][$end];
			if($_POST['iconDate'][$start]) $icons['start_date']	= $_POST['iconDate'][$start];

			//$iconSeq = (int) $_POST['iconSeq'][$key];
			//$icons['icon_seq']		= $iconSeq;
			$icons['goods_seq']		= $goodsSeq;
			$icons['codecd']		= $goodsIcon;
			$result = $this->db->insert('fm_goods_icon', $icons);
		}

		/* 필수옵션 */
		if	($_POST['optionAddPopup'] == 'y'){
			if	($_POST['tmp_option_seq']){
				$this->goodsmodel->moveTmpToOption($goodsSeq, $_POST['tmp_option_seq']);
			}
		}else{
			$defaultOptions = explode(',',$_POST['defaultOption']);
			for($i=0;$i<5;$i++){
				for($j=0;$j<count($_POST['price']);$j++){
					$tmp = 'opt'.$i;
					${$tmp}[$j] = "";
					$tmpcode = 'optcode'.$i;
					${$tmpcode}[$j] = "";
					if( isset($_POST['opt'][$i][$j]) ){
						${$tmpcode}[$j] = $_POST['optcode'][$i][$j];
						${$tmp}[$j] = $_POST['opt'][$i][$j];
						$comp_opt[$j][] = $_POST['opt'][$i][$j];
					}
				}
			}

			$this->goodsmodel->delete_option_info($goodsSeq);
			foreach($_POST['price'] as $key => $price){
				$defaultOption = 'n';
				if( $_POST['defaultOption'] == implode(',',$comp_opt[$key]) ) $defaultOption = 'y';
				if( !$_POST['reserveUnit'][$key] ) $_POST['reserveUnit'][$key] = "percent";
				$optionSeq = (int) $_POST['optionSeq'][$key];
				$options['option_seq']		= $optionSeq;
				unset($options['option_seq']);
				$options['goods_seq'] 		= $goodsSeq;
				$options['default_option'] 	= $defaultOption;
				$options['option_title'] 	= implode(',',$_POST['optionTitle']);

				$options['code_seq']	= ($_POST['optionType'])?str_replace("goodsoption_","",implode(',',$_POST['optionType'])):'';
				$options['option_type'] = ($_POST['optionType'])?implode(',',$_POST['optionType']):'direct';

				$options['option1'] 		= $opt0[$key];
				$options['option2'] 		= $opt1[$key];
				$options['option3'] 		= $opt2[$key];
				$options['option4'] 		= $opt3[$key];
				$options['option5'] 		= $opt4[$key];

				$options['optioncode1'] 		= $optcode0[$key];
				$options['optioncode2'] 		= $optcode1[$key];
				$options['optioncode3'] 		= $optcode2[$key];
				$options['optioncode4'] 		= $optcode3[$key];
				$options['optioncode5'] 		= $optcode4[$key];

				$options['infomation'] 		= $_POST['infomation'][$key];
				$options['coupon_input'] 	= (int) $_POST['coupon_input'][$key];
				$options['consumer_price'] 	= (int) $_POST['consumerPrice'][$key];
				$options['price'] 			= (int) $_POST['price'][$key];
				$options['reserve_rate'] 	= (int) $_POST['reserveRate'][$key];
				$options['reserve_unit'] 	= $_POST['reserveUnit'][$key];
				$options['reserve'] 		= (int) $_POST['reserve'][$key];
				$options['commission_rate'] = (int) $_POST['commissionRate'][$key];
				$result = $this->db->insert( 'fm_goods_option', $options );
				$supplys 					= array();
				$supplys['goods_seq'] 		= $goodsSeq;
				$supplys['supply_price'] 	= $_POST['supplyPrice'][$key];
				$supplys['option_seq'] 		= $this->db->insert_id();
				$supplys['stock'] 			= $_POST['stock'][$key];
				$supplys['badstock'] 		= $_POST['badstock'][$key];
				$supplys['reservation15'] 	= $_POST['reservation15'][$key];
				$supplys['reservation25'] 	= $_POST['reservation25'][$key];

				$this->db->insert( 'fm_goods_supply', $supplys );

			}
		}

		/* 총재고 수량 입력 */
		$this->goodsmodel->total_stock($goodsSeq);

		// 오픈마켓 옵션 가격 조정 정보 저장
		$this->load->model('openmarketmodel');
		$org_mallprice	= $this->openmarketmodel->get_linkage_option_price($goodsSeq);
		if	($_POST['market_tmp_seq']){
			$this->openmarketmodel->save_openmarket_option($goodsSeq, $_POST['market_tmp_seq']);
		}elseif	(count($org_mallprice) < 1){
			$this->openmarketmodel->save_openmarket_option($goodsSeq);
		}else{
			$this->openmarketmodel->chg_price_to_option($goodsSeq);
		}

		/* 추가옵션 */
		if	($_POST['subOptionUse']){
			if	($_POST['tmp_suboption_seq']){
				$this->goodsmodel->moveTmpToSubOption($goodsSeq, $_POST['tmp_suboption_seq']);
			}
		}else{
			$this->goodsmodel->delete_sub_option_info($goodsSeq);
		}
		/* 가용재고 재계산 */
		$this->goodsmodel->modify_reservation_real($goodsSeq,'manual');

		/* 구매자입력사항 */
		$this->db->delete('fm_goods_input', array('goods_seq' => $goodsSeq));
		foreach($_POST['memberInputName'] as $i => $input){
			if( ! isset($_POST['memberInputRequire'][$i]) ) $_POST['memberInputRequire'][$i] = '0';
			else $_POST['memberInputRequire'][$i] = '1';
			//$inputSeq = (int) $_POST['inputSeq'][$i];
			//$subopts['input_seq']		= $inputSeq;
			$inputs['goods_seq'] 		= $goodsSeq;
			$inputs['input_name'] 		= $input;
			$inputs['input_form']		= $_POST['memberInputForm'][$i];
			$inputs['input_limit'] 		= $_POST['memberInputLimit'][$i];
			$inputs['input_require']	= $_POST['memberInputRequire'][$i];
			$result = $this->db->insert('fm_goods_input', $inputs);
		}

		/* 추가이미지 수정 및 삭제시 기존 이미지 삭제 */
		$delImages = array();
		$oldImages = $this->goodsmodel->get_goods_image($goodsSeq);
		for($i=2;$oldImages[$i];$i++){
			foreach($oldImages[$i] as $key=>$data){
				$oldImage = $data['image'];
				if($oldImage){
					$exists=false;
					foreach($_POST[$key.'GoodsImage'] as $j=>$newImage){
						if($oldImage==$newImage){
							$exists=true;break;
						}
					}
					if(!$exists) $delImages[] = $oldImage;
				}
			}
		}
		foreach($delImages as $delImage) @unlink(ROOTPATH.$delImage);

		// 세팅이 변했을 경우, 이미지를 새로 올렸을 경우, 순서가 바꼈을 경우,

		/* 상품이미지 설정 저장 -> 새창페이지로 save_image_config() 변경 @2015-03-03 */

		/* 상품이미지 저장 */
		$this->goodsmodel->upload_goodsImage($_POST['largeGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['viewGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['list1GoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['list2GoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['thumbViewGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['thumbCartGoodsImage']);
		$this->goodsmodel->upload_goodsImage($_POST['thumbScrollGoodsImage']);

		/* 워터마크 이미지  삭제
		$this->load->model('watermarkmodel');
		$r_old_images = $this->goodsmodel->get_goods_image($goodsSeq);
		foreach($r_old_images as $data_image)
		{
			foreach($data_image as $image_type => $image)
			{
			 	$field = $image_type.'GoodsImage';
				if( !in_array($image['image'],$_POST[$field]) ){
					if($image['image']){
						$target_image = str_replace('//','/',ROOTPATH.$image['image']);
						$this->watermarkmodel->del_log($target_image);
					}
				}
			}
		}
		*/
		/* 워터마크 이미지  갱신
		$r_img_type = array('large','view','list1','list2');
		foreach($r_img_type as $img_type)
		{
			$field = $img_type.'GoodsImage';
			foreach($_POST[$field] as $image)
			{
				if( substr_count($image,'/data/tmp/') > 0 )
				{
					$from = $image;
					$to = $this->goodsmodel->get_target_goodsImage($image);

					$from = str_replace('//','/',ROOTPATH.$from);
					$to = str_replace('//','/',ROOTPATH.$to);
					$this->watermarkmodel->move_target_image($from,$to);
				}
			}
		}
		 */

		/* 이미지 */
		$this->db->delete('fm_goods_image', array('goods_seq' => $goodsSeq));
		$this->goodsmodel->insert_goodsImage('largeGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('viewGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('thumbViewGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('thumbCartGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('thumbScrollGoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('list1GoodsImage',$goodsSeq);
		$this->goodsmodel->insert_goodsImage('list2GoodsImage',$goodsSeq);

		// 개별컷 변경으로 경로 워터마크 이미지 경로 보정
		$this->load->model('watermarkmodel');
		$r_img_type = array('large','view','list1','list2');
		foreach($r_img_type as $img_type)
		{
			$field = $img_type.'GoodsImage';
			foreach($_POST[$field] as $image)
			{
				if( substr_count($image,'/data/tmp/') > 0 )
				{					
					$from = $image;
					$to = $this->goodsmodel->get_target_goodsImage($image);
					$from = str_replace('//','/',ROOTPATH.$from);
					$to = str_replace('//','/',ROOTPATH.$to);
					$this->watermarkmodel->move_target_image($from,$to);				
				}
			}
		}


		/* INFO */
		###
		$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';

		$common_contents	= adjustEditorImages($_POST['commonContents'], "/data/editor/");
		$params['info_value']	= $common_contents;

		$params['info_name']	= $_POST['info_name'];
		$params['regist_date']	= date("Y-m-d H:i:s");
		if($_POST['info_select']){	// UPDATE

			$info_name = explode("  [고유번호", $params['info_name']);
			$params['info_name'] = $info_name[0];

			$data = filter_keys($params, $this->db->list_fields('fm_goods_info'));
			$this->db->where('info_seq', $_POST['info_select']);
			$result = $this->db->update('fm_goods_info', $data);
		}else{						// INSERT
			if($params['info_name'] && $params['info_value']){
				$result = $this->db->insert('fm_goods_info', $params);
				$info_seq = $this->db->insert_id();

				$this->db->where('goods_seq', $goodsSeq);
				$result = $this->db->update('fm_goods', array('info_seq'=>$info_seq));
			}
		}

		/* RELATION */
		$this->db->delete('fm_goods_relation', array('goods_seq' => $goodsSeq));
		if($_POST['relation_type']=='MANUAL'){
			for($i=0;$i<count($_POST['relationGoods']);$i++){
				$result = $this->db->insert('fm_goods_relation', array('goods_seq'=>$goodsSeq,'relation_goods_seq'=>$_POST['relationGoods'][$i]));
			}
		}

		### RE OPTION CHECK
		$this->goodsmodel->option_check($goodsSeq);

		// 외부 쿠폰 저장
		if	($goods['coupon_serial_type'] == 'n' && $_POST['coupon_serial_upload']){
			$coupon_serial_list	= explode(',', $_POST['coupon_serial_upload']);
			foreach($coupon_serial_list as $k => $list){
				$data	= explode('|', $list);
				if	($data[0] && $data[1] == 'y'){
					if	(!$this->goodsmodel->chkDuple_coupon_serial($data[0])){
						$this->db->insert('fm_goods_coupon_serial', array('coupon_serial'=>$data[0],'goods_seq'=>$goodsSeq,'regist_date'=>date('Y-m-d H:i:s')));
					}
				}
			}
		}

		// 판매마켓 전송 요청
		if($_POST['goods_type']!='gift'){
			$this->openmarketmodel->request_send_goods($goodsSeq);

			// 디스플레이 캐시 삭제
			$this->load->model('goodsdisplay');
			$this->goodsdisplay->delete_display_cach();

			// 할인혜택 금액 저장
			$this->load->model('goodssummarymodel');
			$this->goodssummarymodel->set_event_price(array('goods'=>array($goodsSeq)));
		}

		if($result){
			$callback = "parent.document.location.reload();";
			if($_POST['goods_type']=='gift'){
				openDialogAlert("사은품이 수정 되었습니다.",400,140,'parent',$callback);
			}elseif($_POST['goods_kind'] == 'coupon'){
				openDialogAlert("쿠폰상품이 저장 되었습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert("상품이 수정 되었습니다.",400,140,'parent',$callback);
			}
		}
	}


	public function goods_copy(){
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(!$auth) exit;


		$oldSeq = $_GET['goods_seq'];

		### FM_GOODS
		$goodSeq = $this->goodsmodel->copy_goods($oldSeq);

		### GOODS_DEFAULT
		$result = $this->goodsmodel->copy_goods_default('fm_category_link', $oldSeq, $goodSeq, 'category_link_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_brand_link', $oldSeq, $goodSeq, 'category_link_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_addition', $oldSeq, $goodSeq, 'addition_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_icon', $oldSeq, $goodSeq, 'icon_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_input', $oldSeq, $goodSeq, 'input_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_relation', $oldSeq, $goodSeq, 'relation_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_location_link', $oldSeq, $goodSeq, 'location_link_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_socialcp_cancel', $oldSeq, $goodSeq, 'seq');

		### OPTION : fm_goods_option, fm_goods_suboption, fm_goods_supply
		$result = $this->goodsmodel->copy_goods_option($oldSeq, $goodSeq);
		/*
		$result = $this->goodsmodel->copy_goods_default('fm_goods_suboption', $oldSeq, $goodSeq, 'suboption_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_option', $oldSeq, $goodSeq, 'option_seq');
		$result = $this->goodsmodel->copy_goods_default('fm_goods_supply', $oldSeq, $goodSeq, 'supply_seq');
		*/

		/* 총재고 수량 입력 */
		$this->goodsmodel->total_stock($goodsSeq);

		### GOODS_IMAGE
		$result = $this->goodsmodel->copy_goods_image('fm_goods_image', $oldSeq, $goodSeq, 'image_seq');

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		// 할인혜택 금액 저장
		$this->load->model('goodssummarymodel');
		$this->goodssummarymodel->set_event_price(array('goods'=>array($goodSeq)));

		###
		//$result = "test L ".$goodsSeq;
		echo $goodSeq;
	}

	public function _icon_cleanup()
	{
		$icon_dir = "./data/icon/goods/";
		// 아이콘이미지가 없을경우 설정값 제거
		$r_icon = code_load('goodsIcon');
		foreach($r_icon as $icon){
			$icon_filename = $icon['codecd'].".gif";
			if( !file_exists( $icon_dir.$icon_filename ) ){
				code_save('goodsIcon',array($icon['codecd']=>''));
			}
		}
	}

	public function icon(){

		// 아이콘 이미지 정리
		$this->_icon_cleanup();

		$config['upload_path'] = "data/icon/goods/";
		$config['allowed_types'] = 'gif';
		$config['max_size']	= $this->config_system['uploadLimit'];
		$config['file_name'] = date('YmdHis').rand(0,9);
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('goodsIconImg') ) {
			$err = $this->upload->display_errors();
			openDialogAlert($err.'[gif 만 가능]',450,140,'parent',"");
			exit;
		}else{
			$fileInfo = $this->upload->data();
			code_save('goodsIcon',array($config['file_name']=>'사용자'));
			$callback = "parent.set_goods_icon();";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	public function del(){
		$no = (int) $_GET['no'];
		$arrDelTable = array(
			'fm_goods_list_summary',
			'fm_goods_option',
			'fm_goods_suboption',
			'fm_goods_supply',
			'fm_goods_input',
			'fm_goods_image',
			'fm_goods_icon',
			'fm_goods_addition',
			'fm_category_link',
			'fm_goods'
		);
		foreach($arrDelTable as $table){
			$query = "delete from `".$table."` where goods_seq=?";
			$this->db->query($query,array($no));
		}
	}


	public function get_info(){
		$seq	= $_GET['seq'];
		$query = $this->db->query("select * from fm_goods_info where info_seq = '{$seq}'");
		$data = $query->result_array();
		$contents = isset($data[0]['info_value']) ? $data[0]['info_value'] : " ";
		$result = array("contents"=>$contents);
		echo "[".json_encode($result)."]";
	}


	public function goods_delete(){
		$goods_arr = $_GET['goods_seq'];
		foreach($goods_arr as $k){
			$result	= $this->goodsmodel->delete_goods($k);
		}
		echo $result;
	}


	public function download_write(){
		## VALID
		$this->validation->set_rules('name', '이름', 'trim|required|xss_clean');		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		if(count($_POST['downloads_item_use'])<1){
			$callback = "parent.document.getElementsByName('name')[0].focus();";
			openDialogAlert("다운로드 항목을 1개 이상 설정해 주세요.",400,140,'parent',$callback);
			exit;
		}
		//print_r($_POST['downloads_item_use']);
		$item = implode("|",$_POST['downloads_item_use']);
		$params['name']			= $_POST['name'];
		$params['criteria']		= 'ITEM';
		$params['item']			= $item;

		$datas = get_data("fm_exceldownload",array("gb"=>'GOODS',"provider_seq"=>'1'));
		if(!$datas){
			$params['provider_seq']	= 1;
			$params['gb']	= 'GOODS';
			$result = $this->db->insert('fm_exceldownload', $params);
			$msg	= "등록 되었습니다.";
		}else{
			$this->db->where(array("gb"=>'GOODS',"provider_seq"=>'1'));
			$result = $this->db->update('fm_exceldownload', $params);
			$msg	= "수정 되었습니다.";
		}
		$func	= "parent.location.reload();";

		openDialogAlert($msg,400,140,'parent',$func);
	}


	public function excel_down(){
		$this->load->model('excelgoodsmodel');
		if(is_array($_POST)){
			$this->excelgoodsmodel->create_excel_list($_POST);
		}else{
			$this->excelgoodsmodel->create_excel_list($_GET);
		}
		exit;
	}

	//excel file down
	public function file_down(){
		$this->load->helper('download');
		if(is_file($_GET['realfiledir'])){
			$data = @file_get_contents($_GET['realfiledir']);
			force_download($_GET['filenames'], $data);
			exit;
		}
	}


	public function excel_upload(){
		###
		$config['upload_path']		= $path = ROOTPATH."/data/tmp/";
		$config['overwrite']			= TRUE;
		$this->load->library('upload');
		if (is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
			$file_ext = end(explode('.', $_FILES['excel_file']['name']));//확장자추출
			$config['allowed_types']	= 'xls';
			$config['file_name']			= 'goods_upload.'.$file_ext;
			$this->upload->initialize($config);
			if ($this->upload->do_upload('excel_file')) {
				$file_nm = $config['upload_path'].$config['file_name'];
				@chmod("{$file_nm}", 0777);
			}else{
				$callback = "";
				openDialogAlert("xls 파일만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}else{
			$callback = "";
			openDialogAlert("파일을 등록해 주세요.",400,140,'parent',$callback);
			exit;
		}
		$this->load->model('excelgoodsmodel');
		$result = $this->excelgoodsmodel->excel_upload($file_nm);
		$callback = "parent.location.reload();";
		openDialogAlert($result['msg'],600,140,'parent',$callback);
		exit;
	}

	public function goods_status_image_upload(){
		$file_ext = end(explode('.', $_FILES['goodsStatusImage']['name']));//확장자추출

		$data = code_load('goodsStatusImage',$_POST['goodsStatusImageCode']);

		$config['upload_path'] = "data/icon/goods_status/";
		$config['allowed_types'] = 'gif|png|jpg';
		$config['overwrite'] = true;
		$config['max_size']	= $this->config_system['uploadLimit'];
		$config['file_name'] = $data[0]['value'] ? $data[0]['value'] : $_POST['goodsStatusImageCode'].".".$file_ext;

		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('goodsStatusImage') ) {
			$err = $this->upload->display_errors();
		}else{
			$fileInfo = $this->upload->data();
			code_save('goodsStatusImage',array($_POST['goodsStatusImageCode']=>$config['file_name']));
			$callback = "parent.closeDialog('popGoodsStatusImageChoice');parent.$('#goodsStatusImage').click();";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	public function restock_notify_delete(){
		$seq_arr = $_GET['restock_notify_seq'];
		foreach($seq_arr as $k){
			$result	= $this->goodsmodel->delete_restock_notify($k);
		}
		echo $result;
	}

	public function restock_notify_send_sms(){
		### Validation
		if($_POST['send_num'] < 1){
			$callback = "";
			openDialogAlert('받는사람이 없습니다.',400,140,'parent',$callback);
			exit;
		}
		$this->validation->set_rules('send_message', '내용','trim|required|xss_clean');
		$this->validation->set_rules('send_sms', '보내는사람','trim|required|max_length[50]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$arrRestockNotifySeq = array();
		unset($phoneNo[0]);
		$key = get_shop_key();

		if(isset($_POST['add_num_chk'])!='Y'){
			switch($_POST['member']){
				case "all":
					$query = $this->db->query("select restock_notify_seq from fm_goods_restock_notify where notify_status = 'none' and cellphone<>''");
					$data = $query->result_array();
					if(count($data)>0){
						foreach($data as $k){
							array_push($arrRestockNotifySeq,$k['restock_notify_seq']);
						}
					}
					break;
				case "search":
					//echo urldecode($_POST["serialize"]);
					$tempArr = explode("&",urldecode($_POST["serialize"]));
					foreach($tempArr as $k){
						$tmp = explode("=",$k);
						if($tmp[1]){
							$sc[$tmp[0]] = $tmp[1];
						}
					}
					$sc['sms'] = 'y';
					$this->load->model('goodsmodel');
					$data = $this->goodsmodel->restock_notify_list($sc);
					if(count($data['record'])>0){
						foreach($data['record'] as $k){
							array_push($arrRestockNotifySeq,$k['restock_notify_seq']);
						}
					}
					break;
				case "select":
					$tempArr = explode(",", $_POST["serialize"]);
					unset($tempArr[0]);
					foreach($tempArr as $k){
						array_push($arrRestockNotifySeq,$k);
					}
					break;
				case "excel":
					break;
			}
		}
		
		$targetList = array();

		$query = $this->db->query("
			select
			a.restock_notify_seq,
			AES_DECRYPT(UNHEX(a.cellphone), '{$key}') as cellphone,
			b.goods_seq,
			b.goods_name,
			c.title1,
			c.option1,
			c.title2,
			c.option2,
			c.title3,
			c.option3,
			c.title4,
			c.option4,
			c.title5,
			c.option5
			from fm_goods_restock_notify as a
			inner join fm_goods as b on a.goods_seq = b.goods_seq
			left join fm_goods_restock_option as c on a.restock_notify_seq = c.restock_notify_seq
			where a.restock_notify_seq in ('".implode("','",$arrRestockNotifySeq)."') and a.notify_status='none'
		");

		foreach($query->result_array() as $v){
			$targetList[$v['goods_seq']]['phone'][] = $v['cellphone'];
			$targetList[$v['goods_seq']]['restock_notify_seq'][] = $v['restock_notify_seq'];
			$targetList[$v['goods_seq']]['goods_name'] = $v['goods_name'];
			$temp = "";
			if($v['option1'] && $v['title1']){
				$temp = $v['title1'].":".$v['option1']." ";
				if($v['option2'] && $v['title2']){
					$temp .= $v['title2'].":".$v['option2']." ";
				}
				if($v['option3'] && $v['title3']){
					$temp .= $v['title3'].":".$v['option3']." ";
				}
				if($v['option4'] && $v['title4']){
					$temp .= $v['title4'].":".$v['option4']." ";
				}
				if($v['option5'] && $v['title5']){
					$temp .= $v['title5'].":".$v['option5']." ";
				}
			}
			$targetList[$v['goods_seq']]['option'][] = $temp;
		}

		if($targetList){
			$smsCnt=0;
			$phoneNo = array();
			$msg = array();
			foreach($targetList as $goods_seq=>$v){
				$x = 0;
				$dataTo		= array();
				foreach($v['phone'] as $cnt=>$cellphone){
					$dataTo = $cellphone;
					$send_message = $_POST["send_message"];
					$send_message = str_replace("{상품고유값}",$goods_seq,$send_message);
					$send_message = str_replace("{상품명}",strip_tags($v['goods_name']),$send_message);
					$send_message = str_replace("{옵션}",strip_tags($v['option'][$x++]),$send_message);

					###
					$str = trim($send_message);
				
					$phoneNo[] = $dataTo;
					$msg[] = $str;

					$this->db->query("
						update fm_goods_restock_notify set notify_status='complete', notify_date=now() where restock_notify_seq in ('".implode("','",$v['restock_notify_seq'])."')
					");

					$smsCnt++;					
				}
			}

			$params['msg'] = $msg;
			$commonSmsData['restock']['phone'] = $phoneNo;;
			$commonSmsData['restock']['params'] = $params;
			
			$result = commonSendSMS($commonSmsData);


			$msg = $smsCnt."건 발송에 성공하였습니다.";
		}else{
				$msg	= "재입고알림을 통보할 고객이 없습니다.";
		}

		###
		$callback = "parent.document.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
		exit;
	}

	// 실제 주문을 검색하여 출고예약량을 업데이트합니다.
	public function all_modify_reservation($goods_seq=null)
	{
		set_time_limit(0);
		$this->load->model('ordermodel');

		$query = "select count(*) cnt from fm_order where step >= 15";
		$query = $this->db->query($query);
		$data = $query->row_array();
		if( $data['cnt'] == 0 && !$goods_seq ){
			$query = "update fm_goods_supply set reservation15=0,reservation25=0";
			$this->db->query($query);
		}else{

			$query = "update fm_goods_supply set reservation15 = 0,reservation25 = 0";
			if($goods_seq){
				$query .= " where goods_seq='$goods_seq'";
			}
			$this->db->query($query);

			$query = "update fm_goods_supply s,fm_goods g,fm_goods_option o set  s.reservation15 = (
						select
							sum(io.ea) ea from fm_order_item i,fm_order_item_option io
						where
							i.goods_seq=g.goods_seq
							and i.item_seq=io.item_seq
							and io.step >= 15
							and io.step <= 45
							and io.option1=o.option1
							and io.option2=o.option2
							and io.option3=o.option3
							and io.option4=o.option4
							and io.option5=o.option5
							)
			where g.goods_seq = o.goods_seq and o.option_seq=s.option_seq";
			if($goods_seq){
				$query .= " and g.goods_seq='$goods_seq'";
			}
			$this->db->query($query);


			$query = "update  fm_goods_supply s,fm_goods g,fm_goods_option o set  s.reservation25 = (
						select
							sum(io.ea) ea from fm_order_item i,fm_order_item_option io
						where
							i.goods_seq=g.goods_seq
							and i.item_seq=io.item_seq
							and io.step >= 25
							and io.step <= 45
							and io.option1=o.option1
							and io.option2=o.option2
							and io.option3=o.option3
							and io.option4=o.option4
							and io.option5=o.option5
							)
			where g.goods_seq = o.goods_seq and o.option_seq=s.option_seq";
			if($goods_seq){
				$query .= " and g.goods_seq='$goods_seq'";
			}
			$this->db->query($query);

			$query = "update  fm_goods_supply s,fm_goods g,fm_goods_suboption o set  s.reservation15 = (
						select
							sum(io.ea) ea from fm_order_item i,fm_order_item_suboption io
						where
							i.goods_seq=g.goods_seq
							and i.item_seq=io.item_seq
							and io.step >= 15
							and io.step <= 45
							and io.title = o.suboption_title
							and io.suboption = o.suboption
							)
			where g.goods_seq = o.goods_seq and o.suboption_seq=s.suboption_seq";
			if($goods_seq){
				$query .= " and g.goods_seq='$goods_seq'";
			}
			$this->db->query($query);

			$query = "update  fm_goods_supply s,fm_goods g,fm_goods_suboption o set  s.reservation25 = (
						select
							sum(io.ea) ea from fm_order_item i,fm_order_item_suboption io
						where
							i.goods_seq=g.goods_seq
							and i.item_seq=io.item_seq
							and io.step >= 25
							and io.step <= 45
							and io.title = o.suboption_title
							and io.suboption = o.suboption
							)
			where g.goods_seq = o.goods_seq and o.suboption_seq=s.suboption_seq";
			if($goods_seq){
				$query .= " and g.goods_seq='$goods_seq'";
			}
			$this->db->query($query);

			$query = "update fm_goods_supply set reservation15 = 0 where reservation15 is null";
			$this->db->query($query);

			$query = "update fm_goods_supply set reservation25 = 0 where reservation25 is null";
			$this->db->query($query);
		}

		config_save('reservation',array('update_date'=>date('Y-m-d H:i:s')));
		echo "OK";
	}

	/* 재고조정하기 */
	public function stock_modify(){

		$this->load->model('stockmodel');

		$process_ea_sum = array_sum($_POST['stock_ea']);

		if(!$process_ea_sum){
			$callback = "";
			if($_POST['reason']=='input'){
				openDialogAlert("입고 수량을 입력해주세요.",400,140,'parent',$callback);
				exit;
			}
			elseif(!$_POST['supply_price_replace']){
				openDialogAlert("조정 수량을 입력해주세요.",400,140,'parent',$callback);
				exit;
			}
		}

		/* 옵션 매입가격, 재고수량 조정 */
		if($_POST['mode']=='optionStockEdit'){

			foreach($_POST['stock_ea'] as $k=>$v){
				$opts = array();
				foreach($_POST['stock_opt'] as $k2=>$v2) $opts[] = $v2[$k];

				/* 보정수량(+/-) */
				$adjust_ea = $_POST['reason']=='input' ? $_POST['stock_ea'][$k] : -$_POST['stock_ea'][$k];

				$this->stockmodel->option_modify(
					$_POST['goods_seq'],
					$opts,
					$_POST['stock_supply_price'][$k],
					$adjust_ea,
					$_POST['reason'],
					$_POST['supply_price_replace']
				);

			}
		}

		/* 서브 매입가격, 재고수량 조정 */
		if($_POST['mode']=='subOptionStockEdit'){

			foreach($_POST['stock_ea'] as $k=>$v){
				$opts = array();
				foreach($_POST['stock_opt'] as $k2=>$v2) $opts[] = $v2[$k];

				/* 보정수량(+/-) */
				$adjust_ea = $_POST['reason']=='input' ? $_POST['stock_ea'][$k] : -$_POST['stock_ea'][$k];

				$this->stockmodel->suboption_modify(
					$_POST['goods_seq'],
					$opts,
					$_POST['stock_supply_price'][$k],
					$adjust_ea,
					$_POST['reason'],
					$_POST['supply_price_replace']
				);

			}
		}

		/* 재고조정 히스토리 저장 */
		$data = array();
		$data['reason'] = $_POST['reason'];
		if($_POST['reason']=='input'){
			$data['supplier_seq'] = $_POST['supplier_seq'];
			$data['reason_detail'] = '';
			$data['stock_date'] = $_POST['stock_date'];
		}else{
			$data['supplier_seq'] = '';
			$data['reason_detail'] = $_POST['reason_detail'];
			$data['stock_date'] = date('Y-m-d');
		}
		$stock_code = $this->stockmodel->insert_stock_history($data);

		foreach($_POST['stock_ea'] as $k=>$v){

			if($v || ($_POST['reason']!='input' && $_POST['supply_price_replace'] && $_POST['stock_prev_supply_price'][$k]!=$_POST['stock_supply_price'][$k])){
				$data = array();
				$data['option_type'] = $_POST['mode']=='optionStockEdit' ? 'option' : 'suboption';
				$data['stock_code'] = $stock_code;
				$data['goods_seq'] = $_POST['goods_seq'];
				if($_POST['reason']!='input'){
					// 분실,오류,불량,기타
					$data['prev_supply_price'] = $_POST['stock_prev_supply_price'][$k];
				}
				$data['supply_price'] = $_POST['stock_supply_price'][$k];
				$data['ea'] = $_POST['stock_ea'][$k];

				foreach($_POST['stock_opt'] as $j=>$v2){
					if(!empty($_POST['stock_opt'][$j][$k])){
						$data['title'.($j+1)] = $_POST['stock_opt_title'][$j];
						$data['option'.($j+1)] = $_POST['stock_opt'][$j][$k];
					}
				}

				$this->stockmodel->insert_stock_history_item($data);
			}
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("재고 조정 완료",400,140,'parent',$callback);


	}

	public function batch_modify()
	{
		#### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		$_POST['goods_kind'] = array('goods','coupon');

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

		$mode  = $_POST['mode'];
		$this->{'_batch_modify_'.$mode}();
	}

	public function _batch_modify_price()
	{
		if(!$_POST['goods_seq']){
			$callback = "";
			$msg = "수정할 상품을 체크하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		if(!$_POST['detail_supply_price']) $_POST['detail_supply_price'] = array();
		if(!$_POST['detail_consumer_price']) $_POST['detail_consumer_price'] = array();
		if(!$_POST['detail_price']) $_POST['detail_price'] = array();
		if(!$_POST['detail_stock']) $_POST['detail_stock'] = array();
		if(!$_POST['detail_reserve_rate']) $_POST['detail_reserve_rate'] = array();
		if(!$_POST['detail_reserve']) $_POST['detail_reserve'] = array();
		if(!$_POST['detail_reserve_unit']) $_POST['detail_reserve_unit'] = array();
		if(!$_POST['detail_default_option']) $_POST['detail_default_option'] = array();

		if(!$_POST['detail_shipping_policy']) $_POST['detail_shipping_policy'] = array();
		if(!$_POST['detail_unlimit_shipping_price']) $_POST['detail_unlimit_shipping_price'] = array();
		if(!$_POST['detail_reserve_policy']) $_POST['detail_reserve_policy'] = array();


		$r_option['supply_price'] = $_POST['supply_price'];
		$r_option['consumer_price'] = $_POST['consumer_price'];
		$r_option['price'] = $_POST['price'];

		// 재고/재고연동/상태/노출/승인 업데이트 페이지에서 처리하므로 체크함.
		if (isset($_POST['stock'])) {
			$r_option['stock'] = $_POST['stock'];
		}

		$r_option['reserve_rate'] = $_POST['reserve_rate'];
		$r_option['reserve'] = $_POST['reserve'];
		$r_option['reserve_unit'] = $_POST['reserve_unit'];

		if($_POST['detail_supply_price']){
			$r_option['supply_price']			= (array) $_POST['detail_supply_price'] + $_POST['supply_price'];
			$r_option['consumer_price']	= (array) $_POST['detail_consumer_price'] + $_POST['consumer_price'];
			$r_option['price']					= (array) $_POST['detail_price'] + $_POST['price'];

			if (isset($_POST['stock'])) {
				$r_option['stock']					= (array) $_POST['detail_stock'] + $_POST['stock'];
			}

			$r_option['reserve_rate']		= (array)  $_POST['detail_reserve_rate'] + $_POST['reserve_rate'];
			$r_option['reserve']				= (array) $_POST['detail_reserve'] + $_POST['reserve'] ;
			$r_option['reserve_unit']			= (array) $_POST['detail_reserve_unit'] + $_POST['reserve_unit'];
		}
		$r_goods['shipping_policy']				= (array) $_POST['detail_shipping_policy'] + $_POST['shipping_policy'];
		$r_goods['unlimit_shipping_price']	= (array) $_POST['detail_unlimit_shipping_price'] + $_POST['unlimit_shipping_price'];
		$r_goods['reserve_policy']				= (array) $_POST['detail_reserve_policy'] + $_POST['reserve_policy'];
		$r_goods['goods_status'] = $_POST['goods_status'];
		$r_goods['goods_view'] = $_POST['goods_view'];
		$r_default_option							= $_POST['detail_default_option'];

		// 상품기본정보 일괄 수정
		foreach($r_goods['shipping_policy'] as $goods_seq => $shipping_policy){
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;

			$r_goods_update = array();
			$update_bind = array();
			$r_set_query = array();
			if($r_goods['unlimit_shipping_price'][$goods_seq]) {
				$r_goods_update['shipping_policy'] ='goods';
				$r_goods_update['goods_shipping_policy'] ='unlimit';
				$r_goods_update['unlimit_shipping_price'] = $r_goods['unlimit_shipping_price'][$goods_seq];
			}
			if($r_goods['reserve_policy'][$goods_seq]) $r_goods_update['reserve_policy'] = $r_goods['reserve_policy'][$goods_seq];
			if($r_goods['goods_view'][$goods_seq]) $r_goods_update['goods_view'] = $r_goods['goods_view'][$goods_seq];
			if($r_goods['goods_status'][$goods_seq]) $r_goods_update['goods_status'] = $r_goods['goods_status'][$goods_seq];
			if($shipping_policy) $r_goods_update['shipping_policy'] = $shipping_policy;

			// 상품 업데이트일자 추가 leewh 2015-01-16
			$r_goods_update['update_date'] = date("Y-m-d H:i:s",time());

			foreach($r_goods_update as $update_field => $update_value){
				$r_set_query[] = "`".$update_field."`=?";
				$update_bind[] = $update_value;
			}
			$update_bind[] = $goods_seq;

			$query = "update fm_goods set ".implode(',',$r_set_query)." where goods_seq=?";
			$this->db->query($query,$update_bind);

		}

		foreach($r_default_option as $goods_seq => $option_seq){
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;
			$query = "update fm_goods_option set default_option='n' where goods_seq=?";
			$this->db->query($query,array($goods_seq));
			$query = "update fm_goods_option set default_option='y' where option_seq=?";
			$this->db->query($query,array($option_seq));
		}

		foreach($r_option['price'] as $option_seq => $price){
			$goods_seq = $_POST['option_seq'][$option_seq];
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;
			$price			= $r_option['price'][$option_seq];
			$supply_price	= $r_option['supply_price'][$option_seq];
			$consumer_price = $r_option['consumer_price'][$option_seq];
			$reserve_rate	= $r_option['reserve_rate'][$option_seq];
			$reserve		= $r_option['reserve'][$option_seq];
			$reserve_unit	= $r_option['reserve_unit'][$option_seq];
			if($reserve_unit=='percent'){
				$reserve = (int) ($price * $reserve_rate / 100);
			}

			if ($r_option['stock'][$option_seq]) $stock = $r_option['stock'][$option_seq];

			$query = "update fm_goods_option set consumer_price=?,price=?,reserve_rate=?,reserve_unit=?,reserve=? where option_seq=?";
			$this->db->query($query,array($consumer_price,$price,$reserve_rate,$reserve_unit,$reserve,$option_seq));

			if ($stock) {
				$query = "update fm_goods_supply set supply_price=?,stock=? where option_seq=?";
				$this->db->query($query,array($supply_price,$stock,$option_seq));
			} else {
				$query = "update fm_goods_supply set supply_price=? where option_seq=?";
				$this->db->query($query,array($supply_price,$option_seq));
			}

		}
		
		/* 총재고 수량 입력 */
		foreach($r_goods['shipping_policy'] as $goods_seq => $shipping_policy){
			$this->goodsmodel->total_stock($goods_seq);
		}

		// 다중판매처 관련 처리 추가
		$this->load->model('openmarketmodel');
		if	($_POST['goods_seq'])foreach($_POST['goods_seq'] as $g => $goods_seq){
			if	($goods_seq){
				$this->openmarketmodel->chg_price_to_option($goods_seq);
				$this->openmarketmodel->request_send_goods($goods_seq);
			}
		}

		// 할인혜택 금액 저장
		$this->load->model('goodssummarymodel');
		$this->goodssummarymodel->set_event_price(array('goods'=>$_POST['goods_seq']));

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	public function _batch_modify_ifprice()
	{
		if($_POST['modify_list'] == 'all'){
			$_GET = $_POST;
			$sc = $_GET;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$r_goods_seq[] = $data['goods_seq'];
			}
		}else{
			$r_goods_seq = $_POST['goods_seq'];
		}

		if(!$r_goods_seq){
			$callback = "";
			$msg = "수정할 상품이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		// 매입가 변경
		if( $_POST['batch_supply_price_yn'] == 1 ){

			$price = $_POST['batch_supply_price'];
			$unit = $_POST['batch_supply_price_unit'];
			$mode = $_POST['batch_supply_price_updown'];
			$field = "supply_price";
			$table = "fm_goods_supply";
			unset($cuttingsale);
			$cuttingsale['cutting_sale_yn']		= $_POST['batch_supply_price_cutting_sale_yn'];
			$cuttingsale['cutting_sale_price']	= $_POST['batch_supply_price_cutting_sale_price'];
			$cuttingsale['cutting_sale_action']	= $_POST['batch_supply_price_cutting_sale_action'];

			$r_query[$table][] = $this->_batch_modify_ifprice_set_query($price,$unit,$mode,$field, $cuttingsale);
			if($mode == 'down' ) {//인하인경우 인하가격이상인경우 조건추가
				$w_query[$table][$field]['price']		= $price;
				$w_query[$table][$field]['unit']		= $unit;
				$w_query[$table][$field]['mode']	= $mode;
				$w_query[$table][$field]['field']		= $field;
			}
		}

		// 소비자가 변경
		if( $_POST['consumer_price_yn'] == 1 ){

			$price = $_POST['batch_consumer_price'];
			$unit = $_POST['batch_consumer_price_unit'];
			$mode = $_POST['batch_consumer_price_updown'];
			$field = "consumer_price";
			$table = "fm_goods_option";
			unset($cuttingsale);
			$cuttingsale['cutting_sale_yn']		= $_POST['batch_consumer_price_cutting_sale_yn'];
			$cuttingsale['cutting_sale_price']	= $_POST['batch_consumer_price_cutting_sale_price'];
			$cuttingsale['cutting_sale_action']	= $_POST['batch_consumer_price_cutting_sale_action'];


			$r_query[$table][] = $this->_batch_modify_ifprice_set_query($price,$unit,$mode,$field, $cuttingsale);

			if($mode == 'down' ) {//인하인경우 인하가격이상인경우 조건추가
				$w_query[$table][$field]['price']		= $price;
				$w_query[$table][$field]['unit']		= $unit;
				$w_query[$table][$field]['mode']	= $mode;
				$w_query[$table][$field]['field']		= $field;
			}

		}

		// 판매가 변경
		if( $_POST['batch_price_yn'] == 1 ){

			$price = $_POST['batch_price'];
			$unit = $_POST['batch_price_unit'];
			$mode = $_POST['batch_price_updown'];
			$field = "price";
			$table = "fm_goods_option";

			unset($cuttingsale);
			$cuttingsale['cutting_sale_yn']		= $_POST['batch_price_cutting_sale_yn'];
			$cuttingsale['cutting_sale_price']	= $_POST['batch_price_cutting_sale_price'];
			$cuttingsale['cutting_sale_action']	= $_POST['batch_price_cutting_sale_action'];
			$r_query[$table][] = $this->_batch_modify_ifprice_set_query($price,$unit,$mode,$field, $cuttingsale);

			if($mode == 'down' ) {//인하인경우 인하가격이상인경우 조건추가
				$w_query[$table][$field]['price']		= $price;
				$w_query[$table][$field]['unit']		= $unit;
				$w_query[$table][$field]['mode']	= $mode;
				$w_query[$table][$field]['field']		= $field;
			}

		}

		// 재고변경
		if( $_POST['batch_stock_yn'] == 1 ){
			$price = $_POST['batch_stock'];
			$unit = 'won';
			$mode = $_POST['batch_stock_updown'];
			$field = "stock";
			$table = "fm_goods_supply";
			$r_query[$table][] = $this->_batch_modify_ifprice_set_query($price,$unit,$mode,$field);

			if($mode == 'down' ) {//인하인경우 인하가격이상인경우 조건추가
				$w_query[$table][$field]['price']		= $price;
				$w_query[$table][$field]['unit']		= $unit;
				$w_query[$table][$field]['mode']	= $mode;
				$w_query[$table][$field]['field']		= $field;
			}
		}

		// 상품상태 변경
		if( $_POST['batch_goods_status_yn'] == 1 ){
			$field = "goods_status";
			$table = "fm_goods";
			$r_query[$table][] =  $field." = '".$_POST['batch_goods_status']."'";
		}

		// 적립금 변경
		if( $_POST['batch_reserve_yn'] == 1 ){
			$field = "reserve_policy";
			$table = "fm_goods";
			$r_query[$table][] =  $field." = '".$_POST['batch_reserve_policy']."'";

			if($_POST['batch_reserve_policy'] == 'goods'){
				$price = $_POST['batch_reserve'];
				$unit = $_POST['batch_reserve_unit'];
				$table = "fm_goods_option";
				if($unit == 'percent') $field = "reserve_rate";
				else $field = "reserve";
				$r_query[$table][] =  $field." = ".$price;
				$r_query[$table][] =  "reserve_unit = '".$unit."'";
			}
		}

		// 배송비 변경
		if( $_POST['batch_shipping_yn'] == 1 ){
			$field = "shipping_policy";
			$table = "fm_goods";
			$r_query[$table][] =  $field." = '".$_POST['batch_shipping_policy']."'";

			if($_POST['batch_shipping_policy'] == 'goods' && $_POST['batch_unlimit_shipping_price']!='' ){
				$r_query[$table][] =  "goods_shipping_policy = 'unlimit'";
				$r_query[$table][] =  "unlimit_shipping_price = '".$_POST['batch_unlimit_shipping_price']."'";
			}
		}

		// 상품노출 변경
		if( $_POST['batch_goods_view_yn'] == 1 ){
			$field = "goods_view";
			$table = "fm_goods";
			$r_query[$table][] =  $field." = '".$_POST['batch_goods_view']."'";
		}

		// 상품노출 변경
		if( $_POST['batch_tax_yn'] == 1 ){
			$field = "tax";
			$table = "fm_goods";
			$r_query[$table][] =  $field." = '".$_POST['batch_tax']."'";
		}

		foreach($r_goods_seq as $goods_seq){
			foreach($r_query as $table => $set_str){
				unset($whereis);
				if($w_query[$table] && $mode == 'down'){
					foreach($w_query[$table] as $where_str) {
						unset($upgoods,$up_price);
						$this->db->limit(1,0);
						$this->db->where('goods_seq', $goods_seq);
						if($table == 'fm_goods_option' ){
							$this->db->where('default_option', 'y');//기본가추출
						}
						$upgoodsquery = $this->db->get($table);
						$upgoods = $upgoodsquery->result_array();
						$orgupgoodsprice = $upgoods[0][$where_str['field']];
						if( $where_str['unit'] == 'percent' ) {
							$up_price = ($orgupgoodsprice*$where_str['field']/100);
						}else{
							$up_price = $where_str['price'];
						}
						$whereis .= " and ".$where_str['field']." >= ".$up_price;//0원 초기화위해 = 구문추가
					}
				}

				// 상품 업데이트일자 추가 leewh 2015-01-16
				if ($table=="fm_goods") array_push($set_str, sprintf("update_date = '%s'", date("Y-m-d H:i:s",time())));

				$query = "update ".$table." set ".implode(',',$set_str)." where goods_seq=?".$whereis;
				$this->db->query($query,array($goods_seq));
				//debug_var($this->db->last_query());exit;

				/* 총재고 수량 입력 */
				$this->goodsmodel->total_stock($goods_seq);
			}
		}
		//exit;

		// 다중판매처 관련 처리 추가
		$this->load->model('openmarketmodel');
		if	($r_goods_seq)foreach($r_goods_seq as $goods_seq){
			if	($goods_seq){
				$this->openmarketmodel->chg_price_to_option($goods_seq);
				$this->openmarketmodel->request_send_goods($goods_seq);
			}
		}

		// 할인혜택 금액 저장
		$this->load->model('goodssummarymodel');
		$this->goodssummarymodel->set_event_price(array('goods'=>$r_goods_seq));

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	public function _batch_modify_ifprice_set_query($price,$unit,$mode,$field, $cuttingsale=null)
	{

		$price = (int) $price;
		$value_str = $field .( $mode=='up' ? "+" : "-" ) ;
		if($cuttingsale && $cuttingsale['cutting_sale_yn'] == 1 ){//절삭 사용시
			$cutting_sale_price =  $cuttingsale['cutting_sale_price'];//10, 100, 1000
				$cutting_sale_price_len = (strlen($cutting_sale_price)-1);//10, 100, 1000
			$cutting_sale_action = $cuttingsale['cutting_sale_action'];//rounding(반올림), ascending(올림), dscending(내림)

			if($unit == 'percent') $value_str .= "(".$field." * ".$price." / 100)";
			else $value_str .= $price;

			//ROUND(숫자,자릿수)  반올림
			//CEILING(숫자) = 값보다 큰 정수 중 가장 작은 수  올림
			//TRUNCATE(숫자,자릿수)  내림, 버림
			if( $cutting_sale_action == 'dscending' ) {//내림
				$value_str = " TRUNCATE((".$value_str."),-".$cutting_sale_price_len.")";
			}elseif( $cutting_sale_action == 'rounding' ){//반올림
				$value_str = " ROUND((".$value_str."),-".$cutting_sale_price_len.")";
			}elseif( $cutting_sale_action == 'ascending' ){//올림
				$value_str = " CEILING( ((".$value_str.")/".$cutting_sale_price."))*".$cutting_sale_price."";
			}
		}else{
			if($unit == 'percent') $value_str .= "(".$field." * ".$price." / 100)";
			else $value_str .= $price;
		}
		return $field." = ".$value_str;

	}

	public function _batch_modify_stock()
	{
		if(!$_POST['goods_seq']){
			$callback = "";
			$msg = "수정할 상품을 체크하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		// 상점 기본 재고정책
		$cfg_order = config_load('order');

		if(!$_POST['detail_stock']) $_POST['detail_stock'] = array();
		if(!$_POST['detail_default_option']) $_POST['detail_default_option'] = array();

		$r_goods['goods_seq']			= $_POST['goods_seq'];
		$r_goods['runout_type']			= $_POST['runout_type'];
		$r_goods['runout_policy']		= $_POST['runout_policy'];
		$r_goods['able_stock_limit']	= $_POST['able_stock_limit'];
		$r_option['stock']					= $_POST['stock'];

		if($_POST['detail_stock']){
			$r_option['stock']					= (array) $_POST['detail_stock'] + $_POST['stock'];
		}

		$r_goods['goods_status'] = $_POST['goods_status'];
		$r_goods['goods_view'] = $_POST['goods_view'];
		$r_default_option							= $_POST['detail_default_option'];

		foreach($r_default_option as $goods_seq => $option_seq){
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;
			$query = "update fm_goods_option set default_option='n' where goods_seq=?";
			$this->db->query($query,array($goods_seq));
			$query = "update fm_goods_option set default_option='y' where option_seq=?";
			$this->db->query($query,array($option_seq));
		}

		foreach($r_option['stock'] as $option_seq => $stock){
			$goods_seq = $_POST['option_seq'][$option_seq];
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;
			$stock = $r_option['stock'][$option_seq];

			$query = "update fm_goods_supply set stock=? where option_seq=?";
			$this->db->query($query,array($stock,$option_seq));
		}

		// 상품기본정보 일괄 수정
		$return_info_arr = array();
		foreach($r_goods['runout_type'] as $goods_seq => $runout_type) {
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;

			$r_goods_update = array();
			$update_bind = array();
			$r_set_query = array();

			// 노출
			if($r_goods['goods_view'][$goods_seq]) $r_goods_update['goods_view'] = $r_goods['goods_view'][$goods_seq];

			// 개별 정책
			if ($runout_type=='goods') {
				if ($r_goods['runout_policy'][$goods_seq]) {
					$runout = $r_goods['runout_policy'][$goods_seq];
					$r_goods_update['runout_policy'] = $r_goods['runout_policy'][$goods_seq];
				}

				$ableStockLimit = 0;
				if ($r_goods['runout_policy'][$goods_seq]=="ableStock") {
					$ableStockLimit = $r_goods['able_stock_limit'][$goods_seq]+1;
					$r_goods_update['able_stock_limit'] = $r_goods['able_stock_limit'][$goods_seq];
				}
			} else {
				$r_goods_update['runout_policy'] = '';
				$r_goods_update['able_stock_limit'] = 0;

				$runout = $cfg_order['runout'];
				$ableStockLimit = 0;

				if ($cfg_order['runout']=='ableStock') {
					$ableStockLimit = $cfg_order['ableStockLimit']+1;
				}
			}

			if($r_goods['goods_status'][$goods_seq]) {
				if($r_goods['goods_status'][$goods_seq] == "normal_runout") {
					// 정상 또는 품절 자동 계산
					$result_info_arr = $this->get_option_goods_status($goods_seq,$runout,$ableStockLimit,$cfg_order['ableStockStep']);

					// 정상에서 품절로 변경되는 상품명
					if ($result_info_arr['runout_gname']) {
						$return_info_arr['runout_gname'][] = $result_info_arr['runout_gname'];
					}

					// 품절에서 정상으로 변경되는 상품명
					if ($result_info_arr['normal_gname']) {
						$return_info_arr['normal_gname'][] = $result_info_arr['normal_gname'];
					}

					// 변경되는 상품 상태
					$r_goods_update['goods_status'] = $result_info_arr['goods_status'];

				} else {
					// 변경하는 재고와 상관없이 상태값 그대로 저장
					$r_goods_update['goods_status'] = $r_goods['goods_status'][$goods_seq];
				}
			}

			// 업데이트할 최종 재고 입력
			$get_tot_option = $this->goodsmodel->get_tot_option($goods_seq);
			$r_goods_update['tot_stock'] = $get_tot_option['stock'];
			$r_goods_update['update_date'] = date("Y-m-d H:i:s",time());

			foreach($r_goods_update as $update_field => $update_value){
				$r_set_query[] = "`".$update_field."`=?";
				$update_bind[] = $update_value;
			}
			$update_bind[] = $goods_seq;

			$query = "update fm_goods set ".implode(',',$r_set_query)." where goods_seq=?";
			$this->db->query($query,$update_bind);
		}

		if ($return_info_arr) {
			$tot_cnt = 0;
			$first_gname = "";
			$gname_table = "";
			$out_gname_table = "";

			$common_table = "<table class=\'info_stock_status_table\' align=\'center\'><thead><tr><th>고유값</th><th>상품명</th></tr></thead>%s</table>";

			// 품절 => 정상
			if (is_array($return_info_arr['normal_gname'])) {
				$tot_cnt += count($return_info_arr['normal_gname']);

				$cre_tr = "";
				foreach ($return_info_arr['normal_gname'] as $key =>$row) {
					if ($key==0) $first_gname = $row['goods_name'];
					$cre_tr .= sprintf("<tr><td>%s</td><td>%s</td></tr>",$row['goods_seq'],addslashes($row['goods_name']));
				}
				$gname_table = sprintf($common_table,$cre_tr);
			}

			// 정상 => 품절
			if (is_array($return_info_arr['runout_gname'])) {
				$tot_cnt += count($return_info_arr['runout_gname']);

				$cre_tr_runout = "";
				foreach ($return_info_arr['runout_gname'] as $key =>$row) {
					if ($key==0 && $first_gname=="") $first_gname = $row['goods_name'];
					$cre_tr_runout .= sprintf("<tr><td>%s</td><td>%s</td></tr>",$row['goods_seq'],addslashes($row['goods_name']));
				}
				$out_gname_table = sprintf($common_table,$cre_tr_runout);
			}

			$msg_cnt = ($tot_cnt>1) ? "외 ".($tot_cnt-1) : "1";
			$msg_str = "상품은 재고정책에 따라 ‘정상’ 에서 ‘품절’ 또는 ‘품절’ 에서 ‘정상’ 으로 변경이 되었습니다. 자세한 변경상품은 아래 버튼을 클릭하여 확인하실 수 있습니다.";
			$msg_show = sprintf("‘%s’ %s개의 %s",$first_gname,$msg_cnt,$msg_str);

			$result_json = array();
			$result_json['msg_show'] = $msg_show;
			$result_json['gname'] = ($gname_table) ? 1 : '';
			$result_json['out_gname'] = ($out_gname_table) ? 1 : '';
			$str_result_json = addslashes(json_encode($result_json));

			echo("<script>
				parent.popup_stock_modify_msg('".$str_result_json."');
				parent.set_table_dialog('dialog_normal_table', '".$gname_table."');
				parent.set_table_dialog('dialog_runout_table', '".$out_gname_table."');
			</script>");
			exit;

		} else {
			$msg = "상품정보가 변경 되었습니다.";
			$callback = "parent.location.reload();";
			openDialogAlert($msg,400,140,'parent',$callback);
		}

	}

	public function _batch_modify_goods()
	{

		if(!$_POST['goods_seq']){
			$callback = "";
			$msg = "수정할 상품을 체크하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$r_fieldname = array('goods_name','summary','info_select','relation_type','relation_count_w','relation_count_h','relation_image_size');
		foreach($_POST['goods_name'] as $goods_seq => $goods_name){
			if(!in_array($goods_seq,$_POST['goods_seq'])) continue;
			$r_field = array();
			$r_value = array();
			foreach($r_fieldname as $fieldname){
				if( $fieldname == 'goods_name' || $fieldname == 'summary' ) {//상품명/간략설명
					$r_value[] = trim($_POST[$fieldname][$goods_seq]);
				}else{
					$r_value[] = str_replace('"',htmlspecialchars('"', ENT_QUOTES),$_POST[$fieldname][$goods_seq]);
				}

				if($fieldname == 'info_select')$fieldname = 'info_seq';
				$r_field[] = $fieldname."=?";
			}

			// 상품 업데이트일자 추가 leewh 2015-01-16
			$r_field[] = "update_date=?";
			$r_value[] = date("Y-m-d H:i:s",time());

			$r_value[] = $goods_seq;
			$query = "update fm_goods set ".implode(',',$r_field)." where goods_seq=?";
			$this->db->query($query,$r_value);
		}

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}


	//상품명/간략설명/공용정보/관련정보 조건업데이트
	public function _batch_modify_ifgoods()
	{
		if($_POST['modify_list'] == 'all'){
			$_GET = $_POST;
			$sc = $_GET;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$r_goods_seq[] = $data['goods_seq'];
			}
		}else{
			$r_goods_seq = $_POST['goods_seq'];
		}

		if(!$r_goods_seq){
			$callback = "";
			$msg = "수정할 상품이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$r_fieldname = array('goods_name','summary','info_select','relation_type','relation_count_w','relation_count_h','relation_image_size');
		foreach($r_goods_seq as $goods_seq) {
			$r_field = array();
			$r_value = array();
			foreach($r_fieldname as $fieldname){
				if( $_POST['batch_'.$fieldname.'_yn'] == 1 || ($_POST['batch_relation_yn'] == 1 && strstr($fieldname,"relation")) ){
					if( $fieldname == 'goods_name' || $fieldname == 'summary' ) {//상품명/간략설명
						$r_value[] = trim($_POST['batch_'.$fieldname]);
					}else{
						$r_value[] = str_replace('"',htmlspecialchars('"', ENT_QUOTES),$_POST['batch_'.$fieldname]);
					}
					if($fieldname == 'info_select')$fieldname = 'info_seq';
					$r_field[] = $fieldname."=?";
				}
			}

			// 상품 업데이트일자 추가 leewh 2015-01-16
			$r_field[] = "update_date=?";
			$r_value[] = date("Y-m-d H:i:s",time());

			$r_value[] = $goods_seq;
			$query = "update fm_goods set ".implode(',',$r_field)." where goods_seq=?";
			$this->db->query($query,$r_value);
		}//endforeach

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}


	//상품코드 자동생성/청약철회 제한상품 업데이트
	public function _batch_modify_ifgoodscode()
	{
		if($_POST['modify_list'] == 'all'){
			$_GET = $_POST;
			$sc = $_GET;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$r_goods_seq[] = $data['goods_seq'];
			}
		}else{
			$r_goods_seq = $_POST['goods_seq'];
		}

		if(!$r_goods_seq){
			$callback = "";
			$msg = "수정할 상품이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}


		if(!$_POST['batch_cancel_type_yn'] && !$_POST['batch_goods_code_yn']){
			$callback = "";
			$msg = "업데이트 조건이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$r_fieldname = array('goods_code','cancel_type');
		foreach($r_goods_seq as $goods_seq) {
			$r_field = array();
			$r_value = array();
			foreach($r_fieldname as $fieldname){
				if( $_POST['batch_'.$fieldname.'_yn'] == 1 ){
					if($fieldname == 'goods_code'){
						$r_value[] =goodscodeautock($goods_seq,'batch');
					}else{
						$r_value[] = str_replace('"',htmlspecialchars('"', ENT_QUOTES),$_POST['batch_'.$fieldname]);
					}
					$r_field[] = $fieldname."=?";
				}
			}

			// 상품 업데이트일자 추가 leewh 2015-01-16
			$r_field[] = "update_date=?";
			$r_value[] = date("Y-m-d H:i:s",time());

			$r_value[] = $goods_seq;
			$query = "update fm_goods set ".implode(',',$r_field)." where goods_seq=?";
			$this->db->query($query,$r_value);
		}//endforeach

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	public function _batch_modify_category()
	{
		$mode = $_POST['target_modify'];
		$act = $_POST['search_'.$mode.'_mode'];

		if(($mode=='category' && $_POST['modify_list_category'] == 'all') || ($mode=='brand' && $_POST['modify_list_brand'] == 'all') || ($mode=='location' && $_POST['modify_list_location'] == 'all')){
			$_GET = $_POST;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$r_goods_seq[] = $data['goods_seq'];
			}
		}else{
			$r_goods_seq = $_POST['goods_seq'];
		}

		if(!$r_goods_seq){
			$callback = "";
			$msg = "수정할 상품이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		if($mode=='category'){
			$source_code = $_POST['category1'];
			if($_POST['category2']) $source_code = $_POST['category2'];
			if($_POST['category3']) $source_code = $_POST['category3'];
			if($_POST['category4']) $source_code = $_POST['category4'];
		}elseif($mode=='brand'){
			$source_code = $_POST['brands1'];
			if($_POST['brands2']) $source_code = $_POST['brands2'];
			if($_POST['brands3']) $source_code = $_POST['brands3'];
			if($_POST['brands4']) $source_code = $_POST['brands4'];
		}elseif($mode=='location'){
			$source_code = $_POST['location1'];
			if($_POST['location2']) $source_code = $_POST['location2'];
			if($_POST['location3']) $source_code = $_POST['location3'];
			if($_POST['location4']) $source_code = $_POST['location4'];
		}else{
			$callback = "";
			$msg = "error";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$code = $_POST[$act.'_'.$mode.'1'];
		if($_POST[$act.'_'.$mode.'2']) $code = $_POST[$act.'_'.$mode.'2'];
		if($_POST[$act.'_'.$mode.'3']) $code = $_POST[$act.'_'.$mode.'3'];
		if($_POST[$act.'_'.$mode.'4']) $code = $_POST[$act.'_'.$mode.'4'];


		if($act == 'del'){
			$this->{'_batch_modify_'.$act}($r_goods_seq,$code,$source_code,$mode,1,1);
		}else if($act){
			$this->{'_batch_modify_'.$act}($r_goods_seq,$code,$source_code,$mode);
		}

		// 할인혜택 금액 저장
		$this->load->model('goodssummarymodel');
		$this->goodssummarymodel->set_event_price(array('goods'=>$r_goods_seq));

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	//상품일괄업데이트 > 아이콘 업데이트
	public function _batch_modify_icon()
	{
		if($_POST['modify_list'] == 'all'){
			$_GET = $_POST;
			$sc = $_GET;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$r_goods_seq[] = $data['goods_seq'];
			}
		}else{
			$r_goods_seq = $_POST['goods_seq'];
		}

		if(!$r_goods_seq){
			$callback = "";
			$msg = "수정할 상품이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		//일괄변경아이콘
		//선택(전체)상품의 아이콘을 체크박스의 필요성이 없다하여 삭제 2014-08-08 jhr
//		if( $_POST['batch_goodsIconCode_yn'] == 1 ){
			$r_icon = $_POST['batch_goodsIconCode'];
			$start_date = $_POST['batch_iconstartDate'];
			$end_date = $_POST['batch_iconendDate'];
			foreach($r_goods_seq as $goods_seq) {
				$query = "delete from fm_goods_icon where goods_seq=?";
				$this->db->query($query,array($goods_seq));
				if($r_icon){
					foreach($r_icon as $codecd){
						$query = "insert into fm_goods_icon set goods_seq=?,codecd=?,start_date=?,end_date=?";
						$this->db->query($query,array($goods_seq,$codecd,$start_date,$end_date));
					}
				}
			}//endforeach

//		}else{
//			foreach($r_goods_seq as $goods_seq) {
//				$iconSeq	= $_POST['iconSeq'][$goods_seq];
//				if($iconSeq){
//					foreach($iconSeq as $seq){
//						$start_date = $_POST['iconstartDate'][$goods_seq][$seq];
//						$end_date	= $_POST['iconendDate'][$goods_seq][$seq];
//						$this->db->where('icon_seq', $seq);
//						$this->db->update('fm_goods_icon',array('start_date'=>$start_date,'end_date'=>$end_date));
//					}
//				}
//			}
//		}

		// 상품 수정일시 변경
		if	(is_array($r_goods_seq) && count($r_goods_seq) > 0){
			$sql	= "update fm_goods set update_date = '".date('Y-m-d H:i:s')."' where goods_seq in ('".implode("', '", $r_goods_seq)."') ";
			$this->db->query($sql);

			// 할인혜택 금액 저장
			$this->load->model('goodssummarymodel');
			$this->goodssummarymodel->set_event_price(array('goods'=>$r_goods_seq));
		}

		$msg = "상품의 아이콘이 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	public function _batch_modify_add($r_goods_seq,$code,$source_code,$mode)
	{
		if(!$code){
			$callback = "";
			if($mode == 'category') $msg = "연결할 카테고리를 선택하세요!";
			else  $msg = "연결할 브랜드를 선택하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('locationmodel');
		if	($mode == 'category')
			$minsort	= $this->categorymodel->getSortValue($code, 'min');
		else if	($mode == 'brand')
			$minsort	= $this->brandmodel->getSortValue($code, 'min');
		else if	($mode == 'location')
			$minsort	= $this->locationmodel->getSortValue($code, 'min');
		else{
			$msg = "error";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$table = "fm_".$mode."_link";
		$r_code = $this->categorymodel->split_category($code);
		foreach($r_goods_seq as $goods_seq){
			$query = "select count(*) cnt from ".$table." where goods_seq=? and link";
			$query = $this->db->query($query,array($goods_seq));
			$data = $query->row_array();
			$link_cnt = $data['cnt'];

			if($mode=='location'){
				foreach($r_code as $k => $location_code){
					$last_k = count($r_code)-1;
					$query = "select count(*) cnt from ".$table." where goods_seq=? and location_code=?";
					$query = $this->db->query($query,array($goods_seq,$location_code));
					$data = $query->row_array();
					if($data['cnt'] > 0) continue;

					$r_insert['link'] = 0;
					if($link_cnt == 0 && $last_k == $k)$r_insert['link'] = 1;
					$r_insert['location_code'] = $location_code;
					$r_insert['goods_seq'] = $goods_seq;
					$r_insert['regist_date'] = date('Y-m-d H:i:s',time());
					$result = $this->db->insert($table, $r_insert);
					if($mode == 'category'){
						$link_seq = $this->db->insert_id();
						$this->db->where('location_link_seq', $link_seq);
						$this->db->update($table,array('sort'=>$minsort-1));
					}
				}
			}else{
				foreach($r_code as $k => $category_code){
					$last_k = count($r_code)-1;
					$query = "select count(*) cnt from ".$table." where goods_seq=? and category_code=?";
					$query = $this->db->query($query,array($goods_seq,$category_code));
					$data = $query->row_array();
					if($data['cnt'] > 0) continue;

					$r_insert['link'] = 0;
					if($link_cnt == 0 && $last_k == $k)$r_insert['link'] = 1;
					$r_insert['category_code'] = $category_code;
					$r_insert['goods_seq'] = $goods_seq;
					$r_insert['regist_date'] = date('Y-m-d H:i:s',time());
					$result = $this->db->insert($table, $r_insert);
					if($mode == 'category'){
						$link_seq = $this->db->insert_id();
						$this->db->where('category_link_seq', $link_seq);
						$this->db->update($table,array('sort'=>$minsort-1));
					}
				}
			}
		}
	}

	public function _batch_modify_move($r_goods_seq,$code,$source_code,$mode)
	{
		if(!$code){
			$callback = "";
			if($mode == 'category') $msg = "연결할 카테고리를 선택하세요!";
			else  $msg = "연결할 브랜드를 선택하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}
		$this->_batch_modify_del($r_goods_seq,$code,$source_code,$mode,1);
		$this->_batch_modify_add($r_goods_seq,$code,$source_code,$mode);
	}

	public function _batch_modify_copy($r_goods_seq,$code,$source_code,$mode)
	{

		if(!$code){
			$callback = "";
			if($mode == 'category') $msg = "연결할 카테고리를 선택하세요!";
			else  $msg = "연결할 브랜드를 선택하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		foreach($r_goods_seq as $goods_seq){
			$oldSeq = $goods_seq;

			### FM_GOODS
			$goodSeq = $this->goodsmodel->copy_goods($oldSeq);

			### GOODS_DEFAULT
			if($mode == 'brand') {
				$result = $this->goodsmodel->copy_goods_default('fm_category_link', $oldSeq, $goodSeq, 'category_link_seq');
				$result = $this->goodsmodel->copy_goods_default('fm_location_link', $oldSeq, $goodSeq, 'location_link_seq');
			}
			if($mode == 'category') {
				$result = $this->goodsmodel->copy_goods_default('fm_brand_link', $oldSeq, $goodSeq, 'category_link_seq');
				$result = $this->goodsmodel->copy_goods_default('fm_location_link', $oldSeq, $goodSeq, 'location_link_seq');
			}
			if($mode == 'location') {
				$result = $this->goodsmodel->copy_goods_default('fm_category_link', $oldSeq, $goodSeq, 'category_link_seq');
				$result = $this->goodsmodel->copy_goods_default('fm_brand_link', $oldSeq, $goodSeq, 'category_link_seq');
			}
			$result = $this->goodsmodel->copy_goods_default('fm_goods_addition', $oldSeq, $goodSeq, 'addition_seq');
			$result = $this->goodsmodel->copy_goods_default('fm_goods_icon', $oldSeq, $goodSeq, 'icon_seq');
			$result = $this->goodsmodel->copy_goods_default('fm_goods_input', $oldSeq, $goodSeq, 'input_seq');
			$result = $this->goodsmodel->copy_goods_default('fm_goods_relation', $oldSeq, $goodSeq, 'relation_seq');
			$result = $this->goodsmodel->copy_goods_default('fm_goods_socialcp_cancel', $oldSeq, $goodSeq, 'seq');

			### OPTION : fm_goods_option, fm_goods_suboption, fm_goods_supply
			$result = $this->goodsmodel->copy_goods_option($oldSeq, $goodSeq);

			### GOODS_IMAGE
			$result = $this->goodsmodel->copy_goods_image('fm_goods_image', $oldSeq, $goodSeq, 'image_seq');
			$r_new_goods_seq[] = $goodSeq;
		}

		$this->_batch_modify_add($r_new_goods_seq,$code,$source_code,$mode);
	}

	public function _batch_modify_del($r_goods_seq,$code,$source_code,$mode,$move_act=0,$except_link=0)
	{

		if(!$source_code){
			$callback = "";
			if($mode == 'category') $msg = "카테고리를 검색하세요!";
			else  $msg = "브랜드를 검색하세요!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$table = "fm_".$mode."_link";
		foreach($r_goods_seq as $goods_seq){
			if($mode=='location'){
				if( $move_act == 1 ){
					$query = "delete from ".$table." where goods_seq=? and location_code like '".$source_code."%'";
				}else{
					$query = "delete from ".$table." where goods_seq=? and location_code not like '".substr($source_code,0,4)."%'";
				}
				$cnt = 0;
				if($except_link){
					$query_except = "select count(*) cnt from $table where link=1 and location_code like '".$source_code."%' and goods_seq=?";
					$query_except =  $this->db->query($query_except,array($goods_seq));
					$data = $query_except->row_array();
					$cnt = $data['cnt'];
				}
				if($cnt==0) $this->db->query($query,array($goods_seq));
			}else{
				if( $move_act == 1 ){
					$query = "delete from ".$table." where goods_seq=? and category_code like '".$source_code."%'";
				}else{
					$query = "delete from ".$table." where goods_seq=? and category_code not like '".substr($source_code,0,4)."%'";
				}
				$cnt = 0;
				if($except_link){
					$query_except = "select count(*) cnt from $table where link=1 and category_code like '".$source_code."%' and goods_seq=?";
					$query_except =  $this->db->query($query_except,array($goods_seq));
					$data = $query_except->row_array();
					$cnt = $data['cnt'];
				}
				if($cnt==0) $this->db->query($query,array($goods_seq));
			}
		}
	}

	public function _batch_modify_all_del($r_goods_seq,$code,$source_code,$mode)
	{
		$table = "fm_".$mode."_link";
		foreach($r_goods_seq as $goods_seq){
			$query = "delete from ".$table." where goods_seq=?";
			$this->db->query($query,array($goods_seq));
		}
	}

	// 상품코드를 일괄 업데이트하기
	public function batch_goodscode_all()
	{
		set_time_limit(0);
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$sc['limitnum'] = 500;//300

		### GOODS
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('locationmodel');

		$loop = $this->goodsmodel->goodscode_batch_goods_list($sc);

		### PAGE & DATA
		$query = "select count(*) cnt from fm_goods where goods_type = 'goods' ";
		$query = $this->db->query($query);
		$data = $query->row_array();
		$all_count = $data['cnt'];

		$idx = 0;
		foreach($loop['record'] as $k => $datarow) {
			goodscodeautock($datarow['goods_seq']);
			$idx++;
		}

		if( ($_GET['page']>1 && $all_count <= ( ($sc['limitnum']*$_GET['page'])) ) || ($_GET['page']==1 && $all_count == $idx ) ) {
			$cfg_goodscodebatch = config_load('goodscodebatch');
			config_save('goodscodebatch',array('update_date'=>$cfg_goodscodebatch['update_date'].'\n'.date('Y-m-d H:i:s')));
			$status = 'FINISH';
		}else{
			$status = 'NEXT';
		}

		$totalpage = @ceil($all_count/ $sc['limitnum']);

		$result = array('status' => $status,'totalcount'=>$all_count,'nextpage'=>($_GET['page']+1),'totalpage'=>$totalpage);
		echo json_encode($result);
		exit;
	}

	//상품코드 자동생성
	public function tmpgoodscode()
	{
		//상품코드 자동등록처리 @2013-02-07
		$returncode = goodscodeautockview();
		echo $returncode;
	}

	function watermark_goods()
	{
		$target_imgs = explode('|',urldecode($_POST['target_image']));
		$this->load->model('watermarkmodel');

		$result = "FALSE";
		if($target_imgs[0]){
			foreach($target_imgs as $target_image){
				if($target_image){
					$this->watermarkmodel->goods_seq = $_POST['goods_seq'];
					$this->watermarkmodel->target_image = str_replace('//','/',ROOTPATH.$target_image);
					$this->watermarkmodel->source_image_cp();

					$result = $this->watermarkmodel->watermark();
				}
			}

		}

		echo $result;
	}

	function watermark_recovery()
	{
		$target_imgs = explode('|',urldecode($_POST['target_image']));
		$this->load->model('watermarkmodel');

		$result = "FALSE";
		if($target_imgs[0]){
			foreach($target_imgs as $target_image){
				if($target_image){
					$this->watermarkmodel->goods_seq = $_POST['goods_seq'];
					$this->watermarkmodel->target_image = str_replace('//','/',ROOTPATH.$target_image);
					$this->watermarkmodel->recovery();
					$result = "OK";
				}
			}
		}
		echo $result;
	}

	public function _batch_modify_watermark()
	{
		if(!$_POST['goods_seq']){
			$msg = "상품을 선택하여 주세요.";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}

		$this->load->model('watermarkmodel');
		$this->watermarkmodel->watermark_setting();

		$r_target_type = array('large','view');
		foreach($_POST['goods_seq'] as $goods_seq){
			$r_images = $this->goodsmodel->get_goods_image($goods_seq);

			$this->watermarkmodel->goods_seq = $goods_seq;
			foreach($r_images as $r_image){
				for($i=0;$i<4;$i++){
					$field = $r_target_type[$i];
					$image = $r_image[$field]['image'];
					$image_src = str_replace('//','/',ROOTPATH.$image);

					if( $image && file_exists($image_src) )
					{
						$this->watermarkmodel->target_image = $image_src;
						if($_POST['remove_watermark']==1){
							$this->watermarkmodel->recovery();
						}else{
							$this->watermarkmodel->source_image_cp();
							$this->watermarkmodel->watermark();
						}
					}
				}
			}
		}

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);

	}

	## 판매마켓 상태 및 금액 일괄조정
	public function _batch_modify_mprice(){

		$this->load->model('openmarketmodel');

		$target_type			= trim($_POST['target_type']);
		$set_status_openmarket	= trim($_POST['set_status_openmarket']);
		$openmarket_use_status	= trim($_POST['openmarket_use_status']);
		$openmarket_use_price	= trim($_POST['openmarket_use_price']);
		$openmarket_status		= trim($_POST['openmarket_status']);
		$set_price_openmarket	= trim($_POST['set_price_openmarket']);
		$top_sel_mall			= trim($_POST['top_sel_mall']);
		$revision_set_data		= trim($_POST['revision_set_data']);
		$set_revision_val		= trim($_POST['set_revision_val']);
		$set_revision_unit		= trim($_POST['set_revision_unit']);
		$set_revision_type		= trim($_POST['set_revision_type']);
		$revision_set			= trim($_POST['revision_set']);
		$goods_seq				= $_POST['goods_seq'];

		// 연동 설정
		$linkage	= $this->openmarketmodel->get_linkage_config();
		if	($linkage['cut_price_unit'] == 'y'){
			$cut_arr	= array('unit'	=> $linkage['cut_price_unit'],
								'type'	=> $linkage['cut_price_type']);
		}

		// 설정 몰 목록
		$mall		= $this->openmarketmodel->get_linkage_mall('code');


		// 적용 대상 ( 전체 )
		if	($target_type == 'all'){
			unset($goods_seq);
			$_GET = $_POST;
			$sc = $_GET;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$goods_seq[] = $data['goods_seq'];
			}
		}

		if	(is_array($goods_seq) && count($goods_seq) > 0){
			$addWhere	= " and goods_seq in ('".implode("', '", $goods_seq)."') ";

			// 상태변경 처리
			if	($openmarket_use_status == 'y'){
				if	($set_status_openmarket != 'all'){
					$addWhere_status	= " and mall_code = '".$set_status_openmarket."' ";
				}
				$addWhere1	= $addWhere . $addWhere_status;

				// 기존 데이터 삭제
				$sql	= "delete from fm_linkage_goods_mall where goods_seq > 0 ".$addWhere1;
				$this->db->query($sql);
				if	($openmarket_status == 'send'){
					if	($set_status_openmarket == 'all'){
						// 마켓 데이터 추가
						$sql	= "select * from fm_linkage_mall group by mall_code ";
						$query	= $this->db->query($sql);
						$result	= $query->result_array();
						if	($result)foreach($result as $k => $set_mall){
							if	(!in_array($set_mall['mall_code'], $bf_mall_code)){
								foreach($goods_seq as $g => $gseq){
									unset($sInsParam);
									$sInsParam['goods_seq']	= $gseq;
									$sInsParam['mall_code']	= $set_mall['mall_code'];
									$sInsParam['mall_name']	= $set_mall['mall_name'];
									$sInsParam['mall_key']	= $set_mall['mall_key'];
									$this->db->insert('fm_linkage_goods_mall', $sInsParam);
								}
							}
							$bf_mall_code[]		= $set_mall['mall_code'];
						}
					}else{
						foreach($goods_seq as $g => $gseq){
							unset($sInsParam);
							$sInsParam['goods_seq']	= $gseq;
							$sInsParam['mall_code']	= $set_status_openmarket;
							$sInsParam['mall_name']	= $mall[$set_status_openmarket]['mall_name'];
							$sInsParam['mall_key']	= $mall[$set_status_openmarket]['mall_key'];
							$this->db->insert('fm_linkage_goods_mall', $sInsParam);
						}
					}
				}
			}

			// 금액변경 처리
			if	($openmarket_use_price == 'y'){
				$addWhere2	= $addWhere;
				if	($set_price_openmarket != 'all'){
					$addWhere2 .= " and mall_code = '".$set_price_openmarket."' ";
				}

				if	($top_sel_mall == 'direct'){
					$cal_type			= 'manual';
					$mall_code			= $set_price_openmarket;
					$mall_name			= $mall[$set_price_openmarket]['mall_name'];
				}else{
					$cal_type			= 'auto';
					$mall_code			= $set_price_openmarket;
					$mall_name			= $mall[$set_price_openmarket]['mall_name'];
					$revision_arr		= explode('|', $revision_set_data);
					$set_revision_val	= $revision_arr[0];
					$set_revision_unit	= $revision_arr[1];
					$set_revision_type	= $revision_arr[2];
				}

				// 기존 데이터 삭제
				$sql		= "delete from fm_linkage_goods_config where goods_seq > 0 ".$addWhere2;
				$this->db->query($sql);
 				$sql		= "delete from fm_linkage_goods_price where goods_seq > 0 ".$addWhere2;
				$this->db->query($sql);

				$sql		= "select * from fm_goods_option where option_seq > 0 ". $addWhere;
				$query		= $this->db->query($sql);
				$result		= $query->result_array();
				if	($result && $set_price_openmarket == 'all'){
					foreach($result as $k => $opt){
						foreach($mall as $m => $malldata){
							$key_code	= $opt['goods_seq'] . '-' . $malldata['mall_code'];
							// 설정 값 추가
							if	(!in_array($key_code, $end_key_code)){
								unset($insParam);
								$insParam['goods_seq']		= $opt['goods_seq'];
								$insParam['option_type']	= 'opt';
								$insParam['cal_type']		= $cal_type;
								$insParam['mall_code']		= $malldata['mall_code'];
								$insParam['mall_name']		= $malldata['mall_name'];
								$insParam['revision_val']	= $set_revision_val;
								$insParam['revision_unit']	= $set_revision_unit;
								$insParam['revision_type']	= $set_revision_type;
								$this->db->insert('fm_linkage_goods_config', $insParam);
							}
							$end_key_code[$key_code]	= $key_code;

							// 금액 데이터 추가
							unset($insParam);
							$consumer_price	= $this->openmarketmodel->calRevision($opt['consumer_price'], $set_revision_val, $set_revision_type, $set_revision_unit, $cut_arr);
							$supply_price	= $this->openmarketmodel->calRevision($opt['supply_price'], $set_revision_val, $set_revision_type, $set_revision_unit, $cut_arr);
							$sale_price		= $this->openmarketmodel->calRevision($opt['price'], $set_revision_val, $set_revision_type, $set_revision_unit, $cut_arr);
							$insParam['linkage_seq']			= $linkage['linkage_seq'];
							$insParam['mall_code']				= $malldata['mall_code'];
							$insParam['mall_name']				= $malldata['mall_name'];
							$insParam['goods_seq']				= $opt['goods_seq'];
							$insParam['option_seq']				= $opt['option_seq'];
							$insParam['suboption_seq']			= '';
							$insParam['option_title']			= $opt['option_title'];
							$insParam['option1']				= $opt['option1'];
							$insParam['option2']				= $opt['option2'];
							$insParam['option3']				= $opt['option3'];
							$insParam['option4']				= $opt['option4'];
							$insParam['option5']				= $opt['option5'];
							$insParam['shop_consumer_price']	= $opt['consumer_price'];
							$insParam['shop_supply_price']		= $opt['supply_price'];
							$insParam['shop_sale_price']		= $opt['price'];
							$insParam['shop_margin']			= $opt['price'] - $opt['supply_price'];
							$insParam['consumer_price']			= $consumer_price;
							$insParam['supply_price']			= $supply_price;
							$insParam['sale_price']				= $sale_price;
							$insParam['margin']					= $sale_price - $supply_price;
							$insParam['regist_date']			= date('Y-m-d H:i:s');
							$this->db->insert('fm_linkage_goods_price', $insParam);
						}
					}
				}elseif	($result){
					foreach($result as $k => $opt){
						// 설정 값 추가
						if	(!in_array($opt['goods_seq'], $end_goods_seq)){
							unset($insParam);
							$insParam['goods_seq']		= $opt['goods_seq'];
							$insParam['option_type']	= 'opt';
							$insParam['cal_type']		= $cal_type;
							$insParam['mall_code']		= $mall_code;
							$insParam['mall_name']		= $mall_name;
							$insParam['revision_val']	= $set_revision_val;
							$insParam['revision_unit']	= $set_revision_unit;
							$insParam['revision_type']	= $set_revision_type;
							$this->db->insert('fm_linkage_goods_config', $insParam);
						}
						$end_goods_seq[$opt['goods_seq']]	= $opt['goods_seq'];

						// 설정 값 추가
						unset($insParam);
						$consumer_price	= $this->openmarketmodel->calRevision($opt['consumer_price'], $set_revision_val, $set_revision_type, $set_revision_unit, $cut_arr);
						$supply_price	= $this->openmarketmodel->calRevision($opt['supply_price'], $set_revision_val, $set_revision_type, $set_revision_unit, $cut_arr);
						$sale_price		= $this->openmarketmodel->calRevision($opt['price'], $set_revision_val, $set_revision_type, $set_revision_unit, $cut_arr);
						$insParam['linkage_seq']			= $linkage['linkage_seq'];
						$insParam['mall_code']				= $mall_code;
						$insParam['mall_name']				= $mall_name;
						$insParam['goods_seq']				= $opt['goods_seq'];
						$insParam['option_seq']				= $opt['option_seq'];
						$insParam['suboption_seq']			= '';
						$insParam['option_title']			= $opt['option_title'];
						$insParam['option1']				= $opt['option1'];
						$insParam['option2']				= $opt['option2'];
						$insParam['option3']				= $opt['option3'];
						$insParam['option4']				= $opt['option4'];
						$insParam['option5']				= $opt['option5'];
						$insParam['shop_consumer_price']	= $opt['consumer_price'];
						$insParam['shop_supply_price']		= $opt['supply_price'];
						$insParam['shop_sale_price']		= $opt['price'];
						$insParam['shop_margin']			= $opt['price'] - $opt['supply_price'];
						$insParam['consumer_price']			= $consumer_price;
						$insParam['supply_price']			= $supply_price;
						$insParam['sale_price']				= $sale_price;
						$insParam['margin']					= $sale_price - $supply_price;
						$insParam['regist_date']			= date('Y-m-d H:i:s');
						$this->db->insert('fm_linkage_goods_price', $insParam);
					}
				}
			}

			// 판매마켓 전송 요청
			foreach($goods_seq as $g => $gseq){
				$this->openmarketmodel->request_send_goods($gseq);
			}
		}

		$msg = "상품정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	//상품일괄업데이트 > 아이콘업데이트 : 개별 아이콘 삭제
	public function goods_icon_del(){
		$goodSeq = $_GET["goods_seq"];
		$icon_seq = $_GET["icon_seq"];
		if( $goodSeq && $icon_seq ){
			$result = $this->db->delete('fm_goods_icon', array('goods_seq' => $goodSeq,'icon_seq' => $icon_seq));
		}else{
			$result = false;
		}
		echo json_encode($result);
		exit;
	}

	public function goods_info_del(){
		$info_seq = $_GET["seq"];
		$result = $this->db->delete('fm_goods_info', array('info_seq' => $info_seq));
		echo json_encode($result);
		exit;
	}

	/**
	* 필수옵션 임시옵션정보 생성 (opt 1단계)
	**/
	public function make_tmp_option(){
		$this->load->model('goodsmodel');
		$params	= $_POST;
		$this->goodsmodel->make_tmp_option($params);

		$msg = "옵션이 추가되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	/**
	* 필수옵션 임시옵션정보을 가지고 실제테이블에 생성 (opt 3단계)
	**/
	public function save_option_tmp(){
		$this->load->model('goodsmodel');
		$params	= $_POST;
		$this->goodsmodel->save_option_tmp($params);

		$msg = "적용되었습니다.";
		$callback = "parent.setTmpSeq();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}


	/**
	* 추가구성옵션 임시옵션정보 생성 (subopt 1단계)
	**/
	public function make_suboption_tmp(){
		$this->load->model('goodsmodel');
		$params	= $_POST;
		$this->goodsmodel->make_suboption_tmp($params);

		$msg = "옵션이 추가되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	/**
	* 추가구성옵션 임시옵션정보을 가지고 실제테이블에 생성 (subopt 3단계)
	**/
	public function save_suboption_tmp(){
		$this->load->model('goodsmodel');
		$params	= $_POST;
		$this->goodsmodel->save_suboption_tmp($params);

		$msg = "옵션이 저장되었습니다.";
		$callback = "parent.setTmpSeq();";
		openDialogAlert($msg,400,140,'parent',$callback);
	}

	public function goods_option_frequently() {
		$this->load->model('goodsmodel');
		$result = false;
		switch($_GET['type']){
			case 'option':
				$type = 'opt';//frequentlyopt
				break;
			case 'suboption':
				$type = 'sub';//frequentlysub
				break;
			case 'inputoption':
				$type = 'inp';//frequentlyinp
				break;
		}
		$loop = $this->goodsmodel->frequentlygoods($type);

		if($loop) {
			$loophtml = '';
			foreach( $loop as $key => $data ){
				$loophtml .= "<option value='".$data['goods_name']."^^".$data['goods_seq']."' >".$data['goods_name']."</option>";
			}
			$result = true;
		}

		$result = array('result' => $result,'loophtml'=>$loophtml);
		echo json_encode($result);
		exit;
	}
	//상품 > 상품후기건수 업데이트
	public function all_update_goods_review_cont()
	{
		set_time_limit(0);
		$cfg_good_review_count = config_load('good_review_count');

		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$sc['limitnum'] = 500;//500

		$gdsql = "select goods_seq from fm_goods  order by goods_seq desc ";
		$gdresult = select_page($sc['limitnum'],$_GET['page'],10,$gdsql,'');
		$gdresult['page']['querystring'] = get_args_list();

		### PAGE & DATA
		$query = "select count(*) cnt from fm_goods ";
		$query = $this->db->query($query);
		$data = $query->row_array();
		$all_count = $data['cnt'];

		$idx = 0;
		foreach($gdresult['record'] as $gdidx => $gdrow) {
			if( $gdrow['goods_seq'] ) {
				$gdupquery = "select count(*) as review_count from fm_goods_review where goods_seq='{$gdrow['goods_seq']}' ";
				$gdupquery = $this->db->query($gdupquery);
				$review_cnt = $gdupquery->row_array();
				if($review_cnt['review_count'] > 0 ) {
					$upsql  = "update fm_goods set review_count=".$review_cnt['review_count']."  where goods_seq='{$gdrow['goods_seq']}' ";
					$this->db->query($upsql);
				}
			}
			$idx++;
		}

		if( ($_GET['page']>1 && $all_count <= ( ($sc['limitnum']*$_GET['page'])) ) || ($_GET['page']==1 && $all_count == $idx ) ) {
			if( !$cfg_good_review_count['update_date'] ) {
				config_save('good_review_count',array('update_date'=>date('Y-m-d H:i:s')));
			}
			$status = 'FINISH';
		}else{
			$status = 'NEXT';
		}

		$totalpage = @ceil($all_count/ $sc['limitnum']);

		$result = array('status' => $status,'totalcount'=>$all_count,'nextpage'=>($_GET['page']+1),'totalpage'=>$totalpage);
		echo json_encode($result);
		exit;
	}

	public function coupon_serial_upload(){
		$path						= ROOTPATH."/data/tmp/";
		$config['upload_path']		= $path;
		$config['allowed_types']	= 'xls';
		$config['overwrite']		= TRUE;
		$file_ext					= end(explode('.', $_FILES['coupon_serial_file']['name']));
		$config['file_name']		= 'coupon_serial_upload.'.$file_ext;

		$this->load->library('upload');
		if (is_uploaded_file($_FILES['coupon_serial_file']['tmp_name'])) {
			if	($file_ext == 'xls'){
				$this->upload->initialize($config);
				if ($this->upload->do_upload('coupon_serial_file')) {
					$file_nm	= $config['upload_path'].$config['file_name'];
					@chmod("{$file_nm}", 0777);

					$this->load->model('goodsmodel');
					$result		= $this->goodsmodel->coupon_serial_upload($file_nm);
					if	($result)foreach($result as $coupon_serial => $status){
						$t++;
						if	($t > 1)	$result_str	.= ',';
						$result_str	.= $coupon_serial.'|'.$status.'|';
						if	($status == 'y')	$s++;
						else					$f++;
					}

					echo '<script>parent.setCouponSerial(\''.$t.'\', \''.$result_str.'\');</script>';
					exit;
				}else{
					$callback = "";
					openDialogAlert("업로드에 실패하였습니다.",400,140,'parent',$callback);
					exit;
				}
			}else{
				$callback = "";
				openDialogAlert("xls 파일만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}else{
			$callback = "";
			openDialogAlert("업로드할 파일이 없습니다.",400,140,'parent',$callback);
			exit;
		}
	}

	//쿠폰상품그룹등록
	public function social_goods_group_regist()
	{
		$this->load->model('socialgoodsgroupmodel');
		$social_goods_group_name =  trim($_POST['social_goods_group_name']);
		$provider_seq =  trim($_POST['provider_seq']);
		$social_goods_group_data = $this->socialgoodsgroupmodel->get_data_numrow(array("select"=>" group_seq ","whereis"=>" and name = '".$social_goods_group_name."'  and provider_seq = '".$provider_seq."' "));
		if( $social_goods_group_data ) {
			$msg = "이미 등록된 쿠폰상품그룹명입니다.";
		}else{
			$insertdata['provider_seq']		= $provider_seq;
			$insertdata['name']		= $social_goods_group_name;
			$insertdata['regist_date'] = date("Y-m-d H:i:s",time());
			$social_goods_group_idx = $this->socialgoodsgroupmodel->sggroup_write($insertdata);
		}

		if($social_goods_group_idx){
			$result = array('result' => true);
		}else{
			$result = array('result' => false, 'msg'=>$msg);
		}
		echo json_encode($result);
		exit;
	}

	// 특수옵션 정보 개별 저장.
	public function save_special_option(){
		$tmp_no		= trim($_POST['tmpSeq']);
		$option_seq	= trim($_POST['optionSeq']);
		$option_no	= trim($_POST['optionNo']);
		$newType	= trim($_POST['newType']);
		if	(!$_POST['direct_zipcode'])	$_POST['direct_zipcode']	= array();

		if	($tmp_no && $option_seq && $newType){
			// 특수옵션별 처리
			switch($newType){
				case 'color':
					$upParam['color']				= trim($_POST['direct_color']);
				break;
				case 'address':
					$upParam['zipcode']				= implode('-', $_POST['direct_zipcode']);
					$upParam['address_type']		= trim($_POST['direct_address_type']);
 					$upParam['address']				= trim($_POST['direct_address']);
					$upParam['address_street']		= trim($_POST['direct_address_street']);
					$upParam['addressdetail']		= trim($_POST['direct_addressdetail']);
					$upParam['biztel']				= trim($_POST['direct_biztel']);
					$upParam['address_commission']	= trim($_POST['direct_address_commission']);
				break;
				case 'date':
					$upParam['codedate']			= trim($_POST['direct_codedate']);
				break;
			}

			if	(is_array($upParam) && count($upParam) > 0){
				$whrParam['tmp_no']		= $tmp_no;
				$whrParam['option_seq']	= $option_seq;
				$this->goodsmodel->save_tmp_option($whrParam, $upParam);
				if	($_POST['same_spc_save_type'] == 'y')
					$this->goodsmodel->save_same_tmp_option($tmp_no, $option_seq, $option_no, $upParam);

				echo '<script type="text/javascript">';
				echo 'parent.loadingStop();';
				echo '</script>';
				exit;
			}
		}
	}

	// 임시 옵션 컬럼당 저장
	public function save_tmpoption_piece(){
		$tmpSeq		= trim($_POST['tmpSeq']);
		$optionSeq	= trim($_POST['optionSeq']);
		unset($_POST['tmpSeq']);unset($_POST['optionSeq']);
		$upParam	= $_POST;
		$saveFunc	= 'save_tmp_option';

		// 저장 대상 컬럼에 따른 예외처리
		if		(isset($_POST['supply_price']) || isset($_POST['stock']))
			$saveFunc	= 'save_tmp_supply';

		if	($tmpSeq && $optionSeq){
			// default_option 초기화
			if	(isset($_POST['default_option'])){
				$tmpWhrParam['tmp_no']			= $tmpSeq;
				$tmpUpParam['default_option']	= 'n';
				$this->goodsmodel->$saveFunc($tmpWhrParam, $tmpUpParam);
			}

			$whrParam['tmp_no']		= $tmpSeq;
			$whrParam['option_seq']	= $optionSeq;
			$this->goodsmodel->$saveFunc($whrParam, $upParam);
		}
	}

	// 임시옵션 항목별 일괄적용
	public function save_tmpoption_cell(){
		$tmpSeq			= trim($_GET['tmpSeq']);
		$target			= str_replace('_all', '', trim($_GET['target']));
		$value			= trim($_GET['value']);
		if	($target != 'tmp_policy')
			$value			= preg_replace('/[^0-9]*/', '', $value);
		$reserve_unit	= trim($_GET['reserve_unit']);
		$saveFunc		= 'save_tmp_option';

		// 저장 대상 컬럼에 따른 예외처리
		if		(in_array($target, array('supply_price', 'stock')))
			$saveFunc	= 'save_tmp_supply';

		if	($tmpSeq && $target){
			$upParam[$target]				= $value;
			// 적립금 지급 정책 변경 시 통합설정 값으로 일괄 변경
			if	($target == 'tmp_policy'){
				$reserves		= ($this->reserves)?$this->reserves:config_load('reserve');
				$upParam['reserve_rate']	= $reserves['default_reserve_percent'];
				$upParam['reserve_unit']	= 'percent';
			}

			if	($reserve_unit)
				$upParam['reserve_unit']	= $reserve_unit;
			$whrParam['tmp_no']				= $tmpSeq;
			if	($saveFunc	== 'save_tmp_supply')	$whrParam['suboption_seq']	= NULL;
			$this->goodsmodel->$saveFunc($whrParam, $upParam);

			echo '<script>parent.tmpSaveAll(\''.$target.'\', \''.$value.'\');</script>';
		}
	}

	// 옵션 기본 노출 수량 설정값 저장
	public function set_option_view_count(){
		$limit_view_count		= 100;
		$option_view_count		= trim($_POST['option_view_count']);
		$suboption_view_count	= trim($_POST['suboption_view_count']);
		if	(!is_numeric($option_view_count) || !is_numeric($suboption_view_count)){
			openDialogAlert('기본 개수를 입력하십시오!',400,140,'parent',$callback);
			exit;
		}
		if	($option_view_count < 1 || $suboption_view_count < 1){
			openDialogAlert('기본 개수를 입력하십시오',400,140,'parent',$callback);
			exit;
		}
		if	($option_view_count > $limit_view_count || $suboption_view_count > $limit_view_count){
			openDialogAlert('기본 개수는 '.$limit_view_count.' 이하로 입력하십시오.',400,140,'parent',$callback);
			exit;
		}

		config_save('goods',array('option_view_count'=>$option_view_count));
		config_save('goods',array('suboption_view_count'=>$suboption_view_count));

		$callback	= 'parent.optionViewSave();';
		openDialogAlert('옵션보기 설정이 저장되었습니다.',400,140,'parent',$callback);
		exit;
	}

	## 옵션 추가/삭제
	public function save_option_one_row(){
		$saveType		= trim($_GET['saveType']);
		$tmpSeq			= trim($_GET['tmpSeq']);
		$optionSeq		= trim($_GET['optionSeq']);
		if	($saveType && $tmpSeq && $optionSeq)
			$opt_seq	= $this->goodsmodel->save_option_one_row($saveType, $tmpSeq, $optionSeq);

		if		($saveType == 'add'){
			echo '<script>parent.add_option_row(\''.$opt_seq.'\');</script>';
		}elseif	($saveType == 'del'){
			echo '<script>parent.del_option_row(\''.$opt_seq.'\');</script>';
		}
	}


	//상품일괄업데이트 >  PC/테블릿용 상품설명 업데이트
	public function _batch_modify_imagehosting()
	{  
		$this->load->model('imagehosting');;

		if($_POST['modify_list'] == 'all'){
			$_GET = $_POST;
			$sc = $_GET;
			$this->goodsmodel->batch_mode = 1;
			$query = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($query);
			foreach($query->result_array() as $data){
				$r_goods_seq[] = $data['goods_seq'];
			}
		}else{
			$r_goods_seq = $_POST['goods_seq'];
		}

		if(!$r_goods_seq){
			$callback = "";
			$msg = "수정할 상품이 없습니다!";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}
		
		$this->_set_imagehosting();//접속정보체크

		//이미지호스팅연결 
		$this->imagehosting->ftpconn(); 
		foreach($r_goods_seq as $goods_seq) {
			$goods = $this->goodsmodel->get_goods($goods_seq);
			//이미지호스팅연결
			$newcontents = $this->imagehosting->set_contents('contents', $goods['contents'], $goods_seq); 
		}
		$this->imagehosting->ftpclose(); 
		//이미지호스팅연결 

		$msg = "PC/테블릿용 상품설명정보가 변경 되었습니다.";
		$callback = "parent.location.reload();";
		openDialogAlert($msg,400,140,'parent',$callback);

	}

	##개별 상품수정페이지에서 > PC/테블릿용 상품설명 일괄변경
	public function batch_modify_imagehostgin(){ 
		$this->load->model('imagehosting');

		if( isset($_POST['no']) ){
			$this->_set_imagehosting();//접속정보체크

			$no = (int) $_POST['no']; 
			$contents =  rawurldecode($_POST['contents']);  
			//$this->load->model('goodsmodel');
			//$goods = $this->goodsmodel->get_goods($no);
			//이미지호스팅연결 
			$this->imagehosting->ftpconn();
			$setcontents =$this->imagehosting->set_contents('contents', $contents, $no);  
			$this->imagehosting->ftpclose();
			if($setcontents['totalnum'] > 0 && $setcontents['changenum']>0) {
				$msg = '변환대상 총 '.number_format($setcontents['totalnum']).'개중에서 '.number_format($setcontents['changenum']).'개 변환완료되었습니다.';
				$msg .= '<br/>PC/테블릿용 상품설명정보가 변경 되었습니다.';
				$result = array('result' => true, 'msg'=>$msg, 'contents'=>$setcontents['newcontents']);
			}else{ 
				$msg = '이미지 호스팅 변환파일이 없습니다.';
				$result = array('result' => false, 'msg'=>$msg, 'contents'=>$setcontents['newcontents']);
			}
			echo json_encode($result); 
			exit; 
		}
		$msg = '잘못된 접근입니다.';
		$result = array('result' => false, 'msg'=>$msg);
		echo json_encode($result);
		exit;
	}
	
	// 이미지 호스팅 FTP연결상태 확인
	function _set_imagehosting($type) { 
		$hostname	= trim($_POST['hostname']).$this->imagehosting->imagehostingftp['gabiaimagehostingurl'];
		$username	= trim($_POST['username']);
		$password	= trim($_POST['password']); 
		if	(!($hostname) || !($username) || !($password)){
			$msg = '이미지 호스팅 정보를 정확히 입력하십시오!';
			if ( $type == 'batch') {
				openDialogAlert($msg,400,140,'parent',$callback);
				exit;
			}else{
				$result = array('result' => false, 'msg'=>$msg);
				echo json_encode($result);
				exit;
			}
		}

		$FTP_CONNECT = @ftp_connect($hostname,$this->imagehosting->imagehostingftp['port']); 
		if (!$FTP_CONNECT) {
			$msg = 'FTP서버 연결에 문제가 발생했습니다.';
			$msg = '이미지 호스팅 정보를 정확히 입력하십시오!';
			if ( $type == 'batch') {
				openDialogAlert($msg,400,140,'parent',$callback);
				exit;
			}else{
				$result = array('result' => false, 'msg'=>$msg);
				echo json_encode($result);
				exit;
			}
		}
		$FTP_CRESULT = @ftp_login($FTP_CONNECT,$username,$password); 

		if (!$FTP_CRESULT) {
			$msg = 'FTP서버 아이디나 패스워드가 일치하지 않습니다.';
			if ( $type == 'batch') {
				openDialogAlert($msg,400,140,'parent',$callback);
				exit;
			}else{
				$result = array('result' => false, 'msg'=>$msg);
				echo json_encode($result);
				exit;
			}
		}

		config_save('imagehosting',array('hostname'=>trim($_POST['hostname'])));  
		config_save('imagehosting',array('r_date'=>date("Y-m-d H:i:s")));  
	}

	function get_option_goods_status($goods_seq,$runout,$ableStockLimit,$ableStockStep) {
		$return_info = array();

		// 재고 업데이트 전 상품 상태값 확인
		$get_goods = $this->goodsmodel->get_goods($goods_seq);
		$before_goods_status = $get_goods['goods_status'];
		$before_tot_stock = $get_goods['tot_stock'];

		// 변경될 재고 확인
		$get_tot_option = $this->goodsmodel->get_tot_option($goods_seq);
		$afterUnUsableStock = (int) $get_tot_option['stock'] - $get_tot_option['badstock'] - $get_tot_option['reservation'.$ableStockStep];

		// 변경될 상품 상태값
		$modify_status = '';
		if ($runout=="stock") { // 재고가 있으면 판매
			if ($get_tot_option['stock'] < 1) {
				$modify_status = "runout";
			} else {
				$modify_status = "normal";
			}
		} else if ($runout=="ableStock") { // 가용 재고가 있으면 판매
			if ($afterUnUsableStock < $ableStockLimit) {
				$modify_status = "runout";
			} else {
				$modify_status = "normal";
			}
		} else if ($runout=="unlimited") { // 재고와 무관 판매
			if ($get_goods['goods_kind'] == 'coupon' && $get_goods['coupon_serial_type'] == 'n') { // 외부제휴사쿠폰
				if ($get_tot_option['stock'] < 1) {
					$modify_status = "runout";
				} else {
					$modify_status = "normal";
				}
			} else {
				$modify_status = "normal";
			}
		}

		// 정상과 품절이었던 상품들 중 상태값이 변경되는 경우 계산
		if ($before_goods_status=="normal" && $modify_status=="runout") {
			$return_info['runout_gname']['goods_seq'] = $get_goods['goods_seq'];
			$return_info['runout_gname']['goods_name'] = $get_goods['goods_name'];
		} else if ($before_goods_status=="runout" && $modify_status=="normal") {
			$return_info['normal_gname']['goods_seq'] = $get_goods['goods_seq'];
			$return_info['normal_gname']['goods_name'] = $get_goods['goods_name'];
		}

		$return_info['goods_status'] = $modify_status;

		return $return_info;
	}

	/* 상품이미지 설정 저장 */
	public function save_image_config()
	{
		/* 상품이미지 설정 체크 */
		$image_arr = array(
			'largeImage'=>'상품상세(확대)',
			'viewImage'=>'상품상세(기본)',
			'list1Image'=>'리스트(1)',
			'list2Image'=>'리스트(2)',
			'thumbView'=>'썸네일(상품상세)',
			'thumbCart'=>'썸네일(장바구니/주문)',
			'thumbScroll'=>'썸네일(스크롤)'
		);
		foreach($image_arr as $image_type => $image_description){
			$width = $_POST[$image_type.'Width'];
			if($width < 1 || !$width){ 
				$result = array('result' => false, 'msg'=>$image_description."이미지 설정은 \'0\'보다 커야 합니다.");
				echo json_encode($result); 
				exit; 
			}
		}

		$this->goodsmodel->set_goodsImageSize('large',$_POST['largeImageWidth'],$_POST['largeImageHeight']);
		$this->goodsmodel->set_goodsImageSize('view',$_POST['viewImageWidth'],$_POST['viewImageHeight']);
		$this->goodsmodel->set_goodsImageSize('list1',$_POST['list1ImageWidth'],$_POST['list1ImageHeight']);
		$this->goodsmodel->set_goodsImageSize('list2',$_POST['list2ImageWidth'],$_POST['list2ImageHeight']);
		$this->goodsmodel->set_goodsImageSize('thumbView',$_POST['thumbViewWidth'],$_POST['thumbViewHeight']);
		$this->goodsmodel->set_goodsImageSize('thumbCart',$_POST['thumbCartWidth'],$_POST['thumbCartHeight']);
		$this->goodsmodel->set_goodsImageSize('thumbScroll',$_POST['thumbScrollWidth'],$_POST['thumbScrollHeight']);
		
		$msg = "상품사진의 사이즈 설정이 변경 되었습니다.";
		$result = array('result' => true, 'msg'=>$msg, 
		'largeImageWidth'=>$_POST['largeImageWidth'], 'largeImageHeight'=>$_POST['largeImageHeight'], 
		'viewImageWidth'=>$_POST['viewImageWidth'], 'viewImageHeight'=>$_POST['viewImageHeight'],
		'list1ImageWidth'=>$_POST['list1ImageWidth'], 'list1ImageHeight'=>$_POST['list1ImageHeight'], 
		'list2ImageWidth'=>$_POST['list2ImageWidth'], 'list2ImageHeight'=>$_POST['list2ImageHeight'], 
		'thumbViewWidth'=>$_POST['thumbViewWidth'], 'thumbViewHeight'=>$_POST['thumbViewHeight'], 
		'thumbCartWidth'=>$_POST['thumbCartWidth'], 'thumbCartHeight'=>$_POST['thumbCartHeight'],
		'thumbScrollWidth'=>$_POST['thumbScrollWidth'], 'thumbScrollHeight'=>$_POST['thumbScrollHeight']); 
		echo json_encode($result); 
		exit; 
	}
}
/* End of file category.php */
/* Location: ./app/controllers/admin/goods_process */