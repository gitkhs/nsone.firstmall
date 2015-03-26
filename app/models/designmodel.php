<?php
class DesignModel extends CI_Model {	
	function __construct() {
		parent::__construct();
		
		$this->load->helper('design');
		$this->load->model('layout');
	}

	/* 보유한 스킨 목록 가져오기 */
	public function get_skin_list($skinPrefix=null){

		$skinList = array();
		$skinPath = ROOTPATH."data/skin/";
		

		$map = directory_map($skinPath,true,false);
		foreach($map as $dir){
			
			$configurationPath = $skinPath.$dir."/configuration/skin.ini";
			if(!file_exists($configurationPath) || in_array($dir,array('.','..'))) continue;
			
			if($skinPrefix){
				if(!preg_match("/^".$skinPrefix."_/",$dir)) continue;
			}else{
				if(preg_match("/^(mobile|fammerce|store|storemobile|storefammerce)_/",$dir)) continue;
			}
			
			$configuration = skin_configuration($dir);
			
			$skinList[] = $configuration;
			
		}		

		if(!function_exists('get_skin_list_cmp')){
			function get_skin_list_cmp ($a, $b) {   if ($a['regdate'] == $b['regdate']) return 0;   return ($a['regdate'] < $b['regdate']) ? 1 : -1;}
		}

		usort($skinList,"get_skin_list_cmp");	

		return $skinList;
	}

	/* 모든 스킨 목록 가져오기 */
	public function get_all_skin_list($skinPrefix=null){

		$skinList = array();
		$skinPath = ROOTPATH."data/skin/";
		
		$map = directory_map($skinPath,true,false);
		foreach($map as $dir){
			
			$configurationPath = $skinPath.$dir."/configuration/skin.ini";
			if(!file_exists($configurationPath) || in_array($dir,array('.','..'))) continue;
			
			$configuration = skin_configuration($dir);
			
			$skinList[] = $configuration;
			
		}		

		if(!function_exists('get_skin_list_cmp')){
			function get_skin_list_cmp ($a, $b) {   if ($a['regdate'] == $b['regdate']) return 0;   return ($a['regdate'] < $b['regdate']) ? 1 : -1;}
		}

		usort($skinList,"get_skin_list_cmp");	

		return $skinList;
	}
	
	/* 스킨 압축 반환 */
	public function export_skin($skin){

		ini_set("memory_limit",-1);
		set_time_limit(0);

		$this->load->library('zipfile');
		$this->load->helper('download');
		$this->load->helper('directory');
		$this->load->helper('file');
		$CI =& get_instance();
		
		$skin_path = ROOTPATH."data/skin/".$skin;
		
		/* layout.sql 생성 */
		$config_layout_queries = array();
		$query = "select * from fm_config_layout where skin=?";
		$query = $CI->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$row['skin'] = "{skin}";
			$keys = "`".implode("`,`",array_keys($row))."`";
			$values = "'".implode("','",array_map("addslashes",$row))."'";
			$config_layout_queries[] = sprintf("INSERT INTO `fm_config_layout` (%s) values (%s);",$keys,$values);		
		}
		if(!write_file($skin_path."/configuration/layout.sql",implode("\r\n",$config_layout_queries))){
			openDialogAlert("layout.sql 생성에 실패하였습니다.",400,140,'parent');
			exit;
		}
		
		/* flash.sql 생성 */
		$design_flash_queries = array();
		$query = "select * from fm_design_flash where skin=?";
		$query = $CI->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$flash_seq = $row['flash_seq'];
			unset($row['flash_seq']);
			$row['skin'] = "{skin}";
			$keys = "`".implode("`,`",array_keys($row))."`";
			$values = "'".implode("','",array_map("addslashes",$row))."'";
			$design_flash_queries[] = sprintf("INSERT INTO `fm_design_flash` (%s) values (%s);",$keys,$values);		
			
