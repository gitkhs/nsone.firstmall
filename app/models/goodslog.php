<?php
class goodslog extends CI_Model {

	var $current_date;
	var $current_year;
	var $current_month;
	var $current_day;
	var $current_hour;

	function __construct() {
		parent::__construct();

		$this->current_date = date('Y-m-d');
		$this->current_hour = date('H');

		list(
			$this->current_year,
			$this->current_month,
			$this->current_day
		) = explode('-',$this->current_date);
	}

	function add($type,$goods_seq,$addCount=1){

		$addCount = (int)$addCount;

		if($goods_seq){

			$data = array(
				'type'			=> $type,
				'stats_date'	=> $this->current_date,
				'goods_seq'		=> $goods_seq,
			);

			$query = $this->db->get_where('fm_stats_goods',$data);
			$result = $query->row_array();

			$this->db->set($data);

			if($query->num_rows){
				$this->db->where($data);
				$this->db->set("cnt","cnt+{$addCount}",false);
				$this->db->update('fm_stats_goods', $data);
			}else{
				$this->db->set("cnt",$addCount);
				$this->db->insert('fm_stats_goods', $data);
			}
		}
	}


}
?>