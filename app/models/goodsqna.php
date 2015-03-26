<?php
class Goodsqna extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_data = 'fm_goods_qna';
		$this->goods = 'fm_goods';
		$this->provider = 'fm_provider';

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
		$sqlSelectClause = "select  * ";
		$sqlFromClause = " from ".$this->table_data." ";

		$sqlWhereClause = " where 1 ";

		//공지만노출여부
		//if( defined('__ADMIN__') != true ) {
			$sqlWhereClause .= " and (onlynotice != '1') ";
		//}

		if(!empty($sc['mid'])) $sqlWhereClause.= ' and mid='.$sc['mid'];//회원
		if(!empty($sc['goods_seq'])) $sqlWhereClause.= ' and (goods_seq like "%,'.$sc['goods_seq'].'" or goods_seq like "'.$sc['goods_seq'].',%" or goods_seq like "%,'.$sc['goods_seq'].',%" or goods_seq='.$sc['goods_seq'].' )';//상품
		if(!empty($sc['member_seq'])) $sqlWhereClause.= ' and mseq='.$sc['member_seq'];//회원
		if(!empty($sc['mseq'])) $sqlWhereClause.= ' and mseq='.$sc['mseq'];//회원


		if( defined('__SELLERADMIN__') === true ) {
		//	$sqlWhereClause.= ' and provider_seq='.$this->providerInfo['provider_seq'];//입점사
		}

		// 등록일 검색(시작)
		if($sc['rdate_s'] AND !$sc['rdate_f']) {
			$start_date = $sc['rdate_s'].' 00:00:00';
			$sqlWhereClause.=" AND m_date >= '{$start_date}' ";
		}

		// 등록일 검색(끝)
		if($sc['rdate_f'] AND !$sc['rdate_s']) {
			$start_date = $sc['rdate_f'].' 23:59:59';
			$sqlWhereClause.=" AND m_date <= '{$start_date}' ";
		}

		// 등록일 검색
		if($sc['rdate_s'] AND $sc['rdate_f']) {
			$start_date = $sc['rdate_s'].' 00:00:00';
			$end_date = $sc['rdate_f'].' 23:59:59';
			$sqlWhereClause.=" AND m_date BETWEEN '{$start_date}' AND '{$end_date}' ";
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
			$sqlWhereClause .= ' and ( ';
			$sqlWhereClause .= ' subject like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%"  or contents like "%'.$sc['search_text'].'%" or mid like "%'.$sc['search_text'].'%"  ';//
			$sqlWhereClause .= $this->getGoodsSearch($sc);//상품명검색
			$sqlWhereClause .= ' ) ';
		}elseif(!empty($sc['catalog_code'])) {
			$sqlWhereClause .= ' and (  1 ';
			$sqlWhereClause .= $this->getGoodsSearch($sc);//상품명검색
			$sqlWhereClause .= ' ) ';
		}elseif(!empty($sc['brand_code'])) {
			$sqlWhereClause .= ' and (  1 ';
			$sqlWhereClause .= $this->getGoodsSearch($sc);//상품명검색
			$sqlWhereClause .= ' ) ';
		}


		$sqlOrderClause =" order by {$sc['orderby']} {$sc['sort']}";
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
			$cntrow = $cntquery->result_array();
			$data['count'] = $cntrow[0]['cnt'];
		}


		//debug_var($data);
		return $data;
	}

	//상품명검색하기
	public function getGoodsSearch($sc) {
		if(!empty($sc['search_text']))
		{
			$whereis = ' ( goods_name like "%'.$sc['search_text'].'%" or summary like "%'.$sc['search_text'].'%"  or contents like "%'.$sc['search_text'].'%") ';//
			$sql = "select goods_seq  from ".$this->goods." where ".$whereis;
			$query = $this->db->query($sql);
			foreach($query->result_array() as $data){
				$arrNo[] = $data['goods_seq'];
			}
			$wheregoods = (isset($arrNo)) ? " or goods_seq in ('".implode("','",$arrNo)."')" : '';
			return $wheregoods;
		}elseif(!empty($sc['catalog_code'])) {//상품카테고리

			$sqlFromClause .= "
				left join fm_goods_image i on (g.goods_seq=i.goods_seq and i.cut_number=1 and i.image_type='list2')
				left join fm_goods_option o on (g.goods_seq=o.goods_seq and o.default_option='y')
				left join fm_category_link l on l.goods_seq=g.goods_seq
				left join fm_category_group as cg on l.category_code = cg.category_code
			";
			$sqlGroupbyClause = " group by g.goods_seq";
			$whereis = " and l.category_code like '".$sc['catalog_code']."%' ";
			$sql = "select g.goods_seq  from ".$this->goods." g {$sqlFromClause} where g.goods_view='look' ".$whereis.$sqlGroupbyClause;
			$query = $this->db->query($sql);
			foreach($query->result_array() as $data){
				$arrNo[] = $data['goods_seq'];
			}
			$wheregoods = (isset($arrNo)) ? " and goods_seq in ('".implode("','",$arrNo)."')" : '  and (goods_seq is null or goods_seq="" )  ';

			return $wheregoods;
		}elseif(!empty($sc['brand_code'])) {//상품브랜드
			$sqlFromClause .= "
				left join fm_goods_image i on (g.goods_seq=i.goods_seq and i.cut_number=1 and i.image_type='list2')
				left join fm_goods_option o on (g.goods_seq=o.goods_seq and o.default_option='y')
				left join fm_brand_link bl on bl.goods_seq=g.goods_seq
				left join fm_brand_group as bg on bl.category_code = bg.category_code
			";
			$sqlGroupbyClause = " group by g.goods_seq";
			$whereis .= " bl.category_code like '".$sc['brand_code']."%'";
			$sql = "select g.goods_seq  from ".$this->goods." g {$sqlFromClause} where ".$whereis.$sqlGroupbyClause;
			$query = $this->db->query($sql);
			foreach($query->result_array() as $data){
				$arrNo[] = $data['goods_seq'];
			}
			$wheregoods = (isset($arrNo)) ? " and goods_seq in ('".implode("','",$arrNo)."')" : '  and (goods_seq is null or goods_seq="" )  ';
			return $wheregoods;
		}else{
			return '';
		}
	}


	//입점사아이디/입점사명 검색하기
	public function getProviderSearch($sc) {
		/**
		if(!empty($sc['search_text']))
		{
			$whereis = ' ( provider_id like "%'.$sc['search_text'].'%" or provider_name like "%'.$sc['search_text'].'%" ) ';//
			$sql = "select provider_seq  from ".$this->provider." where ".$whereis;
			$query = $this->db->query($sql);
			foreach($query->result_array() as $data){
				$arrNo[] = $data['provider_seq'];
			}
			$whereprovider = (isset($arrNo)) ? " or provider_seq in ('".implode("','",$arrNo)."')" : '';
			return $whereprovider;
		}else{
			return '';
		}
		**/
	}

	// 게시물총건수
	public function get_item_total_count($sc)
	{
		$cnt_query = 'select count(*) as cnt from '.$this->table_data;
		
		//프론트 공지만노출여부
		if( defined('__ADMIN__') != true ) {
			$cnt_query .= " where onlynotice != '1' "; 
		}

		if( $this->pagetype == 'mypage' ) {
			if(!empty($sc['member_seq'])) $cnt_query.= ' and mseq='.$sc['member_seq'];//회원
			if(!empty($sc['mseq'])) $cnt_query.= ' and mseq='.$sc['mseq'];//회원
		}

		$cntquery = $this->db->query($cnt_query);
		$cntrow = $cntquery->result_array();
		return $cntrow[0]['cnt'];
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
		$data = $query->row_array();
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
		$sql = "OPTIMIZE TABLE ".$this->table_data;
		$this->db->query($sql);
	}

	// 조회수 증가
	function hit_update($seq) {
		if(empty($seq))return false;
		$this->db->set('hit', 'hit + 1', FALSE);
		$this->db->update($this->table_data, null, array('seq' => $seq));
	}

	// 추천/비추천/추천5가지 증가
	function board_score_update($seq, $scoreid, $plus = ' + ') {
		if(empty($seq))return false;
		$this->db->set($scoreid, $scoreid.' '.$plus.' 1', FALSE);
		$result = $this->db->update($this->table_data, null, array('seq' => $seq));
		return $result;
	}

	// tmpcode 저장
	function tmpcode_update($seq,$tmpcode) {
		if(empty($seq))return false;
		$this->db->set('tmpcode', $tmpcode, FALSE);
		$this->db->update($this->table_data, null, array('seq' => $seq));
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
		$result = $this->db->delete($this->table_data, array('seq' => $seq));
		return $result;
	}

	/*
	 * 게시물전체삭제
	 * @param
	*/
	public function data_delete_id($boardid) {
		//$result = $this->db->delete($this->table_data, array('boardid' => $boardid));
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

/* End of file goodsqna.php */
/* Location: ./app/models/goodsqna */