<?php 

/* 하위브랜드 목록 반환 */
function getChildBrand($code,$exactly=false,$division='catalog')
{
	$CI =& get_instance();
	$CI->load->model('brandmodel');
	return $CI->brandmodel->getChildBrand($code,$exactly,$division);
}

?>