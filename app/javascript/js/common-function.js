
function strip_tags(html){
	 
	//PROCESS STRING
	if(arguments.length < 3) {
		html=html.replace(/<\/?(?!\!)[^>]*>/gi, '');
	} else {
		var allowed = arguments[1];
		var specified = eval("["+arguments[2]+"]");
		if(allowed){
			var regex='</?(?!(' + specified.join('|') + '))\b[^>]*>';
			html=html.replace(new RegExp(regex, 'gi'), '');
		} else{
			var regex='</?(' + specified.join('|') + ')\b[^>]*>';
			html=html.replace(new RegExp(regex, 'gi'), '');
		}
	}

	//CHANGE NAME TO CLEAN JUST BECAUSE 
	var clean_string = html;

	//RETURN THE CLEAN STRING
	return clean_string;
}

/* input form style 적용*/
function apply_input_style(){
	
	setDefaultText();	
	setDatepicker();
	
	$(".onlynumber, .onlynumber_signed").live("keydown",function(e){
		if($(this).hasClass('onlynumber')) return onlynumber(e);	
		if($(this).hasClass('onlynumber_signed')) return onlynumber_signed(e);	
	}).live('focusin',function(){
		if($(this).val()=='0') $(this).val('');
	}).live('focusout',function(){
		if($(this).val()=='') $(this).val('0');
	}).live('change',function(){
		if($(this).attr('max')){
			var max = num($(this).attr('max'));
			if(num($(this).val()) > max){
				$(this).val(max).change();
			}
		}
		
		if($(this).attr('min')){
			var min = num($(this).attr('min'));
			if(num($(this).val()) < min){
				$(this).val(min).change();
			}
		}

		$(this).val( $(this).val().replace(/[^0-9\-]*/gi, ''));

	});
	
	$(".onlyfloat").live("keydown",function(e){		
		if (e.keyCode!=190 && e.keyCode!=110) return onlynumber(e);		
	}).live('focusin',function(){
		if($(this).val()=='0') $(this).val('');
	}).live('focusout',function(){
		if($(this).val()=='') $(this).val('0');
	});
	
	$(".percent").bind("keyup",function(){
		if( $(this).val() > 100 ){						
			$(this).val(100);
		}
	});
	
	help_tooltip();

}

function help_tooltip(){
	/* 툴팁 */
	$(".helpicon, .mainhelpicon, .help, .colorhelpicon").each(function(){
		
		var options = {
			className: 'tip-darkgray',
			bgImageFrameSize: 8,
			alignTo: 'target',
			alignX: 'right',
			alignY: 'center',
			offsetX: 10,
			allowTipHover: false,
			slide: false,
			showTimeout : 0
		}
		
		if($(this).attr('options')){
			var customOptions = eval('('+$(this).attr('options')+')');
			for(var i in customOptions){
				options[i] = customOptions[i];
			}
		}
				
		$(this).poshytip(options);
	});
}

/* input form style 적용*/
function chk_small_goods_image(){
	$('img.small_goods_image').error(function(){
		var noImageSrc = '/data/icon/goods/error/noimage_list.gif';
		if (this.src != noImageSrc) {// image was broken, replace with your new image
			this.src = noImageSrc;
		}
	}).each(function(){
		this.setAttribute('src',this.getAttribute('src'));
	});
}

function reMakeHelpIcon(){
	/* 툴팁 */
	$(".addHelpIcon").each(function(){
		
		var options = {
			className: 'tip-darkgray',
			bgImageFrameSize: 8,
			alignTo: 'target',
			alignX: 'right',
			alignY: 'center',
			offsetX: 10,
			allowTipHover: false,
			slide: false,
			showTimeout : 0
		}
		
		if($(this).attr('options')){
			var customOptions = eval('('+$(this).attr('options')+')');
			for(var i in customOptions){
				options[i] = customOptions[i];
			}
		}
				
		$(this).poshytip(options);
	});
}

function setDatepicker(selector){
	
	if(!selector) selector = ".datepicker";
	var randKey = Math.floor(Math.random() * 0x75bcd15);
		
	/* 달력 */
	$(selector).each(function(i){

		if(!$(this).is(".datepicker")){
			return;
		}
		if($(this).data('datepickerSettingDone')){
			return;
		}

		var randId = randKey.toString() + '_' + i.toString();
		
		var options = {
			dateFormat : 'yy-mm-dd',
			timeFormat: 'hh:mm:ss',
			showOn: "button",
			dayNamesMin : ['일', '월', '화', '수', '목', '금', '토'],
			monthNamesShort : ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
			showButtonPanel : true,
			showMonthAfterYear : true,
			changeYear : true,
			changeMonth : true,
			closeText : '닫기',
			currentText : '오늘',
			yearRange : '1900:c+10',
			buttonImage: "/app/javascript/jquery/icon_calendar.gif",
			buttonImageOnly: true
		}

		if($(this).attr('gettype')){
			options.dateFormat	= $(this).attr('gettype');
		}
		
		if(!$(this).attr('id') || $(this).attr('id').substring(0,11)=='datepicker_'){
			$(this).attr('id','datepicker_'+randId);
		}
		
		if($(this).attr('options')){
			var customOptions = eval('('+$(this).attr('options')+')');
			for(var i in customOptions){
				options[i] = customOptions[i];
			}
		}
		
		if($(this).is(".datepicker"))		{
			$(this).datepicker(options);
			$(this).data('datepickerSettingDone',true);
		}

	});
}

function setTimepicker(selector){
	
	if(!selector) selector = ".datetimepicker";
		
	/* 달력 */
	$(selector).each(function(i){
		
		var options = {
			dateFormat : 'yy-mm-dd',
			timeFormat: 'hh:mm:ss',
			showOn: "button",
			dayNamesMin : ['일', '월', '화', '수', '목', '금', '토'],
			monthNamesShort : ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
			showButtonPanel : true,
			showMonthAfterYear : true,
			changeYear : true,
			changeMonth : true,
			closeText : '닫기',
			currentText : '오늘',
			buttonImage: "/app/javascript/jquery/icon_calendar.gif",
			buttonImageOnly: true
		}
		
		if(!$(this).attr('id') || $(this).attr('id').substring(0,11)=='datepicker_'){
			$(this).attr('id','datepicker_'+i);
		}
		
		if($(this).attr('options')){
			var customOptions = eval('('+$(this).attr('options')+')');
			for(var i in customOptions){
				options[i] = customOptions[i];
			}
		}
		
		if($(this).is(".datetimepicker"))	$(this).datetimepicker(options);

	});
}



