<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/member/sms.html 000029477 */ 
$TPL_admins_arr_1=empty($TPL_VAR["admins_arr"])||!is_array($TPL_VAR["admins_arr"])?0:count($TPL_VAR["admins_arr"]);
$TPL_loop_1=empty($TPL_VAR["loop"])||!is_array($TPL_VAR["loop"])?0:count($TPL_VAR["loop"]);
$TPL_replace_code_loop_1=empty($TPL_VAR["replace_code_loop"])||!is_array($TPL_VAR["replace_code_loop"])?0:count($TPL_VAR["replace_code_loop"]);
$TPL_restriction_item_1=empty($TPL_VAR["restriction_item"])||!is_array($TPL_VAR["restriction_item"])?0:count($TPL_VAR["restriction_item"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<style type="text/css">
/* sms 종류 Tab 메뉴 CSS */
	div.sms-group-tab-lay {width:100%;border-bottom:2px solid #727b99;}
	table.sms-group-tab {width:600px;}
	table.sms-group-tab td {font-size:15px;text-align:center;height:26px;font-weight:bold;}
	table.sms-group-tab td.tab-start {width:2px;background:url('/admin/skin/default/images/common/tab_bg.gif') no-repeat;background-position:0;}
	table.sms-group-tab td.tab-end {width:2px;background:url('/admin/skin/default/images/common/tab_bg.gif') no-repeat;background-position:-598px 0;}
	table.sms-group-tab td.tab-item {cursor:pointer;padding:0 10px;color:#7b7b7b;background-color:#ffffff;border-top:2px solid #727b99;border-right:2px solid #727b99;}
	table.sms-group-tab td.nolinknone {cursor:pointer;padding:0 10px;color:#7b7b7b;background-color:#ffffff;border-top:2px solid #727b99;border-right:2px solid #727b99;}
	table.sms-group-tab td.tab-item span.current-arrow {padding:0 5px;margin-left:12px;}
	table.sms-group-tab td.tab-item:hover {color:#727b99;background-color:#e8e9ee;}
	table.sms-group-tab td.tab-item.current {color:#ffffff;font-weight:bold;background-color:#727b99;}
	table.sms-group-tab td.tab-item.current:hover {color:#ffffff;font-weight:bold;background-color:#727b99;}
	table.sms-group-tab td.tab-item.current span.current-arrow {padding:0 5px;margin-left:12px;background:url('/admin/skin/default/images/common/icon_arw.gif') no-repeat;background-position:0 5px;}
/* --- sms 종류 Tab 메뉴 CSS */
</style>
<script type="text/javascript">
$(document).ready(function() {

	//
	$(".sms_contents").live("keydown",function(){
		str = $(this).val();
		$(this).parent().parent().parent().find(".sms_byte").html(chkByte(str));
	});
	$(".sms_contents").live("keyup",function(){
		str = $(this).val();
		$(this).parent().parent().parent().find(".sms_byte").html(chkByte(str));
	}).trigger('keyup');
	$('.del_message').click(function(){
		$(this).parent().parent().parent().find('textarea').val('');
		$(this).parent().parent().parent().find(".sms_byte").html(chkByte(''));
	});

	//
	$("#addNum").bind("click",function(){
		var cnt		= $(".admins_num1").length + 1;
		var idx		= cnt - 1;
		var addHtml	= "<tr><td>";
		addHtml += "관리자("+cnt+") <input type=\"text\" name=\"admins_num1[]\" size=\"5\" maxlength=\"4\" class='admins_num1'> - <input type=\"text\" name=\"admins_num2[]\" size=\"5\" maxlength=\"4\"> - <input type=\"text\" name=\"admins_num3[]\" size=\"5\" maxlength=\"4\">";
		addHtml += " <span class=\"btn-minus\"><button type=\"button\" id=\"delNum\" idx=\""+idx+"\"></button>";
		addHtml += "</td></tr>";
		$('#add_plus_phone').append(addHtml);

		var disabled	= '';
		var name		= '';
		var ynHtml		= '';
		$(".admin_yn_lay").each(function(){
			name		= $(this).attr('area');
			disabled	= $(this).attr('dis');
			ynHtml	= '<div id="admins_yn_label_'+idx+'"><label><input type="checkbox" name="'+name+'_admins_yn_'+idx+'" value="Y" '+disabled+' /> 관리자('+cnt+')</label></div>';
			$(this).append(ynHtml);
		});
	});
	$("#delNum").live("click",function(){
		$("div#admins_yn_label_"+$(this).attr('idx')).remove();
		$(this).parent().parent().remove();
	});


	/* ### */
	$(".info_code").click(function(){
		$("#s_title").html($(this).attr("title"));
		setSmsInfo($(this).attr("name"));
		openDialog("사용 가능한 치환코드", "infoPopup", {"width":"500","height":"300"});
	});

	$(".default_msg").click(function(){
		var type	= $(this).attr("name");
		$.getJSON('./default_sms_msg?type='+type, function(data){

			if	(data.user){
				$("textarea[name='"+type+"_user']").val(data.user);
				$("textarea[name='"+type+"_user']").parent().parent().parent().find(".sms_byte").html(chkByte(data.user));
			}
			if	(data.admin){
				$("textarea[name='"+type+"_admin']").val(data.admin);
				$("textarea[name='"+type+"_admin']").parent().parent().parent().find(".sms_byte").html(chkByte(data.user));
			}
		});
	});

	// 종류 tab 메뉴
	/*
	$(".tab-item").click(function(){
		$(".tab-item").each(function(){
			$(this).removeClass("current");
		});
		$(this).addClass("current");
		$(".sms_message_group_lay").hide();
		$("#sms_message_group_lay_"+$(this).attr('value')).show();
	});
	*/

	 $(".btnRestriction").on("click",function(){
	   $.get('./sms_restriction?first=1', function(data) {     
			$('#restrictionPopup').html(data);
<?php if($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_PREM'){?>
			var h = "520";
<?php }else{?>
			var h = "590";
<?php }?>
			openDialog("발송시간 제한 설정","restrictionPopup",{"width":"700","height":h});
	   });
	});

	$(".btnSmsReceptionGuide").on("click",function(){
		openDialog("[안내] 문자수신대상","receptionGuidePopup",{"width":"400","height":"320"});
	});

	chkSMSDialog();
});

// 상품명 길이 제한 선택
function goodsname_limit(obj){
	
	if($("select[name='"+obj+"_use']").val() == 'y'){
		$("."+obj+"_limit").show();
		$("select[name='"+obj+"_use']").css("width","70");
	}else{	
		$("."+obj+"_limit").hide();
		$("select[name='"+obj+"_use']").css("width","145");
	}
}

// 종류 tab 메뉴
function tabmenu(no){
   var i=1;
   $(".tab-item").each(function(){
		$(this).removeClass("current");
		if(no == i){
			 $(".sms_message_group_lay").hide();
			 $(this).addClass("current");
			 if($(this).attr("value") == '4'){
				  $("#sms_restriction").show();
			 }else{
				  $("#sms_restriction").hide();
				  $("#sms_message_group_lay_"+$(this).attr('value')).show();
			 }
		}
		i = i+1;
   });
}


function chkSMSDialog(){
	if ( "<?php echo $TPL_VAR["chk"]?>" == '' || "<?php echo $TPL_VAR["sms_auth"]?>" == '' ){
		$.get('../member_process/getAuthPopup', function(data) {
		  	$('#authPopup').html(data);
		  	openDialog("SMS 발송키 등록 안내 <span class='desc'>&nbsp;</span>", "authPopup", {"width":"800","height":"300"});
		});
		return;
	}
}


function setSmsInfo(type){
	$(".s_info").hide();
	switch (type){
		case 'join':	//회원가입 시
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["join"])?>').show();
		break;
		case 'withdrawal':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["withdrawal"])?>').show();
		break;
		case 'order':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["order"])?>').show();
		break;
		case 'settle':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["settle"])?>').show();
		break;
		case 'released':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["released"])?>').show();
		break;
		case 'released2':
		   $('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["released"])?>').show();
		break;
		case 'delivery':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["delivery"])?>').show();
		break;
		case 'delivery2':
		   $('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["delivery"])?>').show();
		break;
		case 'cancel':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["cancel"])?>').show();
		break;
		case 'refund':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["refund"])?>').show();
		break;
		case 'findid':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["findid"])?>').show();
		break;
		case 'findpwd':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["findpwd"])?>').show();
		break;
		case 'coupon_released':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["coupon_released"])?>').show();
		break;
		case 'coupon_released2':
		   $('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["coupon_released"])?>').show();
		break;
		case 'coupon_cancel':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["coupon_cancel"])?>').show();
		break;
		case 'coupon_delivery':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["coupon_delivery"])?>').show();
		break;
		case 'coupon_delivery2':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["coupon_delivery"])?>').show();
		break;
		case 'coupon_refund':
			$('#re_<?php echo implode(",#re_",$TPL_VAR["use_replace_code"]["coupon_refund"])?>').show();
		break;
	}
}
</script>

