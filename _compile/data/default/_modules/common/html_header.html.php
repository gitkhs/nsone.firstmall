<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/data/skin/default/_modules/common/html_header.html 000017202 */  $this->include_("naverWcsScript");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko" xmlns:fb="http://ogp.me/ns/fb#" xmlns:og="http://ogp.me/ns#" >
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# <?php if($TPL_VAR["APP_NAMES"]&&$TPL_VAR["APP_TYPE"]){?><?php echo $TPL_VAR["APP_NAMES"]?><?php }else{?>website<?php }?>: <?php if($TPL_VAR["APP_NAMES"]&&$TPL_VAR["APP_TYPE"]){?>http://ogp.me/ns/fb/<?php echo $TPL_VAR["APP_NAMES"]?><?php }else{?>http://ogp.me/ns/fb/website<?php }?>#">
<?php if(!$TPL_VAR["config_basic"]["metaTagUse"]){?>
<meta name="robots" content="noindex,nofollow">
<?php }?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title><?php if($TPL_VAR["shopTitle"]){?><?php echo strip_tags($TPL_VAR["shopTitle"])?><?php }elseif($TPL_VAR["subject"]){?><?php echo strip_tags($TPL_VAR["subject"])?><?php }elseif($TPL_VAR["goods"]["goods_name"]){?><?php echo strip_tags($TPL_VAR["goods"]["goods_name"])?><?php }elseif($TPL_VAR["config_basic"]["shopName"]){?><?php echo strip_tags($TPL_VAR["config_basic"]["shopName"])?><?php }?></title>

<meta name="generator" content="<?php if($TPL_VAR["meta"]["generator"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["meta"]["generator"]))?><?php }else{?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["shopName"]))?><?php }?>" />

<?php if($TPL_VAR["APP_USE"]=='f'){?>
	<meta name="title" content="<?php if($TPL_VAR["goods"]["goods_name"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["goods_name"]))?><?php }else{?><?php if($TPL_VAR["subject"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["subject"]))?><?php }else{?><?php if($TPL_VAR["shopTitle"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["shopTitle"]))?><?php }?><?php }?><?php }?>" />
	<meta name="description" content="<![CDATA[<?php if($TPL_VAR["goods"]["summary"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["summary"]))?><?php }else{?><?php if($TPL_VAR["subject"]&&$TPL_VAR["contents"]){?><?php echo getstrcut(htmlspecialchars(strip_tags($TPL_VAR["contents"])), 30)?><?php }else{?><?php if($TPL_VAR["mete"]["description"]){?> - <?php echo htmlspecialchars(strip_tags($TPL_VAR["mete"]["description"]))?><?php }?><?php if($TPL_VAR["config_basic"]["metaTagDescription"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["metaTagDescription"]))?><?php }else{?><?php }?><?php }?><?php }?>]]>" />
	<meta property="fb:app_id" content="<?php echo $TPL_VAR["APP_ID"]?>" />
	<meta property="og:url" content="<?php echo $TPL_VAR["url"]?>" />
<?php if($TPL_VAR["APP_NAMES"]=='fammerce_plus'&&$TPL_VAR["APP_TYPE"]=='item'){?>
		<meta property="og:type" content="<?php if($TPL_VAR["APP_TYPE"]){?><?php echo $TPL_VAR["APP_NAMES"]?>:<?php echo $TPL_VAR["APP_TYPE"]?><?php }else{?>website<?php }?>" />
<?php }else{?>
		<meta property="og:type" content="<?php if($TPL_VAR["APP_TYPE"]){?><?php echo $TPL_VAR["APP_TYPE"]?><?php }else{?>website<?php }?>" />
<?php }?>
	<meta property="og:site_name" content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["shopName"]))?>" />
	<meta property="og:title" content="<?php if($TPL_VAR["goods"]["goods_name"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["goods_name"]))?><?php }else{?><?php if($TPL_VAR["subject"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["subject"]))?><?php }else{?><?php if($TPL_VAR["shopTitle"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["shopTitle"]))?><?php }?><?php }?><?php }?>" />
<?php if(!$TPL_VAR["goods"]["summary"]&&$TPL_VAR["goods"]["common_contents"]){?>
		<meta property="og:description" content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["common_contents"]))?>" />
		<meta name="description" content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["common_contents"]))?>" />
