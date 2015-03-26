<?php
/**
* 상품코드 자동등록처리
**/
function goodscodeautock($no, $mode=null){
	if(!$no) return;

	$CI =& get_instance();
	if( SERVICE_CODE == 'P_FREE' ) return;//무료몰불가

	$CI->load->model('goodsmodel');
	$additions = $CI->goodsmodel->get_goods_addition($no);
	$categoriesdefault = $CI->goodsmodel->get_goods_category_default($no); 
	$categories = $CI->goodsmodel->get_goods_category($no);
	if($categories){
		foreach($categories as $key=>$category){
			$tmpCode = substr($categoriesdefault['category_code'],0,strlen($category['category_code'])); 
			if($tmpCode == $category['category_code'] ) $categorycode[] = $category['category_goods_code'];
		}
	}
	$brandsdefault = $CI->goodsmodel->get_goods_brand_default($no);
	$brands = $CI->goodsmodel->get_goods_brand($no);
	if($brands){
		foreach($brands as $key=>$brand){
			$tmpCode = substr($brandsdefault['category_code'],0,strlen($brand['category_code'])); 
			if($tmpCode == $brand['category_code'] ) $brandcode[] = $brand['brand_goods_code'];
		}
	}

	$qry = "select * from fm_goods_code_form  where label_type ='goodsaddinfo'  and codesetting=1 order by sort_seq";
	$query = $CI->db->query($qry);
	$user_arr = $query -> result_array();
	foreach ($user_arr as $datarow){
		if( $datarow['label_code'] == 'goods_seq|' ){//상품고유번호
			$returncode[] = $no;
		}elseif( $datarow['label_code'] == 'category|' ){
			$returncode[] = implode("",$categorycode);
		}elseif( $datarow['label_code'] == 'brand|' ){
			$returncode[] = implode("",$brandcode);
		}else{
			foreach($additions as $addition){
				if($addition['code_seq'] == $datarow['codeform_seq']) {
					$returncode[] = $addition['contents'];
				}
			}//endforeach
		}//endif
	}//endforeach
	if($returncode){
		if($mode == 'batch'){
			return implode('',$returncode);
		}else{
		$CI->db->where('goods_seq', $no);
		$CI->db->update('fm_goods', array('goods_code'=>implode('',$returncode)));
	}
	}
}

function goodscodeautockview(){
	if( SERVICE_CODE == 'P_FREE' ) return;//무료몰불가

	$CI =& get_instance();
	$CI->load->model('goodsmodel');
	$CI->load->model('categorymodel');
	$CI->load->model('brandmodel');

	$no	= ($_POST['no'])? $_POST['no']:'';
	$category_goods_code	= ($_POST['category_goods_code'])? $_POST['category_goods_code']:'';
	$brand_goods_code		= ($_POST['brand_goods_code'])? $_POST['brand_goods_code']:'';
	$addtion_goods_seq		= ($_POST['addtion_goods_seq'])? $_POST['addtion_goods_seq']:'';
	$addtion_goods_code		= ($_POST['addtion_goods_code'])? $_POST['addtion_goods_code']:'';
	$addtion_goods_seq = explode(",",$addtion_goods_seq);
	$addtion_goods_code = explode(",",$addtion_goods_code);

	$categorycode = $CI->categorymodel->get_category_goods_code($category_goods_code);
	$brandcode = $CI->brandmodel->get_brand_goods_code($brand_goods_code);

	$qry = "select * from fm_goods_code_form  where label_type ='goodsaddinfo'  and codesetting=1 order by sort_seq";
	$query = $CI->db->query($qry);
	$user_arr = $query -> result_array();
	foreach ($user_arr as $datarow){

		if( $datarow['label_code'] == 'goods_seq|' ){//상품고유번호
			$returncode[] = $no;
		}elseif( $datarow['label_code'] == 'category|' ){
			$returncode[] = str_replace(" > ","",$categorycode);
		}elseif( $datarow['label_code'] == 'brand|' ){
			$returncode[] =  str_replace(" > ","",$brandcode);;
		}else{
			foreach($addtion_goods_seq as $key=>$addition){
				if($addition == $datarow['codeform_seq']) {
					$returncode[] = $addtion_goods_code[$key];
				}
			}
		}
	}

	return implode('',$returncode);
}

