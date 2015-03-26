<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class main extends front_base {

	public function main_index()
	{
		redirect("main/index");
	}

	public function index()
	{
		$this->template->assign('main',true);
		$this->print_layout($this->template_path());
	}

	public function blank()
	{
		exit;
	}
	
}

