<?php

class managermodel extends CI_Model {
	public function get_manager($manager_seq){
		$bind[] = $manager_seq;
		$query = "select * from fm_manager where manager_seq=?";	
		$query = $this->db->query($query,$bind);
		$row = $query->row_array();
		return $row;
	}

	public function update_passwd($manager_seq,$passwd){		

		$log = "<div>".date("Y-m-d H:i:s")." 비밀번호가 변경되었습니다. (".$_SERVER['REMOTE_ADDR'].")</div>";
		
		$str_md5 = md5($passwd);
		$str_sha256_md5 = hash('sha256',$str_md5);

		$bind[] = $str_sha256_md5;
		$bind[] = $log;
		$bind[] = $manager_seq;		
		$query = "update fm_manager set mpasswd=?,manager_log=concat(?,manager_log),passwordUpdateTime=now() where manager_seq=?";
		$query = $this->db->query($query,$bind);
	}

	public function update_date($manager_seq)
	{
		$log = "<div>".date("Y-m-d H:i:s")." 90일 이후 비밀번호 변경으로 설정하였습니다. (".$_SERVER['REMOTE_ADDR'].")</div>";
		$bind[] = $log;	
		$bind[] = $manager_seq;		
		$query = "update fm_manager set passwordUpdateTime=now(),manager_log=concat(?,manager_log) where manager_seq=?";
		$query = $this->db->query($query,$bind);
	}


}
