<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/setting/order.html 000064918 */ 
$TPL_reasonLoop_1=empty($TPL_VAR["reasonLoop"])||!is_array($TPL_VAR["reasonLoop"])?0:count($TPL_VAR["reasonLoop"]);
$TPL_reasoncouponLoop_1=empty($TPL_VAR["reasoncouponLoop"])||!is_array($TPL_VAR["reasoncouponLoop"])?0:count($TPL_VAR["reasoncouponLoop"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">

/* 사유추가 */
function addReason(reasonCode, ctype){
	var obj="";
	if(reasonCode == "120"){
		obj = '<tr><td class="its-td-align center">↕</td><td class="its-td-align left" style="padding-left:10px;" colspan="3"><div style="position:relative">변&nbsp; &nbsp;심<span style="padding-left:15px;"></span><input type="hidden" name="codecd'+ctype+'[]" value="120"><input type="text" name="reason'+ctype+'[]" size="45" value=""><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td></tr>';
	}else if(reasonCode == "210"){
		obj = '<tr><td class="its-td-align center">↕</td><td class="its-td-align left" style="padding-left:10px;" colspan="3"><div style="position:relative">하&nbsp; &nbsp;자<span style="padding-left:15px;"></span><input type="hidden" name="codecd'+ctype+'[]" value="210"><input type="text" name="reason'+ctype+'[]" size="45" value=""><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td></tr>';
	}else if(reasonCode == "310"){
		obj = '<tr><td class="its-td-align center">↕</td><td class="its-td-align left" style="padding-left:10px;" colspan="3"><div style="position:relative">오배송<span style="padding-left:15px;"></span><input type="hidden" name="codecd'+ctype+'[]" value="310"><input type="text" name="reason'+ctype+'[]" size="45" value=""><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td></tr>';
	}

	$("#reasonTable"+ctype+" tbody").append(obj);

}

function delReason(obj){
	if($(obj).closest('tbody').children('tr').length>1) $(obj).closest('tr').remove();
}

$(document).ready(function() {
	/* 순서변경 */
	$("table.simplelist-table-style tbody").sortable({items:'tr'});

<?php if($TPL_VAR["runout"]){?>
	$("input[name='runout'][value='<?php echo $TPL_VAR["runout"]?>']").attr('checked',true);
<?php }?>
<?php if($TPL_VAR["cartDuration"]){?>
	$("select[name='cartDuration'] option[value='<?php echo $TPL_VAR["cartDuration"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["cancelDuration"]){?>
	$("select[name='cancelDuration'] option[value='<?php echo $TPL_VAR["cancelDuration"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["ableStockStep"]){?>
	$("input[name='ableStockStep'][value='<?php echo $TPL_VAR["ableStockStep"]?>']").attr('checked',true);
<?php }?>
<?php if($TPL_VAR["refundDuration"]){?>
	$("select[name='refundDuration'] option[value='<?php echo $TPL_VAR["refundDuration"]?>']").attr('selected',true);
<?php }?>

	$("input[name='ableStockStep']").change(function(){
		if($(this).is(":checked")){
			$(".ableStockStepImg").hide();
			$(".ableStockStep"+$(this).val()).show();//$("#ableStockStep"+$(this).val()).show();
		}
	}).change();

	//일반 과세사업자 > 세금계산서설정
	$("#biztype_tax").click(function(){
		$("#taxuse").val('1');
		$(".taxuselay").show();
		$(".taxuselaynone").hide();
	});

	//간이/면세사업자 세금계산서불가
	$("#biztype_taxexe").click(function(){
		$("#taxuse").val('0');
		$(".taxuselay").hide();
		$(".taxuselaynone").show();

	});

	//현금영수증
	$("input[name='cashreceiptuse']").click(function(){
		if( $(this).val() == 2 ){//현금영수증만 사용시
			$("#cashreceiptonlylay").show();
		}else{
			$("#cashreceiptonlylay").hide();
		}
	});

	//
	$("#hiworks_request").click(function(){
<?php if($TPL_VAR["domain"]=='firstmall.kr'){?>
		//alert("임시도메인에는 제공되지 않는 기능입니다. 대표 도메인으로 다시 \n접속해 주세요.\n(대표 도메인이 없으시면 먼저 my가비아에서 도메인 연결 신청을\n해주세요.)");
		//return;
<?php }?>
		$.get('hiworks_request', function(data) {
		  	$('#popup').html(data);
			openDialog("하이웍스 신청 <span class='desc'>&nbsp;</span>", "popup", {"width":"800","height":"630"});
		});
	});

<?php if($TPL_VAR["cashreceiptuse"]){?>
		$("input[name='cashreceiptuse'][value='<?php echo $TPL_VAR["cashreceiptuse"]?>']").attr('checked',true);
<?php }?>

<?php if($TPL_VAR["biztype"]){?>
		$("input[name='biztype'][value='<?php echo $TPL_VAR["biztype"]?>']").attr('checked',true);
<?php }?>

<?php if($TPL_VAR["taxuse"]){?>
		$("input[name='taxuse'][value='<?php echo $TPL_VAR["taxuse"]?>']").attr('checked',true);
<?php }?>

<?php if($TPL_VAR["hiworks_use"]){?>
		$("input[name='hiworks_use'][value='<?php echo $TPL_VAR["hiworks_use"]?>']").attr('checked',true);
<?php }?>


<?php if($TPL_VAR["cashreceiptpg"]){?>
		$("select[name='cashreceiptpg'] option[value='<?php echo $TPL_VAR["cashreceiptpg"]?>']").attr('selected',true);
<?php }?>

<?php if($TPL_VAR["cashreceipt_date"]){?>
		$("select[name='cashreceipt_date'] option[value='<?php echo $TPL_VAR["cashreceipt_date"]?>']").attr('selected',true);
<?php }?>

<?php if($TPL_VAR["cancelDisabledStep35"]){?>
		$("select[name='cancelDisabledStep35'][value='<?php echo $TPL_VAR["cancelDisabledStep35"]?>']").attr('selected',true);
<?php }?>

	/* 저장시 action조정 */
<?php if($TPL_VAR["config_system"]["pgCompany"]){?>
	$("#now_operating").html(" : <?php echo $TPL_VAR["config_system"]["pgCompany"]?> 사용");
	$("#now_operating2").html(" : <?php echo $TPL_VAR["config_system"]["pgCompany"]?> 사용");
<?php }?>

	$("select[name='cartDuration']").bind("change",function(){
		change_setting_msg();
	});

	$("select[name='cancelDuration']").bind("change",function(){
		change_setting_msg();
	});

	$("select[name='refundDuration']").bind("change",function(){
		change_setting_msg();
	});

<?php if($TPL_VAR["autocancel"]){?>
	$("input[name='autocancel'][value='<?php echo $TPL_VAR["autocancel"]?>']").attr('checked',true);
<?php }?>

<?php if($TPL_VAR["export_err_handling"]){?>
	$("input[name='export_err_handling'][value='<?php echo $TPL_VAR["export_err_handling"]?>']").attr('checked',true);
<?php }?>


	change_setting_msg();

	/* 상품상태별 이미지 세팅*/
	$("#goodsStatusImage").live("click",function(e){
		$('#popGoodsStatusImage').empty();

		$.ajax({
			type: "get",
			url: "../goods/goods_status_images_setting",
			success: function(result){
				$("#popGoodsStatusImage").html(result);
			}
		});
		openDialog("상품 상태별 이미지 세팅", "popGoodsStatusImage", {"width":"900","height":"450","show" : "fade","hide" : "fade"});
		e.preventDefault();
		return false;
	});
	changeFileStyle();

	/* 선택된 상품상태별이미지 변경창 출력 */
	$(".goodsStatusImage").live("click",function(){
		var codecd = $(this).attr('codecd');
		$("input[name='goodsStatusImageCode']").val(codecd);
		$(".nowGoodsStatusImage").html("<img src='"+$(this).attr('src')+"' />");
		closeDialog("popGoodsStatusImageChoice");
		openDialog("이미지 변경", "popGoodsStatusImageChoice", {"width":"570","height":"250","show" : "fade","hide" : "fade"});
	});

	// 차감금액 계산 최종결제금액 계산 세팅여부
<?php if($TPL_VAR["config_system"]["cutting_sale_use"]){?>
	$("input[name='cutting_sale_use']").attr('checked',true);
<?php }?>
<?php if($TPL_VAR["config_system"]["cutting_sale_price"]){?>
	$("select[name='cutting_sale_price'] option[value='<?php echo $TPL_VAR["config_system"]["cutting_sale_price"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["config_system"]["cutting_sale_action"]){?>
	$("select[name='cutting_sale_action'] option[value='<?php echo $TPL_VAR["config_system"]["cutting_sale_action"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["config_system"]["cutting_settle_use"]){?>
	$("input[name='cutting_settle_use']").attr('checked',true);
<?php }?>
<?php if($TPL_VAR["config_system"]["cutting_settle_price"]){?>
	$("select[name='cutting_settle_price'] option[value='<?php echo $TPL_VAR["config_system"]["cutting_settle_price"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["config_system"]["cutting_settle_action"]){?>
	$("select[name='cutting_settle_action'] option[value='<?php echo $TPL_VAR["config_system"]["cutting_settle_action"]?>']").attr('selected',true);
<?php }?>

	/* 맞교환,반품/환불,적립금포인트지급 단계 설정 */
	$("select[name='save_term']").val('<?php echo $TPL_VAR["save_term"]?>').attr("selected",true);
<?php if($TPL_VAR["save_type"]){?>
		$("select[name='save_type']").val('<?php echo $TPL_VAR["save_type"]?>').attr("selected",true);
<?php }?>

	$("select[name='buy_confirm_use']").val('<?php echo $TPL_VAR["buy_confirm_use"]?>').attr("selected",true);
	$("select[name='buy_confirm_use']").change(function(){
		var buy_confirm_use = $("select[name='buy_confirm_use'] option:selected").val();

		$(".buyConfirmUseImg").hide();
		if( buy_confirm_use == '1' ){

<?php if($TPL_VAR["buy_confirm_service_limit"]=='Y'&&$TPL_VAR["buy_confirm_use"]!='1'){?>
			openDialogAlert("무료몰+에서는 구매확정 기능을 사용하실 수 없습니다.", 400, 150,function(){
				$("select[name='buy_confirm_use']").val('0');
			});
			return;
<?php }?>

			$(".buy_confirm0").hide();
			$(".buy_confirm1").show();
			$(".buy_confirm_rowspan1").attr('rowspan',15);
			$(".buy_confirm_rowspan2").attr('rowspan',7);
			$(".buy_confirm_rowspan3").attr('rowspan',2);
			$(".buyConfirmUselay").show();
		}else{
			$(".buy_confirm1").hide();
			$(".buy_confirm0").show();
			$(".buy_confirm_rowspan1").attr('rowspan',14);
			$(".buy_confirm_rowspan2").attr('rowspan',6);
			$(".buy_confirm_rowspan3").attr('rowspan',1);
			$(".buyConfirmUselay").hide();
			$(".buyConfirmUse0").show();
		}

	}).change();
	/**
	$("input[name='buy_confirm_use'][value='<?php echo $TPL_VAR["buy_confirm_use"]?>']").attr("checked",true);
	$("input[name='buy_confirm_use']").change(function(){
		if($(this).is(":checked")){
			$("input[name='buy_confirm_use']").each(function(){
				if($(this).is(":checked")){
					$("."+$(this).closest("tr").attr('class')).find("select").removeAttr("disabled");
				}else{
					$("."+$(this).closest("tr").attr('class')).find("select").attr("disabled",true);
				}
			});

			$(".buyConfirmUseImg").hide();
			$("#buyConfirmUse"+$(this).val()).show();
		}
	}).change();
	**/
	$("select[name='save_term']").change(function(){
		if(!$(this).is(":disabled")){
			$("span.save_term").html(comma(num($(this).val())));
			$("select[name='save_term']").val($(this).val());
			$("#f_layer").html($(this).val());
		}
	}).change();

	$("input.use_setting").bind("click",function(){
		check_use_setting();
	});
	check_use_setting();

	$("input[name='runout']").bind("change",function(){
		check_runout();

		if($(this).is(":checked")){
			if($(this).val()=='stock'){
				$(".a_layer").html('재고 기준');
			}else if($(this).val()=='ableStock'){
				$(".a_layer").html('가용재고 기준');
			}else if($(this).val()=='unlimited'){
				$(".a_layer").html('재고 무관');
			}
		}
	});
	$("input[name='ableStockLimit'").bind("blur",function(){
		check_runout();
	});

	$("button#runout_info_button").bind("click",function(){
		$.get('../popup/information?template_path=runout', function(data) {
		  	$('#popup').html(data);
		});
		openDialog("[안내] 재고 Q&A", "popup", {"width":"1024","height":"770"});
	});
	check_runout();

	$("select[name='cancelDisabledStep35']").change(function(){
		var cancelDisabledStep35 = $("select[name='cancelDisabledStep35'] option:selected").val();
		//if(cancelDisabledStep35){
			$(".cancelDisabledStep35Img").hide();
			$("#cancelDisabledStep35_"+cancelDisabledStep35).show();
		//}
	}).change();

	$("input[name='export_err_handling']").change(function(){
		if($(this).is(":checked")){
			if($(this).val()=='ignore'){
				$(".export_err_handling_ignore").show();
			}else{
				$(".export_err_handling_ignore").hide();
			}
		}
	}).change();


<?php if($TPL_VAR["service_code"]=='P_FREE'){?>$(".GoodsCouponTable").hide()<?php }?>
});

function change_setting_msg(){
	$(".b_layer").html($("select[name='cartDuration']").val());
	$(".c_layer").html($("select[name='cancelDuration']").val());
	$("#f_layer").html($("select[name='save_term']").val());
}

// 차감금액 계산 최종결제금액 계산 세팅여부
function check_use_setting(){
	$("input.use_setting").each(function(){
		if( $(this).attr('checked') ){
			$(this).parent().next().attr('disabled',true);
		}else{
			$(this).parent().next().attr('disabled',false);
		}
	});
}

function check_runout()
{
	$("table.stock-qa-table tr").removeClass("red");
	$("input[name='runout']:checked").parent().parent().parent().addClass("red");
	$("input[name='runout']:checked").parent().parent().parent().next().addClass("red");
	var ableStockLimit = parseInt($("input[name='ableStockLimit'").val())+1;
	$("#ableStockLimitMsg").html(ableStockLimit);

}

</script>

<style>
.buyerHighlight {background:url("/admin/skin/default/images/common/icon_buyer.gif") no-repeat; display:inline-block; width:40px; height:15px; text-indent:-1000px; overflow:hidden; margin-right:2px; margin-bottom:1px;}
.managerHighlight {background:url("/admin/skin/default/images/common/icon_seller.gif") no-repeat; display:inline-block; width:40px; height:15px; text-indent:-1000px; overflow:hidden; margin-right:2px; margin-bottom:1px;}
.save_term {font-weight:bold;color:red}
#reasonTable td {background-color:#fff; border:1px solid #ddd}
#reasonTablecoupon td {background-color:#fff; border:1px solid #ddd}
.info-table-style th.ltsbgred {font-size:18px;background-color:#f00;width:18px;}

.coupon_status{color:red}
.coupon_status_all{color:red}
.coupon_order_status{color:gray}
.coupon_status_use{color:blue}
.coupon_input_value{color:green}
</style>

<form name="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/order" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 주문</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container body-height-resizing">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap body-height-resizing'>
		<div class="slc-body">

			<div class="item-title">주문 처리 설정 <span class="helpicon" title="주문처리에 관한 사항을 설정 합니다."></span></div>

<?php $this->print_("order_process",$TPL_SCP,1);?>


			<table width="100%" class="info-table-style ">
			<col /><col /><col /><col /><col /><col /><col /><col /><col />
			<tr>
				<th class="its-th-align center white bold ltsbgred" > A </th>
				<th class="its-th-align left bold" colspan="2" >재고에 따른 상품판매 여부<br/>[통합설정]
				<br/>
				<span class="btn small orange"><button type="button" id="runout_info_button">안내) 재고 Q&A </button></span>
				</th>
				<td  colspan="5" >
					<table width="100%" cellpadding="0" cellspacing="0"  class="stock-qa-table">
						<tr>
							<td class="its-td-align center">판매 방식</td>
							<td class="its-td-align center">상황의 발생</td>
							<td class="its-td-align center">재고(가용재고)의 변화</td>
							<td class="its-td-align center">상품의 상태 처리	</td>
							<td class="its-td-align center">결과</td>
						</tr>
						<tr>
							<td class="its-td" rowspan="2"><label><input type="radio" name="runout" value="stock" /> 재고가 있으면 판매</label></td>
							<td class="its-td">[좋은일] 상품을 발송(출고완료)하여</td>
							<td class="its-td">재고가  <strong>0</strong> 이 될 때</td>
							<td class="its-td">품절(또는 재고확보중)로 자동 업데이트되어</td>
							<td class="its-td-align center">판매 중지</td>
						</tr>
						<tr>
							<td class="its-td">[나쁜일] 결제취소/반품/주문처리되돌리기로</td>
							<td class="its-td">재고가 <strong>1</strong> 이상이 될 때</td>
							<td class="its-td">정상으로 자동 업데이트되어</td>
							<td class="its-td-align center">판매 가능</td>
						</tr>
						<tr>
							<td class="its-td" rowspan="2"><label><input type="radio" name="runout" value="ableStock" checked /> 가용재고가 있으면 판매</label></td>
							<td class="its-td">[좋은일] 상품의 주문으로</td>
							<td class="its-td">가용재고가  <input type="text" name="ableStockLimit" size="5" value="<?php echo $TPL_VAR["ableStockLimit"]?>" class="right line onlynumber_signed"> 이하로 될 때</td>
							<td class="its-td">품절(또는 재고확보중)로 자동 업데이트되어</td>
							<td class="its-td-align center">판매 중지</td>
						</tr>
						<tr>
							<td class="its-td">[나쁜일] 결제취소/반품/주문처리되돌리기로</td>
							<td class="its-td">가용재고가     <span id="ableStockLimitMsg" style="font-weight:bold"><?php echo $TPL_VAR["ableStockLimit"]+ 1?></span> 이상이 될 때</td>
							<td class="its-td">정상으로 자동 업데이트되어</td>
							<td class="its-td-align center">판매 가능</td>
						</tr>
						<tr>
							<td class="its-td" rowspan="2"><label><input type="radio" name="runout" value="unlimited" /> 재고와 상관없이 판매</label></td>
							<td class="its-td">[좋은일] 상품을 발송(출고완료)하여</td>
							<td class="its-td">재고가 차감되어도</td>
							<td class="its-td">정상으로 유지되어</td>
							<td class="its-td-align center">판매 가능</td>
						</tr>
						<tr>
							<td class="its-td "> [나쁜일] 결제취소/반품/주문처리되돌리기로</td>
							<td class="its-td "> 재고와 가용재고가 증가되면</td>
							<td class="its-td "> 당연히 정상으로 유지되어</td>
							<td class="its-td-align center"> 판매 가능</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th class="its-th-align center white bold ltsbgred" >B</th>
				<th class="its-th-align left bold"  colspan="2" ><div><b>청약철회 불가 상품 주문 시 <br/>청약철회 관련방침</b></th>
				<td  class="its-td-align"  colspan="5" >
					<table width="100%" cellpadding="0" cellspacing="0" >
						<tr>
							<td  >
								<textarea rows="8" name="cancellation" style="width:98%;" class="line" style="border:1px solid #ccc !important;padding:0px;"><?php echo $TPL_VAR["cancellation"]?></textarea>
							</td>
							<td width="23%">
								<table width="100%" cellpadding="0" cellspacing="0"  style="border-top:1px solid #ccc" >
								<tr>
									<th class="its-th-align center">청약철회</th>
									<th class="its-th-align center">주문무효</th>
									<th class="its-th-align center">결제취소</th>
									<th class="its-th-align center">반품/교환</th>
								</tr>
								<tr>
									<th class="its-th-align center">가능 상품</th>
									<th class="its-th-align center">○</th>
									<th class="its-th-align center">○</th>
									<th class="its-th-align center">○</th>
								</tr>
								<tr>
									<th class="its-th-align center">불가 상품</th>
									<th class="its-th-align center">○</th>
									<th class="its-th-align center red bold">X</th>
									<th class="its-th-align center red bold">X</th>
								</tr>
								</table>
							</td>
						</tr>
					</table>

					<div class="desc"> ※ 주문 시 청약철회 제한 상품에 대한 쇼핑몰의 [청약철회 관련방침] 정책을 입력해 주세요. 소비자는 청약철회 제한 상품을 주문 시 쇼핑몰의 [청약철회 관련방침] 정책에 동의하여 주문하게 됩니다.</div>
				</td>
			</tr>
			<tr>
				<th class="its-th-align center white bold ltsbgred" >B</th>
				<th class="its-th-align left bold"  colspan="2" >할인혜택의 차감금액 계산</th>
				<td class="its-td"  colspan="5" >
					<label><input type="checkbox" name="cutting_sale_use" class="use_setting" value="none"> 절사안함</label>
					<span>
						<span>
							또는 할인혜택을 계산할 때 차감금액을
							<select name="cutting_sale_price">
							<option value="10">일원단위 절사</option>
								<option value="100">십원단위 절사</option>
							</select>
							<select name="cutting_sale_action">
								<option value="dscending">절사(버림)</option>
								<option value="rounding">반올림</option>
								<option value="ascending">올림</option>
							</select>
						</span>
						<div class="desc">※ 예시 : 판매가 19,800원의 3%할인 시 차감금액은 594원, 일원단위 절삭 시 590원</div>
						<div class="desc">※ 할인혜택의 종류 : 이벤트, 복수구매, 모바일, 회원등급, 좋아요, 쿠폰, 프로모션코드, 유입경로</div>
					</span>
				</td>
			</tr>
			<tr>
				<th class="its-th-align center white bold ltsbgred" >B</th>
				<th class="its-th-align left bold"  colspan="2" >장바구니 보존 기간</th>
				<td class="its-td"  colspan="5" >
					장바구니 최근  변경일로부터
						<select name="cartDuration">
							<option value="1">1일</option>
							<option value="2">2일</option>
							<option value="3">3일</option>
							<option value="4">4일</option>
							<option value="5">5일</option>
							<option value="6">6일</option>
							<option value="7" selected="selected" >7일</option>
							<option value="8">8일</option>
							<option value="9">9일</option>
							<option value="10">10일</option>
							<option value="11">11일</option>
							<option value="12">12일</option>
							<option value="13">13일</option>
							<option value="14">14일</option>
						</select>
						동안만 소비자가 <b>장바구니에 담은 상품을 유지</b> →기간이 경과하면 장바구니를 비움
				</td>
			</tr>

			<tr>
				<th class="its-th-align center white bold ltsbgred" >C</th>
				<th class="its-th-align left bold"  colspan="2" >자동 주문 무효일</th>
				<td class="its-td"  colspan="5" >
					<div>
						<label><input type="radio" value="y" name="autocancel" checked="checked"> 자동 주문무효 사용 : 접수된 주문건이 <b>결제가 되지 않고 미입금 상태로</b></label>
						<select name="cancelDuration">
							<option value="1">1일</option>
							<option value="2">2일</option>
							<option value="3">3일</option>
							<option value="4">4일</option>
							<option value="5">5일</option>
							<option value="6">6일</option>
							<option value="7">7일</option>
							<option value="8">8일</option>
							<option value="9">9일</option>
							<option value="10">10일</option>
							<option value="11">11일</option>
							<option value="12">12일</option>
							<option value="13">13일</option>
							<option value="14">14일</option>
						</select>
						이 경과하면 → 해당 주문건을 자동으로 <b>주문무효 처리</b>
					</div>
					<div>
						<label><input type="radio" value="n" name="autocancel" > 자동 주문무효 미사용 : 접수된 주문건을 자동으로 주문무효 처리하지 않음</label>
					</div>
				</td>
			</tr>

			<tr>
				<th class="its-th-align center white bold ltsbgred" >D</th>
				<th class="its-th-align left bold"  colspan="2" >가용재고 계산
				<br/>
				<span class="btn small orange"><button type="button"  onclick="openDialog('[안내] 가용재고 예시', 'able_stock_step_layer',{'width':'1000'})" >안내) 가용재고 예시 </button></span>
				</th>
				<td class="its-td"  colspan="5" >
					<ul>
						<li>
						<label><input type="radio" name="ableStockStep" value="25" checked /> 가용재고(판매 가능한 수량) = 재고 - 출고예약량(결제확인+상품준비+출고준비)</label>
						<span class="desc">:  결제가 완료된 주문건만 출고예약량으로 판단하는 올바른 방식 (권장 옵션)</span>
						</li>
						<li style="padding-top:5px;"><label><input type="radio" name="ableStockStep" value="15" /> 가용재고(판매 가능한 수량) = 재고 - 출고예약량(주문접수+결제확인+상품준비+출고준비)</label>
						</li>
					</ul>
					<div id="able_stock_step_layer" class="left hide">
						<ul>
							<li>
							■ 가용재고(판매 가능한 수량) = 재고 - 출고예약량(결제확인+상품준비+출고준비)
							</li>
							<li><img src="/admin/skin/default/images/common/ex_stock_reckoning.gif"></li>
							<li style="padding-top:5px;">■ 가용재고(판매 가능한 수량) = 재고 - 출고예약량(주문접수+결제확인+상품준비+출고준비)</label></li>
							<li><img src="/admin/skin/default/images/common/ex_stock_reckoning_15.gif"></li>
						</ul>
					</div>
				</td>
			</tr>

			<tr>
				<th class="its-th-align center white bold ltsbgred" >E</th>
				<th class="its-th-align left bold"  colspan="2" >재고에 따른 출고 가능여부
				<br/>
				<span class="btn small orange"><button type="button"  onclick="openDialog('[안내] 판매여부 VS 출고여부 ', 'export_err_handling_layer',{'width':'1000'})" >안내) 판매여부 VS 출고여부 </button></span></th>
				<td class="its-td"  colspan="5" >
					<div>
						<label><input type="radio" name="export_err_handling" value="error" /> 재고가 부족하면 출고완료 불가능
						<span class="desc">: `출고수량(보내는 수량)`보다 > `재고수량(보유한 수량)`이 적으면 출고완료가 처리되지 않음 (권장 옵션) </span></label>
						<span class="desc"> <br/>
						&nbsp;&nbsp;&nbsp;&nbsp;※ 실물배송상품 → 결제확인된 주문건에 대하여 <span class="red">실물상품 재고가 있을 때</span> 출고처리 가능
						<br/>
						&nbsp;&nbsp;&nbsp;&nbsp;※ 쿠폰발송상품 → 결제확인된 주문건에 대하여 <span class="red">쿠폰상품 재고가 있고 쿠폰번호가 있으면 자동</span> 출고완료되어 쿠폰(티켓)번호가 발송됨
						 </span>
						<br/>
						<label><input type="radio" name="export_err_handling" value="ignore" checked="checked" /> 재고가 부족해도 출고완료 가능  </span><span class="desc"> : `출고수량(보내는 수량)`보다 > `재고수량(보유한 수량)`이 적어도 출고완료가 처리됨</span> </label>

						<span class="desc"> <br/>
						&nbsp;&nbsp;&nbsp;&nbsp;※ 실물배송상품 → 결제확인된 주문건에 대하여 <span class="red">실물상품 재고와 무관하게</span> 출고처리 가능
						 <br/>
						&nbsp;&nbsp;&nbsp;&nbsp;※ 쿠폰발송상품 → 결제확인된 주문건에 대하여 <span class="red">쿠폰상품 재고와 무관하게 쿠폰번호가 있으면 자동</span> 출고완료되어 쿠폰(티켓)번호가 발송됨
						 </span>
						<br/>
						</div>
					</div>

					<div id="export_err_handling_layer" class="left hide">
						<ul>
							<li>■ 재고에 따른 상품판매 여부</li>
							<li>상품(옵션별)의 재고 유무에 따라 → 주문을 받을지 안받을지 결정하는 정책입니다.</li>
							<li style="padding-top:5px;">■ 재고에 따른 출고가능 여부</label></li>
							<li>주문된 상품(옵션별)의 재고 유무에 따라 → 출고처리를 할지 안할지 결정하는 정책입니다.</li>
						</ul>
					</div>
				</td>
			</tr>

			<tr>
				<th class="its-th-align center white bold ltsbgred" rowspan="4" >F</th>
				<th class="its-th-align left bold" rowspan="4" >재고 변동 시점
				<br/>
				<span class="btn small orange"><button type="button" onclick="openDialog('부분출고 예시', 'sub_export_desc_layer',{'width':'1000'})">안내) 부분 출고 </button></span>
					<div id="sub_export_desc_layer" class="center hide">
						<img src="/admin/skin/default/images/common/img_release_guide_tbl.gif" />
					</div>
				</th>
				<th class="its-th-align center bold" > 차감시점 </th>
				<td class="its-td"  colspan="5" > `출고완료`처리를 할 때 차감 <span class="desc">(상품을 보내는 시점에 차감)</span></td>
			</tr>
			<tr>
				<th class="its-th-align center bold" > 차감수량 </th>
				<td class="its-td"  colspan="5" > 보내는(출고완료) 상품의 수량만큼만 정확하게 자동으로 재고 차감</td>
			</tr>
			<tr>
				<th class="its-th-align center bold" > 증가시점 </th>
				<td class="its-td"  colspan="5" > ‘반품완료’처리를 할 때 증가  <span class="desc">(상품을 회수 받은 시점에 증가. 단, 실물상품에 한함)</span></td>
			</tr>
			<tr>
				<th class="its-th-align center bold" > 증가수량 </th>
				<td class="its-td"  colspan="5" > 반품된 상품의 수량만큼만 정확하게 자동으로 재고 증가 <span class="desc">(단, 실물상품에 한함)</span></td>
			</tr>

			<tr>
				<th class="its-th-align center white bold ltsbgred" >G</th>
				<th class="its-th-align left bold"  colspan="2" >
					실물 배송 상품의
					<br/>택배업무자동화
					<span class="btn small cyanblue"><button type="button" class="" onclick="document.location.href='./shipping'" >세팅 바로가기</button></span>
				</th>
				<td colspan="5" >
					<table width="100%" cellpadding="0" cellspacing="0" >
						<tr>
							<td class="its-td center">① 주문내역 프린트</td>
							<td class="its-td center">② 상품 가져오기</td>
							<td class="its-td center">③ 운송장번호 입력</td>
							<td class="its-td center">④ 운송장 출력</td>
							<td class="its-td center">⑤ 택배사에 출고정보 전달</td>
							<td class="its-td center">⑥ 배송 추적</td>
						</tr>
						<tr>
							<td class="its-td center">보낼 상품<br/>목록 출력</td>
							<td class="its-td center">보낼 상품<br/>가져오기</td>
							<td class="its-td center">자동 입력</td>
							<td class="its-td center">배로 출력</td>
							<td class="its-td center">택배사에 자동 전달</td>
							<td class="its-td center">실시간 추적 및 자동 배송완료</td>
						</tr>
					</table>
				</td>
			</tr>

			<tr>
				<th class="its-th-align center white bold ltsbgred buy_confirm_rowspan1" rowspan="14" >h <br/>i<br/>j<br/>K</th>
				<th class="its-th-align center"  colspan="2" >↓처리 기준 상품</th>
				<td class="its-td-align center">처리 사항</td>
				<td class="its-td-align center"><span class="buy_confirm1 hide">행위</span><span class="buy_confirm0 " >누가</span></td>
				<td class="its-td-align center">위치</td>
				<td class="its-td-align center">조건</td>
				<td class="its-td-align center">결과</td>
			</tr>
			<tr  class="GoodsOrigTable" >
				<th class="its-th-align left bold buy_confirm_rowspan2"  colspan="2"  rowspan="6"  > <select name="buy_confirm_use">
						<option value="0" selected> 구매확정 미사용</option>
						<option value="1">구매확정 사용</option>
					</select><br/>
				실물 배송 상품의<br/>
				취소, 적립, 반품, 맞교환
				<div class=" pdt5"><span class="btn small orange"><button type="button" onclick="openDialog('[안내] 실물배송상품의 취소(환불)', 'goods_origing_layer1',{'width':'1000'})" >안내) 실물의 취소(환불) </button></span></div>
					<div id="goods_origing_layer1" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 취소(환불)</th>
						</tr>
						<tr><td  class="its-td left">
								<ol style="list-style-type: decimal;margin-left:10px;">
									<li>취소 가능 시점 판단</li>
									<li>취소 가능 상품 판단 (취소수량이 없거나 청약철회 불가 상품은 취소 불가능)</li>
									<li>취소 가능 수량 판단 (취소된 상품수량을 누적하여 제외)</li>
									<li>취소 신청 완료 (환불리스트에서 신청 내역 확인)</li>
									<li>취소금액 환불 <br />
										<div style="margin-left:-5px;" class="desc">
										1) 구매자나 관리자나 모두 신청할 수 있습니다.<br/>
										2) 결제취소 신청 단계를 선택할 수 있습니다.<br/>
										3) 상품청약철회 불가 상품은 구매자가 결제취소 할 수 없습니다.<br/>
										4) 결제취소를 할 때 취소 수량을 결정할 수 있습니다.<br/>
										5) 신용카드 결제취소는 자동으로 취소처리 됩니다.<br/>
										6) 결제취소로 인한 모든 취소금액을 환불리스트에서 일괄 관리합니다.<br/>
										7) 결제취소 처리 시 사용한 적립금은 자동으로 환원됩니다.<br/>
										8) 결제취소 처리 시 사용한 쿠폰은 자동으로 재발급됩니다.<br/>
										9) 결제취소 처리 시 가용재고가 취소수량만큼 자동으로 계산됩니다.<br/>
										10) 취소상품(수량)에 대한 환불금액이 자동으로 계산되며 조정할 수 있습니다.
										</div>
									</li>
								</ol>
							</td>
						</tr>
						</table>
					</div>
				<div class=" pdt5"><span class="btn small orange"><button type="button"  onclick="openDialog('[안내] 실물배송상품의 적립', 'goods_origing_layer2',{'width':'1000'})" >안내) 실물의 적립 </button></span></div>
					<div id="goods_origing_layer2" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 적립</th>
						</tr>
						<tr><td  class="its-td left">
								<ol style="list-style-type: decimal;margin-left:10px;">
									<li>배송완료 된 상품 판단</li>
									<li>배송완료 된 상품 수량 판단</li>
									적립금액 적립 (발송된 상품 수량에 대해서는 정확하게 지급)</li>
								</ol>
						</td></tr>
						</table>
					</div>
				<div class=" pdt5"><span class="btn small orange"><button type="button"   onclick="openDialog('[안내] 실물배송상품의  반품(환불)', 'goods_origing_layer3',{'width':'1000'})" >안내) 실물의 반품(환불) </button></span></div>
					<div id="goods_origing_layer3" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 미사용 쿠폰 환불</th>
						</tr>
						<tr><td  class="its-td left">
								<ol style="list-style-type: decimal;margin-left:10px;">
								<li >반품 가능 상품 판단</li>
								<li >반품 가능 수량 판단 (반품된 상품수량을 누적하여 제외)</li>
								<li >반품 신청 완료 (반품리스트와 환불리스트에서 신청 내역 확인)</li>
								<li >구매자로부터 반품상품을 회수하여 상품의 이상유무 확인</li>
								<li >반품상품을 반품완료 처리 (반품수량만큼 재고 자동 증가)</li>
								<li >반품금액 환불
									<div style="margin-left:-5px;" class="desc">
										1) 구매자나 관리자나 모두 신청할 수 있습니다.<br/>
										2) 반품/맞교환 신청 단계를 선택할 수 있습니다. (구매확정 여부에 따름)<br/>
										3) 반품/맞교환 할 때 반품 수량을 결정할 수 있습니다.<br/>
										4) 반품에 대한 환불처리 시 신용카드 취소가 가능합니다.<br/>
										5) 반품에 대한 환불로 인한 모든 환불금액을 환불리스트에서 일괄 관리합니다.<br/>
										6) 반품완료 처리 시 재고가 반품수량만큼 자동 계산됩니다.<br/>
										7) 반품완료 처리 시 교환상품의 주문이 자동으로 생성(0원 및 결제확인 상태)<br/>
										8) 반품에 대한 환불처리 시 지급된 적립금이 자동 회수됩니다.<br/>
										9) 반품에 대한 환불처리 시 환불 수단을 결정할 수 있습니다.<br/>
										10) 반품상품(수량)에 대한 환불금액이 자동으로 계산되며 조정할 수 있습니다.
									</div>
								</li>
								</ol>
						</td></tr>
						</table>
					</div>
				<div class=" pdt5"><span class="btn small orange"><button type="button"  onclick="openDialog('[안내] 실물배송상품의 맞교환', 'goods_origing_layer4',{'width':'1000'})" >안내) 실물의 맞교환 </button></span></div>
				<div id="goods_origing_layer4" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 미사용 쿠폰 환불</th>
						</tr>
						<tr><td  class="its-td left">
								<ol style="list-style-type: decimal;margin-left:10px;">
								<li >반품 가능 상품 판단</li>
								<li >반품 가능 수량 판단 (반품된 상품수량을 누적하여 제외)</li>
								<li >반품 신청 완료 (반품리스트와 주문리스트에서 신청 내역 확인)</li>
								<li >구매자로부터 반품상품을 회수하여 상품의 이상유무 확인</li>
								<li >반품상품을 반품완료 처리 (반품수량만큼 재고 자동 증가)</li>
								<li >주문리스트에서 맞교환 상품의 수량만큼 자동으로 재주문된 주문을 출고처리
									<div style="margin-left:-5px;" class="desc">
										1) 구매자나 관리자나 모두 신청할 수 있습니다.<br/>
										2) 반품/맞교환 신청 단계를 선택할 수 있습니다. (구매확정 여부에 따름)<br/>
										3) 반품/맞교환 할 때 반품 수량을 결정할 수 있습니다.<br/>
										4) 반품에 대한 환불처리 시 신용카드 취소가 가능합니다.<br/>
										5) 반품에 대한 환불로 인한 모든 환불금액을 환불리스트에서 일괄 관리합니다.<br/>
										6) 반품완료 처리 시 재고가 반품수량만큼 자동 계산됩니다.<br/>
										7) 반품완료 처리 시 교환상품의 주문이 자동으로 생성(0원 및 결제확인 상태)<br/>
										8) 반품에 대한 환불처리 시 지급된 적립금이 자동 회수됩니다.<br/>
										9) 반품에 대한 환불처리 시 환불 수단을 결정할 수 있습니다.<br/>
										10) 반품상품(수량)에 대한 환불금액이 자동으로 계산되며 조정할 수 있습니다.
									</div>
								</li>
								</ol>
						</td></tr>
						</table>
					</div>
				<div class=" pdt5"><span class="btn small orange"><button type="button" onclick="openDialog('구매확정 내역', 'buy_confirm_desc_layer',{'width':'350','height':210})" >안내) 구매확정 내역 </button></span></div>
					<div id="buy_confirm_desc_layer" class="center hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align">구매확정 행위자</th>
							<th class="its-th-align">구매확정 로그</th>
						</tr>
						<tr>
							<td class="its-td-align">구매자</td>
							<td class="its-td-align">구매확정 일시 + 구매자</td>
						</tr>
						<tr>
							<td class="its-td-align">관리자</td>
							<td class="its-td-align">구매확정 일시 + 판매자</td>
						</tr>
						<tr>
							<td class="its-td-align">시스템</td>
							<td class="its-td-align">구매확정 일시 + 자동</td>
						</tr>
						</table>
					</div>
				</th>
				<td class="its-td  left"  rowspan="2" >취소(환불)</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span></td>
				<td class="its-td-align  center">MY페이지</td>
				<td class="its-td left">
					<select name="cancelDisabledStep35">
						<option value="0" <?php if($TPL_VAR["cancelDisabledStep35"]!= 1){?> selected="selected" <?php }?>> 결제확인, 상품준비</option>
						<option value="1"  <?php if($TPL_VAR["cancelDisabledStep35"]== 1){?> selected="selected" <?php }?>>결제확인</option>
					</select>
					<span>상태의 주문 상품만 취소 가능</span>
				</td>
				<td class="its-td left" rowspan="2">취소 → 환불</td>
			</tr>
			<tr class="GoodsOrigTable" >
				<td class="its-td-align  center"><span class="iconsellerHighlight">판매자</span></td>
				<td class="its-td-align  center">관리자환경</td>
				<td class="its-td left">
					<span class="icon-order-step-25">결제확인</span>
					<span class="icon-order-step-35">상품준비</span>
					상태의 주문 상품만 취소 가능
				</td>
			</tr>
			<tr class="GoodsOrigTable buy_confirm1" >
				<td class="its-td left"  rowspan="2" >구매 적립</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span><br/><span class="icon-invoice-auto"></span></td>
				<td class="its-td-align  center">MY페이지</td>
				<td class="its-td left">
						<span class="icon-order-step-55">출고완료</span>
						<span class="icon-order-step-65">배송중</span>
						<span class="icon-order-step-75">배송완료</span>
						상태의 주문 상품을
						<select name="save_term">
						<option value="7" selected="selected" >7일</option>
						<option value="8">8일</option>
						<option value="9">9일</option>
						<option value="10">10일</option>
						<option value="11">11일</option>
						<option value="12">12일</option>
						<option value="13">13일</option>
						<option value="14">14일</option>
						<option value="20">20일</option>
						<option value="25">25일</option>
						<option value="30">30일</option>
						</select>이내에 구매확정 하면 지급 <br/>
						만약 <span class="save_term"></span>일 동안 구매확정 하지 않으면,
						구매확정은 자동 <select name="save_type">
							<option value="exist" selected>처리되지만 적립금/포인트는 소멸시킵니다 </option>
							<option value="give">처리되고 적립금/포인트도 지급합니다 </option>
						</select>
					</td>
				<td class="its-td left " rowspan="2" >구매확정 → 자동 배송완료<br/>지급</td>
			</tr>
			<tr class="GoodsOrigTable buy_confirm1" >
				<td class="its-td-align center"><span class="iconsellerHighlight">판매자</span></td>
				<td class="its-td-align center">관리자환경</td>
				<td class="its-td left">
					<span class="icon-order-step-55">출고완료</span>
					<span class="icon-order-step-65">배송중</span>
					<span class="icon-order-step-75">배송완료</span>
					상태의 주문 상품을 구매확정 하면 지급
				</td>
			</tr>
			<tr class="GoodsOrigTable buy_confirm0" >
				<td class="its-td left buy_confirm_rowspan3"  rowspan="2" >구매 적립</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span></td>
				<td class="its-td-align  center">MY페이지</td>
				<td class="its-td left">
						주문 상품을 <span class="icon-order-step-75">배송완료</span> 상태로 처리하면 지급
					</td>
				<td class="its-td left" >지급</td>
			</tr>
			<tr class="GoodsOrigTable" >
				<td class="its-td left"  rowspan="3" >반품(환불)<br/>맞교환(재주문)</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span></td>
				<td class="its-td-align center">MY페이지</td>
				<td class="its-td left">
					<div class="buy_confirm1">
						<span class="icon-order-step-55">출고완료</span>
						<span class="icon-order-step-65">배송중</span>
						<span class="icon-order-step-75">배송완료</span>
						상태의 주문 상품을 구매확정(<span class="save_term"></span>일) 이전에만 반품/맞교환 가능
					</div>
					<div class="buy_confirm0">
						<span class="icon-order-step-75">배송완료</span> 후
						<select name="save_term">
						<option value="7" selected="selected" >7일</option>
						<option value="8">8일</option>
						<option value="9">9일</option>
						<option value="10">10일</option>
						<option value="11">11일</option>
						<option value="12">12일</option>
						<option value="13">13일</option>
						<option value="14">14일</option>
						<option value="20">20일</option>
						<option value="25">25일</option>
						<option value="30">30일</option>
						</select>이내에만 반품/맞교환 가능
					</div>
				</td>
				<td class="its-td left" rowspan="2" >반품 → 환불<br/>맞교환 → 재주문</td>
			</tr>
			<tr class="GoodsOrigTable" >
				<td class="its-td-align  center"><span class="iconsellerHighlight">판매자</span></td>
				<td class="its-td-align  center">관리자환경</td>
				<td class="its-td left">
					<div class="buy_confirm1">
						<span class="icon-order-step-55">출고완료</span>
						<span class="icon-order-step-65">배송중</span>
						<span class="icon-order-step-75">배송완료</span>
						상태의 주문 상품을 언제나 반품/맞교환 가능
					</div>
					<div class="buy_confirm0">
						<span class="icon-order-step-75">배송완료</span> 후 언제나 반품/맞교환 가능
					</div>
					</td>
			</tr>
			<tr class="GoodsOrigTable" >
				<td class="its-td center" colspan="4">
					<table id="reasonTable" class="simplelist-table-style" align="center" style="width:98%">
						<thead>
						<tr>
							<td width="30" class="its-th-align center">순서</td>
							<td class="its-th" colspan="3"><img src="/admin/skin/default/images/common/icon_plus.gif" onclick="addReason(120,'');" style="cursor:pointer" align="absmiddle"> <b>변심</b> (구매자책임)
							&nbsp;&nbsp;&nbsp;&nbsp;<img src="/admin/skin/default/images/common/icon_plus.gif" onclick="addReason(210,'');" style="cursor:pointer" align="absmiddle"> <b>하자</b> (관리자책임) &nbsp;&nbsp;&nbsp;&nbsp;<img src="/admin/skin/default/images/common/icon_plus.gif" onclick="addReason(310,'');" style="cursor:pointer" align="absmiddle"> <b>오배송</b>(관리자책임)</td>
						</tr>
						</thead>
						<tbody>
