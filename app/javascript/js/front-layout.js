$(function(){
	
	/* 사이드 날개 배너 무빙 처리 */
	$("#leftScrollLayer,#rightScrollLayer").each(function(){
		var scrollLayerTopMargin = 10;
		var scrollLayerObj = $(this);
		var defaultScrollLayerTop = parseInt(scrollLayerObj.offset().top);
		var defaultMarginLeft = parseInt($(this).css('margin-left'));
		var divisionHiddenLayer = $("<div></div>").css({
			'position':'absolute',
			'margin-top':$(this).css('margin-top'),
			'margin-left':$(this).css('margin-left'),
			'margin-bottom':$(this).css('margin-bottom'),
			'margin-right':$(this).css('margin-right'),
			'padding-top':$(this).css('padding-top'),
			'padding-left':$(this).css('padding-left'),
			'padding-bottom':$(this).css('padding-bottom'),
			'padding-right':$(this).css('padding-right'),
			'padding':$(this).css('padding'),
			'width':$(this).width(),
			'height':$(this).height(),
			'z-index':1
		});

		$(window).scroll(function(){
			var scrollTop = parseInt($(document).scrollTop());

			if(scrollTop>defaultScrollLayerTop-scrollLayerTopMargin)
			{
				scrollLayerObj.after(divisionHiddenLayer.show());
				scrollLayerObj.css({
					'position' : 'fixed',
					'z-index' : 6,
					'margin-top' : scrollLayerTopMargin,
					'margin-left' : (defaultMarginLeft-parseInt($(document).scrollLeft()))+'px'
				});
			}
			else
			{
				scrollLayerObj.css({
					'position' : 'absolute',
					'margin-top' : '0px',
					'margin-left' : defaultMarginLeft+'px'
				});
				divisionHiddenLayer.hide();
			}
		});
	});
	
	/* 디자인팝업 쿠키처리 */
	$(".designPopup").each(function(){
		var popup = this;
		var popupStyle = $(popup).attr('popupStyle');
		var popupSeq = $(popup).attr('popupSeq');
		var popupKey = "designPopup"+popupSeq;

		if(typeof gl_mobile_mode != 'undefined' && gl_mobile_mode){
			$(this).css({
				'left' : 'calc(50% - '+($(this).width()/2)+'px)',
				'top' : ($(window).height()/2-$(this).height()/2)+'px'
			});
			if(num($(this).css('left'))==0){
				$(this).css({
				'left' : '50%',
				'margin-left' : '-' + ($(this).width()/2) + 'px'
				});
			}
			$("#designPopupModalBack").remove();
			$("<div id='designPopupModalBack'></div>").css({'background':'#000000','position':'fixed','left':'0px','top':'0px','width':'100%','height':'100%','opacity':'0.5','z-index':'99'}).appendTo('body');
		}

		$(".designPopupClose",popup).click(function(){
			if(popupStyle=='layer')		{$(popup).fadeOut();$("#designPopupModalBack").remove();}
			if(popupStyle=='window')	window.close();
		});
		$(".designPopupTodaymsg",popup).click(function(){
			var timestamp = parseInt(new Date().getTime().toString().substring(0, 10));
			$.cookie(popupKey,timestamp,{expires:1,path:'/'}); // expires : 日단위
			if(popupStyle=='layer')		{$(popup).fadeOut();$("#designPopupModalBack").remove();}
			if(popupStyle=='window')	window.close();
		});
		if(popupStyle=='layer'){
			$(".designPopupBar",popup).css("cursor","move");
			$(this).draggable({handle: $(".designPopupBar",popup)});
		}
	});

	/* 디자인팝업 띠배너 */
	$(".designPopupBand").each(function(i){
		// 첫번째를 제회한 나머지는 제거
		if(i) {
			$(this).remove();
			return;
		}

		var popup = this;
		var popupStyle = $(popup).attr('popupStyle');
		var popupSeq = $(popup).attr('popupSeq');
		var popupKey = "designPopup"+popupSeq;

		$(popup).prependTo('body').slideDown();

		$(".designPopupClose",popup).click(function(){
			$(popup).slideUp(function(){
				$(".designPopupBandBtn").eq(i).prependTo('body').fadeIn();
			});
		});
	});
	$(".designPopupBandBtn").each(function(i){
		// 첫번째를 제회한 나머지는 제거
		if(i) {
			$(this).remove();
			return;
		}

		var btn = this;

		$(this).click(function(){
			$(btn).hide();
			$(".designPopupBand").eq(i).slideDown();
		});
	});

	/* 디자인팝업 모바일띠배너 */
	$(".designPopupBandMobile").each(function(i){
		// 첫번째를 제회한 나머지는 제거
		if(i) {
			$(this).remove();
			return;
		}

		var popup = this;
		var popupStyle = $(popup).attr('popupStyle');
		var popupSeq = $(popup).attr('popupSeq');
		var popupKey = "designPopup"+popupSeq;

		$(popup).prependTo('body').show();
		$(".designPopupClose",popup).click(function(){
			$(popup).slideUp();
		});
	});
	
	/* 오늘본상품 스크롤 : 바인드 */
	if ($(".scrollItemContainer").length > 0) {
		setTodayGoodsScroll();
	} else if($(".rightQuickMenu").length > 0) {
		/* 우측퀵메뉴 기능개선 leewh 2014-06-03 */
		$(window).load(function () {
			setRightMenu();
		});
	}

	/* 상품디스플레이 탭 스크립트 */
	$(".displayTabContainer").each(function(){
		var tabContainerObj = $(this);
		var displayObj = tabContainerObj.closest('.designDisplay, .designCategoryRecommendDisplay, .designBrandRecommendDisplay, .designLocationRecommendDisplay');
		var displaySeq = displayObj.attr('displaySeq');
		$(".displayTabContentsContainer",displayObj).wrap("<div class='displayTabContentsContainerWrap slide' />");

		displayObj.addClass('slide-wrap'); // for swipe

		tabContainerObj.children('li').bind('mouseover click',function(){
			var tabIdx = tabContainerObj.children('li').index(this);
			try{
				$(displayObj).data('slide_current', tabIdx);//for swipe
			}catch(e){};

			var prevContentsHeight = displayObj.find('.displayTabContentsContainer:visible').height();
			
			displayObj.find('.displayTabContentsContainerWrap').removeClass('active').hide().eq(tabIdx).addClass('active').show();
			displayObj.find('.displayTabContentsContainer').hide().eq(tabIdx).show();

			displayObj.find('.displayTabContainer>li').find("img.displayTabItemImageOff").show();
			displayObj.find('.displayTabContainer>li').find("img.displayTabItemImageOn").hide();
			displayObj.find('.displayTabContainer>li').eq(tabIdx).find("img.displayTabItemImageOff").hide();
			displayObj.find('.displayTabContainer>li').eq(tabIdx).find("img.displayTabItemImageOn").show();

			if(tabIdx==0) return;
			
			var displayTabContentsContainerWrap = displayObj.find('.displayTabContentsContainerWrap').eq(tabIdx);

			if(!displayTabContentsContainerWrap.attr('ajaxLoaded')){
				displayTabContentsContainerWrap.attr('ajaxLoaded',true);
				displayTabContentsContainerWrap.children(".displayTabContentsContainer").height(prevContentsHeight);
				displayTabContentsContainerWrap.children(".displayTabContentsContainer").attr("id","displayTabLoading_"+displaySeq+"_"+tabIdx).css('min-height','100px').height(prevContentsHeight);
				loadingStart("#displayTabLoading_"+displaySeq+"_"+tabIdx,{'color':'#000000'});
				var params = {'display_seq':displaySeq,'tab_index':tabIdx};
				if(displayObj.hasClass('designCategoryRecommendDisplay'))	params['category'] = displayObj.attr('category');
				if(displayObj.hasClass('designLocationRecommendDisplay'))	params['location'] = displayObj.attr('location');
				if(displayObj.hasClass('designBrandRecommendDisplay'))		params['brand'] = displayObj.attr('brand');
				$.ajax({
					'global' : false,
					'url' : '/goods/design_display_tab',
					'type' : 'post',
					'data' : params,
					'success' : function(res){
						loadingStop("#displayTabLoading_"+displaySeq+"_"+tabIdx);
						displayTabContentsContainerWrap.html(res);
						displayTabContentsContainerWrap.children().not(".displayTabContentsContainer").remove();
						displayTabContentsContainerWrap.find(".displayTabContentsContainer").addClass('displayTabContentsContainerBox');
						if(displayTabContentsContainerWrap.is(":visible")) {
							try{
								set_goods_display_decoration($(".goodsDisplayImageWrap",displayTabContentsContainerWrap));
								set_goods_display_decoration_event($(".goodsDisplayImageWrap",displayTabContentsContainerWrap));
							}catch(e){};
						}
						try{
							FB.XFBML.parse($(tabContainerObj).get(1));
						}catch(ex){}
						echo.init({
							offset: 100,
							throttle: 250,
							unload: false
						});
					}
				});
			}else{
				try{set_goods_display_decoration($(".goodsDisplayImageWrap",displayTabContentsContainerWrap));}catch(e){};
			}
		});
	});

	/* 상품디스플레이 newswipe형 탭 스크립트 */
	$(".displaySwipeTabContainer").each(function(){
		var tabContainerObj = $(this);
		var displayObj = tabContainerObj.closest('.designDisplay, .designCategoryRecommendDisplay, .designBrandRecommendDisplay, .designLocationRecommendDisplay');
		var displaySeq = displayObj.attr('displaySeq');
		$(".displaySwipeTabContentsContainer",displayObj).each(function(){
			$(this).wrap("<div class='displaySwipeTabContentsContainerWrap slide' tabIdx='"+$(this).attr("tabIdx")+"' />");
		});

		displayObj.addClass('slide-wrap'); // for swipe

		$(".custom_mobile_pagination",displayObj).customMobilePagination({
			'style' : $(".custom_mobile_pagination",displayObj).attr('pagingStyle'),
			'max_page' : displayObj.find('.displaySwipeTabContentsContainerWrap').length-1,
			'now_page' : 1,
			'on_prev' : function(){
				var currentTabContentsIdx = $(displayObj).data('currentTabContentsIdx') ? $(displayObj).data('currentTabContentsIdx') : 0;
				var maxTabContentdIdx = displayObj.find('.displaySwipeTabContentsContainerWrap').length-1;
				chgSlideNewSwipe(currentTabContentsIdx==0?maxTabContentdIdx:currentTabContentsIdx-1,displayObj);
			},
			'on_next' : function(){
				var currentTabContentsIdx = $(displayObj).data('currentTabContentsIdx') ? $(displayObj).data('currentTabContentsIdx') : 0;
				var maxTabContentdIdx = displayObj.find('.displaySwipeTabContentsContainerWrap').length-1;
				chgSlideNewSwipe(currentTabContentsIdx==maxTabContentdIdx?0:currentTabContentsIdx+1,displayObj);

			},
			'on_page' : function(page){
				var tabIdx = $(displayObj).find(".displaySwipeTabContainer>li").index($(displayObj).find(".displaySwipeTabContainer>li.current"));
				var newTabContentsIdx = $(displayObj).find(".displaySwipeTabContentsContainer").index($(displayObj).find(".displaySwipeTabContentsContainerWrap[tabIdx='"+tabIdx+"'] .displaySwipeTabContentsContainer").eq(page-1));
				chgSlideNewSwipe(newTabContentsIdx,displayObj);
			}
		});

		tabContainerObj.children('li').bind('mouseover click',function(){
			var currentTabIdx = $(displayObj).data('currentTabIdx') ? $(displayObj).data('currentTabIdx') : 0;
			var currentTabContentsIdx = $(displayObj).data('currentTabContentsIdx') ? $(displayObj).data('currentTabContentsIdx') : 0;
			var tabIdx = tabContainerObj.children('li').index(this);
			try{
				$(displayObj).data('currentTabIdx', tabIdx);//for swipe
			}catch(e){};

			if($(displayObj).data('newTabContentsIdx') != 'undefined'){
				if(displayObj.find('.displaySwipeTabContentsContainerWrap').eq($(displayObj).data('newTabContentsIdx')).attr("tabIdx")==tabIdx){
					var newTabContentsIdx = $(displayObj).data('newTabContentsIdx');
				}else{
					var newTabContentsIdx = displayObj.find(".displaySwipeTabContentsContainer").index(displayObj.find(".displaySwipeTabContentsContainerWrap[tabIdx='"+tabIdx+"'] .displaySwipeTabContentsContainer:first-child"));
				}
			}else{
				var newTabContentsIdx = displayObj.find(".displaySwipeTabContentsContainer").index(displayObj.find(".displaySwipeTabContentsContainerWrap[tabIdx='"+tabIdx+"'] .displaySwipeTabContentsContainer:first-child"));
			}

			$(displayObj).data('slide_current',newTabContentsIdx);

			var prevContentsHeight = displayObj.find('.displaySwipeTabContentsContainerWrap:visible .displaySwipeTabContentsContainer').height();

			displayObj.find('.displaySwipeTabContentsContainerWrap').removeClass('active').hide().eq(newTabContentsIdx).addClass('active').show();
			displayObj.find('.displaySwipeTabContentsContainer').hide().eq(newTabContentsIdx).show();

			displayObj.find('.displaySwipeTabContainer>li').find("img.displaySwipeTabItemImageOff").show();
			displayObj.find('.displaySwipeTabContainer>li').find("img.displaySwipeTabItemImageOn").hide();
			displayObj.find('.displaySwipeTabContainer>li').eq(tabIdx).find("img.displaySwipeTabItemImageOff").hide();
			displayObj.find('.displaySwipeTabContainer>li').eq(tabIdx).find("img.displaySwipeTabItemImageOn").show();

			// 페이징 숫자 변경
			var max_page = $(displayObj).find(".displaySwipeTabContentsContainer[tabIdx='"+tabIdx+"']").length;
			var now_page = $(displayObj).find(".displaySwipeTabContentsContainer[tabIdx='"+tabIdx+"']").index($(displayObj).find(".displaySwipeTabContentsContainer").eq(newTabContentsIdx))+1;
			$(".custom_mobile_pagination",displayObj).customMobilePagination("set_page",{
				'max_page' : max_page,
				'now_page' : now_page
			});

			if(tabIdx==0) return;
			
			var displaySwipeTabContentsContainerWrap = displayObj.find('.displaySwipeTabContentsContainerWrap').eq(newTabContentsIdx);

			if(!displaySwipeTabContentsContainerWrap.attr('ajaxLoaded')){
				displaySwipeTabContentsContainerWrap.attr('ajaxLoaded',true);
				displaySwipeTabContentsContainerWrap.children(".displaySwipeTabContentsContainer").height(prevContentsHeight);
				displaySwipeTabContentsContainerWrap.children(".displaySwipeTabContentsContainer").attr("id","displaySwipeTabLoading_"+displaySeq+"_"+newTabContentsIdx).css('min-height','100px').height(prevContentsHeight);
				loadingStart("#displaySwipeTabLoading_"+displaySeq+"_"+newTabContentsIdx,{'color':'#000000'});
				var params = {'display_seq':displaySeq,'tab_index':tabIdx};
				if(displayObj.hasClass('designCategoryRecommendDisplay'))	params['category'] = displayObj.attr('category');
				if(displayObj.hasClass('designLocationRecommendDisplay'))	params['location'] = displayObj.attr('location');
				if(displayObj.hasClass('designBrandRecommendDisplay'))		params['brand'] = displayObj.attr('brand');
				$.ajax({
					'global' : false,
					'url' : '/goods/design_display_tab',
					'async' : false,
					'type' : 'post',
					'data' : params,
					'success' : function(res){
						loadingStop("#displaySwipeTabLoading_"+displaySeq+"_"+newTabContentsIdx);

						displaySwipeTabContentsContainerWrap.html(res);
						displaySwipeTabContentsContainerWrap.children().not(".displaySwipeTabContentsContainer").remove();
						$(".displaySwipeTabContentsContainer:gt(0)",displaySwipeTabContentsContainerWrap).each(function(){
							$(this).wrap("<div class='displaySwipeTabContentsContainerWrap slide' ajaxLoaded='true' tabIdx='"+$(this).attr("tabIdx")+"' />");
						});
						displaySwipeTabContentsContainerWrap.after(displaySwipeTabContentsContainerWrap.children(".displaySwipeTabContentsContainerWrap"));
						
						setSlideSwipe(displayObj, '.displaySwipeTabContentsContainerWrap','chgSlideNewSwipe');

						// 페이징 숫자 변경
						var max_page = $(displayObj).find(".displaySwipeTabContentsContainer[tabIdx='"+tabIdx+"']").length;
						var now_page = $(displayObj).find(".displaySwipeTabContentsContainer[tabIdx='"+tabIdx+"']").index($(displayObj).find(".displaySwipeTabContentsContainer").eq(newTabContentsIdx))+1;
						$(".custom_mobile_pagination",displayObj).customMobilePagination("set_page",{
							'max_page' : max_page,
							'now_page' : now_page
						});

						try{
							set_goods_display_decoration($(".goodsDisplayImageWrap",displayObj));
							FB.XFBML.parse($(tabContainerObj).get(1));
						}catch(ex){}

						echo.init({
							offset: 100,
							throttle: 250,
							unload: false
						});
					}
				});
			}else{
				try{set_goods_display_decoration($(".goodsDisplayImageWrap",displaySwipeTabContentsContainerWrap));}catch(e){};
			}
		});
	});

	try{
		if(typeof gl_mobile_mode != 'undefined' && gl_mobile_mode)
		$(".designDisplay, .designCategoryRecommendDisplay, .designBrandRecommendDisplay, .designLocationRecommendDisplay").each(function(){
			if($(this).attr("displayStyle")=="newswipe"){
				setSlideSwipe(this, '.displaySwipeTabContentsContainerWrap','chgSlideNewSwipe');
			}else{
				setSlideSwipe(this, '.displayTabContentsContainerWrap','chgSlideSwipe');
			}
		});
	}catch(e){};

});

