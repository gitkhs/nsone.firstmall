<?php /* Template_ 2.2.6 2015/01/19 14:39:37 /www/nsone_firstmall_kr/admin/skin/default/_modules/layout/header.html 000026536 */ 
$TPL_adminMenu_1=empty($TPL_VAR["adminMenu"])||!is_array($TPL_VAR["adminMenu"])?0:count($TPL_VAR["adminMenu"]);
$TPL_adminMenu2_1=empty($TPL_VAR["adminMenu2"])||!is_array($TPL_VAR["adminMenu2"])?0:count($TPL_VAR["adminMenu2"]);?>
<?php $this->print_("common_html_header",$TPL_SCP,1);?>


<script type="text/javascript">

<?php if($TPL_VAR["managerInfo"]["manager_seq"]){?>
setTimeout(function(){
	loadIssueCounts();
},500);
<?php }?>

$(function(){

	$(".platformhelp").poshytip({
		className: 'tip-darkgray',
		bgImageFrameSize: 8,
		alignTo: 'target',
		alignX: 'up',
		alignY: 'bottom',
		offsetX: -55,
		offsetY: 6,
		allowTipHover: false,
		slide: false,
		showTimeout : 0
	});

	// 매뉴얼 버튼 링크
	$("#page-title-bar").each(function(){
<?php if($TPL_VAR["admin_menual_hidden"]){?> return; <?php }?>

		$(".page-manual-btn<?php echo $TPL_VAR["goods_quick_topmenu"]?>")
		.appendTo($(this))
		.show()
		.children("a")
		.attr('href','http://manual.firstmall.kr/html/manual.php?url=<?php echo $TPL_VAR["admin_menual_url"]?>');
	});
});

</script>


<body>

<!-- 매뉴얼 버튼 -->
<div class="page-manual-btn<?php echo $TPL_VAR["goods_quick_topmenu"]?> hide">
	<a href="#" target="_blank"><span class="hide">매뉴얼보기</span></a>
</div>

<div id="wrap">

	<div id="layout-container" class="<?php echo $TPL_VAR["service_code"]?>"><!-- free premium expantion proexpantion -->

		<!--[ 레이아웃 헤더 : 시작 ]-->
		<div id="layout-header" <?php if($TPL_VAR["managerInfo"]["gnb_icon_view"]!='n'){?>class="icon-view"<?php }?>>

			<!-- 헤더 상단부 -->
			<div class="header-snb-container clearbox">

				<a href="/admin"><h1 class="header-logo"><span>Firstmall</span></h1></a>

<?php if($TPL_VAR["managerInfo"]["manager_seq"]&&$TPL_VAR["config_system"]["mall_auth_yn"]!='y'){?>
				<div class="header-notice bold" style="margin-top:9px;"><a href='http://firstmall.kr/myshop/spec/manager_information.php?num=<?php echo $TPL_VAR["config_system"]["shopSno"]?>' target='_blank'><img src="/admin/skin/default/images/design/mall_auth_msg.png" /></a></div>
<?php }?>

				<!--<p class="header-notice">[공지] My가비아 내 쇼핑몰관리 신설</p>-->

				<form name="headForm" id="headForm" class="header-search" action="/admin/order/catalog">

<?php if(!$TPL_VAR["managerInfo"]["manager_seq"]){?>
					<div style="top:0px;position:absolute;width:290px;border:1px solid #000000;height:26px;z-index:1000;background-color:#ffffff;filter:alpha(opacity:0);"></div>
<?php }?>

					<span class="fl hs-box">
						<select name="hsb_kind" class="hsb-kind">
							<option value="order" <?php if($_GET["hsb_kind"]=='order'){?>selected<?php }?>>주문</option>
							<option value="export" <?php if($_GET["hsb_kind"]=='export'){?>selected<?php }?>>출고</option>
