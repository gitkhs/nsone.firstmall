<?php
class ordermodel extends CI_Model {
	public function __construct()
	{
		// 주문상세 버튼 활성화 상태 정의
		$action['order_deposit'] 		= array('15'); // 입금확인
		$action['goods_ready'] 			= array('25', '40', '50', '60', '70');  // 상품준비
		$action['goods_export'] 		= array('25','35','40','50','60','70'); // 출고처리
		$action['cancel_order'] 		= array('15');  // 주문무효
		$action['cancel_payment']		= array('25','35','40','50','60','70'); // 결제취소
		$action['cancel_payment_etc']	= array('25','35'); // 결제취소 (기타)
		$action['return_list']			= array('55','60','65','70','75'); // 반품신청
		$action['return_coupon_list']	= array('55','60','65','70','75'); // 쿠폰상품 환불신청
		$action['exchange_list']		= array('55','60','65','70','75'); // 맞교환신청
		$action['return'] 				= array('70','75'); // 반품처리
		$action['enuri'] 				= array('15'); // 에누리
		$action['change_bank']			= array('15'); // 무통장정보변경
		$action['shipping_region']		= array('15','25','35','40','45'); // 배송정보변경
		$action['cash_receipts'] 		= array('15','25','35','40','45','50','55','60','65','70','75','85'); // 현금영수증
		$action['tax_bill'] 			= array('15','25','35','40','45','50','55','60','65','70','75','85'); // 세금계산서
		$action['card_slips'] 			= array('25','35','40','45','50','55','60','65','70','75','85'); // 카드전표

		$action['canceltype_cancel_order'] 			= array('15');  //청약철회 > 주문무효 가능상태 : 주문접수
		$action['canceltype_cancel_payment'] 	= array('25','35','40','50','60','70');  //청약철회 > 결제취소 불가: 결제확인 이후
		$action['canceltype_return_order'] 			= array('15');  //청약철회 > 반품/교환/환불 불가

		$action['social_cancel_payment'] 	= array('25','35','40','50','55','60','70','75'); // 쇼셜쿠폰 결제취소
		$this->able_step_action 	= $action;
	}

	/* 사용자화면 주문리스트 */
	public function get_order_list($sc=array()){

		$this->load->model('returnmodel');
		$this->load->model('refundmodel');

		if(!$this->arr_step)	$this->arr_step = config_load('step');
		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		if(!$this->cfg_order)	$this->cfg_order = config_load('order');

		$sc['page']		= !empty($sc['page'])		? intval($sc['page']):'1';
		$sc['perpage']	= !empty($sc['perpage'])	? intval($sc['perpage']):'10';

		$sqlWhereClause = "";
		$sqlLimitClause = "";

		if(!empty($sc['member_seq'])){
			$sqlWhereClause .= " and ord.member_seq = '{$sc['member_seq']}'";
		}

		// 검색어
		if( $sc['keyword'] ){
			$where[] = "
			(
				(
					order_seq LIKE '%" .  $sc['keyword'] . "%'
				) OR (
					order_seq IN
					(
						SELECT order_seq FROM fm_order_item WHERE goods_name LIKE '%" .  $sc['keyword']. "%'
					)
				)
			)
			";
		}

		if( $sc['regist_date'][0] ){
			$where[] = "regist_date >= '".$sc['regist_date'][0]." 00:00:00'";
		}

		if( $sc['regist_date'][1] ){
			$where[] = "regist_date <= '".$sc['regist_date'][1]." 23:59:59'";
		}

		// 삭제주문 조건 추가
		if( $sc['hidden'] ){
			$where[] = "hidden = '".$sc['hidden']."'";
		}

		if( $sc['step_type'] ){
			switch($sc['step_type']){
				case 'order': $where[] = "step = '15'"; break;
				case 'deposit': $where[] = "step in ('25','35')"; break;
				case 'export': $where[] = "step in ('40','45','50','55','60','65','70')"; break;
				case 'export_and_complete': $where[] = "step in ('40','45','50','55','60','65','70','75')"; break;
			}
		}

		if($where) $sqlWherekeyword = 'where '.implode(' and ',$where);

		$sql = "SELECT * FROM (
			SELECT
			ord.*,
			(
				SELECT count(item_seq) FROM fm_order_item WHERE goods_kind ='goods' and order_seq=ord.order_seq ORDER BY item_seq LIMIT 1
			) goodscnt,
			(
				SELECT count(item_seq) FROM fm_order_item WHERE order_seq=ord.order_seq
			) item_cnt,
			(
				SELECT userid FROM fm_member WHERE member_seq=ord.member_seq
			) userid,
			(
				SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq
			) group_name
			FROM
			fm_order ord
			WHERE ord.step!=0
			{$sqlWhereClause}
		) t {$sqlWherekeyword}

		ORDER BY regist_date DESC
		";
		$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		$result['page']['querystring'] = get_args_list();

		// 상품번호, 상품명, 이미지를 구하기 위해 sub query로 3번 날리던걸 1번으로 변경
		$itemsql	= "select goods_seq, goods_name, image from fm_order_item 
						where order_seq = ? order by item_seq limit 1";
		// 필수옵션 적립금, 포인트 각각 sub query로 2번 날리던걸 1번으로 변경
		$optsql		= "SELECT sum(reserve*ea) as tot_reserve, sum(point*ea) as tot_point
						FROM fm_order_item_option A 
							left join fm_order_item B ON A.item_seq = B.item_seq 
						WHERE B.order_seq = ?";
		// 필수옵션 적립금, 포인트 각각 sub query로 2번 날리던걸 1번으로 변경
		$subsql		= "SELECT sum(reserve*ea) as tot_reserve, sum(point*ea) as tot_point
						FROM fm_order_item_suboption A 
							left join fm_order_item B ON A.item_seq = B.item_seq 
						WHERE B.order_seq = ?";

		foreach($result['record'] as $k => $data)
		{
			$no++;

			// 대표 상품 정보
			$query								= $this->db->query($itemsql, array($data['order_seq']));
			$item_data							= $query->row_array();
			$result['record'][$k]['goods_seq']	= $item_data['goods_seq'];
			$result['record'][$k]['goods_name']	= $item_data['goods_name'];
			$result['record'][$k]['image']		= $item_data['image'];

			// 필수옵션 적립금, 포인트
			$query								= $this->db->query($optsql, array($data['order_seq']));
			$opt_data							= $query->row_array();
			$result['record'][$k]['reserve']	= $opt_data['tot_reserve'];
			$result['record'][$k]['point']		= $opt_data['tot_point'];

			// 추가옵션 적립금, 포인트
			$query								= $this->db->query($subsql, array($data['order_seq']));
			$sub_data							= $query->row_array();
			$result['record'][$k]['subreserve']	= $sub_data['tot_reserve'];
			$result['record'][$k]['subpoint']	= $sub_data['tot_point'];

			$result['record'][$k]['mstep'] = $this->arr_step[$result['record'][$k]['step']];
			$result['record'][$k]['mpayment'] = $this->arr_payment[$result['record'][$k]['payment']];
			$step_cnt[$result['record'][$k]['step']]++;
			$tot_settleprice[$result['record'][$k]['step']] += $result['record'][$k]['settleprice'];
			$tot[$result['record'][$k]['step']][$result['record'][$k]['important']] += $result['record'][$k]['settleprice'];

			$result['record'][$k]['step_cnt'] = $step_cnt;
			$result['record'][$k]['tot_settleprice'] = $tot_settleprice;
			$result['record'][$k]['tot'][$result['record'][$k]['important']] = $tot[$result['record'][$k]['step']][$result['record'][$k]['important']];

			###
			$result['record'][$k]['opt_cnt']	= $this->get_option_count('opt', $result['record'][$k]['order_seq']);
			$result['record'][$k]['gift_cnt']	= $this->get_option_count('gift', $result['record'][$k]['order_seq']);
			$result['record'][$k]['gift_nm']	= $this->get_gift_name($result['record'][$k]['order_seq']);

			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !$result['record'][$k]['image'] || !is_file(ROOTPATH.$result['record'][$k]['image']) ) {
				$result['record'][$k]['image'] = viewImg($result['record'][$k]['goods_seq'],'thumbCart');
			}


			//반품정보 가져오기
			$result['record'][$k]['return_list_ea'] = 0;
			$data_return = $this->returnmodel->get_return_for_order($data['order_seq']);
			if($data_return) foreach($data_return as $row_return){
				$result['record'][$k]['return_list_ea'] += $row_return['ea'];
			}

			//환불정보 가져오기
			$result['record'][$k]['refund_list_ea'] = 0;
			$result['record'][$k]['cancel_list_ea'] = 0;
			$data_refund = $this->refundmodel->get_refund_for_order($data['order_seq']);
			if($data_refund) foreach($data_refund as $row_refund){
				if( $row_refund['refund_type'] == 'cancel_payment' ){
					$result['record'][$k]['cancel_list_ea'] += $row_refund['ea'];
				}else{
					$result['record'][$k]['refund_list_ea'] += $row_refund['ea'];
				}
			}
			$result['record'][$k]['reserve'] = $result['record'][$k]['reserve']+$result['record'][$k]['subreserve'];
			$result['record'][$k]['point'] = $result['record'][$k]['point']+$result['record'][$k]['subpoint'];

