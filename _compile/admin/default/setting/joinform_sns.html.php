<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/joinform_sns.html 000037057 */ ?>
<script type="text/javascript">
$(function(){
	
	var pagemode = $("#pagemode").val();

	$("#me2daybtn").click(function(){
		if( $("#use_m_lay").is(':checked') ) {
			if( !$("#key_m_lay").val() ){
				alert('미투데이의 [API Key] 값을 정확히 입력해 주세요.');
				return false;
			}
				$("#use_m").val(1);
		}else{
			$("#use_m").val('');
		}
<?php if($TPL_VAR["use_m"]== 1&&$TPL_VAR["sns"]["total_m"]> 0){?>
		if( !$("#use_m_lay").is(':checked') ) {
			if(!confirm("미투데이 SNS계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_m"])?>명 있습니다.\n미투데이 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_m"])?>명의 회원이 \n미투데이 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
				$("#use_m_lay").attr("checked",true);
			}
		}
<?php }?>

		$("#key_m").val($("#key_m_lay").val());

		$("#memberForm").submit();
		$("#snsdiv_m").dialog('close').remove();
	});

	$("#yozmbtn").click(function(){
		if( $("#use_y_lay").is(':checked') ) {
			if( !$("#key_y_lay").val() ){
				alert('요즘의 [Consumer Key] 설정값을 정확히 입력해 주세요.');
				return false;
			}

			if( !$("#secret_y_lay").val() ){
				alert('요즘의 [Consumer Secret] 설정값을 정확히 입력해 주세요.');
				return false;
			}
			$("#use_y").val(1);
		}else{
			$("#use_y").val('');
		}


<?php if($TPL_VAR["sns"]["use_y"]== 1&&$TPL_VAR["sns"]["total_y"]> 0){?>
		if( !$("#use_y_lay").is(':checked') ) {
			if(!confirm("요즘 SNS계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_y"])?>명 있습니다.\n요즘 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_y"])?>명의 회원이 \n요즘 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
				$("#use_y_lay").attr("checked",true);
			}
		}
<?php }?>

		$("#key_y").val($("#key_y_lay").val());
		$("#secret_y").val($("#secret_y_lay").val());
		$("#memberForm").submit();
		$("#snsdiv_y").dialog('close').remove();
	});

	$("#cyworldbtn").click(function(){

		if( $("#use_c_lay").is(':checked') ) {
			if( !$("#key_c_lay").val() ){
				alert('싸이월드의 [Consumer Key] 설정값을 정확히 입력해 주세요.');
				return false;
			}

			if( !$("#secret_c_lay").val() ){
				alert('싸이월드의 [Key Secret] 설정값을 정확히 입력해 주세요.');
				return false;
			}
			$("#use_c").val(1);
		}else{
			$("#use_c").val('');
		}

		var submit_use='y';
<?php if($TPL_VAR["sns"]["use_c"]== 1&&$TPL_VAR["sns"]["total_c"]> 0){?>
		if( !$("#use_c_lay").is(':checked') ) {
			if(confirm("싸이월드 SNS계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_c"])?>명 있습니다.\n요즘 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_c"])?>명의 회원이 \n요즘 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
				submit_use = 'y';
				$("#use_c_lay").attr("checked",true);
			}else{
				submit_use = 'n';
			}
		}
<?php }?>

		if(submit_use == 'y'){

			$("#key_c").val($("#key_c_lay").val());
			$("#secret_c").val($("#secret_c_lay").val());

			if(pagemode == "joinform"){
				$("#memberForm").submit();
				$("#snsdiv_c").dialog('close').remove();
				if($("#cyworldguide_cont").css("display") == "block") $("#cyworldguide_cont").dialog('close').remove();
			}else{
				var data = $("#snsjoinRegist").serialize();
				$.ajax({
					'url' : '../member_process/joinform_sns_update',
					'type' : 'post',
					'data': data,
					'dataType': 'json',
					'success': function(res) {
						if(res.result == true){
							snsDisplayCyworld('up');
							$("#snsdiv_c").dialog('close');
							if($("#cyworldguide_cont").css("display") == "block") $("#cyworldguide_cont").dialog('close');
						}
					}
					,'error': function(e){}
				});
			}
		}


	});

	$("#naverbtn").click(function(){

		if( $("#use_n_lay").is(':checked') ) {
			if( !$("#key_n_lay").val() ){
				alert('네이버의 [ClientID] 설정값을 정확히 입력해 주세요.');
				return false;
			}
			if( !$("#secret_n_lay").val() ){
				alert('네이버의 [ClientSecret] 설정값을 정확히 입력해 주세요.');
				return false;
			}
			$("#use_n").val(1);
		}else{
			$("#use_n").val('');
		}

		var submit_use='y';
<?php if($TPL_VAR["sns"]["use_n"]== 1&&$TPL_VAR["sns"]["total_n"]> 0){?>
		if( !$("#use_n_lay").is(':checked') ) {
			if(confirm("네이버 계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_n"])?>명 있습니다.\n네이버 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_n"])?>명의 회원이 \n네이버 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
				submit_use = 'y';
				$("#use_n_lay").attr("checked",true);
			}else{
				submit_use = 'n';
			}
		}
<?php }?>

		if(submit_use == 'y'){

			$("#key_n").val($("#key_n_lay").val());
			$("#secret_n").val($("#secret_n_lay").val());

			if(pagemode == "joinform"){
				$("#memberForm").submit();
				$("#snsdiv_n").dialog('close').remove();
				if($("#naverguide_cont").css("display") == "block") $("#naverguide_cont").dialog('close').remove();
			}else{
				var data = $("#snsjoinRegist").serialize();
				$.ajax({
					'url' : '../member_process/joinform_sns_update',
					'type' : 'post',
					'data': data,
					'dataType': 'json',
					'success': function(res) {
						if(res.result == true){
							snsDisplayNaver('up');
							$("#snsdiv_n").dialog('close');
							if($("#naverguide_cont").css("display") == "block") $("#naverguide_cont").dialog('close');
						}
					}
					,'error': function(e){
					}
				});
			}
		}


	});


	$("#kakaobtn").click(function(){

		if( $("#use_k_lay").is(':checked') == true) {
			if( !$("#key_k_lay").val() ){
				alert('카카오의 [Javascript Key] 값을 정확히 입력해 주세요.');
				return false;
			}
			$("#use_k").val(1);
		}else{
			$("#use_k").val(0);
		}

		var submit_use='y';
<?php if($TPL_VAR["sns"]["use_k"]== 1&&$TPL_VAR["sns"]["total_k"]> 0){?>
		if( !$("#use_k_lay").is(':checked') ) {
			if(confirm("카카오 계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_k"])?>명 있습니다.\n카카오 SNS계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_k"])?>명의 회원이 \n카카오 SNS계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
				submit_use = 'y';
				$("#use_k_lay").attr("checked",true);
			}else{
				submit_use = 'n';
			}
		}
<?php }?>

		if(submit_use == 'y'){

			$("#key_k").val($("#key_k_lay").val());

			if(pagemode == "joinform"){
				$("#memberForm").submit();
				$("#snsdiv_k").dialog('close').remove();
				if($("#kakaoguide_cont").css("display") == "block") $("#kakaoguide_cont").dialog('close').remove();
			}else{
				var data = $("#snsjoinRegist").serialize();
				$.ajax({
					'url' : '../member_process/joinform_sns_update',
					'type' : 'post',
					'data': data,
					'dataType': 'json',
					'success': function(res) {
						if(res.result == true){
							$("#snsdiv_k").dialog('close');
							if($("#kakaoguide_cont").css("display") == "block") $("#kakaoguide_cont").dialog('close');
							snsDisplayKakao('up');
						}
					}
					,'error': function(e){
					}
				});
			}
		}



	});

	$("#daumbtn").click(function(){

		if( $("#use_d_lay").is(':checked') ) {
			if( !$("#key_d_lay").val() ){
				alert('다음의 [ClientID] 설정값을 정확히 입력해 주세요.');
				return false;
			}

			if( !$("#secret_d_lay").val() ){
				alert('다음의 [ClientSecret] 설정값을 정확히 입력해 주세요.');
				return false;
			}
			$("#use_d").val(1);
		}else{
			$("#use_d").val('');
		}


		var submit_use='y';
<?php if($TPL_VAR["sns"]["use_d"]== 1&&$TPL_VAR["sns"]["total_d"]> 0){?>
		if( !$("#use_d_lay").is(':checked') ) {
			if(confirm("다음(Daum) 계정으로 쇼핑몰을 이용중인 회원이 <?php echo number_format($TPL_VAR["sns"]["total_d"])?>명 있습니다.\n다음(Daum) 계정을 사용하지 않을 경우 <?php echo number_format($TPL_VAR["sns"]["total_d"])?>명의 회원이 \n다음(Daum) 계정으로 로그인을 하지 못하게 됩니다.\n위와 같은 문제가 발생하더라도 계속 진행하시겠습니까?")){
				submit_use = 'y';
				$("#use_d_lay").attr("checked",true);
			}else{
				submit_use = 'n';
			}
		}
<?php }?>

		if(submit_use == 'y'){

			$("#key_d").val($("#key_d_lay").val());
			$("#secret_d").val($("#secret_d_lay").val());

			if(pagemode == "joinform"){
				$("#memberForm").submit();
				$("#snsdiv_d").dialog('close').remove();
				if($("#daumguide_cont").css("display") == "block") $("#daumguide_cont").dialog('close').remove();
			}else{
				var data = $("#snsjoinRegist").serialize();
				$.ajax({
					'url' : '../member_process/joinform_sns_update',
					'type' : 'post',
					'data': data,
					'dataType': 'json',
					'success': function(res) {
						if(res.result == true){
							snsDisplayDaum('up');
							$("#snsdiv_d").dialog('close');
							if($("#daumguide_cont").css("display") == "block") $("#daumguide_cont").dialog('close');
						}
					}
					,'error': function(e){}
				});
			}
		}
	});

	$(".btn_sns_guide").click(function(){

		var gubun = $(this).attr("gubun");
		switch(gubun){
			case "naver":
				openDialog("<img src=\"/admin/skin/default/images/sns/sns_n0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 네이버 Client Key 발급 방법 안내", "naverguide_cont", {"width":"730","height":"550","show" : "fade","hide" : "fade","modal":false});
			break;
			case "cyworld":
				openDialog("<img src=\"/admin/skin/default/images/sns/sns_c0.gif\"  alt=\"cyworld\" align=\"absmiddle\" /> 싸이월드 Consumer Key 발급 방법 안내", "cyworldguide_cont", {"width":"730","height":"550","show" : "fade","hide" : "fade","modal":false});
			break;
			case "kakao":
				openDialog("<img src=\"/admin/skin/default/images/sns/sns_k0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 카카오 앱키(App Key) 발급 방법 안내", "kakaoguide_cont", {"width":"730","height":"550","show" : "fade","hide" : "fade","modal":false});
			break;
			case "daum":
				openDialog("<img src=\"/admin/skin/default/images/sns/sns_d0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 다음 Consumer Key 발급 방법 안내", "daumguide_cont", {"width":"1000","height":"550","show" : "fade","hide" : "fade","modal":false});
			break;
		}

	});

});
</script>

