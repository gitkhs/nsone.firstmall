<?php
/**
 * 회원 > SNS회원 관련 모듈
 * @author gabia
 * @since version 1.0 -2012-08-26
 */
class Snsmember extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_snsmb = 'fm_membersns';
		if(!empty($_GET['seq'])) {//
			$this->seq = $_GET['seq'];
		}
	}

	/*
	 * SNS회원 관리
	 * @param
	*/
	public function snsmb_list($sc) {

		$sql = "select 
					*
					,(case when rute='facebook' then '페이스북' 
							when rute='twitter' then '트위터'
							when rute='cyworld' then '싸이월드'
							when rute='me2day' then '미투데이'
							when rute='naver' then '네이버'
							when rute='kakao' then '카카오'
							when rute='daum' then '다음'
							when rute='google' then '구글'
							else '' 
					end) as rute_nm
				from ".$this->table_snsmb." where 1 ". $sc['whereis'];
		if ( $sc['sns_f'] ) {
			$sql .= " and sns_f = '".$sc['sns_f']."' ";
		}
		$sql .= " and rute != ''";
		$sql .=" order by seq desc ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	public function snstype_name($sc){

		switch($sc){
			case "facebook": $nm = "페이스북"; break;
			case "twitter": $nm = "트위터"; break;
			case "cyworld": $nm = "싸이월드"; break;
			case "me2day": $nm = "미투데이"; break;
			case "naver": $nm = "네이버"; break;
			case "kakao": $nm = "카카오"; break;
			case "daum": $nm = "다음"; break;
			case "google": $nm = "구글"; break;
			default: $nm = ""; break;
		}

		return $nm;
	}

	/*
	 * SNS회원 관리
	 * @param
	*/
	public function snsmb_list_search($sc) {

		$sql = "select * from ".$this->table_snsmb." where 1 ";

		if ( $sc['sns_f'] ) {
			$sql .= " and sns_f = '".$sc['sns_f']."' ";
		}

		if(!empty($sc['search_text']))
		{
			$sql .= ' and ( sns_f  like "%'.$sc['search_text'].'%" or user_name  like "%'.$sc['search_text'].'%" ) ';
		}

		$sql .=" order by seq desc ";
		$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}

	// SNS회원 총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select seq from '.$this->table_snsmb.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * SNS회원 정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:' * ';
		$sql = "select ".$sc['select']." from  ".$this->table_snsmb."  where 1 ". $sc['whereis'];
		if ( $sc['sns_f'] ) {
			$sql .= " and sns_f = '".$sc['sns_f']."' ";
		}
		$sql .=" order by seq desc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * SNS회원 정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:' * ';
		$sql = "select ".$sc['select']." from  ".$this->table_snsmb."  where 1 ". $sc['whereis'];
		if ( $sc['sns_f'] ) {
			$sql .= " and sns_f = '".$sc['sns_f']."' ";
		}
		$sql .=" order by seq desc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * SNS회원 생성
	 * @param
	*/
	public function snsmb_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_snsmb));
		$result = $this->db->insert($this->table_snsmb, $data);
		return $this->db->insert_id();
	}

	/*
	 * SNS회원 정보
	 * @param
	*/
	public function get_data_optimize() {
		$sql = "optimize table ".$this->table_snsmb;
		$this->db->query($sql);
	}

	/*
	 * SNS회원 수정
	 * @param
	*/
	public function snsmb_modify($params) {
		if(empty($params['sns_f']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_snsmb));
		$result = $this->db->update($this->table_snsmb, $data,array('sns_f'=>$params['sns_f']));
		return $result;
	}


	/*
	 * SNS회원 삭제
	 * @param
	*/
	public function snsmb_delete($sns_f,$rute) {
		if(empty($sns_f))return false;
		$result = $this->db->delete($this->table_snsmb, array('sns_f' => $sns_f, 'rute'=> $rute));
		return $result;
	}

}