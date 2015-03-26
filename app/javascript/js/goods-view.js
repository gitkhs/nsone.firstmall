function getCouponDownlayerclose(){
	$('#couponDownloadDialog').dialog('close');
}

function set_option_join(){
	if(typeof gl_options_join != 'undefined'){
		set_option_join_(gl_options_join);
	}else{
		var gdata = "no="+gl_goods_seq;

		$.ajax({
			type: "get",
			url: "../goods/option_join",
			data: gdata,
			success: function(result){
				set_option_join_(result);
			}
		});
	}
}

function set_option_join_(result){
	var opt = '';
	var data = eval(result);
	$("select[name='viewOptions[]']").find("option").each(function(i){
		if(i>0)$(this).remove();
	});
	var add_price = 0;
	var Optionsspecialhtml = '';
	for(var i=0;i<data.length;i++){
		var obj = data[i];
		add_price = obj.price - gl_goods_price;
		if(obj.event){
			if(obj.event.target_sale) {
				add_price = 0;
			}
		}

		opt = obj.option1;
		if(obj.option2) opt += '/'+obj.option2;
		if(obj.option3) opt += '/'+obj.option3;
		if(obj.option4) opt += '/'+obj.option4;
		if(obj.option5) opt += '/'+obj.option5;

		if( obj.chk_stock ){

			if(obj.color) {
				if( obj.ismobile ){//mobile check
					var whitecolorborder = " style='display:inline-block;width:30px; height:30px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
					Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span  name='viewOptionsspecialbtn' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+" hide '  style='width:30px; height:30px; margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+opt+"\" optvalue=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\"  eqindex='0' ><font  "+whitecolorborder+" >■</font></span></span>";
				}else{
					var whitecolorborder = " style='display:inline-block;width:18px; height:18px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
					Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span  name='viewOptionsspecialbtn' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+" hide '  style='width:30px; height:30px; margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+opt+"\"  optvalue=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\"  eqindex='0' ><font  "+whitecolorborder+" >■</font></span></span>";
				}
			}

			if( add_price == 0){
				$("select[name='viewOptions[]']").append("<option value=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\">"+opt+"</option>");
			}else if(add_price > 0) {
				$("select[name='viewOptions[]']").append("<option value=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\" >"+opt+" (+"+ comma(add_price)+")</option>");
			}else if(add_price < 0){
				$("select[name='viewOptions[]']").append("<option value=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\">"+opt+" (-"+ comma(add_price*-1)+")</option>");
			}
		}else{
			$("select[name='viewOptions[]']").append("<option value=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" disabled=\"disabled\">"+opt+" 품절</option>");
		}
	}
	if( Optionsspecialhtml ) {
		$(".viewOptionsspecialays").html(Optionsspecialhtml);
	}else{
		$(".viewOptionsspecialays").html('');
	}

	// 2014-12-18 옵션 개편 후 (ocw)
	try{
		$("select[name='viewOptions[]']").each(function(){
			var sb = $(this).attr('sb');
			$(this).removeData('selectbox').show();
			$("#sbHolder_"+sb).remove();
			$(this).selectbox();

			if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
				$(this).selectbox("disable");
			}
		});
	}catch(e){};
}

function set_option(n){	
	var st = 'viewOptions[]';

	if(n==0 && typeof gl_option_n0 != 'undefined'){
		set_option_(n,st,gl_option_n0);
	}else{
		var gdata = "no="+gl_goods_seq;
		$("select[name='"+st+"']").each(function(i){
			if(i < n){
				gdata += '&max='+gl_option_divide_title_count+'&options[]='+encodeURIComponent($(this).val());
			}
		});

		$.ajax({
			type: "get",
			url: "../goods/option",
			data: gdata,
			success: function(result){
				set_option_(n,st,result);
			}
		});
	}
}

