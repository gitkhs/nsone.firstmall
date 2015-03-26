if(DM_LOADED == undefined) var DM_LOADED;
if(DM_DESIGN_MODE == undefined) var DM_DESIGN_MODE;

var EYEEDITOR_WINDOW = null;

var DMPNListResizeBarY = 0;
var DMPNListResizeBarStartDPNListHeight = 0;
var DMPNListResizeBarResizing = false;

function DM_init(template_path){
	if(!DM_LOADED){ 
		DM_LOADED = true; 

		DM_loadingStart();

		$.ajax({
			'url' : '/admin/design/get_panel_html',
			'cache' : false,
			'async' : false,
			'data' : {'area':'init','template_path':template_path,'location':window.location.href},
			'success' : function(result){
			
				$('body').prepend(result);	
				
				$(function(){

					/* 디자인에디터모드 쿠키 */
					setDesignEditMode($.cookie('designEditMode')?true:false);
					
					// 디자인 엘리먼트 이벤트 바인딩
					setDesignElementEvent('body',template_path);

					//프로모션쿠폰 > 이벤트페이지 > 소스수정
					$(".designCouponlist").click(function(){ 
						if(DM_DESIGN_MODE){
							var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path; 
							DM_window_sourceeditor(templatePath); 
							return false;
						}
					});

					// 네비게이터 : 클릭한 폴더 열기
					$(".DMPNListDepth1ItemTitle").bind('click',function(){
						if($(this).parent().children(".DMPNListDepth2").is(':hidden')){
							$(".DMPNListDepth1Item").removeClass("opened");
							$(this).parent().addClass("opened");
						}else{
							$(".DMPNListDepth1Item").removeClass("opened");
						}
						
						$(".DMPNListDepth2Item").removeClass("controlMenuOpened");
						$("#DMPanelNavigatorControlMenu").data('template_path','').hide();

					});
					
				
					// 네비게이터 : 컨트롤메뉴 open
					controlMenuOpen = function(DMPNListDepth2Item){
						$(".DMPNListDepth2Item").removeClass("opened");
						$(".DMPNListDepth2Item").removeClass("controlMenuOpened");
						DMPNListDepth2Item.addClass("opened");
						DMPNListDepth2Item.addClass("controlMenuOpened");
						DMPanelNavigatorControlMenuOpen(DMPNListDepth2Item,DMPNListDepth2Item.attr('path'));
					}
					$("#DMPanelNavigator").bind('contextmenu',function(event){
						event.preventDefault();
					});
					$(".DMPNListDepth2Item").bind('contextmenu',function(event){
						event.preventDefault();
						controlMenuOpen($(this));
					});
					$(".DMPNListDepth2Item span.dn_icon").bind('click',function(event){
						event.preventDefault();
						controlMenuOpen($(this).closest(".DMPNListDepth2Item"));
						return false;
					});
					
					// 네비게이터 : 스크롤시 컨트롤메뉴 숨기기
					$("#DMPanelNavigator .DMPNList").scroll(function(){
						$(".DMPNListDepth2Item").removeClass("controlMenuOpened");
						$("#DMPanelNavigatorControlMenu").data('template_path','').hide();
					});

					// 네비게이터 : 컨트롤메뉴 아이템 클릭
					$("#DMPanelNavigatorControlMenu .DMPNCMItem").bind('click',function(){
						var template_path = $("#DMPanelNavigatorControlMenu").data('template_path');
						var cmd = $(this).attr('cmd');
						
						switch(cmd){
							case 'image_insert':
								DM_window_image_insert(template_path);
							break;
							case 'flash_insert':
								DM_window_flash_insert(template_path);
							break;
							case 'banner_insert':
								DM_window_banner_insert(template_path);
							break;
							case 'video_insert':
								DM_window_video_insert(template_path);
							break; 
							case 'display_insert':
								if (typeof gl_mobile_mode == 'undefined') {
									gl_mobile_mode = 0;
								}
								DM_window_display_insert(template_path,'design',gl_mobile_mode?'mobile':'pc');
							break;
							case 'lastest_insert':
								DM_window_lastest_insert(template_path);
							break;
							case 'popup_insert':
								DM_window_popup_insert(template_path);
							break;
							case 'source_edit':
								DM_window_sourceeditor(template_path);
							break;
							case 'layout_edit':
								DM_window_layout(template_path);
							break;
						
						}
					});
					
					// 네비게이터 : 디자인편집모드 On/Off
					$("#DMPanelNavigator .DMPNHSwitch").bind('click',function(){
						if(DM_DESIGN_MODE){
							setDesignEditMode(false);
						}else{
							setDesignEditMode(true);
						}
					});

					/* 디자인네비게이션에서 열려있는 파일로 스크롤 이동 */
					$(".DMPNListDepth2Item.opened").each(function(){
						
						var stop = $(this).closest(".DMPNListDepth1Item").position().top
						+ $(this).position().top;
						
						$(this).closest(".DMPNList").scrollTop(stop);
						
					});
					
					/* 디자인네비게이션 리사이징 */
					$(".DMPNListResizeBar")
					.bind('mousedown',function(e){
						DMPNListResizeBarResizing = true;
						DMPNListResizeBarStartDPNListHeight = $(".DMPNList:eq(0)").height();
						DMPNListResizeBarY = e.clientY;
					});
					$(document)
					.bind('dragstart selectstart',function(){
						if(DMPNListResizeBarResizing){
							return false;
						}
					})
					.bind('mousemove',function(e){
						if(DMPNListResizeBarResizing){
							var moveY = DMPNListResizeBarY-e.clientY;
							$(".DMPNList:eq(0)").css('height',DMPNListResizeBarStartDPNListHeight-moveY);
						}				
					})
					.bind('mouseup',function(e){
						if(DMPNListResizeBarResizing){
							var moveY = DMPNListResizeBarY-e.clientY;
							$(".DMPNList:eq(0)").css('height',DMPNListResizeBarStartDPNListHeight-moveY);
							DMPNListResizeBarResizing = false;
							
							$.cookie("DPNListHeight",DMPNListResizeBarStartDPNListHeight-moveY,{path:'/'});
						}				
					});

					$(document).bind('keydown', 'F8', function(){
						setDesignEditMode(!DM_DESIGN_MODE);
						if(!DM_DESIGN_MODE){
							$(".designElement").each(function(){
								var designElementType = $(this).attr('designElement');
								$(this).css({'opacity':'1'}).removeClass("designElementHover");
								if($(this).is("div")){
									$('.designElementEditBtn',this).css('opacity','0');
								};
							});
						}
						return false;
					});
					
					DM_loadingStop();

				});
			}
		});

	}
}

