<?php
/**
 * @author lgs
 * @version 1.0.0
 * @license copyright by GABIA_lgs
 * @since 12. 2. 1 10:10 ~
 */

// 설정로드
function layout_config_load($skin,$tpl_path=null) {
	$CI =& get_instance();
	if($tpl_path!=null){
		$query = "SELECT `tpl_folder`,`tpl_path`,`tpl_desc`,`value`,`tpl_page` FROM `fm_config_layout` WHERE `skin`=? and `tpl_path`=? order by regist_date,tpl_path";
		$query = $CI->db->query($query,array($skin,$tpl_path));
	}else{
		$query = "SELECT `tpl_folder`,`tpl_path`,`tpl_desc`,`value`,`tpl_page` FROM `fm_config_layout` WHERE `skin`=? order by regist_date,tpl_path";
		$query = $CI->db->query($query,$skin);
	}

	$returnArr = array();

	foreach ($query->result_array() as $row){

		if(preg_match('/a:/',$row['value'])) $row['value'] = unserialize(strip_slashes($row['value']));

		$returnArr[$row['tpl_path']]['tpl_folder'] = $row['tpl_folder'];
		$returnArr[$row['tpl_path']]['tpl_path'] = $row['tpl_path'];
		$returnArr[$row['tpl_path']]['tpl_desc'] = $row['tpl_desc'];
		$returnArr[$row['tpl_path']]['tpl_page'] = $row['tpl_page'];

		if(is_array($row['value'])){
			foreach($row['value'] as $k=>$v){
				$returnArr[$row['tpl_path']][$k] = $v;				
			}
		}

		if($CI->fammerceMode || $CI->storefammerceMode){
			$returnArr[$row['tpl_path']]['width'] = 790;
			$returnArr[$row['tpl_path']]['body_width'] = 790;
		}
		
		if(!empty($returnArr[$row['tpl_path']]['backgroundImage'])){
			$imagePath = "data/skin/".$skin."/images/design";
			$returnArr[$row['tpl_path']]['backgroundImage'] = "/".$imagePath."/".basename($returnArr[$row['tpl_path']]['backgroundImage']);
		}
		
		if(!empty($returnArr[$row['tpl_path']]['bodyBackgroundImage'])){
			$imagePath = "data/skin/".$skin."/images/design";
			$returnArr[$row['tpl_path']]['bodyBackgroundImage'] = "/".$imagePath."/".basename($returnArr[$row['tpl_path']]['bodyBackgroundImage']);
		}

	}

	if(!$returnArr && $tpl_path){
		$returnArr[$tpl_path]['tpl_path'] = $tpl_path;
	}

	if(isset($returnArr)) return $returnArr;
}

// 설정자동로드
function layout_config_autoload($skin,$tpl_path){
	$arrLayoutBasic = layout_config_load($skin,'basic');
	$arrLayout = layout_config_load($skin,$tpl_path);

	if($arrLayout[$tpl_path]['backgroundImage']) unset($arrLayoutBasic['basic']['backgroundColor']);
	if($arrLayout[$tpl_path]['backgroundColor']) unset($arrLayoutBasic['basic']['backgroundImage']);
	if($arrLayout[$tpl_path]['bodyBackgroundImage']) unset($arrLayoutBasic['basic']['bodyBackgroundColor']);
	if($arrLayout[$tpl_path]['bodyBackgroundColor']) unset($arrLayoutBasic['basic']['bodyBackgroundImage']);

	foreach($arrLayoutBasic['basic'] as $key=>$value){
		if(!in_array($key,array('tpl_folder','tpl_path','tpl_desc','tpl_page'))){
			if(!isset($arrLayout[$tpl_path][$key]) || !$arrLayout[$tpl_path][$key]){
				$arrLayout[$tpl_path][$key] = $arrLayoutBasic['basic'][$key];
			}
		}
	}
	return $arrLayout;
}

