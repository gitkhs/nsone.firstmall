<?php
class personcartmodel extends CI_Model {

	public function get_cart($cart_seq) {
		$query = "
			SELECT *
			FROM fm_person_cart
			WHERE cart_seq=?";
		$query = $this->db->query($query,array($cart_seq));
		list($returnArr) = $query->result_array();
		$query->free_result();
		return $returnArr;
	}

	public function get_cart_list($member_seq) {
		$session_id = $this->session->userdata('session_id');
		if($member_seq){
			$where_query[] = "member_seq = ?";
			$where_arr[] = $member_seq;
			$where_query[] = "person_seq = ?";
			$where_arr[] = "0";
		}else{
			$where_query[] = "session_id = ?";
			$where_arr[] = $session_id;
			$where_query[] = "person_seq = ?";
			$where_arr[] = "0";
		}
		$query = "SELECT * FROM fm_person_cart WHERE ".implode(' AND ',$where_query) ." order by cart_seq desc";
		$query = $this->db->query($query,$where_arr);
		foreach($query->result_array() as $row) $returnArr[] = $row;
		$query->free_result();
		return $returnArr;
	}

	public function get_cart_count() {
		$session_id = $this->session->userdata('session_id');
		if($this->userInfo['member_seq']){
			$where_query[] = "member_seq = ?";
			$where_arr[] = $this->userInfo['member_seq'];
		}else{
			$where_query[] = "session_id = ?";
			$where_arr[] = $session_id;
		}
		$query = "SELECT count(*) cnt FROM fm_person_cart WHERE distribution = 'cart' and ".implode(' AND ',$where_query) ." order by cart_seq desc";
		$query = $this->db->query($query,$where_arr);
		$row = $query->result_array();

		return $row[0]['cnt'];
	}

