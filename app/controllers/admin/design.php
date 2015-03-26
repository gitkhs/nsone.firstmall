<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class design extends admin_base {

	var $realSkin;
	var $workingSkin;

	var $realMobileSkin;
	var $workingMobileSkin;

	var $realFammerceSkin;
	var $workingFammerceSkin;

	var $realStoreSkin;
	var $workingStoreSkin;

	var $realStoremobileSkin;
	var $workingStoremobileSkin;

	var $designWorkingSkin;
	var $folders;

	public function __construct() {
		parent::__construct();
		$this->load->helper('design');
		$this->load->model('layout');
		$this->load->helper('text');
		$this->load->model('designmodel');

		$this->template->assign(array('realSkin'=>$this->realSkin));
		$this->template->assign(array('workingSkin'=>$this->workingSkin));

		$this->template->assign(array('realMobileSkin'=>$this->realMobileSkin));
		$this->template->assign(array('workingMobileSkin'=>$this->workingMobileSkin));

		$this->template->assign(array('realFammerceSkin'=>$this->realFammerceSkin));
		$this->template->assign(array('workingFammerceSkin'=>$this->workingFammerceSkin));

		$this->template->assign(array('realStoreSkin'=>$this->realStoreSkin));
		$this->template->assign(array('workingStoreSkin'=>$this->workingStoreSkin));

		$this->template->assign(array('realStoremobileSkin'=>$this->realStoremobileSkin));
		$this->template->assign(array('workingStoremobileSkin'=>$this->workingStoremobileSkin));

		$this->template->assign(array('realStorefammerceSkin'=>$this->realStorefammerceSkin));
		$this->template->assign(array('workingStorefammerceSkin'=>$this->workingStorefammerceSkin));

		$this->template->assign(array('designWorkingSkin'=>$this->designWorkingSkin));

		$this->template->assign(array('mobileMode'=>$this->mobileMode));
		$this->template->assign(array('storemobileMode'=>$this->storemobileMode));
		$this->template->assign(array('fammerceMode'=>$this->fammerceMode));
		$this->template->assign(array('storefammerceMode'=>$this->storefammerceMode));
		$this->template->assign(array('arrSns'=>$this->arrSns));

		// 스킨의 영역별 폴더 구분
		$this->folders = $this->layout->get_folders_in_skin();

		// 웹FTP 템플릿 define
		$this->template->define(array('webftp'=>$this->skin.'/webftp/_webftp.html'));
		$this->template->define(array('mini_webftp'=>$this->skin.'/webftp/_mini_webftp.html'));

		// 페이지목록에서 숨길 파일 리스트
		$this->hidden_page_list = array(
			'goods/contents.html',
			'goods/user_select_list.html',
			'goods/zoom.html',
			'goods/user_select.html',
			'order/optional.html',
			'member/auth_chk.html',
			//'member/join_gate.html',
			'member/register_form.html',
			'mypage/order_exchange.html',
			'mypage/order_refund.html',
			'mypage/order_return.html',
			'mypage/taxwrite.html',
		);

	}

	public function index()
	{
		redirect("/admin/design/skin");
	}

	/* 스킨설정 */
	public function skin(){
		$this->admin_menu();
		$this->tempate_modules();

		$cfg_system	= ($this->config_system) ? $this->config_system : config_load('system');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('skin');

		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		/* 모바일스킨 프리픽스 */
		if($this->config_system['service']['code']=='P_STOR'){
			$skinPrefix = !empty($_GET['prefix']) ? $_GET['prefix'] : 'store';
		}elseif($this->config_system['service']['code']=='P_FAMM'){
			$skinPrefix = !empty($_GET['prefix']) ? $_GET['prefix'] : 'fammerce';
		}else{
			$skinPrefix = !empty($_GET['prefix']) ? $_GET['prefix'] : '';
		}
		$this->template->assign(array('skinPrefix'=>$skinPrefix));

		/* 스킨박스 사이즈 assign */
		$this->template->assign(array(
			"skin_apply_box_width"	=>	206,
			"skin_apply_box_height"	=>	260,
		));

		/* 실적용스킨,작업용스킨 configuration assign */
		switch($skinPrefix){
			case 'mobile':
				$this->template->assign(array('realSkinConfiguration'=>skin_configuration($this->realMobileSkin)));
				$this->template->assign(array('workingSkinConfiguration'=>skin_configuration($this->workingMobileSkin)));
			break;
			case 'fammerce':
				$this->template->assign(array('realSkinConfiguration'=>skin_configuration($this->realFammerceSkin)));
				$this->template->assign(array('workingSkinConfiguration'=>skin_configuration($this->workingFammerceSkin)));
			break;
			case 'store':
				$this->template->assign(array('realSkinConfiguration'=>skin_configuration($this->realStoreSkin)));
				$this->template->assign(array('workingSkinConfiguration'=>skin_configuration($this->workingStoreSkin)));
			break;
			case 'storemobile':
				$this->template->assign(array('realSkinConfiguration'=>skin_configuration($this->realStoremobileSkin)));
				$this->template->assign(array('workingSkinConfiguration'=>skin_configuration($this->workingStoremobileSkin)));
			break;
			case 'storefammerce':
				$this->template->assign(array('realSkinConfiguration'=>skin_configuration($this->realStorefammerceSkin)));
				$this->template->assign(array('workingSkinConfiguration'=>skin_configuration($this->workingStorefammerceSkin)));
			break;
			default:
				$this->template->assign(array('realSkinConfiguration'=>skin_configuration($this->realSkin)));
				$this->template->assign(array('workingSkinConfiguration'=>skin_configuration($this->workingSkin)));
			break;
		}


		$this->template->assign(array('cfg_system'	=> $cfg_system));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 디자인환경 안내 */
	public function main(){
		$this->admin_menu();
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		switch($_GET['setMode']){
			case "pc"			: $frontUrl = "http://{$this->pcDomain}/?setDesignMode=on&setMode=pc"; break;
			case "mobile"		: $frontUrl = "http://{$this->mobileDomain}/?setDesignMode=on&setMode=mobile"; break;
			case "fammerce"		: $frontUrl = "http://{$this->pcDomain}/?setDesignMode=on&setMode=fammerce"; break;
			case "store"		: $frontUrl = "http://{$this->pcDomain}/?setDesignMode=on&setMode=store"; break;
			case "storemobile"	: $frontUrl = "http://{$this->pcDomain}/?setDesignMode=on&setMode=storemobile"; break;
			case "storefammerce": $frontUrl = "http://{$this->pcDomain}/?setDesignMode=on&setMode=storefammerce"; break;
			default				: $frontUrl = "http://{$this->pcDomain}/?setDesignMode=on&setMode=pc"; break;
		}

		$this->template->assign('frontUrl',urlencode($frontUrl));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 디자인모드 세팅 후 프론트로 이동 */
	public function front_action(){
		pageRedirect($_GET['frontUrl']);
	}

	/* 디자인관리 패널 HTML 출력 */
	public function get_panel_html()
	{
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth) return;

		$template_path = $_GET['template_path'];
		// 작업스킨 경로
		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin;

		$tpls = array();
		foreach($this->folders as $key=>$value){
			$tpls[$key] = array('name'=>$value, 'childs'=>array($key));
		}

		if($template_path=='member/agreement.html'){
			$location = parse_url($_GET['location']);
			if(!preg_match("/join_type=/",$location['query'])){
				$template_path='member/join_gate.html';
			}
		}

		$layout_config = layout_config_load($this->designWorkingSkin);
		$skin_configuration = skin_configuration($this->designWorkingSkin);

		// 스킨파일목록
		foreach($tpls as $i=>$directory){
			$tpls[$i]['files'] = array();
			foreach($tpls[$i]['childs'] as $child){
				$map = directory_map($working_skin_path."/".$child,true,false);
				sort($map);

				foreach((array)$map as $file){

					$tpl_path = $child."/".$file;
					$file_path = $working_skin_path."/".$tpl_path;
					if(!preg_match("/\.html$/",$file)) continue;
					if(is_file($file_path)) {

						$file_type = 'normal';
						$file_type_msg = '';

						$tpl_desc = !empty($layout_config[$child."/".$file]['tpl_desc']) ? $layout_config[$child."/".$file]['tpl_desc'] : $file;
						$tpl_page = !empty($layout_config[$child."/".$file]['tpl_page']) ? true : false;

						if(in_array($child,array('layout_header','layout_footer','layout_side','layout_scroll')))
						{
							$url = "#";
							$file_type = 'layout';
							$file_type_msg = '별도의 미리보기 화면이 필요 없는 특별한 레이아웃 영역입니다<br />상단/하단/측면/스크롤의 영역은 해당 영역이 보이는 페이지에서 바로 EYE-DESIGN하세요.';
						} elseif($tpl_path=='goods/view.html'){
							$this->load->model('goodsmodel');
							$query = $this->db->query("select goods_seq from fm_goods order by goods_seq desc limit 1");
							$goods_seq = $query->row_array();
							$goods_seq = $goods_seq['goods_seq'];
							if(!empty($goods_seq)){
								$url = "../goods/view?no={$goods_seq}&designMode=1";
							}else{
								$url = "#";
								$file_type = 'goods_view';
								$file_type_msg = '상품상세페이지를 정확하게 디자인하기 위해서 최소 1개의 상품을 등록해 주세요.<br />이제, 상품등록하고 상품상세페이지를 보면서 바로 EYE-DESIGN 하세요!';
							}
						} elseif($tpl_path=='order/settle.html'){
							$url = "#";
							$file_type = 'settle';
							$file_type_msg = '주문하기페이지를 정확하게 디자인하기 위해서는 테스트로 주문을 하면서 디자인 하는 것이 가장 좋습니다.<br />왜냐하면, 주문할 때는 회원,비회원,쿠폰,적립금,배송비 등 매우 복잡한 경우를 모두 분석해서 주문이 이뤄지기 때문입니다.<br />이제, 주문페이지도 주문을 하면서 바로 EYE-DESIGN 하세요!';
						} elseif($tpl_path=='member/join_gate.html'){
							$url = "/member/agreement";
							$file_type = '';
							$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.';
						} elseif($tpl_path=='member/agreement.html'){
							$url = "/member/agreement?join_type=member";
							$file_type = '';
							$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.';
						} elseif(!$tpl_page && preg_match("/view\.html$/",$tpl_path)){
							$url = "#";
							$file_type = 'noview';
							$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.';
						} elseif($child=='joincheck'){
							$url = "#";
							$file_type = 'noview';
							$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.<br /><br />관리자환경 > 프로모션/쿠폰 > 출석체크 > 페이지꾸미기 버튼을 통해 접근해주세요.';
						} elseif(in_array($child,array('layout_TopBar','layout_MainTopBar'))){
							$url = "#";
							$file_type = 'layout';
							$file_type_msg = '별도의 미리보기 화면이 필요 없는 특별한 레이아웃 영역입니다 해당 영역이 보이는 페이지에서 바로 EYE-DESIGN하세요.';
						} else {
							$url = $tpl_page ? $this->layout->get_tpl_page_url($tpl_path) : "/".substr($tpl_path,0,strpos($tpl_path,'.'))."?designMode=1";

							if(preg_match("/tab_[0-9]*.html/",$file)){
								$url = $url = "../topbar/?no={$file}&designMode=1";
							}
						}

						if(in_array($child,array('popup')))
						{
							//$is_popup = true;
						}

						# 언더바로 시작하는 파일 미표시
						if(preg_match("/^_/",basename($file_path))){
							//$url = "#";
							//$is_include_file = true;
							continue;
						}

						# 미표시파일 목록 처리
						if(in_array($tpl_path,$this->hidden_page_list)) continue;

						$tpls[$i]['files'][] = array(
							'path'		=>	$tpl_path,
							'desc'		=>	$tpl_desc,
							'url'		=>	$url,
							'file_type'	=>	$file_type,
							'file_type_msg' => $file_type_msg,
						);
					}
				}
			}
		}

		// 게시판 목록
		$this->db->select('seq,id,name,skin');
		$query = $this->db->get('fm_boardmanager');
		$boards = $query->result_array();
		foreach($boards as $i=>$row){
			$child = "board/".$row['id']."/".$row['skin'];
			$board_skin_dir = $working_skin_path."/".$child;
			$map = directory_map($board_skin_dir,true,false);

			foreach((array)$map as $file){

				if(!preg_match("/\.html$/",$file)) continue;

				$file_type = 'normal';
				$file_type_msg = '';

				if($file=='write.html'){
					$url = "/board/write?id=".$row['id'];
					$file_type = 'noview';
					$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.';
				} elseif(preg_match("/view\.html$/",$file)){
					$url = "/board/write?id=".$row['id'];
					$file_type = 'board_view';
					$file_type_msg = '게시물 보기페이지를 정확하게 디자인하기 위해서 글을 올리시고 디자인 하는 것이 가장 좋습니다.<br />이제, 게시물 보기페이지도 글을 보면서 바로 EYE-DESIGN 하세요!';
				} else {
					$url = "/board/?id=".$row['id'];
				}

				$tpl_path = $child."/".$file;
				$file_path = $working_skin_path."/".$tpl_path;

				if(is_file($file_path)) {
					$tpl_desc = !empty($layout_config[$child."/".$file]['tpl_desc']) ? $layout_config[$child."/".$file]['tpl_desc'] : $file;

					$is_layout = false;

					$boards[$i]['files'][] =  array(
						'path'		=>	$tpl_path,
						'desc'		=>	$tpl_desc,
						'url'		=>	$url,
						'file_type' => $file_type,
						'file_type_msg' => $file_type_msg,
					);
				}
			}


		}

		$this->template->assign(array(
			"folders"=>$tpls,
			"boards"=>$boards,
			"skin_configuration"=>$skin_configuration,
		));

		$this->template->assign('template_path',$template_path);
		$this->template->assign('css_path','css/common.css');
		$file_path	= $this->skin.'/design/_panel.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}

	/* 전체 페이지 보기 */
	public function all_pages()
	{
		/* tpl_path assign */
		$tpl_path = $_GET['tpl_path']?$_GET['tpl_path']:null;
		$this->template->assign(array('skin'=>$this->designWorkingSkin,'tpl_path'=>$tpl_path));

		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin;

		$layout_config = layout_config_load($this->designWorkingSkin);

		$tpls = array();
		$tpls[] = array('name'=>'상단,하단,좌우',	'childs'=>array('layout_header','layout_footer','layout_side','layout_scroll'), 'is_layout_file'=>true, 'icon'=>'layout');
		foreach($this->folders as $key=>$value){
			if(!in_array($key,array('layout_header','layout_footer','layout_side','layout_scroll'))){
				$tpls[] = array('name'=>$value, 'childs'=>array($key) , 'icon'=>$key);
			}
		}

		foreach($tpls as $i=>$directory){
			$tpls[$i]['files'] = array();
			foreach($tpls[$i]['childs'] as $child){
				$map = directory_map($working_skin_path."/".$child,true,false);
				foreach((array)$map as $file){
					$tpl_path = $child."/".$file;
					$file_path = $working_skin_path."/".$tpl_path;
					if(is_file($file_path)) {
						$tpl_desc = !empty($layout_config[$child."/".$file]['tpl_desc']) ? $layout_config[$child."/".$file]['tpl_desc'] : $file;
						$tpl_page = !empty($layout_config[$child."/".$file]['tpl_page']) ? true : false;

						$file_type = 'normal';
						$file_type_msg = '';

						if(in_array($child,array('layout_header','layout_footer','layout_side','layout_scroll')))
						{
							$url = "#";
							$file_type = 'layout';
							$file_type_msg = '별도의 미리보기 화면이 필요 없는 특별한 레이아웃 영역입니다<br />상단/하단/측면/스크롤의 영역은 해당 영역이 보이는 페이지에서 바로 EYE-DESIGN하세요.';
						} elseif($tpl_path=='goods/view.html'){
							$url = "#";
							$file_type = 'goods_view';
							$file_type_msg = '상품상세페이지를 정확하게 디자인하기 위해서 최소 1개의 상품을 등록해 주세요.<br />이제, 상품등록하고 상품상세페이지를 보면서 바로 EYE-DESIGN 하세요!';
						} elseif($tpl_path=='order/settle.html'){
							$url = "#";
							$file_type = 'settle';
							$file_type_msg = '주문하기페이지를 정확하게 디자인하기 위해서는 테스트로 주문을 하면서 디자인 하는 것이 가장 좋습니다.<br />왜냐하면, 주문할 때는 회원,비회원,쿠폰,적립금,배송비 등 매우 복잡한 경우를 모두 분석해서 주문이 이뤄지기 때문입니다.<br />이제, 주문페이지도 주문을 하면서 바로 EYE-DESIGN 하세요!';
						} elseif(!$tpl_page && preg_match("/view\.html$/",$tpl_path)){
							$url = "#";
							$file_type = 'noview';
							$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.';
						} else {
							$url = $tpl_page ? $this->layout->get_tpl_page_url($tpl_path) : "/".substr($tpl_path,0,strpos($tpl_path,'.'))."?designMode=1";
						}

						if(in_array($child,array('popup')))
						{
							//$is_popup = true;
						}

						# 언더바로 시작하는 파일 미표시
						if(preg_match("/^_/",basename($file_path))){
							//$url = "#";
							//$is_include_file = true;
							continue;
						}

						# 미표시파일 목록 처리
						if(in_array($tpl_path,$this->hidden_page_list)) continue;

						$tpls[$i]['files'][] = array(
							'path'		=>	$tpl_path,
							'desc'		=>	$tpl_desc,
							'url'		=>	$url,
							'tpl_page'	=>	$tpl_page,
							'file_type' => $file_type,
							'file_type_msg' => $file_type_msg,
						);
					}
				}
			}
		}

		// 게시판 목록
		$this->db->select('seq,id,name,skin');
		$query = $this->db->get('fm_boardmanager');
		$boards = $query->result_array();
		foreach($boards as $i=>$row){
			$child = "board/".$row['id']."/".$row['skin'];
			$board_skin_dir = $working_skin_path."/".$child;
			$map = directory_map($board_skin_dir,true,false);

			foreach((array)$map as $file){

				if(!preg_match("/\.html$/",$file)) continue;

				$file_type = 'normal';
				$file_type_msg = '';

				if($file=='write.html'){
					$url = "/board/write?id=".$row['id'];
					$file_type = 'noview';
					$file_type_msg = '화면을 미리 확인할 수 없는 페이지입니다.';
				} elseif(preg_match("/view\.html$/",$file)){
					$url = "/board/write?id=".$row['id'];
					$file_type = 'board_view';
					$file_type_msg = '게시물 보기페이지를 정확하게 디자인하기 위해서 글을 올리시고 디자인 하는 것이 가장 좋습니다.<br />이제, 게시물 보기페이지도 글을 보면서 바로 EYE-DESIGN 하세요!';
				} else {
					$url = "/board/?id=".$row['id'];
				}

				if(in_array($child,array('popup')))
				{
					//$is_popup = true;
				}

				# 언더바로 시작하는 파일 미표시
				if(preg_match("/^_/",basename($file_path))){
					//$url = "#";
					//$is_include_file = true;
					continue;
				}

				# 미표시파일 목록 처리
				if(in_array($tpl_path,$this->hidden_page_list)) continue;



				$tpl_path = $child."/".$file;
				$file_path = $working_skin_path."/".$tpl_path;

				if(is_file($file_path)) {
					$tpl_desc = !empty($layout_config[$child."/".$file]['tpl_desc']) ? $layout_config[$child."/".$file]['tpl_desc'] : $file;

					$is_layout = false;

					$boards[$i]['files'][] =  array(
						'path'		=>	$tpl_path,
						'desc'		=>	$tpl_desc,
						'url'		=>	$url,
						'file_type' => $file_type,
						'file_type_msg' => $file_type_msg,
					);
				}
			}

		}

		$this->template->assign(array(
			"folders_count"=>count($tpls),
			"folders"=>$tpls,
			"boards"=>$boards
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 레이아웃 설정 화면 */
	public function layout()
	{
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$mode = isset($_GET['mode']) ? $_GET['mode'] : null;

		switch($mode){
			case "create" :
				$title = "새 페이지 만들기";
				$this->template->assign(array('title'=>$title,'mode'=>$mode));

				/* tpl_path assign */
				$tpl_path = 'basic';
				$this->template->assign(array(
					'designWorkingSkin'=>$this->designWorkingSkin,
					'tpl_path'=>$tpl_path,
					'folders'=>$this->folders
				));

			break;
			case "edit" :
				$title = "레이아웃/폰트/배경색 설정";
				$this->template->assign(array('title'=>$title,'mode'=>$mode));

				/* tpl_path assign */
				$tpl_path = $_GET['tpl_path']?$_GET['tpl_path']:null;
				$this->template->assign(array('designWorkingSkin'=>$this->designWorkingSkin,'tpl_path'=>$tpl_path));

			break;
			default:
				exit;
			break;
		}

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_autoload($this->designWorkingSkin,$tpl_path);


		/* 레이아웃 영역별 설정 assign */
		$layout_header_config	= layout_config_folder_load($this->designWorkingSkin,'layout_header');
		$layout_TopBar_config	= layout_config_folder_load($this->designWorkingSkin,'layout_TopBar');
		$layout_footer_config	= layout_config_folder_load($this->designWorkingSkin,'layout_footer');
		$layout_side_config		= layout_config_folder_load($this->designWorkingSkin,'layout_side');
		$layout_scroll_config	= layout_config_folder_load($this->designWorkingSkin,'layout_scroll');

		$this->template->assign(array(
			"skin"					=> $this->designWorkingSkin,
			"layout_header_config"	=>	$layout_header_config,
			"layout_TopBar_config"	=>	$layout_TopBar_config,
			"layout_footer_config"	=>	$layout_footer_config,
			"layout_side_config"	=>	$layout_side_config,
			"layout_scroll_config"	=>	$layout_scroll_config,
		));

		/* 측면영역 강제 숨김처리 */
		/*
		if($this->layout->is_fullsize_absolutly($tpl_path)){
			$this->template->assign(array('is_fullsize_absolutly'=>true));
		}
		*/

		// 유료 폰트 사용시 불러오기
		$today = date('Y-m-d',time());
		$this->load->helper('readurl');
		$requestUrl = "http://font.firstmall.kr/engine/font_list.php";
		$font_out = readurl($requestUrl,array('shop_no' => $this->config_system['shopSno']));
		if($font_out){
			$r_font_obj = json_decode($font_out);
		}
		foreach($r_font_obj as $obj){
			$result_font[] = array(
				'service_seq'=>$obj->service_seq,
				'font_seq'=>$obj->font_seq,
				'font_face'=>$obj->font_face,
				'basic_font_yn'=>$obj->basic_font_yn,
				'font_name'=>$obj->font_name
			);
		}
		$this->template->assign(array('loop_font'=>$result_font));
		$this->template->assign($layout_config[$tpl_path]);


		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 스킨 설정 화면 > 디자인작업용스킨 목록 부분 ajax */
	public function get_skin_list_html()
	{
		$this->tempate_modules();

		$skinPrefix = !empty($_GET['skinPrefix']) ? $_GET['skinPrefix'] : '';
		$this->template->assign(array('skinPrefix'=>$skinPrefix));

		/* 보유한 스킨 목록 가져오기 */
		$my_skin_list = $this->designmodel->get_skin_list($skinPrefix);

		$this->template->assign(array('my_skin_list'=>$my_skin_list));

		/* 스킨 현재상태 아이콘 */
		$my_skin_list_icon = array();
		foreach($my_skin_list as $k=>$v){
			$my_skin_list_icon[$k] = array();
			if		($skinPrefix=='mobile'){
				if($this->realMobileSkin == $v['skin']) $my_skin_list_icon[$k][] = "실제적용";
				if($this->workingMobileSkin == $v['skin']) $my_skin_list_icon[$k][] = "디자인작업용";
			}elseif	($skinPrefix=='fammerce'){
				if($this->realFammerceSkin == $v['skin']) $my_skin_list_icon[$k][] = "실제적용";
				if($this->workingFammerceSkin == $v['skin']) $my_skin_list_icon[$k][] = "디자인작업용";
			}elseif	($skinPrefix=='store'){
				if($this->realStoreSkin == $v['skin']) $my_skin_list_icon[$k][] = "실제적용";
				if($this->workingStoreSkin == $v['skin']) $my_skin_list_icon[$k][] = "디자인작업용";
			}elseif	($skinPrefix=='storemobile'){
				if($this->realStoremobileSkin == $v['skin']) $my_skin_list_icon[$k][] = "실제적용";
				if($this->workingStoremobileSkin == $v['skin']) $my_skin_list_icon[$k][] = "디자인작업용";
			}elseif	($skinPrefix=='storefammerce'){
				if($this->realStorefammerceSkin == $v['skin']) $my_skin_list_icon[$k][] = "실제적용";
				if($this->workingStorefammerceSkin == $v['skin']) $my_skin_list_icon[$k][] = "디자인작업용";
			}else	{
				if($this->realSkin == $v['skin']) $my_skin_list_icon[$k][] = "실제적용";
				if($this->workingSkin == $v['skin']) $my_skin_list_icon[$k][] = "디자인작업용";
			}
		}
		$this->template->assign(array('my_skin_list_icon'=>$my_skin_list_icon));
		$this->template->assign(array('skinPrefix'=>$skinPrefix));

		/* 초기 선택 스킨 */
		if		($skinPrefix=='mobile'){
			if(!$_GET['checkedSkin']) $_GET['checkedSkin'] = $this->realMobileSkin;
		}elseif	($skinPrefix=='fammerce'){
			if(!$_GET['checkedSkin']) $_GET['checkedSkin'] = $this->realFammerceSkin;
		}elseif	($skinPrefix=='store'){
			if(!$_GET['checkedSkin']) $_GET['checkedSkin'] = $this->realStoreSkin;
		}elseif	($skinPrefix=='storemobile'){
			if(!$_GET['checkedSkin']) $_GET['checkedSkin'] = $this->realStoremobileSkin;
		}elseif	($skinPrefix=='storefammerce'){
			if(!$_GET['checkedSkin']) $_GET['checkedSkin'] = $this->realStorefammerceSkin;
		}else	{
			if(!$_GET['checkedSkin']) $_GET['checkedSkin'] = $this->realSkin;
		}

		$file_path	= $this->skin.'/design/_skinlist.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}

	/* 소스 편집화면 */
	public function sourceeditor(){
		$this->tempate_modules();
		$this->load->helper('file');
		$this->load->helper('directory');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* tpl_path assign */
		$skin = $this->designWorkingSkin;
		$skinPath = ROOTPATH."data/skin/";
		$tpl_path = $_GET['tpl_path']?$_GET['tpl_path']:null;
		$tpl_realpath = $skinPath.$skin."/".$tpl_path;
		$tpl_fileName = basename($tpl_realpath);
		$tpl_source = read_file($tpl_realpath);
		$searchKeyword = isset($_GET['searchKeyword']) ? $_GET['searchKeyword'] : '';

		/* CSS폴더 파일목록 */
		if(preg_match("/^css\/(.*).css$/",$tpl_path)){
			$css_files = array();
			$css_map = (array)directory_map(dirname($tpl_realpath),true);
			rsort($css_map);
			foreach($css_map as $k=>$v){
				if(preg_match("/(.*).css$/",$v)){
					$desc = "사용자 정의 CSS";
					if($v=='common.css') $desc = "스킨 공통 CSS";
					if($v=='buttons.css') $desc = "버튼 스타일 CSS";
					if($v=='board.css') $desc = "일반 게시판 CSS";
					if($v=='mypage_board.css') $desc = "마이페이지 게시판 CSS";
					if($v=='goods_board.css') $desc = "상품후기,상품문의 게시판 CSS";

					$css_files[] = array(
						'desc'		=> $desc,
						'filename'	=> $v,
						'path'		=> 'css/'.$v,
						'current'	=> $tpl_fileName==$v ? 1 : 0
					);
				}
			}
			$this->template->assign(array('css_files'=>$css_files));
		}

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$tpl_path);
		$this->template->assign(array('layout_config'=>$layout_config[$tpl_path]));

		/* 백업파일 */
		$backup_files = array();
		$skinBackupPath = "data/skin_backup"."/".$skin."/".$tpl_path.date('.YmdHis');
		$skinBackupFileName = basename($skinBackupPath);
		$skinBackupDir = dirname($skinBackupPath);
		$tpl_fileNameForReg = str_replace('.','\.',$tpl_fileName);
		$map = (array)directory_map(ROOTPATH.$skinBackupDir,true);
		rsort($map);
		foreach($map as $k=>$v){
			if(is_file(ROOTPATH.$skinBackupDir.'/'.$v) && preg_match("/{$tpl_fileNameForReg}\.[0-9]{14}/",$v)){
				$backup_files[] = array(
					'path' => $skinBackupDir.'/'.$v,
					'time' => filemtime(ROOTPATH.$skinBackupDir.'/'.$v)
				);

			}
		}

		if(preg_match("/\.css$/",$tpl_fileName)){
			$code_mode = "css";
		}else{
			$code_mode = "htmlmixed";
		}

		if(preg_match("/^board\/([^\/]*)/",$tpl_path,$matches)){
			$source_url = "/board/?id=".$matches[1];
		}else{
			$source_url = $layout_config[$tpl_path]['tpl_page'] ? $this->layout->get_tpl_page_url($tpl_path) : "/".substr($tpl_path,0,strpos($tpl_path,'.'));
		}

		$this->template->assign(array(
			'skin'			=> $skin,
			'tpl_path'		=> $tpl_path,
			'tpl_source'	=> $tpl_source,
			'backup_files'	=> $backup_files,
			'searchKeyword'	=> $searchKeyword,
			'code_mode'		=> $code_mode,
			'source_url'	=> $source_url
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 원본소스 보기 */
	public function source_view_popup(){
		$this->tempate_modules();
		$this->load->helper('file');
		$this->load->helper('readurl');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* tpl_path assign */
		$mode = $_GET['mode'];
		$tpl_path = $_GET['tpl_path']?$_GET['tpl_path']:null;
		$skin = !empty($_GET['skin']) ? $_GET['skin'] : $this->designWorkingSkin;
		$skinPath = ROOTPATH."data/skin/";

		switch($mode){
			case "original":
				/* 중계서버에서 원본스킨 소스 가져오기 */
				$skin_configuration = skin_configuration($skin);
				$originalSkin = $skin_configuration['originalSkin'];
				$param = array(
					'cmd'			=>	'skinFileSource',
					'skin'			=>	$originalSkin,
					'tpl_path'		=>	$tpl_path,
					'service_code'	=>	SERVICE_CODE,
					'hosting_code'	=>	$this->config_system['service']['hosting_code'],
					'subDomain'		=>	$this->config_system['subDomain'],
					'domain'		=>	$this->config_system['domain'],
					'hostDomain'	=>	$_SERVER['HTTP_HOST'],
					'shopSno'		=>	$this->config_system['shopSno'],
				);
				$url = "http://interface.firstmall.kr/firstmall_plus/request.php";
				$tpl_source = readurl($url,$param);

				$this->template->assign(array('skin' => $skin));
			break;
			case "backup":
				$filePath = ROOTPATH.$tpl_path;
				$tpl_source = read_file($filePath);
			break;
			case "category":
				$tpl_source = readurl("http://".$_SERVER['HTTP_HOST'].'/common/category_navigation_html?tpl_path='.urlencode($tpl_path));
			break;
			case "brand":
				$tpl_source = readurl("http://".$_SERVER['HTTP_HOST'].'/common/brand_navigation_html?tpl_path='.urlencode($tpl_path));
			break;
			case "location":
				$tpl_source = readurl("http://".$_SERVER['HTTP_HOST'].'/common/location_navigation_html?tpl_path='.urlencode($tpl_path));
			break;
		}

		if(preg_match("/\.css$/",$tpl_path)){
			$code_mode = "css";
		}else{
			$code_mode = "htmlmixed";
		}

		$this->template->assign(array(
			'tpl_path'		=> $tpl_path,
			'tpl_source'	=> $tpl_source,
			'code_mode'		=> $code_mode
		));

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$tpl_path);
		$this->template->assign(array('layout_config'=>$layout_config[$tpl_path]));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 원본소스 보기 */
	public function file_view_popup(){
		$this->tempate_modules();
		$this->load->helper('file');
		$this->load->helper('readurl');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		/* tpl_path assign */
		$mode = $_GET['mode'];
		$tpl_path = $_GET['tpl_path']?$_GET['tpl_path']:null;

		$filePath = ROOTPATH.$tpl_path;
		$tpl_source = read_file($filePath);

		if(preg_match("/\.css$/",$tpl_path)){
			$code_mode = "css";
		}else{
			$code_mode = "htmlmixed";
		}

		$this->template->assign(array(
			'tpl_path'		=> $tpl_path,
			'tpl_source'	=> $tpl_source,
			'code_mode'		=> $code_mode
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 이미지 변경 화면 */
	public function image_edit(){
		$this->tempate_modules();
		$this->load->model('layout');

		$link = isset($_GET['link']) ? urldecode($_GET['link']) : null;
		$target = isset($_GET['target']) ? $_GET['target'] : null;
		$elementType = isset($_GET['elementType']) ? $_GET['elementType'] : null;

		$designTplPath = base64_decode($_GET['designTplPath']);
		$designImgSrc = base64_decode($_GET['designImgSrc']);
		$designImgSrcOri = base64_decode($_GET['designImgSrcOri']);
		$designImageLabel = $_GET['designImageLabel'];
		$designImgPath = preg_replace("/^\//","",$designImgSrc);

		if(preg_match("/\{(.*)\}/",$designImgSrc) && $_GET['viewSrc']){
			//openDialogAlert("치환코드로 출력되는 이미지는 변경할 수 없습니다.",400,140,'parent',"parent.DM_window_close();");
			//exit;
			$designImgSrc = $_GET['viewSrc'];
			$designImgPath = preg_replace("/^\//","",$_GET['viewSrc']);
			$isReplacedCode = true;
		}

		$tmp = explode('/',$designTplPath);
		array_shift($tmp);
		$tplPath = implode('/',$tmp);


		/* 이미지가로세로 크기 */
		@list($designImgWidth, $designImgHeight) = @getimagesize($designImgPath);

		/* 이미지 용량 */
		$designImgSize = @filesize($designImgPath);

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$tplPath);
		$this->template->assign(array('layout_config'=>$layout_config[$tplPath]));

		$this->template->assign(array(
			'link'				=> $link,
			'target'			=> $target,
			'elementType'		=> $elementType,
			'designTplPath'		=> $designTplPath,
			'designImgSrc'		=> $designImgSrc,
			'designImgSrcOri'	=> $designImgSrcOri,
			'designImageLabel'	=> $designImageLabel,
			'designImgPath'		=> $designImgPath,
			'designImgScale'	=> "{$designImgWidth} x {$designImgHeight}",
			'designImgSize'		=> $designImgSize,
			'tplPath'			=> $tplPath,
			'frequentUrls' => $this->layout->get_frequent_url(),
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 이미지 넣기 화면 */
	public function image_insert(){
		$this->tempate_modules();
		$this->load->model('layout');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$tplPath = $_GET['designTplPath'];
		$folder = dirname($tplPath);

		switch($folder){
			case "layout_header":
				$up_image_name = "img_layout_header_up";
				$down_image_name = "img_layout_header_down";
			break;
			case "layout_footer":
				$up_image_name = "img_layout_footer_up";
				$down_image_name = "img_layout_footer_down";
			break;
			case "layout_side":
				$up_image_name = "img_layout_side_up";
				$down_image_name = "img_layout_side_down";
			break;
			case "layout_scroll":
				if($tplPath=='layout_scroll/left.html'){
					$up_image_name = "img_layout_scroll_l_up";
					$down_image_name = "img_layout_scroll_l_down";
				}else{
					$up_image_name = "img_layout_scroll_up";
					$down_image_name = "img_layout_scroll_down";
				}
			break;
			default:
				$up_image_name = "img_layout_up";
				$down_image_name = "img_layout_down";
			break;
		}
		$this->template->assign(array('up_image_name'=>$up_image_name,'down_image_name'=>$down_image_name));

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$tplPath);
		$this->template->assign(array('layout_config'=>$layout_config[$tplPath]));

		$this->template->assign(array(
			'tplPath' => $tplPath,
			'frequentUrls' => $this->layout->get_frequent_url()
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 플래시 넣기 화면 */
	public function flash_insert(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 플래시 생성 화면 */
	public function flash_create(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		$return_url = "http://".$_SERVER['HTTP_HOST']."/admin/design/flash_insert?template_path=".urlencode($template_path);

		$this->template->assign(array('template_path' => $template_path,'return_url' => $return_url));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 플래시 수정 화면 */
	public function flash_edit(){

		$this->load->library('SofeeXmlParser');

		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$flash_seq = $_GET['flash_seq'];
		$template_path = $_GET['template_path'];

		$query = $this->db->query("select a.*,b.url xmlurl from fm_design_flash a,fm_design_flash_file b where a.flash_seq=? and a.flash_seq=b.flash_seq and b.type='xml' and url like '%data.xml' limit 1",$flash_seq);
		$flash_data = $query->row_array();

		if(!file_exists(ROOTPATH.$flash_data['xmlurl'])){
			echo "XML 파일이 존재하지 않습니다.";
			exit;
		}

		$xmlParser = new SofeeXmlParser();
		$xml_url = "http://".$_SERVER['HTTP_HOST'].$flash_data['xmlurl'];

		$xmlParser->parseFile($xml_url);
		$tree = $xmlParser->getTree();

		if($tree['data']['option'])
		{
			$options = $tree['data']['option'];
		} else {
			$options = $tree['data'];
			$this->template->assign(array('productExpendViewer'=>true));
		}

		if($tree['data']['item']){
			$items[0] = $tree['data']['item'];
			if(is_numeric(key($tree['data']['item'])) ){
				$items = $tree['data']['item'];
			}
		} else {
			$items[0] = $tree['data'];
			if(is_numeric(key($tree['data'])) ){
				$items = $tree['data'];
			}
		}

		$flashmagicxmldir = "/data/flash/xml/";

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array(
			'data'=>$flash_data,
			'template_path' => $template_path,
			'flash_seq'=>$flash_seq,
			'options'=>$options,
			'items'=>$items,
			'first_item'=>$items[0]
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 플래시 목록부분 ajax */
	public function get_flash_list_html(){
		$this->tempate_modules();

		/* 플래시 목록 가져오기 */
		$sql = 'select SQL_CALC_FOUND_ROWS *,(select url from fm_design_flash_file where flash_seq=a.flash_seq and type="img" and (url like "%flash_%" or url like "%thumb%"  ) limit 1) url from fm_design_flash a order by flash_seq desc';
		$query = $this->db->query($sql);
		$flash_list = $query->result_array();

		$this->template->assign(array('flash_list'=>$flash_list));

		$file_path	= $this->skin.'/design/flash_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	/* 동영상 넣기 화면 */
	public function video_insert(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */


		$cfg_goods = config_load("goods");
		if( $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ){
			$cfg_goods['video_use']=  'Y';
		}
		$this->template->assign('cfg_goods',$cfg_goods);

		$this->template->assign(array('setMode' => $this->session->userdata('setMode')));
		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}


	/* 동영상 넣기 >> 수정 화면 */
	public function video_edit(){
		$this->load->model('videofiles');
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
		$videoSeq = ($_POST['videoSeq'])?$_POST['videoSeq']:$_GET['videoSeq'];
		$sc['seq'] = $videoSeq;
		$videodata = $this->videofiles->get_data($sc);
		$this->template->assign($videodata);


		$this->template->assign(array('setMode' => $this->session->userdata('setMode')));
		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->assign(array('realwidth' => $_GET['realwidth']));
		$this->template->assign(array('realheight' => $_GET['realheight']));
		$this->template->assign(array('setMode' => $this->session->userdata('setMode')));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 동영상 생성 화면 */
	public function video_create(){
		$this->load->model('videofiles');
		$this->load->helper('readurl');

		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
		$this->template->assign(array('setMode' => $this->session->userdata('setMode')));
		if($_POST['file_key_W']) {
			$file_key_w = $_POST['file_key_W'];//웹 인코딩 코드
		}
		if($_POST['file_key_I']) {
			$file_key_i = $_POST['file_key_I'];//스마트폰 인코딩 코드
		}

		/* 파라미터 검증*/
		if($file_key_w || $file_key_i) {
			$videofiles['upkind']					= 'design';
			$videofiles['mbseq']					= $this->managerInfo['manager_seq'];//
			$videofiles['r_date']					= date("Y-m-d H:i:s");
			$videofiles['file_key']				= $_POST['file_key'];//
			$videofiles['file_key_w']			= $file_key_w;//웹 인코딩 코드
			$videofiles['file_key_i']				= $file_key_i;//웹 인코딩 코드

			$videoinforesult = readurl(uccdomain('fileinfo',$file_key_w));
			if($videoinforesult){
				$videoinfoarr = xml2array($videoinforesult);
				$videofiles['playtime']		 = ($videoinfoarr['class']['playtime'])?$videoinfoarr['class']['playtime']:'';
				$playtime = $videofiles['playtime'];
			}
			$videofiles['memo']				= $_POST['memo'];//
			$videofiles['encoding_speed']	= ($_POST['encoding_speed'])?$_POST['encoding_speed']:400;
			$videofiles['encoding_screen'] = (is_array($_POST['encoding_screen'])) ? @implode("X",($_POST['encoding_screen'])):'400X300';

			$videoseq = $this->videofiles->videofiles_write($videofiles);
		}

		if( $_POST['file_key_W'] || $_POST['file_key_I'] ) {
			if($playtime){
				$this->template->assign("playtime",$playtime.'초');
			}
			$this->template->assign("r_date",date("Y-m-d H:i:s"));
			$this->template->assign("thumbnailsrc",uccdomain('thumbnail',$file_key_w));
			$this->template->assign("videoseq",$videoseq);
			if( $this->_is_mobile_agent ){
				$this->template->assign("ismobileagent",true);
				if($file_key_i){
					$uccdomainembedsrc = uccdomain('fileurl',$file_key_i);
				}else{
					$uccdomainembedsrc = uccdomain('fileurl',$file_key_w);
				}
			}else{
				if($file_key_i){
					$uccdomainembedsrc = uccdomain('fileswf',$file_key_i);
				}else{
					$uccdomainembedsrc = uccdomain('fileswf',$file_key_w);
				}
			}

			$this->template->assign("uccdomainembedsrc",$uccdomainembedsrc);
			$this->template->assign("file_key_w",$file_key_w);
			$this->template->assign("file_key_i",$file_key_i);
			$this->template->assign("encoding_screen",$_POST['encoding_screen']);
			$this->template->assign("encoding_speed",$_POST['encoding_speed']);


			$this->template->assign("videook",true);
		}else{
			$this->template->assign("videook",false);
		}
		//동영상연결(기본 파일찾기)
		$this->template->assign("uccdomain",uccdomain());
		if( $_POST['error']) {
			$this->template->assign("videoerror",true);
			$this->template->assign("error",$_POST['error']);
		}else{
			$this->template->assign("videoerror",false);
		}

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 동영상 URL 화면 */
	public function video_url(){
		$this->template->assign("realvideourl",$_GET['realvideourl']);
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 동영상 목록부분 ajax */
	public function get_video_list_html(){
		$this->tempate_modules();

		/* 동영상 목록 가져오기 */
		$this->load->model('videofiles');
		$this->load->helper('readurl');


		/**
		 * list setting
		**/
		$videosc['orderby']	= (!empty($_GET['orderby'])) ?	$_GET['orderby']:' seq desc, sort asc ';
		$videosc['sort']		= (!empty($_GET['sort'])) ?			$_GET['sort']:'';
		$videosc['page']		= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$videosc['perpage']	= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):20;

		$videosc['upkind']	= 'design';
		$video_list = $this->videofiles->videofiles_list($videosc);//debug_var($video_list);

		$videosc['searchcount']	= $video_list['count'];
		$videosc['total_page']		= ceil($videosc['searchcount']	 / $videosc['perpage']);
		$videosc['totalcount']		= $this->videofiles->get_item_total_count($videosc);

		if($video_list['result']) $this->template->assign('video_list',$video_list['result']);

		$returnurl = "./video_insert?template_path=".$_GET['template_path'];
		$paginlay =  pagingtag($videosc['searchcount']	,$videosc['perpage'],$returnurl, getLinkFilter('',array_keys($videosc)),'page','" ' );

		if($videosc['searchcount'] > 0) {
			$paginlay = (!empty($paginlay)) ? $paginlay:'<p><a class="on red">1</a><p>';
		}
		$this->template->assign('videopagin',$paginlay);


		$file_path	= $this->skin.'/design/video_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 팝업 생성,수정 화면 */
	public function popup_edit(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$popup_seq = $_GET['popup_seq'];
		$template_path = $_GET['template_path'];

		/* 팝업 목록 가져오기 */
		$query = $this->db->query("select * from fm_design_popup where popup_seq = ?",$popup_seq);
		$popup_data = $query->row_array();

		if(!$popup_data['bar_background_color']){
			$popup_data['bar_background_color'] = '#eeeeee';
		}

		if(!is_object(json_decode($popup_data['bar_msg_today_decoration']))){
			$popup_data['bar_msg_today_decoration'] = json_encode(array('color'=>'#000000'));
		}

		if(!is_object(json_decode($popup_data['bar_msg_close_decoration']))){
			$popup_data['bar_msg_close_decoration'] = json_encode(array('color'=>'#000000'));
		}

		if($popup_data['contents']===null){
			$popup_data['width'] = 380;
			$popup_data['height'] = 350;
			$popup_data['contents'] = file_get_contents(ROOTPATH.'admin/skin/'.$this->skin.'/design/_popup_default_source.html');
		}

		// 팝업 스타일
		$popup_styles = $this->designmodel->get_popup_styles();
		$this->template->assign(array('popup_styles'=>$popup_styles));

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('data'=>$popup_data, 'template_path' => $template_path, 'popup_seq'=>$popup_seq));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 팝업 띄우기 화면 */
	public function popup_insert(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 팝업 목록부분 ajax */
	public function get_popup_list_html(){
		$this->tempate_modules();

		// 팝업 스타일
		$popup_styles = $this->designmodel->get_popup_styles();
		$this->template->assign(array('popup_styles'=>$popup_styles));

		/* 팝업 목록 가져오기 */
		$query = $this->db->query("select * from fm_design_popup order by popup_seq desc");
		$popup_list = $query->result_array();

		$now_time = time();
		foreach($popup_list as $k=>$v){
			switch($v['status']){
				case 'show':
					$popup_list[$k]['status_msg'] = "진행";
				break;
				case 'period':
					if($now_time < strtotime($v['period_s'])){
						$popup_list[$k]['status_msg'] = "대기";
					}elseif($now_time < strtotime($v['period_e'])){
						$popup_list[$k]['status_msg'] = "진행";
					}elseif($now_time >= strtotime($v['period_e'])){
						$popup_list[$k]['status_msg'] = "종료";
					}
				break;
				case 'stop':
					$popup_list[$k]['status_msg'] = "중지";
				break;
			}
		}

		$this->template->assign(array('popup_list'=>$popup_list));

		$file_path	= $this->skin.'/design/popup_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	/* 상품디스플레이 생성,수정 화면 */
	public function display_edit(){
		$this->tempate_modules();
		$this->load->model('goodsmodel');
		$this->load->model('goodsdisplay');
		$this->load->model('eventmodel');
		$this->load->helper('text');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		if($_GET['kind']=='category'){
			$this->load->model('categorymodel');
			$display_seq = $this->categorymodel->get_category_recommend_display_seq($_GET['category_code']);
		}else if($_GET['kind']=='brand'){
			$this->load->model('brandmodel');
			$display_seq = $this->brandmodel->get_brand_recommend_display_seq($_GET['category_code']);
		}else if($_GET['kind']=='location'){
			$this->load->model('locationmodel');
			$display_seq = $this->locationmodel->get_location_recommend_display_seq($_GET['location_code']);
		}else if($_GET['kind']=='relation'){
			$display_seq = $this->goodsmodel->get_goods_relation_display_seq();
		}else{
			$display_seq = $_GET['display_seq'];
		}

		$template_path = $_GET['template_path'];
		$image_decorations = array();

		/* 상품디스플레이 정보 가져오기 */
		$display_data = $this->goodsdisplay->get_display($display_seq);
		$display_tabs = $this->goodsdisplay->get_display_tab($display_seq);

		$platform = $display_data['platform'] ? $display_data['platform'] : $_GET['platform'];
		$platform = $platform ? $platform : 'pc';
		$this->template->assign(array('platform'=>$platform));

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		/* 디스플레이 상품 목록 */
		$display_item = array();
		if($display_seq){
			foreach($display_tabs as $k=>$v){
				$display_tabs[$k]['items'] = $this->goodsdisplay->get_display_item($display_seq,$k);
			}

			/* 이미지 꾸미기 값 파싱 */
			$image_decorations = $this->goodsdisplay->decode_image_decorations($display_data['image_decorations']);
		}

		/* 샘플 상품 정보 */
		$sampleGoodsInfo = array(
			'goods_seq' => '',
			'goods_name' => '샘플 상품',
			'price' => '19800',
			'consumer_price' => '24800',
			'image_cnt' => 2,
			'image2' => '/admin/skin/default/images/design/img_effect_sample2.gif',
		);

		/* 이벤트페이지에 상품디스플레이 넣을경우 체크 */
		if($this->eventmodel->is_event_template_file($template_path)){
			$this->template->assign(array('eventpage'=>1));
		}

		/* 구매조건 페이지에 상품디스플레이 넣을경우 체크 */
		if($this->eventmodel->is_gift_template_file($template_path)){
			$this->template->assign(array('giftpage'=>1));
		}

		$cfg_goods = config_load("goods");
		if( $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ){
			$cfg_goods['video_use']=  'Y';
		}
		$this->template->assign('cfg_goods',$cfg_goods);

		if( $_GET['displaykind'] == 'designvideo' || $display_data['kind'] == 'designvideo' ){
			$this->template->assign('styles',$this->goodsdisplay->get_videostyles());
		}else{
			$styles = $this->goodsdisplay->get_styles();
			if($display_data['kind']=='relation'){
				unset($styles['lattice_b']);
				unset($styles['list']);
			}
			$this->template->assign('styles',$styles);
		}

		$goodsImageSize = config_load('goodsImageSize');
		@asort($goodsImageSize);
		
		$this->template->assign(array(
			'template_path'		=> $template_path,
			'data'				=> $display_data,
			'imageIcons'		=> $this->goodsdisplay->get_image_icons(),
			'goodsImageSizes'	=> $goodsImageSize,
			'display_seq'		=> $display_seq,
			'display_tabs'		=> $display_tabs,
			'image_decorations' => $image_decorations,
			'sampleGoodsInfo'	=> $sampleGoodsInfo
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function display_image_icon(){
		$this->load->model('goodsdisplay');
		$icon = $this->goodsdisplay->get_image_icons();
		echo json_encode( $icon );
	}

	public function display_image_send(){
		$this->load->model('goodsdisplay');
		$icon = $this->goodsdisplay->get_image_icons('send');
		echo json_encode( $icon );
	}

	public function display_image_zzim(){
		$this->load->model('goodsdisplay');
		$icon = $this->goodsdisplay->get_image_icons('zzim');
		echo json_encode( $icon );
	}

	public function display_image_zzim_on(){
		$this->load->model('goodsdisplay');
		$icon = $this->goodsdisplay->get_image_icons('zzim_on');
		echo json_encode( $icon );
	}

	public function display_image_slide(){
		$this->load->model('goodsdisplay');
		$icon = $this->goodsdisplay->get_image_icons('slide');
		echo json_encode( $icon );
	}

	/* 상품디스플레이 띄우기 화면 */
	public function display_insert(){
		$this->tempate_modules();
		$this->load->model('goodsdisplay');

		if(!$_GET['platform']) $_GET['platform'] = 'pc';

		/* 기본 상품 디스플레이 생성 */
		for($i=1;$i<=10;$i++){
			$res = $this->goodsdisplay->get_display($i);
			if(!$res){
				$data = array(
					'display_seq' => $i,
					'admin_comment' => '기본 상품 디스플레이 '.$i,
					'regdate' => date('Y-m-d H:i:s')
				);
				$query = $this->db->insert_string('fm_design_display', $data);
				$this->db->query($query);
			}
		}

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 상품디스플레이 목록부분 ajax */
	public function get_display_list_html(){
		$this->tempate_modules();
		$this->load->model('goodsdisplay');
		/* 상품디스플레이 목록 가져오기 */
		if( $_GET['displaykind'] == 'designvideo'){
			$kindsql = 'designvideo';
			$platformsql = 'pc';
		}else{
			$kindsql = 'design';
			$platformsql = $_GET['platform'] ? $_GET['platform'] : 'pc';
		}
		$query = $this->db->query("
			select *,
				(select count(*) from fm_design_display_tab_item as b where a.display_seq = b.display_seq) as goodsCnt
			from fm_design_display as a
			where kind='{$kindsql}' and platform='{$platformsql}'
			order by display_seq desc
		");
		$display_list = $query->result_array();

		$this->template->assign(array('display_list'=>$display_list));

		$styles = $this->goodsdisplay->get_styles();
		$this->template->assign(array('styles'=>$styles));

		$file_path	= $this->skin.'/design/display_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 게시판 넣기 화면 */
	public function lastest_insert(){
		$this->tempate_modules();
		$this->load->model('layout');
		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		$query = $this->db->query("select * from fm_boardmanager where id not in ('gs_seller_qna','gs_seller_notice','bulkorder') ");
		$boardList = $query->result_array();
		$this->template->assign(array('boardList'=>$boardList));

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array(
			'styles'			=> $this->Boardmanager->styles
		));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 게시판 넣기 변경 화면 */
	public function lastest_edit(){
		$this->tempate_modules();
		$this->load->model('layout');

		$link = isset($_GET['link']) ? $_GET['link'] : null;
		$target = isset($_GET['target']) ? $_GET['target'] : null;
		$elementType = isset($_GET['elementType']) ? $_GET['elementType'] : null;

		$designTplPath = base64_decode($_GET['designTplPath']);
		$designLastestId = $_GET['designLastestId'];

		$tmp = explode('/',$designTplPath);
		array_shift($tmp);
		$tplPath = implode('/',$tmp);

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$tplPath);
		$this->template->assign(array('layout_config'=>$layout_config[$tplPath]));

		$this->template->assign(array(
			'tplPath'		=> $designTplPath,
			'designLastestId'	=> $designLastestId
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}


	/* 가비아 웹FTP */
	public function gabia_webftp(){
		header("location:http://firstmall.kr/popup/webftp/index_firstmall.php?s={$_SERVER['HTTP_HOST']}");
	}

	/* PC 상단바 디자인 설정 JHR */
	public function topBar_design(){


		$this->tempate_modules();

		$template_path = isset($_GET['template_path']) ? $_GET['template_path'] : '';
		$location = dirname($template_path);

		$category_config = skin_configuration($this->designWorkingSkin);
		if(!isset($category_config['topbar'])) $category_config['topbar'] = '';
		$direction = substr($category_config['topbar'],0,1);
		$topbar = explode("|",$category_config['topbar']);
		$this->template->assign(array(
			'allcategory'		=>$topbar[1],
			'category'			=>$topbar[2],
			'brand'				=>$topbar[3],
			'location'			=>$topbar[4],
			'template_path'		=>'_modules/category/category_topBar.html'
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");

	}

	/* 모바일 상단바 디자인 설정 JHR */
	public function mainTopBar_design(){
		$this->tempate_modules();
		$query = $this->db->query("select * from fm_topbar_style  left join fm_topbar_file  on  tab_index = style_index order by tab_seq");
		$data = $query->result_array();
		$list["tab_index"] = $data[0]["tab_index"];
		$list["tab_type"] = $data[0]["tab_type"];
		$list["tab_styleName"] = $data[0]["tab_style"] != "" ? substr($data[0]["tab_style"],0,strlen($data[0]["tab_style"])-1) : "tabGrey";
		$list["tab_style"] = $data[0]["tab_style"];
		$list["tab_cursor"] = $data[0]["tab_cursor"];
		$list["tab_img_prev"] = $data[0]["tab_img_prev"];
		$list["tab_img_next"] = $data[0]["tab_img_next"];

		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin."/main";
		$map = directory_map($working_skin_path,true,false);

		foreach ($data as $row) $tabs[] = $row;
		$tabsArr = array(
			'folders' => $map,
			'tabs' => $tabs
		);

		$template_path = "_modules/common/topbar.html";

		$this->template->assign(array(
			'data'=>$list,
			'tabsData'=>$tabsArr,
			'working_skin'=>$this->designWorkingSkin,
			'template_path'=>$template_path
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 카테고리 네비게이션 디자인 설정 */
	public function category_navigation_design(){


		$this->tempate_modules();

		$template_path = isset($_GET['template_path']) ? $_GET['template_path'] : '';
		$location = dirname($template_path);

		$category_config = skin_configuration($this->designWorkingSkin);
		if(!isset($category_config['category_type'])) $category_config['category_type'] = '';
		$direction = substr($category_config['category_type'],0,1);

		$this->template->assign(array(
			'template_path'		=> $template_path,
			'location'			=> $location,
			'direction'			=>$direction,
			'category_type'		=>$category_config['category_type']
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");

	}

	/* 브랜드 네비게이션 디자인 설정 */
	public function brand_navigation_design(){

		$this->tempate_modules();

		$template_path = isset($_GET['template_path']) ? $_GET['template_path'] : '';
		$location = dirname($template_path);

		$category_config = skin_configuration($this->workingSkin);
		if(!isset($category_config['brand_type'])) $category_config['brand_type'] = '';
		$direction = substr($category_config['brand_type'],0,1);

		$this->template->assign(array(
			'template_path'		=> $template_path,
			'location'			=> $location,
			'direction'			=>$direction,
			'brand_type'		=>$category_config['brand_type']
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");

	}

	/* 지역 네비게이션 디자인 설정 */
	public function location_navigation_design(){

		$this->tempate_modules();

		$template_path = isset($_GET['template_path']) ? $_GET['template_path'] : '';
		$location = dirname($template_path);

		$category_config = skin_configuration($this->workingSkin);
		if(!isset($category_config['location_type'])) $category_config['location_type'] = '';
		$direction = substr($category_config['location_type'],0,1);

		$this->template->assign(array(
			'template_path'		=> $template_path,
			'location'			=> $location,
			'direction'			=>$direction,
			'location_type'		=>$category_config['location_type']
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");

	}

	/* 아이에디터 */
	public function eye_editor(){

		$this->tempate_modules();

		$this->load->helper('file');
		$this->load->helper('directory');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		### SERVICE CHECK
		/*
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('eyeeditor');

		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}
		*/

		$searchKeyword = isset($_GET['searchKeyword']) ? $_GET['searchKeyword'] : '';

		$this->template->define(array('eyeeditor_webftp'=>$this->skin.'/webftp/_eyeeditor_webftp.html'));

		$this->template->assign(array('EYE_EDITOR'=>true));
		$this->template->assign(array('searchKeyword'=>$searchKeyword));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 아이에디터 탭 컨텐츠 반환 */
	public function eye_editor_tabcontents(){
		$this->tempate_modules();

		$this->load->helper('file');
		$this->load->helper('directory');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$tabIdx = $_GET['tabIdx'];
		$tplPath = $_GET['tplPath']?$_GET['tplPath']:null;

		/* tpl_path assign */
		$tpl_realpath = ROOTPATH.$tplPath;
		$tpl_fileName = basename($tpl_realpath);
		$tpl_source = read_file($tpl_realpath);
		$searchKeyword = isset($_GET['searchKeyword']) ? $_GET['searchKeyword'] : '';

		/* 백업파일 */
		$backup_files = array();
		$backupPath = "data/file_backup/".$tplPath.date('.YmdHis');
		$backupDir = dirname($backupPath);
		$tpl_fileNameForReg = str_replace('.','\.',$tpl_fileName);
		$map = (array)directory_map(ROOTPATH.$backupDir,true);
		rsort($map);
		foreach($map as $k=>$v){
			if(is_file(ROOTPATH.$backupDir.'/'.$v) && preg_match("/{$tpl_fileNameForReg}\.[0-9]{14}/",$v)){
				$backup_files[] = array(
					'path' => $backupDir.'/'.$v,
					'time' => filemtime(ROOTPATH.$backupDir.'/'.$v)
				);

			}
		}

		if(preg_match("/\.css$/",$tplPath)){
			$code_mode = "css";
		}else{
			$code_mode = "htmlmixed";
		}

		$filemtime = filemtime($tpl_realpath);
		/* 레이아웃 설정 assign */
		if(preg_match("/^data\/skin\/([^\/]+)\/(.*)/",$tplPath,$matches)){
			$skin = $matches[1];
			$skinTplPath = $matches[2];

			$layout_config = layout_config_load($skin,$skinTplPath);

			$tpl_name = $layout_config[$skinTplPath]['tpl_desc'];

			if(preg_match("/\.html$/",$tplPath)){

				$tpl_url = $layout_config[$skinTplPath]['tpl_page'] ? $this->layout->get_tpl_page_url($skinTplPath) : "/".substr($skinTplPath,0,strpos($skinTplPath,'.'));

				if($skin!=$this->designWorkingSkin){
					$tpl_url .= "&previewSkin=" . $skin;
				}
			}else{
				$tpl_url = "/".$tplPath;
			}


		}else{
			$skin = null;
			$skinTplPath = null;
			$tpl_name = null;
			$tpl_url = "/".$tplPath;
		}

		$this->template->assign(array(
			'tpl_source'	=> $tpl_source,
			'backup_files'	=> $backup_files,
			'code_mode'		=> $code_mode,
			'filemtime'		=> $filemtime,
			'tpl_name'		=> $tpl_name,
			'tpl_url'		=> $tpl_url,
			'skin'			=> $skin,
			'skinTplPath'	=> $skinTplPath,
		));

		$this->template->assign($_GET);
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 플래시매직 결제창 */
	public function flash_payment(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}
		$param = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=MAGIC_FLASH";
		$param = makeEncriptParam($param);

		$this->template->assign('param',$param);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	function editorinsertmenu(){
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$cfg_goods = config_load("goods");
		if( $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ){
			$cfg_goods['video_use']=  'Y';
		}
		$this->template->assign('cfg_goods',$cfg_goods);


		$this->template->define(array('editor_menu'=>$this->skin."/design/_editor_menu.html"));
		$editor_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		$this->template->assign(array('selected_editor_menu'=>$editor_menu));
	}

	/* 에딧터 > 플래시 넣기 화면 */
	public function flash_editor_insert(){
		$this->tempate_modules();
		$this->editorinsertmenu();

		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}


	/* 에딧터 > 플래시 목록부분 ajax */
	public function get_flash_editor_list_html(){
		$this->tempate_modules();

		/**
		 * list setting
		**/
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):1;
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):15;

		/* 플래시 목록 가져오기 */
		$sql = 'select SQL_CALC_FOUND_ROWS *,(select url from fm_design_flash_file where flash_seq=a.flash_seq and type="img" and (url like "%flash_%" or url like "%thumb%"  ) limit 1) url from fm_design_flash a order by flash_seq desc';

		$result = select_page($_GET['perpage'],$_GET['page'],10,$sql,'');
		$result['page']['querystring'] = get_args_list();
		$this->template->assign(array('flash_list'=>$result['record']));
		$this->template->assign('page',$result['page']);

		$file_path	= $this->skin.'/design/flash_editor_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}




	/* 에딧터 > 동영상 삽입 화면 */
	public function video_editor_insert(){

		$this->tempate_modules();
		$this->editorinsertmenu();

		$cfg_goods = config_load("goods");
		if( $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ){
			$cfg_goods['video_use']=  'Y';
		}
		$this->template->assign('cfg_goods',$cfg_goods);

		$this->template->assign(array('setMode' => $this->session->userdata('setMode')));
		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 에딧터 > 동영상 목록부분 ajax */
	public function get_video_editor_list_html(){
		$this->tempate_modules();

		/* 동영상 목록 가져오기 */
		$this->load->model('videofiles');

		/**
		 * list setting
		**/
		$videosc['orderby']	= (!empty($_GET['orderby'])) ?	$_GET['orderby']:' seq desc, sort asc ';
		$videosc['sort']		= (!empty($_GET['sort'])) ?			$_GET['sort']:'';
		$videosc['page']		= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$videosc['perpage']	= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):5;

		if( !empty($_GET['upkind']) ) $videosc['upkind']	= $_GET['upkind'];
		$video_list = $this->videofiles->videofiles_list($videosc);

		$videosc['searchcount']	= $video_list['count'];
		$videosc['total_page']		= ceil($videosc['searchcount']	 / $videosc['perpage']);
		$videosc['totalcount']		= $this->videofiles->get_item_total_count($videosc);

		if($video_list['result']) $this->template->assign('video_list',$video_list['result']);

		$returnurl = "./video_editor_insert?";
		$paginlay =  pagingtag($videosc['searchcount']	,$videosc['perpage'],$returnurl, getLinkFilter('',array_keys($videosc)),'page','" ' );

		if($videosc['searchcount'] > 0) {
			$paginlay = (!empty($paginlay)) ? $paginlay:'<p><a class="on red">1</a><p>';
		}
		$this->template->assign('videopagin',$paginlay);

		$file_path	= $this->skin.'/design/video_editor_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 스킨설정 */
	public function font(){
		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function myfont(){
		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function display_desc_layer(){
		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function mobile_quick_design(){
		$this->tempate_modules();

		$themes = $this->designmodel->get_mobile_themes();
		$this->template->assign('themes',$themes);

		$cssPath = $this->designmodel->get_mobile_buttons_css_path();
		$this->template->assign('cssPath',$cssPath);

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function pc_quick_design(){
		if(preg_match("/^mobile_/i",$this->designWorkingSkin) || preg_match("/^storemobile_/i",$this->designWorkingSkin)){
			echo "<span class='red'>Mobile 디자인환경에서는 PC 버튼 설정이 불가능합니다.<br />디자인환경을 변경해주세요.</span>";
			exit;
		}

		$this->tempate_modules();

		$buttonDirectoryPath = "/data/skin/".$this->designWorkingSkin."/images/buttons/";
		$iconDirectoryPath = "/data/icon/goods_status/";
		$buttonImages = array();

		$buttonImages['goods_view']['title'] = '상품상세 페이지용 버튼';
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_buy','name'=>'바로구매','filename'=>'btn_buy.gif');
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_cart','name'=>'장바구니','filename'=>'btn_cart.gif');
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_wish','name'=>'위시리스트','filename'=>'btn_wish.gif');
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_runout','name'=>'품절','filename'=>'btn_runout.gif');
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_purchasing','name'=>'재고 확보 중','filename'=>'btn_purchasing.gif');
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_restock_notify','name'=>'재입고 알림','filename'=>'btn_restock_notify.gif');
		$buttonImages['goods_view']['buttons'][] = array('code'=>'btn_unsold','name'=>'판매중지','filename'=>'btn_unsold.gif');

		$buttonImages['cart']['title'] = '장바구니용 버튼';
		$buttonImages['cart']['buttons'][] = array('code'=>'btn_order_all','name'=>'전체상품 주문하기','filename'=>'btn_order_all.gif');
		$buttonImages['cart']['buttons'][] = array('code'=>'btn_order_selected','name'=>'선택상품 주문하기','filename'=>'btn_order_selected.gif');
		$buttonImages['cart']['buttons'][] = array('code'=>'btn_shopping_continue','name'=>'계속 쇼핑하기','filename'=>'btn_shopping_continue.gif');

		$buttonImages['settle']['title'] = '주문하기 페이지용 버튼';
		$buttonImages['settle']['buttons'][] = array('code'=>'btn_order','name'=>' 주문하기','filename'=>'btn_order.gif');
		$buttonImages['settle']['buttons'][] = array('code'=>'btn_pay','name'=>'결제하기','filename'=>'btn_pay.gif');
		$buttonImages['settle']['buttons'][] = array('code'=>'btn_order_cart','name'=>'장바구니로','filename'=>'btn_order_cart.gif');
		$buttonImages['settle']['buttons'][] = array('code'=>'btn_shopping_continue_s','name'=>'쇼핑 계속하기','filename'=>'btn_shopping_continue_s.gif');

		$buttonImages['login']['title'] = '로그인 페이지용 버튼';
		$buttonImages['login']['buttons'][] = array('code'=>'btn_login','name'=>'로그인','filename'=>'btn_login.gif');
		$buttonImages['login']['buttons'][] = array('code'=>'btn_login_join','name'=>'회원가입','filename'=>'btn_login_join.gif');
		$buttonImages['login']['buttons'][] = array('code'=>'btn_login_idpw','name'=>'아이디/비밀번호 찾기','filename'=>'btn_login_idpw.gif');
		$buttonImages['login']['buttons'][] = array('code'=>'btn_order_nonmem','name'=>'비회원으로 구매하기','filename'=>'btn_order_nonmem.gif');

		$buttonImages['join']['title'] = '회원가입 페이지용 버튼';
		$buttonImages['join']['buttons'][] = array('code'=>'btn_join','name'=>'회원가입','filename'=>'btn_join.gif');
		$buttonImages['join']['buttons'][] = array('code'=>'btn_myinfo','name'=>'회원정보수정','filename'=>'btn_myinfo.gif');
		$buttonImages['join']['buttons'][] = array('code'=>'btn_go_login','name'=>'로그인','filename'=>'btn_go_login.gif');
		$buttonImages['join']['buttons'][] = array('code'=>'btn_shopping','name'=>'쇼핑하러가기','filename'=>'btn_shopping.gif');

		$buttonImages['etc']['title'] = '회원가입 페이지용 버튼';
		$buttonImages['etc']['buttons'][] = array('code'=>'btn_list','name'=>'주문목록 돌아가기','filename'=>'btn_list.gif');
		$buttonImages['etc']['buttons'][] = array('code'=>'btn_list_return','name'=>'반품목록 돌아가기','filename'=>'btn_list_return.gif');
		$buttonImages['etc']['buttons'][] = array('code'=>'btn_list_refund','name'=>'환불목록 돌아가기','filename'=>'btn_list_refund.gif');
		$buttonImages['etc']['buttons'][] = array('code'=>'btn_ok','name'=>'확인','filename'=>'btn_ok.gif');
		$buttonImages['etc']['buttons'][] = array('code'=>'btn_cancel','name'=>'취소','filename'=>'btn_cancel.gif');

		$buttonImages['icon']['title'] = '리스트 페이지용 아이콘 (아래의 아이콘은 모든 스킨에 공통 사용됩니다)';
		$buttonImages['icon']['buttons'][] = array('code'=>'icon_list_soldout','name'=>'품절','filename'=>'icon_list_soldout.gif');
		$buttonImages['icon']['buttons'][] = array('code'=>'icon_list_warehousing','name'=>'재고 확보 중','filename'=>'icon_list_warehousing.gif');
		$buttonImages['icon']['buttons'][] = array('code'=>'icon_list_stop','name'=>'판매 중지','filename'=>'icon_list_stop.gif');
		$buttonImages['icon']['buttons'][] = array('code'=>'icon_list_cpn','name'=>'쿠폰','filename'=>'icon_list_cpn.gif');
		$buttonImages['icon']['buttons'][] = array('code'=>'icon_list_freedlv','name'=>'무료배송','filename'=>'icon_list_freedlv.gif');
		$buttonImages['icon']['buttons'][] = array('code'=>'icon_list_video','name'=>'동영상','filename'=>'icon_list_video.gif');

		foreach($buttonImages as $k=>$v){
			if($k=='login') $buttonImages[$k]['cols'] = 4;
			else $buttonImages[$k]['cols'] = 7;
		}

		$this->template->assign(array(
			'buttonImages' => $buttonImages,
			'buttonDirectoryPath' => $buttonDirectoryPath,
			'iconDirectoryPath' => $iconDirectoryPath,
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function codes(){
		$this->admin_menu();
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('skin');

		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		$query = $this->db->query("select code_page from fm_design_codes group by code_page order by code_page");
		$arr_code_page = $query->result_array();
		$this->template->assign(array('arr_code_page'=>$arr_code_page));

		/**
		 * list setting
		**/
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):1;
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):15;

		$sql = "select * from fm_design_codes order by code_seq desc";

		$result = select_page($_GET['perpage'],$_GET['page'],10,$sql,'');
		$result['page']['querystring'] = get_args_list();
		$this->template->assign($result);

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 우측 추천상품 생성,수정 화면 */
	public function recomm_goods_edit(){
		$this->tempate_modules();
		$this->load->model('goodsmodel');
		$this->load->model('goodsdisplay');
		$this->load->model('eventmodel');
		$this->load->helper('text');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$tmp_data = layout_config_autoload($this->designWorkingSkin,$this->skin);
		$use_layout_right = $tmp_data[$this->skin]['layoutScrollRight'];

		/* 현재 사용중인 우측스크롤 페이지 지정*/
		//$template_path = $_GET['template_path'];
		$template_path = $use_layout_right;
		$image_decorations = array();

		/* 우측 추천상품 정보 가져오기 */
		$this->load->model('goodsmodel');
		$arr_data_seq = $this->goodsmodel->get_recommend_goods_list(1,5,'admin');

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		/* 디스플레이 상품 목록 */
		$display_item = $this->goodsmodel->get_recommend_item($arr_data_seq);
		$this->template->assign(array(
			'template_path'		=> $template_path,
			'display_item'		=> $display_item
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 배너 넣기 화면 */
	public function banner_insert(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		/* 레이아웃 설정 assign */
		$layout_config = layout_config_load($this->designWorkingSkin,$template_path);
		$this->template->assign(array('layout_config'=>$layout_config[$template_path]));

		$this->template->assign(array('template_path' => $template_path));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 배너 생성 화면 */
	public function banner_create(){
		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$template_path = $_GET['template_path'];

		$styles = $this->designmodel->get_banner_styles();

		$return_url = "http://".$_SERVER['HTTP_HOST']."/admin/design/banner_insert?template_path=".urlencode($template_path);

		$this->template->assign(array('template_path' => $template_path,'return_url' => $return_url,'styles'=>$styles));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 배너 수정 화면 */
	public function banner_edit(){

		$this->tempate_modules();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('design_act');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$banner_styles = $this->designmodel->get_banner_styles();

		$banner_seq = $_GET['banner_seq'];
		$template_path = $_GET['template_path'];
		
		$this->template->assign(array(
			'template_path'=>$template_path,
			'banner_styles'=>$banner_styles,
			'banner_seq'=>$banner_seq
		));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 배너 목록부분 ajax */
	public function get_banner_list_html(){
		$this->tempate_modules();

		$styles = $this->designmodel->get_banner_styles();

		/* 플래시 목록 가져오기 */
		$sql = 'select SQL_CALC_FOUND_ROWS * from fm_design_banner where skin=? order by banner_seq desc';
		$query = $this->db->query($sql,$this->designWorkingSkin);
		$banner_list = $query->result_array();

		$this->template->assign(array('banner_list'=>$banner_list,'styles'=>$styles));

		$file_path	= $this->skin.'/design/banner_list_inc.html';
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 배너 설정 로딩 ajax */
	public function banner_setting_load(){
		if($_GET['banner_seq']){
		
			// 불러오기
			$query = $this->db->query("select * from fm_design_banner where skin=? and banner_seq=?",array($this->designWorkingSkin,$_GET['banner_seq']));
			$result = $query->row_array();

			// 스타일 설정 불러와서 병합
			$styles = $this->designmodel->get_banner_styles();
			$result = array_merge($styles[$result['style']],$result);

			$query = $this->db->query("select * from fm_design_banner_item where skin=? and banner_seq=?",array($this->designWorkingSkin,$_GET['banner_seq']));
			$result_item = $query->result_array();

			foreach($result_item as $k=>$item){
				if($item['image']){
					list($item['image_width'], $item['image_height']) = @getimagesize(ROOTPATH."data/skin/".$this->designWorkingSkin."/".$item['image']);
				}
				$result_item[$k] = $item;
			}

			$result['images'] = $result_item;

			echo json_encode($result);

		}else if($_GET['style']){

			// 스타일 설정, 샘플 병합
			$styles = $this->designmodel->get_banner_styles();
			$sample = $this->designmodel->get_banner_sample($_GET['style']);

			$result = array_merge($styles[$_GET['style']],$sample);
			$result['style'] = $_GET['style'];
			$result['skin'] = $this->designWorkingSkin;

			foreach($result['images'] as $k=>$item){
				if($item['image']){
					list($item['image_width'], $item['image_height']) = @getimagesize(ROOTPATH.$item['image']);
				}
				$result['images'][$k] = $item;
			}

			echo json_encode($result);
		}
	}

	/* 배너 스크립트 반환 */
	public function banner_html_ajax(){
		$this->template->include_('showDesignBanner'); 
		echo showDesignBanner($_GET['banner_seq'],true);
	}

}

/* End of file design.php */
/* Location: ./app/controllers/admin/design.php */