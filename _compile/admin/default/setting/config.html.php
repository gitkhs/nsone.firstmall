<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/setting/config.html 000028687 */ 
$TPL_pageloop_1=empty($TPL_VAR["pageloop"])||!is_array($TPL_VAR["pageloop"])?0:count($TPL_VAR["pageloop"]);
$TPL_systemmobiles_1=empty($TPL_VAR["systemmobiles"])||!is_array($TPL_VAR["systemmobiles"])?0:count($TPL_VAR["systemmobiles"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<?php if($TPL_VAR["APP_USE"]=='f'){?>
<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '<?php echo $TPL_VAR["APP_ID"]?>', //App ID
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true,  // parse XFBML,
      oauth      : true
    });
    // Additional initialization code here
  };
  // Load the SDK Asynchronously
  (function(d){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/ko_KR/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));
   $(document).ready(function() {
    $(".fb-login-button").click(function(){
    });
   });
		
	 function pagetab(){
	  FB.ui({
		method: 'pagetab',
		redirect_uri: '<?php echo $TPL_VAR["redirect_uri_new"]?>'
	  }, function(response){ 
		  if (response != null && response.tabs_added != null) {
                $.each(response.tabs_added, function(pageid) {
					FB.api(pageid, function(response) {
						var pagename = response.name;
						var pageurl		= response.link;
						var pageapplink		= response.link+"/app_<?php echo $TPL_VAR["APP_ID"]?>";
						$.ajax({
						'url' : '../sns_process/config_facebook_page',
						'type' : 'post',
						'data': {"method":"connect", "pageid":pageid, "pagename":pagename, "pageurl":pageurl, "pageapplink":pageapplink},
						'dataType': 'json',
						'success': function(res) {
						 if(res.result == true) {
							openDialogAlert("성공적으로 설정되었습니다. <br> 이제부터 쇼핑몰의 상품을 facebook에서도 판매할 수 있게 되었습니다.",'480','150',function(){<?php if($TPL_VAR["APP_DOMAIN"]==$_SERVER["HTTP_HOST"]){?>document.location.reload();<?php }?>});
						 }else{
							openDialogAlert(res.msg,'400','140',function(){});
						 }
						}
					   });
					});
                });
            } 
		}); 
	 }
</script>
<?php }?>


