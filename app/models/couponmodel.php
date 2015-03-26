<?php
class Couponmodel extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->table_coupon								 = 'fm_coupon';								//쿠폰

		$this->coupon_group								 = 'fm_coupon_group';						//회원
		$this->coupon_issuecategory					 = 'fm_coupon_issuecategory';		//카테고리
		$this->coupon_issuegoods						 = 'fm_coupon_issuegoods';			//상품

		$this->coupon_download							 = 'fm_download';							//발급
		$this->coupon_download_issuecategory	 = 'fm_download_issuecategory';	//발급카테고리
		$this->coupon_download_issuegoods		 = 'fm_download_issuegoods';		//발급상품
		$this->members										 = 'fm_member';

		$this->offlinecoupon								 = 'fm_offline_coupon';							//오프라인 자동
		$this->offlinecoupon_input						 = 'fm_offline_coupon_input';					//오프라인 수동

		$this->couponTypeTitle = array("download"=>"상품","mobile"=>"모바일","birthday"=>"생일자","anniversary"=>"기념일","shipping"=>"배송비","memberGroup"=>"회원등급조정", "member"=>"신규가입", "admin"=>"직접 발급", "offline_coupon"=>"상품(인증번호)", "offline_emoney"=>"인쇄용 (적립금)", "point"=>"포인트 전환",'memberGroup_shipping'=>"회원등급조정(배송비)",'member_shipping'=>"신규가입(배송비)", 'admin_shipping'=>"직접 발급(배송비)",'memberlogin'=>"이달의 컴백회원",'memberlogin_shipping'=>"이달의 컴백회원(배송비)",'membermonths'=>"이달의 등급",'membermonths_shipping'=>"이달의 등급(배송비)",'order'=>"첫 구매");
		
		$this->couponTypeShortTitle = array("download"=>"상품","mobile"=>"모바일","birthday"=>"생일자","anniversary"=>"기념일","shipping"=>"배송비","memberGroup"=>"등급조정", "member"=>"신규가입", "admin"=>"직접 발급", "offline_coupon"=>"상품", "offline_emoney"=>"인쇄용 (적립금)", "point"=>"포인트 전환",'memberGroup_shipping'=>"등급조정(배송비)",'member_shipping'=>"신규가입(배송비)", 'admin_shipping'=>"직접 발급(배송비)",'memberlogin'=>"컴백회원",'memberlogin_shipping'=>"컴백회원(배송비)",'membermonths'=>"이달의 등급",'membermonths_shipping'=>"이달의 등급(배송비)",'order'=>"첫 구매");

		$this->couponIssueType = array("all"=>"전상품","issue"=>"특정상품적용","except"=>"특정상품제외");

		//이벤트페이지의 쿠폰 구분
		$this->couponpagetype = array(
			"mypage"=>array("birthday","anniversary","memberGroup"=>"membergroup","memberGroup_shipping"=>"membergroup_shipping"),
			"promotionpage"=>array("shipping","member","member_shipping","memberlogin","memberlogin_shipping","membermonths","membermonths_shipping","order")); 

		//배송비관련 쿠폰모음
		$this->coupontotaltype = array("birthday","anniversary","memberGroup","shipping","member","memberlogin","membermonths","order");
		$this->couponshipping = array("memberGroup","member","memberlogin","membermonths");

		$this->copuonupload_dir = ROOTPATH.'data/coupon/';//첨부파일폴더
		$this->copuonupload_src = '/data/coupon/';
		$this->today = date("Y-m-d",time());
	}

	/*
	 * 관리자>쿠폰목록
	 * @param
	*/
	public function coupon_list($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS * from ".$this->table_coupon." where 1";

		if(!empty($sc['search_text']))$sql.= " and coupon_name like \"%".$sc['search_text']."%\" ";

		if(!empty($sc['couponType']))
		{
			$couponTypein = implode("','",$sc['couponType']);
			$sql.= " and type in ('".$couponTypein."') ";
		}

		if(!empty($sc['use_type']))
		{
			$use_type = implode("','",$sc['use_type']);
			$sql.= " and use_type in ('".$use_type."') ";
		}

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and update_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and update_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and update_date BETWEEN '{$start_date}' AND '{$end_date}' ";
		}
 
		//발급여부
		if(!empty($sc['issue_stop0']) && !empty($sc['issue_stop1']) ) { 
			//$sql.= " and issue_stop = '0' "; 
		}elseif(!empty($sc['issue_stop0']) && empty($sc['issue_stop1']) ) { 
			$sql.= " and issue_stop != '1' "; 
		}elseif(empty($sc['issue_stop0']) && !empty($sc['issue_stop1']) ) {
			$sql.= " and issue_stop = '1' "; 
		} 
		
		//단독
		if(!empty($sc['coupon_same_time'])) {
			$sql.= " and coupon_same_time = '{$sc[coupon_same_time]}' ";  
		} 
		//제한금액
		if(!empty($sc['limit_goods_price'])) {
			$sql.= " and limit_goods_price >= '{$sc[limit_goods_price]}' "; 
		} 
	   //모바일여부
		if(!empty($sc['sale_agent'])) {
			$sql.= " and sale_agent = '{$sc[sale_agent]}' "; 
		} 
	   //무통장여부
		if(!empty($sc['sale_payment'])) {
			$sql.= " and sale_payment = '{$sc[sale_payment]}' "; 
		} 
		//유입경로여부
		if(!empty($sc['sale_referer'])) {
			$sql.= " and sale_referer = '{$sc[sale_referer]}' "; 
		}  
		// 정렬
		if($sc['orderby'] ) {
			$sql.=" order by {$sc['orderby']} {$sc['sort']} ";
		} else {
			$sql.=" order by coupon_seq desc ";
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
		$sql = 'select coupon_seq from '.$this->table_coupon;
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	//생일자/신규회원가입 쿠폰은 다중지급. //@2014-06-30다중지급가능
	public function get_coupon_multi_list($sc)
	{
		$sc['whereis'] = ($sc['whereis'])?' where 1 '. $sc['whereis']:'';
		$sql = 'select coupon_seq from '.$this->table_coupon.$sc['whereis'];
		$query = $this->db->query($sql);  
		return $query->result_array();
	}

	//발급받은 쿠폰정보가져오기
	public function get_download_coupon($download_seq)
	{
		$this->db->where('download_seq', $download_seq);
		$query = $this->db->get($this->coupon_download);
		$result = $query->row_array();
		return $result;
	}


	// 발급총건수 와 사용건수
	public function get_download_total_count($sc)
	{
		$sc['whereis'] = ($sc['whereis'])?' where 1 '. $sc['whereis']:'';
		$sql = 'select coupon_seq from '.$this->coupon_download.$sc['whereis'];
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 관리자 > 발급내역관리
	 * @param
	*/
	public function download_list($sc)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, m.userid, m.user_name,
					(select bname from fm_member_business where member_seq = d.member_seq) as bname,
					d.*
					from ".$this->coupon_download." d
					left join ".$this->members." m on m.member_seq = d.member_seq where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and d.coupon_seq = '.$sc['no'].' ';
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
		if(!empty($sc['keyword']))$sql.= " and coupon_name like \"%".$sc['keyword']."%\" ";

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

	// 다운받은 총건수
	public function get_download_item_total_count($no)
	{
		$sql = 'select download_seq from '.$this->coupon_download.' where 1';
		if( !empty($no) )
		{
			$sql .= ' and coupon_seq = '.$no.' ';
		}

		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	public function get_coupon($no)
	{
		//$this->db->limit(1,0);
		$this->db->where('coupon_seq', $no);
		$query = $this->db->get('fm_coupon');
		$result = $query->row_array();
		return $result;
	}

	public function get_coupon_group($no)
	{
		$result = false;
		$this->db->where('coupon_seq', $no);
		$query = $this->db->get('fm_coupon_group');
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_coupon_issuecategory($no)
	{
		$result = false;
		$this->db->where('coupon_seq', $no);
		$query = $this->db->get('fm_coupon_issuecategory');
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_coupon_issuegoods($no)
	{
		$result = false;
		$this->db->where('coupon_seq', $no);
		$query = $this->db->get('fm_coupon_issuegoods');
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}


	/* 발급여부체크 */
	public function get_admin_download($memberSeq,$couponSeq)
	{
		$sql = "SELECT * FROM fm_download where coupon_seq='".$couponSeq."' and member_seq='".$memberSeq."' order by download_seq desc ";//최근다운받은쿠폰기준
		$query = $this->db->query($sql);
		list($result) = $query->result_array();
		return $result;
	}


	/* 오프라인쿠폰 >  발급갯수 */
	public function get_offlinecoupon_download_cnt($memberSeq,$couponSeq)
	{
		$sql = "SELECT coupon_seq FROM fm_download where coupon_seq='".$couponSeq."' and member_seq='".$memberSeq."'  ";
		$sql .= " and refund_download_seq is null ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* 오프라인쿠폰 >  인증번호 중복체크 (자동생성의 랜덤 또는 수동생성의 수동등록) */
	public function get_offlinecoupon_serialnumber_download_cnt( $couponSeq, $offline_serialnumber)
	{
		$sql = "SELECT coupon_seq FROM fm_download where coupon_seq='".$couponSeq."' and offline_input_serialnumber='".$offline_serialnumber."' ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	//online write/modify
	public function check_param_online_download()
	{
		$this->validation->set_rules('couponType', '쿠폰종류','trim|required|xss_clean');

		if( $_POST['couponType'] == 'download' || $_POST['couponType'] == 'birthday' || $_POST['couponType'] == 'anniversary' || $_POST['couponType'] == 'shipping'  || strstr($_POST['couponType'],'_shipping') || $_POST['couponType'] == 'memberGroup' ) {
			if($_POST['downloadLimit_'.$_POST['couponType']]=='limit') $this->validation->set_rules('downloadLimitEa_'.$_POST['couponType'], '전체수량제한','trim|required|numeric|xss_clean');
		}

		if( $_POST['couponType'] == 'memberGroup') {
			$this->validation->set_rules('memberGroups_memberGroup[]', '등급 제한','trim|required|max_length[7]|xss_clean');
		}elseif( $_POST['couponType'] == 'memberGroup_shipping' ) {
			$this->validation->set_rules('memberGroups_memberGroup_shipping[]', '등급 제한','trim|required|max_length[7]|xss_clean');
		}elseif( $_POST['couponType'] == 'membermonths' ) {
			$this->validation->set_rules('memberGroups_membermonths[]', '등급 제한','trim|required|max_length[7]|xss_clean');
		}elseif( $_POST['couponType'] == 'membermonths_shipping' ) {
			$this->validation->set_rules('memberGroups_membermonths_shipping[]', '등급 제한','trim|required|max_length[7]|xss_clean');
		}else{
			$this->validation->set_rules('memberGroups_'.$_POST['couponType'].'[]', '등급 제한','trim|max_length[7]|xss_clean');
		}
		$this->validation->set_rules('downloadDate_'.$_POST['couponType'].'[]', '기간 제한','trim|max_length[10]|xss_clean');


		$this->validation->set_rules('couponName', '쿠폰명','trim|required|xss_clean');
		$this->validation->set_rules('couponDesc', '쿠폰 설명','trim|xss_clean');
		$this->validation->set_rules('saleType', '쿠폰 혜택 종류','trim|required|max_length[7]|xss_clean');

		if( $_POST['couponType'] == 'point' ) {
			$_POST['coupon_point']			= ($_POST['coupon_point']>0)?$_POST['coupon_point']:'';
			$this->validation->set_rules('coupon_point', '전환 포인트','trim|required|numeric|xss_clean');
		}

		if( $_POST['couponType'] == 'memberlogin' || $_POST['couponType'] == 'memberlogin_shipping' ) {
			//$_POST['memberlogin_terms']			= ($_POST['memberlogin_terms']>0)?$_POST['memberlogin_terms']:'';
			//$this->validation->set_rules('memberlogin_terms', '최근 미구매한 개월','trim|required|numeric|xss_clean');
		}

		if( $_POST['couponType'] == 'order' ) {
			$_POST['order_terms']			= ($_POST['order_terms']>0)?$_POST['order_terms']:'';
			$this->validation->set_rules('order_terms', '신규가입 후 경과일','trim|required|numeric|xss_clean');
		}

		$_POST['percentGoodsSale']	= ($_POST['percentGoodsSale']>0)?$_POST['percentGoodsSale']:'';
		$_POST['maxPercentGoodsSale']	= ($_POST['maxPercentGoodsSale']>0)?$_POST['maxPercentGoodsSale']:'';
		$_POST['wonGoodsSale']			= ($_POST['wonGoodsSale']>0)?$_POST['wonGoodsSale']:'';
		if( $_POST['couponType'] == 'shipping'  || strstr($_POST['couponType'],'_shipping') ) {
		}else{
			if($_POST['coopon_usetype']=='online'){
				if($_POST['saleType']=='percent'){
					$this->validation->set_rules('percentGoodsSale', '할인율','trim|required|numeric|max_length[3]|xss_clean');
					$this->validation->set_rules('maxPercentGoodsSale', '최대 할인 금액','trim|required|numeric|xss_clean');
				}
				if($_POST['saleType']=='won'){
					$this->validation->set_rules('wonGoodsSale', '할인 금액','trim|required|numeric|xss_clean');
				}
			}
		}
		if(isset($_POST['duplicationUse'])){
			$this->validation->set_rules('duplicationUse', '다중 사용','trim|numeric|xss_clean');
		}
		
		
		//월1회다운가능쿠폰은 발급 당월 말일까지
		$couponmonthsar = array('memberlogin','memberlogin_shipping','membermonths','membermonths_shipping','order');
		if( in_array($_POST['couponType'],$couponmonthsar) ) {
			$_POST['issuePriodType'] = 'months';
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

		$this->validation->set_rules('couponImg', '쿠폰 PC용 이미지','trim|numeric|max_length[3]|xss_clean');
		$this->validation->set_rules('couponmobileImg', '쿠폰 Mobile용 이미지','trim|numeric|max_length[3]|xss_clean'); 

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		// 쿠폰혜택 체크
		if($_POST['coopon_usetype']=='offline'){
			if($_POST['benefit_txt']){
				$paramCoupon['benefit'] = $_POST['benefit_txt'];
			} else {
				$callback = "parent.document.onlineRegist.benefit_txt.focus();";
				openDialogAlert("쿠폰에 명시될 혜택은 필수 입니다.<br/>혜택을 기재해 주세요.",450,140,'parent',$callback);
				exit;
			}

			if($_POST['limit_txt'])	$paramCoupon['limit_txt'] = $_POST['limit_txt'];
		}

		// 기간이 있는 타입 체크
		if($_POST['couponType'] == 'download' || $_POST['couponType'] == 'mobile' || $_POST['couponType'] == 'shipping'){
			// 기간을 기재 했을경우
			if($_POST['downloadDate_'.$_POST['couponType']][0] && $_POST['downloadDate_'.$_POST['couponType']][3]){

				// 제한기간 검사
				$pattan = "/^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/";
				if(!preg_match($pattan,$_POST['downloadDate_'.$_POST['couponType']][0])){
					$callback = "parent.document.onlineRegist.downloadDate_download[0].focus();";
					echo $_POST['downloadDate_'.$_POST['couponType']][0];
					openDialogAlert("제한기간 시작일이 올바르지 않습니다.<br/>날짜를 조정해 주세요.",450,150,'parent',$callback);
					exit;
				}
				if(!preg_match($pattan,$_POST['downloadDate_'.$_POST['couponType']][3])){
					$callback = "parent.document.onlineRegist.downloadDate_download[0].focus();";
					openDialogAlert("제한기간 종료일이 올바르지 않습니다.<br/>날짜를 조정해 주세요.",450,150,'parent',$callback);
					exit;
				}

				// 제한기간 체크
				if(!$_POST['downloadDate_'.$_POST['couponType']][1]) $_POST['downloadDate_'.$_POST['couponType']][1] = "00";
				if(!$_POST['downloadDate_'.$_POST['couponType']][2]) $_POST['downloadDate_'.$_POST['couponType']][2] = "00";
				if(!$_POST['downloadDate_'.$_POST['couponType']][4]) $_POST['downloadDate_'.$_POST['couponType']][4] = "23";
				if(!$_POST['downloadDate_'.$_POST['couponType']][5]) $_POST['downloadDate_'.$_POST['couponType']][5] = "59";
				$downloadDate_start	= $_POST['downloadDate_'.$_POST['couponType']][0] . " " . $_POST['downloadDate_'.$_POST['couponType']][1] . ":" . $_POST['downloadDate_'.$_POST['couponType']][2];
				$downloadDate_end	= $_POST['downloadDate_'.$_POST['couponType']][3] . " " . $_POST['downloadDate_'.$_POST['couponType']][4] . ":" . $_POST['downloadDate_'.$_POST['couponType']][5];

				if(strtotime($downloadDate_start) > strtotime($downloadDate_end)){
					$callback = "parent.document.onlineRegist.downloadDate_download[0].focus();";
					openDialogAlert("제한 기간 종료일이 시작일보다 이후에 있어야 합니다.<br/>날짜를 조정해 주세요.",450,150,'parent',$callback);
					exit;
				}

				// 요일 체크
				if($_POST['downloadWeek_'.$_POST['couponType']]) {
					$paramCoupon['download_week'] = implode("", $_POST['downloadWeek_'.$_POST['couponType']]);
				}else{
					$paramCoupon['download_week'] = "1234567"; 
				}

				// 다운로드 가능시간 체크
				if($_POST['downloadTime_'.$_POST['couponType']][0] && $_POST['downloadTime_'.$_POST['couponType']][1]
					&& $_POST['downloadTime_'.$_POST['couponType']][2] && $_POST['downloadTime_'.$_POST['couponType']][3])
				{
					$paramCoupon['download_starttime'] = $_POST['downloadTime_'.$_POST['couponType']][0].":".$_POST['downloadTime_'.$_POST['couponType']][1];
					$paramCoupon['download_endtime'] = $_POST['downloadTime_'.$_POST['couponType']][2].":".$_POST['downloadTime_'.$_POST['couponType']][3];
				}else{
					$paramCoupon['download_starttime'] = "00:00";
					$paramCoupon['download_endtime'] = "23:59"; 
				}
			}else{
				// 다운로드 가능 시작시간 기본값 설정
				if($_POST['downloadTime_'.$_POST['couponType']][0] && !$_POST['downloadTime_'.$_POST['couponType']][1]){
					$paramCoupon['download_starttime'] = $_POST['downloadTime_'.$_POST['couponType']][0].":00";
				}else if(!$_POST['downloadTime_'.$_POST['couponType']][0] && $_POST['downloadTime_'.$_POST['couponType']][1]){
					$paramCoupon['download_starttime'] = "00:".$_POST['downloadTime_'.$_POST['couponType']][1];
				}else if($_POST['downloadTime_'.$_POST['couponType']][0] && $_POST['downloadTime_'.$_POST['couponType']][1]){
					$paramCoupon['download_starttime'] = $_POST['downloadTime_'.$_POST['couponType']][0].":".$_POST['downloadTime_'.$_POST['couponType']][1];
				}else{
					$paramCoupon['download_starttime'] = "00:00";
				}

				// 다운로드 가능 끝시간 기본값 설정
				if($_POST['downloadTime_'.$_POST['couponType']][2] && !$_POST['downloadTime_'.$_POST['couponType']][3]){
					$paramCoupon['download_endtime'] = $_POST['downloadTime_'.$_POST['couponType']][2].":59";
				}else if(!$_POST['downloadTime_'.$_POST['couponType']][2] && $_POST['downloadTime_'.$_POST['couponType']][3]){
					$paramCoupon['download_endtime'] = "23:".$_POST['downloadTime_'.$_POST['couponType']][1];
				}else if($_POST['downloadTime_'.$_POST['couponType']][2] && $_POST['downloadTime_'.$_POST['couponType']][3]){
					$paramCoupon['download_endtime'] = $_POST['downloadTime_'.$_POST['couponType']][2].":".$_POST['downloadTime_'.$_POST['couponType']][3];
				}else{
					$paramCoupon['download_endtime'] = "23:59";
				}

				// 요일 체크
				if($_POST['downloadWeek_'.$_POST['couponType']]) {
					$paramCoupon['download_week'] = implode("", $_POST['downloadWeek_'.$_POST['couponType']]);
				}
			}

			// 유효기간 체크
			if($_POST['issuePriodType'] == 'date'&& $_POST['downloadDate_'.$_POST['couponType']][3]){

				if($_POST['issueDate'][1] < $_POST['downloadDate_'.$_POST['couponType']][3]){
				$callback = "parent.document.onlineRegist.downloadDate_download[0].focus();";
				openDialogAlert("유효기간 종료일이 다운로드 가능기간보다 이후에 있어야 합니다.<br/>날짜를 조정해 주세요.",450,150,'parent',$callback);
				exit;
				}
			}
		}

		if($_POST['issuePriodType'] == 'date'){
			if($_POST['issueDate'][1] < $_POST['issueDate'][0]){
			$callback = "parent.document.onlineRegist.issueDate[0].focus();";
			openDialogAlert("유효기간 종료일이 시작일보다 이후에 있어야 합니다.<br/>날짜를 조정해 주세요.",450,140,'parent',$callback);
			exit;
			}
		}

		$paramCoupon['type'] 							= $_POST['couponType'];
		$paramCoupon['use_type']						= $_POST['coopon_usetype'];
		$paramCoupon['issue_type'] 					= if_empty($_POST, 'issue_type', 'all');
		$paramCoupon['issue_stop'] 					= if_empty($_POST, $paramCoupon['type'].'_issue_stop', '0');
		$paramCoupon['coupon_point'] 				= if_empty($_POST, 'coupon_point', '0');
		$_POST['duplicationUse'] 						= if_empty($_POST, 'duplicationUse', '0'); 

		$paramCoupon['sale_agent'] 					= if_empty($_POST, 'sale_agent', 'a');
		$paramCoupon['sale_payment'] 				= if_empty($_POST, 'sale_payment', 'a');
		$paramCoupon['sale_referer'] 				= if_empty($_POST, 'sale_referer', 'a');
		$paramCoupon['sale_referer_type'] 		= if_empty($_POST, 'sale_referer_type', 'a');
		$paramCoupon['sale_referer_item'] 		= $_POST['sale_referer_item'];

		if( $paramCoupon['type'] == 'memberlogin' ) {
			$paramCoupon['memberlogin_terms'] 	= if_empty($_POST, 'memberlogin_terms', '1'); 
		}elseif ( $paramCoupon['type'] == 'memberlogin_shipping' ) {
			$paramCoupon['memberlogin_terms'] 	= if_empty($_POST, 'memberlogin_shipping_terms', '1'); 
		}else{
			$paramCoupon['memberlogin_terms'] 	= 0; 
		}
		$paramCoupon['order_terms'] 				= if_empty($_POST, 'order_terms', '0'); 

		

		$paramCoupon['download_limit'] 			= ($_POST['downloadLimit_'.$paramCoupon['type']])?$_POST['downloadLimit_'.$paramCoupon['type']]:'unlimit';
		$paramCoupon['download_limit_ea'] 		= ($_POST['downloadLimitEa_'.$paramCoupon['type']])?$_POST['downloadLimitEa_'.$paramCoupon['type']]:0;

		if(isset($_POST['downloadDate_'.$_POST['couponType']]) && $_POST['downloadDate_'.$_POST['couponType']][0]){
			// 기간제한 시간체크
			if(!$_POST['downloadDate_'.$_POST['couponType']][1])	$_POST['downloadDate_'.$_POST['couponType']][1] = '00';
			if(!$_POST['downloadDate_'.$_POST['couponType']][2])	$_POST['downloadDate_'.$_POST['couponType']][2] = '00';
			$paramCoupon['download_startdate'] 	= $_POST['downloadDate_'.$_POST['couponType']][0]." " .$_POST['downloadDate_'.$_POST['couponType']][1].":".$_POST['downloadDate_'.$_POST['couponType']][2];
		}

		if(isset($_POST['downloadDate_'.$_POST['couponType']]) && $_POST['downloadDate_'.$_POST['couponType']][3]){
			// 기간체한 시간체크
			if(!$_POST['downloadDate_'.$_POST['couponType']][4])	$_POST['downloadDate_'.$_POST['couponType']][4] = '23';
			if(!$_POST['downloadDate_'.$_POST['couponType']][5])	$_POST['downloadDate_'.$_POST['couponType']][5] = '59';
			$paramCoupon['download_enddate'] 	= $_POST['downloadDate_'.$_POST['couponType']][3]." ".$_POST['downloadDate_'.$_POST['couponType']][4].":".$_POST['downloadDate_'.$_POST['couponType']][5];
		}


		//생일쿠폰
		if(isset($_POST['beforeBirthday'])) $paramCoupon['before_birthday'] = $_POST['beforeBirthday'];
		if(isset($_POST['afterBirthday'])) $paramCoupon['after_birthday'] = $_POST['afterBirthday'];

		//기념일쿠폰
		if(isset($_POST['beforeanniversary'])) $paramCoupon['before_anniversary'] = $_POST['beforeanniversary'];
		if(isset($_POST['afteranniversary'])) $paramCoupon['after_anniversary'] = $_POST['afteranniversary'];

		if( $paramCoupon['type'] == 'memberGroup' ) {
			if(isset($_POST['afterUpgrade'])) $paramCoupon['after_upgrade'] = $_POST['afterUpgrade'];
		}elseif ( $paramCoupon['type'] == 'memberGroup_shipping' ) {
			if(isset($_POST['shipping_afterUpgrade'])) $paramCoupon['after_upgrade'] = $_POST['shipping_afterUpgrade'];
		}


		$paramCoupon['coupon_name'] 			= $_POST['couponName'];
		$paramCoupon['coupon_desc'] 			= $_POST['couponDesc'];
		$paramCoupon['sale_type'] 				= $_POST['saleType'];

		$paramCoupon['coupon_same_time']		= if_empty($_POST, 'couponsametime', 'Y');//동시사용여부: Y

		if($paramCoupon['sale_type']=='percent'){
			$paramCoupon['percent_goods_sale'] 			= $_POST['percentGoodsSale'];
			$paramCoupon['max_percent_goods_sale'] 	= $_POST['maxPercentGoodsSale'];
		}elseif($paramCoupon['sale_type']=='won'){
			$paramCoupon['won_goods_sale'] 			= $_POST['wonGoodsSale'];
		}


		$paramCoupon['shipping_type'] 					= $_POST['shippingType'];
		$paramCoupon['won_shipping_sale'] 			= $_POST['wonShippingSale'];
		$paramCoupon['max_percent_shipping_sale'] 	= $_POST['maxPercentShippingSale'];

		$paramCoupon['duplication_use'] 	= ($_POST['duplicationUse'])?$_POST['duplicationUse']:0;
		$paramCoupon['issue_priod_type'] 		= $_POST['issuePriodType'];

		if($paramCoupon['issue_priod_type']=='date') {
			if(isset($_POST['issueDate']) && $_POST['issueDate'][0]){
				$paramCoupon['issue_startdate'] 	= $_POST['issueDate'][0];
			}
			if(isset($_POST['issueDate']) && $_POST['issueDate'][1]){
				$paramCoupon['issue_enddate'] 	= $_POST['issueDate'][1];
			}

		}elseif($paramCoupon['issue_priod_type']=='day') {

			if(isset($_POST['afterIssueDay']) && $_POST['afterIssueDay']){
				$paramCoupon['after_issue_day']		= if_empty($_POST, 'afterIssueDay', '0');
			}

		}elseif($paramCoupon['issue_priod_type']=='months') {
			$paramCoupon['after_issue_day']		= '31';//발급일로부터 말일 28~31사이
		}

		if(isset($_POST['limitGoodsPrice'])){
			$paramCoupon['limit_goods_price'] = $_POST['limitGoodsPrice'];
		}

		//$paramCoupon['coupon_popup_use']		= if_empty($_POST, 'coupon_popup_use', 'N');

		$paramCoupon['coupon_img'] 				= $_POST['couponImg'];
		if(!empty($_POST['couponimage4']) && @is_file(ROOTPATH."data/tmp/".$_POST['couponimage4'])) {//rename
			@rename(ROOTPATH."data/tmp/".$_POST['couponimage4'], $this->copuonupload_dir.$_POST['couponimage4']);
			@chmod($this->copuonupload_dir.$_POST['couponimage4'],0707);
			$paramCoupon['coupon_image4'] 			= $_POST['couponimage4'];
		}

		$paramCoupon['coupon_mobile_img'] 				= $_POST['couponmobileImg'];
		if(!empty($_POST['couponmobileimage4']) && @is_file(ROOTPATH."data/tmp/".$_POST['couponmobileimage4'])) {//rename
			@rename(ROOTPATH."data/tmp/".$_POST['couponmobileimage4'], $this->copuonupload_dir.$_POST['couponmobileimage4']);
			@chmod($this->copuonupload_dir.$_POST['couponmobileimage4'],0707);
			$paramCoupon['coupon_mobile_image4'] 			= $_POST['couponmobileimage4'];
		}

		if( $_POST['couponSeq'] ) {
			$paramCoupon['update_date'] = date('Y-m-d H:i:s',time());
		}else{
			$paramCoupon['regist_date']	= $paramCoupon['update_date'] = date('Y-m-d H:i:s',time());
		} 
		return $paramCoupon;
	}

	//offline coupon write/modfiy
	public function check_param_offline_download()
	{
		$this->validation->set_rules('couponType', '쿠폰종류','trim|required|xss_clean');
		$this->validation->set_rules('offline_type', '인증번호발급방식','trim|required|max_length[11]|xss_clean');

		$this->validation->set_rules('downloadLimitEa_'.$_POST['couponType'], '인증번호 인증횟수 제한','trim|required|numeric|xss_clean');
		$this->validation->set_rules('downloadDate_'.$_POST['couponType'].'[]', '인증번호 인증기간 제한','trim|max_length[10]|xss_clean');

		$this->validation->set_rules('couponName', '쿠폰명','trim|required|xss_clean');
		$this->validation->set_rules('couponDesc', '쿠폰 설명','trim|xss_clean');
		if(!$_POST['couponSeq']) {//등록시에만 적용
			if( $_POST['offline_type'] == 'random') {//자동생성 > 인증번호 갯수
				$this->validation->set_rules('offline_random_num', '자동생성 시 갯수','trim|numeric|min_length[1]|max_length[5]|required|xss_clean');

				if($_POST['offline_random_num'] < 1 ){
					$callback = "if(parent.document.getElementsByName('offline_random_num')[0]) parent.document.getElementsByName('offline_random_num')[0].focus();";
					openDialogAlert('자동생성 시 갯수는 1이상부터 가능합니다..',400,140,'parent',$callback);
					exit;
				}
				if($_POST['offline_random_num'] > 10000 ){
					$callback = "if(parent.document.getElementsByName('offline_random_num')[0]) parent.document.getElementsByName('offline_random_num')[0].focus();";
					openDialogAlert('자동생성 시 갯수는 10000개 이하까지 가능합니다.',400,140,'parent',$callback);
					exit;
				}
			}elseif( $_POST['offline_type'] == 'one') {//자동생성 > 동일번호
				if( $_POST['offlineLimit_one'] == 'limit') {//자동생성 > 동일번호 > 선착순
					$this->validation->set_rules('offlineLimitEa_one', '동일 인증번호 선착순 갯수','trim|numeric|min_length[1]|required|xss_clean');

					if($_POST['offlineLimitEa_one'] < 1 ){
						$callback = "if(parent.document.getElementsByName('offlineLimitEa_one')[0]) parent.document.getElementsByName('offlineLimitEa_one')[0].focus();";
						openDialogAlert('동일 인증번호 선착순 갯수는 1번이상부터 가능합니다..',400,140,'parent',$callback);
						exit;
					}
				}
			}elseif( $_POST['offline_type'] == 'input') {//수동생성 > 동일번호
				$this->validation->set_rules('offline_input_num', '동일 인증번호','trim|required|xss_clean');
				if( $_POST['offlineLimit_input'] == 'limit') {//자동생성 > 동일번호 > 선착순
					$this->validation->set_rules('offlineLimitEa_input', '동일 인증번호 > 선착순 갯수','trim|numeric|required|xss_clean');
					if($_POST['offlineLimitEa_input'] < 1 ){
						$callback = "if(parent.document.getElementsByName('offlineLimitEa_input')[0]) parent.document.getElementsByName('offlineLimitEa_input')[0].focus();";
						openDialogAlert('동일 인증번호 선착순 갯수는 1번이상부터 가능합니다..',400,140,'parent',$callback);
						exit;
					}
				}
			}elseif( $_POST['offline_type'] == 'file') {//수동생성 > 파일
				$this->validation->set_rules('offline_file', '수동생성 > 엑셀파일','trim|required|xss_clean');
			}
		}

		if( $_POST['couponType'] == 'offline_emoney') {//적립금 지급쿠폰

			$this->validation->set_rules('offline_emoney', '사용제한 금액','trim|numeric|xss_clean');

		}else{//offline_coupon

			$this->validation->set_rules('saleType', '쿠폰 혜택 종류','trim|required|max_length[7]|xss_clean');
			if($_POST['saleType']=='percent'){
				$this->validation->set_rules('percentGoodsSale', '할인율','trim|required|numeric|max_length[3]|xss_clean');
				$this->validation->set_rules('maxPercentGoodsSale', '최대 할인 금액','trim|required|numeric|xss_clean');
			}
			if($_POST['saleType']=='won'){
				$this->validation->set_rules('wonGoodsSale', '할인 금액','trim|required|numeric|xss_clean');
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

			$this->validation->set_rules('couponImg', '쿠폰 PC용  이미지','trim|numeric|max_length[3]|xss_clean');
			$this->validation->set_rules('couponmobileImg', '쿠폰 Mobile용  이미지','trim|numeric|max_length[3]|xss_clean'); 
		}


		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}


		$paramCoupon['type'] 							= $_POST['couponType'];
		$paramCoupon['offline_type'] 					= $_POST['offline_type'];

		if( $paramCoupon['offline_type'] == 'random') {//자동생성 > 인증번호 갯수
			$paramCoupon['offline_random_num'] 	= if_empty($_POST, 'offline_random_num', '0');
		}elseif( $paramCoupon['offline_type'] == 'one') {//자동생성 > 동일번호
			$paramCoupon['offline_limit'] 					= $_POST['offlineLimit_one'];
			if( $paramCoupon['offline_limit'] == 'limit') {//자동생성 > 동일번호 > 선착순
				$paramCoupon['offline_limit_ea'] 		= if_empty($_POST, 'offlineLimitEa_one', '0');
			}
		}elseif( $paramCoupon['offline_type'] == 'input') {//수동생성 > 동일번호
			$paramCoupon['offline_input_serialnumber'] 		= if_empty($_POST, 'offline_input_num', '');
			$paramCoupon['offline_limit'] 								= $_POST['offlineLimit_input'];
			if(!$_POST['couponSeq']) {//등록시에만 적용
				// offline쿠폰 인증번호 체크
				$sc['whereis'] = ' and offline_serialnumber = "'.$paramCoupon['offline_input_serialnumber'].'" ';
				$offlienresult = $this->get_offlinecoupon_total_count($sc);
				if(!$offlienresult){
					$offlienresult = $this->get_offlinecoupon_input_total_count($sc);
				}
				if($offlienresult){
					$err = '이미 등록된 인증번호입니다.';
					$callback = "if(parent.document.getElementsByName('offline_input_num')[0]) parent.document.getElementsByName('offline_input_num')[0].focus();";
					openDialogAlert($err,400,140,'parent',$callback);
					exit;
				}
			}

			if( $paramCoupon['offline_limit'] == 'limit') {//자동생성 > 동일번호 > 선착순
				$paramCoupon['offline_limit_ea'] 		= if_empty($_POST, 'offlineLimitEa_input', '0');
			}
		}elseif( $paramCoupon['offline_type'] == 'file') {//수동생성 > 파일
			//'offline_file', '수동생성 > 엑셀파일' upload
		}

		$paramCoupon['issue_type'] 					= if_empty($_POST, 'issue_type', 'all');
		$paramCoupon['issue_stop'] 					= if_empty($_POST, $paramCoupon['type'].'_issue_stop', '0'); 
		$paramCoupon['offline_emoney'] 			= if_empty($_POST, 'offline_emoney', '0');
		$_POST['duplicationUse']       = if_empty($_POST, 'duplicationUse', '0');

		$paramCoupon['sale_agent']					= if_empty($_POST, 'sale_agent', 'a');
		$paramCoupon['sale_payment']				= if_empty($_POST, 'sale_payment', 'a');
		$paramCoupon['sale_referer']					= if_empty($_POST, 'sale_referer', 'a');
		$paramCoupon['sale_referer_type']		= if_empty($_POST, 'sale_referer_type', 'a');
		$paramCoupon['sale_referer_item']		= $_POST['sale_referer_item'];
		
		if( $paramCoupon['type'] == 'memberlogin' ) {
			$paramCoupon['memberlogin_terms'] 	= if_empty($_POST, 'memberlogin_terms', '0'); 
		}elseif ( $paramCoupon['type'] == 'memberlogin_shipping' ) {
			$paramCoupon['memberlogin_terms'] 	= if_empty($_POST, 'memberlogin_shipping_terms', '0'); 
		}else{
			$paramCoupon['memberlogin_terms'] 	= 0; 
		}
		$paramCoupon['order_terms'] 				= if_empty($_POST, 'order_terms', '0'); 

		$paramCoupon['offline_reserve_select'] = $_POST['offline_reserve_select'];
		$paramCoupon['offline_reserve_year'] 	= $_POST['offline_reserve_year'];
		$paramCoupon['offline_reserve_direct'] 	= $_POST['offline_reserve_direct'];

		$paramCoupon['download_limit'] 			= ($_POST['downloadLimit_'.$paramCoupon['type']])?$_POST['downloadLimit_'.$paramCoupon['type']]:'unlimit';
		$paramCoupon['download_limit_ea'] 		= ($_POST['downloadLimitEa_'.$paramCoupon['type']])?$_POST['downloadLimitEa_'.$paramCoupon['type']]:0;

		if(isset($_POST['downloadDate_'.$_POST['couponType']]) && $_POST['downloadDate_'.$_POST['couponType']][0]){
			$paramCoupon['download_startdate'] 	= $_POST['downloadDate_'.$_POST['couponType']][0];
		}

		if(isset($_POST['downloadDate_'.$_POST['couponType']]) && $_POST['downloadDate_'.$_POST['couponType']][1]){
			$paramCoupon['download_enddate'] 	= $_POST['downloadDate_'.$_POST['couponType']][1];
		}

		$paramCoupon['coupon_name'] 			= $_POST['couponName'];
		$paramCoupon['coupon_desc'] 			= $_POST['couponDesc'];
		$paramCoupon['sale_type'] 				= $_POST['saleType'];

		$paramCoupon['coupon_same_time']		= if_empty($_POST, 'couponsametime', 'Y');//동시사용여부: Y

		if($paramCoupon['sale_type']=='percent'){
			$paramCoupon['percent_goods_sale'] 			= $_POST['percentGoodsSale'];
			$paramCoupon['max_percent_goods_sale'] 	= $_POST['maxPercentGoodsSale'];
		}elseif($paramCoupon['sale_type']=='won'){
			$paramCoupon['won_goods_sale'] 			= $_POST['wonGoodsSale'];
		}

		$paramCoupon['shipping_type'] 					= $_POST['shippingType'];
		$paramCoupon['won_shipping_sale'] 			= $_POST['wonShippingSale'];
		$paramCoupon['max_percent_shipping_sale'] 	= $_POST['maxPercentShippingSale'];

		$paramCoupon['duplication_use']  = ($_POST['duplicationUse'])?$_POST['duplicationUse']:0;

		$paramCoupon['issue_priod_type'] 		= $_POST['issuePriodType'];

		if($paramCoupon['issue_priod_type']=='date') {
			if(isset($_POST['issueDate']) && $_POST['issueDate'][0]){
				$paramCoupon['issue_startdate'] 	= $_POST['issueDate'][0];
			}
			if(isset($_POST['issueDate']) && $_POST['issueDate'][1]){
				$paramCoupon['issue_enddate'] 	= $_POST['issueDate'][1];
			}

		}elseif($paramCoupon['issue_priod_type']=='day') {
			if(isset($_POST['afterIssueDay']) && $_POST['afterIssueDay']){
				$paramCoupon['after_issue_day']		= if_empty($_POST, 'afterIssueDay', '0');
			}
		}

		if(isset($_POST['limitGoodsPrice']) && $_POST['limitGoodsPrice']){
			$paramCoupon['limit_goods_price']		= $_POST['limitGoodsPrice'];
		}

		$paramCoupon['coupon_img'] 				= '';
		$paramCoupon['coupon_mobile_img'] 		= '';

		if( $_POST['couponSeq'] ) {
			$paramCoupon['update_date'] = date('Y-m-d H:i:s',time());
		}else{
			$paramCoupon['regist_date']	= $paramCoupon['update_date'] = date('Y-m-d H:i:s',time());
		}
		return $paramCoupon;
	}

	/* 사용자의 상품쿠폰 >다운시 개별체크용  */
	public function get_able_download($today,$memberSeq,$goodsSeq,$category,$couponSeq)
	{ 
		if( defined('__ADMIN__') != true ) {
			if(!$memberSeq) return;
			$membersql = "AND d.member_seq='".$memberSeq."' ";
		} 
		$issue_subquery = $except_subquery = "";
		if(count($category)>0){
			$issue_subquery = "+(select count(*) FROM fm_coupon_issuecategory WHERE coupon_seq=c.coupon_seq AND `type`='issue' AND category_code IN ('".implode("','",$category)."'))";
			$except_subquery = "+(select count(*) FROM fm_coupon_issuecategory WHERE coupon_seq=c.coupon_seq AND `type`='except' AND category_code IN ('".implode("','",$category)."'))";
		}

		if($this->_is_mobile_agent) {
			$coupontypear = array('download','shipping','mobile');
			$mobilequery = " ";
		}else{
			$coupontypear = array('download','shipping');
			$mobilequery = " AND c.sale_agent != 'm' ";//모바일
		} 
		$coupontype = " AND c.type IN ('".implode("','",$coupontypear)."') ".$mobilequery;

		$query = "SELECT coupon.*
					FROM (
						SELECT c.*,
							SUM(if(d.use_status='used',1,0)) used_cnt,
							SUM(if(d.use_status='unused',1,0)) unused_cnt,
							(select regist_date from fm_download where coupon_seq = c.coupon_seq AND member_seq='".$memberSeq."' order by regist_date desc limit 1 ) download_regist_date,
							SUM(if(d.use_status='unused' AND d.issue_enddate < '".$today."',1,0)) cancel_cnt,
							(
								SELECT count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM fm_coupon_issuecategory
								WHERE coupon_seq=c.coupon_seq AND `type`='issue'
							) as all_issue_cnt,
							(
								SELECT count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND goods_seq='".$goodsSeq."' AND `type`='issue'
							)".$issue_subquery." as issue_cnt,
							(
								select count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND goods_seq='".$goodsSeq."' AND `type`='except'
							)".$except_subquery." as except_cnt
						FROM fm_coupon c
							LEFT JOIN fm_download d ON c.coupon_seq = d.coupon_seq ".$membersql."
						WHERE
							c.coupon_seq='".$couponSeq."'
							".$coupontype."
							AND
							( 
								(
									(c.download_startdate is null  AND c.download_enddate is null )
									OR
									(c.download_startdate <='".date('Y-m-d H:i:s',time())."' AND c.download_enddate >='".date('Y-m-d H:i:s',time())."')
								)
								AND (
									(c.download_starttime is null  AND c.download_endtime is null )
									OR
									(c.download_starttime <='".date('H:i',time())."' AND c.download_endtime >= '".date('H:i',time())."')
								) 
								AND INSTR(c.download_week, '".date('N')."') > 0
							)
						GROUP BY c.coupon_seq
					) coupon
				WHERE except_cnt=0
					AND (all_issue_cnt=0 OR issue_cnt>0)
					AND
					(
						( used_cnt > 0 AND duplication_use = 1 )
						OR  used_cnt = 0
					)";
		$query = $this->db->query($query);
		list($result) = $query->result_array();
		return $result;
	}

	/* 상품상세의 다운로드 가능한 쿠폰목록  */
	public function get_able_download_list($today,$memberSeq,$goodsSeq,$category,$price,$use_type=null)
	{
		if( $memberSeq ) {
			$this->load->model('membermodel');
			$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보
			$mbquery = ",
			(
				SELECT count(*)
				FROM fm_coupon_group
				WHERE coupon_seq=c.coupon_seq AND `group_seq`='".$this->mdata['group_seq']."'
			) as mbgroup_issue_cnt,
			(
				SELECT count(*)
				FROM fm_coupon_group
				WHERE coupon_seq=c.coupon_seq
			) as allgroup_issue_cnt";

			$mbwhere = " AND ((allgroup_issue_cnt =0 AND mbgroup_issue_cnt=0) OR (allgroup_issue_cnt>0 AND mbgroup_issue_cnt>0))";
		}

		$issue_subquery = $except_subquery = "";
		if(count($category)>0){
			$issue_subquery = "+(
												select count(*) FROM
												fm_coupon_issuecategory
												WHERE coupon_seq=c.coupon_seq AND `type`='issue' AND category_code IN ('".implode("','",$category)."')
											)";
			$except_subquery = "+(
													select count(*) FROM
													fm_coupon_issuecategory
													WHERE coupon_seq=c.coupon_seq AND `type`='except' AND category_code IN ('".implode("','",$category)."')
												)";
		}

		/**if($this->_is_mobile_agent) {
			$coupontype = "c.type IN ('download','mobile')";
		}else{
			$coupontype = "c.type IN ('download')";
		}
		**/
		if($this->_is_mobile_agent) {
			$coupontypear = array('download','mobile');
			$mobilequery = " AND c.use_type != 'offline' ";//모바일
		}else{
			$coupontypear = array('download');
			$mobilequery = " AND c.sale_agent != 'm' AND c.use_type != 'offline'  ";//모바일
		} 
		$coupontype = " c.type IN ('".implode("','",$coupontypear)."') ".$mobilequery;

		if($use_type){
			$coupontype .= "AND c.use_type = '".$use_type."' ";
		}
		$coupontype .= "AND c.issue_stop = '0' ";

		$query = "SELECT coupon.*
					FROM (
						SELECT c.*,
							SUM(if(d.use_status='used',1,0)) used_cnt,
							SUM(if(d.use_status='unused',1,0)) unused_cnt,
							(select regist_date from fm_download where coupon_seq = c.coupon_seq AND member_seq='".$memberSeq."' order by regist_date desc limit 1 ) download_regist_date,
							SUM(if(d.use_status='unused' AND d.issue_enddate < '".$today."',1,0)) cancel_cnt,
							(
								SELECT count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM fm_coupon_issuecategory
								WHERE coupon_seq=c.coupon_seq AND `type`='issue'
							) as all_issue_cnt,
							(
								SELECT count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND goods_seq='".$goodsSeq."' AND `type`='issue'
							)".$issue_subquery." as issue_cnt,
							(
								select count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND goods_seq='".$goodsSeq."' AND `type`='except'
							)".$except_subquery." as except_cnt
							{$mbquery}
						FROM fm_coupon c
							LEFT JOIN fm_download d ON c.coupon_seq = d.coupon_seq AND d.member_seq='".$memberSeq."'
						WHERE
							(
								".$coupontype."
								AND (
									(c.download_startdate is null  AND c.download_enddate is null )
									OR
									(c.download_startdate <='".date('Y-m-d H:i:s',time())."' AND c.download_enddate >='".date('Y-m-d H:i:s',time())."')
								)
								AND (
									(c.download_starttime is null  AND c.download_endtime is null )
									OR
									(c.download_starttime <='".date('H:i',time())."' AND c.download_endtime >= '".date('H:i',time())."')
								)
								AND INSTR(c.download_week, '".date('N')."') > 0
							)
						GROUP BY c.coupon_seq
					) coupon
				WHERE except_cnt=0
					AND (all_issue_cnt=0 OR issue_cnt>0)
					AND
					(
						( used_cnt > 0 AND duplication_use = 1 )
						OR  used_cnt = 0
					)
					{$mbwhere}
					AND (issue_type = 'all' OR (issue_type = 'issue' AND all_issue_cnt > 0) OR (issue_type = 'except' AND all_issue_cnt = 0) )";
		$query .= ($use_type)?" ORDER BY use_type DESC":" ORDER BY coupon_seq ASC";
		$query = $this->db->query($query);
		foreach($query->result_array() as $data) {
			$data['goods_sale'] = 0;
			if( $data['type'] != 'shipping' ){
				if( $data['sale_type'] == 'percent' && $data['percent_goods_sale'] && $price ){
					$data['goods_sale'] = $data['percent_goods_sale'] * $price / 100;
				}else if( $data['sale_type'] == 'won' && $data['won_goods_sale'] && $price ){
					$data['goods_sale'] = $data['won_goods_sale'];
				}
			}

			if( $data['type'] == 'mobile' ) {//기존 모바일쿠폰제외
				$data['type']				= 'download';//상품쿠폰으로 대체
				$data['sale_agent']	= ($data['sale_agent']!= 'm')?'m':'';//사용환경 모바일로 대체
			}

			//사용제한 - 유입경로 체크
			/**if( couponordercheck(&$data, $goodsSeq, $price, 1) != true ) {
				continue;
			}**/

			$result[] = $data;
		}
		return $result;
	}

	/* 입점마케팅 전달 데이터 통합 설정 할인 쿠폰 설정 적용 */
	public function get_marketing_feed_coupon_max($today,$memberSeq,$goodsSeq,$category,$price)
	{
		$issue_subquery = $except_subquery = "";
		if(count($category)>0){
			$issue_subquery = "+(
												select count(*) FROM
												fm_coupon_issuecategory
												WHERE coupon_seq=c.coupon_seq AND `type`='issue' AND category_code IN ('".implode("','",$category)."')
											)";
			$except_subquery = "+(
													select count(*) FROM
													fm_coupon_issuecategory
													WHERE coupon_seq=c.coupon_seq AND `type`='except' AND category_code IN ('".implode("','",$category)."')
												)";
		}

		// 상품쿠폰만 적용
		$coupontype = " c.type = 'download' AND c.sale_agent = 'a' AND c.use_type = 'online' AND c.issue_stop = '0' ";

		// 해당 유효기간만 적용
		$coupontype .= " AND ( (c.issue_priod_type = 'day') OR (c.issue_priod_type = 'date' AND c.issue_startdate <= '".$today."' AND c.issue_enddate >='".$today."') )";

		$query = "SELECT coupon.*
					FROM (
						SELECT c.*,
							(
								SELECT count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND `type`='issue'
							)+
							(
								SELECT count(*)
								FROM fm_coupon_issuecategory
								WHERE coupon_seq=c.coupon_seq AND `type`='issue'
							) as all_issue_cnt,
							(
								SELECT count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND goods_seq='".$goodsSeq."' AND `type`='issue'
							)".$issue_subquery." as issue_cnt,
							(
								select count(*)
								FROM fm_coupon_issuegoods
								WHERE coupon_seq=c.coupon_seq AND goods_seq='".$goodsSeq."' AND `type`='except'
							)".$except_subquery." as except_cnt
						FROM fm_coupon c
						WHERE
							(
								".$coupontype."
								AND (
									(c.download_startdate is null  AND c.download_enddate is null )
									OR
									(c.download_startdate <='".date('Y-m-d H:i:s',time())."' AND c.download_enddate >='".date('Y-m-d H:i:s',time())."')
								)
								AND (
									(c.download_starttime is null  AND c.download_endtime is null )
									OR
									(c.download_starttime <='".date('H:i',time())."' AND c.download_endtime >= '".date('H:i',time())."')
								)
								AND INSTR(c.download_week, '".date('N')."') > 0
							)
						GROUP BY c.coupon_seq
					) coupon
				WHERE except_cnt=0
					AND (all_issue_cnt=0 OR issue_cnt>0)
					AND (issue_type = 'all' OR (issue_type = 'issue' AND all_issue_cnt > 0) OR (issue_type = 'except' AND all_issue_cnt = 0) ) ORDER BY coupon_seq ASC";

		$query = $this->db->query($query);

		$max = 0;
		$maxCoupon = array();
		foreach($query->result_array() as $data) {
			$data['goods_sale'] = 0;
			if( $data['sale_type'] == 'percent' && $data['percent_goods_sale'] && $price ){
				$data['goods_sale'] = $data['percent_goods_sale'] * $price / 100;
			}else if( $data['sale_type'] == 'won' && $data['won_goods_sale'] && $price ){
				$data['goods_sale'] = $data['won_goods_sale'];
			}

			if($max < $data['goods_sale']) {
				$max = $data['goods_sale'];
				$maxCoupon = $data;
			}
			//$result[] = $data;
		}

		// 적용금액 -> 정액, 정률 다 따져서 금액이 가장 높은 쿠폰 리턴
		return $maxCoupon;
	}

	/* 주문 시 다운로드 가능한 목록  */
	public function get_able_use_list($member_seq,$goods_seq,$category, $price, $goodprice,$ea=1)
	{ 
		if(!$member_seq) return;
		//$today = date("Y-m-d",time());
		if( ! $this->config_system['cutting_price'] ) $this->config_system['cutting_price'] = 10;
		$result = $issue_subquery = $except_subquery = "";

		/**if($this->_is_mobile_agent) {
			$coupontype = " and type NOT IN ('shipping')";
		}else{
			$coupontype = " and type NOT IN ('shipping','mobile')";
		}**/		
		if($this->_is_mobile_agent) {
			$coupontypear = array('shipping','offline_emoney');
			$mobilequery = " ";
		}else{
			$coupontypear = array('shipping','mobile','offline_emoney');
			$mobilequery = " AND sale_agent != 'm' ";
		} 
		$coupontype = " AND (type NOT IN ('".implode("','",$coupontypear)."') and  right(type,9) != '_shipping' ) ".$mobilequery;

		if(count($category)>0){
			$issue_subquery = ", (
				SELECT count(*)
				FROM fm_download_issuecategory
				WHERE download_seq=d.download_seq AND `type`= d.issue_type AND category_code IN ('".implode("','",$category)."')) as cate_cnt";

			$query = "select * from
			(
				select d.*,
				(
					SELECT count(*)
					FROM fm_download_issuegoods
					WHERE download_seq=d.download_seq AND goods_seq='".$goods_seq."' AND `type`= d.issue_type
				) as goods_cnt ".$issue_subquery."
				from fm_download d
				where member_seq = ?
				and issue_startdate <= ?
				and issue_enddate >= ?
				and use_status = 'unused'
			) a where
				(
					(issue_type = 'all')
					OR
					(issue_type = 'issue' AND (goods_cnt > 0 OR cate_cnt > 0))
					OR
					(issue_type = 'except' AND (goods_cnt = 0 AND cate_cnt = 0))
				)
				{$coupontype}
			";
		}else{
			$issue_subquery = '';
			$query = "select * from
			(
				select d.*,
				(
					SELECT count(*)
					FROM fm_download_issuegoods
					WHERE download_seq=d.download_seq AND goods_seq='".$goods_seq."' AND `type`= d.issue_type
				) as goods_cnt ".$issue_subquery."
				from fm_download d
				where member_seq = ?
				and issue_startdate <= ?
				and issue_enddate >= ?
				and use_status = 'unused'
			) a where
				(
					(issue_type = 'all')
					OR
					(issue_type = 'issue' AND (goods_cnt > 0))
					OR
					(issue_type = 'except' AND (goods_cnt = 0))
				)
				{$coupontype}
			";
		}
		$query = $this->db->query($query,array($member_seq, $this->today, $this->today)); 
		$result = array(); 
		foreach($query->result_array() as $data){ 

			//사용제한 - 유입경로 체크
			if( couponordercheck(&$data, $goods_seq, $price, $ea) != true ) {
				continue;
			}  
			$data['goods_sale'] = 0;

			if( $data['coupon_same_time'] == 'N' ) {//단독쿠폰이면
				$data['couponsametimetitle'] = '-단독';
			}else{
				$data['couponsametimetitle'] = '';
			}

			if( $data['sale_payment'] == 'b' ) {//무통장만가능
				$data['couponsametimetitle'] .= ($data['couponsametimetitle'])?',무통장':'무통장';
			}

			if( $data['limit_goods_price'] <= $price ) {//사용제한 원이상인경우만
				if( $data['sale_type'] == 'percent' && $data['percent_goods_sale'] && $goodprice ){

					if( $this->config_system['cutting_price'] != 'none' ){
						$data['goods_sale'] = $data['percent_goods_sale'] * $goodprice / ( $this->config_system['cutting_price'] * 100);
						$data['goods_sale'] = floor($data['goods_sale']);
						$data['goods_sale'] = $data['goods_sale'] * $this->config_system['cutting_price'];
					}else{
						$data['goods_sale'] = $data['percent_goods_sale'] * $goodprice / 100;
						$data['goods_sale'] = floor($data['goods_sale']);
					}

					if($data['max_percent_goods_sale'] < $data['goods_sale']){
						$data['goods_sale'] = $data['max_percent_goods_sale'];
					}
				}else if( $data['sale_type'] == 'won' && $data['won_goods_sale'] && $goodprice ){
					$data['goods_sale'] = $data['won_goods_sale'];
				}

				$data['goods_sale'] = get_price_point($data['goods_sale']);

				// 쿠폰 할인 금액 체크
				$goods_sale = $data['goods_sale'];
				if($data['duplication_use'] == 1) $goods_sale = $data['goods_sale'] * $ea;
				// debug_var($goodprice."/".$ea."/".$goods_sale);
				//if($goodprice*$ea >= $goods_sale && $goods_sale) $result[] = $data;

				//상품의 총할인금액보다 쿠폰할인금액이 큰경우 상품할인금액으로 대체
				if($goodprice*$ea < $goods_sale && $goods_sale)
				{
					$data['goods_sale'] = $goodprice;
				} 

				if( $data['type'] == 'mobile' ) {//기존 모바일쿠폰제외
					$data['type']				= 'download';//상품쿠폰으로 대체
					$data['sale_agent']	= ($data['sale_agent']!= 'm')?'m':'';//사용환경 모바일로 대체
				}

				$result[] = $data;
			}
		}
		@usort($result, 'goods_sale_desc');//할인금액내림차순
		return $result;
	}

	/* 주문 시 배송비쿠폰 다운로드 가능한 목록  */
	public function get_shipping_use_list($member_seq, $price, $shippingprice,$sellcoupon=null, $shippingcouponprice=null)
	{
		//$today = date("Y-m-d",time());
		$result = "";
		$query = "select * from
		(
			select d.*
			from fm_download d
			where member_seq = ?
			and issue_startdate <= ?
			and issue_enddate >= ?
			and use_status = 'unused'
		) a where (type = 'shipping' or  right(type,9) = '_shipping')
		";
		
		if(!$this->_is_mobile_agent) {
			$query .= " and sale_agent != 'm' ";
		}

		$query = $this->db->query($query,array($member_seq, $this->today, $this->today));
		foreach($query->result_array() as $data) {
			
			//사용제한 - 유입경로 체크
			if( couponordercheck(&$data, '', $price, 1) != true ) {
				continue;
			}

			if($sellcoupon == $data['download_seq'] ) {
				$data['shipping_sale'] = $shippingcouponprice;
				$result[] = $data;
			}else{
				$data['shipping_sale'] = 0;
				if( $data['limit_goods_price'] <= $price ) {//사용제한 원이상만
					if( $data['shipping_type'] == 'free' ){
						if($data['max_percent_shipping_sale'] > 0 && $data['max_percent_shipping_sale'] < $shippingprice ){
							$data['shipping_sale'] =  $data['max_percent_shipping_sale'];
						}else{
							$data['shipping_sale'] =  $shippingprice;
						}
					}else if( $data['shipping_type'] == 'won'){
						$data['shipping_sale'] = $data['won_shipping_sale'];
					}

					//할인금액이 판매금액보다 큰경우 구매금액
					if($shippingprice < $data['shipping_sale']){
						$data['shipping_sale'] = $shippingprice;
					}
					$data['shipping_sale'] = get_price_point($data['shipping_sale']); 

					if( $data['coupon_same_time'] == 'N' ) {//단독쿠폰이면
						$data['couponsametimetitle'] = '-단독';
					}else{
						$data['couponsametimetitle'] = '';
					}

					if( $data['sale_payment'] == 'b' ) {//무통장만가능
						$data['couponsametimetitle'] .= ($data['couponsametimetitle'])?',무통장':'무통장';
					}
					

					if( $data['type'] == 'mobile' ) {//기존 모바일쿠폰제외
						$data['type']				= 'download';//상품쿠폰으로 대체
						$data['sale_agent']	= ($data['sale_agent']!= 'm')?'m':'';//사용환경 모바일로 대체
					}

					$result[] = $data;
				}
			}
		}
		
		@usort($result, 'shipping_sale_desc');//할인금액내림차순
		return $result;
	}

	/* 쿠폰을 사용한 주문의 쿠폰 발급 상태 변경 */
	function set_download_use_status($download_seq,$status,$manager_name='',$manager_code='')
	{
		$use_date = date('Y-m-d H:i:s',time());
		$this->db->where('download_seq', $download_seq);
		if($manager_name || $manager_code){
			$this->db->update('fm_download', array('use_status' => $status,'use_date' => $use_date,'confirm_user' => $manager_name,'confirm_user_serial' => $manager_code));
		}else{
			$this->db->update('fm_download', array('use_status' => $status,'use_date' => $use_date));
		}
	}

	//관리자 > 쿠폰 직접발급
	public function _admin_downlod($couponSeq, $memberSeq)
	{
		$now_timestamp = time();
		//$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		// 쿠폰 정보 확인
		$coupons = $this->get_admin_download($memberSeq, $couponSeq);
		if($coupons) return false;//이미 다운받은 쿠폰이 있습니다.

		$couponData = $this->get_coupon($couponSeq);
		if(!$couponData) return false;//쿠폰이 올바르지 않습니다.

		$paramInsert['member_seq']							= $memberSeq;
		$paramInsert['coupon_seq']							= $couponSeq;
		$paramInsert['type']										= $couponData['type'];
		$paramInsert['use_type']								= $couponData['use_type'];
		$paramInsert['coupon_name']							= $couponData['coupon_name'];
		$paramInsert['coupon_desc']							= $couponData['coupon_desc'];
		$paramInsert['coupon_same_time']				= $couponData['coupon_same_time'];
		$paramInsert['sale_type']								= $couponData['sale_type'];
		$paramInsert['issue_type']								= if_empty($couponData, 'issue_type', 'all');//$couponData['issue_type'];
		$paramInsert['shipping_type']							= $couponData['shipping_type'];
		$paramInsert['max_percent_shipping_sale']	= $couponData['max_percent_shipping_sale'];
		$paramInsert['won_shipping_sale']				= $couponData['won_shipping_sale'];
		$paramInsert['percent_goods_sale']				= $couponData['percent_goods_sale'];
		$paramInsert['max_percent_goods_sale']		= $couponData['max_percent_goods_sale'];
		$paramInsert['won_goods_sale']					= $couponData['won_goods_sale'];
		$paramInsert['duplication_use']						= $couponData['duplication_use'];
		$paramInsert['limit_goods_price']					= $couponData['limit_goods_price'];

		$paramInsert['sale_agent']								= $couponData['sale_agent'];
		$paramInsert['sale_payment']							= $couponData['sale_payment'];
		$paramInsert['sale_referer']							= $couponData['sale_referer'];
		$paramInsert['sale_referer_type']					= $couponData['sale_referer_type'];
		$paramInsert['sale_referer_item']					= $couponData['sale_referer_item'];
		//$paramInsert['memberlogin_terms']				= $couponData['memberlogin_terms'];
		//$paramInsert['order_terms']							= $couponData['order_terms'];

		$paramInsert['use_status']							= 'unused';
		$paramInsert['regist_date']							= $now;
		if($couponData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $couponData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $couponData['issue_startdate'];
			$paramInsert['issue_enddate']	= $couponData['issue_enddate'];
		}
		$this->db->insert('fm_download', $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		$couponGoods 	= $this->get_coupon_issuegoods($couponSeq);
		if($couponGoods) foreach($couponGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuegoods', $paramInsert);
			unset($paramInsert);
		}

		$couponCategory = $this->get_coupon_issuecategory($couponSeq);
		if($couponCategory) foreach($couponCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuecategory', $paramInsert);
			unset($paramInsert);
		}
		return true;
	}

	//사용자 > 상품쿠폰 다운시 사용안하고--> _members_downlod 공통이용
	public function _goods_downlod_( $couponSeq, $memberSeq) 
	{
		if(empty($couponSeq))return false;
		if(empty($memberSeq))return false;

		$now_timestamp = time();
		//$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		$couponData 	= $this->get_coupon($couponSeq);
		if(!$couponData) return false;//쿠폰이 올바르지 않습니다.

		$paramInsert['member_seq']							= $memberSeq;
		$paramInsert['coupon_seq']							= $couponSeq;
		$paramInsert['type']										= $couponData['type'];
		$paramInsert['use_type']								= $couponData['use_type'];
		$paramInsert['offline_type']							= $couponData['offline_type'];
		$paramInsert['offline_emoney']						= $couponData['offline_emoney'];
		$paramInsert['coupon_point']							= $couponData['coupon_point'];

		$paramInsert['coupon_name']							= $couponData['coupon_name'];
		$paramInsert['coupon_desc']							= $couponData['coupon_desc'];
		$paramInsert['coupon_same_time']				= $couponData['coupon_same_time'];
		$paramInsert['sale_type']								= $couponData['sale_type'];
		$paramInsert['issue_type']								= if_empty($couponData, 'issue_type', 'all');//$couponData['issue_type'];
		$paramInsert['shipping_type']							= $couponData['shipping_type'];
		$paramInsert['max_percent_shipping_sale']	= $couponData['max_percent_shipping_sale'];
		$paramInsert['won_shipping_sale']				= $couponData['won_shipping_sale'];
		$paramInsert['percent_goods_sale']				= $couponData['percent_goods_sale'];
		$paramInsert['max_percent_goods_sale']		= $couponData['max_percent_goods_sale'];
		$paramInsert['won_goods_sale']					= $couponData['won_goods_sale'];
		$paramInsert['duplication_use']						= $couponData['duplication_use'];
		$paramInsert['limit_goods_price']					= $couponData['limit_goods_price'];
		
		$paramInsert['sale_agent']								= $couponData['sale_agent'];
		$paramInsert['sale_payment']							= $couponData['sale_payment'];
		$paramInsert['sale_referer']							= $couponData['sale_referer'];
		$paramInsert['sale_referer_type']					= $couponData['sale_referer_type'];
		$paramInsert['sale_referer_item']					= $couponData['sale_referer_item'];
		//$paramInsert['memberlogin_terms']				= $couponData['memberlogin_terms'];
		//$paramInsert['order_terms']							= $couponData['order_terms'];

		$paramInsert['use_status']								= 'unused';
		$paramInsert['regist_date']								= $now;

		if($couponData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $couponData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']			= $couponData['issue_startdate'];
			$paramInsert['issue_enddate']			= $couponData['issue_enddate'];
		}

		$this->db->insert('fm_download', $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		$couponGoods 	= $this->couponmodel->get_coupon_issuegoods($couponSeq);
		if($couponGoods) foreach($couponGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuegoods', $paramInsert);
			unset($paramInsert);
		}

		$couponCategory = $this->couponmodel->get_coupon_issuecategory($couponSeq);
		if($couponCategory) foreach($couponCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuecategory', $paramInsert);
			unset($paramInsert);
		}
		return true;
	}

	//사용자 > 쿠폰다운시 자동쿠폰/상품쿠폰/직접발급쿠폰 제외
	public function _members_downlod( $couponSeq, $memberSeq)
	{
		if(empty($memberSeq))return false;
		if(empty($couponSeq))return false;

		$now_timestamp = time();
		//$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);
		$couponData 	= $this->get_coupon($couponSeq);
		if(!$couponData) return false;//쿠폰이 올바르지 않습니다.
		
		
		//배송비만 중복다운체크
		//$downcoupons = $this->get_admin_download($memberSeq, $couponSeq); 
		if( $couponData['type'] == 'shipping' && $couponData['duplication_use'] != 1  && $downcoupons ) {
			return false;//이미 다운받은 쿠폰이 있습니다.
		}


		$paramInsert['member_seq']							= $memberSeq;
		$paramInsert['coupon_seq']							= $couponSeq;
		$paramInsert['type']										= $couponData['type'];
		$paramInsert['use_type']								= $couponData['use_type'];
		$paramInsert['offline_type']							= $couponData['offline_type'];
		$paramInsert['offline_emoney']						= $couponData['offline_emoney'];
		$paramInsert['coupon_point']							= $couponData['coupon_point'];

		$paramInsert['coupon_name']							= $couponData['coupon_name'];
		$paramInsert['coupon_desc']							= $couponData['coupon_desc'];
		$paramInsert['coupon_same_time']				= $couponData['coupon_same_time'];
		$paramInsert['sale_type']								= $couponData['sale_type'];
		$paramInsert['issue_type']								= if_empty($couponData, 'issue_type', 'all');//$couponData['issue_type'];
		$paramInsert['shipping_type']							= $couponData['shipping_type'];
		$paramInsert['max_percent_shipping_sale']	= $couponData['max_percent_shipping_sale'];
		$paramInsert['won_shipping_sale']				= $couponData['won_shipping_sale'];
		$paramInsert['percent_goods_sale']				= $couponData['percent_goods_sale'];
		$paramInsert['max_percent_goods_sale']		= $couponData['max_percent_goods_sale'];
		$paramInsert['won_goods_sale']					= $couponData['won_goods_sale'];
		$paramInsert['duplication_use']						= $couponData['duplication_use'];
		$paramInsert['limit_goods_price']					= $couponData['limit_goods_price'];
		
		$paramInsert['sale_agent']								= $couponData['sale_agent'];
		$paramInsert['sale_payment']							= $couponData['sale_payment'];
		$paramInsert['sale_referer']							= $couponData['sale_referer'];
		$paramInsert['sale_referer_type']						= $couponData['sale_referer_type'];
		$paramInsert['sale_referer_item']						= $couponData['sale_referer_item'];
		//$paramInsert['memberlogin_terms']						= $couponData['memberlogin_terms'];
		//$paramInsert['order_terms']							= $couponData['order_terms'];

		$paramInsert['use_status']								= 'unused';
		$paramInsert['regist_date']								= $now;

		//생일년도/기념일년 체크
		if( $couponData['type'] == 'birthday' || $couponData['type'] == 'anniversary') {
			if($this->today > $downcoupons['anniversary_beforeday'] && $this->today > $downcoupons['anniversary_afterday']) {
				$down_year = date('Y')+1;
			}else{
				$down_year = date('Y');
			}
			$paramInsert['down_year']							= $down_year;
		}elseif( $couponData['type'] == 'memberGroup' || $couponData['type'] == 'memberGroup_shipping') {
			$paramInsert['down_year']							= $this->userInfo['group_seq'];//등급쿠폰이면 해당등급
		}
 
		if($couponData['issue_priod_type'] == 'months'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$paramInsert['issue_enddate']	= date("Y-m-t");//당월의 말일
		}elseif($couponData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $couponData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $couponData['issue_startdate'];
			$paramInsert['issue_enddate']	= $couponData['issue_enddate'];
		}
		if($couponData['type'] == 'point'){//point 전환쿠폰
			$this->load->model('membermodel');
			$this->mdata = $this->membermodel->get_member_data($memberSeq);//회원정보
			if( $this->mdata['point']<1 || $this->mdata['point'] < $couponData['coupon_point'] ) {//포인트가 작거나 없는 경우
				if( $this->mdata['point']<1 ) {//포인트가 작거나 없는 경우
					return false;// 보유포인트가 없습니다
				}else{
					return false;//전환포인트 금액이 보유포인트보다 작습니다
				}
			}
		}
		$this->db->insert('fm_download', $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		if($couponData['type'] == 'point'){//point 전환쿠폰
			$this->load->model('membermodel');
			$params = array(
				'gb'		=> 'minus',
				'type'		=> 'coupon',
				'point'	=> $couponData['coupon_point'],
				'memo'		=> "[차감]포인트전환 쿠폰 [".$couponData['coupon_name']."] 다운에 의한 포인트 차감",
			);//:".$downloadSeq."
			$this->membermodel->point_insert($params, $memberSeq);
		}

		$couponGoods 	= $this->get_coupon_issuegoods($couponSeq);
		if($couponGoods) foreach($couponGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuegoods', $paramInsert);
			unset($paramInsert);
		}

		$couponCategory = $this->get_coupon_issuecategory($couponSeq);
		if($couponCategory) foreach($couponCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuecategory', $paramInsert);
			unset($paramInsert);
		}

		return true;
	}

	//사용자 > 오프라인쿠폰 인증시
	public function _offlinecoupon_members_downlod( $couponSeq, $memberSeq, $offline_serialnumber)
	{
		if(empty($memberSeq))return false;
		if(empty($couponSeq))return false;

		$now_timestamp = time();
		//$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		//중복등록가능
		$couponData 	= $this->get_coupon($couponSeq);
		if(!$couponData) return false;//쿠폰이 올바르지 않습니다.

		$paramInsert['member_seq']							= $memberSeq;
		$paramInsert['coupon_seq']							= $couponSeq;
		$paramInsert['type']										= $couponData['type'];
		$paramInsert['use_type']								= $couponData['use_type'];
		$paramInsert['offline_type']							= $couponData['offline_type'];
		$paramInsert['offline_emoney']						= $couponData['offline_emoney'];
		$paramInsert['coupon_point']							= $couponData['coupon_point'];
		$paramInsert['offline_input_serialnumber']		= $offline_serialnumber;
		$paramInsert['coupon_name']							= $couponData['coupon_name'];
		$paramInsert['coupon_desc']							= $couponData['coupon_desc'];
		$paramInsert['coupon_same_time']				= $couponData['coupon_same_time'];
		$paramInsert['sale_type']								= $couponData['sale_type'];
		$paramInsert['issue_type']								= if_empty($couponData, 'issue_type', 'all');//$couponData['issue_type'];
		$paramInsert['shipping_type']							= $couponData['shipping_type'];
		$paramInsert['max_percent_shipping_sale']	= $couponData['max_percent_shipping_sale'];
		$paramInsert['won_shipping_sale']				= $couponData['won_shipping_sale'];
		$paramInsert['percent_goods_sale']				= $couponData['percent_goods_sale'];
		$paramInsert['max_percent_goods_sale']		= $couponData['max_percent_goods_sale'];
		$paramInsert['won_goods_sale']					= $couponData['won_goods_sale'];
		$paramInsert['duplication_use']						= $couponData['duplication_use'];
		$paramInsert['limit_goods_price']					= $couponData['limit_goods_price'];
		
		$paramInsert['sale_agent']								= $couponData['sale_agent'];
		$paramInsert['sale_payment']							= $couponData['sale_payment'];
		$paramInsert['sale_referer']							= $couponData['sale_referer'];
		$paramInsert['sale_referer_type']					= $couponData['sale_referer_type'];
		$paramInsert['sale_referer_item']					= $couponData['sale_referer_item'];
		//$paramInsert['memberlogin_terms']				= $couponData['memberlogin_terms'];
		//$paramInsert['order_terms']							= $couponData['order_terms'];

		$paramInsert['use_status']								= 'unused';
		$paramInsert['regist_date']								= $now;

		if($couponData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $couponData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $couponData['issue_startdate'];
			$paramInsert['issue_enddate']	= $couponData['issue_enddate'];
		}
		$this->db->insert('fm_download', $paramInsert);
		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		$couponGoods 	= $this->get_coupon_issuegoods($couponSeq);
		if($couponGoods) foreach($couponGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuegoods', $paramInsert);
			unset($paramInsert);
		}

		$couponCategory = $this->get_coupon_issuecategory($couponSeq);
		if($couponCategory) foreach($couponCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuecategory', $paramInsert);
			unset($paramInsert);
		}

		return true;
	}

	//사용자 > 오프라인 적립금 쿠폰 발급완료처리
	public function _offlinecoupon_members_emoney_downlod( $couponSeq, $memberSeq, $offline_serialnumber)
	{
		if(empty($memberSeq))return false;
		if(empty($couponSeq))return false;

		$now_timestamp = time();
		//$today = date('Y-m-d',$now_timestamp);
		$now = date('Y-m-d H:i:s',$now_timestamp);

		$couponData 	= $this->get_coupon($couponSeq);
		if(!$couponData) return false;//쿠폰이 올바르지 않습니다.

		$paramInsert['member_seq']							= $memberSeq;
		$paramInsert['coupon_seq']							= $couponSeq;
		$paramInsert['type']										= $couponData['type'];
		$paramInsert['use_type']								= $couponData['use_type'];
		$paramInsert['offline_type']							= $couponData['offline_type'];
		$paramInsert['offline_emoney']						= $couponData['offline_emoney'];
		$paramInsert['coupon_point']							= $couponData['coupon_point'];
		$paramInsert['offline_input_serialnumber']		= $offline_serialnumber;
		$paramInsert['coupon_name']							= $couponData['coupon_name'];
		$paramInsert['coupon_desc']							= $couponData['coupon_desc'];
		$paramInsert['coupon_same_time']				= $couponData['coupon_same_time'];
		$paramInsert['sale_type']								= $couponData['sale_type'];
		$paramInsert['issue_type']								= if_empty($couponData, 'issue_type', 'all');//$couponData['issue_type'];
		$paramInsert['shipping_type']							= $couponData['shipping_type'];
		$paramInsert['max_percent_shipping_sale']	= $couponData['max_percent_shipping_sale'];
		$paramInsert['won_shipping_sale']				= $couponData['won_shipping_sale'];
		$paramInsert['percent_goods_sale']				= $couponData['percent_goods_sale'];
		$paramInsert['max_percent_goods_sale']		= $couponData['max_percent_goods_sale'];
		$paramInsert['won_goods_sale']					= $couponData['won_goods_sale'];
		$paramInsert['duplication_use']						= $couponData['duplication_use'];
		$paramInsert['limit_goods_price']					= $couponData['limit_goods_price'];
		
		$paramInsert['sale_agent']								= $couponData['sale_agent'];
		$paramInsert['sale_payment']							= $couponData['sale_payment'];
		$paramInsert['sale_referer']							= $couponData['sale_referer'];
		$paramInsert['sale_referer_type']					= $couponData['sale_referer_type'];
		$paramInsert['sale_referer_item']					= $couponData['sale_referer_item'];
		//$paramInsert['memberlogin_terms']				= $couponData['memberlogin_terms'];
		//$paramInsert['order_terms']							= $couponData['order_terms'];

		$paramInsert['use_status']								= 'used';//사용함처리
		$paramInsert['use_date']								= $now;
		$paramInsert['regist_date']								= $now;

		if($couponData['issue_priod_type'] == 'day'){
			$paramInsert['issue_startdate']	= date("Y-m-d",$now_timestamp);
			$to_timestamp = $now_timestamp + ( $couponData['after_issue_day'] * 86400 );
			$paramInsert['issue_enddate']	= date("Y-m-d",$to_timestamp);
		}else{
			$paramInsert['issue_startdate']	= $couponData['issue_startdate'];
			$paramInsert['issue_enddate']	= $couponData['issue_enddate'];
		}
		$this->db->insert('fm_download', $paramInsert);

		$downloadSeq = $this->db->insert_id();
		unset($paramInsert);

		$couponGoods 	= $this->get_coupon_issuegoods($couponSeq);
		if($couponGoods) foreach($couponGoods as $paramInsert){
			unset($paramInsert['issuegoods_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuegoods', $paramInsert);
			unset($paramInsert);
		}

		$couponCategory = $this->get_coupon_issuecategory($couponSeq);
		if($couponCategory) foreach($couponCategory as $paramInsert){
			unset($paramInsert['issuecategory_seq'],$paramInsert['coupon_seq']);
			$paramInsert['download_seq'] = $downloadSeq;
			$this->db->insert('fm_download_issuecategory', $paramInsert);
			unset($paramInsert);
		}

		return true;
	}

	/* 환불에 의한 쿠폰 복원 */
	public function restore_used_coupon($download_seq){

		$sql = "select * from fm_download where download_seq=?";
		$query = $this->db->query($sql,array($download_seq));
		list($download) = $query->result_array($query);

		$sqlck = "select * from fm_download where refund_download_seq=?";
		$queryck = $this->db->query($sqlck,array($download_seq));
		list($downloadck) = $queryck->result_array($queryck);

		if($download && !$downloadck ) {

			$remain_issue_day = (strtotime($download['issue_enddate'])-strtotime(substr($download['use_date'],0,10))) / 86400;
			$remain_issue_day = $remain_issue_day ? (int)$remain_issue_day : 1;

			$download['regist_date']				= date('Y-m-d H:i:s');
			$download['issue_startdate']			= date('Y-m-d');
			$download['issue_enddate']				= date('Y-m-d',strtotime("+".$remain_issue_day." day"));
			$download['coupon_name']				= "[복원]".$download['coupon_name'];
			$download['refund_download_seq']		= $download_seq;

			unset($download['download_seq']);
			unset($download['use_status']);
			unset($download['use_date']);
			//unset($download['order_seq']);

			$this->db->insert('fm_download', $download);
			$item_seq = $this->db->insert_id();

			$success = $item_seq;

			$couponGoods 	= $this->get_coupon_download_issuegoods($download_seq);
			if($couponGoods) foreach($couponGoods as $paramInsert){
				unset($paramInsert['issuegoods_seq'],$paramInsert['coupon_seq']);
				$paramInsert['download_seq'] = $success;
				$this->db->insert('fm_download_issuegoods', $paramInsert);
				unset($paramInsert);
			}

			$couponCategory = $this->get_coupon_download_issuecategory($download_seq);
			if($couponCategory) foreach($couponCategory as $paramInsert){
				unset($paramInsert['issuecategory_seq'],$paramInsert['coupon_seq']);
				$paramInsert['download_seq'] = $success;
				$this->db->insert('fm_download_issuecategory', $paramInsert);
				unset($paramInsert);
			}

		}else{
			$success = false;
		}

		return $success;
	}

	public function get_coupon_download_issuecategory($download_seq)
	{
		$result = false;
		$this->db->where('download_seq', $download_seq);
		$query = $this->db->get($this->coupon_download_issuecategory);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	public function get_coupon_download_issuegoods($download_seq)
	{
		$result = false;
		$this->db->where('download_seq', $download_seq);
		$query = $this->db->get($this->coupon_download_issuegoods);
		foreach( $query->result_array() as $row ){
			$result[] = $row;
		}
		return $result;
	}

	/* 환불에 의한 복원쿠폰가져오기 */
	public function restore_used_coupon_refund($refund_download_seq){
		$sql = "select * from fm_download where refund_download_seq=?";
		$query = $this->db->query($sql,array($refund_download_seq));
		list($download) = $query->result_array($query);
		if($download){
			$success = $download['download_seq'];
		}else{
			$success = false;
		}
		return $success;
	}

	/* 오프라인쿠폰 */
	public function get_offlinecoupon_total_count($sc)
	{
		$sc['whereis'] = ($sc['whereis'])?' where 1 '. $sc['whereis']:'';
		$sql = 'select coupon_seq from '.$this->offlinecoupon. $sc['whereis'];
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 오프라인 인증번호 보기
	* @param
	*/
	public function offlinecoupon_list($sc, $all=false)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, c.*, off.*
					from ".$this->offlinecoupon." off
					left join ".$this->table_coupon." c on c.coupon_seq = off.coupon_seq where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and off.coupon_seq = '.$sc['no'].' ';
		}

		if(!empty($sc['search_text']))$sql.= " and off.offline_serialnumber like '%".$sc['search_text']."%' ";

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and off.regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and off.regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and d.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		$sql.=" order by off.offline_seq desc ";
		if(!$all) $sql .=" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		if(!$all) {
			//총건수
			$sql = "SELECT FOUND_ROWS() as COUNT";
			$query_count = $this->db->query($sql);
			$res_count= $query_count->result_array();
			$data['count'] = $res_count[0]['COUNT'];
		}

		return $data;
	}

	// 총건수
	public function get_offlinecoupon_item_total_count($no)
	{
		$sql = 'select offline_seq from '.$this->offlinecoupon.' where 1';
		if( !empty($no) )
		{
			$sql .= ' and coupon_seq = '.$no.' ';
		}

		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	//개별오프라인쿠폰 정보가가져오기
	public function get_offlinecoupon($no)
	{
		$this->db->limit(1,0);
		$this->db->where('offline_seq', $no);
		$query = $this->db->get($this->offlinecoupon);
		$result = $query->result_array();
		return $result[0];
	}

	//개별오프라인 가져오기
	public function get_offlinecoupon_serialnumber($offline_serialnumber)
	{
		$this->db->limit(1,0);
		$this->db->where('offline_serialnumber', $offline_serialnumber);
		$query = $this->db->get($this->offlinecoupon);
		$result = $query->result_array();
		return $result[0];
	}

	/* 오프라인쿠폰 > 자동등록 : 사용건수 - 변경 */
	function set_offlinecoupon_use_count($offline_serialnumber)
	{
		$upsql = "update ".$this->offlinecoupon." set use_count = use_count-1 where offline_serialnumber = '{$offline_serialnumber}'";
		$this->db->query($upsql);
	}

	// 오프라인쿠폰 > 수동등록
	public function get_offlinecoupon_input_total_count($sc)
	{
		$sc['whereis'] = ($sc['whereis'])?' where 1 '. $sc['whereis']:'';
		$sql = 'select coupon_seq from '.$this->offlinecoupon_input.$sc['whereis'];
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 오프라인 인증번호 보기  > 수동등록
	* @param
	*/
	public function offlinecoupon_input_list($sc, $all=false)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, off.*
					from ".$this->offlinecoupon_input." off
					left join ".$this->table_coupon." c on c.coupon_seq = off.coupon_seq where 1 ";

		if( !empty($sc['no']) )
		{
			$sql .= ' and off.coupon_seq = '.$sc['no'].' ';
		}
		if(!empty($sc['search_text']))$sql.= " and off.offline_serialnumber like '%".$sc['search_text']."%' ";

		// 등록일 검색(시작)
		if($sc['sdate'] AND !$sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$sql.=" and off.regist_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['edate'] AND !$sc['sdate']) {
			$start_date = $sc['edate'].' 23:59:59';
			$sql.=" and off.regist_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['sdate'] AND $sc['edate']) {
			$start_date = $sc['sdate'].' 00:00:00';
			$end_date = $sc['edate'].' 23:59:59';
			$sql.=" and d.regist_date BETWEEN '{$start_date}' and '{$end_date}' ";
		}

		$sql.=" order by off.offline_seq desc ";
		if(!$all)$sql .=" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		if(!$all){
			//총건수
			$sql = "SELECT FOUND_ROWS() as COUNT";
			$query_count = $this->db->query($sql);
			$res_count= $query_count->result_array();
			$data['count'] = $res_count[0]['COUNT'];
		}

		return $data;
	}

	// 오프라인쿠폰 > 수동등록 총건수
	public function get_offlinecoupon_input_item_total_count($no)
	{
		$sql = 'select offline_seq from '.$this->offlinecoupon_input.' where 1';
		if( !empty($no) )
		{
			$sql .= ' and coupon_seq = '.$no.' ';
		}
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	//개별오프라인 수동쿠폰 가져오기
	public function get_offlinecoupon_input($no)
	{
		$this->db->limit(1,0);
		$this->db->where('offline_seq', $no);
		$query = $this->db->get($this->offlinecoupon_input);
		$result = $query->result_array();
		return $result[0];
	}


	//개별오프라인 수동쿠폰 가져오기
	public function get_offlinecoupon_input_serialnumber($offline_serialnumber)
	{
		$this->db->limit(1,0);
		$this->db->where('offline_serialnumber', $offline_serialnumber);
		$query = $this->db->get($this->offlinecoupon_input);
		$result = $query->result_array();
		return $result[0];
	}

	/* 오프라인쿠폰 > 수동쿠폰 : 사용건수 - 변경 */
	function set_offlinecoupon_input_use_count($offline_serialnumber)
	{
		$upsql = "update ".$this->offlinecoupon_input." set use_count = use_count-1 where offline_serialnumber = '{$offline_serialnumber}'";
		$this->db->query($upsql);
	}

	//보유한 쿠폰 리스트
	public function my_download_list($sc, $all=false)
	{
		$sql = "select SQL_CALC_FOUND_ROWS *, m.userid, m.user_name, d.*
					from ".$this->coupon_download." d
					left join ".$this->members." m on m.member_seq = d.member_seq where 1 ";

		// 장바구니 상품용 쿠폰만 추출
		if	($sc['only_cart_goods'] == 'y'){
			if($this->_is_mobile_agent) {
				$coupontypear	= array('shipping','offline_emoney');
				$mobilequery	= " ";
			}else{
				$coupontypear	= array('shipping','mobile','offline_emoney');
				$mobilequery	= " AND d.sale_agent != 'm' ";
			}
			$sql		.= " AND  (d.type NOT IN ('".implode("','",$coupontypear)."') and  right(d.type,9) != '_shipping' ) ".$mobilequery;
		}

		if( !empty($sc['no']) )
		{
			$sql .= ' and d.coupon_seq = '.$sc['no'].' ';
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

		if(!empty($sc['couponUsed']))
		{
			$couponTypein = implode("','",$sc['couponUsed']);
			$sql.= " and use_status in ('".$couponTypein."') ";
		}

		if(!empty($sc['couponDate'])){
			$arr = array();
			foreach($sc['couponDate'] as $key => $cdata){
				switch($cdata){
					case "available":
						$today = date('Y-m-d');
						$arr[] =" d.issue_enddate >= '{$today}' ";
					break;
					case "extinc":
						$today = date('Y-m-d');
						$arr[] =" d.issue_enddate < '{$today}' ";
					break;
				}
			}
			if($arr) $sql.= " and (".implode(' OR ',$arr).")";
		}

		if ($sc['issue_date']){
		// 유효기간 검색(시작) :: 개인 맞춤형 안내용
			// 유효기간 검색
			if($sc['issue_date']['sdate'] AND $sc['issue_date']['edate']) {
				$start_date = $sc['issue_date']['sdate'];
				$end_date = $sc['issue_date']['edate'];
				$sql.=" and d.issue_enddate BETWEEN '{$start_date}' and '{$end_date}' ";
			}
		}

		if(!empty($sc['keyword']))$sql.= " and coupon_name like \"%".$sc['keyword']."%\" ";

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
		if(!$all)$sql .=" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();

		if(!$all){
			//총건수
			$sql = "SELECT FOUND_ROWS() as COUNT";
			$query_count = $this->db->query($sql);
			$res_count= $query_count->result_array();
			$data['count'] = $res_count[0]['COUNT'];
		}
		return $data;
	}

	//	사용가능한 총건수
	public function get_download_have_total_count($sc, $members)
	{ 
		$sql = "select count(*) as cnt from ".$this->coupon_download;
		if(isset($sc['member_seq'])) $sql.= ' where member_seq ='.$sc['member_seq'];//회원    
		$query = $this->db->query($sql);
		$res_count= $query->row_array();  
		$result['totalcount'] = $res_count['cnt'];  
		$sql = "select count(*) as cnt from ".$this->coupon_download." where use_status='unused' and issue_enddate >= date(now())";
		if(isset($sc['member_seq'])) $sql.= ' and member_seq ='.$sc['member_seq'];//회원  
		$query = $this->db->query($sql);
		$res_count= $query->row_array();  
		$result['unusedcount'] = $res_count['cnt'];

		$data = $this->get_my_download($sc,$members,'totalcnt');
		$result['svcount'] = $data['count'];

		return $result;
	}


	/** 
	*@ 마이페이지의 > 다운로드 가능 쿠폰 목록
	* 생일자 : 생일전 ~ 생일후 기간
	* 기념일 : 기념일전 ~ 기념일후 기간
	* 배송비 : 발급전 ~ 발급후 기간
	* 등록 : 등급조정 이후기간
	==> AND 등급제한 체크
	==> AND 전체수량제한
	**/
	public function get_my_download($sc, $members,$totalcnt=null)
	{ 
		if( $totalcnt == 'totalcnt' ) {
			$sql = "SELECT count(coupon.coupon_seq) as cnt ";
		}else{
			$sql = "SELECT SQL_CALC_FOUND_ROWS coupon.*";
		}
		if($sc['coupon_type'] ) {
			$coupon_typein = @implode("','",$sc['coupon_type']);
			$sqltype = " c.type IN ('{$coupon_typein}') ";
		}else{
			$sqltype = " ( 
							(
								c.type='shipping'
								AND (
									(c.download_startdate is null  AND c.download_enddate is null )
									OR
									(c.download_startdate <='".date('Y-m-d H:i:s',time())."' AND c.download_enddate >='".date('Y-m-d H:i:s',time())."')
								)
								AND (
									(c.download_starttime is null  AND c.download_endtime is null )
									OR
									(c.download_starttime <='".date('H:i',time())."' AND c.download_endtime >= '".date('H:i',time())."')
								) 
							)
							OR 
				( c.type in ('memberGroup','point','birthday','anniversary','memberGroup_shipping','memberlogin','memberlogin_shipping','membermonths','membermonths_shipping','order') )
			) ";
		}

		if( !empty($sc['coupon_seq']) )
		{
			$sqltype .= ' and c.coupon_seq = '.$sc['coupon_seq'].' ';
		}

		$sql .= " FROM (
				SELECT c.*, 
					if( (c.type = 'shipping' OR c.type = 'point' ),SUM(if(d.use_status='used',1,0)),0) as used_cnt,
					if( (c.type = 'shipping' OR c.type = 'point' ),SUM(if(d.use_status='unused',1,0)),0) as unused_cnt,
					SUM(if( (c.type ='order') AND substring(d.regist_date ,1,7) = '".$sc['month']."' ,1,0)) as membermonthsuse_order,
					SUM(if( (c.type = 'memberlogin'  OR c.type = 'memberlogin_shipping' ) AND substring(d.regist_date ,1,7) = '".$sc['month']."' ,1,0)) as membermonthsuse_login,						
					SUM(if( (c.type ='membermonths' OR c.type ='membermonths_shipping') AND substring(d.regist_date ,1,7) = '".$sc['month']."' ,1,0)) as membermonthsuse_months, 
					if( (c.type ='birthday'), DATE_SUB(\"".$members['thisyear_birthday']."\", INTERVAL c.before_birthday DAY), 0) as birthday_beforeday,
					if( (c.type ='birthday'), DATE_ADD(\"".$members['thisyear_birthday']."\", INTERVAL c.after_birthday DAY), 0) as birthday_afterday,
					if( (c.type ='birthday') AND (d.regist_date BETWEEN DATE_SUB(\"".$sc['year']."-01-01\", INTERVAL c.before_birthday DAY) AND DATE_ADD(\"".$sc['year']."-12-31\", INTERVAL c.after_birthday DAY)), 1, 0) as birthday_year,
					if( (c.type ='anniversary'), DATE_SUB(\"".$members['thisyear_anniversary']."\", INTERVAL c.before_anniversary DAY), 0) as anniversary_beforeday,
					if( (c.type ='anniversary'), DATE_ADD(\"".$members['thisyear_anniversary']."\", INTERVAL c.after_anniversary DAY), 0) as anniversary_afterday,
					if( (c.type ='anniversary') AND (d.regist_date BETWEEN DATE_SUB(\"".$sc['year']."-01-01\", INTERVAL c.before_anniversary DAY) AND DATE_ADD(\"".$sc['year']."-12-31\", INTERVAL c.after_anniversary DAY)), 1, 0) as anniversary_year,
					 if( (c.type = 'memberGroup' OR c.type = 'memberGroup_shipping') AND (d.regist_date BETWEEN \"".$members['grade_update_date']."\" AND DATE_ADD(\"".$members['grade_update_date']."\", INTERVAL c.after_upgrade DAY)) AND (d.down_year != '".$members['group_seq']."'), 1, 0) as upgrade_groupday_dk,
					if( (c.type = 'memberGroup' OR c.type = 'memberGroup_shipping'), DATE_ADD(\"".$members['grade_update_date']."\", INTERVAL c.after_upgrade DAY), 0) as upgrade_groupday,
					if(  (c.type = 'memberlogin' ) OR ( c.type = 'memberlogin_shipping' ),
					(
						SELECT count(*)
						 FROM fm_member_order
						 WHERE `member_seq`='".$members['member_seq']."' AND month >= replace(substring(DATE_SUB(\"".$sc['today']."\", INTERVAL c.memberlogin_terms MONTH),1,7),'-','')
					),0) as member_order_cnt,
					if(  (c.type = 'memberlogin' ) OR ( c.type = 'memberlogin_shipping' ),
					(
						replace(substring(DATE_SUB(\"".$sc['today']."\", INTERVAL c.memberlogin_terms MONTH),1,7),'-','')
					),0) as member_order_terms,
					if( (c.type = 'order'),
					(
						SELECT count(*)
						 FROM fm_member_order
						 WHERE `member_seq`='".$members['member_seq']."'
					),0) as member_order_total,
					if(  (c.type = 'memberlogin' ) OR (c.type = 'memberlogin_shipping'),
					(
						SELECT count(*)
						FROM fm_member_order
						WHERE `member_seq`='".$members['member_seq']."'
					),0) as login_member_order_total,
					if( (c.type ='order'), DATE_ADD(\"".$members['regist_date']."\", INTERVAL c.order_terms DAY),0) as order_afterday,
					(
						SELECT count(*)
						 FROM fm_coupon_group
						 WHERE coupon_seq=c.coupon_seq AND `group_seq`='".$members['group_seq']."'
					) as mbgroup_issue_cnt,
					(
						SELECT count(*)
						FROM fm_coupon_group
						WHERE coupon_seq=c.coupon_seq
					) as allgroup_issue_cnt
				FROM fm_coupon c
				 LEFT JOIN fm_download d ON c.coupon_seq = d.coupon_seq AND d.member_seq='".$members['member_seq']."'
					 WHERE
						".$sqltype."
					AND c.issue_stop = 0
				 GROUP BY c.coupon_seq
			) coupon
		WHERE
			(
			(allgroup_issue_cnt =0 AND mbgroup_issue_cnt=0) OR (allgroup_issue_cnt>0 AND mbgroup_issue_cnt>0)
			)
			AND
			( 
				( type = 'birthday' AND birthday_year = 0 AND 
					( 
					( CURDATE() <= birthday_afterday AND CURDATE() BETWEEN birthday_beforeday AND birthday_afterday ) 
				OR
					( CURDATE() > birthday_afterday AND (CURDATE() BETWEEN DATE_ADD(birthday_beforeday, INTERVAL 1 YEAR) AND DATE_ADD(birthday_afterday, INTERVAL 1 YEAR)) ) 
					)
				) OR
				( 
					type = 'anniversary' AND anniversary_year = 0  AND 
					( 
					(CURDATE() <= anniversary_afterday AND CURDATE() BETWEEN anniversary_beforeday AND anniversary_afterday ) 
				OR
					(CURDATE() > anniversary_afterday AND (CURDATE() BETWEEN DATE_ADD(anniversary_beforeday, INTERVAL 1 YEAR) AND DATE_ADD(anniversary_afterday, INTERVAL 1 YEAR)) ) 
					)
				) OR
				(
					(type = 'shipping' OR type = 'point' ) AND(( used_cnt > 0 AND duplication_use = 1 AND unused_cnt=0 ) OR  (used_cnt = 0 AND unused_cnt=0))
				) OR
				( 
					( type = 'memberGroup' OR type = 'memberGroup_shipping' )  AND CURDATE() <= upgrade_groupday AND upgrade_groupday_dk = 0 
				) OR
				(
					((type = 'memberlogin' ) OR ( type = 'memberlogin_shipping' ) ) AND 
					( 
						( membermonthsuse_login = 0 ) AND (login_member_order_total > 0) AND (member_order_cnt <= 0) 
					)
				) OR
				(
					(  (type = 'membermonths' ) OR (type = 'membermonths_shipping') ) AND  membermonthsuse_months = 0
				) OR
				(
					( (type = 'order') ) AND ( ( membermonthsuse_order = 0 ) AND ( member_order_total = 0 ) AND ( CURDATE() > order_afterday ) )
				)
			)
			";
		$sql.=" order by coupon.coupon_seq desc ";
		if( $totalcnt != 'totalcnt' && ($sc['perpage']) ) { 
			$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		}
		
		$query = $this->db->query($sql); 
		//debug_var($this->db->last_query());//

		if( $totalcnt == 'totalcnt' ) {//다운가능 총갯수 추출에만
			$datatotalcnt = ($query)?$query->row_array():''; 
			$data['count'] = $datatotalcnt['cnt']; 
		}else{
			$data['result'] = ($query)?$query->result_array():'';
			
			if( $totalcnt != 'couponall' ) {//다운가능 총갯수 추출에만
				//총건수
				$query = "SELECT FOUND_ROWS() as COUNT";
				$query_count = $this->db->query($query);
				$res_count= ($query)?$query_count->result_array():'';
				$data['count'] = $res_count[0]['COUNT']; 
			}
		}

		return $data;
	}
	//쿠폰 새창/이벤트페이지 전체 노출
	public function get_promotion_coupon_download($sc, $totalcnt=null)
	{
		$sqltype = "";
		
		if( !empty($sc['coupon_seq']) )
		{
			$sqltype .= ' and coupon_seq = '.$sc['coupon_seq'].' ';
		}

		if($sc['coupon_type'] ) {
			$coupon_typein = @implode("','",$sc['coupon_type']);
			$sqltype .= " AND type IN ('{$coupon_typein}') "; 
		}

		if( $sc['coupon_popup_use'] ) {//팝업제공여부
			//$sqltype .= " AND coupon_popup_use = '".$sc['coupon_popup_use']."' ";
		}

		if( $coupon_typein == 'shipping' ) {
			$coupon_shipping_query = " OR ( type='shipping'
									AND (
										(download_startdate is null  AND download_enddate is null )
										OR
										(download_startdate <='".date('Y-m-d H:i:s',time())."' AND download_enddate >='".date('Y-m-d H:i:s',time())."')
									)
									AND (
										(download_starttime is null  AND download_endtime is null )
										OR
										(download_starttime <='".date('H:i',time())."' AND download_endtime >= '".date('H:i',time())."')
									)
									) ";
		}
		$sql = "SELECT * FROM fm_coupon
				 WHERE
					(
						( type not in ('admin','admin_shipping','point','offline_coupon','offline_emon') ) 
						".$sqltype."
						".$coupon_shipping_query."
					)
					AND issue_stop = 0
		";
		$query = $this->db->query($sql);
		//debug_var($sql); 
		$data['result'] = ($query)?$query->result_array():'';
		return $data;
	}

	//쿠폰 새창/이벤트페이지 전체 노출
	public function get_promotion_coupon_my_download($sc, $totalcnt=null)
	{
		$sqltype = "";
		$members = $this->mdata;
		if( !empty($sc['coupon_seq']) )
		{
			$sqltype .= ' and c.coupon_seq = '.$sc['coupon_seq'].' ';
		}

		if($sc['coupon_type'] ) {
			$coupon_typein = @implode("','",$sc['coupon_type']);
			$sqltype .= " AND c.type IN ('{$coupon_typein}') "; 
		}

		if( $coupon_typein == 'shipping' ) {
			$coupon_shipping_query = " (OR ( type='shipping'
									AND (
										(download_startdate is null  AND download_enddate is null )
										OR
										(download_startdate <='".date('Y-m-d H:i:s',time())."' AND download_enddate >='".date('Y-m-d H:i:s',time())."')
									)
									AND (
										(download_starttime is null  AND download_endtime is null )
										OR
										(download_starttime <='".date('H:i',time())."' AND download_endtime >= '".date('H:i',time())."')
									)
									)) ";
		}
		$sql = "SELECT * 
				FROM (
					SELECT c.*,
						if( (c.type ='birthday'), DATE_SUB(\"".$members['thisyear_birthday']."\", INTERVAL c.before_birthday DAY), 0) as birthday_beforeday,
						if( (c.type ='birthday'), DATE_ADD(\"".$members['thisyear_birthday']."\", INTERVAL c.after_birthday DAY), 0) as birthday_afterday,
						if( (c.type ='birthday') AND (d.regist_date BETWEEN DATE_SUB(\"".$sc['year']."-01-01\", INTERVAL c.before_birthday DAY) AND DATE_ADD(\"".$sc['year']."-12-31\", INTERVAL c.after_birthday DAY)), 1, 0) as birthday_year,
						if( (c.type ='anniversary'), DATE_SUB(\"".$members['thisyear_anniversary']."\", INTERVAL c.before_anniversary DAY), 0) as anniversary_beforeday,
						if( (c.type ='anniversary'), DATE_ADD(\"".$members['thisyear_anniversary']."\", INTERVAL c.after_anniversary DAY), 0) as anniversary_afterday,
						if( (c.type ='anniversary') AND (d.regist_date BETWEEN DATE_SUB(\"".$sc['year']."-01-01\", INTERVAL c.before_anniversary DAY) AND DATE_ADD(\"".$sc['year']."-12-31\", INTERVAL c.after_anniversary DAY)), 1, 0) as anniversary_year,
						 if( (c.type = 'memberGroup' OR c.type = 'memberGroup_shipping') AND (d.regist_date BETWEEN \"".$members['grade_update_date']."\" AND DATE_ADD(\"".$members['grade_update_date']."\", INTERVAL c.after_upgrade DAY)) AND (d.down_year != '".$members['group_seq']."'), 1, 0) as upgrade_groupday_dk,
						if( (c.type = 'memberGroup' OR c.type = 'memberGroup_shipping'), DATE_ADD(\"".$members['grade_update_date']."\", INTERVAL c.after_upgrade DAY), 0) as upgrade_groupday
						 FROM fm_coupon c
							 LEFT JOIN fm_download d ON c.coupon_seq = d.coupon_seq AND d.member_seq='".$members['member_seq']."'
						 WHERE
						 c.issue_stop = 0
						".$sqltype."
						GROUP BY c.coupon_seq
					) coupon
				 WHERE
					issue_stop = 0
						".$coupon_shipping_query."
					AND
					(
						( type = 'birthday' AND birthday_year = 0 AND 
							( 
							( CURDATE() <= birthday_afterday AND CURDATE() BETWEEN birthday_beforeday AND birthday_afterday ) 
							OR 
							( CURDATE() > birthday_afterday AND (CURDATE() BETWEEN DATE_ADD(birthday_beforeday, INTERVAL 1 YEAR) AND DATE_ADD(birthday_afterday, INTERVAL 1 YEAR)) ) 
							)
						) OR
						( 
							type = 'anniversary' AND anniversary_year = 0  AND 
							( 
							(CURDATE() <= anniversary_afterday AND CURDATE() BETWEEN anniversary_beforeday AND anniversary_afterday ) 
							OR 
							(CURDATE() > anniversary_afterday AND (CURDATE() BETWEEN DATE_ADD(anniversary_beforeday, INTERVAL 1 YEAR) AND DATE_ADD(anniversary_afterday, INTERVAL 1 YEAR)) ) 
							)
						)
					)
		";
		$query = $this->db->query($sql);
		//debug_var($sql); 
		$data['result'] = ($query)?$query->result_array():'';
		return $data;
	}

	/* 관리자의 발급내역 > 총 할인금액추출*/
	public function get_coupontotal($sc, $coupons)
	{

		if(isset($sc['member_seq'])) $addsql.= ' and m.member_seq ='.$sc['member_seq'];//회원

		if( !empty($sc['search_text']) )
		{
			$addsql .= ' and ( m.user_name like "%'.$sc['search_text'].'%" or m.userid  like "%'.$sc['search_text'].'%") ';//
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

		if ( ( $coupons['type'] == 'shipping' || strstr($coupons['type'],'_shipping') ) ) {//배송비할인
			$totalsalequery = "select sum(ord.coupon_sale) as coupon_sale
				from fm_download down
				left join fm_order ord on ord.download_seq = down.download_seq
				left join fm_member m on m.member_seq = ord.member_seq
				WHERE ord.step not in ('0','85','95','99') and  down.coupon_seq='".$sc[no]."'".$addsql;
			$coupon_sale_query = $this->db->query($totalsalequery);
			$coupon_sale = $coupon_sale_query->row_array();
			$coupon_sale['coupon_sale'] = ($coupon_sale['coupon_sale'])?$coupon_sale['coupon_sale']:0;
		}else{
			$totalsalequery = "select sum(o.coupon_sale) as coupon_sale
				from fm_download down
				left join fm_order_item_option o on o.download_seq = down.download_seq
				left join fm_order ord on ord.order_seq = o.order_seq
				left join fm_member m on m.member_seq = ord.member_seq
				WHERE ord.step not in ('0','85','95','99') and  down.coupon_seq = '".$sc[no]."'".$addsql;
			$coupon_sale_query = $this->db->query($totalsalequery);
			$coupon_sale = $coupon_sale_query->row_array();
			$coupon_sale['coupon_sale'] = ($coupon_sale['coupon_sale'])?$coupon_sale['coupon_sale']:0;
		}
		return $coupon_sale;
	}

	/**
	*@ 마이페이지의 > 개별다운시 체크용
	* 생일자 : 생일전 ~ 생일후 기간
	* 기념일 : 기념일전 ~ 기념일후 기간
	* 등록 : 등급조정 이후기간
	**/
	public function get_my_download_member($couponSeq,$members)
	{
		$sql = "SELECT c.*,
		if( (c.type ='birthday'), DATE_SUB(\"".$members['thisyear_birthday']."\", INTERVAL c.before_birthday DAY), 0) as birthday_beforeday,
		if( (c.type ='birthday'), DATE_ADD(\"".$members['thisyear_birthday']."\", INTERVAL c.after_birthday DAY), 0) as birthday_afterday,
		if( (c.type ='anniversary'), DATE_SUB(\"".$members['thisyear_anniversary']."\", INTERVAL c.before_anniversary DAY), 0) as anniversary_beforeday,
		if( (c.type ='anniversary'), DATE_ADD(\"".$members['thisyear_anniversary']."\", INTERVAL c.after_anniversary DAY), 0) as anniversary_afterday,
		if( (c.type = 'memberGroup' OR c.type = 'memberGroup_shipping'), DATE_ADD(\"".$members['grade_update_date']."\", INTERVAL c.after_upgrade DAY), 0) as upgrade_groupday,
		if(  (c.type = 'memberlogin' ) OR ( c.type = 'memberlogin_shipping' ),
		(
			SELECT count(*)
			FROM fm_member_order
			WHERE `member_seq`='".$members['member_seq']."' AND month >= substring(DATE_SUB(\"".$sc['today']."\", INTERVAL c.memberlogin_terms MONTH),1,7)
		),0) as member_order_cnt, 
		if(  (c.type = 'memberlogin' ) OR (c.type = 'memberlogin_shipping') OR (c.type = 'order'),
		(
			SELECT count(*)
			FROM fm_member_order
			WHERE `member_seq`='".$members['member_seq']."'
		),0) as member_order_total,
		if( (c.type ='birthday'), DATE_ADD(\"".$members['regist_date']."\", INTERVAL c.order_terms DAY),0) as order_afterday,
		(
			SELECT count(*)
			FROM fm_coupon_group
			WHERE coupon_seq=c.coupon_seq AND `group_seq`='".$members['group_seq']."'
		) as mbgroup_issue_cnt,
		(
			SELECT count(*)
			FROM fm_coupon_group
			WHERE coupon_seq=c.coupon_seq
		) as allgroup_issue_cnt
		FROM fm_coupon c
		WHERE coupon_seq = ".$couponSeq." ";
		$query = $this->db->query($sql);
		$result = $query->row_array();
		return $result;
	}

}
/* End of file couponmodel.php */
/* Location: ./app/models/couponmodel.php */