function onlynumber(event){
	var e = event.keyCode;	

	if (e>=48 && e<=57) return;
	if (e>=96 && e<=105) return;
	if (e>=37 && e<=40) return;
	if (e==8 || e==9 || e==13 || e==46) return;
	event.returnValue = false;
	return false;
}
function onlynumber_signed(event){
	var e = event.keyCode;	

	if (e>=48 && e<=57) return;
	if (e>=96 && e<=105) return;
	if (e>=37 && e<=40) return;
	if (e==8 || e==9 || e==13 || e==46 || e==109 || e==189) return;
	event.returnValue = false;
	return false;
}

//password -> type change , title add 2012-10-19
function setDefaultText(){
	try{ 
		$('input, textarea')
		.each(function(){
			var thisInputObj = $(this);
			if(thisInputObj.attr('title') != thisInputObj.attr('placeholder') || !thisInputObj.attr('placeholder')) thisInputObj.attr('placeholder',thisInputObj.attr('title'));
		}) 
		$('input, textarea').placeholder();
	} catch (e) { 
		setTimeout(function(){	
			$("input[type='text'][title!=''], textarea[title!=''], input[type='password'][title!=''][password='password']")
			.each(function(){
				var thisInputObj = $(this);
				$(this.form).submit(function(){
					if(thisInputObj.val() == thisInputObj.attr('title')) thisInputObj.val('');
				})
			})
			.bind('focusin focusout keydown',function(event){
				if(event.type=='focusout') 
				{
					if($(this).val() == '') 
					{ 
						if ( $(this).attr('password') == 'password' && $(this).attr('title')) {
							if(($.browser.version >= 9.0 && $.browser.msie) || !$.browser.msie ) {
								if( $(this).attr("id")){
									document.getElementById($(this).attr("id")).type = "text";
								}else{
									document.getElementsByName($(this).attr("name")).type = "text";
								}
								$(this).val($(this).attr('title')).addClass('input-box-default-text');
							} 
						}else{
							$(this).val($(this).attr('title')).addClass('input-box-default-text');
						}
					}
				}
				if(event.type=='focusin' || event.type=='keydown')
				{
					if($(this).val() == $(this).attr('title') ){
						if ( $(this).attr('password') == 'password' && $(this).attr('title')) {
							if(($.browser.version >= 9.0 && $.browser.msie) || !$.browser.msie ) {
								if( $(this).attr("id")){
									document.getElementById($(this).attr("id")).type = "password";
								}else{
									document.getElementsByName($(this).attr("name")).type = "password";
								}
								$(this).val('');
							} 
						}else{
							$(this).val('');
						}
					}
					$(this).removeClass('input-box-default-text');
				}
			}).focusout();
		},300);
		
		setTimeout(function(){	
			$("input[type='password'][title][password!='password']")
			.each(function(){
				var thisInputObj = $(this);
				if(!thisInputObj.attr('uniqCloneId')){
					var uniqCloneId = uniqid();
					var thisCloneObj = $("<input type='text' />");
					thisCloneObj
					.attr('style',$(this).attr('style'))
					.attr('size',$(this).attr('size'))
					.attr('class',$(this).attr('class'))
					.addClass('input-box-default-text');
					//var thisCloneObj = $(this).clone().attr({'type':'text','name':'','id':uniqCloneId});
					if($(this).attr('tabIndex')) thisCloneObj.attr('tabIndex',$(this).attr('tabIndex'));
					$(this).attr('uniqCloneId',uniqCloneId);
					$(thisCloneObj).attr({'value':$(this).attr('title'),'title':''});
					
					thisCloneObj.bind('focus',function(){
						thisInputObj.show().focus();
						$(this).hide();
					});
					$(this).hide().after(thisCloneObj);
					
					$(this).bind('focusout',function(event){
						if($(this).val() == '') 
						{ 
							$(this).hide();
							thisCloneObj.show();
						}else{
							$(this).show();
							thisCloneObj.hide();
						}
					}).focusout();
				}
			})
			
		},300);
	}
}

function comma(x)
{
	var temp = "";
	var x = String(uncomma(x));

	num_len = x.length;
	co = 3;
	while (num_len>0){
		num_len = num_len - co;
		if (num_len<0){
			co = num_len + co;
			num_len = 0;
		}
		temp = ","+x.substr(num_len,co)+temp;
	}
	return temp.substr(1);
}

function uncomma(x)
{
	var reg = /(,)*/g;
	x = parseInt(String(x).replace(reg,""));
	return (isNaN(x)) ? 0 : x;
}

function num(val){
	if(!val || val=='' || isNaN(val)){
		return 0;
	}else{
		return parseInt(val);
	}
}

/* 카테고리 가져오기*/
function category_select_load(preSelectName,selectName,code,callbackFunction){
	$("select[name='" + selectName + "'] option").each(function(){ if( $(this).val() ) $(this).remove(); });
	if(preSelectName && !code) return;		
	$.ajax({
		type: "GET",
		url: "/common/category2json",
		data: "categoryCode=" + code,
		dataType: 'json',
		success: function(result){			
			var options = "";			
			for(var i=0;i<result.length;i++) options += "<option value='"+result[i].category_code+"'>"+result[i].title+"</option>";
			$("select[name='" + selectName + "']").append(options);
			if(options){
				$("select[name='" + selectName + "']").show();
			}
			if(preSelectName){
				$("select[name='" + preSelectName + "'] option[value='"+code+"']").attr("selected",true);
			}

			if(callbackFunction){
				callbackFunction(result);
			}
		}
	});
}


function loadingStart(target,customOptions){ 
	var options = {segments: 12, width: 5.5, space: 6, length: 13, color: '#ffffff', speed: 1.5};
	
	if(customOptions != undefined){
		for(var i in customOptions){
			options[i] = customOptions[i];
		}
	}
	
	if(!target) var target = "#ajaxLoadingLayer";
	$(target).css({'opacity':'0.5'}).activity(options).show();
	$(target).find("div").eq(0).css({'z-index':'1000'});//mobile loadingimg 
}

function loadingStop(target,noHidden){
	if(!target) var target = "#ajaxLoadingLayer";
	if(noHidden){
		$(target).css({'opacity':'1'}).activity(false);
	}else{
		$(target).hide().activity(false);
	}
}


//모바일submit 체크
function loadingstartsubmit(){
	loadingStart("body",{segments: 12, width: 25.5, space: 6, length: 23, color: '#000000', speed: 1.5, valign: 'bottom',padding: '1'});
}