//가입형식 추가 타입별 속성값 가져오기
function get_labelitem_type($data, $gddata,$showtype = null){
	$labelArray = explode("|", $data['label_value']);
	$labelcodeArray = explode("|", $data['label_code']);
	$labelCount = count($labelArray)-1;
	$labelindexBox = '';
	$label_value = ($gddata[0]) ? $gddata[0]['label_value'] : '';
	if($showtype == 'view'){
		$inputBox .= "<table class='selectLabelSet'><tr><td>";
		$inputBox .= $label_value ;
		$inputBox .= '</td></tr></table>';
	}elseif($showtype == 'setting'){
		$inputBox .= "";
		for ($j=0; $j<$labelCount; $j++)
		{
			if($data['codeform_seq'] == 1 && $labelcodeArray[$j] == 'category' ){
				$labelindexBox .=  '카테고리별 코드값은 상품 > <a href="/admin/category/catalog" target="_blank"><span class=" highlight-link hand">카테고리</span></a>에서 입력할 수 있습니다., ';
			}elseif($data['codeform_seq'] == 2 && $labelcodeArray[$j] == 'brand' ){
				$labelindexBox .=  '브랜드별 코드값은 상품 > <a href="/admin/brand/catalog" target="_blank"><span class=" highlight-link hand">브랜드</span></a>에서 입력할 수 있습니다., ';
			}elseif($data['base_type'] == '1' && $labelcodeArray[$j] == 'goods_seq' ){
				$labelindexBox .=  '자동 생성 상품코드의 중복을 방지할 수 있도록 상품의 고유값을 입력합니다., ';
			}else{
			$labelindexBox .=  $labelArray[$j] .'['.$labelcodeArray[$j].'], ';
		}
		}
		$labelindexBox = substr($labelindexBox,0,strlen($labelindexBox)-2);
		$inputBox .= $labelindexBox;
		$inputBox .= '';
	}else{
		if($data['label_type'] == 'goodsaddinfo' ) {//select box
			for ($j=0; $j<$labelCount; $j++)
			{
				$selected = ( $gddata['code_seq'] == $data['codeform_seq'] && $labelcodeArray[$j] == $gddata['contents']) ? "selected" : "";
				$labelindexBox .= '<option value="'. $labelcodeArray[$j] .'" '. $selected .'  >'. $labelArray[$j] .'</option>';
			}
			if($gddata){
				$labelsubBox = '<input type="hidden" name="subselect['.$gddata['code_seq'].'] id="subselect_'.$gddata['code_seq'].'" value="'.$gddata['contents_title'].'" code_seq="'.$gddata['code_seq'].'" class="hiddenLabelDepth">';
			}

			$inputBox .= '<select name="'.$data['label_type'].'['.$data['codeform_seq'].'][]" id="label_'.$data['codeform_seq'].'" codeform_seq="'.$data['codeform_seq'].'" style="height:18px; line-height:16px;" class="'.$data['label_type'].'"  label_type="'.$data['label_type'].'"  label_id="'.$data['label_id'].'" >';
			$inputBox .= $labelindexBox;
			$inputBox .= '</select>';
			$inputBox .= $labelsubBox;
		}else{//checkbox

			if($gddata[0])$cmsdata=count($gddata);
			for ($k=0; $k<$cmsdata; $k++) {
				$ckdata[] = $gddata[$k]['label_value'];
			}

			for ($j=0; $j<$labelCount; $j++) {
				if (is_array($gddata)) {
					$checked = (in_array($labelArray[$j], $ckdata )) ? "checked" : "";
				}
				if ($j > 0) $inputBox .= " ";
				$inputBox .= '<input type="checkbox" name="'.$data['label_type'].'['.$data['codeform_seq'].'][]" class="null labelCheckbox_'.$data['codeform_seq'].'"  codeform_seq="'.$data['codeform_seq'].'"  label_type="'.$data['label_type'].'"  label_id="'.$data['label_id'].'" value="'. $labelArray[$j] .'" '. $checked .'>'. $labelArray[$j] .'['.$labelcodeArray[$j].']';
			}
		}
	}
	return $inputBox;
}

// 회원 등급별 가격대체문구 출력
function get_string_price($data_goods){
	$CI =& get_instance();
	if( $CI->userInfo['member_seq'] == ''){ // 비회원
		if($data_goods['string_price_use'] == 1 && $data_goods['string_price']){
			$string_price = get_string_price_link($data_goods['string_price_link'],$data_goods['string_price_link_url'],$data_goods['string_price']);
		}
	}else if( $CI->userInfo['group_seq'] == '1'){ // 일반회원
		if($data_goods['member_string_price_use'] == 1 && $data_goods['member_string_price']){
			$string_price = get_string_price_link($data_goods['member_string_price_link'],$data_goods['member_string_price_link_url'],$data_goods['member_string_price']);
		}
	}else if( $CI->userInfo['group_seq'] > '1'){ // 일반회원 제외한 모든 회원
		if($data_goods['allmember_string_price_use'] == 1 && $data_goods['allmember_string_price']){
			$string_price = get_string_price_link($data_goods['allmember_string_price_link'],$data_goods['allmember_string_price_link_url'],$data_goods['allmember_string_price']);
		}
	}

	return $string_price;
}

