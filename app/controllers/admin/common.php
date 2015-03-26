<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class common extends admin_base {

	public function __construct() {
		parent::__construct();
	}

	public function zipcode()
	{
		$this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();
		$loop = "";

		if($_GET['dong']){
			$query = "SELECT * FROM zipcode WHERE DONG LIKE '%".$_GET['dong']."%'";
			$query = $ZIP_DB->query($query);
			foreach ($query->result_array() as $row){
				$row['ADDRESS'] = implode(' ',array($row['SIDO'],$row['GUGUN'],$row['DONG']));
				$row['ADDRESSVIEW'] = implode(' ',array($row['SIDO'],$row['GUGUN'],$row['DONG'],$row['BUNJI']));
				$loop[] = $row;
			}

		}

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign("zipcodeFlag",$_GET['zipcodeFlag']);
		$this->template->assign("dong",$_GET['dong']);
		$this->template->assign("loop",$loop);
		$this->template->print_("tpl");
	}

	/* 쿼리로 직접 엑셀 다운로드 */
	public function DirectExcelDownload(){
		$this->load->helper('download');

		$title		= iconv("utf-8","euc-kr",$_POST['title']);
		$excel_type	= $_POST['excel_type'];

		parse_str($_POST['param'], $_GET);
		
		// 방문통계 유입경로 (통계-방문통계-기본-시간별)
		if($excel_type == 'visitor_referer_table'){
			$_GET['year']	= !empty($_GET['year'])		? $_GET['year']		: date('Y');
			$_GET['month']	= !empty($_GET['month'])	? $_GET['month']	: date('m');
			$_GET['day']	= !empty($_GET['day'])		? $_GET['day']		: date('d');

			$stats_date = $_GET['year'] .'-'. $_GET['month'] .'-'. $_GET['day'];
			$this->db->order_by("count desc");
			$query = $this->db->get_where('fm_stats_visitor_referer',array('stats_date'=>$stats_date));
			$loopData = $query->result_array();

			$contents	= '<table border="1" width="800"><tr>';
			$contents	.= '<td>유입경로</td><td>방문자수</td></tr>';
			if($loopData){
				$refererCountSum	= 0;
				foreach($loopData as $val){
					$refererCountSum	+= $val['count'];
					$referer_url		= ($val['referer']) ? $val['referer'] : '직접입력';
					$contents	.= '<tr>';
					$contents	.= '	<td>'.$referer_url.'</td>';
					$contents	.= '	<td>'.number_format($val['count']).'</font></td>';
					$contents	.= '</tr>';
				}
				$contents	.= '<tr><td>합계</td><td>'.number_format($refererCountSum).'</td></tr>';
			}else{
				$contents	.= '<tr><td colspan="2">데이터가 없습니다.</td></tr>';
			}
		}else{
			echo "[ERR : The wrong approach.]";
			echo "<script>alert('잘못된 접근입니다.');history.back();</script>";
			exit;
		}
		
		$contents = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$contents;
		$fileName = $title."_".date('YmdHis').".xls";

		force_download($fileName, $contents);
	}

	public function divExcelDownload(){
		$this->load->helper('download');

		$title = iconv("utf-8","euc-kr",$_POST['title']);
		$contents = $_POST['contents'];

		// (숫자) 통계숫자 표시하기로 하여 주석처리함 leewh 2015-03-02
		//$contents = preg_replace("/\(([^\)]*)\)/","",$contents); // 값에 들어간 괄호를 제거하기 위한 코드

		$contents = strip_tags($contents,"<table><tr><th><td><style>");
		$contents = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$contents;

		$fileName = "{$title}_".date('YmdHis').".xls";

		force_download($fileName, $contents);
	}

	/* 가비아 출력 패널 (배너,팝업) */
	// 우측패널을 따로 호출하고 있음.(?수정필요?) :: 2014-10-24 lwh
	public function getGabiaPannel(){
		$this->load->helper('readurl');

		$code = $_GET['code'];

		$data = array(
			'service_code'	=> SERVICE_CODE,
			'hosting_code'	=> $this->config_system['service']['hosting_code'],
			'subDomain'		=> $this->config_system['subDomain'],
			'domain'		=> $this->config_system['domain'],
			'hostDomain'	=> $_SERVER['HTTP_HOST'],
			'shopSno'		=> $this->config_system['shopSno'],
			'expire_date'	=> $this->config_system['service']['expire_date'],
		);

		if($code == 'font_setting'){
			$data['getdata'] = $_GET['getdata'];
		}

		$res = readurl("http://interface.firstmall.kr/firstmall_plus/request.php?cmd=getGabiaPannel&code={$code}&isdemo=".$this->isdemo['isdemo'],$data);

		echo $res;
	}

	/* 가비아 메뉴얼 패널 */
	public function getGabiaManualPannel(){
		$this->load->helper('readurl');

		$code = $_GET['code'];

		$data = array(
			'service_code'	=> SERVICE_CODE,
			'hosting_code'	=> $this->config_system['service']['hosting_code'],
			'subDomain'		=> $this->config_system['subDomain'],
			'domain'		=> $this->config_system['domain'],
			'hostDomain'	=> $_SERVER['HTTP_HOST'],
			'shopSno'		=> $this->config_system['shopSno'],
			'expire_date'	=> $this->config_system['service']['expire_date'],
		);

		$res = readurl("http://interface.firstmall.kr/firstmall_plus/request.php?cmd=getGabiaManualPannel&code={$code}",$data);

		echo $res;
	}


	/* 상단메뉴별 카운트 반환 */
	public function getIssueCount(){

		$this->load->helper('noticount');

		$issueCount = array();
		$cfg_priod = config_save('noti_count');
		if(!$cfg_priod['order']) $cfg_priod['order'] = "6개월";
		if(!$cfg_priod['board']) $cfg_priod['board'] = "6개월";

		$start_date = str_to_priod_for_noti_count($cfg_priod['order']);

		// 주문처리 수
		$union_query[] = "
		select count(*) as cnt, 'order' as 'type'
		from fm_order
		where step in ('15','25','35','40','50','60','70') and hidden = 'N' and regist_date >= ?";

		// 촐고처리 수
		$union_query[] = "
		select count(*) as cnt,'export' as 'type'
		from (
			select 1 as cnt from fm_goods_export exp LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq, fm_goods_export_item as item
			where ord.regist_date >= ? and exp.export_code = item.export_code and exp.status in ('45','55','65') and not(exp.status='45' and ord.step='85')
			group by exp.export_seq
		) as u1";

		// 반품처리 수
		$union_query[] = "
		select count(*) as cnt, 'returns' as 'type'
		from fm_order_return rt left join fm_order rt_ord on rt.order_seq=rt_ord.order_seq
		where `status` in ('request','ing') and rt_ord.regist_date >= ?
		";

		// 환불처리 수
		$union_query[] = "
		select count(*) as cnt, 'refund' as 'type'
		from fm_order_refund rf left join fm_order rf_ord on rf.order_seq=rf_ord.order_seq
		where `status` in ('request','ing') and rf_ord.regist_date >= ?
		";

		$sql = "
		SELECT *
		FROM (
			".implode(' union ',$union_query)."
		) as a
		";
		$query = $this->db->query($sql,array($start_date,$start_date,$start_date,$start_date));
		$result = $query->result_array();
		foreach($result as $row){
			$issueCount['order']['title'] = "처리해야할 주문";
			$issueCount['order']['total'] += $row['cnt'];
			$issueCount['order'][$row['type']] = $row['cnt'];
		}

		$union_query = array();
		$start_date = str_to_priod_for_noti_count($cfg_priod['board']);

		// 입점사문의,1:1문의 수
		$union_query[] = "
		select count(*) as cnt, 'mbqna' as 'type'
		from fm_boarddata
		where (boardid='mbqna' or boardid='gs_seller_qna') and (re_contents = '' or re_contents is null)
		and display !=1 and r_date>=?
		";

		// 상품문의수
		$union_query[] = "
		select count(*) as cnt, 'gdqna' as 'type' from fm_goods_qna where (re_contents = '' or re_contents is null)
		and display !=1 and r_date>=?
		";

		$sql = "
		SELECT *
		FROM (
			".implode(' union ',$union_query)."
		) as a
		";
		$query = $this->db->query($sql,array($start_date,$start_date));
		$result = $query->result_array();
		foreach($result as $row){
			$issueCount['board']['title'] = "미처리 1:1문의, 상품문의";
			$issueCount['board']['total'] += $row['cnt'];
			$issueCount['board']['mbqna'] += $row['cnt'];
		}

		### 설정 일반정보 여부
		$data_basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		if(!$this->config_system['domain']){
			$issueCount['setting']['basic'] = 1;
		}
		if(!$data_basic['shopName']){
			$issueCount['setting']['basic'] = 1;
		}
		if(!$data_basic['shopTitleTag']){
			$issueCount['setting']['basic'] = 1;
		}

		### 설정 전자결제 여부
		$data_pg = config_load($this->config_system['pgCompany']);
		if( count($data_pg['payment']) == 0 ){
			if($this->config_system['not_use_kakao']=='y')
				$issueCount['setting']['pg'] = 1;
		}

		### 설정 무통장 여부
		$data_bank = config_load('bank');
		if($data_bank)foreach($data_bank as $bank){
			if($bank['accountUse'] == 'y') $bank_cnt++;
		}
		if(!$bank_cnt) $issueCount['setting']['bank'] = 1;

		### 설정 택배/배송비
		$arr = array('delivery','quick','direct');
		foreach($arr as $code){
			$scode = "shipping".$code;
			$data = config_load($scode);
		 	if($data['useYn']=='y') $shipping_cnt++;
		}
		$data = $result = "";
		$codes = code_load('internationalShipping');
		foreach($codes as $code){
			$data = config_load('internationalShipping'.$code['codecd']);
			if($data['company'] && $data['useYn']=='y') $shipping_cnt++;
		}
		if(!$shipping_cnt) $issueCount['setting']['shipping'] = 1;

		// 설정 전체 값 체크
		foreach($issueCount['setting'] as $setVal){
			if($setVal)	$issueCount['setting']['total'] = 1;
		}

		/* 신규 카운터 :: 2014-10-29 lwh */
		### 신규회원
		$startDate	= date('Y-m-d') . " 00:00:00";
		$endDate	= date('Y-m-d') . " 23:59:59";
		$sql = "
		SELECT count(*) as cnt
		FROM fm_member
		WHERE regist_date between '".$startDate."' and '".$endDate."'
		";
		$query = $this->db->query($sql);
		$result = $query->result_array();
		$issueCount['member']['title']		= "오늘 신규회원";
		$issueCount['member']['total']		= $result[0]['cnt'];
		$issueCount['member']['member']		= $result[0]['cnt'];

		### 진행중인 이벤트
		$union_query = array();
		
		// 할인이벤트
		$union_query[] = "
			SELECT count(*) AS cnt, 'event' as 'type'
			FROM fm_event
			WHERE CURRENT_TIMESTAMP( ) 
			BETWEEN start_date
			AND end_date
		";

		// 사은품 이벤트
		$union_query[] = "
			SELECT count( * ) AS cnt, 'gift' AS 'type'
			FROM fm_gift
			WHERE CURRENT_TIMESTAMP( ) 
			BETWEEN start_date
			AND end_date
		";

		// 출석체크
		$union_query[] = "
			SELECT count( * ) AS cnt, 'joincheck' AS 'type'
			FROM fm_joincheck
			WHERE CURRENT_TIMESTAMP( ) 
			BETWEEN start_date
			AND end_date
		";

		$sql = "
		SELECT *
		FROM (
			".implode(' union ',$union_query)."
		) as a
		";

		$query = $this->db->query($sql);
		$result = $query->result_array();
		foreach($result as $row){
			$issueCount['coupon']['title']		= "진행중인 이벤트";
			$issueCount['coupon']['total']		+= $row['cnt'];
			$issueCount['coupon'][$row['type']]	= $row['cnt'];
		}

		echo json_encode($issueCount);
	}

	public function ajax_volume_check(){
		return $this->volume_check();
	}

	public function category2json(){
		$result = array();
		$this->load->model('categorymodel');
		$code = $_GET['categoryCode'];
		$result = $this->categorymodel->get_admin_list($code);
		echo json_encode($result);
	}

	public function brand2json(){
		$result = array();
		$this->load->model('brandmodel');
		$code = $_GET['categoryCode'];
		$result = $this->brandmodel->get_admin_list($code);
		echo json_encode($result);
	}

	public function event2json(){
		$result = array();

		$event_seq = $_GET['event_seq'];

		$query = $this->db->query("select * from fm_event_benefits where event_seq=? order by event_benefits_seq asc",$event_seq);
		$result = $query->result_array();
		foreach($result as $i=>$row){
			$result[$i]['title'] = "[경우".($i+1)."] 할인".number_format($row['event_sale'])."%,적립".number_format($row['event_reserve'])."%";
		}

		echo json_encode($result);
	}

	public function location2json(){
		$result = array();
		$this->load->model('locationmodel');
		$code = $_GET['locationCode'];
		$result = $this->locationmodel->get_admin_list($code);
		echo json_encode($result);
	}

	/* QR 코드 안내*/
	public function qrcode_guide(){
		$this->template->assign(array('key'=>$_GET['key']));
		$this->template->assign(array('value'=>$_GET['value']));
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 혜택안내 */
	public function benifit(){
		$this->template->define(array('tpl'=>$this->template_path()));
		$this->template->print_("tpl");
	}

	/* 상단메뉴 스타일 변경 */
	public function setManagerIconView(){
		$this->db->query("update fm_manager set gnb_icon_view=? where manager_seq=?",array($_GET['val'],$this->managerInfo['manager_seq']));
		$this->managerInfo['gnb_icon_view'] = $_GET['val'];
		$this->session->set_userdata(array('manager'=>$this->managerInfo));
	}

	/* 메뉴얼 레이어 출력 */
	public function showSimpleManual(){
		$this->load->helper('readurl');

		$section = $_GET['section'];

		$data = array(
			'service_code'	=> SERVICE_CODE,
			'hosting_code'	=> $this->config_system['service']['hosting_code'],
			'subDomain'		=> $this->config_system['subDomain'],
			'domain'		=> $this->config_system['domain'],
			'hostDomain'	=> $_SERVER['HTTP_HOST'],
			'shopSno'		=> $this->config_system['shopSno'],
			'expire_date'	=> $this->config_system['service']['expire_date'],
		);

		$res = readurl("http://interface.firstmall.kr/firstmall_plus/request.php?cmd=getGabiaSimpleManualPannel&section={$section}",$data);

		echo $res;

	}

	public function goods_sort_popup(){

		### 이미지 사이즈
		$goodsImageSize = config_load('goodsImageSize');

		$this->template->assign('goodsImageSize',$goodsImageSize);
		$this->template->assign('r_img_size',$r_img_size);

		$this->template->assign($_GET);
		$this->template->define(array('tpl'=>$this->skin.'/common/_goods_sort_popup.html'));
		$this->template->print_("tpl");
	}

	public function goods_sort_popup_process(){
		$this->load->model('goodsmodel');

		$mode = $_GET['mode'];
		$kind = $_GET['kind'];
		$code = $_GET['code'];
		$page = $_GET['page'] ? $_GET['page'] : 1;
		$count_w = $_GET['count_w'] ?  $_GET['count_w'] : 4;
		$count_h = $_GET['count_h'] ? $_GET['count_h'] : 4;
		$perpage = $count_w * $count_h;
		$table = "fm_{$kind}_link";

		// 드래그로 순서변경
		if($mode=='single'){
			$goods_seqs = $_GET['goods_seqs'];
			$goods_sorts = $_GET['goods_sorts'];
			sort($goods_sorts);
			foreach($goods_seqs as $k=>$goods_seq){
				$sort = $goods_sorts[$k];
				$this->db->where("category_code",$code);
				$this->db->where("goods_seq",$goods_seq);
				$this->db->update($table,array("sort"=>$sort));
			}
		}

		// 여러 상품 선택해서 순서변경
		if($mode=='multi' ){
			$goods_seqs = $_GET['goods_seqs'];
			$sort_target_page = $_GET['sort_target_page'];
			$sort_target_location = $_GET['sort_target_location'];

			if($sort_target_page && $sort_target_location){
				$limit_s = (($sort_target_page-1)*$perpage);

				$sql = "select l.* from {$table} as l
				inner join fm_goods g on (l.goods_seq=g.goods_seq and g.goods_view='look' and g.goods_type='goods')
				inner join fm_goods_option o on (g.goods_seq=o.goods_seq and o.default_option='y')
				where l.category_code=? and g.goods_seq not in ('".implode("','",$goods_seqs)."')
				group by g.goods_seq
				order by l.sort asc, g.regist_date desc
				limit ".$limit_s.",".$perpage;

				$query = $this->db->query($sql,$code);
				$result = $query->result_array();

				if($result){
					// 타겟페이지 맨 위로
					if($sort_target_location=='first') {
						$newSort = $result[0]['sort']>0 ? $result[0]['sort'] : 0;

						$sql = "select l.* from {$table} as l where l.category_code=? and l.sort>=? order by l.sort asc";
						$query = $this->db->query($sql,array($code,$result[0]['sort']));
						$result = $query->result_array();

						foreach($goods_seqs as $k=>$goods_seq){
							$sort = $newSort;
							$this->db->where("category_code",$code);
							$this->db->where("goods_seq",$goods_seq);
							$this->db->update($table,array("sort"=>$sort));
							$newSort++;
						}

						foreach($result as $row){
							if(in_array($row['goods_seq'],$goods_seqs)) continue;
							$sort = $newSort;
							$this->db->where("category_code",$code);
							$this->db->where("goods_seq",$row['goods_seq']);
							$this->db->update($table,array("sort"=>$sort));
							$newSort++;
						}

					}

					// 타겟페이지 맨 아래로
					if($sort_target_location=='last') {
						$newSort = $result[$perpage-count($goods_seqs)] ? $result[$perpage-count($goods_seqs)]['sort'] : $result[count($result)-1]['sort']+1;

						$sql = "select l.* from {$table} as l where l.category_code=? and l.sort>=? order by l.sort asc";
						$query = $this->db->query($sql,array($code,$newSort));
						$result = $query->result_array();

						foreach($goods_seqs as $k=>$goods_seq){
							$sort = $newSort;
							$this->db->where("category_code",$code);
							$this->db->where("goods_seq",$goods_seq);
							$this->db->update($table,array("sort"=>$sort));
							$newSort++;
						}

						foreach($result as $row){
							if(in_array($row['goods_seq'],$goods_seqs)) continue;
							$sort = $newSort;
							$this->db->where("category_code",$code);
							$this->db->where("goods_seq",$row['goods_seq']);
							$this->db->update($table,array("sort"=>$sort));
							$newSort++;
						}
					}
				}else{
					$sql = "select max(sort) as sort from {$table} as l
					inner join fm_goods g on (l.goods_seq=g.goods_seq and g.goods_view='look' and g.goods_type='goods')
					inner join fm_goods_option o on (g.goods_seq=o.goods_seq and o.default_option='y')
					where l.category_code=? and g.goods_seq not in ('".implode("','",$goods_seqs)."')
					order by l.sort desc limit 1;";
					$query = $this->db->query($sql,$code);
					$result = $query->result_array();
					$newSort = $result[0]['sort']+1;

					foreach($goods_seqs as $k=>$goods_seq){
						$sort = $newSort;
						$this->db->where("category_code",$code);
						$this->db->where("goods_seq",$goods_seq);
						$this->db->update($table,array("sort"=>$sort));
						$newSort++;
					}
				}

			}
		}

		$sc=array();

		switch($kind){
			case 'category': $sc['category'] = $code; break;
			case 'brand': $sc['brand'] = $code; break;
			case 'location': $sc['location'] = $code; break;
		}

		$sc['admin_category']	= true;
		$sc['sort']				= 'popular';
		$sc['page']				= $page;
		$sc['perpage']			= $perpage;
		$sc['image_size']		= 'thumbCart';
		$list					= $this->goodsmodel->goods_list($sc);
		foreach($list['record'] as $k=>$record){
			$list['record'][$k]['goods_name_chars'] = htmlspecialchars(str_replace(array("'",'"'),'',strip_tags($record['goods_name'])));

			if($record['goods_status'] == 'normal'){
				$list['record'][$k]['goods_status_char'] = "<font size='1' color='#1E1EF0'>정상</font>";
			}else if($record['goods_status'] == 'runout'){
				$list['record'][$k]['goods_status_char'] = "<font size='1' color='#F01E1E'>품절</font>";
			}else if($record['goods_status'] == 'unsold'){
				$list['record'][$k]['goods_status_char'] = "<font size='1' color='#F01E1E'>판매중지</font>";
			}else if($record['goods_status'] == 'purchasing'){
				$list['record'][$k]['goods_status_char'] = "<font size='1' color='#F01E1E'>재고확보중</font>";
			}else{
				$list['record'][$k]['goods_status_char'] = "-";
			}
		}

		echo json_encode($list);
	}
}

/* End of file coupon.php */
/* Location: ./app/controllers/admin/coupon.php */