<?php if($TPL_VAR["config_system"]["service"]["code"]!='P_STOR'){?>
							<option value="goods" <?php if($_GET["hsb_kind"]=='goods'){?>selected<?php }?>>상품</option>
<?php }?>
<?php if(in_array($TPL_VAR["config_system"]["service"]["code"],array('P_STOR','P_EXPA','P_ADVA'))){?>
							<option value="goods" <?php if($_GET["hsb_kind"]=='coupon'){?>selected<?php }?>>쿠폰</option>
<?php }?>
							<option value="member" <?php if($_GET["hsb_kind"]=='member'){?>selected<?php }?>>회원</option>
						</select>
						<input type="text" name="header_search_keyword" value="<?php echo $_GET["header_search_keyword"]?>" />
					</span>
					<span class="fl" style="width:27px;height:27px; background-color:#fff; ;"><input type="image" src="/admin/skin/default/images/main/search_zoom.gif" style="margin:5px;" /></span>
					<div class="relative">
					<div class="absolute" style="top:5px; left:300px;"><img src="/admin/skin/default/images/main/q_icon.png" align="absmiddle" id="search_help" class="hand" /></div>
					<div class="absolute hide" id="search_information">
					<div class="center pdb10 fn12 bold">아래의 정의된 검색어로 빠르게 검색하세요</div>
					<table class="simplelist-table-style" style="width:100%; table-layout:fixed">
						<tr>
							<th class="bold">주문 → 주문리스트</th>
							<th class="bold">출고 → 출고리스트</th>
							<th class="bold">상품 → 실물 배송 상품</th>
							<th class="bold">쿠폰 → 쿠폰 발송 상품</th>
							<th class="bold">회원  → 회원리스트</th>
						</tr>
						<tr>
							<td style="border:1px solid #c8c8c8;" valign="top">
								<div class="pdl5">· 주문자</div>
								<div class="pdl5 pdt5">· 수령자</div>
								<div class="pdl5 pdt5">· 입금자</div>
								<div class="pdl5 pdt5">· 아이디</div>
								<div class="pdl5 pdt5">· 이메일</div>
								<div class="pdl5 pdt5">· 휴대폰</div>
								<div class="pdl5 pdt5">· 상품명</div>
								<div class="pdl5 pdt5">· 상품 고유값</div>
								<div class="pdl5 pdt5">· 상품코드</div>
								<div class="pdl5 pdt5">· 사은품</div>
								<div class="pdl5 pdt5">· 운송장번호</div>
								<div class="pdl5 pdt5">· 주문번호</div>
								<div class="pdl5 pdt5">· 출고번호</div>
								<div class="pdl5 pdt5">· 반품번호</div>
								<div class="pdl5 pdt5">· 환불번호</div>
							</td>
							<td style="border:1px solid #c8c8c8;" valign="top">
								<div class="pdl5">· 주문자</div>
								<div class="pdl5 pdt5">· 수령자</div>
								<div class="pdl5 pdt5">· 입금자</div>
								<div class="pdl5 pdt5">· 아이디</div>
								<div class="pdl5 pdt5">· 이메일</div>
								<div class="pdl5 pdt5">· 휴대폰</div>
								<div class="pdl5 pdt5">· 상품명</div>
								<div class="pdl5 pdt5">· 상품 고유값</div>
								<div class="pdl5 pdt5">· 상품코드</div>
								<div class="pdl5 pdt5">· 사은품</div>
								<div class="pdl5 pdt5">· 운송장번호</div>
								<div class="pdl5 pdt5">· 주문번호</div>
								<div class="pdl5 pdt5">· 출고번호</div>
								<div class="pdl5 pdt5">· 반품번호</div>
								<div class="pdl5 pdt5">· 환불번호</div>
							</td>
							<td style="border:1px solid #c8c8c8;" valign="top">
								<div class="pdl5">· 상품명</div>
								<div class="pdl5 pdt5">· 상품 고유값</div>
								<div class="pdl5 pdt5">· 상품코드</div>
								<div class="pdl5 pdt5">· 태그</div>
								<div class="pdl5 pdt5">· 간략설명</div>
							</td>
							<td style="border:1px solid #c8c8c8;" valign="top">
								<div class="pdl5">· 상품명</div>
								<div class="pdl5 pdt5">· 상품 고유값</div>
								<div class="pdl5 pdt5">· 상품코드</div>
								<div class="pdl5 pdt5">· 태그</div>
								<div class="pdl5 pdt5">· 간략설명</div>
							</td>
							<td style="border:1px solid #c8c8c8;" valign="top">
								<div class="pdl5">· 아이디</div>
								<div class="pdl5 pdt5">· 회원명</div>
								<div class="pdl5 pdt5">· 닉네임</div>
								<div class="pdl5 pdt5">· 이메일</div>
								<div class="pdl5 pdt5">· 주소</div>
								<div class="pdl5 pdt5">· 전화번호</div>
								<div class="pdl5 pdt5">· 핸드폰</div>
							</td>
						</tr>
					</table>
					</div>
					</div>
					<script type="text/javascript">
					$("#search_help").bind("click",function(){
						openDialog("빠른 검색", "search_information", {"width":800});
					});/*.bind("mouseover",function(){
						$(this).attr('src','/admin/skin/default/images/common/btn_srch_q_ov.png');
					}).bind("mouseout",function(){
						$(this).attr('src','/admin/skin/default/images/common/btn_srch_q.png');
					});*/
					</script>
				</form>
				<ul class="header-snb clearbox">
					<li class="item">· <a href="javascript:<?php if($TPL_VAR["service_code"]=='P_FREE'){?>alert('맞춤개발서비스는 프리미엄몰Plus+와 독립몰Plus+에서만 적용됩니다.'); <?php }?>void(window.open('https://firstmall.kr/ec_hosting/customize/write.php?code=etc'));"><b>맞춤개발</b></a></li>
					<li class="item">· <a href="http://firstmall.kr/myshop/" target="_blank">My가비아</a></li>
					<li class="item">· <a href="http://firstmall.kr/ec_hosting/customer/1to1.php" target="_blank">1:1문의</a></li>
					<li class="item header-snb-item-manual">· <a href="http://manual.firstmall.kr/html/manual.php" target="_blank">매뉴얼</a>
						<ul class="header-snb-item-manual-subnb">
							<li class="header-snb-item-manual-subnb-item"><a href="http://manual.firstmall.kr/html/manual.php" target="_blank"><span>온라인 매뉴얼</span></a></li>
							<li class="header-snb-item-manual-subnb-item"><a href="http://interface.firstmall.kr/firstmall_plus/data/manual/firstmall_manual.zip"><span>매뉴얼 다운로드</span></a></li>
							<li class="header-snb-item-manual-subnb-item"><a href="#" onclick="show_simple_manual('menu')"><span>빠른매뉴얼-관리</span></a></li>
							<li class="header-snb-item-manual-subnb-item"><a href="#" onclick="show_simple_manual('setting')"><span>빠른매뉴얼-설정</span></a></li>
							<li class="header-snb-item-manual-subnb-item"><a href="#" onclick="show_simple_manual('design')"><span>빠른매뉴얼-디자인</span></a></li>
						</ul>
					</li>
					<li class="item">·
