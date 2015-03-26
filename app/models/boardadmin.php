<?php
/**
 * 게시글 > 접근권한설정
 * @author gabia
 * @since version 1.0 - 2013-05-03
 */
class Boardadmin extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_boardadmin = 'fm_boardadmin';
	}

	/*
	 * 접근권한설정관리
	 * @param
	*/
	public function boardadmin_all($sc) {

		$sql = "select * from ".$this->table_boardadmin." where 1";

		if(isset($sc['boardid'])) $sql.= ' and boardid="'.$sc['boardid'].'"';
		if(isset($sc['manager_seq'])) $sql.= ' and manager_seq="'.$sc['manager_seq'].'"';
		
		//if(isset($sc['board_act'])) $sql.= ' and board_act="'.$sc['board_act'].'"';
		//if(isset($sc['board_view'])) $sql.= ' and board_act="'.$sc['board_view'].'"'; 


		$sc['orderby']= ($sc['orderby'])?$sc['orderby']:' seq desc ';
		$sc['sort']= ($sc['sort'])?$sc['sort']:' ';

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	/*
	 * 접근권한설정관리
	 * @param
	*/
	public function boardadmin_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS * from ".$this->table_boardadmin." where 1";

		if(isset($sc['boardid'])) $sql.= ' and boardid="'.$sc['boardid'].'"';
		if(isset($sc['manager_seq'])) $sql.= ' and manager_seq="'.$sc['manager_seq'].'"';  

		$sc['orderby']= ($sc['orderby'])?$sc['orderby']:' seq desc';
		$sc['sort']= ($sc['sort'])?$sc['sort']:'';

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
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

	// 접근권한설정총건수
	public function get_item_total_count()
	{
		$sql = 'select seq from '.$this->table_boardadmin;
		$query = $this->db->query($sql);
		return $query->num_rows();
	} 

	/*
	 * 접근권한설정정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];
		$sql = "select ".$sc['select']." from  ".$this->table_boardadmin."  where 1 ". $sc['whereis'];

		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];
		if(isset($sc['boardid'])) $sql.= ' and boardid="'.$sc['boardid'].'"';
		if(isset($sc['manager_seq'])) $sql.= ' and manager_seq="'.$sc['manager_seq'].'"';  
		$sql .=" order by seq desc ";
		$query = $this->db->query($sql); 
		$data = $query->row_array(); 
		return $data;
	}

	/*
	 * 접근권한설정정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];

		$sql = "select ".$sc['select']." from  ".$this->table_boardadmin."  where 1 ". $sc['whereis'];

		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];
		if(isset($sc['boardid'])) $sql.= ' and boardid='.$sc['boardid'];
		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//
		if(isset($sc['manager_seq'])) $sql.= ' and manager_seq="'.$sc['manager_seq'].'"';  

		$sql .=" order by seq desc";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 접근권한설정생성
	 * @param
	*/
	public function boardadmin_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_boardadmin));
		$result = $this->db->insert($this->table_boardadmin, $data);
		return $this->db->insert_id();
	}

	/*
	 * 접근권한설정수정
	 * @param
	*/
	public function boardadmin_modify($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_boardadmin));
		$result = $this->db->update($this->table_boardadmin, $data,array('seq'=>$params['seq']));
		return $result;
	}
  
	/*
	 * 접근권한설정삭제
	 * @param
	*/
	public function boardadmin_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_boardadmin, array('seq' => $seq));
		return $result;
	}

	public function boardadmin_delete_all($manager_seq, $boardid) {
		if(empty($boardid))return false;
		if(empty($manager_seq))return false;
		$result = $this->db->delete($this->table_boardadmin, array('boardid' => $boardid,'manager_seq' => $manager_seq));
		return $result;
	}

	//관리자삭제시
	public function boardadmin_delete_manager($manager_seq) {
		if(empty($manager_seq))return false;
		$result = $this->db->delete($this->table_boardadmin, array('manager_seq' => $manager_seq));
		return $result;
	}
	
	//게시판삭제시
	public function boardadmin_delete_id($boardid) {
		if(empty($boardid))return false; 
		$result = $this->db->delete($this->table_boardadmin, array('boardid' => $boardid));
		return $result;
	}

}
/* End of file boardadmin.php */
/* Location: ./app/models/boardadmin */