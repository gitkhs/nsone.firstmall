/* 전체 카테고리 버튼 스크립트 */
function setCategoryAllBtnEvent(categoryNavigationKey,url,target){
	target = (target != undefined && target != "") ? target : categoryNavigationKey;
	$("#"+categoryNavigationKey+" .categoryAllBtn").live('click',function(){
		if($("#"+target+" .categoryAllContainer").html()==""){
			$(".categoryAll").hide();
			$("#"+target+" .categoryAllContainer").load(url,function(){
				$("#"+target+" .categoryAll").stop(true,true).slideDown('fastest');
			});
		}else{
			if($("#"+target+" .categoryAll").stop(true,true).is(":visible")){
				$("#"+target+" .categoryAll").slideUp('fastest');
			}else{
				$(".categoryAll").hide();
				$("#"+target+" .categoryAll").slideDown('fastest');
			}
		}
		return false;
	});
	$("#"+target+" .categoryAllClose").live('click',function(){
		$("#"+target+" .categoryAll").stop(true,true).slideUp('fastest');
	});
}

/* 전체 브랜드 버튼 스크립트 */
function setBrandAllBtnEvent(categoryNavigationKey,url,target){
	target = (target != undefined && target != "") ? target : categoryNavigationKey;
	$("#"+categoryNavigationKey+" .categoryAllBtn").live('click',function(){
		if($("#"+target+" .categoryAllContainer").html()==""){
			$(".categoryAll").hide();
			$("#"+target+" .categoryAllContainer").load(url,function(){
				$("#"+target+" .categoryAll").stop(true,true).slideDown('fastest');

				$("#"+target+" .categoryAllBrandListGroup").each(function(){
					if($(".categoryAllBrandListGroupItem",this).length){
						$(this).show();
					}else{
						$(this).hide();
					}
				});
			});
		}else{
			if($("#"+target+" .categoryAll").stop(true,true).is(":visible")){
				$("#"+target+" .categoryAll").slideUp('fastest');
			}else{
				$(".categoryAll").hide();
				$("#"+target+" .categoryAll").slideDown('fastest');
			}
		}
		return false;
	});
	$("#"+target+" .categoryAllClose").live('click',function(){
		$("#"+target+" .categoryAll").stop(true,true).slideUp('fastest');
	});
	$("#"+target+" .brandPrefixBtn").live('click',function(){
		$("#"+target+" .brandPrefixBtn.current").removeClass("current");
		$(this).addClass("current");
		var prefix = $(this).attr("prefix");
		if(prefix=="all"){
			$("#"+target+" .categoryAllBrandListGroup").each(function(){
				if($(".categoryAllBrandListGroupItem",this).length){
					$(this).show();
				}else{
					$(this).hide();
				}
			});
			//$("#"+target+" .categoryAllBrandListGroup").show();
		}else{
			$("#"+target+" .categoryAllBrandListGroup").hide().filter("*[prefix='"+prefix+"']").show();
		}
	});
}

/* 전체 지역 버튼 스크립트 */
function setLocationAllBtnEvent(locationNavigationKey,url,target){
	target = (target != undefined && target != "") ? target : locationNavigationKey;
	$("#"+locationNavigationKey+" .categoryAllBtn").live('click',function(){
		if($("#"+target+" .categoryAllContainer").html()==""){
			$(".categoryAll").hide();
			$("#"+target+" .categoryAllContainer").load(url,function(){
				$("#"+target+" .categoryAll").stop(true,true).slideDown('fastest');
			});
		}else{
			if($("#"+target+" .categoryAll").stop(true,true).is(":visible")){
				$("#"+target+" .categoryAll").slideUp('fastest');
			}else{
				$(".categoryAll").hide();
				$("#"+target+" .categoryAll").slideDown('fastest');
			}
		}
		return false;
	});
	$("#"+target+" .categoryAllClose").live('click',function(){
		$("#"+target+" .categoryAll").stop(true,true).slideUp('fastest');
	});
}

$(function(){
	
	/* 상품디스플레이 탭 스크립트 */
	$(".displayTabContainer").each(function(){
		var tabContainerObj = $(this);
		tabContainerObj.children('li').bind('mouseover',function(){
			tabContainerObj.children('li.current').removeClass('current');
			$(this).addClass('current');
			var tabIdx = tabContainerObj.children('li').index(this);
			tabContainerObj.closest('.designDisplay, .designCategoryRecommendDisplay').find('.displayTabContentsContainer').hide().eq(tabIdx).show();
		}).eq(0).trigger('mouseover');
		
		
	});
	
});