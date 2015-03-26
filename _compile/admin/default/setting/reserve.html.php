<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/setting/reserve.html 000045596 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<style>
.label { cursor:pointer }
</style>
<script type="text/javascript">
function check_max_emoney_policy(){

	$("input[name='max_emoney_policy']").each(function(idx){
		if( idx != 0 ){
			var obj =$(this).parent().find("input[type='text']");
			if( $(this).attr("checked") == "checked" ){
				obj.removeAttr("readonly");
			}else{
				obj.val('');
				obj.attr("readonly",true);
			}
		}
	});
}

$(document).ready(function() {

	$('body,input,textarea,select').bind('keydown','Ctrl+s',function(event){
		event.preventDefault();
		$("form#settingForm")[0].submit();
	});

	$("input[name='max_emoney_policy']").bind("click",function(){
		check_max_emoney_policy();
	});

	$(".label").live("click",function(){
		$(this).parent().find("input[type='radio'], input[type='checkbox']").click();
		check_max_emoney_policy();
	});

	$("#setting_basic").click(function(){
		document.location.href='/admin/setting/basic';
	});

	$(".promotioncodehelperbtn").click(function() {
		openDialog("프로모션 코드 안내", "promotioncodehelperlay", {"width":"800","height":"480","show" : "fade","hide" : "fade"});
	});


<?php if($TPL_VAR["max_emoney_policy"]){?>
	$("input[name='max_emoney_policy'][value='<?php echo $TPL_VAR["max_emoney_policy"]?>']").attr('checked',true);
<?php }?>

	$("input[name='point_use'][value='<?php echo $TPL_VAR["point_use"]?>']").attr('checked',true);
	$("input[name='cash_use'][value='<?php echo $TPL_VAR["cash_use"]?>']").attr('checked',true);
	$("input[name='default_point_type'][value='<?php echo $TPL_VAR["default_point_type"]?>']").attr('checked',true);
	//$("input[name='save_step'][value='<?php echo $TPL_VAR["save_step"]?>']").attr('checked',true);

	//$("select[name='save_term']").val('<?php echo $TPL_VAR["save_term"]?>').attr("selected",true);
	//$("select[name='save_type']").val('<?php echo $TPL_VAR["save_type"]?>').attr("selected",true);
	$("select[name='reserve_select']").val('<?php echo $TPL_VAR["reserve_select"]?>').attr("selected",true);
	$("select[name='point_select']").val('<?php echo $TPL_VAR["point_select"]?>').attr("selected",true);
	$("select[name='exchange_emoney_select']").val('<?php echo $TPL_VAR["exchange_emoney_select"]?>').attr("selected",true);

	span_controller('reserve');
	span_controller('point');
	span_controller('exchange_emoney');

	check_max_emoney_policy();

	$("select[name='reserve_select']").live("change",function(){
		span_controller('reserve');
	});

	$("select[name='exchange_emoney_select']").live("change",function(){
		span_controller('exchange_emoney');
	});

	$("select[name='point_select']").live("change",function(){
		span_controller('point');
	});

	$("#infos").live("click",function(){
		openDialog("구매 시 적립금/포인트 적립 금액 안내 <span class='desc'></span>", "infoPopup", {"width":"800","height":"400","show" : "fade","hide" : "fade"});
	});


	$("input[name=emoney_exchange_use]").change(function(){
		if( $("input[name=emoney_exchange_use]:checked").val() == 'y' ){
			$(".emoney_exchange_uselay").find("input,select").removeAttr('disabled');
		}else{
			$(".emoney_exchange_uselay").find("input,select").attr('disabled',true);
		}
	}).change();
});

function span_controller(name){
	var reserve_y = $("span[name='"+name+"_y']");
	var reserve_d = $("span[name='"+name+"_d']");
	var value = $("select[name='"+name+"_select'] option:selected").val();
	if(value==""){
		reserve_y.hide();
		reserve_d.hide();
	}else if(value=="year"){
		reserve_y.show();
		reserve_d.hide();
	}else if(value=="direct"){
		reserve_y.hide();
		reserve_d.show();
	}
}
</script>

<form name="settingForm" id="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/reserve" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 적립금/포인트/이머니</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
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
	<div class='slc-body-wrap body-height-resizing'>
		<div class="slc-body">

			<!-- ### 201211 : emoney/point/cash -->
			<div class="item-title"> 적립금/포인트/이머니 사용여부 </div>
