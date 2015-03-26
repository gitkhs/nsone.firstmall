<?php /* Template_ 2.2.6 2014/11/03 11:38:03 /www/nsone_firstmall_kr/admin/skin/default/setting/goods.html 000043400 */ 
$TPL_goodsaddinfoloop_1=empty($TPL_VAR["goodsaddinfoloop"])||!is_array($TPL_VAR["goodsaddinfoloop"])?0:count($TPL_VAR["goodsaddinfoloop"]);
$TPL_goodsoptionloop_1=empty($TPL_VAR["goodsoptionloop"])||!is_array($TPL_VAR["goodsoptionloop"])?0:count($TPL_VAR["goodsoptionloop"]);
$TPL_goodssuboptionloop_1=empty($TPL_VAR["goodssuboptionloop"])||!is_array($TPL_VAR["goodssuboptionloop"])?0:count($TPL_VAR["goodssuboptionloop"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<script type="text/javascript" src="/app/javascript/js/admin-goodsaddlayer.js?v=20140309"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquery.colorpicker.min.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/custom-color-picker.js"></script>
<script type="text/javascript">

$(document).ready(function() {

	/* 컬러피커 */
	//$(".colorpicker").customColorPicker();

	$("#labelcodetypeuse").live("click",function(){

		var windowLabelNametitle = "";
		var windowLabelValuetitle = "";
		var windowLabelCodetitle = "";
		if( $(this).attr("checked") ) {
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
				labelcodedisplay('dayinput');
<?php }else{?>
				labelcodedisplay('color');
<?php }?>
		}else{
			labelcodedisplay('');
		}
	});

	$(".labelcodetypech, .labelcodetypech_s").live("click",function() {
		if( $(this).attr("checked") ) {
			labelcodedisplay($(this).val());
		}else{
			labelcodedisplay('');
		}
	});

	$(".goodscodesettingBtn").click(function(){
		openDialog('상품코드 자동생성 규칙 세팅',"goodscodesettingDiv",{"width":"450","height":"800"});
	});

	$(".goodscodebatchBtn").click(function(){
		if(confirm("등록된 상품코드가 일괄 업데이트 합니다.\n정말로 일괄 업데이트를 실행하시겠습니까?")){
			openDialog("일괄 업데이트 실행 <span class='desc'>규칙에 따라 상품코드를 일괄 업데이트 합니다.</span>", "goodscodebatchlay", {"width":400,"height":200});
			goodscode_update('1');
		}
	});

	$(".labelList_goodsaddinfo").sortable();
	$(".labelList_goodsoption").sortable();
	$(".labelList_goodssuboption").sortable();
	//$(".labelList_goodscode").disableSelection();

	/* 크롬 브라우저 팝업창 띄운 후 sortable 드래그 오류로 추가 leewh 2014-10-01 */
	if (navigator.userAgent.match(/Chrome/)) {
		$('html, body').css('overflowY', 'auto');
	}
});


//옵션코드 기간, 날짜, 지역(주소), 색상표 추가작업
function labelcodedisplay(codetype) {
	var codesplit = codetype;
	var codetitle = "";
	var labelplushide = '';
	$(".labelcodetypech").removeAttr("readonly");

<?php if($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_PREM'||$TPL_VAR["service_code"]=='P_FAMM'){?>
	if(codesplit)	codesplit = "color";
	$(".labelcodetypech_s").removeAttr("disabled");
	$("#labelcodetype_color").attr("checked",true);
<?php }elseif($TPL_VAR["service_code"]=='P_EXPA'){?>
	$(".labelcodetypech").removeAttr("disabled");
	$(".labelcodetypech_s").removeAttr("disabled");
<?php }else{?>
	$(".labelcodetypech").removeAttr("disabled");
<?php }?>

	$(".labelcodetypech").removeAttr("checked");
	$(".labelcodetypehelp").removeClass("red");
	$(".windowlabelnew").hide();
	$(".labelAddbtn").show();

	switch(codetype) {
		case 'date':
			codetitle = "날짜";
		break;
		case 'address':
			codetitle = "지역";
			windowLabelNametitle = "예시) 제조사";
			windowLabelValuetitle = "예시) 프랑스";
			windowLabelCodetitle = "예시) french";
		break;
		case 'color':
			codetitle = "색상표";
			windowLabelNametitle = "예시) 색상";
			windowLabelValuetitle = "예시) dark blue";
			windowLabelCodetitle = "예시) dblue";
		break;
		case 'dayauto':
			labelplushide = 'hide';
			codetitle = "기간 자동 결정";
			$(".labelAddbtn").hide();
		break;
		case 'dayinput':
			labelplushide = 'hide';
			codetitle = "기간 수동 결정";
		break;
	}

	if( $("input[name=windowLabelSeq]").val() ) {//수정시 특수정보 설정불가
		$("#labelcodetypeuse").attr("readonly","readonly");
		$("#labelcodetypeuse").attr("disabled","disabled");
		if(codetype){
			$("#labelcodetype_"+codesplit).removeAttr("readonly");
			$("#labelcodetype_"+codesplit).removeAttr("disabled");
			$("#labelcodetype_"+codesplit).attr("checked",'checked');
			$("#labelcodetype_"+codesplit).parent().find(".labelcodetypehelp").addClass("red");
			$("#windowlabelnewtitle").text(codetitle);
			$(".windowlabelnew"+codesplit).show();
			if(labelplushide == 'hide') $(".labelAddbtn").hide();

			$(".labelcodetypech").attr("readonly","readonly");
			$(".labelcodetypech").attr("disabled","disabled");
			$(".labelcodetypech_s").attr("readonly","readonly");
			$(".labelcodetypech_s").attr("disabled","disabled");
		}else{
			$("#windowlabelnewtitle").text("없음");
			$(".labelcodetypech").attr("readonly","readonly");
			$(".labelcodetypech").attr("disabled","disabled");
			$(".labelcodetypech").removeAttr("checked");
			$(".labelcodetypech_s").attr("readonly","readonly");
			$(".labelcodetypech_s").attr("disabled","disabled");
			$(".labelcodetypech_s").removeAttr("checked");
		}
	}else{
		$("#labelcodetypeuse").removeAttr("readonly");
		$("#labelcodetypeuse").removeAttr("disabled");
		if(codetype){
			$("#labelcodetype_"+codesplit).removeAttr("readonly");
			$("#labelcodetype_"+codesplit).removeAttr("disabled");
			$("#labelcodetype_"+codesplit).attr("checked",'checked');
			$("#labelcodetype_"+codesplit).parent().find(".labelcodetypehelp").addClass("red");
			$("#windowlabelnewtitle").text(codetitle);
			$(".windowlabelnew"+codesplit).show();
			if(labelplushide == 'hide') $(".labelAddbtn").hide();
		}else{
			$("#windowlabelnewtitle").text("없음");
			$(".labelcodetypech").attr("readonly","readonly");
			$(".labelcodetypech").attr("disabled","disabled");
			$(".labelcodetypech").removeAttr("checked");
			$(".labelcodetypech_s").attr("readonly","readonly");
			$(".labelcodetypech_s").attr("disabled","disabled");
			$(".labelcodetypech_s").removeAttr("checked");
		}
	}

}

function img_view(str){

	if(str != ""){
		$("#imgView").html("<img src='"+str+"'>");
		$("#imgView").show();
	}

}

function colorpickerlay(){
	/* 컬러피커 */
	$(".colorpicker").customColorPicker();
}

var remainsec = 3;
function refresh()
{
	remainsec--;
	if (remainsec == 0)
	{
		var nextpage		= $('#nextpage').val();

		if(parseInt(nextpage) > 0) {
			getAjaxOfflineList(nextpage);
			remainsec= 3;
			refresh();
		}else{
			clearTimeout(timerid);
			$("#totalpagelayer").hide();
			$("#offlinelayfinish").show();
			//$("#totalcountlay").html(" 총 "+ setComma(data.totalcount) +" 건 ");
		}
		return false;
	}
	$('#sec_layer').html(remainsec);
	timerid = setTimeout("refresh()" , 1000);
}

// 상품코드 일괄 업데이트
function goodscode_update(page){
	$.ajax({
	    url : '../goods_process/batch_goodscode_all',
	   'data' : {'page':page},
		'type' : 'get',
		'dataType': 'json',
	    'success' : function(data){
			if(data.status == 'FINISH'){
				openDialogAlert("일괄 업데이트가 완료되었습니다.",'400','140',function(){$(".totalpagelayer").hide();
				$(".goodscodefinish").show();
				document.location.reload();});
			}else if(data.status == 'NEXT'){
				setTimeout("goodscode_update("+data.nextpage+")" , 1000);
			}
			$("#nowpage").text(page);
	    }
	});
}
</script>
<style>
/*레이어팝업*/
.layer_pop {border:3px solid #618298; background:#fff;}
.layer_pop .tit {height:45px; font:14px Dotum; letter-spacing:-1px; font-weight:bold; color:#003775; background:#ebf4f2; border-bottom:1px solid #d8dee3; padding:0 10px; border-right:0;}
.layer_pop .search_input {border:1px solid #cecece; height:17px;}
.layer_pop .left {text-align:left;}

</style>

<div id="goodscodebatchlay" class="hide">

<ul class="left-btns clearbox">
	<li class="left"><div style="margin-top:rpx;">
	총 <span id="totalcount" style="color:#000000; font-size:11px; font-weight: bold"><?php echo $TPL_VAR["totalcount"]?></span>개(현재 <span id="nowpage" >1</span>/총 <span id="totalpage" ><?php echo $TPL_VAR["totalpage"]?></span>페이지)</div></li>
</ul>
<div id="totalpagelayer" class="hidea" >
<table  style="width:100%">
<tr height=23><td>&nbsp;&nbsp;&nbsp;<font color=blue><u>창을 닫으면 상품코드 일괄 업데이트가 중단됩니다..</u></font></td></tr>
<tr height=5><td></td></tr></table>
</div>
<div id="goodscodefinish"  class="hide" ><font size=2 color=red><b> 상품코드 일괄 업데이트가 <span id='totalcountlay'></span>완료되었습니다.</b></font>
</div>

<!-- 서브 레이아웃 영역 : 끝 -->
</form>
</div>
<form name="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/goods" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 상품 코드/정보</h2>
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
	<div class='slc-body-wrap'>
		<div class="slc-body">

			<br style="line-height:10px;" />
			<div class="item-title">자동생성 상품코드 규칙<span class="desc"  style="font-weight:normal;">&nbsp;&nbsp;구매자가 주문을 완료하면 주문내역에 구매상품의 정보(원산지,제조사,브랜드,옵션 등)를 기준으로 조합되어 자동 생성된 고유한 코드가 있어 더욱 정확하게 상품을 배송할 수 있습니다.</span>
<?php if(!$TPL_VAR["isplusfreenot"]){?><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" />
<?php }else{?>
				<span style="float:right;padding-right:8px;"><span class="btn large black"><button type="button" class="goodscodesettingBtn" code="goodscodesetting" title="자동생성 규칙 셋팅" >자동생성 규칙</button></span></span>
<?php }?></div>

			<table  align="center" cellpadding="10" cellspacing="10" border="0">
			<tr>
				<td align="center"><img src="/admin/skin/default/images/common/img_goods_code.gif" /></td>
			</tr>
			</table>



			<table width="100%" class="joinform-user-table info-table-style" >
			<col width="70%" /><col width="30%" />
			<tr>
				<th class="its-th center">상품코드 자동생성 규칙</th>
				<th class="its-th center">일괄 업데이트</th>
			</tr>
			<tbody class="labelList_goodscodesetting">
			<tr>
				<td  class="its-td center">
<?php if($TPL_VAR["goodscodesettingview"]){?><span style="padding-left:30px; line-height:30px;font-size:14px; font-weight:bold; vertical-align:middle;"><?php echo $TPL_VAR["goodscodesettingview"]?></span><?php }else{?><span class="goodscodesettinglay desc" style="font-weight:normal;" >상품코드 자동생성 규칙이 세팅되지 않았습니다. 세팅해 주십시오.</span><?php }?>
				</td>
				<td  class="its-td center">
				좌측 규칙에 따라 상품코드를 일괄 업데이트 합니다.<br/>
				상품이 많을 경우 시간이 오래 걸릴 수 있습니다.<br/>
<?php if(!$TPL_VAR["isplusfreenot"]){?><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" />
<?php }else{?>
				<span class="btn large red"><button type="button" class="goodscodebatchBtn" code="goodscodebatch" title="일괄 업데이트 실행" >일괄 업데이트 실행</button></span>
<?php }?>
				</td>
			</tr>
			</tbody>
			</table>

			<input type="hidden"  id="optcoloraddruse" value="<?php echo $TPL_VAR["optcoloraddruse"]?>">

			<input type="hidden" name="windowLabelmaxSeq"  id="windowLabelmaxSeq" value="<?php if($TPL_VAR["maxseq"]){?><?php echo $TPL_VAR["maxseq"]?><?php }else{?>0<?php }?>">
			<input type="hidden" name="windowLabelSeq"  id="windowLabelSeq" value="">
			<input type="hidden" name="windowLabelId"  id="windowLabelId" value="">
			<input type="hidden" name="windowLabelnewtype"  id="windowLabelnewtype" value="">
			<input type="hidden" name="windowLabelNewtypeuse"  id="windowLabelNewtypeuse" value="">

			<br style="line-height:10px;" />

			<div class="item-title">
					<div class="left">추가정보용 정보값 및 코드값 (제조사, 원산지, 모델명 등)</div>
					<div class="left"  style="margin-left:410px;margin-top:-30px;font-weight:normal;line-height:15px;"  >
						<span  class="desc " >상품 등록/수정 시에 미리 저장된 정보값을 재활용하여 추가정보를 정확하게 관리할 수 있습니다.
						<br/>또한 추가 정보값별 등록된 코드값으로 상품코드를 생성할 수 있습니다.</span>
					</div>
<?php if(!$TPL_VAR["isplusfreenot"]){?>
					<div class="right"><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></div>
<?php }else{?>
					<div class="right" style="margin-top:-10px"><span style="float:right;padding-right:8px;"><span class="btn small black"><button type="button" class="goodscodeBtn" code="goodsaddinfo" title="추가정보 정보값 및 코드값 생성하기" windowlabeltitle="추가" >추가정보 정보값 및 코드값 생성하기</button></span></span>
					</div>
<?php }?>
			</div>

			<table width="100%" class="joinform-user-table info-table-style" >
			<col width="200" /><col width="" /><col width="80" />
			<tr>
				<th class="its-th">구분</th>
				<th class="its-th">정보값[코드값]</th>
				<th class="its-th">&nbsp;</th>
			</tr>
			<tbody class="labelList_goodsaddinfo">
<?php if($TPL_VAR["goodsaddinfoloop"]){?>
<?php if($TPL_goodsaddinfoloop_1){foreach($TPL_VAR["goodsaddinfoloop"] as $TPL_V1){?>
				<tr class="layer<?php echo $TPL_V1["codeform_seq"]?> hand ">
					<td class="its-td  <?php if($TPL_V1["codesetting"]== 1){?> red bold <?php }?>"><?php echo $TPL_V1["label_title"]?><!-- (<?php echo $TPL_V1["label_id"]?>) --> </td>
					<td class="its-td"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"> <?php echo $TPL_V1["label_view"]?></td>
					<td class="its-td">
<?php if($TPL_V1["base_type"]=='0'){?>
						<span class="btn small gray"><button type="button" class="listJoinBtn"    typeid="goodsaddinfo" value="<?php echo $TPL_V1["codeform_seq"]?>"  title="추가정보 정보값 및 코드값 수정하기" windowlabeltitle="추가" >수정</button></span>
<?php }?>
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][codeform_seq]" value="<?php echo $TPL_V1["codeform_seq"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][base_type]" value="<?php echo $TPL_V1["base_type"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][id]" value="<?php echo $TPL_V1["label_id"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][code]" value="<?php echo $TPL_V1["label_code"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][default]" value="<?php echo $TPL_V1["label_default"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][codesetting]" value="<?php echo $TPL_V1["codesetting"]?>">


						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][newtypeuse]" value="<?php echo $TPL_V1["label_newtypeuse"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][newtype]" value="<?php echo $TPL_V1["label_newtype"]?>">

						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][color]" value="<?php echo $TPL_V1["label_color"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][zipcode]" value="<?php echo $TPL_V1["label_zipcode"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][address_type]" value="<?php echo $TPL_V1["label_address_type"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][address]" value="<?php echo $TPL_V1["label_address"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][address_street]" value="<?php echo $TPL_V1["label_address_street"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][addressdetail]" value="<?php echo $TPL_V1["label_addressdetail"]?>">
						<input type="hidden" name="labelItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][biztel]" value="<?php echo $TPL_V1["label_biztel"]?>">
<?php if($TPL_V1["base_type"]=='0'){?>
						<span class="btn-minus" onclick="if(confirm('정말로 삭제하시겠습니까?') ) {deleteRow(this);}"><button type="button" title="삭제" ></button></span>
<?php }?>
						</td>
					</tr>
<?php }}?>
<?php }?>
			</tbody>
			</table>


			<br style="line-height:10px;" />

			<div class="item-title">
				<div>
					<div class="left">필수옵션용 정보값 및 코드값 (색상, 사이즈 등)</div>
					<div class="left"  style="margin-left:330px;margin-top:-30px;font-weight:normal;line-height:15px;"  >
						<span  class="desc " >상품 등록/수정 시에 미리 저장된 정보값을 재활용하여 필수옵션 정보를 정확하게 관리할 수 있습니다.
						<br/>또한 필수옵션 정보값별 등록된 코드값으로 상품코드를 생성할 수 있습니다.</span>
					</div>
				</div>
<?php if(!$TPL_VAR["isplusfreenot"]){?>
					<div class="right"><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></div>
<?php }else{?>
					<div class="right" style="margin-top:-10px"><span style="float:right;padding-right:8px;"><span class="btn small black"><button type="button"  class="goodscodeBtn" code="goodsoption" title="필수옵션 정보값 및 코드값 생성하기" windowlabeltitle="필수옵션" >필수옵션 정보값 및 코드값 생성하기</button></span></span>
					</div>
<?php }?>
			</div>

			<table width="100%" class="joinform-user-table info-table-style">
			<col width="200" /><col width="" /><col width="80" />
						<tr>
				<th class="its-th">구분</th>
				<th class="its-th">정보값[코드값]</th>
				<th class="its-th">&nbsp;</th>
			</tr>
			<tbody class="labelList_goodsoption">
<?php if($TPL_VAR["goodsoptionloop"]){?>
<?php if($TPL_goodsoptionloop_1){foreach($TPL_VAR["goodsoptionloop"] as $TPL_V1){?>
				<tr class="layer<?php echo $TPL_V1["codeform_seq"]?> hand">
					<td class="its-td  <?php if($TPL_V1["codesetting"]== 1){?> red bold <?php }?>"><?php echo $TPL_V1["label_title"]?><!-- (<?php echo $TPL_V1["label_id"]?>) --> </td>
					<td class="its-td"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"><?php echo $TPL_V1["label_view"]?> </td>
					<td class="its-td">
						<span class="btn small gray"><button type="button" class="listJoinBtn"  typeid="goodsoption"  value="<?php echo $TPL_V1["codeform_seq"]?>"   title="필수옵션 정보값 및 코드값 수정하기" windowlabeltitle="필수옵션" >수정</button></span>
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][codeform_seq]" value="<?php echo $TPL_V1["codeform_seq"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][id]" value="<?php echo $TPL_V1["label_id"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][code]" value="<?php echo $TPL_V1["label_code"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][default]" value="<?php echo $TPL_V1["label_default"]?>">

						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][newtypeuse]" value="<?php echo $TPL_V1["label_newtypeuse"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][newtype]" value="<?php echo $TPL_V1["label_newtype"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][color]" value="<?php echo $TPL_V1["label_color"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][zipcode]" value="<?php echo $TPL_V1["label_zipcode"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][address_type]" value="<?php echo $TPL_V1["label_address_type"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][address]" value="<?php echo $TPL_V1["label_address"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][address_street]" value="<?php echo $TPL_V1["label_address_street"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][addressdetail]" value="<?php echo $TPL_V1["label_addressdetail"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][biztel]" value="<?php echo $TPL_V1["label_biztel"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][address_commission]" value="<?php echo $TPL_V1["label_address_commission"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][date]" value="<?php echo $TPL_V1["label_date"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][sdayinput]" value="<?php echo $TPL_V1["label_sdayinput"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][fdayinput]" value="<?php echo $TPL_V1["label_fdayinput"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][dayauto_type]" value="<?php echo $TPL_V1["label_dayauto_type"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][sdayauto]" value="<?php echo $TPL_V1["label_sdayauto"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][fdayauto]" value="<?php echo $TPL_V1["label_fdayauto"]?>">
						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][dayauto_day]" value="<?php echo $TPL_V1["label_dayauto_day"]?>">


						<input type="hidden" name="labelItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][codesetting]" value="<?php echo $TPL_V1["codesetting"]?>">
						<span class="btn-minus" onclick="if(confirm('정말로 삭제하시겠습니까?') ) {deleteRow(this);}"><button type="button" title="삭제" ></button></span>
							</td>
						</tr>
<?php }}?>
<?php }?>
						</tbody>
					</table>

			<br style="line-height:10px;" />

			<div class="item-title">
				<div>
					<div class="left">추가구성옵션용 정보값 및 코드값 (벨트 추가, 깔창 추가, 메모리 추가 등)</div>
					<div class="left"  style="margin-left:510px;margin-top:-30px;font-weight:normal;line-height:15px;"  >
						<span  class="desc " >상품 등록/수정 시에 미리 저장된 정보값을 재활용하여 추가구성옵션 정보를 정확하게 관리할 수 있습니다.
						<br/>또한 추가구성옵션 정보값별 등록된 코드값으로 상품코드를 생성할 수 있습니다.</span>
					</div>
				</div>
