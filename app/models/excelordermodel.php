<?php
class Excelordermodel extends CI_Model {
	var $downloadType		= "Excel5";
	var $saveurl			= "data/tmp";
	
	var $order_cellphones = array();
	var $recipient_cellphones = array();
	var $arr_params = array();
	var $recipient_arr_params = array();
	var $order_no = array();
	var $recipient_order_no = array();
	var $order_count = 0;
	var $recipient_count = 0;

	var $cell = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

	var $itemList = array(

		"order_seq"							=> "*주문번호",
		"delivery_company_code"	=> "*택배사코드",
		"delivery_number"				=> "*송장번호",
		"international"						=> "*국내/해외배송",
		"shipping_method"				=> "*배송구분",
		"option"								=> "*옵션",
		"ea"										=> "*전체수량",
		"refund_ea"							=> "*취소수량",
		"export_ea"							=> "*출고완료수량",
		"request_ea"						=> "*출고할 수량",
		"order_user_name"				=> "주문자명",
		"shipping_seq"			=> "*배송지고유값",
		"recipient_user_name"		=> "수령인",
		"recipient_cellphone"			=> "수령인휴대폰",
		"recipient_phone"				=> "수령인연락처",
		"recipient_zipcode"				=> "우편번호",
		"recipient_address"				=> "주소(지번)",
		"recipient_address_street"		=> "주소(도로명)",
		"recipient_address_detail"	=> "상세주소",
		"recipient_address_all"		=> "전체주소(지번)",
		"recipient_address_street_all"		=> "전체주소(도로명)",
		"goods_name"						=> "상품명",
		"purchase_goods_name"			=> "매입용 상품명",
		"consumer_price"					=> "정가",
		"price"									=> "할인가",
		"ea_price"							=> "할인가x수량",
		"tax"									=> "과세여부",
		"goods_seq"						=> "상품고유번호",
		"goods_code"						=> "주문상품코드",
		"regist_date"						=> "주문일",
		"userid"								=> "주문자아이디",
		"order_phone"						=> "주문자연락처",
		"order_cellphone"				=> "주문자휴대폰",
		"order_email"						=> "주문자이메일",

		"complete_date"					=> "출고완료일",
		"shipping_date"					=> "배송완료일",
		"memo"								=> "사용자메모",
		"admin_memo"					=> "관리자메모",

		"supply_price"						=> "매입가",

		"goods_shipping_cost"		=> "개별배송비",
		"goods_coupon_sale"			=> "상품쿠폰할인",
		"promotion_code_sale"		=> "상품코드할인",
		"member_sale"					=> "회원등급할인",
		"mobile_sale"						=> "모바일할인",
		"fblike_sale"							=> "라이크할인",
		"reserve"								=> "지급 적립금",
		"point"									=> "지급 포인트",
		"shipping_coupon_sale"					=> "배송비쿠폰",
		"shipping_promotion_code_sale"	=> "배송비코드",
		"shipping_cost"						=> "기본배송비",
		"emoney"								=> "적립금사용",
		"cash"									=> "이머니사용",
		"enuri"									=> "에누리",
		"settleprice"							=> "결제금액",
		"deposit_date"					=> "결제일",
		"payment"							=> "결제방법",
	);


	var $requireds = array(
		"order_seq",
		"shipping_seq",
		"international",
		"shipping_method",
		"delivery_company_code",
		"delivery_number",
		"option",
		"ea",
		"refund_ea",
		"export_ea",
		"request_ea"
	);


	var $items = array(
		"ea_price",
		"tax",
	);

	var $orders = array(
		"shipping_coupon_sale",
		"shipping_promotion_code_sale",
		"shipping_cost",
		"emoney",
		"cash",
		"enuri"
	);

	var $temp = array(
		"item_seq"			=> "*옵션고유값",
		"title1"					=> "*옵션명1",
		"option1"				=> "*옵션값1",
		"title2"					=> "*옵션명2",
		"option2"				=> "*옵션값2",
		"title3"					=> "*옵션명3",
		"option3"				=> "*옵션값3",
		"title4"					=> "*옵션명4",
		"option4"				=> "*옵션값4",
		"title5"					=> "*옵션명5",
		"option5"				=> "*옵션값5",
		"subtitle"				=> "*추가옵션명",
		"suboption"			=> "*추가옵션값",
		"subinputoption"	=> "추가입력옵션"
	);
	var $temp_arr = array(
		"item_seq",
		"title1",
		"option1",
		"title2",
		"option2",
		"title3",
		"option3",
		"title4",
		"option4",
		"title5",
		"option5",
		"subtitle",
		"suboption",
		"subinputoption"
	);


	public function excel_cell($count){
		$cell =$count;
		$char = 26;
		for($i=0;$i<$cell;$i++) {
			if($i<$char) $alpha[] = $this->cell[$i];
			else {
				$idx1 = (int)($i-$char)/$char;
				$idx2 = ($i-$char)%$char;
				$alpha[] = $this->cell[$idx1].$this->cell[$idx2];
			}
		}
		return $alpha;
	}
	public function excel_num($column){
		$cell =100;
		$char = 26;
		for($i=0; $i<$cell; $i++) {
			if($i < $char){
				$alpha[] = $this->cell[$i];
				if($column==$this->cell[$i]) return $i;
			}else {
				$idx1 = (int)($i-$char)/$char;
				$idx2 = ($i-$char)%$char;
				$alpha[] = $this->cell[$idx1].$this->cell[$idx2];
				if($column==$this->cell[$idx1].$this->cell[$idx2]) return $i;
			}

		}
	}

	//필수항목 체크
	public function requiredsck($titleitems, $criteria = null){
		$criteriaar = array('option','ea','refund_ea','export_ea','request_ea');
		$requiredsnum=0;
		for($i=0;$i<count($this->requireds);$i++) {
			if( in_array($this->requireds[$i], $criteriaar ) && $criteria=='ORDER' ) {
				$requiredsnum++;//
				continue;
			}

			if( in_array($this->requireds[$i], $titleitems ) ){
				$requiredsnum++;
			}
		}
		if($requiredsnum != count($this->requireds)){
			$diff = array_diff($this->requireds,$titleitems);
			foreach($diff as $k=>$v) $diff[$k] = $this->itemList[$v];
			openDialogAlert('다운로드 양식의 필수항목('.implode(',',$diff).') 이 빠져 있습니다.<br/>다운로드 양식을 다시한번 확인해 주세요.',600,140,'parent','');
			exit;
		}
		return true;
	}

	public function get_shipping_item_tot($order_seq,$shipping_seq){

		$this->load->model('ordermodel');

		$query = "
			SELECT
				a.*, 
				(select purchase_goods_name from fm_goods where goods_seq = a.goods_seq) as purchase_goods_name
			FROM
				fm_order_item a
				inner join fm_order_shipping_item b on (b.shipping_seq=? and a.item_seq=b.order_item_seq)
			WHERE
				a.order_seq=?";
		$query = $this->db->query($query,array($shipping_seq,$order_seq));
		$items = $query->result_array();

		$tot['count']				= count($items);
		foreach($items as $k=>$item){
			$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];

			$tot['item_seq']		= $item['item_seq'];
			$tot['goods_name']		.= $item['goods_name'].', ';//@주문상품
			$tot['purchase_goods_name']		.= $item['purchase_goods_name'].', ';//@주문상품 도매상품명
			$tot['goods_code']		.= $item['goods_code'].', ';//@주문상품
			$tot['goods_seq']		.= $item['goods_seq'].', ';//@주문상품
		}

		$query = "
			SELECT
				a.*,
				c.*,
				b.ea
			FROM
				fm_order_item a
				inner join fm_order_shipping_item_option b on (b.shipping_seq=? and a.item_seq=b.order_item_seq)
				inner join fm_order_item_option c on b.order_item_option_seq = c.item_option_seq
			WHERE
				a.order_seq=?";
		$query = $this->db->query($query,array($shipping_seq, $order_seq));
		$options = $query->result_array();
		foreach($options as $k=>$data){
			$data['out_supply_price'] = $data['supply_price']*$data['ea'];
			$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
			$data['out_price'] = $data['price']*$data['ea'];
			$data['out_member_sale'] = $data['member_sale']*$data['ea'];
			$data['out_reserve'] = $data['reserve']*$data['ea'];
			$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
			$options[$k] = $data;
			$tot['ea'] += $data['ea'];
			$tot['supply_price'] += $data['out_supply_price'];
			$tot['consumer_price'] += $data['out_consumer_price'];
			$tot['price'] += $data['out_price'];
			$tot['coupon_sale'] += $data['coupon_sale'];
			$tot['member_sale'] += $data['out_member_sale'];
			$tot['reserve'] += $data['out_reserve'];
			$tot['real_stock'] += $real_stock;
			$tot['stock'] += $stock;
			$tot['title1']			= $data['title1'];
			$tot['option1']			= $data['option1'];
		}