<script type="text/javascript">
$(document).ready(function() {

	$("button#configlikebtn").bind("click",function(){
		openDialog("좋아요 버튼 넣기", "configlikepopup", {"width":"600","height":"200"});
	});

	$("button#configfacebookpagebtn").bind("click",function(){
		openDialog("보안 안내란?", "configfacebookpagepopup", {"top":"100","width":"680","height":"800"});
	});




	/* mobile sale 추가 */
	<!-- <?php if(!$TPL_VAR["systemmobiles"]){?> -->
			$("#system_mobile_tbl tbody tr").eq(1).remove();
	<!-- <?php }?> -->
	$("#system_mobile_tbl button#etcAdd").live("click",function(){
		var tblObj = $("#system_mobile_tbl tbody");
		var trObj = $("#system_mobile_tbl tbody tr");
		var rowspannum = parseInt(trObj.length+1);
		$("#system_mobile_tbl tbody tr th").eq(0).attr("rowspan",rowspannum);
		var addtr = "<tr>";
		addtr += "<td class='its-td'><span class='btn-minus'><button type='button' class='etcDel'>-</button></span></td>";
		addtr += "<td class='its-td'> 모바일 또는 태블릿 환경에서 구매 시 &#123;상품 할인가(판매가) x 수량&#125;+&#123;좌동&#125;+&#123;좌동&#125;…이<input type='text' name='mobile_price1[]' value='0' size='6' class='line onlynumber input-box-default-text' /> ~ <input type='text' name='mobile_price2[]' value='0' size='6' class='line onlynumber input-box-default-text' />이면 <br/>";
		addtr += "상품별로 ① 상품 할인가(판매가) x 수량의 <input type='text' name='mobile_sale_price[]' value='0' size='3' class='line onlynumber input-box-default-text' />%추가할인,<br>";
		addtr += "<span style='padding-left:50px;'></span>② 적립금은 실 결제금액의 <input type='text' name='mobile_sale_emoney[]' value='0' size='3' class='line onlynumber input-box-default-text' />% 추가 지급, 지급 적립금의 유효기간은 <?php echo $TPL_VAR["reservetitle"]?><br>";
<?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> addtr += "<span readonly='readonly'  class='gray readonly'    >";<?php }?>
		addtr += "<span style='padding-left:50px;'></span>③ 포인트는 실 결제금액의 <input type='text' name='mobile_sale_point[]' value='0' size='3' class='line onlynumber input-box-default-text' />% 추가 지급, 지급 포인트의 유효기간은 <?php echo $TPL_VAR["pointtitle"]?>";
<?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> addtr += "</span>";<?php }?>
		addtr += "</td>";
		addtr += "</tr>";
		tblObj.append(addtr);
	});

	/* mobile sale  삭제 */
	$("#system_mobile_tbl button.etcDel").live("click",function(){
		var trObj = $("#system_mobile_tbl tbody tr");
		var rowspannum = parseInt(trObj.length-1);
		$("#system_mobile_tbl tbody tr th").eq(0).attr("rowspan",rowspannum);
		if($("#system_mobile_tbl tbody tr").length > 1) $(this).parent().parent().parent().remove();
	});


	/* facebook like sale 추가 */
	<!-- <?php if(!$TPL_VAR["systemfblike"]){?> -->
			$("#system_fblike_tbl tbody tr").eq(1).remove();
	<!-- <?php }?> -->
	$("#system_fblike_tbl button#etcAdd").live("click",function(){
		var tblObj = $("#system_fblike_tbl tbody");
		var trObj = $("#system_fblike_tbl tbody tr");
		var rowspannum = parseInt(trObj.length+1);
		$("#system_fblike_tbl tbody tr th").eq(0).attr("rowspan",rowspannum);
		var addtr = "<tr>";
		addtr += "<td class='its-td'><span class='btn-minus'><button type='button' class='etcDel'>-</button></span></td>";
		addtr += "<td class='its-td'> Like(좋아요)한 상품 구매 시 Like(좋아요)한 상품의 &#123;상품 할인가(판매가) x 수량&#125;이 <input type='text' name='fblike_price1[]' value='0' size='6' class='line onlynumber input-box-default-text' /> ~ <input type='text' name='fblike_price2[]' value='0' size='6' class='line onlynumber input-box-default-text' />이면 <br/> ";
		addtr += "상품별로 ① 상품 할인가(판매가) x 수량의 <input type='text' name='fblike_sale_price[]' value='0' size='3' class='line onlynumber input-box-default-text' />% 추가할인, <br>";
		addtr += "<span style='padding-left:50px;'></span>② 적립금은 실 결제금액의 <input type='text' name='fblike_sale_emoney[]' value='0' size='3' class='line onlynumber input-box-default-text' />% 추가 지급, 지급 적립금의 유효기간은 <?php echo $TPL_VAR["reservetitle"]?><br>";
<?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> addtr += "<span readonly='readonly'  class='gray readonly' >";<?php }?>
		addtr += "<span style='padding-left:50px;'></span>③ 포인트는 실 결제금액의 <input type='text' name='fblike_sale_point[]' value='0' size='3' class='line onlynumber input-box-default-text' />% 추가 지급, 지급 포인트의 유효기간은 <?php echo $TPL_VAR["pointtitle"]?>";
<?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> addtr += "</span>";<?php }?>
		addtr += "</td>";
		addtr += "</tr>";
		tblObj.append(addtr);
	});

	/* facebook like  sale  삭제 */
	$("#system_fblike_tbl button.etcDel").live("click",function(){
		var trObj = $("#system_fblike_tbl tbody tr");
		var rowspannum = parseInt(trObj.length-1);
		$("#system_fblike_tbl tbody tr th").eq(0).attr("rowspan",rowspannum);
		if($("#system_fblike_tbl tbody tr").length > 1) $(this).parent().parent().parent().remove();
	});

	$("#facebookpagepopuplay").live("click",function(){
<?php if($TPL_VAR["APP_DOMAIN"]==$_SERVER["HTTP_HOST"]){?>
			pagetab();
			//window.open('../sns/config_facebook?popup=1&snsreferer=<?php echo $_SERVER["HTTP_HOST"]?>', 'config_facebook', 'width=850px,height=480px,toolbar=no,location=no,resizable=yes, scrollbars=no'); 
			
<?php }elseif($TPL_VAR["APP_DOMAIN"]!=$TPL_VAR["config_system"]["subDomain"]){?>
			window.open('http://<?php echo $TPL_VAR["APP_DOMAIN"]?>/admin/sns/config_facebook?popup=1&snsreferer=<?php echo $_SERVER["HTTP_HOST"]?>&pagetab=1', 'config_facebook', 'width=850px,height=480px,toolbar=no,location=no,resizable=yes, scrollbars=no');
<?php }else{?>
			window.open('http://<?php echo $TPL_VAR["config_system"]["subDomain"]?>/admin/sns/config_facebook?popup=1&snsreferer=<?php echo $_SERVER["HTTP_HOST"]?>', 'config_facebook', 'width=850px,height=480px,toolbar=no,location=no,resizable=yes, scrollbars=no');
<?php }?>
	});

	$("#facebookpagepopuploginlay").live("click",function(){
		facebookLogin();
	});//

	$(".facebookpageconnectdel").click(function() {
		var pageid			= $(this).attr("pageid");
		var pagename		= $(this).attr("pagename");
		var pageurl			= $(this).attr("pageurl");
		var pageapplink	= $(this).attr("pageapplink");

		$.ajax({
		'url' : '../sns_process/config_facebook_page',
		'type' : 'post',
		'data': {"method":"delete","pageid":pageid, "pagename":pagename, "pageurl":pageurl, "pageapplink":pageapplink},
		'dataType': 'json',
		'success': function(res) {
		 if(res.result == true) {
			openDialogAlert('페이지를 해지하였습니다.','400','140',function(){document.location.reload();});
		 }else{
			openDialogAlert(res.msg,'400','140',function(){});
		 }
		}
	   });
	});


	/* ### */
	$("select[name='fblike_reserve_select[]']").live("change",function(){
		span_controllers('fblike', 'reserve');
	});

	$("select[name='fblike_point_select[]']").live("change",function(){
		span_controllers('fblike', 'point');
	});

	$("select[name='mobile_reserve_select[]']").live("change",function(){
		span_controllers('mobile', 'reserve');
	});

	$("select[name='mobile_point_select[]']").live("change",function(){
		span_controllers('mobile', 'point');
	});

	span_controllers('fblike', 'reserve');
	span_controllers('fblike', 'point');
	span_controllers('mobile', 'reserve');
	span_controllers('mobile', 'point');
});

