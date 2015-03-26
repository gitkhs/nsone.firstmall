<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class login extends admin_base {

	public function __construct() {
		parent::__construct();

	}

	public function index(){

		$sql = "select count(*) as cnt from fm_manager";
		$query = $this->db->query($sql);
		$result = $query->row_array();
		if(!$result['cnt']){
			redirect("/admin/login/init");
		}

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$auth = config_load('manager_auth');
		$this->template->assign($auth);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function init(){

		$sql = "select count(*) as cnt from fm_manager";
		$query = $this->db->query($sql);
		$result = $query->row_array();
		if($result['cnt']){
			redirect("/admin/login/index");
		}

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

}

/* End of file main.php */
/* Location: ./app/controllers/admin/main.php */