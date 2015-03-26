<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/_modules/display/goods_display_mobile_list.html 000011105 */  $this->include_("snsLikeButton");
$TPL_orders_1=empty($TPL_VAR["orders"])||!is_array($TPL_VAR["orders"])?0:count($TPL_VAR["orders"]);
$TPL_displayTabsList_1=empty($TPL_VAR["displayTabsList"])||!is_array($TPL_VAR["displayTabsList"])?0:count($TPL_VAR["displayTabsList"]);?>
<style>
#<?php echo $TPL_VAR["display_key"]?> .goods_list {padding-top:10px; border-bottom:1px dashed gray;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list li.gl_item {padding-left:8px; border-top:1px solid #ddd;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list li.gl_item>a {display:block; *zoom:1; padding-top:10px; padding-bottom:10px;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list li.gl_item>a:after {content:""; clear:both; display:block;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list li.gl_item:first-child {border-top:none;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list li.gl_item:first-child a {padding-top:0px;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list span.gli_image {float:left; width:25%;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list span.gli_image img {width:100%;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list ol.gli_contents {float:left; width:70%; padding-left:10px; padding-top:10px;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list ol.gli_contents .goods_name {font-size:15px; font-weight:bold;}
#<?php echo $TPL_VAR["display_key"]?> .goods_list ol.gli_contents .goods_summary {display:block; padding-top:5px; font-size:12px; color:gray; }
#<?php echo $TPL_VAR["display_key"]?> .goods_list ol.gli_contents .goods_consumer_price {color:gray}
#<?php echo $TPL_VAR["display_key"]?> .goods_list ol.gli_contents .goods_price {font-weight:bold; font-size:14px; font-family:tahoma; color:#ff3300}
#<?php echo $TPL_VAR["display_key"]?> .goods_list ol.gli_contents .gli_goodsprice {display:block; padding-top:5px; padding-bottom:5px;}
#<?php echo $TPL_VAR["display_key"]?> .fb-like {width:60px;}
</style>
<?php if($TPL_VAR["perpage"]){?>
<div class="goods_list_top pdt10">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="left">
			<span class="list_summary">TOTAL <b><?php echo number_format($TPL_VAR["page"]["totalcount"])?></b> ITEMS</span>
		</td>
		<td align="right">
			<span class="sort_item">
<?php if($TPL_orders_1){$TPL_I1=-1;foreach($TPL_VAR["orders"] as $TPL_K1=>$TPL_V1){$TPL_I1++;?>
<?php if(in_array($TPL_K1,array('popular','low_price','newly'))){?>
<?php if($TPL_I1){?>
						&nbsp;|&nbsp;
<?php }?>
<?php if($TPL_K1==$TPL_VAR["sort"]){?>
							<a href="?sort=<?php echo $TPL_K1?><?php echo $TPL_VAR["sortUrlQuerystring"]?>"><b><?php echo $TPL_V1?></b></a>
<?php }else{?>
							<a href="?sort=<?php echo $TPL_K1?><?php echo $TPL_VAR["sortUrlQuerystring"]?>"><?php echo $TPL_V1?></a>
<?php }?>
<?php }?>
<?php }}?>
			</span>
		</td>
	</tr>
	</table>
</div>

<br style="line-height:10px;" />
<?php }?>

<?php if(count($TPL_VAR["displayTabsList"])> 1){?>
<ul class="displayTabContainer <?php echo $TPL_VAR["tab_design_type"]?>">
<?php if($TPL_displayTabsList_1){$TPL_I1=-1;foreach($TPL_VAR["displayTabsList"] as $TPL_V1){$TPL_I1++;?>
		<li <?php if($TPL_I1== 0){?>class="current"<?php }?> style="width:<?php echo  100/count($TPL_VAR["displayTabsList"])?>%"><?php echo $TPL_V1["tab_title"]?></li>
<?php }}?>
</ul>
<?php }?>