<form name="memberForm" id="memberForm" method="post" target="actionFrame" action="../member_process/sms">

<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar">

		<!-- 타이틀 -->
		<div class="page-title">
			<h2>SMS 발송 관리</h2>
		</div>

		<!-- 좌측 버튼
		<ul class="page-buttons-left">
			<li><span class="btn large icon"><button><span class="arrowleft"></span>이동버튼</button></span></li>
			<li><span class="btn large icon"><button><span class="arrowleft"></span>이동버튼</button></span></li>
		</ul> -->

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  type="button" <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> type="submit" <?php }?> >저장하기<span class="arrowright"></span></button></span></li>
		</ul>
	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<?php $this->print_("top_menu",$TPL_SCP,1);?>


<!-- 서브 레이아웃 영역 : 시작 -->
<div class="item-title">SMS 자동발송 <span class="helpicon" title="각 상황이 일어나면 설정된 SMS가 자동으로 발송됩니다."></span></div>
<div class="clearbox">
	<table class="info-table-style" style="width:100%">
		<colgroup>
			<col width="15%" />
			<col width="45%" />
			<col width="40%" />
		</colgroup>
		<tbody>
		<tr>
			<th class="its-th-align center">발신 번호</th>
			<td class="its-td-align left"  style="padding-left:10px;" colspan="2"><input type="text" name="send_num[]" size="5" maxlength="4" value="<?php echo $TPL_VAR["send_num"][ 0]?>"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > - <input type="text" name="send_num[]" size="5" maxlength="4" value="<?php echo $TPL_VAR["send_num"][ 1]?>"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > - <input type="text" name="send_num[]" size="5" maxlength="4" value="<?php echo $TPL_VAR["send_num"][ 2]?>"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  ></td>
		</tr>
		<tr>
			<th class="its-th-align center">관리자 수신 번호</th>
			<td class="its-td-align left"  style="padding-left:10px;" colspan="2">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody id="add_plus_phone">
