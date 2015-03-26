<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/data/skin/default/_modules/layout.html 000005926 */ ?>
<?php $this->print_("HTML_HEADER",$TPL_SCP,1);?>


<?php if($TPL_VAR["designMode"]){?>
<script type="text/javascript">
/* 디자인매니저 세팅 */
$(function(){
	if(parent.document==document){
		DM_init('<?php echo $TPL_VAR["template_path"]?>');
	}
});
</script>
<?php }?>

<style>
<?php if(!$_GET["popup"]&&!$_GET["iframe"]){?>
body {
<?php if($TPL_VAR["layout_config"]["backgroundColor"]){?>background-color:<?php echo $TPL_VAR["layout_config"]["backgroundColor"]?>;<?php }?>
<?php if($TPL_VAR["layout_config"]["backgroundImage"]){?>background:url('<?php echo $TPL_VAR["layout_config"]["backgroundImage"]?>');<?php }?>
<?php if($TPL_VAR["layout_config"]["backgroundRepeat"]){?>background-repeat:<?php echo $TPL_VAR["layout_config"]["backgroundRepeat"]?>;<?php }?>
<?php if($TPL_VAR["layout_config"]["backgroundPosition"]){?>background-position:<?php echo $TPL_VAR["layout_config"]["backgroundPosition"]?>;<?php }?>
}
<?php }?>
</style>

