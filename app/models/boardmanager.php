<?php
/**
 * 게시글 관련 모듈
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class Boardmanager extends CI_Model {

	# 리스트 스타일 종류 게시글추가 작업
	var $styles = array(
		'display_lattice_a'		=>	array('name'=>'격자형A','count_w'=>4),
		'display_lattice_b'		=>	array('name'=>'격자형B','count_w'=>2,'count_w_fixed'=>true),
		'display_list'			=>	array('name'=>'리스트형','count_w'=>1,'count_w_fixed'=>true)
	);


	var $typeNames = array(
		'text'		=> '텍스트박스',
		'select'   	=> '셀렉트박스',
		'radio'		=> '여러개 중 택1',
		'checkbox'	=> '체크박스',
		'textarea'	=> '에디트박스'
	);



	function __construct() {
		parent::__construct();

		if(isset($_GET['id'])) $this->id = $_GET['id'];
		$this->table_manager		= 'fm_boardmanager';
		$this->cmthidden				= 'Y';//댓글비밀글
		if( defined('__ADMIN__') && !defined('__DESIGNIS__')) {//관리자접근dir
			$this->managerurl				= '/admin/board/main';							//게시판관리
			$this->realboardurl				= '/admin/board/board?id=';				//게시물관리
			$this->realboarduserurl		= '/admin/board/?id=';							//게시물관리
			$this->realboardwriteurl		= '/admin/board/write?id=';					//게시물등록
			$this->realboardviewurl		= '/admin/board/view?id=';					//게시물보기

			$this->realboardpermurl		= './permcheck?id=';			//접근권한페이지
			$this->realboardpwurl			= './pwcheck?id=';			//로그인페이지(직접접근시
		}elseif( defined('__SELLERADMIN__') && !defined('__DESIGNIS__') ) {//관리자접근dir
			$this->managerurl				= '/selleradmin/board/main';							//게시판관리
			$this->realboardurl				= '/selleradmin/board/board?id=';				//게시물관리
			$this->realboarduserurl		= '/selleradmin/board/?id=';							//게시물관리
			$this->realboardwriteurl		= '/selleradmin/board/write?id=';					//게시물등록
			$this->realboardviewurl		= '/selleradmin/board/view?id=';					//게시물보기

			$this->realboardpermurl		= './permcheck?id=';			//접근권한페이지
			$this->realboardpwurl			= './pwcheck?id=';			//로그인페이지(직접접근시
		}else{

			$this->managerurl				= '../board/main';							//게시판관리
			$this->realboardurl				= '../board/board?id=';				//게시물관리
			$this->realboarduserurl		= '../board/?id=';							//게시물관리
			$this->realboardwriteurl		= '../board/write?id=';					//게시물등록
			$this->realboardviewurl		= '../board/view?id=';					//게시물보기

			$this->realboardpermurl		= '../board/permcheck?id=';			//접근권한페이지
			$this->realboardpwurl			= '../board/pwcheck?id=';			//로그인페이지(직접접근시
		}

		$this->renewlist = array("mbqna","faq","goods_qna","goods_review","bulkorder","gs_seller_qna","gs_seller_notice","store_review","store_reservation");//기본스킨리스트

		$this->board_tmp_dir = ROOTPATH.'data/tmp/';//임시저장소
		$this->board_tmp_src = '/data/tmp/';//임시저장소 저장시

		$this->board_capt_dir = ROOTPATH.'data/captcha/';//임시저장소
		$this->board_capt_src = '/data/captcha/';//임시저장소 저장시
		$this->board_captcha_ttf = ROOTPATH.'data/board/verdanab.ttf';//
		$this->board_captcha_ttf_new = ROOTPATH.'data/board/_fonts';//

		if( defined('__ADMIN__') || defined('__SELLERADMIN__') ) {//관리자접근dir
			$this->board_icon_src = '/data/skin/'.$this->workingSkin.'/images/board/icon/';
			$this->board_icon_dir = ROOTPATH.'/data/skin/'.$this->workingSkin.'/images/board/icon/';
		}else{
			$this->board_icon_src = '/data/skin/'.$this->skin.'/images/board/icon/';
			$this->board_icon_dir = ROOTPATH.'/data/skin/'.$this->skin.'/images/board/icon/';
		}

		$this->admin_board_icon_src = '/admin/skin/'.$this->config_system['adminSkin'].'/images/board/icon/';
		$this->admin_board_icon_dir = ROOTPATH.'/admin/skin/'.$this->config_system['adminSkin'].'/images/board/icon/';

		if( defined('__ADMIN__') ||  defined('__SELLERADMIN__') ) {//관리자접근dir
			$this->board_skin_dir = ROOTPATH.'data/skin/'.$this->workingSkin.'/board/';
			$this->board_skin_src = '/data/skin/'.$this->workingSkin.'/board/'; 
		}else{
			$this->board_skin_dir = ROOTPATH.'data/skin/'.$this->skin.'/board/';
			$this->board_skin_src = '/data/skin/'.$this->skin.'/board/';
		}

		$this->board_data_dir = ROOTPATH.'data/board/';//첨부파일폴더
		$this->board_data_src = '/data/board/';

		$this->board_originalskin_dir = ROOTPATH.'board_original/';//스킨폴더
		$this->board_originalskin_src = '/board_original/';
		$bdiconarray = array("hot"=>"icon_hot.gif","new"=>"icon_new.gif","review"=>"icon_review.gif","award"=>"icon_award.png",
			"best"=>"icon_best.gif","best_gray"=>"icon_best_gray.gif","admin"=>"icon_admin.gif","file"=>"ico_file.gif","img"=>"icon_img.gif",
			"video"=>"icon_video.gif","mobile"=>"icon_mobile.gif","hidden"=>"ico_hidden.gif","notice"=>"icon_notice.gif","re"=>"icon_comment_reply.gif",
			"blank"=>"blank.gif","print"=>"b_print.gif","cmt_reply"=>"cmt_reply_btn_delete.gif",
			"snst"=>"sns_t0.gif","snsf"=>"sns_f0.gif","snsm"=>"sns_m0.gif","snsy"=>"sns_y0.gif");
		foreach( $bdiconarray as $key=>$val ) {
			$this->{$key.'_icon_src'} =  $this->admin_board_icon_src.$val; //$this->board_icon_src.$val; 
		}

		
		$bdiconarray = array("recommend"=>"icon_recommend.png","none_rec"=>"icon_none_rec.png",
			"recommend1"=>"icon_recommend1.png","recommend2"=>"icon_recommend2.png","recommend3"=>"icon_recommend3.png","recommend4"=>"icon_recommend4.png","recommend5"=>"icon_recommend5.png",
			"cmt_recommend"=>"icon_cmt_recommend.png","cmt_none_rec"=>"icon_cmt_none_rec.png");
		foreach( $bdiconarray as $key=>$val ) {
			$this->{$key.'_icon_src'} =  $this->admin_board_icon_src.$val; 
		} 

		$this->board_restr = 'RE:';//답글제목형식
		$this->board_cont_restr = '<br /><blockquote style="border-left: #000000 2px solid; padding-bottom: 0px; margin: 0px 0px 0px 5px; padding-left: 5px; adding-right: 0px; padding-top: 0px"><div>------------Original Message------------</div>';//답글내용형식


		$this->goodsreviewicondir = ROOTPATH.'/data/icon/goods_review/';//상품후기의 평가정보아이콘위치
		$this->goodsreviewicon = '/data/icon/goods_review/';//상품후기의 평가정보아이콘위치
	}

	/*
	 * 게시물관리
	 * @param
	*/
	public function manager_list($sc) {
		$sql = "select  SQL_CALC_FOUND_ROWS seq, name, id, totalnum, skin_type, skin, type, auth_read, auth_write, auth_reply, auth_cmt, auth_reply_use, auth_cmt_use,
		CASE WHEN type = 'A' THEN '추가' ELSE '기본' END AS typetitle,
		CASE WHEN auth_reply_use = 'Y' THEN '사용함' ELSE '미사용' END AS auth_reply_use_title,
		CASE WHEN auth_cmt_use = 'Y' THEN '사용함' ELSE '미사용' END AS auth_cmt_use_title,
		CASE WHEN id='gs_seller_qna' AND auth_read = '[all]' THEN '입점사'
				 WHEN auth_read = '[all]' AND secret_use='Y' THEN '비밀글'
				 WHEN auth_read = '[all]' THEN '전체'
				 WHEN auth_read = '[admin]' THEN '관리자'
				 WHEN auth_read like '[member%' THEN '회원'
				 ELSE '관리자' END AS auth_read_title,
		CASE WHEN auth_write = '[all]' THEN '전체'
				 WHEN auth_write = '[admin]' THEN '관리자'
				 WHEN auth_write = '[onlybuyer]' THEN '구매자'
				 WHEN auth_write like '[member%' THEN '회원'
				 ELSE '관리자' END AS auth_write_title
		from  ".$this->table_manager."  where 1";

		if( !empty($sc['skin']) )
		{
			$skin_typein = @implode("','",$sc['skin']);
			$sql .= " and skin IN ('{$skin_typein}') ";
		}

		if( !empty($sc['search_text']))
		{
			$sql .= ' and ( id like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%" ) ';
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		//debug_var($data);
		return $data;
	}

	// 게시물총건수
    function get_item_total_count($sc)
    {
		$sql = 'select  SQL_CALC_FOUND_ROWS seq from '.$this->table_manager;
		$this->db->query($sql);
		return mysql_affected_rows();
    }


	/*
	 * 게시판정보
	 * @param
	*/
	public function manager_whereis_list($sc)
	{
		if( defined('__SELLERADMIN__') === true ) {
			$sc['whereis']		= ' and id in (\'goods_qna\',\'goods_review\',\'gs_seller_qna\',\'gs_seller_notice\') ';
		}

		$whereis = (!empty($sc['whereis']))?$sc['whereis']:'';
		$select = (!empty($sc['select']))?$sc['select']:' * ';
		$sql = "select ".$select." from  ".$this->table_manager."  where 1 ". $whereis;
		if( $sc['manager_setting'] ) {//게시판접근권한
			$sql .=" order by type asc, seq asc ";
		}else{
			$sql .=" order by type desc, seq asc ";
		}
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return $data;
	}


	/*
	 * 게시판정보
	 * @param
	*/
	public function get_managerdata($sc)
	{
		$whereis = (!empty($sc['whereis']))?$sc['whereis']:'';
		$select = (!empty($sc['select']))?$sc['select']:' * ';
		$sql = "select ".$select." from  ".$this->table_manager."  where 1 ". $whereis;
		$sql .=" order by type desc, seq asc ";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return ($data) ? $data[0]:'';
	}

	/*
	 * 게시판정보
	 * @param
	*/
	public function managerdataidck($sc) {
		$sql = "select ".$sc['select']." from  ".$this->table_manager."  where 1 ". $sc['whereis'];
		$sql .=" order by seq asc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		if($data) {
			$gallerycell = explode("X" , $data['gallerycell']);//겔러리인경우
			$data['gallerycell0'] = $gallerycell[0];
			$data['gallerycell1'] = $gallerycell[1];

			$video_screen = explode("X" , $data['video_screen']);
			$data['video_screen0'] = $video_screen[0];
			$data['video_screen1'] = $video_screen[1];

			$video_size = explode("X" , $data['video_size']);
			$data['video_size0'] = $video_size[0];
			$data['video_size1'] = $video_size[1];

			$video_size_mobile = explode("X" , $data['video_size_mobile']);
			$data['video_size_mobile0'] = $video_size_mobile[0];
			$data['video_size_mobile1'] = $video_size_mobile[1];

			$data['subjectcut']				= ($data['subjectcut']>0)?$data['subjectcut']:30;
			$data['mobile_subjectcut']	= $data['subjectcut'];//($data['subjectcut']>0)?intval($data['subjectcut']/2):15;

			if($data['id'] == 'goods_review'){//@2012-11-06 상품후기 답글 미사용처리
				$data['auth_write_reply'] = ($data['auth_write_reply'])?$data['auth_write_reply']:'[all]';//
				$data['viewtype'] = ($data['viewtype'])?$data['viewtype']:'layer';//page, layer
			}
			if($data['id'] == 'goods_qna'){
				$data['viewtype'] =($data['viewtype'])?$data['viewtype']:'layer';//page, layer
			} 
			if( $data['recommend_type'] == '3' ) {
				$data['scoretitle'] = "<span>평가</span>";
			}elseif( $data['recommend_type'] == '2' ) {
				$data['scoretitle'] = "<span>추천/비추천</span>";
			}else{
				$data['scoretitle'] = "<span>추천</span>";
			} 

			//문자답변 사용여부
			$sms_reply_user_yn	= config_load('sms',$data['id'].'_reply_user_yn');
			$sms_reply_user		= config_load('sms',$data['id'].'_reply_user');
			if(trim($sms_reply_user[$data['id'].'_reply_user']) != ''){
				$data['sms_reply_user_yn'] = $sms_reply_user_yn[$data['id'].'_reply_user_yn']; 
 
				// 발송제한 설정 시간 및 예약발송시간
				$board_time_s		= config_load('sms_restriction','board_time_s');
				$board_time_e		= config_load('sms_restriction','board_time_e');
				$board_reserve_time	= config_load('sms_restriction','board_reserve_time');
				if($board_time_s['board_time_s'] && $board_time_e['board_time_e'] && $board_reserve_time['board_reserve_time']){
				   $restriction_msg = "<br /><span style='color:#d90000;line-height:14px;'>발송제한시간 : ";
				   $restriction_msg.= $board_time_s['board_time_s']."시~".$board_time_e['board_time_e']."시 ";
				   $restriction_msg.= " ▶ 08시 +".$board_reserve_time['board_reserve_time']."분</span>";
				}else{
				   $restriction_msg = "";
				}
				$data['restriction_msg'] = $restriction_msg; 
			}

		}
		return $data;
	}

	/*
	 * 게시판생성
	 * @param
	*/
	public function manager_write($params) {
		if(empty($params['id']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_manager));
		$result = $this->db->insert($this->table_manager, $data);
		return $this->db->insert_id();
	}

	/*
	 * 게시판수정
	 * @param
	*/
	public function manager_modify($params) {
		if(empty($_POST['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_manager));
		$result = $this->db->update($this->table_manager, $data,array('seq'=>$_POST['seq']));
		return $result;
	}


	/*
	 * 게시판 개별수정
	 * @param
	*/
	public function manager_item_save($params,$board_id) {
		if(empty($board_id))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_manager));
		$result = $this->db->update($this->table_manager, $data,array('id'=>$board_id));
		return $result;
	}

	/*
	 * 게시판삭제
	 * @param
	*/
	public function manager_delete($board_id) {
		if(empty($board_id))return false;
		$result = $this->db->delete($this->table_manager, array('id' => $board_id));
		return $result;
	}

	/*
	 * 게시판복사
	 * @param
	*/
	public function manager_copy($params, $olddata, $copyid, $new_id) {
		$result =$this->manager_write($params);
		if($result) {
			$seq = $result;
			boarduploaddir($new_id);
			$upparams = "";
			if($olddata['icon_new_img'] && is_file($this->Boardmanager->board_data_dir.$copyid.'/'.$olddata['icon_new_img']) ) {
				$extar = explode("_new.",$olddata['icon_new_img']);
				@copy($this->Boardmanager->board_data_dir.$copyid.'/'.$olddata['icon_new_img'],$this->Boardmanager->board_data_dir.$new_id.'_new.'.$extar[1]);
				@chmod($this->Boardmanager->board_data_dir.$new_id.'_new.'.$extar[1], 0777);
				$upparams['icon_new_img'] = $new_id.'_new.'.$extar[1];
			}

			if($olddata['icon_hot_img'] && is_file($this->Boardmanager->board_data_dir.$copyid.'/'.$olddata['icon_hot_img']) ) {
				$extar = explode("_hot.",$olddata['icon_hot_img']);
				@copy($this->Boardmanager->board_data_dir.$copyid.'/'.$olddata['icon_hot_img'],$this->Boardmanager->board_data_dir.$new_id.'_hot.'.$extar[1]);
				@chmod($this->Boardmanager->board_data_dir.$new_id.'_hot.'.$extar[1], 0777);
				$upparams['icon_hot_img'] = $new_id.'_hot.'.$extar[1];
			}

			if($olddata['icon_review_img'] && is_file($this->Boardmanager->board_data_dir.$copyid.'/'.$olddata['icon_review_img']) ) {
				$extar = explode("_review.",$olddata['icon_review_img']);
				@copy($this->Boardmanager->board_data_dir.$copyid.'/'.$olddata['icon_review_img'],$this->Boardmanager->board_data_dir.$new_id.'_review.'.$extar[1]);
				@chmod($this->Boardmanager->board_data_dir.$new_id.'_review.'.$extar[1], 0777);
				$upparams['icon_review_img'] = $new_id.'_review.'.$extar[1];
			}
			if($upparams){
				$updata = filter_keys($upparams, $this->db->list_fields($this->table_manager));
				$this->db->update($this->table_manager, $updata,array('seq'=>$seq));
			}
		}
		return $result;
	}




	//가입형식 추가 타입별 속성값 가져오기
	public function get_labelitem_type($data, $msdata,$showtype = null){

		switch($data['label_type'])
			{

				case "text" :

					for ($j=0; $j<$data['label_value']; $j++) {
						if ($j > 0) $inputBox .= "<br/>";
						$label_value = ($msdata[$j]) ? $msdata[$j]['label_value'] : '';
						if($showtype == 'view'){
							$inputBox .= $label_value ;
						}else{

							$size = ( $this->mobileMode || $this->storemobileMode )?" ":"size='70' ";
							$inputBox .= '<input type="text" name="label['.$data['bulkorderform_seq'].'][value][]" class=" line text_'.$data['bulkorderform_seq'].'" id="txtlabel_'.$data['bulkorderform_seq'].'" value="'.$label_value.'" '.$size.' style="width:90%;border:1px solid #dbdbdb; margin:1px 0; padding:2px;">';
						}
					}
				break;

				case "select" :
					$labelArray = explode("|", $data['label_value']);
					$labelCount = count($labelArray)-1;
					$labelindexBox = '';
					$label_value = ($msdata[0]) ? $msdata[0]['label_value'] : '';
					if($showtype == 'view'){
						$inputBox .= $label_value ;
					}else{

						for ($j=0; $j<$labelCount; $j++)
						{
							$labelsubArray = explode(";", $labelArray[$j]);
							$selected = ($labelsubArray[0] == $label_value) ? "selected" : "";
							$labelindexBox .= '<option value="'. $labelsubArray[0] .'" '. $selected .' childs="'.implode(";",array_slice($labelsubArray,1)).'">'. $labelsubArray[0] .'</option>';
						}
						if($msdata[0]){
							$labelsubBox = '<input type="hidden" name="subselect['.$data['bulkorderform_seq'].'] id="subselect_'.$data['bulkorderform_seq'].'" value="'.$msdata[0]['label_sub_value'].'" bulkorderform_seq="'.$data['bulkorderform_seq'].'" class="hiddenLabelDepth">';
						}

						$inputBox .= '<select name="label['.$data['bulkorderform_seq'].'][value][]" id="label_'.$data['bulkorderform_seq'].'" bulkorderform_seq="'.$data['bulkorderform_seq'].'" style="height:18px; line-height:16px;" class="selectLabelDepth1">';
						$inputBox .= $labelindexBox;
						$inputBox .= '</select>';
						$inputBox .= $labelsubBox;
					}

				break;

				case "textarea" :

						switch($data['label_value'])
						{
							case "large" :		$height = "300px";	break;
							case "medium" :		$height = "200px";	break;
							case "small" :		$height = "100px";	break;
						}
						$label_value = ($msdata[0]) ? $msdata[0]['label_value'] : '';
						if($showtype == 'view'){
							$inputBox .= $label_value ;
						}else{
							$inputBox .= '<textarea name="label['.$data['bulkorderform_seq'].'][value][]" id="txtarealabel_'.$data['bulkorderform_seq'].'" style="width:90%; height:'. $height .';">'.$label_value.'</textarea>';
						}

				break;

				case "checkbox" :
					$labelArray = explode("|", $data['label_value']);
					$labelCount = count($labelArray)-1;

					if($msdata[0])$cmsdata=count($msdata);
					for ($k=0; $k<$cmsdata; $k++) {
						$ckdata[] = $msdata[$k]['label_value'];
					}

					for ($j=0; $j<$labelCount; $j++) {
						if (is_array($msdata)) {
							$checked = (in_array($labelArray[$j], $ckdata )) ? "checked" : "";
						}
						if ($j > 0) $inputBox .= " ";
						if($showtype == 'view') {
							if($checked ){
								$inputBox .= $labelArray[$j];
							}
						}else{
							$inputBox .= '<input type="checkbox" name="label['.$data['bulkorderform_seq'].'][value][]" class="null labelCheckbox_'.$data['bulkorderform_seq'].'" value="'. $labelArray[$j] .'" '. $checked .'>'. $labelArray[$j];
						}
					}
				break;

				case "radio" :
					$labelArray = explode("|", $data['label_value']);
					$labelIconArray = explode("|", $data['label_icon']);
					$labelCount = count($labelArray)-1;

					for ($j=0; $j<$labelCount; $j++) {
							$labelIconArray[$j] = ($labelIconArray[$j])?$labelIconArray[$j]:'emotion_happy.png';
						if( BOARDID == 'goods_review' && $labelIconArray[$j] ) {
							$iconpath = ROOTPATH.$this->goodsreviewicon.$labelIconArray[$j];
							$iconurl = $this->goodsreviewicon.$labelIconArray[$j];
							if(is_file($iconpath) ) {
								$iconimg = '<img src="'.$iconurl.'" >';
							}else{
								$iconimg='';
							}
						}

						if ( is_array($msdata[0]) ) {
							$checked = ($labelArray[$j] == $msdata[0]['label_value']) ? "checked" : "";
						}else{
							$checked = ( $j == 0 ) ? "checked" : "";
						}
						if ($j > 0) $inputBox .= " ";
						if($showtype == 'view'){
							if($checked ){
								$inputBox .= $iconimg.$labelArray[$j];
							}
						}else{
							$inputBox .= '<label><input type="radio" name="label['.$data['bulkorderform_seq'].'][value][]" class="null" value="'. $labelArray[$j] .'" '. $checked .'> '.$iconimg.$labelArray[$j].'</label>';
						}
					}
				break;
			}

		return $inputBox;
	}

}
/* End of file boardmanager.php */
/* Location: ./app/models/boardmanager */