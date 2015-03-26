var designDisplaySelector="div.designDisplay, div.designCategoryGoodsDisplay, div.designCategoryRecommendDisplay, div.designSearchGoodsDisplay";

$(function(){
	/* 어드민 */
	if(typeof isAdminpage != 'undefined' && isAdminpage == true){
		$(".goodsDisplayDecorationContainer").each(function(){
			var goodsDisplayDecorationContainer = this;
			
			/* 샘플이미지 : 사이즈변경 */
			$("select.image_size",goodsDisplayDecorationContainer).change(function(){
				var image_size = $(this).val();
				$(".goodsDisplayImageWrap img, .goodsDisplayImageTable",goodsDisplayDecorationContainer).css({"width":$("option:selected",this).attr('width'),"height":$("option:selected",this).attr('height')});
				goods_image_decoration_data(goodsDisplayDecorationContainer);
			}).change();
			
			/* 이미지꾸미기 - 오버레이 입력폼 show,hide 효과 */
			$("select.image_overay1, select.image_overay2",goodsDisplayDecorationContainer).change(function(){
				if($(this).val()=='') $(this).parent().children(".image_overay_text").show();
				else $(this).parent().children(".image_overay_text").hide();		
			}).change();
			
			/* 이미지꾸미기 - 아이콘 선택창 열기 버튼  */
			$(".image_icon_select",goodsDisplayDecorationContainer).bind("click",function(){
				var uniqueKey = "image_icon_select_"+Math.floor(Math.random()*10000000);
				$(this).attr('uniqueKey',uniqueKey);
				$("#displayImageIconPopup input[name='uniqueKey']").val(uniqueKey);
				$("#displayImageIconPopup input[name='subPath']").val('');
				set_display_image_icon();
				openDialog("아이콘 선택  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayImageIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
			});
			
			/* 이미지꾸미기 - 보내기아이콘 선택창 열기 버튼  */
			$(".image_send_select",goodsDisplayDecorationContainer).bind("click",function(){
				var uniqueKey = "image_send_select_"+Math.floor(Math.random()*10000000);
				$(this).attr('uniqueKey',uniqueKey);
				$("#displayImageIconPopup input[name='uniqueKey']").val(uniqueKey);
				$("#displayImageIconPopup input[name='subPath']").val('send');
				set_display_image_send();
				openDialog("아이콘 선택  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayImageIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
			});
			
			/* 이미지꾸미기 - 찜 아이콘 선택창 열기 버튼  */
			$(".image_zzim_select",goodsDisplayDecorationContainer).bind("click",function(){
				var uniqueKey = "image_zzim_select_"+Math.floor(Math.random()*10000000);
				$(this).attr('uniqueKey',uniqueKey);
				$("#displayImageIconPopup input[name='uniqueKey']").val(uniqueKey);
				$("#displayImageIconPopup input[name='subPath']").val('zzim');
				set_display_image_zzim();
				openDialog("아이콘 선택  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayImageIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
			});
			
			/* 이미지꾸미기 - 찜 아이콘 선택창 열기 버튼  */
			$(".image_zzim_on_select",goodsDisplayDecorationContainer).bind("click",function(){
				var uniqueKey = "image_zzim_on_select_"+Math.floor(Math.random()*10000000);
				$(this).attr('uniqueKey',uniqueKey);
				$("#displayImageIconPopup input[name='uniqueKey']").val(uniqueKey);
				$("#displayImageIconPopup input[name='subPath']").val('zzim_on');
				set_display_image_zzim_on();
				openDialog("아이콘 선택  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayImageIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
			});
			
			/* 이미지꾸미기 - 이미지더보기 아이콘 선택창 열기 버튼  */
			$(".image_slide_select",goodsDisplayDecorationContainer).bind("click",function(){
				var uniqueKey = "image_slide_select_"+Math.floor(Math.random()*10000000);
				$(this).attr('uniqueKey',uniqueKey);
				$("#displayImageIconPopup input[name='uniqueKey']").val(uniqueKey);
				$("#displayImageIconPopup input[name='subPath']").val('slide');
				set_display_image_slide();
				openDialog("아이콘 선택  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayImageIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
			});
			
			/* 이미지꾸미기 - 퀵바 아이콘 팝업 열기 버튼  */
			$("table.quick_shopping_container",goodsDisplayDecorationContainer).bind("click",function(){
				var uniqueKey = "quick_icon_select_"+Math.floor(Math.random()*10000000);
				$(this).attr('uniqueKey',uniqueKey);
				$("#displayQuickIconPopup input[name='uniqueKey']").val(uniqueKey);
				$("#displayQuickIconPopup .quick_shopping_icon_img").each(function(){
					var randKey = Math.floor(Math.random()*10000000);				
					$(this).attr('src',$(this).attr('src').split('#')[0]+'#'+randKey);
				});
				
				var quick_shopping = eval("("+$(this).closest('.image_decoration_table').find("input[name='quick_shopping']").val()+")");
				for(var i=0;i<quick_shopping.length;i++){
					$("#displayQuickIconPopup input[name='quick_shopping_icon[]'][value='"+quick_shopping[i]+"']").attr('checked',true);
				}
				
				openDialog("빠른 쇼핑 이미지 변경  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayQuickIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
			});
			
			/* 이미지꾸미기 빠른쇼핑 이미지 */
			$("input[name='quick_shopping']",goodsDisplayDecorationContainer).change(function(){
				$("table.quick_shopping_container tr",$(this).closest('tr')).empty();
				var data = eval("("+$(this).val()+")");
				var randKey = Math.floor(Math.random()*10000000);

				for(var i=0;i<data.length;i++){
					if(i=='zzim_on') return;
					if(i=='zzim'){
						$("table.quick_shopping_container tr",$(this).closest('tr')).append("<td width='16'><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+data[i]+".gif#"+randKey+"' /></td>");
					}else{
						$("table.quick_shopping_container tr",$(this).closest('tr')).append("<td><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+data[i]+".gif#"+randKey+"' /></td>");
					}
				}
						
			}).change();
			
			/* 이미지꾸미기 - 체크박스값에 따른 disabled 처리 */
			$(".image_decorate_chk",goodsDisplayDecorationContainer).change(function(){
				var image_decoration_table = $(this).closest('.image_decoration_table');
				if($(this).is(":checked") || $(".image_decorate_chk:checked",image_decoration_table).length){
					$(this).closest(".image_decoration_table").find("*").not(".image_decorate_chk").filter(function(){return $(this).is("select") || $(this).children().length==0;}).css('opacity',1).removeAttr('disabled');
				}else{
					$(this).closest(".image_decoration_table").find("*").not(".image_decorate_chk").filter(function(){return $(this).is("select") || $(this).children().length==0;}).css('opacity',0.3).attr('disabled',true);
				}
			}).change();
		
			/* 이미지꾸미기 선택정보를 샘플이미지에 적용*/
			$(".image_decoration_table select, .image_decoration_table input",goodsDisplayDecorationContainer).change(function(){
				goods_image_decoration_data(goodsDisplayDecorationContainer);
			});
			
			/* 상품정보 텍스트정렬을 샘플이미지에 적용 */
			$("input.text_align",goodsDisplayDecorationContainer).change(function(){
				if($(this).is(':checked')){
					$(".goodsDisplayAlign",goodsDisplayDecorationContainer).css("text-align",$(this).val());
				}
			}).change();
			
			/* 상품정보 항목 값 변경 */
			$("div.info_item .info_item_selector",goodsDisplayDecorationContainer).live('change',function(){ 
				change_info_item(this);
			});
		
			/* 상품정보 항목 Sortable */
			$(".info_item_container",goodsDisplayDecorationContainer).sortable({
					placeholder: "info_item_holder"
			});
			
			$(".image_slide_type",goodsDisplayDecorationContainer).live("change",function(){
				$(".image_slide_sample_bottom, .image_slide_sample_right",goodsDisplayDecorationContainer).hide();
				$(".image_slide_sample_"+$(this).val(),goodsDisplayDecorationContainer).show();
			}).change();
			
			set_info_item_data(goodsDisplayDecorationContainer);
		});
	
		/* 어드민 - 이미지꾸미기 - 아이콘 선택 */
		$("#displayImageIconPopup img.icon").live("click",function(){
			var uniqueKey = $("#displayImageIconPopup input[name='uniqueKey']").val();
			var subPath = $("#displayImageIconPopup input[name='subPath']").val();

			switch(subPath){
				case "send":
					$(".image_send_select[uniqueKey='"+uniqueKey+"']").attr("src",$(this).attr('src'));
					var selectedIconName = $(this).attr("src").replace("/data/icon/goodsdisplay/send/","");
					$(".image_send_select[uniqueKey='"+uniqueKey+"']").closest('table').find('input.image_send').val(selectedIconName).change();
				break;
				case "zzim":
					$(".image_zzim_select[uniqueKey='"+uniqueKey+"']").attr("src",$(this).attr('src'));
					var selectedIconName = $(this).attr("src").replace("/data/icon/goodsdisplay/zzim/","");
					$(".image_zzim_select[uniqueKey='"+uniqueKey+"']").closest('table').find('input.image_zzim').val(selectedIconName).change();
				break;
				case "zzim_on":
					$(".image_zzim_on_select[uniqueKey='"+uniqueKey+"']").attr("src",$(this).attr('src'));
					var selectedIconName = $(this).attr("src").replace("/data/icon/goodsdisplay/zzim_on/","");
					$(".image_zzim_on_select[uniqueKey='"+uniqueKey+"']").closest('table').find('input.image_zzim_on').val(selectedIconName).change();
				break;
				case "slide":
					$(".image_slide_select[uniqueKey='"+uniqueKey+"']").attr("src",$(this).attr('src'));
					var selectedIconName = $(this).attr("src").replace("/data/icon/goodsdisplay/slide/","");
					$(".image_slide_select[uniqueKey='"+uniqueKey+"']").closest('table').find('input.image_slide').val(selectedIconName).change();
				break;
				default:
					$(".image_icon_select[uniqueKey='"+uniqueKey+"']").attr("src",$(this).attr('src'));
					var selectedIconName = $(this).attr("src").replace("/data/icon/goodsdisplay/","");
					$(".image_icon_select[uniqueKey='"+uniqueKey+"']").closest('table').find('input.image_icon').val(selectedIconName).change();
				break;
			}
			
			closeDialog("displayImageIconPopup");
		});	
	}
	
	/* 이미지꾸미기 이미지 설정 */
	set_goods_display_decoration(".goodsDisplayImageWrap");

	/* 이미지꾸미기 이미지 마우스오버 이벤트 설정 */
	set_goods_display_decoration_event(".goodsDisplayImageWrap");
});

/* 어드민 - 이미지꾸미기 - 아이콘 선택후 콜백 */
function set_display_image_icon(){	
	$.getJSON('../design/display_image_icon', function(data) {
		var tag = '';
		$("div#displayImageIconPopup ul li").remove();	

		for(var i=0;i<data.length;i++){			
			tag += '<li style="float:left;width:100px;height:80px;text-align:center">';
			tag += '<img src="/data/icon/goodsdisplay/'+data[i]+'" border="0" class="icon hand hover-select">';					
			tag += '</li>';
		}
		$("div#displayImageIconPopup ul").html(tag);
	});	
}

/* 어드민 - 이미지꾸미기 - 빠른쇼핑 아이콘 선택후 콜백 */
function set_display_quick_icon(quick_shopping_value){
	var uniqueKey = $("#displayQuickIconPopup input[name='uniqueKey']").val();
	$("table.quick_shopping_container[uniqueKey='"+uniqueKey+"']").closest('.image_decoration_table').find("input[name='quick_shopping']").val(quick_shopping_value).change();
	closeDialog('displayQuickIconPopup');
	
	$("div#displayQuickIconPopup form input[type='text']").val('');
}

/* 어드민 - 이미지꾸미기 - 아이콘 선택후 콜백 */
function set_display_image_send(){	
	$.getJSON('../design/display_image_send', function(data) {
		var tag = '';
		$("div#displayImageIconPopup ul li").remove();	

		for(var i=0;i<data.length;i++){			
			tag += '<li style="float:left;width:100px;height:80px;text-align:center">';
			tag += '<img src="/data/icon/goodsdisplay/send/'+data[i]+'" border="0" class="icon hand hover-select">';					
			tag += '</li>';
		}
		$("div#displayImageIconPopup ul").html(tag);
	});	
}

/* 어드민 - 이미지꾸미기 - 아이콘 선택후 콜백 */
function set_display_image_zzim(){	
	$.getJSON('../design/display_image_zzim', function(data) {
		var tag = '';
		$("div#displayImageIconPopup ul li").remove();	

		for(var i=0;i<data.length;i++){			
			tag += '<li style="float:left;width:100px;height:80px;text-align:center">';
			tag += '<img src="/data/icon/goodsdisplay/zzim/'+data[i]+'" border="0" class="icon hand hover-select">';					
			tag += '</li>';
		}
		$("div#displayImageIconPopup ul").html(tag);
	});	
}

/* 어드민 - 이미지꾸미기 - 아이콘 선택후 콜백 */
function set_display_image_zzim_on(){	
	$.getJSON('../design/display_image_zzim_on', function(data) {
		var tag = '';
		$("div#displayImageIconPopup ul li").remove();	

		for(var i=0;i<data.length;i++){			
			tag += '<li style="float:left;width:100px;height:80px;text-align:center">';
			tag += '<img src="/data/icon/goodsdisplay/zzim_on/'+data[i]+'" border="0" class="icon hand hover-select">';					
			tag += '</li>';
		}
		$("div#displayImageIconPopup ul").html(tag);
	});	
}

/* 어드민 - 이미지더보기 - 아이콘 선택후 콜백 */
function set_display_image_slide(){	
	$.getJSON('../design/display_image_slide', function(data) {
		var tag = '';
		$("div#displayImageIconPopup ul li").remove();	

		for(var i=0;i<data.length;i++){			
			tag += '<li style="float:left;width:100px;height:80px;text-align:center">';
			tag += '<img src="/data/icon/goodsdisplay/slide/'+data[i]+'" border="0" class="icon hand hover-select">';					
			tag += '</li>';
		}
		$("div#displayImageIconPopup ul").html(tag);
	});	
}
/* 이미지꾸미기 이미지 설정 */
function set_goods_display_decoration(selector){
	
	$(selector)
	.each(function(){
		
		var that = this;
		var imageObj = $(this).find("img:eq(0)");
		var designDisplayObj = $(that).closest(designDisplaySelector);

		if($(this).data('decorationLoaded') || !$(this).attr("decoration") || $(this).attr("decoration")=="e30="){
			return;
		}

		$(this).wrap("<div class='relative' />")
		$(this).css({'position':'relative','display':'inline-block'})
		
		// 속도를 위해 상품디스플레이객체에서 이미지꾸미기설정을 가져옴
		if(designDisplayObj.find(selector).index(that)>0){
			var decoration = designDisplayObj.data("decoration");
			$(that).data("decoration",decoration);
		}
		else{
			if($(that).attr("decoration")) $(that).data("decoration",eval("("+Base64.decode($(that).attr("decoration"))+")"));
			designDisplayObj.data("decoration",$(that).data("decoration"));
			var decoration = $(that).data("decoration");
		}
		
		if($(that).attr("goodsInfo")) $(that).data("goodsInfo",eval("("+Base64.decode($(that).attr("goodsInfo"))+")"));
		var goodsInfo = $(that).data("goodsInfo");

		/*
		if(goodsInfo.goods_status=='runout'){
			
			if($(".goodsDisplayImageOveraySoldout",this).length){
				$(".goodsDisplayImageOveraySoldout",this).remove();
			}

			var overaySoldoutObj = $("<div class='goodsDisplayImageOveraySoldout'><div class='goodsDisplayImageOveraySoldoutBg'></div><div class='goodsDisplayImageOveraySoldoutText'></div></div>");
			
			var overaySoldoutHeight = 75;

			overaySoldoutObj
			.css({
				'position'			: 'absolute',
				'left'				: 0,
				'top'				: '100%',
				'margin-top'		: -overaySoldoutHeight,
				'width'				: $(that).width(),
				'height'			: overaySoldoutHeight
			});
			
			$(".goodsDisplayImageOveraySoldoutBg",overaySoldoutObj)
			.css({
				//'background-color'	: '#ff0000',
				'color'				: '#fff',
				'opacity'			: 0.3,
				'position'			: 'absolute',
				'left'				: 0,
				'top'				: 0,
				'width'				: $(that).width(),
				'height'			: overaySoldoutHeight
			});

			$(".goodsDisplayImageOveraySoldoutText",overaySoldoutObj)
			.css({
				'background'		: "url('/data/icon/common/soldout_mark.png') no-repeat top right",
				'width'				: '81px',
				'height'			: '71px',
				'position'			: 'absolute',
				'overflow'			: 'hidden',
				'white-space'		: 'nowrap',
				'right'				: 0,
				'top'				: 0,
				'width'				: $(that).width()
			});
			overaySoldoutObj.show();

			imageObj.after(overaySoldoutObj);

		}
		*/
		
		if(decoration){
			// 상품디스플레이 속도개선(20141110) 이후 소스
			if(num($(this).attr('version'))>=20141110){
				if(decoration['image_send'] || decoration['image_zzim']){
					var imageSendObj = $(".goodsDisplayImageSend",this);

					$(".goodsSendBtn",imageSendObj).show().click(function(){
						display_goods_send(this,'top');
						return false;
					});
					
					$(".goodsZzimBtn",imageSendObj).show().click(function(){
						display_goods_zzim(this,goodsInfo.goods_seq);
						return false;
					});				
				}

				if(decoration['image_overay1'] || decoration['image_overay1_text']){
					
					if($(".goodsDisplayImageOveray1",this).length){
						$(".goodsDisplayImageOveray1",this).remove();
					}

					var overay1Obj = $("<div class='goodsDisplayImageOveray1'><div class='goodsDisplayImageOveray1Bg'></div><div class='goodsDisplayImageOveray1Text'></div></div>");
					
					var overay1Height = 20;

					switch(decoration['image_overay1']){
						case "goods_name":
							var overay1Text = goodsInfo['goods_name'];
							break;
						case "price":
							var overay1Text = '￦' + comma(goodsInfo['price']);
							break;
						case "sale_price":
							var overay1Text = '￦' + comma(goodsInfo['sale_price']);
							break;
						case "consumer_price":
							var overay1Text = '￦' + comma(goodsInfo['consumer_price']);
							break;
						case "discount":
							var overay1Text = comma(goodsInfo['consumer_price']) + " → " + comma(goodsInfo['price']);
							break;
						case "sale_discount":
							var overay1Text = comma(goodsInfo['consumer_price']) + " → " + comma(goodsInfo['sale_price']);
							break;
						case "brand_title":
							var overay1Text = goodsInfo['brand_title'];
							break;
						case "related_goods":
							var overay1Text = "<span class='hand' onclick=\"return show_display_related_goods(this,'"+goodsInfo['goods_seq']+"')\" style='display:block;'>관련상품보기</span>";
							break;
						case "":
						default:
							var overay1Text = decoration['image_overay1_text'];
						
							break;
					}
		
					overay1Obj
					.css({
						'position'			: 'absolute',
						'left'				: 0,
						'top'				: '100%',
						'margin-top'		: -overay1Height,
						'width'				: $(that).width(),
						'height'			: overay1Height
					});
					
					$(".goodsDisplayImageOveray1Bg",overay1Obj)
					.css({
						'background-color'	: '#000000',
						'color'				: '#fff',
						'opacity'			: 0.3,
						'position'			: 'absolute',
						'left'				: 0,
						'top'				: 0,
						'width'				: $(that).width(),
						'height'			: overay1Height
					});

					$(".goodsDisplayImageOveray1Text",overay1Obj)
					.html(overay1Text)
					.css({
						'color'				: '#fff',
						'font-size'			: '11px',
						'font-weight'		: 'bold',
						'text-align'		: 'center',
						'position'			: 'absolute',
						'overflow'			: 'hidden',
						'white-space'		: 'nowrap',
						'line-height'		: overay1Height+'px',
						'left'				: 0,
						'top'				: 0,
						'width'				: $(that).width()
					});
					overay1Obj.show();

					imageObj.after(overay1Obj);
					
				}else{
					$(".goodsDisplayImageOveray1",that).remove();
				}

				if(decoration['image_slide'] && goodsInfo['image_cnt']>1){
					var imageSlideObj = $(".goodsDisplayImageSlide",this);
					$(imageSlideObj,imageObj).click(function(){
						show_display_goods_images(that,goodsInfo['goods_seq'],decoration['image_slide_type']);
						return false;
					});
				}

				if(decoration['quick_shopping']){
					var quickShoppingObj = $(that).closest('.goodsDisplayItemWrap').find(".goodsDisplayQuickShopping");
					if(quickShoppingObj.length){
						if(imageObj.width()){
							quickShoppingObj.css({'width': $(that).outerWidth()});
						}else{
							$(imageObj).bind('load',function(){
								quickShoppingObj.css({'width': $(that).outerWidth()});
							});
						}
					}
				}

				if(decoration['use_seconde_image'] && goodsInfo.image2 != null && goodsInfo.image2.length>1){
					imageObj.attr("imageCut2",goodsInfo.image2);
				}else{
					imageObj.removeAttr("imageCut2");
				}

			// 상품디스플레이 속도개선(20141110) 이후 소스
			}else{
				if(decoration['image_border1']){
					$(this).css({"border":decoration['image_border1_width'] + "px solid " + decoration['image_border1'],"margin":"-"+decoration['image_border1_width']+"px"});
					$(this).parent().attr('align','center');
				}
				
				if(decoration['image_icon']){
					if($(".goodsDisplayImageIcon",this).length){
						$(".goodsDisplayImageIcon",this).remove();
					}
					
					var imageIconObj = $("<div class='goodsDisplayImageIcon'><img src='/data/icon/goodsdisplay/"+decoration['image_icon']+"' /><span class='goodsDisplayImageIconText'></span></div>");
					
					if(decoration['image_icon_location']=='right'){
						imageIconObj
						.css({
							'position'			: 'absolute',
							'right'				: '0px',
							'top'				: imageObj.position().top,
							'text-align'		: 'right'
						});
					}else{
						imageIconObj
						.css({
							'position'			: 'absolute',
							'left'				: imageObj.position().left,
							'top'				: imageObj.position().top
						});
					}
					
					switch(decoration['image_icon']){
						case 'icon_best_no.png':
							var designDisplayObj = $(that).closest(designDisplaySelector);
							 
							if(designDisplayObj.length){
								designDisplayObj.data('iconIdx',num(designDisplayObj.data('iconIdx'))+1);
								var iconText = designDisplayObj.data('iconIdx');
							}else{
								var iconText = 1;
							}
							$(".goodsDisplayImageIconText",imageIconObj).html(iconText).css({
								'position'		: 'absolute',
								'font-size'		: '16px',
								'font-family'	: 'tahoma',
								'font-weight'	: 'bold',
								'text-align'	: 'center',
								'color'			: '#ffffff',
								'letter-spacing': '-1px',
								'width'			: 48,
								'top'			: $(imageObj).position().top+25
							});
							
							if(decoration['image_icon_location']=='right'){
								$(".goodsDisplayImageIconText",imageIconObj).css({
									'left'			: $(imageObj).width()-48
								});
							}else{
								$(".goodsDisplayImageIconText",imageIconObj).css({
									'left'			: 0
								});
							}
						break;
						case 'icon_number.png':
							var designDisplayObj = $(that).closest(designDisplaySelector);
							if(designDisplayObj.length){
								designDisplayObj.data('iconIdx',num(designDisplayObj.data('iconIdx'))+1);
								var iconText = designDisplayObj.data('iconIdx');
							}else{
								var iconText = 1;
							}
							$(".goodsDisplayImageIconText",imageIconObj).html(iconText).css({
								'position'		: 'absolute',
								'font-size'		: '18px',
								'font-family'	: 'tahoma',
								'font-weight'	: 'bold',
								'text-align'	: 'right',
								'color'			: '#ffffff',
								'letter-spacing': '-1px',
								'width'			: 28,
								'top'			: $(imageObj).position().top+10
							});
							
							if(decoration['image_icon_location']=='right'){
								$(".goodsDisplayImageIconText",imageIconObj).css({
									'left'			: $(imageObj).width()-46
								});
							}else{
								$(".goodsDisplayImageIconText",imageIconObj).css({
									'left'			: 0
								});
							}
						break;
						case 'icon_sale.png':
							if(goodsInfo.consumer_price && num(goodsInfo.consumer_price) > num(goodsInfo.price)){
								var iconText = Math.round((num(goodsInfo.consumer_price)-num(goodsInfo.price))/num(goodsInfo.consumer_price)*100);
								iconText = num(iconText) + "%";
							}else{
								iconText = "";
								imageIconObj.hide();
							}
							
							$(".goodsDisplayImageIconText",imageIconObj).html(iconText).css({
								'position'		: 'absolute',
								'font-size'		: '16px',
								'font-family'	: 'tahoma',
								'font-weight'	: 'bold',
								'text-align'	: 'center',
								'color'			: '#ffffff',
								'letter-spacing': '-1px',
								'width'			: 48,
								'top'			: $(imageObj).position().top
							});
							
							if(decoration['image_icon_location']=='right'){
								$(".goodsDisplayImageIconText",imageIconObj).css({
									'left'			: $(imageObj).width()-48
								});
							}else{
								$(".goodsDisplayImageIconText",imageIconObj).css({
									'left'			: 0
								});
							}						
						break;
					}
					
					if(decoration['image_icon_over']=='y'){
						imageIconObj.hide();
					}else if(typeof iconText != 'undefined' && iconText.length){
						imageIconObj.show();
					}
					
					imageObj.after(imageIconObj);
				}else{
					$(".goodsDisplayImageIcon",that).remove();
				}
				
				if(decoration['image_send'] || decoration['image_zzim'] || decoration['image_zzim_on']){
					if($(".goodsDisplayImageSend",this).length){
						$(".goodsDisplayImageSend",this).remove();
					}
					
					var btnSend = decoration['image_send'] ? " <img class='goodsSendBtn' src='/data/icon/goodsdisplay/send/"+decoration['image_send']+"' />" : '';
					var btnZzim = decoration['image_zzim'] ? " <span class='goodsZzimBtn'><img src='/data/icon/goodsdisplay/zzim/"+decoration['image_zzim']+"' class='zzimOffImg' /><img src='/data/icon/goodsdisplay/zzim_on/"+decoration['image_zzim_on']+"' class='zzimOnImg' /></span>" : '';
					
					var imageSendObj = $("<div class='goodsDisplayImageSend'>"+btnSend+btnZzim+"</div>");
					
					if(decoration['image_send_location']=='right'){
						imageSendObj
						.css({
							'position'			: 'absolute',
							'left'				: imageObj.position().left-2,
							'top'				: imageObj.position().top+2,
							'width'				: '100%',
							'text-align'		: 'right'
						});
					}else{
						imageSendObj
						.css({
							'position'			: 'absolute',
							'left'				: imageObj.position().left-2,
							'top'				: imageObj.position().top+2
						});
					}

					if(decoration['image_send_over']=='y'){
						imageSendObj.hide();
					}else{
						imageSendObj.show();
					}
					
					imageObj.after(imageSendObj);
					
					if(goodsInfo.wish=='1'){
						$(".goodsZzimBtn .zzimOnImg",imageSendObj).show();
						$(".goodsZzimBtn .zzimOffImg",imageSendObj).hide();
					}else{
						$(".goodsZzimBtn .zzimOffImg",imageSendObj).show();
						$(".goodsZzimBtn .zzimOnImg",imageSendObj).hide();
					}
					
					$(".goodsSendBtn",imageSendObj).click(function(){
						display_goods_send(this,'top');
						return false;
					});
					
					$(".goodsZzimBtn",imageSendObj).click(function(){
						display_goods_zzim(this,goodsInfo.goods_seq);
						return false;
					});
				}else{
					$(".goodsDisplayImageSend",that).remove();
				}

				if(decoration['image_overay1'] || decoration['image_overay1_text']){
					
					if($(".goodsDisplayImageOveray1",this).length){
						$(".goodsDisplayImageOveray1",this).remove();
					}

					var overay1Obj = $("<div class='goodsDisplayImageOveray1'><div class='goodsDisplayImageOveray1Bg'></div><div class='goodsDisplayImageOveray1Text'></div></div>");
					
					var overay1Height = 20;

					switch(decoration['image_overay1']){
						case "goods_name":
							var overay1Text = goodsInfo['goods_name'];
							break;
						case "price":
							var overay1Text = '￦' + comma(goodsInfo['price']);
							break;
						case "sale_price":
							var overay1Text = '￦' + comma(goodsInfo['sale_price']);
							break;
						case "consumer_price":
							var overay1Text = '￦' + comma(goodsInfo['consumer_price']);
							break;
						case "discount":
							var overay1Text = comma(goodsInfo['consumer_price']) + " → " + comma(goodsInfo['price']);
							break;
						case "sale_discount":
							var overay1Text = comma(goodsInfo['consumer_price']) + " → " + comma(goodsInfo['sale_price']);
							break;
						case "brand_title":
							var overay1Text = goodsInfo['brand_title'];
							break;
						case "related_goods":
							var overay1Text = "<span class='hand' onclick=\"return show_display_related_goods(this,'"+goodsInfo['goods_seq']+"')\" style='display:block;'>관련상품보기</span>";
							break;
						case "":
						default:
							var overay1Text = decoration['image_overay1_text'];
						
							break;
					}
		
					overay1Obj
					.css({
						'position'			: 'absolute',
						'left'				: 0,
						'top'				: '100%',
						'margin-top'		: -overay1Height,
						'width'				: $(that).width(),
						'height'			: overay1Height
					});
					
					$(".goodsDisplayImageOveray1Bg",overay1Obj)
					.css({
						'background-color'	: '#000000',
						'color'				: '#fff',
						'opacity'			: 0.3,
						'position'			: 'absolute',
						'left'				: 0,
						'top'				: 0,
						'width'				: $(that).width(),
						'height'			: overay1Height
					});

					$(".goodsDisplayImageOveray1Text",overay1Obj)
					.html(overay1Text)
					.css({
						'color'				: '#fff',
						'font-size'			: '11px',
						'font-weight'		: 'bold',
						'text-align'		: 'center',
						'position'			: 'absolute',
						'overflow'			: 'hidden',
						'white-space'		: 'nowrap',
						'line-height'		: overay1Height+'px',
						'left'				: 0,
						'top'				: 0,
						'width'				: $(that).width()
					});
					overay1Obj.show();

					imageObj.after(overay1Obj);
					
				}else{
					$(".goodsDisplayImageOveray1",that).remove();
				}
				
				if(decoration['image_slide'] && goodsInfo['image_cnt']>1){
					if($(".goodsDisplayImageSlide",this).length){
						$(".goodsDisplayImageSlide",this).remove();
					}

					var imageSlideObj = $("<div class='goodsDisplayImageSlide'><img src='/data/icon/goodsdisplay/slide/"+decoration['image_slide']+"' /></div>");
								
					imageObj.after(imageSlideObj);
					
					imageSlideObj
					.css({
						'position'			: 'absolute',
						'right'				: '0px',
						'top'				: '50%',
						'margin-top'		: -(imageSlideObj.height()/2)+'px'
					});
					
					$(imageSlideObj,imageObj).click(function(){
						show_display_goods_images(that,goodsInfo['goods_seq'],decoration['image_slide_type']);
						return false;
					});
				}else{
					$(".goodsDisplayImageSlide",that).remove();
				}
				
				if(decoration['quick_shopping']){
					if($(that).closest('.goodsDisplayItemWrap').find(".goodsDisplayQuickShopping").length){
						$(that).closest('.goodsDisplayItemWrap').find(".goodsDisplayQuickShopping").remove();
					}
					var quick_shopping = eval("("+decoration['quick_shopping']+")");
					if(quick_shopping.length){
					
						var quickShoppingObj = $("<div class='goodsDisplayQuickShopping'><table class='quick_shopping_container' width='100%' border='0' cellpadding='0' cellspacing='0'><tr></tr></table></div>");
						
						if(imageObj.width()){
							quickShoppingObj.css({'width': $(that).outerWidth()});
						}else{
							$(imageObj).bind('load',function(){
								quickShoppingObj.css({'width': $(that).outerWidth()});
							});
						}
						
						var quickShoppingTableObj = $("table.quick_shopping_container",quickShoppingObj);
						quickShoppingTableObj.css({
							'border-collapse':'collapse',
							'table-layout':'fixed'
						});

						$(that).after(quickShoppingObj);

						for(var i=0;i<quick_shopping.length;i++){
							switch(quick_shopping[i]){
								case 'newwin':
									$('tr',quickShoppingTableObj).append("<td class='goodsNewwinBtn hand'><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+quick_shopping[i]+".gif' /></td>");
									$(".goodsNewwinBtn",quickShoppingTableObj).click(function(){
										if(goodsInfo.goods_seq) window.open($(imageObj).closest('a').attr('href'));
									});
								break;
								case 'quickview':
									$('tr',quickShoppingTableObj).append("<td class='goodsQuickviewBtn hand'><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+quick_shopping[i]+".gif' /></td>");
									$(".goodsQuickviewBtn",quickShoppingTableObj).click(function(){
										display_goods_quickview(this,goodsInfo.goods_seq);
										return false;
									});
								break;
								case 'send':
									$('tr',quickShoppingTableObj).append("<td class='goodsSendBtn hand'><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+quick_shopping[i]+".gif' /></td>");
									$(".goodsSendBtn",quickShoppingTableObj).click(function(){
										display_goods_send(this,'bottom');
										return false;
									});
								break;
								case 'zzim':
									$('tr',quickShoppingTableObj).append("<td class='goodsZzimBtn hand' width='16'><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+quick_shopping[i]+".gif' class='zzimOffImg' /><img src='/data/icon/goodsdisplay/quick_shopping/thumb_"+quick_shopping[i]+"_on.gif' class='zzimOnImg' /></td>");
									if(goodsInfo.wish=='1'){
										$(".goodsZzimBtn .zzimOnImg",quickShoppingTableObj).show();
										$(".goodsZzimBtn .zzimOffImg",quickShoppingTableObj).hide();
									}else{
										$(".goodsZzimBtn .zzimOffImg",quickShoppingTableObj).show();
										$(".goodsZzimBtn .zzimOnImg",quickShoppingTableObj).hide();
									}
									$(".goodsZzimBtn",quickShoppingTableObj).click(function(){
										display_goods_zzim(this,goodsInfo.goods_seq);
										return false;
									});	
								break;
							}
						}
						
						$('td',quickShoppingTableObj).css({
							'height':'14px',
							'text-align':'center',
							'border':'1px solid #e5e5e5',
							'background-color':'#fff',
							'font-size':'11px',
							'letter-spacing':'-1px'
						});
					}
				}else{
					$(that).closest('.goodsDisplayItemWrap').find(".goodsDisplayQuickShopping").remove();
				}

				if(decoration['use_seconde_image'] && goodsInfo.image2 != null && goodsInfo.image2.length>1){
					imageObj.attr("imageCut2",goodsInfo.image2);
				}else{
					imageObj.removeAttr("imageCut2");
				}
			}
		}

		$(that).data('decorationLoaded',true);
		/*
		$(".quick_shopping_view",$(that).closest('.goodsDisplayItemWrap')).click(function(){
			alert(goodsInfo.goods_name + ' 퀵뷰 버튼 클릭');
			return false;
		});
		*/	
		if(goodsInfo['image_cnt']>1){
			$(".quick_shopping_image",$(that).closest('.goodsDisplayItemWrap')).click(function(){
				show_display_goods_images(that,goodsInfo['goods_seq'],decoration['image_slide_type']?decoration['image_slide_type']:'bottom');
			});
		}else{
			$(".quick_shopping_image",$(that).closest('.goodsDisplayItemWrap')).remove();
		}
		/*
		$(".quick_shopping_send",$(that).closest('.goodsDisplayItemWrap')).click(function(){
			alert(goodsInfo.goods_name + ' 보내기 버튼 클릭');
			return false;
		});
		*/		
	});
	
}

function set_goods_display_decoration_event(selector){
	$(selector)
	.bind("mouseenter",function(){
		var decoration = $(this).data("decoration");
		var goodsInfo = $(this).data("goodsInfo");
		var imageObj = $(this).find("img:eq(0)");
		var imageCut2 = imageObj.attr("imageCut2");

		if(imageCut2){
			imageObj.attr('oriSrc',imageObj.attr('src'));
			imageObj.attr('src',imageCut2);
		}
		
		if(decoration){
			if(decoration['image_border']){
				$(this).css({"border":decoration['image_border_width'] + "px solid " + decoration['image_border'],"margin":"-"+decoration['image_border_width']+"px"});
			}
			
			if(decoration['image_opacity']){
				imageObj.css({"opacity":1-decoration['image_opacity']/100});
			}
			
			if(decoration['image_icon'] && decoration['image_icon_over']=='y'){
				var imageIconObj = $(".goodsDisplayImageIcon",this);
				imageIconObj.show();	
			}
			
			if((decoration['image_send'] || decoration['image_zzim'] || decoration['image_zzim_on']) && decoration['image_send_over']=='y'){
				var imageSendObj = $(".goodsDisplayImageSend",this);
				imageSendObj.show();	
			}
			
			if(decoration['image_overay2'] || decoration['image_overay2_text']){

				if($(".goodsDisplayImageOveray2",this).length){
					var overay2Obj = $(".goodsDisplayImageOveray2",this);
				}else{
					var overay2Obj = $("<div class='goodsDisplayImageOveray2'><div class='goodsDisplayImageOveray2Bg'></div><div class='goodsDisplayImageOveray2Text'></div></div>");
				}
				
				var overay2Height = 20;
				var overay2Top = $(".goodsDisplayImageOveray1",this).length ? $(this).height() - $(".goodsDisplayImageOveray1",this).height(): $(this).height();

				switch(decoration['image_overay2']){
					case "goods_name":
						var overay2Text = goodsInfo['goods_name'];
						
						break;
					case "price":
						var overay2Text = '￦' + comma(goodsInfo['price']);
	
						if(goodsInfo['string_price_use']=='1') overay2Text = goodsInfo['string_price'];
					
						break;
					case "consumer_price":
						var overay2Text = '￦' + comma(goodsInfo['consumer_price']);
	
						if(goodsInfo['string_price_use']=='1') overay2Text = goodsInfo['string_price'];
					
						break;
					case "sale_price":
						var overay2Text = '￦' + comma(goodsInfo['sale_price']);
	
						if(goodsInfo['string_price_use']=='1') overay2Text = goodsInfo['string_price'];
					
						break;
					case "discount":
						var overay2Text = comma(goodsInfo['consumer_price']) + " → " + comma(goodsInfo['price']);
	
						if(goodsInfo['string_price_use']=='1') overay2Text = goodsInfo['string_price'];
					
						break;
					case "sale_discount":
						var overay2Text = comma(goodsInfo['consumer_price']) + " → " + comma(goodsInfo['sale_price']);
	
						if(goodsInfo['string_price_use']=='1') overay2Text = goodsInfo['string_price'];
					
						break;
					case "brand_title":
						var overay2Text = goodsInfo['brand_title'];
						break;
					case "related_goods":
						var overay2Text = "<span class='hand' onclick=\"return show_display_related_goods(this,'"+goodsInfo['goods_seq']+"')\" style='display:block'>관련상품보기</span>";
						break;
					case "":
					default:
						var overay2Text = decoration['image_overay2_text'];
					
						break;
				}
				
				overay2Obj
				.css({
					'position'			: 'absolute',
					'left'				: 0,
					'top'				: overay2Top-overay2Height,
					'width'				: $(this).width(),
					'height'			: overay2Height
				});
				
				$(".goodsDisplayImageOveray2Bg",overay2Obj)
				.css({
					'background-color'	: '#000000',
					'color'				: '#fff',
					'opacity'			: 0.3,
					'position'			: 'absolute',
					'left'				: 0,
					'top'				: 0,
					'width'				: $(this).width(),
					'height'			: overay2Height
				});
				
				$(".goodsDisplayImageOveray2Text",overay2Obj)
				.html(overay2Text)
				.css({
					'color'				: '#fff',
					'font-size'			: '11px',
					'font-weight'		: 'bold',
					'text-align'		: 'center',
					'position'			: 'absolute',
					'overflow'			: 'hidden',
					'white-space'		: 'nowrap',
					'line-height'		: overay2Height+'px',
					'left'				: 0,
					'top'				: 0,
					'width'				: $(this).width()
				});
				imageObj.after(overay2Obj.show());
				
			}else{
				$(".goodsDisplayImageOveray2",this).remove();
			}
			
		}
	})
	.bind("mouseleave",function(){
		var decoration = $(this).data("decoration");
		var goodsInfo = $(this).data("goodsInfo");
		var imageObj = $(this).find("img:eq(0)");
		var imageCut2 = imageObj.attr("imageCut2");
		
		if(imageCut2){
			imageObj.attr('src',imageObj.attr('oriSrc'));
		}
		
		if(decoration){
			if(decoration['image_border'] && decoration['image_border1']){
				decoration['image_border1_width'] = decoration['image_border1_width'] ? decoration['image_border1_width'] : 0;
				$(this).css({"border":decoration['image_border1_width'] + "px solid " + decoration['image_border1'],"margin":"-"+decoration['image_border1_width']+"px"});
				//$(this).parent().attr('align','center');
			}else if(decoration['image_border']){
				$(this).css({"border":0,"margin":"0px"});
			}
			
			
			if(decoration['image_opacity']){
				imageObj.css({"opacity":1});
			}
			
			if(decoration['image_overay2'] || decoration['image_overay2_text']){
				$(".goodsDisplayImageOveray2",this).hide();
			}
			
			if(decoration['image_icon'] && decoration['image_icon_over']=='y'){
				$(".goodsDisplayImageIcon",this).hide();
			}
			
			if((decoration['image_send'] || decoration['image_zzim'] || decoration['image_zzim_on']) && decoration['image_send_over']=='y'){
				$(".goodsDisplayImageSend",this).hide();
			}

		}
	});
}

/* 어드민 : 이미지꾸미기 선택정보를 샘플이미지에 적용*/
function goods_image_decoration_data(goodsDisplayDecorationContainer){
	var data = {};
	
	if($("input.use_image_border",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_border",goodsDisplayDecorationContainer).val().length){
			data.image_border1 = $("input.image_border1",goodsDisplayDecorationContainer).val();
			data.image_border1_width = $("input.image_border1_width",goodsDisplayDecorationContainer).val();
			data.image_border = $("input.image_border",goodsDisplayDecorationContainer).val();
			data.image_border_width = $("input.image_border_width",goodsDisplayDecorationContainer).val();
		}
	}
	
	if($("input.use_image_opacity",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_opacity",goodsDisplayDecorationContainer).val().length){
			data.image_opacity = $("input.image_opacity",goodsDisplayDecorationContainer).val();
		}
	}
	
	if($("input.use_image_icon",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_icon",goodsDisplayDecorationContainer).val().length){
			data.image_icon = $("input.image_icon",goodsDisplayDecorationContainer).val();
		}		
		if($("select.image_icon_location",goodsDisplayDecorationContainer).val().length){
			data.image_icon_location = $("select.image_icon_location",goodsDisplayDecorationContainer).val();
		}
		if($("select.image_icon_over",goodsDisplayDecorationContainer).val().length){
			data.image_icon_over = $("select.image_icon_over",goodsDisplayDecorationContainer).val();
		}
	}
	
	if($("input.use_image_send",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_send",goodsDisplayDecorationContainer).val().length){
			data.image_send = $("input.image_send",goodsDisplayDecorationContainer).val();
		}
		if($("select.image_send_location",goodsDisplayDecorationContainer).val().length){
			data.image_send_location = $("select.image_send_location",goodsDisplayDecorationContainer).val();
		}
		if($("select.image_send_over",goodsDisplayDecorationContainer).val().length){
			data.image_send_over = $("select.image_send_over",goodsDisplayDecorationContainer).val();
		}
	}
	
	if($("input.use_image_zzim",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_zzim",goodsDisplayDecorationContainer).val().length){
			data.image_zzim = $("input.image_zzim",goodsDisplayDecorationContainer).val();
		}
		if($("input.image_zzim_on",goodsDisplayDecorationContainer).val().length){
			data.image_zzim_on = $("input.image_zzim_on",goodsDisplayDecorationContainer).val();
		}
		if($("select.image_send_location",goodsDisplayDecorationContainer).val().length){
			data.image_send_location = $("select.image_send_location",goodsDisplayDecorationContainer).val();
		}
		if($("select.image_send_over",goodsDisplayDecorationContainer).val().length){
			data.image_send_over = $("select.image_send_over",goodsDisplayDecorationContainer).val();
		}
	}
	
	
	if($("input.use_image_overay",goodsDisplayDecorationContainer).is(":checked")){
		if($("select.image_overay1",goodsDisplayDecorationContainer).val().length){
			data.image_overay1 = $("select.image_overay1",goodsDisplayDecorationContainer).val();
		}else if($("input.image_overay1_text",goodsDisplayDecorationContainer).val().length){
			data.image_overay1 = $("select.image_overay1",goodsDisplayDecorationContainer).val();
			data.image_overay1_text = $("input.image_overay1_text",goodsDisplayDecorationContainer).val();
		}
		
		if($("select.image_overay2",goodsDisplayDecorationContainer).val().length){
			data.image_overay2 = $("select.image_overay2",goodsDisplayDecorationContainer).val();
		}else if($("input.image_overay2_text",goodsDisplayDecorationContainer).val().length){
			data.image_overay2 = $("select.image_overay2",goodsDisplayDecorationContainer).val();
			data.image_overay2_text = $("input.image_overay2_text",goodsDisplayDecorationContainer).val();
		}
	}
	
	if($("input.use_image_slide",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_slide",goodsDisplayDecorationContainer).val().length){
			data.image_slide = $("input.image_slide",goodsDisplayDecorationContainer).val();
		}
		if($("select.image_slide_type",goodsDisplayDecorationContainer).val().length){
			data.image_slide_type = $("select.image_slide_type",goodsDisplayDecorationContainer).val();
		}
	}
	
	if($("input.use_quick_shopping",goodsDisplayDecorationContainer).is(":checked")){
		if($("input[name='quick_shopping']",goodsDisplayDecorationContainer).val().length){
			data.quick_shopping = $("input[name='quick_shopping']",goodsDisplayDecorationContainer).val();
		}
	}

	if($("input.use_seconde_image",goodsDisplayDecorationContainer).is(":checked")){
		data.use_seconde_image = "1";
	}
	
	var i,dataCnt = 0;
	for(i in data) {
		dataCnt++;
		data[i] = data[i].replace(/"/g,'\\"');
	}

	var decoration = Base64.encode(JSONtoString(data));

	$(".goodsDisplayImageWrap",goodsDisplayDecorationContainer).data("decorationLoaded",false);
	$(".goodsDisplayImageWrap",goodsDisplayDecorationContainer).attr("decoration",decoration);
	$(".goodsDisplayImageWrap",goodsDisplayDecorationContainer).data("decoration",data);
	$("input.image_decoration",goodsDisplayDecorationContainer).val(decoration);
	
	/* 이미지꾸미기 이미지 설정 */
	set_goods_display_decoration(".goodsDisplayImageWrap");
}

function set_info_item_data(goodsDisplayDecorationContainer){
	
	try{
		var info_item_settings = eval($(".info_settings",goodsDisplayDecorationContainer).val());
	}catch(e){
		var info_item_settings = [];
	};
	
	if(typeof info_item_settings == 'object'){
		/* 상품정보 항목 저장값 출력 */
		for(var i=0;i<info_item_settings.length;i++){
			
			var new_info_item = add_new_info_item(goodsDisplayDecorationContainer);
			var info_item_setting = info_item_settings[i];
	
			for(var key in info_item_setting){
				var cellSelector = ".info_item_cell_" + key;
				var selector = ".info_item_" + key;
				var selectValue = info_item_setting[key];
				
				if($(selector,new_info_item).length){
					switch($(selector,new_info_item)[0].tagName){
						case 'INPUT':
							if($(selector,new_info_item).attr('type')=='text'){
								$(selector,new_info_item).val(selectValue).change();
							}else if($(selector,new_info_item).attr('type')=='radio' ){
								$(selector,new_info_item).each(function(idx){ 
									if( $(this).val() == selectValue ) {
										$(this).attr('checked',true).change();
									} 
								});
							}else{
								$(selector,new_info_item).attr('checked',true);
							}
						break;
						case 'SELECT':
							$(selector,new_info_item).val(selectValue);
						break;
					}
				}
			}
			
			$(".info_item_kind",new_info_item).change();
		}
	}else{
		var idx=0;
		
		for(key in info_item_config){
			add_new_info_item(goodsDisplayDecorationContainer);
			$(".info_item_kind",goodsDisplayDecorationContainer).eq(idx).val(key).change();
			idx++;
		}
	}
	
	apply_input_style();
	
}

/* 어드민 : 상품정보 항목 추가 */
function add_new_info_item(obj){
	var goodsDisplayDecorationContainer = $(obj).closest(".goodsDisplayDecorationContainer");
	
	$(".info_item_default .colorpicker",goodsDisplayDecorationContainer).customColorPicker("destroy");
	var new_info_item = $("div.info_item_default",goodsDisplayDecorationContainer).clone();
	new_info_item.removeClass("hide").removeClass("info_item_default").show();
	new_info_item.find("input.info_setting").removeAttr('disabled');

	$("div.info_item_container",goodsDisplayDecorationContainer).append(new_info_item);
	
	$(".colorpicker",goodsDisplayDecorationContainer).customColorPicker();
	
	change_info_item(new_info_item);

	$(".customFontDecoration",new_info_item).customFontDecoration({"change":function(){
		change_info_item(new_info_item);
	}});
	
	$(document).resize();
	
	return new_info_item;
}

/* 어드민 : 상품정보 항목 삭제 */
function remove_info_item(obj){
	$(obj).closest("div.info_item").remove();
}

/* 어드민 : 상품정보 항목 값 변경 */
function change_info_item(obj){

	var info_item = $(obj).closest("div.info_item");
	var kind = $(".info_item_kind",info_item).val();
	var data = {};
	var result_string = '';
	
	if(!info_item_config[kind]) return;
	
	$(".info_item_cell",info_item).hide();

	for(var i=0;i<info_item_config[kind].length;i++){

		var key = info_item_config[kind][i];

		var cellSelector = ".info_item_cell_" + key;
		var selector = ".info_item_" + key;
		var selectValue = "";
		
		$(cellSelector,info_item).show();

		if($(selector,info_item).length){
			switch($(selector,info_item)[0].tagName){
				case 'INPUT':
					if($(selector,info_item).attr('type')=='text'){
						selectValue = $(selector,info_item).val();
					}else if($(selector,info_item).is(':checked')){
						selectValue = $(obj).val();
					}
				break;
				case 'SELECT':
					selectValue = $(selector,info_item).val();
				break;
			}
		}
		
		if(key=='font_decoration'){
			
			data[key] = selectValue.replace(/"/g,'\\"');
		}else{
			data[key] = selectValue;
		}
	}

	result_string = (JSONtoString(data));	 				

	$("input.info_setting",info_item).val(result_string);
	

}

/* 해당 상품의 관련상품 보기 */
function show_display_related_goods(obj,goods_seq){
	var goodsDisplayImageWrapObj = $(obj).closest('.goodsDisplayImageWrap');
	
	if($("#displayRelatedGoodsLayer").length){
		$("<div id='displayRelatedGoodsLayer'></div>").remove();
	}
	
	var html = "<div class='goodsPopupLayer' id='displayRelatedGoodsLayer'>";
	html += "<div class='goodsPopupLayerTitle'>해당 상품의 관련상품 보기</div>";
	html += "<div class='goodsPopupLayerClose'></div>";
	html += "<div class='goodsPopupLayerBody'>";
	html += "</div>";
	html += "</div>";
	
	$(html).hide().appendTo('body');
	
	$(goodsDisplayImageWrapObj).activity({segments: 12, width: 5.5, space: 6, length: 13, color: '#fff', speed: 1.5});
	
	$.ajax({
		'url' : '/goods/display_related_goods',
		'data' : {'goods_seq':goods_seq},
		'success' : function(res){
			
			$(goodsDisplayImageWrapObj).activity(false);
				
			$("#displayRelatedGoodsLayer").css({
				'top' : $(document).scrollTop()+goodsDisplayImageWrapObj.offset().top-$(document).scrollTop()+goodsDisplayImageWrapObj.outerHeight(),
				'left' : goodsDisplayImageWrapObj.offset().left
			}).show();
		
			$("#displayRelatedGoodsLayer .goodsPopupLayerClose").click(function(){
				$("#displayRelatedGoodsLayer").remove();
			});
			
			if(!$("#displayRelatedGoodsLayer").length){
				$("<div id='displayRelatedGoodsLayer'></div>").appendTo('body');
			}
	
			$("#displayRelatedGoodsLayer .goodsPopupLayerBody").html(res);

			if(goodsDisplayImageWrapObj.offset().left+$("#displayRelatedGoodsLayer").outerWidth() > $(window).width()){
				$("#displayRelatedGoodsLayer").css('left',goodsDisplayImageWrapObj.offset().left - (goodsDisplayImageWrapObj.offset().left+$("#displayRelatedGoodsLayer").outerWidth()-$(window).width()));
			}
		}
	})

	return false;	
}


/* 해당 상품의 이미지 더 보기 */
function show_display_goods_images(goodsDisplayImageWrapObj,goods_seq,image_slide_type){
	var imageObj = $(goodsDisplayImageWrapObj).find("img:eq(0)");
	
	if($(goodsDisplayImageWrapObj).find("#displayGoodsImagesLayer").length){
		$(document).click();
		//$("#displayGoodsImagesLayer").stop(true,true).remove();		
	}else{
		if(!$("#displayGoodsImagesLayer").length){
			$('body').append($("<div id='displayGoodsImagesLayer'></div>"));
		}
		//$(goodsDisplayImageWrapObj).after($("#displayGoodsImagesLayer"));
		
		$(goodsDisplayImageWrapObj).activity({segments: 12, width: 5.5, space: 6, length: 13, color: '#fff', speed: 1.5});

		$("#displayGoodsImagesLayer").stop(true,true).empty().hide().click(function(){return false;});

		$.ajax({
			'url' : '/goods/display_goods_images',
			'data' : {'goods_seq':goods_seq,'image_slide_type':image_slide_type},
			'global' : false,
			'success' : function(html){
				
				$(goodsDisplayImageWrapObj).activity(false);
				
				$("#displayGoodsImagesLayer").html(html);

				if(imageObj.offset().left+imageObj.width()+$("#displayGoodsImagesLayer").width() > $(window).width()){
					var left = $(window).width()-$("#displayGoodsImagesLayer").width();
				}else{
					var left = imageObj.offset().left+imageObj.width();
				}
				
				$("#displayGoodsImagesLayer").css({
					'position' : 'absolute',
					'z-index' : 1000,
					'top' : imageObj.offset().top,
					'left' : left
				}).fadeIn();
				
				$("#displayGoodsSendLayer").stop(true,true).remove();
				$(document).one('click',function(e){
					$("#displayGoodsImagesLayer").stop(true,true).remove();					
				});
				
			}
		});
		
	}
	return false;	
}

/* 상품 찜하기 버튼 클릭 */
function display_goods_zzim(btnObj,goods_seq){
	var goodsDisplayItemWrap = $(btnObj).closest(".goodsDisplayItemWrap");
	$.ajax({
		'url' : '/mypage/wish_add_ajax_toggle',
		'data' : {'goods_seq':goods_seq},
		'dataType' : 'json',
		'global' : false,
		'success' : function(res){
			if(res.result == 'not_login'){
				parent.openDialogConfirm('회원만 사용가능합니다. 로그인하시겠습니까?',400,140,function(){
					parent.location.replace(res.url);
				},function(){});
			} else if(res.result == 'add'){
				$(goodsDisplayItemWrap).closest('.goodsDisplayItemWrap').find(".zzimOnImg").show();
				$(goodsDisplayItemWrap).closest('.goodsDisplayItemWrap').find(".zzimOffImg").hide();
			} else if(res.result == 'del'){
				$(goodsDisplayItemWrap).closest('.goodsDisplayItemWrap').find(".zzimOffImg").show();
				$(goodsDisplayItemWrap).closest('.goodsDisplayItemWrap').find(".zzimOnImg").hide();
			}
		}						
	});	
}

/* 상품 퀵뷰 버튼 클릭 */
function display_goods_quickview(btnObj,goods_seq){

	if(!goods_seq) return;

	var basicWidth = 1000;
	$.ajax({
		'url' : '/common/arrLayoutBasic',
		'dataType' : 'json',
		'async' : false,
		'success' : function(res){
			basicWidth = num(res.basic.width);
		}
	});

	if($("#goodsQuickViewLayer").length){
		$("<div id='goodsQuickViewLayer'></div>").remove();
	}

	var html = "<div class='goodsPopupLayer' id='goodsQuickViewLayer'>";
	html += "<div class='goodsPopupLayerTitle'>간략보기</div>";
	html += "<div class='goodsPopupLayerClose'></div>";
	html += "<div class='goodsPopupLayerBody'>";
	html += "<div class='goodsPopupLayerBtnWrap'><a href='/goods/view?no="+goods_seq+"'><span class='goodsPopupLayerMoreBtn'></span></a></div>";
	html += "</div>";
	html += "</div>";

	$(html).hide().appendTo('body');

	$("#goodsQuickViewLayer").css({
		'top' : $(document).scrollTop()+10,
		'left' : '50%',
		'margin-left' : (basicWidth+40)/2*-1,
		'width' : basicWidth+40,
		'height' : $(window).height()-100
	}).show();

	$("#goodsQuickViewLayer .goodsPopupLayerClose").click(function(){
		$("#goodsQuickViewLayer").remove();
	});
	$("<iframe frameborder='0'></iframe>").prependTo($("#goodsQuickViewLayer .goodsPopupLayerBody")).css({
		'margin-top' : '10px',
		'width' : '100%',
		'height' : $(window).height()-100-150
	}).attr("src","/goods/view?no="+goods_seq+"&quickview=1");

}

/* 상품 보내기 버튼 클릭 */
function display_goods_send(btnObj,location){
	var goodsDisplayImageWrapObj = $(btnObj).closest(".goodsDisplayItemWrap").find(".goodsDisplayImageWrap");
	var goodsInfo = $(goodsDisplayImageWrapObj).data("goodsInfo");
	var imageObj = $(goodsDisplayImageWrapObj).find("img:eq(0)");
	
	if($("#displayGoodsSendLayer").length){
		$(document).click();
	}else{
		if(!$("#displayGoodsSendLayer").length){
			goodsDisplayImageWrapObj.after($("<div id='displayGoodsSendLayer'></div>"));
		}
		goodsDisplayImageWrapObj.after($("#displayGoodsSendLayer"));

		$(goodsDisplayImageWrapObj).activity({segments: 12, width: 5.5, space: 6, length: 13, color: '#fff', speed: 1.5});

		$("#displayGoodsSendLayer").css({
			'border':'1px solid #e1e1e1',
			'padding':'2px 0px 2px 3px',
			'background-color':'#fff',
			'white-space':'nowrap',
			'min-width':'100px'
		}).stop(true,true).empty().hide().click(function(){return false;});

		$.ajax({
			'url' : '/common/snslinkurl_tag',
			'data' : {'no':goodsInfo.goods_seq,'goods_name':goodsInfo.goods_name},
			'global' : false,
			'success' : function(html){
				
				$(goodsDisplayImageWrapObj).activity(false);
				
				$("#displayGoodsSendLayer").html(html);

				if(imageObj.offset().left+imageObj.width()+$("#displayGoodsSendLayer").outerWidth() > $(window).width()){
					var left = $("#displayGoodsSendLayer").outerWidth() * -1;
				}else{
					var left = goodsDisplayImageWrapObj.position().left+imageObj.width();
				}
				
				if(location=='top'){
					var top = imageObj.position().top;
				}else{
					var goodsDisplayItemWrapObj = goodsDisplayImageWrapObj.closest('.goodsDisplayItemWrap');
					var top = imageObj.position().top+imageObj.height()+$(".goodsDisplayQuickShopping",goodsDisplayItemWrapObj).height()-$("#displayGoodsSendLayer").outerHeight();
				}
				
				$("#displayGoodsSendLayer").css({
					'position' : 'absolute',
					'z-index' : 1000,
					'top' : top,
					'left' : left
				}).fadeIn();
				
				$("#displayGoodsImagesLayer").stop(true,true).remove();
				$(document).one('click',function(e){
					$("#displayGoodsSendLayer").stop(true,true).remove();
				});
				
			}
		});
		
	}
	return false;	
}

// 자동노출조건 설명
function setCriteriaDescription(){
	$(".displayTabGoodsContainer, .relationGoodsContainer").each(function(){
		var descriptions = new Array();
		
		criteria = $(".displayCriteria",this).val().split(",");
		if(criteria=="") return;
		for(var i=0;i<criteria.length;i++){
			var div = criteria[i].split("=");

			if(div[1]){
				var name = "";
				var value = decodeURIComponent(div[1]);
				switch(div[0]){
					case "selectGoodsName": name = "검색어"; break;
					case "selectGoodsView": name = "상품노출"; break;
					case "selectGoodsStatus": name = "상품상태"; break;
					case "selectCategory1": name = "카테고리1"; break;
					case "selectCategory2": name = "카테고리2"; break;
					case "selectCategory3": name = "카테고리3"; break;
					case "selectCategory4": name = "카테고리4"; break;
					case "selectBrand1": name = "브랜드1"; break;
					case "selectBrand2": name = "브랜드2"; break;
					case "selectBrand3": name = "브랜드3"; break;
					case "selectBrand4": name = "브랜드4"; break;
					case "selectLocation1": name = "지역1"; break;
					case "selectLocation2": name = "지역2"; break;
					case "selectLocation3": name = "지역3"; break;
					case "selectLocation4": name = "지역4"; break;
					case "selectStartPrice": name = "최소가격"; value = comma(value)+"원"; break;
					case "selectEndPrice": name = "최대가격"; value = comma(value)+"원"; break;
					case "auto_order": name = "자동노출기준"; break;
					case "auto_term_type": name = "자동노출 정렬 기준일"; break;
					case "auto_term": name = "자동노출 최근n일"; break;
					case "auto_start_date": name = "자동노출 시작일"; break;
					case "auto_end_date": name = "자동노출 종료일"; break;
					case "selectEvent": name = "이벤트고유번호"; break;
					case "selectEventBenefits": name = "이벤트혜택고유번호"; break;
					case "selectGoodsRelationCategory": name = "해당 카테고리"; value=""; break;
					case "selectGoodsRelationBrand": name = "해당 브랜드"; value=""; break;
					case "selectGoodsRelationLocation": name = "해당 지역"; value=""; break;
					/*
					default:
						name = div[0];
					break;
					*/
				}
				
				switch(div[1]){
					case "look": value = "노출"; break;
					case "notLook": value = "미노출"; break;
					case "normal": value = "정상"; break;
					case "runout": value = "미노출"; break;
					case "purchasing": value = "재고확보중"; break;
					case "unsold": value = "판매중지"; break;
					case "relative": value = "최근"; break;
					case "absolute": value = "고정"; break;
					case "newly": value = "최근등록순"; break;
					case "deposit": value = "판매인기순(구매갯수)"; break;
					case "deposit_price": value = "판매인기순(구매금액)"; break;
					case "review": value = "상품후기많은순"; break;
					case "view": value = "상품조회많은순"; break;
					case "cart": value = "장바구니"; break;
					case "wish": value = "위시리스트"; break;
				}

				if(div[2]) value = div[2];
				
				if(name) descriptions.push(name+(value ? "→"+value : ''));
			}
		}
		
		if(descriptions && descriptions.length){
			$(".displayCriteriaDesc",this).html("<b>[조건]</b> "+descriptions.join(", "));
		}else{
			$(".displayCriteriaDesc",this).html("");
		}
	});
	
}