/* 다이얼로그 띄우기 (타이틀, 레이어아이디, 옵션) */
function openDialog(title, layerId, customOptions, callback){

	if((typeof layerId) != 'string') var layerSelector = layerId;
	else if(layerId.substring(0,1)=='#' || layerId.substring(0,1)=='.' || (typeof layerId) != 'string') var layerSelector = layerId;
	else var layerSelector = "#"+layerId;
	
	var options = {
		"zIndex" : 10000,
//		"show" : "fade",
//		"hide" : "fade",
		"modal" : true,
		"resizable" : false,
		"draggable" : true,
		"noClose" : false
	};

	if(customOptions != undefined){
		for(var i in customOptions){
			options[i] = customOptions[i];
		}
	}
	
	options['title'] = title;

	if(callback){
		$(layerSelector).dialog({
			"modal" : options['modal'],
			"close" : function(){
				callback();
			}
		});
	}

	$(function(){
		if(customOptions['autoOpen']==false){
			
			$(layerSelector)
			.dialog({"autoOpen" : false})
			.dialog("option", options);
			
		}else{
			$(layerSelector)
			.dialog({"autoOpen" : false})
			.dialog("option", options)
			.dialog("open")
			.focus();
		}

		if(parseInt($(layerSelector).closest('.ui-dialog').css('top'))<$(document).scrollTop()){
			$(layerSelector).closest('.ui-dialog').css('top',$(document).scrollTop()+'px');
		}

		if(options['noClose']==false){
			$(".ui-dialog-titlebar-close",$(layerSelector).closest(".ui-dialog")).show();
			$(".ui-dialog-titlebar-close").bind("click",function(){
				if(layerId=='recommendDisplayGoodsSelect'){
					parent.$("body").css("overflow-y","auto");
					$(this).dialog("close");
				}else{
					$("body").css("overflow-y","auto");
					$(this).dialog("close");
				}
			});
		}else{
			$(".ui-dialog-titlebar-close",$(layerSelector).closest(".ui-dialog")).hide();
			$(layerSelector).dialog("option","close",function(){
				if(options['noClose'])	$(layerSelector).dialog("open").focus();
			});
		}
	});
	
}

/* 다이얼로그 닫기 */
function closeDialog(layerId){
	if((typeof layerId) != 'string') var layerSelector = layerId;
	else if(layerId.substring(0,1)=='#' || layerId.substring(0,1)=='.' || (typeof layerId) != 'string') var layerSelector = layerId;
	else var layerSelector = "#"+layerId;
	$(layerSelector).dialog("close");
}

/* 다이얼로그 팝업창띄우기(미리 div영역을 만들어놓을필요가 없음) */
function openDialogPopup(title, layerId, customOptions, onloadCallback){
	closeDialog(layerId);
	
	var layerSelector = "#"+layerId;
	
	if($(layerSelector).length==0){
		layerSelector = $("<div id='"+layerId+"'></div>").appendTo('body');
	}
	
	if(customOptions['url']){
		$.ajax({
			'cache' : false,
			'url' : customOptions['url'],
			'type' : customOptions['type']?customOptions['type']:'get',
			'data' : customOptions['data']?customOptions['data']:{},
			'success' : function(result){
				$(layerSelector).html(result);
				openDialog(title, layerId, customOptions);
				if(onloadCallback){
					onloadCallback();
				}
			}		
		});
	}
	
}

/* Alert 다이얼로그 */
/* 사용예
	openDialogAlert("저장했습니다.",400,140,function(){ 
		alert("확인을 눌렀습니다."); 
	});
*/
function openDialogAlert(msg,width,height,callback,customOptions){

	var options = {
		"hideButton"	: false,
		"modal" 		: true
	};
	
	if(customOptions != undefined){
		for(var i in customOptions){
			options[i] = customOptions[i];
		}
	}
	
	if(width) options['width'] = width;
	if(height) options['height'] = height;

	document.getElementById('openDialogLayerMsg').innerHTML = msg;
	openDialog("알림 <span class='desc'>알림 정보를 표시합니다.</span>", "openDialogLayer", options);
	
	
	$("#openDialogLayer").dialog({
		"modal" : options['modal'],
		"close" : function(){
			$("#openDialogLayerBtns").remove();
			if(callback)	callback();
		}
	});

	$("#openDialogLayerBtns").remove();
	if(!options.hideButton){
		$("#openDialogLayer").append("<div id='openDialogLayerBtns' align='center' style='padding-top:15px'><span class='btn medium'><input type='button' value='확인' onclick=\"$('#openDialogLayer').dialog('close');\" /></span></div>");
	}
	$("#openDialogLayerBtns input:eq(0)").focus();

}

function openDialogAlerttitle(title, msg,width,height,callback,customOptions){

	var options = {
		"hideButton"	: false,
		"modal" 		: true
	};

	if(customOptions != undefined){
		for(var i in customOptions){
			options[i] = customOptions[i];
		}
	}

	options['width'] = width;
	options['height'] = height;

	document.getElementById('openDialogLayerMsg').innerHTML = msg;
	openDialog(title, "openDialogLayer", options);

	if(callback){
		$("#openDialogLayer").dialog({
			"modal" : options['modal'],
			"close" : function(){
				callback();
			}
		});
	}

	$("#openDialogLayerBtns").remove();
	if(!options.hideButton){
		$("#openDialogLayer").append("<div id='openDialogLayerBtns' align='center' style='padding-top:15px'><span class='btn medium'><input type='button' value='확인' onclick=\"$('#openDialogLayer').dialog('close');\" /></span></div>");
	}
	$("#openDialogLayerBtns input:eq(0)").focus();

}

/* Confirm 다이얼로그 */
/* 사용예
	openDialogConfirm('저장하시겠습니까?',400,140,function(){
		alert('예를 눌렀습니다.');
	},function(){
		alert('아니오를 눌렀습니다.');
	});

*/

