<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/setting/_setting_menu.html 000001894 */ ?>
<div class="slc-head">
<ul>
	<li><span class="mitem"><a href="config">판매환경</a></span></li>
	<li><span class="mitem basic"><a href="basic">일반정보</a></span></li>
	<li><span class="mitem"><a href="snsconf">SNS/외부연동</a></span></li>
	<li><span class="mitem"><a href="operating">운영방식</a></span></li>
	<li><span class="mitem pg"><a href="pg">전자결제</a></span></li>
	<li><span class="mitem bank"><a href="bank">무통장</a></span></li>
	<li><span class="mitem"><a href="member">회원</a></span></li>
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
	<li><span class="mitem nofreelinknone"><a href="#">상품 코드/정보</a></span></li>
<?php }else{?>
	<li><span class="mitem"><a href="goods">상품 코드/정보</a></span></li>	
<?php }?>
	<li><span class="mitem"><a href="search">상품/주소 검색</a></span></li>
	<li><span class="mitem"><a href="video">동영상</a></span></li>
	<li><span class="mitem"><a href="order">주문</a></span></li>
	<li><span class="mitem"><a href="sale">매출증빙</a></span></li>
	<li><span class="mitem"><a href="reserve">적립금/포인트/이머니</a></span></li>	
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>		
		<li><span class="mitem noshoplinknone"><a href="#">택배/배송비</a></span></li>
<?php }else{?>
		<li><span class="mitem shipping"><a href="shipping">택배/배송비</a></span></li>
<?php }?>
	<li><span class="mitem"><a href="protect">보안</a></span></li>
	<li><span class="mitem"><a href="manager">관리자</a></span></li>
</ul>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("div.slc-head a[href='<?php echo $TPL_VAR["selected_setting_menu"]?>']").parent().parent().addClass("selected");
});
</script>