function chgSlideSwipe(i,wrap_name){
	var slide_current = $(wrap_name).data('slide_current');
	var currentTabContentsObj = $(wrap_name).find(".displayTabContentsContainer").eq(i);
	var currentTabContentsIdx = currentTabContentsObj.closest(wrap_name).find(".displayTabContentsContainer").index(currentTabContentsObj);
	
	var displayObj = $(wrap_name).find(".displayTabContentsContainer").eq(currentTabContentsIdx).closest(wrap_name);
	$("ul.displayTabContainer>li",displayObj).eq(currentTabContentsIdx).click();
	$("ul.displayTabContainer>li",displayObj).eq(slide_current).removeClass('slide_current').eq(currentTabContentsIdx).addClass('slide_current');
	$(".displayTabContentsContainer.active",displayObj).removeClass('active').eq(currentTabContentsIdx).addClass('slide_current');
	
	$(wrap_name).data('slide_current',i);
}

function chgSlideNewSwipe(i,wrap_name){
	var currentTabIdx = $(wrap_name).data('currentTabIdx');
	var newTabContentsObj = $(wrap_name).find(".displaySwipeTabContentsContainer").eq(i);
	var newTabContentsIdx = newTabContentsObj.closest(wrap_name).find(".displaySwipeTabContentsContainer").index(newTabContentsObj);
	var newTabIdx = newTabContentsObj.attr("tabIdx");

	$(wrap_name).data('slide_current',i)

	var displayObj = $(wrap_name).find(".displaySwipeTabContentsContainer").eq(newTabContentsIdx).closest(wrap_name);
	if(currentTabIdx != newTabIdx){
		$(displayObj).data('newTabContentsIdx',newTabContentsIdx);
		$("ul.displaySwipeTabContainer>li",displayObj).eq(newTabIdx).click();
		$("ul.displaySwipeTabContainer>li",displayObj).eq(currentTabIdx).removeClass('currentTabIdx').eq(newTabIdx).addClass('currentTabIdx');
	}else{
		displayObj.find('.displaySwipeTabContentsContainerWrap').removeClass('active').hide().eq(newTabContentsIdx).addClass('active').show();
		displayObj.find('.displaySwipeTabContentsContainer').hide().eq(newTabContentsIdx).show();
	}
	$(".displaySwipeTabContentsContainer.active",displayObj).removeClass('active').eq(newTabContentsIdx).addClass('currentTabIdx');

	$(wrap_name).data('currentTabIdx',newTabIdx).data('currentTabContentsIdx',newTabContentsIdx);

	// 페이징 숫자 변경
	var max_page = $(displayObj).find(".displaySwipeTabContentsContainer[tabIdx='"+newTabIdx+"']").length;
	var now_page = $(displayObj).find(".displaySwipeTabContentsContainer[tabIdx='"+newTabIdx+"']").index($(displayObj).find(".displaySwipeTabContentsContainer").eq(newTabContentsIdx))+1;
	$(".custom_mobile_pagination",displayObj).customMobilePagination("set_page",{
		'max_page' : max_page,
		'now_page' : now_page
	});

}