<?php if(!$TPL_VAR["isplusfreenot"]){?>
					<div class="right"><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></div>
<?php }else{?>
					<div class="right" style="margin-top:-10px"><span style="float:right;padding-right:8px;"><span class="btn small black"><button type="button" class="goodscodeBtn" code="goodssuboption" title="추가구성옵션 정보값 및 코드값 생성하기" windowlabeltitle="추가구성옵션" >추가구성옵션 정보값 및 코드값 생성하기</button></span></span>
					</div>
<?php }?>
			</div>

			<table width="100%" class="joinform-user-table info-table-style">
			<col width="200" /><col width="" /><col width="80" />
			<tr>
				<th class="its-th">구분</th>
				<th class="its-th">정보값[코드값]</th>
				<th class="its-th">&nbsp;</th>
			</tr>
			<tbody class="labelList_goodssuboption">
<?php if($TPL_VAR["goodssuboptionloop"]){?>
<?php if($TPL_goodssuboptionloop_1){foreach($TPL_VAR["goodssuboptionloop"] as $TPL_V1){?>
				<tr class="layer<?php echo $TPL_V1["codeform_seq"]?> hand">
					<td class="its-td  <?php if($TPL_V1["codesetting"]== 1){?> red bold <?php }?>"><?php echo $TPL_V1["label_title"]?><!-- (<?php echo $TPL_V1["label_id"]?>)  --></td>
					<td class="its-td"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"><?php echo $TPL_V1["label_view"]?></td>
					<td class="its-td">
						 <span class="btn small gray"><button type="button" class="listJoinBtn"  typeid="goodssuboption"  value="<?php echo $TPL_V1["codeform_seq"]?>"  title="추가구성옵션 정보값 및 코드값 수정하기" windowlabeltitle="필수옵션" >수정</button></span>
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][codeform_seq]" value="<?php echo $TPL_V1["codeform_seq"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][id]" value="<?php echo $TPL_V1["label_id"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][code]" value="<?php echo $TPL_V1["label_code"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][default]" value="<?php echo $TPL_V1["label_default"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][codesetting]" value="<?php echo $TPL_V1["codesetting"]?>">

						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][newtypeuse]" value="<?php echo $TPL_V1["label_newtypeuse"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][newtype]" value="<?php echo $TPL_V1["label_newtype"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][color]" value="<?php echo $TPL_V1["label_color"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][zipcode]" value="<?php echo $TPL_V1["label_zipcode"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][address_type]" value="<?php echo $TPL_V1["label_address_type"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][address]" value="<?php echo $TPL_V1["label_address"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][address_street]" value="<?php echo $TPL_V1["label_address_street"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][addressdetail]" value="<?php echo $TPL_V1["label_addressdetail"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][biztel]" value="<?php echo $TPL_V1["label_biztel"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][date]" value="<?php echo $TPL_V1["label_date"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][sdayinput]" value="<?php echo $TPL_V1["label_sdayinput"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][fdayinput]" value="<?php echo $TPL_V1["label_fdayinput"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][dayauto_type]" value="<?php echo $TPL_V1["label_dayauto_type"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][sdayauto]" value="<?php echo $TPL_V1["label_sdayauto"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][fdayauto]" value="<?php echo $TPL_V1["label_fdayauto"]?>">
						<input type="hidden" name="labelItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][dayauto_day]" value="<?php echo $TPL_V1["label_dayauto_day"]?>">

						<span class="btn-minus" onclick="if(confirm('정말로 삭제하시겠습니까?') ) {deleteRow(this);}"><button type="button" title="삭제" ></button></span>
				</td>
			</tr>
<?php }}?>
<?php }?>
			</tbody>
			</table>


			<br style="line-height:10px;" />


		</div>

		<div id="html_error"></div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>

