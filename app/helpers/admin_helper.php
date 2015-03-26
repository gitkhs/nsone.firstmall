<?
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	/* 가비아 출력 패널 (배너,팝업 -- page direct ) */
	function getGabiaPannel($code){

		$CI =& get_instance();
		$CI->load->helper('readurl');

		$data = array(
			'service_code'	=> SERVICE_CODE,
			'hosting_code'	=> $CI->config_system['service']['hosting_code'],
			'subDomain'		=> $CI->config_system['subDomain'],
			'domain'		=> $CI->config_system['domain'],
			'hostDomain'	=> $_SERVER['HTTP_HOST'],
			'shopSno'		=> $CI->config_system['shopSno'],
			'expire_date'	=> $CI->config_system['service']['expire_date'],
		);

		$res = readurl("http://interface.firstmall.kr/firstmall_plus/request.php?cmd=getGabiaPannel&code={$code}&isdemo=".$CI->isdemo['isdemo'],$data);

		// 배너 한꺼번에 호출 :: 2014-10-24 lwh
		if($code == 'allbanner'){
			$tmp_arr					= explode("[END]",$res);
			foreach($tmp_arr as $val){
				preg_match("/^\[S\:[^]]*\]/",trim($val),$matchs);
				$arrkey					= substr($matchs[0],3,-1);
				$banner_arr[$arrkey]	= str_replace($matchs[0],'',$val);
			}
			$res = $banner_arr;
		}

		return $res;
	}

?>