function setDesignElementEvent(wrap,template_path){
	/* 디자인편집모드일때 디자인엘리먼트(이미지,플래시,디스플레이,팝업) 이벤트 정의 */
	$("img[designElement],table[designElement],td[designElement],th[designElement],div[designElement],input[designElement]",wrap).addClass('designElement')
	.bind('mouseover',function(){
		if(DM_DESIGN_MODE){
			var designElementType = $(this).attr('designElement');
			$(this).css({'opacity':'0.5'}).addClass("designElementHover");
			if($(this).is("div")){
				$('.designElementEditBtn',this).css('opacity','0.5');
			};
		}
	})
	.bind('mouseout',function(){ 
		if(DM_DESIGN_MODE){
			var designElementType = $(this).attr('designElement');
			$(this).css({'opacity':'1'}).removeClass("designElementHover");
			if($(this).is("div")){
				$('.designElementEditBtn',this).css('opacity','0');
			};
		}
	})
	.bind('mousedown contextmenu',function(event){
		if(DM_DESIGN_MODE){
			event.preventDefault();
			var designElementType = $(this).attr('designElement');
			
			switch(designElementType){
				case 'image' :
					var designImageLabel = $(this).attr('alt') ? $(this).attr('alt') : '';
					var linkOri = this.tagName=='IMG' && $(this).parent().attr('hrefOri') ? $(this).parent().attr('hrefOri') : '';
					var link = this.tagName=='IMG' && $(this).parent().attr('href') ? $(this).parent().attr('href') : '';
					
					link = linkOri ? Base64.decode(linkOri) : link;
					var target = this.tagName=='IMG' && $(this).parent().attr('target') ? $(this).parent().attr('target') : '';
					var viewSrc = $(this).attr('src');
					DM_window_image_edit($(this).attr('designTplPath'),$(this).attr('designImgSrc'),$(this).attr('designImgSrcOri'),designImageLabel,link,this.tagName,target,viewSrc);
				break; 
				case 'video' : 
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_video_edit(templatePath,$(this).attr('videoSeq'),$(this).attr('realwidth'),$(this).attr('realheight'));
				break; 
				case 'displaylastest' : 
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_lastest_edit(templatePath,$(this).attr('id'));
				break;
				case 'flash' :
					var flashSeq = $(this).attr('flashSeq') ? $(this).attr('flashSeq') : '';
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_flash_edit(templatePath,flashSeq,true);
				break;
				case 'banner' :
					var bannerSeq = $(this).attr('bannerSeq') ? $(this).attr('bannerSeq') : '';
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_banner_edit(templatePath,bannerSeq,true);
				break;
				case 'display' :
				case 'searchGoodsDisplay' :
				case 'goodsRelationDisplay' :
				case 'goodsBigdataDisplay' : 
					var displaySeq = $(this).attr('displaySeq') ? $(this).attr('displaySeq') : '';
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					var perpage = $(this).attr('perpage') ? $(this).attr('perpage') : '';
					DM_window_display_edit(templatePath,displaySeq,true,perpage);
				break;
				/* 우측 퀵메뉴 추천상품 추가 */
				case 'goodsRecommDisplay' :
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					var elementImg = $(this).find("img");
					var designImageLabel = elementImg.attr('alt') ? elementImg.attr('alt') : '';
					var linkOri = elementImg.tagName=='IMG' && elementImg.parent().attr('hrefOri') ? elementImg.parent().attr('hrefOri') : '';
					var link = elementImg.tagName=='IMG' && elementImg.parent().attr('href') ? elementImg.parent().attr('href') : '';
					
					link = linkOri ? Base64.decode(linkOri) : link;
					var target = elementImg.tagName=='IMG' && elementImg.parent().attr('target') ? elementImg.parent().attr('target') : '';
					var viewSrc = elementImg.attr('src');

					DM_window_recomm_goods_edit(templatePath,elementImg.attr('designTplPath'),elementImg.attr('designImgSrc'),elementImg.attr('designImgSrcOri'),designImageLabel,link,elementImg.tagName,target,viewSrc);
				break;
				case 'popup' :
					var popupSeq = $(this).attr('popupSeq') ? $(this).attr('popupSeq') : '';
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_popup_edit(templatePath,popupSeq,true);
				break;
				case 'categoryNavigation' :
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_category_navigation_design(templatePath);
				break;
				case 'brandNavigation' :
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_brand_navigation_design(templatePath);
				break;
				case 'locationNavigation' :
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_location_navigation_design(templatePath);
				break;
				case 'categoryRecommendDisplay' :
					openDialogAlert("관리자모드 > 상품 > 카테고리 > 카테고리 꾸미기에서 변경하실 수 있습니다.",500,140);
				break;
				case 'categoryGoodsDisplay' :
					openDialogAlert("관리자모드 > 상품 > 카테고리 > 카테고리 꾸미기에서 변경하실 수 있습니다.",500,140);
				break;
				case 'brandRecommendDisplay' :
					openDialogAlert("관리자모드 > 상품 > 브랜드 > 브랜드 꾸미기에서 변경하실 수 있습니다.",500,140);
				break;
				case 'brandGoodsDisplay' :
					openDialogAlert("관리자모드 > 상품 > 브랜드 > 브랜드 꾸미기에서 변경하실 수 있습니다.",500,140);
				break;
				case 'locationRecommendDisplay' :
					openDialogAlert("관리자모드 > 상품 > 지역 > 지역 꾸미기에서 변경하실 수 있습니다.",500,140);
				break;
				case 'locationGoodsDisplay' :
					openDialogAlert("관리자모드 > 상품 > 지역 > 지역 꾸미기에서 변경하실 수 있습니다.",500,140);
				break;							
				case 'topBar' :
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_topBar_design(templatePath);
				break;
				case 'mainTopBar' :
					var templatePath = $(this).attr('templatePath') ? $(this).attr('templatePath') : template_path;
					DM_window_mainTopBar_design(templatePath);
				break;
			}
			return false;
		}
		
	});
}

