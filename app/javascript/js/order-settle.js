function check_shipping_method(){
	var idx = $("select[name='international'] option:selected").val();
	$("div.shipping_method_radio").each(function(){
		$(this).hide();
	});
	if(!idx)idx = 0;
	$("div.shipping_method_radio").eq(idx).show();

	if(idx == 0){
		$(".domestic").show();
		$(".international").hide();
	}else{
		$(".international").show(); 
		$(".domestic").hide();
	}
}

function order_price_calculate()
{
	var f = $("form#orderFrm");
	
	f.attr("action","calculate?mode="+gl_mode);
	f.attr("target","actionFrame");
	f[0].submit();	
}


function set_pay_button(){ 
	$.ajax({
		'url' : '../order/settle_order_images',  
		'dataType': 'json',
		'success': function(data) {
			if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
				btn_order_pay1 = '결제하기';
				btn_order_pay2 = '주문하기'; 
			}else{
				if($("#pay>img").attr("src")=='/data/skin/'+gl_skin+'/images/buttons/btn_pay.gif' || $("#pay>img").attr("src")=='/data/skin/'+gl_skin+'/images/buttons/btn_order.gif'){
					var btn_order_pay1 = '<img src="/data/skin/'+gl_skin+'/images/buttons/btn_pay.gif" />';
					var btn_order_pay2 = '<img src="/data/skin/'+gl_skin+'/images/buttons/btn_order.gif" />'; 
				}else{
					var btn_order_pay1 = '<img src="/data/skin/'+gl_skin+'/images/design/btn_order_pay.gif" />';
					var btn_order_pay2 = '<img src="/data/skin/'+gl_skin+'/images/design/btn_order.gif" />'; 

					if(data.btn_order_pay1) btn_order_pay1 = '<img src="'+data.btn_order_pay1+'" />';
					if(data.btn_order_pay2) btn_order_pay2 = '<img src="'+data.btn_order_pay2+'" />';
				}
			}

			$("#pay").html(btn_order_pay1);
			$("input[name='payment']:checked").each(function(){
				if( $(this).val() == "bank" ){
					$("#pay").html(btn_order_pay2);
				}else{
					//쿠폰의 무통장쿠폰인 경우 점검 
					if( eval('$("#coupon_sale_payment_b")') ){  
						var coupon_sale_payment_b = $("#coupon_sale_payment_b").val();
						if(coupon_sale_payment_b>0){ 
							openDialogAlert('현재 무통장 전용 쿠폰을 사용하셨습니다.<br />결제수단을 무통장으로 변경해 주세요!',400,150);
							return false;
						}
					}
				}
				
				if( gl_cashreceiptuse > 0 || gl_taxuse >0 ){
					$("#typereceiptcardlay").hide();
					$("#typereceipttablelay").show();
					
					if( $(this).val() == "card" ||  $(this).val() == "cellphone" || $(this).val() == "kakaopay" ){
						$("#typereceiptcardlay").show();
						$("#typereceipttablelay").hide();
						$(".typereceiptlay").hide();
						$("#typereceiptlay").hide();
					}else{
						$("#typereceiptlay").show();
					}
				}
			});
		}
	});
}

function reverse_pay_button(){
	$("div.pay_layer").eq(0).show();
	$("div.pay_layer").eq(1).hide();
}

function reverse_pay_layer(){
	
	$('#wrap').show();
	$('#layer_pay').hide();
	reverse_pay_button();
}

