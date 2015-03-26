<?php
class bigdatamodel extends CI_Model {
	public function __construct(){

		$this->load->helper('readurl');
		$this->config_system	= ($this->config_system) ? $this->config_system : config_load('system');
	}

	// 빅데이터 집합군 배열
	public function get_kind_array(){
		$kind_array	= array(	'order'		=> '구매한', 
								'view'		=> '본', 
								'review'	=> '리뷰를 쓴', 
								'cart'		=> '장바구니에 담은', 
								'wish'		=> '위시리스트에 담은', 
								'like'		=> '좋아요한');
		return $kind_array;
	}

	// 빅데이터 회원 추출 ( 회원은 기준 데이터로 사용될 데이터로 limit를 주지 않는다. )
	public function get_member_seq($sc){
		$param['shopSno']		= $this->config_system['shopSno'];
		$param['month']			= $sc['src_month'];
		$param['kind']			= $sc['src_kind'];
		$param['goods_seq']		= $sc['goods_seq'];

		$url		= $this->config_system['statistics_url'];
		$url_arr	= parse_url($url);
		if	($url_arr['host']){
			$url		= 'http://' . $url_arr['host'] . '/get_members.php';
			$data		= readurl($url, $param, false, 1);
			if	(!$data)	return false;
			$result	= explode(',', $data);

			return $result;
		}else{
			return false;
		}
	}

	// 빅데이터 상품 추출
	public function get_goods_seq($sc, $limit = ''){

		$param['shopSno']		= $this->config_system['shopSno'];
		$param['limit']			= $limit;
		$param['month']			= $sc['src_month'];
		$param['kind']			= $sc['src_kind'];
		$param['members']		= $sc['members'];
		for ($i = 1; $i <= 4; $i++)
			if	($sc['category'.$i])
				$category_code	= $sc['category'.$i];
		for ($i = 1; $i <= 4; $i++)
			if	($sc['brands'.$i])
				$brand_code	= $sc['brands'.$i];
		for ($i = 1; $i <= 4; $i++)
			if	($sc['location'.$i])
				$location_code	= $sc['location'.$i];

		$param['category_code']	= $category_code;
		$param['brand_code']	= $brand_code;
		$param['location_code']	= $location_code;

		$url		= $this->config_system['statistics_url'];
		$url_arr	= parse_url($url);
		if	($url_arr['host']){
			$url		= 'http://' . $url_arr['host'] . '/get_goods.php';
			$data	= readurl($url, $param, false, 1);
			if	(!$data)	return false;
			$result	= explode(',', $data);

			return $result;
		}else{
			return false;
		}
	}

