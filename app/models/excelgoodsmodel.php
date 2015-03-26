<?php
class Excelgoodsmodel extends CI_Model {
	var $downloadType		= "Excel5";
	var $saveurl			= "data/tmp";
	var $cell = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

	var $itemList = array(
		"goods_seq"				=> "*고유값",
		"goods_code"				=> "*상품코드",
		"category"					=> "*카테고리",
		"goods_name"				=> "*상품명",
		"option"						=> "*옵션",
		"option_use"				=> "*필수옵션사용",
		"option_view_type"		=> "*필수옵션타입",
		"reserve_policy"			=> "*적립금",
		"option_suboption_use"	=> "*추가옵션사용",
		"member_input_use"	=> "*추가입력옵션사용",
		"summary"					=> "간략설명",
		"tax"							=> "과세구분",
		"keyword"					=> "상품검색태그",
		"model"						=> "모델명(추가정보)",
		"brand"						=> "브랜드(추가정보)",
		"manufacture"				=> "제조사(추가정보)",
		"orgin"							=> "원산지(추가정보)",
		"image"						=> "이미지",
		"contents"					=> "상품설명(PC/테블릿)",
		"mobile_contents"		=> "상품설명(모바일)",
		"info_name"					=> "공용정보번호",
		"goods_status"			=> "상태",
		"goods_view"				=> "노출",
		"cg_brand"					=> "브랜드",
		"purchase_goods_name"	=> "매입용상품명",
		"string_price"				=> "가격대체문구",
		"multi_discount"			=> "복수구매할인가",
		"multi_discount_ea"		=> "복수구매할인제한갯수",
		"min_purchase_ea"		=> "최소구매수량",
		"max_purchase_ea"		=> "최대구매수량",
		"unlimit_shipping_price"	=> "배송비",
		"goods_weight"			=> "개별상품중량",
		"relation_seq"				=> "관련상품코드",
		"goods_sub_info"		=> "품목번호",
		"sub_info_desc"			=> "상품정보고시",
		"sale_seq"			=> "*회원그룹할인번호",
	);

	var $requireds = array(
		"goods_seq",
		"goods_code",
		"category",
		"goods_name",
		"option",
		"option_use",
		"option_view_type",
		"reserve_policy",
		"option_suboption_use",
		"member_input_use",
		"sale_seq"
	);

	var $image = array(
		"large"					=> "상품상세확대이미지",
		"view"					=> "상품상세이미지",
		"list1"					=> "리스트이미지1",
		"list2"					=> "리스트이미지2",
		"thumbView"		=> "상품상세썸네일",
		"thumbCart"			=> "장바구니썸네일",
		"thumbScroll"		=> "스크롤썸네일"
	);

	var $image_arr = array(
		"large",
		"view",
		"list1",
		"list2",
		"thumbView",
		"thumbCart",
		"thumbScroll"
	);


	var $temp = array(
		"option_seq"			=> "*옵션고유값",
		"title1"						=> "*옵션명1",
		"option1"					=> "*옵션값1",
		"title2"						=> "*옵션명2",
		"option2"					=> "*옵션값2",
		"title3"						=> "*옵션명3",
		"option3"					=> "*옵션값3",
		"title4"						=> "*옵션명4",
		"option4"					=> "*옵션값4",
		"title5"						=> "*옵션명5",
		"option5"					=> "*옵션값5",
		"infomation"				=> "옵션정보",
		"subrequired"			=> "*추가옵션필수여부",
		"subtitle"					=> "*추가옵션명",
		"suboption"				=> "*추가옵션값",
		"stock"						=> "*재고",
		"supply_price"			=> "*매입가",
		"option_consumer"	=> "*정가",
		"option_price"			=> "*옵션할인가(판매가)",
		"reserve_rate"			=> "*개별적립(원/율)",
		"reserve_unit"			=> "*개별적립금단위",
		"reserve"					=> "개별적립금금액",
		"input_name"			=> "*추가입력옵션명",
		"input_form"			=> "*추가입력형식",
		"input_limit"				=> "*추가입력제한",
		"input_require"		=> "*추가입력필수여부"
	);

	var $temp_arr = array(
		"option_seq",
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
		"infomation",
		"subrequired",
		"subtitle",
		"suboption",
		"stock",
		"supply_price",
		"option_consumer",
		"option_price",
		"reserve_rate",
		"reserve_unit",
		"reserve",
		"input_name",
		"input_form",
		"input_limit",
		"input_require"
	);

	//특수옵션 필드
	var $sync_fields = array(
		'fm_goods_option_tmp' => array(
			'code_seq',
			'option_type',
			'optioncode1',
			'optioncode2',
			'optioncode3',
			'optioncode4',
			'optioncode5',
			'tmpprice',
			'color',
			'zipcode',
			'address_type',
			'address',
			'address_street',
			'addressdetail',
			'newtype',
			'codedate',
			'sdayinput',
			'fdayinput',
			'dayauto_type',
			'sdayauto',
			'fdayauto',
			'dayauto_day',
			'biztel',
			'coupon_input',
			'address_commission',
			'tmp_policy',
			'tmp_date',
			'tmp_no'
		),
		'fm_goods_suboption_tmp' => array(
			'code_seq',
			'sub_sale',
			'suboption_code',
			'commission_rate',
			'suboption_type',
			'color',
			'zipcode',
			'address_type',
			'address',
			'address_street',
			'addressdetail',
			'newtype',
			'codedate',
			'sdayinput',
			'fdayinput',
			'dayauto_type',
			'sdayauto',
			'fdayaut',
			'dayauto_day',
			'biztel',
			'coupon_input',
			'tmp_date',
			'tmp_no',
		)
	);

	var $goods_status_title			= array("normal"=>"정상", "runout"=>"품절", "purchasing"=>"재고확보중", "unsold"=>"판매중지");//상태
	var $goods_view_title				= array("look"=>"노출", "notLook"=>"미노출");//노출
	var $option_view_type_title	= array("divide"=>"옵션분리형", "join"=>"옵션합체형");//필수옵션타입
	var $input_form_title				= array("edit"=>"에디트박스", "text"=>"텍스트박스", "file"=>"이미지 업로드");//추가 입력 형식
	var $tax_title							= array("tax"=>"과세", "exempt"=>"비과세");//과세구분

	//단위
	var $price_unit_title					= array("percent"=>"%", "won"=>"원");
	var $use_title							= array("Y"=>"사용함", "N"=>"미사용");
	var $use_number_title				= array("0"=>"미사용", "1"=>"사용함");

	var $require_title						= array("Y"=>"필수", "N"=>"필수아님","y"=>"필수", "n"=>"필수아님");
	var $require_number_title		= array("0"=>"필수아님", "1"=>"필수");

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

	/**
	* data 동일하게 복사하기
	* $copy_table 복사테이블 //fm_goods_option_tmp, fm_goods_suboption_tmp
	* $target_table 원본테이블 //fm_goods_option, fm_goods_suboption
	* 상품고유번호
	* 상품고유번호 필드명 //'option_seq'suboption_seq
	**/
	function goods_option_replace_into($copy_table, $target_table, $goods_seq , $tmp_no = null) {
		$this->db->delete($copy_table, array('goods_seq' => $goods_seq));//_tmp table
		if(!$tmp_no){
			$tmp_no = ($target_table == 'fm_goods_suboption')?'suboption_seq':'option_seq';
		}
		$copy_fields = $this->db->list_fields($copy_table);
		$target_fields = $this->db->list_fields($target_table);
		unset($target_record,$copy_record);
		foreach($copy_fields as $k=>$copy_field) {
			if( $copy_field == 'tmp_policy' ) continue;
			foreach($target_fields as $target_field) {
				if ($target_field == $copy_field ) {
					$copy_record[$k]		= $copy_field;
					$target_record[$k]	= $copy_field;
				}else{
					if( $copy_field == "tmp_date" ) {
						$copy_record[$k]		= $copy_field;
						$target_record[$k]	= date('Ymd');
					}elseif( $copy_field == "tmp_no" ) {
						$copy_record[$k]		= $copy_field;
						$target_record[$k]	= $tmp_no;
					}
				}
			}
		}

		if( count($copy_record) == count($copy_record) ) {
			$sql = "REPLACE INTO `{$copy_table}` ";//INSERT  REPLACE
			$copy_bind_sql = implode(", ",$copy_record);
			$sql .= " ({$copy_bind_sql}) ";
			$sql .= " select ";
			$target_bind_sql = implode(", ",$target_record);
			$sql .= " {$target_bind_sql} ";
			$sql .= " from {$target_table} where goods_seq='{$goods_seq}' ";
			$this->db->query($sql);
		}
		//debug_var($copy_record);
		//debug_var($target_record);
	}

	//백업된 데이타가져오기
	function get_goods_option_tmp($target_table, $goods_seq , $tmp_no , &$data) {
		$sql = "SELECT * FROM {$target_table} WHERE tmp_no = '{$tmp_no}' ";//goods_seq = '{$goods_seq}' and
		$query = $this->db->query($sql);
		$target_data_query = $query -> result_array();
		foreach ($target_data_query as $target_data){
			foreach($this->sync_fields[$target_table] as $field) {
				$data[$field] = $target_data[$field];
			}
		}
	}

