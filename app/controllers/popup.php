<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class popup extends front_base {

	public function main_index()
	{
		redirect("/popup/index");
	}

	public function index()
	{
		redirect("/popup/catalog");
	}

	
	public function _zipcode_oldzibun()
	{
		// 우편번호 설정
		$cfg_zipcode = config_load('zipcode');

		if( !$_GET['zipcode_type'] &&  $cfg_zipcode['zipcode_street'] ){
			$_GET['zipcode_type'] = "street";
		}elseif( !$_GET['zipcode_type'] &&  $cfg_zipcode['new_zipcode_lot_number']  ){
			$_GET['zipcode_type'] = "zibun";
		}elseif( !$_GET['zipcode_type'] &&  $cfg_zipcode['old_zipcode_lot_number']  ){
			$_GET['zipcode_type'] = "oldzibun";
		}

		if($this->siteType == "mobile")	
			$perpage = "5";
		else 
			$perpage = "10";		

	    $this->load->model('zipcodemodel');		
		$data = $this->zipcodemodel->zipcode_oldzibun($perpage);		

		$zipcode_type = $_GET['zipcode_type'] ? $_GET['zipcode_type'] : "street";

		foreach($_GET as $key => $value){
			if(in_array($key,array('zipcode_type','old_zipcode','keyword'))) continue;
			if($query_string) $query_string .= "&";
			$query_string .= $key."=".$value;
		}
		
		$this->template->assign("cfg_zipcode",$cfg_zipcode);
		if($this->siteType == "mobile"){
			$this->template->assign("zipcodeFlag",$_GET['zipcodeFlag']);			
		}
		$this->template->assign("query_string",$query_string);
		$this->template->assign("zipcode_type",$data['zipcode_type']);
		$this->template->assign("keyword",$data['keyword'] );
		$this->template->assign("loop", $data['loop']);
		$this->template->assign("page",$data['page']);
		$this->print_layout($this->template_path());
	}

	public function zipcode()
	{
		$_GET['popup'] = true;

		// 우편번호 설정
		$cfg_zipcode = config_load('zipcode');
		
		if( ! $cfg_zipcode['zipcode_street'] && $_GET['zipcode_type']=='street' ) $_GET['zipcode_type'] = "";
		if( !$_GET['zipcode_type'] && $cfg_zipcode['zipcode_street'] ){
			$_GET['zipcode_type'] = "street";
		}elseif( !$_GET['zipcode_type'] &&  $cfg_zipcode['new_zipcode_lot_number']  ){
			$_GET['zipcode_type'] = "zibun";
		}elseif( !$_GET['zipcode_type'] &&  $cfg_zipcode['old_zipcode_lot_number']  ){
			$_GET['zipcode_type'] = "oldzibun";
		}
		
		$select_zipcode_type = $_GET['zipcode_type'];
		
		// 구지번 검색		
		if($select_zipcode_type == 'oldzibun'){
			$this->_zipcode_oldzibun();			
			exit;
		}

		if($this->siteType == "mobile")	
			$perpage = "5";
		else 
			$perpage = "10";		

	    $this->load->model('zipcodemodel');
		$data = $this->zipcodemodel->zipcode($perpage);		
		
		if($this->siteType == "mobile"){
			$this->template->assign("zipcodeFlag",$_GET['zipcodeFlag']);			
		}		
		
		$this->template->assign("cfg_zipcode",$cfg_zipcode);
		$this->template->assign("arrSigungu",$data['arrSigungu']);
		$this->template->assign("zipcode_type",$select_zipcode_type);
		$this->template->assign("query_string",$data['query_string'] );
		$this->template->assign("arrSido",$data['arrSido'] );
		$this->template->assign("keyword",$data['keyword'] );
		$this->template->assign("loop", $data['loop']);
		$this->template->assign("page",$data['page']);
		$this->print_layout($this->template_path());

	}
	
	public function sido()
	{
	    $this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();

		$loop = "";
		if($_GET['sido']){
			$query = "SELECT
			concat(SIDO,' ',SIGUNGU) as sigungu_addr,
			concat(SIDO,' ',SIGUNGU,' ', DONG) as addr,
			concat(SIDO,' ',SIGUNGU,' ',STREET) as addr_street
			FROM zipcode_street WHERE";

			if($_GET['zipcode_type'] == "street"){
				$wheres[] = $sidoWheres[] = "(STREET LIKE '".$_GET['sido']."%' OR SIGUNGU LIKE '".$_GET['sido']."%')";// or BUILDING LIKE '".$_GET['sido']."%'
			}else{
				$wheres[] = $sidoWheres[] = "(DONG LIKE '".$_GET['sido']."%' OR SIGUNGU LIKE '".$_GET['sido']."%')";// or SIGUNGUBUILDING LIKE '".$_GET['sido']."%'
			}

			$query = $ZIP_DB->query($query.implode(" AND ", $sidoWheres)." group by SIDO,SIGUNGU,DONG");
			$i=0;
			foreach ($query->result_array() as $row){

				if($before_sigungu != $row['sigungu_addr']){
					$loop[$i]['addr'] = $row['sigungu_addr'];
					$loop[$i]['addr_street'] = $row['sigungu_addr'];
					$i++;
				}
				$before_sigungu = $row['sigungu_addr'];
				$loop[$i]['addr'] = $row['addr'];
				$loop[$i]['addr_street'] = $row['addr_street'];
				$i++;
			}

		}

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign("sidoFlag",$_GET['sidoFlag']);
		$this->template->assign("sido",$_GET['sido']);
		$this->template->assign("idx",$_GET['idx']);
		$this->template->assign("loop",$loop);
		$this->template->print_("tpl");
	}

	public function zipcode_street_sigungu()
	{		
		$arrSigungu = array();
	    $this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();

		 $this->load->model('zipcodemodel');

		$zipcode_type = $_GET['zipcode_type'] ? $_GET['zipcode_type'] : "street";

		if(isset($_GET['zipcode_keyword'])){
			$keyword = $_GET['zipcode_keyword'];
		}else{
			$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : false;
		}

		list($wheres,$sidoWheres) = $this->zipcodemodel->get_where_query($keyword,$zipcode_type);		
		
		if($sidoWheres){
			$arrSigungu = $ZIP_DB->query("select SIGUNGU from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' AND ".implode(" AND ", $sidoWheres)." GROUP BY SIGUNGU");
		}else{
			$arrSigungu = $ZIP_DB->query("select SIGUNGU from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' GROUP BY SIGUNGU");
		}

		$arrSigungu = $arrSigungu->result_array();

		/*
		foreach($arrSigungu as $k => $v){
			$query = $ZIP_DB->query("select count(SIGUNGU) as cnt from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' AND SIGUNGU = '".$v['SIGUNGU']."' AND ".implode(" AND ", $sidoWheres));
			$countData = $query->row_array();
			$arrSigungu[$k]['cnt'] = $countData['cnt'];

		}
		*/
		//echo "select count(SIDO) as cnt from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' AND SIGUNGU = '".$v['SIGUNGU']."' AND ".implode(" AND ", $wheres);
		echo json_encode($arrSigungu);
	}


	public function addressbook()
	{
		$this->print_layout($this->template_path());
	}

	public function addressbook_write()
	{
		$this->print_layout($this->template_path());
	}

	public function designpopup(){
		$popup_seq = $_GET['seq'];
		$popup_key = $_GET['popup_key'];
		$template_path = $this->template_path();

		$query  = $this->db->query("select * from fm_design_popup where popup_seq = ?",$popup_seq);
		$data = $query->row_array();

		$popupHtml = "";
		$popupHtml .= "<div class='designPopup' popupStyle='window' designElement='popup' template_path='{$template_path}' popupSeq='{$popup_seq}' style='left:0px;top:0px;'>";
		$popupHtml .= "<div class='designPopupBody'>";

		if($data['contents_type']=='image'){
			if($data['link'])  $popupHtml .= "<a href='{$data['link']}' target='_opener' onclick='self.close()'>";
			$popupHtml .= "<img src='/data/popup/{$data['image']}' />";
			if($data['link'])  $popupHtml .= "</a>";
		}

		if($data['contents_type']=='text'){
			$popupHtml .= $data['contents'];
		}

		$designPopupTodaymsgCss = font_decoration_attr($data['bar_msg_today_decoration'],'css','style');
		$designPopupCloseCss = font_decoration_attr($data['bar_msg_close_decoration'],'css','style');
			
		$popupHtml .= "</div>";
		$popupHtml .= "<div class='designPopupBar' style='background-color:{$data['bar_background_color']}'>";
		$popupHtml .= "<div class='designPopupTodaymsg' {$designPopupTodaymsgCss}><label><input type='checkbox' /> {$data['bar_msg_today_text']}</label></div>";
		$popupHtml .= "<div class='designPopupClose' {$designPopupCloseCss}>{$data['bar_msg_close_text']}</div>";
		$popupHtml .= "</div>";
		$popupHtml .= "</div>";

		$this->template->assign(array('popupHtml'=>$popupHtml));
		$tpl = $this->template_path();
		$tpl = str_replace("designpopup","_designpopup",$tpl);

		$this->template->assign(array('shopTitle'=>$data['title']));

		$this->tempate_modules();
		$this->template->define(array('tpl'=>$tpl));
		$this->template->print_('tpl');
	}

	public function joincheck(){
		$_GET['popup'] = true;
		$joincheck_seq = $_GET['seq'];

		$query = $this->db->query("select * from fm_joincheck where joincheck_seq=?",$joincheck_seq);
		$data = $query->row_array();

		$tpl = 'popup/_'.$data['skin'].'.html';

		$this->template_path = $tpl;
		$this->template->assign(array("template_path"=>$this->template_path));

		$this->print_layout($this->skin.'/'.$tpl);

	}

	public function issue_list(){
		$coupon_seq	= $_POST['coupon_seq'];
		$type		= $_POST['type'];

		if(!$coupon_seq){
			$coupon_seq	= $_GET['coupon_seq'];
			$type		= $_GET['type'];
		}

		if(isset($coupon_seq)) {

			$no = $coupon_seq;
			$this->load->model('couponmodel');
			$this->load->model('goodsmodel');
			$this->load->model('categorymodel');

			$coupons 			= $this->couponmodel->get_coupon($no);
			$couponGroups 	= $this->couponmodel->get_coupon_group($no);
			$issuegoods 		= $this->couponmodel->get_coupon_issuegoods($no);
			$issuecategorys	= $this->couponmodel->get_coupon_issuecategory($no);

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
				foreach($issuecategorys as $key =>$data){
					if($issuecategorys[$key]['type']=='issue'){
						$issuecategorys[$key]['category'] = $this->categorymodel -> get_category_name_href($data['category_code']);
					}elseif($issuecategorys[$key]['type']=='except'){
						$issuecategorys[$key]['category'] = $this->categorymodel -> get_category_name($data['category_code']);
					}
				}

				$this->template->assign(array('issuecategorys'=>$issuecategorys));
			}

			$dsc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'];
			$downloadtotal = $this->couponmodel->get_download_total_count($dsc);
			$coupons['downloadtotal']	= 0;//발급수-> 수정가능하도록 수정@2012-06-08
			$coupons['downloadtotalbtn']	= number_format($downloadtotal);

			$usc['whereis'] = ' and coupon_seq='.$coupons['coupon_seq'].' and use_status = \'used\' ';
			$usetotal = $this->couponmodel->get_download_total_count($usc);
			$coupons['usetotalbtn']	= number_format($usetotal);//사용건수

			$this->template->assign(array('coupons'=>$coupons));
		}
		$this->print_layout($this->template_path());

	}

}