// 폴더별 설정 로드
function layout_config_folder_load($skin,$tpl_folder){
	$CI =& get_instance();
	
	$returnArr = array();
	
	$map = directory_map_list(directory_map(ROOTPATH."data/skin/".$skin."/".$tpl_folder,false,false));
	
	foreach($map as $item){
		$tpl_path = $tpl_folder.$item;
		$returnArr[$tpl_path]['tpl_folder'] = $tpl_folder;
		$returnArr[$tpl_path]['tpl_path'] = $tpl_path;
	}
	
	
	$query = "SELECT `tpl_folder`,`tpl_path`,`tpl_desc`,`value`,`tpl_page` FROM `fm_config_layout` WHERE `skin`=? and `tpl_folder`=? order by regist_date,tpl_path";
	$query = $CI->db->query($query,array($skin,$tpl_folder));
	foreach ($query->result_array() as $row){

		if(preg_match('/a:/',$row['value'])) $row['value'] = unserialize(strip_slashes($row['value']));

		$returnArr[$row['tpl_path']]['tpl_folder'] = $row['tpl_folder'];
		$returnArr[$row['tpl_path']]['tpl_path'] = $row['tpl_path'];
		$returnArr[$row['tpl_path']]['tpl_desc'] = $row['tpl_desc'];
		$returnArr[$row['tpl_path']]['tpl_page'] = $row['tpl_page'];

		if(is_array($row['value'])){
			foreach($row['value'] as $k=>$v){
				$returnArr[$row['tpl_path']][$k] = $v;				
			}
		}

	}

	/*
	if(!$returnArr && $tpl_path){
		$returnArr[$tpl_path]['tpl_path'] = $tpl_path;
	}
	*/
	
	if(isset($returnArr)) return $returnArr;
}

// 설정저장
function layout_config_save($skin,$tpl_path='basic',$ar_data){
	$CI =& get_instance();
	$tmpTime = time();
	
	$rowData = array();
	$rowData['value'] = array();
	
	foreach($ar_data as $key=>$value){
		if(in_array($key,array('tpl_folder','tpl_path','tpl_desc','tpl_page','regist_date'))){
			$rowData[$key] = $value;
		}else{
			$rowData['value'][$key] = $value;
		}
	}

	if( is_array($rowData['value']) ) $rowData['value'] = serialize($rowData['value']);

	if( !isset($rowData['tpl_folder']) )
	{
		$tmp = explode('/',$tpl_path);
		$rowData['tpl_folder'] = $tmp[0];
	}
	
	if( !isset($ar_data['tpl_page']) )
	{
		$rowData['tpl_page'] = '0';
	}
	
	$rowData['tpl_page'] = (string)$rowData['tpl_page'];

//	$rowData['value'] = addslashes($rowData['value']);
	$date = date('Y-m-d H:i:s',$tmpTime);

	$query = $CI->db->query("select * from `fm_config_layout` where skin=? and tpl_path=?",array($skin,$tpl_path));
	$data = $query->row_array();
	if($data){
		$query = "update `fm_config_layout` set `skin`=?,`tpl_path`=?,`tpl_folder`=?,`tpl_desc`=?,`value`=?,`regist_date`=? where skin=? and tpl_path=?";
		$CI->db->query($query,array($skin,$tpl_path,$rowData['tpl_folder'],$rowData['tpl_desc'],$rowData['value'],$date,$skin,$tpl_path));

	}else{
		$query = "insert into `fm_config_layout` set `skin`=?,`tpl_path`=?,`tpl_folder`=?,`tpl_desc`=?,`value`=?,`tpl_page`=?,`regist_date`=?";
		$CI->db->query($query,array($skin,$tpl_path,$rowData['tpl_folder'],$rowData['tpl_desc'],$rowData['value'],$rowData['tpl_page'],$date));
	}

	// 파일이 없을경우 생성
	$skinPath = APPPATH."../data/skin/";
	$tplFilePath = $skinPath.$skin."/".$tpl_path;
	if(!file_exists($tplFilePath)){
		$CI->load->helper('file');
		write_file($tplFilePath, null);
		@chmod($tplFilePath,0777);
	}
	
	$tmpTime++;
}

// 설정초기화
function layout_config_delete($skin){
	$CI =& get_instance();
	$query = "DELETE FROM `fm_config_layout` WHERE `skin`=?";
	$query = $CI->db->query($query,array($type));
}

