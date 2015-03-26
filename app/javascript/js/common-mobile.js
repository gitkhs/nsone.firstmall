$(function(){
	
	//상품디스플레이의 동영상클릭시 -> 동영상자동실행설정되어있어야함
	$(".goodsDisplayVideoWrap").live("click",function(e){
		$(this).find("img").addClass("hide");
		$(this).find(".thumbnailvideo").hide();
		$(this).find(".mobilethumbnailvideo").hide();
		$(this).find("iframe").removeClass("hide");
		$(this).find("embed").removeClass("hide");
	});
	
	//동영상넣기의 동영상클릭시-> 동영상자동실행설정되어있어야함
	$(".DisplayVideoWrap").live("click",function(e){
		$(this).find("img").addClass("hide");
		$(this).find(".thumbnailvideo").hide();
		$(this).find(".mobilethumbnailvideo").hide();
			$(this).find("iframe").removeClass("hide");
			$(this).find("embed").removeClass("hide"); 
	});
	
	/* 동영상넣기/상품디스플레이 동영상이미지체크 */ 
	$(".thumbnailvideo").each(function(){
		var width = ($(this).attr("width"))?$(this).attr("width"):400;
		var height = ($(this).attr("height"))?$(this).attr("height"):200;
		$(this).css({'width':width});
		$(this).css({'height':height});
	});
	
	$(".mobilethumbnailvideo").each(function(){
		var width = ($(this).attr("width"))?$(this).attr("width"):150;
		var height = ($(this).attr("height"))?$(this).attr("height"):50;
		$(this).css({'width':width});
		$(this).css({'height':height});
	});
	
	$(".datepicker").datepicker("option",{
		showOn: "focus",
		buttonImage: null,
		buttonImageOnly: false
	});
	
	/* ajax로 컨텐츠 로드(마이페이지,고객센터에서 사용) */
	$("a[mobileAjaxCall]").live('click',function(){
		var mobileAjaxCall = $(this).attr('mobileAjaxCall');
		var url = $(this).attr('href');
		var contentsObj = $("div[mobileAjaxCall='"+mobileAjaxCall+"']");
		var that = this;
		
		$("div[mobileAjaxCall]").not(contentsObj).slideUp();
		
		if($(document).data('mobileAjaxCallIng')=='1') {
			alert('실행중입니다.');
			return;
		}else{
			$(document).data('mobileAjaxCallIng','1');
			loadingStart(that,{segments: 12, steps: 3, width:2, space: 1, length: 3, color: '#030303', speed: 1.5});
		}
		
		mobileAjaxContentsLoad(url,mobileAjaxCall,function(){
			contentsObj.slideDown();
			loadingStop(that,true);
			$(document).data('mobileAjaxCallIng','');
		});
		
		return false;		
	});
	
	$(window).load(function(){
		//window.scrollTo(0,1);
	});
});

/* ajax로 컨텐츠 로드(마이페이지,고객센터에서 사용) */
function mobileAjaxContentsLoad(url,mobileAjaxCall,callbackFunction){	
	var contentsObj = $("div[mobileAjaxCall='"+mobileAjaxCall+"']");
	var urlLeft = url.split('?').length>1 ? url.split('?')[0] : url;
	var urlRight = url.split('?').length>1 ? "?"+url.split('?')[1]+"&mobileAjaxCall="+mobileAjaxCall : "?mobileAjaxCall="+mobileAjaxCall;
	
	contentsObj.load(urlLeft+urlRight,function(){
		if(callbackFunction) callbackFunction();	
	});	
}

function openDialogAlert(msg,width,height,callback){
	msg = msg.replaceAll("<br \\/>","\n");
	msg = msg.replaceAll("<br\\/>","\n");
	msg = msg.replaceAll("<br>","\n");
	msg = strip_tags(msg);
	alert(msg);
	if(callback) callback();
}

function openDialogConfirm(msg,width,height,yesCallback,noCallback){
	msg = msg.replaceAll("<br \\/>","\n");
	msg = msg.replaceAll("<br\\/>","\n");
	msg = msg.replaceAll("<br>","\n");
	msg = strip_tags(msg);
	
	if(confirm(msg)){
		if(yesCallback) yesCallback();
	}else{
		if(noCallback) noCallback();
	}
}

function openDialog2(title, layerId, customOptions, callback){

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
		"noClose" : false,
		"top" : -500
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

/* 다이얼로그 띄우기 (타이틀, 레이어아이디, 옵션) */
function openDialog(title, layerId, customOptions){
	
	if((typeof layerId) != 'string') var layerSelector = layerId;
	else if(layerId.substring(0,1)=='#' || layerId.substring(0,1)=='.' || (typeof layerId) != 'string') var layerSelector = layerId;
	else var layerSelector = "#"+layerId;

	var options = {
		"zIndex" : 10000,
//		"show" : "fade",
//		"hide" : "fade",
		"modal" : true,
		"resizable" : false,
		"draggable" : true
	};

	if(customOptions != undefined){
		for(var i in customOptions){
			options[i] = customOptions[i];
		}
	}
	
	options['title'] = title;
	options['width'] = "93%";
	if(options['height'] != undefined){
		options['height'] = options['height'] && options['height'] > $(window).height() ? $(window).height() : options['height'];
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
			.dialog("open");
		}

		$(".ui-dialog-titlebar-close").bind("click",function(){
			if(layerId=='recommendDisplayGoodsSelect'){
				parent.$("body").css("overflow-y","auto");
				$(this).dialog("close");
			}else{
				$("body").css("overflow-y","auto");
				$(this).dialog("close");
			}
		});
	});

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
