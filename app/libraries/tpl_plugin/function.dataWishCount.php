<?php

/**
 * @author lgs
 */

function dataWishCount($limit=null)
{
	$cnt = 0;
	$CI =& get_instance();
	if( $CI->userInfo['member_seq'] ){
		$CI->load->model('wishmodel');
		$cnt = $CI->wishmodel->get_wish_count($CI->userInfo['member_seq']);
	}
	return $cnt;
}
?>