<?
    /* ============================================================================== */
    /* =   PAGE : 결제 요청 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   이 페이지는 주문 페이지를 통해서 결제자가 결제 요청을 하는 페이지        = */
    /* =   입니다. 아래의 ※ 필수, ※ 옵션 부분과 매뉴얼을 참조하셔서 연동을        = */
    /* =   진행하여 주시기 바랍니다.                                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.?       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.05   KCP Inc.   All Rights Reserved.                 = */
    /* ============================================================================== */
?>
<?
	/* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.php파일을 수정하시기 바랍니다.     = */
    /* = -------------------------------------------------------------------------- = */

     include "../../cfg/site_conf_inc.php";       // 환경설정 파일 include
?>
<?
    /* kcp와 통신후 kcp 서버에서 전송되는 결제 요청 정보*/
    $req_tx          = $_POST[ "req_tx"         ]; // 요청 종류          
    $res_cd          = $_POST[ "res_cd"         ]; // 응답 코드          
    $tran_cd         = $_POST[ "tran_cd"        ]; // 트랜잭션 코드      
    $ordr_idxx       = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호    
    $good_name       = $_POST[ "good_name"      ]; // 상품명             
    $good_mny        = $_POST[ "good_mny"       ]; // 결제 총금액        
    $buyr_name       = $_POST[ "buyr_name"      ]; // 주문자명           
    $buyr_tel1       = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호    
    $buyr_tel2       = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호 
    $buyr_mail       = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소 
    $use_pay_method  = $_POST[ "use_pay_method" ]; // 결제 방법          
    $enc_info        = $_POST[ "enc_info"       ]; // 암호화 정보        
    $enc_data        = $_POST[ "enc_data"       ]; // 암호화 데이터
	$rcvr_name		 = $_POST[ "rcvr_name"		]; // 수취인 이름
	$rcvr_tel1		 = $_POST[ "rcvr_tel1"		]; // 수취인 전화번호
	$rcvr_tel2		 = $_POST[ "rcvr_tel2"		]; // 수취인 휴대폰번호
	$rcvr_mail		 = $_POST[ "rcvr_mail"		]; // 수취인 E-Mail
	$rcvr_zipx		 = $_POST[ "rcvr_zipx"		]; // 수취인 우편번호
	$rcvr_add1		 = $_POST[ "rcvr_add1"		]; // 수취인 주소
	$rcvr_add2		 = $_POST[ "rcvr_add2"		]; // 수취인 상세주소

	$tablet_size      = "1.0"; // 화면 사이즈 조정 - 기기화면에 맞게 수정(갤럭시탭,아이패드 - 1.85, 스마트폰 - 1.0)
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">
<head>
<title>스마트폰 웹 결제창</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<meta http-equiv="Cache-Control" content="No-Cache">
<meta http-equiv="Pragma" content="No-Cache">

<meta name="viewport" content="width=device-width; user-scalable=<?=$tablet_size?>; initial-scale=<?=$tablet_size?>; maximum-scale=<?=$tablet_size?>; minimum-scale=<?=$tablet_size?>">