function set_option_(n,st,result){	
	var data = eval(result);
	$("select[name='"+st+"']").eq(n).find("option").each(function(i){
		if(i!=0)$(this).remove();
	});
	var add_price = 0;
	var Optionsspecialhtml = '';
	
	for(var i=0;i<data.length;i++){
		var obj = data[i];
		add_price = obj.price - gl_goods_price;
		if(obj.event){
			if(obj.event.target_sale) {
				add_price = 0;
			}
		}
		
		if( obj.chk_stock ){
			
			if( obj.color && $("select[name='"+st+"']").eq(n).attr("opttype") == 'color'  ) {
				if( obj.ismobile ){//mobile check
					var whitecolorborder = " style='display:inline-block;width:30px; height:30px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
					Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span name='viewOptionsspecialbtn' opspecialtype='color' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+"'  style='width:30px;margin-top:10px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+obj.opt+"\" optvalue=\""+obj.opt+"\"  ='"+obj.price+"' infomation='"+obj.infomation+"' eqindex='"+n+"' opspecial_location='"+obj.opspecial_location.color+"' ><font  "+whitecolorborder+" >■</font></span></span>";
				}else{
					var whitecolorborder = " style='display:inline-block;width:18px; height:18px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
					Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span name='viewOptionsspecialbtn' opspecialtype='color' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+"'  style='width:30px; height:30px; margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+obj.opt+"\"  optvalue=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"' eqindex='"+n+"' opspecial_location='"+obj.opspecial_location.color+"' ><font "+whitecolorborder+" >■</font></span></span>";
				}
			}
			if( gl_option_divide_title_count - n == 1 ){
				if( add_price == 0){
					$("select[name='"+st+"']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"'  >"+obj.opt+"</option>");
				}else if(add_price > 0) {
					$("select[name='"+st+"']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"'  >"+obj.opt+" (+"+ comma(add_price)+")</option>");
				}else if(add_price < 0){
					$("select[name='"+st+"']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"'  >"+obj.opt+" (-"+ comma(add_price*-1)+")</option>");
				}
			}else{
				$("select[name='"+st+"']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' >"+obj.opt+"</option>");
			}
		}else{
			$("select[name='"+st+"']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' disabled='disabled' >"+obj.opt+" 품절</option>");
		}

		// 컬러 옵션 클릭시 상품상세컷 연결 2014-05-13 leewh
		if (obj.color) {
			var colorIdx = parseInt(i+1);
			$("select[name='"+st+"'] option:eq("+colorIdx+")").eq(n).attr("color", obj.color);
		}
		// 컬러 옵션 end
	}

	//색상노출
	if( $("select[name='"+st+"']").eq(n).attr("opttype") == 'color' ) {
		if( n == 0 ){//색상이 첫번째
			$(".viewOptionsspecialays.color").html(Optionsspecialhtml);
		}else if( $("select[name='"+st+"']").length > 1 && n > 0 ) {//두번째부터 다섯번째
			if( $("select[name='"+st+"']").eq((n-1)).find("option:selected").val() ) {
				$(".viewOptionsspecialays.color").html(Optionsspecialhtml);
			}else{
				$(".viewOptionsspecialays.color").html('');
			}
		}
	}

	// 2014-12-18 옵션 개편 후 (ocw)
	try{
		$("select[name='"+st+"']").each(function(){
			var sb = $(this).attr('sb');
			$(this).removeData('selectbox').show();
			$("#sbHolder_"+sb).remove();
			$(this).selectbox();

			if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
				$(this).selectbox("disable");
			}
		});
	}catch(e){};
}

function set_option_ReStock(n,isMobile){
	var gdata = "no="+gl_goods_seq;
	var st = "viewOptionsReStock[]";

	temp = isMobile ? $('#restock').find($("select[name='"+st+"']")) : $("select[name='"+st+"']");

	$(temp).each(function(i){
		if(i < n) gdata += '&max='+gl_option_divide_title_count+'&options[]='+encodeURIComponent($(this).val());
	});
	
	$.ajax({
		type: "get",
		url: "../goods/option",
		data: gdata,
		success: function(result){
			var data = eval(result);
			temp = isMobile ? $('#restock').find($("select[name='"+st+"']")) : $("select[name='"+st+"']");
			$(temp).eq(n).bind("change",function(){
				$(temp).each(function(i){
					if(i>n){
						$(temp).eq(i).find("option").each(function(n){
							if(n!=0)$(this).remove();
						});
					}
				});
			});
			for(var i=0;i<data.length;i++){
				var obj = data[i];
				$(temp).eq(n).append("<option value=\""+obj.opt+"\">"+obj.opt+"</option>");
			}
		}
	});
}

function cutting_sale_price(price){
	if(gl_cutting_sale_price > 0){
		price = Math.floor(price / gl_cutting_sale_price) * gl_cutting_sale_price;
		if(gl_cutting_sale_action == 'rounding'){
			price = Math.round(price / gl_cutting_sale_price) * gl_cutting_sale_price;
		}
		if(gl_cutting_sale_action == 'ascending'){
			price = Math.ceil(price / gl_cutting_sale_price) * gl_cutting_sale_price;
		}
	}
	return price;
}

function get_multi_sale_price(ea,price){
	if(!gl_multi_discount_use
		||!gl_multi_discount_ea
		||!gl_multi_discount
		||!gl_multi_discount_unit) return price;
	if(ea < gl_multi_discount_ea) return price;

	if( gl_multi_discount_unit == 'percent' && gl_multi_discount < 100 ){
		price -= cutting_sale_price( Math.floor(price * gl_multi_discount / 100) );
	}else if(price > gl_multi_discount ) {
		price -= cutting_sale_price(gl_multi_discount);
	}

	return price;
}

function calculate_goods_price(){
	var ea = 0;
	var tot = 0;
	var price = 0;
	var tot_ea = 0;
	var goods_price  = gl_goods_price;


	$(".optionPrice").each(function(){
		tot_ea += parseInt($(this).closest('tr').find("input[name='optionEa[]'],input[name='suboptionEa[]']").val());
	});
	$(".optionPrice").each(function(idx){
		ea = parseInt($(this).closest('tr').find("input[name='optionEa[]'],input[name='suboptionEa[]']").val());
		price = $(this).html();
		//price = price - gl_event_sale_unit;
		price = parseInt(get_multi_sale_price(tot_ea,price));
		if(price < 0) price = 0;
		$(".out_option_price").eq(idx).html(comma(price * ea));
		tot += price * ea;
	});

	$(".suboptionPrice").each(function(i){
		ea = parseInt($(this).closest('tr').find("input[name='optionEa[]'],input[name='suboptionEa[]']").val());
		price = $(this).html();
		if(price < 0) price = 0;
		$(".out_suboption_price").eq(i).html(comma(price * ea));
		tot += price * ea;
	});

	$("#total_goods_price").html(comma(tot));

	check_option_ea();
	check_suboption_ea();
}

function check_option_ea(){

	$("input[name='optionEa[]']").each(function(idx){
		var gdata = "no="+gl_goods_seq;
		if($("input[name='option[0][]']").eq(idx).val()) gdata += "&option1=" + encodeURIComponent( $("input[name='option[0][]']").eq(idx).val() );
		if($("input[name='option[1][]']").eq(idx).val()) gdata += "&option2=" + encodeURIComponent( $("input[name='option[1][]']").eq(idx).val() );
		if($("input[name='option[2][]']").eq(idx).val()) gdata += "&option3=" + encodeURIComponent( $("input[name='option[2][]']").eq(idx).val() );
		if($("input[name='option[3][]']").eq(idx).val()) gdata += "&option4=" + encodeURIComponent( $("input[name='option[3][]']").eq(idx).val() );
		if($("input[name='option[4][]']").eq(idx).val()) gdata += "&option5=" + encodeURIComponent( $("input[name='option[4][]']").eq(idx).val() );
		var obj_ea = $(this);

		$.ajax({
			type: "get",
			url: "../goods/option_stock",
			data: gdata,
			success: function(result){
				var data_obj = eval("("+result+")");
				if(data_obj.runout != 'unlimited'){
					if(data_obj.able_stock < obj_ea.val()){
						if(data_obj.able_stock < 1){
							obj_ea.parent().parent().parent().parent().parent().remove();
							alert('선택하신 상품의 재고가 없습니다.');
						}else{
							obj_ea.val(data_obj.able_stock);
							alert('선택하신 상품의 재고는 ' + data_obj.able_stock + '개 입니다.');
						}
					}

				}
			}
		});
	});

}

function check_suboption_ea(){
	$("input[name='suboptionEa[]']").each(function(idx){
		var gdata = "no="+gl_goods_seq;
		if( $("input[name='suboption[]']").eq(idx).val() ) gdata += "&option=" + encodeURIComponent( $("input[name='suboption[]']").eq(idx).val() );
		if( $("input[name='suboptionTitle[]']").eq(idx).val() ) gdata += "&title=" + encodeURIComponent( $("input[name='suboptionTitle[]']").eq(idx).val() );
		var obj_ea = $(this);
		$.ajax({
			type: "get",
			url: "../goods/suboption_stock",
			data: gdata,
			success: function(result){
				var data_obj = eval("("+result+")");
				if(data_obj.runout != 'unlimited'){
					if(data_obj.able_stock < obj_ea.val()){
						if(data_obj.able_stock < 1){
							obj_ea.parent().parent().parent().parent().parent().parent().remove();
							alert('선택하신 상품의 재고가 없습니다.');
						}else{
							obj_ea.val(data_obj.able_stock);
							alert('선택하신 상품의 재고는 ' + data_obj.able_stock + '개 입니다.');
						}
					}

				}
			}
		});
	});
}


function check_purchase_ea(){

	var item_ea_sum = 0;
	$("input[name='optionEa[]']").each(function(){
		item_ea_sum += num($(this).val());
	});
	$("input[name='suboptionEa[]']").each(function(){
		item_ea_sum += num($(this).val());
	});

	if(gl_min_purchase_limit=='limit' && gl_min_purchase_ea ){
		if(item_ea_sum < gl_min_purchase_ea){
			openDialogAlert($(".goods_name").text() + " 상품의 최소 구매수량은 "+comma(gl_min_purchase_ea)+"개 입니다.",400,140,'');
			return false;
		}
	}
	if(gl_max_purchase_limit=='limit' && gl_max_purchase_ea ){
		if(item_ea_sum>gl_max_purchase_ea){
			openDialogAlert($(".goods_name").text() + " 상품의 최대 구매수량은 "+comma(gl_max_purchase_ea)+"개 입니다.",400,140,'');
			return false;
		}
	}

	return true;
}

function input_limits(obj, limit){
	var value	= obj.value;
	var len		= value.length;
	if(limit>0 && limit){
		if(limit < len){
			alert(limit+"자내로 작성해주세요.");
			obj.value = value.substring(0,limit);
			return;
		}
	}
}
function check_option(){

	var len = $(".optionPrice").length;
	if ( $("select[name='viewOptions[]']").length > 1
		&& $("select[name='viewOptions[]']").first().find("option:selected").val()
		&& !$("select[name='viewOptions[]']").last().find("option:selected").val()
	){
		openDialogAlert("옵션을 선택해 주세요.",400,140,'');
		return false;
	}

	if( $("select[name='viewOptions[]']").length > 0 && len < 1 ){
		openDialogAlert("옵션을 선택해 주세요.",400,140,'');
		return false;
	}

	var subOptionOk = true;
	$("select[name='viewSuboption[]'][required='y']").each(function(){
		var subOptionTitle = $(this).closest('tr').children(".suboptionTitle").text();
		if(subOptionOk && !$("input[name='suboptionTitle[]'][value='"+subOptionTitle+"']").length){
			openDialogAlert(subOptionTitle + " 옵션을 선택해 주세요.",400,140,'');
			subOptionOk = false;
		}

	});
	if(!subOptionOk) return false;

	if($("select[name='viewOptions[]']").length==0 && $("input[name='viewInputs[]'],textarea[name='viewInputs[]']").length){
		
		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			if(mobile_inputs_upload()) return false;
		}

		var msg = '';
		$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(i){
			var inputsTitle = $(".inputsTitle").eq(i).html();
			msg += "<input type='hidden' name='inputsValue["+i+"][]' value='"+$(this).val()+"'>";
			msg += "<input type='hidden' name='inputsTitle["+i+"][]' value='"+inputsTitle+"'>";
		});
		$("table.goods_quantity_table input[name*='inputsValue'],table.goods_quantity_table input[name*='inputsTitle']").remove();
		$("table.goods_quantity_table").append(msg);
	}

	if(!check_purchase_ea()) return false;

	return true;
}

