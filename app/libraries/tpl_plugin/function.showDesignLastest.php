<?php

/* 팝업 출력*/
function showDesignLastest($boardId,$title,$lineCnt,$strcut=20,$option="",$lastestSkin="normal")
{
	$CI =& get_instance();
	$CI->load->helper('javascript');
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;
	
	$options = explode("|",$option);
	
	$recent = array();
	$recent['boardId'] = $boardId;
	$recent['title'] = $title;
	$recent['lineCnt'] = $lineCnt;
	$recent['strcut'] = $strcut;
	$recent['options'] = $options;
	
	if($options) foreach($options as $v) $recent[$v] = true;
	
	$lastest_key = "designLastest".uniqid();
	
	$params = base64_encode(serialize($recent));
	
	//echo "<div class='designLastest {$lastest_key}' designElement='lastest' templatePath='{$template_path}' params='{$params}'>";
	echo "<div class='{$lastest_key}'>";
	
	$CI->template->assign(array(
		'lastest_key'		=>$lastest_key,
		'recent'			=>$recent
	));
	
	$CI->template->define(array($lastest_key=>$CI->skin."/_modules/lastest/{$lastestSkin}.html"));
	$CI->template->print_($lastest_key);	
	echo "</div>";
	

	return;
}
?>