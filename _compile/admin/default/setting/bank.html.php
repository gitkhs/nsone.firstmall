<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/setting/bank.html 000011711 */ 
$TPL_loop_1=empty($TPL_VAR["loop"])||!is_array($TPL_VAR["loop"])?0:count($TPL_VAR["loop"]);
$TPL_loop2_1=empty($TPL_VAR["loop2"])||!is_array($TPL_VAR["loop2"])?0:count($TPL_VAR["loop2"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">
/* 계좌 사용여부에 따라 배경색상 변경 */
function set_tr_bgcolor(){
	var obj = $("table.simplelist-table-style tbody tr");
	obj.css("background-color","#ffffff");
	obj.find("select[name='accountUse[]'] option[value='y']:selected").parent().parent().parent().css("background-color","#dfeaff");
}
$(document).ready(function() {
	/* 계좌순서변경 */
	$("table.simplelist-table-style tbody").sortable({items:'tr'});

	/* 계좌 사용여부에 따라 배경색상 변경 */
	$("table.simplelist-table-style tbody tr select[name='accountUse[]'],table.simplelist-table-style tbody tr select[name='accountUseReturn[]']").live("change",function(){
		set_tr_bgcolor();
	});

	/* 계좌추가 */
	$("#addBank").live("click",function(){
<?php if($TPL_VAR["isdemo"]["isdemo"]){?>
			<?php echo $TPL_VAR["isdemo"]["isdemojs2"]?>

<?php }else{?>

			var obj = $("#bankTable tbody tr").eq(0).clone();
			obj.find("input").val("");
			obj.find("select option").eq(0).attr("selected",true);
			$("#bankTable tbody").append(obj);
			/* 인풋박스 타이틀 표기 */
			setDefaultText();
			/* 계좌 사용여부에 따라 배경색상 변경 */
			set_tr_bgcolor();
<?php }?>
	});

	/* 계좌삭제 */
	$("#bankTable tbody tr .removeBank").live("click",function(){
<?php if($TPL_VAR["isdemo"]["isdemo"]){?>
			<?php echo $TPL_VAR["isdemo"]["isdemojs2"]?>

<?php }else{?>
		 	if($("#bankTable tbody tr .removeBank").length > 1){
				$(this).parent().parent().parent().remove();
			}else{
				$(this).parent().parent().parent().find("input").val("");
				$(this).parent().parent().parent().find("select option").eq(0).attr("selected",true);
				/* 인풋박스 타이틀 표기 */
				setDefaultText();
				/* 계좌 사용여부에 따라 배경색상 변경 */
				set_tr_bgcolor();
			}
<?php }?>
	});

	/* 반품배송비 입금계좌추가 */
	$("#addBankReturn").live("click",function(){
<?php if($TPL_VAR["isdemo"]["isdemo"]){?>
			<?php echo $TPL_VAR["isdemo"]["isdemojs2"]?>

<?php }else{?>

			var obj = $("#bankReturnTable tbody tr").eq(0).clone();
			obj.find("input").val("");
			obj.find("select option").eq(0).attr("selected",true);
			$("#bankReturnTable tbody").append(obj);
			/* 인풋박스 타이틀 표기 */
			setDefaultText();
			/* 계좌 사용여부에 따라 배경색상 변경 */
			set_tr_bgcolor();

<?php }?>
	});

	/* 반품배송비 입금계좌삭제 */
	$("#bankReturnTable tbody tr .removeBankReturn").live("click",function(){
<?php if($TPL_VAR["isdemo"]["isdemo"]){?>
			<?php echo $TPL_VAR["isdemo"]["isdemojs2"]?>

<?php }else{?>

			if($("#bankReturnTable tbody tr .removeBankReturn").length > 1){
				$(this).parent().parent().parent().remove();
			}else{
				$(this).parent().parent().parent().find("input").val("");
				$(this).parent().parent().parent().find("select option").eq(0).attr("selected",true);
				/* 인풋박스 타이틀 표기 */
				setDefaultText();
				/* 계좌 사용여부에 따라 배경색상 변경 */
				set_tr_bgcolor();
			}

<?php }?>
	});

	$("#autodeposit_request").click(function(){
		$.get('bank_payment', function(data) {
		  	$('#popup').html(data);
			openDialog("자동입금 신청 <span class='desc'>&nbsp;</span>", "popup", {"width":"800","height":"630"});
		});
	});

	$("#autodeposit_list").click(function(){
		$.get('bank_history', function(data) {
		  	$('#popup').html(data);
			openDialog("자동입금 서비스 신청 내역 <span class='desc'>&nbsp;</span>", "popup", {"width":"800","height":"550"});
		});
	});

	set_tr_bgcolor();
});
</script>

<form name="pgSettingForm" method="post" enctype="multipart/form-data" action="../setting_process/bank" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">

<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 무통장</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">

			<br class="table-gap" />

			<table style="margin:auto;">
			<tr>
				<td><img src="/admin/skin/default/images/common/icon_setting_now.gif" align="absmiddle" hspace="5" /></td>
				<td>
					<span class="bold fx16">현재설정 : </span>
					<span class="bold fx16 blue" id="now_operating">
<?php if($TPL_VAR["config_system"]["bank"]=="n"){?>
					무통장입금 미사용
<?php }else{?>
					무통장입금 사용
<?php }?>
					</span>
				</td>
			</tr>
			</table>

			<br class="table-gap" />

			<div class="clearbox">
				<div class="item-title" style="float:left;width:92%">무통장 입금 수단 <span class="helpicon" title="쇼핑몰의 무통장 입금 수단을 설정합니다."></span></div>
				<div align="right" style="float:left;width:5%;padding-top:15px;">
					<span class="btn small gray">
					<button type="button" id="addBank">+</button>
					</span>
				</div>
			</div>

			<div class="clearbox">
				<table id="bankTable" class="simplelist-table-style" style="width:100%">
					<colgroup>
						<col width="5%" />
						<col width="10%" />
						<col width="15%" />
						<col width="50%" />
						<col width="10%" />
						<col width="10%" />
					</colgroup>
					<thead>
					<tr>
						<th>순서</th>
						<th>은행</th>
						<th>예금주</th>
						<th>계좌번호</th>
						<th>사용 여부</th>
						<th>삭제</th>
					</tr>
					</thead>
					<tbody>
<?php if($TPL_loop_1){foreach($TPL_VAR["loop"] as $TPL_V1){?>
					<tr>
						<td align="center">↕</td>
						<td align="center">
							<select name="bank[]">
<?php if(is_array($TPL_R2=code_load('bankCode'))&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
<?php if($TPL_V2["codecd"]==$TPL_V1["bank"]){?>
								<option value='<?php echo $TPL_V2["codecd"]?>' selected><?php echo $TPL_V2["value"]?></option>
<?php }else{?>
								<option value='<?php echo $TPL_V2["codecd"]?>'><?php echo $TPL_V2["value"]?></option>
<?php }?>
<?php }}?>
							</select>
						</td>
						<td align="center">
							<input type="text" name="bankUser[]" class="line" title="예금주" size="10" value="<?php echo $TPL_V1["bankUser"]?>"/>
						</td>
						<td align="center">
							<input type="text" name="account[]" class="line" title="계좌번호" size="30" value="<?php echo $TPL_V1["account"]?>"/>
						</td>
						<td align="center">
							<select name="accountUse[]">
<?php if($TPL_V1["accountUse"]=='n'){?>
								<option value="y">사용</option>
								<option value="n" selected>미사용</option>
<?php }else{?>
								<option value="y" selected>사용</option>
								<option value="n">미사용</option>
<?php }?>
							</select>
						</td>
						<td align="center">
							<span class="btn small gray">
							<button type="button" class="removeBank">-</button>
							</span>
						</td>
					</tr>
<?php }}?>
				</tbody>

				</table>
			</div>

			<br class="table-gap" />

			<div class="clearbox">
				<div class="item-title" style="float:left;width:92%">반품배송비 입금계좌</div>
				<div align="right" style="float:left;width:5%;padding-top:15px;">
					<span class="btn small gray">
					<button type="button" id="addBankReturn">+</button>
					</span>
				</div>
			</div>

			<div class="clearbox">
				<table id="bankReturnTable" class="simplelist-table-style" style="width:100%">
					<colgroup>
						<col width="5%" />
						<col width="10%" />
						<col width="15%" />
						<col width="50%" />
						<col width="10%" />
						<col width="10%" />
					</colgroup>
					<thead>
					<tr>
						<th>순서</th>
						<th>은행</th>
						<th>예금주</th>
						<th>계좌번호</th>
						<th>사용 여부</th>
						<th>삭제</th>
					</tr>
					</thead>
					<tbody>
<?php if($TPL_loop2_1){foreach($TPL_VAR["loop2"] as $TPL_V1){?>
					<tr>
						<td align="center">↕</td>
						<td align="center">
							<select name="bankReturn[]">
<?php if(is_array($TPL_R2=code_load('bankCode'))&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
<?php if($TPL_V2["codecd"]==$TPL_V1["bankReturn"]){?>
								<option value='<?php echo $TPL_V2["codecd"]?>' selected><?php echo $TPL_V2["value"]?></option>
<?php }else{?>
								<option value='<?php echo $TPL_V2["codecd"]?>'><?php echo $TPL_V2["value"]?></option>
<?php }?>
<?php }}?>
							</select>
						</td>
						<td align="center">
							<input type="text" name="bankUserReturn[]" class="line" title="예금주" size="10" value="<?php echo $TPL_V1["bankUserReturn"]?>"/>
						</td>
						<td align="center">
							<input type="text" name="accountReturn[]" class="line" title="계좌번호" size="30" value="<?php echo $TPL_V1["accountReturn"]?>"/>
						</td>
						<td align="center">
							<select name="accountUseReturn[]">
<?php if($TPL_V1["accountUseReturn"]=='n'){?>
								<option value="y">사용</option>
								<option value="n" selected>미사용</option>
<?php }else{?>
								<option value="y" selected>사용</option>
								<option value="n">미사용</option>
<?php }?>
							</select>
						</td>
						<td align="center">
							<span class="btn small gray">
							<button type="button" class="removeBankReturn">-</button>
							</span>
						</td>
					</tr>
<?php }}?>
				</tbody>

				</table>
			</div>


			<div class="clearbox">
				<div class="item-title" style="float:left;width:80%">무통장 자동 입금 확인 <span class="desc" style="font-weight:normal;">무통장 자동 입금을 이용하기 위해서 먼저 신청을 하셔야 합니다. </span></div>
				<div align="right" style="float:left;width:15%;padding-top:15px;">
<?php if($TPL_VAR["bankChk"]=='Y'||$TPL_VAR["bankChk"]=='END'){?>
					<span style="float:right;padding-right:8px;padding-top:13px;"><span class="btn small black"><button type="button" id="autodeposit_request">연장신청</button></span></span>

					<span style="float:right;padding-right:8px;padding-top:13px;"><span class="btn small black"><button type="button" id="autodeposit_list">서비스 내역</button></span></span>
<?php }else{?>
					<span style="float:right;padding-right:8px;padding-top:13px;"><span class="btn small black"><button type="button"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="autodeposit_request" <?php }?> />신청하기</button></span></span>
<?php }?>
				</div>
			</div>



<?php if($TPL_VAR["bankChk"]=='Y'){?>
			<div class="clearbox">
			<div style="width:98%;padding-left:15px;">
			<iframe style="width:100%; height:800px;" src="http://bankda.firstmall.kr/?cid=<?php echo $TPL_VAR["cid"]?>" frameborder="0"></iframe>
			</div>
			</div>
<?php }?>

		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->


<div id="popup" class="hide"></div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>