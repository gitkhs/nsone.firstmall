<?php

function debug($str,$result =null){
	if( $_SERVER['REMOTE_ADDR']=='61.35.204.100' || $_SERVER['REMOTE_ADDR']=='106.246.242.226' ) {
		if($result) return true;
		debug_var($str);
	}
}

//TIME얻기
function getNowTimes()
{
	$MicroTsmp = explode(' ',microtime());
	return $MicroTsmp[0]+$MicroTsmp[1];
}

/**
* @주문 : 판매환경, 회원 : 가입환경
* @sitetype 선택값
* @formview : 0 명칭, 1 이미지명
* @form 출력방식 array 전체출력 , 그외 명칭출력 or image
**/
function sitetype($sitetype = NULL, $formview = 'image', $form = 'array'){
	$CI =& get_instance();

	$sitetypeary = array(
		"P"=>array("name"=>"PC", "image"=>"icon_list_pc.gif"),
		"M"=>array("name"=>"모바일/테블릿", "image"=>"icon_list_mobile.gif"),
		"F"=>array("name"=>"페이스북", "image"=>"icon_fb.gif")
	);
	if($form=='array'){
		return $sitetypeary;
	}else{
		if($formview == 'name'){
			return $sitetypeary[$sitetype]['name'];
		}else{
			if(is_file(ROOTPATH.'admin/skin/'.$CI->skin.'/images/common/icon/'.$sitetypeary[$sitetype]['image'])) {
				$imgtag = '<img src="../skin/'.$CI->skin.'/images/common/icon/'.$sitetypeary[$sitetype]['image'].'" alt="'.$sitetypeary[$sitetype]['name'].'" title="'.$sitetypeary[$sitetype]['name'].'" />';
			}else{
				$imgtag = $sitetypeary[$sitetype]['name'];
			}
			return $imgtag;
		}
	}
}

/**
* @주문 : 유입매체
* @marketplace 선택값
* @formview : 0 명칭, 1 이미지명
* @form 출력방식 array 전체출력 , 그외 명칭출력 or image
**/
function sitemarketplace($marketplace = NULL, $formview = 'image', $form = 'array'){
	$CI =& get_instance();

	$sitemarketplaceary = array(
		"daum_shopping"=>array("name"=>"쇼핑하우", "image"=>"icon_search_daum.gif"),
		"about"=>array("name"=>"어바웃", "image"=>"icon_search_about.gif"),
		"basket"=>array("name"=>"바스켓", "image"=>"icon_search_nate.gif"),
		"naver_shopping"=>array("name"=>"지식쇼핑", "image"=>"icon_search_naver.gif"),
		"google"=>array("name"=>"구글", "image"=>"icon_search_google.gif"),
		"etc"=>array("name"=>"기타", "image"=>"icon_search_etc.gif")
	);//"NO"=>array("name"=>"", "image"=>"icon_no"),
	if($form=='array'){
		return $sitemarketplaceary;
	}else{
		if($formview == 'name'){
			return $sitemarketplaceary[$marketplace]['name'];
		}else{
			if(is_file(ROOTPATH.'admin/skin/'.$CI->skin.'/images/common/icon/'.$sitemarketplaceary[$marketplace]['image'])) {
				$imgtag = '<img src="../skin/'.$CI->skin.'/images/common/icon/'.$sitemarketplaceary[$marketplace]['image'].'" alt="'.$sitemarketplaceary[$marketplace]['name'].'" title="'.$sitemarketplaceary[$marketplace]['name'].'" />';
			}else{
				$imgtag = $sitemarketplaceary[$marketplace]['name'];
			}
			return $imgtag;
		}
	}
}
//
function getSearchsitemarketplace($url)
{
	$CI =& get_instance();
	$CI->load->model('visitorlog');
	return $CI->visitorlog->get_referer_sitecd($url);
}

/**
* @회원 : 가입방법
* @rute 선택값
* @formview : 0 명칭, 1 이미지명
* @form 출력방식 array 전체출력 , 그외 명칭출력 or image
**/
function memberrute($rute = NULL, $formview = 'image', $form = 'array'){
	//$ruteary = array("none"=>array("name"=>"쇼핑몰ID", "image"=>"sns_home.gif"), "facebook"=>array("name"=>"Facebook", "image"=>"sns_f0.gif"), "twitter"=>array("name"=>"Twitter", "image"=>"sns_t0.gif"), "yozm"=>array("name"=>"요즘", "image"=>"sns_y0.gif"), "cyworld"=>array("name"=>"싸이월드", "image"=>"sns_c0.gif"), "me2day"=>array("name"=>"미투데이", "image"=>"sns_m0.gif"));//요즘서비스종료, "sns_y"=>array("name"=>"요즘", "image"=>"sns_y0.gif")
	$ruteary = array(
				"none"=>array("name"=>"쇼핑몰ID", "image"=>"sns_home.gif"),
				"sns_f"=>array("name"=>"Facebook", "image"=>"sns_f0.gif"), 
				"sns_t"=>array("name"=>"Twitter", "image"=>"sns_t0.gif"), 
				"sns_c"=>array("name"=>"싸이월드", "image"=>"sns_c0.gif"),
				"sns_m"=>array("name"=>"미투데이", "image"=>"sns_m0.gif"),
				"sns_n"=>array("name"=>"네이버", "image"=>"sns_n0.gif"),
				"sns_k"=>array("name"=>"카카오", "image"=>"sns_k0.gif"),
				"sns_d"=>array("name"=>"다음", "image"=>"sns_d0.gif")
		);
	unset($ruteary['sns_m']); //2014-07-01 서비스 종료

	if($form=='array'){
		return $ruteary;
	}else{
		if($formview == 'name'){
			return $ruteary[$rute][$formview];
		}else{
			if($formview == 'name'){
				return $ruteary[$rute]['name'];
			}else{
				if(is_file(ROOTPATH.'admin/skin/'.$CI->skin.'/images/common/icon/'.$ruteary[$rute]['image'])) {
					$imgtag = '<img src="../skin/'.$CI->skin.'/images/common/icon/'.$ruteary[$rute]['image'].'" alt="'.$ruteary[$rute]['name'].'"  title="'.$ruteary[$rute]['name'].'" />';
				}else{
					$imgtag = $ruteary[$rute]['name'];
				}
				return $imgtag;
			}
		}
	}
}

/* 용량포맷 */
function getSizeFormat($bytes){
	if($bytes>1024*1024) return number_format($bytes/1024/1024) . "MB";
	else if($bytes>1024) return number_format($bytes/1024) . "KB";
	else return number_format($bytes) . "Byte";
}

/* */
function str_split_arr($str, $gb, $number=0){
	$tmp_arr = explode($gb, $str);
	return $tmp_arr[$number];
}

/* 에디터 이미지 임시파일 경로 보정 */
function adjustEditorImages(&$contents, $savedir = '/data/editor/'){

	$CI =& get_instance();

	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');
	$host = ($CI->config_system['domain']) ? $CI->config_system['domain']:$CI->config_system['subDomain'];

	/*
	if(!empty($contents) && isset($_REQUEST['tx_attach_files'])){
		$tx_attach_files = $_REQUEST['tx_attach_files'];
		if(is_array($tx_attach_files)){
			foreach($tx_attach_files as $tx_attach_file){
				// 임시 파일경로
				$tPath = $tx_attach_file;

				// 에디터파일 경로
				$dPath = preg_replace("/^\/data\/tmp\//",$savedir,$tPath);

				// 파일 이동
				@rename(ROOTPATH.preg_replace("/^\//","",$tPath),ROOTPATH.preg_replace("/^\//","",$dPath));

				// 정규식 문자열처리
				$tPathForReg = str_replace(array("/","."),array("\/","\."),$tPath);

				// 보정
				$contents = preg_replace("/".$tPathForReg."/",$dPath,$contents);
			}
		}
	}
	*/

	// 임시파일업로드 ocw : 2012-07-23
	if(preg_match_all("/[\"|']?\/(data\/tmp\/[^\"']+)[\"|']?/",$contents,$matches)){

		foreach($matches[1] as $tPath){

			// 에디터파일 경로
			$dPath = preg_replace("/data\/tmp\//",$savedir,$tPath);

			// 파일 이동
			@rename(ROOTPATH.$tPath,ROOTPATH.$dPath);
			@chmod(ROOTPATH.$dPath,0777);

			// 정규식 문자열처리
			$tPathForReg = str_replace(array("/","."),array("\/","\."),$tPath);

			// 보정
			$contents = preg_replace("/\/".$tPathForReg."/",$dPath,$contents);
		}
	}

	## 정식 도메인에 슬래쉬를 포함한 하위디렉토리 입력시 치환 2014-12-17 pjm
	if($host){
		$host_tmp	= parse_url($host);
		$host		= $host_tmp['host'];
	}

	// 네임태그 보정
	$_POST['contents'] = preg_replace("/([\"|'])?(http:\/\/".$host.")?\/admin\/[^\"'#]+(#[^\"']+)([\"|'])?/","$1$3$4",$_POST['contents']);

	return $contents;
}


/* 업로드 이미지 임시파일 경로 보정 */
function adjustUploadImage($imagePath, $savedir, $newFileName=null){

	if(empty($imagePath)) return $imagePath;

	// 임시 파일경로
	$tPath = preg_replace("/^\//","",$imagePath);
	$savedir = preg_replace("/^\//","",$savedir);

	$tFilename = basename($tPath);
	$tmp = explode(".",$tFilename);
	$tFilename = $tmp[0];

	if(file_exists(ROOTPATH.$tPath)){
		// 에디터파일 경로
		$dPath = preg_replace("/^data\/tmp\//",$savedir,$tPath);

		if($tPath!=$dPath){
			if($newFileName){
				$dPath = preg_replace("/".$tFilename."/",$newFileName,$dPath);
			}

			if(file_exists(ROOTPATH.$dPath)){
				unlink(ROOTPATH.$dPath);
			}

			// 파일 이동
			@rename(ROOTPATH.$tPath,ROOTPATH.$dPath);
			@chmod(ROOTPATH.$dPath,0777);
		}
		$imagePath = "/".$dPath;
	}else{
		$imagePath = "/".$tPath;
	}

	return $imagePath;
}

function sendDirectMail($to_email = array(), $from_email, $title='', $contents=''){
	### SEND
	$CI =& get_instance();
	$CI->load->library('email');	

	foreach($to_email as $k){
		if(ereg("(^[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+)*@[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*$)", $k)) {
			$CI->email->from($from_email, $from_email);
			$CI->email->to($k);
			$CI->email->subject($title);
			$contents = str_replace('\\','',http_src($contents));
			$CI->email->message($contents);
			$CI->email->send();
			$CI->email->clear();
		}
	}
}

/* ini 파일 저장 */
function set_ini_file($filepath,$data,$useSection=false){
	if(file_exists($filepath)){

		$text = '';

		if($useSection){
			foreach($data as $section=>$row){
				$text .= "[".$section."]\r\n";
				foreach($row as $k=>$v){
					$text .= $k." = \"".$v."\"\r\n";
				}
				$text .= "\r\n";
			}
		}else{
			foreach($data as $k=>$v){
				$text .= $k." = \"".$v."\"\r\n";
			}
			$text .= "\r\n";
		}

		return file_put_contents($filepath,$text);
	}else return false;
}

/* 카테고리 리스트를 계층구조의 트리형태로 파싱하는 재귀함수 */
function divisionCategoryDepths($category_list,$category=array(),$idx_code=''){

	if(is_array($category_list)) foreach($category_list as $row){
		if(preg_match("/^{$idx_code}/",$row['category_code'])) {
			if(strlen($idx_code)+4 == strlen($row['category_code'])){
				$category[$row['category_code']] = $row;
				$category[$row['category_code']]['childs'] = array();
				$category[$row['category_code']]['childs'] = divisionCategoryDepths($category_list,$category[$row['category_code']]['childs'],$row['category_code']);
			}
		}
	}
	return $category;
}

/* 지역 리스트를 계층구조의 트리형태로 파싱하는 재귀함수 */
function divisionLocationDepths($category_list,$category=array(),$idx_code=''){
	if(is_array($category_list)) foreach($category_list as $row){
		if(preg_match("/^{$idx_code}/",$row['location_code'])) {
			if(strlen($idx_code)+4 == strlen($row['location_code'])){
				$category[$row['location_code']] = $row;
				$category[$row['location_code']]['childs'] = array();
				$category[$row['location_code']]['childs'] = divisionLocationDepths($category_list,$category[$row['location_code']]['childs'],$row['location_code']);
			}
		}
	}
	return $category;
}

/* fontDecoration json 값을 기준으로 HTML Element Attribute 반환
 * 호출 :	get_node_text_attr('{"color":"#363636", "font":"dotum", "size":"9"}','css','style');
 * 		get_node_text_attr('{"color":"#363636", "font":"dotum", "size":"9"}','script','onmouseover');
 * 반환 : return '"color:#363636;font-family:dotum;font-size:9pt"';
 */