function openDialogConfirm(msg,width,height,yesCallback,noCallback){
	var choicedYes = false;
	
	document.getElementById('openDialogLayerMsg').innerHTML = msg;
	openDialog("알림 <span class='desc'>알림 정보를 표시합니다.</span>", "openDialogLayer", {"width":width,"height":height});		
	
	$("#openDialogLayer").dialog({
			close : function(){
				if(noCallback && !choicedYes){
					noCallback();
				}
			}
	});

	$("#openDialogLayerBtns").remove();
	$("#openDialogLayer").append("<div id='openDialogLayerBtns' align='center' style='padding-top:15px'><span class='btn medium'><input type='button' value='예' id='openDialogLayerConfirmYesBtn' /></span> <span class='btn medium'><input type='button' value='아니오' id='openDialogLayerConfirmNoBtn' /></span></div>");
	$("#openDialogLayerBtns input:eq(0)").focus();
	
	document.getElementById('openDialogLayerConfirmYesBtn').onclick = function(){
		choicedYes = true;
		$("#openDialogLayer").dialog("close");
		if(yesCallback) yesCallback();
	};
	document.getElementById('openDialogLayerConfirmNoBtn').onclick = function(){
		choicedYes = false;
		$("#openDialogLayer").dialog("close");
	};
}

function openDialogConfirmtitle(title,msg,width,height,yesCallback,noCallback){
	var choicedYes = false;
	
	document.getElementById('openDialogLayerMsg').innerHTML = msg;
	openDialog(title, "openDialogLayer", {"width":width,"height":height});		
	
	$("#openDialogLayer").dialog({
			close : function(){
				if(noCallback && !choicedYes){
					noCallback();
				}
			}
	});

	$("#openDialogLayerBtns").remove();
	$("#openDialogLayer").append("<div id='openDialogLayerBtns' align='center' style='padding-top:15px'><span class='btn medium'><input type='button' value='예' id='openDialogLayerConfirmYesBtn' /></span> <span class='btn medium'><input type='button' value='아니오' id='openDialogLayerConfirmNoBtn' /></span></div>");
	$("#openDialogLayerBtns input:eq(0)").focus();
	
	document.getElementById('openDialogLayerConfirmYesBtn').onclick = function(){
		choicedYes = true;
		$("#openDialogLayer").dialog("close");
		if(yesCallback) yesCallback();
	};
	document.getElementById('openDialogLayerConfirmNoBtn').onclick = function(){
		choicedYes = false;
		$("#openDialogLayer").dialog("close");
	};
}


function openSearchSet(id, title){
	var html = "<div class=\"search-form-container\" style='padding:10px;'>";
	html += "<form id='setForm'>";
	html += $("#"+id).html();
	html += "</form>";
	html += "</div>";
	html += "<div style=\"padding-top:10px;\" class=\"center\">";
	html += "<span class=\"btn large black\">";
	html += "<button type=\"submit\" class=\"setBtn\" onclick='settingForm();'>저장하기</button>";
	html += "</span>";
	html += "</div>";
	$('#'+id).html(html);		
	openDialog(title+" <span class='desc'>"+title+"을 합니다.</span>", id, {"width":"900","height":"300"});
}

function JSONtoString(object,quote) {
    var results = [];
    
    if(!quote) quote = '"';
    
    for (var property in object) {
    	var value = object[property];
    	if (value){
    		if(typeof value == "string") value = quote+value+quote;
    		results.push(quote+property.toString()+quote + ':' + value);
    	}
    }
            
    return '{' + results.join(', ') + '}';
}

/**
 * 새창으로 팝업을 띄웁니다
 * popup('zoom.php?seq=7',750,550)
 */
function popup(src,width,height) {
	var scrollbars = "1";
	var resizable = "no";
	if (typeof(arguments[3])!="undefined") scrollbars = arguments[3];
	if (arguments[4]) resizable = "yes";
	window.open(src,'','width='+width+',height='+height+',scrollbars='+scrollbars+',toolbar=no,status=no,resizable='+resizable+',menubar=no');
}


function sprintf () {
	// Return a formatted string  
	// 
	// version: 1103.1210
	// discuss at: http://phpjs.org/functions/sprintf    // +   original by: Ash Searle (http://hexmen.com/blog/)
	// + namespaced by: Michael White (http://getsprink.com)
	// +    tweaked by: Jack
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +      input by: Paulo Freitas    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +      input by: Brett Zamir (http://brett-zamir.me)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// *     example 1: sprintf("%01.2f", 123.1);
	// *     returns 1: 123.10    // *     example 2: sprintf("[%10s]", 'monkey');
	// *     returns 2: '[    monkey]'
	// *     example 3: sprintf("[%'#10s]", 'monkey');
	// *     returns 3: '[####monkey]'
	var regex = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuidfegEG])/g;
	var a = arguments,
		i = 0,
		format = a[i++];

	// pad()
	var pad = function (str, len, chr, leftJustify) {
		if (!chr) {
			chr = ' ';
		}
		var padding = (str.length >= len) ? '' : Array(1 + len - str.length >>> 0).join(chr);
		return leftJustify ? str + padding : padding + str;
	};

	// justify()
	var justify = function (value, prefix, leftJustify, minWidth, zeroPad, customPadChar) {
		var diff = minWidth - value.length;
		if (diff > 0) {
			if (leftJustify || !zeroPad) {
				value = pad(value, minWidth, customPadChar, leftJustify);
			} else {
				value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
			}
		}
		return value;
	}; 
	// formatBaseX()
	var formatBaseX = function (value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
		// Note: casts negative numbers to positive ones
		var number = value >>> 0;        prefix = prefix && number && {
			'2': '0b',
			'8': '0',
			'16': '0x'
		}[base] || '';        value = prefix + pad(number.toString(base), precision || 0, '0', false);
		return justify(value, prefix, leftJustify, minWidth, zeroPad);
	};

	// formatString()
	var formatString = function (value, leftJustify, minWidth, precision, zeroPad, customPadChar) {
		if (precision != null) {
			value = value.slice(0, precision);
		}
		return justify(value, '', leftJustify, minWidth, zeroPad, customPadChar);
	};

	// doFormat()
	var doFormat = function (substring, valueIndex, flags, minWidth, _, precision, type) {
		var number;
		var prefix;
		var method;
		var textTransform;
		var value;
		 if (substring == '%%') { return '%'; }

		// parse flags
		var leftJustify = false,
			positivePrefix = '',
			zeroPad = false,
			prefixBaseX = false,
			customPadChar = ' ';
		var flagsl = flags.length;
		for (var j = 0; flags && j < flagsl; j++) {
			switch (flags.charAt(j)) {
				case ' ':
					positivePrefix = ' ';
					break;
				case '+':
					positivePrefix = '+';
					break;
				case '-':
					leftJustify = true;
					break;
				case "'":
					customPadChar = flags.charAt(j + 1);
					break;
				case '0':
					zeroPad = true;
					break;
				case '#':
					prefixBaseX = true;
					break;
			}
		}

		// parameters may be null, undefined, empty-string or real valued
		// we want to ignore null, undefined and empty-string values
		if (!minWidth) {
			minWidth = 0;
		} else if (minWidth == '*') {
			minWidth = +a[i++];
		} else if (minWidth.charAt(0) == '*') {
			minWidth = +a[minWidth.slice(1, -1)];
		} else {
			minWidth = +minWidth;
		} 
		// Note: undocumented perl feature:
		if (minWidth < 0) {
			minWidth = -minWidth;
			leftJustify = true;
		}

		if (!isFinite(minWidth)) {
			throw new Error('sprintf: (minimum-)width must be finite');
		} 
		if (!precision) {
			precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type == 'd') ? 0 : undefined;
		} else if (precision == '*') {
			precision = +a[i++];
		} else if (precision.charAt(0) == '*') {
			precision = +a[precision.slice(1, -1)];
		} else {
			precision = +precision;
		} 
		// grab value using valueIndex if required?
		value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];

		switch (type) {
			case 's':
				return formatString(String(value), leftJustify, minWidth, precision, zeroPad, customPadChar);
			case 'c':
				return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
			case 'b':
				return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'o':
				return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'x':
				return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'X':
				return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad).toUpperCase();
			case 'u':
				return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'i':
			case 'd':
				number = (+value) | 0;
				prefix = number < 0 ? '-' : positivePrefix;
				value = prefix + pad(String(Math.abs(number)), precision, '0', false);
				return justify(value, prefix, leftJustify, minWidth, zeroPad);
			case 'e':
			case 'E':
			case 'f':
			case 'F':
			case 'g':
			case 'G':
				number = +value;
				prefix = number < 0 ? '-' : positivePrefix;
				method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
				textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
				value = prefix + Math.abs(number)[method](precision);
				return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
			default:
				return substring;
		}
	};
	 
    return format.replace(regex, doFormat);
}