function span_controllers(type, name){
	var nm = type+"_"+name;
	$("select[name='"+nm+"_select[]']").each(function(idx){
		var reserve_y = $("span[name='"+nm+"_y[]']").eq(idx);
		var reserve_d = $("span[name='"+nm+"_d[]']").eq(idx);
		if($(this).val()==""){
			reserve_y.hide();
			reserve_d.hide();
		}else if($(this).val()=="year"){
			reserve_y.show();
			reserve_d.hide();
		}else if($(this).val()=="direct"){
			reserve_y.hide();
			reserve_d.show();
		}
	});
	/*
	$("select[name='"+nm+"_select[]']").each(function(idx){
		var point_y = $("span[name='"+nm+"_y[]']").eq(idx);
		var point_d = $("span[name='"+nm+"_d[]']").eq(idx);
		if($(this).val()==""){
			point_y.hide();
			point_d.hide();
		}else if($(this).val()=="year"){
			point_y.show();
			point_d.hide();
		}else if($(this).val()=="direct"){
			point_y.hide();
			point_d.show();
		}
	});
	*/
}
</script>
<form name="settingForm" id="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/config" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">

<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> 판매환경</h2>
		</div>

		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
		</ul>

	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<!-- 서브 레이아웃 영역 : 시작 -->
