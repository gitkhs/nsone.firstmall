<?php
/**
 * 적립금 내역
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class Emoneymodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_emoney = 'fm_emoney';
	}

	/*
	 * 적립금관리
	 * @param
	*/
	public function emoney_list($sc) {

		$sql = "select * from ".$this->table_emoney." where 1";

		if(!empty($sc['search_text']))
		{
			$sql.= " and '".$sc['search_type']."' like '%'".$sc['search_text']."'%' ";
		}

		if(isset($sc['member_seq'])) $sql.= ' and member_seq ='.$sc['member_seq'];//회원

		// 정렬
		if($sc['orderby'] ) {
			$sql.=" order by {$sc['orderby']} {$sc['sort']} ";
		} else {
			$sql.=" order by seq DESC ";
		}

		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}

	// 적립금총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select emoney_seq from '.$this->table_emoney.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 적립금정보
	 * @param
	*/
	public function get_data($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_emoney."  where 1 ". $sc['whereis'];
		$sql .=" order by emoney_seq ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 적립금정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_emoney."  where 1 ". $sc['whereis'];
		$sql .=" order by emoney_seq asc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 적립금생성
	 * @param
	*/
	public function emoney_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_emoney));
		$result = $this->db->insert($this->table_emoney, $data);
		return $this->db->insert_id();
	}


	/*
	 * 적립금 개별수정
	 * @param
	*/
	public function emoney_modify($params) {
		if(empty($params['emoney_seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_emoney));
		$result = $this->db->update($this->table_emoney, $data,array('emoney_seq'=>$params['emoney_seq']));
		return $result;
	}


	/*
	 * 적립금 개별 삭제
	 * @param
	*/
	public function emoney_delete($emoney_seq) {
		if(empty($emoney_seq))return false;
		$result = $this->db->delete($this->table_emoney, array('emoney_seq' => $emoney_seq));
		return $result;
	}


	/*
	 * 적립금 회원삭제
	 * @param
	*/
	public function emoney_delete_mb($member_seq) {
		if(empty($member_seq))return false;
		$result = $this->db->delete($this->table_emoney, array('member_seq' => $member_seq));
		return $result;
	}


	/*
	 * 적립금 주문삭제
	 * @param
	*/
	public function emoney_delete_ordno($ordno) {
		if(empty($ordno))return false;
		$result = $this->db->delete($this->table_emoney, array('ordno' => $ordno));
		return $result;
	}
}
?>