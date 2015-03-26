/* 카테고리 연결 */
function add_category(cateCode,cateName){
	$("#connectCategoryInfo").hide();
	var t = $("input[name='connectCategory[]'][value='"+cateCode+"']").attr("type");
	if(t == "hidden") return false;		
	var trStr = "<tr>";
	trStr += "<td class=\"its-td-align center\"><label><input type=\"radio\" name=\"firstCategory\" value=\""+cateCode+"\" /></label>";
	trStr += "<input type=\"hidden\" name=\"connectCategory[]\" value=\""+cateCode+"\" />";
	trStr += "</td>";
	trStr += "<td class=\"its-td\"><div class=\"connectCategory\">"+cateName+"</div><div><span class=\"btn-minus\"><button type=\"button\" class=\"categoryDelete\"></button></span></div></td>";			
	trStr += "</tr>";	
	$("#connectCategoryTable").append(trStr);
	$("input:radio[name='firstCategory']").val([cateCode]);
}

/* 브랜드 연결 */
function add_brand(cateCode,cateName){
	$("#connectBrandInfo").hide();
	var t = $("input[name='connectBrand[]'][value='"+cateCode+"']").attr("type");
	if(t == "hidden") return false;		
	var trStr = "<tr>";
	trStr += "<td class=\"its-td-align center\"><label><input type=\"radio\" name=\"firstBrand\" value=\""+cateCode+"\" /></label>";
	trStr += "<input type=\"hidden\" name=\"connectBrand[]\" value=\""+cateCode+"\" />";
	trStr += "</td>";
	trStr += "<td class=\"its-td\"><div class=\"connectBrand\">"+cateName+"</div><div><span class=\"btn-minus\"><button type=\"button\" class=\"brandDelete\"></button></span></div></td>";			
	trStr += "</tr>";	
	$("#connectBrandTable").append(trStr);
	$("input:radio[name='firstBrand']").val([cateCode]);
}

/* 지역 연결 */
function add_location(cateCode,cateName){
	$("#connectLocationInfo").hide();
	var t = $("input[name='connectLocation[]'][value='"+cateCode+"']").attr("type");
	if(t == "hidden") return false;		
	var trStr = "<tr>";
	trStr += "<td class=\"its-td-align center\"><label><input type=\"radio\" name=\"firstLocation\" value=\""+cateCode+"\" /></label>";
	trStr += "<input type=\"hidden\" name=\"connectLocation[]\" value=\""+cateCode+"\" />";
	trStr += "</td>";
	trStr += "<td class=\"its-td\"><div class=\"connectLocation\">"+cateName+"</div><div><span class=\"btn-minus\"><button type=\"button\" class=\"locationDelete\"></button></span></div></td>";			
	trStr += "</tr>";	
	$("#connectLocationTable").append(trStr);
	$("input:radio[name='firstLocation']").val([cateCode]);
}


/* 이미지 업로드 레이어 보기 */
function showImageUploadDialog(){
	nowPath = "data/tmp";	
	$("#imageUploadDialog .uploadPath").html(nowPath);

	$("#imageUploadDialog").dialog("open");
	
	/* Uploadify path 변경 */
	$("#imageUploadButton").uploadifySettings('folder','/' + nowPath);	
}

function set_goodsImage_cut(){
	$("#goodsImageTable tbody tr").each(function(idx){
		var cutname = "<img src='/admin/skin/default/images/common/icon_move.gif'> 대표";
		if(idx > 0){
			cutname = "<img src='/admin/skin/default/images/common/icon_move.gif'>";
			cutname += idx + 1;			
			cutname += "번째 컷";
		}

		$html = $(this).find("td").eq(1).find(".fileColorTitle").html();
		$color = $(this).find("td").eq(1).find(".fileColorTitle").css("color");

		if($html) {
			cutname += " <input type=\"hidden\" name=\"goodsImageColor[]\" value=\""+rgb2hex($color)+"\" /><span class=\"fileColorTitle\" style=\"color:"+rgb2hex($color)+"\">"+$html+"</span>";
		} else {
			cutname += " <input type=\"hidden\" name=\"goodsImageColor[]\" value=\"\" /> <span class=\"fileColorTitle\"></span>";
		}

		$(this).find("td").eq(1).html(cutname);
	});	
}

function set_goodsImage_label(){
	//var division = $("form input[name='imgKind']").val();
	//var divisionIdx = $("form input[name='idx']").val();
	var division = $("#imgKind").val();
	var divisionIdx = $("#idx").val();
	$("form input[name='"+division+"GoodsLabel[]']").eq(divisionIdx).val($("form input[name='goodsImgLabel']").val());	
}

//필수 옵션 조합
function merge_option(opt1,opt2){
	var result = new Array();
	var k = 0;
	for (var i=0;i<opt1.length;i++){
		for (var j=0;j<opt2.length;j++){
			result[k] = opt1[i]+","+opt2[j];
			k++;
		}		
	}	
	return result;
}

function calulate_option_price(tmpidx){
	var container = $("#optionLayer");
	var idx;

	if(typeof tmpidx == 'undefined'){
		var selector = $("input[name='price[]']",container);
		var tmpidxch = false;
	}else{
		var selector = $("input[name='price[]']",container).eq(tmpidx);
		var tmpidxch = true;
	}

	selector.each(function(eachidx){
		if(tmpidxch) {
			idx = tmpidx;
		}else{
			idx = eachidx;
		}
		var rate = 0;
		var priceObj 		= $(this);
		var supplyPriceObj 	= $("input[name='supplyPrice[]']",container).eq(idx);
		var consumerPriceObj = $("input[name='consumerPrice[]']",container).eq(idx);
		var reserveRateObj 	= $("input[name='reserveRate[]']",container).eq(idx);
		var reserveUnit 	= $("select[name='reserveUnit[]']",container).eq(idx).find("option:selected").attr("value");
		var reserveObj 		= $("input[name='reserve[]']",container).eq(idx);
		var taxObj 			= $(".tax",container).eq(idx);
		var supplyRateObj 		= $(".supplyRate",container).eq(idx);
		var discountRateObj = $(".discountRate",container).eq(idx);	
		
		var obj = { 0:supplyPriceObj.val(), 1:consumerPriceObj.val(), 2:priceObj.val(), 3:reserveRateObj.val(), 4:reserveUnit };
		
		var result = calulate_price(obj);

		if(result[0]>0 && result[0]!='Infinity') result[0] = result[0]+"%";  
		else result[0] = "0%";
		supplyRateObj.html(result[0]);
		
		if(result[1]>0 && result[1]!='Infinity') result[1] = result[1]+"%"; 
		else result[1] = "0%";
		discountRateObj.html(result[1]);
		
		supplyPriceObj.val(Math.floor(supplyPriceObj.val()));
		consumerPriceObj.val(Math.floor(consumerPriceObj.val()));
		priceObj.val(Math.floor(priceObj.val()));

		// TAX @2014-03-17
		if( (eval("$(\"input[name='tax']:checked\").val()") && $("input[name='tax']:checked").val()!='tax') || (eval("$('.goodsTax').val()") && $(".goodsTax").val()!='tax') ){
			result[2] = 0;
		}

		taxObj.html(result[2]);
		reserveObj.val(result[3]);
		
		// 마진출력
		var net_profit = priceObj.val() - supplyPriceObj.val();
		$(".net_profit",container).eq(idx).html(comma(net_profit));
	});
}

