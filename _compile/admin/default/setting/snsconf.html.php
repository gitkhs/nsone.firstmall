<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/setting/snsconf.html 000081116 */  $this->include_("snsLikeButton","snslinkurl");
$TPL_systemfblike_1=empty($TPL_VAR["systemfblike"])||!is_array($TPL_VAR["systemfblike"])?0:count($TPL_VAR["systemfblike"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<script type="text/javascript" src="/app/javascript/plugin/zeroclipboard/ZeroClipboard.js"></script>
<script type="text/javascript" src="/app/javascript/plugin/jquploadify/swfobject.js"></script>

<style>
</style>

<script type="text/javascript">
	function fbliketdcss(disabledlay, disablednolay){

		//숨김
		$("."+disabledlay).each(function(){
			$(this).css("background-color","#eeeeee");
			$(this).attr("disabled",true);
			$(this).find("input:radio").attr("disabled",true);
			$(this).find(".desc").hide();
<?php if($TPL_VAR["sns"]["facebook_app"]=='new'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
				$("input[name='fb_like_box_type'][value='OP']").attr("checked",true);
<?php }else{?>
				$("input[name='new_fb_like_box_type'][value='NO']").attr("checked",true);
<?php }?>
		});

		//노출
		$("."+disablednolay).each(function(){
			$(this).css("background-color","#ffffff");
			$(this).attr("disabled",false);
			$(this).find("input:radio").attr("disabled",false);
			$(this).find(".desc").show();
		});

		fblikeSalePrice();
	}

	function fblikeSalePrice(){
<?php if($TPL_VAR["sns"]["facebook_app"]=='basic'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
		var fblikeObj = $("input[name='fb_like_box_type'][value='NO']");
<?php }else{?>
		var fblikeObj = $("input[name='new_fb_like_box_type'][value='NO']");
<?php }?>
		// 좋아요 사용안할시 혜택 부분 disable
		if(fblikeObj.is(":checked") == true){

			$("#system_fblike_tbl").addClass("desc");
			//$("#system_fblike_tbl span").addClass("desc");
			$(".btn-plus").css("display","none");
			$(".btn-minus").css("display","none");

			$("input[name='fblike_ordertype']").attr("disabled",true);
			$("input[name='fblike_price1[]']").each(function(i){ $(this).attr("disabled",true); });
			$("input[name='fblike_price2[]']").each(function(i){ $(this).attr("disabled",true); });
			$("input[name='fblike_sale_price[]']").each(function(i){ $(this).attr("disabled",true); });
			$("input[name='fblike_sale_emoney[]']").each(function(i){ $(this).attr("disabled",true); });
			$("input[name='fblike_sale_point[]']").each(function(i){ $(this).attr("disabled",true); });

		}else{

			$("#system_fblike_tbl").removeClass("desc");
			$("#system_fblike_tbl span").removeClass("desc");
			
			$(".btn-plus").css("display","block");
			$(".btn-minus").css("display","block");

			$("input[name='fblike_ordertype']").attr("disabled",false);
			$("input[name='fblike_price1[]']").each(function(i){
				$(this).attr("disabled",false);
			});
			$("input[name='fblike_price2[]']").each(function(i){
				$(this).attr("disabled",false);
			});
			$("input[name='fblike_sale_price[]']").each(function(i){
				$(this).attr("disabled",false);
			});
			$("input[name='fblike_sale_emoney[]']").each(function(i){
				$(this).attr("disabled",false);
			});
			$("input[name='fblike_sale_point[]']").each(function(i){
				$(this).attr("disabled",false);
			});

		}
	}


	function fblikeiconFileUpload(str){
		if(str > 0) {
			alert('아이콘을 선택해 주세요.');
			return false;
		}
		var frm = $('#fblikeiconRegist');
		frm.attr("action","../setting_process/fblikeiconUpload");
		frm.submit();
	}


	function fblikeiconDisplay(filenm){
		$(".fb-og-like-imglike").attr("src",filenm);
			$('#fblikeiconRegist')[0].reset();
		$("#fblikeiconPopup").dialog("close");
	}

	function fbunlikeiconFileUpload(str){
		if(str > 0) {
			alert('아이콘을 선택해 주세요.');
			return false;
		}
		var frm = $('#fbunlikeiconRegist');
		frm.attr("action","../setting_process/fbunlikeiconUpload");
		frm.submit();
	}

	function snslogoFileUpload(str){
		if(str > 0) {
			alert('로고를 선택해 주세요.');
			return false;
		}
		var frm = $('#snslogoRegist');
		frm.attr("action","../setting_process/snsconf_snslogo");
		frm.submit();
	}

	function snslogoDisplay(filenm){
		$(".snslogo_img").attr("src",filenm);
		$(".snslogo_img").css("display","block");
		$(".snslogo_img").css("width","100");
		$("#snslogomsg").css("display","none");
		$("#snslogoDelete").css("display","block");
		$('#snslogoRegist')[0].reset();
		$("#snslogoUpdatePopup").dialog("close");
	}

	function fbunlikeiconDisplay(filenm){
		$(".fb-og-like-imgunlike").attr("src",filenm);
			$('#fbunlikeiconRegist')[0].reset();
		$("#fbunlikeiconPopup").dialog("close");
	}

	function snsjoinDisplay(sns){
		$(".snslogo_img").attr("src",filenm);
		$(".snslogo_img").css("display","block");
		$(".snslogo_img").css("width","100");
		$("#snslogomsg").css("display","none");
		$("#snslogoDelete").css("display","block");
		$('#snslogoRegist')[0].reset();
		$("#snslogoUpdatePopup").dialog("close");
	}

	$(document).ready(function() {

		/*
		$("#snsconfigkakaotalkbtn").live("click",function(){ 
			openDialog("Key 확인방법 ", "snsconfigkakaotalklay", {"width":"555","height":"685","show" : "fade","hide" : "fade"});
		});
		*/

<?php if($TPL_VAR["sns"]["facebook_app"]=='new'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
			fbliketdcss('fb_like_basic','fb_like_new');
<?php }else{?>
			fbliketdcss('fb_like_new','fb_like_basic');
<?php }?>

		$("#snsfbliketypebtn").click(function(){
			openDialog("연결방식의 차이점", "snsfbliketypelay", {"width":"805","height":"260"});
		});

		$("button#fblikeiconBtn").live("click",function(){
			$('#fblikeiconRegist')[0].reset();
			openDialog("좋아요 했을때 아이콘 <span class='desc'>등록 이미지 사이즈 60X20 를 등록해 주세요.</span>", "fblikeiconPopup", {"width":"460","height":"130","show" : "fade","hide" : "fade"});
		});

		$("button#fbunlikeiconBtn").live("click",function(){
			$('#fbunlikeiconRegist')[0].reset();
			openDialog("좋아요 안했을때 아이콘 <span class='desc'>등록 이미지 사이즈 60X20를 등록해 주세요.</span>", "fbunlikeiconPopup", {"width":"480","height":"130","show" : "fade","hide" : "fade"});
		});
		
		$("button#snslogoUpdate").live("click",function(){
			$('#fbunlikeiconRegist')[0].reset();
			openDialog("Fammerce 쇼핑몰 로고", "snslogoUpdatePopup", {"width":"380","height":"150","show" : "fade","hide" : "fade"});
		});

		$("button#snsmetaTagUpdate").live("click",function(){
			$('#fbunlikeiconRegist')[0].reset();
			openDialog("Fammerce 쇼핑몰 소개", "snsmetaTagUpdatePopup", {"width":"400","height":"335","show" : "fade","hide" : "fade"});
		});

		$("#snslogoDelete").live("click",function(){
			var url = '../setting_process/snslogo_delete';//favicon_delete
			var obj = $(this);
			$.getJSON(url, function(data) {
				if(data['result'] == 'ok'){
					obj.css("display","none");
					$(".snslogo_img").css("display","none");
					$("#snslogomsg").css("display","block");
					//obj.parent().remove();
				}
			});
		});


		 $("#snsy").click(function(){
			 $("#usesnstypelay").attr("disabled",false);
		 });

		 $("#snsn").click(function(){
			 $("#usesnstypelay").attr("disabled",false);
		 });
		
		//짧은주소 관련
		/*
		 $("input[name='shorturl_use']").click(function(){
			 if( $("input[name='shorturl_use']:checked").val() == "Y" ) {
				 $("#shorturl_help_lay").show();
			 }else{
				 $("#shorturl_help_lay").hide();
			 }
		 });
		 */
		//짧은주소 관련
		 $("input[name='shorturl_use']").click(function(){
			 if( $("input[name='shorturl_use']:checked").val() == "Y" ) {
				 $(".btnshorturl").show();
			 }else{
				 $(".btnshorturl").hide();
			 }
		 });


		 //카카오톡 관련
		 $("input[name='kakaotalk_use']").click(function(){
			 if( $("input[name='kakaotalk_use']:checked").val() == "Y" ) {
				 $("#kakaotalk_help_lay").show();
			 }else{
				 $("#kakaotalk_help_lay").hide();
			 }
		 });

		//좋아요박스 방식
		 $("input[name='fb_like_box_type']").click(function(){
			 if( $("input[name='fb_like_box_type']:checked").val() == "OP" ) {
				 $("#fb_like_opengrapy_lay").show();
			 }else{
				 $("#fb_like_opengrapy_lay").hide();
			 }
			 fblikeSalePrice();
		 });

		//좋아요박스 방식
		 $("input[name='new_fb_like_box_type']").click(function(){
			 if( $("input[name='new_fb_like_box_type']:checked").val() == "OP" ) {
				 $("#new_fb_like_opengrapy_lay").show();
			 }else{
				 $("#new_fb_like_opengrapy_lay").hide();
			 }
			 fblikeSalePrice();
		 });

		 //좋아요아이콘삭제
		$("#fblikeboxiconDelete, #fbunlikeboxiconDelete,#new_fblikeboxiconDelete, #new_fbunlikeboxiconDelete").live("click",function(){
			var url = '../setting_process/fblike_delete?fblikemode='+$(this).attr("fblikemode");
			var obj = $(this);
			$.getJSON(url, function(data) {
				if(data['result'] == 'ok'){
					obj.parent().remove();
				}
			});
		});

		$("button#configfacebookpagebtn").bind("click",function(){
			openDialog("보안 안내란?", "configfacebookpagepopup", {"top":"100","width":"680","height":"800"});
		});


		$("button#snsconfigshare").bind("click",function(){
			var data = $("#snsconfigfacebookurllay").html();
			$('#popup').html(data);
			openDialog("쇼핑몰 정보 푸시 활용방법", "popup", {"width":"370","height":"250"});
		});

		var tagCopyClips = [];
		$("button#snsconfigurl").bind("click",function(){
			openDialog("쇼핑몰 정보 공유 활용방법", "snsconfigsharelinklay", {"width":"1024","height":"400"});

			$(".copy_snstag_btn").each(function(i){
				if($("#copy_snstag_btn_"+i).length) return;

				$(this).attr("id","copy_snstag_btn_"+i);
				tagCopyClips[i] = new ZeroClipboard.Client();
				tagCopyClips[i].setHandCursor( true );
				tagCopyClips[i].setCSSEffects( true );
				tagCopyClips[i].setText($(this).attr('code'));
				tagCopyClips[i].addEventListener( 'complete', function(client, text) {
					alert("클립보드에 저장되었습니다.");
				} );
				tagCopyClips[i].glue("copy_snstag_btn_"+i);

				$("#ZeroClipboardMovie_"+(i+1)).parent().css({
						'left' : $("#copy_snstag_btn_"+i).position().left,
						'top' : $("#copy_snstag_btn_"+i).position().top,
						'z-index':10100
				});

				$("#copy_snstag_btn_"+i).after($("#ZeroClipboardMovie_"+(i+1)).parent());
			});
		});

		$(".story_ad_banner").bind("click",function(){
			var storynumber = $(this).attr("story");
			popup('http://firstmall.kr/ec_hosting/shop/story_ad_popup.php?no='+storynumber,'760','470');
		});

<?php if($TPL_VAR["sns"]["facebook_review"]=='Y'){?>
			$("input[name='facebook_review']").attr('checked',true);
<?php }?>
<?php if($TPL_VAR["sns"]["facebook_interest"]=='Y'){?>
			$("input[name='facebook_interest']").attr('checked',true);
<?php }?>

<?php if($TPL_VAR["sns"]["facebook_buy"]=='Y'){?>
			$("input[name='facebook_buy']").attr('checked',true);
<?php }?>

		//getshorturl();

		//fammerce market url 설정
		/*
		$("#shorturl_guide").on("click",function(){
			openDialog("Fammerce Plus 짧은 URL 변환 설정 안내", "shorturl_help_guide",{"width":"650","height":"230","show" : "fade","hide" : "fade"});
		});
		*/
		
		//전용앱 사용 선택시
		$("select[name='app_gubun']").on("change",function(){
			 var app_gubun = $(this).val();
			 if(app_gubun == "new"){
				 openDialog("전용 앱 신청 안내", "app_private_guide",{"width":"530","height":"200","show" : "fade","hide" : "fade"});
			 }
		 });

		/* facebook like sale 추가 */
		<!-- <?php if(!$TPL_VAR["systemfblike"]){?> -->
				$("#system_fblike_tbl tbody tr").eq(1).remove();
		<!-- <?php }?> -->
		$("#system_fblike_tbl button#etcAdd").live("click",function(){
			var tblObj		= $("#system_fblike_tbl tbody");
			var trObj		= $("#system_fblike_tbl tbody tr");
			var rowspannum	= parseInt(trObj.length+1);

			//좋아요 사용안할시 혜택 추가하더라도 disabled 시키기
<?php if($TPL_VAR["sns"]["facebook_app"]=='basic'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
			var fblikeObj = $("input[name='fb_like_box_type'][value='NO']");
<?php }else{?>
			var fblikeObj = $("input[name='new_fb_like_box_type'][value='NO']");
<?php }?>
			var disabled = "";
			if(fblikeObj.is(":checked") == true){
				disabled = "disabled";
			}

			$("#system_fblike_tbl tbody tr th").eq(0).attr("rowspan",rowspannum);
			var addtr = "<tr>";
			addtr += "<td class='its-td'><span class='btn-minus'><button type='button' class='etcDel'>-</button></span></td>";
			addtr += "<td class='its-td fx11'> Like(좋아요)한 상품 구매 시 Like(좋아요)한 상품의 <br /><span style='color:red;'>&#123;상품 할인가(판매가) x 수량&#125;</span>이 <input type='text' name='fblike_price1[]' value='0' size='8' class='line onlynumber input-box-default-text' "+disabled+" style='text-align:right;' /> ~ <input type='text' name='fblike_price2[]' value='0' size='8' class='line onlynumber input-box-default-text' "+disabled+" style='text-align:right;' />이면 <br/> ";
			addtr += "상품별로 ① 상품 할인가(판매가) x 수량의 <input type='text' name='fblike_sale_price[]' value='0' size='3' class='line onlynumber input-box-default-text' "+disabled+" style='text-align:right;' />% 추가할인, <br>";
			addtr += "<span style='padding-left:10px;'></span>② 적립금은 실 결제금액의 <input type='text' name='fblike_sale_emoney[]' value='0' size='3' class='line onlynumber input-box-default-text' "+disabled+" style='text-align:right;' />% 추가 지급, 지급 적립금의 유효기간은 <?php echo $TPL_VAR["reservetitle"]?><br>";
<?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> addtr += "<span readonly='readonly'  class='gray readonly' >";<?php }?>
			addtr += "<span style='padding-left:10px;'></span>③ 포인트는 실 결제금액의 <input type='text' name='fblike_sale_point[]' value='0' size='3' class='line onlynumber input-box-default-text' "+disabled+" style='text-align:right;' />% 추가 지급, 지급 포인트의 유효기간은 <?php echo $TPL_VAR["pointtitle"]?>";
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

		$(".detailview").on("click",function(){
			var mode	= $(this).attr("gb");
			var title	= "";
			var w		= 980;
			var h		= 600;
			switch(mode){
				case "facebook":	title = "페이스북 쇼핑몰"; h = 480;break;
				case "login":		title = "가입 및 로그인"; w = 1000; break;
				case "goodpoint":	title = "좋아요"; break;
				case "friend":		title = "친구초대"; break;
				case "autoview":	title = "활동 공유 (오픈 그래프)"; break;
				case "share":		title = "정보 공유 (퍼가기)"; w = "1050"; break;
				case "push":		title = "상품 미리보기"; break;
			}

			var openObj = "snsconf_detail";
			if($("#snsconf_detail").attr("mode") == mode && $("#snsconf_detail").html() !=''){
				openDialog(title,openObj, {"width":w,"height":h,"show" : "fade","hide" : "fade"});
			}else{
				$.get('../setting/snsconf_detail?mode='+mode, function(data) {
					//console.log(data);
					$("#snsconf_detail").html(data);
					$("#snsconf_detail").attr("mode",mode);
					openDialog(title,openObj, {"width":w,"height":h,"show" : "fade","hide" : "fade"});
				});
			}
			
		});

		/* 메타태그(sns 소개) 저장 */
		$("#btnmetatag").on("click",function(){

			var descript = $("#metaTagDescription").val();
			var keyword  = $("#metaTagKeyword").val();

			var data = {"metaTagDescription":descript,"metaTagKeyword":keyword}
			$.ajax({
				'url' : '../setting_process/snsconf_snsmetatag',
				'type' : 'post',
				'data': data,
				'dataType': 'json',
				'success': function(res) {
					openDialogAlert("저장 되었습니다.",400,140,'parent','');
					$("#vmetaTagDescription").html(descript);
					$("#vmetaTagKeyword").html(keyword);
					$("#snsmetaTagUpdatePopup").dialog("close");
				}
				,'error': function(e){
				}
			});
		});

		/* sns 로그인/회원가입 설정창 띄우기 */
		$(".cyworldconflay").click(function() {
			openDialog("<img src=\"/admin/skin/default/images/sns/sns_c0.gif\"  alt=\"cyworld\"   align=\"absmiddle\" /> 싸이월드 설정하기", "snsdiv_c", {"width":"700","height":"270","show" : "fade","hide" : "fade"});
		});

		$(".naverconflay").click(function() {
			openDialog("<img src=\"/admin/skin/default/images/sns/sns_n0.gif\" alt=\"naver\"  align=\"absmiddle\" /> 네이버 설정하기", "snsdiv_n", {"width":"700","height":"270","show" : "fade","hide" : "fade"});
		});

		$(".kakaoconflay").click(function() {
			openDialog("<img src=\"/admin/skin/default/images/sns/sns_k0.gif\" alt=\"kakao\"  align=\"absmiddle\" /> 카카오 설정하기", "snsdiv_k", {"width":"700","height":"270","show" : "fade","hide" : "fade"});
		});

		$(".daumconflay").click(function() {
			openDialog("<img src=\"/admin/skin/default/images/sns/sns_d0.gif\" alt=\"daum\"  align=\"absmiddle\" /> 다음(Daum) 설정하기", "snsdiv_d", {"width":"700","height":"270","show" : "fade","hide" : "fade"});
		});
		
		// shorturl url 설정
		$(".shorturlConfig").click(function() {
			openDialog("짧은 URL 설정", "shorturl_help_lay", {"width":"500","height":"260","show" : "fade","hide" : "fade"});
		});


	});

	function getshorturl() {
		$.ajax({
		'url' : '../setting_process/shorturl',
		'type' : 'get',
		'data': { "shorturl_test":"<?php echo $TPL_VAR["shorturl_test"]?>"},
		'dataType': 'json',
		'success': function(res) {
			var  shorturllaytag = '예) <a href="'+res.resulturl+'" target="blank">'+res.resulturl+'</a>';
			$(".shorturllay").html(shorturllaytag);
		}
	   });
	}
</script>
<div id="popup" class="hide"></div>
<form name="settingForm" method="post" enctype="multipart/form-data" action="../setting_process/snsconf" target="actionFrame">
<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar" class="gray-bar">
<?php $this->print_("require_info",$TPL_SCP,1);?>


		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="darkgray">설정 →</span> SNS/외부연동</h2>
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
	<style >
	.snsconftitle {color:#000;font-weight:bold;letter-spacing:-1px !important;font-size:14px;font-family:'돋움',Dotum,AppleGothic,sans-serif;margin-left:3px}
	.dialogDiv { font-size:12px;display:none; }
	.dialogDiv .cont1 {margin:0px auto;width:940px;text-align:center;}
	.dialogDiv .cont1 p {text-align:left; line-height:20px;}
	.dialogDiv .cont1 p.tx1 {color:#696969;margin-top:15px;}
	.dialogDiv .cont1 p.tx2 {font-size:14px;font-weight:bold;line-height:22px;}
	.dialogDiv .cont1 .popimg {margin-top:49px;}
	.dialogDiv .cont1 .psbox1 {margin-top:29px;background-color:#f4f4f4;line-height:12px;font-size:11px;width:100%;text-align:left;}
	.dialogDiv .cont1 .psbox1 ul { padding:10px;list-style-type:disc;margin-left:24px; }
	.dialogDiv .cont1 .psbox1 li {padding:3px;}
	.dialogDiv .cont1 .psbox2 {margin-top:29px;background-color:#fff;line-height:12px;font-size:11px;width:100%;text-align:left;}
	.info-table-style th{height:28px;}
	button.copy_snstag_btn {font-size:11px;line-height:14px;}
	.dialogDiv .cont1 .sns_icon {margin:4px 1px 4px 1px;}
	.none { color:#a1a1a1; }
	</style>
	<!-- 서브메뉴 바디 : 시작-->
	<div class='slc-body-wrap'>
		<div class="slc-body">
			<br class="table-gap" />

			<div style="text-align:center;margin-top:25px;margin-bottom:15px;"><img src="/admin/skin/default/images/sns/sns_img.gif" /></div>

			<table align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0px auto;width:90%;">
			<tr>
				<td><div class="item-title">SNS 및 외부 연동 기능 안내</div></td>
				<td align="right" style="letter-spacing:-1px;font-size:11px;">
					<div style="margin-top:20px;"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"  align="absmiddle"> : 앱 방식으로 동작하는 기능</div>
				</td>
			</tr>
			<tr>
				<td  align="center" colspan="2" style="text-align:center;">
				<!-- SNS 및 외부 연동 기능 안내 시작 -->
					<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="info-table-style">
					<col width="12%" /><col width="12.5%" /><col width="12.5%" />
					<col width="12.5%" /><col width="12.5%" /><col width="12.5%" /><col width="12.5%" />
					<tr>
						<th class="its-th-align-dashed" rowspan="2">구분</th>
						<th class="its-th-align-dashed" >페이스북 쇼핑몰</th>
						<th class="its-th-align-dashed" >가입 및 로그인</th>
						<th class="its-th-align-dashed" >좋아요</th>
						<th class="its-th-align-dashed" >친구초대</th>
						<th class="its-th-align-dashed" >활동공유<br />(오픈그라피)</th>
						<th class="its-th-align-dashed" >정보공유<br />(퍼가기)</th>
						<th class="its-th-align-dashed" >상품 미리보기</th>
					</tr>
					<tr>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							페이스북 안에서<br />상품판매<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="facebook">자세히</button></span>
						</th>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							간편한 클릭만으로<br />회원가입과 로그인<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="login">자세히</button></span>
						</th>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							상품마다 좋아요 가능<br />좋아요 혜택 지급<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="goodpoint">자세히</button></span>
						</th>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							페북 친구 바로 초대<br />초대 시 혜택 지급<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="friend">자세히</button></span>
						</th>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							리뷰, 찜, 구매, 좋아요 시<br />페북에 상품 자동 노출<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="autoview">자세히</button></span>
						</th>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							상품, 게시글, 이벤트<br />정보 퍼가기<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="share">자세히</button></span>
						</th>
						<th class="its-th-sub-align fx11 gray" style="line-height:16px">
							쇼핑몰 도메인, 상품,<br />게시글, 이벤트 URL 푸시<br />
							<span class="btn small orange"><button type="button" class="detailview" gb="push">자세히</button></span>
						</th>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/icon_sns_facebook.gif" align="absmiddle"> 페이스북</th>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/icon_sns_tweeter.gif" align="absmiddle"> 트위터</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
						<td class="its-td-align center none">-</td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/icon_sns_cyworld.gif" align="absmiddle"> 싸이월드</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
						<td class="its-td-align center none">-</td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/sns_n0.gif" align="absmiddle"> 네이버</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/sns_k.gif" align="absmiddle"> 카카오</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif" align="absmiddle"><br />(모바일)</td>
						<td class="its-td-align center none">-</td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/sns_daum.gif" align="absmiddle"> 다음</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_star.gif"></td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/sns_kakaostory.gif" align="absmiddle"> 카카오스토리</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif" align="absmiddle"><br />(모바일)</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/sns_line.gif" align="absmiddle"> 라인</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif" align="absmiddle"><br />(모바일)</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/icon_sns_goopl.gif" align="absmiddle"> 구글+</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
					</tr>
					<tr>
						<th class="its-th-align left pdl20"><img src="/admin/skin/default/images/sns/sns_mypeople.gif" align="absmiddle"> 마이피플</th>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center none">-</td>
						<td class="its-td-align center"><img src="/admin/skin/default/images/sns/sns_icon_check.gif"></td>
						<td class="its-td-align center none">-</td>
					</tr>
					</table>
					<div style="margin-top:12px;letter-spacing:-1px;font-size:11px;text-align:right;color:#707070;">
						상기 모든 기능은 모든 플랫폼(PC, 모바일/태블릿, 페이스북 쇼핑몰)에서 동작됩니다. 단, 카카오/카카오스토리/라인의 정보공유 기능은 모바일에서만 동작됩니다. 
					</div>
				<!-- SNS 및 외부 연동 기능 안내 끝 -->
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top:15px;"><div class="item-title">페이스북 앱 및 트위터 앱</div></td>
			</tr>
			<tr>
				<td colspan="2"  align="center">
				<!-- 페이스북 앱 및 트위터 앱 시작 -->
					<style>
					.red { color:#c4060b;} 
					.joinform-user-table.info-table-style th.its-th {padding-left:15px;}
					dl {clear:both;width:100%;}
					dt,dd {float:left;}
					dt{ width:70px;background-color:yellow;}
					dd{ background-color:green;}
					.likeDiv{margin-top:5px;border-top:1px dashed #ccc; padding-top:8px;}
					</style>
					<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="info-table-style" >
					<col width="37%" /><col width="10%" /><col width="53%" />
					<tr>
						<th class="its-th-align">앱정보</th>
						<th class="its-th-align" colspan="2">앱기능</th>
					</tr>
					<!-- 페이스북 쇼핑몰 연동 -->
					<tr>
						<td class="its-td-align left" rowspan="6" style="vertical-align:top;">
						<div class="ml15 mr15">
							<div style="line-height:25px;">
								<select name="app_gubun">
								<option value="basic" <?php if($TPL_VAR["sns"]["facebook_app"]=='basic'){?>selected<?php }?>>기본앱</option>
								<option value="new" <?php if($TPL_VAR["sns"]["facebook_app"]=='new'){?>selected<?php }?>>전용앱</option>
								</select>
								을 사용합니다.
							</div>

							<div class="desc">
							↓ 아래의 앱 정보는 기능 동작 시 사용됩니다. <br />
								<span class="red">*</span> 는 전용앱 사용시 변경 가능합니다.
								<span class="btn small orange" style="float:right;margin-bottom:3px;"><button type="button" onclick="window.open('http://firstmall.kr/ec_hosting/customize/view.php?code=facebook_app','facebook_app')">기본앱 VS 전용앱 비교</button></span>

							</div>

							<div id="" style="display:block">
							<table width="100%;" class="joinform-user-table info-table-style" >
							<col width="20%"/><col width="80%"/>
							<tr>
								<th class="its-th pdl10">앱명칭 <span class="red">*</span></th>
								<td class="its-td">
<?php if($TPL_VAR["sns"]["facebook_app"]!='new'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
								Fammerce Plus
<?php }else{?>
								앱 명칭 변경 가능
<?php }?>
								</td>
							</tr>
							<tr>
								<th class="its-th pdl10">앱이미지 <span class="red">*</span></th>
								<td class="its-td">
<?php if($TPL_VAR["sns"]["facebook_app"]!='new'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
								<img src="/admin/skin/default/images/common/icon/thumb_fb_firstmall.gif">
<?php }else{?>
								앱 이미지 변경 가능
<?php }?>
								</td>
							</tr>
							<tr>
								<th class="its-th pdl10">도메인 <span class="red">*</span></th>
								<td class="its-td">
									http://<select name="likeurl" >
<?php if($TPL_VAR["config_system"]["domain"]&&$TPL_VAR["config_system"]["domain"]!=$TPL_VAR["config_system"]["subDomain"]){?><option value="<?php echo $TPL_VAR["config_system"]["domain"]?>"  <?php if($TPL_VAR["sns"]["likeurl"]==$TPL_VAR["config_system"]["domain"]){?> selected <?php }?>><?php echo $TPL_VAR["config_system"]["domain"]?></option><?php }?>
<?php if($TPL_VAR["config_system"]["subDomain"]){?><option value="<?php echo $TPL_VAR["config_system"]["subDomain"]?>" <?php if(!$TPL_VAR["sns"]["likeurl"]||$TPL_VAR["sns"]["likeurl"]==$TPL_VAR["config_system"]["subDomain"]){?>selected<?php }?>><?php echo $TPL_VAR["config_system"]["subDomain"]?></option><?php }?>
									</select>
									<br /><span class="desc">도메인 변경시 기존 도메인의 좋아요(like) 횟수 초기화됨.</span>
							 </td>
							</tr>
							<tr>
								<th class="its-th pdl10">로고<br />
									<span class="btn small cyanblue"><button type="button" id="snslogoUpdate">등록</button></span>
								</th>
								<td class="its-td" style="text-align:left;">
									<div style="margin-top:5px;">
										<img <?php if($TPL_VAR["config_system"]["snslogo"]){?>src="<?php echo $TPL_VAR["config_system"]["snslogo"]?>?<?php echo time()?>"<?php }?> alt="쇼핑몰 로고" align="absmiddle" class="snslogo_img" <?php if($TPL_VAR["config_system"]["snslogo"]){?>style="width:100px;display:block;"<?php }else{?>style="display:none"<?php }?> />
										
										<span id="snslogomsg" style="display:<?php if(!$TPL_VAR["config_system"]["snslogo"]){?>block<?php }else{?>none<?php }?>;">Fammerce 쇼핑몰 로고를 등록하세요.</span>
										
										<span class="black underline" style="width:27px;font-size:11px;cursor:pointer;display:<?php if($TPL_VAR["config_system"]["snslogo"]){?>block<?php }else{?>none<?php }?>;" id="snslogoDelete">삭제</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="its-th pdl10">소개<br />
									<span class="btn small cyanblue"><button type="button" id="snsmetaTagUpdate">등록</button></span>
								</th>
								<td class="its-td">
									<div style="margin-right:8px;">
									<div class="desc" style="color:#000;font-weight:normal">메타태그 설명 및 SNS용 쇼핑몰간략설명</div>
									<span class="desc" id="vmetaTagDescription" style="line-height:14px;"><?php echo nl2br($TPL_VAR["metaTagDescription"])?></span>
									</div>
									<div style="margin-top:5px;padding-top:5px;margin-right:8px;border-top:1px dashed #ddd">
									<div class="desc" style="color:#000;">메타태그 설명 및 SNS용 쇼핑몰키워드</div>
									<span class="desc" id="vmetaTagKeyword" style="line-height:14px;"><?php echo nl2br($TPL_VAR["metaTagKeyword"])?></span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="its-th pdl10">URL</th>
								<td class="its-td">
									SNS에 URL(주소) 정보가 보여질 때<br />
									<label ><input type="radio" name="shorturl_use" value="N" <?php if($TPL_VAR["sns"]["shorturl_use"]=='N'||!$TPL_VAR["sns"]["shorturl_use"]){?> checked="checked" <?php }?> /> URL(주소) 정보를 그대로 보여줍니다.</label>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<br /><span class="desc ml15">예) <a href="<?php echo $TPL_VAR["shorturl_test"]?>" target="blank"><?php echo $TPL_VAR["shorturl_test"]?></a></span>
									<br />
									<label ><input type="radio" name="shorturl_use" id="shorturl_use" value="Y" <?php if($TPL_VAR["sns"]["shorturl_use"]=='Y'){?> checked="checked" <?php }?> > URL(주소) 정보를 짧게 변환하여 보여줍니다.</label>
									<br /><span class="desc shorturllay ml15">예) <a href="<?php if(!$TPL_VAR["set_string"]){?><?php echo $TPL_VAR["shorturl"]?><?php }else{?>#<?php }?>" target="blank"><?php echo $TPL_VAR["shorturl"]?></a></span>

									<span class="btnshorturl" <?php if($TPL_VAR["sns"]["shorturl_use"]=='N'||!$TPL_VAR["sns"]["shorturl_use"]){?>style="display:none;"<?php }?>><span class="btn small cyanblue "><button type="button" class="shorturlConfig">설정</button></span> <?php if($TPL_VAR["set_string"]){?><?php if($TPL_VAR["set_url"]){?><br/><span class="red" style="padding-left:15px;font-size:11px;letter-spacing:-0.5px;"><?php }else{?><span class="red"><?php }?>(<?php echo $TPL_VAR["set_string"]?>)</span><?php }?></span>

								</td>
							</tr>
							<tr>
								<th class="its-th pdl10">활동 문구 <span class="red">*</span></th>
								<td class="its-td">
									<div class="desc" style="line-height:14px;">
									홍길동 bought a product on Fammerce Plus<br />
									홍길동 reviewed a product on Fammerce Plus <br />
									홍길동 wished a product on Fammerce Plus<br />
									홍길동 likes a product on Fammerce Plus
									</div>
								</td>
							</tr>
							</table>
							</div>
						</div>
						</td>
					
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;line-height:16px;"><img src="/admin/skin/default/images/board/icon/sns_f0.gif" align="absmiddle" vspace="3"><br />페이스북<br />쇼핑몰</div>
						</td>
						<td class="its-td-align left">
						<div class="ml15 mr15">
							<span class="desc">설정 &gt; <a href="config" target=_blank><span class="highlight-link">판매환경</span></a>에서 연결하십시오.</span>
							<div style="margin-top:5px;">
								[페이스북 쇼핑몰 보안 안내]
							</div>
							<table style="line-height:16px;">
							<col width="50px;"><col>
							<tr>
								<td valign="top">기본앱 - </td>
								<td>페이스북 사용자가 보안 연결(https) 사용시 페이스북 內 쇼핑몰 페이지를 방문하면 보안 안내 후 페이스북 쇼핑몰 화면이 보여지게 됩니다. 
								<span class="btn small red"><button type="button" id="configfacebookpagebtn">보안 안내란?</button></span></td>
							</tr>
							<tr>
								<td valign="top">전용앱 - </td>
								<td>보안서버(SSL) 사용 필수. 전용 앱 신청 시 안내 드립니다.</td>
							</tr>
							</table>
						</div>
						</td>
					</tr>
					<!-- 페이스북 가입 및 로그인 -->
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;"><img src="/admin/skin/default/images/board/icon/sns_f0.gif" align="absmiddle"> 가입 및 로그인</div>
						</td>
						<td class="its-td-align left">
						<div class="ml15 mr15">
							<label><input type="checkbox" name="use_f" value="1" <?php if($TPL_VAR["sns"]["use_f"]){?>checked<?php }?>> 사용</label> (설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)
						</div>
						</td>
					</tr>
					<!-- 트위터 가입 및 로그인 -->
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;"><img src="/admin/skin/default/images/board/icon/sns_t0.gif" align="absmiddle"> 가입 및 로그인</div>
						</td>
						<td class="its-td-align left">
						<div class="ml15 mr15">
							<label><input type="checkbox" name="use_t" value="1" <?php if($TPL_VAR["sns"]["use_t"]){?>checked<?php }?>> 사용</label> (설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)
						</div>
						</td>
					</tr>
					<!-- 좋아요 -->
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;"><a name="likebox"></a><img src="/admin/skin/default/images/board/icon/sns_f0.gif" align="absmiddle"> 좋아요</div>
							<div>
								<span class="btn small orange addsaleGuideBtn "><button type="button" class="hand" >안내) 추가 혜택 적용 범위</button></span>
							</div>
						</td>
						<td class="its-td-align left" style="vertical-align:top;min-height:450px;">
						<div class="ml15 mr15">
							<ul>
<?php if($TPL_VAR["sns"]["facebook_app"]!='new'&&$TPL_VAR["sns"]["facebook_ob_like"]=='Y'){?>
								<li>
									<label ><input type="radio" name="fb_like_box_type" value="NO" <?php if($TPL_VAR["sns"]["fb_like_box_type"]=='NO'||!$TPL_VAR["sns"]["fb_like_box_type"]){?> checked="checked" <?php }?> /> 사용 안함(기본앱)</label>
								</li>
								<li>
									<label ><input type="radio" name="fb_like_box_type" value="API" <?php if($TPL_VAR["sns"]["fb_like_box_type"]=='API'||!$TPL_VAR["sns"]["fb_like_box_type"]){?> checked="checked" <?php }?> /> 페이스북 직접 연결방식으로 사용 </label>
									<div class="desc ml15" >좋아요 정보를 페이스북 API로 연결하여 전송. 좋아요 버튼은 페이스북의 디자인으로 고정</div>
								</li>
								<li>
									<label ><input type="radio" name="fb_like_box_type" value="OP" <?php if($TPL_VAR["sns"]["fb_like_box_type"]=='OP'){?> checked="checked" <?php }?> >  페이스북 간접 연결방식으로 사용 </label>
									<div class="desc ml15" >좋아요 정보를 페이스북에 전송. 좋아요 버튼의 디자인 변경 가능. 좋아요 노출 속도 향상. 표시된 좋아요 수와 페이스북의 실제 좋아요 수가 다를 수 있음</div>
									<div id="fb_like_opengrapy_lay" class="ml15 <?php if($TPL_VAR["sns"]["fb_like_box_type"]!='OP'){?> hide <?php }?> ">
									<table width="100%" class=" ">
									<col width="120" /><col width="100" /><col width="" />
										<tbody>
											<tr>
											<td class="desc" > - 좋아요 안했을때 아이콘</td>
											<td><?php echo snsLikeButton('1','button_count','opengrapy','unlike')?></td>
											<td><span class="btn small black"><button type="button" id="fbunlikeiconBtn" >[변경]</button></span></td>
										</tr>
										<tr>
											<td class="desc" > - 좋아요 했을때 아이콘 </td>
											<td><?php echo snsLikeButton('1','button_count','opengrapy','like')?></td>
											<td><span class="btn small black"><button type="button" id="fblikeiconBtn" >[변경]</button></span></td>
										</tr>
									</tbody>
									</table>
									</div>
								</li>
<?php }else{?>
								<!-- 전용앱 -->
									<li>
										<label ><input type="radio" name="new_fb_like_box_type" value="NO" <?php if($TPL_VAR["sns"]["fb_like_box_type"]=='NO'||!$TPL_VAR["sns"]["fb_like_box_type"]){?> checked="checked" <?php }?> /> 사용 안함(전용앱)</label>
									</li>
									<li>
										<label ><input type="radio" name="new_fb_like_box_type" value="API" <?php if($TPL_VAR["sns"]["fb_like_box_type"]=='API'||!$TPL_VAR["sns"]["fb_like_box_type"]){?> checked="checked" <?php }?> /> 페이스북 직접 연결방식으로 사용 </label>
										<div class="desc ml15" >좋아요 정보를 페이스북 API로 연결하여 전송. 좋아요 버튼은 페이스북의 디자인으로 고정</div>
									</li>
									<li>
										<label ><input type="radio" name="new_fb_like_box_type" value="OP" <?php if($TPL_VAR["sns"]["fb_like_box_type"]=='OP'){?> checked="checked" <?php }?> >  페이스북 간접 연결방식으로 사용 </label>
										<div class="desc ml15" >좋아요 정보를 별도로 연결하여 전송. 좋아요 버튼의 디자인 변경 가능. 좋아요 노출 속도 향상</div>
										<div id="new_fb_like_opengrapy_lay" class="ml15 <?php if($TPL_VAR["sns"]["fb_like_box_type"]!='OP'){?> hide <?php }?> ">
										<table width="100%" class=" ">
										<col width="120" /><col width="100" /><col width="" />
											<tbody >
												<tr >
												<td class="desc" > - 좋아요 안했을때 아이콘</td>
												<td><?php echo snsLikeButton('1','button_count','opengrapy','unlike')?></td>
												<td>
													</span> <span class="btn small black"><button type="button" id="fbunlikeiconBtn" >[변경]</button></span>
												</td>
											</tr>
											<tr >
												<td class="desc" > - 좋아요 했을때 아이콘 </td>
												<td><?php echo snsLikeButton('1','button_count','opengrapy','like')?></td>
												<td>
													</span> <span class="btn small black"><button type="button" id="fblikeiconBtn" >[변경]</button></span>
												</td>
											</tr>
										</tbody>
										</table>
										</div>
									</li>
<?php }?>
								<!-- 전용앱 끝 -->
							</ul>

							<div class="likeDiv">
								<label><input type="radio" name="fblike_ordertype" id="fbliketype1" value="1"  <?php if($TPL_VAR["fblike_ordertype"]== 1){?> checked="checked"  <?php }?> > '좋아요'한 상품을 회원이 구매하면 아래의 혜택을 제공합니다.</label><br />
								<label><input type="radio" name="fblike_ordertype" id="fbliketype0" value="0" <?php if($TPL_VAR["fblike_ordertype"]== 0||!$TPL_VAR["fblike_ordertype"]){?> checked="checked"  <?php }?> > '좋아요'한 상품을 회원 또는 비회원이 구매하면 아래의 혜택을 제공합니다.</label>
								
								<table width="100%" class="info-table-style"  id="system_fblike_tbl" >
									<colgroup><col width="40" /><col width="" /></colgroup>
									<tbody>
									<tr>
										<td class="its-td fx11" valign="top"><span class="btn-plus"><button type="button" id="etcAdd">+</button></span></td>
										<td class="its-td fx11"  valign="top">'+'을 클릭하여 Like(좋아요)한 상품을 주문하는 소비자(구매자)를 위한 추가혜택을 설정할 수 있습니다.<br>
										※ 적립금 및 포인트 지급 시점은 관리환경 &gt; 설정 &gt; <a href="/admin/setting/reserve" target="_blank"><span class=" highlight-link hand">적립금/포인트/이머니</span></a>에 따릅니다.<br>
										※ 상품 실 결제금액 = &#123;상품 할인가(판매가) x 수량&#125; – 할인(쿠폰,등급,좋아요,모바일,프로모션코드)</td>
									</tr>
<?php if($TPL_VAR["systemfblike"]){?>
<?php if($TPL_systemfblike_1){foreach($TPL_VAR["systemfblike"] as $TPL_V1){?>
										<tr>
											<td class="its-td fx11"><span class="btn-minus"><button type="button" class="etcDel">-</button></span></td>
											<td class="its-td fx11"> Like(좋아요)한 상품 구매 시 Like(좋아요)한 상품의 <br /><span style="color:red;">&#123;상품 할인가(판매가) x 수량&#125;</span>이 <input type="text" name="fblike_price1[]" value="<?php echo $TPL_V1["price1"]?>" size="8" class="line onlynumber input-box-default-text" style="text-align:right;" /> ~ <input type="text" name="fblike_price2[]" value="<?php echo $TPL_V1["price2"]?>" size="8" class="line onlynumber input-box-default-text" style="text-align:right;" />이면,
												<br/>
												상품별로 ① 상품 할인가(판매가) x 수량의 <input type="text" name="fblike_sale_price[]" value="<?php echo $TPL_V1["sale_price"]?>" size="3" class="line onlynumber input-box-default-text" style="text-align:right;" />% 추가할인, <br />
												<span style="padding-left:10px;"></span>② 적립금은 실 결제금액의 <input type="text" name="fblike_sale_emoney[]" value="<?php echo $TPL_V1["sale_emoney"]?>" size="3" class="line onlynumber input-box-default-text" style="text-align:right;"/>% 추가 지급, 지급 적립금의 유효기간은 <?php echo $TPL_VAR["reservetitle"]?><br />
												<span <?php if(!$TPL_VAR["isplusfreenot"]||!$TPL_VAR["isplusfreenot"]["ispoint"]){?> readonly='readonly' disabled='disabled'class='gray readonly'  <?php }?>  >
												<span style="padding-left:10px;"></span>③ 포인트는 실 결제금액의 <input type="text" name="fblike_sale_point[]" value="<?php echo $TPL_V1["sale_point"]?>" size="3" class="line onlynumber input-box-default-text" style="text-align:right;"/>% 추가 지급, 지급 포인트의 유효기간은 <?php echo $TPL_VAR["pointtitle"]?>

												</span>
											</td>
										</tr>
<?php }}?>
<?php }?>
									</tbody>
								</table>
							</div>
						</div>
						</td>
					</tr>
					
					<!-- 친구초대 -->
<?php if($TPL_VAR["APP_VER"]=="2.0"){?>
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;"><img src="/admin/skin/default/images/board/icon/sns_f0.gif" align="absmiddle"> 친구초대</div>
						</td>
						<td class="its-td left">
							<div class="desc" >※ 페이스북 API가 version v2.0으로 업데이트되면서 <br/> 2014년 4월 30일 이후 출시된 전용앱은 친구 목록 가져오기 기능을 지원하지 않습니다.</div>
						</td> 
					</tr>
<?php }else{?>
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;"><img src="/admin/skin/default/images/board/icon/sns_f0.gif" align="absmiddle"> 친구초대</div>
						</td>
						<td class="its-td-align left">
						<div class="ml15 mr15">
							설정 &gt; 회원 &gt; <a href="member?gb=approval" target=_blank><span class="highlight-link">승인혜택</span></a>에서 설정하십시오.
						</div>
						</td>
					</tr>
<?php }?>
					<!-- 활동 공유 -->
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;"><img src="/admin/skin/default/images/board/icon/sns_f0.gif" align="absmiddle"> 활동공유</div>
						</td>
						<td class="its-td-align left">
						<div class="ml15 mr15">
							<ul>
								<li><label><input type="checkbox" name="facebook_review" value="Y" > 유저가 상품리뷰를 작성하면 유저의 페이스북에 상품 정보를 자동으로 노출 합니다.</label></li>
								<li><label><input type="checkbox" name="facebook_interest" value="Y" > 유저가 위시리스트에 상품을 담으면 유저의 페이스북에 상품 정보를 자동으로 노출 합니다.</label></li>
								<li><label><input type="checkbox" name="facebook_buy" value="Y" > 유저가 구매하기를 클릭하면 유저의 페이스북에 상품 정보를 자동으로 노출 합니다.</label></li>
							</ul>
						</div>
						</td>
					</tr>
					</table>
					
					<!-- 짧은 url 변환 설정 레이어 -->
						<div id="shorturl_help_guide" class="hide">
						<table width="99%" class="joinform-user-table info-table-style">
							<col width="150" /><col width="" />
							<tr>
								<td class="its-td-align">
								<ol style="padding:10px;">
									<li>1. <a href="http://bit.ly/a/account/" target="_blank" ><span class="cyanblue">http://bit.ly/a/account/ </span></a> 방문 (가입 필요)</li>
									<li>2.  <a href="https://bitly.com/a/settings/advanced" target="_blank" ><span class="cyanblue">https://bitly.com/a/settings/advanced</span></a> > Legacy API Key<br/>
									&nbsp;&nbsp;&nbsp;발급받은 API ID/Key 값을 위에 입력칸에 넣은 후 '저장하기' 해 주세요.</li>
								</ol>
								</td>
							</tr>
						</tbody>
						</table> <!-- <span   >짧은주소(bit.ly 셋팅)를 원하시면 ↑위의 정보를 설정해 주십시오.</span> -->
						</div>
					<!-- 전용 앱 신청 안내 레이어 -->
						<div id="app_private_guide" class="hide">
							<div class="fx12" style="line-height:22px;"><strong>전용 앱을 사용하면</strong><br />
							페이스북 쇼핑몰, 회원 가입/로그인, 좋아요, <?php if($TPL_VAR["APP_VER"]!="2.0"){?>친구초대,<?php }?> 활동 공유 기능 동작 시 노출되는 앱 명칭, 앱 이미지, 앱 도메인, 활동 문구를 변경할 수 있는 장점이 있습니다.
							</div>
							<div style="margin:15px auto;text-align:center;">
							<span class="btn large cyanblue"><button type="button" onclick="window.open('http://firstmall.kr/ec_hosting/customize/view.php?code=facebook_app','facebook_app')" style="line-height:26px;">페이스북 전용 앱 및 트위터 전용 앱 신청 안내</button></span>
							</div>
						</div>

				<!-- 페이스북 앱 및 트위터 앱 끝 -->
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top:15px;"><div class="item-title">카카오 앱</div></td>
			</tr>
			<tr>
				<td colspan="2"  align="center">
					<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="info-table-style" >
					<col width="37%" /><col width="10%" /><col width="53%" />
					<tr>
						<th class="its-th-align">앱정보</th>
						<th class="its-th-align" colspan="2">앱기능</th>
					</tr>
					<!-- 카카오 로그인 연동 -->
					<tr>
						<td class="its-td-align left" rowspan="2">
							<div class="ml15 mr15">
								<span class="snslogin_use kakaconfig">먼저 전용앱 설정을 하십시오.</span>
								<span class="btn small cyanblue"><button type="button" class="kakaoconflay">설정</button></span>
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;">
								<img src="/admin/skin/default/images/sns/sns_k0.gif" align="absmiddle"> 가입 및 로그인
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml15 mr15">
								<span class="snslogin_use kakaouse">미사용</span>
								(설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)
							</div>
						</td>
					</tr>
					<tr>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;">
								<img src="/admin/skin/default/images/board/icon/sns_k0.gif" align="absmiddle"> 정보 공유
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml15 mr15">
								<span class="snslogin_use kakaotalk0">미사용 (설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)</span>
								<span class="snslogin_use kakaotalk1 hide">상품상세 페이지, 게시물보기 페이지, 이벤트 페이지에서 카카오톡으로 정보 공유 가능 <br />단, 정보 공유 기능은 모바일 환경 전용입니다.</span>
							</div>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<!-- 네이버 로그인 연동 -->
			<tr>
				<td colspan="2" style="padding-top:15px;"><div class="item-title">네이버 앱</div></td>
			</tr>
			<tr>
				<td colspan="2"  align="center">
					<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="info-table-style" >
					<col width="37%" /><col width="10%" /><col width="53%" />
					<tr>
						<th class="its-th-align">앱정보</th>
						<th class="its-th-align" colspan="2">앱기능</th>
					</tr>
					<tr>
						<td class="its-td-align left">
							<div class="ml15 mr15 mt10 mb10">
								<span class="snslogin_use naverconfig">먼저 전용앱 설정을 하십시오.</span>
								<span class="btn small cyanblue"><button type="button" class="naverconflay">설정</button></span>
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;">
								<img src="/admin/skin/default/images/sns/sns_n0.gif" align="absmiddle"> 가입 및 로그인
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml15 mr15">
								<span class="snslogin_use naveruse">미사용</span>
								(설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)
							</div>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<!-- 다음 로그인 연동 -->
			<tr>
				<td colspan="2" style="padding-top:15px;"><div class="item-title">다음 앱</div></td>
			</tr>
			<tr>
				<td colspan="2"  align="center">
					<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="info-table-style" >
					<col width="37%" /><col width="10%" /><col width="53%" />
					<tr>
						<th class="its-th-align">앱정보</th>
						<th class="its-th-align" colspan="2">앱기능</th>
					</tr>
					<tr>
						<td class="its-td-align left">
							<div class="ml15 mr15 mt10 mb10">
								<span class="snslogin_use daumconfig">먼저 전용앱 설정을 하십시오.</span>
								<span class="btn small cyanblue"><button type="button" class="daumconflay">설정</button></span>
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;">
								<img src="/admin/skin/default/images/sns/sns_d0.gif" align="absmiddle"> 가입 및 로그인
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml15 mr15">
								<span class="snslogin_use daumuse">미사용</span>
								(설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)
							</div>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<!-- 싸이월드 로그인 연동 -->
			<tr>
				<td colspan="2" style="padding-top:15px;"><div class="item-title">싸이월드 앱</div></td>
			</tr>
			<tr>
				<td colspan="2"  align="center">
					<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="info-table-style" >
					<col width="37%" /><col width="10%" /><col width="53%" />
					<tr>
						<th class="its-th-align">앱정보</th>
						<th class="its-th-align" colspan="2">앱기능</th>
					</tr>
					<tr>
						<td class="its-td-align left">
							<div class="ml15 mr15 mt10 mb10">
								<span class="snslogin_use cyworldconfig">먼저 전용앱 설정을 하십시오.</span>
								<span class="btn small cyanblue"><button type="button" class="cyworldconflay">설정</button></span>
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml10" style="letter-spacing:-1px;">
								<img src="/admin/skin/default/images/sns/sns_c0.gif" align="absmiddle"> 가입 및 로그인
							</div>
						</td>
						<td class="its-td-align left">
							<div class="ml15 mr15">
								<span class="snslogin_use cyworlduse">미사용</span>
								(설정 &gt; 회원 &gt; <a href="member" target=_blank><span class="highlight-link">로그인 및 회원가입</span></a>에서도 설정 가능)
							</div>
						</td>
					</tr>
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

<!-- SNS 및 외부연동 기능 안내 -->
<div id="snsconf_detail" mode="" class="dialogDiv"></div>

<div id="snsconfigkakaotalklay" class="hide">
	카카오톡 링크 API 버전 업그레이드로 인해 모바일 쇼핑몰에서 카카오톡 링크 공유를 사용하시던 고객님께서는 다음 단계를 진행하시기 바랍니다.
	<div class="desc" style="line-height:180%;margin-left:5px" >
	<ol style="padding:1px;">
		<li style="padding-bottom:3px;">1. <a href="https://developers.kakao.com/api/kakao" target="blank" >https://developers.kakao.com/api/kakao</a> 접속한 후 하단의 <span class="bold">‘앱 개발 시작하기’</span> 버튼 클릭</li>
		<li style="padding-bottom:3px;">2. 카카오계정으로 로그인</li>
		<li style="padding-bottom:3px;">3. 앱 이름을 입력 후 ‘만들기’ 버튼 클릭
		<br/>
		<img src="/admin/skin/default/images/sns/kakao_1.JPG" >
		</li>
		<li style="padding-bottom:3px;">4. 해당 앱에 대한 키값이 발급되며 3번째 항목의 <span class="red">Javascript 키</span>값을 확인합니다.</li>
		<li style="padding-bottom:3px;">5. 좌측 메뉴에서 설정 – 일반 클릭 → 페이지 중간의 <img src="/admin/skin/default/images/sns/kakao_2.JPG" > 버튼 클릭</li>
		<li style="padding-bottom:3px;">6. ‘웹’ 클릭 → 사이트 <span class="red">모바일 도메인주소</span> 입력 (<?php if($TPL_VAR["config_system"]["domain"]){?>http://m.<?php echo $TPL_VAR["config_system"]["domain"]?><?php }else{?>http://m.<?php echo $TPL_VAR["config_system"]["subDomain"]?><?php }?>) 추가 버튼 클릭
		<br/>
			※ 모바일 주소를 반드시 확인하시고 입력하시기 바랍니다.
			<br/>
			<img src="/admin/skin/default/images/sns/kakao_3.JPG" >
		</li>
		<li style="padding-bottom:3px;">7. 화면의 <span class="red">사이트 도메인주소</span>와 <span class="red">Javascript 키</span> 값을 확인 후
			<br/> 
		퍼스트몰 관리자페이지에 위의 내용을 모바일 도메인주소와 API Javascript Key에 각각 입력하세요.</li>
	</ol>
	</div>
</div>

<!--보안안내-->
<div id="configfacebookpagepopup" class="hide ">
	<div  >
		<div style="padding-bottom:10px;line-height:18px;" class="red left" >
		페이스북 사용자가 보안 연결(https) 사용 상태에서<br/>
		페이스북內 쇼핑몰 페이지를 방문하면<br/>
		↓ 아래의 보안 안내가 자동으로 보여지며, 페이스북 사용자가 안내와 같이<br/>
		&nbsp;&nbsp;보안 설정을 변경 후 쇼핑몰이 보여지게 됩니다.
		</div>
		 <div class="center" style="padding-bottom:5px;">
			<span class="btn small cyanblue center"><button type="button" >샘플) 페이스북 쇼핑몰에서 보안 안내</button></span>
		</div>
		 <div class="center">
			<img src="/admin/skin/default/images/design/facebookpage-ie.jpg" align="absmiddle"  style="padding:5px;"/><br/>
			<img src="/admin/skin/default/images/design/facebookpage-chrome.jpg" align="absmiddle" style="padding:5px;" />
		</div>
	</div>
</div>

<div id="snsconfigfacebookurllay" class="hide">
	홍보하고 싶은 쇼핑몰의 페이지 주소(URL)을 복사하여
	페이스북에 등록하면 자동으로 푸시정보로 변환됩니다.
	<div class="desc" style="line-height:180%;margin-left:20px" >
	<b>쇼핑몰 주요 페이지</b><br />
	- 메인페이지<br />
	- 상품상세페이지<br />
	- 카테고리페이지<br />
	- 브랜드페이지<br />
	- 이벤트페이지<br />
	- 상품후기페이지
	</div>
</div>

<div id="snsconfigsharelinklay"  class="hide">
	아래와 같이 치환코드를 사용하세요. ( 단, 카카오톡/카카오스토리/LINE은 당연히 모바일에서만 동작합니다.)
	<div class="desc" style="line-height:180%;margin-left:20px" >
		<b>- 상품정보공유하기</b> : EYE-DESIGN > 상품상세페이지(/goods/view.html)에 치환코드 삽입<br />
		<b>- 이벤트정보공유하기</b> : 관리환경 > 프로모션/쿠폰 > 이벤트 관리에서 이벤트마다 설정<br />
		<b>- 게시판정보공유하기</b> : EYE-DESIGN > 게시글상세페이지(view.html)에 치환코드 삽입<br />
	</div>

	<br class="table-gap" />
	<table width="100%"  class="info-table-style" >
		<tr>
			<th  class="its-th-align">SNS치환코드</th>
			<th  class="its-th-align center">전체</th>
			<th  class="its-th-align center">페이스북</th>
			<th  class="its-th-align center">트위터</th>
			<th  class="its-th-align center">google+</th>
			<th  class="its-th-align center">싸이월드</th>
			<th  class="its-th-align center">마이피플</th>
			<th  class="its-th-align center">카카오톡</th>
			<th  class="its-th-align center">카카오스토리</th>
			<th  class="its-th-align center">LINE</th>
		</tr>
		<tr>
			<th  class="its-th-align center">상품</th>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"])?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name)//SNS전체}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'fa')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'fa')//페이스북}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'tw')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'tw')//트위터}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'go')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'go')//google+}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'cy')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'cy')//싸이월드}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'my')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'my')//마이피플}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'ka')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'ka')//카카오톡}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'kakaostory')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'kakaostory')//카카오스토리}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('goods',$TPL_VAR["goods"]["goods_name"],'line')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsgoods"]?>', goods.goods_name,'line')//LINE}" >치환코드복사</button></span></td>
		</tr>

		<tr>
			<th  class="its-th-align center">이벤트</th>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명')}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','fa')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','fa')//페이스북}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','tw')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','tw')//트위터}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','go')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','go')//google+}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','cy')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','cy')//싸이월드}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','my')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','my')//마이피플}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','ka')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','ka')//카카오톡}" >치환코드복사</button></span></td>
				
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','kakaostory')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','kakaostory')//카카오스토리}" >치환코드복사</button></span></td>
				
			<td  class="its-td-align center"><?php echo snslinkurl('event','이벤트명','line')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsevent"]?>', '이벤트명','line')//LINE}" >치환코드복사</button></span></td>
		</tr>

		<tr>
			<th  class="its-th-align center">게시글</th>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"])?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject)//SNS전체}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'fa')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'fa')//페이스북}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'tw')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'tw')//트위터}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'go')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'go')//google+}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'cy')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'cy')//싸이월드}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'my')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'my')//마이피플}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'ka')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'ka')//카카오톡}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'kakaostory')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'kakaostory')//카카오스토리}" >치환코드복사</button></span></td>
			<td  class="its-td-align center"><?php echo snslinkurl('board',$TPL_VAR["subject"],'line')?>

				<br />
				<span class="btn small"><button type="button" class="copy_snstag_btn" code="{=snslinkurl('<?php echo $TPL_VAR["snsboard"]?>', subject,'line')//LINE}" >치환코드복사</button></span></td>
		</tr>
	</table>
