<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/main/index.html 000023752 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<link rel="stylesheet" type="text/css" href="/admin/skin/default/css/main.css?dummy=20131015" />

<script type="text/javascript" src="/app/javascript/js/admin-board.js?v=20140823"></script>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/editor_loader.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/editor/js/daum_editor_loader.js"></script>
<script type="text/javascript">
$(function(){
	/* sms ajax 호출 leewh 2014-07-31 */
	get_sms_info();

	/* traffic ajax 호출 :: 2015-01-09 lwh */
	reload_data('main');

	/* 공지사항 / 업그레이드 ajax 호출 :: 2015-01-09 lwh */
	contents_pannel_load('notice');
	contents_pannel_load('upgrade');

	/* 요약통계 업데이트 */
	$('#btn-main-reload').bind('click', function(){
		var last_reload		= '<?php echo $TPL_VAR["last_reload"]* 1000?>';
		var reload_status	= 'n';
		if	(last_reload){
			var last_time		= new Date(last_reload).valueOf();
			var now_datetime	= new Date().valueOf();
			var diff_time		= now_datetime - last_reload;
			if	(diff_time > 60000){
				reload_status	= 'y';
			}
		}

		if	(reload_status == 'y'){
			$("iframe[name='actionFrame']").attr('src', 'main_stats_cach_delete');
		}else{
			openDialogAlert("[지금업데이트하기]는 60초당 1회 가능합니다.<br/>운영쇼핑몰의 상세한 통계 데이터는 통계메뉴에 확인 해 주세요.",530,150,'');
		}
	});

	// 1:1문의 게시글 보기
	$('span.mbqna_boad_view_btn').live('click', function() { //
		var board_seq = $(this).attr('board_seq');
		var boardviewurl = '../board/mbqna_view?id=mbqna&mainview=1&seq='+board_seq;
		boardaddFormDialog(boardviewurl, '80%', '800', '게시글 보기','false');
	});

	// 상품문의 게시글 보기
	$('span.goods_qna_boad_view_btn').live('click', function() {
		var board_seq = $(this).attr('board_seq');
		var boardviewurl = '../board/goods_qna_view?id=goods_qna&mainview=1&seq='+board_seq;
		boardaddFormDialog(boardviewurl, '80%', '800', '게시글 보기','false');
	});

	// 예약문의 답변하기
	$('span.store_reservation_boad_view_btn').live('click', function() { //
		var board_seq = $(this).attr('board_seq');
		var boardviewurl = '../board/store_reservation_write?id=store_reservation&mainview=1&reply=y&seq='+board_seq;
		boardaddFormDialog(boardviewurl, '80%', '800', '게시글 보기','false');
	});

<?php if(!$TPL_VAR["cfg_reservation"]["update_date"]){?>
		reservation_update();
<?php }?>

<?php if($TPL_VAR["config_system"]["mall_auth_yn"]!='y'){?>
		mall_auth_alert();
<?php }?>

	// _main_caching();
});

function _main_caching(){
	$("iframe[name='actionFrame']").attr('src', 'main_caching');
}

/* 컨텐츠 개별 로딩 */
function contents_pannel_load(area){
	
	var areaContentsObj = $("#main-"+area+"-summary .main-summary-contents").length ? $("#main-"+area+"-summary .main-summary-contents") : $("#main-"+area+"-summary");
	areaContentsObj.empty().activity({segments: 8, width: 3.5, space: 1, length: 7, color: '#666', speed: 1.5});

	$.ajax({
		'url' : 'get_main_contents_pannel',
		'data' : {"area":area},
		'global' : false,
		'success' : function(html){
			areaContentsObj.html(html).activity(false);
		}
	});
}

// SMS 갯수 호출
function get_sms_info(){
	var areaSmsObj = $(".servicetxt_sms").length ? $(".servicetxt_sms") : '';
	areaSmsObj.empty().activity({segments: 8, steps: 3, align: 'right', width: 3.5, space: 1, length: 3, color: '#666', speed: 1.5});

	$.ajax({
		'url' : 'get_sms_info',
		'data' : {},
		'dataType' : 'json',
		'global' : false,
		'success' : function(result){
			if (result.html) {
				$(".myservice_area_sms").replaceWith(result.html);
			}

			if (result.txt_cnt) {
				areaSmsObj.html(result.txt_cnt).activity(false);
			}
		}
	});
}