<?php if($TPL_VAR["config_system"]["webmail_domain"]){?>
						<a href="http://webmail.<?php echo $TPL_VAR["config_system"]["webmail_domain"]?>/" target="_blank">웹메일/세금계산서</a>
<?php }else{?>
						<a href="javascript:openDialogAlert('하이웍스를 신청하지 않으셨거나  쇼핑몰과 별도로 신청을 하셨습니다.<br/>쇼핑몰과 별도로 신청을 하셨다면 퍼스트몰 고객센터 1544-3270 으로 문의주시길 바랍니다.<br/><br/>하이웍스를 신청하려면 My가비아><a href=\'http://firstmall.kr/myshop\' target=\'_blank\'><span class=\'highlight-link\'>쇼핑몰관리</span></a> 에서 할 수 있습니다.',600,200,function(){});">웹메일/세금계산서</a>
<?php }?>
					</li>
<?php if($TPL_VAR["managerInfo"]["manager_seq"]){?>
					<li class="item">
						<div class="hsnb-manager">
							<span class="hsnbm-name">
								<img src=<?php if($TPL_VAR["managerInfo"]["mphoto"]){?>"../../../data/icon/manager/<?php echo $TPL_VAR["managerInfo"]["mphoto"]?>"<?php }else{?>"/admin/skin/default/images/main/myprofile_icon.png"<?php }?> width="26" height="26" align="absmiddle" />
								<?php echo $TPL_VAR["managerInfo"]["manager_id"]?>

							</span>
							<div class="hsnbm-menu">
								<img src="/admin/skin/default/images/main/point_c.png" style="position:absolute;left:190px; top:-5px;"/>
								<table class="tb_admin_info" cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td rowspan="2" style="width:65px; padding-left:5px;">
										<img src=<?php if($TPL_VAR["managerInfo"]["mphoto"]){?>"../../../data/icon/manager/<?php echo $TPL_VAR["managerInfo"]["mphoto"]?>"<?php }else{?>"/admin/skin/default/images/main/def_img.png"<?php }?> width="54" height="54" align="absmiddle" />
									</td>
									<td style="padding-left:15px;">
										<span style="font-weight:bold; font-size:14px; color:#5d5d65;font-family:tahoma;"><?php echo $TPL_VAR["managerInfo"]["manager_id"]?></span>&nbsp;<span style="font-size:12px; color:#5d5d65;">(<?php echo $TPL_VAR["managerInfo"]["mname"]?>)</span>
									</td>
								</tr>
								<tr>
									<td style="padding-left:15px;">
										<span style="font-size:12px; color:#348ddb;">
<?php if($TPL_VAR["managerInfo"]["manager_yn"]=='Y'){?>대표운영자<?php }else{?>부운영자<?php }?>
										</span>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="tb_bottom_line" >&nbsp;</td>
								</tr>
								<tr>
									<td colspan="2" style="padding-top:8px;">
										<a href="../setting/manager_reg?manager_seq=<?php echo $TPL_VAR["managerInfo"]["manager_seq"]?>"><img src="/admin/skin/default/images/main/my_pbt01.gif" /></a>
										<a href="../login_process/logout"><img src="/admin/skin/default/images/main/my_pbt02.gif" /></a>
									</td>
								</tr>
								</table>
							</div>
						</div>
					</li>
<?php }?>
				</ul>

			</div>

			<!-- 헤더 네비게이션바 -->
			<table class="header-gnb-container" border="0" cellspacing="0">
