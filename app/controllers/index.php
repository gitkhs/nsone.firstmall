<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class index extends common_base {

	public function main_index()
	{
		/* 미리보기 스킨 세션처리 */
		if(isset($_GET['previewSkin']) && $_GET['previewSkin']){
			$this->session->set_userdata('previewSkin', $_GET['previewSkin']);
			set_cookie(array(
				'name'   => 'setDesignMode',
				'value'  => false,
				'path'   => '/'
			));
		}elseif($this->session->userdata('previewSkin')){
			$this->session->unset_userdata('previewSkin');
		}
		if($_SERVER['QUERY_STRING']){
			redirect("main/index?".$_SERVER['QUERY_STRING']);
		}else{
			redirect("main/index");
		}
	}

}

/* End of file index.php */
/* Location: ./app/controllers/admin/index.php */