// MY 서비스 마우스 오버시 효과 :: 2014-10-27 lwh
function service_over(num,desc){
	$(".myservice_area").removeClass('selhover');
	$(".area"+num).addClass('selhover');
	$("#desc_area").html(desc);
}

// 좌우측 체인지 버튼
function movingAnimation(){
	var duration	= 300;

	$(".btn-main-change-panel").hide();
	$(".main-middlebody-left").animate({opacity:0.4,left:'30%'}, duration );
	$(".main-middlebody-right").animate({opacity:0.4,left:'20%'}, duration );
	if	($(".main-middlebody-left").css("left").replace('px', '') > 0){
		$(".btn-main-change-panel").animate({opacity:0.4,left:'1017px'}, duration );
		$(".btn-main-change-panel").animate({opacity:1}, duration );

		$(".main-middlebody-left").animate({opacity:1,left:'0'}, duration );
		$(".main-middlebody-right").animate({opacity:1,left:'1040px'}, duration );
	}else{
		$(".btn-main-change-panel").animate({opacity:0.4,left:'870px'}, duration );
		$(".btn-main-change-panel").animate({opacity:1}, duration );

		$(".main-middlebody-left").animate({opacity:1,left:'890px'}, duration );
		$(".main-middlebody-right").animate({opacity:1,left:'0'}, duration );
	}
	$(".btn-main-change-panel").show();
}

//관리자 비밀번호 변경
function change_pass()
{
	var gdata = '';
	$.ajax({
		type: "get",
		url: "popup_change_pass",
		data: gdata,
		success: function(result){
			$("#popup_change_pass").html(result);
		}
	});
	openDialog("쇼핑몰 관리자 계정 비밀번호 변경 안내", "popup_change_pass", {"width":700,"height":350});
}

// 출고예약량 업데이트
function reservation_update(){
	openDialogAlert("상품의 출고예약량 업데이트중입니다.<br/>브라우저 창을 닫지 마시고<br>잠시만 기다려 주십시오.",400,150,function(){},{"hideButton":true, "noClose" : true,"modal":true});

	$.ajax({
	    url : '../goods_process/all_modify_reservation',
	    global : false,
	    success : function(data){if(data == 'OK'){
		    closeDialog('openDialogLayer');
		    openDialogAlert("상품의 출고예약량이 정상적으로 업데이트 되었습니다.",400,140,function(){},{"hideButton" : false});
	    }}
	});
}

// 대표자 개인정보 수집 및 실명인증 확인
function mall_auth_alert(){
	openDialogAlert("<div class='left'>퍼스트몰은 전자상거래법 제 9조 3항 및 제 11조 2항에 의거<br /> ‘호스팅 사업자의 신원확인의무’에 의해 개인 정보를 수집할 의무가 있습니다.<br />공정한 거래와 안전한 온라인 서비스 제공을 위해 쇼핑몰 대표자의<br /> 개인정보를 실명인증을 통해 수집합니다.<br /><b><font color='#d00000'>[기본사양관리 > 쇼핑몰관리자정보]</font></b>에서 인증절차를 진행 해 주시기 바랍니다.</div><div class='pdt10'><span class='btn large black'><a href='http://firstmall.kr/myshop/spec/manager_information.php?num=<?php echo $TPL_VAR["config_system"]["shopSno"]?>' target='_blank'>관리자 실명인증 확인하기</a></span></div>",530,210,function(){},{"hideButton" : true});
}

// 트래픽 용량 데이터 추출
function reload_data(type){
	$.ajax({
		'url' : '/admin/main/re_traffic_data',
		'data' : {'domain':'<?php echo $TPL_VAR["config_system"]["subDomain"]?>'},
		'global' : false,
		'success' : function(html){
			var info = html.split("|");
			if(type == 'main')	$('#traffic_area').show();
			else				$('#traffic_area').hide('blind').show('blind');
			$('#traffic_area').html("<b>" + info[1] + "</b> / " + info[0] + "(<b>" + info[2] + "% 사용중</b>)");
		}
	});
}

</script>

