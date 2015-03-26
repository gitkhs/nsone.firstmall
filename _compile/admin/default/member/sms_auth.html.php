<?php /* Template_ 2.2.6 2015/01/27 13:35:25 /www/nsone_firstmall_kr/admin/skin/default/member/sms_auth.html 000004385 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<script type="text/javascript">
$(document).ready(function() {

	$('#sms_charge').live('click', function (){
		$.get('sms_payment', function(data) {
		  	$('#smsPopup').html(data);
			openDialog("등급만들기 <span class='desc'>회원 등급을 생성합니다.</span>", "smsPopup", {"width":"1000","height":"700"});
		});
	});

	$('#search_submit').click(function (){
		smsFrmSubmit();
	});

	if ("<?php echo $TPL_VAR["chk"]?>" != ''){
		smsFrmSubmit();
	}
});

function smsFrmSubmit ()
{
	if ("<?php echo $TPL_VAR["chk"]?>" == '')
	{
		alert('SMS를 먼저 신청하신 후 이용하시기 바랍니다.');
		return false;
	}
	$('#gabiaSMSFrm').attr('action', 'http://firstmall.kr/payment_firstmall/sms_account_log.php');
	$('#gabiaSMSFrm').attr('target', 'gabiaSMS');
	$('#gabiaSMSFrm').submit();
}
</script>


<form name="memberForm" id="memberForm" method="post" target="actionFrame" action="../member_process/sms_auth">

<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar">

		<!-- 타이틀 -->
		<div class="page-title">
<?php if($_GET['sc_gb']=="PERSONAL"){?>
			<h2>고객 리마인드 SMS/Email</h2>
<?php }else{?>
			<h2>SMS 발송키 등록(통합)</h2>
<?php }?>
		</div>

		<!-- 좌측 버튼 -->
		<ul class="page-buttons-left">
			<!--
			<li><span class="btn large icon"><button><span class="arrowleft"></span>이동버튼</button></span></li>
			<li><span class="btn large icon"><button><span class="arrowleft"></span>이동버튼</button></span></li>
			-->
		</ul>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button   <?php if($TPL_VAR["isdemo"]["isdemo"]){?>  type="button" <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> type="submit" <?php }?>>저장하기<span class="arrowright"></span></button></span></li>
		</ul>
	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<?php $this->print_("top_menu",$TPL_SCP,1);?>


<!-- 서브 레이아웃 영역 : 시작 -->
<div class="item-title">SMS 계정</div>

<div class="clearbox">
	<table class="info-table-style" style="width:100%">
		<colgroup>
			<col width="15%" />
			<col />
		</colgroup>
		<tbody>
		<tr>
			<th class="its-th-align center">SMS 계정</th>
			<td class="its-td-align left" style="padding-left:10px;">
<?php if($TPL_VAR["sms_id"]){?>
				<?php echo $TPL_VAR["sms_id"]?>

<?php }else{?>
				SMS 계정이 필요합니다. SMS 계정을 만들어 주세요!
				SMS 계정은 가비아 홈페이지 > 마이가비아 > 쇼핑몰관리에서 만들 수 있습니다.
<?php }?>
			</td>
		</tr>
		</tbody>
	</table>
</div>

<br style="line-height:16px;" />


<div class="item-title">SMS 인증번호</div>

<div class="clearbox">
	<table class="info-table-style" style="width:100%">
		<colgroup>
			<col width="15%" />
			<col />
		</colgroup>
		<tbody>
		<tr>
			<th class="its-th-align center">SMS 발송키 등록</th>
			<td class="its-td-align left" style="padding-left:10px;">
			<!--
				<div style="color:red;">2012-05-14 17:21에 SMS 인증번호가 확인되었습니다.</div>
			-->
				<input type="password" name="sms_auth" value="<?php echo $TPL_VAR["sms_auth"]?>" size="70"/>

				<ul class="description">
					<li>※ 상기 입력란에 MY퍼스트몰에서 발급받은 해당 쇼핑몰의 SMS 발송키를 입력해 주십시오. (최초 1회)</li>
					<li>※ 상기 입력란에 SMS 발송키가 입력되어 있어야 쇼핑몰에서 SMS가 발송됩니다.</li>
					<li>※ SMS 발송의 보안을 강화하기 위해 정기적으로 SMS 발송키 변경을 적극 권장 드립니다.</li>
					<li>※ <a href='https://firstmall.kr/ec_hosting/customer/display_view.php?seq=2519&cate=' target='_blank'><span class='highlight-link-text'>SMS발송키 매뉴얼 보기 ▶</span></a></li>
				</ul>


				<!--div class="description">
				바로가기 클릭시 가비아로그인이 필요하며, 쇼핑몰관리페이지에서 SMS인증번호를 발급받아야하는 쇼핑몰도메인 '서비스관리>SMS서비스-관리'를 클릭하시면 됩니다.
				</div-->

			</td>
		</tr>
		</tbody>
	</table>
</div>

</form>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>