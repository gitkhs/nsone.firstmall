<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/main/_main_myservice_bar.html 000002431 */ 
$TPL_serviceHtml_1=empty($TPL_VAR["serviceHtml"])||!is_array($TPL_VAR["serviceHtml"])?0:count($TPL_VAR["serviceHtml"]);?>
<table class="main-support-service" cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td rowspan="3" style="width:96px; font-size:13px; font-family:Dotum; border-top:none; border-right:none; background-color:#aeb6c1; color:#fff; text-align:center; padding-left:5px;" id="desc_area">
		해당 서비스에<br/>
		마우스를 올리세요.
	</td>
<?php if($TPL_serviceHtml_1){$TPL_I1=-1;foreach($TPL_VAR["serviceHtml"] as $TPL_V1){$TPL_I1++;?>
<?php if($TPL_I1!= 0&&($TPL_I1% 7)== 0){?>
		</tr><tr>
<?php }?>
<?php if(!$TPL_V1["nbsp_html"]){?>
	<th class="myservice_area area<?php echo $TPL_V1["num"]?>" onmouseover="service_over('<?php echo $TPL_V1["num"]?>','<?php echo $TPL_V1["overtxt"]?>');">
<?php if($TPL_V1["num"]== 2){?><div class="myservice_area_sms"><?php }else{?>
		<div class="myservice_area"><?php }?>
<?php if($TPL_V1["expire"]){?><div class='myservice_<?php echo $TPL_V1["expire"]?>'><img src='/admin/skin/default/images/main/icon_<?php echo $TPL_V1["expire"]?>.gif' /></div><?php }?>
		</div><span class="myservice_icon icon<?php echo $TPL_V1["num"]?>"></span><?php echo $TPL_V1["name"]?>

	</th>
	<td class="myservice_area area<?php echo $TPL_V1["num"]?>" onmouseover="service_over('<?php echo $TPL_V1["num"]?>','<?php echo $TPL_V1["overtxt"]?>');">
<?php if($TPL_V1["link"]){?><a href='<?php echo $TPL_V1["link"]?>'><?php }?>
<?php if(is_numeric($TPL_V1["servicetxt"])&&$TPL_V1["servicetxt"]<= 7){?>
<?php if($TPL_V1["servicetxt"]== 0){?><span style="color:#eda72c;">신청</span>
<?php }elseif($TPL_V1["servicetxt"]== 1){?>사용중
<?php }elseif($TPL_V1["servicetxt"]== 2){?>사용(무료)
<?php }elseif($TPL_V1["servicetxt"]== 3){?>사용(유료)
<?php }elseif($TPL_V1["servicetxt"]== 4){?>발행안함
<?php }elseif($TPL_V1["servicetxt"]== 5){?><span style="color:#00aeef;">사용중</span>
<?php }elseif($TPL_V1["servicetxt"]== 6){?>미사용
<?php }elseif($TPL_V1["servicetxt"]== 7){?><span style="color:#eda72c;">설정</span>
<?php }?>
<?php }else{?>
				<?php echo $TPL_V1["servicetxt"]?>

<?php }?>
<?php if($TPL_V1["link"]){?></a><?php }?>
	</td>
<?php }else{?>
		<?php echo $TPL_V1["nbsp_html"]?>

<?php }?>
<?php }}?>
</tr>
</table>