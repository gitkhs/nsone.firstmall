<?php
class navermileagemodel extends CI_Model {
	public function __construct()
	{
		$this->cfg_naver_mileage = config_load('naver_mileage');
		if($this->cfg_naver_mileage['naver_mileage_yn'] == 't'){
			$manager_session = $this->session->userdata('manager');
			$marketing_session = $this->session->userdata('marketing');						
			if($manager_session['manager_seq']=='' && $marketing_session!='nbp'){
				$this->cfg_naver_mileage['naver_mileage_yn'] = 'n';
			}
		}
	}

	public function naver_mileage_decoding($str){
		$arr = explode('|',$str);
		foreach($arr as $val){
			$arr2 = explode('=',$val);
			$result[$arr2[0]] = $arr2[1];
		}
		return $result;
	}

	public function naver_mileage_display()
	{
		if( $this->cfg_naver_mileage['naver_mileage_yn']=='y' ){
			return $this->cfg_naver_mileage;
		}

		$marketing_user = $this->session->userdata('marketing');
		if(($this->cfg_naver_mileage['naver_mileage_yn']=='t' && $this->managerInfo)
			||($this->cfg_naver_mileage['naver_mileage_yn']=='t' && $marketing_user=='nbp')){
			return $this->cfg_naver_mileage;
		}

		return false;
	}
	
	public function request_delete($reqTxId)
	{
		$query = "delete from `fm_naver_mileage` where `reqTxId`=?";
		$this->db->query($query,array($reqTxId));
	}

	public function day_request_delete()
	{
		// 하루 전 미처리 데이터 삭제
		$yesterday = date('Y-m-d H:i:s',time()-24*3600);
		$query = "delete from `fm_naver_mileage` where `status`='NONE' and regist_date < ?";
		$this->db->query($query,array($yesterday));
	}

	public function get_request($order_seq,$mode){
		$query = "select * from fm_naver_mileage where order_seq=? and status=?";
		$query = $this->db->query($query,array($order_seq,$mode));
		$data = $query->row_array();
		return $data;
	}

	// 배치잡 등록
	public function batch_regist($mode,$order_seq)
	{
		$query = "select count(*) cnt from fm_naver_mileage where order_seq=? and `status` = 'OK'";
		$query = $this->db->query($query,array($order_seq));
		$data_mileage = $query->row_array();
		if($data_mileage['cnt'] == 1){
			$data = array(
				'work_type' => $mode,
				'order_seq' => $order_seq,
				'status' => 'none'
			);
			$this->db->insert('fm_naver_mileage_batch',$data);			
		}
	}

	// 배치잡 상태변경
	public function batch_status($batch_seq,$status)
	{
		$data = array('status' => $status);
		$this->db->where('batch_seq', $batch_seq);
		$this->db->update('fm_naver_mileage_batch', $data);
	}

	// 배치잡 가져오기
	public function get_batch()
	{
		$query = "select * from fm_naver_mileage_batch where status='none'";
		$query = $this->db->query($query);
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	// 배치잡 카운트
	public function get_count()
	{
		$query = "select count(*) cnt from fm_naver_mileage_batch where status='none'";
		$query = $this->db->query($query);
		$data = $query->row_array();
		return $data_cnt;
	}

	// 배치잡 삭제
	public function status_batch($status,$batch_seq)
	{
		$query = "update fm_naver_mileage_batch set `status`=? where batch_seq=?";
		$this->db->query($query,array($status,$batch_seq));
	}

	// 상품상세 페이지 적립율 표기
	public function display_view()
	{		
		if( in_array($this->cfg_naver_mileage['naver_mileage_yn'],array('y','t')) ){
			$tag = '<script type="text/javascript" src="/app/javascript/js/naver-mileage.js"></script>';
			$tag .= '
			<script type="text/javascript">
			$(function() {
				setNaverCashAccumRate(true);
			});
			</script>';
			echo $tag;
		}
	}

	public function create_cooke(){
		if($_GET['Ncisy'] && preg_match('/naver.com/',$_SERVER['HTTP_REFERER'])){
			$cookie = array(
				   'name'   => 'Ncisy',
				   'value'  => $_GET['Ncisy'],
				   'expire' => '0',
				   'path'   => '/'
			);
			set_cookie($cookie);
		}
	}

	// 주문결제금액 체크
	public function check_mileage($settle_price)
	{
		
		/* 네이버 마일리지 사용금액 */
		if( $_POST['naver_mileage_txId'] ){
			$query = "select * from fm_naver_mileage where reqTxId=?";
			$query = $this->db->query($query,array($_POST['naver_mileage_txId']));
			$data_naver_mileage = $query->row_array();
			
			$naver_mileage_use_msg = "<strong style='color:green'>".($data_naver_mileage['baseAccumRate'] + $data_naver_mileage['addAccumRate'])."%</strong>";
			$tot_naver_mileage = (int) $data_naver_mileage['mileageUseAmount'] + (int) $data_naver_mileage['cashUseAmount'];
			
			if($tot_naver_mileage <= $settle_price){
				$settle_price = (int) $settle_price - (int) $tot_naver_mileage;
				
				if($data_naver_mileage['mileageUseAmount'] || $data_naver_mileage['cashUseAmount']){
					$naver_mileage_use_msg .= "<span style='color:green'> 적립 / </span>";
					if($data_naver_mileage['mileageUseAmount']){
						$naver_mileage_use_msg .= "마일리지 ".number_format($data_naver_mileage['mileageUseAmount'])."원";						
					}
					if($data_naver_mileage['cashUseAmount']){
						$naver_mileage_use_msg .= " + ";
						$naver_mileage_use_msg .= "캐쉬 ".number_format($data_naver_mileage['cashUseAmount'])."원";						
					}

					$naver_mileage_use_msg .= " 사용";
				}else{
					$naver_mileage_use_msg .= "적립";
				}
				echo '<script type="text/javascript">
				$("#naver_mileage_info",parent.document).addClass("hide");
				$("#naver_mileage",parent.document).removeClass("hide");
				$("#naver_mileage_use_msg",parent.document).html("'.$naver_mileage_use_msg.'");
				$("#naver_mileage_txId",parent.document).val("'.$_POST['naver_mileage_txId'].'");
				</script>';
			}else{
				echo '<script>
				$("#naver_mileage_info",parent.document).removeClass("hide");
				$("#naver_mileage",parent.document).addClass("hide");
				$("#naver_mileage_use_msg",parent.document).html("");
				$("#naver_mileage_txId",parent.document).val("");
				</script>';
				openDialogAlert("네이버 마일리지 사용금액이 올바르지 않습니다.",400,140,'parent',"");
				exit;
			}
		}
		
		return $settle_price;
	}

	public function update_order($order_seq)
	{
		// 네이버 마일리지 주문번호 업데이트
		if( $_POST['naver_mileage_txId'] ){
			$query = "update fm_naver_mileage set order_seq = '".$order_seq."' where reqTxId=?";
			$this->db -> query($query,array($_POST['naver_mileage_txId']));
		}
	}

}
