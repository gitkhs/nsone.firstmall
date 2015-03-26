<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/setting/manager.html 000009074 */ 
$TPL_loop_1=empty($TPL_VAR["loop"])||!is_array($TPL_VAR["loop"])?0:count($TPL_VAR["loop"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">
$(document).ready(function() {
	$("#delete_btn").click(function(){
		var cnt = $("input:checkbox[name='manager_seq[]']:checked").length;
		if(cnt<1){
			alert("삭제할 관리자를 선택해 주세요.");
			return;
		}else{
			var queryString = $("#settingForm").serialize();
			if(!confirm("선택한 관리자를 삭제 시키겠습니까? ")) return;
			$.ajax({
				type: "get",
				url: "../setting_process/manager_delete",
				data: queryString,
				success: function(result){
					//alert(result);
					location.reload();
				}
			});
		}
	});


	$('#manager_charge').live('click', function (){
		$.get('manager_payment', function(data) {
			$('#managerPaymentPopup').html(data);
			openDialog("관리자 계정 추가 신청", "managerPaymentPopup", {"width":"800","height":"650"});
		});
	});


	$("input[name='auto_logout']").click(function(){
		init_auto_logout();
	});


	init_auto_logout();
});


function init_auto_logout(){
	if($("input[name='auto_logout']").attr("checked")){
		$(".auto_logout_select").attr("disabled",false);
	}else{
		$(".auto_logout_select").attr("disabled",true);
	}
}


function chkAll(chk, name){
	if(chk.checked){
		$(".manager_seq").attr("checked",true);
		$("input[name='manager_seq[]'][manager_yn='Y']").attr('checked',false);
	}else{
		$(".manager_seq").attr("checked",false);
	}
}

function manager_reg(){
<?php if($TPL_VAR["service_limit"]&&$TPL_VAR["config_system"]["service"]["max_manager_cnt"]&&$TPL_VAR["use_manager_cnt"]>=$TPL_VAR["config_system"]["service"]["max_manager_cnt"]){?>
	openDialog("관리자 계정 이용 안내", "info", {"width":"600","height":"180"});
	return;
<?php }?>
	location.href='manager_reg';
}



function auto_logout(){
	openDialog("자동로그아웃 설정", "autoLogoutPopup", {"width":"500","height":"150"});
}

</script>
<form name="settingForm" id="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/manger" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">

<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 관리자</h2>
		</div>

		<!-- 우측 버튼
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
		</ul>
		-->

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container body-height-resizing">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap body-height-resizing'>
		<div class="slc-body">

			<div class="item-title">
			관리자 리스트
<?php if($TPL_VAR["service_limit"]&&$TPL_VAR["config_system"]["service"]["max_manager_cnt"]){?>
			<span class="desc">(현재 : <?php echo number_format($TPL_VAR["use_manager_cnt"])?>명 / <?php echo number_format($TPL_VAR["config_system"]["service"]["max_manager_cnt"])?>명까지 가능)</span>
<?php }?>
			<span style="float:right;padding-right:8px;">
<?php if($TPL_VAR["managerInfo"]["manager_yn"]=='Y'){?>
				<span class="btn small"><button type="button"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> onclick="auto_logout();" <?php }?> >자동로그아웃 설정</button></span>&nbsp;

<?php }?>
				<span class="btn small black"><button type="button"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> onclick="manager_reg()" <?php }?> >관리자 등록</button></span></span>
			</div>

			<table width="100%" class="info-table-style">
			<col width="50" /><col width="" /><col width="" /><col width="" /><col width="" />
			<col width="" /><col width="" /><col width="" /><col width="" />
			<tr>
				<th class="its-th-align"><input type="checkbox" onclick="chkAll(this,'manager_seq');"></th>
				<th class="its-th-align">관리자 구분</th>
				<th class="its-th-align">관리자ID (접속허용 IP설정)</th>
				<th class="its-th-align">관리자명</th>
				<th class="its-th-align">전화번호</th>

				<th class="its-th-align">이메일</th>
				<th class="its-th-align">최근 접속일</th>
				<th class="its-th-align">등록일</th>
				<th class="its-th-align">관리</th>
			</tr>
