<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/protect.html 000014729 */ 
$TPL_protectIp_1=empty($TPL_VAR["protectIp"])||!is_array($TPL_VAR["protectIp"])?0:count($TPL_VAR["protectIp"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>

<script type="text/javascript">

$(document).ready(function() {
	
	$("input[name='ssl']").change(function(){
		if($(this).is(":checked")){
			$(".ssl_kind_tbody").hide();
			if($(this).val()){
				$("#ssl_kind_tbody_"+$(this).val()).show();
			}
		}
	}).change();
	
	/* IP 입력칸 포커싱 처리*/
	$(".ip_input").each(function(){
		var that = this;
		$("input",this).each(function(idx){
			$(this).bind('change keyup',function(event){
				// 쉬프트, 탭키는 무시
				if(event.keyCode==9 || event.keyCode==16) return;
				
				$(this).val($(this).val().replace(/[^0-9.]/g,""));
				
				thisInput = this;
				
				var check_band_ok = function(thisClassValue){
					thisClassValue = parseInt(thisClassValue);
					
					if(thisClassValue<0 || thisClassValue>255){
						openDialogAlert("0~255 사이의 숫자만 입력해주세요.",400,140,function(){
							$(thisInput).val('').focus();
						});
						return false;
					}
					
					if($(thisInput).val()!='0'){
						$(thisInput).val($(thisInput).val().replace(/^0*/g,""));
					}

					return true;
				};

				if($(this).val().length>=3 || (/[.]/).test($(this).val())){
					var val = $(this).val();
					
					for(var i=0;i<val.length;i++){
						if(val.substring(i,i+1)=='.'){
							$(this).val(val.substring(0,i));
							if(!check_band_ok($(this).val())) return;
							$("input",that).eq(idx+1).focus().val(val.substring(i+1,val.length)).change();
							break;
						}
						
						if(val.substring(0,i+1).length>=3){
							$(this).val(val.substring(0,i+1));
							if(!check_band_ok($(this).val())) return;
							if(val.substring(i+1,i+2)=='.'){
								$("input",that).eq(idx+1).focus().val(val.substring(i+2,val.length)).change();
							}else{
								$("input",that).eq(idx+1).focus().val(val.substring(i+1,val.length)).change();
							}
							break;
							
						}
						
						
					}
				}
				
				check_band_ok($(this).val());
			});

			$(this).bind('keydown',function(event){

				// 백스페이스 처리
				if(event.keyCode==8 && $(this).val().length==0){
					if(idx>0){
						$("input",that).eq(idx-1).focus();
						$("input",that).eq(idx-1).val($("input",that).eq(idx-1).val().substring(0,2));
						
						return false;
					}
				}

				// 점 처리
				if(event.keyCode==190 || event.keyCode==110){
					if(idx<4 && $(this).val().length>=1){
						$("input",that).eq(idx+1).focus();
					}
					return false;
				}
				
			});
			
		});
	});
	
	/* 차단IP 추가 버튼 */
	$("#btn_add_banip").click(function(){
		var ip = '';
		var ip_end = false;

		var ipInputSelector = ".new_ip_input input";
		for(var i=0;i<$(ipInputSelector).length;i++){
			$(ipInputSelector).eq(i).val($(ipInputSelector).eq(i).val().replace(/ /,''));
			if($(ipInputSelector).eq(i).val().length){
				if(ip_end){
					openDialogAlert("아이피 중간을 비워둘 수 없습니다.",400,140,function(){
						$(ipInputSelector).eq(i-1).focus();
					});
					return;
				}
				ip += $(ipInputSelector).eq(i).val();
				if(i<3) ip += '.';
			}else{
				ip_end = true;
			}
		}
		
		add_banip(ip,'prepend');
		
	});
	
	/* 차단IP 검색 버튼*/
	$("#btn_search_banip").click(function(){
		
		var ip = '';
		$(".search_ip_input input").each(function(idx){
			if($(this).val()){
				if(idx) ip += '.';
				ip += $(this).val();
			}
		});	
	
		$("#ip_list .ip_item").each(function(){
			if($("input[name='protectIp[]']",this).val().substring(0,ip.length)==ip){
				$(this).show();
			}else{
				$(this).hide();
			}
		});
		
		$(".search_ip_input input").attr("disabled",true);
		$(this).attr("disabled",true);
		
	});
	
	/* 차단IP 검색 초기화 버튼*/
	$("#btn_reset_banip").click(function(){
		$("#btn_search_banip").removeAttr("disabled");
		$(".search_ip_input input").removeAttr("disabled");
		$(".search_ip_input input").val('').eq(0).focus();
		$("#ip_list .ip_item").show();
	});
	
	/* 추가 IP 입력폼 엔터키 */
	$(".new_ip_input input").bind('keydown',function(event){
		if(event.keyCode=='13'){
			$("#btn_add_banip").click();
			return false;
		}
	});
	
	/* 검색 IP 입력폼 엔터키 */
	$(".search_ip_input input").bind('keydown',function(event){
		if(event.keyCode=='13'){
			$("#btn_search_banip").click();
			return false;
		}
		
		if(event.keyCode=='27'){
			$("#btn_reset_banip").click();
			return false;
		}
	});
	
	/* 보안서버 신청 버튼 */
	$("#btn_ssl_regist").click(function(){
		window.open("http://hosting.gabia.com/ec_hosting/addservice/ssl.php");
	});

	/* 보안서버 직접입력 체크박스 */
	$("input[name='ssl_external']").change(function(){
		if($(this).is(':checked')){
			$("#ssl_external_area").show();
		}else{
			$("#ssl_external_area").hide();
		}
	}).change();
	 
<?php if($TPL_protectIp_1){foreach($TPL_VAR["protectIp"] as $TPL_V1){?>
	add_banip('<?php echo $TPL_V1?>','append');
<?php }}?>
});

function add_banip(ip,loc){
	if(ip.length){
		var html = '';
		html += '<div class="ip_item clearbox">';
		html += '<input type="hidden" name="protectIp[]" value="'+ip+'"  >';
		html += '<span class="ip_item_ip">'+ip+'</span>';
		html += '<span class="ip_item_del hand" onclick="del_banip(this)"><img src="/admin/skin/default/images/common/icon_minus.gif" /></span>';
		html += '</div>';
		
		if($("input[name='protectIp[]'][value='"+ip+"']").length){
			openDialogAlert("이미 추가한 IP입니다.",400,140);
		}else{
			if(loc=='append')$("#ip_list").append(html);
			if(loc=='prepend')$("#ip_list").prepend(html);
		}
	}
}

/* 아이피 삭제 */
function del_banip(btn){
	$(btn).closest(".ip_item").remove();
}
</script>

<style>
.ssl_kind_tbody {display:none;}
#ip_list {margin-top:5px; border:1px solid #ddd; height:120px; padding:5px; overflow:auto;}
#ip_list .ip_item {height:22px; line-height:22px; border-top:1px dashed #ddd;}
#ip_list .ip_item:first-child {border-top:0px;}
#ip_list .ip_item_ip	{float:left;}
#ip_list .ip_item_del	{float:right; padding-top:2px;}
</style>

<form name="protectSettingForm" method="post" enctype="multipart/form-data" action="../setting_process/protect" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
		
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 보안</h2>
		</div>
		
		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">
			
			<div class="item-title">보안서버 <span class="helpicon" title="개인정보보호를 위해 보안서버 사용을 권장합니다."></span></div>

			<table width="100%" class="info-table-style">
			<col width="150" /><col width="" /><col width="150" /><col width="" />
			<tr>
				<th class="its-th">사용여부</th>
				<td class="its-td">
					<label><input type="radio" name="ssl" value="pay" <?php if($TPL_VAR["ssl"]->ssl_use&&$TPL_VAR["ssl"]->ssl_pay){?>checked="checked"<?php }?> /> 유료 보안서버</label>
					<label><input type="radio" name="ssl" value="free" <?php if($TPL_VAR["ssl"]->ssl_use&&!$TPL_VAR["ssl"]->ssl_pay){?>checked="checked"<?php }?> /> 무료 보안서버</label>
					<label><input type="radio" name="ssl" value="" <?php if(!$TPL_VAR["ssl"]->ssl_use){?>checked="checked"<?php }?> /> 사용안함</label>
				</td>
			</tr>
			<!-- 무료 SSL -->
			<tbody id="ssl_kind_tbody_free" class="ssl_kind_tbody">
				<tr>
					<th class="its-th">상태</th>
					<td class="its-td">정상동작중</td>
				</tr>
				<tr>
					<th class="its-th">종류</th>
					<td class="its-td">Thawte SSL 128bit</td>
				</tr>
				<tr>
					<th class="its-th">유효기간</th>
					<td class="its-td">무료지원</td>
				</tr>
				<tr>
					<th class="its-th">적용 페이지</th>
					<td class="its-td">
						개인의 소중한 정보를 보호하기 위해 로그인, 회원가입/수정 등의 페이지에서 개인정보를 데이터를 암호화합니다.<br />
						보안서버 적용페이지는 아래와 같습니다.<br />
						1) 회원 가입 <br />
						2) 회원 로그인 <br />
						3) 아이디/비밀번호 찾기<br /> 
						4) 주문 페이지<br />
					</td>
				</tr>
			</tbody>	
			<!-- 유료 SSL -->
			<tbody id="ssl_kind_tbody_pay" class="ssl_kind_tbody">
				<tr>
					<th class="its-th">상태</th>
					<td class="its-td">