<?php if(!$TPL_VAR["managerInfo"]["manager_seq"]){?>
			<div id="header-gnb-cover" style="top:0px;position:absolute;width:100%;border:1px solid #000000;height:98px;z-index:100000;background-color:#ffffff;"></div>
			<script>$("#header-gnb-cover").css('opacity',0);</script>
<?php }?>
			<tr>
				<td class="mitem-menu-icon-view">
					<span class="hide">메뉴스타일변경</span>
				</td>
				<td class="mitem-menu-all">
					<span class="top_menu"><a>전체보기</a></span>
				</td>
				<td>
					<table class="header-gnb" align="center" cellspacing="0">
					<tr>
						<td class="mitem-td <?php if($TPL_VAR["adminMenuCurrent"]=='main'){?>current<?php }?>">
							<span class="mitem mitem_main"><a href="../main/index"><span>홈</span></a></span>
						</td>
<?php if($TPL_adminMenu_1){foreach($TPL_VAR["adminMenu"] as $TPL_K1=>$TPL_V1){?>
						<td class="mitem-td <?php if(in_array($TPL_VAR["adminMenuCurrent"],$TPL_V1["folders"])){?>current<?php }?>" code="<?php echo $TPL_K1?>">
							<div class="header-gnb-issueCount-layer" code="<?php echo $TPL_K1?>"></div>
							<span class="mitem mitem_<?php echo $TPL_K1?>"><a href="<?php echo $TPL_V1["childs"][ 0]["url"]?>"><span><?php echo $TPL_V1["childs"][ 0]["name"]?></span></a></span>
<?php if($TPL_V1["childs"][ 1]){?>
							<div class="submenu-wrapper">
								<div class="submenu">
									<table cellpadding="0" cellspacing="0">
									<tr>

										<td valign="top">
											<ul>
<?php if(is_array($TPL_R2=$TPL_V1["childs"])&&!empty($TPL_R2)){$TPL_I2=-1;foreach($TPL_R2 as $TPL_V2){$TPL_I2++;?>
<?php if($TPL_I2> 0){?>
<?php if($TPL_VAR["service_code"]=='P_FREE'&&($TPL_V2["name"]=='프로모션통계'||$TPL_V2["name"]=='상품데이터 일괄업데이트'||$TPL_V2["name"]=='재입고알림 상품리스트'||$TPL_V2["name"]=='개인결제리스트'||$TPL_V2["name"]=='사은품리스트'||$TPL_V2["name"]=='사은품 이벤트')){?>
											<li class="nofreelinknone"><a href="#"><?php echo $TPL_V2["name"]?></a></li>

<?php }elseif($TPL_VAR["service_code"]=='P_FREE'&&($TPL_V2["url"]=='../goods/social_catalog'||$TPL_V2["url"]=='../location/catalog'||$TPL_V2["url"]=='../statistic_goods')){?>
											<li class="nofreelinknone"><a href="#"><?php echo $TPL_V2["name"]?></a></li>

<?php }elseif($TPL_VAR["service_code"]=='P_PREM'&&$TPL_V2["url"]=='../goods/social_catalog'){?>
											<li class="nolinknone"><a href="#"><?php echo $TPL_V2["name"]?></a></li>

<?php }elseif($TPL_VAR["service_code"]=='P_FAMM'&&$TPL_V2["url"]=='../goods/social_catalog'){?>
											<li class="nolinknone"><a href="#"><?php echo $TPL_V2["name"]?></a></li>

<?php }elseif($TPL_VAR["service_code"]=='P_STOR'&&($TPL_V2["url"]=='../goods/catalog'||$TPL_V2["url"]=='../goods/gift_catalog'||$TPL_V2["url"]=='../goods/batch_modify'||$TPL_V2["url"]=='../goods/restock_notify_catalog'||$TPL_V2["url"]=='../event/gift_catalog'||$TPL_V2["url"]=='../promotion/catalog'||$TPL_V2["url"]=='../referer/index'||$TPL_V2["url"]=='../event/gift_catalog'||$TPL_V2["url"]=='../statistic_goods'||$TPL_V2["url"]=='../order/personal')){?>
											<li class="noshoplinknone"><a href="#"><?php echo $TPL_V2["name"]?></a></li>
<?php }else{?>
											<li><a href="<?php echo $TPL_V2["url"]?>"><?php echo $TPL_V2["name"]?></a></li>
<?php }?>

<?php if(count($TPL_V1["childs"])>= 10&&$TPL_I2%$TPL_VAR["adminMenuLimit"]== 0){?>
											</ul>
										</td>
										<td valign="top">
											<ul>
<?php }?>
<?php }?>
<?php }}?>
											</ul>
										</td>
									</tr>
									</table>
								</div>
							</div>
<?php }?>
						</td>
<?php }}?>
					</tr>
					</table>
				</td>
				<td>
					<ul class="header-qnb">
						<!--li class="gnb-item gnb-benifit gnb-benifit-off"><a class="gnb-item-a"><span>혜택설정바로가기</span></a></li-->
						<li class="gnb-item qnb-bigdata <?php if($TPL_VAR["adminMenuCurrent"]=='bigdata'){?>current<?php }?> <?php if($TPL_VAR["service_code"]=='P_FREE'){?>nofreelinknone<?php }?>">
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
							<a href="#" class="gnb-item-a"><span>빅데이터</span></a>
