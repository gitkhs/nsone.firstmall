<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/order/catalog.html 000068587 */ 
$TPL_sitetypeloop_1=empty($TPL_VAR["sitetypeloop"])||!is_array($TPL_VAR["sitetypeloop"])?0:count($TPL_VAR["sitetypeloop"]);
$TPL_referer_list_1=empty($TPL_VAR["referer_list"])||!is_array($TPL_VAR["referer_list"])?0:count($TPL_VAR["referer_list"]);
$TPL_linkage_mallnames_for_search_1=empty($TPL_VAR["linkage_mallnames_for_search"])||!is_array($TPL_VAR["linkage_mallnames_for_search"])?0:count($TPL_VAR["linkage_mallnames_for_search"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<div id="orderAdminSettle" class="hide"></div>
<div id="issueGoodsSelect" class="hide"></div>
<div id="optional_changes_dialog" class="hide"></div>
<style>
.search_label 	{display:inline-block;width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;-o-text-overflow:ellipsis;vertical-align:middle}
span.step_title { font-weight:normal;padding:0 5px 0 5px; }
span.export-list { display:inline-block;background-url("/admin/skin/default/images/common/btn_list_release.gif");width:60px;height:15px; }
div.btn-open-all{ position:absolute;top:3px;left:-62px;}
div.btn-open-all img { cursor:pointer; }
.ft11	{ font-size:11px; }

.barcode-btn {position:absolute; top:-34px; left:10px; cursor:pointer}
.barcode-btn .openImg{display:block;}
.barcode-btn .closeImg{display:none;}
.barcode-btn.opened .openImg{display:none;}
.barcode-btn.opened .closeImg{display:block;}
.barcode-description {display:none; background-color:#d2d8d8; border-top:1px solid #c4cccc; border-bottom:1px solid #c4cccc; text-align:center}
#snsdetailPopup { z-index:1; }
</style>

<script type="text/javascript">

/* variable for ajax list */
var npage		= 1;
var nstep		= '';
var nnum		= '';
var stepArr		= new Array();
var allOpenStep	= new Array();

$(document).ready(function() {

	$(".all-check").toggle(function(){
		$(this).parent().find('input[type=checkbox]').attr('checked',true);
	},function(){
		$(this).parent().find('input[type=checkbox]').attr('checked',false);
	});

	// 기본검색 조건 불러오기
	$("button#get_default_button").live("click",function(){
		$.getJSON('get_search_default', function(result) {
			var patt;
			$(".mod_chkbox").removeAttr("checked");
			for(var i=0;i<result.length;i++){
				patt=/_date/g;
				if( patt.test(result[i][0]) ){
					if(result[i][1] == 'today'){
						set_date('<?php echo date('Y-m-d')?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '3day'){
						set_date('<?php echo date('Y-m-d',strtotime("-3 day"))?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '7day'){
						set_date('<?php echo date('Y-m-d',strtotime("-7 day"))?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '1mon'){
						set_date('<?php echo date('Y-m-d',strtotime("-1 month"))?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '3mon'){
						set_date('<?php echo date('Y-m-d',strtotime("-3 month"))?>','<?php echo date('Y-m-d')?>');
					}
				}
				//patt=/chk_/;
				if(result[i][0]){
					$("form[name='search-form'] input[name='"+result[i][0]+"']").attr("checked",true);
				}
			}
		});
	});
	// 기본검색 조건 저장하기
	$("span#set_default_button").live("click",function(){
		var title = '기본검색 설정<span style="font-size:11px; margin-left:26px;"> - 아래서 원하는 검색조건을 설정하여 편하게 쇼핑몰을 운영하세요</span>';
		openDialog(title, "search_detail_dialog", {"width":"85%","height":"240"});
	});

	$("span.list-important").live("click", function(){
		var param = "?no="+$(this).attr('id');
		if( $(this).hasClass('checked') ){
			$(this).removeClass('checked');
			param += "&val=0";
			$.get('important'+param,function(data) {});

		}else{
			$(this).addClass('checked');
			param += "&val=1";
			$.get('important'+param,function(data) {});
		}
	});

	$("select.list-select").live("change",function(){
		var nm = $(this).attr("name");
		var value_str = $(this).val();
		var that = this;
		var step = nm.replace('select_', "");

		if(value_str=='select'){
			while(step==nstep){
				get_catalog_ajax();
			}
		}

		$("select[name='"+nm+"']").not(this).each(function(idx){
			$(this).find("option[value='"+value_str+"']").attr("selected",true);
			this.selectedIndex = that.selectedIndex;
			$(this).customSelectBox("selectIndex",that.selectedIndex);
		});

		var obj = $(".important-"+step);
		stepArr[step]	= value_str;

		$(".step"+step).removeClass('checked-tr-background');
		if(  value_str == 'select' )
			$(".step"+step).addClass('checked-tr-background');


		obj.each(function(){
			if( value_str ){
				$(this).parent().parent().find("td").eq(0).find("input").attr("checked",false);
				if(  value_str == 'important' && $(this).hasClass('checked') ){
					$(this).parent().parent().find("td").eq(0).find("input").attr("checked",true);
					$(this).parent().parent().parent().find("."+$(this).attr('id')).addClass('checked-tr-background');
				}else if( value_str == 'not-important' && !$(this).hasClass('checked') ){
					$(this).parent().parent().find("td").eq(0).find("input").attr("checked",true);
					$(this).parent().parent().parent().find("."+$(this).attr('id')).addClass('checked-tr-background');
				}else if(  value_str == 'select' ){
					$(this).parent().parent().find("td").eq(0).find("input").attr("checked",true);
				}
			}
		});
	});

	// 결제확인 시
	$("button[name='order_deposit']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});
		if(order_seq.length > 0){
			openDialogConfirm('선택된 주문을 결제확인 하시겠습니까?',400,140,function(){
				var str = order_seq.join('&');
				$.ajax({
					type: "POST",
					url: "../order_process/batch_deposit",
					data: str,
					success: function(result){
						var msg	= "<div><b>결제가 확인되었습니다.</b></div><br/>";
						if			(result == 'all'){
							msg	= msg + "<div style=\"text-align:left;\">▶ 실물상품 : 출고처리하여 상품을 발송하세요.<br/>▶ 쿠폰상품 : 쿠폰번호가 발송되었습니다.</div><br/>";
						}else if	(result == 'coupon'){
							msg	= msg + "<div style=\"text-align:left;\">▶ 쿠폰상품 : 쿠폰번호가 발송되었습니다.</div><br/>";
						}else{
							msg	= msg + "<div style=\"text-align:left;\">▶ 실물상품 : 출고처리하여 상품을 발송하세요.</div><br/>";
						}

						openDialogAlert(msg,500,200,function(){location.reload();});
					}
				});
			},function(){
			});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 주문무효 시
	$("button[name='cancel_order']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});
		if(order_seq.length > 0){
			openDialogConfirm('선택된 주문을 주문무효처리 하시겠습니까?',400,140,function(){
				var str = order_seq.join('&');
				$.ajax({
					type: "POST",
					url: "../order_process/batch_cancel_order",
					data: str,
					success: function(result){
						location.reload();
					}
				});
			},function(){
			});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 삭제처리
	$("button[name='goods_temps']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});
		if(order_seq.length > 0){
			openDialogConfirm('선택된 주문을 삭제처리 하시겠습니까?',400,140,function(){
				var str = order_seq.join('&');
				$.ajax({
					type: "POST",
					url: "../order_process/batch_temps_order",
					data: str,
					success: function(result){
						location.reload();
					}
				});
			},function(){
			});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 일괄출고처리
	$("button[name='goods_export']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});

		if(order_seq.length > 0){
			var str = order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../order/batch_export",
				data: str,
				success: function(result){
					$("#goods_export_dialog").html(result);
				}
			});
			openDialog("주문별 일괄 출고 처리<span class='desc'> - "+order_seq.length+"건</span>", "goods_export_dialog", {"width":"95%","height":"700"});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 일괄출고처리
	$("button[name='order_export']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});

		if(order_seq.length > 0){
			var str = order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../order/order_export",
				data: str,
				success: function(result){
					$("#goods_export_dialog").html(result);
				}
			});
			openDialog("상품별 일괄 출고 처리<span class='desc'> - "+order_seq.length+"건</span>", "goods_export_dialog", {"width":"95%","height":"700"});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 일괄 상태 되돌리기
	$("span.batch_reverse").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});
		if(order_seq.length > 0){
			var str = order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../order_process/batch_reverse",
				data: str,
				success: function(result){
					openDialogAlert(result,400,140,function(){
						document.location.reload();
					});
				}
			});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 일괄 상품준비
	$("button[name='batch_goods_ready']").live("click",function(){

		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});
		if(order_seq.length > 0){
			var str = order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../order_process/batch_goods_ready",
				data: str,
				success: function(result){
					openDialogAlert(result,600,140,function(){
						document.location.reload();
					});
				}
			});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 개별 출고처리
	$("button.goods_export").live("click",function(){
		var order_seq = $(this).attr('id').replace("goods_export_","");
		var url = "goods_export?seq="+order_seq;
		$.get(url, function(data) {
			$('#goods_export_dialog').html(data);
		});
		openDialog("출고처리<span class='desc'> - "+order_seq+"</span>", "goods_export_dialog", {"width":"95%","height":500});
	});

	// 개별 결제확인
	$("button.order_deposit").live("click",function(){
		var order_seq = $(this).attr('id').replace("order_deposit_","");
		actionFrame.location.href = '../order_process/deposit?seq='+order_seq;
	});

	// 개별 상품준비
	$("button.goods_ready").live("click",function(){
		var order_seq = $(this).attr('id').replace("goods_ready_","");
		var url = "../order_process/goods_ready?seq="+order_seq;
		$.get(url, function(result) {
			openDialogAlert(result,400,140,function(){
				document.location.reload();
			});
		});
	});

	// 일괄출고완료
	$("button[name='complete_export']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});

		if(order_seq.length > 0){
			var str = 'mode=complete_export&' + order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../export/batch_status",
				data: str,
				success: function(result){
					$("#goods_export_dialog").html(result);
				}
			});
			openDialog("일괄 출고 완료<span class='desc'> - " + order_seq.length + "건</span>", "goods_export_dialog", {"width":"1000","height":"700"});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 일괄 배송중처리
	$("button[name='going_delivery']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});

		if(order_seq.length > 0){
			var str = 'mode=going_delivery&' + order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../export/batch_status",
				data: str,
				success: function(result){
					$("#goods_export_dialog").html(result);
				}
			});
			openDialog("일괄 배송중 처리<span class='desc'> - " + order_seq.length + "건</span>", "goods_export_dialog", {"width":"1000","height":"700"});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 일괄 배송완료처리
	$("button[name='complete_delivery']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq[idx] = 'seq[]='+$(this).val();
		});

		if(order_seq.length > 0){
			var str = 'mode=complete_delivery&' + order_seq.join('&');
			$.ajax({
				type: "POST",
				url: "../export/batch_status",
				data: str,
				success: function(result){
					$("#goods_export_dialog").html(result);
				}
			});
			openDialog("일괄 배송 완료<span class='desc'> - " + order_seq.length + "건</span>", "goods_export_dialog", {"width":"1000","height":"700"});
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	// 체크박스 색상
	$("input[type='checkbox'][name='order_seq[]']").live('change',function(){
		if($(this).is(':checked')){
			$(this).closest('tr').addClass('checked-tr-background');
		}else{
			$(this).closest('tr').removeClass('checked-tr-background');
		}
	});



	//
	$("img[name='goods_print']").live("click",function(){
		var step = $(this).attr('id');
		var order_seq = new Array();
		var text = "";
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			//order_seq[idx] = 'seq[]='+$(this).val();
			text += $(this).val()+"|";
		});
		if(text){
			printOrderView(text);
		}else{
			alert("선택값이 없습니다.");
			return;
		}
	});

	$(".order_reverse").live("click",function(){
		var order_seq = $(this).attr('id').replace("order_reverse_","");
		actionFrame.location.href = '../order_process/order_reverse?seq='+order_seq;
	});


	$("button[name='download_list']").live("click", function(){
		//window.open("/admin/order/download_list","","");
		location.href = "/admin/order/download_list";
	});

	$("input[name='not_linkage_order']").live("change", function(){
		if($(this).is(":checked")){
			$("select[name='referer']").removeAttr('disabled');
		}else{
			$("select[name='referer']").attr('disabled',true);
		}
	}).change();

	$("button[name='openmarket_order_receive']").live("click", function(){
<?php if(count($TPL_VAR["linkage_mallnames_for_search"])> 1){?>
		openDialog("주문수집","openmarket_order_receive_dialog",{'width':300});
<?php }else{?>
		openmarket_order_receive_submit('<?php echo $TPL_VAR["linkage_mallnames_for_search"][ 0]["mall_code"]?>');
<?php }?>
	});

	$("button[name='openmarket_order_receive_guide']").live("click", function(){
		openDialog("외부 판매마켓 주문 자동수집 안내<span class='desc'></span>", "openmarket_order_receive_guide", {"width":"400","height":"200","show" : "fade","hide" : "fade"});
	});
	$("button[name='openmarket_service_guide']").live("click", function(){
		openDialog("다중 판매마켓 서비스 안내<span class='desc'></span>", "openmarket_service_guide", {"width":"400","height":"200","show" : "fade","hide" : "fade"});
	});

	$("button[name='excel_down']").live("click", function(){
		var step = $(this).attr("step");
		var order_seq = "";
		$("tr.step"+step).find("input[type='checkbox'][name='order_seq[]']:checked").each(function(idx){
			order_seq += $(this).val() + "|";
		});
		if(!order_seq){
			alert("선택값이 없습니다.");
			return;
		}

		if(!$("#select_down_"+step).val()){
			alert("양식을 선택해 주세요.");
			return;
		}
		//actionFrame.location.href="/admin/order_process/excel_down?order_seq="+order_seq+"&seq="+$("#select_down_"+step).val();
		ajaxexceldown('/admin/order_process/excel_down', order_seq, $("#select_down_"+step).val(), step);
	});

	$("button[name='excel_down_all']").live("click", function(){
		var step = $(this).attr("step");
		if(!$("#select_down_"+step).val()){
			alert("양식을 선택해 주세요.");
			return;
		}

		var params			= '<?php echo base64_encode(serialize($_GET))?>';
		ajaxexceldown('/admin/order_process/excel_down_all', params, $("#select_down_"+step).val(), step);
	});


	// export_upload
	$("button[name='excel_upload']").live("click",function(){
		openDialog("출고수량/택배사코드/송장번호 - 엑셀 일괄 업로드 <span class='desc'></span>", "export_upload", {"width":"600","height":"550","show" : "fade","hide" : "fade"});
	});


	$("button[name='order_admin_settle']").live("click",function(){
		order_admin_settle("orderAdminSettle","issueGoods", 'admin');
	});


	$("button[name='order_admin_person']").live("click",function(){
		order_admin_person("orderAdminSettle","issueGoods", 'person');
	});


	$("button#issueGoodsButton").live("click",function(){
		set_goods_list("issueGoodsSelect","issueGoods");
	});


	$("button[name='order_admin_option']").live("click",function(){
		order_admin_option();
	});

	// 결제금액 계산
	$("button#coupon_order").live("click",function(){
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
		closeDialog("coupon_apply_dialog");
	});

	// 매입처/입점사 검색선택
	$("input[name='provider_base']").live('change',function(){
		if($(this).is(":checked")){
			$("input[name='provider_seq']").attr('disabled',true);
			$("input[name='provider_name']").attr('disabled',true);
		}else{
			$("input[name='provider_seq']").removeAttr('disabled');
			$("input[name='provider_name']").removeAttr('disabled');
		}
	}).change();

	$(".order_type_help").live("click", function() {
		openDialog("주문유형 안내", "order_type_help", {"width":1000,"height":400});
	});

	$("input[name='recipient_zipcode[]'], input[name='recipient_address'], input[name='emoney']").live("blur",function(){
		order_price_calculate();
	});

	$(".btn_open_all").live("click",function(){
		var step	= $(this).attr("id");
		$("tr.step"+step).find("span.btn-direct-open").each(function(){
			orderViewOnOff('open', $(this));
		});
		var src	= $(this).attr('src');
		$(this).attr('src', src.replace('_open_', '_close_'));
		$(this).attr("class", "btn_close_all");

		allOpenStep[step]	= 'open';
	});

	$(".btn_close_all").live("click",function(){
		var step	= $(this).attr("id");
		$("tr.step"+step).find("span.btn-direct-open").each(function(){
			orderViewOnOff('close', $(this));
		});
		var src	= $(this).attr('src');
		$(this).attr('src', src.replace('_close_', '_open_'));
		$(this).attr("class", "btn_open_all");

		allOpenStep[step]	= 'close';
	});

	$(".btn-direct-open").live("click", function(){
		var nClass		= $(this).attr("class");
		if	(nClass.search(/opened/) == -1)	orderViewOnOff('open', $(this));
		else								orderViewOnOff('close', $(this));
	});

<?php if($TPL_VAR["old_list"]!='y'){?>
	$(document).find("body").css("overflow", "scroll");
	$(window).scroll(function(){
		if	($(window).scrollTop() == ($(document).height() - $(window).height())){
			get_catalog_ajax();
		}
	});

	get_catalog_ajax();
<?php }?>

	$("span#invoice_manual_button").live("click",function(){
		var title = '택배 업무 자동화 서비스 사용방법';
		openDialog(title, "invoice_manual_dialog", {"width":"700"});
	});

	// 바코드스캔출고처리순서 안내버튼
	$(".barcode-btn").click(function(){
		if($(this).hasClass('opened')){
			$(this).removeClass('opened');
			$(".barcode-description").stop(true,true).slideUp();
		}else{
			$(this).addClass('opened');
			$(".barcode-description").stop(true,true).slideDown();
		}
	});

	$("form[name='search-form']").submit(function(){
		var submit = true;

		// 바코드 검색 체크
		var keyword = $("input[name='keyword']",this).val();
		if(keyword.length==21 && keyword.substring(0,1)=='A' && keyword.substring(keyword.length-1,keyword.length)=='A'){
			var order_seq = keyword.substring(1,20);
			$.ajax({
				'url' : 'order_seq_chk',
				'data' : {'order_seq':keyword},
				'async' : false,
				'success' : function(res){
					if(res=='1'){
						window.open('/admin/order/view?no='+order_seq+'&directExport=1');
						$("form[name='search-form'] input[name='keyword']").val('');
						submit = false;
					}
				}
			});
		}

		return submit;
	});

	$("button[name='print_setting']").click(function(){
		openDialog("주문내역서 프린트 설정", "print_setting_dialog", {"width":"700"});
	});

});


// sns 계정 정보 확인
function snsdetailview(m,snscd,mem_seq,no){

	var disp = $("div#snsdetailPopup"+no).css("display");
	$(".snsdetailPopup").hide();

	var obj	= $("div#snsdetailPopup"+no);
	//$("div.snsdetailPopup").hide();
	if(obj.html() == ''){
		$.get('../member/sns_detail?snscd='+snscd+'&member_seq='+mem_seq+'&no='+no, function(data) {
			obj.html(data);
		});
	}

	if(disp == "none"){ obj.show(); }
}


var loading_status	= 'n';
function get_catalog_ajax(){
	if	(loading_status == 'n'){
		loading_status	= 'y';
		var queryString			= '<?php echo $_SERVER["QUERY_STRING"]?>';
		var stepArrCnt			= stepArr.length;
		var addParam			= '';
		for (var s = 0; s < stepArrCnt; s++ ){
			if	(stepArr[s]){
				addParam	+= '&stepBox%5B'+s+'%5D='+stepArr[s];
			}
		}

		$("#ajaxLoadingLayer").ajaxStart(function() { loadingStop(this); });
		$.ajax({
			type: 'post',
			async:false,
			url: 'catalog_ajax',
			data: queryString +'&page='+npage+'&bfStep='+nstep+'&nnum='+nnum+addParam,
			dataType: 'html',
			success: function(result) {
				$(".order-ajax-list").append(result);
				$(".custom-select-box").customSelectBox();
				$(".custom-select-box-multi").customSelectBox({'multi':true});

				if			(allOpenStep[nstep] == 'open'){
					$("tr.step"+nstep).find("span.btn-direct-open").each(function(){
						orderViewOnOff('open', $(this));
					});
				}else if	(allOpenStep[nstep] == 'close'){
					$("tr.step"+nstep).find("span.btn-direct-open").each(function(){
						orderViewOnOff('close', $(this));
					});
				}

				nstep	= $("#"+npage+"_step").val();
				nnum	= $("#"+npage+"_no").val();
				npage++;

				loading_status	= 'n';


				$(".help, .helpicon").poshytip({
					className: 'tip-darkgray',
					bgImageFrameSize: 8,
					alignTo: 'target',
					alignX: 'right',
					alignY: 'center',
					offsetX: 10,
					allowTipHover: false,
					slide: false,
					showTimeout : 0
				});


			}
		});
		$("#ajaxLoadingLayer").ajaxStart(function() { loadingStart(this); });
	}
}

function orderViewOnOff(openType, thisObj){
	var nextTr		= $(thisObj).parent().parent().next();
	var nClass		= $(thisObj).attr('class');
	if	(openType == 'open'){
		if	(nClass.search(/opened/) == -1){
			var order_seq	= $(thisObj).parent().parent().find("input[type='checkbox']").val();
			$.get('view?no='+order_seq+'&mode=order_list', function(data) {
				nextTr.find('div.order_info').html(data);
			});
			nextTr.removeClass('hide');
			$(thisObj).addClass("opened");
		}
	}else{
		if	(nClass.search(/opened/) != -1){
			nextTr.find('div.order_info').html('');
			nextTr.addClass('hide');
			$(thisObj).removeClass("opened");
		}
	}
}

function ajaxexceldown(url, order_seq, seq, step){
        var inputs ='<input type="hidden" name="order_seq" value="'+ order_seq +'" />';
		inputs +='<input type="hidden" name="seq" value="'+ seq +'" />';
		inputs +='<input type="hidden" name="step" value="'+ step +'" />';
        jQuery('<form action="'+ url +'" method="post" target="actionFrame" >'+inputs+'</form>')
        .appendTo('body').submit().remove();
}


function order_type_help(){
	openDialog("주문유형 안내", "order_type_help", {"width":1000,"height":400});
}

	function cashBilltype(str){

		if(str == 0){
			//현금영수증 개인공제용
			$("#personallay").show();
			$("#businesslay").hide();
		}else{
			//현금영수증 사업자지출증빙용
			$("#personallay").hide();
			$("#businesslay").show();
		}
	}


	// 영수증 발급을 클릭했을경우
	function taxBill(str){
		// 발급안함
		if(str == 0) {
			$('#cash_container').hide();
			$('#tax_container').hide();
			taxRemoveClass();
			cashRemoveClass();
		}
		// 세금계산서 신청일 경우
		else if(str == 1) {
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
		else if(str == 2) {
			$('#cash_container').show();
			$('#tax_container').hide();
			$('#creceipt_number').attr('title', ' ').addClass('required').addClass('numberHyphen');
			taxRemoveClass();
		}
	}

	/**
	 * 세금계산서 폼체크를 삭제한다.
	 */
	function taxRemoveClass() {
		$('#co_name').removeClass('required').val('');
		$('#co_ceo').removeClass('required').val('');
		$('#busi_no').removeClass('required').val('');
		$('#co_zipcode').removeClass('required').val('');
		$('#co_address').removeClass('required').val('');
		$('#co_status').removeClass('required').val('');
		$('#co_type').removeClass('required').val('');
	}

	/**
	 * 현금영수증 폼체크를 삭제한다.
	 */
	function cashRemoveClass() {
		$('#creceipt_number').removeClass('required').val('');
	}

function getCouponselect(e){

	var obj = $(e);
	$("select.coupon_select").each(function(){
		if( obj.find("option:selected").attr('duplication') == 0 ){
			if( obj.attr('id') != $(this).attr("id") && obj.find("option:selected").attr("value") ){
				if(obj.find("option:selected").val() == $(this).find("option:selected").val()){
					$(this).find("option").eq(0).attr("selected",true);
				}
			}
			$(this).parent().next().find("span.sale").html( comma( $(this).find("option:selected").attr("sale") ) );
		}else{
			$(this).parent().next().find("span.sale").html( comma( $(this).find("option:selected").attr("sale") ) );
		}
	});
}

function coupon_apply_click(){
	getCouponAjaxList();
	openDialog("쿠폰 적용 하기", "coupon_apply_dialog", {"width":800,"height":600});
}


/**
 * 쿠폰을 ajax로 검색한다.
 */
function getCouponAjaxList() {
	var f = $("form#orderFrm");
	//alert(f.order_address.value);
	//var queryString = f.formSerialize();
	var queryString;
	$.ajax({
		type: 'post',
		url: '/order/settle_coupon?mode=direct',
		data: queryString,
		dataType: 'json',
		success: function(data) {
			if(data.coupongoods){
				$('#coupon_goods_lay').html(data.coupongoods);
			}
			if(data.couponshipping){
				$('#coupon_shipping_lay').show();
				$('#coupon_shipping_select').html(data.couponshipping);
			}else{
				$('#coupon_shipping_lay').hide();
			}
		}
	});
}

// 쿠폰사용
function getCouponuse(seqno){
	$.getJSON('/order/coupon/goods_coupon_max?no='+seqno, function(data) {
		if(data){
			$(".couponlay_"+seqno).show();
			$("#couponDownloadlay_"+seqno).show();
			if(data.sale_type == 'won'){
				$("#cb_percent_"+seqno).html( comma(data.won_goods_sale) + '원' );
			}else{
				$("#cb_percent_"+seqno).html( comma(data.percent_goods_sale) + '%' );
			}
		}else{
			$(".couponlay_"+seqno).hide();
			$("#couponDownloadlay_"+seqno).hide();
		}
	});
}


function getPromotionck(){
	var cartpromotioncode = $("#cartpromotioncode").val();
	if(!cartpromotioncode){
		alert('프로모션코드를 정확히 입력해 주세요.');
		return false;
	}

	$.ajax({
		'url' : '/order/promotion/getPromotionJson',
		'data' : {'method':'settle','cartpromotioncode':cartpromotioncode},
		'type' : 'post',
		'dataType': 'json',
		'success': function(data) {
			if(data.result){order_price_calculate();
			}else{
				alert(data.msg);
			}
		}
	});
}

function cart_delete(seq){
	actionFrame.location.href = "./cart_del?cart_seq="+seq;
}

function person_cart_delete(seq){
	actionFrame.location.href = "./person_cart_del?cart_seq="+seq;
}


function order_price_calculate(){
	var f = $("form#orderFrm");
	f.attr("action","/order/calculate?mode=<?php echo $TPL_VAR["mode"]?>&adminOrder=admin");
	f.attr("target","actionFrame");
	f[0].submit();
}

//개인결제용 계산
function order_person_price_calculate(){

	var member_seq = $("input[name='member_seq']").val();
	var enuri = $("input[name='enuri']").val();

	var f = $("form#orderFrm");
	f.attr("action","/admin/order/calculate?member_seq="+member_seq+"&enuri="+enuri);
	f.attr("target","actionFrame");
	f[0].submit();
}


function option_modify(id, cart_table){

		var url = "optional_changes?no="+id+"&cart_table="+cart_table;

		$.get(url, function(data) {
			$("div#optional_changes_dialog").html(data);
		});
		openDialog("선택사항변경/추가", "optional_changes_dialog", {"width":500,"height":600});

}

function option_ea_modify(id,cart_table){
	var param = "?seq="+id+"&cart_table="+cart_table;;
	$("form#orderFrm").attr("action","modify"+param);
	$("form#orderFrm").attr("target","actionFrame");
	$("form#orderFrm")[0].submit();

}

function order_admin_option(){

	$.ajax({
		type: "get",
		url: "../order/order_admin_option",
		data: "page=1&inputGoods="+inputGoods+"&displayId="+displayId,
		success: function(result){
			$("div#"+displayId).html(result);
		}
	});
	openDialog("관리자 수동 주문", displayId, {"width":"1000","height":"700","show" : "fade","hide" : "fade"});

}

function cart(cart_table){

	var member_seq = $("input[name='member_seq']").val();

	$.ajax({
		type: "get",
		url: "../order/cart",
		data: "cart_table="+cart_table+"&member_seq="+member_seq,
		success: function(result){
			$(".cart_list").html(result);
		}
	});
	if(cart_table == "person"){
		order_person_price_calculate();
	}else{
		order_price_calculate();
	}
	setTimeout("settle_price_input()",1000);

}

function settle_price_input(){
	$("#total_settle_price").html($("input[name='total_price_temp']").val());
}



function set_ship_info(){

	// 배송지 정보 채우기 t
	if( $("#copy_order_info").attr("checked") ){
		$("input[name='order_zipcode[]']").each(function(idx){
			$("input[name='recipient_zipcode[]']").eq(idx).val( $("input[name='order_zipcode[]']").eq(idx).val() );
		});

		$("input[name='recipient_address_type']").val( $("input[name='order_address_type']").val() );
		$("input[name='recipient_address']").val( $("input[name='order_address']").val() );
		$("input[name='recipient_address_street']").val( $("input[name='order_address_street']").val() );
		$("input[name='recipient_address_detail']").val( $("input[name='order_address_detail']").val() );
		$("input[name='recipient_user_name']").val( $("input[name='order_user_name']").val() );

		$("input[name='order_phone[]']").each(function(idx){
			$("input[name='recipient_phone[]']").eq(idx).val( $("input[name='order_phone[]']").eq(idx).val() );
		});

		$("input[name='order_cellphone[]']").each(function(idx){
			$("input[name='recipient_cellphone[]']").eq(idx).val( $("input[name='order_cellphone[]']").eq(idx).val() );
		});

	}else{
		$("input[name='order_zipcode[]']").each(function(idx){
			$("input[name='recipient_zipcode[]']").eq(idx).val("");
		});

		$("input[name='recipient_address_type']").val("");
		$("input[name='recipient_address']").val("");
		$("input[name='recipient_address_street']").val("");
		$("input[name='recipient_address_detail']").val("");
		$("input[name='recipient_user_name']").val("");

		$("input[name='order_phone[]']").each(function(idx){
			$("input[name='recipient_phone[]']").eq(idx).val("");
		});

		$("input[name='order_cellphone[]']").each(function(idx){
			$("input[name='recipient_cellphone[]']").eq(idx).val("");
		});
	}
	order_price_calculate();
}



function set_goods_list(displayId,inputGoods,ordertype){

	var mem_seq = $("input[name='member_seq']").val();

	if(ordertype == "person"){
		if(mem_seq == ""){
			alert("회원을 선택하세요.");
			return;
		}
	}

	$("div#"+displayId).html("");
	$.ajax({
		type: "get",
		url: "../order/goods_select",
		data: "page=1&inputGoods="+inputGoods+"&displayId="+displayId+"&ordertype="+ordertype+"&member_seq="+mem_seq,
		success: function(result){
			$("div#"+displayId).html(result);
		}
	});
	openDialog("상품 검색", displayId, {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
}


function order_admin_settle(displayId,inputGoods){
	$.ajax({
		type: "get",
		url: "../order/order_settle_admin",
		data: "page=1&inputGoods="+inputGoods+"&displayId="+displayId,
		success: function(result){
			$("div#"+displayId).html(result);
		}
	});
	openDialog("관리자 수동 주문", displayId, {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
}


function order_admin_person(displayId,inputGoods,ordertype){
	$.ajax({
		type: "get",
		url: "../order/order_settle_person",
		data: "page=1&inputGoods="+inputGoods+"&displayId="+displayId+"&ordertype="+ordertype,
		success: function(result){
			$("div#"+displayId).html(result);
		}
	});
	openDialog("개인 결제 만들기", displayId, {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
}


function coupon_member_search(ordertype){
	addFormDialogSel('./download_member?ordertype='+ordertype, '85%', '750', '회원검색');
}

function addFormDialogSel(url, width, height, title, btn_yn) {
	newcreateElementContainer(title);
	newrefreshTable(url);

	$('#dlg').dialog({
		bgiframe: true,
		autoOpen: false,
		width: width,
		height: height,
		resizable: false,
		draggable: false,
		modal: true,
		overlay: {
			backgroundColor: '#000000',
			opacity: 0.8
		},
		buttons: {
			'닫기': function() {
				$(this).dialog('close');
			}
		}
	}).dialog('open');
	return false;
}

function option_close(){
	$('#optional_changes_dialog').dialog('close')
}

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


function set_date(start,end){
	$("input[name='regist_date[]']").eq(0).val(start);
	$("input[name='regist_date[]']").eq(1).val(end);
}

function adminPay(){

	var frm = document.orderFrm;

	frm.action = "/order/pay";
	frm.target = "actionFrame";
	frm.submit();

}

function personPay(){

	var frm = document.orderFrm;

	frm.action = "/admin/order/pay";
	frm.target = "actionFrame";
	frm.submit();

}

function openmarket_order_receive_submit(mall_code){
	//loadingStart();
	$("form[name='orderReceiveForm'] input[name='mall_code']").val(mall_code);
	$("form[name='orderReceiveForm']").submit();
}


function closeExportPopup(){
	openDialogAlert("처리할 주문이 없습니다.", 400, 150, function(){closeDialog("goods_export_dialog");});
}
</script>
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar">

		<!-- 좌측 버튼 -->
		<ul class="page-buttons-left">
			<li>
<?php if($TPL_VAR["config_system"]["invoice_use"]){?>
				<span id="invoice_manual_button" class="hand"><img src="/admin/skin/default/images/common/btn_dliv_auto_on.gif" align="absmiddle" vspace="5" /></span>
<?php }else{?>
				<a href="../setting/shipping"><img src="/admin/skin/default/images/common/btn_dliv_auto_off.gif" align="absmiddle" vspace="5" /></a>
<?php }?>
			</li>
		</ul>

		<!-- 타이틀 -->
		<div class="page-title">
			<a href="../setting/order" target="_blank"><img src="/admin/skin/default/images/common/btn_now_prcs.gif" align="absmiddle" style="margin-left:-115px;" /></a>
			<h2>주문리스트</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">

<?php if($TPL_VAR["service_code"]=='P_FREE'||$TPL_VAR["service_code"]=='P_STOR'){?>
			<li><span class="btn large gray"><button type="button" class="noshopfreelinknone" title="무료몰 Plus+ 또는 홈페이지샵 에서는 해당기능이 지원되지 않습니다.">개인결제 만들기<span class="arrowright"></span></button></span></li>
			<li><span class="btn large gray"><button type="button" class="noshopfreelinknone" title="무료몰 Plus+ 또는 홈페이지샵 에서는 해당기능이 지원되지 않습니다.">관리자가 주문 넣기<span class="arrowright"></span></button></span></li>
<?php }else{?>
			<li><span class="btn small orange"><button type="button" class="order_type_help hand">안내) 주문유형</button></span></li>
			<li><span class="btn large"><button name="order_admin_person">개인결제 만들기<span class="arrowright"></span></button></span></li>
			<li><span class="btn large"><button name="order_admin_settle">관리자가 주문 넣기<span class="arrowright"></span></button></span></li>
<?php }?>
			<li><span class="btn large"><button name="download_list">다운로드항목설정<span class="arrowright"></span></button></span></li>
			<li><span class="btn large"><button name="print_setting">프린트설정<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 주문리스트 검색폼 : 시작 -->
<div class="search-form-container">
	<form name="search-form" method="get">
	<table class="search-form-table">
	<tr>
		<td>
			<table>
			<tr>
				<td width="600">
					<table class="sf-keyword-table">
					<tr>
						<td width="100" align="center">
							<select name="keyword_type" style="width:94px;">
							<option value="">통합검색</option>
							<option value="order_seq">주문번호</option>
							<option value="order_user_name">주문자명</option>
							<option value="depositor">입금자명</option>
							<option value="userid">아이디</option>
							</select>
							<script>$("select[name='keyword_type']").val("<?php echo $_GET["keyword_type"]?>");</script>
						</td>
						<td class="sfk-td-txt"><input type="text" name="keyword" value="<?php echo $_GET["keyword"]?>" title="주문자, 수령자, 입금자, 아이디, 이메일, 휴대폰, 상품명, 상품 고유값, 상품코드, 사은품, 운송장번호, 주문번호, 출고번호, 반품번호, 환불번호" /></td>
						<td class="sfk-td-btn"><button type="submit"><span>검색</span></button></td>
					</tr>
					</table>
				</td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td>
				<span id="set_default_button" class="icon-arrow-down" style="cursor:pointer;">기본검색설정</span>
				<span class="btn small gray"><button type="button" id="get_default_button">적용 ▶</button></span>
				</td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<div class="relative">
		<div class="barcode-btn"><img src="/admin/skin/default/images/common/btn_bacode_open.gif" class="openImg" /><img src="/admin/skin/default/images/common/btn_bacode_close.gif" class="closeImg" /></div>
		<div class="barcode-description">
			<img src="/admin/skin/default/images/common/barcode_step_img.gif" />
		</div>
	</div>

	<table class="search-form-table" id="search_detail_table">
	<tr>
		<td>
			<table class="sf-option-table">
			<tr>
				<th>
					<select name="date_field">
					<option value="regist_date" <?php if($_GET["date_field"]=='regist_date'||!$_GET["date_field"]){?>selected<?php }?>>주문일</option>
					<option value="deposit_date" <?php if($_GET["date_field"]=='deposit_date'){?>selected<?php }?>>입금일</option>
					</select>
				</th>
				<td>
					<input type="text" name="regist_date[]" value="<?php echo $_GET["regist_date"][ 0]?>" class="datepicker line"  maxlength="10" size="10" />
					&nbsp;&nbsp;<span class="gray">-</span>&nbsp;&nbsp;
					<input type="text" name="regist_date[]" value="<?php echo $_GET["regist_date"][ 1]?>" class="datepicker line" maxlength="10" size="10" />
					&nbsp;&nbsp;
					<span class="btn small"><input type="button" value="오늘" onclick="set_date('<?php echo date('Y-m-d')?>','<?php echo date('Y-m-d')?>')" /></span>
					<span class="btn small"><input type="button" value="3일간" onclick="set_date('<?php echo date('Y-m-d',strtotime("-3 day"))?>','<?php echo date('Y-m-d')?>')" /></span>
					<span class="btn small"><input type="button" value="일주일" onclick="set_date('<?php echo date('Y-m-d',strtotime("-7 day"))?>','<?php echo date('Y-m-d')?>')"/></span>
					<span class="btn small"><input type="button" value="1개월" onclick="set_date('<?php echo date('Y-m-d',strtotime("-1 month"))?>','<?php echo date('Y-m-d')?>')"/></span>
					<span class="btn small"><input type="button" value="3개월" onclick="set_date('<?php echo date('Y-m-d',strtotime("-3 month"))?>','<?php echo date('Y-m-d')?>')" /></span>
					<span class="btn small"><input type="button" value="전체" onclick="set_date('','')" /></span>
				</td>
			</tr>

			<tr>
				<th>출고 전</th>
				<td>
<?php if(is_array($TPL_R1=config_load('step'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if($TPL_K1< 50||$TPL_K1> 80){?>
<?php if($_GET["chk_step"][$TPL_K1]){?>
					<label class="search_label"><input type="checkbox" class="mod_chkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" checked="checked" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }else{?>
					<label class="search_label"><input type="checkbox" class="mod_chkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }?>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>출고 후</th>
				<td>
<?php if(is_array($TPL_R1=config_load('step'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if($TPL_K1>= 50&&$TPL_K1< 80){?>
<?php if($_GET["chk_step"][$TPL_K1]){?>
					<label class="search_label"><input type="checkbox" class="mod_chkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" checked="checked" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }else{?>
					<label class="search_label"><input type="checkbox" class="mod_chkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }?>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>결제수단</th>
				<td>
<?php if(is_array($TPL_R1=config_load('payment'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if(!preg_match('/escrow/',$TPL_K1)){?>
<?php if($_GET["payment"][$TPL_K1]){?>
					<label class="search_label"><input type="checkbox" class="mod_chkbox" name="payment[<?php echo $TPL_K1?>]" value="1" checked="checked" /> <?php echo $TPL_V1?></label>
<?php }else{?>
					<label class="search_label"><input type="checkbox" class="mod_chkbox" name="payment[<?php echo $TPL_K1?>]" value="1" /> <?php echo $TPL_V1?></label>
<?php }?>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>판매환경</th>
				<td colspan="3">
<?php if($TPL_sitetypeloop_1){foreach($TPL_VAR["sitetypeloop"] as $TPL_K1=>$TPL_V1){?>
<?php if($_GET["sitetype"][$TPL_K1]){?>
						<label class="search_label" <?php if($TPL_K1=='MF'){?>style="width:150px"<?php }?> ><input type="checkbox" class="mod_chkbox" name="sitetype[<?php echo $TPL_K1?>]" value="<?php echo $TPL_K1?>" checked="checked" /> <?php echo $TPL_V1["name"]?></label>
<?php }else{?>
						<label class="search_label"  <?php if($TPL_K1=='MF'){?>style="width:150px"<?php }?> ><input type="checkbox" class="mod_chkbox" name="sitetype[<?php echo $TPL_K1?>]" value="<?php echo $TPL_K1?>" /> <?php echo $TPL_V1["name"]?></label>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>주문유형</th>
				<td colspan="3">
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[personal]" value="personal" <?php if($_GET["ordertype"]['personal']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_personal.gif"  align="absmiddle" /> 개인결제</label>
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[admin]" value="admin" <?php if($_GET["ordertype"]['admin']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_admin.gif" align="absmiddle" /> 관리자</label>
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[change]" value="change" <?php if($_GET["ordertype"]['change']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_exchange.gif" align="absmiddle" /> 맞교환</label>
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[gift]" value="gift" <?php if($_GET["ordertype"]['gift']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_gift.gif" align="absmiddle" /> 사은품</label>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
<?php if($TPL_VAR["linkage_mallnames_for_search"]){?>
			<tr>
				<th>판매마켓</th>
				<td colspan="3">
					<label class="search_label" style="height:20px;"><input type="checkbox" name="not_linkage_order" value="1" <?php if($_GET["not_linkage_order"]){?>checked="checked"<?php }?> /> 운영쇼핑몰</label>
					<select name="referer">
						<option value="">선택하세요</option>
<?php if($TPL_referer_list_1){foreach($TPL_VAR["referer_list"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["referer_group_name"]?>" <?php if($_GET["referer"]==$TPL_V1["referer_group_name"]){?>selected<?php }?>><?php echo $TPL_V1["referer_group_name"]?></option>
<?php }}?>
						<option value="기타" <?php if($_GET["referer"]=='기타'){?>selected<?php }?>>기타</option>
					</select>
					<br />
<?php if($TPL_linkage_mallnames_for_search_1){$TPL_I1=-1;foreach($TPL_VAR["linkage_mallnames_for_search"] as $TPL_V1){$TPL_I1++;?>
<?php if($TPL_I1&&$TPL_I1% 5== 0){?><br /><?php }?>
<?php if($_GET["linkage_mall_code"][$TPL_V1["mall_code"]]){?>
						<label class="search_label"><input type="checkbox" name="linkage_mall_code[<?php echo $TPL_V1["mall_code"]?>]" value="<?php echo $TPL_V1["mall_code"]?>" checked="checked" /> <?php echo $TPL_V1["mall_name"]?></label>
<?php }else{?>
						<label class="search_label"><input type="checkbox" name="linkage_mall_code[<?php echo $TPL_V1["mall_code"]?>]" value="<?php echo $TPL_V1["mall_code"]?>" /> <?php echo $TPL_V1["mall_name"]?></label>
<?php }?>
<?php }}?>
					<label class="search_label" style="height:20px;"><input type="checkbox" name="etc_linkage_order" value="1" <?php if($_GET["etc_linkage_order"]){?>checked="checked"<?php }?> /> 그 외 마켓</label>
					<span class="icon-check hand all-check"><b>전체</b></span>

					<span class="btn medium"><button type="button" name="openmarket_order_receive">지금바로 주문수집<span class="arrowright"></span></button></span>
					<span class="btn medium"><button type="button" name="openmarket_order_receive_guide">자동수집 안내<span class="arrowright"></span></button></span>
				</td>
			</tr>
<?php }?>
			<tr>
				<th>주문경로<span class="helpicon" title="어디서 유입되어 주문 되었는지 알 수 있습니다."></span></th>
				<td>
					<select name="referer">
						<option value="">선택하세요</option>
<?php if($TPL_referer_list_1){foreach($TPL_VAR["referer_list"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["referer_group_name"]?>" <?php if($_GET["referer"]==$TPL_V1["referer_group_name"]){?>selected<?php }?>><?php echo $TPL_V1["referer_group_name"]?></option>
<?php }}?>
						<option value="기타" <?php if($_GET["referer"]=='기타'){?>selected<?php }?>>기타</option>
					</select>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	</form>
</div>
<!-- 주문리스트 검색폼 : 끝 -->

<!-- 주문리스트 테이블 : 시작 -->
<table class="list-table-style" cellspacing="0">
	<!-- 테이블 헤더 : 시작 -->
	<colgroup>
		<col width="30" />
		<col width="30" />
		<col width="30" />
		<col width="30" />
		<col width="80" />
		<col width="40" />
		<col width="250" />
		<col />
		<col width="45" />
		<col width="45" />
		<col width="140" />
		<col width="100" />
		<col width="80" />
		<col width="80" />
		<col width="80" />
	</colgroup>
	<thead class="lth">
	<tr>
		<th>선택</th>
		<th>중요</th>
		<th>번호</th>
		<th>유입</th>
		<th>주문일시</th>
		<th>환경</th>
		<th>주문번호</th>
		<th>주문상품</th>
		<th>수(종)</th>
		<th>출고 <span class="helpicon" title="해당 주문의 출고리스트를 확인합니다."></span></th>
		<th>받는분 / 주문자</th>
		<th>결제수단</th>
		<th>결제금액</th>
		<th>결제일시</th>
		<th>처리상태</th>
	</tr>
	</thead>
	<!-- 테이블 헤더 : 끝 -->
	<!-- 리스트 : 시작 -->
	<tbody class="ltb order-ajax-list"></tbody>
	<!-- 리스트 : 끝 -->
</table>
<!-- 주문리스트 테이블 : 끝 -->
<div class="hide" id="search_detail_dialog">
<form name="set_search_detail" method="post" action="set_search_default" target="actionFrame">
<div id="contents">
	<table class="search-form-table">
	<tr>
		<td>
			<table class="sf-option-table">
			<tr>
				<th width="100">주문일</th>
				<td class="date" height="30">
					<label class="search_label"><input type="radio" name="regist_date" value="today" <?php if(!$_GET["regist_date_type"]||$_GET["regist_date_type"]=='today'){?> checked="checked" <?php }?>/> 오늘</label>
					<label class="search_label"><input type="radio" name="regist_date" value="3day" <?php if($_GET["regist_date_type"]=='3day'){?> checked="checked" <?php }?>/> 3일간</label>
					<label class="search_label"><input type="radio" name="regist_date" value="7day" <?php if($_GET["regist_date_type"]=='7day'){?> checked="checked" <?php }?>/> 일주일</label>
					<label class="search_label"><input type="radio" name="regist_date" value="1mon" <?php if($_GET["regist_date_type"]=='1mon'){?> checked="checked" <?php }?>/> 1개월</label>
					<label class="search_label"><input type="radio" name="regist_date" value="3mon" <?php if($_GET["regist_date_type"]=='3mon'){?> checked="checked" <?php }?>/> 3개월</label>
					<label class="search_label"><input type="radio" name="regist_date" value="all" <?php if($_GET["regist_date_type"]=='all'){?> checked="checked" <?php }?>/> 전체</label>
				</td>
			</tr>

			<tr>
				<th>출고 전</th>
				<td>
<?php if(is_array($TPL_R1=config_load('step'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if($TPL_K1< 50||$TPL_K1> 80){?>
<?php if($_GET["chk_step"][$TPL_K1]){?>
					<label class="search_label"><input type="checkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" checked="checked" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }else{?>
					<label class="search_label"><input type="checkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }?>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>출고 후</th>
				<td>
<?php if(is_array($TPL_R1=config_load('step'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if($TPL_K1>= 50&&$TPL_K1< 80){?>
<?php if($_GET["chk_step"][$TPL_K1]){?>
					<label class="search_label"><input type="checkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" checked="checked" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }else{?>
					<label class="search_label"><input type="checkbox" name="chk_step[<?php echo $TPL_K1?>]" value="1" /> <span class="icon-order-step-<?php echo $TPL_K1?>"><?php echo $TPL_V1?></span></label>
<?php }?>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>결제수단</th>
				<td>
<?php if(is_array($TPL_R1=config_load('payment'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
<?php if(!preg_match('/escrow/',$TPL_K1)){?>
<?php if($_GET["payment"][$TPL_K1]){?>
					<label class="search_label"><input type="checkbox" name="payment[<?php echo $TPL_K1?>]" value="1" checked="checked" /> <?php echo $TPL_V1?></label>
<?php }else{?>
					<label class="search_label"><input type="checkbox" name="payment[<?php echo $TPL_K1?>]" value="1" /> <?php echo $TPL_V1?></label>
<?php }?>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>판매환경</th>
				<td>
<?php if($TPL_sitetypeloop_1){foreach($TPL_VAR["sitetypeloop"] as $TPL_K1=>$TPL_V1){?>
<?php if($_GET["sitetype"][$TPL_K1]){?>
						<label class="search_label" <?php if($TPL_K1=='MF'){?>style="width:150px"<?php }?> ><input type="checkbox" name="sitetype[<?php echo $TPL_K1?>]" value="<?php echo $TPL_K1?>" checked="checked" /> <?php echo $TPL_V1["name"]?></label>
<?php }else{?>
						<label class="search_label"  <?php if($TPL_K1=='MF'){?>style="width:150px"<?php }?> ><input type="checkbox" name="sitetype[<?php echo $TPL_K1?>]" value="<?php echo $TPL_K1?>" /> <?php echo $TPL_V1["name"]?></label>
<?php }?>
<?php }}?>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			<tr>
				<th>주문유형</th>
				<td colspan="3">
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[personal]" value="personal" <?php if($_GET["ordertype"]['personal']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_personal.gif"  align="absmiddle" /> 개인결제</label>
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[admin]" value="admin" <?php if($_GET["ordertype"]['admin']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_admin.gif" align="absmiddle" /> 관리자</label>
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[change]" value="change" <?php if($_GET["ordertype"]['change']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_exchange.gif" align="absmiddle" /> 맞교환</label>
					<label class="search_label" ><input type="checkbox" class="mod_chkbox" name="ordertype[gift]" value="gift" <?php if($_GET["ordertype"]['gift']){?>checked<?php }?>/> <img src="/admin/skin/default/images/design/icon_order_gift.gif" align="absmiddle" /> 사은품</label>
					<span class="icon-check hand all-check"><b>전체</b></span>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>
<div align="center" style="padding-top:10px;">
	<span class="btn large black">
		<button type="submit">저장하기<span class="arrowright"></span></button>
	</span>
</div>
</form>
</div>
<div id="goods_export_dialog"></div>



<div id="export_upload" class="hide">
<form name="excelRegist" id="excelRegist" method="post" action="../order_process/excel_upload" enctype="multipart/form-data"  target="actionFrame">
<table class="search-form-table" style="width:100%;">
<tr>
	<td height="20">① <b class="red">주문내역</b>을 파일 다운로드(.xls) 하십시오.</td>
</tr>
<tr>
	<td height="20">② 파일을 수정(출고수량, 택배사코드, 송장번호) 하십시오.</td>
</tr>
<?php if($TPL_VAR["config_system"]["invoice_use"]){?>
<tr>
	<td height="20" class="bold red">　 단, 택배업무자동화 서비스가 되는 택배사코드는 송장번호를 입력하지 마십시오.</td>
</tr>
<tr>
	<td height="20" class="bold red">　 택배업무 자동화 서비스 : <?php if(is_array($TPL_R1=get_invoice_company())&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?><?php echo $TPL_V1["company"]?> / <?php echo $TPL_K1?><?php }}?></td>
</tr>
<?php }?>
<tr>
	<td height="20">③ 수정된 파일을 'EXCEL 97~2003 통합문서(*.xls)'로 저장하십시오.</td>
</tr>
<tr>
	<td height="20">④ 아래에서 수정된 파일을 업로드 하십시오.</td>
</tr>
<tr>
	<td style="height:30px; line-height:30px; text-align:center;"><input type="file" name="excel_file" id="excel_file"/></td>
</tr>
<tr>
	<td height="20">⑤ 업로드 된 파일의 주문을 어떤 상태로 변경할지 선택하십시오.
		<select name="step">
			<option value="55">출고완료</option>
			<option value="45">출고준비</option>
		</select>
	</td>
</tr>
</table>

<div style="width:100%;text-align:center;padding-top:10px;">
<span class="btn large cyanblue"><button id="upload_submit">확인</button></span>
</div>


<div style="padding:15px;"></div>
<table class="info-table-style" style="width:100%">
<tr>
	<th class="its-th-align left" style="padding-left:20px;">
	<div style="height:25px;line-height:25px;">* 업로드 후 처리완료 메시지를 확인하십시오.</div>
	<div style="height:25px;line-height:25px;">* 메시지 확인 후 바로 처리 결과내역을 엑셀로 다운로드 받을 수 있습니다.</div>
	<div style="height:25px;line-height:25px;">* 반드시 처리 결과내역을 확인하십시오.</div>
	</th>
</tr>
</table>


<div class="item-title">택배사 코드 안내</div>
<table class="info-table-style" class="info-table-style" style="width:100%">
<colgroup>
	<col width="25%" />
	<col width="25%" />
	<col width="25%" />
	<col width="25%" />
</colgroup>
<thead>
<tr>
	<th class="its-th-align center">택배사</th>
	<th class="its-th-align center">코드</th>
	<th class="its-th-align center">택배사</th>
	<th class="its-th-align center">코드</th>
</tr>
</thead>
<tbody>
<tr>
<!-- <?php if(is_array($TPL_R1=array_merge(get_invoice_company(),config_load('delivery_url')))&&!empty($TPL_R1)){$TPL_I1=-1;foreach($TPL_R1 as $TPL_K1=>$TPL_V1){$TPL_I1++;?> -->
<?php if($TPL_I1% 2== 0&&$TPL_I1!= 0){?></tr><tr><?php }?>
<td class="its-td-align center"><?php echo $TPL_V1["company"]?></td>
<td class="its-td-align center"><?php echo $TPL_K1?></td>
<!-- <?php }}?> -->
</tr>
</tbody>
</table>
<br /><br />
</form>
</div>

<div id="order_type_help" style="display:none;">
	<table width="100%" class="info-table-style">
		<tr>
			<th width="110" rowspan="2" class="its-th-align"><b>주문 유형</b></th>
			<th width="250" rowspan="2" class="its-th-align"><b>관리자 행동</b></th>
			<th width="250" rowspan="2" class="its-th-align"><b>구매자 행동</b></th>
			<th width="190" rowspan="2" class="its-th-align"><b>주문 내역</b></th>
			<th colspan="2" class="its-th-align"><b>혜택</b></th>
		</tr>
		<tr>
			<th width="90" class="its-th-align"><b>할인</b></th>
			<th width="80" class="its-th-align"><b>적립</b></th>
		</tr>
		<tr>
			<td height="60" class="its-td-align center"><b>일반적 주문</b></td>
			<td class="its-td-align left "></td>
			<td class="its-td-align left ">&nbsp;구매자가 <font color="red">주문을 완료함</font></td>
			<td rowspan="3" class="its-td-align left ">&nbsp;<font color="red">모든 주문은 주문리스트에 쌓임</font><br>&nbsp;<span class="desc">개인결제는 <img src="/admin/skin/default/images/design/icon_order_personal.gif">로 표시<br>&nbsp;관리자수동주문은 <img src="/admin/skin/default/images/design/icon_order_admin.gif">으로 표시</span></td>
			<td class="its-td-align center desc">이벤트,복수구매<br>모바일,좋아요<br>등급,쿠폰,코드<br>적립금,이머니</td>
			<td class="its-td-align center desc">적립금/포인트</td>
		</tr>
		<tr>
			<td height="60" class="its-td-align center"><b>개인 결제</b></td>
			<td class="its-td-align left ">&nbsp;관리자가<br>&nbsp;구매자 전용의 개인결제를 만들어 줌<br>&nbsp;관리환경 > 주문 > <a href="/admin/order/personal"><span class=" highlight-link-text hand">개인결제리스트</span></a> 쌓임</td>
			<td class="its-td-align left ">&nbsp;구매자는<br>&nbsp;관리자가 만들어준 자신의 개인결제를<br>&nbsp;MY페이지에서 확인하고 <font color="red">주문을 완료함</font></td>
			<td class="its-td-align center desc ">에누리</td>
			<td class="its-td-align center desc ">적립금/포인트</td>
		</tr>
		<tr>
			<td height="60" class="its-td-align center"><b>관리자<br>수동주문</b></td>
			<td class="its-td-align left ">&nbsp;관리자가<br>&nbsp;구매자 대신 주문을 만들어 <font color="red">주문을 완료함</font></td>
			<td class="its-td-align left "></td>
			<td class="its-td-align center desc ">에누리</td>
			<td class="its-td-align center desc ">적립금/포인트</td>
		</tr>
	</table>
</div>

<div id="openmarket_service_guide" class="hide">
	판매마켓에 상품 판매를 위해 서비스를 신청해 주십시오.<br />
	판매마켓 > 서비스 신청/연장
</div>
<div id="openmarket_order_receive_guide" class="hide">
	외부 판매마켓에서 발생한 주문은<br />
	1시간에 2번씩 자동으로 수집합니다.<br />
	자동으로 수집되는 시간을 기다리기 힘드시면<br />
	[지금바로 주문수집] 버튼을 클릭하십시오.
</div>

<div id="coupon_apply_dialog" style="display:none;">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="list_table_style">
		<tbody>
		<tr>
		<td colspan="2">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
			<th width="480">적용상품</th>
			<th>쿠폰선택</th>
			<th width="60">할인가</th>
			</tr>
			</thead>
			</table>
		</td>
		</tr>
		<tbody id="coupon_goods_lay"></tbody>
	</table>
	<div align="center" style="padding-top:20px;" id="coupon_shipping_lay">
		<div style="width:100px;"><b>배송비 쿠폰 :</b></div>
		<div  ><div id="coupon_shipping_select"></div>
		</div>
	</div>
	<div align="center" style="padding:20px 0 20px 0;"><button type="button" id="coupon_order" class="couponbtn" >적용하기</button></div>
</div>

<div id="invoice_manual_dialog" class="hide">
<?php $this->print_("invoice_guide",$TPL_SCP,1);?>

</div>

<div id="print_setting_dialog" class="hide" style="line-height:20px;">
	<form action="../order_process/print_setting" method="post" target="actionFrame">
	<span class="fx12 black">1. 주문내역서에 주문번호를 바코드로 출력하시겠습니까?</span><br />
	<span class="fx11 gray">주문 검색창에서 바코드를 스캔하면 해당 주문건의 출고화면으로 바로 이동하여 출고처리가 편리해집니다.</span><br />
	<label><input type="radio" name="orderPrintOrderBarcode" value="1" <?php if($TPL_VAR["orderPrintOrderBarcode"]){?>checked<?php }?> /> 예</label> &nbsp; <label><input type="radio" name="orderPrintOrderBarcode" value="" <?php if(!$TPL_VAR["orderPrintOrderBarcode"]){?>checked<?php }?> /> 아니오</label><br />
	<br />
	<span class="fx12 black">2. 주문내역서에 주문상품의 상품코드를 출력하시겠습니까?</span><br />
	<label><input type="radio" name="orderPrintGoodsCode" value="1" <?php if($TPL_VAR["orderPrintGoodsCode"]){?>checked<?php }?> /> 예</label> &nbsp; <label><input type="radio" name="orderPrintGoodsCode" value="" <?php if(!$TPL_VAR["orderPrintGoodsCode"]){?>checked<?php }?> /> 아니오</label><br />
	<br />
	<span class="fx12 black">3. 주문내역서에 주문상품의 상품코드를 바코드로 출력하시겠습니까?</span><br />
	<span class="fx11 gray">해당 주문의 출고처리화면에서 실제 상품의 바코드를 스캔하면 주문상품이 맞는지 검증하여 <br />
	오배송 없이 정확하게 출고가 가능합니다. 해당 상품의 바코드를 계속 스캔하면 출고수량이 +1씩 증가합니다.</span><br />
	<label><input type="radio" name="orderPrintGoodsBarcode" value="1" <?php if($TPL_VAR["orderPrintGoodsBarcode"]){?>checked<?php }?> /> 예</label> &nbsp; <label><input type="radio" name="orderPrintGoodsBarcode" value="" <?php if(!$TPL_VAR["orderPrintGoodsBarcode"]){?>checked<?php }?> /> 아니오</label><br />
	<br />
	<div class="center">
		<span class="btn medium cyanblue"><input type="submit" value="저장" /></span>
	</div>
	</form>
</div>

<div id="openmarket_order_receive_dialog" class="hide">
	<form name="orderReceiveForm" action="../order_process/openmarket_order_receive" target="actionFrame">
	<input type="hidden" name="mall_code" value="" />
	<table class="simpleinfo-table-style" width="100%" border="0" cellpadding="0" cellspacing="0">
	<col /><col width="60" />
<?php if($TPL_linkage_mallnames_for_search_1){foreach($TPL_VAR["linkage_mallnames_for_search"] as $TPL_V1){?>
	<tr>
		<td class="pdl5"><?php echo $TPL_V1["mall_name"]?></td>
		<td align="center">
			<span class="btn small"><input type="button" value="수집" onclick="openmarket_order_receive_submit('<?php echo $TPL_V1["mall_code"]?>')" /></span>
		</td>
	</tr>
<?php }}?>
	</table>
	</form>
</div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>