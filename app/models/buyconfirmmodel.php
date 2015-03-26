<?php
class buyconfirmmodel extends CI_Model
{
	public function get_log_buy_confirm($export_seq)
	{
		$bind[] = $export_seq;
		$query = "select l.*,
			(select manager_id from fm_manager where manager_seq=l.manager_seq) manager_id,
			(select userid from fm_member where member_seq=l.member_seq) member_id
			from fm_log_buy_confirm l where export_seq=? order by seq desc limit 1";
		$query = $this->db->query($query,$bind);
		return $query -> row_array();
	}

	public function log_buy_confirm($data)
	{
		$bind[] = $data['order_seq'];
		$bind[] = $data['export_seq'];

		if($data['manager_seq']){
			$str_field = ",manager_seq=?";
			$bind[] = $data['manager_seq'];
		}
		if($data['member_seq']){
			$str_field = ",member_seq=?";
			$bind[] = $data['member_seq'];
		}
		if($data['doer']){
			$str_field = ",doer=?";
			$bind[] = $data['doer'];
		}

		$query = "insert fm_log_buy_confirm set order_seq=?,export_seq=?,regdate=now()".$str_field;
		$this->db->query($query,$bind);
	}

	public function buy_confirm($buy_confirm,$export_code)
	{
		$bind[] = $buy_confirm;
		$bind[] = $export_code;
		$query = "update fm_goods_export set confirm_date=now(), buy_confirm=? where export_code=?";
		$this->db->query($query,$bind);
	}
}

/* End of file buyconfirmmodel.php */
/* Location: ./app/models/buyconfirmmodel.php */