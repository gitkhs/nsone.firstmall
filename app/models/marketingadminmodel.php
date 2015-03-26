<?php
class marketingAdminModel extends CI_Model {

	public function get_id_pw()
	{
		$this->load->helper('readurl');
		$data = array(
			'cmd' => 'marketingAdmin',
			'v' => $_POST['vcode'],
			'ip' => $_SERVER['REMOTE_ADDR']
		);

		$out = readurl('http://interface.firstmall.kr/firstmall_plus/request.php',$data,$binary=false);
		return $out;
	}

	public function set_session_login($vcode){
		if($vcode == 'navercheckout') $vcode = "nbp";
		$this->session->set_userdata(array('marketing'=>$vcode));
	}

	public function set_session_logout(){
		$this->session->unset_userdata('marketing');
	}
}
