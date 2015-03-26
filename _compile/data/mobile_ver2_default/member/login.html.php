<?php /* Template_ 2.2.6 2014/11/03 11:38:03 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/member/login.html 000018026 */  $this->include_("sslAction");?>
<?php echo $TPL_VAR["is_file_kakao_tag"]?>

<style>
.keyboard_guide {position:relative; padding:5px; min-height:10px; text-align:center;}
.keyboard_guide a[href='#keyboard_specialchars'] {position:absolute; left:5px; top:5px; display:none;}
.keyboard_guide a[href='#keyboard_hangul'] {position:absolute; left:5px; top:5px; display:none;}
.keyboard_guide a[href='#keyboard_open'] {position:absolute; right:5px; top:5px;}
.keyboard_guide a[href='#keyboard_close'] {position:absolute; right:5px; top:5px; display:none;}
.keyboard_guide_img {display:none}
.keyboard_guide_img .keyboard_hangul {margin:20px auto 0 auto; width:290px; height:80px; background:url('/data/skin/mobile_ver2_default/images/common/keyboard.gif') no-repeat; background-size:100% auto}
.keyboard_guide_img .keyboard_specialchars {margin:20px auto 0 auto; width:290px; height:60px; background:url('/data/skin/mobile_ver2_default/images/common/keyboard.gif') no-repeat 0 -85px; background-size:100% auto; display:none;}

