<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/setting/_order_process.html 000012378 */ ?>
<?php if(uri_string()!='admin/setting/order'){?>
	<div class="center pdb20">설정 &gt; <a href="/admin/setting/order" target="_blank"><span class=" highlight-link hand">주문</span></a>에서 처리 프로세스를 수정할 수 있습니다.</div>
<?php }?>
<div class="relative" style="width:1062px; height:288px; margin:10px auto;">
	<div><img src="/admin/skin/default/images/common/img_order_tree_bg_goods.gif" /></div>

	<div id="buyorderautodv1" style="position:absolute; left:634px; top:-40px;"><img src="/admin/skin/default/images/common/img_order_tree_auto_dv.gif" /></div>

	<div id="ableStockStep15" class="ableStockStep15 ableStockStepImg" style="position:absolute; left:245px; top:0px;"><img src="/admin/skin/default/images/common/img_order_tree_release_l.gif" /></div>
	<div id="ableStockStep25" class="ableStockStep25 ableStockStepImg" style="position:absolute; left:345px; top:0px;"><img src="/admin/skin/default/images/common/img_order_tree_release_s.gif" /></div>

	<div id="buyConfirmUse0" class="buyConfirmUse0 buyConfirmUseImg" style="position:absolute; left:861px; top:0px;"><img src="/admin/skin/default/images/common/img_order_tree_emoney_b.gif" /></div>
	<div id="buyConfirmUse1" class="buyConfirmUselay buyConfirmUseImg" style="position:absolute; left:980px; top:0px;"><img src="/admin/skin/default/images/common/img_order_tree_emoney_s.gif" /></div>
	<div id="buyConfirmUse" class="buyConfirmUselay" style="position:absolute; left:965px; top:40px;"><img src="/admin/skin/default/images/common/img_order_tree_confirm.gif" /></div>

	<div id="cancelDisabledStep35_0" class="cancelDisabledStep35_0 cancelDisabledStep35Img" style="position:absolute; left:382px; top:77px;"><img src="/admin/skin/default/images/common/img_order_tree_arw.gif" /></div>
	<div id="cancelDisabledStep35_1" class="cancelDisabledStep35_1 cancelDisabledStep35Img" style="position:absolute; left:382px; top:77px;"><img src="/admin/skin/default/images/common/img_order_tree_half.gif" /></div>

	<div id="export_err_handling_ignore" class="export_err_handling_ignore" style="position:absolute; width:183px; height:43px; left:547px; top:80px; background:url('/admin/skin/default/images/common/icon_oreder_tree_e_bg.gif'); line-height:49px; text-align:center;">
		<span class="bold blue" style=" width: 83px; letter-spacing: -1px; font-family: dotum; font-size: 12px;">재고가 부족해도 출고 가능</span>
	</div>



	<span id="a_layer" class="a_layer absolute center bold blue" style="left:11px; top:98px; width:83px; line-height:15px; font-family:dotum; font-size:12px; letter-spacing:-1px;"><b>000</b></span>
	<span id="b_layer" class="b_layer absolute right bold blue" style="left:157px; top:98px; width:28px; line-height:10px; font-family:tahoma; font-size:15px; "><b>111</b></span>
	<span id="c_layer" class="c_layer absolute right bold blue" style="left:258px; top:99px; width:32px; line-height:10px; font-family:tahoma; font-size:15px;"><b>222</b></span>
	<span id="f_layer" class="absolute right bold blue" style="left:885px; top:99px; width:32px; line-height:10px; font-family:tahoma; font-size:15px;"><b>333</b></span>
</div>

