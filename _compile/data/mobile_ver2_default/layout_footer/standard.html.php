<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/layout_footer/standard.html 000001942 */  $this->include_("confirmLicenseLink");?>
<!-- 하단영역 : 시작 -->

<div id="layout_footer">
	<h2 class="hide">하단 간략 메뉴</h2>
	<ul class="fnb">
<?php if($TPL_VAR["userInfo"]["member_seq"]){?>
		<li><?php if($TPL_VAR["userInfo"]["snsfacebookcon"]&&$TPL_VAR["fbuser"]){?><a href="javascript:void(0);" onclick="FB.logout(function(response) {logout();});return false; "><span class="btn_style">로그아웃</span></a><?php }else{?><a href="../login_process/logout" target= "actionFrame"><span class="btn_style">로그아웃</span></a><?php }?></li>
<?php }else{?>
		<li><a href="../member/login"><span class="btn_style">로그인</span></a></li>
<?php }?>
		<li><a href="../service/cs"><span class="btn_style">고객센터</span></a></li>
		<li><a href="../common/mobile_mode_off"><span class="btn_style">PC버전</span></a></li>
	</ul>
	<h2 class="hide">쇼핑몰 정보</h2>

	<ul class="fcp">
		<li><!--span class="hide"-->회사명 : <!--/span--><b><?php echo $TPL_VAR["config_basic"]["companyName"]?></b></li>
		<li>대표 : <?php echo $TPL_VAR["config_basic"]["ceo"]?></li>
		<li>고객센터 : <a href="tel:<?php echo $TPL_VAR["config_basic"]["companyPhone"]?>"><?php echo $TPL_VAR["config_basic"]["companyPhone"]?></a></li>
		<li>사업자등록번호 : <?php echo $TPL_VAR["config_basic"]["businessLicense"]?><?php echo confirmLicenseLink("[사업자정보확인]")?></li>
		<li>주소 : <?php echo $TPL_VAR["config_basic"]["companyAddress"]?> <?php echo $TPL_VAR["config_basic"]["companyAddressDetail"]?></li>
		<li>통신판매업 신고 : <?php echo $TPL_VAR["config_basic"]["mailsellingLicense"]?></li>
		<li>contact : <font color="990000"><b><?php echo $TPL_VAR["config_basic"]["companyEmail"]?></b></font> for more information</li>
	</ul>
</div>

<!-- 하단영역 : 끝 -->