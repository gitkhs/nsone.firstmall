<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/_modules/common/html_header.html 000033291 */  $this->include_("naverWcsScript");?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko"  xmlns:fb="http://ogp.me/ns/fb#"  xmlns:og="http://ogp.me/ns#" >
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# <?php if($TPL_VAR["APP_NAMES"]&&$TPL_VAR["APP_TYPE"]){?><?php echo $TPL_VAR["APP_NAMES"]?><?php }else{?>website<?php }?>: <?php if($TPL_VAR["APP_NAMES"]&&$TPL_VAR["APP_TYPE"]){?>http://ogp.me/ns/fb/<?php echo $TPL_VAR["APP_NAMES"]?><?php }else{?>http://ogp.me/ns/fb/website<?php }?>#">
<meta charset="utf-8">

<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">

<?php if(!$TPL_VAR["config_basic"]["metaTagUse"]){?>
<meta name="robots" content="noindex,nofollow">
<?php }?>
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
					<meta property="<?php echo $TPL_VAR["APP_TYPE"]?>:price:amount"    content="<?php echo htmlspecialchars(strip_tags($TPL_VAR["goods"]["string_price"]))?>" />
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
<link rel="stylesheet" type="text/css" href="/data/skin/mobile_ver2_default/css/common.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/mobile_ver2_default/css/buttons.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/mobile_ver2_default/css/quick_design.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/mobile_ver2_default/css/jqueryui/black-tie/jquery-ui-1.8.16.custom.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/mobile_ver2_default/css/mobile_pagination.css" />
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
<script type="text/javascript" src="/app/javascript/plugin/custom-mobile-pagination.js"></script>
<?php if(($TPL_VAR["ISADMIN"]||$TPL_VAR["writeditorjs"])&&!$_GET["mobileAjaxCall"]){?>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/editor_loader.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/daum_editor_loader.js?file_use=<?php echo $TPL_VAR["manager"]["file_use"]?>"></script>
<?php }?>
<script type="text/javascript" src="/app/javascript/plugin/validate/jquery.validate.js"  charset="utf-8"></script>
<script type="text/javascript" src="/app/javascript/js/dev-tools.js"></script>
<script type="text/javascript" src="/app/javascript/js/design.js"></script>
<script type="text/javascript" src="/app/javascript/js/common.js"></script>
<script type="text/javascript" src="/app/javascript/js/common-mobile.js"></script>
<script type="text/javascript" src="/app/javascript/js/front-layout.js"></script>
<script type="text/javascript" src="/app/javascript/js/base64.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/ezmark/js/jquery.ezmark.min.js"></script>
<script type="text/javascript" src="/data/skin/mobile_ver2_default/common/script.js"></script>
<!-- <script type="text/javascript" src="/app/javascript/js/goods-display.js"></script> -->
<link rel="stylesheet" type="text/css" href="/app/javascript/plugin/jquery_swipe/jquery_swipe.css" />
<script type="text/javascript" src="/app/javascript/plugin/jquery_swipe/jquery.event.swipe.js"></script>
<script>
var gl_mobile_mode = "<?php if($TPL_VAR["mobileMode"]||$TPL_VAR["storemobileMode"]){?>1<?php }else{?>0<?php }?>";

$(function(){

<?php if(!$TPL_VAR["ISADMIN"]){?>
<?php if($TPL_VAR["config_system"]["protectMouseRight"]){?>
			if($("#layout_body").length){
				$("#layout_body").bind('contextmenu',function(){return false;});
			}else{
				$(window).bind('contextmenu',function(){return false;});
			}
<?php }?>
<?php }?>

<?php if($TPL_VAR["popular_search_complete"]=='y'||$TPL_VAR["auto_search_complete"]=='y'){?>
	$('input[name=search_text]').eq(0).bind("focusin keyup",function(event){
		var obj  = $(this);
		setTimeout(function(){
			if($.browser.opera==true || $.browser.mozilla==true ) {
				 trick();
			} else {
				autocomplete(obj.val());
			}
		},100);
	});

	$('input[name=search_text]').eq(0).bind("blur",function(){
		setTimeout(function(){$('#autocomplete').hide();}, 100);
	});

<?php }?>

	$("#topSearchForm").submit(function(){
<?php if($TPL_VAR["auto_search_use"]=='y'){?>
<?php if($TPL_VAR["auto_search_type"]=="link"){?>
			if($('input[name=search_text]').val() == "<?php echo $TPL_VAR["auto_search_text"]?>"){
				window.open("<?php echo $TPL_VAR["auto_search_link"]?>");
				return false;
			}
<?php }else{?>
			return true;
<?php }?>
<?php }else{?>
			return true;
<?php }?>
    });

	// checkbox -> image mobile2 @2014-01-17
	 // to apply only to checkbox use:
	 $('input[type="checkbox"]').ezMark({
	  checkedCls: 'ez-checkbox-on'
	 });
	 // radio -> image mobile2 @2014-01-17
	 // for only radio buttons: .noradioimg
	 $('input[type="radio"]').ezMark({
	  selectedCls: 'ez-radio-on'
	 });
});

