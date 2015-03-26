<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 1.0 - 2012-11-27
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class Board_goods_process extends admin_base {

	public function __construct() {
		parent::__construct();

		$boardid = (!empty($_POST['board_id'])) ? $_POST['board_id']:$_GET['board_id'];
		define('BOARDID',$boardid);

		$this->load->library('validation');
		$this->load->library('upload');
		$this->load->helper('download');
		$this->load->helper('board');//

		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$this->load->model('Boardindex');
		$this->load->model('Boardcomment');
		$this->load->model('boardbulkorder');
	}

	/* 기본 */
	public function index()
	{
		$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];
	}
}

/* End of file board_process.php */
/* Location: ./app/controllers/admin/board_bulkorder_process.php */