function get_string_price_link($link,$url,$string_price){
	$result = $string_price;
	if($link == 'login'){
		$result = "<span class='hand' onclick=\"window.open('/member/login');\">".$result."</span>";
	}
	if($link == '1:1'){
		$result = "<span class='hand' onclick=\"window.open('/mypage/myqna_catalog');\">".$result."</span>";
	}
	if( $link == 'direct' && $url ){
		$result = "<span class='hand' onclick=\"window.open('".$url."');\">".$result."</span>";
	}
	return $result;
}


/**
* 특수정보 > 자동기간 미리보기
* $deposit_date : 결제일시(0000-00-00)
* $sdayauto : 시작되는 일 int
* $fdayauto : 끝나는 일 int
* $dayauto_type : 해당월(month), 해당일(day), 익월(next)
* $dayauto_day : 동안(day) 또는 말일(end)
**/
function goods_dayauto_setting_day( $deposit_date, $sdayauto, $fdayauto, $dayauto_type, $dayauto_day ) {
	$deposit_datear = explode("-",$deposit_date);

	$depositmonth = $deposit_datear[1];
	$depositday = $deposit_datear[1];
	$sday = $sdayauto;
	$fday = $fdayauto;
	if( $dayauto_type == 'month' ) {
		$social_start_date				= ($sday>0)?date("Y-m-d", strtotime($deposit_datear[0]."-".$depositmonth."-".$sday)):date("Y-m-d", strtotime($deposit_datear[0]."-".$depositmonth));
		$social_end_date_tmp		= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
	}elseif( $dayauto_type == 'day' ) {
		$social_start_date				= ($sday>0)?date("Y-m-d",strtotime('+'.$sday.' day '.$deposit_date)):date("Y-m-d",strtotime($deposit_date));
		$social_end_date_tmp		= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date));
	}elseif( $dayauto_type == 'next' ) {
		$social_start_date				= ($sday>0)?date("Y-m-d",strtotime(date("Y-m",strtotime('+1 month '.$deposit_date))."-".$sday)):date("Y-m-d",strtotime(date("Y-m",strtotime('+1 month '.$deposit_date))));
		$social_end_date_tmp		= ($fday>0)?date("Y-m-d",strtotime('+'.$fday.' day '.$social_start_date)):date("Y-m-d",strtotime($social_start_date)); 
	}

	if( $dayauto_day == 'end' ){//끝나는 날짜의 말일
		$social_end_date = date("Y-m-t", strtotime($social_end_date_tmp));
	}else{
		$social_end_date = date("Y-m-d", strtotime($social_end_date_tmp));
	}
	return array('social_start_date'=>$social_start_date,'social_end_date'=>$social_end_date);
}


// 필수옵션 option + option
function get_goods_options_print_array($param){

	// 특수옵션 처리
	if	($param['newtype']){
		$newtype	= explode(',', $param['newtype']);
		foreach($newtype as $k => $types){
			if(!$types)continue;
			$result[$types] = $k+1;
		}
	}

	return $result;
}

// 특수옵션 날짜/기간/주소 옵션 노출
function get_goods_special_option_print($param) {
	$CI =& get_instance();
	if(!$CI->goodsmodel) $CI->load->model('goodsmodel');
	$option_dayautotitle = '';

	// 특수옵션 처리
	if	($param['newtype']){
		$expire_arr				= array('dayauto', 'date', 'dayinput');
		$address_arr			= array('address');

		$dayauto_type = ($param['dayauto_type']  == 'day')?"이후":"";
		$newtype	= explode(',', $param['newtype']);
		foreach($newtype as $k => $types){
			if	(in_array($types, $expire_arr)){
				$key					= $k + 1;
				if( $types == 'date' ) {
					if($option_dayautotitle)$option_dayautotitle .= "<br/>";
					$option_dayautotitle .= $param['codedate'];
				}elseif( $types == 'dayauto' ) {
					if($option_dayautotitle)$option_dayautotitle .= "<br/>";
					$option_dayautotitle .= '"결제확인" 후 '.$CI->goodsmodel->dayautotype[$param['dayauto_type']].' '.$param['sdayauto'].'일 '.$dayauto_type.'부터 +'.$param['fdayauto'].'일 '.$CI->goodsmodel->dayautoday[$param['dayauto_day']];
				}else{
					if($option_dayautotitle)$option_dayautotitle .= "<br/>";
					$option_dayautotitle .= $param['sdayinput'] . ' ~ ' . $param['fdayinput'];
				}
			}
			if	($param['address'] && in_array($types, $address_arr)){
				if($option_dayautotitle)$option_dayautotitle .= "<br/>";
				if($param['address_type'] == 'street' ){
					$option_dayautotitle	.= ' [' . $param['zipcode'] . ']' .$param['address_street'].' '.$param['addressdetail'];
				}else{
					$option_dayautotitle	.= ' [' . $param['zipcode'] . ']' .$param['address'].' '.$param['addressdetail'];
				}
				$option_dayautotitle .= "<br/>";
				$option_dayautotitle	.= '업체연락처:' .$param['biztel'];
			}
		}
	}
	return $option_dayautotitle;
}