<div id="goodscodeDiv" class="layer_pop hide" >
	<!--입력폼 -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td  valign="top">
			<table  width="100%" class="joinform-user-table info-table-style" border="0" cellspacing="3" cellpadding="0" align="center" border="0" >
			<col  width="150" /><col   />
			<tr>
				<th  class="its-th-align center" > <span class="windowlabeltitle"></span> 정보명
				<td  class="its-td left">
				<input type="text" name="windowLabelName" value="" title="예시) 제조사" size="30" style="height:18px;" class="windowLabelName line">
				<div class="goodscodeoptionnew" >
					<label><input type="checkbox" name="labelcodetypeuse"  id="labelcodetypeuse" value="1" > 특수 정보 사용 : 상기 필수옵션은 기간, 날짜, 지역(주소), 색상표로 구매자에게 표현되어야 합니다.</label>
					<div class="desc"  style="margin-left:15px;">

					<label><input type="radio" name="labelcodetype"  id="labelcodetype_dayinput" class="labelcodetypech"  value="dayinput"  readonly="readonly" disabled="disabled" > <span class="labelcodetypehelp">수동기간 – 쿠폰 상품의 유효기간(사용기간)을 수동 결정 (또는 실물 상품의 기간 정보가 필요할 때)</span></label><br/>

					<label><input type="radio" name="labelcodetype"  id="labelcodetype_dayauto"  class="labelcodetypech"  value="dayauto"  readonly="readonly" disabled="disabled" > <span class="labelcodetypehelp">자동기간 – 쿠폰 상품의 유효기간(사용기간)을 자동 결정 (또는 실물 상품의 기간 정보가 필요할 때)</span></label><br/>

					<label><input type="radio" name="labelcodetype"  id="labelcodetype_date"  class="labelcodetypech"  value="date"  readonly="readonly" disabled="disabled" > <span class="labelcodetypehelp">날짜 – 쿠폰 상품의 유효일(ex.공연일)을 결정 (또는 실물 상품의 날짜 정보가 필요할 때)</span></label><br/>

					<label><input type="radio" name="labelcodetype"  id="labelcodetype_address"  class="labelcodetypech"  value="address"   readonly="readonly" disabled="disabled" > <span class="labelcodetypehelp">지역 – 쿠폰 상품의 사용위치(주소)를 결정 (또는 실물 상품의 지역 정보가 필요할 때)</span></label><br/>

					<label><input type="radio" name="labelcodetype" id="labelcodetype_color"  class="labelcodetypech_s"  value="color"  readonly="readonly" disabled="disabled" > <span class="labelcodetypehelp">색상 – 상품의 색상을 색상표로 표현하고자 할 때</span> </label>
					</div>
				</div>
				</td>
			</tr>
			</table>
	</td>
	</tr>
			<tr>
		<td  valign="top" class="center">
		<!--버튼 -->
		<div style="width:90%; padding:10px 0 0 33px;"><img src="/admin/skin/default/images/common/btn_confirm.gif" alt="확인" class="labelWriteBtn" style="border:0;cursor:pointer;" /></div>
		<!--//버튼 --></td>
			</tr>
			<tr>
		<td  valign="top" class="center">
			<br/>
			<table  width="100%" class="joinform-user-table info-table-style" border="0" cellspacing="3" cellpadding="0" align="center" border="0"  id="labelTable">
			<col width="75" /><col width="75" /><col width="20%" /><col   width="35%" /><col   width="25%" />
			<thead>
			<tr>
				<th  class="its-th-align center" id="labelTh" align="center"  rowspan="2" >순서</th>
				<th  class="its-th-align center" id="labelTh" align="center"  rowspan="2" >기본값</th>
				<th  class="its-th-align center" id="labelTh" align="center" <?php if($TPL_VAR["optcoloraddruse"]){?> colspan="2" <?php }?> ><span class="windowlabeltitle"></span> 정보값</th>
				<th  class="its-th-align center" id="labelTh" align="center" rowspan="2" ><span class="windowlabeltitle"></span> 정보값의 코드값</th>
			</tr>
			<tr>
				<th  class="its-th-align center" id="labelTh" align="center">텍스트</th>
