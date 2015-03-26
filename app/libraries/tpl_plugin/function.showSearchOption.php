<?php
function showSearchOption($kinds=array('category','brand','option1','option2','rate')){
	$CI =& get_instance();
	$CI->load->model('SearchoptionModel');
	
	$params = $_POST ? $_POST : $_GET;
	
	if(preg_match("/^\/goods\/brand_list/",$_SERVER['REQUEST_URI']))	{
		$CI->SearchoptionModel->conditions['brand_prefix_group'] = $params['brand_prefix_group'];
		$CI->SearchoptionModel->conditions['brand_prefix'] = $params['brand_prefix'];
	}
	else if(preg_match("/^\/goods\/catalog/",$_SERVER['REQUEST_URI']))	$CI->SearchoptionModel->conditions['category_code'] = array($params['code']);
	else if(preg_match("/^\/goods\/brand/",$_SERVER['REQUEST_URI']))	$CI->SearchoptionModel->conditions['brand_code'] = array($params['code']);
	else if(preg_match("/^\/goods\/search/",$_SERVER['REQUEST_URI']))	$CI->SearchoptionModel->conditions['search_text'] = array($params['search_text']);

	if(in_array('brand',$kinds)){
		$searchOption['brand']		= $CI->SearchoptionModel->get_results("brand");
	}
	if(in_array('category',$kinds)){
		$searchOption['category']	= $CI->SearchoptionModel->get_results("category");
	}
	if(in_array('option1',$kinds)){
		$searchOption['option1']	= $CI->SearchoptionModel->get_results("option1");
	}
	if(in_array('option2',$kinds)){
		$searchOption['option2']	= $CI->SearchoptionModel->get_results("option2");
	}
	if(in_array('rate',$kinds)){
		$searchOption['rate']	= $CI->SearchoptionModel->get_results("rate");
	}

	$CI->SearchoptionModel->set_conditions();
	
	$CI->template->assign(array('searchOptionconditions'=>$CI->SearchoptionModel->conditions));
	$CI->template->assign(array('searchOption'=>$searchOption));
	$CI->template->define(array('searchOption'=>$CI->skin.'/_modules/common/search_option.html'));
	$CI->template->assign(array('searchOptionKinds'=>$kinds));
	echo $CI->template->fetch("searchOption");
}
?>