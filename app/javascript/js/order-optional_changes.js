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
	    'folder'			: '/data/tmp',
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

function set_option_join(){
	var opt = '';
	var gdata = "no="+gl_goods_seq;

	$.ajax({
		type: "get",
		url: "../goods/option_join",
		data: gdata,
		async: false,
		success: function(result){
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
							var whitecolorborder = " style='display:inline-block;width:28px; height:28x; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
							Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span  name='viewOptionsspecialbtn' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+" hide '  style='width:30px; height:30px; margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+opt+"\"  optvalue=\""+opt+"\" price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\"  eqindex='0' ><font   "+whitecolorborder+" >■</font></span></span>";
						}else{
							var whitecolorborder = " style='display:inline-block;width:18px; height:18px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
							Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span  name='viewOptionsspecialbtn' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+" hide '  style='width:30px; height:30px; margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+opt+"\" optvalue=\""+opt+"\"  price=\""+obj.price+"\" opt1=\""+obj.option1+"\" opt2=\""+obj.option2+"\" opt3=\""+obj.option3+"\" opt4=\""+obj.option4+"\" opt5=\""+obj.option5+"\" infomation=\""+obj.infomation+"\"  eqindex='0' ><font "+whitecolorborder+" >■</font></span></span>";
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
				});
			}catch(e){};
		}
	});
}