function font_decoration_attr($string, $type, $attrName=null){
	$codes = $string ? json_decode($string) : array();
	$result = "";

	if($type=='css'){
		foreach($codes as $k=>$v){
			switch($k){
				case 'color':
					$result .= "color:{$v};";
				break;
				case 'font':
					$result .= "font-family:{$v};";
				break;
				case 'size':
					$result .= "font-size:{$v}pt;";
				break;
				case 'bold':
					$result .= "font-weight:{$v};";
				break;
				case 'underline':
					$result .= "text-decoration:{$v};";
				break;
			}
		}
	}

	if($type=='script'){
		foreach($codes as $k=>$v){
			switch($k){
				case 'color':
					$result .= "this.style.color='{$v}';";
				break;
				case 'font':
					$result .= "this.style.fontFamily='{$v}';";
				break;
				case 'size':
					$result .= "this.style.fontSize='{$v}pt';";
				break;
				case 'bold':
					$result .= "this.style.fontWeight='{$v}';";
				break;
				case 'underline':
					$result .= "this.style.textDecoration='{$v}';";
				break;
			}
		}
	}

	$result = $attrName.'="'.$result.'"';

	return $result;

}


function array_notnull($arr)
{
	if (!is_array($arr)) return;
	foreach ($arr as $k=>$v) if (!$v) unset($arr[$k]);
	return $arr;
}


function remove_value_in_array($arr,$values){
	if(is_array($arr)){
		if(!is_array($values)) $values = array($values);
		$result = array();
		foreach($arr as $k=>$v){
			if(!in_array($v,$values)){
				$result[$k] = $v;
			}
		}
		return $result;
	}else{
		return $arr;
	}
}

// 문자열 길이 구하기
function strBytes_for_sms($str)
{
	$strlen_var = strlen($str);
	$d = 0;
	$euckr_str = mb_convert_encoding($str,'EUC-KR','UTF-8');
	for ($c = 0; $c < $strlen_var; ++$c) {

		$ord_var_c = ord($euckr_str{$d});
		switch (true) {
			case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
				// characters U-00000000 - U-0000007F (same as ASCII)
				$d++;
			break;

			case (($ord_var_c & 0xE0) == 0xC0):
				// characters U-00000080 - U-000007FF, mask 110XXXXX
				// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				$d+=2;
			break;

			case (($ord_var_c & 0xF0) == 0xE0):
				// characters U-00000800 - U-0000FFFF, mask 1110XXXX
				// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				$d+=3;
			break;

			case (($ord_var_c & 0xF8) == 0xF0):
				// characters U-00010000 - U-001FFFFF, mask 11110XXX
				// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				$d+=4;
			break;

			case (($ord_var_c & 0xFC) == 0xF8):
				// characters U-00200000 - U-03FFFFFF, mask 111110XX
				// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				$d+=5;
			break;

			case (($ord_var_c & 0xFE) == 0xFC):
				// characters U-04000000 - U-7FFFFFFF, mask 1111110X
				// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				$d+=6;
				break;
				default:
				$d++;
		}

		if($d >= 80){
			$result[] = $c;
			$d = 0;
		}
	}

	if(!$result[0]) $result[0] = $strlen_var;

	return $result;
}




// utf-8 글자수 계산 함수
function strlen_utf8($str, $checkmb = false) {
	preg_match_all('/[\xE0-\xFF][\x80-\xFF]{2}|./', $str, $match); // target for BMP
	$m = $match[0];
	$mlen = count($m); // length of matched characters

	if (!$checkmb) return $mlen;
	$count=0;

	for ($i=0; $i < $mlen; $i++) {
		$count += ($checkmb && strlen($m[$i]) > 1)?2:1;
	}
	return $count;
}



function sendMail($to_email, $case, $params='', $data=array())
{

	## 개인맞춤형알림(예약 발송) 추가로 인한 메일구분
	$case_tmp = explode("_",$case);
	if($case_tmp[0] == "personal"){
		$email_mode = "email_personal";
		$gb			= "PERSONAL";
	}else{
		$email_mode = "email";
		$gb			= "AUTO";
	}

	$CI =& get_instance();
	$CI->config_basic	= ($CI->config_basic)?$CI->config_basic:config_load('basic');
	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');
	$CI->config_email	= ($CI->config_email['groupcd'] == $email_mode )?$CI->config_email:config_load($email_mode);

	$CI->config_basic['domain'] = ($CI->config_system['domain']) ? "http://".$CI->config_system['domain'] : "http://".$CI->config_system['subDomain'];

	$CI->load->library('email');
	$CI->email->mailtype='html';
	
	$from		= $CI->config_basic['companyEmail'];
	$fromname	= !$CI->config_basic['shopName'] ? 'http://'.$CI->config_basic['domain'] : $CI->config_basic['shopName'];

	###
	if($case == 'board_reply'){
		$mailFile = "../../data/email/cs.html";
	}else{
		$mailFile = "../../data/email/".$case.".html";
	}
	$bodyTpl = "";
	$sendCount = 0;
	$CI->template->assign('basic',$CI->config_basic);
	$CI->template->assign($data);
	$CI->template->define('tpl', $mailFile);
	$bodyTpl = $CI->template->fetch('tpl');
	$body	= trim($bodyTpl);

	$body	= str_replace("http://http://", "http://", $body);

	###

	switch($case){
		case 'board_reply'://문의게시판외 추가게시판 답변시 (관리자무조건제외)
			if(ereg("(^[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+)*@[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*$)", $to_email)) {
				$arr = sendCheck('cs', 'email', 'user', $data, '', $CI->config_email);
				$arrlog	= $arr;
				if(count($arr)>1){					

					$CI->email->from($from, $fromname);
					$CI->email->to($to_email);
					$CI->email->subject($arr[0]);
					$body = str_replace('\\','',http_src($body));
					$CI->email->message($body);
					$CI->email->send();
					$CI->email->clear();
				}
			}
			break;
		default://기타

			$senduse	= true;
			$adminsend	= true;		// admin 발송여부

			### USER
			if(ereg("(^[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+)*@[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*$)", $to_email)) {
				$arr		= sendCheck($case, $email_mode, 'user', $data, '', $CI->config_email);
				$arrlog		= $arr;

				# 리마인드일 경우 제목이 없으면 발송 안함. / admin 발송안함
				if($email_mode == "email_personal"){ 
					if(!trim($arr[0])){
						$senduse	= false;
						$errmsg		= "ERROR : Subject 누락";
					}
					$adminsend = false;
				}

				if(count($arr)>1 && $senduse){
					$CI->email->from($from, $fromname);
					$CI->email->to($to_email);
					$CI->email->subject($arr[0]);
					$body = str_replace('\\','',http_src($body));
					$CI->email->message($body);
					$CI->email->send();
					$CI->email->clear();

					$sendCount++;
				}
			}else{
				$senduse	= false;
				$errmsg		= "메일주소오류";
			}
			### ADMIN
			if($adminsend){
				$arr = sendCheck($case, 'email', 'admin', $data, '',$CI->config_email);

				$CI->config_email	= ($CI->config_email['groupcd'] == $email_mode )?$CI->config_email:config_load($email_mode);
				if($CI->config_email[$case."_admin_email"]) $from = $CI->config_email[$case."_admin_email"];

				if(count($arr)>1){

					$CI->email->from($from, $fromname);
					$CI->email->to($from);
					$CI->email->subject($arr[0]);
					$body = str_replace('\\','',http_src($body));
					$CI->email->message($body);
					$CI->email->send();
					$CI->email->clear();

					$sendCount++;
				}
			}

			break;
	}

	###
	$subject		= $arrlog[0];
	if	(!$subject){
		$subject	= ($headers['Subject'])?$headers['Subject']:$CI->config_email[$case."_title"];
	}

	### LOG
	if($email_mode == "email_personal"){
		### 고객리마인드서비스용 발송로그
		if($data['kind']){
			if($senduse){
				$sql = "select seq from fm_log_curation_summary where inflow_kind='".$data['kind']."' and send_date ='".date("Y-m-d",mktime())."'";
				$query	= $CI->db->query($sql);
				$res	= $query->row_array();
				if(!$res['seq']){
					$CI->db->query("insert into fm_log_curation_summary set inflow_kind='".$data['kind']."',send_sms_total=0,send_date ='".date("Y-m-d",mktime())."'");
					$summary_seq = $CI->db->insert_id();
				}else{
					$summary_seq = $res['seq'];
				}
			}else{ $summary_seq = 0; }

			if(!$subject) $subject = "[제목없음]";

			$memo = $errmsg;
			if($memo){ $memo .= "@@".serialize($CI->config_email)."@@".serialize($arrlog); }

			unset($log_params);
			$log_params['regist_date']	= date('Y-m-d H:i:s');
			$log_params['summary_seq']	= $summary_seq;
			$log_params['sendres']		= ($senduse)? 'y':'n';				//제목없으면 false, 발송안함.
			$log_params['kind']			= $data['kind'];
			$log_params['to_email']		= $to_email;
			$log_params['member_seq']	= $data['member_seq'];
			$log_params['subject']		= $subject;
			$log_params['contents']		= $body;
			$log_params['memo']			= $memo;
			$log_data = filter_keys($log_params, $CI->db->list_fields('fm_log_curation_email'));
			$log_result =  $CI->db->insert('fm_log_curation_email', $log_data);
			### 발송 통계
			if($log_result && $senduse){
				if($summary_seq){
					$CI->db->query("update fm_log_curation_summary set send_email_total=send_email_total+1 where seq='".$summary_seq."'");
				}else{
					$CI->db->query("insert into fm_log_curation_summary set inflow_kind='".$data['kind']."',send_email_total=1,send_date ='".date("Y-m-d",mktime())."'");
				}
			}
		}
	}else{
		## 일반 메일 발송로그
		if($sendCount > 0){
			unset($params);
			$order_seq = "";
			if (!empty($data['order_seq'])) $order_seq = $data['order_seq'];
			if (!empty($data['ordno'])) $order_seq = $data['ordno'];
			$params['regdate']		= date('Y-m-d H:i:s');
			$params['gb']			= $gb;
			$params['total']		= '1';
			$params['to_email']		= $to_email;
			$params['member_seq']	= $data['member_seq'];
			$params['subject']		= $subject;
			$params['contents']		= $body;
			$params['order_seq']	= $order_seq;
			$params_data = filter_keys($params, $CI->db->list_fields('fm_log_email'));
			$result =  $CI->db->insert('fm_log_email', $params_data);
		}
	}

	return true;
}



