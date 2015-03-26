<?php
/**
 * 게시글 관련 관리자 process
 * @author gabia
 * @since version 2.0 - 2012.06.29
 */
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class Board_process extends admin_base {

	public function __construct() {
		parent::__construct();


		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('board_act');
		if(!$auth){
			if($_POST['mode']) { 
				$return = array('result'=>false, 'name'=>$manager['name'], 'msg'=>$this->auth_msg);
				echo json_encode($return); 
			}else{
				$this->admin_menu();
				$this->tempate_modules();
				$callback = "history.go(-1);";
				$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
				$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
				$this->template->print_("denined");
			}
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

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
		$mode = (!empty($_POST['mode']))?$_POST['mode']:$_GET['mode'];

		$sc['whereis']	= ' and id= "'.BOARDID.'" ';
		$sc['select']		= ' * ';
		$manager = $this->Boardmanager->managerdataidck($sc);//게시판정보
		if (!isset($manager['id'])) {
			openDialogAlert("존재하지 않는 게시판입니다.",400,140,'parent','');
			exit;
		}
		boarduploaddir($manager);//폴더생성 및 스킨 복사

		/* 게시글등록 */
		if($mode == 'board_write') {

			### //넘어온 추가항목 seq
			if( BOARDID == 'bulkorder'  ||  BOARDID == 'goods_review' ) {
				$label_pr = $_POST['label'];
				$label_sub_pr = $_POST['labelsub'];
				$label_required = $_POST['required'];
				foreach($label_pr as $l => $data){$label_arr[]=$l;}
			}

			if( $_POST['subject'] == '제목을 입력해 주세요' ) {
				$_POST['subject'] = '';
			}

			if($_POST['contents'] == "<p><br></p>") {
				$_POST['contents'] = "";
			}

			### Validation
			$this->validation->set_rules('subject', '제목','trim|required|xss_clean');
			$this->validation->set_rules('contents', '내용','trim|required'); 

			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();parent.setDefaultText();parent.submitck();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}


			if( $_POST['notice'] && $_POST['onlynotice'] == 1 ){
				if(strtotime($_POST['onlynotice_sdate']) > strtotime($_POST['onlynotice_edate'])){
					$callback = "parent.$('#onlynotice_edate').focus();";
					openDialogAlert("기간 종료일이 시작일보다 이후에 있어야 합니다.<br/>날짜를 조정해 주세요.",450,180,'parent',$callback);
					exit;
				}
			}

			$params['boardid']		=  BOARDID;
			$params['notice']			=  if_empty($_POST, 'notice', '0');//공지
			$params['hidden']		=  if_empty($_POST, 'hidden', '0');//비밀글

			$params['subject']		=  $_POST['subject'];
			$params['editor']			=  ($_POST['daumedit'])?1:0;//모바일
			$params['name']			=  (!empty($_POST['name']))?$_POST['name']:'';
			$params['category']		=  (!empty($_POST['category']))?htmlspecialchars($_POST['category']):'';

			//신규분류
			if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
				$params['category'] = htmlspecialchars($_POST['newcategory']);
			}
			$params['contents']		=  $_POST['contents'];

			$params['re_contents'] = $_POST['re_contents'];//1:1문의 답변시
			$params['re_contents'] = adjustEditorImages($_POST['re_contents'], $this->Boardmodel->upload_src);
			if($this->_is_mobile_agent){//모바일인경우 text
				$params['re_contents'] = nl2br($params['re_contents']);
			}
			$params['re_subject'] = $_POST['re_subject'];//1:1문의 답변시
			if($_POST['reply'] && $_POST['re_subject']) $params['re_date']		= date("Y-m-d H:i:s");

			if( !empty($_POST['pw']) ) $params['pw']  = md5($_POST['pw']);//값이 잇는경우에만 변경

			if(!empty($_POST['email'])) $params['email']=($_POST['email']);
			if(!empty($_POST['tel1'])) $params['tel1']=($_POST['tel1']);
			if(!empty($_POST['tel2'])) $params['tel2']=($_POST['tel2']);

			if(!empty($_POST['board_sms_hand'])) $params['tel1']=($_POST['board_sms_hand']);
			if(!empty($_POST['board_sms_email'])) $params['email']=($_POST['board_sms_email']);

			if(!empty($_POST['board_sms']))		$params['rsms']='Y';
			if(!empty($_POST['board_email']))	$params['remail']='Y';

			if( !empty($_POST['score']) )		$params['score']  = ($_POST['score']);//값이 잇는경우에만 변경
			if( !empty($_POST['score_avg']) )	$params['score_avg'] = $_POST['score_avg'];

			if (isset($_POST['seq']) ) {//답변시

				$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
				$parentsql['select']		= '  seq, gid, comment, upload, depth, subject, contents, r_date, parent, pw, hidden, name ';
				$parentdata = $this->Boardmodel->get_data($parentsql);
				if(empty($parentdata)) {
					$callback = "parent.document.location.href='".$this->Boardmanager->realboardurl.BOARDID."';";
					openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
					exit;
				}

				$parentsql['whereis']	= ' and gid >= '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1);
				//$parentsql['select']		= ' gid ';
				$parentrumrow = $this->Boardindex->get_data_numrow($parentsql);
				if($parentrumrow>98) {
					openDialogAlert("죄송합니다. 더이상 답변을 달 수 없습니다.",400,140,'parent','');
					exit;
				}

				$gidup['set']				= ' gid=gid+0.01 ';
				$gidup['whereis']		= ' gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1);
				$this->Boardmodel->data_gid_save($gidup);//data gid update
				$this->Boardindex->data_gid_save($gidup);//idx gid update

				$params['parent']	= $_POST['seq'];
				$params['gid']			= $parentdata['gid']+0.01;
				$params['depth']		= $parentdata['depth']+1;

				$params['pw']			= $parentdata['pw'];//부모글비밀번호입니다.

				if(!empty($_POST['board_sms']) || !empty($_POST['board_email']) ) {//답변시 본래글의 휴대폰/이메일정보 업데이트
					if(!empty($_POST['board_sms']))		$parentdataupsmsemail['rsms']='Y';
					if(!empty($_POST['board_email']))	$parentdataupsmsemail['remail']='Y';

					if(!empty($_POST['board_sms_hand'])) $parentdataupsmsemail['tel1']=($_POST['board_sms_hand']);
					if(!empty($_POST['board_sms_email'])) $parentdataupsmsemail['email']=($_POST['board_sms_email']);
					$parentdataupsmsemail['seq'] = $_POST['seq'];
					$this->Boardmodel->data_modify($parentdataupsmsemail);
				}


			}else{//새글
				$minsql['whereis']	= ' ';
				$minsql['select']		= ' min(gid) as mingid ';
				$mindata = $this->Boardmodel->get_data($minsql);
				$parentgid = $mindata['mingid'] ? $mindata['mingid']-1 : 100000000.00;
				$params['parent']	= 0;
				$params['gid']			= $parentgid;
				$params['depth']		= 0;
			}

			if( defined('__SELLERADMIN__') === true ) {
				$params['mseq']			= '-'.$this->providerInfo['provider_seq'];//입점사$this->providerInfo['provider_seq'];
				$params['mid']				= $this->providerInfo['provider_id'];
			}else{
				$params['mseq']			= '-1';
				$params['mid']				= $this->managerInfo['manager_id'];
			}
 			$params['r_date']			= date("Y-m-d H:i:s");
			$params['m_date']			= date("Y-m-d H:i:s");
			$params['test11']			= $_POST['test11'];
			$params["ip"]					= $_SERVER['REMOTE_ADDR'];
			$params["agent"]			= $_SERVER['HTTP_USER_AGENT'];

			$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';
			$params['contents'] = adjustEditorImages($params['contents'], $this->Boardmodel->upload_src);// /data/tmp 임시폴더변경 /data/editor
			if($this->_is_mobile_agent){//모바일인경우 text
				$params['contents'] = nl2br($params['contents']);
			}

			//첨부파일추가
			foreach($_FILES as $key => $value)
			{
				for ( $num=0;$num<count($_FILES['file_info']['name']);$num++) {
					if( ! empty($value['name'][$num])){
						$folder			= $this->Boardmodel->upload_path;
						$tmpname	= $value['tmp_name'][$num];
						$file_ext		= end(explode('.', $value['name'][$num]));//확장자추출
						$file_name	= str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
						$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
						$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
						$saveFile		= $folder.$file_name;
						$tmp = getimagesize($value['tmp_name']);
						if(!$tmp['mime']){
							$_FILES['Filedata']['type'] = $file_ext;//확장자추출
						}else{
							$_FILES['Filedata']['type'] = $tmp['mime'];
						}


						$fileresult = board_upload($key, $file_name, $folder, $conf, $saveFile, $num);//status  error, fileInfo
						if( $fileresult['status'] == 1 ) {

							if(is_array($realfilename)) {
								$usefile = false;
								foreach($realfilename as $realfile) {
									$realfilear = @explode("^^",$realfile);
									if($realfilear[0] == $file_name) {$usefile=true;break;}
								}
								if(!$usefile) {
									$realfilename[] = $file_name."^^".$value['name'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];
								}
							} else {
								$realfilename[] = $file_name."^^".$value['name'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];
							}

						}
					}
				}
			}

			//에딧터이미지파일용
			$filenamenumber=0;
			if(!empty($_POST['tx_attach_files'])) {
				if(is_array($_POST['tx_attach_files'])){
						//array_unique($_POST['tx_attach_files']);array_unique($_POST['tx_attach_files_name']);
					foreach($_POST['tx_attach_files'] as $tx_attach_file){
						//$editerimg = preg_replace("/^\/data\/tmp\//","",$tx_attach_file);
						$editerimg = end(explode('/', $tx_attach_file));//확장자추출
						if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$editerimg)) {//데이타이전->한글파일명처리
							$editerimgkorean = iconv('utf-8','cp949',$editerimg);
						}

						$client_name = ($_POST['tx_attach_files_name'][$filenamenumber])?$_POST['tx_attach_files_name'][$filenamenumber]:$editerimg;
						if( @is_file($this->Boardmodel->upload_path.$editerimg) ||  @is_file($this->Boardmodel->upload_path.$editerimgkorean) ) {

							@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimg,$this->Boardmodel->upload_path.'/'.'_thumb_'.$editerimg);//파일복사
							@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimgkorean,$this->Boardmodel->upload_path.'/'.'_thumb_'.$editerimgkorean);//파일복사

							$filesizetmp = @filesize($this->Boardmodel->upload_path.$editerimg);
							$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$editerimg);
							if(!$filetypetmp['mime']){
								$typefile =end(explode('.', $editerimg));//확장자추출
							}else{
								$typefile =$filetypetmp['mime'];
							}


							if(is_array($realfilename)) {
								$usefile = false;
								foreach($realfilename as $realfile) {
									$realfilear = @explode("^^",$realfile);
									if($realfilear[0] == $editerimg) {$usefile=true;break;}
								}
								if(!$usefile) {
									$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
								}
							} else {
								$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
							}
						}else{
							if ( @is_file($this->Boardmanager->board_tmp_dir.$editerimg) || @is_file($this->Boardmanager->board_tmp_dir.$editerimgkorean) ) {

								@rename($this->Boardmanager->board_tmp_dir.$editerimg,$this->Boardmodel->upload_path.'/'.$editerimg);//파일복사
								@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimg,$this->Boardmodel->upload_path.'/'.'_thumb_'.$editerimg);//파일복사

								@rename($this->Boardmanager->board_tmp_dir.$editerimgkorean,$this->Boardmodel->upload_path.'/'.$editerimgkorean);//파일복사
								@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimgkorean,$this->Boardmodel->upload_path.'/'.'_thumb_'.$editerimgkorean);//파일복사


								$filesizetmp = @filesize($this->Boardmodel->upload_path.$editerimg);
								$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$editerimg);
								if(!$filetypetmp['mime']){
									$typefile =end(explode('.', $editerimg));//확장자추출
								}else{
									$typefile =$filetypetmp['mime'];
								}


								if(is_array($realfilename)) {
									$usefile = false;
									foreach($realfilename as $realfile) {
										$realfilear = @explode("^^",$realfile);
										if($realfilear[0] == $editerimg) {$usefile=true;break;}
									}
									if(!$usefile) {
										$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
									}
								} else {
									$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
								}
							}
						}
						$filenamenumber++;
					}
				}
			}

			if(isset($realfilename)){
				$params['upload'] = @implode("|",$realfilename);
			}else{
				$params['upload'] = '';//초기화
			}

			$params['tel1'] = $_POST['board_sms_hand'];
			$params['email'] = $_POST['board_sms_email'];

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
						}elseif($k == '3' ){//
							$params['person_tel1']	= $subdata;
						}elseif($k == '4' ){//
							$params['person_tel2']	= $subdata;
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
			}

			//동영상연동
			if($_POST['file_key_w']) $params['file_key_w'] = $_POST['file_key_w'];//웹 인코딩 코드
			if($_POST['file_key_i']) $params['file_key_i'] = $_POST['file_key_i'];//스마트폰 인코딩 코드
			if($this->session->userdata('boardvideotmpcode') && $params['file_key_w'] )
				$params['videotmpcode'] = $this->session->userdata('boardvideotmpcode');//코드

			if(  BOARDID == 'onlinepromotion' || BOARDID == 'offlinepromotion'  ){//online, offline promotion
				$params['m_date']		= (isset($_POST['start_date']))?($_POST['start_date']." 00:00:00"):'0000-00-00 00:00:00';
				$params['d_date']		= (isset($_POST['end_date']))?($_POST['end_date']." 23:59:59"):'0000-00-00 00:00:00';
				$params['adddata'] 				= "_thumb_".$_POST['adddata'];
				if(!empty($_POST['adddata']) && @is_file(ROOTPATH."data/tmp/".$params['adddata'])) {//rename
					@rename(ROOTPATH."data/tmp/".$params['adddata'], $this->Boardmodel->upload_path."/".$params['adddata']);
					@chmod($this->Boardmodel->upload_path."/".$params['adddata'],0707);
				}else{
					if($_POST['adddata']) $params['adddata'] 				= $_POST['adddata'];
				}
			}

			//상품후기/문의->상품정보변경
			if (isset($_POST['displayGoods']) && is_array($_POST['displayGoods'])) {
				$params['goods_seq']				=  implode(",",$_POST['displayGoods']); 
			}


			if($_POST['displayGoods']) {//상품관련
				$this->load->model('goodsmodel');
				foreach($_POST['displayGoods'] as $displayGoods){
					$goods = $this->goodsmodel->get_goods($displayGoods);
					$providerseq[] = $goods['provider_seq'];
				}
				$params['provider_seq']				=  (isset($providerseq) && is_array($providerseq))?implode(",",$providerseq):'';
			}

			if( $_POST['notice']){
				$params['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
			}else{
				$params['onlynotice']			=	0;//공지영역만 노출여부
			}

			$result = $this->Boardmodel->data_write($params);
			if($result) {
				$newseq = $result;

				//동영상관리
				$this->load->model('videofiles');
				if( $params['file_key_w'] ){
					$videofiles['file_key_w']			= $params['file_key_w'];
					$videofiles['parentseq']			= $newseq;
					$videofiles['type']						= BOARDID;
					$videofiles['upkind']					= 'board';
					$this->videofiles->videofiles_modify_key($videofiles);
				}

				if( BOARDID == 'goods_review' ) {//상품후기 건수/평점
					goods_review_count($params, $newseq); 
				}elseif( BOARDID == 'goods_qna' ) {//상품문의 건수
					goods_qna_count($params, $newseq);
				}

				if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
					$upmanagerparams['category']		= $manager['category'].",".$_POST['newcategory'];
					$upmanagerparams['category']		= str_replace(",,",",",$upmanagerparams['category']);
					//카테고리추가하기
				}

				//게시글수save
				$upmanagerparams['totalnum']		= $manager['totalnum']+1;
				$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//게시글증가

				//공지 Boardindex
				$idxparams['hidden']	= $params['hidden'];//비밀글여부
				$idxparams['notice']		= $params['notice'];//공지여부
				if( $params['notice']){
					$idxparams['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
					$idxparams['onlynotice_sdate']		= $_POST['onlynotice_sdate'];//공지노출 시작일
					$idxparams['onlynotice_edate']		= $_POST['onlynotice_edate'];//공지노출 완료일
				}else{
					$idxparams['onlynotice']			=	0;//공지영역만 노출여부
					$idxparams['onlynotice_sdate']		= '';//공지노출 시작일
					$idxparams['onlynotice_edate']		= '';//공지노출 완료일
				}

				$idxparams['gid']			= $params['gid'];//고유번호
				$idxparams['boardid']	= $params['boardid'];//id
				$this->Boardindex->idx_write($idxparams);

				if ($params['gid'] == '100000000.00')
				{
					$this->Boardmodel->get_data_optimize();
					$this->Boardindex->get_data_optimize();
				}

				//$sendsmscase = ( BOARDID == 'goods_qna')?'qna':(BOARDID == 'mbqna')?'cs':'board_reply';
				if ( !empty($_POST['board_sms']) && !empty($_POST['board_sms_hand']) && $_POST['reply'] ) {//답변시
					$smsparams['msg']		= $_POST["sms_content1"];
					$this->load->model('membermodel');
					$minfo = $this->membermodel->get_member_data($parentdata['mseq']);
					$smsparams['userid']			 = ($minfo['member_seq'])?$minfo['userid']:$parentdata['name'];//비회원은 작성자명
					$smsparams['user_name']	 = ($minfo['member_seq'])?$minfo['user_name']:$parentdata['name'];//작성자명

					$smsparams['board_name'] = $manager['name'];//게시판명
					$commonSmsData[BOARDID."_reply"]['phone'][] = $_POST['board_sms_hand'];
					$commonSmsData[BOARDID."_reply"]['mid'][] = $minfo['userid'];
					$commonSmsData[BOARDID."_reply"]['params'][] = $smsparams;

					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}
					$params['tel1'] = $_POST['board_sms_hand'];
				}

				//회원정보체크
				$this->load->model('membermodel');
				if($parentdata['mseq']) $emoneyminfo = $this->membermodel->get_member_data($parentdata['mseq']);//본래글작성자체크

				if(!empty($emoneyminfo) && $_POST['board_emoney'] && $_POST['board_memo']) { //회원정보체크
					if( BOARDID == 'goods_review' ) {//상품후기
						$emoneyboardparams['type']			= 'goods_review';
					}else{
						$emoneyboardparams['type']			= 'board_'.BOARDID;//table
					}
					$emoneyboardparams['gb']				= 'plus';

					$emoneyboardparams['goods_review']	= (isset($_POST['seq']) ) ?$_POST['seq']:$newseq;//답변시에는 본래글에
					$emoneyboardparams['member_seq']	= $emoneyminfo['member_seq'];
					$emoneyboardparams['manager_seq']	= $this->managerInfo['manager_seq'];

					$emoneyboardparams['emoney']		= $_POST['board_emoney'];
					$emoneyboardparams['memo']		= $_POST['board_memo'];
					if($_POST['board_reserve_select']=='year'){
						$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['board_reserve_year']));
					}else if($_POST['board_reserve_select']=='direct'){
						$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['board_reserve_direct'], date("d"), date("Y")));
					}
					$emoneyboardparams['limit_date']	= $limit_date;
					$this->membermodel->emoney_insert($emoneyboardparams, $emoneyminfo['member_seq']);
				}

				$sendemailcase = ( BOARDID == 'goods_qna' || BOARDID == 'mbqna' )?'cs':'board_reply';
				if ( !empty($_POST['board_email']) && !empty($_POST['board_sms_email']) && $_POST['reply'] ) {//답변시

					$emailparams['subject']		= $parentdata["subject"];
					$emailparams['contents']		= $parentdata["contents"];
					$emailparams['r_date']		= $parentdata["r_date"];

					$emailparams['re_subject']		= (!empty($_POST['re_subject']))?$_POST['re_subject']:$_POST["subject"];
					$emailparams['re_contents']		= (!empty($params["re_contents"]))?$params["re_contents"]:$params["contents"];
					$emailparams['re_date']		= date("Y-m-d H:i:s");
					$this->load->model('membermodel');
					$emailparamsinfo = $this->membermodel->get_member_data($parentdata['mseq']);
					$emailparams['userid']			 = ($emailparamsinfo['member_seq'])?$emailparamsinfo['userid']:$parentdata['name'];
					$emailparams['user_name'] = ($emailparamsinfo['member_seq'])?$emailparamsinfo['user_name']:$parentdata['name'];
					$email = sendMail($_POST['board_sms_email'], $sendemailcase, $emailparamsinfo['userid'] , $emailparams);
				}

				$this->session->unset_userdata('backtype');
				$_POST['backtype'] = (!empty($_POST['backtype']))?($_POST['backtype']):'list';
				$this->session->set_userdata('backtype',$_POST['backtype']);
				if( defined('__SELLERADMIN__') === true ) {
					if( BOARDID == 'gs_seller_qna' ) {//입점사문의게시판
						$sendsmscase = 'gs_seller_qna_write';
						$this->load->model('membermodel');
						$commonSmsData[$sendsmscase]['phone'][] = $_POST['tel1'];
						$commonSmsData[$sendsmscase]['mid'][] = $this->providerInfo['provider_id'];
						$commonSmsData[$sendsmscase]['params'][] = $smsparams;

						if(count($commonSmsData) > 0){
							commonSendSMS($commonSmsData);
						}
					}
				}
				if($_POST['backtype'] == 'list') {
					$callback = ($_POST['returnurl'])?"parent.document.location.href='".$_POST['returnurl']."';":"parent.document.location.href='".$this->Boardmanager->realboardurl.BOARDID."';";
				} elseif($_POST['backtype'] == 'view') {
					$callback = ($_POST['returnurl'])?"parent.boardaddFormDialog('".$_POST['returnurl']."&seq=".$newseq."', '80%', '800', '게시글 보기','false');":"parent.boardaddFormDialog('".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$newseq."', '80%', '800', '게시글 보기','false');";
				} else {
					$callback = "parent.boardaddFormDialog('".$this->Boardmanager->realboardwriteurl.BOARDID."', '90%', '800', '게시글 등록','false');";
				}
				openDialogAlert("게시글을 등록 하였습니다.",400,140,'parent',$callback);
			}else{
				openDialogAlert("게시글 등록에 실패되었습니다.",400,140,'parent','');
			}
			exit;
		}

		/* 게시글수정 */
		elseif($mode == 'board_modify') {

			if( $_POST['notice'] && $_POST['onlynotice'] == 1 ){
				if(strtotime($_POST['onlynotice_sdate']) > strtotime($_POST['onlynotice_edate'])){
					$callback = "parent.$('#onlynotice_edate').focus();";
					openDialogAlert("기간 종료일이 시작일보다 이후에 있어야 합니다.<br/>날짜를 조정해 주세요.",450,180,'parent',$callback);
					exit;
				}
			}

			if( $_POST['reply'] ) {//답변시
				if($_POST['re_contents'] == '<p><br></p>') {
					$_POST['re_contents'] = "";
				}
				### Validation
				$this->validation->set_rules('re_subject', '제목','trim|required|xss_clean');
				$this->validation->set_rules('re_contents', '내용','trim|required'); 
			}else{
				if($_POST['contents'] == '<p><br></p>') {
					$_POST['contents'] = "";
				}
				### Validation
				$this->validation->set_rules('subject', '제목','trim|required|xss_clean');
				$this->validation->set_rules('contents', '내용','trim|required');
			}

			if($this->validation->exec()===false){
			   $err = $this->validation->error_array;
			   $callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();parent.setDefaultText();parent.submitck();";
			   openDialogAlert($err['value'],400,140,'parent',$callback);
			   exit;
			}

			### //넘어온 추가항목 seq
			if( BOARDID == 'bulkorder'  ||  BOARDID == 'goods_review' ) {
				$label_pr = $_POST['label'];
				$label_sub_pr = $_POST['labelsub'];
				$label_required = $_POST['required'];
				foreach($label_pr as $l => $data){$label_arr[]=$l;}
			}

			$parentsql['whereis']	= ' and seq= "'.$_POST['seq'].'" ';
			$parentsql['select']		= ' seq, gid, comment, upload, depth , mseq, subject, contents, r_date, parent, file_key_w, name , goods_seq';
			$parentdata = $this->Boardmodel->get_data($parentsql);//게시판목록
			if(empty($parentdata)) {
				$callback = "parent.document.location.href='".$this->Boardmanager->realboardurl.BOARDID."';";
				openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
				exit;
			}

			$params['boardid']		=  BOARDID;
			$params['notice']			=  if_empty($_POST, 'notice', '0');//공지
			$params['hidden']		=  if_empty($_POST, 'hidden', '0');//비밀글

			$params['subject']		=  $_POST['subject'];
			$params['editor']			=  ($_POST['daumedit'])?1:0;//모바일
			$params['name']			=  (!empty($_POST['name']))?$_POST['name']:'';
			$params['category']		= (!empty($_POST['category']))?htmlspecialchars($_POST['category']):'';
			$params['contents']		=  $_POST['contents'];

			if( !empty($_POST['pw']) ) {
				$params['pw']  = md5($_POST['pw']);
			}elseif( !empty($_POST['oldpw']) ) {
				$params['pw']  = $_POST['oldpw'];
			}

			if(!empty($_POST['email'])) $params['email']=($_POST['email']);
			if(!empty($_POST['tel1'])) $params['tel1']=($_POST['tel1']);
			if(!empty($_POST['tel2'])) $params['tel2']=($_POST['tel2']);

			if(!empty($_POST['board_sms_hand'])) $params['tel1']=($_POST['board_sms_hand']);
			if(!empty($_POST['board_sms_email'])) $params['email']=($_POST['board_sms_email']);


			if(!empty($_POST['board_sms']))		$params['rsms']='Y';
			if(!empty($_POST['board_email']))	$params['remail']='Y';

			if( !empty($_POST['score']) ) $params['score']  = ($_POST['score']);//값이 잇는경우에만 변경

			if(!empty($_POST['re_contents']))  {
				$params['re_contents'] = $_POST['re_contents'];//1:1문의 답변시
				$params['re_contents'] = adjustEditorImages($_POST['re_contents'], $this->Boardmodel->upload_src);
				if($this->_is_mobile_agent){//모바일인경우 text
					$params['re_contents'] = nl2br($params['re_contents']);
				}
			}
			if(!empty($_POST['re_subject'])) $params['re_subject']=($_POST['re_subject']);
			if($_POST['re_subject']) $params['re_date']		= date("Y-m-d H:i:s");
			if( defined('__SELLERADMIN__') === true ) {
				if($parentdata['mseq']  < -1  )$params['m_date']		= date("Y-m-d H:i:s");
			}else{
			if($parentdata['mseq'] == '-1' )$params['m_date']		= date("Y-m-d H:i:s");
			}

			if( !($_POST['re_subject']) && $parentdata['mseq'] == '-1' )  {//답변이 아닌경우
				$params["ip"]				= $_SERVER['REMOTE_ADDR'];
				$params["agent"]		= $_SERVER['HTTP_USER_AGENT'];
			}

			$_REQUEST['tx_attach_files'] = (!empty($_POST['tx_attach_files'])) ? $_POST['tx_attach_files']:'';

			//(/data/tmp 임시폴더에서 게시판폴더로 이동변경 $this->Boardmodel->upload_src
			$params['contents'] = adjustEditorImages($_POST['contents'], $this->Boardmodel->upload_src);
			if($this->_is_mobile_agent){//모바일인경우 text
				$params['contents'] = nl2br($params['contents']);
			}

			//이미등록된 첨부파일 변경시
			if(!empty($_POST['orignfile_info'])) {
				$oldfile = @explode("|",$parentdata['upload']);
				for ( $num=0;$num<count($_POST['orignfile_info']);$num++) {
					$oldrealfile = @explode("^^",$_POST['orignfile_info'][$num]);
					if(@in_array($_POST['orignfile_info'][$num],$oldfile) && @is_file($this->Boardmodel->upload_path.$oldrealfile[0]) && is_uploaded_file($_FILES['file_info']['tmp_name'][$num]) ){//기존위치에 수정시 변경

						if( ! empty($_FILES['file_info']['tmp_name'][$num])) {
							$folder			= $this->Boardmodel->upload_path;
							$tmpname	= $_FILES['file_info']['tmp_name'][$num];
							$file_ext		= end(explode('.', $_FILES['file_info']['name'][$num]));//확장자추출
							$file_name	= str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
							$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
							$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
							$saveFile		= $folder.$file_name;
							$tmp = getimagesize($_FILES['file_info']['tmp_name'][$num]);
							$_FILES['Filedata']['type'] = $tmp['mime'];

							$fileresult = board_upload('file_info', $file_name, $folder, $conf, $saveFile, $num);//status  error, fileInfo
							if( $fileresult['status'] == 1 ) {

								if(is_array($realfilename)) {
									$usefile = false;
									foreach($realfilename as $realfile) {
										$realfilear = @explode("^^",$realfile);
										if($realfilear[0] == $file_name) {$usefile=true;break;}
									}
									if(!$usefile) {
										$realfilename[] = $file_name."^^".$_FILES['file_info']['name'][$num]."^^".$_FILES['file_info']['size'][$num]."^^".$_FILES['file_info']['type'][$num];
									}
								} else {
									$realfilename[] = $file_name."^^".$_FILES['file_info']['name'][$num]."^^".$_FILES['file_info']['size'][$num]."^^".$_FILES['file_info']['type'][$num];
								}

								//@unlink($this->Boardmodel->upload_path.$oldrealfile[0]);//기존위치의 파일삭제
								//@unlink($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0]);//기존위치의 파일삭제
							}
						}
					}

					elseif ( @in_array($_POST['orignfile_info'][$num],$oldfile) && @is_file($this->Boardmodel->upload_path.$oldrealfile[0]) && !is_uploaded_file($_FILES['file_info']['tmp_name'][$num]) ) {
						$realfilename[] = $_POST['orignfile_info'][$num];
					}
				}
			}

			//새로등록하는 첨부파일용
			foreach($_FILES as $key => $value)
			{
				for ( $num=0;$num<count($_FILES['file_info']['name']);$num++) {
					if(  !$_POST['orignfile_info'][$num] && !empty($value['name'][$num])){
						$folder			= $this->Boardmodel->upload_path;
						$tmpname	= $value['tmp_name'][$num];
						$file_ext		= end(explode('.', $value['name'][$num]));//확장자추출
						$file_name	= str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
						$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
						$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
						$saveFile		= $folder.$file_name;

						$tmp = getimagesize($value['tmp_name']);
						if(!$tmp['mime']){
							$_FILES['Filedata']['type'] = $file_ext;//확장자추출
						}else{
							$_FILES['Filedata']['type'] = $tmp['mime'];
						}

						$fileresult = board_upload($key, $file_name, $folder, $conf, $saveFile, $num);//status  error, fileInfo
						if( $fileresult['status'] == 1 ) {

							if(is_array($realfilename)) {
								$usefile = false;
								foreach($realfilename as $realfile) {
									$realfilear = @explode("^^",$realfile);
									if($realfilear[0] == $file_name) {$usefile=true;break;}
								}
								if(!$usefile) {
									$realfilename[] = $file_name."^^".$value['name'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];
								}
							} else {
								$realfilename[] = $file_name."^^".$value['name'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];
							}
						}
					}
				}
			}

			//에딧터이미지파일용
			$filenamenumber=0;
			if(!empty($_POST['tx_attach_files'])) {
				if(is_array($_POST['tx_attach_files'])){
						//array_unique($_POST['tx_attach_files']);array_unique($_POST['tx_attach_files_name']);
					foreach($_POST['tx_attach_files'] as $tx_attach_file){
						//$editerimg = preg_replace("/^\/data\/tmp\//","",$tx_attach_file);
						$editerimg = end(explode('/', $tx_attach_file));//확장자추출
						if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$editerimg)) {//데이타이전->한글파일명처리
							$editerimgkorean = iconv('utf-8','cp949',$editerimg);
						}

						$client_name = ($_POST['tx_attach_files_name'][$filenamenumber])?$_POST['tx_attach_files_name'][$filenamenumber]:$editerimg;
						if( @is_file($this->Boardmodel->upload_path.$editerimg) ||  @is_file($this->Boardmodel->upload_path.$editerimgkorean) ) {

							@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimg,$this->Boardmodel->upload_path.'/'.'_thumb_'.$editerimg);//파일복사
							@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimgkorean,$this->Boardmodel->upload_path.'/'.'_thumb_'.$editerimgkorean);//파일복사

							$filesizetmp = @filesize($this->Boardmodel->upload_path.$editerimg);
							$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$editerimg);
							if(!$filetypetmp['mime']){
								$typefile =end(explode('.', $editerimg));//확장자추출
							}else{
								$typefile =$filetypetmp['mime'];
							}


							if(is_array($realfilename)) {
								$usefile = false;
								foreach($realfilename as $realfile) {
									$realfilear = @explode("^^",$realfile);
									if($realfilear[0] == $editerimg) {$usefile=true;break;}
								}
								if(!$usefile) {
									$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
								}
							} else {
								$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
							}
						}else{
							if ( @is_file($this->Boardmanager->board_tmp_dir.$editerimg) || @is_file($this->Boardmanager->board_tmp_dir.$editerimgkorean) ) {
								@rename($this->Boardmanager->board_tmp_dir.$editerimg,$this->Boardmodel->upload_path.'/'.$editerimg);//파일복사
								@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimg,$this->Boardmodel->upload_path.'/_thumb_'.$editerimg);//파일복사

								@rename($this->Boardmanager->board_tmp_dir.$editerimgkorean,$this->Boardmodel->upload_path.'/'.$editerimgkorean);//파일복사
								@rename($this->Boardmanager->board_tmp_dir.'_thumb_'.$editerimgkorean,$this->Boardmodel->upload_path.'/_thumb_'.$editerimgkorean);//파일복사

								$filesizetmp = @filesize($this->Boardmodel->upload_path.$editerimg);
								$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$editerimg);
								if(!$filetypetmp['mime']){
									$typefile =end(explode('.', $editerimg));//확장자추출
								}else{
									$typefile =$filetypetmp['mime'];
								}


								if(is_array($realfilename)) {
									$usefile = false;
									foreach($realfilename as $realfile) {
										$realfilear = @explode("^^",$realfile);
										if($realfilear[0] == $editerimg) {$usefile=true;break;}
									}
									if(!$usefile) {
										$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
									}
								} else {
									$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
								}
							}
						}
						$filenamenumber++;
					}
				}
			}

			if($_POST['reply'] == 'y' && (BOARDID == 'store_reservation' || BOARDID == 'mbqna' || BOARDID == 'goods_qna' || BOARDID == 'bulkorder' || BOARDID == 'gs_seller_qna') ) {//답변시 원글의 첨부파일포함되도록
				if(isset($realfilename)){
					$params['re_upload'] = @implode("|",$realfilename);
				}else{
					$params['re_upload'] = '';//초기화
				}
			}else{
				if(isset($realfilename)){
					$params['upload'] = @implode("|",$realfilename);
				}else{
					$params['upload'] = '';//초기화
				}
			}

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
					$params['score_avg'] =  round(($scoresum/($scorecnt*5))*100);//round(( ($scoresum*($scorecnt*5))/$scorecnt )); 
				}
			}

			//상품후기/문의-> 답변시상품정보변경
			if (isset($_POST['displayGoods']) && is_array($_POST['displayGoods'])) {
				$params['goods_seq']				=  implode(",",$_POST['displayGoods']); 
			}


			if($_POST['displayGoods']) {//상품관련
				$this->load->model('goodsmodel');
				foreach($_POST['displayGoods'] as $displayGoods){
					$goods = $this->goodsmodel->get_goods($displayGoods);
					$providerseq[] = $goods['provider_seq'];
				}
				$params['provider_seq']				=  (isset($providerseq) && is_array($providerseq))?implode(",",$providerseq):'';
			}

			//동영상
			if($_POST['video_del'] == 1) $params['file_key_w'] = '';//원본파일코드초기화
			if($_POST['file_key_w']) $params['file_key_w'] = $_POST['file_key_w'];//웹 인코딩 코드
			if($_POST['file_key_i']) $params['file_key_i'] = $_POST['file_key_i'];//스마트폰 인코딩 코드

			if($this->session->userdata('boardvideotmpcode') && $params['file_key_w'] )
				$params['videotmpcode'] = $this->session->userdata('boardvideotmpcode');//코드

			if(  BOARDID == 'onlinepromotion' || BOARDID == 'offlinepromotion'  ){//online, offline promotion
				$params['m_date']		= (isset($_POST['start_date']))?($_POST['start_date']." 00:00:00"):'0000-00-00 00:00:00';
				$params['d_date']		= (isset($_POST['end_date']))?($_POST['end_date']." 23:59:59"):'0000-00-00 00:00:00';
				$params['adddata'] 				= "_thumb_".$_POST['adddata'];
				if(!empty($_POST['adddata']) && @is_file(ROOTPATH."data/tmp/".$params['adddata'])) {//rename
					@rename(ROOTPATH."data/tmp/".$params['adddata'], $this->Boardmodel->upload_path."/".$params['adddata']);
					@chmod($this->Boardmodel->upload_path."/".$params['adddata'],0707);
				}else{
					if($_POST['adddata']) $params['adddata'] 				= $_POST['adddata'];
				}
			}


			if( $_POST['reply'] == 'y' && (BOARDID == 'store_reservation' || BOARDID == 'mbqna' || BOARDID == 'goods_qna'  || BOARDID == 'bulkorder' || BOARDID == 'gs_seller_qna'  ) ) {
				unset($params['contents'], $params['subject']);//답변시 원본글  제목,내용 변경안되도록
			}


			if( $_POST['notice']){
				$params['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
			}else{
				$params['onlynotice']			=	0;//공지영역만 노출여부
			}

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

				if( BOARDID == 'goods_review' ) {//상품후기 건수/평점
					if( $params['goods_seq'] != $parentdata['goods_seq'] ) {//변경된 경우
						$oldparams['goods_seq'] = $parentdata['goods_seq'];//이전상품업데이트
						goods_review_count($oldparams, $parentdata['seq']); 
						goods_review_count($params, $parentdata['seq']);//변경상품업데이트
					}
				}elseif( BOARDID == 'goods_qna' ) {//상품문의 건수
					if( $params['goods_seq'] != $parentdata['goods_seq'] ) {//변경된 경우
						$oldparams['goods_seq'] = $parentdata['goods_seq'];
						goods_qna_count($oldparams, $parentdata['seq']); 
						goods_qna_count($params, $parentdata['seq']);
					}
				}

				if(!empty($_POST['newcategory']) && $_POST['category']=='newadd'){
					$upmanagerparams['category']		= $manager['category'].",".$_POST['newcategory'];
					$upmanagerparams['category']		= str_replace(",,",",",$upmanagerparams['category']);
					$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//카테고리변경
					//카테고리추가하기
				}

				//공지 Boardindex
				$idxsc['select']			= ' gid ';
				$idxsc['whereis']		= ' and gid = "'.$parentdata['gid'].'" and boardid = "'.$params['boardid'].'" ';
				$idxdata = $this->Boardindex->get_data($idxsc);// 
				$idxparams['hidden']		= $params['hidden'];//비밀글여부
				$idxparams['notice']		= $params['notice'];//공지여부
				if( $params['notice']){
					$idxparams['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
					$idxparams['onlynotice_sdate']		= $_POST['onlynotice_sdate'];//공지노출 시작일
					$idxparams['onlynotice_edate']		= $_POST['onlynotice_edate'];//공지노출 완료일
				}else{
					$idxparams['onlynotice']			=	0;//공지영역만 노출여부
					$idxparams['onlynotice_sdate']		= '';//공지노출 시작일
					$idxparams['onlynotice_edate']		= '';//공지노출 완료일
				}
				$idxparams['gid']		= $parentdata['gid'];//고유번호
				$idxparams['boardid']	= $params['boardid'];//id

				if( $idxdata ) {
					$this->Boardindex->idx_modify($idxparams);
				}else{
					$this->Boardindex->idx_write($idxparams);
				}

				if( BOARDID == 'bulkorder' || BOARDID == 'gs_seller_qna' ) {//대량구매 or 입점문의 게시판
					$sendsmscase = BOARDID.'_reply';
				}else{
					$sendsmscase = ( BOARDID == 'goods_qna')?'qna':(BOARDID == 'mbqna')?'cs':'board_reply';
				}
				if ( !empty($_POST['board_sms']) && !empty($_POST['board_sms_hand']) && $_POST['reply'] ) {//답변시
					$smsparams['msg']		= $_POST["sms_content1"];
					$this->load->model('membermodel');
					$smsparams = $this->membermodel->get_member_data($parentdata['mseq']);
					$smsparams['board_name'] = $manager['name'];
					$smsparams['userid']			 = ($smsparams['member_seq'])?$smsparams['userid']:$parentdata['name'];//비회원은 작성자명
					$smsparams['user_name']	 = ($smsparams['member_seq'])?$smsparams['user_name']:$parentdata['name'];//작성자명
					$commonSmsData[BOARDID."_reply"]['phone'][] = $_POST['board_sms_hand'];
					$commonSmsData[BOARDID."_reply"]['mid'][] = $smsparams['userid'];
					$commonSmsData[BOARDID."_reply"]['params'][] = $smsparams;

					if(count($commonSmsData) > 0){
						commonSendSMS($commonSmsData);
					}

					$params['tel1'] = $_POST['board_sms_hand'];
				}

				//회원정보체크
				$this->load->model('membermodel');
				if($parentdata['mseq']) $emoneyminfo = $this->membermodel->get_member_data($parentdata['mseq']);//본래글작성자체크
				if(!empty($emoneyminfo) && $_POST['board_emoney'] && $_POST['board_memo']) { //회원정보체크
					if( BOARDID == 'goods_review' ) {//상품후기
						$emoneyboardparams['type']			= 'goods_review';
					}else{
						$emoneyboardparams['type']			= 'board_'.BOARDID;//table
					}
					$emoneyboardparams['gb']				= 'plus';

					$emoneyboardparams['goods_review']= $parentdata['seq'];//본래글
					$emoneyboardparams['member_seq']	= $emoneyminfo['member_seq'];
					$emoneyboardparams['manager_seq']	= $this->managerInfo['manager_seq'];

					$emoneyboardparams['emoney']		= $_POST['board_emoney'];
					$emoneyboardparams['memo']		= $_POST['board_memo'];
					if($_POST['board_reserve_select']=='year'){
						$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$_POST['board_reserve_year']));
					}else if($_POST['board_reserve_select']=='direct'){
						$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$_POST['board_reserve_direct'], date("d"), date("Y")));
					}
					$emoneyboardparams['limit_date']	= $limit_date;
					$this->membermodel->emoney_insert($emoneyboardparams, $emoneyminfo['member_seq']);
				}

				if( (BOARDID == 'bulkorder' || BOARDID == 'gs_seller_qna' ) &&  !empty($_POST['board_email']) && !empty($_POST['board_sms_email']) ) {//대량구매게시판
					$data['email']		= $_POST['board_sms_email'];
					$data['title']				= (!empty($_POST['re_subject']))?$_POST['re_subject']:$params["subject"];
					$data['contents']		= (!empty($params["re_contents"]))?$params["re_contents"]:$params["contents"];
					getSendMail($data);
				}else{
					$sendemailcase = ( BOARDID == 'goods_qna' || BOARDID == 'mbqna' )?'cs':'board_reply';
					if ( !empty($_POST['board_email']) && !empty($_POST['board_sms_email']) && $_POST['reply'] ) {//답변시

						if($sendemailcase == 'board_reply' ){//추가게시판의 답변은 부모글참조
							$reply_parentsql['whereis']	= ' and seq= "'.$parentdata['parent'].'" ';
							$reply_parentsql['select']		= ' seq, gid, comment, upload, depth , mseq, subject, contents, r_date ';
							$reply_parentdata = $this->Boardmodel->get_data($reply_parentsql);//게시판목록
							if($reply_parentdata) {
								$emailparams['subject']		= $reply_parentdata["subject"];
								$emailparams['contents']		= $reply_parentdata["contents"];
								$emailparams['r_date']		= $reply_parentdata["r_date"];
							}
						}else{
							$emailparams['subject']		= $parentdata["subject"];
							$emailparams['contents']		= $parentdata["contents"];
							$emailparams['r_date']		= $parentdata["r_date"];
						}

						$emailparams['re_subject']		= (!empty($_POST['re_subject']))?$_POST['re_subject']:$_POST["subject"];
						$emailparams['re_contents']		= (!empty($params["re_contents"]))?$params["re_contents"]:$_POST["contents"];

						$emailparams['re_date']		= date("Y-m-d H:i:s");
						$this->load->model('membermodel');
						$emailparamsinfo = $this->membermodel->get_member_data($parentdata['mseq']);
						$emailparams['userid']			 = ($emailparamsinfo['member_seq'])?$emailparamsinfo['userid']:$parentdata['name'];
						$emailparams['user_name'] = ($emailparamsinfo['member_seq'])?$emailparamsinfo['user_name']:$parentdata['name'];
						$email = sendMail($_POST['board_sms_email'], $sendemailcase, $emailparamsinfo['userid'] , $emailparams);
					}
				}

				$this->session->unset_userdata('backtype');
				$_POST['backtype'] = (!empty($_POST['backtype']))?($_POST['backtype']):'list';
				$this->session->set_userdata('backtype',$_POST['backtype']);

				if($_POST['mainview']) {
					$callback =  "parent.document.location.reload();";
				}else{
					if($_POST['backtype'] == 'list') {
						$callback = ($_POST['returnurl'])?"parent.document.location.href='".$_POST['returnurl']."';":"parent.document.location.href='".$this->Boardmanager->realboardurl.BOARDID."';";
					}elseif($_POST['backtype'] == 'view'){
						$callback = ($_POST['returnurl'])?"parent.boardaddFormDialog('".$_POST['returnurl']."', '80%', '800', '게시글 보기','false');":"parent.boardaddFormDialog('".$this->Boardmanager->realboardviewurl.BOARDID."&seq=".$_POST['seq']."', '80%', '800', '게시글 보기','false');";
					}else {
						$callback = '';
					}
				}

				if ($_POST['reply'] == 'y') {
					openDialogAlert("답변이 저장되었습니다.",400,140,'parent',$callback);
				} else {
					openDialogAlert("게시글을 수정하였습니다.",400,140,'parent',$callback);
				}
			}else{
				$callback = "";
				openDialogAlert("게시글 수정이 실패 되었습니다.",400,140,'parent',$callback);
			}
			exit;
		}

		/* 게시글삭제 */
		elseif($mode == 'board_delete') {

			$num = 0;
			$parentsql['whereis']	= ' and seq= "'.$_POST['delseq'].'" ';
			$parentsql['select']		= ' * ';
			$parentdata = $this->Boardmodel->get_data($parentsql);//게시판목록
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
			$replysc['whereis'] = ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1).' and parent = '.($parentdata['seq']).'  ';//답변여부
			$replyor = $this->Boardmodel->get_data_numrow($replysc); 

			//게시글 삭제시 적립금/포인트 회수 한번만!
			if( $parentdata['display'] != 1  && $parentdata['mseq'] ) {
				if( BOARDID == 'goods_review' ) {
					$this->_goods_review_less($manager, $parentdata);
				}else{
					$this->_board_less($manager, $parentdata);
				}
			}

			if($replyor==0 && $parentdata['comment']==0) {//답변과 댓글이 없는 경우 real 삭제
				$num++;
				$result = $this->Boardmodel->data_delete($_POST['delseq']);//게시글삭제
				if($result) {
					$this->Boardindex->idx_delete($parentdata['gid']);//index 삭제 

					$this->Boardscorelog->data_parent_delete($_POST['delseq']); //게시글평가제거

					if( BOARDID == 'goods_review' ) {//상품후기
						//상품정보 > 상품후기건수 차감
						if( $parentdata['goods_seq'] ) {
							$reviewparentdata['goods_seq'] = $parentdata['goods_seq'];
							goods_review_count($reviewparentdata, $parentdata['seq'], 'minus');
						}

						if($parentdata['mseq']) {//회원정보체크
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

					//동영상테이블삭제
					if($parentdata['videotmpcode']) {
						$this->videofiles->videofiles_delete_parentseq('board', BOARDID, $parentdata['seq']);
					}


					//게시글수 save
					$upmanagerparams['totalnum']		= $manager['totalnum']-1;
					$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//본래게시판의 게시글감소

					$callback = "parent.document.location.reload();";
					openDialogAlert("게시글을 삭제하였습니다.",400,140,'parent',$callback);
				}else{
					$callback = "parent.document.location.reload();";
					openDialogAlert("게시글 삭제가 실패 되었습니다.",400,140,'parent',$callback);
				}
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

					$this->Boardscorelog->data_parent_delete($_POST['delseq']); //게시글평가제거

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

					//동영상테이블삭제
					if($parentdata['videotmpcode']) {
						$this->videofiles->videofiles_delete_parentseq('board', BOARDID, $parentdata['seq']);
					}

					$callback = "parent.document.location.reload();";
					openDialogAlert("게시글을 삭제하였습니다.",400,140,'parent',$callback);
				}else{
					$callback = "parent.document.location.reload();";
					openDialogAlert("게시글 삭제가 실패 되었습니다.",400,140,'parent',$callback);
				}
			}
		}

		/* 게시글다중삭제(원본글) */
		elseif($mode == 'board_multi_delete') {
			$delseqar = @explode(",",$_POST['delseq']);
			$num = 0;
			for($i=0;$i<sizeof($delseqar);$i++){ if(empty($delseqar[$i]))continue;
				$delseq = $delseqar[$i];
				$parentsql['whereis']	= ' and seq= "'.$delseq.'" ';
				$parentsql['select']		= ' * ';
				$parentdata = $this->Boardmodel->get_data($parentsql);//게시판목록
				if(empty($parentdata)) {
					$callback = "";
					openDialogAlert("존재하지 않는 게시물입니다.",400,140,'parent',$callback);
				}
				$replyor = 0;
				//답변여부
				/**$replysc['whereis']	= ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1) . ' ';
				//$replysc['select']		= " gid ";
				$replyor = $this->Boardindex->get_data_numrow($replysc);//**/
				$replysc['whereis'] = ' and gid > '.$parentdata['gid'].' and gid < '.(intval($parentdata['gid'])+1).' and parent = '.($parentdata['seq']).' ';//답변여부
				$replyor = $this->Boardmodel->get_data_numrow($replysc); 

				//게시글 삭제시 적립금/포인트 회수 한번만!
				if( $parentdata['display'] != 1  && $parentdata['mseq'] ) {
					if( BOARDID == 'goods_review' ) {
						$this->_goods_review_less($manager, $parentdata, 'mulit');
					}else{
						$this->_board_less($manager, $parentdata, 'mulit');
					}
				}

				if($replyor==0 && $parentdata['comment']==0) {//답변과 댓글이 없는 경우 real 삭제
					$num++;
					$result = $this->Boardmodel->data_delete($delseq);//게시글삭제
					if($result) {
						$this->Boardindex->idx_delete($parentdata['gid']);//index 삭제

						$this->Boardscorelog->data_parent_delete($delseq); //게시글평가제거

						if( BOARDID == 'goods_review') {
							//상품정보 > 상품후기건수 차감
							if( $parentdata['goods_seq'] ) {
								$reviewparentdata['goods_seq'] = $parentdata['goods_seq'];
								goods_review_count($reviewparentdata, $parentdata['seq'], 'minus');
							}

							if($parentdata['mseq']) {
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

						//동영상테이블삭제
						if($parentdata['videotmpcode']) {
							$this->videofiles->videofiles_delete_parentseq('board', BOARDID, $parentdata['seq']);
						}

					}
				}else{
					$params['display']			= '1';//삭제글여부1
					$params['subject']			= '';//초기화
					$params['contents']			= '';//초기화
					//$params['comment']		= '';//댓글수 초기화
					$params['upload']			= '';//첨부파일 초기화
					$params['r_date']			= date("Y-m-d H:i:s");
					$result = $this->Boardmodel->data_delete_modify($params,$delseq);

					if($result) {
						//공지글삭제
						$idxparams['display']	= 1;//삭제여부
						$idxparams['notice']		= 0;//공지 해지
						$idxparams['gid']			= $parentdata['gid'];//고유번호
						$this->Boardindex->idx_delete_modify($idxparams);

						$this->Boardscorelog->data_parent_delete($delseq); //게시글평가제거

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

						//동영상테이블삭제
						if($parentdata['videotmpcode']) {
							$this->videofiles->videofiles_delete_parentseq('board', BOARDID, $parentdata['seq']);
						}

					}//endif

				}//reply end
			}//endfor

			//게시글수 save
			$upmanagerparams['totalnum']		= $manager['totalnum']-$num;
			$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//삭제게시판의 게시글감소

			$callback = "parent.document.location.reload();";
			openDialogAlert($num."건의 게시글을 삭제하였습니다.",400,140,'parent',$callback);
		}

		/* 게시글다중삭제(원본글+덧글) */
		elseif($mode == 'board_multi_data_cmt_delete') {

		}

		/* 게시글다중복사 */
		elseif($mode == 'board_multi_copy') {

			$copyseqar = @explode(",",$_POST['delseq']);
			@sort($copyseqar);//역순
			$num = 0;
			for($i=0;$i<sizeof($copyseqar);$i++){
				if(empty($copyseqar[$i]))continue;
				$copyseq = $copyseqar[$i];
				$sc['whereis']	= ' and seq= "'.$copyseq.'" ';
				$sc['select']		= ' * ';
				$boarddata = $this->Boardmodel->get_data($sc);//게시물정보
				if($boarddata) {
					unset($params, $idxparams);
					$nokey = array('seq', 'boardid', 'gid', 'r_date','m_date');
					foreach($boarddata as $key=>$val) {
						if(in_array($key,$nokey)) continue;
						$params[$key] = $val;
					}

					$minsql['whereis']	= ' ';
					$minsql['select']		= ' min(gid) as mingid ';
					$mindata = $this->Boardmodel->get_data($minsql);
					$parentgid = $mindata['mingid'] ? $mindata['mingid']-1 : 100000000.00;
					$params['gid']			= $parentgid;
					$params['boardid']	= $_POST['copyid'];
					$params['r_date'] = date("Y-m-d H:i:s");
					$params['m_date'] = date("Y-m-d H:i:s");
					$params['videotmpcode'] = substr(microtime(), 2, 8);

					$params['contents'] = adjustMoveCopyImages($params['contents'],$_POST['board_id'], $this->Boardmanager->board_data_src.$_POST['copyid'].'/');//이미지경로변경
					$result = $this->Boardmodel->data_copy($params);
					if($result){$num++;
						$parent = $result;
						$sc['whereis']	= ' and id= "'.$_POST['copyid'].'" ';
						$sc['select']		= ' totalnum, id ';
						$copymanager = $this->Boardmanager->managerdataidck($sc);//게시판정보

						if( BOARDID == 'goods_review') {
							if($boarddata['mseq']) {
								//회원정보체크
								$this->load->model('membermodel');
								$minfo = $this->membermodel->get_member_data($boarddata['mseq']);
								$upsql = "update fm_member set review_cnt = review_cnt+1 where member_seq = '".$boarddata['mseq']."'";
								$this->db->query($upsql);
							}
						}

						//게시글수save
						$upmanagerparams['totalnum']		= $copymanager['totalnum']+1;
						$this->Boardmanager->manager_item_save($upmanagerparams,$_POST['copyid']);//복사게시판의 게시글증가

						//공지 Boardindex
						$idxparams['hidden']	= $params['hidden'];//비밀글여부
						$idxparams['notice']		= $params['notice'];//공지여부
						$idxparams['gid']			= $params['gid'];//고유번호
						$idxparams['boardid']	= $params['boardid'];//id
						$this->Boardindex->idx_write($idxparams);

						//첨부파일복사
						if(!empty($boarddata['upload'])){
							$oldfile = @explode("|",$boarddata['upload']);
							for ( $f=0;$f<count($oldfile);$f++) {
								$oldrealfile = @explode("^^",$oldfile[$f]);
								if(@is_file($this->Boardmodel->upload_path.$oldrealfile[0])){
									@copy($this->Boardmodel->upload_path.$oldrealfile[0],$this->Boardmanager->board_data_dir.$_POST['copyid'].'/'.$oldrealfile[0]);//파일복사
									@copy($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0],$this->Boardmanager->board_data_dir.$_POST['copyid'].'/'.'_thumb_'.$oldrealfile[0]);//파일복사
								}
							}
						}//첨부파일

						//동영상테이블복사
						$this->videofiles->videofiles_copy('board', BOARDID, $parentdata['seq'],$_POST['copyid'],$params);

						//댓글복사
						$cmtnokey = array('seq', 'boardid', 'parent');
						$cmtreplynokey = array('seq', 'boardid', 'parent','cmtparent');
						$cmtparentsql['whereis']	= ' and parent= "'.$boarddata['seq'].'"  and boardid= "'.$boarddata['boardid'].'" and depth = 0';
						$cmtparentsql['select']		= ' * ';
						$cmtparentdata = $this->Boardcomment->get_copy_data($cmtparentsql);//댓글정보
						if(!empty($cmtparentdata)) {
							foreach($cmtparentdata as $cmtdatarow) {$idx++;
								foreach($cmtdatarow as $cmtkey=>$cmtval) {
									if(in_array($cmtkey,$cmtnokey)) continue;
									$cmtparams[$cmtkey] = $cmtval;
								}
								$cmtparams['boardid']		= $_POST['copyid'];
								$cmtparams['parent']		= $parent;//복사된새로운글
								$replyseq = $this->Boardcomment->data_write($cmtparams);

								/**
								댓글의 답글
								**/
								$sc['orderby']			= 'seq';
								$sc['sort']					= 'asc';
								$sc['parent']				= (isset($cmtdatarow['parent']))?$cmtdatarow['parent']:'';
								$sc['cmtparent']		= (isset($cmtdatarow['seq']))?$cmtdatarow['seq']:'';
								$cmtreplyqry = $this->Boardcomment->data_list_reply($sc);//댓글목록
								$replyidx = 0;
								foreach($cmtreplyqry['result'] as $cmtreply){$replyidx++;
									foreach($cmtreply as $cmtreplykey=>$cmtval) {
										if(in_array($cmtreplykey,$cmtreplynokey)) continue;
										$cmtreplyparams[$cmtreplykey] = $cmtval;
									}
									$cmtreplyparams['boardid']		= $_POST['copyid'];
									$cmtreplyparams['parent']		= $parent;//복사된새로운글
									$cmtreplyparams['cmtparent']	= $replyseq;//복사된새글에서 새로운댓글
									$this->Boardcomment->data_write($cmtreplyparams);
								}//답글
							}//본래글
						}

					}//복사성공시
				}//$boarddata end
			}
			$callback = "parent.document.location.reload();";
			openDialogAlert($num."건의 게시글을 복사하였습니다.",400,140,'parent',$callback);
		}

		/* 게시글 다중이동 */
		elseif($mode == 'board_multi_move') {

			$copyseqar = @explode(",",$_POST['delseq']);
			@sort($copyseqar);//역순
			$num = 0;
			for($i=0;$i<sizeof($copyseqar);$i++){if(empty($copyseqar[$i]))continue;
				$copyseq = $copyseqar[$i];
				$sc['whereis']	= ' and seq= "'.$copyseq.'" ';
				$sc['select']		= ' * ';
				$boardmanager = $this->Boardmodel->get_data($sc);//게시물정보
				if($boardmanager) {
					unset($params, $idxparams);
					$nokey = array('seq', 'boardid');
					foreach($boardmanager as $key=>$val) {
						if(in_array($key,$nokey)) continue;
						$params[$key] = $val;
					}
					$params['boardid']	= $_POST['copyid'];

					$params['contents'] = adjustMoveCopyImages($params['contents'],$_POST['board_id'], $this->Boardmanager->board_data_src.$_POST['copyid'].'/');//이미지경로변경
					$result = $this->Boardmodel->data_move($params,$copyseq);

					if($result){
						$num++;
						$sc['whereis']	= ' and id= "'.$_POST['copyid'].'" ';
						$sc['select']		= ' totalnum, id ';
						$copymanager = $this->Boardmanager->managerdataidck($sc);//게시판정보
						//게시글수save
						$upmanagerparams['totalnum']		= $copymanager['totalnum']+1;
						$this->Boardmanager->manager_item_save($upmanagerparams,$_POST['copyid']);

						//공지 Boardindex
						/**
						$idxparams['hidden']	= $params['hidden'];//비밀글여부
						$idxparams['notice']		= $params['notice'];//공지여부
						$idxparams['gid']			= $params['gid'];//고유번호
						$idxparams['boardid']	= $params['boardid'];//id
						**/
						$idxsc['select']			= ' gid ';
						$idxsc['whereis']		= ' and gid = "'.$params['gid'].'" and boardid = "'.$params['boardid'].'" ';
						$idxdata = $this->Boardindex->get_data($idxsc);// 
						$idxparams['hidden']		= $params['hidden'];//비밀글여부
						$idxparams['notice']		= $params['notice'];//공지여부
						if( $params['notice']){
							$idxparams['onlynotice']			= ($_POST['onlynotice'])?$_POST['onlynotice']:0;//공지영역만 노출여부
							$idxparams['onlynotice_sdate']		= $_POST['onlynotice_sdate'];//공지노출 시작일
							$idxparams['onlynotice_edate']		= $_POST['onlynotice_edate'];//공지노출 완료일
						}else{
							$idxparams['onlynotice']			=	0;//공지영역만 노출여부
							$idxparams['onlynotice_sdate']		= '';//공지노출 시작일
							$idxparams['onlynotice_edate']		= '';//공지노출 완료일
						}
						$idxparams['gid']		= $params['gid'];//고유번호
						$idxparams['boardid']	= $params['boardid'];//id

						if( $idxdata ) {
							$this->Boardindex->idx_modify($idxparams);
						}else{
							$this->Boardindex->idx_write($idxparams);
						}

						//첨부파일이동
						if(!empty($boardmanager['upload'])){
							$oldfile = @explode("|",$boardmanager['upload']);
							for ( $f=0;$f<count($oldfile);$f++) {
								$oldrealfile = @explode("^^",$oldfile[$f]);
								if(@is_file($this->Boardmodel->upload_path.$oldrealfile[0])){
									@rename($this->Boardmodel->upload_path.$oldrealfile[0],$this->Boardmanager->board_data_dir.$_POST['copyid'].'/'.$oldrealfile[0]);//파일복사
									@rename($this->Boardmodel->upload_path.'_thumb_'.$oldrealfile[0],$this->Boardmanager->board_data_dir.$_POST['copyid'].'/'.'_thumb_'.$oldrealfile[0]);//파일복사
								}
							}
						}//첨부파일

						//동영상테이블이동
						if( $params['file_key_w'] ||  $params['file_key_i'] ){//
							unset($videofiles);
							$videofiles['parentseq']			= $copyseq;
							$videofiles['type']						= $_POST['copyid'];
							$this->videofiles->videofiles_move_parentseq($videofiles);
						}

						//댓글이동
						$cmtparams['boardid']		= $_POST['copyid'];
						$this->Boardcomment->data_parent_modify($cmtparams,$copyseq);

					}//이동성공시
				}
			}

			//게시글수save
			$upmanagerparams['totalnum']		= $manager['totalnum']-$num;
			$result = $this->Boardmanager->manager_item_save($upmanagerparams,BOARDID);//본래게시판의 게시글감소

			$callback = "parent.document.location.reload();";
			openDialogAlert($num."건의 게시글을 이동하였습니다.",400,140,'parent',$callback);
		}

		/* 게시글 파일삭제 */
		elseif($mode == 'board_file_delete') {
			if(empty($_POST['realfiledir'])) {// || empty($_SERVER['HTTP_REFERER'])
				$callback = "document.history(-1)";
				openDialogAlert("다운받을 파일을 선택해 주세요.",400,140,'parent',$callback);
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

			if(empty($_GET['realfiledir'])) {// || empty($_SERVER['HTTP_REFERER'])
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

			if(is_file($_GET['realfiledir'])){
				$data = @file_get_contents($_GET['realfiledir']);
				force_download(mb_convert_encoding(str_replace(" ","_",$_GET['realfilename']), 'euc-kr', 'utf-8'), $data);
				exit;
			}
		}

		/* FAQ 게시글 노출여부설정 */
		elseif($mode == 'board_faq_hidden') {
			$params['hidden'] = (!empty($_POST['hidden']))?1:0;
			$result = $this->Boardmodel->data_modify($params);
			if($result){
				if($params['hidden'] == 1){
					echo "노출로 설정하였습니다.";
				}else{
					echo "노출되지 않도록 설정하였습니다.";
				}
			}else{
				echo "노출을 설정에 실패하였습니다.";
			}
			exit;
		}
	}


	//배너등록하기
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
			$thumbsaveFile		= $folder.'_thumb_'.$file_name;
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
		$result = array('status' => 1,'saveFile' => "/".$saveFile,'thumbsaveFile' => "/".$thumbsaveFile,'file_name' => $file_name);
		echo "[".json_encode($result)."]";
		exit;
	}


	/* 상품후기 지급 적립금/포인트 회수 */
	public function _goods_review_less($manager, $parentdata, $multi=null)
	{
		$this->load->model('membermodel');
		$this->load->model('emoneymodel');


		if( defined('__SELLERADMIN__') === true ) {
			$manager_seq	= $this->providerInfo['provider_seq'];
		}else{
			$manager_seq	= $this->managerInfo['manager_seq'];
		}

		if(isset($_POST['board_less_emoney']))	$board_less_emoney = trim($_POST['board_less_emoney']);
		if(isset($_POST['board_less_point']))		$board_less_point = trim($_POST['board_less_point']);

		$minfo = $this->membermodel->get_member_data($parentdata['mseq']); 

		/************
		* 적립금 회수시작
		*************/
		//if( $board_less_emoney > 0 ) {
			$emautoemoneysc = $this->db->query("select * from fm_emoney where emoney_use !='less' and (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and ( (ordno  = '".$parentdata['order_seq']."' and goods_review = '".$parentdata['goods_seq']."' and (ordno != '' or ordno != 0 ) ) or ( goods_review_parent='".$parentdata['seq']."' ) ) ");
			$emautoemoneyar = $emautoemoneysc->result_array();
			if($emautoemoneyar) {
				foreach($emautoemoneyar as $emautoemoneyck=>$emautoemoney) {
					if( $multi ) $board_less_emoney += $emautoemoney['emoney']; 
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
				if( $multi ) $board_less_emoney += $emjoinck['emoney']; 
				//지급>회수완료업데이트
				$this->db->where('emoney_seq',$emjoinck['emoney_seq']);
				$this->db->update('fm_emoney',array('emoney_use'=>'less'));

			}

			if( $emautoemoneyar || $emjoinck ) {
				if( isset($board_less_emoney) ) {
					$params = array(
						'gb'									=> 'minus',
						'type'								=> 'goods_review_less',
						'emoney'							=> $board_less_emoney,
						'manager_seq'					=> $manager_seq,
						'goods_review'					=> $parentdata['goods_seq'],
						'goods_review_parent'	=> $parentdata['seq'],
						'memo'								=> "[회수]".$manager['name']." 삭제에 의한 적립금 차감",
					);
					$this->membermodel->emoney_insert($params, $parentdata['mseq']);
				}
			}
		//}//endif
		/************
		* 적립금 회수끝
		*************/

		/************
		* 포인트 회수시작
		*************/
		//if( $board_less_point > 0 ) {
			$emautopointsc = $this->db->query("select * from fm_point where point_use !='less' and (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and ( (ordno  = '".$parentdata['order_seq']."' and goods_review = '".$parentdata['goods_seq']."' and (ordno != '' or ordno != 0 ) ) or ( goods_review_parent='".$parentdata['seq']."' ) ) ");
			$emautopointar = $emautopointsc->result_array();

			if( $emautopointar ){

				foreach($emautopointar as $emautopointck=>$emautopoint) {
					if( $multi ) $board_less_point += $emautopoint['point']; 

					//지급>회수완료업데이트
					$this->db->where('point_seq',$emautopoint['point_seq']);
					$this->db->update('fm_point',array('point_use'=>'less'));

				}//end foreach

				if( isset($board_less_point) ) {
					$params = array(
						'gb'									=> 'minus',
						'type'								=> 'goods_review_less',
						'point'								=> $board_less_point,
						'manager_seq'					=> $manager_seq,
						'goods_review'					=> $parentdata['goods_seq'],
						'goods_review_parent'	=> $parentdata['seq'],
						'memo'								=> "[회수]".$manager['name']." 삭제에 의한 포인트 차감",
					);
					$this->membermodel->point_insert($params, $parentdata['mseq']);
				}
			}
		//}//end if
		/************
		* 포인트 회수끝
		*************/

	}


	/* 상품후기외 지급 수동적립금 회수 */
	public function _board_less($manager, $parentdata, $multi=null)
	{
		$this->load->model('membermodel');
		$this->load->model('emoneymodel');


		if( defined('__SELLERADMIN__') === true ) {
			$manager_seq	= $this->providerInfo['provider_seq'];
		}else{
			$manager_seq	= $this->managerInfo['manager_seq'];
		}

		if(isset($_POST['board_less_emoney']))	$board_less_emoney = trim($_POST['board_less_emoney']);
		//if(isset($_POST['board_less_point']))		$board_less_point = trim($_POST['board_less_point']);

		$minfo = $this->membermodel->get_member_data($parentdata['mseq']); 

		/************
		* 적립금 회수시작
		*************/
		//수동적립금 지급여부
		$joinsc['whereis'] = " and emoney_use !='less' and type = 'board_".$manager['id']."' and gb = 'plus' and member_seq = '".$parentdata['mseq']."' and (goods_review = '".$parentdata['seq']."' or goods_review_parent = '".$parentdata['seq']."') ";
		$joinsc['select']	= ' * ';
		$emjoinck = $this->emoneymodel->get_data($joinsc); 

		if( $emjoinck ){
			if( $multi ) $board_less_emoney += $emjoinck['emoney']; 
			//지급>회수완료업데이트
			$this->db->where('emoney_seq',$emjoinck['emoney_seq']);
			$this->db->update('fm_emoney',array('emoney_use'=>'less'));

			if( isset($board_less_emoney) ) {
				$params = array(
					'gb'									=> 'minus',
					'type'								=> 'board_'.$manager['id'].'_less',
					'emoney'							=> $board_less_emoney,
					'manager_seq'					=> $manager_seq,
					'goods_review_parent'	=> $parentdata['seq'],
					'memo'								=> "[회수]".$manager['name']." 삭제에 의한 적립금 차감",
				); 
				$this->membermodel->emoney_insert($params, $parentdata['mseq']);
			}

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

		if( $this->manager['auth_recommend_use'] == 'Y' ) {
			$sc['whereis']	= ' and seq= "'.$_POST['parent'].'" ';
			$sc['select']		= ' seq, gid, comment, upload, depth, file_key_w  ';
			$data = $this->Boardmodel->get_data($sc);

			if(empty($data)) {
				$result = array('result'=>false, 'msg'=>"존재하지 않는 게시물입니다."); 
				echo json_encode($result);
				exit;
			} 
			$parentsql['whereis']	= ' and boardid= "'.$_POST['board_id'].'" '; 
			$parentsql['whereis']	.= ' and type= "board" ';
			$parentsql['whereis']	.= ' and parent= "'.$_POST['parent'].'" ';//게시글 
			if($_POST['cparent']) $parentsql['whereis']	.= ' and cparent= "'.$_POST['cparent'].'" ';//댓글 
			$parentsql['whereis']	.= ' and mseq= "-'.$this->managerInfo['manager_seq'].'" ';
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
					$params['mseq']			= '-'.$this->managerInfo['manager_seq'];//$this->userInfo['member_seq']; 

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