</div>


<div id="snsfbliketypelay"  class="hide">
	<table width="100%"  class="info-table-style" >
		<tr>
			<th  class="its-th-align center" width="50"> 구분 </th>
			<th  class="its-th-align center">직접연결 방식</th>
			<th  class="its-th-align center">간접연결 방식</th>
		</tr>
		<tr>
			<th  class="its-th-align center"> 정의 </th>
			<td  class="its-td left">
				<ul style="list-style:disc" class="ml5" >
					<li>상품의 좋아요 정보를 페이스북 API로 연결하여 전송</li>
					<li>상품의 좋아요 횟수를 실시간으로 가져옴</li>
				</ul>
			</td>
			<td  class="its-td left">
				<ul style="list-style:disc"  class="ml5" >
					<li>상품의 좋아요 정보를 페이스북 OpenGraph로 연결하여 전송</li>
					<li>상품의 좋아요 횟수를 쇼핑몰에 저장하고 있음</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th  class="its-th-align center"> 특징 </th>
			<td  class="its-td left">
				<ul style="list-style:disc"  class="ml5" >
					<li>상품의 좋아요 정보 노출 시 속도 저하 문제 발생 가능성 있음</li>
					<li>좋아요 버튼 이미지 변경 불가능</li>
					<li>좋아요 클릭 시  페이스북에서 제공하는 창에서 좋아요 처리</li>
				</ul>
			</td>
			<td  class="its-td left">
				<ul style="list-style:disc" class="ml5"  >
					<li>상품의 좋아요 정보 노출 시 속도 저하 문제 없음</li>
					<li>좋아요 버튼 이미지 변경 가능</li>
					<li>좋아요 클릭 시 별도 개발된 창에서 좋아요 처리</li>
				</ul>
			</td>
		</tr>
	</table>
