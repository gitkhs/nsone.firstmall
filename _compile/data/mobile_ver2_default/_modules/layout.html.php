<?php /* Template_ 2.2.6 2014/09/29 16:08:53 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/_modules/layout.html 000003618 */ ?>
<?php $this->print_("HTML_HEADER",$TPL_SCP,1);?>


<!--[ 디자인모드 호출 스크립트]-->
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

<!--[ 모바일쇼핑몰 디자인모드시 화면 구성 ]-->
<?php if(($TPL_VAR["mobileMode"]||$TPL_VAR["storemobileMode"])&&$TPL_VAR["designMode"]){?>
<style>
/*
body {background-color:#999}
#wrap {background-color:#fff; margin:60px auto 0 auto; width:320px; height:480px; overflow:auto;}
*/
</style>
<script>
$(function(){
	//$("#wrap").resizable();
});
</script>
<?php }?>

<style>
#layout_body {
<?php if($TPL_VAR["layout_config"]["backgroundColor"]){?>background-color:<?php echo $TPL_VAR["layout_config"]["backgroundColor"]?>;<?php }?>
<?php if($TPL_VAR["layout_config"]["backgroundImage"]){?>background:url('<?php echo $TPL_VAR["layout_config"]["backgroundImage"]?>');<?php }?>
<?php if($TPL_VAR["layout_config"]["backgroundRepeat"]){?>background-repeat:<?php echo $TPL_VAR["layout_config"]["backgroundRepeat"]?>;<?php }?>
<?php if($TPL_VAR["layout_config"]["backgroundPosition"]){?>background-position:<?php echo $TPL_VAR["layout_config"]["backgroundPosition"]?>;<?php }?>
}
#layer_pay {position:absolute;top:0px;width:100%;height:100%;background-color:#ffffff;text-align:center;z-index:999999;}
#payprocessing {text-align:center;position:absolute;width:100%;top:150px;z-index:99999999px;}
</style>
<div id="wrap">
	<div id="layout_side">
<?php $this->print_("common_layout_side",$TPL_SCP,1);?>

	</div>
	<div id="layout_wrap">
<?php if(!$_GET["popup"]&&!$_GET["iframe"]){?>
<?php if($TPL_VAR["layout_config"]["layoutHeader"]!='hidden'){?>
<?php $this->print_("LAYOUT_HEADER",$TPL_SCP,1);?>

<?php }?>
<?php if($TPL_VAR["layout_config"]["layoutMainTopBar"]!='hidden'&&strpos(uri_string(),"main")!==false&&$TPL_VAR["layout_config"]["layoutMainTopBar"]!=''){?>
<?php $this->print_("LAYOUT_MAIN_TOPBAR",$TPL_SCP,1);?>

<?php }?>
<?php }?>

		<div id="layout_body">
<?php if(!($TPL_VAR["layout_config"]["layoutMainTopBar"]!='hidden'&&strpos(uri_string(),"main")!==false&&$TPL_VAR["layout_config"]["layoutMainTopBar"]!='')){?>
<?php $this->print_("LAYOUT_BODY",$TPL_SCP,1);?>

<?php }?>
		</div>

<?php if(!$_GET["popup"]&&!$_GET["iframe"]){?>
<?php if($TPL_VAR["layout_config"]["layoutFooter"]!='hidden'){?>
<?php $this->print_("LAYOUT_FOOTER",$TPL_SCP,1);?>

<?php }?>
<?php }?>

<?php if(!$_GET["iframe"]){?>
<?php if(!$_GET["debug"]){?>
		<iframe name="actionFrame" src="/main/blank" frameborder="0" width="0" height="0" class="hide" ></iframe>
<?php }else{?>
		<iframe name="actionFrame" src="/main/blank" frameborder="0" width="100%" height="500"></iframe>
<?php }?>
		<div id="openDialogLayer" style="display: none">
			<div align="center" id="openDialogLayerMsg"></div>
		</div>
<?php }?>
		<div id="ajaxLoadingLayer" style="display: none"></div>
	</div>
</div>
<div id="mobileZipcodeLayer" style="display: none"></div>
<!-- 결제창을 레이어 형태로 구현-->
<div id="layer_pay" class="hide">
</div>
<div id="payprocessing" class="pay_layer hide">
	<div style="margin:auto;"><img src="/data/skin/mobile_ver2_default/images/design/img_paying.gif" /></div>
	<div style="margin:auto;padding-top:20px;"><img src="/data/skin/mobile_ver2_default/images/design/progress_bar.gif" /></div>
</div>
<?php $this->print_("HTML_FOOTER",$TPL_SCP,1);?>