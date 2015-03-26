<?php
function addImageAttributes($source, $tpl){
	global $__tmp_tpl_path, $__tmp_template_functions;

	$__tmp_template_functions = array(
		'showBrandNavigation',
		'showBrandPageNavigation',
		'showBrandRecommendDisplay',
		'showCategoryNavigation',
		'showCategoryPageBrandNavigation',
		'showCategoryPageNavigation',
		'showCategoryRecommendDisplay',
		'showDesignBanner',
		'showDesignDisplay',
		'showDesignDisplayPaging',
		'showDesignFlash',
		'showDesignLastest',
		'showDesignPopup',
		'showDesignVideo',
		'showLocationNavigation',
		'showLocationRecommendDisplay'
	);

	$on_ms   =$tpl->on_ms;
	$tpl_path =$tpl->tpl_path;
	
	if ($on_ms) $tpl_path=preg_replace('@\\\\+@', '/', $tpl_path);

	$__tmp_tpl_path = $tpl_path;
	
	$source = preg_replace_callback("/<(img|input)[^>]*src[^>]*>/i",'_callback_addImageAttributes', $source);
	//$source = preg_replace_callback("/<(table|tr|td|th)[^>]*background[^>]*>/i",'_callback_addImageAttributes', $source);

	$source = preg_replace_callback("/\{=(".implode("|",$__tmp_template_functions).")\([^\)]*\)\}/i",'_callback_addTemplatePathToDesignElement', $source);

	return $source;
}

function _callback_addImageAttributes($matches){

	global $__tmp_tpl_path;

	$tpl_path = $__tmp_tpl_path;

	$string = $matches[0];

	$designTplPath = str_replace("{$_SERVER['DOCUMENT_ROOT']}/data/skin/","",$tpl_path);
	$designTplPath = base64_encode($designTplPath);

	preg_match("/(src|background)[\s]*=[\s]*[\"|\']?([^>\"']+)[\"|\']?/i",$string,$src_matches);
	$designImgSrc = $src_matches[2];
	$designImgSrc = base64_encode($designImgSrc);
	
	# 속성이 있으면 삭제
	$string = preg_replace("/ (designTplPath|designImgSrc|designElement)=(\"|\')?[^>]*(\"|\')?/","",$string);

	# designTplPath 속성 추가
	$string = preg_replace("/[\s]?(\/?>)$/"," designTplPath='".$designTplPath."' $1",$string);

	# designImgSrc 속성 추가
	$string = preg_replace("/[\s]?(\/?>)$/"," designImgSrc='".$designImgSrc."' $1",$string);

	# designElement 속성 추가
	$string = preg_replace("/[\s]?(\/?>)$/"," designElement='image' $1",$string);

	return $string;
}

/* 카테고리 치환코드에 templatPath값 넣기 */
function _callback_addTemplatePathToDesignElement($matches){

	global $__tmp_tpl_path, $__tmp_template_functions;

	$templatPath = $__tmp_tpl_path;
	$templatPath = str_replace("{$_SERVER['DOCUMENT_ROOT']}/data/skin/","",$templatPath);
	$templatPath = preg_replace("/^([^\/]+)\//","",$templatPath);
	
	$string = $matches[0];

	foreach($__tmp_template_functions as $funcName){
		$string = preg_replace("/\{=".$funcName."\(/i","{=setTemplatePath('".$templatPath."')}{=".$funcName."(",$string);
	}

	return $string;
}