<?php if($TPL_VAR["admins_arr"]){?>
<?php if($TPL_admins_arr_1){$TPL_I1=-1;foreach($TPL_VAR["admins_arr"] as $TPL_V1){$TPL_I1++;?>
					<tr>
						<td>관리자(<?php echo $TPL_I1+ 1?>) <input type="text" name="admins_num1[]" size="5" maxlength="4" value="<?php echo $TPL_V1["number"][ 0]?>" class="admins_num1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > - <input type="text" name="admins_num2[]" size="5" maxlength="4" value="<?php echo $TPL_V1["number"][ 1]?>"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > - <input type="text" name="admins_num3[]" size="5" maxlength="4" value="<?php echo $TPL_V1["number"][ 2]?>"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  ><?php if($TPL_I1== 0){?> <span class="btn-plus"><button type="button" id="addNum"></button><?php }else{?> <span class="btn-minus"><button type="button" id="delNum" idx="<?php echo $TPL_I1?>"></button><?php }?></td>
					</tr>
<?php }}?>
<?php }else{?>
				<tr>
					<td>관리자(1) <input type="text" name="admins_num1[]" size="5" maxlength="4" class="admins_num1"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > - <input type="text" name="admins_num2[]" size="5" maxlength="4"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > - <input type="text" name="admins_num3[]" size="5" maxlength="4"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  > <span class="btn-plus"><button type="button" id="addNum"></button></td>
				</tr>
<?php }?>
				</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<th class="its-th-align center">SMS 또는 LMS</th>
			<td class="its-td-align left"  style="padding-left:10px;" colspan="2">
			90bytes 이하는 SMS로 발송되며, 90bytes 초과 시 LMS로 발송됩니다.
			</td>
		</tr>
		<tr>
			<th class="its-th-align center" rowspan="2">상품명<br />치환코드 길이</th>
			<td class="its-td-align left"  style="padding-left:10px;">
				<div style="float:left;width:200px;">&#123;ord_item&#125;</pre> 주문상품</div>
				<div style="float:left;"><span class="ord_item_limit"><input type="text" name="ord_item_limit" value="<?php echo $TPL_VAR["ord_item_limit"]?>" id="" size="5" style="text-align:right;">자로 </span>
				<select name="ord_item_use" style="width:70px;height:23px;" onchange="goodsname_limit('ord_item')">
					<option value="y" <?php if($TPL_VAR["ord_item_use"]=='y'){?>selected<?php }?>>제한함</option>
					<option value="n" <?php if($TPL_VAR["ord_item_use"]=='n'){?>selected<?php }?>>제한하지 않음</option>
				</select>
				</div>
			</td>
			<td class="its-td-align left"  style="padding-left:10px;">
				<div style="float:left;width:220px;">&#123;repay_item&#125; 취소/반품→환불완료 상품 </div>
				<div style="float:left;"><span class="repay_item_limit"><input type="text" name="repay_item_limit" value="<?php echo $TPL_VAR["repay_item_limit"]?>" id="" size="5" style="text-align:right;">자로 </span>
				<select name="repay_item_use" style="width:70px;height:23px;" onchange="goodsname_limit('repay_item')">
					<option value="y" <?php if($TPL_VAR["repay_item_use"]=='y'){?>selected<?php }?>>제한함</option>
					<option value="n" <?php if($TPL_VAR["repay_item_use"]=='n'){?>selected<?php }?>>제한하지 않음</option>
				</select>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td-align left"  style="padding-left:10px;">
				<div style="float:left;width:200px;">&#123;go_item&#125;</pre> 출고완료/배송완료 상품 </div>
				<div style="float:left;"><span class="go_item_limit"><input type="text" name="go_item_limit" value="<?php echo $TPL_VAR["go_item_limit"]?>" id="" size="5" style="text-align:right;">자로 </span>
				<select name="go_item_use" style="width:70px;height:23px;" onchange="goodsname_limit('go_item')">
					<option value="y" <?php if($TPL_VAR["go_item_use"]=='y'){?>selected<?php }?>>제한함</option>
					<option value="n" <?php if($TPL_VAR["go_item_use"]=='n'){?>selected<?php }?>>제한하지 않음</option>
				</select>
				</div>
			</td>
			<td class="its-td-align left"  style="padding-left:10px;">
				<div style="float:left;width:220px;">&#123;goods_item&#125; 쿠폰 상품 </div>
				<div style="float:left;"><span class="goods_item_limit"><input type="text" name="goods_item_limit" value="<?php echo $TPL_VAR["goods_item_limit"]?>" id="" size="5" style="text-align:right;">자로 </span>
				<select name="goods_item_use" style="width:70px;height:23px;" onchange="goodsname_limit('goods_item')">
					<option value="y" <?php if($TPL_VAR["goods_item_use"]=='y'){?>selected<?php }?>>제한함</option>
					<option value="n" <?php if($TPL_VAR["goods_item_use"]=='n'){?>selected<?php }?>>제한하지 않음</option>
				</select>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