//좋아요박스 op 자체작업
function setFacebooklikeopsave(no){
	var datahost = $(".fblikeopengrapybtn").attr("data-host");
	
	if(typeof datahost!="undefined" && datahost){
		var url = (document.location.protocol == "https:")?'https://'+datahost:'http://'+datahost;
		url = url+'/snsredirect/setFacebooklikeopsave?no='+no;
		actionFrame.location.href = url;
	}
}
/* 파일업로드버튼(Uploadify) 적용 */
function setUserUploadifyButton(uplodifyButtonId, setting){
	//한글도메인체크@2013-03-12
	var fdomain = document.domain;
	var kordomainck = false;
	for(i=0; i<fdomain.length; i++){
	 if (((fdomain.charCodeAt(i) > 0x3130 && fdomain.charCodeAt(i) < 0x318F) || (fdomain.charCodeAt(i) >= 0xAC00 && fdomain.charCodeAt(i) <= 0xD7A3)))
	{
		kordomainck = true;
		break;
	}
	}
	if( !kordomainck ){
	krdomain = '';
	}

	var defaultSetting = {
		'script'			: krdomain+'/common/upload_file',
	    'uploader'			: '/app/javascript/plugin/jquploadify/uploadify.swf',
	    'buttonImg'			: '/app/javascript/plugin/jquploadify/uploadify-search.gif',
	    'cancelImg'			: '/app/javascript/plugin/jquploadify/uploadify-cancel.png',
	    'fileTypeExts'		: '*.jpg;*.gif;*.png;*.jpeg',
	    'fileTypeDesc'		: 'Image Files (.JPG, .GIF, .PNG)',
	    'removeCompleted'	: true,
		'width'				: 64,
		'height'			: 20,
	    'auto'				: true,
	    'multi'				: false,
	    'scriptData'		: {'randomFilename':1},
	    'completeMsg'		: '적용 가능',
		'onCheck'     : function(event,data,key) {
			$("#"+uplodifyButtonId+key).find(".percentage").html("<font color='red'> - 파일명 중복</font>");
	    },
	    'onComplete'		: function (event, ID, fileObj, response, data) {
	    	var result = eval(response)[0];
	    	
			if(result.status!=1){
				openDialogAlert(result.msg,400,150);
				$("#"+uplodifyButtonId+ID).find(".percentage").html("<font color='red'> - "+result.desc+"</font>");
				return false;
			}else{
				var webftpFormItemObj = $("#"+uplodifyButtonId+ID).closest(".webftpFormItem");
				webftpFormItemObj.find(".webftpFormItemInput").val(result.filePath);
				webftpFormItemObj.find(".webftpFormItemInputOriName").val(result.fileInfo.client_name);
				webftpFormItemObj.find(".webftpFormItemPreview").attr('src',krdomain+'/'+result.filePath).show()
					.attr("onclick","window.open('"+krdomain+'/'+result.filePath+"')").css('cursor','pointer');
				if(webftpFormItemObj.find(".webftpFormItemInput").length){
					webftpFormItemObj.find(".webftpFormItemInput").trigger('change');
				}
				if(webftpFormItemObj.find(".webftpFormItemInput").closest('form').length){
					webftpFormItemObj.find(".webftpFormItemInput").closest('form').trigger('change');
				}
				if(webftpFormItemObj.find(".webftpFormItemPreviewSize").length){
					webftpFormItemObj.find(".webftpFormItemPreviewSize").html(result.fileInfo.image_width + " x " + result.fileInfo.image_height);
				}
				webftpFormItemObj.find('object').css('vertical-align','middle');
			}
		},
		'onError'			: function (event,ID,fileObj,errorObj) {
			alert(errorObj.type + ' Error: ' + errorObj.info);
		}
	};
	
	if(setting){
		for(var i in setting){
			if(i=='scriptData'){
				for(var j in setting[i]){
					defaultSetting[i][j] = setting[i][j];
				}
			}else{
				defaultSetting[i] = setting[i];
			}
		}		
	}
	
	$("#"+uplodifyButtonId).uploadify(defaultSetting);
}