<!-- 요즘 key -->
<input type='hidden' name="key_y" id="key_y" value="<?php if($TPL_VAR["sns"]["key_y"]){?><?php echo $TPL_VAR["sns"]["key_y"]?><?php }else{?><?php }?>" >
<input type='hidden' name="secret_y" id="secret_y" value="<?php if($TPL_VAR["sns"]["secret_y"]){?><?php echo $TPL_VAR["sns"]["secret_y"]?><?php }else{?><?php }?>">
<input type="hidden" name="use_y" id="use_y"  value="<?php echo $TPL_VAR["sns"]["use_y"]?>"   <?php if($TPL_VAR["sns"]["use_y"]){?>checked<?php }?> />
<!-- 미투데이 key -->
<input type='hidden' name="key_m" id="key_m" value="<?php if($TPL_VAR["sns"]["key_m"]){?><?php echo $TPL_VAR["sns"]["key_m"]?><?php }else{?><?php }?>">
<input type="hidden" name="use_m" id="use_m"  value="<?php echo $TPL_VAR["sns"]["use_m"]?>"  <?php if($TPL_VAR["use_m"]){?>checked<?php }?>  />
<!-- 싸이월드 key -->
<input type="hidden" name="key_c" id="key_c" value="<?php if($TPL_VAR["sns"]["key_c"]){?><?php echo $TPL_VAR["sns"]["key_c"]?><?php }else{?><?php }?>" />
<input type="hidden" name="secret_c" id="secret_c" value="<?php if($TPL_VAR["sns"]["secret_c"]){?><?php echo $TPL_VAR["sns"]["secret_c"]?><?php }else{?><?php }?>"/>
<input type="hidden" name="use_c" id="use_c"  value="<?php echo $TPL_VAR["sns"]["use_c"]?>"   <?php if($TPL_VAR["sns"]["use_c"]){?>checked<?php }?> />
<!-- 네이버 key -->
<input type='hidden' name="key_n" id="key_n" value="<?php if($TPL_VAR["sns"]["key_n"]){?><?php echo $TPL_VAR["sns"]["key_n"]?><?php }else{?><?php }?>">
<input type='hidden' name="secret_n" id="secret_n" value="<?php if($TPL_VAR["sns"]["secret_n"]){?><?php echo $TPL_VAR["sns"]["secret_n"]?><?php }else{?><?php }?>">
<input type="hidden" name="use_n" id="use_n"  value="<?php echo $TPL_VAR["sns"]["use_n"]?>" />
<!-- 카카오 key -->
<input type='hidden' name="key_k" id="key_k" value="<?php if($TPL_VAR["sns"]["key_k"]){?><?php echo $TPL_VAR["sns"]["key_k"]?><?php }else{?><?php }?>">
<input type="hidden" name="use_k" id="use_k"  value="<?php echo $TPL_VAR["sns"]["use_k"]?>" />
<!-- 다음 key -->
<input type='hidden' name="key_d" id="key_d" value="<?php if($TPL_VAR["sns"]["key_d"]){?><?php echo $TPL_VAR["sns"]["key_d"]?><?php }else{?><?php }?>">
<input type='hidden' name="secret_d" id="secret_d" value="<?php if($TPL_VAR["sns"]["secret_d"]){?><?php echo $TPL_VAR["sns"]["secret_d"]?><?php }else{?><?php }?>">
<input type="hidden" name="use_d" id="use_d"  value="<?php echo $TPL_VAR["sns"]["use_d"]?>" />

