<?php
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class joincheck_process extends front_base {
	public function index()
	{
		$this->load->model('joincheckmodel');

		$joincheck_seq = (!empty($_POST['joincheck_seq']))?$_POST['joincheck_seq']:$_GET['joincheck_seq'];
				
		$params['member_seq']		=  $_POST['member_seq'];		//회원
		$params['check_comment']	=  $_POST['comment'];			//출석맨트
		$params['joincheck_seq']	=  $joincheck_seq;			//출석맨트

		$result = $this->joincheckmodel->joincheck($params['joincheck_seq'],$params['member_seq'],$params['check_comment']);
		
		switch($result['code']){
			case 'success' : 
				$callback = "parent.document.location.href='/joincheck/joincheck_view?seq=".$joincheck_seq."';";
				openDialogAlert($result['msg'],400,140,'parent',$callback);	
			break;
			case 'emoney_pay' : 
				$callback = "parent.document.location.href='/joincheck/joincheck_view?seq=".$joincheck_seq."';";
				openDialogAlert($result['msg'],400,180,'parent',$callback);	
			break;
			case 'duplicate' :
				$callback = "parent.document.location.reload()";
				openDialogAlert($result['msg'],400,140,'parent',$callback);			
			break;
			case 'fail' :
				$callback = "parent.document.location.href='/joincheck/joincheck_view?seq=".$joincheck_seq."';";					
				openDialogAlert($result['msg'],400,140,'parent',$callback);		
			break;
			
			case 'end' :
				$callback = "parent.document.location.reload()";
				openDialogAlert($result['msg'],400,140,'parent',$callback);			
			break;
			
			case 'before' :
				$callback = "parent.document.location.reload()";
				openDialogAlert($result['msg'],400,140,'parent',$callback);			
			break;
			
			case 'stop' :
				$callback = "parent.document.location.reload()";
				openDialogAlert($result['msg'],400,140,'parent',$callback);			
			break;
		}

	}
}
?>