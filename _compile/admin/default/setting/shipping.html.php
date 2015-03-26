<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/setting/shipping.html 000033653 */ 
$TPL_internationalShipping_1=empty($TPL_VAR["internationalShipping"])||!is_array($TPL_VAR["internationalShipping"])?0:count($TPL_VAR["internationalShipping"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">
$(document).ready(function() {
<?php if($TPL_VAR["service_code"]=='P_STOR'){?>
	openDialog("쇼핑몰 업그레이드 안내<span class='desc'></span>", "nostorfreeService", {"width":600,"height":350}, function(){location.replace("/admin/");});	
<?php }?>

<?php if($TPL_VAR["service_limit"]){?>
	$("#addInternationalShipping").attr('disabled',true);
<?php }?>
	$(".modifyDeliveryButton").live("click",function(){
		$.get('shipping_modify?code='+$(this).attr('name'), function(data) {
		  	$('#shippingModifyPopup').html(data);
		});

		if( $(this).attr('name') == 'delivery'){
			openDialog($(this).attr('title')+" 설정", "shippingModifyPopup", {"width":"1000","height":700});
		}else{
			openDialog($(this).attr('title')+" 설정", "shippingModifyPopup", {"width":"1000","height":350});
		}
	});

	$("#addInternationalShipping").live("click",function(){

		$.get('international_shipping?code=regist', function(data) {
		  	$('#internationalShippingPopup').html(data);
		});
		openDialog("해외 배송 추가", "internationalShippingPopup", {"width":"95%","height":500});
		setDefaultText();
	});

	$(".modifyInternational").bind("click",function(){
		var code = $(this).attr("name");
		$.get('international_shipping?code='+code, function(data) {
		  	$('#internationalShippingPopup').html(data);
		});
		openDialog("해외 배송 수정", "internationalShippingPopup", {"width":"95%","height":500});
		setDefaultText();
	});

	$("#invoiceSetting").bind('click',function(){
		openDialog("택배 업무 자동화 서비스 세팅", "invoiceSettingPopup", {"width":800});

		setDefaultText();
	});

	$("input[name='invoice_notuse']").change(function(){
		if($(this).is(":checked")){
			$("#invoiceSettingAuthContainer *").attr("disabled",true);
			$("#invoiceSettingAuthContainer span.btn").addClass("gray");
			$("#invoiceSettingAuthContainer input.invoice_auth_code").val('');
		}else{
			$("#invoiceSettingAuthContainer *").removeAttr("disabled");
			$("#invoiceSettingAuthContainer span.btn").removeClass("gray");
		}
	}).change();


	$("form[name='invoiceSettingForm']").submit(function(){

		var returnValue = true;

		if(!$("input[name='invoice_notuse']").is(":checked")){
			$("input.invoice_auth_code").each(function(){
				var that = this;
				if($(this).val()!='' && $(this).val()!=$(this).attr("auth_code")) {
					openDialogAlert("인증버튼을 클릭하여 인증을 완료해 주시기바랍니다.",400,140,function(){
						$(that).focus();
					});
					returnValue = false;
				}
			});
		}

		return returnValue;
	});

	$("input.invoice_auth_code").bind('keyup change',function(){
		if($(this).val()!='' && $(this).val()==$(this).attr("auth_code")) {
			$(this).closest('div').children(".invoice_auth_code_desc").html("<span class='fx11 blue'>인증완료</span>");
		}else{
			$(this).closest('div').children(".invoice_auth_code_desc").html("");
		}
	}).change();

	$("#modifyShippingAdress").live("click",function(){
		openDialog("보내는 곳 주소 및 반송 주소", "modifyShippingAdressPopup", {"width":800});
		setDefaultText();
	});

    $("#senderZipcodeButton").live("click",function(){
        openDialogZipcode('sender');
    });
	$("#returnZipcodeButton").live("click",function(){
        openDialogZipcode('return');
    });

	// shipping_modify
	$("#addDeliveryCompany").live("click",function(e){
	    var obj = $("select[name='deliveryCompany'] option:selected");
	    var targetObj = $(this).parent().parent().parent();
	    var result = true;
	    targetObj.find("li").each(function(){
	        var clone = $(this).clone();
		if( clone.find("span").html() == obj.html()){
			result = false;
		}
	    });
	    if(obj.val() != "" && result ){
	    	var tag = "<li code='"+obj.val()+"'><input type='hidden' name='deliveryCompanyCode[]' value='"+obj.val()+"'><span style='display:inline-block; width:225px;'>"+obj.html()+"</span><span class=\"btn small gray\"><button type=\"button\" class=\"removeDeliveryCompany\">-</button></span></li>";
	    }
	    targetObj.find("ul").append(tag);
	    e.preventDefault();
		return false;
	});

	$(".removeDeliveryCompany").live("click",function(e){
	    $(this).parent().parent().remove();
	    e.preventDefault();
		return false;
	});

	$("#addDeliveryCost").live("click",function(e){
		if($("table#addDeliveryCostTable tbody tr").length == 0){
			trObj = '<tr><td class="its-td"><span class="btn small gray"><button class="searchArea" type="button">검색</button></span> <span class="sigungu" style="padding-left: 10px;"></span><input name="sigungu[]" type="hidden" value=""></td>	<td class="its-td"><span class="sigungu_street" style="padding-left: 10px;"></span><input name="sigungu_street[]" type="hidden" value=""></td>	<td class="its-td">	<input name="addDeliveryCost[]" class="line onlynumber" type="text" size="5" value="">원	</td>	<td class="its-td-align center"><span class="btn small gray"><button class="delDeliveryCost" type="button">-</button></span></td></tr>';
		}else{
			var trObj = $("table#addDeliveryCostTable tbody tr").eq(0).clone();
			trObj.find(".sigungu").html('');
			trObj.find("input").val('');
		}
		$("table#addDeliveryCostTable tbody").append(trObj);
		e.preventDefault();
		return false;
	});

	$(".delDeliveryCost").live("click",function(e){
		var trObj = $(this).parent().parent().parent();
		/*if(trObj.parent().find("tr").length > 1) */trObj.remove();
		e.preventDefault();
		return false;
	});

	$(".searchArea").live("click",function(e){
		var idx = $(this).parent().parent().parent().index();
		openDialogSido('sigungu',idx);
		e.preventDefault();
		return false;
	});

	$("input[name='orderDeliveryFree']").live("click",function(){
		orderDeliveryFree();
	});

	$("input[name='deliveryCostPolicy']").live("click",function(){
		check_deliveryCostPolicy();
	});
	$("input[name='postpaidDeliveryCostYn']").live("click",function(){
		check_postpaidDeliveryCostYn();
	});
	$("input[name='ifpostpaidDeliveryCostYn']").live("click",function(){
		check_ifpostpaidDeliveryCostYn();
	});

	$("button#issueCategoryButton").live("click",function(){
		var obj;
		var category;
		var categoryCode;

		obj = $("select[name='category1']");
		if(obj.val()){
			category = $("select[name='category1'] option[value='"+obj.val()+"']").html();
			categoryCode = obj.val();
		}
		obj = $("select[name='category2']");
		if(obj.val()){
			category += " > " + $("select[name='category2'] option[value='"+obj.val()+"']").html();
			categoryCode = obj.val();
		}
		obj = $("select[name='category3']");
		if(obj.val()){
			category += " > " + $("select[name='category3'] option[value='"+obj.val()+"']").html();
			categoryCode = obj.val();
		}
		obj = $("select[name='category4']");
		if(obj.val()){
			category += " > " + $("select[name='category4'] option[value='"+obj.val()+"']").html();
			categoryCode = obj.val();
		}

		if(category){
			if($("input[name='issueCategoryCode[]'][value='"+categoryCode+"']").length == 0){
				var tag = "<div style='padding:5px;'><span style='display:inline-block;'>"+category+"</span>";
				tag += "<span class='btn-minus'><button type='button' class='delCategory'></button></span>";
				tag += "<input type='hidden' name='issueCategoryCode[]' value='"+categoryCode+"' /></div>";
				$("div#issueCategory").append(tag);
			}
		}
	});

	$("button#issueBrandButton").live("click",function(){
		var obj;
		var brand;
		var brandCode;

		obj = $("select[name='brand1']");
		if(obj.val()){
			brand = $("select[name='brand1'] option[value='"+obj.val()+"']").html();
			brandCode = obj.val();
		}
		obj = $("select[name='brand2']");
		if(obj.val()){
			brand += " > " + $("select[name='brand2'] option[value='"+obj.val()+"']").html();
			brandCode = obj.val();
		}
		obj = $("select[name='brand3']");
		if(obj.val()){
			brand += " > " + $("select[name='brand3'] option[value='"+obj.val()+"']").html();
			brandCode = obj.val();
		}
		obj = $("select[name='brand4']");
		if(obj.val()){
			brand += " > " + $("select[name='brand4'] option[value='"+obj.val()+"']").html();
			brandCode = obj.val();
		}

		if(brand){			
			if($("input[name='issueBrandCode[]'][value='"+brandCode+"']").length == 0){
				var tag = "<div style='padding:5px;'><span style='display:inline-block;'>"+brand+"</span>";
				tag += "<span class='btn-minus'><button type='button' class='delBrand'></button></span>";
				tag += "<input type='hidden' name='issueBrandCode[]' value='"+brandCode+"' /></div>";
				$("div#issueBrand").append(tag);
			}
		}
	});

	$("button.delCategory").live("click",function(){
		$(this).parent().parent().remove();
	});

	$("button.delBrand").live("click",function(){
		$(this).parent().parent().remove();
	});
});

function hlc_auth(){
	var auth_code = $("input[name='auth_code[hlc]']").val();
	$.ajax({
		'url' : '../setting_process/hlc_auth',
		'type' : 'post',
		'data' : {'auth_code' : auth_code},
		'dataType' : 'json',
		'success' : function(result){
			if(result.code=='success'){
				$("input[name='auth_code[hlc]']").attr('auth_code',auth_code).change();
				openDialogAlert(result.msg,400,140);
			}else{
				openDialogAlert(result.msg,400,140,function(){
					$("input[name='auth_code[hlc]']").focus();
				});
			}
		}
	});
}
</script>
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 택배/배송비</h2>
		</div>

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
		<br style="line-height:10px;" />
		<div class="center" style="padding-left:20px;width:100%;text-align:center;">
			<div style="border:2px #dddddd solid;padding:10px;width:95%;">
				<table width="100%">
				<tr>
				<td align="left">
					무료몰+ : 국내 배송 방법으로 운영할 수 있습니다.<br>
					해외 배송 방법의 쇼핑몰을 운영하시려면 프리미엄몰+ 또는 독립몰+로 업그레이드 하시길 바랍니다.
				</td>
				<td align="right"><img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" /></td>
				</tr>
				</table>
			</div>
			<br style="line-height:20px;" />
		</div>
<?php }?>



		<div id="summary_message" class="center" style="font-size:20px;">실물 배송 상품의 국내 배송방법이 <strong class="red"><?php echo number_format($TPL_VAR["data_providershipping"]["delivery_cnt"])?></strong>개입니다.</div>
		<div class="item-title" style="width:92%">국내 - 기본 배송정책 <span class="desc" style="font-weight:normal">국내로 배송하는 정책(택배 및 배송비용)을 설정합니다. 설정된 정책은 국내 배송을 희망하는 주문자가 주문 시 받고 싶은 배송 방법을 선택할 수 있게 됩니다.</span></div>
		<div class="clearbox" style="padding:3px;">
			<table width="100%" class="info-table-style">

				<thead>

				<tr>
				<th class="its-th-align center" rowspan="2">실물 배송 상품</th>
				<th class="its-th-align center" rowspan="2">배송 방법</th>
				<th class="its-th-align center" rowspan="2">사용 여부</th>
				<th class="its-th-align center" colspan="4">배송비 계산</th>
				</tr>
				<tr>
				<th class="its-th-align center">택배사</th>
				<th class="its-th-align center">지정 상품 기준</th>
				<th class="its-th-align center">실 결제금액 기준</th>
				<th class="its-th-align center">추가 배송 <span class="btn small gray"><button type="button" class="modifyDeliveryButton" title="국내 – 택배 추가배송비" name="add_delivery">세팅</button></span></th>
				</tr>


				</thead>
				<tbody>

				<tr>
					<td class="its-td" rowspan="4">기본 배송 정책</td>
					<td class="its-td">
						택배 (선불)
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["delivery"]["useYn"]=='y'){?>
					<span style="color:blue">사용</span>
<?php }else{?>
					<span style="color:red">미사용</span>
<?php }?>
					<span class="btn small gray"><button type="button" class="modifyDeliveryButton" title="국내 – 택배 배송비" name="delivery">세팅</button></span>
					</td>
					<td class="its-td-align left" style="padding-left:5px;">