<!-- 미투데이 설정 레이어 -->
<div  id="snsdiv_m" class="hide" >
	<span  class="desc" >미투데이로 회원가입과 로그인이 되는 쇼핑몰을 운영하고 싶으시면 ↓ 아래의 정보를 설정해 주십시오.</span>
	<div style="padding-top:5px;" ></div>
	<table width="100%" class="joinform-user-table info-table-style">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th">API Key</th>
				<td class="its-td"><input type='text'  name="key_m_lay"  id="key_m_lay" value="<?php if($TPL_VAR["sns"]["key_m"]){?><?php echo $TPL_VAR["sns"]["key_m"]?><?php }else{?><?php }?>"  size="80"></td>
			</tr>
			<tr >
				<td class="its-td" colspan="2" ><label><input type="checkbox" name="use_m_lay"  id="use_m_lay"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>checked <?php }else{?><?php if($TPL_VAR["use_m"]){?>checked<?php }?> <?php }?>  /> 사용하여 운영함</label></td>
			</tr>
			<tr><td colspan="2" align="center"><div class="center" style="padding:10px;"><span class="btn large black"><button  type="button"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="me2daybtn" <?php }?>  >적용하기</button></span></div></td>
			</tr>
			<tr><td colspan="2" align="center">
				<table width="100%" class="joinform-user-table info-table-style"  align="center">
					<col width="150" /><col width="" />
					<tbody >
						<tr >
							<th class="its-th" >API 키 발급 방법 안내
							<span  style="float:right"><span class="btn small cyanblue" ><button type="button" onclick="window.open('http://manual.firstmall.kr/html/manual.php?category=010012', 'manual')" >매뉴얼 가기</button></span> &nbsp;&nbsp;</span>
							</th>
						</tr>
						<tr >
							<td class="its-td"   align="left">
							<ol style="padding:10px;">
								<li>1. <a href="http://me2day.net/me2/app/get_appkey" target="_blank" ><span class="cyanblue">http://me2day.net/me2/app/get_appkey</span></a> 방문 ( 네이버 로그인 필요)</li>
								<li>2. 필요한 항목값 기입 후 발급 받음<br />
								&nbsp;&nbsp;&nbsp;<b>주요 입력 항목 참조</b><br />
								&nbsp;&nbsp;&nbsp;- 인증방식은 ‘웹기반’ 을 선택<br />
								&nbsp;&nbsp;&nbsp;- 인증방식 선택 후  아래에 나오는 입력칸에 <br />
								&nbsp;&nbsp;&nbsp;CallBack URL:<?php if($TPL_VAR["config_system"]["domain"]){?>
									http://<?php echo $TPL_VAR["config_system"]["domain"]?>/sns_process/sociallogin?sns=me2day
<?php }else{?>
									http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>/sns_process/sociallogin?sns=me2day
<?php }?>
								입력<br />
								&nbsp;&nbsp;&nbsp;<b>( 도메인 변경시 CallBack url 을 변경해야 합니다.)</b>
								</li>
								<li>3. 발급받은 key 값을 위에 입력칸에 넣은 후 ‘사용하여 운영함’ 체크 <br />
								&nbsp;&nbsp;&nbsp;(신청 완료 후 좌측 ‘앱 키 관리’ 에서 확인 가능)</li>
							</ol>
							</td>
						</tr>
					</tbody>
				</table>
			</td></tr>
		</tbody>
	</table>
