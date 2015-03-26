<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/inicis.html 000024714 */ 
$TPL_payment_1=empty($TPL_VAR["payment"])||!is_array($TPL_VAR["payment"])?0:count($TPL_VAR["payment"]);
$TPL_escrow_1=empty($TPL_VAR["escrow"])||!is_array($TPL_VAR["escrow"])?0:count($TPL_VAR["escrow"]);
$TPL_mobilePayment_1=empty($TPL_VAR["mobilePayment"])||!is_array($TPL_VAR["mobilePayment"])?0:count($TPL_VAR["mobilePayment"]);
$TPL_mobileEscrow_1=empty($TPL_VAR["mobileEscrow"])||!is_array($TPL_VAR["mobileEscrow"])?0:count($TPL_VAR["mobileEscrow"]);
$TPL_pcCardCompanyCode_1=empty($TPL_VAR["pcCardCompanyCode"])||!is_array($TPL_VAR["pcCardCompanyCode"])?0:count($TPL_VAR["pcCardCompanyCode"]);
$TPL_mobileCardCompanyCode_1=empty($TPL_VAR["mobileCardCompanyCode"])||!is_array($TPL_VAR["mobileCardCompanyCode"])?0:count($TPL_VAR["mobileCardCompanyCode"]);
$TPL_arrInicisCardCompany_1=empty($TPL_VAR["arrInicisCardCompany"])||!is_array($TPL_VAR["arrInicisCardCompany"])?0:count($TPL_VAR["arrInicisCardCompany"]);?>
<script type="text/javascript">
var onInterestSettingButtonIndex = 0;


/* 모바일/pc플랫폼 테이블간의 높의 조절 */
function sync_height(){
	$("div.inputPgSetting table.info-table-style").eq(1).height( $("div.inputPgSetting table.info-table-style").eq(0).height() );
}

