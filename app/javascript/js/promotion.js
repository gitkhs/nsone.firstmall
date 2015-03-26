/*
 * 관련 자바스크립트
 */ 
$(document).ready(function() {   
	try{ 
		if(eval("DM_LOADED")) 
		{ 
			var DM_LOADED_AD = true;
		} 
	}catch(e){ 
		var DM_LOADED_AD = '';
	}
	/* 디자인팝업 쿠키처리 */
	$(".designPopup").each(function(){
		var popup = this;
		var popupStyle = $(popup).attr('popupStyle');
		var popupSeq = $(popup).attr('popupSeq');
		var popupKey = "designPopup"+popupSeq;

		if(typeof gl_mobile_mode != 'undefined' && gl_mobile_mode){
			var topnum = ( parseInt($(this).height()/2) <= parseInt($(window).height()/2) )? (parseInt($(window).height()/2)-parseInt($(this).height()/2)):(parseInt($(this).height()/2)-parseInt($(window).height()/2)); 
			$(this).css({
				'left' : 'calc(50% - '+($(this).width()/2)+'px)',
				'top' : '0px'
			}); 
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
		if(!DM_LOADED_AD) {//관리자는 제외
			if(popupStyle=='layer' ){
				$(".designPopupBar",popup).css("cursor","move");
				$(this).draggable({handle: $(".designPopupBar",popup)});
			}
		}
	});
 

	// 쿠폰사용가능한 상품 조회하기 (적용대상조회)
	$('.coupongoodsreviewbtn').click(function() {
		var coupon_type = $(this).attr("coupon_type");
		var use_type = $(this).attr("use_type");
		var issue_type = $(this).attr("issue_type");
		var coupon_seq = $(this).attr("coupon_seq"); 
		var coupongoodsreviewerurl = '../coupon/coupongoodsreviewer?no='+coupon_seq+'&coupon_type='+coupon_type;
		var coupon_name = $(this).attr("coupon_name");
		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			addFormDialog(coupongoodsreviewerurl, '97%', '', '쿠폰정보','false');
		}else{
			if( use_type == 'offline' ) {
				addFormDialog(coupongoodsreviewerurl, '650', '', '쿠폰정보','false');
			}else{
				addFormDialog(coupongoodsreviewerurl, '450', '', '쿠폰정보','false');
			}
		}
	});
	
	$('img.newcoupondownbtn_finisn').click(function() {
		openDialogAlert("고객님은 이미 쿠폰을 받으셨습니다.",'400','160');
		return false;
	});
	
	$('img.newcoupondownbtn_no').click(function() {
		openDialogAlert("죄송합니다. 회원님은 대상이 아닙니다.",'400','160'); 
		return false;
	});
	
	
	//쿠폰상품 조회후 상품검색창
	$("input:button[name=goodssearchbtn]").live("click",function(){
		var goods_seq		= $("#coupongoods_goods_seq").val();
		var coupon_seq	= $(this).attr("coupon_seq");

		if(!goods_seq) {
				openDialogAlert("상품 고유값을 정확히 입력해 주세요.",'260','140',function(){$("#coupongoods_goods_seq").focus();return;});
		}else{ 
			$.ajax({
				'url' : '../coupon/coupongoodssearch',
				'data' : {'coupon':coupon_seq,'goods':goods_seq},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){ 
					$(".coupongoodsreviewerno").hide();//상품사용불가
					$(".coupongoodsrevieweryes").hide();//쿠폰사용가능
					if( res.result == 'goodsyes' ) {  
						var imgsrc = (eval("res.goods.src"))?res.goods.src:"/admin/skin/default/images/common/noimage_list.gif";
						$(".coupongoodsrevieweryes").show(); 
						$(".coupongoodsrevieweryes .issueGoods").find(".image").html("<img class=\"goodsThumbView\"  alt=\"\" src=\""+imgsrc+"\" width=\"50\" height=\"50\">"); 
						$(".coupongoodsrevieweryes .issueGoods").find(".name").html(res.goods.name);
						$(".coupongoodsrevieweryes .issueGoods").find(".price").html(res.goods.price);
						$(".coupongoodsrevieweryes .issueGoods").attr("goods_seq",goods_seq); 
						
						openDialog('상품 고유번호 찾기',"coupongoodsreviewerpopup",{"width":"480","height":"280"});
					}else if( res.result == 'goodsno' ) {  						
						var imgsrc = (eval("res.goods.src"))?res.goods.src:"/admin/skin/default/images/common/noimage_list.gif";
						$(".coupongoodsreviewerno").show();
						$(".coupongoodsrevieweryes .issueGoods").find(".image").html("<img class=\"goodsThumbView\"  alt=\"\" src=\""+imgsrc+"\" width=\"50\" height=\"50\">"); 
						$(".coupongoodsrevieweryes .issueGoods").find(".name").html(res.goods.name);
						$(".coupongoodsrevieweryes .issueGoods").find(".price").html(res.goods.price);
						$(".coupongoodsrevieweryes .issueGoods").attr("goods_seq",goods_seq); 
						
						openDialog('상품 고유번호 찾기',"coupongoodsreviewerpopup",{"width":"400","height":"250"});
					}else{
						openDialogAlert("상품을 찾을 수 없습니다.<br/>확인 후 다시 입력하시기 바랍니다.",'250','160'); 
					}
				}
			});
		}
	});
	
	//상품상세보기
	$('.coupongoodsdetail').live("click",function(){ 
		window.open("/goods/view?no="+$(".coupongoodsrevieweryes .issueGoods").attr("goods_seq"),'','');
	});

 
}); 
 