<?php }else{?>
							<a href="../bigdata/catalog" class="gnb-item-a"><span>빅데이터</span></a>
							<ul class="gnb-subnb">
								<li class="gnb-subnb-item"><a href="../bigdata/catalog" style="width:120px;">빅데이터 설정</a></li>
								<li class="gnb-subnb-item"><a href="../bigdata/preview" style="width:120px;">빅데이터 검색</a></li>
							</ul>
<?php }?>
						</li>
						<li class="gnb-item qnb-openmarket <?php if($TPL_VAR["adminMenuCurrent"]=='openmarket'){?>current<?php }?>">
							<a href="../openmarket/index" class="gnb-item-a"><span>판매마켓</span></a>
							<ul class="gnb-subnb">
								<li class="gnb-subnb-item"><a href="../openmarket/regist" style="width:120px;">판매마켓 신청</a></li>
								<li class="gnb-subnb-item"><a href="../openmarket/config" style="width:120px;">판매마켓 설정</a></li>
								<li class="gnb-subnb-item"><a href="../openmarket/category" style="width:120px;">카테고리 매칭</a></li>
							</ul>
						</li>
						<li class="gnb-item qnb-config <?php if($TPL_VAR["adminMenuCurrent"]=='setting'){?>current<?php }?>">
							<a href="../setting/index" class="gnb-item-a"><span>설정</span></a>
							<ol class="gnb-subnb" type="1">
								<li class="gnb-subnb-item"><a href="../setting/config">판매환경</a></li>
								<li class="gnb-subnb-item basic"><a href="../setting/basic">일반정보</a></li>
								<li class="gnb-subnb-item"><a href="../setting/snsconf">SNS/외부연동</a></li>
								<li class="gnb-subnb-item"><a href="../setting/operating">운영방식</a></li>
								<li class="gnb-subnb-item pg"><a href="../setting/pg">전자결제</a></li>
								<li class="gnb-subnb-item bank"><a href="../setting/bank">무통장</a></li>
								<li class="gnb-subnb-item"><a href="../setting/member">회원</a></li>
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
									<li class="gnb-subnb-item nofreelinknone"><a href="#">상품 코드/정보</a></li>
