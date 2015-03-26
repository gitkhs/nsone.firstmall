// 함수부만 따로 호출
document.write('<script type="text/javascript" src="/app/javascript/js/common-function.js?v=140729"></script>');

$(function(){	
	/* 스타일적용 */
	apply_input_style();
	
	//상품디스플레이의 동영상클릭시 -> 동영상자동실행설정되어있어야함
	$(".goodsDisplayVideoWrap").bind("click",function(e){
		$(this).find("img").addClass("hide");
		$(this).find(".thumbnailvideo").hide();
		$(this).find(".mobilethumbnailvideo").hide();
		$(this).find("iframe").removeClass("hide");
		$(this).find("embed").removeClass("hide");
	});
	
	//동영상넣기의 동영상클릭시-> 동영상자동실행설정되어있어야함
	$(".DisplayVideoWrap").bind("click",function(e){
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
});

$(window).load(function() {
	/* 스타일적용 */
	chk_small_goods_image();
	/*	
	$('img.small_goods_image').each(function() {	 
		if (!this.complete ) {// image was broken, replace with your new image
			this.src = '/data/icon/goods/error/noimage_list.gif';
		}
	});
	*/	
	
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

});


String.prototype.replaceAll = function (str1,str2){
	var str    = this;     
	var result   = str.replace(eval("/"+str1+"/gi"),str2);
	return result;
}

// 통계서버로 통계데이터 전달
function statistics_firstmall(act,goods_seq,order_seq,review_point)
{
	var url = '/_firstmallplus/statistics';
	var allFormValues = "act="+act+"&goods_seq="+goods_seq;
	if( order_seq ) allFormValues += "&order_seq="+order_seq;
	if( review_point ) allFormValues += "&review_point="+review_point;
	
	if(act == 'order' && !order_seq) return false;
	if(act == 'review' && !review_point) return false;
	if(!goods_seq) return false;
	$.ajax({
		cache:false,
		timeout:1000,  
		type:"POST",
		url:url,
		data:allFormValues,
		error:function(){},
		success:function(response){}
	});
	return true;
}