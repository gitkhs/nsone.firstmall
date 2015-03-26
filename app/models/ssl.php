<?php
class Ssl extends CI_Model {
	var $ssl_use;			// 사용여부	(1/0)
	var $ssl_pay;			// 유료여부	(1/0)

	var $ssl_kind;			// 종류		(text)
	var $ssl_status;		// 상태		(1/0)
	var $ssl_period_start;	// 시작일
	var $ssl_period_expire;	// 만기일
	var $ssl_port='80';		// 포트
	var $ssl_domain;		// 도메인

	function __construct() {
		parent::__construct();

		$this->ssl_use 				= $this->config_system['ssl_use'];
		$this->ssl_pay 				= $this->config_system['ssl_pay'];
		$this->ssl_kind 			= $this->config_system['ssl_kind'];
		$this->ssl_status 			= $this->config_system['ssl_status'];
		$this->ssl_period_start 	= $this->config_system['ssl_period_start'];
		$this->ssl_period_expire 	= $this->config_system['ssl_period_expire'];
		$this->ssl_port 			= $this->config_system['ssl_port'];
		$this->ssl_domain 			= $this->config_system['ssl_domain'];
		$this->ssl_external 		= $this->config_system['ssl_external'];
		$this->ssl_ex_domain 		= $this->config_system['ssl_ex_domain'];
		$this->ssl_ex_port 			= $this->config_system['ssl_ex_port'];

	}

	function ssl_domain_check(){
		if($this->managerInfo && strstr($_SERVER['REQUEST_URI'],'common/editor_image')) return true;//
		if(!$this->designMode && !$this->mobileMode && !$this->storemobileMode && !$this->fammerceMode && !$this->storefammerceMode && !preg_match("/^m\./i",$_SERVER['HTTP_HOST'])){
			if($this->ssl_external_setting() && $_SERVER['REQUEST_METHOD']=='GET' && preg_replace("/:[0-9]{1,5}/","",$_SERVER['HTTP_HOST'])!=$this->ssl_ex_domain){
				if( !strstr($_SERVER['REQUEST_URI'],'member/register_sns_form') ) {//facebook 임시도메인용
					redirect("http://".$this->ssl_ex_domain.$_SERVER['REQUEST_URI']);
					exit;
				}
			}elseif($this->ssl_pay_is_alive() && $_SERVER['REQUEST_METHOD']=='GET' && preg_replace("/:[0-9]{1,5}/","",$_SERVER['HTTP_HOST'])!=$this->ssl_domain){
				if( !strstr($_SERVER['REQUEST_URI'],'member/register_sns_form') ) {//facebook 임시도메인용
					redirect("http://".$this->ssl_domain.$_SERVER['REQUEST_URI']);
					exit;
				}
			}
		}
	}

	/* SSL세팅값 직접입력 체크 */
	function ssl_external_setting(){
		$currentDate = date('Y-m-d');
		if($this->designMode || $this->mobileMode || $this->storemobileMode ) return false;
		if($this->ssl_use && $this->ssl_pay && $this->ssl_external && $this->ssl_ex_domain && $this->ssl_ex_port){
			return true;
		}
		return false;
	}

	/* 유료사용일경우 상태와 날짜 체크 */
	function ssl_pay_is_alive(){
		$currentDate = date('Y-m-d');
		if($this->designMode || $this->mobileMode || $this->storemobileMode ) return false;
		if($this->ssl_use && $this->ssl_pay && $this->ssl_status && $this->ssl_domain && $this->ssl_port){
			if($this->ssl_period_start <= $currentDate && $currentDate <= $this->ssl_period_expire) return true;
			return false;
		}
		return false;
	}

	/* 중계처리 페이지 URL 변환  */
	function get_ssl_action($action){
		if($this->ssl_external_setting()){
			$url = "https://{$this->ssl_ex_domain}:{$this->ssl_ex_port}/common/ssl?action=".urlencode($action);
		}else if($this->ssl_pay_is_alive()){
			$url = "https://{$this->ssl_domain}:{$this->ssl_port}/common/ssl?action=".urlencode($action);
		}else{
			$url = "https://ssl.gabiafreemall.com/post.php?action=".urlencode($action);
		}
		return $url;
	}

	/* 중계처리 페이지 URL 반환 (미사용. 스크립트처리방식으로 할때 사용) */
	/*
	function get_action_url(){
		if($this->ssl_pay_is_alive()){
			$url = "https://{$this->ssl_domain}:{$this->ssl_port}/common/ssl?action=";
		}else{
			$url = "https://ssl.gabiafreemall.com/post.php?action=";
		}
		return $url;
	}
	*/

	/* SSL 중계처리 페이지로 넘어갔다가 되돌아온 데이터 디코드 */
	function decode(){
		if($this->ssl_use && !empty($_POST['sslEncodedString'])){
			$this->load->helper('cookiesecure');
			$decoded = unserialize(cookieDecode(base64_decode($_POST['sslEncodedString']),50));

			if(is_array($decoded)){
				foreach($decoded as $k=>$v){
					$_POST[$k] = $v;
				}
			}
		}
		sql_injection_check();
	}

}
?>