<?php }else{?>
									<li class="gnb-subnb-item"><a href="../setting/goods">상품 코드/정보</a></li>
<?php }?>
								<li class="gnb-subnb-item"><a href="../setting/search">상품/주소 검색</a></li>
								<li class="gnb-subnb-item"><a href="../setting/video">동영상</a></li>
								<li class="gnb-subnb-item"><a href="../setting/order">주문</a></li>
								<li class="gnb-subnb-item"><a href="../setting/sale">매출증빙</a></li>
								<li class="gnb-subnb-item"><a href="../setting/reserve" style="width:120px;">적립금/포인트/이머니</a></li>
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
									<li class="gnb-subnb-item noshoplinknone"><a href="#">택배/배송비</a></li>
<?php }else{?>
									<li class="gnb-subnb-item shipping"><a href="../setting/shipping">택배/배송비</a></li>
<?php }?>
								<li class="gnb-subnb-item"><a href="../setting/protect">보안</a></li>
								<li class="gnb-subnb-item"><a href="../setting/manager">관리자</a></li>
							</ol>
						</li>
						<li class="gnb-item qnb-design <?php if($TPL_VAR["adminMenuCurrent"]=='design'){?>current<?php }?>">
							<a href="../design/index" class="gnb-item-a"><span>디자인</span></a>
							<ul class="gnb-subnb">
								<li class="gnb-subnb-item"><a href="../design/skin">스킨 설정</a></li>
								<li class="gnb-subnb-item"><a href="../design/font">폰트 설정</a></li>
								<!-- <li class="gnb-subnb-item"><a href="../design/codes">치환코드</a></li> -->
