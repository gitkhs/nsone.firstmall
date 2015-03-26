<?php

/* 상품디스플레이 출력*/
function showBrandRecommendDisplay($category_code)
{
	$CI =& get_instance();
	$CI->load->helper('javascript');
	$CI->load->model('goodsdisplay');
	$CI->load->model('goodsmodel');
	$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;
	
	// 디스플레이 임시 코드명
	$display_key = $CI->goodsdisplay->make_display_key();

	// 디스플레이 설정 데이터
	$query  = $CI->db->query("select d.* from fm_brand as c, fm_design_display as d where c.recommend_display_seq=d.display_seq and c.category_code = ?",$category_code);
	$display = $query->row_array();
	
	$limit = $display['count_w']*$display['count_h'];
	$limit = $limit ? $limit : 4;

	if($display){

		$display_seq = $display['display_seq'];
		
		if($CI->designDisplayTabAjaxIdx){
			$display_tabs = $CI->goodsdisplay->get_display_tab($display_seq,$CI->designDisplayTabAjaxIdx);
			$display_tabs = array($CI->designDisplayTabAjaxIdx=>$display_tabs[0]);
		}else{
			$display_tabs = $CI->goodsdisplay->get_display_tab($display_seq);
		}

		foreach($display_tabs as $tab_index => $display_tab){
			if($display['tab_design_type']=='displayTabTypeImage' && $display_tab['tab_title_img']){
				$display_tabs[$tab_index]['tab_title'] = "<span class='displayTabItemImage'><img src='/data/icon/goodsdisplay/tabs/{$display_tab['tab_title_img']}' class='displayTabItemImageOff' title='{$display_tab['tab_title']}' /><img src='/data/icon/goodsdisplay/tabs/{$display_tab['tab_title_img_on']}' class='displayTabItemImageOn hide' /></span>";
			}
		}
		
		/**
		 * list setting
		**/
		$tabList = array();
		foreach($display_tabs as $tab_index => $display_tab){

			if($tab_index==0 || $CI->designDisplayTabAjaxIdx){
			
				$sc=array();
				
				// 상품 자동노출 조건 파싱
				if($display_tab['auto_use']=='y'){		
					
					$sc = $CI->goodsdisplay->search_condition($display_tab['auto_criteria'], $sc, 'recommend');
					
					$sc['sort']		= $perpage && !empty($_GET['sort']) ? $_GET['sort'] : $sc['auto_order'];
					$sc['brand'] = $category_code;	
				}else{
					$sc['sort']		= $perpage && !empty($_GET['sort']) ? $_GET['sort'] : 'display';
				}
							
				$sc['display_seq']		= $display_seq;
				$sc['display_tab_index']= $tab_index;
				$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
				$sc['perpage']			= $perpage ? $perpage : 1000;
				$sc['image_size']		= $display['image_size'];
				$sc['limit']			= $limit;

				if($CI->goodsdisplay->info_settings_have_eventprice($display['info_settings'])){
					$sc['join_event']	= true;
				}
		
				$list = $CI->goodsmodel->goods_list($sc);

				$tabList[$tab_index] = $display_tab;
				$tabList[$tab_index]['record'] = $list['record'];
			}else{
				$tabList[$tab_index] = $display_tab;
			}
		}
		
		$displayAllGoodsList = array();
		foreach($tabList as $row) $displayAllGoodsList = array_merge($displayAllGoodsList,$row['record']);
		
		if(!$CI->designDisplayTabAjaxIdx) echo "<div id='{$display_key}' class='designBrandRecommendDisplay' designElement='brandRecommendDisplay' templatePath='{$template_path}' displaySeq='{$display_seq}' perpage='{$perpage}' brand='{$category_code}'>";
		$CI->goodsdisplay->set('title',$display['title']);
		$CI->goodsdisplay->set('style',$display['style']);
		$CI->goodsdisplay->set('count_w',$display['count_w']);
		$CI->goodsdisplay->set('count_h',$display['count_h']);
		$CI->goodsdisplay->set('image_decorations',$display['image_decorations']);
		$CI->goodsdisplay->set('image_size',$display['image_size']);
		$CI->goodsdisplay->set('text_align',$display['text_align']);		
		$CI->goodsdisplay->set('info_settings',$display['info_settings']);
		$CI->goodsdisplay->set('display_key',$display_key);
		$CI->goodsdisplay->set('displayGoodsList',$displayAllGoodsList);
		$CI->goodsdisplay->set('displayTabsList',$tabList);
		$CI->goodsdisplay->set('tab_design_type',$display['tab_design_type']);
		$CI->goodsdisplay->print_();
		if(!$CI->designDisplayTabAjaxIdx) echo "</div>";
	}
	
	return;
}
?>