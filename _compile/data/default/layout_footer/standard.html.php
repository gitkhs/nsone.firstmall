<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/data/skin/default/layout_footer/standard.html 000003587 */  $this->include_("confirmLicenseLink","escrow_mark");?>
<!-- 하단영역 : 시작 -->
<table width="100%" align="<?php echo $TPL_VAR["layout_config"]["align"]?>" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td height="50"></td>
</tr>
<tr>
	<td height="1" bgcolor="#dbdbdb"></td>
</tr>
<tr>
	<td height="50">
		<table align="<?php echo $TPL_VAR["layout_config"]["align"]?>" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><a href="/main"><img src="/data/skin/default/images/design/footer_home.gif" /></a></td>
			<td><img src="/data/skin/default/images/design/footer_bar.gif" hspace="10" /></td>
			<td><a href="/service/company"><img src="/data/skin/default/images/design/footer_company.gif" /></a></td>
			<td><img src="/data/skin/default/images/design/footer_bar.gif" hspace="10" /></td>
			<td><a href="/service/agreement"><img src="/data/skin/default/images/design/footer_clause.gif" /></a></td>
			<td><img src="/data/skin/default/images/design/footer_bar.gif" hspace="10" /></td>
			<td><a href="/service/privacy"><img src="/data/skin/default/images/design/footer_policy.gif" /></a></td>
			<td><img src="/data/skin/default/images/design/footer_bar.gif" hspace="10" /></td>
			<td><a href="/service/guide"><img src="/data/skin/default/images/design/footer_guide.gif" title="" /></a></td>
			<td><img src="/data/skin/default/images/design/footer_bar.gif" hspace="10" /></td>
			<td><a href="/service/partnership"><img src="/data/skin/default/images/design/footer_alliance.gif" /></a></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td height="1" bgcolor="#dbdbdb"></td>
</tr>
<tr>
	<td height="30"></td>
</tr>
<tr>
	<td align="<?php echo $TPL_VAR["layout_config"]["align"]?>" style="line-height:1.5em;">
			
		<!-- 이미지로 카피라이트를 이용하실 분들은 주석을 해제하여 사용하여 주십시요  -->
		<!-- <img src="/data/skin/default/images/design/footer_txt.gif" /><br />-->		
		회사명 : <?php echo $TPL_VAR["config_basic"]["companyName"]?>

		<font color="cccccc"><b> | </b></font>사업자등록번호 : <?php echo $TPL_VAR["config_basic"]["businessLicense"]?> <?php echo confirmLicenseLink("[사업자정보확인]")?>

		<font color="cccccc"><b> | </b></font>주소 : <?php if($TPL_VAR["config_basic"]["companyAddress_type"]=="street"){?><?php echo $TPL_VAR["config_basic"]["companyAddress_street"]?><?php }else{?><?php echo $TPL_VAR["config_basic"]["companyAddress"]?><?php }?> <?php echo $TPL_VAR["config_basic"]["companyAddressDetail"]?><br />
		통신판매업 신고 : <?php echo $TPL_VAR["config_basic"]["mailsellingLicense"]?>

		<font color="cccccc"><b> | </b></font>연락처 : <?php echo $TPL_VAR["config_basic"]["companyPhone"]?>

<?php if($TPL_VAR["config_basic"]["companyFax"]){?>
		<font color="cccccc"><b> | </b></font>FAX : <?php echo $TPL_VAR["config_basic"]["companyFax"]?>

<?php }?>
		<font color="cccccc"><b> | </b></font>개인정보관리 책임자 : <?php echo $TPL_VAR["config_basic"]["member_info_manager"]?>

		<font color="cccccc"><b> | </b></font>대표자 : <?php echo $TPL_VAR["config_basic"]["ceo"]?><br />
		contact : <font color="990000"><b><?php echo $TPL_VAR["config_basic"]["companyEmail"]?></b></font> for more information
		
		<!--구매안전표기 -->	
		<div style="position:relative"><div style="position:absolute;top:-75px;left:79%;"><?php echo escrow_mark()?></div></div>	
	</td>
	
	
	
</tr>
<tr>
	<td height="50"></td>
</tr>
</table>
<!-- 하단영역 : 끝 -->