<?php
class Layout extends CI_Model {

	public function __construct() {
		parent::__construct();

		$this->managerInfo = $this->session->userdata('manager');
	}

	/* 현재 프론트화면에 스킨 */
	public function get_view_skin(){

		if($this->session->userdata('previewSkin')){
			$viewSkin = $this->session->userdata('previewSkin');
			return $viewSkin;
		}

		if($this->managerInfo['manager_seq'] && $this->is_design_mode()){
			$viewSkin = $this->designWorkingSkin;
			return $viewSkin;
		}else{
			if			($this->fammerceMode)	$viewSkin = $this->realFammerceSkin;
			else if		($this->mobileMode)		$viewSkin = $this->realMobileSkin;
			else if		($this->storeMode)		$viewSkin = $this->realStoreSkin;
			else if		($this->storemobileMode)		$viewSkin = $this->realStoremobileSkin;
			else if		($this->storefammerceMode)		$viewSkin = $this->realStorefammerceSkin;
			else							$viewSkin = $this->realSkin;
			return $viewSkin;
		}

	}

	/* 현재 프론트화면을 디자인모드로 출력할지 여부를 반환 */
	public function is_design_mode(){

		if($this->managerInfo['manager_seq']){
			$return = true;
		}else{
			$return = false;
		}

		if($this->session->userdata('previewSkin') && $this->session->userdata('previewSkin')!=$this->workingSkin){
			$return = false;
		}

		if($this->session->userdata('previewMobileSkin') && $this->session->userdata('previewMobileSkin')!=$this->workingMobileSkin){
			$return = false;
		}

		// 모바일기기에서는 디자인모드 사용불가
		if($this->_is_mobile_agent){
			$return = false;
		}

		// facebook ,iframe에서는 디자인모드 사용불가
		if($this->session->userdata('fammercemode')){
			$return = false;
		}

		if($this->managerInfo['manager_seq']){

			$this->load->helper('cookie');

			if($_GET['setDesignMode']){
				set_cookie(array(
					'name'   => 'setDesignMode',
					'expire' => '86500',
					'value'  => $_GET['setDesignMode'],
					'path'   => '/'
				));

				if($_GET['setDesignMode']=='on'){
					$return = true;
				}else{
					$return = false;
				}
			}else{
				if(get_cookie('setDesignMode')=='on'){
					$return = true;
				}else{
					$return = false;
				}
			}
		}

		return $return;
	}

	/* 측면디자인 없이 풀사이즈로 보여줄 화면 tpl_path 정의 */
	public function is_fullsize_absolutly($tpl_path) {
		return in_array($tpl_path,array(
			'member/join_gate.html',
			'member/agreement.html',
			'member/register.html',
			'member/register_ok.html',
			'member/find.html',
			'member/login.html',
			'order/settle.html',
			'order/complete.html',
		));
	}

	/* 스킨 내 TPL 폴더 정의 */
	public function get_folders_in_skin(){

		$folders = array();
		$folders['main'] = '메인';
		if($this->mobileMode) $folders['layout_MainTopBar'] = '메인 상단바';
		if(!$this->mobileMode && !$this->fammerceMode && !$this->storefammerceMode) $folders['layout_TopBar'] = '상단바 영역';
		//$folders['cart'] = '장바구니';
		$folders['goods'] = '상품';
		//$folders['board'] = '게시판';
		$folders['order'] = '주문';
		$folders['member'] = '회원';
		$folders['mypage'] = '마이페이지';
		$folders['service'] = '고객센터';
		$folders['intro'] = '인트로';
		$folders['popup'] = '팝업페이지';

		$folders['joincheck'] = '출석체크';
		$folders['coupon'] = '쿠폰';
		$folders['etc'] = '기타';

		// 작업스킨 경로
		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin;

		// 숨길 폴더
		$hide_directories = array('layout_header','layout_footer','layout_side','layout_scroll','configuration','board','_modules','css','images','common');

		// 추가 폴더
		$map = directory_map($working_skin_path,true,false);
		foreach($map as $directory){
			if(is_dir($working_skin_path.'/'.$directory) && !$folders[$directory] && !in_array($directory,$hide_directories)){
				$folders[$directory] = $directory;
			}
		}

		$folders['layout_header'] = '상단 영역';
		$folders['layout_footer'] = '하단 영역';
		if(!$this->mobileMode && !$this->fammerceMode && !$this->storefammerceMode) {
			$folders['layout_side'] = '측면 영역';
			$folders['layout_scroll'] = '스크롤 영역';
		}

		return $folders;
	}

	/* 자주 쓰는 URL */
	public function get_frequent_url(){

		$frequents = array();
		$frequents[] = array('name'=>'메인화면'		,'value'=>'/main/index');
		$frequents[] = array('name'=>'마이페이지'		,'value'=>'/mypage/index');
		$frequents[] = array('name'=>'고객센터'		,'value'=>'/service/cs');

		return $frequents;
	}

	/* 사용자 추가 페이지 url 반환 */
	public function get_tpl_page_url($tpl_path){
		return "/page/index?tpl=".urlencode($tpl_path);
	}

	/* tpl_path의 URL 반환 */
	public function get_tpl_path_url($skin,$tpl_path,$tpl_page=null){
		if($tpl_page==null){
			$query = $this->db->query("select tpl_page from fm_config_layout where skin=? and tpl_path=?",array($skin,$tpl_path));
			$res = $query->row_array();
			$tpl_page = $res['tpl_page'];
		}

		return $tpl_page==1 ? $this->get_tpl_page_url($tpl_path) : "/".preg_replace("/\.html$/","",$tpl_path);
	}

	/* 쇼핑몰 타이틀 반환 */
	public function get_title(){
		$arrBasic = config_load('basic');
	}
}
?>