		$query = "
			SELECT
				a.*,
				c.*,
				(select purchase_goods_name from fm_goods where goods_seq = a.goods_seq) as purchase_goods_name,
				b.ea
			FROM
				fm_order_item a
				inner join fm_order_shipping_item_option b on (b.shipping_seq=? and a.item_seq=b.order_item_seq)
				inner join fm_order_item_suboption c on b.order_item_suboption_seq = c.item_suboption_seq
			WHERE
				a.order_seq=?";
		$query = $this->db->query($query,array($shipping_seq, $order_seq));
		$options = $query->result_array();
		foreach($options as $k=>$data){
			$data['out_supply_price'] = $data['supply_price']*$data['ea'];
			$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
			$data['out_price'] = $data['price']*$data['ea'];
			$data['out_member_sale'] = $data['member_sale']*$data['ea'];
			$data['out_reserve'] = $data['reserve']*$data['ea'];
			$suboptions[$k] = $data;
			$tot['ea'] += $data['ea'];
			$tot['supply_price'] 	+= $data['out_supply_price'];
			$tot['consumer_price'] 	+= $data['out_consumer_price'];
			$tot['price'] 			+= $data['out_price'];
			$tot['member_sale'] 	+= $data['out_member_sale'];
			$tot['reserve'] 		+= $data['out_reserve'];
			$tot['real_stock'] 		+= $real_stock;
			$tot['stock'] 			+= $stock;
			$tot['title1']			= $data['title'];
			$tot['option1']			= $data['suboption'];

			$tot['item_seq']			= $data['item_seq'];
			$tot['goods_name']			= $data['goods_name'];
			$tot['purchase_goods_name']			= $data['purchase_goods_name'];//@주문상품 도매상품명
			$tot['goods_seq']			= $data['goods_seq'];
		}

