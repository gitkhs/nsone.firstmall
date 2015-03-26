<?php
/**
 * 게시글 > 댓글 관련 모듈
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class Boardcomment extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_cmt = 'fm_boardcomment';
		if(!empty($_GET['seq'])) {//게시글상세
			$this->seq = $_GET['seq'];
			if(!empty($_GET['cmt_seq']))$this->cmt_seq = $_GET['cmt_seq'];
		}
	}

	/*
	 *댓글관리
	 * @param
	*/
	public function data_list($sc) {

		$sql = "select  SQL_CALC_FOUND_ROWS * from ".$this->table_cmt." where 1";
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " and boardid = '".$sc['boardid']."' ";
		}
		$sql.= ' and depth = 0 ';//답글
		if(isset($sc['parent'])) $sql.= ' and parent='.$sc['parent'];//게시글
		if(isset($sc['display'])) $sql.= ' and display='.$sc['display'];//삭제글
		if(isset($sc['mid'])) $sql.= ' and mid='.$sc['mid'];//회원

		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( id like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%" ) ';
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$sql .=" limit {$sc['cmtpage']}, {$sc['perpage']} ";
		//echo $sql;
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		//debug_var($data);
		return $data;
	}

	// 댓글총건수
	function get_data_total_count($sc)
	{
		$sql = 'select  SQL_CALC_FOUND_ROWS seq from '.$this->table_cmt;
		if ( defined('BOARDID') ) {
			$sql .= " where boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " where boardid = '".$sc['boardid']."' ";
		}else{
			$sql .= " where 1 ";
		}
		$sql.= ' and depth = 0 ';//답글
		$this->db->query($sql);
		return mysql_affected_rows();
	}


	/*
	 *댓글의 답글 관리
	 * @param
	*/
	public function data_list_reply($sc) {

		$sql = "select  SQL_CALC_FOUND_ROWS * from ".$this->table_cmt." where 1";
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " and boardid = '".$sc['boardid']."' ";
		}
		$sql.= ' and depth != 0 ';//답글
		$sql.= ' and parent='.$sc['parent'];//게시글 고유번호
		$sql.= ' and cmtparent='.$sc['cmtparent'];//댓글보유번호
		if(isset($sc['display'])) $sql.= ' and display='.$sc['display'];//삭제글
		if(isset($sc['mid'])) $sql.= ' and mid='.$sc['mid'];//회원

		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( id like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%" ) ';
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		//debug_var($data);
		return $data;
	}

	/*
	 * 댓글정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";
		$sql = "select ".$sc['select']." from  ".$this->table_cmt."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		if($query) $data = $query->row_array();
		return $data;
	}

	/*
	 * 댓글정보전체가져오기
	 * @param
	*/
	public function get_copy_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";
		$sql = "select ".$sc['select']." from  ".$this->table_cmt."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";//역순으로 복사
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return $data;
	}

	/*
	 * 댓글  갯수
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";
		$sql = "select ".$sc['select']." from  ".$this->table_cmt."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}



	/*
	 * 댓글생성
	 * @param
	*/
	public function data_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_cmt));
		$result = $this->db->insert($this->table_cmt, $data);
		return $this->db->insert_id();
	} 

	// 추천/비추천/추천5가지 증가
	function board_score_update($seq, $scoreid, $plus = ' + ') {
		if(empty($seq))return false;
		$this->db->set($scoreid, $scoreid.' '.$plus.' 1', FALSE);
		$result = $this->db->update($this->table_cmt, null, array('seq' => $seq,'boardid' => BOARDID));
		return $result;
	}

	/*
	 * 댓글수정
	 * @param
	*/
	public function data_modify($params) {
		if(empty($_POST['cmtseq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_cmt));
		$result = $this->db->update($this->table_cmt, $data,array('seq'=>$_POST['cmtseq']));
		return $result;
	}


	/*
	 * 댓글수정
	 * @param
	*/
	public function data_parent_modify($params,$parent) {
		if(empty($parent))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_cmt));
		$result = $this->db->update($this->table_cmt, $data,array('parent'=>$parent));
		return $result;
	}

	/*
	 * 댓글삭제
	 * @param
	*/
	public function data_delete_modify($params,$seq) {
		if(empty($seq))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_cmt));
		$result = $this->db->update($this->table_cmt, $data,array('seq'=>$seq));
		return $result;
	}

	/*
	 * 댓글삭제
	 * @param
	*/
	public function data_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_cmt, array('seq' => $seq,'boardid' => BOARDID));
		return $result;
	}

	/*
	 * 게시물삭제시 댓글
	 * @param
	*/
	public function data_parent_delete($params,$parent) {
		if(empty($parent))return false;
		$result = $this->db->delete($this->table_cmt, array('parent' => $parent,'boardid' => BOARDID));
		return $result;
	}


	/*
	 * 댓글전체삭제
	 * @param
	*/
	public function data_delete_id($boardid) {
		if(empty($boardid))return false;
		$result = $this->db->delete($this->table_cmt, array('boardid' => $boardid));
		return $result;
	}

}
/* End of file boardcomment.php */
/* Location: ./app/models/boardcomment */