<div id="layout-background" <?php if($TPL_VAR["managerInfo"]["gnb_icon_view"]!='n'){?>class="icon-view"<?php }?>><div class="img_bg <?php echo $TPL_VAR["service_code"]?>"></div></div>

<div id="main-body">

	<!-- 좌측 영역 : START -->
	<div class="main-middlebody-left">
		<!-- 메인 카운터 바 -->
		<div class="main-middlearea-stats-bar left-bar" style="margin:0 auto; width:1013px;">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr><td align="center"><?php $this->print_("main_count_bar",$TPL_SCP,1);?></td></tr>
			</table>
		</div>
		
		<!-- 요약 내역 바 : START -->
		<div class="main-middlearea-summary-bar">
			<table cellpadding="0" cellspacing="0" border="0" >
			<tr>
				<!-- 판매현황 -->
				<td id="main-order-summary" width="275px">
					<div class="addPlus">
						<a href="/admin/order/catalog">
							<img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif">
						</a>
					</div>
					<div class="partition_tit">
						<img style="vertical-align: bottom;"  src="/admin/skin/default/images/main/admin_icon01.gif" />&nbsp;
						<span class="mainhelpicon" title="
						아래 데이터는 100일 동안의 주문상태별 주문수량으로<br/>
						주문수량은 주문리스트의 주문 건수와 같습니다.<br/>
						더불어 안내 드릴 사항은<br/>
						해당 주문건을 여러차례 나눠서  출고처리 하였다면<br/>
						아래의 주문 건수는 출고리스트의 출고 건수와는 같지 않습니다."></span>
						<a href="#" onclick="contents_pannel_load('order');return false;"><img src="/admin/skin/default/images/main/re_icon.gif" style="vertical-align:middle;"/></a>
					</div>

					<div class="main-summary-contents"><?php $this->print_("main_order_summary",$TPL_SCP,1);?></div>
				</td>
				<td class="partition_line"><img src="/admin/skin/default/images/main/line01.gif" /></td>
				<!-- 상품현황 -->
				<td id="main-goods-summary" width="195px">
					<div class="addPlus">
						<a href="/admin/goods/catalog">
							<img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif">
						</a>
					</div>
					<div class="partition_tit">
						<img align="absmiddle" src="/admin/skin/default/images/main/admin_icon02.gif" />
					</div>

					<div class="main-summary-contents"><?php $this->print_("main_goods_summary",$TPL_SCP,1);?></div>
				</td>
				<td class="partition_line"><img src="/admin/skin/default/images/main/line01.gif" /></td>
				<!-- 1:1 문의 -->
				<td width="301px">
					<div class="addPlus">
						<a href="/admin/board/board?id=mbqna">
							<img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif" />
						</a>
					</div>
					<div class="partition_tit">
						<img src="/admin/skin/default/images/main/admin_icon03.gif" />&nbsp;
<?php if($TPL_VAR["mbqna_reply_cnt"]&&$TPL_VAR["mbqna_reply_cnt"]> 0){?>
						누적미답변 <span class="redtxt"><?php echo $TPL_VAR["mbqna_reply_cnt"]?></span>
<?php }?>
					</div>

					<div class="main-summary-contents"><?php $this->print_("main_qna_summary",$TPL_SCP,1);?></div>
				</td>
				<td class="partition_line"><img src="/admin/skin/default/images/main/line01.gif" /></td>
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>

				<!-- 예약문의 -->
				<td width="240px">
					<div class="addPlus">
						<a href="/admin/board/board?id=store_reservation">
							<img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif" />
						</a>
					</div>
					<div class="partition_tit">
						<img src="/admin/skin/default/images/main/admin_icon05.gif" />&nbsp;
<?php if($TPL_VAR["reserve_reply_cnt"]&&$TPL_VAR["reserve_reply_cnt"]> 0){?>
						누적미답변 <span class="redtxt"><?php echo $TPL_VAR["reserve_reply_cnt"]?></span>
<?php }?>
					</div>

					<div class="main-summary-contents"><?php $this->print_("main_reserve_summary",$TPL_SCP,1);?></div>
				</td>				