$(document).ready(function() {
	/* 무이자 할부기간 선택팝업 */
	$("button.nonInterestSettingButton").each(function(idx){
		$(this).bind("click",function(){
			onInterestSettingButtonIndex = idx;
			openDialog("무이자할부 <span class='desc'>무이자할부 정보를 설정합니다.</span>", "nonInterestPopup", {"width":"390","height":200});
		});
	});

	/* 무이자 할부기간 삭제 */
	$("button.nonInterestSettingDelete").live("click",function(){
		$(this).parent().parent().remove();
	});

	/* 무이자 할부기간 추가 */
	$("button#nonInterestAddButton").bind("click",function(){
		var arrPlaform = new Array();
		arrPlaform[0] = "pc";
		arrPlaform[1] = "mobile";
		var codeName = arrPlaform[onInterestSettingButtonIndex] + "CardCompanyCode[]";
		var termsName =  arrPlaform[onInterestSettingButtonIndex] + "CardCompanyTerms[]";
		var opt_text = $("select[name=cardCompany] option:selected").html();
		var opt_value = $("select[name=cardCompany] option:selected").val();
		var mons = "";
		$("input[type='checkbox'][name='nonInterestTerms[]']:checked").each(function(){
			 mons += ',' + $(this).val();
		});
		if(mons == "") return false;
		$("input[name='"+codeName+"']").each(function(){
			if($(this).val() == opt_value) $(this).parent().remove();
		});
		mons = mons.substring(1);
		var tag = '<div> - ' + opt_text + ' ' + mons + " 개월";
		tag += '<input type="hidden" name="' + codeName + '" value="' + opt_value + '">';
		tag += '<input type="hidden" name="' + termsName + '" value="' + mons + '">';
		tag += ' <span class="btn small gray"><button type="button" class="nonInterestSettingDelete">삭제<span class="arrowright"></span></button></span></div>';
		$("button.nonInterestSettingButton").eq(onInterestSettingButtonIndex).parent().next().next().append(tag);
		sync_height();
	});

	/* 세팅값 출력 */
<?php if($TPL_payment_1){foreach($TPL_VAR["payment"] as $TPL_V1){?>
	$("input[name='payment[]'][value='<?php echo $TPL_V1?>']").attr('checked',true);
<?php }}?>
<?php if($TPL_escrow_1){foreach($TPL_VAR["escrow"] as $TPL_V1){?>
	$("input[name='escrow[]'][value='<?php echo $TPL_V1?>']").attr('checked',true);
<?php }}?>
<?php if($TPL_VAR["interestTerms"]!=''){?>
	$("select[name='interestTerms'] option[value='<?php echo $TPL_VAR["interestTerms"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["nonInterestTerms"]!=''){?>
	$("select[name='nonInterestTerms'] option[value='<?php echo $TPL_VAR["nonInterestTerms"]?>']").attr('selected',true);
<?php }?>
	$("input[name='cashReceipts'][value='<?php echo $TPL_VAR["cashReceipts"]?>']").attr('checked',true);
<?php if($TPL_mobilePayment_1){foreach($TPL_VAR["mobilePayment"] as $TPL_V1){?>
	$("input[name='mobilePayment[]'][value='<?php echo $TPL_V1?>']").attr('checked',true);
<?php }}?>
<?php if($TPL_mobileEscrow_1){foreach($TPL_VAR["mobileEscrow"] as $TPL_V1){?>
<?php if($TPL_V1!='account'){?>
	$("input[name='mobileEscrow[]'][value='<?php echo $TPL_V1?>']").attr('checked',true);
<?php }?>
<?php }}?>
<?php if($TPL_VAR["mobileInterestTerms"]!=''){?>
	$("select[name='mobileInterestTerms'] option[value='<?php echo $TPL_VAR["mobileInterestTerms"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["mobileNonInterestTerms"]!=''){?>
	$("select[name='mobileNonInterestTerms'] option[value='<?php echo $TPL_VAR["mobileNonInterestTerms"]?>']").attr('selected',true);
<?php }?>
	$("input[name='mobileCashReceipts'][value='<?php echo $TPL_VAR["mobileCashReceipts"]?>']").attr('checked',true);

	$("select[name='nonInterestTerms']").live("change",function(){
		check_nonInterest();
	});

	check_nonInterest();

	$("select[name='mobileNonInterestTerms']").live("change",function(){
		check_nonInterest_mobile();
	});

	check_nonInterest_mobile();

	$("input[name='payment[]'],input[name='mobilePayment[]'],input[name='escrow[]'],input[name='mobileEscrow[]']").bind("change",function(){
		check_use_payment();
	});

	check_use_payment();

	/* 인풋박스 타이틀 표기 */
	setDefaultText();
	/* 인풋파일 스타일 적용 */
	changeFileStyle();
	/* 모바일/pc플랫폼 테이블간의 높의 조절 */
	sync_height();

	$("input[name='virtual_info'][value='"+$("select[name='pgCompany']").val()+"']").attr("checked",true);
});

function check_use_payment(){
	$("input[name='payment[]'],input[name='mobilePayment[]'],input[name='escrow[]'],input[name='mobileEscrow[]']").each(function(){
		if( !$(this).is(":checked") ){
			$(this).closest("td").find("input,select").not(this).attr('disabled',true);
		}else{
			$(this).closest("td").find("input,select").not(this).removeAttr('disabled');
		}
	});
}

function check_nonInterest()
{
	$("select[name='nonInterestTerms'] option").each(function(){
		if( $(this).attr('selected') ){
			if( $(this).val() == 'automatic' ){
				$(this).parent().next().hide().next().find("span:eq(0)").show();
				$(this).parent().next().next().find("span:eq(1)").hide();
				$(this).parent().next().next().next().hide();
			}else{
				$(this).parent().next().show().next().find("span:eq(1)").show();
				$(this).parent().next().next().find("span:eq(0)").hide();
				$(this).parent().next().next().next().show();
			}
		}
	});
}

function check_nonInterest_mobile()
{
	$("select[name='mobileNonInterestTerms'] option").each(function(){
		if( $(this).attr('selected') ){
			if( $(this).val() == 'automatic' ){
				$(this).parent().next().hide().next().find("span:eq(0)").show();
				$(this).parent().next().next().find("span:eq(1)").hide();
				$(this).parent().next().next().next().hide();
			}else{
				$(this).parent().next().show().next().find("span:eq(1)").show();
				$(this).parent().next().next().find("span:eq(0)").hide();
				$(this).parent().next().next().next().show();
			}
		}
	});
}

</script>
<div class="clearbox inputPgSetting">
	<div style="float:left;width:50%">
	<table width="100%" class="info-table-style">
		<col width="10%" /><col width="10%" /><col width="30%" />
		<tr>
			<th class="its-th center bold" colspan="3"  height="20">PC (Facebook쇼핑몰 포함)</th>
		</tr>
		<tr>
			<th class="its-th" rowspan="5">일반</th>
			<td class="its-td">세팅 정보 등록</td>
			<td class="its-td">
				<div><input type="text" name="mallCode" class="line" value="<?php echo $TPL_VAR["mallCode"]?>" title="상점 아이디" /></div>
				<div class="desc">
				상점 아이디는  <span class="bold black">GBF</span>로 시작하는 표준코드만 입력이 가능합니다.
				</div>
				<div><input type="text" name="merchantKey" class="line" value="<?php echo $TPL_VAR["merchantKey"]?>" title="키패스워드" /></div>
				<div style="padding-top:5px;"    <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>  <?php }?>>
<?php if($TPL_VAR["keypass"]){?>ok<?php }?>
					<input   type="file" name="keypass" size="15" <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> />
					<span class="desc">keypass.enc</span>
<?php if(!$TPL_VAR["keypass"]){?>
					<span class="fx11 red">파일 업로드</span>
<?php }?>
				</div>
				<div style="padding-top:5px;"   <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>  <?php }?>>