<?php if(!$TPL_VAR["isplusfreenot"]){?>
				<table width="100%" class="info-table-style">
				<col width="200" /><col width="25%" /><!-- <col width="25%" /><col width="25%" /> -->
				<tr>
					<th class="its-th-align">구분</th>
					<th class="its-th-align"><img src="/admin/skin/default/images/design/icon_m_mileage.gif">적립금(마일리지) <span class="helpicon" title="회원의 구매 및 각종 활동으로 회원에게 적립되는 금액으로 상품 구매 시점에 사용 기준을 만족할 경우 사용이 가능합니다"></span></th>
					<th class="its-th-align"><img src="/admin/skin/default/images/design/icon_m_point.gif">포인트 <span class="helpicon" title="회원의 구매 및 각종 활동으로 회원에게 적립되는 금액으로 각종 프로모션 참여 및 쿠폰/사은품 교환 등에 사용이 가능합니다."></span></th>
					<th class="its-th-align"><img src="/admin/skin/default/images/design/icon_m_cash.gif">이머니(캐쉬) <span class="helpicon" title="입금 및 출금이 가능한 현금성 금액으로 상품 구매 시 사용이 가능합니다."></span></th>
				</tr>
				<tr>
					<th class="its-th-align center">사용여부</th>
					<td class="its-td">
						사용함 (지급함, 사용가능)
					</td>
					<td class="its-td" rowspan="6"  colspan="2">
					<span class="desc">포인트 제도와 이머니(캐쉬) 제도는 업그레이드가 필요합니다.</span> <img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></span>

					</td>
				</tr>
				<tr>
					<th class="its-th-align">적립</th>
					<td class="its-td-align center">구매 및 각종 활동</td>
					<!-- <td class="its-td-align center hide">구매 및 각종 활동</td>
					<td class="its-td-align center hide">현금성 환불, 이머니 구입(입금)</td> -->
				</tr>
				<tr>
					<th class="its-th-align">사용</th>
					<td class="its-td-align center ">구매 시 적립금 사용 (단, 기준 만족 시)</td>
					<!-- <td class="its-td-align center  hide">이벤트 참여, 쿠폰 교환 등에 포인트 사용</td>
					<td class="its-td-align center  hide">구매 시 이머니 사용</td> -->
				</tr>
				<tr>
					<th class="its-th-align">현금성</th>
					<td class="its-td-align center">비현금성</td>
					<!-- <td class="its-td-align center  hide">비현금성</td>
					<td class="its-td-align center  hide">현금성</td> -->
				</tr>
				<tr>
					<th class="its-th-align">인출(출금)</th>
					<td class="its-td-align center">X</td>
					<!-- <td class="its-td-align center  hide">X</td>
					<td class="its-td-align center  hide">○</td> -->
				</tr>
				<tr>
					<th class="its-th-align">유효기간 제한 가능여부</th>
					<td class="its-td-align center">○</td>
					<!-- <td class="its-td-align center  hide">○</td>
					<td class="its-td-align center  hide">X</td> -->
				</tr>
				</table>
<?php }else{?>
			<table width="100%" class="info-table-style">
			<col width="150" /><col width="50" /><col width="25%" /><col width="25%" /><col width="25%" />
			<tr>
				<th colspan="2" class="its-th-align">구분</th>
				<th class="its-th-align"><img src="/admin/skin/default/images/design/icon_m_mileage.gif">적립금(마일리지) <span class="helpicon" title="회원의 구매 및 각종 활동으로 회원에게 적립되는 금액으로 상품 구매 시점에 사용 기준을 만족할 경우 사용이 가능합니다"></span></th>
				<th class="its-th-align"><img src="/admin/skin/default/images/design/icon_m_point.gif">포인트 <span class="helpicon" title="회원의 구매 및 각종 활동으로 회원에게 적립되는 금액으로 각종 프로모션 참여 및 쿠폰/사은품 교환 등에 사용이 가능합니다."></span></th>
				<th class="its-th-align"><img src="/admin/skin/default/images/design/icon_m_cash.gif">이머니(캐쉬) <span class="helpicon" title="입금 및 출금이 가능한 현금성 금액으로 상품 구매 시 사용이 가능합니다."></span></th>
			</tr>
			<tr>
				<th colspan="2" class="its-th-align center">사용여부</th>
				<td class="its-td">
					사용함 (지급함, 사용가능)
				</td>
				<td class="its-td">
					<label><input type="radio" name="point_use" value="N"/>사용안함 (미지급, 사용불가)</label><br>
					<label><input type="radio" name="point_use" value="Y"/>사용함 (지급함, 사용가능)</label>
				</td>
				<td class="its-td">
					<label><input type="radio" name="cash_use" value="N"/>사용안함 (미지급, 사용불가)</label><br>
					<label><input type="radio" name="cash_use" value="Y"/>사용함 (지급함, 사용가능)</label>
				</td>
			</tr>
			<tr>
				<th colspan="2" class="its-th-align">현금성</th>
				<td class="its-td-align center">비현금성</td>
				<td class="its-td-align center">비현금성</td>
				<td class="its-td-align center">현금성</td>
			</tr>
			<tr>
				<th colspan="2" class="its-th-align">인출(출금)</th>
				<td class="its-td-align center">X</td>
				<td class="its-td-align center">X</td>
				<td class="its-td-align center">○</td>
			</tr>
			<tr>
				<th colspan="2" class="its-th-align">유효기간 제한 가능여부</th>
				<td class="its-td-align center">○</td>
				<td class="its-td-align center">○</td>
				<td class="its-td-align center">X</td>
			</tr>
			<tr>
				<th rowspan="2" class="its-th-align">사용여부가 '사용함'일 때<br> 활용방법 <!--span class="btn small black"><button type="submit">자세히<span class="arrowright"></span></button></span--></th>
				<th class="its-th-align center">지급(적립)</td>
				<td class="its-td">
				· 구매 시 적립금  <img src="/admin/skin/default/images/common/icon_oreder_tree_b.gif" align="absmiddle" /><br>
				· 이벤트 기간에 구매 시 추가 적립금 <img src="/admin/skin/default/images/common/icon_oreder_tree_b.gif" align="absmiddle" /><br>
				· 모바일에서 구매 시 추가 적립금 <img src="/admin/skin/default/images/common/icon_oreder_tree_b.gif" align="absmiddle" /><br>
				· '좋아요'한 상품 구매 시 추가 적립금 <img src="/admin/skin/default/images/common/icon_oreder_tree_b.gif" align="absmiddle" /><br>
				· 회원등급별 추가 적립금 <img src="/admin/skin/default/images/common/icon_oreder_tree_b.gif" align="absmiddle" /><br>
				· 회원가입 시 적립금<br>
				· 추천하기 적립금<br>
				· 초대하기 적립금<br>
				· 북마크 적립금<br>
				· 출석체크 적립금<br>
				· 상품후기 적립금
				</td>
				<td class="its-td" style="vertical-align:top;">
				· 구매 시 포인트  <img src="/admin/skin/default/images/common/icon_oreder_tree_c.gif" align="absmiddle" /><br>
				· 이벤트 기간에 구매 시 추가 포인트 <img src="/admin/skin/default/images/common/icon_oreder_tree_c.gif" align="absmiddle" /><br>
				· 모바일에서 구매 시 추가포인트 <img src="/admin/skin/default/images/common/icon_oreder_tree_c.gif" align="absmiddle" /><br>
				· '좋아요'한 상품 구매 시 추가 포인트 <img src="/admin/skin/default/images/common/icon_oreder_tree_c.gif" align="absmiddle" /><br>
				· 회원등급별 추가 포인트 <img src="/admin/skin/default/images/common/icon_oreder_tree_c.gif" align="absmiddle" /><br>
				· 회원가입 시 포인트<br>
				· 추천하기 포인트<br>
				· 초대하기 포인트<br>
				· 북마크 포인트<br>
				· 출석체크 포인트<br>
				· 상품후기 포인트
				</td>
				<td class="its-td" style="vertical-align:top;">· 이머니 환불 가능</td>
			</tr>
			<tr>
				<th class="its-th-align center">사용</td>
				<td class="its-td">· 구매 시 적립금 사용  <img src="/admin/skin/default/images/common/icon_oreder_tree_a.gif" align="absmiddle" /></td>
				<td class="its-td">· 적립금으로 교환<br>· 프로모션 코드로 교환 <span class="btn small orange"><input type="button" value="안내) 프로모션 코드" class="promotioncodehelperbtn" /></span><br>· 쿠폰으로 교환</td>
				<td class="its-td">· 구매 시 이머니 사용</td>
			</tr>
			</table>
<?php }?>

			<div class="item-title hidden">주문 시 적립금 사용 조건 <img src="/admin/skin/default/images/common/icon_oreder_tree_a.gif" align="absmiddle" /></div>

			<table width="100%" class="info-table-style hidden">
			<col width="200" /><col width="" />
			<tr>
				<th class="its-th">보유 적립금 조건</th>
				<td class="its-td">
					보유 적립금이  <input type="text" name="emoney_use_limit" style="text-align:right" class="line onlynumber" size="5" value="<?php echo $TPL_VAR["emoney_use_limit"]?>" />원 이상이면 적립금 사용 가능
				</td>
			</tr>
			<tr>
				<th class="its-th">상품 금액 조건</th>
				<td class="its-td">
					<span style="color:red;">&#123;상품 실 결제금액&#125;+&#123;좌동&#125;+&#123;좌동&#125;…</span> <input type="text" name="emoney_price_limit" style="text-align:right" size="5" class="line onlynumber" value="<?php echo $TPL_VAR["emoney_price_limit"]?>" />원 이상이면 적립금 사용 가능 <br>
					※ <span style="color:red;">상품 실 결제금액</span> = &#123;상품 할인가(판매가) x 수량&#125; – 할인(쿠폰,등급,좋아요,모바일,프로모션코드)
				</td>
			</tr>
			<tr>
				<th class="its-th">적립금 사용한도 조건</th>
				<td class="its-td">
					<table>
					<tr>
					<td>적립금은 최소 <input type="text" name="min_emoney" style="text-align:right" size="5" class="line onlynumber" value="<?php echo $TPL_VAR["min_emoney"]?>" />원 이상&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;~&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;최대&nbsp;&nbsp;</td>
					<td>
						<div><input type="radio" name="max_emoney_policy" value="unlimit" checked="checked" /><span class="label"> 1회 사용한도 제한 없이 사용 가능</span></div>
						<div><input type="radio" name="max_emoney_policy" value="percent_limit" /><span class="label"> <span style="color:red;">&#123;상품 실 결제금액&#125;+&#123;좌동&#125;+&#123;좌동&#125;…</span>의 </span><input type="text" name="max_emoney_percent" style="text-align:right" size="5" class="line onlynumber percent" value="<?php echo $TPL_VAR["max_emoney_percent"]?>" /><span class="label">% 금액까지 사용 가능</span></div>
						<div><input type="radio" name="max_emoney_policy" value="price_limit" /> <input type="text" name="max_emoney" style="text-align:right" size="5" class="line onlynumber" value="<?php echo $TPL_VAR["max_emoney"]?>" /><span class="label">원 까지 사용 가능</span></div>
					</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th class="its-th">적립금 사용 단위</th>
				<td class="its-td">
					<select name="emoney_using_unit">
						<option value="0" selected="selected">&nbsp;&nbsp;&nbsp;원 단위</option>
						<option value="1" <?php if($TPL_VAR["emoney_using_unit"]== 1){?>selected="selected"<?php }?>>십원 단위</option>
						<option value="2 "<?php if($TPL_VAR["emoney_using_unit"]== 2){?>selected="selected"<?php }?>>백원 단위</option>
						<option value="3 "<?php if($TPL_VAR["emoney_using_unit"]== 3){?>selected="selected"<?php }?>>천원 단위</option>
					</select>로 적립금 사용 가능
				</td>
			</tr>
			</table>


			<div class="item-title">주문 후 적립금/포인트 지급 <img src="/admin/skin/default/images/common/icon_oreder_tree_b.gif" align="absmiddle" />  <img src="/admin/skin/default/images/common/icon_oreder_tree_c.gif" align="absmiddle" /></div>
			<table width="100%" class="info-table-style">
			<col width="8%" /><col width="7%" />