<?php }else{?>

				<!-- 상품문의 -->
				<td width="240px">
					<div class="addPlus">
						<a href="/admin/board/board?id=goods_qna">
							<img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif" />
						</a>
					</div>
					<div class="partition_tit">
						<img src="/admin/skin/default/images/main/admin_icon04.gif" />&nbsp;
<?php if($TPL_VAR["goods_qna_reply_cnt"]&&$TPL_VAR["goods_qna_reply_cnt"]> 0){?>
						누적미답변 <span class="redtxt"><?php echo $TPL_VAR["goods_qna_reply_cnt"]?></span>
<?php }?>
					</div>

					<div class="main-summary-contents"><?php $this->print_("main_goodsqna_summary",$TPL_SCP,1);?></div>
				</td>

<?php }?>
			</tr>
			</table>
		</div>
		<!-- 요약 내역 바 : END -->

		<!-- MY SERVICE 바 : START -->
		<div class="main-middlearea-myservice-bar"><?php $this->print_("main_myservice_bar",$TPL_SCP,1);?></div>
		<!-- MY SERVICE 바 : END -->

		<!-- 하단 영역 : START -->
		<div class="main-bottomarea clearbox">

			<!-- 서비스 상태 : START -->
			 <div id="main-service-area" class="main-service-area warning">
				<div class="msa-title">
					<span class="link-more">
						<a href="http://firstmall.kr/myshop" target="_blank"><img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif"></a>
					</span>
				</div>
				<div class="msa-contents">
					<ul>
						<li>
							<span class="version-color"><?php echo $TPL_VAR["service_name"]?></span>
						</li>

<?php if(!preg_match("/^F_SH_/",$TPL_VAR["config_system"]["service"]["hosting_code"])){?>

						<li>
<?php if($TPL_VAR["config_system"]["domain"]){?>
								<span class="service-title">
									<img src="/admin/skin/default/images/main/domain_t01.gif" /> :
								</span>
								<span>http://<?php echo $TPL_VAR["config_system"]["domain"]?></span>
<?php }else{?>
								<span class="service-title">
									<img src="/admin/skin/default/images/main/domain_t01.gif" /> :
								</span>
								<span>http://<?php echo $TPL_VAR["config_system"]["subDomain"]?> (기본)</span>
<?php }?>
						</li>

<?php }?>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/setting_t02.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["config_system"]["service"]["setting_date"]?></span>
						</li>

						<!-- 무료몰 -->