		return $tot;
	}


	//주문별인경우 노출(상품정보 콤마로 구분 추가
	public function get_item($order_seq, $shipping_seq){
		$this->load->model('ordermodel');
		$items = $this->ordermodel->get_shipping_item($order_seq,$shipping_seq);

		/* 주문별한줄 입력/추가 옵션 표시로 상품명, 전체수량 출력 형식 변경 leewh 2014-09-15 */
		$arr_goods_name = array();

		foreach($items as $key=>$item){

			//$options 	= $this->ordermodel->get_option_for_item_by_shipping($item['item_seq'],$shipping_seq);
			//$suboptions = $this->ordermodel->get_suboption_for_item_by_shipping($item['item_seq'],$shipping_seq);
			//$subinputoptions = $this->ordermodel->get_input_for_item($item['item_seq']);

			unset($totitem);
			if($item['shipping_item_option']) foreach($item['shipping_item_option'] as $k => $data){
				unset($totitem);
				$temp_goods_name = "";

				$data['out_supply_price'] = $data['supply_price']*$data['ea'];
				$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
				$data['out_price'] = $data['price']*$data['ea'];

				$data['out_member_sale']				= $data['member_sale']*$data['ea'];
				$data['out_coupon_sale']				= ($data['download_seq'])?$data['coupon_sale']:0;
				$data['out_fblike_sale']					= $data['fblike_sale'];
				$data['out_mobile_sale']					= $data['mobile_sale'];
				$data['out_promotion_code_sale']	= $data['promotion_code_sale'];

				$data['out_reserve'] = $data['reserve']*$data['ea'];
				$data['out_point'] = $data['point']*$data['ea'];

				$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
				$options[$k] = $data;
				$tot['total_ea'] += $data['ea'];

				if($tot['ea']){
					$tot['ea']	.= ', '.$data['ea'];
				}else{
					$tot['ea']	= $data['ea'];
				}
				$tot['supply_price'] += $data['out_supply_price'];
				$tot['consumer_price'] += $data['out_consumer_price'];
				$tot['price'] += $data['out_price'];

				//promotion sale
				$tot['member_sale'] += $data['out_member_sale'];
				$tot['coupon_sale'] += $data['out_coupon_sale'];
				$tot['fblike_sale'] += $data['out_fblike_sale'];
				$tot['mobile_sale'] += $data['out_mobile_sale'];
				$tot['promotion_code_sale'] += $data['out_promotion_code_sale'];

				$tot['reserve'] += $data['out_reserve'];
				$tot['point'] += $data['out_point'];

				$tot['real_stock'] += $real_stock;
				$tot['stock'] += $stock;
				$tot['goods_code']	.= $data['goods_code'].', ';//@주문상품코드
				$tot['title1']			= $data['title1'];
				$tot['option1']		= $data['option1'];
				$tot['title2']			= $data['title2'];
				$tot['option2']		= $data['option2'];
				$tot['title3']			= $data['title3'];
				$tot['option3']		= $data['option3'];
				$tot['title4']			= $data['title4'];
				$tot['option4']		= $data['option4'];
				$tot['title5']			= $data['title5'];
				$tot['option5']		= $data['option5'];

				if($data['title1']) {
					//$totitem['option'] .= ($totitem['option'])?', '.$tot['title1'].':'.$tot['option1']:$tot['title1'].':'.$tot['option1'];
					$totitem['option'] .= $tot['title1'].':'.$tot['option1'];
				}

				if($data['title2']) {
					$totitem['option'] .= $tot['title2'].':'.$tot['option2'];
				}

				if($data['title3']) {
					$totitem['option'] .= $tot['title3'].':'.$tot['option3'];
				}

				if($data['title4']) {
					$totitem['option'] .= $tot['title4'].':'.$tot['option4'];
				}

				if($data['title5']) {
					$totitem['option'] .= $tot['title5'].':'.$tot['option5'];
				}

				if ($totitem['option']) $temp_goods_name = '(필수)'.$totitem['option'];

				if($data['inputs']) foreach($data['inputs'] as $i_data){
					if($i_data['title']) {
						$totitem['inputoption'] .= $i_data['title'].':'.$i_data['value'];
					}
				}
				if ($totitem['inputoption']) $temp_goods_name .= '(입력)'.$totitem['inputoption'];

				if($data['shipping_item_suboption']) foreach($data['shipping_item_suboption'] as $s_data){
					$s_data['out_supply_price'] = $s_data['supply_price']*$s_data['ea'];
					$s_data['out_consumer_price'] = $s_data['consumer_price']*$s_data['ea'];
					$s_data['out_price'] = $s_data['price']*$s_data['ea'];
					$s_data['out_reserve'] = $s_data['reserve']*$s_data['ea'];
					$s_data['out_point'] = $s_data['point']*$s_data['ea'];
					$s_data['out_member_sale'] = $s_data['member_sale']*$s_data['ea'];

					$suboptions[$k] = $s_data;
					//$tot['ea'] += $data['ea'];
					//$tot['ea']	.= $s_data['ea'].', ';
					$tot['ea']	.= ($tot['ea'])?', '.$s_data['ea']:$s_data['ea'];
					$tot['supply_price'] 	+= $s_data['out_supply_price'];
					$tot['consumer_price'] 	+= $s_data['out_consumer_price'];
					$tot['price'] 			+= $s_data['out_price'];

					$tot['member_sale'] 	+= $s_data['out_member_sale'];

					$tot['reserve'] 		+= $s_data['out_reserve'];
					$tot['point'] 			+= $s_data['out_point'];

					$tot['real_stock'] 		+= $real_stock;
					$tot['stock'] 				+= $stock;
					$tot['goods_code']	.= $s_data['goods_code'].', ';//@주문상품코드
					$tot['subtitle']			= $s_data['title'];
					$tot['suboption']		= $s_data['suboption'];

					if($s_data['title']) {
						//$totitem['suboption'] .= ($totitem['suboption'])?', '.$tot['subtitle'].':'.$tot['suboption']:$tot['subtitle'].':'.$tot['suboption'];
						$totitem['suboption'] .= ($totitem['suboption'])?', (추가)'.$tot['subtitle'].':'.$tot['suboption']:'(추가)'.$tot['subtitle'].':'.$tot['suboption'];
					}
				}

				if ($totitem['suboption']) $temp_goods_name .= ($temp_goods_name)?', '.$totitem['suboption']:$totitem['suboption'];
				$arr_goods_name[] = $item['goods_name'].$temp_goods_name;

				$arr_purchase_goods_name[] = $item['purchase_goods_name'];//@주문상품 도매상품명
			}

			/*
			if($subinputoptions) foreach($subinputoptions as $data){
				if($data['title']) {
					$totitem['inputoption'] .= ($totitem['inputoption'])?', '.$data['title'].':'.$data['value']:$data['title'].':'.$data['value'];
				}
			}
			*/

			$tot['item_seq']			= $item['item_seq'];

			if($totitem) {
				/*
				$tot['goods_name']		.= $item['goods_name'].'(-';
				if($totitem['option']){
					$tot['goods_name']		.= $totitem['option'];
				}

				if($totitem['suboption']){
					$tot['goods_name']		.= ($totitem['option'])?' + '.$totitem['suboption']:$totitem['suboption'];
				}

				if($totitem['inputoption']){
					$tot['goods_name']		.= ($totitem['option'] || $totitem['suboption'])?' + '.$totitem['inputoption']:$totitem['inputoption'];
				}
				$tot['goods_name']		.= '), ';
				*/

				$tot['goods_name'] = join(", ",$arr_goods_name);
				$tot['goods_name']		.= ', '; 

				$tot['purchase_goods_name'] = join(", ",$arr_purchase_goods_name);
				$tot['purchase_goods_name']		.= ', '; 

			}else{
				for($ii=0; $ii<count($item['shipping_item_option']); $ii++){
					$tot['goods_name']						.= $item['goods_name'].', ';//@주문상품
					$tot['purchase_goods_name']		.= $item['purchase_goods_name'].', ';//@주문상품 도매상품명
				}
			}

			//$tot['goods_code']		.= $item['goods_code'].', ';//@주문상품
			for($ii=0; $ii<count($item['shipping_item_option']); $ii++){
				$tot['goods_seq']		.= $item['goods_seq'].', ';//@주문상품
			}
			$tot['count']				= count($items);
			/*
			if($tot['ea']){
				$tot['ea'] .= ",".$data['ea'];
			}else{
				$tot['ea'] = $data['ea'];
			}
			*/
			$item['suboptions']	= $suboptions;
			$item['options']	= $options;
			$item['subinputoptions']	= $subinputoptions;
			$items[$key] 		= $item;
			$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
		}
		return $tot;
	}



	public function get_export_item($order_seq,$shipping_seq){
		$query = "
			SELECT
				*
			FROM
				fm_goods_export A LEFT JOIN fm_goods_export_item B ON A.export_code = B.export_code
			WHERE
				A.order_seq=? and A.shipping_seq=?";
		$query = $this->db->query($query,array($order_seq,$shipping_seq));
		foreach($query->result_array() as $data){
			$items['export_ea'] += (int) $data['ea'];
			$items['delivery_company_code']	= $data['delivery_company_code'];
			$items['delivery_number']				= $data['delivery_number'];
			$items['international']									= $data['international'];
			$items['international_shipping_method']		= $data['international_shipping_method'];
			$items['domestic_shipping_method']			= $data['domestic_shipping_method'];
			$items['international_delivery_no']				= $data['international_delivery_no'];
			$items['delivery_company_code']				= $data['delivery_company_code'];
			$items['export_date']					.= ($data['export_date'] && $data['export_date'] != '0000-00-00' )?$data['export_date'].', ':'';//출고일
			$items['shipping_date']				.= ($data['shipping_date'] && $data['shipping_date'] != '0000-00-00' )?$data['shipping_date'].', ':'';//배송완료일
			$items['complete_date']				.= ($data['complete_date'] && $data['complete_date'] != '0000-00-00' )?$data['complete_date'].', ':'';//출고완료일
		}
		return $items;
	}

	public function get_refund_item($order_seq, $shipping_seq){
		$query = "
			SELECT
				*
			FROM
				fm_order_refund A LEFT JOIN fm_order_refund_item B ON A.refund_code = B.refund_code
			WHERE
				A.order_seq=? AND B.shipping_seq=?";
		$query = $this->db->query($query,array($order_seq,$shipping_seq));
		foreach($query->result_array() as $data){
			$items['refund_ea'] += $data['ea'];
		}
		return $items;
	}

	public function get_item_option($seq){
		$datas = get_data("fm_order_item_option ",array("item_option_seq"=>$seq));
		return $datas[0];
	}
	public function get_sub_option($seq){
		$datas = get_data("fm_order_item_suboption ",array("item_suboption_seq"=>$seq));
		return $datas[0];
	}

	public function get_export_items($order_seq, $shipping_seq, $item_seq, $optseqvalue, $optseq){
		$optseqnot = ($optseq=='option_seq')?'suboption_seq':'option_seq';

		$query = "
			SELECT
				*
			FROM
				fm_goods_export A LEFT JOIN fm_goods_export_item B ON A.export_code = B.export_code
			WHERE
				A.order_seq=? AND A.shipping_seq=? AND B.item_seq=? AND B.".$optseq."=? AND (B.".$optseqnot." is null OR B.".$optseqnot." = '') ";
		$query = $this->db->query($query,array($order_seq,$shipping_seq,$item_seq,$optseqvalue));
		foreach($query->result_array() as $data){
			$items['export_ea'] += (int) $data['ea'];
			$items['delivery_company_code']	= $data['delivery_company_code'];
			$items['delivery_number']				= $data['delivery_number'];
			$items['international']									= $data['international'];
			$items['international_shipping_method']		= $data['international_shipping_method'];
			$items['domestic_shipping_method']			= $data['domestic_shipping_method'];
			$items['international_delivery_no']				= $data['international_delivery_no'];
			$items['delivery_company_code']				= $data['delivery_company_code'];
			$items['export_date']					= ($data['export_date'] && $data['export_date'] != '0000-00-00' )?$data['export_date']:'';//출고일
			$items['shipping_date']					= ($data['shipping_date'] && $data['shipping_date'] != '0000-00-00' )?$data['shipping_date']:'';//배송완료일
			$items['complete_date']					= ($data['complete_date'] && $data['complete_date'] != '0000-00-00' )?$data['complete_date']:'';//출고완료일
		}
		return $items;
	}


	public function get_refund_items($order_seq, $shipping_seq, $item_seq){
		$query = "
			SELECT
				*
			FROM
				fm_order_refund A LEFT JOIN fm_order_refund_item B ON A.refund_code = B.refund_code
			WHERE
				A.order_seq=? AND B.shipping_seq=? AND B.item_seq=?";
		$query = $this->db->query($query,array($order_seq,$shipping_seq,$item_seq));
		foreach($query->result_array() as $data){
			$items['refund_ea'] += $data['ea'];
		}
		return $items;
	}

	public function create_excel_list_for_all($_PARAM){

		// 검색조건이 없을 경우 기본 세팅 검색조건을 가져옵니다. ( 기본 isstep이 있음 )
		if( count($_PARAM) < 2 ){
			if($_COOKIE['order_list_search']){
				$arr = explode('&',$_COOKIE['order_list_search']);
				if($arr) foreach($arr as $data){
					$arr2 = explode("=",$data);
					if($arr2[0]!='regist_date' ){
						$key = explode('[',$arr2[0]);
						$_PARAM[$key[0]][ str_replace(']','',$key[1]) ] = $arr2[1];
					}else{
						if($arr2[1] == 'today'){
							$_PARAM['regist_date'][0] = date('Y-m-d');
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3day'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-3 day"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '7day'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-7 day"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '1mon'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-1 month"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == '3mon'){
							$_PARAM['regist_date'][0] = date('Y-m-d',strtotime("-3 month"));
							$_PARAM['regist_date'][1] = date('Y-m-d');
						}else if($arr2[1] == 'all'){
							$_PARAM['regist_date'][0] = '';
							$_PARAM['regist_date'][1] = '';
						}
						$_PARAM['regist_date_type'] = $arr2[1];
					}
				}
			}else{
				// 기본세팅이 없는경우 오늘의 입금확인 주문접수 주문을 검색조건으로 합니다.
				$_PARAM['regist_date'][0] = date('Y-m-d');
				$_PARAM['regist_date'][1] = date('Y-m-d');
				$_PARAM['chk_step'][15] = 1;
				$_PARAM['chk_step'][25] = 1;
			}
		}

		unset($_PARAM['chk_step']);
		$_PARAM['chk_step'][$_PARAM['isstep']]	= 1;
		$_PARAM['nolimit']						= 'y';

		$this->load->model('ordermodel');
		$query		= $this->ordermodel->get_order_catalog_query($_PARAM);
		foreach ($query->result_array() as $row){
			$order_list	.= $row['order_seq'] . '|';
		}

		$this->create_excel_list($_PARAM['seq'], $order_list);
	}

	function sort_shipping_seq($a,$b){
		if ((int)$a['shipping_seq'] == (int)$b['shipping_seq']) {
		   return 0;
		}
		return ((int)$a['shipping_seq'] < (int)$b['shipping_seq']) ? -1 : 1;
	}

	public function create_excel_list($seq, $order_seq){
		$this->load->library('pxl');
		$this->arr_payment = config_load('payment');

		$datas = get_data("fm_exceldownload",array("seq"=>$seq));
		$title_items = explode("|",$datas[0]['item']);
		$this->requiredsck($title_items, $datas[0]['criteria']);//필수항목체크

		// 개인정보 조회 모델 로드
		$this->load->model('logPersonalInformation');

		$order_arr = explode("|",$order_seq);
		for($i=0;$i<count($order_arr)-1;$i++){
			if(!$order_arr[$i]) continue;

			// 개인정보 조회 로그
			//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderprint' 'orderexcel', 'exportexcel'
			$this->logPersonalInformation->insert('orderexcel',$this->managerInfo['manager_seq'],$order_arr[$i]);

			if($datas[0]['criteria']=='ORDER'){//주문별 엑셀양식

				$sql = "SELECT
						A.*,
						B.*
					FROM fm_order A
					INNER JOIN fm_order_shipping B ON A.order_seq = B.order_seq
					WHERE A.order_seq = '{$order_arr[$i]}'";

				$query = $this->db->query($sql);

				foreach ($query->result_array() as $row){
					if($row['member_seq']){
						$member = get_data("fm_member",array("member_seq"=>$row['member_seq']));
						if($member) $row['userid'] = $member[0]['userid'];
					}
					$row['payment']	= $this->arr_payment[$row['payment']];
					if($row['pg'] == 'kakaopay'){ $row['payment'] = '카카오페이'; }

					$row['shipping_coupon_sale'] = $row['coupon_sale'];

					$items = $this->get_item($row['order_seq'],$row['shipping_seq']);
					$row['ea']				= $items['ea'];
					$row['consumer_price']	= $items['consumer_price'];
					$row['supply_price']	= $items['supply_price'];
					$row['price']			= $items['price'];

					//promotion sale
					$row['goods_coupon_sale'] = $items['coupon_sale'];
					$row['member_sale'] = $items['member_sale'];
					$row['fblike_sale'] = $items['fblike_sale'];
					$row['mobile_sale'] = $items['mobile_sale'];
					$row['promotion_code_sale'] = $items['promotion_code_sale'];

					$row['reserve']			= $items['reserve'];
					$row['point']				= $items['point'];

					$row['goods_shipping_cost']				= $items['goods_shipping_cost'];

					//여러개인경우 콤마구분 (명칭, 명칭, )
					$row['goods_name']	= substr($items['goods_name'],0,-2);
					$row['purchase_goods_name']	= substr($items['purchase_goods_name'],0,-2);
					$row['goods_code']		= substr($items['goods_code'],0,-2);
					$row['goods_seq']		= substr($items['goods_seq'],0,-2);

					$export = $this->get_export_item($row['order_seq'],$row['shipping_seq']);
					$row['export_ea']			= (int) $export['export_ea'];
					if( $row['international'] == 'international' ){//해외배송
						$row['shipping_method'] = $export['international_shipping_method'];
						$row['delivery_company_code'] = '';
						$row['delivery_number'] = $export['international_delivery_no'];

						$row['recipient_zipcode'] = $row['international_postcode'];
						$row['recipient_address'] = $row['international_country'].' '.$row['region'].' '.$row['international_town_city'].' '.$row['international_county'];
						$row['recipient_address_detail'] = $row['international_address'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
						$row['recipient_address_street_all'] = $row['recipient_address_street']." ".$row['recipient_address_detail']; //전체주소
					}else{
						$row['delivery_company_code']	= $export['delivery_company_code'];
						$row['delivery_number']				= $export['delivery_number'];
						//$row['shipping_method']				= $export['domestic_shipping_method'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
						$row['recipient_address_street_all'] = $row['recipient_address_street']." ".$row['recipient_address_detail']; //전체주소
					}

					$row['export_date']			= $export['export_date'];//출고일
					$row['complete_date']		= $export['complete_date'];//출고완료일
					$row['shipping_date']		= $export['shipping_date'];//배송완료일

					$refund = $this->get_refund_item($row['order_seq'],$row['shipping_seq']);
					$row['refund_ea']			= $refund['refund_ea'];

					$row['request_ea'] = ($row['ea'] == $row['export_ea'])?0:$row['ea'] - $row['export_ea'] - $row['refund_ea'];

					$data[] = $row;
				}//endforeach
			}else{//상품별양식

				$sql = "SELECT
					C.*, B.*, A.*, D.*,
					E.ea as ea,
					(select purchase_goods_name from fm_goods where goods_seq = B.goods_seq) as purchase_goods_name,
					C.goods_code as optgoods_code
					FROM
						fm_order_item_option C
						LEFT JOIN fm_order_item B ON C.item_seq = B.item_seq
						LEFT JOIN fm_order A ON B.order_seq = A.order_seq
						LEFT JOIN fm_order_shipping_item_option E ON (C.item_seq = E.order_item_seq and C.item_option_seq = E.order_item_option_seq)
						LEFT JOIN fm_order_shipping D ON E.shipping_seq = D.shipping_seq
					WHERE
						A.order_seq = '{$order_arr[$i]}'
					order by A.order_seq, D.shipping_seq, E.shipping_item_seq, C.item_option_seq";
				$query = $this->db->query($sql);

				foreach ($query->result_array() as $row){
					if($row['member_seq']){
						$member = get_data("fm_member",array("member_seq"=>$row['member_seq']));
						if($member) $row['userid'] = $member[0]['userid'];
					}
					$row['goods_code']		=  $row['optgoods_code'];

					$items = $this->get_item_option($row['item_option_seq']);
					$row['member_sale']		= $items['member_sale']*$items['ea'];
					$row['consumer_price'] = $items['consumer_price']*$items['ea'];
					$row['goods_coupon_sale'] = $items['coupon_sale'];
					$row['reserve'] = $items['reserve']*$items['ea'];
					$row['point'] = $items['point']*$items['ea'];

					$export = $this->get_export_items($row['order_seq'], $row['shipping_seq'], $row['item_seq'], $row['item_option_seq'],'option_seq');

					$row['export_ea']			= (int) $export['export_ea'];
					if( $row['international'] == 'international' ){//해외배송인경우
						$row['shipping_method'] = $export['international_shipping_method'];
						$row['delivery_company_code'] = '';
						$row['delivery_number'] = $export['international_delivery_no'];

						$row['recipient_zipcode'] = $row['international_postcode'];
						$row['recipient_address'] = $row['international_country'].' '.$row['region'].' '.$row['international_town_city'].' '.$row['international_county'];
						$row['recipient_address_detail'] = $row['international_address'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소

					}else{
						$row['delivery_company_code']	= $export['delivery_company_code'];
						$row['delivery_number']				= $export['delivery_number'];
						//$row['shipping_method']				= $export['domestic_shipping_method'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
					}

					$row['export_date']			= $export['export_date'];//출고일
					$row['complete_date']		= $export['complete_date'];//출고완료일
					$row['shipping_date']		= $export['shipping_date'];//배송완료일

					$refund = $this->get_refund_items($row['order_seq'], $row['shipping_seq'], $row['item_seq']);
					$row['refund_ea']			= $refund['refund_ea'];

					$row['request_ea'] = ($row['ea'] == $row['export_ea'])?0:$row['ea'] - $row['export_ea'] - $row['refund_ea'];

					$row['ea_price'] = $row['ea'] * $row['price'];
					$row['payment']	= $this->arr_payment[$row['payment']];
					if($row['pg'] == 'kakaopay'){ $row['payment'] = '카카오페이'; }

					//추가입력옵션
					$sql = "SELECT
						*
						FROM
							fm_order_item_input C
							LEFT JOIN fm_order_item B ON C.item_seq = B.item_seq
							LEFT JOIN fm_order A ON B.order_seq = A.order_seq
						WHERE
							A.order_seq = '{$order_arr[$i]}' AND C.item_seq = '{$row['item_seq']}'  AND C.item_option_seq = '{$row['item_option_seq']}' ";
					if( defined('__SELLERADMIN__') === true ){
						//$sql .= " and B.provider_seq='{$this->providerInfo['provider_seq']}'  ";
					}
					$query = $this->db->query($sql.' order by C.item_input_seq ');
					unset($inputoption);
					foreach ($query->result_array() as $rowinput){
						$inputoption .= ($inputoption)?', '.$rowinput['title'].':'.$rowinput['value']:$rowinput['title'].':'.$rowinput['value'];
					}//endforeach
					if($inputoption) $row['subinputoption']		= $inputoption;

					$data[] = $row;
				}//endforeach

				$sql = "SELECT
					C.*, B.*, A.*,					
					(select purchase_goods_name from fm_goods where goods_seq = B.goods_seq) as purchase_goods_name,
					D.shipping_seq,
					D.recipient_user_name,
					D.recipient_phone,
					D.recipient_cellphone,
					D.recipient_zipcode,
					D.recipient_address,
					D.recipient_address_detail,
					D.memo,
					E.ea as ea,
					C.goods_code as subgoods_code
					FROM
						fm_order_item_suboption C
						LEFT JOIN fm_order_item B ON C.item_seq = B.item_seq
						LEFT JOIN fm_order A ON B.order_seq = A.order_seq
						LEFT JOIN fm_order_shipping_item_option E ON (C.item_seq = E.order_item_seq and C.item_suboption_seq = E.order_item_suboption_seq)
						LEFT JOIN fm_order_shipping D ON E.shipping_seq = D.shipping_seq
					WHERE
						A.order_seq = '{$order_arr[$i]}'
					order by A.order_seq, D.shipping_seq, E.shipping_item_seq, C.item_suboption_seq";
				$query = $this->db->query($sql);

				foreach ($query->result_array() as $row){
					if($row['member_seq']){
						$member = get_data("fm_member",array("member_seq"=>$row['member_seq']));
						if($member) $row['userid'] = $member[0]['userid'];
					}
					$row['goods_code']			= $row['subgoods_code'];

					$items = $this->get_sub_option($row['item_suboption_seq']);
					$row['member_sale']		= $items['member_sale']*$items['ea'];
					$row['consumer_price'] = $items['consumer_price']*$items['ea'];
					$row['reserve'] = $items['reserve']*$items['ea'];
					$row['point'] = $items['point']*$items['ea'];

					$row['goods_coupon_sale'] = $items['coupon_sale'];
					$row['subtitle'] = $items['title'];//$row[]	= $items;

					$export = $this->get_export_items($row['order_seq'], $row['shipping_seq'], $row['item_seq'], $row['item_suboption_seq'],'suboption_seq');

					$row['export_ea']			= (int) $export['export_ea'];
					if( $row['international'] == 'international' ){//해외인경우
						$row['shipping_method'] = $export['international_shipping_method'];
						$row['delivery_company_code'] = '';
						$row['delivery_number'] = $export['international_delivery_no'];

						$row['recipient_zipcode'] = $row['international_postcode'];
						$row['recipient_address'] = $row['international_country'].' '.$row['region'].' '.$row['international_town_city'].' '.$row['international_county'];
						$row['recipient_address_detail'] = $row['international_address'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소

					}else{
						$row['delivery_company_code']	= $export['delivery_company_code'];
						$row['delivery_number']				= $export['delivery_number'];
						//$row['shipping_method']				= $export['domestic_shipping_method'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
					}
					$row['export_date']			= $export['export_date'];//출고일
					$row['complete_date']		= $export['complete_date'];//출고완료일
					$row['shipping_date']		= $export['shipping_date'];//배송완료일

					$refund = $this->get_refund_items($row['order_seq'], $row['shipping_seq'], $row['item_seq']);
					$row['refund_ea']			= $refund['refund_ea'];

					$row['request_ea'] = ($row['ea'] == $row['export_ea'])?0:$row['ea'] - $row['export_ea'] - $row['refund_ea'];

					$row['ea_price'] = $row['ea'] * $row['price'];
					$row['payment']	= $this->arr_payment[$row['payment']];
					if($row['pg'] == 'kakaopay'){ $row['payment'] = '카카오페이'; }

					$data[] = $row;
				}//endforeach


			}//엑셀양식구분

		}//endfor

		usort($data,"sort_shipping_seq");

		$this->excel_write($data, $title_items, $datas[0]['criteria']);
	}

	public function excel_write($data, $title_items, $criteria) {
		$this->load->library('pxl');
		$filenames = ($criteria!='ORDER')?"order_goods_down_".date("YmdHis").".xls":"order_down_".date("YmdHis").".xls";

		$item_arr = $this->itemList;
		$fields = array();
		$item = array();
		foreach($title_items as $k){
			if( $k == 'option' && $criteria!='ORDER'){
				$item = array_merge($item, $this->temp_arr);
				$fields = array_merge($fields, $this->temp);
			}else{
				if($k == 'option' || !$item_arr[$k]) continue;
				if( $criteria=='ORDER' && ($k == 'refund_ea' || $k == 'export_ea' || $k == 'request_ea' || $k == 'ea')) {
					$item_arr[$k] = str_replace("*","",$item_arr[$k]);
				}

				$item[] = $k;
				$fields[$k] = $item_arr[$k];
			}
		}
		$cell_arr = $this->excel_cell(count($item));
		$cnt = count($fields);
		$t=2;
		$temp1 = array_search('title1',$item);
		$temp2 = array_search('title2',$item);
		$temp3 = array_search('title3',$item);
		$temp4 = array_search('title4',$item);
		$temp5 = array_search('title5',$item);

		$temp6 = array_search('option1',$item);
		$temp7 = array_search('option2',$item);
		$temp8 = array_search('option3',$item);
		$temp9 = array_search('option4',$item);
		$temp10 = array_search('option5',$item);
		$temp11 = array_search('subtitle',$item);
		$temp12 = array_search('suboption',$item);

		$temp13 = array_search('subinputoption',$item);

		foreach ($data as $k)
		{
			$items = array();
			for($i=0;$i<$cnt;$i++){
				$tmpname = $item[$i];
				$tmpvalue = $k[$tmpname];
				switch($tmpname){
					case 'option_title':
						$tmp_arr = explode(",",$tmpvalue);
						$items[$t][$temp1] = $tmp_arr[0];
						$items[$t][$temp2] = $tmp_arr[1];
						$items[$t][$temp3] = $tmp_arr[2];
						$items[$t][$temp4] = $tmp_arr[3];
						$items[$t][$temp5] = $tmp_arr[4];
					break;
					case 'option1':
						$items[$t][$temp6] = $tmpvalue;
					break;
					case 'option2':
						$items[$t][$temp7] = $tmpvalue;
					break;
					case 'option3':
						$items[$t][$temp8] = $tmpvalue;
					break;
					case 'option4':
						$items[$t][$temp9] = $tmpvalue;
					break;
					case 'option5':
						$items[$t][$temp10] = $tmpvalue;
					break;
					case 'suboption_title':
						$items[$t][$temp11] = $tmpvalue;
					break;
					case 'suboption':
						$items[$t][$temp12] = $tmpvalue;
					break;
					case 'subinputoption':
						$items[$t][$temp13] = $tmpvalue;
					break;
					default:
						$items[$t][$i] = $tmpvalue;
					break;
				}//end switch
			}
			@ksort($items[$t]);
			$datas[] = $items;
			$t++;
		}
		$this->pxl->excel_download($datas, $fields, $filenames,'주문엑셀일괄다운로드');
		//$this->pxl->pxl_excel_down($datas, $fields, $filenames,'주문엑셀일괄다운로드','order');
	}

	public function excel_upload($realfilename, $step){
		$this->arr_step 	= config_load('step');

		$this->load->library('pxl');
		set_time_limit(0);
		ini_set('memory_limit', '3500M');
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '5120MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		$this->objPHPExcel = new PHPExcel();

		$resultExcelData = array();

		if(is_file($realfilename)){
			try {
				// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
				$objReader = IOFactory::createReaderForFile($realfilename);
				// 읽기전용으로 설정
				if( function_exists('$objReader->setReadDataOnly()') ) {
					$objReader->setReadDataOnly(true);
				}
				// 엑셀파일을 읽는다
				$objExcel = $objReader->load($realfilename);

				// 첫번째 시트를 선택
				$objExcel->setActiveSheetIndex(0);
				$objWorksheet = $objExcel->getActiveSheet();

				$maxRow = $objWorksheet->getHighestRow();
				$maxCol = $objWorksheet->getHighestColumn();
				if($nextnum && $nextnum <= $maxRow ){
					$maxRow = $nextnum;
				}
				$colCount = $this->excel_num($maxCol) + 1;
				$cell_arr = $this->excel_cell($colCount);

				for($i=0; $i<$colCount; $i++){
					$tmp = $objWorksheet->getCell($cell_arr[$i] . "1")->getValue();
					switch($tmp){
						case "*배송지고유값":	$cell_shipping_seq	= $cell_arr[$i]; break;
						case "*주문번호":		$cell_order_seq		= $cell_arr[$i]; break;
						case "*국내/해외배송":	$cell_international	= $cell_arr[$i]; break;
						case "*배송구분":		$cell_shipping_method = $cell_arr[$i]; break;
						case "*택배사코드":		$cell_delivery_company_code = $cell_arr[$i]; break;
						case "*송장번호":		$cell_delivery_number = $cell_arr[$i]; break;

						case "*전체수량":		$cell_ea			= $cell_arr[$i]; break;
						case "*취소수량":		$cell_refund_ea		= $cell_arr[$i]; break;
						case "*출고완료수량":	$cell_export_ea		= $cell_arr[$i]; break;
						case "*출고할 수량":		$cell_request_ea	= $cell_arr[$i]; break;

						case "*옵션고유값":		$cell_item_seq		= $cell_arr[$i]; break;
						case "*옵션값1":		$cell_option1 = $cell_arr[$i]; break;
						case "*옵션값2":		$cell_option2 = $cell_arr[$i]; break;
						case "*옵션값3":		$cell_option3 = $cell_arr[$i]; break;
						case "*옵션값4":		$cell_option4 = $cell_arr[$i]; break;
						case "*옵션값5":		$cell_option5 = $cell_arr[$i]; break;
						case "*추가옵션명":		$cell_subtitle = $cell_arr[$i]; break;
						case "*추가옵션값":		$cell_suboption = $cell_arr[$i]; break;
					}
				}
				###
				$row_key = 0;
				$chk_cnt = 0;
				for ($i = 2 ; $i <= $maxRow ; $i++) {
					$order_seq		= $objWorksheet->getCell($cell_order_seq.$i)->getValue();
					$shipping_seq	= $objWorksheet->getCell($cell_shipping_seq.$i)->getValue();

					$international	= $objWorksheet->getCell($cell_international.$i)->getValue();
					$shipping_method = $objWorksheet->getCell($cell_shipping_method.$i)->getValue();

					$delivery_company_code = $objWorksheet->getCell($cell_delivery_company_code.$i)->getValue();
					$delivery_number = $objWorksheet->getCell($cell_delivery_number.$i)->getValue();

					$ea					= ($cell_ea) ? $objWorksheet->getCell($cell_ea.$i)->getValue():0;
					$refund_ea		= ($cell_refund_ea)?$objWorksheet->getCell($cell_refund_ea.$i)->getValue():0;
					$export_ea		= ($cell_export_ea)?$objWorksheet->getCell($cell_export_ea.$i)->getValue():0;
					$request_ea	= ($cell_request_ea)?$objWorksheet->getCell($cell_request_ea.$i)->getValue():0;

					if($cell_item_seq) $item_seq	= $objWorksheet->getCell($cell_item_seq.$i)->getValue();
					if($cell_option1) $option1		= $objWorksheet->getCell($cell_option1.$i)->getValue();
					if($cell_option2) $option2		= $objWorksheet->getCell($cell_option2.$i)->getValue();
					if($cell_option3) $option3		= $objWorksheet->getCell($cell_option3.$i)->getValue();
					if($cell_option4) $option4		= $objWorksheet->getCell($cell_option4.$i)->getValue();
					if($cell_option5) $option5		= $objWorksheet->getCell($cell_option5.$i)->getValue();

					if($cell_subtitle) $subtitle		= $objWorksheet->getCell($cell_subtitle.$i)->getValue();
					if($cell_suboption) $suboption		= $objWorksheet->getCell($cell_suboption.$i)->getValue();

					$resultExcelData[$row_key][0] = $order_seq;

					if($order_seq && $international=='domestic' && ( $shipping_method=='delivery' || $shipping_method=='postpaid' ) &&  $delivery_company_code && ($delivery_number || preg_match("/^auto_/i",$delivery_company_code))){
						if($item_seq && ( ($ea - $refund_ea - $export_ea) >= $request_ea) ) {### ITEM
							$item_option_seq = null;
							$item_suboption_seq = null;
							if($subtitle && $suboption){
								$opt = get_data("fm_order_item_suboption",array("item_seq"=>trim($item_seq), "title"=>trim($subtitle), "suboption"=>trim($suboption), "step <"=>$step));
								if($opt)$item_suboption_seq = $opt[0]['item_suboption_seq'];
							}else{

								unset($r_where);
								$r_where = array("item_seq"=>trim($item_seq),"step <"=>$step);
								if($option1) $r_where['option1'] = trim($option1);
								if($option2) $r_where['option2'] = trim($option2);
								if($option3) $r_where['option3'] = trim($option3);
								if($option4) $r_where['option4'] = trim($option4);
								if($option5) $r_where['option5'] = trim($option5);
								$opt = get_data("fm_order_item_option",$r_where);
								if($opt)$item_option_seq = $opt[0]['item_option_seq'];
							}
							if($opt && $opt[0]["step"] < $step) {
								if($item_suboption_seq || $item_option_seq){
									$result = $this->goods_export($step, $order_seq, $shipping_seq, $item_seq, $shipping_method, $delivery_company_code, $delivery_number, $request_ea, $item_option_seq, $item_suboption_seq);
									if( $result ){
										$chk_cnt++;
										$resultExcelData[$row_key][1] = $result;
									}else{
										$stock_err_order_seq[] = $order_seq;
									}
								}
							}
						}else{	### ORDER
							$result = $this->order_export($step, $order_seq, $shipping_seq, $shipping_method, $delivery_company_code, $delivery_number);
							if( $result ){
								$chk_cnt++;
								$resultExcelData[$row_key][1] = $result;
							}else{
								$stock_err_order_seq[] = $order_seq;
							}
						}
					}

					$row_key++;
				}
				if($chk_cnt>0){
					if($this->order_cellphones){
						$commonSmsData['released']['phone'] = $this->order_cellphones;
						$commonSmsData['released']['params'] = $this->arr_params;
						$commonSmsData['released']['order_no'] = $this->order_no;
					}
					
					if($this->recipient_cellphones){
						$commonSmsData['released2']['phone'] = $this->recipient_cellphones;
						$commonSmsData['released2']['params'] = $this->recipient_arr_params;
						$commonSmsData['released2']['order_no'] = $this->recipient_order_no;

					}

					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}
					
					$data['result']	= true;
					$data['count']	= $chk_cnt;
					$data['msg']	= $chk_cnt.'건 수정 되었습니다.';
					if( $stock_err_order_seq ) {
						$data['msg'] .= "<br/>단, 재고가 부족하거나 출고수량이 초과한  상품이 있는 주문은 ".$this->arr_step[$step]." 처리되지 않았습니다.";
						$stock_err_order_seq = array_unique($stock_err_order_seq);
						$data['msg'] .= '<br/>주문번호 : ' . implode('<br/>주문번호 : ', $stock_err_order_seq);
						$height = count($stock_err_order_seq) * 15;
						$data['height']  = $height;
					}

					$data['result_excel_url']	= $this->excel_upload_result($resultExcelData);
				}else{
					$data['result']	= false;
					$data['count']	= 0;
					$data['msg']	= '수정 가능한 데이터가 없습니다.';
				}
			} catch (exception $e) {
				$data['result']	= false;
				$data['count']	= 0;
				$data['msg']	= '엑셀파일을 읽는도중 오류가 발생하였습니다.<br/><span style="color:red;">필독! 엑셀파일 저장시 확장자가 XLS 인 엑셀 97~2003 양식으로 저장해 주세요.';
			}
		}else{
			$data['result']	= false;
			$data['count']	= 0;
			$data['msg']	= '엑셀파일이 없습니다.';
		}

		return $data;
	}


	public function order_export($step, $order_seq, $shipping_seq, $shipping_method, $delivery_company_code, $delivery_number){
		$this->load->model('ordermodel');
		$this->load->model('exportmodel');

		$order_chk = get_data("fm_order", array("order_seq"=>$order_seq));
		if($order_chk[0]['step']>=45) return false;
		if($order_chk[0]['step']<=15) return false;

		/**
			$shipping_items = $this->ordermodel->get_shipping_item($order_seq,$shipping_seq);
			if(!$cfg_order) $cfg_order = config_load('order');
			// 주문 상품이 모두 출고  체크 및 상품 재고 체크
			$tot_remind = 0;
			$err_stock = false;
			foreach($shipping_items as $item){
				$goods_seq = $item['goods_seq'];
				foreach($item['shipping_item_option'] as $data){
					$step_complete = $this->ordermodel -> get_option_export_complete(
						$order_seq,
						$shipping_seq,
						$data['item_seq'],
						$data['item_option_seq']
					);
					$step_remind = $data['ea'] - $step_complete - $data['step85'];
					$tot_remind += $step_remind;

					// 상품 재고 체크
					if($cfg_order['export_err_handling'] == 'error' && $step == '55'){
						$option1 = $data['option1'];
						$option2 = $data['option2'];
						$option3 = $data['option3'];
						$option4 = $data['option4'];
						$option5 = $data['option5'];
						$goods_stock = (int) $this->goodsmodel->get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5);
						if($goods_stock < $data['ea']){
							$err_stock = true;
						}
					}

					foreach($data['shipping_item_suboption'] as $data_sub){
						$step_complete = $this->ordermodel -> get_suboption_export_complete(
							$order_seq,
							$shipping_seq,
							$data_sub['item_seq'],
							$data_sub['item_suboption_seq']
						);
						$step_remind = $data_sub['ea'] - $step_complete - $data_sub['step85'];
						$tot_remind += $step_remind;

						// 상품 재고 체크
						if($cfg_order['export_err_handling'] == 'error' && $step == '55'){
							$title = $data_sub['title'];
							$suboption = $data_sub['suboption'];
							$goods_stock = (int) $this->goodsmodel->get_goods_suboption_stock($goods_seq,$title,$suboption);
							if($goods_stock < $data_sub['ea']){
								$err_stock = true;
							}
						}
					}
				}
			}
			if( $tot_remind == 0 ) return false;//출고수량이 주문수량을 초과하였습니다.
			if( $err_stock ) return false;//‘출고수량’ 보다 ‘재고수량’이 부족합니다
		**/

		unset($data);

		$data['status']						= $step;
		$data['order_seq']					= $order_seq;
		$data['shipping_seq']				= $shipping_seq;

		$data['domestic_shipping_method']	= $shipping_method;
		$data['delivery_company_code']		= $delivery_company_code;
		$data['delivery_number']			= $delivery_number;

		$data['export_date']				= date('Y-m-d H:i:s');
		$data['status'] 					= $step;

		if($step == '55'){//출고완료인경우
			$data['complete_date']	= date('Y-m-d H:i:s');
		}

		$data['regist_date']				= date('Y-m-d H:i:s');

		$export_code = $this->exportmodel->insert_export($data);

		$r_reservation_goods_seq = array();

		$items = $this->ordermodel->get_shipping_item($order_seq,$shipping_seq);
		foreach($items as $ik => $item){
			$options 	= $this->ordermodel->get_option_for_item($item['item_seq']);

			if($options)foreach($options as $k => $data){
				$step_complete = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
				$step_remind = $data['ea'] - $step_complete - $data['step85'];
				unset($insert_param);
				$insert_param['item_seq'] 			= $item['item_seq'];
				$insert_param['export_code'] 		= $export_code;
				$insert_param['option_seq'] 		= $data['item_option_seq'];
				$insert_param['ea'] 					= $step_remind;

				$this->db->insert('fm_goods_export_item', $insert_param);

				// 주문상태별 수량 변경
				$this->ordermodel->set_step_ea($step,$step_remind,$data['item_option_seq'],'option');

				// 주문 option 상태 변경
				$this->ordermodel->set_option_step($data['item_option_seq'],'option');
			}

			$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq']);
			if($suboptions)foreach($suboptions as $k => $data){
				$step_complete = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
				$step_remind = $data['ea'] - $step_complete - $data['step85'];

				unset($insert_param);
				$insert_param['item_seq'] 			= $item['item_seq'];
				$insert_param['export_code'] 		= $export_code;
				$insert_param['suboption_seq']	= $data['item_suboption_seq'];
				$insert_param['ea'] 					= $step_remind;
				$this->db->insert('fm_goods_export_item', $insert_param);

				// 주문상태별 수량 변경
				$this->ordermodel->set_step_ea($step,$step_remind,$data['item_suboption_seq'],'suboption');

				// 주문 option 상태 변경
				$this->ordermodel->set_option_step($data['item_suboption_seq'],'suboption');
			}

			// 출고완료시 재고 처리
			if( $step == '55' ){
				if($options)foreach($options as $k => $data){
					$this->goodsmodel->stock_option(
							'-',
							$data['ea'],
							$item['goods_seq'],
							$data['option1'],
							$data['option2'],
							$data['option3'],
							$data['option4'],
							$data['option5']
					);

					$this->goodsmodel->modify_reservation_option(
							$data['ea'],$item['goods_seq'],
							$data['option1'],$data['option2'],$data['option3'],$data['option4'],$data['option5'],
							15,'minus'
					);
					$this->goodsmodel->modify_reservation_option(
							$data['ea'],$item['goods_seq'],
							$data['option1'],$data['option2'],$data['option3'],$data['option4'],$data['option5'],
							25,'minus'
					);
				}
				if($suboptions)foreach($suboptions as $k => $data){
					$this->goodsmodel->stock_suboption(
							'-',
							$data['ea'],
							$data['goods_seq'],
							$data['title'],
							$data['option']
					);

					$this->goodsmodel->modify_reservation_suboption(
							$data['ea'],$item['goods_seq'],
							$data['title'],$data['suboption'],
							15,'minus'
					);
					$this->goodsmodel->modify_reservation_suboption(
							$data['ea'],$item['goods_seq'],
							$data['title'],$data['suboption'],
							25,'minus'
					);
				}

				// 출고량 업데이트를 위한 변수정의
				if( !in_array($item['goods_seq'],$r_reservation_goods_seq) ){
					$r_reservation_goods_seq[] = $item['goods_seq'];
				}
			}
		}

		// 출고예약량 업데이트
		foreach($r_reservation_goods_seq as $goods_seq){
			$this->goodsmodel->modify_reservation_real($goods_seq);
		}

		// 주문상태 변경
		$this->ordermodel->set_order_step($order_seq);

		$log_str = "관리자가 출고처리를 하였습니다.";
        $this->ordermodel->set_log($order_seq,'process','ORDER EXCEL : '.$this->managerInfo['mname'],'출고처리',$log_str);

		/* 출고자동화 전송 */
		$this->load->model('invoiceapimodel');
		$this->invoiceapimodel->export($export_code);
		if($invoiceExportResult['resultDeliveryNumber']){
			$delivery_number = $invoiceExportResult['resultDeliveryNumber'][0];
		}

		// 출고완료 메일링
		if( $step == '55' ){
			send_mail_step55($export_code);

			// 출고완료시 sms
			$orders	= $this->ordermodel->get_order($order_seq);
			if( $orders['order_cellphone'] ){

				$params['shopName']		= $this->config_basic['shopName'];
				$params['ordno']		= $order_seq;
				$params['export_code']	= $export_code;
				$params['user_name']	= $orders['order_user_name'];
				$params['member_seq']	= $orders['member_seq'];
				$params['delivery_number']	= $delivery_number;
				$this->arr_params[$this->order_count] = $params;
				$this->order_no[$this->order_count] = $order_seq;
				$this->order_cellphones[$this->order_count]		= $orders['order_cellphone'];

				# 주문자와 받는분이 다를때 받는분에게도 문자 전송
				if( $orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))){
					$this->recipient_cellphones[$this->recipient_count]	= $orders['recipient_cellphone'];	//받는분
					$this->recipient_arr_params[$this->recipient_count] = $params;
					$this->recipient_order_no[$this->recipient_count] = $order_seq;
					$this->recipient_count = $this->recipient_count+1;	
					
				}
				$this->order_count	= $this->order_count+1;	
			}
		}

		return $export_code;
	}



	public function goods_export($step, $order_seq, $shipping_seq, $item_seq, $shipping_method, $delivery_company_code, $delivery_number, $request_ea, $item_option_seq=null, $item_suboption_seq=null){
		$this->load->model('ordermodel');
		$this->load->model('exportmodel');

		$r_reservation_goods_seq = array();

		if(!$request_ea){
			return false;
		}

		if($item_option_seq){
			$opt = get_data("fm_order_item_option", array("item_option_seq"=>trim($item_option_seq)) );
			if($opt[0]['step']>=45) return false;//출고준비이하만가능
			if($opt[0]['step']<=15) return false;
		}

		if($item_suboption_seq){
			$subopt = get_data("fm_order_item_suboption", array("item_suboption_seq"=>trim($item_suboption_seq)) );
			if($subopt[0]['step']>=45) return false;//출고준비이하만가능
			if($subopt[0]['step']<=15) return false;
		}

		unset($data);

		$data['status']						= $step;
		$data['order_seq']					= $order_seq;
		$data['shipping_seq']				= $shipping_seq;
		$data['international']				= 'domestic';

		$data['domestic_shipping_method']	= $shipping_method;
		$data['delivery_company_code'] 		= $delivery_company_code;
		$data['delivery_number']			= $delivery_number;

		$data['export_date']				= date('Y-m-d H:i:s');
		$data['status'] 					= $step;

		if($step == '55'){//출고완료인경우
			$data['complete_date']	= date('Y-m-d H:i:s');
		}

		$data['regist_date']				= date('Y-m-d H:i:s');
		$export_code = $this->exportmodel->insert_export($data);

		unset($data);
		if($item_option_seq){
			$data['item_seq'] 		= $item_seq;
			$data['export_code'] 	= $export_code;
			$data['option_seq'] 	= $item_option_seq;
			$data['ea'] 			= $request_ea;
			if( $request_ea > 0 ){
				$this->db->insert('fm_goods_export_item', $data);
			}

			// 주문상태별 수량 변경
			$this->ordermodel->set_step_ea($step,$request_ea,$item_option_seq,'option');

			// 주문 option 상태 변경
			$this->ordermodel->set_option_step($item_option_seq,'option');
		}

		unset($data);
		if($item_suboption_seq){
			$data['item_seq'] 		= $item_seq;
			$data['export_code'] 	= $export_code;
			$data['suboption_seq'] 	= $item_suboption_seq;
			$data['ea'] 			= $request_ea;
			if( $request_ea > 0 ) $this->db->insert('fm_goods_export_item', $data);

			// 주문상태별 수량 변경
			$this->ordermodel->set_step_ea($step,$request_ea,$item_suboption_seq,'suboption');

			// 주문 option 상태 변경
			$this->ordermodel->set_option_step($item_suboption_seq,'suboption');
		}

		// 주문상태 변경
		$this->ordermodel->set_order_step($order_seq);

		// 출고 완료 시 재고 차감
		if($step == '55'){
			$export_item = $this->exportmodel->get_export_item($export_code);
			$this->load->model('goodsmodel');


			foreach($export_item as $item){
				if($item['opt_type'] == 'opt'){
					$this->goodsmodel->stock_option(
						'-',
						$item['ea'],
						$item['goods_seq'],
						$item['option1'],
						$item['option2'],
						$item['option3'],
						$item['option4'],
						$item['option5']
					);

					// 출고예약량 업데이트
					$reservation = $item['ea'];
					$goods_seq = $item['goods_seq'];
					$option1 = $item['option1'];
					$option2 = $item['option2'];
					$option3 = $item['option3'];
					$option4 = $item['option4'];
					$option5 = $item['option5'];
					$this->goodsmodel->modify_reservation_option($reservation,$goods_seq,$option1,$option2,$option3,$option4,$option5,15,'minus');
					$this->goodsmodel->modify_reservation_option($reservation,$goods_seq,$option1,$option2,$option3,$option4,$option5,25,'minus');

				}else{
					$this->goodsmodel->stock_suboption(
						'-',
						$item['ea'],
						$item['goods_seq'],
						$item['title1'],
						$item['option1']
					);

					// 출고예약량 업데이트
					$reservation = $item['ea'];
					$goods_seq = $item['goods_seq'];
					$title = $item['title1'];
					$option = $item['option1'];
					$this->goodsmodel->modify_reservation_suboption($reservation,$goods_seq,$title,$option,15,'minus');
					$this->goodsmodel->modify_reservation_suboption($reservation,$goods_seq,$title,$option,25,'minus');

				}

				// 출고량 업데이트를 위한 변수정의
				if( !in_array($item['goods_seq'],$r_reservation_goods_seq) ){
					$r_reservation_goods_seq[] = $item['goods_seq'];
				}
			}

			// 출고예약량 업데이트
			foreach($r_reservation_goods_seq as $goods_seq){
				$this->goodsmodel->modify_reservation_real($goods_seq);
			}

			/* 출고자동화 전송 */
			$this->load->model('invoiceapimodel');
			$this->invoiceapimodel->export($export_code);
			if($invoiceExportResult['resultDeliveryNumber']){
				$delivery_number = $invoiceExportResult['resultDeliveryNumber'][0];
			}

			# 오픈마켓 송장등록 #
			$this->load->model('openmarketmodel');
			$this->openmarketmodel->request_send_export($export_code);

			// 출고완료 메일링
        	send_mail_step55($export_code);

			// 출고완료시 sms
			$orders	= $this->ordermodel->get_order($order_seq);
			if( $orders['order_cellphone'] ){

				$params['shopName']		= $this->config_basic['shopName'];
				$params['ordno']		= $order_seq;
				$params['export_code']	= $export_code;
				$params['user_name']	= $orders['order_user_name'];
				$params['member_seq']	= $orders['member_seq'];
				$params['delivery_number']	= $delivery_number;
				$this->arr_params[$this->order_count] = $params;
				$this->order_no[$this->order_count] = $order_seq;
				$this->order_cellphones[$this->order_count]		= $orders['order_cellphone'];

				# 주문자와 받는분이 다를때 받는분에게도 문자 전송
				if( $orders['recipient_cellphone'] && (ereg_replace("[^0-9]", "", $orders['order_cellphone']) !=  ereg_replace("[^0-9]", "", $orders['recipient_cellphone']))){
					$this->recipient_cellphones[$this->recipient_count]	= $orders['recipient_cellphone'];	//받는분
					$this->recipient_arr_params[$this->recipient_count] = $params;
					$this->recipient_order_no[$this->recipient_count] = $order_seq;
					$this->recipient_count = $this->recipient_count+1;	
					
				}
				$this->order_count	= $this->order_count+1;	

			}
		}

		$log_str = "관리자가 출고처리를 하였습니다.";
        $this->ordermodel->set_log($order_seq,'process','GOODS EXCEL : '.$this->managerInfo['mname'],'출고처리',$log_str);

		return $export_code;
	}

	public function excel_upload_result($resultExcelData){
		$this->load->library('pxl');
		$filenames = "order_excel_upload_result_".date("YmdHis").".xls";
		$t = 2;
		$datas = array();
		foreach($resultExcelData as $row){
			$items = array();
			$items[$t] = $row;
			$datas[] = $items;
		}

		$fields['order_seq'] = "주문번호";
		$fields['export_code'] = "출고코드";

		$fileurl = '/data/tmp/'.$filenames;
		$filepath = ROOTPATH.'data/tmp/'.$filenames;
		$result = $this->pxl->excel_download($datas, $fields, $filenames,'엑셀 일괄 업로드 처리결과',false);
		file_put_contents($filepath,$result);
		return $fileurl;
	}

}

/* End of file excelmodel.php */
/* Location: ./app/models/excelmodel */