function calulate_subOption_price(){
	var container = $("#suboptionLayer");
	// subSupplyPrice	subConsumerPrice	subPrice
	$("input[name='subPrice[]']",container).each(function(idx){		
		var priceObj 		= $(this);
		var supplyPriceObj 	= $("input[name='subSupplyPrice[]']",container).eq(idx);
		var consumerPriceObj = $("input[name='subConsumerPrice[]']",container).eq(idx);
		var subReserveRateObj 	= $("input[name='subReserveRate[]']",container).eq(idx);
		var subReserveUnit 	= $("select[name='subReserveUnit[]']",container).eq(idx).find("option:selected").attr("value");
		var subReserveObj 		= $("input[name='subReserve[]']",container).eq(idx);
		var taxObj 			= $(".subTax",container).eq(idx);
		var supplyRateObj 		= $(".subSupplyRate",container).eq(idx);
		var discountRateObj = $(".subDiscountRate",container).eq(idx);		
		var obj = { 0:supplyPriceObj.val(), 1:consumerPriceObj.val(), 2:priceObj.val(), 3:subReserveRateObj.val(), 4:subReserveUnit };
		var result = calulate_price(obj);	
		
		if(result[0]>0 && result[0]!='Infinity') result[0] = result[0]+"%";  
		else result[0] = "0%";
		supplyRateObj.html(result[0]);

		if(result[1]>0 && result[1]!='Infinity') result[1] = result[1]+"%"; 
		else result[1] = "0%";
		discountRateObj.html(result[1]);
		
		supplyPriceObj.val(Math.floor(supplyPriceObj.val()));
		consumerPriceObj.val(Math.floor(consumerPriceObj.val()));
		priceObj.val(Math.floor(priceObj.val()));	

		// TAX @2014-03-17
		if( (eval("$(\"input[name='tax']:checked\").val()") && $("input[name='tax']:checked").val()!='tax') || (eval("$('.goodsTax').val()") && $(".goodsTax").val()!='tax') ){
			result[2] = 0;
		}

		taxObj.html(result[2]);
		subReserveObj.val(result[3]);
		
		// 마진출력
		var net_profit = priceObj.val() - supplyPriceObj.val();
		$(".sub_net_profit",container).eq(idx).html(comma(net_profit));

	});
}

function calulate_price(obj){
	// supply,consumer,price,reserveRate,reserveUnit
	var rate;
	var result = new Array('','','');
	var supply = obj[0];
	var consumer = obj[1];
	var price = obj[2];
	if( obj[3] ) var reserveRate = obj[3];
	if( obj[4] ) var reserveUnit = obj[4];	
	
	// 매입율
	if( consumer && supply ){
		result[0] =  Math.floor( supply / consumer * 100 );		
	}
	
	// 할인율
	if( consumer && price ){
		result[1] =  100 - Math.floor( price / consumer * 100);
	}
	
	// 부가세
	if( price ){
		result[2] =  Math.floor( price - (price / 1.1) );
	}
	
	// 지급적립금
	if( price && reserveRate && reserveUnit ){			
		if( reserveUnit == 'percent' ) {
			rate = reserveRate / 100;
			result[3] =  Math.floor( price * rate ) ;
		}else{
			result[3] =  Math.floor( reserveRate );
		}			
	}	
	return result;	
}

// 옵션일괄적용 활성화
function check_button_optionBatch(){
	if($("input[name='defaultOption']")) $("#optionBatch").attr("disabled",false);
	else $("#optionBatch").attr("disabled",true);
}

function default_option_input(){
	var copyOptionTd;
	var len = $("#optionLayer table tr.optionTr").eq(0).find("td").length;	
	if(len > 8){
		for(var i=0;i<len-9;i++){
			$("#optionLayer table tr.optionTr").eq(0).find("td").eq(0).remove();
		}
	}	
	if( $("#optionLayer table tr").eq(1).children("td").html() ) copyOptionTd = $("#optionLayer table tr").eq(1).children("td");
	else copyOptionTd = $("#optionLayer table tr").eq(2).children("td");
	return copyOptionTd;
}