$(function() {
	// 배송지 정보 채우기 t
	$("input#copy_order_info").bind("click",function(){
		if( $(this).attr("checked") ){
			$("input[name='order_zipcode[]']").each(function(idx){
				//if ($("input[name='order_zipcode[]']").eq(idx).val()) 
				{
					$("input[name='recipient_zipcode[]']").eq(idx).val( $("input[name='order_zipcode[]']").eq(idx).val() );
				}
			});

			//if ($("input[name='order_address_type']").val()) 
			{
				$("input[name='recipient_address_type']").val( $("input[name='order_address_type']").val() );
			}

			//if ($("input[name='order_address']").val()) 
			{
				$("input[name='recipient_address']").val( $("input[name='order_address']").val() );
			}

			//if ($("input[name='order_address_street']").val()) 
			{
				$("input[name='recipient_address_street']").val( $("input[name='order_address_street']").val() );
			}

			if( eval("$(\"input[name='order_address_street']\").val()") ) {
				if($("input[name='order_address_type']").val() == "street"){
					$("input[name='recipient_address']").hide();
					$("input[name='recipient_address_street']").show();
				}else{
					$("input[name='recipient_address']").show();
					$("input[name='recipient_address_street']").hide();
				}
			}

			//if ($("input[name='order_address_detail']").val()) 
			{
				$("input[name='recipient_address_detail']").val( $("input[name='order_address_detail']").val() );
			}

			$("input[name='recipient_user_name']").val( $("input[name='order_user_name']").val() );

			$("input[name='order_phone[]']").each(function(idx){
				$("input[name='recipient_phone[]']").eq(idx).val( $("input[name='order_phone[]']").eq(idx).val() );
				$("input[name='international_recipient_phone[]']").eq(idx).val( $("input[name='order_phone[]']").eq(idx).val() );
			});

			$("input[name='order_cellphone[]']").each(function(idx){
				$("input[name='recipient_cellphone[]']").eq(idx).val( $("input[name='order_cellphone[]']").eq(idx).val() );
				$("input[name='international_recipient_cellphone[]']").eq(idx).val( $("input[name='order_cellphone[]']").eq(idx).val() );
			});

			$("input[name='recipient_email']").val( $("input[name='order_email']").val() );

		}else{
			$("input[name='order_zipcode[]']").each(function(idx){
				//if ($("input[name='order_zipcode[]']").eq(idx).val()) 
				{
					$("input[name='recipient_zipcode[]']").eq(idx).val("");
				}
			});

			//if ($("input[name='order_address_type']").val()) 
			{
				$("input[name='recipient_address_type']").val("");
			}

			//if ($("input[name='order_address']").val()) 
			{
				$("input[name='recipient_address']").val("");
			}

			//if ($("input[name='order_address_street']").val()) 
			{
				$("input[name='recipient_address_street']").val("");
			}

			//if ($("input[name='order_address_detail']").val()) 
			{
				$("input[name='recipient_address_detail']").val("");
			}

			$("input[name='recipient_user_name']").val("");

			$("input[name='order_phone[]']").each(function(idx){
				$("input[name='recipient_phone[]']").eq(idx).val("");
			});

			$("input[name='order_cellphone[]']").each(function(idx){
				$("input[name='recipient_cellphone[]']").eq(idx).val("");
			});

			$("input[name='recipient_email']").val("");

		}
		order_price_calculate();
	});

	// 해외/국내 배송 선택
	$("select[name='international']").bind("change",function(){
		check_shipping_method();
	});
	
	
	
	// 해외배송 방법 선택 시
	$("input[name='shipping_method_international'], select[name='shipping_method_international']").bind("click",function(){		
		$("select[name='region'] option").remove();
		var idx = $(this).val();
		if (idx != '') {
			for(var i=0;i<gl_region[idx].length;i++){
				$("select[name='region']").append("<option value='"+i+"'>"+gl_region[idx][i]+"</option>");
			}
		}
	});

	// 결제 방법 선택
	$("input[name='payment']").bind("change",function(){
		if($(this).is(":checked")){
			if( $(this).val() == "bank" ){ // 무통장
				$(".bank").show();
				$(".kakaopay").hide();
			}else if($(this).val() == "kakaopay"){
				$(".bank").hide();
				$(".kakaopay").show();
			}else{
				$(".bank").hide();
				$(".kakaopay").hide();
			}
			set_pay_button();
			//reverse_pay_button();
			check_typereceipt();
		}
	});
	$("input[name='payment']").first().attr("checked",true).trigger('change');
	if($("input[name='payment']").length%2==1) $("input[name='payment']").closest('ul#payment_type').children('li').last().width('100%');

	// 결제금액 계산
	$("button#coupon_order, button#coupon_cancel").bind("click",function(){

		if($(this).attr('id')=='coupon_cancel'){
			$("select.coupon_select").val('').change();
		}

		$("select.coupon_select").each(function(){
			var str = $(this).attr('id');
			var arr = str.split('_');
			var cart_seq = arr[1];
			var cart_option_seq = arr[2];
			$("input[name='coupon_download["+cart_seq+"]["+cart_option_seq+"]']").val($(this).find("option:selected").val());
		});

		$("select.shipping_coupon_select").each(function(){
			var shipping_coupon_sale = $(this).find("option:selected").attr("sale");
			var download_seq = $(this).find("option:selected").val();

			$("#download_seq").val(download_seq);
			$("#shipping_coupon_sale").val(shipping_coupon_sale);
		});
		order_price_calculate();

		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			$("#coupon_apply").click();
		}else{
			closeDialog("coupon_apply_dialog");
		}
	});

	$("select[name='international'], select[name='region']").bind("change",function(){
		order_price_calculate();
	});
	$("input[name='shipping_method'], input[name='shipping_method_international']").bind("click",function(){
		order_price_calculate();
	});
	$("input[name='recipient_zipcode[]'], input[name='recipient_address'], input[name='emoney']").bind("blur",function(){
		order_price_calculate();
	});
	/*모바일 배송방법 변경시 배송비 계산 추가 leewh 2014-12-12 */
	$("select[name='shipping_method'], select[name='shipping_method_international']").bind("change",function(){
		order_price_calculate();
	});
	
	
	$("#pay").bind("click",function(){

		// 다중배송 모드일때
		if($("#multiShippingChk").is(":checked")){

			// 배송지가 없을때
			if($("#multiShippingContainer .multiShippingItem").length==0){
				openDialogAlert("배송지추가 버튼을 클릭해 주세요.<br /> 배송지추가 버튼 클릭 시 주문상품 및 배송지가 저장됩니다.",400,150);
				return false;
			}

			// 남은 수량
			var remain = 0;
			$("input[name='mainEaMax[]']").each(function(i){
				var max = num($(this).val());
				var sum = 0;
				$("#multiShippingContainer .shippingGoodsList").each(function(){
					$(".eaInput",this).eq(i).each(function(){
						var value = $(this).val() ? num($(this).val()) : 0;
						sum += value;
					});
				});
				remain += max-sum;
			});
			if(remain){
				if(remain_ea()){
					openDialogAlert("아직 보낼 수량이 남았습니다.",400,140);
					return false;
				}else{
					openDialogAlert("배송지 추가 버튼을 클릭해 주세요.",400,140);
					return false;
				}
			}
		}

		if(!gl_isuser){
			if($("input[name='agree']:checked").val()!='Y'){
				alert('개인정보 수집ㆍ이용에 동의하셔야 합니다.');
				$("input[name='agree']").focus();
				return false;
			}
		} 

		if(gl_iscancellation){
			if($("input[name='cancellation']:checked").val()!='Y'){
				alert('청약철회 관련방침에 동의하셔야 합니다.');
				$("input[name='cancellation']").focus();
				return false;
				/**/
			}
		}

		if	($("textarea[name='memo']").val() == $("textarea[name='memo']").attr('title')){
			$("textarea[name='memo']").val('');
		}
		
		//레이어 결제창
		var mobile_new = '';
		if((gl_pg_company == 'inicis') && $("input[name='mobilenew']")){  
			$("input[name='mobilenew']").val('N');
		}	//이니시스는 iframe 사용 안함

		if($("#layer_pay").length > 0 && $("input[name='mobilenew']")) mobile_new = $("input[name='mobilenew']").val();

		var f = $("form#orderFrm");
		f.attr("action",gl_ssl_action);
		

		// 카카오페이 일경우 다른 PG 레이어를 타지 않음.
		var sel_payment	= $("input[name='payment']:checked").val();
		if(sel_payment == 'kakaopay'){
			f.attr("target","actionFrame");
			$("iframe[name='actionFrame']").hide();
		}else{
		
			if(gl_pg_company != 'inicis') {
				if(mobile_new == 'y' && gl_mobile && gl_pg_company && $("input[name='payment']:checked").val() != 'bank'){
					f.attr("target","tar_opener");
				}else{
					f.attr("target","actionFrame");
				}
			}

			if(gl_mobile && $("input[name='payment']:checked").val() != 'bank'){
				if(mobile_new == 'y'){
					mobile_pay_layer();
				}else{

					if(gl_pg_company != 'inicis' ) {
						// 2014-10-23 iphone 버전이 8.1 일경우 결제팝업은 ssl 암호화 리턴 이후 띄운다. (app/controllers/order.php)
						var iphone_ver = 0;
						if(navigator.userAgent.match(/iPhone/i)){
							if(navigator.userAgent.match(/8_1/)) iphone_ver = 81;
						}
						if(iphone_ver == 0){
							if(gl_pg_company == 'inicis'){
								//inicis_mobile_popup();
							}else{
								mobile_popup();
							}
						}
					}
				}
			}
		}

		f.submit();

	});

	// 쿠폰사용 다이얼로그
	$("button#coupon_apply").bind("click",function(){
		sametime_coupon_dialog();//쿠폰사용공통
	});
	
	// 쿠폰사용가능한 상품 조회하기 (적용대상조회) 
	$('.ordercoupongoodsreviewbtn').live("click",function(){ 
		var download_seq = $(this).closest("table").find("select.coupon_select option:selected").val();  
		if(!download_seq) {
				openDialogAlert("상품쿠폰을 선택해 주세요!",400,150);
				return false;
		}
		var ndate = new Date().getTime();
		var coupongoodsreviewerurl = '../coupon/coupongoodsreviewer?no='+download_seq+'&download_seq='+download_seq+'&n='+ndate;
		var coupon_name = $(this).attr("coupon_name");
		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			addFormDialog(coupongoodsreviewerurl, '300', '', '쿠폰정보 확인하기','false');
		}else{
			addFormDialog(coupongoodsreviewerurl, '450', '', '쿠폰정보 확인하기','false');
		}
	});
	
	//배송쿠폰정보
	$('.shippingcoupongoodsreviewbtn').live("click",function(){ 
		var download_seq = $("select.shipping_coupon_select option:selected").val();
		if(!download_seq) {
				openDialogAlert("배송쿠폰을 선택해 주세요!",400,150);
				return false;
		}
		var shipping_coupon_sale = $("select.shipping_coupon_select option:selected").attr("sale");
		var ndate = new Date().getTime();
		var coupongoodsreviewerurl = '../coupon/coupongoodsreviewer?no='+download_seq+'&download_seq='+download_seq+'&n='+ndate;
		var coupon_name = $(this).attr("coupon_name"); 
		
		if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
			addFormDialog(coupongoodsreviewerurl, '300', '', '쿠폰정보 확인하기','false');
		}else{
			addFormDialog(coupongoodsreviewerurl, '450', '', '쿠폰정보 확인하기','false');
		}
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
						$(".coupongoodsrevieweryes .issueGoods").find(".image").html('<img class="goodsThumbView" alt="" src="'+imgsrc+'" width="50" height="50">'); 
						$(".coupongoodsrevieweryes .issueGoods").find(".name").html(res.goods.name);
						$(".coupongoodsrevieweryes .issueGoods").find(".price").html(res.goods.price);
						$(".coupongoodsrevieweryes .issueGoods").attr("goods_seq",goods_seq); 
						
						openDialog('상품 고유번호 찾기',"coupongoodsreviewerpopup",{"width":"480","height":"250"});
					}else if( res.result == 'goodsno' ) {  						
						var imgsrc = (eval("res.goods.src"))?res.goods.src:"/admin/skin/default/images/common/noimage_list.gif";
						$(".coupongoodsreviewerno").show();
						$(".coupongoodsrevieweryes .issueGoods").find(".image").html('<img class="goodsThumbView" alt="" src="'+imgsrc+'" width="50" height="50">'); 
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

	set_pay_button();
	check_shipping_method();

	$('.couponDownload').bind("click",function() {		
		if(!gl_isuser){
			location.href="/member/login?return_url="+gl_request_uri;
			return false;
		}		
		var gl_goods_seq = $(this).attr("goods_seq");
		coupondownlist(gl_goods_seq,gl_request_uri); 
	});

	$("button[name='couponDownloadButton']").live("click",function(){
		var url = '../coupon/download?goods='+$(this).attr('goods')+'&coupon='+$(this).attr('coupon');
		actionFrame.location.href = url;
	});

	order_price_calculate();

	// 영수증 발급을 클릭했을경우
	$("input[name='typereceipt']").bind("click",function() {		
		check_typereceipt();
	});

	/**
	 * 세금계산서 폼체크를 삭제한다.
	 */
	function taxRemoveClass() {
		$('#co_name').removeClass('required');
		$('#co_ceo').removeClass('required');
		$('#busi_no').removeClass('required');
		$('#co_zipcode').removeClass('required');
		$('#co_address').removeClass('required');
		$('#co_status').removeClass('required');
		$('#co_type').removeClass('required');
	}
	
	/**
	 * 매출증빙폼노출
	 */
	function check_typereceipt()
	{		
		var obj =  $("input[name='typereceipt']:checked");

		if(obj.val() == 0) {
			$('#cash_container').hide();
			$('#tax_container').hide();
			taxRemoveClass();
			cashRemoveClass();
		}
		// 세금계산서 신청일 경우
		else if(obj.val() == 1) {
			$('#tax_container').show();
			$('#cash_container').hide();

			$('#co_name').attr('title', ' ').addClass('required');
			$('#co_ceo').attr('title', ' ').addClass('required');
			$('#busi_no').attr('title', ' ').addClass('required').addClass('busiNo');
			$('#co_zipcode').attr('title', ' ').addClass('required');
			$('#co_address').attr('title', ' ').addClass('required');
			$('#co_status').attr('title', ' ').addClass('required');
			$('#co_type').attr('title', ' ').addClass('required');

			cashRemoveClass();
		}
		// 현금영수증 신청일 경우
		else if(obj.val() == 2) {
			$('#cash_container').show();
			$('#tax_container').hide();
			$('#creceipt_number').attr('title', ' ').addClass('required').addClass('numberHyphen');
			taxRemoveClass();
		}
		if( $("input[name='payment']:checked").val() == 'bank'){
			$("#duplicate_message").hide();
		}else{
			$("#duplicate_message").show();			
		}
	}

	/**
	 * 현금영수증 폼체크를 삭제한다.
	 */
	function cashRemoveClass() {
		$('#creceipt_number').removeClass('required').val('');
	}

	//현금영수증 개인공제용
	$("#cuse0").click(function(){
		$("#personallay").show();
		$("#businesslay").hide();
	});
	//현금영수증 사업자지출증빙용
	$("#cuse1").click(function(){
		$("#personallay").hide();
		$("#businesslay").show();
	});

	

	$(".setttlefblikebtn").click(function(){
		window.open('http://'+gl_sub_domain+'/admin/sns/domain_facebook?fblike_return_url='+gl_http_host,'','width=750px,height=500px,toolbar=no,location=no,resizable=yes, scrollbars=yes');
	});	

	$("input[name='gift_use']").change(function(){
		var type = $(this).attr('type');
		var value = $(this).val();

		if(type=='checkbox') value = $(this).is(":checked") ? 'Y' : 'N';

		if(value=='Y'){
			$(".giftTable").show();
			
			if($("#multiShippingChk").is(":checked")){
				$(".giftReceiveTr").show();
			}
		}else if(value=='N'){
			$(".giftTable").hide();
			
			if($("#multiShippingChk").is(":checked")){
				$(".giftReceiveTr").hide();
			}
		}
	});


	//emoney 입력후 엔터
	$("input[name='cash_view']").bind("keydown", function(e) {
		if (e.keyCode == 13) { // enter key
			use_cash();
			return false
		}
	});

	//emoney 입력후 엔터
	$("input[name='emoney_view']").bind("keydown", function(e) {
		if (e.keyCode == 13) { // enter key
			use_emoney();
			return false
		}
	});


	//프로모션코드입력후 엔터
	$("#cartpromotioncode").bind("keydown", function(e) {
		if (e.keyCode == 13) { // enter key
			getPromotionck();
			return false
		}
	});
	
	/* 다중배송 사용여부 체크 액션 */
	$("#multiShippingChk").change(function(){
		if($(this).is(":checked")){
			$("#shippingMainContainer").addClass('multiShipping');
			if($("#multiShippingContainer").children().length){
				$("#multiShippingContainer").show();
			}else{
				$("#multiShippingContainer").hide();
			}
			
			$("select.international").val('0').click();
			$("input[name='shipping_method']").eq(0).attr('checked','checked').click();
			$("tr.shipping_tr").hide();
		}else{
			$("#shippingMainContainer").removeClass('multiShipping');
			$("#multiShippingContainer").hide();
			
			$("select.international").val('1').click();
			$("tr.shipping_tr").show();
			order_price_calculate();
		}
	});

	/* 배송지 삭제 버튼 액션 */
	$(".multiShippingItemDelBtn").live('click',function(){
		var multiShippingItemObj = $(this).closest(".multiShippingItem");
		openDialogConfirm('배송지를 삭제하시겠습니까?',400,140,function(){
			multiShippingItemObj.remove();

			$("#multiShippingContainer .multiShippingItem").each(function(i){
				$(".multiShippingItemPrnNo",this).html(i+1);
				$(".multiCartGoodsSeq",this).attr('name',"multiCartGoodsSeq["+i+"][]");
				$(".multiCartOptionSeq",this).attr('name',"multiCartOptionSeq["+i+"][]");
				$(".multiCartSuboptionSeq",this).attr('name',"multiCartSuboptionSeq["+i+"][]");
				$(".eaInput",this).attr('name',"multiEaInput["+i+"][]");
			});

			if($("#multiShippingContainer").children().length==0){
				$("#multiShippingContainer").hide();
			}

			check_shipping_ea();
			order_price_calculate();
		});		
	});

	/* 배송지 추가 버튼 액션 */
	$(".multiShippingItemDefault").find("input,text,select,textarea").attr('disabled',true);
	$("#shippingMainContainer .shippingAddBtn img").click(function(){

		// 상품수량 입력체크
		var ea		= 0;
		var sum		= 0;
		var csum	= 0;
		var gsum	= 0;
		$("#shippingMainContainer .eaInput").each(function(){
			ea	= $(this).val() ? num($(this).val()) : 0;
			if	(ea > 0){
				if	($(this).attr('goods_kind') == 'coupon')	csum	+= ea;
				else											gsum	+= ea;
				sum	+= ea;
			}
		});

		if (!$("input[name='chkQuickAddress']").is(":checked")) {
			openDialogAlert("배송주소를 선택해 주세요.",400,140,function(){
				$("input[name='chkQuickAddress']:first").focus();
			});
			return false;
		}

		if(sum <= 0){
			openDialogAlert("해당 배송지에 보낼 수량을 입력해주세요",400,140,function(){
				$("#shippingMainContainer .eaInput").eq(0).focus();
			});
			return false;
		}

		var html = "<div class='multiShippingItemDefault'>" + $(".multiShippingItemDefault").html() + "</div>";
		var multiShippingItemIdx = $("#multiShippingContainer .multiShippingItem").length;

		html = html.replace("multiCartGoodsSeq[]","multiCartGoodsSeq["+multiShippingItemIdx+"][]");
		html = html.replace("multiCartOptionSeq[]","multiCartOptionSeq["+multiShippingItemIdx+"][]");
		html = html.replace("multiCartSuboptionSeq[]","multiCartSuboptionSeq["+multiShippingItemIdx+"][]");
		html = html.replace("multiEaInput[]","multiEaInput["+multiShippingItemIdx+"][]");

		var multiShippingItemClone = $(html).appendTo($("#multiShippingContainer")).removeClass('multiShippingItemDefault').addClass('multiShippingItem');

		// 쿠폰 상품만 있을 시 주소 정보 hidden 처리
		if	(gsum > 0){
			$(multiShippingItemClone).find("input[name='multi_is_goods[]']").val('1');
			$(multiShippingItemClone).find(".goods_delivery_info").show();
		}else{
			$(multiShippingItemClone).find("input[name='multi_is_goods[]']").val('0');
			$(multiShippingItemClone).find(".goods_delivery_info").hide();
		}

		// 순번key생성
		var multiShippingItemNo = multiShippingItemNoCnt++;
		multiShippingItemClone.attr('multiShippingItemNo',multiShippingItemNo);

		// 순서 표시
		$("#multiShippingContainer .multiShippingItem").each(function(i){
			$(".multiShippingItemPrnNo",this).html(i+1);
		});
		//$(".multiShippingItemPrnNo",multiShippingItemClone).html($("#multiShippingContainer .multiShippingItem").length+1);

		// 입력폼활성화
		$(multiShippingItemClone).find("input,text,select,textarea").removeAttr('disabled');

		// 메인 입력값 대입
		$("input[name='multi_recipient_user_name[]']",multiShippingItemClone).val($("input[name='recipient_user_name']").val());
		$("input[name='multi_recipient_zipcode[0][]']",multiShippingItemClone).val($("input[name='recipient_zipcode[]']:eq(0)").val());
		$("input[name='multi_recipient_zipcode[1][]']",multiShippingItemClone).val($("input[name='recipient_zipcode[]']:eq(1)").val());
		$("input[name='multi_recipient_address[]']",multiShippingItemClone).val($("input[name='recipient_address']").val());
		$("input[name='multi_recipient_address_street[]']",multiShippingItemClone).val($("input[name='recipient_address_street']").val());
		$("input[name='multi_recipient_address_detail[]']",multiShippingItemClone).val($("input[name='recipient_address_detail']").val());
		$("input[name='multi_recipient_phone[0][]']",multiShippingItemClone).val($("input[name='recipient_phone[]']:eq(0)").val());
		$("input[name='multi_recipient_phone[1][]']",multiShippingItemClone).val($("input[name='recipient_phone[]']:eq(1)").val());
		$("input[name='multi_recipient_phone[2][]']",multiShippingItemClone).val($("input[name='recipient_phone[]']:eq(2)").val());
		$("input[name='multi_recipient_cellphone[0][]']",multiShippingItemClone).val($("input[name='recipient_cellphone[]']:eq(0)").val());
		$("input[name='multi_recipient_cellphone[1][]']",multiShippingItemClone).val($("input[name='recipient_cellphone[]']:eq(1)").val());
		$("input[name='multi_recipient_cellphone[2][]']",multiShippingItemClone).val($("input[name='recipient_cellphone[]']:eq(2)").val());
		$("input[name='multi_recipient_email[]']",multiShippingItemClone).val($("input[name='recipient_email']").val());
		$("textarea[name='multi_memo[]']",multiShippingItemClone).val($("textarea[name='memo']").val());
		if($("textarea[name='multi_memo[]']",multiShippingItemClone).val() == $("textarea[name='memo']").attr('title')){
			$("textarea[name='multi_memo[]']",multiShippingItemClone).val('');
		}
		$("#shippingMainContainer .eaInput").each(function(i){
			$(".eaInput",multiShippingItemClone).eq(i).val($(this).val());
		});

		// 출력
		//$("#multiShippingContainer").append(multiShippingItemClone);
		if($("#multiShippingContainer").children().length){
			$("#multiShippingContainer").show();
		}
		
		$("input[name='giftReceiveShippingIdx']",multiShippingItemClone).click();

		// input값 key세팅
		$("#multiShippingContainer .multiShippingItem").each(function(i){
			$(".multiCartGoodsSeq",this).attr('name',"multiCartGoodsSeq["+i+"][]");
			$(".multiCartOptionSeq",this).attr('name',"multiCartOptionSeq["+i+"][]");
			$(".multiCartSuboptionSeq",this).attr('name',"multiCartSuboptionSeq["+i+"][]");
			$(".eaInput",this).attr('name',"multiEaInput["+i+"][]");
		});

		multiShippingItemZeroClose(multiShippingItemClone);

		// 배송지 받는분 이름 표시
		$(".multi_recipient_user_name",multiShippingItemClone).change();
		
		// 메인 입력폼 초기화
		$("#shippingMainContainer input,#shippingMainContainer textarea").not("input[type='hidden'],input[name='chkQuickAddress']").val('').focusout();
		$("input[name='chkQuickAddress']").attr('checked', false);

		check_shipping_ea();
	});

	/* 남은 수량 표시 */
	check_shipping_ea();

	// 보낼수량 최대값 제한, 추가옵션 개별출고 불가상품 처리
	$(".shippingGoodsList .eaInput")
	.live('blur',function(){
		var value = $(this).val() ? num($(this).val()) : 0;
		$(this).val(value);
		order_price_calculate();
	})
	.live('change keyup',function(){
		var i = $(this).closest(".shippingGoodsList").find(".eaInput").index(this);
		var max = num($("input[name='mainEaMax[]']").eq(i).val());
		var otherSum = 0;
		var thisObj = this;

		$(".shippingGoodsList").each(function(){
			$(".eaInput",this).eq(i).not(thisObj).each(function(){
				var value = $(this).val() ? num($(this).val()) : 0;
				otherSum += value;
			});
		});
		
		if(otherSum+num($(this).val())>max){
			$(this).val(max-otherSum);
		}
		
		if($(this).attr("opt_type")=="opt"){
			if(num($(this).val())>0 && otherSum+num($(this).val())==max){
				$(this).closest(".shippingGoodsList").find("input.eaInput[cart_option_seq='"+$(this).attr('cart_option_seq')+"'][opt_type='sub']").each(function(){
					$(this).val(num($(this).closest('tr').find(".remainEa").html())+num($(this).val())).attr("readonly",true).addClass("disabled");
					$(this).closest('tr').find(".remainEa").html(0);
				});
			}else if(num($(this).val())==0){
				$(this).closest(".shippingGoodsList").find("input.eaInput[cart_option_seq='"+$(this).attr('cart_option_seq')+"'][opt_type='sub']").each(function(){
					$(this).val(0).attr("readonly",true).addClass("disabled");
				});
			}else{
				$(this).closest(".shippingGoodsList").find("input.eaInput[cart_option_seq='"+$(this).attr('cart_option_seq')+"'][opt_type='sub']").removeAttr("readonly").removeClass("disabled");
			}
		}

		check_shipping_ea();
	});

	$("#multiShippingContainer .eaInput")
	.live('change keyup',function(){
		if(num($(this).val())>0){
			$(this).closest('tr.sglRecord').removeClass('sglRecordEaZero');
		}else{
			$(this).closest('tr.sglRecord').addClass('sglRecordEaZero');
		}
	});

	$(".multiShippingItemZeroOpenBtn button")
	.live('click',function(){
		var multiShippingItemObj = $(this).closest('.multiShippingItem');
		multiShippingItemZeroOpen(multiShippingItemObj);
		
	});
	$(".multiShippingItemZeroCloseBtn button")
	.live('click',function(){
		var multiShippingItemObj = $(this).closest('.multiShippingItem');
		multiShippingItemZeroClose(multiShippingItemObj);
		
	});

	$(".multi_recipient_user_name")
	.live('keyup change',function(){
		$(this).closest(".multiShippingItem").find(".multi_recipient_user_name_prn").html($(this).val());
	});
	
	$("input[name='giftReceiveShippingIdx']").live('click change',function(){
		var i = $("input[name='giftReceiveShippingIdx']").index(this);
		$("input[name='giftReceiveShippingIdx']").eq(i).val(i-1);
		
	});

	//프로모션코드 실시간체크
	if(eval("$('#cartpromotioncode').val()")) {
		getPromotionckloding($("#cartpromotioncode").val())
	}

	$("input[name='recipient_address_street']").attr('readonly', true);
});


