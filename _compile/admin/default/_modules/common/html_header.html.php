<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/_modules/common/html_header.html 000004263 */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">
<head>
<title><?php echo $TPL_VAR["config_basic"]["shopName"]?><?php if(!preg_match('/order_print/',$_SERVER["REDIRECT_QUERY_STRING"])){?> 관리자환경 :: 퍼스트몰, 오직 운영자만을 생각한 가장 앞선 쇼핑몰입니다.<?php }?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/common.css" />
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/layout.css?20131119" />
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/buttons.css" />
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/boardnew.css" />
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/page.css" />
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/jqueryui/black-tie/jquery-ui-1.8.16.custom.css" />
<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/poshytip/style.css" />
<link rel="stylesheet" type="text/css" href="/app/javascript/plugin/editor/css/editor.css" />

<?php if($TPL_VAR["config_system"]["favicon"]){?>
<!-- 파비콘 -->
<link rel="shortcut icon" href="<?php echo $TPL_VAR["config_system"]["favicon"]?>" />
<?php }?>
<!-- 자바스크립트 [순서변경하지마세요] -->
<?php if(preg_match('/category\/catalog/',$_SERVER["REQUEST_URI"])){?>
<script type="text/javascript" src="/app/javascript/jquery/jquery.min.1.6.4.js"></script>
<?php }else{?>
<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>
<?php }?>
<script type="text/javascript" src="/app/javascript/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.hotkeys.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.poshytip.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.activity-indicator-1.0.0.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.cookie.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/custom-select-box.js"></script>
<script type="text/javascript" src="/app/javascript/js/dev-tools.js"></script>
<script type="text/javascript" src="/app/javascript/js/common.js?dummy=<?php echo date('YmdHis')?>"></script>
<script type="text/javascript" src="/app/javascript/js/admin-layout.js?dummy=<?php echo date('YmdHis')?>&krdomain=http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>"></script>
<?php if($TPL_VAR["video_use"]){?>
<script type="text/javascript">
var video_use = 'Y';//동영상설정여부
</script>
<?php }?>
<?php if($_GET["id"]){?>
<script type="text/javascript">
//<![CDATA[
var board_id = '<?php echo $_GET["id"]?>';
//]]>
</script>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/editor_loader.js?dummy=<?php echo date('Ymd')?>"></script>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/daum_editor_loader.js?dummy=<?php echo date('Ymd')?>"></script>
<?php }?>
<script type="text/javascript" src="/app/javascript/plugin/validate/jquery.validate.js"></script>

<script type="text/javascript">
//<![CDATA[
var isAdminpage = true;

//한글도메인체크@2013-03-12
var fdomain = document.domain;
var krdomain = 'http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>';
var kordomainck = false;
for(i=0; i<fdomain.length; i++){
 if (((fdomain.charCodeAt(i) > 0x3130 && fdomain.charCodeAt(i) < 0x318F) || (fdomain.charCodeAt(i) >= 0xAC00 && fdomain.charCodeAt(i) <= 0xD7A3)))
{
	kordomainck = true;
	break;
}
}
if( !kordomainck ){
krdomain = '';
}
//]]>
</script>