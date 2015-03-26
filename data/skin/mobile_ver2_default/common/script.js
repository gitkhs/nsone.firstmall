$(function(){

	// 사이드 여닫기
	$("#layout_header a[href='#category']").click(function(){side_menu_onoff();});

	// 사이드 메뉴 여닫기
	$("#layout_side div.menu_navigation_wrap ul.menu li.mitemicon1 a").click(function(){
		if($("ul.submenu",$(this).parent()).length){
			if(!$("ul.submenu>li",$(this).parent()).length){
				$("ul.submenu",$(this).parent()).append('<li class="submitem"><a href="#">'+$(this).text()+' 목록이 없습니다</a></li>');
			}
			if($("ul.submenu",$(this).parent()).is(":visible")){
				$("ul.submenu",$(this).parent()).hide();
			}else{
				$("ul.submenu",$(this).parent()).show();
			}
			return false;
		}
	});

	// 하단퀵메뉴
	$(document)
	.bind('scroll',function(){
		if($(window).height()<$("#quick_layer").height()*3 || ($(document).height()-10 > $(window).height() && $(document).scrollTop()+$(window).height() >= $(document).height()-10)){
			$("#quick_layer").hide();
		}else{
			if(!layout_side_opened) $("#quick_layer").show();
		}
	}).trigger('scroll');
	$(window).resize(function(){$(document).scroll();});

	// 탭버튼스타일의 radio,checkbox
	$(".radio_tab_wrapper input[type='radio'], .radio_tab_wrapper input[type='checkbox']").change(function(){
		$("input[type='radio'], .radio_tab_wrapper input[type='checkbox']",$(this).closest('.radio_tab_wrapper')).each(function(){
			if($(this).is(":checked")){
				$(this).closest('td').addClass('checked');
			}else{
				$(this).closest('td').removeClass('checked');
			}
		});		
	});

	/* 탭형식 공통사용 */
	$(".sub_page_tab_wrap").each(function(){
		var wrapObj = this;
		$(".sub_page_tab td",wrapObj).each(function(i){
			$(this).click(function(){
				$(".sub_page_tab td",wrapObj).removeClass("current");
				$(this).addClass("current");
				$(".sub_page_tab_contents",wrapObj).hide().eq(i).show();
			});
		}).eq(0).click();
	});
	
	// 서브 영역 열기/닫기 
	$(".sub_division_title").click(function(){  
		var contentsObj = $(this).closest('.sub_division_title').next('.sub_division_contents');
		if(contentsObj.is(":visible")){
			$(this).addClass('closed');
			contentsObj.hide();
		}else{
			$(this).removeClass('closed');
			contentsObj.show();
		}
	});  
	$(".sub_division_arw").click(function(){  
		var contentsObj = $(this).closest('.sub_division_title').next('.sub_division_contents');
		if(contentsObj.is(":visible")){
			$(this).addClass('closed');
			contentsObj.hide();
		}else{
			$(this).removeClass('closed');
			contentsObj.show();
		}
	});  
	
});

// 서브 영역 열기/닫기
$(".sub_division_title .sub_division_arw").live('click',function(){
	var contentsObj = $(this).closest('.sub_division_title').next('.sub_division_contents');
	if(contentsObj.is(":visible")){
		$(this).addClass('closed');
		contentsObj.hide();
	}else{
		$(this).removeClass('closed');
		contentsObj.show();
	}
});


var layout_side_opened = false;
function side_menu_onoff(){

	var tHeight		= $("#layout_wrap").height();
	var sHeight		= $("#layout_side").height();
	if	(tHeight > sHeight)
		$("#layout_side").css('height', tHeight+'px');

	// 열기
	if	(!layout_side_opened){
		layout_side_opened = true;
		var orgWidth	= $("#layout_side").width();
		var headerWidth	= $("#layout_header").width() - orgWidth;
		$("#layout_side").css("left", orgWidth*-1 + 'px');
		$("#quick_layer").hide();

		$("#layout_side").show().animate({left:0}, 300, function(){});
		$("#layout_header").animate({left:orgWidth}, 300, function(){
			$("#layout_wrap").css({'position' : 'fixed', 'width':'100%'});
			$("#layout_side").css({'position' : 'relative'});
		});

		$(".designPopupBandMobile").hide();

	// 닫기
	}else{
		layout_side_opened = false;
		$("#layout_wrap").css({'position' : 'relative', 'width':'auto'});
		$("#layout_side").css({'position' : 'absolute'});

		var orgWidth	= $("#layout_side").width();
		var headerWidth	= $("#layout_header").width() + orgWidth;
		$("#layout_side").animate({left:orgWidth*-1}, 300, function(){
			$("#layout_side").hide();
		});
		$("#layout_header").animate({left:'0'}, 300, function(){
			$("#quick_layer").show();
		});

		$(".designPopupBandMobile").show();
	}
}

$(function(){
	
	/* 상품디스플레이 탭 스크립트 */
	$(".displayTabContainer").each(function(){
		var tabContainerObj = $(this);
		tabContainerObj.children('li').css('width',(100/tabContainerObj.children('li').length)+'%');
		tabContainerObj.children('li').bind('mouseover click',function(){
			tabContainerObj.children('li.current').removeClass('current');
			$(this).addClass('current');
			var tabIdx = tabContainerObj.children('li').index(this);
			tabContainerObj.closest('.designDisplay, .designCategoryRecommendDisplay').find('.displayTabContentsContainer').hide().eq(tabIdx).show();
		}).eq(0).trigger('mouseover');
		
		
	});
	
});