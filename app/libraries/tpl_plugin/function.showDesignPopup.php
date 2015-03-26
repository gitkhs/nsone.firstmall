<?php

/* 팝업 출력*/
function showDesignPopup($popup_seq)
{
	$CI =& get_instance();
	$CI->load->helper('javascript');
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

	$popup_key = "designPopup{$popup_seq}";
	
	$query  = $CI->db->query("select * from fm_design_popup where popup_seq = ?",$popup_seq);
	$data = $query->row_array();
	
	if(!$data) return;

	$now_time = time();
	$flag = $data['status']=='show' || ($data['status']=='period' && $now_time >= strtotime($data['period_s']) && $now_time <= strtotime($data['period_e'])) ? true : false;	

	if($CI->input->cookie($popup_key)) {
		if($CI->input->cookie($popup_key)=='1' || time()-$CI->input->cookie($popup_key) < 86400){
			$flag = false; //창숨김처리 쿠키 체크
		}
	}
	
	if($CI->layout->is_design_mode() && $CI->input->cookie('designEditMode')) {
		if($data['status']=='show' || ($data['status']=='period' && $now_time <= strtotime($data['period_e']))){
			$flag = true; //디자인편집모드일땐 무조건 팝업 보여주기
		}
	}
	
	if($flag){
		$html = "";

		if($data['style']=='layer'){
			$html .= "<div class='designPopup' popupStyle='layer' designElement='popup' templatePath='{$template_path}' popupSeq='{$popup_seq}' style='left:{$data['loc_left']}px;top:{$data['loc_top']}px;'>";
			
			if($data['contents_type']=='image'){
				$html .= "<div class='designPopupBody'>";
				if($data['link'])  $html .= "<a href='{$data['link']}'>";	
				$html .= "<img src='/data/popup/{$data['image']}' />";	
				if($data['link'])  $html .= "</a>";
			}
			
			if($data['contents_type']=='text'){
				
				/* 플래시매직 치환 */
				if(preg_match_all("/\{[\s]*=[\s]*showDesignFlash[\s]*\([\s]*([0-9]+)[\s]*\)[\s]*\}/",$data['contents'],$matches)){
					$CI->template->include_('showDesignFlash');
					foreach($matches[0] as $idx=>$val){
						$flash_seq = $matches[1][$idx];
						$replaceContents = showDesignFlash($flash_seq,true);
						$data['contents'] = str_replace($val,$replaceContents,$data['contents']);
					}
				}
				
				$html .= "<div class='designPopupBody' style='width:{$data['width']}px;height:{$data['height']}px;background-color:#fff;'>";
				$html .= $data['contents'];				
			}

			$designPopupTodaymsgCss = font_decoration_attr($data['bar_msg_today_decoration'],'css','style');
			$designPopupCloseCss = font_decoration_attr($data['bar_msg_close_decoration'],'css','style');
			
			$html .= "</div>";
			$html .= "<div class='designPopupBar' style='background-color:{$data['bar_background_color']}'>";
			$html .= "<div class='designPopupTodaymsg' {$designPopupTodaymsgCss}><label><input type='checkbox' /> {$data['bar_msg_today_text']}</label></div>";
			$html .= "<div class='designPopupClose' {$designPopupCloseCss}>{$data['bar_msg_close_text']}</div>";
			$html .= "</div>";
			$html .= "</div>";
		}

		if($data['style']=='mobile_layer'){

			/* 이미지가로세로 크기 */
			@list($imgWidth, $imgHeight) = @getimagesize(ROOTPATH."data/popup/{$data['image']}");
			
			$html .= "<div class='designPopup' popupStyle='layer' designElement='popup' templatePath='{$template_path}' popupSeq='{$popup_seq}' style='left:{$data['loc_left']}px;top:{$data['loc_top']}px;'>";
			
			if($data['contents_type']=='image'){
				$html .= "<div class='designPopupBody'>";
				if($data['link'])  $html .= "<a href='{$data['link']}'>";	
				$html .= "<img src='/data/popup/{$data['image']}' width='{$imgWidth}' height='{$imgHeight}' />";	
				if($data['link'])  $html .= "</a>";
			}
			
			if($data['contents_type']=='text'){
				
				/* 플래시매직 치환 */
				if(preg_match_all("/\{[\s]*=[\s]*showDesignFlash[\s]*\([\s]*([0-9]+)[\s]*\)[\s]*\}/",$data['contents'],$matches)){
					$CI->template->include_('showDesignFlash');
					foreach($matches[0] as $idx=>$val){
						$flash_seq = $matches[1][$idx];
						$replaceContents = showDesignFlash($flash_seq,true);
						$data['contents'] = str_replace($val,$replaceContents,$data['contents']);
					}
				}
				
				$html .= "<div class='designPopupBody' style='width:{$data['width']}px;height:{$data['height']}px;background-color:#fff;'>";
				$html .= $data['contents'];				
			}		
			
			$designPopupTodaymsgCss = font_decoration_attr($data['bar_msg_today_decoration'],'css','style');
			$designPopupCloseCss = font_decoration_attr($data['bar_msg_close_decoration'],'css','style');
			
			$html .= "</div>";
			$html .= "<div class='designPopupBar' style='background-color:{$data['bar_background_color']}'>";
			$html .= "<div class='designPopupTodaymsg' {$designPopupTodaymsgCss}><label><input type='checkbox' /> {$data['bar_msg_today_text']}</label></div>";
			$html .= "<div class='designPopupClose' {$designPopupCloseCss}>{$data['bar_msg_close_text']}</div>";
			$html .= "</div>";
			$html .= "</div>";
		}
		
		if($data['style']=='window'){
			$html .= "<div class='designPopupIcon hide' designElement='popup' templatePath='{$template_path}' popupSeq='{$popup_seq}' style='border:3px dashed gray; left:{$data['loc_left']}px;top:{$data['loc_top']}px;'><span class='btn small red'><input type='button' value='팝업[{$popup_seq}] 설정' /></span></div>";
			
			if($data['contents_type']=='image'){
				$imagePath = ROOTPATH.'/data/popup/'.$data['image'];
				list($imageWidth,$imageHeight) = @getimagesize($imagePath);
				$popupWidth = $imageWidth;
				$popupHeight+=$imageHeight+25;
			}
			if($data['contents_type']=='text'){
				$popupWidth = $data['width'];
				$popupHeight = $data['height']+25;
			}		
			
			
			$html .= js("window.open('/popup/designpopup?seq={$popup_seq}&popup_key={$popup_key}','{$popup_key}','width={$popupWidth},height={$popupHeight},left={$data['loc_left']},top={$data['loc_top']},resizable=no,toolbar=no,menubar=no,status=no,scrollbars=no');");
		}

		if($data['style']=='band'){

			$ilineCss = "";
			if($data['band_background_image']){
				$ilineCss .= "background-image:url(/data/popup/{$data['band_background_image']});";
				if($data['band_background_image_repeat']) $ilineCss .= "background-repeat:{$data['band_background_image_repeat']};";
				if($data['band_background_image_position']) $ilineCss .= "background-position:{$data['band_background_image_position']};";
			}elseif($data['band_background_color']){
				$ilineCss .= "background-color:{$data['band_background_color']};";
			}

			$html .= "<div class='designPopupBand relative hide' popupStyle='band' designElement='popup' templatePath='{$template_path}' popupSeq='{$popup_seq}' style='{$ilineCss}'>";
			$html .= "<div class='designPopupBody center'>";
			if($data['link'])  $html .= "<a href='{$data['link']}'>";	
			$html .= "<img src='/data/popup/{$data['image']}' />";	
			if($data['link'])  $html .= "</a>";
			$html .= "</div>";
			$html .= "<div class='designPopupClose absolute hand' style='right:10px;top:20px;'><img src='/data/icon/common/etc/band_btn_close.gif' /></div>";
			$html .= "</div>";
			$html .= "<div class='designPopupBandBtn hide absolute center hand' style='top:0px; left:50%; width:200px; margin-left:-100px;'><img src='/data/icon/common/etc/band_btn_open.gif' /></div>";
		}

		if($data['style']=='mobile_band'){

			$ilineCss = "";
			if($data['band_background_image']){
				$ilineCss .= "background-image:url(/data/popup/{$data['band_background_image']});";
				if($data['band_background_image_repeat']) $ilineCss .= "background-repeat:{$data['band_background_image_repeat']};";
				if($data['band_background_image_position']) $ilineCss .= "background-position:{$data['band_background_image_position']};";
			}elseif($data['band_background_color']){
				$ilineCss .= "background-color:{$data['band_background_color']};";
			}

			$html .= "<div class='designPopupBandMobile hide' popupStyle='band' designElement='popup' templatePath='{$template_path}' popupSeq='{$popup_seq}' style='position:relative; {$ilineCss}'>";
			$html .= "<div class='designPopupBody center'>";
			if($data['link'])  $html .= "<a href='{$data['link']}'>";	
			$html .= "<img src='/data/popup/{$data['image']}' style='max-width:100%' />";	
			if($data['link'])  $html .= "</a>";
			$html .= "</div>";
			$html .= "<div class='designPopupClose absolute hand' style='right:10px;top:20px;'><img src='/data/icon/common/etc/band_btn_close.gif' /></div>";
			$html .= "</div>";
		}
		
		echo $html;
	}
	
	return;
}
?>