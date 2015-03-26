<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class referer extends admin_base {

	public function __construct(){
		parent::__construct();

		$this->load->model('referermodel');
		$this->load->model('categorymodel');
		$this->load->model('goodsmodel');

		$this->admin_menu();
		$this->tempate_modules();
		$this->file_path	= $this->template_path();

		$this->template->define(array('tpl'=>$this->file_path));

		### AUTH
		$auth = $this->authmodel->manager_limit_act('referer_view');
		if(isset($auth)) $this->template->assign('auth',$auth);

		$this->template->assign('isplusfreenot',$this->isplusfreenot);
	}

	public function index(){
		redirect("/admin/referer/catalog");
	}

	// 유입경로할인 목록
	public function catalog(){

		$params				= $_GET;
		$params['page']		= (!trim($params['page']))		? 0		: trim($params['page']);
		$params['nperpage']	= (!trim($params['nperpage']))	? 10	: trim($params['nperpage']);
		$params['perpage']	= ($params['page'] / $params['nperpage']) + 1;
		$referer			= $this->referermodel->get_referersale_list($params);

		if	($referer['record']){
			$number		= $referer['page']['totalcount'] - $params['page'];
			foreach($referer['record'] as $k => $data){
				$data['number']		= $number;
				$data['date']		= date('Y-m-d H:i', strtotime($data['regist_date']));
				$data['validdate']	= $data['issue_startdate'] . ' ~ ' . $data['issue_enddate'];
				$data['salepricetitle']	= ($data['sale_type'] == 'percent' ) ? $data['percent_goods_sale'].'% 할인, 최대 '.number_format($data['max_percent_goods_sale']).'원': '판매가격의 '.number_format($data['won_goods_sale']).'원';

				$list[]	= $data;
				$number--;
			}
		}

		$paginglay	= pagingtag($referer['page']['totalcount'],$params['nperpage'],getPageUrl($this->file_path).'?', getLinkFilter('',array_keys($params)) );

		if(empty($paginglay))$paginglay = '<p><a class="on red">1</a><p>';
		$this->template->assign('paging',$paginglay);
		$this->template->assign(array('list'=>$list));
		$this->template->assign(array('page'=>$referer['page']));
		$this->template->print_("tpl");
	}

	// 유입경로할인 상세
	public function referersale(){

		if	($_GET['no']){
			$referer			= $this->referermodel->get_referersale_info($_GET['no']);
			$issuegoods 		= $this->referermodel->get_referersale_issuegoods($_GET['no']);
			$issuecategorys		= $this->referermodel->get_referersale_issuecategory($_GET['no']);

			if(($issuegoods)){
				foreach($issuegoods as $key => $tmp) $arrGoodsSeq[] =  $tmp['goods_seq'];
				$goods = $this->goodsmodel->get_goods_list($arrGoodsSeq,'thumbView');
				foreach($issuegoods as $key => $data) $issuegoods[$key] = @array_merge($issuegoods[$key],$goods[$data['goods_seq']]);
				$this->template->assign(array('issuegoods'=>$issuegoods));
			}

			if($issuecategorys){
				foreach($issuecategorys as $key =>$data) $issuecategorys[$key]['category'] = $this->categorymodel -> get_category_name($data['category_code']);
				$this->template->assign(array('issuecategorys'=>$issuecategorys));
			}

			$this->template->assign(array('referer'=>$referer));
		}

		$this->template->print_("tpl");
	}

	// 유입경로 URL 중복 확인
	public function chkRefererUrl(){
		$referer_url	= trim($_GET['referer_url']);
		$url_type		= trim($_GET['url_type']);
		$sdate			= trim($_GET['sdate']);
		$edate			= trim($_GET['edate']);
		// http나 https 제거
		if	(preg_match('/^http/', $referer_url))
			$referer_url	= preg_replace('/^https*\:\/\//', '', $referer_url);

		// 필수값 체크
		if	(!$referer_url || !$sdate || !$edate){
			echo 'fail';
			exit;
		}

		// 유효기간 확인
		if	(strtotime($sdate) > strtotime($edate)){
			echo 'error_date';
			exit;
		}

		$referer	= $this->referermodel->chk_referersale_duple($referer_url, $url_type, $sdate, $edate);
		if	(!$referer['referersale_seq'])	echo 'ok';
		else								echo 'no';
	}
}

/* End of file referer.php */
/* Location: ./app/controllers/admin/referer.php */