function get_option_title(){
	var point_text = $("#optionLayer .point_text").text();
	var tag;
	tag = '<tr>';		
	tag += '<th class="its-th-align center" rowspan="2"><span class="btn-plus"><button type="button" id="addOption"></button></span></th>';
	tag += '<th class="its-th-align center" rowspan="2">기준할인가</th>';
	tag += '<th class="its-th-align center">필수옵션</th>';
	tag += '<th class="its-th-align center" rowspan="2">매입가</th>';
	tag += '<th class="its-th-align center" rowspan="2">매입율</th>';
	tag += '<th class="its-th-align center" rowspan="2">정가(소비자가)</th>';
	tag += '<th class="its-th-align center" rowspan="2">마진 / 할인가(판매가)</th>';
	tag += '<th class="its-th-align center" rowspan="2">할인율</th>';
	tag += '<th class="its-th-align center" rowspan="2">부가세</th>';
	tag += '<th class="its-th-align center" rowspan="2">재고(가용)\n';	
	tag += '</th>';
	tag += '<th class="its-th-align center" rowspan="2">지급 적립금';
	tag += '<select name="reserve_policy">';
	tag += '<option value="shop">기본정책</option>';
	tag += '<option value="goods">개별정책</option>';
	tag += '</select>';
	tag += "<div style='color:#999999;' class='point_text'>"+point_text+"</div>";
	tag += '</th>';
	tag += '<th class="its-th-align center" rowspan="2">옵션정보</th>';	
	tag += '</tr>';
	
	tag += '<tr>';
	$("#optionMakePopup table tbody tr").each(function(idx){

		tag += '<th class="its-th-align center">';
		tag += $(this).find("input[name='optionMakeName[]']").val();
		tag += '<input type="hidden" name="optionTitle[]" value="'+$(this).find("input[name='optionMakeName[]']").val()+'" />';
		tag += '<input type="hidden" name="optionType[]" value="'+$(this).find("select[name='optionMakeId[]'] option:selected").val()+'" />';
		tag += '</th>';		
	});
	tag += '</tr>';
	
	
	return tag;
}

function make_option(){
	var key = 0;
	var cols = 0;
	var optName = new Array();
	var optionType = new Array();
	var optValue = new Array();
	var optPrice = new Array();
	var optCode = new Array();
	var result = new Array();
	var resulttype = new Array();
	var resultPrice = new Array();
	var preTag, tmp; 
	var pattern = /[\,]/;
							
	$("#optionMakePopup table tbody tr").each(function(idx){
		optionType[idx] = $(this).find("select[name='optionMakeId[]'] option:selected").val();

		tmp = $(this).find("input[name='optionMakeValue[]']").val();
		optValue[idx] = tmp.split(',');
		tmp = $(this).find("input[name='optionMakePrice[]']").val();
		optPrice[idx] = tmp.split(',');

		tmp = $(this).find("input[name='optionMakeCode[]']").val();
		optCode[idx] = tmp.split(',');

		cols = idx;
	});

	/* 옵션값 공백 체크 */
	for(var i=0;i<optValue.length;i++){
		for(var j=0;j<optValue[i].length;j++){
			if(optValue[i][j].length==0) {
				openDialogAlert("옵션값을 입력해주세요.",400,140,function(){
					$("#optionMakePopup input[name='optionMakeValue[]']").filter(function(){
						return $(this).val().length==0;
					}).eq(0).focus();
				});
				return false;
			}
		}
	}

	var clone = $("#optionLayer table").clone();		
	var copyOptionTd = default_option_input();
	var tag = get_option_title();
	
	/* 가용재고 : 원래는 유지해야하지만, 일단 첫번 0으로 초기화시킴 */
	copyOptionTd.find("input[name='badstock[]']").val('0');
	copyOptionTd.find("input[name='reservation25[]']").val('0');
	copyOptionTd.find("input[name='reservation25[]']").val('0');
	copyOptionTd.find("input[name='unUsableStock[]']").val('0');
	copyOptionTd.find("span.optionUsableStock").html(comma(num(copyOptionTd.find("input[name='stock[]']").val())));
	
	for ( var i=0;i<optValue.length;i++ ){	
			if(!optValue[i]) continue;
		for ( var j=0;j<optValue[i].length;j++ ){				
			if(! optPrice[i][j] ) optPrice[i][j] = 0;		
		}
	}		
	
	for (var i=0;i<optValue.length;i++){			
		if(!optValue[i]) continue; 
		if( i == 0 ){
			result = optValue[i];
			resultcode = optCode[i];
		} else {
			result = merge_option(result,optValue[i]);
			resultcode = merge_option(resultcode,optCode[i]);
		}			
	}	
	
	for (var i=0;i<result.length;i++){
		result[i] = result[i].split(',');
		if(resultcode[i]){
			resultcode[i] = resultcode[i].split(',');
		}else{
			resultcode[i] = '';
		}
	}		
	
	for (var i=0;i<optPrice.length;i++){
		if(i == 0 && optPrice[i] ){
			resultPrice = optPrice[i];
		}else if( optPrice[i] ){
			resultPrice = merge_option(resultPrice,optPrice[i]);
		}
	}		
	
	for (var i=0;i<resultPrice.length;i++){		
		if (pattern.test(resultPrice[i])){
			resultPrice[i] = resultPrice[i].split(',');
		}
	}
	
	for (var i=0;i<result.length;i++){
		key=0;
		tag += '<tr class="optionTr">';			
		for (var j=0;j<result[i].length;j++){
			var tmpType = $.trim(optionType[key]);
			var tmpValue = $.trim(result[i][j]);
			var tmpCode = $.trim(resultcode[i][j]);
			//tag += '<td class="its-td-align center"><input type="text" size="10" name="opt['+key+'][]" value="' + tmpValue + '" /></td>';
			if( tmpType == 'direct' || !tmpType ) {
				tag += '<td class="its-td-align center"><input type="text" size="10" name="opt['+key+'][]" value="' + tmpValue + '" class="line "/>';
			}else{
				tag += '<td class="its-td-align center"><input type="text" size="10" name="opt['+key+'][]" value="' + tmpValue + '" class="line input-box-default-text-code "/>';
			}
			tag += '<input type="hidden" size="10" name="optcode['+key+'][]" value="' + tmpCode + '" /></td>';
			key++;
		}
		tag += '</tr>';			
	}
	
	clone.empty();//clone.find("*").remove();		
	clone.html(tag);		
	clone.find("tr").eq(0).find("th").eq(2).attr('colspan',cols+1);
			
	key = 0;		
	clone.find("tr").each(function(idx){
		if(idx > 1) {				
			preTag = '<td class="its-td-align center">';
			preTag += '<span class="btn-minus"><button type="button" class="removeOption"></button></span>';
			preTag += '</td>';				
			if(key == 0){
				preTag += '<td class="its-td-align center"><input type="radio" name="defaultOption" value="'+result[key]+'" checked="checked" /></td>';
			}else{
				preTag += '<td class="its-td-align center"><input type="radio" name="defaultOption" value="'+result[key]+'" /></td>';
			}
			sum = 0;
			if(result[key]){
				for (var i=0;i<result[key].length;i++){
					if(result[key]==result[key][i]){
						sum += Math.floor(resultPrice[key]);	
					}else{
						sum += Math.floor(resultPrice[key][i]);	
					}
				}
			}
			
			copyOptionTd.children("input[name='price[]']").val(sum);
			$(this).prepend(preTag);				
			$(this).append(copyOptionTd.clone());
			key++;					
		}								
	});
	
	
	$("#optionLayer").html( clone );	
	calulate_option_price();
}