</div>

<br style="line-height:16px;" />

<!-- 종류 TAB 메뉴 -->
<div class="left sms-group-tab-lay">
	<table class="sms-group-tab" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tab-start"></td>
		<td class="tab-item current" onclick="tabmenu(1)" value="1">공통 <span class="current-arrow"></span></td>
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
		<td class="nolinknone tab-item" value="2">실물 발송 상품 <span class="current-arrow"></span></td>
<?php }else{?>
		<td class="tab-item"  onclick="tabmenu(2)"value="2">실물 발송 상품 <span class="current-arrow"></span></td>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_PREM'){?>
		<td class="nolinknone tab-item" value="3">쿠폰 발송 상품 <span class="current-arrow"></span></td>
<?php }else{?>
		<td class="tab-item" onclick="tabmenu(3)" value="3">쿠폰 발송 상품 <span class="current-arrow"></span></td>
<?php }?>
		<td class="tab-item" onclick="tabmenu(4)" value="4">발송시간 제한<span class="current-arrow"></span></td>
		<td class="tab-end"></td>
	</tr>
	</table>
</div>
<!-- //종류 TAB 메뉴 -->

<br style="line-height:16px;" />

<?php if($TPL_loop_1){foreach($TPL_VAR["loop"] as $TPL_K1=>$TPL_V1){?>
<div id="sms_message_group_lay_<?php echo $TPL_K1?>" class="sms_message_group_lay <?php if($TPL_K1> 1){?>hide<?php }?>">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
<?php if(is_array($TPL_R2=$TPL_V1)&&!empty($TPL_R2)){$TPL_I2=-1;foreach($TPL_R2 as $TPL_V2){$TPL_I2++;?>
<?php if($TPL_V2["name"]){?><input type="hidden" name="group_list[]" value="<?php echo $TPL_V2["name"]?>" /><?php }?>
<?php if($TPL_I2&&$TPL_I2% 3== 0){?>
		</tr><tr><td height="5" colspan="3"></td></tr><tr>
<?php }?>
		<td width="33%" align="center" valign="top">

<?php if($TPL_V2["text"]){?>
			<div class="clearbox">
				<table class="info-table-style">
				<tr>
					<th class="its-th-align center">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td></td>
							<td align="right">
								<span style="padding-left:30px;"><?php echo $TPL_V2["text"]?></span>
<?php if($TPL_V2["add_text"]){?><span class="red"> = <?php echo $TPL_V2["add_text"]?></span><?php }?>
							</td>
							<td align="right" style="padding-right:10px;">
								<span class="btn small cyanblue"><input type="button" value="기본내용" name="<?php echo $TPL_V2["name"]?>" title="<?php echo $TPL_V2["text"]?>" class="default_msg"/></span>
								<span class="btn small cyanblue"><input type="button" value="치환코드" name="<?php echo $TPL_V2["name"]?>" title="<?php echo $TPL_V2["text"]?>" class="info_code"/></span>
							</td>
						</tr>
						</table>
					</th>
				</tr>
				<tr>
					<td class="its-td-align" style="text-align:center;">
						<table border="0" cellpadding="0" cellspacing="0" style="margin:auto;">
						<tr>
							<td align="center" valign="top">
								<!-- ### USER -->
								<div class="sms-define-form">
									<div class="sdf-head clearbox">
										<div class="fl"><img src="/admin/skin/default/images/common/sms_i_antena.gif"></div>
										<div class="fr"><img src="/admin/skin/default/images/common/sms_i_battery.gif"></div>
									</div>
									<div class="sdf-body-wrap">
										<div class="sdf-body">
											<textarea name="<?php echo $TPL_V2["name"]?>_user" class="sms_contents"><?php echo $TPL_V2["user"]?></textarea>
											<div class="sdf-body-foot clearbox">
												<div class="fl"><b class="sms_byte">0</b>byte</div>
												<div class="fr"><img src="/admin/skin/default/images/common/sms_btn_send.gif" align="absmiddle" class="del_message" /></div>
											</div>
										</div>
									</div>
								</div>
							</td>
							<td width="10"></td>
							<td>
								<!-- ### ADMIN -->
								<div class="sms-define-form">
									<div class="sdf-head clearbox">
										<div class="fl"><img src="/admin/skin/default/images/common/sms_i_antena.gif"></div>
										<div class="fr"><img src="/admin/skin/default/images/common/sms_i_battery.gif"></div>
									</div>
									<div class="sdf-body-wrap">
										<div class="sdf-body">
<?php if($TPL_V2["disabled"]=='disabled'){?>
											<textarea readonly style="background-color:#bbbbbb;color:#fff;text-align:center;"><?php echo "\n\n\n"?> &nbsp;관리자 수신이<?php echo "\n"?>불필요한 메세지입니다.</textarea>
<?php }else{?>
											<textarea name="<?php echo $TPL_V2["name"]?>_admin" class="sms_contents"><?php echo $TPL_V2["admin"]?></textarea>
<?php }?>
											<div class="sdf-body-foot clearbox">
												<div class="fl"><b class="sms_byte">0</b>byte</div>
												<div class="fr"><img src="/admin/skin/default/images/common/sms_btn_send.gif" align="absmiddle" class="del_message" /></div>
											</div>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="left pd10" valign="top">
								<label><input type="checkbox" name="<?php echo $TPL_V2["name"]?>_user_yn" value="Y" <?php if($TPL_V2["user_chk"]=='Y'||$TPL_V2["user_req"]=='y'){?>checked<?php }?> <?php if($TPL_V2["user_req"]=='y'){?>onclick="this.checked=true;"<?php }?> /> 
<?php if($TPL_V2["name"]=='join'||$TPL_V2["name"]=='findid'||$TPL_V2["name"]=='withdrawal'||$TPL_V2["name"]=='findpwd'){?>
								고객
<?php }elseif($TPL_V2["name"]=='released2'||$TPL_V2["name"]=='delivery2'||$TPL_V2["name"]=='coupon_released'||$TPL_V2["name"]=='coupon_delivery'){?>
								받는분
<?php }else{?>
								주문자
<?php }?>
								</label>
							</td>
							<td></td>
							<td class="left pd10" valign="top">
								<div class="admin_yn_lay" area="<?php echo $TPL_V2["name"]?>" dis="<?php echo $TPL_V2["disabled"]?>">
<?php if($TPL_V2["arr"]){?>
<?php if(is_array($TPL_R3=$TPL_V2["arr"])&&!empty($TPL_R3)){$TPL_I3=-1;foreach($TPL_R3 as $TPL_V3){$TPL_I3++;?>
									<div id="admins_yn_label_<?php echo $TPL_I3?>">
									<label><input type="checkbox" name="<?php echo $TPL_V2["name"]?>_admins_yn_<?php echo $TPL_I3?>" value="Y" <?php if($TPL_V2["admins_chk"][$TPL_I3]=='Y'){?>checked<?php }?> <?php echo $TPL_V2["disabled"]?>/> 관리자(<?php echo $TPL_I3+ 1?>)</label>
									</div>
<?php }}?>
<?php }else{?>
									<div id="admins_yn_label_0">
									<label><input type="checkbox" name="<?php echo $TPL_V2["name"]?>_admins_yn_0" value="Y"  <?php echo $TPL_V2["disabled"]?>/> 관리자(1)</label>
									</div>
<?php }?>
								</div>
							</td>
						</tr>
<?php if($TPL_V2["rest_msg"]){?>
						<tr>
						   <td colspan="3" class="center">
						   <img src='/admin/skin/default/images/design/icon_order_admin.gif' align='absmiddle' title='관리자 처리 시'>
<?php if($TPL_V2["name"]=='settle'||$TPL_V2["name"]=='delivery'||$TPL_V2["name"]=='delivery2'){?>
						   <img src='/admin/skin/default/images/design/icon_order_system.gif' align='absmiddle' title='시스템 처리 시'>
<?php }?>
						   <?php echo $TPL_V2["rest_msg"]?></td>
						</tr>
<?php }?>
						</table>
					</td>
				</tr>
				</table>
			</div>
<?php }?>
		</td>
<?php }}?>
	</tr>
	</table>
</div>
<?php }}?>
</form>