<div class="relative <?php if($TPL_VAR["service_code"]=='P_FREE'){?>hide<?php }?> " style="width:1062px; height:252px; margin:10px auto;">
	<div><img src="/admin/skin/default/images/common/img_order_tree_bg_cpn.gif" /></div>

	<div class="ableStockStep15 ableStockStepImg" style="position:absolute; left:245px; top:0px;"><img src="/admin/skin/default/images/common/img_order_tree_release_l.gif" /></div>
	<div  class="ableStockStep25 ableStockStepImg" style="position:absolute; left:345px; top:0px;"><img src="/admin/skin/default/images/common/img_order_tree_release_s.gif" /></div>

	<div class="cancelDisabledStep35_0 cancelDisabledStep35Img" style="position:absolute; left:382px; top:77px;"><img src="/admin/skin/default/images/common/img_order_tree_arw.gif" /></div>
	<div class="cancelDisabledStep35_1 cancelDisabledStep35Img" style="position:absolute; left:382px; top:77px;"><img src="/admin/skin/default/images/common/img_order_tree_half.gif" /></div>

	<div class="export_err_handling_ignore" style="position:absolute; width:183px; height:43px; left:547px; top:80px; background:url('/admin/skin/default/images/common/icon_oreder_tree_m_bg.gif'); line-height:49px; text-align:center;">
		<span class="bold blue" style=" width: 83px; letter-spacing: -1px; font-family: dotum; font-size: 12px;">재고가 부족해도 출고 가능</span>
	</div>

	<span class="a_layer absolute center bold blue" style="left:11px; top:98px; width:83px; line-height:15px; font-family:dotum; font-size:12px; letter-spacing:-1px;"><b>000</b></span>
	<span class="b_layer absolute right bold blue" style="left:157px; top:98px; width:28px; line-height:10px; font-family:tahoma; font-size:15px; "><b>111</b></span>
	<span class="c_layer absolute right bold blue" style="left:258px; top:99px; width:32px; line-height:10px; font-family:tahoma; font-size:15px;"><b>222</b></span>
</div>