	public function get_bigdata_goods_display($count_w = 5, $goods_arr = array()){

		$this->load->model('goodsdisplay');

		$goodsBigdataDisplayHTML	= '<div style="text-align:center;line-height:350px;">데이터가 없습니다.</div>';

		if	(is_array($goods_arr) && count($goods_arr) > 0){
			$sc['src_seq']			= $goods_arr;
			$list					= $this->goodsmodel->goods_list($sc);

			if	(!$this->bigdata_display)
				$this->bigdata_display	= $this->goodsdisplay->get_design_display_kind('bigdata');

			$display				= $this->bigdata_display;
			$display_key			= $this->goodsdisplay->make_display_key();
			$class					= 'designGoodsBigdataDisplay';
			$designElement			= 'goodsBigdataDisplay';
			if	($display['style'] == 'rolling_h' && $this->mobileMode){
				$class								= 'designDisplay';
				$designElement						= 'display';
				$display['platform']				= 'mobile';
				$display['style']					= 'newswipe';
				// 구버전 스킨용 예외처리
				if		(!file_exists(ROOTPATH.'data/'.$this->skin.'/_modules/display/goods_display_mobile_newswipe.html'))	$display['style']	= 'lattice_a';
				$display['count_h']					= 1;
				if	(!$display['navigation_paging_style'])
					$display['navigation_paging_style']	= 'paging_style_1';
			}

			// design display
			$this->goodsdisplay->set('title',					$display['title']);
			$this->goodsdisplay->set('platform',				$display['platform']);
			$this->goodsdisplay->set('style',					$display['style']);
			$this->goodsdisplay->set('perpage',					$perpage);
			$this->goodsdisplay->set('count_w',					$count_w);
			$this->goodsdisplay->set('count_w_lattice_b',		$count_w);
			$this->goodsdisplay->set('kind',					$display['kind']);
			$this->goodsdisplay->set('navigation_paging_style',	$display['navigation_paging_style']);
			$this->goodsdisplay->set('goods_video_type',		$display['goods_video_type']);
			$this->goodsdisplay->set('videosize_w',				$display['videosize_w']);
			$this->goodsdisplay->set('videosize_h',				$display['videosize_h']);
			if($perpage){
				$this->goodsdisplay->set('count_h',				ceil($perpage/$count_w));
			}else{
				$this->goodsdisplay->set('count_h',				$display['count_h']);
			}
			$this->goodsdisplay->set('image_decorations',		$display['image_decorations']);
			$this->goodsdisplay->set('image_size',				$display['image_size']);
			$this->goodsdisplay->set('text_align',				$display['text_align']);
			$this->goodsdisplay->set('info_settings',			$display['info_settings']);
			$this->goodsdisplay->set('display_key',				$display_key);
			$this->goodsdisplay->set('displayGoodsList',		$list['record']);
			$this->goodsdisplay->set('displayTabsList',			array($list));
			$this->goodsdisplay->set('APP_USE',					$this->__APP_USE__);
			$this->goodsdisplay->set('tab_design_type',			$display['tab_design_type']);
			$this->goodsdisplay->set('is_bigdata_display',		'y');

			$goodsBigdataDisplayHTML	= '';
			if($display['platform']=='mobile' && $display['style']=='newswipe'){
				$goodsBigdataDisplayHTML	= '<script type="text/javascript" src="/app/javascript/plugin/custom-mobile-pagination.js"></script>';
			}
			$goodsBigdataDisplayHTML		.= '<div id="'.$display_key.'" class="'.$class.'" designElement="'.$designElement.'" templatePath="'.$template_path.'" displaySeq="'.$display['display_seq'].'" perpage="'.$perpage.'" displayStyle="'.$display['style'].'">';
			$goodsBigdataDisplayHTML		.= $this->goodsdisplay->print_(true);
			$goodsBigdataDisplayHTML		.= '</div>';
		}

		return $goodsBigdataDisplayHTML;
	}

	// 현재 상품 정보에서 체크
	public function chk_goods_seq($goods_seq, $sc){
		if	(is_array($goods_seq) && count($goods_seq) > 0){
			$sql	= "select goods_seq from fm_goods where goods_seq in ('".implode("', '", $goods_seq)."') ";

			// 상태검색
			if	(is_array($sc['goods_status']) && count($sc['goods_status']) > 0){
				$sql	.= " and goods_status in ('".implode("', '", $sc['goods_status'])."') ";
			}elseif	($sc['goods_status']){
				$sql	.= " and goods_status = '".$sc['goods_status']."' ";
			}

			// 노출여부검색
			if	(is_array($sc['goods_view']) && count($sc['goods_view']) > 0){
				$sql	.= " and goods_view in ('".implode("', '", $sc['goods_view'])."') ";
			}elseif	($sc['goods_view']){
				$sql	.= " and goods_view = '".$sc['goods_view']."' ";
			}

			$query	= $this->db->query($sql);
			$result	= $query->result_array();
			if	($result)foreach($result as $k => $goods){
				$goods_arr[]	= $goods['goods_seq'];
			}

			// 기존 배열과 비교
			foreach($goods_seq as $k => $seq){
				if	(in_array($seq, $goods_arr))	$return[]	= $seq;
			}

			return $return;
		}else{
			return array();
		}
	}
}

/* End of file bigdatamodel.php */
/* Location: ./app/models/bigdatamodel.php */