function batch_option_price(){
	var defaultSupplyPrice;
	var defaultConsumerPrice;
	var reserveRate,reserveUnit,reserve,price,stock;
	var tax,supplyRate,discountRate,infomation;
	var socialcpuseopen = $("#socialcpuseopen").val();
	
	$("input[name='defaultOption']").each(function(idx){
		if( $(this).attr("checked") ){
			supplyPrice		= $("input[name='supplyPrice[]']").eq(idx).val();
			if(socialcpuseopen){
				coupon_input	= $("input[name='coupon_input[]']").eq(idx).val();
			}
			coupon_input	= $("input[name='coupon_input[]']").eq(idx).val();
			consumerPrice	= $("input[name='consumerPrice[]']").eq(idx).val();
			price			= $("input[name='price[]']").eq(idx).val();
			reserveRate 	= $("input[name='reserveRate[]']").eq(idx).val();
			reserveUnit 	= $("select[name='reserveUnit[]']").eq(idx).find("option:selected").attr("value");
			reserve			= $("input[name='reserve[]']").eq(idx).val();				
			stock			= $("input[name='stock[]']").eq(idx).val();
			infomation		= $("textarea[name='infomation[]']").eq(idx).val();
			tax 			= $(".tax").eq(idx).html();
			supplyRate 		= $(".supplyRate").eq(idx).html();
			discountRate    = $(".discountRate").eq(idx).html();
		}			
	});
	$("input[name='defaultOption']").each(function(idx){
		$("input[name='supplyPrice[]']").eq(idx).val(supplyPrice);
		if(socialcpuseopen){
			$("input[name='coupon_input[]']").eq(idx).val(coupon_input);
		}
		$("input[name='coupon_input[]']").eq(idx).val(coupon_input);
		$("input[name='consumerPrice[]']").eq(idx).val(consumerPrice);
		$("input[name='price[]']").eq(idx).val(price);
		$("input[name='reserveRate[]']").eq(idx).val(reserveRate);
		$("select[name='reserveUnit[]']").eq(idx).find("option[value='"+reserveUnit+"']").attr("selected",true);			
		$("input[name='reserve[]']").eq(idx).val(reserve);
		$("input[name='stock[]']").eq(idx).val(stock);
		$("textarea[name='infomation[]']").eq(idx).val(infomation);
		$(".tax").eq(idx).html(tax);

		$(".supplyRate").eq(idx).html(supplyRate);
		$(".discountRate").eq(idx).html(discountRate);
	});	
}


function get_default_option(){
	var point_text		= $("#optionLayer .point_text").html();
	var policy			= $("select[name='reserve_policy'] option:selected").val();
	if	(!policy)	policy			= $("input[name='reserve_policy']").val();
	var policy_shop		= '';
	var policy_goods	= '';
	if	(policy == 'goods')	policy_goods	= 'selected';
	else					policy_shop		= 'selected';

	if ( socialcpuse_flag ) {
		var socialcp_input_type = $("input[name='socialcp_input_type']:checked").val();
		var couponinputsubtitle = '';
		if( socialcp_input_type == 'price' ) {
			couponinputsubtitle = '금액';
		}else{
			couponinputsubtitle = '횟수';
		}
	}

	var firstTds = "<table class=\"info-table-style\" style=\"width:100%\">";
	firstTds += "<thead>";
	firstTds += "<tr>";				
	firstTds += "<th class=\"its-th-align center\">매입가</th>";
	firstTds += "<th class=\"its-th-align center\">매입율</th>";
	if ( socialcpuse_flag ) {
		firstTds += "<th class=\"its-th-align cente couponinputtitle\" rowspan=\"2\">쿠폰1장→값어치<br/><span class=\"couponinputsubtitle\">"+couponinputsubtitle+"</span></th>";
	}
	firstTds += "<th class=\"its-th-align center\">정가(소비자가)</th>";
	firstTds += "<th class=\"its-th-align center\">마진 / 할인가(판매가)</th>";
	firstTds += "<th class=\"its-th-align center\">할인율</th>";
	firstTds += "<th class=\"its-th-align center\">부가세</th>";			
	firstTds += "<th class=\"its-th-align center\">재고(가용)</th>";
	firstTds += "<th class=\"its-th-align center\">";
	firstTds += "<div style=\"margin-bottom:5px;\">";
	firstTds += "<select name=\"reserve_policy\">";
	firstTds += "<option value=\"shop\" "+policy_shop+">지급 적립금 통합정책 적용</option>";
	firstTds += "<option value=\"goods\" "+policy_goods+">지급 적립금 개별정책 입력</option>";
	firstTds += "</select>";
	firstTds += "</div>";
	firstTds += "지급 적립금";
	if (point_text) {
		firstTds += "<div style='color:#999999;' class='point_text'>"+point_text+"</div>";
	}
	firstTds += "</th>";	
	firstTds += '<th class="its-th-align center" rowspan="2">';
	firstTds += '옵션설명';
	firstTds += '<span class="helpicon addHelpIcon" title="옵션설명이란 해당 옵션에 대한 안내 문구입니다.<br/>옵션설명이 있는 경우 구매자가 해당 옵션을 선택하면 옵션설명이 보여지게 됩니다."></span>';
	firstTds += '</th>';
	
	firstTds += "</tr>";
	firstTds += "</thead>";
	firstTds += "<tbody>";		
	firstTds += "<tr class=\"optionTr\">";				
	firstTds += "<td class=\"its-td-align center\">";
	firstTds += "<input type=\"hidden\" name=\"optionSeq[]\" value=\"\" />";
	firstTds += "<input type=\"text\" name=\"supplyPrice[]\" class=\"line onlynumber\" style=\"text-align:right\" size=\"10\"  value=\"\"/>";
	firstTds += "</td>";				
	firstTds += "<td class=\"its-td-align right supplyRate\" style=\"padding-right:10px\"></td>";
	
	if ( socialcpuse_flag ) {
		firstTds += "<td class=\"its-td-align center couponinputtitle\">";
		firstTds += "<input type=\"text\" name=\"coupon_input[]\" class=\"line onlynumber\" style=\"text-align:right\" size=\"10\" value=\"\"/>";
		firstTds += "</td>";
	}

	firstTds += "<td class=\"its-td-align center\"><input type=\"text\" name=\"consumerPrice[]\" class=\"line onlynumber\" style=\"text-align:right\" size=\"10\" value=\"0\"/></td>";
	firstTds += "<td class=\"its-td-align center pricetd\">"
	firstTds += "<span class=\"net_profit\">0</span> / ";
	firstTds += "<input type=\"text\" name=\"price[]\" class=\"line onlynumber\" style=\"text-align:right\" size=\"10\" value=\"0\"/>";
	firstTds += "</td>";
	firstTds += "<td class=\"its-td-align right discountRate\" style=\"padding-right:10px\"></td>";
	firstTds += "<td class=\"its-td-align right tax\" style=\"padding-right:10px\"></td>";			
	firstTds += "<td class=\"its-td-align center\">";
	firstTds += "<input type=\"text\" name=\"stock[]\" class=\"line onlynumber\" style=\"text-align:right\" size=\"5\" value=\"0\"/>";
	firstTds += "(<span class=\"optionUsableStock\">0</span>)";
	firstTds += "</td>";
	firstTds += "<td class=\"its-td-align center\">";			
	firstTds += "<input type=\"text\" name=\"reserveRate[]\" class=\"line onlynumber\" style=\"text-align:right\" size=\"3\" value=\"0\" />";
	firstTds += "<select name=\"reserveUnit[]\" class=\"line\">";
	firstTds += "	<option value=\"percent\">%</option>";
	firstTds += "	<option value=\"won\">원</option>";						
	firstTds += "</select>";				
	firstTds += "<input type=\"text\" name=\"reserve[]\" class=\"line onlynumber\" value=\"0\" style=\"text-align:right\" size=\"5\" readonly />";
	firstTds += "</td>";
	firstTds += "<td class=\"its-td-align center\">";	
	firstTds += "<textarea name='infomation[]' style='width:100%' rows=2></textarea>";
	firstTds += "</td>";
	firstTds += "</tr>";
	firstTds += "</tbody>";
	firstTds += "</table>";

	return firstTds;
}
 



