<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class Board_process extends front_base {

	public function __construct() {
		parent::__construct();

		$this->load->model('ssl');
		$this->ssl->decode();

		$boardid = (!empty($_POST['board_id'])) ? $_POST['board_id']:$_GET['board_id'];
		define('BOARDID',$boardid);

		$this->load->library('validation');
		$this->load->library('upload');
		$this->load->helper('download');
		$this->load->helper('board');//
		$this->load->model('videofiles');

		$this->load->model('Boardmanager');
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$this->load->model('Boardscorelog');

		if( BOARDID == 'goods_qna' ) {
			$this->load->model('Goodsqna','Boardmodel');
		}elseif( BOARDID == 'goods_review' ) {
			$this->load->model('Goodsreview','Boardmodel');
		}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
			$this->load->model('Boardbulkorder','Boardmodel');
		}else{
			$this->load->model('Boardmodel');
		}
		$this->load->model('Boardindex');
		$this->load->model('Boardcomment');
	}

	/* 기본 */
	public function index()
	{ 
		$sc['whereis']	= ' and id= "'.BOARDID.'" ';
		$sc['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sc);//게시판정보  
		if (!isset($this->manager['id'])) {
			//$callback = "parent.document.location.href='".$this->Boardmanager->managerurl."';";
			openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','parent.submitck();');
			exit;
		}
		boarduploaddir($this->manager);//폴더생성 및 스킨 복사

		$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];
		/* 게시글등록 */
		if($mode == 'board_write') {

			get_auth($this->manager, '', 'write' , $isperm);//접근권한체크
			if ( $isperm['isperm_write'] === false ) {
				$callback = "parent.submitck();parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			// 상점리뷰 게시판시 필수값 조사 및 데이터 조합
			if( BOARDID == 'store_review' ){
				if(!$_POST['seq'])		$_POST['seq']		= $_POST['delseq'];
				if(!$_POST['contents'])	$_POST['contents']	= $_POST['reply_contents_'.$_POST['delseq']];
				if(!$_POST['name'])		$_POST['name']		= $_POST['name_'.$_POST['delseq']];
				if(!$_POST['pw'])		$_POST['pw']		= $_POST['pw_'.$_POST['delseq']];
				if(!$this->userInfo['member_seq'])	$this->validation->set_rules('pw', '비밀번호','trim|required|xss_clean');
			}

			// 예약게시판시 필수값 조사 및 데이터 조합
			if( BOARDID == 'store_reservation') {
				if(!$_POST['seq'])		$_POST['seq']		= $_POST['delseq'];
				if(!$_POST['contents'])	$_POST['contents']	= $_POST['reply_contents_'.$_POST['delseq']];
				if(!$_POST['name'])		$_POST['name']		= $_POST['name_'.$_POST['delseq']];
				if(!$_POST['pw'])		$_POST['pw']		= $_POST['pw_'.$_POST['delseq']];

				$this->validation->set_rules('phone_num2', '연락처','trim|required|xss_clean');
				$this->validation->set_rules('phone_num3', '연락처','trim|required|xss_clean');
				$this->validation->set_rules('reserve_date', '예약날짜','trim|required|xss_clean');
				if(!$this->userInfo['member_seq'])	$this->validation->set_rules('pw', '비밀번호','trim|required|xss_clean');

				$tmp_date_arr = explode('-', $_POST['reserve_date']);
				if(!checkdate($tmp_date_arr[1], $tmp_date_arr[2], $tmp_date_arr[0])) {
					openDialogAlert('예약 날짜 형식이 올바르지 않습니다.',400,140,'parent','parent.submitck();');
					exit;
				}

				$_POST['tel1'] = $_POST['phone_num1'] . "-" . $_POST['phone_num2'] . "-" . $_POST['phone_num3'];
				$_POST['reserve_date'] = $_POST['reserve_date'] . " " . str_pad($_POST['reserve_time_h'], 2, "0", STR_PAD_LEFT) . ":" . str_pad($_POST['reserve_time_m'], 2, "0", STR_PAD_LEFT);
			}

			if( $_POST['name'] == '작성자를 입력해 주세요' ) {
				$_POST['name'] = '';
			}

			if( strstr($_POST['pw'],'비밀번호를 입력해 주세요') ) {
				$_POST['pw'] = '';
				if (!defined('__ISUSER__'))
				{
					openDialogAlert("비밀번호를 정확히 입력해 주세요.",400,140,'parent','parent.submitck();');
					exit;
				}
			}


			if( $_POST['subject'] == '제목을 입력해 주세요' ) {
				$_POST['subject'] = '';
			}

			if( strtolower($_POST['contents']) == "<p>&nbsp;</p>" || strtolower($_POST['contents']) == "<p><br></p>"  ) $_POST['contents']='';

			$this->validation->set_rules('name', '이름','trim|required|xss_clean');
			$this->validation->set_rules('subject', '제목','trim|required|xss_clean');
			$this->validation->set_rules('contents', '내용','trim|required');

			if( BOARDID == 'bulkorder' || BOARDID == 'goods_review' ) {
				$label_pr = $_POST['label'];
				$label_sub_pr = $_POST['labelsub'];
				$label_required = $_POST['required'];

				### //넘어온 추가항목 seq
				foreach($label_pr as $l => $data){$label_arr[]=$l;}
				//추가항목 공백체크
				foreach($label_required as $v){
					if(!in_array($v,$label_arr)){
						$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();parent.submitck();";
						openDialogAlert('체크된 항목은 필수항목입니다.',400,140,'parent',$callback);
						exit;
					}else{
						$query = $this->db->get_where('fm_boardform',array('bulkorderform_seq'=> $v,'boardid'=>BOARDID));
						$form_result = $query -> row_array();
						$label_title = $form_result['label_title'];
						$this->validation->set_rules('label['.$v.'][value][]', $label_title,'trim|required|xss_clean');
					}
				}
				###
			}

			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();parent.submitck();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}

			//스팸방지 // 단 매장용의경우 제외
			if($this->manager['autowrite_use'] == 'Y'  && !defined('__ISUSER__') && !( $this->mobileMode || $this->_is_mobile_agent) && BOARDID != 'store_review' && BOARDID != 'store_reservation') {

				$this->load->model('Captchamodel');

				// First, delete old captchas
				$expiration = time()-7200; // Two hour limit
				$this->Captchamodel->data_delete($expiration);

				// Then see if a captcha exists:
				$params['captcha_code'] = (!empty($_POST['captcha_code'])) ? $_POST['captcha_code']:'';
				$params['ip_address'] = $this->input->ip_address();
				$params['expiration'] = $expiration;
				$captchacnt = $this->Captchamodel->data_query($params);

				if ($captchacnt == 0)
				{
					openDialogAlert("스팸방지코드를 정확히 입력해 주세요.",400,140,'parent','parent.submitck();');
					exit;
				}
			}

			$params['boardid']			=  BOARDID;
			$params['notice']				=  if_empty($_POST, 'notice', '0');//공지
			if( $_POST['notice']){
				$params['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
			}else{
				$params['onlynotice']			=	0;//공지영역만 노출여부
			}

			if( $this->manager['secret_use'] == "A" ) {//무조건비밀글
				$params['hidden']		= 1;//비밀글
			}else{
				$params['hidden']		= if_empty($_POST, 'hidden', '0');//비밀글
			}

			$params['subject']		=  $_POST['subject'];
			$params['editor']			=  ($_POST['daumedit'])?1:0;//모바일
			$params['name']			=  if_empty($_POST, 'name', '');
			$params['category']		=  (!empty($_POST['category']))?htmlspecialchars($_POST['category']):'';
			$params['contents']		=  $_POST['contents'];

			$pw							=  (!empty($_POST['pw']))?md5($_POST['pw']):'';
			$params['pw']			=  (!empty($_POST['oldpw']))?($_POST['oldpw']):$pw;

			$params['email']		=  (!empty($_POST['email']))?($_POST['email']):'';
			$params['tel1']			=  (!empty($_POST['tel1']))?($_POST['tel1']):'';
			$params['tel2']			=  (!empty($_POST['tel2']))?($_POST['tel2']):'';

			$params['rsms']			=  if_empty($_POST, 'board_sms', 'N');//수신여부
			$params['remail']			=  if_empty($_POST, 'board_email', 'N');//수신여부

			$params['score_avg']	=  $_POST['score_avg'];

			if( !empty($_POST['score']) ) $params['score']  = ($_POST['score']);//값이 잇는경우에만 변경

			//상품문의/후기
			$params['goods_seq']				=  (isset($_POST['displayGoods']) && is_array($_POST['displayGoods']))?implode(",",$_POST['displayGoods']):'';

			if($_POST['displayGoods']) {//상품관련
				$this->load->model('goodsmodel');
				foreach($_POST['displayGoods'] as $displayGoods){
					$goods = $this->goodsmodel->get_goods($displayGoods);
					$providerseq[] = $goods['provider_seq'];
				}
				$params['provider_seq']				=  (isset($providerseq) && is_array($providerseq))?implode(",",$providerseq):'';
			}else{
				if( BOARDID == 'bulkorder' ) {//대량구매게시판
					$params['provider_seq'] = $this->userInfo['member_seq'];
				}
			}

			$params['goods_cont']				=  (isset($_POST['displayGoods_cont']) && is_array($_POST['displayGoods_cont']))?implode("^|^",$_POST['displayGoods_cont']):'';

			//회원정보
			if( defined('__ISUSER__') === true ) {
				$this->load->model('membermodel');
				$this->minfo = $this->membermodel->get_member_data($this->userInfo['member_seq']);
				$params['mseq']		= $this->userInfo['member_seq'];
				$params['mid']			= $this->userInfo['userid'];
				if (!$_POST['seq']) $params['pw']			= $this->minfo['password'];//답변이 아닌경우에만 본인의 비밀글로 처리됨
			}

			if (!empty($_POST['seq']) ) {//답변시

				$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
				$parentsql['select']		= '  seq, gid, comment, upload, depth ';
				$parentdata = $this->Boardmodel->get_data($parentsql);
				if(empty($parentdata)) {
					$callback = "parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';parent.submitck();";
					openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
					exit;
				}

				$parentsql['whereis']	= ' and gid >= '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1);
				//$parentsql['select']		= ' gid ';
				$parentrumrow = $this->Boardindex->get_data_numrow($parentsql);
				if($parentrumrow>98) {
					openDialogAlert("죄송합니다. 더이상 답글을 달 수 없습니다.",400,140,'parent','parent.submitck();');
					exit;
				}

				$gidup['set']				= ' gid=gid+0.01 ';
				$gidup['whereis']		= ' gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1);
				$this->Boardmodel->data_gid_save($gidup);//data gid update
				$this->Boardindex->data_gid_save($gidup);//idx gid update

				$params['parent']	= $_POST['seq'];
				$params['gid']			= $parentdata['gid']+0.01;
				$params['depth']		= $parentdata['depth']+1;

			}else{//새글
				$minsql['whereis']	= ' ';
				$minsql['select']		= ' min(gid) as mingid ';
				$mindata = $this->Boardmodel->get_data($minsql);
				$parentgid = $mindata['mingid'] ? $mindata['mingid']-1 : 100000000.00;
				$params['parent']	= 0;
				$params['gid']			= $parentgid;
				$params['depth']		= 0;
			}

			$params['r_date']		= date("Y-m-d H:i:s");
			$params['m_date']		= date("Y-m-d H:i:s");
			$params["ip"]				= $this->input->ip_address();
			$params["agent"]		= $_SERVER['HTTP_USER_AGENT'];

			$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';
			$params['contents'] = adjustEditorImages($params['contents'], $this->Boardmodel->upload_src);// /data/tmp 임시폴더변경 /data/editor

			board_mobile_file($parentdata, $realfilename, $incimage);//첨부파일처리

			if(isset($realfilename)){
				$params['upload'] = @implode("|",$realfilename);
			}else{
				$params['upload'] = '';//초기화
			}

			if(  !$params['editor'] || ( $this->mobileMode || $this->storemobileMode ) || $this->_is_mobile_agent ){//모바일인경우 text
				if ( $_POST['insert_image'] == 'top') {
					$params['contents'] = implode(" ",$incimage).'<br /><br />'.nl2br($params['contents']);
				}elseif ( $_POST['insert_image'] == 'bottom') {
					$params['contents'] = nl2br($params['contents']).'<br /><br />'.implode(" ",$incimage);
				}else{
					$params['contents'] = nl2br($params['contents']);
				}
			}
			$params['insert_image'] =  if_empty($_POST, 'insert_image', 'none');

			//신규분류
			if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
				$params['category'] = htmlspecialchars($_POST['newcategory']);
			}

			if( BOARDID == 'bulkorder' ) {
				$params['payment']				=  (!empty($_POST['payment']))?($_POST['payment']):'';
				$params['typereceipt']			=  (!empty($_POST['typereceipt']))?($_POST['typereceipt']):'';
				$params['total_price']			=  (!empty($_POST['total_price']))?($_POST['total_price']):'0';
				unset($addsetdata);
				### //추가정보 저장
				foreach ($label_pr as $k => $data) {
					foreach ($data['value'] as $j => $subdata) {
						if($k == '1' ){//
							$params['person_name']	 =  $subdata;
						}elseif($k == '2' ){//
							$params['person_email']	= $subdata;
							$params['email']				=  $params['person_email'];
						}elseif($k == '3' ){//
							$params['person_tel1']	= $subdata;
						}elseif($k == '4' ){//
							$params['person_tel2']	= $subdata;
							$params['tel1']				=  $params['person_tel2'];
						}elseif($k == '5' ){//
							$params['company']			= $subdata;
						}elseif($k == '6' ){//
							$params['shipping_date']	= $subdata;
						}
						$query = $this->db->get_where('fm_boardform',array('bulkorderform_seq'=> $k,'boardid'=>BOARDID));
						$form_result = $query -> row_array();
						$setdata = 'label_title='.$form_result['label_title'];
						$setdata .= '^^bulkorderform_seq='.$form_result['bulkorderform_seq'];
						$setdata .= '^^label_value='.$subdata;
						$setdata .= '^^label_sub_value='.$label_sub_pr[$k]['value'][$j];
						$addsetdata[] = $setdata;
					}
				}
				if($addsetdata) $params['adddata'] = @implode("|",$addsetdata);

			}elseif( BOARDID == 'goods_review' ) {//상품후기

				//평가정보
				unset($addsetdata);
				### //추가정보 저장
				foreach ($label_pr as $k => $data) {
					foreach ($data['value'] as $j => $subdata) {
						$query = $this->db->get_where('fm_boardform',array('bulkorderform_seq'=> $k,'boardid'=>BOARDID));
						$form_result = $query -> row_array();
						$setdata = 'label_title='.$form_result['label_title'];
						$setdata .= '^^bulkorderform_seq='.$form_result['bulkorderform_seq'];
						$setdata .= '^^label_value='.$subdata;
						$setdata .= '^^label_sub_value='.$label_sub_pr[$k]['value'][$j];
						$addsetdata[] = $setdata;
					}
				}
				if($addsetdata) $params['adddata'] = @implode("|",$addsetdata);

				//평가점수
				if( is_array($_POST['reviewcategory']) ) {
					$scoresum =0;
					foreach($_POST['reviewcategory'] as $reviewcategory){
						$scoresum+=$reviewcategory[0];
						$reviewcategoryar[] = $reviewcategory[0];
					}
					if($reviewcategoryar) $params['reviewcategory'] = @implode(",",$reviewcategoryar);
					$scorecnt		= count($_POST['reviewcategory']);
						$params['score'] = round(($scoresum/$scorecnt));
						$params['score_avg'] =  round(($scoresum/($scorecnt*5))*100);//round(( ($scoresum*($scorecnt*5))/$scorecnt ));
				}

				if( !isset($_POST['displayGoods']) ){
					openDialogAlert("상품을 선택해 주세요.",400,160,'parent','parent.submitck();');
					exit;
				}

				$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
				$contents_tmp = str_replace('&nbsp;', ' ', $params['contents']);
				$cntlenth = mb_strlen(strip_tags($contents_tmp));

				//상품후기 구매자체크, 적립금 지급시 구매자체크 : 상품 + 주문번호 체크
				if( (strstr($this->manager['auth_write'],'[onlybuyer]') ) ) {//( $reserves['autoemoney'] == 1 && $reserves['autoemoneytype'] != 3 )
					if( !$_POST['ordergoodslist']){
						openDialogAlert($this->manager['name']."는 구매자만 작성이 가능합니다.<br/>주문을 선택해 주세요.",400,160,'parent','parent.submitck();');
						exit;
					}
				}

				if( $reserves['autoemoney'] == 1 ) {//자동지급 사용시에만
					if($reserves['autoemoneytype'] == 2) {//조건2) 배송완료 구매자 + **자 후기등록
						 if( ($reserves['autoemoneystrcut1'] > 0 && $reserves['autoemoneystrcut1'] > $cntlenth)  || !$cntlenth) {
							$contentcutck = false;
							$contentcutlenth = $reserves['autoemoneystrcut1'];
						 }else{
							 $contentcutck = true;
						 }
					}elseif($reserves['autoemoneytype'] == 3) {//조건3) **자 후기등록
						 if( ($reserves['autoemoneystrcut2'] > 0 && $reserves['autoemoneystrcut2'] > $cntlenth) || !$cntlenth) {
							$contentcutck = false;
							$contentcutlenth = $reserves['autoemoneystrcut2'];
						 }else{
							 $contentcutck = true;
						 }
					}elseif($reserves['autoemoneytype'] == 1){//구매자인 회원만 적립금지급가능
						$contentcutck = true;
					}

					if( defined('__ISUSER__') === true && $contentcutck != true) {//적립금자동지급 : 회원만체크합니다.
						if(!$cntlenth){
							openDialogAlert($this->manager['name']."를 입력해 주세요.",400,140,'parent','parent.submitck();');
							exit;
						}else{
							if( $_POST['review_reserve_ok'] != 'ok' ) {
								//openDialogAlert($this->manager['name']."를 ".$contentcutlenth."자 이상 입력해 주세요.",400,160,'parent','parent.submitck();');
								openDialogConfirm($this->manager['name']."를 ".$contentcutlenth."자 이상 입력 시 적립금이 지급됩니다.<br/>".$this->manager['name']."를 추가입력하시겠습니까?  ",400,160,'parent','parent.submitck();parent.$(".review_reserve_ok").val("");parent.loadingStop("body",true);','parent.submitck();parent.chk_review_reserve();');
								exit;
							}else{
								//$contentcutck = false;
							}
						}
					}
				}
				$params['order_seq'] = $_POST['ordergoodslist'];
			}

			$params['file_key_w']		= (!empty($_POST['file_key_w']))?($_POST['file_key_w']):'';//웹 인코딩 코드
			$params['file_key_i']		= (!empty($_POST['file_key_i']))?($_POST['file_key_i']):'';//스마트폰 인코딩 코드
			if($this->session->userdata('boardvideotmpcode') && $params['file_key_w'] )
				$params['videotmpcode'] = $this->session->userdata('boardvideotmpcode');//코드

			if( BOARDID == 'mbqna' ) {//1:1문의
				//$params['goods_seq']=  (isset($_POST['displayGoods']) && is_array($_POST['displayGoods']))?implode(",",$_POST['displayGoods']):'';
				$params['order_seq'] = $_POST['ordergoodslist'];
			}

			if( BOARDID == 'store_reservation' ) { //매장용 예약게시판 추가데이터
				$params['reserve_date'] = $_POST['reserve_date'];
			}

			$result = $this->Boardmodel->data_write($params);

			if($result) {
				$newseq = $result;

				//동영상관리
				if( $params['file_key_w'] ){
					$videofiles['file_key_w']			= $params['file_key_w'];
					$videofiles['parentseq']			= $newseq;
					$videofiles['type']						= BOARDID;
					$videofiles['upkind']					= 'board';
					$this->videofiles->videofiles_modify_key($videofiles);
				}

				if( BOARDID == 'goods_review' ) {//상품후기 등록시 자동적립금지급
					goods_review_count($params, $newseq);
					$this->_goods_review_autoemoney($this->manager, $params, $cntlenth, $newseq);
				}elseif( BOARDID == 'goods_qna' ) {//상품문의 건수
					goods_qna_count($params, $newseq);
				}


				if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
					$upmanagerparams['category']		= $this->manager['category'].",".$_POST['newcategory'];
					$upmanagerparams['category']		= str_replace(",,",",",$upmanagerparams['category']);
					//카테고리추가하기
				}

				//게시글수save
				$upmanagerparams['totalnum']		= $this->manager['totalnum']+1;
				$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//게시글증가

				if( BOARDID == 'goods_review' && defined('__ISUSER__') === true) {
					$upsql = "update fm_member set review_cnt = review_cnt+1 where member_seq = '".$this->userInfo['member_seq']."'";
					$this->db->query($upsql);
				}

				if( BOARDID == 'goods_review' && isset($_POST['displayGoods']) && is_array($_POST['displayGoods']) ){
					/* 상품분석 수집 */
					$this->load->model('goodslog');
					foreach($_POST['displayGoods'] as $goods_seq){
						$this->goodslog->add('review',$goods_seq);
					}
				}

				//공지 Boardindex
				$idxparams['hidden']	= $params['hidden'];//비밀글여부
				$idxparams['notice']		= $params['notice'];//공지여부
				$idxparams['gid']			= $params['gid'];//고유번호
				$idxparams['boardid']	= $params['boardid'];//id
				$this->Boardindex->idx_write($idxparams);

				if ($params['gid'] == '100000000.00')
				{
					$this->Boardmodel->get_data_optimize();
					$this->Boardindex->get_data_optimize();
				}

				//비회원이 등록 후 본문 확인가능함
				if( !defined('__ISUSER__') ) {// && $params['hidden'] == 1
					// 비번입력후 브라우저를 닫기전까지는 접근가능함
					$ss_pwhidden_name = 'board_pwhidden_'.BOARDID;
					$boardpwhiddenss = $this->session->userdata($ss_pwhidden_name);
					$boardpwhiddenssadd = (!empty($boardpwhiddenss)) ? $boardpwhiddenss.'['.$newseq.']':'['.$newseq.']';
					$this->session->set_userdata($ss_pwhidden_name, $boardpwhiddenssadd );
				}

				$this->session->unset_userdata('backtype');
				$_POST['backtype'] = (!empty($_POST['backtype']))?($_POST['backtype']):'list';
				$this->session->set_userdata('backtype',$_POST['backtype']);

				//if($_POST['tel1']) {//SMS발송 글등록시2013-04-24
					$this->manager['userid']			 = ($this->minfo['userid'])?$this->minfo['userid']:$params['name'];//비회원은 작성자명
					$this->manager['board_name'] = $this->manager['name'];
					$this->manager['user_name']	 = ($this->minfo['userid'])?$this->minfo['user_name']:$params['name'];//작성자명
					
					$commonSmsData[BOARDID."_write"]['phone'][] = $_POST['tel1'];
					$commonSmsData[BOARDID."_write"]['mid'][] = $this->minfo['userid'];
					$commonSmsData[BOARDID."_write"]['params'][] = $this->manager;
					
					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}

					//sendSMS($_POST['tel1'], BOARDID."_write", $this->minfo['userid'] , $this->manager);
				//}

				$parent = 'parent';
				$closepopup = 'parent.submitck();';
				if($_POST['backtype'] == 'list') {
					$callback = ($_POST['returnurl'])?$parent.".document.location.href='".$_POST['returnurl']."';":$parent.".document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
				} elseif($_POST['backtype'] == 'view') {
					$callback = ($_POST['returnurl'] && empty($_POST['seq']))?$parent.".document.location.href='".$_POST['returnurl'].$newseq."';":$parent.".document.location.href='".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$newseq."';";
				} else {
					$callback = '';
				}
				if( $_POST['mygdreview'] == 'mygdreview' ) {
					$callback = "<script>parent.openDialog('상품평 등록 및 포인트 지급','writefinishlay',{'width':'430','height':'230'});</script>";
					echo $callback;
					exit;

				}else{

					if( BOARDID == 'goods_review' ) {//상품후기 등록시 자동적립금지급

						if( $this->arrSns['facebook_review'] == 'Y' ) {
							if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {
								$callback .= $parent.".getfbopengraph('{$newseq}', 'write', '{$_SERVER[HTTP_HOST]}','".BOARDID."');";
							}else{//전용앱이거나 앱적용도메인과 현재 접근도메인이 다른경우
								$callback .= $parent.".getfbopengraph('{$newseq}', 'write', '{$this->config_system[subDomain]}','".BOARDID."');";
							}
						}
						
						// 통계데이터(review) 전송
						echo "<script>parent.statistics_firstmall('review','".$goods_seq."','','".$params['score']."');</script>";
					}
					
					if ($_POST['iframe'])
						$callback	= str_replace(BOARDID, BOARDID.'&iframe='.$_POST['iframe'], $callback);

					//echo $callback;

					if ($_POST['calllink'] == 'mypage')
						$callback	= "parent.document.location.href='/mypage/myreserve_catalog';";			
					openDialogAlert("게시글을 등록 하였습니다.",400,140,'parent',$callback.$closepopup);
				}
			}else{
				openDialogAlert("게시글 등록에 실패되었습니다.",400,140,'parent','parent.submitck();');
			}
		}

		/* 게시글수정 */
		elseif($mode == 'board_modify') {

			get_auth($this->manager, '', 'write' , $isperm);//접근권한체크
			if ( $isperm['isperm_write'] === false ) {
				$callback = "parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';parent.submitck();";
				if	($_POST['iframe'])	$callback	= str_replace(BOARDID, BOARDID.'&iframe='.$_POST['iframe'], $callback);
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}


			if( $_POST['subject'] == '제목을 입력해 주세요' ) {
				$_POST['subject'] = '';
			}

			if( strstr($_POST['pw'],'비밀번호를 입력해 주세요') ) {
				$_POST['pw'] = '';
				if (!defined('__ISUSER__'))
				{
					openDialogAlert("비밀번호를 정확히 입력해 주세요.",400,140,'parent','parent.submitck();');
					exit;
				}
			}

			if( strtolower($_POST['contents']) == "<p>&nbsp;</p>" || strtolower($_POST['contents']) == "<p><br></p>"  ) $_POST['contents']='';

			$this->validation->set_rules('subject', '제목','trim|required|xss_clean');

			// 매장용 추가 검사
			if( BOARDID == 'store_review' || BOARDID == 'store_reservation' ){
				$this->validation->set_rules('modify_contents_'.$_POST['delseq'], '수정내용','trim|required');
				$_POST['contents'] = $_POST['modify_contents_'.$_POST['delseq']];
				$_POST['name'] = $_POST['real_name'];
				$_POST['seq'] = $_POST['delseq'];
			}else{
				$this->validation->set_rules('contents', '내용','trim|required');
			}

			if( BOARDID == 'bulkorder' ||  BOARDID == 'goods_review' ) {
				$label_pr = $_POST['label'];
				$label_sub_pr = $_POST['labelsub'];
				$label_required = $_POST['required'];

				### //넘어온 추가항목 seq
				foreach($label_pr as $l => $data){$label_arr[]=$l;}
				//추가항목 공백체크
				foreach($label_required as $v){
					if(!in_array($v,$label_arr)){
						$callback = "parent.submitck();if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
						openDialogAlert('체크된 항목은 필수항목입니다.',400,140,'parent',$callback);
						exit;
					}else{
						$query = $this->db->get_where('fm_boardform',array('bulkorderform_seq'=> $v));
						$form_result = $query -> row_array();
						$label_title = $form_result['label_title'];
						$this->validation->set_rules('label['.$v.'][value][]', $label_title,'trim|required|xss_clean');
					}
				}
				###
			}


			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "parent.submitck();if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}

			if( empty($_POST['name']) && !defined('__ISUSER__')) {
				alert("이름을 입력해 주세요.");
				exit;
			}

			//스팸방지 비회원인경우
			if($this->manager['autowrite_use'] == 'Y' && !defined('__ISUSER__') && !( $this->mobileMode || $this->_is_mobile_agent) && BOARDID != 'store_review') {

				$this->load->model('Captchamodel');

				// First, delete old captchas
				$expiration = time()-7200; // Two hour limit
				$this->Captchamodel->data_delete($expiration);

				// Then see if a captcha exists:
				$params['captcha_code'] = (!empty($_POST['captcha_code'])) ? $_POST['captcha_code']:'';
				$params['ip_address'] = $this->input->ip_address();
				$params['expiration'] = $expiration;
				$captchacnt = $this->Captchamodel->data_query($params);

				if ($captchacnt == 0)
				{
					openDialogAlert("스팸방지코드를 정확히 입력해 주세요.",400,140,'parent','parent.submitck();');
					exit;
				}
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
			if( BOARDID == 'mbqna' || BOARDID == 'goods_qna' ) {
				$parentsql['select']		= ' seq, gid, comment, upload, depth, re_contents, file_key_w ';
			}else{
				$parentsql['select']		= ' seq, gid, comment, upload, depth, file_key_w  ';
			}

			$parentdata = $this->Boardmodel->get_data($parentsql);

			if(empty($parentdata)) {
				$callback = "parent.submitck();parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
				if($_POST['iframe']){
					$callback = "parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID.'&iframe='.$_POST['iframe']."';";
				}

				openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
				exit;
			}

			if ( $parentdata['re_contents'] && ( BOARDID == 'mbqna' || BOARDID == 'goods_qna' ) ) {//답변상태수정불가
				openDialogAlert("답변이 등록된 상태입니다.<br/>수정하실 수 없습니다.",400,140,'parent','parent.submitck();');
				exit;
			}

			$params['boardid']			=  BOARDID;
			/**
			$params['notice']			=  if_empty($_POST, 'notice', '0');//공지
			if( $_POST['notice']){
				$params['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
			}else{
				$params['onlynotice']			=	0;//공지영역만 노출여부
			}
			**/

			if( $this->manager['secret_use'] == "A" ) {//무조건비밀글
				$params['hidden']		= 1;//비밀글
			}else{
				$params['hidden']		= if_empty($_POST, 'hidden', '0');//비밀글
			}

			$params['subject']		=  $_POST['subject'];
			$params['editor']			=  ($_POST['daumedit'])?1:0;//모바일
			$params['name']			=  (!empty($_POST['name']))?$_POST['name']:'';
			$params['category']		= (!empty($_POST['category']))?$_POST['category']:'';
			$params['contents']		=  $_POST['contents'];

			if( BOARDID != 'store_review' && BOARDID != 'store_reservation' ){
				$pw							=  (!empty($_POST['pw']))?md5($_POST['pw']):'';
				$params['pw']				=  (!empty($_POST['oldpw']))?($_POST['oldpw']):$pw;
				$params['tel1']				=  (!empty($_POST['tel1']))?($_POST['tel1']):'';
			}

			if( BOARDID == 'store_reservation' ) { //매장용 예약게시판 추가데이터
				$_POST['tel1']			= $_POST['phone_num1'] . "-" . $_POST['phone_num2'] . "-" . $_POST['phone_num3'];
				$_POST['reserve_date']	= $_POST['reserve_date'] . " " . str_pad($_POST['reserve_time_h'], 2, "0", STR_PAD_LEFT) . ":" . str_pad($_POST['reserve_time_m'], 2, "0", STR_PAD_LEFT);

				/* 수정시에는 전화번호 예약일 수정 불가 2014-02-12 lwh */
				//$params['tel1']			= $_POST['tel1'];
				//$params['reserve_date'] = $_POST['reserve_date'];
			}

			$params['email']			=  (!empty($_POST['email']))?($_POST['email']):'';
			$params['tel2']				=  (!empty($_POST['tel2']))?($_POST['tel2']):'';

			$params['rsms']			=  if_empty($_POST, 'board_sms', 'N');//수신여부
			$params['remail']			=  if_empty($_POST, 'board_email', 'N');//수신여부

			if( !empty($_POST['score']) ) $params['score']  = ($_POST['score']);//값이 잇는경우에만 변경

			//$params['re_contents'] = (!empty($_POST['re_contents']))?$_POST['re_contents']:'';//1:1문의 답변시

			$params['m_date']		= date("Y-m-d H:i:s");
			$params["ip"]				= $this->input->ip_address();
			$params["agent"]		= $_SERVER['HTTP_USER_AGENT'];
			$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';

			//(/data/tmp 임시폴더에서 게시판폴더로 이동변경 $this->Boardmodel->upload_src
			$params['contents'] = adjustEditorImages($_POST['contents'], $this->Boardmodel->upload_src);

			board_mobile_file($parentdata, $realfilename, $incimage);//첨부파일처리
			if(isset($realfilename)){
				$params['upload'] = @implode("|",$realfilename);
			}else{
				$params['upload'] = '';//초기화
			}

			if(  !$params['editor'] || ( $this->mobileMode || $this->storemobileMode ) || $this->_is_mobile_agent ){//모바일인 순서바꾸지마세요.
				if ( $_POST['insert_image'] == 'top') {
					$params['contents'] = @implode(" ",$incimage).'<br /><br />'.nl2br($params['contents']);
				}elseif ( $_POST['insert_image'] == 'bottom') {
					$params['contents'] = nl2br($params['contents']).'<br /><br />'.@implode(" ",$incimage);
				}else{
					$params['contents'] = nl2br($params['contents']);
				}
			}
			$params['insert_image'] =  if_empty($_POST, 'insert_image', 'none');

			//신규분류
			if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
				$params['category'] = $_POST['newcategory'];
			}

			if( BOARDID == 'bulkorder' ) {
				$params['payment']				=  (!empty($_POST['payment']))?($_POST['payment']):'';
				$params['typereceipt']			=  (!empty($_POST['typereceipt']))?($_POST['typereceipt']):'';
				$params['total_price']			=  (!empty($_POST['total_price']))?($_POST['total_price']):'0';
				unset($addsetdata);
				### //추가정보 저장
				foreach ($label_pr as $k => $data) {
					foreach ($data['value'] as $j => $subdata) {
						if($k == '1' ){//
							$params['person_name']		=  $subdata;
						}elseif($k == '2' ){//
							$params['person_email']		= $subdata;
							$params['email']			=  $params['person_email'];
						}elseif($k == '3' ){//
							$params['person_tel1']		= $subdata;
						}elseif($k == '4' ){//
							$params['person_tel2']		= $subdata;
							$params['tel1']				=  $params['person_tel2'];
						}elseif($k == '5' ){//
							$params['company']			= $subdata;
						}elseif($k == '6' ){//
							$params['shipping_date']	= $subdata;
						}
						$query = $this->db->get_where('fm_boardform',array('bulkorderform_seq'=> $k,'boardid'=>BOARDID));
						$form_result = $query -> row_array();
						$setdata = 'label_title='.$form_result['label_title'];
						$setdata .= '^^bulkorderform_seq='.$form_result['bulkorderform_seq'];
						$setdata .= '^^label_value='.$subdata;
						$setdata .= '^^label_sub_value='.$label_sub_pr[$k]['value'][$j];
						$addsetdata[] = $setdata;
					}
				}
				if($addsetdata) $params['adddata'] = @implode("|",$addsetdata);

			}elseif( BOARDID == 'goods_review' ) {

				//평가정보
				unset($addsetdata);
				### //추가정보 저장
				foreach ($label_pr as $k => $data) {
					foreach ($data['value'] as $j => $subdata) {
						$query = $this->db->get_where('fm_boardform',array('bulkorderform_seq'=> $k,'boardid'=>BOARDID));
						$form_result = $query -> row_array();
						$setdata = 'label_title='.$form_result['label_title'];
						$setdata .= '^^bulkorderform_seq='.$form_result['bulkorderform_seq'];
						$setdata .= '^^label_value='.$subdata;
						$setdata .= '^^label_sub_value='.$label_sub_pr[$k]['value'][$j];
						$addsetdata[] = $setdata;
					}
				}
				if($addsetdata) $params['adddata'] = @implode("|",$addsetdata);
				//평가점수
				if( is_array($_POST['reviewcategory']) ) {
					$scoresum =0;
					foreach($_POST['reviewcategory'] as $reviewcategory){
						$scoresum+=$reviewcategory[0];
						$reviewcategoryar[] = $reviewcategory[0];
					}
					if($reviewcategoryar) $params['reviewcategory'] = @implode(",",$reviewcategoryar);
					$scorecnt		= count($_POST['reviewcategory']);

					$params['score'] = round(($scoresum/$scorecnt));
					$params['score_avg'] = round(($scoresum/($scorecnt*5))*100);//round(( ($scoresum*($scorecnt*5))/$scorecnt ));
				}
			}


			//동영상연동
			if($_POST['video_del'] == 1) $params['file_key_w'] = '';//원본파일코드초기화
			if($_POST['file_key_w']) $params['file_key_w'] = $_POST['file_key_w'];//웹 인코딩 코드
			if($_POST['file_key_i']) $params['file_key_i'] = $_POST['file_key_i'];//스마트폰 인코딩 코드

			if($this->session->userdata('boardvideotmpcode') && $params['file_key_w'] )
				$params['videotmpcode'] = $this->session->userdata('boardvideotmpcode');//코드

			$boardidar	= @explode('^^', $this->session->userdata('tmpcode'));
			$params['tmpcode'] = ($boardidar[1])?$boardidar[1]:'';//첨부파일코드

			//상품문의/후기
			$params['goods_seq']				=  (isset($_POST['displayGoods']) && is_array($_POST['displayGoods']))?implode(",",$_POST['displayGoods']):'';

			if($_POST['displayGoods']) {//상품관련
				$this->load->model('goodsmodel');
				foreach($_POST['displayGoods'] as $displayGoods){
					$goods = $this->goodsmodel->get_goods($displayGoods);
					$providerseq[] = $goods['provider_seq'];
				}
				$params['provider_seq']				=  (isset($providerseq) && is_array($providerseq))?implode(",",$providerseq):'';
			}else{
				if( BOARDID == 'bulkorder' ) {//대량구매게시판
					$params['provider_seq'] = $this->userInfo['member_seq'];
				}
			}

			if( BOARDID == 'mbqna' ) {//1:1문의
				//$params['goods_seq']=  (isset($_POST['displayGoods']) && is_array($_POST['displayGoods']))?implode(",",$_POST['displayGoods']):'';
				$params['order_seq'] = $_POST['order_seq'];
			}


			$params['goods_cont']				=  (isset($_POST['displayGoods_cont']) && is_array($_POST['displayGoods_cont']))?implode("^|^",$_POST['displayGoods_cont']):'';

			$result = $this->Boardmodel->data_modify($params);
			if($result) {

				//동영상관리
				if($_POST['video_del'] == 1 && $parentdata['file_key_w']){//연결해제(삭제)
					$this->videofiles->videofiles_delete_key('board',BOARDID,$parentdata['file_key_w']);
				}
				if( $params['file_key_w'] ){
					$videofiles['file_key_w']			= $params['file_key_w'];
					$videofiles['parentseq']			= $_POST['seq'];
					$videofiles['type']						= BOARDID;
					$videofiles['upkind']					= 'board';
					$this->videofiles->videofiles_modify_key($videofiles);
				}

				if( BOARDID == 'goods_review' ) {//상품후기 수정시 자동적립금지급?
					//$this->_goods_review_autoemoney($this->manager, $params, $cntlenth, $_POST['seq']);
				}


				if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
					$upmanagerparams['category']		= $this->manager['category'].",".$_POST['newcategory'];
					$upmanagerparams['category']		= str_replace(",,",",",$upmanagerparams['category']);
					$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//카테고리변경
					//카테고리추가하기
				}

				//공지 Boardindex
				$idxparams['hidden']	= $params['hidden'];//비밀글여부
				//$idxparams['notice']		= $params['notice'];//공지여부
				$idxparams['gid']			= $parentdata['gid'];//고유번호
				$idxparams['boardid']	= $params['boardid'];//id
				$this->Boardindex->idx_modify($idxparams);

				if (isset($_POST['board_sms']) && isset($_POST['board_sms_hand'])) {//답변시
					//SMS
				}

				if (isset($_POST['board_email']) && isset($_POST['board_sms_email']) ) {//답변시
					//Email
				}

				$this->session->unset_userdata('backtype');
				$_POST['backtype'] = (!empty($_POST['backtype']))?($_POST['backtype']):'list';
				$this->session->set_userdata('backtype',$_POST['backtype']);

				$parent = 'parent';
					$closepopup = 'parent.submitck();';

				if($_POST['backtype'] == 'list') {
					$callback = (!empty($_POST['returnurl'])) ?$parent.".document.location.href='".$_POST['returnurl']."';":$parent.".document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
				}elseif($_POST['backtype'] == 'view'){
					$callback = (!empty($_POST['returnurl'])) ?$parent.".document.location.href='".$_POST['returnurl']."';":$parent.".document.location.href='".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$_POST['seq']."';";
				}else {
					$callback = '';
				}

				if	($_POST['iframe'])	$callback	= str_replace(BOARDID, BOARDID.'&iframe='.$_POST['iframe'], $callback);
				openDialogAlert("게시글을 수정하였습니다.",400,140,'parent',$callback.$closepopup);
			}else{
				alert("게시글 수정이 실패 되었습니다.");
			}
			exit;
		}

		/* 게시글삭제 */
		elseif($mode == 'board_delete') {

			get_auth($this->manager, '', 'write' , $isperm);//접근권한체크
			if ( $isperm['isperm_write'] === false ) {
				$callback = "parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			$num = 0;
			$parentsql['whereis']	= ' and seq= "'.$_POST['delseq'].'" ';
			$parentsql['select']		= ' * ';

			$parentdata = $this->Boardmodel->get_data($parentsql);
			if(empty($parentdata)) {
				$callback = "";
				openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
				exit;
			}

			$replyor = '';
			/**
			$replysc['whereis']	= ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1) . ' ';
			//$replysc['select']		= " gid ";
			$replyor = $this->Boardindex->get_data_numrow($replysc);
			**/
			$replysc['whereis'] = ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1).' and parent = '.($parentdata['seq']).' ';//답변여부
			$replyor = $this->Boardmodel->get_data_numrow($replysc); 

			//게시글 삭제시 적립금/포인트 회수 한번만!
			if( $parentdata['display'] != 1  && $parentdata['mseq'] ) {
				if( BOARDID == 'goods_review' ) {
					$this->_goods_review_less($this->manager, $parentdata);
				}else{
					$this->_board_less($this->manager, $parentdata);
				}
			}

			if($replyor==0 && $parentdata['comment']==0) {//답변과 댓글이 없는 경우 real 삭제
				$num++;
				$result = $this->Boardmodel->data_delete($_POST['delseq']);//게시글삭제
				if($result) {
					$this->Boardindex->idx_delete($parentdata['gid']);//index 삭제
					
					//게시글평가제거
					$this->Boardscorelog->data_parent_delete($_POST['delseq']); 

					//첨부파일삭제
					if(!empty($parentdata['upload'])){
						$oldfile = @explode("|",$parentdata['upload']);
						for ( $f=0;$f<count($oldfile);$f++) {
								$oldrealfile = @explode("^^",$oldfile[$f]);
							if(@is_file($this->Boardmodel->upload_path.$oldrealfile[0])){
								@unlink($this->Boardmodel->upload_path.$oldrealfile[0]);//기존위치의 파일삭제
								@unlink($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0]);//기존위치의 파일삭제
							}
						}
					}

					//게시글수 save
					$upmanagerparams['totalnum']		= $this->manager['totalnum']-1;
					$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//본래게시판의 게시글감소

					if( BOARDID == 'goods_review') {
						//상품정보 > 상품후기건수 차감
						if( $parentdata['goods_seq'] ) {
							$reviewparentdata['goods_seq'] = $parentdata['goods_seq'];
							goods_review_count($reviewparentdata, $parentdata['seq'], 'minus');
						}

						if( $parentdata['mseq'] ) {
							//회원정보체크
							$this->load->model('membermodel');
							$minfo = $this->membermodel->get_member_data($parentdata['mseq']);
							if($minfo['review_cnt'] > 0 ){
								$upsql = "update fm_member set review_cnt = review_cnt-1 where member_seq = '".$parentdata['mseq']."'";
								$this->db->query($upsql);
							}
						}
					}elseif( BOARDID == 'goods_qna' ) {//상품문의 건수 차감
						if( $parentdata['goods_seq'] ) {
							$qnaparentdata['goods_seq'] = $parentdata['goods_seq'];
							goods_qna_count($qnaparentdata, $parentdata['seq'], 'minus');
						}
					}

					//$callback = "parent.document.location.reload();";
					$parent = 'parent';
					$callback = (!empty($_POST['returnurl'])) ?$parent.".document.location.href='".$_POST['returnurl']."';":$parent.".document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
					openDialogAlert("게시글을 삭제하였습니다.",400,140,'parent',$callback);
				}else{
					alert("게시글 삭제가 실패 되었습니다.");
				}
				exit;
			}else{
				$params['display']			= '1';//삭제글여부1
				$params['subject']			= '';//초기화함
				$params['contents']			= '';//초기화함
				//$params['comment']		= '';//댓글수 초기화
				$params['upload']			= '';//첨부파일 초기화
				$params['r_date']			= date("Y-m-d H:i:s");
				$result = $this->Boardmodel->data_delete_modify($params,$_POST['delseq']);
				if($result) {

					//공지글삭제
					$idxparams['display']	= 1;//삭제여부
					$idxparams['notice']		= 0;//공지 해지
					$idxparams['gid']			= $parentdata['gid'];//고유번호
					$this->Boardindex->idx_delete_modify($idxparams);

					//첨부파일삭제
					if(!empty($parentdata['upload'])){
						$oldfile = @explode("|",$parentdata['upload']);
						for ( $i=0;$i<count($oldfile);$i++) {
							$oldrealfile = @explode("^^",$oldfile[$i]);
							if(@is_file($this->Boardmodel->upload_path.$oldrealfile[0])){//기존위치에 수정시 변경
								@unlink($this->Boardmodel->upload_path.$oldrealfile[0]);//기존위치의 파일삭제
								@unlink($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0]);//기존위치의 파일삭제
							}
						}
					}

					$parent = 'parent';
					$callback = (!empty($_POST['returnurl'])) ?$parent.".document.location.href='".$_POST['returnurl']."';":$parent.".document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
					openDialogAlert("게시글을 삭제하였습니다.",400,140,'parent',$callback);
				}else{
					alert("게시글 삭제가 실패 되었습니다.");
				}
				exit;
			}
		}


		/* 게시글삭제 */
		elseif($mode == 'board_modifydelete_pwckeck') {

			get_auth($this->manager, '', 'write' , $isperm);//접근권한체크
			if ( $isperm['isperm_write'] === false ) {
				$callback = "parent.document.location.href='".$this->Boardmanager->realboarduserurl.BOARDID."';";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			if(empty($_POST['seq'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			if(empty($_POST['pw'])) {
				$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($return);
				exit;
			}

			$num = 0;
			$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
			$parentsql['select']		= ' * ';
			$parentdata = $this->Boardmodel->get_data($parentsql);
			if(empty($parentdata['seq'])) {
				$return = array('result'=>false, 'msg'=>"존재하지 않는 게시물입니다.");
				echo json_encode($return);
				exit;
			}

			if (md5($_POST['pw']) == $parentdata['pw']) {//비밀번호가 맞는 경우

				if($_POST['modetype'] == 'board_delete') {//비회원 > 게시글 삭제임...
					$replyor = '';
					$replysc['whereis'] = ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1).' and parent = '.($parentdata['seq']).' ';//답변여부
					$replyor = $this->Boardmodel->get_data_numrow($replysc); 

					//게시글 삭제시 적립금/포인트 회수 한번만!
					if( $parentdata['display'] != 1  && $parentdata['mseq'] ) {
						if( BOARDID == 'goods_review' ) {
							$this->_goods_review_less($this->manager, $parentdata);
						}else{
							$this->_board_less($this->manager, $parentdata);
						}
					}

					if($replyor==0 && $parentdata['comment']==0) {//답변과 댓글이 없는 경우 real 삭제
						$num++;
						$result = $this->Boardmodel->data_delete($_POST['seq']);//게시글삭제
						if($result) {
							$this->Boardindex->idx_delete($parentdata['gid']);//index 삭제

							//게시글평가제거
							$this->Boardscorelog->data_parent_delete($_POST['seq']);

							//첨부파일삭제
							if(!empty($parentdata['upload'])){
								$oldfile = @explode("|",$parentdata['upload']);
								for ( $f=0;$f<count($oldfile);$f++) {
										$oldrealfile = @explode("^^",$oldfile[$f]);
									if(@is_file($this->Boardmodel->upload_path.$oldrealfile[0])){//기존위치에 수정시 변경
										@unlink($this->Boardmodel->upload_path.$oldrealfile[0]);//기존위치의 파일삭제
										@unlink($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0]);//기존위치의 파일삭제
									}
								}
							}

							//게시글수 save
							$upmanagerparams['totalnum']		= $this->manager['totalnum']-1;
							$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//본래게시판의 게시글감소

							if( BOARDID == 'goods_review' ) {
								//상품정보 > 상품후기건수 차감
								if( $parentdata['goods_seq'] ) {
									$reviewparentdata['goods_seq'] = $parentdata['goods_seq'];
									goods_review_count($reviewparentdata, $parentdata['seq'], 'minus');
								}

								if( $parentdata['mseq']) {
									//회원정보체크
									$this->load->model('membermodel');
									$minfo = $this->membermodel->get_member_data($parentdata['mseq']);
									if($minfo['review_cnt'] > 0 ){
										$upsql = "update fm_member set review_cnt = review_cnt-1 where member_seq = '".$parentdata['mseq']."'";
										$this->db->query($upsql);
									}
								}
							}elseif( BOARDID == 'goods_qna' ) {//상품문의 건수 차감
								if( $parentdata['goods_seq'] ) {
									$qnaparentdata['goods_seq'] = $parentdata['goods_seq'];
									goods_qna_count($qnaparentdata, $parentdata['seq'], 'minus');
								}
							}
							$return = array('result'=>true, 'msg'=>"정상적으로 삭제되었습니다.");
							echo json_encode($return);
							exit;
						}else{
							$return = array('result'=>false, 'msg'=>"게시글 삭제가 실패 되었습니다.");
							echo json_encode($return);
							exit;
						}
					}else{
						$params['display']			= '1';//삭제글여부1
						$params['subject']			= '';//초기화함
						$params['contents']			= '';//초기화함
						//$params['comment']		= '';//댓글수 초기화
						$params['upload']			= '';//첨부파일 초기화
						$params['r_date']			= date("Y-m-d H:i:s");
						$result = $this->Boardmodel->data_delete_modify($params,$_POST['seq']);
						if($result) {

							//공지글삭제
							$idxparams['display']	= 1;//삭제여부
							$idxparams['notice']		= 0;//공지 해지
							$idxparams['gid']			= $parentdata['gid'];//고유번호
							$this->Boardindex->idx_delete_modify($idxparams);

							//첨부파일삭제
							if(!empty($parentdata['upload'])){
								$oldfile = @explode("|",$parentdata['upload']);
								for ( $i=0;$i<count($oldfile);$i++) {
									$oldrealfile = @explode("^^",$oldfile[$i]);
									if(@is_file($this->Boardmodel->upload_path.$oldrealfile[0])){//기존위치에 수정시 변경
										@unlink($this->Boardmodel->upload_path.$oldrealfile[0]);//기존위치의 파일삭제
										@unlink($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0]);//기존위치의 파일삭제
									}
								}
							}

							$return = array('result'=>true, 'msg'=>"정상적으로 삭제되었습니다.");
							echo json_encode($return);
							exit;
						}else{
							$return = array('result'=>false, 'msg'=>"게시글 삭제가 실패 되었습니다.");
							echo json_encode($return);
							exit;
						}
					}
				}else{//수정인경우

					// 비번입력후 브라우저를 닫기전까지는 등록/삭제가능함
					$ss_pwwrite_name = 'board_pwwrite_'.BOARDID;
					$boardpwwritess = $this->session->userdata($ss_pwwrite_name);
					if ( ( !strstr($boardpwwritess,'['.$_POST['seq'].']') && !empty($boardpwwritess)) || empty($boardpwwritess)) {
						$boardpwwritessadd = (!empty($boardpwwritess)) ? $boardpwwritess.'['.$_POST['seq'].']':'['.$_POST['seq'].']';
						$this->session->set_userdata($ss_pwwrite_name, $boardpwwritessadd );
					}

					$return = array('result'=>true);
					echo json_encode($return);
					exit;
				}
			}else{
				$return = array('result'=>false, 'msg'=>"비밀번호가 일치하지 않습니다.");
				echo json_encode($return);
				exit;
			}
			exit;
		}

		/* 게시글다중삭제 */
		elseif($mode == 'board_multi_delete') {

		}

		/* 게시글다중복사 */
		elseif($mode == 'board_multi_copy') {

		}

		/* 게시글 다중이동 */
		elseif($mode == 'board_multi_move') {

		}

		/* 게시글 파일삭제 */
		elseif($mode == 'board_file_delete') {

			if(empty($_POST['realfiledir'])) {// || empty($_SERVER['HTTP_REFERER']) ie8
				$callback = "document.history(-1)";
				openDialogAlert("다운받을 파일을 선택해 주세요.",400,140,'parent',$callback);
				exit;
			}

			if( strstr($_POST['realfiledir'],'..') ) {
				$callback = "document.history(-1)";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			if( !strstr($_POST['realfiledir'],'/data/') ) {
				$callback = "document.history(-1)";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			if(is_file($_POST['realfiledir'])){
				@unlink($_POST['realfiledir']);
				echo "true";
				exit;
			}else{
				@unlink($this->Boardmodel->upload_path.$realfile.$_POST['realfilename']);
				echo "true";
				exit;
			}
		}

		/* 게시글 파일다운 */
		elseif($mode == 'board_file_down') {
			if(empty($_GET['realfiledir']) ) {// || empty($_SERVER['HTTP_REFERER']) ie8 no
				$callback = "document.history(-1)";
				openDialogAlert("다운받을 파일을 선택해 주세요.",400,140,'parent',$callback);
				exit;
			}

			if( strstr($_GET['realfiledir'],'..') ) {
				$callback = "document.history(-1)";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}


			if( !strstr($_GET['realfiledir'],'/data/') ) {
				$callback = "document.history(-1)";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			//데이타이전->한글파일명처리@2012-11-22
			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$_GET['realfilename']) && preg_match("/[\xA1-\xFE\xA1-\xFE]/",$_GET['realfiledir'])) {
				$_GET['realfiledir'] = str_replace(basename($_GET['realfiledir']),"",($_GET['realfiledir'])).iconv('utf-8','cp949',($_GET['realfilename']));
			}

			if(is_file($_GET['realfiledir'])){
				$data = @file_get_contents($_GET['realfiledir']);
				force_download(mb_convert_encoding(str_replace(" ","_",$_GET['realfilename']), 'euc-kr', 'utf-8'), $data);
				exit;
			}
		}

		/* 게시글 파일보기 */
		elseif($mode == 'board_file_review') {
			if(empty($_GET['realfiledir']) ) {// || empty($_SERVER['HTTP_REFERER']) ie8 no
				$callback = "document.history(-1)";
				openDialogAlert("다운받을 파일을 선택해 주세요.",400,140,'parent',$callback);
				exit;
			}

			if( strstr($_GET['realfiledir'],'..') ) {
				$callback = "document.history(-1)";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}


			if( !strstr($_GET['realfiledir'],'/data/') ) {
				$callback = "document.history(-1)";
				openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
				exit;
			}

			//데이타이전->한글파일명처리@2012-11-22
			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$_GET['realfilename']) && preg_match("/[\xA1-\xFE\xA1-\xFE]/",$_GET['realfiledir'])) {
				$_GET['realfiledir'] = str_replace(basename($_GET['realfiledir']),"",($_GET['realfiledir'])).iconv('utf-8','cp949',($_GET['realfilename']));
			}

			if(is_file($_GET['realfiledir'])){
				$data = @file_get_contents($_GET['realfiledir']);
				echo $data;
				exit;
			}
		}

		/* 게시글 비밀글 > 비밀번호 체크 */
		elseif($mode == 'board_hidden_pwcheck') {

			if(empty($_POST['seq'])) {
				$result = array('result'=>false, 'msg'=>"잘못된 접근입니다.");
				echo json_encode($result);
				exit;
			}

			if(empty($_POST['pw'])) {
				$result = array('result'=>false, 'msg'=>"비밀번호가 일치하지 않습니다.");
				echo json_encode($result);
				exit;
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
			$parentsql['select']		= '  seq, gid, comment, upload, depth, pw, mseq, mid, parent';
			$parentdata = $this->Boardmodel->get_data($parentsql);//게시물정보
			if(empty($parentdata['seq'])) {
				$callback = "document.history(-1)";
				openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
				exit;
			}

			$topparentsql['whereis']	= ' and seq= "'.$parentdata['parent'].'" ';
			$topparentsql['select']		= '  seq, gid, comment, upload, depth, pw, mseq, mid, parent';
			$topparentdata = $this->Boardmodel->get_data($topparentsql);//게시물정보

			if ( md5($_POST['pw']) == $parentdata['pw'] || md5($_POST['pw']) == $topparentdata['pw']) {//원본글 이나 부모글 비밀번호가 동일한경우
				// 비번입력후 브라우저를 닫기전까지는 접근가능함
				$ss_pwhidden_name = 'board_pwhidden_'.BOARDID;
				$boardpwhiddenss = $this->session->userdata($ss_pwhidden_name);
				if ( ( !strstr($boardpwhiddenss,'['.$_POST['seq'].']') && !empty($boardpwhiddenss)) || empty($boardpwhiddenss)) {
					$boardpwhiddenssadd = (!empty($boardpwhiddenss)) ? $boardpwhiddenss.'['.$_POST['seq'].']':'['.$_POST['seq'].']';
					$this->session->set_userdata($ss_pwhidden_name, $boardpwhiddenssadd );
				}

				$result = array('result'=>true);
				echo json_encode($result);
			}else{
				$result = array('result'=>false, 'msg'=>"비밀번호가 일치하지 않습니다.");
				echo json_encode($result);
				//잘못된 비밀번호입니다........
			}
			exit;

		}

		/* 스팸방지 새로고침 */
		elseif($mode == 'captcha_code_refresh') {
			$cap = boardcaptcha('refresh');
			if( $cap['image'] ) {
				$result = array('result'=>true, 'img'=>$cap['image']);
			}else{
				$result = array('result'=>false, 'msg'=>"생성하지 못하였습니다.");
			}
			echo json_encode($result);
			exit;
		} 
	}

	/* 상품후기 자동적립금 지급 */
	public function _goods_review_autoemoney($manager, $data, $cntlenth, $goodsreviewparent)
	{
		//emoney history
		$this->load->model('emoneymodel');
		$this->load->model('membermodel');

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');

		if( $reserves['autoemoney'] == 1 ) {//자동지급 사용시에만
			if($reserves['autoemoneytype'] == 2) {//조건2) 배송완료 구매자 + **자 후기등록
				 if( ($reserves['autoemoneystrcut1'] > 0 && $reserves['autoemoneystrcut1'] > $cntlenth)  || !$cntlenth) {
					$contentcutck = false;
					$contentcutlenth = $reserves['autoemoneystrcut1'];
				 }else{
					 $contentcutck = true;
				 }
			}elseif($reserves['autoemoneytype'] == 3) {//조건3) **자 후기등록
				 if( ($reserves['autoemoneystrcut2'] > 0 && $reserves['autoemoneystrcut2'] > $cntlenth) || !$cntlenth) {
					$contentcutck = false;
					$contentcutlenth = $reserves['autoemoneystrcut2'];
				 }else{
					 $contentcutck = true;
				 }
			}elseif($reserves['autoemoneytype'] == 1){//구매자인 회원만 적립금지급가능
				$contentcutck = true;
			}
		}


		if($reserves['autoemoney'] == 1 && $this->userInfo['member_seq'] && $contentcutck === true ) {//사용함

			//동영상 > 포토 > 기본게시글 우선순위중 지급@2014-05-12
			if( ($data['file_key_w'] && uccdomain('fileswf',$data['file_key_w'])) || ($data['file_key_i'] && uccdomain('fileswf',$data['file_key_i'])) ) {//동영상 > video
				$type = 'goods_review_auto_video';
				$goods_review_emoney = $reserves['autoemoney_video'];
				$goods_review_memo = '동영상 '.$manager['name'].' 작성 적립금';
				$goods_review_emoney_limit_date = get_emoney_limitdate('video_reserve');

				$goods_review_point = $reserves['autopoint_video'];
				$goods_review_memo_point = '동영상 '.$manager['name'].' 작성 포인트';
				$goods_review_point_limit_date = get_point_limitdate('video_point');
			}elseif($data['upload']  && boardisimage($data['upload'], $data['contents']) ) {//첨부파일 > image
				$type = 'goods_review_auto_photo';
				$goods_review_emoney = $reserves['autoemoney_photo'];
				$goods_review_memo = '포토 '.$manager['name'].' 작성 적립금';
				$goods_review_emoney_limit_date = get_emoney_limitdate('photo_reserve');

				$goods_review_point = $reserves['autopoint_photo'];
				$goods_review_memo_point = '포토 '.$manager['name'].' 작성 포인트';
				$goods_review_point_limit_date = get_point_limitdate('photo_point');
			}else{
				$type = 'goods_review_auto';
				$goods_review_emoney = $reserves['autoemoney_review'];
				$goods_review_memo = '일반 '.$manager['name'].' 작성 적립금';
				$goods_review_emoney_limit_date = get_emoney_limitdate('default_reserve');

				$goods_review_point = $reserves['autopoint_review'];
				$goods_review_memo_point = '일반 '.$manager['name'].' 작성 포인트';
				$goods_review_point_limit_date = get_point_limitdate('default_point');
			}

			### 특정기간 추가적립금 또는 추가 포인트 및 유효기간체크 @2014-05-12
			if($reserves['bbs_start_date'] && $reserves['bbs_end_date']){
				$today = date("Y-m-d");
				if($today>=$reserves['bbs_start_date'] && $today<=$reserves['bbs_end_date']){
					$type_add = 'goods_review_date';
					$goods_review_emoney_add	= $reserves['emoneyBbs_limit'];
					$goods_review_memo_add = '특정기간 '.$manager['name'].' 작성 추가적립금';
					$goods_review_emoney_limit_date_add = get_emoney_limitdate('date_reserve');

					$goods_review_point_add	= $reserves['pointBbs_limit'];
					$goods_review_memo_point_add = '특정기간 '.$manager['name'].' 작성 추가포인트';
					$goods_review_point_limit_date_add = get_point_limitdate('date_point');
				}
			}

			if($reserves['autoemoneytype'] == 3) {//조건3) **자 후기등록 => 중복가능
				if($goods_review_emoney > 0 ) {
					$emoney['type']			= $type;
					$emoney['emoney']		=$goods_review_emoney;
					$emoney['gb']				= 'plus';
					$emoney['goods_review']= $data['goods_seq'];
					$emoney['goods_review_parent']= $goodsreviewparent;
					$emoney['ordno']			= $data['order_seq'];
					$emoney['memo']		= $goods_review_memo;
					$emoney['limit_date']	= $goods_review_emoney_limit_date;
					$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
				}

				if($goods_review_point > 0 ) {
					$point['type']			= $type;
					$point['point']			=$goods_review_point;
					$point['gb']				= 'plus';
					$point['goods_review']= $data['goods_seq'];
					$point['goods_review_parent']= $goodsreviewparent;
					$point['ordno']			= $data['order_seq'];
					$point['memo']			= $goods_review_memo_point;
					$point['limit_date']	= $goods_review_point_limit_date;
					$this->membermodel->point_insert($point, $this->userInfo['member_seq']);
				}


				### 특정기간 추가적립금 또는 추가 포인트 및 유효기간체크 @2014-05-12
				if($goods_review_emoney_add > 0 ) {
					$emoney['type']			= $type_add;
					$emoney['emoney']		=$goods_review_emoney_add;
					$emoney['gb']				= 'plus';
					$emoney['goods_review']= $data['goods_seq'];
					$emoney['goods_review_parent']= $goodsreviewparent;
					$emoney['ordno']			= $data['order_seq'];
					$emoney['memo']		= $goods_review_memo_add;
					$emoney['limit_date']	= $goods_review_emoney_limit_date_add;
					$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
				}

				### 특정기간 추가적립금 또는 추가 포인트 및 유효기간체크 @2014-05-12
				if($goods_review_point_add > 0 ) {
					$point['type']			= $type_add;
					$point['point']			=$goods_review_point_add;
					$point['gb']				= 'plus';
					$point['goods_review']= $data['goods_seq'];
					$point['goods_review_parent']= $goodsreviewparent;
					$point['ordno']			= $data['order_seq'];
					$point['memo']			= $goods_review_memo_point_add;
					$point['limit_date']	= $goods_review_point_limit_date_add;
					$this->membermodel->point_insert($point, $this->userInfo['member_seq']);
				}

			}else{

				if( $data['order_seq']) {//주문번호 and 회원 and 주문상품

					$itemwhere_arr = array('order_seq'=>$data['order_seq'], 'goods_seq'=>$data['goods_seq']);
					$itemdata = get_data('fm_order_item', $itemwhere_arr);
					if(!$itemdata){//주문상품이 없는경우
						return false;
					} 

					$autoemoneysc['whereis'] = " and emoney_use!='less'  and (type ='goods_review_date' or type ='goods_review_auto_video' or type = 'goods_review_auto' or type = 'goods_review_auto_photo') and gb = 'plus' and member_seq = '".$this->userInfo['member_seq']."' and ordno  = '".$data['order_seq']."' and goods_review = '".$data['goods_seq']."' ";
					$autoemoneysc['select']	= ' emoney, type, emoney_seq ';
					$emautoemoneyck = $this->emoneymodel->get_data_numrow($autoemoneysc);//지급여부

					if( !$emautoemoneyck ) {//자동지급안된경우

						if($goods_review_emoney > 0 ) {
							$emoney['type']			= $type;
							$emoney['emoney']		=$goods_review_emoney;
							$emoney['gb']				= 'plus';
							$emoney['goods_review']= $data['goods_seq'];
							$emoney['goods_review_parent']= $goodsreviewparent;
							$emoney['ordno']			= $data['order_seq'];
							$emoney['memo']		= $goods_review_memo;
							$emoney['limit_date']	= $goods_review_emoney_limit_date;
							$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
						}

						if($goods_review_emoney_add > 0 ) {
							$emoney['type']			= $type_add;
							$emoney['emoney']		=$goods_review_emoney_add;
							$emoney['gb']				= 'plus';
							$emoney['goods_review']= $data['goods_seq'];
							$emoney['goods_review_parent']= $goodsreviewparent;
							$emoney['ordno']			= $data['order_seq'];
							$emoney['memo']		= $goods_review_memo_add;
							$emoney['limit_date']	= $goods_review_emoney_limit_date_add;
							$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
						}

					}

					//주문번호 and 회원 and 주문상품
					$autoemoneysc = $this->db->query("select  point, type, point_seq  from fm_point where  point_use!='less' and  (type ='goods_review_date' or type ='goods_review_auto_video' or type = 'goods_review_auto' or type = 'goods_review_auto_photo') and gb = 'plus' and member_seq = '".$this->userInfo['member_seq']."' and ordno  = '".$data['order_seq']."' and goods_review = '".$data['goods_seq']."' ");
					$emautopointck = $autoemoneysc->num_rows();
					if( !$emautopointck) {//자동지급안된경우
						if( $goods_review_point > 0 ) {
							$point['type']			= $type;
							$point['point']			=$goods_review_point;
							$point['gb']				= 'plus';
							$point['goods_review']= $data['goods_seq'];
							$point['goods_review_parent']= $goodsreviewparent;
							$point['ordno']			= $data['order_seq'];
							$point['memo']			= $goods_review_memo_point;
							$point['limit_date']	= $goods_review_point_limit_date;
							$this->membermodel->point_insert($point, $this->userInfo['member_seq']);
						}

						if($goods_review_point_add > 0 ) {
							$point['type']			= $type_add;
							$point['point']			=$goods_review_point_add;
							$point['gb']				= 'plus';
							$point['goods_review']= $data['goods_seq'];
							$point['goods_review_parent']= $goodsreviewparent;
							$point['ordno']			= $data['order_seq'];
							$point['memo']			= $goods_review_memo_point_add;
							$point['limit_date']	= $goods_review_point_limit_date_add;
							$this->membermodel->point_insert($point, $this->userInfo['member_seq']);
						}
					}

				}
			}
		}//endif
	}

	/* 상품후기 적립금/포인트 회수 */
	public function _goods_review_less($manager, $parentdata)
	{
		$this->load->model('membermodel');
		$this->load->model('emoneymodel');

		/************
		* 적립금 회수시작
		*************/
		$emautoemoneysc = $this->db->query("select * from fm_emoney where emoney_use !='less' and (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and ( (ordno  = '".$parentdata['order_seq']."' and goods_review = '".$parentdata['goods_seq']."' and (ordno != '' or ordno != 0 ) ) or ( goods_review_parent='".$parentdata['seq']."' ) ) ");
		$emautoemoneyar = $emautoemoneysc->result_array();
		if( $emautoemoneyar ) {
			foreach($emautoemoneyar as $emautoemoneyck=>$emautoemoney) {
				$board_less_emoney += $emautoemoney['emoney'];

				//지급>회수완료업데이트
				$this->db->where('emoney_seq',$emautoemoney['emoney_seq']);
				$this->db->update('fm_emoney',array('emoney_use'=>'less'));

			}//end foreach
		}

		//수동적립금 지급여부
		$joinsc['whereis'] = " and emoney_use !='less' and type = 'goods_review' and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and goods_review = '".$parentdata['seq']."' ";
		$joinsc['select']	= ' * ';
		$emjoinck = $this->emoneymodel->get_data($joinsc);
		if( $emjoinck ){
			$board_less_emoney += $emjoinck['emoney'];

			//지급>회수완료업데이트
			$this->db->where('emoney_seq',$emjoinck['emoney_seq']);
			$this->db->update('fm_emoney',array('emoney_use'=>'less'));
		}

		if( $board_less_emoney ) {
			$params = array(
				'gb'									=> 'minus',
				'type'								=> 'goods_review_less',
				'emoney'							=> $board_less_emoney,
				'goods_review'					=> $parentdata['goods_seq'],
				'goods_review_parent'	=> $parentdata['seq'],
				'memo'								=> "[회수]".$manager['name']." 삭제에 의한 적립금 차감",
			);
			$this->membermodel->emoney_insert($params, $parentdata['mseq']);
		}

		/************
		* 적립금 회수끝
		*************/

		/************
		* 포인트 회수시작
		*************/
		$emautopointsc = $this->db->query("select  * from fm_point where point_use !='less' and (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and ( (ordno  = '".$parentdata['order_seq']."' and goods_review = '".$parentdata['goods_seq']."' and (ordno != '' or ordno != 0 ) ) or ( goods_review_parent='".$parentdata['seq']."' ) ) ");
		$emautopointar = $emautopointsc->result_array();

		if( $emautopointar) {
			foreach($emautopointar as $emautopointck=>$emautopoint) {
				$board_less_point += $emautopoint['point'];

				//지급>회수완료업데이트
				$this->db->where('point_seq',$emautopoint['point_seq']);
				$this->db->update('fm_point',array('point_use'=>'less'));
			}//end foreach
		}

		if( $board_less_point ){
			$params = array(
				'gb'									=> 'minus',
				'type'								=> 'goods_review_less',
				'point'								=> $board_less_point,
				'goods_review'					=> $parentdata['goods_seq'],
				'goods_review_parent'	=> $parentdata['seq'],
				'memo'								=> "[회수]".$manager['name']." 삭제에 의한 포인트 차감",
			);
			$this->membermodel->point_insert($params, $parentdata['mseq']);
		}
		/************
		* 포인트 회수끝
		*************/

	}



	/* 상품후기외 수동적립금 회수 */
	public function _board_less($manager, $parentdata)
	{
		$this->load->model('membermodel');
		$this->load->model('emoneymodel');

		/************
		* 적립금 회수시작
		*************/

		//수동적립금 지급여부
		$joinsc['whereis'] = " and emoney_use !='less' and type = 'board_".$manager['id']."' and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and (goods_review = '".$parentdata['seq']."' or goods_review_parent = '".$parentdata['seq']."')  ";
		$joinsc['select']	= ' * ';
		$emjoinck = $this->emoneymodel->get_data($joinsc);
		if( $emjoinck ){
			$board_less_emoney += $emjoinck['emoney'];
			/**
				$params = array(
					'gb'									=> 'minus',
					'type'								=> 'board_'.$manager['id'].'_less',
					'emoney'							=> $emjoinck['emoney'],
					'goods_review'					=> $emjoinck['seq'],
					'memo'								=> "[회수]".$manager['name']." 삭제에 의한 적립금 차감",
				);
				$this->membermodel->emoney_insert($params, $parentdata['mseq']);
			**/

			//지급>회수완료업데이트
			$this->db->where('emoney_seq',$emjoinck['emoney_seq']);
			$this->db->update('fm_emoney',array('emoney_use'=>'less'));
		}

		if( $board_less_emoney ) {
			$params = array(
				'gb'									=> 'minus',
				'type'								=> 'board_'.$manager['id'].'_less',
				'emoney'							=> $board_less_emoney,
				'goods_review'					=> $parentdata['goods_seq'],
				'goods_review_parent'	=> $parentdata['seq'],
				'memo'								=> "[회수]".$manager['name']." 삭제에 의한 적립금 차감",
			);
			$this->membermodel->emoney_insert($params, $parentdata['mseq']);
		}

		/************
		* 적립금 회수끝
		*************/

	}
 	
	//게시글평가하기
	public function board_score_save()
	{ 
		$sc['whereis']	= ' and id= "'.BOARDID.'" ';
		$sc['select']		= ' * ';
		$this->manager = $this->Boardmanager->managerdataidck($sc);//게시판정보  
		if (!isset($this->manager['id'])) { 
			$result = array('result'=>false, 'msg'=>"존재하지 않는 게시판입니다."); 
			echo json_encode($result);
			exit;
		}

		// 로그인 체크
		$session_arr = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];
		if(!$session_arr['member_seq']){
			$result = array('result'=>false, 'msg'=>"회원만 사용가능합니다."); 
			echo json_encode($result);
			exit;
		}

		if( $this->manager['auth_recommend_use'] == 'Y' ) {
			$sc['whereis']	= ' and seq= "'.$_POST['parent'].'" ';
			$sc['select']		= ' seq, gid, comment, upload, depth, file_key_w  ';
			$data = $this->Boardmodel->get_data($sc);

			if(empty($data)) {
				$result = array('result'=>false, 'msg'=>"존재하지 않는 게시물입니다."); 
				echo json_encode($result);
				exit;
			}
			
			//권한체크
			//비밀글 > 비회원 또는 회원은 본인이 아닌 경우
			if($data['hidden'] == 1 && $data['notice'] == 0) {//공지글은 무조건보기가능
				$parentsql['whereis']	= ' and seq= "'.$data['parent'].'" ';
				$parentsql['select']		= '  seq, gid, comment, upload, depth, pw, mseq, mid, parent';
				$parentdata = $this->Boardmodel->get_data($parentsql);//게시물정보

				if( $data['mseq'] > 0  || ($data['parent'] && $parentdata['mseq'] > 0 ) ) {//회원이 쓴글인경우
					if( ( ($data['mseq'] != $this->userInfo['member_seq'] && $parentdata['mseq'] != $this->userInfo['member_seq']) && defined('__ISUSER__'))  || ( !defined('__ISUSER__') ) ) {//작성자가 아니거나 비회원인 경우
						if(!defined('__ISADMIN__')) {
							$result = array('result'=>false, 'msg'=>"평가권한이 없습니다."); 
							echo json_encode($result);
							exit;
						}
					}
				}
				else{
					// 비번입력후 브라우저를 닫기전까지는 접근가능함
					$ss_pwhidden_name = 'board_pwhidden_'.BOARDID;
					$boardpwhiddenss = $this->session->userdata($ss_pwhidden_name);
					if ( ( !strstr($boardpwhiddenss,'['.$_POST['parent'].']') && isset($boardpwhiddenss)) || empty($boardpwhiddenss)) {
						if(!defined('__ISADMIN__')) {
							$result = array('result'=>false, 'msg'=>"평가권한이 없습니다."); 
							echo json_encode($result);
							exit;
						}
					}
				}
			}

			get_auth($this->manager, $data, 'read', $isperm);//접근권한체크  
			if ( $isperm['isperm_read'] === false ) {
				$result = array('result'=>false, 'msg'=>"평가권한이 없습니다."); 
				echo json_encode($result);
				exit;
			} 
 
			$parentsql['whereis']	= ' and boardid= "'.$_POST['board_id'].'" '; 
			$parentsql['whereis']	.= ' and type= "board" ';
			$parentsql['whereis']	.= ' and parent= "'.$_POST['parent'].'" ';//게시글 
			if($_POST['cparent']) $parentsql['whereis']	.= ' and cparent= "'.$_POST['cparent'].'" ';//댓글 
			$parentsql['whereis']	.= ' and mseq= "'.$this->userInfo['member_seq'].'" ';
			$getscore = $this->Boardscorelog->get_data($parentsql);

			if(!$getscore) {
				//recommend/none_rec/recommend1/recommend2/recommend3/recommend4/recommend5
				$scoreid=  $_POST['scoreid'];
				$result = $this->Boardmodel->board_score_update($data['seq'], $scoreid,' + ');  
				 if( $result ) {
					$params['type']				= 'board';
					$params['boardid']			= $_POST['board_id'];
					$params['scoreid']			= $scoreid;
					$params['parent']			= $_POST['parent'];
					if($_POST['cparent']) $params['cparent']			= $_POST['cparent'];
					$params['mseq']			= $this->userInfo['member_seq'];
					$params['regist_date']	= date("Y-m-d H:i:s");
					$this->Boardscorelog->data_write($params);  

					$sc['whereis']	= ' and seq= "'.$_POST['parent'].'" '; 
					$sc['select']		= ' seq, gid, comment, upload, depth, file_key_w,'.$scoreid;
					$getscoredata = $this->Boardmodel->get_data($sc); 
					
					 $msg = "회원님의 평가가 반영되었습니다.";
				 }else{
					 $msg = "회원님의 평가가 실패되었습니다.";
				 }
			}else{
				 $msg = "이미 평가하신 게시글입니다.";
			}
		}else{
			 $msg = "잘못된 접근입니다.";
		} 

		if( $result ) {
			$result = array('result'=>true, 'msg'=>$msg, 'scoreid'=>$getscoredata[$scoreid]);
		}else{
			$result = array('result'=>false, 'msg'=>$msg);
		}

		echo json_encode($result);
		exit;
	}

}

/* End of file board_process.php */
/* Location: ./app/controllers/admin/board_process.php */