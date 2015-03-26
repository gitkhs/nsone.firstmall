<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/main/_main_goods_summary.html 000002580 */ ?>
<table cellpadding="0" cellspacing="0" border="0" align="center" class="tb_summary_partition">
<!-- 판매중 -->
<tr>
	<td rowspan="2">
		<img src="/admin/skin/default/images/main/admin_mtit12.gif" />
	</td>
	<td><a href="<?php echo $TPL_VAR["goodsSummary"]["goods"]["link"]?>">실물</a></td>
	<td class="rlign">
		<a href="<?php echo $TPL_VAR["goodsSummary"]["goods"]["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_VAR["goodsSummary"]["goods"]["count"])?></span></a> 개
	</td>
</tr>
<tr>
	<td><a href="<?php echo $TPL_VAR["goodsSummary"]["social"]["link"]?>">티켓</a></td>
	<td class="rlign">
		<a href="<?php echo $TPL_VAR["goodsSummary"]["social"]["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_VAR["goodsSummary"]["social"]["count"])?></span></a> 개
	</td>
</tr>
<tr><td class="under_line" colspan="3"></td></tr>
<!-- 종료5일전 -->
<tr>
	<td rowspan="2">
		<img src="/admin/skin/default/images/main/admin_mtit13.gif" />
	</td>
	<td><a href="<?php echo $TPL_VAR["goodsSummary"]["endgoods"]["link"]?>">실물</a></td>
	<td class="rlign">
		<a href="<?php echo $TPL_VAR["goodsSummary"]["endgoods"]["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_VAR["goodsSummary"]["endgoods"]["count"])?></span></a> 개
	</td>
</tr>
<tr>
	<td><a href="<?php echo $TPL_VAR["goodsSummary"]["endsocial"]["link"]?>">티켓</a></td>
	<td class="rlign">
		<a href="<?php echo $TPL_VAR["goodsSummary"]["endsocial"]["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_VAR["goodsSummary"]["endsocial"]["count"])?></span></a> 개
	</td>
</tr>
<tr><td class="under_line" colspan="3"></td></tr>
<!-- 재고10개 이하 -->
<tr>
	<td rowspan="2">
		<img src="/admin/skin/default/images/main/admin_mtit14.gif" />
	</td>
	<td><a href="<?php echo $TPL_VAR["goodsSummary"]["stockgoods"]["link"]?>">실물</a></td>
	<td class="rlign">
		<a href="<?php echo $TPL_VAR["goodsSummary"]["stockgoods"]["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_VAR["goodsSummary"]["stockgoods"]["count"])?></span></a> 건
	</td>
</tr>
<tr>
	<td><a href="<?php echo $TPL_VAR["goodsSummary"]["stocksocial"]["link"]?>">티켓</a></td>
	<td class="rlign">
		<a href="<?php echo $TPL_VAR["goodsSummary"]["stocksocial"]["link"]?>"><span class="cnt_num"><?php echo number_format($TPL_VAR["goodsSummary"]["stocksocial"]["count"])?></span></a> 개
	</td>
</tr>
</table>