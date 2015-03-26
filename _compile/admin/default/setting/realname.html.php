<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/realname.html 000007003 */ ?>
<!-- 회원설정 : 실명확인 -->
<script type="text/javascript">
$(document).ready(function() {

	// 휴대폰본인인증
	$("input[name='useRealnamephone']").click(function(){
		if( $(this).attr("checked") ==  "checked" ){
			$(".realnamephone").attr("disabled",false);
		}else{

<?php if($TPL_VAR["operating"]=='adult'){?>
				if(confirm('고객님께서는 현재 "성인쇼핑몰"을 운영중입니다.\n성인쇼핑몰은 반드시 "아이핀"서비스를 필수로 사용해야 합니다.\n[설정 > 운영방식]에서 일반 또는 회원전용 쇼핑몰로 변경하신 후 \n아이핀서비스 "미사용"으로 변경하시기 바랍니다')){
					location.href = "/admin/setting/operating";
				}
				$(this).attr("checked",true);
<?php }else{?>
				$(".realnamephone").attr("disabled",true);
				$(".realnamephone").val("");
<?php }?>
		}
	});


	// 아이핀본인인증
	$("input[name='useIpin']").click(function(){
		if( $(this).attr("checked") ==  "checked" ){
			$(".ipin").attr("disabled",false);
		}else{
			if( $("input[name='useRealname']").attr("checked") ==  "checked" ){
				alert("본인확인을 사용하기 위해서는 아이핀 정보가 반드시 필요합니다.");
				$(this).attr("checked", true);
				return;
			}

<?php if($TPL_VAR["operating"]=='adult'){?>
				if(confirm('고객님께서는 현재 "성인쇼핑몰"을 운영중입니다.\n성인쇼핑몰은 반드시 "아이핀"서비스를 필수로 사용해야 합니다.\n[설정 > 운영방식]에서 일반 또는 회원전용 쇼핑몰로 변경하신 후 \n아이핀서비스 "미사용"으로 변경하시기 바랍니다')){
					location.href = "/admin/setting/operating";
				}
				$(this).attr("checked",true);
<?php }else{?>
				$(".ipin").attr("disabled",true);
				$(".ipin").val("");
<?php }?>
		}
	});

	$("#now_operating").html('<?php echo $TPL_VAR["status"]?>');

	// ###
<?php if($TPL_VAR["useRealname"]=='Y'){?>
		$("input[name='useRealname']").attr("checked", true);
<?php }else{?>
		$(".realname").attr("disabled","disabled");
<?php }?>

<?php if($TPL_VAR["useRealnamephone"]=='Y'){?>
		$("input[name='useRealnamephone']").attr("checked", true);
<?php }else{?>
		$(".realnamephone").attr("disabled","disabled");
<?php }?>

<?php if($TPL_VAR["useIpin"]=='Y'){?>
		$("input[name='useIpin']").attr("checked", true);
<?php }else{?>
		$(".ipin").attr("disabled","disabled");
<?php }?>

	apply_input_style();

	// 상단 매뉴얼 링크 변경 leewh 2014-10-01
	$(".page-manual-btn a").attr('href','http://manual.firstmall.kr/html/manual.php?category=010011');
});
</script>
<div class="item-title">휴대폰본인인증, 아이핀 설정 <span class="helpicon" title="쇼핑몰 회원가입 시 본인확인(실명확인, 아이핀) 절차를 거침으로써 양질의 회원정보를 보유한 쇼핑몰이 됩니다."></span></div>

<table width="100%" class="info-table-style" border="0">
<col width="150" /><col width="150" /><col width="250" /><col width="200" /><col width="200" />
<!-- 실명확인 -->
<tr>
	<th class="its-th left" colspan="2" > 휴대폰본인인증/아이핀이란?</th>
	<td class="its-td" colspan="2">
		<span style="color: #2d86d7;"><b>휴대폰본인인증</b></span>은 이름, 생년월일, 휴대폰 번호를 통해 본인확인을 할 수 있는 서비스입니다.<br>
		<span style="color: #2d86d7;"><b>아이핀</b></span>은 주민번호 대신 본인확인기관이 공급하는 아이핀 ID/패스워드를 사용하여 본인확인을 할 수 있는 서비스입니다.<br>