/* 즐겨찾기 적립금추가여부 */
function bookmarksitelay(url,title, return_url) { 
	$.ajax({
		type: "post",
		url: "/common/bookmark",
		dataType: 'json',
		success: function(result){bookmarksite(url,title);
			if(result.result == false){
				if(result.type == 'login'){
					openDialogConfirm(result.msg,'400','160',function(){document.location.href=return_url},function(){alert("b3");bookmarksite(url,title);});
				}else{ 
					alert("1");
					openDialogAlert(result.msg,'400','160',function(){ alert("b1");bookmarksite(url,title);});
				}
			}else{
					alert("2");
				openDialogAlert(result.msg,'400','160',function(){alert("b2");bookmarksite(url,title);});
			}
		}
	});

	
					alert("3");
}

/* 즐겨찾기 추가 스크립트 */
function bookmarksite(url,title) { 
	// Google Chrome
	if(window.chrome){
		alert("Ctrl+D키를 누르시면 즐겨찾기에 추가하실 수 있습니다.");
		return false;
	}
	// Internet Explorer
	else if(window.external && !window.sidebar)
	{
		window.external.AddFavorite(url, title); 
	}
	else if(window.external && window.sidebar)
	{
		var obj = document.getElementById("linkbookmark");
		obj.setAttribute('href',url); 
		obj.setAttribute('title',title); 
		return false;
	}
	// Opera,  Firefox
	else if((window.opera && window.print) || (window.external && window.sidebar))
	{
		var elem = document.createElement('a'); 
		elem.setAttribute('href',url); 
		elem.setAttribute('title',title); 
		elem.setAttribute('rel','sidebar'); 
		elem.click(); 
	}
}