//주문시 쿠폰적용
function sametime_coupon_dialog(){ 
	if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
		if($("#coupon_goods_lay").html()=='') getCouponAjaxList();
		if($("#coupon_apply_division").is(":visible")){
			$("#coupon_apply_division").children().slideUp(function(){
				$("#coupon_apply_division").hide();
			});
			$(".btn_arw_dn").show();
			$(".btn_arw_up").hide();
		}else{
			$("#coupon_apply_division").show();
			$("#coupon_apply_division").children().hide().slideDown();
			$(".btn_arw_dn").hide();
			$(".btn_arw_up").show();
		}
	}else{
		getCouponAjaxList();
		//openDialog("쿠폰 적용하기", "coupon_apply_dialog", {"width":800,"height":600});
	}
}

function multiShippingItemZeroOpen(multiShippingItemObj){
	$("tr.sglRecord",multiShippingItemObj).show();
	$(".multiShippingItemZeroCloseBtn",multiShippingItemObj).show();
	$(".multiShippingItemZeroOpenBtn",multiShippingItemObj).hide();
}
function multiShippingItemZeroClose(multiShippingItemObj){
	$(".eaInput",multiShippingItemObj).each(function(){
		if(num($(this).val())>0){
			$(this).closest('tr.sglRecord').show();
		}else{
			$(this).closest('tr.sglRecord').hide();
		}
	});
	$(".multiShippingItemZeroOpenBtn",multiShippingItemObj).show();
	$(".multiShippingItemZeroCloseBtn",multiShippingItemObj).hide();
}

