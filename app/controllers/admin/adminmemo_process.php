<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class adminmemo_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->model('admin/adminmemo');
	}

	public function get_list()
	{
		$page = !empty($_POST['page']) ? $_POST['page'] : 1;
		$search_keyword = !empty($_POST['search_keyword']) ? $_POST['search_keyword'] : '';
		
		$result = $this->adminmemo->get_list(array('page'=>$page,'search_keyword'=>$search_keyword));
		
		foreach($result['record'] as $i=>$row){
			$result['record'][$i]['contents_htmlspecialchars'] = htmlspecialchars($result['record'][$i]['contents']);
			$result['record'][$i]['date'] = date('Y.m.d h:i',strtotime($result['record'][$i]['regist_date']));
		}

		echo json_encode($result);
	}
	
	public function save()
	{
		$contents = trim($_POST['contents']);
		
		if(empty($contents)) return;
		
		$this->adminmemo->save(array('contents'=>$contents));

		$callback = "parent.document.newMemoForm.contents.value='';parent.document.newMemoForm.contents.focus();parent.get_memo_list();";
		openDialogAlert("새 메모가 저장 되었습니다.",400,140,'parent',$callback);
		
	}
	
	public function edit()
	{
		$memo_seq = !empty($_POST['memo_seq']) ? $_POST['memo_seq'] : null;
		$contents = $_POST['contents'];
		
		$this->adminmemo->save(array('memo_seq'=>$memo_seq,'contents'=>$contents));
			
		$callback = "parent.jQuery(\".memo-item[memo_seq='{$memo_seq}'] .memo-item-contents-summary\").text(parent.jQuery(\".memo-item[memo_seq='{$memo_seq}'] textarea\").val());";
		openDialogAlert("메모가 수정 되었습니다.",400,140,'parent',$callback);
	}
	
	public function delete()
	{
		$memo_seq = !empty($_POST['memo_seq']) ? $_POST['memo_seq'] : null;
		
		$this->adminmemo->delete($memo_seq);
			
	}
	
	public function important()
	{
		$memo_seq = !empty($_POST['memo_seq']) ? $_POST['memo_seq'] : null;
		
		$this->adminmemo->important($memo_seq);
	}
	
	public function check(){
		$memo_seq = !empty($_POST['memo_seq']) ? $_POST['memo_seq'] : null;
		
		$this->adminmemo->check($memo_seq);
	}


}

/* End of file adminmemo_process.php */
/* Location: ./app/controllers/admin/adminmemo_process.php */
