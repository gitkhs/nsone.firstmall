<?php

function getGoodsOptions($goods_seq)
{
	
	$CI =& get_instance();

	$query = $CI->db->query("select * from fm_goods_option where goods_seq=?",$goods_seq);
	$result = $query->result_array();

	$title = array();
	$option_rows = array();

	foreach($result as $row){
		if(!$title) $title = explode(",",$row['option_title']);
		$option_rows[$row['option_seq']] = array(
			'option_title' => $title,
			'options' => array($row['option1'],$row['option2'],$row['option3'],$row['option4'],$row['option5'])
		);
	}

	return $option_rows;
}

?>