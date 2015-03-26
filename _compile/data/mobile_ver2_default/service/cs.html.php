<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/service/cs.html 000002431 */ ?>
<style>
#layout_body {background-color:#f5f7f6}
</style>

<!-- 타이틀 -->
<div class="sub_title_bar">
	<h2>고객센터</h2>
	<a href="javascript:history.back();" class="stb_back_btn"><img src="/data/skin/mobile_ver2_default/images/design/btn_back.png" width="22" height="22" /></a>
</div>

<div class="pd5">
	<table class="cs_list_table" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td onclick="document.location.href='../board/?id=notice'"><img src="/data/skin/mobile_ver2_default/images/design/icon_cs_notice.png" width="30" height="30" /><br /><br />공지사항</td>
		<td onclick="document.location.href='../board/?id=goods_review'"><img src="/data/skin/mobile_ver2_default/images/design/icon_cs_review.png" width="30" height="30" /><br /><br />상품리뷰</td>
		<td onclick="document.location.href='../board/?id=goods_qna'"><img src="/data/skin/mobile_ver2_default/images/design/icon_cs_rsv.png" width="30" height="30" /><br /><br />상품문의</td>
	</tr>
	<tr>
		<td onclick="document.location.href='../mypage/myqna_catalog'"><img src="/data/skin/mobile_ver2_default/images/design/icon_cs_q.png" width="30" height="30" /><br /><br />1:1문의</td>
		<td onclick="document.location.href='../service/guide'"><img src="/data/skin/mobile_ver2_default/images/design/icon_cs_info.png" width="30" height="30" /><br /><br />이용안내</td>
		<td onclick="document.location.href='../common/mobile_mode_off'"><img src="/data/skin/mobile_ver2_default/images/design/icon_cs_pc.png" width="30" height="30" /><br /><br />PC버전</td>
	</tr>
	</table>
</div>

<div class="pd10">
	<table width="100%" class="cs_info">
	<tr>
		<th valign="top">CONTACT</th>
		<td valign="top" colspan="2">MON - FRI 10:00 ~ 18:00<br />SAT 10:00 ~ 13:00</td>
	</tr>
	<tr>
		<th>E-MAIL</th>
		<td colspan="2"><a href="mailto:<?php echo $TPL_VAR["config_basic"]["companyEmail"]?>"><?php echo $TPL_VAR["config_basic"]["companyEmail"]?></a></td>
	</tr>
	<tr>
		<th>CALL</th>
		<td>
			<a href="tel:<?php echo $TPL_VAR["config_basic"]["companyPhone"]?>"><?php echo $TPL_VAR["config_basic"]["companyPhone"]?></a>
		</td>
		<td>
			<button class="btn_style" onclick="document.location.href='tel:<?php echo $TPL_VAR["config_basic"]["companyPhone"]?>'">전화걸기</button>
		</td>
	</tr>
	</table>
</diV>