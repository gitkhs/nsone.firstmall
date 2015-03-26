<?php /* Template_ 2.2.6 2014/11/03 11:38:03 /www/nsone_firstmall_kr/admin/skin/default/setting/joinform.html 000053109 */ 
$TPL_user_sub_1=empty($TPL_VAR["user_sub"])||!is_array($TPL_VAR["user_sub"])?0:count($TPL_VAR["user_sub"]);
$TPL_order_sub_1=empty($TPL_VAR["order_sub"])||!is_array($TPL_VAR["order_sub"])?0:count($TPL_VAR["order_sub"]);
$TPL_snsinfo_1=empty($TPL_VAR["snsinfo"])||!is_array($TPL_VAR["snsinfo"])?0:count($TPL_VAR["snsinfo"]);?>
<script type="text/javascript" src="/app/javascript/js/admin-addlayer.js"></script>
<!-- 회원설정 : 가입 -->
<style>
.tmp {border:2px #ab0804 solid;background-color:#000000;opacity:20%}

/*레이어팝업*/
.layer_pop {border:3px solid #618298; background:#fff;}
.layer_pop .tit {height:45px; font:14px Dotum; letter-spacing:-1px; font-weight:bold; color:#003775; background:#ebf4f2; border-bottom:1px solid #d8dee3; padding:0 10px; border-right:0;}
.layer_pop .search_input {border:1px solid #cecece; height:17px;}
.layer_pop .left {text-align:left;}

.layer_pop_input th {font:11px Dotum; font-weight:bold; letter-spacing:-1px; border:0; background:#fff;}
.layer_pop_input td {font:11px Dotum; border:0;}
.layer_pop_input input {height:18px; line-height:15px; padding:0 3px;}
</style>


<script type="text/javascript">
var user_arr = new Array('userid', 'password', 'user_name', 'email', 'phone', 'cellphone', 'address', 'recommend', 'birthday', 'anniversary', 'nickname', 'sex');
var buss_arr = new Array('bname', 'bceo', 'bno', 'bitem', 'badress', 'bperson', 'bpart','bemail', 'bphone', 'bcellphone');

function typeCheck(){
	/**/
	if($("input:radio[name='join_type']:checked").val()=='member_only'){
		$("table.joinform-order-table input[type='checkbox']").attr("disabled","disabled");
	}else{
		for(var i=0;i<buss_arr.length;i++){
			var tmp_nm = buss_arr[i]+"_use";
			$("input:checkbox[name='"+tmp_nm+"']").attr("disabled",false);
		}

		$(".order_chUse").attr("disabled",false);

	}

	/**/
	for(var i=0;i<user_arr.length;i++){
		var tmp_nm = user_arr[i]+"_use";
		var obj = $("input:checkbox[name='"+tmp_nm+"']");
		if(!$("input:checkbox[name='"+tmp_nm+"']").attr("checked")){
			var tmp_nm2 = user_arr[i]+"_required";
			$("input:checkbox[name='"+tmp_nm2+"']").attr("disabled","disabled");
			obj.parent().parent().css("background-color","#ffffff");
			///추천인 동시 적용
			if(tmp_nm == 'recommend_use'){$("input:checkbox[name='recommend_buse']").parent().parent().css("background-color","#ffffff");}
		}else{
			obj.parent().parent().css("background-color","#e7f2fc");
			///추천인 동시적용
			if(tmp_nm == 'recommend_use'){$("input:checkbox[name='recommend_buse']").parent().parent().css("background-color","#e7f2fc");}
		}
	}

	$(".user_chUse").each( function(){
		var user_ch = $(this).attr("user_ch");
		var tmps_nm = "labelItem[user]["+user_ch+"][use]";
		var obj = $("input:checkbox[name='"+tmps_nm+"']");
		if(!$("input:checkbox[name='"+tmps_nm+"']").attr("checked")){
			var tmps_nm2 = "labelItem[user]["+user_ch+"][required]";
			$("input:checkbox[name='"+tmps_nm2+"']").attr("disabled","disabled");
			obj.parent().parent().css("background-color","#ffffff");
		}else{
			obj.parent().parent().css("background-color","#e7f2fc");
		}

	});

	/**/
	for(var i=0;i<buss_arr.length;i++){
		var tmp_nm = buss_arr[i]+"_use";
		var obj = $("input:checkbox[name='"+tmp_nm+"']");
		var tmp_nm2 = buss_arr[i]+"_required";
		if(!$("input:checkbox[name='"+tmp_nm+"']").attr("checked")){
			$("input:checkbox[name='"+tmp_nm2+"']").attr("disabled","disabled");
			obj.parent().parent().css("background-color","#ffffff");
		}else{
			if($("input:radio[name='join_type']:checked").val()!='member_only') $("input:checkbox[name='"+tmp_nm2+"']").attr("disabled",false);
			obj.parent().parent().css("background-color","#e7f2fc");
		}
	}
	$(".order_chUse").each( function(){
		var order_ch = $(this).attr("order_ch");
		var tmps_nm = "labelItem[order]["+order_ch+"][use]";
		var obj = $("input:checkbox[name='"+tmps_nm+"']");
		if(!$("input:checkbox[name='"+tmps_nm+"']").attr("checked")){
			var tmps_nm2 = "labelItem[order]["+order_ch+"][required]";
			$("input:checkbox[name='"+tmps_nm2+"']").attr("disabled","disabled");
			obj.parent().parent().css("background-color","#ffffff");
		}else{
			obj.parent().parent().css("background-color","#e7f2fc");
		}
	});
}

function check_operating(){
	var obj = $("input[name='join_type']:checked").parent();
	obj.parent().parent().parent().children().each(function(){
		$(this).css("border","1px solid #dadada");
		$(this).css("background-color","#ffffff");
	});
	obj.parent().parent().css("border","2px solid #ab0804");
	obj.parent().parent().css("background-color","#fde1e4");

	if( $("input[name='join_type']:checked").val() == 'member_only' && $("#join_sns_mbonlyf").attr("checked") != 'checked' ) {
		$(".join_type_member_only_chose").css("text-decoration","line-through");
		$("#join_sns_mbbizf").attr("checked",false);
		$("#join_sns_bizonlyf").attr("checked",false);
	}else{
		$(".join_type_member_only_chose").css("text-decoration","");
	}

	if( $("input[name='join_type']:checked").val() == 'member_business' &&  $("#join_sns_mbbizf").attr("checked") != 'checked' ) {
		$(".join_type_member_business_chose2").css("text-decoration","line-through");
		$("#join_sns_mbonlyf").attr("checked",false);
		$("#join_sns_bizonlyf").attr("checked",false);
	}else{
		$(".join_type_member_business_chose2").css("text-decoration","");
	}

	if( $("input[name='join_type']:checked").val() == 'business_only' &&  $("#join_sns_bizonlyf").attr("checked") != 'checked' ) {
		$(".join_type_business_only_chose").css("text-decoration","line-through");
		$("#join_sns_mbbizf").attr("checked",false);
		$("#join_sns_mbonlyf").attr("checked",false);
	}else{
		$(".join_type_business_only_chose").css("text-decoration","");
	}

	//obj.parent().parent().css("opacity","0.2");
}


function use_sns(){
	if( $("#use_f").attr("checked") == 'checked' ||  $("#use_t").attr("checked") == 'checked' ||  $("#use_m").val() == 1 || $("#use_y").val() == 1 ||  $("#use_c").attr("checked") == 'checked' ||  $("#use_g").attr("checked") == 'checked' || $("#use_p").attr("checked") == 'checked' || $("#use_n").attr("checked") == 'checked' ) {
		$(".join_type_sns_chose").css("text-decoration","");
	}else{
		$(".join_type_sns_chose").css("text-decoration","line-through");
	}
}
$(document).ready(function() {
	/**/
	$("input[name='join_type'][value='<?php echo $TPL_VAR["join_type"]?>']").attr('checked','checked');

	/**/
	$("input[name='userid_use'][value='<?php echo $TPL_VAR["userid_use"]?>']").attr('checked','checked');
	$("input[name='userid_required'][value='<?php echo $TPL_VAR["userid_required"]?>']").attr('checked','checked');
	$("input[name='password_use'][value='<?php echo $TPL_VAR["password_use"]?>']").attr('checked','checked');
	$("input[name='password_required'][value='<?php echo $TPL_VAR["password_required"]?>']").attr('checked','checked');
	$("input[name='user_name_use'][value='<?php echo $TPL_VAR["user_name_use"]?>']").attr('checked','checked');
	$("input[name='user_name_required'][value='<?php echo $TPL_VAR["user_name_required"]?>']").attr('checked','checked');
	$("input[name='email_use'][value='<?php echo $TPL_VAR["email_use"]?>']").attr('checked','checked');
	$("input[name='email_required'][value='<?php echo $TPL_VAR["email_required"]?>']").attr('checked','checked');
	$("input[name='phone_use'][value='<?php echo $TPL_VAR["phone_use"]?>']").attr('checked','checked');
	$("input[name='phone_required'][value='<?php echo $TPL_VAR["phone_required"]?>']").attr('checked','checked');
	$("input[name='cellphone_use'][value='<?php echo $TPL_VAR["cellphone_use"]?>']").attr('checked','checked');
	$("input[name='cellphone_required'][value='<?php echo $TPL_VAR["cellphone_required"]?>']").attr('checked','checked');
	$("input[name='address_use'][value='<?php echo $TPL_VAR["address_use"]?>']").attr('checked','checked');
	$("input[name='address_required'][value='<?php echo $TPL_VAR["address_required"]?>']").attr('checked','checked');
	$("input[name='recommend_use'][value='<?php echo $TPL_VAR["recommend_use"]?>']").attr('checked','checked');
	$("input[name='recommend_required'][value='<?php echo $TPL_VAR["recommend_required"]?>']").attr('checked','checked');
	$("input[name='birthday_use'][value='<?php echo $TPL_VAR["birthday_use"]?>']").attr('checked','checked');
	$("input[name='birthday_required'][value='<?php echo $TPL_VAR["birthday_required"]?>']").attr('checked','checked');
	$("input[name='anniversary_use'][value='<?php echo $TPL_VAR["anniversary_use"]?>']").attr('checked','checked');
	$("input[name='anniversary_required'][value='<?php echo $TPL_VAR["anniversary_required"]?>']").attr('checked','checked');
	$("input[name='nickname_use'][value='<?php echo $TPL_VAR["nickname_use"]?>']").attr('checked','checked');
	$("input[name='nickname_required'][value='<?php echo $TPL_VAR["nickname_required"]?>']").attr('checked','checked');
	$("input[name='sex_use'][value='<?php echo $TPL_VAR["sex_use"]?>']").attr('checked','checked');
	$("input[name='sex_required'][value='<?php echo $TPL_VAR["sex_required"]?>']").attr('checked','checked');
	/**/
	$("input[name='bname_use'][value='<?php echo $TPL_VAR["bname_use"]?>']").attr('checked','checked');
	$("input[name='bname_required'][value='<?php echo $TPL_VAR["bname_required"]?>']").attr('checked','checked');
	$("input[name='bceo_use'][value='<?php echo $TPL_VAR["bceo_use"]?>']").attr('checked','checked');
	$("input[name='bceo_required'][value='<?php echo $TPL_VAR["bceo_required"]?>']").attr('checked','checked');
	$("input[name='bno_use'][value='<?php echo $TPL_VAR["bno_use"]?>']").attr('checked','checked');
	$("input[name='bno_required'][value='<?php echo $TPL_VAR["bno_required"]?>']").attr('checked','checked');
	$("input[name='bitem_use'][value='<?php echo $TPL_VAR["bitem_use"]?>']").attr('checked','checked');
	$("input[name='bitem_required'][value='<?php echo $TPL_VAR["bitem_required"]?>']").attr('checked','checked');
	$("input[name='badress_use'][value='<?php echo $TPL_VAR["badress_use"]?>']").attr('checked','checked');
	$("input[name='badress_required'][value='<?php echo $TPL_VAR["badress_required"]?>']").attr('checked','checked');
	$("input[name='bperson_use'][value='<?php echo $TPL_VAR["bperson_use"]?>']").attr('checked','checked');
	$("input[name='bperson_required'][value='<?php echo $TPL_VAR["bperson_required"]?>']").attr('checked','checked');
	$("input[name='bpart_use'][value='<?php echo $TPL_VAR["bpart_use"]?>']").attr('checked','checked');
	$("input[name='bpart_required'][value='<?php echo $TPL_VAR["bpart_required"]?>']").attr('checked','checked');
	$("input[name='bemail_use'][value='<?php echo $TPL_VAR["bemail_use"]?>']").attr('checked','checked');
	$("input[name='bemail_required'][value='<?php echo $TPL_VAR["bemail_required"]?>']").attr('checked','checked');
	$("input[name='bphone_use'][value='<?php echo $TPL_VAR["bphone_use"]?>']").attr('checked','checked');
	$("input[name='bphone_required'][value='<?php echo $TPL_VAR["bphone_required"]?>']").attr('checked','checked');
	$("input[name='bcellphone_use'][value='<?php echo $TPL_VAR["bcellphone_use"]?>']").attr('checked','checked');
	$("input[name='bcellphone_required'][value='<?php echo $TPL_VAR["bcellphone_required"]?>']").attr('checked','checked');
	$("input[name='recommend_buse'][value='<?php echo $TPL_VAR["recommend_use"]?>']").attr('checked','checked');
	$("input[name='recommend_brequired'][value='<?php echo $TPL_VAR["recommend_required"]?>']").attr('checked','checked');


<?php if($TPL_VAR["service_limit"]){?>
	$("input[name='join_type'][value='member_business']").attr("disabled",true);
	$("input[name='join_type'][value='business_only']").attr("disabled",true);
<?php }?>


	$("input:checkbox").live('click',function(){
		var tmp = $(this).attr('name');
		if(tmp){
			tmp = tmp.split("_");

			//추천일 체크일경우 개인/기업 둘다 적용
			if(tmp[0]=='recommend'){
				if(tmp[1] =='use'){
					if($(this).attr('checked')){$("input[name='recommend_buse']").attr('checked','checked');}
					else{$("input[name='recommend_buse']").attr('checked',false);}
				}else{
					if($(this).attr('checked')){$("input[name='recommend_brequired']").attr('checked','checked');}
					else{$("input[name='recommend_brequired']").attr('checked',false);}
				}
			}

			if(tmp[tmp.length-1]!='required'){
				if(tmp[0]=='user') tmp[0] = 'user_name';
				var tmp_nm = tmp[0]+"_required";
				if($(this).attr('checked')){
					$("input:checkbox[name='"+tmp_nm+"']").attr("disabled",false);
				}else{
					$("input:checkbox[name='"+tmp_nm+"']").attr("disabled","disabled");
				}
			}
			var tmps = $(this).attr('name').split("[");

			if(tmps[3]=='use]'){
				var tmps_nm = "labelItem["+tmps[1]+"["+tmps[2]+"[required]";
				if($(this).attr('checked')){
					$("input:checkbox[name='"+tmps_nm+"']").attr("disabled",false);
				}else{
					$("input:checkbox[name='"+tmps_nm+"']").attr("disabled","disabled");
				}
			}
			typeCheck();
		}
	});
	typeCheck();

	$("input:radio[name='join_type']").click(function(){
		typeCheck();
		//check_operating();
	});

	$(".join_sns").click(function() {
<?php if($TPL_VAR["sns"]["total_f"]> 0){?>
			if( !$("#use_f").is(':checked') && $(this).attr('name') == 'use_f' ) {
				if(!confirm("페이스북 SNS계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_f"])?>명 있습니다.\n페이스북 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_f"])?>명의 회원이 \n페이스북 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
					$("#use_f").attr("checked",true);
				}
			}
<?php }?>

<?php if($TPL_VAR["sns"]["total_t"]> 0){?>
			if( !$("#use_t").is(':checked')  && $(this).attr('name') == 'use_t' ) {
				if(!confirm("트위터 SNS계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_t"])?>명 있습니다.\n트위터 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_t"])?>명의 회원이 \n트위터 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
					$("#use_t").attr("checked",true);
				}
			}
<?php }?>

		use_sns();
	});

	$(".me2dayconflay").click(function() {
		openDialog("<img src=\"/admin/skin/default/images/sns/sns_m0.gif\" alt=\"me2day\"  align=\"absmiddle\" /> 미투데이 설정하기", "snsdiv_m", {"width":"700","height":"480","show" : "fade","hide" : "fade"});
	});

	/*
	$(".yozmconflay").click(function() {
		openDialog("<img src=\"/admin/skin/default/images/sns/sns_y0.gif\"  align=\"absmiddle\" /> 요즘 설정하기", "snsdiv_y", {"width":"700","height":"550","show" : "fade","hide" : "fade"});
	});
	*/

	$(".cyworldconflay").click(function() {
		openDialog("<img src=\"/admin/skin/default/images/sns/sns_c0.gif\"  alt=\"cyworld\"   align=\"absmiddle\" /> 싸이월드 설정하기", "snsdiv_c", {"width":"700","height":"280","show" : "fade","hide" : "fade"});
	});

	$(".naverconflay").click(function() {
		openDialog("<img src=\"/admin/skin/default/images/sns/sns_n0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 네이버 설정하기", "snsdiv_n", {"width":"700","height":"280","show" : "fade","hide" : "fade"});
	});

	$(".kakaoconflay").click(function() {
		openDialog("<img src=\"/admin/skin/default/images/sns/sns_k0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 카카오 설정하기", "snsdiv_k", {"width":"700","height":"250","show" : "fade","hide" : "fade"});
	});

	$(".daumconflay").click(function() {
		openDialog("<img src=\"/admin/skin/default/images/sns/sns_d0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 다음(Daum) 설정하기", "snsdiv_d", {"width":"700","height":"280","show" : "fade","hide" : "fade"});
	});

	$("#use_k").click(function(){
		var key_k = $.trim($("#key_k").val());
		if(key_k == '' && $(this).attr("checked") == "checked"){
			alert("설정버튼을 눌러 'Kakao Javascript Key'를 먼저 입력해 주세요.");
			$(this).attr("checked",false);
		}
	});

	//check_operating();
	use_sns();

	$(".labelList_user").sortable();
	$(".labelList_user").disableSelection();
	$(".labelList_order").sortable();
	$(".labelList_user").disableSelection();
<?php if($TPL_VAR["service_setting_date_ck"]){?>
		snsinterface();
<?php }?>

	// 상단 매뉴얼 링크 변경 leewh 2014-10-01
	$(".page-manual-btn a").attr('href','http://manual.firstmall.kr/html/manual.php?category=010012');
});

function snsinterface(){
	var pannel = $("div.snspannel");
	$.ajax({
		'url' : '/admin/common/getGabiaPannel',
		'data' : {'code':'sns_right_banner'},
		'global' : false,
		'success' : function(html){
			if(html){
				$(pannel).show().html(html);
				if(!$(this).attr("noAnimation")){
					$(pannel).activity(false);
				}
			}else{
				$(pannel).hide();
			}
		}
	});
}

</script>

<?php if($TPL_VAR["service_limit"]){?>
<div class="center" style="padding-left:20px;width:100%;text-align:center;" >
	<div style="border:2px #dddddd solid;padding:10px;width:95%;">
		<table width="100%">
		<tr>
		<td align="left">
			무료몰+ : 회원 종류는 ‘개인 회원’입니다.<br>
			사업자 회원의 쇼핑몰을 운영하시려면 프리미엄몰+ 또는 독립몰+로 업그레이드 하시길 바랍니다.
		</td>
		<td align="right"><span class="btn large gray"><input type="button" onclick="serviceUpgrade();" value="업그레이드 >"></span></td>
		</tr>
		</table>

	</div>
	<br style="line-height:20px;" />
</div>
<?php }?>

<div class="center" style="padding-left:20px;width:100%;text-align:center;">
	<div style="border:2px #dddddd solid;padding:10px;width:95%;line-height:20px;">
		<table width="100%">
		<tr>
		<td align="left">
			아래의 설정에 따라 귀사의 쇼핑몰을 이용하시는 소비자들께 <strong>쇼핑몰ID</strong>와 <strong>다양한 SNS계정</strong>으로 <strong>회원가입</strong>과 <strong>로그인</strong> 서비스가 가능합니다.<br>
			귀사의 쇼핑몰에 방문한 소비자는 자신이 원하는 방법으로 회원가입하며, <strong>여러 개의 SNS계정을 중복하여 모두 사용</strong>할 수도 있습니다.<br>
			따라서 소비자는 <strong>귀사의 쇼핑몰에서 다른 쇼핑몰보다 더욱 편리하게 쇼핑</strong>을 하실 수 있으십니다.<br>
			당연히 <strong>개인정보보호</strong>를 위하여 소중한 회원의 정보는 <strong>암호화</strong>되어 저장됩니다.
		</td>
		</tr>
		</table>
	</div>
</div>

<div class="item-title">회원 유형</div>

<table width="100%" class="info-table-style">
<col width="150" /><col width="" />
<tr>
	<th class="its-th" style="line-height:22px;">개인, 사업자</th>
	<td class="its-td">
	<label style="padding:0 10px 0 0px;"><input type="radio" name="join_type" value="member_only" checked /> 개인 회원</label>
	<label style="padding:0 10px 0 10px;"><input type="radio" name="join_type" value="business_only"/> 사업자 회원</label>
	<label style="padding:0 0px 0 10px;"><input type="radio" name="join_type" value="member_business"/> 개인+사업자 회원</label>
	</td>
</tr>

<tr>
	<th class="its-th" style="line-height:22px;">회원 가입<br />및<br />로그인 방법</th>
	<td class="its-td" >

	<table width="100%"  border="0" >
	<tr>
		<td style="padding-top:10px;">
		<div style="line-height:18px;">가입 및 로그인 방법을 선택하십시오. </div>
		<style>
		ul.sns_list li{height:115px;text-align:center;float:left;list-style:none;padding:3px;margin:12px 7px 7px 12px;width:202px;}
		.sns_list li .chkuse {width:100%;height:30px;text-align:left;line-height:30px;}
		.sns_list li .useimg {width:100%;height:85px;}
		.sns_list li .useimg img {margin-top:5px;}
		.sns_list li .useimg img:first-child {margin:0px;}
		</style>
		<div align="center">
			<ul class="sns_list">
			<!-- 아이디/패스워드 -->
				<li>
					<div class="chkuse">
						<input type="hidden" name="use_home" id="use_home" value="1" />
						<label><input type="checkbox" checked="checked" readonly="readonly" disabled="disabled"/> 사용</label>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_idpw_join.gif" class="jointypemember" snstype="none" alt="홈페이지" title="홈페이지" />
					</div>
				</li>
			<!-- 페이스북 -->
				<li>
					<div class="chkuse">
						<input type="hidden" name="callbackurl_f" value="<?php if($TPL_VAR["sns"]["callbackurl_f"]){?><?php echo $TPL_VAR["sns"]["callbackurl_f"]?><?php }else{?><?php echo $TPL_VAR["config_system"]["subDomain"]?><?php }?>" size="40"  />
						<input type="hidden" name="key_f" value="<?php if($TPL_VAR["sns"]["key_f"]){?><?php echo $TPL_VAR["sns"]["key_f"]?><?php }else{?><?php echo $TPL_VAR["APP_ID"]?><?php }?>" size="40"  />
						<input type="hidden" name="secret_f" value="<?php if($TPL_VAR["sns"]["secret_f"]){?><?php echo $TPL_VAR["sns"]["secret_f"]?><?php }else{?><?php echo $TPL_VAR["APP_SECRET"]?><?php }?>" size="40"  />
						<input type="hidden" name="name_f" value="<?php if($TPL_VAR["sns"]["name_f"]){?><?php echo $TPL_VAR["sns"]["name_f"]?><?php }else{?><?php echo $TPL_VAR["APP_NAMES"]?><?php }?>" size="40"  />
						<label><input type="checkbox" name="use_f"  id="use_f"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> checked <?php }else{?>   class="join_sns"  <?php if($TPL_VAR["use_f"]){?>checked<?php }?> <?php }?> /> 사용</label>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_facebook_join.gif" class="jointypesns   join_sns" snstype="fb"  alt="페이스북" title="페이스북" />
						<img src="/admin/skin/default/images/design/sns_bt_facebook_login.gif" class="jointypesns   join_sns" snstype="fb"  alt="페이스북" title="페이스북" />
					</div>
				</li>
			<!-- 트위터 -->
				<li>
					<div class="chkuse">
						<input type="hidden" name="key_t" value="<?php if($TPL_VAR["sns"]["key_t"]){?><?php echo $TPL_VAR["sns"]["key_t"]?><?php }else{?><?php echo $TPL_VAR["TW_APP_ID"]?><?php }?>" size="40"  />
						<input type="hidden" name="secret_t" value="<?php if($TPL_VAR["sns"]["secret_t"]){?><?php echo $TPL_VAR["sns"]["secret_t"]?><?php }else{?><?php echo $TPL_VAR["TW_APP_SECRET"]?><?php }?>" size="40"  />
						<label><input type="checkbox" name="use_t" id="use_t"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  checked <?php }else{?> <?php if($TPL_VAR["use_t"]){?>checked<?php }?> class="join_sns"<?php }?> /> 사용</label>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_twitter_join.gif"  class="jointypesns join_sns" snstype="tw" alt="트위터" title="트위터" />
						<img src="/admin/skin/default/images/design/sns_bt_twitter_login.gif"  class="jointypesns join_sns" snstype="tw" alt="트위터" title="트위터" />
					</div>
				</li>
			<!-- 미투데이 -->
			<!-- 싸이월드 -->
				<li>
					<div class="chkuse">
						<span class="btn small black"><button type="button" class="cyworldconflay" >설정</button></span>
<?php if($TPL_VAR["use_c"]){?>사용하여 운영함<?php }else{?>사용하지 않음<?php }?>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_cyworld_join.gif"  class="jointypesns  join_sns" snstype="cy" alt="싸이월드" title="싸이월드"  />
						<img src="/admin/skin/default/images/design/sns_bt_cyworld_login.gif"  class="jointypesns  join_sns" snstype="cy" alt="싸이월드" title="싸이월드"  />
					</div>
				</li>
			<!-- 요즘 : 2014-07-01 서비스 종료 -->
<?php if(date("Ymd")<'20140701'){?> 	<!--2014-07-01 서비스 종료-->
				<li>
					<div class="chkuse">
						<span class="btn small black"><button type="button" class="me2dayconflay" >설정</button></span> <?php if($TPL_VAR["use_m"]){?>사용하여 운영함<?php }else{?>사용하지 않음<?php }?>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_me2day_join.gif"  class="jointypesns   join_sns" snstype="m2"  alt="미투데이" title="미투데이" />
						<img src="/admin/skin/default/images/design/sns_bt_me2day_login.gif"  class="jointypesns   join_sns" snstype="m2"  alt="미투데이" title="미투데이" />
					</div>
				</li>
<?php }?>
			<!-- 네이버 -->
				<li>
					<div class="chkuse">
						<span class="btn small black"><button type="button" class="naverconflay" >설정</button></span> <?php if($TPL_VAR["use_n"]){?>사용하여 운영함<?php }else{?>사용하지 않음<?php }?>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_naver_join.gif"  class="jointypesns  join_sns" snstype="nv"  alt="네이버" title="네이버" />
						<img src="/admin/skin/default/images/design/sns_bt_naver_login.gif"  class="jointypesns  join_sns" snstype="nv"  alt="네이버" title="네이버" />
					</div>
				</li>
			<!-- 카카오 -->
				<li>
					<div class="chkuse">
						<span class="btn small black"><button type="button" class="kakaoconflay" >설정</button></span> <?php if($TPL_VAR["use_k"]){?>사용하여 운영함<?php }else{?>사용하지 않음<?php }?>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_kakao_join.gif" class="jointypesns join_sns" snstype="kk"  alt="카카오" title="카카오" />
						<img src="/admin/skin/default/images/design/sns_bt_kakao_login.gif" class="jointypesns join_sns" snstype="kk"  alt="카카오" title="카카오" />
					</div>
				</li>
			<!--다음 -->
				<li>
					<div class="chkuse">
						<span class="btn small black"><button type="button" class="daumconflay" >설정</button></span> <?php if($TPL_VAR["use_d"]){?>사용하여 운영함<?php }else{?>사용하지 않음<?php }?>
					</div>
					<div class="useimg">
						<img src="/admin/skin/default/images/design/sns_bt_daum_join.gif" class="jointypesns join_sns" snstype="dm"  alt="다음" title="다음" />
						<img src="/admin/skin/default/images/design/sns_bt_daum_login.gif" class="jointypesns join_sns" snstype="dm"  alt="다음" title="다음" />
					</div>
				</li>
			</ul>
		</div>

		<!--- include : joinform_sns_setting.html -->
		<input type="hidden" name="pagemode" id="pagemode" value="joinform">
<?php $this->print_("sns_setting",$TPL_SCP,1);?>


		<!-- <br/>
		<div style="padding:5px;margin-top:15px;"><br/>
				<table width="100%">
				<tr>
				<td align="left"><br/>
				 <h2> 미2데이/싸이월드 사용방법</h2>
				미2데이/싸이월드를 이용하려면 페이스북이나 트위터와 달리 별도의 앱등록이 필요합니다. <br/>

				미2데이개발자 센터에  방문하여  키를 발급 받을 수 있으며, 발급 받은 키를 <span class="btn small black"><button type="button"  >설정</button></span> 클릭해서 넣으면 됩니다.<br/>
				(발급 신청시 넣는 항목에 대해 <a href="http://manual.firstmall.kr/html/manual.php?category=010012/" target="_blank" class="bold blue">매뉴얼</a> 에 설명되어 있으니 확인 후 신청하세요)<br/>
				<div align="center" style="margin-top:5px;margin-bottom:10px;">
					<span class="btn large cyanblue"><button type="button" onclick="window.open('http://me2day.net/me2/app/get_appkey/', 'me2day')" >미2데이 가기</button></span>
				</div>

				네이트 개발자센터에서 개발자등록을 하고 Consumer key 발급받을 수 있으며, 발급 받은 키를 <span class="btn small black"><button type="button"  >설정</button></span> 클릭해서 넣으면 됩니다.<br/>
				<div align="center" style="margin-top:5px;margin-bottom:10px;">
					<span class="btn large cyanblue"><button type="button" onclick="window.open('http://devsquare.nate.com/openApi/registerConsumerKey', 'cyworld')" >싸이월드 가기</button></span>
				</div>


				</td>
				</tr>
				</table>
		</div> -->
	</td>
	<td valign="top" style="width:270px;;text-align:left;padding:5px;"></td>
	<!-- SNS pannel 영역 : 시작 -->
	<!--td valign="top" style="border:1px solid #dadada;width:270px;;text-align:left;padding:5px;"><div style="padding:0px 10px 0px 10px;" /><div class="snspannel  <?php if(!$TPL_VAR["service_setting_date_ck"]){?>hide<?php }?>" style="border:0px solid #dadada;width:270px;;text-align:left;padding:5px;" ></div></div></td-->
	<!-- SNS pannel 영역 : 끝 -->
	</table>

	</td>
</tr>

<tr>
	<th class="its-th" style="line-height:22px;">회원 가입<br />방법 안내</th>
	<td class="its-td  left">
		<table width="100%" >
		<tr>
			<td rowspan="2" width="130" >①  회원가입 방법 선택 </td>
			<td class="join_type_memberid_td"> → [쇼핑몰ID 선택 시] ② 약관동의 → ③ 실명인증 → ④  회원 정보 입력 → ⑤ 회원가입 완료</td>
		</tr>
		<tr>
			<td class="join_type_sns_chose" > → [SNS 또는 외부 계정 선택 시] ② 약관동의 → ③  앱 동의(최초 1회) → ④ 회원가입 완료</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<th class="its-th" style="line-height:25px;">회원 로그인<br />방법 안내</th>
	<td class="its-td  left">
		<span class="join_type_memberid_td"  >[쇼핑몰ID로 로그인 시]	① 아이디와 비밀번호 입력</span><br/>
		<span class="join_type_sns_chose"  >[SNS 또는 외부 계정으로 로그인 시] ① SNS 또는 외부 계정버튼 클릭</span>
	</td>
</tr>
<tr>
	<th class="its-th" style="line-height:22px;">다른 SNS 또는<br />외부 계정도<br>사용하고 싶을 때</th>
	<td class="its-td left">
		<ul style="list-style-type:none;line-height:18px;margin-top:5px;margin-bottom:5px;">
			<li style="line-height:25px;"><strong>위치 : 로그인 > MY페이지 > 회원정보수정</strong></li>
			<li>① 쇼핑몰에서 사용하고 싶은 여러 개의 SNS 또는 외부 계정 추가 선택 가능</li>
			<li>② 쇼핑몰에서 사용 중인 SNS 또는 외부 계정의 사용 해제도 가능</li>
			<li>③ 회원이 어떠한 계정(ID, SNS, 외부)으로 로그인해도 1명의 회원으로 인식</li>
			<li>④ 적립금, 쿠폰, 주문, 상품리뷰, 위시리스트 등의 모든 회원정보 통합 관리</li>
<?php if($TPL_VAR["APP_VER"]!="2.0"){?><li>⑤ 페이스북 로그인 시 페이스북 친구초대가 가능하며, 친구초대 시 혜택 지급 가능 (설정 &gt; 회원 &gt; <a href="member?gb=approval" target=_blank><span class="highlight-link">승인혜택</a>)</li><?php }?>
		</ul>
		<div style="margin-top:24px;">
			<img src="/admin/skin/default/images/design/img_member_snsuse_pc.gif">
		</div>
		<div style="margin-top:24px;">
			<img src="/admin/skin/default/images/design/img_member_snsuse_mobile.gif">
		</div>
	</td>
</tr>

</table>

<div style="padding-top:20px;"></div>

<table width="100%">
<tr>
	<td valign="top" width="50%" style="vertical-align:top;">
	<div class="item-title">쇼핑몰ID(개인)로 회원가입 시 입력항목 <span class="desc" style="font-weight:normal">우측상단의 '가입항목 만들기'로 가입형식을 추가할 수 있습니다.</span></div>

	<table width="100%" class="joinform-user-table info-table-style">
	<col width="150" /><col width="" /><col width="80" /><col width="80" /><col width="10" />
	<tr>
		<th class="its-th">입력항목</th>
		<th class="its-th">항목설명</th>
		<th class="its-th">사용</th>
		<th class="its-th">필수</th>
		<th class="its-th">&nbsp;</th>
	</tr>
	<tr>
		<td class="its-td">아이디</td>
		<td class="its-td small-td-text">6자~20자 / 영문 및 숫자</td>
		<td class="its-td"><input type="checkbox" name="userid_use" value="Y" checked disabled/> 사용</td>
		<td class="its-td"><input type="checkbox" name="userid_required" value="Y" checked disabled/> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td" style="background-color:#e7f2fc;">가입불가아이디</td>
		<td class="its-td" colspan="4" style="background-color:#e7f2fc;"><input type="text" name="disabled_userid" value="<?php echo $TPL_VAR["disabled_userid"]?>" size="60" class="line" /></td>
	</tr>
	<tr>
		<td class="its-td">비밀번호</td>
		<td class="its-td small-td-text">6자~20자 / 영문, 숫자, 특수문자 2개이상 조합 / 아이디와 중복 불가</td>
		<td class="its-td"><input type="checkbox" name="password_use" value="Y" checked disabled/> 사용</td>
		<td class="its-td"><input type="checkbox" name="password_required" value="Y" checked disabled/> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">이름
		<br/><label  ><input type="checkbox" name="user_icon" value="Y" <?php if($TPL_VAR["user_icon"]){?>checked<?php }?>/> 아이콘 사용</label> </td>
		<td class="its-td small-td-text">※ 아이핀/안심체크 사용 시 자동 입력</td>
		<td class="its-td"><input type="checkbox" name="user_name_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="user_name_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">닉네임</td>
		<td class="its-td small-td-text">10자 이내 닉네임 입력</td>
		<td class="its-td"><input type="checkbox" name="nickname_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="nickname_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr> 
	<tr>
		<td class="its-td">이메일 (수신 여부)
		<!-- <br/><label  ><input type="checkbox" name="email_userid" value="Y" <?php if($TPL_VAR["email_userid"]){?>checked<?php }?>/> 아이디로 대체</label> --></td>
		<td class="its-td small-td-text">이메일 입력, 수신동의 체크</td>
		<td class="its-td"><input type="checkbox" name="email_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="email_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">전화번호</td>
		<td class="its-td small-td-text">전화번호 입력</td>
		<td class="its-td"><input type="checkbox" name="phone_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="phone_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">핸드폰 (수신 여부)</td>
		<td class="its-td small-td-text">핸드폰 입력, 수신동의 체크</td>
		<td class="its-td"><input type="checkbox" name="cellphone_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="cellphone_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">주소</td>
		<td class="its-td">우편번호, 주소 입력</td>
		<td class="its-td"><input type="checkbox" name="address_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="address_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">추천인</td>
		<td class="its-td small-td-text">추천인 아이디 입력</td>
		<td class="its-td"><input type="checkbox" name="recommend_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="recommend_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">생일</td>
		<td class="its-td small-td-text">생년월일 입력</td>
		<td class="its-td"><input type="checkbox" name="birthday_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="birthday_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">기념일</td>
		<td class="its-td small-td-text">월일 입력</td>
		<td class="its-td"><input type="checkbox" name="anniversary_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="anniversary_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">성별</td>
		<td class="its-td small-td-text">남/여 구분 체크</td>
		<td class="its-td"><input type="checkbox" name="sex_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="sex_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tbody class="labelList_user">
<?php if($TPL_VAR["user_sub"]){?>
<?php if($TPL_user_sub_1){foreach($TPL_VAR["user_sub"] as $TPL_V1){?>
		<tr class="layer<?php echo $TPL_V1["joinform_seq"]?> ">
			<td class="its-td"><?php echo $TPL_V1["label_title"]?></td>
			<td class="its-td"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"> [<?php echo $TPL_V1["label_ctype"]?>] <?php echo $TPL_V1["label_desc"]?> <span class="btn small gray"><button type="button" class="listJoinBtn" id="listJoinBtn" value="<?php echo $TPL_V1["joinform_seq"]?>" join_type="user" >수정</button></span></td>
			<td class="its-td"><input type="checkbox" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][use]" class="user_chUse" user_ch="<?php echo $TPL_V1["joinform_seq"]?>" value="Y" <?php if($TPL_V1["used"]=='Y'){?> checked <?php }?>/> 사용</td>
			<td class="its-td"><input type="checkbox" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][required]" class="user_chRequired" value="Y" <?php if($TPL_V1["required"]=='Y'){?> checked <?php }?> /> 필수</td>
			<td class="its-td">
			<input type="hidden" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][joinform_seq]" value="<?php echo $TPL_V1["joinform_seq"]?>">
			<input type="hidden" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
			<input type="hidden" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
			<input type="hidden" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][exp]" value="<?php echo $TPL_V1["label_desc"]?>">
			<input type="hidden" name="labelItem[user][<?php echo $TPL_V1["joinform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
			<span class="btn-minus" onclick="deleteRow(this)"><button type="button"></button></span></td>
		</tr>
