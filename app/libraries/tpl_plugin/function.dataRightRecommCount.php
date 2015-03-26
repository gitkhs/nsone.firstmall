<?php

/**
 * @author lgs
 */

function dataRightRecommCount($limit=null)
{
	$CI =& get_instance();
	$CI->load->model('goodsmodel');
	$cnt = $CI->goodsmodel->get_recommend_goods_count();
	return $cnt;
}
?>