<?php
/**
 * 게시글 관련 모듈
 * @author gabia
 * @since version 1.0 - 2012-11-26
 */
class Boardbulkorder extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_data = 'fm_boardbulkorder';
		$this->goods = 'fm_goods';
		$this->upload_path		= $this->Boardmanager->board_data_dir.BOARDID.'/';
		$this->upload_src		= $this->Boardmanager->board_data_src.BOARDID.'/';
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

		if(!empty($sc['category']))
		{
			$sc['category'] = trim(addslashes(str_replace(' ','',$sc['category'])));
			$sqlWhereClause .= " and ( REPLACE(category,' ','') like '%{$sc['category']}%' ) ";
		}

		if(!empty($sc['search_text']))
		{
			$sqlWhereClause .= ' and ( ';
			$sqlWhereClause .= ' company like "%'.$sc['search_text'].'%" or subject like "%'.$sc['search_text'].'%" or name like "%'.$sc['search_text'].'%"  or contents like "%'.$sc['search_text'].'%"  or mid like "%'.$sc['search_text'].'%"  ';
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
			$whereis = ' ( goods_name like "%'.$sc['search_text'].'%" or summary like "%'.$sc['search_text'].'%"  or contents like "%'.$sc['search_text'].'%"  ) ';//
			$sql = "select goods_seq  from ".$this->goods." where ".$whereis;
			$query = $this->db->query($sql);
			foreach($query->result_array() as $data){
				$arrNo[] = $data['goods_seq'];
			}
			$wheregoods = (isset($arrNo)) ? " or goods_seq in ('".implode("','",$arrNo)."')" : '';
			return $wheregoods;
		}else{
			return '';
		}
	}


	// 게시물총건수
	public function get_item_total_count($sc)
	{
		$cnt_query = 'select count(*) as cnt from '.$this->table_data;

		//프론트 공지만노출여부
		if( defined('__ADMIN__') != true ) {
			$cnt_query .= " where onlynotice != '1' "; 
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
							$inputBox .= '<input type="text" name="label['.$data['bulkorderform_seq'].'][value][]" class=" line text_'.$data['bulkorderform_seq'].'" id="txtlabel_'.$data['bulkorderform_seq'].'" value="'.$label_value.'"  '.$size.' style="width:100%;border:1px solid #dbdbdb; margin:1px 0; padding:2px;">';
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
					$labelCount = count($labelArray)-1;

					for ($j=0; $j<$labelCount; $j++) {

						if (is_array($msdata[0])) {
							$checked = ($labelArray[$j] == $msdata[0]['label_value']) ? "checked" : "";
						}
						if ($j > 0) $inputBox .= " ";
						if($showtype == 'view'){
							if($checked ){
								$inputBox .= $labelArray[$j];
							}
						}else{
							$inputBox .= '<input type="radio" name="label['.$data['bulkorderform_seq'].'][value][]" class="null" value="'. $labelArray[$j] .'" '. $checked .'>'. $labelArray[$j];
						}
					}
				break;
			}

		return $inputBox;
	}

}
/* End of file boardbulkorder.php */
/* Location: ./app/models/boardbulkorder */