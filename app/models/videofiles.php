<?php
/**
 * 동영상관리테이블
 * @author gabia
 * @since version 1.0 - 2013-04-25
 */
class Videofiles extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_videofiles = 'fm_videofiles';
	}

	/*
	 * 동영상관리
	 * @param
	*/
	public function videofiles_list_all($sc) {

		$sql = "select * from ".$this->table_videofiles." where 1";
		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//
		if(isset($sc['upkind'])) $sql.= ' and upkind="'.$sc['upkind'].'"';//상품/게시판/직접입력 'goods', 'board', 'design'
		if(isset($sc['type'])) $sql.= ' and type="'.$sc['type'].'"';//
		if(isset($sc['viewer_use'])) $sql.= ' and viewer_use="'.$sc['viewer_use'].'"';//노출여부

		$sc['orderby']= ($sc['orderby'])?$sc['orderby']:' seq desc, sort asc ';
		$sc['sort']= ($sc['sort'])?$sc['sort']:' ';

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	/*
	 * 동영상관리
	 * @param
	*/
	public function videofiles_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS *,CASE WHEN upkind = 'goods' THEN '상품' WHEN upkind = 'board' THEN CONCAT(type,' 게시판') ELSE '동영상' END AS upkindtitle from ".$this->table_videofiles." where 1";

		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//
		if(isset($sc['upkind'])) $sql.= ' and upkind="'.$sc['upkind'].'"';//상품/게시판/직접입력 'goods', 'board', 'design'
		if(isset($sc['type'])) $sql.= ' and type="'.$sc['type'].'"';//
		if(isset($sc['viewer_use'])) $sql.= ' and viewer_use="'.$sc['viewer_use'].'"';//노출여부

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

	// 동영상총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select seq from '.$this->table_videofiles.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 동영상정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];
		$sql = "select ".$sc['select']." from  ".$this->table_videofiles."  where 1 ". $sc['whereis'];

		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];
		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//
		if(isset($sc['upkind'])) $sql.= ' and upkind="'.$sc['upkind'].'"';//상품/게시판/직접입력 'goods', 'board', 'design'
		if(isset($sc['type'])) $sql.= ' and type="'.$sc['type'].'"';//
		if(isset($sc['viewer_use'])) $sql.= ' and viewer_use="'.$sc['viewer_use'].'"';//노출여부

		$sql .=" order by sort asc, seq desc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 동영상정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];

		$sql = "select ".$sc['select']." from  ".$this->table_videofiles."  where 1 ". $sc['whereis'];

		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];
		if(isset($sc['tmpcode'])) $sql.= ' and tmpcode="'.$sc['tmpcode'].'"';//
		if(isset($sc['upkind'])) $sql.= ' and upkind="'.$sc['upkind'].'"';//상품/게시판/직접입력 'goods', 'board', 'design'
		if(isset($sc['type'])) $sql.= ' and type="'.$sc['type'].'"';//
		if(isset($sc['viewer_use'])) $sql.= ' and viewer_use="'.$sc['viewer_use'].'"';//노출여부

		$sql .=" order by sort asc, seq desc";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 동영상생성
	 * @param
	*/
	public function videofiles_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_videofiles));
		$result = $this->db->insert($this->table_videofiles, $data);
		return $this->db->insert_id();
	}

	/*
	 * 동영상수정
	 * @param
	*/
	public function videofiles_modify($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_videofiles));
		$result = $this->db->update($this->table_videofiles, $data,array('seq'=>$params['seq']));
		return $result;
	}

	public function videofiles_modify_key($params) {
		if(empty($params['file_key_w']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_videofiles));
		$result = $this->db->update($this->table_videofiles, $data,array('upkind' => $params['upkind'], 'type' => $params['type'],'file_key_w'=>$params['file_key_w']));
		return $result;
	}

	/*
	 * 동영상복사
	 * @param
	*/
	public function videofiles_copy($upkind, $type, $parentseq,$newtype, $newparentseq, $params) {
		$now = date("Y-m-d H:i:s");
		$sql = "INSERT INTO ".$this->table_videofiles."
			(parentseq, upkind, type , tmpcode, mbseq, file_key_w, file_key_i, playtime, memo, viewer_use, r_date,
			pc_width, pc_height, mobile_width, mobile_height, sort, linkurl , linktarget )
		SELECT
		'{$newparentseq}', '{$upkind}','{$newtype}' , '{$params[videotmpcode]}', mbseq, file_key_w, file_key_i, playtime, memo, viewer_use, '{$now}',
			pc_width, pc_height, mobile_width, mobile_height, sort, linkurl , linktarget
		FROM
			".$this->table_videofiles."
		WHERE
			parentseq = '{$parentseq}' and upkind='{$upkind}'  and type='{$type}' ";
		$result = $this->db->query($sql);
		return $this->db->insert_id();
	}


	/*
	 * 동영상이동
	 * @param
	*/
	public function videofiles_move($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_videofiles));
		$result = $this->db->update($this->table_videofiles, $data,array('seq'=>$params['seq']));
		return $result;
	}

	public function videofiles_move_parentseq($params) {
		if(empty($params['parentseq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_videofiles));
		$result = $this->db->update($this->table_videofiles, $data,array('parentseq'=>$params['parentseq']));
		return $result;
	}


	/*
	 * 동영상삭제
	 * @param
	*/
	public function videofiles_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_videofiles, array('seq' => $seq));
		return $result;
	}


	/*
	 * 동영상삭제
	 * @param
	*/
	public function videofiles_delete_parentseq($upkind, $type, $parentseq) {
		if(empty($parentseq))return false;
		$result = $this->db->delete($this->table_videofiles, array('upkind' => $upkind, 'type' => $type, 'parentseq' => $parentseq));
		return $result;
	}

	/*
	 * 동영상삭제1
	 * @param
	*/
	public function videofiles_delete_tmpcode($upkind, $type,$tmpcode) {
		if(empty($tmpcode))return false;
		$result = $this->db->delete($this->table_videofiles, array('upkind' => $upkind, 'type' => $type,'tmpcode' => $tmpcode));
		return $result;
	}

	/*
	 * 동영상삭제2
	 * @param
	*/
	public function videofiles_delete_key($upkind, $type, $file_key_w) {
		if(empty($file_key_w))return false;
		$result = $this->db->delete($this->table_videofiles, array('upkind' => $upkind, 'type' => $type,'file_key_w' => $file_key_w));
		return $result;
	}

	/*
	 * 동영상삭제3
	 * @param
	*/
	public function videofiles_delete_type($upkind, $type) {
		if(empty($type))return false;
		$result = $this->db->delete($this->table_videofiles, array('upkind' => $upkind, 'type' => $type));
		return $result;
	}

}
?>