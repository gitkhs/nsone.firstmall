<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class coupon extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->admin_menu();
		$this->tempate_modules();
		$this->file_path	= $this->template_path();
		$this->load->model('couponmodel');
		$this->load->model('membermodel');
		$this->load->helper('coupon');

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		$ispoint		= $reserves['point_use'];//포인트 사용여부 설정
		$ispointurl	= '/admin/setting/reserve';//포인트설정페이지
		$this->template->assign('ispoint',$ispoint);
		$this->template->assign('ispointurl',$ispointurl);

		/* 회원 그룹 개발시 변경*/
		$groups = ""; 
		$grquery = $this->db->query("select group_seq,group_name  from fm_member_group order by order_sum_price desc, order_sum_ea desc, order_sum_cnt desc, use_type asc");// where group_seq != 1 
		if($grquery->result_array()) {
			foreach($grquery->result_array() as $row){
				$groups[] = $row; 
			}
		}  
		/**$grquery = $this->db->query("select group_seq,group_name from fm_member_group where group_seq != 1 ");
		foreach($grquery->result_array() as $row){
			$groups[] = $row;
		}**/
		/******************/
		$this->groups = $groups;
		$this->template->assign(array('groups'=>$groups));
		$this->template->define(array('tpl'=>$this->file_path));
		
		//쿠폰 사용 가능한 상품 확인하기 레이어
		$this->template->define(array('coupongoodslayer'=>$this->skin.'/coupon/coupongoodslayer.html'));

	}

	public function index()
	{
		redirect("/admin/coupon/catalog");
	}

	//쿠폰목록
	public function catalog()
	{
		### SEARCH
		$sc						= $_GET;
		if ($sc['search_text'])
		{
			$sc['search_text'] = trim($sc['search_text']);
			$sc['search_text']= stripslashes(htmlspecialchars($sc['search_text']));
		}
		$sc['orderby']		= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'coupon_seq';
		$sc['sort']				= (!empty($_GET['sort'])) ?			$_GET['sort']:'desc';
		$sc['page']			= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;

		if (($sc['couponType'])){
			if(gettype($sc['couponType']) == 'string' ) $sc['couponType'] = unserialize(urldecode($sc['couponType']));
			foreach ($sc['couponType'] as $v) {
				$checked['couponType'][$v] = "checked";
			}
		}
		if (($sc['use_type'])){
			if(gettype($sc['use_type']) == 'string' ) $sc['use_type'] = unserialize(urldecode($sc['use_type']));
			foreach ($sc['use_type'] as $v) {
				$checked['use_type'][$v] = "checked";
			}
		} 

		$this->template->assign('checked',$checked);
		$data = $this->couponmodel->coupon_list($sc);
		if(gettype($sc['couponType']) == 'array'){
			$_GET['couponType'] = urlencode(serialize($sc['couponType']));
		}
		### PAGE & DATA
		$sc['searchcount']		= $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']		= $this->couponmodel->get_item_total_count();

		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']		= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$datarow['date']			= substr($datarow['regist_date'],2,14); 
			$datarow['limit_goods_price_title'] = ( $datarow['type'] == 'offline_emoney' || $datarow['type'] == 'point')?"-":number_format($datarow['limit_goods_price']);
			$datarow['issue_stop_title']	= ($datarow['issue_stop']=='1') ? "<span class='red bold'>중지</span>" : "발급";

			if( $datarow['type'] == 'offline_emoney' ||  $datarow['use_type'] == 'offline'  ){ 
				$datarow['coupon_same_time_title']	= " - ";
				$datarow['issue_type_title']				= " - ";
				$datarow['sale_payment_title']			= " - ";
				$datarow['sale_referer_title']				= " - ";
			}else{
				$datarow['coupon_same_time_title']	= ($datarow['coupon_same_time']=='N') ? "단독" : "동시";
				$datarow['issue_type_title']	= ($datarow['issue_type']=='issue' || $datarow['issue_type']=='except') ? "제한" : "전체";
				$datarow['sale_payment_title']	= ($datarow['sale_payment']=='b') ? "무통장" : "X";
				$datarow['sale_referer_title']	= ($datarow['sale_referer']=='n' || $datarow['sale_referer']=='y') ? "제한" : "무관";
			}


			if($datarow['type'] == 'download' || $datarow['type'] == 'mobile' || ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) || $datarow['type'] == 'offline_coupon' || $datarow['type'] == 'offline_emoney' ){//다운로드/배송비
				$datarow['downloaddate']	= ($datarow['download_startdate'] && $datarow['download_enddate'])?substr($datarow['download_startdate'],2,8).' ~ '.substr($datarow['download_enddate'],2,8):'기간제한없음';
			}elseif($datarow['type'] == 'birthday'){//생일자
				$datarow['downloaddate']	= '생일 '.$datarow['before_birthday'].'일전 ~ '.$datarow['after_birthday'].'일이후까지';
			}elseif($datarow['type'] == 'anniversary'){//기념일
				$datarow['downloaddate']	= '기념일 '.$datarow['before_anniversary'].'일전 ~ '.$datarow['after_anniversary'].'일이후까지';
			}elseif( strstr($datarow['type'],'memberGroup') ){//회원등급 'memberGroup'   'memberGroup_shipping' 
				$datarow['downloaddate']	= '등급조정일로부터 '. ($datarow['after_upgrade']).'일';
			}elseif($datarow['type'] == 'point'){//전환포인트
				$datarow['downloaddate']	= number_format($datarow['coupon_point']).'포인트로 쿠폰 교환';
			}else{
				$datarow['downloaddate']	= '-';
			}
			
			//유효기간항목
			if($datarow['type'] == 'offline_emoney' ) {
				$datarow['issuedate']	= ' - ';
			}else{
				if( $datarow['issue_priod_type'] == 'months' ) {
					$datarow['issuedate']	= '해당월 말일';
				}elseif( $datarow['issue_priod_type'] == 'date' ) {
					$datarow['issuedate']	= $datarow['issue_startdate'].' <br/> '.$datarow['issue_enddate'];
				}else{
					$datarow['issuedate']	= '발급일~'.number_format($datarow['after_issue_day']).'일';
				}
			}
			
			//혜택
			if(( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') )){//배송비
				$datarow['salepricetitle']	= ($datarow['shipping_type'] == 'free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
			}elseif($datarow['use_type'] == 'offline' ){
				$datarow['salepricetitle']	= getstrcut($datarow['benefit'], 15);
			}elseif($datarow['type'] == 'offline_emoney' ){//오프라인 적립금쿠폰
				$datarow['salepricetitle']	='적립금 '.number_format($datarow['offline_emoney']).'원 지급';
			}else{
				$datarow['salepricetitle']	= ($datarow['sale_type'] == 'percent' ) ? $datarow['percent_goods_sale'].'% 할인, (최대 '.number_format($datarow['max_percent_goods_sale']).'원)': '판매가격의 '.number_format($datarow['won_goods_sale']).'원';
			}

			$dsc['whereis'] = ' and coupon_seq='.$datarow['coupon_seq'];
			$downloadtotal = $this->couponmodel->get_download_total_count($dsc);
			$datarow['downloadtotal']	= number_format($downloadtotal);//발급수

			$usc['whereis'] = ' and coupon_seq='.$datarow['coupon_seq'].' and use_status = \'used\' ';
			$usetotal = $this->couponmodel->get_download_total_count($usc);

			$datarow['usetotal']	= number_format($usetotal);//사용건수
			$datarow['issueimg']	= 'online';
			if(strstr($datarow['type'],'offline')){
				//$datarow['use_type'] = '온라인에서<br/>인증 후 사용';
				$datarow['issueimg'] = 'print';
			}
			if( $datarow['type'] == 'admin' || $datarow['type'] == 'admin_shipping' ){//직접발급시
				$datarow['issuebtn']	= (( $datarow['issue_priod_type'] == 'date' && str_replace("-","", substr($datarow['issue_enddate'],0,10)) < date("Ymd"))) ? $this->couponmodel->couponTypeTitle[$datarow['type']]:$this->couponmodel->couponTypeTitle[$datarow['type']].' <span class="btn small cyanblue"><button type="button" class="downloa_write_btn" coupon_seq="'.$datarow['coupon_seq'].'" download_limit="'.$datarow['download_limit'].'" coupon_name="'.$datarow['coupon_name'].'" >발급하기</button></span>';
			}else{
				$datarow['issuebtn']	=$this->couponmodel->couponTypeTitle[$datarow['type']];
				if( $datarow['use_type']=='offline' ) $datarow['issuebtn'].=" (매장)";
			} 
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],getPageUrl($this->file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign(array('perpage'=>$sc['perpage'],'orderby'=>$sc['orderby']));
		$this->template->assign('sc',$sc);

		$member_total_count = $this->membermodel->get_item_total_count();
		$member_total_count = number_format($member_total_count);
		$this->template->assign('member_total_count',$member_total_count); 

		$this->template->print_("tpl");
	}

	//쿠폰적용 상품조회
	public function coupongoodsreviewer()
	{
		if( $_GET['download_seq'] ){
			$this->coupondown = true;
		}
		
		$no = (int) $_GET['no'];
		if($this->coupondown) {
			$this->couponinfo 	= $this->couponmodel->get_download_coupon($no);
		}else{
			$this->couponinfo 	= $this->couponmodel->get_coupon($no);
		}

		if( $this->couponinfo['coupon_type'] == 'offline' ) {
			$this->offline();
		}else{
			$this->online();
		} 
	}

	public function online()
	{ 
		if(isset($_GET['no'])) {
			$no = (int) $_GET['no'];


			$this->load->model('goodsmodel');
			$this->load->model('categorymodel');

			if($this->coupondown) {
				$coupons 		= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_download_coupon($no); 
				$coupons['coupondown'] = $this->coupondown;
			}else{
				$coupons 			= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_coupon($no);
			}

			if( $coupons['type'] == 'mobile' ) {//기존 모바일쿠폰제외
				$coupons['type']				= 'download';//상품쿠폰으로 대체
				//$coupons['sale_agent']	= ($coupons['sale_agent']!= 'm')?'m':'';//사용환경 모바일로 대체
			}
			if (!isset($coupons['coupon_seq'])) pageBack('잘못된 접근입니다.');
			$couponGroups 	= $this->couponmodel->get_coupon_group($no); 
			if($this->coupondown) {
				$issuegoods 	= $this->couponmodel->get_coupon_download_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_download_issuecategory($no);
			}else{
				$issuegoods 	= $this->couponmodel->get_coupon_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_issuecategory($no);
			}
			if($couponGroups){
				foreach($this->groups as $tmp){
					foreach($couponGroups as $key => $group){
						if($tmp['group_seq'] == $group['group_seq']){ 
							$couponGroups[$key]['group_name'] = $tmp['group_name']; 
							$couponGroupsNew[] = $couponGroups[$key];
						}
					}
				} 
				$this->template->assign(array('couponGroups'=>$couponGroupsNew));
			}

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

			$dsc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'];
			$downloadtotal = $this->couponmodel->get_download_total_count($dsc);
			$coupons['downloadtotal']	= 0;//발급수-> 수정가능하도록 수정@2012-06-08
			$coupons['downloadtotalbtn']	= number_format($downloadtotal);

			$usc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'].' and use_status = \'used\' ';
			$usetotal = $this->couponmodel->get_download_total_count($usc);
			$coupons['usetotalbtn']	= number_format($usetotal);//사용건수

			// 기간이 있을경우 -> 시간 가공 후 처리
			if($coupons['download_startdate'])	$coupons['download_starthour']	= date('H', strtotime($coupons['download_startdate']));
			if($coupons['download_startdate'])	$coupons['download_startmin']	= date('i', strtotime($coupons['download_startdate']));
			if($coupons['download_startdate'])	$coupons['download_startdate']	= date('Y-m-d', strtotime($coupons['download_startdate']));
			if($coupons['download_enddate'])	$coupons['download_endhour']	= date('H', strtotime($coupons['download_enddate']));
			if($coupons['download_enddate'])	$coupons['download_endmin']		= date('i', strtotime($coupons['download_enddate']));
			if($coupons['download_enddate'])	$coupons['download_enddate']	= date('Y-m-d', strtotime($coupons['download_enddate']));

			if($coupons['download_starttime'])	$coupons['download_starttime_h']= date('H', strtotime($coupons['download_starttime']));
			if($coupons['download_starttime'])	$coupons['download_starttime_m']= date('i', strtotime($coupons['download_starttime']));
			if($coupons['download_endtime'])	$coupons['download_endtime_h']	= date('H', strtotime($coupons['download_endtime']));
			if($coupons['download_endtime'])	$coupons['download_endtime_m']	= date('i', strtotime($coupons['download_endtime']));

			if( $this->coupondown ) {
				$todayck = date("Y-m-d",time()); 
				if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
					$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
					$coupons['issuedaylimituse'] = true;
				}else{  
					$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
				} 
			}else{
				if( $coupons['issue_priod_type'] == 'date') { 
					$todayck = date("Y-m-d",time()); 
					$coupons['issuedaylimit'] = 0; 
					if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
						$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
						$coupons['issuedaylimituse'] = true;
					}else{  
						$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
					} 
				}
			}
			
			if($coupons['download_week']){
				$downweek = "";

				if(strpos($coupons['download_week'],'1') > 0)	$downweek .= ",월";
				if(strpos($coupons['download_week'],'2') > 0)	$downweek .= ",화";
				if(strpos($coupons['download_week'],'3') > 0)	$downweek .= ",수";
				if(strpos($coupons['download_week'],'4') > 0)	$downweek .= ",목";
				if(strpos($coupons['download_week'],'5') > 0)	$downweek .= ",금";
				if(strpos($coupons['download_week'],'6') > 0)	$downweek .= ",토";
				if(strpos($coupons['download_week'],'7') > 0)	$downweek .= ",일";

				$downweek = substr($downweek,1,strlen($downweek));
				$coupons['download_enddatetitle_week'] = $downweek . " 요일 다운가능";
			} 
			$coupons['type_title'] = $this->couponmodel->couponTypeTitle[$coupons['type']];
			$this->template->assign(array('coupons'=>$coupons));
		}
		//debug_var($coupons);

		if( $coupons['type'] == 'admin' || $coupons['type'] == 'admin_shipping' ){//직접발급시
			$adminissuebtn	= (( $coupons['issue_priod_type'] == 'date' && str_replace("-","", substr($coupons['issue_enddate'],0,10)) < date("Ymd"))) ? false:true;
			$this->template->assign(array('adminissuebtn'=>$adminissuebtn));
		}

		$this->load->model('referermodel');
		$referersaleloop			= $this->referermodel->get_referersale_all('');  
		$this->template->assign(array('referersaleloop'=>$referersaleloop));
		$salerefereritem = explode(",",$coupons['sale_referer_item']); 
		unset($salserefereritemloop);
		foreach($salerefereritem as $key=>$sale_referer_item_val ) {  
			if(!$sale_referer_item_val)continue;
			foreach($referersaleloop as $referersale ) {
				if( !in_array($salserefereritemloopa,$sale_referer_item_val) && $referersale['referersale_seq'] == $sale_referer_item_val ) { 
					$salserefereritemloopa[] = $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_seq']		= $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_name']	= $referersale['referersale_name'];
				}
			}
		} 
		if($salserefereritemloop) $this->template->assign(array('salserefereritemloop'=>$salserefereritemloop));

		$couponfilename = getcouponpagepopup($coupons);
		$this->template->assign('couponfilename',$couponfilename); 

 
		$this->template->assign('query_string',$_GET['query_string']);
		if( !$this->coupongoodsreviewer ) {  
			$this->template->define(array('onlinecoupontypelayer' => $this->skin.'/coupon/onlinecoupontype.html')); 
			$this->template->assign(array('membertypedisabled'=>$membertypedisabled));
			$this->template->print_("tpl");
		}
	}

	//코드보기
	public function online_code() 
	{
		$coupons  = array("type"=>$_GET['type']);
		$coupons['type_title'] = $this->couponmodel->couponTypeTitle[$coupons['type']];
		$couponpopupuse = config_load('couponpopupuse',$_GET['type'].'_popup_use');
		$coupons['popup_use'] = $couponpopupuse[$_GET['type'].'_popup_use'];
		$this->template->assign(array('coupons'=>$coupons));
		$couponfilename = getcouponpagepopup($coupons); 
		$this->template->assign('couponfilename',$couponfilename); 

		$this->load->helper('file');
		$codeall= str_replace("/online_code.html","/codeall.html",$this->file_path);
		$tpl_source_all = read_file(ROOTPATH."admin/skin/".$codeall);		
		$codeall_mobile= str_replace("/online_code.html","/codeall_mobile.html",$this->file_path);
		$tpl_source_all_mobile = read_file(ROOTPATH."admin/skin/".$codeall_mobile);

		//$codeone= str_replace("/online_code.html","/code.html",$this->file_path);
		//$tpl_source = read_file(ROOTPATH."admin/skin/".$codeone);
		//$codeone_mobile= str_replace("/online_code.html","/code_mobile.html",$this->file_path);
		//$tpl_source_mobile = read_file(ROOTPATH."admin/skin/".$codeone_mobile);
 
		$coupondowntarget = coupondowntargethtml($_GET['type']);
		//$tpl_source = str_replace("{다운대상기간치환}",$coupondowntarget,str_replace("{쿠폰유형}",$_GET['type'],str_replace("{쿠폰고유번호}",$_GET['no'],$tpl_source))); 
		//$tpl_source_mobile = str_replace("{다운대상기간치환}",$coupondowntarget,str_replace("{쿠폰유형}",$_GET['type'],str_replace("{쿠폰고유번호}",$_GET['no'],$tpl_source_mobile)));
		$tpl_source_all = str_replace("{다운대상기간치환}",$coupondowntarget,str_replace("{쿠폰유형}",$_GET['type'],str_replace("{쿠폰고유번호}",$_GET['no'],$tpl_source_all))); 
		$tpl_source_all_mobile = str_replace("{다운대상기간치환}",$coupondowntarget,str_replace("{쿠폰유형}",$_GET['type'],str_replace("{쿠폰고유번호}",$_GET['no'],$tpl_source_all_mobile))); 

		$this->template->assign(array('couponcodeallhtml'=>$tpl_source_all,'couponcodehtml'=>$tpl_source,'couponcodeallhtml_mobile'=>$tpl_source_all_mobile,'couponcodehtml_mobile'=>$tpl_source_mobile)); 
		$this->template->print_("tpl");
	}

	public function codeviewer() 
	{
		$this->load->helper('file');
		$codeall= str_replace("/codeviewer.html","/codeall.html",$this->file_path);
		$codeone= str_replace("/codeviewer.html","/code.html",$this->file_path);
		if( $_GET['type'] == 'all' ) {
			$tpl_source = read_file(ROOTPATH."admin/skin/".$codeall);
		}else{
			$tpl_source = read_file(ROOTPATH."admin/skin/".$codeone);
		}
		if(isset($_GET['no'])) $tpl_source = str_replace("{프로모션고유번호}",$_GET['no'],$tpl_source); 
		$this->template->assign(array('couponcodehtml'=>$tpl_source)); 
		//$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function offline()
	{
		if(isset($_GET['no'])) {
			$no = (int) $_GET['no'];
			$this->load->model('goodsmodel');
			$this->load->model('categorymodel');

			if($this->coupondown) {
				$coupons 		= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_download_coupon($no); 
				$coupons['coupondown'] = $this->coupondown;
			}else{
				$coupons 			= ($this->couponinfo)?$this->couponinfo:$this->couponmodel->get_coupon($no);
			}
			if (!isset($coupons['coupon_seq'])) pageBack('잘못된 접근입니다.');
			$couponGroups 		= $this->couponmodel->get_coupon_group($no);
			if($this->coupondown) {
				$issuegoods 	= $this->couponmodel->get_coupon_download_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_download_issuecategory($no);
			}else{
				$issuegoods 	= $this->couponmodel->get_coupon_issuegoods($no);
				$issuecategorys	= $this->couponmodel->get_coupon_issuecategory($no);
			}
			if($couponGroups){
				foreach($couponGroups as $key => $group){
					foreach($this->groups as $tmp){
						if($tmp['group_seq'] == $group['group_seq']){
							$couponGroups[$key]['group_name'] = $tmp['group_name'];
						}
					}
				}
				$this->template->assign(array('couponGroups'=>$couponGroups));
			}

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

			$coupons['download_startdate']	= substr($coupons['download_startdate'], 0, 10);
			$coupons['download_enddate']	= substr($coupons['download_enddate'], 0, 10);

			$dsc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'];
			$downloadtotal = $this->couponmodel->get_download_total_count($dsc);
			$coupons['downloadtotal']	= 0;//발급수-> 수정가능하도록 수정@2012-06-08
			$coupons['downloadtotalbtn']	= number_format($downloadtotal);

			$usc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'].' and use_status = \'used\' ';
			$usetotal = $this->couponmodel->get_download_total_count($usc);
			$coupons['usetotalbtn']	= number_format($usetotal);//사용건수

			if($coupons['offline_type'] == 'file'){//엑셀등록인 경우
				$coupons['offlinecoupontotal'] = $this->couponmodel->get_offlinecoupon_input_item_total_count($coupons['coupon_seq']);
			}else{
				$coupons['offlinecoupontotal'] = $this->couponmodel->get_offlinecoupon_item_total_count($coupons['coupon_seq']);
			}
			
			if( $this->coupondown ) {
				$todayck = date("Y-m-d",time()); 
				if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
					$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
					$coupons['issuedaylimituse'] = true;
				}else{  
					$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
					$coupons['issuedaylimit'] = $issuedaylimit;
				} 
			}else{
				if( $coupons['issue_priod_type'] == 'date') { 
					$todayck = date("Y-m-d",time()); 
					$coupons['issuedaylimit'] = 0; 
					if( $coupons['issue_enddate'] >= date("Y-m-d") ) { 
						$issuedaylimit = intval((strtotime($coupons['issue_enddate'])-strtotime($todayck)) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
						$coupons['issuedaylimituse'] = true;
					}else{  
						$issuedaylimit = intval((strtotime($todayck)-strtotime($coupons['issue_enddate'])) / 86400); 
						$coupons['issuedaylimit'] = $issuedaylimit;
					} 
				}
			}
			$this->template->assign(array('coupons'=>$coupons));
		}
		
		$this->load->model('referermodel');
		$referersaleloop			= $this->referermodel->get_referersale_all();  
		$this->template->assign(array('referersaleloop'=>$referersaleloop));
		$salerefereritem = explode(",",$coupons['sale_referer_item']); 
		unset($salserefereritemloop);
		foreach($salerefereritem as $key=>$sale_referer_item_val ) {  
			if(!$sale_referer_item_val)continue;
			foreach($referersaleloop as $referersale ) {
				if( !in_array($salserefereritemloopa,$sale_referer_item_val) && $referersale['referersale_seq'] == $sale_referer_item_val ) { 
					$salserefereritemloopa[] = $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_seq']		= $sale_referer_item_val;
					$salserefereritemloop[$key]['referersale_name']	= $referersale['referersale_name'];
				}
			}
		} 
		if($salserefereritemloop) $this->template->assign(array('salserefereritemloop'=>$salserefereritemloop));

		$this->template->define(array('offlinecoupontypelayer' => $this->skin.'/coupon/offlinecoupontype.html'));
		
		$this->template->assign('query_string',$_GET['query_string']);
		if( !$this->coupongoodsreviewer ) {  
			$this->template->assign(array('membertypedisabled'=>$membertypedisabled));
			$this->template->assign("offline_coupon_form","/data/coupon/offline_coupon_form.xls");
			$this->template->print_("tpl");
		}
	}

	//인쇄용쿠폰 > 엑셀등록하기
	public function offline_excel()
	{
		$coupon_seq = (int) $_GET['no'];
		$coupons 		= $this->couponmodel->get_coupon($coupon_seq);
		$this->template->assign(array('coupons'=>$coupons));
		$this->template->assign('saveinterval',3);//3초 대기
		$this->template->print_("tpl");
	}

	//인쇄용쿠폰 > 인증번호 보기
	public function offline_coupon()
	{
		$coupon_seq = (int) $_GET['no'];
		$coupons 		= $this->couponmodel->get_coupon($coupon_seq);
		$this->template->assign(array('coupons'=>$coupons));
		$this->template->print_("tpl");
	}

	//발급내역
	public function download()
	{
		$no = (int) $_GET['no'];
		$coupons 		= $this->couponmodel->get_coupon($no);
		$coupons['downloaddatetitle'] = (strstr($coupons['type'],'offline'))?'인증일/인증번호':'발급일';
		$this->template->assign(array('coupons'=>$coupons));
		$this->template->print_("tpl");
	}

	//쿠폰발급 > 회원검색페이지
	public function download_member()
	{
		$no = (int) $_GET['no'];
		$coupons 		= $this->couponmodel->get_coupon($no);
		$this->template->assign(array('coupons'=>$coupons));

		### GROUP
		$this->load->model('membermodel');
		$group_all = $this->membermodel->find_group_list();
		$coupongroups 	= $this->couponmodel->get_coupon_group($no);
		if($coupongroups){
			$i =0;
			foreach($coupongroups as $key => $group){
				foreach($group_all as $tmp){
					if($tmp['group_seq'] == $group['group_seq']){
						$group_arr[$i]['group_seq'] = $tmp['group_seq'];
						$group_arr[$i]['group_name'] = $tmp['group_name'];
					}
				}$i++;
			}
		}else{
			$group_arr = $group_all;
		}
		$this->template->assign('group_arr',$group_arr);
		$this->template->print_("tpl");
	}


	//쿠폰발급 > 회원검색리스트
	public function download_member_list()
	{
		$no = (int) $_POST['no'];
		$coupons 		= $this->couponmodel->get_coupon($no);
		$this->template->assign(array('coupons'=>$coupons));

		$coupongroups 	= $this->couponmodel->get_coupon_group($no);
		$coupongroupsar = '';
		if($coupongroups){
			foreach($coupongroups as $key => $group){
				$coupongroupsar[] = $group['group_seq'];
			}
		}

		$this->load->model('membermodel');

		### SEARCH
		$sc = $_POST;
		$sc['search_text']		= ($sc['search_text'] == '이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소') ? '':$sc['search_text'];
		$sc['keyword']				= $sc['search_text'];
		$sc['orderby']		= 'A.member_seq';
		$sc['sort']				= (isset($_POST['sort'])) ?		$_POST['sort']:'desc';
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):10;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;
		$sc['groupsar']		= $coupongroupsar;

		### MEMBER
		$data = $this->membermodel->admin_member_list($sc);//popup_member_list($sc);
		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount =  get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);
		$sc['totalcount']		= $this->membermodel->get_item_total_count();

		$idx = 0;
		$html = $this->getdownload_member_html($data, $sc,  $page, $coupons);
		if(!empty($html)) {
			$result = array( 'content'=>$html, 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"", 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		echo json_encode($result);
		exit;
	}

	//회원검색 > 발급내역
	function getdownload_member_html($data, $sc, $page, $coupons)
	{
		$html = '';
		$i = 0;
		foreach($data['result'] as $datarow){
			// 쿠폰 정보 확인
			$download_coupons = $this->couponmodel->get_admin_download($datarow['member_seq'], $coupons['coupon_seq']);
			$class = ($download_coupons)?" class='bg-gray' ":"";

			$datarow['number'] = $data['count'] - ( ( $page -1 ) * $sc['perpage'] + $i + 1 ) + 1;
			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';
            if($datarow['user_name'] == ""){
                $datarow['user_name'] = $datarow['bname'];
                $datarow['address'] = $datarow['baddress'];
                $datarow['address_detail'] = $datarow['baddress_detail'];
                $datarow['phone'] = $datarow['bphone'];
                $datarow['cellphone'] = $datarow['bcellphone'];
                $datarow['zipcode'] = $datarow['bzipcode'];
            }
			$html .= '<tr  '.$class.' >';
			if($download_coupons) {
				$html .= '	<td  class="its-td-align center"> </td>';
			}else{
				$html .= '	<td  class="its-td-align center"><input type="checkbox" onclick="chkmember(this);" name="member_chk[]" value="'.$datarow['member_seq'].'" cellphone="'.$datarow['cellphone'].'" email="'.$datarow['email'].'"  userid="'.$datarow['userid'].'"  user_name="'.$datarow['user_name'].'"  class="member_chk" '.$disabled.'/></td>';
			}
			$html .= '	<td class="its-td-align center">'.$datarow['number'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['type'].'</td>';
			$html .= '	<td class="its-td-align center bold"><div class="bold">'.$datarow['userid'].'</div></td>';
			$html .= '	<td class="its-td-align center">'.$datarow['user_name'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['email'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['cellphone'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['phone'].'</td>';
			$html .= '</tr>';
			$i++;
		}//foreach end

		if($i==0){
			if($sc['search_text']){
				$html .= '<tr ><td class="its-td-align center" colspan="8" >"'.$sc['search_text'].'"로(으로) 검색된 회원이 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" colspan="8" >회원이 없습니다.</td></tr>';
			}
		}
		return $html;
	}

	//쿠폰관리 > 발급내역 -> 검색 : 사용여부 , 사용일, 주문상품(주문번호) 또는 적립금 지급
	public function downloadlist()
	{
		$no = (int) $_POST['no'];
		$coupons 		= $this->couponmodel->get_coupon($no);
		$this->template->assign(array('coupons'=>$coupons));

		### SEARCH
		$sc						= $_POST;
		$sc['search_text']		= ($sc['search_text'] == '아이디, 이름') ? '':$sc['search_text'];
		$sc['orderby']		= (!empty($_POST['orderby'])) ?	$_POST['orderby']:'download_seq';
		$sc['sort']				= (!empty($_POST['sort'])) ?			$_POST['sort']:'desc';
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):10;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;

		$data = $this->couponmodel->download_list($sc);

		//발급내역 > 총 할인금액추출
		$coupon_sale = $this->couponmodel->get_coupontotal($sc, $coupons);

		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount = get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	= $data['count'];
		$sc['total_page']	= @ceil($sc['searchcount']/ $sc['perpage']);
		$sc['totalcount']	= $this->couponmodel->get_download_item_total_count($no);

		$idx = 0;
		$html = $this->getdownloadhtml($data, $sc,  $page);
		if(!empty($html)) {
			$result = array( 'content'=>$html,'totalsaleprcie'=>$coupon_sale['coupon_sale'], 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"",'totalsaleprcie'=>$coupon_sale['coupon_sale'],  'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		echo json_encode($result);
		exit;
	}

	//쿠폰관리 > 발급내역
	function getdownloadhtml($data, $sc, $page)
	{
		$this->load->model('ordermodel');
		$html = '';
		$i = 0;

		foreach($data['result'] as $datarow){
			$datarow['number'] = $data['count'] - ( ( $page -1 ) * $sc['perpage'] + $i + 1 ) + 1;
			$datarow['date']			= substr($datarow['regist_date'],2,14);//등록일
			$datarow['use_status_title'] = ($datarow['use_status'] == 'used') ? '<span class="blue" >사용함</span>':'<span class="red" >미사용</span>';
			if(str_replace("-","",$datarow['issue_enddate']) < date("Ymd") && $datarow['use_status'] != 'used') $datarow['use_status_title'] = '<span class="gray" >소멸함</span>';//미사용중 기간지남
			$deletebtn = ($datarow['use_status'] == 'used')?' disabled="disabled" ':'';//

			$datarow['use_date']			  = ($datarow['use_status'] == 'used') ? substr($datarow['use_date'],2,14):'';

			if($datarow['use_status'] == 'used') {
				if ( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ) {
					unset($order_coupon,$items);
					$order_coupon = $this->ordermodel->get_order_shipping_coupon($datarow['member_seq'], $datarow['download_seq']);
					if($order_coupon['order_seq']) $items 				 = $this->ordermodel->get_item($order_coupon['order_seq']);
					$goods_cnt = count($items)-1;
					if($items){
						$goodsinfo = ($goods_cnt > 0) ? '<img src="'.$items[0]['image'].'" /> <br /><span class="goods_name1">'.$items[0]['goods_name'].'</span> 외'.$goods_cnt.'건':'<img src="'.$items[0]['image'].'" /> <br /><span class="goods_name1 orderview bold blue"  onclick="orderinfo(\''.$order_coupon['order_seq'].'\');"  goods_seq="'.$items[0]['goods_seq'].'" >'.$items[0]['goods_name'].'</span>';

						$datarow['goodsview'] = '<span class="goods_name1 hand orderview bold blue" onclick="orderinfo(\''.$order_coupon['order_seq'].'\');"order_seq="'.$order_coupon['order_seq'].'" >['.$order_coupon['order_seq'].']</span><br/>'.$goodsinfo;
					}else{
						$datarow['goodsview'] = "";
					}
					$datarow['coupon_order_saleprice'] = number_format($order_coupon['coupon_sale']).'원&nbsp;';
				} else {
					if ($datarow['type'] == 'offline_emoney') {
						$datarow['goodsview'] = '적립금 '.number_format($datarow['offline_emoney']).'원 지급';
					}else{
						unset($order_coupon);
						$order_coupon = $this->ordermodel->get_option_coupon_item($datarow['member_seq'], $datarow['download_seq']);
						$datarow['goodsview'] = ($order_coupon[0]['order_seq'])?'<span class="goods_name1 hand orderview bold blue" onclick="orderinfo(\''.$order_coupon[0]['order_seq'].'\');" order_seq="'.$order_coupon[0]['order_seq'].'" >['.$order_coupon[0]['order_seq'].']</span><br/><img src="'.$order_coupon[0]['image'].'" /> <br /><span class="goods_name1 hand goodsview bold blue" onclick="goodsinfo(\''.$order_coupon[0]['goods_seq'].'\');"  goods_seq="'.$order_coupon[0]['goods_seq'].'" >'.$order_coupon[0]['goods_name'].'</span>':'';
						$datarow['coupon_order_saleprice'] = number_format($order_coupon[0]['coupon_order_saleprice']).'원&nbsp;';
					}
				}
			}

			if($datarow['use_type'] == 'offline')	$datarow['goodsview'] = '오프라인 상품';

			if((strstr($datarow['type'],'offline'))){
				$datarow['datetitle'] = $datarow['date'].'<br>'.$datarow['offline_input_serialnumber'];
			}else{
				$datarow['datetitle'] = $datarow['date'];
			}

			if ($datarow['type'] != 'offline_emoney') { 
				$datarow['limit_goods_price_title'] = number_format($datarow['limit_goods_price']).'원';//제한금액 이상&nbsp;
			}
			
			if( $datarow['type'] == 'offline_emoney' ||  $datarow['use_type'] == 'offline'  ){ 
				$datarow['coupon_same_time_title']	= " - ";
				$datarow['issue_type_title']					= " - ";
				$datarow['sale_payment_title']			= " - ";
				$datarow['sale_referer_title']				= " - ";
				$datarow['sale_agent_title']					= " - ";
				$datarow['limit_title']							= ' - '; 
			}else{
				$datarow['coupon_same_time_title']	= ($datarow['coupon_same_time']=='N') ? "단독" : "동시";
				$datarow['issue_type_title']					= ($datarow['issue_type']=='issue' || $datarow['issue_type']=='except') ? "제한" : "전체";
				$datarow['sale_payment_title']			= ($datarow['sale_payment']=='b') ? "무통장" : "X";
				$datarow['sale_referer_title']				= ($datarow['sale_referer']=='n' || $datarow['sale_referer']=='y') ? "제한" : "무관";
				$datarow['sale_agent_title']					= ($datarow['sale_agent']=="m") ? 'Mobile' : "X";//<img src="/images/common/icon_mobile.gif" > 
				$datarow['limit_title']							= $datarow['coupon_same_time_title'].'/'.$datarow['limit_goods_price_title'].'/'.$datarow['issue_type_title'].'/'.$datarow['sale_agent_title'].'/'.$datarow['sale_payment_title'].'/'.$datarow['sale_referer_title']; 
			}

			$datarow['issuedate']	= substr($datarow['issue_startdate'],2,10).' <br/> '.substr($datarow['issue_enddate'],2,10);//유효기간

			//혜택
			if( ( $datarow['type'] == 'shipping' || strstr($datarow['type'],'_shipping') ) ){//배송비
				$datarow['salepricetitle']	= ($datarow['shipping_type'] == 'free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
			}elseif($datarow['type'] == 'offline_emoney' ){//오프라인 적립금쿠폰
				$datarow['salepricetitle']	='적립금 '.number_format($datarow['offline_emoney']).'원 지급';
			}else{
				$datarow['salepricetitle']	= ($datarow['sale_type'] == 'percent' ) ? $datarow['percent_goods_sale'].'% 할인, <br/>최대 '.number_format($datarow['max_percent_goods_sale']).'원': number_format($datarow['won_goods_sale']).'원 할인';
			}
            if($datarow['user_name'] == ""){
                $datarow['user_name'] = $datarow['bname'];
            }

			if( $datarow['type'] == 'offline_emoney' ) {
				 $datarow['couponinfobtn'] = "-";
			}else{
				if( $datarow['type'] == 'offline_coupon' ) {
					$coupon_type = "offline";
				}else{
					$coupon_type = "online";
				} 
				$datarow['couponinfobtn'] = '<span class="btn small gray "><input type="button" class="coupongoodsreviewbtnpopup" coupon_type="'.$coupon_type.'" coupon_seq="'.$datarow['coupon_seq'].'" download_seq="'.$datarow['download_seq'].'"  use_type="'.$datarow['use_type'].'"  issue_type="'.$datarow['issue_type'].'"   coupon_name="'.$datarow['coupon_name'].'" value="조회" /></span>';
			}
			$html .= '<tr>';
			$html .= '	<td class="its-td-align center"><input type="checkbox" name="del[]" value="'.$datarow['download_seq'].'"  class="checkeds"  '.$deletebtn.'/></td>';
			$html .= '	<td class="its-td-align center">'.$datarow['number'].'</td>';
			$html .= '	<td class="its-td-align center"><span class=" userinfo hand bold blue"  onclick="userinfo(\''.$datarow['member_seq'].'\');"  mid="'.$datarow['userid'].'" mseq="'.$datarow['member_seq'].'" >'.$datarow['userid'].'</span></td>';
			$html .= '	<td class="its-td-align center bold"><span class=" userinfo hand bold blue"  onclick="userinfo(\''.$datarow['member_seq'].'\');"   mid="'.$datarow['userid'].'" mseq="'.$datarow['member_seq'].'" >'.$datarow['user_name'].'</span></td>';
			$html .= '	<td class="its-td-align center">'.$datarow['datetitle'].'</td>';
			if($datarow['use_type'] == 'online')
				$html .= '	<td class="its-td-align center">'.$datarow['limit_title'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['issuedate'].'</td>';
			if($datarow['use_type'] == 'online'){
				$html .= '	<td class="its-td-align center">'.$datarow['salepricetitle'].'</td>';
				$html .= '	<td class="its-td-align right">'.$datarow['coupon_order_saleprice'].'</td>';
			}
			$html .= '	<td class="its-td-align center">'.$datarow['couponinfobtn'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['use_status_title'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['goodsview'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['use_date'].'</td>';
			$html .= '</tr>';
			$i++;
		}//foreach end

		if($i==0){
			if($sc['search_text']){
				$html .= '<tr ><td class="its-td-align center" colspan="13" >"'.$sc['search_text'].'"로(으로) 검색된 쿠폰내역이 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" colspan="13" >발급내역이 없습니다.</td></tr>';
			}
		}
		return $html;
	}

	//관리자 > 회원 쿠폰 보유/다운가능내역
	public function member_coupon_list(){

		$this->load->model('ordermodel');
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->mdata = $this->membermodel->get_member_data($_GET['member_seq']);//회원정보 
		
		if( !empty($this->mdata['birthday']) && $this->mdata['birthday'] != '0000-00-00' ) {
			$this->mdata['thisyear_birthday'] = date("Y").substr($this->mdata['birthday'],4,6);
			if(checkdate(substr($this->mdata['thisyear_birthday'],5,2),substr($this->mdata['thisyear_birthday'],8,2),substr($this->mdata['thisyear_birthday'],0,4)) != true) {
				$this->mdata['thisyear_birthday'] = date("Y-m-d",strtotime('-1 day', strtotime($this->mdata['thisyear_birthday'])));
			} 
		}

		if ( !empty($this->mdata['anniversary']) ) {
			$this->mdata['thisyear_anniversary'] = date("Y").'-'.$this->mdata['anniversary'];//기념일(mm-dd) 추가
			if(checkdate(substr($this->mdata['thisyear_anniversary'],5,2),substr($this->mdata['thisyear_anniversary'],8,2),substr($this->mdata['thisyear_anniversary'],0,4)) != true) {
				$this->mdata['thisyear_anniversary'] = date("Y-m-d",strtotime('-1 day', strtotime($this->mdata['thisyear_anniversary'])));
			}
		}

		//$this->mdata['grade_update_date'] = ($this->mdata['grade_update_date'] != '0000-00-00 00:00:00')?substr($this->mdata['grade_update_date'],0,10):substr($this->mdata['regist_date'],0,10);//substr($this->mdata['update_date'],0,10) 
		//등급조정쿠폰의 등업된 경우에만 다운가능
		if ($this->mdata['grade_update_date'] != '0000-00-00 00:00:00') {
			$fm_member_group_logsql = "select * from fm_member_group_log where member_seq = '".$this->userInfo['member_seq']."' order by regist_date desc limit 0,1";
			$fm_member_group_logquery = $this->db->query($fm_member_group_logsql);  
			$fm_member_group_log =  $fm_member_group_logquery->row_array(); 
			if( ($fm_member_group_log['prev_group_seq'] >= $fm_member_group_log['chg_group_seq']) || ($this->userInfo['group_seq'] == 1) ) {
				$this->mdata['grade_update_date'] = '';
			}
		}else{
			$this->mdata['grade_update_date'] = substr($this->mdata['regist_date'],0,10);
		}
		
		###
		//쿠폰 다운내역/다운가능내역
		$this->load->helper('coupon');
		down_coupon_list('admin', $sc , $dataloop);
 		###
		
		$svcount = $this->couponmodel->get_download_have_total_count($sc,$this->mdata);
		$this->template->assign($svcount);
		###

		if(isset($dataloop)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],'?tab='.$_GET['tab'].'&member_seq='.$_GET['member_seq'], getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>'; 
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);

		$this->template->print_("tpl");
	}

	

	//상품쿠폰찾기
	public function coupongoodssearch()
	{ 
		$goodsSeq = (int) $_POST['goods'];
		$couponSeq = (int) $_POST['coupon']; 
 
		$today = date('Y-m-d',time());
		$this->load->model('goodsmodel'); 
		
		$tmp = $this->goodsmodel -> get_goods_category($goodsSeq);
		if($tmp) foreach($tmp as $data) $category[] = $data['category_code'];
		$goods = $this->goodsmodel -> get_default_option($goodsSeq);
		if( !$goods ) {
			echo json_encode(array('result'=>false));
			exit;
		}  

		
		$resultgoods = '';
		$goodsinfo	= $this->goodsmodel->get_goods($goodsSeq);
		$images		= $this->goodsmodel->get_goods_image($goodsSeq);
		$resultgoods['name']	= $goodsinfo['goods_name']; 
		$resultgoods['price']	= number_format($goods['price'])."원";
		if($images){
			foreach($images as $image){
				if($image['thumbCart']){
					$resultgoods['src'] = $image['thumbCart']['image'];break;
				}elseif($image['thumbScroll']){
					$resultgoods['src'] = $image['thumbScroll']['image']; break;
				}elseif($image['list1']){
					$resultgoods['src'] = $image['list1']['image']; break;
				}elseif($image['list2']){
					$resultgoods['src'] = $image['list2']['image']; break;
				}elseif($image['thumbView']){
					$resultgoods['src'] = $image['thumbView']['image']; break;
				}
			}
		}
		
		$issuegoods 	= $this->couponmodel->get_coupon_issuegoods($couponSeq);
		$this->couponinfo 	= $this->couponmodel->get_coupon($couponSeq); 
		if($this->couponinfo['issue_type'] == 'issue') {
			if($issuegoods) {
				foreach($issuegoods as $key => $tmp) { 
					if( $tmp['goods_seq'] == $goodsSeq ) { 
						$resultck = 'goodsyes';
						break;
					}
				}
				if(!$resultck) $resultck = 'goodsno';
			}else{
				$resultck = 'goodsno';
			}
		}else{
			if($issuegoods) {
				foreach($issuegoods as $key => $tmp) { 
					if( $tmp['goods_seq'] == $goodsSeq ) {
						$resultck = 'goodsno';
						break;
					}
				}
				if(!$resultck) $resultck = 'goodsyes';
			}else{
				$resultck = 'goodsyes';
			}
		}

		$result = array('result'=>$resultck,"goods"=>$resultgoods);
		echo json_encode($result);
	}
}

/* End of file coupon.php */
/* Location: ./app/controllers/admin/coupon.php */