</div>

<!-- 싸이월드 설정 레이어 -->
<div  id="snsdiv_c" class="hide" >
	<div style="width:100%;height:20px;">
		<div class="desc" style="float:left;">싸이월드로 회원가입과 로그인이 되는 쇼핑몰을 운영하고 싶으시면 ↓ 아래의 정보를 설정해 주십시오.</div>
		<div style="float:right;"><span class="btn small orange" style="line-height:14px;"><button type="button" mode=""  class="btn_sns_guide" gubun="cyworld">안내) 발급 방법 안내 </button></span></div>
	</div>

	<div style="padding-top:5px;">
	<table width="100%" class="joinform-user-table info-table-style">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th">Consumer Key</th>
				<td class="its-td"><input type='text'  name="key_c_lay"  id="key_c_lay" value="<?php if($TPL_VAR["sns"]["key_c"]){?><?php echo $TPL_VAR["sns"]["key_c"]?><?php }else{?>394d5f52e7654e216714d5ea074f242705063b910<?php }?>"  style="width:95%"></td>
			</tr>
			<tr >
				<th class="its-th">Key Secret</th>
				<td class="its-td"><input type='text'  name="secret_c_lay"  id="secret_c_lay" value="<?php if($TPL_VAR["sns"]["secret_c"]){?><?php echo $TPL_VAR["sns"]["secret_c"]?><?php }else{?>35939c0a7c818488a5d4b268399c88db<?php }?>" style="width:95%"></td>
			</tr>
			<tr >
				<td class="its-td" colspan="2" ><label><input type="checkbox" name="use_c_lay"  id="use_c_lay"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> checked <?php }else{?><?php if($TPL_VAR["sns"]["use_c"]){?>checked<?php }?> <?php }?>   /> 사용하여 운영함</label></td>
			</tr>
		</tbody>
	</table>
	</div>
		
	<div class="center" style="padding:10px;"><span class="btn large black"><button  type="button" <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="cyworldbtn" <?php }?> >적용하기</button></span></div>

