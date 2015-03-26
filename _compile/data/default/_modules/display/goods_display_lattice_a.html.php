<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/data/skin/default/_modules/display/goods_display_lattice_a.html 000029367 */  $this->include_("showGoodsSearchForm","snsLikeButton");
$TPL_orders_1=empty($TPL_VAR["orders"])||!is_array($TPL_VAR["orders"])?0:count($TPL_VAR["orders"]);
$TPL_displayTabsList_1=empty($TPL_VAR["displayTabsList"])||!is_array($TPL_VAR["displayTabsList"])?0:count($TPL_VAR["displayTabsList"]);?>
<style>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageWrap {position:relative;}
<?php if($TPL_VAR["decorations"]["image_border1_width"]){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageWrap {border:<?php echo $TPL_VAR["decorations"]["image_border1_width"]?>px solid <?php echo $TPL_VAR["decorations"]["image_border1"]?>; margin:-<?php echo $TPL_VAR["decorations"]["image_border1_width"]?>px;}
<?php }?>
<?php if($TPL_VAR["decorations"]["image_icon"]&&$TPL_VAR["decorations"]["image_icon_location"]=='right'){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageIcon {position:absolute; right:0px; top:0px; <?php if($TPL_VAR["decorations"]["image_icon_over"]=='y'){?>display:none;<?php }?>}
<?php }?>
<?php if($TPL_VAR["decorations"]["image_icon"]&&$TPL_VAR["decorations"]["image_icon_location"]=='left'){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageIcon {position:absolute; left:0px; top:0px; <?php if($TPL_VAR["decorations"]["image_icon_over"]=='y'){?>display:none;<?php }?>}
<?php }?>
<?php if($TPL_VAR["decorations"]["image_icon"]&&preg_match("/^icon_best_no/",$TPL_VAR["decorations"]["image_icon"])){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageIconText {position:absolute;font-size:16px;font-family:tahoma;font-weight:bold;text-align:center;color:#ffffff;letter-spacing:-1px;width:48px;top:25px;left:0px;}
<?php }?>
<?php if($TPL_VAR["decorations"]["image_icon"]&&preg_match("/^icon_number/",$TPL_VAR["decorations"]["image_icon"])){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageIconText {position:absolute;font-size:18px;font-family:tahoma;font-weight:bold;text-align:right;color:#ffffff;letter-spacing:-1px;width:28px;top:10px;left:0px;}
<?php }?>
<?php if($TPL_VAR["decorations"]["image_icon"]&&preg_match("/^icon_sale/",$TPL_VAR["decorations"]["image_icon"])){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageIconText {position:absolute;font-size:16px;font-family:tahoma;font-weight:bold;text-align:center;color:#ffffff;letter-spacing:-1px;width:48px;top:0px;left:0px;}
<?php }?>
<?php if(($TPL_VAR["decorations"]["image_send"]||$TPL_VAR["decorations"]["image_zzim"])&&$TPL_VAR["decorations"]["image_send_location"]=='right'){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageSend {position:absolute;right:0px;top:2px; <?php if($TPL_VAR["decorations"]["image_send_over"]=='y'){?>display:none;<?php }?>}
<?php }?>
<?php if(($TPL_VAR["decorations"]["image_send"]||$TPL_VAR["decorations"]["image_zzim"])&&$TPL_VAR["decorations"]["image_send_location"]=='left'){?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageSend {position:absolute;left:0px;top:2px; <?php if($TPL_VAR["decorations"]["image_send_over"]=='y'){?>display:none;<?php }?>}
<?php }?>
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageSlide {position:absolute;right:0px;top:50%;margin-top:-14px;}
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageOveray1 {display:none; position:absolute;left:0px;top:100%;margin-top:-20px;width:100%;height:20px;}
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageOveray1Bg {background-color:#000000;color:#fff;opacity:0.3;position:absolute;left:0;top:0;width:100%;height:20px}
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayImageOveray1Text {color:#fff;font-size:11px;font-weight:bold;text-align:center;position:absolute;overflow:hidden;white-space:nowrap;line-height:20px;left:0;top:0;width:100%}
#<?php echo $TPL_VAR["display_key"]?> .goodsDisplayQuickShopping {}
#<?php echo $TPL_VAR["display_key"]?> table.quick_shopping_container {border-collapse:collapse;table-layout:fixed}
#<?php echo $TPL_VAR["display_key"]?> table.quick_shopping_container td {height:14px;text-align:center;border:1px solid #e5e5e5;background-color:#fff;font-size:11px;letter-spacing:-1px}
</style>

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
	<table class="displayTabContentsContainer <?php if(count($TPL_VAR["displayTabsList"])> 1){?>displayTabContentsContainerBox<?php }?>" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed">
<?php if($TPL_V1["contents_type"]=='text'){?>
	<tr>
		<td>
<?php if($TPL_VAR["mobileMode"]||$TPL_VAR["storemobileMode"]){?><?php echo $TPL_V1["tab_contents_mobile"]?><?php }else{?><?php echo $TPL_V1["tab_contents"]?><?php }?>
		</td>
	</tr>
<?php }else{?>
<?php if(is_array($TPL_R2=$TPL_V1["grid"])&&!empty($TPL_R2)){$TPL_I2=-1;foreach($TPL_R2 as $TPL_V2){$TPL_I2++;?>
		<tr>
<?php if(is_array($TPL_R3=$TPL_V2)&&!empty($TPL_R3)){$TPL_I3=-1;foreach($TPL_R3 as $TPL_V3){$TPL_I3++;?>
<?php if($TPL_I3){?><td></td><?php }?>
				<td width="<?php echo $TPL_VAR["goodsImageSize"]["width"]?>" valign="top">
					<?php
						 $TPL_VAR["idx"] =  $TPL_I2* $TPL_VAR["count_w"]+ $TPL_I3;
						 $TPL_VAR["moduledisplayGoods"] =  $TPL_V1["record"][$TPL_VAR["idx"]];
					?>
<?php if($TPL_VAR["moduledisplayGoods"]){?>
					<table class="goodsDisplayItemWrap" width="100%" align="<?php echo $TPL_VAR["text_align"]?>" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td align="<?php echo $TPL_VAR["text_align"]?>" width="<?php echo $TPL_VAR["goodsImageSize"]["width"]?>" height="<?php echo $TPL_VAR["goodsImageSize"]["height"]?>">
						<span class="goodsDisplayImageWrap" decoration="<?php echo $TPL_VAR["image_decorations"]?>" goodsInfo="<?php echo base64_encode(json_encode($TPL_VAR["moduledisplayGoods"]))?>" style="max-width:<?php echo $TPL_VAR["goodsImageSize"]["width"]?>px;max-height:<?php echo $TPL_VAR["goodsImageSize"]["height"]?>px;overflow:hidden;" version="20141110">
							<a href="/goods/view?no=<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" target="<?php echo $TPL_VAR["target"]?>">
<?php if($TPL_VAR["moduledisplayGoods"]["image_size"]&&$TPL_VAR["moduledisplayGoods"]["image_size"][ 0]/$TPL_VAR["moduledisplayGoods"]["image_size"][ 1]<$TPL_VAR["goodsImageSize"]["width"]/$TPL_VAR["goodsImageSize"]["height"]){?>
									<img src="<?php echo $TPL_VAR["moduledisplayGoods"]["image"]?>" height="<?php echo $TPL_VAR["goodsImageSize"]["height"]?>" onerror="this.src='/data/skin/default/images/common/noimage.gif';this.style.width='<?php echo $TPL_VAR["goodsImageSize"]["width"]?>px';" />
<?php }else{?>
									<img src="<?php echo $TPL_VAR["moduledisplayGoods"]["image"]?>" width="<?php echo $TPL_VAR["goodsImageSize"]["width"]?>" onerror="this.src='/data/skin/default/images/common/noimage.gif';this.style.height='<?php echo $TPL_VAR["goodsImageSize"]["height"]?>px';" />
<?php }?>
<?php if($TPL_VAR["decorations"]["image_icon"]&&preg_match("/^icon_sale/",$TPL_VAR["decorations"]["image_icon"])){?>
<?php if($TPL_VAR["moduledisplayGoods"]["sale_per"]> 0){?>
									<div class='goodsDisplayImageIcon'>
										<img src='/data/icon/goodsdisplay/<?php echo $TPL_VAR["decorations"]["image_icon"]?>' />
										<span class='goodsDisplayImageIconText'><?php echo $TPL_VAR["moduledisplayGoods"]["sale_per"]?>%</span>
									</div>
<?php }?>
<?php }elseif($TPL_VAR["decorations"]["image_icon"]){?>
									<div class='goodsDisplayImageIcon'>
										<img src='/data/icon/goodsdisplay/<?php echo $TPL_VAR["decorations"]["image_icon"]?>' />
<?php if(preg_match("/^(icon_best_no|icon_number)/",$TPL_VAR["decorations"]["image_icon"])){?>
										<span class='goodsDisplayImageIconText'><?php echo $TPL_VAR["idx"]+ 1?></span>
<?php }?>
									</div>
<?php }?>
<?php if($TPL_VAR["decorations"]["image_send"]||$TPL_VAR["decorations"]["image_zzim"]){?>
									<div class='goodsDisplayImageSend'>
<?php if($TPL_VAR["decorations"]["image_send"]){?>
										<img class='goodsSendBtn' src='/data/icon/goodsdisplay/send/<?php echo $TPL_VAR["decorations"]["image_send"]?>' />
<?php }?>
<?php if($TPL_VAR["decorations"]["image_zzim"]){?>
										<span class='goodsZzimBtn'><img src='/data/icon/goodsdisplay/zzim/<?php echo $TPL_VAR["decorations"]["image_zzim"]?>' class='zzimOffImg' <?php if($TPL_VAR["moduledisplayGoods"]["wish"]=='1'){?>style="display:none"<?php }?> /><img src='/data/icon/goodsdisplay/zzim_on/<?php echo $TPL_VAR["decorations"]["image_zzim_on"]?>' class='zzimOnImg' <?php if($TPL_VAR["moduledisplayGoods"]["wish"]!='1'){?>style="display:none"<?php }?> /></span>
<?php }?>
									</div>
<?php }?>
<?php if($TPL_VAR["decorations"]["image_slide"]&&$TPL_VAR["moduledisplayGoods"]["image_cnt"]> 1){?>
									<div class='goodsDisplayImageSlide'><img src='/data/icon/goodsdisplay/slide/<?php echo $TPL_VAR["decorations"]["image_slide"]?>' /></div>
<?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]||$TPL_VAR["decorations"]["image_overay1_text"]){?>
									<div class='goodsDisplayImageOveray1'>
										<div class='goodsDisplayImageOveray1Bg'></div>
										<div class='goodsDisplayImageOveray1Text'>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='goods_name'){?><?php echo $TPL_VAR["moduledisplayGoods"]["goods_name"]?><?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='price'){?>￦<?php echo number_format($TPL_VAR["moduledisplayGoods"]["price"])?><?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='sale_price'){?>￦<?php echo number_format($TPL_VAR["moduledisplayGoods"]["sale_price"])?><?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='consumer_price'){?>￦<?php echo number_format($TPL_VAR["moduledisplayGoods"]["consumer_price"])?><?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='discount'){?>
<?php if($TPL_VAR["moduledisplayGoods"]["string_price"]){?>
													<?php echo $TPL_VAR["moduledisplayGoods"]["string_price"]?>

<?php }elseif($TPL_VAR["moduledisplayGoods"]["consumer_price"]>$TPL_VAR["moduledisplayGoods"]["price"]){?>
													<?php echo number_format($TPL_VAR["moduledisplayGoods"]["consumer_price"])?> → <?php echo number_format($TPL_VAR["moduledisplayGoods"]["price"])?>

<?php }else{?>
													<?php echo number_format($TPL_VAR["moduledisplayGoods"]["price"])?>

<?php }?>
<?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='sale_discount'){?>
<?php if($TPL_VAR["moduledisplayGoods"]["string_price"]){?>
													<?php echo $TPL_VAR["moduledisplayGoods"]["string_price"]?>

<?php }elseif($TPL_VAR["moduledisplayGoods"]["consumer_price"]>$TPL_VAR["moduledisplayGoods"]["sale_price"]){?>
													<?php echo number_format($TPL_VAR["moduledisplayGoods"]["consumer_price"])?> → <?php echo number_format($TPL_VAR["moduledisplayGoods"]["sale_price"])?>

<?php }else{?>
													<?php echo number_format($TPL_VAR["moduledisplayGoods"]["sale_price"])?>

<?php }?>
<?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='brand_title'){?><?php echo $TPL_VAR["moduledisplayGoods"]["brand_title"]?><?php }?>
<?php if($TPL_VAR["decorations"]["image_overay1"]=='related_goods'){?><span class='hand' onclick="return show_display_related_goods(this,'<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>')" style='display:block;'>관련상품보기</span><?php }?>
<?php if(!$TPL_VAR["decorations"]["image_overay1"]&&$TPL_VAR["image_overay1_text"]){?><?php echo $TPL_VAR["moduledisplayGoods"]["image_overay1_text"]?><?php }?>
										</div>
									</div>
<?php }?>
							</a>
						</span>

<?php if($TPL_VAR["decorations"]["quick_shopping"]&&$TPL_VAR["decorations"]["quick_shopping_data"]){?>
							<div class='goodsDisplayQuickShopping'>
								<table class='quick_shopping_container' width='100%' border='0' cellpadding='0' cellspacing='0'>
								<tr>
<?php if(is_array($TPL_R4=($TPL_VAR["decorations"]["quick_shopping_data"]))&&!empty($TPL_R4)){foreach($TPL_R4 as $TPL_V4){?>
<?php if($TPL_V4=='newwin'){?>
									<td class='goodsNewwinBtn hand' onclick="window.open('/goods/view?no=<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>')"><img src='/data/icon/goodsdisplay/quick_shopping/thumb_newwin.gif' /></td>
<?php }?>
<?php if($TPL_V4=='quickview'){?>
									<td class='goodsQuickviewBtn hand' onclick="display_goods_quickview(this,'<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>')"><img src='/data/icon/goodsdisplay/quick_shopping/thumb_quickview.gif' /></td>
<?php }?>
<?php if($TPL_V4=='send'){?>
									<td class='goodsSendBtn hand' onclick="display_goods_send(this,'bottom')"><img src='/data/icon/goodsdisplay/quick_shopping/thumb_send.gif' /></td>
<?php }?>
<?php if($TPL_V4=='zzim'){?>
									<td class='goodsZzimBtn hand' width='16' onclick="display_goods_zzim(this,'<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>')">
									<img src='/data/icon/goodsdisplay/quick_shopping/thumb_zzim.gif' class='zzimOffImg' <?php if($TPL_VAR["moduledisplayGoods"]["wish"]=='1'){?>style="display:none"<?php }?> />
									<img src='/data/icon/goodsdisplay/quick_shopping/thumb_zzim_on.gif' class='zzimOnImg' <?php if($TPL_VAR["moduledisplayGoods"]["wish"]!='1'){?>style="display:none"<?php }?> />
									</td>
<?php }?>
<?php }}?>
								</tr>
								</table>
							</div>
<?php }?>
						</td>
					</tr>
<?php if(is_array($TPL_R4=$TPL_VAR["info_settings"]["list"])&&!empty($TPL_R4)){foreach($TPL_R4 as $TPL_V4){?>

<?php if($TPL_V4->kind=='brand_title'&&$TPL_VAR["moduledisplayGoods"]["brand_title"]){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
							<a href="/goods/brand?code=<?php echo $TPL_VAR["moduledisplayGoods"]["brand_code"]?>">
								<span <?php echo $TPL_V4->name_css?>>
<?php if($TPL_V4->wrapper){?><?php echo substr($TPL_V4->wrapper, 0, 1)?><?php }?><?php echo $TPL_VAR["moduledisplayGoods"]["brand_title"]?><?php if($TPL_V4->wrapper){?><?php echo substr($TPL_V4->wrapper, 1, 1)?><?php }?>
								</span>
							</a>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='goods_name'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>"><a href="/goods/view?no=<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" target="<?php echo $TPL_VAR["target"]?>"><span <?php echo $TPL_V4->name_css?>><?php echo $TPL_VAR["moduledisplayGoods"]["goods_name"]?></span></a></td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='summary'&&$TPL_VAR["moduledisplayGoods"]["summary"]){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>"><span <?php echo $TPL_V4->name_css?>><?php echo $TPL_VAR["moduledisplayGoods"]["summary"]?></span></td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='icon'){?>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
<?php if(is_array($TPL_R5=$TPL_VAR["moduledisplayGoods"]["icons"])&&!empty($TPL_R5)){foreach($TPL_R5 as $TPL_V5){?>
								<img src="/data/icon/goods/<?php echo $TPL_V5?>.gif" border="0">
<?php }}?>
<?php if($TPL_V4->list_icon_cpn&& 0){?>
								<img src="/data/icon/goods_status/icon_list_cpn.gif" />
<?php }?>
<?php if($TPL_V4->list_icon_freedlv&& 0){?>
								<img src="/data/icon/goods_status/icon_list_freedlv.gif" />
<?php }?>
<?php if($TPL_V4->list_icon_video&&$TPL_VAR["moduledisplayGoods"]["videousetotal"]){?>
								<img src="/data/icon/goods_status/icon_list_video.gif" />
<?php }?>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='consumer_price'&&$TPL_VAR["moduledisplayGoods"]["consumer_price"]>$TPL_VAR["moduledisplayGoods"]["sale_price"]){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
								<span <?php echo $TPL_V4->name_css?>>
<?php if($TPL_VAR["moduledisplayGoods"]["string_price"]){?>
										<?php echo $TPL_VAR["moduledisplayGoods"]["string_price"]?>

<?php }else{?>
										<?php echo number_format($TPL_VAR["moduledisplayGoods"]["consumer_price"])?>

<?php if($TPL_V4->postfix){?><?php echo $TPL_V4->postfix?><?php }?>
<?php }?>
								</span>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='price'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
								<span <?php echo $TPL_V4->name_css?>>
<?php if($TPL_VAR["moduledisplayGoods"]["string_price"]){?>
										<?php echo $TPL_VAR["moduledisplayGoods"]["string_price"]?>

<?php }else{?>
										<?php echo number_format($TPL_VAR["moduledisplayGoods"]["price"])?>

<?php if($TPL_V4->postfix){?><?php echo $TPL_V4->postfix?><?php }?>
<?php }?>
								</span>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='sale_price'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
								<span <?php echo $TPL_V4->name_css?>>
<?php if($TPL_VAR["moduledisplayGoods"]["string_price"]){?>
										<?php echo $TPL_VAR["moduledisplayGoods"]["string_price"]?>

<?php }else{?>
										<?php echo number_format($TPL_VAR["moduledisplayGoods"]["sale_price"])?>

<?php if($TPL_V4->postfix){?><?php echo $TPL_V4->postfix?><?php }?>
<?php }?>
								</span>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='count'&&$TPL_VAR["moduledisplayGoods"]["eventEnd"]&&($TPL_V4->time_count||$TPL_V4->buy_count)){?>
							<tr ><td height="6"></td></tr>
							<tr><td>
							<table width="100%" height="42" cellspacing="0" cellpadding="0" style="border:1px solid #e9e9e9;" bgcolor="#f8f8f8" align="center">
<?php if($TPL_V4->time_count){?>
<?php if($TPL_VAR["moduledisplayGoods"]["eventEnd"]){?>
							<tr ><td height="5"></td></tr>
							<tr >
								<td class="soloEventTd_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" align="<?php echo $TPL_VAR["text_align"]?>" style="font-face:Dotum; font-size:11px;"><img src="/data/skin/default/images/common/icon_clock.gif" style="padding-bottom:2px;">남은시간 <span style="background-color:#c61515; color:#ffffff; padding:2px; font-weight:bold;"><span id="soloday_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>일 <span id="solohour_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>:<span id="solomin_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span>:<span id="solosecond_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>" style="color:#ffffff; font-weight:bold;"></span></span></td>
							</tr>
							<script>
							$(function() {
								timeInterval_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?> = setInterval(function(){
									var time_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?> = showClockTime('text', '<?php echo $TPL_VAR["moduledisplayGoods"]["eventEnd"]["year"]?>', '<?php echo $TPL_VAR["moduledisplayGoods"]["eventEnd"]["month"]?>', '<?php echo $TPL_VAR["moduledisplayGoods"]["eventEnd"]["day"]?>', '<?php echo $TPL_VAR["moduledisplayGoods"]["eventEnd"]["hour"]?>', '<?php echo $TPL_VAR["moduledisplayGoods"]["eventEnd"]["min"]?>', '<?php echo $TPL_VAR["moduledisplayGoods"]["eventEnd"]["second"]?>', 'soloday_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>', 'solohour_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>', 'solomin_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>', 'solosecond_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>', '_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>');
									if(time_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?> == 0){
										clearInterval(timeInterval_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>);
										$(".soloEventTd_<?php echo $TPL_VAR["display_key"]?>_<?php echo $TPL_VAR["moduledisplayGoods"]["goods_seq"]?>").html("단독 이벤트 종료");
									}
								},1000);
							});
							</script>
<?php }?>
<?php }?>
<?php if($TPL_V4->buy_count){?>
							<tr ><td height="6"></td></tr>
							<tr>
								<td align="<?php echo $TPL_VAR["text_align"]?>" style="font-face:Dotum; font-size:11px;">현재 <font color="#c61515"><u><b><?php echo number_format($TPL_VAR["moduledisplayGoods"]["event_order_ea"])?></b></u></font>개 구매</td>
							</tr>
<?php }?>
							</td></tr>
							<tr ><td height="5"></td></tr>
							</table>
<?php }?>

<?php if($TPL_V4->kind=='event_text'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
								<span <?php echo $TPL_V4->name_css?>>
<?php if(is_numeric($TPL_VAR["moduledisplayGoods"]["event_text"])){?>
										<?php echo number_format($TPL_VAR["moduledisplayGoods"]["event_text"])?>

<?php if($TPL_V4->postfix){?><?php echo $TPL_V4->postfix?><?php }?>
<?php }else{?>
										<?php echo $TPL_VAR["moduledisplayGoods"]["event_text"]?>

<?php }?>
								</span>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='fblike'&&$TPL_VAR["APP_USE"]=='f'&&$TPL_VAR["APP_LIKE_TYPE"]!='NO'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>"><?php echo snsLikeButton($TPL_VAR["moduledisplayGoods"]["goods_seq"],$TPL_V4->fblike)?></td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='status_icon'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
<?php if($TPL_V4->status_icon_runout&&$TPL_VAR["moduledisplayGoods"]["goods_status"]=='runout'){?>
								<img src="/data/icon/goods_status/icon_list_soldout.gif" />
<?php }?>
<?php if($TPL_V4->status_icon_purchasing&&$TPL_VAR["moduledisplayGoods"]["goods_status"]=='purchasing'){?>
								<img src="/data/icon/goods_status/icon_list_warehousing.gif" />
<?php }?>
<?php if($TPL_V4->status_icon_unsold&&$TPL_VAR["moduledisplayGoods"]["goods_status"]=='unsold'){?>
								<img src="/data/icon/goods_status/icon_list_stop.gif" />
<?php }?>
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='score'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
								<font style="font-weight:bold; color:#fff; background-color:#ee6600; padding:0 3px; font-family:tahoma"><?php echo round($TPL_VAR["moduledisplayGoods"]["review_sum"]/$TPL_VAR["moduledisplayGoods"]["review_count"])?></font>
								<span class="orange"><?php echo str_repeat('★',round($TPL_VAR["moduledisplayGoods"]["review_sum"]/$TPL_VAR["moduledisplayGoods"]["review_count"]))?></span>
								<span class="gray"><?php echo str_repeat('★', 5-round($TPL_VAR["moduledisplayGoods"]["review_sum"]/$TPL_VAR["moduledisplayGoods"]["review_count"]))?></span>
								상품평 (<span class="red"><?php echo number_format($TPL_VAR["moduledisplayGoods"]["review_count"])?></span>)
							</td>
						</tr>
<?php }?>

<?php if($TPL_V4->kind=='color'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
<?php if(is_array($TPL_R5=($TPL_VAR["moduledisplayGoods"]["colors"]))&&!empty($TPL_R5)){foreach($TPL_R5 as $TPL_V5){?>
								<span style="color:<?php echo $TPL_V5?>;">■</span>
<?php }}?>
							</td>
						</tr>
<?php }?>

<?php }}?>
<?php if($TPL_VAR["is_bigdata_display"]=='y'){?>
						<tr><td height="6"></td></tr>
						<tr>
							<td align="<?php echo $TPL_VAR["text_align"]?>">
								<a href="/bigdata/catalog?no=<?php echo $TPL_V1["goods_seq"]?>"><u style="font-size:11px;color:#0094e1;">다른 사람은 뭘살까?</u></a>
							</td>
						</tr>
<?php }?>
					</table>
<?php }?>
				</td>
<?php }}?>
		</tr>
		<tr>
			<td height="10"></td>
		</tr>
<?php }}?>
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