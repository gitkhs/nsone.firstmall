<?php

/* 지역 네비게이션 출력*/
function showLocationNavigation()
{
	
	$CI =& get_instance();
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

	$skin_configuration = skin_configuration($CI->skin);
	$skin_configuration['location_type'] = !empty($skin_configuration['location_type']) ? $skin_configuration['location_type'] : 'y_single';
	$location_template_path = $CI->skin.'/'.'_modules/location/location_'.$skin_configuration['location_type'].'.html';
	$location_skin_filepath = ROOTPATH.'data/skin/'.$location_template_path;
	$location_skin_filename = basename($location_template_path);
	
	$locationNavigationKey = "locationNavigation".uniqid();

	if(file_exists($location_skin_filepath)){
		$CI->load->model('locationmodel');
		
		switch($skin_configuration['location_type']){
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
		
		$location = $CI->locationmodel->get_location_view(null,$maxDepth);

		switch($maxDepth){
			case "2":
				foreach($location as $k=>$node){
					$location[$k]['node_banner'] = showdesignEditor($node['node_banner']);
				}
			break;
			case "3":
				foreach($location as $k=>$node){
					foreach($location[$k]['childs'] as $j=>$child){
						$location[$k]['childs'][$j]['node_banner'] = showdesignEditor($child['node_banner']);
					}
				}
			break;
		}

		$CI->template->assign(array('location'=>$location,'locationNavigationKey'=>$locationNavigationKey));
		$CI->template->define(array('location'=>$location_template_path));
		$html = $CI->template->fetch("location");		
	}else{
		$html = "<font color='red'>{$location_skin_filename} 파일을 찾을 수 없습니다.</font>";
	}
	
	echo "<div class='designLocationNavigation' id='{$locationNavigationKey}' designElement='locationNavigation' templatePath='{$template_path}'>";
	echo $html;
	echo "</div>";
	
	return;
}

?>