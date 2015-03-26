<?php

/**
 * @author ysm
 * $seq 콤마(,) 구분자로 원하는 프로모션고유번호 없으면 일반코드
 */

function showTopPromotion($seq=null,$bodywidth=null)
{
	$CI =& get_instance();
	if($CI->isplusfreenot['ispromotioncode']) {//사용시

		$CI->load->model('promotionmodel');
		$today = date("Y-m-d",time());
		/* 게시판일경우 게시판스킨별로 레이아웃이 별도처리되므로 분기하여 처리함 */
		if($CI->uri->segment(1)=='board'){
			$board_template_path = $CI->skin.'/'.$CI->template_path;
			$tpl_path = substr($board_template_path,strpos($board_template_path,'/')+1);
			$layout_config = layout_config_autoload($CI->skin,$tpl_path);
		}else{
			$tpl_path = substr($template_path,strpos($template_path,'/')+1);
			$layout_config = layout_config_autoload($CI->skin,$tpl_path);
		}
		$bodywidth = ($bodywidth)?$bodywidth:$layout_config[0]['width'];

		$sc['whereis'] = " and type in ('promotion', 'promotion_shipping') ";//일반코드
		$sc['whereis'] .= " and mainshow = 1 ";//메인노출용만
		$sc['whereis'] .= " and issue_startdate <= '".$today."' and issue_enddate >= '".$today."' ";//유효기간
		//회원체크
		//선착순
		if($seq) {
			$seqar = explode(",",$seq);
			$sc['whereis'] .= " and promotion_seq in ('".implode($seqar)."')";
		}

		$promotioncode = $CI->promotionmodel->get_data($sc);

		$returnhtml = '';
		foreach($promotioncode as $widget){
			if($widget['promotion_img'] == 1 ){
				$widget['bgimg'] = '/data/promotion/promotion_skin_01.gif';
			}else{
				$widget['bgimg'] = '/data/promotion/'.$widget['promotion_image4'];
			}

			$node_text_normalar = explode("^^",$widget['node_text_normal']);
			$widget['node_text_normal'] = $node_text_normalar[0];
			$widget['node_text_normal_style'] = font_decoration_attr($widget['node_text_normal'],'css','style');
			$node_text_normal_url_orgin = ($node_text_normalar[1]) ? json_decode($node_text_normalar[1]) : array();
			$widget['node_text_normal_url_s'] = ($node_text_normal_url_orgin->href)?"<a href='".$node_text_normal_url_orgin->href."' target='".$node_text_normal_url_orgin->target."' >":'';
			if($node_text_normal_url_orgin->href){
				$widget['node_text_normal_url_onclick'] = ($node_text_normal_url_orgin->target == '_blank' )?" onclick=\"window.open('".$node_text_normal_url_orgin->href."', 'promotion', '')\" ":" onclick=\"document.location.href='".$node_text_normal_url_orgin->href."'\" ";
			}
			$widget['node_text_normal_url_e'] = ($node_text_normal_url_orgin->href)?"</a>":'';

			$widget['node_text_show'] = str_replace("[프로모션코드설명]",$widget['promotion_desc'],str_replace("[프로모션코드]",$widget['promotion_input_serialnumber'],$widget['node_text']));
			$returnhtml .= "<li  style=\"height:40px; width:".$bodywidth."px;background:url('".$widget['bgimg']."');\" bgurl='".$widget['bgimg']."' class=\" hand hide \"  ".$widget['node_text_normal_url_onclick']." >".$widget['node_text_normal_url_s']."<span ".$widget['node_text_normal_style'].">".$widget['node_text_show']."</span>".$widget['node_text_normal_url_e']."</li>";//
		}
	}

	return $returnhtml;
}
?>