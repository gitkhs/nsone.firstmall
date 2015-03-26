<?php /* Template_ 2.2.6 2013/02/01 14:16:16 /www/release/seller/data/skin/default/_modules/mybag/mybag.html 000003607 */ ?>
<div id="mybag">
	<div class="background"></div>
	<div class="container">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="334"><span class="mybagTab <?php if(!$_COOKIE["mybagTabCode"]||$_COOKIE["mybagTabCode"]=='recent'){?> mybagTabOn<?php }?>" tabCode="recent"><img src="/data/skin/default/images/design/btm_mybag_tit_recentlyview.gif" class="offImg" /><img src="/data/skin/default/images/design/btm_mybag_tit_recentlyview_on.gif" class="onImg" /></span></td>
			<td width="301"><span class="mybagTab <?php if($_COOKIE["mybagTabCode"]=='cart'){?> mybagTabOn<?php }?>" tabCode="cart"><img src="/data/skin/default/images/design/btm_mybag_tit_mybag.gif" class="offImg" /><img src="/data/skin/default/images/design/btm_mybag_tit_mybag_on.gif" class="onImg" /></span></td>
			<td width="83"><img src="/data/skin/default/images/design/btm_mybag_tit.gif" /></td>
			<td align="right" class="mybag_cart_data">￦<span class="mybag_cart_total_price"></span> / <span class="mybag_cart_count"></span> item(s)</td>
			<td width="30" align="center"><img src="/data/skin/default/images/design/btm_mybag_btn_open.gif" class="mybagOpenBtn" /><img src="/data/skin/default/images/design/btm_mybag_btn_close.gif" class="mybagCloseBtn hide" /></td>
		</tr>
		</table>
		<div class="mybag_contents"></div>
	</div>
</div>

<script>
/* mybag 세팅 */
function mybag_load(){
	$("#mybag .background").css('opacity','0.3');
	$("#mybag .mybagOpenBtn, #mybag .mybagCloseBtn").click(function(){mybag_open();});
	$("#mybag .mybagTab").click(function(){	
		var thisTabObj = this;
		
		$.cookie('mybagTabCode',$(this).attr('tabCode'),{path:'/'});
		
		var action = function(){
			$("#mybag .mybagTab").removeClass('mybagTabOn');
			$(thisTabObj).addClass('mybagTabOn');
			
			$("#mybag .mybag_goods_list").hide();
			$("#mybag .mybag_"+$(thisTabObj).attr('tabCode')+"_list").show();
		};
		
		if($("#mybag .mybag_contents").html()==''){
			mybag_open(true,function(){action();});
		}else{
			mybag_open(true);
			action();
		}
	});
	
	$.ajax({
		'url' : '/common/mybag_data',
		'global' : false,
		'dataType' : 'json',
		'success' : function(res){
			$("#mybag .mybag_cart_total_price").text(comma(res.total_price));
			$("#mybag .mybag_cart_count").text(comma(res.cart_ea_sum));
			$("#mybag").slideDown();
		}
	});
}

/* 장바구니, 최근본상품, 금액 호출 및 재계산  */
function mybag_contents_load(onloadCallback){
	$.ajax({
		'url' : '/common/mybag_contents',
		'global' : false,
		'success' : function(res){
			$("#mybag .mybag_contents").html(res);
			if(onloadCallback) onloadCallback();
		}
	});
}

/* 열기 */
function mybag_open(open,onloadCallback){
	if($("#mybag .mybag_contents").is(':hidden') || open) {
		if($("#mybag .mybag_contents").html()==''){
			// Ajax로 내용 호출
			mybag_contents_load(function(){
				$("#mybag .mybag_contents").slideDown();
				$("#mybag .mybagOpenBtn").hide();
				$("#mybag .mybagCloseBtn").show();
				if(onloadCallback) onloadCallback();
			});
		}else{
			$("#mybag .mybag_contents").slideDown();
			$("#mybag .mybagOpenBtn").hide();
			$("#mybag .mybagCloseBtn").show();
		}
	}else{
		$("#mybag .mybag_contents").slideUp();
		$("#mybag .mybagOpenBtn").show();
		$("#mybag .mybagCloseBtn").hide();
	}
}

<?php if(!in_array(uri_string(),array('order/cart','order/settle'))){?>
mybag_load();
<?php }?>

</script>