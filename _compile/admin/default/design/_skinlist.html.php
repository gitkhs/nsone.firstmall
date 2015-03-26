<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/design/_skinlist.html 000003150 */ 
$TPL_my_skin_list_1=empty($TPL_VAR["my_skin_list"])||!is_array($TPL_VAR["my_skin_list"])?0:count($TPL_VAR["my_skin_list"]);?>
<script>
var arrSkinInfo = {};
</script>

<table class="skin-setting-table" border="0" cellpadding="0" cellspacing="0">
<colgroup>
	<col width="" />
	<col width="" />
	<col width="160" />
	<col width="140" />
	<col width="120" />
	<col width="220" />
</colgroup>
<tr>
	<td class="sst-sub-title">선택</td>
	<td class="sst-sub-title">스킨명 (폴더명)</td>
	<td class="sst-sub-title">현재 상태</td>
	<td class="sst-sub-title">미리보기</td>
	<td class="sst-sub-title">일시</td>
	<td class="sst-sub-title">백업 / 복사 / 이름변경 / 삭제</td>
</tr>
<?php if($TPL_my_skin_list_1){$TPL_I1=-1;foreach($TPL_VAR["my_skin_list"] as $TPL_V1){$TPL_I1++;?>
<tr>
	<td class="sst-row">
		<input type="radio" name="skin_chk" value="<?php echo $TPL_V1["skin"]?>" <?php if($_GET["checkedSkin"]==$TPL_V1["skin"]){?>checked<?php }?> />
		<script>
		arrSkinInfo["<?php echo $TPL_V1["skin"]?>"] = <?php echo json_encode($TPL_VAR["my_skin_list"])?>[<?php echo $TPL_I1?>];
		</script>
	</td>
	<td class="sst-row left">
		<div class="sst-skin-name"><?php echo $TPL_V1["name"]?> (<?php echo $TPL_V1["skin"]?>)</div>
	</td>
	<td class="sst-row">
		<?php echo implode(" / ",$TPL_VAR["my_skin_list_icon"][$TPL_I1])?>

	</td>
	<td class="sst-row">
		<span class="btn small"><a href="<?php if($TPL_VAR["skinPrefix"]=='mobile'){?>http://<?php echo $TPL_VAR["mobileDomain"]?><?php }else{?>/<?php }?>?previewSkin=<?php echo $TPL_V1["skin"]?>" target="_top">미리보기</a></span>
		<span class="btn small"><a href="<?php if($TPL_VAR["skinPrefix"]=='mobile'){?>http://<?php echo $TPL_VAR["mobileDomain"]?><?php }else{?>/<?php }?>?previewSkin=<?php echo $TPL_V1["skin"]?>" target="_blank">새창보기</a></span>
	</td>
	<td class="sst-row"><div class="sst-skin-regdate"><?php echo $TPL_V1["regdate"]?></div></td>
	<td class="sst-row" >
		<span class="btn small"><input type="button" value="백업"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?>  onclick="backup_skin('<?php echo $TPL_V1["skin"]?>')" <?php }?>/></span>
		<span class="btn small"><input type="button" value="복사"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?>  onclick="copy_skin('<?php echo $TPL_V1["skin"]?>')"<?php }?> /></span>
		<span class="btn small"><input type="button" value="이름변경"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> onclick="rename_skin('<?php echo $TPL_V1["skin"]?>','<?php echo $TPL_V1["name"]?>')"<?php }?>  /></span>
		<span class="btn small"><input type="button" value="삭제"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> onclick="delete_skin('<?php echo $TPL_V1["skin"]?>')" <?php }?> /></span>
	</td>
</tr>
<?php }}?>
</table>