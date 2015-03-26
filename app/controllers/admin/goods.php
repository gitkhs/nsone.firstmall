<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class goods extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->helper('goods');
		$this->load->library('snssocial');
	}

	public function index()
	{
		redirect("/admin/goods/catalog");
	}

	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();

		if($this->config_system['service']['code']=='P_STOR' && uri_string()!='admin/goods/social_catalog'){
			redirect('admin/goods/social_catalog'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
		}

		$this->load->model('membermodel');
		$sale_list = $this->membermodel->get_member_sale();
		$this->template->assign(array('sale_list'=>$sale_list));

		// 상품검색폼
		$this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
		$file_path	= $this->template_path();

		list($loop,$sc,$sort) =  $this->_goods_list();

		// 옵션 기본 노출 수량 적용
		$config_goods	= config_load('goods');

		//정렬
		$sorderby = $_GET['orderby'];
		$_GET['orderby'] = $sort."_".$_GET['orderby'];

		## 판매마켓 정보
		/*
		$this->load->model('openmarketmodel');
		$linkage	= $this->openmarketmodel->get_linkage_config();
		if	($linkage['linkage_id']){
			$msc['favorite']	= 1;
			$malldata			= $this->openmarketmodel->get_linkage_support_mall($linkage['linkage_id'], $msc);
			if	($malldata)foreach($malldata as $k => $data){
				$mall[$data['mall_code']]	= $data;
			}
		}
		*/

		$this->template->assign('linkage',$linkage);
		$this->template->assign('mall',$mall);
		$this->template->assign('config_goods',$config_goods);
		$this->template->assign('loop',$loop['record']);
		$this->template->assign('page',$loop['page']);
		$this->template->assign('search_yn',$loop['search_yn']);
		$this->template->assign(array('perpage'=>$_GET['perpage'],'orderby'=>$_GET['orderby'],'sort'=>$sort,'sorderby'=>$sorderby));
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function social_catalog()
	{
		if(in_array($this->config_system['service']['code'], array('P_FREE', 'P_PREM')) && uri_string()!='admin/goods/catalog'){
			redirect('admin/goods/catalog'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
		}

		define('SOCIALCPUSE',true);
		$this->template->assign('socialcpuse',1);
		$this->catalog();
	}

	public function _goods_list()
	{
		### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(isset($auth)) $this->template->assign('auth',$auth);

		if( count($_GET) == 0 ){

			if($_COOKIE['goods_list_search']){
				$arr = explode('&',$_COOKIE['goods_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);

					if( preg_match('/\[/',$arr2[0]) ){
						$key = explode('[',$arr2[0]);
						$_GET[$key[0]][ str_replace(']','',$key[1]) ] = $arr2[1];
					}else{
						if( $arr2[0]!='regist_date') $_GET[$arr2[0]] = $arr2[1];
					}
					if( $arr2[0]=='regist_date'){
						if($arr2[1] == 'today'){
							$_GET['regist_date'][0] = date('Y-m-d');
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3day'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-3 day"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '7day'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-7 day"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '1mon'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3mon'){
							$_GET['regist_date'][0] = date('Y-m-d',strtotime("-3 month"));
							$_GET['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == 'all'){
							$_GET['regist_date'][0] = '';
							$_GET['regist_date'][1] = '';
						}
						$_GET['regist_date_type'] = $arr2[1];
					}
				}
			}
		}

		###
		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		//정렬관련 추가 (정가, 할인가, 재고 오름/내림 차순 정렬)
		$orderbyTmp = explode("_",$_GET['orderby']);
		if(in_array($orderbyTmp[0],array("asc","desc"))){
			foreach($orderbyTmp as $orderK=>$orderV) if($orderK > 0) $orderbyTmp2[] = $orderV;
			$_GET['orderby']	= implode("_",$orderbyTmp2);
			$_GET['sort']		= $orderbyTmp[0];
		}else{
			$_GET['orderby'];
		}
		### SEARCH
		$_GET['orderby'] = ($_GET['orderby']) ? $_GET['orderby']:'goods_seq';
		$_GET['sort']	 = ($_GET['sort']) ? $_GET['sort']:'desc';
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):'10';
		$sc = $_GET;
		$sc['goods_type']	= 'goods';

		if($_GET['optstock'] != ""){
			$sc["optstock"] = $_GET["optstock"];
		}

		if($_GET["category1"] != ""){
			$sc["category"] = $_GET["category1"];
		}
		if($_GET["category2"] != ""){
			$sc["category"] = $_GET["category2"];
		}
		if($_GET["category3"] != ""){
			$sc["category"] = $_GET["category3"];
		}
		if($_GET["category4"] != ""){
			$sc["category"] = $_GET["category4"];
		}
/* --판매마켓
		// 판매마켓 검색
		if(is_array($_GET["openmarket"]) && count($_GET['openmarket']) > 0){
			$sc["openmarket"] = $_GET["openmarket"];
		}
*/
		### GOODS
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('videofiles');
		$cfg_order = config_load('order');
		$this->load->model('ordermodel');
		$this->load->model('locationmodel');

		$loop = $this->goodsmodel->admin_goods_list($sc);

		### ADDITION
		$goods_addition		= $this->goodsmodel->goods_addition_list_all();
		$model				= $goods_addition['model'];
		$brand				= $goods_addition['brand'];
		$manufacture		= $goods_addition['manufacture'];
		$orign				= $goods_addition['orgin'];
		//$brand_title		= $this->brandmodel->get_brand_title();

		$this->template->assign(array('brand'=>$brand,'model'=>$model,'manufacture'=>$manufacture,'orign'=>$orign));

		### PAGE & DATA
		/*
		$query = "select count(*) cnt from fm_goods A LEFT JOIN fm_goods_option B ON A.goods_seq = B.goods_seq LEFT JOIN fm_goods_supply C ON A.goods_seq = C.goods_seq AND B.option_seq = C.option_seq where B.default_option = 'y'";
		$query = $this->db->query($query);
		$data = $query->row_array();
		$loop['page']['all_count'] = $data['cnt'];
		*/

		$idx = 0;
		foreach($loop['record'] as $k => $datarow){
			$idx++;
			$datarow['goods_view_text']	= $datarow['goods_view']=='look' ? "<span style='color:blue'>노출</span>" : "<span style='color:red'>미노출</span>";
			$datarow['number']		= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			$optstock = $this->goodsmodel->get_default_option($datarow['goods_seq']);
			$datarow['option_seq']				= $optstock['option_seq'];
			$datarow['reserve_rate']			= $optstock['reserve_rate'];
			$datarow['default_stock']			= $optstock['stock'];
			$datarow['default_reservation15']	= $optstock['reservation15'];
			$datarow['default_reservation25']	= $optstock['reservation25'];
			$datarow['reserve_unit']			= $optstock['reserve_unit'];
			$datarow['reserve']					= $optstock['reserve'];

			$datarow['consumer_price']			= $optstock['consumer_price'];
			$datarow['price']					= $optstock['price'];
			$datarow['supply_price']			= $optstock['supply_price'];

			$optstocktot = $this->goodsmodel->get_tot_option($datarow['goods_seq']);
			$datarow['stock']					= $optstocktot['stock'];
			$datarow['badstock']				= $optstocktot['badstock'];
			$datarow['rstock']					= $optstocktot['rstock'];
			$datarow['stocknothing']			= $optstocktot['stocknothing'];			//재고 0이하인 옵션갯수
			$datarow['rstocknothing']			= $optstocktot['rstocknothing'];		//가용재고 0이하인 옵션갯수

			unset($videosc);
			$videosc['tmpcode']	= $datarow['videotmpcode'];
			$videosc['upkind']	= 'goods';
			$videosc['type']	= 'contents';
			$videocontentfirst = $this->videofiles->get_data($videosc);
			if($videocontentfirst) {
				$datarow['video_content_file_key_w']= $videocontentfirst['file_key_w'];
				$datarow['video_content_viewer_use']= $videocontentfirst['viewer_use'];
			}

			if($datarow['goods_status']=="runout"){
				$datarow['goods_status_text'] = "<span style='color:gray;'>품절</span>";
				$datarow['goods_status_stock_text'] = "<span style='color:gray;'>품절</span>";
			}else if($datarow['goods_status']=="unsold"){
				$datarow['goods_status_text'] = "<span style='color:red;'>판매중지</span>";
			}else if($datarow['goods_status']=="purchasing"){
				$datarow['goods_status_text'] = "<span style='color:red;'>재고확보중</span>";
			}else{
				$datarow['goods_status_text'] = "<span style='color:blue;'>정상</span>";
				$datarow['goods_status_stock_text'] = "<span style='color:blue;'>정상</span>";
			}

			// 옵션
			$datarow['options'][0] = $optstock;
			//$datarow['options']	= $this->goodsmodel->get_goods_option($datarow['goods_seq']);

			if ($datarow['update_date']=="0000-00-00 00:00:00") {
				$datarow['update_date'] = "&nbsp;";
			}

			$loop['record'][$k] = $datarow;
		}

		return array($loop,$sc,$_GET['sort']);
	}

	public function get_goods_option(){
		$this->load->model('goodsmodel');
		$options	= $this->goodsmodel->get_goods_option($_POST['goods_seq']);
		$this->template->assign(array('options'=>$options));
		$file_path	= $this->template_path();
		$file_path = str_replace("get_goods_option.html","_get_goods_option.html",$file_path);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function batch_option_view()
	{
		$no		= $_GET['no'];
		$mopt	= $_GET['mopt'];
		$cfg_order = config_load('order');
		$this->load->model('goodsmodel');
		$file_path	= $this->template_path();
		if	($mopt){
			$this->load->model('openmarketmodel');
			$linkage		= $this->openmarketmodel->get_linkage_config();
			$malldata		= $this->openmarketmodel->get_linkage_mall('code');
			if	($malldata)foreach($malldata as $m => $minfo){
				unset($minfo['revision']);
				$mall[$minfo['mall_code']]	= $minfo;
			}
			$goodsmalldata	= $this->openmarketmodel->get_linkage_goods_mall($no);
			if	($goodsmalldata)foreach($goodsmalldata as $m => $data){
				$goodsmall[]	= $data['mall_code'];
			}
			$malldata		= $this->openmarketmodel->get_linkage_option_price($no, 'code');
			if	($malldata)foreach($malldata as $m => $data){
				$mallprice[$data['option_seq']][$data['mall_code']]	= $data;
			}
		}

		$data_goods = $this->goodsmodel->get_goods($no);
		$data_option = $this->goodsmodel->get_goods_option($no);
		foreach($data_option as $k=>$data){
			$data['shipping_policy'] = $data_goods['shipping_policy'];
			$data['unlimit_shipping_price'] = $data_goods['unlimit_shipping_price'];
			$data['reserve_policy'] = $data_goods['reserve_policy'];
			$field = 'reservation'.$cfg_order['ableStockStep'];
			$data['able_stock'] = $data['stock'] - $data[$field];

			if	($mopt){
				foreach($mall as $m => $mallopt){
					$mallpricedata	= $mallprice[$data['option_seq']][$mallopt['mall_code']];
					$data['mallprice'][$mallopt['mall_code']]	= $mallpricedata['sale_price'];
				}
			}
			$data_option[$k]=$data;
		}
		$loop = $data_option;

		if($_GET['mode'] != 'view'){
			if	($mopt)	{
				$file_path = str_replace('batch_option_view','batch_option_mall',$file_path);
			} else if ($_GET['mode']=='stock') {
				//재고/재고연동/상태/노출/승인 업데이트
				$file_path = str_replace('batch_option_view','batch_option_stock',$file_path);
			} else {
				$file_path = str_replace('batch_option_view','batch_option',$file_path);
			}
		}

		$this->template->assign(array(
			'linkage'		=> $linkage, 
			'mall'			=> $mall, 
			'goodsmall'		=> $goodsmall, 
			'mallprice'		=> $mallprice, 
		));
		$this->template->assign('loop',$loop);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	
	//이미지호스팅 일괄업데이트시의 옵션정보보기
	public function batch_option_view_imagehosting()
	{
		$no = $_GET['no'];
		$cfg_order = config_load('order');
		$this->load->model('goodsmodel');

		$file_path	= $this->template_path();

		$data_goods = $this->goodsmodel->get_goods($no);
		$data_option = $this->goodsmodel->get_goods_option($no);
		foreach($data_option as $k=>$data){
			$data['shipping_policy'] = $data_goods['shipping_policy'];
			$data['unlimit_shipping_price'] = $data_goods['unlimit_shipping_price'];
			$data['reserve_policy'] = $data_goods['reserve_policy'];
			$field = 'reservation'.$cfg_order['ableStockStep'];
			$data['able_stock'] = $data['stock'] - $data[$field];
			$data_option[$k]=$data;
		}
		$loop = $data_option;
		if($_GET['mode'] != 'view') $file_path = str_replace('batch_option_view_imagehosting','batch_option',$file_path);

		$this->template->assign('loop',$loop);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function batch_modify()
	{
		if( !$this->isplusfreenot ){
			pageBack('무료몰Plus+에서는 [상품 데이터 일괄 업데이트]를 지원하지 않는 기능입니다.\n프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 [상품 데이터 일괄 업데이트]을 이용 가능합니다.');
			exit;
		}

		$cfg_order = config_load('order');
		$this->load->model('brandmodel');
		$this->load->model('locationmodel');
		$this->load->model('openmarketmodel');
		if(!$cfg_order['ableStockStep']) $cfg_order['ableStockStep'] = 25;
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if(!$_GET['mode']) $_GET['mode'] = "price";

		//이미지호스팅사용여부
		if( $_GET['mode'] == "imagehosting" ) {
			$this->load->model("imagehosting");
			$this->template->assign(array('imagehostingftp'=>$this->imagehosting->imagehostingftp)); 
			$this->template->define(array('openmarketimghosting' => $this->skin.'/goods/_openmarket_imagehosting.html')); 
		}

		// 상품검색폼
		$this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form.html'));
		if($_GET['mode'] == "watermark") {
			$config_watermark = config_load('watermark');
			if($config_watermark['watermark_position']!=''){
				$config_watermark['watermark_position'] = explode('|',$config_watermark['watermark_position']);
			}
			$file_path	= $this->template_path();
			$this->template->assign(array('config_watermark'=>$config_watermark));
		}
		$this->template->define(array('list_contents' => $this->skin.'/goods/_batch_modify_'.$_GET['mode'].'.html'));

		### COMMON INFO
		$query2 = $this->db->query("select * from fm_goods_info where info_name != '' order by info_seq desc");
		foreach($query2->result_array() as $v){
			$info_loop[] = $v;
		}
		$this->template->assign('info_loop',$info_loop);

		### 이미지 사이즈
		$tmp = config_load('goodsImageSize');
		foreach($tmp as $k=>$v){
			if(substr($k,0,4)=='list'){
				$v['key'] = $k;
				$r_img_size[] = $v;
			}
		}
		$this->template->assign('r_img_size',$r_img_size);

		### 상품아이콘
		$tmp_goods_icon = code_load('goodsIcon');
		foreach($tmp_goods_icon as $k=>$icon_data){
			$path = ROOTPATH."data/icon/goods/".$icon_data['codecd'].".gif";
			if(file_exists($path)) $r_goods_icon[] = $icon_data;
		}
		$this->template->assign('r_goods_icon',$r_goods_icon);

		$_GET['goods_kind'] = array('goods','coupon');


		$this->load->model('membermodel');
		$sale_list = $this->membermodel->get_member_sale();
		$this->template->assign(array('sale_list'=>$sale_list));

		// 판매마켓 관련 추가
		$LINKAGE_SERVICE	= $this->openmarketmodel->chk_linkage_service();
		$linkage			= $this->openmarketmodel->get_linkage_config();
		$mall				= $this->openmarketmodel->get_linkage_mall('code');
		$this->template->assign(array(
			'LINKAGE_SERVICE'	=> $LINKAGE_SERVICE, 
			'linkage'			=> $linkage, 
			'mall'				=> $mall, 
			'mallcnt'			=> count($mall), 
		));
		list($loop,$sc) =  $this->_goods_list();

		foreach($loop['record'] as $key=>$data){
			if( $_GET['mode'] == "goods" ||  $_GET['mode'] == "ifgoods" ) { 
				$data['goods_name']	= htmlspecialchars($data['goods_name']);
				$data['summary']		= htmlspecialchars($data['summary']);
			}

			if($_GET['mode'] == 'watermark'){
				$data['images'] = $this->goodsmodel->get_goods_image($data['goods_seq']);
				$data['cut_count'] = count($data['images']);

			}

			if( $_GET['mode'] == "imagehosting" ) {
				//$this->imagehosting->get_contents_cnt($data['contents'],$data['changeimg'],$data['orgimg']);
			}

			$data['stock'] = $data['default_stock'];
			$field = 'default_'.'reservation'.$cfg_order['ableStockStep'];
			$data['able_stock'] = $data['stock'] - $data[$field];
			$data['event'] = $this->goodsmodel->get_event_price($data['price'], $data['goods_seq'], $data['category_code'], $data['consumer_price'], $data);
			$data['event_seq'] = $data['event']['event_seq'];
			$data['icons'] = $this->goodsmodel->get_goods_icon($data['goods_seq'],1);
			$data['r_img_size'] = $r_img_size;
			$data['info_loop'] = $info_loop;
			$data['goods_icon'] = $r_goods_icon;

			if($_GET['mode'] == "category") {
				$r_category = $this->goodsmodel->get_goods_category($data['goods_seq']);
				foreach( $r_category as $k_category => $data_category){
					$r_category_code = $this->categorymodel->split_category($data_category['category_code']);
					$r_category_name = array();
					foreach( $r_category_code as $k_code => $code){
						$r_category_name[] = $this->categorymodel->one_category_name($code);
					}
					$data_category['category_name'] = $r_category_name;
					$r_category[$k_category] = $data_category;
				}
				$data['category'] = $r_category;

				$r_brand = $this->goodsmodel->get_goods_brand($data['goods_seq']);
				foreach( $r_brand as $k_brand => $data_brand){
					$r_brand_code = $this->brandmodel->split_brand($data_brand['category_code']);
					$r_brand_name = array();
					foreach( $r_brand_code as $k_code => $code){
						$r_brand_name[] = $this->brandmodel->one_brand_name($code);
					}
					$data_brand['brand_name'] = $r_brand_name;
					$r_brand[$k_brand] = $data_brand;
				}
				$data['brand'] = $r_brand;

				$r_location = $this->goodsmodel->get_goods_location($data['goods_seq']);
				foreach( $r_location as $k_location => $data_location){
					$r_location_code = $this->locationmodel->split_location($data_location['location_code']);
					$r_location_name = array();
					foreach( $r_location_code as $k_code => $code){
						$r_location_name[] = $this->locationmodel->one_location_name($code);
					}
					$data_location['location_name'] = $r_location_name;
					$r_location[$k_location] = $data_location;
				}
				$data['location'] = $r_location;
			}

			if	($_GET['mode'] == 'mprice'){
				unset($mallprice);
				unset($market);
				$mpricedata	= $this->openmarketmodel->get_linkage_option_price($data['goods_seq']);
				if	($mpricedata)foreach($mpricedata as $m => $mopt){
					$market[$mopt['mall_code']][]	= $mopt;
				}
				$mmalldata	= $this->openmarketmodel->get_linkage_goods_mall($data['goods_seq']);
				if	($mmalldata)foreach($mmalldata as $m => $mmall){
					$mallinfo[$mmall['mall_code']]	= 'y';
				}

				foreach($mall as $mallcode => $malldata){
					$mallprice[$mallcode]	= $market[$mallcode][0]['sale_price'];
				}
				$data['mallinfo']	= $mallinfo;
				$data['mallprice']	= $mallprice;
				$data['mallcnt']	= count($mall);
			}

			$loop['record'][$key] = $data;
		}

		$this->template->assign('loop',$loop['record']);
		$this->template->assign('page',$loop['page']);
		$this->template->assign('search_yn',$loop['search_yn']);
		$this->template->assign(array('perpage'=>$_GET['perpage'],'orderby'=>$_GET['orderby']));
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function set_search_default(){
		foreach($_POST as $key => $data){
			if( is_array($data) ){
				foreach($data as $key2 => $data2){
					if($data2) $cookie_arr[] = $key."[".$key2."]"."=".$data2;
				}
			}else if($data){
				if(substr($key,0,2)=='s_') $key = str_replace("s_","",$key);
				$cookie_arr[] = $key."=".$data;
			}
		}
		if($cookie_arr){
			$cookie_str = implode('&',$cookie_arr);
			$_COOKIE['goods_list_search'] = $cookie_str;
			setcookie('goods_list_search',$cookie_str,time()+86400*30);
		}
		$callback = "parent.closeDialog('search_detail_dialog');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_default(){
		$arr = explode('&',$_COOKIE['goods_list_search']);
		foreach($arr as $data){
			$arr2 = explode("=",$data);
			$result[] = $arr2;

		}
		echo json_encode($result);
	}

	public function set_favorite(){
		$this->db->where('goods_seq', $_GET['goods_seq']);
		$result = $this->db->update('fm_goods', array("favorite_chk"=>$_GET['status']));
		echo $result;
	}

	public function regist()
	{
		$this->admin_menu();
		$this->tempate_modules();
		
		## 상품통계
		$_GET['goods_seq'] = $_GET['no'];
		if($_GET['goods_seq']){
			$statFilePath	= $this->skin."/statistic/advanced_statistics.html";
			$this->template->assign(array('service_code' => $this->config_system['service']['code']));
			$this->template->define(array('statTpl'=>$statFilePath));
			$data_stat = $this->template->fetch("statTpl");
			$this->template->assign(array('data_stat' => $data_stat));
		}

		$cfg_order = config_load('order');

		$this->load->model('membermodel');		
		
		//이미지호스팅사용여부
		$this->load->model("imagehosting");
		$this->template->assign(array('imagehostingftp'=>$this->imagehosting->imagehostingftp)); 
		$this->template->define(array('openmarketimghosting' => $this->skin.'/goods/_openmarket_imagehosting.html')); 
		$this->template->assign('goods_quick_topmenu','-quick');

		$limit_stock = '';
		$totstock = 0;
		$reservation15 = 0;
		$reservation25 = 0;

		$this->template->define(array('tpl'=>str_replace("social_","",$this->template_path())));
		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));
		$cfg_goods = config_load('goods');
		$cfg_goods['videototalcut']  = 5;//5건까지만등록가능
		if( $cfg_goods['ucc_domain'] && $cfg_goods['ucc_key'] ){
			$cfg_goods['video_use']=  'Y';
		}
		$this->template->assign('cfg_goods',$cfg_goods);

		$this->load->model('brandmodel');
		$this->load->model('categorymodel');
		$this->load->model('locationmodel');
		$query = "SELECT category_code FROM (SELECT category_code,regist_date FROM fm_category_link where regist_date!='0000-00-00 00:00:00' order by regist_date desc) AS aliasTable GROUP BY  aliasTable.category_code order by aliasTable.regist_date desc limit 30";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['title'] =  $this->categorymodel->get_category_name($row['category_code']);
			if(trim($row['title']))$last_categories[] = $row;
		}

		$query = "SELECT category_code FROM (SELECT category_code,regist_date FROM fm_brand_link where regist_date!='0000-00-00 00:00:00' order by regist_date desc) AS aliasTable GROUP BY  aliasTable.category_code order by aliasTable.regist_date desc limit 30";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['title'] =  $this->brandmodel->get_brand_name($row['category_code']);
			if(trim($row['title']))$last_brands[] = $row;
		}

		$query = "SELECT location_code FROM (SELECT location_code,regist_date FROM fm_location_link where regist_date!='0000-00-00 00:00:00' order by regist_date desc) AS aliasTable GROUP BY  aliasTable.location_code order by aliasTable.regist_date desc limit 30";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['title'] =  $this->locationmodel->get_location_name($row['location_code']);
			if(trim($row['title']))$last_locations[] = $row;
		}

		$tmp = config_load('reserve');
		$default_reserve_percent = $tmp['default_reserve_percent'];

		### COMMON INFO
		$query2 = $this->db->query("select * from fm_goods_info where info_name != '' order by info_seq desc");
		foreach($query2->result_array() as $v){
			$info_loop[] = $v;
		}
		
		if( isset($_GET['no']) ){
			$no = (int) $_GET['no'];
			$this->load->model('goodsmodel');

			$issueGoodsQuery = $this->db->query("select count(*) as cnt from fm_member_group_issuegoods where goods_seq = '".$no."' and type='sale'");
			$issueGoodsSale = $issueGoodsQuery->row_array();

			if($issueGoodsSale["cnt"] > 0){
				$this->template->assign(array('issueGoodsSale'=>'y'));
			}

			$issueGoodsQuery = $this->db->query("select count(*) as cnt from fm_member_group_issuegoods where goods_seq = '".$no."' and type='emoney'");
			$issueGoodsEmoney = $issueGoodsQuery->row_array();

			if($issueGoodsEmoney["cnt"] > 0){
				$this->template->assign(array('issueGoodsEmoney'=>'y'));
			}


			$categories = $this->goodsmodel->get_goods_category($no);
			if($categories){
				foreach($categories as $key => $data) $categories[$key]['title'] = $this->categorymodel->get_category_name($data['category_code']);
			}
			$brands = $this->goodsmodel->get_goods_brand($no);
			if($brands){
				foreach($brands as $key => $data) $brands[$key]['title'] = $this->brandmodel->get_brand_name($data['category_code']);
			}
			$locations = $this->goodsmodel->get_goods_location($no);
			if($locations){
				foreach($locations as $key => $data) $locations[$key]['title'] = $this->locationmodel->get_location_name($data['location_code']);
			}

			$goods = $this->goodsmodel->get_goods($no);
			if( $goods['goods_kind'] == 'coupon' && defined('SOCIALCPUSE') != true ){
				redirect("/admin/goods/social_regist?no=".$no);
				exit;
			}

			$goods['title']			= strip_tags($goods['goods_name']);
			$goods['goods_name']	= htmlspecialchars($goods['goods_name']);
			$goods['summary']		= htmlspecialchars($goods['summary']);
			$goods['purchase_goods_name']	= htmlspecialchars($goods['purchase_goods_name']);
			$goods['keyword']		= htmlspecialchars($goods['keyword']);
			$goods['string_price']	= htmlspecialchars($goods['string_price']);
			$goods['sub_info_desc']	= json_decode($goods['sub_info_desc']);


			$video_size = explode("X" , $goods['video_size']);
			$goods['video_size0'] = $video_size[0];
			$goods['video_size1'] = $video_size[1];
			$video_size_mobile = explode("X" , $goods['video_size_mobile']);
			$goods['video_size_mobile0'] = $video_size_mobile[0];
			$goods['video_size_mobile1'] = $video_size_mobile[1];


			$i=0;
			foreach($goods['sub_info_desc'] as $key => $value){
				if($key != "_empty_" && $key != ""){
					$goods_sub['subInfo'][$i]["title"] = $key;
					$goods_sub['subInfo'][$i]["desc"] = $value;
					$i++;
				}
			}

			$goods['sub_info_desc'] = $goods_sub;

			### 모바일 상품설명
			$goods['mobile_contents'] = trim($goods['mobile_contents']);
			if(in_array(strtolower($goods['mobile_contents']),array("<p>&nbsp;</p>","<p><br></p>"))) $goods['mobile_contents'] = "";
			if(!$goods['mobile_contents'] && $goods['contents']){
				$goods['mobile_contents'] = $this->goodsmodel->set_mobile_contents($goods['contents'],$goods['goods_seq']);
			}

			if($goods['goods_status']=='normal'){
				$goods['goods_status_text'] = "정상";
			}else if($goods['goods_status']=='runout'){
				$goods['goods_status_text'] = "품절";
			}else if($goods['goods_status']=='purchasing'){
				$goods['goods_status_text'] = "재고확보중";
			}else{
				$goods['goods_status_text'] = "판매중지";
			}
			$goods['goods_view_text'] = $goods['goods_view']=='look' ? "노출" : "미노출";
			$images = $this->goodsmodel->get_goods_image($no);
			$additions = $this->goodsmodel->get_goods_addition($no);
			$options = $this->goodsmodel->get_goods_option($no);
			$suboptions = $this->goodsmodel->get_goods_suboption($no);
			//debug_var($options);
			//debug_var($suboptions);

			$suboptions_cnt = 0;
			foreach($suboptions as $k=>$v){
				foreach($v as $k2=>$v2){
					$suboptions[$k][$k2]['suboptions_cnt'] = $suboptions_cnt;
					$suboptions_cnt++;
				}
			}
			$this->template->assign(array('total_suboptions_cnt'=>$suboptions_cnt-1));

			$inputs = $this->goodsmodel->get_goods_input($no);
			$icons = $this->goodsmodel->get_goods_icon($no,'1');

			//쿠폰상품그룹
			if($goods['social_goods_group']){
				$this->load->model('socialgoodsgroupmodel');
				$goods['social_goods_group_data'] = $this->socialgoodsgroupmodel->get_data(array('select'=>' * ','group_seq'=>$goods['social_goods_group']));
			}

			//상품추가양식 정보
			unset($goodscode);
			$codeqry = "select * from fm_goods_code_form  where label_type ='goodsaddinfo' and base_type != '1' order by label_type, sort_seq";
			$codequery = $this->db->query($codeqry);
			$code_arr = $codequery -> result_array();
			foreach ($code_arr as $code_datarow){
				$goodscode[] = $code_datarow;
			}

			//추가정보의 모델명추출
			$defaultadditionsar = array("모델명","브랜드","제조사","원산지");//model, brand, manufacture, orgin
			if($additions){
				foreach($additions as $data_additions){
					foreach ($goodscode as $key=>$gdcode_datarow){
						$goodscode[$key]['label_write'] = get_labelitem_type($gdcode_datarow,$data_additions,'');
						if( in_array($gdcode_datarow['label_title'], $defaultadditionsar,true) ){
							$goodscode[$key]['label_title'] = $gdcode_datarow['label_title'].' [코드]';
						}
					}
					$data_additions['goodsaddinfo'] = $goodscode;
					$newadditions[] = $data_additions;
				}
			}
			$additions = $newadditions;//다시정의함
			foreach($additions as $data_additions){
				if($data_additions['type'] == 'model' ){
					$goods['model_text'] =  $data_additions['contents'];
					break;
				}
			}

			// 지역정보 체크값 기본 N :: 2014-04-01 lwh
			$isAddr = 'N';

			// 총재고, 출고예약량
			foreach($options as $key_option => $data_option){
				$totstock += $data_option['stock'];
				$reservation15 += $data_option['reservation15'];
				$reservation25 += $data_option['reservation25'];
				if	($cfg_order['ableStockStep'] == 15){
					$totunUsableStock	+= $data_option['badstock'] + $data_option['reservation15'];
				}
				if	($cfg_order['ableStockStep'] == 25){
					$totunUsableStock	+= $data_option['badstock'] + $data_option['reservation25'];
				}

				// 기본정책일 경우 적립금 표기
				if($goods['reserve_policy'] == 'shop'){
					$data_option['reserve_rate'] = $default_reserve_percent;
					$data_option['reserve_unit'] = 'percent';
					$data_option['reserve'] = floor($data_option['price'] * ($default_reserve_percent * 0.01));
					$options[$key_option] = $data_option;
				}

				// 지역정보 체크 :: 2014-03-31 lwh
				if(in_array('address',$data_option['divide_newtype'])){
					if($data_option['address'])
						$isAddr = 'Y';
				}
			}

			// 총재고, 출고예약량
			if	($suboptions)foreach($suboptions as $key_suboption => $data_suboption){
				if	($data_suboption)foreach($data_suboption as $key_sub => $data_sub){

					$totsuboptionrowcnt++;
					$totstock += $data_sub['stock'];
					$reservation15 += $data_sub['reservation15'];
					$reservation25 += $data_sub['reservation25'];
					if	($cfg_order['ableStockStep'] == 15){
						$totunUsableStock	+= $data_sub['badstock'] + $data_sub['reservation15'];
					}
					if	($cfg_order['ableStockStep'] == 25){
						$totunUsableStock	+= $data_sub['badstock'] + $data_sub['reservation25'];
					}

					// 기본정책일 경우 적립금 표기
					if($goods['reserve_policy'] == 'shop'){
						$data_sub['reserve_rate'] = $default_reserve_percent;
						$data_sub['reserve_unit'] = 'percent';
						$data_sub['reserve'] = floor($data_sub['price'] * ($default_reserve_percent * 0.01));
						$data_suboption[$key_sub] = $data_sub;
					}
				}
				$suboptions[$key_suboption]	= $data_suboption;
			}
	
			$this->template->assign(array('totstock'=>$totstock));
			$this->template->assign(array('totunUsableStock'=>$totunUsableStock));
			
			### 공용정보
			$info = get_data("fm_goods_info",array("info_seq"=>$goods['info_seq']));
			$goods['common_contents'] = $info ? $info[0]['info_value'] : '';

			// 배송정보 가져오기
			$delivery = $this->goodsmodel->get_goods_delivery($goods);
			if( $goods['goods_kind'] == 'coupon' ) {//쇼셜쿠폰상품@2031-11-13
				/**$this->load->model('locationmodel');
				$locations = $this->goodsmodel->get_goods_location($no);
				if($locations){
					foreach($locations as $key => $data) $locations[$key]['title'] = $this->locationmodel->get_location_name($data['location_code']);
				}
				$this->template->assign(array('locations'=>$locations));**/


				$query = "SELECT location_code FROM (SELECT location_code,regist_date FROM fm_location_link where regist_date!='0000-00-00 00:00:00' order by regist_date desc) AS aliasTable GROUP BY  aliasTable.location_code order by aliasTable.regist_date desc limit 30";
				$query = $this->db->query($query);
				foreach($query->result_array() as $row){
					$row['title'] =  $this->locationmodel->get_location_name($row['location_code']);
					if(trim($row['title']))$last_locations[] = $row;
				}
				$socialcpcancelar = $this->goodsmodel->get_goods_socialcpcancel($no);
				if( $goods['socialcp_cancel_type'] !='payoption' ) {
					$goods['socialcp_cancel_day0'] = $socialcpcancelar[0]['socialcp_cancel_day'];
				}else{
					foreach($socialcpcancelar as $socialcpcancel) {
							$socialcpcancels[] = $socialcpcancel;
					}
					$this->template->assign(array('socialcpcancels'=>$socialcpcancels));
				}

			}

			//
			if	($goods['coupon_serial_type'] == 'n'){
				$coupon_serial_data	= $this->goodsmodel->get_outcoupon_list($goods['goods_seq']);
				if	($coupon_serial_data)foreach($coupon_serial_data as $k => $coupon_data){
					$coupon_serial_tcnt++;
					if	($coupon_serial_tcnt > 1) $coupon_serial_str	.= ',';
					$coupon_serial_str	.= $coupon_data['coupon_serial'].'|a|'. $coupon_data['export_code'];

					if	($coupon_data['export_code'])	$coupon_serial_ecnt++;
				}

				$goods['coupon_serial_tcnt']	= $coupon_serial_tcnt;
				$goods['coupon_serial_ecnt']	= $coupon_serial_ecnt;
				$goods['coupon_serial_str']		= $coupon_serial_str;
			}

			$this->template->assign(array('default_reserve_percent'=>$default_reserve_percent));
			$this->template->assign(array('categories'=>$categories));
			$this->template->assign(array('brands'=>$brands));
			$this->template->assign(array('locations'=>$locations));
			$this->template->assign(array('goods'=>$goods));
			$this->template->assign(array('options'=>$options));
			$this->template->assign(array('isAddr'=>$isAddr));
			$this->template->assign('opts_loop',$options);
			$this->template->assign(array('icons'=>$icons));
			$this->template->assign(array('suboptions'=>$suboptions));
			$this->template->assign(array('totsuboptionrowcnt'=>$totsuboptionrowcnt));
			$this->template->assign('sopts_loop',$suboptions);
			$this->template->assign(array('inputs'=>$inputs));
			$this->template->assign(array('images'=>$images));
			$this->template->assign(array('delivery'=>$delivery));
			$this->template->assign(array('service_code'=>$this->config_system['service']['code']));


			###
			$sql = "SELECT
						distinct A.*, B.*
					FROM
						fm_goods_relation A
						LEFT JOIN
						(SELECT
							g.goods_seq, g.goods_name, o.price
						FROM
							fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y') B ON A.relation_goods_seq = B.goods_seq
					WHERE
						A.goods_seq = '{$no}'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$relation[] = $row;
			}
			if($relation) $this->template->assign('relation',$relation);			
		}
	
		$this->load->model('goodsmodel');
		$frequentlyoptlist = $this->goodsmodel->frequentlygoods('opt',$goods_seq,defined('SOCIALCPUSE'));
		$frequentlysublist = $this->goodsmodel->frequentlygoods('sub',$goods_seq,defined('SOCIALCPUSE'));
		$frequentlyinplist = $this->goodsmodel->frequentlygoods('inp',$goods_seq,defined('SOCIALCPUSE'));
		$this->template->assign(array('frequentlyoptlist'=>$frequentlyoptlist));
		$this->template->assign(array('frequentlysublist'=>$frequentlysublist));
		$this->template->assign(array('frequentlyinplist'=>$frequentlyinplist));
		
		//상품코드양식 정보
		$this->load->helper("goods");
		$gdtypearray = array("goodsaddinfo","goodsoption","goodssuboption");
		foreach($gdtypearray as $gdtype){
			unset($goodscode);
			if($gdtype == 'goodsaddinfo' ){
				$codeqry = "select * from fm_goods_code_form  where label_type ='".$gdtype."' and base_type != '1' order by label_type, sort_seq";
			}else{
				$codeqry = "select * from fm_goods_code_form  where label_type ='".$gdtype."'  order by label_type, sort_seq";
			}

			//추가정보의 모델명추출
			$defaultadditionsar = array("모델명","브랜드","제조사","원산지");//model, brand, manufacture, orgin
			$codequery = $this->db->query($codeqry);
			$code_arr = $codequery -> result_array();
			foreach ($code_arr as $code_datarow){
				$code_datarow['label_write'] = get_labelitem_type($code_datarow,$goods,'');
				$i= 0;
				if($gdtype != 'goodsaddinfo' ){
					$label_value_ar = explode("|", $code_datarow['label_value']);
					$label_code_ar = explode("|", $code_datarow['label_code']);
					$label_default_ar = explode("|", $code_datarow['label_default']);
					foreach($label_code_ar as $code) {if(empty($code))continue;

						$codear['code'] = $code;
						$codear['value'] = $label_value_ar[$i];
						$codear['default'] = $label_default_ar[$i];
						$code_datarow['label_code_ar'][] = $codear;
						$i++;
					}
				}
				if( in_array(trim($code_datarow['label_title']), $defaultadditionsar,true) ){
					$code_datarow['label_title'] = $code_datarow['label_title'].' [코드]';
				}
				$goodscode[] = $code_datarow;
			}
			$this->template->assign($gdtype.'loop', $goodscode);
		}

		if($goods['videotmpcode']){
			$this->session->set_userdata('videotmpcode',$goods['videotmpcode']);
		}else{
			$videotmpcode = substr(microtime(), 2, 8);
			$this->session->set_userdata('videotmpcode',$videotmpcode);
		}
		$this->template->assign('videotmpcode',$this->session->userdata('videotmpcode'));

		//상품별 결제수단
		$possible_pay = explode(",", $goods["possible_pay"]);

		$possible_mobile_pay = explode(",", $goods["possible_mobile_pay"]);
		$bank = $payment = $escrow = "";
		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
			if( $v['accountUse'] == 'y' ){
				$payment['bank'] = true;
				$payment_check['bank'] = in_array('bank', $possible_pay);
			}

		}
		if( $this->config_system['pgCompany'] ){
			$payment_gateway = config_load($this->config_system['pgCompany']);
			$payment_gateway['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');

			foreach($payment_gateway['arrKcpCardCompany'] as $k => $v){
				$payment_gateway['arrCardCompany'][$v['codecd']]=$v['value'];
			}

			if(isset($payment_gateway['payment'])) foreach($payment_gateway['payment'] as $k => $v){
				$payment[$v] = true;
			}

			foreach($possible_pay as $key => $value){
				if(substr($value, 0, 7) != "escrow_"){
					if(!in_array($value, array_keys($payment))){
						unset($possible_pay[$key]);
					}
				}
			}

			$possible_pay = array_values($possible_pay);

			$pg_var = "payment";
			$escrowpg_var = "escrow";
			$escrowAccountLimit ='escrowAccountLimit';
			$escrowVirtualLimit ='escrowVirtualLimit';

			if(isset($payment_gateway[$pg_var])) foreach($payment_gateway[$pg_var] as $k => $v){
				$payment[$v] = true;
				$payment_check[$v] = in_array($v, $possible_pay);
			}

			if(isset($payment_gateway[$escrowpg_var])) foreach($payment_gateway[$escrowpg_var] as $k => $v){
				if($v == 'account'){
					$escrow[$v] = true;
					$escrow_check[$v] = in_array("escrow_".$v, $possible_pay);
				}

				if($v == 'virtual'){
					$escrow[$v] = true;
					$escrow_check[$v] = in_array("escrow_".$v, $possible_pay);
				}
			}

			foreach($possible_pay as $key => $value){
				if(substr($value, 0, 7) == "escrow_"){
					if(!in_array(str_replace("escrow_", "", $value), array_keys($escrow))){
						unset($possible_pay[$key]);
					}
				}
			}
			$possible_pay = array_values($possible_pay);

			$pg_var = "mobilePayment";
			$escrowpg_var = "mobileEscrow";
			$escrowAccountLimit ='mobileescrowAccountLimit';
			$escrowVirtualLimit ='mobileescrowVirtualLimit';


			if($arr) foreach(config_load('bank') as $k => $v){
				list($tmp) = code_load('bankCode',$v['bank']);
				$v['bank'] = $tmp['value'];
				$bank[] = $v;
				if( $v['accountUse'] == 'y' ){
					$mobile_payment['bank'] = true;
					$mobile_payment_check['bank'] = in_array('bank', $possible_mobile_pay);
				}
			}

			if(isset($payment_gateway[$pg_var])){
				foreach($payment_gateway[$pg_var] as $k => $v){
					$mobile_payment[$v] = true;
					$mobile_payment_check[$v] = in_array($v, $possible_mobile_pay);
				}
			}


			foreach($possible_mobile_pay as $key => $value){
				if(substr($value, 0, 7) != "escrow_"){
					if(!in_array($value, array_keys($mobile_payment))){
						unset($possible_mobile_pay[$key]);
					}
				}
			}
			$possible_mobile_pay = array_values($possible_mobile_pay);

			if(isset($payment_gateway[$escrowpg_var])) foreach($payment_gateway[$escrowpg_var] as $k => $v){
				if($v == 'account'){
					$mobile_escrow[$v] = true;
					$mobile_escrow_check[$v] = in_array("escrow_".$v, $possible_mobile_pay);
				}

				if($v == 'virtual'){
					$mobile_escrow[$v] = true;
					$mobile_escrow_check[$v] = in_array("escrow_".$v, $possible_mobile_pay);
				}
			}

			foreach($possible_mobile_pay as $key => $value){
				if(substr($value, 0, 7) == "escrow_"){
					if(!in_array(str_replace("escrow_", "", $value), array_keys($mobile_escrow))){
						unset($possible_mobile_pay[$key]);
					}
				}
			}
			$possible_mobile_pay = array_values($possible_mobile_pay);

		}
		
		//결제수단 치환
		$goods["possible_pay"] = join(",", $possible_pay);
		if($goods["possible_pay"]){
			$possible_pay_str = $this->goodsmodel->get_possible_pay_text($goods["possible_pay"]);
		}

		$goods["possible_mobile_pay_str"] = join(",", $possible_mobile_pay);
		if($goods["possible_mobile_pay_str"]){
			$possible_mobile_pay_str = $this->goodsmodel->get_possible_pay_text($goods["possible_mobile_pay"]);
		}

		$this->template->assign('possible_pay_str',$possible_pay_str);
		$this->template->assign('possible_mobile_pay_str',$possible_mobile_pay_str);
		$this->template->assign('payment',$payment);
		$this->template->assign('escrow',$escrow);
		$this->template->assign('payment_check',$payment_check);
		$this->template->assign('escrow_check',$escrow_check);
		$this->template->assign('mobile_payment',$mobile_payment);
		$this->template->assign('mobile_escrow',$mobile_escrow);
		$this->template->assign('mobile_payment_check',$mobile_payment_check);
		$this->template->assign('mobile_escrow_check',$mobile_escrow_check);

		//동영상관리
		$this->load->model('videofiles');
		unset($videosc);
		$videosc['tmpcode']= $this->session->userdata('videotmpcode');
		$videosc['upkind']= 'goods';
		$videosc['type']= 'image';
		$videoimage = $this->videofiles->get_data($videosc);//debug_var($videoimage);
		if($videoimage) $this->template->assign('videoimage',$videoimage);

		unset($videosc);
		$videosc['tmpcode']= $this->session->userdata('videotmpcode');
		$videosc['upkind']= 'goods';
		$videosc['type']= 'contents';
		$videosc['orderby']= 'sort ';
		$videosc['sort']= 'asc, seq desc ';
		$goodsvideofiles = $this->videofiles->videofiles_list_all($videosc);//debug_var($goodsvideofiles);
		if($goodsvideofiles['result']) $this->template->assign('goodsvideofiles',$goodsvideofiles['result']);

		###
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		$point_text = "";
		if($reserves['point_use']=='Y'){
			switch($reserves['default_point_type']){
				case "per":
					$point_text = "※ 지급 포인트(P) ".$reserves['default_point_percent']."%";
					break;
				case "app":
					$point_text = "※ 지급 포인트(P) ".number_format($reserves['default_point_app'])."원당 ".$reserves['default_point']."포인트";
					break;
				default :
					$point_text = "";
					break;
			}
		}else{
			$point_text = "※ 지급 포인트(P) 없음";
		}
		$this->template->assign(array('point_text'=>$point_text));

		//상품코드 설정여부
		$gdtypearray = array("goodsaddinfo","goodsoption","goodssuboption");
		$goodscodesettingview='';
		foreach($gdtypearray as $gdtype){
			unset($goodscode);
			$qry = "select * from fm_goods_code_form  where label_type ='".$gdtype."'  and codesetting=1 order by sort_seq";
			$query = $this->db->query($qry);
			$user_arr = $query -> result_array();
			foreach ($user_arr as $datarow){
				$goodscodesettingview .= $datarow['label_title'].' + ';
			}
		}
		$this->template->assign('goodscodesettingview',$goodscodesettingview);

		###
		$tmp = config_load('goodsImageSize');
		foreach($tmp as $k=>$v){
			if(substr($k,0,4)=='list'){
				$v['key'] = $k;
				$r_img_size[] = $v;
			}
		}		

		// 상품개별 재고 설정로드
		if($goods['runout_policy']){
			$cfg_runout['runout'] = $goods['runout_policy'];
			$cfg_runout['ableStockLimit'] = $goods['able_stock_limit'];
		}else{
			$cfg_runout['runout'] = $cfg_order['runout'];
			$cfg_runout['ableStockLimit'] = $cfg_order['ableStockLimit'];
		}
		$this->template->assign($cfg_runout);
		$icon_loop = code_load('goodsIcon');
		$this->template->assign('icon_default',$icon_loop[0]);
		
		// 회원등급세트		
		if(!$goods['sale_seq']) $goods['sale_seq'] = 1;
		$_GET['sale_seq'] = $goods['sale_seq'];
		$sale_data = $this->membermodel->get_member_sale_array($goods['sale_seq']);		
		$this->template->assign(array('sale_list'=>$sale_data['sale_list']));
		$this->template->assign(array('sale_data'=>$sale_data['data']));
		$this->template->assign(array('loop'=>$sale_data['loop'],'gcount'=>$sale_data['gcount']));
		$this->template->define('saleTpl', $this->skin.'/goods/member_sale_change.html');
		$sale_html = $this->template->fetch('saleTpl');
		$this->template->assign('sale_html',$sale_html);

		// 기본 배송 정책
		$arr = array('delivery','quick','direct');
		foreach($arr as $code){
			$scode = "shipping".$code;
			$data = config_load($scode);
			$deliveryCompany = array();
			if( isset($data['deliveryCompanyCode']) ){
				foreach( $data['deliveryCompanyCode'] as $deliveryCompanyCode ){
					$tmp = config_load('delivery_url',$deliveryCompanyCode);
					$deliveryCompany[] = $tmp[$deliveryCompanyCode]['company'];
				}
			}

			if(isset($data['deliveryCostPolicy'])){
				switch($data['deliveryCostPolicy']){
					case "free" : $result['price'] = "무료"; break;
					case "pay" : $result['price'] = "유료 " . number_format($data['payDeliveryCost'])." 원"; break;
					case "ifpay" : $result['price'] = "조건부 " . number_format($data['ifpayDeliveryCost']) ."원"; break;
				}
			}else{
				$result['price'] = "-";
			}
			$result['useYnMsg'] = ($data['useYn']=='y') ? "사용" : "미사용";
			if( isset($data['sigungu']) && $data['sigungu'][0]  ) $result['addpriceMsg'] = "설정함";
			else if( $code == 'delivery')  $result['addpriceMsg'] = "미설정";
			else $result['addpriceMsg'] = "-";

			if($data['useYn'] == 'y'){
				$cnt++;
			}

			$data['deliveryCompany'] = implode(',',$deliveryCompany);
			$data_providershipping[$code] = $data;

		}

		$data_providershipping['delivery_cnt'] = $cnt;
		$this->template->assign("data_providershipping",$data_providershipping);

		$arr = $result = "";
		$codes = code_load('internationalShipping');
		$internationalShipping_useYn = false;
		foreach($codes as $code){
			$arr = config_load('internationalShipping'.$code['codecd']);



			if($arr['company']){
				if($arr['useYn']=='y') $internationalShipping_useYn = true;
				$result[] = $arr;
			}
		}
		
		// 옵션 기본 노출 수량 적용
		$config_goods	= config_load('goods');				
		
		## 판매마켓 정보
		$this->load->model('openmarketmodel');
		$LINKAGE_SERVICE	= $this->openmarketmodel->chk_linkage_service();
		if	($LINKAGE_SERVICE){
			
			$linkage			= $this->openmarketmodel->get_linkage_config();			
			$malldata			= $this->openmarketmodel->get_linkage_mall();			
			$linkageOrigin		= $this->openmarketmodel->get_linkage_origin($linkage['linkage_id']);
			if	($no)	$goodsmall	= $this->openmarketmodel->get_linkage_goods_mall($no);
			
			if	($malldata)foreach($malldata as $k => $data){
				$mall[$data['mall_code']]	= $data;
			}
			if	($goodsmall){
				foreach($goodsmall as $k => $data){
					if	($mall[$data['mall_code']]){
						$mall[$data['mall_code']]['goods']	= 1;
						if	($gmall_cnt > 0)	$gmall_str	.= ', ';
						$gmall_str	.= $data['mall_name'];
						$gmall_cnt++;
					}
				}
				$goodsmall[0]['mallstr']	= $gmall_str;
				$goodsmall[0]['mallcnt']	= $gmall_cnt;
			}

			// 추가정보에 원산지 배열 추가
			if	($additions){
				foreach($additions as $a => $data_additions){
					$additions[$a]['linkageOrigin']	= $linkageOrigin;
				}
			}

			$this->template->assign('LINKAGE_SERVICE',	$LINKAGE_SERVICE);
			$this->template->assign('linkageOrigin',	$linkageOrigin);
			$this->template->assign('linkage',			$linkage);
			$this->template->assign('mall',				$mall);
			$this->template->assign('goodsmall',		$goodsmall);
		}		

		// 빅데이터 설정 추가
		$this->load->model('bigdatamodel');
		$this->load->model('usedmodel');
		$chks = $this->usedmodel->used_service_check('bigdata');
		$kinds				= $this->bigdatamodel->get_kind_array();
		$same_type_pattern	= array('category', 'brand', 'location', ',');
		$same_type_replace	= array('동일 카테고리', '동일 브랜드', '동일 지역', ', ');
		foreach($kinds as $kind => $text){
			$cfg			= config_load('bigdata_'. $kind);
			if	($cfg){
				$cfg['title']	= '이 상품을 ' . $text . ' 고객들이 '
								. '가장 많이 (' . $kinds[$cfg['tkind']] . ') 다른 상품';

				$cfg['text']	= '빅데이터 전용페이지 노출, ';
				if	($cfg['use_view_p'] != 'y' && $cfg['use_view_m'] != 'y'){
					$cfg['text']	.= '상품상세페이지 미노출';
				}else{
					$cfg['text']	.= '상품상세페이지 노출';
				}

				$cfg_bigdata[$kind]			= $cfg;
			}
		}
		$this->template->assign(array('chkBigdata'=>$chks['type']));
		$this->template->assign(array('cfg_bigdata'	=> $cfg_bigdata));
		$this->template->assign(array('additions'=>$additions));
		$this->template->assign('linkage',$linkage);
		$this->template->assign('mall',$mall);
		$this->template->assign('goodsmall',$goodsmall);
		$this->template->assign(array('config_goods' => $config_goods));
		$this->template->assign("internationalShipping_useYn",$internationalShipping_useYn);
		$this->template->assign("internationalShipping_loop",$result);
		$this->template->assign('defualt_sale_seq',$defualt_sale_seq[0]["sale_seq"]);
		$this->template->assign('query_string',$_GET['query_string']);
		$this->template->assign(array('r_img_size'=>$r_img_size));
		$this->template->assign('info_loop',$info_loop);
		$this->template->assign(array('default_reserve_percent'=>$default_reserve_percent));
		$this->template->assign(array('last_categories'=>$last_categories));
		$this->template->assign(array('last_brands'=>$last_brands));
		$this->template->assign(array('last_locations'=>$last_locations));
		$this->template->assign(array('limit_stock'=>$limit_stock));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->print_("tpl");
	}

	public function social_regist()
	{
		define('SOCIALCPUSE',true);
		$this->template->assign('socialcpuse',1);
		$this->regist();
	}

	public function icon(){
		//echo json_encode( code_load('goodsIcon') );
		$icon = code_load('goodsIcon');
		$i =0;
		foreach($icon as $k){
			$path = ROOTPATH."/data/icon/goods/".$k['codecd'].".gif";
			if(file_exists($path)) {
				$size = getimagesize($path);
				$loop[$i] = $k;
				$loop[$i]['width'] = $size[0];
				$loop[$i]['li_css'] = sprintf("width:%s;height:%s;", $size[0].'px', $size[1].'px');
				$i++;
			}
		}
		sort($loop);
		echo json_encode( $loop );
	}


	public function select(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));

		$query = $this->db->query("select *, if(CURRENT_TIMESTAMP() between start_date and end_date,'진행 중',if(end_date < CURRENT_TIMESTAMP(),'종료','시작 전')) as status from fm_event where end_date >= CURRENT_TIMESTAMP() order by event_seq desc");
		$eventData = $query->result_array();
		$this->template->assign(array('eventData'=>$eventData));


		$query = $this->db->query("SELECT *,
		if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'종료','시작 전')) as status
		FROM fm_gift
		WHERE gift_gb = 'order' AND end_date >= current_date()
		ORDER BY gift_seq desc");

		$giftData = $query->result_array();
		$this->template->assign(array('giftData'=>$giftData));

		$this->load->model('goodsdisplay');
		$this->template->assign(array('auto_orders'	=> $this->goodsdisplay->auto_orders));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function select_list(){
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if(!isset($_GET['selectGoodsStatus']))$_GET['selectGoodsStatus'] = "";
		if(!isset($_GET['goodsView']))$_GET['goodsView'] = "";
		if(!isset($_GET['sort']))$_GET['sort'] = 0;
		if(!isset($_GET['page']))$_GET['page'] = 1;
		$page = $_GET['page'];
		$adminOrder = $_GET['adminOrder'];
		$adminshipping = $_GET['adminshipping'];

		$where = $subWhere = $whereStr = "";
		$bind = array();

		$arg_list = func_get_args();

		$subWhere = null;
		if( isset($_GET['selectCategory4']) &&  $_GET['selectCategory4'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectCategory4'];
		}else if( isset($_GET['selectCategory3']) && $_GET['selectCategory3'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectCategory3'];
		}else if( isset($_GET['selectCategory2']) && $_GET['selectCategory2'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectCategory2'];
		}else if( isset($_GET['selectCategory1']) && $_GET['selectCategory1'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectCategory1'];
		}
		if($subWhere) $where[] = "g.goods_seq in (select goods_seq from fm_category_link where ".$subWhere.")";

		$subWhere = null;
		if( isset($_GET['selectBrand4']) &&  $_GET['selectBrand4'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectBrand4'];
		}else if( isset($_GET['selectBrand3']) && $_GET['selectBrand3'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectBrand3'];
		}else if( isset($_GET['selectBrand2']) && $_GET['selectBrand2'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectBrand2'];
		}else if( isset($_GET['selectBrand1']) && $_GET['selectBrand1'] ){
			$subWhere = "category_code = ?";
			$bind[] = $_GET['selectBrand1'];
		}
		if($subWhere) $where[] = "g.goods_seq in (select goods_seq from fm_brand_link where ".$subWhere.")";

		$subWhere = null;
		if( isset($_GET['selectLocation4']) &&  $_GET['selectLocation4'] ){
			$subWhere = "location_code = ?";
			$bind[] = $_GET['selectLocation4'];
		}else if( isset($_GET['selectLocation3']) && $_GET['selectLocation3'] ){
			$subWhere = "location_code = ?";
			$bind[] = $_GET['selectLocation3'];
		}else if( isset($_GET['selectLocation2']) && $_GET['selectLocation2'] ){
			$subWhere = "location_code = ?";
			$bind[] = $_GET['selectLocation2'];
		}else if( isset($_GET['selectLocation1']) && $_GET['selectLocation1'] ){
			$subWhere = "location_code = ?";
			$bind[] = $_GET['selectLocation1'];
		}
		if($subWhere) $where[] = "g.goods_seq in (select goods_seq from fm_location_link where ".$subWhere.")";

		if($adminOrder == 'Y' || $adminshipping == 'Y' ){//관리자주문넣기시 쿠폰상품제외
			$where[] = "g.goods_kind != 'coupon' ";
		}

		if( isset($_GET['selectGoodsName']) && $_GET['selectGoodsName'] ){
			$search_text = trim(str_replace(" ","",addslashes($_GET['selectGoodsName'])));
			$where[] = "
			(
				REPLACE(g.goods_name,' ','') like '%{$search_text}%'
				or g.goods_seq = '{$search_text}'
				or g.goods_code like '%{$search_text}%'
				or REPLACE(g.summary,' ','') like '%{$search_text}%'
				or REPLACE(g.keyword,' ','') like '%{$search_text}%'
				or (
					 select group_concat(sc_b.title,sc_b.title_eng) from fm_brand sc_b
					 inner join fm_brand_link sc_b2
					 on sc_b.category_code=sc_b2.category_code
					 where sc_b2.goods_seq=g.goods_seq
				) like '%{$search_text}%'
			)
			";
		}
		if( isset($_GET['selectStartPrice']) && $_GET['selectStartPrice'] ){
			$where[] = "o.price >= ?";
			$bind[] = $_GET['selectStartPrice'];
		}
		if( isset($_GET['selectEndPrice']) && $_GET['selectEndPrice'] ){
			$where[] = "o.price <= ?";
			$bind[] = $_GET['selectEndPrice'];
		}

		if( $_GET['selectGoodsStatus'] ){
			$where[] = "g.goods_status in ('".implode("','",$_GET['selectGoodsStatus'])."')";

		}

		if( $_GET['selectGoodsView'] ){
			$where[] = "g.goods_view = ?";
			$bind[] = $_GET['selectGoodsView'];
		}else if( $_GET['goodsView'] ){
			$where[] = "g.goods_view = ?";
			$bind[] = $_GET['goodsView'];
		}

		//동영상
		if( $_GET['file_key_w'] ){
			$where[] = " ( file_key_w != '') ";// or file_key_w is not null
		}
		if( !empty($_GET['video_use']) && $_GET['video_use'] !="전체" ){
			$where[] = "video_use = '{$_GET['video_use']}' ";
		}
		if( $_GET['videototal'] ){
			$where[] = "videototal > 0 ";
		}

		// 관련상품
		if($_GET['relation_goods_seq']){
			if($_GET['selectGoodsRelationCategory']){
				$sql = "select category_code from fm_category_link where goods_seq = ? and link = 1";
				$query = $this->db->query($sql,$_GET['relation_goods_seq']);
				$tmp = $query->row_array();

				if($tmp) $where[] = "g.goods_seq in (select goods_seq from fm_category_link where category_code=?)";
				$bind[] = $tmp['category_code'];
			}

			if($_GET['selectGoodsRelationBrand']){
				$sql = "select category_code from fm_brand_link where goods_seq = ? and link = 1";
				$query = $this->db->query($sql,$_GET['relation_goods_seq']);
				$tmp = $query->row_array();

				if($tmp) $where[] = "g.goods_seq in (select goods_seq from fm_brand_link where category_code=?)";
				$bind[] = $tmp['category_code'];
			}

			if($_GET['selectGoodsRelationLocation']){
				$sql = "select location_code from fm_location_link where goods_seq = ? and link = 1";
				$query = $this->db->query($sql,$_GET['relation_goods_seq']);
				$tmp = $query->row_array();

				if($tmp) $where[] = "g.goods_seq in (select goods_seq from fm_location_link where location_code=?)";
				$bind[] = $tmp['location_code'];
			}
		}

		$sqlFromClause = "";
		$sqlWhereClause = "";
		$sqlOrderbyClause = "";
		$sqlGroupbyClause = "";

		if($_GET['auto_term'] || ($_GET['auto_start_date'] && $_GET['auto_end_date'])){
			if($_GET['auto_term_type']=='relative') {
				$auto_start_date = date('Y-m-d',strtotime("-{$_GET['auto_term']} day"));
				$auto_end_date = date('Y-m-d');
			}else{
				$auto_start_date = $_GET['auto_start_date'];
				$auto_end_date = $_GET['auto_end_date'];
			}

			switch($_GET['auto_order'])
			{
				case "deposit":
				case "best":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='deposit' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.purchase_ea desc";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "deposit_price":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='deposit_price' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.purchase_ea desc";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "popular":
				case "view":
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.page_view desc";
					$sqlFromClause .= "left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='view' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "review":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='review' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.review_count desc";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "cart":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='cart' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlGroupbyClause = " group by g.goods_seq";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc";
				break;
				case "wish":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='wish' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlGroupbyClause = " group by g.goods_seq";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc";
				break;
				case "newly":
				default:
					if($auto_start_date && $auto_end_date){
						$where[] = "g.regist_date between ? and ?";
						$bind[] = $auto_start_date.' 00:00:00';
						$bind[] = $auto_end_date.' 23:59:59';
					}
					$sqlOrderbyClause =" order by g.regist_date desc, g.goods_seq desc";
				break;
				case "discount":
					$sqlWhereClause .= " and o.consumer_price>0 ";
					$sqlOrderbyClause =" order by o.price/o.consumer_price asc, o.price desc";
				break;
			}
		}else{
			$sqlOrderbyClause =" order by g.regist_date desc, g.goods_seq desc";
		}

		$query = "
		from fm_goods g
		inner join fm_goods_option o on o.goods_seq=g.goods_seq
		{$sqlFromClause}
		";

		if(!empty($_GET['selectEvent']) || !empty($_GET['selectEventBenefits'])){
			$query .= "
				left join fm_event_choice e on g.goods_seq = e.goods_seq
			";

			$where[] = "e.event_seq = ?";
			$bind[] = $_GET['selectEvent'];

			if(!empty($_GET['selectEventBenefits'])){
				$where[] = "e.event_benefits_seq = ?";
				$bind[] = $_GET['selectEventBenefits'];
			}
		}

		if(!empty($_GET['selectGift'])){
			$query .= "
				left join fm_gift_choice e on g.goods_seq = e.goods_seq
			";

			$where[] = "e.gift_seq = ?";
			$bind[] = $_GET['selectGift'];
		}

		if($where){
			$whereStr = ' and '.implode(' and ',$where);
		}

		$query .= " where g.goods_type='goods' and o.default_option ='y' ".$whereStr.$sqlWhereClause;
		$query .= $sqlGroupbyClause.$sqlOrderbyClause;

		if($_GET['return_goods_seq']){
			$limit	= (int)($_GET['count_w']*$_GET['count_h']);
			$selectStr = "select g.goods_seq,g.goods_name,g.goods_type,o.price , g.event_st_num
			,(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=1 and image_type ='thumbCart' limit 1) as image
			,(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=2 and image_type ='thumbCart' limit 1) as image2
			";
			$query = $selectStr.$query. " limit {$limit}";
			$result = $this->db->query($query,$bind);
			$result = $result->result_array();
			echo json_encode($result);
			exit;
		}else{
			$selectStr = "select g.goods_seq,g.goods_name,g.goods_type,o.price, g.event_st_num
			,(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=1 and image_type ='thumbCart' limit 1) as image
			,(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=2 and image_type ='thumbCart' limit 1) as image2
			";
			$query = $selectStr.$query;

			$result = select_page(10,$page,10,$query,$bind);
			$result['page']['querystring'] = get_args_list();

			$this->template->assign('adminOrder',$adminOrder);
			$this->template->assign($result);
			$this->template->define(array('tpl'=>$file_path));
			$this->template->print_("tpl");
		}
	}


	public function gift(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 600;
		$this->template->assign(array('containerHeight'=>$containerHeight));

		$query = $this->db->query("select *, if(CURRENT_TIMESTAMP() between start_date and end_date,'진행 중',if(end_date < CURRENT_TIMESTAMP(),'종료','시작 전')) as status from fm_event where end_date >= CURRENT_TIMESTAMP() order by event_seq desc");
		$eventData = $query->result_array();
		$this->template->assign(array('eventData'=>$eventData));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function gift_list(){
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if(!isset($_GET['goodsStatus']))$_GET['goodsStatus'] = "";
		if(!isset($_GET['goodsView']))$_GET['goodsView'] = "";
		if(!isset($_GET['sort']))$_GET['sort'] = 0;
		if(!isset($_GET['page']))$_GET['page'] = 1;
		$page = $_GET['page'];
		$adminOrder = $_GET['adminOrder'];

		$where = $subWhere = $whereStr = "";
		$bind = array();

		$arg_list = func_get_args();

		if($subWhere){
			$where[] = "g.goods_seq in (select goods_seq from fm_category_link where ".$subWhere.")";
		}
		if( isset($_GET['selectGoodsName']) && $_GET['selectGoodsName'] ){
			//$where[] = "g.goods_name like ?";
			//$bind[] = '%'.$_GET['selectGoodsName'].'%';
			$where[] = " (g.goods_name like '%".$_GET['selectGoodsName']."%' or g.goods_code like '%".$_GET['selectGoodsName']."%' ) ";
		}

		if( isset($_GET['selectStartconsumerPrice']) && $_GET['selectStartconsumerPrice'] ){
			$where[] = "o.consumer_price >= ?";
			$bind[] = $_GET['selectStartconsumerPrice'];
		}
		if( isset($_GET['selectEndconsumerPrice']) && $_GET['selectEndconsumerPrice'] ){
			$where[] = "o.consumer_price <= ?";
			$bind[] = $_GET['selectEndconsumerPrice'];
		}

		if( $_GET['goodsStatus'] ){
			$where[] = "g.goods_status = ?";
			$bind[] = $_GET['goodsStatus'];
		}

		if( $_GET['goodsView'] ){
			$where[] = "g.goods_view = ?";
			$bind[] = $_GET['goodsView'];
		}

		$arrSort = array('g.goods_seq desc','g.goods_seq asc','g.purchase_ea desc','g.purchase_ea asc','g.page_view desc','g.page_view asc','g.review_count desc','g.review_count asc');
		$sortStr = " order by " .$arrSort[$_GET['sort']];

		$query = "select g.goods_seq,g.goods_name,g.goods_type,o.price, o.consumer_price
		from fm_goods g
		inner join fm_goods_option o on o.goods_seq=g.goods_seq";

		if(!empty($_GET['selectEvent']) || !empty($_GET['selectEventBenefits'])){
			$query .= "
				left join fm_event_choice e on g.goods_seq = e.goods_seq
			";

			$where[] = "e.event_seq = ?";
			$bind[] = $_GET['selectEventBenefits'];

			if(!empty($_GET['selectEventBenefits'])){
				$where[] = "e.event_benefits_seq = ?";
				$bind[] = $_GET['selectEventBenefits'];
			}
		}

		if($where){
			$whereStr = ' and '.implode(' and ',$where);
		}

		$query .= "
		where
			g.goods_type='gift' and o.default_option ='y'".$whereStr.$sortStr;
		$result = select_page(10,$page,10,$query,$bind);
		$result['page']['querystring'] = get_args_list();

		$this->template->assign('adminOrder',$adminOrder);
		$this->template->assign($result);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function popup_image()
	{
		$file_path	= $this->template_path();
		
		//이미지설정폼   
		$this->template->define(array('goods_resize_form' => $this->skin.'/goods/_goods_resize_setting.html'));

		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//상품이미지 등록시 일괄등록@2015-03-02 
	public function popup_image_multi()
	{
		$file_path	= $this->template_path();

		//이미지설정폼   
		$this->template->assign('multi',true);
		$this->template->define(array('goods_resize_form' => $this->skin.'/goods/_goods_resize_setting.html'));

		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//동영상등록새창
	public function popup_video()
	{
		$this->load->model('videofiles');
		$this->load->helper('readurl');

		if($_POST['file_key_W']) {

			$file_key_w = $_POST['file_key_W'];//웹 인코딩 코드
		}

		if($_POST['file_key_I']) {
			$file_key_i = $_POST['file_key_I'];//스마트폰 인코딩 코드
		}

		/* 상품등록 파라미터 검증*/
		$goodsSeq = ($_POST['no'])?(int) $_POST['no']:($_GET['no']);
		$uptype = ($_POST['uptype'])?$_POST['uptype']:$_GET['uptype'];

		if(!$this->session->userdata('videotmpcode')){
			$videotmpcode = substr(microtime(), 2, 8);
			$this->session->set_userdata('videotmpcode',$videotmpcode);
		}

		if($file_key_w || $file_key_i) {

			if($_POST['uptype']=='image' && $goodsSeq ) {
				$goods['file_key_w'] = $file_key_w;//웹 인코딩 코드
				$goods['file_key_i'] = $file_key_i;//웹 인코딩 코드
				$goods['videotmpcode'] = $this->session->userdata('videotmpcode');//코드
				$this->db->where('goods_seq', $goodsSeq);
				$this->db->update('fm_goods', $goods);
			}

			$videofiles['parentseq']			= ($goodsSeq)?$goodsSeq:'';
			$videofiles['upkind']					= 'goods';
			$videofiles['type']						= $_POST['uptype'];
			$videofiles['tmpcode']				= $this->session->userdata('videotmpcode');//
			$videofiles['mbseq']					= $this->managerInfo['manager_seq'];//
			$videofiles['r_date']					= date("Y-m-d H:i:s");
			$videofiles['file_key_w']			= $file_key_w;//웹 인코딩 코드
			$videofiles['file_key_i']				= $file_key_i;//웹 인코딩 코드
			$videofiles['memo']					= '';
			$videoinforesult = readurl(uccdomain('fileinfo',$file_key_w));
			if($videoinforesult){
				$videoinfoarr = xml2array($videoinforesult);
				$videofiles['playtime']		 = ($videoinfoarr['class']['playtime'])?$videoinfoarr['class']['playtime']:'';
			}
			$videofiles['memo']				= $_POST['memo'];//
			$videofiles['encoding_speed']	= ($_POST['encoding_speed'])?$_POST['encoding_speed']:400;
			$videofiles['encoding_screen']	= ($_POST['encoding_screen'])?str_replace("|","X",$_POST['encoding_screen']):'400X300';

			$videoseq = $this->videofiles->videofiles_write($videofiles);
		}

		$uccdomainembedsrc = uccdomain('fileswf',$file_key_w);
		$pageurl = uccdomain('fileurl',$file_key_w);
		$this->template->assign("uccdomainembedsrc",$uccdomainembedsrc);
		$this->template->assign("pageurl",$pageurl);
		$this->template->assign("file_key_w",$file_key_w);
		$this->template->assign("file_key_i",$file_key_i);
		$this->template->assign("videoseq",$videoseq);
		$this->template->assign("uptype",$uptype);
		$this->template->assign("goodsSeq",$goodsSeq);
		$this->template->assign("encoding_screen",$_POST['encoding_screen']);
		$this->template->assign("encoding_speed",$_POST['encoding_speed']);

		$this->template->assign("r_date",date("Y-m-d H:i:s"));

		$file_path	= $this->template_path();
		//동영상연결(기본 파일찾기)
		$this->template->assign("uccdomain",uccdomain());
		if( $_POST['file_key_W'] || $_POST['file_key_I']) {
			$this->template->assign("videook",true);
		}else{
			$this->template->assign("videook",false);
		}

		if( $_POST['error']) {
			$this->template->assign("videoerror",true);
			$this->template->assign("error",$_POST['error']);
		}else{
			$this->template->assign("videoerror",false);
		}
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 동영상 URL 화면 */
	public function video_url(){
		$this->template->assign("realvideourl",$_GET['realvideourl']);
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}


	public function download_write(){
		$this->admin_menu();
		$this->tempate_modules();

		$this->load->model('excelgoodsmodel');
		$itemList 	= $this->excelgoodsmodel->itemList;
		$this->template->assign('itemList',$itemList);
		$requireds 	= $this->excelgoodsmodel->requireds;
		$this->template->assign('requireds',$requireds);

		$data = get_data("fm_exceldownload",array("gb"=>"GOODS","provider_seq"=>'1'));
		$item = $data ? explode("|",$data[0]['item']) : null;
		$diff_item = array_diff($requireds, $item);
		if( isset($item) && count($item)>1 ){
			/* DB에 필수 항목 없을 경우 다운로드 항목 배열에 자동으로 추가 leewh 2014-12-01 */
			if ($diff_item) {
				$item = array_merge($item, $diff_item);
			}
			$this->template->assign('items',$item);
		}else{
			$this->template->assign('items',$requireds);
		}

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function _benefits($goods_seq)
	{
		$this->load->model('goodsmodel');
		$this->load->model('membermodel');
		$this->load->model('configsalemodel');
		$this->load->model('couponmodel');

		if($goods_seq){

			$cfg_reserve = ($this->reserves)?$this->reserves:config_load('reserve');

			$goods = $this->goodsmodel->get_goods($goods_seq);
			$options = $this->goodsmodel->get_goods_option($goods_seq);
			foreach($options as $key => $option)
			{
				if($option['default_option'] == 'y'){
					if( $goods['reserve_policy'] == 'shop' ){
						$result['reserve_rate'] = $cfg_reserve['default_reserve_percent'];
						$result['reserve_unit'] = 'percent';
					}
					$result['reserve'] = $this->goodsmodel->get_reserve_with_policy($goods['reserve_policy'],$option['price'],$cfg_reserve['default_reserve_percent'],$option['reserve']);
					$result = $option;
				}
			}
			$result['price_rate'] = ceil(($result['consumer_price']-$result['price']) / $result['consumer_price'] * 100);

			$categorys = $this->goodsmodel->get_goods_category($goods_seq);
			foreach($categorys as $data_category){
				$r_category_code[] = $data_category['category_code'];
			}
			// 이벤트
			$result['event'] = $this->goodsmodel->get_event_price($result['price'], $goods_seq, $r_category_code, $result['consumer_price'], $goods);
			// 회원등급
			$result['member_group'] = $this->membermodel->get_group_for_goods($result['price'],$goods_seq,$r_category_code);
			// 모바일
			$result['systemmobiles'] = $this->configsalemodel->get_mobile_sale_for_goods($result['price']);
			//쿠폰
			$r_coupon = $this->couponmodel->get_able_download_list(date('Y-m-d'),'',$goods_seq,$r_category_code,$result['price']);
			foreach($r_coupon as $data_coupon){
				if($max < $data_coupon['goods_sale']) {
					$max = $data_coupon['goods_sale'];
					$result['max_coupon'] = $data_coupon;
				}
			}
			// 배송정보 가져오기
			$result['delivery'] = $this->goodsmodel->get_goods_delivery($goods);
			// 좋아요 할인
			$result['systemfblikes'] = $this->configsalemodel->get_fblike_sale_for_goods($result['price']);
			// 무이자 할인
			$pg = config_load($this->config_system['pgCompany']);
			if($pg['nonInterestTerms'] == 'manual'){
				$tmp = code_load($this->config_system['pgCompany'].'CardCompanyCode');
				foreach($tmp as $company_code){
					$r_card_company[$company_code['codecd']] = $company_code['value'];
				}
				if($pg['pcCardCompanyCode']) foreach($pg['pcCardCompanyCode'] as $key => $code){
					$result['nointerest'][] = $r_card_company[$code] . " " . $pg['pcCardCompanyTerms'][$key];
				}
			}

			/* 인기지수 : 장바구니 */
			$query = $this->db->query("select count(*) as cnt from fm_cart where goods_seq='{$goods_seq}' and member_seq>0 group by member_seq");
			$cntrow = $query->result_array();
			$result['popularity'][] = array(
				'desc'		=> '<b>장바구니</b>에 담고 있는 회원(현재기준)',
				'value'		=> number_format($cntrow[0]['cnt']) ,
				'postfix'	=> '명',
				'link'		=> '../member/catalog?goods_seq_cond=cart&goods_seq='.$goods_seq,
			);

			/* 인기지수 : 위시리스트 */
			$query = $this->db->query("select count(*) as cnt from fm_goods_wish where goods_seq='{$goods_seq}' and member_seq>0 group by member_seq");
			$cntrow = $query->result_array();
			$result['popularity'][] = array(
				'desc'		=> '<b>위시리스트</b>에 담고 있는 회원(현재기준)',
				'value'		=> number_format($cntrow[0]['cnt']) ,
				'postfix'	=> '명',
				'link'		=> '../member/catalog?goods_seq_cond=wish&goods_seq='.$goods_seq,
			);

			/* 인기지수 : 좋아요 */
			$query = $this->db->query("select count(*) as cnt from fm_goods_fblike where goods_seq='{$goods_seq}' and member_seq>0 group by member_seq");
			$cntrow = $query->result_array();
			$result['popularity'][] = array(
				'desc'		=> '<b>상품을 좋아요</b> 한 회원(누적)',
				'value'		=> number_format($cntrow[0]['cnt']) ,
				'postfix'	=> '명',
				'link'		=> '../member/catalog?goods_seq_cond=fblike&goods_seq='.$goods_seq,
			);

			/* 인기지수 : 재입고알림 */
			$query = $this->db->query("select count(*) as cnt from fm_goods_restock_notify  where goods_seq='{$goods_seq}' and member_seq>0 and notify_status='none' group by member_seq");
			$cntrow = $query->result_array();
			$result['popularity'][] = array(
				'desc'		=> '<b>재입고알림</b>을 요청한 회원(미통보기준)',
				'value'		=> number_format($cntrow[0]['cnt']) ,
				'postfix'	=> '명',
				'link'		=> "../goods/restock_notify_catalog?keyword={$goods_seq}&notifyStatus[]=none",
			);

			/* 인기지수 : 상품리뷰 */
			$query = $this->db->query("select count(*) as cnt from fm_goods_review where goods_seq='{$goods_seq}'");
			$cntrow = $query->result_array();
			$result['popularity'][] = array(
				'desc'		=> '<b>상품 리뷰</b>(누적)',
				'value'		=> number_format($cntrow[0]['cnt']),
				'postfix'	=> '회',
				'link'		=> "../board/board?id=goods_review&goods_seq={$goods_seq}",
			);

			$result['goodsbenefits'] = $goods;
			$result['goods_seq'] = $goods_seq;

			return $result;
		}
	}

	public function benefits()
	{
		$goods_seq = (int) $_GET['goods_seq'];
		$result = $this -> _benefits($goods_seq);
		if($_GET['socialcpuse']){
			$shippingdelivery = config_load("shippingdelivery");
			$this->template->assign('deliveryCostPolicy',$shippingdelivery['deliveryCostPolicy']);

			define('SOCIALCPUSE',true);
			$this->template->assign('socialcpuse',1);
		}

		$this->load->model('eventmodel');
		//단독이벤트추출
		//사은품이벤트추출
		$giftloop = $this->eventmodel->get_gift_event_all($goods_seq);
		$this->template->assign('gifloop',$giftloop);


		// 좋아요 버튼
		$this->load->library('snssocial');
		$this->template->assign('APP_USE',			$this->__APP_USE__);
		$this->template->assign('APP_ID',				$this->__APP_ID__);
		$this->template->assign('APP_SECRET',		$this->__APP_SECRET__);
		$this->template->assign('APP_PAGE',			$this->__APP_PAGE__);
		$this->template->assign('APP_NAMES',		$this->__APP_NAMES__);
		$this->template->assign('likeurl',				$this->likeurl);

		$file_path	= $this->template_path();
		$this->template->assign($result);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function benefits_info()
	{
		$goods_seq = (int) $_GET['goods_seq'];
		$result = $this -> _benefits($goods_seq);
		if($_GET['socialcpuse']){
			define('SOCIALCPUSE',true);
			$this->template->assign('socialcpuse',1);
		}

		$file_path	= $this->template_path();
		$this->template->assign($result);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function goods_status_images_setting(){
		$iconDirectoryPath = "/data/icon/goods_status/";
		$data = code_load('goodsStatusImage');
		$goodsStatusImage = array();
		foreach($data as $row){
			$goodsStatusImage[$row['codecd']] = $row['value'];
		}

		$this->template->assign(array(
			'goodsStatusImage' => $goodsStatusImage,
			'iconDirectoryPath' => $iconDirectoryPath,
		));

		if($_GET['type']) $this->template->assign('type', $_GET['type']);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function restock_notify_catalog(){

		if( !$this->isplusfreenot ){
			pageBack('무료몰Plus+에서는 [재입고알림 요청 상품 리스트]를 지원하지 않는 기능입니다.\n프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 [재입고알림 요청 상품 리스트]을 이용 가능합니다.');
			exit;
		}

		$this->admin_menu();
		$this->tempate_modules();

		$cfg_order = config_load('order');

		$this->load->model('goodsmodel');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('ordermodel');

		### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(isset($auth)) $this->template->assign('auth',$auth);

		### SEARCH
		$_GET['orderby'] = ($_GET['orderby']) ? $_GET['orderby']:'restock_notify_seq';
		$_GET['sort']	 = ($_GET['sort']) ? $_GET['sort']:'desc';
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):'10';
		$sc = $_GET;

		### LIST
		$loop = $this->goodsmodel->restock_notify_list($sc);

		### PAGE & DATA
		$query = "select count(*) cnt from fm_goods_restock_notify A LEFT JOIN fm_goods_option B ON A.goods_seq = B.goods_seq LEFT JOIN fm_goods_supply C ON A.goods_seq = C.goods_seq AND B.option_seq = C.option_seq where B.default_option = 'y'";
		$query = $this->db->query($query);
		$data = $query->row_array();
		$loop['page']['all_count'] = $data['cnt'];

		$idx = 0;
		foreach($loop['record'] as $k => $datarow){
			$idx++;
			$datarow['goods_view_text']	= $datarow['goods_view']=='look' ? "<span style='color:blue'>노출</span>" : "<span style='color:red'>미노출</span>";
			$datarow['number']		= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			//$datarow['catename']	= $this->categorymodel->get_category_name($datarow['category_code']);
			if($cfg_order['ableStockStep'] == 15) $reservation = $datarow['reservation15'];
			if($cfg_order['ableStockStep'] == 25) $reservation = $datarow['reservation25'];
			$datarow['rstock'] = $datarow['stock'] - $reservation;

			if($datarow['goods_status']=="runout"){
				$datarow['goods_status_text'] = "<span style='color:gray;'>품절</span>";
			}else if($datarow['goods_status']=="unsold"){
				$datarow['goods_status_text'] = "<span style='color:red;'>판매중지</span>";
			}else if($datarow['goods_status']=="purchasing"){
				$datarow['goods_status_text'] = "<span style='color:red;'>재고확보중</span>";
			}else{
				$datarow['goods_status_text'] = "<span style='color:blue;'>정상</span>";
			}

			$datarow['title1'] = $datarow["title1"];
			$datarow['option1'] = $datarow["option1"];
			$datarow['title2'] = $datarow["title2"];
			$datarow['option2'] = $datarow["option2"];
			$datarow['title3'] = $datarow["title3"];
			$datarow['option3'] = $datarow["option3"];
			$datarow['title4'] = $datarow["title4"];
			$datarow['option4'] = $datarow["option4"];
			$datarow['title5'] = $datarow["title5"];
			$datarow['option5'] = $datarow["option5"];

			$loop['record'][$k] = $datarow;
		}


		$goods_addition = $this->goodsmodel->goods_addition_list_all();
		$model				= $goods_addition['model'];
		$brand				= $goods_addition['brand'];
		$manufacture	= $goods_addition['manufacture'];
		$orign				= $goods_addition['orgin'];

		$this->template->assign(array('brand'=>$brand,'model'=>$model,'manufacture'=>$manufacture,'orign'=>$orign));

		$file_path	= $this->template_path();
		$this->template->assign('loop',$loop['record']);
		$this->template->assign('page',$loop['page']);
		$this->template->assign('group_arr',$group_arr);
		$this->template->assign(array('perpage'=>$_GET['perpage'],'orderby'=>$_GET['orderby']));
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	/* 재고 조정 */
	public function stock_modify(){
		$file_path	= $this->template_path();

		$stock_code = "ST".date('YmdHis');

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	public function goods_sub_info(){
		$category = $_GET['category'];
		$this->load->model('goodsmodel');
		$result = $this->goodsmodel->get_goods_sub_info($category);

		echo json_encode($result);
	}

	public function gift_catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();

		// 상품검색폼
		$this->template->define(array('goods_search_form' => $this->skin.'/goods/goods_search_form2.html'));
		$file_path	= $this->template_path();

		list($loop,$sc) =  $this->_goods_list2();

		$this->template->assign('loop',$loop['record']);
		$this->template->assign('page',$loop['page']);
		$this->template->assign('search_yn',$loop['search_yn']);
		$this->template->assign(array('perpage'=>$_GET['perpage'],'orderby'=>$_GET['orderby']));
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function _goods_list2()
	{

		### AUTH
		$auth = $this->authmodel->manager_limit_act('goods_act');
		if(isset($auth)) $this->template->assign('auth',$auth);

		###
		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		### SEARCH
		$_GET['orderby'] = ($_GET['orderby']) ? $_GET['orderby']:'goods_seq';
		$_GET['sort']	 = ($_GET['sort']) ? $_GET['sort']:'desc';
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):'10';
		$sc = $_GET;
		$sc['goods_type']	= 'gift';

		### GOODS
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$cfg_order = config_load('order');
		$this->load->model('ordermodel');

		$loop = $this->goodsmodel->admin_goods_list($sc);

		$goods_addition = $this->goodsmodel->goods_addition_list_all();
		$model				= $goods_addition['model'];
		$brand				= $goods_addition['brand'];
		$manufacture	= $goods_addition['manufacture'];
		$orign				= $goods_addition['orgin'];
		/**
		$brand			= $this->goodsmodel->goods_addition_list('brand');
		$model			= $this->goodsmodel->goods_addition_list('model');
		$manufacture	= $this->goodsmodel->goods_addition_list('manufacture');
		$orign			= $this->goodsmodel->goods_addition_list('orign');
		//$brand_title	= $this->brandmodel->get_brand_title();
		**/

		$this->template->assign(array('brand'=>$brand,'model'=>$model,'manufacture'=>$manufacture,'orign'=>$orign,'provider'=>$provider));

		### PAGE & DATA
		$query = "select count(*) cnt from fm_goods A LEFT JOIN fm_goods_option B ON A.goods_seq = B.goods_seq LEFT JOIN fm_goods_supply C ON A.goods_seq = C.goods_seq AND B.option_seq = C.option_seq where B.default_option = 'y' AND A.goods_type = 'gift'";
		$query = $this->db->query($query);
		$data = $query->row_array();
		$loop['page']['all_count'] = $data['cnt'];

		$idx = 0;
		foreach($loop['record'] as $k => $datarow){
			$idx++;

			$optstock = $this->goodsmodel->get_default_option($datarow['goods_seq']);
			$datarow['option_seq']= $optstock['option_seq'];
			$datarow['reserve_rate']= $optstock['reserve_rate'];
			$datarow['reserve_unit']= $optstock['reserve_unit'];
			$datarow['reserve']= $optstock['reserve'];

			$datarow['consumer_price']= $optstock['consumer_price'];
			$datarow['price']= $optstock['price'];
			$datarow['supply_price']= $optstock['supply_price'];
			$datarow['default_stock']= $optstock['stock'];
			$datarow['default_badstock']= $optstock['badstock'];
			$datarow['default_reservation15']= $optstock['reservation15'];
			$datarow['default_reservation25']= $optstock['reservation25'];

			$optstocktot = $this->goodsmodel->get_tot_option($datarow['goods_seq']);
			$datarow['stock']= $optstocktot['stock'];
			$datarow['badstock']= $optstocktot['badstock'];
			$datarow['reservation15']= $optstocktot['reservation15'];
			$datarow['reservation25']= $optstocktot['reservation25'];


			$datarow['goods_view_text']	= $datarow['goods_view']=='look' ? "<span style='color:blue'>노출</span>" : "<span style='color:red'>미노출</span>";
			$datarow['number']		= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			//$datarow['catename']	= $this->categorymodel->get_category_name($datarow['category_code']);
			$reservation = $this->ordermodel->get_reservation_for_goods($cfg_order['ableStockStep'],$datarow['goods_seq']);
			$datarow['rstock'] = $datarow['stock'] - $reservation;

			if($datarow['goods_status']=="runout"){
				$datarow['goods_status_text'] = "<span style='color:gray;'>품절</span>";
			}else if($datarow['goods_status']=="unsold"){
				$datarow['goods_status_text'] = "<span style='color:red;'>판매중지</span>";
			}else if($datarow['goods_status']=="purchasing"){
				$datarow['goods_status_text'] = "<span style='color:red;'>재고확보중</span>";
			}else{
				$datarow['goods_status_text'] = "<span style='color:blue;'>정상</span>";
			}

			// 옵션
			$datarow['options']		= $this->goodsmodel->get_goods_option($datarow['goods_seq']);

			// 최근 매입처
			if($datarow['provider_seq']=='1'){
				$query = $this->db->query("
				select c.supplier_name
				from fm_stock_history_item as a
				inner join fm_stock_history as b on a.stock_code = b.stock_code
				inner join fm_supplier as c on b.supplier_seq = c.supplier_seq
				where a.goods_seq = '{$datarow['goods_seq']}'
				order by b.stock_date desc, b.regist_date desc
				limit 1
				");
				$tmp = $query->row_array();
				$datarow['lastest_supplier_name'] = $tmp['supplier_name'];
			}

			if ($datarow['update_date']=="0000-00-00 00:00:00") {
				$datarow['update_date'] = "&nbsp;";
			}

			$loop['record'][$k] = $datarow;
		}

		return array($loop,$sc);
	}


	public function gift_regist(){
		$this->admin_menu();
		$this->tempate_modules();

		if( !isset($_GET['no']) ){
			$provider_seq = $_GET['provider']=='base' ? 1 : null;
		}

		$limit_stock = '';
		$totstock = 0;
		$reservation15 = 0;
		$reservation25 = 0;

		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->assign('goodsImageSize',config_load('goodsImageSize'));
		$this->template->assign('cfg_goods',config_load('goods'));

		$this->load->model('brandmodel');
		$this->load->model('categorymodel');
		$query = "select category_code from fm_category_link group by category_code order by regist_date desc limit 10";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['title'] =  $this->categorymodel->get_category_name($row['category_code']);
			$last_categories[] = $row;
		}

		$query = "select category_code from fm_brand_link group by category_code order by regist_date desc limit 10";
		$query = $this->db->query($query);
		foreach($query->result_array() as $row){
			$row['title'] =  $this->brandmodel->get_brand_name($row['category_code']);
			$last_brands[] = $row;
		}

		$tmp = config_load('reserve','default_reserve_percent');
		$default_reserve_percent = $tmp['default_reserve_percent'];

		### COMMON INFO
		$query2 = $this->db->query("select * from fm_goods_info where info_name != '' order by info_seq desc");
		foreach($query2->result_array() as $v){
			$info_loop[] = $v;
		}

		if( isset($_GET['no']) ){
			$no = (int) $_GET['no'];
			$this->load->model('goodsmodel');

			$categories = $this->goodsmodel->get_goods_category($no);
			if($categories){
				foreach($categories as $key => $data) $categories[$key]['title'] = $this->categorymodel->get_category_name($data['category_code']);
			}
			$brands = $this->goodsmodel->get_goods_brand($no);


			if($brands){
				foreach($brands as $key => $data) $brands[$key]['title'] = $this->brandmodel->get_brand_name($data['category_code']);
			}

			$goods = $this->goodsmodel->get_goods($no);
			$goods['title']			= strip_tags($goods['goods_name']);
			$goods['goods_name']	= htmlspecialchars($goods['goods_name']);
			$goods['summary']		= htmlspecialchars($goods['summary']);
			$goods['purchase_goods_name']	= htmlspecialchars($goods['purchase_goods_name']);
			$goods['keyword']		= htmlspecialchars($goods['keyword']);
			$goods['string_price']	= htmlspecialchars($goods['string_price']);

			### 모바일 상품설명
			if($goods['mobile_contents']=="<P>&nbsp;</P>") $goods['mobile_contents'] = "";
			if(!$goods['mobile_contents'] && $goods['contents']){
				$goods['mobile_contents'] = $this->goodsmodel->set_mobile_contents($goods['contents'],$goods['goods_seq']);
			}

			if($goods['goods_status']=='normal'){
				$goods['goods_status_text'] = "정상";
			}else if($goods['goods_status']=='runout'){
				$goods['goods_status_text'] = "품절";
			}else if($goods['goods_status']=='purchasing'){
				$goods['goods_status_text'] = "재고확보중";
			}else{
				$goods['goods_status_text'] = "판매중지";
			}
			$goods['goods_view_text'] = $goods['goods_view']=='look' ? "노출" : "미노출";
			$images = $this->goodsmodel->get_goods_image($no);
			$additions = $this->goodsmodel->get_goods_addition($no);
			$options = $this->goodsmodel->get_goods_option($no);
			$suboptions = $this->goodsmodel->get_goods_suboption($no);
			$inputs = $this->goodsmodel->get_goods_input($no);
			$icons = $this->goodsmodel->get_goods_icon($no);

			// 총재고, 출고예약량
			foreach($options as $data_option){
				$totstock += $data_option['stock'];
				$reservation15 += $data_option[$reservation15];
				$reservation25 += $data_option[$reservation25];
			}

			### 공용정보
			$info = get_data("fm_goods_info",array("info_seq"=>$goods['info_seq']));
			$goods['common_contents'] = $info ? $info[0]['info_value'] : '';

			// 배송정보 가져오기
			$delivery = $this->goodsmodel->get_goods_delivery($goods);

			$this->template->assign(array('categories'=>$categories));
			$this->template->assign(array('brands'=>$brands));
			$this->template->assign(array('goods'=>$goods));
			$this->template->assign(array('options'=>$options));
			$this->template->assign(array('additions'=>$additions));
			$this->template->assign(array('icons'=>$icons));
			$this->template->assign(array('suboptions'=>$suboptions));
			$this->template->assign(array('inputs'=>$inputs));
			$this->template->assign(array('images'=>$images));
			$this->template->assign(array('delivery'=>$delivery));

			### 옵션
			if($options){
				$cnt = 0;
				foreach($options as $k){
					$option_cnt = count($k['option_divide_title']);
					for($i=0;$i<$option_cnt;$i++){
						$opt_title[$i] = $k['option_divide_title'][$i];
						if($cnt>0){
							if(!in_array($k['opts'][$i],$opts[$i])){
								$opts[$i][] = $k['opts'][$i];
								$opt_price[$i][] = $k['price'];
							}
						}else{
							$opts[$i][] = $k['opts'][$i];
							$opt_price[$i][] = $k['price'];
						}
					}
					$cnt++;
				}
				for($i=0;$i<count($opt_title);$i++){
					$tmps['title']	= $opt_title[$i];
					$tmps['opt']	= implode(",",$opts[$i]);
					$tmps['price']	= implode(",",$opt_price[$i]);
					$opts_loop[] = $tmps;
				}
				$this->template->assign('opts_loop',$opts_loop);
			}

			### 추가옵션
			if($suboptions){
				$cnt = 0;
				$tmp_cnt = 0;
				unset($tmps);
				foreach($suboptions as $k){
					for($i=0;$i<count($k);$i++){
						if($cnt==0){
							$sopt_title[] = $k[$i]['suboption_title'];
						}else{
							if(!in_array($k[$i]['suboption_title'],$sopt_title)){
								$sopt_title[] = $k[$i]['suboption_title'];
								$tmp_cnt++;
							}
						}
						$sopt_opts[$tmp_cnt][] = $k[$i]['suboption'];
						$sopt_price[$tmp_cnt][] = $k[$i]['price'];
						$cnt++;
					}
				}
				for($i=0;$i<count($sopt_title);$i++){
					$tmps['title']	= $sopt_title[$i];
					$tmps['opt']	= implode(",",$sopt_opts[$i]);
					$tmps['price']	= implode(",",$sopt_price[$i]);
					$sopts_loop[] = $tmps;
				}

				$this->template->assign('sopts_loop',$sopts_loop);
			}

			//print_r($inputs);

			###
			$sql = "SELECT
						distinct A.*, B.*
					FROM
						fm_goods_relation A
						LEFT JOIN
						(SELECT
							g.goods_seq, g.goods_name, o.price
						FROM
							fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y') B ON A.relation_goods_seq = B.goods_seq
					WHERE
						A.goods_seq = '{$no}'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$relation[] = $row;
			}
			if($relation) $this->template->assign('relation',$relation);

			$provider_seq = $goods['provider_seq'];
		}

		/* 입점사 세션일때 */
		if($this->adminSessionType=='provider'){
			/* 등록시 */
			if( !isset($_GET['no']) ){
				$provider_seq = $this->providerInfo['provider_seq'];
			}
			/* 수정시 */
			if( isset($_GET['no']) ){
				if($this->providerInfo['provider_seq'] != $provider_seq){
					pageBack("타 입점사의 상품입니다.");
					exit;
				}
			}

		}

		$this->template->assign(array('provider_seq'=>$provider_seq));
		$this->template->assign(array('provider'=>$provider));
		$this->template->assign(array('provider_charge'=>$provider_charge));
		$this->template->assign(array('provider_list'=>$provider_list));

		### PROVIDER SHIPPING
		if($provider_seq>1){
			$sql = "select * from fm_provider_shipping where provider_seq = '{$provider_seq}'";
			$query = $this->db->query($sql);
			$shipping = $query->result_array();

			$deli_text = "";
			if($shipping[0]['delivery_type']=='free'){
				$deli_text = "비용 : 무료";
			}else if($shipping[0]['delivery_type']=='pay'){
				$deli_text = "비용 : (선불) 유료 ".number_format($shipping[0]['delivery_price'])."원";
			}else if($shipping[0]['delivery_type']=='ifpay'){
				$deli_text = "비용 : ".number_format($shipping[0]['if_free_price'])."원 이상 구매 시 무료, (선불) 유료 ".number_format($shipping[0]['delivery_price'])."원";
			}
			$shipping[0]['deli_text'] = $deli_text;
			$this->template->assign('shipping',$shipping[0]);
		}

		### 품절 기준 수량
		$cfg_order = config_load('order');
		$ableStockLimit = $cfg_order['ableStockLimit'];
		if($cfg_order['runout']=='ableStock'){
			if($cfg_order['ableStockStep'] == 15){
				$limit_stock = $totstock - $reservation15 - $ableStockLimit;
			}else{
				$limit_stock = $totstock - $reservation25 - $ableStockLimit;
			}
		}else if($cfg_order['runout']=='stock'){
			$limit_stock = 0;
		}else{
			$limit_stock = 'unlimited';
		}

		###
		$tmp = config_load('goodsImageSize');
		foreach($tmp as $k=>$v){
			if(substr($k,0,4)=='list'){
				$v['key'] = $k;
				$r_img_size[] = $v;
			}
		}
		$this->template->assign('query_string',$_GET['query_string']);
		$this->template->assign(array('r_img_size'=>$r_img_size));
		$this->template->assign('info_loop',$info_loop);
		$this->template->assign(array('default_reserve_percent'=>$default_reserve_percent));
		$this->template->assign(array('last_categories'=>$last_categories));
		$this->template->assign(array('last_brands'=>$last_brands));
		$this->template->assign(array('limit_stock'=>$limit_stock));
		$this->template->assign(array('cfg_order'=>$cfg_order));
		$this->template->print_("tpl");
	}

	public function member_sale_change(){

		$this->load->model('membermodel');
		$result = $this->membermodel->get_member_sale_array($_GET['sale_seq']);		
	
		$this->template->assign(array('sale_list'=>$result['sale_list']));
		$this->template->assign(array('sale_data'=>$result['data']));
		$this->template->assign(array('loop'=>$result['loop'],'gcount'=>$result['gcount']));

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");

	}

	public function popup_string_price()
	{
		$no = $_GET['no'];
		$this->load->model('goodsmodel');
		$data_goods = $this->goodsmodel->get_goods($no);
		$this->template->assign($data_goods);
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function set_goods_options(){

		$addgoods		= trim($_GET['add_goods_seq']);//자주사용옵션의 상품
		$goods_seq		= trim($_GET['goods_seq']);
		$tmp_seq		= trim($_GET['tmp_seq']);
		$tmp_policy		= trim($_GET['tmp_policy']);
		$islimit		= trim($_GET['islimit']);
		$goodsTax		= trim($_GET['goodsTax']);
		$this->template->assign(array('goodsTax'=>$goodsTax));

		$mode			= trim($_GET['mode']);
		$this->template->assign(array('mode'=>$mode));
		if($_GET['socialcp_input_type']){
			define('SOCIALCPUSE',true);
			$this->template->assign('socialcpuse',1);
		}

		$this->tempate_modules();
		$this->load->model("goodsmodel");


		// 상품 수정일 경우 기존 옵션 정보를 임시로 가져온다.
		if	($goods_seq && !$tmp_seq){
			if($addgoods)	$tmp_seq	= $this->goodsmodel->add_option_tmp_to_option_org($addgoods);
			else			$tmp_seq	= $this->goodsmodel->add_option_tmp_to_option_org($goods_seq);

			$this->template->assign(array('goods_seq'=>$goods_seq));
			$this->template->assign(array('tmp_policy'=>$tmp_policy));
			$this->template->assign(array('reload'=>'y'));
			$this->template->assign(array('islimit'=>$islimit));

		// 상품 신규 등록에서 옵션 초기 등록 시
		}elseif(!$goods_seq && !$tmp_seq){
			if($addgoods)	$tmp_seq	= $this->goodsmodel->add_option_tmp_to_option_org($addgoods);
			else			$tmp_seq	= $this->goodsmodel->add_option_tmp_to_option_org($goods_seq);

			$goods_info['reserve_policy']	= $tmp_policy;
			$this->template->assign(array('tmp_policy'=>$tmp_policy));
			$this->template->assign(array('goods'=>$goods_info));
			$this->template->assign(array('reload'=>'y'));
			$this->template->assign(array('islimit'=>$islimit));
		}else{
			// 기본 정책 정보
			$reserves		= ($this->reserves)?$this->reserves:config_load('reserve');
			$point_text		= "";
			if($reserves['point_use']=='Y'){
				switch($reserves['default_point_type']){
					case "per":
						$point_text = "※ 지급 포인트(P) ".$reserves['default_point_percent']."%";
						break;
					case "app":
						$point_text = "※ 지급 포인트(P) ".number_format($reserves['default_point_app'])."원당 ".$reserves['default_point']."포인트";
						break;
					default :
						$point_text = "";
						break;
				}
			}else{
				$point_text = "※ 지급 포인트(P) 없음";
			}

			### 품절 기준 수량
			$cfg_order = config_load('order');
			$ableStockLimit = $cfg_order['ableStockLimit'];
			if($cfg_order['runout']=='ableStock'){
				if($cfg_order['ableStockStep'] == 15){
					$limit_stock = $totstock - $reservation15 - $ableStockLimit;
				}else{
					$limit_stock = $totstock - $reservation25 - $ableStockLimit;
				}
			}else if($cfg_order['runout']=='stock'){
				$limit_stock = 0;
			}else{
				$limit_stock = 'unlimited';
			}

			// 임시 옵션 정보
			$tmp_option_list					= array();
			if	($tmp_seq)	$tmp_option_list	= $this->goodsmodel->get_option_tmp_list($tmp_seq);

			// 상품 정보 추출
			if	(!$goods_seq)
				$goods_seq			= $tmp_option_list[0]['goods_seq'];
			if	($goods_seq)
				$goods_info			= $this->goodsmodel->get_goods($goods_seq);
			if	($goods_info['goods_seq'])	$reserve_policy	= $goods_info['reserve_policy'];

			// 총재고, 출고예약량
			foreach($tmp_option_list as $key_option => $data_option){
				$totstock += $data_option['stock'];
				$reservation15 += $data_option['reservation15'];
				$reservation25 += $data_option['reservation25'];
				if	($cfg_order['ableStockStep'] == 15){
					$totunUsableStock	+= $data_option['badstock'] + $data_option['reservation15'];
				}
				if	($cfg_order['ableStockStep'] == 25){
					$totunUsableStock	+= $data_option['badstock'] + $data_option['reservation25'];
				}

				// 기본정책일 경우 적립금
				$data_option['shop_reserve']		= floor($data_option['price'] * ($reserves['default_reserve_percent'] * 0.01));

				// 임시 저장된 적립금 정책 로드
				if	($data_option['tmp_policy'])
					$goods_info['reserve_policy']	= $data_option['tmp_policy'];
				if	(!$goods_info['reserve_policy'])
					$goods_info['reserve_policy']	= 'shop';

				// 기본정책일 경우 적립금 표기
				if	($goods_info['reserve_policy'] == 'shop'){
					$data_option['reserve_rate']	= $reserves['default_reserve_percent'];
					$data_option['reserve_unit']	= 'percent';
					$data_option['reserve']			= floor($data_option['price'] * ($reserves['default_reserve_percent'] * 0.01));

				// 개별정책일 경우 적립금 계산
				}else{
					if	($data_option['reserve_unit'] == 'percent')
						$data_option['reserve']			= floor($data_option['price'] * ($data_option['reserve_rate'] / 100));
					else
						$data_option['reserve']			= $data_option['reserve_rate'];
				}

				$tmp_option_list[$key_option]	= $data_option;
			}

			//상품추가양식 정보
			if	($mode != 'view')
				$goodsoptionloop	= $this->goodsmodel->get_add_option_code();

			for($i=0;$i<count($tmp_option_list[0]['option_divide_title']);$i++){
				$tmps['title']						= $tmp_option_list[0]['option_divide_title'][$i];
				$tmps['type']						= $tmp_option_list[0]['option_divide_type'][$i];
				$tmps['code_seq']					= $tmp_option_list[$i]['option_divide_codeseq'][$i];
				$tmps['opt']						= implode(",",$tmp_option_list[0]['optionArr'][$i]);
				$tmps['optcodes']					= implode(",",$tmp_option_list[0]['codeArr'][$i]);
				$tmps['price']						= implode(",",$tmp_option_list[0]['priceArr'][$i]);
				if( $tmp_option_list[0]['divide_newtype'][$i] == 'color' ) {
					$tmps['colors']					= implode(",",$tmp_option_list[0]['colorArr'][0]);
				}elseif( $tmp_option_list[0]['divide_newtype'][$i] == 'address' ) {
					$isAddr							= "Y";
					$tmps['zipcodes']				= implode(",",$tmp_option_list[0]['zipcodeArr'][0]);
					$tmps['address_types']			= implode(",",$tmp_option_list[0]['address_typeArr'][0]);
					$tmps['addresss']				= implode(",",$tmp_option_list[0]['addressArr'][0]);
					$tmps['address_streets']		= implode(",",$tmp_option_list[0]['address_streetArr'][0]);
					$tmps['addressdetails']			= implode(",",$tmp_option_list[0]['addressdetailArr'][0]);
					$tmps['biztels']				= implode(",",$tmp_option_list[0]['biztelArr'][0]);
					$tmps['address_commissions']= implode(",",$tmp_option_list[0]['address_commissionArr'][0]);
				}elseif( $tmp_option_list[0]['divide_newtype'][$i] == 'date' ) {
					$tmps['codedates']				= implode(",",$tmp_option_list[0]['codedateArr'][0]);
				}

				$tmps['sdayinput']					= $tmp_option_list[0]['sdayinput'];
				$tmps['fdayinput']					= $tmp_option_list[0]['fdayinput'];
				$tmps['dayauto_type']				= $tmp_option_list[0]['dayauto_type'];

				$tmps['dayauto_type_title']			= $this->goodsmodel->dayautotype[$tmp_option_list[0]['dayauto_type']];
				$tmps['sdayauto']					= $tmp_option_list[0]['sdayauto'];
				$tmps['fdayauto']					= $tmp_option_list[0]['fdayauto'];
				$tmps['dayauto_day']				= $tmp_option_list[0]['dayauto_day'];
				$tmps['dayauto_day_title']			= $this->goodsmodel->dayautoday[$tmp_option_list[0]['dayauto_day']];

				if( $tmp_option_list[0]['divide_newtype'][$i] == 'dayauto' ) {
					$dayautodate = goods_dayauto_setting_day( date("Y-m-d") , $tmps['sdayauto'], $tmps['fdayauto'], $tmps['dayauto_type'], $tmps['dayauto_day'] );
					$tmps['social_start_date_end']=$dayautodate['social_start_date']."~".$dayautodate['social_end_date'];
				}


				$tmps['newtype']				= $tmp_option_list[0]['divide_newtype'];

				$tmps['goodsoptionloop']	= $goodsoptionloop;
				//debug_var($tmp_option_list);
				//debug_var($tmps);
				$opts_loop[]				= $tmps;
				unset($tmps);
			}

			//자주쓰는 필수옵션
			$freqloop = $this->goodsmodel->frequentlygoods('opt',$goods_seq,defined('SOCIALCPUSE'));
			if($freqloop) {
				$$freqloophtml = '';
				foreach( $freqloop as $freqkey => $freqdata ){
					$$freqloophtml .= "<option value='".$freqdata['goods_name']."^^".$freqdata['goods_seq']."' >".$freqdata['goods_name']."</option>";
				}
			}

			// 옵션 기본 노출 수량 적용
			$config_goods	= config_load('goods');

			$this->template->assign(array(
				'islimit'			=> $islimit,
				'config_goods'		=> $config_goods,
				'reserves'			=> $reserves,
				'point_text'		=> $point_text,
				'limit_stock'		=> $limit_stock,
				'cfg_order'			=> $cfg_order,
				'isAddr'			=> $isAddr,
				'frequentlyopt'		=> $$freqloophtml,
				'tmp_policy'		=> $tmp_policy,
				'totstock'			=> $totstock,
				'totunUsableStock'	=> $totunUsableStock,
				'goods'				=> $goods_info,
				'goods_seq'			=> $goods_seq,
				'goodsoptionloop'	=> $goodsoptionloop,
				'options'			=> $tmp_option_list,
				'opts_loop'			=> $opts_loop,
			));
		}

		$frequentlyoptlist = $this->goodsmodel->frequentlygoods('opt',$goods_seq,defined('SOCIALCPUSE'));
		$this->template->assign(array('frequentlyoptlist'=>$frequentlyoptlist));

		$this->template->assign(array('tmp_seq'=>$tmp_seq));
		$filePath	= $this->template_path();
		$this->template->define(array(
			'tpl'		=> $filePath,
			'ONLY_VIEW'		=> str_replace('set_goods_options', 'view_goods_options', $filePath),
			'EDIT_VIEW'		=> str_replace('set_goods_options', 'edit_goods_options', $filePath),
			'CREATE_OPTION'	=> str_replace('set_goods_options', 'create_goods_options', $filePath),
		));
		$this->template->print_("tpl");
	}

	public function set_goods_suboptions(){

		$addgoods		= trim($_GET['add_goods_seq']);//자주사용옵션의 상품
		$goods_seq		= trim($_GET['goods_seq']);
		$tmp_seq		= trim($_GET['tmp_seq']);
		$tmp_policy		= trim($_GET['tmp_policy']);
		$islimit		= trim($_GET['islimit']);
		$goodsTax		= trim($_GET['goodsTax']);
		$this->template->assign(array('goodsTax'=>$goodsTax));

		$mode			= trim($_GET['mode']);
		$this->template->assign(array('mode'=>$mode));

		if($_GET['socialcp_input_type']){
			define('SOCIALCPUSE',true);
			$this->template->assign('socialcpuse',1);
		}

		$this->tempate_modules();
		$this->load->model("goodsmodel");

		// 상품 수정일 경우 기존 옵션 정보를 임시로 가져온다.
		if	($goods_seq && !$tmp_seq){
			if	($tmp_policy == 'shop')	$addMod	= $mode;
			if($addgoods) {//자주사용옵션 상품정보
				$tmp_seq		= $this->goodsmodel->add_suboption_tmp_to_suboption_org($addgoods, $addMod);
			}else{
				$tmp_seq		= $this->goodsmodel->add_suboption_tmp_to_suboption_org($goods_seq, $addMod);
			}

			$this->template->assign(array('tmp_policy'=>$tmp_policy));
			$this->template->assign(array('reload'=>'y'));
			$this->template->assign(array('islimit'=>$islimit));
		}elseif(!$goods_seq && !$tmp_seq){
			if	($tmp_policy == 'shop')	$addMod	= $mode;
			if($addgoods) {//자주사용옵션 상품정보
				$tmp_seq		= $this->goodsmodel->add_suboption_tmp_to_suboption_org($addgoods, $addMod);
			}else{
				$tmp_seq		= $this->goodsmodel->add_suboption_tmp_to_suboption_org($goods_seq, $addMod);
			}
			$goods_info['reserve_policy']	= $tmp_policy;
			$this->template->assign(array('tmp_policy'=>$tmp_policy));
			$this->template->assign(array('goods'=>$goods_info));
			$this->template->assign(array('reload'=>'y'));
			$this->template->assign(array('islimit'=>$islimit));
		}else{
			// 기본 정책 정보
			$reserves		= ($this->reserves)?$this->reserves:config_load('reserve');
			$point_text		= "";
			if($reserves['point_use']=='Y'){
				switch($reserves['default_point_type']){
					case "per":
						$point_text = "※ 지급 포인트(P) ".$reserves['default_point_percent']."%";
						break;
					case "app":
						$point_text = "※ 지급 포인트(P) ".number_format($reserves['default_point_app'])."원당 ".$reserves['default_point']."포인트";
						break;
					default :
						$point_text = "";
						break;
				}
			}else{
				$point_text = "※ 지급 포인트(P) 없음";
			}
			$this->template->assign(array('reserves'=>$reserves));
			$this->template->assign(array('point_text'=>$point_text));

			// 임시 옵션 정보
			$tmp_option_list	= array();
			if	($tmp_policy == 'shop')	$addMod	= $mode;
			if	($tmp_seq)
				$tmp_option_list	= $this->goodsmodel->get_suboption_tmp_list($tmp_seq, $addMod);

				//debug_var($tmp_option_list);

			//상품추가양식 정보
			$goodssuboptionloop	= $this->goodsmodel->get_add_suboption_code();
			//debug_var($goodssuboptionloop);

			if	(!$goods_seq)
				$goods_seq	= $tmp_option_list[0][0]['goods_seq'];

			if	($goods_seq)
				$goods_info	= $this->goodsmodel->get_goods($goods_seq);

			// 총재고, 출고예약량
			if	($tmp_option_list)foreach($tmp_option_list as $key_suboption => $data_suboption){
				if	($data_suboption)foreach($data_suboption as $key_sub => $data_sub){
					if	($key_sub == 0){
						$tmps['title']						= $tmp_option_list[$key_suboption][0]['suboption_title'];
						$tmps['type']					= $tmp_option_list[$key_suboption][0]['suboption_type'];

						$tmps['code_seq']			= $tmp_option_list[$key_suboption][0]['code_seq'];
						$tmps['opt']						= implode(",",$tmp_option_list[$key_suboption][0]['optArr']);
						$tmps['optcodes']			= implode(",",$tmp_option_list[$key_suboption][0]['codeArr']);

						$tmps['newtype']				= $tmp_option_list[$key_suboption][0]['newtype'];

						$tmps['colors']					= implode(",",$tmp_option_list[$key_suboption][0]['colorArr']);
						$tmps['zipcodes']				= implode(",",$tmp_option_list[$key_suboption][0]['zipcodeArr']);
						$tmps['address_types']			= implode(",",$tmp_option_list[$key_suboption][0]['address_typeArr']);
						$tmps['addresss']			= implode(",",$tmp_option_list[$key_suboption][0]['addressArr']);
						$tmps['address_streets']			= implode(",",$tmp_option_list[$key_suboption][0]['address_streetArr']);
						$tmps['addressdetails']	= implode(",",$tmp_option_list[$key_suboption][0]['addressdetailArr']);
						$tmps['biztels']				= implode(",",$tmp_option_list[$key_suboption][0]['biztelArr']);
						$tmps['codedates']			= implode(",",$tmp_option_list[$key_suboption][0]['codedateArr']);

						$tmps['sdayinput']			= $tmp_option_list[$key_suboption][0]['sdayinput'];
						$tmps['fdayinput']			= $tmp_option_list[$key_suboption][0]['fdayinput'];
						$tmps['dayauto_type']		= $tmp_option_list[$key_suboption][0]['dayauto_type'];
						$tmps['dayauto_type_title'] = $this->goodsmodel->dayautotype[$tmp_option_list[$key_suboption][0]['dayauto_type']];
						$tmps['sdayauto']			= $tmp_option_list[$key_suboption][0]['sdayauto'];
						$tmps['fdayauto']				= $tmp_option_list[$key_suboption][0]['fdayauto'];
						$tmps['dayauto_day']		= $tmp_option_list[$key_suboption][0]['dayauto_day'];
						$tmps['dayauto_day_title'] = $this->goodsmodel->dayautoday[$tmp_option_list[$key_suboption][0]['dayauto_day']];

						if( $tmps['newtype'] == 'dayauto' ) {
							$dayautodate = goods_dayauto_setting_day( date("Y-m-d") , $tmps['sdayauto'], $tmps['fdayauto'], $tmps['dayauto_type'], $tmps['dayauto_day'] );
							$tmps['social_start_date_end']=$dayautodate['social_start_date']."~".$dayautodate['social_end_date'];
						}

						$tmps['price']				= implode(",",$tmp_option_list[$key_suboption][0]['priceArr']);
						$tmps['goodssuboptionloop']	= $goodssuboptionloop;
						$sopts_loop[] = $tmps;
					}

					$totstock += $data_sub['stock'];
					$reservation15 += $data_sub['reservation15'];
					$reservation25 += $data_sub['reservation25'];
					if	($cfg_order['ableStockStep'] == 15){
						$totunUsableStock	+= $data_sub['badstock'] + $data_sub['reservation15'];
					}
					if	($cfg_order['ableStockStep'] == 25){
						$totunUsableStock	+= $data_sub['badstock'] + $data_sub['reservation25'];
					}

					// 기본정책일 경우 적립금 표기
					if($tmp_policy == 'shop'){
						$data_sub['reserve_rate'] = $reserves['default_reserve_percent'];
						$data_sub['reserve_unit'] = 'percent';
						$data_sub['reserve'] = floor($data_sub['price'] * ($reserves['default_reserve_percent'] * 0.01));
						$data_suboption[$key_sub] = $data_sub;
					}
				}
				$tmp_option_list[$key_suboption]	= $data_suboption;
			}

			if	($tmp_policy)
				$goods_info['reserve_policy']	= $tmp_policy;


			//자주쓰는 추가구성옵션
			$freqloop = $this->goodsmodel->frequentlygoods('sub',$goods_seq,defined('SOCIALCPUSE'));
			if($freqloop) {
				$freqloophtml = '';
				foreach( $freqloop as $freqkey => $freqdata ){
					$freqloophtml .= "<option value='".$freqdata['goods_name']."^^".$freqdata['goods_seq']."' >".$freqdata['goods_name']."</option>";
				}
			}

			// 옵션 기본 노출 수량 적용
			$config_goods	= config_load('goods');

			$suboptions_cnt = 0;
			foreach($tmp_option_list as $k=>$v){
				foreach($v as $k2=>$v2){
					$tmp_option_list[$k][$k2]['suboptions_cnt'] = $suboptions_cnt;
					$suboptions_cnt++;
				}
			}
			$this->template->assign(array('total_suboptions_cnt'=>$suboptions_cnt-1));

			$this->template->assign(array('config_goods'=>$config_goods));
			$this->template->assign(array('frequentlyopt'=>$freqloophtml));
			$this->template->assign(array('islimit'=>$islimit));
			$this->template->assign(array('default_reserve_percent'=>$reserves['default_reserve_percent']));
			$this->template->assign(array('tmp_policy'=>$tmp_policy));
			$this->template->assign(array('totstock'=>$totstock));
			$this->template->assign(array('totunUsableStock'=>$totunUsableStock));
			$this->template->assign(array('goods'=>$goods_info));
			$this->template->assign(array('goods_seq'=>$goods_seq));
			$this->template->assign(array('goodssuboptionloop'=>$goodssuboptionloop));
			$this->template->assign(array('reserve_policy'=>$reserve_policy));
			$this->template->assign(array('suboptions'=>$tmp_option_list));
			$this->template->assign(array('sopts_loop'=>$sopts_loop));
		}

		$frequentlysublist = $this->goodsmodel->frequentlygoods('sub',$goods_seq,defined('SOCIALCPUSE'));
		$this->template->assign(array('frequentlysublist'=>$frequentlysublist));

		$this->template->assign(array('tmp_seq'=>$tmp_seq));
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	//상품등록/수정시 입력옵션 가져오기
	public function set_goods_inputoptions() {
		$no		= trim($_GET['goods_seq']);
		$this->tempate_modules();
		$this->load->model("goodsmodel");

		$inputs = $this->goodsmodel->get_goods_input($no);
		$this->template->assign(array('inputs'=>$inputs));
		$this->template->define('*', $this->template_path());
		$html = $this->template->fetch('*');
		$return = array('html'=>$html);
		echo json_encode($return);
	}

	/* 쿠폰상품 무제한 추가 신청 2013-11-28 이원희 */
	public function social_payment(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}

		$req_type = 'COUPON_SALE';
		$param = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=".$req_type."&req_url=/myshop&payment_type=".$_GET['type']."&totalCnt=".$_GET['totalCnt'];
		$param = makeEncriptParam($param);

		$this->template->assign('param',$param);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//쿠폰상품그룹
	public function social_goods_group()
	{
		$this->load->model('providermodel');
		if($_GET['provider_seq']>1){
			$provider = $this->providermodel->get_provider_one($_GET['provider_seq']);
			$_GET['provider_name'] = $provider['provider_name'];
		}else{
			$_GET['provider_name'] = '본사';
		}
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//쿠푼상품리스트폼
	public function social_goods_group_html()
	{
		$this->load->model('providermodel');
		$this->load->model('socialgoodsgroupmodel');
		$result = $this->socialgoodsgroupmodel->social_goods_group_html();
		echo json_encode($result);
		exit;
	}

}

/* End of file goods.php */
/* Location: ./app/controllers/admin/goods.php */