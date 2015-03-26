<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class design_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->library('upload');
		$this->load->helper('design');
		$this->load->helper('javascript');
		$this->load->model('layout');


		/* 루트폴더 정의 */
		$this->dataPath = 'data';
		define('__DESIGNIS__',true);
	}

	/* data경로 체크 */
	public function _datapath_check($path){
		/* data폴더 하위가 맞으면 true 아니면 false */
		return preg_match("/^{$this->dataPath}/",$path) ? true : false;
	}

	/* 레이아웃설정 저장 */
	public function layout()
	{

		if(!isset($_POST['mode']))					$_POST['mode']='';
		if(!isset($_POST['tpl_desc']))				$_POST['tpl_desc']='';
		if(!isset($_POST['width']))					$_POST['width']='';
		if(!isset($_POST['body_width']))			$_POST['body_width']='';
		if(!isset($_POST['align']))					$_POST['align']='';
		if(!isset($_POST['font']))					$_POST['font']='';
		if(!isset($_POST['scrollbarColor']))		$_POST['scrollbarColor']='';
		if(!isset($_POST['backgroundColor']))		$_POST['backgroundColor']='';
		if(!isset($_POST['backgroundImage']))		$_POST['backgroundImage']='';
		if(!isset($_POST['backgroundRepeat']))		$_POST['backgroundRepeat']='';
		if(!isset($_POST['backgroundPosition']))	$_POST['backgroundPosition']='';
		if(!isset($_POST['bodyBackgroundColor']))	$_POST['bodyBackgroundColor']='';
		if(!isset($_POST['bodyBackgroundImage']))	$_POST['bodyBackgroundImage']='';
		if(!isset($_POST['layoutHeaderChk']))		$_POST['layoutHeaderChk']='';
		if(!isset($_POST['layoutHeader']))			$_POST['layoutHeader']='';
		if(!isset($_POST['layoutTopBarChk']))		$_POST['layoutTopBarChk']='';
		if(!isset($_POST['layoutTopBar']))			$_POST['layoutTopBar']='';
		if(!isset($_POST['layoutMainTopBarChk']))	$_POST['layoutMainTopBarChk']='';
		if(!isset($_POST['layoutMainTopBar']))		$_POST['layoutMainTopBar']='';
		if(!isset($_POST['layoutSideChk']))			$_POST['layoutSideChk']='';
		if(!isset($_POST['layoutSide']))			$_POST['layoutSide']='';
		if(!isset($_POST['layoutSideLocation']))	$_POST['layoutSideLocation']='';
		if(!isset($_POST['layoutFooterChk']))		$_POST['layoutFooterChk']='';
		if(!isset($_POST['layoutFooter']))			$_POST['layoutFooter']='';
		if(!isset($_POST['layoutScrollLeftChk']))	$_POST['layoutScrollLeftChk']='';
		if(!isset($_POST['layoutScrollLeft']))		$_POST['layoutScrollLeft']='';
		if(!isset($_POST['layoutScrollRightChk']))	$_POST['layoutScrollRightChk']='';
		if(!isset($_POST['layoutScrollRight']))		$_POST['layoutScrollRight']='';
		if(!isset($_POST['apply_type']))			$_POST['apply_type']='';

		if($_POST['mode']=='create'){
			$this->validation->set_rules('tpl_folder', '디렉토리','trim|required|max_length[30]|xss_clean');
			$this->validation->set_rules('tpl_file_name', '파일명','trim|required|max_length[30]|xss_clean');
			$this->validation->set_rules('tpl_file_ext', '파일확장자','trim|required|max_length[30]|xss_clean');
			$this->validation->set_rules('tpl_file_name_chk', '파일명 중복확인','trim|required|max_length[1]|xss_clean');

		}

		$this->validation->set_rules('tpl_desc', '페이지 설명','trim|required|max_length[100]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		/* 배경이미지 업로드시 */
		if (is_uploaded_file($_FILES['backgroundImage']['tmp_name'])) {
			$upload_path = "data/skin/".$this->designWorkingSkin."/images/design";
			$config['upload_path']		= ROOTPATH.$upload_path;
			$file_ext = end(explode('.', $_FILES['backgroundImage']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= "background_image".substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('backgroundImage')) {
				@chmod($config['upload_path'].'/'.$config['file_name'], 0777);
				$_POST['backgroundImage'] = $config['file_name'];
			}else{
				$callback = "";
				openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}

		/* 본문 배경이미지 업로드시 */
		if (is_uploaded_file($_FILES['bodyBackgroundImage']['tmp_name'])) {
			$upload_path = "data/skin/".$this->designWorkingSkin."/images/design";
			$config['upload_path']		= ROOTPATH.$upload_path;
			$file_ext = end(explode('.', $_FILES['bodyBackgroundImage']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= "body_background_image".substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('bodyBackgroundImage')) {
				@chmod($config['upload_path'].'/'.$config['file_name'], 0777);
				$_POST['bodyBackgroundImage'] = $config['file_name'];
			}else{
				$callback = "";
				openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}

		$arrLayoutBasic = layout_config_load($this->designWorkingSkin,'basic');

		// 폰트 설정시
		$arr_basic_font = array('dotum','gulim','batang','맑은 고딕'); // 기본폰트
		if( $_POST['font_service_seq'] ){
			$this->load->helper('readurl');
			$basic_font_yn = 'y';
			if(in_array($_POST['font'],$arr_basic_font)||!$_POST['font']) $basic_font_yn = 'n';
			$param_get = array(
				'cmd=fontDefault',
				'service_seq='.$_POST['font_service_seq'],
				'shop_no='.$this->config_system['shopSno'],
				'shopdomain='.urlencode($_SERVER['HTTP_HOST']),
				'basic_font_yn='.$basic_font_yn
			);
			$requestUrl = "http://interface.firstmall.kr/firstmall_plus/request.php?".implode('&',$param_get);
			$font_out = readurl($requestUrl);
		}

		if($_POST['mode']=='create'){
			$tpl_path = $_POST['tpl_folder']."/".$_POST['tpl_file_name'].".".$_POST['tpl_file_ext'];
			$data['tpl_page']	= '1';
		}else{
			$tpl_path = $_POST['tpl_path'];
			$data = $tpl_path=='basic' ? $arrLayoutBasic['basic'] : array();
		}

		$data['tpl_desc'] 				= $_POST['tpl_desc'];
		$data['width'] 					= $_POST['width'];
		$data['body_width'] 			= $_POST['body_width'];
		$data['align']					= $_POST['align'];
		$data['font']					= $_POST['font'];
		$data['scrollbarColor']			= $_POST['scrollbarColor'];
		$data['backgroundColor']		= $_POST['backgroundChk']=='color' ? $_POST['backgroundColor'] : '';
		$data['bodyBackgroundColor']	= $_POST['bodyBackgroundChk']=='color' ? $_POST['bodyBackgroundColor'] : '';

		$data['layoutHeader']			= $_POST['layoutHeaderChk']=='hidden' ? 'hidden' : $_POST['layoutHeader'];
		$data['layoutTopBar']			= $_POST['layoutTopBarChk']=='hidden' ? 'hidden' : $_POST['layoutTopBar'];
		$data['layoutMainTopBar']		= $_POST['layoutMainTopBarChk']=='hidden' ? 'hidden' : $_POST['layoutMainTopBar'];
		$data['layoutSide']				= $_POST['layoutSideChk']=='hidden' ? 'hidden' : $_POST['layoutSide'];
		if( $_POST['layoutSideLocation']) $data['layoutSideLocation'] = $_POST['layoutSideLocation'];
		$data['layoutFooter']			= $_POST['layoutFooterChk']=='hidden' ? 'hidden' : $_POST['layoutFooter'];
		$data['layoutScrollLeft']		= $_POST['layoutScrollLeftChk']=='hidden' ? 'hidden' : $_POST['layoutScrollLeft'];
		$data['layoutScrollRight']		= $_POST['layoutScrollRightChk']=='hidden' ? 'hidden' : $_POST['layoutScrollRight'];

		if($_POST['backgroundChk']=='image'){
			$data['backgroundImage']		= $_POST['backgroundImage'] ? $_POST['backgroundImage'] : $_POST['oBackgroundImage'];
			$data['backgroundRepeat']		= $_POST['backgroundRepeat'];
			$data['backgroundPosition']		= $_POST['backgroundPosition'];
		}else{
			$data['backgroundImage']		= '';
			$data['backgroundRepeat']		= '';
			$data['backgroundPosition']		= '';
		}

		if($_POST['bodyBackgroundChk']=='image'){
			$data['bodyBackgroundImage']	= $_POST['bodyBackgroundImage'] ? $_POST['bodyBackgroundImage'] : $_POST['oBodyBackgroundImage'];
		}else{
			$data['bodyBackgroundImage']	= "";
		}

		$saveData = layout_config_load($this->designWorkingSkin,$tpl_path);
		$saveData = $saveData[$tpl_path];

		foreach($data as $key=>$value){

			if($key=='backgroundColor' && empty($_POST['backgroundSetChk'])) continue;
			if($key=='backgroundImage' && empty($_POST['backgroundSetChk'])) continue;
			if($key=='backgroundRepeat' && empty($_POST['backgroundSetChk'])) continue;
			if($key=='backgroundPosition' && empty($_POST['backgroundSetChk'])) continue;
			if($key=='bodyBackgroundColor' && empty($_POST['bodyBackgroundSetChk'])) continue;
			if($key=='bodyBackgroundImage' && empty($_POST['bodyBackgroundSetChk'])) continue;
			if($key=='layoutHeader' && empty($_POST['layoutHeaderSetChk'])) continue;
			if($key=='layoutTopBar' && empty($_POST['layoutTopBarSetChk'])) continue;
			if($key=='layoutMainTopBar' && empty($_POST['layoutMainTopBarSetChk'])) continue;
			if($key=='layoutSide' && empty($_POST['layoutSideSetChk'])) continue;
			if($key=='layoutSideLocation' && empty($_POST['layoutSideSetChk'])) continue;
			if($key=='layoutFooter' && empty($_POST['layoutFooterSetChk'])) continue;
			if($key=='layoutScrollLeft' && empty($_POST['layoutScrollLeftSetChk'])) continue;
			if($key=='layoutScrollRight' && empty($_POST['layoutScrollRightSetChk'])) continue;

			$saveData[$key] = $value;

			if($tpl_path!='basic'){
				if($saveData[$key] == $arrLayoutBasic['basic'][$key]) unset($saveData[$key]);
			}
		}

		layout_config_save($this->designWorkingSkin,$tpl_path,$saveData);


		/* 공통레이아웃설정에서 "마이페이지제외 전체적용" 할 경우 */
		if($tpl_path=='basic' && $_POST['apply_type']=='all'){

			//debug_Var($saveData);

			$query = "select * from fm_config_layout where skin=? and tpl_path!='{$tpl_path}'";
			$query = $this->db->query($query,$this->designWorkingSkin);
			foreach ($query->result_array() as $row){

				$rowConfigData = layout_config_load($this->designWorkingSkin,$row['tpl_path']);
				$rowSaveData = $rowConfigData[$row['tpl_path']];
				foreach($saveData as $key=>$row2){

					if(in_array($key,array('tpl_folder','tpl_path','tpl_desc','tpl_page','regist_date'))) continue;

					if($key=='backgroundColor' && empty($_POST['backgroundSetChk'])) continue;
					if($key=='backgroundImage' && empty($_POST['backgroundSetChk'])) continue;
					if($key=='backgroundRepeat' && empty($_POST['backgroundSetChk'])) continue;
					if($key=='backgroundPosition' && empty($_POST['backgroundSetChk'])) continue;
					if($key=='bodyBackgroundColor' && empty($_POST['bodyBackgroundSetChk'])) continue;
					if($key=='bodyBackgroundImage' && empty($_POST['bodyBackgroundSetChk'])) continue;
					if($key=='layoutHeader' && empty($_POST['layoutHeaderSetChk'])) continue;
					if($key=='layoutSide' && empty($_POST['layoutSideSetChk'])) continue;
					if($key=='layoutSideLocation' && empty($_POST['layoutSideSetChk'])) continue;
					if($key=='layoutFooter' && empty($_POST['layoutFooterSetChk'])) continue;
					if($key=='layoutScrollLeft' && empty($_POST['layoutScrollLeftSetChk'])) continue;
					if($key=='layoutScrollRight' && empty($_POST['layoutScrollRightSetChk'])) continue;

					if($row['tpl_folder']=='mypage'){
						if($key=='layoutSide') continue;
						if($key=='layoutSideLocation') continue;
					}

					$rowSaveData[$key] = $saveData[$key];

					if($rowSaveData[$key] == $arrLayoutBasic['basic'][$key]) unset($rowSaveData[$key]);

				}

				layout_config_save($this->designWorkingSkin,$row['tpl_path'],$rowSaveData);
			}

		}

		if($_POST['mode']=='create'){
			$callback = "parent.parent.DM_window_allpages('{$tpl_path}');";
			openDialogAlert("페이지가 생성 되었습니다.",400,140,'parent',$callback);
		}

		if($_POST['mode']=='edit'){
			$callback = "parent.parent.document.location.reload();document.location.href='about:blank';";
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}

	}

	/* 실제적용 스킨 저장 */
	public function apply_skin(){

		switch($_POST['skinPrefix']){
			case 'mobile':
				if($_POST['type']=='realSkin')		config_save('system',array('mobileSkin'=>$_POST['skin']));
				if($_POST['type']=='workingSkin')	config_save('system',array('workingMobileSkin'=>$_POST['skin']));
				if($_POST['type']=='skin')
				{
													config_save('system',array('mobileSkin'=>$_POST['skin']));
													config_save('system',array('workingMobileSkin'=>$_POST['skin']));
				}
			break;
			case 'fammerce':
				if($_POST['type']=='realSkin')		config_save('system',array('fammerceSkin'=>$_POST['skin']));
				if($_POST['type']=='workingSkin')	config_save('system',array('workingFammerceSkin'=>$_POST['skin']));
				if($_POST['type']=='skin')
				{
													config_save('system',array('fammerceSkin'=>$_POST['skin']));
													config_save('system',array('workingFammerceSkin'=>$_POST['skin']));
				}
			break;
			case 'store':
				if($_POST['type']=='realSkin')		config_save('system',array('storeSkin'=>$_POST['skin']));
				if($_POST['type']=='workingSkin')	config_save('system',array('workingStoreSkin'=>$_POST['skin']));
				if($_POST['type']=='skin')
				{
													config_save('system',array('storeSkin'=>$_POST['skin']));
													config_save('system',array('workingStoreSkin'=>$_POST['skin']));
				}
			break;
			case 'storemobile':
				if($_POST['type']=='realSkin')		config_save('system',array('storemobileSkin'=>$_POST['skin']));
				if($_POST['type']=='workingSkin')	config_save('system',array('workingStoremobileSkin'=>$_POST['skin']));
				if($_POST['type']=='skin')
				{
													config_save('system',array('storemobileSkin'=>$_POST['skin']));
													config_save('system',array('workingStoremobileSkin'=>$_POST['skin']));
				}
			break;
			case 'storefammerce':
				if($_POST['type']=='realSkin')		config_save('system',array('storefammerceSkin'=>$_POST['skin']));
				if($_POST['type']=='workingSkin')	config_save('system',array('workingStorefammerceSkin'=>$_POST['skin']));
				if($_POST['type']=='skin')
				{
													config_save('system',array('storefammerceSkin'=>$_POST['skin']));
													config_save('system',array('workingStorefammerceSkin'=>$_POST['skin']));
				}
			break;
			default:
				if($_POST['type']=='realSkin')		config_save('system',array('skin'=>$_POST['skin']));
				if($_POST['type']=='workingSkin')	config_save('system',array('workingSkin'=>$_POST['skin']));
				if($_POST['type']=='skin')
				{
													config_save('system',array('skin'=>$_POST['skin']));
													config_save('system',array('workingSkin'=>$_POST['skin']));
				}
			break;
		}

	}

	/* 스킨 백업 */
	public function backup_skin(){
		$this->load->model('designmodel');
		$skin = $_GET['skin'];

		$backup_file_name = $skin."_".date('YmdHis').".zip";

		$backup_file_contents = $this->designmodel->export_skin($skin);

		/* zip 다운로드 */
		force_download($backup_file_name, $backup_file_contents);
	}

	/* 스킨 복사 */
	public function copy_skin(){

		$this->load->library('zipfile');
		$this->load->helper('download');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('designmodel');

		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		if($this->demo){
			openDialogAlert("체험 사이트에서는 해당 기능을 제공하지 않습니다.",300,140,'parent');
			exit;
		}

		$skin = $_GET['skin'];
		$skin_path = ROOTPATH."data/skin/".$skin;
		$skin_idx = 0;

		/* 새로운 스킨명 생성 */
		do {
			$skin_idx++;
			$new_skin = $skin.'_'.$skin_idx;
			$new_skin_path = ROOTPATH."data/skin/".$new_skin;
		}while(is_dir($new_skin_path));

		/* 스킨폴더 생성 */
		if(!is_dir($new_skin_path)) mkdir($new_skin_path);
		chmod($new_skin_path,0777);

		/* 스킨파일 복사 */
		$map = directory_map_list(directory_map($skin_path,false,false));
		foreach($map as $k=>$v) {
			if(is_dir($skin_path.$v)) {
				if(!is_dir($new_skin_path.$v)) mkdir($new_skin_path.$v);
			}
			else{
				copy($skin_path.$v,$new_skin_path.$v);
			}
			chmod($new_skin_path.$v,0777);

			if(empty($_SERVER['WINDIR'])){
				@exec("chmod 777 {$new_skin_path} -R");
			}
		}

		/* layout.sql 생성 */
		$config_layout_queries = array();
		$query = "select * from fm_config_layout where skin=?";
		$query = $this->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$row['skin'] = $new_skin;
			$keys = "`".implode("`,`",array_keys($row))."`";
			$values = "'".implode("','",array_map("addslashes",$row))."'";
			$config_layout_queries[] = sprintf("INSERT INTO `fm_config_layout` (%s) values (%s);",$keys,$values);
		}

		/* layout config insert */
		$query = "delete from fm_config_layout where skin=?";
		$this->db->query($query,$new_skin);
		foreach($config_layout_queries as $config_layout_query){
			$this->db->query($config_layout_query);
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

		/* flash.sql 생성 */
		$query = "select * from fm_design_flash where skin=?";
		$query = $this->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$flash_seq = $row['flash_seq'];
			unset($row['flash_seq']);
			$row['skin'] = $new_skin;
			$keys = "`".implode("`,`",array_keys($row))."`";
			$values = "'".implode("','",array_map("addslashes",$row))."'";
			$insert_query = sprintf("INSERT INTO `fm_design_flash` (%s) values (%s);",$keys,$values);
			$this->db->query($insert_query);
			$new_flash_seq = $this->db->insert_id();

			$query2 = "select * from fm_design_flash_file where flash_seq=?";
			$query2 = $this->db->query($query2,$flash_seq);
			foreach ($query2->result_array() as $row2){
				unset($row2['flash_file_seq']);
				$row2['flash_seq'] = $new_flash_seq;
				$keys = "`".implode("`,`",array_keys($row2))."`";
				$values = "'".implode("','",array_map("addslashes",$row2))."'";
				$insert_query = sprintf("INSERT INTO `fm_design_flash_file` (%s) values (%s);",$keys,$values);
				$this->db->query($insert_query);
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

		/* banner.sql 생성 */
		$query = "select * from fm_design_banner where skin=?";
		$query = $this->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$banner_seq = $row['banner_seq'];
			$row['skin'] = $new_skin;
			$keys = "`".implode("`,`",array_keys($row))."`";
			$values = "'".implode("','",array_map("addslashes",$row))."'";
			$insert_query = sprintf("INSERT INTO `fm_design_banner` (%s) values (%s);",$keys,$values);
			$this->db->query($insert_query);
			
			$query2 = "select * from fm_design_banner_item where skin=? and banner_seq=?";
			$query2 = $this->db->query($query2,array($skin,$banner_seq));
			foreach ($query2->result_array() as $row2){
				unset($row2['banner_item_seq']);
				$row2['skin'] = $new_skin;
				$keys = "`".implode("`,`",array_keys($row2))."`";
				$values = "'".implode("','",array_map("addslashes",$row2))."'";
				$insert_query = sprintf("INSERT INTO `fm_design_banner_item` (%s) values (%s);",$keys,$values);
				$this->db->query($insert_query);
			}
		}

		skin_configuration_save($new_skin,"skin",$new_skin);

		openDialogAlert("{$new_skin} 스킨 생성",400,140,'parent',"parent.load_skin_list();");

	}

	/* 스킨 이름변경 */
	public function rename_skin(){

		$skin = $_POST['skin'];
		$skinName = trim($_POST['skinName']);
		$skinFolder = trim($_POST['skinFolder']);
		$skinPrefix = $_POST['skinPrefix'];

		$skin_path = ROOTPATH."data/skin/".$skin;

		if(!preg_match("/^[a-zA-Z0-9\s_가-힝]+$/",$skinName) || !$skinName){
			openDialogAlert("스킨명에 허용하지 않는 문자열이 포함되었습니다.",400,140,'parent');
			exit;
		}

		/* 스킨 명칭 변경 */
		skin_configuration_save($skin,"name",$skinName);

		$category_config = skin_configuration($skin);

		/* 스킨 폴더명 변경 */
		if($skin != $skinFolder){

			if(!preg_match("/^[a-z0-9_]+$/",$skinFolder) || !$skinFolder){
				openDialogAlert("폴더명에 허용하지 않는 문자열이 포함되었습니다.",400,140,'parent');
				exit;
			}

			if($skinPrefix=='mobile'){
				if($category_config['mobile_version']==2){
					if(!preg_match("/^mobile_ver2_/",$skinFolder)){
						openDialogAlert("Mobile Ver2 스킨은 mobile_ver2_로 시작해야합니다.",400,140,'parent');
						exit;
					}
				}else{
					if(preg_match("/^mobile_ver2_/",$skinFolder)){
						openDialogAlert("Mobile Ver2스킨이 아니므로 mobile_ver2_로 시작할 수 없습니다.",400,140,'parent');
						exit;
					}
				}
				if(!preg_match("/^mobile_/",$skinFolder)){
					openDialogAlert("Mobile/Tablet 스킨은 mobile_로 시작해야합니다.",400,140,'parent');
					exit;
				}
			}elseif($skinPrefix=='fammerce'){
				if(!preg_match("/^fammerce_/",$skinFolder)){
					openDialogAlert("Facebook PC 스킨은 fammerce_로 시작해야합니다.",400,140,'parent');
					exit;
				}
			}elseif($skinPrefix=='store'){
				if(!preg_match("/^store_/",$skinFolder)){
					openDialogAlert("매장용 PC 스킨은 store_로 시작해야합니다.",400,140,'parent');
					exit;
				}
			}elseif($skinPrefix=='storemobile'){
				if(!preg_match("/^storemobile_/",$skinFolder)){
					openDialogAlert("매장용 Mobile 스킨은 storemobile_로 시작해야합니다.",400,140,'parent');
					exit;
				}
			}elseif($skinPrefix=='storefammerce'){
				if(!preg_match("/^storefammerce_/",$skinFolder)){
					openDialogAlert("매장용 Facebook PC 스킨은 storefammerce_로 시작해야합니다.",400,140,'parent');
					exit;
				}
			}else{
				if(preg_match("/^mobile_/",$skinFolder)){
					openDialogAlert("Mobile/Tablet 스킨만  mobile_로 시작할 수 있습니다.",400,140,'parent');
					exit;
				}
				if(preg_match("/^fammerce_/",$skinFolder)){
					openDialogAlert("Facebook PC 스킨만  fammerce_로 시작할 수 있습니다.",400,140,'parent');
					exit;
				}
				if(preg_match("/^store_/",$skinFolder)){
					openDialogAlert("매장용 PC 스킨만  store_로 시작할 수 있습니다.",400,140,'parent');
					exit;
				}
				if(preg_match("/^storemobile_/",$skinFolder)){
					openDialogAlert("매장용 Mobile 스킨만  storemobile_로 시작할 수 있습니다.",400,140,'parent');
					exit;
				}
				if(preg_match("/^storefammerce_/",$skinFolder)){
					openDialogAlert("매장용 Facebook PC 스킨만  storefammerce_로 시작할 수 있습니다.",400,140,'parent');
					exit;
				}
			}

			$new_skin_path = ROOTPATH."data/skin/".$skinFolder;

			if(is_dir($new_skin_path)){
				openDialogAlert("동일한 폴더명의 스킨이 존재합니다.",400,140,'parent');
				exit;
			}

			@rename($skin_path,$new_skin_path);
			@chmod($new_skin_path,0777);

			$sql = "update fm_config_layout set skin=? where skin=?";
			$query = $this->db->query($sql,array($skinFolder,$skin));

			$sql = "update fm_design_flash set skin=? where skin=?";
			$query = $this->db->query($sql,array($skinFolder,$skin));

			$sql = "update fm_design_banner set skin=? where skin=?";
			$query = $this->db->query($sql,array($skinFolder,$skin));

			$sql = "update fm_design_banner_item  set skin=? where skin=?";
			$query = $this->db->query($sql,array($skinFolder,$skin));

			skin_configuration_save($skinFolder,"skin",$skinFolder);

			switch($skinPrefix){
				case 'mobile':
					if($this->config_system['mobileSkin']==$skin)				config_save('system',array('mobileSkin'=>$skinFolder));
					if($this->config_system['workingMobileSkin']==$skin)		config_save('system',array('workingMobileSkin'=>$skinFolder));
				break;
				case 'fammerce':
					if($this->config_system['fammerceSkin']==$skin)				config_save('system',array('fammerceSkin'=>$skinFolder));
					if($this->config_system['workingFammerceSkin']==$skin)		config_save('system',array('workingFammerceSkin'=>$skinFolder));
				break;
				case 'store':
					if($this->config_system['storeSkin']==$skin)				config_save('system',array('storeSkin'=>$skinFolder));
					if($this->config_system['workingStoreSkin']==$skin)			config_save('system',array('workingStoreSkin'=>$skinFolder));
				break;
				case 'storemobile':
					if($this->config_system['storemobileSkin']==$skin)			config_save('system',array('storemobileSkin'=>$skinFolder));
					if($this->config_system['workingStoremobileSkin']==$skin)	config_save('system',array('workingStoremobileSkin'=>$skinFolder));
				break;
				case 'storefammerce':
					if($this->config_system['storefammerceSkin']==$skin)			config_save('system',array('storefammerceSkin'=>$skinFolder));
					if($this->config_system['workingStorefammerceSkin']==$skin)	config_save('system',array('workingStorefammerceSkin'=>$skinFolder));
				break;
				default:
					if($this->config_system['skin']==$skin)						config_save('system',array('skin'=>$skinFolder));
					if($this->config_system['workingSkin']==$skin)				config_save('system',array('workingSkin'=>$skinFolder));
				break;
			}

		}

		/* 스킨 스크린샷 변경 */
		if (is_uploaded_file($_FILES['skinScreenshot']['tmp_name'])) {
			$config = array();
			$upload_path = "data/skin/".$skinFolder."/configuration";
			$file_ext = end(explode('.', $_FILES['skinScreenshot']['name']));//확장자추출
			$config['upload_path']			= ROOTPATH.$upload_path;
			$config['allowed_types']		= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= "screenshot.".$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('skinScreenshot')) {
				@chmod($config['upload_path'].'/'.$config['file_name'], 0777);
				skin_configuration_save($skin,"screenshot",$config['file_name']);
			}else{
				$callback = "";
				openDialogAlert("스크린샷은 gif, jpg, jpeg, png 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}

		openDialogAlert("스킨 이름이 변경되었습니다.",400,140,'parent',"parent.document.location.reload();document.location.href='about:blank';");

	}

	/* 스킨 삭제 */
	public function delete_skin(){

		$this->load->helper('directory');

		if($this->demo){
			openDialogAlert("체험 사이트에서는 해당 기능을 제공하지 않습니다.",300,140,'parent');
			exit;
		}

		$skin = $_GET['skin'];
		$skin_path = ROOTPATH."data/skin/".$skin;
		$skinPrefix = $_GET['skinPrefix'];

		switch($skinPrefix){
			case 'mobile':
				if($this->config_system['mobileSkin']==$skin)				{openDialogAlert("현재 적용된 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
				if($this->config_system['workingMobileSkin']==$skin)		{openDialogAlert("작업중인 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
			break;
			case 'fammerce':
				if($this->config_system['fammerceSkin']==$skin)				{openDialogAlert("현재 적용된 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
				if($this->config_system['workingFammerceSkin']==$skin)		{openDialogAlert("작업중인 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
			break;
			case 'store':
				if($this->config_system['storeSkin']==$skin)				{openDialogAlert("현재 적용된 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
				if($this->config_system['workingStoreSkin']==$skin)			{openDialogAlert("작업중인 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
			break;
			case 'storemobile':
				if($this->config_system['storemobileSkin']==$skin)			{openDialogAlert("현재 적용된 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
				if($this->config_system['workingStoremobileSkin']==$skin)	{openDialogAlert("작업중인 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
			break;
			case 'storefammerce':
				if($this->config_system['storefammerceSkin']==$skin)			{openDialogAlert("현재 적용된 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
				if($this->config_system['workingStorefammerceSkin']==$skin)	{openDialogAlert("작업중인 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
			break;
			default:
				if($this->config_system['skin']==$skin)						{openDialogAlert("현재 적용된 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
				if($this->config_system['workingSkin']==$skin)				{openDialogAlert("작업중인 스킨은 삭제할 수 없습니다.",400,140,'parent');exit;}
			break;
		}

		/* layout config delete */
		$query = "delete from fm_config_layout where skin=?";
		$this->db->query($query,$skin);

		/* flash delete */
		$query = "select * from fm_design_flash where skin=?";
		$query = $this->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$query = "delete from fm_design_flash_file where flash_seq=?";
			$this->db->query($query,$row['flash_seq']);
		}
		$query = "delete from fm_design_flash where skin=?";
		$this->db->query($query,$skin);

		/* slide banner delete */
		$query = "select * from fm_design_banner where skin=?";
		$query = $this->db->query($query,$skin);
		foreach ($query->result_array() as $row){
			$query = "delete from fm_design_banner_item where skin=? and banner_seq=?";
			$this->db->query($query,array($skin,$row['banner_seq']));
		}
		$query = "delete from fm_design_banner where skin=?";
		$this->db->query($query,$skin);

		/* 스킨폴더 내 파일 및 디렉토리 삭제 */
		$map = directory_map_list(directory_map($skin_path,false,true));
		rsort($map);
		foreach($map as $k=>$v) {
			chmod($skin_path.$v,0777);
			if(is_file($skin_path.$v)){
				unlink($skin_path.$v);
			}else{
				rmdir($skin_path.$v);
			}
		}

		if(rmdir($skin_path)){
			openDialogAlert("{$skin} 스킨을 삭제하였습니다.",400,140,'parent',"parent.load_skin_list();");
		}elseif(is_dir($skin_path)){
			openDialogAlert("{$skin} 스킨을 삭제 실패",400,140,'parent',"parent.load_skin_list();");
		}else{
			openDialogAlert("{$skin} 스킨은 존재하지 않습니다.",400,140,'parent',"parent.load_skin_list();");
		}
	}

	/* 스킨 다운로드&업로드 */
	public function download_skin(){
		$this->load->helper('file');
		$this->load->helper('readurl');
		$this->load->model('designmodel');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		if($this->demo){
			openDialogAlert("체험 사이트에서는 해당 기능을 제공하지 않습니다.",300,140,'parent');
			exit;
		}

		$url = urldecode($_POST['url']);

		if(!preg_match("/\.zip$/",$url)){
			openDialogAlert("스킨 ZIP 파일이 아닙니다.",300,140,'parent');
			exit;
		}

		if(!$url){
			openDialogAlert("업로드할 스킨 URL이 없습니다.",300,140,'parent');
			exit;
		}

		$data = readurl($url);
		$zip_path = ROOTPATH."data/tmp/download_skin_tmp_".time().".zip";
		$res = write_file($zip_path, $data);

		if(!$res){
			openDialogAlert("스킨 ZIP파일 다운로드에 실패했습니다.",300,140,'parent');
			exit;
		}

		$success = $this->designmodel->zip_extract_skin($zip_path);

		if($success == true){
			openDialogAlert("{$new_skin} 스킨이 추가되었습니다.",400,140,'parent',"parent.load_skin_list();");
		}

	}

	/* 스킨 업로드 */
	public function upload_skin(){

		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->model('designmodel');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		if(!$_FILES['skin_zipfile']['name']){
			openDialogAlert("업로드할 스킨 ZIP파일을 첨부해주세요.",300,140,'parent');
			exit;
		}

		if(!strpos($_FILES['skin_zipfile']['type'],'x-zip')){
			openDialogAlert("ZIP파일이 아닙니다..",300,140,'parent');
			exit;
		}

		$success = $this->designmodel->zip_extract_skin($_FILES['skin_zipfile']['tmp_name']);

		if($success == true){
			openDialogAlert("{$new_skin} 스킨이 추가되었습니다.",400,140,'parent',"parent.document.location.reload();document.location.href='about:blank';");
		}
	}

	/* 소스편집 저장 */
	public function sourceeditor_save(){
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->helper('readurl');
		$this->load->model('usedmodel');

		// 데모몰에서는 차단
		if($this->demo){
			openDialogAlert("데모몰에서는 소스편집이 불가합니다.",400,140,'parent');
			exit;
		}

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		if(!isset($_POST['skin']) || !$_POST['skin']){
			openDialogAlert("skin 값 누락",300,140,'parent');
			exit;
		}

		if(!isset($_POST['tpl_path']) || !$_POST['tpl_path']){
			openDialogAlert("tpl_path 값 누락",300,140,'parent');
			exit;
		}

		/* tpl_path assign */
		$skin = $_POST['skin'];
		$skinPath = ROOTPATH."data/skin";
		$tpl_path = $_POST['tpl_path'];
		$tpl_realpath = $skinPath."/".$skin."/".$tpl_path;
		$tpl_source = $_POST['tpl_source'];

		backup_skin_file($skin,$tpl_path);

		write_file($tpl_realpath, $tpl_source);

		echo js("parent.sourceeditor_edited_mark_off();");
		//openDialogAlert("{$tpl_path} 저장완료",400,140,'parent',"parent.document.getElementById('source_edited_mark').style.display='none';");

		if($_POST['reload']){
			openDialogAlert("{$tpl_path} 저장완료",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			openDialogAlert("{$tpl_path} 저장완료",400,140,'parent',"");
		}
	}

	/* eye editor 소스편집 저장 */
	public function eye_editor_save(){
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->helper('readurl');
		$this->load->model('usedmodel');

		// 데모몰에서는 차단
		if($this->demo){
			$result = array(
				'code'	=> 'failure',
				'msg'	=> "데모몰에서는 소스편집이 불가합니다.",
			);
			echo json_encode($result);
			exit;
		}

		$result = array(
			'code'	=> 'success',
			'msg'	=> '',
		);

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			$result = array(
				'code'	=> 'failure',
				'msg'	=> $res['msg'],
			);
			echo json_encode($result);
			exit;
		}

		if(!isset($_POST['tplPath']) || !$_POST['tplPath']){
			$result = array(
				'code'	=> 'failure',
				'msg'	=> "tplPath 값 누락",
			);
			echo json_encode($result);
			exit;
		}

		/* tplPath assign */
		$tplPath = $_POST['tplPath'];
		$tpl_realpath = ROOTPATH.$tplPath;
		$tpl_source = $_POST['tplSource'];

		if(!backup_file($tplPath)){
			$result = array(
				'code'	=> 'failure',
				'msg'	=> "파일 백업 실패",
			);
			echo json_encode($result);
			exit;
		}

		if(!write_file($tpl_realpath, $tpl_source)){
			$result = array(
				'code'	=> 'failure',
				'msg'	=> "파일 저장 실패",
			);
			echo json_encode($result);
			exit;
		}

		if	(preg_match('/\/\_modules\/display\//', $tplPath)){
			// 해당 디스플레이 캐싱 파일 삭제
			$this->load->model('goodsdisplay');
			$this->goodsdisplay->delete_display_cach();
		}

		@chmod($tpl_realpath,0777);

		$result = array(
			'code'		=> 'success',
			'filemtime'	=> filemtime($tpl_realpath),
			'msg'		=> "{$tplPath} 저장완료",
		);
		echo json_encode($result);
	}

	/* 새 파일 생성 */
	public function eye_editor_newpage(){

		$this->load->helper('file');

		$newFileDirectory = preg_replace("/^\//","",$_POST['newFileDirectory']);
		$newFileName = $_POST['newFileName'];

		$newFileDirectoryReal = ROOTPATH.$newFileDirectory;
		$newFilePath = $newFileDirectory.'/'.$newFileName;
		$newFilePathReal = ROOTPATH.$newFilePath;

		$newFileExtTmp = explode('.',$newFileName);
		$newFileExt = $newFileExtTmp[count($newFileExtTmp)-1];

		if(!in_array($newFileExt,array('html','htm','css','js','ini','txt','cvs'))){
			$result = array(
				'code'		=> 'failure',
				'msg'		=> "허용되지 않는 파일확장자입니다."
			);
			echo json_encode($result);
			exit;
		}

		if(preg_match("/\.\./",$newFilePath)){
			$result = array(
				'code'		=> 'failure',
				'msg'		=> "파일명에 '..'이 포함될 수 없습니다."
			);
			echo json_encode($result);
			exit;
		}

		if(file_exists($newFilePathReal)){
			$result = array(
				'code'		=> 'duplicate',
				'msg'		=> "동일한 이름의 파일이 존재합니다."
			);
			echo json_encode($result);
			exit;
		}

		$res = write_file($newFilePathReal, '');
		@chmod($newFilePathReal,0777);

		if(!$res){
			$result = array(
				'code'		=> 'failure',
				'msg'		=> "파일 생성 실패"
			);
			echo json_encode($result);
			exit;
		}

		if(preg_match("/^data\/skin\/([^\/]+)\/([^\/]+)\/(.*)/",$newFilePath,$matches)){
			$skin = $matches[1];
			$tpl_folder = $matches[2];
			$tpl_path = $tpl_folder.'/'.$matches[3];

			$saveData = array();
			$saveData['skin'] = $skin;
			$saveData['tpl_folder'] = $tpl_folder;
			$saveData['tpl_path'] = $tpl_path;
			$saveData['tpl_desc'] = '';
			$saveData['tpl_page'] = 1;
			$saveData['regist_date'] = date('Y-m-d H:i:s');

			layout_config_save($skin,$tpl_path,$saveData);

		}

		$result = array(
			'code'		=> 'success',
			'tplPath'	=> $newFilePath,
			'msg'		=> "{$newFilePath} 저장완료",
		);
		echo json_encode($result);
	}

	/* 파일 수정시간 반환 */
	public function eye_editor_filemtime(){
		$tplPath = $_POST['tplPath'];
		$tpl_realpath = ROOTPATH.$tplPath;
		echo filemtime($tpl_realpath);
	}

	/* 파일명 중복확인 */
	public function tpl_file_name_chk(){
		$tpl_folder = $_GET['tpl_folder'];
		$tpl_file_name = $_GET['tpl_file_name'];
		$tpl_file_ext = $_GET['tpl_file_ext'];

		$skinPath = ROOTPATH."data/skin/".$this->designWorkingSkin."/".$tpl_folder."/".$tpl_file_name;

		if($tpl_file_ext) $skinPath .= ".".$tpl_file_ext;

		$newFileExtTmp = explode('.',$skinPath);
		$newFileExt = $newFileExtTmp[count($newFileExtTmp)-1];

		if(!preg_match("/^[a-zA-Z0-9_]*$/",$tpl_file_name)){
			echo '0';exit;
		}

		if(!in_array($newFileExt,array('html','htm','css','js','ini','txt','cvs'))){
			echo '0';exit;
		}

		if(preg_match("/\.\./",$tpl_folder) || preg_match("/\.\./",$tpl_file_name)){
			echo '0';exit;
		}

		if(file_exists($skinPath)){
			echo '1';exit;
		}

		echo '1111';
	}

	/* 새로만든페이지 삭제 */
	public function tpl_file_delete(){
		$tplPath = $_POST['tpl_path'];

		$layout_config = layout_config_load($this->designWorkingSkin,$tplPath);

		if(!$tplPath || preg_match("/\.\.\//",$tplPath)){
			openDialogAlert("삭제할 파일 경로가 올바르지 않습니다.",400,140,'parent');
			exit;
		}

		if(!$layout_config[$tplPath]['tpl_page']){
			openDialogAlert("삭제할 수 없는 파일입니다.",400,140,'parent');
			exit;
		}

		$filePath = ROOTPATH.'data/skin/'.$this->designWorkingSkin.'/'.$tplPath;

		@unlink($filePath);

		$this->db->query("delete from fm_config_layout where skin=? and tpl_path=?",array($this->designWorkingSkin,$tplPath));

		$callback= "parent.document.location.reload();";
		openDialogAlert("페이지를 삭제했습니다.", 400, 140, 'parent', $callback);
	}


	/* 중복확인 */
	public function file_name_chk(){
		$tpl_folder = $_GET['tpl_folder'];
		$tpl_file_name = $_GET['tpl_file_name'];

		$skinPath = ROOTPATH.$tpl_folder."/".$tpl_file_name;

		$newFileExtTmp = explode('.',$skinPath);
		$newFileExt = $newFileExtTmp[count($newFileExtTmp)-1];

		if(!preg_match("/^[a-zA-Z0-9_]*$/",$tpl_file_name)){
			echo '0';exit;
		}
		
		if(!in_array($newFileExt,array('html','htm','css','js','ini','txt','cvs'))){
			echo '0';exit;
		}

		if(preg_match("/\.\./",$tpl_folder) || preg_match("/\.\./",$tpl_file_name)){
			echo '0';exit;
		}

		if(file_exists($skinPath)){
			echo '0';exit;
		}

		echo '1';
	}

	/* 이미지 변경 처리 */
	public function image_edit(){
		$this->tempate_modules();
		$this->load->helper('file');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$designTplPath		= isset($_REQUEST['designTplPath']) ? $_REQUEST['designTplPath'] : null;
		$designImgSrc		= isset($_REQUEST['designImgSrc']) ? $_REQUEST['designImgSrc'] : null;
		$designImgSrcOri	= isset($_REQUEST['designImgSrcOri']) ? $_REQUEST['designImgSrcOri'] : null;
		$designImgPath		= isset($_REQUEST['designImgPath']) ? $_REQUEST['designImgPath'] : null;
		$newDesignImgPath	= isset($_REQUEST['newDesignImgPath']) ? $_REQUEST['newDesignImgPath'] : null;
		$link				= isset($_REQUEST['link']) ? trim($_REQUEST['link']) : null;
		$target				= isset($_REQUEST['target']) ? $_REQUEST['target'] : null;
		$imageLabel			= isset($_REQUEST['imageLabel']) ? $_REQUEST['imageLabel'] : null;
		$removeDesignImageArea	= isset($_REQUEST['removeDesignImageArea']) ? true : false;

		$tpl_path			= ROOTPATH."data/skin/".$designTplPath;

		/* data 폴더가 아니면 차단 */
		if($newDesignImgPath){
			if(!$this->_datapath_check($newDesignImgPath) || !$this->_datapath_check($designImgPath)){
				openDialogAlert("권한이 없습니다.",400,140,'parent');
				exit;
			}
		}

		/* 파일이 있으면 */
		if($_POST['newDesignImgPath']){
			if(!copy($_POST['newDesignImgPath'],$_POST['designImgPath'])){
				openDialogAlert("파일 카피 실패",400,140,'parent');
				exit;
			}
		}

		/* 소스 불러오기 */
		$source = read_file($tpl_path);

		/* src 보정 */
		$designImgSrcOriForReg = addslashes($designImgSrcOri);
		$designImgSrcOriForReg = str_replace(array("/",".","(",")","[","]"),array("\\/","\\.","\\(","\\)","\\[","\\]"),$designImgSrcOriForReg);

		/* 이미지 영역 제거 */
		if($removeDesignImageArea){
			$source = preg_replace_callback("/<a [^>]*>(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*>)<\/a>/i",create_function('$matches','return "";'), $source);
			$source = preg_replace_callback("/<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*>/i",create_function('$matches','return "";'), $source);
		}
		/* 링크주소 보정 */
		else {
			/* link 보정*/
			$link = str_replace(array('\'','"',';','\\'),'',$link);

			/* a태그 link추가 */
			if($link){
				if(preg_match("/<a [^>]*>(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*>)<\/a>/i",$source)){
					// a태그가 있으면 수정
					$source = preg_replace_callback("/(<a[^>]*href=[\'|\"])[^\'\">]*([\'|\"][^>]*><img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*><\/a>)/i",create_function('$matches','return "{$matches[1]}'.$link.'{$matches[2]}";'), $source);

					if(preg_match("/<a [^>]*target=[\'|\"][^>]*[\'|\"][^>]*>(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*>)<\/a>/i",$source)){
						// target 태그가 있으면 수정
						$source = preg_replace_callback("/(<a[^>]*target=[\'|\"])[^\'\">]*([\'|\"][^>]*><img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*><\/a>)/i",create_function('$matches','return "{$matches[1]}'.$target.'{$matches[2]}";'), $source);

					}else{
						// target 태그가 없으면 추가
						$source = preg_replace_callback("/(<a [^>]*)(><img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*><\/a>)/i",create_function('$matches','return "{$matches[1]} target=\''.$target.'\'{$matches[2]}";'), $source);
					}

					$source = preg_replace_callback("/(<a[^>]*target=[\'|\"])[^>]*([\'|\"][^>]*><img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*><\/a>)/i",create_function('$matches','return "{$matches[1]}'.$target.'{$matches[2]}";'), $source);
				}else{
					// a태그가 없으면 추가
					$source = preg_replace_callback("/<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*>/i",create_function('$matches','return "<a href=\''.$link.'\' target=\''.$target.'\'>$matches[0]</a>";'), $source);
				}
			}else{
				$source = preg_replace_callback("/<a [^>]*>(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*>)<\/a>/i",create_function('$matches','return "{$matches[1]}";'), $source);
			}

			/* img태그 alt추가 */
			if(preg_match("/<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*alt=[\'|\"][^>]*[\'|\"][^>]*>/i",$source)){
				// alt태그가 있으면 수정
				$source = preg_replace_callback("/(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*alt=[\'|\"])[^\'\">]*([\'|\"][^>]*>)/i",create_function('$matches','return "{$matches[1]}'.$imageLabel.'{$matches[2]}";'), $source);
			}else{
				// alt태그가 없으면 추가
				$source = preg_replace_callback("/(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"])([^>]*>)/i",create_function('$matches','return "{$matches[1]} alt=\\"'.$imageLabel.'\\"{$matches[2]}";'), $source);
			}

			/* img태그 title추가 */
			if(preg_match("/<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*title=[\'|\"][^>]*[\'|\"][^>]*>/i",$source)){
				// title태그가 있으면 수정
				$source = preg_replace_callback("/(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"][^>]*title=[\'|\"])[^\'\">]*([\'|\"][^>]*>)/i",create_function('$matches','return "{$matches[1]}'.$imageLabel.'{$matches[2]}";'), $source);
			}else{
				// title태그가 없으면 추가
				$source = preg_replace_callback("/(<img[^>]*src=[\'|\"]".$designImgSrcOriForReg."[\'|\"])([^>]*>)/i",create_function('$matches','return "{$matches[1]} title=\\"'.$imageLabel.'\\"{$matches[2]}";'), $source);
			}
		}

		if(!write_file($tpl_path,$source)){
			openDialogAlert("소스 변경 실패",400,140,'parent');
			exit;
		}

		openDialogAlert("적용되었습니다.",400,140,'parent','parent.parent.document.location.reload()');
	}

	/* 이미지 넣기 처리 */
	public function image_insert(){
		$this->tempate_modules();
		$this->load->helper('file');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$tplPath			= isset($_REQUEST['tplPath']) ? $_REQUEST['tplPath'] : null;
		$newDesignImgPath	= isset($_REQUEST['newDesignImgPath']) ? $_REQUEST['newDesignImgPath'] : null;
		$uploadPath			= isset($_REQUEST['uploadPath']) ? $_REQUEST['uploadPath'] : null;
		$originalFileName	= isset($_REQUEST['originalFileName']) ? $_REQUEST['originalFileName'] : null;

		$link				= isset($_REQUEST['link']) ? trim($_REQUEST['link']) : null;
		$target				= isset($_REQUEST['target']) ? $_REQUEST['target'] : null;
		$imageLabel			= isset($_REQUEST['imageLabel']) ? $_REQUEST['imageLabel'] : null;
		$location			= isset($_REQUEST['location']) ? $_REQUEST['location'] : null;


		$tpl_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$tplPath;

		$this->validation->set_rules('newDesignImgPath', '삽입 이미지','trim|required|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		if(preg_match("/^data\/tmp/",$newDesignImgPath) && !$originalFileName) $originalFileName = basename($newDesignImgPath);
		$uploadFilePath = $uploadPath.'/'.$originalFileName;

		/* data 폴더가 아니면 차단 */
		if($newDesignImgPath){
			if(!$this->_datapath_check($newDesignImgPath) || !$this->_datapath_check($uploadFilePath)){
				openDialogAlert("권한이 없습니다.",400,140,'parent');
				exit;
			}
		}

		/* 파일이 있으면 */
		if($newDesignImgPath && $newDesignImgPath!=$uploadFilePath){
			if(!copy($newDesignImgPath,$uploadFilePath)){
				openDialogAlert("적용에 실패했습니다.",400,140,'parent');
				exit;
			}
			@chmod($uploadFilePath,0777);
		}

		/* src 보정 */
		$relPath = str_repeat('../',count(explode('/',dirname($_REQUEST['tplPath']))));
		$uploadFilePathForReg = addslashes(str_replace("data/skin/".$this->designWorkingSkin."/",$relPath,$uploadFilePath));

		/* 추가소스 */
		$addSource = "<img src=\"{$uploadFilePathForReg}\" alt=\"{$imageLabel}\" title=\"{$imageLabel}\">";

		/* link 보정*/
		$link = str_replace(array('\'','"',';','\\'),'',$link);

		if($link){
			$addSource = "<a href=\"{$link}\" target=\"".$target."\">{$addSource}</a>";
		}

		/* 소스 불러오기 */
		$source = read_file($tpl_path);

		if($location == 'top'){
			$source = $addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource;
		}

		if(!write_file($tpl_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$_REQUEST['tplPath']) || preg_match("/^board/",$_REQUEST['tplPath']) || preg_match("/^goods/",$_REQUEST['tplPath'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$_REQUEST['tplPath']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}

	/* 팝업 넣기 */
	public function popup_insert(){

		$this->load->helper('file');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;

		if(!$params['popup_seq']){
			openDialogAlert("적용할 팝업을 선택해주세요.",400,140,'parent');
			exit;
		}

		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];

		$addSource = "{=showDesignPopup({$params['popup_seq']})}";

		/* 소스 불러오기 */
		$source = read_file($template_path);

		/* 소스 체크 */
		if(preg_match("/".str_replace(array("{","}","(",")"),array("\\{","\\}","\\(","\\)"),$addSource)."/",$source)){
			openDialogAlert("이미 팝업이 띄워져있습니다.",400,140,'parent');
			exit;
		}

		/* 소스 추가 */
		$source = $addSource . "\n" . $source;

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}

	/* 팝업 생성,변경 처리 */
	public function popup_edit(){
		$params = $_POST;

		if($params['popup_seq']){
			$query = $this->db->get_where('fm_design_popup', array('popup_seq'=>$params['popup_seq']));
			$popupData = $query->row_array();
		}else{
			$popupData = array();
		}

		/* 영역 제거 */
		if(isset($params['removeDesignPopupArea']) && $params['removeDesignPopupArea']=='Y'){
			$this->load->model('usedmodel');

			$res = $this->usedmodel->used_limit_check();
			if(!$res['type']){
				openDialogAlert($res['msg'],600,370,'parent');
				exit;
			}

			$this->load->helper('file');
			$tpl_path = ROOTPATH.'data/skin/'.$this->designWorkingSkin.'/'.$params['template_path'];

			/* 소스 불러오기 */
			$source = read_file($tpl_path);

			$removeSource = "{=showDesignPopup({$params['popup_seq']})}";

			$source = str_replace($removeSource,"",$source);

			if(!write_file($tpl_path,$source)){
				openDialogAlert("소스 변경 실패",400,140,'parent');
				exit;
			}
		}

		if(trim($params['admin_comment'])==''){
			openDialogAlert("관리용 코멘트는 필수입력 사항입니다.",400,140,'parent');
			exit;
		}

		if($params['contents_type']=='image' && !$popupData['image'] && !$params['newImgPath']){
			openDialogAlert("팝업 이미지를 첨부해주세요",400,140,'parent');
			exit;
		}

		/* 본문 배경이미지 업로드시 */
		if (is_uploaded_file($_FILES['band_background_image']['tmp_name'])) {
			$upload_path = "data/skin/".$this->designWorkingSkin."/images/design";
			$config['upload_path']		= ROOTPATH.$upload_path;
			$file_ext = end(explode('.', $_FILES['band_background_image']['name']));//확장자추출
			$config['allowed_types']	= 'jpg|gif|jpeg|png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= "body_background_image".substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);

			if ($this->upload->do_upload('band_background_image')) {
				@chmod($config['upload_path'].'/'.$config['file_name'], 0777);
				$_POST['band_background_image'] = $config['file_name'];
			}else{
				$callback = "";
				openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);
				exit;
			}
		}

		if($params['band_background_type']=='image'){

			if($params['new_band_background_image']){
				$params['band_background_image'] = basename(str_replace('temp_','popup_bandbg_',$params['new_band_background_image']));
				@copy($_POST['new_band_background_image'],ROOTPATH.'data/popup/'.$params['band_background_image']);
				@chmod(ROOTPATH.'data/popup/'.$params['band_background_image'],0777);

				if($popupData['band_background_image']){
					@unlink(ROOTPATH.'data/popup/'.$popupData['band_background_image']);
				}
			}

			$band_background_image['band_background_image']		= $params['band_background_image'] ? $params['band_background_image'] : $params['o_band_background_image'];
			$params['band_background_color']='';
		}else{
			if($popupData['band_background_image']){
				@unlink(ROOTPATH.'data/popup/'.$popupData['band_background_image']);
			}
			$params['band_background_image']='';
			$params['band_background_image_repeat']='';
			$params['band_background_image_position']='';
		}

		if($params['newImgPath']){
			$params['image'] = basename(str_replace('temp_','popup_',$params['newImgPath']));
			@copy($params['newImgPath'],ROOTPATH.'data/popup/'.$params['image']);
			@chmod(ROOTPATH.'data/popup/'.$params['image'],0777);
		}

		if($params['contents']){
			adjustEditorImages($params['contents']);
		}

		if($params['popup_seq']){
			$data = filter_keys($params, $this->db->list_fields('fm_design_popup'));
			$this->db->update('fm_design_popup', $data, "popup_seq = {$params['popup_seq']}");
		}else{
			$params['regdate'] = date('Y-m-d H:i:s');
			$data = filter_keys($params, $this->db->list_fields('fm_design_popup'));
			$this->db->insert('fm_design_popup', $data);
		}

		if($params['popup_seq']){
			$resultMsg = "팝업이 변경되었습니다.";
			if($params['direct']){
				$resultCallback = "top.document.location.reload();document.location.href='about:blank';";
			}else{
				$resultCallback = "top.DM_window_popup_insert('{$params['template_path']}');";
			}
		}else{
			$resultMsg = "팝업이 저장되었습니다.";
			$resultCallback = "top.DM_window_popup_insert('{$params['template_path']}');";
		}

		openDialogAlert($resultMsg,400,140,'parent',$resultCallback);

		//openDialogAlert("팝업이 저장되었습니다.",400,140,'parent',"parent.parent.DM_window_popup_insert('{$params['template_path']}');");
		//openDialogAlert("팝업이 저장되었습니다.",400,140,'parent','parent.parent.document.location.reload()');

	}

	/* 팝업 복사 */
	public function copy_popup(){
		$popup_seq = $_GET['popup_seq'];

		$query = $this->db->query("select * from fm_design_popup where popup_seq = ?",$popup_seq);
		$data = $query->row_array();

		if($data['image']){
			$imagePathOri = ROOTPATH.'data/popup/'.$data['image'];
			preg_match("/\.[a-zA-Z]{3,4}$/",$data['image'],$matches);
			$data['image'] = "popup_".time().sprintf("%04d",rand(0,9999)).$matches[0];
			@copy($imagePathOri,ROOTPATH.'data/popup/'.$data['image']);
			@chmod(ROOTPATH.'data/popup/'.$data['image'],0777);
		}

		unset($data['popup_seq']);
		$data['regdate'] = date('Y-m-d H:i:s');

		$query = $this->db->insert_string('fm_design_popup', $data);
		$this->db->query($query);

		openDialogAlert("팝업이 복사되었습니다.",400,140,'parent',"parent.load_popup_list();");

	}

	/* 팝업 삭제 */
	public function delete_popup(){
		$popup_seqs = $_GET['popup_seqs'];
		$popup_seqs = explode(',',$popup_seqs);
		foreach($popup_seqs as $popup_seq){
			$query = $this->db->query("select * from fm_design_popup where popup_seq = ?",$popup_seq);
			$data = $query->row_array();

			if($data['image']){
				@unlink(ROOTPATH.'data/popup/'.$data['image']);
			}

			$this->db->query("delete from fm_design_popup where popup_seq = ?",$popup_seq);
		}

		openDialogAlert("팝업이 삭제되었습니다.",400,140,'parent',"parent.load_popup_list();");
	}

	/* 플래시 넣기 */
	public function flash_insert(){

		$this->load->helper('file');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;

		if(!$params['flash_seq']){
			openDialogAlert("적용할 플래시를 선택해주세요.",400,140,'parent');
			exit;
		}

		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];

		$addSource = "{=showDesignFlash({$params['flash_seq']})}";

		/* 소스 불러오기 */
		$source = read_file($template_path);

		if($params['location'] == 'top'){
			$source = $addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource;
		}

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}

	/* 플래시 변경 처리 */
	public function flash_edit(){

		$this->load->library('SofeeXmlParser');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;

		/* 영역 제거 */
		if(isset($params['removeDesignFlashArea']) && $params['removeDesignFlashArea']=='Y'){

			$this->load->helper('file');
			$tpl_path = ROOTPATH.'data/skin/'.$this->designWorkingSkin.'/'.$params['template_path'];

			/* 소스 불러오기 */
			$source = read_file($tpl_path);

			$removeSource = "{=showDesignFlash({$params['flash_seq']})}";

			$source = str_replace($removeSource,"",$source);

			if(!write_file($tpl_path,$source)){
				openDialogAlert("소스 변경 실패",400,140,'parent');
				exit;
			}
		}

		error_reporting(E_ALL ^ E_NOTICE);

		$query = $this->db->query("select a.*,b.url xmlurl from fm_design_flash a,fm_design_flash_file b where a.flash_seq=? and a.flash_seq=b.flash_seq and b.type='xml' and url like '%data.xml' limit 1",$_POST['flash_seq']);
		$data = $query->row_array();

		$xmlParser = new SofeeXmlParser();
		$xml_url = "http://".$_SERVER['HTTP_HOST'].$data['xmlurl'];

		$xmlParser->parseFile($xml_url);
		$tree = $xmlParser->getTree();

		$options = $tree['data']['option'] ? $tree['data']['option'] : $tree['data'];

		$items = $tree['data']['item'];
		if(!is_numeric(key($tree['data']['item']))){
			$items[0] = $items;
		}

		$flashW = (int) $_POST['flashW'];
		$flashH = (int) $_POST['flashH'];

		if($flashW == 0){
			openDialogAlert("플래시 넓이를 입력해주세요.",400,140,'parent');
			exit;
		}else if($flashH == 0){
			openDialogAlert("플래시 높이를 입력해주세요.",400,140,'parent');
			exit;
		}else if(isset($_FILES['title']['tmp_name']) && !$options['title']['value'] && !$_FILES['title']['tmp_name']){
			openDialogAlert("타이틀 이미지를 입력하세요.",400,140,'parent');
			exit;
		}

		if(isset($_FILES['visual']))foreach($_FILES['visual']['tmp_name'] as $k => $img){
			if(!$img && !$_POST['ovisual'][$k]){
				openDialogAlert("플래시 이미지를 입력하세요.",400,140,'parent');
				exit;
			}
		}


		if(isset($_FILES['menuOff']))foreach($_FILES['menuOff']['tmp_name'] as $k => $img){
			if(!$img && !$_POST['omenuOff'][$k]){
				openDialogAlert("버튼 이미지를 입력하세요.",400,140,'parent');
				exit;
			}
		}

		if(isset($_FILES['menuOn']))foreach($_FILES['menuOn']['tmp_name'] as $k => $img){
			if(!$img && !$_POST['omenuOn'][$k]){
				openDialogAlert("롤오버 이미지를 입력하세요.",400,140,'parent');
				exit;
			}
		}

		$options['flashW']['value']=$flashW;
		$options['flashH']['value']=$flashH;

		if($items[0]['visual']['value']){
			$tmp = explode('/',$items[0]['visual']['value']);
			$tmp = array_slice($tmp,0,5);
			$dir = implode('/',$tmp);
		}else{
			$tmp = explode('/',$options['title']['value']);
			$tmp = array_slice($tmp,0,7);
			$dir = implode('/',$tmp);
		}

		// 타이틀 이미지 업로드
		if($_FILES['title']['name']){
			$tmp = explode('.',$_FILES['title']['name']);
			$ext = $tmp[count($tmp)-1];
			$filename =  $dir.'/title_'.time().".".$ext;
			move_uploaded_file($_FILES['title']['tmp_name'],ROOTPATH.$filename);
			$options['title']['value'] = $filename;

			if($options['title']['value']){
				unlink($options['title']['value']);
			}
		}

		// 좌측 메뉴 이미지 업로드
		if($_FILES['prevBtn']['name']){
			$tmp = explode('.',$_FILES['prevBtn']['name']);
			$ext = $tmp[count($tmp)-1];
			$filename =  $dir.'/left_'.time().".".$ext;
			move_uploaded_file($_FILES['prevBtn']['tmp_name'],ROOTPATH.$filename);
			$options['prevBtn']['value'] = $filename;

			if($options['prevBtn']['value']){
				unlink($options['prevBtn']['value']);
			}
		}

		// 우측 메뉴 이미지 업로드
		if($_FILES['nextBtn']['name']){
			$tmp = explode('.',$_FILES['nextBtn']['name']);
			$ext = $tmp[count($tmp)-1];
			$filename =  $dir.'/right_'.time().".".$ext;
			move_uploaded_file($_FILES['nextBtn']['tmp_name'],ROOTPATH.$filename);
			$options['nextBtn']['value'] = $filename;

			if($options['nextBtn']['value']){
				unlink($options['nextBtn']['value']);
			}
		}

		// 플래시 파일 업로드
		if($_FILES['detail']['name'][0]){
			$tmp = explode('.',$_FILES['detail']['name'][0]);
			$ext = $tmp[count($tmp)-1];
			$filename = $dir.'/flash_'.time().$k.".".$ext;
			move_uploaded_file($_FILES['detail']['tmp_name'][0],ROOTPATH.$filename);
			$options['detail']['value'] = $filename;
		}else{
			//원본이 있고 원본과 이름이 다를 경우와 새로등록시
			if($_POST['odetail'][0]) {
				$options['detail']['value'] = $_POST['odetail'][0];
			}
		}

		$num = 0;
		foreach($_POST['items'] as $k => $val){
			$num++;//pixpeen add

			if	(isset($_POST['target'][$k]))
				$new_items[$k]['target']['value'] =  $_POST['target'][$k];
			if	(isset($_POST['url'][$k]))
				$new_items[$k]['url']['value'] =  $_POST['url'][$k];

			// 비주얼-비주얼 파일 업로드
			if($_FILES['thumb']['name'][$k]){
				$tmp = explode('.',$_FILES['thumb']['name'][$k]);
				$ext = $tmp[count($tmp)-1];
				$filename = $dir.'/flash_'.time().$k.".".$ext;
				move_uploaded_file($_FILES['thumb']['tmp_name'][$k],ROOTPATH.$filename);
				$new_items[$k]['thumb']['value'] = $filename;
			}else{
				//원본이 있고 원본과 이름이 다를 경우와 새로등록시
				if(isset($_POST['othumb'][$k])) {
					$new_items[$k]['thumb']['value'] = $_POST['othumb'][$k];
				}
			}

			// 비주얼-플래시 파일 업로드
			if($_FILES['visual']['name'][$k]){
				$tmp = explode('.',$_FILES['visual']['name'][$k]);
				$ext = $tmp[count($tmp)-1];
				$filename = $dir.'/flash_'.time().$k.".".$ext;
				move_uploaded_file($_FILES['visual']['tmp_name'][$k],ROOTPATH.$filename);
				$new_items[$k]['visual']['value'] = $filename;
			}else{
				//원본이 있고 원본과 이름이 다를 경우와 새로등록시
				if($_POST['visual'.$num.'type'] == 'pix' && ( ($_POST['visual'.$num] != $_POST['old_visual'.$num] && $_POST['old_visual'.$num]) || ($_POST['visual'.$num] && !$_POST['old_visual'.$num]) )) {
					$new_items[$k]['visual']['value'] = $dir.'/'.$_POST["visual".$num];
				}else{
					$new_items[$k]['visual']['value'] = ($_POST['old_visual'.$num] && $_POST['visual'.$num.'type'] == 'pix')?$dir.'/'.$_POST['old_visual'.$num]:$_POST['ovisual'][$k];
				}
			}

			// 롤오버 파일 업로드
			if($_FILES['menuOn']['tmp_name'][$k]){
				$tmp = explode('.',$_FILES['menuOn']['name'][$k]);
				$ext = $tmp[count($tmp)-1];
				$filename =  $dir.'/btn_over_'.time().$k.".".$ext;
				move_uploaded_file($_FILES['menuOn']['tmp_name'][$k],ROOTPATH.$filename);
				$new_items[$k]['menuOn']['value'] = $filename;
			}else{
				//원본이 있고 원본과 이름이 다를 경우와 새로등록시
				if($_POST['menuOn'.$num.'type'] == 'pix' && ( ($_POST['menuOn'.$num] != $_POST['old_menuOn'.$num] && $_POST['old_menuOn'.$num]) || ($_POST['menuOn'.$num] && !$_POST['old_menuOn'.$num]) )) {
					$new_items[$k]['menuOn']['value'] = $dir.'/'.$_POST["menuOn".$num];
				// 메뉴 버튼이 없는 스타일이 있어서 수정
				}elseif($_POST['old_menuOn'.$num] || isset($_POST['omenuOn'][$k])){
					$new_items[$k]['menuOn']['value'] = ($_POST['old_menuOn'.$num] && $_POST['menuOn'.$num.'type'] == 'pix')?$dir.'/'.$_POST['old_menuOn'.$num]:$_POST['omenuOn'][$k];
				}
			}

			// 버튼 업로드
			if($_FILES['menuOff']['tmp_name'][$k]){
				$tmp = explode('.',$_FILES['menuOff']['name'][$k]);
				$ext = $tmp[count($tmp)-1];
				$filename =  $dir.'/btn_'.time().$k.".".$ext;
				move_uploaded_file($_FILES['menuOff']['tmp_name'][$k],ROOTPATH.$filename);
				$new_items[$k]['menuOff']['value'] = $filename;
			}else{
				//원본이 있고 원본과 이름이 다를 경우와 새로등록시
				if($_POST['menuOff'.$num.'type'] == 'pix' && ( ($_POST['menuOff'.$num] != $_POST['old_menuOff'.$num] && $_POST['old_menuOff'.$num]) || ($_POST['menuOff'.$num] && !$_POST['old_menuOff'.$num]) )) {
					$new_items[$k]['menuOff']['value'] = $dir.'/'.$_POST["menuOff".$num];
				// 메뉴 버튼이 없는 스타일이 있어서 수정
				}elseif($_POST['old_menuOff'.$num] || isset($_POST['omenuOff'][$k])){
					$new_items[$k]['menuOff']['value'] = ($_POST['old_menuOff'.$num] && $_POST['menuOff'.$num.'type'] == 'pix')?$dir.'/'.$_POST['old_menuOff'.$num]:$_POST['omenuOff'][$k];
				}
			}
		}//endfor
		$handle = fopen(ROOTPATH.$data[xmlurl],"w");
		fwrite($handle,'<?xml version="1.0" encoding="utf-8" ?>'.chr(10));
		fwrite($handle,'<data>'.chr(10));
		if(!isset($options['detail'])){
			fwrite($handle,'<option>'.chr(10));
		}
		foreach($options as $key => $data){
			$item = $data[value];
			fwrite($handle,'<'.$key.'>'.$item.'</'.$key.'>'.chr(10));
		}
		if(!isset($options['detail'])){
			fwrite($handle,'</option>'.chr(10));
		}
		foreach($new_items as $k => $data){
			fwrite($handle,'<item>'.chr(10));
			foreach($data as $key => $data1){
				$item = $data1[value];
				fwrite($handle, '<'.$key.'>'.$item.'</'.$key.'>'.chr(10));
			}
			fwrite($handle,'</item>'.chr(10));
		}

		fwrite($handle,'</data>'.chr(10));
		fclose($handle);

		$seq = (int) $_POST['flash_seq'];
		$name = $_POST['name'];
		$query = "update fm_design_flash set name=?, width=?, height=? where flash_seq=?";
		$this->db->query($query,array($name,$options['flashW']['value'],$options['flashH']['value'],$seq));

		$query = "delete from fm_design_flash_file where flash_seq='$seq' and type='img'";
		$this->db->query($query);
		foreach($new_items as $k => $data){
			foreach($data as $key => $data1){
				$item = $data1[value];
				if( $item && !in_array($key,array('url','target')) ){

					if(preg_match("/\.swf/",$item)){
						$type = "swf";
					}else if(preg_match("/\.xml/",$item)){
						$type = "xml";
					}else if(preg_match("/\.js/",$item)){
						$type = "js";
					}else{
						$type = 'img';
					}

					$query = "insert into fm_design_flash_file set type='$type',flash_seq='$seq',url='$item'";
					$this->db->query($query);
				}
			}
		}

		if($options['title']['value']){
			$query = "insert into fm_design_flash_file set type='xml',flash_seq='$seq',url='".$options['title']['value']."'";
			$this->db->query($query);
		}

		if($params['flash_seq']){
			$resultMsg = "플래시가 변경되었습니다.";
			if($params['direct']){
				$resultCallback = "top.document.location.reload();document.location.href='about:blank';";
			}else{
				$resultCallback = "top.DM_window_flash_insert('{$params['template_path']}');";
			}
		}else{
			$resultMsg = "플래시가 저장되었습니다.";
			$resultCallback = "top.DM_window_flash_edit('{$params['template_path']}');";
		}

		openDialogAlert($resultMsg,400,140,'parent',$resultCallback);

	}

	/* 플래시 삭제 */
	public function delete_flash(){
		$flash_seqs = $_GET['flash_seqs'];

		$query = $this->db->query("select * from fm_design_flash_file where find_in_set(flash_seq,'{$flash_seqs}')");

		foreach ($query->result_array() as $row)
		{
			if($row['url']){
				$imgsrc	= ROOTPATH.$row['url'];
				if (is_file($imgsrc) === true && preg_match('/\/xml\//',$imgsrc))
				{
					unlink($imgsrc);
					$dirsrc = dirname($imgsrc);
				}
			}
		}

		if(is_dir($dirsrc)===true && preg_match('/\/xml/',$dirsrc) ){
			@rmdir($dirsrc);
		}

		$this->db->query("delete from fm_design_flash where find_in_set(flash_seq,'{$flash_seqs}')");
		$this->db->query("delete from fm_design_flash_file where find_in_set(flash_seq,'{$flash_seqs}')");

		openDialogAlert("플래시가 삭제되었습니다.",400,140,'parent',"parent.document.location.reload();document.location.href='about:blank';");
	}

	/* 플래시 추가 ajax */
	public function flash_add(){
		$result = '<div class="flashmagictr" style="margin-bottom:5px;padding:10px;border: 1px solid #e3e3e3;">
		<input type="hidden" name="items[]" value="'.$_POST['nextKey'].'" />
		<table width="100%">
		<tr>
			<td>
				<table width="100%">
				<colgroup>
				<col align="center" width="80" /><col width="300" /><col align="left" />
				</colgroup>';
		if($_POST['use_thumb'] == 'Y'){
			$result .= '<tr><td>비주얼 :</td><td>';
			$result .= '<input type="file" name="thumb[]" class="line" />';
			$result .= '</td><td></td></tr>';
		}
		if($_POST['use_detail'] == 'Y'){
			$result .= '<tr><td>비주얼大 :</td><td>';
			$result .= '<input type="file" name="detail[]" class="line" />';
			$result .= '</td><td></td></tr>';
		}
		if($_POST['use_visual'] == 'Y'){
			$result .= '<tr><td>플래시 :</td><td>';
			$result .= '<input type="file" name="visual[]" class="line" />';
			$result .= '</td><td></td></tr>';
		}
		if($_POST['use_menuOff'] == 'Y'){
			$result .= '<tr><td>버튼 :</td><td>';
			$result .= '<input type="file" name="menuOff[]" class="line" />';
			$result .= '</td><td></td></tr>';
		}
		if($_POST['use_menuOn'] == 'Y'){
			$result .= '<tr><td>롤오버 :</td><td>';
			$result .= '<input type="file" name="menuOn[]" class="line" />';
			$result .= '</td><td></td></tr>';
		}
		if($_POST['use_link'] == 'Y'){
			$result .= '<tr><td>링크 :</td><td>';
			$result .= '<select name="target[]">';
			$result .= '<option value="_parent">현재창</option>';
			$result .= '<option value="_blank">새창</option>';
			$result .= '</select>';
			$result .= '<input type="text" name="url[]" size="33" title="링크주소를 입력해주세요" class="line" />';
			$result .= '</td><td>';
			$result .= '</td></tr>';
		}
		$result .= '</table></td>';
		$result .= '<td><span class="btn small"><button type="button" id="image_del">삭제하기</button></span></td></tr></table></div>';

		echo $result;
	}

	/* 동영상 변경 처리 */
	public function video_edit(){
		$this->tempate_modules();
		$this->load->helper('file');
		$this->load->model('videofiles');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;
		if($parmas['setMode'] != 'pc'){
			$params['realwidth'] = $params['mobile_width'];
			$params['realheight'] = $params['mobile_height'];
		}else{
			$params['realwidth'] = $params['pc_width'];
			$params['realheight'] = $params['pc_height'];
		}

		/* 영역 제거 */
		if(isset($params['removeDesignVideoArea']) && $params['removeDesignVideoArea']=='Y'){

			$this->load->helper('file');
			$tpl_path = ROOTPATH.'data/skin/'.$this->designWorkingSkin.'/'.$params['template_path'];

			/* 소스 불러오기 */
			$source = read_file($tpl_path);

			$removeSource = "{=showDesignVideo({$params['seq']},'{$params['old_realwidth']}X{$params['old_realheight']}')}";

			$source = str_replace($removeSource,"",$source);

			if(!write_file($tpl_path,$source)){
				openDialogAlert("소스 변경 실패",400,140,'parent');
				exit;
			}
		}else{//소스변경
			$removeSource = "{=showDesignVideo({$params['seq']},'{$params['old_realwidth']}X{$params['old_realheight']}')}";
			$addSource = "{=showDesignVideo({$params['seq']},'{$params['realwidth']}X{$params['realheight']}')}";
			$source = str_replace($removeSource,$addSource,$source);
		}

		if($parmas['setMode'] != 'pc'){
			$videofiles['mobile_width']		= $params['realwidth'];//
			$videofiles['mobile_height']		= $params['realheight'];//
		}else{
			$videofiles['pc_width']				= $params['realwidth'];//
			$videofiles['pc_height']			= $params['realheight'];//
		}
		$videofiles['memo']					= $params['memo'];//
		$videofiles['seq']						= $params['seq'];//
		$this->videofiles->videofiles_modify($videofiles);

		error_reporting(E_ALL ^ E_NOTICE);

		openDialogAlert("적용되었습니다.",400,140,'parent','parent.parent.document.location.reload()');
	}

	/* 동영상 넣기 */
	public function video_insert(){

		$this->load->helper('file');
		$this->load->model('videofiles');

		$params = $_POST;

		if(!$params['video_seq']){
			openDialogAlert("적용할 동영상을 선택해주세요.",400,140,'parent');
			exit;
		}

		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];
		$size = ($params['mobile_width'])?$params['mobile_width'].'X'.$params['mobile_height']:$params['pc_width'].'X'.$params['pc_height'];
		$addSource = "{=showDesignVideo({$params['video_seq']},'{$size}')}";

		/* 소스 불러오기 */
		$source = read_file($template_path);

		if($params['location'] == 'top'){
			$source = $addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource;
		}

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}

	/* 동영상연결해제 */
	public function delete_video(){
		$video_seqs = $_GET['video_seqs'];
		$this->db->query("delete from fm_videofiles where find_in_set(seq,'{$video_seqs}')");
		openDialogAlert("동영상이 연결해제되었습니다.",400,140,'parent',"parent.load_video_list();");
	}



	/* 상품디스플레이 넣기 */
	public function display_insert(){

		$this->load->helper('file');
		$this->load->model('goodsdisplay');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;
		$location = isset($params['location']) ? $params['location'] : null;

		if(!$params['display_seq']){
			openDialogAlert("적용할 상품디스플레이를 선택해주세요.",400,140,'parent');
			exit;
		}

		$display_data = $this->goodsdisplay->get_display($params['display_seq']);

		if($display_data['platform']=='mobile' && (!$this->mobileMode && !$this->storemobileMode)){
			openDialogAlert("모바일용 상품 디스플레이는 모바일 화면에만 적용 가능합니다.",400,140,'parent');
			exit;
		}

		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];

		/* 소스 불러오기 */
		$source = read_file($template_path);

		if($params['paging']){
			$perpage = $params['perpage'] ? $params['perpage'] : 10;
			$addSource = "{=showDesignDisplayPaging({$params['display_seq']},$perpage)}";

			$checkSource = "/\{(\s*)=(\s)*showDesignDisplayPaging(\s*)\((\s*)([0-9])+(\s*),(\s*)[0-9]+(\s*)\)(\s*)\}/";
			if(preg_match($checkSource,$source)){
				openDialogAlert("한 페이지에 페이징이 되는 상품디스플레이는 한개만 넣을 수 있습니다.",400,140,'parent');
				exit;
			}

		}else{
			$addSource = "{=showDesignDisplay({$params['display_seq']})}";
		}

		if($location == 'top'){
			$source = $addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource;
		}

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}
	}

	/* 상품디스플레이 생성,변경 처리 */
	public function display_edit(){
		$params = $_POST;

		$display_seq = $params['display_seq'];
		$image_decorations = isset($params['image_decoration']) ? $params['image_decoration'] : array();
		$info_setting = isset($params['info_setting']) ? $params['info_setting'] : array();

		if(empty($params['popup']) && trim($params['admin_comment'])==''){
			openDialogAlert("관리용 코멘트는 필수입력 사항입니다.",400,140,'parent');
			exit;
		}

		/* 영역 제거 */
		if(isset($params['removeDesignDisplayArea']) && $params['removeDesignDisplayArea']=='Y'){

			$this->load->model('usedmodel');

			$res = $this->usedmodel->used_limit_check();
			if(!$res['type']){
				openDialogAlert($res['msg'],600,370,'parent');
				exit;
			}

			$this->load->helper('file');
			$tpl_path = ROOTPATH.'data/skin/'.$this->designWorkingSkin.'/'.$params['template_path'];

			/* 소스 불러오기 */
			$source = read_file($tpl_path);

			if($params['perpage']){
				$removeSource = "/\{(\s*)=(\s)*showDesignDisplayPaging(\s*)\((\s*){$display_seq}(\s*),(\s*){$params['perpage']}(\s*)\)(\s*)\}/";
			}else{
				$removeSource = "/\{(\s*)=(\s*)showDesignDisplay(\s*)\((\s*){$display_seq}(\s*)\)(\s*)\}/";
			}

			$source = preg_replace($removeSource,"",$source);

			if(!write_file($tpl_path,$source)){
				openDialogAlert("소스 변경 실패",400,140,'parent');
				exit;
			}
		}

		if(isset($image_decorations)){
			$params['image_decorations'] = $image_decorations;
		}

		if(isset($info_setting)){
			$params['info_settings'] = "[".implode(",",$info_setting)."]";
		}

		if($display_seq){
			//$params['kind'] = 'design';
			$data = filter_keys($params, $this->db->list_fields('fm_design_display'));
			unset($data['auto_use']);
			$this->db->update('fm_design_display', $data, "display_seq = {$display_seq}");

			$this->db->query("delete from fm_design_display_item where display_seq=?",$display_seq);
		}else{
			$params['kind'] = ($params['displaykind'] == 'designvideo' )?'designvideo':'design';
			$params['regdate'] = date('Y-m-d H:i:s');
			$data = filter_keys($params, $this->db->list_fields('fm_design_display'));
			unset($data['auto_use']);
			$this->db->insert('fm_design_display', $data);

			$display_seq = $this->db->insert_id();
		}

		// 이미지업로드
		if($params['popup_tab_design_kind']=='image'){
			$config = array();
			$config['upload_path'] = ROOTPATH."data/icon/goodsdisplay/tabs";
			$config['max_size']	= $this->config_system['uploadLimit'];
			$config['overwrite'] = true;
			$config['allowed_types'] = 'jpg|gif|jpeg|png';

			if(!is_dir($config['upload_path'])) {
				@mkdir($config['upload_path']);
				@chmod($config['upload_path'],0777);
			}

		}

		$this->db->query("delete from fm_design_display_tab where display_seq=?",$display_seq);
		if(!$params['tab_title']) $params['tab_title'] = array('');
		if(count($params['tab_title'])>1){
			foreach($params['tab_title'] as $tab_index => $tab_title){
				$tab_data = array();
				$tab_data['display_seq'] = $display_seq;
				$tab_data['display_tab_index'] = $tab_index;

				$tab_data['tab_title'] = count($params['tab_title']) > 1 ? $tab_title : '';

				// 이미지업로드
				if($params['popup_tab_design_kind']=='image'){
					if($_FILES['new_tab_title_img']['tmp_name'][$tab_index]){
						$config['file_name'] = "tab_{$display_seq}_{$tab_index}";
						$this->upload->initialize($config);
						$this->upload->do_upload('new_tab_title_img',$tab_index);
						$res = $this->upload->data();
						$tab_data['tab_title_img'] = $res['file_name']?$res['file_name']:$params['tab_title_img'][$tab_index];
					}else{
						$tab_data['tab_title_img'] = $params['tab_title_img'][$tab_index];
					}

					if($_FILES['new_tab_title_img_on']['tmp_name'][$tab_index]){
						$config['file_name'] = "tab_{$display_seq}_{$tab_index}_on";
						$this->upload->initialize($config);
						$this->upload->do_upload('new_tab_title_img_on',$tab_index);
						$res = $this->upload->data();
						$tab_data['tab_title_img_on'] = $res['file_name']?$res['file_name']:$params['tab_title_img_on'][$tab_index];
					}else{
						$tab_data['tab_title_img_on'] = $params['tab_title_img_on'][$tab_index];
					}
				}

				$tab_data['contents_type'] = $params['contents_type'][$tab_index];
				$tab_data['auto_use'] = $params['contents_type'][$tab_index] == 'auto' ? 'y' : 'n';
				$tab_data['auto_criteria'] = $params['auto_criteria'][$tab_index];
				$tab_data['tab_contents'] = adjustEditorImages($params['tab_contents'][$tab_index]);
				$tab_data['tab_contents_mobile'] = adjustEditorImages($params['tab_contents_mobile'][$tab_index]);
				$this->db->insert('fm_design_display_tab', $tab_data);
			}
		}elseif(isset($params['contents_type'])){
			$tab_data = array();
			$tab_data['display_seq'] = $display_seq;
			$tab_data['display_tab_index'] = 0;
			$tab_data['tab_title'] = '';
			$tab_data['tab_title_img'] = '';
			$tab_data['tab_title_img_on'] = '';
			$tab_data['contents_type'] = $params['contents_type'][0];
			$tab_data['auto_use'] = $params['contents_type'][0] == 'auto' ? 'y' : 'n';
			$tab_data['auto_criteria'] = $tab_data['auto_use'] == 'y' ? $params['auto_criteria'][0] : '';
			$tab_data['tab_contents'] = adjustEditorImages($params['tab_contents'][0]);
			$tab_data['tab_contents_mobile'] = adjustEditorImages($params['tab_contents_mobile'][0]);
			$this->db->insert('fm_design_display_tab', $tab_data);
		}

		$this->db->query("delete from fm_design_display_tab_item where display_seq=?",$display_seq);
		if(isset($params['auto_goods_seqs'])){
			foreach($params['auto_goods_seqs'] as $tab_index=>$auto_goods_seqs){
				$arr_goods_seqs = explode(",",$auto_goods_seqs);
				foreach($arr_goods_seqs as $goods_seq){
					if($goods_seq){
						$data = array(
							"display_seq" => $display_seq,
							"display_tab_index" => $tab_index,
							"goods_seq" => $goods_seq
						);
						$this->db->insert('fm_design_display_tab_item', $data);
					}
				}
			}
		}

		if($params['display_seq']){
			$resultMsg = "상품디스플레이가 변경되었습니다.";
			if($params['direct']){
				$resultCallback = "top.document.location.reload();document.location.href='about:blank';";
			}else{
				$resultCallback = "top.DM_window_display_insert('{$params['template_path']}','{$params['displaykind']}','{$params['platform']}');";
			}
		}else{
			$resultMsg = "상품디스플레이가 저장되었습니다.";
			$resultCallback = "top.DM_window_display_insert('{$params['template_path']}','{$params['displaykind']}','{$params['platform']}');";
		}

		if(!empty($params['popup'])){
			$resultCallback = "parent.window.close();";
		}

		// 해당 디스플레이 캐싱 파일 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach($display_seq);

		openDialogAlert($resultMsg,400,140,'parent',$resultCallback);
	}

	public function display_icon_upload(){

		$config['upload_path'] = ROOTPATH."data/icon/goodsdisplay";
		if($_POST['subPath']) $config['upload_path'] .= "/".$_POST['subPath'];

		if(!is_dir($config['upload_path'])) {
			@mkdir($config['upload_path']);
			@chmod($config['upload_path'],0777);
		}

		$config['allowed_types'] = 'jpg|gif|jpeg|png';
		$config['max_size']	= $this->config_system['uploadLimit'];
		//$config['file_name'] = date('YmdHis').rand(0,9);

		$this->upload->initialize($config);
		if ( ! $this->upload->do_upload('displayImageIconImg') ) {
			$err = $this->upload->display_errors();

		}else{
			$fileInfo = $this->upload->data();
			if($_POST['subPath']){
				$callback = "parent.set_display_image_{$_POST['subPath']}();";
			}else{
				$callback = "parent.set_display_image_icon();";
			}
			openDialogAlert("아이콘이 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	public function display_quick_icon_upload(){

		$config['upload_path'] = ROOTPATH."data/icon/goodsdisplay/quick_shopping";

		if(!is_dir($config['upload_path'])) {
			@mkdir($config['upload_path']);
			@chmod($config['upload_path'],0777);
		}

		$config['allowed_types'] = 'jpg|gif|jpeg|png';
		$config['max_size']	= $this->config_system['uploadLimit'];
		$config['overwrite'] = true;
		//$config['file_name'] = date('YmdHis').rand(0,9);

		foreach($_FILES as $key=>$row){
			if($row['name']){
				$config['file_name'] = str_replace("quick_shopping_icon_","thumb_",$key).".gif";
				$this->upload->initialize($config);
				$this->upload->do_upload($key);
			}
		}

		$quick_shopping = array();
		foreach($_POST['quick_shopping_icon'] as $value){
			$quick_shopping[] = $value;
		}

		if(!$quick_shopping){
			openDialogAlert("이미지를 선택해주세요.",400,140,'parent',$callback);
		}else{
			$quick_shopping_value = "['".implode("','",$quick_shopping)."']";
			$callback = "parent.set_display_quick_icon(\"{$quick_shopping_value}\");";
			echo js($callback);
		}
	}

	/* 상품디스플레이 삭제 */
	public function delete_display(){
		$display_seqs = $_GET['display_seqs'];

		$this->db->query("delete from fm_design_display where find_in_set(display_seq,'{$display_seqs}')");
		$this->db->query("delete from fm_design_display_item where find_in_set(display_seq,'{$display_seqs}')");
		$this->db->query("delete from fm_design_display_tab where find_in_set(display_seq,'{$display_seqs}')");
		$this->db->query("delete from fm_design_display_tab_item where find_in_set(display_seq,'{$display_seqs}')");

		openDialogAlert("상품디스플레이가 삭제되었습니다.",400,140,'parent',"parent.load_display_list();");
	}

	/* 상품디스플레이 복사 */
	public function copy_display(){
		$display_seq = $_GET['display_seq'];

		$this->load->model('goodsdisplay');

		$this->goodsdisplay->copy_display($display_seq);

		openDialogAlert("상품디스플레이가 복사되었습니다.",400,140,'parent',"parent.load_display_list();");

	}

	/* 상단바 디자인 설정 저장 JHR */
	public function topBar_design(){
		$allCategory	= $_POST['allCategory']=='on' ? '1' : '0';
		$category		= $_POST['categoryChk']=='on' ? $_POST['category'] : '0';
		$brand			= $_POST['brand']=='on' ? '1' : '0';
		$location		= $_POST['location']=='on' ? '1' : '0';
		$data = "topBar|".$allCategory."|".$category."|".$brand."|".$location;

		skin_configuration_save($this->designWorkingSkin,"topbar",$data);

		$callback = "parent.parent.location.reload();document.location.href='about:blank';";
		openDialogAlert("상단바 디자인 설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 모바일 메인상단바 탭 만들기 JHR */
	public function mainTopBar_edit(){
		$params = $_POST;
		$tplFilePath = ROOTPATH."data/skin/".$this->designWorkingSkin."/main";
		$tplTabPath = ROOTPATH."data/skin/".$this->designWorkingSkin."/images/topbar/tabs";
		$seq = $params["tab_index"];

		unset($params['tabStyleName']);
		unset($params['tab_cursor_text']);
		unset($params['tab_cursor_img']);
		unset($params['tab_title']);
		unset($params['tab_title_img']);
		unset($params['tab_title_img_on']);
		unset($params['tab_idx']);
		unset($params['tab_img_idx']);
		unset($params['tab_delete']);
		unset($params['tab_filename']);
		unset($params['tab_img_filename']);

		if($params['tab_type'] == "image"){
			$config = array();
			$config['upload_path'] = $tplTabPath;
			$config['max_size']	= $this->config_system['uploadLimit'];
			$config['overwrite'] = true;
			$config['allowed_types'] = 'jpg|gif|jpeg|png';
			if(!is_dir($config['upload_path'])) {
				@mkdir($config['upload_path']);
				@chmod($config['upload_path'],0777);
			}
			if($_FILES['new_tab_img_prev']['tmp_name']){
				$config['file_name'] = "img_arw_prev";
				$this->upload->initialize($config);
				$this->upload->do_upload('new_tab_img_prev');
				$res = $this->upload->data();
				$params['tab_img_prev'] = $res['file_name']?$res['file_name']:$params['tab_img_prev'];
			}
			if($_FILES['new_tab_img_next']['tmp_name']){
				$config['file_name'] = "img_arw_next";
				$this->upload->initialize($config);
				$this->upload->do_upload('new_tab_img_next');
				$res = $this->upload->data();
				$params['tab_img_next'] = $res['file_name']?$res['file_name']:$params['tab_img_next'];
			}
		}

		if(empty($seq)){
			$this->db->insert("fm_topbar_style",$params);
			$seq = $this->db->insert_id();
		}else{
			$this->db->where(array("tab_index"=>$seq));
			$this->db->update("fm_topbar_style",$params);
		}

		$params = $_POST;
		$setPrams = array();
		$i = 0;
		//텍스트와 이미지의 인덱스가 서로 다르다.
		if($params['tab_type'] == "image") $params['tab_idx'] = $params['tab_img_idx'];

		if(!empty($params['tab_title'])){
			foreach($params['tab_title'] as $tab){
				$setPrams["style_index"] = $seq;
				$setPrams["tab_seq"] = $i;
				if($params['tab_type'] == "text"){
					$setPrams["tab_title"] = $tab;
				}else if($params['tab_type'] == "image"){
					$temp = time();
					if($_FILES['new_tab_title_img_on']['tmp_name'][$i]){
						$config['file_name'] = "tab_".$temp.$i."_on";
						$this->upload->initialize($config);
						$this->upload->do_upload('new_tab_title_img_on',$i);
						$res = $this->upload->data();
						$setPrams['tab_title_img_on'] = $res['file_name']?$res['file_name']:$params['tab_title_img'][$i];
					}else{
						$setPrams['tab_title_img_on'] = $params['tab_title_img_on'][$i];
					}
					if($_FILES['new_tab_title_img']['tmp_name'][$i]){
						$config['file_name'] = "tab_".$temp.$i."_off";
						$this->upload->initialize($config);
						$this->upload->do_upload('new_tab_title_img',$i);
						$res = $this->upload->data();
						$setPrams['tab_title_img'] = $res['file_name']?$res['file_name']:$params['tab_title_img'][$i];
					}else{
						$setPrams['tab_title_img'] = $params['tab_title_img'][$i];
					}
				}

				//값 존재 여부
				$query = $this->db->query("select tab_seq from fm_topbar_file where tab_idx=?",$params['tab_idx'][$i]);
				$pprs = $query->row_array();
				$tab_filename = $params['tab_type'] == "text" ? $params["tab_filename"][$i] : $params["tab_img_filename"][$i];

				if(!isset($pprs["tab_seq"])){
					if($tab_filename != "new"){
						$setPrams["tab_filename"] = $tab_filename;
						$this->db->insert("fm_topbar_file",$setPrams);
					}else{
						$temp = time();
						$fileName = "tab_".$temp.$i.".html";
						$setPrams["tab_filename"] = $fileName;
						$this->createTabFile($tplFilePath,$fileName);
						$this->db->insert("fm_topbar_file",$setPrams);
					}
				}else{
					if($pprs["tab_seq"] <= $i){
						$sql = "update fm_topbar_file set tab_seq = tab_seq+1 where tab_seq < '".$pprs['tab_seq']."' and tab_seq >= $i";
					}else{
						$sql = "update fm_topbar_file set tab_seq = tab_seq-1 where tab_seq > '".$pprs['tab_seq']."' and tab_seq <= $i";
					}
					$this->db->query($sql);
					$where["tab_idx"] = $params["tab_idx"][$i];
					$this->db->where($where);

					if($tab_filename != "new"){
						$setPrams["tab_filename"] = $tab_filename;
					}else{
						//파일 생성하여 다시 넣기
						$temp = time();
						$fileName = "tab_".$temp.$i.".html";
						$setPrams["tab_filename"] = $fileName;
						$this->createTabFile($tplFilePath,$fileName);
					}

					$result = $this->db->update("fm_topbar_file",$setPrams);
				}
				$i++;
			}
		}

		//파일삭제도 같이 해준다.
		if(!empty($params["tab_delete"])){
			$query = "select tab_title_img,tab_title_img_on from fm_topbar_file where tab_idx in (".$params["tab_delete"].")";
			$query = $this->db->query($query);
			$pprs = $query->result_array();
			foreach($pprs as $rs){
				foreach($rs as $val){
					$this->deleteFiles($tplTabPath,$val);
				}
			}
			$query = "delete from fm_topbar_file where tab_idx in (".$params["tab_delete"].")";
			$this->db->query($query);
		}

		$callback = "parent.parent.location.reload();";
		openDialogAlert("상단바 디자인 설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 상단바 파일 삭제 부분 2014-07-23 JHR */
	public function deleteFiles($path,$filename){
		$path = $path."/".$filename;
		if(@is_file($path)) @unlink($path);
	}
	/* 상단바 파일 생성 부분 2014-07-23 JHR */
	public function createTabFile($path,$fName){
		$tabFilePath = $path."/".$fName;
		if(!is_dir($path)){
			@mkdir($path);
			@chmod($path,0777);
		}
		if(!file_exists($tabFilePath)){
			$this->load->helper('file');
			write_file($tabFilePath, '');
			@chmod($tabFilePath,0777);
		}
	}

	/* 카테고리 네비게이션 디자인 설정 저장 */
	public function category_navigation_design(){

		skin_configuration_save($this->designWorkingSkin,"category_type",$_POST['category_type']);

		$callback = "parent.parent.location.reload();document.location.href='about:blank';";
		openDialogAlert("카테고리 네비게이션 디자인 설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 브랜드 네비게이션 디자인 설정 저장 */
	public function brand_navigation_design(){

		skin_configuration_save($this->workingSkin,"brand_type",$_POST['brand_type']);

		$callback = "parent.parent.location.reload();document.location.href='about:blank';";
		openDialogAlert("브랜드 네비게이션 디자인 설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 지역 네비게이션 디자인 설정 저장 */
	public function location_navigation_design(){

		skin_configuration_save($this->workingSkin,"location_type",$_POST['location_type']);

		$callback = "parent.parent.location.reload();document.location.href='about:blank';";
		openDialogAlert("지역 네비게이션 디자인 설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	/* 게시판 넣기 */
	public function lastest_insert(){

		$this->load->helper('file');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;
		$location = isset($params['location']) ? $params['location'] : null;

		if(!$params['lastest_code']){
			openDialogAlert("게시판 넣기 실패",400,140,'parent');
			exit;
		}

		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];

		$addSource = $params['lastest_code'];

		/* 소스 불러오기 */
		$source = read_file($template_path);

		if($location == 'top'){
			$source = $addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource;
		}

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}


	/* 게시판 넣기 추가기능 */
	public function lastest_insert_new(){

		$this->load->helper('file');
		$this->load->model('usedmodel');

		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		if( $boardid == 'goods_qna' ) {
			$this->load->model('Goodsqna','wigetGoodsqna');
		}elseif( $boardid == 'goods_review' ) {
			$this->load->model('Goodsreview','wigetGoodsreview');
		}else{
			$this->load->model('Boardmodel','wigetboardmodel');
		}

		$sc['whereis']	= ' and id= "'.$_POST['boardId'].'" ';
		$sc['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
		if(!$this->manager['seq']){
			openDialogAlert("잘못된 게시판입니다.",400,140,'parent');
			exit;
		}

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST; 

		$params['title'] = adjustEditorImages($params['title']);// /data/tmp 임시폴더변경 /data/editor
		$params['boardtitle'] = $this->manager['name'];
		$image_decorations = isset($params['image_decoration']) ? $params['image_decoration'] : array();

		if(isset($image_decorations)){
			$params['image_decorations'] = $image_decorations;
		}

		if( $params['showRecommend'] && $this->manager['auth_recommend_use'] != 'Y' ){
			openDialogAlert("게시글 평가항목을 사용하지 않는 게시판입니다.",400,140,'parent','parent.showrecommendck();');
			exit;
		} 

		$params['count_w']	= isset($params['count_w']) ? $params['count_w'] : 4;
		$params['count_h']	= isset($params['count_h']) ? $params['count_h'] : 1;
		$params['lineCnt']	= ($params['count_w']*$params['count_h']);

		$latestopt = '';
		$today = date("Y-m-d");

		if($params['boardId'] == 'goods_review'){

			if($params['auto_use'] == 'y' ){//자동
				if($params['auto_review_detail']) $latestopt['auto_review_detail']	= 'auto_review_detail='.$params['auto_review_detail'];
 				//if($params['auto_hit_desc']) $latestopt['auto_hit_desc']	= 'orderby=hit desc';
				//if($params['auto_gid_desc']) $latestopt['auto_gid_desc']	= 'orderby=gid asc';

				if($params['auto_desc'] == 'hit' ) {
					$latestopt['auto_hit_desc']	= 'orderby=hit desc';
					$latestopt['auto_gid_desc']	= '';
				}else{
					$latestopt['auto_gid_desc']	= 'orderby=gid asc';
					$latestopt['auto_hit_desc']	= '';
				}

				if($params['auto_order_seq']) $latestopt['isorder_seq']	= 'isorder_seq='.$params['auto_order_seq'];
				if($params['auto_upload']) $latestopt['isimage']		= 'isimage='.$params['auto_upload'];
				if($params['auto_best']) $latestopt['best']				= 'best='.$params['auto_best'];
				if($params['auto_term'] && $params['auto_term_type'] == 'relative' ){
					$latestopt['rdate_s'] = 'rdate_s='.date("Y-m-d",strtotime("-".$params['auto_term']." day ".$today));
					$latestopt['rdate_f']  = 'rdate_f='.$today;
					$latestopt['auto_term']  = 'auto_term='.$params['auto_term'];
				}else{
					$latestopt['rdate_s'] = 'rdate_s='.$params['auto_start_date'];
					$latestopt['rdate_f']  = 'rdate_f='.$params['auto_end_date'];
				}

			}elseif($params['displayBoard']) {
				$latestopt['isseq'] = ($params['displayBoard'])?'displayBoard='.implode(",",$params['displayBoard']):'displayBoard=';
			}
		}else{
			if($params['none_auto_desc'] == 'hit' ) {
				$latestopt['none_auto_hit_desc']	= 'orderby=hit desc';
				$latestopt['none_auto_gid_desc']	= '';
			}else{
				$latestopt['none_auto_gid_desc']	= 'orderby=gid asc';
				$latestopt['none_auto_hit_desc']	= '';
			}

			if($params['auto_upload']) $latestopt['isimage']		= 'isimage='.$params['auto_upload'];
			if($params['none_auto_term'] && $params['none_auto_term_type'] == 'relative' ){
				$latestopt['rdate_s'] = 'rdate_s='.date("Y-m-d",strtotime("-".$params['none_auto_term']." day ".$today));
				$latestopt['rdate_f']  = 'rdate_f='.$today;
				$latestopt['none_auto_term']  = 'none_auto_term='.$params['none_auto_term'];
			}else{
				$latestopt['rdate_s'] = 'rdate_s='.$params['none_auto_start_date'];
				$latestopt['rdate_f']  = 'rdate_f='.$params['none_auto_end_date'];
			}
		}

		$latestopt['image_w'] = 'image_w='.$params['image_w'];
		$latestopt['image_h']  = 'image_h='.$params['image_h'];

		$location = isset($params['location']) ? $params['location'] : 'top';
		$style		= isset($params['style']) ? $params['style'] : 'display_lattice_a';
		$params['width'] = round(100/$params['count_w']).'%';

		if($style == 'display_lattice_a'){
			$params['toptr'] = '<!--{? (.index_+1) == 1}--><tr class="normal_bbslist"><!--{ / }-->';
			$params['bottomtr'] = '<!--{? (.index_+1) != 0 && ((.index_+1)%'.$params['count_w'].')==0 }--></tr><tr  class="normal_bbslist"><!--{ / }-->';
		}elseif($style == 'display_lattice_b'){
			$params['toptr'] = '<!--{ ? .index_ && .index_ % 2 == 0 }--></tr><tr ><td height="10"></td></tr><tr  class="normal_bbslist"><!--{ / }-->';
			$params['bottomtr'] = '<!--{? (.index_+1) != 0 && ((.index_+1)%'.$params['count_w'].')==0 }--></tr><tr><!--{ / }-->';
		}else{//display_list
			$params['toptr'] = '<!--{ ? .index_ }--><tr><td height="10"></td></tr><!--{ / }-->';
		}

		$params['getBoardData'] = '<!--{@ getBoardData(\''.$params['boardId'].'\',\''.$params['lineCnt'].'\',null,null,\''.$params['strcut'].'\',\''.$params['contentcut'].'\',\''. $params['write_show'].'\',array(\''.implode('\',\'',$latestopt).'\')'.') }-->';

		if($params['showNumber'])$params['Number'] = '{=number_format((.index_+1))}';

		if($params['showImg']) {
			$params['imagelay'] = '{? .filelist }<span class="BoardgoodsDisplayImageWrap"  decoration="'.$params['image_decorations'].'" ><a href="{.wigetboardurl_view}" ><img src="{.filelist}" width="'.$params['image_w'].'" height="'.$params['image_h'].'" onerror="this.src=\'/data/skin/{skin}/images/common/noimage.gif\'" /></a></span>{/}';
		}

		$params['subject'] = '{.subject} {.iconnew} {.iconhot} {.iconfile} {.iconhidden}';
		$params['goodsInfo'] = '<!--{? .goodsInfo }--><a href="{.wigetboardurl_view}" target="'.$params['target'].'">{.goodsInfo.goods_name} </a><br/><!--{/}-->';

		if($params['showView'])$params['hitlay'] = '{=number_format(.hit)}';//조회수

		if($params['showRecommend'] && $this->manager['auth_recommend_use'] == 'Y'  ){//게시글 평가수
			$params['recommendlay'] = '{.recommendlay}';
		}

		if($params['showBuyer'])$params['buyertitle'] = '{.buyertitle}';//구매여부
		if($params['showScore'])$params['scorelay'] = '{.scorelay}';//평점
		if($params['showName'])$params['name'] = '{.name}';//작성자
		if($params['showDate'])$params['r_date'] = '{=substr(.r_date,0,10)}';//날짜
		if($params['showContents'])$params['contents'] = '{.contents}';//내용

		$params['end'] = '<!--{/}-->';

		$params['info_settings'] = isset($params['info_setting']) ? $params['info_setting'] : array();


		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];

		$html = '';
		$addSource = '';
		$lastest_key = "designLastestNew".uniqid();
		$this->template->assign(array('recent'=>$params,'lastest_key'=>$lastest_key,'template_path'=>base64_encode($params['template_path'])));
		$thisfile = $this->designWorkingSkin."/_modules/lastest/".$style.".html";
		$this->template->template_dir = BASEPATH."../data/skin";
		$this->template->compile_dir	= BASEPATH."../_compile/data";
		$this->template->define(array('lastest'=>$thisfile));
		$addSource = $this->template->fetch("lastest");

		/* 소스 불러오기 */
		$source = read_file($template_path);

		if($location == 'top'){
			$source = "\n".$addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource."\n";
		}

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}

	/* 상품디스플레이 상품자동노출조건으로 상품번호를 반환 */
	/*
	public function display_auto_goods_seq(){
		$this->load->model('goodsmodel');

		$result = array();

		$params = $_POST;

		if(empty($params['auto_use']))			$params['auto_use'] = 'n';
		if(empty($params['auto_order']))		$params['auto_order'] = 'newly';
		if(empty($params['auto_term_type']))	$params['auto_term_type'] = 'relative';
		if(empty($params['auto_term']))			$params['auto_term'] = 0;
		if(empty($params['auto_start_date']))	$params['auto_start_date'] = '';
		if(empty($params['auto_end_date']))		$params['auto_end_date'] = '';

		$params['auto_goods_status'] = implode("|",$params['auto_goods_status']);

		if(empty($params['auto_file_key_w']))		$params['auto_file_key_w'] = '0';
		if(empty($params['auto_video_use_image']))		$params['auto_video_use_image'] = 'N';
		if(empty($params['auto_videototal']))		$params['auto_videototal'] = '0';

		if( !empty($params['category4'])){
			$params['auto_category_code'] = $params['category4'];
		}else if( !empty($params['category3'])){
			$params['auto_category_code'] = $params['category3'];
		}else if( !empty($params['category2'])){
			$params['auto_category_code'] = $params['category2'];
		}else if( !empty($params['category1'])){
			$params['auto_category_code'] = $params['category1'];
		}else{
			$params['auto_category_code'] = '';
		}

		if( !empty($params['brands4'])){
			$params['auto_brand_code'] = $params['brands4'];
		}else if( !empty($params['brands3'])){
			$params['auto_brand_code'] = $params['brands3'];
		}else if( !empty($params['brands2'])){
			$params['auto_brand_code'] = $params['brands2'];
		}else if( !empty($params['brands1'])){
			$params['auto_brand_code'] = $params['brands1'];
		}else{
			$params['auto_brand_code'] = '';
		}

		$sc=array();

		// 상품자동노출
		//if($params['auto_use']=='y'){
		if(true){
			$sc['auto_display'] = array(
				'auto_use' => 'y',
				'auto_order' => $params['auto_order'],
				'auto_category_code' => $params['auto_category_code'],
				'auto_brand_code' => $params['auto_brand_code'],
				'auto_goods_status' => $params['auto_goods_status'],
				'auto_term_type' => $params['auto_term_type'],
				'auto_term' => $params['auto_term'],
				'auto_start_date' => $params['auto_start_date'],
				'auto_end_date' => $params['auto_end_date'],
				'goods_video_type' => $params['goods_video_type'],
				'auto_file_key_w' => $params['auto_file_key_w'],
				'auto_video_use_image' => $params['auto_video_use_image'],
				'auto_videototal' => $params['auto_videototal'],
				'auto_video_use_contents' => $params['auto_video_use_contents'],
			);

			// 20130408 : 자동노출&페이징정렬시 문제발생하여 임시로 추가
			$sc['sort']			= $params['auto_order'];
			$sc['page']			= '1';
			$sc['perpage']		= $params['count_w']*$params['count_h'];
			$sc['image_size']	= $params['image_size'];
			$sc['limit']		= $params['count_w']*$params['count_h'];

			$list = $this->goodsmodel->goods_list($sc);
			foreach($list['record'] as $k => $data) {
				$result[] = array(
					'goods_seq'	=> $data['goods_seq'],
					'goods_name'=> $data['goods_name'],
					'price'=> $data['price'],
					'image'=> $data['image'],
				);
			}

		}

		echo json_encode($result);
	}
	*/

	public function mobile_quick_design(){
		$this->load->model('designmodel');

		$skin = $_POST['skin'];
		$kind = $_POST['kind'];
		$mode = $_POST['mode'];

		if($skin != $this->designWorkingSkin){
			openDialogAlert("적용하려는 스킨이 디자인작업용스킨으로 설정되어있지 않습니다.",400,140,'parent');
			exit;
		}

		if($kind=='theme'){
			$theme = $_POST['theme'];
			$oriCssPath = ROOTPATH.'admin/skin/default/css/mobile_theme/'.$theme.'.css';
			if($mode=='save') $cssPath = $this->designmodel->get_mobile_buttons_css_path($this->designWorkingSkin);
			else exit;

			$contents = file_get_contents($oriCssPath);
			$contents = "/* \r\n	이 CSS파일은 아이디자인에 의해 강제로 변형되므로 임의수정시 주의하시기 바랍니다. \r\n	원본소스 : /admin/skin/default/css/quick_design_original.css\r\n*/\r\n\r\n".$contents;
		}else{
			$oriCssPath = ROOTPATH.'admin/skin/default/css/quick_design_original.css';
			if($mode=='save') $cssPath = $this->designmodel->get_mobile_buttons_css_path($this->designWorkingSkin);
			else if($mode=='view') $cssPath = '/data/tmp/quick_design_'.time().'.css';
			else exit;

			$contents = file_get_contents($oriCssPath);
			$contents = "/* \r\n	이 CSS파일은 아이디자인에 의해 강제로 변형되므로 임의수정시 주의하시기 바랍니다. \r\n	원본소스 : /admin/skin/default/css/quick_design_original.css\r\n*/\r\n\r\n".$contents;

			//치환
			$contents = str_replace('{color_header_t1}',$_POST['color_header_t1'],$contents);
			$contents = str_replace('{color_header_b1}',$_POST['color_header_b1'],$contents);

			$contents = str_replace('{color_subtitle_t1}',$_POST['color_subtitle_t1'],$contents);
			$contents = str_replace('{color_subtitle_b1}',$_POST['color_subtitle_b1'],$contents);
			$contents = str_replace('{color_subtitle_b2}',$_POST['color_subtitle_b2'],$contents);

			$contents = str_replace('{color_btnimportant_t1}',$_POST['color_btnimportant_t1'],$contents);
			$contents = str_replace('{color_btnimportant_b1}',$_POST['color_btnimportant_b1'],$contents);
			$contents = str_replace('{color_btnimportant_b2}',$_POST['color_btnimportant_b2'],$contents);

			$contents = str_replace('{color_btnnormal_t1}',$_POST['color_btnnormal_t1'],$contents);
			$contents = str_replace('{color_btnnormal_b1}',$_POST['color_btnnormal_b1'],$contents);
			$contents = str_replace('{color_btnnormal_b2}',$_POST['color_btnnormal_b2'],$contents);

			$contents = str_replace('{color_btncancel_t1}',$_POST['color_btncancel_t1'],$contents);
			$contents = str_replace('{color_btncancel_b1}',$_POST['color_btncancel_b1'],$contents);
			$contents = str_replace('{color_btncancel_b2}',$_POST['color_btncancel_b2'],$contents);

			$contents = str_replace('{color_btnblack_t1}',$_POST['color_btnblack_t1'],$contents);
			$contents = str_replace('{color_btnblack_b1}',$_POST['color_btnblack_b1'],$contents);
			$contents = str_replace('{color_btnblack_b2}',$_POST['color_btnblack_b2'],$contents);

			$contents = str_replace('{color_btn_t1}',$_POST['color_btn_t1'],$contents);
			$contents = str_replace('{color_btn_b1}',$_POST['color_btn_b1'],$contents);
			$contents = str_replace('{color_btn_b2}',$_POST['color_btn_b2'],$contents);
		}

		@file_put_contents(ROOTPATH.$cssPath,$contents);
		@chmod(ROOTPATH.$cssPath,0777);

		if($mode=='save'){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.document.location.href('/admin/design/mobile_quick_design?tpl_path=".urlencode($_GET['tpl_path'])."&kind=direct');document.location.href='about:blank';");
		}
		if($mode=='view'){
			echo "<script>parent.$('#quick_design_css').attr('href','{$cssPath}')</script>";
		}

	}

	public function pc_quick_design_image_upload(){

		$buttonFileName = explode('?',basename($_POST['src']));
		$buttonFileName = $buttonFileName[0];

		if($_POST['key']=='icon'){
			$upload_path = "data/icon/goods_status";
		}else{
			$upload_path = "data/skin/".$this->designWorkingSkin."/images/buttons";
		}

		$config['upload_path']	= ROOTPATH.$upload_path;
		$config['allowed_types'] = 'gif|png|jpg';
		$config['overwrite'] = true;
		$config['max_size']	= $this->config_system['uploadLimit'];
		$config['file_name'] = $buttonFileName;
		$this->load->library('upload');
		$this->upload->initialize($config);

		if ($this->upload->do_upload('image')) {
			@chmod($config['upload_path'].'/'.$config['file_name'], 0777);
			$callback = "parent.closeDialog('popImageChoice');parent.$(\"img.imageBtn[code='{$_POST['code']}']\").attr('src','".dirname($_POST['src']).'/'.$buttonFileName.'?'.time()."');";
			openDialogAlert("이미지가 변경 되었습니다.",400,140,'parent',$callback);
		}else{
			$err = $this->upload->display_errors();
			if($err){
				openDialogAlert(is_array($err)?implode('<br />',$err):$err,400,170,'parent','');
			}
		}
	}

	/* 우측 추천상품 생성,수정 화면 */
	public function recomm_goods_edit(){
		$params = $_POST;

		$this->db->query("delete from fm_design_recommend_item");
		if(isset($params['auto_goods_seqs'])){
			foreach($params['auto_goods_seqs'] as $key=>$auto_goods_seqs){
				if ($auto_goods_seqs) {
					$arr_goods_seqs = explode(",",$auto_goods_seqs);
					$temp_data = array();
					foreach($arr_goods_seqs as $goods_seq){
						$data = array(
							"goods_seq" => $goods_seq
						);
						$temp_data[] = $data;
					}
					$this->db->insert_batch('fm_design_recommend_item', $temp_data); 
				}
			}
		}

		$resultMsg = "추천상품이 저장되었습니다.";
		$resultCallback = "parent.parent.document.location.reload();document.location.href='about:blank';";

		if(!empty($params['popup'])){
			$resultCallback = "parent.window.close();";
		}

		openDialogAlert($resultMsg,400,140,'parent',$resultCallback);
	}

	/* 배너 넣기 */
	public function banner_insert(){

		$this->load->helper('file');
		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			openDialogAlert($res['msg'],600,370,'parent');
			exit;
		}

		$params = $_POST;

		if(!$params['banner_seq']){
			openDialogAlert("적용할 배너를 선택해주세요.",400,140,'parent');
			exit;
		}

		$template_path			= ROOTPATH."data/skin/".$this->designWorkingSkin."/".$params['template_path'];

		$addSource = "{=showDesignBanner({$params['banner_seq']})}";

		/* 소스 불러오기 */
		$source = read_file($template_path);

		if($params['location'] == 'top'){
			$source = $addSource . "\n" . $source;
		}else{
			$source = $source . "\n" . $addSource;
		}

		if(!write_file($template_path,$source)){
			openDialogAlert("적용에 실패했습니다.",400,140,'parent');
			exit;
		}

		if(preg_match("/^layout_/",$params['template_path']) || preg_match("/^board/",$params['template_path']) || preg_match("/^goods/",$params['template_path'])){
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.reload();document.location.href='about:blank';");
		}else{
			$locationUrl = $this->layout->get_tpl_path_url($this->designWorkingSkin,$params['template_path']);
			openDialogAlert("적용되었습니다.",400,140,'parent',"parent.parent.document.location.href='{$locationUrl}';");
		}

	}

	/* 배너 디렉토리 체크 */
	public function banner_directory_check($banner_seq){
		
		// 디렉토리 생성
		if(!is_dir(ROOTPATH."data/skin/{$this->designWorkingSkin}/images/banner")){
			if(!mkdir(ROOTPATH."data/skin/{$this->designWorkingSkin}/images/banner")){
				openDialogAlert("/data/skin/{$this->designWorkingSkin}/images/banner 디렉토리 생성 실패",400,140,'parent');
				exit;
			}
			@chmod(ROOTPATH."data/skin/{$this->designWorkingSkin}/images/banner",0777);
		}

		if($banner_seq){
			if(!is_dir(ROOTPATH."data/skin/{$this->designWorkingSkin}/images/banner/{$banner_seq}")){
				if(!mkdir(ROOTPATH."data/skin/{$this->designWorkingSkin}/images/banner/{$banner_seq}")){
					openDialogAlert("/data/skin/{$this->designWorkingSkin}/images/banner/{$banner_seq} 디렉토리 생성 실패",400,140,'parent');
					exit;
				}
				@chmod(ROOTPATH."data/skin/{$this->designWorkingSkin}/images/banner/{$banner_seq}",0777);
			}
		}
	}

	/* 배너 생성,변경 처리 */
	public function banner_edit(){
		$this->load->model('designmodel');

		$params = $_POST;
		$banner_seq = $params['banner_seq'];
		if(!$banner_seq) $banner_seq = $this->designmodel->get_new_banner_seq($this->designWorkingSkin);

		if($params['skin']!=$this->designWorkingSkin){
			openDialogAlert("작업중이던 스킨과 현재 디자인작업용으로 설정된 스킨이 다릅니다.",400,140,'parent');
			exit;
		}

		$this->banner_directory_check($banner_seq);

		/* 영역 제거 */
		if(isset($params['removeDesignBannerArea']) && $params['removeDesignBannerArea']=='Y'){

			$this->load->model('usedmodel');

			$res = $this->usedmodel->used_limit_check();
			if(!$res['type']){
				openDialogAlert($res['msg'],600,370,'parent');
				exit;
			}

			$this->load->helper('file');
			$tpl_path = ROOTPATH.'data/skin/'.$this->designWorkingSkin.'/'.$params['template_path'];

			/* 소스 불러오기 */
			$source = read_file($tpl_path);

			$removeSource = "/\{(\s*)=(\s*)showDesignBanner(\s*)\((\s*){$banner_seq}(\s*)\)(\s*)\}/";

			$source = preg_replace($removeSource,"",$source);

			if(!write_file($tpl_path,$source)){
				openDialogAlert("소스 변경 실패",400,140,'parent');
				exit;
			}
		}

		$this->validation->set_rules('name', '배너명','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('image[]', '이미지','trim|required|max_length[255]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		// 배경이미지 저장
		if(preg_match("/^data\/tmp/i",$params['background_image']) || preg_match("/\/admin\//i",$params['background_image'])){
			$ext = explode(".",$params['background_image']);
			$ext = $ext[count($ext)-1];
			$save_path = "images/banner/{$banner_seq}/background_image.{$ext}";
			$new_path = "data/skin/{$this->designWorkingSkin}/{$save_path}";
			copy(ROOTPATH.$params['background_image'],ROOTPATH.$new_path);
			chmod(ROOTPATH.$new_path,0777);
			$params['background_image'] = $save_path;
		}

		if($params['banner_seq']){
			$saveParams = $params;
			$saveParams['modtime'] = time();
			$data = filter_keys($saveParams, $this->db->list_fields('fm_design_banner'));
			$this->db->update('fm_design_banner', $data, array("skin"=>$this->designWorkingSkin,"banner_seq"=>$banner_seq));
		}else{
			$saveParams = $params;
			$saveParams['banner_seq'] = $banner_seq;
			$saveParams['skin'] = $this->designWorkingSkin;
			$saveParams['regdate'] = date('Y-m-d H:i:s');
			$saveParams['modtime'] = time();
			$data = filter_keys($saveParams, $this->db->list_fields('fm_design_banner'));
			$this->db->insert('fm_design_banner', $data);
		}

		$this->banner_directory_check($banner_seq);

		$this->db->delete('fm_design_banner_item', array("skin"=>$this->designWorkingSkin,"banner_seq"=>$banner_seq));
		foreach($params['image'] as $k=>$image){
			$data = array();
			$data['banner_seq']	= $banner_seq;
			$data['skin']		= $this->designWorkingSkin;
			$data['link']		= $params['link'][$k];
			$data['target']		= $params['target'][$k];
			$data['image']		= $params['image'][$k];
			$data['tab_image_inactive']		= basename($params['navigation_image_inactive'][$k]);
			$data['tab_image_active']		= basename($params['navigation_image_active'][$k]);

			if(preg_match("/^data\/tmp/i",$data['image']) || preg_match("/^\/admin\//i",$data['image'])){
				$ext = explode(".",$data['image']);
				$ext = $ext[count($ext)-1];
				$save_path = "images/banner/{$banner_seq}/images_".($k+1).".{$ext}";
				$new_path = "data/skin/{$this->designWorkingSkin}/{$save_path}";
				copy(ROOTPATH.$data['image'],ROOTPATH.$new_path);
				chmod(ROOTPATH.$new_path,0777);
				$data['image'] = $save_path;
			}

			// 페이징이 커스텀일때 이미지업로드
			if($params['navigation_paging_style']=='custom'){
				if(preg_match("/^data\/tmp/i",$params['navigation_image_inactive'][$k]) || preg_match("/^\/admin\//i",$params['navigation_image_inactive'][$k])){
					$ext = explode(".",$params['navigation_image_inactive'][$k]);
					$ext = $ext[count($ext)-1];
					$save_path = "images/banner/{$banner_seq}/tab".($k+1).".{$ext}";
					$new_path = "data/skin/{$this->designWorkingSkin}/{$save_path}";
					copy(ROOTPATH.$params['navigation_image_inactive'][$k],ROOTPATH.$new_path);
					chmod(ROOTPATH.$new_path,0777);
					$data['tab_image_inactive'] = basename($save_path);
				}
				if(preg_match("/^data\/tmp/i",$params['navigation_image_active'][$k]) || preg_match("/^\/admin\//i",$params['navigation_image_active'][$k])){
					$ext = explode(".",$params['navigation_image_active'][$k]);
					$ext = $ext[count($ext)-1];
					$save_path = "images/banner/{$banner_seq}/tab".($k+1)."_on.{$ext}";
					$new_path = "data/skin/{$this->designWorkingSkin}/{$save_path}";
					copy(ROOTPATH.$params['navigation_image_active'][$k],ROOTPATH.$new_path);
					chmod(ROOTPATH.$new_path,0777);
					$data['tab_image_active'] = basename($save_path);
				}
			}

			$this->db->insert('fm_design_banner_item', $data);
		}

		if($params['banner_seq']){
			$resultMsg = "배너가 변경되었습니다.";
			if($params['direct']){
				$resultCallback = "top.document.location.reload();document.location.href='about:blank';";
			}else{
				$resultCallback = "top.DM_window_banner_insert('{$params['template_path']}');";
				$resultCallback = "parent.document.location.reload();document.location.href='about:blank';";
			}
		}else{
			$resultMsg = "배너가 저장되었습니다.";
			$resultCallback = "top.DM_window_banner_insert('{$params['template_path']}');";
		}

		if(!empty($params['popup'])){
			$resultCallback = "parent.window.close();";
		}
		
		openDialogAlert($resultMsg,400,140,'parent',$resultCallback);
	}

	/* 배너 삭제 */
	public function delete_banner(){
		$this->load->helper('file');

		$banner_seqs = explode(",",$_GET['banner_seqs']);

		foreach($banner_seqs as $banner_seq){

			if(!$banner_seq) continue;

			$query = $this->db->query("select * from fm_design_banner where skin=? and banner_seq=?",array($this->designWorkingSkin,$banner_seq));
			$banner_data = $query->row_array();
			if(!$banner_data) continue;

			if($banner_data['skin']){
				$banner_image_path = ROOTPATH."data/skin/{$banner_data['skin']}/images/banner/{$banner_seq}";

				if(is_dir($banner_image_path)){
					if(delete_files($banner_image_path,true)){
						@rmdir($banner_image_path);
					}
				}
			}

			$this->db->query("delete from fm_design_banner where skin=? and banner_seq=?",array($this->designWorkingSkin,$banner_seq));
			$this->db->query("delete from fm_design_banner_item where skin=? and banner_seq=?",array($this->designWorkingSkin,$banner_seq));
		}

		
		openDialogAlert("배너가 삭제되었습니다.",400,140,'parent',"parent.document.location.reload();document.location.href='about:blank';");
	}
}



/* End of file design_process.php */
/* Location: ./app/controllers/admin/design_process.php */