.login_form_box {}
.login_form_box input[name='userid'] {display:block; width:100%; border:1px solid #ccc; height:50px; line-height:50px; text-indent:15px; font-size:14px; font-family:dotum;}
.login_form_box input[name='password'] {display:block; width:100%; border:1px solid #ccc; border-top:none; height:50px; line-height:50px; text-indent:15px; font-size:14px; font-family:dotum;}
.login_form_box input.login_btn {margin-top:10px;}

.sns_login_ul {width:100%;text-align:center;margin-top:15px;padding:0px;}
.sns_login_ul li{display:inline-block;width:55px;height:63px;padding:0px;margin:0px;border-left:1px solid #ccc;text-align:center;}
.sns_login_ul li:first-child{ border:0px; }
.sns_login_ul li img{width:45px;height:45px;}
.sns_login_ul li span{line-height:22px;}

</style>

<script>
$(function(){
	$("a[href='#keyboard_specialchars'], a[href='#keyboard_hangul']").click(function(){
		if($(this).attr('href')=='#keyboard_specialchars'){
			$("a[href='#keyboard_specialchars']").hide();
			$("a[href='#keyboard_hangul']").show();

			$(".keyboard_hangul").hide();
			$(".keyboard_specialchars").show();
		}else{
			$("a[href='#keyboard_specialchars']").show();
			$("a[href='#keyboard_hangul']").hide();

			$(".keyboard_hangul").show();
			$(".keyboard_specialchars").hide();
		}
	});

	$("a[href='#keyboard_open'], a[href='#keyboard_close']").click(function(){
		if($(this).attr('href')=='#keyboard_open'){
			$("a[href='#keyboard_hangul']").click();
			$("a[href='#keyboard_open']").hide();
			$("a[href='#keyboard_close']").show();
			$(".keyboard_guide_img").show();
		}else{
			$("a[href='#keyboard_specialchars'], a[href='#keyboard_hangul']").hide();
			$("a[href='#keyboard_open']").show();
			$("a[href='#keyboard_close']").hide();
			$(".keyboard_guide_img").hide();
		}
	});
});

$(function(){
	$("form[name='loginForm'] input[name='userid']").focus();
});

function submitLoginForm(frm){
	if($("input[name='save_id']").is(":checked")){
		$.cookie('save_userid',$("input[name='userid']",frm).val(),{'expires':30,'path':'/'});
	}else{
		$.cookie('save_userid','',{'expires':-1,'path':'/'});
	}

	if($("input[name='save_pw']").is(":checked")){
		$.cookie('save_password',$("input[name='password']",frm).val(),{'expires':30,'path':'/'});
	}else{
		$.cookie('save_password','',{'expires':-1,'path':'/'});
	}

	return true;
}//t
</script>

<div class="sub_title_bar">
<?php if($_GET["order_auth"]){?>
	<h2>주문배송조회</h2>
<?php }elseif($TPL_VAR["mode"]){?>
	<h2>주문</h2>
<?php }else{?>
	<h2>로그인</h2>
<?php }?>
	<a href="javascript:history.back();" class="stb_back_btn"><img src="/data/skin/mobile_ver2_default/images/design/btn_back.png" width="22" height="22" /></a>
</div>

<div class="keyboard_guide">
	<a href="#keyboard_specialchars">특수 기호 보기</a>
	<a href="#keyboard_hangul">한글 보기</a>
	<a href="#keyboard_open">PC 키보드 보기 ▼</a>
	<a href="#keyboard_close">PC 키보드 닫기 ▲</a>

	<div class="keyboard_guide_img">
		<div class="keyboard_hangul"></div>
		<div class="keyboard_specialchars"></div>
	</div>
</div>

<div class="pd10">
	<!-- 로그인 폼 -->
	<div class="login_form_box">

		<form name="loginForm" target="actionFrame" method="post" action="<?php echo sslAction('../login_process/login')?>" onsubmit="return submitLoginForm(this)">
		<input type="hidden" name="return_url" value="<?php echo $TPL_VAR["return_url"]?>"/>
		<input type="hidden" name="order_auth" value="<?php echo $_GET["order_auth"]?>"/>

		<fieldset>
			<input type="text" name="userid" id="userid" value="<?php if($TPL_VAR["idsavechecked"]){?><?php echo $TPL_VAR["idsavechecked"]?><?php }?>" placeholder="아이디" tabindex="1" required="required" />
			<input type="password" password="password" name="password" id="password" placeholder="비밀번호" tabindex="2"  password="password" required="required" />
			<input type="submit" value="로그인" class="login_btn btn_important_large" style="width:100%;" tabindex="3" />

<?php if(!$_GET["order_auth"]){?>
<?php if($TPL_VAR["mode"]=='settle'){?>
				<div class="pdt5"><input type="button" value="비회원으로 주문하기" class="btn_important_large" style="width:100%;" onclick="document.location.href='<?php echo $_GET["return_url"]?>';"/></div>
<?php }elseif($TPL_VAR["mode"]){?>
				<div class="pdt5"><input type="button" value="비회원으로 주문하기" class="btn_important_large" style="width:100%;" onclick="document.location.href='../order/cart';"/></div>
<?php }?>
<?php }?>

			<div class="pdt20 clearbox txt_spacing">
				<div class="fleft"><input type="checkbox" name="idsave" id="idsave" value="checked"  <?php if($TPL_VAR["idsavechecked"]){?> checked="checked" <?php }?> /> <label for="idsave"><span >아이디 저장</span></label></div>
				<div class="fright"><a href="../member/find?mode=findid">아이디</a>/<a href="../member/find?mode=findpw">비밀번호 찾기</a></div>
			</div>
		</fieldset>

		<!-- //SNS 가입폼 : SNS이용할 경우시작 -->
<?php if(count($TPL_VAR["joinform"]["use_sns"])> 0){?>
		<ul class="sns_login_ul">
<?php if(is_array($TPL_R1=$TPL_VAR["joinform"]["use_sns"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if($TPL_K1){?><li class="sns-login-button" snstype="<?php echo $TPL_K1?>">
				<div class="img"><img src="/data/skin/mobile_ver2_default/images/design/sns_icon_<?php echo $TPL_K1?>.png" alt="sign in with <?php echo $TPL_K1?>" title="<?php echo $TPL_V1['nm']?>"/></div>
				<span>로그인</span></li><?php }?>
<?php }}?>
		</ul>
<?php }?>
		<!-- //SNS 가입폼 : SNS이용할 경우 끝 //-->
		</form>
	</div>

	<div class="mgt5" style="margin-top:15px;border-top:2px solid #ddd">
		<div class="pdt20 pdb10 fx14" style="color:#616161">아직 회원이 아니세요?</div>
		<button class="btn_normal_large" onclick="document.location.href='../member/agreement'" style="width:100%">회원가입</button>
	</div>

	<div style="height:40px"></div>

<?php if($_GET["order_auth"]){?>
	<!-- 회원 주문조회 메시지 -->
	<span>비회원은 주문번호와 주문시 기입하셨던 이메일로 조회할 수 있습니다.</span>
	<div class="login_form_box box_style" style="padding:5px">
		<form name="order_auth_form" target="actionFrame" method="post" action="<?php echo sslAction('../mypage_process/order_auth')?>">
		<input type="hidden" name="return_url" value="<?php echo $TPL_VAR["return_url"]?>"/>

		<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td><span class="input_round_style" style="width:100%;"><input type="text" name="order_seq" value="" /></span></td>
				</tr>
				<tr><td height="3"></td></tr>
				<tr>
					<td><span class="input_round_style" style="width:100%;"><input type="text" name="order_email" value="" /></span></td>
				</tr>
				</table>
			</td>
			<td width="70" align="right"><input type="submit" value="조회" class="btn_normal_large" style="width:60px" /></td>
		</tr>
		</table>

		</form>
	</div>
<?php }?>
</div>

<script type="text/javascript">
$(document).ready(function() {
	//기본 login
	$(".sns-login-button").click(function(){
		var snstype = $(this).attr('snstype');
		loginwindowopen(snstype);
	});
});

function loginwindowopen(sns) {
	var w;var h;
	switch(sns) {
		case 'twitter':
			w = 810;h = 550;
			break;
		case 'me2day':
			w = 900;h = 500;
			break;
		case 'yozm':
			w = 600;h = 450;
			break;
		case 'cyworld':
			w = 430;h = 560;
			break;
		case 'naver':
			w = 460;h = 517;
			break;
		case 'kakao':
			loginWithKakao();
			return false;
		break;
		case 'daum':
			w = 650;h = 517;
		break;
		default:
			w = 800;h=400;
	}
<?php if($_SERVER["HTTP_HOST"]==$TPL_VAR["APP_DOMAIN"]){?>
		if(sns == 'facebook' ) {//경고문구창사이즈
			loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
			FB.login(handelStatusChange,{scope:'<?php echo $TPL_VAR["fbuserauth"]?>'});
		}else{
			loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
			snsloginck(sns);
		}
<?php }else{?>
		if(sns == 'me2day' || sns == 'cyworld' || sns == 'naver' || sns == 'daum' ) {//경고문구창사이즈
<?php if($TPL_VAR["sns"]=='cyworld'&&$TPL_VAR["TW_APP_ID"]=='ifHWJYpPA2ZGYDrdc5wQ'){?>
				window.open('http://m.<?php echo $TPL_VAR["config_system"]["domain"]?>/member/register_sns_form?popup=1&formtype=login&firstmallcartid=<?php echo $TPL_VAR["firstmallcartid"]?>&return_url=<?php echo $TPL_VAR["return_url"]?>&snstype='+sns+'&snsreferer=<?php echo $_SERVER["HTTP_HOST"]?>','snspopup','width=410px,height=150px,statusbar=no,scrollbars=no,toolbar=no');
<?php }else{?>
				snsloginck(sns);
<?php }?>
		}else{
			window.open('http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?>/member/register_sns_form?popup=1&formtype=login&firstmallcartid=<?php echo $TPL_VAR["firstmallcartid"]?>&return_url=<?php echo $TPL_VAR["return_url"]?>&snstype='+sns+'&snsreferer=<?php echo $_SERVER["HTTP_HOST"]?>','snspopup','width='+w+'px,height='+h+'px,statusbar=no,scrollbars=no,toolbar=no');
		}
<?php }?>
}

//기본 facebook 로그인 //
function handelStatusChange(response) {
	if (response && response.status == 'connected') {
	// 로그인
	isLogin = true;
	initializeFbTokenValues();
	initializeFbUserValues();
	if(response.authResponse){
		fbId = response.authResponse.userID;
		fbAccessToken = response.authResponse.accessToken;
	}
	FB.api('/me', function(response) {
		 fbUid = response.email;
		 fbName = response.name;
		 if (fbName != "") {
				$.ajax({
				'url' : '../sns_process/facebookloginck',
				'data' : {'facebooktype':'login'},
				'type' : 'post',
				'dataType': 'json',
				'success': function(res) {
					if(res.result == true){
<?php if($TPL_VAR["return_url"]){?>
							document.location.href='<?php echo $TPL_VAR["return_url"]?>';
<?php }else{?>
							document.location.href='../main/index';
<?php }?>
					}else{
						loadingStop("body",true);
						openDialogAlert(res.msg,'400','160',function(){});
					}
				}
				});
		}
	});
   } else if (response.status == 'not_authorized' || response.status == 'unknown') {
		openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
		// 로그아웃된 경우
		isLogin = false;
<?php if(defined('__ISUSER__')){?>
			logoutajax('facebook');
<?php }?>
		if (fbId != "")  initializeFbTokenValues();
		if (fbUid != "") initializeFbUserValues();
		loadingStop("body",true);
	}else{
		openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
		// 로그아웃된 경우
		isLogin = false;
<?php if(defined('__ISUSER__')){?>
			logoutajax('facebook');
<?php }?>
		if (fbId != "")  initializeFbTokenValues();
		if (fbUid != "") initializeFbUserValues();
		loadingStop("body",true);
	}
}

//feed 권한추가 -> 로그인시키지
function handelStatusChangepublish_stream(response) {
	if (response && response.status == 'connected') {
		document.location.href='../mypage/myinfo';
   }else{
		loadingStop("body",true);
		openDialogAlert('연결을 취소하셨습니다.','400','160',function(){});
   }
}


//기본 SNS로그인//
function snsloginck(sns) {
	loadingStop("body",true);
	var w;var h;
	switch(sns) {
		case 'twitter':
			w = 810;h = 550;
			break;
		case 'me2day':
			w = 900;h = 750;
			break;
		case 'cyworld':
			w = 430;h = 560;
			break;
		case 'naver':
			w = 460;h = 517;
			break;
		case 'daum':
			w = 650;h = 517;
		break;
	}
	var width_	= w;
	var height_ = h;
	var left_	= screen.width;
	var top_	= screen.height;

	left_		= left_/2 - (width_/2);
	top_		= top_/2 - (height_/2);
	var newWin  = window.open("../sns_process/snsredirecturl?snsloginstart=1","SnsFrmUp","height="+height_+",width="+width_+",status=yes,scrollbars=no,statusbar=no,resizable=no,left="+left_+",top="+top_+"");

	$.ajax({
	'url' : '../sns_process/' + sns + 'loginck',
	'data' : {'mform':'login'},
	'type' : 'post',
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true) {
			newWin.location = res.loginurl;
			newWin.focus();
			//window.open('../sns_process/snsredirecturl?snsurl='+res.loginurl,'snsredirecturl','width='+w+'px,height='+h+'px,statusbar=no,scrollbars=no,toolbar=no');
		}else{
			newWin.close();
			openDialogAlert(res.msg,'400','140',function(){});
		}
	}
	});
}


//미투데이 쇼핑몰로그인
function me2dayjoginlogin() {
	$.ajax({
	'url' : '../sns_process/me2daylogin',
	'type' : 'post',
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true){
<?php if($TPL_VAR["return_url"]){?>
				document.location.href='<?php echo $TPL_VAR["return_url"]?>';
<?php }else{?>
				document.location.href='../main/index';
<?php }?>
		}else{
			loadingStop("body",true);
			openDialogAlert(res.msg,'400','140',function(){});
		}
	}
	});
}