<?php }}?>
<?php }?>
	</tbody>
	</table>
	</td>
	<td valign="top" width="50%" style="vertical-align:top;">
	<div class="item-title">쇼핑몰ID(사업자)로 회원가입 시 입력항목 <span class="desc" style="font-weight:normal">우측상단의 '가입항목 만들기'로 가입형식을 추가할 수 있습니다.</span></div>

	<table width="100%" class="joinform-order-table info-table-style">
	<col width="150" /><col width="" /><col width="80" /><col width="80" /><col width="10" />
	<tr>
		<th class="its-th">입력항목</th>
		<th class="its-th">항목 설명</th>
		<th class="its-th">사용</th>
		<th class="its-th">필수</th>
		<th class="its-th">&nbsp;</th>
	</tr>
	<tr>
		<td class="its-td">아이디</td>
		<td class="its-td small-td-text">6자~20자 / 영문 및 숫자</td>
		<td class="its-td"><input type="checkbox" name="userid_use" value="Y" checked disabled/> 사용</td>
		<td class="its-td"><input type="checkbox" name="userid_required" value="Y" checked disabled/> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td" style="background-color:#e7f2fc;height:26px;">가입불가아이디</td>
		<td class="its-td" colspan="4" style="background-color:#e7f2fc;">[개인회원] 과 동일함</td>
	</tr>
	<tr>
		<td class="its-td">비밀번호</td>
		<td class="its-td small-td-text">6자~20자 / 영문, 숫자, 특수문자 2개이상 조합 / 아이디와 중복 불가</td>
		<td class="its-td"><input type="checkbox" name="password_use" value="Y" checked disabled/> 사용</td>
		<td class="its-td"><input type="checkbox" name="password_required" value="Y" checked disabled/> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">추천인</td>
		<td class="its-td small-td-text">추천인 아이디 입력</td>
		<td class="its-td"><input type="checkbox" name="recommend_buse" value="Y" disabled/> 사용</td>
		<td class="its-td"><input type="checkbox" name="recommend_brequired" value="Y" disabled/> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">업체명</td>
		<td class="its-td small-td-text">업체명 입력</td>
		<td class="its-td"><input type="checkbox" name="bname_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bname_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">대표자명</td>
		<td class="its-td small-td-text">대표자명 입력</td>
		<td class="its-td"><input type="checkbox" name="bceo_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bceo_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">사업자 등록번호</td>
		<td class="its-td small-td-text">사업자 등록번호 입력</td>
		<td class="its-td"><input type="checkbox" name="bno_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bno_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">업태/종목</td>
		<td class="its-td small-td-text">업태/종목 입력</td>
		<td class="its-td"><input type="checkbox" name="bitem_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bitem_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">사업장 주소</td>
		<td class="its-td small-td-text">우편번호, 주소 입력</td>
		<td class="its-td"><input type="checkbox" name="badress_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="badress_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">담당자명</td>
		<td class="its-td small-td-text">담당자명 입력</td>
		<td class="its-td"><input type="checkbox" name="bperson_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bperson_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">담당자 부서명</td>
		<td class="its-td small-td-text">부서명 입력</td>
		<td class="its-td"><input type="checkbox" name="bpart_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bpart_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>

	<tr>
		<td class="its-td">이메일 (수신 여부)</td>
		<td class="its-td small-td-text">이메일 입력, 수신동의 체크</td>
		<td class="its-td"><input type="checkbox" name="bemail_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bemail_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>

	<tr>
		<td class="its-td">담당자 전화번호</td>
		<td class="its-td small-td-text">전화번호 입력</td>
		<td class="its-td"><input type="checkbox" name="bphone_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bphone_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tr>
		<td class="its-td">핸드폰 (수신 여부)</td>
		<td class="its-td small-td-text">핸드폰 입력, 수신동의 체크</td>
		<td class="its-td"><input type="checkbox" name="bcellphone_use" value="Y" /> 사용</td>
		<td class="its-td"><input type="checkbox" name="bcellphone_required" value="Y" /> 필수</td>
		<td class="its-td">&nbsp;</td>
	</tr>
	<tbody class="labelList_order">