<?php if($TPL_VAR["reasonLoop"]){?>
<?php if($TPL_reasonLoop_1){foreach($TPL_VAR["reasonLoop"] as $TPL_V1){?>
							<tr>
								<td class="its-td-align center move">↕</td>
								<td class="its-td-align left" style="padding-left:10px;" colspan="3"><div style="position:relative"><?php if($TPL_V1["codecd"]=='120'){?>변&nbsp; &nbsp;심<?php }elseif($TPL_V1["codecd"]=='210'){?>하&nbsp; &nbsp;자<?php }elseif($TPL_V1["codecd"]=='310'){?>오배송<?php }?><span style="padding-left:15px;"></span><input type="hidden" name="codecd[]" value="<?php echo $TPL_V1["codecd"]?>"><input type="text" name="reason[]" size="45" value="<?php echo $TPL_V1["reason"]?>"><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td>
							</tr>
<?php }}?>
<?php }else{?>
						<tr>
							<td class="its-td-align center move">↕</td>
							<td class="its-td-align left" style="padding-left:20px;" colspan="3"><div style="position:relative">변심<span style="padding-left:15px;"></span><input type="hidden" name="codecd[]" value="120"><input type="text" name="reason[]" size="45" value="사이즈가 맞지 않아요"><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td>
						</tr>
						<tr>
							<td class="its-td-align center move">↕</td>
							<td class="its-td-align left" style="padding-left:20px;" colspan="3"><div style="position:relative">하자<span style="padding-left:15px;"></span><input type="hidden" name="codecd[]" value="210"><input type="text" name="reason[]" size="45" value="제품파손-파손되었어요"><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td>
						</tr>
						<tr>
							<td class="its-td-align center move">↕</td>
							<td class="its-td-align left" style="padding-left:10px;" colspan="3"><div style="position:relative">오배송<span style="padding-left:15px;"></span><input type="hidden" name="codecd[]" value="310"><input type="text" name="reason[]" size="45" value="제품불일치-다른 상품이 왔어요"><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td>
						</tr>
<?php }?>
						</tbody>
					</table>
				</td>
			</tr>

			<tr class="GoodsCouponTable" >
				<th class="its-th-align left bold"  colspan="2" rowspan="6" >
				쿠폰 발송 상품의<br/>
				취소, 적립, 미사용쿠폰
				<div class=" pdt5"><span class="btn small orange"><button type="button" onclick="openDialog('[안내] 쿠폰발송상품의 취소(환불)', 'goods_coupon_layer1',{'width':'1000'})" >안내) 쿠폰의 취소(환불) </button></span></div>
					<div id="goods_coupon_layer1" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 취소(환불)</th>
						</tr>
						<tr><td  class="its-td  left">
								<ol style="list-style-type: decimal;margin-left:10px;">
									<li class="red">취소 가능 시점 판단</li>
									<li>취소 가능 상품 판단 (취소수량이 없거나 청약철회 불가 상품은 취소 불가능)</li>
									<li>취소 가능 수량 판단 (취소된 상품수량을 누적하여 제외)</li>
									<li>취소 신청 완료 (환불리스트에서 신청 내역 확인)</li>
									<li>취소금액 환불 <br />
										<div style="margin-left:-5px;" class="desc">
										1) 구매자나 관리자나 모두 신청할 수 있습니다.<br/>
										2) 결제취소 신청 단계를 선택할 수 있습니다.<br/>
										3) 상품청약철회 불가 상품은 구매자가 결제취소 할 수 없습니다.<br/>
										4) 결제취소를 할 때 취소 수량을 결정할 수 있습니다.<br/>
										5) 신용카드 결제취소는 자동으로 취소처리 됩니다.<br/>
										6) 결제취소로 인한 모든 취소금액을 환불리스트에서 일괄 관리합니다.<br/>
										7) 결제취소 처리 시 사용한 적립금은 자동으로 환원됩니다.<br/>
										8) 결제취소 처리 시 사용한 쿠폰은 자동으로 재발급됩니다.<br/>
										9) 결제취소 처리 시 가용재고가 취소수량만큼 자동으로 계산됩니다.<br/>
										10) 취소상품(수량)에 대한 환불금액이 자동으로 계산되며 조정할 수 있습니다.
										</div>
									</li>
								</ol>
							</td>
						</tr>
						</table>
					</div>

				<div class=" pdt5"><span class="btn small orange"><button type="button"  onclick="openDialog('[안내] 쿠폰발송상품의 적립', 'goods_coupon_layer2',{'width':'1000'})">안내) 쿠폰의 적립 </button></span></div>
					<div id="goods_coupon_layer2" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 적립</th>
						</tr>
						<tr><td  class="its-td left">
								<ol style="list-style-type: decimal;margin-left:10px;">
									<li class="red" >쿠폰번호가 발송(배송완료) 된 상품 판단</li>
									<li class="red" >쿠폰번호의 모든 값어치(횟수 또는 금액) 소진여부 판단</li>
									<li>적립금액 적립</li>
								</ol>
						</td></tr>
						</table>
					</div>
				<div class=" pdt5"><span class="btn small orange"><button type="button"  onclick="openDialog('[안내] 미사용 쿠폰 환불', 'goods_coupon_layer3',{'width':'1000'})">안내) 미사용 쿠폰 환불</button></span></div>

					<div id="goods_coupon_layer3" class="left hide">
						<table class="info-table-style" width="100%">
						<tr>
							<th class="its-th-align left">■ 미사용 쿠폰 환불</th>
						</tr>
						<tr><td  class="its-td left">
								<ol style="list-style-type: decimal;margin-left:10px;">
								<li class="red" >미사용 쿠폰 환불 대상여부 판단 (미대상 또는 청약철회 불가 상품은 불가능)</li>
								<li class="red" >미사용 쿠폰 환불 신청가능 기간 판단</li>
								<li class="red" >미사용 쿠폰 환불 신청 완료 (반품리스트와 환불리스트에서 신청 내역 확인)</li>
								<li class="red" >미사용 쿠폰금액(잔여 값어치의 %) 적립금 환불
									<div style="margin-left:-5px;" class="desc">
									1) 구매자나 관리자나 모두 신청할 수 있습니다.<br/>
									2) 상품청약철회 불가 상품은 구매자가 신청 할 수 없습니다.<br/>
									3) 신청 할 때 취소 쿠폰을 결정할 수 있습니다.<br/>
									4) 모든 취소금액을 환불리스트에서 일괄 관리합니다.<br/>
									5) 미사용 쿠폰 환불로 재고가 변하지는 않습니다.
									</div>
								</li>
								</ol>
						</td></tr>
						</table>
					</div>
				</th>
				<td class="its-td left" rowspan="2" >취소(환불)</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span></td>
				<td class="its-td-align  center">MY페이지</td>
				<td class="its-td left">
					<span class="icon-order-step-25">결제확인</span>
					<span class="icon-order-step-35">상품준비</span>
					상태의 주문 상품만 취소 가능
					</td>
				<td class="its-td left" rowspan="2">취소 → 환불</td>
			</tr>
			<tr  class="GoodsCouponTable" >
				<td class="its-td-align  center"><span class="iconsellerHighlight">판매자</span></td>
				<td class="its-td-align  center">관리자환경</td>
				<td class="its-td left">
					<span class="icon-order-step-25">결제확인</span>
					<span class="icon-order-step-35">상품준비</span>
					상태의 주문 상품만 취소 가능
				</td>
			</tr>
			<tr  class="GoodsCouponTable" >
				<td class="its-td left">구매 적립</td>
				<td class="its-td-align  center"><span class="icon-invoice-auto"></span></td>
				<td class="its-td-align  center">관리자환경</td>
				<td class="its-td-align  left"> 
					<div style="padding:3px;">
						↓아래와 같이 쿠폰의 유효성을 종합적으로 체크(사용,소멸,환불)하여 배송완료 및 적립 처리
						<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ccc" >
							<thead>
							<tr>
								<th class="its-th-align center">쿠폰 1장의 값어치에 대한 체크 사항 </th>
								<th class="its-th-align center" >상태 처리</th>
								<th class="its-th-align center"  >적립 처리</th>
							</tr> 
							</thead>
							<tbody> 
							<tr>
								<td class="its-td left"><span class="coupon_status_all">전체</span> 값어치 미사용</td>
								<td class="its-td left" >
									<span class="coupon_order_status">(출고완료 상태)</span>
									<br/><span class="coupon_status">사용대기</span>
								</td>
								<td class="its-td left" ></td>
							</tr> 
							<tr>
								<td class="its-td left"><span class="coupon_status_all">전체</span> 값어치 <span class="coupon_input_value">부분</span> 사용</td>
								<td class="its-td left" >
									자동 <span class="icon-order-step-75">배송완료</span>
									<br/><span class="coupon_status">부분사용 중</span>
								</td>
								<td class="its-td left" ></td>
							</tr> 

							<tr>
								<td class="its-td left"><span class="coupon_status_use">잔여</span> 값어치 모두 사용</td>
								<td class="its-td left" >
									<span class="coupon_order_status">(배송완료 상태)</span>
									<br/><span class="coupon_status">전체사용→가치종료</span>
								</td>
								<td class="its-td left" >적립</td>
							</tr>
							<tr>
								<td class="its-td left"><span class="coupon_status_all">전체</span> 값어치 모두 사용</td>
								<td class="its-td left" >
									자동 <span class="icon-order-step-75">배송완료</span>
									<br/><span class="coupon_status">전체사용→가치종료</span>
								</td>
								<td class="its-td left" >적립</td>
							</tr>
							<tr>
								<td class="its-td left"><span class="coupon_status_all">전체</span> 값어치 모두 있고 환불가능기간 만료</td>
								<td class="its-td left" >
									자동 <span class="icon-order-step-75">배송완료</span>
									<br/><span class="coupon_status">전체낙장→가치종료</span>
								</td>
								<td class="its-td left" >적립</td>
							</tr>
							<tr>
								<td class="its-td left"><span class="coupon_status_use">잔여</span> 값어치 <span class="coupon_input_value">남아</span> 있고 환불가능기간 만료</td>
								<td class="its-td left" >
									<span class="coupon_order_status">(배송완료 상태)</span>
									<br/><span class="coupon_status">부분낙장→가치종료</span>
								</td>
								<td class="its-td left" >적립</td>
							</tr>

							<tr>
								<td class="its-td left">
									(유효기간 종료 전) <br/>
									<span class="coupon_status_all">전체</span> 값어치 모두 있고 환불
								</td>
								<td class="its-td left" >
									자동 <span class="icon-order-step-75">배송완료</span>
									<br/><span class="coupon_status">유효기간 종료 전</span>
									<br/><span class="coupon_status">전체미사용→반품/환불→가치종료</span>
								</td>
								<td class="its-td left" >적립(환불금액만큼 제외)</td>
							</tr><tr>
								<td class="its-td left">
									(유효기간 종료 전) <br/>
									<span class="coupon_status_use">잔여</span> 값어치 <span class="coupon_input_value">남아</span>  있고 환불
								</td>
								<td class="its-td left" >
									<span class="coupon_order_status">(배송완료 상태)</span>
									<br/><span class="coupon_status">유효기간 종료 전</span>
									<br/><span class="coupon_status">부분미사용→반품/환불→가치종료</span>
								</td>
								<td class="its-td left" >적립(환불금액만큼 제외)</td>
							</tr>
							<tr>
								<td class="its-td left">
									(유효기간 종료 후) <br/>
									<span class="coupon_status_all">전체</span> 값어치 모두 있고 환불
								</td>
								<td class="its-td left" >
									자동 <span class="icon-order-step-75">배송완료</span>
									<br/><span class="coupon_status">유효기간 종료 후</span>
									<br/><span class="coupon_status">전체미사용→반품/환불→가치종료</span>
								</td>
								<td class="its-td left" >적립(환불금액만큼 제외)</td>
							</tr><tr>
								<td class="its-td left">
									(유효기간 종료 후) <br/>
									<span class="coupon_status_use">잔여</span> 값어치 <span class="coupon_input_value">남아</span>  있고 환불
								</td>
								<td class="its-td left" >
									<span class="coupon_order_status">(배송완료 상태)</span>
									<br/><span class="coupon_status">유효기간 종료 후</span>
									<br/><span class="coupon_status">부분미사용→반품/환불→가치종료</span>
								</td>
								<td class="its-td left" >적립(환불금액만큼 제외)</td>
							</tr>
							</tbody>
						</table>
					</div>
				</td>
				<td class="its-td left">상태처리, 적립</td>
			</tr>
			<tr class="GoodsCouponTable" >
				<td class="its-td left">유효기간 종료 전<br/>취소(환불)</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span><br/><span class="iconsellerHighlight">판매자</span>
				</td>
				<td class="its-td-align  center">MY페이지<br/>관리자환경</td>
				<td class="its-td left"><span class="desc" ><em>상품 등록/수정 시 상품별로 → 유효기간 종료 전 취소 조건을 설정하세요.</em></span></td>
				<td class="its-td left">취소 → 환불</td>
			</tr>
			<tr class="GoodsCouponTable" >
				<td class="its-td left">유효기간 종료 후<br/>미사용쿠폰환불</td>
				<td class="its-td-align  center"><span class="iconbuyerHighlight">구매자</span><br/><span class="iconsellerHighlight">판매자</span></td>
				<td class="its-td-align  center">MY페이지<br/>관리자환경</td>
				<td class="its-td left"><span class="desc" ><em>상품 등록/수정 시 상품별로 → 유효기간 종료 후 미사용쿠폰환불 조건을 설정하세요.</em></span></td>
				<td class="its-td left">취소 → 환불(적립금)</td>
			</tr>
			<tr class="GoodsCouponTable" >
				<td class="its-td left">취소(환불) 사유</td>
				<td class="its-td center" colspan="4">

					<table id="reasonTablecoupon" class="simplelist-table-style" align="center" style="width:98%">
						<thead>
						<tr>
							<td width="30" class="its-th-align center">순서</td>
							<td class="its-th" colspan="3"><img src="/admin/skin/default/images/common/icon_plus.gif" onclick="addReason(120,'coupon');" style="cursor:pointer" align="absmiddle"> <b>변심</b> (구매자책임)</td>
						</tr>
						</thead>
						<tbody>
