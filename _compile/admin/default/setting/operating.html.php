<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/operating.html 000019642 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">
function check_operating(type){
	var obj = $("input[name='operating']:checked").parent();
	var tdx = obj.parent().parent("tr").index();

	obj.parent().parent().parent().children().each(function(idx){
		if (tdx == idx) {
			if (tdx==5) {
				$(this).find("td[rowspan]").css({
					"border-bottom": "2px solid #ab0804"
				});
			}
		} else {
			$(this).next().css({"border":"1px solid #dadada","background-color":"#fff"});
			if (tdx!=5) {
				$(this).find("td[rowspan]").css({
					"border-bottom": "1px solid #dadada"
				});
			}
		}
	});

	obj.parent().parent("tr").css({
		"border-top": "2px solid #ab0804", "border-left": "2px solid #ab0804",
		"border-right": "2px solid #ab0804", "background-color":"#dddddd"
	});
	obj.parent().parent("tr").next().css({
		"border-top": "2px solid #dadada", "border-left": "2px solid #ab0804", "border-right": "2px solid #ab0804",
		"border-bottom": "2px solid #ab0804", "background-color":"#dddddd"});

	if(type!='chg'){
		$("#now_operating").html(obj.children("span").html());
	}
}
$(document).ready(function() {
	$("input[name='operating']").live("click",function(){
		var idx = $(this).parent().parent().parent().index();
<?php if($TPL_VAR["realname"]["adult_chk"]!='Y'){?>
		if(idx == 5) { // 성인몰
			if(confirm('성인 쇼핑몰을 운영 하시려면 먼저 휴대폰인증&아이핀 서비스를 설정하셔야 합니다.\n설정>회원>본인확인으로 이동하시겠습니까?')){
				location.href = "/admin/setting/member?gb=realname";
			}
			$("input[name='operating']").val(['general']);
		}
<?php }?>
		check_operating('chg');
	});

<?php if($TPL_VAR["operating"]){?>
	$("input[name='operating'][value='<?php echo $TPL_VAR["operating"]?>']").attr('checked',true);
	$("input[name='intro_use'][value='<?php echo $TPL_VAR["intro_use"]?>']").attr('checked',true);
	$("input[name='general_use'][value='<?php echo $TPL_VAR["general_use"]?>']").attr('checked',true);
	$("input[name='member_use'][value='<?php echo $TPL_VAR["member_use"]?>']").attr('checked',true);
	$("input[name='adult_use'][value='<?php echo $TPL_VAR["adult_use"]?>']").attr('checked',true);

	// 운영방식 모바일(태블릿) 추가 2014-05-20 leewh
	var intro_m_use = ('<?php echo $TPL_VAR["intro_m_use"]?>') ? '<?php echo $TPL_VAR["intro_m_use"]?>' : '<?php echo $TPL_VAR["intro_use"]?>';
	var general_m_use = ('<?php echo $TPL_VAR["general_m_use"]?>') ? '<?php echo $TPL_VAR["general_m_use"]?>' : '<?php echo $TPL_VAR["general_use"]?>';
	var member_m_use = ('<?php echo $TPL_VAR["member_m_use"]?>') ? '<?php echo $TPL_VAR["member_m_use"]?>' : '<?php echo $TPL_VAR["member_use"]?>';
	var adult_m_use = ('<?php echo $TPL_VAR["adult_m_use"]?>') ? '<?php echo $TPL_VAR["adult_m_use"]?>' : '<?php echo $TPL_VAR["adult_use"]?>';

	$("input[name='intro_m_use'][value='"+intro_m_use+"']").attr('checked',true);
	$("input[name='general_m_use'][value='"+general_m_use+"']").attr('checked',true);
	$("input[name='member_m_use'][value='"+member_m_use+"']").attr('checked',true);
	$("input[name='adult_m_use'][value='"+adult_m_use+"']").attr('checked',true);
<?php }?>
	check_operating('init');

	$("input[name='operating']").live("click",function(){
		init_process();
	});

	$("input[name='intro_use'], input[name='general_use'], input[name='intro_m_use'], input[name='general_m_use']").live("click",function(){
		set_img_general();
	});

	$("input[name='member_use'], input[name='member_m_use']").live("click",function(){
		set_img_member();
	});

	$("input[name='adult_use'], input[name='adult_m_use']").live("click",function(){
		set_img_adult();
	});

<?php if($TPL_VAR["service_limit"]){?>
	$("input[name='operating'][value='member']").attr("disabled",true);
	$("input[name='operating'][value='adult']").attr("disabled",true);
<?php }?>

	init_process();

	// 미리보기 샘플 설정
	set_img_general();
	set_img_member();
	set_img_adult();
});

