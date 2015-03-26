<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class sns_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library('validation');
		$this->load->library('session');
		$this->load->library('snssocial');
	}

	/* 판매환경 > 페이저장 설정 */
	public function config_facebook_page()
	{
		$this->config_system = ($this->config_system)?$this->config_system:config_load('system');
		$this->snssocial->facebooklogin();
		$snsparams['app_id']						= $this->__APP_ID__;
		$snsparams['method']					= ($_POST['method'])?$_POST['method']:$_GET['method'];
		$snsparams['page_id']					= ($_POST['pageid'])?$_POST['pageid']:$_GET['pageid'];
		$snsparams['page_name']				= ($_POST['pagename'])?$_POST['pagename']:$_GET['pagename'];
		$snsparams['page_url']					= ($_POST['pageurl'])?$_POST['pageurl']:$_GET['pageurl'];
		$snsparams['page_app_link']			= ($_POST['pageapplink'])?$_POST['pageapplink']:$_GET['pageapplink']; 
		$return = $this->savepage($snsparams);
		$returnconnect	= $return['returnconnect'];
		$tabs_page		= $return['tabs_page']; 
		if($tabs_page){
			echo json_encode(array('result'=>true,'msg'=>$tabs_page,'returnconnect'=>$returnconnect));
		}else{
			echo json_encode(array('result'=>false,'msg'=>'페이스북 페이지 연동에 실패하였습니다.','returnconnect'=>$returnconnect));
		}
	}
	
	//
	public function savepage($snsparams) {

		//fammerce iframe link 연동
		require_once $_SERVER['DOCUMENT_ROOT'] . '/app/libraries/class.socket.php';
		$bits = parse_url($this->snssocial->fammerceplusUrl);
		$host = $bits['host'];
		$port = isset($bits['port']) ? $bits['port'] : 80;
		$path = isset($bits['path']) ? $bits['path'] : '/';
		$this->client	= new HttpClient($host, $port);
		$data['mode'	]			= $snsparams['method'];
		$data['subdomain'	]	= $this->config_system['subDomain'];

		$domain		= ($this->__APP_DOMAIN__) ? $this->__APP_DOMAIN__ : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}
		$facebookpage_call = "req_domain=".$domain."&page_id=".$snsparams['page_id']."&page_name=".$snsparams['page_name']."&page_url=".$snsparams['page_url']."&app_id=".$this->__APP_ID__."&remoteaddr=".$_SERVER['REMOTE_ADDR']."&req_type=".$this->arrSns['sns_req_type'];
		$facebookpage_call = makeEncriptParam($facebookpage_call);
		$data['facebookparams'	] = $facebookpage_call;

		if ($this->client->post($this->snssocial->fammerceplusUrlPath, $data)) {
			$returnconnect	= $this->client->getContent();
		}

		$page_id_f_ar				= explode(",",$this->arrSns['page_id_f']);
		$page_name_ar			= explode(",",$this->arrSns['page_name_f']);
		$page_url_ar				= explode(",",$this->arrSns['page_url_f']);
		$page_app_link_f_ar	= explode(",",$this->arrSns['page_app_link_f']);
		if( substr($returnconnect,0,5) != "false" ) {
			if($snsparams['method'] == 'delete') {
				if( in_array('['.$snsparams['page_id'].']',$page_id_f_ar) || in_array($snsparams['page_id'],$page_id_f_ar) ) {//등록 된 경우
					$page_id_f_ar				= str_replace($snsparams['page_id'],'',str_replace('['.$snsparams['page_id'].']','',$this->arrSns['page_id_f']));
					$page_name_ar			= str_replace('['.$snsparams['page_name'].']','',$this->arrSns['page_name_f']);
					$page_url_ar				= str_replace('['.$snsparams['page_url'].']','',$this->arrSns['page_url_f']);
					$page_app_link_f_ar	= str_replace('['.$snsparams['page_url'].'?sk=app_'.$this->__APP_ID__.']','',$this->arrSns['page_app_link_f']);
				}else{
					$page_id_f_ar				=  $this->arrSns['page_id_f'];
					$page_name_ar			=  $this->arrSns['page_name_f'];
					$page_url_ar				=  $this->arrSns['page_url_f'];
					$page_app_link_f_ar	=  $this->arrSns['page_app_link_f'];
				}

				config_save('snssocial',array('page_id_f'=> $page_id_f_ar));
				config_save('snssocial',array('page_name_f'=> $page_name_ar));
				config_save('snssocial',array('page_url_f'=> $page_url_ar));
				config_save('snssocial',array('page_app_link_f'=> $page_app_link_f_ar));
				config_save('snssocial',array('page_seq'=> $returnconnect));

				$tabs_page = $this->snssocial->facebook_tabs_delete($snsparams);
			}else{
				$tabs_page = $this->snssocial->facebook_tabs_add($snsparams);

				if(!in_array('['.$snsparams['page_id'].']',$page_id_f_ar)) {//등록 안된 경우
					$page_id_f_ar[]				= '['.$snsparams['page_id'].']';
					$page_name_ar[]			= '['.$snsparams['page_name'].']';
					$page_url_ar[]					= '['.$snsparams['page_url'].']';
					$page_app_link_f_ar[]	= '['.$snsparams['page_url'].'?sk=app_'.$this->__APP_ID__.']';
				}

				foreach($page_id_f_ar as $pagen=>$v) {
					if($page_id_f_ar[$pagen]) {
						$page_id_floop[]				= $page_id_f_ar[$pagen];
						$page_name_floop[]		= $page_name_ar[$pagen];
						$page_url_floop[]			= $page_url_ar[$pagen];
						$page_app_link_floop[]	= $page_app_link_f_ar[$pagen];
					}
				}
				config_save('snssocial',array('page_id_f'=>implode(",",$page_id_floop)));
				config_save('snssocial',array('page_name_f'=>implode(",",$page_name_floop)));
				config_save('snssocial',array('page_url_f'=>implode(",",$page_url_floop)));
				config_save('snssocial',array('page_app_link_f'=>implode(",",$page_app_link_floop)));
				config_save('snssocial',array('page_seq'=> $returnconnect));
			}
		}
		$return['returnconnect'] = $returnconnect;
		$return['tabs_page']		= $tabs_page;
		return $return;
	}

}

/* End of file sns_process.php */
/* Location: ./app/controllers/sns_process.php */