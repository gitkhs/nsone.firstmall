<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/kakaopay.html 000010485 */ 
$TPL_CardCompanyCode_1=empty($TPL_VAR["CardCompanyCode"])||!is_array($TPL_VAR["CardCompanyCode"])?0:count($TPL_VAR["CardCompanyCode"]);
$TPL_arrKakaoCardCompany_1=empty($TPL_VAR["arrKakaoCardCompany"])||!is_array($TPL_VAR["arrKakaoCardCompany"])?0:count($TPL_VAR["arrKakaoCardCompany"]);?>
<script type="text/javascript">
var onInterestSettingButtonIndex = 0;

$(document).ready(function() {
	/* 무이자 할부기간 선택팝업 */
	$("button.kakaononInterestSettingButton").each(function(idx){
		$(this).bind("click",function(){
			onInterestSettingButtonIndex = idx;
			openDialog("무이자할부 <span class='desc'>무이자할부 정보를 설정합니다.</span>", "kakaononInterestPopup", {"width":"390","height":200});
		});
	});

	/* 무이자 할부기간 삭제 */
	$("button.kakaononInterestSettingDelete").live("click",function(){
		$(this).parent().parent().remove();
		kakao_sync_height();
	});

	/* 무이자 할부기간 추가 */
	$("button#kakaononInterestAddButton").bind("click",function(){
		var codeName	= "kakaoCardCompanyCode[]";
		var termsName	= "kakaoCardCompanyTerms[]";
		var opt_text	= $("select[name=kakaocardCompany] option:selected").html();
		var opt_value	= $("select[name=kakaocardCompany] option:selected").val();
		var mons = "";
		$("input[type='checkbox'][name='kakaopay_nonInterestTerms[]']:checked").each(function(){
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
		tag += ' <span class="btn small gray"><button type="button" class="kakaononInterestSettingDelete">삭제<span class="arrowright"></span></button></span></div>';
		$("button.kakaononInterestSettingButton").eq(onInterestSettingButtonIndex).parent().next().next().append(tag);
		kakao_sync_height();
	});

	/* 신용카드 사용 */
	$("input[name='kakaopay_payment']").bind("click", function(){
		kakao_check_use_payment();
	});

	/* 무이자 할부기간 변경 */
	$("select[name='kakaopay_nonInterestTerms']").bind("change",function(){
		kakao_check_nonInterest();
	});

	/* 세팅값 출력 */
<?php if($TPL_VAR["payment"]){?>
	$("input[name='kakaopay_payment']").attr('checked',true);
<?php }?>
<?php if($TPL_VAR["interestTerms"]){?>
	$("select[name='kakaopay_interestTerms'] option[value='<?php echo $TPL_VAR["interestTerms"]?>']").attr('selected',true);
<?php }?>
<?php if($TPL_VAR["nonInterestTerms"]){?>
	$("select[name='kakaopay_nonInterestTerms'] option[value='<?php echo $TPL_VAR["nonInterestTerms"]?>']").attr('selected',true);
<?php }?>

	kakao_check_use_payment();
	kakao_check_nonInterest();
	kakao_sync_height();
});

// 모바일/pc플랫폼 테이블간의 높의 조절
function kakao_sync_height(){
	$("div.kakaoinputPgSetting table.info-table-style").eq(1).height( $("div.kakaoinputPgSetting table.info-table-style").eq(0).height() );
}

// 신용카드 사용 체크
function kakao_check_use_payment(){
	var paymentObj	= $("input[name='kakaopay_payment']");
	if( paymentObj.is(":checked") ){
		paymentObj.closest("td").find("select").removeAttr('disabled');
	}else{
		paymentObj.closest("td").find("select").attr('disabled',true);
	}
}

// 신용카드 무이자 할부 기간 체크
function kakao_check_nonInterest(){
	$("select[name='kakaopay_nonInterestTerms'] option").each(function(){
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
<div class="clearbox kakaoinputPgSetting">
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
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td>MID : </td>
					<td>
						<input type="text" name="kakao_mid" class="line" value="<?php echo $TPL_VAR["mid"]?>" title="MID" />
					</td>
				</tr>
				<tr>
					<td>merchantEncKey : </td>
					<td>
						<input type="text" name="kakao_merchantEncKey" class="line" value="<?php echo $TPL_VAR["merchantEncKey"]?>" title="merchantEncKey" />
					</td>
				</tr>
				<tr>
					<td>merchantHashKey : </td>
					<td>
						<input type="text" name="kakao_merchantHashKey" class="line" value="<?php echo $TPL_VAR["merchantHashKey"]?>" title="merchantHashKey" />
					</td>
				</tr>
				<tr>
					<td>merchantKey : </td>
					<td>
						<input type="text" name="kakao_merchantKey" class="line" value="<?php echo $TPL_VAR["merchantKey"]?>" title="merchantKey" />
					</td>
				</tr>
				<tr>
					<td>cancelPwd : </td>
					<td>
						<input type="text" name="kakao_cancelPwd" class="line" value="<?php echo $TPL_VAR["cancelPwd"]?>" title="cancelPwd" />
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="its-td">신용카드</td>
			<td class="its-td">
				<div>
					<label><input type="checkbox" name="kakaopay_payment" value="card" disabled checked /> 사용</label>
					<input type="hidden" name="kakaopay_payment" value="card" />
				</div>
				<div style="padding-top:5px;">
					할부기간 :
					<select name="kakaopay_interestTerms">
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
					<select name="kakaopay_nonInterestTerms">
						<option value="automatic">자동</option>
						<option value="manual">수동</option>
					</select>
					<span class="btn small black">
					<button type="button" class="kakaononInterestSettingButton">설정하기<span class="arrowright"></span></button>
					</span>
					<div class="desc">
					<span class='red'>무이자할부 수수료를 PG사에서 부담 (권장)</span>
					<span class='red'>무이자할부 수수료를 쇼핑몰에서 부담(PG사와 협의)</span>
					</div>
					<div>
<?php if($TPL_CardCompanyCode_1){$TPL_I1=-1;foreach($TPL_VAR["CardCompanyCode"] as $TPL_V1){$TPL_I1++;?>
						<div>
						- <?php echo $TPL_VAR["arrCardCompany"][$TPL_V1]?> <?php echo $TPL_VAR["CardCompanyTerms"][$TPL_I1]?> 개월
						<input type="hidden" name="kakaoCardCompanyCode[]" value="<?php echo $TPL_V1?>">
						<input type="hidden" name="kakaoCardCompanyTerms[]" value="<?php echo $TPL_VAR["CardCompanyTerms"][$TPL_I1]?>">
						<span class="btn small gray"><button type="button" class="kakaononInterestSettingDelete">삭제<span class="arrowright"></span></button></span>
						</div>
<?php }}?>
					</div>
				</div>
			</td>
		</tr>
	</table>
	</div>
	<div style="float:left;width:50%;">
	<table width="100%" height="285px" class="info-table-style kakaoinputPgSetting">
		<col width="10%" /><col width="10%" /><col width="30%" />
		<tr>
			<th class="its-th center bold" colspan="3" height="20">Mobile/Tablet (Facebook쇼핑몰 포함)</th>
		</tr>
		<tr>
			<th class="its-th" rowspan="2">일반</th>
			<td class="its-td">세팅 정보 등록</td>
			<td class="its-td" valign="middle" height="131">
				PC플랫폼과 동일
			</td>
		</tr>
		<tr>
			<td class="its-td">신용카드</td>
			<td class="its-td" height="93">
				PC플랫폼과 동일
			</td>
		</tr>
	</table>
	</div>
</div>
</form>
<div id="kakaononInterestPopup" style="display: none">
	<input type="hidden" name="kakaononInterestSettingButtonIndex" value="0" />
	<div>
		<select name="kakaocardCompany">
<?php if($TPL_arrKakaoCardCompany_1){foreach($TPL_VAR["arrKakaoCardCompany"] as $TPL_V1){?>
			<option value="<?php echo $TPL_V1["codecd"]?>"><?php echo $TPL_V1["value"]?></option>
<?php }}?>
		</select>
	</div>
	<div>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="2" /> 2개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="3" /> 3개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="4" /> 4개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="5" /> 5개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="6" /> 6개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="7" /> 7개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="8" /> 8개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="9" /> 9개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="10" /> 10개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="11" /> 11개월</label>
	<label><input type="checkbox" name="kakaopay_nonInterestTerms[]" value="12" /> 12개월</label>
	</div>
	<div style="padding:10px 0 0 0" align="center"><span class="btn medium"><button type="button" id="kakaononInterestAddButton">추가</button></span></div>
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