<?php if($TPL_VAR["service_code"]=='P_FREE'){?>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/period_t03.gif" /> :
							</span>
							<span><b>평생</b>(단, 관리자 60일 미접속시 삭제)</span>
						</li>

<?php if($TPL_VAR["config_system"]["service"]["expire_date"]&&$TPL_VAR["config_system"]["service"]["expire_date"]!='0000-00-00'){?>
							<li>
								<span class="service-title">
									<img src="/admin/skin/default/images/main/diskp_t06.gif" /> :
								</span> <span><?php echo $TPL_VAR["config_system"]["service"]["expire_date"]?></span>
							</li>
<?php }?>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/disk_t04.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["usedDiskSpace"]?> / <?php echo $TPL_VAR["maxDiskSpace"]?> (<?php echo $TPL_VAR["usedSpacePercent"]?>% 사용중) <a href="http://firstmall.kr/myshop" target="_blank"><img src="/admin/skin/default/images/main/bt_plus.gif" align="absmiddle" /></a></span>
						</li>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/traffic_t05.gif" /> :
							</span>
							<span>무제한</span>
						</li>

						<!-- 프리미엄몰, 스토어 -->
<?php }elseif($TPL_VAR["service_code"]=='P_PREM'||$TPL_VAR["service_code"]=='P_STOR'){?>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/period_t03.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["config_system"]["service"]["expire_date"]?>까지(<span class="redtxt"><?php echo number_format($TPL_VAR["remainExpireDay"])?>일</span> 남음)</span>
						</li>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/disk_t04.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["usedDiskSpace"]?> / <?php echo $TPL_VAR["maxDiskSpace"]?> (<?php echo $TPL_VAR["usedSpacePercent"]?>% 사용중) <a href="http://firstmall.kr/myshop" target="_blank"><img src="/admin/skin/default/images/main/bt_plus.gif" align="absmiddle" /></a></span>
						</li>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/traffic_t05.gif" /> :
							</span>
							<span>무제한</span>
						</li>

						<!-- 페이머스 -->
<?php }elseif($TPL_VAR["service_code"]=='P_FAMM'){?>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/period_t03.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["config_system"]["service"]["expire_date"]?>까지(<span class="redtxt"><?php echo number_format($TPL_VAR["remainExpireDay"])?>일</span> 남음)</span>
						</li>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/disk_t04.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["usedDiskSpace"]?> / <?php echo $TPL_VAR["maxDiskSpace"]?> (<?php echo $TPL_VAR["usedSpacePercent"]?>% 사용중) <a href="http://firstmall.kr/myshop" target="_blank"><img src="/admin/skin/default/images/main/bt_plus.gif" align="absmiddle" /></a></span>
						</li>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/traffic_t05.gif" /> :
							</span>
							<span>무제한</span>
						</li>

						<!-- 독립몰 -->
<?php }elseif($TPL_VAR["service_code"]=='P_EXPA'||$TPL_VAR["service_code"]=='P_ADVA'){?>

						<li>
							<span class="service-title">
								<img src="/admin/skin/default/images/main/hosting_t07.gif" /> :
							</span>
							<span><?php echo $TPL_VAR["config_system"]["service"]["hosting_name"]?></span>
						</li>

<?php if($TPL_VAR["config_system"]["service"]["hosting_code"]!='F_SH_X'){?>
							<li>
								<span class="service-title">
									<img src="/admin/skin/default/images/main/period_t03.gif" /> :
								</span>
								<span><?php echo $TPL_VAR["config_system"]["service"]["expire_date"]?>까지(<span class="redtxt"><?php echo number_format($TPL_VAR["remainExpireDay"])?>일</span> 남음)</span>
							</li>

<?php if(!preg_match("/^F_SH_/",$TPL_VAR["config_system"]["service"]["hosting_code"])){?>

								<li>
									<span class="service-title">
										<img src="/admin/skin/default/images/main/disk_t04.gif" /> :
									</span>
									<span><?php echo $TPL_VAR["usedDiskSpace"]?> / <?php echo $TPL_VAR["maxDiskSpace"]?> (<?php echo $TPL_VAR["usedSpacePercent"]?>% 사용중) <a href="http://firstmall.kr/myshop" target="_blank"><img src="/admin/skin/default/images/main/bt_plus.gif" align="absmiddle" /></a></span>
								</li>

<?php if($TPL_VAR["config_system"]["service"]["traffic"]> 0){?>
								<li>
									<div style="float:left;">
										<span class="service-title"><img src="/admin/skin/default/images/main/traffic_t05.gif" /> : 
										<span class="helpicon" style="margin-left:-3px;" title="
										*1일 동안의 누적 사용량을 10분 단위로 체크해서 보여 드립니다.<br/>
										*120% 초과시 사이트가 차단되오니 그 전에 트래픽을 초기화하셔야 합니다.<br/>
										*초기화를 하려면 ‘트래픽설정’을 클릭하셔서 firstmall.kr/myshop 에서 ‘자동’ 혹은’ 수동’ 으로 초기화 가능합니다.<br/>
										*초기화를 하면 1번 할때마다  호스팅 사용기간이 1일 줄어듭니다.<br/>"></span></span>
									</div>
									<div style="width:166px;height:10px;margin-left:2px;float:left;" >
										<span id="traffic_area" >
											<b><?php echo $TPL_VAR["traffic"]["usages"]?></b> / <?php echo $TPL_VAR["traffic"]["limits"]?>(<b><?php echo $TPL_VAR["traffic"]["state"]?>% 사용중</b>)
										</span>
									</div>
									<div style="float:left;">
										<a href="http://firstmall.kr/myshop/index_service.php?domain=<?php echo $TPL_VAR["config_system"]["subDomain"]?>" target="_blank"><img src="/admin/skin/default/images/main/reset_bt.gif" align="absmiddle" /></a>
										<img src="/admin/skin/default/images/main/btn_refresh.gif" class="hand" align="absmiddle" onclick="reload_data('reset');" />
									</div>
									<div style="clear:both;"></div>
								</li>
<?php }?>
<?php }?>
<?php }else{?>
						<li>
							<span class="service-title">가비아 쇼핑몰 호스팅 상품이 아니므로 설치정보 및 용량, <br/>기간등을 확인할 수 없습니다.<br/>호스팅 이전을 원하시면 1544-3270 으로 전화 주세요</span>
						</li>
<?php }?>

<?php }?>
					</ul>

					<div class="gabia-pannel-main_service_banner" isdemo="<?php echo $TPL_VAR["isdemo"]["isdemo"]?>" style="width:309px;"><?php echo $TPL_VAR["main_newservice_banner"]?></div>

				</div>
			</div>
			<!-- 서비스 상태 : END -->

			<!-- 공지사항 : START -->
			<div class="main-board-area news">
				<div class="news-title">
					<span class="link-more">
						<a href="http://firstmall.kr/ec_hosting/customer/notice.php" target="_blank"><img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif"></a>
					</span>
				</div>

				<div id="main-notice-summary" class="msa-contents">로딩중</div>
			</div>
			<!-- 공지사항 : END -->

			<!-- 업그레이드 : START -->
			<div class="main-board-area upgrade">
				<div class="upgrade-title">
					<span class="link-more">
						<a href="http://firstmall.kr/ec_hosting/customer/patch.php" target="_blank"><img align="absmiddle" src="/admin/skin/default/images/main/btn_s_more.gif"></a>
					</span>
				</div>

				<div id="main-upgrade-summary" class="msa-contents">로딩중</div>

				<div class="fl" style="margin-top:5px;">
					<div class="gabia-pannel-main_btm_right_banner" style="width:311px; min-height:89px;"><?php echo $TPL_VAR["main_rolling_banner"]?></div>
				</div>
			</div>
			<!-- 업그레이드 : END -->

			<div class="btm_middle_banner" style="margin-left:17px;"><?php echo $TPL_VAR["main_bottom_left_banner"]?></div>
			<div class="btm_middle_banner"><?php echo $TPL_VAR["main_bottom_middle_banner"]?></div>
			<div class="btm_middle_banner"><?php echo $TPL_VAR["main_bottom_right_banner"]?></div>

			<div class="btm_bottom_left_banner"><?php echo $TPL_VAR["main_bottom_big_banner"]?></div>
			<div class="btm_bottom_right_banner">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td><?php echo $TPL_VAR["main_bottom_manual1_banner"]?></td></tr>
				<tr><td><?php echo $TPL_VAR["main_bottom_manual2_banner"]?></td></tr>
				</table>
				<br/>
			</div>
		</div>
		<!-- 하단 영역 : END -->
	</div>
	<!-- 좌측 영역 : END -->

	<div class="btn-main-change-panel" style="position:absolute;top:116px;left:1019px;">
		<img src="/admin/skin/default/images/main/btn_chg.gif" id="btn_main_change" style="cursor:pointer;" onclick="movingAnimation();" />
	</div>	

	<!-- 우측 영역 : START -->
	<div class="main-middlebody-right" >
		
		<div class="main-middlearea-stats-bar right-bar">
			<table cellpadding="0" cellspacing="0" border="0" class="head-title_sub-main">
			<colgroup>
				<col width="170px" />
				<col width="180px" />
				<col width="" />
			</colgroup>
			<tr>
				<td>
					<img align="absmiddle" src="/admin/skin/default/images/main/pointer_tit.gif" /> 
				</td>
				<td>
					<span class="head-title_sub-main">4시간마다 자동업데이트 됩니다.</span>
				</td>
				<td>
					<img align="absmiddle" class="hand" id="btn-main-reload" src="/admin/skin/default/images/main/re_icon.gif" />
				</td>
			</tr>
			</table>
		</div>

		<!-- 통계 순위 -->
		<div id="advanced_statistics_main"><?php $this->print_("main_statistic_area",$TPL_SCP,1);?></div>
	</div>
	<!-- 우측 영역 : END -->

	<!-- 비밀번호 변경 -->
	<div id="popup_change_pass"></div>
<?php if($TPL_VAR["is_change_pass"]){?>
	<script type="text/javascript">change_pass();</script>
<?php }?>
</div>

<?php $this->print_("admin_memo_area",$TPL_SCP,1);?>


<?php $this->print_("layout_footer",$TPL_SCP,1);?>