<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class sales_process extends front_base {

	public function __construct() {
		parent::__construct();
		//세금계산서/현금영수증
		$this->load->model('salesmodel');
		$this->load->model('ordermodel');
	}

	/* 현금영수증발급 */
	public function cashreceiptwrite()
	{
		$this->load->library('cashtax');
		$this->load->library('validation');

		$order 	= $this->ordermodel->get_order($_POST['order_seq']);
		$order_tax_prices = $this->ordermodel->get_order_prices_for_tax($_POST['order_seq'],$order);
		if($_POST["cuse"] == "0"){
			$this->validation->set_rules('creceipt_number[0]', '인증번호','trim|numeric|xss_clean');
		}else{
			$this->validation->set_rules('creceipt_number[1]', '사업자번호','trim|numeric|xss_clean');
		}
		if(isset($_POST["email"])){
			$this->validation->set_rules('email', '이메일','trim|required|valid_email|xss_clean');
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$data_tax = $this->salesmodel->tax_calulate(
			$order_tax_prices["tax"],
			$order_tax_prices["exempt"],
			$order_tax_prices["shipping_cost"],
			$order_tax_prices["sale"],
			$order_tax_prices["tax_sale"]);
		$data_etc = $this->salesmodel->tax_calulate(
			$order_tax_prices["tax"],
			$order_tax_prices["exempt"],
			$order_tax_prices["shipping_cost"],
			$order_tax_prices["sale"],
			$order_tax_prices["etc_sale"]);

		if($_POST['creceipt_seq']){//수정

			$this->db->where('order_seq',$_POST['order_seq']);
			$this->db->update('fm_order',array('typereceipt'=>'2'));

			$cashparams['seq'	]				= $_POST['creceipt_seq'];
			$cashparams['order_seq']			= $_POST['order_seq'];
			$cashparams['tstep']				= "1";
			$cashparams['person']				= $_POST['order_user_name'];
			$cashparams['cuse']					= $_POST['cuse'];
			$cashparams['email']				= $_POST['email'];
			$cashparams['phone']				= $_POST['phone'];
			$cashparams['goodsname'			]	= $order_tax_prices["goods_name"];
			$creceipt_number					= str_replace("-", "", $_POST['creceipt_number'][$_POST['cuse']]);
			$cashparams['creceipt_number'	]	= $creceipt_number;
			$cashparams['regdate']				= date('Y-m-d H:i:s');
			
			$this->salesmodel->sales_modify($cashparams);

			$result_id							= $_POST['creceipt_seq'];
			
		}else{
			$this->db->where('order_seq',$_POST['order_seq']);
			$this->db->update('fm_order',array('typereceipt'=>'2'));

			$cashparams['typereceipt']	= 2;
			$cashparams['type']			= 0;//사용자 수동
			$cashparams['order_seq']	= $_POST['order_seq'];
			$cashparams['member_seq']	= $this->userInfo['member_seq'];
			$cashparams['person']		= $_POST['order_user_name'];
			$cashparams['cuse']			= $_POST['cuse'];
			$cashparams['goodsname'	]	= $order_tax_prices["goods_name"];
			$creceipt_number			= str_replace("-", "", $_POST['creceipt_number'][$_POST['cuse']]);
			$cashparams['creceipt_number']	= $creceipt_number;
			$cashparams['regdate']			= date('Y-m-d H:i:s');
			$cashparams['email']				= $_POST['email'];

			$cashparams['price']		= (int) $data_etc['supply'] + (int) $data_etc['supply_free'] + (int) $data_etc['surtax'];
			$cashparams['supply']		= (int) $data_etc['supply'] + (int) $data_etc['supply_free'];
			$cashparams['surtax']		= (int) $data_etc['surtax'];

			$cashparams['tax_price']	= (int) $data_tax['supply'] + (int) $data_tax['supply_free'] + (int) $data_tax['surtax'];
			$cashparams['tax_supply']	= (int) $data_tax['supply'] + (int) $data_tax['supply_free'];
			$cashparams['tax_surtax']	= (int) $data_tax['surtax'];

			$result_id = $this->salesmodel->sales_write($cashparams);
		}

		$pg						= config_load($this->config_system['pgCompany']);
		$cashparams['mallId']	= $pg['mallId'];
		$cashparams['payment']	= $order['payment'];
		$cashparams['pg_transaction_number'] = $order['pg_transaction_number'];

		if( 25 <= $order['step'] && $order['step'] <= 75  ){
			$result = typereceipt_setting($_POST['order_seq']);
			$callback = "parent.document.location.reload();";
			openDialogAlert("현금영수증이 발급 되었습니다.",400,140,'parent',$callback);
		}else{
			$taxResult['tstep'] = 1;//발급신청접수
			$taxResult['order_seq'] = $cashparams['order_seq'];
			$this->salesmodel->sales_modify($taxResult);

			$callback = "parent.document.location.reload();";
			openDialogAlert("현금영수증이 신청/수정 되었습니다.",400,140,'parent',$callback);
		}


	}

	public function taxwrite()
	{
		$this->load->model('salesmodel');
		$this->load->library('validation');

		$sc['whereis']	= ' and  order_seq="'.$_POST['order_seq'].'" ';
		$sc['select']		= '  *  ';
		$cashparams 		= $this->salesmodel->get_data($sc);

		if($_POST['person'] == ""){
			$taxparams['person']			= $_POST['order_user_name'];
		}else{
			$taxparams['person']			= $_POST['person'];
		}

		if($_POST['email'] != ""){
			$taxparams['email']			= $_POST['email'];
		}

		if($_POST['phone'] != ""){
			$taxparams['phone']			= str_replace("-","",$_POST['phone']);
		}

		$_POST['busi_no'] = str_replace("-", "", $_POST['busi_no']);


		$this->validation->set_rules('co_name', '상호명','trim|required|xss_clean');
		$this->validation->set_rules('busi_no', '사업자번호','trim|required|numeric|xss_clean');
		$this->validation->set_rules('co_ceo', '대표자명','trim|required|xss_clean');
		$this->validation->set_rules('co_status', '업태','trim|required|xss_clean');
		$this->validation->set_rules('co_type', '업종','trim|required|xss_clean');
		$this->validation->set_rules('co_zipcode[]', '우편번호','trim|numeric|xss_clean');
		$this->validation->set_rules('address', '주소','trim|required|xss_clean');
		$this->validation->set_rules('address_detail', '상세주소','trim|required|xss_clean');
		
		$this->validation->set_rules('person', '담당자명','trim|required|xss_clean');
		$this->validation->set_rules('email', '이메일','trim|required|valid_email|xss_clean');
		$this->validation->set_rules('phone', '연락처','trim|required|numeric|xss_clean');
		

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if(strlen($_POST['busi_no']) != 10){
			$callback = "if(parent.document.getElementsByName('busi_no')[0]) parent.document.getElementsByName('busi_no')[0].focus();";
			openDialogAlert("잘못된 사업자번호입니다.",400,140,'parent',$callback);
			exit;
		}


		if($_POST['tax_seq']){//수정

			$this->db->where('order_seq',$_POST['order_seq']);
			$this->db->update('fm_order',array('typereceipt'=>'1'));

			$taxparams['seq'	]			= $_POST['tax_seq'];
			$taxparams['order_seq']			= $_POST['order_seq'];
			$taxparams['co_name']			= $_POST['co_name'];
			$taxparams['co_ceo']			= $_POST['co_ceo'];
			$taxparams['co_status']			= $_POST['co_status'];
			$taxparams['co_type']			= $_POST['co_type'];
			$taxparams['busi_no']			= $_POST['busi_no'];
			$taxparams['person']			= $_POST['person'];
			$taxparams['tstep']				= "1";

			$taxparams['zipcode']			= @implode('-',$_POST['zipcode']);
			$taxparams['address_type']		= ($_POST['address_type'])?$_POST['address_type']:"zibun";
			$taxparams['address']					= $_POST['address'];
			$taxparams['address_street']		= $_POST['address_street'];
			$taxparams['address_detail']		= $_POST['address_detail'];
			$taxparams['up_date']			= date('Y-m-d H:i:s');
			$this->salesmodel->sales_modify($taxparams);

		}else{
			$sc['whereis']	= ' and  order_seq="'.$_POST['order_seq'].'" ';
			$sc['select']		= '  *  ';
			$cashparams 		= $this->salesmodel->get_data($sc);

			if($cashparams['seq']){
				$this->db->where('order_seq',$_POST['order_seq']);
				$this->db->update('fm_order',array('typereceipt'=>'1'));

				$taxparams['seq'	]		= $cashparams['seq'];
				$taxparams['order_seq']		= $_POST['order_seq'];
				$taxparams['co_name']		= $_POST['co_name'];
				$taxparams['co_ceo']		= $_POST['co_ceo'];
				$taxparams['co_status']		= $_POST['co_status'];
				$taxparams['co_type']		= $_POST['co_type'];
				$taxparams['busi_no']		= $_POST['busi_no'];
				$taxparams['person']		= $_POST['order_user_name'];
				$taxparams['zipcode']		= @implode('-',$_POST['zipcode']);
				$taxparams['address_type']	= ($_POST['address_type'])?$_POST['address_type']:"zibun";
				$taxparams['address']				= $_POST['address'];
				$taxparams['address_street']	= $_POST['address_street'];
				$taxparams['address_detail']	= $_POST['address_detail'];
				$taxparams['up_date']		= date('Y-m-d H:i:s');
				$taxparams['person']			= $_POST['person'];
				$this->salesmodel->sales_modify($taxparams);

			}else{

				$this->db->where('order_seq',$_POST['order_seq']);
				$this->db->update('fm_order',array('typereceipt'=>'1'));

				$order_tax_prices = $this->ordermodel->get_order_prices_for_tax($_POST['order_seq']);

				$data_tax = $this->salesmodel->tax_calulate(
				$order_tax_prices["tax"],
				$order_tax_prices["exempt"],
				$order_tax_prices["shipping_cost"],
				$order_tax_prices["sale"],
				$order_tax_prices["tax_sale"]);
				$data_etc = $this->salesmodel->tax_calulate(
				$order_tax_prices["tax"],
				$order_tax_prices["exempt"],
				$order_tax_prices["shipping_cost"],
				$order_tax_prices["sale"],
				$order_tax_prices["etc_sale"]);

				$taxparams['typereceipt'	]	= 1;
				$taxparams['type']				= 2;//사용자 수동
				$taxparams['order_seq']		= $_POST['order_seq'];
				$taxparams['member_seq']	= $this->userInfo['member_seq'];
				$taxparams['price'	]		= $_POST['settleprice'];
				$taxparams['co_name']		= $_POST['co_name'];
				$taxparams['co_ceo']			= $_POST['co_ceo'];
				$taxparams['co_status']		= $_POST['co_status'];
				$taxparams['co_type']			= $_POST['co_type'];
				$taxparams['busi_no']			= $_POST['busi_no'];
				$taxparams['person']			= $_POST['order_user_name'];
				$taxparams['zipcode']			= @implode('-',$_POST['zipcode']);
				$taxparams['address_type']		= ($_POST['address_type'])?$_POST['address_type']:"zibun";
				$taxparams['address']					= $_POST['address'];
				$taxparams['address_street']		= $_POST['address_street'];
				$taxparams['address_detail']		= $_POST['address_detail'];
				$taxparams['order_date']	= date('Y-m-d H:i:s');
				$taxparams['person']			= $_POST['person'];

				// 과세 매출증빙 저장
				$taxparams['price']			= (int) $data_etc['supply'] + (int) $data_etc['surtax'];
				$taxparams['supply']		= (int) $data_etc['supply'];
				$taxparams['surtax']		= (int) $data_etc['surtax'];
				$taxparams['tax_price']		= (int) $data_tax['supply'] + (int) $data_tax['surtax'];
				$taxparams['tax_supply']	= (int) $data_tax['supply'];
				$taxparams['tax_surtax']	= (int) $data_tax['surtax'];
				if( $data_etc['surtax'] > 0 ){
					$this->salesmodel->sales_write($taxparams);
				}

				// 비과세 매출증빙 저장
				$taxparams['price']			= (int) $data_etc['supply_free'];
				$taxparams['supply']		= (int) $data_etc['supply_free'];
				$taxparams['surtax']		= 0;
				$taxparams['tax_price']		= (int) $data_tax['supply_free'];
				$taxparams['tax_supply']	= (int) $data_tax['supply_free'];
				$taxparams['tax_surtax']	= 0;
				if( $data_etc['supply_free'] > 0 ){
					$this->salesmodel->sales_write($taxparams);
				}
			}
		}
		openDialogAlert("세금계산서를 저장하였습니다.",400,140,'parent',"parent.taxlayerclose();parent.location.reload();");
		exit;
	}

	public function taxdelete()
	{
		if(empty($_POST['tax_seq'])) {
			$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
			echo json_encode($return);
			exit;
		}

		$this->load->model('salesmodel');
		$result = $this->salesmodel->sales_delete($_POST['tax_seq']);

		if($result) {
			$this->db->where('order_seq',$_POST['order_seq']);
			$this->db->update('fm_order',array('typereceipt'=>'0'));

			$return = array('result'=>true, 'msg'=>"삭제되었습니다.");
			echo json_encode($return);
			exit;
		}else{
			$return = array('result'=>false, 'msg'=>"세금계산서삭제가 실패 되었습니다.");
			echo json_encode($return);
			exit;
		}
	}
}

/* End of file sales_process.php */
/* Location: ./app/controllers/admin/sales_process.php */