</div>


<div id="fblikeiconPopup" class="hide">
	<form name="fblikeiconRegist" id="fblikeiconRegist" method="post" action="" enctype="multipart/form-data"  target="actionFrame">
	<ul>
		<li style="float:left;width:100px;height:30px;text-align:center" ><input type="file" name="fblikeboxpciconFile" id="fblikeboxpciconFile" onChange="fblikeiconFileUpload();" /></li>
	</ul>
	</form>
</div>

<div id="fbunlikeiconPopup" class="hide">
	<form name="fbunlikeiconRegist" id="fbunlikeiconRegist" method="post" action="" enctype="multipart/form-data"  target="actionFrame">
	<ul>
		<li style="float:left;width:100px;height:30px;text-align:center" ><input type="file" name="fbunlikeboxpciconFile" id="fbunlikeboxpciconFile" onChange="fbunlikeiconFileUpload();" /> </li>
	</ul>
	</form>
</div>

<div id="snslogoUpdatePopup" class="hide">
	<form name="snslogoRegist" id="snslogoRegist" method="post" action="" enctype="multipart/form-data"  target="actionFrame">
	<div style="height:20px;padding-left:30px;"><span class='desc'>Fammerce 로고 사이즈는 200 × 200 으로 등록해 주세요.</span></div>
	<div style="height:30px;padding-left:30px;"><input type="file" name="snslogoFile" class="line"  id="snslogoFile" onChange="snslogoFileUpload();" /></div>
	</form>
