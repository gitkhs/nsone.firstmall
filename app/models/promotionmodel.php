<?php
class Promotionmodel extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->table_promotion								 = 'fm_promotion';								//프로모션
		$this->promotion_issuecategory					 = 'fm_promotion_issuecategory';		//카테고리
		$this->promotion_issuegoods						 = 'fm_promotion_issuegoods';			//상품
		$this->promotion_issuebrand						 = 'fm_promotion_issuebrand';			//브랜드

		$this->promotion_download							 = 'fm_download_promotion';							//발급
		$this->promotion_download_issuecategory	 = 'fm_download_promotion_issuecategory';	//발급카테고리
		$this->promotion_download_issuegoods		 = 'fm_download_promotion_issuegoods';		//발급상품
		$this->promotion_download_issuebrand		 = 'fm_download_promotion_issuebrand';		//발급상품

		$this->promotioncode										 = 'fm_promotion_code';							//프로모션코드 자동
		$this->promotioncode_input							 = 'fm_promotion_code_input';					//프로모션코드 수동

		$this->members											 = 'fm_member';
		$this->members_business									 = 'fm_member_business';


		$this->promotionupload_dir = ROOTPATH.'data/promotion/';//첨부파일폴더
		$this->promotionupload_src = '/data/promotion/';
	}

	//promotion write/modify
	public function check_param_promotion_download()
	{
		$_POST['promotionType']			= ( strstr($_POST['saleType'],'shipping') )?$_POST['promotionType'].'_shipping':$_POST['promotionType'];
		$this->validation->set_rules('promotionType', '프로모션종류','trim|required|xss_clean');

		$this->validation->set_rules('promotionName', '프로모션 코드명','trim|required|xss_clean');
		$this->validation->set_rules('promotionDesc', '프로모션 코드 설명','trim|xss_clean');

		$_POST['percentGoodsSale']			= ($_POST['percentGoodsSale']>0)?$_POST['percentGoodsSale']:'';//혜택1 %
		$_POST['maxPercentGoodsSale']		= ($_POST['maxPercentGoodsSale']>0)?$_POST['maxPercentGoodsSale']:'';//혜택1 최대원
		$_POST['wonGoodsSale']					= ($_POST['wonGoodsSale']>0)?$_POST['wonGoodsSale']:'';//혜택2 원
		$_POST['limitGoodsPrice']				= ($_POST['limitGoodsPrice']>0)?$_POST['limitGoodsPrice']:'';//사용제한-금액

		//선착순 인원수
		if($_POST['downloadLimit_'.$_POST['promotionType']]=='limit') {

			$_POST['downloadLimitEa_'.$_POST['promotionType']]					= ($_POST['downloadLimitEa_'.$_POST['promotionType']]>0)?$_POST['downloadLimitEa_'.$_POST['promotionType']]:'';//혜택2 원
			$this->validation->set_rules('downloadLimitEa_'.$_POST['promotionType'], '선착순 제한인원','trim|required|numeric|xss_clean');
		}

		//프로모션코드 생성방식

		if( strstr($_POST['promotionType'],'promotion') ) {
			$_POST['promotion_code_size']	= ($_POST['promotion_type']=='input')?$_POST['promotionLimit_size2']:$_POST['promotionLimit_size1'];
		}

		//포인트 지급프로모션
		if( $_POST['promotionType'] == 'point' ||  $_POST['promotionType'] == 'point_shipping' ) {
			$_POST['promotion_point']				= ($_POST['promotion_point']>0)?$_POST['promotion_point']:'';//사용제한-금액
			$this->validation->set_rules('promotion_point', '전환 포인트','trim|required|numeric|xss_clean');
		}

		/*
		** 혜택
		*/
		if( $_POST['saleType']=='percent' ) {
			$this->validation->set_rules('percentGoodsSale', '할인율','trim|required|numeric|max_length[3]|xss_clean');
			$this->validation->set_rules('maxPercentGoodsSale', '최대 할인 금액','trim|required|numeric|xss_clean');
		}
		if( $_POST['saleType']=='won' ) {
			$this->validation->set_rules('wonGoodsSale', '할인 금액','trim|required|numeric|xss_clean');
		}
		if( $_POST['saleType']=='shipping_free' ) {
			$_POST['maxPercentShippingSale']				= ($_POST['maxPercentShippingSale']>0)?$_POST['maxPercentShippingSale']:'';
			$this->validation->set_rules('maxPercentShippingSale', '기본 배송비 무료, 최대 배송비할인','trim|required|numeric|xss_clean');
		}
		if( $_POST['saleType']=='shipping_won' ) {
			$_POST['wonShippingSale']				= ($_POST['wonShippingSale']>0)?$_POST['wonShippingSale']:'';
			$this->validation->set_rules('wonShippingSale', '배송비할인','trim|required|numeric|xss_clean');
		}

		$this->validation->set_rules('issuePriodType', '유효 기간 종류','trim|required|max_length[6]|xss_clean');
		if($_POST['issuePriodType']=='date'){
			$this->validation->set_rules('issueDate[]', '유효 기간','trim|required|max_length[10]|xss_clean');
		}
		if($_POST['issuePriodType']=='day'){
			$this->validation->set_rules('afterIssueDay', '유효 기간','trim|required|max_length[10]|xss_clean');
		}

		$this->validation->set_rules('limitGoodsPrice', '사용제한 금액','trim|numeric|xss_clean');

		if($_POST['issue_type'] == 'issue' ){
			$this->validation->set_rules('issueGoods[]', '적용 상품','trim|numeric|xss_clean');
			$this->validation->set_rules('issueCategoryCode[]', '적용 카테고리','trim|xss_clean');
		}elseif($_POST['issue_type'] == 'except' ){
			$this->validation->set_rules('exceptIssueGoods[]', '적용예외상품','trim|numeric|xss_clean');
			$this->validation->set_rules('exceptIssueCategoryCode[]', '적용 예외 카테고리','trim|xss_clean');
		}

		if(!$_POST['promotionSeq']) {//등록시에만 프로모션코드체크
			if( $_POST['promotion_type'] == 'random') {//자동생성 > 프로모션코드 1개생성
				$_POST['promotion_random_num'] = 1;//기본1개 생성 4-4-4-4자리
			}elseif( $_POST['promotion_type'] == 'one') {//자동생성 > 동일번호
			}elseif( $_POST['promotion_type'] == 'input') {//수동생성 > 동일번호
				$this->validation->set_rules('promotion_input_num', '프로모션코드번호','trim|required|xss_clean');
				if( strlen($_POST['promotion_input_num']) != $_POST['promotion_code_size'] ){//자리수체크
					$callback = "parent.document.promotionRegist.promotion_input_num.focus();";
					openDialogAlert("프로모션 코드 수동생성시 ".$_POST['promotion_code_size']."자리로 정확히 입력해 주세요.",450,140,'parent',$callback);
					exit;
				}
			}elseif( $_POST['promotion_type'] == 'file') {//수동생성 > 파일
				$this->validation->set_rules('promotion_file', '수동등록시 엑셀파일','trim|required|xss_clean');
			}
		}

		$this->validation->set_rules('promotionImg', '프로모션 이미지','trim|numeric|max_length[3]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		if($_POST['issuePriodType'] == 'date'){
			if($_POST['issueDate'][1] < $_POST['issueDate'][0]){
				$callback = "parent.document.promotionRegist.issueDate[0].focus();";
				openDialogAlert("유효기간 종료일이 시작일보다 이후에 있어야 합니다.<br/>날짜를 조정해 주세요.",450,140,'parent',$callback);
				exit;
			}
		}

		if(!$_POST['promotionSeq']) {//등록시에만 중복체크
			if( $parampromotion['promotion_type'] == 'input') {//수동생성 > 동일번호
				$_POST['promotion_input_num']		= if_empty($_POST, 'promotion_input_num', '');
 				//프로모션코드 체크
				$sc['whereis'] = ' and code_serialnumber = "'.$_POST['promotion_input_num'].'" ';
				$offlienresult = $this->get_promotioncode_total_count($sc);
				if(!$offlienresult){
					$offlienresult = $this->get_promotioncode_input_total_count($sc);
				}
				if($offlienresult){
					$err = '이미 등록된 프로모션 코드입니다.';
					$callback = "if(parent.document.getElementsByName('promotion_input_num')[0]) parent.document.getElementsByName('promotion_input_num')[0].focus();";
					openDialogAlert($err,400,140,'parent',$callback);
					exit;
				}
			}
		}


		$parampromotion['type'] 								= $_POST['promotionType'];
		$parampromotion['promotion_type'] 				= $_POST['promotion_type'];
		$parampromotion['issue_type'] 						= if_empty($_POST, 'issue_type', 'all');//사용제한 all issue except

		if( $_POST['promotionType'] != "promotion" ) {
			$parampromotion['duplication_use']				= 0;//중복할인여부 -> 개별코드제외
		}else{
			$parampromotion['duplication_use']				= if_empty($_POST, 'duplicationUse', '0');//중복할인여부 -> 일반코드만
		}

		if( $_POST['promotionType'] == 'point' ||  $_POST['promotionType'] == 'point_shipping' ) {
			$parampromotion['downloadLimit_member']	= 1;//회원전용
		}else{
			$parampromotion['downloadLimit_member']	= if_empty($_POST, 'downloadLimit_member', '0');//회원전용여부
		}

		$parampromotion['mainshow'] 						= if_empty($_POST, 'mainshow', '0');
		$parampromotion['promotion_point'] 				= if_empty($_POST, 'promotion_point', '0');
		$parampromotion['node_text'] 						= if_empty($_POST, 'node_text', '');
		$parampromotion['node_text_normal'] 			= if_empty($_POST, 'node_text_normal','');

		if($_POST['node_text_normal_url']) $parampromotion['node_text_normal'] .= '^^{"href":"'.$_POST['node_text_normal_url'].'","target":"'.$_POST['node_text_normal_url_target'].'"}';

		if(!$_POST['promotionSeq']) {//등록시에만 중복체크
			if( $parampromotion['promotion_type'] == 'random') {//자동생성 > 인증번호 갯수

			}elseif( $parampromotion['promotion_type'] == 'one') {//자동생성 -> 발급시자동생성 5~6자리
				$parampromotion['promotion_code_size'] 					= $_POST['promotion_code_size'];
				$parampromotion["promotion_input_serialnumber"]		= substr(mt_rand(), 0, $parampromotion['promotion_code_size']);//숫자
			}elseif( $parampromotion['promotion_type'] == 'input') {//수동생성 > 동일번호
				$parampromotion['promotion_input_serialnumber'] 		= if_empty($_POST, 'promotion_input_num', '');
				$parampromotion['promotion_code_size'] 					= $_POST['promotion_code_size'];
			}elseif( $parampromotion['promotion_type'] == 'file') {//수동생성 > 파일
			}
		}

		//선착순 > 총인원
		$parampromotion['download_limit'] 			= ($_POST['downloadLimit_promotion']=='limit')?'limit':'unlimit';
		$parampromotion['download_limit_ea'] 		= ($_POST['downloadLimit_promotion']=='limit' && $_POST['downloadLimitEa_promotion'])?$_POST['downloadLimitEa_promotion']:0;

		$parampromotion['promotion_name'] 			= $_POST['promotionName'];
		$parampromotion['promotion_desc'] 			= $_POST['promotionDesc'];
		$parampromotion['sale_type'] 					= $_POST['saleType'];

		if($parampromotion['sale_type']=='percent') {
			$parampromotion['percent_goods_sale'] 			= if_empty($_POST, 'percentGoodsSale', '0');
			$parampromotion['max_percent_goods_sale'] 	= if_empty($_POST, 'maxPercentGoodsSale', '0');
		}elseif($parampromotion['sale_type']=='won') {
			$parampromotion['won_goods_sale'] 			=  if_empty($_POST, 'wonGoodsSale', '0');
		}elseif($parampromotion['sale_type']=='shipping_free') {
			$parampromotion['max_percent_shipping_sale'] 	= if_empty($_POST, 'maxPercentShippingSale', '0');
		}elseif($parampromotion['sale_type']=='shipping_won') {
			$parampromotion['won_shipping_sale'] 			= if_empty($_POST, 'wonShippingSale', '0');
		}

		$parampromotion['issue_priod_type'] 		= $_POST['issuePriodType'];//유효기간

		if($parampromotion['issue_priod_type']=='date') {
			if(isset($_POST['issueDate']) && $_POST['issueDate'][0]){
				$parampromotion['issue_startdate'] 	= $_POST['issueDate'][0];
			}
			if(isset($_POST['issueDate']) && $_POST['issueDate'][1]){
				$parampromotion['issue_enddate'] 	= $_POST['issueDate'][1];
			}
		}elseif($parampromotion['issue_priod_type']=='day') {
			if(isset($_POST['afterIssueDay']) && $_POST['afterIssueDay']){
				$parampromotion['after_issue_day']		= $_POST['afterIssueDay'];
			}
		}

		//사용제한 - 금액
		if( isset($_POST['limitGoodsPrice'])) {
			$parampromotion['limit_goods_price']		= $_POST['limitGoodsPrice'];
		}

		$parampromotion['promotion_img'] 				= $_POST['promotionImg'];
		if(!empty($_POST['promotionimage4']) && @is_file(ROOTPATH."data/tmp/".$_POST['promotionimage4'])) {//rename
			@rename(ROOTPATH."data/tmp/".$_POST['promotionimage4'], $this->promotionupload_dir.$_POST['promotionimage4']);
			@chmod($this->promotionupload_dir.$_POST['promotionimage4'],0707);
			$parampromotion['promotion_image4'] 			= $_POST['promotionimage4'];
		}
		if( $_POST['promotionSeq'] ) {
			$parampromotion['update_date'] = date('Y-m-d H:i:s',time());
		}else{
			$parampromotion['regist_date']	= $parampromotion['update_date'] = date('Y-m-d H:i:s',time());
		}

		return $parampromotion;
	}

	/*
	 * 관리
	 * @param
	*/
	public function promotion_list($sc)
	{

		$sql = "select SQL_CALC_FOUND_ROWS * from ".$this->table_promotion." where 1";

		if(!empty($sc['search_text']))$sql.= " and promotion_name like \"%".$sc['search_text']."%\" ";

		if(!empty($sc['promotionType1']))
		{
			$typesql[] = "promotion";
		}

		if(!empty($sc['promotionType2']))
		{
			$typesql[] = "promotion_shipping";
		}

		if(!empty($sc['promotionType3']))
		{
			$typesql[] = "admin";
			$typesql[] = "point";
		}


		if(!empty($sc['promotionType4']))
		{
			$typesql[] = "admin_shipping";
			$typesql[] = "point_shipping";
		}

		if($typesql){
			$typear = implode("', '",$typesql);
			$sql .= ' and  type in (\''.$typear.'\')';
		}

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and regist_date BETWEEN '{$start_date}' AND '{$end_date}' ";
		}

		// 정렬
		if($sc['orderby'] ) {
			$sql.=" order by {$sc['orderby']} {$sc['sort']} ";
		} else {
			$sql.=" order by promotion_seq desc ";
		}

		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}

	// 총건수
	public function get_item_total_count()
	{
		$sql = 'select promotion_seq from '.$this->table_promotion.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 프로모션개별추출
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:' * ';
		$sql = "select ".$sc['select']." from  ".$this->table_promotion."  where 1 ". $sc['whereis'];
		$sql.=" order by promotion_seq desc ";
		if( $sc['perpage'] ) $sql .=" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql);
		$data = $query->result_array();
		return $data;
	}


	//생일자/신규회원가입 프로모션은 1개만가능..
	public function get_promotion_total_count($sc)
	{
		$sql = 'select promotion_seq from '.$this->table_promotion.' where 1 '. $sc['whereis'];
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result['promotion_seq'];
	}

	//발급(인증)받은프로모션정보가져오기
	public function get_download_promotion($download_seq)
	{
		$this->db->limit(1,0);
		$this->db->where('download_seq', $download_seq);
		$query = $this->db->get($this->promotion_download);
		$result = $query->result_array();
		return $result[0];
	}


	// 발급총건수 와 사용건수
	public function get_download_total_count($sc)
	{
		$sql = 'select promotion_seq from '.$this->promotion_download.' where 1 '. $sc['whereis'];
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 발급내역관리
	 * @param
	*/
	public function download_list($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, m.userid, ifnull(mb.bname,m.user_name) as user_name, d.*
					from ".$this->promotion_download." d
					left join ".$this->members." m on m.member_seq = d.member_seq 
					left join ".$this->members_business." mb on mb.member_seq = d.member_seq 
					where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and d.promotion_seq = '.$sc['no'].' ';
		}

		if(isset($sc['member_seq'])) $sql.= ' and m.member_seq ='.$sc['member_seq'];//회원

		if( !empty($sc['search_text']) )
		{
			$sql .= ' and ( m.user_name like "%'.$sc['search_text'].'%" or m.userid  like "%'.$sc['search_text'].'%") ';//
		}

		if(!empty($sc['use_status']))
		{
			$sql.= " and d.use_status='".$sc['use_status']."'";
		}
		if(!empty($sc['keyword']))$sql.= " and promotion_name like \"%".$sc['keyword']."%\" ";

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and d.regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and d.regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and d.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}


		$sql.=" order by d.download_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}


	// 총건수
	public function get_download_item_total_count($no)
	{
		$sql = 'select download_seq from '.$this->promotion_download.' where 1';
		if( !empty($no) )
		{
			$sql .= ' and promotion_seq = '.$no.' ';
		}

		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	public function get_promotion($no)
	{
		$this->db->limit(1,0);
		$this->db->where('promotion_seq', $no);
		$query = $this->db->get($this->table_promotion);
		$result = $query->result_array();
		return $result[0];
	}

	public function get_promotion_issuecategory($no)
	{
		$result = false;
		$this->db->where('promotion_seq', $no);
		$query = $this->db->get($this->promotion_issuecategory);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_promotion_issuegoods($no)
	{
		$result = false;
		$this->db->where('promotion_seq', $no);
		$query = $this->db->get($this->promotion_issuegoods);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_promotion_issuebrand($no)
	{
		$result = false;
		$this->db->where('promotion_seq', $no);
		$query = $this->db->get($this->promotion_issuebrand);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}


	/* 관리자 > 직접프로모션발급 > 발급여부체크 */
	public function get_admin_download($memberSeq,$promotionSeq)
	{
		$sql = "SELECT * FROM ".$this->promotion_download." where promotion_seq='".$promotionSeq."' and member_seq='".$memberSeq."'  ";
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result;
	}

	/* 일반코드 구매시 프로모션코드발급 > 발급여부체크 */
	public function get_orderby_download($memberSeq_buy,$promotionSeq)
	{
		$sql = "SELECT * FROM ".$this->promotion_download." where promotion_seq='".$promotionSeq."' and member_seq_buy='".$memberSeq_buy."'  ";
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result;
	}

	/*프로모션코드 >  발급갯수 */
	public function get_promotioncode_download_cnt($memberSeq,$promotionSeq)
	{
		$sql = "SELECT promotion_seq FROM ".$this->promotion_download." where promotion_seq='".$promotionSeq."' and member_seq='".$memberSeq."'  ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* 관리자 > 직접프로모션발급 > 발급여부체크 */
	public function get_download_serialnumber($promotionSeq, $cartpromotioncode)
	{
		$sql = "SELECT * FROM ".$this->promotion_download." where  promotion_seq='".$promotionSeq."' and promotion_input_serialnumber='".$cartpromotioncode."' ";
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result;
	}

	/* 관리자 > 직접프로모션발급 > 발급여부체크 */
	public function get_download_serialnumber_cnt($promotionSeq, $cartpromotioncode)
	{
		$sql = "SELECT * FROM ".$this->promotion_download." where  promotion_seq='".$promotionSeq."' and promotion_input_serialnumber='".$cartpromotioncode."'  and use_status='used' ";//사용된건만
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* 관리자 > 직접프로모션발급 > 발급여부체크 */
	public function get_download($cartpromotioncode)
	{
		$sql = "SELECT * FROM ".$this->promotion_download." where promotion_input_serialnumber='".$cartpromotioncode."'";
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result;
	}


	public function get_promotion_download_issuecategory($no)
	{
		$result = false;
		$this->db->where('download_seq', $no);
		$query = $this->db->get($this->promotion_download_issuecategory);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_promotion_download_issuegoods($no)
	{
		$result = false;
		$this->db->where('download_seq', $no);
		$query = $this->db->get($this->promotion_download_issuegoods);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_promotion_download_issuebrand($no)
	{
		$result = false;
		$this->db->where('download_seq', $no);
		$query = $this->db->get($this->promotion_download_issuebrand);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	/* 관리자 > 직접프로모션발급 > 발급여부체크 */
	public function get_cart_download($cartpromotioncode,$session_id)
	{
		$sql = "SELECT * FROM ".$this->promotion_download." where promotion_input_serialnumber='".$cartpromotioncode."' and session_id='".$session_id."'  ";
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result;
	}

	/**
	*@ 회원의 다운로드 가능 프로모션 목록
	* 생일자 : 생일전 ~ 생일후 기간
	* 배송비 : 발급전 ~ 발급후 기간
	* 등록 : 등급조정 이후기간
	==> AND 등급제한 체크
	==> AND 전체수량제한
	**/
	public function get_mypage_download($sc, $members)
	{
		$sql = "SELECT SQL_CALC_FOUND_ROWS promotion.*
					FROM (
						SELECT c.*,
							SUM(if(d.use_status='used',1,0)) used_cnt,
							SUM(if(d.use_status='unused',1,0)) unused_cnt,
							(
								SELECT count(*)
								FROM fm_promotion_group
								WHERE promotion_seq=c.promotion_seq
							) as allgroup_issue_cnt
						FROM ".$this->table_promotion." c
							LEFT JOIN ".$this->promotion_download." d ON c.promotion_seq = d.promotion_seq AND d.member_seq='".$members['member_seq']."'

						GROUP BY c.promotion_seq
					) promotion
				WHERE
					((allgroup_issue_cnt =0) OR (allgroup_issue_cnt>0))
					AND
					(
						( used_cnt > 0 AND duplication_use = 1 )
						OR  (used_cnt = 0 AND unused_cnt=0)
					)";
		$sql.=" order by promotion.promotion_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$query = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($query);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}

	/* 일반코드 프로모션코드 전체 목록  */
	public function get_able_promotion_list($goodsSeq, $category, $brand_code, $price, $cartpromotioncode, $goodprice, $ea=1 )
	{
		$today = date("Y-m-d",time());

		$issue_subquery = $except_subquery = "";
		if(count($category)>0){
			$issue_subquery = "+(
												select count(*) FROM
												".$this->promotion_issuecategory."
												WHERE promotion_seq=d.promotion_seq AND `type`='issue' AND category_code IN ('".implode("','",$category)."')
											)";
			$except_subquery = "+(
													select count(*) FROM
													".$this->promotion_issuecategory."
													WHERE promotion_seq=d.promotion_seq AND `type`='except' AND category_code IN ('".implode("','",$category)."')
												)";
		}

		if(count($brand_code)>0){
			$issue_subquery .= "+(
												select count(*) FROM
												".$this->promotion_issuebrand."
												WHERE promotion_seq=d.promotion_seq AND `type`='issue' AND brand_code IN ('".implode("','",$brand_code)."')
											)";
			$except_subquery .= "+(
													select count(*) FROM
													".$this->promotion_issuebrand."
													WHERE promotion_seq=d.promotion_seq AND `type`='except' AND brand_code IN ('".implode("','",$brand_code)."')
												)";
		}

		$query = "SELECT promotion.*, except_cnt ,all_issue_cnt, issue_cnt,   duplication_use
					FROM (
						SELECT d.*,
							(
								SELECT count(*)
								FROM ".$this->promotion_issuegoods."
								WHERE promotion_seq=d.promotion_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM ".$this->promotion_issuebrand."
								WHERE promotion_seq=d.promotion_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM ".$this->promotion_issuecategory."
								WHERE promotion_seq=d.promotion_seq AND `type`='issue'
							) as all_issue_cnt,
							(
								SELECT count(*)
								FROM ".$this->promotion_issuegoods."
								WHERE promotion_seq=d.promotion_seq AND goods_seq='{$goodsSeq}' AND `type`='issue'
							){$issue_subquery} as issue_cnt,
							(
								select count(*)
								FROM ".$this->promotion_issuegoods."
								WHERE promotion_seq=d.promotion_seq AND goods_seq='{$goodsSeq}' AND `type`='except'
							){$except_subquery} as except_cnt
						FROM ".$this->table_promotion." d
						WHERE
							d.promotion_input_serialnumber='".$cartpromotioncode."' and
							(
								(d.issue_startdate is null  AND d.issue_enddate is null )
								OR
								(d.issue_startdate <='{$today}' AND d.issue_enddate >='{$today}')
							)
					) promotion
				WHERE  (except_cnt=0)
					AND (all_issue_cnt=0 OR issue_cnt>0)
					AND (issue_type = 'all' OR (issue_type = 'issue' AND all_issue_cnt > 0) OR (issue_type = 'except' AND all_issue_cnt = 0) )
				ORDER BY promotion_seq ASC";
		$query = $this->db->query($query);
		$result = $query->result_array();
		return $result[0];
	}

	/* 일반코드 프로모션코드 전체 목록  */
	public function get_able_promotion_max($goodsSeq, $category, $brand_code)
	{
		$today = date("Y-m-d",time());

		$issue_subquery = $except_subquery = "";
		if(count($category)>0){
			$issue_subquery = "+(
												select count(*) FROM
												".$this->promotion_issuecategory."
												WHERE promotion_seq=d.promotion_seq AND `type`='issue' AND category_code IN ('".implode("','",$category)."')
											)";
			$except_subquery = "+(
													select count(*) FROM
													".$this->promotion_issuecategory."
													WHERE promotion_seq=d.promotion_seq AND `type`='except' AND category_code IN ('".implode("','",$category)."')
												)";
		}

		if(count($brand_code)>0){
			$issue_subquery .= "+(
												select count(*) FROM
												".$this->promotion_issuebrand."
												WHERE promotion_seq=d.promotion_seq AND `type`='issue' AND brand_code IN ('".implode("','",$brand_code)."')
											)";
			$except_subquery .= "+(
													select count(*) FROM
													".$this->promotion_issuebrand."
													WHERE promotion_seq=d.promotion_seq AND `type`='except' AND brand_code IN ('".implode("','",$brand_code)."')
												)";
		}

		$query = "SELECT promotion.*, except_cnt ,all_issue_cnt, issue_cnt,   duplication_use
					FROM (
						SELECT d.*,
							(
								SELECT count(*)
								FROM ".$this->promotion_issuegoods."
								WHERE promotion_seq=d.promotion_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM ".$this->promotion_issuebrand."
								WHERE promotion_seq=d.promotion_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM ".$this->promotion_issuecategory."
								WHERE promotion_seq=d.promotion_seq AND `type`='issue'
							) as all_issue_cnt,
							(
								SELECT count(*)
								FROM ".$this->promotion_issuegoods."
									WHERE promotion_seq=d.promotion_seq AND goods_seq='{$goodsSeq}' AND `type`='issue'
									){$issue_subquery} as issue_cnt,
									(
									select count(*)
									FROM ".$this->promotion_issuegoods."
									WHERE promotion_seq=d.promotion_seq AND goods_seq='{$goodsSeq}' AND `type`='except'
									){$except_subquery} as except_cnt
									FROM ".$this->table_promotion." d
						WHERE
								(type = 'promotion' or type = 'promotion_shipping') and
								(
								(d.issue_startdate is null  AND d.issue_enddate is null )
								OR
								(d.issue_startdate <='{$today}' AND d.issue_enddate >='{$today}')
								)
								) promotion
								WHERE  (except_cnt=0)
								AND (all_issue_cnt=0 OR issue_cnt>0)
								AND (issue_type = 'all' OR (issue_type = 'issue' AND all_issue_cnt > 0) OR (issue_type = 'except' AND all_issue_cnt = 0) )
								ORDER BY promotion_seq ASC";
			$query = $this->db->query($query);
			$result = $query->result_array();

			return $result;
		}


	/* 개별코드 프로모션코드 전체 목록  */
	public function get_able_download_list($goodsSeq, $category, $brand_code, $price, $cartpromotioncode, $goodprice, $ea=1 )
	{
		$today = date("Y-m-d",time());

		$issue_subquery = $except_subquery = "";
		if(count($category)>0){
			$issue_subquery = "+(
												select count(*) FROM
												".$this->promotion_download_issuecategory."
												WHERE download_seq=d.download_seq AND `type`='issue' AND category_code IN ('".implode("','",$category)."')
											)";
			$except_subquery = "+(
													select count(*) FROM
													".$this->promotion_download_issuecategory."
													WHERE download_seq=d.download_seq AND `type`='except' AND category_code IN ('".implode("','",$category)."')
												)";
		}

		if(count($brand_code)>0){
			$issue_subquery .= "+(
												select count(*) FROM
												".$this->promotion_download_issuebrand."
												WHERE download_seq=d.download_seq AND `type`='issue' AND brand_code IN ('".implode("','",$brand_code)."')
											)";
			$except_subquery .= "+(
													select count(*) FROM
													".$this->promotion_download_issuebrand."
													WHERE download_seq=d.download_seq AND `type`='except' AND brand_code IN ('".implode("','",$brand_code)."')
												)";
		}

		$query = "SELECT promotion.*, except_cnt ,all_issue_cnt, issue_cnt, used_cnt, duplication_use
					FROM (
						SELECT d.*,
							SUM(if(d.use_status='used',1,0)) used_cnt,
							SUM(if(d.use_status='unused',1,0)) unused_cnt,
							(
								SELECT count(*)
								FROM ".$this->promotion_download_issuegoods."
								WHERE download_seq=d.download_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM ".$this->promotion_download_issuebrand."
								WHERE download_seq=d.download_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM ".$this->promotion_download_issuecategory."
								WHERE download_seq=d.download_seq AND `type`='issue'
							) as all_issue_cnt,
							(
								SELECT count(*)
								FROM ".$this->promotion_download_issuegoods."
								WHERE download_seq=d.download_seq AND goods_seq='{$goodsSeq}' AND `type`='issue'
							){$issue_subquery} as issue_cnt,
							(
								select count(*)
								FROM ".$this->promotion_download_issuegoods."
								WHERE download_seq=d.download_seq AND goods_seq='{$goodsSeq}' AND `type`='except'
							){$except_subquery} as except_cnt
						FROM ".$this->promotion_download." d
						WHERE
							d.promotion_input_serialnumber='".$cartpromotioncode."' and
							(
								(d.issue_startdate is null  AND d.issue_enddate is null )
								OR
								(d.issue_startdate <='{$today}' AND d.issue_enddate >='{$today}')
							)
					) promotion
				WHERE  (except_cnt=0)
					AND (all_issue_cnt=0 OR issue_cnt>0)
					AND
					(
						( used_cnt > 0 AND duplication_use = 1 )
						OR  used_cnt = 0
					)
					AND (issue_type = 'all' OR (issue_type = 'issue' AND all_issue_cnt > 0) OR (issue_type = 'except' AND all_issue_cnt = 0) )
				ORDER BY download_seq  ASC";
		$query = $this->db->query($query);
		$result = $query->result_array();
		return $result[0];
	}


	/* 해당 상품의 프로모션코드 전체 목록  */
	public function get_able_download_saleprice($promotion_seq, $cartpromotioncode, $totalprice, $goodprice,$ea )
	{
		if(empty($promotion_seq)) return false;

		$today = date("Y-m-d",time());

		$sc['whereis'] = " and promotion_input_serialnumber ='".$cartpromotioncode."' and promotion_seq ='".$promotion_seq."' ";
		$promotioncode = $this->promotionmodel->get_data($sc);
		$promotioncode = $promotioncode[0];

		if( !(strstr($promotioncode['type'],'promotion')) ){//개별코드인경우
			$promotioncode = $this->get_download_serialnumber($promotion_seq, $cartpromotioncode);
		}

		$promotioncode['promotioncode_sale'] = $promotioncode['promotioncode_shipping_sale_max'] = $promotioncode['promotioncode_shipping_sale'] = 0;
		if( $promotioncode['limit_goods_price'] <= $totalprice ) {//사용제한 원이상인경우만
			if( $promotioncode['sale_type'] == 'percent' && $promotioncode['percent_goods_sale'] && $goodprice ){

				if( $this->config_system['cutting_price'] != 'none' ){
					$promotioncode['promotioncode_sale'] = $promotioncode['percent_goods_sale'] * $goodprice / ( $this->config_system['cutting_price'] * 100);
					$promotioncode['promotioncode_sale'] = floor($promotioncode['promotioncode_sale']);
					$promotioncode['promotioncode_sale'] = $promotioncode['promotioncode_sale'] * $this->config_system['cutting_price'];
				}else{
					$promotioncode['promotioncode_sale'] = $promotioncode['percent_goods_sale'] * $goodprice / 100;
					$promotioncode['promotioncode_sale'] = floor($promotioncode['promotioncode_sale']);
				}

				if($promotioncode['max_percent_goods_sale'] < $promotioncode['promotioncode_sale']){
					$promotioncode['promotioncode_sale'] = $promotioncode['max_percent_goods_sale'];
				}

			}else if( $promotioncode['sale_type'] == 'won' && $promotioncode['won_goods_sale'] && $goodprice ){
				$promotioncode['promotioncode_sale'] = $promotioncode['won_goods_sale'];

			}else if( $promotioncode['sale_type'] == 'shipping_free' && $promotioncode['max_percent_shipping_sale'] ){
				$promotioncode['promotioncode_shipping_sale_max'] = $promotioncode['max_percent_shipping_sale'];

			}else if( $promotioncode['sale_type'] == 'shipping_won' && $promotioncode['won_shipping_sale'] ){
				$promotioncode['promotioncode_shipping_sale'] = $promotioncode['won_shipping_sale'];
			}

			//$promotioncode['promotioncode_sale'] = get_price_point($promotioncode['promotioncode_sale']);

			// 프로모션 할인 금액 체크
			if( strstr($promotioncode['type'],'promotion') && ($promotioncode['sale_type'] == 'percent' || $promotioncode['sale_type'] == 'won') ){//일반코드인경우
				if($promotioncode['duplication_use'] == 1) $promotioncode['promotioncode_sale'] = $promotioncode['promotioncode_sale'] * $ea;
			}

			//상품의 총할인금액보다 코드할인금액이 큰경우 상품금액대체
			if( ($goodprice*$ea) < $promotioncode['promotioncode_sale'] && $promotioncode['promotioncode_sale'])
			{
				$promotioncode['promotioncode_sale'] = $goodprice;
			}
		}
		return $promotioncode;
	}


	/* 실제주문시작 제한체크시작  */
	public function get_able_download_saleprice_pay($promotion_seq, $cartpromotioncode)
	{
		if(empty($promotion_seq)) return false;

		$today = date("Y-m-d",time());
		//실제프로모션코드번호정보
		$promotioncodeData 	= $this->promotionmodel->get_promotioncode_serialnumber($cartpromotioncode);
		if( empty($promotioncodeData) ) {
			$promotioncodeData 	= $this->promotionmodel->get_promotioncode_input_serialnumber($cartpromotioncode);
		}
		if($promotioncodeData) {//프로모션코드 인증1

			$sc['whereis'] = " and promotion_input_serialnumber ='".$cartpromotioncode."' and promotion_seq ='".$promotion_seq."' ";
			$promotioncode = $this->promotionmodel->get_data($sc);
			$promotioncode = $promotioncode[0];

			if( strstr($promotioncode['type'],'promotion') ){//일반코드인경우
				if( $promotioncode['downloadLimit_member'] == 1 &&  empty($this->userInfo['member_seq']) ) {//회원여부 (포인트전환은 회원전용) 인증3
					openDialogAlert("해당 프로모션코드는 회원전용 프로모션코드입니다.\n로그인 후 이용해 주세요.",400,140,'parent',$callback);
					exit;
				}

				//유효기간체크인증4
				if( !($promotioncode['issue_startdate']<=$today && $promotioncode['issue_enddate']>=$today) ) {
					openDialogAlert("해당 프로모션코드 유효기간이 아닙니다..",400,140,'parent',$callback);
					exit;
				}

				if($promotioncode['download_limit'] == 'limit' ) {//선착순인경우
					$promotioncode_downcnt = $this->promotionmodel->get_download_serialnumber_cnt($promotion_seq, $cartpromotioncode);
					if($promotioncode['download_limit_ea'] <= $promotioncode_downcnt && $promotioncode_downcnt ){//인증 횟수가 제한되었을 경우
						openDialogAlert("해당 프로모션코드의 선착순 등록이 종료되었습니다.",400,140,'parent',$callback);
						exit;
					}
				}

			}else{//개별코드인경우 -> 발급후 이용가능//발급 또는 구매 프로모션코드 인증2


				//1회성코드 인증7
				if($promotioncodeData['use_count'] == 0){
					openDialogAlert("해당 프로모션코드는 이미 사용한 코드입니다.",400,140,'parent',$callback);
					exit;
				}

				$promotioncode_down = $this->promotionmodel->get_download_serialnumber($promotion_seq, $cartpromotioncode);
				if(!$promotioncode_down){
					openDialogAlert("해당 프로모션코드는 발급후 이용가능한 프로모션코드입니다.",400,140,'parent',$callback);
					exit;
				}

				if($promotioncode_down['use_status'] == 'used') {
					openDialogAlert("해당 프로모션코드는 이미 사용한 코드입니다.",400,140,'parent',$callback);
					exit;
				}

				//회원여부 (포인트전환은 회원전용) 인증3
				if( $promotioncode_down['downloadLimit_member'] == 1 &&  empty($this->userInfo['member_seq']) ) {
					openDialogAlert("해당 프로모션코드는 회원전용 프로모션코드입니다.\n로그인 후 이용해 주세요.",400,140,'parent',$callback);
					exit;
				}

				//유효기간체크인증4
				if( !($promotioncode_down['issue_startdate']<=$today && $promotioncode_down['issue_enddate']>=$today) ) {
					openDialogAlert("해당 프로모션코드 유효기간이 아닙니다.",400,140,'parent',$callback);
					exit;
				}

				if($promotioncode_down['download_limit'] == 'limit' ) {//선착순인경우6
					$promotioncode_downcnt = $this->promotionmodel->get_download_total_count(array("whereis"=>" and promotion_seq='{$promotion_seq}' and use_status='used' "));
					if($promotioncode_down['download_limit_ea'] <= $promotioncode_downcnt && $promotioncode_downcnt ){//인증 횟수가 제한되었을 경우
						openDialogAlert("해당 프로모션코드의 선착순 등록이 종료되었습니다.",400,140,'parent',$callback);
						exit;
					}
				}
			}
		}else{
			openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
			exit;
		}
	}

	/* 해당 배송프로모션 다운로드 프로모션코드 목록  */
	public function get_shipping_use_list($member_seq, $price, $shippingprice)
	{
		$today = date("Y-m-d",time());
		$result = "";
		$query = "select * from
		(
			select d.*
			from ".$this->promotion_download." d
			where member_seq = ?
			and issue_startdate <= ?
			and issue_enddate >= ?
			and use_status = 'unused'
		) a where type = 'shipping'
		";
		$query = $this->db->query($query,array($member_seq, $today, $today));
		foreach($query->result_array() as $data){
			$data['shipping_sale'] = 0;
			if( $data['limit_goods_price'] <= $price ) {//사용제한 원이상인경우만
				if( $data['sale_type'] == 'shipping_free' ){
					if($data['max_percent_shipping_sale'] > 0 && $data['max_percent_shipping_sale'] < $shippingprice ){
						$data['shipping_sale'] =  $data['max_percent_shipping_sale'];
					}else{
						$data['shipping_sale'] =  $shippingprice;
					}
				}else if( $data['sale_type'] == 'won'){
					$data['shipping_sale'] = $data['won_shipping_sale'];
				}

				//할인금액이 판매금액보다 큰경우 구매금액
				if($shippingprice < $data['shipping_sale']){
					$data['shipping_sale'] = $shippingprice;
				}
				$data['shipping_sale'] = get_price_point($data['shipping_sale']);
				$result[] = $data;
			}
		}
		return $result;
	}


	/* 프로모션을 사용한 주문의 프로모션 발급 상태 변경 */
	function set_orderbuy_use_status($download_seq,$orders,$status)
	{

		$use_date = date('Y-m-d H:i:s',time());
		$this->db->where('download_seq', $download_seq);
		$this->db->update($this->promotion_download, array('order_seq' => $orders['order_seq'],'member_seq_buy' => $orders['member_seq'],'use_status' => $status,'use_date' => $use_date));
	}

	/* 프로모션을 사용한 주문의 프로모션 발급 상태 변경 */
	function set_download_use_status($download_seq,$status)
	{
		$use_date = date('Y-m-d H:i:s',time());
		$this->db->where('download_seq', $download_seq);
		$this->db->update($this->promotion_download, array('use_status' => $status,'use_date' => $use_date));
	}

	//관리자 > 프로모션 직접발급
	public function _admin_downlod($promotionSeq, $memberSeq, $promotion_input_serialnumber)
	{
		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		// 프로모션 정보 확인
		$promotions = $this->get_admin_download($memberSeq, $promotionSeq);
		if($promotions) return false;//이미 다운받은 프로모션이 있습니다.

		$promotionData = $this->get_promotion($promotionSeq);
		if(!$promotionData) return false;//프로모션이 올바르지 않습니다.

		$paramInsert['member_seq']						= $memberSeq;
		$paramInsert['promotion_seq']					= $promotionSeq;
		$paramInsert['type']									= $promotionData['type'];
		$paramInsert['promotion_name']				= $promotionData['promotion_name'];
		$paramInsert['promotion_desc']					= $promotionData['promotion_desc'];
		$paramInsert['promotion_point']					= $promotionData['promotion_point'];
		$paramInsert['download_limit']					= $promotionData['download_limit'];
		$paramInsert['download_limit_ea']				= $promotionData['download_limit_ea'];
		$paramInsert['sale_type']							= $promotionData['sale_type'];
		$paramInsert['issue_type']							= $promotionData['issue_type'];
		$paramInsert['downloadLimit_member']		= if_empty($promotionData, 'downloadLimit_member', '0');
		$paramInsert['max_percent_shipping_sale'] = if_empty($promotionData, 'max_percent_shipping_sale', '0');
		$paramInsert['won_shipping_sale']			=  if_empty($promotionData, 'won_shipping_sale', '0');
		$paramInsert['percent_goods_sale']			= if_empty($promotionData, 'percent_goods_sale', '0');
		$paramInsert['max_percent_goods_sale']	= if_empty($promotionData, 'max_percent_goods_sale', '0');
		$paramInsert['won_goods_sale']				= if_empty($promotionData, 'won_goods_sale', '0');
		$paramInsert['duplication_use']					= $promotionData['duplication_use'];
		$paramInsert['limit_goods_price']				= if_empty($promotionData, 'limit_goods_price', '0');
		$paramInsert['promotion_input_serialnumber']= $promotion_input_serialnumber;
		$paramInsert['use_status']							= 'unused';
		$paramInsert['regist_date']							= $now;
		if($promotionData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $promotionData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $promotionData['issue_startdate'];
			$paramInsert['issue_enddate']	= $promotionData['issue_enddate'];
		}
		$this->db->insert($this->promotion_download, $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		$promotionGoods 	= $this->get_promotion_issuegoods($promotionSeq);
		if($promotionGoods) foreach($promotionGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuegoods, $paramInsert);
			unset($paramInsert);
		}

		$promotionCategory = $this->get_promotion_issuecategory($promotionSeq);
		if($promotionCategory) foreach($promotionCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuecategory, $paramInsert);
			unset($paramInsert);
		}


		$promotionBrand 	= $this->get_promotion_issuebrand($promotionSeq);
		if($promotionBrand) foreach($promotionBrand as $paramInsert){
			unset($paramInsert['issuebrand_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuebrand, $paramInsert);
			unset($paramInsert);
		}

		return true;
	}

	//사용자 > 프로모션코드 주문시 일반코드 발급하기
	public function _members_buy_downlod( $promotionSeq, $orders, $promotion_input_serialnumber)
	{
		if(empty($promotionSeq))return false;

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		// 프로모션코드 발급정보 확인
		//$promotions = $this->get_orderby_download($memberSeq_buy, $promotionSeq);
		//if($promotions) return false;//이미 주문된 프로모션코드가 있는경우

		$promotionData 	= $this->get_promotion($promotionSeq);
		if(!$promotionData) return false;//프로모션zhem이 올바르지 않습니다.

		$paramInsert['promotion_seq']					= $promotionSeq;
		$paramInsert['member_seq']						= $orders['member_seq'];
		$paramInsert['member_seq_buy']				= $orders['member_seq'];
		$paramInsert['order_seq']							= $orders['order_seq'];
		$paramInsert['type']									= $promotionData['type'];
		$paramInsert['promotion_type']					= $promotionData['promotion_type'];
		$paramInsert['promotion_point']					= $promotionData['promotion_point'];
		$paramInsert['download_limit']					= $promotionData['download_limit'];
		$paramInsert['download_limit_ea']				= $promotionData['download_limit_ea'];
		$paramInsert['promotion_name']				= $promotionData['promotion_name'];
		$paramInsert['promotion_desc']					= $promotionData['promotion_desc'];
		$paramInsert['sale_type']							= $promotionData['sale_type'];
		$paramInsert['issue_type']							= $promotionData['issue_type'];
		$paramInsert['downloadLimit_member']		= if_empty($promotionData, 'downloadLimit_member', '0');
		$paramInsert['max_percent_shipping_sale'] = if_empty($promotionData, 'max_percent_shipping_sale', '0');
		$paramInsert['won_shipping_sale']			=  if_empty($promotionData, 'won_shipping_sale', '0');
		$paramInsert['percent_goods_sale']			= if_empty($promotionData, 'percent_goods_sale', '0');
		$paramInsert['max_percent_goods_sale']	= if_empty($promotionData, 'max_percent_goods_sale', '0');
		$paramInsert['won_goods_sale']				= if_empty($promotionData, 'won_goods_sale', '0');
		$paramInsert['duplication_use']					= $promotionData['duplication_use'];
		$paramInsert['limit_goods_price']				= if_empty($promotionData, 'limit_goods_price', '0');
		$paramInsert['promotion_input_serialnumber']= $promotion_input_serialnumber;;
		$paramInsert['use_status']							= 'used';//사용함으로 처리함!!!!
		$paramInsert['use_date']							= $now;
		$paramInsert['regist_date']							= $now;

		if($promotionData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $promotionData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $promotionData['issue_startdate'];
			$paramInsert['issue_enddate']	= $promotionData['issue_enddate'];
		}

		$this->db->insert($this->promotion_download, $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		$promotionGoods 	= $this->get_promotion_issuegoods($promotionSeq);
		if($promotionGoods) foreach($promotionGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuegoods, $paramInsert);
			unset($paramInsert);
		}

		$promotionCategory = $this->get_promotion_issuecategory($promotionSeq);
		if($promotionCategory) foreach($promotionCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuecategory, $paramInsert);
			unset($paramInsert);
		}

		$promotionBrand 	= $this->get_promotion_issuebrand($promotionSeq);
		if($promotionBrand) foreach($promotionBrand as $paramInsert){
			unset($paramInsert['issuebrand_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuebrand, $paramInsert);
			unset($paramInsert);
		}

		return $downloadSeq;
	}

	//사용자 > 프로모션 포인트교환
	public function _members_point_downlod( $promotionSeq, $memberSeq, $promotion_input_serialnumber)
	{
		if(empty($memberSeq))return false;
		if(empty($promotionSeq))return false;

		$now_timestamp = time();
		$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		// 프로모션 정보 확인
		$promotions = $this->get_admin_download($memberSeq, $promotionSeq);
		//if($promotions) return false;//이미 다운받은 프로모션이 있습니다.

		$promotionData 	= $this->get_promotion($promotionSeq);
		if(!$promotionData) return false;//프로모션이 올바르지 않습니다.

		$paramInsert['member_seq']						= $memberSeq;
		$paramInsert['promotion_seq']					= $promotionSeq;
		$paramInsert['type']									= $promotionData['type'];
		$paramInsert['promotion_type']					= $promotionData['promotion_type'];
		$paramInsert['promotion_point']					= $promotionData['promotion_point'];
		$paramInsert['download_limit']					= $promotionData['download_limit'];
		$paramInsert['download_limit_ea']				= $promotionData['download_limit_ea'];
		$paramInsert['promotion_name']				= $promotionData['promotion_name'];
		$paramInsert['promotion_desc']					= $promotionData['promotion_desc'];
		$paramInsert['sale_type']							= $promotionData['sale_type'];
		$paramInsert['issue_type']							= $promotionData['issue_type'];
		$paramInsert['downloadLimit_member']		= if_empty($promotionData, 'downloadLimit_member', '0');
		$paramInsert['max_percent_shipping_sale'] = if_empty($promotionData, 'max_percent_shipping_sale', '0');
		$paramInsert['won_shipping_sale']			=  if_empty($promotionData, 'won_shipping_sale', '0');
		$paramInsert['percent_goods_sale']			= if_empty($promotionData, 'percent_goods_sale', '0');
		$paramInsert['max_percent_goods_sale']	= if_empty($promotionData, 'max_percent_goods_sale', '0');
		$paramInsert['won_goods_sale']				= if_empty($promotionData, 'won_goods_sale', '0');
		$paramInsert['duplication_use']					= $promotionData['duplication_use'];
		$paramInsert['limit_goods_price']				= if_empty($promotionData, 'limit_goods_price', '0');
		$paramInsert['promotion_input_serialnumber']= $promotion_input_serialnumber;
		$paramInsert['use_status']							= 'unused';
		$paramInsert['regist_date']							= $now;

		if($promotionData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $promotionData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $promotionData['issue_startdate'];
			$paramInsert['issue_enddate']	= $promotionData['issue_enddate'];
		}

		if($promotionData['type'] == 'point' || $promotionData['type'] == 'point_shipping'){//point 전환쿠폰
			$this->load->model('membermodel');
			$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보
			if( $this->mdata['point']<1 || $this->mdata['point'] < $promotionData['promotion_point'] ) {//포인트가 작거나 없는 경우
				if( $this->mdata['point']<1 ) {//포인트가 작거나 없는 경우
					return false;// 보유포인트가 없습니다
				}else{
					return false;//전환포인트 금액이 보유포인트보다 작습니다
				}
			}
		}
		$this->db->insert($this->promotion_download, $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		if($promotionData['type'] == 'point' || $promotionData['type'] == 'point_shipping'){//point 전환쿠폰
			$this->load->model('membermodel');
			$params = array(
				'gb'		=> 'minus',
				'type'		=> 'promotioncode',
				'promotioncode'		=> $promotion_input_serialnumber,
				'point'	=> $promotionData['promotion_point'],
				'memo'		=> "[차감]포인트전환 프로모션코드 [".$promotionData['promotion_name']."] 신청에 의한 포인트 차감",
			);//:".$downloadSeq."

			$this->membermodel->point_insert($params, $memberSeq);
		}

		$promotionGoods 	= $this->get_promotion_issuegoods($promotionSeq);
		if($promotionGoods) foreach($promotionGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuegoods, $paramInsert);
			unset($paramInsert);
		}

		$promotionCategory = $this->get_promotion_issuecategory($promotionSeq);
		if($promotionCategory) foreach($promotionCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuecategory, $paramInsert);
			unset($paramInsert);
		}

		$promotionBrand 	= $this->get_promotion_issuebrand($promotionSeq);
		if($promotionBrand) foreach($promotionBrand as $paramInsert){
			unset($paramInsert['issuebrand_seq'],$paramInsert['promotion_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert($this->promotion_download_issuebrand, $paramInsert);
			unset($paramInsert);
		}
		return true;
	}

	/* 환불에 의한 프로모션 복원 */
	public function restore_used_promotion($download_seq){

		$sql = "select * from ".$this->promotion_download." where download_seq=?";
		$query = $this->db->query($sql,array($download_seq));
		list($download) = $query->result_array($query);

		$sqlck = "select * from ".$this->promotion_download." where refund_download_seq=?";
		$queryck = $this->db->query($sqlck,array($download_seq));
		list($downloadck) = $queryck->result_array($queryck);

		if($download && !strstr($download['type'],'promotion')  && !$downloadck ) {//개별코드인경우에만

			$remain_issue_day = (strtotime($download['issue_enddate'])-strtotime(substr($download['use_date'],0,10))) / 86400;
			$remain_issue_day = $remain_issue_day ? $remain_issue_day : 1;

			$download['regist_date']		= date('Y-m-d H:i:s');
			$download['issue_startdate']	= date('Y-m-d');
			$download['issue_enddate']		= date('Y-m-d',strtotime("+{$remain_issue_day} day"));
			$download['promotion_name']		= "[복원]".$download['promotion_name'];
			$download['refund_download_seq']= $download_seq;

			unset($download['download_seq']);
			unset($download['use_status']);
			unset($download['use_date']);
			//unset($download['order_seq']);

			$this->db->insert($this->promotion_download, $download);
			$item_seq = $this->db->insert_id();

			$success = $item_seq;

			$promotionGoods 	= $this->get_promotion_download_issuegoods($download_seq);
			if($promotionGoods) foreach($promotionGoods as $paramInsert){
				unset($paramInsert['issuegoods_seq'],$paramInsert['promotion_seq']);
				$paramInsert['download_seq'] = $success;
				$this->db->insert($this->promotion_download_issuegoods, $paramInsert);
				unset($paramInsert);
			}

			$promotionCategory = $this->get_promotion_download_issuecategory($download_seq);
			if($promotionCategory) foreach($promotionCategory as $paramInsert){
				unset($paramInsert['issuecategory_seq'],$paramInsert['promotion_seq']);
				$paramInsert['download_seq'] = $success;
				$this->db->insert($this->promotion_download_issuecategory, $paramInsert);
				unset($paramInsert);
			}


			$promotionBrand 	= $this->get_promotion_download_issuebrand($download_seq);
			if($promotionBrand) foreach($promotionBrand as $paramInsert){
				unset($paramInsert['issuebrand_seq'],$paramInsert['promotion_seq']);
				$paramInsert['download_seq'] = $success;
				$this->db->insert($this->promotion_download_issuebrand, $paramInsert);
				unset($paramInsert);
			}


		}else{
			$success = false;
		}

		return $success;
	}


	/* 환불에 의한 프로모션검색 */
	public function restore_used_promotioncode_refund($refund_download_seq){
		$sql = "select * from ".$this->promotion_download." where refund_download_seq=?";
		$query = $this->db->query($sql,array($refund_download_seq));
		list($download) = $query->result_array($query);
		if($download){
			$success = $download['download_seq'];
		}else{
			$success = false;
		}
		return $success;
	}





	//프로모션
	public function get_promotioncode_total_count($sc)
	{
		$sql = 'select code_seq from '.$this->promotioncode.' where 1 '. $sc['whereis'];
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 인증번호 보기
	* @param
	*/
	public function promotioncode_list($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, c.*, code.*
					from ".$this->promotioncode." code
					left join ".$this->table_promotion." c on c.promotion_seq = code.promotion_seq where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and code.promotion_seq = '.$sc['no'].' ';//$sql .= ' and code.code_seq = '.$sc['no'].' ';
		}

		if(!empty($sc['search_text']))$sql.= " and code.code_serialnumber like '%".$sc['search_text']."%' ";

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and code.regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and code.regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and d.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		$sql.=" order by code.code_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}

	// 총건수
	public function get_promotioncode_item_total_count($no)
	{
		$sql = 'select code_seq from '.$this->promotioncode.' where 1';
		if( !empty($no) )
		{
			$sql .= ' and promotion_seq = '.$no.' ';
		}

		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	//개별코드프로모션 정보가가져오기
	public function get_promotioncode($no)
	{
		$this->db->limit(1,0);
		$this->db->where('code_seq', $no);
		$query = $this->db->get($this->promotioncode);
		$result = $query->result_array();
		return $result[0];
	}

	//개별코드 가져오기
	public function get_promotioncode_serialnumber($code_serialnumber)
	{
		$this->db->limit(1,0);
		$this->db->order_by("code_seq","desc");
		$this->db->where('code_serialnumber', $code_serialnumber);
		$query = $this->db->get($this->promotioncode);
		$result = $query->result_array();
		return $result[0];
	}

	/*프로모션코드 > 자동등록 : 사용건수 - 변경 */
	function set_promotioncode_use_count($code_serialnumber)
	{
		$upsql = "update ".$this->promotioncode." set use_count = use_count-1 where code_serialnumber = '{$code_serialnumber}'";
		$this->db->query($upsql);
	}


	//프로모션코드 > 수동등록
	public function get_promotioncode_input_total_count($sc)
	{
		$sql = 'select promotion_seq from '.$this->promotioncode_input.' where 1 '. $sc['whereis'];
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 인증번호 보기  > 수동등록
	* @param
	*/
	public function promotioncode_input_list($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, code.*
					from ".$this->promotioncode_input." code
					left join ".$this->table_promotion." c on c.promotion_seq = code.promotion_seq where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and code.promotion_seq = '.$sc['no'].' ';
		}

		if(!empty($sc['search_text']))$sql.= " and code.code_serialnumber like '%".$sc['search_text']."%' ";

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and code.regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and code.regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and d.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		$sql.=" order by code.use_count asc, code.down_use desc ";//$sql.=" order by code.code_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}

	//프로모션코드 > 수동등록 총건수
	public function get_promotioncode_input_item_total_count($no)
	{
		$sql = 'select code_seq from '.$this->promotioncode_input.' where 1';
		if( !empty($no) )
		{
			$sql .= ' and promotion_seq = '.$no.' ';
		}
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	//프로모션코드 > 수동발급코드가져오기
	public function get_promotioncode_input_item($no, $sc)
	{
		$sql = 'select * from '.$this->promotioncode_input."  where 1 ". $sc['whereis'];
		if( !empty($no) )
		{
			$sql .= ' and promotion_seq = '.$no.' ';
		}
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}



	//개별코드 수동프로모션 가져오기
	public function get_promotioncode_input($no)
	{
		$this->db->limit(1,0);
		$this->db->where('code_seq', $no);
		$query = $this->db->get($this->promotioncode_input);
		$result = $query->result_array();
		return $result[0];
	}


	//개별코드 수동프로모션 가져오기
	public function get_promotioncode_input_serialnumber($code_serialnumber)
	{
		$this->db->limit(1,0);
		$this->db->order_by("code_seq","desc");
		$this->db->where('code_serialnumber', $code_serialnumber);
		$query = $this->db->get($this->promotioncode_input);
		$result = $query->result_array();
		return $result[0];
	}

	/*프로모션코드 > 수동프로모션 : 사용건수 - 변경 */
	function set_promotioncode_input_use_count($code_serialnumber)
	{
		$upsql = "update ".$this->promotioncode_input." set use_count = use_count-1 where code_serialnumber = '{$code_serialnumber}'";
		$this->db->query($upsql);
	}

	/*프로모션코드 > 수동(엑셀) 발급여부 업데이트 */
	function set_promotioncode_down_use($code_serialnumber)
	{
		$upsql = "update ".$this->promotioncode_input." set down_use = 1 where code_serialnumber = '{$code_serialnumber}' and down_use = 0";
		$this->db->query($upsql);
	}

	//보유한 프로모션 리스트
	public function my_download_list($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, m.userid, m.user_name, d.*
					from ".$this->promotion_download." d
					left join ".$this->members." m on m.member_seq = d.member_seq where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and d.promotion_seq = '.$sc['no'].' ';
		}

		if(isset($sc['member_seq'])) $sql.= ' and m.member_seq ='.$sc['member_seq'];//회원

		if( !empty($sc['search_text']) )
		{
			$sql .= ' and ( m.user_name like "%'.$sc['search_text'].'%" or m.userid  like "%'.$sc['search_text'].'%") ';//
		}

		if(!empty($sc['use_status']))
		{
			$sql.= " and d.use_status='".$sc['use_status']."'";
		}

		if(!empty($sc['promotionUsed']))
		{
			$promotionTypein = implode("','",$sc['promotionUsed']);
			$sql.= " and use_status in ('".$promotionTypein."') ";
		}

		if(!empty($sc['promotionDate'])){
			$arr = array();
			foreach($sc['promotionDate'] as $key => $cdata){
				switch($cdata){
					case "available":
						$today = date('Y-m-d H:i:s');
						$arr[] =" d.issue_enddate >= '{$today}' ";
					break;
					case "extinc":
						$today = date('Y-m-d H:i:s');
						$arr[] =" d.issue_enddate < '{$today}' ";
					break;
				}
			}
			if($arr) $sql.= " and (".implode(' OR ',$arr).")";
		}



		if(!empty($sc['keyword']))$sql.= " and promotion_name like \"%".$sc['keyword']."%\" ";

		if($sc['check_date']=='regist_date'){
			// 발급일 검색(시작)
			if($sc['sdate'] AND !$sc['edate']) {
				$start_date = $sc['sdate'].' 00:00:00';
				$sql.=" and d.regist_date >= '{$start_date}' ";
			}

			// 발급일 검색(끝)
			if($sc['edate'] AND !$sc['sdate']) {
				$start_date = $sc['edate'].' 23:59:59';
				$sql.=" and d.regist_date <= '{$start_date}' ";
			}

			// 발급일 검색
			if($sc['sdate'] AND $sc['edate']) {
				$start_date = $sc['sdate'].' 00:00:00';
				$end_date = $sc['edate'].' 23:59:59';
				$sql.=" and d.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
			}
		}elseif ($sc['check_date']=='use_date'){
		// 발급일 검색(시작)
			if($sc['sdate'] AND !$sc['edate']) {
				$start_date = $sc['sdate'].' 00:00:00';
				$sql.=" and d.use_date >= '{$start_date}' ";
			}

			// 발급일 검색(끝)
			if($sc['edate'] AND !$sc['sdate']) {
				$start_date = $sc['edate'].' 23:59:59';
				$sql.=" and d.use_date <= '{$start_date}' ";
			}

			// 발급일 검색
			if($sc['sdate'] AND $sc['edate']) {
				$start_date = $sc['sdate'].' 00:00:00';
				$end_date = $sc['edate'].' 23:59:59';
				$sql.=" and d.use_date BETWEEN '{$start_date}' and '{$end_date}' ";
			}
		}


		$sql.=" order by d.download_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();


		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}

	//	사용가능한 건수
	public function get_download_have_total_count($sc, $members)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, m.userid, m.user_name, d.*
					from ".$this->promotion_download." d
					left join ".$this->members." m on m.member_seq = d.member_seq where 1 ";
		if(isset($sc['member_seq'])) $sql.= ' and m.member_seq ='.$sc['member_seq'];//회원

		$query = $this->db->query($sql);
		$result['totalcount'] = $query->num_rows();

		$sql = "select SQL_CALC_FOUND_ROWS *, m.userid, m.user_name, d.*
					from ".$this->promotion_download." d
					left join ".$this->members." m on m.member_seq = d.member_seq where
					 use_status='unused' and d.issue_enddate >= date(now()) ";
		if(isset($sc['member_seq'])) $sql.= ' and m.member_seq ='.$sc['member_seq'];//회원
		$query = $this->db->query($sql);
		$result['unusedcount'] = $query->num_rows();


		$sql = "SELECT SQL_CALC_FOUND_ROWS promotion.*
					FROM (
						SELECT c.*,
							SUM(if(d.use_status='used',1,0)) used_cnt,
							SUM(if(d.use_status='unused',1,0)) unused_cnt,
							(
								SELECT count(*)
								FROM fm_promotion_group
								WHERE promotion_seq=c.promotion_seq
							) as allgroup_issue_cnt
						FROM ".$this->table_promotion." c
							LEFT JOIN ".$this->promotion_download." d ON c.promotion_seq = d.promotion_seq AND d.member_seq='".$members['member_seq']."'
						GROUP BY c.promotion_seq
					) promotion
				WHERE
					((allgroup_issue_cnt =0) OR (allgroup_issue_cnt>0))
					AND
					(
						( used_cnt > 0 AND duplication_use = 1 )
						OR  (used_cnt = 0 AND unused_cnt=0)
					)";
		$sql.=" order by promotion.promotion_seq desc ";
		$query = $this->db->query($sql);
		$result['svcount'] = $query ->num_rows();


		return $result;
	}


/** -cheol
	*@ 회원의 다운로드 가능 프로모션 목록
	* 생일자 : 생일전 ~ 생일후 기간
	* 배송비 : 발급전 ~ 발급후 기간
	* 등록 : 등급조정 이후기간
	==> AND 등급제한 체크
	==> AND 전체수량제한
	**/
	public function get_my_download($sc, $members)
	{
		$sql = "";
		$sql.=" order by promotion.promotion_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		//총건수
		$query = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($query);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];
		return $data;
	}

	//주문완료후 -> 발급과 사용
	public function setPromotionpayment($orders) {
		$promotionSeq = $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
		$cartpromotioncode = $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
		if(empty($promotionSeq) || empty($cartpromotioncode)) {
			return false;
		}

		$this->get_able_download_saleprice_pay($promotionSeq, $cartpromotioncode);

		$promotioncode_input = false;
		$promotioncodeData 	= $this->promotionmodel->get_promotioncode_serialnumber($cartpromotioncode);
		if( empty($promotioncodeData) ) {
			$promotioncode_input = true;
			$promotioncodeData 	= $this->promotionmodel->get_promotioncode_input_serialnumber($cartpromotioncode);
		}

		if($promotioncodeData) {//프로모션코드 인증1
			$sc['whereis'] = " and promotion_input_serialnumber ='".$cartpromotioncode."' and promotion_seq ='".$promotionSeq."' ";
			$promotioncode = $this->promotionmodel->get_data($sc);
			$promotioncode = $promotioncode[0];

			if( strstr($promotioncode['type'],'promotion') ) {//일반코드인경우
				//일반코드 -> 발급시 사용함처리됨
				$downloadSeq = $this->_members_buy_downlod( $promotionSeq, $orders, $cartpromotioncode);
				if( $promotioncode['sale_type'] == 'shipping_free' || $promotioncode['sale_type'] == 'shipping_won' ) {//배송비할인
					$upsql = "update fm_order set  shipping_promotion_code_seq='".$downloadSeq."' where order_seq = '".$orders['order_seq']."' ";
					$this->db->query($upsql);
				}else{
					$upsql = "update fm_order_item_option set  promotion_code_seq='".$downloadSeq."' where order_seq = '".$orders['order_seq']."' ";
					$this->db->query($upsql);
				}
			}else{//개별코드인경우 -> 중복사용불가
				$promotioncode_down = $this->get_download_serialnumber($promotionSeq, $cartpromotioncode);
				$this->set_orderbuy_use_status($promotioncode_down['download_seq'],$orders,'used');

				if( $promotioncode_down['sale_type'] == 'shipping_free' || $promotioncode_down['sale_type'] == 'shipping_won' ) {
					//배송비할인
					$upsql = "update fm_order set  shipping_promotion_code_seq='".$promotioncode_down['download_seq']."' where order_seq = '".$orders['order_seq']."'";// and provider_seq=1
					$this->db->query($upsql);
				}else{
					$upsql = "update fm_order_item_option set  promotion_code_seq='".$promotioncode_down['download_seq']."' where order_seq = '".$orders['order_seq']."' ";
					$this->db->query($upsql);
				}
			}

			if($promotioncode_input){
				$this->set_promotioncode_input_use_count($cartpromotioncode);
			}else{
				$this->set_promotioncode_use_count($cartpromotioncode);
			}
		}
		//프로모션코드session초기화
		$this->session->unset_userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
		$this->session->unset_userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
	}


	//발급내역 > 총 할인금액추출
	public function get_promotiontotal($sc, $promotions)
	{
		if(isset($sc['member_seq'])) {
			$addsql.= ' and m.member_seq ='.$sc['member_seq'];//회원
			$memberleftjoin = true;
		}
		if( !empty($sc['search_text']) )
		{
			$addsql .= ' and ( m.user_name like "%'.$sc['search_text'].'%" or m.userid  like "%'.$sc['search_text'].'%") ';//
			$memberleftjoin = true;
		}

		if( $memberleftjoin ) {
			$memberleftjoin1 = " left join fm_member m on m.member_seq = ord.member_seq";
			$memberleftjoin2 = " left join fm_order ord on ord.order_seq = o.order_seq 
				left join fm_member m on m.member_seq = ord.member_seq
			";
		}

		if(!empty($sc['use_status']))
		{
			$addsql.= " and down.use_status='".$sc['use_status']."'";
		}

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$addsql.=" and down.regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$addsql.=" and down.regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$addsql.=" and down.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		if ( strstr($promotions['type'],'shipping') ) {//배송비할인
			$totalsalequery = "select sum(ord.shipping_promotion_code_sale) as shipping_promotion_code_sale
				from fm_download_promotion down
				left join fm_order ord on ord.shipping_promotion_code_seq = down.download_seq
				{$memberleftjoin1}
				WHERE down.promotion_seq='".$sc[no]."'".$addsql;
			$promotion_order_saleprice_query = $this->db->query($totalsalequery);
			$promotion_order_saleprice = $promotion_order_saleprice_query->row_array();
			$promotion_order_saleprice['promotion_code_sale'] = ($promotion_order_saleprice['shipping_promotion_code_sale'])?$promotion_order_saleprice['shipping_promotion_code_sale']:0;
		}else{
			$totalsalequery = "select sum(o.promotion_code_sale) as promotion_code_sale
				from fm_download_promotion down
				left join fm_order_item_option o on o.promotion_code_seq = down.download_seq
				{$memberleftjoin2}
				WHERE down.promotion_seq = '".$sc[no]."'".$addsql;
			$promotion_order_saleprice_query = $this->db->query($totalsalequery);
			$promotion_order_saleprice = $promotion_order_saleprice_query->row_array();
			$promotion_order_saleprice['promotion_code_sale'] = ($promotion_order_saleprice['promotion_code_sale'])?$promotion_order_saleprice['promotion_code_sale']:0;
		}
		return $promotion_order_saleprice;
	}


}

/* End of file promotionmodel.php */
/* Location: ./app/models/promotionmodel.php */