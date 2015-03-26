<?php
class Excelexportmodel extends CI_Model {
	var $downloadType		= "Excel5";
	var $saveurl			= "/data/tmp";
	var $cell = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

	var $itemList = array(
		"export_code"						=> "*출고번호",
		"complete_date"					=> "*출고완료일",
		"delivery_company_code"	=> "*택배사코드",
		"delivery_number"				=> "*송장번호",

		"status"								=> "출고상태",
		"order_seq"							=> "주문번호",
		"order_user_name"				=> "주문자명",
		"userid"								=> "주문자아이디",
		"order_phone"						=> "주문자연락처",
		"order_cellphone"				=> "주문자휴대폰",
		"order_email"						=> "주문자이메일",

		"recipient_user_name"		=> "수령인",
		"recipient_cellphone"			=> "수령인휴대폰",
		"recipient_phone"				=> "수령인연락처",
		"recipient_zipcode"				=> "우편번호",
		"recipient_address"				=> "주소(지번)",
		"recipient_address_street"				=> "주소(도로명)",
		"recipient_address_detail"	=> "상세주소",
		"recipient_address_all"		=> "전체주소(지번)",
		"recipient_address_street_all"		=> "전체주소(도로명)",

		"goods_code"						=> "주문상품코드",
		"goods_seq"						=> "상품고유값",
		"goods_name"						=> "상품명",
		"purchase_goods_name"			=> "매입용 상품명",
		"option"								=> "옵션",
		"supply_price"						=> "매입가",
		"consumer_price"					=> "정가",
		"price"									=> "할인가",
		"ea_price"							=> "할인가x출고수량",
		"tax"									=> "과세여부",

		"international"						=> "국내/해외배송",
		"shipping_method"				=> "배송구분",

		"ea"										=> "전체수량",
		"refund_ea"							=> "반품수량",
		"export_ea"							=> "출고수량",

		"order_regist_date"				=> "주문일",
		"shipping_date"					=> "배송완료일",
		"export_date"						=> "출고일",

		"memo"								=> "사용자메모",
		"admin_memo"					=> "관리자메모",
		"settleprice"							=> "결제금액",
		"deposit_date"					=> "결제일",
		"payment"							=> "결제방법"
	);

	var $requireds = array(
		"export_code",
		"complete_date",
		"delivery_company_code",
		"delivery_number"
	);

