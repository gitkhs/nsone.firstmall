<?php

/* 카테고리 페이지 네비게이션 출력*/
function showBrandPageNavigation()
{
	
	$CI =& get_instance();
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

	$skin_configuration = skin_configuration($CI->skin);
	$skin_configuration['brand_navigation_type'] = !empty($skin_configuration['brand_navigation_type']) ? $skin_configuration['brand_navigation_type'] : 'single';
	$skin_configuration['brand_navigation_count_w'] = !empty($skin_configuration['brand_navigation_count_w']) ? explode("|",$skin_configuration['brand_navigation_count_w']) : array(4,4,4,4);
	
	if(strlen($_GET['code'])>8){
		$skin_configuration['brand_navigation_type'] = 'single';
	}
	
	$category_template_path = $CI->skin.'/'.'_modules/brand/category_page_'.$skin_configuration['brand_navigation_type'].'.html';
	$category_skin_filepath = ROOTPATH.'data/skin/'.$category_template_path;
	$category_skin_filename = basename($category_template_path);
	
	$brandPageNavigationKey = "brandPageNavigation".uniqid();

	if(file_exists($category_skin_filepath)){
		$CI->load->model('categorymodel');
		$CI->template->assign(array(
			'brandPageNavigationKey'=>$brandPageNavigationKey,
			'brand_navigation_count_w'=>$skin_configuration['brand_navigation_count_w'][strlen($_GET['code'])/4-1],
			'count_w'=>$skin_configuration['brand_navigation_count_w'][strlen($_GET['code'])/4-1]
		));
		$CI->template->define(array('category'=>$category_template_path));
		$html = $CI->template->fetch("category");
	}else{
		$html = "<font color='red'>{$category_skin_filename} 파일을 찾을 수 없습니다.</font>";
	}
	
	echo "<div class='designBrandPageNavigation' id='{$brandPageNavigationKey}' designElement='brandPageNavigation' templatePath='{$template_path}'>";
	echo $html;
	echo "</div>";
	
	return;
}

?>