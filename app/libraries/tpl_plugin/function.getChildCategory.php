<?php 

/* 하위카테고리 목록 반환 */
function getChildCategory($code,$exactly=false,$division='catalog')
{
	$CI =& get_instance();
	$CI->load->model('categorymodel');
	return $CI->categorymodel->getChildCategory($code,$exactly,$division);
}

?>