<?php if($TPL_VAR["optcoloraddruse"]== 1){?><th  class="its-th-align center" id="labelTh" align="center" > 특수 정보 : <span id="windowlabelnewtitle" class="red">없음</span> </th> <?php }?>
			</tr>
			</thead>
			<tbody class="labelList_goodscode" >
			<tr id="labelTr"  class=" layer hand ">
				<td id="labelTd1"  class="its-td left"></td>
				<td id="labelTd2"  class="its-td left"></td>
				<td id="labelTd3"  class="its-td-align center"></td>
<?php if($TPL_VAR["optcoloraddruse"]== 1){?><td id="labelTd5"  class="its-td-align center" nowrap></td> <?php }?>
				<td id="labelTd4"  class="its-td-align center"></td>
			</tr>
			</tbody>
			</table>
				</td>
	</tr>
	<tr>
		<td  valign="top" class="center">
		<!--버튼 -->
		<div style="width:90%; padding:10px 0 0 33px;"><img src="/admin/skin/default/images/common/btn_confirm.gif" alt="확인" class="labelWriteBtn" style="border:0;cursor:pointer;" /></div>
		<!--//버튼 --></td>
	</tr>
	</table>
	<!--//입력폼 -->
</div>

<div id="goodscodesettingDiv" class="hide" >
<form name="GoodscodesettingForm" method="post" enctype="multipart/form-data" action="../setting_process/goodssetting" target="actionFrame">
	<span class="desc">자동생성 규칙에 조합할 항목을 체크하거나 조합순서를 변경하세요.</span>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
	<td  valign="top" class="center">

		<table width="100%" class="info-table-style" >
		<col width="50" /><col width="50" /><col  />
		<tr>
			<th class="its-th-align center " >조합</th>
			<th class="its-th-align center">순서</th>
			<th class="its-th-align center">추가정보 항목명</th>
		</tr>
		<tbody class="labelList_goodsaddinfo">