function mobile_inputs_upload(){
	var formObj = document.goodsForm;
	var formData = new FormData(formObj);

	var exit = false;

	$.ajax({
		'url' : '/goods_process/upload_goods_inputs',
		'mimeType' : 'multipart/form-data',
		'type' : 'post',
		'data' : formData,
		'contentType' : false,
		'cache' : false,
		'processData' : false,
		'dataType' : 'json',
		'async' : false,
		'success' : function(res){
			if(typeof res.status != 'undefined'){
				if(res.status=='1'){
					if(typeof res.fileList != 'undefined' && res.fileList != null){
						for(var i=0;i<res.fileList.length;i++){
							if(typeof res.fileList[i] != 'undefined' && res.fileList[i] != null){
								$("input.webftpFormItemInput").eq(i).val(res.fileList[i].filePath);
								$("input.webftpFormItemInputOriName").eq(i).val(res.fileList[i].fileInfo.client_name);
								$("img.webftpFormItemPreview").eq(i).attr('src','/'+res.fileList[i].filePath).show();
								$("input.inputsUploadButton").eq(i).val('');
							}
						}
					}
				}else{
					alert(res.msg);
					exit = true;
				}
			}else{
				alert('파일 업로드 실패');
				exit = true;
			}
		}
	});

	return exit;
}