// 파일경로 목록 반환
// directory_map_list(directory_map($skin_path,false,false));
function directory_map_list($array=array(),$path=''){
	$result = array();
	if(is_array($array)){
		foreach($array as $k=>$v){
			if(is_array($v)){
				$childPath = $path.'/'.$k;
				$result[] = $childPath;
			}else{
				$childPath = $path;
			}
			$result = array_merge($result,directory_map_list($v,$childPath));
		}
		return $result;
	}else{
		return array($path.'/'.$array);
	}
}

// 스킨디렉토리의 config 가져오기,저장하기
function skin_configuration($skin){
	$skinPath = APPPATH."../data/skin/";
	$configurationPath = $skinPath.$skin."/configuration/skin.ini";
	
	if(file_exists($configurationPath)){
		$configuration = parse_ini_file($configurationPath);
		$configuration['skin'] = $skin;
		$configuration['regdate'] = date('Y-m-d H:i',filemtime($configurationPath));
		return $configuration;
	} else return array();
}

function skin_configuration_save($skin,$key,$value){
	$skinPath = APPPATH."../data/skin/";
	$configurationPath = $skinPath.$skin."/configuration/skin.ini";

	$skin_configuration = skin_configuration($skin);
	$skin_configuration[$key] = $value;

	return set_ini_file($configurationPath,array('information'=>$skin_configuration),true);
}

/* 스킨파일 자동 백업 */
function backup_skin_file($skin,$tpl_path){
	
	$CI =& get_instance();
	
	$CI->load->helper('directory');
	$CI->load->helper('file');
	
	$tpl_realpath = "data/skin/".$skin."/".$tpl_path;
	$tpl_fileName = basename($tpl_realpath);
	
	$skinBackupPath = "data/skin_backup/".$skin."/".$tpl_path.date('.YmdHis');
	$skinBackupFileName = basename($skinBackupPath);
	$skinBackupDir = dirname($skinBackupPath);

	/* 백업파일생성 */
	make_dir($skinBackupPath,ROOTPATH);
	$result = copy(ROOTPATH.$tpl_realpath,ROOTPATH.$skinBackupPath);

	/* 백업파일 개수 초과 삭제 */
	$tpl_fileNameForReg = str_replace('.','\.',$tpl_fileName);
	$backupMax = 5;
	$backupCount = 0;
	$map = directory_map(ROOTPATH.$skinBackupDir,true);
	rsort($map);
	foreach($map as $k=>$v){
		if(is_file(ROOTPATH.$skinBackupDir.'/'.$v) && preg_match("/{$tpl_fileNameForReg}\.[0-9]{14}/",$v)){
			$backupCount++;
			if($backupCount>$backupMax){
				@unlink(ROOTPATH.$skinBackupDir.'/'.$v);
			}
		}
	}
	
	return $result ? true : false;
}

/* 파일 자동 백업 */
function backup_file($tpl_path){
	
	$CI =& get_instance();
	
	$CI->load->helper('directory');
	$CI->load->helper('file');
	
	$tpl_realpath = $tpl_path;
	$tpl_fileName = basename($tpl_realpath);
	
	$backupPath = "data/file_backup/".$tpl_path.date('.YmdHis');
	$backupDir = dirname($backupPath);

	/* 백업파일생성 */
	make_dir($backupPath,ROOTPATH);
	$result = copy(ROOTPATH.$tpl_realpath,ROOTPATH.$backupPath);

	/* 백업파일 개수 초과 삭제 */
	$tpl_fileNameForReg = str_replace('.','\.',$tpl_fileName);
	$backupMax = 5;
	$backupCount = 0;
	$map = directory_map(ROOTPATH.$backupDir,true);
	rsort($map);
	foreach($map as $k=>$v){
		if(is_file(ROOTPATH.$backupDir.'/'.$v) && preg_match("/{$tpl_fileNameForReg}\.[0-9]{14}/",$v)){
			$backupCount++;
			if($backupCount>$backupMax){
				@unlink(ROOTPATH.$backupDir.'/'.$v);
			}
		}
	}
	
	return $result ? true : false;
}

// END
/* End of file design_helper.php */
/* Location: ./app/helpers/design_helper.php */