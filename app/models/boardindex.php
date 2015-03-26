<?php
/**
 * 게시글 > 메인/공지용 관련 모듈
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class Boardindex extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_idx = 'fm_boardindex';
		if(!empty($_GET['seq'])) {//게시글상세
			$this->seq = $_GET['seq'];
			if(!empty($_GET['idx_seq']))$this->idx_seq = $_GET['idx_seq'];
		}
	}

	/*
	 * 게시물관리
	 * @param
	*/
	public function idx_list($sc) {

		$sql = "select * from ".$this->table_idx." where 1";
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " and boardid = '".$sc['boardid']."' ";
		}

		//$sql .= " and (onlynotice = '0' or (onlynotice = '1' and (CURRENT_TIMESTAMP() between onlynotice_sdate and onlynotice_edate))) ";//공지영역노출여부

		if(isset($sc['notice'])) $sql.= ' and notice='.$sc['notice'];//공지글
		if(isset($sc['display'])) $sql.= ' and display='.$sc['display'];//삭제글
		if(isset($sc['hidden'])) $sql.= ' and hidden='.$sc['hidden'];//비밀글

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	/*
	 * 게시물관리
	 * @param
	*/
	public function idx_list_search($sc) {

		//$sql = "select * from ".$this->table_idx." where 1";
		$sql = "select idx.* from ".$this->table_idx." idx ";

		if ( defined('BOARDID') ) {
			if( BOARDID == 'goods_qna' ) {
				$sql .= " LEFT JOIN fm_goods_qna board on idx.gid = board.gid where 1 ";
			}elseif( BOARDID == 'goods_review' ) {
				$sql .= " LEFT JOIN fm_goods_review board on idx.gid = board.gid where 1 ";
			}elseif( BOARDID == 'bulkorder' ) {
				$sql .= " LEFT JOIN fm_boardbulkorder board on idx.gid = board.gid where 1 ";
			}else{
				$sql .= " LEFT JOIN fm_boarddata board on idx.gid = board.gid where 1 ";
			}

			$sql .= " and idx.boardid = '".BOARDID."'";// and board.seq
		}elseif ( !empty($sc['boardid']) ) {
			if( $sc['boardid'] == 'goods_qna' ) {
				$sql .= " LEFT JOIN fm_goods_qna board on idx.gid = board.gid where 1 ";
			}elseif( $sc['boardid'] == 'goods_review' ) {
				$sql .= " LEFT JOIN fm_goods_review board on idx.gid = board.gid where 1 ";
			}elseif( $sc['boardid'] == 'bulkorder' ) {
				$sql .= " LEFT JOIN fm_boardbulkorder board on idx.gid = board.gid where 1 ";
			}else{
				$sql .= " LEFT JOIN fm_boarddata board on idx.gid = board.gid where 1 ";
			}
			$sql .= " and idx.boardid = '".$sc['boardid']."' ";//and board.seq
		}

		if(isset($sc['notice'])) $sql.= ' and idx.notice='.$sc['notice'];//공지글
		if(isset($sc['display'])) $sql.= ' and idx.display='.$sc['display'];//삭제글
		if(isset($sc['hidden'])) $sql.= ' and idx.hidden='.$sc['hidden'];//비밀글

		if(!empty($sc['search_text']))
		{
			//$sql .= ' and ( idx.id like "%'.$sc['search_text'].'%" or idx.name like "%'.$sc['search_text'].'%" ) ';
		}
		$sql .=" group by idx.gid ";
		$sql .=" order by idx.{$sc['orderby']} {$sc['sort']}";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}

	// 게시물총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select gid from '.$this->table_idx.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";

		$sql = "select ".$sc['select']." from  ".$this->table_idx."  where 1 ". $sc['whereis'];
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " and boardid = '".$sc['boardid']."' ";
		}
		$sql .=" order by gid asc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sql = "select count(gid) as cnt from  ".$this->table_idx."  where 1 ". $sc['whereis'];
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($sc['boardid']) ) {
			$sql .= " and boardid = '".$sc['boardid']."' ";
		}
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data['cnt'];
	}


	/*
	 * 게시물 gidupdate
	 * @param
	*/
	public function data_gid_save($gidup) {
		$sql = "update ".$this->table_idx." set ".$gidup['set']." where ".$gidup['whereis'];
		if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($gidup['boardid']) ) {
			$sql .= " and boardid = '".$gidup['boardid']."' ";
		}
		$result = $this->db->query($sql);
		return $result;
	}

	/*
	 * 게시물생성
	 * @param
	*/
	public function idx_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_idx));
		$result = $this->db->insert($this->table_idx, $data);
		return $this->db->insert_id();
	}

	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data_optimize() {
		$sql = "optimize table ".$this->table_idx;
		$this->db->query($sql);
	}

	/*
	 * 게시물수정
	 * @param
	*/
	public function idx_modify($params) {
		if(empty($params['gid']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_idx));
		if($params['boardid']){
			$result = $this->db->update($this->table_idx, $data,array('boardid'=>$params['boardid'],'gid'=>$params['gid']));
		}else{
			$result = $this->db->update($this->table_idx, $data,array('boardid'=>BOARDID,'gid'=>$params['gid']));
		}
		return $result;
	}


	/*
	 * 게시물삭제 -> 정보변경동일함
	 * @param
	*/
	public function idx_delete_modify($params) {
		if(empty($params['gid']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_idx));
		if($params['boardid']){
			$result = $this->db->update($this->table_idx, $data,array('boardid'=>$params['boardid'],'gid'=>$params['gid']));
		}else{
			$result = $this->db->update($this->table_idx, $data,array('boardid'=>BOARDID,'gid'=>$params['gid']));
		}
		return $result;
	}

	/*
	 * 게시물삭제
	 * @param
	*/
	public function idx_delete($gid) {
		if(empty($gid))return false;
		$result = $this->db->delete($this->table_idx, array('gid' => $gid,'boardid' => BOARDID));
		return $result;
	}


	/*
	 * 게시물전체삭제
	 * @param
	*/
	public function idx_delete_id($boardid) {
		if(empty($boardid))return false;
		$result = $this->db->delete($this->table_idx, array('boardid' => $boardid));
		return $result;
	}

}
/* End of file boardindex.php */
/* Location: ./app/models/boardindex */