<?php if($TPL_VAR["isplusfreenot"]){?><col width="38%" /><?php }?>
			<col width="38%" /><col width="9%" />
			<tr>
				<th class="its-th-align" colspan="2">구분</th>
				<th class="its-th-align">적립금(마일리지)</th>
<?php if($TPL_VAR["isplusfreenot"]){?><th class="its-th-align">포인트</th><?php }?>
				<th class="its-th-align">필독안내</th>
			</tr>
			<tr>
				<th class="its-th-align" colspan="2">구매 시 적립 상품</th>
				<td class="its-td">보내진 상품에 대해서만 정확하게 지급</td>
<?php if($TPL_VAR["isplusfreenot"]){?><td class="its-td">보내진 상품에 대해서만 정확하게 지급</td><?php }?>
				<td class="its-td-align center"><span class=" highlight-link hand" id="infos">자세히 ></span></td>
			</tr>
			<tr>
				<th class="its-th-align" rowspan="4">구매 시<br>적립 금액</th>
				<th class="its-th-align">상품</th>

				<td class="its-td">
					상품 실 결제금액의 <input type="text" name="default_reserve_percent" style="text-align:right" size="5" class="line onlynumber percent" value="<?php echo $TPL_VAR["default_reserve_percent"]?>" />% 금액을 적립금으로 지급<br>
					※ 상품별로 적립금을 세팅하고 싶으시면 상품 > <a href="/admin/goods" target="_blank"><span class=" highlight-link hand">상품리스트</span></a>에서 가능합니다
				</td><?php if($TPL_VAR["isplusfreenot"]){?>
				<td class="its-td">
					<label><input type="radio" name="default_point_type" value="per"> 상품 실 결제금액의 <input type="text" name="default_point_percent" style="text-align:right" size="5" class="line onlynumber percent" value="<?php echo $TPL_VAR["default_point_percent"]?>" />% 금액을 포인트로 지급</label>  <br>
					<label><input type="radio" name="default_point_type" value="app"> 상품 실 결제금액           <input type="text" name="default_point_app" style="text-align:right" size="5" class="line onlynumber" value="<?php echo $TPL_VAR["default_point_app"]?>" />원 당  <input type="text" name="default_point" style="text-align:right" size="5" class="line onlynumber" value="<?php echo $TPL_VAR["default_point"]?>" />포인트 지급</label>
				</td>
<?php }?>
				<td class="its-td-align center"><span class=" highlight-link hand" id="infos">자세히 ></span></td>
			</tr>
			<tr>
				<th class="its-th-align">그 외</th>
				<td class="its-td">이벤트/모바일/좋아요/회원등급의 사유로 추가 적립금 지급 가능</td>