<?php if($TPL_VAR["goodsaddinfoloop"]){?>
<?php if($TPL_goodsaddinfoloop_1){foreach($TPL_VAR["goodsaddinfoloop"] as $TPL_V1){?>
			<tr class="settinglayer<?php echo $TPL_V1["codeform_seq"]?> hand">
				<td class="its-td-align">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][codeform_seq]" value="<?php echo $TPL_V1["codeform_seq"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][base_type]" value="<?php echo $TPL_V1["base_type"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][id]" value="<?php echo $TPL_V1["label_id"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][code]" value="<?php echo $TPL_V1["label_code"]?>">
				<input type="hidden" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][default]" value="<?php echo $TPL_V1["label_default"]?>">
				<input type="checkbox" name="SettingItem[goodsaddinfo][<?php echo $TPL_V1["codeform_seq"]?>][codesetting]" value="1" <?php echo $TPL_V1["label_codesetting"]?>/>
				</td>
				<td class="its-td-align"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"></td>
				<td class="its-td left"><?php echo $TPL_V1["label_title"]?></td>
			</tr>
<?php }}?>
<?php }else{?>
			<tr><td colspan="3"  class="its-td"><span class="desc">먼저 코드값을 생성하세요.</span></td></tr>
<?php }?>
		</tbody>
		</table>


		<br style="line-height:10px;" />


		<table width="100%" class="joinform-user-table info-table-style">
		<col width="50" /><col width="50" /><col  />
		<tr>
			<th class="its-th-align center " >조합</th>
			<th class="its-th-align center">순서</th>
			<th class="its-th-align center">필수옵션 항목명</th>
		</tr>
		<tbody class="labelList_goodsoption">
