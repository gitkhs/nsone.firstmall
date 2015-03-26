<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/order/catalog_ajax.html 000027280 */ 
$TPL_record_1=empty($TPL_VAR["record"])||!is_array($TPL_VAR["record"])?0:count($TPL_VAR["record"]);?>
<?php if(!$TPL_VAR["record"]&&$TPL_VAR["page"]== 1){?>
		<tr class="list-row">
			<td colspan="15" align="center">검색어가 없거나 검색 결과가 없습니다.</td>
		</tr>
<?php }else{?>
<?php if($TPL_record_1){foreach($TPL_VAR["record"] as $TPL_K1=>$TPL_V1){?>


<?php if($TPL_V1["end_step_cnt"]){?>
		<!-- 합계 : 시작 -->
		<tr class="list-end-row">
			<td colspan="15" class="list-end-row-td">
				<ul class="left-btns clearbox">
					<li>
						<select class="list-select custom-select-box-multi" name="select_<?php echo $TPL_V1["end_step"]?>"  rows="4">
						<option value="select" <?php if($TPL_VAR["stepBox"][$TPL_V1["end_step"]]=='select'){?>selected<?php }?>>전체선택</option>
						<option value="not-select" <?php if($TPL_VAR["stepBox"][$TPL_V1["end_step"]]=='not-select'){?>selected<?php }?>>선택안함</option>
						<option value="important" <?php if($TPL_VAR["stepBox"][$TPL_V1["end_step"]]=='important'){?>selected<?php }?>>별표선택</option>
						<option value="not-important" <?php if($TPL_VAR["stepBox"][$TPL_V1["end_step"]]=='not-important'){?>selected<?php }?>>별표없음</option>
						</select>
					</li>
					<li>
<?php if($TPL_V1["end_step"]=='15'){?>
						<span class="btn small green"><button name="order_deposit" id="<?php echo $TPL_V1["end_step"]?>">결제확인</button></span>
						<span class="btn small gray"><button name="cancel_order" id="<?php echo $TPL_V1["end_step"]?>">주문무효</button></span>
<?php }?>
<?php if(in_array($TPL_V1["end_step"],$TPL_VAR["able_step_action"]['goods_ready'])){?>
						<span class="btn small deepgreen"><button name="batch_goods_ready" id="<?php echo $TPL_V1["end_step"]?>">상품준비</button></span>
<?php }?>
<?php if(in_array($TPL_V1["end_step"],array('25','35'))){?>
						<span class="btn small red"><button name="goods_export" id="<?php echo $TPL_V1["end_step"]?>">주문별출고</button></span>
						<span class="btn small red"><button name="order_export" id="<?php echo $TPL_V1["end_step"]?>">상품별출고</button></span>
<?php }?>
<?php if($TPL_V1["end_step"]=='45'){?>
						<span class="btn small deepblue"><button name="complete_export" id="<?php echo $TPL_V1["end_step"]?>">출고완료</button></span>
<?php }?>
<?php if($TPL_V1["end_step"]=='50'){?>
						<span class="btn small red"><button name="order_export" id="<?php echo $TPL_V1["end_step"]?>">상품별출고</button></span>
<?php }?>
<?php if($TPL_V1["end_step"]=='55'){?>
						<span class="btn small violet"><button name="going_delivery" id="<?php echo $TPL_V1["end_step"]?>">배송중</button></span>
						<span class="btn small pink"><button name="complete_delivery" id="<?php echo $TPL_V1["end_step"]?>">배송완료</button></span>
<?php }?>
<?php if($TPL_V1["end_step"]=='65'){?>
						<span class="btn small pink"><button name="complete_delivery" id="<?php echo $TPL_V1["end_step"]?>">배송완료</button></span>
<?php }?>
<?php if(in_array($TPL_V1["end_step"],array('95','99'))){?>
						<span class="btn small"><button name="goods_temps" id="<?php echo $TPL_V1["end_step"]?>">삭제처리</button></span>
<?php }?>

						<img src="/admin/skin/default/images/common/btn_print_m_odr.gif" name="goods_print" id="<?php echo $TPL_V1["end_step"]?>" class="hand" align="absmiddle" />

						<span class="hand batch_reverse" id="<?php echo $TPL_V1["end_step"]?>">
<?php if($TPL_V1["end_step"]=='25'){?>
						<span class="helpicon" title="취소, 반품, 환불이 없는 무통장 주문건을 주문접수(미입금)로 되돌릴 수 있습니다."></span> '주문접수'로 되돌리기 <img src="/admin/skin/default/images/common/icon_arrow_back.gif" align="absmiddle" />
<?php }elseif($TPL_V1["end_step"]=='35'){?>
						<span class="helpicon" title="상품준비 중인 주문을 결제확인으로 되돌릴 수 있습니다."></span> '결제확인'로 되돌리기 <img src="/admin/skin/default/images/common/icon_arrow_back.gif" align="absmiddle" />
<?php }elseif($TPL_V1["end_step"]=='95'){?>
						<span class="helpicon" title="주문이 무효된 주문을 다시 주문접수로 되돌릴 수 있습니다."></span> '주문접수'로 되돌리기 <img src="/admin/skin/default/images/common/icon_arrow_back.gif" align="absmiddle" />
<?php }?>
						</span>
					</li>
				</ul>
				<div class="list-end-total-amount">
					<span class="order-step-color-<?php echo $TPL_V1["end_step"]?>"><?php echo $TPL_V1["end_mstep"]?></span> <span class="darkgray">합계</span> &nbsp; <?php echo number_format($TPL_V1["end_step_cnt"])?>건
					&nbsp;&nbsp;&nbsp;
					￦ <span class="fx14 order-step-color-<?php echo $TPL_V1["end_step"]?>"><?php echo number_format($TPL_V1["end_step_settleprice"])?></span>
				</div>
			</td>
		</tr>
		<!-- 합계 : 끝 -->
<?php }?>


<?php if($TPL_V1["start_step"]){?>
		<!-- 리스트타이틀(주문상태 및 버튼) : 시작 -->
		<tr class="list-title-row">
			<td colspan="15" class="list-title-row-td list-title-row-td-step-<?php echo $TPL_V1["step"]?>">
				<div class="relative">
<?php if($TPL_V1["step"]== 15){?>
					<div class="ltr-title ltr-title-step-<?php echo $TPL_V1["step"]?>">
<?php }else{?>
					<div class="ltr-title ltr-title-step-<?php echo $TPL_V1["step"]?>">
<?php }?>
					<div class="btn-open-all"><img src="/admin/skin/default/images/common/icon/btn_open_all.gif" class="btn_open_all" id="<?php echo $TPL_V1["step"]?>" /></div>
<?php if($TPL_V1["step"]== 15){?>					
					<span class="step_title">(출고 전)</span>주문접수
					<span class="helpicon" title="접수된 주문의 입금을 확인하세요"></span>					
<?php }elseif($TPL_V1["step"]== 25){?>					
					<span class="step_title">(출고 전)</span>결제확인
					<span class="helpicon" title="결제가 확인된 주문의 상품을 출고하세요"></span>					
<?php }elseif($TPL_V1["step"]== 35){?>					
					<span class="step_title">(출고 전)</span>상품준비
					<span class="helpicon" title="보내지 못했던 상품의 재고가 확보되셨다면 상품을 출고하세요"></span>					
<?php }elseif($TPL_V1["step"]== 40){?>					
					<span class="step_title">(출고 전)</span>부분 출고준비
					<span class="helpicon" title="보내지 못했던 상품의 재고가 확보되셨다면 상품을 출고하세요"></span>
					<a href='../export/catalog?export_status[45]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 45){?>					
					<span class="step_title">(출고 전)</span> 출고준비
					<span class="helpicon" title="출고리스트에서 출고완료를 처리하세요. 출고수량만큼 재고가 자동 차감됩니다"></span>
					<a href='../export/catalog?export_status[45]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 50){?>					
					<span class="step_title">(출고 후)</span>
					부분 출고완료 <span class="helpicon" title="보내지 못했던 상품의 재고가 확보되셨다면 상품을 출고하세요"></span>
					<a href='../export/catalog?export_status[55]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 55){?>					
					<span class="step_title">(출고 후)</span>출고완료
					<span class="helpicon" title="출고리스트에서 배송완료를 처리하세요."></span>
					<a href='../export/catalog?export_status[55]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 60){?>					
					<span class="step_title">(출고 후)</span>부분 배송 중
					<span class="helpicon" title="보내지 못했던 상품의 재고가 확보되셨다면 상품을 출고하세요"></span>
					<a href='../export/catalog?export_status[65]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 65){?>					
					<span class="step_title">(출고 후)</span>배송 중
					<span class="helpicon" title="출고리스트에서 배송완료를 처리하세요."></span>
					<a href='../export/catalog?export_status[65]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 70){?>					
					<span class="step_title">(출고 후)</span>부분 배송완료
					<span class="helpicon" title="보내지 못했던 상품의 재고가 확보되셨다면 상품을 출고하세요"></span>
					<a href='../export/catalog?export_status[75]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 75){?>					
					<span class="step_title">(출고 후)</span>배송완료
					<span class="helpicon" title="배송이 완료되었습니다."></span>
					<a href='../export/catalog?export_status[75]=1'><span class="export-list"><span class="hide">출고리스트</span></span></a>					
<?php }elseif($TPL_V1["step"]== 85){?>					
					<span class="step_title">(출고 전)</span>결제취소(전체)
					<span class="helpicon" title="결제를 취소한 주문입니다. 환불리스트에서 환불을 처리하세요."></span>					
<?php }elseif($TPL_V1["step"]== 95){?>					
					<span class="step_title">(출고 전)</span>주문무효
					<span class="helpicon" title="입금이 안되어 무효 처리된 주문입니다"></span>					
<?php }elseif($TPL_V1["step"]== 99){?>					
					<span class="step_title">(출고 전)</span>결제실패
					<span class="helpicon" title="주문할 때 오류가 발생한 주문입니다"></span>					
<?php }?>
					</div>

					<ul class="left-btns clearbox">
						<li>
							<select class="list-select custom-select-box-multi" name="select_<?php echo $TPL_V1["step"]?>"  rows="4">
							<option value="select">전체선택</option>
							<option value="not-select">선택안함</option>
							<option value="important">별표선택</option>
							<option value="not-important">별표없음</option>
							</select>
						</li>
						<li>
<?php if($TPL_V1["step"]=='15'){?>
							<span class="btn small green"><button name="order_deposit" id="<?php echo $TPL_V1["step"]?>">결제확인</button></span>
							<span class="btn small gray"><button name="cancel_order" id="<?php echo $TPL_V1["step"]?>">주문무효</button></span>
<?php }?>
<?php if(in_array($TPL_V1["step"],$TPL_VAR["able_step_action"]['goods_ready'])){?>
							<span class="btn small deepgreen"><button name="batch_goods_ready" id="<?php echo $TPL_V1["step"]?>">상품준비</button></span>
<?php }?>
<?php if(in_array($TPL_V1["step"],array('25','35'))){?>
							<span class="btn small red"><button name="goods_export" id="<?php echo $TPL_V1["step"]?>">주문별출고</button></span>
							<span class="btn small red"><button name="order_export" id="<?php echo $TPL_V1["step"]?>">상품별출고</button></span>
<?php }?>
<?php if($TPL_V1["step"]=='45'){?>
							<span class="btn small deepblue"><button name="complete_export" id="<?php echo $TPL_V1["step"]?>">출고완료</button></span>
<?php }?>
<?php if($TPL_V1["step"]=='50'){?>
							<span class="btn small red"><button name="order_export" id="<?php echo $TPL_V1["step"]?>">상품별출고</button></span>
<?php }?>
<?php if($TPL_V1["step"]=='55'){?>
							<span class="btn small violet"><button name="going_delivery" id="<?php echo $TPL_V1["step"]?>">배송중</button></span>
							<span class="btn small pink"><button name="complete_delivery" id="<?php echo $TPL_V1["step"]?>">배송완료</button></span>
<?php }?>
<?php if($TPL_V1["step"]=='65'){?>
							<span class="btn small pink"><button name="complete_delivery" id="<?php echo $TPL_V1["step"]?>">배송완료</button></span>
<?php }?>
<?php if(in_array($TPL_V1["step"],array('95','99'))){?>
							<span class="btn small"><button name="goods_temps" id="<?php echo $TPL_V1["step"]?>">삭제처리</button></span>
<?php }?>

							<img src="/admin/skin/default/images/common/btn_print_m_odr.gif" name="goods_print" id="<?php echo $TPL_V1["step"]?>" class="hand" align="absmiddle" />

						</li>
					</ul>

					<!-- EXCEL -->
					<ul class="right-btns clearbox">
						<li>
							<select class="custom-select-box" id="select_down_<?php echo $TPL_V1["step"]?>">
								<option value="">양식선택</option>
<?php if(is_array($TPL_R2=$TPL_V1["loop"])&&!empty($TPL_R2)){foreach($TPL_R2 as $TPL_V2){?>
								<option value="<?php echo $TPL_V2["seq"]?>"><?php echo $TPL_V2["name"]?></option>
<?php }}?>
							</select>

							<span class="btn small"><button name="excel_down" step="<?php echo $TPL_V1["step"]?>"><img src="/admin/skin/default/images/common/btn_img_ex.gif" align="absmiddle" /> 일괄다운로드(선택)</button></span>
							<span class="btn small"><button name="excel_down_all" step="<?php echo $TPL_V1["step"]?>"><img src="/admin/skin/default/images/common/btn_img_ex.gif" align="absmiddle" /> 일괄다운로드(전체)</button></span>
<?php if($TPL_V1["step"]< 45&&$TPL_V1["step"]> 15){?>
							<span class="btn small"><button name="excel_upload"><img src="/admin/skin/default/images/common/btn_img_ex.gif" align="absmiddle" /> 일괄업로드(송장)</button></span>
<?php }?>
						</li>
					</ul>
					
				</div>
			</td>
		</tr>
		<!-- 리스트타이틀(주문상태 및 버튼) : 끝 -->
<?php }?>


		<tr class="list-row step<?php echo $TPL_V1["step"]?> important_<?php echo $TPL_V1["order_seq"]?> <?php if($TPL_V1["thischeck"]){?>checked-tr-background<?php }?>">
			<td align="center"><input type="checkbox" name="order_seq[]" value="<?php echo $TPL_V1["order_seq"]?>" <?php if($TPL_V1["thischeck"]){?>checked<?php }?> /></td>
			<td align="center">
<?php if($TPL_V1["important"]){?>
			<span class="icon-star-gray hand checked list-important important-<?php echo $TPL_V1["step"]?>" id="important_<?php echo $TPL_V1["order_seq"]?>"></span>
<?php }else{?>
			<span class="icon-star-gray hand list-important important-<?php echo $TPL_V1["step"]?>" id="important_<?php echo $TPL_V1["order_seq"]?>"></span>
<?php }?>
			</td>
			<td align="center" class="ft11"><?php echo $TPL_V1["no"]?></td>
			<td align="center">
<?php if($TPL_V1["linkage_mall_code"]){?>
<?php if(is_numeric(mb_substr($TPL_VAR["linkage_mallnames"][$TPL_V1["linkage_mall_code"]], 0, 1))){?>
					<?php echo mb_substr($TPL_VAR["linkage_mallnames"][$TPL_V1["linkage_mall_code"]], 0, 2)?>

<?php }else{?>
					<?php echo mb_substr($TPL_VAR["linkage_mallnames"][$TPL_V1["linkage_mall_code"]], 0, 1)?>

<?php }?>
<?php }else{?>
<?php if($TPL_V1["referer"]){?><a href="<?php echo $TPL_V1["referer"]?>" target="_blank"><?php }?>
				<span class="help" title="<?php echo $TPL_V1["referer_name"]?> <?php echo $TPL_V1["referer"]?>" style="font-size:11px;font-weight:bold;color:#006666;"><?php echo getstrcut($TPL_V1["referer_name"], 1,'')?></span>
<?php if($TPL_V1["referer"]){?></a><?php }?>
<?php }?>
			</td>
			<td align="center" class="ft11"><?php echo substr($TPL_V1["regist_date"], 2, - 3)?></td>
			<td align="center" class="ft11">
				<?php echo $TPL_V1["sitetypetitle"]?>

<?php if($TPL_V1["marketplacetitle"]&&$TPL_V1["marketplace"]!='etc'){?><span style="display:inline-block;"><?php echo $TPL_V1["marketplacetitle"]?></span><?php }?>
<?php if($TPL_V1["orign_order_seq"]){?><span title="맞교환 주문"><img src="/admin/skin/default/images/design/icon_order_exchange.gif" /></span><?php }?>
<?php if($TPL_V1["admin_order"]){?><img src="/admin/skin/default/images/design/icon_order_admin.gif"><?php }?>
<?php if($TPL_V1["person_seq"]){?><img src="/admin/skin/default/images/design/icon_order_personal.gif"><?php }?>
			</td>
			<td align="left" class="ft11">
				<a href="view?no=<?php echo $TPL_V1["order_seq"]?>"><span class="order-step-color-<?php echo $TPL_V1["step"]?> bold"><?php echo $TPL_V1["order_seq"]?></span></a>

				<a href="javascript:printOrderView('<?php echo $TPL_V1["order_seq"]?>')"><span class="icon-print-order"></span></a>


				<a href="view?no=<?php echo $TPL_V1["order_seq"]?>" target="_blank"><span class="btn-administration"><span class="hide">새창</span></span></a>
				<span class="btn-direct-open"><span class="hide">바로열기</span></span>
				
<?php if($TPL_V1["linkage_mall_order_id"]){?>
				<div class="blue bold"><?php echo $TPL_V1["linkage_mall_order_id"]?></div>
<?php }?>
			</td>
			<td align="left">			
			<div class="goods_name"><?php if($TPL_V1["gift_cnt"]> 0){?><span title="사은품 주문"><img src="/admin/skin/default/images/design/icon_order_gift.gif" align="absmiddle"/></span><?php }?> <?php echo $TPL_V1["goods_name"]?></div>
			</td>
			<td class="right">			
			<?php echo $TPL_V1["tot_ea"]?>(<?php echo $TPL_V1["item_cnt"]?>종)
			</td>
			<td align="center" class="ft11">
<?php if($TPL_V1["step"]>= 40&&$TPL_V1["step"]<= 75){?>
			<a href="../export/catalog?hsb_kind=export&header_search_keyword=<?php echo $TPL_V1["order_seq"]?>" target="_blank"><span class="order-step-color-<?php echo $TPL_V1["step"]?> hand">출고☞</span></a>
<?php }?>
			</td>
			<td class="ft11">
<?php if($TPL_V1["shipping_cnt"]> 1||$TPL_V1["shipping_recipient_user_name"]!=$TPL_V1["order_user_name"]){?>
					<div style="margin-top:5px;"><?php echo $TPL_V1["shipping_recipient_user_name"]?> <?php if($TPL_V1["shipping_cnt"]> 1){?>외 <?php echo ($TPL_V1["shipping_cnt"]- 1)?>명<?php }?></div> 
<?php }?>
				
					<div style="margin-bottom:3px;">
<?php if($TPL_V1["member_seq"]){?>
<?php if($TPL_V1["member_type"]=='개인'){?><img src="/admin/skin/default/images/common/icon/icon_personal.gif" vspace="0" align="absmiddle" />
<?php }elseif($TPL_V1["member_type"]=='기업'){?><img src="/admin/skin/default/images/common/icon/icon_besiness.gif" vspace="0" align="absmiddle" /><?php }?>
					<span><?php echo $TPL_V1["order_user_name"]?></span>
<?php if($TPL_V1["sns_rute"]){?>
						<span>(<img src="/admin/skin/default/images/sns/sns_<?php echo substr($TPL_V1["sns_rute"], 0, 1)?>0.gif" align="absmiddle" snscd="<?php echo $TPL_V1["sns_rute"]?>" mem_seq="<?php echo $TPL_V1["member_seq"]?>" no="<?php echo $TPL_V1["step"]?><?php echo $TPL_K1?>" onclick="snsdetailview('open','<?php echo $TPL_V1["sns_rute"]?>','<?php echo $TPL_V1["member_seq"]?>','<?php echo $TPL_V1["step"]?><?php echo $TPL_K1?>')" class="btnsnsdetail hand">/<span class="blue"><?php echo $TPL_V1["group_name"]?></span>)
						<div id="snsdetailPopup<?php echo $TPL_V1["step"]?><?php echo $TPL_K1?>" class="snsdetailPopup absolute hide" style="margin-left:73px;margin-top:-16px;"></div>
						</span>
<?php }else{?>
						(<a href="/admin/member/detail?member_seq=<?php echo $TPL_V1["member_seq"]?>" target="_blank"><span style="color:#d13b00;"><?php echo $TPL_V1["userid"]?></span>/<span class="blue"><?php echo $TPL_V1["group_name"]?></span></a>)
<?php }?>
<?php }else{?>
					<img src="/admin/skin/default/images/common/icon/icon_personal.gif" /> <?php echo $TPL_V1["order_user_name"]?> (<span class="desc">비회원</span>)
<?php }?>
					</div>
				
			</td>
			
			<!--// 결제 수단 //-->
			<td align="right" class="ft11">
<?php if($TPL_V1["payment"]=='bank'){?>
<?php if($TPL_V1["order_user_name"]==$TPL_V1["depositor"]){?>
				<span class="darkgray"><span title="입금자명"><?php echo $TPL_V1["depositor"]?></span></span>
<?php }else{?>
				<span class="blue"><span title="입금자명"><?php echo $TPL_V1["depositor"]?></span></span>
<?php }?>
<?php }?>
<?php if($TPL_V1["payment"]=='escrow_account'){?>
			<span class="icon-pay-escrow"><span>에스크로</span></span>
			<span class="icon-pay-account"><span><?php echo $TPL_V1["mpayment"]?></span></span>
<?php }elseif($TPL_V1["payment"]=='escrow_virtual'){?>
			<span class="icon-pay-escrow"><span>에스크로</span></span>
			<span class="icon-pay-virtual"><span><?php echo $TPL_V1["mpayment"]?></span></span>
<?php }elseif($TPL_V1["pg"]=='kakaopay'){?>
			<span class="icon-pay-<?php echo $TPL_V1["pg"]?>"><span><?php echo $TPL_V1["pg"]?></span></span>
<?php }else{?>
			<span class="icon-pay-<?php echo $TPL_V1["payment"]?>"><span><?php echo $TPL_V1["mpayment"]?></span></span>
<?php }?>
<?php if($TPL_V1["payment"]=='bank'&&$TPL_V1["bank_name"]){?>
			<span class="darkgray"><span title="은행명"><?php echo $TPL_V1["bank_name"]?></span></span>
<?php }?>
			</td>
			<td align="right" style="padding-right:5px;"><b><?php echo number_format($TPL_V1["settleprice"])?></b></td>
			<td align="center" class="ft11"><?php if($TPL_V1["deposit_date"]){?><?php echo substr($TPL_V1["deposit_date"], 2, - 3)?><?php }?></td>
			<td align="center" class="ft11">
			<div><?php echo $TPL_V1["mstep"]?></div>
<?php if($TPL_V1["cancel_list_ea"]||$TPL_V1["exchange_list_ea"]||$TPL_V1["return_list_ea"]||$TPL_V1["refund_list_ea"]){?>
			<div>
<?php if($TPL_V1["cancel_list_ea"]){?>
				<a href="/admin/refund/catalog?keyword=<?php echo $TPL_V1["order_seq"]?>" target="_blank"><img src='/admin/skin/default/images/common/icon/icon_list_cancel.gif' align="absmiddle"><span style="font-size:11px;color:#ea3b91"><?php echo $TPL_V1["cancel_list_ea"]?></span></a>
<?php }?>
<?php if($TPL_V1["exchange_list_ea"]){?>
				<a href="/admin/returns/catalog?keyword=<?php echo $TPL_V1["order_seq"]?>" target="_blank"><img src='/admin/skin/default/images/common/icon/icon_list_return_exchange.gif' align="absmiddle"><span style="font-size:11px;color:#ea3b91"><?php echo $TPL_V1["exchange_list_ea"]?></span></a>
<?php }?>
<?php if($TPL_V1["return_list_ea"]){?>
				<a href="/admin/returns/catalog?keyword=<?php echo $TPL_V1["order_seq"]?>" target="_blank"><img src='/admin/skin/default/images/common/icon/icon_list_return.gif' align="absmiddle"><span style="font-size:11px;color:#ea3b91"><?php echo $TPL_V1["return_list_ea"]?></span></a>
<?php }?>
<?php if($TPL_V1["refund_list_ea"]){?>
				<a href="/admin/refund/catalog?keyword=<?php echo $TPL_V1["order_seq"]?>" target="_blank"><img src='/admin/skin/default/images/common/icon/icon_list_refund.gif' align="absmiddle"><span style="font-size:11px;color:#ea3b91"><?php echo $TPL_V1["refund_list_ea"]?></span></a>
<?php }?>
			</div>			
<?php }?>
			</td>
		</tr>
		<!--<tr><td colspan="12" style="padding-top:3px;"></td></tr>-->
		<tr class="order-list-summary-row hide">
			<td colspan="15" class="order-list-summary-row-td"><div class="order_info"></div></td>
		</tr>
		<!-- 리스트데이터 : 끝 -->


<?php if($TPL_V1["last_step_cnt"]){?>
		<!-- 합계 : 시작 -->
		<tr class="list-end-row">
			<td colspan="15" class="list-end-row-td">
				<ul class="left-btns clearbox">
					<li>
						<select class="list-select custom-select-box-multi" name="select_<?php echo $TPL_V1["last_step"]?>"  rows="4">
						<option value="select" <?php if($TPL_VAR["stepBox"][$TPL_V1["last_step"]]=='select'){?>selected<?php }?>>전체선택</option>
						<option value="not-select" <?php if($TPL_VAR["stepBox"][$TPL_V1["last_step"]]=='not-select'){?>selected<?php }?>>선택안함</option>
						<option value="important" <?php if($TPL_VAR["stepBox"][$TPL_V1["last_step"]]=='important'){?>selected<?php }?>>별표선택</option>
						<option value="not-important" <?php if($TPL_VAR["stepBox"][$TPL_V1["last_step"]]=='not-important'){?>selected<?php }?>>별표없음</option>
						</select>
					</li>
					<li>
<?php if($TPL_V1["last_step"]=='15'){?>
						<span class="btn small green"><button name="order_deposit" id="<?php echo $TPL_V1["last_step"]?>">결제확인</button></span>
						<span class="btn small gray"><button name="cancel_order" id="<?php echo $TPL_V1["last_step"]?>">주문무효</button></span>
<?php }?>
<?php if(in_array($TPL_V1["last_step"],$TPL_VAR["able_step_action"]['goods_ready'])){?>
						<span class="btn small deepgreen"><button name="batch_goods_ready" id="<?php echo $TPL_V1["last_step"]?>">상품준비</button></span>
<?php }?>
<?php if(in_array($TPL_V1["last_step"],array('25','35'))){?>
						<span class="btn small red"><button name="goods_export" id="<?php echo $TPL_V1["step"]?>">주문별출고</button></span>
						<span class="btn small red"><button name="order_export" id="<?php echo $TPL_V1["step"]?>">상품별출고</button></span>
<?php }?>
<?php if($TPL_V1["last_step"]=='45'){?>
						<span class="btn small deepblue"><button name="complete_export" id="<?php echo $TPL_V1["last_step"]?>">출고완료</button></span>
<?php }?>
<?php if($TPL_V1["last_step"]=='50'){?>
						<span class="btn small red"><button name="order_export" id="<?php echo $TPL_V1["step"]?>">상품별출고</button></span>
<?php }?>
<?php if($TPL_V1["last_step"]=='55'){?>
						<span class="btn small violet"><button name="going_delivery" id="<?php echo $TPL_V1["last_step"]?>">배송중</button></span>
						<span class="btn small pink"><button name="complete_delivery" id="<?php echo $TPL_V1["last_step"]?>">배송완료</button></span>
<?php }?>
<?php if($TPL_V1["last_step"]=='65'){?>
						<span class="btn small pink"><button name="complete_delivery" id="<?php echo $TPL_V1["last_step"]?>">배송완료</button></span>
<?php }?>
<?php if(in_array($TPL_V1["last_step"],array('95','99'))){?>
						<span class="btn small"><button name="goods_temps" id="<?php echo $TPL_V1["last_step"]?>">삭제처리</button></span>
<?php }?>

						<img src="/admin/skin/default/images/common/btn_print_m_odr.gif" name="goods_print" id="<?php echo $TPL_V1["last_step"]?>" class="hand" align="absmiddle" />

						<span class="hand batch_reverse" id="<?php echo $TPL_V1["last_step"]?>">
<?php if($TPL_V1["last_step"]=='25'){?>
						<span class="helpicon" title="취소, 반품, 환불이 없는 무통장 주문건을 주문접수(미입금)로 되돌릴 수 있습니다."></span> '주문접수'로 되돌리기 <img src="/admin/skin/default/images/common/icon_arrow_back.gif" align="absmiddle" />
<?php }elseif($TPL_V1["last_step"]=='35'){?>
						<span class="helpicon" title="상품준비 중인 주문을 결제확인으로 되돌릴 수 있습니다."></span> '결제확인'로 되돌리기 <img src="/admin/skin/default/images/common/icon_arrow_back.gif" align="absmiddle" />
<?php }elseif($TPL_V1["last_step"]=='95'){?>
						<span class="helpicon" title="주문이 무효된 주문을 다시 주문접수로 되돌릴 수 있습니다."></span> '주문접수'로 되돌리기 <img src="/admin/skin/default/images/common/icon_arrow_back.gif" align="absmiddle" />
<?php }?>
						</span>
					</li>
				</ul>
				<div class="list-end-total-amount">
					<span class="order-step-color-<?php echo $TPL_V1["last_step"]?>"><?php echo $TPL_V1["mstep"]?></span> <span class="darkgray">합계</span> &nbsp; <?php echo number_format($TPL_V1["last_step_cnt"])?>건
					&nbsp;&nbsp;&nbsp;
					￦ <span class="fx14 order-step-color-<?php echo $TPL_V1["last_step"]?>"><?php echo number_format($TPL_V1["last_step_settleprice"])?></span>
				</div>
			</td>
		</tr>
		<!-- 합계 : 끝 -->
<?php }?>

<?php }}?>
<?php }?>
		<input type="hidden" id="<?php echo $TPL_VAR["page"]?>_no" value="<?php echo $TPL_VAR["final_no"]?>" />
		<input type="hidden" id="<?php echo $TPL_VAR["page"]?>_step" value="<?php echo $TPL_VAR["final_step"]?>" />