<?php /* Template_ 2.2.6 2014/03/06 15:06:17 /www/gs1302485/lgs/data/email/findid.html 000005402 */ ?>
<html>
<head>
<!--style>
body{font-size:10px;}
TH {color:#4D4D4D; font-size:12px; line-height:14px;}
TD {color:#4D4D4D; font-size:12px; line-height:14px;}
A:link {color:#4D4D4D; text-decoration:none}
A:visited {color:#4D4D4D; text-decoration:none}
A:active {color:#4D4D4D; text-decoration:none}
A:hover {color:#0066FF; text-decoration:none}
.texts {font-size: 12px; color : #616a74;  font-family: "돋움", Dotum; line-height:22px; padding-left:10px;}
.company {font-size: 11px; color : #979ea5;  font-family: "돋움", Dotum; line-height:22px; text-align:center;}


/* 기본 정보 테이블 스타일 */
table.info-table-style {border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;}
table.info-table-style .its-section {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 5px 8px 5px; text-align:center; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-th {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 28px; text-align:left; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-td {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 15px; line-height:180%; letter-spacing:0px;}
table.info-table-style .its-th-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 0; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-td-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px;}
table.info-table-style textarea {background-color:#f0f0f0;}
table.info-table-style textarea.input-box-default-text {color:#a5a5a5 !important}
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
		<td style="color:#4D4D4D; font-size:12px; line-height:14px;"><img src="/data/mail/txt_id.gif"></td>
	</tr>
	<tr><td height="35" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr><td class="texts" style="font-size: 12px; color : #616a74;  font-family: 돋움, Dotum; line-height:22px; padding-left:10px;"><b><?php echo $TPL_VAR["userid"]?></b>님 안녕하세요?  <?php echo $TPL_VAR["basic"]["shopName"]?>입니다.</td></tr>
	<tr><td class="texts" style="font-size: 12px; color : #616a74;  font-family: 돋움, Dotum; line-height:22px; padding-left:10px;">문의하신 아이디를 알려드립니다.</td></tr>
	<tr><td height="20" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr>
		<td align="center" style="color:#4D4D4D; font-size:12px; line-height:14px;">


		<table width="676" class="info-table-style" style="border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;">
		<col width="20%" /><col width="80%" />
		<tr>
			<th class="its-th" style="color:#4D4D4D; font-size:12px; line-height:14px; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 28px; text-align:left; background-color:#f1f1f1; font-weight:normal;">회원 아이디</th>
			<td class="its-td" style="color:#4D4D4D; font-size:12px; line-height:14px; border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 15px; line-height:180%; letter-spacing:0px;"><?php echo $TPL_VAR["userid"]?></td>
		</tr>
		</table>


		</td>
	</tr>
	<tr><td height="100" style="color:#4D4D4D; font-size:12px; line-height:14px;"></td></tr>
	<tr>
		<td align="center" style="color:#4D4D4D; font-size:12px; line-height:14px;"><a href="<?php echo $TPL_VAR["basic"]["domain"]?>" target="_blank"><img src="/data/mail/btn_go.gif" border="0"></a></td>
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