<?php if($TPL_displayTabsList_1){foreach($TPL_VAR["displayTabsList"] as $TPL_V1){?>
<div class="displayTabContentsContainer <?php if(count($TPL_VAR["displayTabsList"])> 1){?>displayTabContentsContainerBox<?php }?>">
<?php if($TPL_V1["contents_type"]=='text'){?>
	<div>
<?php if($TPL_VAR["mobileMode"]||$TPL_VAR["storemobileMode"]){?><?php echo $TPL_V1["tab_contents_mobile"]?><?php }else{?><?php echo $TPL_V1["tab_contents"]?><?php }?>
	</div>
<?php }else{?>
		<ul class="goods_list">
<?php if(is_array($TPL_R2=$TPL_V1["record"])&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
		<li class="gl_item">
			<a href="/goods/view?no=<?php echo $TPL_V2["goods_seq"]?>">
				<span class="gli_image goodsDisplayImageWrap" decoration="<?php echo $TPL_VAR["image_decorations"]?>" goodsInfo="<?php echo base64_encode(json_encode($TPL_V2))?>"><img src="<?php echo $TPL_V2["image"]?>" onerror="this.src='/data/skin/mobile_ver2_default/images/common/noimage.gif'" /></span>
				<ol class="gli_contents">
<?php if(is_array($TPL_R3=$TPL_VAR["info_settings"]["list"])&&!empty($TPL_R3)){foreach($TPL_R3 as $TPL_V3){?>
<?php if($TPL_V3->kind=='brand_title'&&$TPL_V2["brand_title"]){?>
						<li>
							<span <?php echo $TPL_V3->name_css?>>
<?php if($TPL_V3->wrapper){?><?php echo substr($TPL_V3->wrapper, 0, 1)?><?php }?><?php echo $TPL_V2["brand_title"]?><?php if($TPL_V3->wrapper){?><?php echo substr($TPL_V3->wrapper, 1, 1)?><?php }?>
							</span>
						</li>
<?php }?>

<?php if($TPL_V3->kind=='goods_name'){?>
						<li><span class="goods_name" <?php echo $TPL_V3->name_css?>><?php echo $TPL_V2["goods_name"]?></span> <?php if(is_array($TPL_R4=$TPL_V2["icons"])&&!empty($TPL_R4)){foreach($TPL_R4 as $TPL_V4){?><img src="/data/icon/goods/<?php echo $TPL_V4?>.gif" border="0"><?php }}?></li>
<?php }?>

<?php if($TPL_V3->kind=='summary'&&$TPL_V2["summary"]){?>
						<li><span class="goods_summary" <?php echo $TPL_V3->name_css?>><?php echo $TPL_V2["summary"]?></span></li>
<?php }?>

<?php if($TPL_V3->kind=='consumer_price'&&$TPL_V2["consumer_price"]>$TPL_V2["sale_price"]){?>
						<li>
							<span <?php echo $TPL_V3->name_css?>>
<?php if($TPL_V2["string_price"]){?>
								<?php echo $TPL_V2["string_price"]?>

<?php }else{?>
								<?php echo number_format($TPL_V2["consumer_price"])?>

<?php if($TPL_V3->postfix){?><?php echo $TPL_V3->postfix?><?php }?>
<?php }?>
							</span>
						</li>
<?php }?>

<?php if($TPL_V3->kind=='price'){?>
						<li class="gli_goodsprice">
							<span class="goods_price" <?php echo $TPL_V3->name_css?>>
<?php if($TPL_V2["string_price"]){?>
								<?php echo $TPL_V2["string_price"]?>

<?php }else{?>
								<?php echo number_format($TPL_V2["price"])?>

<?php if($TPL_V3->postfix){?><?php echo $TPL_V3->postfix?><?php }?>
<?php }?>
							</span>
						</li>
<?php }?>

<?php if($TPL_V3->kind=='sale_price'){?>
						<li>
							<span <?php echo $TPL_V3->name_css?>>
<?php if($TPL_V2["string_price"]){?>
									<?php echo $TPL_V2["string_price"]?>

<?php }else{?>
									<?php echo number_format($TPL_V2["sale_price"])?>

<?php if($TPL_V3->postfix){?><?php echo $TPL_V3->postfix?><?php }?>
<?php }?>
							</span>
						</li>
<?php }?>

<?php if($TPL_V3->kind=='fblike'&&$TPL_VAR["APP_USE"]=='f'&&$TPL_VAR["APP_LIKE_TYPE"]!='NO'){?>
						<li><?php echo snsLikeButton($TPL_V2["goods_seq"],$TPL_V3->fblike)?></li>
<?php }?>

<?php if($TPL_V3->kind=='status_icon'){?>
						<li>
<?php if($TPL_V3->status_icon_runout&&$TPL_V2["goods_status"]=='runout'){?>
							<img src="/data/icon/goods_status/<?php echo $TPL_VAR["goodsStatusImage"]["icon_runout"]?>" />
<?php }?>
<?php if($TPL_V3->status_icon_purchasing&&$TPL_V2["goods_status"]=='purchasing'){?>
							<img src="/data/icon/goods_status/<?php echo $TPL_VAR["goodsStatusImage"]["icon_purchasing"]?>" />
<?php }?>
<?php if($TPL_V3->status_icon_unsold&&$TPL_V2["goods_status"]=='unsold'){?>
							<img src="/data/icon/goods_status/<?php echo $TPL_VAR["goodsStatusImage"]["icon_unsold"]?>" />
<?php }?>
						</li>
<?php }?>

<?php if($TPL_V3->kind=='score'){?>
						<li>
							<font style="font-weight:bold; color:#fff; background-color:#ee6600; padding:0 3px; font-family:tahoma"><?php echo round($TPL_V2["review_sum"]/$TPL_V2["review_count"])?></font>
							<span class="orange"><?php echo str_repeat('★',round($TPL_V2["review_sum"]/$TPL_V2["review_count"]))?></span>
							<span class="gray"><?php echo str_repeat('★', 5-round($TPL_V2["review_sum"]/$TPL_V2["review_count"]))?></span>
							(<span class="red"><?php echo number_format($TPL_V2["review_count"])?></span>)
						</li>
<?php }?>

<?php if($TPL_V3->kind=='color'){?>
						<li>
<?php if(is_array($TPL_R4=($TPL_V2["colors"]))&&!empty($TPL_R4)){foreach($TPL_R4 as $TPL_V4){?>
							<span style="color:<?php echo $TPL_V4?>;">■</span>
<?php }}?>
						</li>
<?php }?>

<?php if($TPL_V3->kind=='count'&&$TPL_V2["eventEnd"]){?>
							<li class="soloEventTd<?php echo $TPL_V2["goods_seq"]?>" style="font-face:Dotum; font-size:11px;">
								<img src="/data/skin/mobile_ver2_default/images/common/icon_clock.gif" style="padding-bottom:2px;">남은시간 <span style="background-color:#c61515; color:#ffffff; padding:2px; font-weight:bold;"><span id="soloday<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>일 <span id="solohour<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>:<span id="solomin<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>:<span id="solosecond<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span></span>
							<script>
							$(function() {
								timeInterval<?php echo $TPL_V2["goods_seq"]?> = setInterval(function(){
									var time<?php echo $TPL_V2["goods_seq"]?> = showClockTime('text', '<?php echo $TPL_V2["eventEnd"]["year"]?>', '<?php echo $TPL_V2["eventEnd"]["month"]?>', '<?php echo $TPL_V2["eventEnd"]["day"]?>', '<?php echo $TPL_V2["eventEnd"]["hour"]?>', '<?php echo $TPL_V2["eventEnd"]["min"]?>', '<?php echo $TPL_V2["eventEnd"]["second"]?>', 'soloday<?php echo $TPL_V2["goods_seq"]?>', 'solohour<?php echo $TPL_V2["goods_seq"]?>', 'solomin<?php echo $TPL_V2["goods_seq"]?>', 'solosecond<?php echo $TPL_V2["goods_seq"]?>', '<?php echo $TPL_V2["goods_seq"]?>');
									if(time<?php echo $TPL_V2["goods_seq"]?> == 0){
										clearInterval(timeInterval<?php echo $TPL_V2["goods_seq"]?>);
										$("..soloEventTd<?php echo $TPL_VAR["displayGoods"]["goods_seq"]?>").html("단독 이벤트 종료");
									}
								},1000);
							});
							</script>
							</li>
<?php }?>

<?php if($TPL_V3->kind=='event_text'){?>
						<li>
							<span <?php echo $TPL_V3->name_css?>>
<?php if(is_numeric($TPL_V2["event_text"])){?>
									<?php echo number_format($TPL_V2["event_text"])?>

<?php if($TPL_V3->postfix){?><?php echo $TPL_V3->postfix?><?php }?>
<?php }else{?>
									<?php echo $TPL_V2["event_text"]?>

<?php }?>
							</span>
						</li>
<?php }?>
<?php }}?>
<?php if($TPL_VAR["is_bigdata_display"]=='y'){?>
						<li>
							<span>
								<a href="/bigdata/catalog?no=<?php echo $TPL_V1["goods_seq"]?>"><u style="font-size:11px;color:#0094e1;">다른 사람은 뭘살까?</u></a>
							</span>
						</li>
<?php }?>
				</ol>
			</a>
		</li>
<?php }}?>
		</ul>
<?php }?>
</div>
<?php }}?>


<?php if($TPL_VAR["perpage"]){?>
<?php $this->print_("paging",$TPL_SCP,1);?>

<?php }?>