<?php if(!$_GET["popup"]&&!$_GET["iframe"]){?>
	<div id="layout_body" style="min-width:<?php echo $TPL_VAR["layout_config"]["width"]?>px" class="clearbox">

<?php if($TPL_VAR["layout_config"]["layoutHeader"]!='hidden'){?>
<?php $this->print_("LAYOUT_HEADER",$TPL_SCP,1);?>

<?php }?>

<?php if($TPL_VAR["layout_config"]["layoutTopBar"]!='hidden'&&$TPL_VAR["layout_config"]["layoutTopBar"]!=''){?>
<?php $this->print_("LAYOUT_TOPBAR",$TPL_SCP,1);?>

<?php }?>

<?php if($TPL_VAR["layout_config"]["layoutScrollLeft"]!='hidden'||$TPL_VAR["layout_config"]["layoutScrollRight"]!='hidden'){?>
	<table width="<?php echo $TPL_VAR["layout_config"]["width"]?>" align="<?php echo $TPL_VAR["layout_config"]["align"]?>" cellpadding="0" cellspacing="0" border="0">
	<tr>
<?php if($TPL_VAR["layout_config"]["layoutScrollLeft"]!='hidden'){?>
		<td width="0" style="width:0px !important;" valign="top">
			<div style="position:relative;">
				<div id="leftScrollLayer" style="position:absolute; width:100px; margin-left:-120px; top:0px;"><?php $this->print_("LAYOUT_SCROLL_LEFT",$TPL_SCP,1);?></div>
			</div>
		</td>
<?php }?>
		<td width="100%"></td>
<?php if($TPL_VAR["layout_config"]["layoutScrollRight"]!='hidden'){?>
		<td width="0" style="width:0px !important;" valign="top">
			<div style="position:relative;">
				<div id="rightScrollLayer" style="position:absolute; margin-left:20px; top:0px;z-index:10;"><?php $this->print_("LAYOUT_SCROLL_RIGHT",$TPL_SCP,1);?></div>
			</div>
		</td>
<?php }?>
	</tr>
	</table>
<?php }?>

<?php if($TPL_VAR["layout_config"]["layoutSide"]!='hidden'){?>
	<div class="clearbox">
		<div style="width:<?php echo $TPL_VAR["layout_config"]["width"]?>px; <?php if($TPL_VAR["layout_config"]["align"]=='center'){?>margin:auto;<?php }else{?>float:<?php echo $TPL_VAR["layout_config"]["align"]?>;<?php }?>" >
<?php if($TPL_VAR["layout_config"]["layoutSideLocation"]=='left'||!$TPL_VAR["layout_config"]["layoutSideLocation"]){?>
				<div class="fright" style="width:<?php echo $TPL_VAR["layout_config"]["body_width"]?>px; <?php if($TPL_VAR["layout_config"]["bodyBackgroundImage"]){?>background:url('<?php echo $TPL_VAR["layout_config"]["bodyBackgroundImage"]?>');<?php }?>;<?php if($TPL_VAR["layout_config"]["bodyBackgroundColor"]){?>background-color:<?php echo $TPL_VAR["layout_config"]["bodyBackgroundColor"]?><?php }?>"><?php $this->print_("LAYOUT_BODY",$TPL_SCP,1);?></div>
				<div class="fright" style="width:<?php echo $TPL_VAR["layout_config"]["width"]-$TPL_VAR["layout_config"]["body_width"]?>px;"><?php $this->print_("LAYOUT_SIDE",$TPL_SCP,1);?></div>
<?php }elseif($TPL_VAR["layout_config"]["layoutSideLocation"]=='right'){?>
				<div class="fleft" style="width:<?php echo $TPL_VAR["layout_config"]["body_width"]?>px; <?php if($TPL_VAR["layout_config"]["bodyBackgroundImage"]){?>background:url('<?php echo $TPL_VAR["layout_config"]["bodyBackgroundImage"]?>');<?php }?>;<?php if($TPL_VAR["layout_config"]["bodyBackgroundColor"]){?>background-color:<?php echo $TPL_VAR["layout_config"]["bodyBackgroundColor"]?><?php }?>"><?php $this->print_("LAYOUT_BODY",$TPL_SCP,1);?></div>
				<div class="fleft" style="width:<?php echo $TPL_VAR["layout_config"]["width"]-$TPL_VAR["layout_config"]["body_width"]?>px;"><?php $this->print_("LAYOUT_SIDE",$TPL_SCP,1);?></div>
<?php }?>
		</div>
	</div>
<?php }else{?>
	<div class="clearbox">
		<div style="width:<?php echo $TPL_VAR["layout_config"]["width"]?>px; <?php if($TPL_VAR["layout_config"]["align"]=='center'){?>margin:auto;<?php }else{?>float:<?php echo $TPL_VAR["layout_config"]["align"]?>;<?php }?>">
			<div style="<?php if($TPL_VAR["layout_config"]["bodyBackgroundImage"]){?>background:url('<?php echo $TPL_VAR["layout_config"]["bodyBackgroundImage"]?>');<?php }?>;<?php if($TPL_VAR["layout_config"]["bodyBackgroundColor"]){?>background-color:<?php echo $TPL_VAR["layout_config"]["bodyBackgroundColor"]?><?php }?>"><?php $this->print_("LAYOUT_BODY",$TPL_SCP,1);?></div>
		</div>
	</div>
<?php }?>
	</div>
<?php }else{?>
	<div id="layout_body" class="clearbox">
		<div style="<?php if($TPL_VAR["layout_config"]["align"]=='center'){?>margin:auto;<?php }else{?>float:<?php echo $TPL_VAR["layout_config"]["align"]?>;<?php }?>" >
<?php $this->print_("LAYOUT_BODY",$TPL_SCP,1);?>

		</div>
	</div>
<?php }?>

<?php if(!$_GET["popup"]&&!$_GET["iframe"]){?>
<?php if($TPL_VAR["layout_config"]["layoutFooter"]!='hidden'){?>
<?php $this->print_("LAYOUT_FOOTER",$TPL_SCP,1);?>

<?php }?>
<?php }?>

<?php if(!$_GET["iframe"]){?>
<iframe name="actionFrame" src="/main/blank" frameborder="1" width="100%" height="600" <?php if(!$_GET["debug"]){?>class="hide"<?php }?>></iframe>
<div id="openDialogLayer" style="display: none">
	<div align="center" id="openDialogLayerMsg"></div>
</div>
<?php }?>

<div id="ajaxLoadingLayer" style="display: none"></div>

<?php $this->print_("HTML_FOOTER",$TPL_SCP,1);?>