	public function create_excel_list($gets){
		###
		$datas = get_data("fm_exceldownload",array("gb"=>"GOODS"));
		$title_items = explode("|",$datas[0]['item']);
		$this->requiredsck($title_items);//필수항목체크

		if($gets['excel_type']=='select'){


			for($i=0;$i<count($gets['goods_seq']);$i++){
				###
				if( defined('__SELLERADMIN__') === true ){
					//$providersql = "and A.provider_seq={$this->providerInfo['provider_seq']} ";
				}
				###
				$sql = "select
						*
					from
						fm_goods A
					where
						A.goods_seq = '{$gets['goods_seq'][$i]}' {$providersql}
				";
				$query = $this->db->query($sql);
				foreach($query->result_array() as $row){
					### IMAGE
					$goods_image		= $this->get_goods_image_all($row['goods_seq']);
					$row['large']		= $goods_image['large'];
					$row['view']			= $goods_image['view'];
					$row['list1']			= $goods_image['list1'];
					$row['list2']			= $goods_image['list2'];
					$row['thumbView']		= $goods_image['thumbView'];
					$row['thumbCart']		= $goods_image['thumbCart'];
					$row['thumbScroll']		= $goods_image['thumbScroll'];

					### INFO_NAME
					$row['info_name']		= $row['info_seq'];

					//viewer title
					$row['goods_status']			= $this->goods_status_title[$row['goods_status']];
					$row['goods_view']				= $this->goods_view_title[$row['goods_view']];
					$row['option_view_type']	= $this->option_view_type_title[$row['option_view_type']];
					$row['tax']							= $this->tax_title[$row['tax']];

					$row['option_use']						= $this->use_number_title[$row['option_use']];
					$row['option_suboption_use']		= $this->use_number_title[$row['option_suboption_use']];
					$row['member_input_use']			= $this->use_number_title[$row['member_input_use']];

					$row['reserve_policy']	= $row['reserve_policy']=='shop' ? "기본" : "개별";


					### PROVIDER
					//$row['provider_id'] = get_provider_id($row['provider_seq']);

					### ADDITION
					$goods_addition		= $this->get_goods_addition_all($row['goods_seq']);
					$row['model']		= $goods_addition['model'];
					$row['brand']		= $goods_addition['brand'];
					$row['manufacture']	= $goods_addition['manufacture'];
					$row['orgin']		= $goods_addition['orgin'];

					### RELATION
					$row['relation_seq']		= $this->get_goods_relation($row['goods_seq']);

					### CATEGORY --> 맨마지막이 대표로 처리됨
					$row['category']			= $this->get_goods_category($row['goods_seq']);

					### BRAND --> 맨마지막이 대표로 처리됨
					$row['cg_brand']			= $this->get_goods_brand($row['goods_seq']);

					//원|% 합침
					if( $row['multi_discount_unit'] == 'percent' ) {
						$row['multi_discount']	= $row['multi_discount'].'%';
					}else{
						$row['multi_discount']	= $row['multi_discount'].'원';
					}

					### SHIPPING
					if($row['shipping_policy']=="shop") $row['unlimit_shipping_price'] = "";

					### OPTION
					$row['option']		= $this->get_goods_option($row['goods_seq'], $row['reserve_policy']);
					$row['suboption']	= $this->get_goods_suboption($row['goods_seq'], $row['reserve_policy']);
					$row['subinputoption']	= $this->get_goods_subinputoption($row['goods_seq']);

					$row['goods_name'] = htmlspecialchars($row['goods_name']);
					$row['summary'] = htmlspecialchars($row['summary']);
					$row['keyword'] = htmlspecialchars($row['keyword']);
					$row['purchase_goods_name'] = htmlspecialchars($row['purchase_goods_name']);

					$row['contents'] = htmlspecialchars($row['contents']);
					$row['mobile_contents'] = htmlspecialchars($row['mobile_contents']);

					### 품목
					$row['goods_sub_info']	= $row['goods_sub_info'];

					$row['sub_info_desc'] = json_decode($row['sub_info_desc']);
					$sub_info_desc = "";
					foreach($row['sub_info_desc'] as $key => $value){
						$key = str_replace("_empty_","",$key);
						if($key && $value){
							if($sub_info_desc == ""){
								$sub_info_desc = $key.":".$value;
							}else{
								$sub_info_desc .= "|".$key.":".$value;
							}
						}
					}
					$row['sub_info_desc'] = $sub_info_desc;

					###
					$data[] = $row;
				}
			}
		}else{
			###
			$_GET = $gets;

			if( defined('__SELLERADMIN__') === true ){
				//$_GET['provider_seq'] = $this->providerInfo['provider_seq'];
			}

			if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];
			### SEARCH			
			//정렬관련 추가 (정가, 할인가, 재고 오름/내림 차순 정렬)
			$orderbyTmp = explode("_",$_GET['orderby']);
			if(in_array($orderbyTmp[0],array("asc","desc"))){
				foreach($orderbyTmp as $orderK=>$orderV) if($orderK > 0) $orderbyTmp2[] = $orderV;
				$_GET['orderby']	= implode("_",$orderbyTmp2);
			}else{
				$_GET['orderby'];
			}
			$_GET['orderby'] = ($_GET['orderby']) ? $_GET['orderby']:'goods_seq';
			$_GET['sort']	 = ($_GET['sort']) ? $_GET['sort']:'desc';
			$_GET['page']	 = ($_GET['page']) ? intval($_GET['page']):'1';
			$_GET['perpage'] = ($_GET['perpage']) ? intval($_GET['perpage']):'10';
			$sc = $_GET;
			

			$sc['goods_type']	= 'goods';
			$this->goodsmodel->batch_mode = 1;
			$squery = $this->goodsmodel->admin_goods_list($sc);
			$query = $this->db->query($squery);

			foreach ($query->result_array() as $row){
				### IMAGE
				$goods_image		= $this->get_goods_image_all($row['goods_seq']);
				$row['large']		= $goods_image['large'];
				$row['view']			= $goods_image['view'];
				$row['list1']			= $goods_image['list1'];
				$row['list2']			= $goods_image['list2'];
				$row['thumbView']		= $goods_image['thumbView'];
				$row['thumbCart']		= $goods_image['thumbCart'];
				$row['thumbScroll']		= $goods_image['thumbScroll'];

				### INFO_NAME
				$row['info_name']	= $row['info_seq'];

				//viewer title
				$row['goods_status']			= $this->goods_status_title[$row['goods_status']];
				$row['goods_view']				= $this->goods_view_title[$row['goods_view']];
				$row['option_view_type']	= $this->option_view_type_title[$row['option_view_type']];
				$row['tax']							= $this->tax_title[$row['tax']];
				$row['option_use']						= $this->use_number_title[$row['option_use']];
				$row['option_suboption_use']		= $this->use_number_title[$row['option_suboption_use']];
				$row['member_input_use']			= $this->use_number_title[$row['member_input_use']];

				$row['reserve_policy']	= $row['reserve_policy']=='shop' ? "기본" : "개별";

				### PROVIDER
				//$row['provider_id'] = get_provider_id($row['provider_seq']);

				### ADDITION
				$goods_addition		= $this->get_goods_addition_all($row['goods_seq']);
				$row['model']		= $goods_addition['model'];
				$row['brand']		= $goods_addition['brand'];
				$row['manufacture']	= $goods_addition['manufacture'];
				$row['orgin']		= $goods_addition['orgin'];

				### RELATION
				$row['relation_seq'] = $this->get_goods_relation($row['goods_seq']);

				### CATEGORY --> 맨마지막이 대표로 처리됨
				$row['category']			= $this->get_goods_category($row['goods_seq']);

				### BRAND --> 맨마지막이 대표로 처리됨
				$row['cg_brand']			= $this->get_goods_brand($row['goods_seq']);

				//원|% 합침
				if( $row['multi_discount_unit'] == 'percent' ) {
					$row['multi_discount']	= $row['multi_discount'].'%';
				}else{
					$row['multi_discount']	= $row['multi_discount'].'원';
				}

				### SHIPPING
				if($row['shipping_policy']=="shop") $row['unlimit_shipping_price'] = "";

				### OPTION
				$row['option']		= $this->get_goods_option($row['goods_seq'], $row['reserve_policy']);
				$row['suboption']	= $this->get_goods_suboption($row['goods_seq'], $row['reserve_policy']);
				$row['subinputoption']	= $this->get_goods_subinputoption($row['goods_seq']);

				$row['goods_name'] = htmlspecialchars($row['goods_name']);
				$row['summary'] = htmlspecialchars($row['summary']);
				$row['keyword'] = htmlspecialchars($row['keyword']);
				$row['purchase_goods_name'] = htmlspecialchars($row['purchase_goods_name']);

				$row['contents'] = htmlspecialchars($row['contents']);
				$row['mobile_contents'] = htmlspecialchars($row['mobile_contents']);

				### 품목
				$row['goods_sub_info']	= $row['goods_sub_info'];

				$row['sub_info_desc'] = json_decode($row['sub_info_desc']);
				$sub_info_desc = "";
				foreach($row['sub_info_desc'] as $key => $value){
					$key = str_replace("_empty_","",$key);
					if($key && $value){
						if($sub_info_desc == ""){
							$sub_info_desc = $key.":".$value;
						}else{
							$sub_info_desc .= "|".$key.":".$value;
						}
					}
				}

				$row['sub_info_desc'] = $sub_info_desc;
				###
				$data[] = $row;
			}
		}