/* 디자인에디터모드 ON/OFF */
function setDesignEditMode(flag){
	if(flag){
		DM_DESIGN_MODE = true;
		$.cookie('designEditMode',true,{path:'/'});
		$("#DMPanelNavigator .DMPNHeader").addClass('designEditMode');
		$("#DMPanelNavigatorBtn").addClass('designEditMode');

		$("div[designElement]").each(function(){
			if(!$(this).is("div[designElement='popup']")){
				$(this).css('position','relative');
			}
			$(this).append("<div class='designElementEditBtn'></div>");
		});

		$(".designElementEditBtn").css('opacity',0);
		$(".designPopupIcon").show();
	}else{
		DM_DESIGN_MODE = false;
		$.cookie('designEditMode',null,{path:'/'});
		$("#DMPanelNavigator .DMPNHeader").removeClass('designEditMode');
		$("#DMPanelNavigatorBtn").removeClass('designEditMode');
		$(".designElementEditBtn").remove();
		$(".designPopupIcon").hide();
	}
}

/* 네비게이터 메뉴 오픈 */
function DMPanelOpen(animation){
	//DM_window_close();
	
	if(animation){
		$("#DMPanelNavigator").css('margin-left','0px').show().animate({
			'margin-left' : '-170px'
		});
	}else{
		$("#DMPanelNavigator").css('margin-left','0px').show().css({
			'margin-left' : '-170px'
		});
	}
	
	$.cookie('DMPanelClosed',null,{path:'/'});
	
	$("#DMPanelNavigatorBtn").hide();
	$("#DMPanelTop").show().animate({'margin-top':'0px'});
}

