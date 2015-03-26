<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/main/_main_count_bar.html 000005572 */ ?>
<?php if(!strpos($_SERVER["HTTP_USER_AGENT"],'Firefox')){?>
<style type="text/css">
.summary-table .count_num span {top:2px;}
</style>
<?php }?>
<table class="darkgray summary-table" cellpadding="0" cellspacing="0" border="0">
<tr>
	<!--# 오늘 #--->
	<td>
		<img src="/admin/skin/default/images/main/today_tt.gif" />
	</td>
	<!-- 결제건수 -->
	<td class="count_tit">
		<img src="/admin/skin/default/images/main/admin_mtit01.gif" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["todayCount"]["total_cnt_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?><img src="/admin/skin/default/images/main/comma.gif" /><?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="1px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;padding-right:10px;">
		<img src="/admin/skin/default/images/main/admin_mtit02.gif" />
	</td>
	<!-- 결제금액 -->
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["todayCount"]["total_price_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?>
			<img src="/admin/skin/default/images/main/comma.gif" />
<?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="1px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;">
		<img src="/admin/skin/default/images/main/admin_mtit03.gif" />
	</td>
	<!-- 가입 -->
	<td class="count_tit">
		<img src="/admin/skin/default/images/main/admin_mtit04.gif" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["todayCount"]["new_member_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?><img src="/admin/skin/default/images/main/comma.gif" /><?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="2px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;">
		<img src="/admin/skin/default/images/main/admin_mtit05.gif" />
	</td>
	<!-- 방문 -->
	<td class="count_tit">
		<img src="/admin/skin/default/images/main/admin_mtit06.gif" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["todayCount"]["visit_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?><img src="/admin/skin/default/images/main/comma.gif" /><?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="2px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;padding-right:6px;">
		<img src="/admin/skin/default/images/main/admin_mtit05.gif" />
	</td>

	<td style="padding-right:9px;">
		<img src="/admin/skin/default/images/main/line_15.gif" />
	</td>	
	<!--# 누적 #--->
	<td>
		<img src="/admin/skin/default/images/main/next_tt.gif" />
	</td>
	<!-- 회원 -->
	<td class="count_tit">
		<img src="/admin/skin/default/images/main/admin_nt01.gif" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["totalCount"]["member_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?><img src="/admin/skin/default/images/main/comma.gif" /><?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="2px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;">
		<img src="/admin/skin/default/images/main/admin_mtit05.gif" />
	</td>
	<!-- 적립금 -->
	<td class="count_tit">
		<img src="/admin/skin/default/images/main/admin_nt02.gif" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["totalCount"]["emoney_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?><img src="/admin/skin/default/images/main/comma.gif" /><?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="2px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;">
		<img src="/admin/skin/default/images/main/admin_mtit03.gif" />
	</td>
	<!-- 포인트 -->
	<td class="count_tit">
		<img src="/admin/skin/default/images/main/admin_nt03.gif" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="0" border="0" style="padding-top:7px;">
		<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["totalCount"]["point_arr"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
			<td>
<?php if($TPL_V1["value"]==','){?><img src="/admin/skin/default/images/main/comma.gif" /><?php }else{?>
			<td class="count_num"><span><?php echo $TPL_V1["value"]?></span>
<?php }?>
			</td>
			<td width="2px"></td>
<?php }}?>
		</tr>
		</table>
	</td>
	<td style="padding-left:5px;">
		<img src="/admin/skin/default/images/main/admin_nt04.gif" />
	</td>
</tr>
</table>