/**
 * 현재 시각을 Time 형식으로 리턴
 */
function getCurrentTime(date) {
	return toTimeString(new Date(date));
}

/**
 * 자바스크립트 Date 객체를 Time 스트링으로 변환
 * parameter date: JavaScript Date Object
 */
function toTimeString(date) {
	var year  = date.getFullYear();
	var month = date.getMonth() + 1; // 1월=0,12월=11이므로 1 더함
	var day   = date.getDate();

	if (("" + month).length == 1) {month = "0" + month;}
	if (("" + day).length   == 1) {day   = "0" + day;}

	return ("" + year + month + day)
}
/**
 * 현재 年을 YYYY형식으로 리턴
 */
function getYear(date) {
	return getCurrentTime(date).substr(0,4);
}

/**
 * 현재 月을 MM형식으로 리턴
 */
function getMonth(date) {
	return getCurrentTime(date).substr(4,2);
}

/**
 * 현재 日을 DD형식으로 리턴
 */
function getDay(date) {
	return getCurrentTime(date).substr(6,2);
}
//
function getDate(day) {
	var d = new Date();
	var dt = d - day*24*60*60*1000;
	return getYear(dt) + '-' + getMonth(dt) + '-' + getDay(dt);
}


/**
 * 콤마 붙이기 함수
 * @param value int
 */
function setComma(nStr) {
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}




/**
 * 신규생성 다이얼로그 창을 띄운다.
 * <pre>
 * 1. createElementContainer 함수를 이용하여 매번 div 태그를 입력하지 않고 다이얼로그 생성시 자동으로 생성한다.
 * 2. refreshTable 함수를 이용하여 다이얼로그 내용 부분을 불러온다.
 * </pre>
 * @param string url 폼화면 주소
 * @param int width 가로 사이즈
 * @param int height 세로 사이즈
 * @param string title 제목
 * @param string btn_yn 'false'이면 닫기버튼만 나타낸다.
 */
function addFormDialog(url, width, height, title, btn_yn) {
	newcreateElementContainer(title);
	newrefreshTable(url);

	if (btn_yn != 'false') {
		var buttons = {
			'닫기': function() {
				$(this).dialog('close');
			},
			'저장하기': function() {
				$('#form1').submit();
			}
		}
	} 
	else if (btn_yn == 'close') {
		var buttons =  {
			'닫기': function() {
				$(this).dialog('close');
			}
		}
	}
	if( height>0 ) {
		$('#dlg').dialog({
			bgiframe: true,
			autoOpen: false,
			width: width,
			height: height,
			resizable: false,
			draggable: false,
			modal: true,
			overlay: {
				backgroundColor: '#000000',
				opacity: 0.8
			},
			buttons: buttons,
			open: function() { 
					$("#ui-datepicker-div").css("z-index",
					$(this).parents(".ui-dialog").css("z-index")+1);
			}
		}).dialog('open');
	}else{
		$('#dlg').dialog({
			bgiframe: true,
			autoOpen: false,
			width: width, 
			resizable: false,
			draggable: false,
			modal: true,
			overlay: {
				backgroundColor: '#000000',
				opacity: 0.8
			}, 
			buttons: buttons,
			open: function() { 
					$("#ui-datepicker-div").css("z-index",
					$(this).parents(".ui-dialog").css("z-index")+1);
			}
		}).dialog('open');
	}
	return false;
}

function newcreateElementContainer(title) {
	var dlg_title = title ? title : '등록 폼';
	var el = '<div id="dlg" title="' + dlg_title + '"   ><div id="dlg_content" ></div></div>';
	$('#dlg').remove();
	$(el).appendTo('body');
}

function newrefreshTable(url) {
	$.get(url, {}, function(data, textStatus) {
		$('#dlg_content').html(data);
	});
}



function goTwitter(msg,url) {
	var href = "http://twitter.com/home?status=" + encodeURIComponent(msg) + " " + encodeURIComponent(url);
	var a = window.open(href, 'twitter', '');
	if ( a ) {
		a.focus();
	}
}
function goMe2Day(msg,url,tag) {
	var href = "http://me2day.net/posts/new?new_post[body]=" + encodeURIComponent(msg) + " " + encodeURIComponent(url) + "&new_post[tags]=" + encodeURIComponent(tag);
	var a = window.open(href, 'me2Day', '');
	if ( a ) {
		a.focus();
	}
}
function goFaceBook(msg,url) {
	var href = "https://www.facebook.com/sharer/sharer.php?u=" + url + "&t=" + encodeURIComponent(msg);
	var a = window.open(href, 'facebook', '');
	if ( a ) {
		a.focus();
	}
}
function goCyWorld(no) {
	var href = "http://api.cyworld.com/openscrap/post/v1/?xu=http://ticketmonster.co.kr/html/cyworldConnectToXml.php?no=" + no +"&sid=suGPZc14uNs4a4oaJbVPWkDSZCwgY8Xe";
	var a = window.open(href, 'cyworldpost', 'width=450,height=410');
	if ( a ) {
		a.focus();
	}
}
function goYozmDaum(link,prefix,parameter) {
	var href = "http://yozm.daum.net/api/popup/post?sourceid=&link=" + encodeURIComponent(link) + "&prefix=" + encodeURIComponent(prefix) + "&parameter=" + encodeURIComponent(parameter);
	var a = window.open(href, 'yozmSend', 'width=466, height=356');
	if ( a ) {
		a.focus();
	}
}

