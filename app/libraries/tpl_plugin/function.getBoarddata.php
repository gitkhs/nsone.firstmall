<?php

/**
 * @author ysm
 * @version 2.0.0
 * @license copyright by GABIA_ysm
 * @since 2012-11-13
 */
function getBoardData($boardid, $limits = 5, $whereis = null, $boardviewurl=null, $strcut=20, $contentscut=200, $write_show=null,$latestopt=null,$isall =null)
{
	$CI =& get_instance();
	$CI->widgetboardid = $boardid;
	$CI->load->helper('board');
	$CI->load->helper('text');//strcut
	$CI->load->model('Boardmanager');
	$CI->load->model('membermodel'); 
	$CI->load->model('boardadmin');
	if( $boardid == 'goods_qna' ) {
		$CI->load->model('Goodsqna','wigetGoodsqna');
		$CI->wigetBoardmodel = $CI->wigetGoodsqna;
	}elseif( $boardid == 'goods_review' ) {
		$CI->load->model('Goodsreview','wigetGoodsreview');
		$CI->wigetBoardmodel = $CI->wigetGoodsreview;
	}elseif( $boardid == 'bulkorder' ) {
			$CI->load->model('Boardbulkorder','wigetbulkorder');
		$CI->wigetBoardmodel = $CI->wigetbulkorder;
	}else{
		$CI->load->model('Boardmodel','wigetaddboardmodel');
		$CI->wigetBoardmodel = $CI->wigetaddboardmodel;
	}
	$CI->load->model('Boardindex');

	$CI->wigetBoardmodel->upload_path	= $CI->Boardmanager->board_data_dir.$boardid.'/';
	$CI->wigetBoardmodel->upload_src	= $CI->Boardmanager->board_data_src.$boardid.'/';

	$querystr = '';
	$sc['whereis']	= ' and id= "'.$boardid.'" ';
	$sc['select']		= ' * ';
	$CI->manager = $CI->Boardmanager->managerdataidck($sc);//게시판정보

	$CI->wigetboardurl->lists		= $CI->Boardmanager->realboarduserurl.$boardid.$querystr;				//게시물관리

	$CI->wigetboardurl->write		= $CI->Boardmanager->realboardwriteurl.$boardid.$querystr;				//게시물등록
	$CI->wigetboardurl->modify	= $CI->Boardmanager->realboardwriteurl.$boardid.$querystr.'&seq=';	//게시물수정
	$CI->wigetboardurl->view		= $CI->Boardmanager->realboardviewurl.$boardid.$querystr.'&seq=';	//게시물보기
	$CI->wigetboardurl->reply		= $CI->Boardmanager->realboardwriteurl.$boardid.$querystr.'&reply=y&seq=';	//게시물답변

	$CI->wigetboardurl->perm		= $CI->Boardmanager->realboardpermurl.$boardid.'&returnurl=';						//접근권한
	$CI->wigetboardurl->pw			= $CI->Boardmanager->realboardpwurl.$boardid.'&returnurl=';						//접근권한

	$CI->icon_file_img			= $CI->Boardmanager->file_icon_src;//첨부파일icon
	$CI->icon_hidden_img		= $CI->Boardmanager->hidden_icon_src;//비밀글icon
	$CI->notice_img				= $CI->Boardmanager->notice_icon_src;//공지글icon
	$CI->re_img						= $CI->Boardmanager->re_icon_src;//답변글icon
	$CI->blank_img				= $CI->Boardmanager->blank_icon_src;//blank
	$CI->print_img					= $CI->Boardmanager->print_icon_src;//print

	$wigetboardurl = $CI->wigetboardurl;
	getboardicon();

	//게시글/댓글 평가 아이콘
	if( $CI->manager['auth_recommend_use'] == 'Y' ) {
		if( $CI->manager['recommend_type'] == '3' ) {
			$icon2array = array("recommend1","recommend2","recommend3","recommend4","recommend5");
		}elseif($CI->manager['recommend_type'] == '2'){
			$icon2array = array("recommend","none_rec");
		}elseif($CI->manager['recommend_type'] == '1'){ 
			$icon2array = array("recommend");
		} 
	} 

	get_auth($CI->manager, '', 'read', $isperm);//접근권한체크
	$CI->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';
	$CI->manager['fileperm_read']= (isset($isperm['fileperm_read']))?$isperm['fileperm_read']:'';

	get_auth($CI->manager, '', 'write', $isperm);//접근권한체크
	$CI->manager['isperm_write'] = ($isperm['isperm_write'] === true)?'':'_no';

	//게시물추출시 조건추가@2012-11-13
	unset($wdata, $widgetloop,$widget);
 	if($latestopt) {
		foreach($latestopt as $latestoptar){
			$latestoptkey = @explode("=",$latestoptar);
			if($latestoptkey[1]) $widgetsql[$latestoptkey[0]] = $latestoptkey[1];
		}
	}
	//오늘부터 최근일자기준수정1
	if($widgetsql['none_auto_term']){
		$widgetsql['rdate_s'] = date("Y-m-d",strtotime("-".$widgetsql['none_auto_term']." day ".date("Y-m-d")));
		$widgetsql['rdate_f']  = date("Y-m-d");
	}
	if($widgetsql['auto_term']){
		$widgetsql['rdate_s'] = date("Y-m-d",strtotime("-".$widgetsql['auto_term']." day ".date("Y-m-d")));
		$widgetsql['rdate_f']  = date("Y-m-d");
	}


	if( $boardid == 'goods_qna') {
		if($whereis == 'mypage'){//로그인체크
			$widgetsql['member_seq']	= $CI->userInfo['member_seq'];

			$CI->wigetboardurl->lists	= '/mypage/mygdqna_catalog';
			$CI->wigetboardurl->view	= '/mypage/mygdqna_view?seq=';
		}

		$widgetsql['orderby']			= ($widgetsql['orderby'])?$widgetsql['orderby'].', m_date asc':'gid asc, m_date asc';
		$widgetsql['sort']					= ' ';
		$widgetsql['page']				= '0';
		$widgetsql['perpage']			= $limits;
		$wdata = $CI->wigetGoodsqna->data_list($widgetsql,'func');//게시판목록
	}elseif( $boardid == 'goods_review' ) {
		if(!$isall){//layout 페이지는 모든게시글추출용
			if( strstr($CI->template_path,'goods/view') && $_GET['no'] ){//상품상세(/goods/view?no=)
				$CI->pagetype = 'goods';
				$widgetsql['goods_seq']		= (!empty($_GET['no']))?$_GET['no']:'';
			}elseif( strstr($CI->template_path,'goods/catalog') ){//카테고리 (goods/catalog?code=)
				$widgetsql['catalog_code']		= (!empty($CI->getboardcatalogcode))?$CI->getboardcatalogcode:'';
			}elseif( strstr($CI->template_path,'goods/brand')){//브랜드페이지  (goods/brand?code=
				$widgetsql['brand_code']		= (!empty($CI->getboardbrandcode))?$CI->getboardbrandcode:'';
			}
		}

		if($whereis == 'mypage'){//로그인체크
			$widgetsql['member_seq']	= $CI->userInfo['member_seq'];

			$CI->wigetboardurl->lists	= '/mypage/mygdreview_catalog';
			$CI->wigetboardurl->view	= '/mypage/mygdreview_view?seq=';
		}
		$widgetsql['orderby']			= ($widgetsql['orderby'])?$widgetsql['orderby'].', m_date asc':'gid asc, m_date asc';
		$widgetsql['sort']					= ' ';
		$widgetsql['page']				= '0';
		$widgetsql['perpage']			= $limits;
		if($widgetsql['displayBoard']) {
			$widgetsql['isseq']				= explode(",",$widgetsql['displayBoard']);
		}
		$wdata = $CI->wigetGoodsreview->data_list($widgetsql,'func');//게시판목록
	}elseif( $boardid == 'bulkorder' ) {
		if($whereis == 'mypage'){//로그인체크
			$widgetsql['member_seq']	= $CI->userInfo['member_seq'];

			$CI->wigetboardurl->lists	= '/mypage/mybulkorder_catalog';
			$CI->wigetboardurl->view	= '/mypage/mybulkorder_view?seq=';
		}

		$widgetsql['orderby']			= ($widgetsql['orderby'])?$widgetsql['orderby'].', m_date asc':'gid asc, m_date asc';
		$widgetsql['sort']					= ' ';
		$widgetsql['page']				= '0';
		$widgetsql['perpage']			= $limits;
		$wdata = $CI->wigetbulkorder->data_list($widgetsql,'func');//게시판목록
	}else{
		if($whereis == 'mypage'){//로그인체크
			$widgetsql['member_seq']	= $CI->userInfo['member_seq'];
			if( $boardid == 'mbqna' ) {
				$CI->wigetboardurl->lists	= '/mypage/myqna_catalog';
				$CI->wigetboardurl->view	= '/mypage/myqna_view?seq=';
			}
		}
		$widgetsql['orderby']			= ($widgetsql['orderby'])?$widgetsql['orderby'].', m_date asc':'gid asc, m_date asc';
		$widgetsql['sort']					= ' ';
		$widgetsql['boardid']	= $boardid;
		$widgetsql['page']				= '0';
		$widgetsql['perpage']			= $limits;
		if($boardid == 'faq' ) $widgetsql['hidden']			= 2;//노출여부
		$wdata = $CI->wigetaddboardmodel->data_list($widgetsql,'func');//게시판목록
	}
	$idx = $limits;
	foreach($wdata['result'] as $widget){$idx--;
		
		get_auth_btn($CI->manager,$widget);//접근권한체크2
		$widget['wigetboardurl_list'] = $CI->wigetboardurl->lists;
		if($boardviewurl){
			$widget['wigetboardurl_view'] = $boardviewurl.$widget['seq'];
		}else{
			$widget['wigetboardurl_view'] = $CI->wigetboardurl->view.$widget['seq'];
		}

		if ( !empty($widget['goods_seq']) ) {//상품정보추출
			$widget['goodsInfo']		= getGoodsinfo($widget, $widget['goods_seq'], 'write');
			if($widget['goodsInfo'][0]) $widget['goodsInfo'] = $widget['goodsInfo'][0];
		}
		if( $boardid == 'goods_review' ) {
			$widget['buyertitle'] = ($widget['order_seq'])?'구매':'미구매';
			if($CI->manager['goods_review_type'] == 'INT' && $widget['reviewcategory']){
				$widget['scorelay'] = getGoodsScore($widget['score_avg'], $CI->manager);
				if(sizeof(explode(",",$widget['reviewcategory']))>1) $widget['score_avg_lay'] = 'score_avg';
			}else{
				$widget['scorelay'] = getGoodsScore($widget['score'], $CI->manager);
			}

		}

		$widget['number'] = $idx;//번호
		$widget['category'] = (!empty($widget['category']) )? ' <span class="cat">['.$widget['category'].']</span>':'';

		$CI->manager['write_show'] = ($write_show)?$write_show:$CI->manager['write_show'];//작성자표기설정변경
		getminfo($CI->manager, $widget, $mdata, $boardname);//회원정보
		$widget['name'] = $boardname;

		if($widget['hidden'] == 1) {//비밀글
			if( !empty($widget['mseq'])) {//회원이 쓴글인경우
				if( ($widget['mseq'] != $CI->userInfo['member_seq'] && defined('__ISUSER__')) ) {//회원이 쓴글인경우
					$CI->manager['isperm_read'] = '_mbno';
					$CI->manager['fileperm_read']= '';
				}
			}else{
				// 비번입력후 브라우저를 닫기전까지는 접근가능함
				$ss_pwhidden_name = 'board_pwhidden_'.$boardid;
				$boardpwhiddenss = $CI->session->userdata($ss_pwhidden_name);
				if( !strstr($boardpwhiddenss,'['.$widget['seq'].']') && isset($boardpwhiddenss)) {
					$CI->manager['isperm_read'] = '_no';
					$CI->manager['fileperm_read']= $CI->wigetboardurl->pw.urlencode($CI->wigetboardurl->view.$widget['seq']);
				}
			}
		}

		/* 게시물 첨부 이미지 */
		if($widget['upload']) {
			$uploadar = @explode("|",$widget['upload']);
			foreach($uploadar as $filenamear){
				$filelistar = @explode("^^",$filenamear);
				@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;
				$thumbimg	= $realfile;
				$realfile		= (strstr($thumbimg,'_thumb_') && is_file($CI->wigetBoardmodel->upload_path.str_replace('_thumb_','',$thumbimg)))?str_replace('_thumb_','',$thumbimg):$thumbimg;

				if(empty($typefile)) {
					$imagescales = @getImageSize($CI->wigetBoardmodel->upload_path.$realfile);
					$is_image			= ($filetypetmp  && preg_match("/image/",$imagescales['mime']) )?1:0;
					if($is_image) {
						$source = $CI->wigetBoardmodel->upload_path.$thumbimg;
						$target = str_replace($thumbimg, '_widget_thumb_'.$thumbimg,$CI->wigetBoardmodel->upload_path.$realfile);
						if( !is_file($target)){
							$widgetsql['image_w'] = ($widgetsql['image_w'])?$widgetsql['image_w']:'250';
							$widgetsql['image_h'] = ($widgetsql['image_h'])?$widgetsql['image_h']:'250';
							board_image_thumb($source,$target,$widgetsql['image_w'],$widgetsql['image_h'],'widget');
							$widget['filelist'] = $CI->wigetBoardmodel->upload_src.'_widget_thumb_'.$thumbimg;break;
						}else{
							$widget['filelist'] = $CI->wigetBoardmodel->upload_src.'_widget_thumb_'.$thumbimg;break;
						}
					}
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
					if($is_image) {
						$source = $CI->wigetBoardmodel->upload_path.$thumbimg;
						$target = str_replace($thumbimg, '_widget_thumb_'.$thumbimg,$CI->wigetBoardmodel->upload_path.$realfile);
						if( !is_file($target)){
							$widgetsql['image_w'] = ($widgetsql['image_w'])?$widgetsql['image_w']:'250';
							$widgetsql['image_h'] = ($widgetsql['image_h'])?$widgetsql['image_h']:'250'; 
							board_image_thumb($source,$target,$widgetsql['image_w'],$widgetsql['image_h'],'widget');
							$widget['filelist'] = $CI->wigetBoardmodel->upload_src.'_widget_thumb_'.$thumbimg;break;
						}else{
							$widget['filelist'] = $CI->wigetBoardmodel->upload_src.'_widget_thumb_'.$thumbimg;break;
						}
					}
				}
			}
		}

		if(!isset($widget['filelist']) ) {
			@preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$widget['contents'],$list_image);
			if($list_image[1]) {
				$list_imagear = @explode(" ",$list_image[1]);
				$filenamear = $list_imagear[0];

				$filelistar		= @explode("/",$filenamear);
				$thumbimg	= @end($filelistar);
				$realfile		= (strstr($thumbimg,'_thumb_') && is_file($CI->wigetBoardmodel->upload_path.str_replace('_thumb_','',$thumbimg)))?str_replace('_thumb_','',$thumbimg):$thumbimg;
				
				if( $realfile && is_file($CI->wigetBoardmodel->upload_path.$realfile) ){
					$widget['filelist'] = $CI->wigetBoardmodel->upload_src.$realfile;
				}else{
					$widget['filelist'] = $filenamear;
				} 
			}
		}
		
		if($widget['hidden'] == 1  && BOARDID != 'faq' && $CI->manager['isperm_read'] ){
			$widget['contents_real'] =' <span class="gray">비밀글입니다</span>';
			$widget['contents']		 = ' <span class="gray">비밀글입니다</span>';
		}else{
			$contents_tmp =  str_replace('&nbsp;', ' ', $widget['contents']);
			$widget['contents_real'] = $widget['contents'];
			$widget['contents'] = getstrcut(strip_tags($contents_tmp), $contentscut);
		}
		$widget['subject_real'] = $widget['subject'];// 2012-11-09 추가
		$widget['reply_title']		= ($widget['re_contents'])?'<span class="blue" >답변완료</span>':'<span class="gray" >답변대기</span>';//상태

		$widget['recommendlay'] =  getRecommendviewer($widget,'widget'); 

		if($widget['display'] == 1 ){//삭제시
			$widget['iconnew']	= '';
			$widget['iconhot']		= '';
			$widget['iconfile']		= '';
			$widget['iconhidden'] = '';
			$widget['blank']			= ($widget['depth']>0) ? ' <img src="'.$CI->blank_img.'" title="blank" width="'.(($widget['depth']-1)*1).'" ><img src="'.$CI->re_img.'" title="답변" >':'';//답변
			$commentcnt = ($widget['comment']>0) ? ' <span class="comment">('.number_format($widget['comment']).')</span>':'';
			$widget['subject']		= $widget['blank'].' <span class="hand gray boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->wigetboardurl->view.$widget['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$widget['seq'].'"  board_id="'.$boardid.'" ><a>삭제되었습니다 ['.substr($widget['r_date'],0,16).']</a></span>'.$commentcnt;
			$widget['date']			= substr($widget['r_date'],0,16);

			if($widget['replyor'] == 0 && $widget['comment'] == 0) {//삭제후 답변이나  댓글이 없는 경우 삭제가능
				$widget['deletebtn'] = '<span class="btn small  valign-middle"><input type="button" name="boad_delete_btn" board_seq="'.$widget['seq'].'"  board_id="'.$boardid.'" value="삭제" /></span>';
			}
		}else{
			if( $CI->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$CI->manager['icon_new_day'].' day '.substr(str_replace("-","",$widget['r_date']),0,8))) >= date("Ymd") ) {//new
				$widget['iconnew']	= ' <img src="'.$CI->icon_new_img.'" title="new" > ';
			}else{
				$widget['iconnew'] ='';
			}

			$widget['subject']		= getstrcut($widget['subject'], $strcut);

			$widget['iconhot']		= ($CI->manager['icon_hot_visit'] > 0 && $CI->manager['icon_hot_visit'] <= $widget['hit']) ? ' <img src="'.$CI->icon_hot_img.'" title="hot" > ':'';//조회수
			$widget['iconfile']		= ( getBoardFileck($widget['upload'], $widget['contents_real']) ) ?' <img src="'.$CI->icon_file_img.'" title="첨부파일" > ':'';//첨부파일
			$widget['iconhidden'] = ($widget['hidden'] == 1  && $boardid != "faq") ? ' <img src="'.$CI->icon_hidden_img.'" title="비밀글" > ':'';

			$widget['date']			= substr($widget['r_date'],0,16);//등록일
			$widget['blank']			= ($widget['depth']>0) ? ' <img src="'.$CI->blank_img.'" title="blank" width="'.(($widget['depth']-1)*3).'" ><img src="'.$CI->re_img.'" title="답변" >':'';//답변
			$commentcnt = ($widget['comment']>0) ? ' <span class="comment">('.number_format($widget['comment']).')</span>':'';
			$widget['subject']		= $widget['blank'].$widget['category'].' '.$widget['subject'].'</span>'.$commentcnt;

			//상품 >> 첨부파일이 없는경우 >> 상품상세는제외
			if(!isset($widget['filelist']) && $widget['goodsInfo'] && (!strstr($CI->template_path,'goods/view') || $isall ) ) {
				$widget['filelist'] = $widget['goodsInfo']['image'];
			}
		}
		if( $boardid == 'faq' ) {
			$widget['subject']		= ' <a href="'.$CI->wigetboardurl->lists.'&seq='.$widget['seq'].'"  board_seq="'.$widget['seq'].'"  board_id="'.$boardid.'" >'.$widget['subject'].'</a>';
		}else{
			if($boardviewurl){
				$widget['subject']		= ' <a href="'.$boardviewurl.$widget['seq'].'"  board_seq="'.$widget['seq'].'"  board_id="'.$boardid.'" >'.$widget['subject'].'</a>';
			}else{
				$widget['subject']		= ' <a href="'.$CI->wigetboardurl->view.$widget['seq'].'"  board_seq="'.$widget['seq'].'"  board_id="'.$boardid.'" >'.$widget['subject'].'</a>';
			}
		}

		$widgetloop[] = $widget;
	}
	return $widgetloop;
}
?>