<?php if($TPL_VAR["goodsoptionloop"]){?>
<?php if($TPL_goodsoptionloop_1){foreach($TPL_VAR["goodsoptionloop"] as $TPL_V1){?>
			<tr class="settinglayer<?php echo $TPL_V1["codeform_seq"]?> hand">
				<td class="its-td-align">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][codeform_seq]" value="<?php echo $TPL_V1["codeform_seq"]?>">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][id]" value="<?php echo $TPL_V1["label_id"]?>">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][code]" value="<?php echo $TPL_V1["label_code"]?>">
				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][default]" value="<?php echo $TPL_V1["label_default"]?>">

				<input type="hidden" name="SettingItem[goodsoption][<?php echo $TPL_V1["codeform_seq"]?>][codesetting]" value="1">
				<input type="checkbox"  checked="checked"  readonly="readonly" disabled="disabled" />
				</td>
				<td class="its-td-align"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"></td>
				<td class="its-td left"><?php echo $TPL_V1["label_title"]?></td>
			</tr>
<?php }}?>
<?php }else{?>
			<tr><td colspan="3"  class="its-td"><span class="desc">먼저 필수옵션용 코드값을 생성하세요.</span></td></tr>
<?php }?>
		</tbody>
		</table>

		<br style="line-height:10px;" />

		<table width="100%" class="info-table-style">
		<col width="50" /><col width="50" /><col  />
		<tr>
			<th class="its-th-align center " >조합</th>
			<th class="its-th-align center">순서</th>
			<th class="its-th-align center">추가구성옵션 항목명</th>
		</tr>
		<tbody class="labelList_goodssuboption">