	var $temp = array(
		"optiontitle"			=> "필수옵션",
		"suboptiontitle"	=> "추가옵션",
		"subinputoption"	=> "추가입력옵션"
	);
	var $temp_arr = array(
		"optiontitle",
		"suboptiontitle",
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
	public function requiredsck($titleitems, $type='down'){
		for($i=0;$i<count($this->requireds);$i++) {
			if( in_array($this->requireds[$i], $titleitems ) )
				$requiredsnum++;//
		}
		if($requiredsnum != count($this->requireds)){
			if($type == "upload") {
				return false;
			}else{
				openDialogAlert('다운로드 양식의 필수항목이 빠져 있습니다.<br/>다운로드 양식을 다시한번 확인해 주세요.',600,140,'parent','');
				exit;
			}
		}
		return true;
	}

	public function get_item($export_code, $order_seq){
		$this->load->model('ordermodel');
		$query = "
			SELECT
				D.*,
				(select purchase_goods_name from fm_goods where goods_seq = D.goods_seq) as purchase_goods_name,
				C.option_seq,
				C.suboption_seq
			FROM
				fm_order_item D
				LEFT JOIN fm_goods_export_item as C ON C.item_seq = D.item_seq
			WHERE
				C.export_code=? AND D.order_seq=?";
		$query = $this->db->query($query,array($export_code, $order_seq));
		foreach($query->result_array() as $data){
			$items[] = $data;
		}
		foreach($items as $key=>$item){
			unset($totitem);
			if($item['option_seq']) {
				$optwhere = array("item_option_seq='".$item['option_seq']."' ");
				unset($options,$subinputoptions);
				$options 	= $this->ordermodel->get_option_for_item($item['item_seq'],$optwhere);
				$subinputoptions = $this->ordermodel->get_input_for_item($item['item_seq']);

				if($options) foreach($options as $k => $data) {
					$data['out_supply_price'] = $data['supply_price']*$data['ea'];
					$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
					$data['out_price'] = $data['price']*$data['ea'];

					//promotion sale
					$data['out_member_sale']				= $data['member_sale']*$data['ea'];
					$data['out_coupon_sale']				= ($data['download_seq'])?$data['coupon_sale']:0;
					$data['out_fblike_sale']					= $data['fblike_sale'];
					$data['out_mobile_sale']					= $data['mobile_sale'];
					$data['out_promotion_code_sale']	= $data['promotion_code_sale'];

					$data['out_reserve'] = $data['reserve']*$data['ea'];
					$data['out_point'] = $data['point']*$data['ea'];

					$data['step_complete'] = $data['step45']+$data['step55']+$data['step65']+$data['step75'];
					$options[$k] = $data;
					$tot['ea'] += $data['ea'];
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
						$totitem['option'] .= ($totitem['option'])?', '.$tot['title1'].':'.$tot['option1']:$tot['title1'].':'.$tot['option1'];
					}

					if($data['title2']) {
						$totitem['option'] .= ', '.$tot['title2'].':'.$tot['option2'];
					}

					if($data['title3']) {
						$totitem['option'] .= ', '.$tot['title3'].':'.$tot['option3'];
					}

					if($data['title4']) {
						$totitem['option'] .= ', '.$tot['title4'].':'.$tot['option4'];
					}

					if($data['title5']) {
						$totitem['option'] .= ', '.$tot['title5'].':'.$tot['option5'];
					}
				}


				if($subinputoptions) foreach($subinputoptions as $data){
					if($data['title']) {
						$totitem['inputoption'] .= ($totitem['inputoption'])?', '.$data['title'].':'.$data['value']:$data['title'].':'.$data['value'];
					}
				}

			}

			if($item['suboption_seq']) {
				$suboptwhere = array("item_suboption_seq='".$item['suboption_seq']."' ");
				unset($suboptions);
				$suboptions = $this->ordermodel->get_suboption_for_item($item['item_seq'],$suboptwhere);

				if($suboptions) foreach($suboptions as $data){
					$data['out_supply_price'] = $data['supply_price']*$data['ea'];
					$data['out_consumer_price'] = $data['consumer_price']*$data['ea'];
					$data['out_price'] = $data['price']*$data['ea'];
					$data['out_reserve'] = $data['reserve']*$data['ea'];
					$data['out_point'] = $data['point']*$data['ea'];

					$data['out_member_sale']				= $data['member_sale']*$data['ea'];

					$suboptions[$k] = $data;
					$tot['ea'] += $data['ea'];
					$tot['supply_price'] 	+= $data['out_supply_price'];
					$tot['consumer_price'] 	+= $data['out_consumer_price'];
					$tot['price'] 			+= $data['out_price'];

					$tot['member_sale'] 	+= $data['out_member_sale'];

					$tot['reserve'] 		+= $data['out_reserve'];
					$tot['point'] 			+= $data['out_point'];

					$tot['real_stock'] 		+= $real_stock;
					$tot['stock'] 				+= $stock;
					$tot['goods_code']		.= $data['goods_code'].', ';//@주문상품코드
					$tot['subtitle']			= $data['title'];
					$tot['suboption']		= $data['suboption'];

					if($data['title']) {
						$totitem['suboption'] .= ($totitem['suboption'])?', '.$tot['subtitle'].':'.$tot['suboption']:$tot['subtitle'].':'.$tot['suboption'];
					}
				}
			}
			$tot['item_seq']			= $item['item_seq'];
			if($totitem) {
				$tot['goods_name']		.= $item['goods_name'].'(';
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
			}else{
				$tot['goods_name']		.= $item['goods_name'].', ';//@주문상품
			}

			$tot['purchase_goods_name']		.= $item['purchase_goods_name'].', ';//@주문상품 도매상품명

			//$tot['goods_code']		.= $item['goods_code'].', ';//@주문상품
			$tot['goods_seq']		.= $item['goods_seq'].', ';//@주문상품
			$tot['count']				= count($items);

			$item['suboptions']	= $suboptions;
			$item['options']	= $options;
			$item['subinputoptions']	= $subinputoptions;
			$items[$key] 		= $item;
			$tot['goods_shipping_cost']	+= $item['goods_shipping_cost'];
		}
		return $tot;
	}



