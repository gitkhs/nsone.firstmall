<?php
class brandclassificationmodel extends CI_Model {
	protected $table = array("fm_brand_classification");
	private function _query($params) {
		if(isset($params["select"]) && $params["select"] ) {
			$this->db->select($params["select"]);
		}
		
		if(isset($params["secKey"]) && isset($params["secTxt"])) {
			$this->db->like($params["secKey"], $params["secTxt"]);
		}
		
		if(isset($params["not_in_seq"]) && is_array($params["not_in_seq"])) {
			$this->db->where_not_in("seq" ,$params["not_in_seq"]);
		}
		
		if(isset($params["in_seq"]) && is_array($params["in_seq"])) {
			$this->db->where_in("seq" ,$params["in_seq"]);
		}
		$where["(1)"] = "1";
		return $where;
	}

	public function _select_cnt($params=array()) {
		$where = $this->_query($params);
		$this->db->where($where);
		$this->db->from($this->table[0]);
		return $this->db->count_all_results();
	}

	public function _select_list($params=array()) {
		$limit = (isset($params["limit"])) ? $params["limit"] : NULL;
		$offset = (isset($params["offset"])) ? $params["offset"] : NULL;
		$where = $this->_query($params);
		// 정렬관련
		if(isset($params["oType"]) && isset($params["oKey"])){
			$this->db->order_by($params["oKey"], $params["oType"]);
		} else {
			$this->db->order_by("seq", "DESC");
		}
		return $this->db->get_where($this->table[0], $where, $limit, $offset)->result_array();
	}

	public function _select_row($where) {
		return $this->db->where($where)->get($this->table[0], 1)->row_array();
	}

	public function _insert($data) {
		$this->db->insert($this->table[0], $data);
		return $this->db->insert_id();
	}

	public function _delete($where) {
		$this->db->where($where);
		$this->db->delete($this->table[0]);
	}

	public function _update($data, $where) {
		return $this->db->update($this->table[0], $data, $where);
	}
	
	public function _set($params) {
		$this->db->set($params, NULL, FALSE);
		return $this->db->update($this->table[0]);
	}

	public function _replace($data) {
		return $this->db->replace($this->table[0], $data);
	}
}
