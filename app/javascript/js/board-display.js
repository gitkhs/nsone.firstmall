/* 게시판넣기 기능추가 */
$(function(){
			
	/* 이미지꾸미기 이미지 마우스오버 이벤트 설정 */
	$(".BoardgoodsDisplayImageWrap")
	.live("mouseenter",function(){
		var decoration = $(this).data("decoration");
		var goodsInfo = $(this).data("goodsInfo");
		var imageObj = $(this).find("img:eq(0)");
		
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

			if(decoration['image_overay2'] || decoration['image_overay2_text']){

				if($(".goodsDisplayImageOveray2",this).length){
					var overay2Obj = $(".goodsDisplayImageOveray2",this);
				}else{
					var overay2Obj = $("<div class='goodsDisplayImageOveray2'><div class='goodsDisplayImageOveray2Bg'></div><div class='goodsDisplayImageOveray2Text'></div></div>");
				}
				
				var overay2Height = 20;
				var overay2Top = $(".goodsDisplayImageOveray1",this).length ? $(this).height() - $(".goodsDisplayImageOveray1",this).height(): $(this).height();

				switch(decoration['image_overay2']){
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
	.live("mouseleave",function(){
		var decoration = $(this).data("decoration");
		var goodsInfo = $(this).data("goodsInfo");
		var imageObj = $(this).find("img:eq(0)");
		
		if(decoration){
			if(decoration['image_border']){
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
		}
	});
	
	/* 어드민 */
	$(".goodsDisplayDecorationContainer").each(function(){
		var goodsDisplayDecorationContainer = this;
		
		/* 샘플이미지 : 사이즈변경 */
		$("select.image_size",goodsDisplayDecorationContainer).change(function(){
			var image_size = $(this).val();
			$(".BoardgoodsDisplayImageWrap img, .goodsDisplayImageTable",goodsDisplayDecorationContainer).css({"width":$("option:selected",this).attr('width'),"height":$("option:selected",this).attr('height')});
			goods_image_decoration_data_(goodsDisplayDecorationContainer);
		}).change();
		
		/* 이미지꾸미기 - 오버레이 입력폼 show,hide 효과 */
		$("select.image_overay1, select.image_overay2",goodsDisplayDecorationContainer).change(function(){
			if($(this).val()=='') $(this).parent().children(".image_overay_text").show();
			else $(this).parent().children(".image_overay_text").hide();		
		}).change();
		
		/* 이미지꾸미기 - 아이콘 선택창 열기 버튼  */
		$(".image_icon_select",goodsDisplayDecorationContainer).live("click",function(){
			var uniqueKey = "image_icon_select_"+Math.floor(Math.random()*10000000);
			$(this).attr('uniqueKey',uniqueKey);
			$("#displayImageIconPopup input[name='uniqueKey']").val(uniqueKey);
			set_display_image_icon_();
			openDialog("아이콘 선택  <span class='desc'>아이콘으로 사용할 이미지를 클릭하여 주세요.</span>", "#displayImageIconPopup", {"width":"570","height":"300","show" : "fade","hide" : "fade"});
		});
		
		/* 이미지꾸미기 - 체크박스값에 따른 disabled 처리 */
		$(".image_decorate_chk",goodsDisplayDecorationContainer).change(function(){
			if($(this).is(":checked")){
				$(this).closest(".image_decoration_table").find("*").not(this).filter(function(){return $(this).is("select") || $(this).children().length==0;}).css('opacity',1).removeAttr('disabled');
			}else{
				$(this).closest(".image_decoration_table").find("*").not(this).filter(function(){return $(this).is("select") || $(this).children().length==0;}).css('opacity',0.3).attr('disabled',true);
			}	
		}).change();
	
		/* 이미지꾸미기 선택정보를 샘플이미지에 적용*/
		$(".image_decoration_table select, .image_decoration_table input",goodsDisplayDecorationContainer).change(function(){
			goods_image_decoration_data_(goodsDisplayDecorationContainer);
		});
		
		/* 상품정보 텍스트정렬을 샘플이미지에 적용 */
		$("input.text_align",goodsDisplayDecorationContainer).change(function(){
			if($(this).is(':checked')){
				$(".goodsDisplayAlign",goodsDisplayDecorationContainer).css("text-align",$(this).val());
			}
		}).change();
		
		/* 상품정보 항목 값 변경 */
		$("div.info_item .info_item_selector",goodsDisplayDecorationContainer).live('change',function(){
			//change_info_item_(this);
		});
	
		/* 상품정보 항목 Sortable */
		$(".info_item_container",goodsDisplayDecorationContainer).sortable({
				placeholder: "info_item_holder"
		});
		
		set_info_item_data(goodsDisplayDecorationContainer);
	});
	
	/* 어드민 - 이미지꾸미기 - 아이콘 선택 */
	$("#displayImageIconPopup img.icon").live("click",function(){
		var uniqueKey = $("#displayImageIconPopup input[name='uniqueKey']").val();
		
		$(".image_icon_select[uniqueKey='"+uniqueKey+"']").attr("src",$(this).attr('src'));
		
		var selectedIconName = $(this).attr("src").replace("/data/icon/goodsdisplay/","");

		$(".image_icon_select[uniqueKey='"+uniqueKey+"']").closest('table').find('input.image_icon').val(selectedIconName).change();
		closeDialog("displayImageIconPopup");
	});	
	
	/* 이미지꾸미기 이미지 설정 */
	set_goods_display_decoration_(".BoardgoodsDisplayImageWrap");
});

/* 어드민 - 이미지꾸미기 - 아이콘 선택후 콜백 */
function set_display_image_icon_(){	
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

/* 이미지꾸미기 이미지 설정 */
function set_goods_display_decoration_(selector, force){
	
	$(selector)
	.css({'position':'relative','display':'inline-block'})
	.each(function(){
		
		var that = this;
		var imageObj = $(this).find("img:eq(0)");

		if($(this).data('decorationLoaded') || !$(this).attr("decoration")){
			return;
		}
		
		if($(that).attr("decoration")) $(that).data("decoration",eval("("+Base64.decode($(that).attr("decoration"))+")"));
		//if($(that).attr("goodsInfo")) $(that).data("goodsInfo",eval("("+Base64.decode($(that).attr("goodsInfo"))+")"));
		
		var decoration = $(that).data("decoration");
		//var goodsInfo = $(that).data("goodsInfo");
		
		if(decoration){
			
			if(decoration['image_icon']){
				if($(".goodsDisplayImageIcon",this).length){
					$(".goodsDisplayImageIcon",this).remove();
				}
				
				var imageIconObj = $("<div class='goodsDisplayImageIcon'><img src='/data/icon/goodsdisplay/"+decoration['image_icon']+"' /><span class='goodsDisplayImageIconText'></span></div>");
				
				if(decoration['image_icon_location']=='right'){
					imageIconObj
					.css({
						'position'			: 'absolute',
						'left'				: imageObj.position().left,
						'top'				: imageObj.position().top,
						'width'				: '100%',
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
						var designDisplayObj = $(that).closest("div.designDisplay, div.designCategoryBoardDisplay, div.designCategoryRecommendDisplay");
						 
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
						var designDisplayObj = $(that).closest("div.designDisplay");
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
				}else{
					imageIconObj.show();
				}
				
				imageObj.after(imageIconObj);
			}else{
				$(".goodsDisplayImageIcon",that).remove();
			}

			if(decoration['image_overay1'] || decoration['image_overay1_text']){
				
				if($(".goodsDisplayImageOveray1",this).length){
					$(".goodsDisplayImageOveray1",this).remove();
				}

				var overay1Obj = $("<div class='goodsDisplayImageOveray1'><div class='goodsDisplayImageOveray1Bg'></div><div class='goodsDisplayImageOveray1Text'></div></div>");
				
				var overay1Height = 20;

				switch(decoration['image_overay1']){  
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
		}
		
		$(that).data('decorationLoaded',true);
		
	});
	
}




/* 어드민 : 이미지꾸미기 선택정보를 샘플이미지에 적용*/
function goods_image_decoration_data_(goodsDisplayDecorationContainer){
	var data = {};
	
	if($("input.use_image_border",goodsDisplayDecorationContainer).is(":checked")){
		if($("input.image_border",goodsDisplayDecorationContainer).val().length){
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
	
	var i,dataCnt = 0;
	for(i in data) {
		dataCnt++;
		data[i] = data[i].replace(/"/g,'\\"');
	}

	var decoration = Base64.encode(JSONtoString(data));

	$(".BoardgoodsDisplayImageWrap",goodsDisplayDecorationContainer).data("decorationLoaded",false);
	$(".BoardgoodsDisplayImageWrap",goodsDisplayDecorationContainer).attr("decoration",decoration);
	$(".BoardgoodsDisplayImageWrap",goodsDisplayDecorationContainer).data("decoration",data);
	$("input.image_decoration",goodsDisplayDecorationContainer).val(decoration);
	
	/* 이미지꾸미기 이미지 설정 */
	set_goods_display_decoration_(".BoardgoodsDisplayImageWrap");
}

function set_info_item_data(goodsDisplayDecorationContainer){
	
	var info_item_settings = eval($(".info_settings",goodsDisplayDecorationContainer).val());
	
	if(typeof info_item_settings == 'object'){
		/* 상품정보 항목 저장값 출력 */
		for(var i=0;i<info_item_settings.length;i++){
			
			var new_info_item = add_new_info_item_(goodsDisplayDecorationContainer);
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
			add_new_info_item_(goodsDisplayDecorationContainer);
			$(".info_item_kind",goodsDisplayDecorationContainer).eq(idx).val(key).change();
			idx++;
		}
	}
	
	apply_input_style();
	
}

/* 어드민 : 상품정보 항목 추가 */
function add_new_info_item_(obj){
	var goodsDisplayDecorationContainer = $(obj).closest(".goodsDisplayDecorationContainer");
	
	$(".info_item_default .colorpicker",goodsDisplayDecorationContainer).customColorPicker("destroy");
	var new_info_item = $("div.info_item_default",goodsDisplayDecorationContainer).clone();
	new_info_item.removeClass("hide").removeClass("info_item_default").show();
	new_info_item.find("input.info_setting").removeAttr('disabled');

	$("div.info_item_container",goodsDisplayDecorationContainer).append(new_info_item);
	
	$(".colorpicker",goodsDisplayDecorationContainer).customColorPicker();
	
	//change_info_item_(new_info_item);

	$(".customFontDecoration",new_info_item).customFontDecoration({"change":function(){
		//change_info_item_(new_info_item);
	}});
	
	return new_info_item;
}

/* 어드민 : 상품정보 항목 삭제 */
function remove_info_item_(obj){
	$(obj).closest("div.info_item").remove();
}

/* 어드민 : 상품정보 항목 값 변경 */
function change_info_item_(obj){

	var info_item = $(obj).closest("div.info_item");
	var kind = $(".info_item_kind",info_item).val();
	var data = {};
	var result_string = '';
	
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
