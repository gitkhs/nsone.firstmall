<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function sql_injection_check($alert=true){
	
	if( $alert ) $injection['alert'] = $alert;
	
	$eregi_pattern = "\<script|delete[[:space:]]+from|drop[[:space:]]+database|drop[[:space:]]+table|drop[[:space:]]+column|drop[[:space:]]+procedure| create[[:space:]]+table|union[[:space:]]+all|update.+set.+=|insert[[:space:]]+into.+values|select.+from|bulk[[:space:]]+insert|or.+1[[:space:]]*=[[:space:]]1|alter[[:space:]]+table|into[[:space:]]+outfile|\/\*|\*\/";
		
	$injection['check'] = 'N';
	$injection['pattern_val'] = '';
	$injection['key'] = '';
	$injection['value'] = '';

	$CI =& get_instance();

	$managerInfo = $CI->session->userdata('manager');

	if(preg_match("/^admin\/(design|webftp)/i",uri_string())){
		$pass_post = array('tpl_source','tplSource','contents','mobile_contents','commonContents','re_contents','adminMemo','memo');
	}elseif(preg_match("/^admin\/(category|brand|location)/i",uri_string())){
		$pass_post = array('top_html');
	}elseif( $managerInfo['manager_seq'] ){
		$pass_post = array('contents','mobile_contents','commonContents','re_contents','adminMemo','memo');
	}
	
	// 자동 이메일 발송폼 저장 예외처리
	if(preg_match('/admin\/member_process\/email/',$_SERVER['REQUEST_URI'])){
		return true;
	}	

	foreach($_POST as $key => $value){
		if(@in_array($key,$pass_post)) continue;
		if( eregi($eregi_pattern,$value) ){
			$injection['check'] = 'POST';
			$injection['key'] = $key;
			$injection['value'] = $value;
			break;
		}			
		
	}

	if( $injection['check'] == 'N'){
		foreach($_GET as $key => $value){
			if( eregi($eregi_pattern,$value) ){
				$injection['check'] = 'GET';
				$injection['key'] = $key;
				$injection['value'] = $value;
				break;
			}			
		}
	}
	if( $injection['check'] == 'N'){
		$REQUEST_URI_ARRAY = explode("?",$_SERVER["REQUEST_URI"]);
		$get_value = $REQUEST_URI_ARRAY[1];
		if( eregi($eregi_pattern,$get_value) ){
			$injection['check'] = 'REQUEST_URI';
			$injection['value'] = $get_value;
	
		}	
	}
	
	if( $injection['check'] != 'N'){
	
		if( $injection['alert'] ){
			echo "<script language='javascript'>\n";
			echo "alert('유효하지 않은 문자가 체크되었습니다.');\n";
			echo "history.back();\n";
			echo "</script>\n";
			exit;
		}
	}
}

?>