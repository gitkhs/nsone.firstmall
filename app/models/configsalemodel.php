<?php
/**
 * 판매환경 > 할인혜택
 * @author gabia
 * @since version 1.0 - 2012-07-18
 */
class Configsalemodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_confsale			= 'fm_config_sale';
	}

	/*
	 * 관리
	 * @param
	*/
	public function lists($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS * from ".$this->table_confsale."
		where 1 ";

		if( !empty($sc['type']) ) {//수동/자동
			$sql.= " and type = '".$sc['type']."'";
		}

		// 정렬
		if($sc['orderby'] ) {
			$sql.=" order by {$sc['orderby']} {$sc['sort']} ";
		} else {
			$sql.=" order by seq asc ";
		}
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	//총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select  SQL_CALC_FOUND_ROWS seq  from '.$this->table_confsale.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 개별정보
	 * @param
	*/
	public function get_data($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_confsale."  where 1 ". $sc['whereis'];
		$sql .=" order by seq ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 조건검색 건수
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_confsale."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 생성
	 * @param
	*/
	public function confsale_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_confsale));
		$result = $this->db->insert($this->table_confsale, $data);
		return $this->db->insert_id();
	}


	/*
	 * 개별수정
	 * @param
	*/
	public function confsale_modify($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_confsale));
		$result = $this->db->update($this->table_confsale, $data,array('seq'=>$params['seq']));
		return $result;
	}


	/*
	 * 개별 삭제
	 * @param
	*/
	public function confsale_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_confsale, array('seq' => $seq));
		return $result;
	}

	/*
	 * 모바일 할인
	 * @param
	*/
	public function get_mobile_sale_for_goods($price) {
		$sql = "select * from ".$this->table_confsale."	where type='mobile' and price1 <= ? order by price1 desc limit 1";
		$query = $this->db->query($sql,$price);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 모바일 할인
	 * @param
	*/
	public function get_fblike_sale_for_goods($price) {
		$sql = "select * from ".$this->table_confsale."	where type='fblike' and price1 <= ? order by price1 desc limit 1";
		$query = $this->db->query($sql,$price);
		$data = $query->row_array();
		return $data;
	}
}
?>