<?php if(uri_string()!='admin/setting/order'){?>
	<table class="info-table-style" width="100%" align="center">
	<col width="100" />
	<tr>
		<th class="its-th-align center">A</th>
		<td class="its-td">
<?php if($TPL_VAR["runout"]=='stock'){?>상품의 재고가 <span class='red bold'>1</span>이상일 때 주문이 가능하다.<?php }?>
<?php if($TPL_VAR["runout"]=='ableStock'){?>상품의 가용재고가 <span class='red bold'><?php echo $TPL_VAR["ableStockLimit"]+ 1?></span>이상일 때 주문이 가능하다.<?php }?>
<?php if($TPL_VAR["runout"]=='unlimited'){?>상품의 재고와 <span class='red bold'>무관</span>하게 주문이 가능하다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">B</th>
		<td class="its-td">
			장바구니에 담은 상품은 <span class='red bold'><?php echo $TPL_VAR["cartDuration"]?></span>일 동안만 보관한다.
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">C</th>
		<td class="its-td">
<?php if($TPL_VAR["autocancel"]=='y'){?>미입금된 주문은 <span class='red bold'><?php echo $TPL_VAR["cancelDuration"]?></span>일이 경과하면 자동으로 무효처리 한다.<?php }?>
<?php if($TPL_VAR["autocancel"]!='y'){?>미입근된 주문을 자동으로 무효처리 하지 않는다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">D</th>
		<td class="its-td">
<?php if($TPL_VAR["ableStockStep"]=='25'){?>상품을 팔 수 있는 수량(가용재고)은 = 가지고 있는 수량(재고) - 이미 팔린 수량(출고예약수량)이며,<br />이미 팔린 수량은 = <span class='red bold'>결제확인 + 상품준비 + 출고준비</span>의 합이다.<?php }?>
<?php if($TPL_VAR["ableStockStep"]=='15'){?>상품을 팔 수 있는 수량(가용재고)은 = 가지고 있는 수량(재고) - 이미 팔린 수량(출고예약수량)이며,<br />이미 팔린 수량은 = <span class='red bold'>주문접수 + 결제확인 + 상품준비 + 출고준비</span>의 합이다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">M</th>
		<td class="its-td">
<?php if($TPL_VAR["export_err_handling"]=='error'){?>재고가 부족하면 출고완료 할 수 <span class='red bold'>없다.</span><?php }?>
<?php if($TPL_VAR["export_err_handling"]!='error'){?>재고가 부족해도 출고완료 할 수 <span class='red bold'>있다.</span><?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">E</th>
		<td class="its-td">
			상품을 보낼 때(출고완료), 해당 상품의 보내는 수량을 정확하게 <span class='red bold'>재고 차감</span>한다.
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">F</th>
		<td class="its-td">
<?php if($TPL_VAR["buy_confirm_use"]=='1'){?>반품/맞교환 신청은 상품 출고완료 후 <span class='red bold'><?php echo $TPL_VAR["save_term"]?></span>일 이내에만 할 수 있다. 또한 구매확정 이후에는 신청할 수 없다.<?php }?>
<?php if($TPL_VAR["buy_confirm_use"]!='1'){?>반품/맞교환 신청은 상품 출고완료 후 <span class='red bold'><?php echo $TPL_VAR["save_term"]?></span>일 이내에만 할 수 있다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">G</th>
		<td class="its-td">
<?php if($TPL_VAR["buy_confirm_use"]=='1'&&$TPL_VAR["save_type"]=='exist'){?>적립금/포인트는 상품 출고완료 후 <span class='red bold'><?php echo $TPL_VAR["save_term"]?></span>일 이내에 구매확정하면 지급한다. 기간 경과하면 소멸한다.<?php }?>
<?php if($TPL_VAR["buy_confirm_use"]=='1'&&$TPL_VAR["save_type"]=='give'){?>적립금/포인트는 상품 출고완료 후 <span class='red bold'><?php echo $TPL_VAR["save_term"]?></span>일 이내에 구매확정하면 지급한다. 기간 경과해도 지급한다.<?php }?>
<?php if($TPL_VAR["buy_confirm_use"]!='1'){?>적립금/포인트는 상품 <span class='red bold'>배송완료</span> 시 지급한다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">H</th>
		<td class="its-td">
			맞교환 상품이 회수가 되면(반품완료), 다시 보내야 하는 <span class='red bold'>주문서가 자동</span>으로 생성된다.
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">I</th>
		<td class="its-td">
			구매자에게 돈을 돌려 주어야 하는 경우(결제취소 또는 반품→환불) <span class='red bold'>환불리스트</span>에 내역이 쌓인다.
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">J</th>
		<td class="its-td">
<?php if($TPL_VAR["buy_confirm_use"]=='1'){?>반품은 해당 상품이 <span class='red bold'>배송완료</span>일 때 신청 가능하다.<?php }?>
<?php if($TPL_VAR["buy_confirm_use"]!='1'){?>반품은 해당 상품이 <span class='red bold'>출고완료, 배송 중, 배송완료</span>일 때 신청 가능하다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">K</th>
		<td class="its-td">
<?php if($TPL_VAR["buy_confirm_use"]=='1'){?>맞교환은 해당 상품이 <span class='red bold'>배송완료</span>일 때 신청 가능하다.<?php }?>
<?php if($TPL_VAR["buy_confirm_use"]!='1'){?>맞교환은 해당 상품이 <span class='red bold'>출고완료, 배송 중, 배송완료</span>일 때 신청 가능하다.<?php }?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">L</th>
		<td class="its-td">
<?php if($TPL_VAR["cancelDisabledStep35"]!='1'){?>결제취소는 해당 상품이 <span class='red bold'>결제확인, 상품준비일</span>일 때 신청 가능하다.<?php }?>
<?php if($TPL_VAR["cancelDisabledStep35"]=='1'){?>결제취소는 해당 상품이 <span class='red bold'>결제확인</span>일 때 신청 가능하다.<?php }?>
		</td>
	</tr>
	</table>
<?php }?>
<script>
$(".ableStockStepImg").hide();
$(".ableStockStep<?php echo $TPL_VAR["ableStockStep"]?>").show();//$("#ableStockStep<?php echo $TPL_VAR["ableStockStep"]?>").show();


$(".buyConfirmUseImg").hide();
$("#buyConfirmUse").hide();
$(".buyConfirmUse<?php echo $TPL_VAR["buy_confirm_use"]?>").show();


$("#buyorderautodv1").hide();
$("#buyorderautodv<?php echo $TPL_VAR["config_invoice"]?>").show();

$(".cancelDisabledStep35Img").hide();
<?php if($TPL_VAR["cancelDisabledStep35"]){?>
$(".cancelDisabledStep35_1").show();//$("#cancelDisabledStep35_1").show();
<?php }else{?>
$(".cancelDisabledStep35_0").show();//$("#cancelDisabledStep35_0").show();
<?php }?>


<?php if($TPL_VAR["export_err_handling"]=='ignore'){?>
$(".export_err_handling_ignore").show();//$("#export_err_handling_ignore").show();
<?php }else{?>
$(".export_err_handling_ignore").hide();//$("#export_err_handling_ignore").hide();
<?php }?>

<?php if($TPL_VAR["runout"]=='stock'){?>
	$(".a_layer").html('재고 기준');
<?php }elseif($TPL_VAR["runout"]=='ableStock'){?>
	$(".a_layer").html('가용재고 기준');
<?php }elseif($TPL_VAR["runout"]=='unlimited'){?>
	$(".a_layer").html('재고 무관');
<?php }?>
$(".b_layer").html('<?php echo $TPL_VAR["cartDuration"]?>');
$(".c_layer").html('<?php echo $TPL_VAR["cancelDuration"]?>');
$("#f_layer").html('<?php echo $TPL_VAR["save_term"]?>');
</script>