<?php if($TPL_VAR["isplusfreenot"]){?><td class="its-td">이벤트/모바일/좋아요/회원등급의 사유로 추가 포인트 지급 가능</td><?php }?>
				<td class="its-td-align center">&nbsp;</td>
			</tr>
			<tr>
				<th class="its-th-align">제한 조건</th>
				<td class="its-td" colspan="3">
					<label><input type="radio" name="default_reserve_limit" value="0" checked="checked" /> A: 적립금을 사용해도 실 결제금액 기준으로 조건없이 지급 (권장)</label><br />
					<table width="85%" class="info-table-style mt3 ml15">
						<colgroup>
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="20%" />
						</colgroup>
						<tbody><tr>
							<th align="center" class="its-th-align pd5">판매가합계</th>
							<th align="center" class="its-th-align pd5">할인합계<br /><span class="helpicon" title="좋아요할인 + (상품)쿠폰할인 + (상품)코드할인 + 유입처할인 + 모바일 할인 + 회원등급할인"></span></th>
							<th align="center" class="its-th-align pd5">실 결제금액</th>
							<th align="center" class="its-th-align pd5" style="border-top:2px solid red;border-left:2px solid red;border-right:2px solid red;">기대적립금<br />(A 지급 적립금)</th>
							<th align="center" class="its-th-align pd5">구매 시<br />사용적립금</th>
							<th align="center" class="its-th-align pd5">기대적립금<br />-사용적립금<br />(C 지급 적립금)</th>
							<th align="center" class="its-th-align pd5">실 결제금액 - 사용 적립금으로<br />재계산된 적립금<br />(B 지급 적립금)</th>
						</tr>
						<tr>
							<td align="center" class="its-td-align pd5">66,000원</td>
							<td align="center" class="its-td-align pd5">6,000원</td>
							<td align="center" class="its-td-align pd5">60,000원</td>
							<td align="center" class="its-td-align pd5" style="border-left:2px solid red;border-right:2px solid red;border-bottom:2px solid red;"><strong>6,000원</strong></td>
							<td align="center" class="its-td-align pd5">4,000원</td>
							<td align="center" class="its-td-align pd5">2,000원</td>
							<td align="center" class="its-td-align pd5">5,600원</td>
						</tr>
					</tbody></table><br />
					<label><input type="radio" name="default_reserve_limit" value="3" <?php if($TPL_VAR["default_reserve_limit"]== 3){?>checked="checked"<?php }?> /> B: 적립금을 사용하면 사용한 적립금을 제외한 결제금액을 기준으로 적립금 지급 (권장하지 않음)</label><br />
					<table width="85%" class="info-table-style mt3 ml15">
						<colgroup>
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="20%" />
						</colgroup>
						<tbody><tr>
							<th align="center" class="its-th-align pd5">판매가합계</th>
							<th align="center" class="its-th-align pd5">할인합계<br /><span class="helpicon" title="좋아요할인 + (상품)쿠폰할인 + (상품)코드할인 + 유입처할인 + 모바일 할인 + 회원등급할인"></span></th>
							<th align="center" class="its-th-align pd5">실 결제금액</th>
							<th align="center" class="its-th-align pd5">기대적립금<br />(A 지급 적립금)</th>
							<th align="center" class="its-th-align pd5">구매 시<br />사용적립금</th>
							<th align="center" class="its-th-align pd5">기대적립금<br />-사용적립금<br />(C 지급 적립금)</th>
							<th align="center" class="its-th-align pd5" style="border-top:2px solid red;border-left:2px solid red;border-right:2px solid red;">실 결제금액 - 사용 적립금으로<br />재계산된 적립금<br />(B 지급 적립금)</th>
						</tr>
						<tr>
							<td align="center" class="its-td-align pd5">66,000원</td>
							<td align="center" class="its-td-align pd5">6,000원</td>
							<td align="center" class="its-td-align pd5">60,000원</td>
							<td align="center" class="its-td-align pd5">6,000원</td>
							<td align="center" class="its-td-align pd5">4,000원</td>
							<td align="center" class="its-td-align pd5">2,000원</td>
							<td align="center" class="its-td-align pd5" style="border-left:2px solid red;border-right:2px solid red;border-bottom:2px solid red;"><strong>5,600원</strong></td>
						</tr>
					</tbody></table>
					<p class="mt3 ml15" style="font-weight:bold;font-size:11px;">- 56,000원 기준으로 다시 적립금액을 계산하여 지급</p>
					<p class="mb5 ml15" style="font-weight:bold;font-size:11px;">- 위의 경우 60,000원 결제 시 6,000 원이니 56,000원일 때는 5,600원(60,000 : 6,000원 = 56,000 : B 기준 적립금)</p>
					<label><input type="radio" name="default_reserve_limit" value="2" <?php if($TPL_VAR["default_reserve_limit"]== 2){?>checked="checked"<?php }?> /> C: 적립금을 사용하면 기대 적립금에서 사용한 적립금을 뺀 만큼만 적립 (권장하지 않음)</label><br />
					<table width="85%" class="info-table-style mt3 ml15">
						<colgroup>
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="12%" />
							<col width="20%" />
						</colgroup>
						<tbody><tr>
							<th align="center" class="its-th-align pd5">판매가합계</th>
							<th align="center" class="its-th-align pd5">할인합계<br /><span class="helpicon" title="좋아요할인 + (상품)쿠폰할인 + (상품)코드할인 + 유입처할인 + 모바일 할인 + 회원등급할인"></span></th>
							<th align="center" class="its-th-align pd5">실 결제금액</th>
							<th align="center" class="its-th-align pd5">기대적립금<br />(A 지급 적립금)</th>
							<th align="center" class="its-th-align pd5">구매 시<br />사용적립금</th>
							<th align="center" class="its-th-align pd5" style="border-top:2px solid red;border-left:2px solid red;border-right:2px solid red;">기대적립금<br />-사용적립금<br />(C 지급 적립금)</th>
							<th align="center" class="its-th-align pd5">실 결제금액 - 사용 적립금으로<br />재계산된 적립금<br />(B 지급 적립금)</th>
						</tr>
						<tr>
							<td align="center" class="its-td-align pd5">66,000원</td>
							<td align="center" class="its-td-align pd5">6,000원</td>
							<td align="center" class="its-td-align pd5">60,000원</td>
							<td align="center" class="its-td-align pd5">6,000원</td>
							<td align="center" class="its-td-align pd5">4,000원</td>
							<td align="center" class="its-td-align pd5" style="border-left:2px solid red;border-right:2px solid red;border-bottom:2px solid red;"><strong>2,000원</strong></td>
							<td align="center" class="its-td-align pd5">5,600원</td>
						</tr>
					</tbody></table><br />
					<label><input type="radio" name="default_reserve_limit" value="1" <?php if($TPL_VAR["default_reserve_limit"]== 1){?>checked="checked"<?php }?> /> D: 적립금을 사용하면 적립금 지급하지 않음 (권장하지 않음)</label><br />
					<table width="86%" class="mt10" border="0" style="background-color:#ff0000;color:#fff;">
					<tr><td class="its-td-align pd5">필독! 중요 : 실제 적립금은 상품 수량 1개당 계산이 됩니다. 따라서 A, B, C 모두 적립금을 상품 1개당으로 계산되어서 지급 됩니다. 상품 1개당 재계산 참조 ↓<br />상품 1개당 적립금을 계산되어야만 배송시 배송된 상품수량만큼만 정확하게 적립금 지급이 되며 반품 시 반품된 상품수량만큼만 정확하게 적립금 회수가 되어 합리적인 운영이 가능하게 합니다.</td></tr></table>
					<table width="86%" class="info-table-style mt10 mb10">
						<tbody>
						<tr>
							<td align="center" class="its-th-align pd5" rowspan="2">&nbsp;</th>
							<td align="center" class="its-th-align pd5" colspan="7">공통</th>
							<td align="center" class="its-th-align pd5" style="background-color:#ffbbbb;">A 기준</th>
							<td align="center" class="its-th-align pd5" style="background-color:#ffc68c;" colspan="2">B 기준</th>
							<td align="center" class="its-th-align pd5" style="background-color:#3399ff;">C기준</th>
						</tr>
						<tr>
							<td align="center" class="its-th-align pd5" valign="top">단가<br />(할인포함)<br />(ㄱ)</th>
							<td align="center" class="its-th-align pd5" valign="top">수량<br />(ㄴ)</th>
							<td align="center" class="its-th-align pd5" valign="top">할인가<br />(ㄷ=ㄱ*ㄴ)</th>
							<td align="center" class="its-th-align pd5" valign="top" style="background-color:#ffbbbb;">1개당<br />적립금<br />(ㄹ)</th>
							<td align="center" class="its-th-align pd5" valign="top">사용적립금<br />(ㅁ)</th>
							<td align="center" class="its-th-align pd5" valign="top" style="color:#9900cc;">사용적립금을<br />할인가<br />비율대로<br />분배<br />(ㅂ)<br /></th>
							<td align="center" class="its-th-align pd5" valign="top">1개로<br />사용적립금<br />환산<br />(ㅅ=ㅂ/ㄴ)</th>
							<td align="center" class="its-th-align pd5" valign="top" style="background-color:#ffbbbb;">그대로<br />지급(ㄹ)</th>
							<td align="center" class="its-th-align pd5" valign="top" style="background-color:#ffc68c;">사용적립금을<br />뺀 단가<br />(ㅇ=ㄱ-ㅅ)</th>
							<td align="center" class="its-th-align pd5" valign="top" style="background-color:#ffc68c;color:#cc0000;">1개당<br />적립금<br />(ㅈ=ㅇ/ㄴ)</th>
							<td align="center" class="its-th-align pd5" valign="top" style="background-color:#3399ff;">1개당<br />사용적립금을<br />빼고 지급<br />(ㅊ=ㄹ-ㅅ)</th>
						</tr>
						<tr>
							<td align="center" class="its-td-align pd5">상품 1</td>
							<td align="right" class="its-td-align pd5">5,000</td>
							<td align="center" class="its-td-align pd5">8</td>
							<td align="right" class="its-td-align pd5">40,000</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffbbbb;">500</td>
							<td align="right" class="its-td-align pd5">&nbsp;</td>
							<td align="right" class="its-td-align pd5" style="color:#9900cc;">2,666</td>
							<td align="right" class="its-td-align pd5">333</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffbbbb">500</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffc68c;">4,667</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffc68c;color:#cc0000;">466</td>
							<td align="right" class="its-td-align pd5" style="background-color:#3399ff !important;">167</td>
						</tr>
						<tr>
							<td align="center" class="its-td-align pd5">상품 2</td>
							<td align="right" class="its-td-align pd5">10,000</td>
							<td align="center" class="its-td-align pd5">2</td>
							<td align="right" class="its-td-align pd5">20,000</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffbbbb;">1,000</td>
							<td align="right" class="its-td-align pd5">&nbsp;</td>
							<td align="right" class="its-td-align pd5" style="color:#9900cc;">1,333</td>
							<td align="right" class="its-td-align pd5">666</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffbbbb">1,000</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffc68c;">9,334</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffc68c;color:#cc0000;">933</td>
							<td align="right" class="its-td-align pd5" style="background-color:#3399ff !important;">334</td>
						</tr>
						<tr>
							<td align="center" class="its-td-align pd5">합계</td>
							<td align="right" class="its-td-align pd5">&nbsp;</td>
							<td align="right" class="its-td-align pd5">&nbsp;</td>
							<td align="right" class="its-td-align pd5">60,000</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffbbbb;">&nbsp;</td>
							<td align="center" class="its-td-align pd5">4,000</td>
							<td align="right" class="its-td-align pd5">&nbsp;</td>
							<td align="right" class="its-td-align pd5">&nbsp;</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffbbbb;">&nbsp;</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffc68c;">&nbsp;</td>
							<td align="right" class="its-td-align pd5" style="background-color:#ffc68c;">&nbsp;</td>
							<td align="right" class="its-td-align pd5" style="background-color:#3399ff !important;">&nbsp;</td>
						</tr>
					</tbody></table>
					<p class="ml5" style="color:#9900cc;">※ ㅂ 항목은 사용 적립금을 할인가 비율로 나누어서 1개당 사용 적립금을 구함. 즉 4,000원을 40,000원 : 20,000원 = 2:1 비율로 나눔.</p>
					<p class="ml5" style="color:#cc0000;">※ ㅈ 항목은 ㄱ : ㄹ = ㅇ : ㅈ 즉 ㅈ=ㄹ*ㅇ/ㄱ 으로 계산</p>
					<p class="ml5">※ 계산시 원단위 이하는 모두 버림</p>
				</td>
			</tr>
			<tr>
				<th class="its-th-align">유효기간</th>
				<td class="its-td">지급 적립금의 유효기간은
					<select name="reserve_select">
						<option value="">제한하지 않음</option>
						<option value="year">제한 - 12월31일</option>
						<option value="direct">제한 - 직접입력</option>
					</select>
					<span name="reserve_y" class="hide">→ 지급연도 + <input type="text" name="reserve_year" class="line onlynumber" min="0" max="9" style="text-align:right" size="3" maxlength='3'  value="<?php echo $TPL_VAR["reserve_year"]?>" />년 12월 31일</span>
					<span name="reserve_d" class="hide">→ <input type="text" name="reserve_direct" class="line onlynumber" style="text-align:right" size="3" value="<?php echo $TPL_VAR["reserve_direct"]?>" />개월</span>
				</td>
