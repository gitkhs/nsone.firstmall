<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class returns_process extends admin_base {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('validation');
		$this->load->model('returnmodel');
		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$auth = $this->authmodel->manager_limit_act('refund_act');
		if(!$auth){
			pageBack("관리자 권한이 없습니다.");
			exit;
		}
	}

	public function modify()
	{
		$data_return = $this->returnmodel->get_return($_POST['return_code']);
		$data_return_item = $this->returnmodel->get_return_item($data_return['return_code']);

		/* 완료상태일때는 메모만 수정*/
		if($data_return['status']=='complete'){
			$this->db->where('return_code',$_POST['return_code']);
			$this->db->update('fm_order_return',array('admin_memo'=>$_POST['admin_memo']));
			$callback = "parent.document.location.reload();";
			openDialogAlert("반품 관리 메모가 수정 되었습니다.",400,140,'parent',$callback);
			exit;
		}

		$this->validation->set_rules('phone[]', '연락처','trim|required|numeric|max_length[4]|xss_clean');
		$this->validation->set_rules('cellphone[]', '휴대폰','trim|required|numeric|max_length[4]|xss_clean');
		if($_POST['return_method'] == 'shop'){
			$this->validation->set_rules('senderZipcode[]', '우편번호','trim|required|numeric|max_length[4]|xss_clean');
			$this->validation->set_rules('senderAddress', '주소','trim|required|xss_clean');
			$this->validation->set_rules('senderAddressDetail', '상세주소','trim|required|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}



		$zipcode = "";
		if($_POST['phone'][1] && $_POST['phone'][2]) $phone = implode('-',$_POST['phone']);
		if($_POST['cellphone'][1] && $_POST['cellphone'][2]) $cellphone = implode('-',$_POST['cellphone']);
		if($_POST['senderZipcode'][1]) $zipcode = implode('-',$_POST['senderZipcode']);

		$update_param['cellphone'] 		= $cellphone;
		$update_param['phone'] 			= $phone;
		$update_param['sender_zipcode'] = $zipcode;
		$update_param['sender_address_type'] = ($_POST['senderAddress_type'])?$_POST['senderAddress_type']:"zibun";
		$update_param['sender_address'] = $_POST['senderAddress'];
		$update_param['sender_address_street'] = $_POST['senderAddress_street'];
		$update_param['sender_address_detail'] = $_POST['senderAddressDetail'];
		$update_param['return_reason'] 	= $_POST['return_reason'];
		$update_param['admin_memo'] 	= $_POST['admin_memo'];
		$update_param['return_method']	= $_POST['return_method'];
		$update_param['manager_seq']	= $this->managerInfo['manager_seq'];
		$update_param['return_type']	= $_POST['return_type'];
		$update_param['return_shipping_price']	= $_POST['return_shipping_price'];

		if($data_return['status'] != "complete"){
			$update_param['status'] 		= $_POST['status'];
		}

		if($_POST['status'] == 'complete'){
			if($data_return['status']!="complete"){
				$update_param['return_date'] = date('Y-m-d H:i:s');
				// 재고 더하기
				foreach($data_return_item as $item){

					if( $item['goods_kind'] == 'coupon' ) {//쿠폰상품 적립금/포인트, 재고, 할인쿠폰 반환없음
						$retuns_goods_coupon_ea++;
						continue;
					}

					if($item['opt_type'] == 'opt'){

						$this->goodsmodel->stock_option(
							'+',
							$item['ea'],
							$item['goods_seq'],
							$item['option1'],
							$item['option2'],
							$item['option3'],
							$item['option4'],
							$item['option5']
						);
					}else{

						$this->goodsmodel->stock_suboption(
							'+',
							$item['ea'],
							$item['goods_seq'],
							$item['title1'],
							$item['option1']
						);
					}
				}
			}

			// 재주문 넣기(맞교환)
			if($update_param['return_type'] == 'exchange'){
				$this->ordermodel->reorder($data_return['order_seq'],$data_return['return_code']);
			}
		}


		$this->db->where('return_code',$_POST['return_code']);
		$this->db->update('fm_order_return',$update_param);

		foreach($_POST['reason'] as $return_item_seq=>$reason_code)
		{
			unset($update_param);
			$update_param['reason_code'] = $reason_code;
			if (!empty($_POST['reason_desc'][$return_item_seq])) {
				$update_param['reason_desc'] = $_POST['reason_desc'][$return_item_seq];
			}
			$this->db->where('return_item_seq',$return_item_seq);
			$this->db->update('fm_order_return_item',$update_param);
		}

		// 품절체크를 위한 변수선언
		$r_runout_goods_seq = array();

		/* 재고조정 히스토리 저장 */
		if($_POST['status'] == 'complete'){
			if($data_return['status']!="complete"){
				if( !$retuns_goods_coupon_ea ) {//쿠폰상품 적립금/포인트, 재고, 할인쿠폰 반환없음
					$this->load->model('stockmodel');

					$data = array();
					$data['reason']			= 'input';
					$data['supplier_seq']	= '';
					$data['reason_detail']	= '반품';
					$data['stock_date']		= date('Y-m-d H:i:s');
					$stock_code = $this->stockmodel->insert_stock_history($data);

					foreach($data_return_item as $item){
						$data = array();
							$data['goods_name']			= $item['goods_name'];
						$data['option_type']		= $item['option_type'] == 'opt' ? 'option' : 'suboption';
						$data['stock_code']			= $stock_code;
						$data['goods_seq']			= $item['goods_seq'];
						$data['prev_supply_price']	= $item['supply_price'];
						$data['supply_price']		= $item['supply_price'];
						$data['ea']					= $item['ea'];

						for($i=1;$i<=5;$i++){
							if(!empty($item['title'.$i])){
								$data['title'.$i] = $item['title'.$i];
								$data['option'.$i] = $item['option'.$i];
							}
						}

						$this->stockmodel->insert_stock_history_item($data);

						// 품절체크를 업데이트를 위한 변수정의
						if(!in_array($item['goods_seq'],$r_runout_goods_seq)){
							$r_runout_goods_seq[] = $item['goods_seq'];
						}
					}

					// 품절체크
					foreach($r_runout_goods_seq as $goods_seq){
						$this->goodsmodel->runout_check($goods_seq);
					}
				}

				/* 로그저장 */
				$logTitle = "반품완료";
				$logDetail = "관리자가 반품완료처리를 하였습니다.";
				$logParams	= array('return_code' => $_POST['return_code']);
				$this->load->model('ordermodel');
				$this->ordermodel->set_log($data_return['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail,$logParams);

				$callback = "parent.document.location.reload();";
				openDialogAlert("반품처리가 완료 되었습니다.",400,140,'parent',$callback);
				exit;
			}
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("반품정보가 수정 되었습니다.",400,140,'parent',$callback);
	}

	public function batch_reverse_return(){
		$result = array();
		foreach($_POST['code'] as $return_code){
			$result[]= $this->exec_reverse_return($return_code);
		}
		echo implode("<br />",$result);
	}

	public function exec_reverse_return($return_code){

		$this->load->model('goodsmodel');
		$this->load->model('ordermodel');
		$this->load->model('refundmodel');
		$this->load->model('returnmodel');

		$data_return 		= $this->returnmodel->get_return($return_code);
		$data_return_item 	= $this->returnmodel->get_return_item($return_code);
		$data_order			= $this->ordermodel->get_order($data_return['order_seq']);

		if($data_return['status'] == 'complete'){
			return "{$return_code} - 반품 완료된 건은 삭제하실 수 없습니다.";
		}

		if($data_return['return_type']=='return' && $data_return['refund_code']){
			$data_refund 		= $this->refundmodel->get_refund($data_return['refund_code']);
			$data_refund_item 	= $this->refundmodel->get_refund_item($data_return['refund_code']);

			if($data_refund['status'] == 'complete'){
				return "{$refund_code} - 환불 완료된 건은 삭제하실 수 없습니다.";
			}
		}

		$this->db->trans_begin();
		$rollback = false;

		foreach($data_return_item as $return_item){
			if($return_item['opt_type']=='opt'){
				$option_seq = $return_item['option_seq'];

				$query = "select * from fm_order_item_option where item_option_seq=?";
				$query = $this->db->query($query,array($option_seq));
				$optionData = $query->row_array();

				if($data_return['return_type']=='return' && $optionData['refund_ea']>=$return_item['ea']){
					$this->db->set('refund_ea','refund_ea-'.$return_item['ea'],false);
					$this->db->where('item_option_seq',$option_seq);
					$this->db->update('fm_order_item_option');
				}
			}else if($return_item['opt_type']=='sub'){
				$option_seq = $return_item['option_seq'];

				$query = "select * from fm_order_item_suboption where item_suboption_seq=?";
				$query = $this->db->query($query,array($option_seq));
				$optionData = $query->row_array();

				if($data_return['return_type']=='return' && $optionData['refund_ea']>=$return_item['ea']){
					$this->db->set('refund_ea','refund_ea-'.$return_item['ea'],false);
					$this->db->where('item_suboption_seq',$option_seq);
					$this->db->update('fm_order_item_suboption');
				}
			}
		}

		$logTitle	= "반품삭제 {$return_code}";
		$logDetail	= "{$return_code} 반품건을 삭제처리했습니다.";
		$this->ordermodel->set_log($data_order['order_seq'],'process',$this->managerInfo['mname'],$logTitle,$logDetail);

		$sql = "delete from fm_order_return where return_code=?";
		$this->db->query($sql, $return_code);

		$sql = "delete from fm_order_return_item where return_code=?";
		$this->db->query($sql, $return_code);

		if($data_return['return_type']=='return' && $data_return['refund_code']){
			$sql = "delete from fm_order_refund where refund_code=?";
			$this->db->query($sql, $data_return['refund_code']);

			$sql = "delete from fm_order_refund_item where refund_code=?";
			$this->db->query($sql, $data_return['refund_code']);
		}

		if ($this->db->trans_status() === FALSE || $rollback == true)
		{
		    $this->db->trans_rollback();
		    echo "반품삭제 처리중 오류가 발생했습니다.";
			exit;
		}
		else
		{
		    $this->db->trans_commit();
		}

		return "{$return_code} - 삭제완료";

	}
}

/* End of file returns_process.php */
/* Location: ./app/controllers/admin/returns_process.php */