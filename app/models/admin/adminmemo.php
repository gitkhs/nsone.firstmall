<?php
class Adminmemo extends CI_Model {

	public function save($params){
		$this->db->set(array('contents'=>$params['contents']));
		
		if(!empty($params['memo_seq'])) {
			$this->db->where(array('memo_seq'=>$params['memo_seq']));
			$this->db->update('fm_admin_memo');
		}else{
			$this->db->set(array(
				'regist_date'=>date('Y-m-d H:i:s'),
				'manager_id'=>$this->managerInfo['manager_id']
			));
			$this->db->insert('fm_admin_memo');
		}
		
	}
	
	public function delete($memo_seq){
		$this->db->where(array('memo_seq'=>$memo_seq));
		$this->db->delete('fm_admin_memo');
	}
	
	public function get_list($params){
		
		$sql = "select a.*, b.mname from fm_admin_memo as a left join fm_manager as b on a.manager_id=b.manager_id";
	
		if($params['search_keyword']){
			$sql .= " where a.contents like '%{$params['search_keyword']}%'";			
		}
		
		$sql .= " order by a.important desc, a.memo_seq desc";
		
		$result = select_page(10,$params['page'],10,$sql,array());
		
		return $result;
	}
	
	public function important($memo_seq){
		$query = $this->db->get_where('fm_admin_memo', array('memo_seq' => $memo_seq));
		$data = $query->row_array();
			
		if($data['important']){
			$important = 0;
		}else{
			$important = 1;
		}
		$this->db->set(array('important'=>$important));
		$this->db->where(array('memo_seq'=>$memo_seq));
		$this->db->update('fm_admin_memo');
		
		echo $important;
	}
	
	public function check($memo_seq){
		$query = $this->db->get_where('fm_admin_memo', array('memo_seq' => $memo_seq));
		$data = $query->row_array();
			
		if($data['check']){
			$check = 0;
		}else{
			$check = 1;
		}
		$this->db->set(array('check'=>$check));
		$this->db->where(array('memo_seq'=>$memo_seq));
		$this->db->update('fm_admin_memo');
		
		echo $check;
	}
	
}
?>