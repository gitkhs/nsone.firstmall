<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class event_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('designmodel');
//		$this->load->model('goodssummarymodel');
	}

	public function regist(){
		$this->load->model('goodsdisplay');
		$this->load->model('eventmodel');

		$event_seq	= $_POST['event_seq'];
		$daily_event	= $_POST['daily_event'];
		$event_type		= ($_POST['event_type']) ? $_POST['event_type'] : 'multi';
		$tpl_path	= '';
		$start_time		= str_pad($_POST['start_hour'], 2, '0', STR_PAD_LEFT) . ':00:00';
		$end_time		= str_pad($_POST['end_hour'], 2, '0', STR_PAD_LEFT) . ':59:59';

		$app_start_min_time = ($event_type == 'solo') ? str_pad($_POST['app_start_minute'], 2, '0', STR_PAD_LEFT) : "00";
		$app_end_min_time = ($event_type == 'solo') ? str_pad($_POST['app_end_minute'], 2, '0', STR_PAD_LEFT) : "59";
		$app_start_time	= str_pad($_POST['app_start_hour'], 2, '0', STR_PAD_LEFT) . $app_start_min_time;
		$app_end_time	= str_pad($_POST['app_end_hour'], 2, '0', STR_PAD_LEFT) . $app_end_min_time;

		if	($_POST['week'])
			$app_week	= implode('', $_POST['week']);

		if		($event_type == 'solo'){
			// 단독 이벤트 상품 체크
			if	(count($_POST['choice_goods_1']) != 1){
				openDialogAlert("단독 이벤트는 1개의 상품을 선택해야 합니다.",400,150, 'parent','');
				exit;
			}

			// 단독 이벤트 중복 체크
			$param['event_seq']		= $event_seq;
			$param['start_date']	= $_POST['start_date'] . ' ' . $start_time;
			$param['end_date']		= $_POST['end_date'] . ' ' . $end_time;
			$param['goods_seq']		= $_POST['choice_goods_1'][0];
			if ($this->eventmodel->chk_solo_event_duple($param)){
				openDialogAlert("해당 상품의 다른 단독 이벤트와 기간이 중첩됩니다.",400,150, 'parent','');
				exit;
			}
		}

		$data = array();
		$data['title']		= $_POST['title'];
		$data['display']		= ($_POST['display'] == 'n') ? 'n' : 'y';
		$data['event_type']		= $event_type;
		$data['start_date']		= $_POST['start_date'] . ' ' . $start_time;
		$data['end_date']		= $_POST['end_date'] . ' ' . $end_time;
		$data['daily_event']	= (int)$daily_event;
		$data['app_start_time']	= '';
		$data['app_end_time']	= '';
		$data['app_week']		= '';
		if	($daily_event){
			$data['app_start_time']	= $app_start_time;
			$data['app_end_time']	= $app_end_time;
			$data['app_week']		= $app_week;
		}

		$data['update_date'] = date('Y-m-d H:i:s');
		$data['skin']		= $this->workingSkin;
		$data['goods_rule']	= $_POST['goods_rule'];
		$data['apply_goods_kind'] = $_POST['apply_goods_kind'];
		$data['goods_seq']		= ($event_type == 'solo') ? $_POST['choice_goods_1'][0] : '0';

		$title_contents	= adjustEditorImages($_POST['title_contents'], "/data/editor/");
		$data['title_contents']	= $title_contents;
		$data['bgcolor']	= $_POST['bgcolor'];


		if( $event_seq ){
			$query = $this->db->query("select * from fm_event where event_seq=?",$event_seq);
			$eventData = $query->row_array();
			$tpl_path = $eventData['tpl_path']?$eventData['tpl_path']:$this->_get_event_filepath();
			$data['tpl_path']	= $tpl_path;
			$this->db->where("event_seq",$event_seq);
			unset($data['event_type']);//2014-02-12이벤트분류 수정불가
			$result = $this->db->update("fm_event",$data);
		}else{
			if	($event_type == 'solo'){
				$st_num				= $_POST['event_st_num'][0] + 1;
				$data['st_num']		= $st_num;
			}
			$tpl_path = $this->_get_event_filepath();
			$data['tpl_path']	= $tpl_path;
			$data['regist_date'] = date('Y-m-d H:i:s');
			$result = $this->db->insert("fm_event",$data);
			$event_seq = $this->db->insert_id();

			// 디스플레이 생성
			$display = array();
			$display['image_size'] = 'list2';
			$display['count_w'] = '5';
			$display['count_h'] = '4';
			$display['info_settings'] = '[{"kind":"goods_name", "font_decoration":"{\"color\":\"#000000\", \"bold\":\"bold\", \"underline\":\"none\"}"},{"kind":"consumer_price", "font_decoration":"{\"color\":\"#999999\", \"bold\":\"normal\", \"underline\":\"line-through\"}", "postfix":"원"},{"kind":"price", "font_decoration":"{\"color\":\"#999999\", \"bold\":\"normal\", \"underline\":\"line-through\"}", "postfix":"원"},{"kind":"sale_price", "font_decoration":"{\"color\":\"#fb7c03\", \"bold\":\"bold\", \"underline\":\"none\"}", "postfix":"원"},{"kind":"color"}]';
			$display['admin_comment'] = $_POST['title'];
			$display['regdate'] = date('Y-m-d H:i:s');
			$display = filter_keys($display, $this->db->list_fields('fm_design_display'));
			$this->db->insert('fm_design_display', $display);
			$display_seq = $this->db->insert_id();

			$display_tab = array();
			$display_tab['display_seq'] = $display_seq;
			$display_tab['auto_use'] = 'y';
			$display_tab['auto_criteria'] = "selectEvent={$event_seq}";
			$display_tab = filter_keys($display_tab, $this->db->list_fields('fm_design_display_tab'));
			$this->db->insert('fm_design_display_tab ', $display_tab);

			$this->db->where("event_seq",$event_seq);
			$result = $this->db->update("fm_event",array('display_seq'=>$display_seq));
		}

		/* 이벤트 선정상품,카테고리/주문통계 저장 초기화 */
		$arr_delete = array('event_seq'=>$event_seq);
		$this->db->delete('fm_event_benefits',$arr_delete);
		$this->db->delete('fm_event_choice',$arr_delete);
		$this->db->delete('fm_event_order',$arr_delete);

		$insert_benefits['event_seq'] = $event_seq;
		$insert_benefits['regist_date'] = date('Y-m-d H:i:s');
		foreach($_POST['event_sale'] as $benefits_key => $event_sale){
			$benefits_num = $benefits_key + 1;
			$event_benefits_seq = $event_seq.'_'.$benefits_num;
			$insert_benefits['target_sale'] = (int) $_POST['target_sale'][$benefits_key];
			$insert_benefits['event_sale'] = (int) $event_sale;
			$insert_benefits['event_benefits_seq'] = $event_benefits_seq;

			$insert_benefits['event_reserve'] = (int) $_POST['event_reserve'][$benefits_key];
			$reserve_limit = "";
			if($_POST['reserve_select'][$benefits_key]){
				if($_POST['reserve_select'][$benefits_key]=="year"){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['reserve_year'][$benefits_key]));//$_POST['reserve_year'][$benefits_key]."-12-31";
				}else if($_POST['reserve_select'][$benefits_key]=="direct"){
					$reserve_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['reserve_direct'][$benefits_key], date("d"), date("Y")));
				}
			}
			$insert_benefits['reserve_limit']	= $reserve_limit;
			$insert_benefits['event_point']		= (int) $_POST['event_point'][$benefits_key];
			$point_limit = "";
			if($_POST['point_select'][$benefits_key]){
				if($_POST['point_select'][$benefits_key]=="year"){
					$point_limit = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['point_year'][$benefits_key]));//$_POST['point_year'][$benefits_key]."-12-31";
				}else if($_POST['point_select'][$benefits_key]=="direct"){
					$point_limit = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['point_direct'][$benefits_key], date("d"), date("Y")));
				}
			}
			$insert_benefits['point_limit']		= $point_limit;
			$insert_benefits['reserve_select']		= $_POST['reserve_select'][$benefits_key];
			$insert_benefits['reserve_year']		= $_POST['reserve_year'][$benefits_key];
			$insert_benefits['reserve_direct']		= $_POST['reserve_direct'][$benefits_key];
			$insert_benefits['point_select']		= $_POST['point_select'][$benefits_key];
			$insert_benefits['point_year']			= $_POST['point_year'][$benefits_key];
			$insert_benefits['point_direct']		= $_POST['point_direct'][$benefits_key];
			$this->db->insert("fm_event_benefits",$insert_benefits);

			unset($insert_choice);
			$insert_choice['event_seq'] = $event_seq;
			$insert_choice['event_benefits_seq'] = $event_benefits_seq;
			if( $_POST['category_code'][$benefits_key] ) foreach($_POST['category_code'][$benefits_key] as $category_code){
				$insert_choice['choice_type'] = 'category';
				$insert_choice['category_code'] = $category_code;
				$accept['category'][]			= $category_code;
				if(in_array($data['goods_rule'],array('category'))) $this->db->insert("fm_event_choice",$insert_choice);
			}

			unset($insert_choice);
			$insert_choice['event_seq'] = $event_seq;
			$insert_choice['event_benefits_seq'] = $event_benefits_seq;
			if( $_POST['except_category_code'][$benefits_key] ) foreach($_POST['except_category_code'][$benefits_key] as $category_code){
				$insert_choice['choice_type'] = 'except_category';
				$insert_choice['category_code'] = $category_code;
				$except['category'][]			= $category_code;
				if(in_array($data['goods_rule'],array('all','category'))) $this->db->insert("fm_event_choice",$insert_choice);
			}

			unset($insert_choice);
			$insert_choice['event_seq'] = $event_seq;
			$insert_choice['event_benefits_seq'] = $event_benefits_seq;
			if( $_POST['choice_goods_'.$benefits_num] ) foreach($_POST['choice_goods_'.$benefits_num] as $goods_seq){
				$total_choice_goods_seq[]		= $goods_seq;
				$insert_choice['choice_type'] = 'goods';
				$insert_choice['goods_seq'] = $goods_seq;
				$accept['goods'][]			= $goods_seq;
				if(in_array($data['goods_rule'],array('goods_view'))) $this->db->insert("fm_event_choice",$insert_choice);
			}

			unset($insert_choice);
			$insert_choice['event_seq'] = $event_seq;
			$insert_choice['event_benefits_seq'] = $event_benefits_seq;
			if( $_POST['except_goods_'.$benefits_num] ) foreach($_POST['except_goods_'.$benefits_num] as $goods_seq){
				$insert_choice['choice_type'] = 'except_goods';
				$insert_choice['goods_seq'] = $goods_seq;
				$except['goods'][]			= $goods_seq;
				if(in_array($data['goods_rule'],array('all','category'))) $this->db->insert("fm_event_choice",$insert_choice);
			}
		}

		/* 파일생성 : PC 작업용 스킨 */
		$this->load->helper('file');
		$this->load->helper('design');
		$saveData = array(
			'tpl_desc'		=> $data['title'],
			'tpl_page'		=> 1,
			'regist_date'	=> date('Y-m-d H:i:s'),
		);

		$skin_list = $this->designmodel->get_all_skin_list();
		foreach($skin_list as $skin_info){
			$fullpath = ROOTPATH.'data/skin/'.$skin_info['skin'].'/'.$tpl_path;
			if( $skin_info['skin'] && is_dir(ROOTPATH.'data/skin/'.$skin_info['skin'].'/') ) {
				if(!$tpl_path || ($tpl_path && !file_exists($fullpath))){
					if(write_file($fullpath, '')){
						if($display_seq) file_put_contents($fullpath,"{=showDesignDisplay({$display_seq})}");
						layout_config_save($skin_info['skin'],$tpl_path,$saveData);
					}
				}
				@chmod($fullpath,0777);
			}
		}

		// 해당 상품 단독 이벤트 차수 증가
		if	($event_type == 'solo' && $goods_seq && $st_num > 0){
			$this->eventmodel->update_solo_event_stnum($goods_seq, $st_num);
			$accept	= array($goods_seq);
			$except	= array();
		}

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