<?php if($TPL_VAR["config_system"]["invoice_use"]=='1'){?>
						<div> ● 현대택배(업무자동화)</div>
<?php }?>
<?php if(is_array($TPL_R1=explode(',',$TPL_VAR["loop"]["delivery"]["deliveryCompany"]))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
						<div> ● <?php echo $TPL_V1?></div>
<?php }}?>
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["delivery"]["orderDeliveryFree"]=='free'){?>
							<div>지정된 상품 구매 시 무료</div>
<?php if($TPL_VAR["data_providershipping"]["delivery"]["issueGoods"]||$TPL_VAR["data_providershipping"]["delivery"]["issueCategoryCode"]||$TPL_VAR["data_providershipping"]["delivery"]["issueCategoryCode"]){?>
							<div>지정 상품 있음</div>
<?php }?>
<?php }?>
					</td>
					<td class="its-td-align center">
						<div>
<?php if($TPL_VAR["data_providershipping"]["delivery"]["deliveryCostPolicy"]=='free'){?>
							무료
<?php }?>
<?php if($TPL_VAR["data_providershipping"]["delivery"]["deliveryCostPolicy"]=='pay'){?>
							<?php echo number_format($TPL_VAR["data_providershipping"]["delivery"]["payDeliveryCost"])?>원
<?php }?>