/* 남은 수량 표시 */
function check_shipping_ea(){
	
	// 입력수량 최대값 제한, 추가옵션 개별출고 불가상품 disabled
	$(".shippingGoodsList .eaInput").each(function(){
		var i = $(this).closest(".shippingGoodsList").find(".eaInput").index(this);
		var max = num($("input[name='mainEaMax[]']").eq(i).val());
		var otherSum = 0;
		var thisObj = this;

		$(".shippingGoodsList").each(function(){
			$(".eaInput",this).eq(i).not(thisObj).each(function(){
				var value = $(this).val() ? num($(this).val()) : 0;
				otherSum += value;
			});
		});
		
		if(otherSum+num($(this).val())>max){
			$(this).val(max-otherSum);
		}
		
		if($(this).attr("opt_type")=="opt"){
			if(num($(this).val())>0 && otherSum+num($(this).val())==max){
				$(this).closest(".shippingGoodsList").find("input.eaInput[cart_option_seq='"+$(this).attr('cart_option_seq')+"'][opt_type='sub']").each(function(){
					$(this).attr("readonly",true).addClass("disabled");
					$(this).closest('tr').find(".remainEa").html(0);
				});
			}else if(num($(this).val())==0){
				$(this).closest(".shippingGoodsList").find("input.eaInput[cart_option_seq='"+$(this).attr('cart_option_seq')+"'][opt_type='sub']").each(function(){
					$(this).attr("readonly",true).addClass("disabled");
				});
			}else{
				$(this).closest(".shippingGoodsList").find("input.eaInput[cart_option_seq='"+$(this).attr('cart_option_seq')+"'][opt_type='sub']").removeAttr("readonly").removeClass("disabled");
			}
		}
	});
	
	// 남은수량 표시
	$("input[name='mainEaMax[]']").each(function(i){
		var max = num($(this).val());
		var sum = 0;
		$(".shippingGoodsList").each(function(){
			$(".eaInput",this).eq(i).each(function(){
				var value = $(this).val() ? num($(this).val()) : 0;
				sum += value;
			});
		});
		var remain = max-sum;
		$(".shippingGoodsList").each(function(){
			$(".remainEa",this).eq(i).each(function(){
				$(this).html(comma(remain));
			});
		});
	});
	
	// 수량 0이면 회색배경 처리
	$("#multiShippingContainer .eaInput")
	.each(function(){
		if(num($(this).val())>0){
			$(this).closest('tr.sglRecord').removeClass('sglRecordEaZero');
		}else{
			$(this).closest('tr.sglRecord').addClass('sglRecordEaZero');
		}
	});

	// 수량 0인 상품이 없으면 전체상품보기버튼 숨김
	$("#multiShippingContainer .multiShippingItem").each(function(){
		var zeroCnt = 0;
		$(".eaInput",this).each(function(){
			var value = $(this).val() ? num($(this).val()) : 0;
			if(value==0) zeroCnt++;
		});
		if(zeroCnt==0){
			$(".multiShippingItemZeroCloseBtn",this).hide();
		}else{
			if($('tr.sglRecord:hidden',this).length==0 && zeroCnt){
				$(".multiShippingItemZeroOpenBtn",this).hide();
				$(".multiShippingItemZeroCloseBtn",this).show();
			}else{
				$(".multiShippingItemZeroOpenBtn",this).show();
				$(".multiShippingItemZeroCloseBtn",this).hide();
			}
		}
	});

}

