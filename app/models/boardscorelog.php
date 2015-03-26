<?php
/**
 * 게시글 > 추천/비추천 관련 모듈
 * @author gabia
 * @since version 1.0 - 2014.08.21
 */
class Boardscorelog extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_score = 'fm_boardscorelog';
	}

	/*
	 *관리
	 * @param
	*/
	public function data_list($sc, $totalcount = null) {

		$sql = "select  SQL_CALC_FOUND_ROWS * from ".$this->table_score." where 1";
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " and boardid = '".$sc['boardid']."' ";
		} 
		if(isset($sc['parent'])) $sql.= ' and parent='.$sc['parent'];//게시글
		if(isset($sc['cparent'])) $sql.= ' and cparent='.$sc['cparent'];//게시글
		if(isset($sc['mseq'])) $sql.= ' and mseq='.$sc['mseq'];//회원

		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( id like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%" ) ';
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		if($sc['page'] && $sc['perpage']) $sql .=" limit {$sc['page']}, {$sc['perpage']} ";  
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		
		if($totalcount) {
			//총건수
			$sql = "SELECT FOUND_ROWS() as COUNT";
			$query_count = $this->db->query($sql);
			$res_count= $query_count->result_array();
			$data['count'] = $res_count[0]['COUNT']; 
		}
		return $data;
	}

	// 총건수
	function get_data_total_count($sc)
	{
		$sql = 'select  SQL_CALC_FOUND_ROWS seq from '.$this->table_score;
		if ( defined('BOARDID') ) {
			$sql .= " where boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " where boardid = '".$sc['boardid']."' ";
		}else{
			$sql .= " where 1 ";
		} 
		if(isset($sc['parent'])) $sql.= ' and parent='.$sc['parent'];//게시글
		if(isset($sc['cparent'])) $sql.= ' and cparent='.$sc['cparent'];//게시글
		if(isset($sc['mseq'])) $sql.= ' and mseq='.$sc['mseq'];//회원

		$this->db->query($sql);
		return mysql_affected_rows();
	}

	/*
	 * 정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";
		$sql = "select ".$sc['select']." from  ".$this->table_score."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		if($query) $data = $query->row_array();
		return $data;
	} 

	/*
	 *  갯수
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";
		$sql = "select ".$sc['select']." from  ".$this->table_score."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}



	/*
	 * 생성
	 * @param
	*/
	public function data_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_score));
		$result = $this->db->insert($this->table_score, $data);
		return $this->db->insert_id();
	}
 
	/*
	 * 삭제
	 * @param
	*/
	public function data_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_score, array('seq' => $seq,'boardid' => BOARDID));
		return $result;
	}

	/*
	 * 게시글 삭제시 로그삭제
	 * @param
	*/
	public function data_parent_delete($parent) {
		if(empty($parent))return false;
		$result = $this->db->delete($this->table_score, array('parent' => $parent,'boardid' => BOARDID));
		return $result;
	}

	/*
	 * 댓글 삭제시 로그삭제
	 * @param
	*/
	public function data_cparent_delete($parent,$cparent) {
		if(empty($parent))return false;
		if(empty($cparent))return false;
		$result = $this->db->delete($this->table_score, array('parent' => $parent,'cparent' => $cparent,'boardid' => BOARDID));
		return $result;
	}
 
	/*
	 * 게시판전체삭제  로그삭제
	 * @param
	*/
	public function data_delete_id($boardid) {
		if(empty($boardid))return false;
		$result = $this->db->delete($this->table_score, array('boardid' => $boardid));
		return $result;
	}

}

/* End of file boardscorelog.php */
/* Location: ./app/models/boardscorelog */