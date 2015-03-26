<?php
/* 네이버 맵 출력 */
function showNaverMapApi($width="500", $height="400", $mapAdress="", $view_name="")
{
	$CI =& get_instance();
	$CI->load->library('SofeeXmlParser');
	$maparr = $CI->config_basic;
	$view_name	= ($view_name) ?	$view_name : $maparr['companyName'];
	$id_name = "NaverMap".uniqid();

	if(!$mapAdress)	$addr = urlencode($maparr['companyAddress'].$maparr['companyAddressDetail']);
	else			$addr = urlencode($mapAdress);
	
	$xmlParser = new SofeeXmlParser();
	$key = $maparr['mapKey'];
	$url = "http://openapi.map.naver.com/api/geocode.php?key=".$key."&encoding=utf-8&coord=latlng&query=".$addr;
	$xmlParser->parseFile($url);
	$tree = $xmlParser->getTree();

	if ($tree['error']['error_code']['value'] == '020' || !$key)
		return " err ";

	if($tree['geocode']['item'][0]['point']['x']['value']){
		$point = array('y'=>$tree['geocode']['item'][0]['point']['x']['value'], 'x'=>$tree['geocode']['item'][0]['point']['y']['value']);
	}else{
		$point = array('y'=>$tree['geocode']['item']['point']['x']['value'], 'x'=>$tree['geocode']['item']['point']['y']['value']);
	}
	
	$returnHTML = "<script type='text/javascript' src='http://openapi.map.naver.com/openapi/naverMap.naver?ver=2.0&key=".$key."'></script>";
	$returnHTML .= "<script type='text/javascript' src='/app/javascript/js/naverMap.js'></script>";
	$returnHTML .= "<div id = '".$id_name."' style='border:1px solid #000; width:".$width."px; height:".$height."px; margin:20px;'></div>";
	$returnHTML .= "<script type='text/javascript'>callMap('".$id_name."','".$point['x']."','".$point['y']."','".$width."','".$height."','".$view_name."');</script>";
	
	return $returnHTML;
}
?>