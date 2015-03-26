<?php
/**
 * 게시글 관련 모듈
 * @author gabia
 * @since version 1.0 - 2012.06.29
 */
class Boardmodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_data = 'fm_boarddata';
		if(!empty($_GET['seq'])) {//게시글상세
			$this->seq = $_GET['seq'];
		}
		if ( $this->widgetboardid ) {
			$this->upload_path		= $this->Boardmanager->board_data_dir.$this->widgetboardid.'/';
			$this->upload_src		= $this->Boardmanager->board_data_src.$this->widgetboardid.'/';
		}else{
			$this->upload_path		= $this->Boardmanager->board_data_dir.BOARDID.'/';
			$this->upload_src		= $this->Boardmanager->board_data_src.BOARDID.'/';
		}
	}

	/*
	 * 게시물관리
	 * @param
	*/
	public function data_list($sc, $func = null) {
		$sqlSelectClause = "select * ";
		$sqlFromClause = " from ".$this->table_data." ";

		if ( $sc['boardid'] ) {
			$sqlWhereClause = " where boardid= '".$sc['boardid']."' ";
		}elseif( defined('BOARDID') ){
			$sqlWhereClause = " where boardid = '".BOARDID."' ";
		}else{
			$sqlWhereClause = " where 1 ";
		}

		//공지만노출여부
		//if( defined('__ADMIN__') != true ) {
			$sqlWhereClause .= " and (onlynotice != '1') ";
		//}

		// 평점 정보 추가
		if( $sc['score_avg'] ) {
			$sqlWhereClause .= " and score_avg = '".$sc['score_avg']."' ";
		}

		if(!empty($sc['mid'])) $sqlWhereClause.= ' and mid='.$sc['mid'];//회원
		if(!empty($sc['member_seq'])) $sqlWhereClause.= ' and mseq='.$sc['member_seq'];//회원
		if(!empty($sc['mseq'])) $sqlWhereClause.= ' and mseq='.$sc['mseq'];//회원

		// 등록일 검색(시작)
		if($sc['rdate_s'] AND !$sc['rdate_f']) {
			$start_date = $sc['rdate_s'].' 00:00:00';
			$sqlWhereClause.=" AND r_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['rdate_f'] AND !$sc['rdate_s']) {
			$start_date = $sc['rdate_f'].' 23:59:59';
			$sqlWhereClause.=" AND r_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['rdate_s'] AND $sc['rdate_f']) {
			$start_date = $sc['rdate_s'].' 00:00:00';
			$end_date = $sc['rdate_f'].' 23:59:59';
			$sqlWhereClause.=" AND r_date BETWEEN '{$start_date}' AND '{$end_date}' ";
		}


		if( !empty($sc['isimage']) ) {//첨부파일 : 이미지검색
			$sqlWhereClause .= " and ( upload like '%image/%') ";
		}

		if( !empty($sc['display']) ) {//삭제글
			$display = ($sc['display']-1);
			$sqlWhereClause .= " and display='{$display}' ";
		}


		if(!empty($sc['hidden']) && $sc['hidden'] != 'all' ){
			if( $sc['hidden'] == '2' ) {//비밀글
				$sqlWhereClause .= " and hidden ='1' ";
			}elseif( $sc['hidden'] == '1' ) {//비밀글
				$sqlWhereClause .= " and hidden !='1' ";
			}
		}

		if( !empty($sc['notice']) ) {//공지글(팝업공지글)
			$notice = ($sc['notice']-1);
			$sqlWhereClause .= " and notice='{$notice}' ";
		}

		if( !empty($sc['onlypopup']) ) {//팝업여부
			$sqlWhereClause .= " and ( onlypopup='y' or (onlypopup ='d' and onlypopup_sdate <= '".date("Y-m-d")."' and onlypopup_edate >= '".date("Y-m-d")."' )) ";
		}

		if( !empty($sc['searchreply']) ) {//답변여부
			if( ($sc['searchreply'])=='y' ) {//답변대기중
				$sqlWhereClause .= " and (re_contents = '' or re_contents is null) ";
			}else{
				$sqlWhereClause .= " and re_contents !='' ";
			}
		}

		//동영상
		if( $sc['file_key_w'] ){
			$sql .= " and ( file_key_w != '') ";
		}

		if(!empty($sc['category']))
		{
			$sc['category'] = trim(addslashes(str_replace(' ','',$sc['category'])));
			$sqlWhereClause .= " and ( REPLACE(category,' ','') like '%{$sc['category']}%' ) ";
		}

		if(!empty($sc['search_text']))
		{
			$sqlWhereClause .= ' and ( subject like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%"  or contents like "%'.$sc['search_text'].'%"  or mid like "%'.$sc['search_text'].'%" ) ';
		}

		$sqlOrderClause =" order by {$sc['orderby']} {$sc['sort']}";
		$sqlOrderClause .=", boardid desc ";
		$limit =" limit {$sc['page']}, {$sc['perpage']} ";

		$sql = "
			{$sqlSelectClause}
			{$sqlFromClause}
			{$sqlWhereClause}
			{$sqlOrderClause}
		";

		$query = $this->db->query($sql.$limit);
		$data['result'] = $query->result_array();

		if( !$func ){
			//총건수
			$cnt_query = 'select count(*) as cnt '. $sqlFromClause . $sqlWhereClause;
			$cntquery = $this->db->query($cnt_query);
			$cntrow = $cntquery->row_array();
			$data['count'] = $cntrow['cnt'];
		}

		return $data;
	}

	// 미답변 갯수 추출 :: 2014-10-22 lwh
	public function reply_count($sc) {
		$sqlSelectClause	= "select count(*) cnt ";
		if( $sc['boardid'] == 'goods_qna' ){
			$sqlFromClause		= " from fm_".$sc['boardid']." ";
			$sqlWhereClause		= " where 1=1 ";
		}else{
			$sqlFromClause		= " from ".$this->table_data." ";
			$sqlWhereClause		= " where boardid= '".$sc['boardid']."' ";
		}			

		if( !empty($sc['searchreply']) ) {//답변여부
			if( ($sc['searchreply'])=='y' ) {//답변대기중
				$sqlWhereClause .= " and (re_contents = '' or re_contents is null) and display != 1 ";
			}else{
				$sqlWhereClause .= " and re_contents != '' ";
			}
		}

		$sql = "
				{$sqlSelectClause}
				{$sqlFromClause}
				{$sqlWhereClause}
			";

		$query		= $this->db->query($sql);
		$cnt_data	= $query->result_array();

		return $cnt_data[0]['cnt'];
	}


	// 게시물총건수
	public function get_item_total_count($sc)
	{
		$cnt_query = 'select count(*) as cnt from '.$this->table_data;

		if ( !empty($sc['boardid']) ) {
			$cnt_query .= " where boardid = '".$sc['boardid']."' ";
		}elseif ( defined('BOARDID') ) {
			$cnt_query .= " where boardid = '".BOARDID."' ";
		}else{
			$cnt_query .= " where 1 ";
		}

		if( $this->pagetype == 'mypage' ) {
			if(!empty($sc['member_seq'])) $cnt_query.= ' and mseq='.$sc['member_seq'];//회원
			if(!empty($sc['mseq'])) $cnt_query.= ' and mseq='.$sc['mseq'];//회원
		}

		//프론트 공지만노출여부
		if( defined('__ADMIN__') != true ) {
			$cnt_query .= " and (onlynotice != '1') "; 
			if( BOARDID == 'faq')$cnt_query .= " and (hidden = '1') "; 
		}


		$cntquery = $this->db->query($cnt_query);
		$cntrow = $cntquery->row_array();
		return $cntrow['cnt'];
	}


	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";

		$sql = "select ".$sc['select']." from  ".$this->table_data."  where 1 ". $sc['whereis'];
		$sql .=" order by gid asc";
		if( $sc['page'] && $sc['perpage'] ) $sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		if($query) $data = $query->row_array();
		return $data;
	}


	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data_prenext($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";

		$sql = "select ".$sc['select']." from  ".$this->table_data."  where 1 ". $sc['whereis'];
		$sql .="order by ".$sc['orderby']."  limit 0,1 ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";

		$sql = "select ".$sc['select']." from  ".$this->table_data."  where 1 ". $sc['whereis'];
		$sql .=" order by gid ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 게시물생성
	 * @param
	*/
	public function data_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_data));
		$result = $this->db->insert($this->table_data, $data);
		return $this->db->insert_id();
	}

	/*
	 * 게시물정보
	 * @param
	*/
	public function get_data_optimize() {
		$sql = "optimize table  ".$this->table_data;
		$this->db->query($sql);
	}

	// 조회수 증가
	function hit_update($seq) {
		if(empty($seq))return false;
		$this->db->set('hit', 'hit + 1', FALSE);
		$this->db->update($this->table_data, null, array('seq' => $seq,'boardid' => BOARDID));
	}

	// 추천/비추천/추천5가지 증가
	function board_score_update($seq, $scoreid, $plus = ' + ') {
		if(empty($seq))return false;
		$this->db->set($scoreid, $scoreid.' '.$plus.' 1', FALSE);
		$result = $this->db->update($this->table_data, null, array('seq' => $seq,'boardid' => BOARDID));
		return $result;
	}

	// tmpcode 저장
	function tmpcode_update($seq,$tmpcode) {
		if(empty($seq))return false;
		$this->db->set('tmpcode', $tmpcode, FALSE);
		$this->db->update($this->table_data, null, array('seq' => $seq,'boardid' => BOARDID));
	}

	/*
	 * 게시물 수정
	 * @param
	*/
	public function data_modify($params) {
		if(empty($_POST['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_data));
		$result = $this->db->update($this->table_data, $data,array('seq'=>$_POST['seq']));
		return $result;
	}


	/*
	 * 게시물 개별수정
	 * @param
	*/
	public function data_item_save($params, $seq) {
		if(empty($seq))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_data));
		$result = $this->db->update($this->table_data, $data,array('seq'=>$seq));
		return $result;
	}


	/*
	 * 게시물 gidupdate
	 * @param
	*/
	public function data_gid_save($gidup) {
		$sql = "update ".$this->table_data." set ".$gidup['set']." where ".$gidup['whereis'];
		/**if ( defined('BOARDID') ) {
			$sql .= " and boardid = '".BOARDID."' ";
		}elseif ( !empty($gidup['boardid']) ) {
			$sql .= " and boardid = '".$gidup['boardid']."' ";
		}**/
		$result = $this->db->query($sql);
		return $result;
	}

	/*
	 * 게시물삭제
	 * @param
	*/
	public function data_delete_modify($params,$seq) {
		if(empty($seq))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_data));
		$result = $this->db->update($this->table_data, $data,array('seq'=>$seq));
		return $result;
	}

	/*
	 * 게시물개별삭제
	 * @param
	*/
	public function data_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_data, array('seq' => $seq,'boardid' => BOARDID));
		return $result;
	}

	/*
	 * 게시물전체삭제
	 * @param
	*/
	public function data_delete_id($boardid) {
		$result = $this->db->delete($this->table_data, array('boardid' => $boardid));
		return $result;
	}

	/*
	 * 게시물이동
	 * @param
	*/
	public function data_move($params, $seq) {
		if(empty($seq))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_data));
		$result = $this->db->update($this->table_data, $data,array('seq'=>$seq));
		return $result;
	}

	/*
	 * 게시물복사
	 * @param
	*/
	public function data_copy($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_data));
		$result = $this->db->insert($this->table_data, $data);
		return $this->db->insert_id();
	}
}
/* End of file board.php */
/* Location: ./app/models/board */