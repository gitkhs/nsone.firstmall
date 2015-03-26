$(function(){

	/* 상단 대쉬보드 스크롤처리 */
	var dashBoardObj = $("#page-title-bar");
	if(dashBoardObj[0]){
		var defaultDashBoardTop = parseInt(dashBoardObj.offset().top);

		$(window).bind('scroll resize',function(){
			var scrollTop = parseInt($(document).scrollTop());

			if(scrollTop>defaultDashBoardTop)
			{
				dashBoardObj.addClass('flyingMode');
				dashBoardObj.find("span.btn.black").removeClass('black').addClass('cyanblue');
				dashBoardObj.find(".icon-arrow-right").removeClass('icon-arrow-right').addClass('icon-arrow-right_gray');
			}
			else
			{
				dashBoardObj.removeClass('flyingMode');
				dashBoardObj.find("span.btn.cyanblue").removeClass('cyanblue').addClass('black');
				dashBoardObj.find(".icon-arrow-right_gray").removeClass('icon-arrow-right_gray').addClass('icon-arrow-right');
			}
		});
	}	

	/* 셀렉트박스 스타일 */
	$(".custom-select-box").customSelectBox();
	$(".custom-select-box-multi").customSelectBox({'multi':true});

	/* 서브 레이아웃 라운딩 처리  */
	$("div.slc-body-wrap").each(function(){

		/* 바디부분 라운드 처리 */
		$("div.slc-body",this)
		.wrap("<div class='slc-body-wtl body-height-resizing'></div>")
		.wrap("<div class='slc-body-wtr body-height-resizing'></div>")
		.wrap("<div class='slc-body-wbl body-height-resizing'></div>")
		.wrap("<div class='slc-body-wbr body-height-resizing'></div>");
	
		/* 바디부분 높이 리사이징 */
		$(window).bind('ajaxComplete resize',function(){
			if($("div.slc-body").offset().top+$("div.slc-body").outerHeight() < $(window).height()-50){
				$("div.slc-body").css('min-height',$(window).height()-$("div.slc-body").offset().top-50);
			}
		}).trigger('ajaxComplete');
		
		/* 백그라운드 변경 */
		$("body").css('background-color','#32323a');
		$("#layout-body").css('padding-bottom',0);

	});

	/* 이미지체크박스 스타일 */
	$(".imageCheckboxContainer").each(function(){
		var imageCheckboxContainer = this;
		$(".imageCheckboxItem img",this).bind('click',function(event){
			event.preventDefault();
			$(this).closest("label").children('input').click().change();
		});
		$(".imageCheckboxItem input[type='radio']",this).bind('change',function(){
			if($(this).is(":checked")){
				$(imageCheckboxContainer).find(".imageCheckboxItem").removeClass('selected').children().css('opacity',1);
				$(this).closest(".imageCheckboxItem").addClass('selected').children().css('opacity',0.8);
			}
		});

		if($(".imageCheckboxItem input[type='radio'][checked]",this).length){
			$(".imageCheckboxItem input[type='radio'][checked]",this).change();
		}else{
			$(".imageCheckboxItem:eq(0) input[type='radio']",this).attr('checked',true).change();
		}
	});

	/* body 리사이징 */
	$(".body-height-resizing").each(function(){
		var thisOffsetTop = ($(this).offset().top-$(this).parent().offset().top)+15;
		var thisHeight = $(this).outerHeight();
		var parentHeight = $(this).parent().innerHeight();

		if((thisOffsetTop+thisHeight)<parentHeight){
			$(this).css('min-height',parentHeight-thisOffsetTop);
		}
	});

	
	/* Ajax 로딩미이지 */
	$("#ajaxLoadingLayer").ajaxStart(function() {
		loadingStart(this);
	});
	$("#ajaxLoadingLayer").ajaxStop(function() {
		loadingStop(this);
	});	
	
	/* 가비아 출력 패널 (배너,팝업,공지,업그레이드) */
	$("div.gabia-pannel").each(function(){
		var pannel = this;
		
		if(!$(this).attr("noAnimation")){
			$(pannel).activity({segments: 8, width: 3.5, space: 1, length: 7, color: '#666', speed: 1.5});
		}
		
		$.ajax({
			'url' : '/selleradmin/common/getGabiaPannel',
			'data' : {'code':$(this).attr("code")},
			'global' : false,
			'success' : function(html){
				if(html){
					$(pannel).show().html(html);
					if(!$(this).attr("noAnimation")){
						$(pannel).activity(false);
					}
				}else{
					$(pannel).hide();
				}
			}
		});
	});

	/* 플러스>몰인몰 입점사공지 */
	$("div.sellernotice-pannel").each(function(){
		var pannel = this;
		
		if(!$(this).attr("noAnimation")){
			$(pannel).activity({segments: 8, width: 3.5, space: 1, length: 7, color: '#666', speed: 1.5});
		}
		
		$.ajax({
			'url' : '/selleradmin/common/getSellerNoticePannel',
			'data' : {'url':$(this).attr("url")},
			'global' : false,
			'success' : function(html){
				if(html){
					$(pannel).show().html(html);
					if(!$(this).attr("noAnimation")){
						$(pannel).activity(false);
					}
				}else{
					$(pannel).hide();
				}
			}
		});
	});
	
	/* 상단 메뉴 관련*/
	{
			
		$("#layout-header .header-gnb-container table.header-gnb td.mitem-td").each(function(){
			$(this)
			.bind('mouseenter',function(){
				$("div.submenu",this).stop(true,true).slideDown('fast');	
			})
			.bind('mouseleave',function(){
				$("div.submenu",this).stop(true,true).slideUp('fast');	
			});
		});
		
		$("#layout-header .header-gnb-container ul.header-qnb li.gnb-item").each(function(){
			$(this)
			.bind('mouseenter',function(){
				$("ul.gnb-subnb",this).stop(true,true).slideDown('fast');	
			})
			.bind('mouseleave',function(){
				$("ul.gnb-subnb",this).stop(true,true).slideUp('fast');	
			});
		});
	
	
	
	
		$("#layout-header .header-gnb-container2 table.header-gnb td.mitem-td").each(function(){
			$(this)
			.bind('mouseenter',function(){
				$("div.submenu",this).stop(true,true).slideDown('fast');	
			})
			.bind('mouseleave',function(){
				$("div.submenu",this).stop(true,true).slideUp('fast');	
			});
		});
		
		$("#layout-header .header-gnb-container2 ul.header-qnb li.gnb-item").each(function(){
			$(this)
			.bind('mouseenter',function(){
				$("ul.gnb-subnb",this).stop(true,true).slideDown('fast');	
			})
			.bind('mouseleave',function(){
				$("ul.gnb-subnb",this).stop(true,true).slideUp('fast');	
			});
		});
	
		$("#layout-header .header-snb-container ul.header-snb .hsnb-manager").click(function(){
			$(this).toggleClass('opened');
		});
	}
	
	/* 상단 검색폼 */
	$("#layout-header select.hsb-kind").each(function(){
		switch($("option:selected",this).text())
		{
			case '주문': var keywordTitle = "주문자,수령자,입금자,아이디 등"; break;
			case '출고': var keywordTitle = "아이디,주문자,수령자 등"; break;
			case '회원': var keywordTitle = "이름,아이디,이메일,연락처,주소"; break;
			case '상품': var keywordTitle = "상품명,상품코드"; break;
			default : var keywordTitle = "검색";
		}
		$("#layout-header input[name='header_search_keyword']").attr("title",keywordTitle);
	}).bind('keyup change',function(){
		var keywordObj = $("#layout-header input[name='header_search_keyword']");
		switch($("option:selected",this).text())
		{
			case '주문': 
				var keywordTitle	= "주문자,수령자,입금자,아이디 등"; 
				var action			= "/selleradmin/order/catalog";
				break;
			case '출고': 
				var keywordTitle	= "아이디,주문자,수령자 등"; 
				var action			= "/selleradmin/export/catalog";
				break;
			case '회원': 
				var keywordTitle	= "이름,아이디,이메일,연락처,주소"; 
				var action			= "/selleradmin/member/catalog";
				break;
			case '상품': 
				var keywordTitle	= "상품명,상품코드"; 
				var action			= "/selleradmin/goods/catalog";
				break;
			default : var keywordTitle = "검색";
		}

		$("#headForm").attr("action",action);		
		
		if(keywordObj.val()==keywordObj.attr('title')){
			keywordObj.attr("title",keywordTitle);
			keywordObj.val('').focusout();
		}
		
		keywordObj.attr("title",keywordTitle);

	}).change();
	
	/* 상단 주메뉴 이슈 카운트 표시 */
	$(window).resize(function(){
		$(".header-gnb-issueCount-layer").each(function(){
			var code = $(this).attr('code');
			var mitemtdObj = $("td.mitem-td").filter("[code='"+code+"']");

			$(this)
			.attr('code',code)
			.css({
				'left'	: mitemtdObj.offset().left,
				'top'	: mitemtdObj.offset().top,
				'width' : mitemtdObj.outerWidth()
			})
			.appendTo("#layout-header");
			
		});
	}).resize();
	
	/* QR코드 안내 */
	$(".qrcodeGuideBtn").live('click',function(){
		if($(this).attr('target')=='parent'){
			parent.openDialog("QR 코드","qrcodeGuideLayer",{"width":950,"height":665});
			var doc = parent.document;
		}else{
			openDialog("QR 코드","qrcodeGuideLayer",{"width":950,"height":665});
			var doc = document;
		}
		
		$("#qrcodeGuideLayer",doc).html('');
		$.ajax({
			'url' : '/selleradmin/common/qrcode_guide?key=' + $(this).attr('key') + '&value=' + $(this).attr('value'),
			'success' : function(result){
				$("#qrcodeGuideLayer",doc).html(result);
			}
		});
		return false;
	});
});