<?php if($TPL_loop_1){foreach($TPL_VAR["loop"] as $TPL_V1){?>
			<tr>
				<td class="its-td-align center"><input type="checkbox" name="manager_seq[]" value="<?php echo $TPL_V1["manager_seq"]?>" class="manager_seq" <?php if($TPL_V1["manager_yn"]=='Y'){?>disabled<?php }?> manager_yn="<?php echo $TPL_V1["manager_yn"]?>"></td>
				<td class="its-td"><?php if($TPL_V1["manager_yn"]=='Y'){?>대표운영자<?php }else{?>부운영자<?php }?></td>
				<td class="its-td"><span class="blue bold hand" onclick="location.href='manager_reg?manager_seq=<?php echo $TPL_V1["manager_seq"]?>'"><?php echo $TPL_V1["manager_id"]?></span> (<?php if($TPL_V1["limit_ip"]){?><?php echo $TPL_V1["limit_ip"]?><?php }else{?>미설정<?php }?>)</td>
				<td class="its-td"><?php echo $TPL_V1["mname"]?></td>
				<td class="its-td"><?php echo $TPL_V1["mphone"]?></td>

				<td class="its-td"><?php echo $TPL_V1["memail"]?></td>
				<td class="its-td"><?php echo $TPL_V1["lastlogin_date"]?></td>
				<td class="its-td"><?php echo $TPL_V1["mregdate"]?></td>
				<td class="its-td-align center">
					<span class="btn small gray"><button type="button"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> onclick="location.href='manager_reg?manager_seq=<?php echo $TPL_V1["manager_seq"]?>'" <?php }?>>수정</button></span>
				</td>
			</tr>
<?php }}?>
			</table>

			<div style="padding:5px;">
			<span class="btn small gray"><button type="button" class="hand"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  type="button" <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?>  id="delete_btn" <?php }?>>삭제</button></span>
			</div>

			<br style="line-height:10px;" />

			<!-- 페이징 -->
			<div class="paging_navigation" style="margin:auto;"><?php echo $TPL_VAR["pagin"]?></div>


		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->


</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>


<div id="info" class="hide">
<table width="100%">
<tr><td>무료몰+ : 기본 1계정 (계정 추가 시 1계정당 11,000원, 최초 1회 결제로 기간 관계 없이 계속 이용)</td></tr>
<tr><td>프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 관리자 계정을 무제한 이용 가능합니다.</td></tr>
<tr><td height="20"></td></tr>
<tr>
	<td align="center">
	<span class="btn medium cyanblue valign-middle"><input type="button" value="추가신청 > " id="manager_charge" /></span>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" />
	</td>
</tr>
</table>
</div>

<div id="managerPaymentPopup" class="hide"></div>
<div id="autoLogoutPopup" class="hide">
<form name="autoFrm" method="post" action="../setting_process/auto_logout" target="actionFrame">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<label><input type="checkbox" name="auto_logout" value="Y" <?php if($TPL_VAR["auto_logout"]=="Y"){?>checked<?php }?>>사용</label> 
				&nbsp;&nbsp;
				<select name="until_time" class="auto_logout_select">
					<option value="1" <?php if($TPL_VAR["until_time"]== 1){?>selected<?php }?>>1시간 후</option>
					<option value="2" <?php if($TPL_VAR["until_time"]== 2){?>selected<?php }?>>2시간 후</option>
					<option value="3" <?php if($TPL_VAR["until_time"]== 3){?>selected<?php }?>>3시간 후</option>
					<option value="4" <?php if($TPL_VAR["until_time"]== 4){?>selected<?php }?>>4시간 후</option>
					<option value="5" <?php if($TPL_VAR["until_time"]== 5){?>selected<?php }?>>5시간 후</option>
					<option value="6" <?php if($TPL_VAR["until_time"]== 6){?>selected<?php }?>>6시간 후</option>
					<option value="10" <?php if($TPL_VAR["until_time"]== 10){?>selected<?php }?>>10시간 후</option>
					<option value="12" <?php if($TPL_VAR["until_time"]== 12){?>selected<?php }?>>12시간 후</option>
				</select>
				자동으로 로그아웃 합니다.
			</td>
		</tr>
	</table>

	<div align="center">
	<span class="btn large gray"><input type="submit" value="저장"></span>
	<span class="btn large gray"><input type="button" value="취소" onclick="closeDialog('#autoLogoutPopup');"></span>
	</div>
</form>
</div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>