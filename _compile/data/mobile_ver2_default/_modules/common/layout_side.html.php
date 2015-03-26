<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/_modules/common/layout_side.html 000004804 */  $this->include_("getChildCategory","getChildBrand","getChildLocation");?>
<ul class="top_navigation">
	<li><a href="/main/index"><img src="/data/skin/mobile_ver2_default/images/design/t_i_home.png" width="25" height="24" vspace="4" /><br />홈</a></li>
	<li><a href="/order/cart"><img src="/data/skin/mobile_ver2_default/images/design/t_i_cart.png" width="25" height="24" vspace="4" /><br />장바구니<?php if($TPL_VAR["push_count_cart"]){?><span class="pushCount" style="position:absolute;top:0px;right:-4px;"><?php echo $TPL_VAR["push_count_cart"]?></span><?php }?></a></li>
	<li><a href="/goods/recently"><img src="/data/skin/mobile_ver2_default/images/design/t_i_lately.png" width="25" height="24" vspace="4" /><br />최근 본 상품<?php if($TPL_VAR["push_count_today"]){?><span class="pushCount" style="position:absolute;top:0px;right:0px;"><?php echo $TPL_VAR["push_count_today"]?></span><?php }?></a></li>
</ul>

<div class="top_userinformation">
<?php if($TPL_VAR["userInfo"]["member_seq"]){?>
	<div class="welcome"><b><?php if($TPL_VAR["userInfo"]["user_name"]){?><?php echo $TPL_VAR["userInfo"]["user_name"]?><?php }else{?><?php echo $TPL_VAR["userInfo"]["userid"]?><?php }?></b>님 반갑습니다.</div>
	<div class="logoutbtn"><a href="../login_process/logout" target= "actionFrame"><input type="button" value="로그아웃" /></a></div>
<?php }else{?>
	<div class="welcome">고객님 반갑습니다.</div>
	<div class="loginbtn"><a href="../member/login"><input type="button" value="로그인" /></a></div>
	<div class="joinbtn"><a href="../member/agreement"><input type="button" value="회원가입" /></a></div>
<?php }?>
</div>

<div class="menu_navigation_wrap">
	<ul class="menu">
		<li class="mitem mitemicon1">
			<a href="#"><img src="/data/skin/mobile_ver2_default/images/design/l_i_cate.png" width="25" height="25" /> 카테고리</a>
			<ul class="submenu">
<?php if(is_array($TPL_R1=getChildCategory('',true,''))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
				<li class="submitem"><a href="/goods/catalog?code=<?php echo $TPL_V1["category_code"]?>"><?php echo $TPL_V1["ori_title"]?></a></li>
<?php }}?>
			</ul>
		</li>
		<li class="mitem mitemicon1">
			<a href="#"><img src="/data/skin/mobile_ver2_default/images/design/l_i_brand.png" width="25" height="25" /> 브랜드</a>
			<ul class="submenu">
<?php if(is_array($TPL_R1=getChildBrand('',true,''))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
				<li class="submitem"><a href="/goods/brand?code=<?php echo $TPL_V1["category_code"]?>"><?php echo $TPL_V1["ori_title"]?></a></li>
<?php }}?>
			</ul>
		</li>
		<li class="mitem mitemicon1">
			<a href="#"><img src="/data/skin/mobile_ver2_default/images/design/l_i_location.png" width="25" height="25" /> 지역</a>
			<ul class="submenu">
<?php if(is_array($TPL_R1=getChildLocation('',true,''))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
				<li class="submitem"><a href="/goods/location?code=<?php echo $TPL_V1["location_code"]?>"><?php echo $TPL_V1["ori_title"]?></a></li>
<?php }}?>
			</ul>
		</li>
		<li class="mitem mitemicon2"><a href="/goods/search"><img src="/data/skin/mobile_ver2_default/images/design/l_i_search.png" width="25" height="25" /> 검색</a></li>
		<li class="mitem mitemicon2"><a href="/order/cart"><img src="/data/skin/mobile_ver2_default/images/design/l_i_cart.png" width="25" height="25" /> 장바구니</a><?php if($TPL_VAR["push_count_cart"]){?><span class="pushCount" style="position:absolute;top:12px;right:27px;"><?php echo $TPL_VAR["push_count_cart"]?></span><?php }?></li>
		<li class="mitem mitemicon2"><a href="/goods/recently"><img src="/data/skin/mobile_ver2_default/images/design/l_i_lately.png" width="25" height="25" /> 최근 본 상품</a><?php if($TPL_VAR["push_count_today"]){?><span class="pushCount" style="position:absolute;top:12px;right:27px;"><?php echo $TPL_VAR["push_count_today"]?></span><?php }?></li>
		<li class="mitem mitemicon2"><a href="/mypage/wish"><img src="/data/skin/mobile_ver2_default/images/design/l_i_wish.png" width="25" height="25" /> 위시리스트</a><?php if($TPL_VAR["push_count_wish"]){?><span class="pushCount" style="position:absolute;top:12px;right:27px;"><?php echo $TPL_VAR["push_count_wish"]?></span><?php }?></li>
		<li class="mitem mitemicon2"><a href="/mypage/index"><img src="/data/skin/mobile_ver2_default/images/design/l_i_mypage.png" width="25" height="25" /> 마이페이지</a></li>
		<li class="mitem mitemicon2"><a href="/service/cs"><img src="/data/skin/mobile_ver2_default/images/design/l_i_cs.png" width="25" height="25" /> 고객센터</a></li>
	</ul>
</div>