			if($step_cnt[$result['record'][$k]['step']] == 1)
			{
				$result['record'][$k]['start'] = true;
				$ek = $k-1;
				if($ek >= 0 ){
					$result['record'][$ek]['end'] = true;
				}
			}
		}

		if($result['record'])
		{
			$result['record'][$k]['end'] = true;
			foreach($result['record'] as $k => $data){
				$result['record'][$k]['no'] = $no;
				$no--;
			}
		}


		return $result;
	}


	public function get_option_count($type='opt', $order_seq){
		if($type=='opt'){
			$sql = "select count(item_seq) as cnt from fm_order_item A left join fm_goods B on A.goods_seq = B.goods_seq where A.order_seq = '{$order_seq}'";
		}else{
			$sql = "select count(item_seq) as cnt from fm_order_item A left join fm_goods B on A.goods_seq = B.goods_seq where A.order_seq = '{$order_seq}' AND B.goods_type = 'gift'";
		}
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return $data[0]['cnt'];
	}

	public function get_gift_name($order_seq){
		$sql = "select B.goods_name from fm_order_item A left join fm_goods B on A.goods_seq = B.goods_seq where A.order_seq = '{$order_seq}' AND B.goods_type = 'gift'";

		$query = $this->db->query($sql);
		$gift_nm = "";
		foreach($query->result_array() as $k){
			$temp[] = $k['goods_name'];
		}
		$gift_nm = @implode(", ", $temp);
		return $gift_nm;
	}


	public function get_order_seq()
	{
		$query = "select regist_date from fm_order_sequence where regist_date=? limit 1";
		$query = $this->db->query($query,array(date('Y-m-d')));
		$data = $query -> result_array();
		if( !$data[0]['regist_date'] ){
			$query = "truncate table fm_order_sequence";
			$this->db->query($query);
			$query = "alter table fm_order_sequence auto_increment = 17530";
			$this->db->query($query);
		}

		$insert_params['regist_date'] 	= date('Y-m-d');
		$this->db->insert('fm_order_sequence', $insert_params);
		return date('YmdHis').$this->db->insert_id();
	}

	/* 주문 생성시 단일배송데이터 생성 */
	public function insert_single_shipping($order_seq, $shipping_cost=0, $area_add_delivery_cost=0){
		$this->db->query("delete from fm_order_shipping where order_seq=?",$order_seq);
		$this->db->query("delete from fm_order_shipping_item where order_seq=?",$order_seq);
		$this->db->query("delete from fm_order_shipping_item_option where order_seq=?",$order_seq);

		$multi_insert_params = array();
		$multi_insert_params['order_seq']					= $order_seq;
		$multi_insert_params['regist_date']					= date('Y-m-d H:i:s');
		$multi_insert_params['recipient_user_name']			= $_POST['recipient_user_name'];
		$multi_insert_params['recipient_phone'] 			= (string)$_POST['recipient_phone'][0].'-'.$_POST['recipient_phone'][1].'-'.$_POST['recipient_phone'][2];
		$multi_insert_params['recipient_cellphone'] 		= (string)$_POST['recipient_cellphone'][0].'-'.$_POST['recipient_cellphone'][1].'-'.$_POST['recipient_cellphone'][2];
		$multi_insert_params['recipient_zipcode'] 			= $_POST['recipient_zipcode'][0].'-'.$_POST['recipient_zipcode'][1];
		$multi_insert_params['recipient_address_type']		= ($_POST['recipient_address_type'])?$_POST['recipient_address_type']:"zibun";
		$multi_insert_params['recipient_address'] 				= $_POST['recipient_address'];
		$multi_insert_params['recipient_address_street'] 	= $_POST['recipient_address_street'];
		$multi_insert_params['recipient_address_detail'] 	= $_POST['recipient_address_detail'];
		if($_POST['international'] == '1'){
			$multi_insert_params['region'] 						= $_POST['region'];
			$multi_insert_params['international_address'] 		= $_POST['international_address'];
			$multi_insert_params['international_town_city'] 		= $_POST['international_town_city'];
			$multi_insert_params['international_county'] 			= $_POST['international_county'];
			$multi_insert_params['international_postcode'] 		= $_POST['international_postcode'];
			$multi_insert_params['international_country'] 		= $_POST['international_country'];
			if( $_POST['international_recipient_phone'] )
				$multi_insert_params['recipient_phone'] = implode('-',$_POST['international_recipient_phone']);
			if( $_POST['international_recipient_cellphone'] )
				$multi_insert_params['recipient_cellphone'] = implode('-',$_POST['international_recipient_cellphone']);
		}
		$multi_insert_params['recipient_email'] 			= $_POST['recipient_email'];
		$multi_insert_params['memo'] = '';
		$multi_insert_params['shipping_cost'] 					= $shipping_cost;
		$multi_insert_params['shipping_promotion_code_sale'] 	= $_POST['shipping_promotion_code_sale'];
		if(!preg_match('/이 곳은 집배원님이 보시는 메시지란입니다/',$_POST['memo']) && !preg_match('/이곳은 택배기사님이 확인하는 메세지란입니다/',$_POST['memo'])) $multi_insert_params['memo'] = (string)$_POST['memo'];
		$multi_insert_params['area_add_delivery_cost']		= $area_add_delivery_cost;
		$this->db->insert('fm_order_shipping', $multi_insert_params);
		$shipping_seq = $this->db->insert_id();

		return $shipping_seq;
	}

	/* 주문 생성 */
	public function insert_order($settle_price, $shipping_cost, $shipping=null/*몰인몰에서는 사용안함*/, $freeprice=0,$postpaid=0)
	{		
		$mode		= "cart";
		$emoney_use = "none";
		$order_seq	= $this->get_order_seq();
		$policy = $shipping ? $shipping[0][0] : array();
		if( isset($_GET['mode']) ) $mode = $_GET['mode'];
		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']) $member_seq = $this->userInfo['member_seq'];

		if($_POST['adminOrder'] == 'admin'){
			$member_seq=$_POST['member_seq'];
		}

		// 카드, 휴대폰일경우 매출전표만 출력가능하게 수정
		if($_POST['payment'] == 'card' || $_POST['payment'] == 'cellphone' ) $_POST['typereceipt'] = 0;

		$insert_params['order_seq'] 					= $order_seq;
		$insert_params['person_seq'] 					= $_POST['person_seq'];
		$insert_params['original_settleprice'] 			= $settle_price+(int)$_POST['enuri'];
		$insert_params['settleprice'] 					= $settle_price;
		$insert_params['mode'] 							= $mode;
		$insert_params['payment'] 						= $_POST['payment'];
		$insert_params['step'] 							= '0';
		$insert_params['deposit_yn'] 					= "n";
		$insert_params['depositor'] 					= $_POST['depositor'];
		$insert_params['bank_account'] 					= $_POST['bank'];
		$insert_params['emoney_use'] 					= $emoney_use;
		$insert_params['emoney'] 						= (int)$_POST['emoney'];
		$insert_params['cash_use'] 						= 'none';
		$insert_params['cash'] 							= (int)$_POST['cash'];
		$insert_params['enuri'] 						= (int)$_POST['enuri'];
		$insert_params['member_seq']					= $member_seq;
		$insert_params['order_user_name'] 				= $_POST['order_user_name'];
		$insert_params['order_phone'] 					= implode('-',$_POST['order_phone']);
		$insert_params['order_cellphone'] 				= implode('-',$_POST['order_cellphone']);
		$insert_params['order_email'] 					= $_POST['order_email'];
		//$insert_params['recipient_user_name']			= $_POST['recipient_user_name'];
		//$insert_params['recipient_phone'] 				= implode('-',$_POST['recipient_phone']);
		//$insert_params['recipient_cellphone'] 			= implode('-',$_POST['recipient_cellphone']);
		$insert_params['freeprice'] 					= $freeprice;
		if($_POST['international'] == '1'){
			$insert_params['international'] 				= 'international';
			$insert_params['shipping_method_international'] = $_POST['shipping_method_international'];
			$insert_params['international_cost']			= $shipping_cost;

			// 주문서에 기본 배송정책 저장
			$insert_params['delivery_cost'] 				= $shipping_cost;
		}else{
			$insert_params['international'] 				= 'domestic';
			$insert_params['shipping_method'] 				= $_POST['shipping_method'];

			// 주문서에 기본 배송정책 저장
			if($policy['deliveryCostPolicy'] == 'ifpay'){
				$insert_params['delivery_if'] = $policy['ifpayFreePrice'];
				$insert_params['delivery_cost'] = $policy['ifpayDeliveryCost'];
			}
			if($policy['deliveryCostPolicy'] == 'pay'){
				$insert_params['delivery_cost'] = $policy['payDeliveryCost'];
			}

			//$insert_params['recipient_zipcode'] 			= implode('-',$_POST['recipient_zipcode']);
			//$insert_params['recipient_address'] 			= $_POST['recipient_address'];
			//$insert_params['recipient_address_detail'] 		= $_POST['recipient_address_detail'];
			$insert_params['shipping_cost']					= $shipping_cost;

			$insert_params['postpaid']					= $postpaid;
		}

		//쇼셜쿠폰 받는사람이메일추가@2013-10-22
		$insert_params['recipient_email'] 					= ($_POST['recipient_email'])?$_POST['recipient_email']:$_POST['order_email'];

		//$insert_params['memo'] = "";
		if(!preg_match('/이 곳은 집배원님이 보시는 메시지란입니다/',$_POST['memo']) && !preg_match('/이곳은 택배기사님이 확인하는 메세지란입니다/',$_POST['memo'])) $insert_params['memo'] = (string)$_POST['memo'];
		$insert_params['download_seq'] 					= $_POST['download_seq'];
		$insert_params['coupon_sale'] 					= $_POST['coupon_sale'];

		$insert_params['shipping_promotion_code_seq'] 					= $_POST['shipping_promotion_code_seq'];
		$insert_params['shipping_promotion_code_sale'] 					= $_POST['shipping_promotion_code_sale'];

		$insert_params['typereceipt'] 						= $_POST['typereceipt'];
		$insert_params['regist_date'] 					= date('Y-m-d H:i:s',time());
		$insert_params['session_id'] 					= $session_id;

		if($_POST["adminOrder"] == "admin"){

			$insert_params['admin_order'] 				= $this->session->userdata["manager"]["manager_id"];

		}

		//판매환경
		if($this->_is_mobile_agent) {//mobile
			$insert_params['sitetype'] 				= 'M';
		}elseif($this->fammerceMode || $this->storefammerceMode) {//fammerce
			$insert_params['sitetype'] 				= 'F';
		}else{
			$insert_params['sitetype'] 				= 'P';
		}

		//스킨환경
		if($this->mobileMode) {//mobile 1
			$insert_params['skintype'] 				= 'M';
		}elseif($this->fammerceMode) {//fammerce 3
			$insert_params['skintype'] 				= 'F';
		}elseif($this->storemobileMode) {//mobile 2
			$insert_params['skintype'] 				= 'OFF_M';
		}elseif($this->storefammerceMode) {//fammerce 4
			$insert_params['skintype'] 				= 'OFF_F';
		}elseif($this->storeMode) {//pc 5
			$insert_params['skintype'] 				= 'OFF_P';
		}else{//pc 6
			$insert_params['skintype'] 				= 'P';
		}

		$insert_params['marketplace'] 				= $_COOKIE['marketplace'];//유입매체
		## 유입경로
		$insert_params['referer']					= $this->session->userdata('shopReferer');
		$insert_params['referer_domain']			= $this->session->userdata('refererDomain');

		## 고객리마인드서비스 유입경로 2014-07-31, 유입로그는 결제확인 시 저장
		if($_COOKIE["curation"]){
			$curation_tmp		= explode("^",$_COOKIE["curation"]);
			$curation_inflow	= $curation_tmp[1];
			$curation_seq		= $curation_tmp[2];
			$insert_params['curation_inflow']	= $curation_inflow;		//고객리마인드 유입구분
			$insert_params['curation_seq']		= $curation_seq;		//고객리마인드 유입로그번호
		}

		// 다중배송지
		if($_POST['multiShippingChk']){
			$insert_params['shipping_method'] 	= 'delivery';
		}
		## sns 로그인 계정(2014-07-02)
		if($_POST['adminOrder'] != 'admin'){
			$snslogn = $this->session->userdata('snslogn');
			if($snslogn) $insert_params['sns_rute'] 	= $snslogn;
		}

		// 개인 결제 주문일 경우 관리자 메모가 있을 때 저장되게 함 leewh 2014-12-01
		if (!empty($_POST['person_seq']) && !empty($_POST['admin_memo'])) {
			$insert_params['admin_memo'] 			= $_POST['admin_memo'];
		}

		$this->db->insert('fm_order', $insert_params);

		return $order_seq;
	}

	// 주문 총주문수량 / 총상품종류 업데이트 leewh 2014-08-01
	public function update_order_total_info($order_seq) {
		$query = "
		UPDATE fm_order O
		INNER JOIN
		(
			SELECT ord.order_seq,
			(SELECT count(item_seq) FROM fm_order_item WHERE order_seq=ord.order_seq) item_cnt,
			(SELECT IFNULL(SUM(ea), 0) FROM fm_order_item_option WHERE order_seq=ord.order_seq) opt_ea,
			(SELECT IFNULL(SUM(ea), 0) FROM fm_order_item_suboption WHERE order_seq=ord.order_seq) sub_ea
			FROM
			fm_order ord
			WHERE ord.order_seq=?
		) T ON O.order_seq = T.order_seq
		SET O.total_ea = T.opt_ea+T.sub_ea, O.total_type = T.item_cnt WHERE O.order_seq=?";

		$this->db->query($query,array($order_seq,$order_seq));
	}

	// 주문 적립금 상태 변경
	public function set_emoney_use($order_seq,$status){
		$this->db->where('order_seq',$order_seq);
		$this->db->update('fm_order',array('emoney_use'=>$status));
	}
	public function set_cash_use($order_seq,$status){
		$this->db->where('order_seq',$order_seq);
		$this->db->update('fm_order',array('cash_use'=>$status));
	}

	// 주문서 정보 가져오기
	public function get_order($order_seq, $wheres=array()){

		$binds = array($order_seq);

		$sql = "select a.*,
		ifnull(b.recipient_user_name,a.recipient_user_name) as recipient_user_name,
		ifnull(b.recipient_phone,a.recipient_phone) as recipient_phone,
		ifnull(b.recipient_cellphone,a.recipient_cellphone) as recipient_cellphone,
		ifnull(b.recipient_zipcode,a.recipient_zipcode) as recipient_zipcode,
		ifnull(b.recipient_address_type,a.recipient_address_type) as recipient_address_type,
		ifnull(b.recipient_address,a.recipient_address) as recipient_address,
		ifnull(b.recipient_address_street,a.recipient_address_street) as recipient_address_street,
		ifnull(b.recipient_address_detail,a.recipient_address_detail) as recipient_address_detail,
		ifnull(b.region,a.region) as region,
		ifnull(b.international_address,a.international_address) as international_address,
		ifnull(b.international_town_city,a.international_town_city) as international_town_city,
		ifnull(b.international_county,a.international_county) as international_county,
		ifnull(b.international_postcode,a.international_postcode) as international_postcode,
		ifnull(b.international_country,a.international_country) as international_country,
		ifnull(b.memo,a.memo) as memo,
		a.referer, a.referer_domain, c.referer_group_cd,
		IF(c.referer_group_no>0, c.referer_group_name, IF(LENGTH(a.referer)>0,'기타','직접입력')) as referer_name,
		(case when ifnull(a.sns_rute,'')!='' then 
			ifnull((select (case when ifnull(user_name,'')!='' then user_name else email end) from fm_membersns where member_seq=a.member_seq and rute=a.sns_rute),'disconnect') 
		else '' end) as sns_user
		from fm_order a
		left join fm_order_shipping b on a.order_seq=b.order_seq
		LEFT JOIN fm_referer_group c on a.referer_domain = c.referer_group_url 
		where a.order_seq=?
		";
		if($wheres) {
			foreach($wheres as $k=>$v){
				$sql .= " and {$k}=?";
				$binds[] = $v;
			}
		}

		$sql .= " group by a.order_seq";

		$query = $this->db->query($sql,$binds);
		list($orders) = $query->result_array($query);

		if(!$this->arr_payment)	$this->arr_payment = config_load('payment');
		$orders['mpayment'] = $this->arr_payment[$orders['payment']];

		if($orders['pg']=='kakaopay')
			$orders['mpayment'] = '카카오페이';

		return $orders;
	}

	// 주문서 상품 정보 가져오기
	public function get_item($order_seq){
		$query = "
		SELECT
		A.*,
		(select goods_type from fm_goods where goods_seq = A.goods_seq) as goods_type,
		(select cancel_type from fm_goods where goods_seq = A.goods_seq) as cancel_type,
		ifnull((select sum(goods_shipping_cost) from fm_order_shipping_item where order_item_seq=A.item_seq),0) as goods_shipping_cost_ori, 
		ifnull((select sum(add_goods_shipping) from fm_order_shipping_item where order_item_seq=A.item_seq),0) as add_goods_shipping, 
		ifnull((select sum(goods_shipping_cost) from fm_order_shipping_item where order_item_seq=A.item_seq),0)+ifnull((select sum(add_goods_shipping) from fm_order_shipping_item where order_item_seq=A.item_seq),0) as goods_shipping_cost 
		FROM fm_order_item A
		WHERE A.order_seq=?";
		$binds = array($order_seq);
		$query = $this->db->query($query,$binds);
		foreach($query->result_array() as $data){
			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !$data['image'] || !is_file(ROOTPATH.$data['image']) ) {
				$data['image'] = viewImg($data['goods_seq'],'thumbCart');
			}
			$items[] = $data;
		}
		return $items;
	}

	//배송지정보
	public function get_order_shipping($shipping_seq){
		$query = $this->db->query("select * from fm_order_shipping where shipping_seq=?",$shipping_seq);
		return $query->row_array();
	}

	// 주문서 상품 정보, 배송지별로 가져오기
	public function get_item_by_shipping($order_seq){
		$query = "
		SELECT
		a.*,
		b.ea as ea,
		b.shipping_seq
		FROM fm_order_item a
		INNER JOIN fm_order_shipping_item_option b on (a.order_seq=b.order_seq and a.item_seq=b.order_item_seq)
		INNER JOIN fm_order_shipping c on b.shipping_seq=c.shipping_seq
		WHERE a.order_seq=?
		GROUP BY b.shipping_seq,b.order_item_seq
		";
		$query = $this->db->query($query,array($order_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	// 주문서 상품 정보 가져오기
	public function get_gift_item($order_seq){
		$query = "
		SELECT count(*) as cnt
		FROM fm_order_item A LEFT JOIN fm_goods B ON A.goods_seq = B.goods_seq
		WHERE order_seq=? and B.goods_type <> 'gift'";
		$query = $this->db->query($query,array($order_seq));

		list($result) = $query->result_array($query);

		return $result['cnt'];
	}

	public function get_option_for_order($order_seq, $where = array()){
		$query = "
		SELECT
		*
		FROM fm_order_item_option
		WHERE order_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		$query = $this->db->query($query,array($order_seq));

		foreach($query->result_array() as $data){
			$items[] = $data;
		}

		return $items;
	}

	public function get_suboption_for_order($order_seq, $where = array()){
		$query = "
		SELECT
		*
		FROM fm_order_item_suboption
		WHERE order_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		$query = $this->db->query($query,array($order_seq));

		foreach($query->result_array() as $data){
			$items[] = $data;
		}

		return $items;
	}



	public function get_option_for_item($item_seq, $where = array()){
		$query = "
		SELECT
		*
		FROM fm_order_item_option
		WHERE item_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		$query = $this->db->query($query,array($item_seq));

		foreach($query->result_array() as $data){
			$items[] = $data;
		}

		return $items;
	}

	// 배송지별
	public function get_option_for_item_by_shipping($item_seq, $shipping_seq, $where = array()){
		$query = "
		SELECT
		a.*,
		b.ea as ea
		FROM fm_order_item_option a
		INNER JOIN fm_order_shipping_item_option b on (a.order_seq=b.order_seq and a.item_seq=b.order_item_seq and a.item_option_seq=b.order_item_option_seq)
		WHERE a.item_seq=? and b.shipping_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		$query = $this->db->query($query,array($item_seq,$shipping_seq));

		foreach($query->result_array() as $data){
			$items[] = $data;
		}

		return $items;
	}

	public function get_suboption_for_item($item_seq, $where = array()){
		$query = "
		SELECT
		*
		FROM fm_order_item_suboption
		WHERE item_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);
		$query .= " order by item_suboption_seq desc";

		$query = $this->db->query($query,array($item_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	// 배송지별
	public function get_suboption_for_item_by_shipping($item_seq, $shipping_seq, $where = array()){
		$query = "
		SELECT
		a.*,
		b.ea as ea
		FROM fm_order_item_suboption a
		INNER JOIN fm_order_shipping_item_option b on (a.order_seq=b.order_seq and a.item_seq=b.order_item_seq and a.item_suboption_seq=b.order_item_suboption_seq)
		WHERE a.item_seq=? and b.shipping_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		$query = $this->db->query($query,array($item_seq,$shipping_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	// 배송지별
	public function get_suboption_for_option_by_shipping($item_seq, $item_option_seq, $shipping_seq, $where = array()){
		$query = "
		SELECT
		a.*,
		b.ea as ea
		FROM fm_order_item_suboption a
		INNER JOIN fm_order_shipping_item_option b on (a.order_seq=b.order_seq and a.item_seq=b.order_item_seq and a.item_suboption_seq=b.order_item_suboption_seq)
		WHERE a.item_seq=? and a.item_option_seq=? and b.shipping_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		$query = $this->db->query($query,array($item_seq,$item_option_seq,$shipping_seq));
		foreach($query->result_array() as $data){
			$data['step85'] = $this->refundmodel->get_refund_suboption_ea($shipping_seq,$data['item_seq'],$data['item_suboption_seq']);
			$items[] = $data;
		}
		return $items;
	}

	public function get_input_for_item($item_seq){
		$query = "
		SELECT
		*, title as subinputtitle, value as subinputoption
		FROM fm_order_item_input
		WHERE item_seq=?";
		$query = $this->db->query($query,array($item_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	public function get_suboption_for_option($item_seq, $option_seq, $where = array()){
		$query = "
		SELECT
		*
		FROM fm_order_item_suboption
		WHERE item_seq=? and item_option_seq=?";

		if($where) $query .= " and " . implode(" and ",$where);

		//$query .= " order by item_suboption_seq desc";
		$query .= " order by item_suboption_seq";

		$query = $this->db->query($query,array($item_seq, $option_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	public function get_input_for_option($item_seq, $option_seq){
		$query = "
		SELECT
		*, title as subinputtitle,
		value as subinputoption
		FROM fm_order_item_input
		WHERE item_seq=? and item_option_seq=?";
		$query = $this->db->query($query,array($item_seq, $option_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	// 주문 상품과 주문서의 주문 상태 업데이트
	public function set_step($order_seq,$step,$arr=''){
		$data['step'] = $step;

		if($step=='25'){ // 입금확인
			$data['deposit_yn'] = 'y';
			$data['deposit_date'] = date('Y-m-d H:i:s');

			/* 상품분석 수집 */
			$this->load->model('goodslog');
			$query = $this->db->query("SELECT a.goods_seq, (select sum(price) from fm_order_item_option where order_seq=a.order_seq) as option_price_sum, (select sum(price) from fm_order_item_suboption where order_seq=a.order_seq) as suboption_price_sum  FROM `fm_order_item` as a WHERE a.order_seq=?",$order_seq);
			$items = $query->result_array();
			foreach($items as $item){
				$this->goodslog->add('deposit',$item['goods_seq']);
				$this->goodslog->add('deposit_price',$item['goods_seq'],$item['option_price_sum']+$item['suboption_price_sum']);
			}

			// 네이버 마일리지
			$this->load->model('navermileagemodel');
			$this->navermileagemodel->batch_regist('confirm',$order_seq);
		}

		if($step=='95'){ // 주문무효
			$data['deposit_yn'] = 'n';
		}

		if($arr) $data = array_merge($data,$arr);
		foreach($data as $field => $val){
			$r_field[] = $field."=?";
			$bind[] = $val;
		}

		$where[] = "order_seq = ?";
		$bind[] = $order_seq;

		// 출고 이후 건은 입금확인 처리 불가
		if($step=='25'){
			$where[] = "(step < ? OR step > ?)";
			$bind[] = '25';
			$bind[] = '75';
		}

		$this->db->query('update fm_order set '.implode(',',$r_field).' where '.implode(' and ',$where),$bind);
		
		$query = "update fm_order_item_option set step=? where order_seq=? and ea!=step85";
		$this->db->query($query,array($step,$order_seq));
		$query = "update fm_order_item_suboption set step=? where order_seq=? and ea!=step85";
		$this->db->query($query,array($step,$order_seq));

		if($step=='25'){ // 입금확인 유효기간 설정
			$this->set_order_valid_date($order_seq,$data['deposit_date']);

			// 쿠폰상품 자동 출고처리
			$this->load->model('exportmodel');
			$this->exportmodel->coupon_payexport($order_seq);
		}
	}

	// 유효기간 설정
	/**
	* order_seq : 주문번호
	* deposit_date 결제일시 (date('Y-m-d H:i:s');)
	**/
	public function set_order_valid_date($order_seq,$deposit_date) {
		$deposit_datear = explode("-",$deposit_date);
		//결제확인시
		$result_option = $this->get_item_option($order_seq);
		$result_suboption = $this->get_item_suboption($order_seq);
		if($result_option){
			foreach($result_option as $data_option) {

				$optionnewtype = explode(",",$data_option['newtype']);
				if( in_array("date",$optionnewtype) ) {
					$social_start_date =$social_end_date_tmp = date("Y-m-d",strtotime($data_option['codedate']));

				}elseif( in_array("dayinput",$optionnewtype)) {
					$social_start_date					= date("Y-m-d",strtotime($data_option['sdayinput']));
					$social_end_date_tmp			= date("Y-m-d",strtotime($data_option['fdayinput']));

				}elseif( in_array("dayauto",$optionnewtype) ) {
					$depositmonth = $deposit_datear[1];
					$depositday = $deposit_datear[1];
					$sday = $data_option['sdayauto'];
					$fday = $data_option['fdayauto'];
					if( $data_option['dayauto_type'] == 'month' ) {
						$social_start_date				= ($sday>0)?date("Y-m-d", strtotime($deposit_datear[0]."-".$depositmonth."-".$sday)):date("Y-m-d", strtotime($deposit_datear[0]."-".$depositmonth."-01"));
						$social_end_date_tmp		= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
					}elseif( $data_option['dayauto_type'] == 'day' ) {
						$social_start_date				= ($sday>0)?date("Y-m-d",strtotime('+'.$sday.' day '.$deposit_date)):date("Y-m-d",strtotime($deposit_date)); 
						$social_end_date_tmp		= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
					}elseif( $data_option['dayauto_type'] == 'next' ) {
						$social_start_date				= ($sday>0)?date("Y-m-d",strtotime(date("Y-m",strtotime('+1 month '.$deposit_date))."-".$sday)):date("Y-m-d",strtotime(date("Y-m",strtotime('+1 month '.$deposit_date))."-01"));
						$social_end_date_tmp		= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
					}
				}

				if( $data_option['dayauto_day'] == 'end' ){//끝나는 날짜의 말일
					$social_end_date = date("Y-m-t", strtotime($social_end_date_tmp));
				}else{
					$social_end_date = date("Y-m-d", strtotime($social_end_date_tmp));
				}

				$this->db->where('item_option_seq',$data_option['item_option_seq']);
				$this->db->where('order_seq',$order_seq);
				$this->db->update('fm_order_item_option',array('social_start_date'=>$social_start_date,'social_end_date'=>$social_end_date));
			}
		}

		if($result_suboption){
			foreach($result_suboption as $data_suboption) {

				if( ($data_suboption['newtype'] == "date") ) {
					$social_start_date =$social_end_date_tmp = date("Y-m-d",strtotime($data_suboption['codedate']));

				}elseif( ($data_suboption['newtype'] == "dayinput")) {
					$social_start_date				= date("Y-m-d",strtotime($data_suboption['sdayinput']));
					$social_end_date_tmp		= date("Y-m-d",strtotime($data_suboption['fdayinput']));

				}elseif( ($data_suboption['newtype'] == "dayauto") ) {
					$depositmonth = $deposit_datear[1];
					$depositday = $deposit_datear[1];
					$sday = $data_suboption['sdayauto'];//day
					$fday = $data_suboption['fdayauto'];//day
					if( $data_suboption['dayauto_type'] == 'month' ) {
						$social_start_date			= ($sday>0)?date("Y-m-d",strtotime($deposit_datear[0]."-".$depositmonth."-".$sday)):date("Y-m-d",strtotime($deposit_datear[0]."-".$depositmonth."-01"));
						$social_end_date_tmp	= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
					}elseif( $data_suboption['dayauto_type'] == 'day' ) {
						$social_start_date			= ($sday>0)?date("Y-m-d",strtotime('+'.$sday.' day '.$deposit_date)):date("Y-m-d",strtotime($deposit_date)); 
						$social_end_date_tmp	= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
					}elseif( $data_suboption['dayauto_type'] == 'next' ) {
						$social_start_date			= ($sday>0)?date("Y-m-d",strtotime(date("Y-m",strtotime('+1 month '.$deposit_date))."-".$sday)):date("Y-m-d",strtotime(date("Y-m",strtotime('+1 month '.$deposit_date))."-01"));
						$social_end_date_tmp	= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
					}
				}

				if( $data_suboption['dayauto_day'] == 'end' ){//끝나는 날짜의 말일
					$social_end_date = date("Y-m-t", strtotime($social_end_date_tmp));
				}else{
					$social_end_date = date("Y-m-d", strtotime($social_end_date_tmp));
				}
				$this->db->where('item_suboption_seq',$data_suboption['item_suboption_seq']);
				$this->db->where('item_option_seq',$data_suboption['item_option_seq']);
				$this->db->where('order_seq',$order_seq);
				$this->db->update('fm_order_item_suboption',array('social_start_date'=>$social_start_date,'social_end_date'=>$social_end_date));
			}
		}
	}

	// 주문 상품과 주문서의 주문 상태 거꾸로 가기
	public function set_reverse_step($order_seq,$step,$arr='',$mode='normal'){
		$data['step'] = $step;
		$this->load->model('goodsmodel');

		// 출고량 업데이트를 위한 변수선언
		$r_reservation_goods_seq = array();

		if($step=='15' && $mode=='normal'){
			/* 상품분석 수집 */
			$this->load->model('goodslog');
			$query = $this->db->query("SELECT a.goods_seq, (select sum(price) from fm_order_item_option where order_seq=a.order_seq) as option_price_sum, (select sum(price) from fm_order_item_suboption where order_seq=a.order_seq) as suboption_price_sum  FROM `fm_order_item` as a WHERE a.order_seq=?",$order_seq);
			$items = $query->result_array();
			foreach($items as $item){
				$this->goodslog->add('deposit',$item['goods_seq'],-1);
				$this->goodslog->add('deposit_price',$item['goods_seq'],-($item['option_price_sum']+$item['suboption_price_sum']));
			}

			$data['deposit_yn'] = 'n';
			$data['deposit_date'] = '';

			// 해당 주문 상품의 출고예약량 업데이트
			$result_option = $this->get_item_option($order_seq);
	   		$result_suboption = $this->get_item_suboption($order_seq);
			if($result_option){
				foreach($result_option as $data_option){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_option['goods_seq'];
					}
				}
			}
			if($result_suboption){
				foreach($result_suboption as $data_suboption){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_suboption['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_option['goods_seq'];
					}

				}
			}

		}else if($step=='15') { // 주문무효에서 주문접수로 변경시
			$orders = $this->ordermodel->get_order($order_seq);
			$options = $this->ordermodel->get_item_option($order_seq);

			if($orders['member_seq']){
				$this->load->model('membermodel');
				$emoney = 0;
				$orders['emoney'] = (int) $orders['emoney'];
				if($orders['member_seq']){
					 $emoney = $this->membermodel->get_emoney($orders['member_seq']);
				}
				if($emoney < $orders['emoney']) return false;

				/* 적립금 사용 */
				if($orders['emoney_use']=='return' && $orders['emoney'] > 0 )
				{
					$params = array(
						'gb'		=> 'minus',
						'type'		=> 'order',
						'emoney'	=> $orders['emoney'],
						'ordno'	=> $order_seq,
						'memo'		=> "[복원]주문접수({$order_seq})에 의한 적립금 사용",
					);
					$this->membermodel->emoney_insert($params, $orders['member_seq']);
					$this->ordermodel->set_emoney_use($order_seq,'use');
				}

				$cash = 0;
				$orders['cash'] = (int) $orders['cash'];
				if($orders['member_seq']){
					 $cash = get_member_money('cash', $orders['member_seq']);
				}
				if($cash < $orders['cash']) return false;

				if($orders['cash_use']=='return'  && $orders['cash'] > 0 )
				{
					$params = array(
						'gb'		=> 'minus',
						'type'		=> 'order',
						'cash'	=> $orders['cash'],
						'ordno'	=> $order_seq,
						'memo'		=> "[복원]주문접수({$order_seq})에 의한 이머니 사용",
					);
					$this->membermodel->cash_insert($params, $orders['member_seq']);
					$this->ordermodel->set_cash_use($order_seq,'use');
				}
			}

			// 해당 주문 상품의 출고예약량 업데이트
			$result_option = $this->get_item_option($order_seq);
	   		$result_suboption = $this->get_item_suboption($order_seq);
			if($result_option){
				foreach($result_option as $data_option){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_option['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_option['goods_seq'];
					}
				}
			}
			if($result_suboption){
				foreach($result_suboption as $data_suboption){
					// 출고량 업데이트를 위한 변수정의
					if(!in_array($data_suboption['goods_seq'],$r_reservation_goods_seq)){
						$r_reservation_goods_seq[] = $data_suboption['goods_seq'];
					}
				}
			}

		}

		$this->db->where('order_seq',$order_seq);
		if($arr) $data = array_merge($data,$arr);
		$this->db->update('fm_order',$data);

		if($step == '25'){
			$query = "update fm_order_item_option set step=?,step35=0 where item_seq in (select item_seq from fm_order_item where order_seq=?) and ea!=step85";
			$this->db->query($query,array($step,$order_seq));
			$query = "update fm_order_item_suboption set step=?,step35=0 where item_seq in (select item_seq from fm_order_item where order_seq=?) and ea!=step85";
			$this->db->query($query,array($step,$order_seq));
		}else{
			$query = "update fm_order_item_option set step=? where item_seq in (select item_seq from fm_order_item where order_seq=?)  and ea!=step85";
			$this->db->query($query,array($step,$order_seq));
			$query = "update fm_order_item_suboption set step=? where item_seq in (select item_seq from fm_order_item where order_seq=?)  and ea!=step85";
			$this->db->query($query,array($step,$order_seq));
		}

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		return true;
	}

	// 에누리 적용
	public function set_enuri($order_seq,$enuri,$oriEnuri){
		$data[] = $oriEnuri;
		$data[] = $enuri;
		$data[] = $enuri;
		$data[] = $order_seq;
		$query = "update fm_order set settleprice=settleprice+?-?, enuri=? where order_seq=?";
		$this->db->query($query,$data);
	}

	// 주문 삭제
	public function delete_order($order_seq){
		$this->db->query("delete from fm_order_item_input where item_seq in (select item_seq from fm_order_item where order_seq=?)",array($order_seq));
		$this->db->query("delete from fm_order_item_option where item_seq in (select item_seq from fm_order_item where order_seq=?)",array($order_seq));
		$this->db->query("delete from fm_order_item_suboption where item_seq in (select item_seq from fm_order_item where order_seq=?)",array($order_seq));
		$this->db->where('order_seq',$order_seq);
		$this->db->delete(array('fm_order_item','fm_order'));
	}

	// 주문 옵션 가져오기
	public function get_item_option($order_seq,$tax=''){
		$query = "select o.*,i.*, o.goods_code as opt_goods_code  from fm_order_item i, fm_order_item_option o where i.item_seq=o.item_seq and i.order_seq=?";
		if($tax){
			$query .= " and i.tax = 'tax'";
		}
		$query = $this->db->query($query,array($order_seq));
		foreach($query->result_array() as $data){
			$data['goods_code'] = $data['opt_goods_code'];
			$result[] = $data;
		}
		return $result;
	}
	// 주문 서브옵션 가져오기
	public function get_item_suboption($order_seq,$tax=''){
		$query = "select o.*,i.*, o.goods_code as opt_goods_code  from fm_order_item i, fm_order_item_suboption o where i.item_seq=o.item_seq and i.order_seq=?";
		if($tax){
			$query .= " and i.tax = 'tax'";
		}
		$query = $this->db->query($query,array($order_seq));
		foreach($query->result_array() as $data){
			$data['goods_code'] = $data['opt_goods_code'];
			$result[] = $data;
		}
		return $result;
	}

	/**
	* 주문시점의 쇼셜쿠폰상품의 취소(환불)설정 가져오기
	* order_seq : 주문고유번호
	* item_seq : 주문상품 고유번호
	* social_start_date : 유효기간시작
	* social_end_date : 유효기간종료
	**/
	public function get_item_socialcp_cancel( $order_seq , $item_seq ) {
		$result = false;
		$sql = "select * from fm_order_socialcp_cancel where order_seq=? and item_seq=? ";
		$sql .= " order by socialcp_seq asc limit 1";//% 취소(환불) 설정 1개
		$query = $this->db->query($sql,array($order_seq, $item_seq)); 
		if( $query ) {
		foreach($query->result_array() as $data){
			$result[] = $data;
				$firstpercent = $data['socialcp_seq'];
			}
		}

		$sql = "select * from fm_order_socialcp_cancel where order_seq=? and item_seq=?  and socialcp_seq != ? ";
		$sql .= " order by socialcp_cancel_day desc";//% 공제!! 후 취소(환불) 설정
		$query = $this->db->query($sql,array($order_seq, $item_seq,$firstpercent)); 
		if( $query ) {
			foreach($query->result_array() as $data){
				$result[] = $data;
			}
		}
		return $result;
	}

	public function get_reservation_for_goods($cfg,$goods_seq){
		if( !$cfg ){
			$tmp = config_load('order');
			$cfg = $tmp['ableStockStep'];
		}
		$query = "
			select
				sum(o.ea) ea from fm_order_item i,fm_order_item_option o
			where
				i.item_seq = o.item_seq
				and i.goods_seq = ?
				and o.step >= ?
				and o.step <= '45'
			";
			$query = $this->db->query($query,array($goods_seq,$cfg));

			list($result) = $query->result_array($query);
			return $result['ea'];
	}

	// 출고 예약량 가져오기
	public function get_option_reservation($cfg,$goods_seq,$option1,$option2,$option3,$option4,$option5){
		if(!$this->goodsmodel) $this->load->model('goodsmodel');
		return $this->goodsmodel->get_option_reservation($cfg,$goods_seq,$option1,$option2,$option3,$option4,$option5);
	}

	// 출고 예약량 가져오기
	public function get_suboption_reservation($cfg,$goods_seq,$title,$suboption){
		if(!$this->goodsmodel) $this->load->model('goodsmodel');
		return $this->goodsmodel->get_suboption_reservation($cfg,$goods_seq,$title,$suboption);
	}

	// 주문에서 출고 예약량 가져오기
	public function get_option_reservation_from_order($cfg,$goods_seq,$option1,$option2,$option3,$option4,$option5){
		$query = "
			select
				sum(o.ea) ea from fm_order_item i,fm_order_item_option o
			where
				i.item_seq=o.item_seq
				and i.goods_seq=?
				and o.step >= ?
				and o.step <= '45'
				and o.option1=?
				and o.option2=?
				and o.option3=?
				and o.option4=?
				and o.option5=?
			";
			$query = $this->db->query($query,array(
				$goods_seq,
				$cfg,
				$option1,
				$option2,
				$option3,
				$option4,
				$option5
			));

			list($result) = $query->result_array($query);
			return $result['ea'];
	}

	// 주문에서 출고 예약량 가져오기
	public function get_suboption_reservation_from_order($cfg,$goods_seq,$title,$suboption){
		$query = "
			select
				sum(ea) ea from fm_order_item i,fm_order_item_suboption o
			where
				i.item_seq=o.item_seq
				and i.goods_seq=?
				and o.step >= ?
				and o.step <= '45'
				and o.title=?
				and o.suboption=?
			";
			$query = $this->db->query($query,array(
				$goods_seq,
				$cfg,
				$title,
				$suboption
			));

			list($result) = $query->result_array($query);
			return $result['ea'];
	}

	// 주문 로그 저장
	public function set_log($order_seq,$type,$actor,$title='',$detail='',$caccel_arr='',$export_code=''){
		$tmp = array(
			'HTTP_HOST'=>$_SERVER['HTTP_HOST'],
			'REMOTE_ADDR'=>$_SERVER['REMOTE_ADDR'],
			'REQUEST_URI'=>$_SERVER['REQUEST_URI'],
			'HTTP_REFERER'=>$_SERVER['HTTP_REFERER'],
			'GET'=>$_GET,
			'POST'=>$_POST
		);

		$sys_memo = serialize($tmp);

		$detail .= chr(10).$sys_memo;
		$data['order_seq']		= $order_seq;
		$data['type']			= $type;
		$data['actor']			= $actor;
		if(!$data['title']) $data['title'] = "";
		$data['title']			= $title;
		$data['detail']			= $detail;
		$data['regist_date']	= date('Y-m-d H:i:s');
		if($export_code){
			$data['export_code'] = $export_code;
		}
		if($caccel_arr) $data 	= array_merge($data,$caccel_arr);
		$this->db->insert('fm_order_log', $data);
	}

	// 주문 로그 가져오기
	public function get_log($order_seq,$type,$wheres=array()){
		if($type!='all') $this->db->where('type',		$type);
		$this->db->where('order_seq',	$order_seq);
		foreach($wheres as $k=>$v){
			$this->db->where($k,	$v);
		}
		$query = $this->db->get('fm_order_log');
		foreach ($query->result_array() as $data)
		{
		    $result[] = $data;
		}
		return $result;
	}

	public function get_delivery_method($orders){
		if($orders['international'] == 'domestic'){ // 국내배송인 경우
			if($orders['shipping_method'] == 'postpaid'){ //착불
				$data = code_load('shipping','delivery');
				$data[0]['value'] = "택배 (착불)";
			}
			elseif($orders['shipping_method'] == 'delivery'){
				$data = code_load('shipping',$orders['shipping_method']);
				$data[0]['value'] = "택배 (선불)";
			}else{
				$data = code_load('shipping',$orders['shipping_method']);
			}
		}else{
			$data = code_load('internationalShipping',$orders['shipping_method_international']);
		}
		return $data[0]['value'];
	}

	// 출고수량 체크
	public function check_option_remind_ea($ea,$seq)
	{
		$query = "select ea,step35,step45,step55,step65,step75,step85 from fm_order_item_option where item_option_seq = ?";
		$query = $this->db->query($query,$seq);
		$row = $query->row_array();
		$remind = ((int) $row['ea'] + (int) $row['step35']) - ((int) $row['step45'] + (int) $row['step55'] + (int) $row['step65'] + (int) $row['step75'] + (int) $row['step85']) - $ea;
		if( $remind < 0 ) return false;
		return $remind;
	}

	// 출고수량 체크
	public function check_suboption_remind_ea($ea,$seq)
	{
		$query = "select ea,step35,step45,step55,step65,step75,step85 from fm_order_item_suboption where item_suboption_seq = ?";
		$query = $this->db->query($query,$seq);
		$row = $query->row_array();
		$remind = ((int) $row['ea'] + (int) $row['step35']) - ((int) $row['step45'] + (int) $row['step55'] + (int) $row['step65'] + (int) $row['step75'] + (int) $row['step85']) - $ea;
		if( $remind < 0 ) return false;
		return $remind;
	}

	// 취소,배송 상태 별 수량 업데이트
	public function set_step_ea($step,$ea,$seq,$mode='option'){
		if($mode == 'suboption'){
			$table = "fm_order_item_suboption";
			$where_field = "item_suboption_seq";
		}else{
			$table = "fm_order_item_option";
			$where_field = "item_option_seq";
		}
		$field = 'step'.$step;

		$query = "update ".$table." set ".$field." = ".$field." + ( ? ) where ".$where_field." = ?";
		$this->db->query($query,array($ea,$seq));

		if(in_array( $step,array(35,45,55) )){
			$query = "update ".$table." set  step35 = ea - step85 - step45 - step55 - step65 - step75 where ".$where_field." = ? and step = 35 ";
			$this->db->query($query,array($seq));
		}
	}

	// 주문된 옵션,추가옵션 모두 상품준비로 변경
	public function set_step35_ea($order_seq, $option_seq = null, $option_type = null){
		// update 구분
		if	($order_seq){
			$update_order	= 'all';
			if		($option_seq > 0 && $option_type == 'option'){
				$update_order	= 'option';
				$addWhere		= " and item_option_seq = '".$option_seq."' ";
			}elseif	($option_seq > 0 && $option_type == 'suboption'){
				$update_order	= 'suboption';
				$addWhere		= " and item_suboption_seq = '".$option_seq."' ";
			}
		}

		// 옵션 update
		if	($update_order == 'all' || $update_order == 'option'){
			$query = "update fm_order_item_option set step35=ea-step85,step45=0,step55=0,step65=0,step75=0 where order_seq = ? and step = '25' ".$addWhere;
			$this->db->query($query,array($order_seq));
		}

		// 추가옵션 update
		if	($update_order == 'all' || $update_order == 'suboption'){
			$query = "update fm_order_item_suboption set step35=ea-step85,step45=0,step55=0,step65=0,step75=0 where order_seq = ? and step = '25' ".$addWhere;
			$this->db->query($query,array($order_seq));
		}
	}

	// 취소,배송 상태 별 수량으로 option,suboption상태 변경
	public function set_option_step($seq,$mode='option'){
		if($mode == 'suboption'){
			$table = "fm_order_item_suboption";
			$where_field = "item_suboption_seq";
		}else{
			$table = "fm_order_item_option";
			$where_field = "item_option_seq";
		}
		$query = "select * from ".$table." where ".$where_field."=?";
		$query = $this->db->query($query,array($seq));
		list($row) = $query->result_array();

		$arr = array('85','35','45','55','65','75');
		foreach($arr as $ea_step){
			$field		= 'step'.$ea_step;
			if	($ea_step < 85) $addFld[]	= $field;
			if( $row['ea'] == $row[$field] ){
				$step = $ea_step;
				break;
			}

			// 부분상태로
			if( $row[$field] > 0 && $row['ea'] != $row[$field] && $ea_step > 35 && $ea_step < 85){
				$step = $ea_step - 5;
			}
		}

		if	($step){
			$query = "update ".$table." set step=? where ".$where_field."=?";
			$this->db->query($query,array($step,$seq));
		// 결제확인 상태로 처리 ( 상태별수량의 합이 0이면 결제확인 )
		}else{
			$query = "update ".$table." set step=? where (".implode('+', $addFld).") = 0 and  step >= 25 and ".$where_field."=? ";
			$this->db->query($query,array(25,$seq));
		}
	}

	public function set_order_step($order_seq){
		$step = 0;
		$this->load->model('refundmodel');
		$this->load->model('membermodel');
		$this->load->model('eventmodel');


		/* 가장큰상태값, 가장작은상태값 */
		$query = $this->db->query("
			select max(a.step) max_step, min(a.step) min_step from
			(select step from fm_order_item_option where order_seq=? and step between ? and ? union select step from fm_order_item_suboption where order_seq=? and step between ? and ?) as a
		",array($order_seq,15,75,$order_seq,15,75));
		$data = $query->row_array();
		$max_step = $data['max_step'];
		$min_step = $data['min_step'];
/*
		if($min_step==25 && 35<$max_step){
			$query = $this->db->query("select 'option' as option_type, item_option_seq as option_seq from fm_order_item_option where order_seq=? and step=? union select 'suboption' as option_type, item_suboption_seq as option_seq from fm_order_item_suboption where order_seq=? and step=?",array($order_seq,25,$order_seq,25));
			foreach($query->result_array() as $row){
				// 주문상태별 수량 변경
				$this->ordermodel->set_step_ea(35,0,$row['option_seq'],$row['option_type']);

				// 주문 option 상태 변경
				$this->ordermodel->set_option_step($row['option_seq'],$row['option_type']);
			}
		}
*/
		/* 부분(출고,배송) 상태값 */
		$query = $this->db->query("select step from fm_order_item_option where order_seq=? and step between ? and ? union select step from fm_order_item_suboption where order_seq=? and step between ? and ?",array($order_seq,15,75,$order_seq,15,75));
		$max_sub = 0;
		foreach($query->result_array() as $data){
			if( substr($data['step'],1) == 0 || in_array($data['step'], array('25','35'))){
				$max_sub = $data['step'];
			}
		}

		if($max_sub && $max_step > 35) $max_step = substr($max_step,0,1).'0';
		$step = $max_step;

		/* 모든 item이 결제취소일 경우 체크*/
		$query = "
			select sum(t.step85_cnt) as step85_cnt, sum(t.total_cnt) as total_cnt from
			(
				select sum(if(step='85',1,0)) as step85_cnt, count(*) as total_cnt from fm_order_item_option where order_seq=?
				union
				select sum(if(step='85',1,0)) as step85_cnt, count(*) as total_cnt from fm_order_item_suboption where order_seq=?
			) as t
		";
		$query = $this->db->query($query,array($order_seq,$order_seq));
		$res = $query->row_array();
		if($res['step85_cnt'] && $res['step85_cnt']==$res['total_cnt']) $step = 85;

		if($step){
			$query = "update fm_order set step=? where order_seq=?";
			$this->db->query($query,array($step,$order_seq));
		}

		// 주문이 모두 출고완료  경우
		if($step == 55){
			// 에스크로 주문 배송정보 전달
			$data_order = $this->get_order($order_seq);
			$pg = config_load($this->config_system['pgCompany']);
			if( preg_match('/escrow/',$data_order['payment']) ){
				if( preg_match('/virtual/',$data_order['payment']) ){
					$where_array = array('res_cd'=>'00');		
					$data_pg_log = $this->get_pg_log($order_seq,$where_array);
					if( $data_pg_log[0]['tno'] ) $data_order['pg_transaction_number'] = $data_pg_log[0]['tno'];
				}
				$this -> {$this->config_system['pgCompany'].'_delivery'}($data_order,$pg);
			}

			// 네이버 마일리지
			$this->load->model('navermileagemodel');
			$this->navermileagemodel->batch_regist('delivery',$order_seq);

		}


		// 주문이 배송완료 일경우 주문한 회원의 구매횟수 및 구매금액 업데이트
		if($step == 75){
			$data_order = $this->get_order($order_seq);
			$data_item_option 	 = $this->get_item_option($order_seq);
			$data_item_suboption = $this->get_item_suboption($order_seq);
			$data_item 	= $this->get_item($order_seq);

			//이벤트 판매건/주문건/주문금액 필드추가 및 실시간업데이트 @2013-06-19
			foreach($data_item as $item){
				if( $item['event_seq'] ) {
					$this->eventmodel->event_order($item['event_seq']);
					$this->eventmodel->event_order_batch($item['event_seq']);
				}
			}

			if($data_item_option) foreach($data_item_option as $item) $order_ea += $item['ea'];
			if($data_item_suboption) foreach($data_item_suboption as $item) $order_ea += $item['ea'];

			$refund_price 	= $this->refundmodel->get_refund_price_for_order($order_seq,'cancel_payment','complete');
			$refund_ea 		= $this->refundmodel->get_refund_ea_for_order($order_seq,'cancel_payment','complete');

			$settle_price =  $data_order['settleprice'] - $refund_price;
			$order_ea =  $order_ea - $refund_ea;

			if($data_order['member_seq']){
				$this->membermodel->member_order($data_order['member_seq']);

				//주문건/주문금액 필드추가 및 실시간업데이트 @2013-06-19
				$this->membermodel->member_order_batch($data_order['member_seq']);
			}
		}
	}



	// 주문서 정보 가져오기 > 배송쿠폰
	public function get_order_shipping_coupon($member_seq=null, $download_seq){
		if(!$download_seq) return false;//!$member_seq &&
		$addmembersql = ($member_seq)? ' and member_seq='.$member_seq.'  ':'';
		$query = $this->db->query('select * from fm_order where download_seq=?'.$addmembersql,array($download_seq));
		list($orders) = $query->result_array($query);
		return $orders;
	}

	//프로모션코드 > 배송프로모션코드
	public function get_order_shipping_promotion($member_seq=null, $shipping_promotion_code_seq){
		if(!$shipping_promotion_code_seq) return false;//!$member_seq &&
		$addmembersql = ($member_seq)? ' and ord.member_seq='.$member_seq.'  ':'';
		$query = "
		SELECT i.goods_name, i.goods_seq, i.order_seq, i.image, ord.shipping_promotion_code_sale as promotion_order_saleprice
		FROM  fm_order_item i
		left join fm_order ord on ord.order_seq = i.order_seq
		WHERE ord.step != 0 and  ord.shipping_promotion_code_seq='".$shipping_promotion_code_seq ."'".$addmembersql;
		$query = $this->db->query($query);//s.provider_seq = 1 and
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		return $items;
	}

	//프로모션코드 주문상품 정보
	public function get_option_promotioncode_item($member_seq=null, $promotion_code_seq){
		if(!$promotion_code_seq) return false;//!$member_seq &&

		$addmembersql = ($member_seq)? ' and ord.member_seq='.$member_seq.'  ':'';
		$query = "
		SELECT i.goods_name, i.goods_seq, i.order_seq, i.image, sum(o.promotion_code_sale) as promotion_order_saleprice
		FROM  fm_order_item i
		left join fm_order ord on ord.order_seq = i.order_seq
		left join fm_order_item_option o on i.item_seq=o.item_seq
		WHERE  ord.step != 0 and  o.promotion_code_seq='".$promotion_code_seq."'".$addmembersql;
		$query = $this->db->query($query);
		foreach($query->result_array() as $data){
			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !$data['image'] || !is_file(ROOTPATH.$data['image']) ) {
				$data['image'] = viewImg($data['goods_seq'],'thumbCart');
			}
			$items[] = $data;
		}
		return $items;
	}

	//쿠폰 주문상품 정보
	public function get_option_coupon_item($member_seq=null, $download_seq){
		if(!$download_seq) return false;//!$member_seq &&
		$addmembersql = ($member_seq)? ' and ord.member_seq='.$member_seq.'  ':'';
		$query = "
		SELECT i.goods_name, i.goods_seq, i.order_seq, i.image, sum(o.coupon_sale) as coupon_order_saleprice
		FROM  fm_order_item i
		left join fm_order ord on ord.order_seq = i.order_seq
		left join fm_order_item_option o on i.item_seq=o.item_seq
		WHERE  ord.step != 0 and  o.download_seq='".$download_seq."'".$addmembersql;
		$query = $this->db->query($query);
		foreach($query->result_array() as $data){
			//주문상품의 이미지가 없는경우 실제상품의 이미지를 가져옴
			if( !$data['image'] || !is_file(ROOTPATH.$data['image']) ) {
				$data['image'] = viewImg($data['goods_seq'],'thumbCart');
			}
			$items[] = $data;
		}
		return $items;
	}

	// 재주문 넣기
	public function reorder($orign_order_seq,$return_code){

		$this->load->model('returnmodel');
		$data_order = $this->get_order($orign_order_seq);
		$data_item 	= $this->get_item($orign_order_seq);
		$data_option = $this->get_item_option($orign_order_seq);
		$data_sub 	= $this->get_item_suboption($orign_order_seq);

		// 생성할 주문서에 들어갈 값 정리
		$arr_del = array(
			'log',
			'bank_account',
			'virtual_account',
			'virtual_date',
			'pg_transaction_number',
			'pg_approval_number',
			'cash_receipts_no',
			'emoney_use',
			'emoney',
			'cash_use',
			'cash',
			'shipping_cost',
			'international_cost',
			'download_seq',
			'coupon_sale',
			'typereceipt',
			'important',
			'mpayment',
			'referer_group_cd',
			'referer_name'
		);
		$insert_order = $data_order;
		// 반품시 최상위 주문번호 저장 :: 2014-11-27 lwh
		if($data_order['top_orign_order_seq'])
			$insert_order['top_orign_order_seq'] = $data_order['top_orign_order_seq'];
		else
			$insert_order['top_orign_order_seq'] = $orign_order_seq;

		$insert_order['order_seq'] = $order_seq = $this -> get_order_seq();
		$insert_order['orign_order_seq'] = $orign_order_seq;
		$insert_order['original_settleprice'] = 0;
		$insert_order['settleprice'] = 0;
		$insert_order['enuri'] = 0;
		$insert_order['step'] = 25;
		$insert_order['deposit_yn'] = 'y';
		$insert_order['deposit_date'] = date('Y-m-d H:i:s');
		$insert_order['regist_date'] = date('Y-m-d H:i:s');
		$insert_order['payment'] = 'bank';
		foreach($arr_del as $data) unset($insert_order[$data]);

		$newinsert_order = filter_keys($insert_order, $this->db->list_fields('fm_order'));
		$this->db->insert('fm_order', $newinsert_order);

		$arr_del = array(
			'item_seq',
			'goods_shipping_cost',
			'shipping_policy',
			'shipping_unit',
			'basic_shipping_cost',
			'add_shipping_cost',
			'cancel_type',
			'goods_shipping_cost_ori',
			'add_goods_shipping'
		);
		$arr_del_option = array(
			'item_option_seq',
			'price',
			'member_sale',
			'download_seq',
			'coupon_sale',
			'promotion_code_sale',
			'promotion_code_seq',
			'consumer_price',
			'supply_price',
			'refund_ea',
			'step85',
			'step35',
			'step45',
			'step55',
			'step65',
			'step75',
			'goods_seq',
			'goods_code',
			'image',
			'goods_name',
			'goods_shipping_cost',
			'shipping_policy',
			'shipping_unit',
			'basic_shipping_cost',
			'add_shipping_cost',
			'multi_discount_ea',
			'shipping_seq',
			'provider_seq',
			'goods_type',
			'cancel_type',
			'individual_refund',
			'individual_refund_inherit',
			'individual_export',
			'individual_return',
			'tax',
			'fblike_sale',
			'mobile_sale',
			'event_seq',
			'goods_kind',
			'socialcp_input_type',
			'socialcp_use_return',
			'socialcp_use_emoney_day',
			'socialcp_use_emoney_percent',
		);

		$arr_del_suboption = array(
			'item_suboption_seq',
			'price',
			'consumer_price',
			'supply_price',
			'refund_ea',
			'step85',
			'step35',
			'step45',
			'step55',
			'step65',
			'step75',
			'goods_seq',
			'goods_code',
			'image',
			'goods_name',
			'goods_shipping_cost',
			'shipping_policy',
			'shipping_unit',
			'basic_shipping_cost',
			'add_shipping_cost',
			'multi_discount_ea',
			'shipping_seq',
			'provider_seq',
			'goods_type',
			'cancel_type',
			'individual_refund',
			'individual_refund_inherit',
			'individual_export',
			'individual_return',
			'tax',
			'fblike_sale',
			'mobile_sale',
			'event_seq',
			'goods_kind',
			'socialcp_input_type',
			'socialcp_use_return',
			'socialcp_use_emoney_day',
			'socialcp_use_emoney_percent',
		);


		$date_return_item =$this->returnmodel->check_return_item($return_code);
		foreach($data_item as $item){
			// 교환 요청 수량만큼난 조회
			foreach($date_return_item as $return_option){
			  if($item['item_seq'] == $return_option['item_seq']){
					if($return_option['option_seq']){
						$insert_item = $item;
						$insert_item['order_seq'] = $order_seq;
						foreach($arr_del as $data) unset($insert_item[$data]);
						$this->db->insert('fm_order_item', $insert_item);
						$item_seq = $this->db->insert_id();

						// 상품 옵션 재주문
						unset($insert_option);
						foreach($data_option as $option){
							if($option['item_option_seq'] == $return_option['option_seq']){
								$insert_option = $option;

								// 상위 seq 존재시 대체 :: 2014-11-27 lwh
								if($option['top_item_option_seq']){
									$insert_option['top_item_option_seq'] = $option['top_item_option_seq'];
									$insert_option['top_item_seq'] = $option['top_item_seq'];
								}else{
									$insert_option['top_item_option_seq'] = $option['item_option_seq'];
									$insert_option['top_item_seq'] = $option['item_seq'];
								}

								$insert_option['ea'] = $return_option['ea'];
								$insert_option['order_seq'] = $order_seq;
								$insert_option['item_seq'] = $item_seq;
								$insert_option['step'] = 25;
							}
						}
						if($insert_option){
							unset($insert_option['tax']);
							foreach($arr_del_option as $data) unset($insert_option[$data]);

							$newinsert_option = filter_keys($insert_option, $this->db->list_fields('fm_order_item_option'));
							$this->db->insert('fm_order_item_option', $newinsert_option);
							$item_option_seq = $this->db->insert_id();
						}
					}else if($return_option['suboption_seq']){
						// 서브상품 옵션 재주문
						unset($insert_sub_option);
						foreach($data_sub as $option){
							if($option['item_suboption_seq'] == $return_option['suboption_seq']){
								$insert_sub_option = $option;

								// 상위 seq 존재시 대체 :: 2014-11-27 lwh
								if($option['top_item_suboption_seq']){
									$insert_sub_option['top_item_suboption_seq'] = $option['top_item_suboption_seq'];
								}else{
									$insert_sub_option['top_item_suboption_seq'] = $option['item_suboption_seq'];
								}

								$insert_sub_option['ea'] = $return_option['ea'];
								$insert_sub_option['order_seq'] = $order_seq;
								$insert_sub_option['item_seq'] = $item_seq;
								$insert_sub_option['item_option_seq'] = $item_option_seq;
								$insert_sub_option['step'] = 25;
							}
						}
						if($insert_sub_option){
							unset($insert_sub_option['tax']);
							foreach($arr_del_suboption as $data) unset($insert_sub_option[$data]);

							$newinsert_sub_option = filter_keys($insert_sub_option, $this->db->list_fields('fm_order_item_suboption'));
							$this->db->insert('fm_order_item_suboption', $newinsert_sub_option);
							$item_suboption_seq = $this->db->insert_id();
						}
					}
			   }
			}
		}

		// 배송지 데이터 자동생성
		$this->ordermodel->check_shipping_data($order_seq);

	}

	public function get_option_reserve($seq, $type='reserve'){
		$query = "select reserve, point from fm_order_item_option where item_option_seq=?";
		$query = $this->db->query($query,array($seq));
		$data = $query->result_array();
		return $data[0][$type];
	}

	public function get_suboption_reserve($seq, $type='reserve'){
		$query = "select reserve, point from fm_order_item_suboption where item_suboption_seq=?";
		$query = $this->db->query($query,array($seq));
		$data = $query->result_array();
		return $data[0][$type];
	}

	// 최근배송지 저장 : 단일배송지
	public function insert_delivery_address($insert_mode=''){

		if($_POST['insert_mode']){
			$insert_mode = $_POST['insert_mode'];
		}

		if($this->userInfo['member_seq']) $member_seq = $this->userInfo['member_seq'];
		if($member_seq){
			if($insert_mode == 'order'){
				$insert_params['often'] 					= 'Y';
				$insert_params['lately'] 					= 'Y';
				$insert_params['regist_date']				= date('Y-m-d H:i:s');
			}elseif($insert_mode == 'insert'){
				$insert_params['address_description']		= $_POST['address_description'];
				$insert_params['often'] 					= 'Y';
				$insert_params['regist_date']				= date('Y-m-d H:i:s');
			}else{
				$insert_params['lately'] 					= 'Y';
				$insert_params['regist_date']				= date('Y-m-d H:i:s');
			}

			$insert_params['member_seq'] 					= $member_seq;
			$insert_params['recipient_user_name']			= $_POST['recipient_user_name'];

			if($_POST['international'] == '1'){

				$insert_params['international'] 				= 'international';
				$insert_params['region'] 						= $_POST['region'];
				$insert_params['international_address'] 		= $_POST['international_address'];
				$insert_params['international_town_city'] 		= $_POST['international_town_city'];
				$insert_params['international_county'] 			= $_POST['international_county'];
				$insert_params['international_postcode'] 		= $_POST['international_postcode'];
				$insert_params['international_country'] 		= $_POST['international_country'];
				$insert_params['recipient_phone'] 				= implode('-',$_POST['international_recipient_phone']);
				$insert_params['recipient_cellphone'] 			= implode('-',$_POST['international_recipient_cellphone']);

			}else if($_POST['international'] == '0'){

				$insert_params['international'] 				= 'domestic';
				$insert_params['recipient_zipcode'] 			= implode('-',$_POST['recipient_zipcode']);
				$insert_params['recipient_address_type'] 		= $_POST['recipient_address_type'];
				$insert_params['recipient_address'] 			= $_POST['recipient_address'];
				$insert_params['recipient_address_street'] 		= $_POST['recipient_address_street'];
				$insert_params['recipient_address_detail'] 		= $_POST['recipient_address_detail'];
				$insert_params['recipient_phone'] 				= implode('-',$_POST['recipient_phone']);
				$insert_params['recipient_cellphone'] 			= implode('-',$_POST['recipient_cellphone']);
			}

			if($_POST['save_delivery_address']){
				$insert_params['default'] 					= 'Y';
			}

			if($_POST['address_group']){
				$insert_params['address_group'] 				= $_POST['address_group'];
			}

			$this->db->insert('fm_delivery_address', $insert_params);
			$address_seq = $this->db->insert_id();

			### Private Encrypt
			$cellphone = get_encrypt_qry('recipient_cellphone');
			$phone = get_encrypt_qry('recipient_phone');
			$sql = "update fm_delivery_address set  {$cellphone}, {$phone}, update_date = now() where address_seq = {$address_seq}";
			$this->db->query($sql);
			###

			if($_POST['save_delivery_address']){
				$sql = "update fm_delivery_address set `default` = 'N' where member_seq=? and address_seq!=?";
				$this->db->query($sql,array($member_seq,$address_seq));
			}
		}
	}

	// 최근배송지 저장 : 다중배송지
	public function insert_multi_delivery_address($params){
		if($this->userInfo['member_seq']) $member_seq = $this->userInfo['member_seq'];
		if($member_seq){
			$insert_params['lately'] 						= 'Y';
			$insert_params['regist_date']					= date('Y-m-d H:i:s');
			$insert_params['member_seq'] 					= $member_seq;
			$insert_params['recipient_user_name']			= $params['recipient_user_name'];
			$insert_params['recipient_phone'] 				= $params['recipient_phone'];
			$insert_params['recipient_cellphone'] 			= $params['recipient_cellphone'];
			$insert_params['international'] 				= 'domestic';
			$insert_params['recipient_zipcode'] 			= $params['recipient_zipcode'];
			$insert_params['recipient_address_type'] 		= $params['recipient_address_type'];
			$insert_params['recipient_address'] 				= $params['recipient_address'];
			$insert_params['recipient_address_street'] 	= $params['recipient_address_street'];
			$insert_params['recipient_address_detail'] 	= $params['recipient_address_detail'];

			$this->db->insert('fm_delivery_address', $insert_params);

			### Private Encrypt
			$cellphone = get_encrypt_qry('recipient_cellphone');
			$phone = get_encrypt_qry('recipient_phone');
			$sql = "update fm_delivery_address set  {$cellphone}, {$phone}, update_date = now() where address_seq = {$this->db->insert_id()}";
			$this->db->query($sql);
			###
		}
	}

	public function update_delivery_address($seq){

		if($this->userInfo['member_seq']) $member_seq = $this->userInfo['member_seq'];
		if($member_seq){

			$params['address_description']			= $_POST['address_description'];
			$params['often'] 						= 'Y';
			$params['member_seq'] 					= $member_seq;
			$params['recipient_user_name']			= $_POST['recipient_user_name'];

			if ($_POST['address_group']) {
				$params['address_group']			= $_POST['address_group'];
			}
			if ($_POST['select_address_group']) {
				$params['address_group']			= $_POST['select_address_group'];
			}
			if($_POST['international'] == '1'){

				$params['international'] 				= 'international';
				$params['region'] 						= $_POST['region'];
				$params['international_address'] 		= $_POST['international_address'];
				$params['international_town_city'] 		= $_POST['international_town_city'];
				$params['international_county'] 			= $_POST['international_county'];
				$params['international_postcode'] 		= $_POST['international_postcode'];
				$params['international_country'] 		= $_POST['international_country'];
				$params['recipient_phone'] 		= implode('-',$_POST['international_recipient_phone']);
				$params['recipient_cellphone'] 	= implode('-',$_POST['international_recipient_cellphone']);

			}else if($_POST['international'] == '0'){

				$params['international'] 				= 'domestic';
				$params['recipient_zipcode'] 			= implode('-',$_POST['recipient_zipcode']);
				$params['recipient_address_type']		= ($_POST['recipient_address_type'])?$_POST['recipient_address_type']:"zibun";
				$params['recipient_address'] 				= $_POST['recipient_address'];
				$params['recipient_address_street'] 	= $_POST['recipient_address_street'];
				$params['recipient_address_detail'] 	= $_POST['recipient_address_detail'];
				$params['recipient_phone'] 				= implode('-',$_POST['recipient_phone']);
				$params['recipient_cellphone'] 			= implode('-',$_POST['recipient_cellphone']);

			}

			if($_POST['save_delivery_address']){
				$params['default'] 					= 'Y';
			}

			$insert_params['update_date']				= date('Y-m-d H:i:s');
			$this->db->where('address_seq',$seq);
			$this->db->update('fm_delivery_address', $params );

			### Private Encrypt
			$cellphone = get_encrypt_qry('recipient_cellphone');
			$phone = get_encrypt_qry('recipient_phone');
			$sql = "update fm_delivery_address set  {$cellphone}, {$phone}, update_date = now() where address_seq = {$seq}";
			$this->db->query($sql);
			###

			if($_POST['save_delivery_address']){
				$sql = "update fm_delivery_address set `default` = 'N' where member_seq=? and address_seq!=?";
				$this->db->query($sql,array($member_seq,$seq));
			}
		}
	}

	public function get_order_item_option($option_seq){
		$query = "select *,(select goods_seq from fm_order_item where item_seq=fm_order_item_option.item_seq) goods_seq from fm_order_item_option where item_option_seq=?";
		$query = $this->db->query($query,array($option_seq));
		$data = $query->row_array();
		return $data;
	}

	public function get_order_item_suboption($suboption_seq){
		$query = "select *,(select goods_seq from fm_order_item where item_seq=fm_order_item_suboption.item_seq) goods_seq from fm_order_item_suboption where item_suboption_seq=?";
		$query = $this->db->query($query,array($suboption_seq));
		$data = $query->row_array();
		return $data;
	}

	public function get_order_total_ea($order_seq){
		$order_total_ea = 0;

		$query = "select sum(ea) as total_ea from fm_order_item_option where order_seq=?";
		$query = $this->db->query($query,array($order_seq));
		$data = $query->row_array();
		$order_total_ea += $data['total_ea'];

		$suboption_total_ea = 0;
		$query = "select sum(ea) as total_ea from fm_order_item_suboption where order_seq=?";
		$query = $this->db->query($query,array($order_seq));
		$data = $query->row_array();
		$order_total_ea += $data['total_ea'];

		return $order_total_ea;


	}

	public function get_pg_log($order_seq,$where_array=''){
		$query = "select * from fm_order_pg_log where order_seq=?";
		$bind[] = $order_seq;
		if($where_array){
			foreach($where_array as $field => $val){
				$query .= " and `".$field."`=?";
				$bind[] = $val;
			}
		}
		$query = $this->db->query($query,$bind);
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}


	public function lg_delivery($data_order,$pg){

		$mid =  $pg['mallCode'];
		$oid =  $data_order['order_seq']; //주문번호
		$orderdate = date('YmdHis');

		$dlvtype = '03';
		$mertkey = $pg['merchantKey'];

		$dlvdate =  date('YmdHi');

		$this->load->model('exportmodel');
		$data_export = $this->exportmodel->get_export_for_order($data_order['order_seq']);
		$pg_val['dlv_invoice'] = $data_export[0]['mdelivery_number'];
		$dlvno =  $pg_val['dlv_invoice'];

		$compID["code0"] = "CJ";	//CJ GLS
		$compID["code1"] = "";		//DHL코리아
		$compID["code2"] = "KB";	//KGB택배
		$compID["code3"] = "";		//경동택배
		$compID["code4"] = "KE";	//대한통운
		$compID["code5"] = "";		//동부택배(훼밀리)
		$compID["code6"] = "LG";	//로젠택배
		$compID["code7"] = "PO";	//우체국택배
		$compID["code8"] = "HN";	//하나로택배
		$compID["code9"] = "HJ";	//한진택배
		$compID["code10"] = "HD";	//현대택배
		$compID["code11"] = "";		//동원택배
		$compID["code12"] = "DS";	//대신택배
		$compID["code13"] = "";		//세덱스
		$compID["code14"] = "FE";	//동부익스프레스
		$compID["code15"] = "";		//천일택배
		$compID["code16"] = "";		//사가와택배
		$compID["code17"] = "IY";	//일양택배
		$compID["code18"] = "IN";	//이노지스
		$compID["code19"] = "";		//편의점택배
		$compID["code20"] = "";		//건영택배
		$compID["code21"] = "YC";	//엘로우캡

		$dlvcompcode = $compID[$data_export[0]['delivery_company_code']];

		/*
		대한통운 KE
		아주택배 AJ
		우체국택배 PO
		트라넷 TN
		현대택배 HD
		Bell Express BE
		HTH SS
		KT로지스택배 KT
		일양로지스 IY
		하나로택배 HN
		우편등기 RP
		로젠택배 LG
		엘로우캡 YC
		이젠택배 EZ
		한진택배 HJ
		동부익스프레스 FE
		CJ GLS CJ
		KGB택배 KB
		SC로지스택배 SC
		이노지스택배 IN
		대신택배 DS
		*/

		// 송장번호 -와 공백 제거
		$dlvno = str_replace(array('-',' '),'',$dlvno);

		$hashdata = md5($mid.$oid.$dlvdate.$dlvcompcode.$dlvno.$mertkey);
		$service_url = "https://pgweb.dacom.net/pg/wmp/mertadmin/jsp/escrow/rcvdlvinfo.jsp";

		$str_url = $service_url."?mid=$mid&oid=$oid&productid=$productid&orderdate=$orderdate&dlvtype=$dlvtype&rcvdate=$rcvdate&rcvname=$rcvname&rcvrelation=$rcvrelation&dlvdate=$dlvdate&dlvcompcode=$dlvcompcode&dlvno=$dlvno&dlvworker=$dlvworker&dlvworkertel=$dlvworkertel&hashdata=$hashdata";

		$ch = curl_init();

		curl_setopt ($ch, CURLOPT_URL, $str_url);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, COOKIE_FILE_PATH);
		curl_setopt ($ch, CURLOPT_COOKIEFILE, COOKIE_FILE_PATH);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		$fp = curl_exec ($ch);

		if(curl_errno($ch)){
				 //실패
		} else {
			if(trim($fp)=="OK") {
					//성공
			} else {
					//실패

			}
		}

	}
	public function kcp_delivery($data_order,$pg){
	    $this->load->model('ordermodel');
	    $order_data = $this->ordermodel->get_order($data_order['order_seq']);

	    $this->load->model('exportmodel');
	    $data_export = $this->exportmodel->get_export_for_order($data_order['order_seq']);
	    $pg_val['dlv_invoice'] = $data_export[0]['mdelivery_number'];

	    $arr_delivery = config_load('delivery_url');
	    $pg_val['mdelivery'] = $arr_delivery[$data_export[0]['delivery_company_code']]['company'];

	    $g_conf_site_cd   = $pg['mallCode'];
	    /* 테스트 3grptw1.zW0GSo4PQdaGvsF__ */
	    $g_conf_site_key  = $pg['merchantKey'];


	    $mod_type = 'STE1';
	    $tno = $order_data['pg_transaction_number'];
	    $_POST['deli_numb'] = $pg_val['dlv_invoice'];
	    $_POST['deli_corp'] = $pg_val['mdelivery'];

	    require_once ROOTPATH."pg/kcp/sample/pp_ax_hub_lib.php"; // library [수정불가]
	    $c_PayPlus = new C_PP_CLI;
	    $ordr_idxx = $data_order['order_seq'];

	    $g_conf_home_dir  = ROOTPATH."pg/kcp/";
	    $g_conf_gw_url    = $pg['mallCode']=='T0007' ? "testpaygw.kcp.co.kr" : "paygw.kcp.co.kr";

	    $g_conf_log_level = "3";           // 변경불가
	    $g_conf_gw_port   = "8090";        // 포트번호(변경불가)

	    $tran_cd = "00200000";
	    $cust_ip        = getenv( "REMOTE_ADDR"    ); // 요청 IP
	    $c_PayPlus->mf_set_modx_data( "tno",      $tno      );                              // KCP 원거래 거래번호
	    $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type );                              // 원거래 변경 요청 종류
	    $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  );                              // 변경 요청자 IP
	    $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc );                              // 변경 사유

	    if ($mod_type == "STE1")                                                                 // 상태변경 타입이 [배송요청]인 경우
	    {
	         $c_PayPlus->mf_set_modx_data( "deli_numb",   $_POST[ "deli_numb" ] );   // 운송장 번호
	         $c_PayPlus->mf_set_modx_data( "deli_corp",   $_POST[ "deli_corp" ] );   // 택배 업체명
	    }

	    $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, "", $tran_cd, "",
	    $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
	    $cust_ip, "3" , 0, 0, $g_conf_key_dir, $g_conf_log_dir);

	    $res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
	    $res_msg = $c_PayPlus->m_res_msg; // 결과 메시지
	}
	public function allat_delivery($data_order,$pg){
	}

	public function kspay_delivery($data_order,$pg){
	}

	public function inicis_delivery($data_order,$pg)
	{
		$this->load->model('exportmodel');
		$data_item = $this->get_item($data_order['order_seq']);
		$cnt = count($data_item);

		$data_export = $this->exportmodel->get_export_for_order($data_order['order_seq']);
		$pg_val['dlv_goods'] = $data_item[0]['goods_name'];
		if($cnt > 2) $pg_val['dlv_goods'] .= " 외 ".($cnt-1)."건";

		$today = date('y-m-d');
		$nowtime = date('H:i:s');
		$pg_val['tid'] = $data_order['pg_transaction_number'];
		$pg_val['mid'] = $pg['escrowMallCode'];
		$pg_val['admin'] = $pg['merchantKey'];
		$pg_val['oid'] = $data_order['order_seq'];
		$pg_val['dlv_date'] = $today;
		$pg_val['dlv_time'] = $nowtime;
		$pg_val['dlv_invoice'] = $data_export[0]['mdelivery_number'];
		$pg_val['dlv_name'] = $data_export[0]['mdelivery'];
		$pg_val['dlv_exname'] = $this->config_basic['shopName'];
		$pg_val['dlv_invoiceday'] = $today.' '.$nowtime;
		$pg_val['dlv_sendname'] = $this->config_basic['shopName'];
		$pg_val['dlv_sendpost'] = $this->config_basic['companyZipcode'];
		$pg_val['dlv_sendaddr1'] = $this->config_basic['companyAddress'];
		$pg_val['dlv_sendaddr2'] = $this->config_basic['companyAddressDetail'];
		$pg_val['dlv_sendtel'] = $this->config_basic['companyPhone'];
		$pg_val['dlv_recvname'] = iconv('utf-8','euc-kr',$data_order['recipient_user_name']);
		$pg_val['dlv_recvpost'] = $data_order['recipient_zipcode'];
		$pg_val['dlv_recvaddr'] = iconv('utf-8','euc-kr',$data_order['recipient_address'].' '.$data_order['recipient_address_detail']);
		$pg_val['dlv_recvtel'] = $data_order['recipient_phone'];
		$pg_val['price'] = $data_order['settleprice'];

		require(dirname(__FILE__)."/../../pg/inicis/libs/INILib.php");
		/***************************************
		 * 2. INIpay50 클래스의 인스턴스 생성 *
		 ***************************************/
		$iniescrow = new INIpay50;

		/*********************
		 * 3. 지불 정보 설정 *
		 *********************/
		$iniescrow->SetField("inipayhome", dirname(__FILE__)."/../../pg/inicis");      // 이니페이 홈디렉터리(상점수정 필요)
		$iniescrow->SetField("tid",$pg_val['tid']); // 거래아이디
		$iniescrow->SetField("mid",$pg_val['mid']); // 상점아이디
	    /**************************************************************************************************
	     * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
	     * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
	     * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
	     * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
	     **************************************************************************************************/
		$iniescrow->SetField("admin",$pg_val['admin']); // 키패스워드(상점아이디에 따라 변경)
		$iniescrow->SetField("type", "escrow"); 				                    // 고정 (절대 수정 불가)
		$iniescrow->SetField("escrowtype", "dlv"); 				                    // 고정 (절대 수정 불가)
		$iniescrow->SetField("dlv_ip", getenv("REMOTE_ADDR")); // 고정
		$iniescrow->SetField("debug","true"); // 로그모드("true"로 설정하면 상세한 로그가 생성됨)

		$iniescrow->SetField("oid",$pg_val['oid']);
		$iniescrow->SetField("soid","1");
		$iniescrow->SetField("dlv_date",$pg_val['dlv_date']);
		$iniescrow->SetField("dlv_time",$pg_val['dlv_time']);
		$iniescrow->SetField("dlv_report",'I');
		$iniescrow->SetField("dlv_invoice",$pg_val['dlv_invoice']); // 송장번호
		$iniescrow->SetField("dlv_name",$pg_val['dlv_name']); // 배송등록자

		$iniescrow->SetField("dlv_excode",'9999'); // 배송 코드
		$iniescrow->SetField("dlv_exname",$pg_val['dlv_exname']); // 택배사 명
		$iniescrow->SetField("dlv_charge",'BH'); // 배송비 지급방법

		$iniescrow->SetField("dlv_invoiceday",$pg_val['dlv_invoiceday']); // 배송등록 확인일시
		$iniescrow->SetField("dlv_sendname",$pg_val['dlv_sendname']); // 송신자 이름
		$iniescrow->SetField("dlv_sendpost",$pg_val['dlv_sendpost']); // 송신자 우편번호
		$iniescrow->SetField("dlv_sendaddr1",$pg_val['dlv_sendaddr1']); // 송신자 주소1
		$iniescrow->SetField("dlv_sendaddr2",$pg_val['dlv_sendaddr2']); // 송신자 주소2
		$iniescrow->SetField("dlv_sendtel",$pg_val['dlv_sendtel']); // 송신자 전화번호

		$iniescrow->SetField("dlv_recvname",$pg_val['dlv_recvname']); // 수신자 이름
		$iniescrow->SetField("dlv_recvpost",$pg_val['dlv_recvpost']); // 수신자 우편번호
		$iniescrow->SetField("dlv_recvaddr",$pg_val['dlv_recvaddr']); // 수신자 주소
		$iniescrow->SetField("dlv_recvtel",$pg_val['dlv_recvtel']); // 수신자 전화번호

		$iniescrow->SetField("dlv_goodscode",$goodsCode); // 상품 코드
		$iniescrow->SetField("dlv_goods",$pg_val['dlv_goods']); // 상품명(필수)
		$iniescrow->SetField("dlv_goodscnt",$goodCnt); // 상품수량
		$iniescrow->SetField("price",$pg_val['price']); // 상품가격(필수)
		$iniescrow->SetField("dlv_reserved1",$reserved1);
		$iniescrow->SetField("dlv_reserved2",$reserved2);
		$iniescrow->SetField("dlv_reserved3",$reserved3);

		$iniescrow->SetField("pgn",$pgn);

		/*********************
		 * 3. 배송 등록 요청 *
		 *********************/
		$iniescrow->startAction();


		/**********************
		 * 4. 배송 등록  결과 *
		 **********************/
		 $tid        = $iniescrow->GetResult("tid"); 					// 거래번호
		 $resultCode = $iniescrow->GetResult("ResultCode");		// 결과코드 ("00"이면 지불 성공)
		 $resultMsg  = $iniescrow->GetResult("ResultMsg"); 			// 결과내용 (지불결과에 대한 설명)
		 $dlv_date   = $iniescrow->GetResult("DLV_Date");
		 $dlv_time   = $iniescrow->GetResult("DLV_Time");
	}


	public function insert_order_person()
	{
		$mode		= "cart";

		$this->load->model('personcartmodel');

		$payment = "";
		for($i=0; $i<count($_POST['payment']); $i++){
			$payment .= $_POST['payment'][$i]."|";
		}


		$insert_params['title'] 				= $_POST['title'];
		$insert_params['member_seq'] 			= $_POST['member_seq'];
		$insert_params['admin_memo'] 			= $_POST['admin_memo'];
		$insert_params['order_user_name'] 		= $_POST['order_user_name'];
		$insert_params['order_phone'] 			= $_POST['order_phone'][0]."-".$_POST['order_phone'][1]."-".$_POST['order_phone'][2];
		$insert_params['order_cellphone'] 		= $_POST['order_cellphone'][0]."-".$_POST['order_cellphone'][1]."-".$_POST['order_cellphone'][2];
		$insert_params['order_email'] 			= $_POST['order_email'];
		$insert_params['enuri'] 				= $_POST['enuri'];
		$insert_params['pay_type'] 				= $payment;
		$insert_params['total_price'] 			= str_replace(",", "", $_POST['total_price_temp']);
		$insert_params['admin_memo'] 			= $_POST['admin_memo'];
		$insert_params['regist_date'] 			= date("Y-m-d H:i:s");


		$this->db->insert('fm_person', $insert_params);
		$person_seq = $this->db->insert_id();


		return $person_seq;
	}

	/* 주문서의 과세금액, 비과세금액 반환, 배송비 반환 */
	public function get_order_prices_for_tax($order_seq,$order=''){

		$sales_config = config_load('order');

		$exempt_chk = 0;			// 비과세상품 종수
		$exempt_price = 0;			// 비과세상품 금액
		$exempt_shipping_price = 0; // 비과세상품 배송비

		if(!$order) $order = $this->ordermodel->get_order($order_seq);
		$items = $this->ordermodel->get_item($order_seq);


		$enuri = (int) $order['enuri'];
		$cash = (int) $order['cash'];
		$emoney = (int) $order['emoney'];
		$settle_price = (int) $order['settle_price'];

		//상품명 생성
		$item_name = $items[0]['goods_name'];
		if( (count($items) - 1) > 0){
			$item_name .= " 외 " . ( count($items)-1 ) . "건";
		}

		// 할인
		$tax_sale = 0; // 과세 할인
		$sale = $enuri;	// 비과세 할인

		// 이머니 적립금 미포함
		$etc_sale += $emoney;
		$etc_sale += $cash;

		// 세금계산서 이머니 적립금 포함 설정
		if($sales_config["sale_reserve_yn"] != "Y"){
			$tax_sale += $emoney;
		}
		if($sales_config["sale_emoney_yn"] != "Y"){
			$tax_sale += $cash;
		}

		foreach($items as $key=>$item){
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);

			if($item['tax']!="tax")
			{
				if($options) foreach($options as $k => $data){
					$exempt_price += (($data['price']-$data['member_sale'])*$data['ea']);
				}
				if($suboptions) foreach($suboptions as $k => $data){
					$exempt_price += (($data['price']-$data['member_sale'])*$data['ea']);
				}
			}else{
				if($options) foreach($options as $k => $data){
					$tax_price += (($data['price']-$data['member_sale'])*$data['ea']);
				}
				if($suboptions) foreach($suboptions as $k => $data){
					$tax_price += (($data['price']-$data['member_sale'])*$data['ea']);
				}
			}
		}

		$data_shipping = $this->get_shipping($order_seq);
		foreach($data_shipping as $data){
			$shipping_cost += (int) $data['tot_goods_shipping_cost'];
			$shipping_cost += (int) $data['shipping_cost'];
		}

		$result = array(
			'tax'=>$tax_price,
			'exempt'=>$exempt_price,
			'shipping_cost'=>$shipping_cost,
			'sale'=>$sale,
			'tax_sale'=>$tax_sale,
			'etc_sale'=>$etc_sale,
			'goods_name'=>$item_name
		);
		return $result;
	}

	/* 주문서의 과세금액, 부가세, 비과세금액 반환 */
	public function get_order_tax_prices($order_seq){

		$exempt_chk = 0;			// 비과세상품 종수
		$exempt_price = 0;			// 비과세상품 금액
		$exempt_shipping_price = 0; // 비과세상품 배송비

		$order = $this->ordermodel->get_order($order_seq);
		$items = $this->ordermodel->get_item($order_seq);

		foreach($items as $key=>$item){
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);
			$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);

			if($item['tax']!="tax"){

				if($options) foreach($options as $k => $data){
					$exempt_price += ($data['price']*$data['ea']);
				}

				if($suboptions) foreach($suboptions as $k => $data){
					$exempt_price += ($data['price']*$data['ea']);
				}

				$exempt_shipping_price += $item['basic_shipping_cost']+$item['add_shipping_cost'];
				$exempt_chk++;
			}
		}

		if(count($items)==$exempt_chk){
			$tax_type = "exempt";
		}else if($exempt_chk == 0){
			$tax_type = "tax";
		}else{
			$tax_type = "mix";
		}

		### TAX : EXEMPT
		$exempt_price	= $exempt_price + $exempt_shipping_price;
		if($tax_type=='mix'){
			$sum_price		= $order['settleprice']-$exempt_price;
			$tax_price		= round($sum_price/1.1);
			$comm_tax_mny	= $tax_price;
			$comm_vat_mny	= $sum_price - $tax_price;
			$comm_free_mny	= $exempt_price;
		}else if($tax_type=='exempt'){
			$comm_tax_mny	= 0;
			$comm_vat_mny	= 0;
			$comm_free_mny	= $order['settleprice'];
		}else{
			$tax_price		= round($order['settleprice']/1.1);
			$comm_tax_mny	= $tax_price;
			$comm_vat_mny	= $order['settleprice'] - $tax_price;
			$comm_free_mny	= 0;
		}

		return array(
			'comm_tax_mny' => $comm_tax_mny,	// 과세 금액
			'comm_vat_mny' => $comm_vat_mny,	// 부가세
			'comm_free_mny' => $comm_free_mny,	// 비과세 금액
		);

	}

	/* 특정 주문의 반품으로 인해  생성된 맞교환 주문번호 반환 */
	public function get_child_order_seq($order_seq){
		$result = array();
		$query = "select * from fm_order where orign_order_seq=?";
		$query = $this->db->query($query,array($order_seq));
		foreach($query->result_array() as $data){
			$result[] = $data['order_seq'];
		}
		return $result;
	}

	// 주문 배송지 가져오기
	public function get_shipping($order_seq,$shipping_seq=null){
/*
		if(basename(uri_string())=='order_prints'){
			$this->viewSummaryMode = 1;
		}
*/
		$this->db->where('order_seq',$order_seq);
		if($shipping_seq)	$this->db->where('shipping_seq',$shipping_seq);
		$this->db->order_by('shipping_seq','asc');
		$query = $this->db->get("fm_order_shipping");

		foreach($query->result_array() as $data){
			if(!$this->viewSummaryMode || $query->num_rows>1) $data['shipping_items'] = $this->get_shipping_item($order_seq,$data['shipping_seq']);

			$data['recipient_zipcode'] = explode('-',$data['recipient_zipcode']);
			$data['recipient_phone'] = explode('-',$data['recipient_phone']);
			$data['recipient_cellphone'] = explode('-',$data['recipient_cellphone']);
			foreach($data['shipping_items'] as $shipping_item){
				$data['tot_goods_shipping_cost'] += $shipping_item['goods_shipping_cost'];

				$data['shipping_items_cnt'] += $shipping_item['tot_goods_cnt'];
			}

			$result[] = $data;
		}

		return $result;
	}

	// 주문 배송지별 상품 가져오기
	public function get_shipping_item($order_seq, $shipping_seq, $goods_kind = ''){

		$bind	= array($shipping_seq,$order_seq,$shipping_seq);

		if	($goods_kind){
			$bind[]		= $goods_kind;
			$addWhere	= " and i.goods_kind = ? ";
		}

		$query = "select i.*,p.*,
		(select purchase_goods_name from fm_goods where goods_seq = i.goods_seq) as purchase_goods_name,
		ifnull((select sum(goods_shipping_cost) from fm_order_shipping_item where order_item_seq=i.item_seq AND shipping_seq=?),0) as goods_shipping_cost
		from fm_order_shipping_item p
		inner join fm_order_item i on (p.order_seq=i.order_seq and p.order_item_seq=i.item_seq)
		where p.order_seq=? and p.shipping_seq=? ".$addWhere."
		order by p.shipping_seq asc, p.shipping_item_seq asc
		";//(select goods_type from fm_goods where goods_seq = i.goods_seq) as goods_type,
		$query = $this->db->query($query,$bind);
		foreach($query->result_array() as $item){

			$item['shipping_item_option'] = $this->get_shipping_item_options($item['shipping_item_seq']);
			//$item['shipping_item_suboption'] = $this->get_shipping_item_suboptions($item['shipping_item_seq']);
			if($item['shipping_item_option']) foreach($item['shipping_item_option'] as $k=>$option){
				$item['shipping_item_option'][$k]['inputs']	= $this->get_input_for_option($option['item_seq'], $option['item_option_seq']);
				$item['shipping_item_option'][$k]['shipping_item_suboption'] = $this->get_suboption_for_option_by_shipping($option['item_seq'], $option['item_option_seq'], $option['shipping_seq']);
				$item['tot_goods_cnt']		+= count($item['shipping_item_option'][$k]['shipping_item_suboption']) + 1;
			}

			$result[] = $item;
		}
		return $result;
	}

	// 주문 배송지별 상품 옵션들 가져오기
	public function get_shipping_item_options($shipping_item_seq){
		$this->load->model('refundmodel');
		$query = $this->db->query("SELECT B.*,A.*,B.ea as order_ea FROM fm_order_shipping_item_option A
		inner join fm_order_item_option B on A.order_item_option_seq = B.item_option_seq
		WHERE shipping_item_seq=?",array($shipping_item_seq));
		$options = $query->result_array();

		if($options) foreach($options as $k=>$option){
			$options[$k]['step85'] = $this->refundmodel->get_refund_option_ea($option['shipping_seq'],$option['order_item_seq'],$option['order_item_option_seq']);
		}
		return $options;
	}

	// 주문 배송지별 상품 추가옵션들 가져오기
	public function get_shipping_item_suboptions($shipping_item_seq){
		$this->load->model('refundmodel');
		$query = $this->db->query("SELECT B.*,A.*,B.ea as order_ea FROM fm_order_shipping_item_option A
		inner join fm_order_item_suboption B on A.order_item_suboption_seq = B.item_suboption_seq
		WHERE shipping_item_seq=?",array($shipping_item_seq));
		$suboptions = $query->result_array();

		if($suboptions) foreach($suboptions as $k=>$suboption){
			$suboptions[$k]['step85'] = $this->refundmodel->get_refund_suboption_ea($suboption['shipping_seq'],$suboption['order_item_seq'],$suboption['order_item_suboption_seq']);
		}
		return $suboptions;
	}

	// 주문 배송지별 상품 옵션 가져오기
	public function get_shipping_item_option($shipping_seq, $item_option_seq){
		$query = $this->db->query("SELECT B.*,A.*,B.ea as order_ea FROM fm_order_shipping_item_option A
		inner join fm_order_item_option B on A.order_item_option_seq = B.item_option_seq
		WHERE shipping_seq=? and A.order_item_option_seq=?",array($shipping_seq,$item_option_seq));
		$option = $query->row_array();
		return $option;
	}

	// 주문 배송지별 상품 추가옵션 가져오기
	public function get_shipping_item_suboption($shipping_seq, $item_suboption_seq){
		$query = $this->db->query("SELECT B.*,A.*,B.ea as order_ea FROM fm_order_shipping_item_option A
		inner join fm_order_item_suboption B on A.order_item_suboption_seq = B.item_suboption_seq
		WHERE shipping_seq=? and A.order_item_suboption_seq=?",array($shipping_seq, $item_suboption_seq));
		$suboption = $query->row_array();
		return $suboption;
	}

	// 배송지별 상품 옵션 출고완료 개수 반환
	public function get_option_export_complete($order_seq,$shipping_seq,$order_item_seq,$order_item_option_seq){
		$query = $this->db->query("
			select sum(b.ea) as complete_ea
			from fm_goods_export as a
			inner join fm_goods_export_item b on (a.export_code=b.export_code)
			where a.order_seq=? and a.shipping_seq=? and b.item_seq=? and b.option_seq=?
		",array($order_seq,$shipping_seq,$order_item_seq,$order_item_option_seq));
		$result = $query->row_array();
		return $result['complete_ea'];
	}

	// 배송지별 상품 서브옵션 출고완료 개수 반환
	public function get_suboption_export_complete($order_seq,$shipping_seq,$order_item_seq,$order_item_suboption_seq){
		$query = $this->db->query("
			select sum(b.ea) as complete_ea
			from fm_goods_export as a
			inner join fm_goods_export_item b on (a.export_code=b.export_code)
			where a.order_seq=? and a.shipping_seq=? and b.item_seq=? and b.suboption_seq=?
		",array($order_seq,$shipping_seq,$order_item_seq,$order_item_suboption_seq));
		$result = $query->row_array();
		return $result['complete_ea'];
	}

	/* 배송지테이블 누락데이터 생성 */
	public function check_shipping_data($order_seq=null,$return=false){

		if(!$order_seq) $order_seq = $_GET['order_seq'];

		$sql = "
		select a.*
		from fm_order as a
		left join fm_order_shipping as b on a.order_seq=b.order_seq
		where b.shipping_seq is null
		";
		if($order_seq) {
			$sql .= " and a.order_seq = '{$order_seq}' ";
		}

		$orderCnt = 0;
		$result = mysql_query($sql);
		while($order=mysql_fetch_assoc($result)){

			unset($this->db->query_times);
			unset($this->db->queries);

			$this->db->trans_begin();

			/* fm_order_shipping */
			$data = array(
				'order_seq'						=> $order['order_seq'],
				'recipient_user_name'			=> $order['recipient_user_name'],
				'recipient_phone'				=> $order['recipient_phone'],
				'recipient_cellphone'			=> $order['recipient_cellphone'],
				'recipient_zipcode'				=> $order['recipient_zipcode'],
				'recipient_address_type'		=> $order['recipient_address_type'],
				'recipient_address'				=> $order['recipient_address'],
				'recipient_address_street'	=> $order['recipient_address_street'],
				'recipient_address_detail'	=> $order['recipient_address_detail'],
				'region'						=> $order['region'],
				'international_address'			=> $order['international_address'],
				'international_town_city'		=> $order['international_town_city'],
				'international_county'			=> $order['international_county'],
				'international_postcode'		=> $order['international_postcode'],
				'international_country'			=> $order['international_country'],
				'memo'							=> $order['memo'],
				'regist_date'					=> $order['regist_date'],
				'shipping_cost'					=> $order['shipping_cost'],
				'shipping_promotion_code_sale'	=> $order['shipping_promotion_code_sale'],
				'shipping_coupon_sale'			=> $order['coupon_sale'],
			);
			foreach($data as $k=>$v) if($v==null) $data[$k]='';
			$this->db->insert("fm_order_shipping",$data);
			$shipping_seq = $this->db->insert_id();

			/* fm_order_shipping_item */
			$sql = "select * from fm_order_item where order_seq=?";
			$query = $this->db->query($sql,$order['order_seq']);
			$order_items = $query->result_array();
			foreach($order_items as $order_item){

				/* fm_order_shipping_item */
				$data = array(
					'shipping_seq'					=> $shipping_seq,
					'order_seq'						=> $order_item['order_seq'],
					'order_item_seq'				=> $order_item['item_seq'],
					'goods_shipping_cost'			=> $order_item['goods_shipping_cost'],
				);
				$this->db->insert("fm_order_shipping_item",$data);
				$shipping_item_seq = $this->db->insert_id();

				/* fm_order_item_option */
				$sql = "select * from fm_order_item_option where order_seq=? and item_seq=?";
				$query = $this->db->query($sql,array($order_item['order_seq'],$order_item['item_seq']));
				$order_item_options = $query->result_array();
				foreach($order_item_options as $order_item_option){
					/* fm_order_shipping_item_option */
					$data = array(
						'shipping_item_seq'				=> $shipping_item_seq,
						'shipping_seq'					=> $shipping_seq,
						'order_seq'						=> $order_item_option['order_seq'],
						'order_item_seq'				=> $order_item_option['item_seq'],
						'order_item_option_seq'			=> $order_item_option['item_option_seq'],
						'ea'							=> $order_item_option['ea'],
					);
					$this->db->insert("fm_order_shipping_item_option",$data);
				}

				/* fm_order_item_suboption */
				$sql = "select * from fm_order_item_suboption where order_seq=? and item_seq=?";
				$query = $this->db->query($sql,array($order_item['order_seq'],$order_item['item_seq']));
				$order_item_suboptions = $query->result_array();
				foreach($order_item_suboptions as $order_item_suboption){
					/* fm_order_shipping_item_option */
					$data = array(
						'shipping_item_seq'				=> $shipping_item_seq,
						'shipping_seq'					=> $shipping_seq,
						'order_seq'						=> $order_item_suboption['order_seq'],
						'order_item_seq'				=> $order_item_suboption['item_seq'],
						'order_item_suboption_seq'		=> $order_item_suboption['item_suboption_seq'],
						'ea'							=> $order_item_suboption['ea'],
					);
					$this->db->insert("fm_order_shipping_item_option",$data);
				}
			}

			/* fm_goods_export */
			$data = array(
				'shipping_seq' => $shipping_seq,
            );
			$this->db->where('order_seq', $order['order_seq']);
			$this->db->update('fm_goods_export', $data);

			/* fm_order_refund_item */
			$sql = "select * from fm_order_refund where order_seq='{$order['order_seq']}'";
			$res = mysql_query($sql);
			while($refund=mysql_fetch_assoc($res)){
				$data = array(
					'shipping_seq' => $shipping_seq,
	            );
				$this->db->where('refund_code', $refund['refund_code']);
				$this->db->update('fm_order_refund_item', $data);
			}

			if ($this->db->trans_status() === FALSE)
			{
			    $this->db->trans_rollback();
			    echo 'error';
				exit;
			}
			else
			{
				$orderCnt++;
			    $this->db->trans_commit();
			}

		}

		$exportCnt=0;
		$sql = "select a.*, b.shipping_seq as order_shipping_seq from fm_goods_export a inner join fm_order_shipping b on a.order_seq=b.order_seq where a.shipping_seq is null group by a.export_code";
		$query = $this->db->query($sql);
		$exports = $query->result_array();
		foreach($exports as $export){

			$exportCnt++;

			unset($this->db->query_times);
			unset($this->db->queries);

			$data = array(
				'shipping_seq' => $export['order_shipping_seq'],
            );
			$this->db->where('export_code', $export['export_code']);
			$this->db->update('fm_goods_export', $data);
		}

		if($return){
			return array(
				"order" => $orderCnt,
				"export" => $exportCnt,
			);
		}else{
			echo json_encode(array(
				"order" => $orderCnt,
				"export" => $exportCnt,
			));
		}

	}

	public function get_order_catalog_query( $_PARAM = array('list') ){

		$page		= (trim($_PARAM['page'])) ? trim($_PARAM['page']) : 1;
		$nperpage	= 20;
		$limit_s	= ($page - 1) * $nperpage;
		$limit_e	= $nperpage;

		$record = "";

		//유입매체
		$sitemarketplaceloop = sitemarketplace($_PARAM['sitemarketplace'], 'image', 'array');

		if($_PARAM['header_search_keyword']) {
			$_PARAM['keyword'] = $_PARAM['header_search_keyword'];
			/*
			$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
			$_PARAM['regist_date'][1] = date('Y-m-d');
			*/
		}

		### 2012-08-10
		if($_PARAM['mode']=='bank'){
			$_PARAM['regist_date'][0] = date("Y-m-d", mktime(0,0,0,date("m")-1, date("d"), date("Y")));
			$_PARAM['regist_date'][1] = date('Y-m-d');
			$_PARAM['chk_step'][15] = 1;
			$where_order[] = " ord.settleprice >= '".$_PARAM['sprice']."' ";
			$where_order[] = " ord.settleprice <= '".$_PARAM['eprice']."' ";
		}

		// 검색어
		if( $_PARAM['keyword'] ){

			$keyword_type = preg_replace("/[^a-z_]/i","",trim($_PARAM['keyword_type']));
			$keyword = str_replace("'","\'",trim($_PARAM['keyword']));
			
			if($keyword_type){
				$arr_field = array(
					'order_seq' => 'ord.order_seq',
					'order_user_name' => 'ord.order_user_name',
					'depositor' => 'ord.depositor',
					'userid' => 'mem.userid'
				);

				$where[] = $arr_field[$keyword_type]." = '" . $keyword . "'";
			// 검색어가 주문번호 일 경우
			}/*else if(preg_match('/^([0-9]{13,19})$/',$keyword)  && (strlen($keyword) == 19 || strlen($keyword) == 13)){
				$where[] = "ord.order_seq = '" . $keyword . "'";

			// 검색어가 출고번호 일 경우
			}*/else if(preg_match('/^(D[0-9]{14})$/',$keyword)){
				$where[] = "ord.order_seq = (SELECT order_seq FROM fm_goods_export WHERE export_code = '" . $keyword . "')";
			// 검색어가 반품번호 일 경우
			}else if(preg_match('/^(R[0-9]{12})$/',$keyword)){
				$where[] = "ord.order_seq = (SELECT order_seq FROM fm_order_return WHERE return_code = '" . $keyword . "')";
			// 검색어가 환불번호 일 경우
			}else if(preg_match('/^(C[0-9]{12})$/',$keyword)){
				$where[] = "ord.order_seq = (SELECT order_seq FROM fm_order_refund WHERE refund_code = '" . $keyword . "')";
			}else{

				if(preg_match('/^([0-9]+)$/',$keyword)){
					$add_goodsseq_where = " OR goods_seq = '" . $keyword . "' ";
				}else{
					$add_goodsseq_where = "";
				}

				$where[] = "
				(
					ord.order_seq = '" . $keyword . "' OR
					mem.user_name like '%" . $keyword . "%' OR
					bus.bname like '%" . $keyword . "%' OR
					order_user_name  like '%" . $keyword . "%' OR
					depositor like '%" . $keyword . "%' OR
					order_email like '%" . $keyword . "%' OR
					order_phone like '%" . $keyword . "%' OR
					order_cellphone like '%" . $keyword . "%' OR
					userid like '%" . $keyword . "%' OR
					ifnull(linkage_mall_order_id,'') LIKE '%" . $keyword . "%' OR
					EXISTS (
						SELECT order_seq FROM fm_order_shipping WHERE order_seq = ord.order_seq and (
							recipient_phone LIKE '%" . $keyword . "%' OR
							recipient_cellphone LIKE '%" . $keyword . "%' OR
							recipient_user_name LIKE '%" . $keyword . "%')
					) OR
					EXISTS (
						SELECT
							order_seq
						FROM fm_order_item WHERE order_seq = ord.order_seq and (
							goods_name LIKE '%" . $keyword . "%' OR
							goods_code = '" . $keyword . "' 
							" . $add_goodsseq_where . "
							)
					) OR
					EXISTS (
						SELECT order_seq FROM fm_goods_export WHERE order_seq = ord.order_seq and (
							delivery_number LIKE '%" . $keyword . "%' OR
							international_delivery_no LIKE '%" . $keyword . "%')
					)
				)
				";
			}

		}


		// 주문일
		$date_field = $_PARAM['date_field'] ? $_PARAM['date_field'] : 'regist_date';
		if($_PARAM['regist_date'][0]){
			$where_order[] = "ord.".$date_field." >= '".$_PARAM['regist_date'][0]." 00:00:00'";
		}
		if($_PARAM['regist_date'][1]){
			$where_order[] = "ord.".$date_field." <= '".$_PARAM['regist_date'][1]." 23:59:59'";
		}

		// 주문상태
		if( $_PARAM['chk_step'] ){
			unset($arr);
			foreach($_PARAM['chk_step'] as $key => $data){
				$arr[] = "ord.step = '".$key."'";
				if( $key == 25 ) $settle_yn = 'y';
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}

		//상품에서 조회
		if($_PARAM['goods_seq']){
			$goods_seq = str_replace("'","\'",$_PARAM['goods_seq']);
			$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
			$_PARAM['regist_date'][1] = date('Y-m-d');
			$_PARAM['chk_step'][75] = 1;
			$arr[] = "ord.step = '75'";
			$where_order[] = "(".implode(' OR ',$arr).")";
			$goods_seq_field = "";
			$goodsviewjoin = "LEFT JOIN fm_order_item orditm ON orditm.order_seq=ord.order_seq ";
			$where_order[]  = " orditm.goods_seq = '".$goods_seq."' ";

		}else{
			$goodsviewjoin = "";
			$goods_seq_field = "";
		}

		// 결제수단
		if( $_PARAM['payment'] ){
			unset($arr);
			foreach($_PARAM['payment'] as $key => $data){
				// 카카오페이 검색방식 변경 :: 2015-02-26 lwh
				if( $key == 'kakaopay' ){
					$arr[] = "ord.pg = '".$key."'";
				} else if ( $key == 'card' ){
					$arr[] = "ord.payment = '".$key."' and ord.pg is NULL";
				} else{
					$arr[] = "ord.payment = '".$key."'";
				}

				if( in_array($key,array('virtual','account')) ){
					$arr[] = "ord.payment = 'escrow_".$key."'";
				}
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}


		// 주문유형
		if( $_PARAM['ordertype'] ){
			unset($arr);
			foreach($_PARAM['ordertype'] as $key => $data){

				if($key == "personal"){
					$arr[] = " (person_seq is not null and person_seq <> '') ";
				}
				if($key == "admin"){
					$arr[] = " (admin_order is not null and admin_order <> '') ";
				}
				if($key == "change"){
					$arr[] = " (orign_order_seq is not null and orign_order_seq <> '') ";
				}
				if($key == "gift"){
					$arr[] = " EXISTS (select 'o' from fm_order_item where order_seq = ord.order_seq and goods_type = 'gift' limit 1) ";
				}
			}
			$where[] = "(".implode(' OR ',$arr).")";
		}

		###
		$where[] = "hidden = 'N'";

		// 판매환경
		if( $_PARAM['sitetype'] ){
			unset($arr);
			foreach($_PARAM['sitetype'] as $key => $data){
				$arr[] = "ord.sitetype = '".$key."'";
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}

		// 유입매체
		if( $_PARAM['marketplace'] ){
			unset($arr);
			foreach($_PARAM['marketplace'] as $key => $data){
				if($key == 'etc'){
					foreach($sitemarketplaceloop as $marketplace => $tmp){
						if($marketplace != 'etc') $where_marketplace[] = "ord.marketplace != '$marketplace'";
					}
					if($where_marketplace){
						$arr[] = "(".implode(' AND ',$where_marketplace).")";
					}
					$arr[] = "ord.marketplace is null";
				}else{
					$arr[] = "ord.marketplace = '".$key."'";
				}
			}
			$where_order[] = "(".implode(' OR ',$arr).")";
		}

		// 오더함
		if( $_PARAM['search_ordered'] ){
			$where_order[] = "(order_seq in (select order_seq from fm_order_item_option where ordered=1) OR order_seq in (select order_seq from fm_order_item_suboption where ordered=1))";
		}

		// 품절
		if( $_PARAM['search_runout'] ){
			$where_order[] = "(order_seq in (select order_seq from fm_order_item_option where runout=1) OR order_seq in (select order_seq from fm_order_item_suboption where runout=1))";
		}

		// 맞교환
		if( $_PARAM['search_change'] ){
			$where_order[] = "orign_order_seq !=''";
		}

		### referer
		if	($_PARAM['referer']){
			$where_order[]	= " (IF(rg.referer_group_no>0, rg.referer_group_name, IF(LENGTH(ord.referer)>0,'기타','직접입력'))) = '" . $_PARAM['referer'] . "' ";
		}

		/**
		$this->db->order_by("seq","desc");
		$this->db->where('gb', 'ORDER');
		$query = $this->db->get("fm_exceldownload");
		foreach ($query->result_array() as $row){
			$row['count'] = count(explode("|",$row['item']));
			$loop[] = $row;
		}
		**/

		### 2014-05-29
		if($_PARAM['linkage_mall_code'] || $_PARAM['not_linkage_order'] || $_PARAM['etc_linkage_order']){
			$arr = array();

			if($_PARAM['not_linkage_order']){
				$arr[] = "(linkage_mall_code is null or linkage_mall_code = '')";
			}
			if($_PARAM['linkage_mall_code']){
				$arr[] = "linkage_mall_code in ('".implode("','",$_PARAM['linkage_mall_code'])."')";
			}
			if($_PARAM['etc_linkage_order']){
				$this->load->model('openmarketmodel');
				$linkage_malldata		= $this->openmarketmodel->get_linkage_mall();
				$linkage_malldata		= $this->openmarketmodel->sort_linkage_mall($linkage_malldata);
				$search_mall_code = array();
				foreach($linkage_malldata as $k => $data){
					if	($data['default_yn'] == 'Y'){
						$search_mall_code[] = $data['mall_code'];
					}
				}
				for($i=0;$i<count($search_mall_code);$i++){
					if($i<10) unset($search_mall_code[$i]);
				}
				$arr[] = "linkage_mall_code in ('".implode("','",$search_mall_code)."')";
			}

			$where_order[] = "(".implode(' OR ',$arr).")";
		}
		if($_PARAM['linkage_mall_order_id']){
			$where[] = "linkage_mall_order_id in ('".implode("','",$_PARAM['linkage_mall_order_id'])."')";
		}

		if($where_order){
			$str_where_order = " AND " . implode(' AND ',$where_order) ;
		}

		if($where){
			$str_where_order .= " and " .implode(' AND ',$where);
		}
		$sort = "ORDER BY step ASC, regist_date DESC";

		if	($_PARAM['nolimit'] != 'y')
			$addLimit	= " LIMIT {$limit_s}, {$limit_e} ";

		if( $where_order || $where ){
			$key = get_shop_key();
			if	($_PARAM['query_type'] == 'summary'){
				$query	= "
				SELECT
				count(*) as cnt,
				sum(ord.settleprice) as total_settleprice
				FROM
				fm_order ord
				".$goodsviewjoin."
				LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
				LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
				LEFT JOIN fm_referer_group rg ON ord.referer_domain = rg.referer_group_url 
				WHERE ord.step={$_PARAM['end_step']}  {$str_where_order}
				";
			}elseif	($_PARAM['query_type'] == 'total_record'){
				$query	= "
				SELECT
				count(*) as cnt,
				sum(ord.settleprice) as total_settleprice
				FROM
				fm_order ord
				".$goodsviewjoin."
				LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
				LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
				LEFT JOIN fm_referer_group rg ON ord.referer_domain = rg.referer_group_url 
				WHERE ord.step!=0  {$str_where_order}
				";
			}else{
				$query	= "
				SELECT
				ord.*,
				(select count(item_seq) from fm_order_item where order_seq = ord.order_seq and goods_type = 'gift') gift_cnt,
				(SELECT goods_name FROM fm_order_item WHERE order_seq=ord.order_seq LIMIT 1) goods_name,
				(SELECT count(item_seq) FROM fm_order_item WHERE order_seq=ord.order_seq) item_cnt,
				(SELECT sum(ea) FROM fm_order_item_option WHERE order_seq=ord.order_seq) opt_ea,
				(SELECT sum(ea) FROM fm_order_item_suboption WHERE order_seq=ord.order_seq) sub_ea,				
				shipping.shipping_seq,
				shipping.recipient_user_name shipping_recipient_user_name,
				shipping.recipient_user_name recipient_user_name,
				shipping.recipient_phone recipient_phone,
				shipping.recipient_cellphone recipient_cellphone,
				shipping.recipient_zipcode recipient_zipcode,
				shipping.recipient_address recipient_address,
				shipping.recipient_address_street recipient_address_street,
				shipping.recipient_address_detail recipient_address_detail,
				shipping.region region,
				shipping.international_address international_address,
				shipping.international_town_city international_town_city,
				shipping.international_county international_county,
				shipping.international_postcode international_postcode,
				shipping.international_country international_country,
				shipping.memo memo,
				(SELECT count(shipping_seq) FROM fm_order_shipping WHERE order_seq=ord.order_seq) shipping_cnt,
				(
					SELECT userid FROM fm_member WHERE member_seq=ord.member_seq
				) userid,
				(
					SELECT AES_DECRYPT(UNHEX(email), '{$key}') as email FROM fm_member WHERE member_seq=ord.member_seq
				) mbinfo_email,
				(
					SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq
				) group_name,
				mem.rute as mbinfo_rute,
				mem.user_name as mbinfo_user_name,
				bus.business_seq as mbinfo_business_seq,
				bus.bname as mbinfo_bname,
				ord.referer, ord.referer_domain,
				IF(rg.referer_group_no>0, rg.referer_group_name, IF(LENGTH(ord.referer)>0,'기타','직접입력')) as referer_name
				FROM
				fm_order ord
				".$goodsviewjoin."
				LEFT JOIN fm_order_shipping shipping ON shipping.shipping_seq=(select min(shipping_seq) from fm_order_shipping where order_seq=ord.order_seq)
				LEFT JOIN fm_member mem ON mem.member_seq=ord.member_seq
				LEFT JOIN fm_member_business bus ON bus.member_seq=mem.member_seq
				LEFT JOIN fm_referer_group rg ON ord.referer_domain = rg.referer_group_url 
				WHERE ord.step!=0 {$str_where_order} {$sort}".$addLimit;
			}

			return $this->db->query($query,$bind);
		}
	}

	//쇼셜쿠폰상품의 취소설정 @2013-10-22
	public function order_insert_socialcp_cancel($goodSeq, $order_seq, $item_seq) {
		$this->db->where('goods_seq', $goodSeq);
		$query = $this->db->get('fm_goods_socialcp_cancel');
		foreach($query->result_array() as $data){
			$params = filter_keys($data, $this->db->list_fields('fm_goods_socialcp_cancel'));
			unset($params['seq'],$params['goods_seq'],$params['regist_date']);
			$params['order_seq'] = $order_seq;
			$params['item_seq'] = $item_seq;
			$result = $this->db->insert('fm_order_socialcp_cancel', $params);
		}
		return $result;
	}

	// 쿠폰상품 취소 정체 추출
	public function get_order_coupon_cancel($order_seq, $item_seq){
		$sql	= "select * from fm_order_socialcp_cancel
					where order_seq = '".$order_seq."' and item_seq = '".$item_seq."' ";
		$query	= $this->db->query($sql);
		return $query->result_array();
	}

	public function get_gift_event($gift_categorys, $gift_goods, $gift_loop, $total_price){
		
		$this->load->model('categorymodel');

		$today = date("Y-m-d");
		$sql = "SELECT * FROM fm_gift WHERE gift_gb = 'order' AND display = 'y' AND start_date <= '{$today}' AND end_date >= '{$today}'";

		$query = $this->db->query($sql);
		
		$gift_cnt = 0;
		foreach ($query->result_array() as $v){
			unset($g_result);

			if($v['goods_rule']=='all'){
				$g_result = gift_order_check_all($v['gift_seq'], $v['gift_rule'], $total_price, $gift_loop);			
			}else if($v['goods_rule']=='category'){
				$category_check = false;
				foreach($gift_categorys as $data){
				$category_codes = $this->categorymodel->split_category($data);

					foreach($category_codes as $category_code){

						$sql = "SELECT count(*) as cnt FROM fm_gift_choice WHERE category_code = '{$category_code}' and gift_seq = '".$v['gift_seq']."'";

						$query = $this->db->query($sql);
						$boolen = $query->result_array();

						if($boolen[0]["cnt"] > 0){
							$category_check = true;
						}
					}

				}
				if($category_check){
					$g_result = gift_order_check_category($v['gift_seq'], $v['gift_rule'], $total_price, $gift_loop);
				}

			}else if($v['goods_rule']=='goods'){
				$goods_check = false;
				foreach($gift_goods as $data){
					$sql = "SELECT count(*) as cnt FROM fm_gift_choice WHERE goods_seq = '{$data}' and gift_seq = '{$v['gift_seq']}'";
					$query = $this->db->query($sql);

					$boolen = $query->result_array();
					if($boolen[0]["cnt"] > 0){
						$goods_check = true;
					}
				}
				if($goods_check){

					$g_result = gift_order_check_goods($v['gift_seq'], $v['gift_rule'], $total_price, $gift_loop);
				}
			}


			//사은품 재고 체크
			$gift_count = count($g_result['goods']);
			for($i=0; $i<$gift_count; $i++){
				$sql	= "SELECT a.goods_view, a.goods_status, b.stock FROM fm_goods a, fm_goods_supply b WHERE a.goods_seq = b.goods_seq and a.goods_seq = '".$g_result['goods'][$i]."' limit 1";
				$query	= $this->db->query($sql);
				$info	= $query->result_array();
				$ea		= $info[0]['stock'];
				$display = $info[0]['goods_view'];
				$goods_status = $info[0]['goods_status'];
				if($ea < 1 || $display != "look" || $goods_status != "normal"){
					unset($g_result['goods'][$i]);
				}
			}

			
			if( count($g_result['goods'])>0 ){
					$gifts['gift_seq']	= $v['gift_seq'];
					$gifts['title']		= $v['title'];
					$gifts['gift_contents']		= $v['gift_contents'];
					$gifts['gift_rule']		= $v['gift_rule'];
					$gifts['goods']		= $g_result['goods'];
					$gifts['ea']		= $g_result['ea'];
					$gloop[]			= $gifts;
					$gift_cnt++;
			}
			
		}
		
		$result['gift_cnt'] = $gift_cnt;
		$result['gloop'] = $gloop;

		return $result;
		
	}

	// 주문 적립금,에누리,이머니사용액 상품옵션/추가옵션 별로 나눔
	public function update_unit_emoney_cash_enuri($order_seq)
	{
		$tot = 0;
		$param = array();
		$data_order = $this->get_order($order_seq);
		$result_option = $this->get_item_option($order_seq);
		$result_suboption = $this->get_item_suboption($order_seq);
		
		foreach($result_option as $data_option){
			$tot += (int) $data_option['price']* (int) $data_option['ea'];
			$param[] = array(
				'type' => 'option',
				'seq' => $data_option['item_option_seq'],
				'unit_price' => $data_option['price'],
				'ea' => $data_option['ea']
			);
		}

		foreach($result_suboption as $data_suboption){
			$tot += (int) $data_suboption['price']* (int) $data_suboption['ea'];
			$param[] = array(
				'type' => 'suboption',
				'seq' => $data_suboption['item_suboption_seq'],
				'unit_price' => $data_suboption['price'],
				'ea' => $data_suboption['ea']
			);
		}

		if($data_order['emoney']>0)						
			$param = $this->calculate_allotment($data_order['emoney'],$tot,$param,'emoney');

		if($data_order['cash']>0)						
			$param = $this->calculate_allotment($data_order['cash'],$tot,$param,'cash');

		if($data_order['enuri']>0)						
			$param = $this->calculate_allotment($data_order['enuri'],$tot,$param,'enuri');
		
		foreach($param as $data){
			$bind = array();
			if($data['type']=='option'){
				$bind[] = (int) $data['unit_emoney'];
				$bind[] = (int) $data['unit_cash'];
				$bind[] = (int) $data['unit_enuri'];
				$bind[] = (int) $data['seq'];
				$query = "update fm_order_item_option set unit_emoney=?,unit_cash=?,unit_enuri=? where item_option_seq=?";
				$this->db->query($query,$bind);
			}else if($data['type']=='suboption'){
				$bind[] = (int) $data['unit_emoney'];
				$bind[] = (int) $data['unit_cash'];
				$bind[] = (int) $data['unit_enuri'];
				$bind[] = (int) $data['seq'];
				$query = "update fm_order_item_suboption set unit_emoney=?,unit_cash=?,unit_enuri=? where item_suboption_seq=?";
				$this->db->query($query,$bind);
			}
		}		
	}	
	
	// 에누리,캐쉬,적립금 사용액 상품별 계산
	public function calculate_allotment($emoney,$tot,$param,$field){		
		foreach($param as $k => $data){
			$tmp_emoney = $emoney * $data['unit_price'] * $data['ea'] / $tot;			
			$data['unit_'.$field] = floor($tmp_emoney / $data['ea']);
			$param[$k] = $data;
		}
		return $param;
	}

	// 세금계산서 증빙금액 업데이트
	public function update_tax_sales($order_seq) {

		$this->load->model('salesmodel');

		$order_tax_prices = $this->get_order_prices_for_tax($order_seq);
		$data_tax = $this->salesmodel->tax_calulate(
		$order_tax_prices["tax"],
		$order_tax_prices["exempt"],
		$order_tax_prices["shipping_cost"],
		$order_tax_prices["sale"],
		$order_tax_prices["tax_sale"]);

		$data_etc = $this->salesmodel->tax_calulate(
		$order_tax_prices["tax"],
		$order_tax_prices["exempt"],
		$order_tax_prices["shipping_cost"],
		$order_tax_prices["sale"],
		$order_tax_prices["etc_sale"]);

		$taxparams = array();

		// 과세 매출증빙 저장
		if( $data_etc['surtax'] > 0 ){
			$qry = "select seq from fm_sales where tstep=1 and typereceipt=1 and surtax > 0 and order_seq = '".$order_seq."'";
			$query = $this->db->query($qry);
			list($tax_info) = $query->result_array();

			if (!$tax_info) return false;
			$taxparams['seq']			= $tax_info['seq'];
			$taxparams['price']			= (int) $data_etc['supply'] + (int) $data_etc['surtax'];
			$taxparams['supply']		= (int) $data_etc['supply'];
			$taxparams['surtax']		= (int) $data_etc['surtax'];
			$taxparams['tax_price']		= (int) $data_tax['supply'] + (int) $data_tax['surtax'];
			$taxparams['tax_supply']	= (int) $data_tax['supply'];
			$taxparams['tax_surtax']	= (int) $data_tax['surtax'];
			$this->salesmodel->sales_modify($taxparams);
		}

		// 비과세 매출증빙 저장
		if( $data_etc['supply_free'] > 0 ){
			$qry = "select seq from fm_sales where tstep=1 and typereceipt=1 and surtax = 0 and order_seq = '".$order_seq."'";
			$query = $this->db->query($qry);
			list($tax_info) = $query->result_array();

			if (!$tax_info) return false;
			$taxparams['seq']			= $tax_info['seq'];
			$taxparams['price']			= (int) $data_etc['supply_free'];
			$taxparams['supply']		= (int) $data_etc['supply_free'];
			$taxparams['surtax']		= 0;
			$taxparams['tax_price']		= (int) $data_tax['supply_free'];
			$taxparams['tax_supply']	= (int) $data_tax['supply_free'];
			$taxparams['tax_surtax']	= 0;
			$this->salesmodel->sales_modify($taxparams);
		}
	}

	function get_usable_emoney($total_price,$settle_price, $member_emoney){
		$reserve_use = true;
		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');

		$returnInfo = array();
		$err_reserve = "";

		// 적립금 사용 단위
		if ($reserves['emoney_using_unit'] > 0) {
			if ($reserves['emoney_using_unit']==1) {
				$using_unit = $use_emoney%10;
			} else if ($reserves['emoney_using_unit']==2) {
				$using_unit = $use_emoney%100;
			} else if ($reserves['emoney_using_unit']==3) {
				$using_unit =$use_emoney%1000;
			}
		}

		$usable_emoney = $member_emoney;

		if($usable_emoney > $settle_price){
			$usable_emoney = $settle_price;
		}

		if($member_emoney >= $reserves['emoney_use_limit'] ){
			if($reserves['max_emoney_policy'] == 'percent_limit' && $reserves['max_emoney_percent']){
				$max_emoney = (int) ($total_price * $reserves['max_emoney_percent'] / 100);
			}else if($reserves['max_emoney_policy'] == 'price_limit' && $reserves['max_emoney']){
				$max_emoney = (int) $reserves['max_emoney'];
			}

			if($max_emoney > $settle_price) $max_emoney = $settle_price;

			if($usable_emoney < $reserves['min_emoney']){
				$usable_emoney = 0;
				$err_reserve = "적립금은  최소 ".number_format($reserves['min_emoney'])."원부터 사용가능 합니다.";
			}
			if($usable_emoney > $max_emoney && $reserves['max_emoney_policy'] != 'unlimit'){
				$usable_emoney = $max_emoney;
			}
		}

		if($reserves['emoney_use_limit'] > $member_emoney){
			$usable_emoney = 0;
			$err_reserve = number_format($reserves['emoney_use_limit'])."원 이상 적립하여야 합니다.";
		}

		if($reserves['emoney_price_limit'] > $total_price){
			$usable_emoney = 0;
			$err_reserve = "상품을 ".number_format($reserves['emoney_price_limit'])."원 이상 사야 합니다.";
		}

		$returnInfo['emoney'] = $usable_emoney;
		if ($err_reserve) {
			$returnInfo['err_reserve'] = $err_reserve;
		}

		return $returnInfo;
	}

	function get_usable_cash($total_price,$settle_price, $member_cash){
		$reserve_use = true;

		$usable_cash = $member_cash;

		if( $usable_cash > $settle_price ){
			$usable_cash = $settle_price;
		}

		return $usable_cash;
	}
}

/* End of file ordermodel.php */
/* Location: ./app/models/ordermodel.php */