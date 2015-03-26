<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class errdoc extends front_base {
	function error_404()
	{		
		$arr_asign = array(
			'companyName'=>$this->config_basic['companyName'],
			'companyPhone'=>$this->config_basic['companyPhone']
		);
		$this->template->assign($arr_asign);
		$this->template->define(array('tpl'=>$this->skin.'/errdoc/404.html'));
		$this->template->print_("tpl");
	}
}
?>