<?php }else{?>
		<meta property="og:description" content="<?php if($TPL_VAR["goods"]["summary"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["summary"]))?><?php }else{?><?php if($TPL_VAR["subject"]&&$TPL_VAR["contents"]){?><?php echo getstrcut(htmlspecialchars(strip_tags($TPL_VAR["contents"])), 30)?><?php }else{?><?php if($TPL_VAR["mete"]["description"]){?> - <?php echo htmlspecialchars(strip_tags($TPL_VAR["mete"]["description"]))?><?php }?><?php if($TPL_VAR["config_basic"]["metaTagDescription"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["metaTagDescription"]))?><?php }else{?><?php }?><?php }?><?php }?>" />
		<meta name="description" content="<?php if($TPL_VAR["goods"]["summary"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["summary"]))?><?php }else{?><?php if($TPL_VAR["subject"]&&$TPL_VAR["contents"]){?><?php echo getstrcut(htmlspecialchars(strip_tags($TPL_VAR["contents"])), 30)?><?php }else{?><?php if($TPL_VAR["mete"]["description"]){?> - <?php echo htmlspecialchars(strip_tags($TPL_VAR["mete"]["description"]))?><?php }?><?php if($TPL_VAR["config_basic"]["metaTagDescription"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["metaTagDescription"]))?><?php }else{?><?php }?><?php }?><?php }?>" />
<?php }?>
<?php if($TPL_VAR["APP_NAMES"]&&$TPL_VAR["goods"]["goods_seq"]){?>
<?php if($TPL_VAR["APP_NAMES"]=='fammerce_plus'&&$TPL_VAR["APP_TYPE"]=='item'){?>
<?php if($TPL_VAR["goods"]["string_price_use"]){?>
				<meta property="<?php echo $TPL_VAR["APP_NAMES"]?>:price"    content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["string_price"]))?>" />
<?php }else{?>
<?php if($TPL_VAR["goods"]["consumer_price"]> 0){?>
					<meta property="<?php echo $TPL_VAR["APP_NAMES"]?>:price"    content="<?php echo number_format($TPL_VAR["goods"]["consumer_price"])?>원 →<?php echo number_format($TPL_VAR["goods"]["price"])?>원 (<?php echo number_format($TPL_VAR["goods"]["consumer_price"]-$TPL_VAR["goods"]["price"])?>원 할인)" />
<?php }else{?>
				<meta property="<?php echo $TPL_VAR["APP_NAMES"]?>:price"    content="<?php echo number_format($TPL_VAR["goods"]["price"])?>원" />
<?php }?>
<?php }?>
			  <meta property="<?php echo $TPL_VAR["APP_NAMES"]?>:url"      content="<?php echo $TPL_VAR["url"]?>" />
			  <meta property="<?php echo $TPL_VAR["APP_NAMES"]?>:category" content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["fbcategory_title"]))?>" />