<?php if($TPL_VAR["service_code"]!='P_STOR'){?>
<?php if($TPL_VAR["service_code"]!='P_FAMM'){?>
										<li class="gnb-subnb-item"><a href="../design/main?setMode=pc" target="_blank">디자인환경 (PC)</a></li>
										<li class="gnb-subnb-item"><a href="../design/main?setMode=mobile" target="_blank">디자인환경 (Mobile)</a></li>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
										<li class="gnb-subnb-item"><a><span  id="freefacebookconfignone">디자인환경 (Facebook)</span></a></li>
<?php }else{?>
										<li class="gnb-subnb-item"><a href="../design/main?setMode=fammerce" target="_blank">디자인환경 (Facebook)</a></li>
<?php }?>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
								<li class="gnb-subnb-item "><a href="../design/main?setMode=store" target="_blank">디자인환경 (PC)</a></li>
								<li class="gnb-subnb-item "><a href="../design/main?setMode=storemobile" target="_blank">디자인환경 (Mobile)</a></li>
								<li class="gnb-subnb-item "><a href="../design/main?setMode=storefammerce" target="_blank">디자인환경 (Facebook)</a></li>
<?php }?>
								<li class="gnb-subnb-item"><a href="#" onclick="DM_window_eyeeditor('data/skin/<?php echo $TPL_VAR["designWorkingSkin"]?>/main/index.html')">HTML 에디터</a></li>
							</ul>
						</li>
						<li class="gnb-item qnb-goshop">
							<a href="#" class="gnb-item-a"><span>바로가기</span></a>
							<ul class="gnb-subnb">
<?php if($TPL_VAR["service_code"]!='P_STOR'){?>
<?php if($TPL_VAR["service_code"]!='P_FAMM'){?>
										<li class="gnb-subnb-item"><a href="http://<?php echo $TPL_VAR["pcDomain"]?>/?setDesignMode=off&setMode=pc" target="_blank">PC</a></li>
										<li class="gnb-subnb-item"><a href="http://<?php echo $TPL_VAR["mobileDomain"]?>/?setDesignMode=off&setMode=mobile" target="_blank">Mobile</a></li>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
										<li class="gnb-subnb-item"><a><span id="freefacebookconfignone">Facebook</span></a></li>
<?php }else{?>
<?php if($TPL_VAR["facebookConnected"]){?>
											<li class="gnb-subnb-item"><a href="<?php echo $TPL_VAR["facebookapp_url"]?>" target="_blank">Facebook</a></li>
<?php }else{?>
											<li class="gnb-subnb-item"><a href="/admin/setting/config" target="_blank">Facebook</a></li>
<?php }?>
<?php }?>
<?php }?>
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
								<li class="gnb-subnb-item "><a href="http://<?php echo $TPL_VAR["pcDomain"]?>/?setDesignMode=off&setMode=store" target="_blank">PC</a></li>
								<li class="gnb-subnb-item "><a href="http://<?php echo $TPL_VAR["mobileDomain"]?>/?setDesignMode=off&setMode=storemobile" target="_blank">Mobile</a></li>
<?php if($TPL_VAR["facebookConnected"]){?>
										<li class="gnb-subnb-item"><a href="<?php echo $TPL_VAR["facebookapp_url"]?>" target="_blank">Facebook</a></li>
<?php }else{?>
										<li class="gnb-subnb-item"><a href="/admin/setting/config" target="_blank">Facebook</a></li>
<?php }?>
<?php }?>
							</ul>
						</li>
					</ul>
				</td>
			</tr>
			</table>

			<!--[ 혜택설정바로가기 : 시작 ]-->
			<div class="relative">
				<div class="benifit-popup hide"></div>
			</div>
			<!--[ 혜택설정바로가기 : 끝 ]-->
			<!--[ 관리자 메뉴 전체 보기 : 시작 ]-->
			<div class="relative">
				<div class="header-menu-all hide">
					<div class="header-menu-all-title">
						<span class="title-text-area">메뉴에 마우스를 올려보세요.</span>
						<span class="title-text-default">메뉴에 마우스를 올려보세요.</span>
						<img src="/admin/skin/default/images/common/btn_close_big.gif" class="menu-all-close-btn" />
					</div>
					<table class="header-menu-table" width="100%">
					<tr>