</div>

<div id="snsmetaTagUpdatePopup" class="hide">
	<form name="snsmetatagRegist" id="snsmetatagRegist" method="post" action="" target="actionFrame">
	<div style="height:20px;padding-left:10px;">
		<span class='desc' style="color:#000;">메타태그 설명(Description) 및 SNS용 쇼핑몰간략설명</span>
	</div>
	<div style="height:80px;padding-left:10px;">
		<textarea name="metaTagDescription" id="metaTagDescription" style="width:330px;height:70px;font-size:11px;" rows="4" class="line" title="검색엔진에서 수집할 사이트의 설명을 입력하세요" ><?php echo $TPL_VAR["metaTagDescription"]?></textarea>
	</div>
	<div style="margin-top:20px;height:20px;padding-left:10px;">
		<span class='desc' style="color:#000;">메타태그 설명(metaTagkeyword) 및 SNS용 쇼핑몰키워드</span>
	</div>
	<div style="height:80px;padding-left:10px;">
		<textarea name="metaTagKeyword" id="metaTagKeyword" style="width:330px;height:70px;font-size:11px;" rows="4" class="line" title="검색엔진에서 수집할 사이트의 키워드(콤마구분)을 입력하세요.<?php echo chr( 10)?>예) 여성의류, 캐주얼 패션, 청소년 의류"><?php echo $TPL_VAR["metaTagKeyword"]?></textarea>
	</div>
	<div class="center" style="padding:10px;">
		<span class="btn small black"><button  type="button" id="btnmetatag">등록하기</button></span>
	</div>

	</form>