function init_process(){
	var chk = $("input[name='operating']:checked").val();
	if(chk=='general'){
		$("input[name='general_use']").attr("disabled",false);
		$("input[name='general_m_use']").attr("disabled",false);
		$("input[name='member_use']").attr("disabled",true);
		$("input[name='member_m_use']").attr("disabled",true);
		$("input[name='adult_use']").attr("disabled",true);
		$("input[name='adult_m_use']").attr("disabled",true);
	}else if(chk=='member'){
		$("input[name='general_use']").attr("disabled",true);
		$("input[name='general_m_use']").attr("disabled",true);
		$("input[name='member_use']").attr("disabled",false);
		$("input[name='member_m_use']").attr("disabled",false);
		$("input[name='adult_use']").attr("disabled",true);
		$("input[name='adult_m_use']").attr("disabled",true);
	}else if(chk=='adult'){
		$("input[name='general_use']").attr("disabled",true);
		$("input[name='general_m_use']").attr("disabled",true);
		$("input[name='member_use']").attr("disabled",true);
		$("input[name='member_m_use']").attr("disabled",true);
		$("input[name='adult_use']").attr("disabled",false);
		$("input[name='adult_m_use']").attr("disabled",false);
	}
}

function set_img_general() {
		var intro = $("input[name='intro_use']:checked").val();
		var general = $("input[name='general_use']:checked").val();
		var img_box = $("#img_box_intro img");

		// PC
		if (intro=="Y" && general=="Y" || intro=="N" && general=="Y") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_underconstruction.gif");
		} else if (intro=="Y" && general=="N") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_intro.gif");
		} else if (intro=="N" && general=="N") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_main.gif");
		}

		var intro_m = $("input[name='intro_m_use']:checked").val();
		var general_m = $("input[name='general_m_use']:checked").val();
		var img_box_m = $("#img_box_intro_m img");

		// 모바일
		if (intro_m=="Y" && general_m=="Y" || intro_m=="N" && general_m=="Y") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_underconstruction_m.gif");
		} else if (intro_m=="Y" && general_m=="N") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_intro_m.gif");
		} else if (intro_m=="Y" && general_m=="P") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample.gif");
		} else if (intro_m=="N" && general_m=="N") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_main_m.gif");
		} else if (intro_m=="N" && general_m=="P") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_main_pc.gif");
		}
}

function set_img_member() {
		var member = $("input[name='member_use']:checked").val();
		var img_box = $("#img_box_member img");

		// PC
		if (member=="Y") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_member.gif");
		} else if (member=="N") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_underconstruction.gif");
		}

		var member_m = $("input[name='member_m_use']:checked").val();
		var img_box_m = $("#img_box_member_m img");

		// 모바일
		if (member_m=="Y") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_member_m.gif");
		} else if (member_m=="P") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_member_pc.gif");
		} else if (member_m=="N") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_underconstruction_m.gif");
		}
}

function set_img_adult() {
		var adult = $("input[name='adult_use']:checked").val();
		var img_box = $("#img_box_adult img");

		// PC
		if (adult=="Y") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_19.gif");
		} else if (adult=="N") {
			img_box.attr("src","/admin/skin/default/images/common/intro_sample_underconstruction.gif");
		}

		var adult_m = $("input[name='adult_m_use']:checked").val();
		var img_box_m = $("#img_box_adult_m img");

		// 모바일
		if (adult_m=="Y") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_19_m.gif");
		} else if (adult_m=="P") {
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_19_pc.gif");
		} else if (adult_m=="N") { // 오픈 준비 중
			img_box_m.attr("src","/admin/skin/default/images/common/intro_sample_underconstruction_m.gif");
		}
}
</script>
<form name="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/operating" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 운영방식</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">

