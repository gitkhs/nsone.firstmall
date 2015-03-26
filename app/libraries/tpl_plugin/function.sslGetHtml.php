<?php

/* SSL 체크박스 HTML 반환 */
function sslGetHtml($section="")
{
	$CI =& get_instance();
	$CI->load->model('ssl');

	if(!$CI->ssl->ssl_use) return;
	
	switch($section){
		case "order"	: $postfixTitle = "을 통한 주문"; break;
		case "join"		: $postfixTitle = "을 통한 회원가입"; break;
		case "find_id"	: $postfixTitle = "을 통한 아이디찾기"; break;
		case "find_pw"	: $postfixTitle = "을 통한 비밀번호찾기"; break;
		case "login"	: $postfixTitle = ""; break;
		default			: $postfixTitle = ""; break;
	}
	
	$html = "<label><input type='checkbox' class='sslCheckBox' /> 보안접속 {$postfixTitle}</label>";
	
	return $html; 
}
?>