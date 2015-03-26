<?php

/* 카테고리 네비게이션 출력*/
function showCategoryNavigation()
{
	$CI =& get_instance();
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;
	
	$skin_configuration = skin_configuration($CI->skin);
	$skin_configuration['category_type'] = !empty($skin_configuration['category_type']) ? $skin_configuration['category_type'] : 'y_single';
	$category_template_path = $CI->skin.'/'.'_modules/category/category_'.$skin_configuration['category_type'].'.html';
	$category_skin_filepath = ROOTPATH.'data/skin/'.$category_template_path;
	$category_skin_filename = basename($category_template_path);

	$categoryNavigationKey = "categoryNavigation".uniqid();

	if(file_exists($category_skin_filepath)){
		$CI->load->model('categorymodel');

		switch($skin_configuration['category_type']){
			case "y_single":
			case "x_single":
				$maxDepth = "1";
				break;
			case "y_single_sub":
			case "x_single_sub":
				$maxDepth = "2";
				break;
			case "y_double_sub":
			case "x_double":
				$maxDepth = "3";
				break;
			default :
				$maxDepth = "4";
				break;
		}

		$category = $CI->categorymodel->get_category_view(null,$maxDepth);

		switch($maxDepth){
			case "2":
				foreach($category as $k=>$node){
					$category[$k]['node_banner'] = showdesignEditor($node['node_banner']);
				}
			break;
			case "3":
				foreach($category as $k=>$node){
					foreach($category[$k]['childs'] as $j=>$child){
						$category[$k]['childs'][$j]['node_banner'] = showdesignEditor($child['node_banner']);
					}
				}
			break;
		}

		$CI->template->assign(array('category'=>$category,'categoryNavigationKey'=>$categoryNavigationKey));
		$CI->template->define(array('category'=>$category_template_path));
		$html = $CI->template->fetch("category");
	}else{
		$html = "<font color='red'>{$category_skin_filename} 파일을 찾을 수 없습니다.</font>";
	}

	echo "<div class='designCategoryNavigation' id='{$categoryNavigationKey}' designElement='categoryNavigation' templatePath='{$template_path}'>";
	echo $html;
	echo "</div>";

	return;
}

?>