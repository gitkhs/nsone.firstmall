<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class mshop_process extends front_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
	}

	public function add_myshop(){
		$this->validation->set_rules('memo', '메모','trim|max_length[50]|xss_clean');
		if($this->validation->exec()===false){
			$callback = "if(parent.document.getElementsByName('memo')[0]) parent.document.getElementsByName('memo')[0].focus();";
			$text = "메모는 50자이내로 입력해 주세요.";
			openDialogAlert($text,300,140,'parent',$callback);
			exit;
		}

		if	(!trim($_POST['shop_no']) || !trim($_POST['seq'])){
			$callback	= "parent.window.close();";
			$text		= "단골 미니샵 등록에 실패하였습니다.";
			openDialogAlert($text,300,140,'parent',$callback);
			exit;
		}

		$this->load->model("membermodel");
		$this->membermodel->add_myminishop();


		$callback	= "parent.addok();";
		$text		= "등록되었습니다.";
		openDialogAlert($text,300,140,'parent',$callback);
		exit;
	}

	public function delete_myshop(){
		$mseq				= $_GET['mseq'];
		$pseq				= $_GET['shopno'];
		$result['result']	= 'fail';
		if	($mseq && $pseq){
			$this->load->model("membermodel");
			$this->membermodel->delete_myshop($mseq, $pseq);
			$result['result']	= 'ok';
		}

		echo json_encode($result);
	}

	public function save_memolist(){
		$member_seq				= trim($_POST['mseq']);
		$memo					= $_POST['memo'];
		$params['member_seq']	= $member_seq;
		if	($member_seq && count($memo) > 0){
			$this->load->model("membermodel");
			foreach($memo as $provider_seq => $memo_str){
				if	($provider_seq){
					$params['provider_seq']	= $provider_seq;
					$params['memo']			= $memo_str;
					$this->membermodel->update_myshop_memo($params);
				}
			}
		}

		$callback	= "parent.location.reload();";
		$text		= "수정되었습니다.";
		openDialogAlert($text,300,140,'parent',$callback);
		exit;
	}
}