<?php if($TPL_VAR["isplusfreenot"]){?>
					<td class="its-td"><span <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?>  >지급 포인트의 유효기간은
					<select name="point_select" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> >
						<option value="">제한하지 않음</option>
						<option value="year">제한 - 12월31일</option>
						<option value="direct">제한 - 직접입력</option>
					</select>
					<span name="point_y" class="hide">→ 지급연도 + <input type="text" name="point_year" class="line onlynumber" min="0" max="9" style="text-align:right" size="3" maxlength="3" value="<?php echo $TPL_VAR["point_year"]?>" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> />년 12월 31일</span>
					<span name="point_d" class="hide">→ <input type="text" name="point_direct" class="line onlynumber" style="text-align:right" size="3" value="<?php echo $TPL_VAR["point_direct"]?>" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> />개월</span></span>
				</td>
<?php }?>
				<td class="its-td-align center">&nbsp;</td>
			</tr>

			<tr>
				<th class="its-th-align" colspan="2">구매 시 적립 시점</th>
				<td class="its-td" colspan="3">

<?php if($TPL_VAR["buy_confirm_use"]){?>
						<b>[구매확정 사용 중]</b><br />
						구매자가 '구매확정' 할 때 적립금/포인트 지급<br />
						단, <?php echo $TPL_VAR["save_term"]?>일 경과 후에는 자동으로 '구매확정' 처리하고
<?php if($TPL_VAR["save_type"]=='exist'){?>
						적립금/포인트는 소멸 시킴
<?php }else{?>
						적립금/포인트도 지급됨
<?php }?>
						<br />
						※ 적립금/포인트 적립 시점 세팅은 설정 &gt; <a href="order"><span class="highlight-link">주문</span></a>에서 가능합니다.					
<?php }else{?>
						<b>[구매확정 미사용 중]</b><br />
						관리자가 '배송완료' 처리 시 적립금/포인트 지급<br />
						※ 적립금/포인트 적립 시점 세팅은 설정 &gt; <a href="order"><span class="highlight-link">주문</span></a>에서 가능합니다.					
<?php }?>

				</td>
			</tr>
			</table>

			<div class="item-title hidden">포인트교환 <span class="helpicon" title="포인트를 적립금, 프로모션코드, 쿠폰 으로 전환할 수 있습니다."></span> <?php if(!$TPL_VAR["isplusfreenot"]){?><span class="null desc" style="font-weight: normal;">포인트의 적립금교환 기능은 업그레이드가 필요합니다. <img src='/admin/skin/default/images/common/btn_upgrade.gif' class='hand' onclick='serviceUpgrade();' align='absmiddle' /></span><?php }?></div>

			<table width="100%" class="info-table-style hidden">
			<col width="200" /><col width="" />
			<tr>
				<td class="its-th-align center">항목</td>
				<td class="its-th-align center">적립금</td>
				<td class="its-th-align center">프로모션코드</td>
				<td class="its-th-align center">쿠폰</td>
			</tr>
			<tr>
				<th class="its-th-align center">설명</th>
				<td class="its-td">마이페이지에서 적립금으로 교환 신청할 수 있음</td>
				<td class="its-td">마이페이지에서 프로모션 코드 다운로드 할 수 있음</td>
				<td class="its-td">마이페이지에서 쿠폰을 다운로드 할 수 있음</td>
			</tr>
			<tr>
				<th class="its-th-align center">설정</th>
				<td class="its-td ">
					<label><input type="radio" name="emoney_exchange_use" value="n" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> <?php if($TPL_VAR["emoney_exchange_use"]!='y'){?>checked<?php }?>> 사용안함 (적립금으로 교환 불가능)</label><br>
					<label><input type="radio" name="emoney_exchange_use" value="y" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> <?php if($TPL_VAR["emoney_exchange_use"]=='y'){?>checked<?php }?>> 사용함</label><br>
					<div style="padding-left:18px;" class="emoney_exchange_uselay">
						최소 교환 가능 포인트 : <input type="text" name="minum_point" value="<?php echo $TPL_VAR["emoney_minum_point"]?>" size="6" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> title="0" style="text-align:right;">
						<br><div style="padding-top:5px;">
						교환비율 설정 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : 포인트 <input type="text" name="point_rate" value="<?php echo $TPL_VAR["emoney_point_rate"]?>" size="6" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> title="0" style="text-align:right;">P 를 적립금 1원으로 교환
						<br><div style="padding-top:5px;">
						교환 적립금 유효기간 : 
							<select name="exchange_emoney_select" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> >
								<option value="">제한하지 않음</option>
								<option value="year">제한 - 12월31일</option>
								<option value="direct">제한 - 직접입력</option>
							</select>
							<span name="exchange_emoney_y" class="hide">→ 지급연도 + <input type="text" name="exchange_emoney_year" class="line onlynumber" min="0" max="9" style="text-align:right" size="3" maxlength="3" value="<?php echo $TPL_VAR["exchange_emoney_year"]?>" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> />년 12월 31일</span>
							<span name="exchange_emoney_d" class="hide">→ <input type="text" name="exchange_emoney_direct" class="line onlynumber" style="text-align:right" size="3" value="<?php echo $TPL_VAR["exchange_emoney_direct"]?>" <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?> />개월</span>
					</div>
				</td>
				<td class="its-td-align center"><span class="desc">프로모션/쿠폰 &gt; <a href="/admin/promotion/catalog" target="_blank"><span class="highlight-link">프로모션코드</span></a> </span></td>
				<td class="its-td-align center"><span class="desc">프로모션/쿠폰 &gt; <a href="/admin/coupon/catalog" target="_blank"><span class="highlight-link">쿠폰</span></td>
			</tr>
			</table>

		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>