<?php if($TPL_VAR["service_limit"]){?>
			<div class="center" style="padding-left:20px;width:100%;text-align:center;">
				<div style="border:2px #dddddd solid;padding:10px;width:95%;">
					<table width="100%">
					<tr>
					<td align="left">
						무료몰+ : ‘일반쇼핑몰’ 방식으로 운영 할 수 있습니다.<br>
						회원전용 또는 성인쇼핑몰을 운영하시려면 프리미엄몰+ 또는 독립몰+로 업그레이드 하시길 바랍니다.
					</td>
					<td align="right"><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></td>
					</tr>
					</table>

				</div>
				<br style="line-height:20px;" />
			</div>
<?php }?>

			<br class="table-gap" />

			<table style="margin:auto;">
			<tr>
				<td><img src="/admin/skin/default/images/common/icon_setting_now.gif" align="absmiddle" hspace="5" /></td>
				<td>
					<span class="bold fx16">현재설정 : </span>
					<span class="bold fx16 blue" id="now_operating"></span>
				</td>
			</tr>
			</table>

			<br class="table-gap" />

			<div class="item-title">운영 방식 <span class="desc" style="font-weight:normal;">일반, 회원전용, 성인전용의 운영방식을 플랫폼(데스크탑, 모바일/태블릿)별로 세팅할 수 있습니다.</span></div>

			<table width="100%" class="info-table-style">
			<col width="8%" /><col width="7%" /><col width="20%" /><col width="35%" /><col width="15%" /><col width="15%" />
			<tr>
				<th class="its-th-align" colspan="2">운영 방식</th>
				<th class="its-th-align">샘플 미리보기</th>
				<th class="its-th-align">인트로 페이지 및 운영 여부</th>
				<th class="its-th-align">정상 운영 시 방문 권한</th>
				<th class="its-th-align">정상 운영 시 구매 권한</th>
			</tr>
			<tr>
				<td class="its-td" rowspan="2"><label><input type="radio" name="operating" value="general" checked="checked" /> <span>일반</span></label></td>
				<td class="its-td-align center"><span style="line-height:120%;">데스크탑<br />(페이스북)</span></td>
				<td class="its-td-align"><div id="img_box_intro" style="text-align:center;"><img src="/admin/skin/default/images/common/intro_sample_underconstruction.gif"></div></td>
				<td class="its-td">
					<div>① 인트로 페이지
					<div style="padding-left:15px;"><label><input type="radio" name="intro_use" value="N" checked="checked">사용안함</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="intro_use" value="Y">사용함</label> </div>

					<div>② 정상 운영 여부
					<div style="padding-left:15px;"><label><input type="radio" name="general_use" value="N" checked="checked">운영 중</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="general_use" value="Y">오픈 준비 중(공사 중)</label> </div>

					<div style="padding-left:28px;">단, 관리자 로그인 상태에서는 접속 가능</div>

				</td>
				<td class="its-td-align center">회원, 비회원</td>
				<td class="its-td-align center">회원, 비회원</td>
			</tr>
			<tr>
				<td class="its-td-align center">모바일/태블릿</td>
				<td class="its-td-align"><div id="img_box_intro_m" style="text-align:center;"><img src="/admin/skin/default/images/common/intro_sample_underconstruction.gif"></div></td>
				<td class="its-td">
					<div>① 인트로 페이지
					<div style="padding-left:15px;"><label><input type="radio" name="intro_m_use" value="N" checked="checked">사용안함</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="intro_m_use" value="Y">사용함</label> </div>

					<div>② 정상 운영 여부
					<div style="padding-left:15px;"><label><input type="radio" name="general_m_use" value="N" checked="checked">운영 중(최적화된 모바일 환경)</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="general_m_use" value="P">운영 중(PC와 동일)</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="general_m_use" value="Y">오픈 준비 중(공사 중)</label> </div>

					<div style="padding-left:28px;">단, 관리자 로그인 상태에서는 접속 가능</div>

				</td>
				<td class="its-td-align center">회원, 비회원</td>
				<td class="its-td-align center">회원, 비회원</td>
			</tr>
			<tr>
				<td class="its-td" rowspan="2">
