<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class promotion_process extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->library(array('validation','pxl'));
		$this->load->model('promotionmodel');
	}

	public function index()
	{
		redirect("/admin/promotion/catalog");
	}

	public function promotionusesave(){
		config_save("reserve" ,array('promotioncode_use'=>$_POST['promotioncode_use']));
		$result = array("result"=>true,"msg"=>"설정이 저장 되었습니다");
		echo json_encode($result);
		exit;
	}


	//프로모션코드 등록
	public function promotion()
	{
		$parampromotion = $this->promotionmodel->check_param_promotion_download();
		$this->db->insert('fm_promotion', $parampromotion);
		$promotionSeq = $this->db->insert_id();
		$paramoffline["use_count"]				= 1;
		$paramoffline["code_number"]		= mt_rand();//생성
		$paramoffline["promotion_seq"]		= $promotionSeq;
		$paramoffline["regist_date"]			= $parampromotion["regist_date"];
		if( $parampromotion['promotion_type'] == 'input') {//수동생성1
			$paramoffline["code_serialnumber"] = $parampromotion["promotion_input_serialnumber"];
			$this->db->insert('fm_promotion_code', $paramoffline);
		}elseif( $parampromotion['promotion_type'] == 'one') {//자동생성 -> 발급시자동생성 5~6자리
			$paramoffline["code_serialnumber"]		= $parampromotion["promotion_input_serialnumber"];
			$this->db->insert('fm_promotion_code', $paramoffline);
		}elseif( $parampromotion['promotion_type'] == 'random') {//자동생성 >  -> 발급시자동생성 4-4-4-4
			//$paramoffline["code_serialnumber"]		= $parampromotion["promotion_input_serialnumber"];
			//$this->db->insert('fm_promotion_code', $paramoffline);
		}elseif( $parampromotion['promotion_type'] == 'file') {//수동생성2 > 파일
			//'offline_file', '수동생성 > 엑셀파일' $this->db->insert('fm_promotion_code_input', $paramoffline);
		}

		//사용제한
		if($_POST['issue_type'] == 'issue' ) {//사용가능
			if(isset($_POST['issueGoods'])){
				foreach($_POST['issueGoods'] as $goodsSeq){
					$paramIssuegoods['promotion_seq']	= $promotionSeq;
					$paramIssuegoods['goods_seq']		= $goodsSeq;
					$paramIssuegoods['type']					= 'issue';
					$this->db->insert('fm_promotion_issuegoods', $paramIssuegoods);
				}
			}
			if(isset($_POST['issueCategoryCode'])){
				foreach($_POST['issueCategoryCode'] as $categoryCode){
					$paramIssuecategory['promotion_seq']	= $promotionSeq;
					$paramIssuecategory['category_code']	= $categoryCode;
					$paramIssuecategory['type']					= 'issue';
					$this->db->insert('fm_promotion_issuecategory', $paramIssuecategory);
				}
			}
			if(isset($_POST['issueBrandCode'])){
				foreach($_POST['issueBrandCode'] as $brandCode){
					$paramIssuebrand['promotion_seq']		= $promotionSeq;
					$paramIssuebrand['brand_code']			= $brandCode;
					$paramIssuebrand['type']						= 'issue';
					$this->db->insert('fm_promotion_issuebrand', $paramIssuebrand);
				}
			}
		}elseif($_POST['issue_type'] == 'except' ){//사용불가
			if(isset($_POST['exceptIssueGoods'])){
				foreach($_POST['exceptIssueGoods'] as $goodsSeq){
					$paramIssuegoods['promotion_seq']		= $promotionSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']						= 'except';
					$this->db->insert('fm_promotion_issuegoods', $paramIssuegoods);
				}
			}
			if(isset($_POST['exceptIssueCategoryCode'])){
				foreach($_POST['exceptIssueCategoryCode'] as $categoryCode){
					$paramIssuecategory['promotion_seq']		= $promotionSeq;
					$paramIssuecategory['category_code']		= $categoryCode;
					$paramIssuecategory['type']						= 'except';
					$this->db->insert('fm_promotion_issuecategory', $paramIssuecategory);
				}
			}

			if(isset($_POST['exceptIssueBrandCode'])){
				foreach($_POST['exceptIssueBrandCode'] as $brandCode){
					$paramIssuebrand['promotion_seq']		= $promotionSeq;
					$paramIssuebrand['brand_code']			= $brandCode;
					$paramIssuebrand['type']						= 'except';
					$this->db->insert('fm_promotion_issuebrand', $paramIssuebrand);
				}
			}
		}


		if( $parampromotion['promotion_type'] == 'file') {//수동생성2 > 파일
			$callback = "parent.offlineexcelsave('".$promotionSeq."');";
			openDialogAlert("인증번호를 일괄등록시작합니다.<br><b><font color=red>창이 닫히지 않도록 주의해 주세요.</font></b>",400,140,'parent',$callback);
		}else{
			$callback = "parent.document.location.href='/admin/promotion/regist?no=".$promotionSeq."';";
			openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
		}
	}

	//온라인프로모션코드수정
	public function promotion_modify()
	{

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('promotion_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$promotionSeq = (int) $_POST['promotionSeq'];
		$parampromotion = $this->promotionmodel->check_param_promotion_download();
		$this->db->where('promotion_seq', $promotionSeq);
		$this->db->update('fm_promotion', $parampromotion);

		$this->db->delete('fm_promotion_issuecategory', array('promotion_seq' => $promotionSeq));
		$this->db->delete('fm_promotion_issuegoods', array('promotion_seq' => $promotionSeq));
		$this->db->delete('fm_promotion_issuebrand', array('promotion_seq' => $promotionSeq));

		$paramIssuecategory = array();
		if($_POST['issue_type'] == 'issue' ){
			if( isset($_POST['issueCategoryCode']) ){
				foreach($_POST['issueCategoryCode'] as $key => $categoryCode){
					if( isset($_POST['issueCategoryCodeSeq'][$categoryCode]) ){
						$paramIssuecategory['issuecategory_seq']	= $_POST['issueCategoryCodeSeq'][$categoryCode];
					}else{
						$paramIssuecategory['issuecategory_seq'] = '';
					}

					$paramIssuecategory['promotion_seq']			= $promotionSeq;
					$paramIssuecategory['category_code']		= $categoryCode;
					$paramIssuecategory['type']						= 'issue';
					$this->db->insert('fm_promotion_issuecategory', $paramIssuecategory);
				}
			}

			if( isset($_POST['issueGoods']) ){
				foreach($_POST['issueGoods'] as $key => $goodsSeq){
					if( isset($_POST['issueGoodsSeq'][$goodsSeq]) ){
						$paramIssuegoods['issuegoods_seq']	= $_POST['issueGoodsSeq'][$goodsSeq];
					}else{
						$paramIssuegoods['issuegoods_seq'] = '';
					}
					$paramIssuegoods['promotion_seq']		= $promotionSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']						= 'issue';
					$this->db->insert('fm_promotion_issuegoods', $paramIssuegoods);
				}
			}

			if( isset($_POST['issueBrandCode']) ){
				foreach($_POST['issueBrandCode'] as $key => $brandSeq){
					if( isset($_POST['issueBrandSeq'][$brandSeq]) ){
						$paramIssuebrand['issuebrand_seq']	= $_POST['issueBrandSeq'][$brandSeq];
					}else{
						$paramIssuebrand['issuebrand_seq'] = '';
					}
					$paramIssuebrand['promotion_seq']		= $promotionSeq;
					$paramIssuebrand['brand_code']				= $brandSeq;
					$paramIssuebrand['type']						= 'issue';
					$this->db->insert('fm_promotion_issuebrand', $paramIssuebrand);
				}
			}

		}elseif($_POST['issue_type'] == 'except' ){
			if( isset($_POST['exceptIssueCategoryCode']) ){
				foreach($_POST['exceptIssueCategoryCode'] as $key => $categoryCode){
					if( isset($_POST['exceptIssueCategoryCodeSeq'][$categoryCode]) ){
						$paramIssuecategory['issuecategory_seq']	= $_POST['exceptIssueCategoryCodeSeq'][$categoryCode];
					}else{
						$paramIssuecategory['issuecategory_seq'] = '';
					}
					$paramIssuecategory['promotion_seq']			= $promotionSeq;
					$paramIssuecategory['category_code']		= $categoryCode;
					$paramIssuecategory['type']						= 'except';
					$this->db->insert('fm_promotion_issuecategory', $paramIssuecategory);
				}
			}
			if( isset($_POST['exceptIssueGoods']) ){
				foreach($_POST['exceptIssueGoods'] as $key => $goodsSeq){
					if( isset($_POST['exceptIssueGoodsSeq'][$goodsSeq]) ){
						$paramIssuegoods['issuegoods_seq']	= $_POST['exceptIssueGoodsSeq'][$goodsSeq];
					}else{
						$paramIssuegoods['issuegoods_seq'] = '';
					}
					$paramIssuegoods['promotion_seq']		= $promotionSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']						= 'except';
					$this->db->insert('fm_promotion_issuegoods', $paramIssuegoods);
				}
			}

			if( isset($_POST['exceptIssueBrandCode']) ){
				foreach($_POST['exceptIssueBrandCode'] as $key => $brandSeq){
					if( isset($_POST['exceptIssueBrandSeq'][$brandSeq]) ){
						$paramIssuebrand['issuebrand_seq']	= $_POST['exceptIssueBrandSeq'][$brandSeq];
					}else{
						$paramIssuebrand['issuebrand_seq'] = '';
					}
					$paramIssuebrand['promotion_seq']		= $promotionSeq;
					$paramIssuebrand['brand_code']			= $brandSeq;
					$paramIssuebrand['type']						= 'except';
					$this->db->insert('fm_promotion_issuebrand', $paramIssuebrand);
				}
			}

		}

		$callback = "parent.document.location.reload();";
		openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
	}

	//온라인프로모션코드삭제
	public function promotion_delete()
	{
		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('promotion_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$promotionSeq = (int) $_POST['promotionSeq'];
		$promotions 	= $this->promotionmodel->get_promotion($promotionSeq);
		if($promotions) {
			$this->db->delete('fm_promotion_issuecategory', array('promotion_seq' => $promotionSeq));
			$this->db->delete('fm_promotion_issuegoods', array('promotion_seq' => $promotionSeq));
			$this->db->delete('fm_promotion_issuebrand', array('promotion_seq' => $promotionSeq));

			$this->db->delete('fm_promotion_code', array('promotion_seq' => $promotionSeq));
			$this->db->delete('fm_promotion_code_input', array('promotion_seq' => $promotionSeq));

			$result = $this->db->delete('fm_promotion', array('promotion_seq' => $promotionSeq));
			if($result) {
				if(@is_file($this->promotionmodel->promotionupload_dir.$promotions['promotion_image4'])) {
					@unlink($this->promotionmodel->promotionupload_dir.$promotions['promotion_image4']);
				}

				$return = array('result' => 'true', 'msg' => '삭제 되었습니다.');
				echo json_encode($return);
			}else{
				$return = array('result' => 'false', 'msg' => '프로모션코드삭제가 실패되었습니다.');
				echo json_encode($return);
			}
		}else{
			$return = array('result' => 'false', 'msg' => '잘못된 접근입니다.');
			echo json_encode($return);
		}
		exit;
	}

	//개별 프로모션코드 > 인증번호 보기
	public function promotion_code_list()
	{
		$no = (int) $_POST['no'];
		$promotions 		= $this->promotionmodel->get_promotion($no);
		$this->template->assign(array('promotions'=>$promotions));

		### SEARCH
		$sc = $_POST;
		$sc['perpage']		= (!empty($_POST['perpage'])) ?	intval($_POST['perpage']):20;
		$sc['page']			= (!empty($_POST['page'])) ?		intval(($_POST['page'] - 1) * $sc['perpage']):0;

		if($promotions['promotion_type'] == 'file'){//랜덤등록과 엑셀등록인 경우
			$data = $this->promotionmodel->promotioncode_input_list($sc);
		}else{
			$data = $this->promotionmodel->promotioncode_list($sc);
		}
		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount =  get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);
		if($promotions['promotion_type'] == 'file'){//엑셀등록인 경우
			$sc['totalcount'] = $this->promotionmodel->get_promotioncode_input_item_total_count($no);
		}else{
			$sc['totalcount'] = $this->promotionmodel->get_promotioncode_item_total_count($no);
		}

		$html = '';
		$i = 0;
		foreach($data['result'] as $datarow){
			if($promotions['promotion_type'] == 'file'){//랜덤등록과 엑셀등록인 경우
				$downusecolor = ($datarow['down_use'] == 1)? ' blue':'   ';
				$usecolor			 = ($datarow['use_count'] == 0)? ' red bold ':$downusecolor;
				//$downusetitle = ($datarow['down_use'] == 1)? ' 발급 ':'  ';
				//$shtml = '<span class="'.$downusecolor.'" >'.$downusetitle.'</span>';
			}else{
				$downusecolor = ' blue';
				$usecolor = ($datarow['use_count'] == 0)? ' red bold ':$downusecolor;
			}
			$datarow['number'] = $data['count'] - ( ( $page -1 ) * $sc['perpage'] + $i + 1 ) + 1;
			$html .= '<tr >';
			$html .= '	<td class="its-td-align center">'.$datarow['number'].'</td>';
			$html .= '	<td class="its-td-align center '.$usecolor.'" >'.$datarow['code_serialnumber'].' '.$shtml.'</td>';

			$html .= '</tr>';
			$i++;
		}//foreach end

		if($i==0){
			if($sc['search_text']){
				$html .= '<tr ><td class="its-td-align center" colspan="7" >"'.$sc['search_text'].'"로(으로) 검색된 인증번호가 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" colspan="7" >인증번호가 없습니다.</td></tr>';
			}
		}
		if(!empty($html)) {
			$result = array( 'content'=>$html, 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"", 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		echo json_encode($result);
		exit;
	}

	//프로모션코드이미지등록하기
	public function upload_file()
	{
		$this->load->helper('board');//

		$folder = "data/tmp/";

		foreach($_FILES as $key => $value)
		{
			$tmpname	= $value['tmp_name'];
			$file_ext		= end(explode('.', $value['name']));//확장자추출
			$file_name	= 'promotion_app_'.str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
			$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
			$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
			$saveFile		= $folder.$file_name;
			$config['allowed_types'] = 'jpg|gif|png';
			$tmp = @getimagesize($value['tmp_name']);
			if(!$tmp['mime']){
				$_FILES['Filedata']['type'] = $file_ext;//확장자추출
			}else{
				$_FILES['Filedata']['type'] = $tmp['mime'];
			}

			$fileresult = board_upload($key, $file_name, $folder, $config, $saveFile, 0, 'promotion');//status  error, fileInfo
			if(!$fileresult['status']){
				$error = array('status' => 0,'msg' => $fileresult['error'],'desc' => '업로드 실패');
				echo "[".json_encode($error)."]";
				exit;
			}
		}
		$result = array('status' => 1,'saveFile' => "/".$saveFile,'file_name' => $file_name);
		echo "[".json_encode($result)."]";
		exit;
	}

	// 엑셀등록하기
	public function upload_excelfile()
	{
		$this->load->helper('board');//

		$folder = "data/tmp/";

		foreach($_FILES as $key => $value)
		{
			$tmpname	= $value['tmp_name'];
			$file_ext		= end(explode('.', $value['name']));//확장자추출
			$file_name	= 'promotion_code_excel_'.str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
			$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
			$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
			$saveFile		= $folder.$file_name;
			$config['allowed_types'] = 'xls';
			$tmp = @getimagesize($value['tmp_name']);
			if(!$tmp['mime']){
				$_FILES['Filedata']['type'] = $file_ext;//확장자추출
			}else{
				$_FILES['Filedata']['type'] = $tmp['mime'];
			}

			$fileresult = board_upload($key, $file_name, $folder, $config, $saveFile, 0, 'promotion');//status  error, fileInfo
			if(!$fileresult['status']){
				$error = array('status' => 0,'msg' => $fileresult['error'],'desc' => '업로드 실패');
				echo "[".json_encode($error)."]";
				exit;
			}
		}
		$readdata['filename']= $file_name;
		$readdata['savedir']= ROOTPATH.'/'.$folder;
		$realfilename = $readdata['savedir'].$readdata['filename'];
		$data = $this->_excelreader_promotion($realfilename);
		if($data['result']){
			$result = array('status' => 1,'saveFile' => "/".$saveFile,'file_name' => $file_name);
		}else{
			$result = array('status' => 0,'msg'=>'잘못된 엑셀입니다.','desc' => '잘못된 엑셀양식');
		}
		echo "[".json_encode($result)."]";
		exit;
	}

	// 엑셀등록하기 > query
	function promotion_excel_save()
	{
		$no = (int) $_POST['no'];
		$promotions 		= $this->promotionmodel->get_promotion($no);
		$this->template->assign(array('promotions'=>$promotions));

		$sc['perpage']			= 100;//
		$sc['page']				= (!empty($_POST['page'])) ?		intval($_POST['page']):2;

		$realfilename			= ROOTPATH.'/data/tmp/'.$_POST['file_name'];
		$firstnum					= ($_POST['page'] != 2) ? (($sc['page']-2)*$sc['perpage'])+2:2;
		$nextnum					= intval(($sc['page'] - 1) * $sc['perpage'])+1;
		$data = $this->_excelreader_promotion($realfilename, $firstnum, $nextnum);//, $firstnum=2, $nextnum= 1002

		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount =  get_page_count($sc, $data['count']);
		### PAGE & DATA
		$sc['total_page']		= @ceil($data['count']/ $sc['perpage']);
		$sc['totalcount']		=  $data['count'];
		$nextpage = ( $nextnum > $sc['totalcount'] ) ? 0:($sc['page']+1);
		$nowpage = ($sc['page']-1);

		$i = $failcount = $succescount = 0;
		$html = '';
		foreach($data['loop'] as $code_serialnumber){
			$sc['whereis'] = ' and code_serialnumber = "'.$code_serialnumber.'" ';
			$promotion_code = $this->promotionmodel->get_promotioncode_input_total_count($sc);

			if($promotion_code){
				$failcount++;
				$class		= " class='bg-gray' ";
				$success	= '중복번호';
			}else{
				unset($paramcodeinput);$succescount++;
				$paramcodeinput["code_number"]			= mt_rand();//생성
				$paramcodeinput["promotion_seq"]				= $no;
				$paramcodeinput["regist_date"]				= date('Y-m-d H:i:s',time());
				$paramcodeinput["use_count"]					= 1;
				$paramcodeinput["code_serialnumber"] = $code_serialnumber;
				$this->db->insert('fm_promotion_code_input', $paramcodeinput);
				$class = " ";
				$success	= '정상등록';
			}

			$number = $data['count'] - ( ( $sc['page'] - 2 ) * $sc['perpage'] + $i + 1 ) + 1;

			$html .= '<tr  '.$class.' >';
			$html .= '	<td class="its-td-align center">'.$number.'</td>';
			$html .= '	<td class="its-td-align center">'.$code_serialnumber.'</td>';
			$html .= '	<td class="its-td-align center">'.$success.'</td>';
			$html .= '</tr>';
			$i++;
		}//foreach end

		if($i==0){
			$html .= '<tr ><td class="its-td-align center" colspan="7" >등록자료가 없습니다.</td></tr>';
		}

		$result = array( 'content'=>$html, 'totalcount'=>$sc['totalcount'], 'nowpage'=>$nowpage, 'total_page'=>$sc['total_page'], 'page'=>$sc['page'], 'nextpage'=>$nextpage, 'pagecount'=>(int)$pagecount,'firstnum'=>$firstnum,  'nextnum'=>$nextnum);
		echo json_encode($result);
		exit;
	}

	/**
	* 엑셀 파일읽어오기
	* firstnum
	* nextnum
	**/
	function _excelreader_promotion($realfilename, $firstnum=2, $nextnum = NULL)
	{
		$this->load->library('pxl');
		$this->objPHPExcel = new PHPExcel();
		if(is_file($realfilename)){
		 try {
				// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
				$objReader = IOFactory::createReaderForFile($realfilename);
				// 읽기전용으로 설정
				$objReader->setReadDataOnly(true);
				// 엑셀파일을 읽는다
				$objExcel = $objReader->load($realfilename);

				// 첫번째 시트를 선택
				$objExcel->setActiveSheetIndex(0);
				$objWorksheet = $objExcel->getActiveSheet();

				$maxRow = $objWorksheet->getHighestRow();
				if($nextnum && $nextnum <= $maxRow ){
					$maxRow = $nextnum;
				}

				$maxCol = $objWorksheet->getHighestColumn();
				unset($loop);
				$totalcnt = 0;
				for ($i = $firstnum ; $i <= $maxRow ; $i++) { // 기본 두번째 행부터 읽는다 첫번째 타이틀임
					$promotion = $objWorksheet->getCell('A' . $i)->getValue(); // 첫번째 열 getCell('A' . $i) 두번째:getCell('B' . $i) 세번째:getCell('C' . $i)
					if($promotion){
						$totalcnt++;
						$loop[] = $promotion;
					}
				}//endfor
				if($totalcnt) {
					$data['result']		= true;
					$data['count']		= ($objWorksheet->getHighestRow()-1);
					$data['loop']		= $loop;
				}else{
					$data['result']		= false;
					$data['count']		= ($objWorksheet->getHighestRow()-1);
					$data['msg']			= '엑셀파일의 데이타가 없습니다.';
				}
			} catch (exception $e) {
				$data['result']		= false;
				$data['count']	= 0;
				$data['msg']			= '엑셀파일을 읽는도중 오류가 발생하였습니다.';
			}
		}else{
			$data['result']		= false;
			$data['count']	= 0;
			$data['msg']			= '엑셀파일이 없습니다.';
		}
		return $data;
	}

	//프로모션코드 >  엑셀다운
	public function promotion_code_exceldown()
	{
		$no = (int) $_GET['no'];
		$promotions 	= $this->promotionmodel->get_promotion($no);

		if($promotions['promotion_type'] == 'file'){//엑셀등록인 경우
			$totalcount = $this->promotionmodel->get_promotioncode_input_total_count($no);
		}else{
			$totalcount = $this->promotionmodel->get_promotioncode_item_total_count($no);
		}

		### SEARCH
		$sc = $_GET;
		$sc['perpage']		= $totalcount;
		$sc['page']			= 0;

		if($promotions['promotion_type'] == 'file'){//랜덤등록과 엑셀등록인 경우
			$writedata = $this->promotionmodel->promotioncode_input_list($sc);
		}else{
			$writedata = $this->promotionmodel->promotioncode_list($sc);
		}

		$writedata['filename']= $promotions['promotion_name'].'promotioncode_down_'.str_replace(" ", "", (substr(microtime(), 2, 6))).'.xls';//
		$writedata['savedir']= ROOTPATH.'/data/tmp/';
		$writedata['saveurl']= '/data/tmp/';
		$writedata['exceltype']= 'Excel5';//'Excel2007';
		$this->_exceldownload($writedata);
	}

	//엑셀로 다운로드받기
	function _exceldownload($writedata)
	{
		$this->load->library('pxl');
		$filename = $writedata['savedir'].$writedata['filename'];
		$this->objPHPExcel = new PHPExcel();
		// Assign cell values
		//Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$this->objPHPExcel->setActiveSheetIndex(0);
		$this->objPHPExcel->getActiveSheet()->setCellValue("A1", "프로모션코드");
		$i=2;
		foreach ($writedata["result"] as $k=>$v)
		{
			$this->objPHPExcel->getActiveSheet()->setCellValue("A".$i, $v['code_serialnumber']);
			$i++;
		}
		$this->objPHPExcel->getActiveSheet()->setTitle("프로모션코드");
		$objWriter = IOFactory::createWriter($this->objPHPExcel, $writedata['exceltype']);
		if( $writedata['exceltype'] == 'Excel2007' ) {
			header("Content-charset=utf-8");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.urlencode($writedata['filename']).'"');//한글명이 있는경우를 위해 urlencode($writedata['filename'])
			header('Cache-Control: max-age=0');
		}else{
			header("Content-charset=utf-8");
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header('Content-Disposition: attachment;filename="'.urlencode($writedata['filename']).'"');
			header("Content-Transfer-Encoding: binary ");
		}
		$objWriter->save('php://output');
	}

	//엑셀로 웹상폴더에 저장하기
	function _excelsave($writedata)
	{
		$this->load->library('pxl');
		$this->objPHPExcel = new PHPExcel();
		$filename			= $writedata['filename'];
		$savedir			= $writedata['savedir'];
		$exceltype		= $writedata['exceltype'];
		// Assign cell values
		$this->objPHPExcel->setActiveSheetIndex(0);
		$this->objPHPExcel->getActiveSheet()->setCellValue("A1", "프로모션코드");
		$i=2;
		foreach ($writedata["result"] as $k=>$v)
		{
			$this->objPHPExcel->getActiveSheet()->setCellValue("A".$i, $v['code_serialnumber']);
			$i++;
		}
		$objWriter = IOFactory::createWriter($this->objPHPExcel, $exceltype);
		$objWriter->save($savedir.$filename);
	}

	//수동생성 > 인증코드입력시
	public function offlinepromotion_ck()
	{
		if(empty($_POST['promotion_input_num'])) {
			echo '';exit;
		}

		// offline프로모션코드 인증번호 체크
		$sc['whereis'] = ' and code_serialnumber = "'.$_POST['promotion_input_num'].'" ';
		$offlienresult = $this->promotionmodel->get_promotioncode_total_count($sc);
		if(!$offlienresult){
			$offlienresult = $this->promotionmodel->get_promotioncode_input_total_count($sc);
		}
		echo !$offlienresult ? 'true' : 'false';
		exit;
	}

	//관리자 > 프로모션코드발급하기
	public function download_write()
	{
		$this->load->model('membermodel');

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('promotion_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$promotionSeq = (int) $_POST['no'];

		unset($memberArr);
		if( $_POST['target_type'] == 'all' ) {
			$key = get_shop_key();
			$query = $this->db->query("select member_seq, userid,  AES_DECRYPT(UNHEX(email), '{$key}') as email from fm_member where status != 'withdrawal'");
			$memberArr = $query->result_array();
		}

		elseif( $_POST['target_type'] == 'group' ) {
			$memberGrouparr = implode("','",$_POST['memberGroups']);
			$key = get_shop_key();
			$whereis = " and group_seq in ('".$memberGrouparr."') ";//group_seq
			$query = $this->db->query("select member_seq, userid, AES_DECRYPT(UNHEX(email), '{$key}') as email from fm_member where status != 'withdrawal'".$whereis);
			$memberArr = $query->result_array();
		}

		elseif($_POST['target_type'] == 'member') {
			$target_member_ar = explode("],[",$_POST['target_member']);
			foreach($target_member_ar as $target_memberseq) {
				if(empty($target_memberseq))continue;
				$target_memberseq = str_replace("],","",str_replace("[","",$target_memberseq));
				$target_member['member_seq'] = $target_memberseq;
				$mdata = $this->membermodel->get_member_data($target_memberseq);
				$target_member['email'] = $mdata['email'];
				$memberArr[] = $target_member;
			}
		}
		$promotions 	= $this->promotionmodel->get_promotion($promotionSeq);
		$downloadcnt = 0;
		if(is_array($memberArr) ) {
			//최종 관리자 > 프로모션코드직접발급하기
			foreach($memberArr as $k){
				if(empty($k['member_seq']))continue;
				// 발급프로모션코드 정보 확인
				$downpromotions = $this->promotionmodel->get_admin_download($k['member_seq'], $promotionSeq);
				if(!$downpromotions){
					if( $promotions['promotion_type'] == 'random') {//자동생성 >  -> 발급시자동생성 4-4-4-4
						$paramoffline["code_serialnumber"]		= strtoupper(substr(md5( uniqid('') ), 0, 4)).'-'.strtoupper(substr(md5( uniqid('') ), 0, 4)).'-'.strtoupper(substr(md5( uniqid('') ), 0, 4)).'-'.strtoupper(substr(md5( uniqid('') ), 0, 4));//영문+숫자
					}elseif( $promotions['promotion_type'] == 'file' ) {//수동생성2 > 파일
						$inputsc['whereis'] = ' and down_use = 0 ';
						$promotioninput = $this->promotionmodel->get_promotioncode_input_item($promotionSeq, $inputsc);
						if($promotioninput['code_serialnumber']){
							$paramoffline["code_serialnumber"] = $promotioninput['code_serialnumber'];
						}
					}
					if($paramoffline["code_serialnumber"]) {
						if( $this->promotionmodel->_admin_downlod( $promotionSeq, $k['member_seq'], $paramoffline["code_serialnumber"]) ) {

							$downloadcnt++;
							if( $promotions['promotion_type'] == 'random') {//자동생성 > 발급시자동생성 4-4-4-4
								$paramoffline["code_number"]		= mt_rand();//생성
								$paramoffline["promotion_seq"]		= $promotionSeq;
								$paramoffline["regist_date"]			= date("Y-m-d H:i:s");
								$paramoffline["use_count"]				= 1;
								$this->db->insert('fm_promotion_code', $paramoffline);
							}elseif( $promotions['promotion_type'] == 'file' ) {//수동생성2 > 파일
								$this->promotionmodel->set_promotioncode_down_use($paramoffline["code_serialnumber"]);
							}

							if( $k['email'] ) {
								/**
								$data['email']= $k['email'];
								$data['title']	= '프로모션코드가 발급되었습니다.';
								$data['contents']	= '프로모션코드가 발급되었습니다. <br/>프로모션코드 : '.$paramoffline["code_serialnumber"].'';
								getSendMail($data);
								**/
								if($promotions["sale_type"] == 'shipping_free'){
									$promotionsale = "기본배송비 무료 (최대 " .number_format($promotions["max_percent_shipping_sale"])."원)";
								}else if($promotions["sale_type"] =='shipping_won'){
									$promotionsale = number_format($promotions["won_shipping_sale"]);
								}else if($promotions["sale_type"] =='won'){
									$promotionsale = number_format($promotions["won_goods_sale"])."원 할인";
								}else{
									$promotionsale = number_format($promotions["percent_goods_sale"])."% 할인";
								}
								if ($promotions['issue_priod_type'] == 'day') {
									$promotionlimitdate = ($promotions['after_issue_day']>0) ? '다운로드 후 '.$promotions['after_issue_day'].'일 간 사용가능':'다운로드 후 오늘까지만 사용가능';
								}else{
									$promotionlimitdate = substr($promotions['issue_enddate'], 5,2).'월 '. substr($promotions['issue_enddate'],8,2).'일 까지 사용가능';
								}

								$emailparams['promotioncode']		= $paramoffline["code_serialnumber"];
								$emailparams['promotionsale']		= $promotionsale;
								$emailparams['promotionlimitdate']	= $promotionlimitdate;
								$emailparams['member_seq']			= $k['member_seq'];
								sendMail($k['email'], 'promotion', $k['userid'] , $emailparams);
							}
						}
					}
				}
			}
		}else{
			openDialogAlert("발급대상을 선택해 주세요.",400,140,'parent','');
			exit;
		}
		if($downloadcnt > 0) {
			$callback = "parent.document.location.reload();";
			openDialogAlert(($downloadcnt)."건의 프로모션코드가 발급되었습니다.",400,140,'parent',$callback);
		}else{
			openDialogAlert("프로모션코드발급 실패하였습니다.",400,140,'parent',$callback);
		}
		exit;
	}

	//프로모션코드복사
	public function promotion_copy()
	{

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('promotion_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$promotions 	= $this->promotionmodel->get_promotion($_POST['copy_promotion_seq']);
		if($promotions) {
			$nokey = array('promotion_seq', 'promotion_name', 'promotion_desc', 'promotion_image4', 'regist_date','update_date');
			foreach($promotions as $key=>$val) {
				if(in_array($key,$nokey)) continue;
				$parampromotion[$key] = $val;
			}

			$parampromotion['promotion_name']	= $_POST['promotion_name'];
			$parampromotion['promotion_desc']	= $_POST['promotion_desc'];
			$parampromotion['regist_date']		= date("Y-m-d H:i:s");
			$parampromotion['update_date']	= $parampromotion['regist_date'];
			$result =$this->db->insert('fm_promotion', $parampromotion);
			$new_promotion_seq = $this->db->insert_id();

			if($result){
				if(@is_file($this->promotionmodel->promotionupload_dir.$promotions['promotion_image4'])) {
					@copy($this->promotionmodel->promotionupload_dir.$promotions['promotion_image4'],$this->promotionmodel->promotionupload_dir.$new_promotion_seq.'_'.$promotions['promotion_image4']);//파일복사
					$promotion_image4 = $new_promotion_seq.'_'.$promotions['promotion_image4'];
					$this->db->where('promotion_seq', $new_promotion_seq);
					$this->db->update('fm_promotion', array('promotion_image4' => $promotion_image4));
				}

				$promotionBrands 	= $this->promotionmodel->get_promotion_issuebrand($_POST['copy_promotion_seq']);
				$promotioncategorys 	= $this->promotionmodel->get_promotion_issuecategory($_POST['copy_promotion_seq']);
				$promotionGoods 	= $this->promotionmodel->get_promotion_issuegoods($_POST['copy_promotion_seq']);

				$paramBrand = array();
				if($promotionBrands){
					foreach($promotionBrands as $key => $brand){
						$paramBrand['promotion_seq']	= $new_promotion_seq;
						$paramBrand['brand_code']		= $brand['brand_code'];
						$this->db->insert('fm_promotion_brand', $paramBrand);
					}
				}

				$paramGoods = array();
				if($promotionGoods){
					foreach($promotionGoods as $key => $Goods){
						$paramGoods['promotion_seq']	= $new_promotion_seq;
						$paramGoods['goods_seq']			= $Goods['goods_seq'];
						$paramGoods['type']					= $Goods['type'];
						$this->db->insert('fm_promotion_issuegoods', $paramGoods);
					}
				}

				$paramCategory = array();
				if($promotioncategorys){
					foreach($promotioncategorys as $key => $Categorys){
						$paramCategory['promotion_seq']	= $new_promotion_seq;
						$paramCategory['category_code']	= $Categorys['category_code'];
						$paramCategory['type']					= $Categorys['type'];
						$this->db->insert('fm_promotion_issuecategory', $paramCategory);
					}
				}

				$callback = "parent.document.location.reload();";
				openDialogAlert("프로모션코드를 복사하였습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert("프로모션코드복사가 실패하였습니다.",400,140,'parent','');
			}
		}else{
			openDialogAlert("잘못된 접근입니다.",400,140,'parent','');
		}
	}


	//프로모션코드발급 > sql 회원검색 전체인경우
	public function download_member_search_all()
	{

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('promotion_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->load->model('membermodel');
		### SEARCH
		$sc = $_POST;
		$sc['search_text']		= ($sc['search_text'] == '이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소') ? '':$sc['search_text'];
		$sc['orderby']		= 'A.member_seq';
		### MEMBER
		$i=0;
		$data = $this->membermodel->popup_member_list($sc);
		foreach($data['result'] as $datarow){
			$download_promotions = $this->promotionmodel->get_admin_download($datarow['member_seq'], $_POST['no']);
			if(!$download_promotions) {
				$searchallmember[$i]['user_name'] = $datarow['user_name'];
				$searchallmember[$i]['userid']			 = $datarow['userid'];
				$searchallmember[$i]['member_seq']			 = $datarow['member_seq'];
				$i++;
			}
		}

		$result = array('searchallmember'=>$searchallmember,'totalcnt'=>$i);
		echo json_encode($result);
		exit;
	}

	//프로모션코드발급하기 > 발급대상찾기시 다운로드 권한 보여주기
	public function download_promotion_info()
	{
		$promotions 	= $this->promotionmodel->get_promotion($_POST['promotionSeq']);
		$download_limitlay = $download_groupsMsglay = $download_datelay = '';

		if($promotions) {
			/* 회원 그룹 개발시 변경*/
			$groups = "";
			$query = $this->db->query("select group_seq,group_name from fm_member_group");
			foreach($query->result_array() as $row){
				$groups[] = $row;
			}
			/******************/
			$dsc['whereis'] = ' and promotion_seq='.$promotions['promotion_seq'];
			$downloadtotal = $this->promotionmodel->get_download_total_count($dsc);
			$downloadtotal	= number_format($downloadtotal);//발급수

			$download_limitlay = ($promotions['download_limit'] == 'limit')? '현재 '.$downloadtotal.'건 / 누적 '.$promotions['download_limit_ea'].'<input type="hidden" name="downloadtotal" id="downloadtotal" value="'.$downloadtotal.'"><input type="hidden" name="download_limit_ea" id="download_limit_ea" value="'.$promotions['download_limit_ea'].'">':'제한없음';
			$download_limitlay .= '<input type="hidden" name="download_limit" id="download_limit" value="'.$promotions['download_limit'].'">';

			$this->load->model('membermodel');
			$downloadmbtotalcountlay = '전체회원 '.number_format($this->membermodel->get_item_total_count()).'명 <input type="hidden" name="member_total_count" id="member_total_count" value="'.$this->membermodel->get_item_total_count().'">';
			$download_groupsMsglay = '다운로드 권한 제한은 없습니다.';


			$promotions['date']			= ($promotions['update_date'])?substr($promotions['update_date'],2,14):substr($promotions['regist_date'],2,14);//등록일
			$promotions['limit_goods_price'] = number_format($promotions['limit_goods_price']);

			if($promotions['type'] == 'birthday' || $promotions['type'] == 'memberGroup' || $promotions['type'] == 'member' ){//직접발급시
				$promotions['issuedate']	= '발급일로부터 '.number_format($promotions['after_issue_day']).'일';
			}else{
				if( $promotions['issue_priod_type'] == 'date' ) {
					$promotions['issuedate']	= substr($promotions['issue_startdate'],2,10).' ~ '.substr($promotions['issue_enddate'],2,10);
				}else{
					$promotions['issuedate']	= '발급일로부터 '.number_format($promotions['after_issue_day']).'일';
				}
			}

			if($promotions['type'] == 'shipping' ){//배송비
				$promotions['salepricetitle']	= ($promotions['sale_type'] == 'free' ) ? '무료, 최대 '.number_format($promotions['max_percent_shipping_sale']).'원': '배송비 '.number_format($promotions['won_shipping_sale']).'원';//
			}elseif($promotions['type'] == 'promotion_point' ){//오프라인 포인트프로모션코드
				$promotions['salepricetitle']	='포인트 '.number_format($promotions['promotion_point']).'원 지급';
			}else{
				$promotions['salepricetitle']	= ($promotions['sale_type'] == 'percent' ) ? $promotions['percent_goods_sale'].'% 할인, 최대 '.number_format($promotions['max_percent_goods_sale']).'원': '판매가격의 '.number_format($promotions['won_goods_sale']).'원';
			}

			$dsc['whereis'] = ' and promotion_seq='.$promotions['promotion_seq'];
			$downloadtotal = $this->promotionmodel->get_download_total_count($dsc);
			$promotions['downloadtotal']	= number_format($downloadtotal);//발급수

			$usc['whereis'] = ' and promotion_seq='.$promotions['promotion_seq'].' and use_status = \'used\' ';
			$usetotal = $this->promotionmodel->get_download_total_count($usc);

			$promotions['usetotal']			= number_format($usetotal);//사용건수

			$promotions['issueimg'] = ( strstr($promotions['type'],'promotion') )?'promotion':'promotionnone';
			$promotions['issueimgalt'] = ( strstr($promotions['type'],'promotion') )?'일반코드':'개별코드';

			if($promotions['type'] == 'admin' ){//직접발급시
				$promotions['issuebtn']	= (( $promotions['issue_priod_type'] == 'date' && str_replace("-","", substr($promotions['issue_enddate'],0,10)) < date("Ymd"))) ? '':'<span class="btn small cyanblue"><button type="button" class="downloa_write_btn" promotion_seq="'.$promotions['promotion_seq'].'" download_limit="'.$promotions['download_limit'].'" promotion_name="'.$promotions['promotion_name'].'" >발급하기</button></span>';
			}else{
				$promotions['issuebtn']	= $this->promotionmodel->promotionTypeTitle[$promotions['type']];
			}

			if( $promotions['promotion_type'] == 'random') {//자동생성 >  -> 발급시자동생성 4-4-4-4
				$codesc['whereis'] = ' and promotion_seq = "'.$promotions['promotion_seq'].'" ';
				$promotioncodetotal = $this->promotionmodel->get_promotioncode_total_count($codesc);
				$downusecountlay = '총 '.number_format($promotioncodetotal).'건 ';
			}elseif( $promotions['promotion_type'] == 'file' ) {//수동생성2 > 파일
				$inputsc['whereis'] = ' and promotion_seq = "'.$promotions['promotion_seq'].'" ';
				$promotioncodetotal = $this->promotionmodel->get_promotioncode_input_total_count($inputsc);
				$inputsc['whereis'] = ' and promotion_seq = "'.$promotions['promotion_seq'].'" and down_use = 1 ';
				$promotioncodedownuse = $this->promotionmodel->get_promotioncode_input_total_count($inputsc);
				if($promotioncodedownuse){
					$downusecountlay = '총 '.number_format($promotioncodetotal).'건중에서 '.number_format($promotioncodedownuse).'건 발급';
				}else{
					$downusecountlay = '총 '.number_format($promotioncodetotal).'건 ';
				}
			}

			$result = array('downloadmbtotalcountlay'=>$downloadmbtotalcountlay,'download_limitlay'=>$download_limitlay,'download_groupsMsglay'=>$download_groupsMsglay,'download_datelay'=>$download_datelay,'downusecountlay'=>$downusecountlay,'promotion'=>$promotions);
			echo json_encode($result);
			exit;
		}
	}

	//발급(인증)받은프로모션코드삭제하기
	public function download_delete()
	{
		$delseqar = @explode(",",$_POST['delseqar']);
		$delnum = 0;
		for($i=0;$i<sizeof($delseqar);$i++){ if(empty($delseqar[$i]))continue;
			$download_seq = $delseqar[$i];
			$download_promotions 	= $this->promotionmodel->get_download_promotion($download_seq);
			if($download_promotions['use_status'] != 'used') {//미사용만 삭제함
				$this->db->delete('fm_download_promotion_issuecategory', array('download_seq' => $download_seq));
				$this->db->delete('fm_download_promotion_issuegoods', array('download_seq' => $download_seq));
				$this->db->delete('fm_download_promotion_issuebrand', array('download_seq' => $download_seq));
				$result = $this->db->delete('fm_download_promotion', array('download_seq' => $download_seq));
				if($result) {
					$downloadcnt++;
				}
			}
		}

		if($downloadcnt > 0) {
			$result = array( 'result'=>true, 'downloadcnt'=>$downloadcnt,'msg'=>"선택된 ".$downloadcnt."건의 회원들에게 지급된 해당 프로모션코드을<br/>정상적으로 삭제하였습니다.");
		}else{
			$result = array( 'result'=>false,'msg'=>"발급(인증)프로모션코드삭제를 실패하였습니다.");
		}

		echo json_encode($result);
		exit;
	}

}

/* End of file promotion_process.php */
/* Location: ./app/controllers/admin/category.php */
