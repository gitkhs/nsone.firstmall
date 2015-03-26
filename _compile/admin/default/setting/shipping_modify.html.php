<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/setting/shipping_modify.html 000025762 */ 
$TPL_data_issue_category_1=empty($TPL_VAR["data_issue_category"])||!is_array($TPL_VAR["data_issue_category"])?0:count($TPL_VAR["data_issue_category"]);
$TPL_data_issue_brand_1=empty($TPL_VAR["data_issue_brand"])||!is_array($TPL_VAR["data_issue_brand"])?0:count($TPL_VAR["data_issue_brand"]);
$TPL_data_issue_goods_1=empty($TPL_VAR["data_issue_goods"])||!is_array($TPL_VAR["data_issue_goods"])?0:count($TPL_VAR["data_issue_goods"]);
$TPL_data_except_goods_1=empty($TPL_VAR["data_except_goods"])||!is_array($TPL_VAR["data_except_goods"])?0:count($TPL_VAR["data_except_goods"]);
$TPL_sigungu_1=empty($TPL_VAR["sigungu"])||!is_array($TPL_VAR["sigungu"])?0:count($TPL_VAR["sigungu"]);?>
<style>
.descDeliveryCodePolicy {line-height:18px; font-size:12px; font-weight:bold;}
.descDeliveryCodePolicyGray,
.descDeliveryCodePolicyGray * {color:#999 !important; font-weight:Normal;}
</style>
<script type="text/javascript">
function openDialogSido(sidoFlag,idx){
	if(! $(this).is("#"+sidoFlag+"Id") ){
		$("body").append("<div id='"+sidoFlag+"Id'></div>");
		$.get('../popup/sido',{'sidoFlag':sidoFlag,'sido':'','idx':idx}, function(data) {
			$("#"+sidoFlag+"Id").html(data);
		});
		openDialog("시/군/구/읍/면/동/리 검색",sidoFlag+"Id", {"width":500,"height":600});
	}
}

function check_deliveryCostPolicy()
{
	$(".descDeliveryCodePolicy").addClass("descDeliveryCodePolicyGray");

	$("input[name='deliveryCostPolicy']").each(function(i){
		$(this).parent().parent().find("div").attr('disabled',true);
		if( $(this).val() == $("input[name='deliveryCostPolicy']:checked").val() ){
			$(this).parent().parent().find("div").attr('disabled',false);

			$(".descDeliveryCodePolicy").eq(i).removeClass("descDeliveryCodePolicyGray");
		}
	});
}

function check_postpaidDeliveryCostYn()
{
	var obj = $("input[name='postpaidDeliveryCostYn']");
	obj.parent().parent().find("input[type='text']").attr("disabled",true);
	if( obj.attr("checked") ){
		obj.parent().parent().find("input[type='text']").attr("disabled",false);
	}
}

function check_ifpostpaidDeliveryCostYn()
{
	var obj = $("input[name='ifpostpaidDeliveryCostYn']");
	obj.parent().parent().find("input[type='text']").attr("disabled",true);
	if( obj.attr("checked") ){
		obj.parent().parent().find("input[type='text']").attr("disabled",false);
	}
}

$(document).ready(function() {

<?php if($TPL_VAR["useYn"]){?>
	$("input[name='useYn'][value='<?php echo $TPL_VAR["useYn"]?>']").attr("checked",true);
<?php }?>
<?php if($TPL_VAR["deliveryCostPolicy"]){?>
	$("input[name='deliveryCostPolicy'][value='<?php echo $TPL_VAR["deliveryCostPolicy"]?>']").attr("checked",true);
<?php }?>
<?php if($TPL_VAR["postpaidDeliveryCostYn"]){?>
	$("input[name='postpaidDeliveryCostYn']").attr("checked",true);
<?php }?>
<?php if($TPL_VAR["ifpostpaidDeliveryCostYn"]){?>
	$("input[name='ifpostpaidDeliveryCostYn']").attr("checked",true);
<?php }?>
<?php if($TPL_VAR["multiDeliveryUseYn"]){?>
	$("input[name='multiDeliveryUseYn']").attr("checked",true);
<?php }?>

	check_deliveryCostPolicy();
	check_postpaidDeliveryCostYn();
	check_ifpostpaidDeliveryCostYn();


	$("#issueGoods").sortable();
	$("#issueGoods").disableSelection();

	$("#exceptIssueGoods").sortable();
	$("#exceptIssueGoods").disableSelection();

	$('img.goodsThumbView').each(function() {
		if (!this.complete ) {// image was broken, replace with your new image
			// this.src = '/data/icon/error/noimage_list.gif';
		}
	});

	/* 카테고리 불러오기 */
	category_admin_select_load('','category1','');
	$("select[name='category1']").bind("change",function(){
		category_admin_select_load('category1','category2',$(this).val());
		category_admin_select_load('category2','category3',"");
		category_admin_select_load('category3','category4',"");
	});
	$("select[name='category2']").bind("change",function(){
		category_admin_select_load('category2','category3',$(this).val());
		category_admin_select_load('category3','category4',"");
	});
	$("select[name='category3']").bind("change",function(){
		category_admin_select_load('category3','category4',$(this).val());
	});

	/* 브랜드 불러오기 */
	brand_admin_select_load('','brand1','');
	$("select[name='brand1']").bind("change",function(){
		category_admin_select_load('brand1','brand2',$(this).val());
		category_admin_select_load('brand2','brand3',"");
		category_admin_select_load('brand3','brand4',"");
	});
	$("select[name='brand2']").bind("change",function(){
		brand_admin_select_load('brand2','brand3',$(this).val());
		brand_admin_select_load('brand3','brand4',"");
	});
	$("select[name='brand3']").bind("change",function(){
		brand_admin_select_load('brand3','brand4',$(this).val());
	});

	orderDeliveryFree();

	$("button#issueGoodsButton").bind("click",function(){
		set_goods_list("issueGoodsSelect","issueGoods");
	});

	$("button#exceptIssueGoodsButton").bind("click",function(){
		set_goods_list("exceptIssueGoodsSelect","exceptIssueGoods");
	});
	
	$("input[name='deliveryCostPolicy']").bind("click",function(){
		if ($(this).val() == 'free')
		{
			$("input[name='orderDeliveryFree']").attr('checked',false);
		}
	});
	
	$("input[name='orderDeliveryFree'][value='free']").bind("click",function(){
		if( $("input[name='deliveryCostPolicy'][value='free']").attr('checked') ){
			$("input[name='orderDeliveryFree'][value='free']").attr('checked',false);
		}
	});

	


	$(".default_add_area").bind("click",function(){
		$.ajax({
			type: "get",
			url: "../setting/default_add_delivery",
			data: "",
			success: function(result){
				$("#default_add_delivery").html(result);
			}
		});
		openDialog("기본 도서지역 리스트", "default_add_delivery", {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
	});

});

function default_add_delivery_set(){
	var trObj, overlap;

	for(var i=0; i<$("input[name='default_add_seq[]'").length; i++){
		if($("input[name='default_add_seq[]'").eq(i).is(":checked")){
			//alert($("input[name='default_sigungu[]'").val());
			//alert($("input[name='default_sigungu_street[]'").val());
			//alert($("input[name='default_addDeliveryCost[]'").val());
			overlap = true;
			for(var j=0; j<$("input[name='sigungu[]'").length; j++){
				if($("input[name='sigungu[]'").eq(j).val() == $("input[name='default_sigungu[]'").eq(i).val()){
					overlap = false;
				}
			}
			if($("table#addDeliveryCostTable tbody tr").length == 0){
				trObj = '<tr><td class="its-td"><span class="btn small gray"><button class="searchArea" type="button">검색</button></span> <span class="sigungu" style="padding-left: 10px;">'+$("input[name='default_sigungu[]'").eq(i).val()+'</span><input name="sigungu[]" type="hidden" value="'+$("input[name='default_sigungu[]'").eq(i).val()+'"></td>	<td class="its-td"><span class="sigungu_street" style="padding-left: 10px;">'+$("input[name='default_sigungu_street[]'").eq(i).val()+'</span><input name="sigungu_street[]" type="hidden" value="'+$("input[name='default_sigungu_street[]'").eq(i).val()+'"></td>	<td class="its-td">	<input name="addDeliveryCost[]" class="line onlynumber" type="text" size="5" value="'+$("input[name='default_addDeliveryCost[]'").eq(i).val()+'">원	</td>	<td class="its-td-align center"><span class="btn small gray"><button class="delDeliveryCost" type="button">-</button></span></td></tr>';
				$("table#addDeliveryCostTable tbody").append(trObj);
			}else{
				if(overlap){
					trObj = $("table#addDeliveryCostTable tbody tr").eq(0).clone();
					trObj.find(".sigungu").html($("input[name='default_sigungu[]'").eq(i).val());
					trObj.find(".sigungu_street").html($("input[name='default_sigungu_street[]'").eq(i).val());
					trObj.find("input[name='sigungu[]']").val($("input[name='default_sigungu[]'").eq(i).val());
					trObj.find("input[name='sigungu_street[]']").val($("input[name='default_sigungu_street[]'").eq(i).val());
					trObj.find("input[name='addDeliveryCost[]']").val($("input[name='default_addDeliveryCost[]'").eq(i).val());
					$("table#addDeliveryCostTable tbody").append(trObj);
				}
			}
		}
	}
	closeDialog("#default_add_delivery");
}

function set_goods_list(displayId,inputGoods){
	$('#'+displayId).dialog('close');//초기화
	$.ajax({
		type: "get",
		url: "../goods/select",
		data: "page=1&adminshipping=Y&inputGoods="+inputGoods+"&displayId="+displayId,
		success: function(result){
			$("div#"+displayId).html(result);
		}
	});
	openDialog("상품 검색", displayId, {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
}

function orderDeliveryFree(){
	
	var obj = $("input[name='orderDeliveryFree']");

	if( obj.attr('checked') == false ){
		obj.parent().next().next().attr("disabled",true);
	}else{
		obj.parent().next().next().attr("disabled",false);
	}
}
</script>
<form name="shippingFrm" method="post" target="actionFrame" action="../setting_process/shipping">
<input type="hidden" name="shipping" value="<?php echo $_GET["code"]?>" />

<?php if($_GET["code"]!='add_delivery'&&$_GET["code"]!='address'){?>
<table width="100%" class="info-table-style">
<colgroup>
	<col width="15%" />
	<col />
</colgroup>
<tbody>
<tr>
	<th class="its-th-align center">사용설정</th>
	<td class="its-td">
	<label style="padding-right:30px;"><input type="radio" name="useYn" value="y" />사용</label>
	<label><input type="radio" name="useYn" value="n" checked="checked" />미사용</label>
	</td>
</tr>

<tr>
	<th class="its-th-align center">설명</th>
	<td class="its-td">
	<input type="text" name="summary" value="<?php echo $TPL_VAR["summary"]?>" class="line" size="70" />
	</td>
</tr>
<?php if($_GET["code"]=='delivery'){?>
<tr>
	<th class="its-th-align center">택배사</th>
	<td class="its-td">
		<div style="float:left;padding-right:5px;">
		<select name="deliveryCompany" size="22" style="width:250px;border:1px solid #ccc;">
<?php if(is_array($TPL_R1=config_load('delivery_url'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if(!in_array($TPL_K1,array('code22','code23','code24'))){?>
			<option value='<?php echo $TPL_K1?>'><?php echo $TPL_V1["company"]?></option>
<?php }?>
<?php }}?>
		</select>
		<span class="btn small gray"><button type="button" id="addDeliveryCompany">></button></span>
		</div>
		<div style="float:left;width:300px;height:323px;border-collapse:collapse;border:1px solid #ccc;padding-left:3px;overflow:auto">
		<ul>
<?php if(is_array($TPL_R1=get_shipping_company('domestic','delivery'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if(substr($TPL_K1, 0, 5)=='auto_'){?>
				<li code='<?php echo $TPL_K1?>' style="background-color:yellow"><span style='display:inline-block; width:225px;'><?php echo $TPL_V1["company"]?></span></li>
<?php }else{?>
				<li code='<?php echo $TPL_K1?>'><input type='hidden' name='deliveryCompanyCode[]' value='<?php echo $TPL_K1?>'><span style='display:inline-block; width:225px;'><?php echo $TPL_V1["company"]?></span><span class="btn small gray"><button type="button" class="removeDeliveryCompany">-</button></span></li>
<?php }?>
<?php }}?>
		</ul>
		</div>
		<div class="clearbox" style="padding:5px;"></div>
		<div class="desc">※ 배송추적URL 변경요청은 <a href="http://firstmall.kr/ec_hosting/customer/1to1.php" target="_blank">마이가비아 > 1:1문의게시판</a>을 통해 접수해 주시면 확인 후 처리되어집니다.</div>
	</td>
</tr>
<tr>
	<th class="its-th-align center">
	구매상품<br />
	조건 기준<br />
	배송비 무료화
	</th>
<?php if($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_STOR'){?>
	<td style="border-left:1px solid #dadada;">
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada; height:50px;">
		<br/>
		<span class="desc">해당 기능은 업그레이드가 필요합니다.</span> <img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand noshopfreelinknone" align="absmiddle" />
		</div>
	</td>
<?php }else{?>
	<td style="border-left:1px solid #dadada;">
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada;">
		<label><input type="checkbox" name="orderDeliveryFree" value="free" <?php if($TPL_VAR["orderDeliveryFree"]=='free'){?>checked<?php }?>> 아래의 조건을 만족하는 상품 구매 시 해당 주문의 배송비 → 무료(0원)</label>
		<div style="height:5px;"></div>
		<table width="100%" class="info-table-style">
			<tr>
			<th class="its-th-align center">
			적용상품
			</th>
			<td class="its-td-align" style="width:700px">

				<div id="issuescategorylay" style="width:100%" >
					<div style="margin:10px;">
						<div>
							<select class="line" name="category1">
								<option value="">1차 카테고리</option>
							</select>
							<select class="line" name="category2">
								<option value="">2차 카테고리</option>
							</select>
							<select class="line" name="category3">
								<option value="">3차 카테고리</option>
							</select>
							<select class="line" name="category4">
								<option value="">4차 카테고리</option>
							</select>
							<span class="btn small gray"><button type="button" id="issueCategoryButton">선택</button></span>
						</div>

						<div id="issueCategory" >
<?php if($TPL_data_issue_category_1){foreach($TPL_VAR["data_issue_category"] as $TPL_K1=>$TPL_V1){?>
						<div style='padding:5px;'>
						<span style='display:inline-block;'><?php echo $TPL_V1["name"]?></span>
						<span class='btn-minus'><button type='button' class='delCategory'></button></span>
						<input type="hidden" name='issueCategoryCode[]' value='<?php echo $TPL_K1?>' />
						</div>
<?php }}?>

						</div>
					</div>
				</div>
				<div style="clear: both"></div>
				<div id="issuesbrandlay" style="width:100%">
					<div  style="margin:10px;">
						<div>
							<select class="line" name="brand1">
								<option value="">1차 브랜드</option>
							</select>
							<select class="line" name="brand2">
								<option value="">2차 브랜드</option>
							</select>
							<select class="line" name="brand3">
								<option value="">3차 브랜드</option>
							</select>
							<select class="line" name="brand4">
								<option value="">4차 브랜드</option>
							</select>
							<span class="btn small gray"><button type="button" id="issueBrandButton">선택</button></span>
						</div>

						<div id="issueBrand">
<?php if($TPL_data_issue_brand_1){foreach($TPL_VAR["data_issue_brand"] as $TPL_K1=>$TPL_V1){?>
						<div style='padding:5px;'>
						<span style='display:inline-block;'><?php echo $TPL_V1["name"]?></span>
						<span class='btn-minus'><button type='button' class='delBrand'></button></span>
						<input type="hidden" name='issueBrandCode[]' value='<?php echo $TPL_K1?>' />
						</div>
<?php }}?>
						</div>
					</div>
				</div>
				<div style="clear: both"></div>
				<div id="issuesgoodslay">
					<div style="margin:10px;" >
						<div>
							<span class="btn small gray"><button type="button" id="issueGoodsButton">상품선택</button></span>
						</div>
						<div class="clearbox" style="height:5px;"></div>
						<div id="issueGoods" >
<?php if($TPL_data_issue_goods_1){foreach($TPL_VAR["data_issue_goods"] as $TPL_V1){?>
						<div class='goods fl move'>
						<div align='center' class='image'>

							<img class="goodsThumbView" alt="" src="<?php echo $TPL_V1["image"]?>" width="50" height="50">
						</div>
						<div align='center' class='name' style='width:70px;overflow:hidden;white-space:nowrap;'><?php echo $TPL_V1["goods_name"]?></div>
						<div align='center' class='price'><?php echo number_format($TPL_V1["price"])?></div>
						<input type="hidden" name='issueGoods[]' value='<?php echo $TPL_V1["goods_seq"]?>' />
						</div>
<?php }}?>
						</div>
						<div id="issueGoodsSelect"></div>
					</div>
				</div>

			</td>
			</tr>
			<tr>
			<th class="its-th-align center">
			예외
			</th>
			<td class="its-td-align">

				<div id="exceptgoodslay">
					<div style="margin:10px;">
						<div>
							<span class="btn small gray"><button type="button" id="exceptIssueGoodsButton">상품선택</button></span>
						</div>
						<div class="clearbox" style="height:5px;"></div>
						<div id="exceptIssueGoods">
<?php if($TPL_data_except_goods_1){foreach($TPL_VAR["data_except_goods"] as $TPL_V1){?>
						<div class='goods fl move'>
						<div align='center' class='image'>
							<img class="goodsThumbView" alt="" src="<?php echo $TPL_V1["image"]?>" width="50" height="50">
						</div>
						<div align='center' class='name' style='width:70px;overflow:hidden;white-space:nowrap;'><?php echo $TPL_V1["goods_name"]?></div>
						<div align='center' class='price'><?php echo number_format($TPL_V1["price"])?></div>
						<input type="hidden" name='exceptIssueGoods[]' value='<?php echo $TPL_V1["goods_seq"]?>' />
						</div>
<?php }}?>
						</div>
						<div id="exceptIssueGoodsSelect"></div>
					</div>
				</div>
			</td>
			</tr>
		</table>
		</div>
	</td>
<?php }?>
</tr>
<tr>
	<th class="its-th-align center">
	실 결제금액<br/>
	조건 기준<br/>
	배송비
	</th>
	<td style="border-left:1px solid #dadada;">
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada;">
		<label><input type="radio" name="deliveryCostPolicy" value="free"> 무료</label>
		</div>
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada;">
		<label><input type="radio" name="deliveryCostPolicy" value="pay"> 유료</label>
		<div style="padding-left:10px" disabled="disabled">선불 → 유료 <input type="text" name="payDeliveryCost" class="line onlynumber" size="5" value="<?php echo $TPL_VAR["payDeliveryCost"]?>" />원</div>
		<div style="padding-left:10px" disabled="disabled"><label><input type="checkbox" name="postpaidDeliveryCostYn" value="y"> 착불 → 유료</label> <input type="text" name="postpaidDeliveryCost" class="line onlynumber" size="5" value="<?php echo $TPL_VAR["postpaidDeliveryCost"]?>" />원 <span class="desc">(주문 시 배송비를 포함하지 않고 결제됨. 단 개별 배송비 상품은 선불로만 배송이 가능함)</span></div>
		</div>
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada;">
		<label><input type="radio" name="deliveryCostPolicy" value="ifpay" checked="checked"> 주문금액 기준 조건부 무료</label>
		<div style="padding-left:10px">할인적용가의 합이 <input type="text" name="ifpayFreePrice" class="line onlynumber" size="6" value="<?php echo $TPL_VAR["ifpayFreePrice"]?>" />원 이상이면 무료, 미만이면
		선불 → 유료 <input type="text" name="ifpayDeliveryCost" class="line onlynumber" size="5" value="<?php echo $TPL_VAR["ifpayDeliveryCost"]?>" />원</div>
		<div class="desc" style="padding-left:10px;">할인적용가란? 상품 판매가에서 각종 할인혜택(이벤트,복수구매,모바일,회원등급,좋아요,쿠폰,프로모션코드)이 적용된 금액입니다.</div>

		<div style="padding-left:10px" disabled="disabled"><label><input type="checkbox" name="ifpostpaidDeliveryCostYn" value="y"> 착불 → 유료</label> <input type="text" name="ifpostpaidDeliveryCost" class="line onlynumber" size="5" value="<?php echo $TPL_VAR["ifpostpaidDeliveryCost"]?>" />원 <span class="desc">(주문 시 배송비를 포함하지 않고 결제됨. 단 개별 배송비 상품은 선불로만 배송이 가능함)</span></div>
		</div>

	</td>
</tr>
<?php if($TPL_VAR["config_system"]["multiDelivery"]||$TPL_VAR["config_system"]["multi_shipping"]=='y'||$TPL_VAR["multiDeliveryUseYn"]){?>
<tr>
	<th class="its-th-align center">
	복수(다중) 배송지
<?php if($TPL_VAR["multi_shipping_service_limit"]){?>
	<span class="btn small cyanblue"><input type="button" onclick="serviceUpgrade();" value="업그레이드 >"></span>
<?php }?>
	</th>
	<td style="border-left:1px solid #dadada;" <?php if($TPL_VAR["multi_shipping_service_limit"]){?>disabled="disabled"<?php }?>>
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada;">
			<label><input type="checkbox" name="multiDeliveryUseYn" value="y"> 사용합니다 : 구매자가 복수 (N곳)의 배송지로 주문을 할 수 있으며, 복수의 배송지로 상품을 보냅니다.</label>
		</div>
		<div style="padding:5px 5px 5px 15px;border-bottom:1px solid #dadada;">
			<div class="descDeliveryCodePolicy">N곳 → 선불 : 무료 + 지역별추가배송비</div>
			<div class="descDeliveryCodePolicy">N곳 → 선불 : N × <span></span>원 + 지역별추가배송비</div>
			<div class="descDeliveryCodePolicy">N곳 → 선불 : <font color="red">N - (구매금액/<span></span>원)의 몫 × <span></span>원</font> + 지역별추가배송비</div>
		</div>
	</td>
</tr>
<?php }?>
<?php }?>
</tbody>
</table>
<?php }?>
<?php if($_GET["code"]=='add_delivery'){?>
<div class="item-title" style="width:97%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="70%">지역별 추가 배송비 <span class="helpicon" title="지역별 추가 배송비를 설정합니다."></span></td>
			<td width="30%" align="right"><span class="btn small"><button type="button" class="default_add_area">기본도서지역</button></span></td>
		</tr>
	</table>
</div>
<table width="100%" class="info-table-style" id="addDeliveryCostTable">
<colgroup>
	<col width="41%"/>
	<col width="41%"/>
	<col width="13%" />
	<col width="5%"/>
</colgroup>
<thead>
<?php if($_GET["code"]=='add_delivery'){?>
<tr>
	<th class="its-th-align center" colspan="2">지역</th>
	<th class="its-th-align center" rowspan="2">추가 배송비</th>
	<th class="its-th-align center" rowspan="2"><span class="btn small gray"><button type="button" id="addDeliveryCost">+</button></span></th>
</tr>
<tr>
	<th class="its-th-align center">지번</th>
	<th class="its-th-align center">도로명</th>
</tr>
<?php }?>
</thead>
<tbody>
<?php if(!$TPL_VAR["sigungu"]){?>
<tr>
	<td class="its-td"><span class="btn small gray"><button type="button" class="searchArea">검색</button></span> <span style="padding-left:10px;" class="sigungu"></span><input type="hidden" name="sigungu[]" value="" /></td>
	<td class="its-td">
	<input type="text" name="addDeliveryCost[]" class="line onlynumber" size="5" value="" />원
	</td>
	<td class="its-td-align center"><span class="btn small gray"><button type="button" class="delDeliveryCost">-</button></span></td>
</tr>
<?php }?>
<?php if($TPL_sigungu_1){foreach($TPL_VAR["sigungu"] as $TPL_K1=>$TPL_V1){?>
<tr>
	<td class="its-td"><span class="btn small gray"><button type="button" class="searchArea">검색</button></span> <span style="padding-left:10px;" class="sigungu"><?php echo $TPL_V1?></span><input type="hidden" name="sigungu[]" value="<?php echo $TPL_V1?>" /></td>
	<td class="its-td"><span style="padding-left:10px;" class="sigungu_street"><?php echo $TPL_VAR["sigungu_street"][$TPL_K1]?></span><input type="hidden" name="sigungu_street[]" value="<?php echo $TPL_VAR["sigungu_street"][$TPL_K1]?>" /></td>
	<td class="its-td">
	<input type="text" name="addDeliveryCost[]" class="line onlynumber" size="5" value="<?php echo $TPL_VAR["addDeliveryCost"][$TPL_K1]?>" />원
	</td>
	<td class="its-td-align center"><span class="btn small gray"><button type="button" class="delDeliveryCost">-</button></span></td>
</tr>
<?php }}?>
</tbody>
</table>
<?php }?>

<?php if($_GET["code"]=='address'){?>
<div class="item-title" style="width:92%"><span style="display:inline-block;"></span>보내는 곳 주소 및 반송 주소</div>
<table width="100%" class="info-table-style">
<col width="200" /><col width="" /><col width="200" /><col width="" />
<tr>
	<th class="its-th">보내는 곳 주소</th>
	<td class="its-td">
		<input type="text" name="senddingZipcode[]" value="<?php echo substr($TPL_VAR["sendding_zipcode"], 0, 3)?>" size="5" class="line" /> -
		<input type="text" name="senddingZipcode[]" value="<?php echo substr($TPL_VAR["sendding_zipcode"], 4, 3)?>" size="5" class="line" />
		<span class="btn small"><input type="button" id="senddingZipcodeButton" value="우편번호" /></span>
		<input type="text" name="senddingAddress" value="<?php echo $TPL_VAR["sendding_address"]?>" size="40" class="line" />
		<input type="text" name="senddingAddressDetail" value="<?php echo $TPL_VAR["sendding_address_detail"]?>" size="40" class="line" />
	</td>
</tr>
<tr>
	<th class="its-th">반송 주소</th>
	<td class="its-td">
		<input type="text" name="returnZipcode[]" value="<?php echo substr($TPL_VAR["return_zipcode"], 0, 3)?>" size="5" class="line" /> -
		<input type="text" name="returnZipcode[]" value="<?php echo substr($TPL_VAR["return_zipcode"], 4, 3)?>" size="5" class="line" />
		<span class="btn small"><input type="button" id="returnZipcodeButton" value="우편번호" /></span>
		<input type="text" name="returnAddress" value="<?php echo $TPL_VAR["return_address"]?>" size="40" class="line" />
		<input type="text" name="returnAddressDetail" value="<?php echo $TPL_VAR["return_address_detail"]?>" size="40" class="line" />
	</td>
</tr>
</table>
<?php }?>

<div style="padding:10px;" class="center">
<span class="btn large black"><button type="submit" class="addDeliveryCost">저장하기</button></span>
</div>
</form>