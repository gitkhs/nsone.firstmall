<?php /* Template_ 2.2.6 2014/09/29 16:08:53 /www/nsone_firstmall_kr/admin/skin/default/setting/video.html 000007361 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">
$(document).ready(function() {
});
</script>
<form name="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/video" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 동영상</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  type="button" <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> type="submit" <?php }?>>저장하기<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container body-height-resizing">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">
			<div class="item-title">동영상
			<span class="desc" style="font-weight:normal;">모든 페이지에서 상품과 게시판의 동영상을 구매자에게 보여줄 수 있습니다. 아래의 세팅정보를 설정하세요.</span>
			</div>

				<table width="100%" class="info-table-style">
				<col width="80" /><col width="130" /><col width="" /><tr>
					<th class="its-th-align center" colspan="2" >이용 안내</th>
					<td class="its-td" class="its-td" >
						TV 홈쇼핑처럼 인터넷쇼핑몰에서도 동영상으로 상품을 소개하고 판매하세요!<br/>
						상품후기를 리뷰 전문가처럼 동영상으로 올려 보세요! 쇼핑몰의 수준이 달려집니다.<br/>
						동영상은 PC, 태블릿, 스마트폰 모든 환경에서 최적화되어 플레이 됩니다.<br/>
					</td>
					<td class="its-td" >
						<p >
						<span class="btn large black"><button type="button" class="" onclick="window.open('https://hosting.gabia.com/flvhosting/price_hd.php', 'flvhosting');" >동영상 서비스 신청 안내</button></span>
						or 동영상 서비스를 신청하셨다면 <span class="btn large cyanblue"><button type="button" class="" onclick="window.open('http://admin.smartucc.kr', 'admin');" >동영상 서비스 관리</button></span>

						</p>
					</td>
				</tr>
				<tr>
					<th class="its-th-align center" rowspan="4"  >세팅정보</th>
				</tr>
				<tr>
					<th class="its-th-align center"  >UCC 아이디</th>
					<td class="its-td"  colspan="3" >
						<input type="text" name="ucc_id"  id="ucc_id"  size="72"  value="<?php echo $TPL_VAR["cfg_goods"]["ucc_id"]?>" class="line"   <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>  />
						 &nbsp;&nbsp;&nbsp;&nbsp;<span class="desc">동영상 서비스 신청 시 입력하신 관리자ID를 입력하세요</span>
					</td>
				</tr>
				<tr>
					<th class="its-th-align center"  >UCC 도메인</th>
					<td class="its-td"  colspan="3" >
						<input type="text" name="ucc_domain"  id="ucc_domain" size="72"  value="<?php echo str_replace('web.mvod.','',$TPL_VAR["cfg_goods"]["ucc_domain"])?>" class="line"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>   />
						&nbsp;&nbsp;&nbsp;&nbsp;<span class="desc">동영상 서비스 신청 시 도메인(무료 2차 도메인 또는 등록된  도메인)을 입력하세요</span>
					</td>
				</tr>
				<tr>
					<th class="its-th-align center" >UCC 인증키</th>
					<td class="its-td"  colspan="3" >
						<input type="text" name="ucc_key"  id="ucc_key"  size="72"  value="<?php echo $TPL_VAR["cfg_goods"]["ucc_key"]?>" class="line"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?>   />
						 &nbsp;&nbsp;&nbsp;&nbsp;<span class="desc"><a href="http://admin.smartucc.kr" target="_blank" style="color:#cd500b; text-decoration:underline">동영상 관리 페이지 지원센터 > 연동 환경 설정</a> 페이지에서 인증키를 확인 후 입력하세요.</span>
					</td>
				</tr>
				<tr>
				<td colspan="4">
				<table width="100%" border="0" style="line-height:180%;">
					<col width="" /><col width="" />
					<tr>
						<td class="left" valign="top"  style="padding-right:10px !important;padding-left:10px !important;padding-bottom:30px !important;padding-top:30px !important;border-bottom:1px solid #e5e5e5;border-right:1px solid #e5e5e5;">
							<div >
								<span class="bold">&nbsp;<img src="/admin/skin/default/images/common/bullet_vs.gif" align="absmiddle" hspace="5" />메인 페이지 및 기타 모든 페이지에서 상품을 동영상으로 보여줄 수 있습니다.</span> <br/>
								<img src="/admin/skin/default/images/common/video_i1.jpg" align="absmiddle" hspace="5" />
							</div>
						</td>
						<td class="left" valign="top" style="padding-left:10px !important;padding-right:10px !important;padding-bottom:30px !important;padding-top:30px !important;border-bottom:1px solid #e5e5e5;">
							<div >
								<span class="bold">&nbsp;<img src="/admin/skin/default/images/common/bullet_vs.gif" align="absmiddle" hspace="5" />모든 페이지에서 회사소개, 브랜드소개 등의 동영상을 보여줄 수 있습니다.</span> <br/>
								<img src="/admin/skin/default/images/common/video_i2.jpg" align="absmiddle" hspace="5" />
							</div>
						</td>
					</tr>
					<tr>
						<td class="left" valign="top"  style="padding-right:10px !important;padding-left:10px !important;padding-bottom:30px !important;padding-top:34px !important;border-bottom:1px solid #e5e5e5;border-right:1px solid #e5e5e5;">
							<div >
								<span class="bold">&nbsp;<img src="/admin/skin/default/images/common/bullet_vs.gif" align="absmiddle" hspace="5" />상품상세 페이지의 상품이미지영역과 상품설명영역에서 동영상을 보여줄 수 있습니다.</span> <br/>
								<img src="/admin/skin/default/images/common/video_i3.jpg" align="absmiddle" hspace="5" />
							</div>
						</td>
						<td class="left" valign="top" style="padding-left:10px !important;padding-right:10px !important;padding-bottom:30px !important;padding-top:34px !important;border-bottom:1px solid #e5e5e5;">
							<div >
								<span class="bold">&nbsp;<img src="/admin/skin/default/images/common/bullet_vs.gif" align="absmiddle" hspace="5" />상품후기 또는 추가로 생성한 게시판에서 업로드된 동영상을 보여줄 수 있습니다.</span> <br/>
								<img src="/admin/skin/default/images/common/video_i4.jpg" align="absmiddle" hspace="5" />
							</div>
						</td>
					</tr>
				</table>
			</tr>
			</table>

		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>