	public function get_export_ea($export_code){
		$query = "
			SELECT
				sum(B.ea) as export_ea
			FROM
				fm_goods_export A LEFT JOIN fm_goods_export_item B ON A.export_code = B.export_code
			WHERE
				A.export_code=?";
		$query = $this->db->query($query,array($export_code));
		$res = $query->row_array();
		return $res['export_ea'];
	}

	public function get_refund_item($order_seq){
		$query = "
			SELECT
				*
			FROM
				fm_order_refund A LEFT JOIN fm_order_refund_item B ON A.refund_code = B.refund_code
			WHERE
				A.order_seq=?";
		$query = $this->db->query($query,array($order_seq));
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

	public function get_refund_items($order_seq, $item_seq){
		$query = "
			SELECT
				*
			FROM
				fm_order_refund A LEFT JOIN fm_order_refund_item B ON A.refund_code = B.refund_code
			WHERE
				A.order_seq=? AND B.item_seq=?";
		$query = $this->db->query($query,array($order_seq,$item_seq));
		foreach($query->result_array() as $data){
			$items['refund_ea'] += $data['ea'];
		}
		return $items;
	}


	public function create_excel_list($criteria, $export_code){

		$this->load->library('pxl');
		$this->arr_payment = config_load('payment');

		$datas = get_data("fm_exceldownload",array("gb"=>"EXPORT"));
		$title_items = explode("|",$datas[0]['item']);
		$this->requiredsck($title_items);//필수항목체크

		$order_arr = explode("|",$export_code);

		// 개인정보 조회 로그 모델 로드
		$this->load->model('logPersonalInformation');

		for($i=0;$i<count($order_arr)-1;$i++){
			if(!$order_arr[$i]) continue;
			if($criteria=='EXPORT'){//출고번호별

				$sql = "SELECT
					A.*,
					B.member_seq,
					B.order_user_name,
					B.order_phone,
					B.order_cellphone,
					C.recipient_user_name,
					C.recipient_phone,
					C.recipient_cellphone,
					C.recipient_zipcode,
					C.recipient_address,
					C.recipient_address_street,
					C.recipient_address_detail,
					C.memo,
					B.international_country,
					B.international_town_city,
					B.international_county,
					B.international_address,
					B.regist_date as order_regist_date,
					B.admin_memo,
					B.settleprice,
					B.deposit_date,
					B.order_email,
					B.payment,
					B.pg
					FROM
						fm_goods_export A
						left join fm_order B on A.order_seq = B.order_seq
						left join fm_order_shipping C ON (A.order_seq = C.order_seq AND A.shipping_seq=C.shipping_seq)
					WHERE
						A.export_code = '{$order_arr[$i]}'";
				$query = $this->db->query($sql);
				foreach ($query->result_array() as $row){

					// 개인정보 조회 로그
					//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderprint' 'orderexcel', 'exportexcel'
					$this->logPersonalInformation->insert('exportexcel',$this->managerInfo['manager_seq'],$row['export_seq']);

					if($row['member_seq']){
						$member = get_data("fm_member",array("member_seq"=>$row['member_seq']));
						if($member) $row['userid'] = $member[0]['userid'];
					}

					// 카카오페이 표기 수정 :: 2015-03-05 lwh
					if($row['pg']=='kakaopay')
							$row['payment']	= '카카오페이';
					else	$row['payment']	= $this->arr_payment[$row['payment']];

					$items = $this->get_item($row['export_code'], $row['order_seq']);

					//여러개인경우 콤마구분 (명칭, 명칭, )
					$row['goods_name']	= substr($items['goods_name'],0,-2);
					$row['purchase_goods_name']	= substr($items['purchase_goods_name'],0,-2);
					$row['goods_code']		= substr($items['goods_code'],0,-2);
					$row['goods_seq']		= substr($items['goods_seq'],0,-2);
					$row['ea']					= $items['ea'];
					$row['goods_code']		= $items['goods_code'];//상품코드

					$row['export_ea']			= $this->get_export_ea($row['export_code']);

					if( $row['international'] == 'international' ){
						$row['shipping_method'] = $row['international_shipping_method'];
						$row['delivery_number'] = $row['international_delivery_no'];
						$row['delivery_company_code'] = '';
						$row['recipient_address'] = $row['international_country'].' '.$row['international_town_city'].' '.$row['international_county'];
						$row['recipient_address_detail'] = $row['international_address'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
					}else{
						$row['shipping_method'] = $row['domestic_shipping_method'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
						$row['recipient_address_street_all'] = $row['recipient_address_street']." ".$row['recipient_address_detail']; //전체주소
					}

					if($row['shipping_method']=='direct' || $row['shipping_method']=='quick'){
						$row['delivery_number']			= '';
						$row['delivery_company_code']		= '';
					}


					$row['status'] = $this->exportmodel->arr_status[$row['status']];
					$data[] = $row;
				}
			}else{//상품별

				$sql = "SELECT
					A.*,
					B.member_seq,
					B.order_user_name,
					B.order_phone,
					B.order_cellphone,
					E.recipient_user_name,
					E.recipient_phone,
					E.recipient_cellphone,
					E.recipient_zipcode,
					E.recipient_address,
					E.recipient_address_street,
					E.recipient_address_detail,
					E.memo,
					B.international_country,
					B.international_town_city,
					B.international_county,
					B.international_address,
					B.regist_date as order_regist_date,
					B.admin_memo,
					B.settleprice,
					B.deposit_date,
					B.payment,
					B.pg,
					B.order_email,
					C.item_seq,
					C.option_seq,
					C.suboption_seq,
					C.ea as export_ea,
					D.goods_seq,
					D.goods_code,
					D.tax,
					D.goods_name
					FROM
						fm_goods_export as A
						INNER JOIN fm_order as B ON A.order_seq = B.order_seq
						INNER JOIN fm_goods_export_item as C ON A.export_code = C.export_code
						INNER JOIN fm_order_item as D ON C.item_seq = D.item_seq
						INNER JOIN fm_order_shipping E ON (A.order_seq = E.order_seq AND A.shipping_seq=E.shipping_seq)
					WHERE
						A.export_code = '{$order_arr[$i]}'";
				$query = $this->db->query($sql);

				foreach ($query->result_array() as $row){
					if($row['member_seq']){
						$member = get_data("fm_member",array("member_seq"=>$row['member_seq']));
						if($member) $row['userid'] = $member[0]['userid'];
					}

					$items = array();
					unset($optiontitle,$inputoption);
					if($row['option_seq']) {
						$items = $this->get_item_option($row['option_seq']);
						/* 매입가 추가 단가로 표시하려면 주석처리 하면됨. leewh 2014-09-24 */
						$items['supply_price'] = $items['supply_price']*$items['ea'];
						$items['consumer_price'] = $items['consumer_price']*$items['ea'];
						$items['price'] = $items['price'];
						$items['ea_price'] = $row['export_ea'] * $items['price'];

						if($items['title1']) {
							$optiontitle .= ($totitem['option'])?', '.$items['title1'].':'.$items['option1']:$items['title1'].':'.$items['option1'];
						}

						if($items['title2']) {
							$optiontitle .= ', '.$items['title2'].':'.$items['option2'];
						}

						if($items['title3']) {
							$optiontitle .= ', '.$items['title3'].':'.$items['option3'];
						}

						if($items['title4']) {
							$optiontitle .= ', '.$items['title4'].':'.$items['option4'];
						}

						if($items['title5']) {
							$optiontitle .= ', '.$items['title5'].':'.$items['option5'];
						}
						if($optiontitle) $row['optiontitle']		= $optiontitle;

						//추가입력옵션
						$sql = "SELECT
							*
							FROM
								fm_order_item_input C
								LEFT JOIN fm_order_item B ON C.item_seq = B.item_seq
								LEFT JOIN fm_order A ON B.order_seq = A.order_seq
							WHERE
								A.order_seq = '{$row[order_seq]}' AND C.item_seq = '{$row['item_seq']}'  AND C.item_option_seq = '{$row['item_option_seq']}' ";
						$query = $this->db->query($sql.' order by C.item_input_seq ');
						foreach ($query->result_array() as $rowinput){
							$inputoption .= ($inputoption)?', '.$rowinput['title'].':'.$rowinput['value']:$rowinput['title'].':'.$rowinput['value'];
						}//endforeach
						if($inputoption) $row['subinputoption']		= $inputoption;
					}
					elseif($row['suboption_seq']) {
						$items = $this->get_sub_option($row['suboption_seq']);
						/* 매입가 추가 단가로 표시하려면 주석처리 하면됨. leewh 2014-09-24 */
						$items['supply_price']		= $items['supply_price'];
						$items['consumer_price']		= $items['consumer_price'];
						$items['price']						= $items['price'];
						$items['ea_price']				= $row['export_ea'] * $items['price'];
						if($items['title']) {
							$row['suboptiontitle'] = $items['title'].':'.$items['suboption'];
						}

					}else{
						continue;
					}

					$row['price_export_ea'] = $row['export_ea'] * $items['price'];
					$row = array_merge($row,$items);

					if( $row['international'] == 'international' ){
						$row['shipping_method']		 	= $row['international_shipping_method'];
						$row['delivery_number'] 		= $row['international_delivery_no'];
						$row['delivery_company_code']	= '';

						$row['recipient_address'] = $row['international_country'].' '.$row['international_town_city'].' '.$row['international_county'];
						$row['recipient_address_detail'] = $row['international_address'];
						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
					}else{
						$row['shipping_method']			= $row['domestic_shipping_method'];
						$row['delivery_number']			= $row['delivery_number'];
						$row['delivery_company_code']	= $row['delivery_company_code'];

						$row['recipient_address_all'] = $row['recipient_address']." ".$row['recipient_address_detail']; //전체주소
						$row['recipient_address_street_all'] = $row['recipient_address_street']." ".$row['recipient_address_detail']; //전체주소
					}

					if($row['shipping_method']=='direct' || $row['shipping_method']=='quick'){
						$row['delivery_number']			= '';
						$row['delivery_company_code']		= '';
					}

					$row['status'] = $this->exportmodel->arr_status[$row['status']];

					// 카카오페이 표기 수정 :: 2015-03-05 lwh
					if($row['pg']=='kakaopay')
							$row['payment']	= '카카오페이';
					else	$row['payment']	= $this->arr_payment[$row['payment']];

					$data[] = $row;
				}

			}

		}

		$this->excel_write($data, $title_items, $criteria);
	}

	//쿠폰상품 > 사용내역
	public function create_excel_coupon_use(){
		$this->load->library('pxl');
		$this->arr_payment = config_load('payment');
		$title_items = array();
		$add_title_items = array();
		$order_arr = explode("|",$export_code);

		$searchdate = ($_POST['searchdate'])?$_POST['searchdate']:$_GET['searchdate'];

		// 개인정보 조회 로그 모델 로드
		$this->load->model('socialgoodsgroupmodel');
		$couponuse_total = array();
		$sql = "SELECT
			L.*,
			L.regist_date as couponuse_regist_date,
			A.*,
			CASE WHEN L.coupon_value_type = 'price' THEN '원'
			ELSE '회' END AS coupon_value_unit,
			B.member_seq,
			B.order_user_name,
			B.order_phone,
			B.order_cellphone,
			B.recipient_user_name,
			B.recipient_phone,
			B.recipient_cellphone,
			B.regist_date as order_regist_date,
			B.admin_memo,
			B.settleprice,
			B.deposit_date,
			B.payment,
			B.order_email,
			C.item_seq,
			C.option_seq,
			C.suboption_seq,
			C.ea as export_ea,
			C.coupon_value,
			C.coupon_remain_value,
			C.coupon_serial,
			D.goods_seq,
			D.goods_code,
			D.tax,
			D.goods_name,
			D.social_goods_group
			FROM
				fm_goods_coupon_use_log as L
				INNER JOIN fm_goods_export as A ON A.export_code = L.export_code
				INNER JOIN fm_order as B ON A.order_seq = B.order_seq
				INNER JOIN fm_goods_export_item as C ON A.export_code = C.export_code
				INNER JOIN fm_order_item as D ON C.item_seq = D.item_seq
			WHERE
				L.confirm_user_serial is not null ";

		if( defined('__SELLERADMIN__') === true ){
			$sql .= " and D.provider_seq='{$this->providerInfo['provider_seq']}'  ";
		}

		if( $searchdate ) {
			$searchdatear = explode(" ~ ",$searchdate);
			$sql .= " and L.regist_date between '{$searchdatear[0]} 00:00:00' and '{$searchdatear[1]} 23:59:59'";
		}

		$sql.= " order by D.social_goods_group, D.goods_seq ,  L.regist_date asc ";
		$query = $this->db->query($sql);//debug_var($this->db->last_query());
		foreach ($query->result_array() as $row){$i++;
			if($row['member_seq']){
				$member = get_data("fm_member",array("member_seq"=>$row['member_seq']));
				if($member) $row['userid'] = $member[0]['userid'];
			}
			if($row['coupon_value_type'] == 'price' ) {
				$row['coupon_use_value_price'] = $row['coupon_use_value'];
				$row['coupon_remain_value_price'] = $row['coupon_value']-$row['coupon_use_value'];//$row['coupon_remain_value'];
			}else{
				$row['coupon_use_value_pass'] = $row['coupon_use_value'];
				$row['coupon_remain_value_pass'] = $row['coupon_value']-$row['coupon_use_value'];//$row['coupon_remain_value'];
			}

			$goodsseq = $row['goods_seq'];
			$sgg = $row['social_goods_group'];
			$confirm_user_serial = $row['confirm_user_serial'];
			$coupon_use_area = trim($row['coupon_use_area']);

			$query = "select * from fm_order_item_option where item_option_seq=?";
			$query = $this->db->query($query,array($row['option_seq']));
			$optionData = $query->row_array();

			if ( $row['coupon_value_type'] == 'price' ) {//금액
				$row['coupon_use_pass_price']	= (int) $row['coupon_use_value_price'];
			}else{//횟수
				$row['coupon_use_pass_price']	= (int) ($optionData['coupon_input_one'] * $row['coupon_use_value_pass']);
			}
			if( $row['address_commission'] > 0 ) {
				$row['address_commission_price'] = $row['coupon_use_pass_price']*(100-$row['address_commission'])/100;
				$row['address_commission_account'] = $row['coupon_use_pass_price'] - $row['address_commission_price'];
			}else{
				$row['address_commission'] = 0;
			}

			//쿠폰상품그룹
			if($row['social_goods_group']){
				$social_goods_group_data = $this->socialgoodsgroupmodel->get_data(array('select'=>' * ','group_seq'=>$row['social_goods_group']));
				$row['social_goods_group_name'] = $social_goods_group_data['name'];
			}else{
				$row['social_goods_group_name'] ="";
			}
			$s = ($s)?$s+1:2;
			if( in_array($sgg,$couponuse_total[$sgg]) ) {
				$couponuse_total[$sgg]['count']			= $s;
				$couponuse_total[$sgg]['address_commission_price']			= $couponuse_total[$sgg]['address_commission_price'] + $row['address_commission_price'];
				$couponuse_total[$sgg]['address_commission_account']		= $couponuse_total[$sgg]['address_commission_account'] + $row['address_commission_account'];
			}else{
				$couponuse_total[$sgg]['count']			= $s;
				$couponuse_total[$sgg][$sgg]			= $sgg;
				$couponuse_total[$sgg]['social_goods_group_name']			= ($row['social_goods_group_name'])?$row['social_goods_group_name']:"쿠폰상품그룹없음";
				$couponuse_total[$sgg]['address_commission_price']			= $row['address_commission_price'];
				$couponuse_total[$sgg]['address_commission_account']		= $row['address_commission_account'];
			}

			$data[] = $row;
		}

		$this->load->library('pxl');
		$filenames = "couponuse_down_".date("YmdHis").".xls";
		$fields = array("order_regist_date"=>"쿠폰 구매일","userid"=>"회원아이디","order_user_name"=>"회원이름",
			"order_cellphone"=>"핸드폰","order_email"=>"이메일","export_code"=>"출고번호","goods_seq"=>"상품고유값",
			"goods_name"=>"상품명","coupon_serial"=>"쿠폰코드","social_goods_group_name"=>"쿠폰 그룹명",
			"couponuse_regist_date"=>"쿠폰사용일","confirm_user"=>"확인매장명","confirm_user_serial"=>"확인코드",
			"coupon_use_area"=>"쿠폰사용장소","settle"=>"쿠폰구매가격","coupon_value"=>"쿠폰금액(횟수)","coupon_use_value_pass"=>"쿠폰사용횟수","coupon_remain_value_pass"=>"잔여쿠폰횟수","coupon_use_value_price"=>"쿠폰사용금액","coupon_remain_value_price"=>"잔여쿠폰금액","coupon_use_pass_price"=>"사용금액","address_commission"=>"매장수수료율","address_commission_price"=>"매장수수료","address_commission_account"=>"매장정산금액");

		$datas = array();
		$t=2;
		foreach ($data as $k)
		{
			$items = array();
			$i=0;
			foreach ($fields as $fieldskey=>$fieldsval)
			{
				$tmpvalue = $k[$fieldskey];
				if( $fieldskey == 'coupon_value' ) {
					$tmpvalue.=$k['coupon_value_unit'];
				}
				$items[$t][$i] = $tmpvalue;
				$i++;
			}
			@ksort($items[$t]);
			$datas[] = $items;
			$t++;
		}

		//debug_var($datas);exit;
		if(!$searchdate) $searchdate = '전체';
		$this->pxl->excel_download($datas, $fields, $filenames, '쿠폰상품사용내역', true, $searchdate, $couponuse_total);
	}

	public function excel_write($data, $title_items, $criteria) {
		$this->load->library('pxl');
		$filenames = ($criteria=='EXPORT')?"export_down_".date("YmdHis").".xls":"export_goods_down_".date("YmdHis").".xls";

		$item_arr = $this->itemList;
		$fields = array();
		$item = array();
		foreach($title_items as $k){
			if( $k == 'option' && $criteria!='EXPORT'){
				$item = array_merge($item, $this->temp_arr);
				$fields = array_merge($fields, $this->temp);
			}else{
				if($k == 'option' || !$item_arr[$k] || ( $criteria=='EXPORT' && (in_array($k, array('supply_price', 'consumer_price', 'price', 'ea_price'))) ) ) continue;

				$item[] = $k;
				$fields[$k] = $item_arr[$k];
			}
		}
		$cell_arr = $this->excel_cell(count($item));
		$cnt = count($fields);
		$t=2;
		foreach ($data as $k)
		{
			$items = array();
			for($i=0;$i<$cnt;$i++){
				$tmpname = $item[$i];
				$tmpvalue = $k[$tmpname];
				$items[$t][$i] = $tmpvalue;
			}
			@ksort($items[$t]);
			$datas[] = $items;
			$t++;
		}
		$this->pxl->excel_download($datas, $fields, $filenames,'출고엑셀일괄다운로드');
		//$this->pxl->pxl_excel_down($datas, $fields, $filenames,'출고엑셀일괄다운로드','export');
	}


	public function excel_upload($realfilename){
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
						case "*출고번호":			$cell_export_code				= $cell_arr[$i]; break;
						case "*출고완료일":		$cell_complete_date				= $cell_arr[$i]; break;
						case "*택배사코드":		$cell_delivery_company_code		= $cell_arr[$i]; break;
						case "*송장번호":			$cell_delivery_number			= $cell_arr[$i]; break;
					}
				}

				###
				$row_key = 0;
				$chk_cnt = 0;
				for ($i = 2 ; $i <= $maxRow ; $i++) {
					$export_code		= $objWorksheet->getCell($cell_export_code.$i)->getValue();
					$complete_date		= $objWorksheet->getCell($cell_complete_date.$i)->getValue();
					$delivery_company_code = $objWorksheet->getCell($cell_delivery_company_code.$i)->getValue();
					$delivery_number = $objWorksheet->getCell($cell_delivery_number.$i)->getValue();

					$resultExcelData[$row_key][0] = $export_code;

					if($export_code){
						$result = $this->export_update($export_code, $complete_date, $delivery_company_code, $delivery_number);
						if($result) {
							$chk_cnt++;
							$resultExcelData[$row_key][1] = '성공';
						}
					}

					$row_key++;
				}

				if($chk_cnt>0){
					$data['result']	= true;
					$data['count']	= $chk_cnt;
					$data['msg']	= $chk_cnt.'건 수정 되었습니다.';
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


	public function export_update($export_code, $complete_date, $delivery_company_code, $delivery_number){
		$this->load->model('ordermodel');
		$this->load->model('exportmodel');

		$export_chk = get_data("fm_goods_export", array("export_code"=>$export_code));

		if($export_chk[0]['international']=='domestic'){
			$data['delivery_number']			= $delivery_number;
			$data['delivery_company_code']		= $delivery_company_code;
			$data['international_delivery_no']		= '';

			$shipping_method = $export_chk[0]['domestic_shipping_method'];
		}else{
			$data['international_delivery_no']	= $delivery_number;
			$data['delivery_number']			= '';
			$data['delivery_company_code']		= '';

			$shipping_method = $export_chk[0]['international_shipping_method'];
		}

		if($shipping_method=='direct' || $shipping_method=='quick'){
			$data['delivery_number']			= '';
			$data['delivery_company_code']		= '';
			$data['international_delivery_no']		= '';
		}

		//$data['export_date']				= $complete_date;//출고일
		$data['complete_date']			= $complete_date;//출고완료일

		$this->db->where('export_code',$export_code);
		$this->db->update('fm_goods_export',$data);

		# 오픈마켓 송장등록 #
		$this->load->model('openmarketmodel');
		$this->openmarketmodel->request_send_export($export_code);

		return true;
	}

	public function excel_upload_result($resultExcelData){
		$this->load->library('pxl');
		$filenames = "export_excel_upload_result_".date("YmdHis").".xls";
		$t = 2;
		$datas = array();
		foreach($resultExcelData as $row){
			$items = array();
			$items[$t] = $row;
			$datas[] = $items;
		}

		$fields['export_code'] = "출고코드";
		$fields['result'] = "처리결과";

		$fileurl = '/data/tmp/'.$filenames;
		$filepath = ROOTPATH.'data/tmp/'.$filenames;
		$result = $this->pxl->excel_download($datas, $fields, $filenames,'엑셀 일괄 업로드 처리결과',false);
		file_put_contents($filepath,$result);
		return $fileurl;
	}

}
/* End of file excelexport.php */
/* Location: ./app/models/excelexport */