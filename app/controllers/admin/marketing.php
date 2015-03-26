<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class marketing extends admin_base {

	public function __construct() {
		parent::__construct();
	}

	public function index()
	{
		redirect("/admin/marketing/marketplace");
	}

	public function login(){
		$this->load->model('marketingAdminModel');

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->assign(array('vendor'=>$vendor));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function logout(){
		$this->load->model('marketingAdminModel');

		$vcode = $this->session->userdata('marketing');
		$this->marketingAdminModel->set_session_logout();

		pageRedirect("/admin/marketing/login?v=".$vcode, "", "top");
	}

	public function marketplace()
	{
		$params = array();

		$gabiaPageUrl = "http://firstmall.kr/ec_hosting/marketing/marketplace.php";

		$params['firstmall'] = urlencode(iconv('utf-8','euc-kr','yes'));
		$params['shopSno'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['shopSno']));
		$params['domain'] = urlencode(iconv('utf-8','euc-kr',$_SERVER['HTTP_HOST']));
		$params['type'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['service']['code']));

		/* 마케팅서비스 신청을 위한 파라미터 */
		$params['shopDomain'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['domain']));
		$params['shopName'] = urlencode(iconv('utf-8','euc-kr',str_replace(' ','',$this->config_basic['shopName'])));
		$params['tel'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['companyPhone']));
		$params['email'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['companyEmail']));
		$params['name'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['ceo']));

		$params['isdemo'] = urlencode(iconv('utf-8','euc-kr',$this->isdemo['isdemo']));

		if($_GET["setpage"] != ""){
			$params['p'] = urlencode(iconv('utf-8','euc-kr',$_GET["setpage"]));
		}

		$paramsStrings = array();
		foreach($params as $k=>$v) $paramsStrings[] = $k."=".$v;

		$gabiaPageUrl .= "?" . implode('&',$paramsStrings);

		$this->template->assign(array('gabiaPageUrl'=>$gabiaPageUrl));

		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function marketplace_url()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('categorymodel');

		if( !$this->managerInfo['manager_id']){
			$vendor = $this->session->userdata('marketing');
			if($vendor) $visible[$vendor] = true;
		}else{
			$visible['daum'] = true;
			$visible['ebay'] = true;
			$visible['nbp'] = true;
		}

		$navercheckout = config_load('navercheckout');

		foreach((array)$navercheckout['except_category_code'] as $k=>$row){
			$navercheckout['except_category_code'][$k]['category_name']  = $this->categorymodel->get_category_name($row['category_code']);
		}

		foreach((array)$navercheckout['except_goods'] as $k=>$row){
			$sql = "select g.goods_name,
			(select image from fm_goods_image where goods_seq=g.goods_seq and image_type='thumbCart' order by cut_number limit 1) image,
			(select price from fm_goods_option where goods_seq=g.goods_seq and default_option='y') price from
			fm_goods g where goods_seq=?";
			$query = $this->db->query($sql,$row['goods_seq']);
			$goods = $query->row_array();

			$navercheckout['except_goods'][$k]['goods_name'] = $goods['goods_name'];
			$navercheckout['except_goods'][$k]['image'] = $goods['image'];
			$navercheckout['except_goods'][$k]['price'] = $goods['price'];
		}

		$naver_wcs = config_load('naver_wcs');
		$naver_mileage = config_load('naver_mileage');
		$arrmarket = config_load('marketing');

		// 전달 이미지 설정 호출 lwh 2014-02-28
		$marketing_image = config_load('marketing_image');

		### 입점마케팅 전달 데이터 통합 설정 - 입점마케팅 상품명,카드무이자할부 leewh 2015-01-29
		$marketing_feed = config_load('marketing_feed');

		// 입점마케팅 상품 추가할인
		$marketing_sale = config_load('marketing_sale');

		$this->template->assign(array(
			"visible"=>$visible,
			"navercheckout"=>$navercheckout,
			"naver_wcs"=>$naver_wcs,
			"naver_mileage"=>$naver_mileage,
			"arrmarket"=>$arrmarket,
			"marketing_image"=>$marketing_image,
			"marketing_feed"=>$marketing_feed,
			"marketing_sale"=>$marketing_sale
		));

		if($this->session->userdata('marketing')){
			$this->load->model('marketingAdminModel');
			$vendor = $this->marketingAdminModel->vendors[$this->session->userdata('marketing')];
			$this->template->assign(array('vendor'=>$vendor));
		}
		
		//$this->template->assign(marketing.marketdaum
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	public function keyword()
	{
		$params = array();

		$gabiaPageUrl = "http://firstmall.kr/ec_hosting/marketing/keyword.php";

		$params['firstmall'] = urlencode(iconv('utf-8','euc-kr','yes'));
		$params['shopSno'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['shopSno']));
		$params['domain'] = urlencode(iconv('utf-8','euc-kr',$_SERVER['HTTP_HOST']));
		$params['type'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['service']['code']));

		/* 마케팅서비스 신청을 위한 파라미터 */
		$params['shopDomain'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['domain']));
		$params['shopName'] = urlencode(iconv('utf-8','euc-kr',str_replace(' ','',$this->config_basic['shopName'])));
		$params['tel'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['companyPhone']));
		$params['email'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['companyEmail']));
		$params['name'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['ceo']));

		$paramsStrings = array();
		foreach($params as $k=>$v) $paramsStrings[] = $k."=".$v;

		$gabiaPageUrl .= "?" . implode('&',$paramsStrings);

		$this->template->assign(array('gabiaPageUrl'=>$gabiaPageUrl));

		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	### 다음쇼핑하우 입점신청
	public function marketplace_daumshopping_apply(){
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->helper('readurl');

		$url = "http://interface.firstmall.kr/firstmall_plus/request.php?cmd=daumshopping_apply&shopSno={$this->config_system['shopSno']}";
		$formHtml = readurl($url);
		$this->template->assign(array('formHtml'=>$formHtml));

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}


	public function banner()
	{
		$params = array();

		$gabiaPageUrl = "http://firstmall.kr/ec_hosting/marketing/banner.php";

		$params['firstmall'] = urlencode(iconv('utf-8','euc-kr','yes'));
		$params['shopSno'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['shopSno']));
		$params['domain'] = urlencode(iconv('utf-8','euc-kr',$_SERVER['HTTP_HOST']));
		$params['type'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['service']['code']));

		/* 마케팅서비스 신청을 위한 파라미터 */
		$params['shopDomain'] = urlencode(iconv('utf-8','euc-kr',$this->config_system['domain']));
		$params['shopName'] = urlencode(iconv('utf-8','euc-kr',str_replace(' ','',$this->config_basic['shopName'])));
		$params['tel'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['companyPhone']));
		$params['email'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['companyEmail']));
		$params['name'] = urlencode(iconv('utf-8','euc-kr',$this->config_basic['ceo']));

		if($_GET["setpage"] != ""){
			$params['p'] = urlencode(iconv('utf-8','euc-kr',$_GET["setpage"]));
		}

		$paramsStrings = array();
		foreach($params as $k=>$v) $paramsStrings[] = $k."=".$v;

		$gabiaPageUrl .= "?" . implode('&',$paramsStrings);

		$this->template->assign(array('gabiaPageUrl'=>$gabiaPageUrl));

		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}
}

/* End of file marketing.php */
/* Location: ./app/controllers/admin/marketing.php */