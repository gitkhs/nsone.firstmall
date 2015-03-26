<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class intro extends front_base {

	public function main_index()
	{
		redirect("intro/index");
	}

	public function index()
	{
		$this->print_layout($this->template_path());
	}

	/* IP 접속차단 */
	public function denined_ip(){
		$this->print_layout($this->template_path());
	}

	/* 공사중 */
	public function construction(){
		$this->print_layout($this->template_path());
	}

	public function intro_main(){
		$_SESSION['intro'] = "intro_main";
		$this->print_layout($this->template_path());
	}

	/* 회원전용 */
	public function member_only(){
		$this->load->helper('cookie');
		$this->template->assign('idsavechecked',get_cookie('userlogin'));

		$joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		unset($joinform['use_y']);//폐지@2013-04-29
		unset($joinform['use_m']);//폐지@2014-07-01
		$use_sns = array();
		if($joinform['use_f']) $use_sns['facebook'] = array('nm'=>'페이스북','cd'=>'fb');
		if($joinform['use_t']) $use_sns['twitter']	= array('nm'=>'트위터','cd'=>'tw');
		if($joinform['use_c']) $use_sns['cyworld']	= array('nm'=>'싸이월드','cd'=>'cy');
		if($joinform['use_n']) $use_sns['naver']	= array('nm'=>'네이버','cd'=>'nv');
		if($joinform['use_k']) $use_sns['kakao']	= array('nm'=>'카카오','cd'=>'kk');
		if($joinform['use_d']) $use_sns['daum']		= array('nm'=>'카카오','cd'=>'dm');
		$joinform['use_sns'] = $use_sns;
		if($joinform) $this->template->assign('joinform',$joinform);

		$this->print_layout($this->template_path());
	}

	/* 성인인증 */
	public function adult_only(){
		$this->load->helper('cookie');
		$this->template->assign('idsavechecked',get_cookie('userlogin'));

		$realname = config_load('realname');
		$auth = $this->session->userdata('auth');

		$joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		unset($joinform['use_y']);//폐지@2013-04-29
		unset($joinform['use_m']);//폐지@2014-07-01
		$use_sns = array();
		if($joinform['use_f']) $use_sns['facebook'] = array('nm'=>'페이스북','cd'=>'fb');
		if($joinform['use_t']) $use_sns['twitter']	= array('nm'=>'트위터','cd'=>'tw');
		if($joinform['use_c']) $use_sns['cyworld']	= array('nm'=>'싸이월드','cd'=>'cy');
		if($joinform['use_n']) $use_sns['naver']	= array('nm'=>'네이버','cd'=>'nv');
		if($joinform['use_k']) $use_sns['kakao']	= array('nm'=>'카카오','cd'=>'kk');
		if($joinform['use_d']) $use_sns['daum']		= array('nm'=>'카카오','cd'=>'dm');
		$joinform['use_sns'] = $use_sns;
		if($joinform) $this->template->assign('joinform',$joinform);

		$this->template->assign('realnameinfo',$realname);
		$this->template->assign('realname',$realname['useRealname']);

		$this->print_layout($this->template_path());
	}

}