<div id="infoPopup" class="hide">
	<div style=""><span style="font-weight:bold;" id="s_title"></span> 사용 가능한 치환코드입니다.</div>
	<table class="info-table-style" style="width:100%">
	<colgroup>
		<col width="30%" />
		<col width="70%" />
	</colgroup>
	<thead>
	<tr>
		<th class="its-th-align center" >치환코드</th>
		<th class="its-th-align center" >설명</th>
	</tr>
	</thead>
	<tbody>
<?php if($TPL_replace_code_loop_1){foreach($TPL_VAR["replace_code_loop"] as $TPL_K1=>$TPL_V1){?>
	<tr class="s_info hide" oldid="s_tab<?php echo $TPL_K1+ 1?>" id="re_<?php echo $TPL_V1["cd"]?>">
		<td class="its-td-align center">&#123;<?php echo $TPL_V1["cd"]?>&#125;</td>
		<td class="its-td"><?php echo $TPL_V1["nm"]?> <?php if($TPL_V1["etc"]){?><br /><span style='color:#696969;font-size:11px;line-height:15px;font-family:돋움;'><?php echo $TPL_V1["etc"]?></span><?php }?></td>
	</tr>
<?php }}?>
	</tbody>
	</table>
	<div style="padding:10px;"></div>
</div>


