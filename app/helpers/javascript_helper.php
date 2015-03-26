<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 자바스크립트 관련 helper 모음.
 * @author gabia
 * @since version 1.0 - 2009. 7. 7.
 */

function js($content) {
	return "
	<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\n
	<script type=\"text/javascript\" charset=\"utf-8\">".$content."</script>
	";
}

function alert($msg) {
	echo js("alert('$msg')");
}

function pageRedirect($url, $msg = '', $target = 'self') {
	if ($msg) {
		alert($msg);
	}
	echo js($target . ".document.location.replace('$url')");
}

function pageLocation($url, $msg = '', $target = 'self') {
	if ($msg) {
		alert($msg);
	}
	echo js($target . ".document.location.href='$url'");
}

function pageBack($msg = '') {
	if ($msg) {
		alert($msg);
	}
	echo js("history.back();");
	exit;
}

function pageReload($msg = '', $target = 'self') {
	if ($msg) {
		alert($msg);
	}
	echo js($target . ".document.location.reload();");
	if($target=='parent' || $target=='top') echo js("document.location.href='about:blank';");
}

function pageClose($msg = '') {
	if ($msg) {
		alert($msg);
	}
	echo js("self.close();");
}

function openerRedirect($url, $msg = '') {
	if ($msg) {
		alert($msg);
	}
	echo js("opener.document.location.replace('$url')");
}

function openDialogAlert($msg,$width,$height,$target = 'self',$callback='',$options=array()) {
	$CI =& get_instance();
	if($CI->mobileMode || $CI->storemobileMode){
		$msg = str_replace(array("<br />","<br/>","<br>"),"",$msg);
		$msg = strip_tags($msg);
	}

	if (strpos($_SERVER['HTTP_USER_AGENT'], "Firefox") !== false) {
		if (strpos($callback, "location.reload()") !== false) $callback = str_replace("location.reload()","location.reload(true)",$callback);
	}
	echo("<script type='text/javascript'>");
	echo("{$target}.loadingStop('body',true);");
	echo("{$target}.loadingStop();");
	echo("{$target}.openDialogAlert('{$msg}','{$width}','{$height}',function(){{$callback}},".json_encode($options).");");
	echo("</script>");
}

function openDialogConfirm($msg,$width,$height,$target = 'self',$yesCallback='',$noCallback='') {
	$CI =& get_instance();
	if($CI->mobileMode || $CI->storemobileMode){
		$msg = str_replace(array("<br />","<br/>","<br>"),"",$msg);
		$msg = strip_tags($msg);
	}
	echo("<script type='text/javascript'>");
	echo("{$target}.loadingStop();");
	echo("{$target}.openDialogConfirm('{$msg}','{$width}','{$height}',function(){{$yesCallback}},function(){{$noCallback}});");
	echo("</script>");
}


// END
/* End of file helper.php */
/* Location: ./app/helper/javascript.php */
