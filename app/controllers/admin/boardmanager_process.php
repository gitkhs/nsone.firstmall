<?php
/**
 * 게시판 관련 관리자 process
 * @author gabia
 * @since version 2.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class Boardmanager_process extends admin_base {

	public function __construct() {
		parent::__construct();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('board_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->load->helper('board');//
		$this->load->library('validation');
		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$this->load->model('Boardscorelog');
	}

	/* 기본 */
	public function index()
	{
		$mode = (isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
		/* 게시판생성 */
		if($mode == 'boardmanager_write') {


			$this->load->model('usedmodel');
			$result = $this->usedmodel->used_service_check('board');
			if(!$result['type']){
				$html = '<table width="100%"><tr><td align="left">무료몰+ : 기본 5개 (게시판 추가 시 1개당 2,200원, 최초 1회 결제로 기간 관계 없이 계속 이용)<br />프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 게시판을 무제한 이용 가능합니다.</td></tr><tr><td align="center"><br /><br /><span class="btn large gray"><input type="button" onclick="serviceBoardAdd();" value="추가 신청 >"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="btn large gray"><input type="button" onclick="serviceUpgrade();" value="업그레이드 >"></span></td></tr></table>';
				openDialogAlert($html,600,210,'parent','');
				exit;
			}

			### Validation
			if( $_POST['board_id'] == '영문, 숫자, 언더스코어(_), 하이픈(-) 가능' ) {
				$_POST['board_id'] = '';
			}

			if( $_POST['board_name'] == '영문,한글, 숫자, 언더스코어(_), 하이픈(-) 가능' ) {
				$_POST['board_name'] = '';
			}


			$this->validation->set_rules('board_id', '게시판 아이디','trim|required|max_length[32]|xss_clean');
			$this->validation->set_rules('board_name', '게시판명','trim|required|max_length[32]|xss_clean');

			if ( strstr( $_POST['skin'][0], 'gallery') ) {
				$_POST['gallerycell'][0]						=  ($_POST['gallerycell'][0]>0)?$_POST['gallerycell'][0]:'';
				$_POST['gallerycell'][1]						=  ($_POST['gallerycell'][1]>0)?$_POST['gallerycell'][1]:'';
				$this->validation->set_rules('gallerycell[0]', '페이지당 노출 수','trim|required|numeric|xss_clean');
				$this->validation->set_rules('gallerycell[1]', '페이지당 노출 수','trim|required|numeric|xss_clean');

				$_POST['gallery_list_w']						=  ($_POST['gallery_list_w']>0)?$_POST['gallery_list_w']:'';
				$_POST['gallery_list_h']						=  ($_POST['gallery_list_h']>0)?$_POST['gallery_list_h']:'';
				$this->validation->set_rules('gallery_list_w', '리스트 이미지 가로사이즈','trim|required|numeric|xss_clean');
				$this->validation->set_rules('gallery_list_h', '리스트 이미지 세로사이즈','trim|required|numeric|xss_clean');

			}else{
				$_POST['pagenum']						=  ($_POST['pagenum']>0)?$_POST['pagenum']:'';
				$this->validation->set_rules('pagenum', '페이지당 노출 수','trim|required|numeric|xss_clean');
			}

			//읽기권한 및 접근권한
			if ($_POST['auth_read'][0] == 'member') {
				if(!is_array($_POST['auth_read_group'])){
					$this->validation->set_rules('auth_read_group', '읽기권한시 [회원그룹]','trim|required|xss_clean');
				}
			}

			if ($_POST['auth_write'][0] == 'member') {
				if(!is_array($_POST['auth_write_group'])){
					$this->validation->set_rules('auth_write_group', '작성권한-쓰기 [회원그룹]','trim|required|xss_clean');
				}
			}

			if ($_POST['auth_reply'][0] == 'member') {
				if(!is_array($_POST['auth_reply_group'])){
					$this->validation->set_rules('auth_reply_group', '작성권한-답글 [회원그룹]','trim|required|xss_clean');
				}
			}

			if ($_POST['auth_cmt'][0] == 'member') {
				if(!is_array($_POST['auth_cmt_group'])){
					$this->validation->set_rules('auth_cmt_group', '작성권한-댓글 [회원그룹]','trim|required|xss_clean');
				}
			}

			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}

			/* 게시판 아이디 체크 추가 leewh 2014-08-20 */
			$arr_idck = array('store_reservation', 'store_review');
			if (in_array($_POST['board_id'], $arr_idck)) {
				$callback = "if(parent.document.getElementsByName('board_id')[0]) parent.document.getElementsByName('board_id')[0].focus();";
				openDialogAlert("생성할 수 없는 게시판 아이디 입니다.<br/>아이디를 다시 입력해 주세요.",400,150,'parent',$callback);
				exit;
			}

			$params['id']						=  $_POST['board_id'];
			$params['name']					=  $_POST['board_name'];
			$params['auth_read_use']	=  $_POST['auth_read_use'];
			$params['auth_write_use']	=  $_POST['auth_write_use'];
			$params['auth_reply_use']	=  $_POST['auth_reply_use'];
			$params['auth_cmt_use']	=  $_POST['auth_cmt_use'];

			$params['autowrite_use']	=  $_POST['autowrite_use'];
			$params['file_use']				=  $_POST['file_use'];
			$params['onlyimage_use']	=  $_POST['onlyimage_use'];
			$params['video_use']			=  $_POST['video_use'];
			$params['video_type']			=  ($_POST['video_type'])?$_POST['video_type']:'400';
			$params['video_screen'] = (is_array($_POST['video_screen'])) ? @implode("X",($_POST['video_screen'])):'400X300';
			$params['video_size'] = (is_array($_POST['video_size'])) ? @implode("X",($_POST['video_size'])):'';
			$params['video_size_mobile'] = (is_array($_POST['video_size_mobile'])) ? @implode("X",($_POST['video_size_mobile'])):'';//화면크기

			$params['file_type']				=  (!empty($_POST['file_type']))?$_POST['file_type']:'';
			$params['secret_use']			=  $_POST['secret_use'];

			$params['write_show']		=  $_POST['write_show'];
			$params['show_name_type']		=  $_POST['show_name_type'];
			$params['show_grade_type']		=  $_POST['show_grade_type'];

			$params['content_default']		=  $_POST['content_default'];//기본내용
			$params['content_default_mobile']		=  $_POST['content_default_mobile'];//모바일-기본내용

			$params['pagenum']			=  ( strstr( $_POST['skin'][0], 'gallery') ) ? $_POST['gallerycell'][0]*$_POST['gallerycell'][1]:$_POST['pagenum'];
			$params['list_show']			=  $_POST['list_show'];

			$params['icon_new_day']	=  $_POST['icon_new_day'];
			$params['icon_hot_visit']		=  $_POST['icon_hot_visit'];
			$params['goods_num']		=  $_POST['goods_num'];
			$params['goods_review_type'] =  $_POST['goods_review_type'];
			$params['subjectcut']			=  ($_POST['subjectcut']>0)?$_POST['subjectcut']:30;
			$params['contcut']				=  ($_POST['contcut']>0)?$_POST['contcut']:200;

			$params['gallery_list_w']		=  ($_POST['gallery_list_w']>0)?$_POST['gallery_list_w']:250;
			$params['gallery_list_h']		=  ($_POST['gallery_list_h']>0)?$_POST['gallery_list_h']:250;

			$params['skin'] =  $_POST['skin'][0];

			//읽기권한 및 접근권한
			if ($_POST['auth_read'][0] == 'member') {
				$params['auth_read'] = '[member]';
				foreach($_POST['auth_read_group'] as $groupid=>$groupval){
					$params['auth_read'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_read'] = '['.$_POST['auth_read'][0].']';//관리자전용 또는 전체이용가능시
			}

			if ($_POST['auth_write'][0] == 'member') {
				$params['auth_write'] = '[member]';
				foreach($_POST['auth_write_group'] as $groupid=>$groupval){
					$params['auth_write'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_write'] = '['.$_POST['auth_write'][0].']';//관리자전용 또는 전체이용가능시
			}

			if ($_POST['auth_reply'][0] == 'member') {
				$params['auth_reply'] = '[member]';
				foreach($_POST['auth_reply_group'] as $groupid=>$groupval){
					$params['auth_reply'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_reply'] = '['.$_POST['auth_reply'][0].']';//관리자전용 또는 전체이용가능시
			}

			if ($_POST['auth_cmt'][0] == 'member') {
				$params['auth_cmt'] = '[member]';
				foreach($_POST['auth_cmt_group'] as $groupid=>$groupval){
					$params['auth_cmt'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_cmt'] = '['.$_POST['auth_cmt'][0].']';//관리자전용 또는 전체이용가능시
			}

			$params['category'] = (is_array($_POST['category'])) ? @implode(",",($_POST['category'])):'';//카테고리 콤마로 구분
			$params['category'] = htmlspecialchars($params['category']);
			$params['list_show'] = (is_array($params['list_show'])) ? '[subject]'.@implode("",$params['list_show']):'[subject]';//리스트 표시항목 콤마로 구분
			if( $_POST['board_id'] == 'goods_qna' ||  $_POST['board_id'] == 'goods_review' ) $params['list_show'] .= '[images]';

			$params['gallerycell'] = (is_array($_POST['gallerycell'])) ? @implode("X",($_POST['gallerycell'])):'';
			$params['write_admin'] = if_empty($_POST, 'write_admin', '관리자');
			$params['write_admin_type'] =  $_POST['write_admin_type'];
			
			
			/**
			* 게시판아이콘설정
			* 관리자아이콘 : admin 
			* new 
			* hot
			* review
			* 게시글평가 : recommend none_rec recommend1~recommend5
			* 댓글평가 : cmt_recommend, cmt_none_rec
			**/
			 $iconarray = array("admin","new","hot","review","recommend","none_rec","recommend1","recommend2","recommend3","recommend4","recommend5","cmt_recommend","cmt_none_rec");
			 foreach($iconarray as $icontype){
				 if(isset($_POST['real_icon_name_'.$icontype])){
					if($_POST['real_icon_name_'.$icontype] && is_file($this->Boardmanager->board_tmp_dir.$_POST['real_icon_name_'.$icontype]) ) {
						@copy($this->Boardmanager->board_tmp_dir.$_POST['real_icon_name_'.$icontype],$this->Boardmanager->board_data_dir.$_POST['board_id'].'_'.$icontype.'.'.$_POST['real_icon_ext_'.$icontype]);
						@chmod($this->Boardmanager->board_data_dir.$_POST['board_id'].'_'.$icontype.'.'.$_POST['real_icon_ext_'.$icontype], 0777);
						unlink($this->Boardmanager->board_tmp_dir.$_POST['real_icon_name_'.$icontype]);
						$params['icon_'.$icontype.'_img'] = $_POST['board_id'].'_'.$icontype.'.'.$_POST['real_icon_ext_'.$icontype];
					}
				}
			 } 
			$sms_arr = array($_POST['board_id']."_write",$_POST['board_id']."_reply");
			$smsboard_arr = array("BOARDID_write","BOARDID_reply");
			for($i=0;$i<count($sms_arr);$i++){
				$user_id = $sms_arr[$i]."_user";
				$user_chk = $sms_arr[$i]."_user_yn";
				$userboard_id = $smsboard_arr[$i]."_user";
				$userboard_chk = $smsboard_arr[$i]."_user_yn";
				if($i!=2){
					if(if_empty($_POST, $userboard_chk, 'N') == 'Y' ) {
						$this->validation->set_rules($userboard_id, '내용','trim|required|xss_clean');
					}
					if($this->validation->exec()===false){
						$err = $this->validation->error_array;
						$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
						openDialogAlert('SMS 메시지를 입력해 주세요.',400,140,'parent',$callback);
						exit;
					}
				}

				config_save('sms',array($user_id=>$_POST[$userboard_id]));
				$admin_id = $sms_arr[$i]."_admin";
				$adminboard_id = $smsboard_arr[$i]."_admin";
				config_save('sms',array($admin_id=>$_POST[$adminboard_id]));
				config_save('sms',array($user_chk=>if_empty($_POST, $userboard_chk, 'N')));
				##
				$sms_info = config_load('sms_info');
				$cnt = $sms_info['admis_cnt'];
				for($j=0;$j<$cnt+1;$j++){
					$admins_chk = $sms_arr[$i]."_admins_yn_".$j;
					$adminsboard_chk = $smsboard_arr[$i]."_admins_yn_".$j;
					config_save('sms',array($admins_chk=>if_empty($_POST, $adminsboard_chk, 'N')));
				}
			}
			
			if ( $_POST['writer_date_regit'] && $_POST['writer_date_login']){
				$params['writer_date'] = 'all';
			}elseif ( $_POST['writer_date_regit'] || $_POST['writer_date_login']){
				$params['writer_date'] = ($_POST['writer_date_regit'])?$_POST['writer_date_regit']:$_POST['writer_date_login'];
			}else{
				$params['writer_date'] =  if_empty($_POST, 'writer_date', 'none');
			}
			$params['recommend_type'] =  if_empty($_POST, 'recommend_type', '1');
			$params['cmt_recommend_type'] =  if_empty($_POST, 'cmt_recommend_type', '1');
			$params['auth_recommend_use'] =  if_empty($_POST, 'auth_recommend_use', 'N');
			$params['auth_cmt_recommend_use'] =  if_empty($_POST, 'auth_cmt_recommend_use', 'N');
			$params['recommend_icon_file'] =  $_POST['real_icon_name_recommend'];
			$params['none_rec_icon_file'] =  $_POST['real_icon_name_none_rec'];
			$params['recommend_good1_icon_file'] =  $_POST['real_icon_name_recommend_good1'];
			$params['recommend_good2_icon_file'] =  $_POST['real_icon_name_recommend_good2'];
			$params['recommend_good3_icon_file'] =  $_POST['real_icon_name_recommend_good3'];
			$params['recommend_good4_icon_file'] =  $_POST['real_icon_name_recommend_good4'];
			$params['recommend_good5_icon_file'] =  $_POST['real_icon_name_recommend_good5'];
			$params['cmt_recommend_icon_file'] =  $_POST['real_icon_name_cmt_recommend'];
			$params['cmt_none_rec_icon_file'] =  $_POST['real_icon_name_cmt_none_rec_icon_file']; 

			$params['r_date'] = date("Y-m-d H:i:s");
			$params['m_date'] = date("Y-m-d H:i:s");

			$result = $this->Boardmanager->manager_write($params);

			if($result) {
				
				//게시판접근권한
				$this->load->model('boardadmin');
				foreach($_POST['managerauth'] as $k => $manager) { 
					$this->boardadmin->boardadmin_delete_all($k,$_POST['board_id']);
					$board_act = ($_POST['board_act'][$k]>0)?$_POST['board_act'][$k]:'0';
					$board_view = ($_POST['board_view'][$k]>0)?$_POST['board_view'][$k]:'0';
					$board_view_pw = ($board_view && $_POST['board_view_pw'][$k]>0)?$_POST['board_view_pw'][$k]:'0';
					$badparams['boardid']				= $_POST['board_id'];
					$badparams['manager_seq']		= $k;
					$badparams['board_act']			= $board_act;
					$badparams['board_view']			= ($board_view_pw==2)?$board_view_pw:$board_view;
					$badparams['r_manager_seq']	= $this->managerInfo['manager_seq'];
					$badparams['r_date']					= date('Y-m-d H:i:s'); 
					$this->boardadmin->boardadmin_write($badparams);
					unset($badparams);
				}


				$sc['whereis']	= ' and seq= "'.$result.'" ';
				$sc['select']		= ' * ';
				$manager = $this->Boardmanager->get_managerdata($sc);//게시판정보
				boarduploaddir($manager);//폴더생성 및 스킨 복사
				//icon 추가
				$callback = "parent.document.location.href='".$this->Boardmanager->managerurl."';";
				openDialogAlert(addslashes($_POST['board_name'])." 게시판을 생성하였습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert(addslashes($_POST['board_name'])."생성에 실패하였습니다.",400,140,'parent','');
			}
			exit;
		}

		/* 게시판수정 */
		elseif($mode == 'boardmanager_modify') { 
			if( !$this->isplusfreenot && $_POST['board_id'] == 'bulkorder' ){
				$html = '<table width="100%"><tr><td align="left">무료몰Plus+에서는 대량구매게시판을 지원하지 않는 기능입니다. <br />프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 대량구매게시판을 이용 가능합니다.</td></tr><tr><td align="center"><br /><br /><span class="btn large gray"><input type="button" onclick="serviceUpgrade();" value="업그레이드 >"></span></td></tr></table>';
				openDialogAlert($html,600,210,'parent','');
				exit;
			}

			$sc['whereis']	= ' and id= "'.$_POST['board_id'].'" ';
			$sc['select']		= ' * ';
			$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
			if (!isset($manager['id'])) {
				$callback = "parent.document.location.href='".$this->Boardmanager->managerurl."';";
				openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','');
				exit;
			}

			$this->validation->set_rules('board_name', '게시판명','trim|required|max_length[32]|xss_clean');

			if ( strstr( $_POST['skin'][0], 'gallery') ) {
				$_POST['gallerycell'][0]						=  ($_POST['gallerycell'][0]>0)?$_POST['gallerycell'][0]:'';
				$_POST['gallerycell'][1]						=  ($_POST['gallerycell'][1]>0)?$_POST['gallerycell'][1]:'';
				$this->validation->set_rules('gallerycell[0]', '페이지당 노출 수','trim|required|numeric|xss_clean');
				$this->validation->set_rules('gallerycell[1]', '페이지당 노출 수','trim|required|numeric|xss_clean');

				$_POST['gallery_list_w']						=  ($_POST['gallery_list_w']>0)?$_POST['gallery_list_w']:'';
				$_POST['gallery_list_h']						=  ($_POST['gallery_list_h']>0)?$_POST['gallery_list_h']:'';
				$this->validation->set_rules('gallery_list_w', '리스트 이미지 가로사이즈','trim|required|numeric|xss_clean');
				$this->validation->set_rules('gallery_list_h', '리스트 이미지 세로사이즈','trim|required|numeric|xss_clean');
			}else{
				$_POST['gallery_list_w']						=  ($_POST['gallery_list_w']>0)?$_POST['gallery_list_w']:'250';
				$_POST['gallery_list_h']						=  ($_POST['gallery_list_h']>0)?$_POST['gallery_list_h']:'250';

				$_POST['pagenum']								=  ($_POST['pagenum']>0)?$_POST['pagenum']:'';
				$this->validation->set_rules('pagenum', '페이지당 노출 수','trim|required|numeric|xss_clean');
			}


			if( $_POST['board_id'] == 'goods_qna' ||  $_POST['board_id'] == 'goods_review' ){
				$this->validation->set_rules('goods_num', '상품상세 > 페이지당 노출 수','trim|required|numeric|xss_clean');
			}


			//읽기권한 및 접근권한
			if ($_POST['auth_read'][0] == 'member') {
				if(!is_array($_POST['auth_read_group'])){
					$this->validation->set_rules('auth_read_group', '읽기권한시 [회원그룹]','trim|required|xss_clean');
				}
			}

			if( $manager['type'] == 'A' ) {
				if ($_POST['auth_write'][0] == 'member') {
					if(!is_array($_POST['auth_write_group'])){
						$this->validation->set_rules('auth_write_group', '작성권한-쓰기 [회원그룹]','trim|required|xss_clean');
					}
				}

				if ($_POST['auth_reply'][0] == 'member') {
					if(!is_array($_POST['auth_reply_group'])){
						$this->validation->set_rules('auth_reply_group', '작성권한-답글 [회원그룹]','trim|required|xss_clean');
					}
				}

				if ($_POST['auth_cmt'][0] == 'member') {
					if(!is_array($_POST['auth_cmt_group'])){
						$this->validation->set_rules('auth_cmt_group', '작성권한-댓글 [회원그룹]','trim|required|xss_clean');
					}
				}
			}else{
				if ($_POST['auth_write'][0] == 'member') {
					if(!is_array($_POST['auth_write_group'])){
						$this->validation->set_rules('auth_write_group', '작성권한시 [회원그룹]','trim|required|xss_clean');
					}
				}
			}


			if($_POST['board_id'] == 'goods_review' ){//상품후기 수동/자동 적립금 설정추가 @2012-11-05
				if( $_POST['autoemoney'] == 1 ) {//자동지급 사용
					if( $_POST['autoemoneytype'] == 2 ){//조건2
						$_POST['autoemoneystrcut2'] = '0';
						$_POST['autoemoneystrcut1'] = ($_POST['autoemoneystrcut1']>0)?$_POST['autoemoneystrcut1']:'';
						$this->validation->set_rules('autoemoneystrcut1','상품후기 글자제한수','trim|required|numeric|xss_clean');
					}elseif( $_POST['autoemoneytype'] == 3 ){//조건3
						$_POST['autoemoneystrcut1'] = '0';
						$_POST['autoemoneystrcut2'] = ($_POST['autoemoneystrcut2']>0)?$_POST['autoemoneystrcut2']:'';
						$this->validation->set_rules('autoemoneystrcut2','상품후기 글자제한수','trim|required|numeric|xss_clean');
					}else{
						$this->validation->set_rules('autoemoneytype','적립금 자동 지급시 조건','trim|required|xss_clean');
					}
				}else{
					$_POST['autoemoneytype'] = '0';
					$_POST['autoemoneystrcut1'] = '0';
					$_POST['autoemoneystrcut2'] = '0';
				}
				$_POST['autoemoney_video'] = ($_POST['autoemoney_video']>0)?$_POST['autoemoney_video']:0;
				$_POST['autoemoney_photo'] = ($_POST['autoemoney_photo']>0)?$_POST['autoemoney_photo']:0;
				$_POST['autoemoney_review'] = ($_POST['autoemoney_review']>0)?$_POST['autoemoney_review']:0;
			}


			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}


			$params['id']						=  $_POST['board_id'];
			$params['name']					=  $_POST['board_name'];
			$params['auth_read_use']	=  $_POST['auth_read_use'];
			$params['auth_write_use']	=  $_POST['auth_write_use'];
			$params['auth_reply_use']	=  $_POST['auth_reply_use'];
			$params['auth_cmt_use']	=  $_POST['auth_cmt_use'];

			$params['autowrite_use']	=  $_POST['autowrite_use'];
			$params['file_use']				=  $_POST['file_use'];
			$params['onlyimage_use']	=  $_POST['onlyimage_use'];
			$params['video_use']				=  $_POST['video_use'];
			$params['video_type']			=  ($_POST['video_type'])?$_POST['video_type']:'400';
			$params['video_screen'] = (is_array($_POST['video_screen'])) ? @implode("X",($_POST['video_screen'])):'400X300';
			$params['video_size'] = (is_array($_POST['video_size'])) ? @implode("X",($_POST['video_size'])):'';
			$params['video_size_mobile'] = (is_array($_POST['video_size_mobile'])) ? @implode("X",($_POST['video_size_mobile'])):'';//화면크기
			$params['file_type']				=  (!empty($_POST['file_type']))?$_POST['file_type']:'';
			$params['secret_use']			=  $_POST['secret_use'];

			$params['write_show']		=  $_POST['write_show'];
			$params['show_name_type']		=  $_POST['show_name_type'];
			$params['show_grade_type']		=  $_POST['show_grade_type'];

			$params['content_default']		=  $_POST['content_default'];//기본내용
			$params['content_default_mobile']		=  $_POST['content_default_mobile'];//모바일-기본내용

			$params['pagenum']			=  ( strstr( $_POST['skin'][0], 'gallery') ) ? ($_POST['gallerycell'][0]*$_POST['gallerycell'][1]):$_POST['pagenum'];
			$params['list_show']			=  $_POST['list_show'];

			$params['icon_new_day']	=  $_POST['icon_new_day'];
			$params['icon_hot_visit']		=  $_POST['icon_hot_visit'];
			$params['goods_num']		=  $_POST['goods_num'];
			$params['goods_review_type'] =  $_POST['goods_review_type'];

			$params['subjectcut']			=  ($_POST['subjectcut'])?$_POST['subjectcut']:30;
			$params['contcut']				=  ($_POST['contcut'])?$_POST['contcut']:200;


			$params['gallery_list_w']		=  ($_POST['gallery_list_w']>0)?$_POST['gallery_list_w']:250;
			$params['gallery_list_h']		=  ($_POST['gallery_list_h']>0)?$_POST['gallery_list_h']:250;

			if(isset($_POST['skin'][0] )) $params['skin'] =  $_POST['skin'][0];

			if ($_POST['auth_read'][0] == 'member') {
				$params['auth_read'] = '[member]';
				foreach($_POST['auth_read_group'] as $groupid=>$groupval){
					$params['auth_read'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_read'] = '['.$_POST['auth_read'][0].']';//관리자전용 또는 전체이용가능시
			}

			if ($_POST['auth_write'][0] == 'member') {
				$params['auth_write'] = '[member]';
				foreach($_POST['auth_write_group'] as $groupid=>$groupval){
					$params['auth_write'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_write'] = '['.$_POST['auth_write'][0].']';//관리자전용 또는 전체이용가능시
			}

			if ($_POST['auth_reply'][0] == 'member') {
				$params['auth_reply'] = '[member]';
				foreach($_POST['auth_reply_group'] as $groupid=>$groupval){
					$params['auth_reply'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_reply'] = '['.$_POST['auth_reply'][0].']';//관리자전용 또는 전체이용가능시
			}

			if ($_POST['auth_cmt'][0] == 'member') {
				$params['auth_cmt'] = '[member]';
				foreach($_POST['auth_cmt_group'] as $groupid=>$groupval){
					$params['auth_cmt'] .= '[group:'.$groupval.']';
				}
			}else{
				$params['auth_cmt'] = '['.$_POST['auth_cmt'][0].']';//관리자전용 또는 전체이용가능시
			}


			//댓글권한추가됨
			if($_POST['board_id'] == 'goods_review' ){//상품후기
				if ($_POST['auth_write_cmt'][0] == 'member') {
					$params['auth_write_cmt'] = '[member]';
				}else{
					$params['auth_write_cmt'] = '['.$_POST['auth_write_cmt'][0].']';//관리자전용 또는 전체이용가능시
				}
			}elseif($_POST['board_id'] == 'notice' ){//공지사항
				if ($_POST['auth_write_cmt'][0] == 'member') {
					$params['auth_write_cmt'] = '[member]';
					foreach($_POST['auth_write_cmt_group'] as $groupid=>$groupval){
						$params['auth_write_cmt'] .= '[group:'.$groupval.']';
					}
				}else{
					$params['auth_write_cmt'] = '['.$_POST['auth_write_cmt'][0].']';//관리자전용 또는 전체이용가능시
				}
			}

			$params['viewtype']				=  ($_POST['viewtype'])?$_POST['viewtype']:"page";


			$params['category'] = (is_array($_POST['category'])) ? @implode(",",($_POST['category'])):'';//카테고리 콤마로 구분
			$params['list_show'] = (is_array($params['list_show'])) ? '[subject]'.@implode("",$params['list_show']):'[subject]';//리스트 표시항목 콤마로 구분
			if( $_POST['board_id'] == 'goods_qna' ||  $_POST['board_id'] == 'goods_review' ) $params['list_show'] .= '[images]';

			$params['m_date'] = date("Y-m-d H:i:s");
			$params['category'] = htmlspecialchars($params['category']);

			$params['gallerycell'] = (is_array($_POST['gallerycell'])) ? @implode("X",($_POST['gallerycell'])):'';
			$params['write_admin'] = if_empty($_POST, 'write_admin', '관리자');
			$params['write_admin_type'] =  $_POST['write_admin_type'];


			if($_POST['board_id'] == 'goods_review' ){//상품후기
				$params['reviewcategory'] = (is_array($_POST['reviewcategory'])) ? @implode(",",($_POST['reviewcategory'])):'';//평가점수 콤마로 구분

				$boardformdelsql	= "delete from fm_boardform where boardid = '".$_POST['board_id']."'";
				$this->db->query($boardformdelsql);
				$user_sub_arr = $_POST['labelItem']['user'];
				$sort_user=0;
				foreach($user_sub_arr as $k => $sub_arr){
					if($sub_arr['use'] =='')$sub_arr['use'] ='N';
					if($sub_arr['required'] =='')$sub_arr['required'] ='N';
					$sort_user++;
					$data = array(
									'bulkorderform_seq'=> $sub_arr['bulkorderform_seq'],
									'boardid' => $_POST['board_id'],
									'label_id' => $sub_arr['id'],
									'label_title' => $sub_arr['name'],
									'label_desc' => $sub_arr['exp'],
									'label_type' => $sub_arr['type'],
									'label_value' => $sub_arr['value'],
									'label_icon' => $sub_arr['icon'],
									'required' => $sub_arr['required'],
									'used' => $sub_arr['use'],
									'sort_seq' => $sort_user,
									'regist_date' => date('Y-m-d H:i:s'),
								);
					$this->db->insert('fm_boardform', $data);
				}
			}elseif($_POST['board_id'] == 'bulkorder' ){//대량구매
				$params['bulk_show']					= (is_array($_POST['bulk_show'])) ? @implode("",($_POST['bulk_show'])):'';
				$params['bulk_totprice']				= $_POST['bulk_totprice'];
				$params['bulk_payment_type']	= $_POST['bulk_payment_type'];

				$boardformdelsql	= "delete from fm_boardform where boardid = '".$_POST['board_id']."'";
				$this->db->query($boardformdelsql);
				$user_sub_arr = $_POST['labelItem']['user'];
				$sort_user=0;
				foreach($user_sub_arr as $k => $sub_arr){
					if($sub_arr['use'] =='')$sub_arr['use'] ='N';
					if($sub_arr['required'] =='')$sub_arr['required'] ='N';
					$sort_user++;
					$data = array(
									'bulkorderform_seq'=> $sub_arr['bulkorderform_seq'],
									'boardid' => $_POST['board_id'],
									'label_id' => $sub_arr['id'],
									'label_title' => $sub_arr['name'],
									'label_desc' => $sub_arr['exp'],
									'label_type' => $sub_arr['type'],
									'label_value' => $sub_arr['value'],
									'required' => $sub_arr['required'],
									'used' => $sub_arr['use'],
									'sort_seq' => $sort_user,
									'regist_date' => date('Y-m-d H:i:s'),
								);
					$this->db->insert('fm_boardform', $data);
				}
			}

			$sms_arr = array($_POST['board_id']."_write",$_POST['board_id']."_reply");
			for($i=0;$i<count($sms_arr);$i++){
				$user_id = $sms_arr[$i]."_user";
				$user_chk = $sms_arr[$i]."_user_yn";
				if($i!=2){
					if(if_empty($_POST, $user_chk, 'N') == 'Y' ) {
						$this->validation->set_rules($user_id, '내용','trim|required|xss_clean');
					}
					if($this->validation->exec()===false){
						$err = $this->validation->error_array;
						$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
						openDialogAlert('SMS 메시지를 입력해 주세요.',400,140,'parent',$callback);
						exit;
					}
				}

				config_save('sms',array($user_id=>$_POST[$user_id]));
				$admin_id = $sms_arr[$i]."_admin";
				config_save('sms',array($admin_id=>$_POST[$admin_id]));
				$user_chk = $sms_arr[$i]."_user_yn";
				config_save('sms',array($user_chk=>if_empty($_POST, $user_chk, 'N')));
				##
				$sms_info = config_load('sms_info');
				$cnt = $sms_info['admis_cnt'];
				for($j=0;$j<$cnt+1;$j++){
					$admins_chk = $sms_arr[$i]."_admins_yn_".$j;
					config_save('sms',array($admins_chk=>if_empty($_POST, $admins_chk, 'N')));
				}
			} 
			
			if ( $_POST['writer_date_regit'] && $_POST['writer_date_login']){
				$params['writer_date'] = 'all';
			}elseif ( $_POST['writer_date_regit'] || $_POST['writer_date_login']){
				$params['writer_date'] = ($_POST['writer_date_regit'])?$_POST['writer_date_regit']:$_POST['writer_date_login'];
			}else{
				$params['writer_date'] =  if_empty($_POST, 'writer_date', 'none');
			}
			$params['recommend_type'] =  if_empty($_POST, 'recommend_type', '1');
			$params['cmt_recommend_type'] =  if_empty($_POST, 'cmt_recommend_type', '1');
			$params['auth_recommend_use'] =  if_empty($_POST, 'auth_recommend_use', 'N');
			$params['auth_cmt_recommend_use'] =  if_empty($_POST, 'auth_cmt_recommend_use', 'N');

			if($_POST['real_icon_name_recommend']) $params['recommend_icon_file'] =  $_POST['real_icon_name_recommend'];
			if($_POST['real_icon_name_none_rec'])	$params['none_rec_icon_file'] =  $_POST['real_icon_name_none_rec'];
			if($_POST['real_icon_name_recommend_good1']) $params['recommend_good1_icon_file'] =  $_POST['real_icon_name_recommend_good1'];
			if($_POST['real_icon_name_recommend_good2']) $params['recommend_good2_icon_file'] =  $_POST['real_icon_name_recommend_good2'];
			if($_POST['real_icon_name_recommend_good3']) $params['recommend_good3_icon_file'] =  $_POST['real_icon_name_recommend_good3'];
			if($_POST['real_icon_name_recommend_good4']) $params['recommend_good4_icon_file'] =  $_POST['real_icon_name_recommend_good4'];
			if($_POST['real_icon_name_recommend_good5']) $params['recommend_good5_icon_file'] =  $_POST['real_icon_name_recommend_good5'];
			if($_POST['real_icon_name_cmt_recommend'])	$params['cmt_recommend_icon_file'] =  $_POST['real_icon_name_cmt_recommend'];
			if($_POST['real_icon_name_cmt_none_rec_icon_file']) $params['cmt_none_rec_icon_file'] =  $_POST['real_icon_name_cmt_none_rec_icon_file']; 

			$result = $this->Boardmanager->manager_modify($params); 
			if($result) {
			
				
				//게시판접근권한
				$this->load->model('boardadmin'); 
				foreach($_POST['managerauth'] as $k => $manager) { 
					$this->boardadmin->boardadmin_delete_all($k,$_POST['board_id']);
					$board_act = ($_POST['board_act'][$k]>0)?$_POST['board_act'][$k]:'0';
					$board_view = ($_POST['board_view'][$k]>0)?$_POST['board_view'][$k]:'0';
					$board_view_pw = ($board_view && $_POST['board_view_pw'][$k]>0)?$_POST['board_view_pw'][$k]:'0';
					$badparams['boardid']				= $_POST['board_id'];
					$badparams['manager_seq']		= $k;
					$badparams['board_act']			= $board_act;
					$badparams['board_view']			= ($board_view_pw==2)?$board_view_pw:$board_view;
					$badparams['r_manager_seq']	= $this->managerInfo['manager_seq'];
					$badparams['up_date']					= date('Y-m-d H:i:s');  
					$this->boardadmin->boardadmin_write($badparams);
					unset($badparams);
				}


				if($_POST['board_id'] == 'goods_review' ){//상품후기 수동/자동 적립금 설정추가 @2012-11-05
					//$groupcd = "reserve";
					$reservear['autoemoney'] = $_POST['autoemoney'];
					$reservear['autoemoneytype'] = $_POST['autoemoneytype'];
					$reservear['autoemoneystrcut1'] = $_POST['autoemoneystrcut1'];
					$reservear['autoemoneystrcut2'] = $_POST['autoemoneystrcut2'];
					$reservear['autoemoney_photo'] = $_POST['autoemoney_photo'];
					$reservear['autoemoney_video'] = $_POST['autoemoney_video'];
					$reservear['autoemoney_review'] = $_POST['autoemoney_review'];

					$reservear['autopoint_photo'] = $_POST['autopoint_photo'];
					$reservear['autopoint_video'] = $_POST['autopoint_video'];
					$reservear['autopoint_review'] = $_POST['autopoint_review'];

					$reservear['reserve_goods_review'] = $_POST['reserve_goods_review'];

					//특정기간 유효기간 추가 @2014-05-12
					$reservear['date_reserve_select'] = $_POST['date_reserve_select'];
					$reservear['date_reserve_year'] = $_POST['date_reserve_year'];
					$reservear['date_reserve_direct'] = $_POST['date_reserve_direct'];

					$reservear['photo_reserve_select'] = $_POST['photo_reserve_select'];
					$reservear['photo_reserve_year'] = $_POST['photo_reserve_year'];
					$reservear['photo_reserve_direct'] = $_POST['photo_reserve_direct'];

					$reservear['video_reserve_select'] = $_POST['video_reserve_select'];
					$reservear['video_reserve_year'] = $_POST['video_reserve_year'];
					$reservear['video_reserve_direct'] = $_POST['video_reserve_direct'];

					$reservear['default_reserve_select'] = $_POST['default_reserve_select'];
					$reservear['default_reserve_year'] = $_POST['default_reserve_year'];
					$reservear['default_reserve_direct'] = $_POST['default_reserve_direct'];

					if( $this->isplusfreenot) {//무료몰이아닌경우에만 적용 @2013-01-14

						//특정기간 유효기간 추가 @2014-05-12
						$reservear['date_point_select'] = $_POST['date_point_select'];
						$reservear['date_point_year'] = $_POST['date_point_year'];
						$reservear['date_point_direct'] = $_POST['date_point_direct'];

						$reservear['photo_point_select'] = $_POST['photo_point_select'];
						$reservear['photo_point_year'] = $_POST['photo_point_year'];
						$reservear['photo_point_direct'] = $_POST['photo_point_direct'];

						$reservear['video_point_select'] = $_POST['video_point_select'];
						$reservear['video_point_year'] = $_POST['video_point_year'];
						$reservear['video_point_direct'] = $_POST['video_point_direct'];

						$reservear['default_point_select'] = $_POST['default_point_select'];
						$reservear['default_point_year'] = $_POST['default_point_year'];
						$reservear['default_point_direct'] = $_POST['default_point_direct'];

					}

					$reservear['bbs_start_date'] = $_POST['bbs_start_date'];
					$reservear['bbs_end_date'] = $_POST['bbs_end_date'];
					$reservear['emoneyBbs_limit'] = $_POST['emoneyBbs_limit'];
					$reservear['pointBbs_limit'] = $_POST['pointBbs_limit'];
				}

				if(is_array($reservear)) config_save_array('reserve',$reservear);


				$sc['whereis']	= ' and id= "'.$_POST['board_id'].'" ';
				$sc['select']		= ' * ';
				$upmanager = $this->Boardmanager->get_managerdata($sc);//게시판정보
				boarduploaddir($upmanager);//폴더생성 및 스킨 복사
				$callback = "parent.document.location.href='/admin/board/manager_write?id=".$_POST['board_id']."';";
				openDialogAlert(addslashes($_POST['board_name'])." 게시판설정을 수정하였습니다.",400,140,'parent',$callback);
			}else{
				$callback = "";
				openDialogAlert(addslashes($_POST['board_name'])." 수정이 실패 되었습니다.",400,140,'parent',$callback);
			}
			exit;
		}

		/* 게시판삭제 */
		elseif($mode == 'boardmanager_delete') {
			$sc['whereis']	= ' and id= "'.$_POST['delid'].'" ';
			$sc['select']		= ' * ';
			$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
			if (!isset($manager['id'])) {
				$callback = "parent.document.location.href='".$this->Boardmanager->managerurl."';";
				openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','');
				exit;
			}
			$result = $this->Boardmanager->manager_delete($_POST['delid']);
			if($result) {
				define('BOARDID',$_POST['delid']);
				if( $_POST['delid'] == 'goods_qna' ) {
					$this->load->model('Goodsqna','Boardmodel');
				}elseif( $_POST['delid'] == 'goods_review' ) {
					$this->load->model('Goodsreview','Boardmodel');
				}elseif( $_POST['delid'] == 'bulkorder' ) {//대량구매게시판
					$this->load->model('Boardbulkorder','Boardmodel');
				}else{
					$this->load->model('Boardmodel');
				}

				$this->load->helper('file_helper');
				$this->load->model('Boardindex');//공지용
				$this->load->model('Boardcomment');
				delete_files($this->Boardmanager->board_data_dir.$_POST['delid'].'/', TRUE, 1);//게시판 데이타 폴더 삭제
				delete_files($this->Boardmanager->board_skin_dir.$_POST['delid'].'/', TRUE, 1);//게시판 스킨폴더 삭제

				//접근권한제거
				$this->load->model('boardadmin');
				$this->boardadmin->boardadmin_delete_id($_POST['delid']);

				$this->Boardindex->idx_delete_id($_POST['delid']);//index 삭제
				$this->Boardmodel->data_delete_id($_POST['delid']);//게시글전체 삭제
				$this->Boardcomment->data_delete_id($_POST['delid']);//댓글전체 삭제
				$this->Boardscorelog->data_delete_id($_POST['delid']);//댓글/게시글 평가 전체 삭제
				$resulta = array('result'=>true);
				echo json_encode($resulta);
				exit;
			}else{
				$resulta = array('result'=>false);
				echo json_encode($resulta);
				exit;
			}
			exit;
		}

		/* 게시판다중삭제 */
		elseif($mode == 'boardmanager_multi_delete') {
			$delidar = @explode(",",$_POST['delidar']);
			$num = 0;
			for($i=0;$i<sizeof($delidar);$i++){ if(empty($delidar[$i]))continue;
				$delid = $delidar[$i];
				define('BOARDID',$delid);

				$result = $this->Boardmanager->manager_delete($delid);
				if($result) {$num++;
					if( $delid == 'goods_qna' ) {
						$this->load->model('Goodsqna','Boardmodel');
					}elseif( $delid == 'goods_review' ) {
						$this->load->model('Goodsreview','Boardmodel');
					}elseif( $delid == 'bulkorder' ) {
						$this->load->model('Boardbulkorder','Boardmodel');
					}else{
						$this->load->model('Boardmodel');
					}

					$this->load->helper('file_helper');
					$this->load->model('Boardindex');//공지용
					$this->load->model('Boardcomment');

					delete_files( $this->Boardmanager->board_data_dir.$delid.'/', TRUE, 1);//게시판 데이타폴더 삭제
					delete_files($this->Boardmanager->board_skin_dir.$delid.'/', TRUE, 1);//게시판 스킨폴더 삭제 

					//접근권한제거
					$this->load->model('boardadmin');
					$this->boardadmin->boardadmin_delete_id($_POST['delid']);

					$this->Boardindex->idx_delete_id($delid);//index 삭제
					$this->Boardmodel->data_delete_id($delid);//게시글전체 삭제
					$this->Boardcomment->data_delete_id($delid);//댓글전체 삭제
					$this->Boardscorelog->data_delete_id($delid);//댓글/게시글 평가 전체 삭제
				}
			}
			$resulta = array('num'=>$num);
			echo json_encode($resulta);
			exit;

		}

		/* 게시판복사 */
		elseif($mode == 'boardmanager_copy') {

			$this->load->model('usedmodel');
			$result = $this->usedmodel->used_service_check('board');
			if(!$result['type']){
				$html = '<table width="100%"><tr><td align="left">무료몰+ : 기본 5개 (게시판 추가 시 1개당 2,200원, 최초 1회 결제로 기간 관계 없이 계속 이용)<br />프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 게시판을 무제한 이용 가능합니다.</td></tr><tr><td align="center"><br /><br /><span class="btn large gray"><input type="button" onclick="serviceBoardAdd();" value="추가 신청 >"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="btn large gray"><input type="button" onclick="serviceUpgrade();" value="업그레이드 >"></span></td></tr></table>';
				openDialogAlert($html,600,210,'parent','');
				exit;
			}

			### Validation
			if( $_POST['id'] == '영문, 숫자, 언더스코어(_), 하이픈(-) 가능' ) {
				$_POST['id'] = '';
			}

			if( $_POST['name'] == '영문,한글, 숫자, 언더스코어(_), 하이픈(-) 가능' ) {
				$_POST['name'] = '';
			}

			$this->validation->set_rules('id', '게시판 아이디','trim|required|max_length[32]|xss_clean');
			$this->validation->set_rules('name', '게시판명','trim|required|max_length[32]|xss_clean');


			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}

			###
			$sc['whereis']	= ' and id= "'.$_POST['id'].'" ';//등록시
			$sc['select']		= ' seq, id, name ';
			$rdata = $this->Boardmanager->managerdataidck($sc);//게시판정보
			if($rdata) {//등록된 게시판 아이디인경우
				$callback = "if(parent.document.getElementsByName('userid')[0]) parent.document.getElementsByName('userid')[0].focus();";
				openDialogAlert("이미사용중인 아이디입니다.",400,140,'parent',$callback);
				exit;
			}

			$sc['whereis']	= ' and id= "'.$_POST['copyid'].'" ';
			$sc['select']		= ' * ';
			$boardmanager = $this->Boardmanager->get_managerdata($sc);//게시판정보
			if($boardmanager){
				$nokey = array('seq','type','id','name','totalnum','r_date','m_date');
				foreach($boardmanager as $key=>$val) {
					if(in_array($key,$nokey)) continue;
					$params[$key] = $val;
				}
				$params['r_date'] = date("Y-m-d H:i:s");
				$params['m_date'] = date("Y-m-d H:i:s");
				$params['id'] = $_POST['id'];
				$params['name'] = $_POST['name'];
				$params['type'] = 'A';
				$result = $this->Boardmanager->manager_copy($params,$boardmanager, $_POST['copyid'], $_POST['id']);
			}

			if($result) {
				$sc['whereis']	= ' and id= "'.$_POST['copyid'].'" ';
				$sc['select']		= ' * ';
				$cpmanager = $this->Boardmanager->get_managerdata($sc);//게시판정보
				boarduploaddir($cpmanager);//폴더생성 및 스킨 복사

				$callback = "parent.document.location.reload();";
				openDialogAlert("게시판을 복사하였습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert("게시판복사가 실패 되었습니다.",400,140,'parent',$callback);
			}
			exit;
		}

		/* 게시판 아이콘
		 * @ new/hot/review 저장 
		 * @ 게시판수정시에는 자동저장됨
		*/
		elseif($mode == 'boardmanager_icon') {

			$this->load->library('upload');
			if (is_uploaded_file($_FILES['board_icon']['tmp_name'])) {
				$config['upload_path']		= (isset($_POST['boardid'])) ? $this->Boardmanager->board_data_dir.$_POST['boardid'].'/':$this->Boardmanager->board_tmp_dir;

				$file_ext = end(explode('.', $_FILES['board_icon']['name']));//확장자추출
				$config['allowed_types']	= 'jpg|gif|jpeg|png';

				$tmp = @getimagesize($_FILES['board_icon']['tmp_name']);
				$_FILES['Filedata']['type'] = $tmp['mime'];
				$config['overwrite']			= TRUE;
				$config['file_name']			= (isset($_POST['boardid'])) ? $_POST['boardid'].'_'.$_POST['icontype'].'.'.$file_ext:substr(microtime(), 2, 6).'_'.$_POST['icontype'].'.'.$file_ext;
				$this->upload->initialize($config);

				if ($this->upload->do_upload('board_icon')) {

					@chmod($config['upload_path'].$config['file_name'], 0777);

					if(isset($_POST['boardid'])) {
						$callback = 'parent.iconFileUploadComplete("true","'.$_POST['icontype'].'","'.$config['file_name'].'","'.$this->Boardmanager->board_data_src.$_POST['boardid'].'/","'.$file_ext.'");';

						$params['icon_'.$_POST['icontype'].'_img'] = $config['file_name'];
						$this->Boardmanager->manager_modify($params);//수정시

						$sc['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
						$sc['select']		= ' * ';
						$manager = $this->Boardmanager->get_managerdata($sc);//게시판정보
						boarduploaddir($manager);//폴더생성 및 스킨 복사
					}else{
						$callback = 'parent.iconFileUploadComplete("true","'.$_POST['icontype'].'","'.$config['file_name'].'","'.$this->Boardmanager->board_tmp_src.'","'.$file_ext.'");';
						//(icontype,filename, filedir)
					}

					openDialogAlert("등록하였습니다.",400,140,'parent',$callback);

				}else{

					$callback = 'parent.iconFileUploadComplete("false","'.$_POST['icontype'].'","","","");';
					openDialogAlert("gif, jpg,jpeg, png 만 가능합니다.",400,140,'parent',$callback);

				}

			}else{
				$callback = 'parent.iconFileUploadComplete("false","'.$_POST['icontype'].'","","","");';
				openDialogAlert("등록 파일이 없습니다.",400,140,'parent',$callback);
			}
			exit;

		}

		/* 게시판아이디 중복체크 */
		elseif($mode == 'boardmanager_idck') {
			if(empty($_POST['board_id'])) echo '';

			$sc['whereis']	= ' and id= "'.$_POST['board_id'].'" ';
			$sc['select']		= ' seq, id, name ';
			$result = $this->Boardmanager->managerdataidck($sc);//게시판정보
			echo !$result ? 'true' : 'false';
			exit;
		}

		/* 게시판 스킨설정 */
		elseif($mode == 'boardmanager_skin_help') {
			$this->load->helper('file');
			if ( $_POST['boardid'] ){//게시판이 생성된경우
				$skin_type_img	= $this->Boardmanager->board_skin_src.$_POST['boardid'].'/'.$_POST['skinname'].'/skin_type.gif';
				$skin_help_file		= $this->Boardmanager->board_skin_dir.'/'.$_POST['boardid'].'/'.$_POST['skinname'].'/help.txt';
				$skin_help = read_file($skin_help_file);
				if(!$skin_help) {
					$_POST['skinname'] = ($_POST['skinname'])?$_POST['skinname']:'default01';
					$skin_type_img	= $this->Boardmanager->board_originalskin_src.$_POST['skinname'].'/skin_type.gif';
					$skin_help_file		= $this->Boardmanager->board_originalskin_dir.'/'.$_POST['skinname'].'/help.txt';
					$skin_help = read_file($skin_help_file);
				}
			}else{
				$_POST['skinname'] = ($_POST['skinname'])?$_POST['skinname']:'default01';
				$skin_type_img	= $this->Boardmanager->board_originalskin_src.$_POST['skinname'].'/skin_type.gif';
				$skin_help_file		= $this->Boardmanager->board_originalskin_dir.'/'.$_POST['skinname'].'/help.txt';
			}

			$skin_help = read_file($skin_help_file);
			if(!$skin_help && $_POST['boardid']) {
				if(in_array($_POST['boardid'], $this->Boardmanager->renewlist) ) {//기본만
					if( $_POST['boardid']=='goods_qna' ||  $_POST['boardid']=='goods_review' ) {
						$skin_type_img	= '/data/skin/'.$this->skin.'/images/board/skin_type_goods.gif';
						$skin_help_file		= ROOTPATH.'/data/skin/'.$this->skin.'/images/board/help_goods.txt';
					}else{
						$skin_type_img	= '/data/skin/'.$this->skin.'/images/board/skin_type_'.$_POST['boardid'].'.gif';
						$skin_help_file		= ROOTPATH.'/data/skin/'.$this->skin.'/images/board/help_'.$_POST['boardid'].'.txt';
					}
				}else{
					$skin_type_img	= '/data/skin/'.$this->skin.'/images/board/skin_type_'.$_POST['skinname'].'.gif';
					$skin_help_file		= ROOTPATH.'/data/skin/'.$this->skin.'/images/board/help_'.$_POST['skinname'].'.txt';
				}
				$skin_help = read_file($skin_help_file);
			}

			$result = array('skin_type_img'=>'<img src="'.$skin_type_img.'" >', 'skin_help'=>nl2br($skin_help));
			echo json_encode($result);
			exit;
		}
	}

	//에딧터 iframe 설정
	public function boardiframeusesave(){
		//config_save("board_editor" ,array('editor_secu_domain'=>$_POST['editor_secu_domain']));
		//config_save("board_editor" ,array('editor_secu_file'=>$_POST['editor_secu_file']));
		//config_save("board_editor" ,array('editor_secu_image'=>$_POST['editor_secu_image']));

		$result = array("result"=>true,"msg"=>"설정이 저장 되었습니다.");
		echo json_encode($result);
		exit;
	}

	//boardmain list 설정
	public function boardmanagermain(){
		if( !$_POST['boardmain_item_use'] ) {
			openDialogAlert("주요 게시판을 한개이상 선택해 주세요.",400,140,'parent',$callback);
			exit;
		}
		## 정보 초기화
		$sql	= "delete from fm_config where groupcd = 'board_main'";
		$query	= $this->db->query($sql);

		foreach($_POST['boardmain_item_use'] as $boardid){$idx++;
			config_save("board_main" ,array($boardid=>$idx));
		}
		$callback = "parent.document.location = '/admin/board/index';";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		exit;
	}


}

/* End of file boardmanager_process.php */
/* Location: ./app/controllers/admin/boardmanager_process.php */