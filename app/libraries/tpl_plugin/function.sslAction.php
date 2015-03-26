<?php

/* SSL Form Action URL 반환 */
function sslAction($action)
{
	$CI =& get_instance();
	$CI->load->helper('url');
	$CI->load->model('ssl');

	if( defined('BOARDID') && ($CI->_is_mobile_agent || $CI->_is_mobile_domain)) return $action;//모바일인경우 첨부파일용때문에 보안제외

	if(!$CI->ssl->ssl_use) return $action;
	if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') return $action;

	if(!preg_match("/^http:\/\//",$action)){
		$protocol = 'http://';
		$domain = $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT']==80 ? '' : ':'.$_SERVER['SERVER_PORT'];

		if(preg_match("/^\//",$action)){
			$action = $protocol.$domain.$port.$action;
		}else{
			$action = $protocol.$domain.$port.'/'.dirname(uri_string()).'/'.$action;
		}
	}

	return $CI->ssl->get_ssl_action($action);

}
?>