<?php if($TPL_VAR["order_sub"]){?>
<?php if($TPL_order_sub_1){foreach($TPL_VAR["order_sub"] as $TPL_V1){?>
		<tr class="layer<?php echo $TPL_V1["joinform_seq"]?>">
			<td class="its-td"><?php echo $TPL_V1["label_title"]?></td>
			<td class="its-td"><img src="/admin/skin/default/images/common/icon_move.gif"  style="cursor:pointer"> [<?php echo $TPL_V1["label_ctype"]?>] <?php echo $TPL_V1["label_desc"]?> <span class="btn small gray"><button type="button" class="listJoinBtn" id="listJoinBtn" value="<?php echo $TPL_V1["joinform_seq"]?>" join_type="order" style="cursor:pointer;">수정</button></span></td>
			<td class="its-td"><input type="checkbox" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][use]" class="order_chUse" order_ch="<?php echo $TPL_V1["joinform_seq"]?>" value="Y" <?php if($TPL_V1["used"]=='Y'){?> checked <?php }?> /> 사용</td>
			<td class="its-td"><input type="checkbox" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][required]" class="order_chRequired"  value="Y" <?php if($TPL_V1["required"]=='Y'){?> checked <?php }?> /> 필수</td>
			<td class="its-td">
			<input type="hidden" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][joinform_seq]" value="<?php echo $TPL_V1["joinform_seq"]?>">
			<input type="hidden" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
			<input type="hidden" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
			<input type="hidden" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][exp]" value="<?php echo $TPL_V1["label_desc"]?>">
			<input type="hidden" name="labelItem[order][<?php echo $TPL_V1["joinform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
			<span class="btn-minus" onclick="deleteRow(this)"><button type="button"></button></span></td>
		</tr>
