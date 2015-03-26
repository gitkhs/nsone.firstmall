<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class sales_process extends admin_base {

	public function __construct() {
		parent::__construct();
		//세금계산서/현금영수증
		$this->load->library('validation');
		$this->load->model('salesmodel');
		$this->load->library('cashtax');
	}

	/* 현금영수증신청 */
	public function cashreceipt_regist(){
		$this->validation->set_rules('order_date', '거래일시', 'trim|required|numeric|xss_clean');
		if	($_POST['cuse'] == '1'){
			$this->validation->set_rules('creceipt_number[1]', '사업자번호 ','trim|required|numeric|max_length[10]|xss_clean');
		}else{
			$this->validation->set_rules('creceipt_number[0]', '주민(휴대폰)번호', 'trim|required|numeric|max_length[13]|xss_clean');
		}
		$this->validation->set_rules('name', '주문자명 ','trim|required|xss_clean');
		//$this->validation->set_rules('email', '이메일 ','trim|required|valid_email|xss_clean');
		//$this->validation->set_rules('phone', '전화번호 ','trim|required|numeric|xss_clean');
		$this->validation->set_rules('goodsname', '상품명 ','trim|required|xss_clean');
		$this->validation->set_rules('amount', '발행액 ','trim|required|numeric|xss_clean');
		$this->validation->set_rules('supply', '공급액 ','trim|required|numeric|xss_clean');
		$this->validation->set_rules('surtax', '부가세 ','trim|required|numeric|xss_clean');
		if($this->validation->exec() === false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.cashreceiptform[\"{$err['key']}\"])
			parent.document.cashreceiptform[\"{$err['key']}\"].focus();";
			$callback .= "else if (parent.document.getElementsByName('{$err['key']}'))
			parent.document.getElementsByName('{$err['key']}').focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$creceipt_number					= str_replace("-", "", $_POST['creceipt_number'][$_POST['cuse']]);
		$cashparams['typereceipt'		]	= 2;
		if	($_POST['mode'] == 'mod' && $_POST['seq']){
			$cashparams['seq']	= $_POST['seq'];
		}else{
			$cashparams['type'				]	= 1;//관리자수동
			$cashparams['order_seq'			]	= date('YmdHis').$result_id;
		}
		$cashparams['vat_type'			]	= $_POST['vat_type'];
		$cashparams['price'				]	= $_POST['amount'];
		$cashparams['supply'			]	= $_POST['supply'];
		$cashparams['surtax'			]	= $_POST['surtax'];
		$cashparams['person'			]	= $_POST['name'];
		$cashparams['email'				]	= $_POST['email'];
		$cashparams['phone'				]	= $_POST['phone'];
		$cashparams['cuse'				]	= $_POST['cuse'];
		$cashparams['goodsname'			]	= $_POST['goodsname'];
		$cashparams['order_date'		]	= date('Y-m-d H:i:s', strtotime($_POST['order_date']));
		$cashparams['creceipt_number'	]	= $creceipt_number;
		$cashparams['regdate'			]	= date('Y-m-d H:i:s');
		

		if	($_POST['mode'] == 'mod' && $_POST['seq']){
			$result	= $this->salesmodel->sales_modify($cashparams);
			if($result){
				$callback = "parent.document.location.reload();";
				openDialogAlert("현금영수증이 수정되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location.reload();";
				openDialogAlert("현금영수증 수정요류",400,140,'parent',$callback);
			}
		}else{
			$result	= $this->salesmodel->sales_write($cashparams);
			if($result){
				$callback = "parent.document.location.reload();";
				openDialogAlert("관리자에 의해 현금영수증이 신청되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location.reload();";
				openDialogAlert("발급오류",400,140,'parent',$callback);
			}
		}
	}

	/* 세금계산서신청 */
	public function tax_regist(){

		$this->validation->set_rules('order_seq', '주문번호 ','trim|required|xss_clean');
		$this->validation->set_rules('co_name', '상호명 ','trim|required|xss_clean');
		$this->validation->set_rules('busi_no', '사업자번호 ','trim|required|max_length[12]|xss_clean');
		$this->validation->set_rules('co_ceo', '대표자명 ','trim|required|xss_clean');
		$this->validation->set_rules('co_status', '업태 ','trim|required|xss_clean');
		$this->validation->set_rules('co_type', '업종','trim|required|xss_clean');
		$this->validation->set_rules('Zipcode[]', '주소','trim|required|xss_clean');
		$this->validation->set_rules('Address', '주소','trim|required|xss_clean');
		//$this->validation->set_rules('Address', '상세주소','trim|required|xss_clean');
		$this->validation->set_rules('person', '담당자이름','trim|required|xss_clean');
		$this->validation->set_rules('email', '담당자이메일','trim|required|valid_email|xss_clean');
		$this->validation->set_rules('phone', '전화번호','trim|required|xss_clean');
		$this->validation->set_rules('amount', '발행액','trim|required|numeric|xss_clean');
		$this->validation->set_rules('supply', '공급액','trim|required|numeric|xss_clean');
		$this->validation->set_rules('surtax', '부가세','trim|required|numeric|xss_clean');
		if($_POST['busi_no']){
			if(!preg_match('/^[0-9]{3}\-[0-9]{2}\-[0-9]{5}$/', $_POST['busi_no'])){
				$callback = "if(parent.document.taxform[\"busi_no\"])
				parent.document.taxform[\"busi_no\"].focus();";
				$callback .= "else if (parent.document.getElementsByName('busi_no'))
				parent.document.getElementsByName('busi_no').focus();";
				openDialogAlert('유효하지 않은 사업자번호입니다.',400,140,'parent',$callback);
				exit;
			}
		}

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.taxform[\"{$err['key']}\"])
			parent.document.taxform[\"{$err['key']}\"].focus();";
			$callback .= "else if (parent.document.getElementsByName('{$err['key']}'))
			parent.document.getElementsByName('{$err['key']}').focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}
		if	($_POST['mode'] == 'mod' && $_POST['seq']){
			$cashparams['seq']	= $_POST['seq'];
		}else{
			$cashparams['typereceipt'	]		= 1;
			$cashparams['type'			]		= 1;//관리자수동
			$cashparams['tstep'			]		= 1;//신청
		}
		$cashparams['vat_type'		]		= $_POST['vat_type'];
		$cashparams['order_seq'		]		= $_POST['order_seq'];
		$cashparams['co_name'		]		= $_POST['co_name'];
		$cashparams['busi_no'		]		= $_POST['busi_no'];
		$cashparams['co_ceo'		]		= $_POST['co_ceo'];
		$cashparams['co_status'		]		= $_POST['co_status'];
		$cashparams['co_type'		]		= $_POST['co_type'];
		$cashparams['zipcode'		]		= implode("-",$_POST['Zipcode']);
		$cashparams['address_type'	]		= $_POST['Address_type'];
		$cashparams['address'		]		= $_POST['Address'];
		$cashparams['address_street']		= $_POST['Address_street'];
		$cashparams['address_detail']		= $_POST['Address_detail'];
		$cashparams['person'		]		= $_POST['person'];
		$cashparams['email'			]		= $_POST['email'];
		$cashparams['phone'			]		= $_POST['phone'];
		$cashparams['price'			]		= $_POST['amount'];
		$cashparams['supply'		]		= $_POST['supply'];
		$cashparams['surtax'		]		= $_POST['surtax'];
		$cashparams['tax_price'		]		= $_POST['amount'];
		$cashparams['tax_supply'	]		= $_POST['supply'];
		$cashparams['tax_surtax'	]		= $_POST['surtax'];
		$cashparams['regdate'		]		= date('Y-m-d H:i:s');
		if	($_POST['mode'] == 'mod' && $_POST['seq']){
			$result	= $this->salesmodel->sales_modify($cashparams);
			if($result){
				$callback = "parent.document.location.reload();";
				openDialogAlert("세금계산서가 수정되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location.reload();";
				openDialogAlert("세금계산서 수정요류",400,140,'parent',$callback);
			}
		}else{
			$result	= $this->salesmodel->sales_write($cashparams);
			if($result){
				$callback = "parent.document.location.reload();";
				openDialogAlert("관리자에 의해 세금계산서가 신청되었습니다.",400,140,'parent',$callback);
			}else{
				$callback = "parent.document.location.reload();";
				openDialogAlert("신청오류",400,140,'parent',$callback);
			}
		}
	}


	/* 현금영수증발급 */
	public function cashreceiptwrite()
	{
		if	($_POST['cuse'] == '1'){
			$this->validation->set_rules('creceipt_number[1]', '사업자번호 ','trim|required|numeric|max_length[10]|xss_clean');
		}else{
			$this->validation->set_rules('creceipt_number[0]', '주민(휴대폰)번호','trim|required|numeric|max_length[13]|xss_clean');
		}
		$this->validation->set_rules('name', '주문자명 ','trim|required|xss_clean');
		$this->validation->set_rules('email', '이메일 ','trim|required|valid_email|xss_clean');
		$this->validation->set_rules('phone', '전화번호 ','trim|required|xss_clean');
		$this->validation->set_rules('goodsname', '상품명 ','trim|required|xss_clean');
		$this->validation->set_rules('amount', '발행액 ','trim|required|numeric|xss_clean');
		$this->validation->set_rules('supply', '공급액 ','trim|required|numeric|xss_clean');
		$this->validation->set_rules('surtax', '부가세 ','trim|required|numeric|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.cashreceiptform[\"{$err['key']}\"])
			parent.document.cashreceiptform[\"{$err['key']}\"].focus();";
			$callback .= "else if (parent.document.getElementsByName('{$err['key']}'))
			parent.document.getElementsByName('{$err['key']}').focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$creceipt_number					= str_replace("-", "", $_POST['creceipt_number'][$_POST['cuse']]);
		$cashparams['typereceipt'		]	= 2;
		$cashparams['type'				]	= 1;//관리자수동
		$cashparams['price'				]	= $_POST['amount'];
		$cashparams['supply'			]	= $_POST['supply'];
		$cashparams['surtax'			]	= $_POST['surtax'];
		$cashparams['person'			]	= $_POST['name'];
		$cashparams['email'				]	= $_POST['email'];
		$cashparams['phone'				]	= $_POST['phone'];
		$cashparams['cuse'				]	= $_POST['cuse'];
		$cashparams['goodsname'			]	= $_POST['goodsname'];
		$cashparams['creceipt_number'	]	= $creceipt_number;
		$cashparams['regdate'			]	= date('Y-m-d H:i:s');
		$cashparams['order_seq'			]	= date('YmdHis').$result_id;

		$result_id	= $this->salesmodel->sales_write($cashparams);
		$result		= typereceipt_setting($cashparams['order_seq'], $result_id);

		if($result){
			$callback = "parent.document.location.reload();";
			openDialogAlert("관리자에 의해 현금영수증이 신청되었습니다.",400,140,'parent',$callback);
		}else{
			$callback = "parent.document.location.reload();";
			openDialogAlert("발급오류",400,140,'parent',$callback);
		}

		/*
		$cashparams['paydt']= $cashparams['regdate'];

		$taxResult = $this->cashtax->getCashTax('pay', $cashparams);

		if (is_array($taxResult) == true)
		{
			$taxResult['seq']						= $result_id;
			$taxResult['tstep']					= 2;//발급완료
			$taxResult['issue_date']			= date('Y-m-d H:i:s');
			$taxResult['order_seq']			= $cashparams['order_seq'];
			$this->salesmodel->sales_modify($taxResult);
			$callback = "parent.document.location.reload();";
			openDialogAlert("현금영수증이 발급 되었습니다.",400,140,'parent',$callback);
		}
		else
		{
			$upResult['seq']					= $result_id;
			$upResult['tstep']				= 4;//발급실패
			$upResult['issue_date']		= date('Y-m-d H:i:s');
			$upResult['order_seq']		= $cashparams['order_seq'];
			$this->salesmodel->sales_modify($upResult);

			$this->cashtax->getCashTax('mod', $cashparams);
			$callback = "parent.document.location.reload();";
			openDialogAlert($taxResult,400,140,'parent',$callback);
		}
		*/
	}

	public function receipt_process(){
		$seq			= $_GET["seq"];
		$order_seq		= $_GET["order_seq"];

		$result = typereceipt_setting($order_seq, $seq);
		if($result){
			echo "true";
			$return = array('result'=>true, 'msg'=>"현금영수증이 발급되었습니다.");
		}else{
			echo "false";
			$return = array('result'=>false, 'msg'=>"PG 나 하이웍스에서 응답이 없습니다.\n일시적인 장애가 있을 수 있으니 잠시 후에 다시 시도 해 주세요.\n\n(계속적으로 발생하면 퍼스트몰 고객센터로 문의하시길 바랍니다.");
		}
		//echo json_encode($return);
		exit;

	}

	//현금영수증 발급취소
	public function cashreceiptcancel()
	{
		$delidar = @explode(",",$_POST['delidar']);
		$delnum = 0;
		for($i=0;$i<sizeof($delidar);$i++){ if(empty($delidar[$i]))continue;
			$upseq = $delidar[$i];

			$sc['whereis']	= ' and  seq="'.$upseq.'" ';
			$sc['select']		= '  *  ';
			$cashparams 		= $this->salesmodel->get_data($sc);
			if($cashparams){
				$upResult = $this->cashtax->getCashTax('mod', $cashparams);
				if (is_array($upResult) == true)
				{
					$upResult['seq']				= $cashparams['seq'];
					$upResult['issue_date']	= date('Y-m-d H:i:s');
					$upResult['tstep']			= 3;//발급취소
					//debug_var($upResult);
					$result = $this->salesmodel->sales_modify($upResult);

					if($result) {
						$delnum++;
					}
				}else{
					$return = array('result'=>true, 'msg'=>$upResult);
					echo json_encode($return);
					exit;
				}
			}
		}
		$return = array('result'=>true, 'msg'=>"[".$delnum."]건의 현금영수증의 발급이 취소되었습니다.");
		echo json_encode($return);
		exit;
	}


	public function taxwrite()
	{

		$cashparams['typereceipt']					= 1;
		$cashparams['type']							= 1;//관리자수동
		$cashparams['price']						= $_POST['amount'];
		$cashparams['supply']						= $_POST['supply'];
		$cashparams['surtax']						= $_POST['surtax'];
		$cashparams['person']						= $_POST['person'];
		$cashparams['email']						= $_POST['email'];
		$cashparams['phone']						= $_POST['phone'];
		$cashparams['busi_no']						= $_POST['busi_no'];
		$cashparams['co_name']						= $_POST['co_name'];
		$cashparams['co_ceo']						= $_POST['co_ceo'];
		$cashparams['co_status']					= $_POST['co_status'];
		$cashparams['co_type']						= $_POST['co_type'];
		$cashparams['zipcode']						= implode("-",$_POST['zipcode']);
		$cashparams['address']						= ($_POST['address_type'])?$_POST['address_type']:'zibun';
		$cashparams['address']						= $_POST['address'];
		$cashparams['address_street']			= $_POST['address_street'];
		$cashparams['address_detail']			= $_POST['address_detail'];
		//$cashparams['order_seq']					= $_POST['order_seq'];
		$cashparams['regdate']						= date('Y-m-d H:i:s');

		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');

		if(!eregi("(^[_0-9a-zA-Z-]+(\.[_0-9a-zA-Z-]+)*@[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*$)", $_POST['email'], $regs)){
			openDialogAlert("이메일 형식이 올바르지 않습니다.",400,140,'parent',"");
			exit;
		}

		if($arrBasic["businessConditions"] == "" || $arrBasic["businessLine"] == ""){
			openDialogAlert("공급자 사업자 정보에 업태/종목이 작성되어 있지 않습니다.",400,140,'parent',"");
			exit;
		}

		$result_id = $this->salesmodel->sales_write($cashparams);

		$cashparams['order_seq']					= date('YmdHis').$result_id;
		$cashparams['paydt']						= $cashparams['regdate'];

		//$taxResult = $this->cashtax->getCashTax('pay', $cashparams);




		$taxResult = $this->salesmodel->hiworks_bill_send($cashparams);


		if ($taxResult['result'])
		{
			$taxResult['seq']						= $result_id;
			$taxResult['tstep']					= 2;//발급완료
			$taxResult['issue_date']			= date('Y-m-d H:i:s');
			$taxResult['order_seq']			= $cashparams['order_seq'];
			$this->salesmodel->sales_modify($taxResult);
			$callback = "parent.document.location.reload();";
			openDialogAlert("관리자에 의해 세금계산서가 신청되었습니다.",400,140,'parent',$callback);
		}
		else
		{
			$upResult['seq']					= $result_id;
			$upResult['tstep']				= 4;//발급실패
			$upResult['issue_date']		= date('Y-m-d H:i:s');
			$upResult['order_seq']		= $cashparams['order_seq'];
			$this->salesmodel->sales_modify($upResult);

			$this->cashtax->getCashTax('mod', $cashparams);
			$callback = "parent.document.location.reload();";
			openDialogAlert($taxResult["message"],400,140,'parent',$callback);
		}
	}



	//세금계산서 발급완료처리
	public function taxupdate()
	{
		$delidar = @explode(",",$_POST['delidar']);
		$delnum = 0;
		for($i=0;$i<sizeof($delidar);$i++){ if(empty($delidar[$i]))continue;

			$upResult['seq']				= $delidar[$i];
			$upResult['tstep']			= 2;//발급완료
			$upResult['pg_kind']		= $this->config_system['pgCompany'];
			$result = $this->salesmodel->sales_modify($upResult);
			if($result) {
				$delnum++;
			}
		}
		$return = array('result'=>true, 'msg'=>"[".$delnum."]건의 세금계산서가 발급완료되었습니다.");
		echo json_encode($return);
		exit;
	}


	public function tax_update()
	{
		$seq			= $_GET["seq"];
		$order_seq		= $_GET["order_seq"];

		$orders = config_load('order');
		$this->template->assign('orders',$orders);
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
		if($arrBasic["businessConditions"] == "" || $arrBasic["businessLine"] == ""){

			$return = array('result'=>false, 'msg'=>"공급자 사업자 정보에 업태/종목이 작성되어 있지 않습니다.");
			echo json_encode($return);
			exit;
		}

		if($orders['biztype']=='tax' && $orders['hiworks_use']=='Y'){
			if($this->config_system['webmail_admin_id'] && $this->config_system['webmail_domain'] && $this->config_system['webmail_key']){
				$sql	= "SELECT * FROM fm_sales WHERE seq = '{$seq}'";
				$query = $this->db->query($sql);
				$data = $query->result_array();

				// 과세 상품 가격 구하기
				if(!$data[0]['email'] && $data[0]['order_seq']){
					$order_data = $this->ordermodel->get_order($data[0]['order_seq']);
					$data[0]['email'] = $order_data['order_email'];
				}

				$result = $this->salesmodel->hiworks_bill_send($data[0]);
				if($result['result']){
					$return = array('result'=>true, 'msg'=>"처리 되었습니다.");
					echo json_encode($return);
					exit;
				}else{
					$return = array('result'=>false, 'msg'=>$result['message']);
					echo json_encode($return);
					exit;
				}
			}else{
				$return = array('result'=>false, 'msg'=>"하이웍스 정보가 올바르지 않습니다.\n설정> 매출증빙에서 하이웍스를 설정해 주세요.");
				echo json_encode($return);
				exit;
			}

		}else{
			$upResult['seq']			= $seq;
			$upResult['tstep']			= 2;//발급완료
			$upResult['pg_kind']		= $this->config_system['pgCompany'];
			$result = $this->salesmodel->sales_modify($upResult);

			$return = array('result'=>true, 'msg'=>"처리되었습니다.");
			echo json_encode($return);
			exit;
		}
	}


	public function tax_check(){
		$seq			= $_POST["seq"];
		/*
		$result = $this->salesmodel->hiworks_bill_check($seq);
		*/

		$sql	= "SELECT * FROM fm_sales WHERE seq = '{$seq}'";
		$query = $this->db->query($sql);
		$data = $query->row_array();

		$taxResult = $this->salesmodel->hiworks_bill_send($data);

		if ($taxResult['result'])
		{
			$taxResult['seq']						= $result_id;
			$taxResult['tstep']					= 2;//발급완료
			$upResult['up_date']				= date('Y-m-d H:i:s');
			$taxResult['issue_date']			= date('Y-m-d H:i:s');
			$taxResult['order_seq']			= $cashparams['order_seq'];
			$this->salesmodel->sales_modify($taxResult);
			$log_msg	= '하이웍스로 전송성공';
			$this->salesmodel->sales_log_wirte($seq, $log_msg);
		}
		else
		{
			$upResult['seq']					= $result_id;
			$upResult['tstep']				= 4;//발급실패
			$upResult['up_date']		= date('Y-m-d H:i:s');
			$upResult['issue_date']		= date('Y-m-d H:i:s');
			$upResult['order_seq']		= $cashparams['order_seq'];
			$this->salesmodel->sales_modify($upResult);

			$this->cashtax->getCashTax('mod', $cashparams);
			$log_msg	= '하이웍스로 전송실패<br>'.$taxResult['message'];
			$this->salesmodel->sales_log_wirte($seq, $log_msg);
		}

		if($taxResult['message'] == "W"){
			$message = "처리되었습니다. 하이웍스에 로그인 하셔서 발급 하시면 세금계산서가 발행됩니다";
		}else{
			$message = "처리중 에러가 발생하였습니다.";
		}

		$return = array('result'=>$result['result'], 'msg'=>$message);
		echo json_encode($return);
		exit;
	}



	//신청서삭제
	public function sales_multi_delete()
	{
		$delidar = @explode(",",$_POST['delidar']);
		$delnum = 0;
		for($i=0;$i<sizeof($delidar);$i++){ if(empty($delidar[$i]))continue;
			$delseq = $delidar[$i];
			$result = $this->salesmodel->sales_delete($delseq);
			if($result) {
				if($_POST['type'] != 1) {//수동발급이 아닌경우
					$this->db->where('order_seq',$_POST['order_seq']);
					$this->db->update('fm_order',array('typereceipt'=>'0'));
				}
				$delnum++;
			}
		}

		$return = array('result'=>true, 'msg'=>"[".$delnum."]건의 신청서가 삭제되었습니다.");
		echo json_encode($return);
		exit;
	}


	### 수기등록
	public function manual_cash(){
		$seq = $_POST['seq'];
		$sql = "UPDATE fm_sales SET tstep = 2 WHERE seq = '{$seq}'";
		$result = $this->db->query($sql);
		echo json_encode($result);
		exit;
	}

	public function sales_unlink(){
		$seq = $_GET['seq'];
		$sql = "UPDATE fm_sales SET tstep = 2, approach = 'unlink', up_date = '".date("Y-m-d H:i:s")."' WHERE seq = '{$seq}'";
		$result = $this->db->query($sql);
		$callback = "parent.document.location.reload();";
		openDialogAlert("처리되었습니다.",400,140,'parent',$callback);
		exit;
	}

	public function sales_cancel(){
		$seq = $_GET['seq'];
		$order_seq = $_GET['order_seq'];
		$sql = "UPDATE fm_sales SET tstep = 3, approach = 'unlink', up_date = '".date("Y-m-d H:i:s")."' WHERE seq = '{$seq}'";
		$result = $this->db->query($sql);

		$sql = "UPDATE fm_order SET typereceipt = ''  WHERE order_seq = '{$order_seq}'";
		$result = $this->db->query($sql);


		$callback = "parent.document.location.reload();";
		openDialogAlert("처리되었습니다.",400,140,'parent',$callback);
		exit;
	}

	public function tax_send_log(){
		$seq	= $_POST['seq'];
		if	($seq){
			$result				= $this->salesmodel->get_sales_log($seq);
			$result['result']	= true;
			echo json_encode($result);
			exit;
		}

		echo json_encode(array('result'=>false));
	}

	public function sales_memo(){
		$seq	= $_GET['seq'];
		$sql = "select admin_memo as memo from fm_sales  WHERE seq = '{$seq}'";
		$query = $this->db->query($sql);
		$result = $query->row_array();

		echo json_encode($result);
		exit;
	}

	public function memo_regist(){
		$seq	= $_POST['sales_seq'];
		$order_seq	= $_POST['order_seq'];

		$sales_memo	= str_replace("'", "''", $_POST['sales_memo']);

		$sql = "UPDATE fm_sales SET admin_memo = '{$sales_memo}'  WHERE seq = '{$seq}'";
		echo $sql;
		$query = $this->db->query($sql);

		$callback = "parent.sales_memo('{$seq}', '{$order_seq}');";
		openDialogAlert("저장되었습니다..",400,140,'parent',$callback);
		exit;
	}

}

/* End of file sales_process.php */
/* Location: ./app/controllers/admin/sales_process.php */