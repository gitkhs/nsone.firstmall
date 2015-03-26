<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class event extends admin_base {

	public function __construct() {
		parent::__construct();
	}

	public function index()
	{
		redirect("/admin/promotion/catalog");
	}

	public function catalog()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('eventmodel');

		$event	= $this->eventmodel->get_event_list();
		$result	= $event['result'];
		$count	= $event['count'];
		$sc		= $event['sc'];

		/* 상품명 getstrcut 자르기 오류로 title 태그 내용 삭제 처리 leewh 2014-12-09 */
		foreach ($result['record'] as $key => $row) {
			if (!empty($row['goods_name'])) {
				$result['record'][$key]['goods_name'] = trim(preg_replace("/<title[^>]*>(.*?)<\/title>/is", "", $row['goods_name']));
			}
		}

		$this->template->assign(array('list'=>$result['record']));
		$this->template->assign($result);
		$this->template->assign(array(
			'count'=>$count,
			'sc'=>$sc
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function regist(){
		$this->load->model('categorymodel');
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('goodsdisplay');
		$this->load->model('eventmodel');

		$event_seq = $_GET['event_seq'];

		$query = $this->db->query("select * ,
		if(CURRENT_TIMESTAMP() between start_date and end_date,'진행 중',if(end_date < CURRENT_TIMESTAMP(),'종료','시작 전')) as status
		from fm_event where event_seq=?",$event_seq);
		$data = $query->row_array();
		$data['start_time']			= strtotime($data['start_date']);
		$data['end_time']			= strtotime($data['end_date']);
		$data['app_start_hour']		= substr($data['app_start_time'], 0, 2);
		$data['app_start_minute']	= substr($data['app_start_time'], 2, 2);
		$data['app_end_hour']		= substr($data['app_end_time'], 0, 2);
		$data['app_end_minute']		= substr($data['app_end_time'], 2, 2);

		$query = "select * from fm_event_benefits where event_seq=? order by event_benefits_seq asc";
		$query = $this->db->query($query,$event_seq);
		foreach($query->result_array() as $row){
			$query2 = "select c.*,g.goods_name,
			(select image from fm_goods_image where goods_seq=c.goods_seq and image_type='thumbCart' order by cut_number limit 1) image,
			(select price from fm_goods_option where goods_seq=c.goods_seq and default_option='y') price from
			fm_event_choice c left join fm_goods g on c.goods_seq and g.goods_seq = c.goods_seq where event_benefits_seq=?
			order by event_choice_seq asc";
			$query2 = $this->db->query($query2,$row['event_benefits_seq']);
			foreach($query2->result_array() as $row2){
				if($row2['category_code']){
					$row2['category_name'] = $this->categorymodel->get_category_name($row2['category_code']);
				}
				$row[$row2['choice_type']][] = $row2;
			}
			$data['data_choice'][] = $row;
		}

		/* 샘플 상품 정보 */
		$sampleGoodsInfo = array(
			'goods_name' => '샘플 상품',
			'price' => '19800',
			'consumer_price' => '24800',
		);

		$styles = array();
		foreach($this->goodsdisplay->styles as $k=>$v)
		{
			if(in_array($k,array('lattice_a','lattice_b','list'))){
				$styles[$k]=$v;
			}
		}

		/* 리스트 이미지 꾸미기 값 파싱 */
		$list_image_decorations = $this->goodsdisplay->decode_image_decorations($data['list_image_decorations']);

		if	($event_seq){
			$stats	= $this->eventmodel->get_event_order_result($event_seq);
		}

		$this->template->assign(array(
			'stats'							=> $stats,
			'styles'						=> $styles,
			'orders'						=> $this->goodsdisplay->orders,
			'goodsImageSizes'				=> config_load('goodsImageSize'),
			'list_image_decorations'		=> $list_image_decorations,
			'sampleGoodsInfo'				=> $sampleGoodsInfo
		));		

		$this->template->assign("event",$data);
		$this->template->assign(array('snsevent' => 'event'));

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		switch($reserves['reserve_select']){
			case "year":
				$reserves['reservetitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['reserve_year']));
				break;
			case "direct":
				$reserves['reservetitle'] = $reserves['reserve_direct'].'개월';
				break;
			default:
				$reserves['reservetitle'] = '제한하지 않음';
				break;
		}

		switch($reserves['point_select']){
			case "year":
				$reserves['pointtitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['point_year']));
				break;
			case "direct":
				$reserves['pointtitle'] = $reserves['point_direct'].'개월';
				break;
			default:
				$reserves['pointtitle'] = '제한하지 않음';
				break;
		}
		$this->template->assign($reserves);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}



	public function gift_catalog()
	{

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('gift_view');
		if(!$auth){
			$callback = "history.go(-1);";
			$this->template->assign(array('auth_msg'=>$this->auth_msg,'callback'=>$callback));
			$this->template->define(array('denined'=>$this->skin.'/common/denined.html'));
			$this->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->admin_menu();
		$this->tempate_modules();

		$sc=array();
		$sc['sort']				= $_GET['sort'] ? $_GET['sort'] : 'evt.gift_seq desc';
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['keyword']			= $_GET['keyword'];
		$sc['perpage']			= $_GET['perpage'] ? $_GET['perpage'] : 10;

		if( count($_GET) == 0 ){
			$_GET['event_status'] = array('before','ing','end');
		}

		$where = array();

		// 검색어
		if( $_GET['keyword'] ){
			$where[] = "
				CONCAT(
					title
				) LIKE '%" . addslashes($_GET['keyword']) . "%'
			";
		}

		// 일자검색
		if( $_GET['date'] ){
			$date_field = $_GET['date'];
			if($_GET['sdate'] && $_GET['edate']) $where[] = "date_format({$date_field},'%Y-%m-%d') between '{$_GET['sdate']}' and '{$_GET['edate']}'";
			else if($_GET['sdate']) $where[] = "'{$_GET['sdate']}' < date_format({$date_field},'%Y-%m-%d')";
			else if($_GET['edate']) $where[] = "date_format({$date_field},'%Y-%m-%d') < '{$_GET['edate']}'";
		}

		// 이벤트진행상태
		if( $_GET['event_status'] ){
			$arr = array();
			foreach($_GET['event_status'] as $key => $data){

				switch($data){
					case "before":
						$arr[] = "start_date > current_date()";
					break;
					case "ing":
						$arr[] = "current_date() between start_date and end_date";
					break;
					case "end":
						$arr[] = "end_date < current_date()";
					break;
				}
			}
			if($arr) $where[] = "(".implode(' OR ',$arr).")";
		}

		if( $_GET['gift_gb'] ){
			$arr2 = array();
			foreach($_GET['gift_gb'] as $key => $data){

				switch($data){
					case "order":
						$arr2[] = "gift_gb = 'order'";
					break;
					case "buy":
						$arr2[] = "gift_gb = 'buy'";
					break;
				}
			}
			if($arr2) $where[] = "(".implode(' OR ',$arr2).")";
		}


		$sqlWhereClause = $where ? " where ".implode(' AND ',$where) : "";

		$query = "SELECT SQL_CALC_FOUND_ROWS *,
		if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'종료','시작 전')) as status
		FROM fm_gift as evt
		{$sqlWhereClause}
		ORDER BY {$sc['sort']}";

		$result = select_page($sc['perpage'],$sc['page'],10,$query,array());

		$count['total']	 = get_rows('fm_gift');

		$today = date("Y-m-d");
		$sql = "SELECT count(gift_seq) as cnt FROM fm_gift WHERE start_date <= '{$today}' AND end_date >= '{$today}'";
		$query = $this->db->query($sql);
		$temp = $query->row_array();
		$count['ing'] = $temp['cnt'];
		$sql = "SELECT count(gift_seq) as cnt FROM fm_gift WHERE end_date < '{$today}'";
		$query = $this->db->query($sql);
		$temp = $query->row_array();
		$count['end'] = $temp['cnt'];

		$this->template->assign($result);
		$this->template->assign(array(
			'count'=>$count,
			'sc'=>$sc
		));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}



	public function gift_regist(){
		$this->load->model('categorymodel');
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('goodsdisplay');

		$event_seq = $_GET['event_seq'];
		$query = $this->db->query("select * ,
		if(current_date() between start_date and end_date,'진행 중',if(end_date < current_date(),'종료','시작 전')) as status
		from fm_gift where gift_seq=?",$event_seq);
		$data = $query->row_array();


		$gift_gb = $_GET['gb'];
		if($data['gift_gb']){
			$gift_gb = $data['gift_gb'];
		}
		$this->template->assign("gift_gb", $gift_gb);


		if($data['goods_rule']=='goods'){
			$sql = "SELECT
						distinct A.*, B.*
					FROM
						fm_gift_choice A
						LEFT JOIN
						(SELECT
							g.goods_seq, g.goods_name, o.price
						FROM
							fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y') B ON A.goods_seq = B.goods_seq
					WHERE
						A.gift_seq = '{$data['gift_seq']}'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$limit_goods[] = $row;
			}
			if($limit_goods) $this->template->assign('issuegoods',$limit_goods);
		}else if($data['goods_rule']=='category'){
			###
			$this->load->model('categorymodel');
			$this->db->where('gift_seq', $data['gift_seq']);
			$query = $this->db->get('fm_gift_choice');
			foreach ($query->result_array() as $row){
				$row['title'] =  $this->categorymodel->get_category_name($row['category_code']);
				$limit_cate[] = $row;
			}
			if($limit_cate) $this->template->assign('issuecategorys',$limit_cate);
		}


		###
		$sql = "select * from fm_gift_benefit where gift_seq = '{$data['gift_seq']}'";
		$query = $this->db->query($sql);
		if($data['gift_rule']=='default'){
			$temp = $query->result_array();
			$this->template->assign('default',$temp[0]);
			$search = str_replace("|", ",", $temp[0]['gift_goods_seq']);
			if($search == ""){
				$search = "''";
			}
			$sql = "SELECT
						g.goods_seq, g.goods_name, o.price
					FROM
						fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y'
					WHERE
						g.goods_seq in ($search)";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$defaultGifts[] = $row;
			}
			if($defaultGifts) $this->template->assign('defaultGifts',$defaultGifts);
		}else if($data['gift_rule']=='price'){
			$cnt = 1;
			foreach ($query->result_array() as $v){
				$search = str_replace("|", ",", $v['gift_goods_seq']);
				if($search == ""){
					$search = "''";
				}
				$sql = "SELECT
							g.goods_seq, g.goods_name, o.price
						FROM
							fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y'
						WHERE
							g.goods_seq in ($search)";
				$query = $this->db->query($sql);
				unset($temps);
				foreach ($query->result_array() as $row){
					$temps[] = $row;
				}
				$v['gifts']		= $temps;
				$v['num']		= $cnt;
				$priceLoop[]	= $v;
				$cnt++;
			}
			$this->template->assign('priceLoop',$priceLoop);
		}else if($data['gift_rule']=='quantity'){
			foreach ($query->result_array() as $v){
				$gift_goods_seq = $v['gift_goods_seq'];
				$qtyLoop[] = $v;
			}
			$this->template->assign('qtyLoop',$qtyLoop);

			$search = str_replace("|", ",", $gift_goods_seq);
			if($search == ""){
				$search = "''";
			}

			$sql = "SELECT
						g.goods_seq, g.goods_name, o.price
					FROM
						fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y'
					WHERE
						g.goods_seq in ($search)";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$qtyGifts[] = $row;
			}
			if($qtyGifts) $this->template->assign('qtyGifts',$qtyGifts);
		}else if($data['gift_rule']=='lot'){
			foreach ($query->result_array() as $v){
				$gift_goods_seq = $v['gift_goods_seq'];
				$lotLoop[] = $v;
			}
			$this->template->assign('lotLoop',$lotLoop);

			$search = str_replace("|", ",", $gift_goods_seq);
			if($search == ""){
				$search = "''";
			}

			$sql = "SELECT
						g.goods_seq, g.goods_name, o.price
					FROM
						fm_goods g LEFT JOIN fm_goods_option o ON g.goods_seq = o.goods_seq AND o.default_option = 'y'
					WHERE
						g.goods_seq in ($search)";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$lotGifts[] = $row;
			}
			if($lotGifts) $this->template->assign('lotGifts',$lotGifts);
		}


		/* 샘플 상품 정보 */
		$sampleGoodsInfo = array(
			'goods_name' => '샘플 상품',
			'price' => '19800',
			'consumer_price' => '24800',
		);

		$styles = array();
		foreach($this->goodsdisplay->styles as $k=>$v)
		{
			if(in_array($k,array('lattice_a','lattice_b','list'))){
				$styles[$k]=$v;
			}
		}

		/* 리스트 이미지 꾸미기 값 파싱 */
		$list_image_decorations = $this->goodsdisplay->decode_image_decorations($data['list_image_decorations']);


		$this->template->assign(array(
			'styles'						=> $styles,
			'orders'						=> $this->goodsdisplay->orders,
			'goodsImageSizes'				=> config_load('goodsImageSize'),
			'list_image_decorations'		=> $list_image_decorations,
			'sampleGoodsInfo'				=> $sampleGoodsInfo
		));

		$this->template->assign("event",$data);
		$this->template->assign(array('snsevent' => 'event'));

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function soloregist(){
		$this->load->model('categorymodel');
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('goodsdisplay');
		$this->load->model('eventmodel');

		$event_seq = $_GET['event_seq'];

		$query = $this->db->query("select * ,
		if(CURRENT_TIMESTAMP() between start_date and end_date,'진행 중',if(end_date < CURRENT_TIMESTAMP(),'종료','시작 전')) as status
		from fm_event where event_seq=?",$event_seq);
		$data = $query->row_array();

		// 날짜를 강제로 0000-00-00 00 으로 변경 :: 2014-10-22 lwh
		if($data['start_date'] != '0000-00-00 00:00:00' && $data['end_date'] != '0000-00-00 00:00:00'){
			$data['start_time']			= strtotime($data['start_date']);
			$data['end_time']			= strtotime($data['end_date']);
			$data['app_start_hour']		= substr($data['app_start_time'], 0, 2);
			$data['app_start_minute']	= substr($data['app_start_time'], 2, 2);
			$data['app_end_hour']		= substr($data['app_end_time'], 0, 2);
			$data['app_end_minute']		= substr($data['app_end_time'], 2, 2);
		}

		$query = "select * from fm_event_benefits where event_seq=? order by event_benefits_seq asc";
		$query = $this->db->query($query,$event_seq);
		foreach($query->result_array() as $row){
			$query2 = "select c.*,g.goods_name,
			(select image from fm_goods_image where goods_seq=c.goods_seq and image_type='thumbCart' order by cut_number limit 1) image,
			(select price from fm_goods_option where goods_seq=c.goods_seq and default_option='y') price from
			fm_event_choice c left join fm_goods g on c.goods_seq and g.goods_seq = c.goods_seq where event_benefits_seq=?
			order by event_choice_seq asc";
			$query2 = $this->db->query($query2,$row['event_benefits_seq']);
			foreach($query2->result_array() as $row2){
				if($row2['category_code']){
					$row2['category_name'] = $this->categorymodel->get_category_name($row2['category_code']);
				}
				$row[$row2['choice_type']][] = $row2;
			}
			$data['data_choice'][] = $row;
		}
		if	(!$data['data_choice'])	$data['data_choice'][0]	= array();

		/* 샘플 상품 정보 */
		$sampleGoodsInfo = array(
			'goods_name' => '샘플 상품',
			'price' => '19800',
			'consumer_price' => '24800',
		);

		$styles = array();
		foreach($this->goodsdisplay->styles as $k=>$v)
		{
			if(in_array($k,array('lattice_a','lattice_b','list'))){
				$styles[$k]=$v;
			}
		}

		/* 리스트 이미지 꾸미기 값 파싱 */
		$list_image_decorations = $this->goodsdisplay->decode_image_decorations($data['list_image_decorations']);

		if	($event_seq){
			$stats	= $this->eventmodel->get_event_order_result($event_seq);
		}


		$this->template->assign(array(
			'stats'							=> $stats,
			'styles'						=> $styles,
			'orders'						=> $this->goodsdisplay->orders,
			'goodsImageSizes'				=> config_load('goodsImageSize'),
			'list_image_decorations'		=> $list_image_decorations,
			'sampleGoodsInfo'				=> $sampleGoodsInfo
		));

		if($data['title_contents'] == "" && $event_seq == ""){
			$data['title_contents'] = "<p><span style='color: rgb(127, 127, 127); font-family: Verdana; font-size: 25pt; font-weight: bold; mso-ascii-font-family: Verdana; mso-fareast-font-family: Verdana; mso-bidi-font-family: Verdana; mso-color-index: 1; mso-font-kerning: 12.0pt; language: en-US; mso-style-textfill-type: solid; mso-style-textfill-fill-themecolor: text1; mso-style-textfill-fill-color: #7F7F7F; mso-style-textfill-fill-alpha: 100.0%; mso-style-textfill-fill-colortransforms: \"lumm=50000 lumo=50000\";'>ONE DAY
SPECIAL PRICE</span><br></p>";
		}

		$this->template->assign("event",$data);
		$this->template->assign(array('snsevent' => 'event'));

		$reserves = ($this->reserves)?$this->reserves:config_load('reserve');
		switch($reserves['reserve_select']){
			case "year":
				$reserves['reservetitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['reserve_year']));
				break;
			case "direct":
				$reserves['reservetitle'] = $reserves['reserve_direct'].'개월';
				break;
			default:
				$reserves['reservetitle'] = '제한하지 않음';
				break;
		}

		switch($reserves['point_select']){
			case "year":
				$reserves['pointtitle'] = date("Y년 m월 d일", mktime(0,0,0,12, 31, date("Y")+$reserves['point_year']));
				break;
			case "direct":
				$reserves['pointtitle'] = $reserves['point_direct'].'개월';
				break;
			default:
				$reserves['pointtitle'] = '제한하지 않음';
				break;
		}
		$this->template->assign($reserves);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
}

/* End of file event.php */
/* Location: ./app/controllers/admin/event.php */