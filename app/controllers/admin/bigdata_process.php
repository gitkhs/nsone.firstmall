<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class bigdata_process extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->load->model('bigdatamodel');
	}

	## 설정 저장
	public function save_config(){

		$kinds	= $this->bigdatamodel->get_kind_array();
		foreach($kinds as $kind => $text){
			if	($_POST[$kind]){
				$groupcd	= 'bigdata_'. $kind;
				$same_type	= '';
				if	($_POST[$kind]['same_type'][0])
					$same_type	= implode(',', $_POST[$kind]['same_type']);

				config_save($groupcd, array('list_count_w'	=> $_POST[$kind]['list_count_w']));
				config_save($groupcd, array('view_count_w'	=> $_POST[$kind]['view_count_w']));
				config_save($groupcd, array('use_view_p'	=> $_POST[$kind]['use_view_p']));
				config_save($groupcd, array('use_view_m'	=> $_POST[$kind]['use_view_m']));
				config_save($groupcd, array('smonth'		=> $_POST[$kind]['smonth']));
				config_save($groupcd, array('tmonth'		=> $_POST[$kind]['tmonth']));
				config_save($groupcd, array('tkind'			=> $_POST[$kind]['tkind']));
				config_save($groupcd, array('same_type'		=> $same_type));
				config_save($groupcd, array('use_except'	=> $_POST[$kind]['use_except']));
				config_save($groupcd, array('except'		=> $_POST[$kind]['except']));
			}
		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}
}

/* End of file bigdata_process.php */
/* Location: ./app/controllers/admin/bigdata_process.php */