<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/main/_main_qna_summary.html 000001059 */ 
$TPL_mbqnaloop_1=empty($TPL_VAR["mbqnaloop"])||!is_array($TPL_VAR["mbqnaloop"])?0:count($TPL_VAR["mbqnaloop"]);?>
<!-- 1:1문의 -->
<table cellpadding="0" cellspacing="0" border="0" align="center" class="tb_summary_partition">
<?php if($TPL_VAR["mbqnaloop"]){?>
<?php if($TPL_mbqnaloop_1){foreach($TPL_VAR["mbqnaloop"] as $TPL_V1){?>
<?php if($TPL_V1["seq"]< 1){?>
	<tr>
		<td colspan="4">&nbsp;</td>
	</tr>
<?php }else{?>
	<tr>
		<td class="category llign" width="72px"><?php echo $TPL_V1["real_category"]?></td>
		<td class="llign subject"><?php echo $TPL_V1["main_subject"]?></td>
		<td class="redtxt rlign" width="35px"><?php if(!($TPL_V1["re_contents"])&&$TPL_V1["seq"]){?>미답변<?php }?></td>
		<td class="rlign" width="30px"><?php echo $TPL_V1["main_date"]?></td>
	</tr>
<?php }?>
<?php }}?>
<?php }else{?>
	<tr ><td colspan="4" > 등록된 게시글이 없습니다. </td><tr>
<?php }?>
</table>