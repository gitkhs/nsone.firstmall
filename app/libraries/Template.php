<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.'libraries/Template_.class'.EXT);
require_once(APPPATH.'libraries/Template_.compiler'.EXT);

/**
 * @version 1.0.0
 * @license copyRight By GD
 * @since 10. 7. 21 오전 1:09 ~
 * 코드이그나이터에서 템플릿 언더바를 사용
 */
class Template extends Template_
{
	var $compile_check =true;
	var $compile_ext   ="php";
	var $skin          ="";
	var $notice        =false;
	var $path_digest   =false;

	var $prefilter     ='';
	var $postfilter    ='';
	var $permission    =0777;
	var $safe_mode     =false;
	var $auto_constant =false;

	var $caching       =true;
	var $cache_expire  =0;


	function Template(){

		$NEWBASEPATH =  str_replace("system/","",BASEPATH);
		if(preg_match('/^admin\//',uri_string())){
			$this->template_dir = $NEWBASEPATH."/admin/skin";
			$this->compile_dir	= $NEWBASEPATH."/_compile/admin";
		}else{
			$this->template_dir = $NEWBASEPATH."/data/skin";
			$this->compile_dir	= $NEWBASEPATH."/_compile/data";
		}

		/* 컴파일 디렉토리가 없으면 생성 */
		if(!is_dir($this->compile_dir)){
			@mkdir($this->compile_dir);
			@chmod($this->compile_dir,0777);
		}

		$this->cache_dir	= BASEPATH."cache";
		$this->prefilter	= "adjustPath";
	}
}
?>