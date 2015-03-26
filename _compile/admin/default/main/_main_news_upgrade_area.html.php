<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/main/_main_news_upgrade_area.html 000000903 */ 
$TPL_mainNewsUpgradeList_1=empty($TPL_VAR["mainNewsUpgradeList"])||!is_array($TPL_VAR["mainNewsUpgradeList"])?0:count($TPL_VAR["mainNewsUpgradeList"]);?>
<ul class="pd5">
<?php if($TPL_VAR["mainNewsUpgradeList"]){?>
<?php if($TPL_mainNewsUpgradeList_1){foreach($TPL_VAR["mainNewsUpgradeList"] as $TPL_V1){?>
	<li style="position:relative;"><a href="<?php echo $TPL_V1["link"]["value"]?>" target="_blank"><?php echo getstrcut(strip_tags($TPL_V1["title"]["value"]), 27)?></a>
	<div class="date"><?php echo date('m.d',strtotime($TPL_V1["pubDate"]["value"]))?></div>
	</li>
<?php }}?>
<?php }else{?>
	<li style="position:relative;padding-top:20px;text-align:center;"><a href="#" target="_blank">업그레이드 내역이 없습니다.</a></li>
<?php }?>
</ul>