<!-- 발송시간 제한 -->
<div id="sms_restriction" class="hide">

	<div style="padding-left:15px;line-height:27px;">아래의 <span style="padding:0px 15px 0px 15px;line-height:3px;background-color:#fff9ce;border:1px solid #edd003;"></span>에 해당되는 행위가 설정된 심야시간에 발생할 경우 해당 문자는 설정된 오전 시간에 자동으로 발송됩니다.
	<span class="btn small cyanblue"><button type="button" class="btnRestriction">설정</button></span>
	</div>

	<table class="info-table-style" style="width:100%">
		<!-- 테이블 헤더 : 시작 -->
		<colgroup>
			<col width="6%" />
			<col width="18%" />
			<col width="12%" />
			<col width="23%" />
			<col width="23%" />
			<col width="6%" />
			<col width="6%" />
			<col width="6%" />
		</colgroup>
		<thead class="lth">
		<tr>
			<th colspan="2" rowspan="2" class="its-th-align">문자 발송 경우</th>
			<th colspan="3" class="its-th-align">행위(액션)</th>
			<th colspan="3" class="its-th-align">문자수신대상 <span class="btn small cyanblue"><button type="button" class="btnSmsReceptionGuide">안내</button></span></th>
		</tr>
		<tr>
			<th class="its-th-align">고객</th>
			<th class="its-th-align">관리자</th>
			<th class="its-th-align">시스템</th>
			<th class="its-th-align">고객</th>
			<th class="its-th-align">관리자</th>
			<th class="its-th-align">입점 판매자</th>
		</tr>
		</thead>
		<tbody class="ltb otb" >