function default_suboption_input(){
	var firstTds = '<td class="its-td-align center"><input type="text" name="subSupplyPrice[]" class="line onlynumber" style="text-align:right" size="10" /></td>';				
	firstTds += '<td class="its-td-align right subSupplyRate" style="padding-right:10px"></td>';
	firstTds += '<td class="its-td-align center"><input type="text" name="subConsumerPrice[]" class="line onlynumber" style="text-align:right" size="10" /></td>';
	firstTds += '<td class="its-td-align center"><span class="sub_net_profit"></span> / <input type="text" name="subPrice[]" class="line onlynumber" style="text-align:right" size="10" /></td>';
	firstTds += '<td class="its-td-align right subDiscountRate" style="padding-right:10px"></td>';
	firstTds += '<td class="its-td-align right subTax" style="padding-right:10px"></td>';
	firstTds += '<td class="its-td-align center"><input type="text" name="subStock[]" class="line onlynumber" style="text-align:right" size="5" /></td>';
	firstTds += '<td class="its-td-align center"><input style="text-align: right;" class="line onlynumber input-box-default-text" name="subReserveRate[]" value="0" size="3" type="text"><select class="line" name="subReserveUnit[]"><option value="percent" selected>%</option><option value="won">원</option>	</select><input style="text-align: right;" class="line onlynumber input-box-default-text" name="subReserve[]" value="0" size="5" type="text" readonly /></td>';
	return firstTds;
}

function get_suboption_title(){
	var point_text = $("#optionLayer .point_text").text();
	tagTitle = '<thead>';
	tagTitle += '<tr>';		
	tagTitle += '<th class="its-th-align center" rowspan="2"><span class="btn-minus"><button type="button" id="addSuboptionButton"></button></span></th>';		
	tagTitle += '<th class="its-th-align center" colspan="4">추가옵션</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">매입가</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">매입율</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">정가(소비자가)</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">마진 / 할인가(판매가)</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">할인율</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">부가세</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">재고(가용)</th>';
	tagTitle += '<th class="its-th-align center" rowspan="2">';
	tagTitle += "지급 적립금<div style='color:#999999;' class='point_text'>"+point_text+"</div>";
	tagTitle += '</th>';
	tagTitle += '</tr>';
	tagTitle += '<tr>';
	tagTitle += '<th class="its-th-align center">회원할인 <span class="helpicon" title="체크 → 추가옵션에 대한 회원등급별 추가할인과 추가적립이 적용됩니다."></span></th>';
	tagTitle += '<th class="its-th-align center">필수여부 <span class="helpicon" title="체크 → 구매자가 해당 상품을 구매하기 위해서는 추가 상품을 반드시 선택하도록 합니다."></span></th>';
	tagTitle += '<th class="its-th-align center">옵션명</th>';
	tagTitle += '<th class="its-th-align center">옵션값</th>';
	tagTitle += '</tr>';
	tagTitle += '</thead>';
	
	return tagTitle;
}