<div class="sub-layout-container body-height-resizing">

	<!-- 서브메뉴 탭 : 시작 -->
<?php $this->print_("setting_menu",$TPL_SCP,1);?>

	<!-- 서브메뉴 탭 : 끝 -->

	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">
			<br class="table-gap" />
			<table style="margin:auto;">
			<tr>
				<td><img src="/admin/skin/default/images/common/icon_setting_now.gif" align="absmiddle" hspace="5" /></td>
				<td>
					<span class="bold fx16">현재설정 : </span>
<?php if($TPL_VAR["service_code"]!='P_FAMM'&&$TPL_VAR["config_system"]["subDomain"]){?> <span class="bold fx16 blue" >PC 사용,</span> <?php }else{?><span class="bold fx16 gray" > PC 미사용,</span> <?php }?>
<?php if($TPL_VAR["service_code"]!='P_FAMM'&&$TPL_VAR["config_system"]["subDomain"]){?> <span class="bold fx16 blue" >모바일/태블릿 사용,</span> <?php }else{?><span class="bold fx16 gray" > 모바일/태블릿 미사용,</span> <?php }?>
<?php if($TPL_VAR["service_code"]!='P_FREE'&&$TPL_VAR["facebookConnected"]){?> <span class="bold fx16 blue" >Facebook PC 사용</span> <?php }else{?><span class="bold fx16 gray" > Facebook PC 미사용</span> <?php }?>
				</td>
			</tr>
			</table>
			<br class="table-gap" />


			<style>
			table.platform-info-table-style {border-collapse:collapse;}
			table.platform-info-table-style th {background-color:#f1f1f1; height:38px;}
			table.platform-info-table-style td {padding:0 10px; border:1px solid #dadada; font-size:11px; letter-spacing:-1px;}
			</style>
			<div>
			<table align="center" cellpadding="0" cellspacing="0" border="0" align="center" width="930">
			<tr>
				<td align="left" >

					<div class="item-title">판매환경 설정 <span class="helpicon" title="귀사가 운영할 수 있는 쇼핑몰 플랫폼(판매환경)입니다."></span></div>

					<table class="platform-info-table-style " border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style=" border:0px;padding:5px;" colspan="2"><img src="/admin/skin/default/images/design/img_setting_desktop.gif" /></td>
						<td style=" border:0px;padding:5px;" ><img src="/admin/skin/default/images/design/img_setting_facebook.gif" /></td>
						<td style=" border:0px;padding:5px;" ><img src="/admin/skin/default/images/design/img_setting_mobile.gif" /></td>
					</tr>
					<tr >
						<th style="border:1px solid #dadada;" width="195">구분</th>
						<th  <?php if($TPL_VAR["service_code"]=='P_FAMM'){?> colspan="2" align="center" <?php }?> style="border:1px solid #dadada;  " align="center">PC 또는 노트북 <?php if($TPL_VAR["service_code"]!='P_FAMM'){?> <img src="/admin/skin/default/images/common/icon/icon_live.gif" /><?php }?></th>
						<th style="border:1px solid #dadada;  " align="center"  >페이스북 <br />PC 또는 노트북  <?php if($TPL_VAR["service_code"]!='P_FREE'&&$TPL_VAR["facebookConnected"]){?> <img src="/admin/skin/default/images/common/icon/icon_live.gif" /> <?php }?></th>
<?php if($TPL_VAR["service_code"]!='P_FAMM'){?>
							<th style="border:1px solid #dadada;" align="center">모바일 또는 태블릿 <?php if($TPL_VAR["service_code"]!='P_FAMM'){?> <img src="/admin/skin/default/images/common/icon/icon_live.gif" /><?php }?></th>
<?php }?>
					</tr>
					<tr >
						<th style="border:1px solid #dadada;">
							<div style="font-size:13px;font-weight:bold;text-align:center">소비자는 귀사 쇼핑몰을<br />어디에서 볼 수 있나요?</div>
						</th>
						<td <?php if($TPL_VAR["service_code"]=='P_FAMM'){?> colspan="2" align="center" <?php }?>  >
							<div style=" padding:5px;" >
<?php if($TPL_VAR["service_code"]=='P_FAMM'){?>
								<span id="onlyfacebooklinknone" config="1" class="hand" > 업그레이드 안내</span>
<?php }else{?>
								(임시)
								<span style="letter-spacing:0px;">http://<?php echo $TPL_VAR["config_system"]["subDomain"]?></span><br />
								<br />
								(정식) <span style="letter-spacing:0px;"><?php if($TPL_VAR["config_system"]["domain"]){?> http://<?php echo $TPL_VAR["config_system"]["domain"]?><?php }?></span>
								<br />
								<span class="gray">정식도메인은 <a href="http://firstmall.kr/myshop" target="_blank" style="color:#cd500b; text-decoration:underline">마이가비아</a>에서 연결신청하세요.</span>
<?php }?>
							</div>
						</td>

						<td>
							<div style=" padding:10px 5px 10px 8px;">
<?php if($TPL_VAR["service_code"]!='P_FREE'){?>
<?php if($TPL_VAR["APP_USE"]=='f'){?><span class="btn small cyanblue"><button type="button" id="facebookpagepopuplay">설정</button></span><br /><?php }?>
								<div id="snsdiv_f" style="width:210px;word-break:break-all">
									<ul >
<?php if($TPL_pageloop_1){$TPL_I1=-1;foreach($TPL_VAR["pageloop"] as $TPL_V1){$TPL_I1++;?>
										<li style="padding:0 5px;">
										<input type="hidden" name="page_id_f" value="<?php echo $TPL_V1["page_id_f"]?>" size="40"  />
										<input type="hidden" name="page_url_f" value="<?php echo $TPL_V1["page_url_f"]?>" size="100"  />
										<input type="hidden" name="page_name_f" value="<?php echo $TPL_V1["page_name_f"]?>" size="40"  />
										<?php echo ($TPL_I1+ 1)?>. <?php if($TPL_V1["page_name_f"]){?>[<?php echo $TPL_V1["page_name_f"]?>]<?php }?>
<?php if($TPL_V1["page_app_link_f"]){?>
											<span class="btn small"><input type="button" value="facebook" onclick="window.open('<?php echo $TPL_V1["page_app_link_f"]?>','facebookpage');"/></span>
											<input type="hidden" name="page_app_link_f" value="<?php echo $TPL_V1["page_app_link_f"]?>" size="100"  />

											<span class="btn small"><input type="button" value="해제"  <?php if($TPL_VAR["isdemo"]["isdemo"]){?> <?php echo $TPL_VAR["isdemo"]["isdemojs1"]?> <?php }else{?>  class="facebookpageconnectdel" <?php }?>  pageid="<?php echo $TPL_V1["page_id_f"]?>"  pagename="<?php echo $TPL_V1["page_name_f"]?>"  pageurl="<?php echo $TPL_V1["page_url_f"]?>"  pageapplink="<?php echo $TPL_V1["page_app_link_f"]?>" ></span>
											<div style="padding-top:3px;"><a href="<?php echo $TPL_V1["page_app_link_f"]?>"  target="_blank" style="color:#cd500b; text-decoration:underline">연결된 페이스북 페이지 바로가기 ></a><div>
<?php }elseif($TPL_V1["page_url_f"]){?>
											<span class="btn small"><input type="button" value="facebook" onclick="window.open('<?php echo $TPL_V1["page_url_f"]?>?sk=app_<?php echo $TPL_V1["key_f"]?>','facebookpage');"/></span>
											<span class="btn small"><input type="button" value="해제" class="facebookpageconnectdel" pageid="<?php echo $TPL_V1["page_id_f"]?>"  pagename="<?php echo $TPL_V1["page_name_f"]?>"  pageurl="<?php echo $TPL_V1["page_url_f"]?>"  pageapplink="<?php echo $TPL_V1["page_url_f"]?>?sk=app_<?php echo $TPL_V1["key_f"]?>" ></span>
											<br/><a href="<?php echo $TPL_V1["page_url_f"]?>?sk=app_<?php echo $TPL_V1["key_f"]?>" target="_blank" style="color:#cd500b; text-decoration:underline">연결된 페이스북 페이지 바로가기 ></a><br/>
<?php }?>
										</li>
<?php }}?>
									</ul>
								</div>
<?php }else{?>
								<span class="desc" style="font-weight:normal">페이스북 쇼핑몰 운영은<br />업그레이드가 필요합니다.</span><br /><img src='/admin/skin/default/images/common/btn_upgrade.gif' class='hand' onclick='serviceUpgrade();' align='absmiddle' />
<?php }?>
							</div>
						</td>
<?php if($TPL_VAR["service_code"]!='P_FAMM'){?>
							<td  class="left"  >
								<div style="margin-left:10px;  padding:5px;">
									(임시)
									<span style="letter-spacing:0px;">http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?></span><br />
									<br />
<?php if($TPL_VAR["config_system"]["domain"]){?>
									<a href="http://m.<?php echo str_replace("www.","",$TPL_VAR["config_system"]["domain"])?>" target="_blank" style="color:#000;">(정식) <span style="letter-spacing:0px;">http://m.<?php echo str_replace("www.","",$TPL_VAR["config_system"]["domain"])?></span></a>
<?php }else{?>
									<span class="desc">(정식) http://m.도메인</span>
<?php }?>

									<br />
									<span class="gray">PC의 정식도메인 연결신청 시 자동 생성됨</span>
								</div>
							</td>
<?php }?>
					</tr>
					<tr>
						<th class="right"  style="border:1px solid #dadada; padding:5px;">
							<div style="font-size:13px;font-weight:bold;text-align:center">소비자는 귀사 쇼핑몰에서<br />SNS 및 외부 연동 기능을<br />이용할 수 있나요?
							</div>
						</th>
						<td class="left" colspan="3">
							<div style="padding:10px;line-height:26px;">
								소비자는 귀사의 PC, FACEBOOK, MOBILE/TABLET 사이트에서 ↓ 아래의 SNS 및 외부 연동 기능을 이용할 수 있습니다.
								<ul style="list-style-type:square;margin-left:18px;line-height:24px;">
								<li>
									<img src="/admin/skin/default/images/sns/sns_f0.gif" alt="페이스북" title="페이스북" align="absmiddle"/>
									<img src="/admin/skin/default/images/sns/sns_t0.gif" alt="트위터" title="트위터" align="absmiddle"/>
									<img src="/admin/skin/default/images/sns/sns_k0.gif" alt="카카오" title="카카오" align="absmiddle"/>
									<img src="/admin/skin/default/images/sns/sns_n0.gif" alt="네이버" title="네이버" align="absmiddle"/>
									<img src="/admin/skin/default/images/sns/sns_c0.gif" alt="싸이월드" title="싸이월드" align="absmiddle"/>
									<img src="/admin/skin/default/images/sns/sns_d0.gif" alt="다음(daum)" title="다음(daum)" align="absmiddle"/> 로 가입하고 로그인
								</li>
								<li><img src="/admin/skin/default/images/sns/sns_f0.gif" alt="페이스북" title="페이스북" align="absmiddle"/> '좋아요'하고 추가 할인 받기</li>
								<li><img src="/admin/skin/default/images/sns/sns_f0.gif" alt="페이스북" title="페이스북" align="absmiddle"/> 친구들을 초대하고 적립금 받기</li>
								<li>위시리스트에 담은 상품을, '좋아요'한 상품을, 구매하려는 상품을, 작성한 리뷰를 <img src="/admin/skin/default/images/sns/sns_f0.gif" alt="페이스북" title="페이스북" align="absmiddle"/>친구들에게 바로 알리기</li>
								<li>
									<img src="/admin/skin/default/images/board/icon/sns_f0.gif" alt="페이스북" title="페이스북" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_t0.gif" alt="트위터" title="트위터" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_g0.gif" alt="구글플러스" title="구글플러스" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_my0.gif" alt="마이피플" title="마이피플" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_c0.gif" alt="싸이월드" title="싸이월드" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_k0.gif" alt="카카오톡" title="카카오톡" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_ks0.png" alt="카카오스토리" title="카카오스토리" align="absmiddle"/>
									<img src="/admin/skin/default/images/board/icon/sns_ln0.png" alt="라인" title="라인" align="absmiddle"/>
									에 상품, 게시물 이벤트 공유하기
								</li>
								</ul>
								↑ 위 기능은 설정 &gt; <a href="snsconf" target="_blank" style="color:#cd500b; text-decoration:underline">SNS/외부연동</a>에서 설정할 수 있습니다.
							</div>
						</td>
					</tr>
					<tr>
					<td class="left" colspan="4" style=" border:0px;padding:5px;" >
						<div >
						<span class="darkgray" style="line-height:20px;">
							※ 페이스북 쇼핑몰은 설정(앱을 페이스북 페이지에 연결)해야만 보여집니다. 계속 설정하면 여러 개의 페이스북 페이지에 연결할 수도 있습니다.<br />
							※ 페이스북 모바일의 뉴스피드 또는 프로필에서 보여지는 상품을 클릭 시 모바일 쇼핑몰로 이동합니다.<br />
							※ 관리환경 우측 상단 &gt; 디자인 &gt; 디자인환경(PC) 또는 디자인환경(Mobile/Tablet) 또는 디자인환경(Facebook PC) 에서 판매환경별 디자인을 할 수 있습니다.<br />
						</span>
						</div>
					</td></tr>
					</table>
				</td>
			</tr>
			</table>
			</div>
			<a name="config_sales"></a>

			<br class="table-gap" /><br class="table-gap" />
			<table  align="center" cellpadding="0" cellspacing="0" border="0"  width="930" >
			<tr>
				<td valign="top" width="100%" >
				<table width="100%" class="info-table-style" id="system_mobile_tbl" <?php if($TPL_VAR["service_code"]=='P_FAMM'){?> disabled="disabled" <?php }?>>
					<colgroup><col width="230" /><col width="40" /><col width="" /></colgroup>
					<tbody>
						<tr>
							<th class="its-th" rowspan="<?php echo count($TPL_VAR["systemmobiles"])+ 1?>">모바일/태블릿에서 구매 시<br/> 상품별 혜택 설정
							
							<div>
								<span class="btn small orange addsaleGuideBtn "><button type="button" class="hand" >안내) 추가 혜택 적용 범위</button></span>
							</div>
							<div>
								<span class="gray">
									※ 모바일/태블릿에서 발생한 주문은 주문리스트에서 표시되며, 검색 가능
								</span>
							</div>
							</th>
							<td class="its-td" valign="top"><span class="btn-plus"><button type="button" <?php if($TPL_VAR["service_code"]!='P_FAMM'){?> id="etcAdd" <?php }?> >+</button></span>
							</td>
							<td class="its-td" valign="top">'+'을 클릭하여 모바일/태블릿 환경에서 주문하는 소비자(구매자)를 위한 추가혜택을 설정할 수 있습니다.<br>
							※ 적립금 및 포인트 지급 시점은 관리환경 > 설정 > <a href="/admin/setting/reserve" target="_blank"><span class=" highlight-link hand">적립금/포인트/이머니</span></a>에 따릅니다.<br>
							※ 상품 실 결제금액 = &#123;상품 할인가(판매가) x 수량&#125; – 할인(쿠폰,등급,좋아요,모바일,프로모션코드)
							</td>
						</tr>
<?php if($TPL_VAR["systemmobiles"]){?>
<?php if($TPL_systemmobiles_1){foreach($TPL_VAR["systemmobiles"] as $TPL_V1){?>
						<tr>
							<td class="its-td"><span class="btn-minus"><button type="button" class="etcDel" >-</button></span></td>
							<td class="its-td"> 모바일 또는 태블릿 환경에서 구매 시 <span style="color:red;">&#123;상품 할인가(판매가) x 수량&#125;+&#123;좌동&#125;+&#123;좌동&#125;</span>…이<input type="text" name="mobile_price1[]" value="<?php echo $TPL_V1["price1"]?>" size="6" class="line onlynumber input-box-default-text" /> ~ <input type="text" name="mobile_price2[]" value="<?php echo $TPL_V1["price2"]?>" size="6" class="line onlynumber input-box-default-text" />이면
								<br/>
								상품별로 ① 상품 할인가(판매가) x 수량의 <input type="text" name="mobile_sale_price[]" value="<?php echo $TPL_V1["sale_price"]?>" size="3" class="line onlynumber input-box-default-text" />%추가할인, <br>
								<span style="padding-left:50px;"></span>② 적립금은 실 결제금액의 <input type="text" name="mobile_sale_emoney[]" value="<?php echo $TPL_V1["sale_emoney"]?>" size="3" class="line onlynumber input-box-default-text" />% 추가 지급, 지급 적립금의 유효기간은 <?php echo $TPL_VAR["reservetitle"]?>

								<br>
								<span <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?>  > <span style="padding-left:50px;"></span>③ 포인트는 실 결제금액의 <input type="text" name="mobile_sale_point[]" value="<?php echo $TPL_V1["sale_point"]?>" size="3" class="line onlynumber input-box-default-text" />% 추가 지급, 지급 포인트의 유효기간은 <?php echo $TPL_VAR["pointtitle"]?>

							</td>
						</tr>
<?php }}?>
<?php }?>
					</tbody>
				</table>

			</td>
			</tr>
			</table>




		</div>
	</div>
	<!-- 서브메뉴 바디 : 끝 -->
</div>
<!-- 서브 레이아웃 영역 : 끝 -->
</form>


<div id="configlikepopup" class="hide">
	<div style="padding-bottom:10px;">우선 설정 > <a href="./snsconf#likebox" target="_blank"><span class=" highlight-link hand">SNS마케팅</span></a> 메뉴에서 좋아요 사용여부를 확인해 주세요.</div>
 Q. 메인페이지처럼 상품이 보여지는 상품리스트에서 상품마다 좋아요 버튼을 넣고 싶어요!<br/>
 A. EYE-DESIGN > 상품디스플레이 에서 좋아요 버튼을 노출 시키세요.<br/>
 <br/>
 Q. 상품상세페이지에서 해당 상품의 좋아요 버튼을 넣고 싶어요!<br/>
 A. 자동으로 보여집니다. 물론, 좋아요를 했을 때의 혜택도 자동으로 안내되어집니다.
</div>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>