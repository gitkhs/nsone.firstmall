<?php

/* 플래시 출력*/
function showDesignFlash($flash_seq,$return=false)
{
	//error_reporting(E_ALL);

	$CI =& get_instance();
	$CI->load->helper('javascript');
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

	$flash_key = "designFlash{$flash_seq}";

	$query  = $CI->db->query("select * from fm_design_flash where flash_seq = ?",$flash_seq);
	$data = $query->row_array();

	if(!$data) return;

	$CI->load->helper('file');
	$CI->load->helper('readurl');
	$CI->load->library('SofeeXmlParser');
	
	$xml_url = $menuOn = $menuOff = $swf_url = '';
	$js_sources = array();

	
	$query = $CI->db->query("select * from fm_design_flash_file where flash_seq=? order by url asc",$flash_seq);
	foreach ($query->result_array() as $data_file){
			
		$data_file['url'] = str_replace('/data/flash/xml/','/data/skin/'.$CI->skin.'/images/flash/',$data_file['url']);
		
		if( preg_match('/data.xml/',$data_file['url']) ){
			$xml_url = $data_file['url'];
		} else if($data_file['type']=='js' &&  preg_match('/\.js/',$data_file['url']) ){
			$js_sources[] = array(
				'url' => $data_file['url'],
				'source' => iconv('euc-kr','utf-8',read_file(ROOTPATH.$data_file['url']))
			);
		} else if($data_file['type']=='js' && preg_match('/On\.png/',$data_file['url']) ){
			$menuOn = $data_file['url'];
		} else if( preg_match('/\Off.png/',$data_file['url']) ){
			$menuOff = $data_file['url'];
		} else if($data_file['type']=='swf' && preg_match('/\.swf/',$data_file['url'])){
			$swf_url = $data_file['url'];
		}
		
	}

	
	
	$xmlParser = new SofeeXmlParser();

	if(!file_exists(ROOTPATH.$xml_url)){
		echo "XML 파일이 존재하지 않습니다.";
		exit;
	}
	
	$xmlParser->parseFile("http://".$_SERVER['HTTP_HOST'].$xml_url);
	$tree = $xmlParser->getTree();
	
	
	$option = $tree['data']['option'] ? $tree['data']['option'] : $tree['data'];	
	list($icon,$action,$effect) = explode('_',$option['style']['value']);	
	list($icon0,$icon1) = explode('|',$icon);
	
	$icon_gab = 0;
	if($icon == 'I|V'){
		$icon_gab = 30; 
	}

	if(!is_numeric(array_pop(array_keys($tree['data']['item'])))){
		$tree['data']['item'] = array($tree['data']['item']);
	}

	$tree_items = array();
	$tree_items_size = array();
	
	foreach($tree['data']['item'] as $k => $v){
		$size = array();
		$size[0] = $size[1] = 0;	
		if($icon0=='I' && !$v['menuOn']['value']){
			$v['menuOn']['value'] = $menuOn;
			$v['menuOff']['value'] = $menuOff;
		}	
		
		$v['visual']['value'] = str_replace('/data/flash/xml/','/data/skin/'.$CI->skin.'/images/flash/',$v['visual']['value']);
		$v['menuOff']['value'] = str_replace('/data/flash/xml/','/data/skin/'.$CI->skin.'/images/flash/',$v['menuOff']['value']);
		$v['menuOn']['value'] = str_replace('/data/flash/xml/','/data/skin/'.$CI->skin.'/images/flash/',$v['menuOn']['value']);
		
		if(!empty($v['menuOn'])){
			$file = ROOTPATH.$v['menuOn']['value'];
			if(is_file($file)){		
				$size = getimagesize($file);
			}
		}
		
		$tree_items[$k] = $v;
		$tree_items_size[$k] = $size;
	}
		


	$flag = true;	

	if($CI->input->cookie($flash_key)) $flag = false; //창숨김처리 쿠키 체크

	if($CI->layout->is_design_mode() && $CI->input->cookie('designEditMode')) {
		$flag = true; //디자인편집모드일땐 무조건 팝업 보여주기
	}

	if($flag){
		$html = "";

		$html .= "<div class='designFlash' designElement='flash' templatePath='{$template_path}' flashSeq='{$flash_seq}' style='width:{$data['width']}px;height:{$data['height']}px;'>";
		$html .= "<div class='flash_magic_scriptbanner' id = 'divBtnTypeBanner{$flash_seq}' style = 'margin:0px; float:left'>
					<script>
					flash('{$swf_url}?xmlpath={$xml_url}','{$option[flashW][value]}','{$option[flashH][value]}','','transparent');
					</script>
					</div>";
		$html .= "</div>";

		if($return) return $html;
		else echo $html;
	}

	return;

}
?>