function get_suboption_input(optName, optValue, optCode, optType){
	
	var tag = '<tbody>';	
	for (var i=0;i<optValue.length;i++){						
						
			if(!optValue[i]) continue;
		for (var j=0;j < optValue[i].length;j++){	
			var tmpName = $.trim(optName[i]);
			var tmpType = $.trim(optType[i]);
			var tmpCode = $.trim(optCode[i][j]);
			var tmpValue = $.trim(optValue[i][j]);
			if(tmpValue){
				tag += '<tr class="suboptionTr">';
				tag += '<td class="its-td-align center"><span class="btn-minus"><button type="button" class="delSuboptionButton"></button></span></td>';
				if(j == 0){
					tag += '<td class="its-td-align center"><input type="checkbox" size="10" name="subSale[]" value="y" /></td>';
					tag += '<td class="its-td-align center"><input type="checkbox" size="10" name="subRequired[]" value="y" /></td>';
					//tag += '<td class="its-td-align center"><input type="text" size="10" name="suboptTitle[]" value="' + tmpName + '" />';
					if( tmpType == 'direct' || !tmpType ) {
						tag += '<td class="its-td-align center"><input type="text" size="10" name="suboptTitle[]" value="' + tmpName + '"  class="line " />';
					}else{
						tag += '<td class="its-td-align center"><input type="text" size="10" name="suboptTitle[]" value="' + tmpName + '"  class="line input-box-default-text-code " />';
					}
					tag += '<input type="hidden" size="10" name="suboptType[]" value="' + tmpType + '" /></td>';
				}else{
					tag += '<td class="its-td-align center"></td>';
					tag += '<td class="its-td-align center"></td>';
					tag += '<td class="its-td-align center"></td>';
				}

				//tag += '<td class="its-td-align center"><input type="text" size="10" name="subopt[' + i + '][]" value="' + tmpValue + '" />';
				if( tmpType == 'direct' || !tmpType ) {
					tag += '<td class="its-td-align center"><input type="text" size="10" name="subopt[' + i + '][]" value="' + tmpValue + '"  class="line " />';
				}else{
					tag += '<td class="its-td-align center"><input type="text" size="10" name="subopt[' + i + '][]" value="' + tmpValue + '"  class="line input-box-default-text-code " />';
				}
				tag += '<input type="hidden" size="10" name="suboptCode[' + i + '][]" value="' + tmpCode + '" /></td></tr>';
			}
		}			
	}
	tag += '</tbody>';
	return tag;	
}

function get_suboption_price(optPrice){
	var k = 0;
	var subPrice = new Array();
	for (var i=0;i<optPrice.length;i++){						
		for (var j=0;j < optPrice[i].length;j++){		
			subPrice[k] = optPrice[i][j];
			k++;
		}			
	}
	return subPrice;
}

function make_suboption(){
	var optValue = new Array();
	var optPrice = new Array();
	var optName = new Array();
	var subPrice = new Array();
	
	var optCode = new Array();
	var optType = new Array();

	var copyOptionTd, tmp, tag;			
	var makeNameObj = $("input[name='suboptionMakeName[]']");
	var makeValueObj = $("input[name='suboptionMakeValue[]']");
	var makePriceObj = $("input[name='suboptionMakePrice[]']");	
	var makeCodeObj = $("input[name='suboptionMakeCode[]']");
	var makeTypeObj = $("input[name='suboptionMakeType[]']");	
	$("#suboptionLayer").html("<table class='info-table-style' width='100%'></table>");
	var clone = $("div#suboptionLayer table").clone();
	
	var tagTitle = get_suboption_title();
	var firstTdsTag = default_suboption_input();		
	
	var nameArr = new Array();
	makeNameObj.each(function(idx){
		if(!$.inArray($(this).val(),nameArr)){
			alert("옵션명이 중복되었습니다.");
			return;
		}
		nameArr[idx] = $(this).val();	 
		optName[idx] = $(this).val();	
		tmp = makeValueObj.eq(idx).val();
		optValue[idx] = tmp.split(',');
		tmp = makePriceObj.eq(idx).val();
		optPrice[idx] = tmp.split(',');			
		
		tmp = makeCodeObj.eq(idx).val();
		optCode[idx] = tmp.split(',');		
		tmp = makeTypeObj.eq(idx).val();
		optType[idx] = tmp.split(',');	

		cols = idx;
	});

	for (var i=0;i < optValue.length;i++){	
			if(!optValue[i]) continue;
		for (var j=0;j < optValue[i].length;j++){
			if( ! optPrice[i] ) optPrice[i] = new Array();
			if( ! optPrice[i][j] ){					
				optPrice[i][j] = 0;
			}
		}
	}
	
	subPrice = get_suboption_price(optPrice);
	tag = get_suboption_input(optName, optValue, optCode, optType);
	clone.children("thead").remove();
	clone.children("tbody").remove();
	
	clone.html( tagTitle );
	clone.append( tag );	
			
	clone.find("tbody tr").each(function(idx){			
		$(this).append( firstTdsTag );
	});
	
	clone.find("input[name='subRequired[]']").each(function(idx){
		   $(this).val(idx);     
	});

	clone.find("input[name='subPrice[]']").each(function(idx){
		$(this).val(subPrice[idx]);	
	});
		
	$("#suboptionLayer").html( clone );	
	calulate_subOption_price();	
}

/* 추가옵션가격 일괄 적용 */
function batch_suboption_price(){
	var n = 0;
	var supplyPrice,consumerPrice,price;
	var subReserveRate,subReserveUnit,subReserve,subStock;
	$("div#suboptionLayer input[name='suboptTitle[]']").each(function(i){		
		$("div#suboptionLayer input[name='subopt["+i+"][]']").each(function(j){
			if(j == 0){
				supplyPrice		= $("input[name='subSupplyPrice[]']").eq(n).val();
				consumerPrice	= $("input[name='subConsumerPrice[]']").eq(n).val();
				price			= $("input[name='subPrice[]']").eq(n).val();
				subStock			= $("input[name='subStock[]']").eq(n).val(); 

				subReserveRate 	= $("input[name='subReserveRate[]']").eq(n).val();
				subReserveUnit 	= $("select[name='subReserveUnit[]']").eq(n).find("option:selected").attr("value");
				subReserve			= $("input[name='subReserve[]']").eq(n).val();
			}else{
				$("input[name='subSupplyPrice[]']").eq(n).val(supplyPrice);
				$("input[name='subConsumerPrice[]']").eq(n).val(consumerPrice);
				$("input[name='subPrice[]']").eq(n).val(price);

				$("input[name='subStock[]']").eq(n).val(subStock); 

				$("input[name='subReserveRate[]']").eq(n).val(subReserveRate);
				$("select[name='subReserveUnit[]']").eq(n).find("option[value='"+subReserveUnit+"']").attr("selected",true);			
				$("input[name='subReserve[]']").eq(n).val(subReserve);
			}
			n++;
		});
	});
}

