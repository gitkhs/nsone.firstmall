<?php
class cartmodel extends CI_Model {

	/**
	table 명 변수선언
	*/
	var $tb_cart = "fm_cart";
	var $tb_cart_option = "fm_cart_option";
	var $tb_cart_suboption = "fm_cart_suboption";
	var $tb_cart_input = "fm_cart_input";

	/**
	 @input
	  -$prefix : person 개인결제 , 없을경우 장바구니
	 */
	public function change_cart($prefix='')
	{
		$this->tb_cart = "fm_".$prefix."_cart";
		$this->tb_cart_option = "fm_".$prefix."_cart_option";
		$this->tb_cart_suboption = "fm_".$prefix."_cart_suboption";
		$this->tb_cart_input = "fm_".$prefix."_cart_input";
	}

	/**
	 @input
	  -$cart_seq :장바구니 일련번호
	 @return
	  -$returnArr : 장바구니 정보
	 */
	public function get_cart($cart_seq) {
		$query = "
			SELECT *
			FROM ".$this->tb_cart."
			WHERE cart_seq=?";
		$query = $this->db->query($query,array($cart_seq));
		list($returnArr) = $query->result_array();
		$query->free_result();
		return $returnArr;
	}

	/**
	 @input
	  -$mode :장바구니 구분값(cart 장바구니 ,direct 바로구매,choice 선택구매)
	 @return
	  -$returnArr : 장바구니 정보
	 */
	public function get_cart_list($mode='') {
		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']){
			$where_query[] = "member_seq = ?";
			$where_arr[] = $this->userInfo['member_seq'];
		}else{
			$where_query[] = "session_id = ?";
			$where_arr[] = $session_id;
		}

		if($mode!=''){
			$where_query[] = "distribution = ?";
			$where_arr[] = $mode;
		}