function snsWin(sns, enc_tit, enc_sbj, enc_tag, enc_url, isMobile,imgurl,imgwidth,imgheight)
{  
	//짧은주소체크
	var sourturl = enc_url;
	$.ajax({url: '/common/get_shortURL?url='+enc_url,global:false,async: false,dataType: 'json',success: function(data) {if(data) sourturl = data;}});
	
	var snsset = new Array();
	if(isMobile){
		snsset['tw'] = 'http://mobile.twitter.com/home/?status=' + enc_sbj + '+++' + sourturl;
		snsset['me'] = 'http://me2day.net/plugins/mobile_post/new?new_post[body]=' + enc_sbj + '+++["'+enc_tit+'":' + sourturl + '+]&new_post[tags]='+enc_tag;
		snsset['my']		= 'https://m.mypeople.daum.net/mypeople/mweb/share.do?source_id=' + enc_tit + '&link=' + sourturl + '&prefix=' + enc_sbj;
	}else{
		snsset['tw'] = 'http://twitter.com/home/?status=' + enc_sbj + '+++' + sourturl;
		snsset['me'] = 'http://me2day.net/posts/new?new_post[body]=' + enc_sbj + '+++["'+enc_tit+'":' + sourturl + '+]&new_post[tags]='+enc_tag+'&redirect_url='+sourturl;
		snsset['my']		= 'https://mypeople.daum.net/mypeople/web/share.do?source_id=' + enc_tit + '&link=' + sourturl + '&prefix=' + enc_sbj;
	}

	snsset['ka'] = 'kakaolink://sendurl?msg=' + enc_sbj + '&url=' + sourturl + '&appid=' + document.domain + '&appver=4.0&type=link&appname=' + enc_tit + '&apiver=2.0';
	snsset['kaapi']		= "api";
	snsset['kakaostory']	= 'storylink://posting?post=' + enc_sbj + '&appid=' + document.domain + '&appver=4.0&apiver=1.0&appname=' + enc_tit + '&urlinfo=' + sourturl + '';
	snsset['go']		= 'https://plus.google.com/share?url=' + sourturl;
	snsset['fa'] = 'https://www.facebook.com/sharer/sharer.php?u=' + sourturl + '&t=' + enc_sbj;
	snsset['yo'] = "http://yozm.daum.net/api/popup/prePost?link=" + sourturl + "&prefix=" + enc_sbj + "&parameter=" + sourturl;

	snsset['pi'] = '';//이미지전용
	snsset['na'] = 'http://api.nateon.nate.com/web/note/SendNote.do?msg='+enc_sbj+ '&lurl='+sourturl;
	snsset['cy'] = 'http://csp.cyworld.com/bi/bi_recommend_pop.php?title='+enc_tit+ '&url='+sourturl+ '&thumbnail='+imgurl+ '&summary='+enc_sbj;
	snsset['line']		= 'http://line.me/R/msg/text/?' + enc_sbj + '%0D%0A' + sourturl; 
	
	if( snsset[sns] ) {  
		if(sns == 'ka'){//app link copy
			executeURLLink(enc_sbj, enc_tit, sourturl,isMobile);
		}else if(sns == 'kaapi'){ 
			sendKakaotalk(enc_sbj, enc_tit, enc_url,imgurl,imgwidth,imgheight); 
		}else if(sns == 'kakaostory'){ 
			sendKakaostorynew(enc_sbj, enc_tit, sourturl,isMobile,imgurl);
		}else{
			var a = window.open(snsset[sns], "SnsWinUp"+sns);
			if ( a ) {
				a.focus();
			}
		}
	}
}

//카카오스토리연동
function sendKakaostorynew(enc_sbj, enc_tit, enc_url,isMobile,imgurl)
{   
	var appid = 'http://' + document.domain;
	kakaostorynew.link("story").send({   
        post : enc_url,
        appid : appid,
        appver : "1.0",
        appname : enc_tit,
        urlinfo : JSON.stringify({title:enc_sbj, imageurl:[imgurl], type:"article"})
    });
}
 

//카카오톡 연결
function sendKakaotalk(enc_sbj, enc_tit, enc_url,imgurl,imgwidth,imgheight)
{ 
	if(imgurl && imgwidth >= 70 && imgheight >= 70) {
		Kakao.Link.createTalkLinkButton({
		container: '.kakao-link-btn', 
		label: enc_tit,
		image: {
		src: imgurl,
		width: imgwidth,
		height: imgheight
		},
		webButton: {
		text: enc_sbj,
		url: enc_url
		}
		});
	}else{
		Kakao.Link.createTalkLinkButton({
		container: '.kakao-link-btn', 
		label: enc_tit, 
		webButton: {
		text: enc_sbj,
		url: enc_url
		}
		});
	}
	// 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다. 개발자 사이트에 등록한 웹사이트 중 첫번째 URL
}
 
function executeURLLink(enc_sbj, enc_tit, enc_url,isMobile,imgurl)
{ 
	if ( !isMobile ) {
		alert( 'PC에서 접속한 경우에는 작동하지 않습니다.\n\n카카오톡이 설치된 아이폰/안드로이드폰등으로 접속하는 경우에만 사용 가능합니다.' );
		return;
	}
    /* 
    msg, url, appid, appname은 실제 서비스에서 사용하는 정보로 업데이트되어야 합니다. 
    */
	var appid = 'http://' + document.domain;
    kakao.link("talk").send({
        msg : enc_sbj,
        url : enc_url,
        appid : appid,
        appver : "2.0",
        appname : enc_tit,
        type : "link"
    });

}