/* 네비게이터 메뉴 닫기 */
function DMPanelClose(){
	$("#DMPanelNavigator").animate({
		'margin-left' : '0px'
	},function(){
		$(this).hide();
	});

	$("#DMPanelNavigatorBtn").show().css('margin-left','0px').animate({'margin-left':'-50px'},500);
	
	$.cookie('DMPanelClosed',1,{path:'/'});
	
	$("#DMPanelTop").animate({
		'margin-top':'-38px'
	},function(){
		$(this).hide();
	});
}

/* 네비게이터 컨트롤메뉴 열기 */
function DMPanelNavigatorControlMenuOpen(DMPNListDepth2Item, template_path){
	var item = $(DMPNListDepth2Item);
	var cm = $("#DMPanelNavigatorControlMenu");

	if($(DMPNListDepth2Item).attr('file_type')=='layout'){
		
		$("li",cm).each(function(){
			if(	$(this).is("li[cmd='image_insert']") || $(this).is("li[cmd='source_edit']") || $(this).is("li[cmd='flash_insert']")){
				$(this).show();
			}else{
				$(this).hide();
			}
		});
		
	}else if($(DMPNListDepth2Item).attr('file_type')=='popup'){
		
		$("li",cm).each(function(){
			if(	$(this).is("li[cmd='layout_edit']")){
				$(this).hide();
			}else{
				$(this).show();
			}
		});
		
	}else{
		$("li",cm).show();
	}
	
	if(cm.data('template_path')==template_path){
		cm.data('template_path','');
		cm.hide();
		$(DMPNListDepth2Item).removeClass("controlMenuOpened");
	}else{
		cm.data('template_path',template_path);
		cm.show();
	
		cm.css({
			'top' : item.position().top + item.closest('.DMPNListDepth1Item').position().top + item.closest('.DMPNList').position().top +  item.height()
		});
	}
}

function DM_window(title,width,height,url,options){
	$("#DMWindowTitle .DMWTTextLeft").empty();
	$("#DMWindowTitle .DMWTTextRight").empty();
	$("#DMWindowTitle .DMWTTextCenter .prefix").empty();
	$("#DMWindowTitle .DMWTTextCenter .title").html(title);
	
	$("#DMWindow")
	.draggable({ iframeFix: true, opacity: 0.7  });
	
	
	$("#DMWindowBody").css({
		'width' : width,
		'height' : height
	});
	
	if(url){
		$("#DMWindowBodyIframe").attr('src',url).css('opacity','0').one('load',function(){
			$("#DMWindowBodyIframe").css('opacity',1);
		});
	}

	if(options && options['alone']){
		DMPanelClose();
		$("#DMWindow").data('alone',true);

		$("#DMWindow").css({
			'left' : '50%',
			'top' : '50%',
			'margin-left' : $("#DMWindow").width()/2*-1,
			'margin-top' : $("#DMWindow").height()/2*-1
		});
	}else if(options && options['fullscreen']){
		DMPanelClose();
		$("#DMWindow").data('alone',true);

		$("#DMWindow").css({
			'left' : '0',
			'top' : '0',
			'margin-left' : 0,
			'margin-top' : 0
		});
		
		$("#DMWindowBody").css({
			'width' : $(window).width()-10,
			'height' :$(window).height()-$("#DMWindowTitle").height()-10
		});
	}else{
		DMPanelOpen(false);
		$("#DMWindow").data('alone',false);

		$("#DMWindow").css({
			'left' : '100%',
			'top' : $("#DMPanelNavigator").css('top'),
			'margin-left' : parseInt($("#DMPanelNavigator").css('margin-left')) - $("#DMWindow").outerWidth() - 5,
			'margin-top' : 0
		
		});
	}

	$("body").css('overflow','hidden');
	
	$("#DMWindow").show();
	$("#DMModalBackground").css('opacity','0.5').show();
}

