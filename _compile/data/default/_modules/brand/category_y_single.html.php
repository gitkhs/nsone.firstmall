<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/default/_modules/brand/category_y_single.html 000001359 */ 
$TPL_brand_1=empty($TPL_VAR["brand"])||!is_array($TPL_VAR["brand"])?0:count($TPL_VAR["brand"]);?>
<style>
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryDepth1		{position:relative; height:25px; padding-right:5px; text-align:right;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllContainer {position:relative; z-index:100}
</style>

<script>
setBrandAllBtnEvent("<?php echo $TPL_VAR["categoryNavigationKey"]?>","/common/brand_all_navigation?template_path=<?php echo $TPL_VAR["template_path"]?>&categoryNavigationKey=<?php echo $TPL_VAR["categoryNavigationKey"]?>&requesturi=<?php echo urlencode($_SERVER["REQUEST_URI"])?>");
</script>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<td class="categoryDepth1">
		<a href="#" class="categoryAllBtn"><img src="/data/skin/default/images/common/left_all_brand.gif" /></a>
		<div class="categoryAllContainer"></div>
	</td>
</tr>
<?php if($TPL_brand_1){foreach($TPL_VAR["brand"] as $TPL_V1){?>
<tr><td height="1" bgcolor="#e5e5e5"></td></tr>
<tr>
	<td class="categoryDepth1">
		<a href="/goods/brand?code=<?php echo $TPL_V1["category_code"]?>"><b><?php echo $TPL_V1["name"]?></b></a>
	</td>
</tr>
<?php }}?>
</table>