</div>

<!-- 싸이월드 설정 가이드 -->
<div id="cyworldguide_cont" style="margin-top:5px;" class="hide">

	<table width="100%" class="joinform-user-table info-table-style"  align="center">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th" >Consumer 키 발급 방법 안내</th>
			</tr>
			<tr >
				<td class="its-td"   align="left">
				<ol style="padding:10px;">
					<li>1. <a href="http://devsquare.nate.com/openApi/registerConsumerKey" target="_blank" ><span class="cyanblue"><u>http://devsquare.nate.com/openApi/registerConsumerKey</u></span></a> 방문 (네이트 로그인 필요)</li>
					<li style="margin-top:15px;">2. 정보 입력 후 신청<br />
						<img src="/admin/skin/default/images/common/guide_member_cy1.gif"><br />
						<span style="padding-top:5px;padding-left:25px;">
						<strong>( 도메인 변경시 Consumer 키를 새로 발급받으셔서 변경해 주셔야 합니다.)</strong></span><br />
						<span style="padding-left:25px;">- 신청 후에는 인증하신 이메일 계정으로 전송된 발급 정보를 확인 해주시기 바랍니다.
						</span>
					</li>
					<li style="margin-top:15px;">
					3. 발급받은 Consumer Key와 인증 하신 이메일 계정으로 발송된 Key Secret 값을 위에 입력칸에 넣은 후<br />
						<span style="padding-left:20px;">‘사용하여 운영함’ 체크 (신청 완료 후 좌측 ‘Consumer Key 관리’ 에서 확인 가능)</span>
					</li>
					<!--
					<li>2. 'Consumer Key 발급신청'페이지에서 필요한 항목값 기입 후 이메일로 발급 받음<br />
					&nbsp;&nbsp;&nbsp;도메인:<?php if($TPL_VAR["config_system"]["domain"]){?>
						<?php echo $TPL_VAR["config_system"]["domain"]?>

<?php }else{?>
						<?php echo $TPL_VAR["config_system"]["subDomain"]?>

<?php }?>
					입력<br />
					&nbsp;&nbsp;&nbsp;<b>( 도메인 변경시 Consumer 키를 새로 발급받으셔서 변경해 주셔야 합니다.)</b><br />
					&nbsp;&nbsp;&nbsp;- 신청 후에는 인증하신 이메일 계정으로 전송된 발급 정보를 확인 해주시기 바랍니다.<br />
					<li>3. 발급받은 Consumer Key와 인증 하신 이메일 계정으로 발송된 Key Secret 값을 위에 입력칸에 넣은 후 ‘사용하여 운영함’ 체크 <br />
					&nbsp;&nbsp;&nbsp;(신청 완료 후 좌측 ‘Consumer Key 관리’ 에서 확인 가능)</li>
					-->
				</ol>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<!-- 요즘 설정  -->
