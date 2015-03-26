<?php
/**
 * 쿠폰상품그룹 관련 모듈
 * @author gabia
 * @since version 1.0 - 2014.03.20
 */
class Socialgoodsgroupmodel extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->social_goods = 'fm_goods';
		$this->social_goods_group = 'fm_goods_social_group';
	}

	/*
	 * 쿠폰상품그룹관리
	 * @param
	*/
	public function sggroup_list($sc) {

		$sql = "select SQL_CALC_FOUND_ROWS * from ".$this->social_goods_group." where 1";
		if( defined('__SELLERADMIN__') === true ){
			$sql.= ' and provider_seq='.$this->providerInfo['provider_seq'];
		}else{
			//$sql.= ' and provider_seq=1';
			if(!empty($sc['provider_seq']))
			{
				$sql .= ' and  provider_seq = "'.$sc['provider_seq'].'" ';
			}
		}
		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( name like "%'.$sc['search_text'].'%" ) ';
		}

		if(!empty($sc['group_seq'])) $sql.= ' and group_seq='.$sc['group_seq'];

		$sql.=" order by group_seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";

		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		//debug_var($this->db->last_query());

		//총건수
		$sql = "SELECT FOUND_ROWS() as COUNT";
		$query_count = $this->db->query($sql);
		$res_count= $query_count->result_array();
		$data['count'] = $res_count[0]['COUNT'];

		return $data;
	}


	/*
	 * 쿠폰상품그룹관리
	 * @param
	*/
	public function sggroup_list_search($sc) {

		$sql = "select * from ".$this->social_goods_group." ";
		if( defined('__SELLERADMIN__') === true ){
			$sql.= ' and provider_seq='.$this->providerInfo['provider_seq'];
		}else{
			//$sql.= ' and provider_seq=1';
			if(!empty($sc['provider_seq']))
			{
				$sql .= ' and  provider_seq = "'.$sc['provider_seq'].'" ';
			}
		}
		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( name like "%'.$sc['search_text'].'%" ) ';
		}

		$sql .=" order by {$sc['orderby']} {$sc['sort']}";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}

	// 쿠폰상품그룹총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select group_seq from '.$this->social_goods_group;

		if( defined('__SELLERADMIN__') === true ){
			$sql.= ' where provider_seq='.$this->providerInfo['provider_seq'];
		}else{
			//$sql.= ' where provider_seq=1';
			if(!empty($sc['provider_seq']))
			{
				$sql .= ' and  provider_seq = "'.$sc['provider_seq'].'" ';
			}
		}
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 쿠폰상품그룹정보
	 * @param
	*/
	public function get_data($sc) {
		$sql = "select ".$sc['select']." from  ".$this->social_goods_group."  where 1 ". $sc['whereis'];
		if( defined('__SELLERADMIN__') === true ){
			$sql.= ' and provider_seq='.$this->providerInfo['provider_seq'];
		}else{
			//$sql.= ' and provider_seq=1';
			if(!empty($sc['provider_seq']))
			{
				$sql .= ' and  provider_seq = "'.$sc['provider_seq'].'" ';
			}
		}
		if(!empty($sc['group_seq'])) $sql.= ' and group_seq='.$sc['group_seq'];
		$sql .=" order by group_seq desc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 쿠폰상품그룹정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sql = "select ".$sc['select']." from  ".$this->social_goods_group."  where 1 ". $sc['whereis'];
		if( defined('__SELLERADMIN__') === true ){
			$sql.= ' and provider_seq='.$this->providerInfo['provider_seq'];
		}else{
			//$sql.= ' and provider_seq=1';
			if(!empty($sc['provider_seq']))
			{
				$sql .= ' and  provider_seq = "'.$sc['provider_seq'].'" ';
			}
		}
		if(!empty($sc['group_seq'])) $sql.= ' and group_seq='.$sc['group_seq'];
		$sql .=" order by group_seq desc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}


	/*
	 * 쿠폰상품그룹생성
	 * @param
	*/
	public function sggroup_write($params) {
		$result = $this->db->insert($this->social_goods_group, $params);
		return $this->db->insert_id();
	}

	/*
	 * 쿠폰상품그룹정보
	 * @param
	*/
	public function get_data_optimize() {
		$sql = "optimize table ".$this->social_goods_group;
		$this->db->query($sql);
	}

	/*
	 * 쿠폰상품그룹수정
	 * @param
	*/
	public function sggroup_modify($params) {
		if(empty($params['group_seq']))return false;
		$result = $this->db->update($this->social_goods_group, $params,array('group_seq'=>$params['group_seq']));
		return $result;
	}

	/*
	 * 쿠폰상품그룹삭제
	 * @param
	*/
	public function idx_delete($group_seq) {
		if(empty($group_seq))return false;
		$result = $this->db->delete($this->social_goods_group, array('group_seq' => $group_seq));
		return $result;
	}

	public function social_goods_group_html()
	{
		//$this->load->model('providermodel');
		/**
		 * list setting
		**/
		$sc							= $_GET;
		if ($sc['search_text'])
		{
			$sc['search_text'] = trim($sc['search_text']);
			$sc['search_text']= stripslashes(htmlspecialchars($sc['search_text']));
		}
		$sc['orderby']			= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'group_seq desc';
		$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):20;
		$sc['page']			= (!empty($_GET['page'])) ?		intval(($_GET['page'] - 1) * $sc['perpage']):0;

		$data = $this->sggroup_list($sc);//게시글목록
		//debug_var($this->db->last_query());

		// 페이징 처리 위한 변수 셋팅
		$page =  get_current_page($sc);
		$pagecount =  get_page_count($sc, $data['count']);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']		= @ceil($sc['searchcount']/ $sc['perpage']);

		$html = '';
		$html .= '<tr >';
		$i = 0;$cellnum = 5;
		foreach($data['result'] as $datarow){
				if( defined('__SELLERADMIN__') === true ){
				}else{
					if($_GET['type'] == 'list' ){
						/**
						if($datarow['provider_seq']>1){
							$provider = $this->providermodel->get_provider_one($datarow['provider_seq']);
							$provider_name = $provider['provider_name'];
						}else{
							$provider_name = '본사';
						}
						**/
					}
				}
				if( $i && ( ($i%$cellnum) == 0) ) $html .= '</tr><tr>';
				$celltd = ($i%$cellnum);
				$selcss = ( $_GET['sel_group_seq'] == $datarow['group_seq'] )?' style="background-color:#f6f6f6;font-weight: bold !important;" ':'';
				$html .= '<td nowrap width="50px" height="20px" class="its-td-align center social_goods_group_sel hand" '.$selcss.' social_goods_group_seq="'.$datarow['group_seq'].'"  social_goods_group_name="'.$datarow['name'].'" >';
				$html .= $datarow['name'];
				if($provider_name) $html .= '<br/><span class="desc">['.$provider_name.']</span>';
				$html .= '</td>';
				$i++;
		}

		if($i>0){
			for($j = $celltd;$j<4;$j++){
				$html .= '<td width="50px" height="20px" class="its-td-align center" > </td>';
			}
		}

		$html .= '</tr>';

		if($i==0){
			if($sc['search_text']){
				$html .= '<tr ><td class="its-td-align center" >"'.$sc['search_text'].'"로(으로) 검색된 쿠폰상품그룹이 없습니다.</td></tr>';
			}else{
				$html .= '<tr ><td class="its-td-align center" >쿠폰상품그룹이 없습니다.</td></tr>';
			}
		}
		if(!empty($html)) {
			$result = array( 'content'=>$html, 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}else{
			$result = array( 'content'=>"", 'searchcount'=>$sc['searchcount'], 'totalcount'=>$sc['totalcount'], 'nowpage'=>(($sc['page']/$sc['perpage'])+1), 'total_page'=>$sc['total_page'], 'page'=>$page, 'pagecount'=>(int)$pagecount);
		}
		return $result;
	}

}
?>