<?php if($TPL_VAR["data_providershipping"]["delivery"]["deliveryCostPolicy"]=='ifpay'){?>
							<?php echo number_format($TPL_VAR["data_providershipping"]["delivery"]["ifpayFreePrice"])?>원 이상 구매 시 무료, 미만  <?php echo number_format($TPL_VAR["data_providershipping"]["delivery"]["ifpayDeliveryCost"])?>원
<?php }?>
						</div>
					</td>

					<td class="its-td-align left" style="padding-left:5px;">
<?php if($TPL_VAR["data_providershipping"]["delivery"]["useYn"]=='y'&&$TPL_VAR["data_providershipping"]["delivery"]["sigungu"][ 0]){?>
<?php if(is_array($TPL_R1=$TPL_VAR["data_providershipping"]["delivery"]["sigungu"])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
							<div> ● <?php echo $TPL_V1?> <?php echo number_format($TPL_VAR["data_providershipping"]["delivery"]["addDeliveryCost"][$TPL_K1])?>원 추가</div>
<?php }}?>
<?php }?>
					</td>
				</tr>

				<tr>

					<td class="its-td">
						택배 (착불)
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["delivery"]["useYn"]=='y'&&($TPL_VAR["data_providershipping"]["delivery"]["postpaidDeliveryCostYn"]=='y'||$TPL_VAR["data_providershipping"]["delivery"]["ifpostpaidDeliveryCostYn"]=='y')){?>
					<span style="color:blue">사용</span>
<?php }else{?>
					<span style="color:red">미사용</span>
<?php }?>
					<span class="btn small gray"><button type="button" class="modifyDeliveryButton" title="국내 – 택배 배송비" name="delivery">세팅</button></span>
					</td>
					<td class="its-td-align center" style="padding-left:5px;">
						↑상동
					</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">
						<div>
