<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/design/skin.html 000027252 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<script>
$(function(){

	/* 실제스킨 적용버튼 */
	$("#btnRealSkinApply").click(function(){
		$(this).attr('disabled',true);
		apply_skin('realSkin');
	});

	/* 작업용스킨 적용버튼 */
	$("#btnWorkingSkinApply").click(function(){
		$(this).attr('disabled',true);
		apply_skin('workingSkin');
	});

	$("#btnSkinApply").click(function(){
		$(this).attr('disabled',true);
		apply_skin('skin');
	});

	load_skin_list();

<?php if($TPL_VAR["skinPrefix"]){?>
	$("div.slc-head .mitem[skinPrefix='<?php echo $TPL_VAR["skinPrefix"]?>']").closest('li').addClass("selected");
	$("div.slc-head .mitem[skinPrefix='<?php echo $TPL_VAR["skinPrefix"]?>'] .onImg").hide();
	$("div.slc-head .mitem[skinPrefix='<?php echo $TPL_VAR["skinPrefix"]?>'] .offImg").show();
<?php }else{?>
	$("div.slc-head .mitem:eq(0)").closest('li').addClass("selected");
	$("div.slc-head .mitem:eq(0) .onImg").hide();
	$("div.slc-head .mitem:eq(0) .offImg").show();
<?php }?>

	var skinPrefixCellClassName = "<?php if($TPL_VAR["skinPrefix"]){?><?php echo $TPL_VAR["skinPrefix"]?><?php }else{?>pc<?php }?>SkinPrefixCell";

	$("th."+skinPrefixCellClassName).css({
		'font-weight'	: 'bold',
		'border-top'	: '2px solid red',
		'border-left'	: '2px solid red',
		'border-right'	: '2px solid red'
	});
	$("td."+skinPrefixCellClassName).css({
		'font-weight'	: 'bold',
		'border-left'	: '2px solid red',
		'border-right'	: '2px solid red'
	});
	$("td."+skinPrefixCellClassName).last().css({
		'border-bottom'	: '2px solid red'
	});

});

/* 스킨목록 불러오기 */
function load_skin_list(){
	var checkedSkin = $("input[name='skin_chk']:checked").val();
	$(".sst-skin-list-container").load("../design/get_skin_list_html?skinPrefix=<?php echo $TPL_VAR["skinPrefix"]?>&checkedSkin="+(checkedSkin?checkedSkin:''));
}

/* 선택 스킨 적용 */
function apply_skin(type){

	if(!$("input[name='skin_chk']:checked").length){
		if(type=='realSkin')	alert('실제 적용할 스킨을 선택해주세요');
		if(type=='workingSkin')	alert('작업용으로 적용할 스킨을 선택해주세요');
		if(type=='skin')	alert('적용할 스킨을 선택해주세요');
		return;
	}

	var skin = $("input[name='skin_chk']:checked").val();


	var panelId = type + "Panel";

	var applySkinBox = $(".sst-apply-skin-box:eq(0)").clone();

	if(type=='skin'){
		panelId = "realSkinPanel";
		var applySkinBox2 = $(".sst-apply-skin-box:eq(0)").clone();
		applySkinBoxAnimation(skin,"workingSkinPanel",applySkinBox2);
	}

	applySkinBoxAnimation(
			skin,
			panelId,
			applySkinBox,
			function(){
				if(type=='realSkin'){

					openDialogConfirm(arrSkinInfo[skin].name + ' 스킨을 실제적용스킨으로 설정하시겠습니까?',400,140,function(){
						apply_skin_process(type,skin);
						$("#"+panelId + " .sst-body").empty().append(applySkinBox.css({'position':'relative','left':0,'top':0}));

						applySkinBox.find(".sst-apply-skin-popup a").attr("href","/?previewSkin="+skin);
					},function(){
						applySkinBox.remove();
					});

					$("#btnRealSkinApply").removeAttr('disabled');
				}
				else if(type=='workingSkin'){

					openDialogConfirm(arrSkinInfo[skin].name + ' 스킨을 작업용스킨으로 설정하시겠습니까?',400,140,function(){
						apply_skin_process(type,skin);
						$("#"+panelId + " .sst-body").empty().append(applySkinBox.css({'position':'relative','left':0,'top':0}));

						applySkinBox.find(".sst-apply-skin-popup a").attr("href","/?previewSkin="+skin);
					},function(){
						applySkinBox.remove();
					});

					$("#btnWorkingSkinApply").removeAttr('disabled');
				}
				else if(type=='skin'){

					openDialogConfirm(arrSkinInfo[skin].name + ' 스킨을 실제적용스킨 & 작업용스킨으로 설정하시겠습니까?',400,160,function(){
						apply_skin_process(type,skin);
						$("#"+panelId + " .sst-body").empty().append(applySkinBox.css({'position':'relative','left':0,'top':0}));

						applySkinBox.find(".sst-apply-skin-popup a").attr("href","/?previewSkin="+skin);
						applySkinBox2.find(".sst-apply-skin-popup a").attr("href","/?previewSkin="+skin);
					},function(){
						applySkinBox.remove();
						applySkinBox2.remove();
					});

					$("#btnSkinApply").removeAttr('disabled');
				}

			}
	);
}

