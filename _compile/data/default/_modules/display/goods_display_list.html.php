<?php /* Template_ 2.2.6 2014/08/14 14:52:21 /www/release/seller/data/skin/default/_modules/display/goods_display_list.html 000017772 */  $this->include_("showGoodsSearchForm","snsLikeButton");
$TPL_orders_1=empty($TPL_VAR["orders"])||!is_array($TPL_VAR["orders"])?0:count($TPL_VAR["orders"]);
$TPL_displayTabsList_1=empty($TPL_VAR["displayTabsList"])||!is_array($TPL_VAR["displayTabsList"])?0:count($TPL_VAR["displayTabsList"]);?>
<div><font face="arial black, 돋움" size="2"><b><?php echo $TPL_VAR["title"]?></b></font></div>

<?php if($TPL_VAR["perpage"]){?>
<br style="line-height:10px;" />

<!--[ 상품 검색 폼 ]-->
<div style="padding:0 0 20px 0">
<?php echo showGoodsSearchForm($TPL_VAR["sc"])?>

</div>

<div class="goods_list_summary">전체 상품 <b><?php echo number_format($TPL_VAR["page"]["totalcount"])?></b> 개</div>
<div class="goods_list_top">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="left">
<?php if($TPL_VAR["sort"]){?>
			<span class="sort_item">
<?php if($TPL_orders_1){$TPL_I1=-1;foreach($TPL_VAR["orders"] as $TPL_K1=>$TPL_V1){$TPL_I1++;?>
<?php if($TPL_I1){?>
					&nbsp;&nbsp;|&nbsp;&nbsp;
<?php }?>
<?php if($TPL_K1==$TPL_VAR["sort"]){?>
						<a href="?sort=<?php echo $TPL_K1?>&<?php echo get_args_list(array('page','sort'))?>"><b><?php echo $TPL_V1?></b></a>
<?php }else{?>
						<a href="?sort=<?php echo $TPL_K1?>&<?php echo get_args_list(array('page','sort'))?>"><?php echo $TPL_V1?></a>
<?php }?>
<?php }}?>
			</span>
<?php }?>
		</td>
		<td align="right">
			<select name="perpage" onchange="document.location.href='?perpage='+this.value+'&<?php echo get_args_list(array('page','perpage'))?>'">
				<option value="<?php echo $TPL_VAR["perpage_min"]?>" <?php if($_GET["perpage"]==$TPL_VAR["perpage_min"]){?>selected<?php }?>><?php echo number_format($TPL_VAR["perpage_min"])?>개씩 보기</option>
				<option value="<?php echo $TPL_VAR["perpage_min"]* 2?>" <?php if($_GET["perpage"]==$TPL_VAR["perpage_min"]* 2){?>selected<?php }?>><?php echo number_format($TPL_VAR["perpage_min"]* 2)?>개씩 보기</option>
				<option value="<?php echo $TPL_VAR["perpage_min"]* 5?>" <?php if($_GET["perpage"]==$TPL_VAR["perpage_min"]* 5){?>selected<?php }?>><?php echo number_format($TPL_VAR["perpage_min"]* 5)?>개씩 보기</option>
				<option value="<?php echo $TPL_VAR["perpage_min"]* 10?>" <?php if($_GET["perpage"]==$TPL_VAR["perpage_min"]* 10){?>selected<?php }?>><?php echo number_format($TPL_VAR["perpage_min"]* 10)?>개씩 보기</option>
			</select>
			
			<ul class="goods_list_style">
				<li <?php if($TPL_VAR["list_style"]=='lattice_a'){?>class="lattice_a_on"<?php }else{?>class="lattice_a"<?php }?>><a href="?display_style=lattice_a&<?php echo get_args_list(array('page','display_style'))?>" title="격자형A"></a></li>
				<li <?php if($TPL_VAR["list_style"]=='lattice_b'){?>class="lattice_b_on"<?php }else{?>class="lattice_b"<?php }?>><a href="?display_style=lattice_b&<?php echo get_args_list(array('page','display_style'))?>" title="격자형B"></a></li>
				<li <?php if($TPL_VAR["list_style"]=='list'){?>class="list_on"<?php }else{?>class="list"<?php }?>><a href="?display_style=list&<?php echo get_args_list(array('page','display_style'))?>" title="리스트형"></a></li>
			</ul>
		</td>
	</tr>
	</table>
</div>

<br style="line-height:10px;" />
<?php }?>

<?php if(count($TPL_VAR["displayTabsList"])> 1){?>
<ul class="displayTabContainer <?php echo $TPL_VAR["tab_design_type"]?>">
<?php if($TPL_displayTabsList_1){$TPL_I1=-1;foreach($TPL_VAR["displayTabsList"] as $TPL_V1){$TPL_I1++;?>
		<li <?php if($TPL_I1== 0){?>class="current"<?php }?>><?php echo $TPL_V1["tab_title"]?></li>
<?php }}?>
</ul>
<?php }?>

