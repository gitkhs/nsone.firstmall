<?php
/**
 * @author lgs
 * @version 1.0.0
 * @license copyright by GABIA_lgs
 * @since 12. 5. 29 16:54 ~
 */

function use_shipping_method(){
	$loop = $result = "";
	$codes = code_load('shipping');
	foreach($codes as $k=>$code){
		$scode = "shipping".$code['codecd'];
		$data = config_load($scode);
		$data['method'] = $code['value'];
		$data['code'] = $code['codecd'];
		if($data['code'] == 'delivery'){
			$data['method'] = "택배(선불)";
		}
		if($data['useYn']=='y') {
			$result[] = $data;
			if($data['code'] == 'delivery' && ($data['ifpostpaidDeliveryCostYn']=='y'||$data['postpaidDeliveryCostYn'] == 'y')){
				$data_postpaid = $data;
				$data_postpaid['method'] = "택배(착불)";
				$data_postpaid['code'] = "postpaid";
				$result[] = $data_postpaid;
			}
		}
	}

	if($result) $loop[0]= $result;

	$arr = $result = "";
	$codes = code_load('internationalShipping');
	foreach($codes as $code){
		$arr = config_load('internationalShipping'.$code['codecd']);
		$arr['code']  = $code['codecd'];
		$arr['method'] = $code['value'];
		if( isset($arr['company']) && $arr['company'] ){
			if($arr['useYn']=='y') $result[] = $arr;
		}
	}
	if( $result ) $loop[1] = $result;
	return $loop;
}

function get_shipping_company($international,$method_code){
	$CI =& get_instance();
	$CI->load->model('invoiceapimodel');

	foreach(get_invoice_company() as $k=>$data){
		$result[$k] = $data;
	}
	
	$loop = use_shipping_method();
	if( $international == 'domestic' ){
		foreach($loop[0] as $data){
			if( $data['code'] == $method_code )
			{
				foreach($data['deliveryCompanyCode'] as $delivery_code)
				{
					$arr = config_load('delivery_url',$delivery_code);
					$result[$delivery_code] = $arr[$delivery_code];
				}
			}
		}
	}else{
		//return $loop[1];
	}
	return $result;

}

function get_invoice_company(){
	$CI =& get_instance();
	$CI->load->model('invoiceapimodel');
	$result = array();
	$invoice_vendor = $CI->invoiceapimodel->get_usable_invoice_vendor();
	foreach($invoice_vendor as $delivery_code=>$vendor){
		$result['auto_'.$delivery_code] = array(
			'company' => $vendor['company'].'(업무자동화)',
			'url' => $CI->invoiceapimodel->invoice_vendor_cfg[$delivery_code]['url']
		);
	}
	return $result;
}

function get_international_code($key){
	$arr = $result = "";
	$codes = code_load('internationalShipping');
	foreach($codes as $code){
		$arr = config_load('internationalShipping'.$code['codecd']);
		$arr['code']  = $code['codecd'];
		$arr['method'] = $code['value'];
		if( isset($arr['company']) && $arr['company'] ){
			if($arr['useYn']=='y') $result[] = $arr;
		}
	}
	return $result[$key]['code'];
}

function get_international_company(){
	$arr = $result = "";
	$codes = code_load('internationalShipping');
	foreach($codes as $code){
		$arr = config_load('internationalShipping'.$code['codecd']);
		$arr['code']  = $code['codecd'];
		$arr['method'] = $code['value'];
		if( isset($arr['company']) && $arr['company'] ){
			if($arr['useYn']=='y') $result[] = $arr;
		}
	}
	return $result;
}

function get_delivery_company($code,$mode='company'){
	$arr = get_shipping_company('domestic','delivery');
	return $arr[$code][$mode];
}

function get_delivery_url($code=null){
	$CI =& get_instance();
	$CI->load->model('invoiceapimodel');
	
	$arr = config_load('delivery_url',$code);

	if($code){
		if(preg_match("/^auto_/",$code)){
			$arrAuto = $CI->invoiceapimodel->invoice_vendor_cfg[str_replace('auto_','',$code)];
			$arr = $arrAuto;
		}
	}else{
		$arrAuto = $CI->invoiceapimodel->invoice_vendor_cfg;
		foreach($arrAuto as $code=>$row){
			$arr['auto_'.$code] = $row;
		}
	}
	

	return $arr;
}

function get_domestic_method($code){

	if($code == 'postpaid'){
		$arr = code_load('shipping','delivery');
		$arr[0]['value'] = "택배 (착불)";
	}else if($code == 'delivery'){
		$arr = code_load('shipping',$code);
		$arr[0]['value'] = "택배 (선불)";
	}else{
		$arr = code_load('shipping',$code);
	}
	return $arr[0]['value'];
}

function get_international_method($code){
	$arr = code_load('internationalShipping',$code);
	return $arr[0]['value'];
}

function get_international_method_code($code){
	$arr = array('EMS'=>'code23','FEDEX'=>'code24');
	return $arr[$code];
}
