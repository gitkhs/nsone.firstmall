<?php

function addImageAttributesBefore($source, $tpl){
	global $__tmp_tpl_path;

	$on_ms   =$tpl->on_ms;
	$tpl_path =$tpl->tpl_path;
	if ($on_ms) $tpl_path=preg_replace('@\\\\+@', '/', $tpl_path);

	$__tmp_tpl_path = $tpl_path;

	$source = preg_replace_callback("/<(img|input)[^>]*src[^>]*>/i",'_callback_addImageAttributesBefore', $source);
	$source = preg_replace_callback("/<(table|tr|td|th)[^>]*background[^>]*>/i",'_callback_addImageAttributesBefore', $source);
	$source = preg_replace_callback("/<a [^>]*href[^>]*>/i",'_callback_addHrefAttributeBefore', $source);
	
	return $source;
}

function _callback_addImageAttributesBefore($matches){

	global $__tmp_tpl_path;

	$tpl_path = $__tmp_tpl_path;

	$string = $matches[0];

	preg_match("/(src|background)[\s]*=[\s]*[\"|\']?([^>\"']+)[\"|\']?/i",$string,$src_matches);
	$designImgSrcOri = base64_encode($src_matches[2]);

	# 속성이 있으면 삭제
	$string = preg_replace("/ (designImgSrcOri)=(\"|\')?[^>]*(\"|\')?/","",$string);

	# designImgSrcOri 속성 추가
	$string = preg_replace("/[\s]?(\/?>)$/"," designImgSrcOri='".$designImgSrcOri."' $1",$string);

	return $string;
}

/* A태그의 href 원본값 */
function _callback_addHrefAttributeBefore($matches){
	global $__tmp_tpl_path;

	$tpl_path = $__tmp_tpl_path;

	$string = $matches[0];

	preg_match("/href[\s]*=[\s]*[\"|\']?([^>\"']+)[\"|\']?/",$string,$src_matches);

	$hrefOri = base64_encode($src_matches[1]);

	# 속성이 있으면 삭제
	$string = preg_replace("/ (hrefOri)=(\"|\')?[^>]*(\"|\')?/","",$string);

	# hrefOri 속성 추가
	$string = preg_replace("/[\s]?(\/?>)$/"," hrefOri='".$hrefOri."' $1",$string);

	return $string;
}