function sendCheck($case, $type, $gb = 'user', $params = array(),$order_no,$info=null){
	
	$CI		=& get_instance();
	if(!$info) $info	= config_load($type);

	$send_yn	= ($info[$case."_".$gb."_yn"])?$info[$case."_".$gb."_yn"]:'N';//$gb=='user' ? $info[$case."_".$gb."_yn"] : 'Y';

	// 쿠폰상품의 출고와 배송 완료는 무조건 발송.
	if	($gb == 'user' && in_array($case, array('coupon_released', 'coupon_delivery'))){
		$send_yn	= 'Y';
	}

	## 상품명 길이 제한 2014-08-27
	if($type == "sms"){
		$goods_limit     = config_load('sms_goods_limit');                 //게시판
	}

	$CI->config_basic	= ($CI->config_basic)?$CI->config_basic:config_load('basic');
	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');
	$CI->config_basic['domain'] = ($CI->config_system['domain']) ? $CI->config_system['domain'] : $CI->config_system['subDomain'];

	if( $send_yn != 'Y' && $gb=='user' && ($type=="sms" || $type=="sms_personal")){
		return false;
	}else if($send_yn != 'Y' && ($type=="email" || $type == "email_personal")){
		return false;
	}else{
		if( $order_no && !$params['ordno'] ) $params['ordno']	= $order_no;
		if( !$order_no && $params['ordno'] ) $order_no			= $params['ordno'];

		if( $order_no ) {
			if( !$params['goods_name'] ) {
				$items = get_data("fm_order_item",array("order_seq"=>$order_no));
				$params['goods_name']	= $items[0]['goods_name'];
				if	(count($items) > 1)
					$params['goods_name']	.= '외 '.(count($items) - 1).'건';
			}
		}
		if($params['goods_name']) $params['goods_name']	= strip_tags(str_replace("%","% ",$params['goods_name']));
		if($params['delivery_company']){
			$params['delivery_company'] = str_replace("(업무자동화)","",$params['delivery_company']);
		}

		$shopDomain	= ($CI->config_basic['domain']) ? "http://".$CI->config_basic['domain'] : "http://".$_SERVER['HTTP_HOST'];
		$replaceArr['{domain}'				]	= $shopDomain;
		$replaceArr['{shopDomain}'		]	= $shopDomain;
		$replaceArr['{shopName}'			]	= $CI->config_basic['shopName'				];
		$replaceArr['{사업자등록번호}'		]	= $CI->config_basic['businessLicense'		];
		$replaceArr['{통신판매업신고번호}'	]	= $CI->config_basic['mailsellingLicense'	];
		$replaceArr['{대표자}'					]	= $CI->config_basic['ceo'							];
		$replaceArr['{상점주소}'				]	= $CI->config_basic['companyAddress'		];
		$replaceArr['{상점전화}'				]	= $CI->config_basic['companyPhone'		];
		$replaceArr['{상점팩스}'				]	= $CI->config_basic['companyFax'				];
		$replaceArr['{password}'			]	= $params['passwd'				];
		$replaceArr['{쿠폰번호}'				]	= $params['couponNumber'		];
		$replaceArr['{계좌번호}'				]	= $params['bankinfo'				];
		$replaceArr['{boardName}'			]	= $params['board_name'			];	//board

		## 상품명 길이 제한 수정 2014-08-27
		if($type == "sms"){
		   if($goods_limit['go_item_use'] == 'y'){
				$go_item = getstrcut($params['goods_name'],$goods_limit['go_item_limit']);
		   }else{
				$go_item = $params['goods_name'];
		   }
		   if($goods_limit['ord_item_use'] == 'y'){
				$ord_item = getstrcut($params['goods_name'],$goods_limit['ord_item_limit']);
		   }else{
				$ord_item = $params['goods_name'];
		   }
		   if($goods_limit['repay_item_use'] == 'y'){
				$repay_item = getstrcut($params['goods_name'],$goods_limit['repay_item_limit']);
		   }else{
				$repay_item = $params['goods_name'];
		   }
		   if($goods_limit['goods_name_use'] == 'y'){
				$goods_name = getstrcut($params['goods_name'],$goods_limit['goods_name_limit']);
		   }else{
				$goods_name = $params['goods_name'];
		   }
		}

		$replaceArr['{go_item}'				]     = $go_item;
		$replaceArr['{ord_item}'			]     = $ord_item;
		$replaceArr['{repay_item}'			]     = $repay_item;
		$replaceArr['{goods_name}'			]     = $goods_name;

		$replaceArr['{bank_account}'		]	= $params['bank_account'		];
		$replaceArr['{userid}'				]	= $params['userid'				];
		$replaceArr['{user_name}'			]	= $params['user_name'			];
		$replaceArr['{username}'			]	= $params['user_name'			];
		$replaceArr['{userName}'			]	= $params['user_name'			];
		$replaceArr['{settle_kind}'			]	= $params['settle_kind'			];
		$replaceArr['{delivery_company}'	]	= $params['delivery_company'	];
		$replaceArr['{delivery_number}'		]	= $params['delivery_number'		];

		## 고객리마인드 서비스 관련 추가 2014-07-22
		$replaceArr['{coupon_count}'		]	= $params['coupon_count'		];		//만료되는 할인 쿠폰 갯수, 2014-07-22
		if($type == "sms_personal"){
			$replaceArr['{mypage_short_url}'	]	= $params['mypage_short_url_m'	];
		}elseif($type == "email_personal"){
			$replaceArr['{mypage_short_url}'	]	= $params['mypage_short_url_e'	];
		}
		$replaceArr['{mileage_rest}'		]	= $params['mileage_rest'		];		//마일리지

		###
		if(!$order_no){
			$replaceArr['{ordno}'			]	= $params['ordno'				];
			$replaceArr['{userid}'			]	= $params['userid'				];
			$replaceArr['{user_name}'		]	= $params['user_name'			];
		}else{
			$params['ordno'] = $order_no;
		}
		## etc
		foreach($params as $key => $val){
			$pattern	= '{'.$key.'}';
			if	(!$replaceArr[$pattern] && !is_array($val) && !is_numeric($key))
				$replaceArr[$pattern]	= $val;
		}

		### 회원정보 공통 치환
		$CI->load->model('membermodel');
		$replaceText	= $CI->membermodel->get_replacetext();
		foreach ($replaceText as $k => $v){
			$value	= '';
			if	($v['key']){
				if		(${$v['val']}[$v['key']])	$value	= ${$v['val']}[$v['key']];
				elseif	($v['val'] == 'params'){
					if		($params['member_seq']){
						if	(!$tmp)	$tmp	= $CI->membermodel->get_member_data($params['member_seq']);
						if	($tmp[$v['key']])		$value	= $tmp[$v['key']];
					}elseif	($params['userid']){
						if	(!$tmp)	$tmp	= $CI->membermodel->get_member_data_only($params['userid']);
						if	($tmp[$v['key']])		$value	= $tmp[$v['key']];
					}
				}
			}else{
				$value	= $$v['val'];
			}

			if	($v['type'] == 'number')
				$value	= number_format($value);

			$replaceTitleArr[$k]	= $value;
		}

		### 프로모션 발급 자동메일에서 치환안되는 값
		if(!$tmp) $tmp = $CI->membermodel->get_member_data($params['member_seq']);
		if(!$replaceArr['{username}'			]) $replaceArr['{username}'			]	= $tmp['user_name'			];
		if(!$replaceArr['{userid}'			]) $replaceArr['{userid}'			]	= $tmp['userid'				];
		
		//비회원이면 주문자명으로 대체
		if(!$replaceArr['{username}'] && $params['order_user_name'] ) $replaceArr['{username}'] = $params['order_user_name'];

		unset($tmp);
		###
		
		if( $params['ordno']){
			
			$orders = get_data("fm_order",array("order_seq"=>$params['ordno']));
			
			$replaceArr['{settleprice}']	 = number_format($orders[0]['settleprice'])."원";
			$replaceArr['{ordno}']	 = $params['ordno'];
			$replaceArr['{user_name}']	 = $orders[0]['order_user_name'];
			$replaceArr['{order_user}']		= $orders[0]['order_user_name'];
			switch($orders[0]['payment']){
				case "card": $temp_text = "신용카드 결제완료"; break;
				case "bank": $temp_text = substr($orders[0]['bank_account'],0,12)." 입금확인"; break;
				case "account": $temp_text = substr($orders[0]['bank_account'],0,12)." 계좌이체완료"; break;
				case "cellphone": $temp_text = "휴대폰 결제완료"; break;
				case "virtual": $temp_text = substr($orders[0]['virtual_account'],0,12)." 입금확인"; break;
				case "escrow_virtual": $temp_text = substr($orders[0]['virtual_account'],0,12)." 입금확인"; break;
				case "escrow_account": $temp_text = substr($orders[0]['bank_account'],0,12)." 계좌이체완료"; break;
				default: $temp_text = "결제완료"; break;
			}
			$replaceArr['{settle_kind}']	 = $temp_text;
			if($orders[0]['step']>='25' && $orders[0]['step']<='85'){
				if($params['export_code']){
					$exports = get_data("fm_goods_export",array("export_code"=>$params['export_code']));
				}else{
					$exports = get_data("fm_goods_export",array("order_seq"=>$params['ordno']));
				}
				if($exports){
					//받는분
					if($exports[0]['shipping_seq']){
						$shipping = get_data("fm_order_shipping",array("shipping_seq"=>$exports[0]['shipping_seq']));
						$replaceArr['{recipient_user}']	= $shipping[0]['recipient_user_name'];
					}
					$replaceArr['{export_code}']	 = $exports[0]['export_code'];
					if	(!$replaceArr['{delivery_number}'])
						$replaceArr['{delivery_number}']	 = $exports[0]['delivery_number'];
					if	(!$replaceArr['{delivery_company}']){
						$tmp = config_load('delivery_url',$exports[0]['delivery_company_code']);
						$replaceArr['{delivery_company}']	 = $tmp[$exports[0]['delivery_company_code']]['company'];
					}
				}
			}
		}

		foreach ($replaceArr as $key => $val){
			$patterns[]		= "/".$key."/";
			$replacements[]	= $val;

			$title_patterns[]		= "/".$key."/";
			$title_replacements[]	= $val;
		}
		foreach ($replaceTitleArr as $key => $val){
			$patterns[]		= "/".$key."/";
			$replacements[]	= $val;

			$title_patterns[]		= "/".$key."/";
			$title_replacements[]	= $val;
		}
		if($type=='sms'){
			$send_msg	= $info[$case."_".$gb];
			$msg	= preg_replace($patterns, $replacements, $send_msg);
			return $msg;
		}else{
			$send_msg	= $info[$case."_skin"];
			$msg		= preg_replace($patterns, $replacements, $send_msg);
			$send_title	= preg_replace($title_patterns, $title_replacements, $info[$case."_title"]);
			//$send_title	= $info[$case."_title"];

			if(in_array($case,array("personal_coupon","personal_review","personal_timesale","personal_cart","personal_membership","personal_emoney"))){ $case_title =$case."_title"; }else{ $case_title = ''; }
			
			$return = array($send_title, $msg,$case_title);
			return $return;
		}
	}
	return false;
}



function adminSendChK($case, $number=0){
	$CI		=& get_instance();
	$CI->config_sms = ($CI->config_sms)?$CI->config_sms:config_load('sms');
	$send_yn	= $CI->config_sms[$case."_admins_yn_".$number] ? $CI->config_sms[$case."_admins_yn_".$number] : "N";
	return $send_yn;
}


//개별메일발송
function getSendMail($data=array()){
	$CI =& get_instance();
	$CI->load->library('email');

	$CI->config_basic	= ($CI->config_basic)?$CI->config_basic:config_load('basic');
	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');
	$CI->config_basic['domain']	= ($CI->config_system['domain'])? $CI->config_system['domain'] : $CI->config_system['subDomain'];

	$title			= $data['title'];
	$contents		= $data['contents'];
	$email			= $data['email'];

	$body = adjustEditorImages($contents);
	if(ereg("(^[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+)*@[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*$)", $email)) {
		$from_email		= !$CI->config_basic['companyEmail'] ? 'gabia@gabia.com' : $CI->config_basic['companyEmail'];
		$from_name	= !$CI->config_basic['companyName'] ? 'http://'.$CI->config_basic['domain'] : $CI->config_basic['companyName'];		
		
		$CI->email->from($from_email, $from_name);
		$CI->email->to($email);
		$CI->email->subject($title);
		$body = str_replace('\\','',http_src($body));
		$CI->email->message($body);
		$CI->email->send();
		$CI->email->clear();
	}

	### LOG
	$emailparams['regdate']		= date('Y-m-d H:i:s');
	$emailparams['gb']				= 'MANUAL';
	$emailparams['from_email']	= $CI->config_basic['companyEmail'];
	$emailparams['subject']		= $title;
	$emailparams['contents']		= $body;
	$data = filter_keys($emailparams, $CI->db->list_fields('fm_log_email'));
	$CI->db->insert('fm_log_email', $data);
	return $resSend;
}




function printHangul($str){
	preg_match('/^([\x00-\x7e]|.{2})*/',$str,$r_str);
	return $r_str[0];
}



// ICONS
function find_icons($type = 'common'){
	$dir = ROOTPATH."data/icon/".$type;
	//echo $dir;
	if(!is_dir($dir)){
		if(!is_dir(ROOTPATH."data/icon")){
			@mkdir(ROOTPATH."data/icon");
			@chmod(ROOTPATH."data/icon",0777);
		}
		@mkdir($dir);
		@chmod($dir,0777);
	}
	$path = $dir;

	$icon = dir($path);
	if($icon){
		while ($entry = $icon->read()) {
			if (preg_match("/(\.gif)$/i", $entry) || preg_match("/(\.png)$/i", $entry) || preg_match("/(\.jpg)$/i", $entry) || preg_match("/(\.jpeg)$/i", $entry)){
				$retArray[] = $entry;
			}
		}
	}

	/* 설정 > 회원 > 등급 아이콘 등록일순 정렬 추가 2014-09-22 */
	if ($type=="common") {
		natsort($retArray);
		$arrFile1 = array();
		$arrFile2 = array();
		foreach($retArray as $key => $val) {
			if(strpos($val, "icon_grade") !== false) {
				$arrFile1[] = $val;
			} else {
				$arrFile2[] = $val;
			}
		}

		$retArray = array_merge($arrFile1, $arrFile2);
	}
	return $retArray;
}

// ADMIN > GOODS > LIST
function viewImg($goodSeq, $type, $img_size='N'){
	$CI =& get_instance();

	$CI->db->where(array('goods_seq'=>$goodSeq,'cut_number'=>'1','image_type'=>$type));
	$query = $CI->db->get('fm_goods_image');
	$data = $query->result_array();

	$size = config_load('goodsImageSize', $type);

	$data[0]['image'] = trim($data[0]['image']);
	if(preg_match('/http:\/\//',$data[0]['image'])){
		if($img_size=='Y'){
			return "<img src='".$data[0]['image']."' width='".$size[$type]['width']."' height='".$size[$type]['height']."'/>";
		}else{
			return $data[0]['image'];
		}
	}else if(!empty($data[0]['image']) && file_exists(ROOTPATH.$data[0]['image'])){
		if($img_size=='Y'){
			return "<img src='".$data[0]['image']."' width='".$size[$type]['width']."' height='".$size[$type]['height']."'/>";
		}else{
			return $data[0]['image'];
		}
	}else{
		if(substr($type,0,5)=='thumb'){
			if($img_size=='Y'){
				return "<img src='/admin/skin/default/images/common/noimage_list.gif' width='".$size[$type]['width']."' height='".$size[$type]['height']."'/>";
			}else{
				return "/admin/skin/default/images/common/noimage_list.gif";
			}
		}else{
			if($img_size=='Y'){
				return "<img src='/admin/skin/default/images/common/noimage.gif' width='".$size[$type]['width']."' height='".$size[$type]['height']."'/>";
			}else{
				return "/admin/skin/default/images/common/noimage.gif";
			}
		}
	}
}


/**
 * ajax 현재 페이지
 * @param array $search_context
 * @return int
 */
function get_current_page($search_context) {
	return (int)($search_context['page'] / $search_context['perpage']) + 1;
}

/**
 * ajax 페이지 갯수를 구한다.
 * @param array $search_context
 * @param int $total_count
 * @return int
 */
