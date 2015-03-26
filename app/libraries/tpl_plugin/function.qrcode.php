<?php

/**
 * @author cow
 */

function qrcode($key,$value,$matrixPointSize=4)
{
	$CI =& get_instance();
	$CI->load->library('QRcode');
	
	$domain = !empty($CI->config_system['domain']) ? $CI->config_system['domain'] : $CI->config_system['sub_domain'];
	$domain = $domain ? $domain : $_SERVER['HTTP_HOST'];

	switch($key){
		case "shop":
			$matrixPointSize = $value ? $value : $matrixPointSize; 
			$string = "http://{$domain}";
		break;
		case "goods":
			$string = "http://{$domain}/goods/view?no={$value}";
		break;
		case "category":
			$string = "http://{$domain}/goods/catalog?code={$value}";
		break;
		case "brand":
			$string = "http://{$domain}/goods/brand?code={$value}";
		break;
		case "event":
			$query = $CI->db->query("select tpl_path from fm_event where event_seq='{$value}'");
			$result = $query->row_array();
			$string = "http://{$domain}/page/index?tpl=".urlencode($result['tpl_path']);
		break;
		case "delivery":
			$CI->load->model('exportmodel');
			$export = $CI->exportmodel->get_export($value);
			$string = !empty($export['tracking_url']) ? $export['tracking_url'] : "";
		break;
		case "url":
		default:
			$string = $value;
		break;
	}
	
	$matrixPointSize = min(max((int)$matrixPointSize, 1), 10);
	
	if(!is_dir(ROOTPATH."data/qrcode")){
		@mkdir(ROOTPATH."data/qrcode");
	}
	
	$imageFileName = 'qrcode_'.md5($string.'|'.$matrixPointSize).'.png';
	$imageFilePath = ROOTPATH."data/qrcode/".$imageFileName;
	$imageFileUrl = "http://".$domain."/data/qrcode/".$imageFileName;

	QRcode::png($string,$imageFilePath,null,$matrixPointSize);

	$imageTag = "<a href='{$string}' target='_blank'><img src='{$imageFileUrl}' align='absmiddle' title='{$string}'/></a>";
	
	echo $imageTag;
		
}
?>