<?php if($TPL_VAR["mcert"]){?>ok<?php }?>
					<input type="file" name="mcert" size="15"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> />
					<span class="desc">mcert.pem</span>
<?php if(!$TPL_VAR["mcert"]){?>
					<span class="fx11 red">파일 업로드</span>
<?php }?>
				</div>
				<div style="padding-top:5px;"   <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>  <?php }?>>
<?php if($TPL_VAR["mpriv"]){?>ok<?php }?>
					<input type="file" name="mpriv" size="15"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> />
					<span class="desc">mpriv.pem</span>
<?php if(!$TPL_VAR["mpriv"]){?>
					<span class="fx11 red">파일 업로드</span>
<?php }?>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">신용카드</td>
			<td class="its-td">
				<div><label><input type="checkbox" name="payment[]" value="card" /> 사용</label></div>
				<div style="padding-top:5px;">
					할부기간 :
					<select name="interestTerms">
						<option value="0">일시불</option>
						<option value="2">2개월</option>
						<option value="3">3개월</option>
						<option value="4">4개월</option>
						<option value="5">5개월</option>
						<option value="6">6개월</option>
						<option value="7">7개월</option>
						<option value="8">8개월</option>
						<option value="9">9개월</option>
						<option value="10">10개월</option>
						<option value="11">11개월</option>
						<option value="12">12개월</option>
					</select>
					<span class="desc">할부가 가능한 최대기간을 선택</span>
				</div>
				<div style="padding-top:5px;">
					무이자 할부기간 :
					<select name="nonInterestTerms">
						<option value="automatic">자동</option>
						<option value="manual">수동</option>
					</select>
					<span class="btn small black">
					<button type="button" class="nonInterestSettingButton">설정하기<span class="arrowright"></span></button>
					</span>
					<div class="desc">
					<span class='red'>무이자할부 수수료를 PG사에서 부담 (권장)</span>
					<span class='red'>무이자할부 수수료를 쇼핑몰에서 부담(PG사와 협의)</span>
					</div>
					<div>