<?php }else{?>
<?php if($TPL_VAR["goods"]["string_price_use"]){?>
					<meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:price:amount"    content="0" />
<?php }else{?>
<?php if($TPL_VAR["goods"]["consumer_price"]> 0){?>
						<meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:price:amount"    content="<?php echo ($TPL_VAR["goods"]["price"])?>" />
<?php }else{?>
					<meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:price:amount"    content="<?php echo $TPL_VAR["goods"]["price"]?>" />
<?php }?>
<?php }?>

<?php if($TPL_VAR["goods"]["brand"]){?>
				 <meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:brand" content="<?php echo strip_tags($TPL_VAR["view_brand"])?>" />
<?php }?>
				  <meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:price:currency"    content="KRW" />
				  <meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:product_link"      content="<?php echo $TPL_VAR["url"]?>" />
				  <meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:category" content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["fbcategory_title"]))?>" />
<?php }?>
<?php }?>
<?php if($TPL_VAR["APP_IMG"]){?>
<?php if($_SERVER["HTTPS"]=='on'){?>
		<meta property="og:image" content="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"  />
		<link rel="image_src" href="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"/>
<?php }else{?>
		<meta property="og:image" content="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"  />
		<link rel="image_src" href="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"/>
<?php }?>
<?php }?>
<?php }else{?>
	<meta name="title" content="[<?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["shopName"]))?>]<?php if($TPL_VAR["goods"]["goods_name"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["goods_name"]))?> <?php }else{?><?php if($TPL_VAR["subject"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["subject"]))?><?php }else{?><?php if($TPL_VAR["mete"]["description"]){?> - <?php echo htmlspecialchars(strip_tags($TPL_VAR["mete"]["description"]))?><?php }?><?php if($TPL_VAR["config_basic"]["metaTagDescription"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["metaTagDescription"]))?><?php }else{?><?php }?><?php }?><?php }?>" />
	<meta name="description" content="<?php if($TPL_VAR["goods"]["summary"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["summary"]))?><?php }else{?><?php if($TPL_VAR["subject"]&&$TPL_VAR["contents"]){?><?php echo getstrcut(htmlspecialchars(strip_tags($TPL_VAR["contents"])), 30)?><?php }else{?><?php if($TPL_VAR["mete"]["description"]){?> - <?php echo htmlspecialchars(strip_tags($TPL_VAR["mete"]["description"]))?><?php }?><?php if($TPL_VAR["config_basic"]["metaTagDescription"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["metaTagDescription"]))?><?php }else{?><?php }?><?php }?><?php }?>" />

<?php if($TPL_VAR["APP_IMG"]){?>
<?php if($_SERVER["HTTPS"]=='on'){?>
		<meta property="og:image" content="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"  />
		<link rel="image_src" href="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"/>
<?php }else{?>
		<meta property="og:image" content="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"  />
		<link rel="image_src" href="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["APP_IMG"]?>"/>
<?php }?>
<?php }?>

<?php }?>

<meta name="keywords" content="<?php if($TPL_VAR["goods"]["summary"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["summary"]))?><?php }?><?php if($TPL_VAR["config_basic"]["metaTagKeyword"]){?><?php echo htmlspecialchars(strip_tags($TPL_VAR["config_basic"]["metaTagKeyword"]))?><?php }else{?><?php }?>" />

<?php if(!$TPL_VAR["APP_IMG"]&&$TPL_VAR["SNSLOGO"]){?>
<?php if($_SERVER["HTTPS"]=='on'){?>
<meta property="og:image" content="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["SNSLOGO"]?>"  />
<link rel="image_src" href="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["SNSLOGO"]?>"/>
<?php }else{?>
<meta property="og:image" content="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["SNSLOGO"]?>"  />
<link rel="image_src" href="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["SNSLOGO"]?>"/>
<?php }?>
<?php }?>

<!-- CSS -->
<link rel="stylesheet" type="text/css" href="/data/font/font.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/default/css/common.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/default/css/buttons.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/default/css/jqueryui/black-tie/jquery-ui-1.8.16.custom.css" />
<?php if($TPL_VAR["ISADMIN"]||$TPL_VAR["writeditorjs"]){?>
<link rel="stylesheet" type="text/css" href="/app/javascript/plugin/editor/css/editor.css" />
<?php }?>
<?php if($TPL_VAR["config_system"]["favicon"]){?>
<!-- 파비콘 -->
<?php if($_SERVER["HTTPS"]=='on'){?>
<link rel="shortcut icon" href="https://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["config_system"]["favicon"]?>" />
<?php }else{?>
<link rel="shortcut icon" href="http://<?php echo $_SERVER["HTTP_HOST"]?>/<?php echo $TPL_VAR["config_system"]["favicon"]?>" />
<?php }?>
<?php }?>
<!-- /CSS -->

<!-- 자바스크립트 -->
<script type="text/javascript" src="/app/javascript/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/app/javascript/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.poshytip.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.activity-indicator-1.0.0.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.cookie.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.slides.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.placeholder.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/custom-select-box.js"></script>
<?php if($TPL_VAR["ISADMIN"]||$TPL_VAR["writeditorjs"]){?>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/editor_loader.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/daum_editor_loader.js?file_use=<?php echo $TPL_VAR["manager"]["file_use"]?>"></script>
<script type="text/javascript" src="/app/javascript/js/dev-tools.js"></script>
<script type="text/javascript" src="/app/javascript/js/design.js"></script>
<?php }?>
<script type="text/javascript" src="/app/javascript/js/common.js?dummy=<?php echo date('YmdHis')?>"></script>
<script type="text/javascript" src="/app/javascript/js/front-layout.js?dummy=<?php echo date('YmdHis')?>"></script>
<script type="text/javascript" src="/app/javascript/js/base64.js"></script>
<script type="text/javascript" src="/app/javascript/js/goods-display.js?dummy=<?php echo date('YmdHis')?>"></script>
<script type="text/javascript" src="/app/javascript/js/board-display.js?dummy=<?php echo date('YmdHis')?>"></script>
<script type="text/javascript" src="/data/skin/default/common/script.js"></script>