		$query = "SELECT * FROM ".$this->tb_cart." WHERE ".implode(' AND ',$where_query) ." order by cart_seq desc";
		$query = $this->db->query($query,$where_arr);
		foreach($query->result_array() as $row) $returnArr[] = $row;
		$query->free_result();
		return $returnArr;
	}

	/**
	@return
	 -장바구니 담긴 수
	*/
	public function get_cart_count() {
		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']){
			$where_query[] = "member_seq = ?";
			$where_arr[] = $this->userInfo['member_seq'];
		}else{
			$where_query[] = "session_id = ?";
			$where_arr[] = $session_id;
		}
		$query = "SELECT count(*) as cnt FROM ".$this->tb_cart_option." cart_opt
		left join ".$this->tb_cart." cart on cart.cart_seq = cart_opt.cart_seq
		WHERE cart.distribution = 'cart' and ".implode(' AND ',$where_query) ." order by cart.cart_seq desc";
		$query = $this->db->query($query,$where_arr);
		$row = $query->result_array();

		return $row[0]['cnt'];
	}

	/**
	 @input
	  -$cart_seq : 장바구니 일련번호
	 @return
	  -$returnArr : 장바구니정보, 상품 옵션 정보
	*/
	public function get_cart_option($cart_seq) {
		$this->load->model('goodsmodel');
		$returnArr = array();
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.optioncode1,goods.optioncode2,goods.optioncode3,goods.optioncode4,goods.optioncode5,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where option_seq=goods.option_seq and goods_seq=goods.goods_seq) supply_price
			FROM ".$this->tb_cart_option." cart,fm_goods_option goods
			WHERE cart.option1=goods.option1
				AND cart.option2=goods.option2
				AND cart.option3=goods.option3
				AND cart.option4=goods.option4
				AND cart.option5=goods.option5
				AND goods.goods_seq =
				(
					select goods_seq from ".$this->tb_cart." where cart_seq=?
				)
				AND cart.cart_seq=?
			ORDER BY cart.cart_option_seq DESC";
		$query = $this->db->query($query,array($cart_seq,$cart_seq));
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		$query->free_result();
		return $returnArr;
	}

	/**
	@input
	 -$cart_seq : 장바구니 일련번호
	@return
	 -$returnArr : 장바구니정보, 상품 추가옵션 정보
	*/
	public function get_cart_suboption($cart_seq) {
		$returnArr = array();
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,goods.suboption_code,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where suboption_seq=goods.suboption_seq and goods_seq=goods.goods_seq) supply_price
			FROM ".$this->tb_cart_suboption." cart,fm_goods_suboption goods
			WHERE cart.suboption=goods.suboption AND cart.suboption_title=goods.suboption_title
				AND goods.goods_seq =
				(
					select goods_seq from ".$this->tb_cart." where cart_seq=?
				)
				AND cart.cart_seq=?
			ORDER BY cart.cart_suboption_seq DESC";
		$query = $this->db->query($query,array($cart_seq,$cart_seq));
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		$query->free_result();
		return $returnArr;
	}

	/**
	@input
	 -$cart_seq : 장바구니 일련번호
	@return
	 -$returnArr : 장바구니정보, 상품 추가입력사항 정보
	*/
	public function get_cart_input($cart_seq) {
		$returnArr = "";
		$query = "
			SELECT *
			FROM ".$this->tb_cart_input."
			WHERE cart_seq=?
			ORDER BY cart_input_seq DESC";
		$query = $this->db->query($query,array($cart_seq));
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		$query->free_result();
		return $returnArr;
	}

	/**
	@input
	 -$cart_seq : 장바구니 일련번호
	@return
	 -$returnArr : 장바구니정보, 상품 추가입력사항 정보
	*/
	public function delete($cart_seqs){
		$this->db->select('cart_seq');
		if($this->userInfo['member_seq']) $this->db->where('member_seq', $this->userInfo['member_seq']);
		else $this->db->where('session_id', $this->session->userdata('session_id'));
		$this->db->where_in('cart_seq',$cart_seqs);
		$query = $this->db->get($this->tb_cart);
		foreach ($query->result_array() as $row)
		{
			$tables = array($this->tb_cart_option, $this->tb_cart_input, $this->tb_cart_suboption, $this->tb_cart);
			$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
		}
	}

	/**
	 장바구니 옵션,추가 옵션 삭제
	 @input
	  -$cart_option_seq : 장바구니 옵션테이블 일련번호
	  -$cart_suboption_seq : 장바구니 추가옵션테이블 일련번호
	 */
	public function delete_option($cart_option_seq=null,$cart_suboption_seq=null){

		$cart_seq = null;

		if($cart_option_seq){
			$query = $this->db->query("select cart_seq from ".$this->tb_cart_option." where cart_option_seq=?",$cart_option_seq);
			$result = $query->row_array();
			$cart_seq = $result['cart_seq'];
		}
		if($cart_suboption_seq){
			$query = $this->db->query("select cart_seq from ".$this->tb_cart_suboption." where cart_suboption_seq=?",$cart_suboption_seq);
			$result = $query->row_array();
			$cart_seq = $result['cart_seq'];
		}

		if(!$cart_seq) return;

		$this->db->select('cart_seq');
		if($this->userInfo['member_seq']) $this->db->where('member_seq', $this->userInfo['member_seq']);
		else $this->db->where('session_id', $this->session->userdata('session_id'));

		if($cart_option_seq){
			$query = $this->db->query("delete from ".$this->tb_cart_option." where cart_option_seq=?",$cart_option_seq);
		}
		if($cart_suboption_seq){
			$query = $this->db->query("delete from ".$this->tb_cart_suboption." where cart_suboption_seq=?",$cart_suboption_seq);
		}

		$query = $this->db->query("
			select count(*) as cnt from ".$this->tb_cart_option." where cart_seq = '{$cart_seq}'
			union
			select count(*) as cnt from ".$this->tb_cart_suboption." where cart_suboption_seq = '{$cart_seq}'
		");
		$result = $query->result_array();
		if(!$result){
			$this->db->where('cart_seq',$cart_seq);
			$query = $this->db->get($this->tb_cart);
			foreach ($query->result_array() as $row)
			{
				$tables = array($this->tb_cart_option, $this->tb_cart_input, $this->tb_cart_suboption, $this->tb_cart);
				$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
			}
		}
	}

	/**
	 장바구니 삭제
	 @input
	  -$mode : 장바구니 구분(cart,direct,choice)
	 */
	public function delete_mode($mode){

		$where_option_str = '';

		if($mode == 'choice'){
			$where_option_str = " and choice='y'";
		}

		if($mode == 'direct'){
			$where[] = "distribution = ?";
			$where_val[] = 'direct';
		}

		if($this->userInfo['member_seq']){
			$where[] = "member_seq=?";
			$where_val[] = $this->userInfo['member_seq'];
		}else{
			$where[] = "session_id=?";
			$where_val[] = $this->session->userdata('session_id');
		}

		$query = "select cart_option_seq from fm_cart_option where cart_seq in (select cart_seq from fm_cart where ".implode(' and ',$where).")".$where_option_str;
		$query = $this->db->query($query,$where_val);
		foreach ($query->result_array() as $row){
			$tables = array($this->tb_cart_option, $this->tb_cart_input, $this->tb_cart_suboption);
			$this->db->delete($tables,array('cart_option_seq' => $row['cart_option_seq']));
		}

		$query = "select count(*) cnt,cart_seq from fm_cart_option where cart_seq in (select cart_seq from fm_cart where ".implode(' and ',$where).") group by cart_seq";
		$query = $this->db->query($query,$where_val);
		foreach ($query->result_array() as $row){
			if($row['cnt'] == 0){
				$tables = array($this->tb_cart);
				$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
			}
		}
	}

	/**
	 이전에 담긴 바로 구매
	 */
	public function delete_for_settle(){

		$where_option_str = '';

		$where[] = "distribution = ?";
		$where_val[] = 'direct';

		if($this->userInfo['member_seq']){
			$where[] = "member_seq=?";
			$where_val[] = $this->userInfo['member_seq'];
		}else{
			$where[] = "session_id=?";
			$where_val[] = $this->session->userdata('session_id');
		}

		$query = "select max(cart_seq) cart_seq from fm_cart where ".implode(' and ',$where);
		$query = $this->db->query($query,$where_val);
		$max_row = $query->row_array();

		$query = "select cart_seq from fm_cart where ".implode(' and ',$where)." and cart_seq!='".$max_row['cart_seq']."'";
		$query = $this->db->query($query,$where_val);
		foreach ($query->result_array() as $row){
				$tables = array($this->tb_cart_option, $this->tb_cart_input, $this->tb_cart_suboption, $this->tb_cart);
				$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
		}
	}

	public function merge_for_member($member_seq){

		$session_id = $this->session->userdata('session_id');

		$this->db->where('session_id',$session_id);
		$this->db->update($this->tb_cart, array('member_seq' => $member_seq, 'session_id'=>''));

		$carts = $this->get_cart_list('cart');
		$arr_done = array();
		foreach($carts as $cart){
			if( !in_array($cart['goods_seq'],$arr_done) ){
				$this->merge_for_goods($cart['goods_seq'],$cart['cart_seq'],$member_seq);
			}
			$arr_done[] = $cart['goods_seq'];
		}
	}

	public function merge_for_choice(){
		if($this->userInfo['member_seq']){
			$this->db->where('member_seq',$this->userInfo['member_seq']);
			$this->db->where('distribution','choice');
		}else{
			$session_id = $this->session->userdata('session_id');
			$this->db->where('session_id',$session_id);
			$this->db->where('distribution','choice');
		}
		$this->db->update($this->tb_cart, array('distribution' => 'cart'));

		$carts = $this->get_cart_list();
		$arr_done = array();
		foreach($carts as $cart){
			if(!in_array($cart['goods_seq'],$arr_done)){
				$this->merge_for_goods($cart['goods_seq'],$cart['cart_seq'],$member_seq);
			}
			$arr_done[] = $cart['goods_seq'];
		}
	}

	public function cart_list($admin=""){
		$total = 0;
		$total_point =0;
		$result = "";
		$member_seq = "";
		$where_query = "";
		$shop_total_price = 0;
		$shop_total_price_exempt = 0;
		$exempt_chk	= 0;
		$shop_shipping_policy = "";
		$default_box_ea = false;
		$session_id = $this->session->userdata('session_id');

		$cfg_reserve = config_load('reserve');


		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('promotionmodel');

		if($admin != ""){
			$where_arr[] = "admin";
			$where_query[] = "cart.session_id = ?";
			$where_arr[] = $session_id;
		}else{

			if(!isset($_GET['mode'])) $mode = 'cart';
			else $mode = $_GET['mode'];
			$where_arr[] = $mode;

			if($this->userInfo['member_seq']){

				$this->load->model('membermodel');

				$member_seq = $this->userInfo['member_seq'];
				$where_query[] = "cart.member_seq = ?";
				$where_arr[] = $member_seq;
			}else{
				$where_query[] = "cart.session_id = ?";
				$where_arr[] = $session_id;
			}
		}

		$query = "
		SELECT cart.cart_seq,cart.fblike,
		goods.goods_seq,goods.goods_name,goods.goods_code,goods.cancel_type,goods.goods_kind,goods.sale_seq,
		goods.shipping_weight_policy,goods.goods_weight,
		goods.shipping_policy,goods.goods_shipping_policy,
		goods.unlimit_shipping_price,goods.limit_shipping_price,
		goods.limit_shipping_ea,goods.limit_shipping_subprice,
		(select image from fm_goods_image where cut_number = 1 AND image_type = 'thumbCart' AND cart.goods_seq = goods_seq limit 1) as image,
		(
			SELECT sum(ea)
			FROM ".$this->tb_cart_suboption."
			WHERE cart_seq=cart.cart_seq
		) sub_ea,
		sum(cart_opt.ea) ea,
		(
			SELECT COUNT(cart_suboption_seq)
			FROM ".$this->tb_cart_suboption."
			WHERE cart_seq=cart.cart_seq
		) sub_cnt,
		(
			SELECT SUM(g.price*s.ea)
			FROM fm_goods_suboption g,".$this->tb_cart_suboption." s
			WHERE g.goods_seq=cart.goods_seq
			AND g.suboption=s.suboption
			AND g.suboption_title=s.suboption_title
			AND s.cart_seq=cart.cart_seq
		) sub_price,
		(
			SELECT SUM(g.reserve*s.ea)
			FROM fm_goods_suboption g,".$this->tb_cart_suboption." s
			WHERE g.goods_seq=cart.goods_seq
			AND g.suboption=s.suboption
			AND g.suboption_title=s.suboption_title
			AND s.cart_seq=cart.cart_seq
		) sub_reserve,
		goods_opt.price,
		goods_opt.consumer_price,
		goods_opt.reserve_unit as reserve_unit,
		SUM(IF(cart_opt.option1!='',1,0)) opt_cnt,
		SUM(goods_opt.reserve*cart_opt.ea) reserve,
		goods.reserve_policy,
		goods.multi_discount_use,
		goods.multi_discount_ea,
		goods.multi_discount,
		goods.multi_discount_unit,
		goods.tax,
		goods.social_goods_group,
		goods.socialcp_input_type,goods.socialcp_cancel_type,
		goods.socialcp_cancel_use_refund,goods.socialcp_cancel_payoption,goods.socialcp_cancel_payoption_percent,
		goods.socialcp_use_return,goods.socialcp_use_emoney_day,goods.socialcp_use_emoney_percent,
		goods.individual_refund,
		goods.individual_refund_inherit,
		goods.individual_export,
		goods.individual_return
		FROM ".$this->tb_cart." cart
		,fm_goods goods
		,".$this->tb_cart_option." cart_opt
		,fm_goods_option goods_opt
		WHERE cart.distribution=?
		AND cart.goods_seq = goods.goods_seq
		AND goods.goods_status = 'normal'
		AND cart.cart_seq = cart_opt.cart_seq
		AND cart.goods_seq = goods_opt.goods_seq
		AND ifnull(cart_opt.option1,'') = ifnull(goods_opt.option1,'')
		AND ifnull(cart_opt.option2,'') = ifnull(goods_opt.option2,'')
		AND ifnull(cart_opt.option3,'') = ifnull(goods_opt.option3,'')
		AND ifnull(cart_opt.option4,'') = ifnull(goods_opt.option4,'')
		AND ifnull(cart_opt.option5,'') = ifnull(goods_opt.option5,'')";
		if($where_query){
			$query .= ' AND '.implode(' AND ', $where_query);
		}
		$query .= " GROUP BY cart.cart_seq ORDER BY cart.cart_seq DESC";
		$query = $this->db->query($query,$where_arr);
		$shipping_price['goods'] = 0;
		$shipping_exempt = 0;
		$promocodeSale = 0;
		$cart_items = $query->result_array();

		foreach ($cart_items as $row){
			$categorys = $this->goodsmodel->get_goods_category($row['goods_seq']);
			foreach($categorys as $key => $data) $row['r_category'] = $this->categorymodel->split_category($data['category_code']);

			$cart_options = $this->cartmodel->get_cart_option($row['cart_seq']);
			$cart_suboptions = $this->cartmodel->get_cart_suboption($row['cart_seq']);
			$cart_inputs = $this->cartmodel->get_cart_input($row['cart_seq']);
			$shipping = $this->goodsmodel->get_goods_delivery($row,$row['ea']);

			$row['goods_name'] = strip_tags(str_replace(array("\"","'"),'',$row['goods_name']));

			$arr_multi = array(
				'multi_discount_use' => $row['multi_discount_use'],
				'multi_discount_ea' => $row['multi_discount_ea'],
				'multi_discount' => $row['multi_discount'],
				'multi_discount_unit' => $row['multi_discount_unit']
			);

			if($row['reserve_policy'] == 'shop') $row['reserve'] = 0;

			$row['point'] = 0;
			foreach($cart_options as $key_option => $data_option){

				$data_option['ori_price'] = $data_option['price'];
				$categorys = $this->goodsmodel->get_goods_category($data_option['goods_seq']);
				foreach($categorys as $key => $data) $arr_category = $this->categorymodel->split_category($data['category_code']);

				// event sale
				$data_option['event'] = $this->goodsmodel->get_event_price($data_option['ori_price'], $row['goods_seq'], $arr_category, $data_option['consumer_price'],$data_option);
				if($data_option['event']['event_seq']) {
					if($data_option['event']['target_sale'] == 1 && $data_option['consumer_price'] > 0 ){//정가기준 할인시
						$data_option['price'] = ($data_option['consumer_price'] > $data_option['event']['event_sale_unit'])?$data_option['consumer_price'] - (int) $data_option['event']['event_sale_unit']:0;
					}else{
						$data_option['price'] = ($data_option['price'] > $data_option['event']['event_sale_unit'])?$data_option['price'] - (int) $data_option['event']['event_sale_unit']:0;
					}
				}
				/**$r_event = $this->goodsmodel->get_event_price($row['ori_price'], $row['goods_seq'], $arr_category);
				$data_option['price'] -= (int) $r_event['event_sale_unit'];**/

				// multi sale
				$data_option['price'] = (int) $this->goodsmodel->get_multi_sale_price($row['ea'],$data_option['price'],$arr_multi);
				$row['tot_price'] += $data_option['price'] * $data_option['ea'];

				// reserve
				if($row['reserve_policy'] == 'shop') {
					$data_option['reserve'] = $this->goodsmodel->get_reserve_with_policy($row['reserve_policy'],$data_option['price'],$cfg_reserve['default_reserve_percent'],$data_option['reserve_rate'],$data_option['reserve_unit'],$data_option['reserve']);
					$data_option['reserve'] += (int) $data_option['event']['event_reserve_unit'];
					$row['reserve'] += $data_option['reserve'] * $data_option['ea'];
				}

				###optoin point
				$data_option['point'] = (int) $this->goodsmodel->get_point_with_policy($data_option['price']);
				$row['point'] += ($data_option['point'] * $data_option['ea']);
				$row['point'] += (int) $data_option['event']['event_point_unit'];

				$cart_options[$key_option] = $data_option;
			}

			//suboption point
			foreach($cart_suboptions as $key_suboption => $data_suboption){
				###
				$data_suboption['point'] = (int) $this->goodsmodel->get_point_with_policy($data_suboption['price']);
				$row['point'] += ($data_suboption['point'] * $data_suboption['ea']);
			}

			$row['reserve']+=$row['sub_reserve'];
			$row['cart_options'] = $cart_options ? $cart_options : array();
			$row['cart_suboptions'] = $cart_suboptions ? $cart_suboptions : array();
			$row['cart_inputs'] = $cart_inputs;

			$row['tot_price'] += $row['sub_price'];
			$row['ea'] += $row['sub_ea'];

			$row['goods_shipping'] = 0;
			if($row['shipping_policy'] == 'shop'){
				$shop_total_price += $row['tot_price'];
				if($row['tax']!="tax"){
					$shop_total_price_exempt += $row['tot_price'];
				}
				$shop_shipping_policy = $shipping;
				$default_box_ea = true;
			}else{
				$shop_total_price += $row['tot_price'];
				$row['goods_shipping'] = $shipping['price'];
				$shipping_price['goods'] += $row['goods_shipping'];
				$box_ea += $shipping['box_ea'];
				if($row['tax']!="tax"){
					$shop_total_price_exempt += $row['tot_price'];
					$shipping_exempt += $shipping['price'];
				}
			}



			// 회원추가적립
			$member_point = 0;
			$member_reserve = 0;
			$row['opt_ea'] = $row['ea'] - $row['sub_ea'];
			if($adminOrder == "admin"){
				if($_POST["sale_seq"][$data['goods_seq']] && $_POST["group_seq"][$data['goods_seq']]){
					$member_reserve_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['price'],$cart['total'],$row['goods_seq'],$category,$_POST["sale_seq"][$row['goods_seq']],	$_POST["group_seq"][$row['goods_seq']],'reserve');
					$member_reserve += $member_reserve_unit * $row['opt_ea'];
					$member_reserve_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['sub_price'],$cart['total'],$row['goods_seq'],$category,$_POST["sale_seq"][$row['goods_seq']],$_POST["group_seq"][$row['goods_seq']],'reserve');
					$member_reserve += $member_reserve_unit * $row['sub_ea'];

					$member_point_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['price'],$cart['total'],$row['goods_seq'],$category,$_POST["sale_seq"][$row['goods_seq']],$_POST["group_seq"][$row['goods_seq']],'point');
					$member_point += $member_point_unit * $row['opt_ea'];
					$member_point_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['sub_price'],$cart['total'],$row['goods_seq'],$category,$_POST["sale_seq"][$row['goods_seq']],$_POST["group_seq"][$row['goods_seq']],'point');
					$member_point += $member_point_unit * $row['sub_ea'];
				}
			}else if($this->userInfo['member_seq']){
				$member_reserve_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['price'],$cart['total'],$row['goods_seq'],$category,$row["sale_seq"],'','reserve');
				$member_reserve += $member_reserve_unit * $row['opt_ea'];
				$member_reserve_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['sub_price'],$cart['total'],$row['goods_seq'],$category,$row["sale_seq"],'','reserve');
				$member_reserve += $member_reserve_unit * $row['sub_ea'];

				$member_point_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['price'],$cart['total'],$row['goods_seq'],$category,$row["sale_seq"],'','point');
				$member_point += $member_point_unit * $row['opt_ea'];
				$member_point_unit = (int) $this->membermodel->get_group_addreseve($this->userInfo['member_seq'],$row['sub_price'],$cart['total'],$row['goods_seq'],$category,$row["sale_seq"],'','point');
				$member_point += $member_point_unit * $row['sub_ea'];
			}
			$row['reserve'] += $member_reserve;
			$row['point'] += $member_point;


			###
			if($row['tax']!="tax"){
				$exempt_chk++;
			}

			$total_point += $row['point'];
			$total_reserve += $row['reserve'];
			$total += $row['tot_price'];
			$result[] = $row;
		}
		//print_r($result);
		###
		if($query->num_rows()==$exempt_chk){
			$tax_type = "exempt";
		}else if($exempt_chk == 0){
			$tax_type = "tax";
		}else{
			$tax_type = "mix";
		}

		if($member_seq && $result) foreach($result as $k => $row){
			$categorys = $this->goodsmodel->get_goods_category($row['goods_seq']);
			foreach($categorys as $key => $data) $arr_category = $this->categorymodel->split_category($data['category_code']);
			foreach($row['cart_options'] as $key_option => $data_option){
				$add_reserve = (int) $this->membermodel->get_group_addreseve($member_seq,$data_option['price'],$total,$row['goods_seq'],$arr_category);
				$data_option['reserve'] += $add_reserve;
				$row['reserve'] += $add_reserve*$data_option['ea'];
				$total_reserve+= $add_reserve*$data_option['ea'];

				$add_point = (int) $this->membermodel->get_group_addreseve($member_seq,$data_option['price'],$total,$row['goods_seq'],$arr_category,'point');
				$data_option['point'] += $add_point;
				$row['point'] += $add_point*$data_option['ea'];
				$total_point+= $add_point*$data_option['ea'];

				$row['cart_options'][$key_option] = $data_option;
			}
			$result[$k] = $row;
		}

		foreach($result as $k => $row){$result[$k]['promocodeSale']=0;
			foreach($row['cart_options'] as $key_option => $data_option){
				if($data_option['promotion_code_seq'] && $data_option['promotion_code_serialnumber']) {
					//promotion code sale
					$promotions = $this->promotionmodel->get_able_download_saleprice($data_option['promotion_code_seq'],$data_option['promotion_code_serialnumber'], $total, $data_option['price'],$data_option['ea']);
					$result[$k]['cart_options'][$key_option]['promotioncode_sale'] = (int) $promotions['promotioncode_sale'];
					$result[$k]['promocodeSale'] += (int) $promotions['promotioncode_sale'];
					$promocodeSale += (int) $promotions['promotioncode_sale'];
				}
			}
		}

		$shipping_price['shop'] = $shop_shipping_policy['price'];
		if($shop_total_price && $shop_shipping_policy['free']){
			if($shop_shipping_policy['free'] <= $shop_total_price){
				$shipping_price['shop'] = 0;
			}
		}

		if( $shop_total_price && $default_box_ea)$box_ea += 1;
		$total_price = $total + array_sum($shipping_price);
		$exempt_price = $shop_total_price_exempt;
		$arr = array(
			'total_reserve'=>$total_reserve,
			'total_point'=>$total_point,
			'taxtype'=>$tax_type,
			'exempt_shipping'=>$shipping_exempt,
			'exempt_price'=>$exempt_price,
			'list'=>$result,
			'total'=>$total,
			'shipping_price'=>$shipping_price,
			'shipping_company_cnt'=>$shipping_company_cnt,
			'shop_shipping_policy'=>$shop_shipping_policy,
			'total_price'=>$total_price,
			'promocodeSale'=>$promocodeSale,
			'box_ea'=>$box_ea
		);

		return $arr;
	}

	function merge_for_goods($goods_seq,$cart_seq,$member_seq=''){

		$session_id = $this->session->userdata('session_id');
		if(!$member_seq ) $member_seq = $this->userInfo['member_seq'];

		$options = $this->get_cart_option($cart_seq);
		$suboptions = $this->get_cart_suboption($cart_seq);

		if($member_seq) $this->db->where('member_seq',$member_seq);
		else $this->db->where('session_id',$session_id);

		$this->db->where('goods_seq',$goods_seq);
		$this->db->where('cart_seq !=',$cart_seq);
		$this->db->where('distribution','cart');
		$this->db->select('cart_seq');
		$query = $this->db->get($this->tb_cart);

		foreach($query->result_array() as $row){
			$pre_cart_seq = $row['cart_seq'];

			$this->db->where('cart_seq', $pre_cart_seq);
			$this->db->update($this->tb_cart_option, array('cart_seq'=>$cart_seq));

			$this->db->where('cart_seq', $pre_cart_seq);
			$this->db->update($this->tb_cart_suboption, array('cart_seq'=>$cart_seq));

			$this->db->where('cart_seq', $pre_cart_seq);
			$this->db->update($this->tb_cart_input, array('cart_seq'=>$cart_seq));

			$this->db->delete($this->tb_cart,array('cart_seq' => $pre_cart_seq));
		}


	}

	public function get_cart_option_by_cart_option($cart_option_seq)
	{
		$bind[0] = $cart_option_seq;
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where option_seq=goods.option_seq and goods_seq=goods.goods_seq) supply_price
			FROM ".$this->tb_cart_option." cart,fm_goods_option goods
			WHERE cart.cart_option_seq=?
			AND goods.goods_seq =
			(
				select goods_seq from ".$this->tb_cart." where cart_seq=cart.cart_seq
			)
			AND cart.option1=goods.option1
			AND cart.option2=goods.option2
			AND cart.option3=goods.option3
			AND cart.option4=goods.option4
			AND cart.option5=goods.option5";
		$query = $this->db->query($query,$bind);
		$data = $query->row_array();
		return $data;
	}

	public function get_cart_by_cart_option($cart_option_seq)
	{
		$bind[0] = $cart_option_seq;
		$query = "select * from ".$this->tb_cart." where cart_seq = (select cart_seq from ".$this->tb_cart_option." where cart_option_seq=?)";
		$query = $this->db->query($query,$bind);
		$data = $query->row_array();
		return $data;
	}

	public function get_cart_suboption_by_cart_option($cart_option_seq)
	{
		$returnArr = "";
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,goods.sub_sale,
			(select supply_price from fm_goods_supply where suboption_seq=goods.suboption_seq and goods_seq=goods.goods_seq) supply_price
			FROM ".$this->tb_cart_suboption." cart,fm_goods_suboption goods
			WHERE cart.suboption=goods.suboption AND cart.suboption_title=goods.suboption_title
				AND goods.goods_seq =
				(
					select goods_seq from ".$this->tb_cart." where cart_seq=cart.cart_seq
				)
				AND cart.cart_option_seq=?
			ORDER BY cart.cart_suboption_seq DESC";
		$query = $this->db->query($query,array($cart_option_seq,$cart_option_seq));
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		$query->free_result();
		return $returnArr;
	}

	public function get_cart_input_by_cart_option($cart_option_seq)
	{
		$returnArr = "";
		$query = "
			SELECT *
			FROM ".$this->tb_cart_input."
			WHERE cart_option_seq=?
			ORDER BY cart_input_seq ASC";
		$query = $this->db->query($query,array($cart_option_seq));
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		$query->free_result();
		return $returnArr;
	}

	public function delete_cart_option($cart_option_seq,$mode='del')
	{
		$data = $this->get_cart_option_by_cart_option($cart_option_seq);
		$cart_seq = $data['cart_seq'];

		$bind[0]=$cart_option_seq;
		$query="delete from ".$this->tb_cart_option." where cart_option_seq=?";
		$this->db->query($query,$bind);
		$query="delete from ".$this->tb_cart_suboption." where cart_option_seq=?";
		$this->db->query($query,$bind);
		$query="delete from ".$this->tb_cart_input." where cart_option_seq=?";
		$this->db->query($query,$bind);

		if($mode == 'del'){
			$query = "select count(*) cnt from ".$this->tb_cart_option." where cart_seq=?";
			$query = $this->db->query($query,array($cart_seq));
			$data = $query->row_array();
			$cnt = $data['cnt'];
			if($cnt==0){
				$query="delete from ".$this->tb_cart." where cart_seq=?";
				$this->db->query($query,array($cart_seq));
			}
		}
	}

	public function catalog($admin=""){

		// 참조 load
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('promotionmodel');
		$this->load->model('membermodel');
		$this->load->library('sale');

		// 기본값 세팅
		$total						= 0;
		$total_point				= 0;
		$result						= '';
		$member_seq					= '';
		$where_query				= '';
		$shop_total_price			= 0;
		$shop_total_price_exempt	= 0;
		$exempt_chk					= 0;
		$shop_shipping_policy		= '';
		$default_box_ea				= false;
		$applypage					= 'saleprice';
		$session_id					= $this->session->userdata('session_id');
		$cfg_reserve				= config_load('reserve');
		$shipping_price['goods']	= 0;
		$shipping_exempt			= 0;
		$promocodeSale				= 0;
		$provider_shipping_policy	= array();
		$provider_sum_goods_price	= array();
		$provider_shipping_price	= array();
		$provider_box_ea			= array();

		//--> sale library 적용 
		$param['cal_type']				= 'list';
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<-- sale library 적용 

		if	(is_array($admin)){
			$param = $admin;
			unset($admin);
			$admin		= '';
			$admin		= $param['member_seq'];
			$cart_sdate = $param['cart_sdate'];
			$cart_edate = $param['cart_edate'];
			$cart_today	= array("today" => $param['today'], "todayw" => $param['todayw']);
		}

		if	($admin == "admin"){
			$where_arr[]		= "admin";
			$where_query[]		= "cart.session_id = ?";
			$where_arr[]		= $session_id;
		}elseif	($admin != ""){
			$where_query[]		= "cart.member_seq = ?";
			$where_arr[]		= "cart";
			$where_arr[]		= $admin;
			# 개인맞춤형알림에서 사용 2014-07-25
			if	($cart_sdate && $cart_edate){
				$where_query[]	= "cart.regist_date between ? and ? ";
				$where_arr[]	= $cart_sdate;
				$where_arr[]	= $cart_edate;
			}
		}else{
			$mode			= (isset($_GET['mode'])) ? $_GET['mode'] : 'cart';
			$where_arr[]	= $mode;

			if	($mode == 'choice'){
				$where_query[]	= "cart_opt.choice = ?";
				$where_arr[]	= 'y';
			}

			if	($this->userInfo['member_seq']){
				$member_seq		= $this->userInfo['member_seq'];
				$where_query[]	= "cart.member_seq = ?";
				$where_arr[]	= $member_seq;
			}else{
				$where_query[] = "member_seq=0 and cart.session_id = ?";
				$where_arr[] = $session_id;
			}
		}

		$sql	= "SELECT
					cart.fblike,cart.cart_seq,
					goods.goods_seq,goods.goods_name,goods.goods_code,goods.goods_type,
					goods.cancel_type,goods.sale_seq,goods.goods_kind,goods.socialcp_event,
					goods.shipping_weight_policy,goods.goods_weight,
					goods.shipping_policy,goods.goods_shipping_policy,
					goods.unlimit_shipping_price,goods.limit_shipping_price,
					goods.limit_shipping_ea,goods.limit_shipping_subprice,
					goods_img.image,
					goods_opt.price,
					goods_opt.reserve_rate,
					goods_opt.reserve_unit as reserve_unit,
					goods_opt.reserve reserve,
					goods_opt.consumer_price,
					goods.min_purchase_ea,
					goods.max_purchase_ea,
					(select supply_price from fm_goods_supply 
						where option_seq=goods_opt.option_seq) as supply_price,
					goods.reserve_policy,
					goods.multi_discount_use,
					goods.multi_discount_ea,
					goods.multi_discount,
					goods.multi_discount_unit,
					goods.tax,
					goods.social_goods_group,
					goods.socialcp_input_type,goods.socialcp_cancel_type,
					goods.socialcp_cancel_use_refund,goods.socialcp_cancel_payoption,
					goods.socialcp_cancel_payoption_percent,goods.socialcp_use_return,
					goods.socialcp_use_emoney_day,goods.socialcp_use_emoney_percent,
					goods.individual_refund,
					goods.individual_refund_inherit,
					goods.individual_export,
					goods.individual_return,
					goods.possible_pay,
					goods.possible_mobile_pay,
					cart_opt.*
				FROM
					".$this->tb_cart_option." cart_opt
					left join ".$this->tb_cart." cart on cart.cart_seq = cart_opt.cart_seq
					left join fm_goods_image goods_img on goods_img.cut_number = 1 AND goods_img.image_type = 'thumbCart' AND cart.goods_seq = goods_img.goods_seq, 
					fm_goods goods, 
					fm_goods_option goods_opt
				WHERE cart.distribution=?
				AND cart.goods_seq = goods.goods_seq
				AND goods.goods_status = 'normal'
				AND goods.goods_view = 'look'
				AND cart.goods_seq = goods_opt.goods_seq
				AND ifnull(cart_opt.option1,'') = ifnull(goods_opt.option1,'')
				AND ifnull(cart_opt.option2,'') = ifnull(goods_opt.option2,'')
				AND ifnull(cart_opt.option3,'') = ifnull(goods_opt.option3,'')
				AND ifnull(cart_opt.option4,'') = ifnull(goods_opt.option4,'')
				AND ifnull(cart_opt.option5,'') = ifnull(goods_opt.option5,'')";
		if	($where_query){
			$sql	.= ' AND '.implode(' AND ', $where_query);
		}
		$sql		.= " ORDER BY cart.goods_seq,cart_opt.cart_option_seq ASC";
		$query		= $this->db->query($sql, $where_arr);
		$cart_list	= $query->result_array();
		foreach ($cart_list as $row){
			$goods_ea[$row['goods_seq']]	+= $row['ea'];			
			$r_cart_option[]				= $row;
		}
		foreach ($cart_list as $row){

			// 특수문자 처리
			$row['goods_name']	= strip_tags(str_replace(array("\"","'"),'',$row['goods_name']));

			// 카테고리정보
			$tmparr2	= array();
			if	($r_goods[$row['goods_seq']]['r_category']){
				$row['r_category']		= $r_goods[$row['goods_seq']]['r_category'];
			}else{
				$categorys				= $this->goodsmodel->get_goods_category($row['goods_seq']);
				foreach($categorys as $key => $data){
					$tmparr				= $this->categorymodel->split_category($data['category_code']);
					foreach($tmparr as $cate)	$tmparr2[]	= $cate;
				}
				if	($tmparr2){
					$tmparr2									= array_values(array_unique($tmparr2));
					$row['r_category']							= $tmparr2;
					$r_goods[$row['goods_seq']]['r_category']	= $tmparr2;
				}
			}

			// 브랜드정보
			$tmparr2	= array();
			if	($r_goods[$row['goods_seq']]['r_brand']){
				$row['r_brand']			= $r_goods[$row['goods_seq']]['r_brand'];
			}else{
				$brands					= $this->goodsmodel->get_goods_brand($row['goods_seq']);
				foreach($brands as $key => $data){
					$tmparr				= $this->brandmodel->split_brand($data['category_code']);
					foreach($tmparr as $cate)	$tmparr2[]	= $cate;
				}
				if	($tmparr2){
					$tmparr2									= array_values(array_unique($tmparr2));
					$row['r_brand']								= $tmparr2;
					$r_goods[$row['goods_seq']]['r_brand']		= $tmparr2;
				}
			}

			// 옵션 별 최대 구매,최소 구매수량
			$option_name												= $row['option1'] . ';' 
																		. $row['option2'] . ';' 
																		. $row['option3'] . ';' 
																		. $row['option4'] . ';' 
																		. $row['option5'];
			$r_goods[$row['goods_seq']]['ea_for_option'][$option_name]	+= $row['ea'];
			$r_goods[$row['goods_seq']]['min_purchase_ea']				= $row['min_purchase_ea'];
			$r_goods[$row['goods_seq']]['max_purchase_ea']				= $row['max_purchase_ea'];


			// 추가옵션
			$cart_suboptions		= $this->cartmodel->get_cart_suboption_by_cart_option($row['cart_option_seq']);

			// 추가입력사항
			$cart_inputs			= $this->cartmodel->get_cart_input_by_cart_option($row['cart_option_seq']);

			$row['org_price']		= $row['price'];
			$row['tot_ori_price']	+= $row['price'] * $row['ea'];

			//----> sale library 적용
			unset($param,$row['reserve'],$row['point']);
			$param['consumer_price']		= $row['consumer_price'];
			$param['price']					= $row['price'];
			$param['ea']					= $row['ea'];
			$param['goods_ea']				= $goods_ea[$row['goods_seq']];
			$param['category_code']			= $row['r_category'];
			$param['goods_seq']				= $row['goods_seq'];
			$param['goods']					= $row;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);

			$row['basic_sale']				= $sales['one_sale_list']['basic'];
			$row['event_sale_target']		= ($sales['target_list']['event'] == 1) ? 'consumer_price' : 'price';
			$row['event_sale']				= $sales['one_sale_list']['event'];
			$row['multi_sale']				= $sales['one_sale_list']['multi'];
			$row['price']					= $sales['one_result_price'];
			$row['event']					= $this->sale->cfgs['event'];
			$row['eventEnd']				= $sales['eventEnd'];
			$this->sale->reset_init();
			unset($sales);
			//<---- sale library 적용

			$row['ori_price']	= $row['price'];
			$row['tot_price']	+= $row['price'] * $row['ea'];

			// 로우 길이
			$r_goods[$row['goods_seq']]['cnt']++;
			if($r_goods[$row['goods_seq']]['cnt'] == 1){
				$row['first']		= 1;
			}else{
				$row['first']		= 0;
			}

			//suboption point
			foreach($cart_suboptions as $key_suboption => $data_suboption)
			{
				if($this->userInfo['member_seq']){
					// 포인트
					$data_suboption['point'] = (int) $this->goodsmodel->get_point_with_policy($data_suboption['price']);
					$data_suboption['point'] = $data_suboption['point'] * $data_suboption['ea'];
					// 적립금
					$data_suboption['reserve'] = $this->goodsmodel->get_reserve_with_policy($row['reserve_policy'],$data_suboption['price'],$cfg_reserve['default_reserve_percent'],$data_suboption['reserve_rate'],$data_suboption['reserve_unit'],$data_suboption['reserve']);
					$data_suboption['reserve'] = $data_suboption['reserve'] * $data_suboption['ea'];
				}else{
					$data_suboption['reserve'] = 0;
					$data_suboption['point'] = 0;
				}
				$cart_suboptions[$key_suboption] = $data_suboption;


				// 상품별
				$r_goods[$row['goods_seq']]['reserve'] += (int) $data_suboption['reserve'];
				$r_goods[$row['goods_seq']]['point'] += (int) $data_suboption['point'];
				$r_goods[$row['goods_seq']]['ea'] += (int) $data_suboption['ea'];
				$r_goods[$row['goods_seq']]['price'] += $data_suboption['price'] * $data_suboption['ea'];

				// 추가 적립금
				$row['suboption_point'] += $data_suboption['point'];
				$row['suboption_reserve'] += $data_suboption['reserve'];

				$row['tot_ori_price'] += $data_suboption['price'] * $data_suboption['ea'];
				$row['tot_price'] += $data_suboption['price'] * $data_suboption['ea'];

				// 옵션별 구매수량
				$option_name = $data_suboption['suboption_title'].';'.$data_suboption['suboption'];
				$r_goods[$row['goods_seq']]['ea_for_suboption'][$option_name] += $data_suboption['ea'];

				// 장바구니 카운트
				$r_goods[$row['goods_seq']]['cnt']++;
			}

			$row['cart_suboptions'] = $cart_suboptions ? $cart_suboptions : array();
			$row['cart_inputs'] = $cart_inputs;

			if($row['goods_kind'] == 'coupon'){
				$row['shipping_policy'] = 'goods';
				$row['goods_shipping_policy'] = 'unlimit';
				$row['unlimit_shipping_price'] = 0;
			}

			// 상품별 저장
			$r_goods[$row['goods_seq']]['goods_kind'] = $row['goods_kind'];
			$r_goods[$row['goods_seq']]['goods_name'] = $row['goods_name'];
			$r_goods[$row['goods_seq']]['sale_seq']	= $row['sale_seq'];
			$r_goods[$row['goods_seq']]['price'] += $row['price'] * $row['ea'];
			$r_goods[$row['goods_seq']]['ea'] += $row['ea'];
			$r_goods[$row['goods_seq']]['option_ea'] += $row['ea'];
			$r_goods[$row['goods_seq']]['shipping_weight_policy'] = $row['shipping_weight_policy'];
			$r_goods[$row['goods_seq']]['goods_weight'] = $row['goods_weight'];
			$r_goods[$row['goods_seq']]['shipping_policy'] = $row['shipping_policy'];
			$r_goods[$row['goods_seq']]['goods_shipping_policy'] = $row['goods_shipping_policy'];
			$r_goods[$row['goods_seq']]['unlimit_shipping_price'] = $row['unlimit_shipping_price'];
			$r_goods[$row['goods_seq']]['limit_shipping_price'] = $row['limit_shipping_price'];
			$r_goods[$row['goods_seq']]['limit_shipping_ea'] = $row['limit_shipping_ea'];
			$r_goods[$row['goods_seq']]['limit_shipping_subprice'] = $row['limit_shipping_subprice'];
			$r_goods[$row['goods_seq']]['shipping_weight_policy'] = $row['shipping_weight_policy'];
			$r_goods[$row['goods_seq']]['reserve'] += (int) $row['reserve'];
			$r_goods[$row['goods_seq']]['point'] += (int) $row['point'];
			$r_goods[$row['goods_seq']]['tax'] = $row['tax'];
			$r_goods[$row['goods_seq']]['event'] = $row['event'];

			//쇼셜쿠폰상품저장@2013-10-22
			$r_goods[$row['goods_seq']]['socialcp_input_type'] = $row['socialcp_input_type'];
			$r_goods[$row['goods_seq']]['socialcp_cancel_type'] = $row['socialcp_cancel_type'];
			$r_goods[$row['goods_seq']]['socialcp_use_return'] = $row['socialcp_use_return'];
			$r_goods[$row['goods_seq']]['socialcp_use_emoney_day'] = $row['socialcp_use_emoney_day'];
			$r_goods[$row['goods_seq']]['socialcp_use_emoney_percent'] = $row['socialcp_use_emoney_percent'];
			$r_goods[$row['goods_seq']]['social_goods_group'] = $row['social_goods_group'];//쿠폰상품그룹

			$r_goods[$row['goods_seq']]['socialcp_cancel_use_refund'] = $row['socialcp_cancel_use_refund'];
			$r_goods[$row['goods_seq']]['socialcp_cancel_payoption'] = $row['socialcp_cancel_payoption'];
			$r_goods[$row['goods_seq']]['socialcp_cancel_payoption_percent'] = $row['socialcp_cancel_payoption_percent']; 

			// 과세 비과세
			if($row['tax']=="exempt"){
				$exempt_chk++;
			}

			$result[] = $row;
		}

		### 과세 비과세

		if($query->num_rows()==$exempt_chk){
			$tax_type = "exempt";
		}else if($exempt_chk == 0){
			$tax_type = "tax";
		}else{
			$tax_type = "mix";
		}

		// 배송비계산

		$row = array();
		foreach($r_goods as $goods_seq => $row){
			$shipping = $this->goodsmodel->get_goods_delivery($row,$row['ea']);
			$row['goods_shipping'] = 0;
			if($row['shipping_policy'] == 'shop'){
				$shop_total_price += $row['price'];
				if($row['tax']=="exempt"){
					$shop_total_price_exempt += $row['price'];
				}
				$shop_shipping_policy = $shipping;
				$default_box_ea = true;
			}else{
				//$shop_total_price += $row['tot_price'];
				$row['goods_shipping'] = $shipping['price'];
				$shipping_price['goods'] += $row['goods_shipping'];
				$box_ea += $shipping['box_ea'];
				if($row['tax']=="exempt"){
					//$shop_total_price_exempt += $row['price'];
					$shipping_exempt += $shipping['price'];
				}
			}
			$r_goods[$goods_seq] = $row;

			// 총포인트

			$total_point += $row['point'];


			// 총적립금

			$total_reserve += $row['reserve'];

			// 총상품금액

			$total += $row['price'];

			// 총상품수
			$total_ea += $row['ea'];
		}


		// 추가적립금,추가포인트

		if($member_seq && $result) foreach($result as $k => $row){

			$add_reserve = (int) $this->membermodel->get_group_addreseve($member_seq,$row['price'],$total,$row['goods_seq'],$row['r_category']);
			$row['reserve'] += $add_reserve * $row['ea'];
			$total_reserve += $add_reserve * $row['ea'];

			$add_point = (int) $this->membermodel->get_group_addreseve($member_seq,$row['price'],$total,$row['goods_seq'],$row['r_category'],'point');
			$row['point'] += $add_point*$row['ea'];
			$total_point+= $add_point*$row['ea'];

			$result[$k] = $row;
		}


		// 프로모션코드

		foreach($result as $k => $row){
			$result[$k]['promocodeSale']=0;
			if($row['promotion_code_seq'] && $row['promotion_code_serialnumber']) {
				$promotions = $this->promotionmodel->get_able_download_saleprice($row['promotion_code_seq'],$row['promotion_code_serialnumber'], $total, $row['price'],$row['ea']);
				$row['promotioncode_sale'] = (int) $promotions['promotioncode_sale'];
				$row['promocodeSale'] += (int) $promotions['promotioncode_sale'];
				$promocodeSale += (int) $promotions['promotioncode_sale'];

				$result[$k] = $row;
			}
		}

		$shipping_price['shop'] = $shop_shipping_policy['price'];
		if	($shop_shipping_policy['free'] > 0 && $shop_shipping_policy['free'] < $shop_total_price)
			$shipping_price['shop']	= 0;

		
		if( $shop_total_price && $default_box_ea)$box_ea += 1;
		$total_price = $total + array_sum($shipping_price);
		$exempt_price = $shop_total_price_exempt;
		$arr = array(
			'data_goods'=>$r_goods,
			'total_reserve'=>$total_reserve,
			'total_point'=>$total_point,
			'taxtype'=>$tax_type,
			'exempt_shipping'=>$shipping_exempt,
			'exempt_price'=>$exempt_price,
			'list'=>$result,
			'total'=>$total,
			'total_ea'=>$total_ea,
			'shipping_price'=>$shipping_price,
			'shipping_company_cnt'=>$shipping_company_cnt,
			'provider_shipping_price'=>$provider_shipping_price,
			'provider_shipping_policy'=>$provider_shipping_policy,
			'provider_box_ea'=>$provider_box_ea,
			'shop_shipping_policy'=>$shop_shipping_policy,
			'total_price'=>$total_price,
			'promocodeSale'=>$promocodeSale,
			'total_price_for_shop_delivery'=>$shop_total_price,
			'box_ea'=>$box_ea
		);

		return $arr;
	}

	function insert_cart_alloption($cart_seq,$inputs)
	{
		$catetmp = $this->goodsmodel->get_goods_category($_POST['goodsSeq']);
		unset($category);
		foreach($catetmp as $caterow) {
			if( strlen($caterow['category_code']) > 4) {
				if(strlen($caterow['category_code']) == 16) {
					$category[] = substr($caterow['category_code'], 0, 16);
					$category[] = substr($caterow['category_code'], 0, 12);
					$category[] = substr($caterow['category_code'], 0, 8);
					$category[] = substr($caterow['category_code'], 0, 4);
				}elseif(strlen($caterow['category_code']) == 12) {
					$category[] = substr($caterow['category_code'], 0, 12);
					$category[] = substr($caterow['category_code'], 0, 8);
					$category[] = substr($caterow['category_code'], 0, 4);
				}elseif(strlen($caterow['category_code']) == 8) {
					$category[] = substr($caterow['category_code'], 0, 8);
					$category[] = substr($caterow['category_code'], 0, 4);
				}else{
					$category[] = substr($caterow['category_code'], 0, 4);
				}
			}else{
				$category[] = $caterow['category_code'];
			}
		}

		$brands = $this->goodsmodel->get_goods_brand($_POST['goodsSeq']);
		unset($brand_code);
		if($brands) foreach($brands as $bkey => $branddata){
			if( $branddata['link'] == 1 ){
				$brand_codear= $this->brandmodel->split_brand($branddata['category_code']);
				$brand_code[] = $brand_codear[0];
			}
		}

		$cart = $this->cartmodel->catalog($adminOrder);
		unset($insert_data,$max);
		foreach($_POST['optionEa'] as $k1 => $ea){
			unset($insert_data,$max);
			for($i=0;$i<5;$i++){
				if( !isset($_POST['option'][$i][$k1]) || !$_POST['option'][$i][$k1] ) $_POST['option'][$i][$k1] = "";
				if( !isset($_POST['optionTitle'][$i][$k1]) || !$_POST['optionTitle'][$i][$k1] ) $_POST['optionTitle'][$i][$k1] = null;
			}
			$insert_data['option1']		= $_POST['option'][0][$k1];
			$insert_data['title1']		= $_POST['optionTitle'][0][$k1];
			$insert_data['option2']		= $_POST['option'][1][$k1];
			$insert_data['title2']		= $_POST['optionTitle'][1][$k1];
			$insert_data['option3']		= $_POST['option'][2][$k1];
			$insert_data['title3']		= $_POST['optionTitle'][2][$k1];
			$insert_data['option4']		= $_POST['option'][3][$k1];
			$insert_data['title4']		= $_POST['optionTitle'][3][$k1];
			$insert_data['option5']		= $_POST['option'][4][$k1];
			$insert_data['title5']		= $_POST['optionTitle'][4][$k1];
			$insert_data['ea'] 			= $ea;
			$insert_data['cart_seq']	= $cart_seq;

			/**
			**/
			if( $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')) ){

				$sc['whereis'] = " and promotion_input_serialnumber ='".$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))."'";
				$promotioncode = $this->promotionmodel->get_data($sc);
				$promotioncode = $promotioncode[0];

				list($price,$reserve) = $this->goodsmodel->get_goods_option_price(
					$_POST['goodsSeq'],
					$insert_data['option1'],
					$insert_data['option2'],
					$insert_data['option3'],
					$insert_data['option4'],
					$insert_data['option5']
				);

				if( strstr($promotioncode['type'],'promotion') ){//일반코드
					$promotions = $this->promotionmodel->get_able_promotion_list($_POST['goodsSeq'], $category, $brand_code, $cart['total'], $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')), $price, $ea );
				}else{//개별코드
					$promotions = $this->promotionmodel->get_able_download_list($_POST['goodsSeq'], $category, $brand_code, $cart['total'], $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')), $price, $ea );
				}

				if( $promotions) {
					if($promotions['duplication_use'] == 1) {//중복할인은 무조건추가
							$insert_data['promotion_code_seq']	= $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
							$insert_data['promotion_code_serialnumber']	= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
					}else{//중복할인이 아닌경우 상품 할인가(판매가)가 최대값인 상품으로 처리함
						foreach($cart['list'] as $cartkey => $cartdata){
							//debug_var("1 ".$_POST['goodsSeq']."==".$cartdata['goods_seq']."==>".$max[$_POST['goods_seq']] ." < ". $price."===>".$cartdata['cart_seq']);
							if($_POST['goodsSeq'] == $cartdata['goods_seq']) {
								//debug_var("2 ".$_POST['goodsSeq']."==".$cartdata['goods_seq']."==>".$max[$_POST['goods_seq']] ." < ". $price."===>".$cartdata['cart_seq']);
								if( ($max[$_POST['goods_seq']] && $max[$_POST['goods_seq']] < $price) || !$max[$_POST['goods_seq']]){
									$insert_data['promotion_code_seq']	= $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
									$insert_data['promotion_code_serialnumber']	= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));

									$max[$_POST['goods_seq']] = $price;
									$upsql = "update fm_cart_option set promotion_code_seq=null,promotion_code_serialnumber=null where cart_seq = {$cartdata['cart_seq']} ";
									$this->db->query($upsql);
								}
							}
						}

						if($cart['list'] && !$max[$_POST['goods_seq']]){//최초상품인경우
							//debug_var("3 ".$_POST['goodsSeq']."==".$cartdata['goods_seq']."==>".$max[$_POST['goods_seq']] ." < ". $price."===>".$cartdata['cart_seq']);
							$insert_data['promotion_code_seq']	= $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
							$insert_data['promotion_code_serialnumber']	= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
						}
					}
				}
			}

			$this->db->insert($this->tb_cart_option, $insert_data);

			// 첫번째상품 일련번호구하기
			$cart_option_seq = $this->db->insert_id();
			if($k1 == 0) $first_cart_option_seq = $this->db->insert_id();

			// 장바구니 추가입력항목
			if( isset($_POST['inputsValue']) && is_array($_POST['inputsValue'][0])){
				// 2014-12-18 옵션 개편 후 (ocw)
				unset($insert_data);
				$path = "./data/order/";
				if( isset($_POST['inputsValue']) ){
					$k = 0;
					foreach($inputs as $key_input => $data_input){
						if( $data_input ){
							if($data_input['input_form']=='file'){

								$file_path = $_POST['inputsValue'][$key_input][$k1];

								/* 이미지파일 확장자 */
								$this->arrImageExtensions = array('jpg','jpeg','png','gif','bmp','tif','pic');
								$this->arrImageExtensions = array_merge($this->arrImageExtensions,array_map('strtoupper',$this->arrImageExtensions));

								if(preg_match("/^\/data\/tmp\//i",str_replace(realpath(ROOTPATH),"",realpath($file_path))) && file_exists($file_path)){
									$file_ext = end(explode('.', $file_path));
									if( in_array($file_ext,$this->arrImageExtensions) ){
										$fname = $cart_seq.'_'.$cart_option_seq.'_'.$k.".".$file_ext;
										copy($file_path, $path.$fname);
										$insert_data['type'] = 'file';
										$insert_data['input_title'] = $data_input['input_name'];
										$insert_data['input_value'] = $fname;
										$insert_data['cart_seq'] = $cart_seq;
										$insert_data['cart_option_seq']	= $cart_option_seq;
										$this->db->insert($this->tb_cart_input, $insert_data);
										$k++;
									}
								}else/* if ($file_path)*/{
									$insert_data['type'] = 'file';
									$insert_data['input_title'] = $_POST['inputsTitle'][$key_input][$k1];
									$insert_data['input_value'] = $file_path ? $file_path : '';
									$insert_data['cart_seq'] = $cart_seq;
									$insert_data['cart_option_seq']	= $cart_option_seq;
									$this->db->insert($this->tb_cart_input, $insert_data);
								}
							}else{
								$insert_data['type'] = 'text';
								$insert_data['input_title'] = $_POST['inputsTitle'][$key_input][$k1];
								$insert_data['input_value'] = $_POST['inputsValue'][$key_input][$k1];
								$insert_data['cart_seq'] = $cart_seq;
								$insert_data['cart_option_seq']	= $cart_option_seq;
								$this->db->insert($this->tb_cart_input, $insert_data);
							}
						}
					}
				}
			}
		}

		// 장바구니 추가입력항목
		if( isset($_POST['inputsValue']) && !is_array($_POST['inputsValue'][0])){
			// 2014-12-18 옵션 개편 전 (ocw)
			unset($insert_data);
			$path = "./data/order/";
			if( isset($_POST['inputsValue']) ){
				$i = 0;
				$k =0;
				foreach($inputs as $key_input => $data_input){
					if( $data_input ){
						if($data_input['input_form']=='file'){

							if($_FILES['inputsValue']['tmp_name']){
								if($_FILES['inputsValue']['tmp_name'][$k]){
									$file_ext = end(explode('.', $_FILES['inputsValue']['name'][$k]));
									if( !in_array($file_ext,array('php','php3','php4','html','htm')) ){
										$fname = $cart_seq.'_'.$first_cart_option_seq.'_'.$k.".".$file_ext;
										move_uploaded_file($_FILES['inputsValue']['tmp_name'][$k], $path.$fname);
										$insert_data['type'] = 'file';
										$insert_data['input_title'] = $data_input['input_name'];
										$insert_data['input_value'] = $fname;
										$insert_data['cart_seq'] = $cart_seq;
										$insert_data['cart_option_seq']	= $first_cart_option_seq;
										$this->db->insert($this->tb_cart_input, $insert_data);
									}
								}
								$k++;
							}else if ($_POST['inputsValue'][$i]){
								$insert_data['type'] = 'file';
								$insert_data['input_title'] = $data_input['input_name'];
								$insert_data['input_value'] = $_POST['inputsValue'][$i];
								$insert_data['cart_seq'] = $cart_seq;
								$insert_data['cart_option_seq']	= $first_cart_option_seq;
								$this->db->insert($this->tb_cart_input, $insert_data);
								$i++;
							}
						}else{
							$insert_data['type'] = 'text';
							$insert_data['input_title'] = $data_input['input_name'];
							$insert_data['input_value'] = $_POST['inputsValue'][$i];
							$insert_data['cart_seq'] = $cart_seq;
							$insert_data['cart_option_seq']	= $first_cart_option_seq;
							$this->db->insert($this->tb_cart_input, $insert_data);
							$i++;
						}
					}
				}
			}
		}

		//장바구니 추가입력옵션
		unset($insert_data);
		if( isset($_POST['suboption']) ){
			foreach($_POST['suboption'] as $k1 => $suboption){
				$insert_data['ea']				= $_POST['suboptionEa'][$k1];
				$insert_data['suboption_title']	= $_POST['suboptionTitle'][$k1];
				$insert_data['suboption']		= $suboption;
				$insert_data['cart_seq'] 		= $cart_seq;
				$insert_data['cart_option_seq']	= $first_cart_option_seq;
				$this->db->insert($this->tb_cart_suboption, $insert_data);
			}
		}
	}

	/* 우측 퀵메뉴 장바구니 목록 반환 */
	function get_right_cart_list($page,$limit){
		$display_item = array();

		$this->load->library('sale');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');

		//----> sale library 적용
		$applypage						= 'lately_scroll';
		$param['cal_type']				= 'list';
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		if (!$page) $page = 1;
		$start = ($page-1)*$limit;
		$limit = "LIMIT {$start} , {$limit}";

		$add_and = "";
		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']){
			$add_and = "AND cart.member_seq = {$this->userInfo['member_seq']}";
		}else{
			$add_and = "AND cart.session_id = '{$session_id}'";
		}

		$query = $this->db->query("
		SELECT
		goods.goods_seq,goods.sale_seq,goods.goods_name,goods_img.image, goods_opt.price, goods_opt.consumer_price, cart_opt.cart_option_seq
		FROM
		fm_cart_option cart_opt
		left join fm_cart cart on cart.cart_seq = cart_opt.cart_seq
		left join fm_goods_image goods_img on goods_img.cut_number = 1 AND goods_img.image_type = 'thumbScroll' AND cart.goods_seq = goods_img.goods_seq
		,fm_goods goods
		,fm_goods_option goods_opt
		WHERE cart.distribution='cart'
		AND cart.goods_seq = goods.goods_seq
		AND goods.goods_status = 'normal'
		AND goods.goods_view = 'look'
		AND cart.goods_seq = goods_opt.goods_seq
		AND ifnull(cart_opt.option1,'') = ifnull(goods_opt.option1,'')
		AND ifnull(cart_opt.option2,'') = ifnull(goods_opt.option2,'')
		AND ifnull(cart_opt.option3,'') = ifnull(goods_opt.option3,'')
		AND ifnull(cart_opt.option4,'') = ifnull(goods_opt.option4,'')
		AND ifnull(cart_opt.option5,'') = ifnull(goods_opt.option5,'') ".$add_and." ORDER BY cart.goods_seq,cart_opt.cart_option_seq ASC {$limit}");

		foreach ($query->result_array() as $data) {

			// 해당 상품의 전체 카테고리
			$category	= array();
			$tmp		= $this->goodsmodel->get_goods_category($data['goods_seq']);
			foreach($tmp as $row)	$category[]		= $row['category_code'];

			//----> sale library 적용
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $data['consumer_price'];
			$param['price']						= $data['price'];
			$param['total_price']				= $data['price'];
			$param['ea']						= 1;
			$param['goods_ea']					= 1;
			$param['category_code']				= $category;
			$param['goods_seq']					= $data['goods_seq'];
			$param['goods']						= $data;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);
			$data['sale_price']					= $sales['result_price'];
			$this->sale->reset_init();
			//<---- sale library 적용

			$display_item[] = $data;
		}
		return $display_item;
	}


	##### START : 개인결제 및 관리자 주문 관련 추가 함수 @2015-01-21 #####


	// 장바구니 목록 데이터 추출
	// @dataType : enum(cart, direct)
	// @directCart : [goodsSeq]=>([optionType enum(opt, sub)]=>([optionSeq/suboptionSeq]=>ea)))
	public function cart_list_data($dataType = 'cart', $directCart = array()){

		// 상품 데이터 추출
		if	($dataType == 'direct'){
			$list	= $this->get_direct_goods_list($directCart);
		}else{
			$list	= $this->get_cart_goods_list();
		}
echo '<pre>';
print_r($list);
echo '</pre>';
exit;
	}

	// 직접 지정한 장바구니 상품 추출
	public function get_direct_goods_list($directCart){
		if	($directCart) foreach($directCart as $goods_seq => $optdata){
			$goodsSeqArr[]	= $goods_seq;
			if	($optdata){
				foreach($optdata as $optType => $optlist){
					if	($optlist) foreach($optlist as $optSeq => $ea){
						if	($optType == 'sub'){
							$suboptionSeqArr[]	= $optSeq;
						}else{
							$optionSeqArr[]		= $optSeq;
						}
					}
				}
			}else{
				$directCart[$goods_seq]	= array();
			}
		}

		// goods_seq 검색
		if	(is_array($goodsSeqArr) && count($goodsSeqArr) > 0){
			$goodsSeqSql		= " and g.goods_seq in ('".implode("', '", $goodsSeqArr)."') ";
		}
		// option_seq 검색
		if	(is_array($optionSeqArr) && count($optionSeqArr) > 0){
			$optionSeqSql		= " and opt.option_seq in ('".implode("', '", $optionSeqArr)."') ";
		}else{
			$optionSeqSql		= " and opt.default_option = 'y' ";
		}
		// suboption_seq 검색
		if	(is_array($suboptionSeqArr) && count($suboptionSeqArr) > 0){
			$unionSuboption		= " union (
										select 
											g3.goods_seq			as goods_seq, 
											'sub'					as option_type, 
											''						as option_seq, 
											sub.suboption_seq		as suboption_seq, 
											sub.suboption_title		as option_title, 
											sub.suboption			as option1, 
											''						as option2, 
											''						as option3, 
											''						as option4, 
											''						as option5, 
											sub.consumer_price		as consumer_price, 
											sub.price				as price, 
											sub.reserve_rate		as reserve_rate, 
											sub.reserve_unit		as reserve_unit, 
											sub.reserve				as reserve, 
											sub.commission_rate		as commission_rate 
										from 
											fm_goods				as g3, 
											fm_goods_suboption		as sub  
										where 
											g3.goods_seq = sub.goods_seq and 
											g3.goods_status = 'normal' and 
											g3.goods_view = 'look' and 
											sub.suboption_seq in ('".implode("', '", $suboptionSeqArr)."')
											".str_replace('g.', 'g3.', $goodsSeqSql)."
									)";
		}

		$sql	= "select 
						g.goods_seq, g.goods_name, g.goods_code, g.goods_type,
						g.cancel_type, g.sale_seq, g.goods_kind, g.socialcp_event,
						g.shipping_weight_policy, g.goods_weight,
						g.shipping_policy, g.goods_shipping_policy,
						g.unlimit_shipping_price, g.limit_shipping_price,
						g.limit_shipping_ea, g.limit_shipping_subprice,
						g.min_purchase_ea, g.max_purchase_ea, g.reserve_policy,
						g.multi_discount_use, g.multi_discount_ea,
						g.multi_discount, g.multi_discount_unit, g.tax, 
						g.social_goods_group, g.socialcp_input_type, g.socialcp_cancel_type,
						g.socialcp_cancel_use_refund, g.socialcp_cancel_payoption,
						g.socialcp_cancel_payoption_percent, g.socialcp_use_return,
						g.socialcp_use_emoney_day, g.socialcp_use_emoney_percent,
						g.individual_refund, g.individual_refund_inherit, g.individual_export,
						g.individual_return, g.possible_pay, g.possible_mobile_pay,
						img.image, 
						opt.*
					from 
						fm_goods	as g 
						left join fm_goods_image img 
							on ( img.cut_number = 1 AND img.image_type = 'thumbCart' AND g.goods_seq = img.goods_seq ),  
						(
							(
								select 
									g2.goods_seq			as goods_seq, 
									'opt'					as option_type, 
									opt2.option_seq			as option_seq, 
									''						as suboption_seq, 
									opt2.option_title		as option_title, 
									opt2.option1			as option1, 
									opt2.option2			as option2, 
									opt2.option3			as option3, 
									opt2.option4			as option4, 
									opt2.option5			as option5, 
									opt2.consumer_price		as consumer_price, 
									opt2.price				as price, 
									opt2.reserve_rate		as reserve_rate, 
									opt2.reserve_unit		as reserve_unit, 
									opt2.reserve			as reserve, 
									opt2.commission_rate	as commission_rate 
								from 
									fm_goods				as g2, 
									fm_goods_option			as opt2 
								where 
									g2.goods_seq = opt2.goods_seq and 
									g2.goods_status = 'normal' and 
									g2.goods_view = 'look' 
									".str_replace('g.', 'g2.', $goodsSeqSql)."
									".str_replace('opt.', 'opt2.', $optionSeqSql)."
							)
							".$unionSuboption."
						)	opt 
					where 
						g.goods_seq = opt.goods_seq 
					order by g.goods_seq, opt.option_type 
					";
		$query	= $this->db->query($sql);
		$result	= $query->result_array();
/*
		if	($result)foreach($result as $k => $data){
			$optSeq		= ($data['option_type'] == 'sub') ? $data['suboption_seq'] : $data['option_seq'];
			$data['ea']	= 1;
			if	($directCart[$data['goods_seq']][$data['option_type']][$optSeq]){
				$data['ea']	= $directCart[$data['goods_seq']][$data['option_type']][$optSeq];
			}

			$directCart[$data['goods_seq']][$data['option_type']][$optSeq]	= $data;
		}

		if	($directCart) foreach($directCart as $goods_seq => $optdata){
			if	($optdata) foreach($optdata as $optType => $optlist){
				if	($optlist) foreach($optlist as $optSeq => $data){
					$return[]	= $data;
				}
			}
		}
*/
		return $result;
	}

	// 장바구니에서 상품 추출
	public function get_cart_goods_list(){
	}

	##### END : 개인결제 및 관리자 주문 관련 추가 함수 @2015-01-21 #####
}

/* End of file category.php */
/* Location: ./app/models/category */