/* 오늘본상품 스크롤 : 바인드 */
function setTodayGoodsScroll(){
	var scrollItemViewCnt = 4;		// 한번에 보일 상품개수
	var scrollItemImageMaxWidth = 40;	// 상품이미지 최대가로크기
	var scrollItemImageMaxHeight = 60;	// 상품이미지 최대세로크기
	var scrollItemImageGap = 5;		// 사품이미지 간격

	$(".scrollItemContainer").each(function(){
		if($(this).data('scrollSetted')){
			unsetTodayGoodsScroll(this);
		}

		var scrollItemContainerObj = this;
		var goodsCnt = $(".scrollItemGoods",this).length;
		var firstGoodsIdx = 0;

		// 상품이미지 사이즈맞춤
		var scrollItemImageWidth = $(".scrollItemGoodsImg",this).width();
		var scrollItemImageHeight = $(".scrollItemGoodsImg",this).height();
		if(scrollItemImageWidth) scrollItemImageMaxWidth = scrollItemImageWidth;
		if(scrollItemImageHeight) scrollItemImageMaxHeight = scrollItemImageHeight;
		$(".scrollItemGoodsImg",this).css({'max-width':scrollItemImageMaxWidth,'max-height':scrollItemImageMaxHeight});
		$(".scrollItemGoodsImg",this).css({'width':scrollItemImageWidth});
		$(".scrollItemGoodsImg",this).css('margin-top',scrollItemImageGap);
		$(".scrollItemGoods",this).css({"height":scrollItemImageHeight+scrollItemImageGap,"overflow":"hidden"});
		
		$(".scrollItemGoodsImg",this).each(function(){
			if($(this).height()<scrollItemImageHeight){
				var mrgn = (scrollItemImageHeight-$(this).height())/2;
				$(this).css({'margin-top':'+='+mrgn,'margin-bottom':'+='+mrgn});
			}
		});

		// 전체높이
		var scrollItemListHeight = (scrollItemImageHeight+scrollItemImageGap)*(goodsCnt<scrollItemViewCnt ? goodsCnt : scrollItemViewCnt);

		$(".scrollItemList",this).css({'overflow':'hidden','height':scrollItemListHeight});

		if(goodsCnt<scrollItemViewCnt) scrollItemViewCnt = goodsCnt;
		if(goodsCnt>scrollItemViewCnt){
			$(".scrollItemList",this).append($(".scrollItemGoods",this).clone());
			var scrollItemListCnt = goodsCnt*2;

			$(".scrollItemArrowUp",this).bind("click",function(){
				firstGoodsIdx -= scrollItemViewCnt;
				if(firstGoodsIdx<scrollItemViewCnt-1) {
					firstGoodsIdx+=goodsCnt;
					$(".scrollItemList",scrollItemContainerObj).scrollTop((scrollItemImageHeight+scrollItemImageGap)*firstGoodsIdx);
				}else{
					$(".scrollItemList",scrollItemContainerObj).scrollTop($(".scrollItemList",scrollItemContainerObj).scrollTop()-scrollItemListHeight);
				}
			});

			$(".scrollItemArrowDown",this).bind("click",function(){

				firstGoodsIdx += scrollItemViewCnt;
				if(firstGoodsIdx>goodsCnt) {
					firstGoodsIdx=(firstGoodsIdx-goodsCnt);
					$(".scrollItemList",scrollItemContainerObj).scrollTop((scrollItemImageHeight+scrollItemImageGap)*firstGoodsIdx);
				}else{
					$(".scrollItemList",scrollItemContainerObj).scrollTop($(".scrollItemList",scrollItemContainerObj).scrollTop()+scrollItemListHeight);
				}

			});
		}

	});

	$(".scrollItemContainer").data('scrollSetted',true);
}

