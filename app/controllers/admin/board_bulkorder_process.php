<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 2.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class Board_bulkorder_process extends admin_base {

	public function __construct() {
		parent::__construct();


		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('board_act');
		if(!$auth){
			if($_POST['mode']) { 
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>$this->auth_msg);
				echo json_encode($return); 
			}else{
				$this->admin_menu();
				$this->tempate_modules();
				$callback = "history.go(-1);";
				$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
				$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
				$this->template->print_("denined");
			}
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$boardid = (!empty($_POST['board_id'])) ? $_POST['board_id']:$_GET['board_id'];
		define('BOARDID',$boardid);

		$this->load->library('validation');
		$this->load->library('upload');
		$this->load->helper('download');
		$this->load->helper('board');//

		$this->load->model('Boardmanager');
		$this->load->model('Boardbulkorder','Boardmodel');
		$this->load->model('Boardindex');
		$this->load->model('Boardcomment');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
	}

	/* 기본 */
	public function index()
	{
		$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];

		$sc['whereis']	= ' and id= "'.BOARDID.'" ';
		$sc['select']		= ' * ';
		$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
		if (!isset($manager['id'])) {
			openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','');
			exit;
		}

		if($mode == 'bulkorder_best') {
			$params['best'] = $_POST['best'];
			$result = $this->Boardmodel->data_modify($params);
			echo $result;
			exit;
		}
	}
}

/* End of file board_process.php */
/* Location: ./app/controllers/admin/board_goods_process.php */