/* 추가입력 폼 입력타입 체크 */
function check_memberInputMakeForm(idx){
	var inputlimit;
	var text	= "";
	var img		= "<input type=\"hidden\" name=\"memberInputMakeLimit[]\" value=\"file\" /> 2M이하";
	$("select[name='memberInputMakeForm[]'] option:selected").each( function(n){
		if( $(this).val() == 'file'){
			$(this).parent().parent().next().children("div.textLimit").hide();
			$(this).parent().parent().next().children("div.uploadLimit").show();
			$(this).parent().parent().next().children("div.textLimit").html("");
			$(this).parent().parent().next().children("div.uploadLimit").html(img);
		}else{			
			inputlimit		= $("input[name='memberInputMakeLimit[]']").eq(n).val();
			if(inputlimit>0){
				text	= "<input type=\"text\" name=\"memberInputMakeLimit[]\" class=\"line\" size=\"2\" value=\""+inputlimit+"\" />자 이내";
			}else{
				text = "<input type=\"text\" name=\"memberInputMakeLimit[]\" class=\"line\" size=\"2\" value=\"0\" />자 이내";
			}
			$(this).parent().parent().next().children("div.textLimit").show();
			$(this).parent().parent().next().children("div.uploadLimit").hide();
			$(this).parent().parent().next().children("div.textLimit").html(text);
			$(this).parent().parent().next().children("div.uploadLimit").html("");
		}
	});
}

/* 추가입력 폼 생성 */
function get_memberInput_title(){
	var tag = '';	
	tag += '<table  class="simplelist-table-style" style="width:100%">';
	tag += '<thead>';
	tag += '<tr>';	
	tag += '	<th class="its-th-align center"></th>';	
	tag += '	<th class="its-th-align center">추가 입력명</th>';
	tag += '	<th class="its-th-align center">추가입력 값</th>';	
	tag += '	<th class="its-th-align center">필수</th>';	
	tag += '</tr>';
	tag += '</thead>';
	tag += '<tbody>';
	tag += '</tbody>';
	tag += '</table>';
	return tag;	
}

/* 추가입력 폼 내용 생성 */
function get_memberInput(){
	var tag = '';
	var iName,iForm,iRequire,iRequireView;
	
	$("div#memberInputDialog input[name='memberInputMakeName[]']").each(function(i){
		iName = $(this).val();
		iForm = $("select[name='memberInputMakeForm[]']").eq(i).children("option:selected").val();
		iLimit = $("input[name='memberInputMakeLimit[]']").eq(i).val();

		tag += '<tr>';
		tag += '<td class="its-td-align center">';
		tag += '<span class="btn-minus"><button type="button" class="delMemberInput"></button></span>';	
		tag += '</td>';
		tag += '<td class="its-td-align center">';
		tag += iName;
		tag += '</td>';
		tag += '<td class="its-td">';
		if(iForm == 'text'){
			tag += '텍스트박스('+iLimit+'자 이내)';
		}else if(iForm == 'edit'){
			tag += '에디트박스('+iLimit+'자 이내)';
		}else if(iForm == 'file'){
			tag += '이미지 업로드 (2M이하)';				
		}		
		tag += '</td>';
		tag += '<td class="its-td-align center">';		
		tag += '<input type="hidden" name="memberInputName[]" value="'+ iName +'">';
		tag += '<input type="hidden" name="memberInputForm[]" value="'+ iForm +'">';
		tag += '<input type="hidden" name="memberInputLimit[]" value="'+ iLimit +'">';
		tag += '<label><input type="checkbox" name="memberInputRequire['+i+']" value="require"> 필수</label>';		
		tag += '</td>';
		
		tag += '</tr>';
	});
	return tag;	
}

function set_goods_icon(){	
	$.getJSON('icon', function(data) {
		var tag = '';
		var width_sum = 0;
		$("div#goodsIconPopup ul li").remove();
		for(var i=0;i<data.length;i++){
			width_sum += data[i].width+20;
			if (width_sum >= 450) {
				tag += '</ul><ul>';
				width_sum = 0;
			}
			tag += '<li style="float:left;padding:5px 10px 5px 10px;'+data[i].li_css+'">';
			tag += '<input type="hidden" name="goodsIconCode[]" value="'+data[i].codecd+'">';
			tag += '<img src="/data/icon/goods/'+data[i].codecd+'.gif" border="0" class="hand icon">';
			tag += '</li>';
		}
		$("div#goodsIconPopup ul").html(tag);
	});
}

/* 가격 대체 문구 */
function show_stringPrice(){
	var obj = $("input[name='stringPriceUse']");
	if( obj.attr("checked") ){			
		obj.parent().parent().find("span").show();
	}else{
		obj.parent().parent().find("span").hide();
	}
}

/* 복수구매 할인 */
function show_multiDiscountUse(){
	var obj = $("input[name='multiDiscountUse']"); 
	if( obj.attr("checked") ){			
		obj.parent().parent().find("span").show();
	}else{
		obj.parent().parent().find("span").hide();
	}
}

/* 최소구매수량 */
function show_minPurchaseLimit(){
	var obj = $("input[name='minPurchaseLimit']:checked");	
	if( obj.val() == 'limit' ){			
		obj.parent().parent().find("span").show();
	}else{
		obj.parent().parent().find("span").hide();
	}
}

/* 최대 구매수량 */
function show_maxPurchaseLimit(){
	var obj = $("input[name='maxPurchaseLimit']:checked");	
	if( obj.val() == 'limit' ){			
		obj.parent().parent().find("span").show();
	}else{
		obj.parent().parent().find("span").hide();
	}
}

/* 필수옵션 사용 */
function show_optionUse(){
	var obj = $("input[name='optionUse']"); 
	if( obj.is(':checked') ){
		obj.parent().parent().find("span").show();
		$("form div#optionRegistLayer").show();
	}else{
		//$("form div#optionLayer").html(defaultOption);
		var default_option = get_default_option();
		$("#optionLayer").html(default_option);
		obj.parent().parent().find("span").hide();
		$("form div#optionRegistLayer").hide();
		$("select[name='reserve_policy']").change(function(){reserve_policy();});
		reMakeHelpIcon();
	}
	calulate_option_price();
}