//		$this->goodssummarymodel->set_event_price($accept, $except);

		if($result){
			$callback = "parent.document.location = '/admin/event/catalog';";
			openDialogAlert("이벤트가 저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	public function event_delete(){
		$event_seq = $_GET['event_seq'];

		$query = $this->db->query("select * from fm_event where event_seq=?",$event_seq);
		$data = $query->row_array();

		//$query = $this->db->query("delete from fm_event where event_seq=?",$event_seq);
		//$query = $this->db->query("delete from fm_event_benefits where event_seq=?",$event_seq);
		//$query = $this->db->query("delete from fm_event_choice where event_seq=?",$event_seq);

		$arr_delete = array('event_seq'=>$event_seq);
		$this->db->delete('fm_event',$arr_delete);
		$this->db->delete('fm_event_benefits',$arr_delete);
		$this->db->delete('fm_event_choice',$arr_delete);
		$this->db->delete('fm_event_order',$arr_delete);
		
		$skin_list = $this->designmodel->get_all_skin_list();
		foreach($skin_list as $skin_info){
			$fullpath = ROOTPATH.'data/skin/'.$skin_info['skin'].'/'.$data['tpl_path'];
			if(file_exists($fullpath)){
				@unlink($fullpath);
			}
		}

		// 디스플레이 캐시 삭제
		$this->load->model('goodsdisplay');
		$this->goodsdisplay->delete_display_cach();

//		$this->goodssummarymodel->set_event_price();

		$callback = "parent.document.location.reload();";
		openDialogAlert("이벤트가 삭제 되었습니다.",400,140,'parent',$callback);
	}

	public function gift_delete(){
		$event_seq = $_GET['event_seq'];

		$query = $this->db->query("select * from fm_gift where gift_seq=?",$event_seq);
		$data = $query->row_array();

		$result = $this->db->query("delete from fm_gift where gift_seq=?",$event_seq);
		$result = $this->db->query("delete from fm_gift_choice where gift_seq=?",$event_seq);
		
		$skin_list = $this->designmodel->get_all_skin_list();
		foreach($skin_list as $skin_info){
			$fullpath = ROOTPATH.'data/skin/'.$skin_info['skin'].'/'.$data['tpl_path'];
			if(file_exists($fullpath)){
				@unlink($fullpath);
			}
		}
	}

	public function event_copy(){
		$event_seq = $_GET['event_seq'];

		$query = $this->db->query("select * from fm_event where event_seq=?",$event_seq);
		$data = $query->row_array();

		unset($data['event_seq']);
		unset($data['pageview']);

		$tpl_path = $this->_get_event_filepath();

		$skin_list = $this->designmodel->get_all_skin_list();
		foreach($skin_list as $skin_info){
			$ori_fullpath = ROOTPATH.'data/skin/'.$skin_info['skin'].'/'.$data['tpl_path'];
			if(file_exists($ori_fullpath)){
				$fullpath = ROOTPATH.'data/skin/'.$skin_info['skin'].'/'.$tpl_path;

				if(!is_dir(dirname($fullpath)))
				{
					@mkdir(dirname($fullpath));
					@chmod(dirname($fullpath),0777);
				}

				@copy($ori_fullpath,$fullpath);
				@chmod($fullpath,0707);
			}
		}

		if	($data['event_type'] == 'solo'){
			$soloquery	= $this->db->query("select max(st_num) as st_num from fm_event where goods_seq=?",$data['goods_seq']);
			$maxd	= $soloquery->row_array();

			$data['st_num']	= $maxd['st_num'] + 1;
			$this->db->query("update fm_goods set event_st_num = '".$data['st_num']."' where goods_seq=?",$data['goods_seq']);
			/**
			$query	= $this->db->query("select * from fm_event where goods_seq='".$data['goods_seq']."' order by end_date desc limit 1");
			$dates	= $query->row_array();
			// 이벤트 일자 중복을 막기 위해 종료일 하루 뒤에서 시작하게.
			$start_time			= strtotime($data['start_date']);
			$end_time			= strtotime($data['end_date']);
			$final_time			= strtotime($dates['end_date']);
			$start_datetime		= strtotime(date('Y-m-d', $start_time));
			$end_datetime		= strtotime(date('Y-m-d', $end_time));
			$final_datetime		= strtotime(date('Y-m-d', $final_time));
			$diff_time			= $end_datetime - $start_datetime;
			$final_diff_time	= $final_datetime - $end_datetime;
			$new_start_time		= strtotime('+1 day', $start_time+$diff_time+$final_diff_time);
			$new_end_time		= strtotime('+1 day', $end_time+$diff_time+$final_diff_time);
			$data['start_date']	= date('Y-m-d H:i:s', $new_start_time);
			$data['end_date']	= date('Y-m-d H:i:s', $new_end_time);
			**/

			$data['start_date']	= "";
			$data['end_date']	= "";
		}

		if($tpl_path) $data['tpl_path'] = $tpl_path;
		$data['regist_date'] = date('Y-m-d H:i:s');
		$data['update_date'] = date('Y-m-d H:i:s');

		$result = $this->db->insert("fm_event",$data);
		$new_event_seq	= $this->db->insert_id();

		$query	= $this->db->query("select * from fm_event_benefits where event_seq=?",$event_seq);
		$data	= $query->result_array();

		if	($data){
			foreach($data as $k => $row){
				$event_benefits_seq_old = $row['event_benefits_seq'];
				unset($row);
				$benefits					= explode('_', $event_benefits_seq_old);
				$row['event_benefits_seq']	= $new_event_seq . '_' . $benefits[1];
				$row['event_seq']			= $new_event_seq;
				$row['regist_date']			= date('Y-m-d H:i:s');

				$this->db->insert("fm_event_benefits",$row);
			}
		}

		$query	= $this->db->query("select * from fm_event_choice where event_seq=?",$event_seq);
		$data	= $query->result_array();
		if	($data){
			foreach($data as $k => $row){
				unset($row['event_choice_seq']);

				$benefits					= explode('_', $row['event_benefits_seq']);
				$row['event_benefits_seq']	= $new_event_seq . '_' . $benefits[1];
				$row['event_seq']			= $new_event_seq;

				$this->db->insert("fm_event_choice",$row);
			}
		}


		$callback = "parent.location.reload();";
		openDialogAlert('복사되었습니다.<br/>정보를 수정해 주세요!',400,180,'parent',$callback);
	}

	/* 새 페이지명 생성 */
	public function _get_event_filepath($filename="event"){
		$this->load->model('designmodel');
		
		$filenamePrefix = $filename.date('ym');
		$filepath		= "";

		if(!is_dir($eventPath))
		{
			@mkdir($eventPath);
			@chmod($eventPath,0777);
		}

		$skin_list = $this->designmodel->get_all_skin_list();

		for($i=1;$i<1000;$i++){
			$num = sprintf("%03d",$i);

			$exists = false;
			foreach($skin_list as $skin_info){
				$eventPath	= ROOTPATH.'data/skin/'.$skin_info['skin'].'/etc/';
				$filepath	= $eventPath.$filenamePrefix.$num.".html";
				if(file_exists($filepath)) $exists = true;
			}

			if(!$exists) return 'etc/'.$filenamePrefix.$num.".html";
		}
	}


	public function gift_regist(){
		$event_seq	= $_POST['gift_seq'];
		$tpl_path	= '';

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('gift_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		### Validation
		$this->validation->set_rules('title', '사은품 이벤트 명','trim|required|max_length[40]|xss_clean');
		$this->validation->set_rules('start_date', '사은품 이벤트 기간','trim|required|max_length[10]|xss_clean');
		$this->validation->set_rules('end_date', '사은품 이벤트 기간','trim|required|max_length[10]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['gift_rule']=='price'){
			for($i=0;$i<count($_POST['sprice2']);$i++){
				if($_POST['sprice2'][$i]>0 && isset($_POST['eprice2'][$i]) && !($_POST['eprice2'][$i]>$_POST['sprice2'][$i])){
					$callback = "parent.$(\"input[name='eprice2[]']\").eq({$i}).focus()";
					openDialogAlert("기준 상한금액을 입력해주세요",400,165,'parent',$callback);
					exit;
				}
			}
		}else if($_POST['gift_rule']=='quantity'){
			for($i=0;$i<count($_POST['sprice3']);$i++){
				if($_POST['sprice3'][$i]>0 && isset($_POST['eprice3'][$i]) && !($_POST['eprice3'][$i]>$_POST['sprice3'][$i])){
					$callback = "parent.$(\"input[name='eprice3[]']\").eq({$i}).focus()";
					openDialogAlert("기준 상한금액을 입력해주세요",400,165,'parent',$callback);
					exit;
				}
			}
		}

		$data = array();
		$data['title']			= $_POST['title'];
		$data['start_date']		= $_POST['start_date'];
		$data['end_date']		= $_POST['end_date'];
		$data['display']		= $_POST['display'];
		$data['update_date']	= date('Y-m-d H:i:s');
		$data['skin']			= $this->workingSkin;
		$data['goods_rule']		= $_POST['goods_rule'];
		$data['gift_rule']		= $_POST['gift_rule'];
		//$data['gift_contents']	= $_POST['gift_contents'];
		$gift_contents	= adjustEditorImages($_POST['gift_contents'], "/data/editor/");
		$data['gift_contents']	= $gift_contents;

		$data['gift_gb']		= $_POST['gift_gb'];

		if($event_seq){
			$query = $this->db->query("select * from fm_gift where gift_seq=?",$event_seq);
			$eventData = $query->row_array();
			$tpl_path = $eventData['tpl_path']?$eventData['tpl_path']:$this->_get_event_filepath("gift");
			$data['tpl_path']	= $tpl_path;
			$this->db->where("gift_seq",$event_seq);
			$result = $this->db->update("fm_gift",$data);
		}else{
			$tpl_path = $this->_get_event_filepath("gift");
			$data['tpl_path']	= $tpl_path;
			$data['regist_date'] = date('Y-m-d H:i:s');
			$result = $this->db->insert("fm_gift",$data);
			$event_seq = $this->db->insert_id();

			// 디스플레이 생성
			$display = array();
			$display['image_size'] = 'list2';
			$display['count_w'] = '5';
			$display['count_h'] = '4';
			$display['info_settings'] = '[{"kind":"goods_name", "font_decoration":"{\"color\":\"#000000\", \"bold\":\"bold\", \"underline\":\"none\"}"},{"kind":"consumer_price", "font_decoration":"{\"color\":\"#999999\", \"bold\":\"normal\", \"underline\":\"line-through\"}", "postfix":"원"},{"kind":"price", "font_decoration":"{\"color\":\"#999999\", \"bold\":\"normal\", \"underline\":\"line-through\"}", "postfix":"원"},{"kind":"sale_price", "font_decoration":"{\"color\":\"#fb7c03\", \"bold\":\"bold\", \"underline\":\"none\"}", "postfix":"원"},{"kind":"color"}]';
			$display['admin_comment'] = $_POST['title'];
			$display['regdate'] = date('Y-m-d H:i:s');
			$display = filter_keys($display, $this->db->list_fields('fm_design_display'));
			$this->db->insert('fm_design_display', $display);
			$display_seq = $this->db->insert_id();

			$display_tab = array();
			$display_tab['display_seq'] = $display_seq;
			$display_tab['auto_use'] = 'y';
			$display_tab['auto_criteria'] = "selectGift={$event_seq}";
			$display_tab = filter_keys($display_tab, $this->db->list_fields('fm_design_display_tab'));
			$this->db->insert('fm_design_display_tab ', $display_tab);

			$this->db->where("event_seq",$event_seq);
			$result = $this->db->update("fm_event",array('display_seq'=>$display_seq));
		}

		/* 구매 대상 상품 */
		$arr_delete = array('gift_seq'=>$event_seq);
		$this->db->delete('fm_gift_choice',$arr_delete);
		if($_POST['goods_rule']=='goods'){
			$issueGoods = array_unique($_POST['issueGoods']);
			for($i=0;$i<count($issueGoods);$i++){
				if($issueGoods[$i])
				$result = $this->db->insert('fm_gift_choice', array('gift_seq'=>$event_seq,'goods_seq'=>$issueGoods[$i],'choice_type'=>'goods'));
			}
		}else if($_POST['goods_rule']=='category'){
			for($i=0;$i<count($_POST['issueCategoryCode']);$i++){
				$result = $this->db->insert('fm_gift_choice', array('gift_seq'=>$event_seq,'category_code'=>$_POST['issueCategoryCode'][$i],'choice_type'=>'category'));
			}
		}

		### 사은품 증정 방식
		$arr_delete = array('gift_seq'=>$event_seq);
		$this->db->delete('fm_gift_benefit',$arr_delete);
		if($_POST['gift_rule']=='default'){
			$arr = array_unique($_POST['defaultGift']);
			$gift_goods_seq = implode("|",$arr);
			$iparams['gift_seq']		= $event_seq;
			$iparams['benefit_rule']	= $_POST['gift_rule'];
			$iparams['sprice']			= $_POST['sprice1'][0];
			$iparams['ea']				= 1;
			$iparams['gift_goods_seq']	= $gift_goods_seq;
			$result = $this->db->insert('fm_gift_benefit', $iparams);
		}else if($_POST['gift_rule']=='price'){
			$iparams['gift_seq']		= $event_seq;
			$iparams['benefit_rule']	= $_POST['gift_rule'];
			for($i=0;$i<count($_POST['sprice2']);$i++){
				$iparams['sprice']			= $_POST['sprice2'][$i];
				$iparams['eprice']			= $_POST['eprice2'][$i];
				$iparams['ea']				= 1;
				$id = "priceGift".($i+1);
				if (!isset($_POST[$id])) {
					$no = $i+1;
					$s_msg = "{$no} 번째 사은품을 선택해주세요.";
					openDialogAlert($s_msg,400,150, 'parent','');
					exit;
				}
				$arr = array_unique($_POST[$id]);
				$gift_goods_seq = implode("|",$arr);
				$iparams['gift_goods_seq']	= $gift_goods_seq;
				$result = $this->db->insert('fm_gift_benefit', $iparams);
			}
			$iparams['gift_goods_seq']	= $gift_goods_seq;
		}else if($_POST['gift_rule']=='quantity'){
			$arr = array_unique($_POST['qtyGift']);
			$gift_goods_seq = implode("|",$arr);
			$iparams['gift_seq']		= $event_seq;
			$iparams['benefit_rule']	= $_POST['gift_rule'];
			$iparams['gift_goods_seq']	= $gift_goods_seq;
			for($i=0;$i<count($_POST['sprice3']);$i++){
				$iparams['sprice']			= $_POST['sprice3'][$i];
				$iparams['eprice']			= $_POST['eprice3'][$i];
				$iparams['ea']				= $_POST['ea3'][$i];
				$result = $this->db->insert('fm_gift_benefit', $iparams);
			}
		}else if($_POST['gift_rule']=='lot'){
			$arr = array_unique($_POST['lotGift']);
			$gift_goods_seq = implode("|",$arr);
			$iparams['gift_seq']		= $event_seq;
			$iparams['benefit_rule']	= $_POST['gift_rule'];
			$iparams['gift_goods_seq']	= $gift_goods_seq;
			$iparams['sprice']			= $_POST['sprice1'][0];
			$iparams['ea']				= 1;
			$result = $this->db->insert('fm_gift_benefit', $iparams);
		}


		if($data['gift_gb']=="buy"){
			$today = date("Y-m-d");
			if($_POST['display'] == 'y' && $_POST['start_date'] <= $today && $_POST['end_date'] >= $today){
				//$qry = "update fm_gift set display = 'n' where gift_gb = 'buy'";
				//$this->db->query($qry);
				$qry = "update fm_gift set display = 'y' where gift_seq = '{$event_seq}'";
				$this->db->query($qry);
			}
		}



		/* 파일생성 */
		$this->load->helper('file');
		$this->load->helper('design');
		$skin_list = $this->designmodel->get_all_skin_list();

		$saveData = array(
			'tpl_desc'		=> $data['title'],
			'tpl_page'		=> 1,
			'regist_date'	=> date('Y-m-d H:i:s'),
		);
		foreach($skin_list as $skin_info){
			$fullpath = ROOTPATH.'data/skin/'.$skin_info['skin'].'/'.$tpl_path;
			if( $skin_info['skin'] && is_dir(ROOTPATH.'data/skin/'.$skin_info['skin'].'/') ) {
				if(!$tpl_path || ($tpl_path && !file_exists($fullpath))){
					if(write_file($fullpath, '')){
						layout_config_save($skin_info['skin'],$tpl_path,$saveData);
						$fp= fopen($fullpath,'w');
						$print = $this->gift_page($_POST['gift_gb']);
						foreach($print as $k){
							fwrite($fp, $k);
							fwrite($fp, "\n");
						}

						if($display_seq) fwrite($fp,"{=showDesignDisplay({$display_seq})}\n");
						fclose($fp);

						@chmod($fullpath,0707);
					}
				}
			}
		}

		if($result){
			$callback = "parent.document.location = '/admin/event/gift_catalog';";
			openDialogAlert("이벤트가 저장 되었습니다.",400,140,'parent',$callback);
		}
	}


	public function gift_cont(){
		$gift_seq	= $_GET['seq'];
		$query = $this->db->query("select * from fm_gift where gift_seq=?",$gift_seq);
		$data = $query->row_array();

		if($data['gift_gb'] != "order"){
			$qry = "update fm_gift set display = 'n' where gift_gb = '{$data['gift_gb']}'";
			//$this->db->query($qry);
		}

		$qry = "update fm_gift set display = 'y' where gift_seq = '{$gift_seq}'";
		$this->db->query($qry);

		$callback = "parent.document.location.reload();";
		openDialogAlert("처리 되었습니다.",400,140,'parent',$callback);
	}

	public function gift_page($gift_gb){
		$html[] = "";
		$html[] = "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
		$html[] = "<tr>";
		if($gift_gb != "order"){
		$html[] = "	<td align='center'><img src='../images/common/gift_top.gif'></td>";
		}else{
		$html[] = "	<td align='center'><img src='../images/common/gift_order_top.gif'></td>";
		}
		$html[] = "</tr>";
		$html[] = "</table>";
		$html[] = "<div style='padding:25px;'></div>";
		$html[] = "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
		$html[] = "<tr>";
		$html[] = "	<td width='33%' align='center'><img src='../images/common/thumb_gift.gif'></td>";
		$html[] = "	<td width='33%' align='center'><img src='../images/common/thumb_gift.gif'></td>";
		$html[] = "	<td width='33%' align='center'><img src='../images/common/thumb_gift.gif'></td>";
		$html[] = "	</tr>";
		$html[] = "	<tr><td colspan='3' height='20'></td></tr>";
		$html[] = "	<tr>";
		$html[] = "		<td width='33%' align='center'><img src='../images/common/gift_icon_a.gif'> 사은품명</td>";
		$html[] = "		<td width='33%' align='center'><img src='../images/common/gift_icon_b.gif'> 사은품명</td>";
		$html[] = "		<td width='33%' align='center'><img src='../images/common/gift_icon_c.gif'> 사은품명</td>";
		$html[] = "	</tr>";
		$html[] = "	</table>";
		$html[] = "	<div style='padding:30px;'></div>";
		$html[] = "	<div style='width:100%;text-align:center;'><div style='border:2px solid #d9d9d9;background:#f7f7f7;width:900px;'></div></div>";
		$html[] = "	<div style='padding:25px;'></div>";
		$html[] = "	<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
		$html[] = "	<tr>";
		$html[] = "		<td align='center'><img src='../images/common/gift_items_tit.gif'></td>";
		$html[] = "	</tr>";
		$html[] = "	</table>";
		$html[] = "	<div style='padding:30px;'></div>	";
		return $html;
	}
}