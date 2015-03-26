<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/data/skin/default/layout_header/standard.html 000006154 */  $this->include_("showTopPromotion");?>
<!-- 상단영역 : 시작 -->
<table width="<?php echo $TPL_VAR["layout_config"]["width"]?>" align="<?php echo $TPL_VAR["layout_config"]["align"]?>" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td valign="top">
		<a href="/main/index" target='_self'><img src="/data/skin/default/images/design/logo.gif" /></a>
	</td>
	<td valign="top">
		
		<table align="right" cellpadding="0" cellspacing="0" border="0">
		<tr><td height="50"></td></tr>
		<tr>
			<td align="right">
				<form name="topSearchForm" id="topSearchForm" action="../goods/search">
				<input type="hidden" name="keyword_log_flag" value="Y" />
				<input type="text" name="search_text" value="" style="width:300px; background-color:#f3f3f3; border:0px; height:20px; line-height:20px; text-indent:5px;" <?php if($TPL_VAR["auto_search_use"]=='y'){?>title="<?php echo $TPL_VAR["auto_search_text"]?>"<?php }?>/><input type="image" src="/data/skin/default/images/icon/icon_search.gif" value="검색" style="margin-left:5px; vertical-align:middle;" />
				</form>
			</td>
		</tr>
		<tr>
			<td align="right">
				
<?php if($TPL_VAR["auto_search_use"]=='y'){?>
				<script type="text/javascript">
				$("form#topSearchForm").submit(function(event){
					var search_text = $("form#topSearchForm input[name='search_text']").val();
					if (search_text == "" && "<?php echo $TPL_VAR["auto_search_use"]?>" == "y") {
						$("form#topSearchForm input[name='search_text']").val('<?php echo $TPL_VAR["auto_search_text"]?>');
					}

					if( $("form#topSearchForm input[name='search_text']").val() == '<?php echo $TPL_VAR["auto_search_text"]?>' ){
<?php if($TPL_VAR["auto_search_type"]=='direct'){?>						
<?php if($TPL_VAR["auto_search_target"]=='_self'){?>
							document.location.href="<?php echo $TPL_VAR["auto_search_link"]?>";
<?php }else{?>
							var openNewWindow = window.open("about:blank");
							openNewWindow.document.location.href="<?php echo $TPL_VAR["auto_search_link"]?>";
<?php }?>
<?php }else{?>							 
<?php if($TPL_VAR["auto_search_target"]=='_self'){?>
							document.location.href="../goods/search?search_text=<?php echo urlencode($TPL_VAR["auto_search_link"])?>";
<?php }else{?>
							var openNewWindow = window.open("about:blank");
							openNewWindow.document.location.href="../goods/search?search_text=<?php echo urlencode($TPL_VAR["auto_search_link"])?>";
<?php }?>							
<?php }?>
						
						setTimeout(function(){
							$("input[name='search_text']").focusout();	
						},50);							
						event.preventDefault();
						return false;
					}
				});
				</script>
<?php }?>
			</td>
		</tr>
		<tr><td height="18"></td></tr>
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
				<tr>
<?php if($TPL_VAR["userInfo"]["member_seq"]){?>
					<td><?php if($TPL_VAR["userInfo"]["snsfacebookcon"]&&$TPL_VAR["fbuser"]){?><a href="../login_process/logout" target= "actionFrame"   ><img src="/data/skin/default/images/design/top_logout.gif" alt="logout" /></a><?php }else{?><a href="../login_process/logout" target= "actionFrame"   ><img src="/data/skin/default/images/design/top_logout.gif" alt="logout" /></a><?php }?></td>
					<td width="20"></td>
					<td><a href="../mypage/myinfo"><img src="/data/skin/default/images/design/top_myinfo.gif" /></a></td>
<?php }else{?>
					<td><a href="../member/login"><img src="/data/skin/default/images/design/top_login.gif" /></a></td>
					<td width="20"></td>
					<td><a href="../member/agreement"><img src="/data/skin/default/images/design/top_join.gif" /></a></td>
<?php }?>
					<td width="20"></td>
					<td><a href="../order/cart"><img src="/data/skin/default/images/design/top_cart.gif" /></a></td>
					<td width="20"></td>
					<td><a href="../mypage/order_catalog"><img src="/data/skin/default/images/design/top_order.gif" /></a></td>
					<td width="20"></td>
					<td><a href="../mypage/index"><img src="/data/skin/default/images/design/top_mypage.gif" /></a></td>
					<td width="20"></td>
					<td><a href="../service/cs"><img src="/data/skin/default/images/design/top_cscenter.gif" title="" alt="" /></a></td>
					<td width="20"></td>
					<td><a href="<?php echo $TPL_VAR["bookmark"]?>" ><img src="/data/skin/default/images/design/top_bookmark.gif" title="" /></a></td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>
<?php if(showTopPromotion(null,$TPL_VAR["layout_config"]["width"])){?>
<table width="<?php echo ($TPL_VAR["layout_config"]["width"])?>" height="45"  align="<?php echo $TPL_VAR["layout_config"]["align"]?>" cellpadding="0" cellspacing="0" border="0" >
<tr>
	<td valign="top">
		<style>
		.header_promo {position:relative; height:40px; width:<?php echo ($TPL_VAR["layout_config"]["width"])?>px; margin:auto; border:1px solid #c6c6c6;}
		.header_promo .promo_prev {position:absolute; left:10px; top:10px;z-index:100;}
		.header_promo .promo_next {position:absolute; right:10px; top:10px;z-index:100;}
		.header_promo .promo_list {width:<?php echo ($TPL_VAR["layout_config"]["width"])?>px; white-space:nowrap;list-style:none;}
		.header_promo .promo_list li {float:left;list-style:none;display:inline-block; width:<?php echo ($TPL_VAR["layout_config"]["width"])?>px; height:40px; line-height:40px; text-align:center; font-size:16px; font-family:dotum; font-weight:bold; color:#fff; letter-spacing:-1px;}
		</style>
		<div class="header_promo relative">
			<div class="promo_prev prev "><a href="#" class=""><img src="/data/skin/default/images/common/promo_arrow_prev.png" /></a></div>
			<div class="promo_next next "><a href="#" class=""><img src="/data/skin/default/images/common/promo_arrow_next.png" /></a></div>
			<div class="promo_list slides_container ">
				<?php echo showTopPromotion(null,$TPL_VAR["layout_config"]["width"])?>

			</div>
			<script>
				$('.header_promo').slides({
					preload: true,
					play: 3000,
					generateNextPrev: false,
					generatePagination: false
				});
			</script>
		</div>
</td>
</tr>
</table>
<?php }?>


<!-- 상단영역 : 끝 -->