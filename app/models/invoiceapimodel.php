<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class invoiceapimodel extends CI_Model {	
	var $config_invoice;
	var $invoice_vendor_cfg = array(
		'hlc' => array(
			'company'=>'현대택배',
			'url'=>'http://www.hydex.net/ehydex/jsp/home/distribution/tracking/tracingView.jsp?InvNo='
		)
	);

	function __construct() {
		parent::__construct();
		
		$this->load->library('invoiceapi');

		if(!$this->config_invoice){
			$this->config_invoice = config_load('invoice');
		}
	}
	
	# 사용가능한 택배사 업체 정보 반환
	function get_usable_invoice_vendor(){
		if($this->config_system['invoice_use']){

			$result = array();
			foreach($this->config_invoice as $k=>$row){
				if($row['use']){
					$row['company'] = $this->invoice_vendor_cfg[$k]['company'];
					$result[$k] = $row;
				}
			}
			return $result;
		}else{
			return array();
		}
	}

	# 출고데이터 송신
	function export($arr_export_code=array(),$forced=false){

		if(!$this->config_system['invoice_use']) return false;
		if(!is_array($arr_export_code)) $arr_export_code = array($arr_export_code);

		$sqlWhereAdd = $forced ? "" : " and a.invoice_send_yn != 'y' ";

		$sql = "
			select 
			a.order_seq,
			a.export_code,
			a.delivery_company_code,
			o.payment,
			o.order_user_name,
			o.order_phone,
			o.order_cellphone,
			'' as order_zipcode,
			'' as order_address,
			'' as order_address_detail,
			o.shipping_method,
			b.shipping_cost,
			b.recipient_user_name,
			b.recipient_phone,
			b.recipient_cellphone,
			b.recipient_zipcode,
			b.recipient_address_type,
			b.recipient_address,
			b.recipient_address_street,
			b.recipient_address_detail,
			b.memo,
			c.ea,
			d.goods_name,
			g.goods_code,
			if(c.option_seq is not null,title1,title) as title1,
			if(c.option_seq is not null,title2,'') as title2,
			if(c.option_seq is not null,title3,'') as title3,
			if(c.option_seq is not null,title4,'') as title4,
			if(c.option_seq is not null,title5,'') as title5,
			if(c.option_seq is not null,option1,suboption) as option1,
			if(c.option_seq is not null,option2,'') as option2,
			if(c.option_seq is not null,option3,'') as option3,
			if(c.option_seq is not null,option4,'') as option4,
			if(c.option_seq is not null,option5,'') as option5,
			(select sum(goods_shipping_cost) from fm_order_shipping_item where shipping_seq=a.shipping_seq) as goods_shipping_cost
			from fm_goods_export a
			inner join fm_order o on o.order_seq=a.order_seq
			inner join fm_order_shipping b on b.shipping_seq=a.shipping_seq
			inner join fm_goods_export_item c on c.export_code=a.export_code
			inner join fm_order_item as d on d.item_seq=c.item_seq
			left join fm_order_item_option as e on e.item_option_seq=c.option_seq
			left join fm_order_item_suboption as f on f.item_suboption_seq=c.suboption_seq
			left join fm_goods as g on g.goods_seq=d.goods_seq
			where a.export_code in ('".implode("','",$arr_export_code)."')
			{$sqlWhereAdd}
			and a.delivery_company_code like 'auto_%'
			group by a.export_code
			order by a.export_code asc
		";
		$query = $this->db->query($sql);
		$data = $query->result_array();

		$arrData = array();
		foreach($data as $k=>$row){
			if(preg_match("/^auto_/",$row['delivery_company_code'])){
				$vendor = str_replace("auto_","",$row['delivery_company_code']);
				if($vendor){
					// 상품옵션 취합
					$row['goods_options'] = array();
					for($i=1;$i<=5;$i++) if(!empty($row['option'.$i])) {
						$row['goods_options'][] = $row['title'.$i].":".$row['option'.$i];
					}
					$row['goods_options'] = implode(" / ",$row['goods_options']);

					// 결제수단
					$row['mpayment'] = $this->arr_payment[$row['payment']];

					// 매출액
					$row['salesprice'] = $row['price'] * $row['ea'];

					// 배송비
					$row['sum_shipping_cost'] = $row['shipping_cost']+$row['goods_shipping_cost'];

					$arrData[$vendor][] = $row;
					unset($data[$k]);
				}
			}
		}

		$resultData = array();
		$resultDeliveryNumber = array();
		foreach($arrData as $vendor=>$data){
			$result = $this->invoiceapi->send($vendor.".export",$data);

			if($result['code']!='success'){
				return array(
					'code' => $result['code'],
					'msg' => $result['msg']
				);
			}

			foreach((array)$result['data'] as $row){
				if($row['export_code']){
					$sql = "update fm_goods_export set invoice_send_yn='y', delivery_number=? where export_code=?";
					$this->db->query($sql,array($row['delivery_number'],$row['export_code']));
					$resultDeliveryNumber[] = $row['delivery_number'];
				}
			}
			
			$resultData = array_merge($resultData,(array)$result['data']);
		}

		return array(
			'code' => 'success',
			'resultDeliveryNumber' => $resultDeliveryNumber,
			'msg' => number_format(count($resultData))."건 처리 완료"
		);

	}

	# 출고결과데이터 수신
	function result($vendor, $arr_export_code=array()){

		if(!$this->config_system['invoice_use']) return false;

		$this->load->model('exportmodel');
		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->helper('order');

		$cfg_order = config_load('order');

		if(!is_array($arr_export_code)) $arr_export_code = array($arr_export_code);
		
		$result = $this->invoiceapi->send($vendor.".result",array('arr_export_code'=>$arr_export_code));
	
		if($result['code']!='success'){
			return array(
				'code' => $result['code'],
				'msg' => $result['msg']
			);
		}

		$success_export_code = array();

		$this->managerInfo['mname'] = '택배업무자동화서비스';

		foreach($result['data'] as $row){
			$this->db->query("update fm_goods_export set delivery_number=? where export_code=?",array($row['delivery_number'],$row['export_code']));

			if($row['status']=='export'){
				$this->db->query("update fm_goods_export set delivery_number=? where export_code=?",array($row['delivery_number'],$row['export_code']));

				$this->exportmodel->exec_complete_export($row['export_code'],$cfg_order,false);

				$success_export_code[] = $row['export_code'];
			}

			if($row['status']=='delivery_complete'){
				$this->db->query("update fm_goods_export set delivery_number=? where export_code=?",array($row['delivery_number'],$row['export_code']));

				$this->exportmodel->exec_complete_delivery($row['export_code'],true,false);

				$success_export_code[] = $row['export_code'];
			}
		}

		return array(
			'code' => 'success',
			'msg' => number_format(count($success_export_code))."건 처리 완료",
			'success_export_code' => $success_export_code
		);
	}

	# 현대택배 인증
	# 사업자번호와 입력한 신용코드 전송하여 성공여부 반환
	function hlc_auth($auth_code){
		$result = $this->invoiceapi->send("hlc.auth",array(
			'company_no'	=> $this->config_basic['businessLicense'],
			'auth_code'		=> $auth_code,
		));
		return $result;
	}

	# 현대택배 운송장 프린트
	function hlc_invoice_print($exports){
		$config_shipping = config_load('shipping');

		$exports_chunked = array_chunk($exports,5);
		
		$resultData = array();
		
		foreach($exports_chunked as $exports){
			$result = $this->invoiceapi->send("hlc.invoice_print",array(
				'config_basic'=>$this->config_basic,
				'config_shipping'=>$config_shipping,
				'exports'=>$exports,
				'sub_division_yn'=>'y' // 상품수가 많으면 송장을 분할할지 여부
			));
			if(!$resultData)$resultData = (array)$result['data'];
			$resultData['list'] = array_merge($resultData['list'],(array)$result['data']['list']);
		}

		return $resultData;
	}

	

}
?>