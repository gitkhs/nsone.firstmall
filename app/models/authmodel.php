<?php
class Authmodel extends CI_Model {
	
	public function manager_limit_view($path){
		if($this->managerInfo['manager_yn']=='Y') return true;

		$chk_view = $this->manager_path_return($path);
		//echo $path." : ".$chk_view."<br>";

		$cnt = 0;
		$auth_arr = explode("||",$this->managerInfo['manager_auth']);
		foreach($auth_arr as $k){
			$tmp_arr = explode("=",$k);	
			//$auth[$tmp_arr[0]] = $tmp_arr[1];
			//echo $tmp_arr[0]." : ".$chk_view." : ".$tmp_arr[1]."<br>";
			if($tmp_arr[0] == $chk_view && $tmp_arr[1]=='N') $cnt++;
		}

		if($cnt>0){
			return false;
		}else{
			return true;
		}
	}

	public function manager_limit_act($action){

		//if($this->managerInfo['manager_yn']=='Y') return true;
		if ($action != "member_download" && $this->managerInfo['manager_yn']=='Y') return true;

		$auth_arr = explode("||",$this->managerInfo['manager_auth']);
		foreach($auth_arr as $k){
			$tmp_arr = explode("=",$k);	
			$auth[$tmp_arr[0]] = $tmp_arr[1];
		}
		$act_auth = $auth[$action];

		// 회원정보다운로드 설정 체크 추가 2014-07-16
		if ($action=="member_download" && !$act_auth) {
			$act_auth='N';
		}

		if($act_auth=='N') return false;
		else return true;
	}


	public function manager_path_return($path){
		$path_arr = explode("/",$path);
		if(strpos($path,'setting')){
			$path2 = explode(".",$path_arr[2]);
			$chk_view = $path_arr[1]."_".$path2[0]."_view";
		}else{
			$chk_view = $path_arr[1]."_view";
		}
		return $chk_view;
	}

	public function manager_auth_arr(){
		$cnt = 0;
		$auth_arr = explode("||",$this->managerInfo['manager_auth']);
		foreach($auth_arr as $k){
			$tmp_arr = explode("=",$k);	
			$auth[$tmp_arr[0]] = $tmp_arr[1];
		}
		return $auth;
	}



}
?>