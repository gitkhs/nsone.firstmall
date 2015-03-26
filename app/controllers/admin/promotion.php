<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class promotion extends admin_base {

	public function __construct() {
		parent::__construct();

		$this->admin_menu();
		$this->tempate_modules();
		$this->file_path	= $this->template_path();
		$this->load->model('promotionmodel');
		$this->load->model('membermodel');

		$ispointurl	= '/admin/setting/reserve';//포인트설정페이지
		$this->template->assign('ispoint',$this->isplusfreenot['ispoint']);
		$this->template->assign('ispointurl',$ispointurl);

		/* 회원 그룹 개발시 변경*/
		$groups = "";
		$query = $this->db->query("select group_seq,group_name from fm_member_group");
		foreach($query->result_array() as $row){
			$groups[] = $row;
		}
		/******************/
		$this->groups = $groups;
		$this->template->assign(array('groups'=>$groups));

		$this->template->define(array('tpl'=>$this->file_path));

	}

	public function index()
	{
		redirect("/admin/promotion/catalog");
	}

	//프로모션목록
	public function catalog()
	{

		### SEARCH
		$sc						= $_GET;
		if ($sc['search_text'])
		{
			$sc['search_text'] = trim($sc['search_text']);
			$sc['search_text']= stripslashes(htmlspecialchars($sc['search_text']));
		}
		$sc['orderby']		= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'promotion_seq';
		$sc['sort']				= (!empty($_GET['sort'])) ?			$_GET['sort']:'desc';
		$sc['page']			= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;

		$this->template->assign('checked',$checked);
		$data = $this->promotionmodel->promotion_list($sc);
		### PAGE & DATA
		$sc['searchcount']	= $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']		= $this->promotionmodel->get_item_total_count();

		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']		= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$datarow['date']			= substr($datarow['regist_date'],2,14);//등록일($datarow['update_date'])?substr($datarow['update_date'],2,14):
			$datarow['limit_goods_price'] = number_format($datarow['limit_goods_price']);

			$datarow['downloaddate']	= '-';
			$datarow['pointtitle'] = ( $datarow['type'] == 'point' )?'포인트(P) '.number_format($datarow['promotion_point']).'원 이상':'-';

			if( $datarow['issue_priod_type'] == 'date' ) {
				$datarow['issuedate']	= substr($datarow['issue_startdate'],2,10).' ~ '.substr($datarow['issue_enddate'],2,10);
			}else{
				$datarow['issuedate']	= '발급일로부터 '.number_format($datarow['after_issue_day']).'일';
			}

			if( strstr($datarow['type'],'shipping')  ){//배송비
				$datarow['salepricetitle']	= ($datarow['sale_type'] == 'shipping_free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
			}elseif($datarow['type'] == 'promotion_point' ){//개별코드 포인트
				$datarow['salepricetitle']	='포인트 '.number_format($datarow['promotion_point']).'원 지급';
			}else{
				$datarow['salepricetitle']	= ($datarow['sale_type'] == 'percent' ) ? $datarow['percent_goods_sale'].'% 할인, 최대 '.number_format($datarow['max_percent_goods_sale']).'원': '판매가격의 '.number_format($datarow['won_goods_sale']).'원';
			}
			$datarow['issuetypetitle'] = ( strstr($datarow['type'],'shipping')  )?'배송비할인':'';

			$dsc['whereis'] = ' and promotion_seq='.$datarow['promotion_seq'];
			$downloadtotal = $this->promotionmodel->get_download_total_count($dsc);
			$datarow['downloadtotal']	= number_format($downloadtotal);//발급수

			$usc['whereis'] = ' and promotion_seq='.$datarow['promotion_seq'].' and use_status = \'used\' ';
			$usetotal = $this->promotionmodel->get_download_total_count($usc);

			$datarow['usetotal']			= number_format($usetotal);//사용건수

			$datarow['issueimg'] = ( strstr($datarow['type'],'promotion') )?'promotion':'promotionnone';
			$datarow['issueimgalt'] = ( strstr($datarow['type'],'promotion') )?'일반코드':'개별코드';
			if( strstr($datarow['type'],'admin') ){//직접발급시
				$datarow['issuebtn']	= (( $datarow['issue_priod_type'] == 'date' && str_replace("-","", substr($datarow['issue_enddate'],0,10)) < date("Ymd"))) ? '':'<span class="btn small cyanblue"><button type="button" class="downloa_write_btn" promotion_seq="'.$datarow['promotion_seq'].'" download_limit="'.$datarow['download_limit'].'" promotion_name="'.$datarow['promotion_name'].'" >발급하기</button></span>';
			}else{
				$datarow['issuebtn']	= $this->promotionmodel->promotionTypeTitle[$datarow['type']];
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

	public function regist()
	{
		$this->load->helper('file');
		$codeall= str_replace("/regist.html","/codeall.html",$this->file_path);
		$codeone= str_replace("/regist.html","/code.html",$this->file_path);
		$tpl_sourceall = read_file(ROOTPATH."admin/skin/".$codeall);
		$tpl_source = read_file(ROOTPATH."admin/skin/".$codeone);
		if(isset($_GET['no'])) $tpl_source = str_replace("{프로모션고유번호}",$_GET['no'],$tpl_source);
		$this->template->assign(array('promocodeallhtml'=>$tpl_sourceall,'promocodehtml'=>$tpl_source));

		if(isset($_GET['no'])) {
			$no = (int) $_GET['no'];

			$this->load->model('goodsmodel');
			$this->load->model('categorymodel');
			$this->load->model('brandmodel');

			$promotions 		= $this->promotionmodel->get_promotion($no);
			$issuegoods 		= $this->promotionmodel->get_promotion_issuegoods($no);
			$issuebrand 		= $this->promotionmodel->get_promotion_issuebrand($no);
			$issuecategorys	= $this->promotionmodel->get_promotion_issuecategory($no);

			if(($issuegoods)){
				foreach($issuegoods as $key => $tmp) $arrGoodsSeq[] =  $tmp['goods_seq'];
				$goods = $this->goodsmodel->get_goods_list($arrGoodsSeq,'thumbView');
				foreach($issuegoods as $key => $data) $issuegoods[$key] = @array_merge($issuegoods[$key],$goods[$data['goods_seq']]);
				$this->template->assign(array('issuegoods'=>$issuegoods));
			}

			if(($issuebrand)) {
				foreach($issuebrand as $key =>$data) $issuebrand[$key]['brand'] = $this->brandmodel -> get_brand_name($data['brand_code']);
				$this->template->assign(array('issuebrands'=>$issuebrand));
			}

			if($issuecategorys){
				foreach($issuecategorys as $key =>$data) $issuecategorys[$key]['category'] = $this->categorymodel -> get_category_name($data['category_code']);
				$this->template->assign(array('issuecategorys'=>$issuecategorys));
			}

			$dsc['whereis'] = ' and promotion_seq='.$promotions['promotion_seq'];
			$downloadtotal = $this->promotionmodel->get_download_total_count($dsc);
			$promotions['downloadtotal']	= 0;//발급수-> 수정가능하도록 수정@2012-06-08
			$promotions['downloadtotalbtn']	= number_format($downloadtotal);

			if($promotions['promotion_type'] == 'file' ){//직접발급시
				$filepromotiontotal = $this->promotionmodel->get_promotioncode_input_item_total_count($promotions['promotion_seq']);
				$promotions['filepromotiontotal']	= number_format($filepromotiontotal);//사용건수
			}elseif( $promotions['promotion_type'] == 'random' ) {
				$codesc['whereis'] = ' and promotion_seq = "'.$promotions['promotion_seq'].'" ';
				$promotioncodetotal = $this->promotionmodel->get_promotioncode_total_count($codesc);
				$promotions['filepromotiontotal']	= number_format($promotioncodetotal);//사용건수
			}

			$usc['whereis'] = ' and promotion_seq='.$promotions['promotion_seq'].' and use_status = \'used\' ';
			$usetotal = $this->promotionmodel->get_download_total_count($usc);
			$promotions['usetotalbtn']	= number_format($usetotal);//사용건수

			$promotions['node_text_title'] = str_replace("[프로모션코드설명]",$promotions['promotion_desc'],str_replace("[프로모션코드]",$promotions['promotion_input_serialnumber'],$promotions['node_text']));

			if( strstr($promotions['type'],'admin') ){//직접발급시
				$adminissuebtn	= (( $promotions['issue_priod_type'] == 'date' && str_replace("-","", substr($promotions['issue_enddate'],0,10)) < date("Ymd"))) ? false:true;
				$this->template->assign(array('adminissuebtn'=>$adminissuebtn));
			}

			$node_text_normalar = @explode("^^",$promotions['node_text_normal']);
			$promotions['node_text_normal'] = $node_text_normalar[0];
			$promotions['node_text_normal_style'] = font_decoration_attr($promotions['node_text_normal'],'css','style');
			$node_text_normal_url_orgin = ($node_text_normalar[1]) ? json_decode($node_text_normalar[1]) : array();
			$promotions['node_text_normal_url'] = $node_text_normal_url_orgin->href;
			$promotions['node_text_normal_url_target'] = $node_text_normal_url_orgin->target;

			$this->template->assign(array('promotion'=>$promotions));
		}


		$this->template->assign("limittitle","{상품 할인가(판매가) x 수량} + {좌동} + {좌동}...");
		$this->template->assign("promotion_code_form","/data/promotion/promotion_form.xls");
		$this->template->print_("tpl");
	}


	//엑셀등록하기
	public function promotion_excel()
	{
		$promotion_seq = (int) $_GET['no'];
		$promotions 		= $this->promotionmodel->get_promotion($promotion_seq);
		$this->template->assign(array('promotion'=>$promotions));
		$this->template->assign('saveinterval',3);//3초 대기
		$this->template->print_("tpl");
	}

	//인증번호 보기
	public function promotion_code()
	{
		$promotion_seq = (int) $_GET['no'];
		$promotions 		= $this->promotionmodel->get_promotion($promotion_seq);
		$this->template->assign(array('promotion'=>$promotions));
		$this->template->print_("tpl");
	}

	//발급내역
	public function download()
	{
		$no = (int) $_GET['no'];
		$promotions 		= $this->promotionmodel->get_promotion($no);
		$promotions['downloaddatetitle'] = (strstr($promotions['type'],'offline'))?'인증일':'발급일';
		$this->template->assign(array('promotion'=>$promotions));
		$this->template->print_("tpl");
	}

	//프로모션발급 > 회원검색페이지
	public function download_member()
	{
		$no = (int) $_GET['no'];
		$promotions 		= $this->promotionmodel->get_promotion($no);
		$this->template->assign(array('promotion'=>$promotions));

		### GROUP
		$this->load->model('membermodel');
		$group_all = $this->membermodel->find_group_list();
		$this->template->assign('group_arr',$group_all);
		$this->template->print_("tpl");
	}


	//프로모션발급 > 회원검색리스트
	public function download_member_list()
	{
		$no = (int) $_POST['no'];
		$promotions 		= $this->promotionmodel->get_promotion($no);
		$this->template->assign(array('promotion'=>$promotions));
		$this->load->model('membermodel');

		### SEARCH
		$sc = $_POST;
		$sc['search_text']		= ($sc['search_text'] == '이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소') ? '':$sc['search_text'];
		$sc['orderby']		= 'A.member_seq';
		$sc['sort']				= (isset($_POST['sort'])) ?		$_POST['sort']:'desc';
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):10;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;
		//$sc['groupsar']		= $promotiongroupsar;

		### MEMBER
		$data = $this->membermodel->popup_member_list($sc);
		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount =  get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);
		$sc['totalcount']		= $this->membermodel->get_item_total_count();

		$idx = 0;
		$html = $this->getdownload_member_html($data, $sc,  $page, $promotions);
		if(!empty($html)) {
			$result = array( 'content'=>$html, 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"", 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		echo json_encode($result);
		exit;
	}

	//회원검색 > 발급내역
	function getdownload_member_html($data, $sc, $page, $promotions)
	{
		$html = '';
		$i = 0;
		foreach($data['result'] as $datarow){
			// 프로모션 정보 확인
			$download_promotions = $this->promotionmodel->get_admin_download($datarow['member_seq'], $promotions['promotion_seq']);
			$class = ($download_promotions)?" class='bg-gray' ":"";

			$datarow['number'] = $data['count'] - ( ( $page -1 ) * $sc['perpage'] + $i + 1 ) + 1;
			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';
			$html .= '<tr  '.$class.' >';
			if($download_promotions) {
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
				$html .= '<tr ><td class="its-td-align center" colspan="7" >"'.$sc['search_text'].'"로(으로) 검색된 회원이 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" colspan="7" >회원이 없습니다.</td></tr>';
			}
		}
		return $html;
	}

	//프로모션관리 > 발급내역 -> 검색 : 사용여부 , 사용일, 주문상품(주문번호) 또는 포인트 지급
	public function downloadlist()
	{
		$no = (int) $_POST['no'];
		$promotions 		= $this->promotionmodel->get_promotion($no);
		$this->template->assign(array('promotion'=>$promotions));

		### SEARCH
		$sc						= $_POST;
		$sc['search_text']		= ($sc['search_text'] == '아이디, 이름') ? '':$sc['search_text'];
		$sc['orderby']		= (!empty($_POST['orderby'])) ?	$_POST['orderby']:'download_seq';
		$sc['sort']				= (!empty($_POST['sort'])) ?			$_POST['sort']:'desc';
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):10;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;

		$data = $this->promotionmodel->download_list($sc);

		//발급내역 > 총 할인금액추출
		$promotion_order_saleprice = $this->promotionmodel->get_promotiontotal($sc, $promotions);

		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount = get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);
		$sc['totalcount']		= $this->promotionmodel->get_download_item_total_count($no);

		$idx = 0;
		$html = $this->getdownloadhtml($data, $sc,  $page);
		if(!empty($html)) {
			$result = array( 'content'=>$html,'totalsaleprcie'=>$promotion_order_saleprice['promotion_code_sale'],  'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"",'totalsaleprcie'=>$promotion_order_saleprice['promotion_code_sale'],  'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		echo json_encode($result);
		exit;
	}

	//프로모션관리 > 발급내역
	function getdownloadhtml($data, $sc, $page)
	{
		$this->load->model('membermodel');
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
				if ( strstr($datarow['type'],'shipping') ) {//배송비할인
					$order_promotion = $this->ordermodel->get_order_shipping_promotion($datarow['member_seq_buy'], $datarow['download_seq']);
					$items 				 = $this->ordermodel->get_item($order_promotion[0]['order_seq']);
					$goods_cnt = count($items)-1;
					$goodsinfo = ($goods_cnt > 0) ? '<img src="'.$items[0]['image'].'" /> <br /><span class="goods_name1">'.$items[0]['goods_name'].'</span> 외'.$goods_cnt.'건':'<img src="'.$items[0]['image'].'" /> <br /><span class="goods_name1 orderview bold blue"  onclick="orderinfo(\''.$order_promotion[0]['order_seq'].'\');"  goods_seq="'.$items[0]['goods_seq'].'" >'.$items[0]['goods_name'].'</span>';

					$datarow['goodsview'] = '<span class="goods_name1 hand orderview bold blue" onclick="orderinfo(\''.$order_promotion[0]['order_seq'].'\');"order_seq="'.$order_promotion[0]['order_seq'].'" >['.$order_promotion[0]['order_seq'].']</span><br/>'.$goodsinfo;
					$datarow['promotion_order_saleprice'] = number_format($order_promotion[0]['promotion_order_saleprice']).'원&nbsp;';
				} else {
					$order_promotion = $this->ordermodel->get_option_promotioncode_item($datarow['member_seq_buy'], $datarow['download_seq']);
					$datarow['goodsview'] = ($order_promotion[0])?'<span class="goods_name1 hand orderview bold blue" onclick="orderinfo(\''.$order_promotion[0]['order_seq'].'\');" order_seq="'.$order_promotion[0]['order_seq'].'" >['.$order_promotion[0]['order_seq'].']</span><br/><img src="'.$order_promotion[0]['image'].'" /> <br /><span class="goods_name1 hand goodsview bold blue" onclick="goodsinfo(\''.$order_promotion[0]['goods_seq'].'\');"  goods_seq="'.$order_promotion[0]['goods_seq'].'" >'.$order_promotion[0]['goods_name'].'</span>':'';
					$datarow['promotion_order_saleprice'] = number_format($order_promotion[0]['promotion_order_saleprice']).'원&nbsp;';
				}
			}

			$datarow['datetitle'] = $datarow['date'];

			if ($datarow['type'] != 'promotion_point') {
				$datarow['limit_goods_price_title'] = number_format($datarow['limit_goods_price']).'원 이상 구매 시&nbsp;';//제한금액
			}
			$datarow['issuedate']	= substr($datarow['issue_startdate'],2,10).' ~ '.substr($datarow['issue_enddate'],2,10);//유효기간

			//혜택
			if( strstr($datarow['type'],'shipping') ){//배송비
				$datarow['salepricetitle']	= ($datarow['sale_type'] == 'shipping_free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
			}elseif($datarow['type'] == 'promotion_point' ){//개별코드 포인트전환
				$datarow['salepricetitle']	='포인트 '.number_format($datarow['promotion_point']).'원 지급';
			}else{
				$datarow['salepricetitle']	= ($datarow['sale_type'] == 'percent' ) ? $datarow['percent_goods_sale'].'% 할인, 최대 '.number_format($datarow['max_percent_goods_sale']).'원': '판매가격의 '.number_format($datarow['won_goods_sale']).'원';
			}

			if($datarow['member_seq_buy']){
				$memberbuyer = $this->membermodel->get_member_data($datarow['member_seq_buy']);//구매회원정보
				if($memberbuyer){
					$datarow['userid_buy']			= $memberbuyer['userid'];
					$datarow['user_name_buy']	= $memberbuyer['user_name'];
				}else{
					$datarow['user_name_buy'] = '비회원';
				}
			}else{
				$datarow['user_name_buy'] = '';
			}

			if($datarow['userid']){
				$datarow['user_name'] = ($datarow['user_name'])?$datarow['user_name']:'';
			}else{
				$datarow['user_name'] = ($datarow['user_name'])?$datarow['user_name']:'비회원';
			}


			$html .= '<tr>';
			$html .= '	<td class="its-td-align center"><input type="checkbox" name="del[]" value="'.$datarow['download_seq'].'"  class="checkeds"  '.$deletebtn.'/></td>';
			$html .= '	<td class="its-td-align center">'.$datarow['number'].'</td>';
			if($datarow['member_seq']){
				$html .= '	<td class="its-td-align center"><span class=" userinfo hand bold blue"  onclick="userinfo(\''.$datarow['member_seq'].'\');"  mid="'.$datarow['userid'].'" mseq="'.$datarow['member_seq'].'" >'.$datarow['userid'].'<br/>'.$datarow['user_name'].'</span></td>';
			}else{
				$html .= '	<td class="its-td-align center">'.$datarow['user_name'].'</td>';
			}
			$html .= '	<td class="its-td-align center">'.$datarow['date'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['limit_goods_price_title'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['issuedate'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['salepricetitle'].'</td>';
			$html .= '	<td class="its-td-align right">'.$datarow['promotion_order_saleprice'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['promotion_input_serialnumber'].'</td>';
			if($datarow['member_seq_buy']){
				$html .= '	<td class="its-td-align center"><span class=" userinfo hand bold blue"  onclick="userinfo(\''.$datarow['member_seq_buy'].'\');"  mid="'.$datarow['userid_buy'].'" mseq="'.$datarow['member_seq_buy'].'" >'.$datarow['userid_buy'].'<br/>'.$datarow['user_name_buy'].'</span></td>';
			}else{
				$html .= '	<td class="its-td-align center">'.$datarow['user_name_buy'].'</td>';
			}
			$html .= '	<td class="its-td-align center">'.$datarow['use_status_title'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['goodsview'].'</td>';
			$html .= '	<td class="its-td-align center">'.$datarow['use_date'].'</td>';
			$html .= '</tr>';
			$i++;
		}//foreach end

		if($i==0){
			if($sc['search_text']){
				$html .= '<tr ><td class="its-td-align center" colspan="13" >"'.$sc['search_text'].'"로(으로) 검색된 프로모션내역이 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" colspan="13" >발급/사용내역이 없습니다.</td></tr>';
			}
		}
		return $html;
	}

	//관리자 > 개별회원 프로모션보유내역 레이어
	public function member_promotion_list(){

		$this->load->model('ordermodel');

		if($_GET['tab'] ==1 || !$_GET['tab'] ){
			unset($sc);
			### SEARCH
			$sc['orderby']		= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'download_seq';
			$sc['sort']				= (!empty($_GET['sort'])) ?			$_GET['sort']:'desc';
			$sc['page']			= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
			$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
			$sc['member_seq']	= $_GET['member_seq'];

			$data = $this->promotionmodel->download_list($sc);

			/**
			 * count setting
			**/
			$sc['searchcount']	 = $data['count'];
			$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
			$sc['totalcount']	 = $this->promotionmodel->get_item_total_count($sc);
			$idx = 0;
			foreach($data['result'] as $datarow){
				$idx++;
				$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
				$datarow['date']			= substr($datarow['regist_date'],2,14);//발급일
				$datarow['use_status_title'] = ($datarow['use_status'] == 'used') ? '사용함':'미사용';

				if(str_replace("-","",$datarow['issue_enddate']) < date("Ymd") && $datarow['use_status'] != 'used') $datarow['use_status_title'] = '<span class="gray" >소멸함</span>';//미사용중 기간지남

				$datarow['use_date']			  = ($datarow['use_status'] == 'used') ? $datarow['use_date']:'';

				if($datarow['use_status'] == 'used') {
					if ( strstr($datarow['type'],'shipping') ) {
						$order_promotion = $this->ordermodel->get_order_shipping_promotion($datarow['member_seq'], $datarow['download_seq']);
						$items 				 = $this->ordermodel->get_item($order_promotion['order_seq']);
						$goods_cnt = count($items)-1;
						$goodsinfo = ($goods_cnt > 0) ? '<img src="'.$items[0]['image'].'" /> <br /><span class="goods_name1">'.$items[0]['goods_name'].'</span> 외'.$goods_cnt.'건':'<img src="'.$items[0]['image'].'" /> <br /><span class="goods_name1"  goods_seq="'.$items[0]['goods_seq'].'" >'.$items[0]['goods_name'].'</span>';

						$datarow['goodsview'] = '<span class="goods_name1">['.$order_promotion['order_seq'].']</span><br/>'.$goodsinfo;
					} else {
						if ($datarow['type'] == 'promotion_point') {
							$datarow['goodsview'] = '포인트 '.number_format($datarow['promotion_point']).'원 지급';
						}else{
							$order_promotion = $this->ordermodel->get_option_promotioncode_item($datarow['member_seq'], $datarow['download_seq']);
							$datarow['goodsview'] = ($order_promotion[0])?'<span class="goods_name1">['.$order_promotion[0]['order_seq'].']</span><br/><img src="'.$order_promotion[0]['image'].'" /> <br /><span class="goods_name1" goods_seq="'.$order_promotion[0]['goods_seq'].'" >'.$order_promotion[0]['goods_name'].'</span>':'';
						}
					}
				}

				$datarow['limit_goods_price'] = number_format($datarow['limit_goods_price']);
				$datarow['downloaddate']	.='<br>'.substr($datarow['issue_startdate'],2,10).' ~ '.substr($datarow['issue_enddate'],2,10);

				if( strstr($datarow['type'],'shipping')  ){//배송비
					$datarow['salepricetitle']	= ($datarow['sale_type'] == 'shipping_free' ) ? '무료, 최대 '.number_format($datarow['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
				}else{
					$datarow['salepricetitle']	= ($datarow['sale_type'] == 'percent' ) ? $datarow['percent_goods_sale'].'%, 최대 '.number_format($datarow['max_percent_goods_sale']).'원': '판매가격의 '.number_format($datarow['won_goods_sale']).'원';
				}
				$datarow['issuebtn']	= $this->promotionmodel->promotionTypeTitle[$datarow['type']];

				$dataloop[] = $datarow;
			}//
		}else{
			### SEARCH
			unset($sc);
			$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
			$sc['perpage']			= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
			$sc['today']			= date('Y-m-d',time());

			$this->mdata = $this->membermodel->get_member_data($_GET['member_seq']);//회원정보
			$this->mdata['birthday'] = date("Y").substr($this->mdata['birthday'],4,6);
			$this->mdata['grade_update_date'] = ($this->mdata['grade_update_date'] != '0000-00-00 00:00:00')?substr($this->mdata['grade_update_date'],0,10):substr($this->mdata['regist_date'],0,10);//substr($this->mdata['regist_date'],0,10)
			$data = $this->promotionmodel->get_mypage_download($sc,$this->mdata);

			/**
			 * count setting
			**/
			$sc['searchcount'] = $data['count'];
			$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
			$sc['totalcount']	 = $this->promotionmodel->get_item_total_count($sc);

			$idx = 0;
			foreach($data['result'] as $datarow){
				$idx++;
				$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
				$datarow['date']			= substr($datarow['regist_date'],0,16);//발급일
				$datarow['use_status_title'] = ($datarow['use_status'] == 'used') ? '사용함':'미사용';
				if(str_replace("-","",$datarow['issue_enddate']) < date("Ymd") && $datarow['use_status'] != 'used') $datarow['use_status_title'] = '<span class="gray" >소멸함</span>';//미사용중 기간지남

				$promotions = $this->promotionmodel->get_promotion($datarow['promotion_seq']);
				$datarow['limit_goods_price'] = number_format($promotions['limit_goods_price']);

				if($promotions['type'] == 'birthday' || $promotions['type'] == 'memberGroup' || $promotions['type'] == 'member' ){//직접발급시
					$datarow['downloaddate']	= '발급일로부터 '.number_format($promotions['after_issue_day']).'일';
				}else{
					if( $promotions['issue_priod_type'] == 'date' ) {
						$datarow['downloaddate']	= substr($promotions['issue_startdate'],2,10).' ~ '.substr($promotions['issue_enddate'],2,10);
					}else{
						$datarow['downloaddate']	= '발급일로부터 '.number_format($promotions['after_issue_day']).'일';
					}
				}

				if( strstr($promotions['type'],'shipping') ){//배송비
					$datarow['salepricetitle']	= ($promotions['sale_type'] == 'shipping_free' ) ? '무료, 최대 '.number_format($promotions['max_percent_shipping_sale']).'원': '배송비 '.number_format($datarow['won_shipping_sale']).'원';//
				}else{
					$datarow['salepricetitle']	= ($promotions['sale_type'] == 'percent' ) ? $promotions['percent_goods_sale'].'%, 최대 '.number_format($promotions['max_percent_goods_sale']).'원': '판매가격의 '.number_format($promotions['won_goods_sale']).'원';
				}
				$datarow['issuebtn']	= $this->promotionmodel->promotionTypeTitle[$promotions['type']];
				if($promotions['promotion_img'] == '4' && @is_file($this->promotionmodel->copuonupload_dir.$promotions['promotion_image4'])){
					$datarow['downloadbtn']	= $this->promotionmodel->copuonupload_src.$promotions['promotion_image4'];
				}else{
					$datarow['downloadbtn']	= $this->promotionmodel->copuonupload_src.'promotion_0'.$promotions['promotion_img'].'.gif';
				}

				$dataloop[] = $datarow;
			}//
		}
		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],'?tab='.$_GET['tab'].'&member_seq='.$_GET['member_seq'], getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('downloadtotal',$downloadtotal);//보유프로모션수량
		$this->template->assign('issuestotal',$issuestotal);//다운가능프로모션수량
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);

		$this->template->print_("tpl");
	}

}

/* End of file promotion.php */
/* Location: ./app/controllers/admin/promotion.php */