function set_option(n){
	var gdata = "no="+gl_goods_seq;
	$("select[name='viewOptions[]']").each(function(i){
		if(i < n){
			gdata += '&max='+gl_option_divide_title_count+'&options[]='+encodeURIComponent($(this).val());
		}
	});

	$.ajax({
		type: "get",
		url: "../goods/option",
		data: gdata,
		async: false,
		success: function(result){
			var data = eval(result);
			$("select[name='viewOptions[]']").eq(n).find("option").each(function(i){
				if(i!=0)$(this).remove();
			});
			var add_price = 0;
			var Optionsspecialhtml = ''; 
			var goods_price  = gl_goods_price;
			goods_price = goods_price - gl_event_sale_unit;

			for(var i=0;i<data.length;i++){
				var obj = data[i];
				add_price = obj.price - goods_price;
				if(obj.event){
					if(obj.event.target_sale) {
						add_price = 0;
					}
				}
				if( obj.chk_stock ){
					if( obj.color && $("select[name='viewOptions[]']").eq(n).attr("opttype") == 'color'  ) {
						if( obj.ismobile ){//mobile check
							var whitecolorborder = " style='display:inline-block;width:28px; height:28px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
							Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span name='viewOptionsspecialbtn' opspecialtype='color' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+"'  style='width:30px;margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+obj.opt+"\"  optvalue=\""+obj.opt+"\" ='"+obj.price+"' infomation='"+obj.infomation+"' eqindex='"+n+"' opspecial_location='"+obj.opspecial_location.color+"' ><font "+whitecolorborder+" >■</font></span></span>";
						}else{
							var whitecolorborder = " style='display:inline-block;width:18px; height:18px; border:1px solid #ccc; background-color:"+obj.color+"; cursor:pointer;' ";
							Optionsspecialhtml += "<span  class='"+obj.color+"'  ><span name='viewOptionsspecialbtn' opspecialtype='color' class='viewOptionsspecialbtn hand bbs_btn "+obj.color+"'  style='width:30px; height:30px; margin-top:2px; margin-left:2px; border:0px solid #e8e8e8; color:"+obj.color+";size:25px;'  value=\""+obj.opt+"\"  optvalue=\""+obj.opt+"\"  price='"+obj.price+"' infomation='"+obj.infomation+"' eqindex='"+n+"' opspecial_location='"+obj.opspecial_location.color+"' ><font "+whitecolorborder+" >■</font></span></span>";
						}
					}
					if( gl_option_divide_title_count - n == 1 ){
						if( add_price == 0){
							$("select[name='viewOptions[]']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"'>"+obj.opt+"</option>");
						}else if(add_price > 0) {
							$("select[name='viewOptions[]']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"' >"+obj.opt+" (+"+ comma(add_price)+")</option>");
						}else if(add_price < 0){
							$("select[name='viewOptions[]']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' infomation='"+obj.infomation+"'>"+obj.opt+" (-"+ comma(add_price*-1)+")</option>");
						}
					}else{
						$("select[name='viewOptions[]']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"'>"+obj.opt+"</option>");
					}
				}else{
					$("select[name='viewOptions[]']").eq(n).append("<option value=\""+obj.opt+"\" price='"+obj.price+"' disabled='disabled'>"+obj.opt+" 품절</option>");
				}
			}
			
			//색상노출
			if( $("select[name='viewOptions[]']").eq(n).attr("opttype") == 'color' ) {
				if( n == 0 ){//색상이 첫번째
					$(".viewOptionsspecialays.color").html(Optionsspecialhtml); 
				}else if( $("select[name='viewOptions[]']").length > 1 && n > 0 ) {//두번째부터 다섯번째
					if( $("select[name='viewOptions[]']").eq((n-1)).find("option:selected").val() ) {
						$(".viewOptionsspecialays.color").html(Optionsspecialhtml); 
					}else{
						$(".viewOptionsspecialays.color").html(''); 
					}
				}
			}

			// 2014-12-18 옵션 개편 후 (ocw)
			try{
				$("select[name='viewOptions[]']").each(function(){
					var sb = $(this).attr('sb');
					$(this).removeData('selectbox').show();
					$("#sbHolder_"+sb).remove();
					$(this).selectbox();
				});
			}catch(e){};
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
	goods_price = goods_price - gl_event_sale_unit;

	$(".optionPrice").each(function(){
		tot_ea += parseInt($(this).parent().prev().find("input").val());
	});
	$(".optionPrice").each(function(idx){
		ea = parseInt($(this).parent().prev().find("input").val());
		price = $(this).html();
		price = price - gl_event_sale_unit;
		price = get_multi_sale_price(tot_ea,price);
		$(".out_option_price").eq(idx).html(comma(price * ea));
		tot += price * ea;
	});

	$(".suboptionPrice").each(function(i){		
		ea = parseInt($(this).parent().prev().find("input").val());		
		price = $(this).html();

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
			url: "/goods/option_stock",
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
			url: "/goods/suboption_stock",
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
			openDialogAlert($(".goods_name").text() + " 상품의 최소 구매수량은 '+comma(gl_min_purchase_ea)+'개 입니다.",400,140,'');
			return false;
		}
	}
	if(gl_max_purchase_limit=='limit' && gl_max_purchase_ea ){
		if(item_ea_sum>gl_max_purchase_ea){
			openDialogAlert($(".goods_name").text() + " 상품의 최대 구매수량은 '+comma(gl_max_purchase_ea)+'개 입니다.",400,140,'');
			return false;
		}
	}

	return true;
}

function input_limits(obj, limit){
	var value	= obj.value;
	var len		= value.length;
	if(limit>0 && limit){
		if(limit <= len){
			alert(limit+"자내로 작성해주세요.");
			obj.value = value.substring(0,limit-1);
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
	$("select[name='viewSuboption[]'][require='y']").each(function(){
		var subOptionTitle = $(this).parent().parent().find(".suboptionTitle").text();
		if(!$("input[name='suboptionTitle[]'][value='"+subOptionTitle+"']").length){
			openDialogAlert(subOptionTitle + " 옵션을 선택해 주세요.",400,140,'');
			subOptionOk = false;
		}

	});
	if(!subOptionOk) return false;

	if(!check_purchase_ea()) return false;

	return true;
}

$(document).ready(function(){

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
		$("select[name='viewOptions[]']").last().bind("change",function(){
			var msg			= '';
			var optTag		= '';
			var price		= 0;
			var optTitle	= '';
			var result		= false;
			var viewType	= $(this).attr('viewType');

			// 이미 선택된 옵션 인지  체크
			var join_option = "";
			var join_options = new Array();
			$("select[name='viewOptions[]']").each(function(idx){
				join_option += "|" + $(this).find("option:selected").val();
			});

			if($(this).find("option:selected").attr('infomation') != ""){
				$("#viewoptionsInfoTr").show();
				$("#viewOptionsInfo").html($(this).find("option:selected").attr('infomation'));
			}else{
				$("#viewoptionsInfoTr").hide();
				$("#viewOptionsInfo").html("");
			}

			$("input[name='option[0][]']").each(function(i){
				join_options[i] = "";
				$("select[name='viewOptions[]']").each(function(idx){
					join_options[i] += "|"+ $("input[name='option["+idx+"][]']").eq(i).val();
				});
			});
			for(var i=0;i<join_options.length;i++){
				if(join_option == join_options[i]) return false;
			}
			if(!$(this).find("option:selected").val()) return false;
			
			if(viewType == 'store'){
				$("select[name='viewOptions[]']").each(function(idx){
					if(msg) msg += "<br/>";
					optTitle = $("#option_title_"+idx).val();
					msg += optTitle;
					msg += " : " + $(this).find("option:selected").val();
					msg += "<input type='hidden' name='option["+idx+"][]' value=\""+ $(this).find("option:selected").val()+"\">";
					msg += "<input type='hidden' name='optionTitle["+idx+"][]' value=\"" + optTitle + "\">";
					price = $(this).find("option:selected").attr('price');
				});

				optTag += "<div class='option_ea'>";				
				optTag += "<table class='goods_quantity_table' width='100%' cellpadding='0' cellspacing='0' border='0'>";
				optTag += "	<tr class='quanity_row'>";
				optTag += "		<td colspan='2'>" + msg + "</td>";
				optTag += "	</tr>";
				optTag += "	<tr class='quanity_row'>";
				optTag += "		<td class='quantity_cell'>";
				optTag += "			<div class='hand eaMinus' viewType='store'>-</div>";
				optTag += "			<input type='text' name='optionEa[]' value='1' class='onlynumber' style='border:1px solid #d0d0d0; width:23px; height:21px; float:left; text-align:center;' />";
				optTag += "			<div class='hand eaPlus' viewType='store'>+</div>";
				optTag += "			<div class='both'></div>";
				optTag += "		</td>";
				optTag += "		<td class='quantity_cell' align='right'>";
				optTag += "			<span class='optionPrice hide'>"+price+"</span>";
				optTag += "			<strong class='out_option_price'>"+comma(price)+"</strong>원";
				optTag += "		</td>";			
				optTag += "</tr>";
				optTag += "</table>";
				optTag += "</div>";
				
				$("div.option_ea_area").first().html(optTag);
				$("div.goods_quantity_table_container").show();
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

				optTag += "<td class='quantity_cell option_text'>" + msg + "</td>";
				optTag += "<td class='quantity_cell'>";
				optTag += "	<table align='center' border='0' cellpadding='1' cellspacing='0'>";
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
				optTag += "<td class='quantity_cell' align='right'>";
				optTag += "	<span class='optionPrice hide'>"+price+"</span><strong class='out_option_price'>"+comma(price)+"</strong>원";
				optTag += "</td>";

				$("tr.quanity_row").first().html(optTag);
				$("div.goods_quantity_table_container").show();
			}
			
			calculate_goods_price();
		});
	}else{
		
		set_option_join();
		/**
		$(".viewOptionsspecialbtn").live('click',function(){ 
			$("select[name='viewOptions[]']").val($(this).attr("optvalue")).change(); 
		});
		**/

		$("select[name='viewOptions[]']").last().bind("change",function(){
			var optTag = '';
			var price = $(this).find("option:selected").attr('price');

			// 이미 선택된 옵션 인지  체크
			var join_option = "";
			var join_options = new Array();
			var titles = $("th.optionTitle").html()
			titles = titles.split(',');
			for(var idx=0;idx<titles.length;idx++){
				var key = idx+1;
				join_option += "|" + $(this).find('option:selected').attr('opt'+key);
			}
			$("input[name='option[0][]']").each(function(i){
				join_options[i] = "";
				for(var idx=0;idx<titles.length;idx++){
					join_options[i] += "|"+ $("input[name='option["+idx+"][]']").eq(i).val();
				}
			});
			for(var i=0;i<join_options.length;i++){
				if(join_option == join_options[i]) return false;
			}

			optTag += gl_opttag;

			optTag += "</td>";
			optTag += "<td class='quantity_cell'>";
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
			optTag += "<td class='quantity_cell' align='right'>";
			optTag += "	<span class='optionPrice hide'>"+price+"</span><strong class='out_option_price'>"+comma(price)+"</strong>원";
			optTag += "</td>"; 
			
			var optvalue = $(this).find("option:selected").val();
			$(".viewOptionsspecialbtn").hide();
			$(".viewOptionsspecialbtn").each(function(){  
				if( $(this).attr("optvalue") == optvalue ){
					$(this).show();
				}
			});

			$("tr.quanity_row").first().html(optTag);
			$("div.goods_quantity_table_container").show();
			calculate_goods_price();
		});
	}

	$(".viewSubOptionsspecialbtn").live('click',function(){
		var eqindex = $(this).attr("eqindex");
		$("select[name='viewSuboption[]']").eq(eqindex).val($(this).attr("suboptvalue")).change();
	});

	$("select[name='viewSuboption[]']").bind("change",function(){
		var msg			= '';
		var optTag		= '';
		var price		= 0;
		var viewType	= $(this).attr('viewType');
		var idx			= $("select[name='viewSuboption[]']").index(this);
		var title		= $(".suboptionTitle").eq(idx).html();
		var suboption	= $(this).find("option:selected").val();

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

		if(viewType == 'store'){
			optTitle = $("#suboptionTitle_"+idx).val();
			msg = optTitle + " : " + $(this).find("option:selected").val();
			msg += "<input type='hidden' name='suboption[]' class='suboption' value=\""+ suboption +"\">";
			msg += "<input type='hidden' name='suboptionTitle[]' value=\""+ optTitle +"\">";
			price = $(this).find("option:selected").attr('price');
			seq	= $(this).find("option:selected").attr('seq');

			optTag += "<div class='sub_option_ea'>";				
			optTag += "<table class='goods_quantity_table' width='100%' cellpadding='0' cellspacing='0' border='0'>";
			optTag += "<input type='hidden' name='suboption_seq[]' value='" + seq + "' />";
			optTag += "	<tr class='quanity_row suboption_tr'>";
			optTag += "		<td colspan='2' class='option_text quantity_cell_sub'>" + msg + "</td>";
			optTag += "	</tr>";
			optTag += "	<tr class='quanity_row suboption_tr'>";
			optTag += "		<td class='quantity_cell_sub'>";
			optTag += "			<div class='hand eaMinus' viewType='store'>-</div>";
			optTag += "			<input type='text' name='suboptionEa[]' value='1' class='onlynumber' style='border:1px solid #d0d0d0; width:23px; height:21px; float:left; text-align:center;' />";
			optTag += "			<div class='hand eaPlus' viewType='store'>+</div>";
			optTag += "			<div class='both'></div>";
			optTag += "		</td>";
			optTag += "		<td class='quantity_cell_sub' align='right'>";
			optTag += "			<span class='suboptionPrice hide'>"+price+"</span>";
			optTag += "			<div class='hand removeOption' viewType='store'>×</div>";
			optTag += "			<div style='float:right; padding-top:3px;'>";
			optTag += "				<strong class='out_suboption_price'>"+comma(price)+"</strong>원&nbsp;";
			optTag += "			</div>";
			optTag += "			<div class='both'></div>";
			optTag += "		</td>";			
			optTag += "</tr>";
			optTag += "</table>";
			optTag += "</div>";

		}else{
			msg = $(".suboptionTitle").eq(idx).html() + " : " + $(this).find("option:selected").val();
			msg += "<input type='hidden' name='suboption[]' class='suboption' value=\""+ suboption +"\">";
			msg += "<input type='hidden' name='suboptionTitle[]' value=\""+ title +"\">";
			price = $(this).find("option:selected").attr('price');

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
			optTag += "<td class='quantity_cell_sub' align='right'>";
			optTag += "	<span class='suboptionPrice hide'>"+price+"</span><strong class='out_suboption_price'>"+comma(price)+"</strong>원 <img src='/data/skin/"+gl_skin+"/images/icon/icon_del_detail.gif' class='hand removeOption' />";
			optTag += "</td>";
			optTag += "</tr>";
		}

		if( $("table.goods_quantity_table tr.quanity_row").length == 0 ){
			openDialogAlert("옵션을 먼저 선택해주세요.",400,140,'');
			$(this).find("option").eq(0).attr('selected',true);
			return false;
		}

		if(viewType == 'store'){			
			$("div.option_ea_area").eq(0).after(optTag);
			$("div.goods_quantity_table_container").show();
		}else{
			$("table.goods_quantity_table tr.quanity_row").eq(0).after(optTag);
			$("div.goods_quantity_table_container").show();
		}
		calculate_goods_price();
	});

	$(".eaPlus").die().live("click",function(e){
		if($(this).attr('viewType') == 'store'){
			var eaObj = $(this).closest('.quanity_row').find("input");
			var val = parseInt(eaObj.val())+1;
			if(val > 0) eaObj.val(parseInt(eaObj.val())+1);
		}else{
			var eaObj = $(this).parent().parent().prev().find("input");
			var val = parseInt(eaObj.val())+1;
			if(val > 0) eaObj.val(parseInt(eaObj.val())+1);
		}
		calculate_goods_price();

		return false;
	});
	$(".eaMinus").die().live("click",function(e){
		if($(this).attr('viewType') == 'store'){
			var eaObj = $(this).closest('.quanity_row').find("input");
			var val = parseInt(eaObj.val())-1;
			if(val > 0) eaObj.val(parseInt(eaObj.val())-1);
		}else{
			var eaObj = $(this).parent().parent().prev().find("input");
			var val = parseInt(eaObj.val())-1;
			if(val > 0) eaObj.val(parseInt(eaObj.val())-1);
		}
		calculate_goods_price();

		return false;
	});
	$(".removeOption").die().live("click",function(e){

		var trObj = $(this).closest("tr.quanity_row");
		var idx = $("table.goods_quantity_table tr.quanity_row").index(trObj);

		if( idx == 0 ){
			if($("table.goods_quantity_table tr.suboption_tr").length > 0){
				openDialogAlert("추가구성을 먼저 삭제해주세요.",400,140,'');
				return false;
			}
		}

		if($(this).attr('viewType') == 'store'){
			$(this).closest("div.sub_option_ea").remove();			
		}else{
			$(this).parent().parent().remove();			
		}

		if($("table.goods_quantity_table").find("tr").length == 0){
			$("div.goods_quantity_table_container").hide();
		}
		calculate_goods_price();

		return false;
	});

	$("input.ea_change").die().live("keyup",function(e){
		calculate_goods_price();

		return false;
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

	if($("input[name='inputsValue[0][]']").length){
		$(".inputsUploadButton").each(function(){
			setUserUploadifyButton($(this).attr('id'));
		});
	}
});