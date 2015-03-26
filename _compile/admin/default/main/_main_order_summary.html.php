<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/main/_main_order_summary.html 000002419 */ 
$TPL_orderSummary_1=empty($TPL_VAR["orderSummary"])||!is_array($TPL_VAR["orderSummary"])?0:count($TPL_VAR["orderSummary"]);?>
<style type="text/css">
.pop_area { width:97px;height:33px; background-color:#fff; border:1px #636364 solid; }
.pop_link { line-height:13px; padding-left:5px; font-size:11px; }
.pop_link span { color:#6E6E6E; }
.pop_link span:hover { color:#000; }
</style>
<table cellpadding="0" cellspacing="0" border="0" align="center" width="95%" class="tb_summary_partition">
<?php if($TPL_orderSummary_1){$TPL_I1=-1;foreach($TPL_VAR["orderSummary"] as $TPL_K1=>$TPL_V1){$TPL_I1++;?>
<?php if($TPL_K1=='101'){?>
<tr><td class="under_line2" colspan="4"></td></tr>
<?php }?>
<?php if(($TPL_I1% 2)== 0){?>
<tr>
	<td class="s_tit">
<?php if($TPL_V1["link_export"]){?>
		<a class="t_bold" style="position:relative; cursor:pointer;" onmouseover='$(".link_div_<?php echo $TPL_K1?>").show();' onmouseout='$(".link_div_<?php echo $TPL_K1?>").hide();'>
			<?php echo $TPL_V1["name"]?>

			<div class="link_div_<?php echo $TPL_K1?>" style="position:absolute; top:-10px; right:-100px; display:none;">
				<div class="msg_div_<?php echo $TPL_K1?> pop_area" >
					<table cellspacing="0" cellpadding="0" width="100%" height="27px" border="0" style="padding-top:5px;">
					<tr><td class="pop_link"><a href="../order/catalog?chk_step[<?php echo $TPL_K1?>]=1"><strong>·</strong><span>주문리스트 가기</span></a></td></tr>
					<tr><td class="pop_link"><a href="../export/catalog?export_status[<?php echo $TPL_K1?>]=1"><strong>·</strong><span>출고리스트 가기</span></a></td></tr>
					</table>
				</div>
			</div>
		</a>
<?php }else{?>
		<a href="<?php echo $TPL_V1["link"]?>" class="t_bold" ><?php echo $TPL_V1["name"]?></a>
<?php }?>
	</td>
	<td class="rlign" width="57px">
		<a href="<?php echo $TPL_V1["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_V1["count"])?></span></a> 건
	</td>
<?php }else{?>
	<td class="e_tit"><a href="<?php echo $TPL_V1["link"]?>" class="t_bold"><?php echo $TPL_V1["name"]?></a></td>
	<td class="rlign" width="57px">
		<a href="<?php echo $TPL_V1["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_V1["count"])?></span></a> 건
	</td>
</tr>
<?php }?>
<?php }}?>
</table>