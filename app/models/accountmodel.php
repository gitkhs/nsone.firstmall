<?php
class Accountmodel extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	public function get_refund_data($export, $provider){
		$sql = "
		SELECT
			sum(A.refund_price) as r_price,
			sum(B.ea) as r_ea
		FROM
			fm_order_refund A
			left join fm_order_refund_item B ON A.refund_code = B.refund_code
			left join fm_order_item C ON C.item_seq = B.item_seq
		WHERE
			A.status = 'complete'
			AND A.refund_date like '{$export}%'
			AND C.provider_seq = '{$provider}'
			";
		//echo $sql."<br>";
		$query = $this->db->query($sql);
		$data = $query->result_array();

		$temp = array($data[0]['r_ea'], $data[0]['r_price']);
		return $temp;
	}

	public function get_charge_data($price, $provider){
		$sql = "SELECT * FROM fm_provider_charge WHERE link = '1' AND provider_seq = '{$provider}'";
		$query = $this->db->query($sql);
		$data = $query->result_array();

		$calcu = ($price * ($data[0]['charge']/100));

		if($price<=0){
			$temp = array(0, 0);
		}else{
			$temp = array($calcu, $data[0]['charge']);
		}

		return $temp;
	}

}

/* End of file categorymodel.php */
/* Location: ./app/models/categorymodel.php */