//cyworld 쇼핑몰로그인
function cyworldjoginlogin() {
	$.ajax({
	'url' : '../sns_process/cyworldlogin',
	'type' : 'post',
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true){
<?php if($TPL_VAR["return_url"]){?>
				document.location.href='<?php echo $TPL_VAR["return_url"]?>';
<?php }else{?>
				document.location.href='../main/index';
<?php }?>
		}else{
			loadingStop("body",true);
			openDialogAlert(res.msg,'400','140',function(){});
		}
	}
	});
}

//naver login
function naverjoinlogin() {
	$.ajax({
	'url' : '../sns_process/naverlogin',
	'type' : 'post',
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true){
<?php if($_GET["return_url"]){?>
				document.location.href='<?php echo $_GET["return_url"]?>';
<?php }else{?>
				document.location.href='../main/index';
<?php }?>
		}else{
			loadingStop("body",true);
			openDialogAlert(res.msg,'400','140',function(){});
		}
	}
	});
}


//daum login
function daumaccess(str){

	loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});

	str = encodeURIComponent(str);

	$.ajax({
	'url' : '../sns_process/daumuserinfo',
	'type' : 'post',
	'data' : {'str':str},
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true){
			daumjoinlogin();
		}else{
			alert(res.message);
		}
	}});
}

