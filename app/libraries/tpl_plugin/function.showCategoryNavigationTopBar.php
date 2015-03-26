<?php

/* 카테고리 네비게이션 출력*/
function showCategoryNavigationTopBar()
{

	$CI =& get_instance();

	$skin_configuration = skin_configuration($CI->skin);

	//초기세팅 skin.ini 파일에 기본값을 넣어준다.
	if(!$skin_configuration['topbar']){
		$data = "topBar|1|5|1|1";
		skin_configuration_save($CI->designWorkingSkin,"topbar",$data);
		$skin_configuration['topbar'] = $data;
	}

	$topbar = explode('|',$skin_configuration['topbar']);
	$html = "<font color='red'>설정파일이 누락되었습니다.</font>";
	if(sizeOf($topbar) == 5){
		$skin_configuration['category_type'] = !empty($skin_configuration['topbar']) ? $topbar[0] : 'topBar';
		$category_template_path = $CI->skin.'/'.'_modules/category/category_'.$skin_configuration['category_type'].'.html';
		$category_skin_filepath = ROOTPATH.'data/skin/'.$category_template_path;
		$category_skin_filename = basename($category_template_path);

		$categoryNavigationKey = "categoryNavigation".uniqid();

		if(file_exists($category_skin_filepath)){
			$CI->load->model('categorymodel');
			$category = $CI->categorymodel->get_category_view(null,2);
			$tmpCategory = array();
			if($topbar[2]){
				foreach($category as $key){
					$tmpCategory[++$i] = $key;
					if($i == $topbar[2]) break;
				}
			}
			$data = array(
				'category'=>$tmpCategory,
				'categoryNavigationKey'=>$categoryNavigationKey,
				'allcategory' => $topbar[1],
				'brand' => $topbar[3],
				'location' => $topbar[4]
			);
			$CI->template->assign($data);
			$CI->template->define(array('category'=>$category_template_path));
			$html = $CI->template->fetch("category");
		}else{
			$html = "<font color='red'>{$category_skin_filename} 파일을 찾을 수 없습니다.</font>";
		}
	}

	echo "<div class='topBar' id='{$categoryNavigationKey}' designElement='topBar'>";
	echo $html;
	echo "</div>";

	return;
}

?>