function applySkinBoxAnimation(skin,panelId,applySkinBox,callFunction){

	applySkinBox.find(".sst-apply-skin-screenshot img").attr('src','/data/skin/'+skin+'/configuration/'+arrSkinInfo[skin].screenshot).end();
	applySkinBox.find(".sst-apply-skin-screenshot img").css({'width':<?php echo $TPL_VAR["skin_apply_box_width"]?>,'height':<?php echo $TPL_VAR["skin_apply_box_height"]?>});
	applySkinBox.find(".sst-apply-skin-name").html(arrSkinInfo[skin].name + " (" + skin + ")");

	applySkinBox.appendTo('body')
	.css({
		'position'	:'absolute',
		'width'		:0,
		'height'	:0,
		'left'		:$("input[name='skin_chk']:checked").offset().left,
		'top'		:$("input[name='skin_chk']:checked").offset().top,
	})
	.animate(
		{
			'width'		:'<?php echo $TPL_VAR["skin_apply_box_width"]?>',
			'height'	:'<?php echo $TPL_VAR["skin_apply_box_height"]?>',
			'left'		:$("#"+panelId + " .sst-apply-skin-screenshot").offset().left,
			'top'		:$("#"+panelId + " .sst-apply-skin-screenshot").offset().top
		}
		,callFunction
	);
}

/* 선택 스킨 적용  처리 */
function apply_skin_process(type, skin){
	$.ajax({
		'url' : '../design_process/apply_skin',
		'data' : {'type':type, 'skin':skin, 'skinPrefix':'<?php echo $TPL_VAR["skinPrefix"]?>'},
		'type' : 'post',
		'success' : function(res){
			load_skin_list();
		}
	});
}

/* 스킨백업*/
function backup_skin(skin){
	openDialogConfirm(skin + ' 스킨을 ZIP파일로 다운로드 하시겠습니까?',400,140,function(){
		$("iframe[name='actionFrame']").attr('src','../design_process/backup_skin?skin=' + skin);
	});
}

/* 스킨복사*/
function copy_skin(skin){
	openDialogConfirm(skin + ' 스킨을 복사하시겠습니까?',400,140,function(){
		loadingStart();
		$("iframe[name='actionFrame']").attr('src','../design_process/copy_skin?skin=' + skin);
	});
}

/* 스킨 삭제 */
function delete_skin(skin){
	openDialogConfirm(skin + ' 스킨을 삭제하시겠습니까?',400,140,function(){
		loadingStart();
		$("iframe[name='actionFrame']").attr('src','../design_process/delete_skin?skin=' + skin + '&skinPrefix=<?php echo $TPL_VAR["skinPrefix"]?>');
	});
}


