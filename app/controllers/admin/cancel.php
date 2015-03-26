<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class cancel extends admin_base {
	
	public function __construct() {
		parent::__construct();
	}

	public function index()
	{
		redirect("/admin/order/catalog");		
	}
	
	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}	
}

/* End of file cancel.php */
/* Location: ./app/controllers/admin/cancel.php */