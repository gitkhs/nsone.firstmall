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
		
		if(!$(this).attr("isdemo") ) { 
			isdemo = false;
		}else{
			isdemo = $(this).attr("isdemo");
		}

		if(!$(this).attr("getdata") ) { 
			getdata = false;
		}else{
			getdata = $(this).attr("getdata");
		}
		
		$.ajax({
			'url' : '/admin/common/getGabiaPannel',
			'data' : {'code':$(this).attr("code"),'isdemo':isdemo,'getdata':getdata},
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
			
		$(".header-snb-item-manual")
		.bind('mouseenter',function(){
			$("ul.header-snb-item-manual-subnb",this).stop(true,true).slideDown('fast');	
		})
		.bind('mouseleave',function(){
			$("ul.header-snb-item-manual-subnb",this).stop(true,true).slideUp('fast');	
		});
		

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
				$(".gnb-subnb",this).stop(true,true).slideDown('fast');	
			})
			.bind('mouseleave',function(){
				$(".gnb-subnb",this).stop(true,true).slideUp('fast');	
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
				$(".gnb-subnb",this).stop(true,true).slideDown('fast');	
			})
			.bind('mouseleave',function(){
				$(".gnb-subnb",this).stop(true,true).slideUp('fast');	
			});
		});
	
		$("#layout-header .header-snb-container ul.header-snb .hsnb-manager").click(function(){
			$(this).toggleClass('opened');
		});
	}
	
	/* 혜택설정바로가기 */
	$(".gnb-benifit").bind("click",function(){
		$(".benifit-popup").toggleClass('current');
		if	( $(".benifit-popup").hasClass('current') ){
			if($(".benifit-popup").html()==""){
				$(".benifit-popup").load("/admin/common/benifit");
			}
			$(".benifit-popup").slideDown('fast');
			$(".gnb-benifit").addClass('gnb-benifit-on');
			$(".gnb-benifit").removeClass('gnb-benifit-off');
		}else{
			$(".benifit-popup").slideUp('fast');
			$(".gnb-benifit").addClass('gnb-benifit-off');
			$(".gnb-benifit").removeClass('gnb-benifit-on');
		}
	});
	
	$("button.benifit-popup-close-btn").bind("click",function(){
		$(".benifit-popup").removeClass('current');
		$(".benifit-popup").slideUp('fase');
	});

	/* 상단 메뉴스타일 변경 */
	$(".mitem-menu-icon-view").bind('click',function(){
		if($("#layout-header").hasClass("icon-view")){
			$("#layout-header").removeClass("icon-view");
			$("#layout-background").removeClass("icon-view");
		}else{
			$("#layout-header").addClass("icon-view");
			$("#layout-background").addClass("icon-view");
		}
		
		$(window).scroll().resize();

		$.ajax({
			'global' : false,
			'url' : '/admin/common/setManagerIconView?val=' + ($("#layout-header").hasClass("icon-view")?'y':'n')			
		});	
	
		return false;
	});

	/* 상단 전체메뉴 보기 */
	$(".mitem-menu-all").click(function(){
		$(".mitem-menu-all").toggleClass('current');
		if	( $(".mitem-menu-all").hasClass('current') )
			$(".header-menu-all").slideDown('fast');
		else
			$(".header-menu-all").slideUp('fast');
	
	});

	$(".menu-all-close-btn").click(function(){
		$(".mitem-menu-all").removeClass('current');
		$(".header-menu-all").slideUp('fase');
	});

	$(".menu-item").each(function(){
		if	( $(this).attr('lines') > 1)	var currentName	= 'current2';
		else								var currentName	= 'current';
		$(this)
		.bind('mouseenter', function(){
			$(".title-text-area").addClass(currentName)
			$(".title-text-area").html($(this).text().replace(/\[\:BR\:\]/g, '<br/>'))
		})
		.bind('mouseleave', function(){
			$(".title-text-area").removeClass(currentName)
			$(".title-text-area").html($('.title-text-default').text())
		});
	});

	/* 상단 검색폼 */
	$("#layout-header select.hsb-kind").each(function(){
		switch($("option:selected",this).text())
		{
			case '주문': var keywordTitle = "주문자,수령자,입금자,아이디 등"; break;
			case '출고': var keywordTitle = "아이디,주문자,수령자 등"; break;
			case '회원': var keywordTitle = "이름,아이디,이메일,연락처,주소"; break;
			case '상품': var keywordTitle = "상품명,상품코드"; break;
			case '쿠폰': var keywordTitle = "쿠폰상품명,쿠폰상품코드,고유값,태그"; break;
			default : var keywordTitle = "검색";
		}
		$("#layout-header input[name='header_search_keyword']").attr("title",keywordTitle);
	}).bind('keyup change',function(){
		var keywordObj = $("#layout-header input[name='header_search_keyword']");
		switch($("option:selected",this).text())
		{
			case '주문': 
				var keywordTitle	= "주문자명,수령자명,입금자명"; 
				var action			= "/admin/order/catalog";
				break;
			case '출고': 
				var keywordTitle	= "주문자명,수령자명,입금자명,쿠폰번호"; 
				var action			= "/admin/export/catalog";
				break;
			case '회원': 
				var keywordTitle	= "아이디,회원명,닉네임,이메일"; 
				var action			= "/admin/member/catalog";
				break;
			case '상품': 
				var keywordTitle	= "상품명,상품코드,고유값,태그"; 
				var action			= "/admin/goods/catalog";
				break;
			case '쿠폰': 
				var keywordTitle	= "쿠폰상품명,쿠폰상품코드,고유값,태그"; 
				var action			= "/admin/goods/social_catalog";
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
				'width' : mitemtdObj.outerWidth()
			});
			
		});
	}).resize();
	
	/* QR코드 안내 */
	$(".qrcodeGuideBtn").bind('click',function(){
		if($(this).attr('target')=='parent'){
			parent.openDialog("QR 코드","qrcodeGuideLayer",{"width":950,"height":665});
			var doc = parent.document;
		}else{
			openDialog("QR 코드","qrcodeGuideLayer",{"width":950,"height":665});
			var doc = document;
		}
		
		$("#qrcodeGuideLayer",doc).html('');
		$.ajax({
			'url' : '/admin/common/qrcode_guide?key=' + $(this).attr('key') + '&value=' + $(this).attr('value'),
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
function openDialogZipcode(zipcodeFlag,idx,ziptype,page,keyword,zipcode){
	var zipcode_val = '';
	if( zipcode ) keyword = zipcode;	
	if(! $(this).is("#"+zipcodeFlag+"Id") ){
		$("body").append("<div id='"+zipcodeFlag+"Id'></div>");
		
		var url = '../popup/zipcode';
		var params = {'zipcodeFlag':zipcodeFlag,'keyword':keyword,'zipcode_type':ziptype,'page':page};
		
		if(idx) params.idx = idx; 
		
		$.get(url,params, function(data) {
			$("#"+zipcodeFlag+"Id").html(data);
		});
		openDialog("우편번호 검색 <span class='desc'>지역명으로 우편번호를 검색합니다.</span>",zipcodeFlag+"Id", {"width":900,"height":550});
		setDefaultText();
	}
}

/* 파일첨부 Input박스 버튼 변경 */
function changeFileStyle(){
	$("input[type='file']").each(function(){

		var oriFilebox = $(this);

		if($(this).hasClass("uploadify")) return;
		$(this).addClass("uploadify");

		var newFilebox = $('<input type="text" value="" size="'+this.size+'" class="'+this.className+' line uploadifyViewBox" readonly style="cursor:default" />');
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

/* 파일첨부 Input박스 버튼 언셋 */
function changeFileStyleUnset(wrap){
	$("input[type='file']",wrap).each(function(){
		var oriFilebox = $(this);
		
		if($(this).hasClass("uploadify")) {
			oriFilebox.unwrap(".file-search-btn");
			oriFilebox.prev(".uploadifyViewBox").remove();
			oriFilebox.unbind("change");

			oriFilebox.removeClass("uploadify");
		}
	});
}

/* copyContent 클립보드 복사 */
function copyContent(str) 
{ 
    if (window.clipboardData && (document.selection || window.getSelection)) { // IE
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
		'script'			: krdomain+'/admin/webftp/upload_file',
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

/* 쿼리로 엑셀로 다운로드하기 :: 2014-10-31 lwh  - 사용시 admin/common.php 작업필 */
function DirectExcelDownload(title, excel_type, queryString){
	// EX : DirectExcelDownload('유입경로','visitor_referer_table', '{_SERVER.QUERY_STRING}')
	var randKey = Math.floor(Math.random()*1000000);
	
	var html = "";
	html += "<form id='DirectExcelDownloadForm"+randKey+"' action='../common/DirectExcelDownload' method='post' class='hide'>";
	html += "<textarea name='title' id='DirectExcelDownloadTitle"+randKey+"'></textarea>";
	html += "<input type='text' name='excel_type' id='DirectExcelDownloadType"+randKey+"' />";
	html += "<input type='text' name='param' id='DirectExcelDownloadParam"+randKey+"' />";
	html += "</form>";
	
	$(html).appendTo("body");
	
	$("#DirectExcelDownloadTitle"+randKey).val(title);
	$("#DirectExcelDownloadType"+randKey).val(excel_type);
	$("#DirectExcelDownloadParam"+randKey).val(queryString);	
	$("#DirectExcelDownloadForm"+randKey).submit();
}

/* 특정영역의 테이블을 엑셀로 다운로드하기 */
function divExcelDownload(title, selector){
	var clone = $(selector).clone();
	// input 박스 제거
	clone.find("input[type='text'],textarea,select").each(function(){
		$(this).after($(this).val());
		$(this).remove();
	});
	clone.find("*.hide").each(function(){
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

	var browserArr	= $.browser.version.split('.');
	var browserVer	= browserArr[0];

	$.ajax({
		"url" : "../common/getIssueCount",
		"dataType" : "json",
		"global" : false,
		"success" : function(data){
			for(var i in data){
				
				$(".header-gnb .mitem-td[code='"+i+"']").each(function(){

					$(".header-gnb-issueCount-layer[code='"+i+"']").append(getIssueCountIcon(data[i]['total'])).effect('bounce');
					
					if(data[i]['title']){
						if(!$.browser.msie || ($.browser.msie && browserVer > 8))
							$(".header-gnb-issueCount-layer[code='"+i+"']").attr('title',data[i]['title']);
					}
					
					$("li a[href*='order/catalog']",this).append(getIssueCountIcon(data[i]['order']));
					$("li a[href*='export/catalog']",this).append(getIssueCountIcon(data[i]['export']));
					$("li a[href*='refund/catalog']",this).append(getIssueCountIcon(data[i]['refund']));
					$("li a[href*='returns/catalog']",this).append(getIssueCountIcon(data[i]['returns']));
					$("li a[href*='board/index']",this).append(getIssueCountIcon(data[i]['mbqna']));
					// 신규회원
					$("li a[href*='member/catalog']",this).append(getIssueCountIcon(data[i]['member']));
					// 할인이벤트
					$("li a[href*='event/catalog']",this).append(getIssueCountIcon(data[i]['event']));
					// 사은품 이벤트
					$("li a[href*='event/gift_catalog']",this).append(getIssueCountIcon(data[i]['gift']));
					// 출석체크
					$("li a[href*='joincheck/catalog']",this).append(getIssueCountIcon(data[i]['joincheck']));
					
				});
				
				if(i == 'setting'){
					if(data[i]['basic']){
						$(".gnb-subnb li.basic a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.basic a").append("  <span class='red bold' style='font-size:11px'>!</span>");
					}
					if(data[i]['pg']){
						$(".gnb-subnb li.pg a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.pg a").append("  <span class='red bold' style='font-size:11px'>!</span>");
					}
					if(data[i]['bank']){
						$(".gnb-subnb li.bank a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.bank a").append("  <span class='red bold' style='font-size:11px'>!</span>");
					}
					if(data[i]['shipping']){
						$(".gnb-subnb li.shipping a").append(" <span class='red bold' style='font-size:11px'>!</span>");
						$("div.slc-head ul li span.shipping a").append(" <span class='red bold' style='font-size:11px'>!</span>");
					}

					if(data[i]['total']){
						if(!$.browser.msie || ($.browser.msie && browserVer > 8))
							$(".header-gnb-issueCount-layer[code='setting']").attr('title','필수 설정');
						
						$(".qnb-config").append("<span class='issueCount' style='left:45px; top:0px; position:absolute;'><span class='hgi-left'><span class='hgi-right'><span class='hgi-bg'>!</span></span></span></span>");
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
		url: "/admin/common/category2json",
		data: "categoryCode=" + code,
		dataType: 'json',
		global:false,
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
			if($("select[name='" + selectName + "']").attr("defaultValue")){
				$("select[name='" + selectName + "'] option[value='"+$("select[name='" + selectName + "']").attr("defaultValue")+"']").attr("selected",true).change();
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
		url: "/admin/common/brand2json",
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
			if($("select[name='" + selectName + "']").attr("defaultValue")){
				$("select[name='" + selectName + "'] option[value='"+$("select[name='" + selectName + "']").attr("defaultValue")+"']").attr("selected",true).change();
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
		url: "/admin/common/event2json",
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
			if($("select[name='" + selectName + "']").attr("defaultValue")){
				$("select[name='" + selectName + "'] option[value='"+$("select[name='" + selectName + "']").attr("defaultValue")+"']").attr("selected",true).change();
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
			if($("select[name='" + selectName + "']").attr("defaultValue")){
				$("select[name='" + selectName + "'] option[value='"+$("select[name='" + selectName + "']").attr("defaultValue")+"']").attr("selected",true).change();
			}

			if(callbackFunction){
				callbackFunction(result);
			}
		}
	});
}

/* 매뉴얼 레이어 띄우기 */
function show_simple_manual(section){
	if(!$("#simpleManualContainer").length){
		$("<div id='simpleManualContainer'></div>").appendTo('body');
	}
	$("#simpleManualContainer").empty();
	$.ajax({
		'global'	: false,
		'url'		: "/admin/common/showSimpleManual?section=" + section,
		'success'	: function(result){
			$("#simpleManualContainer").html(result);
		}
	});

/*

	var modalBackground = $("<div id='#modalBackground'></div>").css({'display':'none','position':'fixed','z-index':'1000','left':'0px','top':'0px','width':'100%','height':'100%','background-color':'#000','opacity':'0.5'});

	if(section=='menu'){
		modalBackground.appendTo('body').show();
	}

	if(section=='setting'){
		modalBackground.appendTo('body').show();
	}

	if(section=='design'){
		openDialogAlert("준비중입니다.",400,140);
	}
	*/
}

function printOrderView(ordno){
	var target = window.open('about:blank', 'printOrderViewPopup', 'width=850px,height=800px,toolbar=no,location=no,resizable=yes,scrollbars=yes');
	var ordno = ordno.split('|');
	var inputs = '';
	for(var i=0;i<ordno.length;i++){
		inputs +='<input type="hidden" name="ordarr[]" value="'+ ordno[i] +'" />';
	}
	jQuery('<form action="/admin/order/order_prints" method="post" target="printOrderViewPopup">'+inputs+'</form>')
	.appendTo('body').submit().remove();
}

function printExportView(ordno, code){
	var target = window.open('about:blank', 'printExportViewPopup', 'width=850px,height=800px,toolbar=no,location=no,resizable=yes,scrollbars=yes');
	var code = code.split('|');
	var order = ordno.split('|');
	var inputs = '';
	for(var i=0;i<order.length;i++){
		inputs +='<input type="hidden" name="export[]" value="'+ code[i] +'" />';
		inputs +='<input type="hidden" name="order[]" value="'+ order[i] +'" />';
	}
	jQuery('<form action="/admin/export/export_prints" method="post" target="printExportViewPopup">'+inputs+'</form>')
	.appendTo('body').submit().remove();
}

function printInvoiceView(ordno, code){
	var target = window.open('about:blank', 'printInvoiceViewPopup', 'width=800px,height=800px,toolbar=no,location=no,resizable=yes,scrollbars=yes,menubar=yes');
	var code = code.split('|');
	var order = ordno.split('|');
	var inputs = '';
	for(var i=0;i<order.length;i++){
		inputs +='<input type="hidden" name="export[]" value="'+ code[i] +'" />';
		inputs +='<input type="hidden" name="order[]" value="'+ order[i] +'" />';
	}
	jQuery('<form action="/admin/export/invoice_prints" method="post" target="printInvoiceViewPopup">'+inputs+'</form>')
	.appendTo('body').submit().remove();
}

/* 카테고리,브랜드 상품 정렬 레이어팝업 띄우기 */
function show_goods_sort_popup(kind,code,count_w,count_h){
	top.openDialogPopup("상품 순서 변경", "goods-sort-popup", {
		'url' : '/admin/common/goods_sort_popup',
		'data' : {'kind':kind,'code':code,'count_w':count_w,'count_h':count_h},
		'modal' : false,
		'width' : 'auto',
		'height' : (count_h>1?count_h:2)*100+100
	});	
}

/* 에디터사용(이벤트시 에디터로딩) */
function view_editor(textid,buttonid)
{
	$("#"+textid).addClass("daumeditor");
	$("#"+buttonid).css("display","none");
	DaumEditorLoader.init(".daumeditor");
}

/* 디자인창 : 아이에디터 */
var EYEEDITOR_WINDOW = null;
function DM_window_eyeeditor(template_path){
	var queryString = template_path ? "?template_path="+encodeURIComponent(template_path?template_path:'') : "";
	if(!EYEEDITOR_WINDOW || (EYEEDITOR_WINDOW && typeof EYEEDITOR_WINDOW.document == 'unknown')){
		EYEEDITOR_WINDOW = window.open("/admin/design/eye_editor"+queryString,"eyceditorWindowAdmin","width=950,height=650,scrollbar=no,resizable=yes");
	}else{
		EYEEDITOR_WINDOW.document.focus();
	}
}