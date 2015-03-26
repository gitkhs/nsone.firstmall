<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class common extends front_base {
	public function test()
	{
		$this->load->model('visitorlog');
		$referer_sitecd = $this->visitorlog->get_referer_sitecd("");
	}

	//한글도메인체크를 위해추가됨@2012-10-30
	public function domainjson()
	{
		$return = array('subdomain'=>$this->config_system['subDomain'], 'domain'=>$this->config_system['domain']);
		echo json_encode($return);
	}

	public function code2json()
	{
		$arrCode = code_load($_GET['groupcd']);
		echo json_encode($arrCode);
	}


	public function category2json(){
		$result = array();
		$this->load->model('categorymodel');
		$code = $_GET['categoryCode'];
		$result = $this->categorymodel->get_list($code,array("hide='0'"));
		echo json_encode($result);
	}

	//상품후기 >> 주문검색추가
	public function orderlistjson(){
		$this->arr_step = config_load('step');

		$sc['whereis']	= ' and id= "goods_review" ';
		$sc['select']		= ' * ';
		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
		if( strstr($manager['auth_write'],'[onlybuyer]') ) {//구매자만 가능한경우
			$auth_write = "onlybuyer";
		}elseif( strstr($manager['auth_write'],'[member]') ){
			$auth_write = "member";
		}else{
			$auth_write = "all";
		}
		if( $this->userInfo['member_seq'] ) {
			$result = array('auth_write'=>$auth_write, 'data'=>array());
		}else{
			$result = array('auth_write'=>$auth_write, 'nonorder'=>true,'data'=>array());
		}
		//$where[] = " (step = '70' OR step = '75') ";//부분배송완료, 배송완료
		if($this->userInfo['member_seq']) {//회원전용
			$where[] = " member_seq = '".$this->userInfo['member_seq']."' ";
			$where[] = " order_seq IN ( SELECT order_seq FROM fm_order_item WHERE goods_seq = '".$_POST['goods_seq']."')";
		}else{
			//$where[] = " member_seq is null ";//회원주문 검색불가
			$where[] = " order_seq IN ( SELECT order_seq FROM fm_order_item WHERE goods_seq = '".$_POST['goods_seq']."')";
			$where[] = " order_seq = '".$this->session->userdata('sess_order')."' ";
		}

		$query = "SELECT order_seq , step FROM (
			SELECT
			export.* ,
			export.status as step,
			ord.member_seq
			FROM
			fm_goods_export export
			LEFT JOIN fm_order ord ON ord.order_seq=export.order_seq
			LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
			WHERE export.status = '75'
		) t WHERE " . implode(' AND ',$where) . " ORDER BY order_seq ASC, regist_date DESC";
		$query = $this->db->query($query,$bind);
		foreach ($query->result_array() as $row){
			$row['mstep'] = $this->arr_step[$row['step']];
			$result['data'][] = $row;
		}
		echo json_encode($result);
		/**
		if(!$result && !$this->userInfo['member_seq'] ){
			echo json_encode($result);
		}else{
			echo json_encode($result);
		}
		**/
		$this->session->unset_userdata('sess_order');//비회원주문번호세션제거
	}

	//1:1문의 >> 주문검색추가
	public function myqanorderlistjson(){
		$this->arr_step = config_load('step');

		$sc['whereis']	= ' and id= "goods_review" ';
		$sc['select']		= ' * ';
		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
		if( strstr($manager['auth_write'],'[onlybuyer]') ) {//구매자만 가능한경우
			$auth_write = "onlybuyer";
		}elseif( strstr($manager['auth_write'],'[member]') ){
			$auth_write = "member";
		}else{
			$auth_write = "all";
		}

		$result = array('auth_write'=>$auth_write, 'data'=>array());
		$where[] = " member_seq = '".$this->userInfo['member_seq']."' ";
		$query = "SELECT order_seq , step, goods_name, item_cnt FROM (
			SELECT
			ord.*,
			(
				SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq
			) group_name,
			(
				SELECT goods_name FROM fm_order_item WHERE order_seq=ord.order_seq ORDER BY item_seq LIMIT 1
			) goods_name,
			(
				SELECT count(item_seq) FROM fm_order_item WHERE order_seq=ord.order_seq
			) item_cnt,
			mem.rute as mbinfo_rute,
			mem.user_name as mbinfo_user_name,
			bus.business_seq as mbinfo_business_seq,
			bus.bname as mbinfo_bname
			FROM
			fm_order ord
			LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
			LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
			WHERE ord.step!=0
		) t WHERE " . implode(' AND ',$where) . " ORDER BY step ASC, regist_date DESC";
		$query = $this->db->query($query,$bind);
		foreach ($query->result_array() as $row){
			$row['mstep'] = $this->arr_step[$row['step']];
			$result['data'][] = $row;
		}
		echo json_encode($result);
	}

	public function download(){

		$downfile = ROOTPATH.$_GET['downfile'];

		if(preg_match("/php/",$downfile)){
			echo("올바른 파일이 아닙니다.");
		}

		$arr = explode('/',$downfile);
		$filename = $arr[count($arr)-1];

		if ( file_exists($downfile) )
		{
			header("Content-Type: application/octet-stream");
			Header("Content-Disposition: attachment;; filename=$filename");
			header("Content-Transfer-Encoding: binary");
			Header("Content-Length: ".(string)(filesize($downfile)));
			Header("Cache-Control: cache, must-reval!idate");
			header("Pragma: no-cache");
			header("Expires: 0");

			$fp = fopen($downfile, "rb"); //rb 읽기전용 바이러니 타입

			while ( !feof($fp) )
			{
				echo fread($fp, 100*1024); //echo는 전송을 뜻함.
			}

			fclose ($fp);

			flush(); //출력 버퍼비우기 함수..
		}
		else
		{
			echo("존재하지 않는 파일입니다.");
		}

	}


	/* 에디터 첨부이미지 임시업로드(uplodify처리) */
	public function editor_image_upload_temp(){

		$this->load->model('usedmodel');

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			$result = array(
				'status' => 0,
				'msg' => '파일 저장 공간이 부족하여 업로드가 불가능합니다.',
				'desc' => '업로드 실패'
			);
			echo "[".json_encode($result)."]";
			exit;
		}

		/* 이미지파일 확장자 */
		$this->arrImageExtensions = array('jpg','jpeg','png','gif','bmp','tif','pic','ai','psd','eps','dwg');
		$this->arrImageExtensions = array_merge($this->arrImageExtensions,array_map('strtoupper',$this->arrImageExtensions));

		$result = array(
			'status' => 0,
			'msg' => '업로드 실패하였습니다.',
			'desc' => '업로드 실패'
		);

		$path = '/data/tmp';
		$targetPath = ROOTPATH.$path;

		if (!empty($_FILES)) {

			$fileName = "temp_".time().sprintf("%04d",rand(0,9999));

			$size = getimagesize($_FILES['Filedata']['tmp_name']);
			$_FILES['Filedata']['type'] = $size['mime'];
			$config['upload_path'] = $targetPath;
			$config['allowed_types'] = implode('|',$this->arrImageExtensions);
			$config['max_size']	= $this->config_system['uploadLimit'];
			$config['file_name'] = $fileName;
			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('Filedata'))
			{
				$result = array(
					'status' => 0,
					'msg' => $this->upload->display_errors(),
					'desc' => '업로드 실패'
				);
			}else{
				$fileInfo = $this->upload->data();
				$filePath = $path.'/'.$fileInfo['file_name'];

				if( $this->session->userdata('tmpcode') ) {
					$boardidar	= @explode('^^', $this->session->userdata('tmpcode'));
					$sql['whereis']	= ' and id= "'.$boardidar[0].'" ';
					$sql['select']		= ' * ';
					$this->load->model('Boardmanager');
					$this->load->model('membermodel'); 
					$this->load->model('boardadmin');
					$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
					$this->manager['gallery_list_w'] = ($this->manager['gallery_list_w'])?$this->manager['gallery_list_w']:250;
					if($fileInfo['is_image'] == true && $fileInfo['image_width'] > $this->manager['gallery_list_w']) {//이미지인경우
						$this->load->helper('board');
						$source = $fileInfo['full_path'];
						$target = str_replace($fileInfo['file_name'], '_thumb_'.$fileInfo['file_name'],$fileInfo['full_path']);
						board_image_thumb($source,$target,$this->manager['gallery_list_w'],$this->manager['gallery_list_h']);
					}
				}else{
					if($fileInfo['is_image'] == true && $fileInfo['image_width'] > 250) {//이미지인경우
						$this->load->helper('board');
						$source = $fileInfo['full_path'];
						$target = str_replace($fileInfo['file_name'], '_thumb_'.$fileInfo['file_name'],$fileInfo['full_path']);
						board_image_thumb($source,$target,'250','250');
					}
				}

				$result = array('status' => 1,'filePath' => $filePath,'fileInfo'=>$fileInfo);
			}

		}

		echo "[".json_encode($result)."]";
	}

	/* 에디터 첨부이미지 임시업로드(uplodify처리) */
	public function editor_upload_temp(){

		$this->load->model('usedmodel');
		/* 파일확장자 */

		$arrFileExtensions = array('jpg','jpeg','png','gif','pic','tif','tiff','jfif','bmp','txt','hwp','docx','docm','doc','ppt','pptx','pptm','pps','ppsx','xls','xlsx','xlsm','xlam','xla','ai','psd','eps','pdf','ods','ogg','mp4','avi','wmv','zip','rar','tar','7z','tbz','tgz','lzh','gz','dwg');

		$arrFileExtensions = array_merge($arrFileExtensions,array_map('strtoupper',$arrFileExtensions));

		$res = $this->usedmodel->used_limit_check();
		if(!$res['type']){
			$result = array(
				'status' => 0,
				'msg' => '파일 저장 공간이 부족하여 업로드가 불가능합니다.',
				'desc' => '업로드 실패'
			);
			echo "[".json_encode($result)."]";
			exit;
		}

		$result = array(
			'status' => 0,
			'msg' => '업로드 실패하였습니다.',
			'desc' => '업로드 실패'
		);

		if (!empty($_FILES)) {
			$size			= @getimagesize($_FILES['Filedata']['tmp_name']);

			if($this->session->userdata('tmpcode') && !$size && $this->session->userdata('tmpcode')) {//이미지가 아닌경우 게시판으로 업로드
				$boardidar	= @explode('^^', $this->session->userdata('tmpcode'));
				$path = '/data/board/'.$boardidar[0];//게시판폴더로 이동마//
			}else{
				$path = '/data/tmp';
			}
			$targetPath = ROOTPATH.$path;
			$file_ext		= @end(explode('.', $_FILES['Filedata']['name']));//확장자추출
			if(!$size['mime']){
				$_FILES['Filedata']['type'] = $file_ext;//확장자추출
			}else{
				$_FILES['Filedata']['type'] = $size['mime'];
			}

			$config['upload_path'] = $targetPath;
			$config['allowed_types'] = @implode('|',$arrFileExtensions);
			$config['max_size']	= $this->config_system['uploadLimit'];
			$config['file_name']	= md5($_FILES['Filedata']['name']).substr(date('YmdHisw'),8,14).'.'.$file_ext;//새로운이름으로
			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('Filedata'))
			{
				$result = array(
					'status' => 0,
					'msg' => $this->upload->display_errors(),
					'desc' => '업로드 실패'
				);
			}else{
				$fileInfo = $this->upload->data();
				$filePath = $path.'/'.$fileInfo['file_name'];

				if($this->session->userdata('tmpcode')) {//이미지가 아닌경우 게시판으로 업로드
					$boardidar	= @explode('^^', $this->session->userdata('tmpcode'));
					$sql['whereis']	= ' and id= "'.$boardidar[0].'" ';
					$sql['select']		= ' * ';
					$this->load->model('Boardmanager');
					$this->load->model('membermodel'); 
					$this->load->model('boardadmin');
					$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
					$this->manager['gallery_list_w'] = ($this->manager['gallery_list_w'])?$this->manager['gallery_list_w']:250;
					if($fileInfo['is_image'] == true && $fileInfo['image_width'] > $this->manager['gallery_list_w']) {//이미지인경우
						$this->load->helper('board');
						$source = $fileInfo['full_path'];
						$target = str_replace($fileInfo['file_name'], '_thumb2_'.$fileInfo['file_name'],$fileInfo['full_path']);
						board_image_thumb($source,$target,$this->manager['gallery_list_w'],$this->manager['gallery_list_h']);
					}
				}else{
					if($fileInfo['is_image'] == true && $fileInfo['image_width'] > 250) {//이미지인경우
						$this->load->helper('board');
						$source = $fileInfo['full_path'];
						$target = str_replace($fileInfo['file_name'], '_thumb_'.$fileInfo['file_name'],$fileInfo['full_path']);
						board_image_thumb($source,$target,'250','250');
					}
				}

				$result = array('status' => 1,'filePath' => $filePath,'fileInfo'=>$fileInfo,'filetype'=>$_FILES['Filedata']['type']);
			}

		}

		echo "[".json_encode($result)."]";
	}

	/* 카테고리 네비게이션 디자인 영역 HTML 보기 */
	public function category_navigation_html(){
		$this->load->model('categorymodel');

		$tpl_path = $_GET['tpl_path'];

		$category = $this->categorymodel->get_category_view();

		$categoryNavigationKey = "categoryNavigation".uniqid();
		$this->template->assign(array('category'=>$category,'categoryNavigationKey'=>$categoryNavigationKey));
		$this->template->define(array('category'=>$this->designWorkingSkin.'/'.$tpl_path));

		$tpl_source = $this->template->fetch("category");
		$tpl_source = "<div id='{$categoryNavigationKey}'>\n{$tpl_source}\n</div>";

		echo $tpl_source;
	}

	/* 브랜드 네비게이션 디자인 영역 HTML 보기 */
	public function brand_navigation_html(){
		$this->load->model('brandmodel');

		$tpl_path = $_GET['tpl_path'];

		$category = $this->brandmodel->get_brand_view();

		$categoryNavigationKey = "categoryNavigation".uniqid();
		$this->template->assign(array('brand'=>$category,'categoryNavigationKey'=>$categoryNavigationKey));
		$this->template->define(array('brand'=>$this->designWorkingSkin.'/'.$tpl_path));

		$tpl_source = $this->template->fetch("brand");
		$tpl_source = "<div id='{$categoryNavigationKey}'>\n{$tpl_source}\n</div>";

		echo $tpl_source;
	}

	/* 지역 네비게이션 디자인 영역 HTML 보기 */
	public function location_navigation_html(){
		$this->load->model('locationmodel');

		$tpl_path = $_GET['tpl_path'];

		$category = $this->locationmodel->get_location_view();

		$categoryNavigationKey = "categoryNavigation".uniqid();
		$this->template->assign(array('location'=>$category,'categoryNavigationKey'=>$categoryNavigationKey));
		$this->template->define(array('location'=>$this->designWorkingSkin.'/'.$tpl_path));

		$tpl_source = $this->template->fetch("location");
		$tpl_source = "<div id='{$categoryNavigationKey}'>\n{$tpl_source}\n</div>";

		echo $tpl_source;
	}

	/* SSL 중계처리 페이지 */
	public function ssl(){
		$this->load->helper('cookiesecure');

		$action_url = $_GET['action'];

		$encoded = base64_encode(cookieEncode(serialize($_POST), 50));

		echo '
		<form name="sslForm" method="post" action="'.$action_url.'">
			<input type="hidden" name="sslEncodedString" value="'.$encoded.'">
		</form>
		<script language="javascript">
			document.sslForm.submit();
		</script>
		';
	}
	//* IP차단 페이지 */
	public function denined_ip(){
		echo "접근이 차단되었습니다.";
		exit;
	}

	/* ALLAT 데이터 암호화를 위한 폼*/
	public function allat_enc(){
		$actionUrl = $_POST['actionUrl'];

		$params = array();

		$html = "";
		$html .= "<script language=JavaScript charset='euc-kr' src='https://tx.allatpay.com/common/AllatPayRE.js'></script>";
		$html .= "<form name='fm' method='post' action='{$actionUrl}'>";
		foreach($_POST as $k=>$v) {
			if(is_array($v)){
				foreach($v as $k2=>$v2) $html .= "<input type='hidden' name='{$k}[{$k2}]' value='{$v2}'>";
			}else{
				$html .= "<input type='hidden' name='{$k}' value='{$v}'>";
			}
		}
		$html .= "</form>";
		$html .= "<script>";
		$html .= "var ret = invisible_Cancel(document.fm);";
		$html .= "if( ret.substring(0,4)!='0000' && ret.substring(0,4)!='9999'){";
		$html .= "alert(ret.substring(4,ret.length));";
		$html .= "}";
		$html .= "if( ret.substring(0,4)=='9999' ){";
		$html .= "alert(ret.substring(8,ret.length));";
		$html .= "}";
		$html .= "</script>";
		echo $html;
	}

	/* 즐겨찾기(북마크) */
	public function bookmark(){
		$result = array('result'=>false,'msg'=>'즐겨찾기 해 주셔서 감사합니다.');
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');

		$default_reserve_bookmark = $reserves['default_reserve_bookmark'];
		$default_point_bookmark = $reserves['default_point_bookmark'];

		$bookmarkuser = $this->session->userdata('bookmark');
		$bookmarkpointuser = $this->session->userdata('bookmarkpoint');

		if( ( $default_reserve_bookmark > 0 && !strstr($bookmarkuser,'['.$this->userInfo['member_seq'].']') &&  !$bookmarkuser ) || ( $default_point_bookmark > 0  && !strstr($bookmarkpointuser,'['.$this->userInfo['member_seq'].']')  && !$bookmarkpointuser) ) {

			if ( !defined('__ISUSER__') ) {//비회원인 경우
					$msg = '';

					if( $default_reserve_bookmark ) {
						$msg .= ' 적립금 '.number_format($default_reserve_bookmark).'원';
					}

					if ($default_point_bookmark) {
						$msg .= ($msg)?', 포인트 '.number_format($default_point_bookmark).'원':' 포인트 '.number_format($default_point_bookmark).'원';
					}

					$result = array('result'=>false, 'type'=>'login', 'msg'=>'즐겨찾기를 하시면 '.$msg.'이 지급됩니다.<br>로그인 하시겠습니까?');
			}else{
				$this->load->model('membermodel');
				$msg = '';

				if($default_reserve_bookmark > 0 ) {
					$this->load->model('emoneymodel');
					$sc['select']		= 'emoney_seq';
					$sc['whereis']	= ' and type="bookmark" and member_seq = '.$this->userInfo['member_seq'];
					$bookmarkck = $this->emoneymodel->get_data($sc);
					if(!$bookmarkck){//회원중복체크
						$this->session->set_userdata('bookmark', '['.$this->userInfo['member_seq'].']' );

						$emoney['gb']				= 'plus';
						$emoney['type']			= 'bookmark';
						$emoney['emoney']		= $default_reserve_bookmark;
						$emoney['memo']		= '즐겨찾기 적립';
						$emoney['limit_date']	= get_emoney_limitdate('bookmark');
						$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
					}
					$msg .= ' 적립금 '.number_format($default_reserve_bookmark).'원';
				}

				if($default_point_bookmark > 0 ) {
					$this->load->model('pointmodel');
					$sc['select']		= 'point_seq';
					$sc['whereis']	= ' and type="bookmark" and member_seq = '.$this->userInfo['member_seq'];
					$bookmarkck = $this->pointmodel->get_data($sc);
					if(!$bookmarkck){//회원중복체크
						$this->session->set_userdata('bookmarkpoint', '['.$this->userInfo['member_seq'].']' );

						### POINT
						$iparam['gb']				= "plus";
						$iparam['type']				= 'bookmark';
						$iparam['point']			= $default_point_bookmark;
						$iparam['memo']			= '즐겨찾기 적립';
						$iparam['limit_date']	= get_point_limitdate('bookmark');
						$this->membermodel->point_insert($iparam, $this->userInfo['member_seq']);
					}
					$msg .= ($msg)?', 포인트 '.number_format($default_point_bookmark).'원':' 포인트 '.number_format($default_point_bookmark).'원';
				}
				$result = array('result'=>true, 'type'=>'login', 'msg'=>'즐겨찾기를 하시면 '.$msg.'이 지급됩니다.');
			}
		}
		echo json_encode($result);
	}

	/* 모바일모드에서 PC모드로 전환 */
	public function mobile_mode_off(){

		$referer = parse_url($_SERVER['HTTP_REFERER']);
		if(!$referer['host']) $referer['host'] = $_SERVER['HTTP_HOST'];

		$host = preg_replace("/^m\./","",$referer['host']);
		$path = $referer['path'];
		$protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';
		$query = !empty($referer['query']) ? "?" . $referer['query'] : "";
		$query = $query ? $query."&setMode=pc" : "?setMode=pc";

		// 모바일 상품상세 시 예외처리 2014-01-15 lwh
		if($path == "/goods/view_contents")		$path = "/goods/view";

		// 모바일 출고상세 예외처리 2015-01-14 ocw
		if($path == "/mypage/export_view")		$path = "/mypage/order_view";

		// 카테고리리스트 예외처리 2015-01-16 ocw
		if($path == "/goods/category_list")		$path = "/main/index";

		$url = $protocol.$host.$path.$query;

		pageRedirect($url);
	}


	/* facebook모드에서 PC모드로 전환 */
	public function facebook_mode_off(){
		$this->session->set_userdata('fammercemode','');
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		$host = preg_replace("/^m\./","",$referer['host']);
		$path = $referer['path'];
		$protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';
		$query = !empty($referer['query']) ? "?" . $referer['query'] : "";

		$url = $protocol.$host.$path.$query;

		pageRedirect($url);
	}

	public function mybag_data(){

		$this->load->model('cartmodel');
		$cart = $this->cartmodel->cart_list();
		$cart['list'] = is_array($cart['list'])?$cart['list']:array();

		$cart_ea_sum = 0;
		foreach($cart['list'] as $row){
			if($row['cart_options']){
				foreach($row['cart_options'] as $option) {
					$cart_ea_sum+=$option['ea'];
				}
			}
			if($row['cart_suboptions']){
				foreach($row['cart_suboptions'] as $suboption) {
					$cart_ea_sum+=$suboption['ea'];
				}
			}
		}

		$this->template->assign(array(
			'cart_ea_sum'	=> $cart_ea_sum,
		));

		$result = array(
			'cart_ea_sum' => $cart_ea_sum,
			'total_price' => $cart['total_price']
		);

		echo json_encode($result);

	}

	public function mybag_contents(){

		$this->load->model('cartmodel');
		$cart = $this->cartmodel->cart_list();
		$cart['list'] = is_array($cart['list'])?$cart['list']:array();

		$cart_ea_sum = 0;
		$cart_item_list = array();
		foreach($cart['list'] as $row){
			if($row['cart_options']){
				foreach($row['cart_options'] as $option) {
					$cart_item_list[] = array_merge($row,$option);
					$cart_ea_sum+=$option['ea'];
				}
			}
			if($row['cart_suboptions']){
				foreach($row['cart_suboptions'] as $suboption) {
					$cart_item_list[] = array_merge($row,$suboption);
					$cart_ea_sum+=$suboption['ea'];
				}
			}
		}

		$size = config_load('goodsImageSize','thumbScroll');

		$this->template->assign(array(
			'size'				=> $size['thumbScroll'],
			'cart'				=> $cart,
			'cart_item_list'	=> $cart_item_list,
			'cart_ea_sum'	=> $cart_ea_sum,
		));

		$cart_promotioncode = $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
		$this->template->assign('cart_promotioncode' , $cart_promotioncode);
		$this->template->assign('ispromotioncode' , $this->isplusfreenot['ispromotioncode']);

		$this->template->define(array('tpl'=>$this->skin.'/_modules/mybag/mybag_contents.html'));
		$this->template->print_("tpl");
	}

	public function mybag_goods_cart_del()
	{
		$this->load->model('cartmodel');
		$this->cartmodel->delete_option($_POST['cart_option_seq'],$_POST['cart_suboption_seq']);
	}

	public function mybag_goods_today_del(){

		$goods_seq = $_POST['goods_seq'];

		// 오늘본 상품 쿠키
		$today_num = 0;
		$today_view = $_COOKIE['today_view'];
		if( $today_view ) $today_view = unserialize($today_view);
		if( $today_view ) foreach($today_view as $v){
			if($v!=$goods_seq){
				$data_today_view[] = $v;
			}
		}
		if( $data_today_view ) $data_today_view = serialize($data_today_view);
		setcookie('today_view',$data_today_view,time()+86400,'/');

	}

	public function ajax_get_search_option(){
		$this->load->model('SearchoptionModel');

		$searchOption['category']	= $this->SearchoptionModel->get_results("category");
		$searchOption['brand']		= $this->SearchoptionModel->get_results("brand");
		$searchOption['option1']	= $this->SearchoptionModel->get_results("option1");
		$searchOption['option2']	= $this->SearchoptionModel->get_results("option2");
		$searchOption['rate']		= $this->SearchoptionModel->get_results("rate");

		echo json_encode($searchOption);

	}


	//전국매장안내 네이버지도추가
	public function get_map(){
		$this->load->library('SofeeXmlParser');
		$xmlParser = new SofeeXmlParser();
		$addr= urlencode($_GET['addr']);
		$key=($_GET['key']);
		$url="http://openapi.map.naver.com/api/geocode.php?key=".$key."&encoding=utf-8&coord=latlng&query=".$addr;
		$xmlParser->parseFile($url);
		$tree = $xmlParser->getTree();
		if($tree['geocode']['item'][0]['point']['x']['value']){
			$returnpoint = array('y'=>$tree['geocode']['item'][0]['point']['x']['value'], 'x'=>$tree['geocode']['item'][0]['point']['y']['value']);
		}else{
			$returnpoint = array('y'=>$tree['geocode']['item']['point']['x']['value'], 'x'=>$tree['geocode']['item']['point']['y']['value']);
		}
		echo json_encode($returnpoint);
		exit;
	}

	// 다음에디터 사진 첨부 팝업
	public function editor_image() {
		if($_GET['redomain']) {//한글도메인
			$this->load->helper("krdomain");
			$redomaindecode = urldecode($_GET['redomain']);
			$redomainar = explode(".",$redomaindecode);
			$_GET['redomain'] = krencode($redomainar[0]).str_replace($redomainar[0],"",$redomaindecode);
		}

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('watermark');
		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		$config_watermark = config_load('watermark');
		if($config_watermark['watermark_position']!=''){
			$config_watermark['watermark_position'] = explode('|',$config_watermark['watermark_position']);
		}

		if($config_watermark['watermark_image'] && $config_watermark['watermark_type']){
			$config_watermark['watermark_setting_status'] = 'y';
		}

		$file_path	= "app/javascript/plugin/editor/pages/trex/image.html";

		$this->template->template_dir = ROOTPATH;
		$this->template->assign(array('config_watermark'=>$config_watermark));
		$this->template->assign(array('managerInfo'=>$this->managerInfo));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	function editor_image_watermark()
	{

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('watermark');
		if(!$result['type']) return;
		if(!$this->managerInfo) return;
		if(!$_POST['target_image']) return;

		$target_imgs = explode('|',urldecode($_POST['target_image']));
		$this->load->model('watermarkmodel');

		$result = "FALSE";
		if($target_imgs[0]){
			foreach($target_imgs as $target_image){
				if($target_image){
					$this->watermarkmodel->target_image = str_replace('//','/',ROOTPATH.$target_image);
					$this->watermarkmodel->source_image = $this->watermarkmodel->target_image;
					$this->watermarkmodel->watermark();
					$result = "OK";
				}
			}

		}

		echo $result;
	}

	public function category_all_navigation(){
		$this->load->helper('design');
		$this->load->model('categorymodel');

		$categoryNavigationKey = $_GET['categoryNavigationKey'];
		$categoryData = $this->categorymodel->get_category_view(null,3,'gnb');

		$tpl_path = substr($_GET['template_path'],strpos($_GET['template_path'],'/')+1);
		$layout_config = layout_config_autoload($this->skin,$tpl_path);

		if(preg_match("/^\/goods\/catalog\?(.*)/",$_GET['requesturi'],$matches)){
			$params = array();
			$tmp = explode("&",$matches[1]);
			foreach($tmp as $strings){
				list($k,$v) = explode("=",$strings);
				$params[$k]=$v;
			}
			if($params['code']){
				$currentCategoryData = $this->categorymodel->get_category_data($params['code']);
				$this->template->assign(array('category_gnb_banner' => $currentCategoryData['node_gnb_banner']));
			}
		}

		$this->template->assign(array('categoryNavigationKey'=>$categoryNavigationKey));
		$this->template->assign(array('categoryData'=>$categoryData));
		$this->template->assign(array('layout_config'=>$layout_config[$tpl_path]));
		$this->template->define(array('tpl'=>$this->skin.'/_modules/category/all_navigation.html'));
		$this->template->print_("tpl");
	}

	public function brand_all_navigation(){
		$this->load->helper('design');
		$this->load->model('brandmodel');

		$categoryNavigationKey = $_GET['categoryNavigationKey'];
		$categoryData = $this->brandmodel->get_brand_view(null,3,'gnb');

		$tpl_path = substr($_GET['template_path'],strpos($_GET['template_path'],'/')+1);
		$layout_config = layout_config_autoload($this->skin,$tpl_path);

		if(preg_match("/^\/goods\/brand\?(.*)/",$_GET['requesturi'],$matches)){
			$params = array();
			$tmp = explode("&",$matches[1]);
			foreach($tmp as $strings){
				list($k,$v) = explode("=",$strings);
				$params[$k]=$v;
			}
			if($params['code']){
			/*
				$bestBrandData = $this->brandmodel->get_all(array("category_code='{$params['code']}'","best='Y'"));
				$bestBrandData = $this->brandmodel->design_set($bestBrandData,'gnb');
				$this->template->assign(array('bestBrandData' => $bestBrandData));
			*/
				$currentCategoryData = $this->brandmodel->get_brand_data($params['code']);
				$this->template->assign(array('category_gnb_banner' => $currentCategoryData['node_gnb_banner']));
			}
		}

		if(!$bestBrandData){
			$bestBrandData = $this->brandmodel->get_all(array("best='Y'"));
			$bestBrandData = $this->brandmodel->design_set($bestBrandData,'gnb');
			$this->template->assign(array('bestBrandData' => $bestBrandData));
		}

		$this->template->assign(array('categoryNavigationKey'=>$categoryNavigationKey));
		$this->template->assign(array('categoryData'=>$categoryData));
		$this->template->assign(array('layout_config'=>$layout_config[$tpl_path]));
		$this->template->define(array('tpl'=>$this->skin.'/_modules/brand/all_navigation.html'));
		$this->template->print_("tpl");
	}

	public function location_all_navigation(){
		$this->load->helper('design');
		$this->load->model('locationmodel');

		$locationNavigationKey = $_GET['locationNavigationKey'];
		$locationData = $this->locationmodel->get_location_view(null,3,'gnb');

		$tpl_path = substr($_GET['template_path'],strpos($_GET['template_path'],'/')+1);
		$layout_config = layout_config_autoload($this->skin,$tpl_path);

		if(preg_match("/^\/goods\/location\?(.*)/",$_GET['requesturi'],$matches)){
			$params = array();
			$tmp = explode("&",$matches[1]);
			foreach($tmp as $strings){
				list($k,$v) = explode("=",$strings);
				$params[$k]=$v;
			}
			if($params['code']){
				$currentLocationData = $this->locationmodel->get_location_data($params['code']);
				$this->template->assign(array('location_gnb_banner' => $currentLocationData['node_gnb_banner']));
			}
		}

		$this->template->assign(array('locationNavigationKey'=>$locationNavigationKey));
		$this->template->assign(array('locationData'=>$locationData));
		$this->template->assign(array('layout_config'=>$layout_config[$tpl_path]));
		$this->template->define(array('tpl'=>$this->skin.'/_modules/location/all_navigation.html'));
		$this->template->print_("tpl");
	}


	//검색어 자동완성 기능 불러오기
	public function autocomplete(){
		$key = $_POST["key"];
		$key = str_replace(' ', '',addslashes($key));

		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->library('sale');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');
		$cfg_tmp		= config_load("search");
		$cfg_search['popular_search'] = $cfg_tmp['popular_search']?$cfg_tmp['popular_search']:'n';
		$cfg_search['popular_search_limit_day'] = $cfg_tmp['popular_search_limit_day']?$cfg_tmp['popular_search_limit_day']:30;
		$cfg_search['popular_search_recomm_limit_day'] = $cfg_tmp['popular_search_recomm_limit_day']?$cfg_tmp['popular_search_recomm_limit_day']:30;

		$cfg_search['auto_search'] = $cfg_tmp['auto_search']?$cfg_tmp['auto_search']:'n';
		$cfg_search['auto_search_limit_day'] = $cfg_tmp['auto_search_limit_day']?$cfg_tmp['auto_search_limit_day']:30;
		$cfg_search['auto_search_recomm_limit_day'] = $cfg_tmp['auto_search_recomm_limit_day']?$cfg_tmp['auto_search_recomm_limit_day']:30;

		//----> sale library 적용
		$applypage						= 'list';
		$param['cal_type']				= 'list';
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		if($key){
			$timestamp = strtotime('-'.$cfg_search['auto_search_limit_day'].'day');
			$enddate = date('Y-m-d',$timestamp);

			$query = "select keyword,sum(cnt) cnt from fm_search_list where `keyword` like '%".$key."%' group by `keyword` limit 10";
			$query = $this->db->query($query);
			foreach ($query->result_array() as $row){
				$row['key'] = $row["keyword"];
				$row['keyword'] = str_replace($key, "<font color='#f6620b'><b>".$key."</b></font>", htmlspecialchars($row["keyword"]));
				$result[] = $row;
			}

			$timestamp = strtotime('-'.$cfg_search['auto_search_recomm_limit_day'].'day');
			$enddate = date('Y-m-d',$timestamp);

			$query = "select * from (
						select g.goods_seq, g.goods_name, g.sale_seq, sum(ei.ea) ea,
					 	(select image from fm_goods_image where goods_seq = g.goods_seq and (image_type = 'list1' or image_type = 'list2') limit 1) as goods_img,
						(select consumer_price from fm_goods_option where goods_seq = g.goods_seq and default_option='y' limit 1) as consumer_price, 
						(select price from fm_goods_option where goods_seq = g.goods_seq and default_option='y' limit 1) as price
						from fm_order_item oi,fm_goods_export_item ei,fm_goods_export ex,fm_goods g
						where oi.item_seq=ei.item_seq
						and ei.export_code=ex.export_code
						and oi.goods_seq=g.goods_seq
						and (g.goods_seq in (select b.goods_seq from fm_category a,fm_category_link b where a.category_code=b.category_code and a.title like '%".$key."%') OR oi.goods_name like '%".$key."%')
						and ex.`status`='75'
						and ex.shipping_date >= '".$enddate."'
						and g.goods_view = 'look'
						group by oi.goods_seq
						limit 20
					) t order by rand() desc limit 1";
			$query = $this->db->query($query);
			foreach ($query->result_array() as $row){

				// 카테고리정보
				$tmparr2	= array();
				$categorys	= $this->goodsmodel->get_goods_category($row['goods_seq']);
				foreach($categorys as $val){
					$tmparr = $this->categorymodel->split_category($val['category_code']);
					foreach($tmparr as $cate) $tmparr2[] = $cate;
				}
				if($tmparr2){
					$tmparr2 = array_values(array_unique($tmparr2));
					$row['r_category']	= $tmparr2;
				}

				//----> sale library 적용
				unset($param, $sales);
				$param['option_type']				= 'option';
				$param['consumer_price']			= $row['consumer_price'];
				$param['price']						= $row['price'];
				$param['total_price']				= $row['price'];
				$param['ea']						= 1;
				$param['category_code']				= $row['r_category'];
				$param['goods_seq']					= $row['goods_seq'];
				$param['goods']						= $row;
				$this->sale->set_init($param);
				$sales								= $this->sale->calculate_sale_price($applypage);
				$row['price']						= $sales['result_price'];
				$this->sale->reset_init();
				//<---- sale library 적용

				$result_recomm[] = $row;
			}
		}
		// if(!$result) unset($key);
		if(!$key){
			unset($result, $result_recomm);
			$timestamp = strtotime('-'.$cfg_search['popular_search_limit_day'].'day');
			$enddate = date('Y-m-d',$timestamp);

			$query = "select * from (select keyword,sum(cnt) cnt from fm_search_list where regist_date >= '".$enddate."' group by `keyword` order by cnt desc limit 10) t order by cnt desc";
			$query = $this->db->query($query);
			foreach ($query->result_array() as $row){
				$row['key']	= $row["keyword"];
				$result[]	= $row;
			}

			$timestamp = strtotime('-'.$cfg_search['popular_search_recomm_limit_day'].'day');
			$enddate = date('Y-m-d',$timestamp);

			$query = "select * from (
						select g.goods_seq, g.goods_name,g.sale_seq, sum(ei.ea) ea,
					 	(select image from fm_goods_image where goods_seq = g.goods_seq and (image_type = 'list1' or image_type = 'list2') limit 1) as goods_img,
						(select consumer_price from fm_goods_option where goods_seq = g.goods_seq and default_option='y' limit 1) as consumer_price, 
						(select price from fm_goods_option where goods_seq = g.goods_seq and default_option='y' limit 1) as price
						from fm_order_item oi,fm_goods_export_item ei,fm_goods_export ex,fm_goods g
						where oi.item_seq=ei.item_seq
						and ei.export_code=ex.export_code
						and oi.goods_seq=g.goods_seq
						and ex.`status`='75'
						and ex.shipping_date >= '".$enddate."'
						and g.goods_view = 'look'
						group by oi.goods_seq
						limit 20
					) t order by rand() desc limit 1";
			$query = $this->db->query($query);
			foreach ($query->result_array() as $row){

				// 카테고리정보
				$tmparr2	= array();
				$categorys	= $this->goodsmodel->get_goods_category($row['goods_seq']);
				foreach($categorys as $val){
					$tmparr = $this->categorymodel->split_category($val['category_code']);
					foreach($tmparr as $cate) $tmparr2[] = $cate;
				}
				if($tmparr2){
					$tmparr2 = array_values(array_unique($tmparr2));
					$row['r_category']	= $tmparr2;
				}

				//----> sale library 적용
				unset($param, $sales);
				$param['option_type']				= 'option';
				$param['consumer_price']			= $row['consumer_price'];
				$param['price']						= $row['price'];
				$param['total_price']				= $row['price'];
				$param['ea']						= 1;
				$param['category_code']				= $row['r_category'];
				$param['goods_seq']					= $row['goods_seq'];
				$param['goods']						= $row;
				$this->sale->set_init($param);
				$sales								= $this->sale->calculate_sale_price($applypage);
				$row['price']						= $sales['result_price'];
				$this->sale->reset_init();
				//<---- sale library 적용

				$result_recomm[] = $row;
			}
		}

		// 추천상품이 없을 경우
		if(!$result_recomm){
			$query = "
			select * from (
			select
			*,
			(select consumer_price from fm_goods_option where goods_seq = g.goods_seq and default_option='y' limit 1) as consumer_price,
			(select price from fm_goods_option where goods_seq = g.goods_seq and default_option='y' limit 1) as price,
			(select image from fm_goods_image where goods_seq = g.goods_seq and (image_type = 'list1' or image_type = 'list2') limit 1) as goods_img
			from fm_goods g
			order by
			g.like_count+g.review_count+g.purchase_ea+g.cart_count+g.wish_count desc limit 20
			) t
			order by rand()	limit 1";

			$query = $this->db->query($query);
			foreach ($query->result_array() as $row){

				// 카테고리정보
				$tmparr2	= array();
				$categorys	= $this->goodsmodel->get_goods_category($row['goods_seq']);
				foreach($categorys as $val){
					$tmparr = $this->categorymodel->split_category($val['category_code']);
					foreach($tmparr as $cate) $tmparr2[] = $cate;
				}
				if($tmparr2){
					$tmparr2 = array_values(array_unique($tmparr2));
					$row['r_category']	= $tmparr2;
				}

				//----> sale library 적용
				unset($param, $sales);
				$param['option_type']				= 'option';
				$param['consumer_price']			= $row['consumer_price'];
				$param['price']						= $row['price'];
				$param['total_price']				= $row['price'];
				$param['ea']						= 1;
				$param['category_code']				= $row['r_category'];
				$param['goods_seq']					= $row['goods_seq'];
				$param['goods']						= $row;
				$this->sale->set_init($param);
				$sales								= $this->sale->calculate_sale_price($applypage);
				$row['price']						= $sales['result_price'];
				$this->sale->reset_init();
				//<---- sale library 적용

				$result_recomm[] = $row;
			}
		}


		$this->template->assign(array('key'=>$key));
		$this->template->assign(array('skin'=>$this->skin));
		$this->template->assign(array('result'=>$result));
		$this->template->assign(array('result_recomm'=>$result_recomm));
		$this->template->define(array('tpl'=>$this->skin.'/goods/autocomplete.html'));
		$this->template->print_("tpl");
		//echo '</tr><tr><td align="right" valign="bottom"><a href="javascript:autocomplete_nouse();">기능끄기</a></td></tr></table>';
	}

	public function snslinkurl_tag(){
		$this->template->include_('snslinkurl');
		snslinkurl('goods', $_GET['goods_name']);
	}

	public function arrLayoutBasic(){
		$arrLayoutBasic = layout_config_load($this->skin,'basic');
		echo json_encode($arrLayoutBasic);
	}

	//SNSlink 짧은주소
	public function get_shortURL(){
		if($_GET['url'] && $this->arrSns['shorturl_use'] == 'Y' && $this->arrSns['shorturl_app_id'] && $this->arrSns['shorturl_app_key'] ){
			$sns_url_fa = get_shortURL(urlencode($_GET['url']));
		}
		if($_GET['jsoncallback']) {
			echo $_GET["jsoncallback"] ."(".json_encode($sns_url_fa).");";
		}else{
			echo json_encode($sns_url_fa);
		}
	}

	/* 우측 퀵메뉴 리스트 생성 (ajax 호출) */
	public function get_right_display(){
		$type = $_GET["type"];
		$page = $_GET["page"];
		$limit = $_GET["limit"];
		$result = array();
		$fname="recent";

		if ($type=="right_item_recent") {
			$today_view = $_COOKIE['today_view'];
			if( $today_view ) {
				$today_view = unserialize($today_view);
				krsort($today_view);
				$start = ($page-1)*$limit;
				if($limit) $today_view = array_slice($today_view,$start,$limit);
				$this->load->model('goodsmodel');
				$result = $this->goodsmodel->get_goods_list($today_view,'thumbScroll');
			}
		} else if ($type=="right_item_recomm") {
			$this->load->model('goodsmodel');
			$data = $this->goodsmodel->get_recommend_goods_list($page,$limit);
			$result = $this->goodsmodel->get_recommend_item($data);
			$fname="recommend";
		} else if ($type=="right_item_cart") {
			$this->load->model('cartmodel');
			$result = $this->cartmodel->get_right_cart_list($page,$limit);
			$fname="cart";
		} else if ($type=="right_item_wish") {
			if ($this->userInfo['member_seq']) {
				$member_seq = $this->userInfo['member_seq'];
				$this->load->model('wishmodel');
				$result = $this->wishmodel->get_right_wish_list($member_seq,$page,$limit);
			}
			$fname="wish";
		}

		if ($result) {
			foreach ($result as $key => $value) {
				if	($result[$key]['sale_price'])	$result[$key]['price']	= $result[$key]['sale_price'];
				$lenGood = strlen(strip_tags($result[$key]['goods_name']));
				if ($lenGood > 15) {
					$result[$key]['goods_name'] = getstrcut(strip_tags($result[$key]['goods_name']),15);
				}

				// 회원 등급별 가격대체문구 출력
				$result[$key]['string_price'] = get_string_price($result[$key]);
				$result[$key]['string_price_use']	= 0;
				if	($result[$key]['string_price'] != '')	$result[$key]['string_price_use']	= 1;

				$temp_price = "";
				if ($result[$key]['string_price_use']==1) {
					$temp_price = $result[$key]['string_price'];
				} else {
					$temp_price = number_format($result[$key]['sale_price'])."원";
				}
				$result[$key]['replace_price'] = $temp_price;
			}
		}

		$this->template->assign(array('dataRightQuicklist'=>$result));
		$this->template->define(array('tpl'=>$this->skin.'/_modules/display/right_'.$fname.'_display.html'));
		$this->template->print_("tpl");
	}	
	
	/* 우측 퀵메뉴 총개수 (ajax 호출) */
	public function get_right_total(){
		$type = $_GET["type"];

		if ($type=="right_item_cart") {
			$this->load->model('cartmodel');
			$total= number_format($this->cartmodel->get_cart_count());
		} else if ($type=="right_item_wish") {
			$total = 0;

			if ($this->userInfo['member_seq']) {
				$this->load->model('wishmodel');
				$total = $this->wishmodel->get_wish_count($this->userInfo['member_seq']);
			}
		} else if ($type=="right_item_recent") {
			$today_view = $_COOKIE['today_view'];
			$total = 0;
			if( $today_view ) {
				$today_view = unserialize($today_view);

				// DB에 존재하는 상품만 카운트 leewh 2014-11-18
				$this->load->model('goodsmodel');
				$result = $this->goodsmodel->get_goods_list($today_view,'thumbScroll');
				$total = count($result);
			}
		}
		echo $total;
	}

	/* 배송조회 URL 추출 */
	public function get_delivery_url(){
		$sql		= "select * from fm_config where groupcd = 'delivery_url'";
		$query		= $this->db->query($sql);
		$delivery	= $query->result_array();
		if	($delivery)foreach($delivery as $k => $data){
			$info	= unserialize(stripslashes($data['value']));
			if	($data['codecd'] && $info['url']){
				$result[$data['codecd']]	= $info['url'];
			}
		}

		echo json_encode($result);
	}

	/* data경로 체크 */
	public function _datapath_check($path){
		/* data폴더 하위가 맞으면 true 아니면 false */
		return preg_match("/^{$this->dataPath}/",$path) ? true : false;
	}

	/* 파일 업로드*/
	public function upload_file(){

		/* 이미지파일 확장자 */
		$this->arrImageExtensions = array('jpg','jpeg','png','gif','bmp','tif','pic');
		$this->arrImageExtensions = array_merge($this->arrImageExtensions,array_map('strtoupper',$this->arrImageExtensions));
	
		// 데모몰에서는 차단
		if($this->demo){
			$result = array(
				'status' => 0,
				'msg' => '데모몰에서는 업로드가 불가합니다.',
				'desc' => '업로드 불가'
			);
			echo "[".json_encode($result)."]";
			exit;
		}
	
		$result = array(
			'status' => 0,
			'msg' => '업로드 실패하였습니다.',
			'desc' => '업로드 실패'
		);

		$path = 'data/tmp';
		$targetPath = ROOTPATH.$path;

		if (!empty($_FILES)) {
		
			$fileName = $_POST['randomFilename'] ? "temp_".time().sprintf("%04d",rand(0,9999)) : $_FILES['Filedata']['name'];
		
			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$_FILES['Filedata']['name']) && !$_POST['allowKorean']){
				$result = array(
					'status' => 0,
					'msg' => '파일명에 한글 또는 특수문자가 포함되어있습니다.<br />영문 파일명으로 변경 후 업로드해주세요.',
					'desc' => '한글/특수문자 파일명 업로드 불가'
				);
			}else{
				$size = getimagesize($_FILES['Filedata']['tmp_name']);
				$_FILES['Filedata']['type'] = $size['mime'];
				$config['upload_path'] = $targetPath;
				$config['allowed_types'] = implode('|',$this->arrImageExtensions);
				$config['max_size']	= 2048; // 사용자 업로드는 2MB로 제한
				$config['file_name'] = $fileName;
				$config['overwrite'] = true;
				$this->load->library('upload', $config);
				if ( ! $this->upload->do_upload('Filedata'))
				{
					$result = array(
						'status' => 0,
						'msg' => $this->upload->display_errors(),
						'desc' => '업로드 실패'
					);
				}else{
					$fileInfo = $this->upload->data();
					$filePath = $path.'/'.$fileInfo['file_name'];
					@chmod($targetPath,0777);
					$result = array('status' => 1,'filePath' => $filePath,'fileInfo'=>$fileInfo);
				}
			}
		}
	
		echo "[".json_encode($result)."]";
	
	}
}

// END
/* End of file common.php */
/* Location: ./app/controllers/common.php */