//데모경고창
function servicedemoalert(e) { 
	if( e.name == "use_f"  ||e.name == "use_t" ) {
		$("input[name="+e.name+"]").attr("checked",'checked');
	}else if( e.type == "checkbox" ) {
		$("input[name="+e.name+"]").attr("checked",''); 
		$("input[name="+e.name+"]").removeAttr("checked");
	}else if( e.type == "file" ) {
		$("input[name="+e.name+"]").attr("disabled","disabled");
	}
	
	$.ajax({
		type: "get",
		url: "/admin/main/main_demo",
		success: function(result){
			$("#main_demo").html(result);
			openDialog("제한 기능 안내", "main_demo", {"width":"700","height":"500","show" : "fade","hide" : "fade"});
		}
	});

	//alert( "type:" + e.type + "/name:" + e.name );
	//alert( '체험 사이트에서는 해당 기능을 제공하지 않습니다.' );
	return;
}


//무료몰업그레이드
function serviceUpgrade(){
	window.open('http://firstmall.kr/ec_hosting/addservice/frelocate.php?p=upgrade&s=P_FREE','','');
}
//무료몰 > 게시판추가
function serviceBoardAdd(){
	//window.open('http://customer.gabia.com/1to1/1to1.php','','');
	$.get('board_payment', function(data) {
		$('#boardPaymentPopup').html(data);
		openDialog("게시판 추가 신청", "boardPaymentPopup", {"width":"800","height":"650"});
	});
}