<?php if($TPL_VAR["data_providershipping"]["delivery"]["ifpostpaidDeliveryCostYn"]=='y'){?><?php echo number_format($TPL_VAR["data_providershipping"]["delivery"]["ifpostpaidDeliveryCost"])?>원<?php }?>
<?php if($TPL_VAR["data_providershipping"]["delivery"]["postpaidDeliveryCostYn"]=='y'){?><?php echo number_format($TPL_VAR["data_providershipping"]["delivery"]["postpaidDeliveryCost"])?>원<?php }?>
						</div>
					</td>

					<td class="its-td-align center">-</td>
				</tr>
				<tr>

					<td class="its-td">
						퀵서비스 (착불)
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["quick"]["useYn"]=='y'){?>
					<span style="color:blue">사용</span>
<?php }else{?>
					<span style="color:red">미사용</span>
<?php }?>
					<span class="btn small gray"><button type="button" class="modifyDeliveryButton" title="국내 – 퀵서비스" name="quick">세팅</button></span>
					</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
				</tr>
				<tr>
					<td class="its-td">
						직접수령
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["direct"]["useYn"]=='y'){?>
					<span style="color:blue">사용</span>
<?php }else{?>
					<span style="color:red">미사용</span>
<?php }?>
					<span class="btn small gray"><button type="button" class="modifyDeliveryButton" title="국내 – 직접수령" name="direct">세팅</button></span>
					</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
				</tr>

				<tr>
					<td class="its-td" rowspan="4">개별 배송 정책</td>
					<td class="its-td">
						택배 (선불)
					</td>
					<td class="its-td-align center">
					상품별로 세팅<br/>
					<a href="/admin/goods/catalog" target="_blank"><span class="highlight-link">바로가기></span></a>
					</td>
					<td class="its-td-align center">=기본배송 정책</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">=기본배송 정책</td>
				</tr>

				<tr>
					<td class="its-td">
						택배 (착불)
					</td>
					<td class="its-td-align center">
					상품별로 세팅<br/>
					<a href="/admin/goods/catalog" target="_blank"><span class="highlight-link">바로가기></span></a>
					</td>
					<td class="its-td-align center">=기본배송 정책</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
				</tr>
				<tr>
					<td class="its-td">
						퀵서비스(착불)
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["quick_use_yn"]=='y'){?>
					<span style="color:blue">사용</span>
<?php }else{?>
					<span style="color:red">미사용</span>
<?php }?>
					<span class="desc">(=기본 배송)</span>
					</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
				</tr>
				<tr>
					<td class="its-td">
						직접수령
					</td>
					<td class="its-td-align center">