/* 오늘본상품 스크롤 : 언바인드 */
function unsetTodayGoodsScroll(scrollItemContainerObj){
	$(scrollItemContainerObj).each(function(){
		var scrollItemListCnt = $(".scrollItemGoods",this).length;
		var goodsCnt = scrollItemListCnt/3;
		$(".scrollItemGoods:gt("+goodsCnt/3-1+")",this).remove();
		
		$(".scrollItemArrowUp",this).unbind("click");
		$(".scrollItemArrowDown",this).unbind("click");
		$(".scrollItemList",this).hide();
	});
	
}

//출석체크 팝업
function jc_view_btn(url,sz1,sz2){
	var str= url;
	popup(str,sz1,sz2);
	
}

function openMobileDialog(title, layerId, customOptions){
	if((typeof layerId) != 'string') var layerSelector = layerId;
	else if(layerId.substring(0,1)=='#' || layerId.substring(0,1)=='.' || (typeof layerId) != 'string') var layerSelector = layerId;
	else var layerSelector = "#"+layerId;

	$(layerSelector).css({
		"padding":"10px",
		"background-color":"#fff",
		"position":"absolute",
		"top":"0px",
		"left":"0px",
		"z-index":"220",
		"width":"1000px",
		"border":"2px solid #000"
	}).prepend("<strong>"+title+"</strong><br /><br />").show();

	var layerBackgroundId = "mobileDialogBackground";
	if($("#"+layerBackgroundId).length==0){
		var layerBackgroundSelector = $("<div id='"+layerBackgroundId+"' style='background-color:#fff;position:fixed;width:100%;height:100%;top:0px;left:0px;z-index:200;'></div>").appendTo('body');
		$(layerBackgroundSelector).height($(document).height());
	}
	$(layerBackgroundSelector).show();
}