/* 스킨업로드 Dialog */
function upload_skin(){
	openDialog("스킨업로드 <span class='desc'>스킨 ZIP파일을 업로드합니다.</span>", "skinUploadDialogLayer", {"width":410,"height":180});
}

/* 스킨업로드 파일전송 */
function upload_skin_submit(frm){
	loadingStart();
	frm.submit();
	return false;
}

/* 스킨 이름변경 */
function rename_skin(skinFolder,skinName){
	openDialog("스킨 이름변경 <span class='desc'>스킨의 명칭과 폴더명을 변경합니다.</span>", "skinRenameDialogLayer", {"width":550,"height":235});
	$("#skinRenameDialogLayer input[name='skin']").val(skinFolder);
	$("#skinRenameDialogLayer input[name='skinName']").val(skinName);
	$("#skinRenameDialogLayer input[name='skinFolder']").val(skinFolder);
}

/* 스킨 이름변경 전송 */
function rename_skin_submit(frm){
	loadingStart();
	frm.submit();
	return false;
}

// 디스플레이 캐시 on/off 답변영역 open/close 처리
function onoffDisplayCachAnswer(){
	if	($("ul.display-cach-answer").css("display") == 'none'){
		$("ul.display-cach-answer").show();
	}else{
		$("ul.display-cach-answer").hide();
	}
}

// 디스플레이 캐시 on/off 상태 변경
function chgDisplayCachStatus(){
	var now_status	= $("span.display-cach-status").html();
	var chg_status	= '';
	if	(now_status == 'OFF')	chg_status = 'ON';
	else						chg_status = 'OFF';

	$.ajax({
		type: "post",
		url: "../setting_process/set_display_status",
		data: "status="+chg_status,
		success: function(result){
			$("span.display-cach-status").html(chg_status);
			$("button.display-cach-btn").html('캐시기능 '+now_status+'하기');
		}
	});
}
</script>