function remain_ea(){
	// 남은 수량
	var remain = 0;
	$("input[name='mainEaMax[]']").each(function(i){
		var max = num($(this).val());
		var sum = 0;
		$(".shippingGoodsList").each(function(){
			$(".eaInput",this).eq(i).each(function(){
				var value = $(this).val() ? num($(this).val()) : 0;
				sum += value;
			});
		});
		remain += max-sum;
	});
	return remain;
}

function delivery_address(str, orderby){
	var url = "/mypage/delivery_address?tab="+str+"&popup=1"+"&order="+orderby;
	$.get(url, function(data) {
		$("div#delivery_address_dialog").html(data);
	});
	openDialog("자주 쓰는 배송지", "delivery_address_dialog", {"width":700,"height":400});


}

function getPromotionckloding(cartpromotioncode) {
	//var cartpromotioncode = $("#cartpromotioncode").val();
	if( cartpromotioncode ) {
		$.ajax({
			'url' : '../promotion/getPromotionJson?mode='+gl_mode,
			'data' : {'cartpromotioncode':cartpromotioncode},
			'type' : 'post',
			'dataType': 'json',
			'success': function(data) {
				order_price_calculate();
			}
		});
	}
}


function getPromotionck(){
	var cartpromotioncode = $("#cartpromotioncode").val();
	if(!cartpromotioncode){
		openDialogAlert('프로모션코드를 정확히 입력해 주세요.','400','140');
		return false;
	}

	$.ajax({
		'url' : '../promotion/getPromotionJson?mode='+gl_mode,
		'data' : {'method':'settle','cartpromotioncode':cartpromotioncode},
		'type' : 'post',
		'dataType': 'json',
		'success': function(data) {
			if(data.result){
				var promotionDetailhelphtml = '<div  ><ul  >';
				promotionDetailhelphtml +=  "<li>- <b>" + data.issue_enddatetitle + "</b> </li>";
				if(data.sale_type == 'shipping_free'){
					promotionDetailhelphtml +=  "<li>- <b>기본배송비 무료 (최대 " + comma(data.max_percent_shipping_sale) + "원)</b></li>";
					promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
				}else if(data.sale_type == 'shipping_won'){
					var realprice = comma(data.won_shipping_sale);

					promotionDetailhelphtml +=  "<li>- <b>기본배송비 "+ realprice +"원 할인 </b></li>";
					promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
				}else if(data.sale_type == 'won'){
					var realprice = comma(data.won_goods_sale);

					promotionDetailhelphtml +=  "<li>- <b>"+ realprice +"원 할인 </b></li>";
					promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
					if(data.issue_type == 'all') {
							promotionDetailhelphtml +=  "<li>- 전체 사용 가능</li>";
					}else{
						if(data.goodshtml) {
							if(data.issue_type == 'except'){
								promotionDetailhelphtml +=  "<li>- "+ data.goodshtml +" 상품 사용 불가</li>";
							}else if(data.issue_type == 'issue'){
								promotionDetailhelphtml +=  "<li>- "+ data.goodshtml +" 상품 사용 가능</li>";
							}
						}

						if(data.brandhtml) {
							if(data.issue_type == 'except'){
								promotionDetailhelphtml +=  "<li>- "+ data.brandhtml +" 브랜드 사용 불가</li>";
							}else if(data.issue_type == 'issue'){
								promotionDetailhelphtml +=  "<li>- "+ data.brandhtml +" 브랜드 사용 가능</li>";
							}
						}

						if(data.categoryhtml) {
							if(data.issue_type == 'except'){
								promotionDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 불가</li>";
							}else if(data.issue_type == 'issue'){
								promotionDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 가능</li>";
							}
						}
					}
				}else{
					var realpercent = (data.percent_goods_sale);

					promotionDetailhelphtml +=  "<li>- <b>" + realpercent + "% 할인 (최대 " + comma(data.max_percent_goods_sale) + "원)</b></li>";
					promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
					if(data.issue_type == 'all') {
							promotionDetailhelphtml +=  "<li>- 전체 사용 가능</li>";
					}else{
						if(data.goodshtml) {
							if(data.issue_type == 'except'){
								promotionDetailhelphtml +=  "<li>- "+ data.goodshtml +" 상품 사용 불가</li>";
							}else if(data.issue_type == 'issue'){
								promotionDetailhelphtml +=  "<li>- "+ data.goodshtml +" 상품 사용 가능</li>";
							}
						}

						if(data.brandhtml) {
							if(data.issue_type == 'except'){
								promotionDetailhelphtml +=  "<li>- "+ data.brandhtml +" 브랜드 사용 불가</li>";
							}else if(data.issue_type == 'issue'){
								promotionDetailhelphtml +=  "<li>- "+ data.brandhtml +" 브랜드 사용 가능</li>";
							}
						}

						if(data.categoryhtml) {
							if(data.issue_type == 'except'){
								promotionDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 불가</li>";
							}else if(data.issue_type == 'issue'){
								promotionDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 가능</li>";
							}
						}
					}
				}
				promotionDetailhelphtml +=  "</ul></div><div id='openDialogLayerBtns' align='center' style='padding-top:15px'><span class='btn medium'><input type='button' value='확인' onclick=\"$('#PromotionDialog').dialog('close');\" /></span></div>";
				$("div#PromotionDialog").html(promotionDetailhelphtml);
				var promotionwidth = ($("div#PromotionDialog").width()>300)?$("div#PromotionDialog").width()+100:400;
				var promotionheight = ($("div#PromotionDialog").height()>100)?$("div#PromotionDialog").height()+100:200;

				openDialog('<table width="100%" border="0" cellpadding="0" cellspacing="0"  ><tr><td class="left pdl5" style="font-weight:bold; color:#fff; font-size:12px;">프로모션코드</td> </tr></table>', "PromotionDialog", {"width":promotionwidth,"height":promotionheight});
				//openDialogAlert(promotionDetailhelphtml,'250','180');

				$(".cartPromotionTh").show();
				$(".cartPromotionTd").show();
				$("#pricePromotionTd").show();
				$(".cartpromotioncodedellay").show();
				$(".cartpromotioncodeinputlay").hide();

			}else{
				openDialogAlert(data.msg,'400','140');
			}

			order_price_calculate();
		}
	});
}