<?php if($TPL_VAR["data_providershipping"]["direct_use_yn"]=='y'){?>
					<span style="color:blue">사용</span>
<?php }else{?>
					<span style="color:red">미사용</span>
<?php }?>
					<span class="desc">(=기본 배송)</span>
					</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
					<td class="its-td-align center">-</td>
				</tr>


				</tbody>
			</table>
		</div>

		<div class="item-title"  style="width:92%">택배 업무 자동화 서비스 <span class="desc" style="font-weight:normal">주문된 상품을 택배로 보낼 때 반드시 해야 하는 택배 업무를 자동화합니다.</span></div>
		<table width="100%" class="info-table-style" style="table-layout:fixed">
			<colgroup>
				<col width="160"/>
				<col />
			</colgroup>
			<tr>
				<th class="its-th">
					서비스 세팅
					<span class="btn small red"><button type="button" id="invoiceSetting">세팅</button></span>
				</th>
				<td class="its-td" colspan="4">
<?php if($TPL_VAR["config_system"]["invoice_use"]=='1'){?>
						<div class="desc">
						[<?php echo $TPL_VAR["config_system"]["invoice_use_date"]?>] <span class="red">세팅 되었습니다. 앞으로의 출고건에 대하여 택배 업무 자동화가 아래의 설명과 같이 동작 가능합니다.</span>
						</div>
<?php }else{?>
					좌측의 세팅 버튼을 클릭하여 택배 업무 자동화 서비스를 세팅 해 주십시오.
<?php }?>
				</td>
			</tr>
			<tr>
				<th class="its-th" rowspan="2">
					자동화 서비스
				</th>
				<td class="its-td">
					①  운송장번호
				</td>
				<td class="its-td">
					② 운송장 출력
				</td>
				<td class="its-td">
					③ 택배사에 출고정보 전달
				</td>
				<td class="its-td">
					④ 배송 추적
				</td>
			</tr>
			<tr>
				<td class="its-td">
					수동 입력<br />→ 자동 할당
				</td>
				<td class="its-td">
					택배사 홈페이지에서 출력<br />→ 쇼핑몰 관리환경에서 바로 출력
				</td>
				<td class="its-td">
					택배사 홈페이지에서 엑셀 업로드<br />→ 택배사에 자동 전송
				</td>
				<td class="its-td">
					수동 처리<br />→ 자동으로 추적하여 배송완료 자동 처리
				</td>
			</tr>
		</table>

		<div class="item-title"  style="width:92%">해외 – 표준상품무게 배송정책
			<span class="desc" style="font-weight:normal">해외로 배송하는 정책(택배 및 배송비용)을 설정합니다.  설정된 정책은 해외 배송을 희망하는 주문자가 주문 시 받고 싶은 배송 방법을 선택할 수 있게 됩니다.</span>
		</div>
		<div style="position:relative"><div style="position:absolute;right:3px;top:-30px;"><span class="btn large gray"><button type="button" id="addInternationalShipping">해외배송 등록</button></span></div></div>
		<div class="clearbox" style="padding:3px;">
			<table width="100%" class="info-table-style">
				<colgroup>

				</colgroup>
				<thead>
				<tr>
					<th class="its-th-align center" rowspan="2">실물 배송 상품</th>
					<th class="its-th-align center" rowspan="2">배송 방법</th>
					<th class="its-th-align center" rowspan="2">사용 여부</th>
					<th class="its-th-align center" colspan="2">배송비 계산</th>
				</tr>
				<tr>
					<th class="its-th-align center">택배사</th>
					<th class="its-th-align center">무게별 지역별 차등 배송비</th>
				</tr>
				</thead>
				<tbody>

