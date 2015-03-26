<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/_modules/common/paging.html 000003808 */ ?>
<?php if($TPL_VAR["mobileAjaxCall"]){?>
	<table align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<div class="paging_navigation">
<?php if($TPL_VAR["page"]["totalpage"]> 1&&$TPL_VAR["page"]["nowpage"]== 1){?><a href="../<?php echo uri_string()?>?page=<?php echo $TPL_VAR["page"]["totalpage"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="prev" <?php if($TPL_VAR["mobileAjaxCall"]){?>mobileAjaxCall="<?php echo $TPL_VAR["mobileAjaxCall"]?>"<?php }?>>◀ 이전</a>
<?php }elseif($TPL_VAR["page"]["nowpage"]> 1){?><a href="../<?php echo uri_string()?>?page=<?php echo ($TPL_VAR["page"]["nowpage"]- 1)?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="prev" <?php if($TPL_VAR["mobileAjaxCall"]){?>mobileAjaxCall="<?php echo $TPL_VAR["mobileAjaxCall"]?>"<?php }?>>◀ 이전</a><?php }?>
				
				<?php echo $TPL_VAR["page"]["nowpage"]?>

<?php if($TPL_VAR["page"]["totalpage"]> 1){?>
				/
				<a href="../<?php echo uri_string()?>?page=<?php echo $TPL_VAR["page"]["totalpage"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="on" <?php if($TPL_VAR["mobileAjaxCall"]){?>mobileAjaxCall="<?php echo $TPL_VAR["mobileAjaxCall"]?>"<?php }?>><?php echo $TPL_VAR["page"]["totalpage"]?></a>
<?php }?>
				
<?php if($TPL_VAR["page"]["totalpage"]> 1&&$TPL_VAR["page"]["nowpage"]==$TPL_VAR["page"]["totalpage"]){?><a href="../<?php echo uri_string()?>?page=1&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="next" <?php if($TPL_VAR["mobileAjaxCall"]){?>mobileAjaxCall="<?php echo $TPL_VAR["mobileAjaxCall"]?>"<?php }?>>다음 ▶</a>
<?php }elseif($TPL_VAR["page"]["nowpage"]<$TPL_VAR["page"]["totalpage"]){?><a href="../<?php echo uri_string()?>?page=<?php echo ($TPL_VAR["page"]["nowpage"]+ 1)?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="next" <?php if($TPL_VAR["mobileAjaxCall"]){?>mobileAjaxCall="<?php echo $TPL_VAR["mobileAjaxCall"]?>"<?php }?>>다음 ▶</a><?php }?>
			</div>
		</td>
	</tr>
	</table>
<?php }else{?>
	<table align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<div class="paging_navigation" queryStr="<?php echo $TPL_VAR["page"]["querystring"]?>" total="<?php echo $TPL_VAR["page"]["totalpage"]?>" now="<?php echo $TPL_VAR["page"]["nowpage"]?>">
<?php if($TPL_VAR["page"]["totalpage"]> 1&&$TPL_VAR["page"]["nowpage"]== 1){?><a href="../<?php echo uri_string()?>?page=<?php echo $TPL_VAR["page"]["totalpage"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="prev">◀ 이전</a>
<?php }elseif($TPL_VAR["page"]["nowpage"]> 1){?><a href="../<?php echo uri_string()?>?page=<?php echo ($TPL_VAR["page"]["nowpage"]- 1)?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="prev">◀ 이전</a><?php }?>
				
				<?php echo $TPL_VAR["page"]["nowpage"]?>

<?php if($TPL_VAR["page"]["totalpage"]> 1){?>
				/
				<a href="../<?php echo uri_string()?>?page=<?php echo $TPL_VAR["page"]["totalpage"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="on" <?php if($TPL_VAR["mobileAjaxCall"]){?>mobileAjaxCall="<?php echo $TPL_VAR["mobileAjaxCall"]?>"<?php }?>><?php echo $TPL_VAR["page"]["totalpage"]?></a>
<?php }?>
<?php if($TPL_VAR["page"]["totalpage"]> 1&&$TPL_VAR["page"]["nowpage"]==$TPL_VAR["page"]["totalpage"]){?><a href="../<?php echo uri_string()?>?page=1&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="next">다음 ▶</a>
<?php }elseif($TPL_VAR["page"]["nowpage"]<$TPL_VAR["page"]["totalpage"]){?><a href="../<?php echo uri_string()?>?page=<?php echo ($TPL_VAR["page"]["nowpage"]+ 1)?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="next">다음 ▶</a><?php }?>
			</div>
		</td>
	</tr>
	</table>
<?php }?>