			$query2 = "select * from fm_design_flash_file where flash_seq=?";
			$query2 = $CI->db->query($query2,$flash_seq);
			foreach ($query2->result_array() as $row2){
				unset($row2['flash_file_seq']);
				$row2['flash_seq'] = "{flash_seq}";
				$keys = "`".implode("`,`",array_keys($row2))."`";
				$values = "'".implode("','",array_map("addslashes",$row2))."'";
				$design_flash_queries[] = sprintf("INSERT INTO `fm_design_flash_file` (%s) values (%s);",$keys,$values);
			}
		}
		if(!write_file($skin_path."/configuration/flash.sql",implode("\r\n",$design_flash_queries))){
			openDialogAlert("flash.sql 생성에 실패하였습니다.",400,140,'parent');
			exit;
		}

		/* banner.sql 생성 */
		$design_banner_queries = array();
		$query = "select * from fm_design_banner where skin=?";
		$query = $CI->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$banner_seq = $row['banner_seq'];
			$row['skin'] = "{skin}";
			$keys = "`".implode("`,`",array_keys($row))."`";
			$values = "'".implode("','",array_map("addslashes",$row))."'";
			$design_banner_queries[] = sprintf("INSERT INTO `fm_design_banner` (%s) values (%s);",$keys,$values);		
			
			$query2 = "select * from fm_design_banner_item where skin=? and banner_seq=?";
			$query2 = $CI->db->query($query2,array($skin,$banner_seq));
			foreach ($query2->result_array() as $row2){
				unset($row2['banner_item_seq']);
				$row2['skin'] = "{skin}";
				$keys = "`".implode("`,`",array_keys($row2))."`";
				$values = "'".implode("','",array_map("addslashes",$row2))."'";
				$design_banner_queries[] = sprintf("INSERT INTO `fm_design_banner_item` (%s) values (%s);",$keys,$values);
			}
		}
		if(!write_file($skin_path."/configuration/banner.sql",implode("\r\n",$design_banner_queries))){
			openDialogAlert("banner.sql 생성에 실패하였습니다.",400,140,'parent');
			exit;
		}

		/* zip 생성 */
		$this->zipfile->reset();
		$map = directory_map_list(directory_map($skin_path,false,false));
		foreach($map as $k=>$v) {
			if(is_file($skin_path.$v)){
				$this->zipfile->addFile(read_file($skin_path.$v),$skin.$v);
			}	
		}
		$backup_file_contents = $this->zipfile->file();
		
		
		if(!$backup_file_contents){
			openDialogAlert("ZIP 파일 생성에 실패하였습니다.",400,140,'parent');
			exit;
		}
		
		return $backup_file_contents;
	}
	
	public function zip_extract_skin($zip_path){
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('usedmodel');
		
		$this->load->library('pclzip',array('p_zipname' => $zip_path));
		
		$tmp_path = ROOTPATH."data/tmp/".time();
		$extract = $this->pclzip->extract(PCLZIP_OPT_PATH, $tmp_path);
		
		if(!$extract)
		{  
			openDialogAlert("압축해제 실패",300,140,'parent');
			exit;
		}
		
		/* 스킨명 */
		$map = array_keys(directory_map($tmp_path));
		$skin = $new_skin = $map[0];
		$new_skin_path = ROOTPATH."data/skin/".$new_skin;
		$skin_idx = 0;
		while(is_dir($new_skin_path)){
			$skin_idx++;
			$new_skin = $skin."_".$skin_idx;
			$new_skin_path = ROOTPATH."data/skin/".$new_skin;
		}

		rename($tmp_path."/".$skin,$new_skin_path);
		rmdir($tmp_path);
		chmod($new_skin_path,0777);
		
		if(empty($_SERVER['WINDIR'])){
			@exec("chmod 777 {$new_skin_path} -R");
		}

		if(!file_exists($new_skin_path."/configuration/layout.sql")){
			openDialogAlert("스킨 업로드에 실패하였습니다.\\n레이아웃 설정파일이 존재하지 않습니다.",300,150,'parent');
			exit;
		}
		
		$this->db->trans_begin();

		/* layout config delete */
		$query = "delete from fm_config_layout where skin=?";
		$this->db->query($query,$new_skin);
		
		/* layout config insert */
		$success = true;
		$layout_sql_contents = explode("\r\n",read_file($new_skin_path."/configuration/layout.sql"));
		foreach($layout_sql_contents as $query){
			if(trim($query)){
				if(preg_match("/^INSERT INTO `fm_config_layout`/",$query) && substr_count($query,"');")==1){
					$query = str_replace("{skin}",$new_skin,$query);
					$this->db->query($query,$new_skin);
				}else{
					$success = false;
					openDialogAlert("스킨 업로드에 실패하였습니다.\\n레이아웃 설정파일에 문제가 있습니다.",300,150,'parent');
					break;
				}
			}
		}
		
		/* newskin flash delete */
		$query = "select * from fm_design_flash where skin=?";
		$query = $this->db->query($query,$new_skin);
		foreach ($query->result_array() as $row){
			$query = "delete from fm_design_flash_file where flash_seq=?";
			$this->db->query($query,$row['flash_seq']);
		}
		$query = "delete from fm_design_flash where skin=?";
		$this->db->query($query,$new_skin);
		
		/* flash insert */
		$success = true;
		$new_flash_seq = null;
		$flash_sql_contents = explode("\r\n",read_file($new_skin_path."/configuration/flash.sql"));
		foreach($flash_sql_contents as $query){
			if(trim($query)){
				if(preg_match("/^INSERT INTO `fm_design_flash`/",$query) && substr_count($query,"');")==1){
					$query = str_replace("{skin}",$new_skin,$query);
					$this->db->query($query);
					$new_flash_seq = $this->db->insert_id();
				}else if(preg_match("/^INSERT INTO `fm_design_flash_file`/",$query) && substr_count($query,"');")==1){
					$query = str_replace("{flash_seq}",$new_flash_seq,$query);
					$this->db->query($query);
				}else{
					$success = false;
					debug_var($query);
					openDialogAlert("스킨 업로드에 실패하였습니다.\\nflash.sql파일에 문제가 있습니다.",300,140,'parent');
					break;
				}
			}
		}

		/* newskin slide banner delete */
		$query = "select * from fm_design_banner where skin=?";
		$query = $this->db->query($query,$new_skin);
		foreach ($query->result_array() as $row){
			$query = "delete from fm_design_banner_item where skin=? and banner_seq=?";
			$this->db->query($query,array($new_skin,$row['banner_seq']));
		}
		$query = "delete from fm_design_banner where skin=?";
		$this->db->query($query,$new_skin);
		
		/* banner insert */
		$success = true;
		$banner_sql_contents = explode("\r\n",read_file($new_skin_path."/configuration/banner.sql"));
		foreach($banner_sql_contents as $query){
			if(trim($query)){
				if(preg_match("/^INSERT INTO `fm_design_banner`/",$query) && substr_count($query,"');")==1){
					$query = str_replace("{skin}",$new_skin,$query);
					$this->db->query($query);
				}else if(preg_match("/^INSERT INTO `fm_design_banner_item`/",$query) && substr_count($query,"');")==1){
					$query = str_replace("{skin}",$new_skin,$query);
					$this->db->query($query);
				}else{
					$success = false;
					openDialogAlert("스킨 업로드에 실패하였습니다.\\nbanner.sql파일에 문제가 있습니다.",300,140,'parent');
					break;
				}
			}
		}
		
		/* 실패했을경우 업로드중인 데이터 삭제 */
		if($success == false){
			$this->db->trans_rollback();

			/* layout config delete */
			#$query = "delete from fm_config_layout where skin=?";
			#$this->db->query($query,$new_skin);
			
			/* 스킨폴더 내 파일 및 디렉토리 삭제 */
			$map = directory_map_list(directory_map($new_skin_path,false,true));
			rsort($map);
			foreach($map as $k=>$v) {
				chmod($new_skin_path.$v,0777);
				if(is_file($new_skin_path.$v)){
					unlink($new_skin_path.$v);
				}else{
					rmdir($new_skin_path.$v);
				}
			}
			
			rmdir($new_skin_path);
		}
				
		/* 완료했을경우 layout.sql,flash.sql 삭제 */
		if($success){
			skin_configuration_save($new_skin,"skin",$new_skin);

			$this->db->trans_commit();

			@unlink($new_skin_path."/configuration/layout.sql");
			@unlink($new_skin_path."/configuration/flash.sql");
			@unlink($new_skin_path."/configuration/banner.sql");
		}
		
		return $success;
		
	}

	// 모바일 Quick 디자인 css파일 경로 반환
	public function get_mobile_buttons_css_path(){
		if( SERVICE_CODE=='P_STOR') {
			$skin = $this->workingStoremobileSkin;
		}else{
			$skin = $this->workingMobileSkin;
		}
		$cssPath = "/data/skin/".$skin."/css/quick_design.css";

		$skin_configuration = skin_configuration($skin);

		return $skin_configuration['mobile_version']=='2' && file_exists(ROOTPATH.$cssPath) ? $cssPath : null;
	}

	// 모바일 Quick 디자인 테마
	public function get_mobile_themes(){
		$array = array(
			'red'=> array(
				'name'	=> '레드',
				'color'	=> '#ad0005',
				'childs' => array('red1')
			),
			'pink'=> array(
				'name'	=> '핑크',
				'color'	=> '#ff87dc',
				'childs' => array('pink1')
			),
			'orange'=> array(
				'name'	=> '오렌지',
				'color'	=> '#ff7800',
				'childs' => array('orange1')
			),
			'yellow'=> array(
				'name'	=> '옐로우',
				'color'	=> '#fbab00',
				'childs' => array('yellow1')
			),
			'brown'=> array(
				'name'	=> '브라운',
				'color'	=> '#916345',
				'childs' => array('brown1','brown2')
			),
			'green'=> array(
				'name'	=> '그린',
				'color'	=> '#68a90b',
				'childs' => array('green1','green2')
			),
			'blue'=> array(
				'name'	=> '블루',
				'color'	=> '#2c5cc9',
				'childs' => array('blue1','blue2','blue3','blue4')
			),
			'violet'=> array(
				'name'	=> '바이올렛',
				'color'	=> '#8722c8',
				'childs' => array('violet1')
			),
		);
		return $array;
	}

	// 팝업 스타일
	public function get_popup_styles($key=null){
		$array = array(
			'window'		=> 'PC용 → 윈도우 팝업',
			'layer'			=> 'PC용 → 레이어 팝업',
			'mobile_layer'	=> '모바일/태블릿용 → 레이어 팝업',
			'band'			=> 'PC용 → 띠배너',
			'mobile_band'	=> '모바일/태블릿용 → 띠배너'
		);
		if(!is_null($key)) return $array[$key];
		return $array;
	}

	// 배너 스타일
	public  function get_banner_styles($platform=''){
		$array = array(
			'pc_style_1' => array(
				'platform' => 'pc',
				'name'	=> 'PC STYLE 1',
				'use_image_size' => true,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => false,
				'use_navigation_paging_custom' => false,
				'use_swipe' => false,
			),
			'pc_style_2' => array(
				'platform' => 'pc',
				'name'	=> 'PC STYLE 2',
				'use_image_size' => true,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => false,
				'use_swipe' => false,
			),
			'pc_style_3' => array(
				'platform' => 'pc',
				'name'	=> 'PC STYLE 3',
				'use_image_size' => true,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => false,
				'use_swipe' => false,
			),
			'pc_style_4' => array(
				'platform' => 'pc',
				'name'	=> 'PC STYLE 4',
				'use_image_size' => true,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => true,
				'use_swipe' => false,
			),
			'pc_style_5' => array(
				'platform' => 'pc',
				'name'	=> 'PC STYLE 5',
				'use_image_size' => true,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => true,
				'use_swipe' => false,
			),
			'mobile_style_1' => array(
				'platform' => 'mobile',
				'name'	=> 'MOBILE STYLE 1',
				'use_image_size' => true,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => false,
			),
			'mobile_style_2' => array(
				'platform' => 'mobile',
				'name'	=> 'MOBILE STYLE 2',
				'use_image_size' => false,
				'use_image_margin' => false,
				'use_background' => false,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => false,
			),
			'mobile_style_3' => array(
				'platform' => 'mobile',
				'name'	=> 'MOBILE STYLE 3',
				'use_image_size' => false,
				'use_image_margin' => true,
				'use_background' => true,
				'use_navigation_paging' => true,
				'use_navigation_paging_custom' => false,
			),
		);

		if($platform){
			foreach($array as $k=>$v){
				if($platform!=$v['platform']) unset($array[$k]);
			}
		}
		
		return $array;
	}

	// 배너 샘플
	public  function get_banner_sample($style){
		$array = array(
			'pc_style_1' => array(
				'name'	=> 'PC STYLE 1 샘플',
				'height' => "470",
				'background_color' => "#bbc2d2",
				'background_image' => "/admin/skin/default/images/design/banner/pc_style_1/st1_tit.jpg",
				'background_repeat' => "no-repeat",
				'background_position' => "center top",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "56",
				'image_side_margin' => "18",
				'image_width' => "290",
				'image_height' => "384",
				'navigation_btn_style' => "btn_style_2",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "",
				'navigation_paging_align' => "center",
				'navigation_paging_position' => "bottom",
				'navigation_paging_margin' => "10",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample1.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample2.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample3.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample4.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample5.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample6.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_1/st1_sample7.jpg"),
				)
			),
			'pc_style_2' => array(
				'name'	=> 'PC STYLE 2 샘플',
				'height' => "295",
				'background_color' => "#ffffff",
				'background_image' => "",
				'background_repeat' => "no-repeat",
				'background_position' => "left top",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "0",
				'image_side_margin' => "0",
				'image_width' => "852",
				'image_height' => "295",
				'navigation_btn_style' => "btn_style_3",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "paging_style_1",
				'navigation_paging_align' => "right",
				'navigation_paging_position' => "over",
				'navigation_paging_margin' => "20",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_2/st2_sample1.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_2/st2_sample2.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_2/st2_sample3.jpg"),
				)
			),
			'pc_style_3' => array(
				'name'	=> 'PC STYLE 3 샘플',
				'height' => "310",
				'background_color' => "#e5ebf8",
				'background_image' => "",
				'background_repeat' => "no-repeat",
				'background_position' => "left top",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "10",
				'image_side_margin' => "10",
				'image_width' => "832",
				'image_height' => "290",
				'navigation_btn_style' => "",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "paging_style_1",
				'navigation_paging_align' => "center",
				'navigation_paging_position' => "bottom",
				'navigation_paging_margin' => "24",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_3/st3_sample1.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_3/st3_sample2.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_3/st3_sample3.jpg"),
				)
			),
			'pc_style_4' => array(
				'name'	=> 'PC STYLE 4 샘플',
				'height' => "310",
				'background_color' => "#ffffff",
				'background_image' => "",
				'background_repeat' => "no-repeat",
				'background_position' => "left top",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "0",
				'image_side_margin' => "0",
				'image_width' => "852",
				'image_height' => "310",
				'navigation_btn_style' => "",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "custom",
				'navigation_paging_align' => "center",
				'navigation_paging_position' => "over",
				'navigation_paging_margin' => "10",
				'navigation_paging_spacing' => "1",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_4/st4_sample1.jpg","tab_image_inactive"=>"tab1.jpg","tab_image_active"=>"tab1_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_4/st4_sample2.jpg","tab_image_inactive"=>"tab2.jpg","tab_image_active"=>"tab2_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_4/st4_sample3.jpg","tab_image_inactive"=>"tab3.jpg","tab_image_active"=>"tab3_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_4/st4_sample4.jpg","tab_image_inactive"=>"tab4.jpg","tab_image_active"=>"tab4_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_4/st4_sample5.jpg","tab_image_inactive"=>"tab5.jpg","tab_image_active"=>"tab5_on.jpg"),
				)
			),
			'pc_style_5' => array(
				'name'	=> 'PC STYLE 5 샘플',
				'height' => "354",
				'background_color' => "#ffffff",
				'background_repeat' => "no-repeat",
				'background_position' => "left top",
				'background_position' => "",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "0",
				'image_side_margin' => "0",
				'image_width' => "490",
				'image_height' => "246",
				'navigation_btn_style' => "btn_style_3",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "custom",
				'navigation_paging_align' => "center",
				'navigation_paging_position' => "bottom",
				'navigation_paging_margin' => "16",
				'navigation_paging_spacing' => "2",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_5/st5_sample1_b.jpg","tab_image_inactive"=>"tab1.jpg","tab_image_active"=>"tab1_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_5/st5_sample2_b.jpg","tab_image_inactive"=>"tab2.jpg","tab_image_active"=>"tab2_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_5/st5_sample3_b.jpg","tab_image_inactive"=>"tab3.jpg","tab_image_active"=>"tab3_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_5/st5_sample4_b.jpg","tab_image_inactive"=>"tab4.jpg","tab_image_active"=>"tab4_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_5/st5_sample5_b.jpg","tab_image_inactive"=>"tab5.jpg","tab_image_active"=>"tab5_on.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/pc_style_5/st5_sample6_b.jpg","tab_image_inactive"=>"tab6.jpg","tab_image_active"=>"tab6_on.jpg"),
				)
			),
			'mobile_style_1' => array(
				'name'	=> 'MOBILE STYLE 1 샘플',
				'height' => "234",
				'background_color' => "#4a4c51",
				'background_image' => "/admin/skin/default/images/design/banner/mobile_style_1/st1_tit.jpg",
				'background_repeat' => "no-repeat",
				'background_position' => "10px 10px",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "36",
				'image_side_margin' => "10",
				'image_width' => "230",
				'image_height' => "168",
				'navigation_btn_style' => "",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "paging_style_1",
				'navigation_paging_align' => "center",
				'navigation_paging_position' => "bottom",
				'navigation_paging_margin' => "10",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_1/st1_sample1.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_1/st1_sample2.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_1/st1_sample3.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_1/st1_sample4.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_1/st1_sample5.jpg"),
				)
			),
			'mobile_style_2' => array(
				'name'	=> 'MOBILE STYLE 2 샘플',
				'height' => "",
				'background_color' => "#ffffff",
				'background_image' => "",
				'background_repeat' => "no-repeat",
				'background_position' => "left top",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "0",
				'image_side_margin' => "0",
				'image_width' => "",
				'image_height' => "",
				'navigation_btn_style' => "btn_style_3",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "paging_style_1",
				'navigation_paging_align' => "right",
				'navigation_paging_position' => "over",
				'navigation_paging_margin' => "8",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_2/st2_sample1.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_2/st2_sample2.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_2/st2_sample3.jpg"),
				)
			),
			'mobile_style_3' => array(
				'name'	=> 'MOBILE STYLE 3 샘플',
				'height' => "",
				'background_color' => "#e5ebf8",
				'background_image' => "",
				'background_repeat' => "no-repeat",
				'background_position' => "left top",
				'image_border_use' => "n",
				'image_border_width' => "0",
				'image_border_color' => "#ffffff",
				'image_opacity_use' => "n",
				'image_opacity_percent' => "0",
				'image_top_margin' => "10",
				'image_side_margin' => "10",
				'image_width' => "",
				'image_height' => "",
				'navigation_btn_style' => "",
				'navigation_btn_visible' => "fixed",
				'navigation_paging_style' => "paging_style_1",
				'navigation_paging_align' => "center",
				'navigation_paging_position' => "bottom",
				'navigation_paging_margin' => "10",
				'slide_event' => "auto",
				'images' => array(
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_3/st3_sample1.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_3/st3_sample2.jpg"),
					array("target"=>"_blank","link"=>"/main/index","image"=>"/admin/skin/default/images/design/banner/mobile_style_3/st3_sample3.jpg"),
				)
			),
		);
		return $array[$style];
	}

	// 새로운 banner_seq 반환
	public function get_new_banner_seq($skin){
		$query = $this->db->query("select banner_seq from fm_design_banner where skin=? order by banner_seq desc limit 1",$skin);
		$result = $query->row_array();
		if($result){
			return $result['banner_seq']+1;
		}else{
			return 1;
		}
	}

}
?>