		$this->excel_write($data, $title_items);
	}

	public function excel_write($data, $title_items) {
		$this->load->library('pxl');
		$filenames = "goods_down_".date("YmdHis").".xls";
		$item_arr = $this->itemList;
		$fields = array();
		$item = array();
		foreach($title_items as $k){
			if( $k == 'option' ){
				$item = array_merge($item, $this->temp_arr);
				$fields = array_merge($fields, $this->temp);
			}elseif( $k == 'image' ){
				$item = array_merge($item, $this->image_arr);
				$fields = array_merge($fields, $this->image);
			}else{
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
		$temp0 = array_search('option5',$item);

		$temps1 = array_search('option_seq',$item);
		$temps2 = array_search('option_price',$item);
		$temps3 = array_search('reserve_rate',$item);
		$temps4 = array_search('reserve_unit',$item);
		$temps5 = array_search('reserve',$item);
		$temps6 = array_search('stock',$item);

		//$temps17 = array_search('commission_rate',$item);
		$temps7 = array_search('supply_price',$item);
		$temps8 = array_search('option_consumer',$item);
		$temps9 = array_search('subrequired',$item);
		$temps10 = array_search('subtitle',$item);
		$temps11 = array_search('suboption',$item);
		$temps12 = array_search('infomation',$item);

		$temps13 = array_search('input_name',$item);
		$temps14 = array_search('input_form',$item);
		$temps15 = array_search('input_limit',$item);
		$temps16 = array_search('input_require',$item);

		foreach ($data as $k)
		{
			$items = array();
			for($i=0;$i<$cnt;$i++){
				$tmp = $item[$i];
				if( $tmp!="stock" &&  $tmp!="option"  &&  $tmp!="suboption"  ){
					$items[$t][$i] = $k[$tmp];
				}
			}
			$t++;
			if($k['option']){
				for($j=0;$j<count($k['option']);$j++){//
					$tmp_arr = explode(",",$k['option'][$j]['option_title']);
					$items[$t][$temp1] = htmlspecialchars($tmp_arr[0]);//$tmp_arr[0];
					$items[$t][$temp2] = htmlspecialchars($tmp_arr[1]);//$tmp_arr[1];
					$items[$t][$temp3] = htmlspecialchars($tmp_arr[2]);//$tmp_arr[2];
					$items[$t][$temp4] = htmlspecialchars($tmp_arr[3]);//$tmp_arr[3];
					$items[$t][$temp5] = htmlspecialchars($tmp_arr[4]);//$tmp_arr[4];
					$items[$t][$temp6] = $k['option'][$j]['option1'];
					$items[$t][$temp7] = $k['option'][$j]['option2'];
					$items[$t][$temp8] = $k['option'][$j]['option3'];
					$items[$t][$temp9] = $k['option'][$j]['option4'];
					$items[$t][$temp0] = $k['option'][$j]['option5'];

					$items[$t][$temps1] = $k['option'][$j]['option_seq'];
					$items[$t][$temps2] = $k['option'][$j]['price'];
					$items[$t][$temps3] = $k['option'][$j]['reserve_rate'];

					//viewer title
					$k['option'][$j]['reserve_unit']	= $this->price_unit_title[$k['option'][$j]['reserve_unit']];
					$items[$t][$temps4] = $k['option'][$j]['reserve_unit'];
					$k['option'][$j]['reserve']	= $k['option'][$j]['reserve_unit']=='%' ? floor($k['option'][$j]['price']*$k['option'][$j]['reserve_rate'] / 100) : $k['option'][$j]['reserve_rate'];

					$items[$t][$temps5] = $k['option'][$j]['reserve'];
					$items[$t][$temps6] = $k['option'][$j]['stock'];
					//$items[$t][$temps17] = $k['option'][$j]['commission_rate'];
					$items[$t][$temps7] = $k['option'][$j]['supply_price'];
					$items[$t][$temps8] = $k['option'][$j]['consumer_price'];
					$items[$t][$temps12] = $k['option'][$j]['infomation'];

					ksort($items[$t]);
					$t++;
				}
			}

			if($k['suboption']) {
				for($j=0;$j<count($k['suboption']);$j++) {
					$items[$t][$temps1] = $k['suboption'][$j]['suboption_seq'];
					$items[$t][$temps2] = $k['suboption'][$j]['price'];
					$items[$t][$temps3] = $k['suboption'][$j]['reserve_rate'];

					//viewer title
					$k['suboption'][$j]['reserve_unit']	= $this->price_unit_title[$k['suboption'][$j]['reserve_unit']];
					$items[$t][$temps4] = $k['suboption'][$j]['reserve_unit'];

					$k['suboption'][$j]['reserve']	= $k['suboption'][$j]['reserve_unit']=='%' ? floor($k['suboption'][$j]['price']*$k['suboption'][$j]['reserve_rate'] / 100) : $k['suboption'][$j]['reserve_rate'];
					$items[$t][$temps5] = $k['suboption'][$j]['reserve'];
					$items[$t][$temps6] = $k['suboption'][$j]['stock'];
					$items[$t][$temps17] = $k['suboption'][$j]['commission_rate'];
					$items[$t][$temps7] = $k['suboption'][$j]['supply_price'];
					$items[$t][$temps8] = $k['suboption'][$j]['consumer_price'];

					//viewer title
					$k['suboption'][$j]['sub_required']	= $this->require_title[$k['suboption'][$j]['sub_required']];
					$items[$t][$temps9] = $k['suboption'][$j]['sub_required'];

					$items[$t][$temps10] = htmlspecialchars($k['suboption'][$j]['suboption_title']);//$k['suboption'][$j]['suboption_title'];
					$items[$t][$temps11] = $k['suboption'][$j]['suboption'];
					ksort($items[$t]);
					$t++;
				}
			}

			//추가입력옵션
			if($k['subinputoption']) {
				for($j=0;$j<count($k['subinputoption']);$j++) {
					$items[$t][$temps13] = htmlspecialchars($k['subinputoption'][$j]['input_name']);//$k['subinputoption'][$j]['input_name'];

					//viewer title
					$k['subinputoption'][$j]['input_form']	= $this->input_form_title[$k['subinputoption'][$j]['input_form']];
					$items[$t][$temps14] = $k['subinputoption'][$j]['input_form'];

					$items[$t][$temps15] = ($k['subinputoption'][$j]['input_form']=="file" || strstr($k['subinputoption'][$j]['input_form'],"이미지") )?"2M":$k['subinputoption'][$j]['input_limit'];

					//viewer title
					$k['subinputoption'][$j]['input_require']	= $this->require_number_title[$k['subinputoption'][$j]['input_require']];
					$items[$t][$temps16] = $k['subinputoption'][$j]['input_require'];

					ksort($items[$t]);
					$t++;
				}
			}

			$datas[] = $items;
		}
		$this->pxl->excel_download($datas, $fields, $filenames,'상품엑셀일괄다운로드');
		//$this->pxl->pxl_excel_down($datas, $fields, $filenames,'상품엑셀일괄다운로드','goods');
	}

	public function excel_upload($realfilename){
		$this->load->library('pxl');
		$this->load->model('goodsmodel');
		$this->load->model('membermodel');

		set_time_limit(0);
		ini_set('memory_limit', '3500M');
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '5120MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		$this->objPHPExcel = new PHPExcel();

		//viewer title 키와 값변경
		$this->goods_status_title			= array_flip($this->goods_status_title);
		$this->goods_view_title				= array_flip($this->goods_view_title);
		$this->option_view_type_title		= array_flip($this->option_view_type_title);
		$this->tax_title							= array_flip($this->tax_title);
		$this->price_unit_title					= array_flip($this->price_unit_title);
		$this->input_form_title				= array_flip($this->input_form_title);
		$this->use_title							= array_flip($this->use_title);
		$this->require_title						= array_flip($this->require_title);
		$this->use_number_title				= array_flip($this->use_number_title);
		$this->require_number_title		= array_flip($this->require_number_title);



		if(is_file($realfilename)){
			try {
				// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
				$objReader = IOFactory::createReaderForFile($realfilename);
				// 읽기전용으로 설정
				//if( function_exists('$objReader->setReadDataOnly()') ) {
					$objReader->setReadDataOnly(true);
				//}
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

				$item_arr = $this->itemList;
				$fields = array();
				$item = array();

				$datas = get_data("fm_exceldownload",array("gb"=>"GOODS"));
				$items = explode("|",$datas[0]['item']);
				if( !$this->requiredsck($items, 'upload') ) {//필수항목체크
					$data['result']	= false;
					$data['count']	= 0;
					$data['msg']	= '다운로드 양식의 필수항목이 빠져 있습니다.<br/>다운로드 양식을 다시한번 확인해 주세요.';
					return $data;
				}

				foreach($items as $k) {
					if( $k == 'option' ){
						$item = array_merge($item, $this->temp_arr);
						$fields = array_merge($fields, $this->temp);
					}elseif( $k == 'image' ){
						$item = array_merge($item, $this->image_arr);
						$fields = array_merge($fields, $this->image);
					}else{
						$item[] = $k;
						$fields[$k] = $item_arr[$k];
					}
				}

				for($i=0; $i<$colCount; $i++){
					$tmp = $objWorksheet->getCell($cell_arr[$i] . "1")->getValue();
					foreach($fields as $k=>$v){
						if($tmp==$v) $tab[$k] = $cell_arr[$i];
					}
				}
				$sucess			= 0;
				###
				for ($i = 2 ; $i <= $maxRow ; $i++) {
					$goods_seq	= $objWorksheet->getCell($tab['goods_seq'].$i)->getValue();
					$goods_name = $objWorksheet->getCell($tab['goods_name'].$i)->getValue();
					if (is_object($goods_name)) {//excel셀서식적용시실제값가져오기
						$objRichText = new PHPExcel_RichText($objWorksheet->getCell($tab['goods_name'].$i));
						$goods_name = $objRichText->getPlainText();
					}

					if($goods_name){
						unset($params);
						### FM_GOODS

						//if($tab['provider_id']) $params['provider_id']		= $objWorksheet->getCell($tab['provider_id'].$i)->getValue();

						if($tab['goods_code']) $params['goods_code']		= $objWorksheet->getCell($tab['goods_code'].$i)->getValue();
						$params['goods_name'] = trim(($goods_name));

						if($tab['summary']) $params['summary']			= trim(($objWorksheet->getCell($tab['summary'].$i)->getValue()));
						if($tab['keyword']) $params['keyword']			= trim(($objWorksheet->getCell($tab['keyword'].$i)->getValue()));
						if($tab['contents']) $params['contents']			= trim(($objWorksheet->getCell($tab['contents'].$i)->getValue()));
						if($tab['mobile_contents']) $params['mobile_contents']			= trim(($objWorksheet->getCell($tab['mobile_contents'].$i)->getValue()));
						if($tab['info_name']) $params['info_name']		= trim(($objWorksheet->getCell($tab['info_name'].$i)->getValue()));
						if($tab['goods_status']) {
							$params['goods_status']	 = $objWorksheet->getCell($tab['goods_status'].$i)->getValue();
							if($this->goods_status_title[$params['goods_status']])
								$params['goods_status']	 = $this->goods_status_title[$params['goods_status']];
						}
						if($tab['goods_view']) {
							$params['goods_view']	= $objWorksheet->getCell($tab['goods_view'].$i)->getValue();
							if( $this->goods_view_title[$params['goods_view']] )
								$params['goods_view']	= $this->goods_view_title[$params['goods_view']];
						}
						if($tab['purchase_goods_name']) $params['purchase_goods_name']	= trim(($objWorksheet->getCell($tab['purchase_goods_name'].$i)->getValue()));

						if($tab['string_price']) {
							$params['string_price']		= trim(($objWorksheet->getCell($tab['string_price'].$i)->getValue()));
							if($params['string_price']) $params['string_price_use'] = '1';
						}

						if($tab['multi_discount']) {
							$params['multi_discount']	= $objWorksheet->getCell($tab['multi_discount'].$i)->getValue();
							if( strstr($params['multi_discount'],'%') ){
								$params['multi_discount_unit']	= 'percent';
							}else{
								$params['multi_discount_unit']	= 'won';
							}
							$params['multi_discount'] = str_replace("원","",str_replace("%","",$params['multi_discount']));//숫자형
							if($params['multi_discount']>0) $params['multi_discount_use']	= '1';
						}

						if($tab['multi_discount_ea']) $params['multi_discount_ea']	= $objWorksheet->getCell($tab['multi_discount_ea'].$i)->getValue();

						if($tab['min_purchase_ea']) {
							$params['min_purchase_ea']	= $objWorksheet->getCell($tab['min_purchase_ea'].$i)->getValue();
							$params['min_purchase_limit'] = ($params['min_purchase_ea']>0)?'limit':'unlimit';// unlimit|limit
						}

						if($tab['max_purchase_ea']) {
							$params['max_purchase_ea']	= $objWorksheet->getCell($tab['max_purchase_ea'].$i)->getValue();
							$params['max_purchase_limit'] = ($params['max_purchase_ea']>0)?'limit':'unlimit';// unlimit|limit
						}

						if($tab['goods_weight']) $params['goods_weight']		= $objWorksheet->getCell($tab['goods_weight'].$i)->getValue();
						if($tab['relation_seq']) $params['relation_seq']		= $objWorksheet->getCell($tab['relation_seq'].$i)->getValue();
						if($tab['infomation']) $params['infomation']		= $objWorksheet->getCell($tab['infomation'].$i)->getValue();
						if($tab['goods_sub_info']) $params['goods_sub_info']	= $objWorksheet->getCell($tab['goods_sub_info'].$i)->getValue();

						if($tab['sub_info_desc']) {
							$params['sub_info_desc']		= $objWorksheet->getCell($tab['sub_info_desc'].$i)->getValue();

							$sub_info_desc_arr = explode("|", $params['sub_info_desc']);
							unset($sub_info_desc);//상품정보고시 초기화
							foreach($sub_info_desc_arr as $k){
								$sub_info_desc_arr2 = explode(":", $k);
								$sub_info_desc[$sub_info_desc_arr2[0]] = $sub_info_desc_arr2[1];
							}

							$params['sub_info_desc'] = $sub_info_desc;
						}

						if($tab['sale_seq']){
							$params['sale_seq'] = $objWorksheet->getCell($tab['sale_seq'].$i)->getValue();
						}else{
							$default_sale_seq = $this->membermodel->get_member_sale("", "sale_seq");
							$params['sale_seq'] = $defualt_sale_seq[0]["sale_seq"];
						}
						if(!$params['sale_seq']) $params['sale_seq'] = 1;

						

						if($tab['unlimit_shipping_price']){
							$unlimit_shipping_price = $objWorksheet->getCell($tab['unlimit_shipping_price'].$i)->getValue();
							if($unlimit_shipping_price =='') {
								$params['shipping_policy']	= 'shop';
								$params['unlimit_shipping_price'] = 0;
							}else if($unlimit_shipping_price >= 0) {
								$params['shipping_policy']	= 'goods';
								$params['goods_shipping_policy']	= 'unlimit';
								$params['unlimit_shipping_price']	= $unlimit_shipping_price;
							}
						}

						###
						if($tab['option_use']){
							$params['option_use']		= $objWorksheet->getCell($tab['option_use'].$i)->getValue();
							if( $this->use_number_title[$params['option_use']] ) {
								$params['option_use'] = (string) $this->use_number_title[$params['option_use']];
							}else{
								$params['option_use'] = '0';
							}
						}
						###
						if($tab['option_suboption_use']){
							$params['option_suboption_use']		= $objWorksheet->getCell($tab['option_suboption_use'].$i)->getValue();
							if( $this->use_number_title[$params['option_suboption_use']] ) {
								$params['option_suboption_use'] = (string) $this->use_number_title[$params['option_suboption_use']];
							}else{
								$params['option_suboption_use'] = '0';
							}
						}

						if($tab['member_input_use']){
							$params['member_input_use']		= $objWorksheet->getCell($tab['member_input_use'].$i)->getValue();
							if( $this->use_number_title[$params['member_input_use']] ) {
								$params['member_input_use'] = (string) $this->use_number_title[$params['member_input_use']];
							}else{
								$params['member_input_use'] = '0';
							}
						}

						if($tab['option_view_type']){
							$params['option_view_type']		= $objWorksheet->getCell($tab['option_view_type'].$i)->getValue();
							if( $this->option_view_type_title[$params['option_view_type']] )
								$params['option_view_type']	= $this->option_view_type_title[$params['option_view_type']];
						}

						if($tab['tax']){
							$params['tax']		= $objWorksheet->getCell($tab['tax'].$i)->getValue();
							if( $this->tax_title[$params['tax']] && $this->config_system['service']['code'] != 'P_FREE') {
								$params['tax']	= $this->tax_title[$params['tax']];
							}else{
								$params['tax']	= 'tax';//과세
							}
						}

						if($tab['reserve_policy']){
							$params['reserve_policy']		= $objWorksheet->getCell($tab['reserve_policy'].$i)->getValue();
							$params['reserve_policy'] = $params['reserve_policy']=="기본" ? "shop" : "goods";
						}

						foreach($params as $k=>$v) {
							if(is_null($v)) $params[$k] = '';
							if (is_object($params[$k])) {//excel셀서식적용시실제값가져오기
								$objRichText = new PHPExcel_RichText($objWorksheet->getCell($tab[$k].$i));
								$params[$k] = $objRichText->getPlainText();
							}
						}

						if($goods_seq){
							$result = $this->update_goods($goods_seq, $params, $tab);
						}else{
							$goods_seq = $this->insert_goods($params);
							$this->goods_regist = true;//생성됨
						}

						$r_goods_seq[$goods_seq] = 1;

						### CATEGORY
						if($tab['category']){
							unset($addcategoryar, $addcategory);
							$addcategory = $objWorksheet->getCell($tab['category'].$i)->getValue();
							$addcategory = trim($addcategory);
							$addcategory = str_replace(",","|",$addcategory);
							$addcategoryar = @explode("|",$addcategory);
							$category = trim(end($addcategoryar));//무조건 맨마지막 대표카테고리 추출
							if( empty($category) ) $category = trim($addcategoryar[count($addcategoryar)-2]);
							if($category) {
								$this->update_goods_addcategory($goods_seq, $addcategory, $category);//
							}else{
								if($goods_seq){//수정시 카테고리가 없는 경우 초기화함
									$this->db->delete('fm_category_link ', array('goods_seq' => $goods_seq));
								}
							}
						}

						### BRAND
						if($tab['cg_brand']){
							unset($addbrand, $addbrand);
							$addbrand = $objWorksheet->getCell($tab['cg_brand'].$i)->getValue();
							$addbrand = trim($addbrand);
							$addbrand = str_replace(",","|",$addbrand);
							$addbrandar = @explode("|",$addbrand);
							$brand = trim(end($addbrandar));//무조건 맨마지막 대표카테고리 추출
							if( empty($brand) ) $brand = trim($addbrandar[count($addbrandar)-2]);
							if($brand) {
								$this->update_goods_addbrand($goods_seq, $addbrand, $brand);
							}else{
								if($goods_seq){//수정시 브랜드가 없는 경우 초기화함
									$this->db->delete('fm_brand_link ', array('goods_seq' => $goods_seq));
								}
							}
						}

						### FM_GOODS_IMAGE
						unset($params);
						if($tab['large']){
							$params['large']		= $objWorksheet->getCell($tab['large'].$i)->getValue();
							$params['view']			= $objWorksheet->getCell($tab['view'].$i)->getValue();
							$params['list1']		= $objWorksheet->getCell($tab['list1'].$i)->getValue();
							$params['list2']		= $objWorksheet->getCell($tab['list2'].$i)->getValue();
							$params['thumbView']	= $objWorksheet->getCell($tab['thumbView'].$i)->getValue();
							$params['thumbCart']	= $objWorksheet->getCell($tab['thumbCart'].$i)->getValue();
							$params['thumbScroll']	= $objWorksheet->getCell($tab['thumbScroll'].$i)->getValue();
							$this->update_goods_image($goods_seq, $params);
						}

						### FM_GOODS_ADDITION
						unset($params);
						if($tab['model']) $params['model']	= trim(($objWorksheet->getCell($tab['model'].$i)->getValue()));
						if($tab['brand']) $params['brand']	= trim(($objWorksheet->getCell($tab['brand'].$i)->getValue()));
						if($tab['manufacture']) $params['manufacture']	= trim(($objWorksheet->getCell($tab['manufacture'].$i)->getValue()));
						if($tab['orgin']) $params['orgin']	= trim(($objWorksheet->getCell($tab['orgin'].$i)->getValue()));
						$this->update_goods_addition($goods_seq, $params, $tab);

						###
						$this->goods_option_replace_into('fm_goods_option_tmp', 'fm_goods_option', $goods_seq , 'option_seq');
						$this->goods_option_replace_into('fm_goods_suboption_tmp', 'fm_goods_suboption', $goods_seq , 'suboption_seq');

						$result = $this->db->delete('fm_goods_option', array('goods_seq' => $goods_seq));
						$result = $this->db->delete('fm_goods_suboption', array('goods_seq' => $goods_seq));
						$result = $this->db->delete('fm_goods_input', array('goods_seq' => $goods_seq));
						$result = $this->db->delete('fm_goods_supply', array('goods_seq' => $goods_seq));
						$goods_arr[] = $goods_seq;
						$temp_seq	= $goods_seq;
						$count		= 0;

						$sucess++;
					}else{

						if($temp_seq){

							### FM_GOODS_OPTION && FM_GOODS_SUBOPTION
							unset($params);
							$params['option_seq']			= $objWorksheet->getCell($tab['option_seq'].$i)->getValue();
							$params['option_price']		= $objWorksheet->getCell($tab['option_price'].$i)->getValue();
							$params['reserve_rate']		= $objWorksheet->getCell($tab['reserve_rate'].$i)->getValue();
							$params['reserve_unit']		= $objWorksheet->getCell($tab['reserve_unit'].$i)->getValue();
							if( $this->price_unit_title[$params['reserve_unit']] )
								$params['reserve_unit']	= $this->price_unit_title[$params['reserve_unit']];

							$params['reserve']		= $objWorksheet->getCell($tab['reserve'].$i)->getValue();

							$params['title1']		= trim($objWorksheet->getCell($tab['title1'].$i)->getValue());
							$params['title2']		= trim($objWorksheet->getCell($tab['title2'].$i)->getValue());
							$params['title3']		= trim($objWorksheet->getCell($tab['title3'].$i)->getValue());
							$params['title4']		= trim($objWorksheet->getCell($tab['title4'].$i)->getValue());
							$params['title5']		= trim($objWorksheet->getCell($tab['title5'].$i)->getValue());
							$params['option1']		= trim($objWorksheet->getCell($tab['option1'].$i)->getValue());
							$params['option2']		= trim($objWorksheet->getCell($tab['option2'].$i)->getValue());
							$params['option3']		= trim($objWorksheet->getCell($tab['option3'].$i)->getValue());
							$params['option4']		= trim($objWorksheet->getCell($tab['option4'].$i)->getValue());
							$params['option5']		= trim($objWorksheet->getCell($tab['option5'].$i)->getValue());
							$params['infomation']		= $objWorksheet->getCell($tab['infomation'].$i)->getValue();

							$params['subrequired']	= $objWorksheet->getCell($tab['subrequired'].$i)->getValue();
							if( $this->require_title[$params['subrequired']] ){
								$params['subrequired']	= $this->require_title[$params['subrequired']];
							}else{
								$params['subrequired']	= 'n';
							}

							$params['subtitle']		= trim(($objWorksheet->getCell($tab['subtitle'].$i)->getValue()));
							$params['suboption']	= $objWorksheet->getCell($tab['suboption'].$i)->getValue();

							$params['input_name']	= trim(($objWorksheet->getCell($tab['input_name'].$i)->getValue()));
							$params['input_form']		= $objWorksheet->getCell($tab['input_form'].$i)->getValue();
							if( $this->input_form_title[$params['input_form']] )
								$params['input_form']	= $this->input_form_title[$params['input_form']];

							$params['input_limit']		= $objWorksheet->getCell($tab['input_limit'].$i)->getValue();
							$params['input_require']	= $objWorksheet->getCell($tab['input_require'].$i)->getValue();

							if( $this->require_number_title[$params['input_require']] ) {
								$params['input_require']	= $this->require_number_title[$params['input_require']];
							}else{
								$params['input_require']	= '0';
							}

							$params['stock']	= $objWorksheet->getCell($tab['stock'].$i)->getValue();
							//$params['commission_rate']	= $objWorksheet->getCell($tab['commission_rate'].$i)->getValue();
							$params['supply_price']	= $objWorksheet->getCell($tab['supply_price'].$i)->getValue();
							$params['consumer_price']	= $objWorksheet->getCell($tab['option_consumer'].$i)->getCalculatedValue();//계산식일경우(정가)

							foreach($params as $k=>$v) {
								if(is_null($v)) $params[$k] = '';
								if (is_object($params[$k])) {//excel셀서식적용시실제값가져오기
									$objRichText = new PHPExcel_RichText($objWorksheet->getCell($tab[$k].$i));
									$params[$k] = $objRichText->getPlainText();
								}
							}
							### OPTION
							if( $params['option_price'] >= 0 ) {
								if($params['title1']){
									if($count==0) $default = "y";
									else $default = "n";
									$this->update_goods_option($temp_seq, $params, $default);
									$count++;
								}else{
									if($count==0) {
										$this->update_goods_opt($temp_seq, $params);
									}
								}

								if($params['subtitle']){
									$this->update_goods_suboption($temp_seq, $params);
									$count++;
								}
							}

							if($params['input_name']){
								$this->update_goods_subinputoption($temp_seq, $params);
								$count++;
							}

						}
					}

				}

				/* 총재고 수량 입력 */
				foreach($goods_arr as $goods_seq){
					$this->goodsmodel->total_stock($goods_seq);
				}

				// 다중판매처 관련 처리 추가
				/*
				$this->load->model('openmarketmodel');
				if	($goods_arr)foreach($goods_arr as $g => $goods_seq){
					if	($goods_seq){
						$this->openmarketmodel->chg_price_to_option($goods_seq);
						$this->openmarketmodel->request_send_goods($goods_seq);
					}
				}
				*/

				if($sucess>0){
					$data['result']	= true;
					$data['count']	= $sucess;
					//$data['msg']	= $sucess.'건 수정 되었습니다.';
					###
					$this->goods_data_check($goods_arr);
					$data['msg']	= '처리 되었습니다.';
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




	### GET
	public function get_goods_image($goods_seq, $image_type){
		$datas = get_data("fm_goods_image",array("goods_seq"=>$goods_seq,'image_type'=>$image_type));
		$result = ($datas)? $datas[0]['image']:'';
		return $result;
	}

	public function get_goods_image_all($goods_seq, $image_type){
		$datas = get_data("fm_goods_image",array("goods_seq"=>$goods_seq));//,'cut_number'=>1
		foreach ($datas as $row){
			$r_data[$row['image_type']][] = $row['image'];
		}
		if($r_data)  foreach($r_data as $k => $imagear){
				$result[$k] =  implode('|',$imagear);
		}
		return $result;
	}

	public function get_goods_addition($goods_seq, $type){
		$datas = get_data("fm_goods_addition",array("goods_seq"=>$goods_seq,'type'=>$type));
		$result = ($datas)? $datas[0]['contents']:'';
		return $result;
	}

	public function get_goods_addition_all($goods_seq){
		$datas = get_data("fm_goods_addition",array("goods_seq"=>$goods_seq));
		foreach ($datas as $row){
			$result[$row['type']] = $row['contents'];
		}
		return $result;
	}

	public function get_goods_relation($goods_seq){
		$this->db->where('goods_seq', $goods_seq);
		$query = $this->db->get("fm_goods_relation");
		if($query) foreach ($query->result_array() as $row){
			$loop[] = $row['relation_goods_seq'];
		}
		$relation = ($loop)? implode("|",$loop):'';
		return $relation;
	}

	//카테고리
	public function get_goods_category($goods_seq){
		$datas = get_data("fm_category_link",array("goods_seq"=>$goods_seq));
		if($datas){
			foreach ($datas as $row){ $num++;
				if($row['link'] == 1 ) {
					$category = $row['category_code'];
				}else{
					$result[] = $row['category_code'];
				}
			}
		}

		if( $num == 1 ) {
			$result_category = ($result)?implode("|",$result):$category;
		}else{
			$result_category = ($category)?implode("|",$result).'|'.$category:implode("|",$result);
		}
		return $result_category;
	}

	//브랜드
	public function get_goods_brand($goods_seq){
		$datas = get_data("fm_brand_link",array("goods_seq"=>$goods_seq));
		if($datas){
			foreach ($datas as $row){ $num++;
				if($row['link'] == 1 ) {
					$brand = $row['category_code'];
				}else{
					$result[] = $row['category_code'];
				}
			}
		}

		if( $num == 1 ) {
			$result_brand = ($result)?implode("|",$result):$brand;
		}else{
			$result_brand = ($brand)?implode("|",$result).'|'.$brand:implode("|",$result);
		}

		return $result_brand;
	}

	//카테고리
	public function get_goods_category_sort($goods_seq){
		$datas = get_data("fm_category_link",array("goods_seq"=>$goods_seq));
		if($datas){
			foreach ($datas as $row){
				$resultsort[$row['category_code']] = $row['sort'];
			}
		}
		return $resultsort;
	}

	//브랜드
	public function get_goods_brand_sort($goods_seq ){
		$datas = get_data("fm_brand_link",array("goods_seq"=>$goods_seq));
		if($datas){
			foreach ($datas as $row){
				$resultsort[$row['category_code']] = $row['sort'];
			}
		}
		return $resultsort;
	}

	public function get_goods_option($goods_seq, $reserve_policy){

		if( $reserve_policy == "shop" || $reserve_policy == "기본" ) {
			$tmp = config_load('reserve','default_reserve_percent');
			$default_reserve_percent = $tmp['default_reserve_percent'];
		}

		$sql = "SELECT distinct A.*, B.* FROM fm_goods_option A LEFT JOIN fm_goods_supply B ON A.option_seq = B.option_seq WHERE A.goods_seq = '{$goods_seq}' AND B.goods_seq = '{$goods_seq}' AND B.option_seq is not null;";
		$query = $this->db->query($sql);
		if($query) foreach ($query->result_array() as $row){
			if( $default_reserve_percent ) {//기본정책인경우
				$row['reserve_rate'] = $default_reserve_percent;
				$row['reserve_unit '] = 'percent';
			}
			$loop[] = $row;
		}
		return $loop;
	}

	public function get_goods_suboption($goods_seq, $reserve_policy){
		if( $reserve_policy == "shop" || $reserve_policy == "기본" ) {
			$tmp = config_load('reserve','default_reserve_percent');
			$default_reserve_percent = $tmp['default_reserve_percent'];
		}

		$sql = "SELECT A.suboption_seq, A.*, B.* FROM fm_goods_suboption A LEFT JOIN fm_goods_supply B ON A.suboption_seq = B.suboption_seq WHERE A.goods_seq = '{$goods_seq}' AND B.goods_seq = '{$goods_seq}' AND B.suboption_seq is not null;";
		$query = $this->db->query($sql);
		if($query) foreach ($query->result_array() as $row){
			if( $default_reserve_percent ) {//기본정책인경우
				$row['reserve_rate'] = $default_reserve_percent;
				$row['reserve_unit '] = '%';
			}
			$loop[] = $row;
		}
		return $loop;
	}


	public function get_goods_subinputoption($goods_seq){
		$sql = "SELECT *, input_name as input_name   FROM fm_goods_input WHERE goods_seq = '{$goods_seq}' ";
		$query = $this->db->query($sql);
		if($query) foreach ($query->result_array() as $row){
			$loop[] = $row;
		}
		return $loop;
	}

	### UPDATE
	public function update_goods($goods_seq, $params, $tab ){
		$ckgoods = $this->goodsmodel->get_goods($goods_seq);
		if(empty($ckgoods)) return false;

		if(isset($params['goods_name']))				$goods['goods_name'] = ($params['goods_name']);
		$goods['goods_code']						= (isset($params['goods_code']))?$params['goods_code']:'';
		if( $tab['summary'] ){
			$goods['summary']							= (isset($params['summary']))?$params['summary']:'';
		}

		if( $tab['keyword'] ){
			$goods['keyword']							= (isset($params['keyword']))?$params['keyword']:'';
		}

		if( $tab['contents'] ){
			$goods['contents']							= (isset($params['contents']))?$params['contents']:'';
		}

		if( $tab['mobile_contents'] ){
			$goods['mobile_contents']				= (isset($params['mobile_contents']))?$params['mobile_contents']:'';
		}

		if( $tab['tax'] ){
			$goods['tax']									= (isset($params['tax']))?$params['tax']:'tax';
		}

		if( $tab['info_name'] ){
			$goods['info_seq']							= (isset($params['info_name']))?$params['info_name']:0;
		}

		if( $tab['goods_sub_info'] ){
			$goods['goods_sub_info']				= (isset($params['goods_sub_info']))?$params['goods_sub_info']:NULL;
		}

		if( $tab['sub_info_desc'] ){
			$goods['sub_info_desc']					= (isset($params['sub_info_desc']))?json_encode($params['sub_info_desc']):'';
		}

		//필수항목
		$goods['option_use']						= (isset($params['option_use']))?$params['option_use']:'0';
		$goods['option_suboption_use']		= (isset($params['option_suboption_use']))?$params['option_suboption_use']:'0';
		$goods['member_input_use']			= (isset($params['member_input_use']))?$params['member_input_use']:'0';
		if(strlen(trim($params['option_view_type']))>2){
			$goods['option_view_type']			= $params['option_view_type'];
		}else{
			if(isset($params['option_use'])=="1")		$goods['option_view_type'] = 'divide';
		}
		$goods['reserve_policy']					= (isset($params['reserve_policy']))?$params['reserve_policy']:'shop';

		if( $tab['goods_status'] ){
			$goods['goods_status']					= (isset($params['goods_status']))?$params['goods_status']:'normal';
		}

		if( $tab['goods_view'] ){
			$goods['goods_view']						= (isset($params['goods_view']))?$params['goods_view']:'notLook';
		}

		if( $tab['purchase_goods_name'] ){
			$goods['purchase_goods_name']	= (isset($params['purchase_goods_name']))?$params['purchase_goods_name']:'';
		}

		if( $tab['string_price'] ){
			$goods['string_price']						= (isset($params['string_price']))?$params['string_price']:'';
			$goods['string_price_use'] = (isset($params['string_price_use']))?$params['string_price_use']:'0';
		}

		/*
		if( $tab['string_price_use'] ){
			$goods['string_price_use']				= (isset($params['string_price_use']))?$params['string_price_use']:'0';
		}
		*/

		if( $tab['multi_discount'] ){
			$goods['multi_discount']					= (isset($params['multi_discount']))?$params['multi_discount']:0;
			$goods['multi_discount_use']			= (isset($params['multi_discount_use']))?$params['multi_discount_use']:'0';
			$goods['multi_discount_unit']			= (isset($params['multi_discount_unit']))?$params['multi_discount_unit']:''; //won/percent
		}

		if( $tab['multi_discount_ea'] ){
			$goods['multi_discount_ea']			= (isset($params['multi_discount_ea']))?$params['multi_discount_ea']:0;
		}

		if( $tab['min_purchase_ea'] ){
			$goods['min_purchase_ea']				= (isset($params['min_purchase_ea']))?$params['min_purchase_ea']:0;
			$goods['min_purchase_limit']			= (isset($params['min_purchase_limit']))?$params['min_purchase_limit']:'unlimit';
		}

		if( $tab['max_purchase_ea'] ){
			$goods['max_purchase_ea']			= (isset($params['max_purchase_ea']))?$params['max_purchase_ea']:0;
			$goods['max_purchase_limit']			= (isset($params['max_purchase_limit']))?$params['max_purchase_limit']:'unlimit';
		}

		if( $tab['unlimit_shipping_price'] ) {
			if(isset($params['unlimit_shipping_price']) =='0') {
				$goods['unlimit_shipping_price'] = 0;
			}else{
				$goods['unlimit_shipping_price'] = $params['unlimit_shipping_price'];
			}
			$goods['shipping_policy']					= (isset($params['shipping_policy']))?$params['shipping_policy']:'shop';
			$goods['goods_shipping_policy']		= (isset($params['goods_shipping_policy']))?$params['goods_shipping_policy']:'unlimit';
		}

		if( $tab['goods_weight'] ) {
			$goods['goods_weight']					= (isset($params['goods_weight']))?$params['goods_weight']:'';
		}

		if( $tab['sale_seq'] ) {
			$goods['sale_seq']					= (isset($params['sale_seq']))?$params['sale_seq']:'';
		}

		if( $tab['relation_seq'] ) {
			$relationcnt=0;
			if($params['relation_seq']){
				$result = $this->db->delete('fm_goods_relation', array('goods_seq' => $goods_seq));
				$relation_arr = explode("|", $params['relation_seq']);
				foreach($relation_arr as $k){
					if(empty($k)) continue;
					$ckrelation = $this->goodsmodel->get_goods($k);
					if(empty($ckrelation)) continue;
					$relationcnt++;
					$result = $this->db->insert('fm_goods_relation', array("goods_seq"=>$goods_seq,"relation_goods_seq"=>$k));
				}
			}

			if($relationcnt>0){
				$goods['relation_type'] = 'MANUAL';//직접선정
			}else{
				$goods['relation_type'] = 'AUTO';//자동선정
				$result = $this->db->delete('fm_goods_relation', array('goods_seq' => $goods_seq));
			}

		}

		$goods['update_date'] = date("Y-m-d H:i:s",time());
        $this->db->where('goods_seq', $goods_seq);
		$this->db->update('fm_goods', $goods);

	}
	### INSERT
	public function insert_goods($params) {
		if(isset($params['goods_code']))				$goods['goods_code'] = $params['goods_code'];
		if(isset($params['goods_name']))				$goods['goods_name'] = ($params['goods_name']);
		if(isset($params['summary']))					$goods['summary'] = $params['summary'];
		if(isset($params['keyword']))						$goods['keyword'] = $params['keyword'];
		if(isset($params['contents']))						$goods['contents'] = $params['contents'];
		if(isset($params['mobile_contents']))		$goods['mobile_contents'] = $params['mobile_contents'];
		if(isset($params['tax']))								$goods['tax'] = $params['tax'];
		if(isset($params['info_name']))					$goods['info_seq'] = $params['info_name'];
		if(isset($params['goods_sub_info']))			$goods['goods_sub_info'] = $params['goods_sub_info'];
		if(isset($params['sub_info_desc']))			$goods['sub_info_desc'] =  json_encode($params['sub_info_desc']);
		if(isset($params['option_use']))						$goods['option_use'] = $params['option_use'];
		if(isset($params['option_suboption_use']))	$goods['option_suboption_use'] = $params['option_suboption_use'];
		if(isset($params['member_input_use']))		$goods['member_input_use'] = $params['member_input_use'];

		if(strlen(trim($params['option_view_type']))>2){
			$goods['option_view_type']			= $params['option_view_type'];
		}else{
			if(isset($params['option_use'])=="1")		$goods['option_view_type'] = 'divide';
		}
		if(isset($params['reserve_policy']))				$goods['reserve_policy'] = $params['reserve_policy'];
		if(isset($params['goods_status']))					$goods['goods_status'] = $params['goods_status'];
		if(isset($params['goods_view']))					$goods['goods_view'] = $params['goods_view'];
		if(isset($params['purchase_goods_name'])) $goods['purchase_goods_name'] = $params['purchase_goods_name'];
		if(isset($params['string_price']))					$goods['string_price'] = $params['string_price'];
		if(isset($params['string_price_use']))			$goods['string_price_use'] = $params['string_price_use'];
		if(isset($params['multi_discount']))				$goods['multi_discount'] = $params['multi_discount'];
		if(isset($params['multi_discount_use']))		$goods['multi_discount_use'] = $params['multi_discount_use'];
		if(isset($params['multi_discount_ea']))			$goods['multi_discount_ea'] = $params['multi_discount_ea'];
		if(isset($params['multi_discount_unit']))		$goods['multi_discount_unit'] = $params['multi_discount_unit'];//won/percent
		if(isset($params['min_purchase_ea']))			$goods['min_purchase_ea'] = $params['min_purchase_ea'];
		if(isset($params['min_purchase_limit']))		$goods['min_purchase_limit'] = $params['min_purchase_limit'];
		if(isset($params['max_purchase_ea']))			$goods['max_purchase_ea'] = $params['max_purchase_ea'];
		if(isset($params['max_purchase_limit']))		$goods['max_purchase_limit'] = $params['max_purchase_limit'];

		if(isset($params['unlimit_shipping_price']) =='0') {
			$goods['unlimit_shipping_price'] = 0;
		}else{
			$goods['unlimit_shipping_price'] = $params['unlimit_shipping_price'];
		}

		if(isset($params['shipping_policy']))				$goods['shipping_policy'] = $params['shipping_policy'];
		if(isset($params['goods_shipping_policy']))	$goods['goods_shipping_policy'] = $params['goods_shipping_policy'];
		if(isset($params['goods_weight']))				$goods['goods_weight'] = $params['goods_weight'];

		// 회원등급혜택
		$goods['sale_seq'] = 1;
		if(isset($params['sale_seq']))				$goods['sale_seq'] = $params['sale_seq'];//회원그룹할인

		$relationcnt=0;
		if($params['relation_seq']){
			$relation_arr = explode("|", $params['relation_seq']);
			foreach($relation_arr as $k){
				if(empty($k)) continue;
				$relationcnt++;
			}
		}

		if($relationcnt>0){
			$goods['relation_type'] = 'MANUAL';//직접선정
		}else{
			$goods['relation_type'] = 'AUTO';//자동선정
		}	

		$goods['regist_date'] = $goods['update_date'] = date("Y-m-d H:i:s",time());
		$result = $this->db->insert('fm_goods', $goods);
		$goods_seq = $this->db->insert_id();
		//debug_var($goods);
		//debug_var($goods_seq." insert_goods()->".$this->db->last_query());

		$relationcnt=0;
		if($params['relation_seq']){
			$result = $this->db->delete('fm_goods_relation', array('goods_seq' => $goods_seq));
			$relation_arr = explode("|", $params['relation_seq']);
			foreach($relation_arr as $k){
				if(empty($k)) continue;
				$ckrelation = $this->goodsmodel->get_goods($k);
				if(empty($ckrelation)) continue;
				$relationcnt++;
				$result = $this->db->insert('fm_goods_relation', array("goods_seq"=>$goods_seq,"relation_goods_seq"=>$k));
			}
		}

		return $goods_seq;
	}




	public function update_goods_image($goods_seq, $params){
		$r_target = array('large','view','list1','list2','thumbView','thumbCart','thumbScroll');
		foreach($r_target as $target){
			if($params[$target]){
				$result = $this->db->delete('fm_goods_image', array('goods_seq'=>$goods_seq,"image_type"=>$target));
				$arr = explode('|',$params[$target]);
				foreach($arr as $k => $image){
					$cut_number = $k+1;
					$result = $this->db->insert('fm_goods_image', array("goods_seq"=>$goods_seq,"image"=>$image,"image_type"=>$target,"cut_number"=>$cut_number));
				}
			}
		}
	}

	public function update_goods_addition($goods_seq, $params, $tab ){
		if( $tab['model'] ) {
			if(($params['model'])){
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"model"));
				$result = $this->db->insert('fm_goods_addition', array("goods_seq"=>$goods_seq,"contents"=>$params['model'],"type"=>"model"));
			}else{
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"model"));
			}
		}
		if( $tab['brand'] ) {
			if(($params['brand'])){
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"brand"));
				$result = $this->db->insert('fm_goods_addition', array("goods_seq"=>$goods_seq,"contents"=>$params['brand'],"type"=>"brand"));
			}else{
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"brand"));
			}
		}
		if( $tab['manufacture'] ) {
			if(($params['manufacture'])){
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"manufacture"));
				$result = $this->db->insert('fm_goods_addition', array("goods_seq"=>$goods_seq,"contents"=>$params['manufacture'],"type"=>"manufacture"));
			}else{
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"manufacture"));
			}

		}
		if( $tab['orgin'] ) {
			if(($params['orgin'])){
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"orgin"));
				$result = $this->db->insert('fm_goods_addition', array("goods_seq"=>$goods_seq,"contents"=>$params['orgin'],"type"=>"orgin"));
			}else{
				$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goods_seq,"type"=>"orgin"));
			}
		}
	}



	public function update_goods_opt($goods_seq, $params){
		$sql = "SELECT * FROM fm_goods_option WHERE goods_seq = '{$goods_seq}'";
		$query = $this->db->query($sql);
		if( $query->num_rows()<1 ){
			if(!$params['consumer_price']) $params['consumer_price'] = 0;
			if(!$params['stock']) $params['stock'] = 0;
			$params['reserve']	= $params['reserve_unit']=='percent' ? floor($params['option_price']*$params['reserve_rate'] / 100) : $params['reserve_rate'];

			$params['reserve']		= ($params['reserve'])?$params['reserve']:0;

			$result = $this->db->insert('fm_goods_option', array(
				"goods_seq"=>$goods_seq,
				"default_option"=>"y",
				"consumer_price "=>$params['consumer_price'],
				"price"=>$params['option_price'],
				"reserve_rate"=>$params['reserve_rate'],
				"reserve_unit"=>$params['reserve_unit'],
				"reserve"=>$params['reserve'],
				"infomation"=>$params['infomation']
				));

			//debug_var($goods_seq." update_goods_opt()->".$this->db->last_query());

			$opt['option_seq']		= $this->db->insert_id();

			$opt['goods_seq']		= $goods_seq;
			$opt['supply_price']	= $params['supply_price'];
			$opt['stock']			= $params['stock'];
			$result = $this->db->insert('fm_goods_supply', $opt);

			/* 총재고 수량 입력 */
			//$this->goodsmodel->total_stock($goods_seq);
			//debug_var($goods_seq." update_goods_opt()->".$this->db->last_query());

		}
	}

	public function update_goods_option($goods_seq, $params, $default){
		$temp[] = $params['title1'];
		if(($params['title2'])) $temp[] = $params['title2'];
		if(($params['title3'])) $temp[] = $params['title3'];
		if(($params['title4'])) $temp[] = $params['title4'];
		if(($params['title5'])) $temp[] = $params['title5'];
		$option_title = implode(",",$temp);

		$data['goods_seq']		= $goods_seq;
		$data['default_option']	= $default;
		if( defined('__SELLERADMIN__') === true ) {//입점사 수수료율수정불가
		}else{
			$data['commission_rate']	= $params['commission_rate'];
			if(!$data['commission_rate']) $data['commission_rate'] = 0;
		}

		$data['consumer_price']	= $params['consumer_price'];
		$data['price']			= $params['option_price'];
		$data['reserve_rate']	= $params['reserve_rate'];
		$data['reserve_unit']	= $params['reserve_unit'];
		$params['reserve']	= ($data['reserve_unit']=="percent") ? floor($data['price']*$data['reserve_rate'] / 100) : $data['reserve_rate'];
		$data['reserve']		= ($params['reserve'])?$params['reserve']:0;

		$data['option_title']	= $option_title;
		$data['option1']		= $params['option1'];
		if(!$data['option1'])  $data['option1']		= "";
		if(($params['option2'])) $data['option2']		= $params['option2'];
		if(($params['option3'])) $data['option3']		= $params['option3'];
		if(($params['option4'])) $data['option4']		= $params['option4'];
		if(($params['option5'])) $data['option5']		= $params['option5'];
		$data['infomation'] = $params['infomation'];

		//백업정보 가져오기
		$this->get_goods_option_tmp('fm_goods_option_tmp', $goods_seq , $params['option_seq'] , $data);

		$data = filter_keys($data, $this->db->list_fields('fm_goods_option'));//필드점검
		$result = $this->db->insert('fm_goods_option', $data);
		//debug_var($goods_seq." update_goods_option()->".$this->db->last_query());

		if(!$params['stock']) $params['stock'] = 0;
		$opt['option_seq']		= $this->db->insert_id();
		$opt['goods_seq']		= $goods_seq;
		$opt['supply_price']	= $params['supply_price'];
		$opt['stock']			= $params['stock'];
		$result = $this->db->insert('fm_goods_supply', $opt);
		//debug_var($goods_seq." update_goods_option()->".$this->db->last_query());
	}


	public function update_goods_suboption($goods_seq, $params){
		if(!$params['commission_rate']) $params['commission_rate'] = 0;
		if(!$params['consumer_price']) $params['consumer_price'] = 0;
		if(!$params['stock']) $params['stock'] = 0;
		if(!$params['reserve']) $params['reserve'] = 0;
		if(!$params['reserve_rate']) $params['reserve_rate'] = 0;
		if(!$params['reserve_unit']) $params['reserve_unit'] = 'percent';
		$params['reserve']	= ($params['reserve_unit']=="percent") ? floor($params['option_price']*$params['reserve_rate'] / 100) : $params['reserve_rate'];

		$data['goods_seq']			= $goods_seq;
		$data['sub_required']		= $params['subrequired'];
		$data['suboption_title']	= $params['subtitle'];
		$data['suboption']			= $params['suboption'];
		$data['consumer_price']	= $params['consumer_price'];
		$data['price']					= $params['option_price'];
		$data['reserve_rate']		= $params['reserve_rate'];
		$data['reserve_unit']		= $params['reserve_unit'];
		$data['reserve']				= $params['reserve'];

		//백업정보 가져오기
		$this->get_goods_option_tmp('fm_goods_suboption_tmp', $goods_seq , $params['option_seq'] , $data);

		$data = filter_keys($data, $this->db->list_fields('fm_goods_suboption'));//필드점검
		$result = $this->db->insert('fm_goods_suboption', $data);
		//debug_var($goods_seq." update_goods_suboption()->".$this->db->last_query());

		$opt['suboption_seq']		= $this->db->insert_id();
		$opt['goods_seq']		= $goods_seq;
		$opt['supply_price']	= $params['supply_price'];
		$opt['stock']			= $params['stock'];
		$result = $this->db->insert('fm_goods_supply', $opt);
	}

	public function update_goods_subinputoption($goods_seq, $params){

		$form = array('text','edit','file');
		$inputs['goods_seq'] 		= $goods_seq;
		$inputs['input_name'] 		= $params['input_name'];
		$inputs['input_form']		= (in_array($params['input_form'],$form))?$params['input_form']:'text';
		$inputs['input_limit'] 		= $params['input_limit'];
		$inputs['input_require']	= ( $params['input_require']=='1' )?'1':'0';
		$result = $this->db->insert('fm_goods_input', $inputs);

		//debug_var($goods_seq." update_goods_subinputoption()->".$this->db->last_query());
	}


	public function goods_data_check($goods_arr){
		foreach($goods_arr as $v){
			$sql = "SELECT * FROM fm_goods_option WHERE goods_seq = '{$v}'";
			$query = $this->db->query($sql);
			if( $query->num_rows()<1 ){
				$temp['goods_seq'] = $v;
				$temp['default_option'] = 'y';
				$temp['consumer_price'] = 100;
				$temp['price'] = 100;
				$temp['reserve'] = 0;
				$result = $this->db->insert('fm_goods_option', $temp);
				unset($temp);
			}
		}
	}

	//추가카테고리 대표카테고리 포함
	public function update_goods_addcategory($goods_seq, $addcategory, $category=null) {
		### CATEGORY --> 맨마지막이 대표로 처리됨
		$oldcategory = $this->get_goods_category_sort($goods_seq);
		$this->db->delete('fm_category_link ', array('goods_seq' => $goods_seq));
		$addcategory = str_replace(",","|",$addcategory);
		$addcategoryar = explode("|",$addcategory);
		foreach($addcategoryar as $add){
			$len = round(strlen($add)/4) + 1;
			for($i=1;$i<$len;$i++){
				$cnt = $i * 4;
				$cates = substr($add,0,$cnt);

				$sql = "SELECT * FROM fm_category_link WHERE goods_seq = '{$goods_seq}' AND category_code = '{$cates}'";
				$query = $this->db->query($sql);
				$cate_chk = $query->result_array();
				if(!$cate_chk){
					unset($data);
					$data['goods_seq']			= $goods_seq;
					$data['category_code']	= $cates;
					$data['link']						= ($category==$cates) ? '1' : '0';
					$data['sort']						= (!empty($oldcategory[$cates]))?$oldcategory[$cates]:0;
					$data['regist_date']	= date("Y-m-d H:i:s");
					$result = $this->db->insert('fm_category_link', $data);
					if($result && $data['sort'] == 0 && empty($oldcategory[$cates])) {//신규만
						$link_seq = $this->db->insert_id();
						$this->db->where('category_link_seq', $link_seq);
						$this->db->update('fm_category_link',array('sort'=>$link_seq));
					}
				}
			}
		}
	}

	//추가브랜드 , 대표브랜드포함
	public function update_goods_addbrand($goods_seq, $addcategory, $category=null){

		### BRAND --> 맨마지막이 대표로 처리됨
		$old_cg_brand			= $this->get_goods_brand_sort($goods_seq);
		$this->db->delete('fm_brand_link ', array('goods_seq' => $goods_seq));
		$addcategory = str_replace(",","|",$addcategory);
		$addcategoryar = explode("|",$addcategory);
		foreach($addcategoryar as $add){
			$len = round(strlen($add)/4) + 1;
			for($i=1;$i<$len;$i++){
				$cnt = $i * 4;
				$cates = substr($add,0,$cnt);

				$sql = "SELECT * FROM fm_brand_link WHERE goods_seq = '{$goods_seq}' AND category_code = '{$cates}'";
				$query = $this->db->query($sql);
				$cate_chk = $query->result_array();
				if(!$cate_chk){
					unset($data);
					$data['goods_seq']			= $goods_seq;
					$data['category_code']	= $cates;
					$data['link']						= ($category==$cates) ? '1' : '0';
					$data['sort']						= ( !empty($old_cg_brand[$cates]) )?$old_cg_brand[$cates]:0;
					$data['regist_date']	= date("Y-m-d H:i:s");
					$result = $this->db->insert('fm_brand_link', $data);
					if($result && $data['sort'] == 0 && empty($old_cg_brand[$cates])) {//신규만
						$link_seq = $this->db->insert_id();
						$this->db->where('category_link_seq', $link_seq);
						$this->db->update('fm_brand_link',array('sort'=>$link_seq));
					}
				}
			}
		}
	}


}
/* End of file excelgoods.php */
/* Location: ./app/models/excelgoods */