<?php if($TPL_VAR["config_system"]["service"]["code"]=='P_STOR'){?>
					<span class="hand noshoplinknone"><input type="radio" name="operating" value="member" disabled="disabled" /> 회원 전용</span>
<?php }else{?>
					<label><input type="radio" name="operating" value="member" /> <span>회원 전용</span></label>
<?php }?>
				</td>
				<td class="its-td-align center"><span style="line-height:120%;">데스크탑<br />(페이스북)</span></td>
				<td class="its-td-align"><div id="img_box_member" style="text-align:center;"><img src="/admin/skin/default/images/common/intro_sample_member.gif"></div></td>
				<td class="its-td">
					<div>① 인트로 페이지 : 사용 필수</div>
					<div>② 정상 운영 여부</div>
					<div style="padding-left:15px;"><label><input type="radio" name="member_use" value="Y" checked="checked">운영 중</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="member_use" value="N">오픈 준비 중(공사 중) - 로그인 및 회원가입 제한됨</label></div>
					<div style="padding-left:28px;">단, 관리자 로그인 상태에서는 접속 가능</div>
				</td>
				<td class="its-td-align center">회원</td>
				<td class="its-td-align center">회원</td>
			</tr>
			<tr>
				<td class="its-td-align center">모바일/태블릿</td>
				<td class="its-td-align"><div id="img_box_member_m" style="text-align:center;"><img src="/admin/skin/default/images/common/intro_sample_member.gif"></div></td>
				<td class="its-td">
					<div>① 인트로 페이지 : 사용 필수</div>
					<div>② 정상 운영 여부</div>
					<div style="padding-left:15px;"><label><input type="radio" name="member_m_use" value="Y" checked="checked">운영 중(최적화된 모바일 환경)</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="member_m_use" value="P">운영 중(PC와 동일)</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="member_m_use" value="N">오픈 준비 중(공사 중) - 로그인 및 회원가입 제한됨</label></div>
					<div style="padding-left:28px;">단, 관리자 로그인 상태에서는 접속 가능</div>
				</td>
				<td class="its-td-align center">회원</td>
				<td class="its-td-align center">회원</td>
			</tr>
			<tr>
				<td class="its-td" rowspan="2">
<?php if($TPL_VAR["config_system"]["service"]["code"]=='P_STOR'){?>
					<span class="hand noshoplinknone"><input type="radio" name="operating" value="adult" disabled="disabled" /> 성인 전용</span>
<?php }else{?>
					<label><input type="radio" name="operating" value="adult" /> <span>성인 전용</span></label>
<?php }?>
				</td>
				<td class="its-td-align center"><span style="line-height:120%;">데스크탑<br />(페이스북)</span></td>
				<td class="its-td-align"><div id="img_box_adult" style="text-align:center;"><img src="/admin/skin/default/images/common/intro_sample_19.gif"></div></td>
				<td class="its-td">
					<div>① 인트로 페이지 : 사용 필수</div>
					<div>② 휴대폰본인인증/아이핀 : 사용 필수</div>
					<div>③ 정상 운영 여부</div>
					<div style="padding-left:15px;"><label><input type="radio" name="adult_use" value="Y" checked="checked">운영 중</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="adult_use" value="N">오픈 준비 중(공사 중) - 로그인 및 회원가입 제한됨</label></div>
					<div style="padding-left:28px;">단, 관리자 로그인 상태에서는 접속 가능</div>
				</td>
				<td class="its-td-align center">성인 인증된<br />회원, 비회원</td>
				<td class="its-td-align center">성인 인증된<br />회원, 비회원</td>
			</tr>
			<tr>
				<td class="its-td-align center">모바일/태블릿</td>
				<td class="its-td-align"><div id="img_box_adult_m" style="text-align:center;"><img src="/admin/skin/default/images/common/intro_sample_19.gif"></div></td>
				<td class="its-td">
					<div>① 인트로 페이지 : 사용 필수</div>
					<div>② 휴대폰본인인증/아이핀 : 사용 필수</div>
					<div>③ 정상 운영 여부</div>
					<div style="padding-left:15px;"><label><input type="radio" name="adult_m_use" value="Y" checked="checked">운영 중(최적화된 모바일 환경)</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="adult_m_use" value="P">운영 중(PC와 동일)</label></div>
					<div style="padding-left:15px;"><label><input type="radio" name="adult_m_use" value="N">오픈 준비 중(공사 중) - 로그인 및 회원가입 제한됨</label></div>
					<div style="padding-left:28px;">단, 관리자 로그인 상태에서는 접속 가능</div>
				</td>
				<td class="its-td-align center">성인 인증된<br />회원, 비회원</td>
				<td class="its-td-align center">성인 인증된<br />회원, 비회원</td>
			</tr>
			</table>
		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>

<div id="shopBranchPopup" style="display: none">
	<div align="center">
	<select name="shopBranchSel">
		<option value="">쇼핑몰 분류1을 선택하세요.</option>
<?php if(is_array($TPL_R1=code_load('shopBranch'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
		<option value='<?php echo $TPL_V1["codecd"]?>'><?php echo $TPL_V1["value"]?></option>
<?php }}?>
	</select>
	<select name="shopBranchSub">
		<option value="">쇼핑몰 분류2를 선택하세요.</option>
	</select>
	</div>

	<div style="padding:10px 0 0 0" align="center"><span class="btn medium"><input type="button" value="추가" id="shopBranchButton" /></span></div>
</div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>