<?php if($TPL_internationalShipping_1){$TPL_I1=-1;foreach($TPL_VAR["internationalShipping"] as $TPL_V1){$TPL_I1++;?>
				<tr>
<?php if($TPL_I1== 0){?>
					<td class="its-td" rowspan="<?php echo $TPL_internationalShipping_1?>">
						<div>기본무게 정책</div>
						<div class="desc">무게가 비슷한 상품들</div>
					</td>
<?php }?>
					<td class="its-td">
						택배 (선불)
					</td>
					<td class="its-td-align center">
						<?php echo $TPL_V1["useYnMsg"]?>

						<span class="btn small gray">
						<button type="button" class="modifyInternational" name="<?php echo $TPL_V1["company"]?>">세팅</button>
						</span>
					</td>

					<td class="its-td"><span style="display:inline;padding-right:15px"><?php echo $TPL_V1["companyMsg"]?></span></td>
					<td class="its-td">
						배송 상품의 무게를 지역(배송거리)에 따라 배송비 부과
					</td>
				</tr>
<?php }}?>
				<tr>
					<td class="its-td">
						<div>개별무게 정책</div>
						<div class="desc">무게가 크게 다른 상품들</div>
					</td>
					<td class="its-td">
						택배 (선불)
					</td>
					<td class="its-td-align center">
						상품별로 세팅<br/>
						<a href="/admin/goods/catalog" target="_blank"><span class="highlight-link">바로가기></span></a>
					</td>
					<td class="its-td">=기본배송 정책</td>
					<td class="its-td">
						배송 상품의 무게를 지역(배송거리)에 따라 배송비 부과
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<div class="item-title"  style="width:92%">보내는 곳 주소 및 반송 주소</div>
		<div style="position:relative"><div style="position:absolute;right:3px;top:-30px;"><span class="btn large gray"><button type="button" id="modifyShippingAdress">수정</button></span></div></div>
		<div class="clearbox" style="padding:3px;">
		<table width="100%" class="info-table-style">
			<colgroup>
				<col width="19%"/>
				<col />
			</colgroup>
			<tr>
				<th class="its-th">
					보내는 곳 주소
				</th>
				<td class="its-td">
<?php if($TPL_VAR["config_shipping"]["senderAddress_street"]){?>
					<span <?php if($TPL_VAR["config_shipping"]["senderAddress_type"]=="street"){?>style="font-weight:bold;"<?php }?>>(도로명) </span>
					<?php echo implode('-',$TPL_VAR["config_shipping"]["senderZipcode"])?>

					<?php echo $TPL_VAR["config_shipping"]["senderAddress_street"]?>

					<?php echo $TPL_VAR["config_shipping"]["senderAddressDetail"]?>

					<br><span <?php if($TPL_VAR["config_shipping"]["senderAddress_type"]!="street"){?>style="font-weight:bold;"<?php }?>>(지번) </span><?php }?>
					<?php echo implode('-',$TPL_VAR["config_shipping"]["senderZipcode"])?>

					<?php echo $TPL_VAR["config_shipping"]["senderAddress"]?>

					<?php echo $TPL_VAR["config_shipping"]["senderAddressDetail"]?>

				</td>
			</tr>
			<tr>
				<th class="its-th">
					반송 주소
				</th>
				<td class="its-td">