<?php if($TPL_pcCardCompanyCode_1){$TPL_I1=-1;foreach($TPL_VAR["pcCardCompanyCode"] as $TPL_V1){$TPL_I1++;?>
					<div>
					- <?php echo $TPL_VAR["arrCardCompany"][$TPL_V1]?> <?php echo $TPL_VAR["pcCardCompanyTerms"][$TPL_I1]?> 개월
					<input type="hidden" name="pcCardCompanyCode[]" value="<?php echo $TPL_V1?>">
					<input type="hidden" name="pcCardCompanyTerms[]" value="<?php echo $TPL_VAR["pcCardCompanyTerms"][$TPL_I1]?>">
					<span class="btn small gray"><button type="button" class="nonInterestSettingDelete">삭제<span class="arrowright"></span></button></span>
					</div>
<?php }}?>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">계좌이체</td>
			<td class="its-td"><label><input type="checkbox" name="payment[]" value="account" /> 사용</label></td>
		</tr>
		<tr>
			<td class="its-td">가상계좌</label></td>
			<td class="its-td">
				<div>
					<label>
						<input type="checkbox" name="payment[]" value="virtual" />
						사용
					</label>
					<span class="btn small orange"><button type="button" class="button_virtual_info">필독) 입금확인 URL 세팅 방법</button></span>
				</div>
				<div>
					<div class="desc">아래의 가상계좌 입금확인 URL을 반드시 세팅 하십시오.</div>
					<div class="desc">세팅하지 않으시면 자동으로 입금확인 되지 않습니다!</div>
					<div>http://<span class="red">쇼핑몰도메인입력</span>/payment/inicis_return</div>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">핸드폰</td>
			<td class="its-td">
				<label><input type="checkbox" name="payment[]" value="cellphone" /> 사용</label>
			</td>
		</tr>
		<tr>
			<th class="its-th" rowspan="4">에스크로
			<span class="helpicon" title="에스크로(구매대금예치)는 구매자를 보호하는 제도로써 소비자가 지불한 물품대금을<br/>에스크로사업자(은행 등 공신력있는 제3자)가 맡아 가지고 있다가 배송이 정상적으로<br/>완료되면 판매자 계좌로 입금하는 결제대금 예치제도입니다"></span>
			</th>
			<td class="its-td">세팅 정보 등록</td>
			<td class="its-td">
				<div><input type="text" name="escrowMallCode" class="line" value="<?php echo $TPL_VAR["escrowMallCode"]?>" title="상점 아이디" /></div>
				<div><input type="text" name="escrowMerchantKey"" class="line" value="<?php echo $TPL_VAR["escrowMerchantKey"]?>" title="키패스워드" /></div>
				<div style="padding-top:5px;"   <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>  <?php }?>>
<?php if($TPL_VAR["escrowKeypass"]){?>ok<?php }?>
					<input type="file" name="escrowKeypass" size="15"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> />
					<span class="desc">keypass.enc</span>
<?php if(!$TPL_VAR["escrowKeypass"]){?>
					<span class="fx11 red">파일 업로드</span>
<?php }?>
				</div>
				<div style="padding-top:5px;"   <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>  <?php }?>>
<?php if($TPL_VAR["escrowMcert"]){?>ok<?php }?>
					<input type="file" name="escrowMcert" size="15"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> />
					<span class="desc">mcert.pem</span>
<?php if(!$TPL_VAR["escrowMcert"]){?>
					<span class="fx11 red">파일 업로드</span>
<?php }?>
				</div>
				<div style="padding-top:5px;"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?>  <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?>  <?php }?> >
<?php if($TPL_VAR["escrowMpriv"]){?>ok<?php }?>
					<input type="file" name="escrowMpriv" size="15" <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemodisabled"]?> <?php }?> />
					<span class="desc">mpriv.pem</span>