function closeMobileDialog(layerId){
	if((typeof layerId) != 'string') var layerSelector = layerId;
	else if(layerId.substring(0,1)=='#' || layerId.substring(0,1)=='.' || (typeof layerId) != 'string') var layerSelector = layerId;
	else var layerSelector = "#"+layerId;

	$(layerSelector).hide();
	$("#mobileDialogBackground").hide();
}

function autocomplete(keyword) {
	if($.cookie('autocomplete') == "none"){
		var offset = $('input[name=search_text]').offset();
		if( offset && $('#autocomplete').length == 0) {
			$('<div id="autocomplete" style="display:none; position:absolute;"></div>').prependTo(document.body);
			$('#autocomplete').css({
				'position' : 'absolute',
				'z-index' : 999,
				'left' : offset.left,
				'top' : offset.top+$('input[name=search_text]').height()
			});
		}
		$('#autocomplete').html("<div style=\"border-collapse:collapse; background:#ffffff; border:1px solid #e0e0e0; padding:7px 7px 7px 0px;\" align='right'><a href=\"javascript:autocomplete_use();\" style=\"font-size:11px; font-family:dotum; color:#95959f;\">기능켜기</a></div>");				
		$('#autocomplete').show();

	}else{

	  $.post("/common/autocomplete",{
		key : keyword
	  },function(data){
			var offset = $('input[name=search_text]').offset();

			if( offset && $('#autocomplete').length == 0) {
				$('<div id="autocomplete" style="display:none; position:absolute;"></div>').prependTo(document.body);
				$('#autocomplete').css({
					'position' : 'absolute',
					'z-index' : 999,
					'width' : '400',
					'left' : offset.left,
					'top' : offset.top+$('input[name=search_text]').height()+5,
					'background' : '#FFFFFF'
				});

				if(typeof gl_mobile_mode != 'undefined' && gl_mobile_mode){
					$('#autocomplete').css({'width' : '95%'});
				}
			}
			if(data){
				$('#autocomplete').show();
				$('#autocomplete').html(data);
				//$('#search_list > ul').html(data);
			}else{
				$('#autocomplete').hide();
			}

	  });
	}
}

function autocomplete_nouse(){
	$("#autocomplete").hide();
	$.cookie('autocomplete', 'none');
	autocomplete($('input[name=search_text]').val());
}

function autocomplete_use(){
	$("#autocomplete").hide();
	$.cookie('autocomplete', 'block');
	autocomplete($('input[name=search_text]').val());
}

	
function openDialogAll(layerid,title,url,contents_layer){
	if (document.body.scrollTop == 0) {
		var top = document.documentElement.scrollTop;
	} else {
		var top = document.body.scrollTop;
	}

	var pop = $("<div id='"+layerid+"' style='position:absolute;top:"+top+"px;background:#fff;width:100%;z-index:2;'></div>");	
	var pop_title = $('<div class="sub_division_title">'+title+'<div class="sub_division_arw" onclick="javascript:closeDialogAll(\''+layerid+'\')"></div></div>');		
	var pop_contents = "";
	
	if(url){
		$.get(url, function(data) {
			$("#"+contents_layer).html(data);
			pop_contents = $("#"+contents_layer).html();
			pop.append(pop_title);
			pop.append(pop_contents);	
			$("body").append(pop);
		});
	}else{		
		pop_contents = $("#"+contents_layer).html();
		pop.append(pop_title);
		pop.append(pop_contents);	
		$("body").append(pop);	
	}	
}

function openDialogModal(layerid,title,url,contents_layer){
	var top = document.body.scrollTop == 0 ? document.documentElement.scrollTop : document.body.scrollTop;
	$('#wrap').hide();
	$("body").append("<div id='"+layerid+"' style='position:absolute;top:0px;background:#fff;width:100%;height:100%;z-index:102'></div>");

	var pop_title = $('<div class="sub_division_title">'+title+'<div class="sub_division_arw" onclick="javascript:closeDialogAll(\''+layerid+'\')"></div></div>');		
	
	if(url){
		$.get(url, function(data) {
			$("#"+contents_layer).html(data);
			$("#"+layerid).append(pop_title,$("#"+contents_layer).html());
		});
	}else{
		$("#"+layerid).append(pop_title,$("#"+contents_layer).html());
	}
	$(window).scrollTop(0);
}

function closeDialogAll(layerid)
{
	$("#"+layerid).remove();
	$('#restock').find("select[name='viewOptionsReStock[]']").die();
	$('#wrap').show();
}

