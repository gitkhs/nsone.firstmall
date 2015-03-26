<?php 

/* 하위지역 목록 반환 */
function getChildLocation($code,$exactly=false,$division='catalog')
{
	$CI =& get_instance();
	$CI->load->model('locationmodel');
	return $CI->locationmodel->getChildLocation($code,$exactly,$division);
}

?>