<?php if($TPL_VAR["goodssuboptionloop"]){?>
<?php if($TPL_goodssuboptionloop_1){foreach($TPL_VAR["goodssuboptionloop"] as $TPL_V1){?>
			<tr class="settinglayer<?php echo $TPL_V1["codeform_seq"]?> hand">
				<td class="its-td-align">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][codeform_seq]" value="<?php echo $TPL_V1["codeform_seq"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][type]" value="<?php echo $TPL_V1["label_type"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][id]" value="<?php echo $TPL_V1["label_id"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][name]" value="<?php echo $TPL_V1["label_title"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][value]" value="<?php echo $TPL_V1["label_value"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][code]" value="<?php echo $TPL_V1["label_code"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][default]" value="<?php echo $TPL_V1["label_default"]?>">
				<input type="hidden" name="SettingItem[goodssuboption][<?php echo $TPL_V1["codeform_seq"]?>][codesetting]" value="1">
				<input type="checkbox"  checked="checked"  readonly="readonly" disabled="disabled" />
				</td>
				<td class="its-td-align"><img src="/admin/skin/default/images/common/icon_move.gif" style="cursor:pointer"></td>
				<td class="its-td left"><?php echo $TPL_V1["label_title"]?></td>
			</tr>
<?php }}?>
<?php }else{?>
			<tr><td colspan="3"  class="its-td"><span class="desc">먼저 추가구성옵션용 코드값을 생성하세요.</span></td></tr>
<?php }?>
		</tbody>
		</table>
	</td>
	</tr>
	<tr>
		<td  valign="top" class="center">
		<!--버튼 -->
		<div style="width:90%; padding:10px 0 0 33px;"><input type="image" src="/admin/skin/default/images/common/btn_confirm.gif" alt="확인" class="SettingWriteBtn" style="border:0;cursor:pointer;" /></div>
		<!--//버튼 --></td>
	</tr>
	</table>
</form>
</div>
<div id="imgView" style="position:absolute; top:50%; left:30%; display:none; cursor:pointer" onclick="this.style.display = 'none'"></div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>