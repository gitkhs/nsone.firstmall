<?php /* Template_ 2.2.6 2012/08/01 13:37:06 /www/lgs/data/email/refund.html 000011601 */ 
$TPL_items_1=empty($TPL_VAR["items"])||!is_array($TPL_VAR["items"])?0:count($TPL_VAR["items"]);?>
<html>
<head>
<!--style>
body{font-size:10px;}
TH {color:#4D4D4D; font-size:12px; line-height:14px}
TD {color:#4D4D4D; font-size:12px; line-height:14px}
A:link {color:#4D4D4D; text-decoration:none}
A:visited {color:#4D4D4D; text-decoration:none}
A:active {color:#4D4D4D; text-decoration:none}
A:hover {color:#0066FF; text-decoration:none}
.texts {font-size: 12px; color : #616a74;  font-family: "돋움", Dotum; line-height:22px; padding-left:10px;}
.company {font-size: 11px; color : #979ea5;  font-family: "돋움", Dotum; line-height:22px; text-align:center;}

/* 기본 정보 테이블 스타일 */
table.info-table-style {border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;}
table.info-table-style .its-section {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 5px 8px 5px; text-align:center; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-th {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 28px; text-align:left; background-color:#f1f1f1; font-weight:normal; font-size:12px;}
table.info-table-style .its-td {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 15px; line-height:180%; letter-spacing:0px; font-size:12px;}
table.info-table-style .its-th-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 0; background-color:#f1f1f1; font-weight:normal; font-size:12px;}
table.info-table-style .its-td-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; font-size:12px;}
table.info-table-style .its-th-center {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;}
table.info-table-style .its-td-center {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;}

table.info-table-style textarea {background-color:#f0f0f0;}
table.info-table-style textarea.input-box-default-text {color:#a5a5a5 !important}
.item-title {margin-top:10px; padding-left:30px; line-height:30px; font-size:14px; font-weight:bold; vertical-align:middle;width:696px;text-align:left;}
</style-->
</head>
 
<body style="font-size:10px;">
<table width="696" border="0" cellpadding="0" cellspacing="0">
	<tr><td height="20" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr>
		<td style="color:#4D4D4D; font-size:12px; line-height:14px;"><img src="/data/mail/logo.gif"></td>
	</tr>
	<tr><td height="20" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr><td bgcolor="#000000" height="2" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr><td height="60" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<!-- 내용시작 -->
	<tr>
		<td style="color:#4D4D4D; font-size:12px; line-height:14px;"><img src="/data/mail/txt_refund.gif"></td>
	</tr>
	<tr><td height="35" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr><td class="texts" style="font-size: 12px; color : #616a74;  font-family: 돋움, Dotum; line-height:22px; padding-left:10px;">반품 상품의 환불이 완료되었습니다.</td></tr>
	<tr><td class="texts" style="font-size: 12px; color : #616a74;  font-family: 돋움, Dotum; line-height:22px; padding-left:10px;">자세한 환불내역은 <?php echo $TPL_VAR["basic"]["shopName"]?> > 마이페이지 에서 조회하실 수 있습니다.</td></tr>
	<tr><td height="20" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr>
		<td align="center">

		<div class="item-title" style="margin-top:10px; padding-left:30px; line-height:30px; font-size:14px; font-weight:bold; vertical-align:middle;width:696px;text-align:left;">환불 내역</div>	
		<table width="676" class="info-table-style" style="border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;">
		<tr>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">환불사유</th>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">환불방법</th>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">환불금액</th>
<?php if($TPL_VAR["refund_emoney"]){?>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">환원적립금</th>
<?php }?>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">환불완료 일시</th>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">환불상태</th>
		</tr>
		<tr>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_VAR["refund_reason"]?></td>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_VAR["mrefund_method"]?></td>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_VAR["refund_price"]?>원</td>
<?php if($TPL_VAR["refund_emoney"]){?>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_VAR["refund_emoney"]?>원</td>
<?php }?>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_VAR["refund_date"]?></td>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_VAR["mstatus"]?></td>
		</tr>
		</table>

		<div style="padding:5px;"></div>

		<table width="676" class="info-table-style" style="border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;">
		<tr>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">취소상품</th>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">주문수량</th>
			<th class="its-th-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px; background-color:#f1f1f1;">취소수량</th>
		</tr>
<?php if($TPL_items_1){foreach($TPL_VAR["items"] as $TPL_V1){?>
		<tr>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;">
				<span class="order-item-image"><img class="small_goods_image" src="<?php echo $TPL_V1["image"]?>" /></span>
				<span class="goods_name"><?php echo character_limiter($TPL_V1["goods_name"], 41)?></span>
				<div>
<?php if($TPL_V1["option1"]){?>
					<span class="option"><?php echo $TPL_V1["title1"]?> : <?php echo $TPL_V1["option1"]?></span>
<?php }?>
<?php if($TPL_V1["option2"]){?>
					<span class="option"><?php echo $TPL_V1["title2"]?> : <?php echo $TPL_V1["option2"]?></span>
<?php }?>
<?php if($TPL_V1["option3"]){?>
					<span class="option"><?php echo $TPL_V1["title3"]?> : <?php echo $TPL_V1["option3"]?></span>
<?php }?>
<?php if($TPL_V1["option4"]){?>
					<span class="option"><?php echo $TPL_V1["title4"]?> : <?php echo $TPL_V1["option4"]?></span>
<?php }?>
<?php if($TPL_V1["option5"]){?>
					<span class="option"><?php echo $TPL_V1["title5"]?> : <?php echo $TPL_V1["option5"]?></span>
<?php }?>		
				</div>
			</td>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_V1["option_ea"]?></td>
			<td class="its-td-center" style="color:#4D4D4D; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px; text-align:center; font-size:12px;"><?php echo $TPL_V1["ea"]?></td>
		</tr>
<?php }}?>
		</table>
		
		</td>
	</tr>
	<tr><td height="100" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr>
		<td align="center" style="color:#4D4D4D; font-size:12px; line-height:14px;"><a href="http://<?php echo $TPL_VAR["basic"]["domain"]?>"><img src="/data/mail/btn_go.gif" border="0"></a></td>
	</tr>
	<tr><td height="30" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr><td bgcolor="#000000" height="2" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr><td height="20" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr>
		<td class="company" style="font-size: 11px; color : #979ea5;  font-family: 돋움, Dotum; line-height:22px; text-align:center;">
			사업자등록번호 : <?php echo $TPL_VAR["basic"]["businessLicense"]?>&nbsp;&nbsp;&nbsp;&nbsp;통신판매업신고번호 : <?php echo $TPL_VAR["basic"]["mailsellingLicense"]?>&nbsp;&nbsp;&nbsp;&nbsp;대표이사 : <?php echo $TPL_VAR["basic"]["ceo"]?>

		</td>
	</tr>
	<tr>
		<td class="company" style="font-size: 11px; color : #979ea5;  font-family: 돋움, Dotum; line-height:22px; text-align:center;">
			주소 : <?php echo $TPL_VAR["basic"]["companyAddress"]?>&nbsp;&nbsp;&nbsp;&nbsp;대표전화 : <?php echo $TPL_VAR["basic"]["companyPhone"]?>&nbsp;&nbsp;&nbsp;&nbsp;팩스 : <?php echo $TPL_VAR["basic"]["companyFax"]?>

		</td>
	</tr>
</table>






</body>

</html>