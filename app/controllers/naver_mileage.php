<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class naver_mileage extends front_base {
	/*
	CREATE TABLE `fm_naver_mileage` (
	  `mileage_request_seq` int(10) unsigned NOT NULL auto_increment,
	  `regist_date` datetime NOT NULL COMMENT '등록일시',
	  `baseAccumRate` varchar(4) null COMMENT '가맹점이 설정한 기본 적립율',
	  `addAccumRate` varchar(4) null COMMENT '네이버가 지급하는 추가 적립율',
	  `mileageUseAmount` bigint(20) null COMMENT '이용자가 설정한 네이버마일리지 액수',
	  `cashUseAmount` bigint(20) null COMMENT '이용자가 설정한 네이버캐쉬 액수',
	  `totalUseAmount` bigint(20) null COMMENT '네이버마일리지 사용금액과 네이버캐쉬 사용금액의 합',
	  `balanceAmount` bigint(20) null COMMENT '이용자의 네이버마일리지 총 잔액',
	  `status` enum('NONE','OK','CANCEL','ERROR') not null default 'none' COMMENT '상태',
	  `order_seq` bigint(20) unsigned null COMMENT '주문번호',
	  `orderProductName` varchar(200) null COMMENT '대표 상품명',
	  `qty` int(9) unsigned null COMMENT '주문수량',
	  `orderAmount` bigint(19) unsigned null COMMENT '주문금액',
	  `amount` bigint(19) unsigned null COMMENT '적립 대상 금액',
	  `isMember` enum('Y','N') null COMMENT '가맹점 회원 여부',
	  `isConfirmed` enum('Y','N') null COMMENT '입금확인여부',
	  `items` text null,
	  PRIMARY KEY  (`mileage_request_seq`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1075305656;

	CREATE TABLE `fm_naver_mileage_batch` (
		  `batch_seq` int(10) unsigned NOT NULL auto_increment,
		  `work_type` enum('confirm','cancel','delivery') not null default 'confirm' COMMENT '작업종류',
		  `order_seq` bigint(20) unsigned not null COMMENT '주문번호',
		  `status` enum('none','done','error') not null default 'none',
		  PRIMARY KEY  (`batch_seq`)
	)
	*/
	public function __construct()
	{
		// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

		parent::__construct();
		$this->load->model('navermileagemodel');
		$this->load->model('goodsmodel');
		$this->load->helper('readurl');

		$config_naver_mileage = $this->navermileagemodel->naver_mileage_display();
		if( $config_naver_mileage['naver_mileage_api_id'] && $config_naver_mileage['naver_mileage_secret']){
			$this->api_id = $config_naver_mileage['naver_mileage_api_id'];
			$this->secret = $config_naver_mileage['naver_mileage_secret'];
		}

		if($config_naver_mileage['naver_mileage_yn'] == 'y'){
			$this->mode = "service";
		}

		if($config_naver_mileage['naver_mileage_yn'] == 't'){
			$this->mode = "test";
		}

		$this->doneUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].'/naver_mileage/bridge');
	}

	public function _get_signature($timestamp)
	{
		return hash_hmac("sha1", $timestamp.$this->doneUrl, $this->secret);
	}

	public function get_accum_rate()
	{

		$api_id = $this->api_id;
		if($this->mode == 'test'){
			$url = 'http://sandbox-api.mileage.naver.com/v2/partner/'.$api_id.'/getAccumRate';
		}else{
			$url = 'http://api.mileage.naver.com/v2/partner/'.$api_id.'/getAccumRate';
		}

		$Ncisy = get_cookie('Ncisy');
		if($Ncisy){
			$str = urldecode($_COOKIE['Ncisy']);
			$r_Ncisy = $this->navermileagemodel->naver_mileage_decoding($str);

			if($r_Ncisy['e'] < time()){
				delete_cookie('Ncisy');
			}else{
				if($r_Ncisy['ba'])	$out = '{"baseAccumRate":"'.$r_Ncisy['ba'].'","addAccumRate":"'.$r_Ncisy['aa'].'","serviceStatus":"STATUS_002","resultCode":"1000","resultMessage":"\uc131\uacf5"}';
			}
		}

		if(!$out){
			$out = readurl($url);
			$obj_result = json_decode($out);
			if($obj_result->response->resultCode == '1000'){
				echo json_encode($obj_result->response);
			}
		}else{
			echo $out;
		}

	}

	public function get_popup_url()
	{
		$settle_price = $_GET['price'];
		$timestamp = time();
		$doneUrl = $this->doneUrl;
		$secret = $this->secret;
		$api_id = $this->api_id;
		$signature = $this->_get_signature($timestamp);

		if($this->mode == 'test'){
			$popup_url = 'https://sandbox-service.mileage.naver.com/service/v2/accumulation/' .$api_id;
		}else{
			$popup_url = 'https://service.mileage.naver.com/service/v2/accumulation/'.$api_id;
		}

		$popup_url .= "?doneUrl=".$doneUrl;
		$popup_url .= "&amp;Ncisy=".$_GET['Ncisy'];
		$popup_url .= "&amp;reqTxId=";
		$popup_url .= "&amp;maxUseAmount=".$settle_price;
		$popup_url .= "&amp;sig=".$signature;
		$popup_url .= "&amp;timestamp=".$timestamp;

		echo $popup_url;
	}

	public function bridge()
	{
		// 하루 전 미처리 데이터 삭제
		$this->navermileagemodel->day_request_delete();

		// 중복데이터 삭제
		$this->navermileagemodel->request_delete($_GET['reqTxId']);

		if($_GET['reqTxId']){
			$insert_param = array(
				'reqTxId' => $_GET['reqTxId'],
				'regist_date' => date('Y-m-d H:i:s'),
				'status' => 'NONE',
				'baseAccumRate' => $_GET['baseAccumRate'],
				'addAccumRate' => $_GET['addAccumRate'],
				'mileageUseAmount' => $_GET['mileageUseAmount'],
				'cashUseAmount' => $_GET['cashUseAmount'],
				'totalUseAmount' => $_GET['totalUseAmount']
			);

			$this->db->insert('fm_naver_mileage', $insert_param);
			$mileage_request_seq = $this->db->insert_id();

			$mileageUseAmount = $cashUseAmount = $reqTxId = '';
			if($_GET['resultCode'] == 'OK'){
				$mileageUseAmount = $_GET['mileageUseAmount'];
				$cashUseAmount = $_GET['cashUseAmount'];
				$reqTxId = $_GET['reqTxId'];

				if($mileageUseAmount)
				{
					echo("<script type='text/javascript'>opener.document.getElementById('naver_mileage_mileageUseAmount_msg').innerHTML='마일리지: ".number_format($mileageUseAmount)."';</script>");
				}

				if($cashUseAmount)
				{
					echo("<script type='text/javascript'>opener.document.getElementById('naver_mileage_cashUseAmount_msg').innerHTML='캐쉬: ".number_format($cashUseAmount)."';</script>");
				}

				echo("<script type='text/javascript'>opener.document.getElementById('naver_mileage_txId').value='".$reqTxId."';opener.order_price_calculate();self.close();</script>");
			}
		}
	}

	public function complete()
	{
		$this->load->model('ordermodel');
		$this->load->model('categorymodel');
		$this->load->model('goodsmodel');

		$order_seq = $_GET['order_seq'];
		$session_id = $this->session->userdata('session_id');
		$isMember = "N";
		if($session_id) $isMember = "Y";

		$api_id = $this->api_id;
		if($this->mode == 'test'){
			$url = 'https://sandbox-api.mileage.naver.com/v2/partner/'.$api_id.'/payment';
		}else{
			$url = 'https://api.mileage.naver.com/v2/partner/'.$api_id.'/payment';
		}

		$data = $this->navermileagemodel->get_request($order_seq,'NONE');
		//debug_var($data);
		if($data['mileage_request_seq']){

			$data_order = $this->ordermodel->get_order($order_seq);
			$data_item = $this->ordermodel->get_item($order_seq);

			foreach($data_item as $key=>$item){
				$ea = 0;
				$unit_price = 0;
				$tot = 0;
				$category = array();
				$data_option 	= $this->ordermodel->get_option_for_item($item['item_seq']);
				$data_suboption = $this->ordermodel->get_suboption_for_item($item['item_seq']);

				if($data_option)foreach($data_option as $option){
					$ea += $option['ea'];
					$tot += $option['ea'] * $option['ori_price'];
					if(!$unit_price) $unit_price = $option['ori_price'];
					$discount += $option['member_sale']*$option['ea'];
					$discount += $option['coupon_sale'];
					$discount += $option['promotion_code_sale'];
					$discount += $option['fblike_sale'];
					$discount += $option['mobile_sale'];

				}
				if($data_suboption)foreach($data_suboption as $suboption){
					$ea += $suboption['ea'];
					$tot += $suboption['ea'] * $suboption['price'];
				}

				$categorys = $this->goodsmodel->get_goods_category($item['goods_seq']);

				if($categorys) foreach($categorys as $key => $data_category){
					if( $data_category['link'] == 1 ){
						$category_code = $this->categorymodel->split_category($data_category['category_code']);
					}
				}

				if($category_code)foreach($category_code as $code){
					$category[] = $this->categorymodel->one_category_name($code);
				}

				$str_item =  $item['goods_seq'].",".base64_encode($item['goods_name']).",".$unit_price.",".$ea.",".$tot.",";
				if($category){
					$str_item .= base64_encode(implode('>',$category));
				}

				$goods_shipping_cost += $data_item['goods_shipping_cost'];
				$arr_item[] = $str_item;
				$tot_ea += $ea;
			}
			$items = "";
			if($arr_item){
				$items = implode('|',$arr_item);
			}
			if($data_order['step'] == '15'){
				$isConfirmed = "N";
			}
			if($data_order['step'] == '25'){
				$isConfirmed = "Y";
			}

			$goods_name = $data_item[0]['goods_name'];
			$goods_name_len = mb_strlen($goods_name, "UTF-8");
			if($goods_name_len > 200){
				$goods_name = mb_substr($goods_name,0,200,"UTF-8");
			}

			$order_amount = $data_order['settleprice']+$data['cashUseAmount']+$data['mileageUseAmount']+$data_order['emoney']+$discount;
			$amount = $data_order['settleprice']+$data['cashUseAmount']+$data_order['cash']-$data_order['shipping_cost']-$goods_shipping_cost;

			$post_data = array(
				'format' => 'xml',
				'reqTxId' => $data['reqTxId'],
				'orderNo' => $order_seq,
				'orderProductName' => base64_encode($goods_name),
				'qty' => $tot_ea,
				'orderAmount' => $order_amount,
				'amount' => $amount,
				'mileageUseAmount' => $data['mileageUseAmount'],
				'cashUseAmount' => $data['cashUseAmount'],
				'isMember' => $isMember,
				'isConfirmed' => $isConfirmed,
				'items' => $items,
				'secret' => $this->secret,
			);

			if( $data_order['step']=='15' || $data_order['step']=='25' ){
				$update_data = array(
					'orderProductName' => base64_encode($goods_name),
					'qty' => $tot_ea,
					'orderAmount' => $order_amount,
					'amount' => $amount,
					'isMember' => $isMember,
					'isConfirmed' => $isConfirmed,
					'status' => 'OK',
					'items' => $items
				);
				if(!$data['orderProductName']){
					$this->db->where('mileage_request_seq', $data['mileage_request_seq']);
					$this->db->update('fm_naver_mileage', $update_data);
					$out = readurl($url,$post_data);

				}
			}
		}

	}

	public function error_file($post_data){

		ob_start();
		var_dump($post_data);
		$somecontent = ob_get_contents();
		ob_end_clean();
		$mypath=BASEPATH."../data/";
		$filename = $mypath.'message.txt';
		$handle = fopen($filename,"a");
		fwrite($handle, chr(10)."--------------------------------- start ".date("YmdHis")."---------------------------------".chr(10));
		fwrite($handle,$somecontent);
		fwrite($handle, chr(10)."----------------------------------------------------------------------------".chr(10));
		fclose($handle);
		@chmod($filename,0777);
	}

	public function batch_info()
	{
		$this->template->template_dir = BASEPATH."../partner";
		$this->template->compile_dir	= BASEPATH."../_compile/";
		$this->template->define(array('tpl'=>'naver_mileage.html'));
		$this->template->print_('tpl');
	}

	// 배치잡 실행
	public function batch()
	{
		$data_batch = $this->navermileagemodel->get_batch();
		foreach($data_batch as $batch){
			$this->{'_'.$batch['work_type']}($batch);
		}
		echo "OK";
	}

	// 거래 확정
	public function _confirm($data_batch)
	{
		$api_id = $this->api_id;
		$data = $this->navermileagemodel->get_request($data_batch['order_seq'],'OK');
		if($this->mode == 'test'){
			$url = 'https://sandbox-api.mileage.naver.com/v2/partner/'.$api_id.'/payment/'.$data['mileage_request_seq'].'/stated';
		}else{
			$url = 'https://api.mileage.naver.com/v2/partner/'.$api_id.'/payment/'.$data['mileage_request_seq'].'/stated';
		}

		if($data['mileage_request_seq']){
			$post_data['format'] = "xml";
			$post_data['secret'] = $this->secret;
			$out = readurl($url,$post_data);
			$result = xml2array($out);
			if($result['resultCode'] == 1000){
				$update_data = array('isConfirmed' => 'Y');
				$this->db->where('mileage_request_seq', $data['mileage_request_seq']);
				$this->db->update('fm_naver_mileage', $update_data);
				$this->db->status_batch('done',$data['batch_seq']);
			}
		}
	}

	public function _cancel($data_batch)
	{
		$this->load->model('ordermodel');

		$data_order = $this->ordermodel->get_order($data_batch['order_seq']);
		if($data_order['step'] == 85){
			$this->_order_cancel($data_batch);
		}else{
			$this->_item_cancel($data_batch);
		}
	}

	// 거래취소
	public function _order_cancel($data_batch)
	{
		$api_id = $this->api_id;
		$data = $this->navermileagemodel->get_request($data_batch['order_seq'],'OK');
		if($this->mode == 'test'){
			$url = 'https://sandbox-api.mileage.naver.com/v2/partner/'.$api_id.'/payment/'.$data['mileage_request_seq'].'/cancel/stated';
		}else{
			$url = 'https://api.mileage.naver.com/v2/partner/'.$api_id.'/payment/'.$data['mileage_request_seq'].'/cancel/stated';
		}

		if($data['mileage_request_seq']){
			$post_data['format'] = "xml";
			$post_data['secret'] = $this->secret;
			$out = readurl($url,$post_data);
			$result = xml2array($out);
			if($result['resultCode'] == 1000){
				$update_data['status'] = 'CANCEL';
				$this->db->where('mileage_request_seq', $data['mileage_request_seq']);
				$this->db->update('fm_naver_mileage', $update_data);
				$this->db->status_batch('done',$data_batch['batch_seq']);
			}
		}
	}

	// 부분취소
	public function _item_cancel($data_batch)
	{
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');

		$api_id = $this->api_id;
		$data_request = $this->navermileagemodel->get_request($data_batch['order_seq'],'OK');
		if($this->mode == 'test'){
			$url = 'https://sandbox-api.mileage.naver.com/v2/partner/'.$api_id.'/repayment/'.$data['mileage_request_seq'];
		}else{
			$url = 'https://api.mileage.naver.com/v2/partner/'.$api_id.'/repayment/'.$data['mileage_request_seq'];
		}

		$qty = 0;
		$cancel_ea = 0;
		$data_order = $this->ordermodel->get_order($data_batch['order_seq']);
		$data_item = $this->ordermodel->get_item($data_batch['order_seq']);
		foreach($data_item as $k => $item){

			$ea = 0;
			$unit_price = 0;
			$category = array();
			$data_option 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$data_suboption = $this->ordermodel->get_suboption_for_item($item['item_seq']);

			if($data_option)foreach($data_option as $option){
				$option['ea'] = $option['ea'] - $option['step85'];
				$ea += $option['ea'];
				$tot += $option['ea'] * $option['price'];
				if(!$unit_price == 0) $unit_price = $option['price'];
			}
			if($data_suboption)foreach($data_option as $suboption){
				$suboption['ea'] = $suboption['ea'] - $suboption['step85'];
				$ea += $suboption['ea'];
				$tot += $suboption['ea'] * $suboption['price'];
			}

			$tot_ea += $ea;
			if($ea <= 0) continue;

			$categorys = $this->goodsmodel->get_goods_category($data_item['goods_seq']);
			if($categorys) foreach($categorys as $key => $data){
				if( $data['link'] == 1 ){
					$category_code = $this->categorymodel->split_category($data['category_code']);
				}
			}

			if($category_code)foreach($category_code as $code){
				$category[] = $this->categorymodel->one_category_name($code);
			}

			$str_item =  $data_item['goods_seq'].",".$data_item['goods_name'].",".$unit_price.",".$ea.",".$tot.",";
			if($category){
				$str_item .= implode('>',$category);
			}

			$arr_item[] = $str_item;
		}

		$data_refund = $this->ordermodel->get_refund_for_order($data_batch['order_seq']);
		foreach($data_refund as $k => $refund){
			if($refund['status']=='complete'){
				$cancel_ea += $refund['ea'];
				$cancel_price += $refund['refund_price'];
			}
		}
		$tot_ea -= $cancel_ea;
		$orderAmount = $data_option['settleprice'] - $cancel_price;

		$items = "";
		if($arr_item){
			$items = implode('|',$arr_item);
		}

		if($data['mileage_request_seq']){
			$post_data['format'] = "xml";
			$post_data['secret'] = $this->secret;
			$post_data['orderNo'] = $data_order['order_seq'];
			$post_data['orderProductName'] = $data_option[0]['goods_name'];
			$post_data['qty'] = $tot_ea;
			$post_data['orderAmount'] = $orderAmount;
			$post_data['amount'] = $data_request['amount'];
			$post_data['mileageUseAmount'] = $data_request['mileageUseAmount'];
			$post_data['cashUseAmount'] = $data_request['cashUseAmount'];
			$post_data['isMember'] = $data_request['isMember'];
			$post_data['isConfirmed'] = $data_request['isConfirmed'];
			$post_data['items'] = $items;

			$out = readurl($url,$post_data);
			$result = xml2array($out);
			if($result['resultCode'] == 1000){
				$update_data['status'] = 'CANCEL';
				$update_data['baseAccumRate'] = $result['baseAccumRate'];
				$update_data['addAccumRate'] = $result['addAccumRate'];
				$update_data['baseAccumAmount'] = $result['baseAccumAmount'];
				$update_data['addAccumAmount'] = $result['addAccumRate'];
				$this->db->where('mileage_request_seq', $data_request['mileage_request_seq']);
				$this->db->update('fm_naver_mileage', $update_data);
				$this->db->status_batch('done',$data_batch['batch_seq']);
			}
		}
	}

	// 배송정보전달
	public function _delivery($data_batch)
	{
		$this->load->model('ordermodel');
		$this->load->model('exportmodel');

		$api_id = $this->api_id;
		$data = $this->navermileagemodel->get_request($data_batch['order_seq'],'OK');
		if($this->mode == 'test'){
			$url = 'https://sandbox-api.mileage.naver.com/v2/partner/'.$api_id.'/delivery/'.$data['mileage_request_seq'].'/invoice';
		}else{
			$url = 'https://api.mileage.naver.com/v2/partner/'.$api_id.'/delivery/'.$data['mileage_request_seq'].'/invoice';
		}

		$data_order = $this->ordermodel->get_order($data_batch['order_seq']);
		$data_export = $this->exportmodel->get_export_for_order($data_batch['order_seq']);

		if($data['mileage_request_seq']){
			$post_data['format'] = "xml";
			$post_data['secret'] = $this->secret;
			$post_data['shippedDate'] = $this->secret;
			$post_data['recipientName'] = $data_order['recipient_user_name'];
			$post_data['recipientTelNo'] = $data_order['recipient_phone'];
			$post_data['recipientCphNo'] = $data_order['recipient_cellphone'];
			if($data_order['international'] == 'domestic'){
				$address = $data_order['recipient_address'];
				if($data_order['recipient_address_detail']) $address .= " ".$data_order['recipient_address_detail'];
			}else{
				$address = $data_order['international_address'];
				if($data_order['international_town_city']) $address .= " ".$data_order['international_town_city'];
				if($data_order['international_county']) $address .= " ".$data_order['international_county'];
				if($data_order['international_country']) $address .= " ".$data_order['international_country'];
			}

			$post_data['address'] = $address;
			$post_data['serviceCompany'] = $data_export[0]['mdelivery'];
			$post_data['invoiceNo'] = $data_export[0]['mdelivery_number'];

			$out = readurl($url,$post_data);
			$result = xml2array($out);
			if($result['resultCode'] == 1000){
				$update_data = array('isConfirmed' => 'Y');
				$this->db->where('mileage_request_seq', $data['mileage_request_seq']);
				$this->db->update('fm_naver_mileage', $update_data);
				$this->db->status_batch('done',$data_batch['batch_seq']);
			}
		}
	}
}