<?php if($TPL_VAR["config_shipping"]["returnAddress_street"]){?>
					<span <?php if($TPL_VAR["config_shipping"]["returnAddress_type"]=="street"){?>style="font-weight:bold;"<?php }?>>(도로명) </span>
					<?php echo implode('-',$TPL_VAR["config_shipping"]["returnZipcode"])?>

					<?php echo $TPL_VAR["config_shipping"]["returnAddress_street"]?>

					<?php echo $TPL_VAR["config_shipping"]["returnAddressDetail"]?>

					<br><span <?php if($TPL_VAR["config_shipping"]["returnAddress_type"]!="street"){?>style="font-weight:bold;"<?php }?>>(지번) </span><?php }?>
					<?php echo implode('-',$TPL_VAR["config_shipping"]["returnZipcode"])?>

					<?php echo $TPL_VAR["config_shipping"]["returnAddress"]?>

					<?php echo $TPL_VAR["config_shipping"]["returnAddressDetail"]?>

				</td>
			</tr>
		</table>
		</div>

		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
	</div>


<!-- 서브 레이아웃 영역 : 끝 -->
<div id="shippingModifyPopup" style="display:none"></div>
<div id="internationalShippingPopup" style="display:none"></div>
<div id="invoiceSettingPopup" style="display:none">
	<form name="invoiceSettingForm" action="../setting_process/invoice_setting" target="actionFrame" method="post">

	<table class="info-table-style" width="100%">
	<col width="170" />
		<tr>
			<th class="its-th">사용여부</th>
			<td class="its-td">
				<label><input type="checkbox" name="invoice_notuse" value="1" <?php if(!$TPL_VAR["config_system"]["invoice_use"]){?>checked<?php }?> /> 사용하지 않겠습니다.</label>
			</td>
		</tr>
	</table>

	<div id="invoiceSettingAuthContainer">

		<div class="gabia-pannel" code="invoice_guide"></div>

		<!-- 현대택배 : start -->
		<table class="info-table-style" width="100%">
		<col width="170" />
		<tr>
			<th class="its-th">세팅비</th>
			<td class="its-td">
				<strike class="gray">110,000원</strike> → 0원 (이벤트 적용)
			</td>
		</tr>
		<tr>
			<th class="its-th">서비스 이용료</th>
			<td class="its-td">
				0원
			</td>
		</tr>
		<tr>
			<th class="its-th">계약 대리점명</th>
			<td class="its-td">
				<input type="text" class="line" name="branch_name" value="<?php echo $TPL_VAR["config_invoice"]["hlc"]["branch_name"]?>" />
				<span class="desc">예) 서울남부지점</span>
			</td>
		</tr>
		<tr>
			<th class="its-th">신용코드 인증</th>
			<td class="its-td">
				<div>
					<input type="text" class="invoice_auth_code line" name="auth_code[hlc]" value="<?php echo $TPL_VAR["config_invoice"]["hlc"]["auth_code"]?>" auth_code="<?php echo $TPL_VAR["config_invoice"]["hlc"]["auth_code"]?>" /> <span class="btn small black"><button type="button" onclick="hlc_auth()" >인증</button></span>
					<span class="invoice_auth_code_desc"></span>
				</div>
			</td>
		</tr>
		<tr>
			<th class="its-th">
				운송장 프린트 세팅
				<div class="gabia-pannel" code="invoice_print_setting_guide"></div>
			</th>
			<td class="its-td">
				<table cellpadding="0" cellspacing="0">
				<tr>
					<td><label><input type="radio" name="print_type" value="label_a" id="print_type_label_a" checked /> 라벨프린트 A타입</label></td>
					<td width="20"></td>
					<td><label><input type="radio" name="print_type" value="label_b" id="print_type_label_b"/> 라벨프린트 B타입</label></td>
					<td width="20"></td>
					<td><label><input type="radio" name="print_type" value="a4" id="print_type_a4" /> 레이저프린트 A4용지</label></td>
				</tr>
				<tr>
					<td height="5" colspan="5"></td>
				</tr>
				<tr>
					<td valign="top" align="center"><label for="print_type_label_a"><img src="/admin/skin/default/images/common/img_dliv_label_a.gif" /></label></td>
					<td></td>
					<td valign="top" align="center"><label for="print_type_label_b"><img src="/admin/skin/default/images/common/img_dliv_label_b.gif" /></label></td>
					<td></td>
					<td valign="top" align="center"><label for="print_type_a4"><img src="/admin/skin/default/images/common/img_dliv_a4.gif" /></label></td>
				</tr>
				</table>

				<script>
				$("input[name='print_type'][value='<?php echo $TPL_VAR["config_invoice"]["hlc"]["print_type"]?>']").attr('checked',true);
				</script>

			</td>
		</tr>
		</table>

		<!-- 현대택배 : end -->

	</div>
	<div class="center pdt20">
		<span class="btn medium cyanblue"><input type="submit" value="확인" /></span>
	</div>
	</form>