<div  id="snsdiv_y"  class="hide" >
	<div style="padding-top:5px;"></div>

	<table width="100%" class="joinform-user-table info-table-style">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th">Consumer Key</th>
				<td class="its-td"><input type='text'  name="key_y_lay"  id="key_y_lay" value="<?php if($TPL_VAR["sns"]["key_y"]){?><?php echo $TPL_VAR["sns"]["key_y"]?><?php }else{?><?php }?>"  size="80"></td>
			</tr>
			<tr >
				<th class="its-th">Consumer Secret</th>
				<td class="its-td"><input type='text'  name="secret_y_lay"  id="secret_y_lay" value="<?php if($TPL_VAR["sns"]["secret_y"]){?><?php echo $TPL_VAR["sns"]["secret_y"]?><?php }else{?><?php }?>"  size="80"></td>
			</tr>
			<tr >
				<td class="its-td" colspan="2" ><label><input type="checkbox" name="use_y_lay"  id="use_y_lay"  value="1"   <?php if($TPL_VAR["sns"]["use_y"]){?>checked<?php }?> /> 사용하여 운영함</label></td>
			</tr>
			<tr><td colspan="2" align="center">
			<div class="center" style="padding:10px;"><span class="btn large black"><button  type="button"  id="yozmbtn"   >적용하기</button></span></div></td>
			</tr>
			<tr><td colspan="2" align="center">
				<table width="100%" class="joinform-user-table info-table-style"  align="center">
					<col width="150" /><col width="" />
					<tbody >
						<tr >
							<th class="its-th">API 키 발급 방법 안내
							<span  style="float:right"><span class="btn small cyanblue" ><button type="button" onclick="window.open('http://manual.firstmall.kr/html/manual.php?category=010012', 'manual')" >매뉴얼 가기</button></span> &nbsp;&nbsp;</span>
							</th>
						</tr>
						<tr >
							<td class="its-td"  align="left">
							<ol style="padding:10px;">
								<li>1. <a href="https://dna.daum.net/myapi/authapi/new" target="_blank">https://dna.daum.net/myapi/authapi/new</a> 방문 ( 다음 로그인 필요)</li>
								<li>2. 필요한 항목값 기입 후 발급 받음<br />
								&nbsp;&nbsp;&nbsp;<b>주요 입력 항목 참조</b><br />
								&nbsp;&nbsp;&nbsp;- 앱url 은 쇼핑몰 url 기입<br />
								&nbsp;&nbsp;&nbsp;- 앱형태는 ‘웹서비스’ 선택<br />
								&nbsp;&nbsp;&nbsp;- 서비스 권한은 ‘읽기/쓰기 선택<br />
								&nbsp;&nbsp;&nbsp;- 인증방식 선택 후  아래에 나오는 입력칸에 <br />
								&nbsp;&nbsp;&nbsp;CallBack URL:<?php if($TPL_VAR["config_system"]["domain"]){?>http://<?php echo $TPL_VAR["config_system"]["domain"]?>/sns_process/sociallogin?sns=yozm<?php }else{?>http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>/sns_process/sociallogin?sns=yozm<?php }?>
								입력<br />
								&nbsp;&nbsp;&nbsp;<b>( 도메인 변경시 CallBack url 을 변경해야 합니다.)</b>
								</li>
								<li>3. 신청 후 ‘인증형 API’ 의 관리 및 통계 에서 컨슈머 키와 컨슈머 시크릿을 확인할 수 있으며<br />
								&nbsp;&nbsp;&nbsp;위의 입력칸에 넣은 후 ‘사용하여 운영함’ 체크</li>
							</ol>
							</td>
						</tr>
					</tbody>
				</table>
			</td></tr>
		</tbody>
	</table>

</div>

<!-- 네이버 설정 가이드 -->
<div  id="snsdiv_n" class="snsdiv_n hide" >

	<div style="width:100%;height:20px;">
		<div class="desc" style="float:left;">네이버로 회원가입과 로그인이 되는 쇼핑몰을 운영하고 싶으시면 ↓ 아래의 정보를 설정해 주십시오.</div>
		<div style="float:right;"><span class="btn small orange" style="line-height:14px;"><button type="button" mode=""  class="btn_sns_guide" gubun="naver">안내) 발급 방법 안내 </button></span></div>
	</div>

	<div style="margin-top:5px;">
	<table width="100%" class="joinform-user-table info-table-style">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th">ClientID</th>
				<td class="its-td"><input type='text'  name="key_n_lay"  id="key_n_lay" value="<?php if($TPL_VAR["sns"]["key_n"]){?><?php echo $TPL_VAR["sns"]["key_n"]?><?php }else{?><?php }?>"  style="width:95%"></td>
			</tr>
			<tr >
				<th class="its-th">ClientSecret</th>
				<td class="its-td"><input type='text'  name="secret_n_lay"  id="secret_n_lay" value="<?php if($TPL_VAR["sns"]["secret_n"]){?><?php echo $TPL_VAR["sns"]["secret_n"]?><?php }else{?><?php }?>"style="width:95%"></td>
			</tr>
			<tr >
				<td class="its-td" colspan="2" ><label><input type="checkbox" name="use_n_lay"  id="use_n_lay"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> checked <?php }else{?><?php if($TPL_VAR["sns"]["use_n"]){?>checked<?php }?> <?php }?>   /> 사용하여 운영함</label></td>
			</tr>
		</tbody>
	</table>
	
	<div class="center" style="padding:10px;"><span class="btn large black"><button  type="button" <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="naverbtn" <?php }?> >적용하기</button></span></div>

	</div>

</div>

