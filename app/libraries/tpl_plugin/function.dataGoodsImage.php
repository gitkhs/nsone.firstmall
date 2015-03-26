<?php

/**
 * @author lgs 'large', 'view', 'list1', 'list2', 'thumbView', 'thumbCart', 'thumbScroll'
 */

function dataGoodsImage($goods_seq,$image_type='list1',$cut_number=1)
{
	$CI =& get_instance();
	$query = "select image from fm_goods_image where goods_seq=? and image_type=? and cut_number=?";
	$query = $CI->db->query($query,array($goods_seq,$image_type,$cut_number));
	$data = $query->row_array();
	return $data['image'];
}
?>