<style type="text/css">
	.LINE { background-color:#afc3ff }
	.HEAD { font-family:"굴림","굴림체"; font-size:9pt; color:#065491; background-color:#eff5ff; text-align:left; padding:3px; }
	.TEXT { font-family:"굴림","굴림체"; font-size:9pt; color:#000000; background-color:#FFFFFF; text-align:left; padding:3px; }
	    B { font-family:"굴림","굴림체"; font-size:13pt; color:#065491;}
	INPUT { font-family:"굴림","굴림체"; font-size:9pt; }
	SELECT{font-size:9pt;}
	.COMMENT { font-family:"굴림","굴림체"; font-size:9pt; line-height:160% }
</style>
<!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
<script type="text/javascript" src="../common/approval_key.js"></script>

<script language="javascript">

   /* 주문번호 생성 예제 */
	function init_orderid() 
    {
		var today = new Date();
		var year  = today.getFullYear();
		var month = today.getMonth()+ 1;
		var date  = today.getDate();
		var time  = today.getTime();

        if(parseInt(month) < 10) {
            month = "0" + month;
        }

		var vOrderID = year + "" + month + "" + date + "" + time;
        var vDEL_YMD = year + "" + month + "" + date;

        document.forms[0].ordr_idxx.value = vOrderID;
    }

	/* 에스크로 가맹점 장바구니 상품 상세 정보 생성 예제 */ 
	function create_goodInfo()
    {
		var chr30 = String.fromCharCode(30);	// ASCII 코드값 30
        var chr31 = String.fromCharCode(31);	// ASCII 코드값 31

        var good_info = "seq=1" + chr31 + "ordr_numb=20060310_0001" + chr31 + "good_name=양말" + chr31 + "good_cntx=2" + chr31 + "good_amtx=1000" + chr30 +
                        "seq=2" + chr31 + "ordr_numb=20060310_0002" + chr31 + "good_name=신발" + chr31 + "good_cntx=1" + chr31 + "good_amtx=1500" + chr30 +
                        "seq=3" + chr31 + "ordr_numb=20060310_0003" + chr31 + "good_name=바지" + chr31 + "good_cntx=1" + chr31 + "good_amtx=1000";

		/*에스크로 사용시 이용 하시기 바랍니다. */
		//document.sm_form.good_info.value = good_info;
    }
	
   /* kcp web 결제창 호출 (변경불가)*/
    function call_pay_form()
    {

       var v_frm = document.sm_form;

        layer_cont_obj   = document.getElementById("content");
        layer_vcnt_obj = document.getElementById("layer_vcnt");

        layer_cont_obj.style.display = "none";
        layer_vcnt_obj.style.display = "block";

        v_frm.target = "frm_vcnt";
        v_frm.action = PayUrl;
        
		if(v_frm.Ret_URL.value == "")
		{
			/* Ret_URL값은 현 페이지의 URL 입니다. */
			alert("연동시 Ret_URL을 반드시 설정하셔야 됩니다.");
			return false;
		}
		else
        {
			v_frm.submit();
		}
    }
	
	/* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청*/
    function chk_pay()
    {
        /*kcp 결제서버에서 가맹점 주문페이지로 폼값을 보내기위한 설정(변경불가)*/
        self.name = "tar_opener";

        var pay_form = document.pay_form;

        if (pay_form.res_cd.value == "3001" )
        {
            alert("사용자가 취소하였습니다.");
            pay_form.res_cd.value = "";
            return false;
        }
        if (pay_form.enc_data.value != "" && pay_form.enc_info.value != "" && pay_form.tran_cd.value !="" )
        {
            jsf__show_progress(true);
            alert("페이지 하단의 확인 버튼을 눌러 주세요.");
        }
        else
        {
             jsf__show_progress(false);
             return false;
        }
    }

	function  jsf__show_progress( show )
    {
        if ( show == true )
        {
            document.getElementById("show_pay_btn") .style.display = 'inline';
            document.getElementById("show_progress").style.display = 'inline';
            document.getElementById("show_req_btn") .style.display = 'none';
        }
        else
        { 
            document.getElementById("show_pay_btn") .style.display  = 'none';
            document.getElementById("show_progress").style.display = 'none';
            document.getElementById("show_req_btn") .style.display = 'inline';
        }
    }

    /* 최종 결제 요청*/
    function jsf__pay ()
    {
        var pay_form = document.pay_form;
        pay_form.submit();
    }

</script>
</head>

<body onload="init_orderid();chk_pay();create_goodInfo();">

<div id="content">
<form name="sm_form" method="post">

<table border="0" width="100%">
	<tr>
		<td align="center">
			<b style="color:blue">* 스마트폰 가상계좌 결제 *</b>
		</td>
	</tr>
</table>
<BR>
<table width="50%" border="0" align="center">
<tr>
<td width="50%" valign="top" align="center">
            <table border="0" width="90%" class="LINE" cellspacing="1" cellpadding="1" align="center">
      <tr>
          <td class="TEXT" colspan="2" style="text-align:center"><b>주문 정보</b></td>
      </tr>
      <tr>
          <td class="HEAD">good_name(상품명)</td>
          <td class="TEXT"><input type="text" name='good_name' maxlength="100" value='아이폰'></td>
      </tr>
      <tr> 
          <td class="HEAD">good_mny(상품금액)</td>
          <td class="TEXT"><input type="text" name='good_mny' size="9" maxlength="9" value='1000' ></td>
      </tr>
      <tr> 
          <td class="HEAD">buyr_name(주문자이름)</td>
          <td class="TEXT"><input type="text" name='buyr_name' size="20" maxlength="20" value="홍길동"></td>
      </tr>
      <tr> 
          <td class="HEAD">buyr_tel1(주문자 연락처)</td>
          <td class="TEXT"><input type="text" name='buyr_tel1' size="20" maxlength="20" value='02-2108-1000'></td>
      </tr>
      <tr> 
          <td class="HEAD">buyr_tel2(주문자 핸드폰 번호)</td>
          <td class="TEXT"><input type="text" name='buyr_tel2' size="20" maxlength="20" value='011-1234-5678'></td>
      </tr>
      <tr> 
          <td class="HEAD">buyr_mail(주문자 E-mail)</td>
          <td class="TEXT"><input type="text" name='buyr_mail' size="30" maxlength="30" value='@kcp.co.kr'></td>
      </tr>
      <tr>
          <td class="HEAD">ipgm_date(마감일자)</td>
          <td class="TEXT"><input type="text" name='ipgm_date' size="30" maxlength="10" value='20100506'></td>
      </tr>
        </table>
    </td>
</tr>
</table>

<table width="100%" border="0">
      <tr id='show_req_btn' align="center">
          <td class="TEXT" colspan="2" style="text-align:center">
              <!-- <input type="submit" value="결제등록 요청버튼"> -->
              <input type="button" name="submitChecked" onClick="kcp_AJAX();" value="결제등록요청" />
              <input type="button" name="btn" value="Reload" onClick="javascript:location.reload()">
          </td>
      </tr>
      <tr id='show_progress'  style='display:none;'>
          <td class="TEXT" colspan="2" style="text-align:center">반드시 확인버튼을 클릭 하셔야만 결제가 진행됩니다.</td>
      </tr>
      <tr id='show_pay_btn' align="center" style='display:none;'>
          <td class="TEXT" colspan="2" style="text-align:center">
              <!-- <input type="submit" value="결제버튼"> -->
              <input type="button" name="pay_btn" onClick="jsf__pay();" value="확인" />
          </td>
      </tr>
</table>
<!-- 필수 사항 -->

<!-- 요청 구분 -->
<input type='hidden' name='req_tx'       value='pay'>
<!-- 사이트 코드 -->
<input type="hidden" name='site_cd'      value="<?=$g_conf_site_cd?>">
<!-- 사이트 키 -->
<input type='hidden' name='site_key'     value='<?=$g_conf_site_key?>'>
<!-- 결제수단-->
<input type="hidden" name='pay_method'   value="VCNT">
<!-- 주문번호 -->
<input type="hidden" name='ordr_idxx'    value="">
<!-- 통화 코드 -->
<input type="hidden" name='currency'     value="410">
<!-- 결제등록 키 -->
<input type="hidden" name='approval_key' id="approval" >
<!-- 리턴 URL (kcp와 통신후 결제를 요청할 수 있는 암호화 데이터를 전송 받을 가맹점의 주문페이지 URL) -->
<!-- 반드시 가맹점 주문페이지의 URL을 입력 해주시기 바랍니다. -->
<input type="hidden" name='Ret_URL'      value="">
<!-- 현금영수증 적용 여부-->
<input type="hidden" name='disp_tax_yn'  value="Y">
<!-- 인증시 필요한 파라미터(변경불가)-->
<input type="hidden" name='ActionResult' value="vcnt">

<!-- 에스크로 사용유무 에스크로 사용 업체(가상계좌만 해당)는 Y로 세팅 해주시기 바랍니다.-->
<input type="hidden" name='escw_used'  value='N'>
<?
/*
에스크로 서비스를 이용할 경우 주석을 풀어 주시기 바랍니다.
<!-- 에스크로 정보 필드 (에스크로 신청 가맹점은 필수로 값 세팅)-->

<!-- 에스크로 결제처리모드 -->
<input type="hidden" name='pay_mod'   value='O'>
<!-- 수취인이름 -->
<input type='hidden' name='rcvr_name' value='KCPMAN'>
<!-- 수취인 연락처 -->
<input type='hidden' name='rcvr_tel1' value='02-2108-1000'>
<!-- 수취인 휴대폰 번호 -->
<input type='hidden' name='rcvr_tel2' value='010-1111-1111'>
<!-- 수취인 E-MAIL -->
<input type='hidden' name='rcvr_add1' value='서울시 구로구 구로3동'>
<!-- 수취인 우편번호 -->
<input type='hidden' name='rcvr_add2' value='우림 e-biz Center 508 -KCP-'>
<!-- 수취인 주소 -->
<input type='hidden' name='rcvr_mail' value='bobbykim@kcp.co.kr'>
<!-- 수취인 상세 주소 -->
<input type='hidden' name='rcvr_zipx' value='152750'>
<!-- 장바구니 상품 개수 -->
<input type='hidden' name='bask_cntx' value="3">
<!-- 장바구니 정보(상단 스크립트 참조) -->
<input type='hidden' name='good_info' value="">
<!-- 배송소요기간 -->
<input type="hidden" name='deli_term' value='03'>
*/
?>
<!-- 화면 크기조정 부분 - Start - -->
<input type="text" name='tablet_size'	 value="<?=$tablet_size?>"/>
<!-- 화면 크기조정 부분 - End - -->
</form>
</div>

<!-- 스마트폰에서 KCP 결제창을 레이어 형태로 구현-->
<div id="layer_vcnt" style="position:absolute; left:1px; top:1px; width:310;height:400; z-index:1; display:none;">
    <table width="310" border="-" cellspacing="0" cellpadding="0" style="text-align:center">
        <tr>
            <td>
                <iframe name="frm_vcnt" frameborder="0" border="0" width="310" height="400" scrolling="auto"></iframe>
            </td>
        </tr>
    </table>
</div>

<form name="pay_form" method="POST" action="../common/pp_ax_hub.php">
    <input type="hidden" name="req_tx"         value="<?=$req_tx?>">      <!-- 요청 구분          -->
    <input type="hidden" name="res_cd"         value="<?=$res_cd?>">      <!-- 결과 코드          -->
    <input type="hidden" name="tran_cd"        value="<?=$tran_cd?>">     <!-- 트랜잭션 코드      -->
    <input type="hidden" name="ordr_idxx"      value="<?=$ordr_idxx?>">   <!-- 주문번호           -->
    <input type="hidden" name="good_mny"       value="<?=$good_mny?>">    <!-- 휴대폰 결제금액    -->
    <input type="hidden" name="good_name"      value="<?=$good_name?>">   <!-- 상품명             -->
    <input type="hidden" name="buyr_name"      value="<?=$buyr_name?>">   <!-- 주문자명           -->
    <input type="hidden" name="buyr_tel1"      value="<?=$buyr_tel1?>">   <!-- 주문자 전화번호    -->
    <input type="hidden" name="buyr_tel2"      value="<?=$buyr_tel2?>">   <!-- 주문자 휴대폰번호  -->
    <input type="hidden" name="buyr_mail"      value="<?=$buyr_mail?>">   <!-- 주문자 E-mail      -->
	<input type="hidden" name="cash_yn"        value="<?=$cash_yn?>">	  <!-- 현금영수증 등록여부-->
    <input type="hidden" name="enc_info"       value="<?=$enc_info?>">    <!-- 암호화 정보        -->
    <input type="hidden" name="enc_data"       value="<?=$enc_data?>">    <!-- 암호화 데이터      -->
    <input type="hidden" name="use_pay_method" value="001000000000">      <!-- 요청된 결제 수단   -->
    <input type="hidden" name="rcvr_name"      value="<?=$rcvr_name?>">   <!-- 수취인 이름        -->
    <input type="hidden" name="rcvr_tel1"      value="<?=$rcvr_tel1?>">   <!-- 수취인 전화번호    -->
    <input type="hidden" name="rcvr_tel2"      value="<?=$rcvr_tel2?>">   <!-- 수취인 휴대폰번호  -->
    <input type="hidden" name="rcvr_mail"      value="<?=$rcvr_mail?>">   <!-- 수취인 E-Mail      -->
    <input type="hidden" name="rcvr_zipx"      value="<?=$rcvr_zipx?>">   <!-- 수취인 우편번호    -->
    <input type="hidden" name="rcvr_add1"      value="<?=$rcvr_add1?>">   <!-- 수취인 주소        -->
    <input type="hidden" name="rcvr_add2"      value="<?=$rcvr_add2?>">   <!-- 수취인 상세 주소   -->
</form>
</body>
</html>