</div>
<div id="modifyShippingAdressPopup" style="display:none">
	<form name="invoiceSettingForm" action="../setting_process/modify_shipping_address" target="actionFrame" method="post">
	<table width="100%" class="info-table-style">
	<colgroup>
	<col width="19%"/>
	<col />
	</colgroup>
	<tr>
		<th class="its-th">
		보내는 곳 주소
		</th>
		<td class="its-td">
			<input type="text" name="senderZipcode[]" value="<?php echo $TPL_VAR["config_shipping"]["senderZipcode"][ 0]?>" size="4" class="line" /> -
			<input type="text" name="senderZipcode[]" value="<?php echo $TPL_VAR["config_shipping"]["senderZipcode"][ 1]?>" size="4" class="line" />
			<span class="btn small"><input type="button" id="senderZipcodeButton" value="우편번호" /></span><br />
			<input type="text" name="senderAddress_type" value="<?php echo $TPL_VAR["config_shipping"]["senderAddress_type"]?>" size="40" class="line hide" />
			(지번) <input type="text" name="senderAddress" value="<?php echo $TPL_VAR["config_shipping"]["senderAddress"]?>" size="60" class="line" /><br />
			(도로명) <input type="text" name="senderAddress_street" value="<?php echo $TPL_VAR["config_shipping"]["senderAddress_street"]?>" size="58" class="line " /><br />
			(공통상세) <input type="text" name="senderAddressDetail" value="<?php echo $TPL_VAR["config_shipping"]["senderAddressDetail"]?>" size="56" class="line" />
		</td>
	</tr>
	<tr>
		<th class="its-th">
		반송 주소
		</th>
		<td class="its-td">
			<input type="text" name="returnZipcode[]" value="<?php echo $TPL_VAR["config_shipping"]["returnZipcode"][ 0]?>" size="4" class="line" /> -
			<input type="text" name="returnZipcode[]" value="<?php echo $TPL_VAR["config_shipping"]["returnZipcode"][ 1]?>" size="4" class="line" />
			<span class="btn small"><input type="button" id="returnZipcodeButton" value="우편번호" /></span><br />
			<input type="text" name="returnAddress_type" value="<?php echo $TPL_VAR["config_shipping"]["returnAddress_type"]?>" size="40" class="line hide" />
			(지번) <input type="text" name="returnAddress" value="<?php echo $TPL_VAR["config_shipping"]["returnAddress"]?>" size="60" class="line" /><br />
			(도로명) <input type="text" name="returnAddress_street" value="<?php echo $TPL_VAR["config_shipping"]["returnAddress_street"]?>" size="58" class="line " /><br />
			(공통상세) <input type="text" name="returnAddressDetail" value="<?php echo $TPL_VAR["config_shipping"]["returnAddressDetail"]?>" size="56" class="line" />
		</td>
	</tr>
	</table>
	<div class="center pdt20">
		<span class="btn medium cyanblue"><input type="submit" value="확인" /></span>
	</div>
	</form>
</div>

<div id="default_add_delivery" class="hide"></div>
<?php $this->print_("layout_footer",$TPL_SCP,1);?>