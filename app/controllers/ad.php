<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class ad extends front_base {
	
	public function index(){
		$this->load->model('visitorlog');
		
		if(empty($_GET['code'])) redirect("/");
		
		$query = $this->db->query("select * from fm_inflow where inflow_code=?",$_GET['code']);
		$inflowData = $query->row_array();
		
		if(empty($inflowData['url'])) redirect("/");

		/* 방문자 분석 기록 */
		$this->visitorlog->execute();

		redirect("{$inflowData['url']}");
	}

}