<?php if($TPL_VAR["reasoncouponLoop"]){?>
<?php if($TPL_reasoncouponLoop_1){foreach($TPL_VAR["reasoncouponLoop"] as $TPL_V1){?>
							<tr>
								<td class="its-td-align center move">↕</td>
								<td class="its-td-align left" style="padding-left:10px;" colspan="3"><div style="position:relative"><?php if($TPL_V1["codecd"]=='120'){?>변&nbsp; &nbsp;심<?php }elseif($TPL_V1["codecd"]=='210'){?>하&nbsp; &nbsp;자<?php }elseif($TPL_V1["codecd"]=='310'){?>오배송<?php }?><span style="padding-left:15px;"></span><input type="hidden" name="codecdcoupon[]" value="<?php echo $TPL_V1["codecd"]?>"><input type="text" name="reasoncoupon[]" size="45" value="<?php echo $TPL_V1["reason"]?>"><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td>
							</tr>
<?php }}?>
<?php }else{?>
						<tr>
							<td class="its-td-align center move">↕</td>
							<td class="its-td-align left" style="padding-left:20px;" colspan="3"><div style="position:relative">변심<span style="padding-left:15px;"></span><input type="hidden" name="codecdcoupon[]" value="120"><input type="text" name="reasoncoupon[]" size="45" value="사이즈가 맞지 않아요"><div style="position:absolute; right:5px; top:5px;"><img src="/admin/skin/default/images/common/icon_minus.gif" onclick="delReason(this);" style="cursor:pointer"></div></div></td>
						</tr>
<?php }?>
						</tbody>
					</table>
				</td>
			</tr>
			</table>


	<!-- 서브메뉴 바디 : 끝 -->

