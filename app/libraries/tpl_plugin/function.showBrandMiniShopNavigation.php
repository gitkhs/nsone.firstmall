<?php

/* 카테고리 페이지 네비게이션 출력*/
function showBrandMiniShopNavigation($template_path='')
{
	
	$CI =& get_instance();

	$skin_configuration = skin_configuration($CI->skin);
	$skin_configuration['brand_navigation_type'] = !empty($skin_configuration['brand_navigation_type']) ? $skin_configuration['brand_navigation_type'] : 'single';
	$skin_configuration['brand_navigation_count_w'] = !empty($skin_configuration['brand_navigation_count_w']) ? explode("|",$skin_configuration['brand_navigation_count_w']) : array(4,4,4,4);
	
	if(strlen($_GET['brand'])>8){
		$skin_configuration['brand_navigation_type'] = 'single';
	}
	
	$category_template_path = $CI->skin.'/'.'_modules/brand/category_minishop_'.$skin_configuration['brand_navigation_type'].'.html';
	$category_skin_filepath = ROOTPATH.'data/skin/'.$category_template_path;
	$category_skin_filename = basename($category_template_path);
	
	$brandPageNavigationKey = "brandPageNavigation".uniqid();

	if(file_exists($category_skin_filepath)){
		$cont_w_num	= (strlen($_GET['brand'])==0) ? 0 : (strlen($_GET['brand']) / 4) - 1;
		$CI->load->model('categorymodel');
		$CI->template->assign(array(
			'brandPageNavigationKey'=>$brandPageNavigationKey,
			'count_w'=>$skin_configuration['brand_navigation_count_w'][$cont_w_num]
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