function daumjoinlogin() {

	$.ajax({
	'url' : '../sns_process/daumlogin',
	'type' : 'post',
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true){
			document.location.href='../main/index';
		}else{
			loadingStop("body",true);
			openDialogAlert(res.msg,'400','140',function(){});
		}
	},'error': function(res){
		debug('error : ');
		debug(res);
	}
	});
}

//kakao login start
<?php if(array_key_exists('kakao',$TPL_VAR["joinform"]["use_sns"])){?>
	function loginWithKakao() {
		
		var IEIndex = navigator.appVersion.indexOf("MSIE");         // MSIE를 찾고 인덱스를 리턴
		var IE8Over = navigator.userAgent.indexOf("Trident");		// MS IE 8이상 버전 체크

		if( IEIndex > 0 && IE8Over < 0 )  {
			alert("카카오 로그인을 지원하지 않는 브라우저 버전입니다.\nInternet Explorer 8 버전 이상 사용해 주세요.");
		}else{

			// 로그인 창을 띄웁니다.
			Kakao.Auth.login({
				success: function(authObj) {
					var aaccess_token	= authObj.access_token;

					if(authObj.access_token){
						Kakao.API.request({
							url: '/v1/user/me',
							success: function(userObj) {
								var kakaoObj		= $.extend(authObj,userObj);
								kakaojoinlogin(kakaoObj);
							}

						});
					}else{
						alert("잘못된 접근입니다.");
						return false;
					}
				},fail: function(res){
					alert('잘못된 접근입니다.');
					//JSON.stringify(res);
				}
			});
		}
	};

	function kakaoAPI(kakaoKey){ Kakao.init(kakaoKey); }

	$.ajax({
		'url' : '../sns_process/kakaokeys',
		'dataType': 'json',
		'success': function(res) {
			if(res.result == true){
				kakaoAPI(res.keys);
			}
		}
	});

	function kakaojoinlogin(kakaoObj) {
		$.ajax({
		'url' : '../sns_process/kakaologin',
		'type' : 'post',
		'dataType': 'json',
		'data': kakaoObj,
		'success': function(res) {
			if(res.result == true){
<?php if($_GET["return_url"]){?>
					document.location.href='<?php echo $_GET["return_url"]?>';
<?php }else{?>
					document.location.href='../main/index';
<?php }?>
			}else{
				loadingStop("body",true);
				openDialogAlert(res.msg,'400','140',function(){});
			}
		}
		});
	}
<?php }?>
//kakao login end

//회원정보 초기화 시키기..
function logoutajax(sns){
	$.ajax({
	'url' : '../sns_process/'+sns+'logout',
	'dataType': 'json',
	'success': function(res) {
		if(res.result == true){
<?php if($TPL_VAR["return_url"]){?>
				document.location.href='<?php echo $TPL_VAR["return_url"]?>';
<?php }else{?>
				document.location.href='../main/index';
<?php }?>
		}else{
			loadingStop("body",true);
			openDialogAlert(res.msg,'400','140',function(){});
		}
	}
	});
}
</script>