/* 디자인창 : 소스에디터 */
function DM_window_sourceeditor(tpl_path,searchKeyword){
	DM_window_eyeeditor("data/skin/"+designWorkingSkin+"/"+tpl_path,searchKeyword);
	//DM_window("<span class='source_edited_mark' style='display:none'>*</span>소스편집",950,600,"/admin/design/sourceeditor?tpl_path="+encodeURIComponent(tpl_path)+"&searchKeyword="+searchKeyword,{'fullscreen':true});
}

/* 디자인창 : 아이에디터 */
function DM_window_eyeeditor(template_path,searchKeyword){
	var queryString = template_path ? "?template_path="+encodeURIComponent(template_path?template_path:'') : "";

	searchKeyword = typeof searchKeyword == 'undefined' ? '' : searchKeyword;
	queryString = queryString ? queryString+"&searchKeyword="+encodeURIComponent(searchKeyword) : "?searchKeyword="+encodeURIComponent(searchKeyword);

	if(!EYEEDITOR_WINDOW || (EYEEDITOR_WINDOW && (typeof EYEEDITOR_WINDOW.document == 'unknown' || typeof EYEEDITOR_WINDOW.document == 'undefined' || EYEEDITOR_WINDOW.top == null))){
		EYEEDITOR_WINDOW = window.open("/admin/design/eye_editor"+queryString,"eyceditorWindow","width=1010,height=650,scrollbar=no,resizable=yes");
	}else{
		//EYEEDITOR_WINDOW.document.focus();
		EYEEDITOR_WINDOW.focus();
		EYEEDITOR_WINDOW.searchKeyword = searchKeyword;
		EYEEDITOR_WINDOW.addTabItem(template_path);
	}
}

/* 디자인창 : 레이아웃설정 */
function DM_window_layout(tpl_path){
	DM_window('레이아웃/폰트/배경색 설정',1000,650,'/admin/design/layout?mode=edit&tpl_path='+tpl_path);
}

/* 디자인창 : 새페이지만들기&레이아웃설정 */
function DM_window_layout_create(tpl_path){
	DM_window('새 페이지 만들기',950,600,'/admin/design/layout?mode=create&tpl_path='+tpl_path)
}

/* 디자인창 : 전체페이지 */
function DM_window_allpages(tpl_path){
	DM_window('전체페이지',1000,600,'/admin/design/all_pages?tpl_path='+tpl_path,{'alone':true})
}

/* 디자인창 : 이미지넣기 */
function DM_window_image_insert(designTplPath){
	var title = '이미지 넣기';
	DM_window(title,1000,600,'/admin/design/image_insert?designTplPath='+encodeURIComponent(designTplPath))
}

/* 디자인창 : 이미지변경 */
function DM_window_image_edit(designTplPath,designImgSrc,designImgSrcOri,designImageLabel,link,elementType,target,viewSrc){
	var title = '이미지 변경';
	if(link.length>0) link = encodeURIComponent(encodeURIComponent(link)); // 두번 encode
	DM_window(title,1000,600,'/admin/design/image_edit?designTplPath='+designTplPath+'&designImgSrc='+designImgSrc+'&designImgSrcOri='+designImgSrcOri+'&designImageLabel='+encodeURIComponent(designImageLabel)+'&link='+link+'&elementType='+elementType+'&target='+target+'&viewSrc='+viewSrc)
}

/* 디자인창 : 플래시넣기 */
function DM_window_flash_insert(template_path){
	var title = '플래시 넣기';
	DM_window(title,1000,600,'/admin/design/flash_insert?template_path='+encodeURIComponent(template_path))
}

/* 디자인창 : 플래시변경 */
function DM_window_flash_edit(template_path,flashSeq,direct){
	var title = '플래시 변경';
	DM_window(title,1000,600,'/admin/design/flash_edit?template_path='+encodeURIComponent(template_path)+'&direct='+(direct?1:0)+'&flash_seq='+flashSeq)
}