/* 프로모션코드 초기화하기 */
function getPromotionCartDel(){
	$.ajax({
		'url' : '/promotion/getPromotionCartDel',
		'success' : function(){
			$(".cartPromotionTh").hide();
			$(".cartPromotionTd").hide();
			$("#pricePromotionTd").hide();
			$(".cartpromotioncodedellay").hide();
			$("#promotion_shipping_salse").empty();
			$(".cartpromotioncodeinputlay").show();
			order_price_calculate();
		}
	});
}

// facebook 라이크 할인 적용 및 오픈그라피
function getfblikeopengraph(){
	$.get('../order/fblike_opengraph', function(data) {
	});
}

// 쿠폰 사용 취소
function cancelCouponSelect(obj){
	obj.val("").prop("selected", true); //IE7
	obj.find("option:selected").attr("selected",false);
	obj.parents('tr').find("span.sale").html( comma( 0 ) ); 
	obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",''); 
}



//상품쿠폰선택
function getCouponselect(e){
	var obj = $(e);
	if( obj.find("option:selected").attr("value") ) {
		var oldidx = obj.parents('tr').find("span.sale").attr("oldidx");
		var oldsale = obj.parents('tr').find("span.sale").attr("oldsale"); 
		if( obj.find("option:selected").attr('couponsametime') == 'N' ) {//단독쿠폰
			if( $.cookie( "couponsametimeuse") ) {
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
			}else{
				var msg = "단독으로만 적용할 수 있는 쿠폰을 선택하셨습니다.<br/>기존에 적용된 쿠폰은 모두 해제됩니다. 적용하시겠습니까?";
			}
			openDialogConfirm(msg,400,150,function(){
				getCouponsametimeselect(obj,'goods');//선택해제
				getCouponselectreal(obj);//단독쿠폰 중복쿠폰여부
				$.cookie( "couponsametimeuse", true );
			},function(){
				if( $.cookie( "couponsametimeuse") ) {//이전단독쿠폰일경우
					cancelCouponSelect(obj);  
					if(oldidx){
						if( obj.find("option").eq(oldidx).attr('duplication') == 1 ) {//중복쿠폰이 아닌경우
							obj.find("option").eq(oldidx).attr("selected",true);
							obj.parents('tr').find("span.sale").html( comma( oldsale ) );
							//obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
						}
					} 
				}else{
					cancelCouponSelect(obj); 
					if(oldidx){ 
						obj.find("option").eq(oldidx).attr("selected",true);
						obj.parents('tr').find("span.sale").html( comma( oldsale ) );
						//obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
					} 
				}
				return false;});
		}else{//단독쿠폰아닌경우
			if( $.cookie( "couponsametimeuse") ) {//이전에 단독쿠폰 선택된 경우
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
				openDialogConfirm(msg,400,150,function() {
					getCouponsametimeselect(obj,'goods');//선택해제
					getCouponselectreal(obj);
					$.cookie( "couponsametimeuse", null );
				},function(){
					cancelCouponSelect(obj); 
					if(oldidx){
						if( obj.find("option").eq(oldidx).attr('duplication') == 1 ) {//중복쿠폰이 아닌경우
							obj.find("option").eq(oldidx).attr("selected",true);
							obj.parents('tr').find("span.sale").html( comma( oldsale ) );
							//obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
						}
					} 
					return false;});
			}else{
				getCouponselectreal(obj);
			}
		}
	}else{
		obj.val("").prop("selected", true); //IE7
		obj.find("option").attr("selected",false); //선택제외
		obj.parents('tr').find("span.sale").html( comma( 0 ) ); 
		obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
		if( $.cookie( "couponsametimeuse") ) {
			$.cookie( "couponsametimeuse", null );
		}
	}
}

