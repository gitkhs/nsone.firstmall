<?php

/**
 * @author ocw
 */

function confirmLicenseLink($string)
{
	$CI =& get_instance();
	$CI->load->helper('readurl');

	$businessLicense = preg_replace("/[^0-9]/",'',$CI->config_basic['businessLicense']);

	$url = "https://www.ftc.go.kr/info/bizinfo/communicationViewPopup.jsp?wrkr_no={$businessLicense}";
	//$res = readurl($url);

	//if($res){
		$html = "";
		$html .= "<a href=\"javascript:;\" onclick=\"window.open('{$url}','communicationViewPopup','width=750,height=700')\">";
		$html .= $string;
		$html .= "</a>";
	/*
	}else{
		$html = "";
		$html .= "<a href=\"javascript:;\" onclick=\"openDialogAlert('공정거래위원회 데이터베이스에서<br />사업자번호 {$businessLicense}의 정보를 찾을 수 없습니다.',500,155)\">";
		$html .= $string;
		$html .= "</a>";
	}
	*/

	return $html;
}
?>