function get_page_count($search_context, $total_count) {
	$pagecount = (int)(($total_count + $search_context['perpage']  - 1) / $search_context['perpage'] );
	$pagecount = $pagecount == 0 ? 1 : $pagecount;
	return $pagecount;
}


function get_return_data($data, $number, $type = "*"){
	$len	= strlen($data) - $number;
	$f_str	= substr($data,0,$number);
	$e_str	= "";
	for($i=0;$i<$len;$i++){
		$e_str .= $type;
	}
	return $f_str.$e_str;
}



function getHtmlFile($file)
{
	if ($fp = fopen($file, "r"))
	{
		$data = fread($fp, filesize($file));
		fclose($fp);

		return $data;
	} else
	{
		return false;
	}
}


function setHtmlFile($file,$data,$enc=0,$charset="")
{
	if ($fp = fopen($file,"w"))
	{
		//if (strtolower($charset)!="euc-kr") $data = iconv("UTF-8","EUC-KR",$data);
		fwrite($fp,toInputBox($data, $enc));
		fclose($fp);
		@chmod($file, 0777);
		return true;
	} else
	{
		return false;
	}
}

function toInputBox($var,$enc=0)
{
	if (!$enc) $var = htmlspecialchars($var, ENT_QUOTES);
	return $var;
}


/*
* RELATED ITEM ###
*
*
*/
function get_related_goods($seq, $type, $count){
	$CI =& get_instance();
	$CI->load->model('goodsmodel');

	if(!$seq) return "";

	switch($type){
		case "AUTO":
			$CI->load->model('goodsdisplay');

			$sql = "select category_code from fm_category_link where goods_seq = ? and link = 1";
			$query = $CI->db->query($sql,$seq);
			$cate = $query->row_array();

			$sql = "select * from fm_goods where goods_seq = ?";
			$query = $CI->db->query($sql,$seq);
			$goods = $query->row_array();

			$sql = "select * from fm_design_display where kind = 'relation'";
			$query = $CI->db->query($sql);
			$display = $query->row_array();

			if($goods['relation_count_w']==0 && $goods['relation_count_h']==0){
				$display['count_w'] = 4;
				$display['count_h'] = 1;
			}else{
				$display['count_w'] = $goods['relation_count_w'];
				$display['count_h'] = $goods['relation_count_h'];
			}
			$display['image_size'] = $goods['relation_image_size'];
			$display['auto_criteria'] = $goods['relation_criteria'];

			$sc = $CI->goodsdisplay->search_condition($display['auto_criteria'], $sc,'relation');

			$sc['limit']	= $display['count_w']*$display['count_h'];
			$sc['sort']		= $sc['auto_order'];
			$sc['category'] = $cate['category_code'];

			$list = $CI->goodsmodel->goods_list($sc);

			return $list['record'];

			break;

		case "MANUAL":
			$CI->load->library('sale');
			$cfg_reserve	= ($CI->reserves) ? $CI->reserves : config_load('reserve');

			//----> sale library 적용
			$applypage						= 'relation';
			$param['cal_type']				= 'list';
			$param['reserve_cfg']			= $cfg_reserve;
			$param['member_seq']			= $CI->userInfo['member_seq'];
			$param['group_seq']				= $CI->userInfo['group_seq'];
			$CI->sale->set_init($param);
			$CI->sale->preload_set_config($applypage);
			//<---- sale library 적용

			$sql = "SELECT
				B.*, C.consumer_price, C.price
			FROM
				fm_goods_relation A
				LEFT JOIN fm_goods B ON A.relation_goods_seq = B.goods_seq
				LEFT JOIN fm_goods_option C ON A.relation_goods_seq = C.goods_seq AND C.default_option = 'y'
			WHERE
				A.goods_seq = {$seq}
				AND B.goods_status = 'normal'
				AND B.goods_view = 'look'
			ORDER BY
				relation_seq ASC
			limit {$count}";
			$query = $CI->db->query($sql);
			$result	= $query->result_array();
			if	($result)foreach($result as $k => $data){
				// 해당 상품의 전체 카테고리
				$category	= array();
				$tmp		= $CI->goodsmodel->get_goods_category($data['goods_seq']);
				foreach($tmp as $row)	$category[]		= $row['category_code'];

				$data['org_price']					= ($data['consumer_price']) ? $data['consumer_price'] : $data['price'];

				//----> sale library 적용
				unset($param, $sales);
				$param['option_type']				= 'option';
				$param['consumer_price']			= $data['consumer_price'];
				$param['price']						= $data['price'];
				$param['total_price']				= $data['price'];
				$param['ea']						= 1;
				$param['goods_ea']					= 1;
				$param['category_code']				= $category;
				$param['goods_seq']					= $data['goods_seq'];
				$param['goods']						= $data;
				$CI->sale->set_init($param);
				$sales								= $CI->sale->calculate_sale_price($applypage);
				$data['sale_price']					= $sales['result_price'];
				$CI->sale->reset_init();
				//<---- sale library 적용

				$goods_data[]	= $data;
			}

			return $goods_data;
			break;

	}
	return "";
}

//모바일접속체크
function isMobilecheck($agent) {
	$MobileArray = array("iphone","lgtelecom","skt","mobile","samsung","nokia","blackberry","android","iPad","sony","phone","x11");
	$checkCount = 0;
	for($i=0; $i<sizeof($MobileArray); $i++) {
		if($MobileArray[$i]=='skt'){
			if(preg_match("/$MobileArray[$i]/", strtolower($agent)) && !preg_match("/asktb/", strtolower($agent))){ return $MobileArray[$i];}
		}else{
			if(preg_match("/$MobileArray[$i]/", strtolower($agent))){ return $MobileArray[$i];}
		}
	}
	return '';
}

//IPhone 8 버전 체크
function isIPhoneVercheck($agent) {
	$MobileArray = array("8_1");
	$checkCount = 0;
	for($i=0; $i<sizeof($MobileArray); $i++) {
		if(preg_match("/$MobileArray[$i]/", strtolower($agent))){ return $MobileArray[$i];}
	}
	return '';
}

//bookmark 체크
function bookmarkckeck($bookmarkuser,$title)
{
	$CI =& get_instance();
	$reserves = ($CI->reserves)?$CI->reserves:config_load('reserve');
	$bm_url = ($CI->config_system['domain'])?$CI->config_system['domain']:$CI->config_system['subDomain'];
	$bm_url .="/main/index";

	if($reserves['default_reserve_bookmark'] > 0 && !$bookmarkuser ){
		if($title) {
			$bookmark = 'javascript:;"  onclick="bookmarksitelay(\'http://'.$bm_url.'\', \''.$title.'\',  \'/member/login?return_url='.urlencode($_SERVER['REQUEST_URI']).'\' )';
		}else{
			$bookmark = 'javascript:;"  onclick="bookmarksitelay(\'http://'.$bm_url.'\', \''.$CI->config_basic['shopName'].'\',  \'/member/login?return_url='.urlencode($_SERVER['REQUEST_URI']).'\' )';
		}
	}else{
		if($title) {
			$bookmark = 'javascript:;"  onclick="bookmarksite(\'http://'.$bm_url.'\', \''.$title.'\')';
		}else{
			$bookmark = 'javascript:;"  onclick="bookmarksite(\'http://'.$bm_url.'\', \''.$CI->config_basic['shopName'].'\')';
		}
	}
	return $bookmark;
}


function urlencode_rfc3986($input) {
	if (is_scalar($input)) {
			return str_replace('+',' ',str_replace('%7E', '~', rawurlencode($input)));
	} else {
		return '';
	}
}




function typereceipt_setting($order_seq, $seq = null){
	$CI =& get_instance();
	$order_config = config_load('order');
	$sql = "";

	###
	if($seq){
		//echo "seq";
		$sql = "SELECT * FROM fm_sales WHERE seq = '{$seq}' AND tstep = 1 AND typereceipt = 2";
	}else{
		//echo "order_seq";
		$CI->load->model('ordermodel');
		$orders	= $CI->ordermodel->get_order($order_seq);

		###
		if($orders['typereceipt']=="2"){
			$sql = "SELECT * FROM fm_sales WHERE order_seq = '{$order_seq}' AND tstep = 1 AND typereceipt = 2";
		}else{
			return false;
		}
	}
	$query = $CI->db->query($sql);
	$typereceipt = $query->result_array();

	//echo " T : ".$typereceipt[0]['seq'];
	if($typereceipt[0]['seq']){
		//echo $CI->config_system['pgCompany']." : ".$orders['cashreceipt_auto'];
		###
		if($CI->config_system['pgCompany']){
			$pg	= config_load($CI->config_system['pgCompany']);
			$CI->load->model('salesmodel');
			$CI->load->library('cashtax');

			$cashparams['creceipt_number']			= $typereceipt[0]['creceipt_number'];
			$cashparams['typereceipt'	]			= 2;
			$cashparams['type'			]			= $typereceipt[0]['type'];
			$cashparams['order_seq'		]			= $order_seq;
			$cashparams['member_seq']				= $typereceipt[0]['member_seq'];
			$cashparams['price'			]			= $typereceipt[0]['price'];
			$cashparams['person']					= $typereceipt[0]['person'];
			$cashparams['cuse']						= $typereceipt[0]['cuse'];
			$cashparams['goodsname'		]			= $typereceipt[0]['goodsname'];
			$cashparams['paydt']					= date("Y-m-d H:i:s");
			$cashparams['surtax'			]		= $typereceipt[0]['surtax'];
			$cashparams['supply'			]		= $typereceipt[0]['supply'];
			$cashparams['mallId'			]		= $pg['mallId'];

			$taxResult = $CI->cashtax->getCashTax('pay', $cashparams);

			//echo "taxResult : ".$taxResult."<br>";
			if (is_array($taxResult) == true)
			{
				$taxResult['seq']		= $typereceipt[0]['seq'];
				$taxResult['tstep']		= 2;//발급완료
				$taxResult['up_date']		= date("Y-m-d H:i:s");
				$taxResult['order_seq'] = $cashparams['order_seq'];
				$taxResult['pg_kind']	= $CI->config_system['pgCompany'];
				$CI->salesmodel->sales_modify($taxResult);
				$log_msg	= $CI->config_system['pgCompany'] . '(으)로 전송성공';
				$CI->salesmodel->sales_log_wirte($typereceipt[0]['seq'], $log_msg);
				return true;
			}
			else
			{
				$upResult['seq']		= $typereceipt[0]['seq'];
				$upResult['tstep']		= 4;//발급취소
				$upResult['order_seq']	= $cashparams['order_seq'];
				$CI->salesmodel->sales_modify($upResult);
				$CI->cashtax->getCashTax('mod', $cashparams);
				$log_msg	= $CI->config_system['pgCompany'] . '(으)로 전송실패'.$taxResult;
				$CI->salesmodel->sales_log_wirte($typereceipt[0]['seq'], $log_msg);
				return false;
			}


		}else{
			//$sql = "UPDATE fm_sales SET tstep = '2' WHERE seq = '{$typereceipt[0]['seq']}'";
			//$CI->db->query($sql);

			return true;
		}
	}
	return false;
}


###
function getSaleStatus($gb="pg", $type="text"){
	$CI =& get_instance();
	$orders = config_load('order');
	print_r($orders);
	switch($gb){
		case "pg":
			if($CI->config_system['pgCompany']){
				$param['type'] = true;
				$param['text'] = "자동발급";
			}else{
				$param['type'] = false;
				$param['text'] = "발급불가";
			}
			break;
		case "cash":
			if($orders['cashreceiptuse']){
				$param['type'] = true;
				$param['text'] = "자동발급";
			}else{
				$param['type'] = false;
				$param['text'] = "발급불가";
			}
			break;
	}
	return $param[$type];
}

function get_file_down($path, $filenm){
	$filenm = iconv('UTF-8', 'EUC-KR', $filenm);
	header('Content-type: application/octet-stream');
	header("Content-Disposition: attachment; filename=".$filenm."");
	readfile($path);
}

function get_args_list($exp=array('page')){
	if(!is_array($exp)) $exp = array($exp);
	$data = $_GET;
	foreach($exp as $v){
		if($v) unset($data[$v]);
	}
	return http_build_query($data, '', '&');
}