<?php if(!$TPL_VAR["escrowMpriv"]){?>
					<span class="fx11 red">파일 업로드</span>
<?php }?>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">계좌이체</td>
			<td class="its-td">
				<div><label><input type="checkbox" name="escrow[]" value="account"  /> 사용</label></div>
				<div>구매자가 <input type="text" name="escrowAccountLimit" class="line onlynumber" size="6" value="<?php echo $TPL_VAR["escrowAccountLimit"]?>" />원 이상의 금액을 결제할 때 계좌이체 에스크로 결제수단을 선택할 수 있습니다.</div>
				<div class="desc bold" style="color:blue">※ 단, 이니시스 계좌이체 에스크로 사용은 이니시스(02-3430-5858)에 별도로 요청하셔야만 정상동작합니다.</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">가상계좌</td>
			<td class="its-td">
				<div><label><input type="checkbox" name="escrow[]" value="virtual" /> 사용</label></div>
				<div>구매자가 <input type="text" name="escrowVirtualLimit" class="line onlynumber" size="6" value="<?php echo $TPL_VAR["escrowVirtualLimit"]?>" />원 이상의 금액을 결제할 때 계좌이체 에스크로 결제수단을 선택할 수 있습니다.</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">
			에스크로<br/>인증마크
			<span class="helpicon" title="쇼핑몰 하단에 에스크로 인증마크를 노출함으로써 소비자에게<br/>쇼핑몰의 에스크로 여부를 정확하게 알릴 수 있습니다."></span>
			</td>
			<td class="its-td" valign="top">
				<a href="http://mark.inicis.com/certi2/certi.php" target="_blank">이니시스 인증센터(http://mark.inicis.com/certi2/certi.php)</a>에서 에스크로 인증마크를 발급 받으셔서 인증마크가 노출되어야 하는 영역에 삽입하십시요.
				<span class="desc hand" Onclick="javascript:window.open('inics_escrow_info','inics_escrow_info','scrollbars=no,resizable=no,width=739,height=771');">셈플보기▶</span>
			</td>
		</tr>

	</table>
	</div>
	<div style="float:left;width:50%" <?php if($TPL_VAR["service_code"]=='P_FAMM'){?>disabled="disabled"<?php }?>>
	<table width="100%" class="info-table-style inputPgSetting">
		<col width="10%" /><col width="10%" /><col width="30%" />
		<tr>
			<th class="its-th center bold" colspan="3" height="20">Mobile/Tablet (Facebook쇼핑몰 포함)</th>
		</tr>
		<tr>
			<th class="its-th" rowspan="5">일반</th>
			<td class="its-td">세팅 정보 등록</td>
			<td class="its-td" valign="middle" height="60">
				PC플랫폼과 동일
			</td>
		</tr>
		<tr>
			<td class="its-td">신용카드</td>
			<td class="its-td">
				<div><label><input type="checkbox" name="mobilePayment[]" value="card" /> 사용</label></div>
				<div style="padding-top:5px;">
					할부기간 :
					<select name="mobileInterestTerms">
						<option value="0">일시불</option>
						<option value="2">2개월</option>
						<option value="3">3개월</option>
						<option value="4">4개월</option>
						<option value="5">5개월</option>
						<option value="6">6개월</option>
						<option value="7">7개월</option>
						<option value="8">8개월</option>
						<option value="9">9개월</option>
						<option value="10">10개월</option>
						<option value="11">11개월</option>
						<option value="12">12개월</option>
					</select>
					<span class="desc">할부가 가능한 최대기간을 선택</span>
				</div>
				<div style="padding-top:5px;">
					무이자 할부기간 :
					<select name="mobileNonInterestTerms">
						<option value="automatic">자동</option>
						<option value="manual">수동</option>
					</select>
					<span class="btn small black">
					<button type="button" class="nonInterestSettingButton">설정하기<span class="arrowright"></span></button>
					</span>
					<div class="desc">
					<span class='red'>무이자할부 수수료를 PG사에서 부담 (권장)</span>
					<span class='red'>무이자할부 수수료를 쇼핑몰에서 부담(PG사와 협의)</span>
					</div>
					<div>
<?php if($TPL_mobileCardCompanyCode_1){$TPL_I1=-1;foreach($TPL_VAR["mobileCardCompanyCode"] as $TPL_V1){$TPL_I1++;?>
					<div>
					- <?php echo $TPL_VAR["arrCardCompany"][$TPL_V1]?> <?php echo $TPL_VAR["mobileCardCompanyTerms"][$TPL_I1]?> 개월
					<input type="hidden" name="mobileCardCompanyCode[]" value="<?php echo $TPL_V1?>">
					<input type="hidden" name="mobileCardCompanyTerms[]" value="<?php echo $TPL_VAR["mobileCardCompanyTerms"][$TPL_I1]?>">
					<span class="btn small gray"><button type="button" class="nonInterestSettingDelete">삭제<span class="arrowright"></span></button></span>
					</div>
<?php }}?>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">계좌이체</td>
			<td class="its-td"><label><input type="checkbox" name="mobilePayment[]" value="account" /> 사용</label></td>
		</tr>
		<tr>
			<td class="its-td">가상계좌</label></td>
			<td class="its-td">
				<div>
					<label>
						<input type="checkbox" name="mobilePayment[]" value="virtual" />
						사용
					</label>
					<span class="btn small orange"><button type="button" class="button_virtual_info">필독) 입금확인 URL 세팅 방법</button></span>
				</div>
				<div>
					<div class="desc">아래의 가상계좌 입금확인 URL을 반드시 세팅 하십시오.</div>
					<div class="desc">세팅하지 않으시면 자동으로 입금확인 되지 않습니다!</div>
					<div>http://<span class="red">쇼핑몰도메인입력</span>/payment/inicis_return</div>
				</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">핸드폰</td>
			<td class="its-td">
				<label><input type="checkbox" name="mobilePayment[]" value="cellphone" /> 사용</label>
			</td>
		</tr>
		<tr>
			<th class="its-th" rowspan="4">
			에스크로
			<span class="helpicon" title="에스크로(구매대금예치)는 구매자를 보호하는 제도로써 소비자가 지불한 물품대금을<br/>에스크로사업자(은행 등 공신력있는 제3자)가 맡아 가지고 있다가 배송이 정상적으로<br/>완료되면 판매자 계좌로 입금하는 결제대금 예치제도입니다"></span>
			</th>
			<td class="its-td">세팅 정보 등록</td>
			<td class="its-td" valign="middle" height="60">
				PC플랫폼과 동일
			</td>
		</tr>
		<tr>
			<td class="its-td">계좌이체</td>
			<td class="its-td">
				<div><label><input type="checkbox" name="mobileEscrow[]" value="account" disabled="disabled" /> 사용</label></div>
				<div>구매자가 <input type="text" name="mobileEscrowAccountLimit" class="line onlynumber" size="6" disabled="disabled" />원 이상의 금액을 결제할 때 계좌이체 에스크로 결제수단을 선택할 수 있습니다.</div>
			</td>
		</tr>

		<tr>
			<td class="its-td">가상계좌</td>
			<td class="its-td">
				<div><label><input type="checkbox" name="mobileEscrow[]" value="virtual" disabled="disabled"  /> 사용</label></div>
				<div>구매자가 <input type="text" name="mobileEscrowVirtualLimit" class="line onlynumber" size="6" disabled="disabled"  />원 이상의 금액을 결제할 때 계좌이체 에스크로 결제수단을 선택할 수 있습니다.</div>
			</td>
		</tr>
		<tr>
			<td class="its-td">
			에스크로<br/>인증마크
			<span class="helpicon" title="쇼핑몰 하단에 에스크로 인증마크를 노출함으로써 소비자에게<br/>쇼핑몰의 에스크로 여부를 정확하게 알릴 수 있습니다."></span>
			</td>
			<td class="its-td" valign="top">
				<a href="http://mark.inicis.com/certi2/certi.php" target="_blank">이니시스 인증센터(http://mark.inicis.com/certi2/certi.php)</a>에서 에스크로 인증마크를 발급 받으셔서 인증마크가 노출되어야 하는 영역에 삽입하십시요.
				<span class="desc hand" Onclick="javascript:window.open('inics_escrow_info','inics_escrow_info','scrollbars=no,resizable=no,width=739,height=771');">셈플보기▶</span>
			</td>
		</tr>
	</table>
	</div>
</div>
</form>
<div id="nonInterestPopup" style="display: none">
	<input type="hidden" name="nonInterestSettingButtonIndex" value="0" />
	<div>
	<select name="cardCompany">
<?php if($TPL_arrInicisCardCompany_1){foreach($TPL_VAR["arrInicisCardCompany"] as $TPL_V1){?>
		<option value="<?php echo $TPL_V1["codecd"]?>"><?php echo $TPL_V1["value"]?></option>
<?php }}?>
	</select>
	</div>
	<div>
	<label><input type="checkbox" name="nonInterestTerms[]" value="2" /> 2개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="3" /> 3개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="4" /> 4개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="5" /> 5개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="6" /> 6개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="7" /> 7개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="8" /> 8개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="9" /> 9개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="10" /> 10개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="11" /> 11개월</label>
	<label><input type="checkbox" name="nonInterestTerms[]" value="12" /> 12개월</label>
	</div>
	<div style="padding:10px 0 0 0" align="center"><span class="btn medium"><button type="button" id="nonInterestAddButton">추가</button></span></div>
</div>
<script type="text/javascript">
$(".helpicon").poshytip({
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
</script>