/* 추가옵션 사용 */
function show_subOptionUse(){
	var obj = $("input[name='subOptionUse']");
	if( obj.is(':checked') ){
		obj.parent().parent().find("span").show();
		$("#suboptionIndividualSettingLayer").show();
	}else{
		$("#suboptionLayer").html("");
		obj.parent().parent().find("span").hide();
		$("#suboptionIndividualSettingLayer").hide();
	}
	calulate_subOption_price();
}

/* 구매자 추가입력 */
function show_memberInputUse(){
	var obj = $("input[name='memberInputUse']");
	if( obj.is(':checked') ){
		obj.parent().parent().find("span").show();
	}else{
		$("#memberInputLayer").html("");
		obj.parent().parent().find("span").hide();
	}
}

/* 다량옵션오류방지를 위하여 인코딩된 옵션폼값을 인코딩 : disable 처리 */
function encodeFormValue(containerSelector){
	var container = $(containerSelector);
	var selector = "input[type='text'],input[type='hidden'],input[type='radio'],input[type='checkbox'],select,textarea";
	var data = new Array();
	$(selector,container).each(function(){
		var name = $(this).attr("name");
		var type = $(this).attr("type");
		
		if(name=='encodedFormValue') return;
		if($(this).is(":disabled")) return;
		if((type=='radio' || type=='checkbox') && !$(this).is(":checked")) return;
		
		data.push($(this).attr('name') + "=" + encodeURIComponent($(this).val()));
	});
	
	$(selector,container).each(function(){
		var name = $(this).attr("name");
		if(name=='encodedFormValue') return;
		var oriDisabled = $(this).is(":disabled")?true:false; 
		$(this).data("oriDisabled",oriDisabled).attr("disabled",true);
	});
	
	$("textarea[name='encodedFormValue']").val(data);
}

/* 다량옵션오류방지를 위하여 인코딩된 옵션폼값을 인코딩 : disable 해제 */
function encodeFormValueOff(containerSelector){
	var container = $(containerSelector);
	var selector = "input[type='text'],input[type='hidden'],input[type='radio'],input[type='checkbox'],select,textarea";
	$(selector,container).each(function(){
		var name = $(this).attr("name");
		if(name=='encodedFormValue') return;
		var oriDisabled = $(this).data("oriDisabled")?true:false; 
		$(this).attr("disabled",oriDisabled);
	});
	
	$("textarea[name='encodedFormValue']").val('');
}

//새창 > 상품사진 멀티일괄/일괄 등록시 
function save_image_config(){
	var saveinfo = {'largeImageWidth':$('#largeImageWidth').val(), 'largeImageHeight':$('#largeImageHeight').val(),
	'viewImageWidth':$('#viewImageWidth').val(), 'viewImageHeight':$('#viewImageHeight').val(),
	'list1ImageWidth':$('#list1ImageWidth').val(), 'list1ImageHeight':$('#list1ImageHeight').val(),
	'list2ImageWidth':$('#list2ImageWidth').val(), 'list2ImageHeight':$('#list2ImageHeight').val(),
	'thumbViewWidth':$('#thumbViewWidth').val(), 'thumbViewHeight':$('#thumbViewHeight').val(),
	'thumbCartWidth':$('#thumbCartWidth').val(), 'thumbCartHeight':$('#thumbCartHeight').val(),
	'thumbScrollWidth':$('#thumbScrollWidth').val(), 'thumbScrollHeight':$('#thumbScrollHeight').val()};

	$.ajax({
		'type': "POST",
		'url': "../goods_process/save_image_config",
		'data': saveinfo,
		'dataType' : 'json',
		'success': function(result){ 
			if( result.result ) { 
				$("#largeImageWidth",window.opener.document).val(result.largeImageWidth);
				$("#largeImageHeight",window.opener.document).val(result.largeImageHeight);

				$("#viewImageWidth",window.opener.document).val(result.viewImageWidth);
				$("#viewImageHeight",window.opener.document).val(result.viewImageHeight);

				$("#list1ImageWidth",window.opener.document).val(result.list1ImageWidth);
				$("#list1ImageHeight",window.opener.document).val(result.list1ImageHeight);

				$("#list2ImageWidth",window.opener.document).val(result.list2ImageWidth);
				$("#list2ImageHeight",window.opener.document).val(result.list2ImageHeight);

				$("#thumbViewWidth",window.opener.document).val(result.thumbViewWidth);
				$("#thumbViewHeight",window.opener.document).val(result.thumbViewHeight);

				$("#thumbCartWidth",window.opener.document).val(result.thumbCartWidth);
				$("#thumbCartHeight",window.opener.document).val(result.thumbCartHeight);

				$("#thumbScrollWidth",window.opener.document).val(result.thumbScrollWidth);
				$("#thumbScrollHeight",window.opener.document).val(result.thumbScrollHeight);

				
				$(".largeImageWidth",window.opener.document).text(result.largeImageWidth);
				$(".largeImageHeight",window.opener.document).text(result.largeImageHeight);

				$(".viewImageWidth",window.opener.document).text(result.viewImageWidth);
				$(".viewImageHeight",window.opener.document).text(result.viewImageHeight);

				$(".list1ImageWidth",window.opener.document).text(result.list1ImageWidth);
				$(".list1ImageHeight",window.opener.document).text(result.list1ImageHeight);

				$(".list2ImageWidth",window.opener.document).text(result.list2ImageWidth);
				$(".list2ImageHeight",window.opener.document).text(result.list2ImageHeight);

				$(".thumbViewWidth",window.opener.document).text(result.thumbViewWidth);
				$(".thumbViewHeight",window.opener.document).text(result.thumbViewHeight);

				$(".thumbCartWidth",window.opener.document).text(result.thumbCartWidth);
				$(".thumbCartHeight",window.opener.document).text(result.thumbCartHeight);

				$(".thumbScrollWidth",window.opener.document).text(result.thumbScrollWidth);
				$(".thumbScrollHeight",window.opener.document).text(result.thumbScrollHeight);

				$(".save_image_input").attr("disabled","disabled").attr("readonly","readonly");
				$("button.save_image_config").parent().addClass("gray").removeClass("cyanblue");
				$("#save_image_config_ck").removeAttr("checked")
				openDialogAlert(result.msg,400,150);
			}else{
				openDialogAlert(result.msg,400,150);
			}
		}
	}); 
}