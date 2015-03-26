<?php

/* 스크립트배너 출력*/
function showDesignBanner($banner_seq,$return=false)
{
	$CI =& get_instance();
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

	$CI->load->model('layout');
	if($return){
		$skin = $CI->designWorkingSkin;
	}else{
		$skin = $CI->layout->get_view_skin();
	}

	$query  = $CI->db->query("select * from fm_design_banner where skin=? and banner_seq = ?",array($skin,$banner_seq));
	$banner = $query->row_array();

	$query  = $CI->db->query("select * from fm_design_banner_item where skin=? and banner_seq = ?",array($skin,$banner_seq));
	$banner_item = $query->result_array();

	if(!$banner) return;

	$html = "";

	if(BANNER_SCRIPT_LOADED!==true){
		// 한페이지에 여러 배너 노출할 때 스크립트는 1회만 로드
		define("BANNER_SCRIPT_LOADED",true);
		$html .= "<script type='text/javascript' src='/app/javascript/jquery/jquery.ui.touch-punch.min.js'></script>";
		$html .= "<script type='text/javascript' src='/app/javascript/plugin/anibanner/jquery.anibanner.js?v=20140808'></script>";
		$html .= "<link rel='stylesheet' type='text/css' href='/app/javascript/plugin/anibanner/anibanner.css' />";
	}

	if($banner['navigation_paging_style']=='custom'){
		/* 이미지가로세로 크기 */
		@list($customImageWidth, $customImageHeight) = @getimagesize(ROOTPATH."data/skin/{$skin}/images/banner/{$banner_seq}/{$banner_item[0]['tab_image_inactive']}");
		$banner['navigation_paging_height'] = $customImageHeight;
	}

	$html .= "<div class='designBanner' designElement='banner' templatePath='{$template_path}' bannerSeq='{$banner_seq}' style='height:{$banner['height']}px;'></div>";

	$html .= "<script>";
	$html .= "$(function(){";
	$html .= "var settings = {";
	$html .= "'platform' : '{$banner['platform']}',";
	$html .= "'modtime' : '{$banner['modtime']}',";
	$html .= "'style' : '{$banner['style']}',";
	$html .= "'height' : '{$banner['height']}',";
	$html .= "'background_color' : '{$banner['background_color']}',";
	$html .= "'background_image' : '/data/skin/{$skin}/{$banner['background_image']}',";
	$html .= "'background_repeat' : '{$banner['background_repeat']}',";
	$html .= "'background_position' : '{$banner['background_position']}',";
	$html .= "'image_border_use' : '{$banner['image_border_use']}',";
	$html .= "'image_border_width' : '{$banner['image_border_width']}',";
	$html .= "'image_border_color' : '{$banner['image_border_color']}',";
	$html .= "'image_opacity_use' : '{$banner['image_opacity_use']}',";
	$html .= "'image_opacity_percent' : '{$banner['image_opacity_percent']}',";
	$html .= "'image_top_margin' : '{$banner['image_top_margin']}',";
	$html .= "'image_side_margin' : '{$banner['image_side_margin']}',";
	$html .= "'image_width' : '{$banner['image_width']}',";
	$html .= "'image_height' : '{$banner['image_height']}',";
	$html .= "'navigation_btn_style' : '{$banner['navigation_btn_style']}',";
	$html .= "'navigation_btn_visible' : '{$banner['navigation_btn_visible']}',";
	$html .= "'navigation_paging_style' : '{$banner['navigation_paging_style']}',";
	$html .= "'navigation_paging_height' : '{$banner['navigation_paging_height']}',";
	$html .= "'navigation_paging_align' : '{$banner['navigation_paging_align']}',";
	$html .= "'navigation_paging_position' : '{$banner['navigation_paging_position']}',";
	$html .= "'navigation_paging_margin' : '{$banner['navigation_paging_margin']}',";
	$html .= "'navigation_paging_spacing' : '{$banner['navigation_paging_spacing']}',";
	$html .= "'slide_event' : '{$banner['slide_event']}',";
	$html .= "'images' : [";
	foreach($banner_item as $k=>$item){
		if($k) $html .= ",";
		$html .= "{'link':'{$item['link']}','target':'{$item['target']}','image':'/data/skin/{$skin}/{$item['image']}'}";
	}
	$html .= "],";
	$html .= "'navigation_paging_custom_images' : [";
	foreach($banner_item as $k=>$item){
		if($k) $html .= ",";
		$html .= "{'active':'/data/skin/{$skin}/images/banner/{$banner_seq}/{$item['tab_image_active']}','inactive':'/data/skin/{$skin}/images/banner/{$banner_seq}/{$item['tab_image_inactive']}'}";
	}
	$html .= "]";
	$html .= "};";	
	$html .= "$('.designBanner[bannerSeq=\"{$banner_seq}\"]').anibanner(settings);";
	$html .= "});";
	$html .= "</script>";


	if($return) return $html;
	else echo $html;

	return;

}
?>