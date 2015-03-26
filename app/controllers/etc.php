<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class etc extends front_base {
	
	public function main_index()
	{
		
	}

	/* 플래시매직 아이프레임 내용 출력 */
	public function flashview(){
		
		$this->load->helper('file');
		$this->load->helper('readurl');
		$this->load->library('SofeeXmlParser');
		
		$xml_url = $menuOn = $menuOff = $swf_url = '';
		$js_sources = array();

		$flash_seq = (int) $_GET['flash_seq'];
		$query = $this->db->query("select * from fm_design_flash_file where flash_seq=? order by url asc",$flash_seq);
		foreach ($query->result_array() as $data){
				
			$data['url'] = str_replace('/data/flash/xml/','/data/skin/'.$this->skin.'/images/flash/',$data['url']);
			
			if( preg_match('/data.xml/',$data['url']) ){
				$xml_url = $data['url'];
			} else if($data['type']=='js' &&  preg_match('/\.js/',$data['url']) ){
				$js_sources[] = array(
					'url' => $data['url'],
					'source' => iconv('euc-kr','utf-8',read_file(ROOTPATH.$data['url']))
				);
			} else if($data['type']=='js' && preg_match('/On\.png/',$data['url']) ){
				$menuOn = $data['url'];
			} else if( preg_match('/\Off.png/',$data['url']) ){
				$menuOff = $data['url'];
			} else if($data['type']=='swf' && preg_match('/\.swf/',$data['url'])){
				$swf_url = $data['url'];
			}
			
		}
		
		$xmlParser = new SofeeXmlParser();

		if(!file_exists(ROOTPATH.$xml_url)){
			echo "XML 파일이 존재하지 않습니다.";
			exit;
		}
		
		$xmlParser->parseFile("http://".$_SERVER['HTTP_HOST'].$xml_url);
		$tree = $xmlParser->getTree();
		
		$option = $tree['data']['option'] ? $tree['data']['option'] : $tree['data'];	
		list($icon,$action,$effect) = explode('_',$option['style']['value']);	
		list($icon0,$icon1) = explode('|',$icon);
		
		$icon_gab = 0;
		if($icon == 'I|V'){
			$icon_gab = 30; 
		}

		if(empty($tree['data']['item']) && empty($tree['data']['item'][0]['visual']) && !empty($tree['data']['thumb'])){
			$tree['data']['item'][0]['visual'] =$tree['data']['thumb'];
			$tree['data']['item'][0]['menuOff']['value'] ='/admin/skin/default/images/common/blank.gif';
			$tree['data']['item'][0]['menuOn']['value'] ='/admin/skin/default/images/common/blank.gif';
		}
		if(!is_numeric(array_pop(array_keys($tree['data']['item'])))){
			$tree['data']['item'] = array($tree['data']['item']);
		}

		$tree_items = array();
		$tree_items_size = array();
		foreach($tree['data']['item'] as $k => $v){
			$size = array();
			$size[0] = $size[1] = 0;	
			if($icon0=='I' && !$v['menuOn']['value']){
				$v['menuOn']['value'] = $menuOn;
				$v['menuOff']['value'] = $menuOff;
			}	
			
			$v['visual']['value'] = str_replace('/data/flash/xml/','/data/skin/'.$this->skin.'/images/flash/',$v['visual']['value']);
			$v['menuOff']['value'] = str_replace('/data/flash/xml/','/data/skin/'.$this->skin.'/images/flash/',$v['menuOff']['value']);
			$v['menuOn']['value'] = str_replace('/data/flash/xml/','/data/skin/'.$this->skin.'/images/flash/',$v['menuOn']['value']);
			
			if(!empty($v['menuOn'])){
				$file = ROOTPATH.$v['menuOn']['value'];
				if(is_file($file)){		
					$size = getimagesize($file);
				}
			}
			
			$tree_items[$k] = $v;
			$tree_items_size[$k] = $size;
		}
		
		$this->template->assign(array(
			'flash_seq' => $flash_seq,
			'option' => $option,
			'icon0' => $icon0,
			'icon1' => $icon1,
			'icon_gab' => $icon_gab,
			'js_sources' => $js_sources,
			'xml_url' => $xml_url,
			'swf_url' => $swf_url,
			'tree_items' => $tree_items,
			'tree_items_size' => $tree_items_size,
		));
		
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 상품 디스플레이용 노출 함수
	public function print_display_cach(){
		$display_seq	= (int)$_GET['display_seq'];
		$perpage		= $_GET['set_perpage'];
		$kind			= $_GET['set_kind'];

		$this->designDisplayTabAjaxIdx=(int)$_GET['tab_idx'];

		if	($display_seq > 0){
			$this->template->include_('showDesignDisplay'); 
			return showDesignDisplay($display_seq, $perpage, $kind, 'cach');
		}
	}
}