/* 주문상세정보 열기,닫기 처리 */
function toggleOrderDetailBody(btn){
	$(btn).toggleClass("opened");
	$(btn).parents(".order-list-summary-row").find(".order-detail-table").toggleClass("summary-mode");
}

// 우편번호 다이얼로그 박스
function openDialogZipcode(zipcodeFlag,idx,ziptype){
	if(! $(this).is("#"+zipcodeFlag+"Id") ){
		$("body").append("<div id='"+zipcodeFlag+"Id'></div>");

		var url = '../popup/zipcode';
		var params = {'zipcodeFlag':zipcodeFlag,'keyword':'','zipcode_type':ziptype};
		
		if(idx) params.idx = idx; 
		
		$.get(url,params, function(data) {
			$("#"+zipcodeFlag+"Id").html(data);
		});
		openDialog("우편번호 검색 <span class='desc'>지역명으로 우편번호를 검색합니다.</span>",zipcodeFlag+"Id", {"width":900,"height":480});
		setDefaultText();
	}
}

/* 파일첨부 Input박스 버튼 변경 */
function changeFileStyle(){
	$("input[type='file']").each(function(){
		
		if($(this).hasClass("uploadify")) return;
		
		var oriFilebox = $(this);
		var newFilebox = $('<input type="text" value="" size="'+this.size+'" class="'+this.className+' line" readonly style="cursor:default" />');
		//var newBtn = $('<span class="btn small" style="margin-left:2px; "><input type="button" value="찾아보기" /></span>');
		var newBtn = $('<span class="file-search-btn"></span>');

		oriFilebox.css({
			'width'	: '82px',
			'height' : '20px',
			'cursor' : 'pointer',
			'opacity' : '0'
		});

		oriFilebox.after(newBtn).after(newFilebox);
		newBtn.append(oriFilebox);

		oriFilebox.bind("change",function(){
			newFilebox.val(oriFilebox.val());
		});
	});
}

