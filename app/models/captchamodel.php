<?php
/**
 * 게시글 관련 자동스펨방지 모듈
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */

class Captchamodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table = 'fm_captcha';

		if ( ! @is_dir($this->Boardmanager->board_capt_dir) ){
			@mkdir($this->Boardmanager->board_capt_dir);
			@chmod($this->Boardmanager->board_capt_dir,0777);
		}

	}

	/*
	 * 생성
	 * @param
	*/
	public function data_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table));
		$result = $this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}
	/*
	 *
	 * @param
	*/
	public function data_query($params) {
		$sql = "SELECT COUNT(*) AS count FROM ".$this->table." WHERE word = ? AND ip_address = ? AND captcha_time > ?";
		$binds = array($params['captcha_code'], $params['ip_address'], $params['expiration']);
		$query = $this->db->query($sql, $binds);
		$row = $query->row();
		return ($row->count)?$row->count:0;
	}

	/*
	 * 삭제
	 * @param
	*/
	public function data_delete($expiration,$ip=null) {
		$sql = "DELETE FROM ".$this->table;
		if($ip) {
			$sql .= " WHERE ip_address = '".$ip."'";
		}else{
			$sql .= " WHERE captcha_time < '".$expiration."'";
		}
		$result = $this->db->query($sql);;
		return $result;
	}



}

/* End of file category.php */
/* Location: ./app/models/Captchamodel */