// paging (페이지당출력수,현재페이지넘버,페이지숫자링크갯수,쿼리,인자)
function select_page($number,$page,$page_number,$query,$bind,$SELECD_DB=null) {
	$CI =& get_instance();
	$count=1;
	$start = ($page-1)*$number;

	$query = trim($query)." limit $start , $number";
	$ar_return['record'] = array();

	if(!preg_match('/SQL_CALC_FOUND_ROWS/',$query)) {
		$query = preg_replace("/^select/i","select SQL_CALC_FOUND_ROWS",$query);
	}
	if($SELECD_DB){
		$query = $SELECD_DB->query($query,$bind);
	}else{
		$query = $CI->db->query($query,$bind);
	}

	$rquery = mysql_query("SELECT FOUND_ROWS()",$query->conn_id);
	list($totalcount) = mysql_fetch_array($rquery);

	foreach($query->result_array() as $row){
		$row['_no'] =$start+$count;
		$row['_rno'] =$totalcount-($start+$count)+1;
		$ar_return['record'][] = $row;
		$count++;
	}

	if( $totalcount%$number ) $totalpage = (int)($totalcount/$number)+1;
	else $totalpage = $totalcount/$number;

	$step = ceil($page/$page_number);

	$querystring = get_args_list();

	$ar_return['page']=array(
		'totalpage'=>$totalpage,
		'totalcount'=>$totalcount,
		'nowpage'=>$page,
		'page'=>array(),
		'next'=>false,
		'prev'=>false,
		'last'=>false,
		'first'=>false,
		'querystring'=>$querystring
	);

	if($step*$page_number<$totalpage) $ar_return['page']['next']=$step*$page_number+1;
	if($step!=1) $ar_return['page']['prev']=($step-1)*$page_number;

	if($ar_return['page']['prev']) $ar_return['page']['first']=1;
	if($ar_return['page']['next']) $ar_return['page']['last']=$totalpage;

	if($ar_return['page']['next']) $count=$page_number;
	else {
		if($totalpage) $count=$totalpage%$page_number ? $totalpage%$page_number : $page_number;
		else $count=0;
	}

	$loop_start = ($step-1)*$page_number+1;
	for($i=0;$i<$count;$i++)
	{
		$ar_return['page']['page'][$i]=$loop_start+$i;
	}

	$html = "";
	if($ar_return['page']['first'])	$html .= "<a href='?page={$ar_return['page']['first']}&amp;{$ar_return['page']['querystring']}' class='first'><span>◀ 처음</span></a>";
	if($ar_return['page']['prev'])	$html .= "<a href='?page={$ar_return['page']['prev']}&amp;{$ar_return['page']['querystring']}' class='prev'><span>◀ 이전</span></a>";
	foreach($ar_return['page']['page'] as $value){
		if($ar_return['page']['nowpage'] == $value){
			$html .= "<a href='?page={$value}&amp;{$ar_return['page']['querystring']}' class='on'>{$value}</a>";
		}else{
			$html .= "<a href='?page={$value}&amp;{$ar_return['page']['querystring']}'>{$value}</a>";
		}

	}

	if($ar_return['page']['next'])	$html .= "<a href='?page={$ar_return['page']['next']}&amp;{$ar_return['page']['querystring']}' class='next'><span> 다음 ▶</span></a>";
	if($ar_return['page']['last'])	$html .= "<a href='?page={$ar_return['page']['last']}&amp;{$ar_return['page']['querystring']}' class='last'><span> 마지막 ▶</span></a>";

	$ar_return['page']['html'] = $html;

	return $ar_return;
}

function login_check(){

	$CI =& get_instance();
	$session_arr = ( $CI->session->userdata('user') )?$CI->session->userdata('user'):$_SESSION['user'];

	if(!$session_arr['member_seq']){
		$url = "/member/login?return_url=".$_SERVER["REQUEST_URI"];

		if($_GET['designMode']){
			$msg = "마이페이지 영역의 페이지들을 정확하게 디자인하기 위해서 회원 로그인 해 주세요.\\n로그인 페이지에서 로그인 하면, 선택한 마이페이지 영역의 페이지로 바로 이동합니다.\\n이제, 마이페이지 영역의 페이지도 회원로그인 후 바로 EYE-DESIGN 하세요!";
		}else{
			$msg = "로그인이 필요한 페이지입니다.";
		}
		alert($msg);
		//if($CI->session->userdata('fammercemode')){
		if( $CI->fammerceMode  || $CI->storefammerceMode ) {
			pageRedirect($url,'','self');
		}else{
			pageRedirect($url,'','parent');
		}
		die();
	}
}

function login_check_confirm(){

	$CI =& get_instance();
	$session_arr = ( $CI->session->userdata('user') )?$CI->session->userdata('user'):$_SESSION['user'];

	if(!$session_arr['member_seq']){
		if( strstr($_SERVER["REQUEST_URI"],"popup=1") ) $_SERVER["REQUEST_URI"] = str_replace("popup=1","",$_SERVER["REQUEST_URI"]);
		$url = "/member/login?return_url=".$_SERVER["REQUEST_URI"]; 

		$msg = "로그인이 필요한 페이지입니다.<br/><strong>로그인하시겠습니까?</strong>"; 
		if ( $CI->returnpopup ) {//레이어인경우
			$yescallback = "parent.location.href='".$url."';";//opener
			openDialogConfirm($msg,400,160,'parent',$yescallback,'');
		}elseif( $CI->fammerceMode  || $CI->storefammerceMode ) {
			$yescallback = "self.location.href='".$url."';";
			openDialogConfirm($msg,400,160,'self',$yescallback,'');
		}else{
			$yescallback = "parent.location.href='".$url."';";
			openDialogConfirm($msg,400,160,'parent',$yescallback,'');
		}
		die();
	}
}

/**
 * 페이징.
 * @param int totalrows 게시글총건수
 * @param int perpage 현재페이지
 * @param text paginurl 링크url
 * @param text qstr href 이외의 onclick 등의 이벤트 예) onclick="window.open(this.href);return false;"
 * @param text query_string_segment 페이징 명칭시
 */
function pagingtag($totalrows, $perpage, $paginurl, $qstr, $query_string_segment='page', $anchor_class='')
{
	$CI =& get_instance();
	$CI->load->library('pagination');
	$config['suffix'] = $qstr;
	$config['num_links'] = 5;//본래글 좌우 출력숫자
	$config['page_query_string'] = TRUE;//?page=1  쿼리로 넘기기
	$config['query_string_segment'] = $query_string_segment;//'page';
	$config['base_url']		= $paginurl;//링크url
	$config['total_rows']	= $totalrows;//총갯수
	$config['per_page']		= $perpage;//출력페이지
	$config['anchor_class']		= $anchor_class;//출력페이지


	$config['prev_link'] = '<span class="prev btn"></span>';//◀ 이전
	$config['prev_tag_open'] = '';
	$config['prev_tag_close'] = '';

	$config['first_link'] = '<span class="first btn"></span>';//맨처음
	$config['last_link'] = '<span class="end btn"></span>';//맨마지막

	$config['next_link'] = '<span class="next btn "></span>';//다음 ▶
	$config['next_tag_open'] = '';
	$config['next_tag_close'] = '';

	$config['cur_tag_open'] = '<a class="on red">';
	$config['cur_tag_close'] = '</a>';
	$CI->pagination->initialize($config);
	return $CI->pagination->create_links();
}

/**
 * 페이징.
 * @param int totalrows 게시글총건수
 * @param int perpage 현재페이지
 * @param text paginurl 링크url
 * @param text qstr href 이외의 onclick 등의 이벤트 예) onclick="window.open(this.href);return false;"
 * @param text query_string_segment 페이징 명칭시
 */
function pagingtagfront($totalrows, $perpage, $paginurl, $qstr, $query_string_segment='page', $anchor_class='')
{
	$CI =& get_instance();
	$CI->load->library('pagination');
	$config['suffix'] = $qstr;
	$config['num_links'] = 5;//본래글 좌우 출력숫자
	$config['page_query_string'] = TRUE;//?page=1  쿼리로 넘기기
	$config['query_string_segment'] = $query_string_segment;//'page';
	$config['base_url']		= $paginurl;//링크url
	$config['total_rows']	= $totalrows;//총갯수
	$config['per_page']		= $perpage;//출력페이지
	$config['anchor_class']		= $anchor_class;//출력페이지

	if($CI->mobileMode || $CI->storemobileMode){
		$config['prev_link'] = '<span class="prev">◀ 이전</span>';//◀ 이전
		$config['prev_tag_open'] = '';
		$config['prev_tag_close'] = '';

		$config['first_link'] = '';//맨처음
		$config['last_link'] = '';//맨마지막

		$config['next_link'] = '<span class="next">다음 ▶</span>';//다음 ▶
		$config['next_tag_open'] = '';
		$config['next_tag_close'] = '';

		$config['display_pages'] = false;

		if( !empty($_GET['mobileAjaxCall'])){
		$config['anchor_class'] .= ' mobileAjaxCall="'.$_GET['mobileAjaxCall'].'" ';
		}

		$config['cur_tag_open'] = '<a class="on red">';
		$config['cur_tag_close'] = '</a>';
	}else{
		$config['prev_link'] = '<span class="prev">◀ 이전</span>';//◀ 이전
		$config['prev_tag_open'] = '';
		$config['prev_tag_close'] = '';

		$config['first_link'] = '<span class="first">맨처음</span>';//맨처음
		$config['last_link'] = '<span class="end">맨마지막</span>';//맨마지막

		$config['next_link'] = '<span class="next">다음 ▶</span>';//다음 ▶
		$config['next_tag_open'] = '';
		$config['next_tag_close'] = '';


		$config['cur_tag_open'] = '<a class="on red">';
		$config['cur_tag_close'] = '</a>';
	}

	$CI->pagination->initialize($config);
	return $CI->pagination->create_links();
}

function pagingtagjs($cur_page, $arr_block, $totalpage, $js_tag, $perblock = 10){
	if	(!$totalpage){
		// 데이터가 없을 경우 기본 페이징 노출
		$js				= str_replace('[:PAGE:]', 1, $js_tag);
		$paging_html	.= '<a onclick="' . $js . '" class="first hand"><span class="first btn"></span></a>&nbsp;';
		$paging_html	.= '<a onclick="' . $js . '" class="prev hand"><span class="prev btn"></span></a>&nbsp;';
		$paging_html	.= '<a class="on red">1</a>&nbsp;';
		$paging_html	.= '<a onclick="' . $js . '" class="next hand"><span class="next btn"></span></a>&nbsp;';
		$paging_html	.= '<a onclick="' . $js . '" class="end hand"><span class="end btn"></span></a>&nbsp;';
	}else{
		if	($cur_page > $perblock){
			$js				= str_replace('[:PAGE:]', 1, $js_tag);
			$paging_html	.= '<a onclick="' . $js . '" class="first hand"><span class="first btn"></span></a>&nbsp;';
			$prev_page		= ceil(($cur_page - $perblock) / $perblock) * $perblock;
			$js				= str_replace('[:PAGE:]', $prev_page, $js_tag);
			$paging_html	.= '<a onclick="' . $js . '" class="prev hand"><span class="prev btn "></span></a>&nbsp;';
		}

		foreach ($arr_block as $page){
			$js	= str_replace('[:PAGE:]', $page, $js_tag);
			if	($page == $cur_page)	$paging_html	.= '<a class="on red">' . $page . '</a>&nbsp;';
			else						$paging_html	.= '<a onclick="' . $js . '" class="hand">' . $page . '</a>&nbsp;';

			$block_end_page	= $page;
		}

		if	($block_end_page < $totalpage){
			$js				= str_replace('[:PAGE:]', ($block_end_page + 1), $js_tag);
			$paging_html	.= '<a onclick="' . $js . '" class="next hand"><span class="next btn"></span></a>&nbsp;';
			$js				= str_replace('[:PAGE:]', $totalpage, $js_tag);
			$paging_html	.= '<a onclick="' . $js . '" class="end hand"><span class="end btn "></span></a>&nbsp;';
		}
	}

	return preg_replace('/\&nbsp\;$/', '', $paging_html);
}

// 가격절삭
function get_price_point($price,$config_system='',$mode='sale'){
	if(!$config_system)	$config_system = config_load('system');

	$cutting_action = $config_system['cutting_'.$mode.'_action'];
	$cutting_price = $config_system['cutting_'.$mode.'_price'];
	$cutting_use = $config_system['cutting_'.$mode.'_use'];

	if($cutting_use == 'none') return $price;

	if( $cutting_action=='rounding' ){
		return round($price / $cutting_price) * $cutting_price;
	}else if($cutting_action=='dscending'){
		return floor($price / $cutting_price) * $cutting_price;
	}else if($cutting_action=='ascending'){
		return ceil($price / $cutting_price) * $cutting_price;
	}
	return $price;
}

function getLinkFilter($default,$arr)
{
	foreach($arr as $val) {
		if($val == 'page' || $val == 'cmtpage')continue;
		if (!empty($_GET[$val])) {
			if(is_array($_GET[$val])){
				foreach($_GET[$val] as $k=>$v){
					$default .= '&amp;'.@urlencode($val."[{$k}]").'='.@urlencode($v);
				}
			}else{
				$default .= '&amp;'.$val.'='.@urlencode($_GET[$val]);
			}
		}elseif (!empty($_POST[$val])) {
			if(is_array($_POST[$val])){
				foreach($_POST[$val] as $k=>$v){
					$default .= '&amp;'.@urlencode($val."[{$k}]").'='.@urlencode($v);
				}
			}else{
				$default .= '&amp;'.$val.'='.@urlencode($_POST[$val]);
			}
		}elseif(!empty($GLOBALS[$val])){
			if(is_array($GLOBALS[$val])){
				foreach($GLOBALS[$val] as $k=>$v){
					$default .= '&amp;'.@urlencode($val."[{$k}]").'='.@urlencode($v);
				}
			}else{
				$default .= '&amp;'.$val.'='.@urlencode($GLOBALS[$val]);
			}
		}
	}
	return $default;
}

