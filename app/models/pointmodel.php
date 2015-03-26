<?php
/**
 * 포인트 내역
 * @author gabia
 * @since version 1.0 - 2013-02-25
 */
class pointmodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_point = 'fm_point';
	}

	/*
	 * 포인트관리
	 * @param
	*/
	public function point_list($sc) {

		$sql = "select * from ".$this->table_point." where 1";

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

	// 포인트총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select point_seq from '.$this->table_point.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 포인트정보
	 * @param
	*/
	public function get_data($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_point."  where 1 ". $sc['whereis'];
		$sql .=" order by point_seq ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 포인트정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_point."  where 1 ". $sc['whereis'];
		$sql .=" order by point_seq asc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 포인트생성
	 * @param
	*/
	public function point_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_point));
		$result = $this->db->insert($this->table_point, $data);
		return $this->db->insert_id();
	}


	/*
	 * 포인트 개별수정
	 * @param
	*/
	public function point_modify($params) {
		if(empty($params['point_seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_point));
		$result = $this->db->update($this->table_point, $data,array('point_seq'=>$params['point_seq']));
		return $result;
	}


	/*
	 * 포인트 개별 삭제
	 * @param
	*/
	public function point_delete($point_seq) {
		if(empty($point_seq))return false;
		$result = $this->db->delete($this->table_point, array('point_seq' => $point_seq));
		return $result;
	}


	/*
	 * 포인트 회원삭제
	 * @param
	*/
	public function point_delete_mb($member_seq) {
		if(empty($member_seq))return false;
		$result = $this->db->delete($this->table_point, array('member_seq' => $member_seq));
		return $result;
	}


	/*
	 * 포인트 주문삭제
	 * @param
	*/
	public function point_delete_ordno($ordno) {
		if(empty($ordno))return false;
		$result = $this->db->delete($this->table_point, array('ordno' => $ordno));
		return $result;
	}
}

/* End of file pointmodel.php */
/* Location: ./app/models/pointmodel */