</div>

<form name="snsjoinRegist" id="snsjoinRegist" method="post" action="" target="actionFrame">
<input type="hidden" name="pagemode" id="pagemode" value="snsconf">
<!--- include : joinform_sns_setting.html -->
<?php $this->print_("sns_setting",$TPL_SCP,1);?>

</form>

<!--- include : snsconf_shorturl_setting.html -->
<?php $this->print_("shorturl_setting",$TPL_SCP,1);?>



<script type="text/javascript">
	function snsDisplayKakao(mode){
		if( $("#use_k_lay").is(':checked') ) {
			$(".kakaouse").html('사용');
			$(".kakaotalk0").hide();
			$(".kakaotalk1").show();
			$(".kakaconfig").html('전용앱이 설정되었습니다.');
		}else{
			$(".kakaouse").html('미사용');
			$(".kakaotalk0").show();
			$(".kakaotalk1").hide();
			$(".kakaconfig").html('먼저 전용앱 설정을 하십시오.');
		}
		if(mode == 'up') openDialogAlert("설정이 저장 되었습니다.",400,140,'parent','');
	}
	function snsDisplayNaver(mode){
		if( $("#use_n_lay").is(':checked') ) {
			$(".naveruse").html('사용');
			$(".naverconfig").html('전용앱이 설정되었습니다.');
		}else{
			$(".naveruse").html('미사용');
			$(".naverconfig").html('먼저 전용앱 설정을 하십시오.');
		}
		if(mode == 'up') openDialogAlert("설정이 저장 되었습니다.",400,140,'parent','');
	}
	function snsDisplayDaum(mode){
		if( $("#use_d_lay").is(':checked') ) {
			$(".daumuse").html('사용');
			$(".daumconfig").html('전용앱이 설정되었습니다.');
		}else{
			$(".daumuse").html('미사용');
			$(".daumconfig").html('먼저 전용앱 설정을 하십시오.');
		}
		if(mode == 'up') openDialogAlert("설정이 저장 되었습니다.",400,140,'parent','');
	}
	function snsDisplayCyworld(mode){
		if( $("#use_c_lay").is(':checked') ) {
			$(".cyworlduse").html('사용');
			$(".cyworldconfig").html('전용앱이 설정되었습니다.');
		}else{
			$(".cyworlduse").html('미사용');
			$(".cyworldconfig").html('먼저 전용앱 설정을 하십시오.');
		}
		if(mode == 'up') openDialogAlert("설정이 저장 되었습니다.",400,140,'parent','');
	}
	snsDisplayKakao();
	snsDisplayNaver();
	snsDisplayDaum();
	snsDisplayCyworld();
</script>

<?php $this->print_("layout_footer",$TPL_SCP,1);?>