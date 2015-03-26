<?php

/**
 * @author lgs
 */

function dataCartCount()
{
	$CI =& get_instance();
	$CI->load->model('cartmodel');
	return $CI->cartmodel->get_cart_count();
}
?>