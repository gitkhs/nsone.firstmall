<?php
/**
 * 매출증빙 서류 : 현금영수증/매출증빙 내역
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class Salesmodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_sales			= 'fm_sales';
		$this->table_order			= 'fm_order';
		$this->table_member		= 'fm_member';
		$this->table_member_group		= 'fm_member_group';
	}

	/*
	 * 매출증빙관리
	 * @param
	*/
	public function sales_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS sales.*, ord.payment,
		(
			SELECT userid FROM ".$this->table_member." WHERE member_seq=sales.member_seq
		) userid,
		(
			SELECT group_name FROM ".$this->table_member." m, ".$this->table_member_group." g WHERE m.group_seq=g.group_seq and m.member_seq=sales.member_seq
		) group_name,
		ifnull(sales.order_date,ord.regist_date) as order_date
		from ".$this->table_sales." sales
		LEFT JOIN ".$this->table_order." ord ON ( (sales.order_seq = ord.order_seq)  )
		where 1 /*and ( if(sales.typereceipt<>0,15,ord.step) > 0 )*/";//결제이후상태만노출

		//if($sc['isAll'] != 'y'){
			$sql	.= " and (ord.step not in ('0','95','99') or sales.type=1) ";
		//}

		if(!empty($sc['keyword']))
		{
			$sql.= " and (sales.order_seq like '%".$sc['keyword']."%' or sales.person like '%".$sc['keyword']."%' or sales.order_name like '%".$sc['keyword']."%') ";
		}


		if(!empty($sc['sdate']) && !empty($sc['edate']))
		{
			$sql.= " and ( ord.regist_date >= '{$sc['sdate']} 00:00:00' AND ord.regist_date <= '{$sc['edate']} 24:00:00' ) ";//주문일
		}


		if( !empty($sc['admin_type']) ) {//수동/자동
			foreach($sc['admin_type'] as $type){
				if($type == "1"){
					$str_type = "2,0";
				}else if($type == "2"){
					if($str_type == ""){
						$str_type = "1";
					}else{
						$str_type = "1,2,0";
					}
				}
			}
			$sql.= " and sales.type in (".$str_type.") ";
		}


		if( !empty($sc['ostep']) ) {//수동/자동
			$sql.= " and (";
			foreach($sc['ostep'] as $type){
				if($type == "1"){
					$str_sql.= " ord.step > '15'";
				}else if($type == "2"){
					if($str_sql == ""){
						$str_sql = " ord.step <= '15'";
					}else{
						$str_sql = " ord.step <= '15' or ord.step > '15'";
					}
				}
			}
			$sql.= " ".$str_sql.") ";
		}



		if( !empty($sc['tstep']) ) {//수동/자동
			$sql.= " and (";
			foreach($sc['tstep'] as $tstep){
				if($tstep == "1"){
					//$tstep_sql = " sales.tstep = '1' and type <> '0'";
					$tstep_sql = " sales.tstep = '1' and sales.typereceipt <> '0' ";
				}else if($tstep == "2"){
					if($tstep_sql == ""){
						$tstep_sql = " (sales.tstep = '2' and (sales.approach <> 'unlink' or approach IS NULL)) ";
					}else{
						$tstep_sql = $tstep_sql." or (sales.tstep = '2' and (sales.approach <> 'unlink' or approach IS NULL)) ";
					}
				}else if($tstep == "5"){
					if($tstep_sql == ""){
						$tstep_sql = " (sales.tstep = '2' and sales.approach = 'unlink') ";
					}else{
						$tstep_sql = $tstep_sql." or (sales.tstep = '2' and sales.approach = 'unlink') ";
					}
				}else if($tstep == "3"){
					if($tstep_sql == ""){
						$tstep_sql = " sales.tstep = '3' ";
					}else{
						$tstep_sql = $tstep_sql." or sales.tstep = '3' ";
					}
				}
			}
			$sql.= " ".$tstep_sql.") ";
		}
		//echo $sql;
		if(count($sc['orefund']) == 1){
			if($sc['orefund'][0] == "1"){
				$sql .= " and EXISTS (select refund_code from fm_order_refund where order_seq = ord.order_seq) ";
			}else if($sc['orefund'][0] == "2"){
				$sql .= " and NOT EXISTS (select refund_code from fm_order_refund where order_seq = ord.order_seq) ";
			}
		}



		###
		if( !empty($sc['typereceipt'])) {
			$typereceipt = "'".join("','", $sc['typereceipt'])."'";
			$sql.= " and sales.typereceipt in ({$typereceipt}) ";
		}

		// 정렬
		if($sc['orderby'] ) {
			$sql.=" order by sales.{$sc['orderby']} {$sc['sort']} ";
		} else {
			$sql.=" order by sales.seq desc ";
		}

		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		return $data;
	}


	/*
	 * 매출증빙관리
	 * @param
	 */
	public function sales_tax_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS ord.*, sales.tstep, sales.seq as tax_seq
		from ".$this->table_sales." sales
		RIGHT JOIN ".$this->table_order." ord ON sales.order_seq = ord.order_seq
		LEFT JOIN ".$this->table_member." mb ON ord.member_seq = mb.member_seq
		where ord.member_seq='".$sc['member_seq']."' and ord.typereceipt != 2 and ord.payment not in ('card','cellphone') and ord.step not in ('0','85','95','99') ";
		if(!empty($sc['keyword']))
		{
			$sql.= " and (sales.order_seq like '%".$sc['keyword']."%' or sales.person like '%".$sc['keyword']."%') ";
		}

		// 정렬
		$sql.=" order by ord.order_seq desc ";

		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//검색총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		return $data;
	}


	// 매출증빙총건수
	public function get_item_total_count($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS sales.seq
		from ".$this->table_sales." sales
		LEFT JOIN ".$this->table_order." ord ON ( (sales.order_seq = ord.order_seq)  )
		where 1 and ( if(sales.typereceipt<>0,15,ord.step) > 0 ) and ord.step > 0";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 매출증빙정보
	 * @param
	*/
	public function get_data($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_sales."  where 1 ". $sc['whereis'];
		$sql .=" order by seq ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 매출증빙정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_sales."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 매출증빙생성
	 * @param
	*/
	public function sales_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_sales));
		$result = $this->db->insert($this->table_sales, $data);

		return $this->db->insert_id();
	}


	/*
	 * 매출증빙 개별수정
	 * @param
	*/
	public function sales_modify($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_sales));
		$result = $this->db->update($this->table_sales, $data,array('seq'=>$params['seq']));
		return $result;
	}


	/*
	 * 매출증빙 개별 삭제
	 * @param
	*/
	public function sales_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_sales, array('seq' => $seq));
		return $result;
	}


	/*
	 * 매출증빙 회원삭제
	 * @param
	*/
	public function sales_delete_ord($ordno_seq) {
		if(empty($ordno_seq))return false;
		$result = $this->db->delete($this->table_sales, array('ordno_seq' => $ordno_seq));
		return $result;
	}



	public function hiworks_bill_send($data){
		require_once ROOTPATH."/app/libraries/cfg.php";
		$domain			= $this->config_system['webmail_domain'];
		$license_no		= $this->config_system['webmail_key'];
		$license_id		= $this->config_system['webmail_admin_id'];
		$partner_id		= 'A0001';


		if($license_id != ""){
			$HB = new Hiworks_Bill($domain, $license_id, $license_no, $partner_id);
			if($data['tax_surtax'] == 0){
				$HB->set_type( "B" , "B", "S" );
				$doc_type = "bill";
			}else{
				$HB->set_type( HB_DOCUMENTTYPE_TAX , HB_TAXTYPE_TAX, HB_SENDTYPE_SEND );
				$doc_type = "taxbill";
			}

			//  기본정보 입력
			$tax_info['person'] = $data['person'] ? $data['person'] : $data['co_ceo'];
			$HB->set_basic_info( $tax_info['person'], $data['email'], $data['phone'], '', '', '');

			 // 세액
			$taxval = $data['tax_surtax'];

			// 세금계산서 발행 금액 검증
			/*
			if($doc_type == 'taxbill'){
				$this->load->model('ordermodel');
				$order_tax_option = $this->ordermodel->get_item_option($data['order_seq']);
				$order_tax_suboption = $this->ordermodel->get_item_suboption($data['order_seq']);
				$sum_sale_price = 0;
				$sum_sale_price_tax = 0;
				foreach($order_tax_option as $data_option){
					$sale_price = $data_option['sale_price'] * $data_option['ea'];
					$sum_sale_price += $sale_price;
					if($data_option['tax'] == 'tax'){
						$sum_sale_price_tax += $sale_price;
					}
				}
				if($order_tax_suboption) foreach($order_tax_suboption as $data_option){
					$sale_price = $data_option['sale_price'] * $data_option['ea'];
					$sum_sale_price += $sale_price;
					if($data_option['tax'] == 'tax'){
						$sum_sale_price_tax += $sale_price;
					}
				}

				if( $sum_sale_price_tax ){
					$sum_sale_price_surtax = $sum_sale_price_tax - round($sum_sale_price_tax / 1.1);
					if($taxval < $sum_sale_price_surtax && $sum_sale_price){
						$result = array('result' => false, 'message' => '세금계산서 발행이 불가 - 부가세 금액오류');
						return $result;
					}
				}
			}
			*/

			// 공급가
			$supplyprice = (int)$data['tax_price'] - $taxval;

			$set_paydt = substr($data['order_date'],0,10);
			$set_paydt = $set_paydt ? $set_paydt : date('Y-m-d');

			$supplyprice_total = (int)$supplyprice;
			$taxval_total = $taxval;

			//echo $supplyprice_total." : ".$taxval_total."<br>";
			$HB->set_document_info($set_paydt, $supplyprice_total, $taxval_total, HB_PTYPE_RECEIPT, '', '', '', '', '');

			$basic = config_load('basic');
			$HB->set_company_info( $basic['businessLicense'], $basic['companyName'], $basic['ceo'], $basic['companyAddress'], $basic['businessConditions'], $basic['businessLine'], HB_COMPANYPREFIX_SUPPLIER );

			$HB->set_company_info( $data['busi_no'], $data['co_name'], $data['co_ceo'], $data['address'], $data['co_status'], $data['co_type'], HB_COMPANYPREFIX_CONSUMER );

			###
			$tax_info[paydt] = explode("-",substr($data['order_date'],0,10));
			$tax_info[paydt][1] = $tax_info[paydt][1] ? $tax_info[paydt][1] : date('m');
			$tax_info[paydt][2] = $tax_info[paydt][2] ? $tax_info[paydt][2] : date('d');

			if($data["goodsname"] == ""){
				$goods_name = "물품구매대금";
			}else{
				$goods_name = $data["goodsname"];
			}

			$tax_info[ea] = 1;
			$tax_info[r_price]	= $supplyprice_total;
			$price				= $supplyprice_total;
			$tax_row			= $taxval_total;
			$sum				= $data['tax_price'];

			$HB->set_work_info( $tax_info[paydt][1], $tax_info[paydt][2], $goods_name, 'EA', $tax_info[ea], $tax_info[r_price], $price, $tax_row, '', $sum );

			$rs = $HB->send_document( HB_SOAPSERVER_URL );
			if (!$rs) {
				$msg = iconv("EUC-KR", "UTF-8", $HB->showError());
				$result = array('result' => false, 'message' => $msg);
			}else{
				$hiworks_no = $HB->get_document_id();
				$HB->set_document_id($hiworks_no);
				$status = $HB->check_document( HB_SOAPSERVER_URL );
				$add_qry = "";
				if($status[0][now_state] == 'S') {
					$add_qry = '2';
				}
				$state = explode("|",$status[0][now_state]);
				$sql = "UPDATE fm_sales SET hiworks_no = '{$hiworks_no}', tstep = '5', hiworks_status = '{$state[0]}', issue_date = now() WHERE seq = '{$data[seq]}'";
				$this->db->query($sql);
				$result = array('result' => true, 'message' => $state[0]);
			}
		}else{
			$result = array('result' => true, 'message' => "신청완료");
		}
		return $result;
		unset($HB, $rs, $status);
	}


	public function hiworks_bill_check($seq){
		$sql	= "SELECT * FROM fm_sales WHERE seq = '{$seq}'";
		$query	= $this->db->query($sql);
		$data	= $query->result_array();

		if($data[0]){
			require_once ROOTPATH."/app/libraries/cfg.php";
			$domain			= $this->config_system['webmail_domain'];
			$license_no		= $this->config_system['webmail_key'];
			$license_id		= $this->config_system['webmail_admin_id'];
			$partner_id		= 'A0001';
			$HB = new Hiworks_Bill($domain, $license_id, $license_no, $partner_id);
			$HB->set_document_id($data[0]['hiworks_no']);

			$documet_result_array = $HB->check_document( HB_SOAPSERVER_URL );
			if (!$documet_result_array) {
				$msg = iconv("EUC-KR", "UTF-8", $HB->showError());
				$result = array('result' => false, 'message' => $msg);
			}else{
				$status = explode("|",$documet_result_array[0]['now_state']);
				//$HB->view('Result :', $documet_result_array);
				if($data[0]['hiworks_status']==$status[0]){
					$result = array('result' => true, 'message' => $this->hiworks_status_msg($data[0]['hiworks_status']));
				}else{
					$add_qry = "";
					if($status[0]=="S"){
						$add_qry = ", tstep = 2 ";
					}
					$sql = "UPDATE fm_sales SET hiworks_status = '{$status[0]}', issue_date = now() {$add_qry} WHERE seq = '{$data[0]['seq']}'";
					$this->db->query($sql);
					$result = array('result' => true, 'message' => $this->hiworks_status_msg($status[0]));
				}
			}
			return $result;
			unset($HB, $rs);
		}
	}


	public function hiworks_status_msg($hiworks_status){
		switch($hiworks_status){
			case "W"; $datarow['tax_msg'] = "승인요청전"; break;
			case "T"; $datarow['tax_msg'] = "승인요청"; break;
			case "R"; $datarow['tax_msg'] = "승인요청"; break;
			case "S"; $datarow['tax_msg'] = "발행"; break;
			case "B"; $datarow['tax_msg'] = "반려"; break;
			case "C"; $datarow['tax_msg'] = "승인취소요청"; break;
			case "A"; $datarow['tax_msg'] = "승인취소완료"; break;
			case "E"; $datarow['tax_msg'] = "에러"; break;
			case "1"; $datarow['tax_msg'] = "전송중"; break;
			case "2"; $datarow['tax_msg'] = "전송중"; break;
			case "3"; $datarow['tax_msg'] = "전송중"; break;
			case "4"; $datarow['tax_msg'] = "전송완료"; break;
			case "5"; $datarow['tax_msg'] = "전송실패"; break;
			default: $datarow['tax_msg'] = "전송"; break;
		}
		return $datarow['tax_msg'];
	}

	public function sales_log_wirte($seq, $log_msg){
		if	($seq && $log_msg){
			$log_msg	= addslashes($log_msg);
			$sql		= "insert into fm_sales_log (seq, reg_date, log_msg)"
						. "values(".$seq.", '".date('Y-m-d H:i:s')."','".$log_msg."')";
			$this->db->query($sql);
		}
	}

	public function get_sales_log($seq){
		$sql			= "SELECT * FROM fm_sales_log WHERE seq = '{$seq}'";
		$query			= $this->db->query($sql);
		$data			= $query->result_array();
		$data['count']	= count($data);
		return $data;
	}

	public function tax_calulate($tax,$exempt,$shipping_cost,$sale,$tax_sale)
	{
		$sale += $tax_sale;

		if($tax > 0){
			$tax = $tax + $shipping_cost;
		}else{
			$exempt = $exempt + $shipping_cost;
		}

		if($tax){ // 과세 상품가합
			if( $tax < $sale){
				$sale = $sale - $tax;
				$supply = 0;
			}else{
				$supply = $tax- $sale;
				$sale = 0;;
			}

			// 부가세 계산
			if($supply){
				$surtax = $supply - round($supply / 1.1);
				$supply = $supply - $surtax;
			}
		}

		if($exempt){ // 비과세 상품가합
			$supply_free = $exempt - $sale;
		}

		$result = array('supply'=>$supply,'surtax'=>$surtax,'supply_free'=>$supply_free);
		return $result;
	}
}
?>