<?php if($TPL_displayTabsList_1){foreach($TPL_VAR["displayTabsList"] as $TPL_V1){?>
<table class="displayTabContentsContainer <?php if(count($TPL_VAR["displayTabsList"])> 1){?>displayTabContentsContainerBox<?php }?>" width="100%" cellpadding="0" cellspacing="0" border="0">
<?php if($TPL_V1["contents_type"]=='text'){?>
	<tr>
		<td>
<?php if($TPL_VAR["mobileMode"]||$TPL_VAR["storemobileMode"]){?><?php echo $TPL_V1["tab_contents_mobile"]?><?php }else{?><?php echo $TPL_V1["tab_contents"]?><?php }?>
		</td>
	</tr>
<?php }else{?>
<?php if(is_array($TPL_R2=$TPL_V1["record"])&&!empty($TPL_R2)){$TPL_I2=-1;foreach($TPL_R2 as $TPL_V2){$TPL_I2++;?>
<?php if($TPL_I2){?><tr><td height="10"></td></tr><tr><td height="10" style="border-top:1px dashed #ddd"></td></tr><?php }?>
		<tr>
			<td>
				<table class="goodsDisplayItemWrap" width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td width="<?php echo $TPL_VAR["goodsImageSize"]["width"]?>" height="<?php echo $TPL_VAR["goodsImageSize"]["height"]?>"><span class="goodsDisplayImageWrap" decoration="<?php echo $TPL_VAR["image_decorations"]?>" goodsInfo="<?php echo base64_encode(json_encode($TPL_V2))?>"><a href="/goods/view?no=<?php echo $TPL_V2["goods_seq"]?>" target="<?php echo $TPL_VAR["target"]?>"><?php if($TPL_V2["image_size"]&&$TPL_V2["image_size"][ 0]/$TPL_V2["image_size"][ 1]<$TPL_VAR["goodsImageSize"]["width"]/$TPL_VAR["goodsImageSize"]["height"]){?><img src="<?php echo $TPL_V2["image"]?>" height="<?php echo $TPL_VAR["goodsImageSize"]["height"]?>" onerror="this.src='/data/skin/default/images/common/noimage.gif';this.style.width='<?php echo $TPL_VAR["goodsImageSize"]["width"]?>px';" /><?php }else{?><img src="<?php echo $TPL_V2["image"]?>" width="<?php echo $TPL_VAR["goodsImageSize"]["width"]?>" onerror="this.src='/data/skin/default/images/common/noimage.gif';this.style.height='<?php echo $TPL_VAR["goodsImageSize"]["height"]?>px';" /><?php }?></a></span></td>
					<td width="30"></td>
					<td align="left">
<?php if($TPL_VAR["info_settings"]["data"]["icon"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["icon"]->name_css?>>
<?php if(is_array($TPL_R3=$TPL_V2["icons"])&&!empty($TPL_R3)){foreach($TPL_R3 as $TPL_V3){?>
								<img src="/data/icon/goods/<?php echo $TPL_V3?>.gif" border="0">
<?php }}?>
<?php if($TPL_VAR["info_settings"]["data"]["icon"]>$TPL_VAR["list_icon_cpn"]&& 0){?>
								<img src="/data/icon/goods_status/icon_list_cpn.gif" />
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["icon"]>$TPL_VAR["list_icon_freedlv"]&& 0){?>
								<img src="/data/icon/goods_status/icon_list_freedlv.gif" />
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["icon"]>$TPL_VAR["list_icon_video"]&&$TPL_V2["videousetotal"]){?>
								<img src="/data/icon/goods_status/icon_list_video.gif" />
<?php }?>
							</div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["goods_name"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["goods_name"]->name_css?>><a href="/goods/view?no=<?php echo $TPL_V2["goods_seq"]?>" target="<?php echo $TPL_VAR["target"]?>"><?php echo $TPL_V2["goods_name"]?></a></div>
<?php }?>

<?php if($TPL_VAR["info_settings"]["data"]["provider_name"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["provider_name"]->name_css?>><a href="/mshop/?m=<?php echo $TPL_V2["provider_seq"]?>" target="<?php echo $TPL_VAR["target"]?>"><?php echo $TPL_V2["provider_name"]?></a></div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["summary"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["summary"]->name_css?>><?php echo $TPL_V2["summary"]?></div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["score"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["score"]->name_css?> class="pdt10">
								<font style="font-weight:bold; color:#fff; background-color:#ee6600; padding:0 3px; font-family:tahoma"><?php echo round($TPL_VAR["goods"]["review_sum"]/$TPL_VAR["goods"]["review_count"])?></font>
								<span class="orange"><?php echo str_repeat('★',round($TPL_VAR["goods"]["review_sum"]/$TPL_VAR["goods"]["review_count"]))?></span>
								<span class="gray"><?php echo str_repeat('★', 5-round($TPL_VAR["goods"]["review_sum"]/$TPL_VAR["goods"]["review_count"]))?></span>
								상품평 (<span class="red"><?php echo number_format($TPL_VAR["goods"]["review_count"])?></span>)
							</div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["color"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["coror"]->name_css?> class="pdt10">
<?php if(is_array($TPL_R3=($TPL_V2["colors"]))&&!empty($TPL_R3)){foreach($TPL_R3 as $TPL_V3){?>
								<span style="color:<?php echo $TPL_V3?>;">■</span>
<?php }}?>
							</div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["event_text"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["event_text"]->name_css?> class="pdt10">
								<span <?php echo $TPL_VAR["info_settings"]["data"]["event_text"]->name_css?>>
<?php if(is_numeric($TPL_V2["event_text"])){?>
											<?php echo number_format($TPL_V2["event_text"])?>

<?php if($TPL_VAR["info_settings"]["data"]["event_text"]->postfix){?><?php echo $TPL_VAR["info_settings"]["data"]["event_text"]->postfix?><?php }?>
<?php }else{?>
											<?php echo $TPL_V2["event_text"]?>

<?php }?>
									</span>
							</div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["fblike"]&&$TPL_VAR["APP_USE"]=='f'&&$TPL_VAR["APP_LIKE_TYPE"]!='NO'){?>
							<div class="pdt10">
								<?php echo snsLikeButton($TPL_V2["goods_seq"],$TPL_VAR["info_settings"]["data"]["fblike"]->fblike)?>

							</div>
<?php }?>
							
<?php if($TPL_VAR["info_settings"]["data"]["status_icon"]){?>
							<div <?php echo $TPL_VAR["info_settings"]["data"]["status_icon"]->name_css?> class="pdt10">
<?php if($TPL_VAR["info_settings"]["data"]["status_icon"]->status_icon_runout&&$TPL_V2["goods_status"]=='runout'){?>
									<img src="/data/icon/goods_status/icon_list_soldout.gif" />
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["status_icon"]->status_icon_purchasing&&$TPL_V2["goods_status"]=='purchasing'){?>
									<img src="/data/icon/goods_status/icon_list_warehousing.gif" />
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["status_icon"]->status_icon_unsold&&$TPL_V2["goods_status"]=='unsold'){?>
									<img src="/data/icon/goods_status/icon_list_stop.gif" />
<?php }?>
							</div>
<?php }?>
									
					</td>
					<td width="150">
						<table border="0" align="center">
<?php if($TPL_VAR["info_settings"]["data"]["consumer_price"]&&$TPL_V2["consumer_price"]>$TPL_V2["price"]){?>
						<tr>
							<td align="right" class="pdr5">
								<span class="desc">정가</span>
							</td>
							<td align="right">
								<span <?php echo $TPL_VAR["info_settings"]["data"]["consumer_price"]->name_css?>>
									<?php echo number_format($TPL_V2["consumer_price"])?>

<?php if($TPL_VAR["info_settings"]["data"]["consumer_price"]->postfix){?><?php echo $TPL_VAR["info_settings"]["data"]["consumer_price"]->postfix?><?php }?>
								</span>
							</td>
						</tr>
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["price"]){?>
						<tr>
							<td align="right" class="pdr5">
								<span class="desc">판매가</span>
							</td>
							<td align="right">
								<span <?php echo $TPL_VAR["info_settings"]["data"]["price"]->name_css?>>
<?php if($TPL_V2["string_price"]){?>
										<?php echo $TPL_V2["string_price"]?>

<?php }else{?>
										<?php echo number_format($TPL_V2["price"])?>

<?php if($TPL_VAR["info_settings"]["data"]["price"]->postfix){?><?php echo $TPL_VAR["info_settings"]["data"]["price"]->postfix?><?php }?>
<?php }?>
								</span>
							</td>
						</tr>
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["sale_price"]){?>
						<tr>
							<td align="right" class="pdr5">
								<span class="desc">최종혜택가</span>
							</td>
							<td align="right">
								<span <?php echo $TPL_VAR["info_settings"]["data"]["sale_price"]->name_css?>>
<?php if($TPL_V1["string_price"]){?>
										<?php echo $TPL_V2["string_price"]?>

<?php }else{?>
										<?php echo number_format($TPL_V2["sale_price"])?>

<?php if($TPL_VAR["info_settings"]["data"]["sale_price"]->postfix){?><?php echo $TPL_VAR["info_settings"]["data"]["sale_price"]->postfix?><?php }?>
<?php }?>
								</span>
							</td>
						</tr>
<?php }?>

<?php if($TPL_VAR["info_settings"]["data"]["count"]&&$TPL_V2["eventEnd"]&&($TPL_VAR["info_settings"]["data"]["time_count"]||$TPL_VAR["info_settings"]["data"]["buy_count"])){?>
							<div class="pdt10">
							<table width="100%" height="42" cellspacing="0" cellpadding="0" style="border:1px solid #e9e9e9;" bgcolor="#f8f8f8" align="center">
<?php if($TPL_VAR["info_settings"]["data"]["time_count"]){?>
<?php if($TPL_V2["eventEnd"]){?>
							<tr ><td height="5"></td></tr>
							<tr >
								<td class="soloEventTd_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>" align="<?php echo $TPL_VAR["text_align"]?>" style="font-face:Dotum; font-size:11px;"><img src="/data/skin/default/images/common/icon_clock.gif" style="padding-bottom:2px;">남은시간 <span style="background-color:#c61515; color:#ffffff; padding:2px; font-weight:bold;"><span id="soloday_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>일 <span id="solohour_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>:<span id="solomin_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>:<span id="solosecond_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span></span></td>
							</tr>
							<script>
							$(function() {
								timeInterval_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?> = setInterval(function(){
									var time_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?> = showClockTime('text', '<?php echo $TPL_V2["eventEnd"]["year"]?>', '<?php echo $TPL_V2["eventEnd"]["month"]?>', '<?php echo $TPL_V2["eventEnd"]["day"]?>', '<?php echo $TPL_V2["eventEnd"]["hour"]?>', '<?php echo $TPL_V2["eventEnd"]["min"]?>', '<?php echo $TPL_V2["eventEnd"]["second"]?>', 'soloday_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>', 'solohour_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>', 'solomin_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>', 'solosecond_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>', '_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>');
									if(time_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?> == 0){
										clearInterval(timeInterval_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>);
										$(".soloEventTd_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_V2["goods_seq"]?>").html("단독 이벤트 종료");
									}
								},1000);
							});
							</script>
<?php }?>
<?php }?>
<?php if($TPL_VAR["info_settings"]["data"]["buy_count"]){?>
							<tr ><td height="6"></td></tr>
							<tr>
								<td align="<?php echo $TPL_VAR["text_align"]?>" style="font-face:Dotum; font-size:11px;">현재 <font color="#c61515"><u><b><?php echo number_format($TPL_V2["event_order_cnt"])?></b></u></font>개 구매</td>
							</tr>
<?php }?>
							</td></tr>
							<tr ><td height="5"></td></tr>
							</table>
							</div>
<?php }?>	
										
						</table>
					</td>
					<td width="120" align="center">
						<table border="0" align="center">
<?php if($TPL_V2["reserve"]> 0){?>
								<tr><td align="right"><img src="/data/skin/default/images/icon/icon_ord_emn.gif" /></td><td><?php echo number_format($TPL_V2["reserve"])?>원</td></tr>
<?php }?>
<?php if($TPL_VAR["isplusfreenot"]&&$TPL_VAR["isplusfreenot"]["ispoint"]&&$TPL_V2["point"]> 0){?>
								<tr><td align="right"><img src="/data/skin/default/images/icon/icon_ord_point.gif" /> <?php echo number_format($TPL_V2["point"])?>P</td></tr>
<?php }?>
						</table>
					</td>
				</tr>
				</table>
			</td>
		</tr>
<?php }}?>
		<tr><td height="10"></td></tr>
<?php }?>
</table>
<?php }}?>

<?php if($TPL_VAR["perpage"]){?>
<div style="height:30px"></div>

<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td>
		<div class="paging_navigation">
<?php if($TPL_VAR["page"]["first"]){?><a href="?page=<?php echo $TPL_VAR["page"]["first"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="first">◀ 처음</a><?php }?>
<?php if($TPL_VAR["page"]["prev"]){?><a href="?page=<?php echo $TPL_VAR["page"]["prev"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="prev">◀ 이전</a><?php }?>
<?php if(is_array($TPL_R1=$TPL_VAR["page"]["page"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
<?php if($TPL_VAR["page"]["nowpage"]==$TPL_V1){?>
					<a href="?page=<?php echo $TPL_V1?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="on"><?php echo $TPL_V1?></a>
<?php }else{?>
					<a href="?page=<?php echo $TPL_V1?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>"><?php echo $TPL_V1?></a>
<?php }?>
<?php }}?>
<?php if($TPL_VAR["page"]["next"]){?><a href="?page=<?php echo $TPL_VAR["page"]["next"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="next">다음 ▶</a><?php }?>
<?php if($TPL_VAR["page"]["last"]){?><a href="?page=<?php echo $TPL_VAR["page"]["last"]?>&amp;<?php echo $TPL_VAR["page"]["querystring"]?>" class="last">마지막 ▶</a><?php }?>
		</div>
	</td>
</tr>
</table>
<?php }?>