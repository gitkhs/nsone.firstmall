<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/layout_header/standard.html 000003702 */  $this->include_("showGoodsOrderForm");?>
<script type="text/javascript">
$(function(){
	// 검색폼 여닫기
	$("#layout_header a[href='#search']").click(function(){
		if($("#layout_header form.search_form").is(":visible")){
			$("#layout_header form.search_form").hide();
			$("#top_search").attr('src','/data/skin/mobile_ver2_default/images/design/top_search.png');
		}else{
			$("#layout_header form.search_form").show();
			$("#layout_header form.search_form input.search_text").focus();
			$("#top_search").attr('src','/data/skin/mobile_ver2_default/images/design/top_close.png');
		}
		return false;
	});

	// 검색폼입력창 배경
	$("#layout_header form.search_form input.search_text").bind('change keyup',function(){
		if($(this).val().length){
			$(this).removeClass('search_text_bg');
		}else{
			$(this).addClass('search_text_bg');
		}
	}).change();

<?php if(uri_string()=='goods/search'){?>
	$("#top_search").attr('src','/data/skin/mobile_ver2_default/images/design/top_close.png');
<?php }?>
});
</script>

<!-- 상단영역 : 시작 -->

<div id="layout_header">
	<h1><a href="/main/index"><?php echo $TPL_VAR["config_basic"]["shopName"]?></a></h1>

	<a href="#category"><img src="/data/skin/mobile_ver2_default/images/design/top_menu.png" alt="카테고리" width="24" height="18" /></a>
	<a href="#search"><img src="/data/skin/mobile_ver2_default/images/design/top_search.png" id="top_search" alt="검색" width="25" height="25" /></a>

	<h2 class="hide">상품검색</h2>
	<form action="../goods/search" class="search_form <?php if(uri_string()!='goods/search'){?>hide<?php }?>">
		<input type="hidden" name="keyword_log_flag" value="Y" />
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td bgcolor="ffffff"><input type="text" name="search_text" value="<?php echo htmlspecialchars($_GET["search_text"])?>" class="search_text" /></td>
			<td width="5"></td>
			<td width="25"><input type="image" src="/data/skin/mobile_ver2_default/images/design/top_search.png" width="25" height="25" /></td>
		</tr>
		</table>
	</form>
	
</div>
<!-- 상단영역 : 끝 -->

<!-- 퀵메뉴 영역 : 시작 -->

<?php if(uri_string()=='goods/view_contents'||(uri_string()=='board'&&$_GET["id"]=='goods_review')){?>
<?php echo showGoodsOrderForm($_GET["no"])?>

<?php }else{?>
<div id="quick_layer">
	<table class="common_quick">
	<col width="25%" />
	<col width="25%" />
	<col width="25%" />
	<col width="25%" />
	<tr>
		<td><a href="/main/index"><img src="/data/skin/mobile_ver2_default/images/design/ftr_home.png" width="25" height="24" vspace="2" /><br />홈</a></td>
		<td><a href="/order/cart" class="relative"><img src="/data/skin/mobile_ver2_default/images/design/ftr_cart.png" width="26" height="24" vspace="2" /><br />장바구니<?php if($TPL_VAR["push_count_cart"]){?><span class="pushCount" style="position:absolute;top:0px;right:5px;"><?php echo $TPL_VAR["push_count_cart"]?></span><?php }?></a></td>
		<td><a href="/mypage/order_catalog?step_type=order" class="relative"><img src="/data/skin/mobile_ver2_default/images/design/ftr_delivery.png" width="26" height="24" vspace="2" /><br />주문/배송<?php if($TPL_VAR["push_count_order"]){?><span class="pushCount" style="position:absolute;top:0px;right:5px;"><?php echo $TPL_VAR["push_count_order"]?></span><?php }?></a></td>
		<td><a href="../mypage/index"><img src="/data/skin/mobile_ver2_default/images/design/ftr_mypage.png" width="21" height="24" vspace="2" /><br />마이페이지</a></td>
	</tr>
	</table>
</div>
<?php }?>
<!-- 퀵메뉴 영역 : 끝 -->