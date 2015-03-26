<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class goods_process extends front_base {

	public function __construct() {
		parent::__construct();

	}

	public function restock_notify_apply(){
		$this->load->model('ssl');
		$this->ssl->decode();

		$this->load->library('validation');
		$this->validation->set_rules('cellphone[]', '휴대폰번호','trim|required|max_length[4]|numeric|xss_clean');

		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		$goods_seq = $_POST['goods_seq'];
		$isMobile = $_POST['isMobile'];
		$member_seq = !empty($this->userInfo['member_seq']) ? $this->userInfo['member_seq'] : null;
		$cellphone = implode("-",$_POST['cellphone']);
		$optionType = $_POST['optionType'];
		$key = get_shop_key();
		$opFlag = false;
		$callback = $isMobile ? "parent.closeDialogAll('restock');" : "parent.closeDialog('restockNotifyApply');";

		if($_POST['viewOptionsReStock'][0]){
			$opFlag = true;
			if($optionType == "divide"){
				$option = $this->setOptionFunc($_POST['title'],$_POST['viewOptionsReStock']);
			}else if($optionType == "join"){
				$title = explode(",",$_POST['title'][0]);
				$viewOptionsReStock = explode("/",$_POST['viewOptionsReStock'][0]);
				$option = $this->setOptionFunc($title,$viewOptionsReStock);
			}
		}

		$sql_where = " notify_status='none' and goods_seq='$goods_seq' and AES_DECRYPT(UNHEX(cellphone), '{$key}')='$cellphone' ";
		if($option){
			foreach($option as $key => $val){
				if($sql_where) $sql_where .= " and ";
				$sql_where .= sprintf('`%s`="%s"' , $key, $val);
			}
		}else{
			$sql_where .= " and restock_option_seq is null ";
		}

		$query = $this->db->query("select * from fm_goods_restock_notify a left join fm_goods_restock_option b on a.restock_notify_seq = b.restock_notify_seq where ".$sql_where);
		if($query->row_array()){
			openDialogAlert("이미 동일한 신청내역이 있습니다.",400,140,'parent',$callback);
			exit;
		}

		$this->db->insert("fm_goods_restock_notify",array(
			'goods_seq' => $goods_seq,
			'member_seq' => $member_seq,
			'cellphone' => $cellphone,
			'regist_date' => date('Y-m-d H:i:s')
		));
		$restock_notify_seq = $this->db->insert_id();

		### Private Encrypt
		$cellphone = get_encrypt_qry('cellphone');
		$sql = "update fm_goods_restock_notify set {$cellphone} where restock_notify_seq = {$restock_notify_seq}";
		$this->db->query($sql);

		### Option Add
		if($opFlag){
			$option["restock_notify_seq"] = $restock_notify_seq;
			$this->db->insert("fm_goods_restock_option",$option);
		}

		openDialogAlert("해당 상품의 재입고 시<br />휴대폰 알림 신청이 완료었습니다.",400,150,'parent',$callback);

	}

	public function setOptionFunc($arr,$arr2){
		$op = array();
		$len = sizeOf($arr);
		for($i=0; $i < $len; $i++){
			$op["title".($i+1)] = $arr[$i];
			$op["option".($i+1)] = $arr2[$i];
		}
		return $op;
	}
	
	// 브랜드 전체 검색부분 추가 혹시나 다른곳에서도 동종 이슈가 잇을수 있어서 AJAX 로 처리
	public function brand_search() {
		$keywordSql = "( (1) = '1' )";
		if($this->input->post("keyword")) {
			$keywordSql = "( title LIKE '".$this->input->post("keyword")."%' OR title_eng LIKE '".$this->input->post("keyword")."%')";
		}
		
		$classificationSql = "( (1) = '1' )";
		$all = false;
		if(is_array($this->input->post("classification"))) {
			$classificationOr = array();
			foreach($this->input->post("classification") as $classification) {
				if($classification) {
					$classificationOr[] = "classification LIKE '%\"seq\"%\"".$classification."\"%'" ;
				} else {
					$all = true;
					break;
				}
			}
			if($all) {
				$classificationSql = "( (1) = '1' )";
			} else {
				$classificationSql = " ( ".implode(" OR ", $classificationOr)." ) ";
			}
		}
		if( ! $this->input->post("keyword") && !$this->input->post("classification")) {
			$result["result"] = false;
		} else {
			if(! $this->input->post("keyword") && $all) {
				$result["result"] = false;
			} else {
				$sql = "SELECT * FROM `fm_brand` WHERE ".$keywordSql." AND ".$classificationSql;
				$result = $this->db->query($sql)->result_array();
			}
		}
		echo json_encode($result);
		return true;
		exit;
	}

	// 모바일에서 추가입력옵션의 파일 업로드처리하는 함수
	public function upload_goods_inputs(){

		/* 이미지파일 확장자 */
		$this->arrImageExtensions = array('jpg','jpeg','png','gif','bmp','tif','pic');
		$this->arrImageExtensions = array_merge($this->arrImageExtensions,array_map('strtoupper',$this->arrImageExtensions));

		// 데모몰에서는 차단
		if($this->demo){
			$result = array(
				'status' => 0,
				'msg' => '데모몰에서는 업로드가 불가합니다.',
				'desc' => '업로드 불가'
			);	
			echo "[".json_encode($result)."]";
			exit;
		}
		
		if(!$_FILES){
			$result = array('status' => 1);
			echo json_encode($result);
			exit;
		}

		$result = array(
			'status' => 0,
			'msg' => '업로드 실패하였습니다.',
			'desc' => '업로드 실패'
		);

		$path = 'data/tmp';
		$target_path = ROOTPATH.$path;

		foreach($_FILES['viewInputsUploader']['name'] as $k=>$v){
			if($v){

				$file_ext = end(explode('.', $_FILES['viewInputsUploader']['name'][$k]));
				$file_name = "temp_".$k."_".time().sprintf("%04d",rand(0,9999)).'.'.$file_ext;

				if( !in_array($file_ext,$this->arrImageExtensions) ){
					$result = array(
						'status' => 0,
						'msg' => '허용되지 않는 확장자입니다.',
						'desc' => '허용되지 않는 확장자'
					);
					break;
				}else if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$_FILES['viewInputsUploader']['name'][$k]) && !$_POST['allowKorean']){
					$result = array(
						'status' => 0,
						'msg' => '파일명에 한글이 포함되어있습니다.<br />영문 파일명으로 변경 후 업로드해주세요.',
						'desc' => '한글파일명 업로드 불가'
					);
					break;
				}else{
					@list($imgWidth, $imgHeight) = getimagesize($_FILES['viewInputsUploader']['tmp_name'][$k]);

					if(!move_uploaded_file($_FILES['viewInputsUploader']['tmp_name'][$k], $target_path.'/'.$file_name))
					{
						$result = array(
							'status' => 0,
							'msg' => $this->upload->display_errors(),
							'desc' => '업로드 실패'
						);
						break;
					}else{
						@chmod($target_path.'/'.$file_name,0777);

						unset($result['msg']);
						unset($result['desc']);
						$result['status'] = 1;
						$result['fileList'][] = array('filePath' => $path.'/'.$file_name,'fileInfo'=>array(
							'client_name' => $_FILES['viewInputsUploader']['name'][$k],
							'image_width' => $imgWidth,
							'image_height' => $imgHeight
						));
					}
				}
			}else{
				$result['status'] = 1;
				$result['fileList'][] = null;
			}
		}

		echo json_encode($result);
	}

}

/* End of file goods_process.php */
/* Location: ./app/controllers/goods_process.php */