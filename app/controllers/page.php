<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class page extends front_base {

	/* 추가페이지 뷰 */
	public function index()
	{
		$tpl = isset($_GET['tpl']) ? $_GET['tpl'] : '';
		$skin = $this->skin;

		// 확장자 검증
		$tmp = explode('.',$tpl);
		$ext = $tmp[count($tmp)-1];
		if( !in_array($ext,array('html','htm')) ){
			echo("정상적인 파일이 아닙니다.");
			exit;
		}

		$this->template_path = $tpl;
		$this->template->assign(array("template_path"=>$this->template_path));

		$this->check_event();

		$this->print_layout($skin.'/'.$tpl);

	}

	/* 이벤트페이지 체크 */
	public function check_event(){
		$this->load->model('eventmodel');

		$event_type = "event";
		if($this->eventmodel->is_gift_template_file($this->template_path)){
			$event_type = "gift";
			$query = $this->db->query("select *, if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'종료','시작 전')) as status from fm_".$event_type." where tpl_path=?",array($this->template_path));
		}else{//
			$query = $this->db->query("select *, if(CURRENT_TIMESTAMP() between start_date and end_date,'진행 중',if(end_date < CURRENT_TIMESTAMP(),'종료','시작 전')) as status from fm_".$event_type." where tpl_path=?",array($this->template_path));
		}

		$data = $query->row_array();

		$this->template->assign(array($event_type."_seq"=>$data[$event_type.'_seq']));

		if($data){

			// 관리자가 아닌경우
			if(!defined('__ISADMIN__')) {

				$this->load->helper("javascript");

				// 페이지뷰 증가
				$this->db->query("update fm_".$event_type." set pageview=pageview+1 where tpl_path=?",array($this->template_path));

				// 이벤트 노출 체크
				if($data['display']=='n'){
					pageRedirect("/","공개되지 않은 이벤트입니다.");
					exit;
				}

				// 이벤트 종료 체크
				switch($data['status']){
					case "시작 전":
						pageRedirect("/","이벤트 시작 전입니다.");
						exit;
					break;
					case "종료":
						pageRedirect("/","종료된  이벤트입니다.");
						exit;
					break;
				}

			}

		}

	}

}