//배송비쿠폰선택
function getShippingCouponselect(e) {
	var obj = $(e);
	if( obj.find("option:selected").attr("value") ) { 
		var oldidx = obj.find("option").attr("oldidx");
		var oldsale = obj.find("option").attr("oldsale"); 
		if(!oldidx) {
			obj.find("option").attr("oldidx", obj.find("option:selected").index() );
			obj.find("option").attr("oldsale",obj.find("option:selected").attr("sale")); 
		} 
		if( obj.find("option:selected").attr('couponsametime') == 'N' ) {//단독쿠폰
			if( $.cookie( "couponsametimeuse") ) {
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
			}else{
				var msg = "단독으로만 적용할 수 있는 쿠폰을 선택하셨습니다.<br/>기존에 적용된 쿠폰은 모두 해제됩니다. 적용하시겠습니까?";
			}
			openDialogConfirm(msg,400,150,function(){
				getCouponsametimeselect(obj,'');//선택해제
				$.cookie( "couponsametimeuse", true );
				return true;
			},function(){
				if( $.cookie( "couponsametimeuse") ) {
					cancelCouponSelect(obj);  
					if(oldidx){
						if( obj.find("option").eq(oldidx).attr('duplication') == 1 ) {//중복쿠폰이 아닌경우
							obj.find("option").eq(oldidx).attr("selected",true);
							obj.parents('tr').find("span.sale").html( comma( oldsale ) );
						}
					} 
				}else{
					cancelCouponSelect(obj); 
					if(oldidx){ 
						obj.find("option").eq(oldidx).attr("selected",true);
						obj.parents('tr').find("span.sale").html( comma( oldsale ) );
					} 
				}
					return false;});
		}else{
			if( $.cookie( "couponsametimeuse") ) {
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
				openDialogConfirm(msg,400,150,function(){
					getCouponsametimeselect(obj,'');//선택해제
					$.cookie( "couponsametimeuse", null );
					return true;
				},function(){
					
					cancelCouponSelect(obj);
					if(oldidx){
						obj.find("option").eq(oldidx).attr("selected",true);
						obj.parents('tr').find("span.sale").html( comma( oldsale ) ); 
					} 
					return false;
				});
			}
		}
	}else{
		obj.val("").prop("selected", true); //IE7
		obj.find("option").attr("selected",false); //선택제외 
		if( $.cookie( "couponsametimeuse") ) {
			$.cookie( "couponsametimeuse", null );
		}
	}
	var download_seq = $("select.shipping_coupon_select option:selected").val();
	if(download_seq) { 
		var shipping_coupon_sale = $("select.shipping_coupon_select option:selected").attr("sale");
		$(".shippingcoupongoodsreviewbtn").attr("download_seq",download_seq);
		$(".shipping_coupon_sale").html( comma( shipping_coupon_sale ) );
	}else{ 
		$(".shippingcoupongoodsreviewbtn").attr("download_seq",'');
		$(".shipping_coupon_sale").html( comma( 0 ) );
	}
}

//단독쿠폰으로 중복이 아닌경우 선택된 정보이외에 모두 제외
function getCouponsametimeselect(obj, coupontype){
	if( coupontype == 'goods') {//상품쿠폰은 배송비쿠폰제외
		$("select#shipping_coupon_select").each(function(){
			if( !$(this).find("option:selected").val() ) return true; //continue;
			$(this).val("").prop("selected", true); //IE7
			$(this).find("option").attr("selected",false); //선택제외
			$(".shippingcoupongoodsreviewbtn").attr("download_seq",'');
			$(".shipping_coupon_sale").html( comma( 0 ) );
		});
	}
	$("select.coupon_select").each(function(){
		if( !$(this).find("option:selected").val() ) return true; //continue;
		if( obj.attr('id') != $(this).attr("id") ) {
			$(this).val("").prop("selected", true); //IE7
			$(this).find("option").attr("selected",false); //선택제외
			$(this).parents('tr').find("span.sale").html( comma( 0 ) );
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
		}
	});
}


//쿠폰선택
function getCouponselectreal(obj) { 
	$("select.coupon_select").each(function(idx){//
		if( obj.find("option:selected").attr('duplication') == 1 ) {//중복쿠폰 
			if( obj.attr('id') != $(this).attr("id") && !$(this).find("option:selected").val() ) {//선택하지 않는 상품인경우
				$("select#"+$(this).attr("id")+" option[value='"+obj.find("option:selected").val()+"']").attr("selected",true);
			}   
			$(this).parents('tr').find("span.sale").attr("oldidx",$(this).find("option:selected").index());
			$(this).parents('tr').find("span.sale").attr("oldsale",$(this).find("option:selected").attr("sale"));
			$(this).parents('tr').find("span.sale").html( comma( $(this).find("option:selected").attr("sale") ) );
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",$(this).find("option:selected").val());
		}else{
			
			if( obj.attr('id') != $(this).attr("id") && obj.find("option:selected").attr("value") ){
				if(obj.find("option:selected").val() == $(this).find("option:selected").val()){
					$(this).find("option").eq(0).attr("selected",true);
				}
			}  
			$(this).parents('tr').find("span.sale").attr("oldidx",$(this).find("option:selected").index());
			$(this).parents('tr').find("span.sale").attr("oldsale",$(this).find("option:selected").attr("sale"));
			$(this).parents('tr').find("span.sale").html( comma( $(this).find("option:selected").attr("sale") ) );
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",$(this).find("option:selected").val());
			
		}
	});
}



/**
 * 쿠폰을 ajax로 검색한다.
 */
function getCouponAjaxList() { 
	var f = $("form#orderFrm");
	var queryString = f.formSerialize(); 
	$.ajax({
		type: 'post',
		url: './settle_coupon?mode='+gl_mode,
		data: queryString,
		dataType: 'json',
		success: function(data) {
			if( data.coupon_error ){
				$('#coupon_goods_lay').html(''); 
				$('#coupon_shipping_lay').hide();
				if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode){
					$("#coupon_apply_division").children().slideUp(function(){
						$("#coupon_apply_division").hide();
					});
					$(".btn_arw_dn").show();
					$(".btn_arw_up").hide();
				}else{
					closeDialog("coupon_apply_dialog");
				}
				openDialogAlert('적용 가능한 쿠폰이 없습니다.','400','140');
			}else{
				openDialog("쿠폰 적용하기", "coupon_apply_dialog", {"width":800,"height":600});
				if(data.coupongoods){
					$('#coupon_goods_lay').html(data.coupongoods);
				}
				if(data.checkshippingcoupons>0){
					$('#coupon_shipping_lay').show();
					$('#coupon_shipping_select').html(data.couponshipping);
				}else{
					$('#coupon_shipping_lay').hide();
				}
			}
		}
	});
}