/* copyContent 클립보드 복사 */
function copyContent(str) 
{ 
    if (document.selection) 
    { 
        bResult = window.clipboardData.setData("Text",str); 
        if (bResult) alert('클립보드에 저장되었습니다.'); 
    } else { 
        str = encodeforFlash(str); 
        var flashcopier = 'flashcopier'; 
        if(!document.getElementById(flashcopier)) { 
            var divholder = document.createElement('div'); 
            divholder.id = flashcopier; 
            document.body.appendChild(divholder); 
        } 
        document.getElementById(flashcopier).innerHTML = ''; 
        var divinfo = '<embed src="_clipboard.swf" FlashVars="clipboard='+str+'" width="1" height="1" type="application/x-shockwave-flash"></embed>'; 
        document.getElementById(flashcopier).innerHTML = divinfo; 
        alert('클립보드에 저장되었습니다.'); 
    } 
}; 

/* 파일업로드버튼(Uploadify) 적용 */
function setUploadifyButton(uplodifyButtonId, setting){	
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
		'script'			: krdomain+'/selleradmin/webftp/upload_file',
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
				/* mini_webftp 연동을 위한 세팅 */
				//if(typeof(useWebftpFormItem)!='undefined')
				{
					//if(useWebftpFormItem)
					{
						var webftpFormItemObj = $("#"+uplodifyButtonId+ID).closest(".webftpFormItem");
						webftpFormItemObj.find(".webftpFormItemInput").val(result.filePath);
						webftpFormItemObj.find(".webftpFormItemInputOriName").val(result.fileInfo.client_name);
						webftpFormItemObj.find(".webftpFormItemPreview").attr('src','/'+result.filePath).show();
						
						if(webftpFormItemObj.find(".webftpFormItemPreviewSize").length){
							webftpFormItemObj.find(".webftpFormItemPreviewSize").html(result.fileInfo.image_width + " x " + result.fileInfo.image_height);
						}
					}
				}
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

/*	Select박스 자동완성 
 * 	$(this).combobox();
 * */
(function( $ ) {
	$.widget( "ui.combobox", {
		_create: function() {
			var input,
				that = this,
				select = this.element.hide(),
				selected = select.children( ":selected" ),
				value = selected.val() ? selected.text() : "",
				wrapper = this.wrapper = $( "<span>" )
					.addClass( "ui-combobox" )
					.insertAfter( select );

			function removeIfInvalid(element) {
				var value = $( element ).val(),
					matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( value ) + "$", "i" ),
					valid = false;
				select.children( "option" ).each(function() {
					if ( $( this ).text().match( matcher ) ) {
						this.selected = valid = true;
						return false;
					}
				});
				if ( !valid ) {
					// remove invalid value, as it didn't match anything
					$( element )
						.val( "" );
					select.val( "" );
					input.data( "autocomplete" ).term = "";
					return false;
				}
			}

			input = $( "<input>" )
				.appendTo( wrapper )
				.val( value )
				.attr( "title", "" )
				.addClass( "ui-state-default ui-combobox-input" )
				.autocomplete({
					delay: 0,
					minLength: 0,
					source: function( request, response ) {
						var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
						response( select.children( "option" ).map(function() {
							var text = $( this ).text();
							if ( this.value && ( !request.term || matcher.test(text) ) )
								return {
									label: text.replace(
										new RegExp(
											"(?![^&;]+;)(?!<[^<>]*)(" +
											$.ui.autocomplete.escapeRegex(request.term) +
											")(?![^<>]*>)(?![^&;]+;)", "gi"
										), "<strong>$1</strong>" ),
									value: text,
									option: this
								};
						}) );
					},
					select: function( event, ui ) {
						ui.item.option.selected = true;
						that._trigger( "selected", event, {
							item: ui.item.option
						});
						select.change();
					},
					change: function( event, ui ) {
						if ( !ui.item )
							return removeIfInvalid( this );
					}
				})
				.addClass( "ui-widget ui-widget-content ui-corner-left" );

			input.data( "autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};

			$( "<a>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.appendTo( wrapper )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-right ui-combobox-toggle" )
				.click(function() {
					// close if already visible
					if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
						input.autocomplete( "close" );
						removeIfInvalid( input );
						return;
					}

					// work around a bug (likely same cause as #5265)
					$( this ).blur();

					// pass empty string as value to search for, displaying all results
					input.autocomplete( "search", "" );
					input.focus();
				});

		},

		destroy: function() {
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	});
})( jQuery );

/* 특정영역의 테이블을 엑셀로 다운로드하기 */
function divExcelDownload(title, selector){
	var clone = $(selector).clone();
	// input 박스 제거
	clone.find("input[type='text'],textarea,select").each(function(){
		$(this).after($(this).val());
		$(this).remove();
	});
	/*	
	// a 태그 제거
	clone.find("a").each(function(){
		$(this).after($(this).html());
		$(this).remove();
	});
	*/
	// class,style,width 제거
	//clone.find("*[class]").removeClass();
	//clone.find("*[style]").removeAttr('style');
	//clone.find("*[width]").removeAttr('width');
	clone.find("table").attr('border',1);
	clone.find("th").css('background-color','#e5e5e5');
	
	var randKey = Math.floor(Math.random()*1000000);
	
	var html = "";
	html += "<form id='divExcelDownloadForm"+randKey+"' action='../common/divExcelDownload' method='post' class='hide'>";
	html += "<textarea name='title' id='divExcelDownloadTitle"+randKey+"'></textarea>";
	html += "<textarea name='contents' id='divExcelDownloadContents"+randKey+"'></textarea>";
	html += "</form>";
	
	$(html).appendTo("body");
	
	$("#divExcelDownloadTitle"+randKey).val(title);
	$("#divExcelDownloadContents"+randKey).val(clone.html());
	$("#divExcelDownloadForm"+randKey).submit();
	
}

/* 메뉴별 이슈 카운트 출력 */
function loadIssueCounts(){

	$.ajax({
		"url" : "../common/getIssueCount",
		"dataType" : "json",
		"global" : false,
		"success" : function(data){
			for(var i in data){
				
				$(".header-gnb .mitem-td[code='"+i+"']").each(function(){

					$(".header-gnb-issueCount-layer[code='"+i+"']").append(getIssueCountIcon(data[i]['total'])).effect('bounce');
					
					if(data[i]['title']){
						$(".header-gnb-issueCount-layer[code='"+i+"']").attr('title',data[i]['title']);
					}
					
					$("li a[href*='order/catalog']",this).append(getIssueCountIcon(data[i]['order']));
					$("li a[href*='export/catalog']",this).append(getIssueCountIcon(data[i]['export']));
					$("li a[href*='refund/catalog']",this).append(getIssueCountIcon(data[i]['refund']));
					$("li a[href*='returns/catalog']",this).append(getIssueCountIcon(data[i]['returns']));
					$("li a[href*='board/index']",this).append(getIssueCountIcon(data[i]['mbqna']));
					
				});
				
				if(i == 'setting'){
					if(data[i]['basic']){
						$("ul.gnb-subnb li.basic a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.basic a").append("  <span class='red bold' style='font-size:11px'>!</span>");
					}
					if(data[i]['pg']){
						$("ul.gnb-subnb li.pg a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.pg a").append("  <span class='red bold' style='font-size:11px'>!</span>");
					}
					if(data[i]['bank']){
						$("ul.gnb-subnb li.bank a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.bank a").append("  <span class='red bold' style='font-size:11px'>!</span>");
					}
					if(data[i]['shipping']){
						$("ul.gnb-subnb li.shipping a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.shipping a").append(" <span class='red bold' style='font-size:11px'>!</span>");
					}
				}
				
			}
			 
			$(".header-gnb-issueCount-layer[title]").poshytip({
				className: 'tip-darkgray',
				bgImageFrameSize: 8,
				alignTo: 'target',
				alignX: 'right',
				alignY: 'inner-top',
				offsetY: 7,
				offsetX: -12,		
				allowTipHover: false,
				slide: false,
				showTimeout : 0
			});
		}
	});
	

}

/* 이슈카운트 아이콘 반환 */
function getIssueCountIcon(count){
	if(parseInt(count)>0){
		return " <span class='issueCount'><span class='hgi-left'><span class='hgi-right'><span class='hgi-bg'>"+count+"</span></span></span></span>";
	}else{
		return " <span class='issueCountZero'></span>";
	}
}

function layerPopupOpen(ID){
	var popupKey = "layerPopup"+ID;

	if(!$.cookie(popupKey)){
		$("#"+ID).show();
	}
}


function layerPopupClose(ID){
	var popupKey = "layerPopup"+ID;
	if($("#"+ID+" input[name='hiddenToday']").is(':checked')){
		$.cookie(popupKey,1,{expires:86500,path:'/'});
	}
	$("#"+ID).fadeOut();
}

/* 관리자 카테고리 가져오기*/
function category_admin_select_load(preSelectName,selectName,code,callbackFunction){
	$("select[name='" + selectName + "'] option").each(function(){ if( $(this).val() ) $(this).remove(); });
	if(preSelectName && !code) return;		
	$.ajax({
		type: "GET",
		url: "/selleradmin/common/category2json",
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

/* 관리자 브랜드 가져오기*/
function brand_admin_select_load(preSelectName,selectName,code,callbackFunction){
	$("select[name='" + selectName + "'] option").each(function(){ if( $(this).val() ) $(this).remove(); });
	if(preSelectName && !code) return;		
	$.ajax({
		type: "GET",
		url: "/selleradmin/common/brand2json",
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

/* 관리자 지역 가져오기*/
function location_admin_select_load(preSelectName,selectName,code,callbackFunction){
	$("select[name='" + selectName + "'] option").each(function(){ if( $(this).val() ) $(this).remove(); });
	if(preSelectName && !code) return;		
	$.ajax({
		type: "GET",
		url: "/admin/common/location2json",
		data: "locationCode=" + code,
		dataType: 'json',
		success: function(result){			
			var options = "";			
			for(var i=0;i<result.length;i++) options += "<option value='"+result[i].location_code+"'>"+result[i].title+"</option>";
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

/* 관리자 이벤트 가져오기*/
function event_admin_select_load(preSelectName,selectName,event_seq,callbackFunction){
	$("select[name='" + selectName + "'] option").each(function(){ if( $(this).val() ) $(this).remove(); });
	if(preSelectName && !event_seq) return;		
	$.ajax({
		type: "GET",
		url: "/selleradmin/common/event2json",
		data: "event_seq=" + event_seq,
		dataType: 'json',
		success: function(result){			
			var options = "";			
			for(var i=0;i<result.length;i++) options += "<option value='"+result[i].event_benefits_seq+"'>"+result[i].title+"</option>";
			$("select[name='" + selectName + "']").append(options);
			if(options){
				$("select[name='" + selectName + "']").show();
			}
			if(preSelectName){
				$("select[name='" + preSelectName + "'] option[value='"+event_seq+"']").attr("selected",true);
			}

			if(callbackFunction){
				callbackFunction(result);
			}
		}
	});
}

/* 에디터사용(이벤트시 에디터로딩) */
function view_editor(textid,buttonid)
{
	$("#"+textid).addClass("daumeditor");
	$("#"+buttonid).css("display","none");
	DaumEditorLoader.init(".daumeditor");
}