	public function get_cart_option($cart_seq) {
		$this->load->model('goodsmodel');
		$returnArr = "";
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where option_seq=goods.option_seq and goods_seq=goods.goods_seq) supply_price
			FROM fm_person_cart_option cart,fm_goods_option goods
			WHERE cart.option1=goods.option1
				AND cart.option2=goods.option2
				AND cart.option3=goods.option3
				AND cart.option4=goods.option4
				AND cart.option5=goods.option5
				AND goods.goods_seq =
				(
					select goods_seq from fm_person_cart where cart_seq=?
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

	public function get_cart_suboption($cart_seq) {
		$returnArr = "";
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where suboption_seq=goods.suboption_seq and goods_seq=goods.goods_seq) supply_price
			FROM fm_person_cart_suboption cart,fm_goods_suboption goods
			WHERE cart.suboption=goods.suboption AND cart.suboption_title=goods.suboption_title
				AND goods.goods_seq =
				(
					select goods_seq from fm_person_cart where cart_seq=?
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

	public function get_cart_input($cart_seq) {
		$returnArr = "";
		$query = "
			SELECT *
			FROM fm_person_cart_input
			WHERE cart_seq=?
			ORDER BY cart_input_seq DESC";
		$query = $this->db->query($query,array($cart_seq));
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		$query->free_result();
		return $returnArr;
	}

	public function delete($cart_seqs){

		$this->db->select('cart_seq');
		if($this->userInfo['member_seq']) $this->db->where('member_seq', $this->userInfo['member_seq']);
		else $this->db->where('session_id', $this->session->userdata('session_id'));
		$this->db->where_in('cart_seq',$cart_seqs);
		$query = $this->db->get('fm_person_cart');

		foreach ($query->result_array() as $row)
		{
			$tables = array('fm_person_cart_option', 'fm_person_cart_input', 'fm_person_cart_suboption', 'fm_person_cart');
			$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
		}
	}

	public function delete_option($cart_option_seq=null,$cart_suboption_seq=null){
		$this->db->select('cart_seq');
		if($this->userInfo['member_seq']) $this->db->where('member_seq', $this->userInfo['member_seq']);
		else $this->db->where('session_id', $this->session->userdata('session_id'));

		$cart_seq = null;

		if($cart_option_seq){
			$query = $this->db->query("select cart_seq from fm_person_cart_option where cart_option_seq=?",$cart_option_seq);
			$result = $query->row_array();
			$cart_seq = $result['cart_seq'];
		}
		if($cart_suboption_seq){
			$query = $this->db->query("select cart_seq from fm_person_cart_suboption where cart_suboption_seq=?",$cart_suboption_seq);
			$result = $query->row_array();
			$cart_seq = $result['cart_seq'];
		}

		if(!$cart_seq) return;

		if($cart_option_seq){
			$query = $this->db->query("delete from fm_person_cart_option where cart_option_seq=?",$cart_option_seq);
		}
		if($cart_suboption_seq){
			$query = $this->db->query("delete from fm_person_cart_suboption where cart_suboption_seq=?",$cart_suboption_seq);
		}

		$query = $this->db->query("
			select count(*) as cnt from fm_person_cart_option where cart_seq = '{$cart_seq}'
			union
			select count(*) as cnt from fm_person_cart_suboption where cart_suboption_seq = '{$cart_seq}'
		");
		$result = $query->result_array();
		if(!$result){
			$this->db->where('cart_seq',$cart_seq);
			$query = $this->db->get('fm_person_cart');
			foreach ($query->result_array() as $row)
			{
				$tables = array('fm_person_cart_option', 'fm_person_cart_input', 'fm_person_cart_suboption', 'fm_person_cart');
				$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
			}
		}
	}

	public function delete_mode($mode){
		$this->db->select('cart_seq');
		if($this->userInfo['member_seq']) $this->db->where('member_seq', $this->userInfo['member_seq']);
		else $this->db->where('session_id', $this->session->userdata('session_id'));
		$this->db->where('distribution',$mode);
		$query = $this->db->get('fm_person_cart');
		foreach ($query->result_array() as $row)
		{
			$tables = array('fm_person_cart_option', 'fm_person_cart_input', 'fm_person_cart_suboption', 'fm_person_cart');
			$this->db->delete($tables,array('cart_seq' => $row['cart_seq']));
		}
	}

	// 회원이면 이전에 담았던 장바구니 데이터를 합칩니다.
	public function merge_for_member($member_seq){

		$session_id = $this->session->userdata('session_id');

		$this->db->where('session_id',$session_id);
		$this->db->update('fm_person_cart', array('member_seq' => $member_seq));

		$carts = $this->get_cart_list();
		$arr_done = array();
		foreach($carts as $cart){
			if(!in_array($cart['goods_seq'],$arr_done)){
				$this->merge_for_goods($cart['goods_seq'],$cart['cart_seq'],$member_seq);
			}
			$arr_done[] = $cart['goods_seq'];
		}

	}

	public function merge_for_choice($member_seq){
		if($member_seq){
			$this->db->where('member_seq',$member_seq);
			$this->db->where('person_seq',"0");
			$this->db->where('distribution','choice');
		}else{
			$session_id = $this->session->userdata('session_id');
			$this->db->where('session_id',$session_id);
			$this->db->where('distribution','choice');
		}
		$this->db->update('fm_person_cart', array('distribution' => 'cart'));

		$carts = $this->get_cart_list($member_seq);
		$arr_done = array();
		foreach($carts as $cart){
			if(!in_array($cart['goods_seq'],$arr_done)){
				$this->merge_for_goods($cart['goods_seq'],$cart['cart_seq'],$member_seq);
			}
			$arr_done[] = $cart['goods_seq'];
		}
	}

	public function cart_list($member_seq="", $person_seq="0"){
		$total = 0;
		$total_point =0;
		$result = "";
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
//		$this->load->model('promotionmodel');
		$this->load->model('membermodel');

		$mode = 'cart';

		$where_arr[] = $mode;
		$where_query[] = "cart.member_seq = ?";
		$where_arr[] = $member_seq;



		$where_query[] = "cart.person_seq = ?";
		$where_arr[] = $person_seq;



		$query = "
		SELECT cart.cart_seq,cart.fblike,
		goods.goods_seq,goods.goods_name,goods.goods_code,
		goods.shipping_weight_policy,goods.goods_weight,
		goods.shipping_policy,goods.goods_shipping_policy,
		goods.unlimit_shipping_price,goods.limit_shipping_price,
		goods.limit_shipping_ea,goods.limit_shipping_subprice,
		goods_img.image,
		(
			SELECT sum(ea)
			FROM fm_person_cart_suboption
			WHERE cart_seq=cart.cart_seq
		) sub_ea,
		sum(cart_opt.ea) ea,
		(
			SELECT COUNT(cart_suboption_seq)
			FROM fm_person_cart_suboption
			WHERE cart_seq=cart.cart_seq
		) sub_cnt,
		(
			SELECT SUM(g.price*s.ea)
			FROM fm_goods_suboption g,fm_person_cart_suboption s
			WHERE g.goods_seq=cart.goods_seq
			AND g.suboption=s.suboption
			AND g.suboption_title=s.suboption_title
			AND s.cart_seq=cart.cart_seq
		) sub_price,
		(
			SELECT SUM(g.reserve*s.ea)
			FROM fm_goods_suboption g,fm_person_cart_suboption s
			WHERE g.goods_seq=cart.goods_seq
			AND g.suboption=s.suboption
			AND g.suboption_title=s.suboption_title
			AND s.cart_seq=cart.cart_seq
		) sub_reserve,
		goods_opt.price,
		goods_opt.reserve as reserve_unit,
		SUM(IF(cart_opt.option1!='',1,0)) opt_cnt,
		SUM(goods_opt.reserve*cart_opt.ea) reserve,
		goods.reserve_policy,
		goods.multi_discount_use,
		goods.multi_discount_ea,
		goods.multi_discount,
		goods.multi_discount_unit,
		goods.tax
		FROM fm_person_cart cart
		left join fm_goods_image goods_img on goods_img.cut_number = 1 AND goods_img.image_type = 'thumbCart' AND cart.goods_seq = goods_img.goods_seq
		,fm_goods goods
		,fm_person_cart_option cart_opt
		,fm_goods_option goods_opt
		WHERE cart.distribution=?
		AND cart.goods_seq = goods.goods_seq
		AND cart.cart_seq = cart_opt.cart_seq
		AND cart.goods_seq = goods_opt.goods_seq
		AND cart_opt.option1 = goods_opt.option1
		AND cart_opt.option2 = goods_opt.option2
		AND cart_opt.option3 = goods_opt.option3
		AND cart_opt.option4 = goods_opt.option4
		AND cart_opt.option5 = goods_opt.option5";
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

			$cart_options = $this->personcartmodel->get_cart_option($row['cart_seq']);
			$cart_suboptions = $this->personcartmodel->get_cart_suboption($row['cart_seq']);
			$cart_inputs = $this->personcartmodel->get_cart_input($row['cart_seq']);
			$shipping = $this->goodsmodel->get_goods_delivery($row,$row['ea']);
			$suboptions = $this->goodsmodel->get_goods_suboption($row['goods_seq']);

			$cnt_sub_required = 0;
			$suboption_title_required = '';
			if (is_array($suboptions)) {
				foreach ($suboptions as $key_option => $data_option) {
					foreach($data_option as $k => $sub_opt){
						if ($sub_opt['sub_required'] == 'y') {
							$suboption_title_required = $sub_opt['suboption_title'];
							$cnt_sub_required++; 
						}
					}
				}
			}
			$row['cnt_sub_required']	 = $cnt_sub_required;
			$row['suboption_title_required']	 = $suboption_title_required;

			$row['goods_name'] = strip_tags(str_replace(array("\"","'"),'',$row['goods_name']));

			$arr_multi = array(
				'multi_discount_use' => $row['multi_discount_use'],
				'multi_discount_ea' => $row['multi_discount_ea'],
				'multi_discount' => $row['multi_discount'],
				'multi_discount_unit' => $row['multi_discount_unit']
			);

			// debug_var($this->config_system['cutting_price']);

			if($row['reserve_policy'] == 'shop') $row['reserve'] = 0;//기본정책시 초기화

			$row['point'] = 0;
			foreach($cart_options as $key_option => $data_option){

				$data_option['ori_price'] = $data_option['price'];
				$categorys = $this->goodsmodel->get_goods_category($data_option['goods_seq']);
				foreach($categorys as $key => $data) $arr_category = $this->categorymodel->split_category($data['category_code']);
				/*
				// 이벤트 할인 /적립
				$data_option['event'] = $this->goodsmodel->get_event_price($data_option['ori_price'], $row['goods_seq'], $arr_category, $data_option['consumer_price'],$data_option);
				if($data_option['event']['event_seq']) {
					if($data_option['event']['target_sale'] == 1 && $data_option['consumer_price'] > 0 ){//정가기준 할인시
						$data_option['price'] = ($data_option['consumer_price'] > $data_option['event']['event_sale_unit'])?$data_option['consumer_price'] - (int) $data_option['event']['event_sale_unit']:0;
					}else{
						$data_option['price'] = ($data_option['price'] > $data_option['event']['event_sale_unit'])?$data_option['price'] - (int) $data_option['event']['event_sale_unit']:0;
					}
				}
				*/
				/**$r_event = $this->goodsmodel->get_event_price($data_option['ori_price'], $row['goods_seq'], $arr_category);
				$data_option['price'] -= (int) $r_event['event_sale_unit'];**/

				// 복수구매 할인
				//$data_option['price'] = (int) $this->goodsmodel->get_multi_sale_price($row['ea'],$data_option['price'],$arr_multi);
				$row['tot_price'] += $data_option['price'] * $data_option['ea'];

				// 적립금 계산 -> goods 인경우 sql 에서 이미처리됨
				if($row['reserve_policy'] == 'shop') {//기본정책시
					$data_option['reserve'] = $this->goodsmodel->get_reserve_with_policy($row['reserve_policy'],$data_option['price'],$cfg_reserve['default_reserve_percent'],$data_option['reserve_rate'],$data_option['reserve_unit'],$data_option['reserve']);
					$data_option['reserve'] += (int) $r_event['event_reserve_unit'];
					$row['reserve'] += $data_option['reserve'] * $data_option['ea'];
				}

				###optoin point
				$data_option['point'] = (int) $this->goodsmodel->get_point_with_policy($data_option['price']);
				$row['point'] += ($data_option['point'] * $data_option['ea']);

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


			###
			if($row['tax']!="tax"){
				$exempt_chk++;
			}

			$total_point += $row['point'];
			$total_reserve += $row['reserve'];
			$total += $row['tot_price'];
			$result[] = $row;
		}


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
					//프로모션코드할인
					/*
					$promotions = $this->promotionmodel->get_able_download_saleprice($data_option['promotion_code_seq'],$data_option['promotion_code_serialnumber'], $total, $data_option['price'],$data_option['ea']);
					$result[$k]['cart_options'][$key_option]['promotioncode_sale'] = (int) $promotions['promotioncode_sale'];
					$result[$k]['promocodeSale'] += (int) $promotions['promotioncode_sale'];
					$promocodeSale += (int) $promotions['promotioncode_sale'];
					*/
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
		$this->db->where('person_seq',"0");
		$this->db->where('goods_seq',$goods_seq);
		$this->db->where('cart_seq !=',$cart_seq);
		$this->db->where('distribution','cart');
		$this->db->select('cart_seq');
		$query = $this->db->get('fm_person_cart');

		foreach($query->result_array() as $row){
			$pre_cart_seq = $row['cart_seq'];

			$this->db->where('cart_seq', $pre_cart_seq);
			$this->db->update('fm_person_cart_option', array('cart_seq'=>$cart_seq));

			$this->db->where('cart_seq', $pre_cart_seq);
			$this->db->update('fm_person_cart_suboption', array('cart_seq'=>$cart_seq));

			$this->db->where('cart_seq', $pre_cart_seq);
			$this->db->update('fm_person_cart_input', array('cart_seq'=>$cart_seq));

			$this->db->delete('fm_person_cart',array('cart_seq' => $pre_cart_seq));
		}


	}

	public function get_cart_option_by_cart_option($cart_option_seq)
	{
		$bind[0] = $cart_option_seq;
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where option_seq=goods.option_seq and goods_seq=goods.goods_seq) supply_price
			FROM fm_person_cart_option cart,fm_goods_option goods
			WHERE cart.cart_option_seq=?
			AND goods.goods_seq =
			(
				select goods_seq from fm_person_cart where cart_seq=cart.cart_seq
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
		$query = "select * from fm_person_cart where cart_seq = (select cart_seq from fm_person_cart_option where cart_option_seq=?)";
		$query = $this->db->query($query,$bind);
		$data = $query->row_array();
		return $data;
	}

	public function get_cart_suboption_by_cart_option($cart_option_seq)
	{
		$returnArr = "";
		$query = "
			SELECT cart.*,goods.price,goods.consumer_price,goods.goods_seq,
			goods.reserve_rate,goods.reserve_unit,goods.reserve,goods.commission_rate,
			(select supply_price from fm_goods_supply where suboption_seq=goods.suboption_seq and goods_seq=goods.goods_seq) supply_price
			FROM fm_person_cart_suboption cart,fm_goods_suboption goods
			WHERE cart.suboption=goods.suboption AND cart.suboption_title=goods.suboption_title
				AND goods.goods_seq =
				(
					select goods_seq from fm_person_cart where cart_seq=cart.cart_seq
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
			FROM fm_person_cart_input
			WHERE cart_option_seq=?
			ORDER BY cart_input_seq DESC";
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
		$query="delete from fm_person_cart_option where cart_option_seq=?";
		$this->db->query($query,$bind);
		$query="delete from fm_person_cart_suboption where cart_option_seq=?";
		$this->db->query($query,$bind);
		$query="delete from fm_person_cart_input where cart_option_seq=?";
		$this->db->query($query,$bind);

		if($mode == 'del'){
			$query = "select count(*) cnt from fm_person_cart_option where cart_seq=?";
			$query = $this->db->query($query,array($cart_seq));
			$data = $query->row_array();
			$cnt = $data['cnt'];
			if($cnt==0){
				$query="delete from fm_person_cart where cart_seq=?";
				$this->db->query($query,array($cart_seq));
			}
		}
	}

	public function catalog($member_seq="", $person_seq="0")
	{
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('promotionmodel');
		$this->load->model('cartmodel');
		$this->load->library('sale');

		$mode						= 'cart';
		$applypage					= 'saleprice';
		$total						= 0;
		$total_point				= 0;
		$result						= "";
		$where_query				= "";
		$shop_total_price			= 0;
		$shop_total_price_exempt	= 0;
		$exempt_chk					= 0;
		$shop_shipping_policy		= "";
		$default_box_ea				= false;
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

		$query = "
		SELECT
		cart.fblike,
		goods.goods_seq,goods.goods_name,goods.goods_code,goods.goods_kind,
		goods.shipping_weight_policy,goods.goods_weight,
		goods.shipping_policy,goods.goods_shipping_policy,
		goods.unlimit_shipping_price,goods.limit_shipping_price,
		goods.limit_shipping_ea,goods.limit_shipping_subprice,
		goods_img.image,
		(select supply_price from fm_goods_supply where option_seq=goods_opt.option_seq) supply_price,
		goods_opt.price,
		goods_opt.reserve_unit as reserve_unit,
		goods_opt.reserve*cart_opt.ea reserve,
		goods.reserve_policy,
		goods.multi_discount_use,
		goods.multi_discount_ea,
		goods.multi_discount,
		goods.multi_discount_unit,
		goods.tax,
		cart_opt.*
		FROM
		fm_person_cart_option cart_opt
		left join fm_person_cart cart on cart.cart_seq = cart_opt.cart_seq
		left join fm_goods_image goods_img on goods_img.cut_number = 1 AND goods_img.image_type = 'thumbCart' AND cart.goods_seq = goods_img.goods_seq
		,fm_goods goods
		,fm_goods_option goods_opt
		WHERE cart.distribution=?
		AND cart.goods_seq = goods.goods_seq
		AND goods.goods_status = 'normal'
		AND cart.goods_seq = goods_opt.goods_seq
		AND cart_opt.option1 = goods_opt.option1
		AND cart_opt.option2 = goods_opt.option2
		AND cart_opt.option3 = goods_opt.option3
		AND cart_opt.option4 = goods_opt.option4
		AND cart_opt.option5 = goods_opt.option5
		AND cart.member_seq = ? ";
		if($person_seq)	$query	.= " AND cart.person_seq = ? ";
		else			$query	.= " AND (cart.person_seq = ? or cart.person_seq is null) ";
		$query		.= "ORDER BY cart.goods_seq,cart_opt.cart_option_seq DESC ";
		$query		= $this->db->query($query, array($mode, $member_seq, $person_seq));
		$cart_list	= $query->result_array();
		foreach ($cart_list as $row){
			$goods_ea[$row['goods_seq']]	+= $row['ea'];			
			$r_cart_option[]				= $row;
		}
		foreach ($cart_list as $row){

			// 가격합산 오류로 주석처리 leewh 2014-11-25
			//$r_goods[$row['goods_seq']]	= $row;

			// 추가옵션
			$cart_suboptions	= $this->get_cart_suboption_by_cart_option($row['cart_option_seq']);
			// 추가입력사항
			$cart_inputs		= $this->get_cart_input_by_cart_option($row['cart_option_seq']);

			// 할인 미적용가
			$row['org_price']		= $row['price'];

			// 상품명 특수문자 처리
			$row['goods_name']		= strip_tags(str_replace(array("\"","'"),'',$row['goods_name']));

			// 카테고리정보
			if($r_goods[$row['goods_seq']]['r_category']){
				$row['r_category'] = $r_goods[$row['goods_seq']]['r_category'];
			}else{
				$categorys = $this->goodsmodel->get_goods_category($row['goods_seq']);
				foreach($categorys as $key => $data) $row['r_category'] = $this->categorymodel->split_category($data['category_code']);
				$r_goods[$row['goods_seq']]['r_category'] = $row['r_category'];
			}

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
			$row['event']					= $this->sale->cfgs['event'];
			$row['price']					= $sales['one_result_price'];
			$row['reserve']					= $sales['one_tot_reserve'];
			$row['point']					= $sales['one_tot_point'];
			$row['eventEnd']				= $sales['eventEnd'];
			$this->sale->reset_init();
			unset($sales);
			//<---- sale library 적용

			$row['ori_price']	= $row['price'];
			$row['tot_price']	+= $row['price'] * $row['ea'];

			// 적립금계산
			if($row['reserve_policy'] == 'shop') {
				$row['reserve'] += $this->goodsmodel->get_reserve_with_policy($row['reserve_policy'],$row['price'],$cfg_reserve['default_reserve_percent'],$row['reserve_rate'],$row['reserve_unit'],$row['reserve']);
				$row['reserve'] = $row['reserve'] * $row['ea'];
			}

			// 포인트계산
			$row['point'] += (int) $this->goodsmodel->get_point_with_policy($row['price']);
			$row['point'] = $row['point'] * $row['ea'];

			// 장바구니 row수
			$r_goods[$row['goods_seq']]['cnt']++;
			if($r_goods[$row['goods_seq']]['cnt'] == 1){
				$row['first'] = 1;
			}else{
				$row['first'] = 0;
			}

			//suboption point
			foreach($cart_suboptions as $key_suboption => $data_suboption)
			{
				// 추가 옵션 포인트 계산
				$data_suboption['point'] = (int) $this->goodsmodel->get_point_with_policy($data_suboption['price']);
				$data_suboption['point'] += $data_suboption['point'] * $data_suboption['ea'];

				// 추가 옵션 적립금 계산
				$data_suboption['reserve'] = $this->goodsmodel->get_reserve_with_policy($row['reserve_policy'],$data_suboption['price'],$cfg_reserve['default_reserve_percent'],$data_suboption['reserve_rate'],$data_suboption['reserve_unit'],$data_suboption['reserve']);
				$data_suboption['reserve'] = $data_suboption['reserve'] * $data_suboption['ea'];
				$cart_suboptions[$key_suboption] = $data_suboption;

				// 상품별로 가격 적립금 포인트 업데이트
				$r_goods[$row['goods_seq']]['reserve'] += (int) $data_suboption['reserve'];
				$r_goods[$row['goods_seq']]['point'] += (int) $data_suboption['point'];
				$r_goods[$row['goods_seq']]['ea'] += (int) $data_suboption['ea'];
				$r_goods[$row['goods_seq']]['price'] += $data_suboption['price'] * $data_suboption['ea'];

				// 장바구니 row수
				$r_goods[$row['goods_seq']]['cnt']++;
			}

			$row['cart_suboptions'] = $cart_suboptions ? $cart_suboptions : array();
			$row['cart_inputs'] = $cart_inputs;

			// 상품별로 가격 적립금 포인트 업데이트
			$r_goods[$row['goods_seq']]['price'] += $row['price'] * $row['ea'];
			$r_goods[$row['goods_seq']]['ea'] += $row['ea'];
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

			// 과세 비과세
			if($row['tax']!="tax"){
				$exempt_chk++;
			}

			$result[] = $row;
		}

		### 과세 비과세 여부
		if($query->num_rows()==$exempt_chk){
			$tax_type = "exempt";
		}else if($exempt_chk == 0){
			$tax_type = "tax";
		}else{
			$tax_type = "mix";
		}

		// 배송비 계산
		$row = array();
		foreach($r_goods as $goods_seq => $row){
			$shipping = $this->goodsmodel->get_goods_delivery($row,$row['ea']);
			$row['goods_shipping'] = 0;
			if($row['shipping_policy'] == 'shop'){
				$shop_total_price += $row['price'];
				if($row['tax']!="tax"){
					$shop_total_price_exempt += $row['price'];
				}
				$shop_shipping_policy = $shipping;
				$default_box_ea = true;
			}else{
				$shop_total_price += $row['tot_price'];
				$row['goods_shipping'] = $shipping['price'];
				$shipping_price['goods'] += $row['goods_shipping'];
				$box_ea += $shipping['box_ea'];
				if($row['tax']!="tax"){
					$shop_total_price_exempt += $row['price'];
					$shipping_exempt += $shipping['price'];
				}
			}
			$r_goods[$goods_seq] = $row;

			// 총포인트
			$total_point += $row['point'];

			// 총 적립금
			$total_reserve += $row['reserve'];

			// 총할인가
			$total += $row['price'];

			// 총수량
			$total_ea += $row['ea'];
		}

		// 회원 추가 적립금/포인트 계산
		if($member_seq && $result) foreach($result as $k => $row){

			$add_reserve = (int) $this->membermodel->get_group_addreseve($member_seq,$row['price'],$total,$row['goods_seq'],$row['r_category']);
			$row['reserve'] += $add_reserve * $row['ea'];
			$total_reserve += $add_reserve * $row['ea'];

			$add_point = (int) $this->membermodel->get_group_addreseve($member_seq,$row['price'],$total,$row['goods_seq'],$row['r_category'],'point');
			$row['point'] += $add_point*$row['ea'];
			$total_point+= $add_point*$row['ea'];

			$result[$k] = $row;
		}

		// 프로모션코드 할인
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
		if($shop_total_price && $shop_shipping_policy['free']){
			if($shop_shipping_policy['free'] <= $shop_total_price){
				$shipping_price['shop'] = 0;
			}
		}

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
			'box_ea'=>$box_ea
		);

		return $arr;
	}

	function insert_cart_alloption($cart_seq,$inputs)
	{

		// 장바구니 옵션 저장
		unset($insert_data);
		foreach($_POST['optionEa'] as $k1 => $ea){
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

			if( $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')) ){
				$sc['whereis'] = " and promotion_input_serialnumber ='".$this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'))."'";
				$promotioncode = $this->promotionmodel->get_data($sc);
				$promotioncode = $promotioncode[0];

				if( strstr($promotioncode['type'],'promotion') ){//일반코드인경우
					$promotions = $this->promotionmodel->get_able_promotion_list($goods_seq, $category, $brand_code, $sum_goods_price, $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')), $cart_option['price'], $ea );
				}else{//개별코드
					$promotions = $this->promotionmodel->get_able_download_list($goods_seq, $category, $brand_code, $sum_goods_price, $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id')), $cart_option['price'], $ea );
				}

				if($promotions){
					$insert_data['promotion_code_seq']	= $this->session->userdata('cart_promotioncodeseq_'.$this->session->userdata('session_id'));
					$insert_data['promotion_code_serialnumber']	= $this->session->userdata('cart_promotioncode_'.$this->session->userdata('session_id'));
				}
			}

			$this->db->insert('fm_person_cart_option', $insert_data);

			// 첫번째 상품옵션의 일련번호 구하기
			if($k1 == 0) $cart_option_seq = $this->db->insert_id();
		}

		// 장바구니 추가입력사항 저장
		unset($insert_data);
		if( isset($_POST['inputsValue']) ){
			$i = 0;
			$k =0;
			foreach($inputs as $key_input => $data_input){
				if( $data_input ){
					if($data_input['input_form']=='file' && $_FILES['inputsValue']['tmp_name'][$k] ){
						move_uploaded_file($_FILES['inputsValue']['tmp_name'][$key_input], $path.$_FILES['inputsValue']['name'][$k]);
						$insert_data['type'] = 'file';
						$insert_data['input_title'] = $data_input['input_name'];
						$insert_data['input_value'] = $_FILES['inputsValue']['name'][$k];
						$k++;
					}else{
						$insert_data['type'] = 'text';
						$insert_data['input_title'] = $data_input['input_name'];
						$insert_data['input_value'] = $_POST['inputsValue'][$i];
					}
					$insert_data['cart_seq'] = $cart_seq;
					$insert_data['cart_option_seq']	= $cart_option_seq;
					$this->db->insert('fm_person_cart_input', $insert_data);
					$i++;
				}
			}
		}

		// 장바구니 추가옵션 저장
		unset($insert_data);
		if( isset($_POST['suboption']) ){
			foreach($_POST['suboption'] as $k1 => $suboption){
				$insert_data['ea']				= $_POST['suboptionEa'][$k1];
				$insert_data['suboption_title']	= $_POST['suboptionTitle'][$k1];
				$insert_data['suboption']		= $suboption;
				$insert_data['cart_seq'] 		= $cart_seq;
				$insert_data['cart_option_seq']	= $cart_option_seq;
				$this->db->insert('fm_person_cart_suboption', $insert_data);
			}
		}
	}
}

/* End of file category.php */
/* Location: ./app/models/category */