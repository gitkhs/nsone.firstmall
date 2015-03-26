<?php

/* 상품디스플레이 출력*/
function showDesignDisplay($display_seq,$perpage=null,$kind=null,$iscach=null){

	$CI			=& get_instance();
	$CI->load->helper('basic');
	$cfg_system	= ($CI->config_system) ? $CI->config_system : config_load('system');

	if	( $iscach == 'cach' || $cfg_system['display_cach'] == 'OFF' ){
		$CI->load->helper('javascript');
		$CI->load->model('goodsdisplay');
		$CI->load->model('goodsmodel');
		if	($_GET['userInfo'] && !$CI->userInfo)
			$CI->userInfo	= unserialize(base64_decode($_GET['userInfo']));

		$template_path = $CI->__tmp_template_path ? $CI->__tmp_template_path : $CI->template_path;

		// 디스플레이 임시 코드명
		$display_key = $CI->goodsdisplay->make_display_key();

		// 디스플레이 설정 데이터
		$display = $CI->goodsdisplay->get_display($display_seq);
		
		// 모바일전용 자동로딩형 일때
		if($display['platform']=='mobile' && $display['style']=='newmatrix'){
			$perpage = $display['count_w'] * $display['count_h'];
			$_GET['perpage'] = $perpage;
		}

		if($perpage){
			$perpage = $_GET['perpage'] ? $_GET['perpage'] : $perpage;
			$perpage = $perpage ? $perpage : 10;
			
			$perpage_min = $display['count_w']*$display['count_h'];
			if($perpage != $display['count_w']*$display['count_h']){
				$display['count_h'] = ceil($perpage/$display['count_w']);
			}

			/* 카테고리 정보 */
			if($_GET['category_code']){
				$CI->load->model('categorymodel');
				$CI->categoryData = $categoryData = $CI->categorymodel->get_category_data($code);
			}
		}else{
			$limit = $display['count_w']*$display['count_h'];
			$limit = $limit ? $limit : 4;
		}

		// 모바일전용 스와이프형 일때
		if($display['platform']=='mobile' && $display['style']=='newswipe'){
			$display['count_w'] = $display['count_w_swipe'];
			$display['count_h'] = $display['count_h_swipe'];
			$limit = $display['count_max_swipe'];
			$limit = $limit ? $limit : $display['count_w']*$display['count_h'];
		}

		if($display){
				
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
						$sc = $CI->goodsdisplay->search_condition($display_tab['auto_criteria'], $sc);
						
						$sc['sort']		= $perpage && !empty($_GET['sort']) ? $_GET['sort'] : $sc['auto_order'];
					}else{
						$sc['sort']		= $perpage && !empty($_GET['sort']) ? $_GET['sort'] : 'display';
					}

					$sc['admin_category']	= (defined('__ISADMIN__'))? true : false;
					$sc['display_seq']		= $display_seq;
					$sc['display_tab_index']= $CI->designDisplayTabAjaxIdx?$CI->designDisplayTabAjaxIdx:$tab_index;
					$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
					$sc['perpage']			= $perpage ? $perpage : 1000;
					if($perpage){
						$sc['category_code']	= !empty($_GET['category_code'])	? $_GET['category_code'] : '';
						$sc['brands']			= !empty($_GET['brands'])		? $_GET['brands'] : array();
						$sc['brand_code']		= !empty($_GET['brand_code'])	? $_GET['brand_code'] : '';
						$sc['search_text']		= !empty($_GET['search_text'])	? $_GET['search_text'] : '';
						$sc['old_search_text']	= !empty($_GET['old_search_text'])	? $_GET['old_search_text'] : '';
						$sc['start_price']		= !empty($_GET['start_price'])	? $_GET['start_price'] : '';
						$sc['end_price']		= !empty($_GET['end_price'])	? $_GET['end_price'] : '';
						$sc['color']			= !empty($_GET['color'])		? $_GET['color'] : '';		
					}
					$sc['image_size']		= $display['image_size'];
					$sc['limit']			= $limit;
					
					if($CI->goodsdisplay->info_settings_have_eventprice($display['info_settings'])){
						$sc['join_event']	= true;
					}

					$list = $CI->goodsmodel->goods_list($sc);

					$tabList[$CI->designDisplayTabAjaxIdx?$CI->designDisplayTabAjaxIdx:$tab_index] = $display_tab;
					$tabList[$CI->designDisplayTabAjaxIdx?$CI->designDisplayTabAjaxIdx:$tab_index]['record'] = $list['record'];
				}else{
					$tabList[$CI->designDisplayTabAjaxIdx?$CI->designDisplayTabAjaxIdx:$tab_index] = $display_tab;
				}
			}

			if( $display['kind']== 'designvideo') {
				$CI->load->model('videofiles');
				if($list['record']) {
					foreach($list['record'] as $k => $data) {
						if( $display['goods_video_type']== 'contents' ) {//
							unset($videosc);
							$videosc['tmpcode']= $data['videotmpcode'];
							$videosc['upkind']= 'goods';
							$videosc['type']= 'contents';
							$videoimage = $CI->videofiles->get_data($videosc);//debug_var($videoimage);
							if($videoimage) {
								$list['record'][$k]['file_key_w'] = $videoimage['file_key_w'];
								$list['record'][$k]['file_key_i'] = $videoimage['file_key_i'];
							}else{
								$list['record'][$k]['file_key_w'] = '';
								$list['record'][$k]['file_key_i'] = '';
							}

							//동영상
							if( $CI->session->userdata('setMode')=='mobile' && $list['record'][$k]['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
								$list['record'][$k]['uccdomain_thumbnail']	= uccdomain('thumbnail',$list['record'][$k]['file_key_i']);
								$list['record'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$list['record'][$k]['file_key_i']);
								$list['record'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$list['record'][$k]['file_key_i']);
								$list['record'][$k]['videosize_w'] = $videoimage['mobile_width'];
								$list['record'][$k]['videosize_h'] = $videoimage['mobile_height'];
							}elseif( uccdomain('thumbnail',$list['record'][$k]['file_key_w']) && $list['record'][$k]['file_key_w'] ) {
								$list['record'][$k]['uccdomain_thumbnail']	= uccdomain('thumbnail',$list['record'][$k]['file_key_w']);
								$list['record'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$list['record'][$k]['file_key_w']);
								$list['record'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$list['record'][$k]['file_key_w']);
								$list['record'][$k]['videosize_w'] = $videoimage['pc_width'];
								$list['record'][$k]['videosize_h'] = $videoimage['pc_height'];
							}
						}else{

							$videosc['tmpcode']= $data['videotmpcode'];
							$videosc['upkind']= 'goods';
							$videosc['type']= 'image';
							$videoimage = $CI->videofiles->get_data($videosc);//debug_var($videoimage);

							//동영상
							if( $CI->session->userdata('setMode')=='mobile' && $data['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
								$list['record'][$k]['uccdomain_thumbnail']	= uccdomain('thumbnail',$data['file_key_i']);
								$list['record'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_i']);
								$list['record'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_i']);
								$list['record'][$k]['videosize_w'] = $videoimage['mobile_width'];
								$list['record'][$k]['videosize_h'] = $videoimage['mobile_height'];
							}elseif( uccdomain('thumbnail',$data['file_key_w']) && $data['file_key_w'] ) {
								$list['record'][$k]['uccdomain_thumbnail']	= uccdomain('thumbnail',$data['file_key_w']);
								$list['record'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_w']);
								$list['record'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_w']);
								$list['record'][$k]['videosize_w'] = $videoimage['pc_width'];
								$list['record'][$k]['videosize_h'] = $videoimage['pc_height'];
							}
						}
					}
				}

				$tabList[0]['record'] = $list['record'];
			}
			
			
			if($perpage){
				
				$tmpGET = $_GET;
				unset($tmpGET['page']);
				unset($tmpGET['sort']);
				$sortUrlQuerystring = getLinkFilter('',array_keys($tmpGET));
				
				$sc['list_style']		= $_GET['display_style'] ? $_GET['display_style'] : $display['style'];

				$CI->template->assign(array(
					'categoryData'			=> $categoryData,
					'sortUrlQuerystring'	=> $sortUrlQuerystring,
					'sort'					=> $sc['sort'],
					'orders'				=> $CI->goodsdisplay->orders,
					'sc'					=> $sc,
					'perpage_min' 			=> $perpage_min,
					'list_style'			=> $sc['list_style'],
				));

				if($display['style']=='rolling_h'){
					$display['style'] = "lattice_a";
					if($display['count_w']<=2) $display['count_w'] = 4;
				}
				
				$CI->template->assign($list);
			}else{
				$sc['list_style'] = $display['style'];
				
				$CI->template->assign('perpage',null);
			}
					
			$displayAllGoodsList = array();
			foreach($tabList as $row) $displayAllGoodsList = array_merge($displayAllGoodsList,(array)$row['record']); 
			if(FACEBOOK_TAG_PRINTED!='YES' && strstr($display['info_settings'],"fblike") && ( !$CI->__APP_LIKE_TYPE__ || $CI->__APP_LIKE_TYPE__ == 'API') ) {//라이크포함시
				echo $CI->is_file_facebook_tag;
			}

			if($display['platform']=='mobile' && $sc['list_style']=='newswipe'){
				echo "<script type=\"text/javascript\" src=\"/app/javascript/plugin/custom-mobile-pagination.js\"></script>";
			}

			if(!$CI->designDisplayTabAjaxIdx) echo "<div id='{$display_key}' class='designDisplay' designElement='display' templatePath='{$template_path}' displaySeq='{$display_seq}' perpage='{$perpage}' displayStyle='{$sc['list_style']}'>";
			$CI->goodsdisplay->set('title',$display['title']);
			$CI->goodsdisplay->set('platform',$display['platform']);
			$CI->goodsdisplay->set('style',$sc['list_style']);
			$CI->goodsdisplay->set('perpage',$perpage);
			$CI->goodsdisplay->set('count_w',$display['count_w']);
			$CI->goodsdisplay->set('count_w_lattice_b',$display['count_w_lattice_b']);
			$CI->goodsdisplay->set('kind', $display['kind']);
			$CI->goodsdisplay->set('navigation_paging_style', $display['navigation_paging_style']);
			//if($display['kind'] == 'designvideo') {//동영상인경우
				$CI->goodsdisplay->set('goods_video_type',$display['goods_video_type']);
				$CI->goodsdisplay->set('videosize_w',$display['videosize_w']);
				$CI->goodsdisplay->set('videosize_h',$display['videosize_h']);
			//}

			if($perpage){
				$CI->goodsdisplay->set('count_h',ceil($perpage/$display['count_w']));
			}else{
				$CI->goodsdisplay->set('count_h',$display['count_h']);
			}
			$CI->goodsdisplay->set('image_decorations',$display['image_decorations']);
			$CI->goodsdisplay->set('image_size',$display['image_size']);
			$CI->goodsdisplay->set('text_align',$display['text_align']);
			$CI->goodsdisplay->set('info_settings',$display['info_settings']);
			$CI->goodsdisplay->set('display_key',$display_key);
			$CI->goodsdisplay->set('displayGoodsList',$displayAllGoodsList);
			$CI->goodsdisplay->set('displayTabsList',$tabList);
			$CI->goodsdisplay->set('APP_USE',$CI->__APP_USE__);
			$CI->goodsdisplay->set('tab_design_type',$display['tab_design_type']);
			$CI->goodsdisplay->print_();
			if(!$CI->designDisplayTabAjaxIdx) echo "</div>";
		}
	}else{
		$CI->load->model('goodsdisplay');
		$cachfile	= $CI->goodsdisplay->checkDesignDisplayCach($display_seq, (int)$CI->designDisplayTabAjaxIdx, $perpage, $kind);
		if	(!$cachfile)	$cachfile	= $CI->goodsdisplay->createDesignDisplayCach($display_seq, (int)$CI->designDisplayTabAjaxIdx, $perpage, $kind);

		include $cachfile;
	}

	return;
}
?>