</div>
<!-- 서브 레이아웃 영역 : 끝 -->

</form>
<!-- 아이콘 선택 -->
<div id="goodsIconPopup" class="hide">
	<form enctype="multipart/form-data" method="post" action="../goods_process/icon" target="actionFrame">
	<input type="hidden" name="iconIndex" value="0" />
	<ul>
<?php if(is_array($TPL_R1=code_load('goodsIcon'))&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_V1){?>
	<li style="float:left;width:100px;height:30px;text-align:center">
		<input type="hidden" name="goodsIconCode[]" value="<?php echo $TPL_V1["codecd"]?>">
		<img src="/data/icon/goods/<?php echo $TPL_V1["codecd"]?>.gif" border="0" class="hand hover-select">
	</li>
<?php }}?>
	</ul>
	<div class="clearbox"></div>
	<div>
	<input type="file" name="goodsIconImg" /> <span class="btn small black"><button type="submit">추가</button></span>
	</div>
	</form>
</div>

<!-- 상품상태별 이미지 선택 -->
<div id="popGoodsStatusImageChoice" class="hide">
	<form enctype="multipart/form-data" method="post" action="../goods_process/goods_status_image_upload" target="actionFrame">
	<input type="hidden" name="goodsStatusImageCode" value="" />
	<table align="center" height="160">
	<tr>
		<td><div class="nowGoodsStatusImage pd10"></div></td>
		<td><input type="file" name="goodsStatusImage" /> <span class="btn small black"><button type="submit">확인</button></span></td>
	</tr>
	</table>
	</form>
</div>

<!--### 상품상태별 이미지세팅 -->
<div id="popGoodsStatusImage" class="hide"></div>
<div id="popup" class="hide"></div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>