<!-- #POP -->
<div id="infoPopup" style="display:none;">
	<table width="100%" class="info-table-style">
	<col width="20%" /><col width="80%" />
	<tr>
		<th class="its-th-align">구매 시 적립 상품</th>
		<td class="its-td">
			↓아래 예시와 같이 보낸 상품수량에 해당되는 적립금액만 정확하게 지급됩니다.<br>
			동일 상품 10개 주문(적립금 100원) → 상품 10개를 발송하여 적립금 1,000원 지급<br>
			동일 상품 10개 주문(적립금 100원) → 상품 1개를 발송하여 적립금 100원 지급
		</td>
	</tr>
	<tr>
		<th class="its-th-align">구매 시 적립 금액</th>
		<td class="its-td">
			<span style="color:red;">실 결제금액(9,000)</span> = 할인가(10,000) – 할인(1,000)<br>
			최종 결제금액 = 배송비 + <span style="color:red;">실 결제금액</span> – 사용 적립금액<br>
			<table width="90%" border="1">
			<tr>
				<td rowspan="2" align="center">단가</td>
				<td rowspan="2" align="center">수량</td>
				<td rowspan="2" align="center">할인가</td>
				<td colspan="4" align="center">할인</td>
			</tr>
			<tr>
				<td align="center">상품쿠폰</td>
				<td align="center">회원등급</td>
				<td align="center">상품Like</td>
				<td align="center">모바일</td>
			</tr>
			<tr>
				<td align="right" style="padding-right:7px;">1,000원</td>
				<td align="right" style="padding-right:7px;">10개</td>
				<td align="right" style="padding-right:7px;">10,000원</td>
				<td align="right" style="padding-right:7px;">250원</td>
				<td align="right" style="padding-right:7px;">250원</td>
				<td align="right" style="padding-right:7px;">250원</td>
				<td align="right" style="padding-right:7px;">250원</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<th class="its-th-align">구매 시 적립 시점</th>
		<td class="its-td">
			`구매확정`에 대한 안내<br>
			<table width="90%" border="1">
			<tr>
				<td align="center">조건</td>
				<td align="center">행위</td>
				<td align="center">적립금</td>
				<td align="center">포인트</td>
				<td align="center">구매확정</td>
				<td align="center">배송완료</td>
				<td align="center">이메일, SMS</td>
			</tr>
			<tr>
				<td align="center" rowspan="2">설정 기간 내에만</td>
				<td align="center">소비자가 구매확정</td>
				<td align="center">○</td>
				<td align="center">○</td>
				<td align="center">○(구매자)</td>
				<td align="center">○</td>
				<td align="center">○</td>
			</tr>
			<tr>
				<td align="center">관리자가 구매확정</td>
				<td align="center">○</td>
				<td align="center">○</td>
				<td align="center">○(관리자)</td>
				<td align="center">○</td>
				<td align="center">○</td>
			</tr>
			<tr>
				<td align="center">설정 기간 경과 후</td>
				<td align="center">관리자가 구매확정</td>
				<td align="center">△</td>
				<td align="center">△</td>
				<td align="center">○(자동/관리자)</td>
				<td align="center">○</td>
				<td align="center">○</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>

