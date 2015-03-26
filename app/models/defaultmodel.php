<?php
class DefaultModel extends CI_Model {	

	/*
	public function SelectRow($where) {
		return $this->db->where($where)->get("config", 1)->row_array();
	}
	*/
	
	public function DefaultSiteRow() {
		
		$this->db->select("key, value");		
		
		$TypeArr	= array("design", "system");
		$KeyArr		= array("adminskin", "skin", "domain", "mall_id", "admin_title", "front_title");

		$this->db->where_in("type", $TypeArr);		
		$this->db->where_in("key", $KeyArr);
		
		$result = $this->db->get("config")->result_array();

		if(is_array($result)){
			foreach($result as $value){
				$ReturnArr[$value["key"]] = $value["value"];
			}
		}
		
		return $ReturnArr;
	}
}
?>