function htmlspecialchars(str) {
	if (typeof(str) == "string") {
		str = str.replace(/&/g, "&amp;"); /* must do &amp; first */
		str = str.replace(/"/g, "&quot;");
		str = str.replace(/'/g, "&#039;");
		str = str.replace(/</g, "&lt;");
		str = str.replace(/>/g, "&gt;");
	}
	return str;
}

//flash(파일주소, 가로, 세로, 배경색, 윈도우모드, 변수, 경로)
function flash(url,w,h,bg,win,vars,base){
	var s=
	"<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0' width='"+w+"' height='"+h+"' align='middle'>"+
	"<param name='allowScriptAccess' value='always' />"+
	"<param name='movie' value='"+url+"' />"+
	"<param name='wmode' value='transparent' />"+
	"<param name='menu' value='false' />"+
	"<param name='quality' value='high' />"+
	"<param name='FlashVars' value='"+vars+"' />"+
	"<param name='bgcolor' value='"+bg+"' />"+
	"<param name='base' value='"+base+"' />"+
	"<embed src='"+url+"' base='"+base+"' wmode='transparent' menu='false' quality='high' bgcolor='"+bg+"' width='"+w+"' height='"+h+"' align='middle' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />"+
	"</object>";
	document.write(s);
}

function isNumber(s) {
	s += ''; // 문자열로 변환
	s = s.replace(/^\s*|\s*$/g, ''); // 좌우 공백 제거
	if (s == '' || isNaN(s)) return false;
	return true;
}

function uniqid (prefix, more_entropy) {
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +    revised by: Kankrelune (http://www.webfaktory.info/)
  // %        note 1: Uses an internal counter (in php_js global) to avoid collision
  // *     example 1: uniqid();
  // *     returns 1: 'a30285b160c14'
  // *     example 2: uniqid('foo');
  // *     returns 2: 'fooa30285b1cd361'
  // *     example 3: uniqid('bar', true);
  // *     returns 3: 'bara20285b23dfd1.31879087'
  if (typeof prefix == 'undefined') {
    prefix = "";
  }

  var retId;
  var formatSeed = function (seed, reqWidth) {
    seed = parseInt(seed, 10).toString(16); // to hex str
    if (reqWidth < seed.length) { // so long we split
      return seed.slice(seed.length - reqWidth);
    }
    if (reqWidth > seed.length) { // so short we pad
      return Array(1 + (reqWidth - seed.length)).join('0') + seed;
    }
    return seed;
  };

  // BEGIN REDUNDANT
  if (!this.php_js) {
    this.php_js = {};
  }
  // END REDUNDANT
  if (!this.php_js.uniqidSeed) { // init seed with big random int
    this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
  }
  this.php_js.uniqidSeed++;

  retId = prefix; // start with prefix, add current milliseconds hex string
  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
  if (more_entropy) {
    // for more entropy we add a float lower to 10
    retId += (Math.random() * 10).toFixed(8).toString();
  }

  return retId;
}


//정식 오픈그라피>바로구매시 적용
function getfbopengraph(gdseq, type, urldomain, id)
{
	
	 if (document.location.protocol == "https:") {
		var url = 'https://'+urldomain+'/sns_process/fbopengraph';
	 }else{
		var url = 'http://'+urldomain+'/sns_process/fbopengraph';
	 }
	$.getJSON(url + "?no="+gdseq+"&id="+id+"&type="+type+"&jsoncallback=?"); 
}

//페이스북>me/feed 글남기기
function getfbmefeed(boardseq, type, urldomain, boardid)
{	
	 if (document.location.protocol == "https:") {
		var url = 'https://'+urldomain+'/sns_process/fbmefeed';
	 }else{
		var url = 'http://'+urldomain+'/sns_process/fbmefeed';
	 }
	$.getJSON(url + "?no="+boardseq+"&id="+boardid+"&type="+type+"&jsoncallback=?"); 
}


function chkByte(str){
	var cnt = 0;
	for(i=0;i<str.length;i++) {
		cnt += str.charCodeAt(i) > 128 ? 2 : 1;
		if(str.charCodeAt(i)==10) cnt++;
	}
	return cnt;
}

//단독이벤트 남은 시간
function showClockTime(numberType, year, month, day, hour, min, second, dayDiv, hourDiv, minDiv, secondDiv, goods_seq){
	
	var close_date = new Date(year, month-1, day, hour, min, second);
	var close_timestamp = Math.floor(close_date.getTime()/1000);
	var now_timestamp = Math.floor((new Date()).getTime()/1000);
	var remind_timestamp = close_timestamp - now_timestamp;

	if(remind_timestamp<0) {
		return;
	}
	
	var remind_days = Math.floor(remind_timestamp/86400);
	var remind_hours = Math.floor((remind_timestamp - (86400 * remind_days))/3600);
	var remind_minutes = Math.floor((remind_timestamp - ((86400 * remind_days) + (3600 * remind_hours))) / 60);
	var remind_seconds = remind_timestamp%60;
	var day="";
	var hour="", min="", second="";

	if(numberType == "img"){
		if (remind_days > 0){ 
			day = "";
			for(var i=0;i<remind_days.toString().length;i++){
				day += "<img src='/data/icon/goods/social_no"+remind_days.toString().substring(i,i+1)+".png'>";
			}
			$('.'+dayDiv).html(day);
		}else{
			day = "<img src='/data/icon/goods/social_no0.png'>";
			$('.'+dayDiv).html(day);
		}
		
		// 시간처리
		if (remind_hours > -1){
			if (remind_hours < 10){
				hour = "<img src='/data/icon/goods/social_no0.png'><img src='/data/icon/goods/social_no"+ Math.floor(remind_hours % 10) +".png'>";
			} else {
				hour = "<img src='/data/icon/goods/social_no"+Math.floor(remind_hours / 10)+".png'>";
				hour += "<img src='/data/icon/goods/social_no"+Math.floor(remind_hours % 10)+".png'>";
			}
			$('.'+hourDiv).html(hour);
		}

		// 분처리
		if (remind_minutes > -1){
			if (remind_minutes < 10){
				min = "<img src='/data/icon/goods/social_no0.png'><img src='/data/icon/goods/social_no"+Math.floor(remind_minutes % 10)+".png'>";
			} else {
				min = "<img src='/data/icon/goods/social_no"+Math.floor(remind_minutes / 10)+".png'>";
				min += "<img src='/data/icon/goods/social_no"+Math.floor(remind_minutes % 10)+".png'>";
			}
			$('.'+minDiv).html(min);
		}

		// 초처리
		if (remind_seconds > -1){
			if (remind_seconds < 10){
				second = "<img src='/data/icon/goods/social_no0.png'><img src='/data/icon/goods/social_no"+Math.floor(remind_seconds % 10)+".png'>";
			} else {
				second = "<img src='/data/icon/goods/social_no"+Math.floor(remind_seconds / 10)+".png'>";
				second += "<img src='/data/icon/goods/social_no"+Math.floor(remind_seconds % 10)+".png'>";
			}
			$('.'+secondDiv).html(second);
		}
	}else{
		remind_hours = strRight("0"+remind_hours, 2);
		remind_minutes = strRight("0"+remind_minutes, 2)
		remind_seconds = strRight("0"+remind_seconds, 2)

		$('#'+dayDiv).html(remind_days);
		$('#'+hourDiv).html(remind_hours);
		$('#'+minDiv).html(remind_minutes);
		$('#'+secondDiv).html(remind_seconds);
	}

	return remind_timestamp;
	
}


function strRight(Str, Num){
	if(Num <= 0){
		return "";		
	}else if(Num > String(Str).length){
		return Str;
	}else{
		var iLen = String(Str).length;
		return String(Str).substring(iLen, iLen-Num);
	}
}

function strstr (haystack, needle, bool) {
  // From: http://phpjs.org/functions
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Onno Marsman
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // *     example 1: strstr('Kevin van Zonneveld', 'van');
  // *     returns 1: 'van Zonneveld'
  // *     example 2: strstr('Kevin van Zonneveld', 'van', true);
  // *     returns 2: 'Kevin '
  // *     example 3: strstr('name@example.com', '@');
  // *     returns 3: '@example.com'
  // *     example 4: strstr('name@example.com', '@', true);
  // *     returns 4: 'name'
  var pos = 0;

  haystack += '';
  pos = haystack.indexOf(needle);
  if (pos == -1) {
    return false;
  } else {
    if (bool) {
      return haystack.substr(0, pos);
    } else {
      return haystack.slice(pos);
    }
  }
}

function rgb2hex(rgb) {
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}
 
// 배송사 배송조회 링크 추출
var delivery_url_list	= '';
function get_delivery_url_list(codecd, delivery_code, target){
	$.getJSON("/common/get_delivery_url", {}, function(result){
		delivery_url_list	= result;
		open_search_delivery(codecd, delivery_code, target);
	});
}

// 배송사 배송조회 링크 전송
function open_search_delivery(codecd, delivery_code, target){
	if	(!delivery_url_list){
		get_delivery_url_list(codecd, delivery_code, target);
	}else{
		var url	= delivery_url_list[codecd];
		if	(url && delivery_code){
			if	(url.search(/\=$/) != -1)	url	= url + delivery_code.replace(/[^0-9a-zA-Z]/g, '');

			if	(target){
				eval(target+'.location.href="'+url+'";');
			}else{
				window.open(url);
			}
		}
	}
}

// 동적 폼 생성 및 Submit
var DomSaver	= function(fDivId, fMethod, fAction, fTarget){
	this.fDivId		= fDivId;
	this.fMethod	= fMethod;
	this.fAction	= fAction;
	this.fTarget	= fTarget;
	this.sTarget	= '';
	this.saveStatus	= 'null';
};
DomSaver.prototype.setTarget	= function(obj){
	this.sTarget	= obj;
	this.saveStatus	= 'ready';
};
DomSaver.prototype.sendValue	= function(val){
	if	(this.saveStatus == 'ready'){

		// 해당 ID의 Div 객체 생성 및 초기화
		var oDiv		= document.getElementById(this.fDivId);
		oDiv.innerHTML	= '';

		// form 생성
		var oForm		= document.createElement('form');
		oForm.method	= this.fMethod;
		oForm.action	= this.fAction;
		if	(this.fTarget)	oForm.target	= this.fTarget;

		// input 생성
		var oInput		= '';
		for (var k in val){
			oInput			= '';
			oInput			= document.createElement('input');
			oInput.setAttribute('type', 'hidden');
			oInput.setAttribute('name', k);
			oInput.setAttribute('value', val[k]);
			oForm.appendChild(oInput);
		}

		// form 추가 및 submit
		oDiv.appendChild(oForm);
		oForm.submit();

		// this.saveStatus를 초기화
		this.saveStatus	= '';
	}
}; 


//쿠폰받기
function coupondownlist(gl_goods_seq,gl_request_uri)
{
	$('div#couponDownloadDialog').dialog('close'); 
	$.get('../coupon/goods_coupon_list?no='+gl_goods_seq+'&return_url='+gl_request_uri, function(data) {
		$("div#couponDownloadDialog").html(data);
	});
	openDialog("쿠폰받기", "couponDownloadDialog", {"width":700,"height":350});
}


function popup_change_pass(){
	openDialog("비밀번호 변경", "popupChangePassword", {"width":500,"height":250, "noClose":true});
}

function close_popup_change(){
	closeDialog('popupChangePassword');
}

function update_rate_checked(){
	//나중에 하기 클릭 시....비활성화
	if($("input[name='update_rate']").attr("checked")){
		$(".passwordField").attr("disabled",true);
	}else{
		$(".passwordField").attr("disabled",false);
	}

}


function passwordAfterUpdate(){
	$("input[name='password_mode']").val('after');
	$('#passUpdateForm').submit();
}