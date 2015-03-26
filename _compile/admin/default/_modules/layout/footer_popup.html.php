<?php /* Template_ 2.2.6 2014/09/29 15:56:49 /www/nsone_firstmall_kr/admin/skin/default/_modules/layout/footer_popup.html 000001083 */ ?>
</div>

<iframe name="actionFrame" src="" frameborder="1" width="100%" height="550" class="hide"></iframe>

<div id="openDialogLayer" style="display: none">
	<div align="center" id="openDialogLayerMsg"></div>
</div>

<div id="ajaxLoadingLayer" style="display: none"></div>

<div id="nofreeService" class="hide">
<div>
		<table width="100%">
		<tr>
		<td align="left">
			사용중이신 서비스에서는 해당기능이 지원되지 않습니다.<br />
			프리미엄몰 Plus+ 또는 독립몰 Plus+로 업그레이드 하시길 바랍니다.<br />
		</td>
		</tr>
		<tr>
		<td align="center"><br /><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<img src="/admin/skin/default/images/common/btn_upgrade.gif" class="hand" onclick="serviceUpgrade();" align="absmiddle" />
		</td>
		</tr>
		</table>
	</div>
	<br style="line-height:20px;" />
</div>

</body>
<?php $this->print_("common_html_footer",$TPL_SCP,1);?>