/* 디자인창 : 플래시만들기 */
function DM_window_flash_create(template_path){
	var title = '플래시 만들기';
	DM_window(title,950,600,'/admin/design/flash_create?template_path='+encodeURIComponent(template_path));
}

/* 디자인창 : 슬라이드 배너만들기 */
function DM_window_banner_create(template_path){
	var title = '슬라이드 배너 만들기';
	DM_window(title,1000,600,'/admin/design/banner_edit?template_path='+encodeURIComponent(template_path))
}

/* 디자인창 : 슬라이드 배너넣기 */
function DM_window_banner_insert(template_path){
	var title = '슬라이드 배너 넣기';
	DM_window(title,1000,600,'/admin/design/banner_insert?template_path='+encodeURIComponent(template_path))
}

/* 디자인창 : 슬라이드 배너변경 */
function DM_window_banner_edit(template_path,bannerSeq,direct){
	var title = '슬라이드 배너 변경';
	DM_window(title,1000,600,'/admin/design/banner_edit?template_path='+encodeURIComponent(template_path)+'&direct='+(direct?1:0)+'&banner_seq='+bannerSeq)
}

/* 디자인창 : 동영상넣기 */
function DM_window_video_insert(template_path){
	var title = '동영상 넣기';
	DM_window(title,1000,600,'/admin/design/video_insert?template_path='+encodeURIComponent(template_path))
}
 
/* 디자인창 : 동영상변경 */
function DM_window_video_edit(template_path, videoSeq, realwidth, realheight ){
	var title = '동영상 변경';
	DM_window(title,1000,500,'/admin/design/video_edit?template_path='+encodeURIComponent(template_path)+'&videoSeq='+videoSeq+'&realwidth='+realwidth+'&realheight='+realheight)
}
/* 디자인창 : 팝업띄우기 */
function DM_window_popup_insert(template_path){
	var title = '띠배너/팝업 띄우기';
	DM_window(title,1000,600,'/admin/design/popup_insert?template_path='+encodeURIComponent(template_path))
}

/* 디자인창 : 팝업수정&생성 */
function DM_window_popup_edit(template_path,popup_seq,direct){
	var title = '띠배너/팝업 만들기';
	DM_window(title,1000,600,'/admin/design/popup_edit?template_path='+encodeURIComponent(template_path)+'&direct='+(direct?1:0)+'&popup_seq='+(popup_seq?popup_seq:''));
}
  

/* 디자인창 : 상품디스플레이 띄우기 */
function DM_window_display_insert(template_path, displaykind, displayplatform) {
	var title = '상품디스플레이'; 
	var dispalymode = (displaykind)?displaykind:'design';
	var platform = (displayplatform)?displayplatform:'pc';
	DM_window(title,1000,650,'/admin/design/display_insert?displaykind='+dispalymode+'&platform='+platform+'&template_path='+encodeURIComponent(template_path))
} 

/* 디자인창 : 상품디스플레이 수정&생성 */
function DM_window_display_edit(template_path,display_seq,direct,perpage, displaykind, displayplatform){
	var title = '상품디스플레이';
	var dispalymode = (displaykind)?displaykind:'design'; 
	var platform = (displayplatform)?displayplatform:'pc';
	DM_window(title,1000,600,'/admin/design/display_edit?template_path='+encodeURIComponent(template_path)+'&direct='+(direct?1:0)+'&displaykind='+dispalymode+'&platform='+platform+'&perpage='+(perpage?perpage:'')+'&display_seq='+(display_seq?display_seq:''));
}

/* 디자인창 : 카테고리 네비게이션 디자인 */
function DM_window_category_navigation_design(template_path){
	var title = '카테고리 네비게이션 디자인';
	DM_window(title,1000,420,'/admin/design/category_navigation_design?template_path='+encodeURIComponent(template_path));
}

/* 디자인창 : 브랜드 네비게이션 디자인 */
function DM_window_brand_navigation_design(template_path){
	var title = '브랜드 네비게이션 디자인';
	DM_window(title,1000,420,'/admin/design/brand_navigation_design?template_path='+encodeURIComponent(template_path));
}