// 우편번호 다이얼로그 박스
function openDialogZipcode(zipcodeFlag,idx,ziptype,page){	
	
	var sheight = $('body').prop("scrollHeight");

	var idxtag = "";	
	$("body").append("<div id='"+zipcodeFlag+"BgId' style='position:absolute;top:0px;background:#000;width:100%;height:"+sheight+"px;z-index:101;visibility:visible;filter:alpha(opacity=30); opacity:0.3; -moz-opacity:0.3;'></div>");
	$("body").append("<div id='"+zipcodeFlag+"Id' style='position:absolute;top:0px;background:#fff;width:100%;height:100%;z-index:102'></div>");
	if(!ziptype) ziptype = 'street';
	if(idx) idxtag = "&multiIdx"+idx; 	
	var iframe = '<div class="sub_division_title">주소찾기<div class="sub_division_arw" onclick="javascript:closeDialogZipcode(\''+zipcodeFlag+'\')"></div></div>';	
	iframe += '<div style="height:100%;">';
	iframe += '<iframe id="'+zipcodeFlag+'contents_frame" name="'+zipcodeFlag+'contents_frame" src="../popup/zipcode?zipcodeFlag='+zipcodeFlag+'&zipcode_type='+ziptype+'&page='+page+'&popup=1'+idxtag+'" width="100%" height="93%" frameborder="0" allowTransparency="true"></iframe>';
	iframe += '</div>';		
	$("#"+zipcodeFlag+"Id").html(iframe);
	$(window).scrollTop(0);
}

function closeDialogZipcode(zipcodeFlag)
{
	$("#"+zipcodeFlag+"Id").remove();
	$("#"+zipcodeFlag+"BgId").remove();
}

/* 우측 퀵메뉴 기능개선 leewh 2014-06-03 */
function setRightMenu() {
	setRightClickBtn("right_item_recent",1);

	$(".rightTitleMenu")	.on("click", function(){
		if ($(this).next(".right_itemList").css("display")=="none") {
			$(".right_itemList").hide();
			setRightClickBtn($(this).parent().attr('class'),1);
		} else {
			$(this).next(".right_itemList").hide();
		}
	});

	$(".right_quick_btn_prev").on("click", function(){
		type = $(this).parent().parent().parent().attr('class');
		current_page = parseInt($("."+type+" #right_page_div .right_quick_current_page").text())-1;
		setRightClickBtn(type,current_page,'move');
		return false;
	});

	$(".right_quick_btn_next").on("click", function(){
		type = $(this).parent().parent().parent().attr('class');
		current_page = parseInt($("."+type+" #right_page_div .right_quick_current_page").text())+1;
		setRightClickBtn(type,current_page,'move');
		return false;
	});

	$("#right_cart_total").on("click", function(){
		location.href="../order/cart";
		return false;
	});

	$("#right_wish_total").on("click", function(){
		location.href="../mypage/wish";
		return false;
	});

	setRightMenuCss();
}

/* 우측 퀵메뉴 기능개선 각 메뉴 변수 설정 */
function setRightClickBtn(type, page, act){
	// 우측 스크롤 페이지 로딩시 설정한 전역변수 사용
	var right_recent = (typeof($set_right_recent)!=='undefined') ? $set_right_recent : 5;
	var right_recomm = (typeof($set_right_recomm)!=='undefined') ? $set_right_recomm : 5;
	var right_cart = (typeof($set_right_cart)!=='undefined') ? $set_right_cart : 5;
	var right_wish = (typeof($set_right_wish)!=='undefined') ? $set_right_wish : 5;
	var limit,tot_item,tot_page;
	var act = (typeof(act)!=='undefined') ? act : '';

	if (type=="right_item_recent") {
		limit = right_recent;
		tot_item = $("#right_recent_total").text();
	} else if (type=="right_item_recomm") {
		limit = right_recomm;
		tot_item = $("#right_recomm_total").text();
	} else if (type=="right_item_cart") {
		limit = right_cart;
		tot_item = $("#right_cart_total").text();
	} else if (type=="right_item_wish") {
		limit = right_wish;
		tot_item = $("#right_wish_total").text();
	}

	tot_page = (tot_item) ? Math.ceil(tot_item/limit) : 0;

	if (tot_page) {
		current_page = $("."+type+" #right_page_div .right_quick_current_page").text();
		if (tot_page) {
			if (page > tot_page) {
				page = tot_page;
			} else if (page < 1) {
				page = 1;
			}
		}

		if (act=="move" && page==current_page) {
		} else {
			getRightItemList(type,page,limit);
		}

		$("."+type+" #right_page_div .right_quick_total_page").text(tot_page);
		$("."+type+" #right_page_div .right_quick_current_page").text(page);
		$(".right_itemList").hide();
		$("."+type+" .right_itemList").show();
		$("."+type+" .right_itemList").children().show();
	} else {
		$(".right_itemList").hide();
	}
}

/* 우측 퀵메뉴 기능개선 각 메뉴 리스트 호출 */
function getRightItemList(type,page,limit) {
	$.ajax({
		'async' : false,
		'url' : '/common/get_right_display',
		'type' : 'GET',
		'data' : "type="+type+"&page="+page+"&limit="+limit,
		'success' : function(html){
			if (html) {
				$("."+type+" .right_itemList ul").html(html);
				if (type=="right_item_recent") {
					getRightItemTotal(type);
				}
				setRightBoxDetail(type);
			}
		}
	});
}

/* 우측 퀵메뉴 기능개선 장바구니,위시리스트 카운트 호출 */
function getRightItemTotal(type) {
	if (type=="right_item_cart") {
		objCnt = $("#right_cart_total");
	} else if (type=="right_item_wish") {
		objCnt = $("#right_wish_total");
	} else if (type=="right_item_recent") {
		objCnt = $("#right_recent_total");
	}

	$.ajax({
		'async' : false,
		'url' : '/common/get_right_total',
		'type' : 'GET',
		'data' : "type="+type,
		'success' : function(total){
			if (objCnt.length) {
				objCnt.text(total);
			}
		}
	});
}