// 쿠폰사용
function getCouponuse(seqno){
	$(".couponlay_"+seqno).hide();
	$.getJSON('../coupon/goods_coupon_max?no='+seqno, function(data) {		
		if(data){
			$(".couponlay_"+seqno).show();
			var cb_percent_tag = '';
			if(data.sale_type == 'won'){
				var cb_percent_tag = data.coupon_name + '<br/>('+comma(data.won_goods_sale) + '원)';
			}else{
				var cb_percent_tag = data.coupon_name + '<br/>('+comma(data.percent_goods_sale) + '%)';
				$(".couponlay_"+seqno+" .cb_percent").html(''+(data.coupon_name) + '<br/>('+comma(data.percent_goods_sale) + '%)	');
			}
			cb_percent_tag += '<span class="btn small black"><button type="button" >쿠폰받기</button></span>';
			$(".couponlay_"+seqno+" .cb_percent").html(cb_percent_tag); //
		}
	});
}

function getCouponDownlayerclose(){
	$('#couponDownloadDialog').dialog('close');
}

function mobile_pay_layer(){
	var divLayer = $("#payprocessing").clone().wrapAll("<div/>").parent().html();
	divLayer = divLayer + '<iframe name="tar_opener" frameborder="0" border="0" width="350" height="100%" scrolling="auto" style="margin:0px;auto;"></iframe>';
	$("#layer_pay").html(divLayer);
	$("#layer_pay").css("display","block");
}

function inicis_mobile_popup(){
	var xpos = 100;
	var ypos = 100;
	var position = "top=" + ypos + ",left=" + xpos;
	var features = position + ", width=320, height=440";
	var wallet = window.open("", "tar_opener", features);
	//var wallet = window.open("", "BTPG_WALLET", features);
	wallet.focus();
}

function mobile_popup(){
	var xpos = 100;
	var ypos = 100;
	var position = "top=" + ypos + ",left=" + xpos;
	var features = position + ", width=320, height=440";
	var wallet = window.open("", "tar_opener", features);
	wallet.focus();
}

function use_cash(){
	if($("input[name='cash_view']").val() < 1){
		openDialogAlert('이머니를 정확히 입력해 주세요.','400','140');
		return false;
	}

	if($("input[name='cash_view']").val() > 0){
		$("input[name='cash']").val( $("input[name='cash_view']").val() );
		$(".cash_input_button").hide();
		$(".cash_all_input_button").hide();
		$(".cash_cancel_button").show();
	}
	order_price_calculate();
}

function use_all_cash(){
	$("input[name='cash_all']").val('y');
	$("input[name='cash']").val(0);
	$(".cash_input_button").hide();
	$(".cash_all_input_button").hide();
	$(".cash_cancel_button").show();

	order_price_calculate();
}

function cancel_cash(){
	$("input[name='cash']").val(0);
	$("input[name='cash_view']").val(0);
	$("input[name='cash_all']").val('');
	$(".cash_cancel_button").hide();
	$(".cash_input_button").show();
	$(".cash_all_input_button").show();
	$("#priceCashTd").hide();
	order_price_calculate();
}

function use_emoney(){
	if($("input[name='emoney_view']").val() < 1 ){
		openDialogAlert('적립금을 정확히 입력해 주세요.','400','140');
		return false;
	}

	if($("input[name='emoney_view']").val() > 0){
		$("input[name='emoney']").val( $("input[name='emoney_view']").val() );
		$(".emoney_input_button").hide();
		$(".emoney_all_input_button").hide();
		$(".emoney_cancel_button").show();
	}

	// 적립금액 제한 조건 알림 추가 leewh 2014-07-01
	if ($("#default_reserve_limit").length) {
		if ($("#default_reserve_limit").val()==1) {
			alert("적립금 사용으로 적립금을 지급하지 않습니다.");
		} else if ($("#default_reserve_limit").val()==2) {
			alert("기대적립금에서 사용한 적립금을 제외하고 적립금을 지급합니다.");
		} else if ($("#default_reserve_limit").val()==3) {
			alert("사용한 적립금을 제외하고 결제금액을 기준으로 적립금을 지급합니다.");
		}
	}
	order_price_calculate();
}

function use_all_emoney(){
	$("input[name='emoney_all']").val('y');
	$("input[name='emoney']").val(0);
	$(".emoney_input_button").hide();
	$(".emoney_all_input_button").hide();
	$(".emoney_cancel_button").show();

	order_price_calculate();
}

function cancel_emoney(){
	$("input[name='emoney']").val(0);
	$("input[name='emoney_view']").val(0);
	$("input[name='emoney_all']").val('');
	$(".emoney_cancel_button").hide();
	$(".emoney_input_button").show();
	$(".emoney_all_input_button").show();
	$("#priceEmoneyTd").hide();
	order_price_calculate();
}

function limit_chk(gift_seq, obj){

	var f = eval("document.orderFrm.gift_"+gift_seq)
	if(!f){
		f = $("input[name='gift_"+gift_seq+"[]']");
	}
	var cnt = 0;
	var f2 = eval("document.orderFrm.gift_"+gift_seq+"_limit");
	var limitCnt = f2.value;

	for(i=0; i<f.length; i++){
		if(f[i].checked == true){
			cnt++;
		}
	}

	if(cnt > limitCnt){
		alert("사은품을 최대 "+limitCnt+"개까지 선택하실 수 있습니다.");
		obj.checked = false;
	}

}

function multi_zipcode_popup(obj){
	var idx = $(obj).closest('.multiShippingItem').attr('multiShippingItemNo');
	window.open('../popup/zipcode?popup=1&mtype=order_multi&multiIdx='+idx+'&zipcode=multi_recipient_address[]&address=multi_recipient_address&address_street=multi_recipient_address_street&address_detail=multi_recipient_address_detail','popup_zipcode','width=600,height=480');
}

function add_delivery_address(obj){
	var container = $(obj).closest('.multiShippingItem');
	var params = {
		'recipient_user_name' : $(".multi_recipient_user_name",container).val(),
		'recipient_zipcode[0]' : $(".multi_recipient_zipcode:eq(0)",container).val(),
		'recipient_zipcode[1]' : $(".multi_recipient_zipcode:eq(1)",container).val(),
		'recipient_address' : $(".multi_recipient_address",container).val(),
		'recipient_address_street' : $(".multi_recipient_address_street",container).val(),
		'recipient_address_detail' : $(".multi_recipient_address_detail",container).val(),
		'recipient_phone[0]' : $(".multi_recipient_phone:eq(0)",container).val(),
		'recipient_phone[1]' : $(".multi_recipient_phone:eq(1)",container).val(),
		'recipient_phone[2]' : $(".multi_recipient_phone:eq(2)",container).val(),
		'recipient_cellphone[0]' : $(".multi_recipient_cellphone:eq(0)",container).val(),
		'recipient_cellphone[1]' : $(".multi_recipient_cellphone:eq(1)",container).val(),
		'recipient_cellphone[2]' : $(".multi_recipient_cellphone:eq(2)",container).val()
	};
	$.ajax({
		'url' : '../mypage_process/add_delivery_address',
		'data' : params,
		'type' : 'post',
		'dataType' : 'json',
		'success' : function(result){
			openDialogAlert(result.msg,400,140);
		}
	});
}

var exception_sale	= 0;
function exception_saleprice(sale){
	if	(exception_sale != sale){
		exception_sale	= sale;
		openDialogAlert("할인금액이 상품금액을 초과하여 일부할인이 제외되었습니다.", 500, 150);
	}
}