//한글자름 -1 뒤에서 자를경우 추가 :: 게시글 작성자 글자숨김체크
function getstrcut( $str, $n = 500, $end_char = '...' , $minusnum = 1)
{
  $CI =& get_instance();
  $charset = $CI->config->item('charset');
  if ( mb_strlen( $str , $charset) < $n ) {
    return $str ;
  }

  $str = preg_replace( "/\s+/iu", ' ', str_replace( array( "\r\n", "\r", "\n" ), ' ', $str ) );

  if ( mb_strlen( $str , $charset) <= $n ) {
    return $str;
  }
  return (strstr($n,'-'))?mb_substr(trim($str), $n, $minusnum ,$charset) . $end_char:mb_substr(trim($str), 0, $n ,$charset) . $end_char ;
}

function getPageUrl($file_path) {
	$file_nm = end(explode("/",$file_path));
	$file_arr = explode(".",$file_nm);
	return $file_arr[0];
}

function get_query_string(){
	if($_SERVER['QUERY_STRING']){
		$tmp = explode("&",$_SERVER['QUERY_STRING']);
		foreach($tmp as $k=>$v){
			if(preg_match("/^query_string=/",$v)){
				unset($tmp[$k]);
			}
		}
		$_SERVER['QUERY_STRING'] = implode("&",$tmp);
	}
	return $_SERVER['QUERY_STRING'];
}


/**
*
* @
*/
function if_empty($arr=array(), $key=null, $default=null) {
	if (array_key_exists($key, $arr)) {
		if( empty($arr[$key]) ) {
			return $arr[$key] = $default;
		} else {
			return $arr[$key];
		}
	}
	return $default;
}

/**
 * include_keys에 있는 배열 항목만으로 배열을 구한다.
 * @param array $params
 * @param string $include_keys
 * @return array
 */
function filter_keys($params=array(), $include_keys=array()) {
	$new_arr = array();
	foreach ($params as $key => $val) {
		if($key != 'mode') {
			if ( in_array($key, $include_keys) ) {
				$new_arr[$key] = $params[$key];
			}
		}
	}
	return $new_arr;
}

function get_manager_name($manager_seq){
	$CI =& get_instance();
	$sql = "select mname from fm_manager where manager_seq = '{$manager_seq}'";
	$query = $CI->db->query($sql);
	$info = $query->result_array();
	$mname = $info[0]['mname'] ? $info[0]['mname'] : "";
	return $mname;
}


function get_provider_id($provider_seq){
	$CI =& get_instance();
	$sql = "select provider_id from fm_provider where provider_seq = '{$provider_seq}'";
	$query = $CI->db->query($sql);
	$info = $query->result_array();
	$mname = $info[0]['provider_id'] ? $info[0]['provider_id'] : "";
	return $mname;
}

function get_provider_seq($provider_id){
	$CI =& get_instance();
	$sql = "select provider_seq from fm_provider where provider_id = '{$provider_id}'";
	$query = $CI->db->query($sql);
	$info = $query->result_array();
	$mname = $info[0]['provider_seq'] ? $info[0]['provider_seq'] : "1";
	return $mname;
}


function get_use_check($type='point'){
	$reserves = config_load('reserve');
	$gb = $type."_use";
	if($reserves[$gb]=="Y") return true;
	return false;
}



