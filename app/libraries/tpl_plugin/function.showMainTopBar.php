<?php

/* 모바일 메인상단바 출력*/
function showMainTopBar($template_path='')
{

	$CI =& get_instance();
	
	$skin_configuration = skin_configuration($CI->skin);
	$topbar_template_path = $CI->skin.'/'.'_modules/common/topbar.html';
	$topbar_skin_filepath = ROOTPATH.'data/skin/'.$topbar_template_path;

	if(file_exists($topbar_skin_filepath)){
		$topbar	= explode('|',$skin_configuration['topbar']);
		$query	= "select * from fm_topbar_style left join fm_topbar_file on tab_index = style_index order by tab_seq";
		$query	= $CI->db->query($query);
		$data	= $query->result_array();

		$opt["tab_type"] = $data[0]["tab_type"];
		$opt["tab_style"] = $data[0]["tab_style"];
		$opt["tab_cursor"] = $data[0]["tab_cursor"];
		$opt["tab_img_prev"] = $data[0]["tab_img_prev"];
		$opt["tab_img_next"] = $data[0]["tab_img_next"];

		$CI->template->assign(array('lists'=>$data,'opt'=>$opt));
		$CI->template->define(array('topbar'=>$topbar_template_path));
		$html = $CI->template->fetch("topbar");
	}
	echo $html;

	return;
}

?>