/* 우측 퀵메뉴 기능개선 각 메뉴 선택 삭제(추천상품제외) */
function rightDeleteItem(type, seq, obj){
	var url,param,objCnt,page;
	var page = $("."+type+" #right_page_div .right_quick_current_page").text();

	if (type=="right_item_recent") {
		url = "/goods/goods_recent_del";
		objCnt = $("#right_recent_total");
		param = "goods_seq="+seq;
	} else if (type=="right_item_cart") {
		url = "/order/quickCartDel";
		objCnt = $("#right_cart_total");
		param = "cart_option_seq="+seq;
	} else if (type=="right_item_wish") {
		url = "/mypage/quickWishDel";
		objCnt = $("#right_wish_total");
		param = "wish_seq="+seq;
	}

	$.ajax({
		'async' : false,
		'url' : url,
		'type' : 'post',
		'data' : param,
		'success' : function(msg){
			if (msg=="ok") {
				obj.closest("li").remove();
				objCnt.text(objCnt.text()-1);
				setRightClickBtn(type,page,'del');
			}
		}
	});
}

/* 우측 퀵메뉴 기능개선 삭제버튼과 상세내용박스 */
function setRightMenuCss(){
	$('.rightQuickitemLi a').live("mouseenter", function(e) {
		objGood = $(this).parent().find('.right_quick_goods img');
		objDel = $(this).parent().find('.right_quick_btn_delete');
		objDetail = $(this).parent().parent().find('.rightQuickitemDetail');

		objGood.css("border","2px solid #acacac");
		objDetail.css({"visibility":"visible"});
		objDetail.addClass("rightQuickitemDetailCss");
		if (objDel.length) {
			objDel.css({"visibility":"visible"});
		}
	}).live("mouseleave", function(e) {
		objGood = $(this).parent().find('.right_quick_goods img');
		objDel = $(this).parent().find('.right_quick_btn_delete');
		objDetail = $(this).parent().parent().find('.rightQuickitemDetail');
		if (objDel.length) {
			objDel.css({"visibility":"hidden"});
		}

		objDetail.css({"visibility":"hidden"});
		objGood.css("border","2px solid #fff");
	});
}

/* 우측 퀵메뉴 기능개선 상품이미지 css 설정 */
function setRightBoxDetail(type){
	imgGoods = $("."+type+" .right_itemList ul li .right_quick_goods img");

	if (imgGoods.length) {
		if (window.sidebar) { //파이어폭스
			$.each(imgGoods, function(idx, item) {
				$(this).attr("src", $(this).attr("src")).load(function() {
					setRightCommonCSS($(this));
				});
			});
		} else {
			imgGoods.bind('load', function(){
				setRightCommonCSS($(this));
			});
		}
	}
}

function setRightCommonCSS(obj) {
	objGood = obj;
	objDetail = objGood.parent().parent().parent().find('.rightQuickitemDetail');
	objGood.css("border","2px solid #fff");
	objDetail.css({"border":"2px solid #acacac"});

	// 삭제버튼 위치 계산
	objDel = objGood.parent().parent().find('.right_quick_btn_delete');
	if (objDel.length) { // 추천상품 없음.
		objDelImgWidth = objDel.outerWidth();
		objDelImgPst = objDel.position().left-objDelImgWidth;
		objDel.css({"top":"0px","left":objDelImgPst+"px"});
	}

	// 상품 상세 레이어 크기와 위치 계산
	imgWidth = objGood.outerWidth();
	objDetailPst = objGood.position().left+imgWidth;
	imgHeight = objGood.height();
	detailHeight = objDetail.height();

	tempImgHeight = imgHeight;
	if (detailHeight > imgHeight) {
		tempImgHeight = detailHeight;
	}

	objDetail.css({"right":objDetailPst+"px","height":tempImgHeight+"px"});
}

if(!$.isFunction("facebookopendialog")){
	function facebookopendialog(url) {
		
		if( gl_mobile_mode ){
			var width = "350";
		}else{
			var width = "400";
		}
		var height = "250";

		var iframe = $('<iframe frameborder="0" width="0" height="0"  marginwidth="0"marginheight="0" scrolling="no" ></iframe>');
		var dialog = $("<div id='facebookoglikeId' class='facebookoglikeId' style='text-align:center'><br/><!img src='/admin/skin/default/images/design/img_progress_bar.gif'' align='absmiddle'></div>").append(iframe).appendTo("body").dialog({//
			autoOpen: false,
			modal: true,
			resizable: false,
			width: width,
			height: height,
			close: function () {
				iframe.attr("src", "");
			}
		});
		iframe.attr({
			src: url
		});
		dialog.dialog("option", "title", title).dialog("open"); 
		loadingStart($(".facebookoglikeId"),{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5});
	}
}

if(!$.isFunction("facebooklay")){
	function facebooklay(mode){
		
		if( gl_mobile_mode ){
			var width = "350";
		}else{
			var width = "400";
		}
		var height = "160";
		loadingStop($(".facebookoglikeId").find("body"),true);
		$('.facebookoglikeId').dialog("close");
		if( mode == 'unlike' ){
			openDialogAlerttitle(title,"페이스북 좋아요.<br/> 취소",width,height);
		}else if( mode == 'like' ){
			openDialogAlerttitle(title,"페이스북 좋아요.<br/> 성공",width,height);
		}else{
			openDialogAlerttitle(title,"페이스북 좋아요.<br/> 다시 시도해 주세요.",width,height);
		}
	}
}

// 가시영역 이미지로드
var header_script = document.createElement("script");
header_script.src = "/app/javascript/js/echo.js";//포함시킬 js 파일 (경로가 존재하면 경로까지 작성)
document.getElementsByTagName("head")[0].appendChild(header_script);