$(document).ready(function(){

	// 가격대체문구 상품 필수/추가옵션 비활성화 처리 leewh 2015-01-15
	if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
		try{
			if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
				$("select").attr("disabled",true).selectbox("disable");
				return false;
			}
		}catch(e){};
	}

	if( gl_option_view_type == 'divide' && gl_options_count ){
		$("select[name='viewOptions[]']").bind("change",function(){
			var n = parseInt($(this).attr('id')) + 1;
			set_option(n);
		});
		set_option(0);
	}

	if( gl_option_view_type == 'divide' ){
		$(".viewOptionsspecialbtn").live('click',function(){
			var eqindex = $(this).attr("eqindex");
			$("select[name='viewOptions[]']").eq(eqindex).val($(this).attr("optvalue")).change();
		});
		var viewOptionsApply = function(thisObj){
			var msg = '';
			var optTag = '';
			var price = 0;
			var optTitle = '';
			var result = false;

			// 이미 선택된 옵션 인지  체크
			var join_option = "";
			var join_options = new Array();
			var exit = false;
			$("select[name='viewOptions[]']").each(function(idx){
				if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
					$(this).find("option").eq(0).attr('selected',true);
					exit = true;
					return false;
				}

				if ($(this).find("option:selected").val() =="" ) {
					alert("옵션을 먼저 선택해 주세요.");
					$(this).focus();
					exit = true;
					return false;
				}
				join_option += "|" + $(this).find("option:selected").val();
			});

			if (exit) return false;

			$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(){
				var inputsTitle = $(this).closest('tr').children(".inputsTitle").html();
				if($(this).is(":required") && !$(this).val().length){
					alert(inputsTitle + " 옵션을 입력해 주세요.");
					$(this).focus();
					exit = true;
					return false;
				}
				join_option += "|" + $(this).val();
			});

			if (exit) return false;

			if($(thisObj).find("option:selected").attr('infomation') != "" && $(thisObj).find("option:selected").attr('infomation')!="null"){
				$("#viewoptionsInfoTr").show();
				$("#viewOptionsInfo").html($(thisObj).find("option:selected").attr('infomation'));
			}else{
				$("#viewoptionsInfoTr").hide();
				$("#viewOptionsInfo").html("");
			}

			$("input[name='option[0][]']").each(function(i){
				join_options[i] = "";
				$("select[name='viewOptions[]']").each(function(idx){
					join_options[i] += "|"+ $("input[name='option["+idx+"][]']").eq(i).val();
				});
				$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(idx){
					join_options[i] += "|"+ $("input[name='inputsValue["+idx+"][]']").eq(i).val();
				});
			});

			for(var i=0;i<join_options.length;i++){
				if(join_option == join_options[i]) return false;
			}
			if(!$(thisObj).find("option:selected").val()) return false;

			if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
				$("select[name='viewOptions[]']").each(function(idx){
					if(msg) msg += "<br />";
					optTitle = $(".optionTitle").eq(idx).html();
					msg += optTitle;
					msg += " : " + $(this).find("option:selected").val();
					msg += "<input type='hidden' name='option["+idx+"][]' value=\""+ $(this).find("option:selected").val()+"\">";
					msg += "<input type='hidden' name='optionTitle["+idx+"][]' value=\"" + optTitle + "\">";
					price = $(this).find("option:selected").attr('price');
				});
				if($("input[name='viewInputs[]'],textarea[name='viewInputs[]']").length){
					$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(idx){
						if(msg) msg += "<br/>";
						inputsTitle = $(".inputsTitle").eq(idx).html();
						msg += inputsTitle;
						if($(this).closest(".webftpFormItem").length){
							msg += " : " + $(this).closest(".webftpFormItem").find(".webftpFormItemInputOriName").val();
						}else{
							msg += " : " + $(this).val();
						}
						msg += "<input type='hidden' name='inputsValue["+idx+"][]' value=\""+ $(this).val()+"\">";
						msg += "<input type='hidden' name='inputsTitle["+idx+"][]' value=\"" + inputsTitle + "\">";
					});
				}
				optTag += "<tr class='quanity_row'>";
				optTag += "<td class='quantity_cell option_col_text'>";
				optTag += "	<table align='left' border='0' cellpadding='0' cellspacing='0'>";
				optTag += "	<tr>";
				optTag += "	    <td class='quantity_cell option_text'>" + msg + "</td>";
				optTag += "	</tr>";
				optTag += "	<tr>";
				optTag += "		<td style='white-space:nowrap'><button class='btn_graybox eaMinus'>-</button><input type='text' name='optionEa[]' value='1' class='onlynumber ea_change' style='text-align:center; width:31px; height:31px; border:1px solid #d0d0d0' /><button class='btn_graybox eaPlus'>+</button></td>";
				optTag += "	</tr>";
				optTag += "	</table>";
				optTag += "</td>";
				optTag += "<td class='quantity_cell option_col_price' align='right' colspan='2'>";
				optTag += "	<span class='optionPrice hide'>"+price+"</span><strong class='out_option_price'>"+comma(price)+"</strong>원 <button class='btn_graybox removeOption'>x</button>";
				optTag += "</td>";
				optTag += "</tr>";
			}else{
				$("select[name='viewOptions[]']").each(function(idx){
					if(msg) msg += "<br/>";
					optTitle = $(".optionTitle").eq(idx).html();
					msg += optTitle;
					msg += " : " + $(this).find("option:selected").val();
					msg += "<input type='hidden' name='option["+idx+"][]' value=\""+ $(this).find("option:selected").val()+"\">";
					msg += "<input type='hidden' name='optionTitle["+idx+"][]' value=\"" + optTitle + "\">";
					price = $(this).find("option:selected").attr('price');
				});
				if($("input[name='viewInputs[]'],textarea[name='viewInputs[]']").length){
					$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(idx){
						if(msg) msg += "<br/>";
						inputsTitle = $(".inputsTitle").eq(idx).html();
						msg += inputsTitle;
						if($(this).closest(".webftpFormItem").length){
							msg += " : " + $(this).closest(".webftpFormItem").find(".webftpFormItemInputOriName").val();
						}else{
							msg += " : " + $(this).val();
						}
						msg += "<input type='hidden' name='inputsValue["+idx+"][]' value=\""+ $(this).val()+"\">";
						msg += "<input type='hidden' name='inputsTitle["+idx+"][]' value=\"" + inputsTitle + "\">";
					});
				}
				optTag += "<tr class='quanity_row'>";
				optTag += "<td class='quantity_cell option_text'>" + msg + "</td>";
				optTag += "<td class='quantity_cell option_col_text'>";
				optTag += "	<table align='left' border='0' cellpadding='1' cellspacing='0'>";
				optTag += "	<tr>";
				optTag += "		<td><input type='text' name='optionEa[]' value='1' class='onlynumber ea_change' style='width:25px; height:15px;' /></td>";
				optTag += "		<td align='right' style='font-size:0px;'>";
				optTag += "			<div><img src='/data/skin/"+gl_skin+"/images/design/btn_num_plus.gif' class='hand eaPlus' /></div>";
				optTag += "			<div><img src='/data/skin/"+gl_skin+"/images/design/btn_num_minus.gif' class='hand eaMinus' /></div>";
				optTag += "		</td>";
				optTag += "		<td>개</td>";
				optTag += "	</tr>";
				optTag += "	</table>";
				optTag += "</td>";
				optTag += "<td class='quantity_cell option_col_price' align='right'>";
				optTag += "	<span class='optionPrice hide'>"+price+"</span><strong class='out_option_price'>"+comma(price)+"</strong>원 <img src='/data/skin/"+gl_skin+"/images/icon/icon_del_detail.gif' class='hand removeOption' />";
				optTag += "</td>";
				optTag += "</tr>";
			}

			$("select[name='viewOptions[]']").first().focus();

			$("table.goods_quantity_table").append(optTag);
			$("div.goods_quantity_table_container").show();
			calculate_goods_price();
		};
	}else{
		set_option_join();
		
		var viewOptionsApply = function(thisObj){
			if(!$(thisObj).find("option:selected").val()) {
				alert("옵션을 먼저 선택해 주세요.");
				$(this).focus();
				return false;
			}
			
			var exit = false;
			$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(){
				var inputsTitle = $(this).closest('tr').children(".inputsTitle").html();
				if($(this).is(":required") && !$(this).val().length){
					alert(inputsTitle + " 옵션을 입력해 주세요.");
					$(this).focus();
					exit = true;
					return false;
				}
			});
			if(exit) return false;

			var msg = gl_opttag;
			var optTag = '';
			var price = $(thisObj).find("option:selected").attr('price');

			// 이미 선택된 옵션 인지  체크
			var join_option = "";
			var join_options = new Array();
			var titles = $("th.optionTitle").html()
			titles = titles.split(',');
			for(var idx=0;idx<titles.length;idx++){
				var key = idx+1;
				join_option += "|" + $(thisObj).find('option:selected').attr('opt'+key);
			}
			$("input[name='option[0][]']").each(function(i){
				join_options[i] = "";
				for(var idx=0;idx<titles.length;idx++){
					join_options[i] += "|"+ $("input[name='option["+idx+"][]']").eq(i).val();
				}
			});
			$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(){
				join_option += "|" + $(this).val();
			});
			$("input[name='inputsValue[0][]']").each(function(i){
				$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(idx){
					join_options[i] += "|"+ $("input[name='inputsValue["+idx+"][]']").eq(i).val();
				});
			});
			for(var i=0;i<join_options.length;i++){
				if(join_option == join_options[i]) return false;
			}

			if($("input[name='viewInputs[]'],textarea[name='viewInputs[]']").length){
				$("input[name='viewInputs[]'],textarea[name='viewInputs[]']").each(function(idx){
					if(msg) msg += "<br/>";
					inputsTitle = $(".inputsTitle").eq(idx).html();
					msg += inputsTitle;
					if($(this).closest(".webftpFormItem").length){
						msg += " : " + $(this).closest(".webftpFormItem").find(".webftpFormItemInputOriName").val();
					}else{
						msg += " : " + $(this).val();
					}
					msg += "<input type='hidden' name='inputsValue["+idx+"][]' value=\""+ $(this).val()+"\">";
					msg += "<input type='hidden' name='inputsTitle["+idx+"][]' value=\"" + inputsTitle + "\">";
				});
			}

			if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
				optTag += "<tr class='quanity_row'>";
				optTag += "<td class='quantity_cell option_col_text'>";
				optTag += "	<table align='left' border='0' cellpadding='0' cellspacing='0'>";
				optTag += "	<tr>";
				optTag += msg;
				optTag += "	</tr>";
				optTag += "	<tr>";
				optTag += "		<td><button class='btn_graybox eaMinus'>-</button><input type='text' name='optionEa[]' value='1' class='onlynumber ea_change' style='text-align:center; width:31px; height:31px; border:1px solid #d0d0d0' /><button class='btn_graybox eaPlus'>+</button></td>";
				optTag += "	</tr>";
				optTag += "	</table>";
				optTag += "</td>";
				optTag += "<td class='quantity_cell option_col_price' align='right' colspan='2'>";
				optTag += "	<span class='optionPrice hide'>"+price+"</span><strong class='out_option_price'>"+comma(price)+"</strong>원 <button class='btn_graybox removeOption'>x</button>";
				optTag += "</td>";
				optTag += "</tr>";
			}else{
				optTag += msg;

				optTag += "</td>";
				optTag += "<td class='quantity_cell option_col_text'>";
				optTag += "	<table align='center' border='0' cellpadding='1' cellspacing='0'>";
				optTag += "	<tr>";
				optTag += "		<td>";
				optTag += "			<input type='text' name='optionEa[]' value='1' class='onlynumber ea_change' style='width:25px; height:15px;' />";
				optTag += "		</td>";
				optTag += "		<td align='right' style='font-size:0px;'>";
				optTag += "			<div><img src='/data/skin/"+gl_skin+"/images/design/btn_num_plus.gif' class='hand eaPlus' /></div>";
				optTag += "			<div><img src='/data/skin/"+gl_skin+"/images/design/btn_num_minus.gif' class='hand eaMinus' /></div>";
				optTag += "		</td>";
				optTag += "		<td>개</td>";
				optTag += "	</tr>";
				optTag += "	</table>";
				optTag += "</td>";
				optTag += "<td class='quantity_cell option_col_price' align='right'>";
				optTag += "	<span class='optionPrice hide'>"+price+"</span><strong class='out_option_price'>"+comma(price)+"</strong>원<img src='/data/skin/"+gl_skin+"/images/icon/icon_del_detail.gif' class='hand removeOption' />";
				optTag += "</td>";
				optTag += "</tr>";
			}

			var optvalue = $(thisObj).find("option:selected").val();
			$(".viewOptionsspecialbtn").hide();
			$(".viewOptionsspecialbtn").each(function(i){
				if( $(this).attr("optvalue") == optvalue ){
					$(this).show();
				}
			});

			$("table.goods_quantity_table").append(optTag);
			$("div.goods_quantity_table_container").show();
			calculate_goods_price();
		};
	}

	if($(".viewOptionsApply").length && $("input[name='viewInputs[]'],textarea[name='viewInputs[]']").length){

		// 모바일 추가입력옵션 파일 업로드처리(pc의 uploadify를 모바일에서 사용못하므로 대체스크립트)
		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			$(".viewOptionsApply").click(function(){
				if($(".viewOptionsApply").length && $(".inputsUploadButton").length){
					if(mobile_inputs_upload()) return false;
				}
				viewOptionsApply($("select[name='viewOptions[]']").last().get());
			});
		}
		// PC
		else{
			$(".viewOptionsApply").click(function(){
				viewOptionsApply($("select[name='viewOptions[]']").last().get());
			});

			$(".inputsUploadButton").each(function(){
				setUserUploadifyButton($(this).attr('id'));
			});
		}
	}else{
		$("select[name='viewOptions[]']").last().bind("change",function(){
			viewOptionsApply(this);
		});

		if(!(typeof gl_mobile_mode!="undefined" && gl_mobile_mode)){
			$(".inputsUploadButton").each(function(){
				setUserUploadifyButton($(this).attr('id'));
			});
		}
	}

	$(".viewSubOptionsspecialbtn").die().live('click',function(){
		var eqindex = $(this).attr("eqindex");
		$("select[name='viewSuboption[]']").eq(eqindex).val($(this).attr("suboptvalue")).change();
		// 2014-12-18 옵션 개편 후 (ocw)
		try{
			$("select[name='viewSuboption[]']").eq(eqindex).each(function(){
				var sb = $(this).attr('sb');
				$(this).removeData('selectbox').show();
				$("#sbHolder_"+sb).remove();
				$(this).selectbox();
			});
		}catch(e){};
	});

	$("select[name='viewSuboption[]']").bind("change",function(){
		var msg = '';
		var optTag = '';
		var price = 0;
		var idx = $("select[name='viewSuboption[]']").index(this);
		var title = $(".suboptionTitle").eq(idx).html();
		var suboption = $(this).find("option:selected").val();

		// 이미 선택된 옵션 인지  체크
		var result = true;
		if(suboption)
		{
			$("input[name='suboption[]']").each(function(key){
				if(suboption == $("input[name='suboption[]']").eq(key).val() && title == $("input[name='suboptionTitle[]']").eq(key).val()){
					result = false;
				}
			});
		}
		if(!result) return false;

		if(!suboption) return false;

		msg = $(".suboptionTitle").eq(idx).html() + " : " + $(this).find("option:selected").val();
		msg += "<input type='hidden' name='suboption[]' class='suboption' value=\""+ suboption +"\">";
		msg += "<input type='hidden' name='suboptionTitle[]' value=\""+ title +"\">";
		price = $(this).find("option:selected").attr('price');
		
		

		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			optTag += "<tr class='quanity_row suboption_tr'>";
			optTag += "<td class='quantity_cell_sub'>";
			optTag += "	<table align='left' border='0' cellpadding='0' cellspacing='0'>";
			optTag += "	<tr>";
			optTag += "     <td class='option_text'>- " + msg + "</td>";
			optTag += "	</tr>";
			optTag += "	<tr>";
			optTag += "		<td><button class='btn_graybox eaMinus'>-</button><input type='text' name='suboptionEa[]' value='1' class='onlynumber ea_change' style='text-align:center; width:31px; height:31px; border:1px solid #d0d0d0' /><button class='btn_graybox eaPlus'>+</button></td>";
			optTag += "	</tr>";
			optTag += "	</table>";
			optTag += "</td>";
			optTag += "<td class='quantity_cell_sub_price' align='right' colspan='2'>";
			optTag += "	<span class='suboptionPrice hide'>"+price+"</span><strong class='out_suboption_price'>"+comma(price)+"</strong>원 <button class='btn_graybox removeOption'>x</button>";
			optTag += "</td>";
			optTag += "</tr>";
		}else{
			optTag += "<tr class='quanity_row suboption_tr'>";
			optTag += "<td class='option_text quantity_cell_sub'>- " + msg + "</td>";
			optTag += "<td class='quantity_cell_sub'>";
			optTag += "	<table align='center' border='0' cellpadding='1' cellspacing='0'>";
			optTag += "	<tr>";
			optTag += "		<td><input type='text' name='suboptionEa[]' value='1' class='onlynumber ea_change' style='width:25px; height:15px;' /></td>";
			optTag += "		<td align='right' style='font-size:0px;'>";
			optTag += "			<div><img src='/data/skin/"+gl_skin+"/images/design/btn_num_plus.gif' class='hand eaPlus' /></div>";
			optTag += "			<div><img src='/data/skin/"+gl_skin+"/images/design/btn_num_minus.gif' class='hand eaMinus' /></div>";
			optTag += "		</td>";
			optTag += "		<td>개</td>";
			optTag += "	</tr>";
			optTag += "	</table>";
			optTag += "</td>";
			optTag += "<td class='quantity_cell_sub_price' align='right'>";
			optTag += "	<span class='suboptionPrice hide'>"+price+"</span><strong class='out_suboption_price'>"+comma(price)+"</strong>원 <img src='/data/skin/"+gl_skin+"/images/icon/icon_del_detail.gif' class='hand removeOption' />";
			optTag += "</td>";
			optTag += "</tr>";
		}

		if( $("table.goods_quantity_table tr.quanity_row").length == 0 && $("select[name='viewOptions[]']").length > 0 ){
			if(typeof gl_string_price_use == 'undefined' || !gl_string_price_use){
				openDialogAlert("옵션을 먼저 선택해주세요.",400,140,'');
			}
			$(this).find("option").eq(0).attr('selected',true);
			// 2014-12-18 옵션 개편 후 (ocw)
			try{
				var sb = $(this).attr('sb');
				$(this).removeData('selectbox').show();
				$("#sbHolder_"+sb).remove();
				$(this).selectbox();
			}catch(e){};
			return false;
		}

		if( $("table.goods_quantity_table tr.quanity_row").length == 0 ){
			$("table.goods_quantity_table").append(optTag);
		}else{
			var targetObj = $("table.goods_quantity_table tr.quanity_row").eq(0);
			while(targetObj.next().is(".suboption_tr")){
				targetObj = targetObj.next();
			}
			targetObj.after(optTag);
		}
		$("div.goods_quantity_table_container").show();
		calculate_goods_price();
	});

	$(".eaPlus").die().live("click",function(e){
		var eaObj = $(this).closest('tr').find("input[name='optionEa[]'],input[name='suboptionEa[]']");
		var val = parseInt(eaObj.val())+1;
		if(val > 0) eaObj.val(parseInt(eaObj.val())+1);
		calculate_goods_price();

		return false;
	});
	$(".eaMinus").die().live("click",function(e){
		var eaObj = $(this).closest('tr').find("input[name='optionEa[]'],input[name='suboptionEa[]']");
		var val = parseInt(eaObj.val())-1;
		if(val > 0) eaObj.val(parseInt(eaObj.val())-1);
		calculate_goods_price();

		return false;
	});
	$(".removeOption").die().live("click",function(e){

		var trObj = $(this).closest("tr.quanity_row");
		var idx = $("table.goods_quantity_table tr.quanity_row").index(trObj);

		if( idx == 0 ){
			if($("table.goods_quantity_table tr.suboption_tr").length > 0 && $("table.goods_quantity_table tr.quanity_row").length != $("table.goods_quantity_table tr.suboption_tr").length){
				//openDialogAlert("추가구성을 먼저 삭제해주세요.",400,140,'');
				//return false;
				$("table.goods_quantity_table tr.suboption_tr").remove();
			}
		}
		$(this).parent().parent().remove();
		if($("table.goods_quantity_table").find("tr").length == 0){
			$("div.goods_quantity_table_container").hide();
		}
		calculate_goods_price();

		return false;
	});

	$("input[name='optionEa[]'],input[name='suboptionEa[]']").die().live("keyup",function(e){
		calculate_goods_price();

		return false;
	});


	$('#couponDownload, #mbox, .couponDownload').bind("click",function() {
		var memberSeq = gl_member_seq;

		if( !memberSeq ){
			location.href="/member/login?return_url="+gl_request_uri;
			return false;
		}
		coupondownlist(gl_goods_seq,gl_request_uri);
	}); 

	$("button[name='couponDownloadButton']").die().live("click",function(){
		var url = '../coupon/download?goods='+$(this).attr('goods')+'&coupon='+$(this).attr('coupon');
		actionFrame.location.href = url;
	});
	
	if( $(".couponDownloadlay").html() ){
		
		$.getJSON('../coupon/goods_coupon_max?no='+gl_goods_seq, function(data) {
			if(data){
				$(".couponDownloadlay").show();

				var couponDetailpricelayhtml = '<div>';
				var couponDetailhelphtml = '<ul>';
				couponDetailhelphtml +=  "<li>- <b>" + data.download_enddatetitle + "</b> </li>";
				couponDetailhelphtml +=  "<li>- <b>" + data.issue_enddatetitle + "</b> </li>";
				if(data.sale_type == 'won'){
					var realprice = comma(data.won_goods_sale);

					couponDetailhelphtml +=  "<li>- <b>"+ realprice +"원 할인 </b></li>";
					$(".cb_percent").html( realprice + '원' );
					for (var i = 0; i < realprice.length ; i++) {
						var number = (realprice.substring(i,i+1));
						if(number == ',') {
							couponDetailpricelayhtml += "<img src='/data/coupon/coupon_i_comma.png' >";
						}else{
							couponDetailpricelayhtml += "<img src='/data/coupon/coupon_i_no"+number+".png' >";
						}
					}
					couponDetailpricelayhtml +=  "<img src='/data/coupon/coupon_i_won.png' >";
				}else{
					var realpercent = (data.percent_goods_sale);

					couponDetailhelphtml +=  "<li>- <b>" + realpercent + "% 할인 (최대 " + comma(data.max_percent_goods_sale) + "원)</b></li>";
					$(".cb_percent").html( realpercent + '%' );
					for (var i = 0; i < realpercent.length ; i++) {
						var number = parseInt(realpercent.substring(i,i+1));
						if(number >= 0 ) couponDetailpricelayhtml += "<img src='/data/coupon/coupon_i_no"+number+".png' >";
					}
					couponDetailpricelayhtml +=  "<img src='/data/coupon/coupon_i_per.png' >";
					couponDetailpricelayhtml +=  "<img src='/data/coupon/coupon_i_dc.png' >";
				}
				couponDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
				if(data.categoryhtml) {
					if(data.issue_type == 'except'){
						couponDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 불가</li>";
					}else if(data.issue_type == 'issue'){
						couponDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 가능</li>";
					}else{
						couponDetailhelphtml +=  "<li>- 전체 상품 사용 가능</li>";
					}
				}

				//동시사용불가
				if( data.coupon_same_time  == 'N' ){
					couponDetailhelphtml +=  "<li>- 단독 쿠폰( 타 쿠폰과 함께 사용 불가)</li>";
				}

				//중복사용여부
				if( data.duplication_use  == '1' ){
					couponDetailhelphtml +=  "<li>- 중복 다운로드 가능(사용 후 다시 다운로드 가능)</li>";
				}

				couponDetailhelphtml +=  "</ul>";
				couponDetailpricelayhtml +=  "</div>";

				if( data.coupon_img == 4 ) {
					$(".lt").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');
					$(".rt").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');
					$(".lb").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');
					$(".rb").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');

					$("#couponDetailhelplay").html(couponDetailhelphtml);//우측 기본정보
				}else{
					$(".lt").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$(".rt").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$(".lb").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$(".rb").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$("#couponDetailhelplay").html(couponDetailhelphtml);//우측 기본정보
					$("#couponDetailpricelay").html(couponDetailpricelayhtml);//좌측 쿠폰이미지
				}
				$("#couponDetaillay").html(data.couponDetaillay);//
			}else{
				$(".couponDownloadlay").hide();
			}
		});
	} 

	$.getJSON('/promotion/goods_coupon_max?no='+gl_goods_seq+'&price='+gl_goods_price, function(data) {
		if(data){
			if(data.codenumber) $(".promotion_code_area").show();
			$(".promotion_code_area span.cb_codepercent").html(data.benifit);
			$(".promotion_code_area span.cb_codenumber").html(data.codenumber);
		}
	});

	$("#buy").bind("click",function(){
		if( check_option() ){
			var f = $("form[name='goodsForm']");
			f.attr("action","../order/add?mode=direct");
			f.submit();
			f.attr("action","../order/add");
		}
	});

	// 모바일2에서 사용 2014-01-13 lwh
	$('#addCart_option').bind("click",function(){
		if	($(this).hasClass('isopen')){
			var f = $("form[name='goodsForm']");
			if( check_option() ) f.submit();
		}else{
			showGoodsOptionLayer();
		}
	});

	$("#addCart").bind("click",function(){
		var f = $("form[name='goodsForm']");
		if( check_option() ) f.submit();
	});

	$("input[name='optionEa[]']").die().live("keyup",function(){
		calculate_goods_price();
		return false;
	});
	$("input[name='suboptionEa[]']").die().live("keyup",function(){
		calculate_goods_price();
		return false;
	});
	$("input[name='optionEa[]'],input[name='suboptionEa[]']").die().live("change",function(){
		if(!(num($(this).val()) > 0)) $(this).val(1).keyup();
	});

	$("#price_area").bind("mouseover",function(){
		$(this).closest("td").find("div").removeClass("hide");
		$(".goods_spec_table").find(".fb-like").hide();
	}).bind("mouseout",function(){
		$(this).closest("td").find("div").addClass("hide");
		$(".goods_spec_table").find(".fb-like").show();
	});

	$(".gift_goods").bind("mouseover",function(){
		$(this).closest("td").find("div").removeClass("hide");
	}).bind("mouseout",function(){
		$(this).closest("td").find("div").addClass("hide");
	});

	$('#nointerest').toggle(function() {
		$(this).closest("td").find("div").removeClass("hide");
	}, function() {
		$(this).closest("td").find("div").addClass("hide");
	});

	$("#nointerest_event").bind("click",function(){
		$(this).closest("div").hide();
	});

	if(typeof gl_goods_seq!="undefined" && gl_goods_seq){
		setFacebooklikeopsave(gl_goods_seq);
	}

});