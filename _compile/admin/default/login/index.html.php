<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/login/index.html 000011764 */  $this->include_("sslAction");?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<style>
.login_txt {margin:0px; border:1px solid #c5c9ce !important; cursor:default; width:180px; height:12px;}
.login_txt:focus {margin:0px; border:1px solid #434b55 !important; cursor:text; width:180px; height:12px;}

body {overflow:hidden;}
#Footer {position:fixed;width:100%;bottom:0px;background-color:#fff}
</style>

<script type="text/javascript">
var close_timestamp;
$(document).ready(function() {
	
	/* 아이프레임일경우 레이아웃 숨김 */
	if(parent.window.document != document){
		$("#layout-header").hide();
		$("#Footer").hide();
	}
	
	$("#tab1").click(function(){
		$(".sms_send_btn").html("전송");
		$(this).attr("background","/admin/skin/default/images/common/login_tab_bg_on.gif");
		$("#tab2").attr("background","/admin/skin/default/images/common/login_tab_bg.gif");
		$("input[name='manager_yn']").val('Y');
		$(".tab2_y").hide(); 
		$(".tab1_y").show();
		$(".tab1_y input[name='main_id']").focus();
	});

	$("#tab2").click(function(){
		$(".sms_send_btn").html("전송");
		$(this).attr("background","/admin/skin/default/images/common/login_tab_bg_on.gif");
		$("#tab1").attr("background","/admin/skin/default/images/common/login_tab_bg.gif");
		$("input[name='manager_yn']").val('N');
		$(".tab1_y").hide();
		$(".tab2_y").show();		
		$(".tab2_y input[name='admin_id']").focus();
	});

	$("#loginForm").submit(function(){
		if($("input[name='save_id']").attr("checked")){
			if($("input[name='manager_yn']").val()=='Y'){
				setCookie('save_id',$("input[name='main_id']").val(), 30);
			}else{
				setCookie('save_id',$("input[name='admin_id']").val(), 30);
				setCookie('sub_id',$("input[name='sub_id']").val(), 30);
			}
		}else{
			deleteCookie('save_id');
			deleteCookie('sub_id');
		}
		//$("#loginForm").submit();
		return true;
	});

	var s_id = getCookie('save_id');
	var sub_id = getCookie('sub_id');
	if(s_id!=""){
		$("input[name='main_id']").val(s_id).focus();
		$("input[name='admin_id']").val(s_id);
		$("input[name='sub_id']").val(sub_id);
		$("input[name='save_id']").attr("checked",true);
	}else{
		$(".tab1_y input[name='main_id']").focus();
	}
	
<?php if($_GET["autoLogin"]){?>
	$("#loginForm input[name='main_id']").val("<?php echo $_GET["main_id"]?>");
	$("#loginForm input[name='main_pwd']").val("<?php echo $_GET["main_pwd"]?>");
	$("#loginForm").submit();
<?php }?>
});


function setCookie( cookieName, cookieValue, expireDate ){
 	var today = new Date();
 	today.setDate( today.getDate() + parseInt( expireDate ) );
 	document.cookie = cookieName + "=" + escape( cookieValue ) + "; path=/; expires=" + today.toGMTString() + ";"
}

function deleteCookie( cookieName ){
 	var expireDate = new Date();
 	//어제 날짜를 쿠키 소멸 날짜로 설정한다.
 	expireDate.setDate( expireDate.getDate() - 1 );
 	document.cookie = cookieName + "= ; expires=" + expireDate.toGMTString() + "; path=/";
}

function getCookie( cookieName ){
  var search = cookieName + "=";
  var cookie = document.cookie;
 
 	// 현재 쿠키가 존재할 경우
 	if( cookie.length > 0 ){
  		// 해당 쿠키명이 존재하는지 검색한 후 존재하면 위치를 리턴.
  		startIndex = cookie.indexOf( cookieName );
  		// 만약 존재한다면
  		if( startIndex != -1 ){
  	 		// 값을 얻어내기 위해 시작 인덱스 조절
      		startIndex += cookieName.length;
   			// 값을 얻어내기 위해 종료 인덱스 추출
     		endIndex = cookie.indexOf( ";", startIndex );
      		// 만약 종료 인덱스를 못찾게 되면 쿠키 전체길이로 설정
      		if( endIndex == -1) endIndex = cookie.length;
     	 	// 쿠키값을 추출하여 리턴
      		return unescape( cookie.substring( startIndex + 1, endIndex ) );
    	}else{ // 쿠키 내에 해당 쿠키가 존재하지 않을 경우
        	return "";
  		}
 	}else{   // 쿠키 자체가 없을 경우
  		return "";
 	}
}

function search_admin()
{
	window.open('http://firstmall.kr/ec_hosting/customer/search_login.php?origin_site=http://firstmall.kr&login_error=0&target_url=/ec_hosting/customer/domain_select.php' , '', 'width=550, height=400');
}

function openAuthHp(manager_id){
	$("input[name='manager_id']").val(manager_id);
	openDialog("핸드폰 인증", "authHpDiv", {"width":"500","height":"150"}, "");
}


function openAuthHpInput(manager_hp){
	closeDialog('authHpDiv');
	$(".hp_number").html(manager_hp);
	$("input[name='auth_hp_ok']").attr("disabled", false);
	$("input[name='input_auth_hp']").attr("disabled", false);
	openDialog("핸드폰 인증", "authHpInputDiv", {"width":"500","height":"200"}, "");
	time_start();
}

function sms_re_send(){
	$("input[name='mode']").val("modify");
	$("#autoFrm").submit();
}

function auth_hp_clear(timeClear){
	if(timeClear == "Y"){
		clearInterval(timeInterval);
		$("input[name='auth_hp_ok']").attr("disabled", false);
		$("input[name='input_auth_hp']").attr("disabled", false);
		time_start();
	}
	$("input[name='input_auth_hp']").val('');
	$("input[name='input_auth_hp']").focus();
}

function login_submit(){
	var auth_hp = $("input[name='input_auth_hp']").val();
	if(auth_hp == ""){
		alert("인증번호를 입력하세요.");
	}else{
		$("input[name='auth_hp']").val(auth_hp);
		$("#loginForm").submit(); 
	}
}

function time_start(){
	close_timestamp = Math.floor((new Date()).getTime()/1000+180);
	timeInterval = setInterval(function(){
		var now_timestamp = Math.floor((new Date()).getTime()/1000);
		
		var remind_timestamp = close_timestamp - now_timestamp;

		var remind_days = Math.floor(remind_timestamp/86400);
		var remind_hours = Math.floor((remind_timestamp - (86400 * remind_days))/3600);
		var remind_minutes = Math.floor((remind_timestamp - ((86400 * remind_days) + (3600 * remind_hours))) / 60);
		var remind_seconds = remind_timestamp%60;
	
		//remind_minutes = strRight("0"+remind_minutes, 2);
		remind_seconds = strRight("0"+remind_seconds, 2);

		$('.timeSpan').html(remind_minutes+"분 "+remind_seconds+"초");


		if(remind_timestamp == 0){
			clearInterval(timeInterval<?php echo $TPL_VAR["goods"]["goods_seq"]?>);
			$("input[name='auth_hp_ok']").attr("disabled", true);
			$("input[name='input_auth_hp']").attr("disabled", true);
			$('.timeSpan').html("입력시간만료");
		}

	},1000);

}
</script>


<div class="Index" id="Index">
<form name="loginForm" id="loginForm" method="post" action="<?php echo sslAction('/admin/login_process/login')?>" target="actionFrame">
<input type="hidden" name="manager_yn" value="Y"/>
<input type="hidden" name="auth_hp" value=""/>
<?php if($_GET["return_url"]){?><input type="hidden" name="return_url" value="<?php echo $_GET["return_url"]?>"/><?php }?>

<table width="100%" height="500">
<tr>
	<td align="center">

	<div style="padding-bottom:15px;"><img src="/admin/skin/default/images/common/login_title.gif"></div>
	
	<div style="border:3px solid #b8b8b8;width:520px;height:240px;">
		
		<table width="400" cellpadding="0" cellspacing="0">
		<tr><td colspan="2" height="40"></td></tr>
		<tr>
			<td width="200" height="30" background="/admin/skin/default/images/common/login_tab_bg_on.gif" class="center" style="color:#ffffff;font-weight:bold;cursor:pointer;" id="tab1">대표관리자</td>
			<td width="200" background="/admin/skin/default/images/common/login_tab_bg.gif" class="center" style="color:#ffffff;font-weight:bold;cursor:pointer;" id="tab2">부관리자</td>
		</tr>
		</table>
			
		<table class="tab1_y" width="400" cellpadding="0" cellspacing="0">
		<tr><td colspan="2" height="36"></td></tr>
		<tr>
			<td align="left">대표 관리자 아이디</td>
			<td align="left">
				<input type="text" name="main_id" class="login_txt" tabindex="1" title="아이디" value="" />
			</td>
			<td rowspan="3" align="right">
				<input type="image" src="/admin/skin/default/images/common/login_btn.gif" style="cursor:pointer;" class="submit_btn" tabindex="3">
			</td>
		</tr>
		<tr><td colspan="3" height="8"><td></tr>
		<tr>
			<td align="left">대표 관리자 비밀번호</td>
			<td align="left">
				<input type="password" name="main_pwd" class="login_txt" tabindex="2" title="비밀번호" value="" />
			</td>
		</tr>
		</table>
		
		<table class="tab2_y" style="display:none;" width="400" cellpadding="0" cellspacing="0">
		<tr><td colspan="2" height="26"></td></tr>
		<tr>
			<td align="left">대표 관리자 아이디</td>
			<td align="left">
				<input type="text" name="admin_id" class="login_txt" tabindex="1" />
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr><td colspan="3" height="8"><td></tr>
		<tr>
			<td align="left">부 관리자 아이디</td>
			<td align="left">
				<input type="text" name="sub_id" class="login_txt" tabindex="2" />
			</td>
			<td rowspan="3" align="right">
				<input type="image" src="/admin/skin/default/images/common/login_btn.gif" style="cursor:pointer;" class="submit_btn" tabindex="4">
			</td>
		</tr>
		<tr><td colspan="3" height="8"><td></tr>
		<tr>
			<td align="left">부 관리자 비밀번호</td>
			<td align="left">
				<input type="password" name="sub_pwd" class="login_txt" tabindex="3" />
			</td>
		</tr>
		</table>
	
		<table width="400" cellpadding="0" cellspacing="0">
		<tr><td height="12"><td></tr>
		<tr>
			<td align="center">
				<span style="font-size:11px;font-family:Dotum;font-weight:bold;color:#4c4e50"><label><input type="checkbox" name="save_id" value="Y"> 아이디 저장하기</label></span>
				<span style="padding-left:20px;"><img src="/admin/skin/default/images/common/login_search_idpw.gif" align="absmiddle" onclick="search_admin();" class="hand"></span>
			</td>
		</tr>
		<tr><td height="40"><td></tr>
		</table>
		
	</div>

	</td>
</tr>
</table> 

</form>
</div>


<div class="Footer" id="Footer">
<table width="100%" height="100%">
<tr><td height="1" bgcolor="#e0e0e0"></td></tr>
<tr><td height="5"></td></tr>
<tr>
	<td>

	<table width="100%">
	<tr> 
		<td width="158" align="center"><img src="/admin/skin/default/images/common/logo_gabiacns.gif"></td>
		<td align="left" style="font-family:'돋움',Dotum,AppleGothic,sans-serif;font-size:11px;letter-spacing:-1px;">Copyrightⓒ <b>GABIA C&S.</b> All Right Reserved.</td>
		<td align="right" style="font-family:'돋움',Dotum,AppleGothic,sans-serif;font-size:11px;letter-spacing:-1px;padding-right:10px;"><b>퍼스트몰</b>, 오직 운영자만을 생각한 가장 앞선 쇼핑몰입니다.</td>
	</tr>
	</table>

	</td>
</tr>
</table>
</div>
<div id="authHpDiv" class="hide">
	<form name="autoFrm" id="autoFrm" method="post" action="../login_process/auth_sms_send" target="actionFrame">
		<input type="hidden" name="manager_id" value="">
		<input type="hidden" name="mode" value="new">
		<div align="center">
		핸드폰 인증을 진행 해 주십시오.
		<br>
		(1일 1회 1기기 기준으로 인증이 필요함)
		<br><br>
		<span class="btn large gray"><input type="submit" value="인증번호받기"></span>
		</div>
	</form>
</div>


<div id="authHpInputDiv" class="hide">
	<form name="autoFrm" method="post" action="<?php echo sslAction('/admin/login_process/login')?>" target="actionFrame">
	<div align="center">
	<span class="hp_number"></span>으로 인증번호가 전송되었습니다.<br>
	남은 시간 : <span class="red timeSpan">3분 00초</span><br>
	인증번호 <input type="text" name="input_auth_hp" value=""> <span class="btn large gray"><input type="button" name="auth_hp_ok" value="확인" onclick="login_submit();"></span>
	<br><br>
	인증번호를 받지 못하셨나요? <a href="javascript:sms_re_send();">재전송</a>
	</div>
</div>

<script>
</script>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>