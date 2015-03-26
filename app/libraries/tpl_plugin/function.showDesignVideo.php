<?php

/* 동영상 출력*/
function showDesignVideo($video_seq,$size=null,$return=false)
{

	$CI =& get_instance();
	$CI->load->helper('javascript');
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

	$video_key = "designvideo{$video_seq}";
	if(  defined('__ADMIN__') ){
		$thumbplay = "/admin/skin/".$CI->skin."/images/common/thumb_play.png";
	}else{
		$thumbplay = "/data/skin/".$CI->skin."/images/common/thumb_play.png";
	}


	$query  = $CI->db->query("select * from fm_videofiles where seq = ?",$video_seq);
	$data = $query->row_array();

	if(!$data) return;

	$flag = true;

	if($CI->input->cookie($video_key)) $flag = false; //창숨김처리 쿠키 체크
	if( defined('__ADMIN__') != true ){
		if($CI->layout->is_design_mode() && $CI->input->cookie('designEditMode')) {
			$flag = true; //디자인편집모드일땐 무조건 팝업 보여주기
		}
	}
	if(isset($size)){
		$size = @explode("X",$size);
		$width = $size[0];
		$height = $size[1];
	}
	$CI->manager['video_use'] = 'Y';//2014-02-14강제로 동영상노출

	if($flag){
		$html = "";
		$html .= "<style>.thumbnailvideo {position:absolute;z-index:5;background-position:center;background:url('".$thumbplay."') no-repeat center center;vertical-align:middle;}</style>";

		if($CI->mobileMode || $CI->storemobileMode || $CI->_is_mobile_agent) {//mobile 접속시
			if( $data['file_key_i'] && uccdomain('fileurl',$data['file_key_i']) ) {
				if(empty($width)){
					$width =($data['mobile_width']>0)?$data['mobile_width']:200;
				}
				if(empty($height)){
					$height =($data['mobile_height']>0)?$data['mobile_height']:100;
				}
				$src = uccdomain('fileurl',$data['file_key_i']);
				$imgsrc = uccdomain('thumbnail',$data['file_key_i']);

				$html .= '<div class="designvideo" designElement="video" templatePath="'.$template_path.'" videoSeq="'.$video_seq.'" realwidth="'.$size[0].'"  realheight="'.$size[1].'" style="width:'.$width.'px;height:'.$height.'px;">';
					$html .= '		<div class="content DisplayVideoWrap hand" style="width:'.$width.';height:'.$height.';" ><span class="thumbnailvideo" width="'.$width.'" height="'.$height.'"></span>';
					$html .= '			<iframe width="'.$width.'" height="'.$height.'" src="'.$src.'&g=tag&width='.$width.'&height='.$height.'" frameborder="0" allowfullscreen class="hide" ></iframe>';
					$html .= '		<img src="'.$imgsrc.'" width="'.$width.'" height="'.$height.'"  onerror="this.src=\'/data/skin/'.$CI->skin.'/images/common/noimage.gif\'" />';
					$html .= '		</div>';
				$html .= '</div>';
			}elseif( $data['file_key_w'] && uccdomain('fileurl',$data['file_key_w'] ) ) {
				if(empty($width)){
					$width =($data['mobile_width']>0)?$data['mobile_width']:200;
				}
				if(empty($height)){
					$height =($data['mobile_height']>0)?$data['mobile_height']:100;
				}
				$src = uccdomain('fileurl',$data['file_key_w']);
				$imgsrc = uccdomain('thumbnail',$data['file_key_w']);
				$html .= '<div class="designvideo" designElement="video" templatePath="'.$template_path.'" videoSeq="'.$video_seq.'"  realwidth="'.$size[0].'"  realheight="'.$size[1].'" style="width:'.$width.'px;height:'.$height.'px;">';
					$html .= '	<div class="content DisplayVideoWrap hand" style="width:'.$width.';height:'.$height.';" ><span class="thumbnailvideo" width="'.$width.'" height="'.$height.'"></span>';
					$html .= '			<iframe width="'.$width.'" height="'.$height.'" src="'.$src.'&g=tag&width='.$width.'&height='.$height.'" frameborder="0" allowfullscreen class="hide" ></iframe>';
					$html .= '		<img src="'.$imgsrc.'" width="'.$width.'" height="'.$height.'"  onerror="this.src=\'/data/skin/'.$CI->skin.'/images/common/noimage.gif\'" />';
					$html .= '	</div>';
				$html .= '</div>';
			}
		}else{
			if( $data['file_key_w'] && uccdomain('fileurl',$data['file_key_w'] ) ) {
				if(empty($width)){
					$width =($data['pc_width']>0)?$data['pc_width']:400;
				}
				if(empty($height)){
					$height =($data['pc_height']>0)?$data['pc_height']:300;
				}
				$src = uccdomain('fileswf',$data['file_key_w']);
				$imgsrc = uccdomain('thumbnail',$data['file_key_w']);
				$html .= '<div class="designvideo" designElement="video" templatePath="'.$template_path.'" videoSeq="'.$video_seq.'"  realwidth="'.$size[0].'"  realheight="'.$size[1].'" style="width:'.$width.'px;height:'.$height.'px;">';
					$html .= '	<div class="content DisplayVideoWrap hand" style="width:'.$width.';height:'.$height.';" ><span class="thumbnailvideo" width="'.$width.'" height="'.$height.'"></span>';
					$html .= '		<embed src="'.$src.'" width="'.$width.'" height="'.$height.'"  allowfullscreen="true" wmode="transparent" class="hide" ></embed>';
					$html .= '		<img src="'.$imgsrc.'" width="'.$width.'" height="'.$height.'"  onerror="this.src=\'/data/skin/'.$CI->skin.'/images/common/noimage.gif\'" />';
					$html .= '	</div>';
				$html .= '</div>';
			}
		}
		if($return) return $html;
		else echo $html;
	}

	return;

}
?>