<style>
table.skin-setting-table {width:100%; border-collapse:collapse;}
table.skin-setting-table .sst-title {height:25px; text-align:center; font-size:11px; font-weight:bold;}
table.skin-setting-table .sst-body {}
table.skin-setting-table .sst-row {height:35px; border:1px solid #ddd; text-align:center; padding:0 5px;}
table.skin-setting-table .sst-skin-name {font-size:12px; color:#666; font-weight:bold;}
table.skin-setting-table .sst-skin-regdate {font-size:11px; color:#999;}
table.skin-setting-table .sst-sub-title {padding:3px; border:1px solid #ddd; background:#e5e5e5; text-align:center; font-size:11px;}

.sst-apply-skin-box {width:<?php echo $TPL_VAR["skin_apply_box_width"]?>px; height:<?php echo $TPL_VAR["skin_apply_box_height"]?>px;}
.sst-apply-skin-screenshot {background-color:#fff; border:3px solid #9f9fa7; overflow:hidden;}
.sst-apply-skin-screenshot img {width:<?php echo $TPL_VAR["skin_apply_box_width"]?>px; height:<?php echo $TPL_VAR["skin_apply_box_height"]?>px;}
.sst-apply-skin-footer {border:1px solid #d8d8d8; height:22px; line-height:22px; background-color:#e8e8e8;}
.sst-apply-skin-name {text-indent:5px; width:150px; overflow:hidden; white-space:nowrap; color:#333; text-align:left; font-size:11px;}
.sst-apply-skin-popup {padding:5px 5px 0 0;}

#realSkinPanel		.sst-title {color:#ce213d;}
#realSkinPanel		.sst-realSkin-title {color:#ce213d;}
#realSkinPanel		.sst-workingSkin-title {color:#0555ce;}
#realSkinPanel 		.sst-apply-skin-screenshot {border:3px solid #3d3f4c;}
#workingSkinPanel	.sst-title {color:#0555ce}
#workingSkinPanel 	.sst-apply-skin-screenshot {border:3px solid #9f9fa7;}
#skinPanel			.sst-title {color:#ce213d;}
#skinPanel 			.sst-apply-skin-screenshot {border:3px solid #3d3f4c;}

div#display_cach_lay {width:100%;margin:0;padding:0;}
div#display_cach_lay ul li {line-height:23px;}
div#display_cach_lay ul li p {padding-left:14px;}
div#display_cach_lay ul li p.first {padding:0;}
div#display_cach_lay ul li p.second {padding-left:28px;}
</style>

<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">

		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">디자인 →</span> 스킨 설정</h2>
		</div>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container body-height-resizing">

	<div class="slc-head pdt10">
	<ul>
<?php if($TPL_VAR["service_code"]!='P_STOR'){?>
<?php if($TPL_VAR["service_code"]=='P_FAMM'){?>
				<li><span id="onlyfacebooklinknone" class=" hand" skinPrefix=''><a ><img src="/admin/skin/default/images/common/icon/icon_tab_pc.gif" class="offImg" />PC 스킨</a></span></li>
<?php }else{?>
				<li><span class="mitem" skinPrefix=''><a href="skin"><img src="/admin/skin/default/images/common/icon/icon_tab_pc.gif" class="offImg" /><img src="/admin/skin/default/images/common/icon/icon_tab_pc_on.gif" class="onImg hide" /> PC 스킨</a></span></li>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_FAMM'){?>
				<li><span  id="onlyfacebooklinknone"  class="hand" ><a ><img src="/admin/skin/default/images/common/icon/icon_tab_mobile.gif" class="offImg" /> Mobile/Tablet 스킨</a></span></li>
<?php }else{?>
				<li><span class="mitem" skinPrefix='mobile'><a href="skin?prefix=mobile"><img src="/admin/skin/default/images/common/icon/icon_tab_mobile.gif" class="offImg" /><img src="/admin/skin/default/images/common/icon/icon_tab_mobile_on.gif" class="onImg hide" /> Mobile/Tablet 스킨</a></span></li>
<?php }?>
<?php if($TPL_VAR["service_code"]!='P_FREE'){?>
				<li><span class="mitem" skinPrefix='fammerce'><a href="skin?prefix=fammerce"><img src="/admin/skin/default/images/common/icon/icon_tab_facebook.gif" class="offImg" /><img src="/admin/skin/default/images/common/icon/icon_tab_facebook_on.gif" class="onImg hide" /> Facebook  PC 스킨 </a></span></li>
<?php }else{?>
				<li><span id="freefacebookconfignone" skinPrefix='' class="hand gray" ><a><img src="/admin/skin/default/images/common/icon/icon_tab_facebook.gif" class="offImg" /> Facebook  PC 스킨</a></span></li>
<?php }?>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
		<li style="display:"><span class="mitem" skinPrefix='store'><a href="skin?prefix=store"><!--<img src="/admin/skin/default/images/common/icon/icon_tab_store.gif" class="offImg" /><img src="/admin/skin/default/images/common/icon/icon_tab_store_on.gif" class="onImg hide" /> -->PC 스킨 </a></span></li>
		<li style="display:"><span class="mitem" skinPrefix='storemobile'><a href="skin?prefix=storemobile"><!--<img src="/admin/skin/default/images/common/icon/icon_tab_storemobile.gif" class="offImg" /><img src="/admin/skin/default/images/common/icon/icon_tab_storemobile_on.gif" class="onImg hide" /> -->Mobile 스킨 </a></span></li>
		<li style="display:"><span class="mitem" skinPrefix='storefammerce'><a href="skin?prefix=storefammerce"><!--<img src="/admin/skin/default/images/common/icon/icon_tab_storefammerce.gif" class="offImg" /><img src="/admin/skin/default/images/common/icon/icon_tab_storefammerce_on.gif" class="onImg hide" /> -->Facebook PC 스킨 </a></span></li>
<?php }?>
	</ul>
	</div>

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">

<?php if($TPL_VAR["service_limit"]){?>
			<br style="line-height:10px;" />
			<div class="center" style="padding-left:20px;width:100%;text-align:center;">
				<div style="border:2px #dddddd solid;padding:10px;width:95%;">
					<table width="100%">
					<tr>
					<td align="left">
						무료몰+ : 실제적용 스킨과 디자인작업용 스킨을 동일하게 운영합니다.<br>
						실제적용 스킨과 디자인작업용 스킨을 따로 분리하여 쇼핑몰을 운영하시려면 프리미엄몰+ 또는 독립몰+로 업그레이드 하시길 바랍니다.<br />업그레이드를 하시면 아래 유료형 전용 스킨도 무료로 이용이 가능 하십니다.
					</td>
					<td align="right"><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></td>
					</tr>
					</table>
				</div>
				<br style="line-height:20px;" />
			</div>
<?php }?>

			<div style="width:912px; margin:25px auto 0px auto;">

				<!-- 적용 스킨 -->
				<table align="center" border="0" cellpadding="0" cellspacing="0" style="margin:auto;">
				<col /><col width="150" /><col />
				<tr>
					<td>
						<table id="realSkinPanel" class="skin-setting-table" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td class="sst-title">실제적용 스킨</td>
						</tr>
						<tr>
							<td class="sst-body">
								<div class="sst-apply-skin-box">
									<!-- <div class="sst-apply-skin-screenshot"><img src="http://interface.firstmall.kr/firstmall_plus/skin/source/<?php echo $TPL_VAR["realSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div> -->
<?php if($TPL_VAR["skinPrefix"]=='mobile'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["realMobileSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='fammerce'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["realFammerceSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='store'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["realStoreSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='storemobile'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["realStoremobileSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='storefammerce'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["realStorefammerceSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div>
<?php }else{?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["realSkin"]?>/configuration/<?php echo $TPL_VAR["realSkinConfiguration"]["screenshot"]?>" /></div>
<?php }?>

									<div class="sst-apply-skin-footer">
										<div class="fl sst-apply-skin-name"><?php echo $TPL_VAR["realSkinConfiguration"]["name"]?> (<?php echo $TPL_VAR["realSkinConfiguration"]["skin"]?>)</div>
										<div class="fr sst-apply-skin-popup"><a href="/?previewSkin=<?php echo $TPL_VAR["realSkinConfiguration"]["skin"]?>" target="_blank"><img src="/admin/skin/default/images/design/btn_newwin.gif" align="absmiddle" /></a></div>
									</div>
								</div>
							</td>
						</tr>
						</table>
					</td>
					<td></td>
					<td>
						<table id="workingSkinPanel" class="skin-setting-table" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td class="sst-title">디자인작업용 스킨</td>
						</tr>
						<tr>
							<td class="sst-body">
								<div class="sst-apply-skin-box">
									<!--<div class="sst-apply-skin-screenshot"><img src="http://interface.firstmall.kr/firstmall_plus/skin/source/<?php echo $TPL_VAR["realSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>-->
<?php if($TPL_VAR["skinPrefix"]=='mobile'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["workingMobileSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='fammerce'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["workingFammerceSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='store'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["workingStoreSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='storemobile'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["workingStoremobileSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>
<?php }elseif($TPL_VAR["skinPrefix"]=='storefammerce'){?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["workingStorefammerceSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>
<?php }else{?>
										<div class="sst-apply-skin-screenshot"><img src="/data/skin/<?php echo $TPL_VAR["workingSkin"]?>/configuration/<?php echo $TPL_VAR["workingSkinConfiguration"]["screenshot"]?>" /></div>
<?php }?>
									<div class="sst-apply-skin-footer">
										<div class="fl sst-apply-skin-name"><?php echo $TPL_VAR["workingSkinConfiguration"]["name"]?> (<?php echo $TPL_VAR["workingSkinConfiguration"]["skin"]?>)</div>
										<div class="fr sst-apply-skin-popup"><a href="/?previewSkin=<?php echo $TPL_VAR["workingSkinConfiguration"]["skin"]?>" target="_blank"><img src="/admin/skin/default/images/design/btn_newwin.gif" align="absmiddle" /></a></div>
									</div>
								</div>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>

				<br /><br /><br />

				<table width="100%"  border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top">
						<table class="skin-setting-table" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td class="sst-title">
								<table width="100%" height="28" border="0" cellpadding="0" cellspacing="0" background="/admin/skin/default/images/design/win_tbl_thbg.gif" style="border:1px solid #cccccc; border-bottom:1px solid #a1a1a1;">
								<tr>
									<td width="30%"></td>
									<td>
<?php if($_GET["prefix"]=='mobile'){?>Mobile/Tablet
<?php }elseif($_GET["prefix"]=='fammerce'){?>Facebook PC
<?php }else{?>PC<?php }?>
										보유 스킨
									</td>
									<td width="30%" align="right">
										<span class="btn small black"><input type="button" value="스킨업로드"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> onclick="upload_skin()" <?php }?> /></span>&nbsp;
									</td>
								</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign="top">
								<div class="sst-skin-list-container" style="max-height:250px; overflow:auto;"></div>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>

				<br />

<?php if($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_FAMM'){?>
				<div align="center">
					<span class="btn large"><input type="button" value="선택한 스킨을 실제적용스킨으로 설정하기" style="color:#999 !important" disabled ></span>
					<span class="btn large"><input type="button" value="선택한 스킨을 작업용스킨으로 설정하기" style="color:#999 !important" disabled /></span>
				</div>
				<div align="center" class="pd10">
					<span class="btn large red"><input type="button" value="선택한 스킨을 실제적용 & 작업용스킨으로 설정하기" id="btnSkinApply" /></span>
				</div>
<?php }else{?>
				<div align="center">
					<span class="btn large red"><input type="button" value="선택한 스킨을 실제적용스킨으로 설정하기" id="btnRealSkinApply" /></span>
					<span class="btn large cyanblue"><input type="button" value="선택한 스킨을 작업용스킨으로 설정하기" id="btnWorkingSkinApply" /></span>
				</div>
<?php }?>

				<br style="line-height:20px;" />

				<!-- 디스플레이 캐시 설정 영역 : 시작 -->
				<div id="display_cach_lay">
				<ul>
					<li class="hand" onclick="onoffDisplayCachAnswer();">Q. FTP 접속 후 상품디스플레이 파일을 수정하였습니다. 그리고 쇼핑몰 화면을 확인하였는데 수정이 되지 않았습니다.</li>
				</ul>
				<ul class="display-cach-answer hide">
					<li>
						<p class="first">A. 쇼핑몰화면에서 <span style="color:red;">상품디스플레이 영역을 빠르게 로딩하기 위하여 캐시 기능을 적용</span>되어 있기 때문입니다.</p>
						<p>아래와 같이 <span style="color:red;">FTP로 접속하여 상품디스플레이 파일을 직접 수정 하실 경우</span>에는 </p>
						<p>상품디스플레이 영역의 캐시 기능을 OFF한 후에 수정된 화면을 확인해 주십시오.</p>
						<p>1) FTP 접속</p>
						<p>2) data/skin/스킨명/_modules/display/ 디렉토리 안에 있는 파일 수정</p>
						<p class="second">(data/skin/스킨명/_modules/display/ 디렉토리 안에 있는 파일이 상품디스플레이 파일입니다)</p>
						<p>3) 쇼핑몰 관리자환경 > 디자인 > 스킨 설정 → [상품디스플레이 영역 캐시 OFF하기] 클릭</p>
						<p>4) 쇼핑몰 화면에서 수정된 상품디스플레이 확인</p>
						<p>5) data/skin/스킨명/_modules/display/ 디렉토리 안에 있는 파일 수정이 완료되면</p>
						<p class="second">쇼핑몰 관리자환경 > 디자인 > 스킨 설정 → [상품디스플레이 영역 캐시 ON하기] 클릭하여 상품디스플레이 로딩 속도를 다시 빠르게 함</p>
					</li>
					<li>
						<strong>▶ 상품디스플레이 영역 캐시 기능 : <span style="color:red;">현재 <span class="display-cach-status"><?php if($TPL_VAR["cfg_system"]["display_cach"]=='OFF'){?>OFF<?php }else{?>ON<?php }?></span> 상태</span></strong>
						<span class="btn small black"><button type="button" class="display-cach-btn" onclick="chgDisplayCachStatus();">캐시기능 <?php if($TPL_VAR["cfg_system"]["display_cach"]=='OFF'){?>ON<?php }else{?>OFF<?php }?>하기</button></span>
					</li>
				</ul>
				</div>
				<!-- 디스플레이 캐시 설정 영역 : 끝 -->

				<!-- 스킨다운로드 영역 : 시작 -->
<?php if($TPL_VAR["skinPrefix"]){?>
					<div class="gabia-pannel" code="<?php echo $TPL_VAR["skinPrefix"]?>_skin_list"  isdemo="<?php echo $TPL_VAR["isdemo"]["isdemo"]?>" ></div>
<?php }else{?>
					<div class="gabia-pannel" code="skin_list"  isdemo="<?php echo $TPL_VAR["isdemo"]["isdemo"]?>" ></div>
<?php }?>
				<!-- 스킨다운로드 영역  영역 : 끝 -->
			</div>

		</div>
	</div>
</div>

<!-- 스킨업로드 레이어 -->
<div id="skinUploadDialogLayer" class="hide">
	<form action="../design_process/upload_skin" target="actionFrame" enctype="multipart/form-data" method="post" onsubmit="return upload_skin_submit(this)">
		<table width="100%" class="info-table-style">
		<colgroup>
			<col width="30%" />
			<col />
		</colgroup>
		<tr>
			<td class="its-th-align center">파일첨부</td>
			<td class="its-td"><input type="file" name="skin_zipfile" /></td>
		</tr>
		</table>
		<br />

		<div align="center"><span class="btn large"><input type="submit" value="업로드" /></span></div>
	</form>
</div>

<!-- 스킨 리네임 레이어 -->
<div id="skinRenameDialogLayer" class="hide">
	<form action="../design_process/rename_skin" target="actionFrame" enctype="multipart/form-data" method="post" onsubmit="return rename_skin_submit(this)">
	<input type="hidden" name="skin" value="" />
	<input type="hidden" name="skinPrefix" value="<?php echo $TPL_VAR["skinPrefix"]?>" />
		<table width="100%" class="info-table-style">
		<col width="150" />
		<tr>
			<td class="its-th">스킨명</td>
			<td class="its-td"><input type="text" name="skinName" value="" style="width:130px" maxlength="30" /> <span class="desc">영문대소문자, 숫자, 언더바, 한글만 가능</span></td>
		</tr>
		<tr>
			<td class="its-th">폴더명(스킨코드)</td>
			<td class="its-td"><input type="text" name="skinFolder" value="" style="width:130px" maxlength="30" /> <span class="desc">영문소문자, 숫자, 언더바만 가능</span></td>
		</tr>
		<tr>
			<td class="its-th">스크린샷</td>
			<td class="its-td"><input type="file" name="skinScreenshot" value="" style="width:220px" class="line" /> <span class="desc">변경시에만 첨부</span></td>
		</tr>
		</table>
		<br />

		<div align="center"><span class="btn large"><input type="submit" value="저장" /></span></div>
	</form>
</div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>