<?php if($TPL_restriction_item_1){foreach($TPL_VAR["restriction_item"] as $TPL_K1=>$TPL_V1){?>
			
<?php if(is_array($TPL_R2=$TPL_V1)&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_K2=>$TPL_V2){?>
<?php if($TPL_K2!='usecnt'){?>
<?php if(($TPL_VAR["service_code"]!='P_FREE'&&$TPL_VAR["service_code"]!='P_PREM')||(($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_PREM')&&$TPL_K1!='coupon')){?>
		<tr class="list-row">
<?php if($TPL_VAR["ori_key"]!=$TPL_K1){?>
			<td rowspan="<?php echo count($TPL_V1)- 1?>" class="its-td-align center"><?php echo $TPL_VAR["restriction_title"][$TPL_K1]?></td>
<?php }?>
			<td class="its-td-align"><span class="pdl10"><?php if(!$TPL_VAR["restriction_title"][$TPL_K2]){?>×<?php }else{?><?php echo $TPL_VAR["restriction_title"][$TPL_K2]?><?php }?></span></td>
			<td class="its-td-align center"><?php if(!$TPL_V2["ac_customer"]){?>×<?php }else{?><div style="padding-left:5px;text-align:left;"><?php echo $TPL_V2["ac_customer"]?></div><?php }?></td>
			<td class="its-td-align center" <?php if($TPL_V2["use"]=='y'&&$TPL_V2["ac_admin"]){?>style="background-color:#fff9ce;"<?php }?>><?php if(!$TPL_V2["ac_admin"]){?>×<?php }else{?><div style="padding-left:5px;text-align:left;"><?php echo $TPL_V2["ac_admin"]?></div><?php }?></td>
			<td class="its-td-align center" <?php if($TPL_V2["use"]=='y'&&$TPL_V2["ac_system"]){?>style="background-color:#fff9ce;"<?php }?>><?php if(!$TPL_V2["ac_system"]){?>×<?php }else{?><div style="padding-left:5px;text-align:left;"><?php echo $TPL_V2["ac_system"]?></div><?php }?></td>
			<td class="its-td-align center"><?php if(!$TPL_V2["tg_customer"]){?>×<?php }else{?><?php echo $TPL_V2["tg_customer"]?><?php }?></td>
			<td class="its-td-align center"><?php if(!$TPL_V2["tg_admin"]){?>×<?php }else{?><?php echo $TPL_V2["tg_admin"]?><?php }?></td>
			<td class="its-td-align center"><?php if(!$TPL_V2["tg_seller"]){?>×<?php }else{?><?php echo $TPL_V2["tg_seller"]?><?php }?></td>
			<?php echo $this->assign('ori_key',$TPL_K1)?>

		</tr>
<?php }?><?php }?>
<?php }}?>
<?php }}?>
		</tbody>
	</table>
</div>


<div id="authPopup" class="hide"></div>
<div id="restrictionPopup" class="hide"></div>
<div id="receptionGuidePopup" class="hide">
	<table class="info-table-style" style="width:100%">
	<colgroup>
		<col width="20%" />
		<col width="80%" />
	</colgroup>
	<thead>
	<tr>
		<th class="its-th-align center" >기호</th>
		<th class="its-th-align center" >설명</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td class="its-td-align center">×</td>
		<td class="its-td">수신 불가</td>
	</tr>
	<tr>
		<td class="its-td-align center">○</td>
		<td class="its-td">수신 가능</td>
	</tr>
	<tr>
		<td class="its-td-align center"><span style='color:#696969'>●</span></td>
		<td class="its-td">수신 가능<br /><span style='color:#696969;font-size:11px;line-height:15px;font-family:돋움;'>(관리자 처리 또는 시스템 자동 처리 시 발송시간<br />제한을 설정하지 않음)</span></td>
	</tr>
	<tr>
		<td class="its-td-align center"><span style='color:#d90000'>●</span></td>
		<td class="its-td">수신 가능<br /><span style='color:#696969;font-size:11px;line-height:15px;font-family:돋움;'>(관리자 처리 또는 시스템 자동 처리 시 발송시간<br />제한을 설정함)</span></td>
	</tr>
	</tbody>
	</table>
</div>


<script type="text/javascript">
var no = <?php echo $_GET['no']?>;
tabmenu(no);
goodsname_limit('ord_item');
goodsname_limit('repay_item');
goodsname_limit('go_item');
goodsname_limit('goods_item');
</script>
<?php $this->print_("layout_footer",$TPL_SCP,1);?>