<?php /* Template_ 2.2.6 2014/09/29 16:08:53 /www/nsone_firstmall_kr/data/skin/mobile_ver2_default/goods/catalog.html 000004022 */  $this->include_("showCategoryDepth");
$TPL_childCategoryData_1=empty($TPL_VAR["childCategoryData"])||!is_array($TPL_VAR["childCategoryData"])?0:count($TPL_VAR["childCategoryData"]);
$TPL_orders_1=empty($TPL_VAR["orders"])||!is_array($TPL_VAR["orders"])?0:count($TPL_VAR["orders"]);?>
<script>
$(function(){
	$(".sub_category_btn").click(function(){
		if($(".sub_category_list ul").is(":visible")){
			$(".sub_category_list ul").slideUp();
			$(this).html("<img src='/data/skin/mobile_ver2_default/images/design/btn_arw_open.png' width='32' />");
		}else{
			$(".sub_category_list ul").slideDown();
			$(this).html("<img src='/data/skin/mobile_ver2_default/images/design/btn_arw_close.png' width='32' />");
		}
	});
});
</script>
<style>
.sub_category_list {position:relative; z-index:10}
.sub_category_list ul {position:absolute; width:100%; display:none;}
.sub_category_list ul li a {display:block; background:url('/data/skin/mobile_ver2_default/images/design/btn_arw_r.png') no-repeat 98% center; background-size:15px 15px; background-color:#f4f4f4; height:40px; line-height:40px; text-indent:10px; border-bottom:1px solid #ddd;}

.sub_category_btn {position:absolute; right:0px; top:50%; margin-top:-16px; height:28px; line-height:28px; width:40px; cursor:pointer; text-align:center; font-size:35px;}
</style>

<div class="sub_title_bar">
	<h2><?php echo $TPL_VAR["categoryTitle"]?></h2>
	<a href="javascript:history.back();" class="stb_back_btn"><img src="/data/skin/mobile_ver2_default/images/design/btn_back.png" width="22" height="22" /></a>
<?php if($TPL_VAR["childCategoryData"]){?>
	<div class="sub_category_btn">
		<img src="/data/skin/mobile_ver2_default/images/design/btn_arw_open.png" width='32' />
	</div>
<?php }?>
</div>

<?php if($TPL_VAR["childCategoryData"]){?>
<div class="sub_category_list">
	<ul>
<?php if($TPL_childCategoryData_1){foreach($TPL_VAR["childCategoryData"] as $TPL_V1){?>
		<li><a href="?code=<?php echo $TPL_V1["category_code"]?>"><?php echo $TPL_V1["title"]?></a></li>
<?php }}?>
	</ul>
</div>
<?php }?>

<div class="goods_list_top">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="25" align="left" colspan="2">
		<?php echo showCategoryDepth($_GET["code"])?>

		</td>
	</tr>
	<tr>
		<td align="left">
<?php if($TPL_VAR["sort"]){?>
			<select onchange="document.location.href=$(this).val();" class="common-select styled">
<?php if($TPL_orders_1){foreach($TPL_VAR["orders"] as $TPL_K1=>$TPL_V1){?>
			<option value="?sort=<?php echo $TPL_K1?>&<?php echo get_args_list(array('page','sort'))?>" <?php if($TPL_K1==$TPL_VAR["sort"]){?>selected<?php }?>><?php echo $TPL_V1?></option>
<?php }}?>
			</select>
<?php }?>
		</td>
		<td align="right" valign="bottom">
<?php if($_GET["display_style"]!='mobile_lattice_a'){?><a href="?display_style=mobile_lattice_a&<?php echo get_args_list(array('display_style','page'))?>"><img src="/data/skin/mobile_ver2_default/images/design/btn_gallery.png" width="30" height="30" /></a><?php }?>
<?php if($_GET["display_style"]&&$_GET["display_style"]!='mobile_list'){?><a href="?display_style=mobile_list&<?php echo get_args_list(array('display_style','page'))?>"><img src="/data/skin/mobile_ver2_default/images/design/btn_list.png" width="30" height="30" /></a><?php }?>
		</td>
	</tr>
	</table>
</div>

<div style="height:10px"></div>

<?php if($TPL_VAR["page"]["totalcount"]== 0){?>
<table align="center" cellpadding="0" cellspacing="0" border="0">
<tr><td height="50"></td></tr>
<tr>
	<td>
		<span class="small">이 분류에 상품이 없습니다.</span>
	</td>
</tr>
<tr><td height="50"></td></tr>
</table>
<?php }else{?>
<?php echo $TPL_VAR["goodsDisplayHTML"]?>

<?php }?>


<div style="height:20px"></div>

<?php $this->print_("paging",$TPL_SCP,1);?>


<div style="height:30px"></div>