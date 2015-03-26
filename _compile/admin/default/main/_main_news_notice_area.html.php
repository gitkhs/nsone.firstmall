<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/main/_main_news_notice_area.html 000000664 */ 
$TPL_mainNewsNoticeList_1=empty($TPL_VAR["mainNewsNoticeList"])||!is_array($TPL_VAR["mainNewsNoticeList"])?0:count($TPL_VAR["mainNewsNoticeList"]);?>
<ul class="pd5">
<?php if($TPL_mainNewsNoticeList_1){foreach($TPL_VAR["mainNewsNoticeList"] as $TPL_V1){?>
	<li style="position:relative;"><a href="<?php echo $TPL_V1["link"]["value"]?>" target="_blank"><?php echo getstrcut($TPL_V1["title"]["value"], 27)?></a> 
	<div class="date"><?php echo date('m.d',strtotime($TPL_V1["pubDate"]["value"]))?></div></li>
<?php }}?>
</ul>