function get_emoney_limitdate($type='order'){
	$limit_date = "";

	if($type=='order'){//주문1
		$reserve = config_load('reserve');
		if($reserve['reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$reserve['reserve_year']));
		}else if($reserve['reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$reserve['reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='join'){//가입시2
		$app = config_load('member');
		if($app['reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['reserve_year']));
		}else if($app['reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='recomm'){//추천받은자3
		$app = config_load('member');
		if($app['recomm_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['recomm_reserve_year']));
		}else if($app['recomm_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['recomm_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='joiner'){//추천한자4
		$app = config_load('member');
		if($app['joiner_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['joiner_reserve_year']));
		}else if($app['joiner_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['joiner_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='bookmark'){//즐겨찾기5
		$reserve = config_load('reserve');
		if($reserve['book_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$reserve['book_reserve_year']));
		}else if($reserve['book_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$reserve['book_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='invite'){//초대시 초대한자에게6
		$app = config_load('member');
		if($app['cnt_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['cnt_reserve_year']));
		}else if($app['cnt_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['cnt_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='invite_to'){//초대받은자가 가입시에 초대받은자에게7
		$app = config_load('member');
		if($app['invit_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['invit_reserve_year']));
		}else if($app['invit_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['invit_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='invite_from'){//초대받은자가 가입시 초대한자에게8
		$app = config_load('member');
		if($app['invited_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['invited_reserve_year']));
		}else if($app['invited_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['invited_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='photo_reserve'){//상품후기>사진9
		$app = config_load('reserve');
		if($app['photo_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['photo_reserve_year']));
		}else if($app['photo_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['photo_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='default_reserve'){//상품후기>게시글10
		$app = config_load('reserve');
		if($app['default_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['default_reserve_year']));
		}else if($app['default_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['default_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='video_reserve'){//상품후기>동영상11
		$app = config_load('reserve');
		if($app['video_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['video_reserve_year']));
		}else if($app['video_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['video_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='date_reserve'){//상품후기>특정기간 추가지급
		$app = config_load('reserve');
		if($app['date_reserve_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['date_reserve_year']));
		}else if($app['date_reserve_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['date_reserve_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='exchange_emoney'){//포인트 교환
		$app = config_load('reserve');
		if($app['exchange_emoney_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['exchange_emoney_year']));
		}else if($app['exchange_emoney_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['exchange_emoney_direct'], date("d"), date("Y")));
		}
	}


	return $limit_date;
}

function get_point_limitdate($type='order'){
	$limit_date = "";

	if($type=='order'){//주문1
		$reserve = config_load('reserve');
		if($reserve['point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$reserve['point_year']));
		}else if($reserve['point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$reserve['point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='join'){//가입2
		$app = config_load('member');
		if($app['point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['point_year']));
		}else if($app['point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='recomm'){//추천받은자3
		$app = config_load('member');
		if($app['recomm_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['recomm_point_year']));
		}else if($app['recomm_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['recomm_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='joiner'){//추천한자4
		$app = config_load('member');
		if($app['joiner_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['joiner_point_year']));
		}else if($app['joiner_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['joiner_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='bookmark'){//즐겨찾기5
		$reserve = config_load('reserve');
		if($reserve['book_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$reserve['book_point_year']));
		}else if($reserve['book_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$reserve['book_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='invite'){//추천6
		$app = config_load('member');
		if($app['cnt_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['cnt_point_year']));
		}else if($app['cnt_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['cnt_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='invite_to'){//초대받은자7
		$app = config_load('member');
		if($app['invit_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['invit_point_year']));
		}else if($app['invit_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['invit_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='invite_from'){//초대한자8
		$app = config_load('member');
		if($app['invited_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['invited_point_year']));
		}else if($app['invited_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['invited_point_direct'], date("d"), date("Y")));
		}
	}


	else if($type=='photo_point'){//상품후기>사진9
		$app = config_load('reserve');
		if($app['photo_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['photo_point_year']));
		}else if($app['photo_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['photo_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='default_point'){//상품후기>게시글10
		$app = config_load('reserve');
		if($app['default_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['default_point_year']));
		}else if($app['default_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['default_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='video_point'){//상품후기>동영상11
		$app = config_load('reserve');
		if($app['video_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['video_point_year']));
		}else if($app['video_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['video_point_direct'], date("d"), date("Y")));
		}
	}

	else if($type=='date_point'){//상품후기>특정기간 추가지급
		$app = config_load('reserve');
		if($app['date_point_select']=='year'){
			$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$app['date_point_year']));
		}else if($app['date_point_select']=='direct'){
			$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$app['date_point_direct'], date("d"), date("Y")));
		}
	}


	return $limit_date;
}


function get_member_money($type='cash', $member_seq){
	if(!$member_seq) return 0;
	$CI =& get_instance();
	$sql = "select * from fm_member where member_seq = '{$member_seq}'";
	$query = $CI->db->query($sql);
	$info = $query->result_array();
	return $info[0][$type];
}


function get_goods_point($price){
	$point = 0;
	$reserves = config_load('reserve');
	if($reserves['default_point_type']=='per'){
		 $point = (int) ($price * $reserves['default_point_percent'] / 100);
	}else{
		if($reserves['default_point_app']>0) $point = round($price / $reserves['default_point_app']) * $reserves['default_point'];
	}
	return $point;
}



function gift_order_check_all($gift_seq, $type, $total, $arr){
	$CI =& get_instance();
	if($type=='default'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}'";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}else if($type=='price'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}' AND eprice >= '{$total}' order by gift_seq desc limit 1";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}else if($type=='quantity'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}' AND eprice >= '{$total}' order by gift_seq desc limit 1";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= $info[0]['ea'];
	}else if($type=='lot'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}'";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;

	}

	if(count($garr)>0){
		$temp['goods']	= $garr;
		$temp['ea']		= $ea;
		return $temp;
	}else{
		return false;
	}
}


function gift_order_check_category($gift_seq, $type, $total, $arr){
	$CI =& get_instance();

	$sql = "SELECT * FROM fm_gift_choice WHERE choice_type = 'category' AND gift_seq = '{$gift_seq}'";
	$query	= $CI->db->query($sql);
	foreach($query->result_array() as $k){
		$cate[] = $k['category_code'];
	}

	$total = 0;
	for($i=0;$i<count($arr);$i++){
		$sql = "SELECT * FROM fm_category_link WHERE goods_seq = '{$arr[$i]['goods_seq']}' and category_code in ('".implode("','",$cate)."') limit 1";
		$query	= $CI->db->query($sql);
		$temp = $query->result_array();
		if($temp && $temp[0]['category_code']){
			$total +=  $arr[$i]['tot_price'];
		}
	}

	if($type=='default'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}'";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}else if($type=='price'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}' AND eprice >= '{$total}' order by gift_seq desc limit 1";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}else if($type=='quantity'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}' AND eprice >= '{$total}' order by gift_seq desc limit 1";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= $info[0]['ea'];
	}else if($type=='lot'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}'";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}

	if(count($garr)>0){
		$temp['goods']	= $garr;
		$temp['ea']		= $ea;
		return $temp;
	}else{
		return false;
	}
}

function gift_order_check_goods($gift_seq, $type, $total, $arr){
	$CI =& get_instance();

	$sql = "SELECT * FROM fm_gift_choice WHERE choice_type = 'goods' AND gift_seq = '{$gift_seq}'";
	$query	= $CI->db->query($sql);
	foreach($query->result_array() as $k){
		$goods[] = $k['goods_seq'];
	}
	$total = 0;
	for($i=0;$i<count($arr);$i++){
		if(in_array($arr[$i]['goods_seq'], $goods)){
			$total +=  $arr[$i]['tot_price'];
		}
	}

	if($type=='default'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}'";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}else if($type=='price'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}' AND eprice >= '{$total}' order by gift_seq desc limit 1";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= 1;
	}else if($type=='quantity'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}' AND eprice >= '{$total}' order by gift_seq desc limit 1";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= $info[0]['ea'];
	}else if($type=='lot'){
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' AND sprice <= '{$total}'";
		$query	= $CI->db->query($sql);
		$info	= $query->result_array();
		$garr	= explode("|",$info[0]['gift_goods_seq']);
		$ea		= $info[0]['ea'];
	}

	if(count($garr)>0){
		$temp['goods']	= $garr;
		$temp['ea']		= $ea;
		return $temp;
	}else{
		return false;
	}

}

function get_gift_image($goods_seq, $type){
	$CI =& get_instance();
	$sql = "SELECT * FROM fm_goods_image WHERE goods_seq = '{$goods_seq}' AND cut_number = 1 AND image_type = '{$type}'";
	$query	= $CI->db->query($sql);
	$info	= $query->result_array();
	return $info[0]['image'];
}

function get_gift_name($goods_seq){
	$CI =& get_instance();
	$sql = "SELECT * FROM fm_goods WHERE goods_seq = '{$goods_seq}' limit 1";
	$query	= $CI->db->query($sql);
	$info	= $query->result_array();
	return $info[0]['goods_name'];
}

function get_gift_stock($goods_seq){
	$CI =& get_instance();
	$sql = "SELECT * FROM fm_goods_option A LEFT JOIN fm_goods_supply B ON A.option_seq = B.option_seq WHERE A.goods_seq = '{$goods_seq}' limit 1";
	$query	= $CI->db->query($sql);
	$info	= $query->result_array();
	return $info[0]['stock'];
}



function get_category_name($category){
	$CI =& get_instance();
	$sql = "SELECT * FROM fm_category WHERE category_code = '{$category}'";
	$query	= $CI->db->query($sql);
	$info	= $query->result_array();

	$html = "";
	if($info[0]['node_type']=='image'){
		if($info[0]['node_image_over']){
			$html = "<img src='".$info[0]['node_image_normal']."' onmouseover=\"this.src='".$info[0]['node_image_over']."'\" onmouseout=\"this.src='".$info[0]['node_image_normal']."'\"/>";
		}else{
			$html = "<img src='".$info[0]['node_image_normal']."'/>";
		}
	}else{
		$html = $info[0]['title'];
	}
	return $html;
}

 function unescape($text){
	return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', create_function('$word','return iconv("UTF-16LE", "UTF-8", chr(hexdec(substr($word[1], 2, 2))).chr(hexdec(substr($word[1], 0, 2))));'), $text));
}

/* 다량옵션오류방지를 위하여 인코딩된 옵션폼값을 디코딩 */
function decodeFormValue($encodedFormValue="",$dataType="POST"){
	if(!empty($encodedFormValue)){
		$encodedFormValue = explode(",",$encodedFormValue);
		foreach($encodedFormValue as $item){
			list($key,$value) = explode("=",$item);
			preg_match("/\[(.*)\]/",$key,$matches);
			$tmp = explode("[",$key);
			$keyCode = $tmp[0];
			$keyString = $matches[0];
			$keyValue = urldecode($value);
			$eval = "\$_{$dataType}[{$keyCode}]{$keyString}=\$keyValue;";
			eval($eval);
		}
	}
}

/* 0보다 작으면 무조건 0 반환 */
function zerobase($num=0){
	if($num<0) $num = 0;
	return $num;
}

//동영상연동
function uccdomain($urltype=null,$file_key_w=null,$manager=null){
	$CI =& get_instance();
	$cfg_goods = config_load("goods");
	$uccdomain = $cfg_goods['ucc_domain'];
	$ucc_key = $cfg_goods['ucc_key'];

	if( ($CI->manager) || $manager ) {
		$video_use = ($manager)?$manager['video_use']:$CI->manager['video_use'];
	}else{
		$video_use = ($uccdomain && $ucc_key)?'Y':'N';
	}

	if(!( defined('__ADMIN__')  || defined('__SELLERADMIN__') ) && $video_use == 'N' ) return false;

	//web.mvod.고객 도메인
	switch($urltype) {
		case 'thumbnail':
			$uccscripturl= ($file_key_w)?'http://'.$uccdomain.'/flash_response/thumbnail_view.php?k='.$file_key_w:'';
		break;

		case 'fileinfo':
			/**
			* xml 파일정보
			* filename : 파일명,  //class_name : 분류명 //playtime :동영상플레이시간(초) //thumbnail_root(썸네일경로)
			**/
			$uccscripturl = ($file_key_w)?'http://'.$uccdomain.'/flash_response/get_fileinfo.php?k='.$file_key_w:'';
		break;

		case 'fileurl':
			$uccscripturl = ($file_key_w)?'http://'.$uccdomain.'/view_play.php?k='.$file_key_w:'';//play_r->view_play
		break;

		case 'fileswf':
			$uccscripturl = ($file_key_w)?'http://'.$uccdomain.'/swf/gplayer2.swf?host='.$uccdomain.'&k='.$file_key_w:'';
		break;

		default:
			//&c=분류코드 없음
			$uccscripturl = ($ucc_key && $video_use == 'Y' )?'http://'.$uccdomain.'/gabiaSmartHDUploader.js.php?e=utf-8&k='.$ucc_key:'';
		break;
	}
	return $uccscripturl;

}

function GetValueNameCheck($str , $name){
	if(!$str)return;
	if(!strstr($str,$name))return;

	$pos1 = 0;  //length의 시작 위치
	$pos2 = 0;  //:의 위치

	while( $pos1 <= strlen($str) )
	{
		$pos2 = strpos( $str , ":" , $pos1);
		$len = substr($str , $pos1 , $pos2 - $pos1);
		$key = substr($str , $pos2 + 1 , $len);
		$pos1 = $pos2 + $len + 1;
		if( $key == $name )
		{
			$pos2 = strpos( $str , ":" , $pos1);
			$len = substr($str , $pos1 , $pos2 - $pos1);
			$value = substr($str , $pos2 + 1 , $len);
			return $value;
		}
		else
		{
			// 다르면 스킵한다.
			$pos2 = strpos( $str , ":" , $pos1);
			$len = substr($str , $pos1 , $pos2 - $pos1);
			$pos1 = $pos2 + $len + 1;
		}
	}
}



/**
* @에딧터 동영상/플래시 치환
**/
function showdesignEditor($content) {
	if(!$content) return false;

	$CI =& get_instance();
	/* 플래시매직 치환 {=showDesignFlash(36)} */
	if(preg_match_all("/\{[\s]*=[\s]*showDesignFlash[\s]*\([\s]*([0-9]+)[\s]*\)[\s]*\}/",$content,$matches)){
		$CI->template->include_('showDesignFlash');
		foreach($matches[0] as $idx=>$val){
			$flash_seq = $matches[1][$idx];
			$replaceContents = showDesignFlash($flash_seq,true);
			$content = str_replace($val,$replaceContents,$content);
		}
	}

	/* 동영상 치환 {=showDesignVideo(67,"400X300")} */
	if(preg_match_all("/\{[\s]*=[\s]*showDesignVideo[\s]*\([\s]*([0-9]+)[\s]*,*\"[\s]*([0-9]+)[\s]*X[\s]*([0-9]+)[\s]*\"*\)[\s]*\}/",$content,$matches)){
		$CI->template->include_('showDesignVideo');
		foreach($matches[0] as $idx=>$val){
			$video_seq = $matches[1][$idx];
			$video_width = $matches[2][$idx];
			$video_height = $matches[3][$idx];
			$replaceContents = showDesignVideo($video_seq,$video_width."X".$video_height,true);
			$content = str_replace($val,$replaceContents,$content);
		}
	}

	/* 동영상 치환 {=showDesignVideo(39)} */
	if(preg_match_all("/\{[\s]*=[\s]*showDesignVideo[\s]*\([\s]*([0-9]+)[\s]*\)[\s]*\}/",$content,$matches)){
		$CI->template->include_('showDesignVideo');
		foreach($matches[0] as $idx=>$val){
			$video_seq = $matches[1][$idx];
			$replaceContents = showDesignVideo($video_seq,null,true);
			$content = str_replace($val,$replaceContents,$content);
		}
	}

	/* 슬라이드배너 치환 {=showDesignBanner(36)} */
	if(preg_match_all("/\{[\s]*=[\s]*showDesignBanner[\s]*\([\s]*([0-9]+)[\s]*\)[\s]*\}/",$content,$matches)){
		$CI->template->include_('showDesignBanner');
		foreach($matches[0] as $idx=>$val){
			$banner_seq = $matches[1][$idx];
			$replaceContents = showDesignBanner($banner_seq,true);
			$content = str_replace($val,$replaceContents,$content);
		}
	}

	return $content;
}


/**
* @페이스북 common
**/
function isfacebook() {

	$CI =& get_instance(); 
	$CI->arrSns = ($CI->arrSns)?$CI->arrSns:config_load('snssocial');
	$CI->joinform = ($CI->joinform)?$CI->joinform:config_load('joinform');
	//$CI->arrSns['key_f'] = 1;$CI->arrSns['likebox_f'] = 1;

	$CI->config_system = ($CI->config_system)?$CI->config_system:config_load('system');
	if( $CI->config_system['service']['code'] == 'P_STOR') {//marketshop domain check
		if( $CI->arrSns['key_f'] == '455616624457601' ) {
			if( !$CI->config_system['fbDomain'] ) {
				$fbDomainar = explode(".",$CI->config_system['subDomain']);
				$CI->config_system['fbDomain']	= $fbDomainar[0].".firstmall.kr";
			}
			$CI->arrSns['domain_f']	= $CI->config_system['fbDomain'];
		}
	}
	$CI->arrSns['sns_req_type']	= (isset($CI->arrSns['sns_req_type']))?$CI->arrSns['sns_req_type']:'FREE';//기본앱 FREE, 전용앱 그외
	 
	if( $CI->joinform['use_f'] || $CI->arrSns['fb_like_box_type'] != 'NO' ) {//페이스북의 회원과 좋아요 미사용시 해지
		$CI->__APP_USE__				= (isset($CI->arrSns['fb_use']))?$CI->arrSns['fb_use']:'f';
	}
	if( $CI->arrSns['key_f'] != '455616624457601' ) {//전용앱
		$CI->__APP_VER__				= (isset($CI->arrSns['fb_ver']))?$CI->arrSns['fb_ver']:'1.0';//버전 기본앱 1.0, 전용앱 2014-04-30 이후 2.0 
	}else{
		$CI->__APP_VER__ ='1.0';
	} 

	//publish_stream, -> feed 등록시 필요로 skin 상에서처리
	$CI->userauth		= 'email,user_friends,public_profile,publish_actions,read_friendlists,';
	$CI->adminauth		= 'email,user_friends,public_profile,manage_pages,publish_actions,read_friendlists,';
	if($CI->arrSns['key_f'] == '455616624457601' && $CI->__APP_VER__ == '1.0' ) {//기본앱
		$CI->userauth			.= 'user_birthday,';
		$CI->adminauth		.= 'user_birthday,';
	}
	

	if($CI->arrSns['key_f'] == '455616624457601') {//기본앱
		$CI->arrSns['facebook_app']	= 'basic';
		$CI->__APP_DOMAIN__			= (isset($CI->arrSns['domain_f']))?$CI->arrSns['domain_f']:$CI->config_system['subDomain'];
		$CI->arrSns['facebook_ob_like']	= 'Y';//facebook like opengraph yes
	}else{//전용앱
		$CI->arrSns['facebook_app']	= 'new';
		$CI->__APP_DOMAIN__		= (isset($CI->arrSns['domain_f']))?$CI->arrSns['domain_f']:'';
		$CI->__APP_LIKEBOX__		= (isset($CI->arrSns['likebox_f']))?$CI->arrSns['likebox_f']:'';
		if($CI->__APP_LIKEBOX__){
			$CI->arrSns['facebook_ob_like']	= 'Y';//facebook like opengraph yes
		}else{
			$CI->arrSns['facebook_ob_like']	= 'N';//facebook like opengraph no
		}
	}

	if($CI->__APP_DOMAIN__){
		if( strstr($_SERVER['HTTP_HOST'],"www.") && !strstr($CI->__APP_DOMAIN__,"www.") ){
			$CI->__APP_DOMAIN__ = "www.".$CI->__APP_DOMAIN__;
		}
		if( strstr($_SERVER['HTTP_HOST'],"m.")  && !strstr($CI->__APP_DOMAIN__,"m.") ){
			$CI->__APP_DOMAIN__ = "m.".$CI->__APP_DOMAIN__;
		}
	}
	if ($_SERVER['HTTPS'] == "on") {
		$CI->__APP_DOMAIN__ .= ":".$_SERVER['SERVER_PORT'];
	}

	$CI->__APP_ID__				= (isset($CI->arrSns['key_f']))?$CI->arrSns['key_f']:'';//'455616624457601'
	$CI->__APP_SECRET__		= (isset($CI->arrSns['secret_f']))?$CI->arrSns['secret_f']:'';//
	$CI->__APP_PAGE__			= (isset($CI->arrSns['page_id_f']))?$CI->arrSns['page_id_f']:'';
	$CI->__APP_NAMES__		= (isset($CI->arrSns['name_f']))?$CI->arrSns['name_f']:'';//fammerce_plus or add open grapy name
	$CI->__APP_STORY__		= (isset($CI->arrSns['story_f']))?$CI->arrSns['story_f']:'love';//love
	$CI->__APP_LIKE_TYPE__	= $CI->arrSns['fb_like_box_type'];

	//예전소스때문에 분석후 오픈그라피적용
	$CI->load->helper('file');
	$headerhtmlfile	= ROOTPATH."data/skin/".$CI->config_system['skin']."/_modules/common/html_header.html";
	$headerhtmlfilesource = read_file($headerhtmlfile);
	if( $CI->__APP_ID__ == '455616624457601' && strpos($headerhtmlfilesource,'content="{? APP_TYPE }{APP_TYPE}{:}website{/}"') ) {//2013-07-24 opengrapy ok!
		$CI->arrSns['objecttype_f']			= "product";//item or product
		$CI->arrSns['story_interests_f']  = 'wish';
		$CI->arrSns['story_write_f']		= 'review';
		$CI->arrSns['story_buy_f']			= 'buy';
	}

	$CI->__APP_STORY_INTERESTS__		= (isset($CI->arrSns['story_interests_f']))?$CI->arrSns['story_interests_f']:$CI->__APP_STORY__;
	$CI->__APP_STORY_WRITE__				= (isset($CI->arrSns['story_write_f']))?$CI->arrSns['story_write_f']:$CI->__APP_STORY__;
	$CI->__APP_STORY_BUY__					= (isset($CI->arrSns['story_buy_f']))?$CI->arrSns['story_buy_f']:$CI->__APP_STORY__;
	$CI->__APP_TYPE__							= (isset($CI->arrSns['objecttype_f']))?$CI->arrSns['objecttype_f']:'item';//item or product

	$CI->__TW_APP_KEY__						= (isset($CI->arrSns['key_t']))?$CI->arrSns['key_t']:'';//'ifHWJYpPA2ZGYDrdc5wQ'
	$CI->__TW_APP_SECRET__				= (isset($CI->arrSns['secret_t']))?$CI->arrSns['secret_t']:'';

	$CI->domainurl	= ($CI->config_system['domain'] ) ? 'http://'.$CI->config_system['domain']:'http://'.$CI->config_system['subDomain'];
	if($CI->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {//전용앱과 현재도메인이 동일한경우
		if ($_SERVER['HTTPS'] == "on") {
			$CI->domainurl	=  'https://'.$_SERVER['HTTP_HOST'];
		}else{
			$CI->domainurl	=  'http://'.$_SERVER['HTTP_HOST'];
		}
		if(!preg_match('/^admin\//',uri_string())){
			if( preg_match("/^m\./",$_SERVER['HTTP_HOST'])){
				$CI->config_system['subDomain'] = preg_replace("/^m\./","",$_SERVER['HTTP_HOST']);
			}else{
				$CI->config_system['subDomain']  = $_SERVER['HTTP_HOST'];
			}
		}

	}

	$CI->firstmallurl	= ($_SERVER['HTTPS'] == "on")?'https://'.$CI->config_system['subDomain']:'http://'.$CI->config_system['subDomain'];
	if($CI->arrSns['likeurl']){
		$CI->likeurl		= ($CI->__APP_ID__ != '455616624457601'  || !$CI->__APP_USE__ )?'http://'.$CI->arrSns['likeurl'].'/goods/view?':$CI->firstmallurl.'/goods/view?appid='.$CI->__APP_ID__;
	}else{
		$CI->likeurl		= ($CI->__APP_ID__ != '455616624457601'  || !$CI->__APP_USE__)?$CI->firstmallurl.'/goods/view?':$CI->firstmallurl.'/goods/view?appid='.$CI->__APP_ID__;
	}

	if(isset($CI->userauth)) $CI->template->assign('fbuserauth',		$CI->userauth);
	if($CI->session->userdata('fbuser')) $CI->template->assign('fbuser',$CI->session->userdata('fbuser'));//실제로그인된 경우
	if(is_file(ROOTPATH.$CI->config_system['snslogo'])) {//'/data/icon/favicon/'.
		$CI->template->assign('SNSLOGO',$CI->config_system['snslogo']);
	}

	$CI->is_file_facebook_result = false;
	if( $CI->arrSns['fb_like_box_type'] == 'API'  || !$CI->arrSns['fb_like_box_type'] ) {//직접방식
		$CI->is_file_facebook_result = true;
	}
	
	if ( strstr(uri_string(),'member/') || strstr(uri_string(),'mypage/') ) {//마이페이지
		$CI->joinform = ($CI->joinform)?$CI->joinform:config_load('joinform');
		if( $CI->joinform['use_f'] && $CI->is_file_facebook_result != true ) {//페이스북회원사용하면
			$CI->is_file_facebook_result = true;
		}
	}elseif ( strstr(uri_string(),'order/') || strstr(uri_string(),'goods/')) {//주문/장바구니  
		$CI->load->model('configsalemodel');//좋아요 할인 혜택여부 추가할인 추가적립
		$CI->systemfblike = $CI->configsalemodel->lists(array('type'=>'fblike'));
		$CI->template->assign('fblikesale',$CI->systemfblike['result']);
		$CI->template->assign('firstmallcartid',$CI->session->userdata('session_id'));
		if(  $CI->arrSns['fb_like_box_type'] == 'API' && count($CI->systemfblike['result'])  && $CI->is_file_facebook_result != true ) {//라이크할인혜택있으면
			$CI->is_file_facebook_result = true;
		}
	}
	
	if( $CI->is_file_facebook_result || in_array('register_sns_form',$CI->uri->rsegments)  || ( strstr(uri_string(),'order/complete') && $CI->arrSns['facebook_buy'] == 'Y') ) {
		$CI->is_file_facebook_tag = "<script type='text/javascript' src='/app/javascript/js/facebook.js?v=20140307' charset='utf8'></script>";
		$CI->template->assign('is_file_facebook',true);
		$CI->template->assign('is_file_facebook_tag',$CI->is_file_facebook_tag);
	}

	## 카카오 로그인 SDK
	if($CI->arrSns['use_k'] && $CI->arrSns['key_k']){
		$CI->is_file_kakao_tag = "<script src=\"/app/javascript/plugin/kakao/kakao.min.js\"></script>";
		$CI->template->assign('is_file_kakao_tag',$CI->is_file_kakao_tag);
	}
	
	$CI->template->assign(array(
		'APP_USE'=>$CI->__APP_USE__,
		'APP_VER'=>$CI->__APP_VER__, 
		'APP_LIKE_TYPE'=>$CI->__APP_LIKE_TYPE__,
		'APP_DOMAIN'=>$CI->__APP_DOMAIN__,
		'APP_ID'=>$CI->__APP_ID__,
		'APP_SECRET'=>$CI->__APP_SECRET__,
		'APP_PAGE'=>$CI->__APP_PAGE__,
		'APP_NAMES'=>$CI->__APP_NAMES__,
		'APP_STORY'=>$CI->__APP_STORY__,
		'APP_TYPE'=>$CI->__APP_TYPE__,
		'likeurl'=>$CI->likeurl,
		'url'=>$CI->firstmallurl.$_SERVER['REQUEST_URI'],
		'TW_APP_ID'=>$CI->__TW_APP_KEY__,
		'TW_APP_SECRET'=>	$CI->__TW_APP_SECRET__,
		'storyvideo',true)
	);
}


function won_print($price) {
	if($price%10000 == 0){
		return ($price/10000) . '만원';
	}else{
		return number_format($price) . '원';
	}
}

function get_sms_remind_count(){
	$CI =& get_instance();

	$limit	= commonCountSMS();

	$return['cnt']	= $limit;
	$return['link']	= "/admin/member/sms_charge";

	return $return;
}

// 쿠폰 인증번호 자동생성
function get_coupon_serialnumber($append_str = ''){
	$CI	=& get_instance();
	$CI->load->model('goodsmodel');

	$result	= true;
	while ($result){
		$coupon_serial	= strtoupper(substr(md5(uniqid('').$append_str), 0, 16));
		$result			= $CI->goodsmodel->chkDuple_coupon_serial($coupon_serial);
	}
	return $coupon_serial;
}

//상품상세/게시글보기/기타 연결 url 짧은주소표기
function get_shortURL($longURL, $shorturl=NULL) {
	$CI =& get_instance();
	$CI->arrSns	= ($CI->arrSns)?$CI->arrSns:config_load('snssocial');
	if( $shorturl ) {
		$shortURL_domain = $shorturl;
	}else{
		$shortURL_domain=($CI->arrSns['shorturl_domain'])?$CI->arrSns['shorturl_domain']:'bitly.com';
	}

	switch($shortURL_domain) {
	case "bit.ly" :
	case "j.mp" :
	case "bitly.com" :
		  $login			= ($CI->arrSns['shorturl_app_id'])?$CI->arrSns['shorturl_app_id']:'o_4tnp6nj2be';
		  $api_key	= ($CI->arrSns['shorturl_app_key'])?$CI->arrSns['shorturl_app_key']:'R_cd25129309f847a5bad9e988b978bef1';
		  $curlopt_url = "http://api.".$shortURL_domain."/v3/shorten?login=".$login."&apiKey=".$api_key."&uri=".$longURL."&format=txt";
		 break;
	case "is.gd" :
		  $curlopt_url = "http://is.gd/api.php?longurl=".$longURL;
		break;
	case "v.gd" :
		  $curlopt_url = "http://v.gd/create.php?format=simple&url=".$longURL;
		 break;
	case "to.ly" :
		  $curlopt_url = "http://to.ly/api.php?longurl=".$longURL;
		 break;
	case "goo.gl" :
		  $api_key = $CI->arrSns['shorturl_app_key'];//"API 열쇠";
		  $curlopt_url = "https://www.googleapis.com/urlshortener/v1/url?key=".$api_key;
		 break;
	case "durl.me" :
		  $curlopt_url = "http://durl.me/api/Create.do?type=json&longurl=".$longURL;
		 break;
	case "tinyurl" :
		  $curlopt_url = "http://tinyurl.com/api-create.php?url=".$longURL;
		break;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $curlopt_url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($shortURL_domain == "goo.gl" || $shortURL_domain == "durl.me") {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$jsonArray = array('longUrl' => $longURL);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonArray));
		$shortURL = curl_exec($ch);
		curl_close($ch);
		$result_array = json_decode($shortURL, true);

		if($result_array['shortUrl']) return $result_array['shortUrl']; // durl.me
		else if($result_array['id']) return $result_array['id']; // goo.gl
		else return false;
	}
	$shortURL = curl_exec($ch);
	curl_close($ch);

	if( ($shortURL_domain == "bitly.com") && bin2hex(substr($shortURL, -1, 1)) == "0a") $shortURL = substr($shortURL, 0, strlen($shortURL)-1);
	return $shortURL;
}

function like_count_print($fbcount) {
	if( $fbcount> 0 && ($fbcount%10000) == 0 ){
		return number_format($fbcount/10000) . '만';
	}else{
		return number_format($fbcount) . '';
	}
}

function http_src($content)
{
	$CI =& get_instance();
	$CI->config_system	= ($CI->config_system)?$CI->config_system:config_load('system');

	$host = ($CI->config_system['domain']) ? "http://".$CI->config_system['domain'] : "http://".$CI->config_system['subDomain'];
	$host = preg_replace("/:[0-9].+$/","",$host); //포트번호 삭제

	$pattern_a = array("@(\s*href|\s*src)(\s*=\s*'{1})(/[^']+)('{1})@ie"
					, "@(\s*href|\s*src)(\s*=\s*\"{1})(/[^\"]+)(\"{1})@ie"
					, "@(\s*href|\s*src)(\s*=\s*)(/[^\s>\"\']+)(\s|>)@ie"
	);
	$replace_a = "'\\1\\2".($host)."\\3\\4'";
	$content = preg_replace($pattern_a, $replace_a, $content);

	return $content;
}

// 특정테그만 삭제
function strip_tag_arrays($str, $strip_tags) {
	$cnt = sizeof($strip_tags);
	for ($i=0; $i<$cnt; $i++) {
		$tag_pattern = "<{$strip_tags[$i]}[^>]*>";
		$str = eregi_replace($tag_pattern, '', $str);
		$str = eregi_replace("</{$strip_tags[$i]}>", '', $str);
	}
	return $str;
}

// 가시이미지 로딩용 테그 만들기
function lazy_image($str)
{
	
	preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$str,$temp);
	$temp[1] = array_unique($temp[1]);	
	foreach($temp[1] as $a){
		$str = str_replace("src=\"".$a."\"","data-echo=\"".$a."\"",$str);
	}
	
	return $str;
}

## 접속브라우저 정보 확인
function getBrowser() 
{ 
	$u_agent	= $_SERVER['HTTP_USER_AGENT']; 
	$bname		= 'Unknown';
	$platform	= 'Unknown';
	$version	= "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) { $platform = 'linux'; }
	elseif (preg_match('/macintosh|mac os x/i', $u_agent)) { $platform = 'mac'; }
	elseif (preg_match('/windows|win32/i', $u_agent)) { $platform = 'windows'; }
	 
	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/(MSIE|Trident)/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { $bname = 'Internet Explorer'; $ub = "MSIE"; } 
	elseif(preg_match('/Firefox/i',$u_agent)) { $bname = 'Mozilla Firefox'; $ub = "Firefox"; } 
	elseif(preg_match('/Chrome/i',$u_agent)) { $bname = 'Google Chrome'; $ub = "Chrome"; } 
	elseif(preg_match('/Safari/i',$u_agent)) { $bname = 'Apple Safari'; $ub = "Safari"; } 
	elseif(preg_match('/Opera/i',$u_agent)) { $bname = 'Opera'; $ub = "Opera"; } 
	elseif(preg_match('/Netscape/i',$u_agent)) { $bname = 'Netscape'; $ub = "Netscape"; } 
	 
	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .
	')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}
	 
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){ $version= $matches['version'][0]; }
		else { $version= $matches['version'][1]; }
	}
	else { $version= $matches['version'][0]; }
	 
	// check if we have a number
	if ($version==null || $version=="") {$version="?";}
	return array('userAgent'=>$u_agent, 'name'=>$bname, 'nickname'=>$ub, 'version'=>$version, 'platform'=>$platform, 'pattern'=>$pattern);
}

//통합 SMS 발송
function commonSendSMS($smsDAta){
	$CI =& get_instance();

	require_once ROOTPATH."/app/libraries/sms.class.php";	

	$auth = config_load('master');

	$sms_id = $CI->config_system['service']['sms_id'];
	$sms_api_key = $auth['sms_auth'];

	$gabiaSmsApi = new gabiaSmsApi($sms_id,$sms_api_key);	

	$result['msg'] = $gabiaSmsApi->sendSMS($smsDAta);
	$result['code'] = $gabiaSmsApi->getResultCode();

	return $result;
}

function commonCountSMS(){

	$CI =& get_instance();

	include_once ROOTPATH."/app/libraries/sms.class.php";
	$auth = config_load('master');
	$sms_id = $CI->config_system['service']['sms_id'];
	$sms_api_key = $auth['sms_auth'];

	$gabiaSmsApi = new gabiaSmsApi($sms_id,$sms_api_key);
	$params	= "sms_id=" . $sms_id . '&sms_pw=' . md5($sms_id);
	$params = makeEncriptParam($params);
	$limit	= $gabiaSmsApi->getSmsCount();
	
	return $limit;
}

//브랜드 네이밍 오름차순 정렬
function firstmallplus_brand_asc($x, $y) {
	if ($x['title'] == $y['title']){
		return 0;
	} else if ($x['title'] > $y['title']) {
		return 1;
	} else {
		return -1;
	}
}

//브랜드 네이밍 내림차순 정렬
function firstmallplus_brand_desc($x, $y) {
	if ($x['title'] == $y['title']){
		return 0;
	} else if ($x['title'] < $y['title']) {
		return 1;
	} else {
		return -1;
	}
}
