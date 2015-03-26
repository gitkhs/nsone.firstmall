<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/default/_modules/category/all_navigation.html 000004074 */ ?>
<style>
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAll {display:none; z-index:100; position:absolute; width:<?php echo $TPL_VAR["layout_config"]["width"]?>px; background-color:#fff}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllBorder {border:2px solid #000;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllTable {width:100%; table-layout:fixed;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth1 {height:32px; background-color:#b2bdbf; overflow: hidden; white-space:nowrap; text-overflow:ellipsis; -o-text-overflow:ellipsis;-ms-text-overflow:ellipsis;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth1 a {font-weight:bold; color:#fff; font-size:12px; font-family:dotum;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth2 {height:30px; padding-left:10px; line-height:30px; background-color:#f7f7f7; text-align:left; border-bottom:1px solid #e8e8e8; overflow: hidden; white-space:nowrap; text-overflow:ellipsis; -o-text-overflow:ellipsis;-ms-text-overflow:ellipsis;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth2 a {font-weight:bold; color:#000; font-size:12px; font-family:dotum;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth3 {padding:4px 0 4px 10px;background-color:#fff; text-align:left;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth3 a {font-weight:normal; color:#4f4f4f; font-size:12px; font-family:dotum;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllDepth3Last {border-bottom:1px solid #e8e8e8;}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllTable>tbody>tr>th {border-left:1px solid #93a3a3}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllTable>tbody>tr>td {border-left:1px solid #e8e8e8}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllTable>tbody>tr>th:first-child,
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllTable>tbody>tr>td:first-child {border-left:0px}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllClose {position:absolute; right:0px; top:100%; cursor:pointer}
#<?php echo $TPL_VAR["categoryNavigationKey"]?> .categoryAllBanner {margin-top:-1px; padding:15px; border-top:1px solid #e0e0e0; background-color:#f7f7f7; text-align:center;}
</style>

<div class="categoryAll">
	<div class="categoryAllBorder">
		<table class="categoryAllTable" border="0" cellpadding="0" cellspacing="0">
<?php if(is_array($TPL_R1=array_chunk($TPL_VAR["categoryData"], 10,true))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
		<tr>
<?php if(is_array($TPL_R2=$TPL_V1)&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
			<th class="categoryAllDepth1"><a href="/goods/catalog?code=<?php echo $TPL_V2["category_code"]?>"><b><?php echo $TPL_V2["title"]?></b></a></th>
<?php }}?>
		</tr>
		<tr>
<?php if(is_array($TPL_R2=$TPL_V1)&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
			<td valign="top">
<?php if(is_array($TPL_R3=$TPL_V2["childs"])&&!empty($TPL_R3)){foreach($TPL_R3 as $TPL_V3){?>
					<div class="categoryAllDepth2"><a href="/goods/catalog?code=<?php echo $TPL_V3["category_code"]?>"><?php echo $TPL_V3["title"]?></a></div>
<?php if(is_array($TPL_R4=$TPL_V3["childs"])&&!empty($TPL_R4)){$TPL_I4=-1;foreach($TPL_R4 as $TPL_V4){$TPL_I4++;?>
						<div class="categoryAllDepth3 <?php if($TPL_I4==count($TPL_V3["childs"])- 1){?>categoryAllDepth3Last<?php }?>"><a href="/goods/catalog?code=<?php echo $TPL_V4["category_code"]?>"><?php echo $TPL_V4["title"]?></a></div>
<?php }}?>
<?php }}?>
			</td>
<?php }}?>
		</tr>		
<?php }}?>
		</table>
<?php if($TPL_VAR["category_gnb_banner"]){?>
		<div class="categoryAllBanner">
			<?php echo $TPL_VAR["category_gnb_banner"]?>

		</div>
<?php }?>
	</div>	
	<div class="categoryAllClose"><img src="/data/skin/default/images/common/btn_close_full.gif" title="전체 카테고리 닫기" /></div>
</div>