<?php
/**
 *좋아요상품 관련 모듈
 * @author gabia
 * @since version 1.0 - 2012-10-05
 */
class Goodsfblike extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->table_fblike = 'fm_goods_fblike';
	}

	/*
	 * 좋아요상품관리
	 * @param
	*/
	public function fblike_list($sc)
	{
		$sql = "select * from ".$this->table_fblike." where 1";
		$sql .=" order by like_seq desc";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}


	/*
	 * 좋아요상품관리
	 * @param
	*/
	public function fblike_list_search($sc)
	{
		$sql = "select * from ".$this->table_fblike." where 1 ". $sc['whereis'];
		$sql .=" order by like_seq desc";
		if	($sc['page'] && $sc['perpage'])
			$sql .=" limit {$sc['page']}, {$sc['perpage']} ";
		$query = $this->db->query($sql);
		$data['result'] = $query->result_array();
		return $data;
	}

	// 좋아요상품총건수
	public function get_item_total_count($sc)
	{
		$sql = 'select like_seq from '.$this->table_fblike.' where 1';
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 좋아요상품정보
	 * @param
	*/
	public function get_data($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";

		$sql = "select ".$sc['select']." from  ".$this->table_fblike."  where 1 ". $sc['whereis'];
		$sql .=" order by like_seq desc ";
		$query = $this->db->query($sql);
		$data = $query->row_array();
		return $data;
	}

	/*
	 * 좋아요상품정보
	 * @param
	*/
	public function get_data_numrow($sc) {
		$sc['select'] = ($sc['select'])?$sc['select']:" * ";

		$sql = "select ".$sc['select']." from  ".$this->table_fblike."  where 1 ". $sc['whereis'];
		$sql .=" order by like_seq desc ";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/*
	 * 좋아요상품생성
	 * @param
	*/
	public function fblike_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_fblike));
		$result = $this->db->insert($this->table_fblike, $data);
		return $this->db->insert_id();
	}

	/*
	 * 좋아요상품정보
	 * @param
	*/
	public function get_data_optimize() {
		$sql = "optimize table ".$this->table_fblike;
		$this->db->query($sql);
	}

	/*
	 * 좋아요상품수정
	 * @param
	*/
	public function fblike_modify($params) {
		if(empty($params['like_seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_fblike));
		$result = $this->db->update($this->table_fblike, $data,array('like_seq' => $params['like_seq']));
		return $result;
	}

	/*
	 * 좋아요상품삭제
	 * @param
	*/
	public function fblike_delete($like_seq) {
		if(empty($like_seq))return false;
		$result = $this->db->delete($this->table_fblike, array('like_seq' => $like_seq));
		return $result;
	}

	/*
	 * 좋아요상품 저장하기
	  * 장바구니 좋아요여부 저장
	*/
	public function set_fblike_goods($mode,$product_url,$fb_action_id=null) {
		$this->load->model('membermodel');

		$ss_fblike_name = 'goods_fblike';
		$goodsfblikess = $this->session->userdata($ss_fblike_name);
		$goodseq = @end(explode("=",$product_url));
		$session_id = $this->session->userdata('session_id');//like 한경우 DB 화처리

		if( $this->session->userdata('fbuser') ) {
			$sns_id = $this->session->userdata('fbuser');
		}elseif(get_cookie('fbuser')){
			$sns_id = get_cookie('fbuser');
		}

		if($this->userInfo['member_seq']){
			if( !$sns_id ) {
				$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보
				if($this->mdata['sns_f']) $sns_id = $this->mdata['sns_f'];
			}
		}
		$sc['select']  = " like_seq, member_seq, sns_id, fb_action_id, session_id ";
		$whereis = " and goods_seq='".$goodseq."' ".$addwhereis;
		$sc['whereis'] = $whereis;

		$ckfblike = $this->getgoodsfblike($goodseq, $sns_id, $session_id);
		//debug_var($this->db->last_query());

		if($mode == 'unlike') {//좋아요취소시

			if ( strstr($goodsfblikess,'['.$product_url.']') && $goodsfblikess ) {
				$goodsfblikessadd = str_replace('['.$product_url.']','',$goodsfblikess);
				$this->session->set_userdata($ss_fblike_name, $goodsfblikessadd);
				$goodsfblikess = $this->session->userdata($ss_fblike_name);
			}
			if($ckfblike) $delfblike=$this->fblike_delete($ckfblike['like_seq']);
			$like_seq = $ckfblike['like_seq'];
			//debug_var($this->db->last_query());

			$this->db->where(array('goods_seq'=>$goodseq, 'session_id'=>$session_id));
			$this->db->update('fm_cart', array('fblike'=>'N'));
			//debug_var($this->db->last_query());

		}else{

			if ( ( !strstr($goodsfblikess,'['.$product_url.']') && isset($goodsfblikess)) || empty($goodsfblikess)) {
				$goodsfblikessadd = ($goodsfblikess) ? $goodsfblikess.'['.$product_url.']':'['.$product_url.']';
				$this->session->set_userdata($ss_fblike_name, $goodsfblikessadd);
				$goodsfblikess = $this->session->userdata($ss_fblike_name);
			}

			if($ckfblike) {
				$member_seq	= ($this->userInfo['member_seq'])?$this->userInfo['member_seq']:$ckfblike['member_seq'];
				$sns_id			= ($sns_id)?$sns_id:$ckfblike['sns_id'];
				$fb_action_id	= ($fb_action_id)?$fb_action_id:$ckfblike['fb_action_id'];
				$session_id		= ($session_id)?$session_id:$ckfblike['session_id'];

				$insdata = array(
				'like_seq' => $ckfblike['like_seq'],
				'member_seq' => $member_seq,
				'sns_id' => $sns_id,
				'fb_action_id' => $fb_action_id,
				'session_id' => $session_id,
				'date' => date('Y-m-d H:i:s'),
				'ip' => $this->input->ip_address(),
				'agent' => $_SERVER['HTTP_USER_AGENT']
				);//'goods_seq' => $goodseq,
				$this->fblike_modify($insdata);
				$like_seq = $ckfblike['like_seq'];
			}else{
				$insdata = array(
				'goods_seq' => $goodseq,
				'member_seq' => $this->userInfo['member_seq'],
				'sns_id' => $sns_id,
				'fb_action_id' => $fb_action_id,
				'session_id' => $session_id,
				'date' => date('Y-m-d H:i:s'),
				'ip' => $this->input->ip_address(),
				'agent' => $_SERVER['HTTP_USER_AGENT']
				);
				$like_seq =  $this->fblike_write($insdata);
			}
			//debug_var($this->db->last_query());
			$this->db->where(array('goods_seq'=>$goodseq, 'session_id'=>$session_id));
			$this->db->update('fm_cart', array('fblike'=>'Y'));
			//debug_var($this->db->last_query());
		}
		return $like_seq;
	}

	function getgoodsfblike($goodseq, $sns_id = null, $session_id){
		$this->load->model('membermodel');

		if( !$sns_id ) {
			if( $this->session->userdata('fbuser') ) {
				$sns_id = $this->session->userdata('fbuser');
			}elseif(get_cookie('fbuser')){
				$sns_id = get_cookie('fbuser');
			}else{
				if($this->userInfo['member_seq']){
					if( !$sns_id ) {
						$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보
						if($this->mdata['sns_f']) $sns_id = $this->mdata['sns_f'];
					}
				}
			}
		}

		$addwhereis = " and (session_id='".$session_id."' or sns_id = '".$sns_id."'  or member_seq = '".$this->userInfo['member_seq']."' ) ";
		$sc['select']  = " like_seq, member_seq, sns_id, fb_action_id, session_id ";
		$whereis = " and goods_seq='".$goodseq."' ".$addwhereis;
		$sc['whereis'] = $whereis;
		$ckfblike = $this->get_data($sc);
		return $ckfblike;
	}
}
?>