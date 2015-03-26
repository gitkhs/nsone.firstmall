<?php
class wishmodel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	// 위시리스트에 담기
	public function add($goods_seqs){

		$this->load->model('goodslog');

		foreach($goods_seqs as $goods_seq){
			$query = "select count(*) cnt from fm_goods_wish where goods_seq=? and member_seq=?";
			$query = $this->db->query($query,array($goods_seq,$this->userInfo['member_seq']));
			list($data) = $query->result_array();
			if($data['cnt']==0){
				$query = "insert into fm_goods_wish (`goods_seq`,`member_seq`,`regist_date`) values(?,?,now())";
				$this->db->query($query,array($goods_seq,$this->userInfo['member_seq']));
			}

			/* 상품분석 수집 */
			$this->goodslog->add('wish',$goods_seq);
			/* 개인맞춤형 알림 상세유입로그 */
			$this->load->helper('reservation');
			$curation = array("action_kind"=>"wish","goods_seq"=>$goods_seq);
			curation_log($curation);
		}
	}

	// 위시리스트 가져오기
	public function get_wish($seq){
		$query = "SELECT * FROM fm_goods_wish WHERE wish_seq=?";
		$query = $this->db->query($query,array($seq));
		list($data) = $query->result_array();
		return $data;
	}

	// 위시리스트 목록
	public function get_list($member_seq,$imageType='thumbView',$regdate=null)
	{
		$page				= (!empty($_GET['page']) && $_GET['page']>0) ?		intval($_GET['page']):1;
		$query = "
		SELECT w.wish_seq,
		g.*,
		o.reserve,
		o.consumer_price,
		o.price,
		(select image from fm_goods_image where goods_seq=w.goods_seq order by image_type=? desc, cut_number asc limit 1) as image
		FROM fm_goods_wish w
		inner join fm_goods g on w.goods_seq=g.goods_seq
		inner join fm_goods_option o on (w.goods_seq=o.goods_seq and o.default_option='y')
		WHERE member_seq=?
		AND g.goods_view = 'look' ";

		if($regdate['today_s'] && $regdate['today_e']){
			$query .= " and w.regist_date between '".$regdate['today_s']."' and '".$regdate['today_e']."'";
		}
		$query .= " ORDER BY w.regist_date DESC";
		$result = select_page(12,$page,10,$query,array($imageType,$member_seq));
		$result['page']['querystring'] = get_args_list();

		return $result;
	}

	public function get_wish_count($member_seq)
	{
		$query = "SELECT count(*) cnt
		FROM fm_goods_wish w
		inner join fm_goods g on w.goods_seq=g.goods_seq
		inner join fm_goods_option o on (w.goods_seq=o.goods_seq and o.default_option='y')
		WHERE w.member_seq=?
		AND g.goods_view = 'look'
		ORDER BY w.regist_date DESC";
		$query = $this->db->query($query,array($member_seq));
		$row = $query->result_array();

		return $row[0]['cnt'];


	}

	// 위시리스트 삭제
	public function del($wish_seqs){
		$query = "delete from fm_goods_wish where wish_seq in (".implode(',',$wish_seqs).") and member_seq=?";
		$this->db->query($query,array($this->userInfo['member_seq']));
	}

	// 상품의 위시 여부 확인 2014-01-10 lwh
	public function confirm_wish($goods_seq){
		$query = "select wish_seq from fm_goods_wish where goods_seq=? and member_seq=?";
		$query = $this->db->query($query,array($goods_seq,$this->userInfo['member_seq']));
		$wish_seq = $query->result_array();

		return $wish_seq[0]['wish_seq'];
	}

	/* 우측퀵메뉴 wish 리스트 leewh 2014-06-10 */
	public function get_right_wish_list($member_seq,$page,$limit,$imageType='thumbScroll') {

		$this->load->library('sale');
		$cfg_reserve	= ($this->reserves) ? $this->reserves : config_load('reserve');

		//----> sale library 적용
		$applypage						= 'lately_scroll';
		$param['cal_type']				= 'list';
		$param['reserve_cfg']			= $cfg_reserve;
		$param['member_seq']			= $this->userInfo['member_seq'];
		$param['group_seq']				= $this->userInfo['group_seq'];
		$this->sale->set_init($param);
		$this->sale->preload_set_config($applypage);
		//<---- sale library 적용

		if (!$page) $page = 1;
		$start = ($page-1)*$limit;
		$limit = "LIMIT {$start} , {$limit}";

		$query = "
		SELECT
		w.wish_seq,g.goods_seq,g.sale_seq,g.goods_name,o.price,o.consumer_price,
		(select image from fm_goods_image where goods_seq=w.goods_seq order by image_type=? desc, cut_number asc limit 1) as image
		FROM
		fm_goods_wish w
		inner join fm_goods g on w.goods_seq=g.goods_seq
		inner join fm_goods_option o on (w.goods_seq=o.goods_seq and o.default_option='y')
		WHERE member_seq=?
		AND g.goods_view = 'look'
		ORDER BY w.regist_date DESC {$limit}";
		$query = $this->db->query($query,array($imageType,$member_seq));

		$display_item = array();
		foreach ($query->result_array() as $data) {

			// 해당 상품의 전체 카테고리
			$category	= array();
			$tmp		= $this->goodsmodel->get_goods_category($data['goods_seq']);
			foreach($tmp as $row)	$category[]		= $row['category_code'];

			//----> sale library 적용
			unset($param, $sales);
			$param['option_type']				= 'option';
			$param['consumer_price']			= $data['consumer_price'];
			$param['price']						= $data['price'];
			$param['total_price']				= $data['price'];
			$param['ea']						= 1;
			$param['goods_ea']					= 1;
			$param['category_code']				= $category;
			$param['goods_seq']					= $data['goods_seq'];
			$param['goods']						= $data;
			$this->sale->set_init($param);
			$sales								= $this->sale->calculate_sale_price($applypage);
			$data['sale_price']					= $sales['result_price'];
			$this->sale->reset_init();
			//<---- sale library 적용

			$display_item[] = $data;
		}
		return $display_item;
	}

}
/* End of file wishmodel.php */
/* Location: ./app/models/wishmodel */