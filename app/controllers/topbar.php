<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class topbar extends front_base {
	public function index()
	{
		$tpl_path = $this->designWorkingSkin."/main/".$_GET['no'];
		if($this->designMode){
			$this->template_path = "main/".$_GET['no'];
			$this->template->assign(array("template_path"=>$this->template_path));
			$this->template->prefilter		= "addImageAttributesBefore | ".$this->template->prefilter." | addImageAttributes ";
		}else{
			$this->template->prefilter		= "addImageLazyAttributes | ".$this->template->prefilter;
		}
		$this->print_layout($tpl_path);
		return;
	}

	public function getTab()
	{
		$tpl_path = $this->designWorkingSkin."/main/".$_GET['no'];

		if($this->designMode){
			$this->template_path = "main/".$_GET['no'];
			$this->template->assign(array("template_path"=>$this->template_path));
			$this->template->prefilter		= "addImageAttributesBefore | ".$this->template->prefilter." | addImageAttributes";
		}else{
			$this->template->prefilter		= "addImageLazyAttributes | ".$this->template->prefilter;
		}
		$this->template->define(array('topbar'=>$tpl_path));
		$html = $this->template->fetch("topbar");
		echo $html;
		return;
	}

	public function getGoodAjax()
	{
		$tpl_path = $this->designWorkingSkin."/_modules/common/getGoodAjax.html";
		$this->template->assign(array('seq'=>$_GET["seq"],'perpage'=>$_GET["perpage"]));
		$this->template->define(array('goods'=>$tpl_path));
		$html = $this->template->fetch("goods");
		echo $html;
		return;
	}
}