<?php if($TPL_VAR["ssl"]->ssl_status){?>
						정상동작중
<?php }else{?>
						미신청 <span class="btn small cyanblue"><input type="button" value="보안서버 신청" id="btn_ssl_regist" /></span>
<?php }?>
					<div><label class="desc"><input type="checkbox" name="ssl_external" value="1"<?php if($TPL_VAR["ssl"]->ssl_external){?>checked<?php }?> /> SSL세팅값 직접입력</label></div>
					<div id="ssl_external_area" class="hide">
						<table border="0" cellpadding="0" cellspacing="2" class="desc">
						<tr>
							<td class="right">SSL Domain </td>
							<td class="pdl5 pdb5"><input type="text" name="ssl_ex_domain" value="<?php echo $TPL_VAR["ssl"]->ssl_ex_domain?>" title="firstmall.kr" /> ex) firstmall.kr</td>
						</tr>
						<tr>
							<td class="right">SSL Port </td>
							<td class="pdl5 pdb5"><input type="text" name="ssl_ex_port" value="<?php echo $TPL_VAR["ssl"]->ssl_ex_port?>" title="5000" /> ex) 5000</td>
						</tr>
						</table>
					</div>
					</td>
				</tr>
<?php if($TPL_VAR["ssl"]->ssl_status){?>
				<tr>
					<th class="its-th">종류</th>
					<td class="its-td"><?php echo $TPL_VAR["ssl"]->ssl_kind?></td>
				</tr>
				<tr>
					<th class="its-th">유효기간</th>
					<td class="its-td"><?php echo $TPL_VAR["ssl"]->ssl_period_start?> ~ <?php echo $TPL_VAR["ssl"]->ssl_period_expire?></td>
				</tr>
<?php }?>
				<tr>
					<th class="its-th">적용 페이지</th>
					<td class="its-td">
						유료 보안서버는 강력한 128/256bit로 암호화를 지원하여 개인의 정보를 한층 강화합니다.<br />
						유료 보안서버는 해당되는 도메인에만 단독으로 사용되어집니다. (VS 무료보안서버 : 공유하여 사용)<br />
						개인의 소중한 정보를 보호하기 위해 로그인, 회원가입/수정 등의 페이지에서 개인정보를 데이터를 암호화합니다. <br />
						보안서버 적용페이지는 아래와 같습니다.<br />
						1) 회원 가입<br />
						2) 회원 로그인 <br />
						3) 아이디/비밀번호 찾기 <br />
						4) 주문 페이지
					</td>
				</tr>
			</tbody>		
			</table>
			
			<div class="item-title">접속차단 IP</div>

			<table width="100%" class="info-table-style">
			<col width="150" /><col width="" /><col width="150" /><col width="" />
			<tr>
				<th class="its-th">설정</th>
				<td class="its-td">
				
					<table>
					<tr>
						<td>
							<span class="new_ip_input ip_input">
							<input type="text" value="" size="4" class="line" />.
							<input type="text" value="" size="4" class="line" />.
							<input type="text" value="" size="4" class="line" />.
							<input type="text" value="" size="4" class="line" />
							</span>
							<span class="btn small"><input type="button" value="추가" id="btn_add_banip" /></span>
							<br /><br />
							<span class="desc">※ 대역 차단 안내<br />
							123 . 123 . 123 . [공란] 입력 시<br />
							123 . 123 . 123 . 0~255 대역이 모두 차단됨
							</span>
						</td>
						<td width="50" class="center">
							
						</td>
						<td valign="top">
							<b>접속차단 IP 리스트</b><br />
							<span class="search_ip_input ip_input">
								<input type="text" value="" size="4" class="line" />.
								<input type="text" value="" size="4" class="line" />.
								<input type="text" value="" size="4" class="line" />.
								<input type="text" value="" size="4" class="line" />
							</span>
							<span class="btn small"><input type="button" value="검색" id="btn_search_banip" /></span>
							<span class="btn small"><input type="button" value="초기화" id="btn_reset_banip" /></span>
							<br />
							<div id="ip_list"></div>					
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
			
			<div class="item-title">컨텐츠 무단 복사 보호</div>

			<table width="100%" class="info-table-style">
			<col width="150" /><col width="" /><col width="150" /><col width="" />
			<tr>
				<th class="its-th">마우스 오른쪽 클릭</th>
				<td class="its-td">
					<label><input type="radio" name="protectMouseRight" value="0" <?php if(!$TPL_VAR["config_system"]["protectMouseRight"]){?>checked="checked"<?php }?> /> 허용</label>
					<label><input type="radio" name=protectMouseRight value="1" <?php if($TPL_VAR["config_system"]["protectMouseRight"]=='1'){?>checked="checked"<?php }?> /> 금지</label> (상품이미지를 포함하여 마우스 오른쪽 클릭을 통한 도구창 사용 금지)
				</td>
			</tr>
			<tr>
				<th class="its-th">마우스 드래그&복사</th>
				<td class="its-td">
					<label><input type="radio" name="protectMouseDragcopy" value="0" <?php if(!$TPL_VAR["config_system"]["protectMouseDragcopy"]){?>checked="checked"<?php }?> /> 허용</label>
					<label><input type="radio" name=protectMouseDragcopy value="1" <?php if($TPL_VAR["config_system"]["protectMouseDragcopy"]=='1'){?>checked="checked"<?php }?> /> 금지</label> (컨텐츠의 드래그와 Ctrl키 사용을 금지)
				</td>
			</tr>
			</table>

		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>