<!-- 네이버 설정 가이드 -->
<div id="naverguide_cont" style="margin-top:5px;display:none;">
	<table width="100%" class="joinform-user-table info-table-style"  align="center">
		<col width="150" /><col width="" />
		<tbody>
			<tr>
				<th class="its-th">Client Key 키 발급 방법 안내</th>
			</tr>
			<tr >
				<td class="its-td"   align="left">
				<ol style="padding:10px;">
					<li>1. <a href="http://developer.naver.com/wiki/pages/NaverLogin" target="_blank" ><u><span class="cyanblue">http://developer.naver.com/wiki/pages/NaverLogin</span></u></a> 방문 (네이버 로그인 필요)</li>
					<li style="margin-top:15px;">2. '네이버 로그인 등록' 클릭
						<br />
						<img src="/admin/skin/default/images/common/guide_member_naver1.gif">
					</li>
					<li style="margin-top:15px;">3. 입력 후 신청(발급까지 2~3일 소요) <br />
						<img src="/admin/skin/default/images/common/guide_member_naver2.gif">
					</li>
					<li style="margin-top:15px;">4. 발급받은 ClientID, ClientSecret 값을 위에 입력칸에 넣은 후 ‘사용하여 운영함’ 체크
						<div style="margin-left:25px;">(신청 완료 후 우측 상단 ‘키 발급/관리’ 에서 확인 가능)</div>
					</li>
						<!--
						<strong>주요 입력 항목 참조</strong>
						<div style="margin-left:25px;">
						- 서비스 환경 : PC/Mobile 웹<br />
						- 서비스 / Callback URL<br />
							<div style="margin-left:20px;">
								<strong>PC웹</strong><br />
							사이트 URL : <?php if($TPL_VAR["config_system"]["domain"]){?>
							http://<?php echo $TPL_VAR["config_system"]["domain"]?>

<?php }else{?>
							http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>

<?php }?><br />
							CallBack URL : <?php if($TPL_VAR["config_system"]["domain"]){?>
							http://<?php echo $TPL_VAR["config_system"]["domain"]?>/sns_process/naveruserck
<?php }else{?>
							http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>/sns_process/naveruserck
<?php }?>
							<br />
							<strong>Mobile웹</strong><br />
							사이트 URL : <?php if($TPL_VAR["config_system"]["domain"]){?>
							http://m.<?php echo $TPL_VAR["config_system"]["domain"]?>

<?php }else{?>
							http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?>

<?php }?><br />
							CallBack URL : <?php if($TPL_VAR["config_system"]["domain"]){?>
							http://m.<?php echo $TPL_VAR["config_system"]["domain"]?>/sns_process/naveruserck
<?php }else{?>
							http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?>/sns_process/naveruserck
<?php }?>
							</div>
						</div>
						-->
					<li style="margin-top:10px;">
					<strong>( 도메인 변경시 ClientID,ClientSecret 키를 신규 발급받아 변경해 주셔야 합니다.)</strong>
					</li>
				</ol>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<!-- 카카오 설정 -->
<div  id="snsdiv_k" class="snsdiv_k hide" >

	<div style="width:100%;height:20px;">
		<div class="desc" style="float:left;">카카오로 회원가입과 로그인이 되는 쇼핑몰을 운영하고 싶으시면 ↓ 아래의 정보를 설정해 주십시오.</div>
		<div style="float:right;"><span class="btn small orange" style="line-height:14px;"><button type="button" mode=""  class="btn_sns_guide" gubun="kakao">안내) 발급 방법 안내 </button></span></div>
	</div>

	<div style="margin-top:5px;">
	<table width="100%" class="joinform-user-table info-table-style">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th">Javascript Key</th>
				<td class="its-td"><input type='text'  name="key_k_lay"  id="key_k_lay" value="<?php if($TPL_VAR["sns"]["key_k"]){?><?php echo $TPL_VAR["sns"]["key_k"]?><?php }else{?><?php }?>"  style="width:95%"></td>
			</tr>
			<tr >
				<td class="its-td" colspan="2" ><label><input type="checkbox" name="use_k_lay"  id="use_k_lay"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> checked <?php }else{?><?php if($TPL_VAR["sns"]["use_k"]){?>checked<?php }?> <?php }?>   /> 사용하여 운영함</label></td>
			</tr>
		</tbody>
	</table>
	
	<div class="center" style="padding:10px;"><span class="btn large black"><button  type="button" <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="kakaobtn" <?php }?> >적용하기</button></span></div>

	</div>

</div>