<div  id="promotioncodehelperlay"  class="hide" >
	<div style="margin:10px;"><span class="bold">프로모션 코드란?</span> 구매자가 온라인 쇼핑몰에서 물건을 구매할 때 할인 받을 수 있는 코드로 누구나 쉽게 사용할 수 있습니다.</div>
	<div style="border:0px #dddddd solid;padding:3px;width:95%;line-height:20px;">
	<table width="100%" class="info-table-style" align="center" >
	<colgroup><col width="100" /><col width="150" /><col width="150" /></colgroup>
	<tbody>
		<tr>
			<th class="its-th center" colspan="3"> 프로모션 코드 vs. 쿠폰 비교</th>
		</tr>
		<tr>
			<th class="its-th center" >비교 대상</th>
			<th class="its-th bold center" >프로모션 코드</th>
			<th class="its-th bold center" >쿠폰</th>
		</tr>
		<tr>
			<th class="its-th center" >배포 방법</th>
			<td class="its-td red center" >코드값 공개 (다운로드 불필요)</td>
			<td class="its-td red center" >소비자 다운로드</td>
		</tr>

		<tr>
			<th class="its-th center" >유효기간</th>
			<td class="its-td center" >유효기간 세팅 가능</td>
			<td class="its-td center" >(좌동)</td>
		</tr>
		<tr>
			<th class="its-th center" >혜택</th>
			<td class="its-td center" >구매 시 할인금액 세팅 가능</td>
			<td class="its-td center" >(좌동)</td>
		</tr>

		<tr>
			<th class="its-th center" >사용제한 – 구매금액</th>
			<td class="its-td center" >세팅 가능</td>
			<td class="its-td center" >(좌동)</td>
		</tr>
		<tr>
			<th class="its-th center" >사용제한 – 선착순</th>
			<td class="its-td center" >선착순 사용 제한 세팅 가능</td>
			<td class="its-td center" >다운로드 횟수 및 기간제한 세팅 가능</td>
		</tr>

		<tr>
			<th class="its-th center" >사용제한 - 회원</th>
			<td class="its-td red center" >비회원,회원 모두 사용 가능 <br/>(회원만 사용하도록 세팅도 가능)</td>
			<td class="its-td red center" >회원만 사용 가능</td>
		</tr>
		<tr>
			<th class="its-th center" >사용제한 - 상품</th>
			<td class="its-td center" >사용 가능 상품 세팅 가능</td>
			<td class="its-td center" >(좌동)</td>
		</tr>
		</tbody>
	</table>
	</div>
</div>
<?php $this->print_("layout_footer",$TPL_SCP,1);?>