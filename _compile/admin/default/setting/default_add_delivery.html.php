<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/setting/default_add_delivery.html 000002187 */ 
$TPL_loop_1=empty($TPL_VAR["loop"])||!is_array($TPL_VAR["loop"])?0:count($TPL_VAR["loop"]);?>
<script>
$(document).ready(function() {	
	$("#deliveryChkAll").click(function(){
		if($(this).attr("checked")){
			$(".deliveryChk").attr("checked",true).change();
		}else{
			$(".deliveryChk").attr("checked",false).change();
		}
	});

});
</script>
<table align="right">
	<tr>
		<td>추가 배송비는 지역 추가 후 수정할 수 있습니다.</td>
	</tr>
</table>
<table width="100%" class="info-table-style">
<colgroup>
	<col width="5%" />
	<col width="41%" />
	<col width="41%" />
	<col width="13%" />
</colgroup>
<thead>
<tr>
	<th class="its-th-align center" rowspan="2"><input type="checkbox" name="deliveryChkAll" value="y" id="deliveryChkAll"></th>
	<th class="its-th-align center" colspan="2">지역</th>
	<th class="its-th-align center" rowspan="2">추가 배송비</th>
</tr>
<tr>
	<th class="its-th-align center">지번</th>
	<th class="its-th-align center">도로명</th>
</tr>

</thead>
<?php if($TPL_loop_1){foreach($TPL_VAR["loop"] as $TPL_V1){?>
<tr>
	<td class="its-td center"><input type="checkbox" name="default_add_seq[]" value="y" class="deliveryChk"></td>
	<td class="its-td"><span style="padding-left:10px;"><?php echo $TPL_V1["sigungu"]?></span><input type="hidden" name="default_sigungu[]" value="<?php echo $TPL_V1["sigungu"]?>" /></td>
	<td class="its-td"><span style="padding-left:10px;"><?php echo $TPL_V1["sigungu_street"]?></span><input type="hidden" name="default_sigungu_street[]" value="<?php echo $TPL_V1["sigungu_street"]?>" /></td>
	<td class="its-td center">
	<input type="hidden" name="default_addDeliveryCost[]" class="line onlynumber" size="5" value="<?php echo $TPL_V1["addCost"]?>" /><?php echo $TPL_V1["addCost"]?>원
	</td>
</tr>
<?php }}?>
</table>
<div style="padding-top:10px;"></div>
<center><span class="btn medium cyanblue"><button type="button" class="default_add_delivery_set" onclick="default_add_delivery_set();">추  가</button></span></center>