<!-- 카카오 설정 가이드 -->
<div id="kakaoguide_cont" style="margin-top:5px;display:none;">
	<table width="100%" class="joinform-user-table info-table-style"  align="center">
		<col width="150" /><col width="" />
		<tbody>
			<tr>
				<th class="its-th">Kakao App Key 키 발급 방법 안내</th>
			</tr>
			<tr >
				<td class="its-td"   align="left">
				<ol style="padding:1px;">
					<li style="padding-bottom:3px;">1. <a href="https://developers.kakao.com/apps" target="blank" >https://developers.kakao.com/apps</a> 접속한 후 하단의 <span class="bold">‘앱 개발 시작하기’</span> 버튼 클릭</li>
					<li style="padding-bottom:3px;">2. 카카오계정으로 로그인</li>
					<li style="padding-bottom:3px;">3. 앱 이름을 입력 후 ‘만들기’ 버튼 클릭
					<br/>
					<img src="/admin/skin/default/images/sns/kakao_1.JPG" >
					</li>
					<li style="padding-bottom:3px;">4. 해당 앱에 대한 키값이 발급되며 3번째 항목의 <span class="red">Javascript 키</span>값을 확인합니다.</li>
					<li style="padding-bottom:3px;">5. 좌측 메뉴에서 설정 – 일반 클릭 → 페이지 중간의 <img src="/admin/skin/default/images/sns/kakao_2.JPG" > 버튼 클릭</li>
					<li style="padding-bottom:3px;">6. ‘웹’ 클릭 → <span class="red">사이트 도메인주소</span> 입력(사용하려는 모든 도메인 주소 입력)후 추가 클릭<br />
						<div style="margin-left:15px;">								
						<span style="color:blue;">※ 필독 : 도메인은 <?php if($TPL_VAR["config_system"]["domain"]){?>http://<?php echo $TPL_VAR["config_system"]["domain"]?>, http://www.<?php echo $TPL_VAR["config_system"]["domain"]?><?php }else{?>http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>, http://www.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php }?>
<?php if($TPL_VAR["config_system"]["domain"]){?>, http://m.<?php echo $TPL_VAR["config_system"]["domain"]?><?php }else{?>http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php }?> 등, 모바일 도메인과 www 포함한 것과 포함하지 않은 것 모두 입력
						</div>
						<img src="/admin/skin/default/images/sns/kakao_3.JPG" >
					</li>
					<li style="padding-bottom:3px;">7. 추가 버튼 클릭</li>
					<li style="padding-bottom:3px;">8. 화면의 <span class="red">사이트 도메인주소</span>와 <span class="red">Javascript 키</span> 값을 확인 후
						<br/> 
					퍼스트몰 관리자페이지에 위의  API Javascript Key 를 입력 하세요.</li>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<!-- 다음 설정 -->
<div  id="snsdiv_d" class="snsdiv_d hide" >

	<div style="width:100%;height:20px;">
		<div class="desc" style="float:left;">다음으로 회원가입과 로그인이 되는 쇼핑몰을 운영하고 싶으시면 ↓ 아래의 정보를 설정해 주십시오.</div>
		<div style="float:right;"><span class="btn small orange" style="line-height:14px;"><button type="button" mode=""  class="btn_sns_guide" gubun="daum">안내) 발급 방법 안내 </button></span></div>
	</div>

	<div style="margin-top:5px;">
	<table width="100%" class="joinform-user-table info-table-style">
		<col width="150" /><col width="" />
		<tbody >
			<tr >
				<th class="its-th">ClientID</th>
				<td class="its-td"><input type='text'  name="key_d_lay"  id="key_d_lay" value="<?php if($TPL_VAR["sns"]["key_d"]){?><?php echo $TPL_VAR["sns"]["key_d"]?><?php }else{?><?php }?>"  style="width:95%"></td>
			</tr>
			<tr >
				<th class="its-th">ClientSecret</th>
				<td class="its-td"><input type='text'  name="secret_d_lay"  id="secret_d_lay" value="<?php if($TPL_VAR["sns"]["secret_d"]){?><?php echo $TPL_VAR["sns"]["secret_d"]?><?php }else{?><?php }?>"style="width:95%"></td>
			</tr>
			<tr >
				<td class="its-td" colspan="2" ><label><input type="checkbox" name="use_d_lay"  id="use_d_lay"  value="1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> checked <?php }else{?><?php if($TPL_VAR["sns"]["use_d"]){?>checked<?php }?> <?php }?>   /> 사용하여 운영함</label></td>
			</tr>
		</tbody>
	</table>
	
	<div class="center" style="padding:10px;"><span class="btn large black"><button  type="button" <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="daumbtn" <?php }?> >적용하기</button></span></div>

	</div>

</div>

<!-- 다음 설정 가이드 -->
<div id="daumguide_cont" style="margin-top:5px;display:none;">
	<table width="100%" class="joinform-user-table info-table-style"  align="center">
		<col width="150" /><col width="" />
		<tbody>
			<tr>
				<th class="its-th">Client Key 키 발급 방법 안내</th>
			</tr>
			<tr >
				<td class="its-td"   align="left">
				<ol style="padding:10px;">
					<li>1. <a href="http://developers.daum.net/console" target="_blank" ><u><span class="cyanblue">http://developers.daum.net/console</span></u></a> 방문</li>
					<li style="margin-top:15px;">2. 좌측 상단의 <img src="/admin/skin/default/images/common/guide_api_btn_daum1.jpg" align="absmiddle" /> 클릭(다음 로그인 필요)</li>
					<li style="margin-top:15px;">3. 앱 이름: 내용 입력 > 완료 클릭
						<br />
						<img src="/admin/skin/default/images/common/guide_api_daum2.jpg" />
					</li>
					<li style="margin-top:15px;">4. API 키 > Oauth <img src="/admin/skin/default/images/common/guide_api_btn_daum4.jpg" align="absmiddle" />클릭<br />
						<img src="/admin/skin/default/images/common/guide_api_daum3.jpg" width="900px" />
					</li>
					<li>5. 내용 입력 > 완료<br />
						<img src="/admin/skin/default/images/common/guide_api_daum5.jpg" /></div>
					</li>
				</ol>
				</td>
			</tr>
		</tbody>
	</table>
</div>