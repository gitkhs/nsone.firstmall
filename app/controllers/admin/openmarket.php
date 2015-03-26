<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class openmarket extends admin_base {
	public function __construct() {
		parent::__construct();

		$this->load->model('openmarketmodel');
		$this->template->assign(array('LINKAGE_SERVICE' => $this->openmarketmodel->chk_linkage_service()));
	}

	public function index(){
		redirect("/admin/openmarket/config");
	}

	## 서비스 신청 유료결제 페이지
	public function regist(){
		$params	= "num=" . $this->config_system['shopSno'];
		$params = makeEncriptParam($params);

		$linkage_service = LINKAGE_SERVICE ? $this->openmarketmodel->get_linkage_service() : null;

		$this->template->assign(array('param'=>$params,'linkage_service'=>$linkage_service));

		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 판매마켓 설정
	public function config(){

		$params	= "num=" . $this->config_system['shopSno'];
		$params = makeEncriptParam($params);

		// 연동 지원 업체 및 마켓 정보
		$comlist	= $this->openmarketmodel->get_linkage_company($sc);
		// 현재 설정된 연동 업체 및 마켓 정보
		$linkage	= $this->openmarketmodel->get_linkage_config();
		if	(!$linkage['linkage_id'])	$linkage	= $comlist[0];
		$mall		= $this->openmarketmodel->get_linkage_mall();
		if	($mall)foreach($mall as $k => $data){
			$rmall[$data['mall_code']][]	= $data;
		}

		$this->openmarketmodel->ended_linkage_service();
		$this->template->assign(array(
			'comlist'	=> $comlist, 
			'malllist'	=> $small, 
			'linkage'	=> $linkage, 
			'mall'		=> $rmall, 
			'param'		=> $params,
		));

		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 지원하는 마켓 목록
	public function get_mall_list(){
		$seq		= trim($_GET['seq']);

		$sc['seq']			= $seq;
		$linkage			= $this->openmarketmodel->get_linkage_company($sc);
		$linkage			= $linkage[0];
		$sc['linkage_seq']	= $linkage['linkage_seq'];
		$malllist			= $this->openmarketmodel->get_linkage_support_mall($linkage['linkage_id'], $sc);
		if	($malllist)foreach($malllist as $m => $malldata){
			$small[$malldata['mall_type']][]	= $malldata;
		}
		$mall				= $this->openmarketmodel->get_linkage_mall();
		if	($mall)foreach($mall as $k => $data){
			$rmall[$data['mall_code']][]	= $data;
		}
		$this->openmarketmodel->ended_linkage_service();
		$this->template->assign(array(
			'malllist'	=> $small, 
			'linkage'	=> $linkage, 
			'mall'		=> $rmall, 
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 카테고리 매칭 페이지
	public function category(){

		// 몰 카테고리 기준 카테고리 목록 ( 매칭된 카테고리 포함 )
		$divStr				= '>';
		$linkage			= $this->openmarketmodel->get_linkage_config();
		$match_category		= $this->openmarketmodel->get_shop_category();
		if	($match_category)foreach($match_category as $k => $data){
			$category[$data['category_code']]['title']			= $data['title'];
			$category[$data['category_code']]['name']			= $data['title'];
			$category[$data['category_code']]['linkseq']		= $data['category_linkage_seq'];
			$category[$data['category_code']]['linkage']		= '';
			if	($data['linkage_category_name1'])
				$category[$data['category_code']]['linkage']	.= $data['linkage_category_name1'];
			if	($data['linkage_category_name2'])
				$category[$data['category_code']]['linkage']	.= $divStr.$data['linkage_category_name2'];
			if	($data['linkage_category_name3'])
				$category[$data['category_code']]['linkage']	.= $divStr.$data['linkage_category_name3'];
			if	($data['linkage_category_name4'])
				$category[$data['category_code']]['linkage']	.= $divStr.$data['linkage_category_name4'];
			$display_name										= array();
			$category_len										= strlen($data['category_code']);
			for ($c = 4; $c <= $category_len; $c+=4){
				$tmp_code			= substr($data['category_code'], 0, $c);
				$display_name[]		= $category[$tmp_code]['title'];
			}
			if	(is_array($display_name) && count($display_name) > 0)
				$category[$data['category_code']]['name']	= implode($divStr, $display_name);

			if	(strlen($data['category_code']) == 4){
				$fcategory[$data['category_code']]			= $category[$data['category_code']];
			}
		}

		// 연동업체 카테고리
		$sc['depth']		= 1;
		$linkage_category	= $this->openmarketmodel->get_linkage_category($linkage['linkage_id'], $sc);
		$this->openmarketmodel->ended_linkage_service();

		$this->template->assign(array(
			'linkage'			=> $linkage, 
			'category'			=> $category, 
			'fcategory'			=> $fcategory, 
			'linkage_category'	=> $linkage_category 
		));
		$this->admin_menu();
		$this->tempate_modules();
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 하위 카테고리 목록 추출
	public function get_next_category(){
		$result				= array('result'=>false,'msg'=>'잘못된 접근입니다.');
		$parent_code		= trim($_GET['parent_code']);
		if	($parent_code){
			$sc['pCode']		= $parent_code;
			$category			= $this->openmarketmodel->get_shop_category($sc);
			$result				= array('result'=>false,'msg'=>'하위 카테고리가 없습니다.');
			if	($category){
				foreach($category as $k => $cate){
					$data[]			= array('code'=>$cate['category_code'], 
											'name'=>$cate['title']);
				}
				$result['result']	= true;
				$result['msg']		= '';
				$result['data']		= $data;
			}
		}
		$this->openmarketmodel->ended_linkage_service();

		echo json_encode($result);
	}

	## 연동업체 하위 카테고리 목록 추출
	public function get_next_category_linkage(){

		$result					= array('result'=>false,'msg'=>'잘못된 접근입니다.');
		$linkage				= $this->openmarketmodel->get_linkage_config();
		$parent_code			= trim($_GET['parent_code']);
		$parent_depth			= trim($_GET['parent_depth']);
		$parent_key				= 'category_code'.$parent_depth;
		$category_code_key		= 'category_code'.($parent_depth + 1);
		$category_name_key		= 'category_name'.($parent_depth + 1);
		if	($parent_code && $parent_depth){

			// 연동업체 카테고리
			$sc[$parent_key]	= $parent_code;
			$sc['depth']		= $parent_depth + 1;
			$linkage_category	= $this->openmarketmodel->get_linkage_category($linkage['linkage_id'], $sc);
			$result				= array('result'=>false,'msg'=>'하위 카테고리가 없습니다.');
			if	($linkage_category){
				foreach($linkage_category as $k => $cate){
					$data[]			= array('code'=>$cate[$category_code_key], 
											'name'=>$cate[$category_name_key]);
				}
				$result['result']	= true;
				$result['msg']		= '';
				$result['data']		= $data;
			}
		}
		$this->openmarketmodel->ended_linkage_service();

		echo json_encode($result);
	}

	## 연동 업체 선택 팝업
	public function set_use_mall(){
		$openType			= trim($_GET['openType']);
		$resfunc			= trim($_GET['resfunc']);
		$orgvalinputname	= trim($_GET['orgvalinputname']);

		$malldata	= $this->openmarketmodel->get_linkage_mall();
		if	($malldata)foreach($malldata as $k => $data){
			$mall[$data['mall_code']]	= $data;
		}

		$this->tempate_modules();
		$this->template->assign(array(
			'openType'			=> $openType, 
			'orgvalinputname'	=> $orgvalinputname, 
			'resfunc'			=> $resfunc, 
			'mall'				=> $mall, 
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 연동 마켓별 option 금액 조정
	public function set_option_price(){
		$openType			= trim($_GET['openType']);
		$resfunc			= trim($_GET['resfunc']);
		$tmpseq				= trim($_GET['tmpseq']);
		$goods_seq			= trim($_GET['goods_seq']);
		$optuse				= trim($_GET['optuse']);
		$opttmpseq			= trim($_GET['opttmpseq']);
		$optprice			= trim($_GET['optprice']);

		// 판매마켓 설정 정보
		$linkage			= $this->openmarketmodel->get_linkage_config();

		// 현재 필수옵션 사용중
		if	($optuse == 'y'){
			$this->load->model('goodsmodel');

			// 필수옵션이 현 페이지에서 수정되었음.
			if	($opttmpseq){
				$opt		= $this->goodsmodel->get_option_tmp_list($opttmpseq);
			// 필수옵션이 현 페이지에서 수정되지 않았음.
			}elseif	($goods_seq){
				$opt		= $this->goodsmodel->get_goods_option($goods_seq);
			}else{
				echo 'error:옵션을 먼저 등록해 주세요.';
				exit;
			}
		// 현재 필수옵션 미사용중
		}elseif	($optprice){
			$opt[0]['price']	= $optprice;
		}else{
			echo 'error:옵션 가격을 입력해 주세요.';
			exit;
		}

		// 현재 설정된 마켓 목록
		$malldata	= $this->openmarketmodel->get_linkage_mall();
		if	(!$malldata){
			echo 'error:판매마켓을 먼저 설정해 주세요.';
			exit;
		}

		// tmpseq가 있는 경우 ( 현 페이지에서 이미 한번 수정했음을 의미 따라서 tmp가 최신 값임 )
		if	($tmpseq){
			$mgconfig			= $this->openmarketmodel->get_linkage_goods_config_tmp($tmpseq, 'opt');
			if	($mgconfig[0]['cal_type'] == 'manual')
				$mgpricedata	= $this->openmarketmodel->get_linkage_option_price_tmp($tmpseq);

		// tmpseq가 없는 경우 ( 현 페이지에서 수정한적이 없음을 의미 따라서 상품정보의 값이 최신 값임 )
		}elseif	($goods_seq){
			$mgconfig			= $this->openmarketmodel->get_linkage_goods_config($goods_seq, 'opt');
			if	($mgconfig[0]['cal_type'] == 'manual')
				$mgpricedata	= $this->openmarketmodel->get_linkage_option_price($goods_seq);
		}

		// market 데이터를 option_seq와 mall_code 기준으로 재배열
		if	($mgpricedata)foreach($mgpricedata as $m => $data){
			$mgprice[$data['option_seq']][$data['mall_code']]	= $data;
		}

		// market별 설정 데이터를 mall_code 기준으로 재배열
		if	($mgconfig){
			$cal_type	= $mgconfig[0]['cal_type'];
			foreach($mgconfig as $m => $data){
				$tmp_mgconfig[$data['mall_code']]	= $data;
			}
			unset($mgconfig);
			$mgconfig	= $tmp_mgconfig;
		}
		

		// 금액 조정 set 재정의
		foreach($malldata as $k => $data){

			$mallConfig	= $mgconfig[$data['mall_code']];

			$data['save_type']	= '';
			// 기존 저장된 데이터로 변경
			if	($mallConfig){
				if	($data['revision_val'] == $mallConfig['revision_val'] && $data['revision_unit'] == $mallConfig['revision_unit'] && $data['revision_type'] == $mallConfig['revision_type']){
					$data['save_type']	= 'Y';
					$mall[$data['mall_code']]['save_type']	= $data['save_type'];
				}
				$mall[$data['mall_code']]['linkage_seq']	= $data['linkage_seq'];
				$mall[$data['mall_code']]['mall_code']		= $data['mall_code'];
				$mall[$data['mall_code']]['mall_name']		= $data['mall_name'];
				$mall[$data['mall_code']]['mall_key']		= $data['mall_key'];
				$mall[$data['mall_code']]['default_yn']		= $data['default_yn'];
				$mall[$data['mall_code']]['revision_val']	= $mallConfig['revision_val'];
				$mall[$data['mall_code']]['revision_unit']	= $mallConfig['revision_unit'];
				$mall[$data['mall_code']]['revision_type']	= $mallConfig['revision_type'];
			}elseif	($data['default_yn'] == 'Y'){
				$mall[$data['mall_code']]['linkage_seq']	= $data['linkage_seq'];
				$mall[$data['mall_code']]['mall_code']		= $data['mall_code'];
				$mall[$data['mall_code']]['mall_name']		= $data['mall_name'];
				$mall[$data['mall_code']]['mall_key']		= $data['mall_key'];
				$mall[$data['mall_code']]['default_yn']		= $data['default_yn'];
				$mall[$data['mall_code']]['revision_val']	= $data['revision_val'];
				$mall[$data['mall_code']]['revision_unit']	= $data['revision_unit'];
				$mall[$data['mall_code']]['revision_type']	= $data['revision_type'];
			}

			$data['revision_str']	= '';
			if	($data['default_yn'] == 'Y')		$data['revision_str']	.= '[기본] ';
			$data['revision_str']	.= $data['revision_val'];
			if	($data['revision_unit'] == 'won')	$data['revision_str']	.= '원 ';
			else									$data['revision_str']	.= '% ';
			if	($data['revision_type'] == 'M')		$data['revision_str']	.= '-조정';
			else									$data['revision_str']	.= '+조정';

			$mall[$data['mall_code']]['revision'][]		= $data;
		}

		// 옵션 배열 재정의 몰코드 배열 추가
		if	($opt)foreach($opt as $o => $option){
			$opt[$o]['mall']	= $mall;
		}

		$this->tempate_modules();
		$this->template->assign(array(
			'linkage'			=> $linkage, 
			'mall'				=> $mall, 
			'mgprice'			=> $mgprice, 
			'openType'			=> $openType, 
			'cal_type'			=> $cal_type,
			'mgconfig'			=> $mgconfig, 
			'resfunc'			=> $resfunc, 
			'opt'				=> $opt, 
		));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	## 상품 일괄 전송 팝업
	public function select_send_goods(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$containerHeight = !empty($_GET['containerHeight']) ? $_GET['containerHeight'] : 350;
		$this->template->assign(array('containerHeight'=>$containerHeight));

		$this->load->model('goodsdisplay');
		$this->template->assign(array('auto_orders'	=> $this->goodsdisplay->auto_orders));

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## 상품 목록
	public function goods_list(){
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('goodsmodel');
		$file_path	= $this->template_path();

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

		if( count($_GET) == 0 ){
			if($_COOKIE['goods_list_search']){
				$arr = explode('&',$_COOKIE['goods_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);
					if( $arr2[0]=='regist_date'){
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
		$_GET['orderby'] = ($_GET['orderby']) ? $_GET['orderby']:'goods_name';
		$_GET['sort']	 = ($_GET['sort']) ? $_GET['sort']:'asc';
		$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
		$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):'10';
		$sc = $_GET;
		$sc['goods_type']	= 'goods';

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

		// 판매마켓 검색
		if(is_array($_GET["openmarket"]) && count($_GET['openmarket']) > 0){
			$sc["openmarket"] = $_GET["openmarket"];
		}

		### GOODS
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('videofiles');
		$cfg_order = config_load('order');
		$this->load->model('ordermodel');
//		$this->load->model('providermodel');
		$this->load->model('locationmodel');

		$loop = $this->goodsmodel->admin_goods_list($sc);

		### ADDITION
		$goods_addition = $this->goodsmodel->goods_addition_list_all();
		$model				= $goods_addition['model'];
		$brand				= $goods_addition['brand'];
		$manufacture		= $goods_addition['manufacture'];
		$orign				= $goods_addition['orgin'];
//		$provider			= $this->providermodel->provider_goods_list();
		//$brand_title	= $this->brandmodel->get_brand_title();

		$this->template->assign(array('brand'=>$brand,'model'=>$model,'manufacture'=>$manufacture,'orign'=>$orign,'provider'=>$provider));

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
			$datarow['provider_status_text']	= $datarow['provider_status']=='1' ? "<b>승인</b>" : "<b>미승인</b>";
			$datarow['number']		= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			$optstock = $this->goodsmodel->get_default_option($datarow['goods_seq']);

			$datarow['commission_rate']= $optstock['commission_rate'];
			$datarow['option_seq']= $optstock['option_seq'];
			$datarow['reserve_rate']= $optstock['reserve_rate'];
			$datarow['reserve_unit']= $optstock['reserve_unit'];
			$datarow['reserve']= $optstock['reserve'];

			$datarow['consumer_price']	= $optstock['consumer_price'];
			$datarow['price']			= $optstock['price'];
			$datarow['supply_price']	= $optstock['supply_price'];
			$datarow['default_stock']	= $optstock['stock'];
			$datarow['default_badstock']= $optstock['badstock'];

			$optstocktot = $this->goodsmodel->get_tot_option($datarow['goods_seq']);
			$datarow['stock']			= $optstocktot['stock'];
			$datarow['badstock']		= $optstocktot['badstock'];
			$datarow['rstock']			= $optstocktot['rstock'];
			$datarow['stocknothing']	= $optstocktot['stocknothing'];			//재고 0이하인 옵션갯수
			$datarow['rstocknothing']	= $optstocktot['rstocknothing'];		//가용재고 0이하인 옵션갯수

			//$datarow['catename']	= $this->categorymodel->get_category_name($datarow['category_code']);
			$reservation = $this->ordermodel->get_reservation_for_goods($cfg_order['ableStockStep'],$datarow['goods_seq']);
			$datarow['rstock'] = $datarow['stock'] - $reservation;

			unset($videosc);
			$videosc['tmpcode']= $datarow['videotmpcode'];
			$videosc['upkind']= 'goods';
			$videosc['type']= 'contents';
			$videocontentfirst = $this->videofiles->get_data($videosc);
			if($videocontentfirst) {
				$datarow['video_content_file_key_w']= $videocontentfirst['file_key_w'];
				$datarow['video_content_viewer_use']= $videocontentfirst['viewer_use'];
			}

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
			$datarow['options'][0] = $optstock;
			//$datarow['options']	= $this->goodsmodel->get_goods_option($datarow['goods_seq']);

			$loop['record'][$k] = $datarow;
		}

		$gd_search_arr = explode('&',$_COOKIE['goods_list_search']);
		foreach($gd_search_arr as $gd_search_data){
			$gd_search_arr2 = explode("=",$gd_search_data);
			if( strstr($gd_search_arr2[0],"goodsStatus") ){
				$gdsearchcookie['goodsStatus'][] = $gd_search_arr2[1];
			}elseif( strstr($gd_search_arr2[0],"goodsView") ){
				$gdsearchcookie['goodsView'][] = $gd_search_arr2[1];
			}elseif( strstr($gd_search_arr2[0],"taxView") ){
				$gdsearchcookie['taxView'][] = $gd_search_arr2[1];
			}else{
				$gdsearchcookie[$gd_search_arr2[0]] = $gd_search_arr2[1];
			}
		}

		$this->template->assign('gdsearchcookie',$gdsearchcookie);

		// 옵션 기본 노출 수량 적용
		$config_goods	= config_load('goods');

		//정렬
		$sorderby = $_GET['orderby'];
		$_GET['orderby'] = $sort."_".$_GET['orderby'];

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

	## 주의사항
	public function notice_pop(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## 연동 마켓 확인
	public function chk_linkagemall_status(){
		$goods_seq			= trim($_GET['goodsSeq']);
		$result				= array('status'=>false);

		// 판매마켓 설정 정보
		$linkage			= $this->openmarketmodel->get_linkage_config();
		if	($linkage){
			$this->load->model('openmarket/'.$linkage['linkage_id'].'model','linkagemodel');
			$this->linkagemodel->customer_id	= $linkage['linkage_code'];
			$mallproduct	= $this->linkagemodel->send_mallproduct($goods_seq);
			if	($mallproduct['count'] > 0){
				if	($mallproduct['list'])foreach($mallproduct['list'] as $mall){
					$malllist[$mall['mall_id']]		= $mall['mall_name'];
				}
				$result		= array('status'=>true,'mall'=>$malllist);
			}
		}

		echo json_encode($result);
	}

	## 전송대기 상품 검색
	public function src_ready_send_goods(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if	(!$this->config_system)	$this->get_config_system();
		$this->template->assign(array('shopSno'=>$this->config_system['shopSno']));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## 전송대기 상품 검색
	public function get_send_ready(){

		$url	= 'http://linkage1.firstmall.kr/get_send_ready.php?'.$_SERVER['QUERY_STRING'];

		$cu = curl_init();
		curl_setopt($cu, CURLOPT_URL, $url); // 데이터를 보낼 URL 설정
		curl_setopt($cu, CURLOPT_HEADER, FALSE);
		curl_setopt($cu, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($cu, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cu, CURLOPT_TIMEOUT,50); // REQUEST 에 대한 결과값을 받는 시간 설정.
		curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, 0); //
		curl_setopt($cu, CURLOPT_SSL_VERIFYHOST, 1); //
		$result		= curl_exec($cu);

		echo $result;
	}
}

/* End of file openmarket.php */
/* Location: ./app/controllers/admin/openmarket.php */