<?php if($TPL_VAR["auto_search_complete"]=='y'){?>
/*파이어폭스 트릭*/
var db = "";
function trick() {
  if (db != $('input[name=search_text]').eq(0).val()) {
      db = $('input[name=search_text]').eq(0).val();
      autocomplete(db);
  } else {
  }
  setTimeout("trick()", 100);
}
/*파이어폭스 트릭 끝*/
<?php }?>

window.onorientationchange = function(){

}
</script>

<?php if($TPL_VAR["APP_USE"]=='f'&&$TPL_VAR["is_file_facebook"]){?>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '<?php echo $TPL_VAR["APP_ID"]?>', //App ID
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true,  // parse XFBML,
      oauth      : true
    });

<?php if($TPL_VAR["fammercemode"]){?>
		FB.Canvas.setAutoGrow();
<?php }?>

<?php if(!($TPL_VAR["cartpage"]||$TPL_VAR["settlepage"])){?>
		// like 이벤트가 발생할때 호출된다.
		FB.Event.subscribe('edge.create', function(response) {
			$.ajax({'url' : '../sns_process/facebooklikeck', 'type' : 'post', 'data' : {'mode':'like', 'product_url':response}});
		});

		// unlike 이벤트가 발생할때 호출된다.
		FB.Event.subscribe('edge.remove', function(response) {
			$.ajax({'url' : '../sns_process/facebooklikeck', 'type' : 'post', 'data' : {'mode':'unlike', 'product_url':response}});
		});
<?php }?>

	// logout 이벤트가 발생할때 호출된다.
	FB.Event.subscribe('auth.logout', function(response) {

	});
  };
	$(document).ready(function() {
		//기본 login
		$(".fb-login-button").click(function(){
<?php if((strstr($_SERVER["HTTP_HOST"],'.firstmall.kr')||$_SERVER["HTTP_HOST"]==$TPL_VAR["APP_DOMAIN"])){?>
				FB.login(handelStatusChange,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }else{?>
				document.location.href = 'http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php if(strstr($_SERVER["REQUEST_URI"],"?")){?><?php echo $_SERVER["REQUEST_URI"]?>&handelStatusChange_autologin=1<?php }else{?><?php echo $_SERVER["REQUEST_URI"]?>?handelStatusChange_autologin=1<?php }?>';
<?php }?>
		});
<?php if($_GET["handelStatusChange_autologin"]){?>
			FB.login(handelStatusChange,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }?>

		//기업회원 login
		$(".fb-login-button-business").click(function(){
<?php if((strstr($_SERVER["HTTP_HOST"],'.firstmall.kr')||$_SERVER["HTTP_HOST"]==$TPL_VAR["APP_DOMAIN"])){?>
				FB.login(handelStatusChangebiz,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }else{?>
				document.location.href = 'http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php if(strstr($_SERVER["REQUEST_URI"],"?")){?><?php echo $_SERVER["REQUEST_URI"]?>&handelStatusChangebiz_autologin=1<?php }else{?><?php echo $_SERVER["REQUEST_URI"]?>?handelStatusChangebiz_autologin=1<?php }?>';
<?php }?>
		});
<?php if($_GET["handelStatusChangebiz_autologin"]){?>
			FB.login(handelStatusChangebiz,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }?>

		//회원통합 로그인(이메일동일)
		$(".fb-login-button-connect").click(function(){
<?php if((strstr($_SERVER["HTTP_HOST"],'.firstmall.kr')||$_SERVER["HTTP_HOST"]==$TPL_VAR["APP_DOMAIN"])){?>
				FB.login(handelStatusChangeconnect,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }else{?>
				document.location.href = 'http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php if(strstr($_SERVER["REQUEST_URI"],"?")){?><?php echo $_SERVER["REQUEST_URI"]?>&handelStatusChangeconnect_autologin=1<?php }else{?><?php echo $_SERVER["REQUEST_URI"]?>?handelStatusChangeconnect_autologin=1<?php }?>';
<?php }?>
		});
<?php if($_GET["handelStatusChangeconnect_autologin"]){?>
			FB.login(handelStatusChangeconnect,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }?>

		//새로가입(이메일동일함)
		$(".fb-login-button-noconnect").click(function(){
<?php if((strstr($_SERVER["HTTP_HOST"],'.firstmall.kr')||$_SERVER["HTTP_HOST"]==$TPL_VAR["APP_DOMAIN"])){?>
				FB.login(handelStatusChangenoconnect,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }else{?>
				document.location.href = 'http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php if(strstr($_SERVER["REQUEST_URI"],"?")){?><?php echo $_SERVER["REQUEST_URI"]?>&handelStatusChangenoconnect_autologin=1<?php }else{?><?php echo $_SERVER["REQUEST_URI"]?>?handelStatusChangenoconnect_autologin=1<?php }?>';
<?php }?>
		});

<?php if($_GET["handelStatusChangenoconnect_autologin"]){?>
			FB.login(handelStatusChangenoconnect,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
<?php }?>
	});
</script>
<script>(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
js = d.createElement(s); js.id = id;
js.src = "//connect.facebook.net/ko_KR/all.js";//#xfbml=1&appId=<?php echo $TPL_VAR["APP_ID"]?>

fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
 <script type="text/javascript">
  var fbId = "";
  var fbAccessToken = "";
  var isLogin = false;
  var isFirst = true;
  var fbUid = "";
  var fbName = "";
	function handelStatusChange(response) {
		if (response && response.status == 'connected') {
		// 로그인
		isLogin = true;
		initializeFbTokenValues();
		initializeFbUserValues();
		fbId = response.authResponse.userID;
		fbAccessToken = response.authResponse.accessToken;
		FB.api('/me', function(response) {
			 fbUid = response.email;
			 fbName = response.name;
			 if (fbName != "") {
<?php if(!defined('__ISUSER__')){?>
				loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
					$.ajax({
					'url' : '../sns_process/facebookloginck',
					'type' : 'post',
					'dataType': 'json',
					'success': function(res) {
						if(res.result == 'emailck'){//이메일이 등록된 경우
							openDialog("회원 통합하기  <span class='desc'>로그인해 주세요.</span>", "member_facebook_connect", {"width":"470","height":"250"});
						}else if(res.result == true){
<?php if($_GET["return_url"]){?>
								document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
								document.location.href='/mypage/';
<?php }else{?>
								document.location.reload();
<?php }?>
						}else{
							loadingStop("body",true);
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}
					});
<?php }?>
			}
		});
	   } else if (response.status == 'not_authorized' || response.status == 'unknown') {
			openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
			// 로그아웃된 경우
			isLogin = false;
<?php if(defined('__ISUSER__')){?>
			loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
				$.ajax({
				'url' : '../sns_process/facebooklogout',
				'dataType': 'json',
				'success': function(res) {
					if(res.result == true){
<?php if($_GET["return_url"]){?>
							document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
							document.location.href='../main/';
<?php }else{?>
							document.location.reload();
<?php }?>
					}else{
						document.location.reload();
					}
				}
				});

<?php }?>
			if (fbId != "")  initializeFbTokenValues();
			if (fbUid != "") initializeFbUserValues();
		}
	}

	function handelStatusChangebiz(response) {
		if (response && response.status == 'connected') {
		// 로그인
		isLogin = true;
		initializeFbTokenValues();
		initializeFbUserValues();
		fbId = response.authResponse.userID;
		fbAccessToken = response.authResponse.accessToken;
		FB.api('/me', function(response) {
			 fbUid = response.email;
			 fbName = response.name;
			 if (fbName != "") {
<?php if(!defined('__ISUSER__')){?>
				loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
					$.ajax({
					'url' : '../sns_process/facebookloginck',
					'data' : {'mtype':'biz'},
					'type' : 'post',
					'dataType': 'json',
					'success': function(res) {
						if(res.result == 'emailck'){//이메일이 등된 경우
							openDialog("회원 통합하기  <span class='desc'>로그인해 주세요.</span>", "member_facebook_connect", {"width":"470","height":"250"});
						}else if(res.result == true){
<?php if($_GET["return_url"]){?>
								document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
								document.location.href='/mypage/';
<?php }else{?>
								document.location.reload();
<?php }?>
						}else{
							loadingStop("body",true);
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}
					});
<?php }?>
			}
		});
	   } else if (response.status == 'not_authorized' || response.status == 'unknown') {
			openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
			// 로그아웃된 경우
			isLogin = false;
<?php if(defined('__ISUSER__')){?>
			loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
				$.ajax({
				'url' : '../sns_process/facebooklogout',
				'dataType': 'json',
				'success': function(res) {
					if(res.result == true){
<?php if($_GET["return_url"]){?>
							document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
							document.location.href='../main/';
<?php }else{?>
							document.location.reload();
<?php }?>
					}else{
						document.location.reload();
					}
				}
				});

<?php }?>
			if (fbId != "")  initializeFbTokenValues();
			if (fbUid != "") initializeFbUserValues();
		}
	}

	//회원통합을 위한 로그인
	function handelStatusChangeconnect(response) {
		if (response && response.status == 'connected') {
		// 로그인
		isLogin = true;
		initializeFbTokenValues();
		initializeFbUserValues();
		fbId = response.authResponse.userID;
		fbAccessToken = response.authResponse.accessToken;
		FB.api('/me', function(response) {
			 fbUid = response.email;
			 fbName = response.name;
			 if (fbName != "") {
<?php if(!defined('__ISUSER__')){?>
				loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
					var userid = $("#facebook_userid").val();
					var password = $("#facebook_password").val();
					$.ajax({
					'url' : '../sns_process/facebookloginck',
					'data' : {'facebooktype':'mbconnect','userid':userid, 'password':password},
					'type' : 'post',
					'dataType': 'json',
					'success': function(res) {
						if(res.result == true){
<?php if($_GET["return_url"]){?>
								document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
								document.location.href='/mypage/';
<?php }else{?>
								document.location.reload();
<?php }?>
						}else{
							loadingStop("body",true);
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}
					});
<?php }?>
			}
		});
	   } else if (response.status == 'not_authorized' || response.status == 'unknown') {
			openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
			// 로그아웃된 경우
			isLogin = false;
<?php if(defined('__ISUSER__')){?>
			loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
				$.ajax({
				'url' : '../sns_process/facebooklogout',
				'dataType': 'json',
				'success': function(res) {
					if(res.result == true){
<?php if($_GET["return_url"]){?>
							document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
							document.location.href='../main/';
<?php }else{?>
							document.location.reload();
<?php }?>
					}else{
						document.location.reload();
					}
				}
				});

<?php }?>
			if (fbId != "")  initializeFbTokenValues();
			if (fbUid != "") initializeFbUserValues();
		}
	}

	//회원통합하지 않고 가입하기
	function handelStatusChangenoconnect(response) {
		if (response && response.status == 'connected') {
		// 로그인
		isLogin = true;
		initializeFbTokenValues();
		initializeFbUserValues();
		fbId = response.authResponse.userID;
		fbAccessToken = response.authResponse.accessToken;
		FB.api('/me', function(response) {
			 fbUid = response.email;
			 fbName = response.name;
			 if (fbName != "") {
<?php if(!defined('__ISUSER__')){?>
				loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
					$.ajax({
					'url' : '../sns_process/facebookloginck',
					'data' : {'facebooktype':'noconnect'},
					'type' : 'post',
					'dataType': 'json',
					'success': function(res) {
						if(res.result == true){
<?php if($_GET["return_url"]){?>
								document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
								document.location.href='/mypage/';
<?php }else{?>
								document.location.reload();
<?php }?>
						}else{
							loadingStop("body",true);
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}
					});
<?php }?>
			}
		});
	   } else if (response.status == 'not_authorized' || response.status == 'unknown') {
			openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
			// 로그아웃된 경우
			isLogin = false;
<?php if(defined('__ISUSER__')){?>
			loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
				$.ajax({
				'url' : '../sns_process/facebooklogout',
				'dataType': 'json',
				'success': function(res) {
					if(res.result == true){
<?php if($_GET["return_url"]){?>
							document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
							document.location.href='../main/';
<?php }else{?>
							document.location.reload();
<?php }?>
					}else{
						document.location.reload();
					}
				}
				});
<?php }?>
			if (fbId != "")  initializeFbTokenValues();
			if (fbUid != "") initializeFbUserValues();
		}
	}

	function initializeFbTokenValues() {
		fbId = "";
		fbAccessToken = "";
	}
	function initializeFbUserValues() {
		fbUid = "";
		fbName = "";
	}
	function logout(){
		// 로그아웃된 경우
		FB.logout();
		isLogin = false;
<?php if(defined('__ISUSER__')){?>
		loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
			$.ajax({
			'url' : '../sns_process/facebooklogout',
			'dataType': 'json',
			'success': function(res) {
				if(res.result == true){
<?php if($_GET["return_url"]){?>
						document.location.href='<?php echo $_GET["return_url"]?>';
<?php }elseif($TPL_VAR["login"]||$TPL_VAR["register"]){?>
						document.location.href='../main/';
<?php }else{?>
						document.location.reload();
<?php }?>
				}else{
					document.location.reload();
				}
			}
			});
<?php }?>
		if (fbId != "")  initializeFbTokenValues();
		if (fbUid != "") initializeFbUserValues();
	}
 </script>
<?php }?>

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
<!--facebook area-->
<div id="fb-root"></div>
<!--facebook area end-->
<?php }?>