<?php
class socialcpconfirmmodel extends CI_Model
{
	public function get_log_socialcp_confirm($export_seq)
	{
		$bind[] = $export_seq;
		$query = "select l.*,
			(select manager_id from fm_manager where manager_seq=l.manager_seq) manager_id,
			(select userid from fm_member where member_seq=l.member_seq) member_id
			from fm_log_socialcp_confirm l where export_seq=? order by seq desc limit 1";
		$query = $this->db->query($query,$bind);
		return $query -> row_array();
	}

	public function log_socialcp_confirm($data)
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

		$query = "insert fm_log_socialcp_confirm set order_seq=?,export_seq=?,regdate=now()".$str_field;
		$this->db->query($query,$bind);
	}

	public function socialcp_confirm($socialcp_confirm,$socialcp_status,$export_code)
	{
		$bind[] = $socialcp_confirm;
		$bind[] = $socialcp_status;
		$bind[] = $export_code;
		$query = "update fm_goods_export set socialcp_confirm_date=now(), socialcp_confirm=?, socialcp_status=? where export_code=?";
		$this->db->query($query,$bind);
	}
}

/* End of file socialcpconfirmmodel.php */
/* Location: ./app/models/socialcpconfirmmodel.php */