</tr>

<tr>
	<th class="its-th left"  colspan="2" >
		사용여부
		<br/>
		(실명확인 및 성인인증에 사용됨)
	</th>
	<td class="its-td-align center" >
		<label for="useRealnamephone" ><input type="checkbox" name="useRealnamephone" id="useRealnamephone" value="Y" /> 휴대폰인증</label>
	</td>
	<td class="its-td-align center" >
		<label for="useIpin" ><input type="checkbox" name="useIpin" id="useIpin" value="Y" /> 아이핀 </label>
	</td>
</tr>


<tr>
	<th class="its-th left" rowspan="2" >세팅 정보</th>
	<th class="its-th left" >사이트 코드</th>
	<td class="its-td">
		<input type="text" name="realnamephoneSikey" value="<?php echo $TPL_VAR["realnamephoneSikey"]?>"  oldval="<?php echo $TPL_VAR["realnamephoneSikey"]?>" size="40" class="line  realnamephone" <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> style="width:95%" />
	</td>
	<td class="its-td">
		<input type="text" name="ipinSikey" value="<?php echo $TPL_VAR["ipinSikey"]?>"  oldval="<?php echo $TPL_VAR["ipinSikey"]?>" size="40" class="line ipin " <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> style="width:95%" />
	</td>

</tr>
<tr>
	<th class="its-th left" >사이트 패스워드</th>
	<td class="its-td">
		<input type="text" name="realnamePhoneSipwd" value="<?php echo $TPL_VAR["realnamePhoneSipwd"]?>" oldval="<?php echo $TPL_VAR["realnamePhoneSipwd"]?>" size="40" class="line realnamephone" <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> style="width:95%" />
	</td>
	<td class="its-td">
		<input type="text" name="ipinKeyString" value="<?php echo $TPL_VAR["ipinKeyString"]?>"  oldval="<?php echo $TPL_VAR["ipinKeyString"]?>" size="40" class="line ipin " <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> style="width:95%"  />
	</td>
</tr>
<tr>
	<th class="its-th left" colspan="2" >
	휴대폰본인인증/아이핀 <br>서비스
	<td class="its-td" colspan="2">
		<div style="padding-bottom:15px;">
		<span class="btn large gray" align="absmiddle"><button type="button" onclick="window.open('https://firstmall.kr/ec_hosting/addservice/ipin.php','','')">계약서류 다운로드</button></span>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<img src="/admin/skin/default/images/common/namecheck.jpg" align="absmiddle">
		</div>
		<span>
			건전한 인터넷 세상을 열어갑니다  나이스평가정보 <b>Name Check</b><br />
			쇼핑몰 회원가입 시 본인확인(휴대폰본인인증, 아이핀) 절차를 거침으로써 양질의 회원정보를 보유한 쇼핑몰이 됩니다.<br />
			이용 방법은 아래와 같습니다.<br />
			① 계약서류를 작성하여 NameCheck(나이스평가정보)에 (등기)우편 발송 하세요.<br />
			② NameCheck(나이스평가정보)으로부터 세팅 정보를 받으시게 됩니다.<br />
			③ 위에 입력란에 세팅 정보를 저장하면 쇼핑몰 회원가입 및 아이디패스워드 찾기 시 휴대폰본인인증, 아이핀이 동작하게 됩니다.<br />
			<div>참고: 아이디패스워드 찾기시 인증을 통해 가입한 회원은 해당 인증 수단을 통해 찾을 수 있습니다.</div>
		</span><br>
	</td>
</tr>

</table>