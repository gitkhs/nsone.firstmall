<?php
class logPersonalInformation extends CI_Model {
	public function insert($type,$manager_seq,$type_seq=''){
		$bind[] = $type;
		$bind[] = $manager_seq;
		$bind[] = $type_seq;
		$query = "insert into fm_log_personal_information set `type`=?,`manager_seq`=?,`type_seq`=?,ip='".$_SERVER['REMOTE_ADDR']."',regist_date=now()";
		$this->db->query($query,$bind);
	}
}

/* End of file logpersonalinformation.php */
/* Location: ./app/models/logpersonalinformation */