<?php if(is_array($TPL_R1=$TPL_VAR["adminMenu2"]["menu_titles"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
						<th>
<?php if($TPL_V1["url"]){?>
							<a href="<?php echo $TPL_V1["url"]?>"><?php echo $TPL_V1["name"]?></a>
<?php }else{?>
							<?php echo $TPL_V1["name"]?>

<?php }?>
						</th>
<?php }}?>
					</tr>
					<tr>
<?php if($TPL_adminMenu2_1){$TPL_I1=-1;foreach($TPL_VAR["adminMenu2"] as $TPL_V1){$TPL_I1++;?>
<?php if($TPL_I1> 0){?>
						<td>
<?php if(is_array($TPL_R2=$TPL_V1)&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
							<li><span class="menu-item" lines="<?php echo $TPL_V2["lines"]?>">
<?php if(preg_match('/^P_FAMM/',$TPL_V2["etype"])){?>
<?php if($TPL_VAR["service_code"]=='P_FAMM'){?>
<?php if($TPL_V2["etype"]=='P_FAMM1'){?>
											<a href="<?php echo $TPL_V2["url"]?>"><?php echo $TPL_V2["name"]?></a>
<?php }else{?>
											<span  id="<?php echo $TPL_V2["url"]?>" ><?php echo $TPL_V2["name"]?></span>
<?php }?>
<?php }else{?>
<?php if($TPL_V2["etype"]=='P_FAMM3'){?>
											<a href="http://<?php echo $TPL_VAR["pcDomain"]?>/?setDesignMode=off&setMode=pc"><?php echo $TPL_V2["name"]?></a>
<?php }elseif($TPL_V2["etype"]=='P_FAMM4'){?>
											<a href="http://<?php echo $TPL_VAR["mobileDomain"]?>/?setDesignMode=off&setMode=mobile"><?php echo $TPL_V2["name"]?></a>
<?php }else{?>
											<a href="<?php echo $TPL_V2["url2"]?>"><?php echo $TPL_V2["name"]?></a>
<?php }?>
<?php }?>
<?php }elseif($TPL_V2["etype"]=='P_FREE'){?>
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
										<span  id="<?php echo $TPL_V2["url"]?>" ><?php echo $TPL_V2["name"]?></span>
<?php }else{?>
										<a href="<?php echo $TPL_V2["url2"]?>"><?php echo $TPL_V2["name"]?></a>
<?php }?>
<?php }elseif($TPL_V2["etype"]=='FACEBOOK'){?>
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
										<span  id="<?php echo $TPL_V2["url"]?>" ><?php echo $TPL_V2["name"]?></span>
<?php }else{?>
<?php if($TPL_VAR["facebookConnected"]){?>
										<a href="<?php echo $TPL_VAR["facebookapp_url"]?>"><?php echo $TPL_V2["name"]?></a>
<?php }?>
<?php }?>
<?php }else{?>
								<a href="<?php echo $TPL_V2["url"]?>"><?php echo $TPL_V2["name"]?></a>
<?php }?>
								<span class="menu-alt"> - <?php echo $TPL_V2["alt"]?></span>
							</span>
<?php if($TPL_V2["required"]=='Y'){?>
							<img src="/admin/skin/default/images/common/icon_must3.gif" class="menu-all-must-img" />
<?php }?>
							</li>
<?php }}?>
						</td>
<?php }?>
<?php }}?>
					</tr>
					</table>
<?php if($TPL_VAR["managerInfo"]["manager_seq"]&& 1== 0){?>
					<div class="gabia-pannel top-menu-all-banner" code="top_menu_all_banner"></div>
<?php }?>
				</div>
			</div>
			<!--[ 관리자 메뉴 전체 보기 : 끝 ]-->


		</div>
		<!--[ 레이아웃 헤더 : 끝 ]-->

		<div id="layout-body">
		<!--[ 레이아웃 바디(본문) : 시작 ]-->