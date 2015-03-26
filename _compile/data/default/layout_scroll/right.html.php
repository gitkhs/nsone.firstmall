<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/data/skin/default/layout_scroll/right.html 000007046 */  $this->include_("dataGoodsToday","dataRightRecommCount","dataCartCount","dataWishCount");?>
<style type="text/css">
.right_item_recent, .right_item_recomm, .right_item_cart, .right_item_wish, .rightBookMark {border-left:1px #e5e5e5 solid;border-right:1px #e5e5e5 solid;}
.rightBookMark {text-align:center;padding:14px 0px 7px;border-bottom:1px #e5e5e5 solid;}
.rightBlankBox {border-top:1px #e5e5e5 solid;margin:7px 0px 9px;}
.rightTitleMenu {text-align:center;padding-top:14px;cursor:pointer;}
.rightTitleMenu span, #right_recomm_total {font-weight:bold;color:#e89f0f;text-align:right;right:9px;width:12px;position:relative;float:right;}
.rightBorderTop {margin:14px 9px 2px 9px;border-top:1px #333 solid;min-width:60px;}
.rightBoxBorder {margin:0px 9px;border-top:1px #333 solid;min-width:60px;}
.right_quick_paging {position:relative;padding:9px 0px 7px;color:#acacac;text-align:center;display:none;}
.right_quick_paging .right_page_box {display:inline;}
.right_quick_paging .right_quick_btn_prev {position:absolute;left:9px;}
.right_quick_paging .right_quick_btn_next {position:absolute;right:9px;}
.right_itemList,.rightBorderTop,.rightBoxBorder {display:none;}
.rightQuickitemLi {z-index:8;}
.right_itemList ul li {padding-top:4px;position:relative;text-align:center;}
.right_itemList ul li .right_quick_goods img{max-width:60px;position:relative;}
.rightQuickitemDetail {display:none;width:150px;top:4px;}
.rightQuickitemDetailCss {text-align:left;display:block;overflow:visible;position:absolute;color:#fff;background-color:#acacac;}
.right_quick_goods_box .right_quick_btn_delete {position:absolute;top:0px;visibility:hidden;}
.rightQuickitemDetail span {text-align:left;font-size:11px;display:block;width:132px;position:relative;}
.rightQuickitemDetail span.right_item_title{letter-spacing:-1px;padding:8px 8px 3px 8px;}
.rightQuickitemDetail span.right_item_price{font-weight:bold;padding:3px 8px 6px 8px;}
.right_quick_goods_box {position:relative;display:block;}
.rightQuickMenu {background-color:#fff}
</style>

<script type="text/javascript">
$(document).ready(function() {
	$set_right_recent = 5;	/* 최근 본상품 설정 */
	$set_right_recomm = 5;	/* 추천상품 설정 */
	$set_right_cart = 5;		/* 장바구니 설정 */
	$set_right_wish = 5;		/* 위시리스트 설정 */
});
</script>

<div id="rightQuickMenuWrap" class="rightQuickMenuWrap">
	<div id="rightQuickMenu" class="rightQuickMenu">
		<div class="top_img"><img src="/data/skin/default/images/common/right_quick_menu_tit.gif" /></div>
		<!-- 최근본상품 -->
		<div class="right_item_recent">
				<p class="rightTitleMenu"><img src="/data/skin/default/images/common/right_quick_menu_recent.gif" alt="최근본상품" /> <span id="right_recent_total"><?php echo number_format(count(dataGoodsToday()))?></span></p>
			<div class="right_itemList">
				<p class="rightBorderTop"></p>
				<ul></ul>
				<div id="right_page_div" class="right_quick_paging">
						<a class="right_quick_btn_prev" href="#"><img src="/data/skin/default/images/common/right_quick_menu_left_icon.gif" alt="prev" /></a>
						<div class="right_page_box"><span class="right_quick_current_page bold"></span><span class="right_quick_separation">/</span><span class="right_quick_total_page"></span></div>
						<a class="right_quick_btn_next" href="#"><img src="/data/skin/default/images/common/right_quick_menu_right_icon.gif" alt="next" /></a>
				</div>
				<p class="rightBoxBorder"></p>
			</div>
		</div>
		<!-- 추천상품 -->
		<div class="right_item_recomm">
			<div class="rightTitleMenu"><div designElement='goodsRecommDisplay'><img src="/data/skin/default/images/common/right_quick_menu_recommend.gif" alt="추천상품" /> <span id="right_recomm_total"><?php echo number_format(dataRightRecommCount())?></span></div></div>
			<div class="right_itemList">
				<p class="rightBorderTop"></p>
				<ul></ul>
				<div id="right_page_div" class="right_quick_paging">
						<a class="right_quick_btn_prev" href="#"><img src="/data/skin/default/images/common/right_quick_menu_left_icon.gif" alt="prev" /></a>
						<div class="right_page_box"><span class="right_quick_current_page bold"></span><span class="right_quick_separation">/</span><span class="right_quick_total_page"></span></div>
						<a class="right_quick_btn_next" href="#"><img src="/data/skin/default/images/common/right_quick_menu_right_icon.gif" alt="next" /></a>
				</div>
				<p class="rightBoxBorder"></p>
			</div>
		</div>
		<!-- 장바구니 -->
		<div class="right_item_cart">
			<p class="rightTitleMenu"><img src="/data/skin/default/images/common/right_quick_menu_cart.gif" alt="장바구니" /> <span id="right_cart_total"><?php echo number_format(dataCartCount())?></span></p>
			<div class="right_itemList">
				<p class="rightBorderTop"></p>
				<ul></ul>
				<div id="right_page_div" class="right_quick_paging">
						<a class="right_quick_btn_prev" href="#"><img src="/data/skin/default/images/common/right_quick_menu_left_icon.gif" alt="prev" /></a>
						<div class="right_page_box"><span class="right_quick_current_page bold"></span><span class="right_quick_separation">/</span><span class="right_quick_total_page"></span></div>
						<a class="right_quick_btn_next" href="#"><img src="/data/skin/default/images/common/right_quick_menu_right_icon.gif" alt="next" /></a>
				</div>
				<p class="rightBoxBorder"></p>
			</div>
		</div>
		<!-- 위시리스트 -->
		<div class="right_item_wish">
			<p class="rightTitleMenu"><img src="/data/skin/default/images/common/right_quick_menu_wish.gif" alt="위시리스트" /> <span id="right_wish_total"><?php echo number_format(dataWishCount())?></span></p>
			<div class="right_itemList">
				<p class="rightBorderTop"></p>
				<ul></ul>
				<div id="right_page_div" class="right_quick_paging">
						<a class="right_quick_btn_prev" href="#"><img src="/data/skin/default/images/common/right_quick_menu_left_icon.gif" alt="prev" /></a>
						<div class="right_page_box"><span class="right_quick_current_page bold"></span><span class="right_quick_separation">/</span><span class="right_quick_total_page"></span></div>
						<a class="right_quick_btn_next" href="#"><img src="/data/skin/default/images/common/right_quick_menu_right_icon.gif" alt="next" /></a>
				</div>
				<p class="rightBoxBorder"></p>
			</div>
		</div>
		<!-- 북마크 -->
		<div class="rightBookMark">
			<a href="<?php echo $TPL_VAR["bookmark"]?>" id="linkbookmark" title="즐겨찾기에 추가" rel="sidebar" ><img src="/data/skin/default/images/common/right_quick_menu_bookmark.gif" alt="북마크" /></a>
		</div>
	</div>
	<div id="rightQuickMenuBottom" class="rightQuickMenuBottom">
		<div class="rightBlankBox"></div>
		<!--TOP -->
		<div class="rightTop center">
			<a href="javascript:;" onclick="$('body,html').animate({scrollTop:0},'fast')"><img src="/data/skin/default/images/common/right_quick_menu_top.gif" alt="top" /></a>
		</div>
	</div>
</div>