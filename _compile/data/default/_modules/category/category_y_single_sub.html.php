<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/default/_modules/category/category_y_single_sub.html 000003130 */ 
$TPL_category_1=empty($TPL_VAR["category"])||!is_array($TPL_VAR["category"])?0:count($TPL_VAR["category"]);?>
<style>
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryDepth1		{position:relative; height:25px; padding-right:5px; text-align:right;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categorySub {position:absolute; padding:15px 17px; z-index:10; display:none; top:0px; left:100%; border-collapse:collapse; border:1px solid black; background-color:white; text-align:left;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categorySubItemsTitle {}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categorySubItems {margin-top:10px; min-width:120px;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categorySubItems li.categorySubDepth {padding-left:15px; padding-top:3px; padding-bottom:3px; background:url("/data/skin/default/images/common/bullet_dot.gif") no-repeat left center;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categorySub .categorySubBar {padding-left:10px; border-right:1px solid #eee;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryDepth1:hover .categorySub {display:block;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllContainer {position:relative; z-index:100}
</style>

<script>
setCategoryAllBtnEvent("<?php echo $TPL_VAR["categoryNavigationKey"]?>","/common/category_all_navigation?template_path=<?php echo $TPL_VAR["template_path"]?>&categoryNavigationKey=<?php echo $TPL_VAR["categoryNavigationKey"]?>&requesturi=<?php echo urlencode($_SERVER["REQUEST_URI"])?>");
</script>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<td class="categoryDepth1">
		<a href="#" class="categoryAllBtn"><img src="/data/skin/default/images/common/left_all_category.gif" /></a>
		<div class="categoryAllContainer"></div>
	</td>
</tr>
<?php if($TPL_category_1){foreach($TPL_VAR["category"] as $TPL_V1){?>
<tr><td height="1" bgcolor="#e5e5e5"></td></tr>
<tr>
	<td class="categoryDepth1">
		<div class="relative">
		<a href="/goods/catalog?code=<?php echo $TPL_V1["category_code"]?>"><b><?php echo $TPL_V1["name"]?></b></a>
<?php if($TPL_V1["childs"]){?>
		<div class="categorySub">
			<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top">
					<div class="categorySubItemsTitle"><img src="/data/skin/default/images/common/stit_items.gif" /></div>
					<ul class="categorySubItems" cellpadding="0" cellspacing="0">
<?php if(is_array($TPL_R2=$TPL_V1["childs"])&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
						<li class="categorySubDepth"><a href="/goods/catalog?code=<?php echo $TPL_V2["category_code"]?>"><?php echo $TPL_V2["name"]?></a></li>
<?php }}?>
					</ul>
				</td>
<?php if($TPL_V1["node_banner"]){?>
				<td class="categorySubBar"></td>
				<td class="pdl10" valign="top">
					<?php echo $TPL_V1["node_banner"]?>

				</td>
<?php }?>
			</tr>
			</table>
		</div>
<?php }?>
		</div>
	</td>
</tr>
<?php }}?>
</table>