<script>
$(function(){
<?php if(!$TPL_VAR["ISADMIN"]){?>
<?php if($TPL_VAR["config_system"]["protectMouseRight"]){?>
			if($("#layout_body").length){
				$("#layout_body").bind('contextmenu',function(){return false;});
			}else{
				$(window).bind('contextmenu',function(){return false;});
			}
<?php }?>

<?php if($TPL_VAR["config_system"]["protectMouseDragcopy"]){?>
			var excludeTags=["input","textarea","select"];
			$(document).bind('selectstart',function(event){
				if(excludeTags.indexOf(event.target.tagName.toLowerCase())==-1) return false;
			});
<?php }?>
<?php }?>
<?php if($TPL_VAR["popular_search_complete"]=='y'||$TPL_VAR["auto_search_complete"]=='y'){?>
	$('form#topSearchForm input[name=search_text]').eq(0).bind("focusin keyup",function(event){
		var obj  = $(this);
		setTimeout(function(){
			autocomplete(obj.val());
		},100);
	});

	$('form#topSearchForm input[name=search_text]').eq(0).bind("blur",function(){
		setTimeout(function(){$('#autocomplete').hide();}, 500);
	});

<?php }?>

<?php if($TPL_VAR["passwordChange"]=="Y"){?>
		popup_change_pass();
<?php }?>
});
</script>


<style type="text/css">

/* 레이아웃설정 폰트 적용 */
#layout_body body,
#layout_body table,
#layout_body div,
#layout_body input,
#layout_body textarea,
#layout_body select,
#layout_body span
{
<?php if($TPL_VAR["layout_config"]["font"]){?>		font-family:"<?php echo $TPL_VAR["layout_config"]["font"]?>" !important; <?php }?>
}

/* 레이아웃설정 스크롤바색상 적용 */
<?php if($TPL_VAR["layout_config"]["scrollbarColor"]){?>
html, body, div, textarea {
	scrollbar-base-color:#ffffff;
	scrollbar-arrow-color:<?php echo $TPL_VAR["layout_config"]["scrollbarColor"]?>;
	scrollbar-shadow-color:<?php echo $TPL_VAR["layout_config"]["scrollbarColor"]?>;
	scrollbar-3dlight-color:#ffffff;
	scrollbar-highlight-color:<?php echo $TPL_VAR["layout_config"]["scrollbarColor"]?>;
	scrollbar-darkshadow-color:#ffffff;
	scrollbar-face-color:#ffffff;
}
<?php }?>
</style>
<?php if($TPL_VAR["config_basic"]["naver_wcs_use"]=='y'&&!$_GET["popup"]&&!$_GET["iframe"]){?>
<!--[ 네이버 공통유입 스크립트 ]-->
<?php echo naverWcsScript()?>

<?php }?>
<!-- /자바스크립트 -->
</head>

<body>
<?php if($TPL_VAR["APP_USE"]=='f'&&$TPL_VAR["is_file_facebook"]){?>
	<script type="text/javascript">
	//<![CDATA[
	 var is_user = false;
<?php if(defined('__ISUSER__')){?>
	 is_user = <?php echo defined('__ISUSER__')?>;
<?php }?>
	 var plus_app_id = '<?php echo $TPL_VAR["APP_ID"]?>';
	 var fammercemode = '<?php echo $TPL_VAR["fammercemode"]?>';
	 var fbId = "";
	 var fbAccessToken = "";
	 var isLogin = false;
	 var isFirst = true;
	 var fbUid = "";
	 var fbName = "";
	 var mbpage = false;
<?php if($TPL_VAR["login"]||$TPL_VAR["register"]){?>
	 mbpage = true;
<?php }?>

	 var orderpage = false;
<?php if($TPL_VAR["cartpage"]||$TPL_VAR["settlepage"]){?>
		orderpage = true;
<?php }?>

	//]]>
	</script>
	<!--facebook area-->
	<div id="fb-root"></div>
	<!--facebook area end-->
<?php }?>