<?php }}?>
<?php }?>
	</tbody>
	</table>
	</td>
</tr>
<tr>
	<td>
	
	<style>
	table.snsinfo { position:Relative;width:90%;padding:4px; border-top:1px solid #ccc;}
	table.snsinfo th,table.snsinfo td { border-bottom:1px solid #ccc;border-right:1px solid #ccc;font-size:11px;}
	table.snsinfo th {background-color:#eee; color:#222;}
	table.snsinfo td {text-align:center;color:#666;}
	table.snsinfo td.use {background-color:#e7f2fc;}
	table.snsinfo td:first-child {color:#222;background-color:#eee; }
	table.snsinfo th:first-child,table.snsinfo td:first-child { border-left:1px solid #ccc; }
	</style>

	<div style="margin-bottom:0px;">
	<div class="item-title">SNS계정(개인)으로 회원가입 시 입력항목</div>

	<table width="100%" class="joinform-order-table info-table-style">
	<col width="150" /><col width="" /><col width="80" /><col width="80" /><col width="10" />
	<tr>
		<th class="its-th">입력항목</th>
		<th class="its-th">항목 설명</th>
	</tr>
	<tr>
		<td class="its-td" style="text-align:centerl">없음</td>
		<td class="its-td small-td-text">
		<div style="line-height:22px;">SNS별 자동으로 아래의 정보를 가져옵니다.</div>
		<table class="snsinfo">
		<tr>
			<th>구분</th>
			<th>이메일</th>
			<th>이름</th>
			<th>성별</th>
			<th>생일</th>
			<th>닉네임</th>
		</tr>
<?php if($TPL_snsinfo_1){foreach($TPL_VAR["snsinfo"] as $TPL_K1=>$TPL_V1){?>
		<tr>
			<td><?php echo $TPL_K1?></td>
			<td <?php if($TPL_V1["email"]){?>class="use"<?php }?>><?php if($TPL_V1["email"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["name"]){?>class="use"<?php }?>><?php if($TPL_V1["name"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["sex"]){?>class="use"<?php }?>><?php if($TPL_V1["sex"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["birthday"]){?>class="use"<?php }?>><?php if($TPL_V1["birthday"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["nickname"]){?>class="use"<?php }?>><?php if($TPL_V1["nickname"]){?>○<?php }else{?>&nbsp;<?php }?></td>
		</tr>
<?php }}?>
		</table>

		<div class="desc" style="margin-top:10px;font-weight:normal">
		또한 회원가입 후 회원은 자신의 MY페이지에서<br />
		상기 설정된 <strong>쇼핑몰ID(개인)</strong> 기준의 입력항목 중<br />
		아이디와 비밀번호를 제외한 나머지 입력항목을 추가로 입력할 수 있습니다.
		</div>
		</td>
	</tr>
	</tbody>
	</table>
	</div>
	</td>
<td>
<div style="margin-bottom:0px;">

	<div class="item-title">SNS계정(사업자)으로 회원가입 시 입력항목</div>

	<table width="100%" class="joinform-order-table info-table-style">
	<col width="150" /><col width="" /><col width="80" /><col width="80" /><col width="10" />
	<tr>
		<th class="its-th">입력항목</th>
		<th class="its-th">항목 설명</th>
	</tr>
	<tr>
		<td class="its-td">없음</td>
		<td class="its-td small-td-text">
		<div style="line-height:22px;">SNS별 자동으로 아래의 정보를 가져옵니다.</div>
		<table class="snsinfo">
		<tr>
			<th>구분</th>
			<th>이메일</th>
			<th>이름</th>
			<th>성별</th>
			<th>생일</th>
			<th>닉네임</th>
		</tr>
<?php if($TPL_snsinfo_1){foreach($TPL_VAR["snsinfo"] as $TPL_K1=>$TPL_V1){?>
		<tr>
			<td><?php echo $TPL_K1?></td>
			<td <?php if($TPL_V1["email"]){?>class="use"<?php }?>><?php if($TPL_V1["email"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["name"]){?>class="use"<?php }?>><?php if($TPL_V1["name"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["sex"]){?>class="use"<?php }?>><?php if($TPL_V1["sex"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["birthday"]){?>class="use"<?php }?>><?php if($TPL_V1["birthday"]){?>○<?php }else{?>&nbsp;<?php }?></td>
			<td <?php if($TPL_V1["nickname"]){?>class="use"<?php }?>><?php if($TPL_V1["nickname"]){?>○<?php }else{?>&nbsp;<?php }?></td>
		</tr>
<?php }}?>
		</table>

		<div class="desc" style="margin-top:10px;font-weight:normal">
		또한 회원가입 후 회원은 자신의 MY페이지에서<br />
		상기 설정된 <strong>쇼핑몰ID(사업자)</strong> 기준의 입력항목 중<br />
		아이디와 비밀번호를 제외한 나머지 입력항목을 추가로 입력할 수 있습니다.
		</div>
		</td>
	</tr>
	</tbody>
	</table>
</div>
</td>
</tr>
</table>



<input type="hidden" name="windowLabelComment" value="N">
<input type="hidden" name="windowLabelSeq" value="">
<input type="hidden" name="windowLabelType" value="">

<input type="hidden" name="Label_cnt" value="<?php echo $TPL_VAR["sub_cnt"]["cnt"]?>">
<input type="hidden" name="Label_maxid" value="<?php echo $TPL_VAR["sub_cnt"]["maxid"]?>">
<input type="hidden" name="windowJoinType" value="">
<style>
.btn-add-plus button {display:inline-block;width:22px;height:18px;overflow:visible;position:relative;margin:0;padding:0;border:0;background:url('/admin/skin/default/images/design/icon_design_plus.gif') no-repeat; cursor:pointer;}
.btn-add-minus button {display:inline-block;width:22px;height:18px;overflow:visible;position:relative;margin:0;padding:0;border:0;background:url('/admin/skin/default/images/design/icon_design_minus.gif') no-repeat;cursor:pointer;}
.btn-sub-plus button {display:inline-block;width:22px;height:18px;overflow:visible;position:relative;margin:0;padding:0;border:0;background:url('/admin/skin/default/images/common/icon_plus.gif') no-repeat;cursor:pointer;}
.btn-sub-minus button {display:inline-block;width:22px;height:18px;overflow:visible;position:relative;margin:0;padding:0;border:0;background:url('/admin/skin/default/images/common/icon_minus.gif') no-repeat;cursor:pointer;}
</style>

<div id="joinDiv" class="layer_pop" style="display:none">
	<!--팝업타이틀 -->

	<!--입력폼 -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="45%" valign="top">
			<table width="100%" border="0" cellspacing="3" cellpadding="0" align="center" id="labelTable">
			<tr>
				<th align="left">항목명</th>
				<td><input type="text" name="windowLabelName" value="" size="30" style="height:18px;" class="line"></td>
			</tr>
			<tr>
				<th align="left">항목설명</th>
				<td><input type="text" name="windowLabelExp" value="" size="30" style="height:18px;" class="line"></td>
			</tr>
			<tr id="labelTr">
				<th id="labelTh" align="left">항목값</th>
				<td id="labelTd" ></td>
			</tr>
			</table>

			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td width="75"></td>
                <td><!-- input type="checkbox" name="windowLabelCheck" value="" size="30" class="null"> 항목값 필수--></td>
			</tr>
			</table>
        <!--버튼 -->
        <div style="width:90%; padding:10px 0 0 130px;"><img src="/admin/skin/default/images/common/btn_confirm.gif" alt="확인" id="labelWriteBtn" style="border:0;cursor:pointer;"/></div>
        <!--//버튼 -->
		</td>
		<td width="55%" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2" height="20"></td>
			</tr>
			<tr>
				<td width="30" align="right" valign="top"><img src="/admin/skin/default/images/common/campaign_i_arrow.gif" style="margin-top:40px;" /></td>
				<td align="left">
					<div style=" width:88%; height:250px; border:3px solid #dddddd; padding:15px;">
<?php $this->print_("surveyForm",$TPL_SCP,1);?>

                    </div>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	<!--//입력폼 -->
</div>