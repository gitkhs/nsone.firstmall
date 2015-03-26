<?php
class Goodsmodel extends CI_Model {
	public function __construct(){
		$cfg_order = config_load('order');
		$this->load->helper('goods');
		$this->reservation_field = 'reservation'.$cfg_order['ableStockStep'];

		$this->dayautotype = array("month"=>"해당 월","day"=>"해당 일","next"=>"익월");
		$this->dayautoday = array("day"=>"동안","end"=>"이 되는 월의 말일");
	}
	public function goods_temp_image_upload($filename,$folder){
		$tmp = getimagesize($_FILES['Filedata']['tmp_name']);
		$_FILES['Filedata']['type'] = $tmp['mime'];
		$config['upload_path'] = $folder;
		$config['allowed_types'] = 'jpeg|jpg|png|gif';
		$config['max_size']	= $this->config_system['uploadLimit'];
		$config['file_name'] = $filename;
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('Filedata'))
		{
			$result = array('status' => '0','error' => $this->upload->display_errors());
		}else{
			$result = array('status' => 1,'fileInfo'=>$this->upload->data());
		}
		return $result;
	}

	public function goods_temp_image_resize($source,$target,$width,$height){
		$this->load->library('image_lib');
		$config['image_library'] = 'gd2';
		$config['source_image'] = $source;
		$config['new_image'] = $target;
		$config['quality'] = '100%';
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $width;
		$config['height'] = $height;
		$this->image_lib->initialize($config);
		if ( ! $this->image_lib->resize())
		{
			$result = array('status' => '0','error' => $this->image_lib->display_errors());
		}else{
			$result = array('status' => 1);
		}
		$this->image_lib->clear();
		return $result;
	}
	/* 상품등록 파라미터 검증*/
	public function check_param_regist(){
		$_POST['chkPrice'] = $_POST['price'][0];
		$_POST['chkStock'] = array_sum($_POST['stock']);
		//if($_POST['chkStock']<1) unset($_POST['chkStock']);

		if($_POST['goods_type']=='gift'){
			$_POST['tax']				= 'tax';
			$_POST['tax_chk']			= 'Y';
			$_POST['minPurchaseLimit']	= 'unlimit';
			$_POST['maxPurchaseLimit']	= 'unlimit';
			$_POST['reserve_policy']	= 'shop';
			$_POST['shippingWeightPolicy']	= 'shop';
			$_POST['relation_type']		= 'AUTO';
			$_POST['relation_count_w']	= 4;
			$_POST['relation_count_h']	= 1;
			$_POST['info_select']		= 0;
		}else{
			$_POST['goods_type'] = 'goods';
		}

		if( $_POST['string_price_use'] == '' ) $_POST['string_price_use'] = 0;
		if( $_POST['member_string_price_use'] == '' ) $_POST['member_string_price_use'] = 0;
		if( $_POST['allmember_string_price_use'] == '' ) $_POST['allmember_string_price_use'] = 0;

		if( !isset($_POST['multiDiscountUse']) ) $_POST['multiDiscountUse'] = 0;
		if( !isset($_POST['optionUse']) ) $_POST['optionUse'] = 0;
		if( !isset($_POST['subOptionUse']) ) $_POST['subOptionUse'] = 0;
		if( !isset($_POST['memberInputUse']) ) $_POST['memberInputUse'] = 0;
		if( !isset($_POST['restockNotifyUse']) ) $_POST['restockNotifyUse'] = 0;

		$chkArr['string_price']				= "";
		$chkArr['string_price_link_url']	= "";
		$chkArr['member_string_price']		= "";
		$chkArr['member_string_price_link_url']	= "";
		$chkArr['allmember_string_price']			= "";
		$chkArr['allmember_string_price_link_url']	= "";

		$chkArr['multiDiscountEa']			= "";
		$chkArr['multiDiscount']			= "";
		$chkArr['multiDiscountUnit']		= "";
		$chkArr['minPurchaseEa']			= "";
		$chkArr['maxPurchaseOrderLimit']	= "";
		$chkArr['maxPurchaseEa']			= "";
		$chkArr['optionViewType']			= "";
		$chkArr['goodsShippingPolicy']		= "";
		$chkArr['unlimitShippingPrice']		= "";
		$chkArr['limitShippingEa']			= "";
		$chkArr['limitShippingPrice']		= "";
		$chkArr['limitShippingSubPrice']	= "";
		$chkArr['goodsWeight']				= "";

		$this->validation->set_rules('firstCategory', '대표카테고리','trim|xss_clean');
		$this->validation->set_rules('connectCategory[]', '카테고리연결','trim|xss_clean');
		$this->validation->set_rules('viewLayout', '상품상세페이지','trim|required|xss_clean');
		$this->validation->set_rules('goodsStatus', '상태','trim|required|xss_clean');
		$this->validation->set_rules('goodsView', '노출','trim|required|xss_clean');
		$this->validation->set_rules('goodsCode', '상품 코드','trim|xss_clean');
		if($_POST['goods_type']=='gift'){
			$this->validation->set_rules('goodsName', '사은품명','trim|required');//|xss_clean
		}else{
			$this->validation->set_rules('goodsName', '상품명','trim|required');//|xss_clean
		}
		$this->validation->set_rules('purchaseGoodsName', '매입용 상품명','trim');//|xss_clean
		$this->validation->set_rules('summary', '간략 설명','trim');//|xss_clean
		$this->validation->set_rules('keyword', '상품 검색 태그','trim');//|xss_clean
		//$this->validation->set_rules('contents', '상품 설명','trim|xss_clean');
		//$this->validation->set_rules('commonContents', '공용 정보','trim|xss_clean');
		$this->validation->set_rules('info_name', '공용 정보명','trim|xss_clean');

		if( $_POST['string_price_use'] == 1 ){
			$this->validation->set_rules('string_price', '가격 대체 문구','trim|required|xss_clean');
			$chkArr['string_price']	= $_POST['string_price'];
		}
		if( $_POST['member_string_price_use'] == 1 ){
			$this->validation->set_rules('member_string_price', '가격 대체 문구','trim|required|xss_clean');
			$chkArr['member_string_price']	= $_POST['member_string_price'];
		}
		if( $_POST['allmember_string_price_use'] == 1 ){
			$this->validation->set_rules('allmember_string_price', '가격 대체 문구','trim|required|xss_clean');
			$chkArr['allmember_string_price']	= $_POST['allmember_string_price'];
		}
		if( $_POST['string_price_link'] == 'direct' ){
			$this->validation->set_rules('string_price_link_url', '가격디스플레이 링크주소','trim|required|xss_clean');
			$chkArr['string_price_link_url']	= $_POST['string_price_link_url'];
		}
		if( $_POST['member_string_price_link'] == 'direct' ){
			$this->validation->set_rules('member_string_price_link_url', '가격디스플레이 링크주소','trim|required|xss_clean');
			$chkArr['member_string_price_link_url']	= $_POST['member_string_price_link_url'];
		}
		if( $_POST['allmember_string_price_link'] == 'direct' ){
			$this->validation->set_rules('allmember_string_price_link_url', '가격디스플레이 링크주소','trim|required|xss_clean');
			$chkArr['allmember_string_price_link_url']	= $_POST['allmember_string_price_link_url'];
		}

		$this->validation->set_rules('tax', '부가세','trim|required|xss_clean');
		if( $_POST['multiDiscountUse'] == 1 ){
			$this->validation->set_rules('multiDiscountEa', '복수구매할인','trim|required|xss_clean');
			$this->validation->set_rules('multiDiscount', '복수구매할인','trim|numeric|required|xss_clean');
			$this->validation->set_rules('multiDiscountUnit', '복수구매할인','trim|required|xss_clean');
			$chkArr['multiDiscountEa'] 		= $_POST['multiDiscountEa'];
			$chkArr['multiDiscount']		= $_POST['multiDiscount'];
			$chkArr['multiDiscountUnit']	= $_POST['multiDiscountUnit'];
		}
		$this->validation->set_rules('minPurchaseLimit', '최소 구매수량','trim|required|xss_clean');

		if( $_POST['minPurchaseLimit'] == 'limit' ){
			$this->validation->set_rules('minPurchaseEa', '최소 구매수량','trim|numeric|required|xss_clean');
			if($_POST['minPurchaseEa'] < 2){
				echo("<script>parent.able_save();</script>");
				openDialogAlert('최소 구매수량은 2개 이상 입력하셔야 합니다.',400,140,'parent','');
				exit;
			}
			$chkArr['minPurchaseEa']	= $_POST['minPurchaseEa'];
		}else{
			$_POST['minPurchaseEa'] = 1;
		}
		$this->validation->set_rules('maxPurchaseLimit', '최대 구매수량','trim|required|xss_clean');
		if( $_POST['maxPurchaseLimit'] == 'limit' ){
			// $this->validation->set_rules('maxPurchaseOrderLimit', '최대 구매수량','trim|numeric|required|xss_clean');
			$this->validation->set_rules('maxPurchaseEa', '최대 구매수량','trim|numeric|required|xss_clean');
			//$chkArr['maxPurchaseOrderLimit']	= $_POST['maxPurchaseOrderLimit'];
			if( $_POST['minPurchaseEa'] > $_POST['maxPurchaseEa'] ){
				echo("<script>parent.able_save();</script>");
				openDialogAlert('최대 구매수량은 최소 구매수량('.$_POST['minPurchaseEa'].'개) 이상 입력하셔야 합니다.',400,140,'parent','');
				exit;
			}

			$chkArr['maxPurchaseEa']			= $_POST['maxPurchaseEa'];
		}

		if( $_POST['multiDiscountUse'] ){
			if($_POST['multiDiscountEa'] < 2){
				openDialogAlert('복수구매 할인은 최소 2개 이상부터 가능합니다.',400,140,'parent','');
				exit;
			}
		}

		if( $_POST['optionUse'] == '1' ){
			$this->validation->set_rules('optionViewType', '옵션 출력 형식','trim|required|xss_clean');
			$chkArr['optionViewType']	= $_POST['optionViewType'];
		}

		$this->validation->set_rules('chkPrice', '할인가(판매가)','trim|numeric|required|xss_clean');
		$this->validation->set_rules('chkStock', '재고','trim|numeric|xss_clean');
		if( isset($_POST['opt']) ){
			$this->validation->set_rules('defaultOption', '기준할인가','trim|required|xss_clean');
		}
		$this->validation->set_rules('shippingPolicy', '국내 배송','trim|required|xss_clean');
		if( $_POST['shippingPolicy'] == 'goods' ){
			$this->validation->set_rules('goodsShippingPolicy', '개별 배송비 정책','trim|required|xss_clean');
			$chkArr['goodsShippingPolicy']	= $_POST['goodsShippingPolicy'];
			if( $_POST['goodsShippingPolicy'] == 'unlimit' ){
				$this->validation->set_rules('unlimitShippingPrice', '개별 배송비 정책','trim|numeric|required|xss_clean');
				$chkArr['unlimitShippingPrice']	= $_POST['unlimitShippingPrice'];
			}else if( $_POST['goodsShippingPolicy'] == 'limit' ){
				$this->validation->set_rules('limitShippingEa', '개별 배송비 정책','trim|numeric|required|xss_clean');
				$this->validation->set_rules('limitShippingPrice', '개별 배송비 정책','trim|numeric|required|xss_clean');
				$this->validation->set_rules('limitShippingSubPrice', '개별 배송비 정책','trim|numeric|required|xss_clean');
				$chkArr['limitShippingEa']			= $_POST['limitShippingEa'];
				$chkArr['limitShippingPrice']		= $_POST['limitShippingPrice'];
				$chkArr['limitShippingSubPrice']	= $_POST['limitShippingSubPrice'];
			}
		}

		###
		if( $_POST['tax'] == 'exempt' ){
			if(!isset($_POST['tax_chk']) && !$_POST['tax_chk']=="Y"){
				$callback = "parent.$(\"input[name='tax_chk']\").focus();";
				openDialogAlert("비과세 주의사항 읽음을 체크해 주세요.",400,140,'parent',$callback);
				exit;
			}
		}

		$this->validation->set_rules('shippingWeightPolicy', '해외 배송','trim|xss_clean');
		if( $_POST['shippingWeightPolicy'] == 'goods' ){
			$this->validation->set_rules('goodsWeight', '상품 중량 ','trim|numeric|required|xss_clean');
			$chkArr['goodsWeight']	= $_POST['goodsWeight'];
		}
		$this->validation->set_rules('adminMemo', '관리자 메모','trim|xss_clean');
		if	($_POST['feed_evt_text'])
			$this->validation->set_rules('feed_evt_text', '이벤트 문구','trim|max_length[100]|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			if( $err['key'] == 'chkPrice' )	$callback = "parent.document.getElementsByName('price[]')[0].focus();";
			if( $err['key'] == 'chkStock' ) $callback = "parent.document.getElementsByName('stock[]')[0].focus();";
			echo("<script>parent.able_save();</script>");
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		###
		$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';
		$contents			= adjustEditorImages($_POST['contents'], "/data/editor/");
		$common_contents	= adjustEditorImages($_POST['commonContents'], "/data/editor/");
		$mobile_contents	= adjustEditorImages($_POST['mobile_contents'], "/data/editor/");

		if($_POST['subOptionUse']){
			$goods['individual_refund'] 				= $_POST['individual_refund'];
			$goods['individual_refund_inherit'] 		= $_POST['individual_refund_inherit'];
			$goods['individual_export'] 				= $_POST['individual_export'];
			$goods['individual_return'] 				= $_POST['individual_return'];
		}

		$goods['view_layout'] 				= $_POST['viewLayout'];
		$goods['goods_status']				= $_POST['goodsStatus'];
		$goods['goods_view'] 				= $_POST['goodsView'];
		$goods['goods_code'] 				= $_POST['goodsCode'];
		$goods['goods_name'] 				= $_POST['goodsName'];
		$goods['goods_name_linkage']		= ($_POST['goodsNameLinkage'])?$_POST['goodsNameLinkage']:$_POST['goodsName'];
		$goods['purchase_goods_name'] 		= $_POST['purchaseGoodsName'];
		$goods['summary'] 					= $_POST['summary'];
		$goods['keyword'] 					= $_POST['keyword'];
		$goods['contents'] 					= $contents;

		//이미지호스팅체크 변환대상갯수
		if ( $contents ) {
			$this->load->model("imagehosting");
			$this->imagehosting->get_contents_cnt($goods['contents'],$goods['convert_image_cnt'],$goods['noconvert_image_cnt']);
		} 

		$goods['common_contents'] 			= $common_contents;
		$goods['mobile_contents'] 			= $mobile_contents;

		$goods['string_price_use'] 			= $_POST['string_price_use'];
		$goods['string_price'] 				= $chkArr['string_price'];
		$goods['string_price_link'] 		= $_POST['string_price_link'];
		$goods['string_price_link_url'] 	= $chkArr['string_price_link_url'];
		$goods['member_string_price_use'] 		= $_POST['member_string_price_use'];
		$goods['member_string_price'] 			= $chkArr['member_string_price'];
		$goods['member_string_price_link'] 		= $_POST['member_string_price_link'];
		$goods['member_string_price_link_url'] 	= $chkArr['member_string_price_link_url'];
		$goods['allmember_string_price_use'] 	= $_POST['allmember_string_price_use'];
		$goods['allmember_string_price'] 		= $chkArr['allmember_string_price'];
		$goods['allmember_string_price_link'] 	= $_POST['allmember_string_price_link'];
		$goods['allmember_string_price_link_url']	= $chkArr['allmember_string_price_link_url'];

		$goods['tax'] 						= $_POST['tax'];
		$goods['multi_discount_use'] 		= $_POST['multiDiscountUse'];
		$goods['multi_discount_ea'] 		= $chkArr['multiDiscountEa'];
		$goods['multi_discount'] 			= $chkArr['multiDiscount'];
		$goods['multi_discount_unit'] 		= $chkArr['multiDiscountUnit'];
		$goods['min_purchase_limit'] 		= $_POST['minPurchaseLimit'];
		$goods['min_purchase_ea'] 			= $chkArr['minPurchaseEa'];
		$goods['max_purchase_limit'] 		= $_POST['maxPurchaseLimit'];
		$goods['max_purchase_order_limit'] 	= $chkArr['maxPurchaseOrderLimit'];
		$goods['max_purchase_ea'] 			= $chkArr['maxPurchaseEa'];
		$goods['option_use'] 				= $_POST['optionUse'];
		$goods['reserve_policy'] 			= $_POST['reserve_policy'];
		$goods['option_view_type'] 			= $chkArr['optionViewType'];
		$goods['option_suboption_use']		= $_POST['subOptionUse'];
		$goods['member_input_use'] 			= $_POST['memberInputUse'];
		$goods['shipping_policy'] 			= $_POST['shippingPolicy'];
		$goods['goods_shipping_policy'] 	= $chkArr['goodsShippingPolicy'];
		$goods['unlimit_shipping_price'] 	= $chkArr['unlimitShippingPrice'];
		$goods['limit_shipping_ea'] 		= $chkArr['limitShippingEa'];
		$goods['limit_shipping_price'] 		= $chkArr['limitShippingPrice'];
		$goods['limit_shipping_subprice'] 	= $chkArr['limitShippingSubPrice'];
		$goods['shipping_weight_policy'] 	= $_POST['shippingWeightPolicy'];
		$goods['goods_weight'] 				= $chkArr['goodsWeight'];
		$goods['admin_memo'] 				= $_POST['adminMemo'];
		$goods['restock_notify_use'] 		= $_POST['restockNotifyUse'];
		$goods['individual_refund']			= $_POST['individual_refund'];
		$goods['individual_refund_inherit']	= $_POST['individual_refund_inherit'];
		$goods['individual_export']			= $_POST['individual_export'];
		$goods['individual_return']			= $_POST['individual_return'];

		/*
		* 쿠폰 위치서비스 설정 :: 2014-04-01 lwh
		*/
		$goods['pc_mapview']				= ($_POST['pc_mapView'])	? $_POST['pc_mapView']	: 'N';
		$goods['m_mapview']					= ($_POST['m_mapView'])		? $_POST['m_mapView']	: 'N';

		/*
		*자주사용하는 옵션여부 저장
		*/
		$goods['frequentlyopt'] 			= ($_POST['frequentlyopt'])?'1':'0';
		$goods['frequentlysub'] 			= ($_POST['frequentlysub'])?'1':'0';
		$goods['frequentlyinp'] 			= ($_POST['frequentlyinp'])?'1':'0';

		// 입점마케팅 관련 추가
		$goods['feed_status']				= $_POST['feed_status'];
		if	($_POST['feed_status'] != 'N')
			$goods['feed_status'] 				= 'Y';
		$goods['feed_evt_sdate'] 			= $_POST['feed_evt_sdate'];
		$goods['feed_evt_edate'] 			= $_POST['feed_evt_edate'];
		$goods['feed_evt_text'] 			= $_POST['feed_evt_text'];

		$goods['relation_type'] 			= $_POST['relation_type'];
		$goods['relation_count_w'] 			= $_POST['relation_count_w'];
		$goods['relation_count_h'] 			= $_POST['relation_count_h'];
		$goods['relation_image_size'] 		= $_POST['relation_image_size'];
		$goods['relation_criteria'] 		= $_POST['relation_criteria'];
		$goods['info_seq'] 					= $_POST['info_select'];
		$goods['goods_type'] 				= $_POST['goods_type'];

		if($_POST['goods_sub_info'] == 0 ){
			$goods['goods_sub_info'] 			= 0;
		}else{
			$goods['goods_sub_info'] 			= $_POST['goods_sub_info'];
		}

		$goods['cancel_type'] 			= ($_POST['cancel_type'])?'1':'0';//청약철회상품여부

		$goods['socialcp_event'] 			= ($_POST['socialcp_event'])?$_POST['socialcp_event']:'0';

		if( $_POST['goods_kind'] == 'coupon' ){//쇼셜쿠폰 상품이면
			$goods['goods_kind'] 					= 'coupon';
			$goods['socialcp_input_type'] 	= ($_POST['socialcp_input_type'])?$_POST['socialcp_input_type']:'pass';
			$goods['socialcp_cancel_type'] 	= ($_POST['socialcp_cancel_type'])?$_POST['socialcp_cancel_type']:'pay';
			$goods['socialcp_use_return'] 	= ($_POST['socialcp_use_return'])?$_POST['socialcp_use_return']:'0';

			$goods['socialcp_use_emoney_day'] 		= ($_POST['socialcp_use_emoney_day'])?$_POST['socialcp_use_emoney_day']:0;
			$goods['socialcp_use_emoney_percent']		= ($_POST['socialcp_use_emoney_percent'])?$_POST['socialcp_use_emoney_percent']:0;

			$goods['socialcp_cancel_use_refund'] 	= ($_POST['socialcp_cancel_use_refund'])?$_POST['socialcp_cancel_use_refund']:'0';
			$goods['socialcp_cancel_payoption'] 	= ($_POST['socialcp_cancel_payoption'])?$_POST['socialcp_cancel_payoption']:'0';
			$goods['socialcp_cancel_payoption_percent'] 	= ($_POST['socialcp_cancel_payoption_percent'])?$_POST['socialcp_cancel_payoption_percent']:0;

			$goods['shipping_weight_policy'] 	= 'shop';
		}else{
			$goods['socialcp_input_type'] 	= 'price';
			$goods['goods_kind'] 					= 'goods';
		}

		if( $_POST['social_goods_group_name'] ){
			if( $_POST['social_goods_group'] && trim($_POST['social_goods_group_name']) == trim($_POST['social_goods_group_name_tmp']) ){
				$goods['social_goods_group']		= $_POST['social_goods_group'];
			}else{
				$this->load->model('socialgoodsgroupmodel');
				$social_goods_group_name =  trim($_POST['social_goods_group_name']);
				$social_goods_group_data = $this->socialgoodsgroupmodel->get_data_numrow(array("select"=>" group_seq ","whereis"=>" and name = '".$social_goods_group_name."' "));
				if( $social_goods_group_data ) {
					openDialogAlert("이미 등록된 쿠폰상품그룹명입니다.",400,140,'parent',$callback);
					exit;
				}else{
					if( defined('__SELLERADMIN__') === true ){
						$insertdata['provider_seq'] = $this->providerInfo['provider_seq'];
					}else{
						$insertdata['provider_seq'] = ($goods['provider_seq'])?$goods['provider_seq']:1;
					}
					$insertdata['name'] = trim($_POST['social_goods_group_name']);
					$insertdata['regist_date'] = date("Y-m-d H:i:s",time());
					$social_goods_group_idx = $this->socialgoodsgroupmodel->sggroup_write($insertdata);
					$goods['social_goods_group']		= ($social_goods_group_idx)?$social_goods_group_idx:0;
				}
			}
		}


		//동영상
		foreach($_POST['videofiles']['image'] as $videoimageseq) {//상품이미지영역은 1개뿐
			$videoimageseq = ($videoimageseq)?$videoimageseq:0;
			$goods['video_use']		= (!empty($_POST['viewer_use']['image'][$videoimageseq]))?'Y':'N';//노출여부
			$goods['video_position']		= (!empty($_POST['viewer_position']['image'][$videoimageseq]))?$_POST['viewer_position']['image'][$videoimageseq]:'first';//노출위치(맨앞/맨뒤)
			$goods['video_view_type']		= ($goods['video_use'] == 'Y')?1:0;//상단 상품이미지
			if($_POST['pc_width']['image'][$videoimageseq]) $goods['video_size'] =$_POST['pc_width']['image'][$videoimageseq]."X".$_POST['pc_height']['image'][$videoimageseq];//화면크기
			if($_POST['mobile_width']['image'][$videoimageseq]) $goods['video_size_mobile'] = $_POST['mobile_width']['image'][$videoimageseq]."X".$_POST['mobile_height']['image'][$videoimageseq];//화면크기


			if($_POST['video_del']['image'][$videoimageseq] == 1) {
				$goods['file_key_w'] = '';//원본파일코드초기화
				$goods['file_key_i'] = '';//원본파일코드초기화
			}else{
				if($_POST['file_key_w']['image'][$videoimageseq]) $goods['file_key_w'] = $_POST['file_key_w']['image'][$videoimageseq];
				if($_POST['file_key_i']['image'][$videoimageseq]) $goods['file_key_i'] = $_POST['file_key_i']['image'][$videoimageseq];
			}
		}
		$goods['videototal'] = count($_POST['videofiles']['contents']);//상품설명영역동영상여부if( count($_POST['videofiles']['contents']) > 0 )
		$goods['videousetotal'] = count($_POST['viewer_use']['image']) + count($_POST['viewer_use']['contents']);//노출동영상갯수

		if($_POST['videotmpcode']){//$this->session->userdata('videotmpcode')
			$goods['videotmpcode'] = $_POST['videotmpcode'];//코드
		}

		//개별재고 여부
		$goods['runout_policy'] = $_POST['runout_policy'];
		$goods['able_stock_limit'] = $_POST['able_stock_limit'];

		// 외부 쿠폰 저장
		if	($_POST['coupon_serial_type'] == 'n' && $_POST['coupon_serial_upload'])
			$goods['coupon_serial_type'] = 'n';
		else
			$goods['coupon_serial_type'] = 'a';

		// 입점 마케팅 상품명 개별설정
		if (isset($_POST['feed_goods_name'])) {
			if (isset($_POST['feed_goods_use']) && empty($_POST['feed_goods_name'])) {
				$callback = "parent.document.getElementsByName('feed_goods_name')[0].focus();";
				openDialogAlert('입점 마케팅 상품명을 입력해 주세요.',400,140,'parent',$callback);
				exit;
			}

			if ($_POST['feed_goods_use']=='Y') {
				$goods['feed_goods_use'] = 'Y';
			} else {
				$goods['feed_goods_use'] = 'N';
			}
			$goods['feed_goods_name'] = $_POST['feed_goods_name'];
		}

		return $goods;
	}

	public function set_goodsImageSize($type,$width,$height){
		$arrNames['large'] 			= '상품상세(확대)';
		$arrNames['view'] 			= '상품상세(기본)';
		$arrNames['list1'] 			= '리스트(1)';
		$arrNames['list2'] 			= '리스트(2)';
		$arrNames['thumbView'] 		= '썸네일(상품상세)';
		$arrNames['thumbCart'] 		= '썸네일(장바구니/주문)';
		$arrNames['thumbScroll'] 	= '썸네일(스크롤)';
		config_save( 'goodsImageSize', array($type =>array('name'=>$arrNames[$type],'width'=>$width,'height'=>$height)));
	}

	public function upload_goodsImage_dir(){
		$dir = ROOTPATH.'/data/goods/'.date("Ym");
		@mkdir($dir);
		@chmod($dir,0777);
		$dir = str_replace(ROOTPATH,"",$dir);
		return $dir;
	}

	public function get_target_goodsImage($file){
		if( substr_count($file,'/data/goods/') > 0 || substr_count($file,'/data/tmp/') > 0 ){
			$dir = $this->upload_goodsImage_dir();
			$arr = explode('/', $file);
			$fn = $arr[count($arr)-1];
			$target = $dir.'/'.$fn;
		}else{
			$target= $file;
		}
		return $target;
	}

	public function upload_goodsImage($arr){
		foreach( $arr as $i => $file ){
			if(substr_count($file,'/data/goods/') > 0 || substr_count($file,'/data/tmp/') > 0){
				$target = $this->get_target_goodsImage($file);
				rename('.'.$file,'.'.$target);
				@chmod('.'.$target,0777);
			}
		}
	}

	// 대표컷의 리스트1,리스트2,썸네일(장바구니 스크롤)
	public function list_image_create()
	{
		$arr_first_keyhead = array('list1','list2','thumbCart','thumbScroll');
		if($_POST['largeGoodsImage'][0]){
			foreach($arr_first_keyhead as $first_keyhead){
				if(!$_POST[$first_keyhead.'GoodsImage'][0]){
					$_POST[$first_keyhead.'GoodsImage'][0] = str_replace('large',$first_keyhead,$_POST['largeGoodsImage'][0]);

					$source = '.'.$_POST['largeGoodsImage'][0];
					$target = '.'.$_POST[$first_keyhead.'GoodsImage'][0];

					$width = $_POST[$first_keyhead.'ImageWidth'];
					$height = $_POST[$first_keyhead.'ImageHeight'];
					if(!$width){
						$width = $_POST[$first_keyhead.'Width'];
						$height = $_POST[$first_keyhead.'Height'];
					}

					$this->goods_temp_image_resize($source,$target,$width,$height);
				}
			}
		}
	}

	// 대표컷2의 리스트1,리스트2
	public function cut2_list_image_create()
	{
		$arr_first_keyhead = array('list1','list2');
		if($_POST['largeGoodsImage'][1]){
			foreach($arr_first_keyhead as $first_keyhead){
				if(!$_POST[$first_keyhead.'GoodsImage'][1]){
					$_POST[$first_keyhead.'GoodsImage'][1] = str_replace('large',$first_keyhead,$_POST['largeGoodsImage'][1]);

					$source = '.'.$_POST['largeGoodsImage'][1];
					$target = '.'.$_POST[$first_keyhead.'GoodsImage'][1];

					$width = $_POST[$first_keyhead.'ImageWidth'];
					$height = $_POST[$first_keyhead.'ImageHeight'];
					if(!$width){
						$width = $_POST[$first_keyhead.'Width'];
						$height = $_POST[$first_keyhead.'Height'];
					}

					$this->goods_temp_image_resize($source,$target,$width,$height);
				}
			}
		}
	}

	public function insert_goodsImage($key,$goodsSeq){

		if(! isset($_POST[$key]) ) return false;
		$tmp_cnt = 0;

		foreach($_POST[$key] as $i => $img){
			if(!$img) continue;

			// 대표컷1~2의 리스트1,리스트2,썸네일(장바구니 스크롤)
			if($key == 'largeGoodsImage' && $tmp_cnt ==0 && $img) $this->list_image_create();
			if($key == 'largeGoodsImage' && $tmp_cnt ==1 && $img) $this->cut2_list_image_create();

			$labelKey = str_replace('Image','Label',$key);
			$type = str_replace('GoodsImage','',$key);
			/*
			if(isset($_POST[$key.'Seq'][$i])){
				$imgs['image_seq'] = $_POST[$key.'Seq'][$i];
			}
			*/
			$imgs = array();
			$imgs['image_type'] = $type;
			$imgs['goods_seq'] = $goodsSeq;
			$imgs['cut_number'] = $tmp_cnt+1;
			/**if(($key=='thumbCartGoodsImage' || $key=='thumbScrollGoodsImage') && $tmp_cnt == 1){
				@unlink('.'.$img);
				$imgs['image'] = "";
			}elseif((substr($key,0,4)=='list' || $key=='thumbCartGoodsImage' || $key=='thumbScrollGoodsImage') && $tmp_cnt > 1){
				@unlink('.'.$img);
				$imgs['image'] = "";
			}else{
				$imgs['image'] = $this->get_target_goodsImage($img);
			}**/
			$imgs['image'] = $this->get_target_goodsImage($img);

			if($_POST["goodsImageColor"][$i]) {
				$imgs["match_color"] = $_POST["goodsImageColor"][$i];
			}

			$imgs['label'] = $_POST[$labelKey][$i];//$imgs['label'] = $_POST['goodsImgLabel'][$i];
			$result = $this->db->insert('fm_goods_image', $imgs);
			unset($imgs);
			$tmp_cnt++;


		}
	}

	public function get_goods_category($no){
		$result = false;
		$query = "select c.title, c.category_goods_code, l.* from fm_category_link l,fm_category c where l.category_code=c.category_code and l.goods_seq=?";
		$query = $this->db->query($query,array($no));
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	public function get_goods_brand($no){

		$result = false;
		$query = "select c.title, c.brand_goods_code, l.* from fm_brand_link l,fm_brand c where l.category_code=c.category_code and l.goods_seq=?";
		$query = $this->db->query($query,array($no));
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	public function get_goods_location($no){

		$result = false;
		$query = "select c.title, '' as location_goods_code, l.* from fm_location_link l,fm_location c where l.location_code=c.location_code and l.goods_seq=?";
		$query = $this->db->query($query,array($no));
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	public function get_goods_category_default($no){
		$result = false;
		$query = "select c.title,l.* from fm_category_link l,fm_category c where l.category_code=c.category_code and l.goods_seq=? and l.link=1";
		$query = $this->db->query($query,array($no));
		$result = $query->row_array();
		return $result;
	}

	public function get_goods_brand_default($no){
		$result = false;
		$query = "select c.title,l.* from fm_brand_link l,fm_brand c where l.category_code=c.category_code and l.goods_seq=? and l.link=1";
		$query = $this->db->query($query,array($no));
		$result = $query->row_array();
		return $result;
	}

	public function get_goods_location_default($no){
		$result = false;
		$query = "select c.title,l.* from fm_location_link l,fm_location c where l.location_code=c.location_code and l.goods_seq=? and l.link=1";
		$query = $this->db->query($query,array($no));
		$result = $query->row_array();
		return $result;
	}

	public function get_goods($no){
		$result = false;
		$query = "select * from fm_goods where goods_seq=? limit 1";
		$query = $this->db->query($query,array($no));
		$result = $query->result_array();
		$ea = 1;
		if($result[0]['min_purchase_limit'] == 'limit' && $result[0]['min_purchase_ea']){
			$ea = $result[0]['min_purchase_ea'];
		}
		$result[0]['min_purchase_ea'] = $ea;

		$ea = 0;
		if($result[0]['max_purchase_limit'] == 'limit' && $result[0]['max_purchase_ea']){
			$ea = $result[0]['max_purchase_ea'];
		}
		$result[0]['max_purchase_ea'] = $ea;

		### 공용정보 노출 :: 2014-01-14 lwh 
		###  => goods/view_contents, goodsmodel/get_goods_view에서 이쪽으로 옮김. 2015-03-13 pjm
		$info = get_data("fm_goods_info",array("info_seq"=>$result[0]['info_seq']));
		if($result[0]['info_seq'] && $info){
			$result[0]['common_contents'] = $info[0]['info_value'];
			if( ( $this->mobileMode || $this->storemobileMode ) || $this->_is_mobile_agent ) {
				$result[0]['common_contents'] = $this->set_mobile_common_contents($result[0]['common_contents']);
			}
		}

		return $result[0];
	}

	public function get_goods_option($no){
		$op1tArr	= array();
		$op2tArr	= array();
		$op3tArr	= array();
		$op4tArr	= array();
		$op5tArr	= array();
		$result = false;
		$sql = "select o.*,s.badstock, s.stock, s.supply_price, s.reservation15, s.reservation25 from fm_goods_option o left join fm_goods_supply s on o.option_seq=s.option_seq where o.goods_seq=? order by o.option_seq asc";
		$query = $this->db->query($sql,array($no));
		while($data = mysql_fetch_assoc($query->result_id)){
			$optJoin = "";$optcodeJoin = "";
			if( $data['option_title'] ) $data['option_divide_title'] = explode(',',$data['option_title']);

			if( $data['option_type'] ) $data['option_divide_type'] = explode(',',$data['option_type']);
			if( $data['code_seq'] ) $data['option_divide_codeseq'] = explode(',',$data['code_seq']);

			if( $data['newtype'] )			$data['divide_newtype']			= explode(',',$data['newtype']);
			if( $data['tmpprice'] )			$data['divide_tmpprice']			= explode(',',$data['tmpprice']);

			if( $data['dayauto_type'] ) $data['dayauto_type_title'] = $this->dayautotype[$data['dayauto_type']];
			if( $data['dayauto_day'] ) $data['dayauto_day_title'] = $this->dayautoday[$data['dayauto_day']];
			if( $data['color']!='' ) $data['color'] = trim($data['color']);

			if( $data['option1']!='' ) $optJoin[] = $data['option1'];
			if( $data['option2']!='' ) $optJoin[] = $data['option2'];
			if( $data['option3']!='' ) $optJoin[] = $data['option3'];
			if( $data['option4']!='' ) $optJoin[] = $data['option4'];
			if( $data['option5']!='' ) $optJoin[] = $data['option5'];
			if( $optJoin ){
				$data['opts'] = $optJoin;
			}
			if( $data['optioncode1'] ) $optcodeJoin[] = $data['optioncode1'];
			if( $data['optioncode2'] ) $optcodeJoin[] = $data['optioncode2'];
			if( $data['optioncode3'] ) $optcodeJoin[] = $data['optioncode3'];
			if( $data['optioncode4'] ) $optcodeJoin[] = $data['optioncode4'];
			if( $data['optioncode5'] ) $optcodeJoin[] = $data['optioncode5'];
			if( $optcodeJoin ){
				$data['optcodes'] = $optcodeJoin;
			}

			if( $data['option1']!='' && !in_array($data['option1'], $op1tArr))
				$op1tArr[] = $data['option1'];
			if( $data['option2'] != '' && !in_array($data['option2'], $op2tArr) )
				$op2tArr[] = $data['option2'];
			if( $data['option3'] != '' && !in_array($data['option3'], $op3tArr) )
				$op3tArr[] = $data['option3'];
			if( $data['option4'] != '' && !in_array($data['option4'], $op4tArr) )
				$op4tArr[] = $data['option4'];
			if( $data['option5'] != '' && !in_array($data['option5'], $op5tArr) )
				$op5tArr[] = $data['option5'];

			if	($data['consumer_price']){
				$data['supplyRate'] = floor($data['supply_price'] / $data['consumer_price'] * 100);
				$data['discountRate'] = (int) ( ($data['consumer_price'] - $data['price']) / $data['consumer_price'] * 100 );

			}
			$data['tax']		= floor($data['price'] - ($data['price'] / 1.1));
			$data['net_profit']	= $data['price'] - $data['supply_price'];

			$data['rstock'] = $data['stock'] - $data[$this->reservation_field];
			$result[] = $data;
		}
		if	($result[0]){
			$result[0]['optionArr'][] = $op1tArr;
			$result[0]['optionArr'][] = $op2tArr;
			$result[0]['optionArr'][] = $op3tArr;
			$result[0]['optionArr'][] = $op4tArr;
			$result[0]['optionArr'][] = $op5tArr;
		}

		return $result;
	}


	public function get_goods_default_option($no){
		$result = false;
		$sql = "select o.*,s.badstock, s.stock, s.supply_price, s.reservation15, s.reservation25 from fm_goods_option o left join fm_goods_supply s on o.option_seq=s.option_seq where o.goods_seq=? and o.default_option = 'y' order by o.option_seq asc";
		$query = $this->db->query($sql,array($no));
		foreach($query->result_array() as $data){
			$optJoin = "";
			if( $data['option_title'] ) $data['option_divide_title'] = explode(',',$data['option_title']);

			if( $data['option_type'] ) $data['option_divide_type'] = explode(',',$data['option_type']);
			if( $data['code_seq'] ) $data['option_divide_codeseq'] = explode(',',$data['code_seq']);

			if( $data['newtype'] ) $data['divide_newtype'] = explode(',',$data['newtype']);

			if( $data['dayauto_type'] ) $data['dayauto_type_title'] = $this->dayautotype[$data['dayauto_type']];
			if( $data['dayauto_day'] ) $data['dayauto_day_title'] = $this->dayautoday[$data['dayauto_day']];
			if( $data['color']!='' ) $data['color'] = trim($data['color']);

			if( $data['option1']!='' ) $optJoin[] = $data['option1'];
			if( $data['option2']!='' ) $optJoin[] = $data['option2'];
			if( $data['option3']!='' ) $optJoin[] = $data['option3'];
			if( $data['option4']!='' ) $optJoin[] = $data['option4'];
			if( $data['option5']!='' ) $optJoin[] = $data['option5'];
			if( $optJoin ){
				$data['opts'] = $optJoin;
			}
			if( $data['optioncode1'] ) $optcodeJoin[] = $data['optioncode1'];
			if( $data['optioncode2'] ) $optcodeJoin[] = $data['optioncode2'];
			if( $data['optioncode3'] ) $optcodeJoin[] = $data['optioncode3'];
			if( $data['optioncode4'] ) $optcodeJoin[] = $data['optioncode4'];
			if( $data['optioncode5'] ) $optcodeJoin[] = $data['optioncode5'];
			if( $optcodeJoin ){
				$data['optcodes'] = $optcodeJoin;
			}

			$result[] = $data;
		}
		return $result;
	}


	public function get_default_option($no){
		$result = false;
		$query = "select o.*,s.badstock, s.stock, s.supply_price, s.reservation15, s.reservation25
			from fm_goods_option o,fm_goods_supply s where o.option_seq=s.option_seq and o.default_option='y' and o.goods_seq=?";
		$query = $this->db->query($query,array($no));
		foreach($query->result_array() as $data){
			$optJoin = "";
			$optcodeJoin = "";
			if( $data['option_title'] ) $data['option_divide_title'] = explode(',',$data['option_title']);

			if( $data['option_type'] ) $data['option_divide_type'] = explode(',',$data['option_type']);
			if( $data['code_seq'] ) $data['option_divide_codeseq'] = explode(',',$data['code_seq']);

			if( $data['newtype'] ) $data['divide_newtype'] = explode(',',$data['newtype']);

			if( $data['dayauto_type'] ) $data['dayauto_type_title'] = $this->dayautotype[$data['dayauto_type']];
			if( $data['dayauto_day'] ) $data['dayauto_day_title'] = $this->dayautoday[$data['dayauto_day']];
			if( $data['color']!='' ) $data['color'] = trim($data['color']);

			if( $data['option1']!='' ) $optJoin[] = $data['option1'];
			if( $data['option2']!='' ) $optJoin[] = $data['option2'];
			if( $data['option3']!='' ) $optJoin[] = $data['option3'];
			if( $data['option4']!='' ) $optJoin[] = $data['option4'];
			if( $data['option5']!='' ) $optJoin[] = $data['option5'];
			if( $optJoin ){
				$data['opts'] = $optJoin;
			}

			if( $data['optioncode1'] ) $optcodeJoin[] = $data['optioncode1'];
			if( $data['optioncode2'] ) $optcodeJoin[] = $data['optioncode2'];
			if( $data['optioncode3'] ) $optcodeJoin[] = $data['optioncode3'];
			if( $data['optioncode4'] ) $optcodeJoin[] = $data['optioncode4'];
			if( $data['optioncode5'] ) $optcodeJoin[] = $data['optioncode5'];
			if( $optcodeJoin ){
				$data['optcodes'] = $optcodeJoin;
			}
			$data['rstock'] = $data['stock']-$data[$this->reservation_field];
			$result = $data;
		}
		return $result;
	}

	public function get_tot_option($no){
		$result = false;
		$query = "select
			sum(s.stock) as stock,
			sum(case when s.stock <= 0 then 1 else 0 end) as stocknothing,
			sum(s.badstock) as badstock,
			sum(s.reservation15) as reservation15,
			sum(s.reservation25) as reservation25,
			sum(case when ( CONVERT(s.stock * 1, SIGNED) - CONVERT(s.".$this->reservation_field." * 1, SIGNED)) <= 0 then 1 else 0 end) as rstocknothing
			from fm_goods_option o,fm_goods_supply s where o.option_seq=s.option_seq and o.goods_seq=?";
		$query = $this->db->query($query,array($no));
		foreach($query->result_array() as $data){
			$data['rstock'] = $data['stock']-$data[$this->reservation_field];
			$result = $data;
		}
		return $result;
	}

	public function get_goods_addition ($no){
		$result = false;
		$sql = "select addition_seq, title, contents, type, code_seq , contents_title , linkage_val, 
				CASE WHEN type = 'model' THEN '모델명'
					WHEN type = 'brand' THEN '브랜드'
					WHEN type = 'manufacture' THEN '제조사'
					WHEN type = 'orgin' THEN '원산지'
					WHEN type = 'direct' THEN title
					ELSE title END AS name from fm_goods_addition where goods_seq=? order by addition_seq asc";
		$query = $this->db->query($sql,array($no));
		//$this->db->where('goods_seq', $no);
		//$query = $this->db->get('fm_goods_addition');
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	public function get_goods_icon ($no, $admin=null){
		$result = false;
		$today = date('Y-m-d');

		$sql = "select * from fm_goods_icon where goods_seq=?";

		if(empty($admin)){
			$sql .= " and (
				(ifnull(start_date,'0000-00-00') = '0000-00-00' and ifnull(end_date,'0000-00-00') = '0000-00-00')
				or
				(curdate() between start_date and end_date)
				or
				(start_date <= curdate() and ifnull(end_date,'0000-00-00') = '0000-00-00')
				or
				(end_date >= curdate() and ifnull(start_date,'0000-00-00') = '0000-00-00')
			)
			";
		}

		$sql .= " order by icon_seq asc";

		$query = $this->db->query($sql,$no);
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	//쇼셜쿠폰취소(환불)
	public function get_goods_socialcpcancel($no){
		$result = false;
		$sql = "select * from fm_goods_socialcp_cancel where goods_seq=?";
		$sql .= " order by seq asc limit 1";//% 취소(환불) 가능 1개
		$query = $this->db->query($sql,array($no));
		foreach($query->result_array() as $data){
			$result[] = $data;
			$firstpercent = $data['seq'];
		}

		$sql = "select * from fm_goods_socialcp_cancel where goods_seq=? and seq != ? ";
		$sql .= " order by socialcp_cancel_day desc";//% 공제 후 취소(환불) 가능
		$query = $this->db->query($sql,array($no,$firstpercent));
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	public function get_goods_suboption ($no){
		$result = false;
		$arr = array();
		$query = "select o.*,s.stock,s.badstock,s.supply_price, s.reservation15, s.reservation25  from fm_goods_suboption o,fm_goods_supply s where o.suboption_seq=s.suboption_seq and o.goods_seq=? order by o.suboption_seq asc";
		$query = $this->db->query($query,array($no));
		foreach($query->result_array() as $data){
			if(!in_array($data['suboption_title'],$arr)) $arr[] = $data['suboption_title'];
			list($key) = array_keys($arr,$data['suboption_title']);

			if	($data['consumer_price']){
				$data['supplyRate'] = floor($data['supply_price'] / $data['consumer_price'] * 100);
				$data['discountRate'] = 100 - floor($data['price'] / $data['consumer_price'] * 100);
			}
			$data['tax']		= floor($data['price'] - ($data['price'] / 1.1));
			$data['net_profit']	= $data['price'] - $data['supply_price'];

			$result[$key][] = $data;
		}
		return $result;
	}

	public function get_goods_image($no){
		$result = false;
		$this->db->where('goods_seq', $no);
		$this->db->order_by('cut_number asc, image_seq asc');
		$query = $this->db->get('fm_goods_image');
		foreach($query->result_array()  as $data){
			if(preg_match("/^\//",$data['image']) && !file_exists(ROOTPATH.$data['image'])) continue;
			$result[$data['cut_number']][$data['image_type']] = $data;
		}
		return $result;
	}

	public function get_goods_input($no){
		$result = false;
		$this->db->where('goods_seq', $no);
		$this->db->order_by("input_seq asc");
		$query = $this->db->get('fm_goods_input');
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}

	/*
	 상품(단품) 해당하는 배송정보를 가져온다. 가격비교 및 상품상세에서 사용
	 shipping_policy : goods,shop
	 goods_shipping_policy limited,unlimited  (shipping_unit > 0 ? limited :  unlimited)
	 unlimit_shipping_price : 합포장이 무제한인 경우 개별배송비(goods_shipping_cost)
	 limit_shipping_price : 개별배송비(basic_shipping_cost)
	 limit_shipping_ea 합포장 단위(shipping_unit)
	 limit_shipping_subprice 추가 합포장 배송비(add_shipping_cost)
	*/
	public function get_goods_delivery($goods,$ea=1){

		$delivery['policy'] = $goods['shipping_policy'];
		// 개별 배송비 계산
		if( $goods['shipping_policy']=='goods'){
			$delivery['price'] = 0;
			$delivery['type'] = 'delivery';
			$delivery['box_ea'] = 1;
			if( $goods['goods_shipping_policy'] == 'unlimit' ){
				if( $goods['unlimit_shipping_price'] > 0 ) $delivery['price'] = $goods['unlimit_shipping_price'];
			}else if( $goods['goods_shipping_policy'] == 'limit' ){
				if($ea > $goods['limit_shipping_ea']){
					$delivery['box_ea'] = ceil($ea / $goods['limit_shipping_ea']);
				}
				if( $goods['limit_shipping_price'] > 0 ){
					$delivery['price'] = $goods['limit_shipping_price'];
					if($ea > $goods['limit_shipping_ea']){
						$delivery['price'] += (ceil($ea / $goods['limit_shipping_ea']) - 1) * $goods['limit_shipping_subprice'];
					}
				}
			}
		}else{
			// 기본 배송비 계산
			$arr = array('delivery','quick','direct');
			foreach($arr as $code){
				if(isset($delivery['type'])) continue;
				$scode = "shipping".$code;
				$arrBasicPolicy = config_load($scode);

				if( $arrBasicPolicy[orderDeliveryFree] == 'free' ){

					if( $arrBasicPolicy['issueGoods'] &&  in_array($goods['goods_seq'],$arrBasicPolicy['issueGoods']) ){
						$free = true;
					}

					if( $goods['category_code'] ) foreach($goods['category_code'] as $catecd){
						if($arrBasicPolicy['issueCategoryCode'] && in_array($catecd,$arrBasicPolicy['issueCategoryCode'])){
							$free = true;
						}
					}

					if( $goods['brand_code'] ) foreach($goods['brand_code'] as $brandcd){
						if($arrBasicPolicy['issueBrandCode'] && in_array($brandcd,$arrBasicPolicy['issueBrandCode'])){
							$free = true;
						}
					}

					if( $arrBasicPolicy['exceptIssueGoods'] &&  in_array($goods['goods_seq'],$arrBasicPolicy['exceptIssueGoods']) ){
						$free = false;
					}


				}

				if($arrBasicPolicy['useYn'] == 'y'){
					$delivery['type'] = $code;
					$delivery['summary'] = $arrBasicPolicy['summary'];
					$delivery['price'] = 0;
					if($code == 'delivery'){
						switch($arrBasicPolicy['deliveryCostPolicy']){
							case "ifpay" :
								if($arrBasicPolicy['ifpayFreePrice'] > 0){
									$delivery['price'] = $arrBasicPolicy['ifpayDeliveryCost'];
									$delivery['free'] = $arrBasicPolicy['ifpayFreePrice'];
								}
								break;
							case "pay" :
								if($arrBasicPolicy['payDeliveryCost'] > 0){
									$delivery['price'] = $arrBasicPolicy['payDeliveryCost'];
								}
								break;
							case "free":
									$delivery['price'] = 0;
								break;
						}
					}else{
						$delivery['type'] = $code;
					}
				}

				if( $free ) $delivery['price'] = 0;
			}
		}
		return $delivery;
	}

	public function get_goods_list($arrNo,$imageType='list1'){

		$this->load->model('categorymodel');
		$this->load->library('sale');

		//--> sale library 할인 적용 사전값 전달
		if	($imageType == 'thumbScroll')	$applypage	= 'lately_scroll';
		else								$applypage	= 'list';
		$param['cal_type']				= 'list';
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<-- //sale library 할인 적용 사전값 전달

		$query = "select g.*,o.*,i.*, g.goods_seq as goods_seq  from
		fm_goods g
		left join fm_goods_image i on (g.goods_seq=i.goods_seq and i.cut_number=1 and i.image_type=?)
		left join fm_goods_option o on (g.goods_seq=o.goods_seq and o.default_option='y')
		where g.goods_seq in (".implode(',',$arrNo).")
		group by g.goods_seq order by field(g.goods_seq, ".implode(',',$arrNo).")
		";
		$query = $this->db->query($query,array($imageType));
		foreach($query->result_array() as $data){
			$query2 = "select * from fm_goods_icon where goods_seq=?";
			$query2 = $this->db->query($query2,$data['goods_seq']);
			foreach($query2->result_array() as $data2){
				$data['icons'][] = str_replace('.gif','',$data2);
			}

			$data['org_price']	= ($data['consumer_price'] > 0) ? $data['consumer_price'] : $data['price'];

			// 카테고리정보
			$tmparr2 = array();
			$categorys = $this->get_goods_category($data['goods_seq']);
			foreach($categorys as $val){
				$tmparr = $this->categorymodel->split_category($val['category_code']);
				foreach($tmparr as $cate) $tmparr2[] = $cate;
			}
			if($tmparr2){
				$tmparr2 = array_values(array_unique($tmparr2));
				$data['r_category']	= $tmparr2;
			}

			//----> sale library 적용
			unset($param, $sales);
			$param['consumer_price']		= $data['consumer_price'];
			$param['price']					= $data['price'];
			$param['total_price']			= $data['price'];
			$param['ea']					= 1;
			$param['category_code']			= $goods['r_category'];
			$param['goods_seq']				= $data['goods_seq'];
			$param['goods']					= $data;
			$this->sale->set_init($param);
			$sales	= $this->sale->calculate_sale_price($applypage);
			$data['sale_price']				= $sales['result_price'];
			$data['tot_reserve']			= $data['reserve'] + $sales['tot_reserve'];
			$this->sale->reset_init();
			unset($sales);
			//<---- sale library 적용

			$result[$data['goods_seq']] = $data;
		}

		return $result;
	}

	/* 상품 뷰 증가 */
	public function increase_page_view($no){
		$bind[] = $no;
		$query = "update fm_goods set page_view=page_view+1 where goods_seq=?";
		$this->db->query($query,$bind);

		/* 상품분석 수집 */
		$this->load->model('goodslog');
		$this->goodslog->add('view',$no);
	}

	/* 상품 리뷰 증가/차감 */
	public function goods_review_count($no, $type = 'plus'){
		$bind[] = $no;
		$query = "update fm_goods a set a.review_count= ifnull((SELECT count(*) FROM `fm_goods_review` WHERE find_in_set(a.goods_seq,goods_seq)),0)
		, a.review_sum = ifnull((SELECT sum(ifnull(score,0)) FROM `fm_goods_review` WHERE find_in_set(a.goods_seq,goods_seq)),0)
		where a.goods_seq=?";
		$this->db->query($query,$bind);
	}

	/* 상품 문의 증가/차감 */
	public function goods_qna_count($no, $type = 'plus'){
		$bind[] = $no;
		$query = "update fm_goods a set a.qna_count= ifnull((SELECT count(*) FROM `fm_goods_qna` WHERE find_in_set(a.goods_seq,goods_seq)),0)
		where a.goods_seq=?";
		$this->db->query($query,$bind);
	}


	/* 상품 like 증가 */
	public function goods_like_count($no, $count){
		$bind[] = $no;
		if( $this->__APP_ID__ == '455616624457601' && $this->__APP_VER__ == "1.0") {//기본앱 1.0버전
			$like_count = $count['like_count'] + $count['share_count'];
			$query = "update fm_goods set like_count='".$like_count."',fb_update='".date('Y-m-d')."' where goods_seq=?";
		}else{
			$query = "update fm_goods a set a.like_count= ifnull((SELECT count(*) FROM `fm_goods_fblike` WHERE a.goods_seq = goods_seq),0),fb_update='".date('Y-m-d')."' where a.goods_seq=?";
		}
		$this->db->query($query,$bind);
	}
	/* 상품 like 정보가져오기 */
	public function goods_like_viewer($no){ 
		$query = "SELECT like_count  FROM `fm_goods` where goods_seq='{$no}'"; 
		$query = $this->db->query($query);
		list($row) = $query->result_array();  
		return $row;
	}



	/* 사용자화면 상품리스트 */
	public function goods_list($sc){

		// ----- 기본 선언 ---- //
		$data	= array();
		if(!$sc['page'])			$sc['page']			= 1;
		if(!$sc['perpage'])			$sc['perpage']		= 10;
		if(!$sc['image_size'])		$sc['image_size']	= 'view';
		if($sc['category_code'])	$sc['category']		= $sc['category_code'];
		if($sc['brand_code'])		$sc['brand']		= $sc['brand_code'];
		if($sc['location_code'])	$sc['location']		= $sc['location_code'];
		if($sc['brand'] && !is_array($sc['brand']))	$sc['brands'][]	= $sc['brand'];
		$platform	= 'P';

		// ----- 기본 로드 ---- //
		$this->load->model('membermodel');
		$this->load->model('categorymodel');
		$this->load->library('sale');

		// 회원 등급
		$member_group		= '0';
		if($this->userInfo['group_seq'] > 0){
			$member_group			= $this->userInfo['group_seq'];
			$sc['member_group_seq']	= $this->userInfo['group_seq'];
		}

		//--> sale library 할인 적용 사전값 전달
		$param['cal_type']				= 'list';
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $member_group;
		$this->sale->set_init($param);
		$this->sale->preload_set_config('list');
		//<-- //sale library 할인 적용 사전값 전달

		// ----- 기본 Query ---- //
		$sqlSelectClause	= "select
								g.goods_seq,
								g.sale_seq,
								g.goods_status,
								g.goods_kind,
								g.socialcp_event,
								g.goods_name,
								g.goods_code,
								g.summary,
								g.string_price_use,
								g.string_price,
								g.string_price_link,
								g.string_price_link_url,
								g.member_string_price_use,
								g.member_string_price,
								g.member_string_price_link,
								g.member_string_price_link_url,
								g.allmember_string_price_use,
								g.allmember_string_price,
								g.allmember_string_price_link,
								g.allmember_string_price_link_url,
								g.file_key_w,
								g.file_key_i,
								g.videotmpcode,
								g.videousetotal,
								g.purchase_ea,
								g.shipping_policy,
								g.review_count,
								g.review_sum,
								g.reserve_policy,
								g.multi_discount_use, 
								g.multi_discount_ea, 
								g.multi_discount, 
								g.multi_discount_unit, 
								o.consumer_price,
								o.price,
								o.reserve_rate,
								o.reserve_unit,
								o.reserve,
								if(g.goods_shipping_policy='unlimit',unlimit_shipping_price,limit_shipping_price) as goods_shipping_price,
								(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=1 and image_type ='{$sc['image_size']}' limit 1) as image,
								(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=2 and image_type ='{$sc['image_size']}' limit 1) as image2,
								(select count(*) from fm_goods_image where goods_seq=g.goods_seq and image_type ='view') as image_cnt,
								(select group_concat(ifnull(color,'')) from fm_goods_option where goods_seq=g.goods_seq) as colors,
								l.category_link_seq,
								l.sort,
								(select category_code from fm_category_link where link=1 and goods_seq=g.goods_seq limit 1) as category_code,
								gls.brand_title	as brand_title, 
								gls.brand_title_eng	as brand_title_eng, 
								gls.brand_code	as brand_code, 
								gls.today_icon	as icons, 
								gls.price_".date('H')."	as sale_price, 
								gls.today_solo_start, 
								gls.today_solo_end, 
								gls.price_00, 
								gls.price_01, 
								gls.price_02, 
								gls.price_03, 
								gls.price_04, 
								gls.price_05, 
								gls.price_06, 
								gls.price_07, 
								gls.price_08, 
								gls.price_09, 
								gls.price_10, 
								gls.price_11, 
								gls.price_12, 
								gls.price_13, 
								gls.price_14, 
								gls.price_15, 
								gls.price_16, 
								gls.price_17, 
								gls.price_18, 
								gls.price_19, 
								gls.price_20, 
								gls.price_21, 
								gls.price_22, 
								gls.price_23 ";
		$sqlFromClause		= " from
									fm_goods_option o,
									fm_goods g 
									left join fm_goods_list_summary as gls 
									on ( g.goods_seq = gls.goods_seq and gls.platform = '".$platform."' )
									left join fm_category_link l on l.goods_seq=g.goods_seq
									left join fm_category_group as cg on l.category_code = cg.category_code ";
		$sqlWhereClause		= "where 
									g.goods_type = 'goods' and 
									o.goods_seq = g.goods_seq and 
									o.default_option ='y' ";
		$sqlGroupbyClause	= " group by g.goods_seq ";
		$sqlOrderbyClause	= " order by g.goods_seq desc, g.goods_seq desc";
		$sqlLimitClause		= "";


		// ----- 검색조건 추가 ---- //

		// 모바일 요약페이지에서는 큰이미지 사용
		if(!empty($sc['list_style']) && $sc['list_style']=='mobile_zoom'){
			$sc['image_size']			= 'large';
		}

		// ----- 쿼리 추가 ---- //
		// 상품 자동노출일때
		if(!empty($sc) && $sc['auto_use'] == 'y'){
			if($sc['auto_term_type'] == 'relative') {
				$auto_start_date = date('Y-m-d',strtotime("-{$sc['auto_term']} day"));
				$auto_end_date = date('Y-m-d');
			}else{
				$auto_start_date = $sc['auto_start_date'];
				$auto_end_date = $sc['auto_end_date'];
			}

			// ------> 요기를 수정함으로써 40 ~ 70초 쿼리를 3 ~ 7초 쿼리로 약 30 ~ 60초 가량 이득봄
			$gstSubSql	= "select goods_seq, sum(cnt) cnt from fm_stats_goods where type='[:STAT_TYPE:]' and stats_date between '{$auto_start_date}' and '{$auto_end_date}' group by goods_seq";
			switch($sc['auto_order']){
				case "deposit":
				case "best":
					$gstSubSql			= str_replace('[:STAT_TYPE:]', 'deposit', $gstSubSql);
					$sqlFromClause		.= " left join (".$gstSubSql.") gst on g.goods_seq=gst.goods_seq ";
					$sqlOrderbyClause	= " order by gst.cnt desc, g.purchase_ea desc";
				break;
				case "deposit_price":
					$gstSubSql			= str_replace('[:STAT_TYPE:]', 'deposit_price', $gstSubSql);
					$sqlFromClause		.= " left join (".$gstSubSql.") gst on g.goods_seq=gst.goods_seq ";
					$sqlOrderbyClause	= " order by gst.cnt desc, g.purchase_ea desc";
				break;
				case "popular":
				case "view":
					$gstSubSql			= str_replace('[:STAT_TYPE:]', 'view', $gstSubSql);
					$sqlFromClause		.= " left join (".$gstSubSql.") gst on g.goods_seq=gst.goods_seq ";
					$sqlOrderbyClause	= " order by gst.cnt desc, g.page_view desc";
				break;
				case "review":
					$gstSubSql			= str_replace('[:STAT_TYPE:]', 'review', $gstSubSql);
					$sqlFromClause		.= " left join (".$gstSubSql.") gst on g.goods_seq=gst.goods_seq ";
					$sqlOrderbyClause	= " order by gst.cnt desc, g.review_count desc";
				break;
				case "cart":
					$gstSubSql			= str_replace('[:STAT_TYPE:]', 'cart', $gstSubSql);
					$sqlFromClause		.= " left join (".$gstSubSql.") gst on g.goods_seq=gst.goods_seq ";
					$sqlOrderbyClause	= " order by gst.cnt desc";
				break;
				case "wish":
					$gstSubSql			= str_replace('[:STAT_TYPE:]', 'wish', $gstSubSql);
					$sqlFromClause		.= " left join (".$gstSubSql.") gst on g.goods_seq=gst.goods_seq ";
					$sqlOrderbyClause	= " order by gst.cnt desc";
				break;
				case "newly":
				default:
					$sqlWhereClause		.= " and g.regist_date between '{$auto_start_date} 00:00:00' and '{$auto_end_date} 23:59:59'";
					$sqlOrderbyClause	= " order by g.regist_date desc, g.goods_seq desc";
				break;
				case "discount":
					$sqlWhereClause		.= " and o.consumer_price>0 ";
					$sqlOrderbyClause	= " order by o.price/o.consumer_price asc, o.price desc";
				break;
			}

			//이미지영역 동영상여부
			if($sc['auto_file_key_w'])
				$sqlWhereClause		.= " and ( g.file_key_w != '' ) ";
			//이미지영역 동영상 있으면서 노출여부 포함
			if($sc['auto_file_key_w'] && $sc['auto_video_use_image'])
				$sqlWhereClause		.= " and ( g.video_use = '{$sc['auto_video_use_image']}') ";
			//설명영역 동영상여부
			if($sc['auto_videototal'])
				$sqlWhereClause		.= " and ( g.videototal > 0 ) ";

			if($sc['selectGoodsView']) {
				$sqlWhereClause		.= " and g.goods_view = '{$sc['selectGoodsView']}' ";
			}else{
				$sqlWhereClause		.= " and g.goods_view = 'look' ";
			}

			// 해당 이벤트 상품 추출 ( 이벤트 대상 상품 테이블을 추가하여 속도개선 필요 )
			if(!empty($sc['selectEvent']) || !empty($sc['selectEventBenefits'])){
				$query = $this->db->query("select * from fm_event where event_seq=?",$sc['selectEvent']);
				$eventinfo = $query->row_array();

				if($eventinfo['goods_rule']=='all'){
					$sqlWhereClause .= " and g.goods_seq not in (select goods_seq from fm_event_choice where choice_type='except_goods' and event_seq = '{$sc['selectEvent']}' and event_benefits_seq = '{$sc['selectEventBenefits']}')";
					$sqlWhereClause .= " and g.goods_seq not in (select ec.goods_seq from fm_event_choice e inner join fm_category_link ec on e.category_code=ec.category_code where e.choice_type='except_category' and e.event_seq = '{$sc['selectEvent']}' and e.event_benefits_seq = '{$sc['selectEventBenefits']}' group by ec.goods_seq)";
				}

				if($eventinfo['goods_rule']=='goods_view'){
					$sqlFromClause .= " left join fm_event_choice e_goods on (g.goods_seq = e_goods.goods_seq and e_goods.choice_type='goods')";
					$sqlWhereClause .= " and e_goods.event_seq = '{$sc['selectEvent']}' ";
					if($sc['selectEventBenefits']){
						$sqlWhereClause .= " and e_goods.event_benefits_seq = '{$sc['selectEventBenefits']}' ";
					}
				}

				if($eventinfo['goods_rule']=='category'){
					$sqlWhereClause .= " and g.goods_seq in (select ec.goods_seq from fm_event_choice e inner join fm_category_link ec on e.category_code=ec.category_code where e.choice_type='category' and e.event_seq = '{$sc['selectEvent']}' and e.event_benefits_seq = '{$sc['selectEventBenefits']}' group by ec.goods_seq)";
					$sqlWhereClause .= " and g.goods_seq not in (select goods_seq from fm_event_choice where choice_type='except_goods' and event_seq = '{$sc['selectEvent']}' and event_benefits_seq = '{$sc['selectEventBenefits']}')";
					$sqlWhereClause .= " and g.goods_seq not in (select ec.goods_seq from fm_event_choice e inner join fm_category_link ec on e.category_code=ec.category_code where e.choice_type='except_category' and e.event_seq = '{$sc['selectEvent']}' and e.event_benefits_seq = '{$sc['selectEventBenefits']}' group by ec.goods_seq)";
				}
			}

			// 사은품과 상품의 1:N 구조로 where절 in query로 변경할 수 있게 수정해야 함.
			if(!empty($sc['selectGift'])){
				$sqlFromClause .= " left join fm_gift_choice gf on g.goods_seq = gf.goods_seq";
				$sqlWhereClause .= " and gf.gift_seq = '{$sc['selectGift']}' ";
			}
		}else if(!empty($sc['display_seq'])){
			if(!isset($sc['display_tab_index'])) $sc['display_tab_index'] = 0;
			$sqlFromClause .= " inner join fm_design_display_tab_item on (g.goods_seq=fm_design_display_tab_item.goods_seq and fm_design_display_tab_item.display_seq='{$sc['display_seq']}' and fm_design_display_tab_item.display_tab_index='{$sc['display_tab_index']}')";
			$sqlOrderbyClause = " order by fm_design_display_tab_item.display_tab_item_seq asc";
			$sqlWhereClause .= " and g.goods_view='look' ";
		}else{
			$sqlWhereClause .= " and g.goods_view='look' ";
		}

		// 20130408 : 자동노출&페이징정렬시 문제발생하여 임시로 추가
		switch($sc['sort']){
			case "popular":
				if(!empty($sc['category']))
					$sqlOrderbyClause	= " order by l.sort asc, g.regist_date desc ";
				elseif(!empty($sc['brand']))
					$sqlOrderbyClause	= " order by bl.sort asc, g.regist_date desc ";
				else
					$sqlOrderbyClause	= " order by g.page_view desc ";
			break;
			case "newly":
				$sqlOrderbyClause =" order by g.regist_date desc, g.goods_seq desc";
			break;
			case "popular_sales":
				$sqlOrderbyClause =" order by g.purchase_ea desc, g.goods_seq desc";
			break;
			case "low_price":
				$sqlOrderbyClause =" order by o.price asc, g.goods_seq desc";
			break;
			case "high_price":
				$sqlOrderbyClause =" order by o.price desc, g.goods_seq desc";
			break;
			case "review":
				$sqlOrderbyClause =" order by g.review_count desc, g.goods_seq desc";
			break;
		}

		if($sc['goods_status']) {
			if(is_array($sc['goods_status']))
				$sqlWhereClause		.= " and g.goods_status in ('".implode("','",$sc['goods_status'])."') ";
		}

		if(!empty($sc['goods_seq_string'])){
			$arr_goods_seq_string	= explode(',',preg_replace("/[^0-9,]/","",$sc['goods_seq_string']));
			$sqlWhereClause			.= " and g.goods_seq in ('".implode("','",$arr_goods_seq_string)."')";
		}

		if(!empty($sc['goods_seq_exclude'])){
			$sqlWhereClause			.= " and g.goods_seq != '".$sc['goods_seq_exclude']."' ";
		}

		if(!empty($sc['category'])){
			$sqlWhereClause			.= " and l.category_code = '".$sc['category']."'";
		}

		if(!empty($sc['color'])){
			$sqlFromClause			.= " inner join fm_goods_option oc on oc.goods_seq=g.goods_seq and ifnull(oc.color,'') = '".$sc['color']."' ";
		}

		if(!empty($sc['brands'])){
			$sqlSelectClause		.= " ,bl.category_link_seq,bl.sort,bl.category_code ";
			$sqlFromClause			.= " left join fm_brand_link bl on bl.goods_seq=g.goods_seq ";
			if(!empty($sc['member_group_seq'])){
				$sqlFromClause		.= " left join fm_brand_group as bg on bl.category_code = bg.category_code ";
			}
			$sqlWhereClause			.= " and bl.category_code in ('".implode("','",$sc['brands'])."')";
		}

		if(!empty($sc['location'])){
			$sqlSelectClause		.= ",ll.location_link_seq,ll.sort,ll.location_code ";
			$sqlFromClause			.= " left join fm_location_link ll on ll.goods_seq=g.goods_seq ";
			$sqlWhereClause			.= " and ll.location_code = '".$sc['location']."'";
		}

		if(!$sc['admin_category']  && !defined('__ISADMIN__')){
			if(!empty($sc['member_group_seq'])){
				$sqlWhereClause		.= " and ( cg.group_seq is null or find_in_set('".$sc['member_group_seq']."',concat_ws(',',cg.group_seq) ) )";
			}else{
				$sqlWhereClause		.= " and ( cg.group_seq is null )";
			}
		}

		if(!empty($sc['list_goods_status'])) {
			$sqlWhereClause			.= " and g.goods_status in ('".str_replace('|',"','",$sc['list_goods_status'])."')";
		}

		if($sc['start_price']){
			$sqlWhereClause			.= " and o.price >= '{$sc['start_price']}' ";
		}

		if($sc['end_price']){
			$sqlWhereClause			.= " and o.price <= '{$sc['end_price']}' ";
		}

		if(!empty($sc['search_text'])){
			if(!is_array($sc['search_text']))	$sc['search_text']	= array($sc['search_text']);
			if((!empty($sc['insearch']) && $sc['insearch']==1) && $_GET['old_search_text']){
				$arr_search_text	= explode("\n",$_GET['old_search_text']);
				foreach($arr_search_text as $search_text){
					if(trim($search_text) && !in_array($search_text,$sc['search_text'])){
						$sc['search_text'][]	= trim($search_text);
					}
				}
			}

			foreach($sc['search_text'] as $search_text){
				$search_text = str_replace(' ', '',addslashes($search_text));
				switch($sc['search_text']){
					case 'goods_name':
						$sqlWhereClause		.= " and REPLACE(g.goods_name,' ','') like '%{$search_text}%'";
					break;
					case 'goods_code':
						$sqlWhereClause		.= " and g.goods_code like '%{$search_text}%'";
					break;
					case 'summary':
						$sqlWhereClause		.= " and REPLACE(g.summary,' ','') like '%{$search_text}%'";
					break;
					case 'keyword':
						$sqlWhereClause		.= " and REPLACE(g.keyword,' ','') like '%{$search_text}%'";
					break;
					default:
						$sqlWhereClause		.= " and (
													REPLACE(g.goods_name,' ','') like '%{$search_text}%'
													or g.goods_seq = '{$search_text}'
													or g.goods_code like '%{$search_text}%'
													or REPLACE(g.summary,' ','') like '%{$search_text}%'
													or REPLACE(g.keyword,' ','') like '%{$search_text}%'
													or (
														 select group_concat(sc_b.title,sc_b.title_eng) from fm_brand sc_b
														 inner join fm_brand_link sc_b2
														 on sc_b.category_code=sc_b2.category_code
														 where sc_b2.goods_seq=g.goods_seq
													) like '%{$search_text}%' ) ";
					break;
				}
			}
		}

		if(!empty($this->userInfo['member_seq'])){
			$sqlSelectClause	.= ",if(w.wish_seq is not null,1,0) as wish ";
			$sqlFromClause		.= " left join fm_goods_wish as w on w.goods_seq=g.goods_seq and w.member_seq='{$this->userInfo['member_seq']}' ";
		}

		if(!empty($sc['relation'])){
			$sqlFromClause		.= " inner join fm_goods_relation r on g.goods_seq=r.relation_goods_seq";
			$sqlWhereClause		.= " and r.goods_seq = '{$sc['relation']}'";
			$sqlOrderbyClause	= " order by r.relation_seq asc ";
		}

		if(is_array($sc['src_seq']) && count($sc['src_seq']) > 0){
			$sqlWhereClause		.= " and g.goods_seq in ('".implode("', '", $sc['src_seq'])."') ";
		}

		if(!empty($sc['limit'])){
			$sqlLimitClause = "limit {$sc['limit']}";
		}

		$sql = "
			{$sqlSelectClause}
			{$sqlFromClause}
			{$sqlWhereClause}
			{$sqlGroupbyClause}
			{$sqlOrderbyClause}
			{$sqlLimitClause}
		";
		if($sqlLimitClause){
			$query				= $this->db->query($sql);
			$result['record']	= $query->result_array();
		}else{
			$result				= select_page($sc['perpage'],$sc['page'],10,$sql,array());
		}

		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');
		if($result['record']){
			foreach($result['record'] as $k => $data){

				// 카테고리정보
				$tmparr2	= array();
				$categorys	= $this->get_goods_category($data['goods_seq']);
				foreach($categorys as $key => $val){
					$tmparr	= $this->categorymodel->split_category($val['category_code']);
					foreach($tmparr as $cate) $tmparr2[]	= $cate;
				}
				if($tmparr2){
					$tmparr2				= array_values(array_unique($tmparr2));
					$data['r_category']		= $tmparr2;
				}

				//--> sale library 적용
				unset($param, $reserve);
				$param['consumer_price']		= $data['consumer_price'];
				$param['price']					= $data['price'];
				$param['total_price']			= $data['price'];
				$param['ea']					= 1;
				$param['category_code']			= $data['r_category'];
				$param['goods_seq']				= $data['goods_seq'];
				$param['goods']					= $data;
				$this->sale->set_init($param);
				$sales	= $this->sale->calculate_sale_price('list');

				$data['sale_price']						= $sales['result_price'];
				$result['record'][$k]['org_price']		= ($data['consumer_price']) ? $data['consumer_price'] : $data['price'];
				$result['record'][$k]['sale_per']		= $sales['sale_per'];
				$result['record'][$k]['sale_price']		= $sales['result_price'];
				$result['record'][$k]['eventEnd']		= $sales['eventEnd'];
				$result['record'][$k]['event_text']		= $sales['text_list']['event'];
				$result['record'][$k]['event_order_ea']	= $sales['event_order_ea'];
				$result['record'][$k]['reserve']		= $this->get_reserve_with_policy($data['reserve_policy'],$sales['result_price'],$cfg_reserve['default_reserve_percent'],$data['reserve_rate'],$data['reserve_unit'],$data['reserve']) + $sales['tot_reserve'];
				$this->sale->reset_init();
				//<-- sale library 적용

				$result['record'][$k]['string_price'] = get_string_price($data);
				$result['record'][$k]['string_price_use'] = 0;
				if($result['record'][$k]['string_price']!='') $result['record'][$k]['string_price_use'] = 1;

				// 아이콘에서 .gif 제거 및 이미지 크기 추출
				$result['record'][$k]['icons']	= str_replace('.gif','',$data['icons']);
				if	(file_exists(ROOTPATH.$data['image']))
					$result['record'][$k]['image_size']	= getimagesize(ROOTPATH.$data['image']);
				if(!empty($result['record'][$k]['icons']) && !is_array($result['record'][$k]['icons'])){
					$result['record'][$k]['icons']		= explode(",",$result['record'][$k]['icons']);
				}
			}
		}

		$result['page']['querystring'] = get_args_list();
		return $result;
	}

	/* 상품 옵션 재고 */
	public function get_goods_option_stock($goods_seq,$option1,$option2,$option3,$option4,$option5){
		$where_val[] = $goods_seq;
		$where[] = "o.goods_seq=?";
		if($option1!=''){
			$where[] = "o.option1=?";
			$where_val[] = $option1;
		}
		if($option2!=''){
			$where[] = "o.option2=?";
			$where_val[] = $option2;
		}
		if($option3!=''){
			$where[] = "o.option3=?";
			$where_val[] = $option3;
		}
		if($option4!=''){
			$where[] = "o.option4=?";
			$where_val[] = $option4;
		}
		if($option5!=''){
			$where[] = "o.option5=?";
			$where_val[] = $option5;
		}

		if($option1=='' && $option2=='' && $option3=='' && $option4=='' && $option5==''){
			$where[] = "o.option1=''";
			$where[] = "o.option2=''";
			$where[] = "o.option3=''";
			$where[] = "o.option4=''";
			$where[] = "o.option5=''";
		}

		$where_str = " and ". implode(' and ',$where);
		$query = "select sum(s.stock) stock from fm_goods_option o,fm_goods_supply s where o.option_seq=s.option_seq ".$where_str;
		$query = $this->db->query($query,$where_val);
		$data = $query->result_array();
		return $data[0]['stock'];
	}

	/* 상품 옵션 가격 */
	public function get_goods_option_price($goods_seq,$option1,$option2,$option3,$option4,$option5){
		$where_val[] = $goods_seq;
		$where[] = "goods_seq=?";
		if($option1!=''){
			$where[] = "option1=?";
			$where_val[] = $option1;
		}
		if($option2!=''){
			$where[] = "option2=?";
			$where_val[] = $option2;
		}
		if($option3!=''){
			$where[] = "option3=?";
			$where_val[] = $option3;
		}
		if($option4!=''){
			$where[] = "option4=?";
			$where_val[] = $option4;
		}
		if($option5!=''){
			$where[] = "option5=?";
			$where_val[] = $option5;
		}

		if($option1=='' && $option2=='' && $option3=='' && $option4=='' && $option5==''){
			$where[] = "option1=''";
			$where[] = "option2=''";
			$where[] = "option3=''";
			$where[] = "option4=''";
			$where[] = "option5=''";
		}

		$where_str = implode(' and ',$where);

		$query = "select price,reserve from fm_goods_option where ".$where_str." limit 1";
		$query = $this->db->query($query,$where_val);
		$data = $query->result_array();
		return array($data[0]['price'],$data[0]['reserve']);
	}

	/* 상품 옵션 코드 */
	public function get_goods_option_code($goods_seq,$option1,$option2,$option3,$option4,$option5){
		$where_val[] = $goods_seq;
		$where[] = "goods_seq=?";
		if($option1!=''){
			$where[] = "option1=?";
			$where_val[] = $option1;
		}
		if($option2!=''){
			$where[] = "option2=?";
			$where_val[] = $option2;
		}
		if($option3!=''){
			$where[] = "option3=?";
			$where_val[] = $option3;
		}
		if($option4!=''){
			$where[] = "option4=?";
			$where_val[] = $option4;
		}
		if($option5!=''){
			$where[] = "option5=?";
			$where_val[] = $option5;
		}

		if($option1=='' && $option2=='' && $option3=='' && $option4=='' && $option5==''){
			$where[] = "option1=''";
			$where[] = "option2=''";
			$where[] = "option3=''";
			$where[] = "option4=''";
			$where[] = "option5=''";
		}

		$where_str = implode(' and ',$where);
		$query = "select * from fm_goods_option where ".$where_str." limit 1";
		$query = $this->db->query($query,$where_val);
		$data = $query->result_array();

		$optioninfo = array($data[0]['optioncode1'],$data[0]['optioncode2'],$data[0]['optioncode3'],$data[0]['optioncode4'],$data[0]['optioncode5'],$data[0]['color'],$data[0]['zipcode'],$data[0]['address_type'],$data[0]['address'],$data[0]['address_street'],$data[0]['addressdetail'],$data[0]['biztel'],$data[0]['coupon_input'],$data[0]['codedate'],$data[0]['sdayinput'],$data[0]['fdayinput'],$data[0]['dayauto_type'],$data[0]['sdayauto'],$data[0]['fdayauto'],$data[0]['dayauto_day'],$data[0]['newtype'],$data[0]['address_commission']);
		return $optioninfo;
	}


	/* 상품 서브옵션 재고 */
	public function get_goods_suboption_stock($goods_seq,$title,$suboption){
		$query = "select s.stock from fm_goods_suboption o,fm_goods_supply s where o.goods_seq=? and o.suboption_seq=s.suboption_seq and o.suboption_title=? and o.suboption=? limit 1";
		$query = $this->db->query($query,array($goods_seq,$title,$suboption));
		$data = $query->result_array();
		return $data[0]['stock'];
	}

	/* 상품 서브옵션 코드 */
	public function get_goods_suboption_code($goods_seq,$title,$suboption){
		$query = "select * from fm_goods_suboption  where goods_seq=? and suboption_title=? and suboption=? limit 1";
		$query = $this->db->query($query,array($goods_seq,$title,$suboption));
		$data = $query->result_array();

		$suboptioninfo = array($data[0]['suboption_code'],$data[0]['color'],$data[0]['zipcode'],$data[0]['address_type'],$data[0]['address'],$data[0]['address_street'],$data[0]['addressdetail'],$data[0]['biztel'],$data[0]['coupon_input'],$data[0]['codedate'],$data[0]['sdayinput'],$data[0]['fdayinput'],$data[0]['dayauto_type'],$data[0]['sdayauto'],$data[0]['fdayauto'],$data[0]['dayauto_day'],$data[0]['newtype']);
		return $suboptioninfo;
	}

	/* 상품 리스트 */
	public function admin_goods_list($sc) {

		$CI =& get_instance();
		if(!isset($_GET['page']))$_GET['page'] = 1;

		### goods_type
		if( !empty($sc['goods_type']) )
		{
			$where[] = " C.goods_type = '{$sc['goods_type']}' ";
		}

		if( !empty($sc['goods_kind']) ){
			if(!is_array($sc['goods_kind'])) $sc['goods_kind'] = array($sc['goods_kind']);
			$where[] = " C.goods_kind in ('".implode("','",$sc['goods_kind'])."') ";//일반상품
		}else{
			if( SOCIALCPUSE === true ) {
				$where[] = " C.goods_kind = 'coupon' ";//쇼셜쿠폰상품
			}else{
				$where[] = " C.goods_kind = 'goods' ";//일반상품
			}
		}

		### 카테고리 미연결상품
		if( !empty($_GET['goods_category_no']) ){
			$where[] = " (SELECT count(*) FROM fm_category_link E WHERE E.goods_seq = C.goods_seq) = 0";
		}

		### 브랜드 미연결상품
		if( !empty($_GET['goods_brand_no']) ){
			$where[] = "(SELECT count(*) FROM fm_brand_link E WHERE E.goods_seq = C.goods_seq) = 0 ";
		}

		### 지역 미연결상품
		if( !empty($_GET['goods_location_no']) ){
			$where[] = "(SELECT count(*) FROM fm_location_link E WHERE E.goods_seq = C.goods_seq) = 0 ";
		}

		### 쿠폰상품그룹
		if( !empty($sc['social_goods_group']) && !empty($sc['social_goods_group_name']) )
		{
			$where[] = " C.social_goods_group = '{$sc['social_goods_group']}' ";
		}

		###
		if($_GET['keyword']=='상품명, 상품코드' || $_GET['keyword']=='사은품명, 상품코드') unset($_GET['keyword']);
		if( !empty($_GET['keyword'])) {
			$keyword = trim(addslashes(str_replace(' ','',$_GET['keyword'])));
			$where[] = " (
				REPLACE(C.goods_name,' ','') like '%{$keyword}%'
				or C.goods_code like '%{$keyword}%'
				or C.goods_seq = '{$keyword}'
				or REPLACE(C.summary,' ','') like '%{$keyword}%'
				or REPLACE(C.keyword,' ','') like '%{$keyword}%'
			)";
		}//고유번호,상품명,상품 코드,간략 설명,상품 검색 태그

		### CATEGORY
		$tmp_link_str = !empty($_GET['goods_category']) ? " and link=1 " : "";
		if( !empty($_GET['category4']) ){
			$where[] = "C.goods_seq in (select goods_seq from fm_category_link where category_code='{$_GET[category4]}' {$tmp_link_str})";
		}else if( !empty($_GET['category3']) ){
			$where[] = "C.goods_seq in (select goods_seq from fm_category_link where category_code='{$_GET[category3]}' {$tmp_link_str})";
		}else if( !empty($_GET['category2']) ){
			$where[] = "C.goods_seq in (select goods_seq from fm_category_link where category_code='{$_GET[category2]}' {$tmp_link_str})";
		}else if( !empty($_GET['category1']) ){
			$where[] = "C.goods_seq in (select goods_seq from fm_category_link where category_code='{$_GET[category1]}' {$tmp_link_str})";
		}

		### BRAND
		$tmp_link_str = !empty($_GET['goods_brand']) ? " and link=1 " : "";
		if( !empty($_GET['brands4']) ){
			$where[] = " C.goods_seq in (select goods_seq from fm_brand_link where category_code='{$_GET[brands4]}' {$tmp_link_str})";
		}else if( !empty($_GET['brands3']) ){
			$where[] = " C.goods_seq in (select goods_seq from fm_brand_link where category_code='{$_GET[brands3]}' {$tmp_link_str})";
		}else if( !empty($_GET['brands2']) ){
			$where[] = " C.goods_seq in (select goods_seq from fm_brand_link where category_code='{$_GET[brands2]}' {$tmp_link_str})";
		}else if( !empty($_GET['brands1']) ){
			$where[] = "C.goods_seq in (select goods_seq from fm_brand_link where category_code='{$_GET[brands1]}' {$tmp_link_str})";
		}

		### LOCATION
		$tmp_link_str = !empty($_GET['goods_location']) ? " and link=1 " : "";
		if( !empty($_GET['location4']) ){
			$where[] = " C.goods_seq in (select goods_seq from fm_location_link where location_code='{$_GET[location4]}' {$tmp_link_str})";
		}else if( !empty($_GET['location3']) ){
			$where[] = " C.goods_seq in (select goods_seq from fm_location_link where location_code='{$_GET[location3]}' {$tmp_link_str})";
		}else if( !empty($_GET['location2']) ){
			$where[] = " C.goods_seq in (select goods_seq from fm_location_link where location_code='{$_GET[location2]}' {$tmp_link_str})";
		}else if( !empty($_GET['location1']) ){
			$where[] = "C.goods_seq in (select goods_seq from fm_location_link where location_code='{$_GET[location1]}' {$tmp_link_str})";
		}

		//동영상
		if( $_GET['file_key_w'] ){
			$where[] = " ( C.file_key_w != '') ";// or file_key_w is not null

			if( !empty($_GET['video_use']) && $_GET['video_use'] !="전체" ){
				$where[] = "C.video_use = '{$_GET['video_use']}' ";
			}

		}
		if( $_GET['videototal'] ){
			$where[] = "C.videototal > 0 ";
		}

		### DATE
		if( !empty($_GET['sdate']) && !empty($_GET['edate']) ){
			$where[] = "C.{$_GET['date_gb']} between '{$_GET['sdate']} 00:00:00' and '{$_GET['edate']} 23:59:59' ";
		}else if( !empty($_GET['sdate']) && empty($_GET['edate']) ){
			$where[] = "C.{$_GET['date_gb']} >= '{$_GET['sdate']}' ";
		}else if( empty($_GET['sdate']) && !empty($_GET['edate']) ){
			$where[] = "C.{$_GET['date_gb']} <= '{$_GET['edate']}' ";
		}

		### PRICE
		if( $_GET['sprice']!='' ){
			//$where[] = "C.goods_seq in (select goods_seq from fm_goods_option where {$_GET['price_gb']} >= '{$_GET['sprice']}' and default_option='y')";
			$where[] = "D.{$_GET['price_gb']} >= '{$_GET['sprice']}'";
		}
		if( $_GET['eprice']!='' ){
			//$where[] = "C.goods_seq in (select goods_seq from fm_goods_option where {$_GET['price_gb']} <= '{$_GET['eprice']}' and default_option='y')";
			$where[] = "D.{$_GET['price_gb']} <= '{$_GET['eprice']}'";
		}


		### STOCK
		//옵션별 재고검색
		if($_GET['optstock']){
			if( $_GET['sstock']!='' ){
				$where[] = "(select count(*)  from fm_goods_supply where option_seq is NOT NULL and goods_seq=C.goods_seq and stock >='{$_GET['sstock']}') > 0";
			}
			if( $_GET['estock']!='' ){
				$where[] = "(select count(*)  from fm_goods_supply where option_seq is NOT NULL and goods_seq=C.goods_seq and stock <='{$_GET['estock']}') > 0";
			}
		}else{
		//총재고검색
			if( $_GET['sstock']!='' ){
				$where[] = "C.tot_stock >='{$_GET['sstock']}' ";
			}
			if( $_GET['estock']!='' ){
				$where[] = "C.tot_stock <='{$_GET['estock']}' ";
			}

			//$where[] = "C.goods_seq in (select goods_seq  from fm_goods_supply where option_seq is NOt NULL group by goods_seq having  sum(stock) <='{$_GET['estock']}') ";
		}

		### PAGE_VIEW
		if( $_GET['spage_view']!='' ){
			$where[] = "C.page_view >= '{$_GET['spage_view']}' ";
		}
		if( $_GET['epage_view']!='' ){
			$where[] = "C.page_view <= '{$_GET['epage_view']}' ";
		}

		### 청약철회여부 0:가능 1:불가능
		if( isset($_GET['cancel_type'])){
			if($_GET['cancel_type'][1] == '1' ){
				$where[] = " (  C.cancel_type = '". implode('\' OR C.cancel_type= \'',$_GET['cancel_type'])."' ) ";
			}else{
				$where[] = " (  C.cancel_type = '0' or C.cancel_type is null ) ";
			}
		}


		### GOODSVIEW
		if( !empty($_GET['goodsView']) && count($_GET['goodsView'])=='1' && !empty($_GET['goodsView'][0]))
		{
			$where[] = "C.goods_view = '{$_GET['goodsView'][0]}' ";
		}

		### TAX
		if( !empty($_GET['taxView']) && count($_GET['taxView'])=='1' )
		{
			$where[] = "C.tax = '{$_GET['taxView'][0]}' ";
		}

		### GOODS STATUS
		if( !empty($_GET['goodsStatus']) ){
			foreach($_GET['goodsStatus'] as $k){
				$tmp[] = "'".$k."'";
			}
			$tmp_text = implode(",",$tmp);
			$where[] = "C.goods_status in ( {$tmp_text} ) ";
		}


		### search_reserve 0:기본 1:개별
		if($_GET['search_reserve'][0] && $_GET['search_reserve'][1]){
		}else{
			if($_GET['search_reserve'][0]){
				$where[] = "C.reserve_policy='shop'";
			}
			if($_GET['search_reserve'][1]){
				$where[] = "C.reserve_policy='goods'";
			}
		}

		### search_delivery 0:국내기본 1:국내개별 2:해외
		if($_GET['search_delivery'][0]){
			$where[] = "C.shipping_policy='shop'";
		}
		if($_GET['search_delivery'][1]){
			$where[] = "C.shipping_policy='goods'";
		}

		### MOEDEL
		if( !empty($_GET['model']) )
		{
			$where[] = "C.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = C.goods_seq AND K.contents = '{$_GET['model']}' ) ";
		}
		### BRAND
		if( !empty($_GET['brand']) )
		{
			$where[] = "C.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = C.goods_seq AND K.contents = '{$_GET['brand']}' ) ";
		}
		### MANUFACTURE
		if( !empty($_GET['manufacture']) )
		{
			$where[] = "C.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = C.goods_seq AND K.contents = '{$_GET['manufacture']}' AND K.type ='manufacture' ) ";
		}
		### ORIGN
		if( !empty($_GET['orign']) )
		{
			$where[] = "C.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = C.goods_seq AND K.contents = '{$_GET['orign']}' AND K.type ='orgin' ) ";
		}
		### SALE_SEQ
		if($_GET['sale_seq']){
			$where[] = "C.sale_seq='".$_GET['sale_seq']."'";
		}

		
		
		//일괄업데이트시 미변환추가
		if( !empty($_GET['gabiaimagehostign']) )
		{
			$where[] = " C.convert_image_date > 0 AND C.noconvert_image_cnt > 0";
		}


		### icon
		if( !empty($_GET['goodsIconCode']) ){
			foreach($_GET['goodsIconCode'] as $icon => $icon_use){
				if($icon_use) $r_icon[] = $icon;
			}
		}
		if( $r_icon )
		{
			$where[] = " C.goods_seq in (select goods_seq codecd from fm_goods_icon where codecd in (".implode(',',$r_icon)."))";
			$search_yn = 'y';
		}

		// 판매마켓 검색
		if(is_array($_GET["openmarket"]) && count($_GET['openmarket']) > 0){
			$where[]	= " C.goods_seq in (select goods_seq from fm_linkage_goods_mall where mall_code in ('".implode("', '", $_GET["openmarket"])."') ) ";
		}

		if($where)	$AwhereSql = " where " . implode(' and ',$where);

		//정렬관련 추가 (정가, 할인가, 재고 오름/내림 차순 정렬)
		if(in_array($_GET['orderby'],array("consumer_price","price"))){
			$orderby = "D.".$_GET['orderby'];
		}else{
			$orderby = "C.".$_GET['orderby'];
		}

		$sql = "select
				C.*,
				CASE WHEN C.goods_status = 'unsold' THEN '판매중지'
					WHEN C.goods_status = 'purchasing' THEN '재고확보중'
					WHEN C.goods_status = 'runout' THEN '품절'
					ELSE '정상' END AS goods_status_text
				from
					fm_goods as C 
					left join fm_goods_option as D on C.goods_seq=D.goods_seq and D.default_option='y'
				".$AwhereSql."
				order by {$orderby} {$_GET['sort']}";
		if($this->batch_mode){
			return $sql;
		}

		$result = select_page($_GET['perpage'],$_GET['page'],10,$sql,'');
		$result['page']['querystring'] = get_args_list();

		return $result;
	}


	public function goods_addition_list($type) {
		$sql = "select distinct A.contents, A.* from fm_goods_addition A where A.type = '{$type}' group by A.contents order by A.addition_seq desc";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return $data;
	}


	public function goods_addition_list_all(){
		$sql = "select distinct A.contents, A.*  from fm_goods_addition A where A.type != 'direct' group by A.contents,A.type ";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row){
			$result[$row['type']][] = $row;
		}
		return $result;
	}

	###
	public function delete_goods($goodSeq){
		### DEFAULT
		$result = $this->db->delete('fm_category_link', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_addition', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_icon', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_input', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_option', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_relation', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_suboption', array('goods_seq' => $goodSeq));
		$result = $this->db->delete('fm_goods_supply', array('goods_seq' => $goodSeq));

		//동영상삭제
		$result = $this->db->delete('fm_videofiles', array('upkind' => 'goods','parentseq' => $goodSeq));

		// qrcode 이미지 삭제
		$domain = !empty($this->config_system['domain']) ? $this->config_system['domain'] : $this->config_system['sub_domain'];
		$domain = $domain ? $domain : $_SERVER['HTTP_HOST'];
		for($i=1;$i<=10;$i++){
			if(file_exists(ROOTPATH."data/qrcode/qrcode_".md5("http://{$domain}/goods/view?no={$goodSeq}"."|".$i).".png")){
				@unlink(ROOTPATH."data/qrcode/qrcode_".md5("http://{$domain}/goods/view?no={$goodSeq}"."|".$i).".png");
			}
		}

		### IMAGE
		$this->db->where('goods_seq', $goodSeq);
		$query = $this->db->get('fm_goods_image');
		foreach($query->result_array() as $data){
			###
			if(isset($data['image'])){
				$target = ".".$data['image'];
				$result = unlink($target);
			}
			$result = $this->db->delete('fm_goods_image', array('image_seq' => $data['image_seq']));
		}
		$result = $this->db->delete('fm_goods_image', array('goods_seq' => $goodSeq));

		$result = $this->db->delete('fm_goods', array('goods_seq' => $goodSeq));
		return $result;
	}


	###
	public function copy_goods($oldSeq){
		/*
		$this->db->where('goods_seq', $oldSeq);
		$query = $this->db->get("fm_goods");
		$data = $query->result_array();

		$params = filter_keys($data, $this->db->list_fields('fm_goods'));
		unset($params['goods_seq']);
		unset($params['update_date']);
		unset($params['admin_log']);
		$params['regist_date'] = date("Y-m-d H:i:s");

		$result = $this->db->insert('fm_goods', $params);
		*/
		$now = date("Y-m-d H:i:s");
		$sql = "INSERT INTO fm_goods
			(view_layout, goods_status,cancel_type, sale_seq, goods_view, favorite_chk, goods_code, goods_name, purchase_goods_name, summary, keyword, contents, mobile_contents, info_seq, common_contents, string_price_use, string_price, tax, multi_discount_use, multi_discount_ea, multi_discount, multi_discount_unit, min_purchase_limit, min_purchase_ea, max_purchase_limit, max_purchase_order_limit, max_purchase_ea, reserve_policy, option_use, option_view_type, option_suboption_use, member_input_use, shipping_policy, goods_shipping_policy, unlimit_shipping_price, limit_shipping_ea, limit_shipping_price, limit_shipping_subprice, shipping_weight_policy, goods_weight, relation_type, relation_count_w, relation_count_h, relation_image_size, relation_criteria, admin_memo, admin_log, regist_date, goods_type, goods_sub_info, sub_info_desc,string_price_link,string_price_link_url,member_string_price_use,member_string_price,member_string_price_link,member_string_price_link_url,allmember_string_price_use,allmember_string_price,allmember_string_price_link,allmember_string_price_link_url, goods_kind, socialcp_event, socialcp_input_type, socialcp_use_return, socialcp_use_emoney_day, socialcp_use_emoney_percent,social_goods_group,tot_stock,socialcp_cancel_type,socialcp_cancel_use_refund,socialcp_cancel_payoption,socialcp_cancel_payoption_percent)
		SELECT
			view_layout, goods_status,cancel_type, sale_seq, goods_view, favorite_chk, goods_code, goods_name, purchase_goods_name, summary, keyword, contents, mobile_contents,  info_seq, common_contents, string_price_use, string_price, tax, multi_discount_use, multi_discount_ea, multi_discount, multi_discount_unit, min_purchase_limit, min_purchase_ea, max_purchase_limit, max_purchase_order_limit, max_purchase_ea, reserve_policy, option_use, option_view_type, option_suboption_use, member_input_use, shipping_policy, goods_shipping_policy, unlimit_shipping_price, limit_shipping_ea, limit_shipping_price, limit_shipping_subprice, shipping_weight_policy, goods_weight, relation_type, relation_count_w, relation_count_h, relation_image_size, relation_criteria, admin_memo, admin_log, '{$now}', goods_type, goods_sub_info, sub_info_desc,string_price_link,string_price_link_url,member_string_price_use,member_string_price,member_string_price_link,member_string_price_link_url,allmember_string_price_use,allmember_string_price,allmember_string_price_link,allmember_string_price_link_url, goods_kind, socialcp_event, socialcp_input_type, socialcp_use_return, socialcp_use_emoney_day, socialcp_use_emoney_percent,social_goods_group,tot_stock,socialcp_cancel_type,socialcp_cancel_use_refund,socialcp_cancel_payoption,socialcp_cancel_payoption_percent
		FROM
			fm_goods
		WHERE
			goods_seq = '{$oldSeq}'";
		$result = $this->db->query($sql);
		$goods_seq = $this->db->insert_id();
		return $goods_seq;
	}


	###
	public function copy_goods_default($table, $oldSeq, $goodSeq, $unset_seq){
		$this->db->where('goods_seq', $oldSeq);
		$query = $this->db->get($table);
		foreach($query->result_array() as $data){
			$params = filter_keys($data, $this->db->list_fields($table));
			unset($params[$unset_seq]);
			if(isset($params['regist_date'])) $params['regist_date'] = date("Y-m-d H:i:s");
			$params['goods_seq'] = $goodSeq;
			$params	= $this->copy_goods_exception($table, $params);
			$result = $this->db->insert($table, $params);
		}
		return $result;
	}

	public function copy_goods_exception($table, $params){
		switch($table){
			case 'fm_category_link':
				$this->load->model('categorymodel');
				$minsort	= $this->categorymodel->getSortValue($params['category_code'], 'min');
				$params['sort']	= $minsort - 1;
			break;
			case 'fm_brand_link':
				$this->load->model('brandmodel');
				$minsort	= $this->brandmodel->getSortValue($params['category_code'], 'min');
				$params['sort']	= $minsort - 1;
			break;
			case 'fm_location_link':
				$this->load->model('locationmodel');
				$minsort	= $this->locationmodel->getSortValue($params['location_code'], 'min');
				$params['sort']	= $minsort - 1;
			break;
		}

		return $params;
	}

	public function copy_goods_option($oldSeq, $goodsSeq){
		### OPTION
		$sql = "SELECT distinct A.*, B.* FROM fm_goods_option A LEFT JOIN fm_goods_supply B ON A.option_seq = B.option_seq WHERE A.goods_seq = '{$oldSeq}' AND B.goods_seq = '{$oldSeq}' AND B.option_seq is not null;";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $data){
			$oparams['goods_seq']		= $goodsSeq;
			$oparams['default_option']	= $data['default_option'];
			$oparams['option_title']	= $data['option_title'];
			$oparams['option1']			= $data['option1'];
			$oparams['option2']			= $data['option2'];
			$oparams['option3']			= $data['option3'];
			$oparams['option4']			= $data['option4'];
			$oparams['option5']			= $data['option5'];

			$oparams['option_type']			= $data['option_type'];
			$oparams['code_seq']				= $data['code_seq'];
			$oparams['optioncode1']			= $data['optioncode1'];
			$oparams['optioncode2']			= $data['optioncode2'];
			$oparams['optioncode3']			= $data['optioncode3'];
			$oparams['optioncode4']			= $data['optioncode4'];
			$oparams['optioncode5']			= $data['optioncode5'];

			$oparams['tmpprice']			= $data['tmpprice'];
			$oparams['color']								= trim($data['color']);
			$oparams['zipcode']				= $data['zipcode'];
			$oparams['address_type']					= $data['address_type'];
			$oparams['address']							= $data['address'];
			$oparams['address_street']				= $data['address_street'];
			$oparams['addressdetail']					= $data['addressdetail'];
			$oparams['biztel']								= $data['biztel'];
			$oparams['address_commission']		= $data['address_commission'];
			$oparams['newtype']			= $data['newtype'];

			$oparams['coupon_input']	= $data['coupon_input'];//쇼셜쿠폰의 1장값어치 횟수-금액
			$oparams['codedate']			= $data['codedate'];
			$oparams['sdayinput']			= $data['sdayinput'];
			$oparams['fdayinput']			= $data['fdayinput'];
			$oparams['dayauto_type']	= $data['dayauto_type'];
			$oparams['sdayauto']			= $data['sdayauto'];
			$oparams['fdayauto']			= $data['fdayauto'];
			$oparams['dayauto_day']	= $data['dayauto_day'];

			$oparams['consumer_price']	= $data['consumer_price'];
			$oparams['price']						= $data['price'];
			$oparams['reserve_rate']		= $data['reserve_rate'];
			$oparams['reserve_unit']			= $data['reserve_unit'];
			$oparams['reserve']					= $data['reserve'];
			$result = $this->db->insert('fm_goods_option', $oparams);
			$option_seq = $this->db->insert_id();
			$sparams['goods_seq']		= $goodsSeq;
			$sparams['option_seq']		= $option_seq;
			$sparams['supply_price']	= $data['supply_price'];
			$sparams['stock']			= $data['stock'];
			$result = $this->db->insert('fm_goods_supply', $sparams);
		}
		unset($oparams);
		unset($sparams);
		### SUBOPTION
		$sql = "SELECT A.suboption_seq, A.*, B.* FROM fm_goods_suboption A LEFT JOIN fm_goods_supply B ON A.suboption_seq = B.suboption_seq WHERE A.goods_seq = '{$oldSeq}' AND B.goods_seq = '{$oldSeq}' AND B.suboption_seq is not null;";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $data){
			$oparams['goods_seq']		= $goodsSeq;

			$oparams['suboption_title']	= $data['suboption_title'];
			$oparams['sub_required']	= $data['sub_required'];
			$oparams['sub_sale']		= $data['sub_sale'];

			$oparams['suboption_type']	= $data['suboption_type'];
			$oparams['suboption_code']	= $data['suboption_code'];
			$oparams['code_seq']		= $data['code_seq'];

			$oparams['color']					= trim($data['color']);
			$oparams['zipcode']				= $data['zipcode'];
			$oparams['address_type']			= $data['address_type'];
			$oparams['address']			= $data['address'];
			$oparams['address_street']			= $data['address_street'];
			$oparams['addressdetail']	= $data['addressdetail'];
			$oparams['biztel']				= $data['biztel'];
			$oparams['newtype']			= $data['newtype'];

			$oparams['coupon_input']	= $data['coupon_input'];//쇼셜쿠폰의 1장값어치 횟수-금액
			$oparams['codedate']			= $data['codedate'];
			$oparams['sdayinput']			= $data['sdayinput'];
			$oparams['fdayinput']			= $data['fdayinput'];
			$oparams['dayauto_type']	= $data['dayauto_type'];
			$oparams['sdayauto']			= $data['sdayauto'];
			$oparams['fdayauto']			= $data['fdayauto'];
			$oparams['dayauto_day']		= $data['dayauto_day'];

			$oparams['suboption']		= $data['suboption'];
			$oparams['consumer_price']	= $data['consumer_price'];
			$oparams['price']			= $data['price'];
			$oparams['reserve_rate']	= $data['reserve_rate'];
			$oparams['reserve_unit']	= $data['reserve_unit'];
			$oparams['reserve']			= $data['reserve'];
			$result = $this->db->insert('fm_goods_suboption', $oparams);
			$suboption_seq = $this->db->insert_id();
			$sparams['goods_seq']		= $goodsSeq;
			$sparams['suboption_seq']	= $suboption_seq;
			$sparams['supply_price']	= $data['supply_price'];
			$sparams['stock']			= $data['stock'];
			$result = $this->db->insert('fm_goods_supply', $sparams);
		}
		return $result;
	}


	###
	public function copy_goods_image($table, $oldSeq, $goodSeq, $unset_seq){
		$this->db->where('goods_seq', $oldSeq);
		$query = $this->db->get($table);
		$cnt = 0;
		foreach($query->result_array() as $data){
			$params = filter_keys($data, $this->db->list_fields($table));
			unset($params[$unset_seq]);
			if(isset($params['regist_date'])) $params['regist_date'] = date("Y-m-d H:i:s");
			$params['goods_seq'] = $goodSeq;

			###
			if(strpos($data['image'],'goods')){
				$target = $this->clone_image($params['image'], $cnt, $goodSeq.'_');
				$params['image'] = $target;
			}

			$result = $this->db->insert($table, $params);
			$cnt++;
		}
		return $result;
	}


	public function clone_image($file, $idx, $prefix=''){
		$dir = $this->upload_goodsImage_dir();
		$ext = end(explode('.', $file));
		$filenm	= $prefix.date('YmdHis').$idx.rand(0,9);
		$target = $dir.'/'.$filenm.'.'.$ext;
		$result = copy(ROOTPATH.$file, ROOTPATH.$target);
		return $target;
	}

	// 옵션재고
	public function stock_option($mode,$ea,$goods_seq,$option1,$option2,$option3='',$option4='',$option5=''){
		if($mode == '+'){
			$query = "
			update fm_goods_supply set stock = stock + ? where option_seq in
			(
				select option_seq from fm_goods_option where goods_seq=? and option1=? and option2=? and option3=? and option4=? and option5=?
			)";
			$this->db->query($query,array($ea,$goods_seq,$option1,$option2,$option3,$option4,$option5));
		}

		if($mode == '-'){
			$query = "
			update fm_goods_supply set stock = stock - IF(stock>=?,?,stock) where option_seq in
			(
				select option_seq from fm_goods_option where goods_seq=? and option1=? and option2=? and option3=? and option4=? and option5=?
			)";
			$this->db->query($query,array($ea,$ea,$goods_seq,$option1,$option2,$option3,$option4,$option5));
		}

		$this->runout_check($goods_seq);

		// 재고 차감에 따른 다중판매처 상품정보 전달
		$this->load->model('openmarketmodel');
		$this->openmarketmodel->request_send_goods($goods_seq);
	}

	// 재고 총 수량
	public function total_stock($goods_seq){

		$query = "select 
						sum(s.stock) tot_stock
					from 
						fm_goods_option as o 
						left join fm_goods_supply as s on o.option_seq=s.option_seq
					where 
						o.goods_seq=?";
		$res	= $this->db->query($query,array($goods_seq));
		$data	= $res->result_array();
		$total	= $data[0]['tot_stock'];

		$query = "update fm_goods set tot_stock=? where goods_seq=?";
		$result = $this->db->query($query,array($total,$goods_seq));

	}


	// 서브옵션재고
	public function stock_suboption($mode,$ea,$goods_seq,$title,$option){

		if($mode == '+'){
			$query = "
			update fm_goods_supply set stock = stock + ? where suboption_seq in
			(
				select suboption_seq from fm_goods_suboption where goods_seq=? and suboption_title=? and suboption=?
			)";
			$this->db->query($query,array($ea,$goods_seq,$title,$option));
		}

		if($mode == '-'){
			$query = "
			update fm_goods_supply set stock = stock - IF(stock>=?,?,stock) where suboption_seq in
			(
				select suboption_seq from fm_goods_suboption where goods_seq=? and suboption_title=? and suboption=?
			)";
			$this->db->query($query,array($ea,$ea,$goods_seq,$title,$option));
		}


	}

	// 복수구매 할인 계산
	public function get_multi_sale_price($ea,$price,$arr_multi){
		$discount = 0;
		if(!$arr_multi['multi_discount_use']
			||!$arr_multi['multi_discount_ea']
			||!$arr_multi['multi_discount']
			||!$arr_multi['multi_discount_unit']) return $price;
		if($ea < $arr_multi['multi_discount_ea']) return $price;

		if( $arr_multi['multi_discount_unit'] == 'percent' && $arr_multi['multi_discount'] < 100 ){
			$discount = ( $price * $arr_multi['multi_discount'] / 100 );
		}else if($price > $arr_multi['multi_discount'] ) {
			$discount = $arr_multi['multi_discount'];
		}

		$discount = get_price_point($discount);
		$price -= $discount;

		return $price;
	}

	// 상품 구매수 증가
	public function get_purchase_ea($ea,$goods_seq){
		$query = "update fm_goods set purchase_ea = purchase_ea + ? where goods_seq=?";
		$this->db->query($query,array($ea,$goods_seq));
	}

	// 적립금 설정 별 적립금액 구하기
	public function get_reserve_with_policy($policy,$price,$shop_rate,$reserve_rate,$reserve_unit,$reserve){
		if($policy == 'shop'){
			$reserve = (int) ($price * $shop_rate / 100);
		}else{
			if($reserve_unit == 'percent'){
				$reserve = (int) ($price * $reserve_rate / 100);
			}elseif($reserve_unit == 'won'){
				$reserve = (int) $reserve_rate;
			}
		}
		//$reserve = get_price_point($reserve);
		return $reserve;
	}

	public function get_point_with_policy($price){
		if(!$price) return 0;
		$reserves = config_load('reserve');
		$point = 0;
		if($reserves['point_use']=='Y'){
			switch($reserves['default_point_type']){
				case "per":
					if( $reserves['default_point_percent'] ) $point = (int) ($price * $reserves['default_point_percent'] / 100);
					break;
				case "app":
					$point = $price / $reserves['default_point_app'] * $reserves['default_point'];
					break;
				default :
					$point = 0;
					break;
			}
		}else{
			$point = 0;
		}
		return $point;
	}

	// 모든 판매 가능한 상품 정보
	public function get_goods_all($update_date,$image_type,$isFeed=false){
		if(!$image_type) $where_val[] = 'list1';
		else $where_val[] = $image_type;
		$result = "";

		// 상품 아이콘 서브쿼리
		$goods_icon_subquery = "
		select group_concat(c.codecd) from fm_goods_icon c where c.goods_seq=g.goods_seq and
		(
			(ifnull(start_date,'0000-00-00') = '0000-00-00' and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(curdate() between start_date and end_date)
			or
			(start_date <= curdate() and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(end_date >= curdate() and ifnull(start_date,'0000-00-00') = '0000-00-00')
		)
		";

		$query = "
		select
			g.*,
			i.image,
			o.consumer_price,
			o.price,
			o.reserve_rate,
			o.reserve_unit,
			o.reserve,
			({$goods_icon_subquery}) as icons,
			l.category_link_seq,l.sort,l.category_code,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='model' ) model,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='brand' ) brand,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='manufacture' ) manufacture,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='orgin' ) orgin
		from
			fm_goods g
			left join fm_goods_image i on ( i.goods_seq=g.goods_seq and i.cut_number=1 and i.image_type = ? )
			left join fm_goods_option o on ( o.goods_seq=g.goods_seq and o.default_option ='y' )
			,fm_category_link l
		where
			l.goods_seq=g.goods_seq and link = 1 and g.goods_type='goods'";

		$where[] = "goods_status = ?";
		$where_val[] = 'normal';
		$where[] = "goods_view = ?";
		$where_val[] = 'look';

		if($update_date){
			$where[] = "update_date > ?";
			$where_val[] = $update_date;
		}
		if($isFeed){
			$where[] = "g.feed_status != ?";
			$where_val[] = 'N';
		}

		$query .= ' and ' . implode(' and ', $where);
		$query = $this->db->query($query,$where_val);

		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		return $result;
	}


	// 모든 판매 가능한 상품 정보
	public function get_goods_all_partner($update_date,$image_type,$isFeed=false){
		if(!$image_type) $where_val[] = 'list1';
		else $where_val[] = $image_type;
		$result = "";

		// 상품 아이콘 서브쿼리
		$goods_icon_subquery = "
		select group_concat(c.codecd) from fm_goods_icon c where c.goods_seq=g.goods_seq and
		(
			(ifnull(start_date,'0000-00-00') = '0000-00-00' and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(curdate() between start_date and end_date)
			or
			(start_date <= curdate() and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(end_date >= curdate() and ifnull(start_date,'0000-00-00') = '0000-00-00')
		)
		";

		$query = "
		select
			g.*,
			i.image,
			o.consumer_price,
			o.price,
			o.reserve_rate,
			o.reserve_unit,
			o.reserve,
			({$goods_icon_subquery}) as icons,
			l.category_link_seq,l.sort,l.category_code,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='model' limit 1) model,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='brand' limit 1) brand,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='manufacture' limit 1) manufacture,
			( select contents from fm_goods_addition where goods_seq=g.goods_seq and type='orgin' limit 1) orgin,
			( select category_code from fm_brand_link bl where goods_seq=g.goods_seq and link=1 limit 1) brand_code,
			( select b.title from fm_brand_link brl,fm_brand b where brl.category_code=b.category_code and brl.goods_seq=g.goods_seq and brl.link=1) brand_title,
			( select c.title from fm_category_link cl,fm_category c where cl.category_code=c.category_code and cl.goods_seq=g.goods_seq and cl.link=1) category_title
		from
			fm_goods g
			left join fm_goods_image i on ( i.goods_seq=g.goods_seq and i.cut_number=1 and i.image_type = '".$image_type."' )
			left join fm_goods_option o on ( o.goods_seq=g.goods_seq and o.default_option ='y' )
			,fm_category_link l
		where
			l.goods_seq=g.goods_seq and link = 1 and g.goods_type='goods' and g.goods_status = 'normal' and g.goods_view = 'look' and g.string_price_use != '1' ";

		if($update_date){
			$query .= " and update_date > '$update_date'";
		}
		if($isFeed){
			$query .= " and (g.feed_status = 'Y' or g.feed_status is NULL)";

		}




		return $query;
	}

	// 모든 판매 가능한 상품 정보
	public function get_goods_all_partner_count($update_date,$image_type,$isFeed=false){
		if(!$image_type) $where_val[] = 'list1';
		else $where_val[] = $image_type;
		$result = "";

		// 상품 아이콘 서브쿼리
		$goods_icon_subquery = "
		select group_concat(c.codecd) from fm_goods_icon c where c.goods_seq=g.goods_seq and
		(
			(ifnull(start_date,'0000-00-00') = '0000-00-00' and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(curdate() between start_date and end_date)
			or
			(start_date <= curdate() and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(end_date >= curdate() and ifnull(start_date,'0000-00-00') = '0000-00-00')
		)
		";

		$query = "
		select
			count(*) as cnt

		from
			fm_goods g,fm_goods_option o

		where
			o.goods_seq=g.goods_seq and o.default_option ='y'

			and g.goods_type='goods'
			and g.goods_status = 'normal'
			and g.goods_view = 'look'
			and g.string_price_use != '1'
			and (g.feed_status = 'Y' or g.feed_status is NULL)";

		if($update_date){
			$query .= " and update_date > '$update_date'";
		}
		if($isFeed){
			$query .= " and (g.feed_status = 'Y' or g.feed_status is NULL)";

		}

		return $query;
	}



	// 모바일 상세 설명 생성
	public function set_mobile_contents($contents,$goods_seq)
	{
		$this->load->library('image_lib');
		$cnt = preg_match_all("/<IMG[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$contents, $matches);
		foreach($matches[1] as $img_key => $ori_img){
			$img = $ori_img;
			$t_arr_img = explode(' ',$ori_img);
			$ori_img = $t_arr_img[0];

			if( preg_match('/http:\/\//',$img) ){
				$arr_img = explode('/',$img);
				unset($arr_img[0],$arr_img[1],$arr_img[2]);
				$img = implode('/',$arr_img);
			}else{
				if(substr($img,0,1) == '/') $img = substr($img,1);
			}

			$img_tag = '<img src="'.$ori_img.'" border="0" />';

			$size = @getimagesize($img);
			if( $size ){
				$limit = ($size[0]*$size[1]*$size[bits]) * 0.9;
				if($limit < 20000000){
					if($size[0] > 550) $img_tag = '<img src="'.$ori_img.'" width="550" border="0" />';
					if( substr($img,0,4)=='data' && is_file($img)){
						if($size[0] > 550){
							$arr_img = explode('/',$img);
							$target = 'mobile_'.str_replace(array('mobile_','temp_'),'',$arr_img[count($arr_img)-1]);
							$config['image_library'] = 'gd2';
							$config['source_image'] = $img;
							$config['new_image'] = $target;
							$config['maintain_ratio'] = TRUE;
							$config['width'] = 550;
							$config['height'] = ($config['width'] / $size[0]) * $size[1];
							$this->image_lib->initialize($config);
							if($this->image_lib->resize()){
								unset($arr_img[count($arr_img)-1]);
								$mobile_img = implode('/',$arr_img).'/'.$target;
								@chmod($mobile_img,0777);
								$img_tag = '<img src="'.'http://'.$_SERVER['HTTP_HOST'].'/'.$mobile_img.'" border="0" />';
							}
							$this->image_lib->clear();
						}
					}

				}
			}
			$replace[$img_key] = $img_tag;
		}
		$mobile_contents = str_replace($matches[0],$replace,$contents);
		$query = "update fm_goods set mobile_contents=? where goods_seq=?";
		$this->db->query($query,array($mobile_contents,$goods_seq));

		return $mobile_contents;
	}

	// 공용정보 모바일 이미지 강제줄임
	public function set_mobile_common_contents($contents)
	{
		$cnt = preg_match_all("/<IMG[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$contents, $matches);
		foreach($matches[1] as $img_key => $ori_img){
			$img = $ori_img;
			$t_arr_img = explode(' ',$ori_img);
			$ori_img = $t_arr_img[0];

			if( preg_match('/http:\/\//',$img) ){
				$arr_img = explode('/',$img);
				unset($arr_img[0],$arr_img[1],$arr_img[2]);
				$img = implode('/',$arr_img);
			}else{
				if(substr($img,0,1) == '/') $img = substr($img,1);
			}

			$img_tag = '<img src="'.$ori_img.'" border="0" />';

			$size = @getimagesize($img);
			if( $size ){
				$limit = ($size[0]*$size[1]*$size[bits]) * 0.9;
				if($limit < 20000000){
					if($size[0] > 300) $img_tag = '<img src="'.$ori_img.'" width="300" border="0" />';
				}
			}
			$replace[$img_key] = $img_tag;
		}
		$mobile_contents = str_replace($matches[0],$replace,$contents);
		return $mobile_contents;
	}



	/**
	*## 상품의 가장큰 할인율,가장빨리 종료되는 이벤트 정보 가져오기
	* price 할인가 적용시
	* goods_seq 상품고유번호
	* r_category_code 적용카테고리
	* consumer_price 정가 적용시
	* $goodsinfo socialcp_event 단독이벤트만 적용시 이벤트간이 아닌경우 판매중지로 노출(프론트)
	**/
	public function get_event_price($price, $goods_seq, $r_category_code, $consumer_price = 0, $goodsinfo = null, $cart_today = '')
	{
		$app_week_arr		= array("1"	=> "월요일", "2"	=> "화요일", "3"	=> "수요일",
									"4"	=> "목요일", "5"	=> "금요일", "6"	=> "토요일", "7"	=> "일요일");
		if(!is_array($r_category_code)){
			$this->load->model('categorymodel');
			$r_category_code	= $this->categorymodel->split_category($r_category_code);
		}
		if(!is_array($r_category_code)) $r_category_code	= array($r_category_code);
		if(!$cart_today){
			$today		= date('Y-m-d H:i:s');
			$today_w	= date('w');
		}else{
			$today		= $cart_today['today'];
			$today_w	= $cart_today['todayw'];
		}
		$todaytime		= date('H');
 		//단독이벤트 추출
		$solo_query		= "select b.*, e.title, e.start_date, e.end_date, e.event_type, e.app_week, 
							e.app_start_time, e.app_end_time, e.tpl_path, e.event_order_cnt, 
							e.event_order_ea, e.event_order_price, e.title_contents, e.bgcolor, 
							e.daily_event
						from 
							fm_event_benefits b 
							left join fm_event e on b.event_seq=e.event_seq 
						where e.event_seq = b.event_seq and e.goods_rule = 'goods_view' and 
								e.display='y' and e.event_type = 'solo' and e.start_date <= '".$today."' 
								and e.end_date >= '".$today."' and (
									( e.app_week = '' or e.app_week = '0' or  e.app_week is null  )
									or
									( e.app_week like '%".$today_w."%'  and LEFT(e.app_start_time,2) <= '".$todaytime."' and LEFT(e.app_end_time,2) >= '".$todaytime."')
								) and (select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'goods' and goods_seq = '$goods_seq') > 0 ";
		$query			= "select * from (".$solo_query.") t 
							order by t.event_sale desc, t.end_date asc limit 1";
		$query			= $this->db->query($query);
		$data_event		= $query->row_array();
		if( !$data_event ) {
			if( $goodsinfo['socialcp_event'] == 1 ) {	// 단독이벤트만 적용 시
				if ( define('__ADMIN__') != true ) {	// 프론트면 판매중지로 노출
					$data_event['event_goodsStatus']	= true;	// "unsold";
				}
			}else{//일반이벤트이면
				$where_category = "'".implode("','",$r_category_code)."'";

				// 특정요일에만
				$week_number = date('w');
				if(  $week_number == 0 ) $week_number = 7;
				$str_where_week = "(e.app_week = '' or e.app_week = '0' or  e.app_week is null or e.app_week like '%".$week_number."%')";

				// 특정 시간 에만
				$str_where_time = "(e.app_start_time is null OR e.app_start_time='' OR LEFT(e.app_start_time,2) <= '".$todaytime."') and (e.app_start_time is null OR e.app_start_time='' OR LEFT(e.app_end_time,2) >= '".$todaytime."')";

				$r_query[] = "select  b.*,e.title,e.start_date,e.end_date,e.event_type,e.app_week,e.app_start_time,e.app_end_time,e.tpl_path,
					e.event_order_cnt,e.event_order_ea,e.event_order_price,e.daily_event
					from fm_event_benefits b left join fm_event e on b.event_seq=e.event_seq where
						e.event_seq = b.event_seq and e.goods_rule='all' and e.display='y' and e.event_type = 'multi'
						and e.start_date <= '$today' and e.end_date >= '$today'
						and ".$str_where_week."
						and ".$str_where_time."
		and	(select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'except_category' and category_code in ( $where_category ) )=0
						and	(select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'except_goods' and goods_seq = '$goods_seq')=0
						and ( e.apply_goods_kind = (select goods_kind from fm_goods where goods_seq='$goods_seq') OR e.apply_goods_kind='all' OR e.apply_goods_kind is null)
				";
				$r_query[] = "select b.*,e.title,e.start_date,e.end_date,e.event_type,e.app_week,e.app_start_time,e.app_end_time,e.tpl_path,
					e.event_order_cnt,e.event_order_ea,e.event_order_price,e.daily_event
					from fm_event_benefits b left join fm_event e on b.event_seq=e.event_seq where
						e.event_seq = b.event_seq and e.goods_rule='category' and e.display='y' and e.event_type = 'multi'
						and e.start_date <= '$today' and e.end_date >= '$today'
						and ".$str_where_week."
						and ".$str_where_time."
		and	(select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'category' and category_code in ( $where_category ) ) > 0
		and	(select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'except_category' and category_code in ( $where_category ) )= 0
						and	(select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'except_goods' and goods_seq = '$goods_seq') = 0
				";
				$r_query[] = "select b.*,e.title,e.start_date,e.end_date,e.event_type,e.app_week,e.app_start_time,e.app_end_time,e.tpl_path,
					e.event_order_cnt,e.event_order_ea,e.event_order_price,e.daily_event
				from fm_event_benefits b left join fm_event e on b.event_seq=e.event_seq where
					e.event_seq = b.event_seq and e.goods_rule='goods_view' and e.display='y' and e.event_type = 'multi'
					and e.start_date <= '$today' and e.end_date >= '$today'
					and ".$str_where_week."
					and ".$str_where_time."
					and	(select count(*) from fm_event_choice where event_benefits_seq = b.event_benefits_seq and choice_type = 'goods' and goods_seq = '$goods_seq') > 0
				";
				$query = 'select * from (('.implode(') union (',$r_query).')) t order by t.event_sale desc,t.end_date asc limit 1';
				$query = $this->db->query($query);
				$data_event = $query->row_array();
			}
		}

		$data_event['event_sale_unit'] = $data_event['event_reserve_unit'] = $data_event['event_point_unit'] = 0;
		if($data_event['target_sale'] == 1 && $consumer_price > 0) {//정가기준 정가가 있을때
			$consumer_price = (int) $consumer_price;
			$data_event['event_sale_unit'] =  floor( $consumer_price * $data_event['event_sale'] / 100 );
		}else{
			$data_event['event_sale_unit'] = floor($price * $data_event['event_sale'] / 100);
		}

		### EMONEY -> 실 결제금액 기준
		if($data_event['event_reserve'])				$data_event['event_reserve_unit']		=  floor( $price * $data_event['event_reserve'] / 100);
		### POINT -> 실 결제금액 기준
		if($data_event['event_point'])					$data_event['event_point_unit']			=  floor( $price * $data_event['event_point'] / 100);

		if($data_event['event_sale_unit'])			$data_event['event_sale_unit'] = get_price_point($data_event['event_sale_unit']);
		if($data_event['event_reserve_unit'])		$data_event['event_reserve_unit']		= get_price_point($data_event['event_reserve_unit']);
		if($data_event['event_point_unit'])			$data_event['event_point_unit']			= get_price_point($data_event['event_point_unit']);

		if($data_event['daily_event'] && $data_event['app_week']){
			for($i=0;$i<strlen($data_event['app_week']);$i++) {
				$app_week = substr($data_event['app_week'],$i,1);
				if($app_week_arr[$app_week])$app_week_title[] = $app_week_arr[$app_week];
			}
			$data_event['app_week_title'] = implode(', ',$app_week_title);
			$data_event['app_start_time_title'] = substr($data_event['app_start_time'],0,2).":".substr($data_event['app_start_time'],2,2);
			$data_event['app_end_time_title'] = substr($data_event['app_end_time'],0,2).":".substr($data_event['app_end_time'],2,2);
		}
		return $data_event;
	}

	###
	public function option_check($seq){
		$datas = get_data("fm_goods_option",array("goods_seq"=>$seq,"default_option"=>'y'));
		if(empty($datas) || !$datas[0]['option_seq']){
			$sql = "UPDATE fm_goods_option A SET A.default_option = 'y' WHERE A.option_seq = (SELECT B.option_seq FROM (SELECT min(option_seq) as option_seq FROM fm_goods_option WHERE goods_seq = '{$seq}') B)";
			$this->db->query($sql);
		}
		return;
	}

	/* 재입고알림 요첨 상품 리스트 */
	public function restock_notify_list($sc) {
		$CI =& get_instance();
		$key = get_shop_key();

		if(!isset($sc['page']))$sc['page'] = 1;

		### CATEGORY
		if( !empty($sc['category4']) ){
			$where_link = "category_code = '{$sc[category4]}'";
		}else if( !empty($sc['category3']) ){
			$where_link = "category_code like '{$sc[category3]}%'";
		}else if( !empty($sc['category2']) ){
			$where_link = "category_code like '{$sc[category2]}%'";
		}else if( !empty($sc['category1']) ){
			$where_link = "category_code like '{$sc[category1]}%'";
		}

		if( $where_link ){
			$where_link_sql = " AND F.goods_seq in (select goods_seq from fm_category_link where ".$where_link.")";
			if( $sc['search_link_category'] ){
				$where_link_sql = " AND F.goods_seq in (select goods_seq from fm_category_link where ".$where_link." and link)";
			}
		}

		### BRAND
		$where_link = '';
		if( !empty($sc['brands4']) ){
			$where_link = "category_code = '{$sc[brands4]}'";
		}else if( !empty($sc['brands3']) ){
			$where_link = "category_code like '{$sc[brands3]}%'";
		}else if( !empty($sc['brands2']) ){
			$where_link = "category_code like '{$sc[brands2]}%'";
		}else if( !empty($sc['brands1']) ){
			$where_link = "category_code like '{$sc[brands1]}%'";
		}

		if( $where_link ){
			$where_brand_link_sql = " AND F.goods_seq in (select goods_seq from fm_brand_link where ".$where_link.")";
			if( $sc['search_link_category'] ){
				$where_brand_link_sql = " AND F.goods_seq in (select goods_seq from fm_brand_link where ".$where_link." and link)";
			}
		}

		$sql = "SELECT Z.* FROM (SELECT K.*, (SELECT E.category_code FROM fm_category_link E WHERE E.goods_seq = K.goods_seq AND E.link = '1' limit 1) as category_code, (SELECT F.category_code FROM fm_brand_link F WHERE F.goods_seq = K.goods_seq AND F.link = '1' limit 1) as brand_code FROM
			(select
				A.restock_notify_seq,
				A.member_seq,
				A.goods_seq,
				A.notify_status,
				A.notify_date,
				A.regist_date,
				AES_DECRYPT(UNHEX(A.cellphone), '{$key}') as cellphone,
				CASE WHEN A.notify_status = 'none' THEN '미통보'
					WHEN A.notify_status = 'complete' THEN '통보'
					END AS goods_status_text,
				B.consumer_price, B.price,
				C.stock,
				C.badstock,
				C.reservation15,
				C.reservation25,
				D.userid,
				D.user_name,
				E.group_name,
				F.goods_name,
				F.goods_code,
				F.cancel_type,
				F.goods_status,
				F.goods_view,
				F.tax,
				D.rute as mbinfo_rute,
				D.user_name as mbinfo_user_name,
				G.business_seq as mbinfo_business_seq,
				G.bname as mbinfo_bname,
				H.restock_option_seq,
				H.title1,
				H.option1,
				H.title2,
				H.option2,
				H.title3,
				H.option3,
				H.title4,
				H.option4,
				H.title5,
				H.option5
			from
				fm_goods_restock_notify  A
				LEFT JOIN fm_goods_option B ON A.goods_seq = B.goods_seq
				LEFT JOIN fm_goods_supply C ON A.goods_seq	= C.goods_seq AND B.option_seq = C.option_seq
				LEFT JOIN fm_member D ON A.member_seq = D.member_seq
				LEFT JOIN fm_member_business G ON A.member_seq = G.member_seq
				LEFT JOIN fm_member_group E ON D.group_seq = E.group_seq
				LEFT JOIN fm_goods F ON A.goods_seq = F.goods_seq
				LEFT JOIN fm_goods_restock_option H ON A.restock_notify_seq = H.restock_notify_seq
			where
				B.default_option = 'y' ".$where_link_sql.$where_brand_link_sql." ) K ) Z
			WHERE
				1 ";


		###
		if($sc['keyword']=='상품명, 상품코드' || $sc['keyword']=='사은품명, 상품코드') unset($sc['keyword']);
		if( !empty($sc['keyword'])){
			$sql .= " and ( Z.goods_name like '%{$sc['keyword']}%' or Z.goods_code like '%{$sc['keyword']}%' or Z.goods_seq like '%{$sc['keyword']}%' ) ";
		}



		### DATE
		if( !empty($sc['sdate']) && !empty($sc['edate']) ){
			$sql .= " AND Z.{$sc['date_gb']} between '{$sc['sdate']} 00:00:00' and '{$sc['edate']} 23:59:59' ";
		}else if( !empty($sc['sdate']) && empty($sc['edate']) ){
			$sql .= " AND Z.{$sc['date_gb']} >= '{$sc['sdate']}' ";
		}else if( empty($sc['sdate']) && !empty($sc['edate']) ){
			$sql .= " AND Z.{$sc['date_gb']} <= '{$sc['edate']}' ";
		}

		### PRICE
		if( $sc['sprice'] ){
			$sql .= " AND Z.{$sc['price_gb']} >= '{$sc['sprice']}' ";
		}
		if( $sc['eprice'] ){
			$sql .= " AND Z.{$sc['price_gb']} <= '{$sc['eprice']}' ";
		}

		### STOCK
		if( $sc['sstock'] ){
			$sql .= " AND Z.stock >= '{$sc['sstock']}' ";
		}
		if( $sc['estock'] ){
			$sql .= " AND Z.stock <= '{$sc['estock']}' ";
		}

		### PAGE_VIEW
		if( $sc['spage_view'] ){
			$sql .= " AND Z.page_view >= '{$sc['spage_view']}' ";
		}
		if( $sc['epage_view'] ){
			$sql .= " AND Z.page_view <= '{$sc['epage_view']}' ";
		}


		### GOODSVIEW
		if( !empty($sc['goodsView']) && count($sc['goodsView'])=='1' )
		{
			$sql .= " AND Z.goods_view = '{$sc['goodsView'][0]}' ";
		}

		### TAX
		if( !empty($sc['taxView']) && count($sc['taxView'])=='1' )
		{
			$sql .= " AND Z.tax = '{$sc['taxView'][0]}' ";
		}

		### GOODS STATUS
		if( !empty($sc['goodsStatus']) ){
			foreach($sc['goodsStatus'] as $k){
				$tmp[] = "'".$k."'";
			}
			$tmp_text = implode(",",$tmp);
			$sql .= " AND Z.goods_status in ( {$tmp_text} ) ";
		}

		### NOTIFY STATUS
		if( !empty($sc['notifyStatus']) ){
			foreach($sc['notifyStatus'] as $k){
				$tmp[] = "'".$k."'";
			}
			$tmp_text = implode(",",$tmp);
			$sql .= " AND Z.notify_status in ( {$tmp_text} ) ";
		}


		### MOEDEL
		if( !empty($sc['model']) )
		{
			$sql .= " AND Z.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = Z.goods_seq AND K.contents = '{$sc['model']}' ) ";
		}
		### BRAND
		if( !empty($sc['brand']) )
		{
			$sql .= " AND Z.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = K.goods_seq AND K.contents = '{$sc['brand']}' ) ";
		}
		### MANUFACTURE
		if( !empty($sc['manufacture']) )
		{
			$sql .= " AND Z.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = Z.goods_seq AND K.contents = '{$sc['manufacture']}' ) ";
		}
		### ORIGN
		if( !empty($sc['orign']) )
		{
			$sql .= " AND K.goods_seq = any( select K.goods_seq from fm_goods_addition K where K.goods_seq = Z.goods_seq AND K.contents = '{$sc['orign']}' ) ";
		}

		### GOODS_SEQ
		if( !empty($sc['goods_seq']) )
		{
			$sql .= " AND Z.goods_seq = '{$sc['goods_seq']}' ";
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$result = select_page($sc['perpage'],$sc['page'],10,$sql,'');
		$result['page']['querystring'] = get_args_list();

		foreach($result['record'] as $i=>$data){
			if($data['member_seq']){
				$result['record'][$i]['member_type']	= $data['mbinfo_business_seq'] ? '기업' : '개인';
			}

		}
		return $result;
	}

	###
	public function delete_restock_notify($restock_notify_seq){
		### DEFAULT
		$result = $this->db->delete('fm_goods_restock_notify', array('restock_notify_seq' => $restock_notify_seq));
		$result .= $this->db->delete('fm_goods_restock_option', array('restock_notify_seq' => $restock_notify_seq));
		return $result;
	}

	### 서브 옵션 출고예약량 업데이트($mode : plus,minus,modify)
	public function modify_reservation_suboption($ea,$goods_seq,$title,$option,$ablestock_step,$mode='modify')
	{
		$reservation_field = 's.reservation'.$ablestock_step;
		if( $mode == 'modify' ) $set_query = $reservation_field." = ?";
		else if($mode == 'plus') $set_query = $reservation_field." = ifnull(". $reservation_field .",0) + ?";
		else if($mode == 'minus') $set_query = $reservation_field." = ifnull(". $reservation_field .",0) - ?";
		$val[] = $ea;
		$val[] = $goods_seq;
		$val[] = $title;
		$val[] = $option;
		$query = "update fm_goods_suboption o,fm_goods_supply s set ".$set_query." where o.suboption_seq=s.suboption_seq and o.goods_seq=? and o.suboption_title=? and o.suboption=?";
		$this->db->query($query,$val);
	}

	### 옵션 출고예약량 업데이트($mode : plus,minus,modify)
	public function modify_reservation_option($ea,$goods_seq,$option1,$option2,$option3,$option4,$option5,$ablestock_step,$mode='modify')
	{

		$reservation_field = 's.reservation'.$ablestock_step;
		if( $mode == 'modify' ) $set_query = $reservation_field." = ?";
		else if($mode == 'plus') $set_query = $reservation_field." = ifnull(". $reservation_field .",0) + ?";
		else if($mode == 'minus') $set_query = $reservation_field." = ifnull(". $reservation_field .",0) - ?";

		$val[] = $ea;
		$val[] = $goods_seq;
		$where[] = "o.goods_seq=?";
		if($option1){
			$where[] = "o.option1=?";
			$val[] = $option1;
		}
		if($option2){
			$where[] = "o.option2=?";
			$val[] = $option2;
		}
		if($option3){
			$where[] = "o.option3=?";
			$val[] = $option3;
		}
		if($option4){
			$where[] = "o.option4=?";
			$val[] = $option4;
		}
		if($option5){
			$where[] = "o.option5=?";
			$val[] = $option5;
		}

		if(!$option1 && !$option2 && !$option3 && !$option4 && !$option5 ){
			$where[] = "o.option1=''";
			$where[] = "o.option2=''";
			$where[] = "o.option3=''";
			$where[] = "o.option4=''";
			$where[] = "o.option5=''";
		}

		$where_str = " and ". implode(' and ',$where);
		$query = "update fm_goods_option o,fm_goods_supply s set ".$set_query." where o.option_seq=s.option_seq ".$where_str;
		$this->db->query($query,$val);

		$this->runout_check($goods_seq);

	}

	### 상품 품절처리
	public function runout_check($goods_seq,$mode='auto')
	{
		$cfg = config_load('order');

		//상품개별재고
		$data_goods = $this->get_goods($goods_seq);
		if($data_goods['runout_policy']){
			$cfg['runout'] = $data_goods['runout_policy'];
			$cfg['ableStockLimit'] = $data_goods['able_stock_limit'];
		}

		if( $cfg['runout'] != 'unlimited' ){
			$field = "s.reservation".$cfg['ableStockStep'];
			$query = "select sum(s.stock) stock,sum(if(IFNULL(s.stock,0) < IFNULL($field,0),0,IFNULL(s.stock,0) - IFNULL(".$field.",0))) ablestock from fm_goods_supply s,fm_goods_option o where o.option_seq = s.option_seq and o.goods_seq=?";
			$query = $this->db->query($query,array($goods_seq));
			$data = $query -> row_array();

			$tstock = $data['stock'];
			$able_stock_limit = 0;
			if($cfg['runout'] == 'ableStock') {
				$tstock = $data['ablestock'];
				$able_stock_limit = $cfg['ableStockLimit'];
			}

			// 재고가 없을 경우
			if($tstock <= $able_stock_limit && $mode == 'auto'){
				$query = "update fm_goods set `goods_status` = 'runout' where `goods_seq`=? and `goods_status`='normal'";
				$this->db->query($query,array($goods_seq));
			}

			// 재고가 있을 경우
			if($tstock > $able_stock_limit && $mode == 'auto'){
				$query = "update fm_goods set `goods_status` = 'normal' where `goods_seq`=? and `goods_status`='runout'";
				$this->db->query($query,array($goods_seq));
			}
		}

		$this->total_stock($goods_seq);

	}

	public function get_option_reservation($cfg,$goods_seq,$option1,$option2,$option3,$option4,$option5){

		$where[] = "o.goods_seq=?";
		$val[] = $goods_seq;
		if($option1){
			$where[] = "o.option1=?";
			$val[] = $option1;
		}
		if($option2){
			$where[] = "o.option2=?";
			$val[] = $option2;
		}
		if($option3){
			$where[] = "o.option3=?";
			$val[] = $option3;
		}
		if($option4){
			$where[] = "o.option4=?";
			$val[] = $option4;
		}
		if($option5){
			$where[] = "o.option5=?";
			$val[] = $option5;
		}

		if(!$option1 && !$option2 && !$option3 && !$option4 && !$option5 ){
			$where[] = "o.option1=''";
			$where[] = "o.option2=''";
			$where[] = "o.option3=''";
			$where[] = "o.option4=''";
			$where[] = "o.option5=''";
		}
		$reservation_field = $this->reservation_field;
		$query = "select s.".$reservation_field." from fm_goods_option o,fm_goods_supply s where o.option_seq = s.option_seq and o.goods_seq=s.goods_seq and ".implode(' and ',$where);
		$query = $this->db->query($query,$val);
		$data = $query -> row_array();

		return $data[$reservation_field];

	}

	public function get_suboption_reservation($cfg,$goods_seq,$title,$suboption){
		$where[] = "o.goods_seq=?";
		$val[] = $goods_seq;
		$where[] = "o.suboption_title=?";
		$val[] = $title;
		$where[] = "o.suboption=?";
		$val[] = $suboption;

		$reservation_field = $this->reservation_field;
		$query = "select s.".$reservation_field." from fm_goods_suboption o,fm_goods_supply s where o.suboption_seq = s.suboption_seq and o.goods_seq=s.goods_seq and ".implode(' and ',$where);
		$query = $this->db->query($query,$val);
		$data = $query -> row_array($query);
		return (int) $data[$reservation_field];
	}

	public function get_goods_icon_codecd($no){
		$result = '';
		$tmp = $this->get_goods_icon($no);
		if($tmp) foreach($tmp as $data){
			$result[] = $data['codecd'];
		}
		return $result;
	}

	public function get_goods_sub_info($category){
		if($category != ""){
			$where = "category = '".$category."'";
			$query = "select * from fm_goods_sub_info where ".$where;
			$query = $this->db->query($query);
			$i=0;
			$result = $query->result_array();
		}else{
			$result = "";
		}
		return $result;
	}


	/* 상품코드 일괄 업데이트 리스트 */
	public function goodscode_batch_goods_list($sc) {
		$CI =& get_instance();

		if(!isset($_GET['page']))$_GET['page'] = 1;

		$sql = "select goods_seq from fm_goods where goods_type = 'goods' ";

		$sql .=" order by goods_seq desc ";
		$result = select_page($sc['limitnum'],$_GET['page'],10,$sql,'');
		$result['page']['querystring'] = get_args_list();

		return $result;
	}

	public function get_sale_price($goods_seq, $goods_price, $category, $sale_seq, $consumer_price =0, $goods = null){
		$this->load->library('sale');

		$applypage	= 'saleprice';

		//----> sale library 적용
		unset($param,$row['reserve'],$row['point']);
		$param['cal_type']				= 'each';
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$param['consumer_price']		= $consumer_price;
		$param['price']					= $goods_price;
		$param['ea']					= 1;
		$param['category_code']			= $category;
		$param['goods_seq']				= $goods_seq;
		$param['goods']					= $goods;
		$this->sale->set_init($param);
		$sales		= $this->sale->calculate_sale_price($applypage);
		$sale_price	= $sales['result_price'];
		$this->sale->reset_init();
		unset($sales);
		//<---- sale library 적용

		return $sale_price;
	}

	// 실제 주문을 검색하여 출고예약량을 업데이트합니다.
	public function modify_reservation_real($goods_seq,$mode='auto')
	{
		$query = "update fm_goods_supply set reservation15 = 0,reservation25 = 0 where goods_seq=?";
		$this->db->query($query,array($goods_seq));

		$query = "update fm_goods_supply s,(
					select o.option_seq,ord.ea25,ord.ea15,ord.step35 from fm_goods_option o,
					(
						select sum(if(io.step>=25 and io.step<=45,io.ea,0)) ea25,
							sum(if(io.step>=15 and io.step<=45,io.ea,0)) ea15,
							sum(if(io.step in(50,60,70) and io.step35>0,io.step35,0)) step35,
							io.option1,io.option2,io.option3,io.option4,io.option5
						from fm_order_item i,fm_order_item_option io
						where i.goods_seq=?
							and i.item_seq=io.item_seq
							and ((io.step >= 15 and io.step <= 45) or io.step in (50,60,70))
						group by io.option1,io.option2,io.option3,io.option4,io.option5
					) ord
					where o.goods_seq=?
					and ord.option1=o.option1
					and ord.option2=o.option2
					and ord.option3=o.option3
					and ord.option4=o.option4
					and ord.option5=o.option5
				) opt
				set s.reservation25 = opt.ea25+opt.step35,
					s.reservation15 = opt.ea15+opt.step35
				where
				s.option_seq=opt.option_seq";
		$this->db->query($query,array($goods_seq,$goods_seq));

		$query = "update fm_goods_supply s,(
					select o.suboption_seq,ord.ea25,ord.ea15,ord.step35 from fm_goods_suboption o,
					(
						select sum(if(io.step>=25 and io.step<=45,io.ea,0)) ea25,
							sum(if(io.step>=15 and io.step<=45,io.ea,0)) ea15,
							sum(if(io.step in(50,60,70) and io.step35>0,io.step35,0)) step35,
							io.title,io.suboption
						from fm_order_item i,fm_order_item_suboption io
						where i.goods_seq=?
							and i.item_seq=io.item_seq
							and ((io.step >= 15 and io.step <= 45) or io.step in (50,60,70))
						group by io.title,io.suboption
					) ord
					where o.goods_seq=?
					and ord.title = o.suboption_title
					and ord.suboption = o.suboption
				) opt
				set s.reservation25 = opt.ea25+opt.step35,
					s.reservation15 = opt.ea15+opt.step35
				where
				s.suboption_seq=opt.suboption_seq";
		$this->db->query($query,array($goods_seq,$goods_seq));

		$query ="update fm_goods_supply s,fm_goods_option o set s.reservation15=s.stock
		where o.goods_seq=? and o.option_seq=s.option_seq and s.stock < s.reservation15";
		$this->db->query($query,array($goods_seq));

		$query ="update fm_goods_supply s,fm_goods_option o set s.reservation25=s.stock
		where o.goods_seq=? and o.option_seq=s.option_seq and s.stock < s.reservation25";
		$this->db->query($query,array($goods_seq));

		$this->runout_check($goods_seq,$mode);
	}


	public function get_add_option_code(){
		$this->load->helper("goods");
		$query		= "select * from fm_goods_code_form "
					. "where label_type ='goodsoption' order by label_type, sort_seq";
		$rs			= $this->db->query($query);
		foreach($rs->result_array() as $code_datarow){
			$code_datarow['label_write']	= get_labelitem_type($code_datarow,'','');
			$codes							= explode('|', $code_datarow['label_code']);
			$values							= explode('|', $code_datarow['label_value']);
			$defaults						= explode('|', $code_datarow['label_default']);
			$colors							= explode('|', $code_datarow['label_color']);
			$zipcodes						= explode('|', $code_datarow['label_zipcode']);
			$address_type						= explode('|', $code_datarow['label_address_type']);
			$address								= explode('|', $code_datarow['label_address']);
			$address_street					= explode('|', $code_datarow['label_address_street']);
			$addressdetail						= explode('|', $code_datarow['label_addressdetail']);
			$biztel									= explode('|', $code_datarow['label_biztel']);
			$address_commission			= explode('|', $code_datarow['label_address_commission']);

			$codedate							= explode('|', $code_datarow['label_date']);
			$sdayinput							= explode('|', $code_datarow['label_sdayinput']);
			$fdayinput							= explode('|', $code_datarow['label_fdayinput']);
			$dayauto_type					= explode('|', $code_datarow['label_dayauto_type']);
			$sdayauto							= explode('|', $code_datarow['label_sdayauto']);
			$fdayauto							= explode('|', $code_datarow['label_fdayauto']);
			$dayauto_day						= explode('|', $code_datarow['label_dayauto_day']);

			$code_arr						= array();
			$codes_cnt						= count($codes);
			for ($c = 0; $c < $codes_cnt; $c++){
				if	($codes[$c]){
					$code_arr[]	= array(
						'code'=>$codes[$c],'value'=>$values[$c],'default'=>$defaults[$c],
						'colors'=>$colors[$c],
						'zipcode'=>$zipcodes[$c],'address_type'=>$address_type[$c],'address'=>$address[$c],'address_street'=>$address_street[$c],'addressdetail'=>$addressdetail[$c],'biztel'=>$biztel[$c],
						'codedate'=>$codedate[$c],
						'sdayinput'=>$sdayinput[$c],'fdayinput'=>$fdayinput[$c],
						'dayauto_type'=>$dayauto_type[$c],'sdayauto'=>$sdayauto[$c],'fdayauto'=>$fdayauto[$c],'dayauto_day'=>$dayauto_day[$c],'address_commission'=>$address_commission[$c]);
				}
			}
			$code_datarow['code_arr']		= $code_arr;

			$result[]						= $code_datarow;
		}

		return $result;
	}

	public function get_add_suboption_code(){
		$this->load->helper("goods");
		$query		= "select * from fm_goods_code_form  "
					. "where label_type ='goodssuboption'  order by label_type, sort_seq";
		$rs			= $this->db->query($query);
		foreach($rs->result_array() as $code_datarow){
			$code_datarow['label_write']	= get_labelitem_type($code_datarow,'','');
			$codes							= explode('|', $code_datarow['label_code']);
			$values							= explode('|', $code_datarow['label_value']);
			$defaults						= explode('|', $code_datarow['label_default']);

			$colors							= explode('|', $code_datarow['label_color']);
			$zipcodes						= explode('|', $code_datarow['label_zipcode']);
			$address_type					= explode('|', $code_datarow['label_address_type']);
			$address						= explode('|', $code_datarow['label_address']);
			$address_street					= explode('|', $code_datarow['label_address_street']);
			$addressdetail				= explode('|', $code_datarow['label_addressdetail']);
			$biztel							= explode('|', $code_datarow['label_biztel']);

			$codedate					= explode('|', $code_datarow['label_date']);
			$sdayinput					= explode('|', $code_datarow['label_sdayinput']);
			$fdayinput					= explode('|', $code_datarow['label_fdayinput']);
			$dayauto_type			= explode('|', $code_datarow['label_dayauto_type']);
			$sdayauto					= explode('|', $code_datarow['label_sdayauto']);
			$fdayauto					= explode('|', $code_datarow['label_fdayauto']);
			$dayauto_day				= explode('|', $code_datarow['label_dayauto_day']);

			$code_arr						= array();
			$codes_cnt						= count($codes);
			for ($c = 0; $c < $codes_cnt; $c++){
				if	($codes[$c]){
					$code_arr[]	= array('code'=>$codes[$c],'value'=>$values[$c],'default'=>$defaults[$c],
						'colors'=>$colors[$c],
						'zipcode'=>$zipcodes[$c],'address_type'=>$address_type[$c],'address'=>$address[$c],'address_street'=>$address_street[$c],'addressdetail'=>$addressdetail[$c],'biztel'=>$biztel[$c],
						'codedate'=>$codedate[$c],
						'sdayinput'=>$sdayinput[$c],'fdayinput'=>$fdayinput[$c],
						'dayauto_type'=>$dayauto_type[$c],'sdayauto'=>$sdayauto[$c],'fdayauto'=>$fdayauto[$c],'dayauto_day'=>$dayauto_day[$c]);
				}
			}
			$code_datarow['code_arr']		= $code_arr;

			$result[]						= $code_datarow;
		}

		return $result;
	}

	/**
	* 최초생성된 임시정보 가져오기
	**/
	public function get_option_tmp_list($tmp_seq){
		$opt1Arr	= array();$opt2Arr	= array();$opt3Arr	= array();$opt4Arr	= array();$opt5Arr	= array();
		$code1Arr	= array();$code2Arr	= array();$code3Arr	= array();$code4Arr	= array();$code5Arr	= array();
		$price1Arr	= array();$price2Arr	= array();$price3Arr	= array();$price4Arr	= array();$price5Arr	= array();
		$color1Arr	= array();$color2Arr	= array();	$color3Arr= array();$color4Arr	= array();$color5Arr	= array();
		$zipcode1Arr	= array();$zipcode2Arr	= array();$zipcode3Arr	= array();$zipcode4Arr	= array();$zipcode5Arr	= array();
		$address1Arr	= array();$address_typeArr	= array();$address_streetArr	= array();
		$address2Arr	= array();$address3Arr	= array();$address4Arr	= array();$address5Arr	= array();
		$addressdetail1Arr	= array();$addressdetail2Arr	= array();$addressdetail3Arr	= array();$addressdetail4Arr	= array();$addressdetail5Arr	= array();

		$address_commission1Arr	= array();$address_commission2Arr	= array();$address_commission3Arr	= array();$address_commission4Arr	= array();$address_commission5Arr	= array();


		$biztel1Arr	= array();$biztel2Arr	= array();$biztel3Arr	= array();$biztel4Arr	= array();$biztel5Arr	= array();
		$codedate1Arr	= array();$codedate2Arr	= array();$codedate3Arr	= array();$codedate4Arr	= array();$codedate5Arr	= array();

		$result		= false;
		$sql = "select o.*,s.badstock, s.stock, s.supply_price, s.reservation15, s.reservation25 from fm_goods_option_tmp o left join fm_goods_supply_tmp s on o.option_seq=s.option_seq where o.tmp_no=? order by o.option_seq asc";
		$query = $this->db->query($sql,array($tmp_seq));
		foreach($query->result_array() as $data){
			$optJoin = "";$optcodeJoin = "";
			if( $data['option_title'] ) $data['option_divide_title'] = explode(',',$data['option_title']);

			if( $data['option_type'] ) $data['option_divide_type'] = explode(',',$data['option_type']);
			if( $data['code_seq'] ) $data['option_divide_codeseq'] = explode(',',$data['code_seq']);

			if( $data['tmpprice'] )			$data['divide_tmpprice']			= explode(',',$data['tmpprice']);
			if( $data['newtype'] ) $data['divide_newtype'] = explode(',',$data['newtype']);

			if( $data['color'] )					$data['divide_color']				= trim($data['color']);
			if( $data['zipcode'] )				$data['divide_zipcode']			= ($data['zipcode']);
			if( $data['address_type'] )			$data['divide_address_type']			= ($data['address_type']);
			if( $data['address'] )			$data['divide_address']			= ($data['address']);
			if( $data['address_street'] )			$data['divide_address_street']			= ($data['address_street']);
			if( $data['addressdetail'] )	$data['divide_addressdetail']	= ($data['addressdetail']);
			if( $data['biztel'] )				$data['divide_biztel']					= ($data['biztel']);
			if( $data['address_commission'] )	$data['divide_address_commission']	= ($data['address_commission']);
			if( $data['codedate'] )			$data['divide_codedate']			= ($data['codedate']);

			if( $data['dayauto_type'] ) $data['dayauto_type_title'] = $this->dayautotype[$data['dayauto_type']];
			if( $data['dayauto_day'] ) $data['dayauto_day_title'] = $this->dayautoday[$data['dayauto_day']];

			if( $data['newtype'] )			$data['divide_newtype']			= explode(',',$data['newtype']);

			if( $data['option1']!='' ) $optJoin[] = $data['option1'];
			if( $data['option2']!='' ) $optJoin[] = $data['option2'];
			if( $data['option3']!='' ) $optJoin[] = $data['option3'];
			if( $data['option4']!='' ) $optJoin[] = $data['option4'];
			if( $data['option5']!='' ) $optJoin[] = $data['option5'];
			if( $optJoin ){
				$data['opts'] = $optJoin;
			}
			if( $data['option1']!='' ) $optcodeJoin[] = $data['optioncode1'];
			if( $data['option2']!='' ) $optcodeJoin[] = $data['optioncode2'];
			if( $data['option3']!='' ) $optcodeJoin[] = $data['optioncode3'];
			if( $data['option4']!='' ) $optcodeJoin[] = $data['optioncode4'];
			if( $data['option5']!='' ) $optcodeJoin[] = $data['optioncode5'];
			if( $optcodeJoin ){
				$data['optcodes'] = $optcodeJoin;
			}

			// 세로로 묶음
			if( $data['option1']!='' && !in_array($data['option1'], $opt1Arr)){
				$opt1Arr[]		= $data['option1'];
				$code1Arr[]		= $data['optioncode1'];
				$price1Arr[]		= $data['divide_tmpprice'][0];
			}
			if( $data['option2'] != '' && !in_array($data['option2'], $opt2Arr) ){
				$opt2Arr[]		= $data['option2'];
				$code2Arr[]		= $data['optioncode2'];
				$price2Arr[]		= $data['divide_tmpprice'][1];
			}

			if( $data['option3'] != '' && !in_array($data['option3'], $opt3Arr) ){
				$opt3Arr[]		= $data['option3'];
				$code3Arr[]		= $data['optioncode3'];
				$price3Arr[]		= $data['divide_tmpprice'][2];
			}
			if( $data['option4'] != '' && !in_array($data['option4'], $opt4Arr) ){
				$opt4Arr[]		= $data['option4'];
				$code4Arr[]		= $data['optioncode4'];
				$price4Arr[]		= $data['divide_tmpprice'][3];
			}
			if( $data['option5'] != '' && !in_array($data['option5'], $opt5Arr) ){
				$opt5Arr[]		= $data['option5'];
				$code5Arr[]		= $data['optioncode5'];
				$price5Arr[]		= $data['divide_tmpprice'][4];
			}

			if	($data['consumer_price']){
				$data['supplyRate'] = floor($data['supply_price'] / $data['consumer_price'] * 100);
				$data['discountRate'] = 100 - floor($data['price'] / $data['consumer_price'] * 100);
			}
			$data['tax']		= floor($data['price'] - ($data['price'] / 1.1));
			$data['net_profit']	= $data['price'] - $data['supply_price'];

			$result[] = $data;
		}

		if	($result[0]){
			$result[0]['optionArr'][]	= $opt1Arr;
			$result[0]['optionArr'][]	= $opt2Arr;
			$result[0]['optionArr'][]	= $opt3Arr;
			$result[0]['optionArr'][]	= $opt4Arr;
			$result[0]['optionArr'][]	= $opt5Arr;
			$result[0]['codeArr'][]		= $code1Arr;
			$result[0]['codeArr'][]		= $code2Arr;
			$result[0]['codeArr'][]		= $code3Arr;
			$result[0]['codeArr'][]		= $code4Arr;
			$result[0]['codeArr'][]		= $code5Arr;
			$result[0]['priceArr'][]	= $price1Arr;
			$result[0]['priceArr'][]	= $price2Arr;
			$result[0]['priceArr'][]	= $price3Arr;
			$result[0]['priceArr'][]	= $price4Arr;
			$result[0]['priceArr'][]	= $price5Arr;

			//등록된 옵션정보의 상품코드 정보 가져오기
			for ($o = 0; $o < 5; $o++) {
				if ($result[0]['divide_newtype'][$o] == 'color' ) {
					$codeqry = "select * from fm_goods_code_form  where label_type ='goodsoption' and codeform_seq = '".$result[0]['option_divide_codeseq'][$o]."' order by label_type, sort_seq";
					$codequery = $this->db->query($codeqry);
					$code_arr = $codequery -> result_array();
					foreach ($code_arr as $code_datarow){
						$label_code_ar = explode("|", $code_datarow['label_code']);
						$label_color_ar = explode("|", $code_datarow['label_color']);
						$idx=0;
						foreach($label_code_ar as $code) {
							if( in_array( $code , $result[0]['codeArr'][$o]) ) {
								$colorArr[] = $label_color_ar[$idx];
							}
							$idx++;
						}
					}
					$result[0]['colorArr'][]					= $colorArr;
				}elseif($result[0]['divide_newtype'][$o] == 'address' ) {
					$codeqry = "select * from fm_goods_code_form  where label_type ='goodsoption' and codeform_seq = '".$result[0]['option_divide_codeseq'][$o]."' order by label_type, sort_seq";
					$codequery = $this->db->query($codeqry);
					$code_arr = $codequery -> result_array();
					foreach ($code_arr as $code_datarow){
						$label_code_ar = explode("|", $code_datarow['label_code']);
						$label_zipcode_ar = explode("|", $code_datarow['label_zipcode']);
						$label_address_type_ar = explode("|", $code_datarow['label_address_type']);
						$label_address_ar = explode("|", $code_datarow['label_address']);
						$label_address_street_ar = explode("|", $code_datarow['label_address_street']);
						$label_addressdetail_ar = explode("|", $code_datarow['label_addressdetail']);
						$label_biztel_ar = explode("|", $code_datarow['label_biztel']);
						$label_address_commission_ar = explode("|", $code_datarow['label_address_commission']);
						$idx=0;
						foreach($label_code_ar as $code) {
							if( in_array( $code , $result[0]['codeArr'][$o]) ) {
								$zipcodeArr[] = $label_zipcode_ar[$idx];
								$address_typeArr[] = $label_address_type_ar[$idx];
								$addressArr[] = $label_address_ar[$idx];
								$address_streetArr[] = $label_address_street_ar[$idx];
								$addressdetailArr[] = $label_addressdetail_ar[$idx];
								$biztelArr[] = $label_biztel_ar[$idx];
								$address_commissionArr[] = $label_address_commission_ar[$idx];
							}
							$idx++;
						}
					}
					$result[0]['zipcodeArr'][]				= $zipcodeArr;
					$result[0]['address_typeArr'][]			= $address_typeArr;
					$result[0]['addressArr'][]				= $addressArr;
					$result[0]['address_streetArr'][]		= $address_streetArr;
					$result[0]['addressdetailArr'][]	= $addressdetailArr;
					$result[0]['biztelArr'][]					= $biztelArr;
					$result[0]['address_commissionArr'][]					= $address_commissionArr;
				}elseif($result[0]['divide_newtype'][$o] == 'date' ){
					$codeqry = "select * from fm_goods_code_form  where label_type ='goodsoption' and codeform_seq = '".$result[0]['option_divide_codeseq'][$o]."' order by label_type, sort_seq";
					$codequery = $this->db->query($codeqry);
					$code_arr = $codequery -> result_array();
					foreach ($code_arr as $code_datarow){
						$label_code_ar = explode("|", $code_datarow['label_code']);
						$label_date_ar = explode("|", $code_datarow['label_date']);
						$idx=0;
						foreach($label_code_ar as $code) {
							if( in_array( $code , $result[0]['codeArr'][$o]) ) {
								$codedateArr[] = $label_date_ar[$idx];
							}
							$idx++;
						}
					}
					$result[0]['codedateArr'][]			= $codedateArr;
				}
			}
		}

		return $result;
	}

	public function get_suboption_tmp_list($tmp_seq, $mode = ''){
		$result	= false;
		$arr	= array();
		// 적립금 기본 정책
		$reserves		= config_load('reserve');
		$reserve_rate	= $reserves['default_reserve_percent'];

		$query	= "select o.*,s.stock,s.badstock,s.supply_price, s.reservation15, s.reservation25  from fm_goods_suboption_tmp o,fm_goods_supply_tmp s where o.suboption_seq=s.suboption_seq and o.tmp_no=? order by o.suboption_seq asc";
		$query	= $this->db->query($query,array($tmp_seq));
		$idx	= 0;
		foreach($query->result_array() as $data){
			if(!in_array($data['suboption_title'],$arr)) $arr[] = $data['suboption_title'];
			list($key) = array_keys($arr,$data['suboption_title']);
			$data['idx']		= $idx;
			if	($data['consumer_price']){
				$data['supplyRate'] = floor($data['supply_price'] / $data['consumer_price'] * 100);
				$data['discountRate'] = 100 - floor($data['price'] / $data['consumer_price'] * 100);
			}
			$data['tax']		= floor($data['price'] - ($data['price'] / 1.1));
			$data['net_profit']	= $data['price'] - $data['supply_price'];

			if	($key != $bkey){
				$result[$bkey][0]['optArr']	= $optArr;
				$result[$bkey][0]['codeArr']	= $codeArr;
				$result[$bkey][0]['priceArr']	= $priceArr;

				$result[$bkey][0]['colorArr']					= $colorArr;
				$result[$bkey][0]['zipcodeArr']			= $zipcodeArr;
				$result[$bkey][0]['address_typeArr']			= $address_typeArr;
				$result[$bkey][0]['addressArr']			= $addressArr;
				$result[$bkey][0]['address_streetArr']			= $address_streetArr;
				$result[$bkey][0]['addressdetailArr']	= $addressdetailArr;
				$result[$bkey][0]['biztelArr']				= $biztelArr;
				$result[$bkey][0]['codedateArr']			= $codedateArr;

				unset($optArr,$codeArr,$codeArr,$priceArr,$colorArr,$zipcodeArr,$address_typeArr,$addressArr,$address_streetArr,$addressdetailArr,$biztelArr,$codedateArr);
				/**
				$optArr		= '';
				$codeArr	= '';
				$priceArr	= '';
				$colorArr		= '';
				$zipcodeArr	= '';
				$addressArr	= '';
				$addressdetailArr	= '';
				$biztelArr	= '';
				$codedateArr	= '';
				**/

			}
			$bkey		= $key;
			$optArr[]	= $data['suboption'];
			$codeArr[]	= $data['suboption_code'];

			$colorArr[]							= trim($data['color']);
			$zipcodeArr[]			= $data['zipcode'];
			$address_typeArr[]			= $data['address_type'];
			$addressArr[]			= $data['address'];
			$address_streetArr[]			= $data['address_street'];
			$addressdetailArr[]	= $data['addressdetail'];
			$biztelArr[]				= $data['biztel'];
			$codedateArr[]			= $data['codedate'];

			$priceArr[]	= $data['price'];

			if	($mode == 'chgPolicy'){
				$data['reserve_rate']	= $reserve_rate;
				$data['reserve_unit']	= 'percent';
				$data['reserve']		= 0;
				if	($data['price'] > 0){
					$data['reserve']	= round($data['price'] * ($reserve_rate * 0.01));
				}

				$params['reserve_rate']	= $data['reserve_rate'];
				$params['reserve_unit']	= $data['reserve_unit'];
				$params['reserve']		= $data['reserve'];
				$this->db->where(array("tmp_no"=>$tmp_seq,"suboption_seq"=>$data['suboption_seq']));
				$this->db->update('fm_goods_suboption_tmp', $params);
			}

			if( $data['dayauto_type'] ) $data['dayauto_type_title'] = $this->dayautotype[$data['dayauto_type']];
			if( $data['dayauto_day'] ) $data['dayauto_day_title'] = $this->dayautoday[$data['dayauto_day']];

			$result[$key][] = $data;
			$idx++;
		}

		if	($result[$key][0]){
			$result[$key][0]['optArr']					= $optArr;
			$result[$key][0]['codeArr']					= $codeArr;
			$result[$key][0]['priceArr']				= $priceArr;

			$result[$key][0]['colorArr']				= $colorArr;
			$result[$key][0]['zipcodeArr']				= $zipcodeArr;
			$result[$key][0]['address_typeArr']			= $address_typeArr;
			$result[$key][0]['addressArr']				= $addressArr;
			$result[$key][0]['address_streetArr']		= $address_streetArr;
			$result[$key][0]['addressdetailArr']		= $addressdetailArr;
			$result[$key][0]['biztelArr']				= $biztelArr;
			$result[$key][0]['codedateArr']				= $codedateArr;
		}

		return $result;
	}

	/**
	* 필수옵션 수정시 임시옵션정보 생성 (opt 2단계)
	**/
	public function add_option_tmp_to_option_org($goods_seq){

		$tmp_seq	= date('YmdHis').$this->managerInfo['manager_id'];

		if	($goods_seq){
			$query		= "select * from fm_goods_option where goods_seq = ? ";
			$rs			= $this->db->query($query,array($goods_seq));
			foreach($rs->result_array() as $list){
				$option_seq		= $list['option_seq'];
				$default		= 'n';
				$reserve_unit	= 'percent';

				$supplySql		= "select * from fm_goods_supply where option_seq = ? ";
				$supplyRs		= $this->db->query($supplySql,array($option_seq));
				$org_supply		= $supplyRs->result_array();
				$orgSupply		= $org_supply[0];

				if($list['default_option'] == 'y' && $setDefaultY != 'y'){
					$setDefaultY = $default = 'y';
				}
				if(!is_null($list['reserve_unit']))
					$reserve_unit	= $list['reserve_unit'];

				$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$options['code_seq']		= $list['code_seq'];
				$options['option_type']		= $list['option_type'];
				$options['default_option']	= $default;
				$options['option_title']	= $this->optionReplace($list['option_title']);
				$options['option1']			= $this->optionReplace($list['option1']);
				$options['option2']			= $this->optionReplace($list['option2']);
				$options['option3']			= $this->optionReplace($list['option3']);
				$options['option4']			= $this->optionReplace($list['option4']);
				$options['option5']			= $this->optionReplace($list['option5']);
				$options['optioncode1']		= $list['optioncode1'];
				$options['optioncode2']		= $list['optioncode2'];
				$options['optioncode3']		= $list['optioncode3'];
				$options['optioncode4']		= $list['optioncode4'];
				$options['optioncode5']		= $list['optioncode5'];
				$options['coupon_input']		= $this->optionReplace($list['coupon_input'], 'int');
				$options['consumer_price']	= $this->optionReplace($list['consumer_price'], 'int');
				$options['price']			= $this->optionReplace($list['price'], 'int');
				$options['reserve_rate']	= $this->optionReplace($list['reserve_rate'], 'int');
				$options['reserve_unit']	= $reserve_unit;
				$options['reserve']			= $this->optionReplace($list['reserve'], 'int');
				$options['infomation']		= $list['infomation'];
				$options['commission_rate']	= $this->optionReplace($list['commission_rate'], 'int');
				$options['tmp_policy']		= $_GET['tmp_policy'];
				$options['tmp_date']		= date('Ymd');
				$options['tmp_no']			= $tmp_seq;

				$options['newtype']			= $list['newtype'];
				$options['tmpprice']			= $list['tmpprice'];
				$options['color']						= trim($list['color']);
				$options['zipcode']			= $list['zipcode'];
				$options['address_type']					= $list['address_type'];
				$options['address']							= $list['address'];
				$options['address_street']				= $list['address_street'];
				$options['addressdetail']					= $list['addressdetail'];
				$options['biztel']								= $list['biztel'];
				$options['address_commission']		= $list['address_commission'];

				$options['codedate']	= $list['codedate'];
				$options['sdayinput']	= $list['sdayinput'];
				$options['fdayinput']	= $list['fdayinput'];
				$options['dayauto_type']	= $list['dayauto_type'];
				$options['sdayauto']	= $list['sdayauto'];
				$options['fdayauto']	= $list['fdayauto'];
				$options['dayauto_day']	= $list['dayauto_day'];
				$options['fix_option_seq']	= $list['fix_option_seq'];

				$this->db->insert( 'fm_goods_option_tmp', $options );
				$option_seq	= $this->db->insert_id();

				if	($option_seq){
					$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
					$supply['option_seq']		= $option_seq;
					$supply['supply_price']		= $this->optionReplace($orgSupply['supply_price'], 'int');
					$supply['stock']			= $this->optionReplace($orgSupply['stock'], 'int');
					$supply['badstock']			= $this->optionReplace($orgSupply['badstock'], 'int');
					$supply['reservation15']	= $this->optionReplace($orgSupply['reservation15'], 'int');
					$supply['reservation25']	= $this->optionReplace($orgSupply['reservation25'], 'int');
					$supply['ablestock15']		= $this->optionReplace($orgSupply['ablestock15'], 'int');
					$supply['tmp_date']			= date('Ymd');
					$supply['tmp_no']			= $tmp_seq;
					$this->db->insert( 'fm_goods_supply_tmp', $supply );
				}
			}
		}

		return $tmp_seq;
	}


	/**
	* 추가구성옵션 수정시 임시옵션정보 생성 (subopt 2단계)
	**/
	public function add_suboption_tmp_to_suboption_org($goods_seq, $mode = ''){

		$tmp_seq	= date('YmdHis').$this->managerInfo['manager_id'];
		// 적립금 기본 정책
		$reserves		= config_load('reserve');
		$reserve_rate	= $reserves['default_reserve_percent'];

		$query		= "select * from fm_goods_suboption where goods_seq = ? ";
		$rs			= $this->db->query($query,array($goods_seq));
		foreach($rs->result_array() as $list){
			$suboption_seq	= $list['suboption_seq'];
			$supplySql		= "select * from fm_goods_supply where suboption_seq = ? ";
			$supplyRs		= $this->db->query($supplySql,array($suboption_seq));
			$org_supply		= $supplyRs->result_array();
			$orgSupply		= $org_supply[0];

			if	($list['sub_required'] != 'y')	$list['sub_required']	= 'n';
			if	($list['sub_sale'] != 'y')		$list['sub_sale']		= 'n';

			if	($mode == 'chgPolicy'){
				$list['reserve_rate']	= $reserve_rate;
				$list['reserve_unit']	= 'percent';
				$list['reserve']		= 0;
				if	($list['price'] > 0){
					$list['reserve']	= round($list['price'] * ($reserve_rate * 0.01));
				}
			}


			$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
			$options['code_seq']		= $list['code_seq'];
			$options['sub_required']	= $list['sub_required'];
			$options['sub_sale']		= $list['sub_sale'];
			$options['suboption_type']	= $list['suboption_type'];
			$options['suboption_title']	= $this->optionReplace($list['suboption_title']);
			$options['suboption_code']	= $list['suboption_code'];
			$options['suboption']		= $this->optionReplace($list['suboption']);
			$options['coupon_input']		= $this->optionReplace($list['coupon_input'], 'int');
			$options['consumer_price']	= $this->optionReplace($list['consumer_price'], 'int');
			$options['price']			= $this->optionReplace($list['price'], 'int');
			$options['reserve_rate']	= $this->optionReplace($list['reserve_rate'], 'int');
			$options['reserve_unit']	= $list['reserve_unit'];
			$options['commission_rate']	= $this->optionReplace($list['commission_rate'], 'int');
			$options['reserve']			= $list['reserve'];
			$options['tmp_date']		= date('Ymd');
			$options['tmp_no']			= $tmp_seq;

			$options['newtype']			= $list['newtype'];
			$options['color']						= trim($list['color']);
			$options['zipcode']			= $list['zipcode'];
			$options['address_type']			= $list['address_type'];
			$options['address']			= $list['address'];
			$options['address_street']			= $list['address_street'];
			$options['addressdetail']	= $list['addressdetail'];
			$options['biztel']				= $list['biztel'];

			$options['codedate']		= $list['codedate'];
			$options['sdayinput']		= $list['sdayinput'];
			$options['fdayinput']		= $list['fdayinput'];
			$options['dayauto_type']	= $list['dayauto_type'];
			$options['sdayauto']		= $list['sdayauto'];
			$options['fdayauto']			= $list['fdayauto'];
			$options['dayauto_day']	= $list['dayauto_day'];

			$this->db->insert( 'fm_goods_suboption_tmp', $options );
			$suboption_seq	= $this->db->insert_id();

			if	($suboption_seq){
				$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$supply['suboption_seq']	= $suboption_seq;
				$supply['supply_price']		= $this->optionReplace($orgSupply['supply_price'], 'int');
				$supply['stock']			= $this->optionReplace($orgSupply['stock'], 'int');
				$supply['badstock']			= $this->optionReplace($orgSupply['badstock'], 'int');
				$supply['reservation15']	= $this->optionReplace($orgSupply['reservation15'], 'int');
				$supply['reservation25']	= $this->optionReplace($orgSupply['reservation25'], 'int');
				$supply['ablestock15']		= $this->optionReplace($orgSupply['ablestock15'], 'int');
				$supply['tmp_date']			= date('Ymd');
				$supply['tmp_no']			= $tmp_seq;
				$this->db->insert( 'fm_goods_supply_tmp', $supply );
			}
		}

		return $tmp_seq;
	}

	/**
	* 필수옵션 임시옵션정보 생성 (opt 1단계)
	**/
	public function make_tmp_option($params) {

		if( $params['socialcpuseopen'] == 'price' || $params['socialcpuseopen'] == 'pass' ) {
			if( !( in_array('date',$params['optionMakenewtype']) || in_array('dayinput',$params['optionMakenewtype']) || in_array('dayauto',$params['optionMakenewtype']) ) ){//coupon goods
				$msg = "[쿠폰상품]의 유효기간(날짜, 자동기간, 수동기간)옵션을 추가해 주세요.";
				openDialogAlert($msg,470,150,'parent',$callback);
				exit;
			}
		}

		$query	= "delete from fm_goods_option_tmp where tmp_no = '".$params['tmp_seq']."'";
		$this->db->query($query);
		$query	= "delete from fm_goods_supply_tmp "
				. "where tmp_no = '".$params['tmp_seq']."' and option_seq > 0";
		$this->db->query($query);

		$goods_seq	= trim($params['goods_seq']);
		$tmp_seq	= trim($params['tmp_seq']);
		$socialcpuseopen	= trim($params['socialcpuseopen']);
		$defaults	= 'y';

		// 적립금 기본 정책
		$reserves		= config_load('reserve');
		$reserve_rate	= $reserves['default_reserve_percent'];

		// 옵션 재정의
		$optionList	= array();
		$totalRow	= 1;
		for ($o = 0; $o < 5; $o++){
			if ( trim($params['optionMakeValue'][$o])) {
				$idx++;
				if	($idx > 1)	$addComma	= ',';
				$titles		.= $addComma.$params['optionMakeName'][$o];
				$types		.= $addComma.$params['optionMakeId'][$o];

				$newtypes		.= $addComma.$params['optionMakenewtype'][$o];
				$newtypesar[$o]		= $params['optionMakenewtype'][$o];
				$code_seq		.= $addComma.str_replace("goodsoption_","",$params['optionMakeId'][$o]);

				if($params['optionMakenewtype'][$o] != 'direct' ) {//상품코드
					//색상, 주소
					if($params['optionMakenewtype'][$o] == 'color' && $params['optionMakecolor'][$o]) $colors = explode(",",$params['optionMakecolor'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakezipcode'][$o]) $zipcodes	= explode(",",$params['optionMakezipcode'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakeaddress_type'][$o]) $address_types	= explode(",",$params['optionMakeaddress_type'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakeaddress'][$o]) $addresss	= explode(",",$params['optionMakeaddress'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakeaddress_street'][$o]) $address_streets	= explode(",",$params['optionMakeaddress_street'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakeaddressdetail'][$o]) $addressdetails	= explode(",",$params['optionMakeaddressdetail'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakebiztel'][$o]) $biztels	= explode(",",$params['optionMakebiztel'][$o]);
					if($params['optionMakenewtype'][$o] == 'address' && $params['optionMakeaddress_commission'][$o]) $address_commissions	= explode(",",$params['optionMakeaddress_commission'][$o]);

					//날짜, 수동기간, 자동기간추가
					if($params['optionMakenewtype'][$o] == 'date' && $params['optionMakecodedate'][$o]) $codedate	= explode(",",$params['optionMakecodedate'][$o]);

					if($params['optionMakenewtype'][$o] == 'dayinput' && $params['optionMakesdayinput'][$o])$sdayinput	=explode(",",$params['optionMakesdayinput'][$o]);
					if($params['optionMakenewtype'][$o] == 'dayinput' && $params['optionMakefdayinput'][$o]) $fdayinput	= explode(",",$params['optionMakefdayinput'][$o]);
					if($params['optionMakenewtype'][$o] == 'dayauto' && $params['optionMakedayauto_type'][$o]) $dayauto_type= explode(",",$params['optionMakedayauto_type'][$o]);
					if($params['optionMakenewtype'][$o] == 'dayauto' && $params['optionMakesdayauto'][$o]>=0) $sdayauto = explode(",",$params['optionMakesdayauto'][$o]);
					if($params['optionMakenewtype'][$o] == 'dayauto' && $params['optionMakefdayauto'][$o]) $fdayauto	= explode(",",$params['optionMakefdayauto'][$o]);
					if($params['optionMakenewtype'][$o] == 'dayauto' && $params['optionMakedayauto_day'][$o]) $dayauto_day	= explode(",",$params['optionMakedayauto_day'][$o]);
				}

				${'option'.$idx.'_arr'}	= explode(',', trim($params['optionMakeValue'][$o]));
				${'option'.$idx.'_cnt'}	= count(${'option'.$idx.'_arr'});
				${'price'.$idx.'_arr'}	= explode(',', trim($params['optionMakePrice'][$o]));
				${'code'.$idx.'_arr'}	= explode(',', trim($params['optionMakeCode'][$o]));
				$totalRow				= $totalRow * ${'option'.$idx.'_cnt'};
				$lastOpt				= $idx;
			}
		}
		$colordepth = array_keys($newtypesar, "color");
		$addressdepth = array_keys($newtypesar, "address");
		$datedepth = array_keys($newtypesar, "date");
		$dayinputdepth = array_keys($newtypesar, "dayinput");
		$dayautodepth = array_keys($newtypesar, "dayauto");

		$optidx = $o1 = $o2 = $o3 = $o4 = $o5 = 0;
		for ($o = 1; $o <= $totalRow; $o++){

			$nOpt	= $lastOpt;
			while ($nOpt > 0){
				$nCnt	= ${'option'.$nOpt.'_cnt'} - 1;
				if	(${'o'.$nOpt} > $nCnt){
					${'o'.$nOpt}	= 0;
					$aOpt	= $nOpt - 1;
					${'o'.$aOpt}++;
				}
				$nOpt	= $nOpt - 1;
			}

			$price	= $price1_arr[$o1] + $price2_arr[$o2] + $price3_arr[$o3]
					+ $price4_arr[$o4] + $price5_arr[$o5];

			$reserve	= 0;
			if	($price > 0){
				$reserve		= round($price * ($reserve_rate * 0.01));
			}

			$option1		= (!is_null($option1_arr[$o1])) ? $option1_arr[$o1] : '';
			$option2		= (!is_null($option2_arr[$o2])) ? $option2_arr[$o2] : '';
			$option3		= (!is_null($option3_arr[$o3])) ? $option3_arr[$o3] : '';
			$option4		= (!is_null($option4_arr[$o4])) ? $option4_arr[$o4] : '';
			$option5		= (!is_null($option5_arr[$o5])) ? $option5_arr[$o5] : '';

			$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
			$options['code_seq']		= $this->optionReplace($code_seq);
			$options['default_option']	= ($defaults) ? $defaults : 'n';
			$options['option_type']		= $this->optionReplace($types);
			$options['option_title']			= $this->optionReplace($titles);
			$options['option1']			= $this->optionReplace($option1);
			$options['option2']			= $this->optionReplace($option2);
			$options['option3']			= $this->optionReplace($option3);
			$options['option4']			= $this->optionReplace($option4);
			$options['option5']			= $this->optionReplace($option5);
			$options['optioncode1']		= $code1_arr[$o1];
			$options['optioncode2']		= $code2_arr[$o2];
			$options['optioncode3']		= $code3_arr[$o3];
			$options['optioncode4']		= $code4_arr[$o4];
			$options['optioncode5']		= $code5_arr[$o5];

			$tmpprices			= $price1_arr[$o1]. ',' . $price2_arr[$o2]. ',' . $price3_arr[$o3]. ',' . $price4_arr[$o4]. ',' . $price5_arr[$o5];

			$options['newtype']				= $this->optionReplace($newtypes);
			$options['tmpprice']				= $tmpprices;

			$oo = ($optidx%5);
			$coo = array_keys(${'code'.($colordepth[0]+1).'_arr'}, $options['optioncode'.($colordepth[0]+1)]);
			$aoo = array_keys(${'code'.($addressdepth[0]+1).'_arr'}, $options['optioncode'.($addressdepth[0]+1)]);
			$doo = array_keys(${'code'.($datedepth[0]+1).'_arr'}, $options['optioncode'.($datedepth[0]+1)]);

			$dioo = array_keys(${'code'.($dayinputdepth[0]+1).'_arr'}, $options['optioncode'.($dayinputdepth[0]+1)]);
			$daoo = array_keys(${'code'.($dayautodepth[0]+1).'_arr'}, $options['optioncode'.($dayautodepth[0]+1)]);

			//색상, 주소
			$options['color']					= $colors[$coo[0]];
			$options['zipcode']				= $zipcodes[$aoo[0]];
			$options['address_type']					= $address_types[$aoo[0]];
			$options['address']							= $addresss[$aoo[0]];
			$options['address_street']				= $address_streets[$aoo[0]];
			$options['addressdetail']					= $addressdetails[$aoo[0]];
			$options['biztel']								= $biztels[$aoo[0]];
			$options['address_commission']		= $address_commissions[$aoo[0]];

			//날짜, 수동기간, 자동기간추가
			$options['codedate']			= $codedate[$doo[0]];

			$options['sdayinput']			= $sdayinput[0];//$dioo[0]
			$options['fdayinput']			= $fdayinput[0];//$dioo[0]
			$options['dayauto_type']		= $dayauto_type[0];//$daoo[0]
			$options['sdayauto']			= $sdayauto[0];//$daoo[0]
			$options['fdayauto']				= $fdayauto[0];//$daoo[0]
			$options['dayauto_day']		= $dayauto_day[0];//$daoo[0]

			$options['coupon_input']		= ($socialcpuseopen == 'pass' || $price == 0 )?1:$this->optionReplace($price, 'int');
			$options['consumer_price']	= '0';
			$options['price']			= $this->optionReplace($price, 'int');
			$options['reserve_rate']	= $this->optionReplace($reserve_rate, 'int');
			$options['reserve_unit']	= 'percent';
			$options['reserve']			= $this->optionReplace($reserve, 'int');
			$options['infomation']		= '';
			$options['commission_rate']	= '0';
			$options['tmp_date']		= date('Ymd');
			$options['tmp_no']			= $tmp_seq;


			$this->db->insert( 'fm_goods_option_tmp', $options );
			$option_seq	= $this->db->insert_id();

			if	($option_seq){
				$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$supply['option_seq']		= $option_seq;
				$supply['supply_price']		= '0';
				$supply['stock']			= '0';
				$supply['badstock']			= '0';
				$supply['reservation15']	= '0';
				$supply['reservation25']	= '0';
				$supply['ablestock15']		= '0';
				$supply['tmp_date']			= date('Ymd');
				$supply['tmp_no']			= $tmp_seq;
				$this->db->insert( 'fm_goods_supply_tmp', $supply );
			}

			$defaults = 'n';
			${'o'.$lastOpt}++;
			$optidx++;
		}
		//exit;
	}

	/**
	* 추가구성옵션 임시옵션정보 최초생성 (subopt 1단계)
	**/
	public function make_suboption_tmp($params) {

		$query	= "delete from fm_goods_suboption_tmp where tmp_no = '".$params['tmp_seq']."'";
		$this->db->query($query);
		$query	= "delete from fm_goods_supply_tmp "
				. "where tmp_no = '".$params['tmp_seq']."' and suboption_seq > 0";
		$this->db->query($query);

		$reserves		= config_load('reserve');
		$reserve_rate	= $reserves['default_reserve_percent'];

		$goods_seq	= ($params['goods_seq']) ? $params['goods_seq'] : '0';
		$socialcpuseopen	= trim($params['socialcpuseopen']);

		$titleCnt	= count($params['suboptionMakeName']);
		for ($lo = 0; $lo < $titleCnt; $lo++){
			if	($params['suboptionMakeValue'][$lo] ) {

				$subTitle	= $params['suboptionMakeName'][$lo];

				$subType	= $params['suboptionMakeId'][$lo];
				$code_seq	= str_replace('goodssuboption_', '', $subType);

				$prices	= explode(',', $params['suboptionMakePrice'][$lo]);
				$codes	= explode(',', $params['suboptionMakeCode'][$lo]);
				$values	= explode(',', $params['suboptionMakeValue'][$lo]);

				$newtype	= $params['suboptionMakenewtype'][$lo];

				//색상, 주소
				$colors					= ($newtype == 'color')?explode(",",$params['suboptionMakecolor'][$lo]):'';
				$zipcodes				= ($newtype == 'address')?explode(",",$params['suboptionMakezipcode'][$lo]):'';
				$address_types			= ($newtype == 'address')?explode(",",$params['suboptionMakeaddress_type'][$lo]):'';
				$addresss			= ($newtype == 'address')?explode(",",$params['suboptionMakeaddress'][$lo]):'';
				$address_streets			= ($newtype == 'address')?explode(",",$params['suboptionMakeaddress_street'][$lo]):'';
				$addressdetails	= ($newtype == 'address')?explode(",",$params['suboptionMakeaddressdetail'][$lo]):'';
				$biztels				= ($newtype == 'address')?explode(",",$params['suboptionMakebiztel'][$lo]):'';

				//날짜, 수동기간, 자동기간추가
				$codedates			= ($newtype == 'date')?explode(",",$params['suboptionMakecodedate'][$lo]):'';

				$sdayinputs			= ($newtype == 'dayinput')?($params['suboptionMakesdayinput'][$lo]):'';
				$fdayinputs			= ($newtype == 'dayinput')?($params['suboptionMakefdayinput'][$lo]):'';
				$dayauto_types	= ($newtype == 'dayauto')?($params['suboptionMakedayauto_type'][$lo]):'';
				$sdayautos			= ($newtype == 'dayauto')?($params['suboptionMakesdayauto'][$lo]):'';
				$fdayautos			= ($newtype == 'dayauto')?($params['suboptionMakefdayauto'][$lo]):'';
				$dayauto_days	= ($newtype == 'dayauto')?($params['suboptionMakedayauto_day'][$lo]):'';

				$valCnt	= count($values);
				for ($o = 0; $o < $valCnt; $o++){
					$reserve	= 0;
					if	($prices[$o] > 0){
						$reserve	= round($prices[$o] * ($reserve_rate * 0.01));
					}

					$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
					$options['code_seq']		= $this->optionReplace($code_seq, 'int');
					$options['sub_required']	= 'n';
					$options['sub_sale']		= 'n';
					$options['suboption_type']	= $this->optionReplace($subType);
					$options['suboption_title']	= $this->optionReplace($subTitle);
					$options['suboption_code']	= $codes[$o];

					$options['newtype']				= $this->optionReplace($newtype);

					$options['color']					= ($colors[$o]);
					$options['zipcode']				= ($zipcodes[$o]);
					$options['address_type']				= ($address_types[$o]);
					$options['address']				= ($addresss[$o]);
					$options['address_street']				= ($address_streets[$o]);
					$options['addressdetail']		= ($addressdetails[$o]);
					$options['biztel']					= ($biztels[$o]);

					$options['codedate']			= ($codedates[$o]);

					$options['sdayinput']			= ($sdayinputs);
					$options['fdayinput']			= ($fdayinputs);
					$options['dayauto_type']		= ($dayauto_types);
					$options['sdayauto']			= ($sdayautos);
					$options['fdayauto']				= ($fdayautos);
					$options['dayauto_day']		= ($dayauto_days);

					$options['suboption']		= $this->optionReplace($values[$o]);
					$options['coupon_input']		=  ($socialcpuseopen == 'pass' || $prices[$o] == 0)?1:$this->optionReplace($prices[$o], 'int');
					$options['consumer_price']	= '0';
					$options['price']			= $this->optionReplace($prices[$o], 'int');
					$options['reserve_rate']	= $this->optionReplace($reserve_rate, 'int');
					$options['reserve_unit']	= 'percent';
					$options['reserve']			= $this->optionReplace($reserve, 'int');
					$options['commission_rate']	= 0;//$this->optionReplace($subCommissionRate[$s], 'int');
					$options['tmp_date']		= date('Ymd');
					$options['tmp_no']			= $params['tmp_seq'];

					$this->db->insert( 'fm_goods_suboption_tmp', $options );
					$suboption_seq	= $this->db->insert_id();

					if ($suboption_seq){
						$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
						$supply['suboption_seq']	= $suboption_seq;
						$supply['supply_price']		= '0';
						$supply['stock']			= '0';
						$supply['badstock']			= '0';
						$supply['reservation15']	= '0';
						$supply['reservation25']	= '0';
						$supply['ablestock15']		= '0';
						$supply['tmp_date']			= date('Ymd');
						$supply['tmp_no']			= $params['tmp_seq'];
						$this->db->insert( 'fm_goods_supply_tmp', $supply );
					}
				}
			}
		}
		//exit;
	}

	/**
	* 필수옵션 새창 임시옵션정보 최종생성 (opt 3단계)
	**/
	public function save_option_tmp($params){
		foreach($params as $k => $v){	$$k	= $v;	}
		$today = date("Y-m-d");

		if( $params['socialcpuseopen'] == 'price' || $params['socialcpuseopen'] == 'pass' ) {
			foreach($params as $k => $v) {
				$$k	= $v;
				if( $k == 'coupon_input' &&  in_array(0,$v) ) {
					$msg = "[쿠폰상품]의 쿠폰1장의 값어치를 정확히 입력해 주세요.";
					openDialogAlert($msg,450,140,'parent',$callback);
					exit;
				}

				if( $k == 'optnewtype') {
					$couponexpire =  false;
					if( !( in_array('date',$v) || in_array('dayinput',$v) || in_array('dayauto',$v) ) ){//coupon goods
						$msg = "[쿠폰상품]의 유효기간(날짜, 자동기간, 수동기간)옵션을 추가해 주세요.";
						openDialogAlert($msg,470,150,'parent',$callback);
						exit;
					}

					if( in_array('date', $v) ) {
						foreach($params['codedate'] as $key => $codedate){
							if( $codedate >= $today ) {
								$couponexpire = true;
								break;
							}else{
								$social_start_date	= $codedate;
								$social_end_date	= $codedate;
							}
						}
						if( $couponexpire === false ) {
							$msg = "[쿠폰상품]의 유효기간을 정확히 입력해 주세요.";
							if( strstr($social_start_date,'0000-00-00') || strstr($social_end_date,'0000-00-00') || !$social_start_date  || !$social_end_date){
								$msg .= "<br/>유효기간이 없습니다.";
							}else{
								$msg .= "<br/>유효기간이 ".$codedate." 입니다.";
							}
							openDialogAlert($msg,450,160,'parent',$callback);
							exit;
						}
					}elseif( in_array('dayinput', $v) ) {
						foreach($params['fdayinput'] as $key => $fdayinput){
							if( $fdayinput >= $today ) {
								$couponexpire = true;
								break;
							}else{
								$social_start_date = $params['sdayinput'][$key];
								$social_end_date = $fdayinput;
							}
						}
						if( $couponexpire === false ) {
							$msg = "[쿠폰상품]의 유효기간을 정확히 입력해 주세요.";
							if( strstr($social_start_date,'0000-00-00') || strstr($social_end_date,'0000-00-00')  || !$social_start_date  || !$social_end_date){
								$msg .= "<br/>유효기간이 없습니다.";
							}else{
								$msg .= "<br/>유효기간이 ".$social_start_date." ~ ".$social_end_date." 입니다.";
							}
							openDialogAlert($msg,450,160,'parent',$callback);
							exit;
						}
					}
				}
			}//endforeach
		}//endif

		// 기존 옵션 정보 삭제
		$query	= "delete from fm_goods_option_tmp where tmp_no = '".$tmp_seq."' ";
		$this->db->query($query);
		// 기존 재고 정보 삭제
		$query	= "delete from fm_goods_supply_tmp where option_seq > 0 and tmp_no = '".$tmp_seq."' ";
		$this->db->query($query);

		$reserves		= config_load('reserve');
		$default_rate	= $reserves['default_reserve_percent'];

		$titles		= implode(',', $optionTitle);
		$typs		= implode(',', $optionType);
		$newtypes		= implode(',', $optnewtype);

		$optCnt		= count($opt);
		$sOptCnt	= count($opt[0]);
		for ($s = 0; $s < $sOptCnt; $s++){
			$optionStr			= '';
			$sqlOptionFld		= '';
			$sqlOptionCodeFld	= '';
			$sqlOption			= '';
			$sqlOptionCode		= '';
			$default			= 'n';
			$optionArr			= array();

			$newtypeArr		= array();
			$tmppriceArr		= array();
			$colorArr				= array();
			$zipcodeArr			= array();
			$address_typeArr			= array();
			$addressArr			= array();
			$address_streetArr			= array();
			$addressdetailArr= array();
			$biztelArr				= array();
			$address_commissionArr= array();

			for ($o = 0; $o < $optCnt; $o++){
				${'option'.($o+1)}		= $opt[$o][$s];
				${'optioncode'.($o+1)}	= $optcode[$o][$s];

				$tmppriceArr[]				= $opttmpprice[$o][$s];

				if	(!is_null($opt[$o][$s]))
					$optionArr[]			= $opt[$o][$s];
			}
			if	($aleady_default != 'y' && $defaultOption == implode(',', $optionArr)){
				$aleady_default	= 'y';
				$default		= 'y';
			}

			if	($reserve_policy == 'shop'){
				$reserveRate[$s]	= $default_rate;
				$reserveUnit[$s]	= 'percent';
				$reserve[$s]		= round($price[$s] * ($default_rate * 0.01));
			}

			$codes			= 0;
			if	($types != 'direct') $code_seq = str_replace('goodsoption_', '', $typs);

			$options['goods_seq']			= $this->optionReplace($goods_seq, 'int');
			$options['code_seq']			= $code_seq;
			$options['default_option']	= ($default) ? $default : 'n';
			$options['option_type']		= $this->optionReplace($typs);
			$options['option_title']	= $this->optionReplace($titles);
			$options['option1']			= $this->optionReplace($option1);
			$options['option2']			= $this->optionReplace($option2);
			$options['option3']			= $this->optionReplace($option3);
			$options['option4']			= $this->optionReplace($option4);
			$options['option5']			= $this->optionReplace($option5);
			$options['optioncode1']		= $optioncode1;
			$options['optioncode2']		= $optioncode2;
			$options['optioncode3']		= $optioncode3;
			$options['optioncode4']		= $optioncode4;
			$options['optioncode5']		= $optioncode5;

			$options['tmpprice']				= implode(',', $tmppriceArr);

			$options['newtype']				= $this->optionReplace($newtypes);

			$colors									= $optcolor[$s];//implode(',', $optcolor);
			$zipcodes								= $optzipcode[$s];//implode(',', $optzipcode);
			$address_types							= $optaddress_type[$s];//implode(',', $optaddress);
			$addresss							= $optaddress[$s];//implode(',', $optaddress);
			$address_streets							= $optaddress_street[$s];//implode(',', $optaddress);
			$addressdetails					= $optaddressdetail[$s];//implode(',', $optaddressdetail);
			$biztels								= $optbiztel[$s];//implode(',', $optbiztel);
			$address_commissions		= $optaddress_commission[$s];

			$codedates							= $codedate[$s];//implode(',', $codedate);
			$sdayinputs							= $sdayinput[$s];//implode(',', $sdayinput);
			$fdayinputs							= $fdayinput[$s];//implode(',', $fdayinput);
			$dayauto_types					= $dayauto_type[$s];//implode(',', $dayauto_type);
			$sdayautos							= $sdayauto[$s];//implode(',', $sdayauto);
			$fdayautos							= $fdayauto[$s];//implode(',', $fdayauto);
			$dayauto_days					= $dayauto_day[$s];//implode(',', $dayauto_day);

			$options['color']								= ($colors);
			$options['zipcode']							= ($zipcodes);
			$options['address_type']					= ($address_types);
			$options['address']							= ($addresss);
			$options['address_street']				= ($address_streets);
			$options['addressdetail']					= ($addressdetails);
			$options['biztel']								= ($biztels);
			$options['address_commission']		= ($address_commissions);

			$options['codedate']		= ($codedates);
			$options['sdayinput']		= ($sdayinputs);
			$options['fdayinput']		= ($fdayinputs);
			$options['dayauto_type']	= ($dayauto_types);
			$options['sdayauto']		= ($sdayautos);
			$options['fdayauto']			= ($fdayautos);
			$options['dayauto_day']	= ($dayauto_days);
			$options['coupon_input']	= $this->optionReplace($coupon_input[$s], 'int');
			$options['consumer_price']	= $this->optionReplace($consumerPrice[$s], 'int');
			$options['price']				= $this->optionReplace($price[$s], 'int');
			$options['reserve_rate']	= $this->optionReplace($reserveRate[$s], 'int');
			$options['reserve_unit']	= ($reserveUnit[$s]) ? $reserveUnit[$s] : 'percent';
			$options['reserve']			= $this->optionReplace($reserve[$s], 'int');
			$options['infomation']		= addslashes($infomation[$s]);
			$options['commission_rate']	= 0;//$this->optionReplace($commissionRate[$s], 'int');
			$options['tmp_policy']		= $reserve_policy;
			$options['tmp_date']		= date('Ymd');
			$options['tmp_no']			= $tmp_seq;


			$this->db->insert( 'fm_goods_option_tmp', $options );
			$option_seq	= $this->db->insert_id();

			if	($option_seq){
				$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$supply['option_seq']		= $option_seq;
				$supply['supply_price']		= $this->optionReplace($supplyPrice[$s], 'int');
				$supply['stock']				= $this->optionReplace($stock[$s], 'int');
				$supply['badstock']			= $this->optionReplace($badstock[$s], 'int');
				$supply['reservation15']	= $this->optionReplace($reservation15[$s], 'int');
				$supply['reservation25']	= $this->optionReplace($reservation25[$s], 'int');
				$supply['ablestock15']		= $this->optionReplace($unUsableStock[$s], 'int');
				$supply['tmp_date']			= date('Ymd');
				$supply['tmp_no']			= $tmp_seq;
				$this->db->insert( 'fm_goods_supply_tmp', $supply );
			}
		}//exit;
	}


	/**
	* 추가구성옵션 새창 임시옵션정보 최종생성 (opt 3단계)
	**/
	public function save_suboption_tmp($params){
		foreach($params as $k => $v){	$$k	= $v;	}

		// 기존 옵션 정보 삭제
		$query	= "delete from fm_goods_suboption_tmp where tmp_no = '".$tmp_seq."' ";
		$this->db->query($query);
		// 기존 재고 정보 삭제
		$query	= "delete from fm_goods_supply_tmp where suboption_seq > 0 and tmp_no = '".$tmp_seq."' ";
		$this->db->query($query);

		$reserves		= config_load('reserve');
		$default_rate	= $reserves['default_reserve_percent'];

		$idx		= 0;
		$s			= 0;
		foreach($subopt as $k => $sopt){
			$titles			= $suboptTitle[$s];
			$types			= $suboptType[$s];
			$subopt_s		= $subopt[$k];
			$subRequiredVal	= (in_array($s, $subRequired))	? 'y' : 'n';
			$subSaleVal		= (in_array($s, $subSale))		? 'y' : 'n';
			$codes			= 0;
			if	($types != 'direct')
				$codes			= str_replace('goodssuboption_', '', $types);

			$subopt_scnt	= count($subopt_s);
			for ($z = 0; $z < $subopt_scnt; $z++){
				$suboptCodeVal	= $suboptCode[$k][$z];
				$reserve		= 0;
				if	($tmp_policy != 'goods'){
					if	($subPrice[$idx] > 0){
						$reserve	= round($subPrice[$idx] * ($default_rate * 0.01));
					}

					$reserve_rate	= $this->optionReplace($default_rate, 'int');
					$reserve_unit	= 'percent';
					$reserve		= $this->optionReplace($reserve, 'int');
				}else{
					$reserve_rate	= $this->optionReplace($subReserveRate[$idx], 'int');
					$reserve_unit	= ($subReserveUnit[$idx]) ? $subReserveUnit[$idx] : 'percent';
					$reserve		= $this->optionReplace($subReserve[$idx], 'int');
				}

				$newtypes					= $suboptionnewtype[$idx];
				$suboptcolors				= ($newtypes == 'color' ) ? ($suboptcolor[$idx]):'';
				$suboptzipcodes			= ($newtypes == 'address' ) ? ($suboptzipcode[$idx]):'';
				$suboptaddress_types			= ($newtypes == 'address' ) ? ($suboptaddress_type[$idx]):'';
				$suboptaddresss			= ($newtypes == 'address' ) ? ($suboptaddress[$idx]):'';
				$suboptaddress_streets			= ($newtypes == 'address' ) ? ($suboptaddress_street[$idx]):'';
				$suboptaddressdetails= ($newtypes == 'address' ) ? ($suboptaddressdetail[$idx]):'';
				$suboptbiztels= ($newtypes == 'address' ) ? ($suboptbiztel[$idx]):'';

				$codedates			= ($newtypes == 'date' ) ? ($codedate[$idx]):'';
				$sdayinputs			= ($newtypes == 'dayinput' ) ? ($sdayinput[$idx]):'';
				$fdayinputs			= ($newtypes == 'dayinput' ) ? ($fdayinput[$idx]):'';
				$dayauto_types	= ($newtypes == 'dayauto' ) ? ($dayauto_type[$idx]):'';
				$sdayautos			= ($newtypes == 'dayauto' ) ? ($sdayauto[$idx]):'';
				$fdayautos			= ($newtypes == 'dayauto' ) ? ($fdayauto[$idx]):'';
				$dayauto_days	= ($newtypes == 'dayauto' ) ? ($dayauto_day[$idx]):'';

				$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$options['code_seq']		= $codes;
				$options['sub_required']	= ($subRequiredVal) ? $subRequiredVal : 'n';
				$options['sub_sale']		= ($subSaleVal) ? $subSaleVal : 'n';
				$options['suboption_type']	= $this->optionReplace($types);
				$options['suboption_title']	= $this->optionReplace($titles);
				$options['suboption_code']	= $this->optionReplace($suboptCodeVal);

				$options['newtype']				= ($newtypes);

				$options['color']					= ($suboptcolors);
				$options['zipcode']				= ($suboptzipcodes);
				$options['address_type']				= ($suboptaddress_types);
				$options['address']				= ($suboptaddresss);
				$options['address_street']				= ($suboptaddress_streets);
				$options['addressdetail']		= ($suboptaddressdetails);
				$options['biztel']					= ($suboptbiztels);

				$options['codedate']			= ($codedates);
				$options['sdayinput']			= ($sdayinputs);
				$options['fdayinput']			= ($fdayinputs);
				$options['dayauto_type']		= ($dayauto_types);
				$options['sdayauto']			= ($sdayautos);
				$options['fdayauto']				= ($fdayautos);
				$options['dayauto_day']		= ($dayauto_days);

				$options['suboption']		= $this->optionReplace($subopt_s[$z]);
				$options['coupon_input']			= $this->optionReplace($subcoupon_input[$idx], 'int');
				$options['consumer_price']	= $this->optionReplace($subConsumerPrice[$idx], 'int');
				$options['price']			= $this->optionReplace($subPrice[$idx], 'int');
				$options['reserve_rate']	= $reserve_rate;
				$options['reserve_unit']	= $reserve_unit;
				$options['reserve']			= $reserve;
				$options['commission_rate']	= 0;//$this->optionReplace($subCommissionRate[$idx], 'int');
				$options['tmp_date']		= date('Ymd');
				$options['tmp_no']			= $tmp_seq;


				$this->db->insert( 'fm_goods_suboption_tmp', $options );
				$suboption_seq	= $this->db->insert_id();

				if	($suboption_seq){
					$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
					$supply['suboption_seq']	= $suboption_seq;
					$supply['supply_price']		= $this->optionReplace($subSupplyPrice[$idx], 'int');
					$supply['stock']			= $this->optionReplace($subStock[$idx], 'int');
					$supply['badstock']			= '0';
					$supply['reservation15']	= '0';
					$supply['reservation25']	= '0';
					$supply['ablestock15']		= '0';
					$supply['tmp_date']			= date('Ymd');
					$supply['tmp_no']			= $_POST['tmp_seq'];
					$this->db->insert( 'fm_goods_supply_tmp', $supply );
				}

				$idx++;
			}

			$s++;
		}
		//exit;
	}

	public function moveTmpToOption($goods_seq, $tmp_seq){

		$this->delete_option_info($goods_seq);

		$query		= "select * from fm_goods_option_tmp where tmp_no = ? order by option_seq asc";
		$rs			= $this->db->query($query,array($tmp_seq));
		$result		= $rs->result_array();
		foreach($result as $tmp_list){
			$tmp_option_seq	= $tmp_list['option_seq'];
			$supplySql	= "select * from fm_goods_supply_tmp where tmp_no = ? and option_seq = ? ";
			$supplyRs	= $this->db->query($supplySql,array($tmp_seq, $tmp_option_seq));
			$tmp_supply	= $supplyRs->result_array();
			$tmpSupply	= $tmp_supply[0];

			if	($default == 'y' && $tmp_list['default_option'] == 'y'){
				$tmp_list['default_option']	= 'n';
			}
			if	($tmp_list['default_option'] == 'y')	$default	= 'y';

			$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
			$options['code_seq']		= $tmp_list['code_seq'];
			$options['default_option']	= $tmp_list['default_option'];
			$options['option_type']		= $tmp_list['option_type'];
			$options['option_title']	= $this->optionReplace($tmp_list['option_title']);
			$options['option1']			= $this->optionReplace($tmp_list['option1']);
			$options['option2']			= $this->optionReplace($tmp_list['option2']);
			$options['option3']			= $this->optionReplace($tmp_list['option3']);
			$options['option4']			= $this->optionReplace($tmp_list['option4']);
			$options['option5']			= $this->optionReplace($tmp_list['option5']);
			$options['optioncode1']		= $tmp_list['optioncode1'];
			$options['optioncode2']		= $tmp_list['optioncode2'];
			$options['optioncode3']		= $tmp_list['optioncode3'];
			$options['optioncode4']		= $tmp_list['optioncode4'];
			$options['optioncode5']		= $tmp_list['optioncode5'];

			$options['tmpprice']				= $tmp_list['tmpprice'];
			$options['color']								= trim($tmp_list['color']);
			$options['zipcode']				= $tmp_list['zipcode'];
			$options['address_type']					= $tmp_list['address_type'];
			$options['address']							= $tmp_list['address'];
			$options['address_street']				= $tmp_list['address_street'];
			$options['addressdetail']					= $tmp_list['addressdetail'];
			$options['biztel']								= $tmp_list['biztel'];
			$options['address_commission']		= $tmp_list['address_commission'];

			$options['newtype']				= $tmp_list['newtype'];

			$options['codedate']			= $tmp_list['codedate'];
			$options['sdayinput']			= $tmp_list['sdayinput'];
			$options['fdayinput']			= $tmp_list['fdayinput'];
			$options['dayauto_type']		= $tmp_list['dayauto_type'];
			$options['sdayauto']			= $tmp_list['sdayauto'];
			$options['fdayauto']				= $tmp_list['fdayauto'];
			$options['dayauto_day']		= $tmp_list['dayauto_day'];

			$options['coupon_input']		= $this->optionReplace($tmp_list['coupon_input'], 'int');
			$options['consumer_price']	= $this->optionReplace($tmp_list['consumer_price'], 'int');
			$options['price']			= $this->optionReplace($tmp_list['price'], 'int');
			$options['reserve_rate']	= $this->optionReplace($tmp_list['reserve_rate'], 'int');
			$options['reserve_unit']	= $tmp_list['reserve_unit'];
			$options['reserve']			= $this->optionReplace($tmp_list['reserve'], 'int');
			$options['infomation']		= $tmp_list['infomation'];
			$options['commission_rate']	= $this->optionReplace($tmp_list['commission_rate'], 'int');
			$options['fix_option_seq']	= $tmp_list['fix_option_seq'];
			$this->db->insert( 'fm_goods_option', $options );
			$option_seq	= $this->db->insert_id();
			if	(!$tmp_list['fix_option_seq']){
				$this->db->where(array('option_seq'=>$option_seq));
				$this->db->update('fm_goods_option', array('fix_option_seq'=>$option_seq));
			}
			$chg_seq_result[]	= array($tmp_option_seq => $option_seq);

			if	($option_seq){
				$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$supply['option_seq']		= $option_seq;
				$supply['supply_price']		= $this->optionReplace($tmpSupply['supply_price'], 'int');
				$supply['stock']			= $this->optionReplace($tmpSupply['stock'], 'int');
				$supply['badstock']			= $this->optionReplace($tmpSupply['badstock'], 'int');
				$supply['reservation15']	= $this->optionReplace($tmpSupply['reservation15'], 'int');
				$supply['reservation25']	= $this->optionReplace($tmpSupply['reservation25'], 'int');
				$supply['ablestock15']		= $this->optionReplace($tmpSupply['ablestock15'], 'int');
				$this->db->insert( 'fm_goods_supply', $supply );
			}
		}

		$query		= "delete from fm_goods_option_tmp where tmp_no = ? ";
		$this->db->query($query,array($tmp_seq));
		$query		= "delete from fm_goods_supply_tmp where tmp_no = ? and option_seq > 0";
		$this->db->query($query,array($tmp_seq));

		return $chg_seq_result;
	}

	public function moveTmpToSubOption($goods_seq, $tmp_seq){

		$this->delete_sub_option_info($goods_seq);

		$query		= "select * from fm_goods_suboption_tmp where tmp_no = ? ";
		$rs			= $this->db->query($query,array($tmp_seq));
		foreach($rs->result_array() as $tmp_list){
			$tmp_suboption_seq	= $tmp_list['suboption_seq'];

			$supplySql	= "select * from fm_goods_supply_tmp "
						. "where tmp_no = ? and suboption_seq = ? ";
			$supplyRs	= $this->db->query($supplySql,array($tmp_seq, $tmp_suboption_seq));
			$tmp_supply	= $supplyRs->result_array();
			$tmpSupply	= $tmp_supply[0];

			$options['goods_seq']		= $this->optionReplace($goods_seq, 'int');
			$options['code_seq']		= $tmp_list['code_seq'];
			$options['sub_required']	= $tmp_list['sub_required'];
			$options['sub_sale']		= $tmp_list['sub_sale'];
			$options['suboption_type']	= $tmp_list['suboption_type'];
			$options['suboption_title']	= $this->optionReplace($tmp_list['suboption_title']);
			$options['suboption_code']	= $tmp_list['suboption_code'];

			$options['color']							= trim($tmp_list['color']);
			$options['zipcode']				= $tmp_list['zipcode'];
			$options['address_type']				= $tmp_list['address_type'];
			$options['address']				= $tmp_list['address'];
			$options['address_street']				= $tmp_list['address_street'];
			$options['addressdetail']		= $tmp_list['addressdetail'];
			$options['biztel']					= $tmp_list['biztel'];

			$options['newtype']				= $tmp_list['newtype'];

			$options['codedate']			= $tmp_list['codedate'];
			$options['sdayinput']			= $tmp_list['sdayinput'];
			$options['fdayinput']			= $tmp_list['fdayinput'];
			$options['dayauto_type']		= $tmp_list['dayauto_type'];
			$options['sdayauto']			= $tmp_list['sdayauto'];
			$options['fdayauto']				= $tmp_list['fdayauto'];
			$options['dayauto_day']		= $tmp_list['dayauto_day'];

			$options['suboption']		= $this->optionReplace($tmp_list['suboption']);
			$options['coupon_input']		= $this->optionReplace($tmp_list['coupon_input'], 'int');
			$options['consumer_price']	= $this->optionReplace($tmp_list['consumer_price'], 'int');
			$options['price']			= $this->optionReplace($tmp_list['price'], 'int');
			$options['reserve_rate']	= $this->optionReplace($tmp_list['reserve_rate'], 'int');
			$options['reserve_unit']	= $tmp_list['reserve_unit'];
			$options['reserve']			= $this->optionReplace($tmp_list['reserve'], 'int');
			$options['commission_rate']	= 0;//$this->optionReplace($tmp_list['commission_rate'], 'int');



			$this->db->insert( 'fm_goods_suboption', $options );
			$suboption_seq	= $this->db->insert_id();

			if($suboption_seq){
				$supply['goods_seq']		= $this->optionReplace($goods_seq, 'int');
				$supply['suboption_seq']	= $suboption_seq;
				$supply['supply_price']		= $this->optionReplace($tmpSupply['supply_price'], 'int');
				$supply['stock']			= $this->optionReplace($tmpSupply['stock'], 'int');
				$supply['badstock']			= $this->optionReplace($tmpSupply['badstock'], 'int');
				$supply['reservation15']	= $this->optionReplace($tmpSupply['reservation15'], 'int');
				$supply['reservation25']	= $this->optionReplace($tmpSupply['reservation25'], 'int');
				$supply['ablestock15']		= $this->optionReplace($tmpSupply['ablestock15'], 'int');

				$this->db->insert( 'fm_goods_supply', $supply );
			}
		}

		$query		= "delete from fm_goods_suboption_tmp where tmp_no = ? ";
		$this->db->query($query,array($tmp_seq));
		$query		= "delete from fm_goods_supply_tmp where tmp_no = ? and suboption_seq > 0";
		$this->db->query($query,array($tmp_seq));
	}

	public function delete_option_info($goods_seq){
		$query	= "delete from fm_goods_option where goods_seq = '".$goods_seq."' ";
		$this->db->query($query);
		$query	= "delete from fm_goods_supply "
				. "where goods_seq = '".$goods_seq."' and option_seq > 0";
		$this->db->query($query);
	}

	public function delete_sub_option_info($goods_seq){
		$query	= "delete from fm_goods_suboption where goods_seq = '".$goods_seq."' ";
		$this->db->query($query);
		$query	= "delete from fm_goods_supply "
				. "where goods_seq = '".$goods_seq."' and suboption_seq > 0";
		$this->db->query($query);
	}

	public function get_possible_pay_text($possible_pay){

		$possible_pay = str_replace("card", "신용카드", $possible_pay);
		$possible_pay = str_replace("escrow_account", "에스크로 계좌이체", $possible_pay);
		$possible_pay = str_replace("escrow_virtual", "에스크로 가상계좌", $possible_pay);
		$possible_pay = str_replace("account", "계좌이체", $possible_pay);
		$possible_pay = str_replace("virtual", "가상계좌", $possible_pay);
		$possible_pay = str_replace("cellphone", "핸드폰", $possible_pay);
		$possible_pay = str_replace("bank", "무통장 입금", $possible_pay);

		return $possible_pay;
	}

	public function optionReplace($val, $valType = 'str'){
		switch($valType){
			case 'int':
				$val	= preg_replace('/[^0-9]/', '', $val);
				if(is_null($val))	$val	= '0';
			break;
			case 'str':
				$val	= preg_replace('/[\"]/', '', $val);
				if(is_null($val))	$val	= '';
			break;
		}

		return $val;
	}

	//자주사용하는 상품 필수/추가구성/추가입력 옵션
	public function frequentlygoods($Type='opt',$goods_seq=null,$socialcp=null){
		$result = false;
		if($goods_seq){
			$query = "select goods_name,goods_seq from fm_goods where frequently".$Type."=1 and goods_seq!='".$goods_seq."'";
		}else{
			$query = "select goods_name,goods_seq from fm_goods where frequently".$Type."=1 ";
		}
		$query .= ($socialcp)? " and goods_kind ='coupon' ":" and goods_kind ='goods' ";
		$query = $this->db->query($query);
		$result = $query->result_array();
		return $result;
	}

	// 필수옵션에서 지역정보 데이터를 추출
	public function get_option_address($goods_seq){
		$sql		= "select newtype, option1, option2, option3, option4, option5
						from fm_goods_option where goods_seq = ? and newtype like '%address%' ";
		$query		= $this->db->query($sql, array($goods_seq));
		$result		= $query->result_array();
		$address	= array();
		if	($result){
			foreach($result as $k => $data){
				$address_fld	= 'option1';
				if	(preg_match('/\,/', $data['newtype'])){
					$typeArr		= explode(',', $data['newtype']);
					$tmp_no			= array_search('address', $typeArr) + 1;
					$address_fld	= 'option'.$tmp_no;
				}

				$address[]	= $data[$address_fld];
			}
		}

		return array_unique($address);
	}

	// 필수옵션에서 지역 수수료 데이터를 추출
	public function get_option_address_commission($goods_seq,$use_coupon_area){
		$sql		= "select newtype, option1, option2, option3, option4, option5, address_commission
						from fm_goods_option where goods_seq = ? and newtype like '%address%' ";
		$query		= $this->db->query($sql, array($goods_seq));
		$result		= $query->result_array();
		$address_commission = 0;
		if	($result){
			foreach($result as $k => $data){
				$address_fld	= 'option1';
				if	(preg_match('/\,/', $data['newtype'])){
					$typeArr		= explode(',', $data['newtype']);
					$tmp_no			= array_search('address', $typeArr) + 1;
					$address_fld	= 'option'.$tmp_no;
				}
				if( trim($data[$address_fld]) == trim($use_coupon_area) ) {
					$address_commission = $data['address_commission'];
					break;
				}
			}//end foreach
		}
		return $address_commission;
	}

	public function getCategoryGoodsColors($sc){

		$binds = array();

		$sql = "select group_concat(DISTINCT ifnull(o.color,'')) as colors from fm_goods g ";

		if($sc['category_code']){
			$sql .= " inner join fm_category_link c on (g.goods_seq = c.goods_seq and c.category_code=?) ";
			$binds[] = $sc['category_code'];
		}

		if($sc['brand_code']){
			$sql .= " inner join fm_brand_link b on (g.goods_seq = b.goods_seq and b.category_code=?) ";
			$binds[] = $sc['brand_code'];
		}

		if($sc['brands']){
			$sql .= " inner join fm_brand_link b on (g.goods_seq = b.goods_seq and b.category_code in ('".implode("','",$sc['brands'])."')) ";
		}

		$sql .= " inner join fm_goods_option o on g.goods_seq = o.goods_seq ";
		if(!empty($this->categoryData['list_goods_status'])) $sql .= " and g.goods_status in ('".str_replace('|',"','",$this->categoryData['list_goods_status'])."')";

		$query = $this->db->query($sql,$binds);
		$result = $query->row_array();

		$colors = array_values(array_notnull(array_unique(explode(',',$result['colors']))));

		return $colors;

	}

	// 쿠폰번호 중복 체크
	public function chkDuple_coupon_serial($coupon_serial){
		$sql	= "select * from fm_goods_coupon_serial where coupon_serial = '".$coupon_serial."' ";
		$query	= $this->db->query($sql);
		$result	= $query->row_array();

		if	($result['coupon_serial'])	return true;
		else							return false;
	}

	// 외부 쿠폰번호 추출
	public function get_outcoupon_list($goods_seq){
		$sql	= "select * from fm_goods_coupon_serial where goods_seq = ? ";
		$query	= $this->db->query($sql, array($goods_seq));
		$result	= $query->result_array();

		return $result;
	}

	// 외부 쿠폰번호 일괄등록
	public function coupon_serial_upload($filename){

		$this->load->library('pxl');
		set_time_limit(0);
		ini_set('memory_limit', '3500M');

		$cacheMethod		= PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings		= array( ' memoryCacheSize ' => '5120MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		$this->objPHPExcel	= new PHPExcel();

		// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
		$objReader		= IOFactory::createReaderForFile($filename);
		// 읽기전용으로 설정
		$objReader->setReadDataOnly(true);
		// 엑셀파일을 읽는다
		$objExcel		= $objReader->load($filename);
		// 첫번째 시트를 선택
		$objExcel->setActiveSheetIndex(0);
		$objWorksheet	= $objExcel->getActiveSheet();
		$maxRow			= $objWorksheet->getHighestRow();
		$maxCol			= $objWorksheet->getHighestColumn();

		for ($i = 1 ; $i <= $maxRow ; $i++) {
			$coupon_serial			= $objWorksheet->getCell('A'.$i)->getValue();
			$coupon_serial			= preg_replace('/[^a-zA-Z0-9\-\_]/', '', trim($coupon_serial));
			$result[$coupon_serial]	= 'y';
			if($this->chkDuple_coupon_serial($coupon_serial) || !$coupon_serial){
				$result[$coupon_serial]	= 'n';
			}
		}

		return $result;
	}

	// 출고 시 외부 쿠폰번호 추출
	public function get_out_coupon_serial_code($goods_seq){
		$goods	= $this->get_goods($goods_seq);
		if	($goods['coupon_serial_type'] == 'n'){
			$sql	= "select coupon_serial from fm_goods_coupon_serial where goods_seq = ? "
					. "and (export_code = '' or export_code is null) "
					. "order by coupon_serial limit 1 ";
			$query	= $this->db->query($sql, array($goods_seq));
			$result	= $query->row_array();
			if	($result['coupon_serial']){
				return $result['coupon_serial'];
			}else{
				return false;
			}
		}else{
			return 'a';
		}

		return false;
	}

	// 출고 시 외부 쿠폰번호 사용처리
	public function use_out_coupon_serial_code($coupon_serial, $goods_seq, $export_code){

		$where_arr['coupon_serial']	= $coupon_serial;
		$where_arr['goods_seq']		= $goods_seq;
		$update_arr['export_code']	= $export_code;
		$update_arr['export_date']	= date('Y-m-d H:i:s');
		$this->db->where($where_arr);
		$this->db->update('fm_goods_coupon_serial', $update_arr);

		// 외부 쿠폰일 경우 쿠폰 코드가 없으면 품절 처리한다.
		$sql	= "select coupon_serial from fm_goods_coupon_serial where goods_seq = ? "
				. "and (export_code = '' or export_code is null) "
				. "order by coupon_serial limit 1 ";
		$query	= $this->db->query($sql, array($goods_seq));
		$result	= $query->row_array();
		if	(!$result['coupon_serial']){
			$where_arr2['goods_seq']		= $goods_seq;
			$update_arr2['goods_status']	= 'runout';
			$this->db->where($where_arr2);
			$this->db->update('fm_goods', $update_arr2);
		}
	}


	/*
	$arr = array(
		'is_mobile_agent'=>$is_mobile_agent,
		'mode'=>$mode, // list , view, cart, settle, view_max
		'goods_seq'=>$goods_seq,
		'price'=>$price,
		'consumer_price'=>$consumer_price,
		'ea'=>$ea,
		'category'=>$category,
		'brand'=>$brand,
		'data_goods'=>$data_goods,
		'group_seq'=>$group_seq,
		'data_event'=>$data_event
	);
	*/
	public function calculate_goods_sale($arr_sale_param){

		extract($arr_sale_param);

		if(!$this->membermodel) $this->load->model('membermodel');

		if(!$this->configsalemodel) $this->load->model('configsalemodel');
		if(!$this->Goodsfblike) $this->load->model('Goodsfblike');

		$this->load->model('Goodsfblike');
		if($this->userInfo['member_seq']){
			$qry_arr = array("select"=>"*","whereis"=>"and member_seq='".$this->userInfo['member_seq']."' and goods_seq='$goods_seq'");
			$data_fblike = $this->Goodsfblike->get_data($qry_arr);
		}else{
			// $data_fb_id 페이스북 아이디
			$qry_arr = array("select"=>"*","whereis"=>"and sns_id='".$data_fb_id."' and goods_seq='$goods_seq'");
			$data_fblike = $this->Goodsfblike->get_data($qry_arr);
		}

		// 이벤트
		if(!$data_event){
			$data_event = $this->get_event_price($price, $goods_seq, $category, $consumer_price, $data_goods);
		}
		$event_sale_unit = $data_event['event_sale_unit'];

		// 복수구매 할인 계산
		$data_multi = $this->get_multi_sale_price($ea,$price,$data_goods);
		$multi_sale_unit = $price - $data_multi;

		// 상품단가
		$discount_price_unit = $price - (int) $event_sale_unit - (int) $multi_sale_unit;
		$discount_price = $discount_price_unit * $ea;

		// 회원등급
		$in_category = @implode("','",$category);

		$member_sale_cnt = (int) $this->membermodel->get_group_except_category($group_seq,$data_goods['sale_seq'],$in_category,'sale');
		$member_sale_cnt += (int) $this->membermodel->get_group_except_goods_seq($group_seq,$data_goods['sale_seq'],$goods_seq,'sale');

		if( $member_sale_cnt == 0 ){
			$data_member_benift = $this->membermodel->get_group_benifit($group_seq,$data_goods['sale_seq']);
			if($data_member_benift['sale_price_type'] == 'PER'){
				if($data_member_benift['sale_use'] == "N" || $data_member_benift['sale_limit_price'] <= $discount_price ){
					$member_sale_unit = (int) ($data_member_benift['sale_price'] * $discount_price / 100);
					$member_sale_unit = (int) get_price_point($member_sale_unit,$this->config_system);
					$data_member_benift['sale_rate'] = $data_member_benift['sale_price'];
				}
			}else{
				if($data_member_benift['sale_use'] == "N" || $data_member_benift['sale_limit_price'] <= $discount_price ){
					$member_sale_unit = $data_member_benift['sale_price'];
					$data_member_benift['sale'] = $data_member_benift['sale_price'];
				}
			}
		}

		// 유입경로할인
		if($this->session->userdata('shopReferer')){
			$this->load->model('referermodel');
			$referer_sale = $this->referermodel->sales_referersale($this->session->userdata('shopReferer'), $goods_seq, $discount_price, 1);
			$referer_sale_unit = $referer_sale['sales_price'];
		}

		// 모바일 할인
		if($this->_is_mobile_agent) {//$this->mobileMode  ||

			$sc['type'] = 'mobile';
			$systemmobiles = $this->configsalemodel->lists($sc);

			foreach($systemmobiles['result'] as $k => $systemmobiles_price) {
				if($systemmobiles_price['price1']<= $discount_price && $systemmobiles_price['price2'] >= $discount_price){
					$mobile_sale_unit = (int) ($systemmobiles_price['sale_price'] * $discount_price / 100);
					$mobile_sale_unit = (int) get_price_point($mobile_sale_unit,$this->config_system);
					break;
				}
			}
		}

		//like 할인시
		if($data_fblike['like_seq']){

			$sc['type'] = 'fblike';
			$systemfblike = $this->configsalemodel->lists($sc);

			foreach($systemfblike['result'] as $k => $systemfblike_price) {
				if($systemfblike_price['price1']<= $discount_price && $systemfblike_price['price2'] >= $discount_price){
					$fblike_sale_unit = (int) ($systemfblike_price['sale_price'] * $discount_price / 100);
					$fblike_sale_unit = (int) get_price_point($goods['fblike_sale_unit'],$this->config_system);
					break;
				}
			}
		}

		$result['event_sale_unit'] = (int) $event_sale_unit;
		$result['multi_sale_unit'] = (int) $multi_sale_unit;
		$result['discount_price'] = $discount_price;
		$result['member_sale_unit'] = (int) $member_sale_unit;
		$result['referer_sale_unit'] = (int) $referer_sale_unit;
		$result['mobile_sale_unit'] = (int) $mobile_sale_unit;
		$result['fblike_sale_unit'] = (int) $fblike_sale_unit;
		$result['data_event'] = $data_event;
		$result['data_member_benift'] = $data_member_benift;

		return $result;
	}


	// 상품 공통 관련상품디스플레이 설정키값 가져오기
	public function get_goods_relation_display_seq(){
		$query  = $this->db->query("select * from fm_design_display where kind='relation'");
		$display = $query->row_array();
		if(!$display){
			$this->db->insert("fm_design_display",array(
				'admin_comment'	=> '관련상품',
				'kind'			=> 'relation',
				'count_w'		=> 4,
				'count_h'		=> 1,
				'style'			=> 'lattice_a',
				'image_size'	=> 'list1',
				'text_align'	=> 'left',
				'info_settings' => '[{"kind":"goods_name", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}"},{"kind":"summary", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}"},{"kind":"icon"},{"kind":"consumer_price", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}", "postfix":"won"},{"kind":"price", "font_decoration":"{\\"color\\":\\"#000000\\", \\"bold\\":\\"normal\\", \\"underline\\":\\"none\\"}", "postfix":"원"},{"kind":"fblike"},{"kind":"status_icon"}]',
			));
			$query  = $this->db->query("select * from fm_design_display where kind='relation'");
			$display = $query->row_array();
		}
		return $display['display_seq'];
	}

	// 임시 필수 옵션 단순 저장
	public function save_tmp_option($whrParam, $upParam){
		$this->db->where($whrParam);
		$this->db->update('fm_goods_option_tmp', $upParam);

		// 적립금 설정 변경 시 적립금 자동계산 추가
		if	(isset($upParam['reserve_rate']) && !empty($upParam['reserve_unit'])){
			foreach($whrParam as $fld => $val){
				$addWhere[]	= $fld . " = '".$val."' ";
			}
			$reserve_rate	= $upParam['reserve_rate'];
			$reserve_unit	= $upParam['reserve_unit'];
			if	($reserve_unit == 'percent')
				$sql	= "update fm_goods_option_tmp set reserve = (FLOOR(price * ".$reserve_rate."/100)) where ".implode(' and ', $addWhere);
			else
				$sql	= "update fm_goods_option_tmp set reserve = ".$reserve_rate." where ".implode(' and ', $addWhere);
			$this->db->query($sql);
		}
	}

	// 임시 재고 정보 단순 저장
	public function save_tmp_supply($whrParam, $upParam){
		$this->db->where($whrParam);
		$this->db->update('fm_goods_supply_tmp', $upParam);
	}

	// 적립금 정책 저장
	public function save_goods_policy($goods_seq, $reserve_policy){
		$this->db->where(array('goods_seq' => $goods_seq));
		$this->db->update('fm_goods', array('reserve_policy' => $reserve_policy));
	}

	// 동일 옵션에 동일한 값으로 적용
	public function save_same_tmp_option($tmp_no, $option_seq, $option_no, $upParam){

		$sql	= "select * from fm_goods_option_tmp where tmp_no = ? "
				. "and option_seq = ? ";
		$query	= $this->db->query($sql, array($tmp_no, $option_seq));
		$opt	= $query->row_array();
		if	($opt['option_seq']){
			foreach($upParam as $fld => $val){
				$u++;
				if	($u > 1)	$addUpdate	.= ", ";
				$addUpdate	.= $fld . "='".$val."' ";
			}
			$sql	= "update fm_goods_option_tmp set " . $addUpdate
					. "where tmp_no = ? and option".$option_no."=? ";
			$this->db->query($sql, array($tmp_no, $opt['option'.$option_no]));
		}
	}

	// option 복사/삭제
	public function save_option_one_row($type, $tmpSeq, $seq){
		if	($type && $seq){
			if		($type == 'add'){
				// option insert
				$sql	= "select * from fm_goods_option_tmp where tmp_no = ? and default_option = 'y' order by option_seq limit 1";
				$query	= $this->db->query($sql, array($tmpSeq));
				$option	= $query->row_array();
				$org_option_seq	= $option['option_seq'];
				if	($option){
					unset($option['option_seq']);
					unset($option['default_option']);
					$this->db->insert('fm_goods_option_tmp', $option);
					$option_seq	= $this->db->insert_id();
				}
				// supply insert
				$sql	= "select * from fm_goods_supply_tmp where tmp_no = ? and option_seq = ? ";
				$query	= $this->db->query($sql, array($tmpSeq, $org_option_seq));
				$supply	= $query->row_array();
				if	($supply){
					unset($supply['supply_seq']);
					$supply['option_seq']		= $option_seq;
					$supply['reservation15']	= 0;
					$supply['reservation25']	= 0;
					$supply['ablestock15']		= 0;
					$this->db->insert('fm_goods_supply_tmp', $supply);
				}
			}elseif	($type == 'del'){
				if	($tmpSeq && $seq){
					$sql	= "select * from fm_goods_option_tmp where tmp_no = ? and option_seq = ? ";
					$query	= $this->db->query($sql, array($tmpSeq, $seq));
					$option	= $query->row_array();
					if	($option['default_option'] == 'y'){
						$sql		= "select * from fm_goods_option_tmp where tmp_no = ? order by option_seq limit 1";
						$query		= $this->db->query($sql, array($tmpSeq));
						$tmpoption	= $query->row_array();
						$upParam['default_option']	= 'y';
						$this->db->where(array('tmp_no'=>$tmpSeq,'option_seq'=>$tmpoption['option_seq']));
						$this->db->update('fm_goods_option_tmp', $upParam);
					}
					$this->db->where(array('tmp_no'=>$tmpSeq,'option_seq'=>$seq));
					$this->db->delete('fm_goods_option_tmp');
					$this->db->where(array('tmp_no'=>$tmpSeq,'option_seq'=>$seq));
					$this->db->delete('fm_goods_supply_tmp');
					$option_seq	= $seq;
				}
			}
		}

		return $option_seq;
	}

	public function get_option_info_by_optionval($param)
	{
		$query = "select * from fm_goods_option where goods_seq=? 
					and ifnull(option1,'')=? and ifnull(option2,'')=? and ifnull(option3,'')=?
					and ifnull(option4,'')=? and ifnull(option5,'')=?";
		$bind[] = $param['goods_seq'];
		$bind[] = $param['option1'];
		$bind[] = $param['option2'];
		$bind[] = $param['option3'];
		$bind[] = $param['option4'];
		$bind[] = $param['option5'];
		$query = $this->db->query($query,$bind);
		return $query->row_array();
	}

	public function get_suboption_info_by_suboptionval($param)
	{
		$query = "select * from fm_goods_suboption where goods_seq=? 
					and ifnull(suboption_title,'')=? and ifnull(suboption,'')=?";
		$bind[] = $param['goods_seq'];
		$bind[] = $param['suboption_title'];
		$bind[] = $param['suboption'];		
		$query = $this->db->query($query,$bind);
		return $query->row_array();
	}

	/* 우측 퀵메뉴 추천상품 가져오기 */
	public function get_recommend_goods_count(){
		$query = "SELECT count(*) as cnt FROM fm_design_recommend_item ORDER BY recommend_item_seq ASC";
		$query = $this->db->query($query);
		$cnt = 0;
		foreach ($query->result() as $row) {
			$cnt = $row->cnt;
		}
		return $cnt;
	}

	/* 우측 퀵메뉴 추천상품 가져오기 */
	public function get_recommend_goods_list($page,$limit,$admin=""){

		if ($admin){
			$limit = '';
		} else {
			if (!$page) $page = 1;
			$start = ($page-1)*$limit;
			$limit = "LIMIT {$start} , {$limit}";
		}

		$query = "SELECT goods_seq FROM fm_design_recommend_item ORDER BY recommend_item_seq ASC {$limit}";
		$query = $this->db->query($query);
		$data = array();
		foreach ($query->result() as $row) {
			$data[] = $row->goods_seq;
		}
		return $data;
	}

	/* 우측 퀵메뉴 추천상품목록 반환 */
	public function get_recommend_item($data){
		$display_item = array();
		$goods_seqs = join(',',$data);
		if (!$goods_seqs) return;

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

		$query = $this->db->query("
		select
			g.goods_seq,
			g.goods_name,
			g.sale_seq,
			(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=1 and image_type='thumbScroll' limit 1) as image,
			o.price,
			o.consumer_price 
		from
			fm_design_recommend_item r
			inner join fm_goods g on (g.goods_seq=r.goods_seq and g.goods_type = 'goods')
			left join fm_goods_option o on (o.goods_seq=g.goods_seq and o.default_option ='y')
		where r.goods_seq in ( ".$goods_seqs." ) order by r.recommend_item_seq asc");

		foreach ($query->result_array() as $data) {

			// 해당 상품의 전체 카테고리
			$category	= array();
			$tmp		= $this->get_goods_category($data['goods_seq']);
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

	/* 적립금 설정 별 구매 시 적립금액 제한 조건 B*/
	public function get_reserve_standard_pay($standard_price, $ea, $tot_real_price, $use_emoney) {
		$give_reserve = (int) (((($standard_price*$ea)/$tot_real_price)*$use_emoney)/$ea);
		return $give_reserve;
	}

	/* 적립금 설정 별 구매 시 적립금액 제한 조건 C */
	public function get_reserve_limit($tot_reserve_one, $ea, $appointed_reserve, $use_emoney) {
		$reserve_subtract = $appointed_reserve - $use_emoney;
		$give_reserve = (int) ((($tot_reserve_one / $appointed_reserve)*$use_emoney)/$ea);
		return $give_reserve;
	}




















############################ 리뷰용 임시 함수들 #####################################

	public function get_sale_price_tmp($goods_seq, $goods_price, $category, $sale_seq, $consumer_price =0, $goodsinfo = null){

		$this->load->model('membermodel');

		$sale_price = 0;
		$event_price = 0;

		//이벤트 할인
		$eventData = $this->get_event_price($goods_price, $goods_seq, $category, $consumer_price, $goodsinfo);
		if( $eventData['event_seq'] ) {
			$event_price = $eventData["event_sale_unit"];
			if( $eventData['target_sale'] == 1 && $consumer_price > 0 ){//정가기준 할인시
				$goods_price = ($consumer_price > $event_price)?$consumer_price - (int) $event_price:0;
			}else{
				$goods_price = ($goods_price > $event_price)?$goods_price - (int) $event_price:0;
			}
		}

		//mobile 할인
		if( $this->_is_mobile_agent ) {//$this->mobileMode  ||
			$this->load->model('configsalemodel');
			$sc['type'] = 'mobile';
			$systemmobiles = $this->configsalemodel->lists($sc);

			foreach($systemmobiles['result'] as $fblike => $systemmobiles_price) {
				if($systemmobiles_price['price1']<= $goods_price && $systemmobiles_price['price2'] >= $goods_price){
					$opt_mobile_goods_sale = $systemmobiles_price['sale_price'] * $goods_price / 100; // 모바일 할인
					break;
				}//endif
			}//end foreach
		}


		if(!$opt_mobile_goods_sale){
			$opt_mobile_goods_sale = 0;
		}

		//회원할인
		if($this->userInfo['member_seq']){
			$members = $this->membermodel->get_member_data($this->userInfo['member_seq']);
			$sale_price = $this->membermodel->get_member_group($members['group_seq'],$goods_seq,$category,$goods_price,$tot_price, $sale_seq);
		}else{
			$sale_price = $this->membermodel->get_member_group("0",$goods_seq,$category,$goods_price,$tot_price, $sale_seq);
		}


		$price = $goods_price-$opt_mobile_goods_sale-$sale_price;

		return $price;
	}

	public function goods_list_tmp($sc){
		$data = array();

		if(!$sc['page']) $sc['page'] = 1;
		if(!$sc['perpage']) $sc['perpage'] = 10;
		if(!$sc['image_size']) $sc['image_size'] = 'view';

		/* 카테고리 접근제한 조건 */
		$this->load->model('membermodel');
		$memberData = $this->membermodel->get_member_data($this->userInfo['member_seq']);
		if(!empty($this->userInfo['member_seq']) && $memberData['group_seq']) {
			$sc['member_group_seq']	= $memberData['group_seq'];
		}

		// 모바일 요약페이지에서는 큰이미지 사용
		if(!empty($sc['list_style']) && $sc['list_style']=='mobile_zoom')
		{
			$sc['image_size'] = 'large';
		}

		if($sc['category_code'])	$sc['category'] = $sc['category_code'];
		if($sc['brand_code'])		$sc['brand'] = $sc['brand_code'];
		if($sc['location_code'])	$sc['location'] = $sc['location_code'];

		// 상품 아이콘 서브쿼리
		$goods_icon_subquery = "
		select group_concat(c.codecd) from fm_goods_icon c where c.goods_seq=g.goods_seq and
		(
			(ifnull(start_date,'0000-00-00') = '0000-00-00' and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(curdate() between start_date and end_date)
			or
			(start_date <= curdate() and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(end_date >= curdate() and ifnull(start_date,'0000-00-00') = '0000-00-00')
		)
		";

		$sqlGroupbyClause = "";

		$sqlSelectClause = "
			select
			g.goods_seq,
			g.sale_seq,
			g.goods_status,
			g.goods_kind,
			g.socialcp_event,
			g.goods_name,
			g.goods_code,
			g.summary,
			g.string_price_use,
			g.string_price,
			g.string_price_link,
			g.string_price_link_url,
			g.member_string_price_use,
			g.member_string_price,
			g.member_string_price_link,
			g.member_string_price_link_url,
			g.allmember_string_price_use,
			g.allmember_string_price,
			g.allmember_string_price_link,
			g.allmember_string_price_link_url,
			g.file_key_w,
			g.file_key_i,
			g.videotmpcode,
			g.videousetotal,
			g.purchase_ea,
			g.shipping_policy,
			g.review_count,
			g.review_sum,
			g.reserve_policy,
			if(g.goods_shipping_policy='unlimit',unlimit_shipping_price,limit_shipping_price) as goods_shipping_price,
			(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=1 and image_type ='{$sc['image_size']}' limit 1) as image,
			(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=2 and image_type ='{$sc['image_size']}' limit 1) as image2,
			(select count(*) from fm_goods_image where goods_seq=g.goods_seq and image_type ='view') as image_cnt,
			o.consumer_price,
			o.price,
			o.reserve_rate,
			o.reserve_unit,
			o.reserve,
			({$goods_icon_subquery}) as icons,
			(select group_concat(ifnull(color,'')) from fm_goods_option where goods_seq=g.goods_seq) as colors
		";
		$sqlFromClause = " from
		fm_goods_option o,
		fm_goods g
		 ";
		$sqlWhereClause = "
			where g.goods_type = 'goods'
			and o.goods_seq=g.goods_seq and o.default_option ='y'
		";

		$sqlLimitClause = "";

		/* 상품 자동노출일때 */
		if(!empty($sc) && $sc['auto_use']=='y'){
			if($sc['auto_term_type']=='relative') {
				$auto_start_date = date('Y-m-d',strtotime("-{$sc['auto_term']} day"));
				$auto_end_date = date('Y-m-d');
			}else{
				$auto_start_date = $sc['auto_start_date'];
				$auto_end_date = $sc['auto_end_date'];
			}

			switch($sc['auto_order'])
			{
				case "deposit":
				case "best":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='deposit' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.purchase_ea desc";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "deposit_price":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='deposit_price' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.purchase_ea desc";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "popular":
				case "view":
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.page_view desc";
					$sqlFromClause .= "left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='view' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "review":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='review' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc, g.review_count desc";
					$sqlGroupbyClause = " group by g.goods_seq";
				break;
				case "cart":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='cart' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlGroupbyClause = " group by g.goods_seq";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc";
				break;
				case "wish":
					$sqlFromClause .= " left join fm_stats_goods on g.goods_seq=fm_stats_goods.goods_seq and fm_stats_goods.type='wish' and fm_stats_goods.stats_date between '{$auto_start_date}' and '{$auto_end_date}'";
					$sqlGroupbyClause = " group by g.goods_seq";
					$sqlOrderbyClause =" order by fm_stats_goods.cnt desc";
				break;
				case "newly":
				default:
					$sqlWhereClause .= " and g.regist_date between '{$auto_start_date} 00:00:00' and '{$auto_end_date} 23:59:59'";
					$sqlOrderbyClause =" order by g.regist_date desc, g.goods_seq desc";
				break;
				case "discount":
					$sqlWhereClause .= " and o.consumer_price>0 ";
					$sqlOrderbyClause =" order by o.price/o.consumer_price asc, o.price desc";
				break;
			}

			/* 20130408 : 자동노출&페이징정렬시 문제발생하여 임시로 추가  */
			switch($sc['sort'])
			{
				case "popular":
					if(!empty($sc['category'])){
						$sqlOrderbyClause =" order by l.sort asc, g.regist_date desc";
					}elseif(!empty($sc['brand'])){
						$sqlOrderbyClause =" order by bl.sort asc, g.regist_date desc";
					}else{
						$sqlOrderbyClause =" order by g.page_view desc";
					}
				break;
				case "newly":
					$sqlOrderbyClause =" order by g.regist_date desc, g.goods_seq desc";
				break;

				case "low_price":
					$sqlOrderbyClause =" order by o.price asc, g.goods_seq desc";
				break;
				case "high_price":
					$sqlOrderbyClause =" order by o.price desc, g.goods_seq desc";
				break;

				default:
					$sqlOrderbyClause =$sqlOrderbyClause;
				break;

			}

			//동영상
			if($sc['auto_file_key_w']) $sqlWhereClause .= " and ( g.file_key_w != '' ) ";//이미지영역 동영상여부
			if($sc['auto_file_key_w'] && $sc['auto_video_use_image'])  $sqlWhereClause .= " and ( g.file_key_w != '' )  and ( g.video_use = '{$sc['auto_video_use_image']}') ";//이미지영역 동영상 있으면서 노출여부 포함
			if($sc['auto_videototal'])  $sqlWhereClause .= " and ( g.videototal > 0 ) ";//설명영역 동영상여부

			if($sc['selectGoodsView']) {
				$sqlWhereClause .= " and g.goods_view='{$sc['selectGoodsView']}' ";
			}

			if(!empty($sc['selectEvent']) || !empty($sc['selectEventBenefits'])){
				$query = $this->db->query("select * from fm_event where event_seq=?",$sc['selectEvent']);
				$eventinfo = $query->row_array();

				if($eventinfo['goods_rule']=='all'){
					$sqlWhereClause .= " and g.goods_seq not in (select goods_seq from fm_event_choice where choice_type='except_goods' and event_seq = '{$sc['selectEvent']}' and event_benefits_seq = '{$sc['selectEventBenefits']}')";
					$sqlWhereClause .= " and g.goods_seq not in (select ec.goods_seq from fm_event_choice e inner join fm_category_link ec on e.category_code=ec.category_code where e.choice_type='except_category' and e.event_seq = '{$sc['selectEvent']}' and e.event_benefits_seq = '{$sc['selectEventBenefits']}' group by ec.goods_seq)";
				}

				if($eventinfo['goods_rule']=='goods_view'){
					$sqlFromClause .= " left join fm_event_choice e_goods on (g.goods_seq = e_goods.goods_seq and e_goods.choice_type='goods')";
					$sqlWhereClause .= " and e_goods.event_seq = '{$sc['selectEvent']}' ";
					if($sc['selectEventBenefits']){
						$sqlWhereClause .= " and e_goods.event_benefits_seq = '{$sc['selectEventBenefits']}' ";
					}
				}

				if($eventinfo['goods_rule']=='category'){
					$sqlWhereClause .= " and g.goods_seq in (select ec.goods_seq from fm_event_choice e inner join fm_category_link ec on e.category_code=ec.category_code where e.choice_type='category' and e.event_seq = '{$sc['selectEvent']}' and e.event_benefits_seq = '{$sc['selectEventBenefits']}' group by ec.goods_seq)";
					$sqlWhereClause .= " and g.goods_seq not in (select goods_seq from fm_event_choice where choice_type='except_goods' and event_seq = '{$sc['selectEvent']}' and event_benefits_seq = '{$sc['selectEventBenefits']}')";
					$sqlWhereClause .= " and g.goods_seq not in (select ec.goods_seq from fm_event_choice e inner join fm_category_link ec on e.category_code=ec.category_code where e.choice_type='except_category' and e.event_seq = '{$sc['selectEvent']}' and e.event_benefits_seq = '{$sc['selectEventBenefits']}' group by ec.goods_seq)";
				}

			}

			if(!empty($sc['selectGift'])){
				$sqlFromClause .= " left join fm_gift_choice gf on g.goods_seq = gf.goods_seq";
				$sqlWhereClause .= " and gf.gift_seq = '{$sc['selectGift']}' ";
			}

		}else if(!empty($sc['display_seq'])){
			if(!isset($sc['display_tab_index'])) $sc['display_tab_index'] = 0;
			$sqlFromClause .= " inner join fm_design_display_tab_item on (g.goods_seq=fm_design_display_tab_item.goods_seq and fm_design_display_tab_item.display_seq='{$sc['display_seq']}' and fm_design_display_tab_item.display_tab_index='{$sc['display_tab_index']}')";
			$sqlOrderbyClause = " order by fm_design_display_tab_item.display_tab_item_seq asc";
		}

		$sqlWhereClause .= " and g.goods_view='look' ";

		if($sc['goods_status']) {
			if(is_array($sc['goods_status']))
				$sqlWhereClause .= " and g.goods_status in ('".implode("','",$sc['goods_status'])."') ";
		}

		if(!empty($sc['goods_seq_string'])){
			$arr_goods_seq_string = explode(',',preg_replace("/[^0-9,]/","",$sc['goods_seq_string']));
			$sqlWhereClause .= " and g.goods_seq in ('".implode("','",$arr_goods_seq_string)."')";
		}

		if(!empty($sc['goods_seq_exclude'])){
			$sqlWhereClause .= " and g.goods_seq != '".$sc['goods_seq_exclude']."' ";
		}

		$sqlSelectClause .= ",l.category_link_seq,l.sort,(select category_code from fm_category_link where link=1 and goods_seq=g.goods_seq limit 1) as category_code ";
		$sqlGroupbyClause = " group by g.goods_seq";

		if(!empty($sc['category']))
		{
			$sqlFromClause .= "
				left join fm_category_link l on l.goods_seq=g.goods_seq and l.category_code = '".$sc['category']."'
				left join fm_category_group as cg on l.category_code = cg.category_code
			";
			$sqlWhereClause .= " and l.category_code = '".$sc['category']."'";
		}else{
			$sqlFromClause .= "
				left join fm_category_link l on l.goods_seq=g.goods_seq
				left join fm_category_group as cg on l.category_code = cg.category_code
			";
		}

		if(!empty($sc['color']))
		{
			$sqlFromClause .= "
				inner join fm_goods_option oc on oc.goods_seq=g.goods_seq and ifnull(oc.color,'') = '".$sc['color']."'
			";
		}

		if($sc['brand']) $sc['brands'] = array($sc['brand']);
		if(!empty($sc['brands']))
		{
			$sqlSelectClause .= ",bl.category_link_seq,bl.sort,bl.category_code ";
			$sqlFromClause .= "
				left join fm_brand_link bl on bl.goods_seq=g.goods_seq
			";
			if(!empty($sc['member_group_seq'])){
				$sqlFromClause .= "
					left join fm_brand_group as bg on bl.category_code = bg.category_code
				";
			}
			$sqlGroupbyClause = " group by g.goods_seq";
			$sqlWhereClause .= " and bl.category_code in ('".implode("','",$sc['brands'])."')";
		}

		if(!empty($sc['location']))
		{
			$sqlSelectClause .= ",ll.location_link_seq,ll.sort,ll.location_code ";
			$sqlFromClause .= "left join fm_location_link ll on ll.goods_seq=g.goods_seq ";
			$sqlGroupbyClause = " group by g.goods_seq";
			$sqlWhereClause .= " and ll.location_code = '".$sc['location']."'";
		}

		if(!$sc['admin_category']  && !defined('__ISADMIN__')){
			if(!empty($sc['member_group_seq'])){
				$sqlWhereClause .= " and ( cg.group_seq is null or find_in_set('".$sc['member_group_seq']."',concat_ws(',',cg.group_seq) ) )";
			}else{
				$sqlWhereClause .= " and ( cg.group_seq is null )";
			}
		}

		if(!empty($sc['list_goods_status'])) {
			$sqlWhereClause .= " and g.goods_status in ('".str_replace('|',"','",$sc['list_goods_status'])."')";
		}

		if($sc['start_price']){
			$sqlWhereClause .= " and o.price >= '{$sc['start_price']}' ";
		}

		if($sc['end_price']){
			$sqlWhereClause .= " and o.price <= '{$sc['end_price']}' ";
		}

		if(!empty($sc['search_text'])){

			if(!is_array($sc['search_text'])) $sc['search_text'] = array($sc['search_text']);

			if($_GET['old_search_text']){
				$arr_search_text = explode("\n",$_GET['old_search_text']);

				foreach($arr_search_text as $search_text){
					if(trim($search_text) && !in_array($search_text,$sc['search_text'])){
						$sc['search_text'][] = trim($search_text);
					}
				}
			}

			foreach($sc['search_text'] as $search_text){
				$search_text = str_replace(' ', '',addslashes($search_text));
				switch($sc['search_text']){
					case 'goods_name':
						$sqlWhereClause .= " and REPLACE(g.goods_name,' ','') like '%{$search_text}%'";
					break;
					case 'goods_code':
						$sqlWhereClause .= " and g.goods_code like '%{$search_text}%'";
					break;
					case 'summary':
						$sqlWhereClause .= " and REPLACE(g.summary,' ','') like '%{$search_text}%'";
					break;
					case 'keyword':
						$sqlWhereClause .= " and REPLACE(g.keyword,' ','') like '%{$search_text}%'";
					break;
					default:
						$sqlWhereClause .= " and
						(
							REPLACE(g.goods_name,' ','') like '%{$search_text}%'
							or g.goods_seq = '{$search_text}'
							or g.goods_code like '%{$search_text}%'
							or REPLACE(g.summary,' ','') like '%{$search_text}%'
							or REPLACE(g.keyword,' ','') like '%{$search_text}%'
							or (
								 select group_concat(sc_b.title,sc_b.title_eng) from fm_brand sc_b
								 inner join fm_brand_link sc_b2
								 on sc_b.category_code=sc_b2.category_code
								 where sc_b2.goods_seq=g.goods_seq
							) like '%{$search_text}%'
						)
						";
					break;
				}
			}
		}

		if(!empty($this->userInfo['member_seq'])){
			$sqlSelectClause .= ",if(w.wish_seq is not null,1,0) as wish ";
			$sqlFromClause .= "
				left join fm_goods_wish as w on w.goods_seq=g.goods_seq and w.member_seq='{$this->userInfo['member_seq']}'
			";
		}

		if(!empty($sc['relation'])){
			$sqlFromClause .= " inner join fm_goods_relation r on g.goods_seq=r.relation_goods_seq";
			$sqlWhereClause .= " and r.goods_seq = '{$sc['relation']}'";
			$sqlOrderbyClause =" order by r.relation_seq asc";
		}

		if(!$sqlOrderbyClause){
			if($sc['display_seq']){
				if(!isset($sc['display_tab_index'])) $sc['display_tab_index'] = 0;
				$sqlFromClause .= "
					inner join fm_design_display_tab_item as d on d.goods_seq=g.goods_seq and d.display_seq='{$sc['display_seq']}' and d.display_tab_index='{$sc['display_tab_index']}'
				";
			}
			switch($sc['sort'])
			{
				case "popular":
					if(!empty($sc['category'])){
						$sqlOrderbyClause =" order by l.sort asc, g.regist_date desc";
					}elseif(!empty($sc['brand'])){
						$sqlOrderbyClause =" order by bl.sort asc, g.regist_date desc";
					}else{
						$sqlOrderbyClause =" order by g.page_view desc";
					}
				break;
				case "newly":
					$sqlOrderbyClause =" order by g.regist_date desc, g.goods_seq desc";
				break;
				case "popular_sales":
					$sqlOrderbyClause =" order by g.purchase_ea desc, g.goods_seq desc";
				break;
				case "low_price":
					$sqlOrderbyClause =" order by o.price asc, g.goods_seq desc";
				break;
				case "high_price":
					$sqlOrderbyClause =" order by o.price desc, g.goods_seq desc";
				break;
				case "review":
					$sqlOrderbyClause =" order by g.review_count desc, g.goods_seq desc";
				break;
				case "display":
					$sqlOrderbyClause =" order by d.display_tab_index asc, d.display_tab_item_seq, g.goods_seq desc";
				break;
				default:
					$sqlOrderbyClause =" order by g.goods_seq desc, g.goods_seq desc";
				break;

			}
		}

		if(!empty($sc['limit'])){
			$sqlLimitClause = "limit {$sc['limit']}";
		}

		$sql = "
			{$sqlSelectClause}
			{$sqlFromClause}
			{$sqlWhereClause}
			{$sqlGroupbyClause}
			{$sqlOrderbyClause}
			{$sqlLimitClause}
		";
		if($sqlLimitClause){
			$query = $this->db->query($sql);
			$result['record'] = $query->result_array();
		}else{
			$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());
		}


		$cfg_reserve = ($this->reserves)?$this->reserves:config_load('reserve');
		if($result['record']){
			$this->load->model('categorymodel');
			foreach($result['record'] as $k => $data){

				// 브랜드 정보 ( 세개의 컬럼이 각각의 subquery로 되어 있는걸 한개의 쿼리로 수정 )
				$sql	= "select br.title as brand_title, br.
								title_eng as brand_title_eng,
								br.category_code as brand_code
							from fm_brand br,
								fm_brand_link brl
							where
								br.category_code = brl.category_code and brl.link = '1' and
								brl.goods_seq = '".$data['goods_seq']."' limit 1";
				$query	= $this->db->query($sql);
				$brand	= $query->row_array();
				$result['record'][$k]['brand_title']		= $brand['brand_title'];
				$result['record'][$k]['brand_title_eng']	= $brand['brand_title_eng'];
				$result['record'][$k]['brand_code']			= $brand['brand_code'];

				// 카테고리정보
				$tmparr2 = array();
				$categorys = $this->get_goods_category($data['goods_seq']);
				foreach($categorys as $key => $val){
					$tmparr = $this->categorymodel->split_category($val['category_code']);
					foreach($tmparr as $cate) $tmparr2[] = $cate;
				}
				if($tmparr2){
					$tmparr2 = array_values(array_unique($tmparr2));
					$data['r_category'] = $tmparr2;
				}

				// 등급혜택가격,정보 포함
				$result['record'][$k]['sale_price'] = $this->get_sale_price_tmp($data['goods_seq'], $data['price'], $data['r_category'] , $data['sale_seq'], $data['consumer_price']);
				$result['record'][$k]['string_price'] = get_string_price($data);
				$result['record'][$k]['string_price_use'] = 0;
				if($result['record'][$k]['string_price']!='') $result['record'][$k]['string_price_use'] = 1;

				// 아이콘에서 .gif 제거 및 이미지 크기 추출
				$result['record'][$k]['icons'] = str_replace('.gif','',$data['icons']);
				if	(file_exists(ROOTPATH.$data['image']))
					$result['record'][$k]['image_size'] = getimagesize(ROOTPATH.$data['image']);
				if(!empty($result['record'][$k]['icons']) && !is_array($result['record'][$k]['icons'])){
					$result['record'][$k]['icons'] = explode(",",$result['record'][$k]['icons']);
				}

				/* 이벤트가격,정보 포함 */
				if($sc['join_event']){
					if($categorys) foreach($categorys as $key => $value){
						if( $value['link'] == 1 ){
							$data['category_code'] = $this->categorymodel->split_category($value['category_code']);
						}
					}

					$result['record'][$k]['event'] = $this->get_event_price($data['price'], $data['goods_seq'], $data['category_code'], $data['consumer_price'], $data);

					if($result['record'][$k]['event']['event_sale'] || $result['record'][$k]['event']['event_reserve']){

						if($result['record'][$k]['event']['event_sale_unit'] > 0 ){
							if( $result['record'][$k]['event']['target_sale'] == 1 && $data['consumer_price'] > 0 ){//정가기준 할인시
								$result['record'][$k]['event_text'] = ($data['consumer_price'] > $result['record'][$k]['event']['event_sale_unit'])?$data['consumer_price'] - (int) $result['record'][$k]['event']['event_sale_unit']:0;

								//최종혜택가 적용
								$result['record'][$k]['sale_price'] = ($data['consumer_price'] > $result['record'][$k]['event']['event_sale_unit'])?$data['consumer_price'] - (int) $result['record'][$k]['event']['event_sale_unit']:0;

							}else{
								$result['record'][$k]['event_text'] = ($data['price'] > $result['record'][$k]['event']['event_sale_unit'])?$data['price'] - (int) $result['record'][$k]['event']['event_sale_unit']:0;
							}
						}
						else{
							if($result['record'][$k]['event']['event_sale']){
								$result['record'][$k]['event_text'] = "{$result['record'][$k]['event']['event_sale']}%추가할인";
							}
							if($result['record'][$k]['event']['event_reserve']){
								$result['record'][$k]['event_text'] = "{$result['record'][$k]['event']['event_reserve']}%추가적립";
							}
						}

					}
				}

				$result['record'][$k]['reserve'] = $this->get_reserve_with_policy($data['reserve_policy'],$data['price'],$cfg_reserve['default_reserve_percent'],$data['reserve_rate'],$data['reserve_unit'],$data['reserve']);
			}
		}

		for($i=0; $i<count($result['record']); $i++){
			$eventData = $this->get_event_price($result['record'][$i]['price'], $result['record'][$i]['goods_seq'], $result['record'][$i]['category_code']);
			$eventEnd="";
			$result['record'][$i]['event_order_cnt'] = $eventData['event_order_cnt'];

			if($eventData['end_date'] && $eventData['event_type'] == "solo"){
				if($eventData['app_end_time']){
					$eventEndDate = explode("-", $eventEndDateTime[0]);
					$eventEnd['year'] = date("Y");
					$eventEnd['month'] = date("m");
					$eventEnd['day'] = date("d");

					$eventEnd['hour'] = substr($eventData['app_end_time'], 0, 2);
					$eventEnd['min'] = substr($eventData['app_end_time'], -2);
					$eventEnd['second'] = "00";

				}else{
					$eventEndDateTime = explode(" ", $eventData['end_date']);

					$eventEndDate = explode("-", $eventEndDateTime[0]);
					$eventEnd['year'] = $eventEndDate[0];
					$eventEnd['month'] = $eventEndDate[1];
					$eventEnd['day'] = $eventEndDate[2];

					$eventEndTime = explode(":", $eventEndDateTime[1]);
					$eventEnd['hour'] = $eventEndTime[0];
					$eventEnd['min'] = $eventEndTime[1];
					$eventEnd['second'] = $eventEndTime[2];
				}
			}

			$result['record'][$i]['eventEnd'] = $eventEnd;
		}

		$result['page']['querystring'] = get_args_list();
		return $result;
	}

	// 상품 상세 노출용 데이터 추출
	public function get_goods_view($no, $no_related = false, $no_bigdata = false){

		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('membermodel');
		$this->load->model('wishmodel');
		$this->load->model('videofiles');
		$this->load->model('bigdatamodel');
		$this->load->helper('order');
		$this->load->library('sale');

		$applypage					= 'view';
		$cfg_reserve				= ($this->reserves)?$this->reserves:config_load('reserve');
		$cfg_order					= config_load('order');
		$goods						= $this->get_goods($no);
		$goods['string_price']		= get_string_price($goods);
		$goods['string_price_use']	= 0;
		if	($goods['string_price'] != '')	$goods['string_price_use']	= 1;
		$runout 					= true;
		$goods['title']				= strip_tags($goods['goods_name']);
		$videosc['tmpcode']			= $goods['videotmpcode'];
		$videosc['upkind']			= 'goods';
		$videosc['type']			= 'contents';
		$videosc['viewer_use']		= 'Y';
		$videosc['orderby']			= 'sort ';
		$videosc['sort']			= 'asc, seq desc ';
		$alerts						= array();
		$goodsStatusImage			= array();

		if	($goods['goods_type'] == 'gift'){
			return array('status'=>'error', 'errType'=>'echo', 'msg'=>'<script>alert("해당상품이 존재하지 않습니다.");top.location.href="/main";</script>');
		}
		if (!isset($goods['goods_seq'])) {
			return array('status'=>'error', 'errType'=>'back', 'msg'=>'해당상품이 존재하지 않습니다.');
		}

		$cfg_goods	= config_load('goods');
		if	($goods['video_use'] == 'Y' ){
			$video_size						= explode("X" , $goods['video_size']);
			$goods['video_size0']			= $video_size[0];
			$goods['video_size1']			= $video_size[1];
			$video_size_mobile				= explode("X" , $goods['video_size_mobile']);
			$goods['video_size_mobile0']	= $video_size_mobile[0];
			$goods['video_size_mobile1']	= $video_size_mobile[1];

			//상품 이미지 영역 노출 동영상 ( 모바일이면서 file_key_i 값이 있는 경우 )
			if	( $this->session->userdata('setMode') == 'mobile' && $goods['file_key_i'] ){
				$goods['uccdomain_thumbnail']		= uccdomain('thumbnail',$goods['file_key_i']);
				$goods['uccdomain_fileswf']			= uccdomain('fileswf',$goods['file_key_i']);
				$goods['uccdomain_fileurl']			= uccdomain('fileurl',$goods['file_key_i']);
				$video_size0						= $goods['video_size_mobile0'];
				$video_size1						= $goods['video_size_mobile1'];
				if	($goods['video_view_type'] != 2)	$goods['video_view']	= 'y';
			}elseif( uccdomain('thumbnail',$goods['file_key_w']) && $goods['file_key_w'] ) {
				$goods['uccdomain_thumbnail']		= uccdomain('thumbnail',$goods['file_key_w']);
				$goods['uccdomain_fileswf']			= uccdomain('fileswf',$goods['file_key_w']);
				$goods['uccdomain_fileurl']			= uccdomain('fileurl',$goods['file_key_w']);
				$video_size0						= $goods['video_size0'];
				$video_size1						= $goods['video_size1'];
				if	($goods['video_view_type'] != 2)	$goods['video_view'] = 'y';
			}
		}else{
			unset($goods['file_key_w'], $goods['file_key_i'], $goods['video_size']);
			$goods['video_use']		= 'N';
		}

		//동영상리스트
		$goodsvideofiles		= $this->videofiles->videofiles_list_all($videosc);
		if	($goodsvideofiles['result']) foreach($goodsvideofiles['result'] as $k => $data){
			// 상품상세 노출 동영상 ( 모바일이면서 file_key_i 값이 있는 경우 )
			if	( $this->session->userdata('setMode') == 'mobile' && $data['file_key_i'] ){
				$goodsvideofiles['result'][$k]['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_i']);
				$goodsvideofiles['result'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_i']);
				$goodsvideofiles['result'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_i']);
			}elseif( uccdomain('thumbnail',$data['file_key_w']) && $data['file_key_w'] ) {
				$goodsvideofiles['result'][$k]['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_w']);
				$goodsvideofiles['result'][$k]['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_w']);
				$goodsvideofiles['result'][$k]['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_w']);
			}
		}

		$i = 0;
		$goods['sub_info_desc']		= json_decode($goods['sub_info_desc']);
		if	($goods['sub_info_desc'])foreach($goods['sub_info_desc'] as $key => $value){
			if	($key != "_empty_" && $key != ""){
				$goods_sub['subInfo'][$i]["title"]	= $key;
				$goods_sub['subInfo'][$i]["desc"]	= $value;
				$i++;
			}
		}
		$goods['sub_info_desc']	= $goods_sub;

		// 페이지 로딩 후 경고메시지
		if	( $goods['goods_view'] == 'notLook'){
			$msg	= '미노출 상품 입니다.';
			if(empty($this->managerInfo['manager_seq']))
				return array('status'=>'error', 'errType'=>'back', 'msg'=>$msg);
			else $alerts[]		= $msg;
		}

		// 상품상태별 아이콘
		$tmp				= code_load('goodsStatusImage');
		foreach($tmp as $row){
			$goodsStatusImage[$row['codecd']]	= $row['value'];
		}

		// 회원정보 가져오기
		if	($this->userInfo){
			$data_member	= $this->membermodel->get_member_data($this->userInfo['member_seq']);
		}

		// 등급 체크
		$arr_group_seq		= $this->categorymodel->get_category_group_for_goods($no);
		if	($arr_group_seq && !$this->userInfo){
			if	(empty($this->managerInfo['manager_seq']))
				return array('status'=>'error', 'errType'=>'redirect', 'msg'=>'회원 전용 상품 입니다.', 'url'=>'/member/login?return_url='.$_SERVER['REQUEST_URI']);
		}
		if	($arr_group_seq && $data_member['group_seq'] && !in_array($data_member['group_seq'],$arr_group_seq)){
			$msg	= $data_member['group_name'].'회원 그룹은 접근 권한이 없습니다.';
			if	(empty($this->managerInfo['manager_seq']))
				return array('status'=>'error', 'errType'=>'back', 'msg'=>$msg);
			else $alerts[] = $msg;
		}

		// 브랜드 등급체크
		$arr_brand_group_seq	= $this->brandmodel->get_brand_group_for_goods($no);
		if	($arr_brand_group_seq && !$this->userInfo['member_seq']){
			if	(empty($this->managerInfo['manager_seq']))
				return array('status'=>'error', 'errType'=>'back', 'msg'=>'회원 전용 상품 입니다.','url'=>'/member/login?return_url='.$_SERVER["REQUEST_URI"]);
		}

		if	($arr_brand_group_seq && $data_member['group_seq'] && !in_array($data_member['group_seq'],$arr_brand_group_seq)){
			$msg	= $data_member['group_name'].'회원 그룹은 접근 권한이 없습니다.';
			if	(empty($this->managerInfo['manager_seq']))
				return array('status'=>'error', 'errType'=>'back', 'msg'=>$msg,'url'=>'/member/login?return_url='.$_SERVER["REQUEST_URI"]);
			else $alerts[]	= $msg;
		}
		$sessionMember	= $data_member;
		$images			= $this->get_goods_image($no);
		$additions		= $this->get_goods_addition($no);

		## 상품이미지 영역에 동영상 포함
		if	($goods['video_view'] == "y"){
			$imagesVideo['view'] = array('goods_seq' => $no
						,'cut_number' => 1
						,'image_type' => 'video'
						,'image' => $goods['uccdomain_fileurl']."&g=tag&width=".$video_size0."&height=".$video_size1
						,'match_color' =>''
						,'label' =>''
					);
			$imagesVideo['thumbView'] = array('goods_seq' => $no
						,'cut_number' => 1
						,'image_type' => 'video'
						,'image' => "/data/skin/".$this->skin."/images/common/icon_video_100.jpg"
						,'match_color' => ''
						,'label' => ''
					);
			$k=1;
			if($goods['video_position'] == "first"){ $imageloop[$k] = $imagesVideo; $k++; }
			foreach($images as $imagesItem){
				$imageloop[$k] = $imagesItem;
				$k++; 
			}
			if($goods['video_position'] == "last"){ $imageloop[$k] = $imagesVideo; }

			$images = $imageloop;
		}

		//추가정보의 모델명추출
		if($additions)foreach($additions as $data_additions){
			if( strstr($data_additions['type'],"goodsaddinfo_") ){
				$data_additions['contents_code'] = $data_additions['contents'];
				$data_additions['contents'] = $data_additions['contents_title'];
			}
			$newadditions[] = $data_additions;
		}
		$additions	= $newadditions;//재정의
		$options	= $this->get_goods_option($no);
		$suboptions	= $this->get_goods_suboption($no);
		$inputs		= $this->get_goods_input($no);
		$icons		= $this->get_goods_icon($no);

		// 카테고리정보
		$tmparr2	= array();
		$categorys	= $this->get_goods_category($goods['goods_seq']);
		foreach($categorys as $key => $val){
			if( $val['link'] == 1 ){
				$goods['category_code'] = $this->categorymodel->split_category($val['category_code']);
				$category_code=$goods['category_code'][count($goods['category_code'])-1];
			}else{
				if( $goods['category_code'] ) $goods['sub_category_code'] = $this->categorymodel->split_category($val['category_code']);
			}

			$tmparr = $this->categorymodel->split_category($val['category_code']);
			foreach($tmparr as $cate) $tmparr2[] = $cate;
		}
		if($tmparr2){
			$tmparr2 = array_values(array_unique($tmparr2));
			$goods['r_category'] = $tmparr2;
		}

		if($goods['category_code'])foreach($goods['category_code'] as $code){
			$goods['category'][] = $this->categorymodel->one_category_name($code);
		}

		$brands = $this->get_goods_brand($no);
		if($brands) foreach($brands as $key => $data){
			if( $data['link'] == 1 ){
				$goods['brand_code'] = $this->brandmodel->split_brand($data['category_code']);
				$goods['brand_name_eng'] = $data['title_eng'];
			}
		}

		if($goods['brand_code'])foreach($goods['brand_code'] as $code){
			$goods['brand'][] = $this->brandmodel->one_brand_name($code);
			$last_code = $code;
			$last_brand = $this->brandmodel->one_brand_name($code);
		}


		$view_brand = '<a href="./brand?code='.$last_code.'">'.$last_brand.'</a>';

		if($last_code){
			$brandInfo = $this->brandmodel->get_brand_info($last_code);
			$assignData['brandInfo']	= $brandInfo;
		}

		// 쿠폰 위치서비스 사용여부 :: 2014-04-01 lwh
		if($this->mobileMode)	$mapview_use	= $goods['m_mapview'];
		else					$mapview_use	= $goods['pc_mapview'];

		if($options)foreach($options as $k => $opt){

			/* 대표가격 */
			if($opt['default_option'] == 'y'){
				//----> sale library 적용 ( 정가기준 목록에서는 sale_price를 넘기지 않음 )
				unset($param, $sales);
				$param['cal_type']			= 'each';
				$param['option_type']		= 'option';
				$param['reserve_cfg']		= $cfg_reserve;
				$param['member_seq']		= $this->userInfo['member_seq'];
				$param['group_seq']			= $this->userInfo['group_seq'];
				$param['consumer_price']	= $opt['consumer_price'];
				$param['price']				= $opt['price'];
				$param['total_price']		= $opt['price'];
				$param['ea']				= 1;
				$param['goods_ea']			= 1;
				$param['category_code']		= $goods['r_category'];
				$param['goods_seq']			= $goods['goods_seq'];
				$param['goods']				= $goods;
				$this->sale->set_init($param);
				$sales						= $this->sale->calculate_sale_price($applypage);

				$goods['sales']				= $sales;
				$goods['org_price']			= ($opt['consumer_price']) ? $opt['consumer_price'] : $opt['price'];
				$goods['consumer_price']	= $opt['consumer_price'];
				$goods['price']				= $opt['price'];
				// 기존 스킨 유지를 위해 추가.
				$goods['basic_sale']		= $sales['sale_list']['basic'];
				$goods['event_sale_unit']	= $sales['sale_list']['event'];
				$goods['referer_sale_unit']	= $sales['sale_list']['referer'];
				$goods['mobile_sale_unit']	= $sales['sale_list']['mobile'];
				$goods['fblike_sale_unit']	= $sales['sale_list']['like'];
				$goods['member_sale_unit']	= $sales['sale_list']['member'];
				$goods['event']				= $this->sale->cfgs['event'];
				$goods['member_group']		= ($this->sale->cfgs['member']) ? $this->sale->cfgs['member'] : $this->sale->cfgs['no_member'];
				$goods['sum_sale_price']	= $sales['total_sale_price'];
				$goods['sale_price']		= $sales['result_price'];
				$goods['sale_rate']			= $sales['sale_per'];
				$goods['referer_sale']		= $this->sale->refererSales;
				$goods['mobile_sale']		= $this->sale->mobileSales;
				$goods['like_sale']			= $this->sale->get_fblikesale_config_list();
				$goods['group_benifits']	= $this->sale->get_groupsale_config();
				$goods['eventEnd']			= $sales['eventEnd'];
				$goods['point']				= (int) $this->get_point_with_policy($sales['result_price']) + $sales['tot_point'];
				$goods['reserve']			= $this->get_reserve_with_policy($goods['reserve_policy'],$sales['result_price'],$cfg_reserve['default_reserve_percent'],$opt['reserve_rate'],$opt['reserve_unit'],$opt['reserve']) + $sales['tot_reserve'];

				$this->sale->reset_init();
				//<---- sale library 적용
			}

			// 재고 체크
			$opt['chk_stock'] = check_stock_option($goods['goods_seq'],$opt['option1'],$opt['option2'],$opt['option3'],$opt['option4'],$opt['option5'],0,$cfg_order,'view');
			if( $opt['chk_stock'] ) $runout = false;

			$opt['opspecial_location'] = get_goods_options_print_array($opt);

			/* 쿠폰 위치서비스 사용시 배열 추가 lwh 2014-04-01 */
			if($mapview_use=='Y'){
				$mapArr[$k]['o_seq']			= $opt['option_seq'];
				$mapArr[$k]['option']			= $opt['option'.$opt['opspecial_location']['address']];
				$mapArr[$k]['address']			= $opt['address']. " " .$opt['addressdetail'];
				$mapArr[$k]['address_street']	= $opt['address_street'];
				$mapArr[$k]['biztel']			= $opt['biztel'];
			}

			if($data['newtype']) {
				$data['infomation'] = ($data['infomation'])?$data['infomation'].'<br/>'.get_goods_special_option_print($data):get_goods_special_option_print($data);
			}

			$options[$k] = $opt;
		}

		if($mapview_use=='Y'){
			$assignData['mapArr']	= $mapArr;
		}

		unset($opt);

		$sub_runout = false;
		if($suboptions) foreach($suboptions as $key => $tmp){
			foreach($tmp as $k => $opt){
				$opt['chk_stock'] = check_stock_suboption($goods['goods_seq'],$opt['suboption_title'],$opt['suboption'],0,$cfg_order,'view');
				if( $opt['chk_stock'] ){
					$sub_runout = true;
				}

				// 회원등급할인
				//----> sale library 적용
				unset($param);
				$param['cal_type']				= 'each';
				$param['option_type']			= 'suboption';
				$param['sub_sale']				= $opt['sub_sale'];
				$param['reserve_cfg']			= $cfg_reserve;
				$param['member_seq']			= $this->userInfo['member_seq'];
				$param['group_seq']				= $this->userInfo['group_seq'];
				$param['consumer_price']		= $opt['consumer_price'];
				$param['price']					= $opt['price'];
				$param['total_price']			= $opt['price'];
				$param['ea']					= 1;
				$param['category_code']			= $goods['r_category'];
				$param['goods_seq']				= $goods['goods_seq'];
				$param['goods']					= $goods;
				$this->sale->set_init($param);
				$sales							= $this->sale->calculate_sale_price($applypage);
				$opt['price']					= $sales['result_price'];
				$this->sale->reset_init();
				unset($sales);
				//<---- sale library 적용

				$suboptions[$key][$k] = $opt;
			}
		}

		if(isset($options[0]['option_divide_title'])) $goods['option_divide_title'] = $options[0]['option_divide_title'];
		if(isset($options[0]['divide_newtype'])) $goods['divide_newtype'] = $options[0]['divide_newtype'];


		// 배송정보 가져오기
		$delivery = $this->get_goods_delivery($goods);

		// 오늘본 상품 쿠키
		$today_num = 0;
		$today_view = $_COOKIE['today_view'];
		if( $today_view ) $today_view = unserialize($today_view);
		if( $today_view ) foreach($today_view as $v){
			$today_num++;
			if( count($today_view) > 50 && $today_num == 1 ) continue;
			$data_today_view[] = $v;

		}
		if( ! in_array($no , $today_view) ) {
			$data_today_view[] = $no;

			//페이지뷰 증가
			$this->increase_page_view($no);
		}
		if( $data_today_view ) $data_today_view = serialize($data_today_view);
		setcookie('today_view',$data_today_view,time()+86400,'/');

		/* 동영상/플래시매직 치환 */
		$goods['contents'] = showdesignEditor($goods['contents']);
		$goods['mobile_contents'] = showdesignEditor($goods['mobile_contents']);
		$goods['common_contents'] = showdesignEditor($goods['common_contents']);		

		// 모바일 상세 설명 생성
		if( !$goods['mobile_contents'] )
		{
			$goods['mobile_contents'] = $this->set_mobile_contents($goods['contents'],$goods['goods_seq']);
		}

		if($this->__APP_USE__ == 'f') {//페이스북회원인경우 
			//카테고리추가
			foreach($categorys as $fbtitlecategorys)$category_title[] = $fbtitlecategorys['title'];
			if	(is_array($category_title))			$fbcategory_title	= implode(" > ", $category_title);
		}

		if($images){
			foreach($images as $image){
				if($image['view']['image']) {
					$filetypetmp = @getimagesize(ROOTPATH.$image['view']['image']);
					if($filetypetmp[0] > 200){
						$APP_IMG	= $image['view']['image'];
						break;
					}else{
						$APP_IMG	= $image['large']['image'];
						break;
					}
				}elseif($image['large']['image']) {
					$APP_IMG	= $image['large']['image'];
					break;
				}
			}
		}

		// 사은품정보
		$today = date('Y-m-d');
		$gift_goods[] = $goods['goods_seq'];
		$gift_categorys = $goods['r_category'];
		$this->load->model('giftmodel');
		$goods['gift'] = $this->giftmodel->get_gift($today,$goods['price'],$gift_goods,$gift_categorys);

		// 사은품을 선택할 수 있는 조건(사은품 재고는 필수)
		if(!$goods['gift']['benifits']) unset($goods['gift']);

		// debug_var($gift_result);
		// 무이자 할인
		$pg = config_load($this->config_system['pgCompany']);
		if($pg['nonInterestTerms'] == 'manual'){
			$tmp = code_load($this->config_system['pgCompany'].'CardCompanyCode');
			foreach($tmp as $company_code){
				$r_card_company[$company_code['codecd']] = $company_code['value'];
			}
			if($pg['pcCardCompanyCode']) foreach($pg['pcCardCompanyCode'] as $key => $code){
				$goods['nointerest'][] = $r_card_company[$code] . " " . $pg['pcCardCompanyTerms'][$key];
			}
		}
		$this->protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';

		// 단독이벤트만 판매시 이벤트기간이 아니면 판매중지 @2013-11-29
		if( $goods['socialcp_event'] == 1 && $goods['event']['event_goodsStatus'] === true ){
			$goods['goods_status'] = 'unsold';
		}

		// 재고가 없을 시 품절로 상태 표기
		if( $goods['goods_status'] == 'normal' &&  $runout ){
			$goods['goods_status'] = 'runout';
		}

		// 위시여부 2014-01-10 lwh
		$wish_seq = $this->wishmodel->confirm_wish($_GET['no']);

		// 관련상품 로딩
		if($goods['relation_count_w']*$goods['relation_count_h'] && !$no_related){

			$this->load->model('goodsdisplay');
			$sql = "select * from fm_design_display where kind = 'relation'";
			$query = $this->db->query($sql);
			$display = $query->row_array();
			if(!$display){
				$this->get_goods_relation_display_seq();
				$sql = "select * from fm_design_display where kind = 'relation'";
				$query = $this->db->query($sql);
				$display = $query->row_array();
			}

			if($goods['relation_count_w']==0 && $goods['relation_count_h']==0){
				$display['count_w'] = 4;
				$display['count_h'] = 1;
			}else{
				$display['count_w'] = $goods['relation_count_w'];
				$display['count_h'] = $goods['relation_count_h'];
			}
			$display['image_size'] = $goods['relation_image_size'];
			$display['auto_criteria'] = $goods['relation_criteria'];

			$sc['limit'] = $display['count_w']*$display['count_h'];

			if($goods['relation_type']=='AUTO'){
				$sc = $this->goodsdisplay->search_condition($display['auto_criteria'], array(),'relation');
				if(!$sc['category']) $sc['category'] = $category_code;

				$sc['sort']		= $sc['auto_order'];
				$sc['display_seq']		= $display['display_seq'];
				$sc['display_tab_index']= 0;
				$sc['page']				= 1;
				$sc['perpage']			= $display['count_w']*$display['count_h'];
				$sc['image_size']		= $display['image_size'];
				$sc['goods_seq_exclude']= $goods['goods_seq'];

				if($this->goodsdisplay->info_settings_have_eventprice($display['info_settings'])){
					$sc['join_event']	= true;
				}

				$list = $this->goods_list($sc);
			}else{
				$sc['relation'] = $goods['goods_seq'];
				$list = $this->goods_list($sc);
			}
			if($list['record']){
				$display_key = $this->goodsdisplay->make_display_key();
				$this->goodsdisplay->set('display_key',$display_key);
	//			$this->goodsdisplay->set('title',$display['title']);
				$this->goodsdisplay->set('style',$display['style']);
				$this->goodsdisplay->set('count_w',$display['count_w']);
				$this->goodsdisplay->set('count_h',$display['count_h']);
				$this->goodsdisplay->set('image_decorations',$display['image_decorations']);
				$this->goodsdisplay->set('image_size',$display['image_size']);
				$this->goodsdisplay->set('text_align',$display['text_align']);
				$this->goodsdisplay->set('info_settings',$display['info_settings']);
				$this->goodsdisplay->set('displayGoodsList',$list['record']);
				$this->goodsdisplay->set('displayTabsList',array($list));
				$this->goodsdisplay->set('tab_design_type',$display['tab_design_type']);

				$goodsRelationDisplayHTML = "<div id='{$display_key}' class='designGoodsRelationDisplay' designElement='goodsRelationDisplay' displaySeq='{$display['display_seq']}'>";
				$goodsRelationDisplayHTML .= $this->goodsdisplay->print_(true);
				$goodsRelationDisplayHTML .= "</div>";

				$assignData['goodsRelationDisplayHTML']	= $goodsRelationDisplayHTML;
			}
		}

		// 빅데이터 추가
		if	(!$no_bigdata){
			$kinds				= $this->bigdatamodel->get_kind_array();
			foreach($kinds as $kind => $text){
				$cfg				= config_load('bigdata_'. $kind);
				$cfg['same_type']	= explode(',', $cfg['same_type']);
				if	(	( ($this->mobileMode || $this->storemobileMode) && $cfg['use_view_m'] == 'y' ) || 
						( !($this->mobileMode || $this->storemobileMode) && $cfg['use_view_p'] == 'y' ) ) {

					// 대상 회원 정보 추출 ( IP )
					unset($sc, $members);
					$sc['src_month']	= $cfg['smonth'];
					$sc['src_kind']		= $kind;
					$sc['goods_seq']	= $no;
					$members			= $this->bigdatamodel->get_member_seq($sc);
					unset($exceptCnt, $category, $brand, $location, $sc, $goodsList);
					if	(is_array($members) && count($members) > 0){
						// 빅데이터 상품 추출
						$limit				= 20;
						if	($cfg['except'] > 0){
							$exceptCnt		= $cfg['except'];
							$limit			= $cfg['except'];
						}
						if	(count($cfg['same_type']) > 0){
							foreach($cfg['same_type'] as $k => $type){
								if		($type == 'category'){
									$category	= $this->get_goods_category_default($no);
								}elseif	($type == 'brand'){
									$brand		= $this->get_goods_brand_default($no);
								}elseif	($type == 'location'){
									$location	= $this->get_goods_location_default($no);
								}
							}
							$sc['category1']	= $category['category_code'];
							$sc['brands1']		= $brand['category_code'];
							$sc['location1']	= $location['category_code'];
						}
						$sc['src_month']	= $cfg['tmonth'];
						$sc['src_kind']		= $cfg['tkind'];
						$sc['members']		= implode(',', $members);
						$goodsList			= $this->bigdatamodel->get_goods_seq($sc, $limit);

						// 제한 수량보다 적으면 미노출 처리
						if	( $exceptCnt > 0 && $exceptCnt > count($goodsList) )	$goodsList	= array();
					}

					if	(is_array($goodsList) && count($goodsList) > 0){
						if	($this->mobileMode)	$cfg['view_count_w']	= 3;

						$reKinds[$kind]['cfg']		= $cfg;
						$reKinds[$kind]['textStr']	= '상품을 '.$text.' 고객들이 가장 많이 '.$kinds[$cfg['tkind']].' 다른 상품';
						$reKinds[$kind]['display']	= $this->bigdatamodel->get_bigdata_goods_display($cfg['view_count_w'], $goodsList);
					}
				}
			}
		}

		/* 쇼핑몰 타이틀 */
		if($this->config_basic['shopGoodsTitleTag'] && $goods['title']){
			$title		= str_replace('{상품명}', $goods['title'], $this->config_basic['shopGoodsTitleTag']);
			$assignData['shopTitle']			= $title;
		}

		if($goods['event']['end_date'] && $goods['event']['event_type'] == "solo")
			$assignData['eventEnd']				= $goods['eventEnd'];
		if	($goodsvideofiles['result'])
			$assignData['goodsvideofiles']		= $goodsvideofiles['result'];


		$assignData['APP_IMG']				= $APP_IMG;
		$assignData['fbcategory_title']		= $fbcategory_title;
		$assignData['sales']				= $goods['sales'];
		$assignData['mobilesale']			= $goods['mobile_sale'];
		$assignData['fblikesale']			= $goods['like_sale'];
		$assignData['goodsStatusImage']		= $goodsStatusImage;
		$assignData['goodsImageSize']		= config_load('goodsImageSize');
		$assignData['sub_runout']			= $sub_runout;
		$assignData['sessionMember']		= $sessionMember;
		$assignData['goods']				= $goods;
		$assignData['options']				= $options;
		$assignData['additions']			= $additions;
		$assignData['suboptions']			= $suboptions;
		$assignData['inputs']				= $inputs;
		$assignData['images']				= $images;
		$assignData['icons']				= $icons;
		$assignData['delivery']				= $delivery;
		$assignData['view_brand']			= $view_brand;
		$assignData['cfg_reserve']			= $cfg_reserve;
		$assignData['wish_seq']				= $wish_seq;
		$assignData['bigdata']				= $reKinds;

		return array(	'assign'	=> $assignData, 
						'category'	=> $category, 
						'goods'		=> $goods,
						'alerts'	=> $alerts 
					);
	}
}

/* End of file goods.php */
/* Location: ./app/models/goods.php */