<?php
/**
 * 게시글 > 첨부파일모듈
 * @author gabia
 * @since version 1.0 - 2013-05-03
 */
class Boardfiles extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_files = 'fm_boardfiles';
	}

	/*
	 * 첨부파일관리
	 * @param
	*/
	public function files_list_all($sc) {

		$sql = "select * from ".$this->table_files." where 1";

		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//

		$sc['orderby']= ($sc['orderby'])?$sc['orderby']:' seq desc, sort asc ';
		$sc['sort']= ($sc['sort'])?$sc['sort']:' ';

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	/*
	 * 첨부파일관리
	 * @param
	*/
	public function files_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS * from ".$this->table_files." where 1";

		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//

		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( tmpcode like "%'.$sc['search_text'].'%" file_key_w like "%'.$sc['search_text'].'%" file_key_i like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%  or memo like "%'.$sc['search_text'].'% " ) ';
		}

		$sc['orderby']= ($sc['orderby'])?$sc['orderby']:' seq desc, sort asc ';
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

	// 첨부파일총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select seq from '.$this->table_files.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 첨부파일정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];
		$sql = "select ".$sc['select']." from  ".$this->table_files."  where 1 ". $sc['whereis'];

		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];
		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//

		$sql .=" order by sort asc, seq desc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 첨부파일정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];

		$sql = "select ".$sc['select']." from  ".$this->table_files."  where 1 ". $sc['whereis'];

		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];
		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//

		$sql .=" order by sort asc, seq desc";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 첨부파일생성
	 * @param
	*/
	public function files_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_files));
		$result = $this->db->insert($this->table_files, $data);
		return $this->db->insert_id();
	}

	/*
	 * 첨부파일수정
	 * @param
	*/
	public function files_modify($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_files));
		$result = $this->db->update($this->table_files, $data,array('seq'=>$params['seq']));
		return $result;
	}

	public function files_modify_tmpcode($params) {
		if(empty($params['tmpcode']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_files));
		$result = $this->db->update($this->table_files, $data,array('boardid' => $params['boardid'], 'tmpcode'=>$params['tmpcode']));
		return $result;
	}

	/*
	 * 첨부파일복사
	 * @param
	*/
	public function files_copy($oldboardid, $parentseq, $newboardid, $newparentseq, $filepath, $params) {
		$now = date("Y-m-d H:i:s");
		$sql = "INSERT INTO ".$this->table_files."
			(parentseq, boardid,is_image,tmpcode,mbrseq,file_ext, name, tmpname, thumbname, file_size, r_date, image_width, image_height, filepath)
		SELECT
		'{$newparentseq}', '{$newboardid}', is_image, '{$params[tmpcode]}', mbrseq, file_ext , name,tmpname, thumbname, file_size,'{$now}', image_width, image_height, '{$filepath}'
		FROM
			".$this->table_files."
		WHERE
			parentseq = '{$parentseq}' and boardid='{$oldboardid}' ";
		$result = $this->db->query($sql);
		return $this->db->insert_id();
	}

	/*
	 * 첨부파일이동
	 * @param
	*/
	public function files_move($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_files));
		$result = $this->db->update($this->table_files, $data,array('seq'=>$params['seq']));
		return $result;
	}


	/*
	 * 첨부파일삭제
	 * @param
	*/
	public function files_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_files, array('seq' => $seq));
		return $result;
	}


	/*
	 * 첨부파일삭제1
	 * @param
	*/
	public function files_delete_parentseq($parentseq) {
		if(empty($parentseq))return false;
		$result = $this->db->delete($this->table_files, array('parentseq' => $parentseq));
		return $result;
	}

	/*
	 * 첨부파일삭제2
	 * @param
	*/
	public function files_delete_tmpcode($boardid, $tmpcode) {
		if(empty($boardid) || empty($tmpcode))return false;
		$result = $this->db->delete($this->table_files, array('boardid' => $boardid, 'tmpcode' => $tmpcode));
		return $result;
	}


	/*
	 * 첨부파일삭제3
	 * @param
	*/
	public function files_delete_filename($boardid, $tmpcode,$filename) {
		if(empty($boardid) || empty($tmpcode) || empty($filename))return false;
		$result = $this->db->delete($this->table_files, array('boardid' => $boardid, 'tmpcode' => $tmpcode,'tmpname' => $filename));
		return $result;
	}
}
/* End of file boardfiles.php */
/* Location: ./app/models/boardfiles */