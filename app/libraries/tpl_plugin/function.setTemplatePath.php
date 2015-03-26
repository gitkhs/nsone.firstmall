<?php
// 치환코드 내에서 사용하는 $__tmp_template_path 변수에 값을 저장
function setTemplatePath($templatPath)
{
	$CI =& get_instance();
	$CI->__tmp_template_path = $templatPath;
}

?>