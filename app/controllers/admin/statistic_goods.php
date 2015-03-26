<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class statistic_goods extends admin_base {
	
	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('statsmodel');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('statistic_goods');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('statistic_goods_detail');
		if(!$result['type']){
			$this->template->assign('statistic_goods_detail_limit','Y');
		}

		$this->seriesColors = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575", "#839557", "#958c12",
        "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c3b8f3", "#EA28A2", "#8566cc");
		$this->template->assign(array('seriesColors'=>$this->seriesColors));

		/* 쇼핑몰분석통계 메뉴 */
		$this->template->define(array('goods_menu'=>$this->skin."/statistic_goods/_goods_menu.html"));
		$goods_menu = $this->uri->rsegments[count($this->uri->rsegments)];
		$goods_menu = str_replace(array("_monthly","_daily"),"",$goods_menu);
		$this->template->assign(array('selected_goods_menu'=>$goods_menu));
		$this->template->assign(array('service_code' => $this->config_system['service']['code']));
	}

	public function index()
	{
		redirect("/admin/statistic_goods/goods_cart");		
	}

	public function goods_cart(){

		$cfg_order = config_load('order');
		$statlist	= array();

		$params['sdate']		= trim($_GET['sdate']);
		$params['edate']		= trim($_GET['edate']);
		$params['keyword']		= trim($_GET['keyword']);
		$params['category1']	= trim($_GET['category1']);
		$params['category2']	= trim($_GET['category2']);
		$params['category3']	= trim($_GET['category3']);
		$params['category4']	= trim($_GET['category4']);
		$params['brands1']		= trim($_GET['brands1']);
		$params['brands2']		= trim($_GET['brands2']);
		$params['brands3']		= trim($_GET['brands3']);
		$params['brands4']		= trim($_GET['brands4']);
		$params['order_by']		= trim($_GET['order_by']);

		$statSql	= $this->statsmodel->get_goods_cart_stats($params);
		if	($statSql){
			foreach($statSql->result_array() as $k => $data){
				$statlist[$k]							= $data;

				if	($bfName != $data['stat_goods_name']){
					$lank++;
					$statlist[$k]['goods_first']		= 'y';
					$statlist[$k]['lank']				= $lank;
					$lk									= $k;
				}

				$statlist[$lk]['tstock']			+= $data['stock'];
				$statlist[$lk]['tbadstock']			+= $data['badstock'];
				$statlist[$lk]['treservation15']	+= $data['reservation15'];
				$statlist[$lk]['treservation25']	+= $data['reservation25'];

				$bfName	= $data['stat_goods_name'];
			}
		}

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function goods_wish(){

		$cfg_order = config_load('order');
		$statlist	= array();

		$params['sdate']		= trim($_GET['sdate']);
		$params['edate']		= trim($_GET['edate']);
		$params['keyword']		= trim($_GET['keyword']);
		$params['category1']	= trim($_GET['category1']);
		$params['category2']	= trim($_GET['category2']);
		$params['category3']	= trim($_GET['category3']);
		$params['category4']	= trim($_GET['category4']);
		$params['brands1']		= trim($_GET['brands1']);
		$params['brands2']		= trim($_GET['brands2']);
		$params['brands3']		= trim($_GET['brands3']);
		$params['brands4']		= trim($_GET['brands4']);
		$params['order_by']		= trim($_GET['order_by']);

		$statSql	= $this->statsmodel->get_goods_wish_stats($params);
		$statlist	= $statSql->result_array();

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function goods_search(){

		$cfg_order = config_load('order');
		$statlist	= array();

		$params['sdate']		= trim($_GET['sdate']);
		$params['edate']		= trim($_GET['edate']);
		$params['keyword']		= trim($_GET['keyword']);

		$statSql	= $this->statsmodel->get_goods_search_stats($params);
		$statlist	= $statSql->result_array();
		foreach($statlist as $k=>$data){
			$statlist[$k]['keyword'] = str_replace(array('<','>'),array('[',']'),$data['keyword']);
		}

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function goods_search_view(){
		
		if(!$_GET['search_priod']) $_GET['search_priod'] = 30;
		$statSql	= $this->statsmodel->get_goods_search_by_age($_GET['keyword'],$_GET['search_priod']);
		$statlist	= $statSql->result_array();
		foreach($statlist as $k=>$data){
			if($data['age'] > 10){
				$dataForChartAge[$k][] = substr($data['age'],0,1).'0대';
			}else if($data['age'] > 0){
				$dataForChartAge[$k][] = '10대';
			}else{
				$dataForChartAge[$k][] = '비회원';
			}
			$dataForChartAge[$k][] = $data['cnt'];
		}
		$arr_sex['female'] = "여성";
		$arr_sex['male'] = "남성";
		$arr_sex['none'] = "비회원";
		$statSql	= $this->statsmodel->get_goods_search_by_sex($_GET['keyword'],$_GET['search_priod']);
		$statlist	= $statSql->result_array();
		foreach($statlist as $k=>$data){
			$dataForChartSex[$k][] = $arr_sex[$data['sex']];
			$dataForChartSex[$k][] = $data['cnt'];
		}

		$statSql	= $this->statsmodel->get_goods_search_by_date($_GET['keyword'],$_GET['search_priod']);
		$statlist	= $statSql->result_array();
		foreach($statlist as $k=>$data){
			$dataForChartDate[$k]['regist_date'] = $data['regist_date'];
			$dataForChartDate[$k]['cnt'] = $data['cnt'];
		}

		$file_path	= $this->template_path();
		$this->template->assign(array('dataForChartAge'=>$dataForChartAge));
		$this->template->assign(array('dataForChartSex'=>$dataForChartSex));
		$this->template->assign(array('dataForChartDate'=>$dataForChartDate));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function goods_search_detail(){
		if(!$_GET['page']) $_GET['page'] = 1;		
		$file_path	= $this->template_path();
		$result = $this->statsmodel->get_goods_search_paging_by_date($_GET['keyword'],$_GET['sdate'],$_GET['edate'],$_GET['page']);
		$this->template->assign($result);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function goods_review(){

		$cfg_order = config_load('order');
		$statlist	= array();

		$params['sdate']		= trim($_GET['sdate']);
		$params['edate']		= trim($_GET['edate']);
		$params['keyword']		= trim($_GET['keyword']);
		$params['category1']	= trim($_GET['category1']);
		$params['category2']	= trim($_GET['category2']);
		$params['category3']	= trim($_GET['category3']);
		$params['category4']	= trim($_GET['category4']);
		$params['brands1']		= trim($_GET['brands1']);
		$params['brands2']		= trim($_GET['brands2']);
		$params['brands3']		= trim($_GET['brands3']);
		$params['brands4']		= trim($_GET['brands4']);
		$params['order_by']		= trim($_GET['order_by']);

		$statSql	= $this->statsmodel->get_goods_review_stats($params);
		$statlist	= $statSql->result_array();

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function goods_restock(){

		$cfg_order = config_load('order');
		$statlist	= array();

		$params['sdate']		= trim($_GET['sdate']);
		$params['edate']		= trim($_GET['edate']);
		$params['keyword']		= trim($_GET['keyword']);
		$params['category1']	= trim($_GET['category1']);
		$params['category2']	= trim($_GET['category2']);
		$params['category3']	= trim($_GET['category3']);
		$params['category4']	= trim($_GET['category4']);
		$params['brands1']		= trim($_GET['brands1']);
		$params['brands2']		= trim($_GET['brands2']);
		$params['brands3']		= trim($_GET['brands3']);
		$params['brands4']		= trim($_GET['brands4']);
		$params['order_by']		= trim($_GET['order_by']);

		$statSql	= $this->statsmodel->get_goods_restock_stats($params);
		$statlist	= $statSql->result_array();

		$this->template->assign(array('sc'=>$_GET));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->assign(array('statlist'=>$statlist));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file statistic_promotion.php */
/* Location: ./app/controllers/admin/statistic_promotion.php */