/* 디자인창 : 추천상품 수정&생성 */
function DM_window_recomm_goods_edit(template_path,designTplPath,designImgSrc,designImgSrcOri,designImageLabel,link,elementType,target,viewSrc){
	var title = '추천상품';
	DM_window(title,1000,600,'/admin/design/recomm_goods_edit?template_path='+encodeURIComponent(template_path)+'&designTplPath='+designTplPath+'&designImgSrc='+designImgSrc+'&designImgSrcOri='+designImgSrcOri+'&designImageLabel='+encodeURIComponent(designImageLabel)+'&link='+encodeURIComponent(link)+'&elementType='+elementType+'&target='+target+'&viewSrc='+viewSrc);
}

/* 디자인창 : 지역 네비게이션 디자인 */
function DM_window_location_navigation_design(template_path){
	var title = '지역 네비게이션 디자인';
	DM_window(title,1000,420,'/admin/design/location_navigation_design?template_path='+encodeURIComponent(template_path));
}

/* 디자인창 : 게시판넣기 >> 사이즈변경 */
function DM_window_lastest_insert(template_path){
	var title = '게시판 넣기';
	DM_window(title,1000,600,'/admin/design/lastest_insert?template_path='+encodeURIComponent(template_path))
}

	
/* 디자인창 : 게시판넣기변경 */
function DM_window_lastest_edit(designTplPath,designLastestId){
	var title = '게시판 변경';
	DM_window(title,630,100,'/admin/design/lastest_edit?designTplPath='+designTplPath+'&designLastestId='+designLastestId)
}

/* 디자인창 : pc Quick 디자인 */
function DM_window_pc_quick_design(template_path){
	DM_window('PC버튼 Quick 디자인',1100,650,'/admin/design/pc_quick_design?tpl_path='+template_path)
}

/* 디자인창 : 모바일 Quick 디자인 */
function DM_window_mobile_quick_design(template_path){
	DM_window('모바일 Quick 디자인',950,650,'/admin/design/mobile_quick_design?tpl_path='+template_path)
}

/* 디자인창 : PC 상단바 */
function DM_window_topBar_design(template_path){
	DM_window('상단바(_modules/category/category_topBar.html) 변경',800,250,'/admin/design/topBar_design?template_path='+encodeURIComponent(template_path));
}

/* 디자인창 : 모바일 상단바 */
function DM_window_mainTopBar_design(template_path){
	DM_window('메인 상단바(_modules/common/topbar.html) 변경',900,700,'/admin/design/mainTopBar_design?template_path='+encodeURIComponent(template_path));
}

/* 디자인창 : 닫기 */
function DM_window_close(){
	if($("#DMWindow").data('alone')){
		$("#DMPanelNavigatorBtn").show();
	}
	$("#DMWindow").hide();	
	$("#DMModalBackground").hide();
	
	$("body").css('overflow','');
}

function DM_window_title_set(type, html){
	switch(type){
		case "left":
			$("#DMWindow #DMWindowTitle .DMWTTextLeft").html(html);
		break;
		case "right":
		break;
			$("#DMWindow #DMWindowTitle .DMWTTextRight").html(html);
		case "center":
			$("#DMWindow #DMWindowTitle .DMWTTextCenter .prefix").html(html);
		break;
		case "title":
			$("#DMWindow #DMWindowTitle .DMWTTextCenter .title").html(html);
		break;
	}
}

function DM_loadingStart(){
	/*
	var DMIndicatorId = 'DMIndicator';	
	if($("#"+DMIndicatorId).length){
		$("#"+DMIndicatorId).show();
	}else{
		var html = "";
		html += "<div id='"+DMIndicatorId+"' style='position:fixed;left:50%;top:50%;width:190px;height:162px;margin-left:-95px;margin-top:-81px;background:url(\"/app/javascript/plugin/images/preloader_bg.png\") no-repeat;text-align:center;'>";
		html += "<div style='padding-top:30px;'><img src='/app/javascript/plugin/images/preloader.gif' /></div>";
		html += "<div style='padding-top:17px;color:#fff;font-size:11px;letter-spacing:-1px;'>로딩중입니다.</div>";
		html += "<div style='padding-top:3px;color:#fff;font-size:11px;letter-spacing:-1px;'>(관리자에게만 로딩 표시가 나타남)</div>";
		html += "</div>";
		$(html).appendTo($('body'));
	}
	*/
}

function DM_loadingStop(){
	/*
	var DMIndicatorId = 'DMIndicator';
	$("#"+DMIndicatorId).hide();
	*/	
}