<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/default/_modules/location/location_y_single.html 000001392 */ 
$TPL_location_1=empty($TPL_VAR["location"])||!is_array($TPL_VAR["location"])?0:count($TPL_VAR["location"]);?>
<style>
#<?php echo $TPL_VAR["locationNavigationKey"]?> .categoryDepth1		{position:relative; height:25px; padding-right:5px; text-align:right;}
#<?php echo $TPL_VAR["locationNavigationKey"]?> .categoryAllContainer {position:relative; z-index:100}
</style>

<script>
setLocationAllBtnEvent("<?php echo $TPL_VAR["locationNavigationKey"]?>","/common/location_all_navigation?template_path=<?php echo $TPL_VAR["template_path"]?>&locationNavigationKey=<?php echo $TPL_VAR["locationNavigationKey"]?>&requesturi=<?php echo urlencode($_SERVER["REQUEST_URI"])?>");
</script>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<td class="categoryDepth1">
		<a href="#" class="categoryAllBtn"><img src="/data/skin/default/images/common/left_all_location.gif" /></a>
		<div class="categoryAllContainer"></div>
	</td>
</tr>
<?php if($TPL_location_1){foreach($TPL_VAR["location"] as $TPL_V1){?>
<tr><td height="1" bgcolor="#e5e5e5"></td></tr>
<tr>
	<td class="categoryDepth1">
		<a href="/goods/location?code=<?php echo $TPL_V1["location_code"]?>"><b><?php echo $TPL_V1["name"]?></b></a>
	</td>
</tr>
<?php }}?>
</table>