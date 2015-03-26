<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/member/sms_charge.html 000003356 */ ?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<script type="text/javascript">
$(document).ready(function() {

	$('#sms_charge').live('click', function (){
		$.get('sms_payment', function(data) {
		  	$('#smsPopup').html(data);
			openDialog("SMS 충전 <span class='desc'>&nbsp;</span>", "smsPopup", {"width":"800","height":"600"});
		});
	});

	$('#search_submit').click(function (){
		smsFrmSubmit();
	});

	if ("<?php echo $TPL_VAR["chk"]?>" != ''){
		smsFrmSubmit();
	}else{
		$.get('../member_process/getAuthPopup?type=A', function(data) {
		  	$('#authPopup').html(data);
			openDialog("SMS 계정 <span class='desc'>&nbsp;</span>", "authPopup", {"width":"600","height":"150"});
		});
	}

});

function smsFrmSubmit ()
{
	if ("<?php echo $TPL_VAR["chk"]?>" == '')
	{
		$.get('../member_process/getAuthPopup?type=A', function(data) {
		  	$('#authPopup').html(data);
			openDialog("SMS 계정 <span class='desc'>&nbsp;</span>", "authPopup", {"width":"600","height":"150"});
		});
		return;
	}
	$('#gabiaSMSFrm').attr('action', 'http://firstmall.kr/payment_firstmall/sms_account_log.php');
	$('#gabiaSMSFrm').attr('target', 'gabiaSMS');
	$('#gabiaSMSFrm').submit();

	$("#gabiaSMS").css("width",$("#top_table").css("width"));
}
</script>

<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar">

		<!-- 타이틀 -->
		<div class="page-title">
<?php if($_GET['sc_gb']=="PERSONAL"){?>
			<h2>고객 리마인드 SMS/Email</h2>
<?php }else{?>
			<h2>SMS충전(통합)</h2>
<?php }?>
		</div>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<?php $this->print_("top_menu",$TPL_SCP,1);?>


<!-- 서브 레이아웃 영역 : 시작 -->
<div class="item-title">SMS 충전</div>
<div class="clearbox">
	<table class="info-table-style" style="width:100%" id="top_table">
		<colgroup>
			<col width="15%" />
			<col />
		</colgroup>
		<tbody>
		<tr>
			<th class="its-th-align center">현재 SMS 잔여건수</th>
			<td class="its-td-align left" style="padding-left:10px;"> <?php echo $TPL_VAR["count"]?>건 <span class="btn small cyanblue"><button type="button"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?> id="sms_charge" <?php }?>>충전</button></td>
		</tr>
		</tbody>
	</table>
</div>

<div class="clearbox">
<form name="gabiaSMSFrm" id="gabiaSMSFrm" method="post">
<input type="hidden" name="params" value="<?php echo $TPL_VAR["param"]?>">
<div class="item-title">SMS 충전 내역
	<select name="year">
		<?php
		$year	= date('Y');
		for($y=2002; $y<=$year; $y++)
		{
		?>
		<option value="<?=$y?>"<?=($year == $y) ? " selected" : ""?>><?=$y?></option>
		<?php
		}
		?>
	</select>

	<span class="btn small gray"><button type="button" id="search_submit">검색</button>
</div>
</form>
</div>


<?php if($TPL_VAR["chk"]!=''){?>
<div class="clearbox">
<table width="96%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
	<td>
		<iframe name="gabiaSMS" id="gabiaSMS" style="width:100%;height:700px;" frameborder="0"></iframe>
	</td>
</tr>
</table>
</div>
<?php }?>


<div id="smsPopup" class="hide"></div>
<div id="authPopup" class="hide"></div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>