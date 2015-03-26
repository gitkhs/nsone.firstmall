<?php
/**
 * 게시판/게시물 관련 클래스
 * @author gabia
 * @since version 2.0 - 2012.06.29
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class Board extends front_base {
	private $boardid;
	var  $manager;

	function __construct() {
		parent::__construct();

		$file_path	= $this->template_path();
		define('FILE_PATH', $file_path);
		if ( !defined('BOARDID') && $_GET['id'] ) {
			define('BOARDID',$_GET['id']);
		}
		$this->load->helper(array('text','board','file','download','cookie'));
		$this->load->library('upload');
		$this->load->model('Boardmanager');
		//$this->load->model('providermodel'); 
		$this->load->model('membermodel'); 
		$this->load->model('boardadmin');
		$this->load->model('Boardscorelog');
		//$this->load->model('boardfiles');
		if ( defined('BOARDID') ) {
			if( BOARDID == 'goods_qna' ) {
				$this->load->model('Goodsqna','Boardmodel');
			}elseif( BOARDID == 'goods_review' ) {
				$this->load->model('Goodsreview','Boardmodel');
			}elseif( BOARDID == 'bulkorder' ) {//대량구매게시판
				$this->load->model('Boardbulkorder','Boardmodel');
			}else{
				$this->load->model('Boardmodel');
			}
		}
		if( !$this->isplusfreenot && BOARDID == 'bulkorder' ){
			pageClose('무료몰Plus+에서는 대량구매게시판을 지원하지 않는 기능입니다.\n프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 대량구매게시판을 이용 가능합니다.');
			exit;
		}

		if( BOARDID == 'gs_seller_qna' || BOARDID == 'gs_seller_notice' ){
			pageClose('입점사전용게시판입니다.');
			exit;
		}
		$this->board_editor = config_load('board_editor');

		$this->load->model('Boardindex');//공지용

		$this->thisfile = $this->uri->rsegments[count($this->uri->rsegments)];

		if ( isset($_GET['seq']) ) {//게시글 상세인 경우
			$this->load->model('Boardcomment');
			$querystr = (isset($_GET['reply'])) ? str_replace("&iframe=&","&",str_replace("&popup=&","&",str_replace("seq=".$_GET['seq'],"",str_replace("&seq=".$_GET['seq'],"",str_replace("reply=".$_GET['reply'],"",str_replace("&reply=".$_GET['reply'],"",str_replace("id=".BOARDID,"",str_replace("&id=".BOARDID,"",$_SERVER['QUERY_STRING'])))))))):str_replace("&iframe=&","",str_replace("&popup=&","",str_replace("seq=".$_GET['seq'],"",str_replace("&seq=".$_GET['seq'],"",str_replace("id=".BOARDID,"",str_replace("&id=".BOARDID,"",$_SERVER['QUERY_STRING']))))));
		}else{
			$querystr = str_replace("&iframe=&","&",str_replace("&popup=&","&",str_replace("id=".BOARDID,"",str_replace("&id=".BOARDID,"",$_SERVER['QUERY_STRING']))));
		}
		$querystr = str_replace("&&","&",str_replace("&&&","&",$querystr));
		if( $querystr &&  substr($querystr,0,1) != "&" ) $querystr = "&".$querystr;
		parse_str($querystr);

		if ( defined('BOARDID') ) {
			$sql['whereis']	= ' and id= "'.BOARDID.'" ';
			$sql['select']		= ' * ';
			$this->manager = $this->Boardmanager->managerdataidck($sql);//게시판정보
			boarduploaddir($this->manager);//게시판 스킨생성 및 복사
		}

		$this->joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		$cfg_goods = config_load('goods');
		if($this->manager['video_use'] == 'Y' ) {
			$video_size = explode("X" , $this->manager['video_size']);
			$this->manager['video_size0'] = ($video_size[0])?$video_size[0]:'400';
			$this->manager['video_size1'] = ($video_size[1])?$video_size[1]:'200';

			$video_size_mobile = explode("X" , $this->manager['video_size_mobile']);
			$this->manager['video_size_mobile0'] = ($video_size_mobile[0])?$video_size_mobile[0]:'200';
			$this->manager['video_size_mobile1'] = ($video_size_mobile[1])?$video_size_mobile[1]:'100';
		}else{
			unset($this->manager['file_key_w'],$this->manager['file_key_i'],$this->manager['video_size']);
			//$this->manager['video_use']	= 'N';
		}

		$this->manager['file_use'] = (!(  $this->siteType == 'mobile' || $this->_is_mobile_agent) && $this->manager['file_use'] == 'Y' && $this->manager['onlyimage_use'] == 'Y' )?'img':$this->manager['file_use'];

		$this->template->assign('manager',$this->manager);
		$this->board_dir = $this->Boardmanager->board_skin_dir.BOARDID.'/';
		$this->board_skin = $this->Boardmanager->board_skin_dir.BOARDID.'/'.$this->manager['skin'];
		getBoardCommentPrenextCopy();

		define('BORADSKIN', BOARDID.'/'.$this->manager['skin']);
		$this->template->assign('skindir', BORADSKIN);
		$this->template->assign('skinimgdir', BORADSKIN.'/image/');

		$this->boardurl->lists			= $this->Boardmanager->realboarduserurl.BOARDID.$querystr;				//게시물관리

		$popup = (!empty($_GET['popup'])) ? $_GET['popup']:'';
		$iframe = (!empty($_GET['iframe'])) ? $_GET['iframe']:'';

		if( !strstr($querystr,'&popup=1') && $iframe==1) {//새창인경우
			$this->boardurl->resets		= (!empty($_GET['popup']) || !empty($_GET['iframe']))?$this->Boardmanager->realboarduserurl.BOARDID.'&popup=1&iframe='.$iframe:$this->Boardmanager->realboarduserurl.BOARDID;				//게시물관리
			$this->boardurl->write		= $this->Boardmanager->realboardwriteurl.BOARDID.$querystr.'&popup=1';				//게시물등록
			$this->boardurl->reply		= $this->Boardmanager->realboardwriteurl.BOARDID.$querystr.'&popup=1&reply=y&seq=';	//게시물답변
			$this->boardurl->modify		= $this->Boardmanager->realboardwriteurl.BOARDID.$querystr.'&popup=1&seq=';	//게시물수정
			$this->boardurl->view		= $this->Boardmanager->realboardviewurl.BOARDID.$querystr.'&popup=1&seq=';	//게시물보기
			$this->boardurl->perm		= $this->Boardmanager->realboardpermurl.BOARDID.'&popup=1&returnurl=';		//접근권한
			$this->boardurl->pw			= $this->Boardmanager->realboardpwurl.BOARDID.'&popup=1&returnurl=';		//접근권한
			$this->boardurl->querystr	= $querystr;	//검색키
		}else{
			$this->boardurl->resets		= $this->Boardmanager->realboarduserurl.BOARDID;			//게시물관리
			if($iframe==1)	$this->boardurl->resets		.= $querystr;								//게시물관리 iframe

			$this->boardurl->write		= $this->Boardmanager->realboardwriteurl.BOARDID.$querystr;				//게시물등록
			$this->boardurl->reply		= $this->Boardmanager->realboardwriteurl.BOARDID.$querystr.'&reply=y&seq=';	//게시물답변
			$this->boardurl->modify		= $this->Boardmanager->realboardwriteurl.BOARDID.$querystr.'&seq=';	//게시물수정
			$this->boardurl->view		= $this->Boardmanager->realboardviewurl.BOARDID.$querystr.'&seq=';	//게시물보기
			$this->boardurl->perm		= $this->Boardmanager->realboardpermurl.BOARDID.'&returnurl=';						//접근권한
			$this->boardurl->pw			= $this->Boardmanager->realboardpwurl.BOARDID.'&returnurl=';						//접근권한
			$this->boardurl->querystr	= $querystr;	//검색키
		}

		$this->boardurl->goodsview		= '/goods/view?no=';						//상품접근

		$this->icon_file_img			= $this->Boardmanager->file_icon_src;//첨부파일icon
		$this->icon_image_img			= $this->Boardmanager->img_icon_src;//이미지icon
		$this->icon_video_img			= $this->Boardmanager->video_icon_src;//동영상icon
		$this->icon_mobile_img			= $this->Boardmanager->mobile_icon_src;//모바일icon

		$this->icon_award_img			= $this->Boardmanager->award_icon_src;//goodsreview

		$this->icon_hidden_img			= $this->Boardmanager->hidden_icon_src;//비밀글icon
		$this->notice_img				= $this->Boardmanager->notice_icon_src;//공지글icon
		$this->re_img					= $this->Boardmanager->re_icon_src;//답변글icon
		$this->blank_img				= $this->Boardmanager->blank_icon_src;//blank
		$this->print_img				= $this->Boardmanager->print_icon_src;//print

		$this->snst_img					= $this->Boardmanager->snst_icon_src;//twitter
		$this->snsf_img					= $this->Boardmanager->snsf_icon_src;//facebook
		$this->snsm_img					= $this->Boardmanager->snsm_icon_src;//m2day
		$this->snsy_img					= $this->Boardmanager->snsy_icon_src;//요즘

		getboardicon();//icon setting
		$this->template_path = str_replace('board/','board/'.BORADSKIN.'/',$this->template_path);
		$this->template->assign(array(
				"template_path"=>$this->template_path
		));
		$this->template->assign('ismobile',$this->_is_mobile_agent);//ismobile

		$this->protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';

		if( BOARDID == 'goods_review' ) {
			$reserves = ($this->reserves)?$this->reserves:config_load('reserve');//적립금자동지급관련
			if( $reserves['autoemoney'] == 1 &&  $reserves['autoemoneytype'] != 3 ){
				if( $reserves['autoemoney_video'] > 0 || $reserves['autoemoney_photo'] > 0  || $reserves['autoemoney_review'] > 0 || $reserves['autopoint_video'] > 0 || $reserves['autopoint_photo'] > 0  || $reserves['autopoint_review'] > 0 ) {
					$reserves['autoemoneytitle'] = true; 
				}
			} 
			$this->template->assign('reserves',$reserves);
			$this->session->unset_userdata('sess_order');//비회원주문번호세션제거
		}
		$this->template->assign("templateskin","../../".$this->skin);//댓글, 이전/다음글, 파일첨부 경로추출

	}

	//사용자 게시물 등록/보기/리스트 스킨출력
	public function index($mode=null)
	{
		$this->lists();

		$this->template->assign('manager',$this->manager);//접근권한체크
		$board = $this->skin.'/board/'.BORADSKIN.'/index.html';
		if($mode == 'view'){
			$this->template->assign("viewlist",$mode);
			$this->template->define(array("listskin"=>$board));//게시글 상세 하단의 리스트출력
		}else{
			$this->template->define(array("skin"=>$board));
			$this->print_layout(FILE_PATH);
		}
	}

	//사용자 게시물 등록/수정
	public function lists($page=null)
	{
		if( defined('BOARDID') ) $this->template->assign(array('ISBOARDID'=>BOARDID));

		$this->pagetype = $page;
		$this->template->assign('pagetype',$page);
		if (!isset($this->manager['id'])) {
			pageBack('존재하지 않는 게시판입니다.');exit;
		}

		if( !$this->isplusfreenot && $this->manager['id'] == 'bulkorder' ){
			pageClose('무료몰Plus+에서는 대량구매게시판을 지원하지 않는 기능입니다.\n프리미엄몰+ 또는 독립몰+로 업그레이드 하시면 대량구매게시판을 이용 가능합니다.');
			exit;
		}

		//getmanagerauth('board_list');//* 관리자 권한 체크

		get_auth($this->manager, '', 'read', $isperm);//접근권한체크
		$this->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';
		$this->manager['fileperm_read']= (isset($isperm['fileperm_read']))?$isperm['fileperm_read']:'';
		if ( $isperm['isperm_read'] === false  && $page != 'goods' ) {
			if(!defined('__ISADMIN__')) {
				if(BOARDID == 'mbqna') {//1:1문의게시판은 마이페이지에서만 이용가능
					if(!empty($_GET['popup']) ) {pageClose('접근권한이 없습니다!');}else{
						$board = $this->skin.'/board/_permcheck.html';
						$this->template->assign('boardurl',$this->boardurl);
						$this->template->assign($data);
						$this->print_layout($board);
						exit;
					}
				}
			}
		}

		get_auth($this->manager, '', 'write', $isperm);//접근권한체크
		$this->manager['isperm_write'] = ($isperm['isperm_write'] === true)?'':'_no';//'.$this->manager['isperm_write'].'


		$this->boardurl->userurl = '../../board?id='.BOARDID;//사용자보기
		$this->template->assign('boardurl',$this->boardurl);//link url
		//분류리스트 categorylist
		if($this->manager['category']){
			$categorylist = @explode(",",$this->manager['category']);
			$this->template->assign('categorylist',$categorylist);
		}
		$this->template->assign('commentlay',$this->manager['auth_cmt_use']);
		
		/** 
		$boardsql['whereis']	= ' ';//전체
		$boardsql['select']		= ' seq, id, name, totalnum ';
		$boardmanagerlist = $this->Boardmanager->manager_whereis_list($boardsql);//바로가기게시판
		$this->template->assign('boardmanagerlist',$boardmanagerlist);//바로가기게시판 

		$sc['whereis']	= ' and skin_type = "default" and id != "'.BOARDID.'" ';//기본
		$sc['select']		= ' seq, id, name, totalnum ';
		$boardmanagercopylist = $this->Boardmanager->manager_whereis_list($sc);//게시판정보
		$this->template->assign('boardmanagercopylist',$boardmanagercopylist);//게시판복사목록
		**/

		/**
		 * notice setting
		**/
		$idxsc['orderby']			= 'gid';
		$idxsc['sort']				= '';
		$idxsc['notice']			= '1';

		$ndata = $this->Boardindex->idx_list($idxsc);//공지글목록
		/**
		 * list setting
		**/
		$sc['orderby']			= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'gid asc';//, m_date desc
		$sc['sort']					= (!empty($_GET['sort'])) ?			$_GET['sort']:' ';
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'0';
		if( (BOARDID == 'goods_qna' || BOARDID == 'goods_review') && $page == 'goods' ) {//pagenum
			$sc['perpage']			=  $this->manager['goods_num'];
		}else{
			$sc['perpage']			= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):$this->manager['pagenum'];
		}

		if($page == 'mypage' || BOARDID == 'mbqna' ) {//마이페이지와 1:1문의게시판인경우
			$sc['mseq']				= $this->userInfo['member_seq'];
		}

		if ($_GET['search_text'])
		{
			$sc['search_text'] = trim($_GET['search_text']);
			$sc['search_text']= stripslashes(htmlspecialchars($sc['search_text']));
		}

		$sc['rdate_s']			= (!empty($_GET['rdate_s']))?		$_GET['rdate_s']:'';
		$sc['rdate_f']			= (!empty($_GET['rdate_f']))?		$_GET['rdate_f']:'';
		$sc['category']			= (!empty($_GET['category']))?		$_GET['category']:'';


		$sc['goods_seq']		= (!empty($_GET['goods_seq']))?$_GET['goods_seq']:'';
		$sc['display']			= (!empty($_GET['display']))?		$_GET['display']:'';
		$sc['hidden']			= (!empty($_GET['hidden']))?		$_GET['hidden']:'';

		if(BOARDID == 'faq' ) $sc['hidden']			= 2;//노출여부

		$sc['score']				= (!empty($_GET['score']))?		$_GET['score']:'';//상품후기
		$sc['finish']				= (!empty($_GET['finish']))?		$_GET['finish']:'';//프로모션(원더플레이스)
		$data = $this->Boardmodel->data_list($sc);//게시글목록
		getBoardimagefile($data);//첨부파일 리스트추출용

		/**
		 * count setting
		**/
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = $this->Boardmodel->get_item_total_count($sc);

		$multi_copymove = true;//게시글이동복사여부
		switch ( BOARDID ) {
		 case "goods_qna":
			$multi_copymove =false;
		 break;
		 case "goods_review":
			$multi_copymove =false;//게시글이동복사 불가
			$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
			$query = $this->db->query($qry);
			$user_arr = $query -> result_array();
			$this->boardform_arr = $user_arr;//평점검색용
		 break;
		 case "faq":
			 unset($ndata);
			 $multi_copymove =false;//게시글이동복사 불가
		 break;
		 case "mbqna":
		 break;
		 case "bulkorder"://대량구매게시판
		 break;
		}
		$this->board_lists($ndata, $data, $sc, $noticeloop, $loop);

		//@2014-02-27 패치 이전소스때문에 board_comment.js 호출여부체크함
		$this->load->helper('file');
		if($page){//goods detail or mypage
			$view_layer = str_replace("_catalog","_view_layer",FILE_PATH);
			$headerhtmlfile	= ROOTPATH."data/skin/".$view_layer;
		}else{
			$headerhtmlfile	= ROOTPATH."data/skin/".$this->skin."/board/".BORADSKIN."/view.html";
		}
		$headerhtmlfilesource = read_file($headerhtmlfile);
		if( strpos($headerhtmlfilesource,'{#commentskin}') ) {
			$this->template->assign('commentskinjsuse',true);
		}

		$this->template->assign('multi_copymove',$multi_copymove);

		/**
		 * pagin setting
		**/
		$popup = (!empty($_GET['popup'])) ? $_GET['popup']:'';
		$iframe = (!empty($_GET['iframe'])) ? $_GET['iframe']:'';
		$paginlay =  pagingtagfront($sc['searchcount'], $sc['perpage'], $this->boardurl->resets, getLinkFilter('',array_keys($sc)) );
		if(isset($noticeloop)) $this->template->assign('noticeloop',$noticeloop);
		if(isset($loop)) $this->template->assign('loop',$loop);
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('sc',$sc);

	}

	public function board_lists($ndata, $data, $sc, & $noticeloop, & $loop)
	{
		$idx = 0;
		foreach($ndata['result'] as $ndatarow) {$idx++;
			if( $ndatarow['onlynotice'] == 1 && ($ndatarow['onlynotice_sdate'] && $ndatarow['onlynotice_edate']) && !( date('Y-m-d') >=  $ndatarow['onlynotice_sdate'] && date('Y-m-d')  <=  $ndatarow['onlynotice_edate']) ) continue;//공지만노출시 기간체크
			
			$noticesql['whereis']	= ' and gid= "'.$ndatarow['gid'].'" ';
			if( !(BOARDID == 'goods_qna' || BOARDID == 'goods_review'  || BOARDID == 'bulkorder') ) {
				$noticesql['whereis']	.= ' and boardid= "'.BOARDID.'" ';
			}
			$noticesql['select']		= ' * ';
			$notice = $this->Boardmodel->get_data($noticesql);//게시판목록

			if ( ( BOARDID == 'goods_qna' ||  BOARDID == 'goods_review' ) && $this->pagetype == 'goods' ) {//상품상세 공지글 노출여부
				if( $notice['goods_seq'] && $notice['goods_seq'] != $_GET['goods_seq'] ) continue;
			}

			if(isset($notice['seq'])) {
				$notice['number'] = (!isset($_GET['seq']) || $_GET['seq'] != $notice['seq']) ? '<img src="'.$this->notice_img.'" title="공지" >' : ' <span class="now">&gt;&gt;</span> ';//공지
 
				if($notice['hidden'] == 1  && $notice['mseq'] < 1  && BOARDID != 'faq' ) {//비밀글
					$notice['iconhidden'] = ' <img src="'.$this->icon_hidden_img.'" title="비밀글" > '; 
				}else{
					$notice['iconhidden'] = '';
				}

				getminfo($this->manager, $notice, $minfo, $boardname);//회원정보
				$notice['name'] = $boardname;
				$notice['category'] = (!empty($notice['category']) )? ' <span class="cat">['.$notice['category'].']</span>':'';

				if($this->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$notice['r_date']),0,8))) >= date("Ymd") ) {//new
					$notice['iconnew']	= ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ';
				}

				if($this->manager['icon_hot_visit'] > 0 && $this->manager['icon_hot_visit'] <= $notice['hit'] ) {//조회수
					$notice['iconhot']		= ' <img src="'.$this->icon_hot_img.'" title="hot" > ';
				}

				if( getBoardFileck($notice['upload'], $notice['contents']) ) {//첨부파일
					$notice['iconfile']		= ' <img src="'.$this->icon_file_img.'" title="첨부파일" align="absmiddle" > ';
				}
				if( boardisimage($notice['upload'], $notice['contents']) ) {//첨부파일 > image
					$notice['iconimage']		= ' <img src="'.$this->icon_image_img.'" title="첨부파일" align="absmiddle" > ';
					$notice['isphoto']		= '포토';
				}else{
					$notice['isphoto']		= '일반';
				}
				if( $notice['file_key_w'] && uccdomain('fileswf',$notice['file_key_w']) ) {//첨부파일 > video
					$notice['iconvideo']		= ' <img src="'.$this->icon_video_img.'" title="동영상" align="absmiddle" > ';
				}
				if(isMobilecheck($notice['agent'])) {//agent > mobile ckeck
					$notice['iconmobile']		= ' <img src="'.$this->icon_mobile_img.'" title="모바일" align="absmiddle" > ';
				}

					$notice['iconbest']		= ($notice['best'] == 'checked')?' <img src="'.$this->icon_best_img.'" title="best" /> ':' <img src="'.$this->icon_best_gray_img.'" title="not best" /> ';
				$notice['iconaward']		= ($notice['best'] == 'checked')?' <img src="'.$this->icon_award_img.'" title="best" /> ':'';

				$notice['subject_real'] 	= $notice['subject'];
				if( $this->manager['viewtype'] != "layer" ) {
					$notice['subject']		= (  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ? getstrcut(strip_tags($notice['subject']), $this->manager['mobile_subjectcut']):getstrcut(strip_tags($notice['subject']), $this->manager['subjectcut']);
				}
				$notice['subjectcut']		= $notice['subject'];

				if( BOARDID == 'goods_qna' ||  BOARDID == 'goods_review' ) {
					$commentcnt = ($notice['comment']>0 && !(  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ) ? ' <span class="comment">('.number_format($notice['comment']).')</span>':'';
				}else{
					$commentcnt = ($notice['comment']>0) ? ' <span class="comment">('.number_format($notice['comment']).')</span>':'';
				}
				$notice['commentcnt'] = $commentcnt;

				get_auth($this->manager,$notice, 'read', $isperm);//접근권한체크1
				$this->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';
				$this->manager['fileperm_read']= (isset($isperm['fileperm_read']))?$isperm['fileperm_read']:''; 
				get_auth_btn($this->manager,$notice);//접근권한체크2 
				$notice['isperm_read']		= $this->manager['isperm_read'];
				$notice['fileperm_read']	= $this->manager['fileperm_read'];

				$notice['subject']		= $notice['category'].' <span class="hand highlight-link boad_view_btn'.$this->manager['isperm_read'].'" viewtype="'.$this->manager['viewtype'].'"  pagetype="'.$this->pagetype.'" viewlink="'.$this->boardurl->view.$notice['seq'].'" fileperm_read="'.$this->manager['fileperm_read'].'"  board_seq="'.$notice['seq'].'"  board_id="'.BOARDID.'" ><a>'.$notice['subject'].'</a>&nbsp;&nbsp; </span>'.$commentcnt;

				$notice['date']			= substr($notice['r_date'],0,16);//등록일

				if( BOARDID == 'goods_review' ){
					if($this->manager['goods_review_type'] == 'INT' && $notice['reviewcategory']){
						$notice['scorelay'] = getGoodsScore($notice['score_avg'], $this->manager);
						if(sizeof(explode(",",$notice['reviewcategory']))>1) $notice['score_avg_lay'] = 'score_avg';
					}else{
						$notice['scorelay'] = getGoodsScore($notice['score'], $this->manager);
					}
					$notice['emoneylay']	=  getBoardEmoneybtn($notice, $this->manager, 'view');

					//평가정보노출
					if($notice['adddata']){
						unset($adddata);
						$msubdata = @explode("|",$notice['adddata']);
						if(!$adddata[$msubdatakeyseq])$add=0;
						foreach ($msubdata as $msubdataar){
							$msubdatakey = @explode("^^",$msubdataar);
							$msubdatakeyseq = str_replace("bulkorderform_seq=","",$msubdatakey[1]);
							foreach ($msubdatakey as $msubdataval){if(!$adddata[$msubdatakeyseq])$add=0;
								$msubdatavalreal = @explode("=",$msubdataval);
								if($msubdatavalreal[1]) $adddata[$msubdatakeyseq][$add][$msubdatavalreal[0]] = $msubdatavalreal[1];
							}
							if($adddata[$msubdatakeyseq])$add++;
						}
						$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
						$query = $this->db->query($qry);
						$user_arr = $query -> result_array();
						unset($goodsreview_sub);
						foreach ($user_arr as $user){
							$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$adddata[$user['bulkorderform_seq']],'view');
							$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
							$goodsreview_sub[] = $user;
						}
						$notice['goodsreview_sub'] = $goodsreview_sub;
					}

					//상품후기의 평가점수
					if($this->manager['reviewcategory']){
						unset($reviewcategorylist);
						$reviewcategoryar = @explode(",",$this->manager['reviewcategory']);
						$reviewcategorydataar = @explode(",",$notice['reviewcategory']);
						foreach($reviewcategoryar as $k=>$reviewcategory){
							$reviewcategorylistar['idx'] = $k;
							$reviewcategorylistar['title'] = $reviewcategory;
							$reviewcategorylistar['score'] = $reviewcategorydataar[$k];
							$reviewcategorylist[] = $reviewcategorylistar;
						}
						$notice['reviewcategorylist'] = $reviewcategorylist;
					}

					$notice['buyertitle'] = ($notice['order_seq'])?'구매':'미구매';
				}else{
					//평점이 있는경우 평점정보 추가
					if($notice['score_avg'] ) {//&& ($this->storeMode || $this->storefammerceMode || $this->storemobileMode)
						$img_full = "<img src='/data/skin/store_default/images/design/star_full.gif' title='".$notice['score_avg']."' />";
						$img_half = "<img src='/data/skin/store_default/images/design/star_half.gif' title='".$notice['score_avg']."' />";
						$img_empty = "<img src='/data/skin/store_default/images/design/star_empty.gif' title='".$notice['score_avg']."' />";

						$fullStar = $notice['score_avg'] / 2;
						$halfStar = $notice['score_avg'] % 2;

						$totalStar = 0;
						$emptyStar = 0;
						$printStar = "";

						for ($i = 1; $i <= $fullStar; $i++) { $printStar .= $img_full; $totalStar++; }
						for ($i = 1; $i <= $halfStar; $i++) { $printStar .= $img_half; $totalStar++; }

						$emptyStar = 5 - $totalStar;
						for ($i = 1; $i <= $emptyStar; $i++) { $printStar .= $img_empty; }
						$notice['scorelay'] = $printStar;
					}
				}

				$contents_tmp =  str_replace('&nbsp;', ' ', str_ireplace('</p>',' ',$notice['contents']));
				$notice['contentcut']		= getstrcut(strip_tags($contents_tmp), $this->manager['contcut']); 
				
				if($notice['hidden'] == 1  && BOARDID != 'faq' && $this->manager['isperm_read']) {
					$notice['contents']		= ' <span class="gray">비밀글입니다</span>';
					$notice['contentcut']		= ' <span class="gray">비밀글입니다</span>';
				}
				
				//리스트노출 게시글평가
				$notice['recommendlay'] =  getRecommendviewer($notice);

				if( BOARDID == 'goods_qna' || BOARDID == 'goods_review'  || BOARDID == 'bulkorder'  ) {
					if(!empty($notice['goods_seq']) && $notice['depth'] == 0 ){
							if( BOARDID == 'bulkorder' ) {
								$notice['goodsInfo']		= getBulkorderGoodsinfo($notice, $notice['goods_seq'], 'write');
								if($notice['goodsInfo'][0]) $notice['goodsInfo'] = $notice['goodsInfo'][0];
								$notice['subject']			= getBulkorderGoodsinfo($notice, $notice['goods_seq'], 'list');
							}else{
								$notice['goodsInfo']		= getGoodsinfo($notice, $notice['goods_seq'], 'write');
								if($notice['goodsInfo'][0]) $notice['goodsInfo'] = $notice['goodsInfo'][0];
								$notice['subject']			= getGoodsinfo($notice, $notice['goods_seq'], 'list');
							}
					}else{
						$notice['subject']		= $notice['category'].' <span class="hand highlight-link boad_view_btn'.$this->manager['isperm_read'].'" viewtype="'.$this->manager['viewtype'].'"  pagetype="'.$this->pagetype.'" viewlink="'.$this->boardurl->view.$notice['seq'].'" fileperm_read="'.$this->manager['fileperm_read'].'"  board_seq="'.$notice['seq'].'"  board_id="'.BOARDID.'" ><a>'.$notice['subject'].'</a>&nbsp;&nbsp; </span>'.$commentcnt;
					}
				} 

				$noticeloop[] = $notice;
			}

			unset($notice);
		}//endforeach

		$idx = 0;
		foreach($data['result'] as $datarow) {$idx++;

			$datarow['number'] = ((isset($_GET['seq']) && ($_GET['seq']) != $datarow['seq']) || (!isset($_GET['seq']))) ? $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1: ' <span class="now">&gt;&gt;</span> ';//번호

			$datarow['category'] = (!empty($datarow['category']))? ' <span class="cat">['.$datarow['category'].']</span>':'';

			getminfo($this->manager, $datarow, $mdata, $boardname);//회원정보
			$datarow['name'] = $boardname;

			/**$replysc['whereis']	= ' and gid > '.$datarow['gid'].' and gid < '.(intval($datarow['gid'])+1) . ' ';//답변여부
			//$replysc['select']		= "  count(gid)  ";
			$datarow['replyor']	= $this->Boardindex->get_data_numrow($replysc);**/
			$replysc['whereis'] = ' and gid > '.$datarow['gid'].' and gid < '.(intval($datarow['gid'])+1).' and parent = '.($datarow['seq']).' ';//답변여부
			$datarow['replyor'] = $this->Boardmodel->get_data_numrow($replysc); 

			$datarow['re_contents_real'] 	= $datarow['re_contents'];
			$datarow['re_contents']		= (  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ? getstrcut(strip_tags($datarow['re_contents']), $this->manager['subjectcut']):getstrcut(strip_tags($datarow['re_contents']), $this->manager['subjectcut']);
			$datarow['date']			= substr($datarow['r_date'],0,16);//등록일

			if(  $this->siteType == 'mobile'  && ( BOARDID != 'notice' ) ) {
				if($datarow['re_contents_real']){
					if($datarow['re_date'] ){
						if( getDateFormat($datarow['re_date'],"Y-m-d") == date("Y-m-d") ){
							$datarow['reply_title']		= '<span style="color:#06C" >답변 '.getDateFormat($datarow['re_date'],"H:i").' </span>';
						}else{
							$datarow['reply_title']		= '<span style="color:#06C" >답변 '.getDateFormat($datarow['re_date'],"Y-m-d").'</span>';
						}
					}else{
						$datarow['reply_title']		= ($datarow['re_contents_real'])?'<span class="blue" >답변완료</span>':'<span class="gray" >답변대기</span>';//상태
					}
				}else{
					$datarow['reply_title']		= '<span class="gray" >답변대기</span>';
				}
			}else{
				$datarow['reply_title']		= ($datarow['re_contents_real'])?'<span class="blue" >답변완료</span>':'<span class="gray" >답변대기</span>';//상태
			}

			if( BOARDID == 'faq' ) {//faq hidden 노출여부로 이용됨
				$hiddenckeck	= ($datarow['hidden'] == "1") ? ' checked ':'';//비밀글/노출글 '노출':'미노출'
				$datarow['tdclass']	= ($datarow['hidden'] == "1") ? ' bg-silver ':'';
			}else{
				$datarow['tdclass']	= ($datarow['re_contents_real']) ? ' bg-silver ':'';
			}

			$datarow['subject_real'] 	= $datarow['subject'];
			if( BOARDID != 'faq' ) {//faq
				if( $this->manager['viewtype'] != "layer" && !$this->pagetype ) {
					$datarow['subject']		= (  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ? getstrcut(strip_tags($datarow['subject']), $this->manager['mobile_subjectcut']):getstrcut(strip_tags($datarow['subject']), $this->manager['subjectcut']);
				}
			}
			$datarow['subjectcut']		= $datarow['subject'];

			get_auth($this->manager,$datarow, 'read', $isperm);//접근권한체크
			$this->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';
			$this->manager['fileperm_read']= (isset($isperm['fileperm_read']))?$isperm['fileperm_read']:'';
			
			if( BOARDID != 'faq' && $datarow['notice'] != '1' ) {//faq hidden 노출여부로 이용됨
				get_auth_btn($this->manager,$datarow);//접근권한체크 isperm_read
			}

			if( BOARDID == 'goods_review' ){
				if($this->manager['goods_review_type'] == 'INT' && $datarow['reviewcategory']) {
					$datarow['scorelay'] = getGoodsScore($datarow['score_avg'], $this->manager);
					if(sizeof(explode(",",$datarow['reviewcategory']))>1) $datarow['score_avg_lay'] = 'score_avg';
				}else{
					$datarow['scorelay'] = getGoodsScore($datarow['score'], $this->manager);
				}
				$datarow['emoneylay']	=  getBoardEmoneybtn($datarow, $this->manager, 'view');

				//평가정보노출
				if($datarow['adddata']){
					unset($adddata);
					$msubdata = @explode("|",$datarow['adddata']);
					if(!$adddata[$msubdatakeyseq])$add=0;
					foreach ($msubdata as $msubdataar){
						$msubdatakey = @explode("^^",$msubdataar);
						$msubdatakeyseq = str_replace("bulkorderform_seq=","",$msubdatakey[1]);
						foreach ($msubdatakey as $msubdataval){if(!$adddata[$msubdatakeyseq])$add=0;
							$msubdatavalreal = @explode("=",$msubdataval);
							if($msubdatavalreal[1]) $adddata[$msubdatakeyseq][$add][$msubdatavalreal[0]] = $msubdatavalreal[1];
						}
						if($adddata[$msubdatakeyseq])$add++;
					}
					$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
					$query = $this->db->query($qry);
					$user_arr = $query -> result_array();
					unset($goodsreview_sub);
					foreach ($user_arr as $user){
						$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$adddata[$user['bulkorderform_seq']],'view');
						$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
						$goodsreview_sub[] = $user;
					}
					$datarow['goodsreview_sub'] = $goodsreview_sub;
				}

				//상품후기의 평가점수
				if($this->manager['reviewcategory']){
					unset($reviewcategorylist);
					$reviewcategoryar = @explode(",",$this->manager['reviewcategory']);
					$reviewcategorydataar = @explode(",",$datarow['reviewcategory']);
					foreach($reviewcategoryar as $k=>$reviewcategory){
						$reviewcategorylistar['idx'] = $k;
						$reviewcategorylistar['title'] = $reviewcategory;
						$reviewcategorylistar['score'] = $reviewcategorydataar[$k];
						$reviewcategorylist[] = $reviewcategorylistar;
					}
					$datarow['reviewcategorylist'] = $reviewcategorylist;
				}

				$datarow['buyertitle'] = ($datarow['order_seq'])?'구매':'미구매';
			}else{
				//평점이 있는경우 평점정보 추가
				if($datarow['score_avg'] ) {//&& ($this->storeMode || $this->storefammerceMode || $this->storemobileMode)
					$img_full = "<img src='/data/skin/store_default/images/design/star_full.gif' title='".$datarow['score_avg']."' />";
					$img_half = "<img src='/data/skin/store_default/images/design/star_half.gif' title='".$datarow['score_avg']."' />";
					$img_empty = "<img src='/data/skin/store_default/images/design/star_empty.gif' title='".$datarow['score_avg']."' />";

					$fullStar = $datarow['score_avg'] / 2;
					$halfStar = $datarow['score_avg'] % 2;

					$totalStar = 0;
					$emptyStar = 0;
					$printStar = "";

					for ($i = 1; $i <= $fullStar; $i++) { $printStar .= $img_full; $totalStar++; }
					for ($i = 1; $i <= $halfStar; $i++) { $printStar .= $img_half; $totalStar++; }

					$emptyStar = 5 - $totalStar;
					for ($i = 1; $i <= $emptyStar; $i++) { $printStar .= $img_empty; }
					$datarow['scorelay'] = $printStar;
				}
			}

			if($datarow['display'] == 1 ){//삭제시
				$datarow['iconnew']	= '';
				$datarow['iconhot']		= '';
				$datarow['iconfile']		= '';
				$datarow['iconimage']	= '';
				$datarow['iconmobile']		= '';
				if($datarow['hidden'] == 1  && BOARDID != 'faq' ) {//비밀글
					$datarow['iconhidden'] = ' <img src="'.$this->icon_hidden_img.'" title="비밀글" > ';
				}

				if( BOARDID == 'goods_review' ||  BOARDID == 'goods_qna' ) {
					$datarow['blank']			= ($datarow['depth']>0) ? ' <img src="'.$this->blank_img.'" title="blank" width="'.(($datarow['depth']-1)*53).'"  height="1"><img src="'.$this->re_img.'" title="답변" >':'';//답변
					$commentcnt = ($datarow['comment']>0 && !(  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ) ? ' <span class="comment">('.number_format($datarow['comment']).')</span>':'';
				}else{
					$datarow['blank']			= ($datarow['depth']>0) ? ' <img src="'.$this->blank_img.'" title="blank" width="'.(($datarow['depth']-1)*13).'"  height="1"><img src="'.$this->re_img.'" title="답변" >':'';//답변
					$commentcnt = ($datarow['comment']>0) ? ' <span class="comment">('.number_format($datarow['comment']).')</span>':'';
				}

				$datarow['commentcnt'] = $commentcnt;

				$datarow['isperm_read']		= $this->manager['isperm_read'];
				$datarow['fileperm_read']	= $this->manager['fileperm_read'];

				if( BOARDID == 'faq' ) {
					$datarow['subject']		= $datarow['blank'].$datarow['category'].' <span class="hand gray boad_faqview_btn " viewtype="'.$this->manager['viewtype'].'"  pagetype="'.$this->pagetype.'"  viewlink="'.$this->boardurl->view.$datarow['seq'].'" fileperm_read="'.$this->manager['fileperm_read'].'"  board_seq="'.$datarow['seq'].'"  board_id="'.BOARDID.'" ><a>삭제되었습니다 ['.substr($datarow['d_date'],0,16).']</a></span>'.$commentcnt;
				}else{
					$datarow['subject']		= $datarow['blank'].$datarow['category'].' <span class="hand gray boad_view_btn'.$this->manager['isperm_read'].'" viewtype="'.$this->manager['viewtype'].'"  pagetype="'.$this->pagetype.'"  viewlink="'.$this->boardurl->view.$datarow['seq'].'" fileperm_read="'.$this->manager['fileperm_read'].'"  board_seq="'.$datarow['seq'].'"  board_id="'.BOARDID.'" ><a>삭제되었습니다 ['.substr($datarow['d_date'],0,16).']</a></span>'.$commentcnt;
				}
				$datarow['subjectcut']		= "<span class='gray'>삭제되었습니다</span>";
				$datarow['subject_real'] 	= $datarow['subjectcut'];

				if($datarow['replyor'] == 0 && $datarow['comment'] == 0) {//삭제후 답변이나  댓글이 없는 경우 삭제가능
					$datarow['deletebtn'] = '<span class="btn small  valign-middle"><input type="button" name="boad_delete_btn" board_seq="'.$datarow['seq'].'"  board_id="'.BOARDID.'" value="삭제" /></span>';
				}

			}else{

				if( $this->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$datarow['r_date']),0,8))) >= date("Ymd") ) {//new
					$datarow['iconnew']	= ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ';
				}else{
					$datarow['iconnew'] ='';
				}

				$datarow['iconhot']		= ($this->manager['icon_hot_visit'] > 0 && $this->manager['icon_hot_visit'] <= $datarow['hit']) ? ' <img src="'.$this->icon_hot_img.'" title="hot" > ':'';//조회수
				$datarow['iconfile']		= ( getBoardFileck($datarow['upload'], $datarow['contents'])  ) ?' <img src="'.$this->icon_file_img.'" title="첨부파일" align="absmiddle" > ':'';//첨부파일

				$datarow['iconbest']		= ($datarow['best'] == 'checked')?' <img src="'.$this->icon_best_img.'" title="best" /> ':' <img src="'.$this->icon_best_gray_img.'" title="not best" /> ';
				$datarow['iconaward']		= ($datarow['best'] == 'checked')?' <img src="'.$this->icon_award_img.'" title="베스트" /> ':'';


				if( boardisimage($datarow['upload'],$datarow['contents']) ) {//첨부파일 > image
					$datarow['iconimage']		= ' <img src="'.$this->icon_image_img.'" title="첨부파일" align="absmiddle" > ';
					$datarow['isphoto']		= '포토';
				}else{
					$datarow['isphoto']		= '일반';
				}

  				if( $datarow['file_key_w'] && uccdomain('fileswf',$datarow['file_key_w']) ) {//첨부파일 > video
					$datarow['iconvideo']		= ' <img src="'.$this->icon_video_img.'" title="동영상" align="absmiddle" > ';
				}

				if(isMobilecheck($datarow['agent'])) {//agent > mobile ckeck
					$datarow['iconmobile']		= ' <img src="'.$this->icon_mobile_img.'" title="모바일" align="absmiddle" > ';
				} 

				$datarow['iconhidden'] = ($datarow['hidden'] == 1  && BOARDID != 'faq' ) ? ' <img src="'.$this->icon_hidden_img.'" title="비밀글" > ':'';

				//동영상
				if( isMobilecheck($datarow['agent']) && $datarow['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
					$datarow['uccdomain_thumbnail']	= uccdomain('thumbnail',$datarow['file_key_i'], $this->manager);
					$datarow['uccdomain_fileswf']			= uccdomain('fileswf',$datarow['file_key_i'], $this->manager);
					$datarow['uccdomain_fileurl']			= uccdomain('fileurl',$datarow['file_key_i'], $this->manager);
				}elseif( uccdomain('thumbnail',$datarow['file_key_w'], $this->manager) && $datarow['file_key_w'] ) {
					$datarow['uccdomain_thumbnail']	= uccdomain('thumbnail',$datarow['file_key_w'], $this->manager);
					$datarow['uccdomain_fileswf']			= uccdomain('fileswf',$datarow['file_key_w'], $this->manager);
					$datarow['uccdomain_fileurl']			= uccdomain('fileurl',$datarow['file_key_w'], $this->manager);
				}
				if( BOARDID == 'goods_review' ||  BOARDID == 'goods_qna' ) {
					$datarow['blank']			= ($datarow['depth']>0) ? ' <img src="'.$this->blank_img.'" title="blank" width="'.(($datarow['depth']-1)*53).'"  height="1"><img src="'.$this->re_img.'" title="답변" >':'';//답변
					$commentcnt = ($datarow['comment']>0 && !(  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ) ? ' <span class="comment">('.number_format($datarow['comment']).')</span>':'';
				}else{
					$datarow['blank']			= ($datarow['depth']>0) ? ' <img src="'.$this->blank_img.'" title="blank" width="'.(($datarow['depth']-1)*13).'"  height="1"><img src="'.$this->re_img.'" title="답변" >':'';//답변
					$commentcnt = ($datarow['comment']>0) ? ' <span class="comment">('.number_format($datarow['comment']).')</span>':'';
				}

				$datarow['commentcnt'] = $commentcnt;


				$datarow['isperm_read']		= $this->manager['isperm_read'];
				$datarow['fileperm_read']	= $this->manager['fileperm_read'];
				$datarow['subject_real'] 	= $datarow['subject'];

				if( BOARDID == 'faq' ) {
					$datarow['subject']		= $datarow['blank'].$datarow['category'].' <span class="hand highlight-link boad_faqview_btn " viewtype="'.$this->manager['viewtype'].'"  pagetype="'.$this->pagetype.'"  viewlink="'.$this->boardurl->view.$datarow['seq'].'" fileperm_read="'.$this->manager['fileperm_read'].'"  board_seq="'.$datarow['seq'].'"  board_id="'.BOARDID.'" ><a>'.$datarow['subject'].'</a> &nbsp;&nbsp; </span>'.$commentcnt;
				}else{
					$datarow['subject']		= $datarow['blank'].$datarow['category'].' <span class="hand highlight-link  boad_view_btn'.$this->manager['isperm_read'].'" viewtype="'.$this->manager['viewtype'].'"  pagetype="'.$this->pagetype.'"  viewlink="'.$this->boardurl->view.$datarow['seq'].'" fileperm_read="'.$this->manager['fileperm_read'].'"  board_seq="'.$datarow['seq'].'"  board_id="'.BOARDID.'" ><a>'.$datarow['subject'].'</a> &nbsp;&nbsp; </span>'.$commentcnt;
				}

				$contents_tmp =  str_replace('&nbsp;', ' ',str_ireplace('</p>',' ',$datarow['contents']));
				$datarow['contentcut']		= getstrcut(strip_tags($contents_tmp), $this->manager['contcut']);
			}
			
			if($datarow['hidden'] == 1  && BOARDID != 'faq' && $this->manager['isperm_read']) {
				$datarow['contents']		= ' <span class="gray">비밀글입니다</span>';
				$datarow['contentcut']		= ' <span class="gray">비밀글입니다</span>';
			}
			
			//리스트노출 게시글평가
			$datarow['recommendlay'] =  getRecommendviewer($datarow); 

			if( BOARDID == 'faq' ){
				getBoardUploadAllfiles($datarow);//전체첨부파일 가져오기(상단에서 이미지만 추출함)
			}

			if( BOARDID == 'goods_qna' || BOARDID == 'goods_review'  || BOARDID == 'bulkorder'  ) {
				if(!empty($datarow['goods_seq']) && $datarow['depth'] == 0 ){
						if( BOARDID == 'bulkorder' ) {
							$datarow['goodsInfo']		= getBulkorderGoodsinfo($datarow, $datarow['goods_seq'], 'write');
							if($datarow['goodsInfo'][0]) $datarow['goodsInfo'] = $datarow['goodsInfo'][0];
							$datarow['subject']			= getBulkorderGoodsinfo($datarow, $datarow['goods_seq'], 'list');
						}else{
							$datarow['goodsInfo']		= getGoodsinfo($datarow, $datarow['goods_seq'], 'write');
							if($datarow['goodsInfo'][0]) $datarow['goodsInfo'] = $datarow['goodsInfo'][0];
							$datarow['subject']			= getGoodsinfo($datarow, $datarow['goods_seq'], 'list');
						}
				}else{
					$datarow['subject']	= $datarow['iconmobile'].$datarow['subject'].$datarow['iconimage'].$datarow['iconfile'].$datarow['iconnew'].$datarow['iconhot'].$datarow['iconhidden'];
				}
			}
			$loop[] = $datarow;
		}
	}

	//사용자 게시물 등록/수정
	public function write($page=null)
	{
 		if ( !defined('BOARDID') ) {
			if(!empty($_GET['popup']) ) { pageClose('존재하지 않는 게시판입니다.');}else{ pageBack('존재하지 않는 게시판입니다.');}
		}

		if (empty($this->manager['id'])) {
			if(!empty($_GET['popup']) ) { pageClose('존재하지 않는 게시물입니다.');}else{ pageBack('존재하지 않는 게시판입니다.');}
		}
		if( defined('BOARDID') ) $this->template->assign(array('ISBOARDID'=>BOARDID));
		
		//getmanagerauth('board_act');//* 부관리자 권한 체크
		$this->template->assign('writeditorjs',true);//에딧터


		if( $page == 'mypage' && $_GET['order_seq'] && $_GET['goods_seq'] && !$_GET['seq']) {//마이페이지 등록된 경우 수정모드
			$goodsreviewsc = $this->db->query("select  seq as board_seq, r_date as board_rdate  from fm_goods_review where mseq = '".$this->userInfo['member_seq']."' and order_seq = '".$_GET['order_seq']."' and goods_seq = '".$_GET['goods_seq']."' ");
			$modify_seq = $goodsreviewsc->row_array();
			if($modify_seq) $_GET['seq'] = $modify_seq['board_seq'];
		}


		$displayGoods = '';
		if ( !empty($_GET['seq']) ) {//수정시

			$sc['whereis']	= ' and seq= "'.$_GET['seq'].'" ';
			$sc['select']		= ' * ';
			$data = $this->Boardmodel->get_data($sc);//게시글보기

			if(( $this->siteType == 'mobile' ) || $this->_is_mobile_agent){// !$data['editor'] ||
				$data['contents'] = strip_tags(str_ireplace('</p>',' ',$data['contents']));
			}

			$idxsc['select']			= ' * ';
			$idxsc['whereis']		= ' and gid = "{$data[gid]}" ';
			$idxdata = $this->Boardindex->get_data($idxsc);//공지글목록
			if( $idxdata ) {
				$data['onlynotice'] = $idxdata['onlynotice'];
				$data['onlynotice_sdate'] = $idxdata['onlynotice_sdate'];
				$data['onlynotice_edate'] = $idxdata['onlynotice_edate'];
			}

			if( BOARDID == 'goods_review' || BOARDID == 'goods_qna'  || BOARDID == 'bulkorder' ) {
				$data['boardid'] = ($data['boardid'])?$data['boardid']:BOARDID;
			}
			if (empty($data['seq'])) {
				if(!empty($_GET['popup']) ) { pageClose('존재하지 않는 게시물입니다.');}else{ pageBack('존재하지 않는 게시물입니다.');}
			}

			if ( $data['re_contents'] && ( BOARDID == 'mbqna' || BOARDID == 'goods_qna' ) ) {//답변상태수정불가
				if(!empty($_GET['popup']) ) { pageClose('답변이 등록된 상태입니다.\n수정하실 수 없습니다.');}else{ pageBack('답변이 등록된 상태입니다.\n수정하실 수 없습니다.');}
			}

			get_auth($this->manager, $data, 'write' , $isperm);//접근권한체크
			if ( $isperm['isperm_write'] === false ) {
				//if(!defined('__ISADMIN__')) {
					if(!empty($_GET['popup']) ) {  pageClose('접근권한이 없습니다!');}else{
						if(!$this->boardurl->perm){
							if($_SERVER['HTTP_REFERER']) {
								pageBack('접근권한이 없습니다!');
								exit;
							}else{
								$url = "/main/index";
								pageRedirect($url,'접근권한이 없습니다!');
								exit;
							}
						}else{
							$board = $this->skin.'/board/_permcheck.html';
							$this->template->assign('boardurl',$this->boardurl);
							$this->template->assign($data);
							$this->print_layout($board);
							exit;
						}
					}
				//}
			}

			get_auth($this->manager, $data, 'reply' , $isperm);//접근권한체크
			if ( $isperm['isperm_reply'] === false && $_GET['reply'] == 'Y' ) {
				//if(!defined('__ISADMIN__')) {
					if(!empty($_GET['popup']) ) {  pageClose('접근권한이 없습니다!');}else{
						if(!$this->boardurl->perm){
								if($_SERVER['HTTP_REFERER']) {
									pageBack('접근권한이 없습니다!');
									exit;
								}else{
									$url = "/main/index";
									pageRedirect($url,'접근권한이 없습니다!');
									exit;
								}
							}else{
							$board = $this->skin.'/board/_permcheck.html';
							$this->template->assign('boardurl',$this->boardurl);
							$this->template->assign($data);
							$this->print_layout($board);
							exit;
						}
					}
				//}
			}

			if($data['tmpcode']){
				$this->session->set_userdata('tmpcode',$data['tmpcode']);
			}else{
				//첨부파일 등록
				$data['tmpcode'] = BOARDID.'^^'.substr(microtime(), 2, 6).'^^'.$data['mseq'];
				$this->session->set_userdata('tmpcode',$data['tmpcode']);
			}

			$isperm_moddel = ( $isperm['isperm_moddel'] === true)?'':'_no';//수정/삭제권한

			if(!empty($data['goods_seq'])){
				if( BOARDID == 'bulkorder' ) {
					$displayGoods = getBulkorderGoodsinfo($data, $data['goods_seq'].','.$_GET['goods_seq'], 'write');
				}else{
					$displayGoods = getGoodsinfo($data, $data['goods_seq'].','.$_GET['goods_seq'], 'write');
				}
			}

			if (!empty($_GET['reply']) == 'Y' && (BOARDID != 'mbqna' && BOARDID != 'bulkorder') ) {//&& BOARDID != 'goods_qna'
				$data['subject']		= $this->Boardmanager->board_restr.$data['subject'];//답변
				$data['contents']		= $this->Boardmanager->board_cont_restr.$data['contents'];
				if( !($this->userInfo['member_seq']) ) {
					$data['name']		= '';
					$data['pw']		= '';
					$data['email']		= '';
					$data['tel1']		= '';
					$data['tel2']		= '';
				}
				$mode = "board_write";
			}else{
				if ( $isperm_moddel == '_no' ) {
					if( (($data['mseq'] > 0 ) && $data['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || ( !empty($data['mseq']) && !defined('__ISUSER__') ) ) {
						//if(!defined('__ISADMIN__')) {
							if(!empty($_GET['popup']) ){
								pageClose('접근권한이 없습니다!');
							}else{
								if(!$this->boardurl->perm){
									if($_SERVER['HTTP_REFERER']) {
										pageBack('접근권한이 없습니다!');
										exit;
									}else{
										$url = "/main/index";
										pageRedirect($url,'접근권한이 없습니다!');
										exit;
									}
								}else{
									$board = $this->skin.'/board/_permcheck.html';
									$this->template->assign('boardurl',$this->boardurl);
									$this->template->assign($data);
									$this->print_layout($board);
									exit;
								}
							}
						//}
					}else{

						$ss_pwwrite_name = 'board_pwwrite_'.BOARDID;
						$boardpwwritess = $this->session->userdata($ss_pwwrite_name);
						if ( (isset($data['seq']) && !strstr($boardpwwritess,'['.$data['seq'].']') && !empty($boardpwwritess)) || empty($boardpwwritess)) {
							//if(!defined('__ISADMIN__')) {
								if(!empty($_GET['popup']) ){
									pageClose('접근권한이 없습니다!');
								}else{
									if(!$this->boardurl->perm){
										if($_SERVER['HTTP_REFERER']) {
											pageBack('접근권한이 없습니다!');
											exit;
										}else{
											$url = "/main/index";
											pageRedirect($url,'접근권한이 없습니다!');
											exit;
										}
									}else{
										$board = $this->skin.'/board/_permcheck.html';
										$this->template->assign('boardurl',$this->boardurl);
										$this->template->assign($data);
										$this->print_layout($board);
										exit;
									}
								}
							//}
						}
					}
				}

				$mode = "board_modify";
			}

			if( BOARDID == 'bulkorder' ) {
				$cfg['order'] = config_load('order');
				$this->template->assign('cfg',$cfg);

				if( $this->config_system['pgCompany'] ){
					$payment_gateway = config_load($this->config_system['pgCompany']);
					$payment_gateway['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');

					foreach($payment_gateway['arrKcpCardCompany'] as $k => $v){
						$payment_gateway['arrCardCompany'][$v['codecd']]=$v['value'];
					}

					if(isset($payment_gateway['payment'])) foreach($payment_gateway['payment'] as $k => $v){
						$payment[$v] = true;
					}
					$this->template->assign('payment',$payment);
				}

				$msubdata = @explode("|",$data['adddata']);
				if(!$adddata[$msubdatakeyseq])$add=0;
				foreach ($msubdata as $msubdataar){
					$msubdatakey = @explode("^^",$msubdataar);
					$msubdatakeyseq = str_replace("bulkorderform_seq=","",$msubdatakey[1]);
					foreach ($msubdatakey as $msubdataval){if(!$adddata[$msubdatakeyseq])$add=0;
						$msubdatavalreal = @explode("=",$msubdataval);
						if($msubdatavalreal[1]) $adddata[$msubdatakeyseq][$add][$msubdatavalreal[0]] = $msubdatavalreal[1];
					}
					if($adddata[$msubdatakeyseq])$add++;
				}

				//대량구매 추가양식 정보
				$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
				$query = $this->db->query($qry);
				$user_arr = $query -> result_array();
				foreach ($user_arr as $user){
					$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$adddata[$user['bulkorderform_seq']]);
					$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
					$bulkorder_sub[] = $user;
				}
				$this->template->assign('bulkorder_sub', $bulkorder_sub);

			}elseif( BOARDID == 'goods_review' ) {
				$msubdata = @explode("|",$data['adddata']);
				if(!$adddata[$msubdatakeyseq])$add=0;
				foreach ($msubdata as $msubdataar){
					$msubdatakey = @explode("^^",$msubdataar);
					$msubdatakeyseq = str_replace("bulkorderform_seq=","",$msubdatakey[1]);
					foreach ($msubdatakey as $msubdataval){if(!$adddata[$msubdatakeyseq])$add=0;
						$msubdatavalreal = @explode("=",$msubdataval);
						if($msubdatavalreal[1]) $adddata[$msubdatakeyseq][$add][$msubdatavalreal[0]] = $msubdatavalreal[1];
					}
					if($adddata[$msubdatakeyseq])$add++;
				}

				$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
				$query = $this->db->query($qry);
				$user_arr = $query -> result_array();
				unset($goodsreview_sub);
				foreach ($user_arr as $user){
					$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$adddata[$user['bulkorderform_seq']]);
					$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
					$goodsreview_sub[] = $user;
				}
				$this->template->assign('goodsreview_sub', $goodsreview_sub);
			}

			$data['subject']			= htmlspecialchars($data['subject']);
			$data['hit']					= number_format($data['hit']);
			$data['datacategory']	= ($data['category'])?$data['category']:$_GET['category'];

			//파일리스트 filelist
			if(!empty($_GET['reply']) != 'Y') {
				@preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$data['contents'],$list_image);
				foreach($list_image[1] as $allfilenamear){
					$filenamear = @explode(" ",$allfilenamear);
					$filenamear = $filenamear[0];
					$filelistar = @explode("/",$filenamear);
					$thumbimg		= @end($filelistar);
					$realfile		= $thumbimg;//(strstr($thumbimg,'temp_') && is_file($this->Boardmodel->upload_path.str_replace('temp_','',$thumbimg)))?str_replace('temp_','',$thumbimg):
					$writefilesize = get_file_info($this->Boardmodel->upload_path.$realfile, 'size');
					$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$realfile);
					$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
					$orignalfile	= $filenamear;
					$sizefile		= $writefilesize['size'];
					$typefile		= @end(explode('.', $realfile));//확장자추출
					if($writefilesize){
						$data['filelist'][] = array('orignfile'=>$realfile,'realfilename'=>$realfile,'realfile'=>$this->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$this->Boardmodel->upload_path.$realfile,'realfileurl'=>$this->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$this->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$this->Boardmodel->upload_src.$thumbimg);
					}else{
						$writefilesize = get_file_info(ROOTPATH.str_replace("../","/",$orignalfile), 'size');
						$filetypetmp = @getimagesize(ROOTPATH.str_replace("../","/",$orignalfile));
						$is_image	= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
						$orignalfile	= str_replace("../","/",$filenamear);
						$sizefile		= $writefilesize['size'];
						$typefile		= @end(explode('.', $realfile));//확장자추출
						if( $writefilesize ){//다른폴더인경우(이전등의사유로)
							$data['filelist'][] = array('orignfile'=>$realfile,'realfilename'=>$realfile,'realfile'=>$orignalfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>ROOTPATH.$orignalfile,'realfileurl'=>$orignalfile,'realthumbfiledir'=>ROOTPATH.$thumbimg,'realthumbfileurl'=>$thumbimg);
						}
					}
				}
				if($data['upload']){
					$uploadar = @explode("|",$data['upload']);
					foreach($uploadar as $filenamear){
						$filelistar = @explode("^^",$filenamear);
						@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;
						if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
							$realfilename = iconv('utf-8','cp949',$realfile);

							if(empty($typefile)) {
							$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$realfilename);
							$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
								if(!$filetypetmp['mime']){
									$typefile =end(explode('.', $realfile));//확장자추출
								}else{
									$typefile =$filetypetmp['mime'];
								}
							}else{  
								$is_image			= ( preg_match("/image/",$typefile) )?1:0;
							}

							if(!$sizefile) {
								$realfile_temp			= @explode("/",$realfile);
								$orignalfile		= @end($realfile_temp);
							}

							if(!$sizefile) {
								$writefilesize = get_file_info($this->Boardmodel->upload_path.$realfilename, 'size');
								$sizefile		= $writefilesize['size'];
							}
						}

						if( is_file($this->Boardmodel->upload_path.$realfile) || ($realfilename && is_file($this->Boardmodel->upload_path.$realfilename) )) {  
							if(empty($typefile)) {
							$filetypetmp = @getimagesize($this->Boardmodel->upload_path.$realfile);
							$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
								if(!$filetypetmp['mime']){
									$typefile =end(explode('.', $realfile));//확장자추출
								}else{
									$typefile =$filetypetmp['mime'];
								}
							}else{  
								$is_image			= ( preg_match("/image/",$typefile) )?1:0;
							}

							if(!$sizefile) {
								$realfile_temp			= @explode("/",$realfile);
								$orignalfile		= @end($realfile_temp);
							}

							if(!$sizefile) {
								$writefilesize = get_file_info($this->Boardmodel->upload_path.$realfile, 'size');
								$sizefile		= $writefilesize['size'];
							}

							if(is_array($data['filelist'])) {
								$usefile = false;
								foreach($data['filelist'] as $realfilenew) {
									if($realfilenew['orignfile'] == $realfile) {$usefile=true;break;}
								}
								if(!$usefile) {
									$data['filelist'][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$this->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$this->Boardmodel->upload_path.$realfile,'realfileurl'=>$this->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$this->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$this->Boardmodel->upload_src.$thumbimg);
								}
							} else {
								$data['filelist'][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$this->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$this->Boardmodel->upload_path.$realfile,'realfileurl'=>$this->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$this->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$this->Boardmodel->upload_src.$thumbimg);
							}
						}
					}
				}
			}

			$data['noticeckeck']		= ($data['notice'] == "1") ? 'checked':'';//공지
			$data['hiddenlay']	= ( $this->manager['secret_use'] == "Y" || $this->manager['secret_use'] == "A" ) ? '':' hide';//비밀글사용여부
			if($this->manager['secret_use'] == "A") {//무조건 비밀글
				$data['hiddenckeck']	= ' disabled="disabled"  checked ';//비밀글/노출글
			}else{
				$data['hiddenckeck']	= ($data['hidden'] == "1" && ($this->manager['secret_use'] == "Y" || BOARDID == "faq") ) ? 'checked':'';//비밀글/노출글
			}

			$this->boardurl->view = $this->boardurl->view.$_GET['seq'];//

			if(BOARDID == 'goods_review' ) {
				$data['scorelay']		= getGoodsScore($data['score'], $this->manager, 'write');
			}

			if( isset($this->userInfo['member_seq']) ) {
				$data['name']	= $this->userInfo['user_name'];
				$data['user_name']	= $this->userInfo['user_name'];
				$this->load->model('membermodel');
				$this->minfo = $this->membermodel->get_member_data($this->userInfo['member_seq']);
				$data['pw']		= $this->minfo['password'];
				$data['email']	= (!empty($data['email'])) ? $data['email'] : $this->minfo['email'];
				$data['tel1']		= (!empty($data['tel1'])) ? $data['tel1'] : $this->minfo['phone'];
				$data['tel2']		= (!empty($data['tel2'])) ? $data['tel2'] : $this->minfo['cellphone'];
			}

			//동영상
			if(  $this->session->userdata('setMode')=='mobile' && $data['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
				$data['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_i'], $this->manager);
				$data['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_i'], $this->manager);
				$data['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_i'], $this->manager);
			}elseif( uccdomain('thumbnail',$data['file_key_w'], $this->manager) && $data['file_key_w'] ) {
				$data['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_w'], $this->manager);
				$data['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_w'], $this->manager);
				$data['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_w'], $this->manager);
			}


		} else {//등록시

			//모바일-기본내용추출
			if ( ( $this->siteType == 'mobile' ) ) {
				if($this->manager['content_default_mobile']){
					$this->template->assign('contents',strip_tags(nl2br($this->manager['content_default_mobile'])));
				}
			}else{
				if($this->manager['content_default']) {//기본내용
				$this->template->assign('contents',$this->manager['content_default']);
				}
			}

			if( BOARDID == 'bulkorder' ) {
				$cfg['order'] = config_load('order');
				$this->template->assign('cfg',$cfg);
				if( $this->config_system['pgCompany'] ){
					$payment_gateway = config_load($this->config_system['pgCompany']);
					$payment_gateway['arrKcpCardCompany'] = code_load('kcpCardCompanyCode');

					foreach($payment_gateway['arrKcpCardCompany'] as $k => $v){
						$payment_gateway['arrCardCompany'][$v['codecd']]=$v['value'];
					}

					if(isset($payment_gateway['payment'])) foreach($payment_gateway['payment'] as $k => $v){
						$payment[$v] = true;
					}
					$this->template->assign('payment',$payment);
				}
				//대량구매 추가양식 정보
				$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
				$query = $this->db->query($qry);
				$user_arr = $query -> result_array();
				foreach ($user_arr as $user){
					$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$msubdata);
					$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
					$bulkorder_sub[] = $user;
				}
				$this->template->assign('bulkorder_sub', $bulkorder_sub);

			}elseif( BOARDID == 'goods_review' ){

				$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
				$query = $this->db->query($qry);
				$user_arr = $query -> result_array();
				unset($goodsreview_sub);
				foreach ($user_arr as $user){
					$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$msubdata);
					$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
					$goodsreview_sub[] = $user;
				}
				$this->template->assign('goodsreview_sub', $goodsreview_sub);
			}

			get_auth($this->manager, '', 'write' , $isperm);//접근권한체크
			if ( $isperm['isperm_write'] === false ) {
				//if(!defined('__ISADMIN__')) {
					if(!empty($_GET['popup']) ){ pageClose('접근권한이 없습니다!');}else{
						if(!$this->boardurl->perm){
							if($_SERVER['HTTP_REFERER']) {
								pageBack('접근권한이 없습니다!');
								exit;
							}else{
								$url = "/main/index";
								pageRedirect($url,'접근권한이 없습니다!');
								exit;
							}
						}else{
							$board = $this->skin.'/board/_permcheck.html';
							$this->template->assign('boardurl',$this->boardurl);
							$this->template->assign($data);
							$this->print_layout($board);
							exit;
						}
					}
				//}
			}
			$mode = "board_write";

			$data['filelist'] = '';//파일리스트 filelist
			$data['noticeckeck'] = '';
			$data['hiddenlay']	= ( $this->manager['secret_use'] == "Y" || $this->manager['secret_use'] == "A" ) ? '':' hide';//비밀글사용여부
			if($this->manager['secret_use'] == "A") {//무조건 비밀글
				$data['hiddenckeck']	= ' disabled="disabled"  checked ';//비밀글/노출글
			}else{
				$data['hiddenckeck'] = '';//비밀글/노출글
			}
			$data['datacategory']	= ($_GET['category'])?$_GET['category']:'';

			if( isset($this->userInfo['member_seq']) ) {

				$this->load->model('membermodel');
				$this->minfo = $this->membermodel->get_member_data($this->userInfo['member_seq']);

				$data['name']	= $this->userInfo['user_name'];
				$data['user_name']	= $this->userInfo['user_name'];
				$data['pw']		= $this->minfo['password'];
				$data['email']	= $this->minfo['email'];
				$data['tel1']		= $this->minfo['phone'];
				$data['tel2']		= $this->minfo['cellphone'];
			}

			if(BOARDID == 'goods_review' ) {
				$data['scorelay']		= getGoodsScore('', $this->manager, 'write');
			}
			if(!empty($_GET['goods_seq'])){
				if( BOARDID == 'bulkorder' ) {
					$displayGoods = getBulkorderGoodsinfo('', $_GET['goods_seq'], 'write');
				}else{
					$displayGoods = getGoodsinfo('', $_GET['goods_seq'], 'write');
				}
			}

			//첨부파일 등록
			$this->session->unset_userdata('tmpcode');
			$tmpcode = BOARDID.'^^'.substr(microtime(), 2, 6).'^^'.$this->userInfo['member_seq'];
			$this->session->set_userdata('tmpcode',$tmpcode);
		}


		if($data['videotmpcode']){
			$this->session->set_userdata('boardvideotmpcode',$data['videotmpcode']);
		}else{
			if($this->manager['video_use'] == 'Y' ){//사용중인경우
				$boardvideotmpcode = substr(microtime(), 2, 8);
				$this->session->set_userdata('boardvideotmpcode',$boardvideotmpcode);
			}
		}

		//동영상관리
		/**
		if($data['file_key_w'] ) {
			$this->load->model('videofiles');
			$videosc['tmpcode']		= $this->session->userdata('boardvideotmpcode');
			$videosc['file_key_w']	= $data['file_key_w'];
			$videosc['upkind']= 'board';
			$videosc['type']= BOARDID;
			$videoboard = $this->videofiles->get_data($videosc);
			if($videoboard) $this->template->assign('videoboard',$videoboard);
		}
		**/

		//분류리스트 categorylist
		if($this->manager['category']){
			$categorylist = @explode(",",$this->manager['category']);
			$data['categorylist'] = $categorylist;
		}


			if ( $_POST && BOARDID == 'goods_review' ){
				$data['subject'] = $_POST['subject'];
				$data['contents'] = $_POST['contents'];
				$data['datacategory'] = $_POST['category'];
				foreach($_POST['reviewcategory'] as $reviewcategory){
					$reviewcategoryar[] = $reviewcategory[0];
				}
				if($reviewcategoryar) $data['reviewcategory'] = @implode(",",$reviewcategoryar);
				$data['hiddenckeck']	= ($_POST['hidden'] == "1") ? 'checked':'';
				$_GET['goods_seq']	= ($_POST['displayGoods']) ? $_POST['displayGoods'][0]:'';

				if(!empty($_GET['goods_seq'])){
					$displayGoods = getGoodsinfo('', $_GET['goods_seq'], 'write');
				}
			}


		//상품후기의 평가점수
		if($this->manager['reviewcategory']){
			$reviewcategoryar = @explode(",",$this->manager['reviewcategory']);
			$reviewcategorydataar = @explode(",",$data['reviewcategory']);
			foreach($reviewcategoryar as $k=>$reviewcategory){
				$reviewcategorylistar['idx'] = $k;
				$reviewcategorylistar['title'] = $reviewcategory;
				$reviewcategorylistar['score'] = $reviewcategorydataar[$k];
				$reviewcategorylist[] = $reviewcategorylistar;
			}
			$data['reviewcategorylist'] = $reviewcategorylist;
		}
		if( defined('__ISUSER__') != true ) {
			$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
			$member = config_load('member');
			$data['privacy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['privacy']));
			//개인정보 수집-이용
			$data['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));
		}

		// xxs
		if ( !empty($_GET['seq']) ) {//수정시
			$data['contents'] = getcontents($data);
		}
		
		$backtype = $this->session->userdata('backtype');
		$this->template->assign('backtype',$backtype);
		$this->template->assign($data);
		$this->template->assign('mode',$mode);
		$this->boardurl->lists = $this->boardurl->lists;

		$this->template->assign('displayGoods',$displayGoods);//상품정보

		$this->template->assign('boardurl',$this->boardurl);

		$board = $this->skin.'/board/'.BORADSKIN.'/'.$this->thisfile.'.html';//write
		$this->template->define(array("skin"=>$board));

		//스팸방지
		if($this->manager['autowrite_use'] == 'Y' && $page!='mypage' && !( $this->siteType == 'mobile' && $this->mobileMode ) ) {// || $this->_is_mobile_agent
			$cap = boardcaptcha();

			$securimage = $this->skin.'/board/_securimage_show.html';//view
			$this->template->assign('captcha_image',$cap['image']);
			$this->template->define(array("securimage"=>$securimage));
		}

		//모바일 첨부파일 input file 추가
		$mobile_file = $this->skin.'/board/_mobile_file.html';
		$this->template->define(array("mobile_file"=>$mobile_file));

		//동영상연결(기본 파일찾기)
		$this->template->assign("uccdomain",uccdomain());

		$this->print_layout(FILE_PATH);
	}

	/**
	 * 사용자 > 게시물 보기
	 * @param id : 게시판아이디
	**/
	public function view($page=null)
	{
		if( defined('BOARDID') ) $this->template->assign(array('ISBOARDID'=>BOARDID));
		$this->pagetype = $page;
		if ( !defined('BOARDID') ) pageBack('존재하지 않는 게시판입니다.');
		if (!isset($_GET['seq'])) pageBack('잘못된 접근입니다.');

		if (!isset($this->manager['id'])) pageBack('존재하지 않는 게시판입니다.');

		$_GET['seq']		= (int) $_GET['seq'];
		$sc['whereis']		= ' and seq= "'.$_GET['seq'].'" ';
		$sc['select']		= ' * ';
		$data = $this->Boardmodel->get_data($sc);//게시글
		getmanagerauth('board_view',$data);//* 부관리자 권한 체크
		if( BOARDID == 'goods_review' || BOARDID == 'goods_qna'  || BOARDID == 'bulkorder' ) {
			$data['boardid'] = ($data['boardid'])?$data['boardid']:BOARDID;
		}
		if (!isset($data['seq'])) pageBack('존재하지 않는 게시물입니다.');

		if(  $this->siteType == 'mobile' ) {
			if($data['re_contents']){
				if( $data['re_date'] ) {
					if( getDateFormat($data['re_date'],"Y-m-d") == date("Y-m-d") ){
						$data['reply_title']		= '<span  > '.getDateFormat($data['re_date'],"H:i").'</span>';
					}else{
						$data['reply_title']		= '<span > '.getDateFormat($data['re_date'],"Y-m-d").'</span>';
					}
				}else{
					$data['reply_title']	= ($data['re_contents'])?'<span class="blue" >답변완료</span>':'<span class="gray" >답변대기</span>';//상태
				}
			}else{
				$data['reply_title']		= '';
			}
		}else{
			$data['reply_title']	= ($data['re_contents'])?'<span class="blue" >답변완료</span>':'<span class="gray" >답변대기</span>';//상태
		}

		if( BOARDID == 'bulkorder' ) {
			$msubdata = @explode("|",$data['adddata']);
			if(!$adddata[$msubdatakeyseq])$add=0;
			foreach ($msubdata as $msubdataar){
				$msubdatakey = @explode("^^",$msubdataar);
				$msubdatakeyseq = str_replace("bulkorderform_seq=","",$msubdatakey[1]);
				foreach ($msubdatakey as $msubdataval){if(!$adddata[$msubdatakeyseq])$add=0;
					$msubdatavalreal = @explode("=",$msubdataval);
					if($msubdatavalreal[1]) $adddata[$msubdatakeyseq][$add][$msubdatavalreal[0]] = $msubdatavalreal[1];
				}
				if($adddata[$msubdatakeyseq])$add++;
			}

			//대량구매 추가양식 정보
			$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
			$query = $this->db->query($qry);
			$user_arr = $query -> result_array();
			foreach ($user_arr as $user){
				if($user['bulkorderform_seq'] == '1' && $user['used'] == 'Y'){//
					$data['person_name_title']	 =  $user['label_title'];
				}elseif($user['bulkorderform_seq'] == '2'  && $user['used'] == 'Y'){//
					$data['person_email_title']	 =  $user['label_title'];
				}elseif($user['bulkorderform_seq'] == '3'  && $user['used'] == 'Y'){//
					$data['person_tel1_title']	 =  $user['label_title'];
				}elseif($user['bulkorderform_seq'] == '4'  && $user['used'] == 'Y'){//
					$data['person_tel2_title']	 =  $user['label_title'];
				}elseif($user['bulkorderform_seq'] == '5'  && $user['used'] == 'Y'){//
					$data['company_title']	 =  $user['label_title'];
				}elseif($user['bulkorderform_seq'] == '6'  && $user['used'] == 'Y'){//
					$data['shipping_date_title']	 =  $user['label_title'];
				}else{
					$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$adddata[$user['bulkorderform_seq']],'view');
					$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
					$bulkorder_sub[] = $user;
				}
			}
			$this->template->assign('bulkorder_sub', $bulkorder_sub);

		}elseif( BOARDID == 'goods_review' ){
			if($data['adddata']) {
				$msubdata = @explode("|",$data['adddata']);
				if(!$adddata[$msubdatakeyseq])$add=0;
				foreach ($msubdata as $msubdataar){
					$msubdatakey = @explode("^^",$msubdataar);
					$msubdatakeyseq = str_replace("bulkorderform_seq=","",$msubdatakey[1]);
					foreach ($msubdatakey as $msubdataval){if(!$adddata[$msubdatakeyseq])$add=0;
						$msubdatavalreal = @explode("=",$msubdataval);
						if($msubdatavalreal[1]) $adddata[$msubdatakeyseq][$add][$msubdatavalreal[0]] = $msubdatavalreal[1];
					}
					if($adddata[$msubdatakeyseq])$add++;
				}
				$qry = "select * from fm_boardform  where boardid='".BOARDID."'  order by sort_seq, boardid asc";
				$query = $this->db->query($qry);
				$user_arr = $query -> result_array();
				unset($goodsreview_sub);
				foreach ($user_arr as $user){
					if( $user['used'] == 'Y' ) {
						$user['label_view'] = $this->Boardmanager->get_labelitem_type($user,$adddata[$user['bulkorderform_seq']],'view');
						$user['label_ctype'] = $this->Boardmanager->typeNames[$user['label_type']];
						$goodsreview_sub[] = $user;
					}
				}
				$this->template->assign('goodsreview_sub', $goodsreview_sub);
			}
		}

		//비밀글 > 비회원 또는 회원은 본인이 아닌 경우
		if($data['hidden'] == 1 && $data['notice'] == 0) {//공지글은 무조건보기가능
			$parentsql['whereis']	= ' and seq= "'.$data['parent'].'" ';
			$parentsql['select']		= '  seq, gid, comment, upload, depth, pw, mseq, mid, parent';
			$parentdata = $this->Boardmodel->get_data($parentsql);//게시물정보

			if( $data['mseq'] > 0  || ($data['parent'] && $parentdata['mseq'] > 0 ) ) {//회원이 쓴글인경우
				if( ( ($data['mseq'] != $this->userInfo['member_seq'] && $parentdata['mseq'] != $this->userInfo['member_seq']) && defined('__ISUSER__'))  || ( !defined('__ISUSER__') ) ) {//작성자가 아니거나 비회원인 경우
					if(!defined('__ISADMIN__')) {
						$board = $this->skin.'/board/_pwcheck.html';
						$this->template->assign('boardurl',$this->boardurl);
						$this->template->assign('returnurl',urlencode($this->boardurl->view.$data['seq']));//$this->boardurl->pw.
						$this->template->assign($data);
						$this->print_layout($board);
						exit;
					}
				}
			}
			else{
				// 비번입력후 브라우저를 닫기전까지는 접근가능함
				$ss_pwhidden_name = 'board_pwhidden_'.BOARDID;
				$boardpwhiddenss = $this->session->userdata($ss_pwhidden_name);
				if ( ( !strstr($boardpwhiddenss,'['.$_GET['seq'].']') && isset($boardpwhiddenss)) || empty($boardpwhiddenss)) {
					if(!defined('__ISADMIN__')) {
						$board = $this->skin.'/board/_pwcheck.html';
						$this->template->assign('boardurl',$this->boardurl);
						$this->template->assign('returnurl',urlencode($this->boardurl->view.$data['seq']));//$this->boardurl->pw.
						$this->template->assign($data);
						$this->print_layout($board);
						exit;
					}
				}
			}
		}

		get_auth($this->manager, $data, 'read', $isperm);//접근권한체크
		if(!defined('__ISADMIN__')) {
			if($page == 'goods'){//상품상세경고창
				if ( $isperm['isperm_read'] === false ) pageBack('접근권한이 없습니다.');
			}elseif(BOARDID == 'mbqna'){
				//마이페이지 >> 1:1문의인경우 본인만보기가 가능
				if( !($isperm['isperm_moddel'] === true)  && $data['notice'] == 0 ) {
					pageBack('읽기권한이 없습니다.');
					exit;
				}
			}else{
				if ( $isperm['isperm_read'] === false ) {
					$board = $this->skin.'/board/_permcheck.html';
					$this->template->assign('boardurl',$this->boardurl);
					$this->template->assign($data);
					$this->print_layout($board);
					exit;
				}
			}
		}
		$this->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';

		get_auth($this->manager, $data, 'write', $isperm);//접근권한체크
		$this->manager['isperm_write']	= ($isperm['isperm_write'] === true)?'':'_no';//등록권한
		$this->manager['isperm_moddel'] = ( $isperm['isperm_moddel'] === true)?'':'_no';//수정/삭제권한

		if( $this->manager['isperm_moddel'] == '_no' )  {

			if( ($data['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || ( !empty($data['mseq']) && !defined('__ISUSER__') ) ) {
				$this->manager['isperm_moddel'] = '_mbno';//버튼숨김(회원 > 본인만 가능함
			}else{

				$ss_pwwrite_name = 'board_pwwrite_'.BOARDID;
				$boardpwwritess = $this->session->userdata($ss_pwwrite_name);
				if ( strstr($boardpwwritess,'['.$data['seq'].']') && !empty($boardpwwritess)) {
					$this->manager['isperm_moddel'] = '';//비회원 > 접근권한있음
				}
			}
		}

		if( BOARDID == 'goods_review' ||  BOARDID == 'notice' ){
			get_auth($this->manager, $cmtdatarow, 'write_cmt', $isperm);//접근권한체크
			$this->manager['isperm_write_cmt'] = ($isperm['isperm_write_cmt'] === true)?'':'_no';
		}elseif($this->manager['type'] == 'A') {//추가게시판인경우
			get_auth($this->manager, $cmtdatarow, 'reply', $isperm);//접근권한체크
			$this->manager['isperm_reply'] = ($isperm['isperm_reply'] === true)?'':'_no';
			get_auth($this->manager, $cmtdatarow, 'cmt', $isperm);//접근권한체크
			$this->manager['isperm_cmt'] = ($isperm['isperm_cmt'] === true)?'':'_no';
			$this->manager['isperm_write_cmt'] = $this->manager['isperm_cmt'];
		}else{
			$this->manager['isperm_write_cmt'] = $this->manager['isperm_write'];
		}

		$this->template->assign('commentlay',$this->manager['auth_cmt_use']);
		$this->template->assign('replylay',$this->manager['auth_reply_use']);

		$cfg_goods = config_load('goods');
		if($this->manager['video_use'] == 'Y' ) {
			$video_size = explode("X" , $this->manager['video_size']);
			$this->manager['video_size0'] = $video_size[0];
			$this->manager['video_size1'] = $video_size[1];

			$video_size_mobile = explode("X" , $this->manager['video_size_mobile']);
			$this->manager['video_size_mobile0'] = $video_size_mobile[0];
			$this->manager['video_size_mobile1'] = $video_size_mobile[1];
		}else{
			unset($this->manager['file_key_w'],$this->manager['file_key_i'],$this->manager['video_size']);
			$this->manager['video_use']	= 'N';
		}

		//동영상
		if(  $this->session->userdata('setMode')=='mobile' && $data['file_key_i'] ){//모바일이면서 file_key_i 값이 있는 경우
			$data['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_i'], $this->manager);
			$data['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_i'], $this->manager);
			$data['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_i'], $this->manager);
		}elseif( uccdomain('thumbnail',$data['file_key_w'], $this->manager) && $data['file_key_w'] ) {
			$data['uccdomain_thumbnail']		= uccdomain('thumbnail',$data['file_key_w'], $this->manager);
			$data['uccdomain_fileswf']			= uccdomain('fileswf',$data['file_key_w'], $this->manager);
			$data['uccdomain_fileurl']			= uccdomain('fileurl',$data['file_key_w'], $this->manager);
		}

		$this->template->assign('managerview',$this->manager);

		// 한번 읽은글은 브라우저를 닫기전까지는 조회수 증가
		$ss_hit_name = 'board_hit_'.BOARDID;
		$boardhitss = $this->session->userdata($ss_hit_name);
		if ( ( !strstr($boardhitss,'['.$_GET['seq'].']') && isset($boardhitss)) || empty($boardhitss)) {
			$boardhitssadd = (isset($boardhitss)) ? $boardhitss.'['.$_GET['seq'].']':'['.$_GET['seq'].']';
			$this->Boardmodel->hit_update($_GET['seq']);
			$this->session->set_userdata($ss_hit_name, $boardhitssadd );
		}

		//파일리스트 filelistif(!empty($_GET['reply']) != 'Y')
		getBoardViewUploadAllfiles($data);

		if($this->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$data['r_date']),0,8))) >= date("Ymd") ) {//new
			$data['iconnew']	= ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ';
		}

		if($this->manager['icon_new_day'] > 0 &&  date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$data['cmt_date']),0,8))) >= date("Ymd") ) {//new
			$data['cmt_iconnew']	= ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ';//댓글최근등록날짜
		}

		if($this->manager['icon_hot_visit'] > 0 && $this->manager['icon_hot_visit'] <= $data['hit'] ) {//조회수
			$data['iconhot']		= ' <img src="'.$this->icon_hot_img.'" title="hot" > ';
		}

		if($data['upload'] ) {//첨부파일
			$data['iconfile']		= ' <img src="'.$this->icon_file_img.'" title="첨부파일" align="absmiddle" > ';
		}

		if($data['hidden'] == 1 ) {//비밀글
			$data['iconhidden'] = ' <img src="'.$this->icon_hidden_img.'" title="비밀글" > ';
		}
		if($data['upload']  && boardisimage($data['upload'], $data['contents']) ) {//첨부파일 > image
			$data['iconimage']		= ' <img src="'.$this->icon_image_img.'" title="첨부파일" align="absmiddle" > ';
		}
		if(isMobilecheck($data['agent'])) {//agent > mobile ckeck
			$data['iconmobile']		= ' <img src="'.$this->icon_mobile_img.'" title="모바일" align="absmiddle" > ';
		}

		$data['comment']= number_format($data['comment']);
		if($data['display'] == 1 ) {
			$data['subject'] = ' 삭제되었습니다. ['.$data['r_date'].']';
			$data['contents'] = '<span class="gray" >삭제된 글입니다.</span>';
		}

		$data['contents'] = getcontents($data);//xss/csrf
		$data['buyertitle'] = ($data['order_seq'])?'구매':'미구매';

		$data['snst'] = $this->snst_img;
		$data['snsf'] = $this->snsf_img;
		$data['snsm'] = $this->snsm_img;
		$data['snsy'] = $this->snsy_img;

		$data['noticeckeck']		= ($data['notice'] == "1") ? 'checked':'';//공지
		$data['hiddenckeck']	= ($data['hidden'] == "1") ? 'checked':'';//비밀글
		$data['subject']			= ($data['subject']);

		if(BOARDID == 'goods_review' ) {
			$data['iconaward']		= ($data['best'] == 'checked')?' <img src="'.$this->icon_award_img.'" title="best" /> ':'';
			if($prenextdata['mseq'] > 0 && $prenextdata['mseq'] != trim($this->userInfo['member_seq']) && defined('__ISUSER__') ) {//본인인 경우에만
				$data['emoneyviewlay']	=  getBoardEmoneybtn($data,$this->manager, 'view');//상품후기의 적립금여부
			}

			if( $this->manager['goods_review_type'] == 'INT' && $data['reviewcategory'] ) {
				$data['scorelay'] = getGoodsScore($data['score_avg'], $this->manager);
				if(sizeof(explode(",",$data['reviewcategory']))>1) $data['score_avg_lay'] = 'score_avg';
			}else{
				$data['scorelay'] = getGoodsScore($data['score'], $this->manager);
			}
		}

		$replysc['whereis'] = ' and gid > '.$data['gid'].' and gid < '.(intval($data['gid'])+1).' and parent = '.($data['seq']).' ';//답변여부
		$data['replyor'] = $this->Boardmodel->get_data_numrow($replysc); 

		getminfo($this->manager, $data, $mdata, $mbname,'','board_view');//회원정보
		$data['name'] = $mbname;

		getminfo($this->manager, $data, $mdata, $adminname,true);//게시판관리자정보
		$data['adminname'] = $adminname;

		$data['datacategory'] = $data['category'];
		if( isset($this->userInfo['member_seq']) )$data['user_name']	= $this->userInfo['user_name'];

		if(!empty($data['goods_seq'])){
			if( BOARDID == 'bulkorder') {
				if(strstr($this->manager['bulk_show'],'[goods]')){//상품사용시
					$goodsview	= getBulkorderGoodsinfo($data, $data['goods_seq'], 'view');
				}
			}else{
				$goodsview	= getGoodsinfo($data, $data['goods_seq'], 'view');
			}
			if($this->__APP_USE__ == 'f') {//페이스북
			}
			$this->load->model('goodsmodel');
			$images = $this->goodsmodel->get_goods_image($data['goods_seq']);
			if($images){
				foreach($images as $image){
					if($image['view']['image']) {
						$filetypetmp = @getimagesize(ROOTPATH.$image['view']['image']);
						if($filetypetmp[0] > 200){
							$this->template->assign('APP_IMG',	$image['list1']['image']);
							$viewappimg = true;
							break;
						}else{
							$this->template->assign('APP_IMG',	$image['view']['image']);
							$viewappimg = true;
							break;
						}
					}elseif($image['list1']['image']) {
						$this->template->assign('APP_IMG',	$image['list1']['image']);
						$viewappimg = true;
						break;
					}
				}
			}
		}else{
			if( BOARDID == 'bulkorder') {
				if(strstr($this->manager['bulk_show'],'[goods]')){//상품사용시
					$goodsview	= '상품정보가 없습니다.';
				}
			}else{
				$goodsview	= '상품정보가 없습니다.';
			}
		}  

		if(  !$viewappimg ) { //페이스북 $this->__APP_USE__ == 'f' && 
			if($data['filelistimages']) {
				foreach($data['filelistimages'] as $k=>$image) {  
					if($image['realthumbfileurl']){
						$this->template->assign('APP_IMG',	$image['realthumbfileurl']);
					}else{
						$this->template->assign('APP_IMG',	$image['realfile']);
					}
					break;
				}//endforeach
			}else{	
				@preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$data['contents'],$list_image);
				foreach($list_image[1] as $allfilenamear){//본문내용에서 이미지추출
					$filenamear = @explode(" ",$allfilenamear);
					$filenamear = $filenamear[0];
					$orignalfile	= $filenamear; 
					$filetypetmp = @getimagesize(ROOTPATH.str_replace("../","/",$orignalfile));
					$is_image	= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
					$orignalfile	= str_replace("../","/",$filenamear); 
					if( $is_image ) { 
						$this->template->assign('APP_IMG',$orignalfile);
						break;
					}
				}
			}
		}

		if($page != 'goods'){//상품상세에서는 제외
			$this->template->assign('goodsview',$goodsview);//상품정보
		}

		//스팸방지
		if($this->manager['autowrite_use'] == 'Y' && !defined('__ISUSER__')  && !( $this->siteType == 'mobile' && $this->mobileMode ) ) {// || $this->_is_mobile_agent
			$cap = boardcaptcha();

			$securimage = $this->skin.'/board/_securimage_show.html';//view
			$this->template->assign('captcha_image',$cap['image']);
			$this->template->define("securimage",$securimage);
 			$this->template->define("securimage_reply",$securimage);
		}


		if( ( $this->siteType == 'mobile'  || $this->_is_mobile_agent) && $data['file_key_i'] ){ //모바일화면인경우 처리
			$data['file_key_w'] = $data['file_key_i'];
		}

		//상품후기의 평가점수
		if($this->manager['reviewcategory']){
			$reviewcategoryar = @explode(",",$this->manager['reviewcategory']);
			$reviewcategorydataar = @explode(",",$data['reviewcategory']);
			foreach($reviewcategoryar as $k=>$reviewcategory){
				$reviewcategorylistar['idx'] = $k;
				$reviewcategorylistar['title'] = $reviewcategory;
				$reviewcategorylistar['score'] = $reviewcategorydataar[$k];
				$reviewcategorylist[] = $reviewcategorylistar;
			}
			$data['reviewcategorylist'] = $reviewcategorylist;
		}else{
			$reviewcategoryar = @explode(",","평점");
			$reviewcategorydataar = @explode(",","평점");
			foreach($reviewcategoryar as $k=>$reviewcategory){
				$reviewcategorylistar['idx'] = $k;
				$reviewcategorylistar['title'] = $reviewcategory;
				$reviewcategorylistar['score'] = $reviewcategorydataar[$k];
				$reviewcategorylist[] = $reviewcategorylistar;
			}
			$data['reviewcategorylist'] = $reviewcategorylist;
		}

		if(count($data['reviewcategorylist']) < 2 ) unset($data['reviewcategorylist']);//게시글상세 평점 한개이상일때 노출

		$data['hiddenlay']	= ( $this->manager['secret_use'] == "Y" || $this->manager['secret_use'] == "A" ) ? '':' hide';//비밀글사용여부
		if($this->manager['secret_use'] == "A") {//무조건 비밀글
			$data['hiddenckeck']	= ' disabled="disabled"  checked ';//비밀글/노출글
		}else{
			$data['hiddenckeck']	= ($data['hidden'] == "1" && ($this->manager['secret_use'] == "Y" || BOARDID == "faq") ) ? 'checked':'';//비밀글/노출글
		}

		$data['cmthiddenlay']	= ( $this->Boardmanager->cmthidden == "Y") ? '':' hide';//비밀댓글사용여부


		$this->getBoardPreNext($data, 'pre', 'asc', ' > ', 'prelay',$page);//이전글
		$this->getBoardPreNext($data, 'next', 'desc', ' < ', 'nextlay',$page);//다음글

		if(isset($_GET['cmtpage'])) {
			$this->boardurl->cmtview = str_replace("&cmtpage=".$_GET['cmtpage'],"",str_replace("&cmtlist=1","&",$this->boardurl->view).$_GET['seq']);//.'&cmtlist=1&'
		}else{
			$this->boardurl->cmtview = str_replace("&cmtlist=1","&",$this->boardurl->view).$_GET['seq'];//.'&cmtlist=1&'
		}
		$this->template->assign('boardurl',$this->boardurl); 

		//게시글평가 skin
		if( $this->manager['auth_recommend_use'] == 'Y' ){
			if( $this->userInfo['member_seq'] ) {
				$scoresql['whereis']	= ' and boardid= "'.BOARDID.'" '; 
				$scoresql['whereis']	.= ' and type= "board" ';
				$scoresql['whereis']	.= ' and parent= "'.$data['seq'].'" ';//게시글 
				$scoresql['whereis']	.= ' and mseq= "'.$this->userInfo['member_seq'].'" ';
				$getscoredata = $this->Boardscorelog->get_data($scoresql); 
				if($getscoredata) $data['is_recommend'] = '_y';
				if($data['display'] == 1) $data['is_recommend'] = '_d';
			}
			$scoreskin = $this->skin.'/board/_score.html';
			$this->template->define(array("scoreskin"=>$scoreskin));
		}
		$this->template->assign($data);

		$this->getCommentlists($_GET['seq'],$data);//댓글출력

		if($page != 'mypage' && !($_GET['mode'] == 'ajax' || $_GET['mode'] == 'layer') )$this->index('view');//하단의 리스트출력

		$board = $this->skin.'/board/'.BORADSKIN.'/'.$this->thisfile.'.html';//view

		//@2014-02-27 패치 이전소스때문에 board_comment.js 호출여부체크함
		$this->load->helper('file');
		if($page){
			$headerhtmlfile	= ROOTPATH."data/skin/".FILE_PATH;
		}else{
			$headerhtmlfile	= ROOTPATH."data/skin/".$board;
		}
		$headerhtmlfilesource = read_file($headerhtmlfile);
		if( strpos($headerhtmlfilesource,'{#commentskin}') ) {
			$this->template->assign('commentskinjsuse',true);
		}

		//댓글skin
		$commentskin = $this->skin.'/board/_comment.html';//view
		$this->template->define(array("commentskin"=>$commentskin));

		//이전글/다음글skin
		$prenextskin = $this->skin.'/board/_prenext.html';//view
		$this->template->define(array("prenextskin"=>$prenextskin));
		$this->template->assign("pagemode",$_GET['mode']);
		if( $_GET['mode'] == 'ajax' ){//new mobile
			$this->template->define(array('tpl'=>str_replace("view.html","view_ajax.html",FILE_PATH)));//
			$this->template->define(array("skin"=>$board));
			$this->template->print_('tpl');
		}elseif( $_GET['mode'] == 'commentview' && strstr(FILE_PATH,'commentview.html') ){//new
				$board = $this->skin.'/board/'.BORADSKIN.'/commentview.html';//view
				$this->template->define(array("skin"=>$board));
				$this->print_layout(str_replace("commentview.html","view.html",FILE_PATH));
		}elseif( $_GET['mode'] == 'layer' && $_GET['iframe'] == '1' && $this->manager['viewtype'] == 'layer'  && ( BOARDID == 'goods_qna' || BOARDID == 'goods_review' )  && ($page== 'goods' || $page == 'mypage') ) {
			$this->template->define(array('tpl'=>str_replace("_view.html","_view_layer.html",FILE_PATH)));//
			$this->template->print_('tpl');
		}else{
			$this->template->define(array("skin"=>$board));
			$this->print_layout(FILE_PATH);
		}

	}

	/**
	 * 사용자 > 게시물 보기
	 * @param id : 게시판아이디
	**/
	public function goods_board_view($page=null)
	{
		$board = new Board;
		$board->boardurl = $this->boardurl;
		$board->view($page);
	}

	/**
	 * 사용자 > 코멘트 보기
	**/
	public function commentview($page=null)
	{
		$board = new Board;
		$board->boardurl = $this->boardurl;
		$board->view($page);
	}

	//댓글
	public function getCommentlists($parent, $boarddata) {

		$sc['orderby']			= 'seq';
		$sc['sort']					= 'desc';
		$sc['cmtpage']			= (!empty($_GET['cmtpage'])) ?		intval($_GET['cmtpage']):'0';
		$_GET['cmtpage']		= $sc['cmtpage'];//페이징처리를 위해
		if($this->manager['viewtype'] == 'layer' && ($this->pagetype== 'goods' || $this->pagetype == 'mypage') ) {
			$sc['perpage']			= 5;
		}else{
			$sc['perpage']			= 10;
		}

		$sc['parent']				= (!empty($parent))?$parent:'';

		$boarddata['isperm_view'] = false;
		if ( $boarddata['mseq'] > 0 && $boarddata['mseq'] == $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) {//원작자 읽기권한체크
			$boarddata['isperm_view'] = true;
		}

		$cmtdata = $this->Boardcomment->data_list($sc);//댓글목록
		$boarddata['boardisperm_moddel'] = $this->manager['isperm_moddel'];
		$sc['searchcount']	 = $cmtdata['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = $this->Boardcomment->get_data_total_count($sc);
		$idx = 0;
		foreach($cmtdata['result'] as $cmtdatarow){$idx++;
			getminfo($this->manager, $cmtdatarow, $mdata, $boardname);//회원정보
			$cmtdatarow['name'] = $boardname;
			$cmtdatarow['mbname'] = $this->userInfo['user_name'];

			get_auth($this->manager, $cmtdatarow, 'write', $isperm);//접근권한체크
			$isperm_write	= (defined('__ISUSER__') === true)?'':'_no';//답글은 회원전용

			$cmtdatarow['isperm_moddel'] = ( $isperm['isperm_moddel'] === true)?'':'_no';
			$cmtdatarow['isperm_hide'] = $cmtdatarow['isperm_moddel'];

			if( (($cmtdatarow['mseq'] > 0 ) && $cmtdatarow['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || ( !empty($cmtdatarow['mseq']) && !defined('__ISUSER__') ) ) {
				$cmtdatarow['isperm_moddel'] = '_mbno';

				if( ($cmtdatarow['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || $cmtdatarow['mseq'] < 0 ) {
					$cmtdatarow['isperm_hide'] = 'hide';//버튼숨김(회원 > 본인만 가능함 or 관리자인경우 숨김
				}

			}else{

				$ss_pwwrite_name = 'board_cmtpwhidden_'.BOARDID.'_'.$cmtdatarow['parent'];
				$boardpwwritess = $this->session->userdata($ss_pwwrite_name);
				if ( strstr($boardpwwritess,'['.$cmtdatarow['seq'].']') && !empty($boardpwwritess)) {
					$cmtdatarow['isperm_moddel'] = '';//비회원 > 접근권한있음
				}
			}

			if( $cmtdatarow['hidden'] == 1) {

				$ss_pwview_name = 'cmthidden_view_'.BOARDID.'_'.$cmtdatarow['parent'];
				$boardpwviews = $this->session->userdata($ss_pwview_name);
				if ( strstr($boardpwviews,'['.$cmtdatarow['seq'].']') && !empty($boardpwviews)) {
					$cmtdatarow['isperm_hidden'] = '';//비회원 > 접근권한있음
				}else{
					if( $cmtdatarow['isperm_moddel'] == '_mbno' ) {
						$cmtdatarow['isperm_moddel'] = '_hidden_mbno';
						$cmtdatarow['isperm_hidden'] = '_hidden_mbno';
					}elseif( $cmtdatarow['isperm_moddel'] == '_no' ) {
						$cmtdatarow['isperm_moddel'] = '_hidden_no';
						$cmtdatarow['isperm_hidden'] = '_hidden_no';
					}elseif( $cmtdatarow['isperm_moddel'] == '_root' ) {
						$cmtdatarow['isperm_moddel'] = '_hidden_root';
						$cmtdatarow['isperm_hidden'] = '_hidden_root';
					}
				}
			}

			$cmtdatarow['isperm_hide'] = ( $cmtdatarow['mseq'] < 0 || $cmtdatarow['display'] == 1 )?'hide':'';//관리가등록글은 수정삭제불가
			$cmtdatarow['isperm_hidden'] = ( $boarddata['isperm_view'] === true )?'':$cmtdatarow['isperm_hidden'];//원작자는 댓글/비밀글보기가능

			//비밀글
			$cmtdatarow['iconhidden'] = ( $cmtdatarow['hidden'] == 1)?' <img src="'.$this->icon_hidden_img.'" title="비밀글" > ':'';
			
			if( $this->manager['auth_cmt_recommend_use'] == 'Y' ){
				if( $this->userInfo['member_seq'] ) {
					$scoresql['whereis']	= ' and boardid= "'.BOARDID.'" '; 
					$scoresql['whereis']	.= ' and type= "comment" ';
					$scoresql['whereis']	.= ' and parent= "'.$cmtdatarow['parent'].'" ';//게시글 
					$scoresql['whereis']	.= ' and cparent= "'.$cmtdatarow['seq'].'" ';//게시글 
					$scoresql['whereis']	.= ' and mseq= "'.$this->userInfo['member_seq'].'" ';
					$getscoredata = $this->Boardscorelog->get_data($scoresql); 
					if($getscoredata) $cmtdatarow['is_cmt_recommend'] = '_y';
					if($cmtdatarow['display'] == 1) $cmtdatarow['is_cmt_recommend'] = '_d';
				}
			}

			$replytitle = ($isperm_write == '_no') ? '로그인 후 이용해 주세요.':'';
			$replytitlereadonly = ($isperm_write == '_no') ? ' readonly="readonly" ':'';
			$cmtdatarow['isperm_write'] = $isperm_write;
			$cmtdatarow['replytitle'] = $replytitle;
			$cmtdatarow['replytitlereadonly'] = $replytitlereadonly;

			if($cmtdatarow['display'] == 1 ){//삭제시
				$cmtdatarow['iconnew']	= '';
				$cmtdatarow['content']		= ' <span class="hand gray  " >삭제되었습니다 ['.substr($cmtdatarow['r_date'],0,16).']</span>';

				$cmtdatarow['deletebtn'] = '<span class="btn small valign-middle"><input type="button" name="boad_cmt_delete_btn'.$cmtdatarow['isperm_moddel'].'"   board_cmt_seq="'.$cmtdatarow['seq'].'" value="삭제" /></span>';
			}else{
				if( !empty($cmtdatarow['isperm_hidden'])  ){//비밀글
					$cmtdatarow['content']		= ' <span class="hand gray boad_cmt_content_'.$cmtdatarow['seq'].' " board_cmt_seq="'.$cmtdatarow['seq'].'"><span class="boad_cmt_content'.$cmtdatarow['isperm_hidden'].'" board_cmt_seq="'.$cmtdatarow['seq'].'" >비밀댓글입니다.</span></span>';
				}
				$cmtdatarow['iconnew']	= ( date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$cmtdatarow['r_date']),0,8))) >= date("Ymd") ) ? ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ' :'';

				$cmtdatarow['date']			= substr($cmtdatarow['r_date'],0,16);//등록일
				$cmtdatarow['org_content']	= $cmtdatarow['content']; // 본래 내용 추가 2014-01-14 lwh
				$cmtdatarow['content']		= ' <span  board_seq="'.$cmtdatarow['seq'].'"  board_id="'.$cmtdatarow['boardid'].'"  >'.$cmtdatarow['content'].'</span>';
			}

				$cmtdatarow['idx'] = $idx;

			/**
			댓글의 답글
			**/
			$sc['orderby']			= 'seq';
			$sc['sort']				= 'desc';
			$sc['parent']			= (isset($cmtdatarow['parent']))?$cmtdatarow['parent']:'';
			$sc['cmtparent']		= (isset($cmtdatarow['seq']))?$cmtdatarow['seq']:'';
			$cmtreplyqry = $this->Boardcomment->data_list_reply($sc);//댓글목록
			$cmthtml = '';
			$replyidx = 0;
			unset($cmtreplyloop);
			foreach($cmtreplyqry['result'] as $cmtreply){$replyidx++;
				getminfo($this->manager, $cmtreply, $mdata, $boardname);//회원정보
				$cmtreply['name'] = $boardname;

				$cmtreply['isperm_moddel'] = ( ($cmtreply['mseq'] == $this->userInfo['member_seq'] && defined('__ISUSER__') === true) )?'':'_no';
				$cmtreply['isperm_hide'] = $cmtreply['isperm_moddel'];

				if( (($cmtreply['mseq'] > 0 ) && $cmtreply['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || ( !empty($cmtreply['mseq']) && !defined('__ISUSER__') ) ) {
					$cmtreply['isperm_moddel'] = '_mbno';

					if( ($cmtreply['mseq'] != $this->userInfo['member_seq'] && defined('__ISUSER__') === true ) || $cmtreply['mseq'] < 0 ) {
						$cmtreply['isperm_hide'] = 'hide';//버튼숨김(회원 > 본인만 가능함 or 관리자인경우 숨김
					}

				}else{

					$ss_pwwrite_name = 'board_cmtpwhidden_'.BOARDID.'_'.$cmtdatarow['parent'];
					$boardpwwritess = $this->session->userdata($ss_pwwrite_name);
					if ( strstr($boardpwwritess,'['.$cmtreply['seq'].']') && !empty($boardpwwritess)) {
						$cmtreply['isperm_moddel'] = '';//비회원 > 접근권한있음
					}
				}

				if( $cmtreply['hidden'] == 1 ) {//비밀글

					$ss_pwview_name = 'cmthidden_view_'.BOARDID.'_'.$cmtdatarow['parent'];
					$boardpwviews = $this->session->userdata($ss_pwview_name);
					if ( strstr($boardpwviews,'['.$cmtreply['seq'].']') && !empty($boardpwviews)) {
						$cmtreply['isperm_hidden'] = '';//비회원 > 접근권한있음
						if ( $cmtdatarow['isperm_moddel'] ) {
							if( $cmtreply['isperm_moddel'] == '_mbno' ) {
								$cmtreply['isperm_moddel'] = '_hidden_mbno';
							}elseif( $cmtreply['isperm_moddel'] == '_no' ) {
								$cmtreply['isperm_moddel'] = '_hidden_no';
							}
						}
					}else{
						if ( $cmtdatarow['isperm_moddel'] ) {
							if( $cmtreply['isperm_moddel'] == '_mbno' ) {
								$cmtreply['isperm_moddel'] = '_hidden_mbno';
								$cmtreply['isperm_hidden'] = '_hidden_mbno';
							}elseif( $cmtreply['isperm_moddel'] == '_no' ) {
								$cmtreply['isperm_moddel'] = '_hidden_no';
								$cmtreply['isperm_hidden'] = '_hidden_no';
							}
						}
					}
				}

				$cmtreply['isperm_hide'] = ( $cmtreply['mseq'] < 0 || $cmtreply['display'] == '1'  )?'hide':'';//관리가등록글은 수정삭제불가
				$cmtreply['isperm_hidden'] = ( $boarddata['isperm_view'] === true  )?'':$cmtreply['isperm_hidden'];//원작자는 댓글/비밀글보기가능

				$cmtreply['iconhidden'] = ( $cmtreply['hidden'] == 1)?' <img src="'.$this->icon_hidden_img.'" title="비밀글" > ':'';
				
				if( $this->manager['auth_cmt_recommend_use'] == 'Y' ){
					if( $this->userInfo['member_seq'] ) {
						$scoresql['whereis']	= ' and boardid= "'.BOARDID.'" '; 
						$scoresql['whereis']	.= ' and type= "comment" ';
						$scoresql['whereis']	.= ' and parent= "'.$cmtreply['parent'].'" ';//게시글 
						$scoresql['whereis']	.= ' and cparent= "'.$cmtreply['seq'].'" ';//게시글 
						$scoresql['whereis']	.= ' and mseq= "'.$this->userInfo['member_seq'].'" ';
						$getscoredata = $this->Boardscorelog->get_data($scoresql); 
						if($getscoredata) $cmtreply['is_cmt_recommend'] = '_y';
						if($cmtreply['display'] == '1' ) $cmtreply['is_cmt_recommend'] = '_d';
					}
				}


				$cmtreply['iconnew']	= ( date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$cmtreply['r_date']),0,8))) >= date("Ymd") ) ? ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ' :'';


				if( !empty($cmtreply['isperm_hidden'])  ){//비밀글
					$cmtreply['content']		= ' <span class="hand gray boad_cmt_reply_content_'.$cmtreply['seq'].'" board_cmt_seq="'.$cmtdatarow['seq'].'" board_cmt_reply_seq="'.$cmtreply['seq'].'" ><span class=" boad_cmt_reply_content'.$cmtreply['isperm_hidden'].' " board_cmt_seq="'.$cmtdatarow['seq'].'"    board_cmt_reply_seq="'.$cmtreply['seq'].'" >비밀댓글입니다.</span></span>';
				}

				$cmtreply['date']			= substr($cmtreply['r_date'],0,16);//등록일
				$cmtreply['content']		= ' <span  board_seq="'.$cmtreply['seq'].'"  board_id="'.$cmtreply['boardid'].'" > '.($cmtreply['content']).'</span>';

				$cmtreply['deletebtn'] = '<span class="small valign-middle hand"><img src="'.$this->cmt_reply_img.'" title="답글삭제" class="boad_cmt_delete_reply_btn'.$cmtreply['isperm_moddel'].'" board_cmt_seq="'.$cmtdatarow['seq'].'" board_cmt_reply_seq="'.$cmtreply['seq'].'"  ></span>';
				$cmtreplyloop[] = $cmtreply;
			}
			$cmtdatarow['cmtreplyloop'] = $cmtreplyloop;
			$cmtloop[] =$cmtdatarow;
		}

		if( !empty($_GET['cmtpage']) ) {
			$returnurl = str_replace("&cmtpage=".$_GET['cmtpage'],"",str_replace("&cmtlist=1","",str_replace("&&","&",$this->boardurl->cmtview))).'&cmtlist=1';
		}else{
			$returnurl = $this->boardurl->cmtview.'&cmtlist=1';//.$_GET['seq']
		}
		$returnurl = str_replace("&&","&",str_replace("&&&","&",$returnurl));

		if($this->manager['viewtype'] == 'layer' && ($this->pagetype== 'goods' || $this->pagetype == 'mypage') ) {
			$paginlay =  pagingtagfront($sc['searchcount']	,$sc['perpage'], $returnurl, getLinkFilter('',array_keys($sc)), 'cmtpage', ' onclick=" boardviewtypeshow(this.href,\''.$parent.'\',\''.$this->manager['viewtype'].'\');return false;" ');
		}else{
			$paginlay =  pagingtagfront($sc['searchcount']	,$sc['perpage'], $returnurl, getLinkFilter('',array_keys($sc)), 'cmtpage', '');
		}

		if($sc['searchcount'] > 0) {
			$paginlay = (!empty($paginlay)) ? $paginlay:'<p><a class="on" style="color:#ff9102">1</a><p>';
		}

		if(isset($cmtloop)) $this->template->assign('cmtloop',$cmtloop);
		$this->template->assign('cmtpagin',$paginlay);
	}

	//이전글, 다음글
	public function getBoardPreNext($data, $type, $order, $whereis, $prenextlayid,$page=null){
		if( BOARDID == 'goods_qna' || BOARDID == 'goods_review'  || BOARDID == 'bulkorder' ) {
			$whereis	= ' and gid '.$whereis.' "'.$data['gid'].'" ';
			if($_GET['goods_seq']) $whereis .= ' and (goods_seq like "%,'.$_GET['goods_seq'].'" or goods_seq like "'.$_GET['goods_seq'].',%" or goods_seq like "%,'.$_GET['goods_seq'].',%" or goods_seq='.$_GET['goods_seq'].' )';//상품


			if($page == 'mypage'  || BOARDID == 'mbqna' ) {//마이페이지에서 접근시
				$whereis .= ' and mseq = "'.$this->userInfo['member_seq'].'" ';
			}

			if( defined('__SELLERADMIN__') === true  && (BOARDID == 'goods_qna' || BOARDID == 'goods_review') ) {//입점사관리자인경우
				//$whereis .= ' and provider_seq = "'.$this->providerInfo['provider_seq'].'" ';
			}

			$prenextsql['whereis'] = $whereis;
			$prenextsql['select']		= ' seq, gid, subject, comment, display, m_date, r_date, d_date, hit, upload, hidden  , mseq , name, contents ';
		}else{
			$whereis	= ' and gid '.$whereis.' "'.$data['gid'].'" ';
			if($page == 'mypage'  || BOARDID == 'mbqna' ) {//마이페이지에서 접근시
				$whereis .= ' and mseq = "'.$this->userInfo['member_seq'].'" ';
			}
			$prenextsql['whereis']	= ' and boardid = "'.BOARDID.'" '.$whereis;
			$prenextsql['select']		= ' seq, gid, boardid, subject, comment, display, m_date, r_date, d_date, hit, upload, hidden , mseq ,  name, contents ';
		}
		$prenextsql['orderby']	= ' gid '.$order.' ';
		$prenextdata = $this->Boardmodel->get_data_prenext($prenextsql);

		if (isset($prenextdata['seq'])) {
			$isperm_read = '';
			if(isset($prenextdata['hidden']) && $prenextdata['hidden'] == 1 ){//비밀글
				if ( ($prenextdata['mseq'] > 0 ) ) {//작성자가 회원인 경우
					if( $prenextdata['mseq'] != trim($this->userInfo['member_seq']) && defined('__ISUSER__') || ( !defined('__ISUSER__') ) ) {//작성자가 아니거나 비회원인 경우
						$isperm_read = '_mbno';
					}
				}else{//비회원인경우
					// 비번입력후 브라우저를 닫기전까지는 접근가능함
					$ss_pwhidden_name = 'board_pwhidden_'.BOARDID;
					$boardpwhiddenss = $this->session->userdata($ss_pwhidden_name);
					if(!strstr($boardpwhiddenss,'['.$prenextdata['seq'].']') && isset($boardpwhiddenss)) {// ||
						$isperm_read = '_no';
					}
				}
			}
			$prenextdata['subject']			= (  $this->siteType == 'mobile'  || $this->_is_mobile_agent) ? getstrcut(strip_tags($prenextdata['subject']), $this->manager['mobile_subjectcut']):getstrcut(strip_tags($prenextdata['subject']), $this->manager['subjectcut']);

			if($prenextdata['display'] == 1 ) {
				$prenextdata['subject'] = '<span class="gray" >삭제되었습니다. ['.$prenextdata['r_date'].'] </span>';
			}

			$iconnew		= ($this->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$this->manager['icon_new_day'].' day '.substr(str_replace("-","",$prenextdata['r_date']),0,8))) >= date("Ymd") )  ? ' <img src="'.$this->icon_new_img.'" title="new" align="absmiddle" > ':'';
			$iconhot		= ($this->manager['icon_hot_visit'] > 0 && $this->manager['icon_hot_visit'] <= $prenextdata['hit'] ) ? ' <img src="'.$this->icon_hot_img.'" title="hot" > ':'';
			$iconfile		= (($prenextdata['upload']) && getBoardFileck($prenextdata['upload'], $prenextdata['contents']) )?' <img src="'.$this->icon_file_img.'" title="첨부파일" align="absmiddle" > ':'';
			$iconhidden = ($prenextdata['hidden'] == 1 ) ?' <img src="'.$this->icon_hidden_img.'" title="비밀글" > ':'';

			$commentcnt = ($prenextdata['comment']>0) ? ' <span class="comment">('.number_format($prenextdata['comment']).')':'';
			$prenextdata['commentcnt']		= $commentcnt;
			$prenextlay['subject'] = '<span class="hand highlight-link boad_view_btn'.$isperm_read.' sbj" viewlink="'.$this->boardurl->view.$prenextdata['seq'].'" viewtype="'.$this->manager['viewtype'].'"  board_seq="'.$prenextdata['seq'].'"  board_id="'.BOARDID.'" ><a >'.$prenextdata['subject'].'</a></span> '.$commentcnt.$iconnew.$iconhot.$iconfile.$iconhidden;
			getminfo($this->manager, $prenextdata, $mdata, $boardname);//회원정보
			$prenextlay['real_name'] = $prenextdata['real_name'];
			$prenextlay['name'] = $boardname;
			$prenextlay['date']	= substr($prenextdata['r_date'],0,16);//등록일
			$prenextlay['m_date']	= substr($prenextdata['m_date'],0,16);
			$prenextlay['d_date']	= substr($prenextdata['d_date'],0,16);
			$this->template->assign($prenextlayid,$prenextlay);
		}
	}


	//비밀번호 찾기
	public function pwcheck()
	{
		$board = $this->skin.'/board/_pwcheck.html';
		$this->template->assign('boardurl',$this->boardurl);
		$this->print_layout($board);
	}


	//잘못된 접근
	public function permcheck()
	{
		$board = $this->skin.'/board/_permcheck.html';
		$this->template->assign('boardurl',$this->boardurl);
		$this->print_layout($board);
	}

	//잘못된 접근
	public function MY_permcheck()
	{
		echo "test";
	}

	//동영상등록새창
	public function popup_video()
	{
		$this->load->model('videofiles');
		$this->load->helper('readurl');

		$boardid = ($_POST['id'])?$_POST['id']:$_GET['id'];
		$sc['whereis']	= ' and id= "'.$boardid.'" ';
		$sc['select']		= ' * ';
		$this->manager  = $this->Boardmanager->managerdataidck($sc);//게시판정보
		$this->template->assign('manager',$this->manager );
		$cfg_goods = config_load('goods');
		$this->template->assign('cfg_goods',$cfg_goods);

		$_GET['iframe'] = 1;
		if($_POST['file_key_W']) {
			$file_key_w = $_POST['file_key_W'];//웹 인코딩 코드
		}
		if($_POST['file_key_I']) {
			$file_key_i = $_POST['file_key_I'];//스마트폰 인코딩 코드
		}


		/* 파라미터 검증*/
		$seq = ($_POST['seq'])?(int) $_POST['seq']:($_GET['seq']);
		if($file_key_w || $file_key_i) {
			if($seq ){
				$params['file_key_w']	= $file_key_w;//웹 인코딩 코드
				$params['file_key_i']	= $file_key_i;//웹 인코딩 코드
				$params['seq']				= $seq;
				$this->Boardmodel->data_modify($params);
			}

			$videofiles['parentseq']			= ($seq)?$seq:'';
			$videofiles['upkind']					= 'board';
			$videofiles['type']						= $boardid;
			$videofiles['tmpcode']				= $this->session->userdata('boardvideotmpcode');//
			$videofiles['mbseq']					= $this->userInfo['member_seq'];//
			$videofiles['r_date']					= date("Y-m-d H:i:s");
			$videofiles['file_key_w']			= $file_key_w;//웹 인코딩 코드
			$videofiles['file_key_i']				= $file_key_i;//웹 인코딩 코드
			$videoinforesult = readurl(uccdomain('fileinfo',$file_key_w));
			if($videoinforesult){
				$videoinfoarr = xml2array($videoinforesult);
				$videofiles['playtime']		 = ($videoinfoarr['class']['playtime'])?$videoinfoarr['class']['playtime']:'';
			}
			$videofiles['memo']					= '';
			$videofiles['encoding_speed']	= ($_POST['encoding_speed'])?$_POST['encoding_speed']:400;
			$videofiles['encoding_screen'] = (is_array($_POST['encoding_screen'])) ? @implode("X",($_POST['encoding_screen'])):'400X300';

			$videoseq = $this->videofiles->videofiles_write($videofiles);
		}


		if( $this->_is_mobile_agent ){
			$this->template->assign("ismobileagent",true);
			if($file_key_i){
				$uccdomainembedsrc = uccdomain('fileurl',$file_key_i, $manager);
			}else{
				$uccdomainembedsrc = uccdomain('fileurl',$file_key_w, $manager);
			}
		}else{
			if($file_key_i){
				$uccdomainembedsrc = uccdomain('fileswf',$file_key_i, $manager);
			}else{
				$uccdomainembedsrc = uccdomain('fileswf',$file_key_w, $manager);
			}
		}
		$this->template->assign("uccdomainembedsrc",$uccdomainembedsrc);
		$this->template->assign("file_key_w",$file_key_w);
		$this->template->assign("file_key_i",$file_key_i);
		$this->template->assign("encoding_screen",$_POST['encoding_screen']);
		$this->template->assign("encoding_speed",$_POST['encoding_speed']);


		$file_path	= $this->template_path();
		//동영상연결(기본 파일찾기)
		$this->template->assign("uccdomain",uccdomain());
		if( $_POST['file_key_W'] || $_POST['file_key_I'] ) {
			$this->template->assign("videook",true);
		}else{
			$this->template->assign("videook",false);
		}

		if( $_POST['error']) {
			$this->template->assign("videoerror",true);
			$this->template->assign("error",$_POST['error']);
		}else{
			$this->template->assign("videoerror",false);
		}
		$board = $this->skin.'/board/_popup_video.html';
		$this->print_layout($board);
	}


	public function zoom()
	{
		$board = $this->skin.'/board/_zoom.html';
		$this->print_layout($board);
	}
}
/* End of file board.php */
/* Location: ./app/controllers/board.php */


