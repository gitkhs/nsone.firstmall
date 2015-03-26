<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class member extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->load->helper('member');
		$this->template->assign('mname',$this->managerInfo['mname']);
	}

	public function index()
	{
		redirect("/admin/member/catalog");
	}

	### 회원리스트
	public function catalog()
	{

		$this->load->model('snsmember');
		$this->load->model('membermodel');
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		// 개인 정보 조회 로그
		// $type,$manager_seq,$type_seq
		$this->load->model('logPersonalInformation');
		$this->logPersonalInformation->insert('memberlist',$this->managerInfo['manager_seq'],'');

		for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
		for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
		$this->template->assign('m_arr',$m_arr);
		$this->template->assign('d_arr',$d_arr);

		#### AUTH
		$auth_act		= $this->authmodel->manager_limit_act('member_act');
		if(isset($auth_act)) $this->template->assign('auth_act',$auth_act);
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		if(isset($auth_promotion)) $this->template->assign('auth_promotion',$auth_promotion);
		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(isset($auth_send)) $this->template->assign('auth_send',$auth_send);

		// 회원정보다운로드 체크
		if ($this->managerInfo['manager_yn']=='Y') {
			$this->load->model('managermodel');
			$mg_info =$this->managermodel->get_manager($this->managerInfo['manager_seq']);
			$mg_auth_arr = explode("||",$mg_info['manager_auth']);
			$mg_auth = array();

			$auth_member_down = false;
			foreach($mg_auth_arr as $k){
				$tmp_arr = explode("=",$k);
				$mg_auth[$tmp_arr[0]] = $tmp_arr[1];
			}

			if ($mg_auth['member_download']=='Y') {
				$auth_member_down = true;
			}
		} else {
			$auth_member_down	= $this->authmodel->manager_limit_act('member_download');
		}
		if(isset($auth_member_down)) $this->template->assign('auth_member_down',$auth_member_down);

		###
		if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

		### GROUP
		$group_arr = $this->membermodel->find_group_list();

		### SEARCH
		//print_r($_POST);
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'A.member_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):10;


		// 판매환경
		if( $_GET['sitetype'] ){
			$sc['sitetype'] = implode('\',\'',$_GET['sitetype']);
		}

		// 가입양식	if( $_GET['rute'] )$sc['rute'] = implode('\',\'',$_GET['rute']);
 		if( $_GET['snsrute'] ) {
			foreach($_GET['snsrute'] as $key=>$val){$sc[$val] = 1;}
		}


		### MEMBER
		$data = $this->membermodel->admin_member_list($sc);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$cntquery = $this->db->query("select count(*) as cnt from fm_member where status in ('done','hold') ");
		$cntrow = $cntquery->result_array();
		$sc['totalcount'] = $cntrow[0]['cnt'];

		$idx = 0;
		$this->load->model('Goodsreview','Boardmodel');//리뷰건
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			$adddata = $this->membermodel->get_member_seq_only($datarow['member_seq']);
			$datarow['email'] = $adddata['email'];
			$datarow['phone'] = $adddata['phone'];
			$datarow['cellphone'] = $adddata['cellphone'];
			$datarow['group_name'] = $adddata['group_name'];

			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';

			if($datarow['business_seq']){
				$datarow['user_name'] = $datarow['bname'];
				$datarow['cellphone'] = $datarow['bcellphone'];
				$datarow['phone'] = $datarow['bphone'];
			}
			###
			//$temp_arr	= $this->membermodel->get_order_count($datarow['member_seq']);
			//$datarow['member_order_cnt']	= $temp_arr['cnt'];

			//리뷰건
			$sc['whereis'] = ' and mseq='.$datarow['member_seq'];
			$sc['select'] = ' count(gid) as cnt ';
			$gdreviewquery = $this->Boardmodel->get_data($sc);
			$datarow['gdreview_sum'] = $gdreviewquery['cnt'];

			if($datarow['rute'] != "none" ) {
				$snsmbsc['select'] = ' * ';
				$snsmbsc['whereis'] = ' and member_seq = \''.$datarow['member_seq'].'\' ';
				$snslist = $this->snsmember->snsmb_list($snsmbsc);
				if($snslist['result'][0]) $datarow['snslist'] = $snslist['result'];
			}

			/****/

			$dataloop[] = $datarow;
		}

		## 유입경로 그룹
		$this->load->model('statsmodel');
		$referer_list	= $this->statsmodel->get_referer_grouplist();
		$this->template->assign('referer_list',$referer_list);


		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );

		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';

		//가입환경
		$sitetypeloop = sitetype($_GET['sitetype'], 'image', 'array');
		$this->template->assign('sitetypeloop',$sitetypeloop);

		//가입양식
		$ruteloop = memberrute($_GET['rute'], 'image', 'array');
		$this->template->assign('ruteloop',$ruteloop);

		$this->template->assign('pagin',$paginlay);
		$this->template->assign('group_arr',$group_arr);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);


		$this->template->assign('query_string',get_query_string());

		$this->template->define('member_list',$this->skin.'/member/member_list.html');
		$this->template->define('member_search',$this->skin.'/member/member_search.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function set_search_default(){
		foreach($_POST as $key => $data){
			if( is_array($data) ){
				foreach($data as $key2 => $data2){
					if($data2) $cookie_arr[] = $key."[".$key2."]"."=".$data2;
				}
			}else if($data){
				$cookie_arr[] = $key."=".$data;
			}
		}
		if($cookie_arr){
			$cookie_str = implode('&',$cookie_arr);
			if($_POST['gb']=='withdrawal'){
				$_COOKIE['withdrawal_search'] = $cookie_str;
				setcookie('withdrawal_search',$cookie_str,time()+86400*30);
			}else{
				$_COOKIE['member_list_search'] = $cookie_str;
				setcookie('member_list_search',$cookie_str,time()+86400*30);
			}
		}
		$callback = "parent.closeDialog('search_detail_dialog');";
		openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
	}

	public function get_search_default(){
		$arr = explode('&',$_COOKIE['member_list_search']);
		foreach($arr as $data){
			$arr2 = explode("=",$data);
			$result[] = $arr2;

		}
		echo json_encode($result);
	}

	public function get_search_withdrawal(){
		$arr = explode('&',$_COOKIE['withdrawal_search']);
		foreach($arr as $data){
			$arr2 = explode("=",$data);
			$result[] = $arr2;

		}
		echo json_encode($result);
	}



	### 회원상세
	public function detail()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		#### AUTH
		$auth_act		= $this->authmodel->manager_limit_act('member_act');
		if(isset($auth_act)) $this->template->assign('auth_act',$auth_act);
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		if(isset($auth_promotion)) $this->template->assign('auth_promotion',$auth_promotion);
		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(isset($auth_send)) $this->template->assign('auth_send',$auth_send);

		if(!isset($_GET['member_seq'])){
			$callback = "parent.history.back();";
			openDialogAlert("잘못된 접근입니다.",400,140,'parent',$callback);
			die();
		}

		// 개인정보 조회 로그
		//'member', 'memberlist', 'order', 'export', 'return', 'refund', 'orderexcel', 'exportexcel'
		$this->load->model('logPersonalInformation');
		$this->logPersonalInformation->insert('member',$this->managerInfo['manager_seq'],$_GET['member_seq']);

		###
		$this->load->model('membermodel');
		$data = $this->membermodel->get_member_data($_GET['member_seq']);
		if($data['auth_type']=='auth'){
			$data['auth_type'] = "실명인증";
		}else if($data['auth_type']=='ipin'){
			$data['auth_type'] = "아이핀";
		}else{
			$data['auth_type'] = "없음";
		}

		$data['zip_arr'] = explode("-",$data['zipcode']);
		$data['bzip_arr'] = explode("-",$data['bzipcode']);

		$withdrawal = code_load('withdrawal');
		if($withdrawal) $this->template->assign('withdrawal_arr',$withdrawal);


		$joinform = ($this->joinform)?$this->joinform:config_load('joinform');
		if($joinform) $this->template->assign('joinform',$joinform);
		$this->template->assign('memberIcondata',memberIconConf());

		//가입 추가 정보 리스트
		//$mdata = $this->mdata;
		$qry = "select * from fm_joinform where used='Y' order by sort_seq";
		$query = $this->db->query($qry);
		$form_arr = $query -> result_array();
		foreach ($form_arr as $k => $subdata){
		$msubdata=$this->membermodel->get_subinfo($data['member_seq'],$subdata['joinform_seq']);
		$subdata['label_view'] = $this -> membermodel-> get_labelitem_type($subdata,$msubdata);
		$sub_form[] = $subdata;
		}
		$this->template->assign('form_sub',$sub_form);

		###
		$grade_list = $this->membermodel->find_group_list();
		$grade_list = array_reverse($grade_list);
		$this->template->assign('grade_list',$grade_list);
		//print_r($grade_list);

		//1:1문의건
		$this->load->model('Boardmodel');
		$sc['whereis'] = " and boardid = 'mbqna'   and mseq='".$data['member_seq']."'";
		$sc['select'] = " count(gid) as cnt ";
		$mbqnaquery = $this->Boardmodel->get_data($sc);
		$data['mbqna_sum'] = $mbqnaquery['cnt'];

		$sc['whereis'] = " and re_contents!='' and boardid = 'mbqna'  and mseq='".$data['member_seq']."'";
		$sc['select'] = " count(gid) as cnt ";
		$mbqnareplyquery = $this->Boardmodel->get_data($sc);
		$data['mbqna_reply'] = $mbqnareplyquery['cnt'];//답변완료수 / 전체질문수

		//리뷰건
		$this->load->model('goodsreview');
		$sc['whereis'] = " and mseq='".$data['member_seq']."'";
		$sc['select'] = " count(gid) as cnt ";
		$gdreviewquery = $this->goodsreview->get_data($sc);
		$data['gdreview_sum'] = $gdreviewquery['cnt'];

		//상품문의건
		$this->load->model('goodsqna');
		$sc['whereis'] = "and mseq='".$data['member_seq']."'";
		$sc['select'] = " count(gid) as cnt ";
		$gdqnaquery = $this->goodsqna->get_data($sc);
		$data['gdqna_sum'] = $gdqnaquery['cnt'];

		//쿠폰보유건 test
		$this->load->model('couponmodel');
		$dsc['whereis'] = " and use_status='unused' and member_seq='".$data['member_seq']."'";
		$data['coupondownloadtotal'] = $this->couponmodel->get_download_total_count($dsc);

		$this->load->model('snsmember');


		$snsmbsc['select'] = " * ";
		$snsmbsc['whereis'] = " and member_seq ='".$data['member_seq']."' ";
		$snslist = $this->snsmember->snsmb_list($snsmbsc);
		if($snslist['result'][0]) $data['snslist'] = $snslist['result'];

		if($data['sns_f']) {//facebook 전용인경우
			foreach($snslist['result'] as $snslist){
				if( $snslist['sns_f'] == $data['sns_f'] ) {
					$snsmb['sns'] = $snslist;
					break;
				}
			}
			if($snsmb) $this->template->assign($snsmb);

			$data['totalinviteck'] = $data['member_invite_cnt'];// 추천회원수

			//$fquery = $this->db->query("select count(A.member_seq) as total from fm_memberinvite A LEFT JOIN fm_member B ON A.member_seq = B.member_seq WHERE B.fb_invite = '".$data['member_seq']."' and B.status = 'done' ");
			$fquery = $this->db->query("select count(member_seq) as total from fm_member WHERE fb_invite = '".$data['member_seq']."' and status != 'withdrawal' ");
			$snsftotal = $fquery->row_array();
			$data['totalinvitejoin'] = $snsftotal['total'];//초대후 회원가입된 회원수
		}

		//추천회원정보
		if($data['fb_invite']){
			$fb_invitequery = $this->db->query("select userid from fm_member WHERE member_seq = '".$data['fb_invite']."' and status != 'withdrawal' ");
			$fb_invite = $fb_invitequery->row_array();
			$data['fb_invite_id'] = $fb_invite['userid'];
		}


		$data['totalrecommend'] = $data['member_recommend_cnt'];// 추천회원수

		//$arr_step 	= config_load('step');
		$order_summary = array();
		/*
		입금을 확인하세요! : 주문접수
		상품을 출고하세요! : 결제확인, 상품준비, 부분출고준비, 부분출고완료, 부분배송중, 부분배송완료
		출고를 완료하세요! : 출고준비
		배송을 완료하세요! : 출고완료, 배송중
		반품을 회수하세요! : 반품접수, 반품 처리 중
		환불을 처리하세요! : 환불접수, 환불 처리 중
		*/

		/* 입금을 확인하세요 */
		$query = "
		select sum(settleprice) as settleprice, count(*) as cnt,
		( select sum(ea) from fm_order_item_option where item_seq in (select item_seq from fm_order_item where order_seq = fm_order.order_seq) ) as option_ea,
		( select sum(ea) from fm_order_item_suboption where item_seq in (select item_seq from fm_order_item where order_seq = fm_order.order_seq) ) as suboption_ea
		from fm_order where step = '15' and member_seq=?";
		$query = $this->db->query($query,$data['member_seq']);
		$result = $query->row_array();
		$order_summary[] = array(
			'title'			=> '입금을 확인하세요',
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'ea'			=> $result['option_ea']+$result['suboption_ea'],
			'link'			=> '../order/catalog?chk_step[15]=1&keyword_type=userid&keyword='.$data['userid']
		);

		/* 상품을 출고하세요 */
		$query = "
		select sum(settleprice) as settleprice, count(*) as cnt,
		( select sum(ea) from fm_order_item_option where item_seq in (select item_seq from fm_order_item where order_seq = fm_order.order_seq) ) as option_ea,
		( select sum(ea) from fm_order_item_suboption where item_seq in (select item_seq from fm_order_item where order_seq = fm_order.order_seq) ) as suboption_ea
		from fm_order where step in ('25','35','40','50','60','70') and member_seq=?";
		$query = $this->db->query($query,$data['member_seq']);
		$result = $query->row_array();
		$order_summary[] = array(
			'title'		=> '상품을 출고하세요',
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'ea'			=> $result['option_ea']+$result['suboption_ea'],
			'link'		=> '../order/catalog?chk_step[25]=1&chk_step[35]=1&chk_step[40]=1&chk_step[50]=1&chk_step[60]=1&chk_step[70]=1&keyword_type=userid&keyword='.$data['userid']
		);

		/* 출고를 완료하세요 */
		$query = "
		SELECT count(exp.export_seq) cnt ,
		sum(opt.price*item.ea) opt_price,
		sum(sub.price*item.ea) sub_price,
		sum(item.ea) ea
		FROM
		fm_goods_export exp
			LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq
		,fm_goods_export_item item
			LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
			LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
		WHERE exp.export_code=item.export_code AND exp.status='45' and ord.member_seq=?
		group by exp.export_seq";

		$query = $this->db->query($query,$data['member_seq']);
		$result = array();
		foreach($query->result_array() as $tmp){
			$result['cnt'] += $tmp['cnt'];
			$result['settleprice'] += $tmp['opt_price']+$tmp['sub_price'];
			$result['ea'] += $tmp['ea'];
		}
		$order_summary[] = array(
			'title'		=> '출고를 완료하세요',
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'ea'			=> $result['ea'],
			'link'		=> '../export/catalog?export_status[45]=1&keyword_type=mem.userid&keyword='.$data['userid']
		);

		/* 배송을 완료하세요 */
		$query = "
		SELECT count(exp.export_seq) cnt ,
		sum(opt.price*item.ea) opt_price,
		sum(sub.price*item.ea) sub_price,
		sum(item.ea) ea
		FROM
		fm_goods_export exp
			LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq
		,fm_goods_export_item item
			LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
			LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
		WHERE exp.export_code=item.export_code AND exp.status in ('55','65') and ord.member_seq=?
		group by exp.export_seq";

		$query = $this->db->query($query,$data['member_seq']);
		$result = array();
		foreach($query->result_array() as $tmp){
			$result['cnt'] += $tmp['cnt'];
			$result['settleprice'] += $tmp['opt_price']+$tmp['sub_price'];
			$result['ea'] += $tmp['ea'];
		}
		$order_summary[] = array(
			'title'		=> '배송을 완료하세요',
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'ea'			=> $result['ea'],
			'link'		=> '../export/catalog?export_status[55]=1&export_status[65]=1&keyword_type=mem.userid&keyword='.$data['userid']
		);

		/* 반품을 회수하세요 */
		$query = "
		SELECT count(ret.return_seq) cnt ,
		sum(opt.price*item.ea) opt_price,
		sum(sub.price*item.ea) sub_price,
		sum(item.ea) ea
		FROM
		fm_order_return ret
			LEFT JOIN fm_order ord ON ord.order_seq=ret.order_seq
		,fm_order_return_item item
			LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
			LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
		WHERE ret.return_code=item.return_code AND ret.status in ('request','ing') and ord.member_seq=?
		group by ret.return_seq";
		$query = $this->db->query($query,$data['member_seq']);
		$result = array();
		foreach($query->result_array() as $tmp){
			$result['cnt'] += $tmp['cnt'];
			$result['settleprice'] += $tmp['opt_price']+$tmp['sub_price'];
			$result['ea'] += $tmp['ea'];
		}
		$order_summary[] = array(
			'title'		=> '반품을 회수하세요',
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'ea'			=> $result['ea'],
			'link'		=> '../returns/catalog?return_status[]=request&return_status[]=ing&keyword_type=mem.userid&keyword='.$data['userid']
		);

		/* 환불을 처리하세요 */
		$query = "
		SELECT count(ref.refund_seq) cnt ,
		sum(opt.price*item.ea) opt_price,
		sum(sub.price*item.ea) sub_price,
		sum(item.ea) ea
		FROM
		fm_order_refund ref
			LEFT JOIN fm_order ord ON ord.order_seq=ref.order_seq
			LEFT JOIN fm_order_return ret ON ret.refund_code=ref.refund_code
		,fm_order_refund_item item
			LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
			LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
		WHERE ref.refund_code=item.refund_code AND ref.status in ('request','ing') and (ret.status is null or ret.status='complete') and ord.member_seq=?
		group by ref.refund_seq";
		$query = $this->db->query($query,$data['member_seq']);
		$result = array();
		foreach($query->result_array() as $tmp){
			$result['cnt'] += $tmp['cnt'];
			$result['settleprice'] += $tmp['opt_price']+$tmp['sub_price'];
			$result['ea'] += $tmp['ea'];
		}
		$order_summary[] = array(
			'title'		=> '환불을 처리하세요',
			'settleprice'	=> $result['settleprice'],
			'count'			=> $result['cnt'],
			'ea'			=> $result['ea'],
			'link'		=> '../refund/catalog?refund_status[]=request&refund_status[]=ing&keyword_type=mem.userid&keyword='.$data['userid']
		);

		foreach($order_summary as $tmp){
			$tot_order_summary['settleprice'] += $tmp['settleprice'];
			$tot_order_summary['count'] 	+= $tmp['count'];
			$tot_order_summary['ea'] 		+= $tmp['ea'];
		}


		###
		/**
		$temp_arr	= $this->membermodel->get_order_count($data['member_seq']);
		$data['order_cnt'] = $temp_arr['cnt'];
		$data['order_sum'] = $temp_arr['sum'];
		**/

		if(!$tot_order_summary['settleprice']) $tot_order_summary['settleprice'] = $temp_arr['sum'];

		for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
		for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
		$this->template->assign('m_arr',$m_arr);
		$this->template->assign('d_arr',$d_arr);
		$this->template->assign('query_string',$_GET['query_string']);

		$this->template->assign($data);
		$this->template->assign(array(
			'order_summary'		=> $order_summary,
			'tot_order_summary'	=> $tot_order_summary
		));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function emoney_detail()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];
		###
		$this->load->model('membermodel');
		$data = $this->membermodel->get_member_data($member_seq);
		$this->template->assign($data);

		###
		$limit	= commonCountSMS();

		$this->template->assign('count',$limit);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function point_detail()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];
		###
		$this->load->model('membermodel');
		$data = $this->membermodel->get_member_data($member_seq);
		$this->template->assign($data);

		$limit	= commonCountSMS();

		$this->template->assign('count',$limit);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function cash_detail()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];
		###
		$this->load->model('membermodel');
		$data = $this->membermodel->get_member_data($member_seq);
		$this->template->assign($data);


		$limit	= commonCountSMS();

		$this->template->assign('count',$limit);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sms_pop()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		if($_GET['member_seq']){
			$member_seq = $_GET['member_seq'];
			###
			$this->load->model('membermodel');
			$data = $this->membermodel->get_member_data($member_seq);
			$this->template->assign($data);
		}
		if($_GET['cellphone']){
			$this->template->assign('cellphone',$_GET['cellphone']);
		}

		//쿠폰상품의 확인코드 SMS보내기
		if($_GET['certify_code']){
			$certify_code_msg = $this->config_basic['shopName']."쇼핑몰에서 판매된 쿠폰 상품에 대하여 구매자가 귀사 매장 방문 시 쿠폰 사용 확인코드는 ".$_GET['certify_code']."입니다.";
			$this->template->assign('certify_code_msg',$certify_code_msg);
		}

		$limit	= commonCountSMS();

		$this->template->assign('count',$limit);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

	}


	public function email_pop()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];
		if($member_seq){
			$this->load->model('membermodel');
			$data = $this->membermodel->get_member_data($member_seq);
			$this->template->assign($data);
		}else if($_GET['email']){
			$data['email'] = $_GET['email'];
			$this->template->assign($data);
		}

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$master = config_load('master');

		###
		$query = $this->db->query("select * from fm_log_email order by seq desc limit 10");
		$emailData = $query->result_array();

		###
		$this->load->model('usedmodel');
		$email_chk = $this->usedmodel->hosting_check();
		$this->template->assign('email_chk',$email_chk);

		###
		$this->template->assign(array('mail_count'=>$master['mail_count'],'email'=>$data['email']));
		$this->template->assign('loop',$emailData);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function cash_pop()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];
		if($member_seq){
			$this->load->model('membermodel');
			$data = $this->membermodel->get_member_data($member_seq);
			$this->template->assign($data);
		}else if($_GET['email']){
			$data['email'] = $_GET['email'];
			$this->template->assign($data);
		}

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$master = config_load('master');

		###
		$query = $this->db->query("select * from fm_log_email order by seq desc limit 10");
		$emailData = $query->result_array();

		###
		$this->load->model('usedmodel');
		$email_chk = $this->usedmodel->hosting_check();
		$this->template->assign('email_chk',$email_chk);

		###
		$this->template->assign(array('mail_count'=>$master['mail_count'],'email'=>$data['email']));
		$this->template->assign('loop',$emailData);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function emoney_list()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];

		###
		$sc = $_GET;
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['member_seq']		= $member_seq;
		$sc['perpage']			= '5';

		$this->load->model('membermodel');

		$data = $this->membermodel->emoney_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_emoney',array('member_seq'=>$member_seq));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			/**
			$datarow['contents'] = "";
			if($datarow['type']=='order'){
				$datarow['contents'] = "주문번호 ".$datarow['ordno'];
			}else if($datarow['type']=='join'){
				$datarow['contents'] = "회원가입";
			}else if($datarow['type']=='bookmark'){
				$datarow['contents'] = "즐겨찾기";
			}else if($datarow['type']=='refund'){
				$datarow['contents'] = "환불 ".$datarow['ordno'];
			}
			**/
			$datarow['mname'] = get_manager_name($datarow['manager_seq']);
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function used_history(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$type	= $_GET['type'];
		$seq	= $_GET['seq'];
		$table	= "fm_".$type;
		$seq_nm	= $type."_seq";

		$sql = "select A.*, B.memo as pmemo from fm_used_log A left join {$table} B ON A.used_seq = B.{$seq_nm} where A.parent_seq = '{$seq}' order by A.seq asc";
		//echo $sql;
		$query = $this->db->query($sql);
		foreach($query->result_array() as $v){
			/**
			if($v['type']=='order'){
				$v['contents'] = "주문번호 ".$v['ordno'];
			}else if($v['type']=='join'){
				$v['contents'] = "회원가입";
			}else if($v['type']=='bookmark'){
				$v['contents'] = "즐겨찾기";
			}else if($v['type']=='refund'){
				$v['contents'] = "환불 ".$v['ordno'];
			}
			**/
			$loop[] = $v;
		}
		$this->template->assign('loop',$loop);

		//print_r($loop);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function point_list()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];

		###
		$sc = $_GET;
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['member_seq']		= $member_seq;
		$sc['perpage']			= '5';

		$this->load->model('membermodel');

		$data = $this->membermodel->point_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_point',array('member_seq'=>$member_seq));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			/**
			$datarow['contents'] = "";
			if($datarow['type']=='order'){
				$datarow['contents'] = "주문번호 ".$datarow['ordno'];
			}else if($datarow['type']=='join'){
				$datarow['contents'] = "회원가입";
			}else if($datarow['type']=='bookmark'){
				$datarow['contents'] = "즐겨찾기";
			}else if($datarow['type']=='refund'){
				$datarow['contents'] = "환불 ".$datarow['ordno'];
			}
			**/
			$datarow['mname'] = get_manager_name($datarow['manager_seq']);
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function cash_list()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$member_seq = $_GET['member_seq'];

		###
		$sc = $_GET;
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['member_seq']		= $member_seq;
		$sc['perpage']			= '5';

		$this->load->model('membermodel');

		$data = $this->membermodel->cash_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_point',array('member_seq'=>$member_seq));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			/**
			$datarow['contents'] = "";
			if($datarow['type']=='order'){
				$datarow['contents'] = "주문번호 ".$datarow['ordno'];
			}else if($datarow['type']=='join'){
				$datarow['contents'] = "회원가입";
			}else if($datarow['type']=='bookmark'){
				$datarow['contents'] = "즐겨찾기";
			}else if($datarow['type']=='refund'){
				$datarow['contents'] = "환불 ".$datarow['ordno'];
			}
			**/
			$datarow['mname'] = get_manager_name($datarow['manager_seq']);
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	//초대하기 내역입니다.
	public function invite_list()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		###

		$this->load->model('membermodel');
		$this->load->model('snsfbinvite');
		unset($sc);
		### SEARCH
		$sc = $_GET;
		$sc['orderby']		= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'seq';
		$sc['sort']				= (!empty($_GET['sort'])) ?			$_GET['sort']:'desc';
		$sc['page']			= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
		$sc['member_seq']			= $_GET['member_seq'];
		$data = $this->snsfbinvite->snsinvite_list_search($sc);

		/**
		 * count setting
		**/
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = $this->snsfbinvite->get_item_total_count($sc);

		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$datarow['date']			= substr($datarow['r_date'],2,14);//초대일

			$datarow['joinck'] = ($datarow['joinck'] == 1)? "Y":"N";
			$dataloop[] = $datarow;
		}

		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],'?member_seq='.$_GET['member_seq'], getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	//추천하기 내역입니다.
	public function recommend_list()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		###

		$this->load->model('membermodel');
		$data = $this->membermodel->get_member_data($_GET['member_seq']);
		unset($sc);
		### SEARCH
		$sc = $_GET;
		$sc['orderby']		= (!empty($_GET['orderby'])) ?	$_GET['orderby']:'seq';
		$sc['sort']				= (!empty($_GET['sort'])) ?			$_GET['sort']:'desc';
		$sc['page']			= (!empty($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']		= (!empty($_GET['perpage'])) ?	intval($_GET['perpage']):10;
		$sc['recommend']			= $data['userid'];
		$data = $this->membermodel->recommend_list($sc);

		/**
		 * count setting
		**/
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = @ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = $this->membermodel->recommend_total_count($sc);

		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$datarow['date']			= substr($datarow['regist_date'],2,14);//추천(가입)일
			$dataloop[] = $datarow;
		}
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtagfront($sc['searchcount'],$sc['perpage'],'?member_seq='.$_GET['member_seq'], getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	###
	public function withdrawal()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		###
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'withdrawal_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

		###
		$this->load->model('membermodel');
		$data = $this->membermodel->admin_withdrawal_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_member',array('status'=>'withdrawal'));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	### SMS발송관리
	public function sms()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->load->model('membermodel');

		##
		$sms_info = config_load('sms_info');
		if($sms_info['send_num']) $send_num = explode("-",$sms_info['send_num']);
		if($sms_info['admis_cnt']>0){
			for($i=0;$i<$sms_info['admis_cnt'];$i++){
				$id = "admins_num_".$i;
				$v['number'] = explode("-",$sms_info[$id]);
				$admins_arr[] = $v;
			}
		}

		###
		$sms		= config_load('sms');
		$sms_rest	= config_load('sms_restriction');

		### 공통
		$msg_group[1]['name']		= array('join', 'findid', 'order', 'withdrawal', 'findpwd', 'settle');//cs
		$msg_group[1]['title']		= array('회원가입 시', '아이디 찾기', '주문접수 시', '회원탈퇴 시', '비밀번호 찾기', '결제확인 시');//1:1문의 답변 시
		$msg_group[1]['disable']	= array('', 'disabled', '', 'disabled', 'disabled', '');//''
		$msg_group[1]['user_req']	= array('', '', '', '', '', '');//''
		$msg_group[1]['provider']	= array('', '', '', '', '', 'y');//''

		### 실물상품
		$msg_group[2]['name']          = array('released','released2', 'cancel',  'delivery', 'delivery2','refund');
		$msg_group[2]['title']         = array('출고완료 시', '출고완료 시 받는분(≠주문자)','결제취소→환불완료 시', '배송완료 시', '배송완료 시 받는분(≠주문자)', '반품→환불완료 시');
		$msg_group[2]['disable']     = array('', 'disabled', '', '', 'disabled', '');
		$msg_group[2]['user_req']     = array('', '', '', '', '', '');
		$msg_group[2]['provider']     = array('', '', '', 'y', 'y', '');

		### 쿠폰상품
		$msg_group[3]['name']          = array('coupon_released2', 'coupon_released', 'coupon_cancel','coupon_delivery2', 'coupon_delivery', 'coupon_refund');
		$msg_group[3]['title']          = array('출고완료 시(쿠폰발송) 주문자(≠받는분)','출고완료 시(쿠폰발송)', '결제취소→환불완료 시', '배송완료 시(쿠폰사용) 주문자(≠받는분)','배송완료 시(쿠폰사용)', '반품→환불완료 시');
		$msg_group[3]['disable']     = array('disabled', '', '', 'disabled', '', '');
		$msg_group[3]['user_req']     = array('', 'y', '', '', 'y', '');
		$msg_group[3]['provider']     = array('', '', '', 'y', 'y', '');

		## 발송제한 설정 시간 및 예약발송시간
		if($sms_rest['config_time_s'] && $sms_rest['config_time_e'] && $sms_rest['reserve_time']){
			if($sms_rest['reserve_time'] > 60){
				$sms_rest['reserve_time'] = ($sms_rest['reserve_time']/60)."시간";
			}else{
				$sms_rest['reserve_time'] .= "분";
			}
			$restriction_msg = "<span style='color:#d90000;font-size:11px;line-height:14px;'>발송제한시간 : ";
			$restriction_msg.= $sms_rest['config_time_s']."시~".$sms_rest['config_time_e']."시 ";
			$restriction_msg.= " ▶ 08시 +".$sms_rest['reserve_time']."</span>";
		}else{
			$restriction_msg = "";
		}

		/* 기본 메시지가 빈 값이 있는 경우가 있어서 추가 leewh 2014-10-20 */
		$msg_arr			= parse_ini_file(APPPATH."config/_default_sms_msg.ini", true);
		foreach ($msg_group as $k => $data){
			$sms_arr		= $data['name'];
			$sms_text		= $data['title'];
			$sms_dis		= $data['disable'];
			$user_req		= $data['user_req'];
			$sms_provider	= $data['provider'];
			$sms_cnt		= count($sms_arr);

			for($i = 0; $i < $sms_cnt; $i++){
				###
				$name		= $sms_arr[$i];

				###
				$v['name']			= $name;
				$v['text']			= $sms_text[$i];
				$v['provider_use']	= $sms_provider[$i];

				$v['user']			= (trim($sms[$name.'_user'])) ? trim($sms[$name.'_user']) : $msg_arr[$name.'_user'];
				$v['admin']			= trim($sms[$name.'_admin']);
				$v['disabled']		= $sms_dis[$i];
				$v['user_req']		= $user_req[$i];
				$v['arr']			= $admins_arr;
				$v['user_chk']		= $sms[$name."_user_yn"];
				$v['provider_chk']	= $sms[$name."_provider_yn"];
				if($sms_rest[$name] == "checked" && $restriction_msg) $v['rest_msg'] = $restriction_msg;
				for($j = 0; $j < $sms_info['admis_cnt']; $j++)
					$v['admins_chk'][] = $sms[$name."_admins_yn_".$j];

				$loop[$k][]		= $v;
				unset($v);
			}
		}


		## 치환코드 리스트
			$replace_item	= array();
			$replace_item[] = array("cd" => "shopName"			,"nm" => "쇼핑몰 이름(설정 &gt; 일반정보)");
			$replace_item[] = array("cd" => "shopDomain"		,"nm" => "쇼핑몰 도메인");
			$replace_item[] = array("cd" => "userid"			,"nm" => "회원아이디");
			$replace_item[] = array("cd" => "username"			,"nm" => "회원명(회원명 없을시 제외)");
			$replace_item[] = array("cd" => "password"			,"nm" => "회원비밀번호");
			$replace_item[] = array("cd" => "order_user"		,"nm" => "주문자명");
			$replace_item[] = array("cd" => "recipient_user"	,"nm" => "받는분이름");
			$replace_item[] = array("cd" => "ordno"				,"nm" => "주문번호");
			$replace_item[] = array("cd" => "orduserName"		,"nm" => "주문자명");
			$replace_item[] = array("cd" => "go_item"           ,"nm" => "출고완료/배송완료 상품","etc"=>"여러 개의 상품인 경우 외 몇 건으로 표시됨<br />상품명은 치환코드 길이 설정을 따릅니다.");
			$replace_item[] = array("cd" => "ord_item"           ,"nm" => "주문상품","etc"=>"여러 개의 상품인 경우 외 몇 건으로 표시됨<br />상품명은 치환코드 길이 설정을 따릅니다.");
			$replace_item[] = array("cd" => "bank_account"		,"nm" => "입금은행 계좌번호 예금주");
			$replace_item[] = array("cd" => "settleprice"		,"nm" => "입금(결제)금액");
			$replace_item[] = array("cd" => "settle_kind"		,"nm" => "결제수단 수단별확인메시지","etc"=>"<div style='color:#999999;'>
																	신용카드 예시) 카드결제 완료<br>
																	계좌이체 예시) 계좌이체 완료<br>
																	가상계좌 예시) 가상계좌 완료<br>
																	무통장 예시) OO은행 입금확인<br>
																	핸드폰 예시) 핸드폰 결제완료
																	</div>");
			$replace_item[] = array("cd" => "delivery_company"	,"nm" => "택배사명");
			$replace_item[] = array("cd" => "delivery_number"	,"nm" => "운송장번호");
			$replace_item[] = array("cd" => "coupon_serial"		,"nm" => "쿠폰인증코드");
			$replace_item[] = array("cd" => "couponNum"			,"nm" => "쿠폰발송회차");
			$replace_item[] = array("cd" => "coupon_value"		,"nm" => "쿠폰값어치");
			$replace_item[] = array("cd" => "options"			,"nm" => "필수옵션");
			$replace_item[] = array("cd" => "used_time"			,"nm" => "쿠폰사용일시");
			$replace_item[] = array("cd" => "coupon_used"		,"nm" => "쿠폰사용 값어치");
			$replace_item[] = array("cd" => "coupon_remain"		,"nm" => "쿠폰잔여 값어치");
			$replace_item[] = array("cd" => "used_location"		,"nm" => "쿠폰 사용처");
			$replace_item[] = array("cd" => "confirm_person"	,"nm" => "쿠폰사용 확인자");
			$replace_item[] = array("cd" => "goods_name"        ,"nm" => "쿠폰상품","etc"=>"여러 개의 상품인 경우 외 몇 건으로 표시됨<br />상품명은 치환코드 길이 설정을 따릅니다.");
			$replace_item[] = array("cd" => "repay_item"        ,"nm" => "취소/반품->환불완료 상품","etc"=>"여러 개의 상품인 경우 외 몇 건으로 표시됨<br />상품명은 치환코드 길이 설정을 따릅니다.");
	
		## 공통
			// 회원가입 시
			$use_replace_code['join']		= array("shopName","shopDomain","userid","username");
			// 회원탈퇴 시
			$use_replace_code['withdrawal'] = array("shopName","shopDomain");
			// 아이디 찾기
			$use_replace_code['findid']		= array("shopName","shopDomain","userid","username");
			// 비밀번호 찾기
			$use_replace_code['findpwd']	= array("shopName","shopDomain","password");
			// 주문접수 시
			$use_replace_code['order']		= array("shopName","shopDomain","ordno","ord_item","bank_account","settleprice"
													,"userid","order_user");
			// 결제확인 시
			$use_replace_code['settle']		= array("shopName","shopDomain","ordno","ord_item","settleprice","settle_kind"
													,"userid","order_user");
		## 실물 발송상품
			// 출고완료 시
			$use_replace_code['released']	= array("shopName","shopDomain","ordno","go_item","delivery_company","delivery_number"
													,"userid","order_user","recipient_user");
			// 배송완료 시
			$use_replace_code['delivery']	= array("shopName","shopDomain","ordno","go_item","userid","order_user","recipient_user");
			//결제취소→환불완료 시
			$use_replace_code['cancel']		= array("shopName","shopDomain","repay_item","userid","order_user");
			// 반품→환불완료 시
			$use_replace_code['refund']		= array("shopName","shopDomain","repay_item","userid","order_user");
		## 쿠폰 발송 상품
			// 출고완료 시(쿠폰발송)
			$use_replace_code['coupon_released']	= array("shopName","shopDomain","coupon_serial","couponNum","coupon_value"
														,"options","goods_name","userid","order_user","recipient_user");
			// 결제취소→환불완료 시
			$use_replace_code['coupon_cancel']		= array("shopName","shopDomain","coupon_serial","couponNum","goods_name"
													,"userid","order_user","recipient_user");
			//배송완료 시(쿠폰사용)
			$use_replace_code['coupon_delivery']	= array("shopName","shopDomain","coupon_serial","couponNum","coupon_value"
													,"options","used_time","coupon_used","coupon_remain","used_location"
													,"confirm_person","goods_name","userid","order_user");
			// 반품→환불완료 시
			$use_replace_code['coupon_refund']		= array("shopName","shopDomain","coupon_serial","couponNum","goods_name"
													,"userid","order_user");


		$this->template->assign('loop',$loop);
		$this->template->assign('replace_code_loop',$replace_item);
		$this->template->assign('use_replace_code',$use_replace_code);

		## 상품명 치환코드 길이 제한
		$sms_goods_limit          = config_load('sms_goods_limit');
		$sms_goods_limit_default  = array("goods_item_use"=>"n","go_item_use"=>"n","ord_item_use"=>"n","repay_item_use"=>"n");
		if(!$sms_goods_limit){
			$sms_goods_limit = $sms_goods_limit_default;
		}
		$this->template->assign($sms_goods_limit);
		## 발송시간 제한 관련 시작
		$restriction     = $this->membermodel->get_sms_restriction();
		if($restriction[1]){
			if($restriction_msg) $restriction_msg = "<br />".$restriction_msg;
			foreach($restriction[1] as $key=>$item){
			   if(is_array($item)){
					foreach($item as $key2=>$opt){

						 if($opt['use'] == "y"){
							  ## 관리자 행위(액션) 제한 시
							  if($opt['ac_admin'] && $sms_rest[$key2] == "checked"){

								   $opt['ac_admin']     = $opt['ac_admin'].$restriction_msg;
								   if($opt['ac_system']) {
										$opt['ac_system']     = $opt['ac_system'].$restriction_msg;
								   }else{
										$opt['ac_system'] = '';
								   }

								   if($opt['tg_customer'])	$opt['tg_customer']	= "<span style='color:#d90000'>●</span>";
								   if($opt['tg_admin'])	$opt['tg_admin']		= "<span style='color:#d90000'>●</span>";
								   if($opt['tg_seller'])	$opt['tg_seller']	= "<span style='color:#d90000'>●</span>";
								   $opt['setting'] = "y";

							  }else{

								   $tmp_rest[$key][$key2]['ac_admin'] = $opt['ac_admin'];

								   if($opt['tg_customer'])	$opt['tg_customer']	= "<span style='color:#696969'>●</span>";
								   if($opt['tg_admin'])		$opt['tg_admin']	= "<span style='color:#696969'>●</span>";
								   if($opt['tg_seller'])	$opt['tg_seller']	= "<span style='color:#696969'>●</span>";
								   $opt['setting'] = "n";
							  }
							  $tmp_rest[$key][$key2] = $opt;
						 }else{
							  $tmp_rest[$key][$key2] = $opt;
						 }
					}
			   }else{
					$tmp_rest[$key] = $item;
			   }
			}
		}

		if(!$_GET['no']) $_GET['no'] = 1;

		$this->template->assign('restriction_title',$restriction[0]);
		$this->template->assign('restriction_item',$tmp_rest);
		## 발송시간제한 관련 끝.

		$auth = config_load('master');
		$sms_chk	= commonCountSMS();

		$this->template->assign('tab1','-on');
		$this->template->assign(array('send_num'=>$send_num,'admins_arr'=>$admins_arr,'chk'=>$sms_chk,'sms_auth'=>$auth['sms_auth']));
		//$this->template->assign(array('sms_arr'=>$sms_arr,'sms_text'=>$sms_text));
		$this->template->define('top_menu',$this->skin.'/member/top_menu.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## SNS 발송시간 제한 설정
	public function sms_restriction(){

	  $this->admin_menu();
	  $this->tempate_modules();
	  $file_path     = $this->template_path();
	  $this->load->model('membermodel');

	  $restriction = $this->membermodel->get_sms_restriction();

	  $this->template->assign('restriction_title',$restriction[0]);
	  $this->template->assign('restriction_item',$restriction[1]);

	  ## 설정/예약 시간대
	  $loop_reserve_time = array('10'=>'10분','20'=>'20분','30'=>'30분','60'=>'1시간','120'=>'2시간','180'=>'3시간','240'=>'4시간','300'=>'5시간');

	  $loop_config_time = array();
	  for($i=1; $i<=24;$i++) $loop_config_time[] = ((int)$i<10) ? "0".(int)$i:$i;

	  $sms_rest = array();
	  $sms_restriction = config_load('sms_restriction');
	  if($_GET['mode'] == "board"){
		   ## 게시판 SMS 발송시간 제한 설정
		   $sms_rest = $sms_restriction;
		   if($_GET['first'] && !($sms_rest['board_time_s'] && $sms_rest['board_time_e'] && $sms_rest['board_reserve_time'])){
				$selected['config_time_s']['21']     = "selected";
				$selected['config_time_e']['08']     = "selected";
				$selected['reserve_time']['10']      = "selected";
		   }else{
				$selected['config_time_s'][$sms_rest['board_time_s']]          = "selected";
				$selected['config_time_e'][$sms_rest['board_time_e']]          = "selected";
				$selected['reserve_time'][$sms_rest['board_reserve_time']]     = "selected";
		   }
		   $config_field = array("board_time_s","board_time_e","board_reserve_time");
	  }else{
		   ## 일반 SMS 발송시간 제한 설정
		   if($_GET['first'] && !$sms_restriction){
				$selected['config_time_s']['21']     = "selected";
				$selected['config_time_e']['08']     = "selected";
				$selected['reserve_time']['10']          = "selected";
		   }else{
				foreach($sms_restriction as $k=>$v){
					 $tmp = explode("__",$k);
					 if($v == "on"){ $v = "checked"; }
					 if(count($tmp) > 1){
						  $tmp[0] = str_replace("admin_","",$tmp[0]);
						  $sms_rest[$tmp[0]][$tmp[1]] = $v;
					 }else{
						  $sms_rest[$k] = $v;
					 }
				}
				$selected['config_time_s'][$sms_rest['config_time_s']]     = "selected";
				$selected['config_time_e'][$sms_rest['config_time_e']]     = "selected";
				$selected['reserve_time'][$sms_rest['reserve_time']]     = "selected";
		   }
		   $config_field = array("config_time_s","config_time_e","reserve_time");
	  }
	  $this->template->assign(array('loop_config_time'=>$loop_config_time,'loop_reserve_time'=>$loop_reserve_time,'selected'=>$selected));
	  $this->template->assign(array('sms_rest'=>$sms_rest,'config_field'=>$config_field));
	  $this->template->define(array('tpl'=>$file_path));
	  $this->template->print_("tpl");
	}


	public function default_sms_msg(){
		$msg_arr			= parse_ini_file(APPPATH."config/_default_sms_msg.ini", true);
		$result['user']		= $msg_arr[$_GET['type'].'_user'];
		$result['admin']	= $msg_arr[$_GET['type'].'_admin'];

		echo json_encode($result);
	}

	public function sms_charge()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$auth = config_load('master');
		$sms_id = $this->config_system['service']['sms_id'];
		$sms_api_key = $auth['sms_auth'];

		$params	= "sms_id=" . $sms_id . '&sms_pw=' . md5($sms_id);
		$params = makeEncriptParam($params);
		$limit	= commonCountSMS();

		$sms_chk = $sms_id;

		if($_GET['sc_gb'] == "PERSONAL"){
			$this->template->assign('tab5','-on');
		}else{
			$this->template->assign('tab2','-on');
		}
		$this->template->assign(array('count'=>$limit,'param'=>$params,'chk'=>$sms_chk));
		$this->template->define('top_menu',$this->skin.'/member/top_menu.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sms_payment(){
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$mall_id	= isset($mall_id) ? $mall_id : $this->config_system['service']['cid'];
		$domain		= isset($this->config_system['subDomain']) ? $this->config_system['subDomain'] : $_SERVER['SERVER_NAME'];
		$arr_domain	= explode(".", $domain);
		if ($arr_domain[0]=="www") {
			$domain = substr($domain, 4);
		}
		$sms_call = "req_domain=".$domain."&req_mallid=".$mall_id."&req_type=SMS&req_url=/myhg/mylist/spec/firstmall/sms/index.php";
		$sms_call = makeEncriptParam($sms_call);

		$this->template->assign('param',$sms_call);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sms_history()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();


		$config_system = $this->config_system['service'];
		$sms_id=$this->config_system['service']['sms_id'];

		$sms_api_key=$this->config_system['service']['sms_api_key'];

		if($sms_api_key){
			$sms_chk = "true";
		}else{
			$sms_chk = "false";
		}

		###
		if($_GET['tran_phone']) $today = "";
		$this->template->assign('tran_phone',$_GET['tran_phone']);
		$this->template->assign('tab3','-on');
		$this->template->assign(array('maxDay'=>$maxDay,'today'=>$today,'sms_id'=>$sms_id,'chk'=>$sms_chk,'sms_auth'=>$auth['sms_auth']));
		$this->template->define('top_menu',$this->skin.'/member/top_menu.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function sms_auth()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/sms.class.php";

		$auth = config_load('master');
		$sms_id = $this->config_system['service']['sms_id'];
		$sms_api_key = $auth['sms_auth'];

		//$sms_send	= new SMS_SEND();
		$gabiaSmsApi = new gabiaSmsApi($sms_id,$sms_api_key);

		if($_GET['sc_gb'] == "PERSONAL"){
			$this->template->assign('tab6','-on');
		}else{
			$this->template->assign('tab4','-on');
		}
		$this->template->assign(array('sms_id'=>$sms_id,'sms_auth'=>$auth['sms_auth'],'auth_date'=>$auth['auth_date']));
		$this->template->define('top_menu',$this->skin.'/member/top_menu.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	# 개인맞춤형알림 세팅 및 충전
	public function sms_setting()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/sms.class.php";

		$auth = config_load('master');
		$sms_id = $this->config_system['service']['sms_id'];
		$sms_api_key = $auth['sms_auth'];

		//$sms_send	= new SMS_SEND();
		$gabiaSmsApi = new gabiaSmsApi($sms_id,$sms_api_key);
		$sms_info = array('sms_id'=>$sms_id,'sms_auth'=>$auth['sms_auth'],'auth_date'=>$auth['auth_date']);

		$this->template->assign($sms_info);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	### 이메일발송관리
	public function email()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		### 공통
		$group_name					= array('', '공통');
		$email_group[1]['name']		= array('order', 'settle', 'join', 'withdrawal', 'findid', 'findpwd', 'cs', 'promotion');
		$email_group[1]['title']	= array('주문접수', '결제확인', '회원가입', '회원탈퇴', '아이디찾기', '비밀번호찾기', '1:1문의', '프로모션코드발급');
		$email_group[1]['colspan']	= array('', '', '', '', '', '', '');

		### 실물상품
		$group_name[2]	= '실물 배송 상품';
		$email_group[2]['name']		= array('released', 'delivery', 'cancel', 'refund');
		$email_group[2]['title']	= array('출고완료', '배송완료', '결제취소', '환불완료');
		$email_group[2]['colspan']	= array('2', '2', '', '3');


		### 쿠폰상품
		$group_name[3]	= '쿠폰 발송 상품';
		$email_group[3]['name']		= array('coupon_released', 'coupon_delivery', 'coupon_cancel', 'coupon_refund');
		$email_group[3]['title']	= array('출고완료(쿠폰발송)', '배송완료(쿠폰사용)', '결제취소', '환불완료(취소환불,미사용쿠폰환불)');
		$email_group[3]['colspan']	= array('2', '2', '', '3');

		$email = config_load('email');
		foreach ($email_group as $k => $data){
			$email_arr		= $data['name'];
			$email_text		= $data['title'];
			$email_colspan	= $data['colspan'];
			$email_cnt		= count($email_text);

			for($i = 0; $i < $email_cnt; $i++){
				###
				$name			= $email_arr[$i];
				$v['name']		= $name;
				$v['text']		= $email_text[$i];
				$v['col']		= $email_colspan[$i];
				$v['user_chk']	= 'N';
				$v['admin_chk']	= 'N';
				###

				if(isset($email[$name."_user_yn"]))
					$v['user_chk']	= $email[$name."_user_yn"];
				if(isset($email[$name."_admin_yn"]))
					$v['admin_chk']	= $email[$name."_admin_yn"];

				$loop[$k]['list'][]		= $v;
			}
			if	($perline < $email_cnt)
				$perline	= $email_cnt;
		}

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$this->template->assign('group_name',$group_name);
		$this->template->assign('perline',$perline);
		$this->template->assign('email',$basic['companyEmail']);
		$this->template->assign('loop',$loop);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function email_history()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		###
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';
		$sc['sc_gb']			= strtoupper($_GET['sc_gb']);

		###
		$this->load->model('membermodel');
		$data = $this->membermodel->email_history_list($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		//$sc['totalcount']	 = get_rows('fm_log_email');
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		$this->template->assign('sc_gb',$sc['sc_gb']);
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$this->template->assign('tab2','-on');
		$this->template->define('top_menu',$this->skin.'/member/top_menu.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	### 이메일대량발송
	public function amail()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		###
		$email_mass = config_load('email_mass');
		$email_mass['phoneArr']		= explode("-",$email_mass['phone']);
		$email_mass['mobileArr']	= explode("-",$email_mass['cellphone']);

		$this->template->assign($email_mass);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function amail_send()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		for ($m=1;$m<=12;$m++){	$m_arr[] = str_pad($m, 2, '0', STR_PAD_LEFT); }
		for ($d=1;$d<=31;$d++){	$d_arr[] = str_pad($d, 2, '0', STR_PAD_LEFT); }
		$this->template->assign('m_arr',$m_arr);
		$this->template->assign('d_arr',$d_arr);

		## 유입경로 그룹
		$this->load->model('statsmodel');
		$referer_list	= $this->statsmodel->get_referer_grouplist();
		$this->template->assign('referer_list',$referer_list);


		//가입양식
		$ruteloop = memberrute($_GET['rute'], 'image', 'array');
		$this->template->assign('ruteloop',$ruteloop);


		#### AUTH
		$auth_act		= $this->authmodel->manager_limit_act('member_act');
		if(isset($auth_act)) $this->template->assign('auth_act',$auth_act);
		$auth_promotion = $this->authmodel->manager_limit_act('member_promotion');
		if(isset($auth_promotion)) $this->template->assign('auth_promotion',$auth_promotion);
		$auth_send	= $this->authmodel->manager_limit_act('member_send');
		if(isset($auth_send)) $this->template->assign('auth_send',$auth_send);

		###
		$cid = preg_replace("/gabia-/","", $this->config_system['service']["cid"]);
		$email_mass = config_load('email_mass');
		$email_mass['cid']			= $cid;
		$email_mass['phoneArr']		= explode("-",$email_mass['phone']);
		$email_mass['mobileArr']	= explode("-",$email_mass['cellphone']);
		$email_mass['server_name']	= $_SERVER["SERVER_NAME"];
		$this->template->assign('mass',$email_mass);

		###
		$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$this->template->assign('mInfo',$mInfo);

		### GROUP
		$this->load->model('membermodel');
		$group_arr = $this->membermodel->find_group_list();

		### SEARCH
		//print_r($_POST);
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'member_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

 		if( $_GET['snsrute'] ) {
			foreach($_GET['snsrute'] as $key=>$val){$sc[$val] = 1;}
		}

		### MEMBER
		$data = $this->membermodel->admin_member_list($sc);

		### PAGE & DATA
		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$sc['totalcount']	 = get_rows('fm_member',array('status !='=>'withdrawal'));
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;

			$adddata = $this->membermodel->get_member_seq_only($datarow['member_seq']);
			$datarow['email'] = $adddata['email'];
			$datarow['phone'] = $adddata['phone'];
			$datarow['cellphone'] = $adddata['cellphone'];
			$datarow['group_name'] = $adddata['group_name'];

			$datarow['type']	= $datarow['business_seq'] ? '기업' : '개인';
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			if($datarow['business_seq']){
				$datarow['user_name'] = $datarow['bname'];
				$datarow['cellphone'] = $datarow['bcellphone'];
				$datarow['phone'] = $datarow['bphone'];
			}


			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';
		$this->template->assign('pagin',$paginlay);



		$this->template->assign('group_arr',$group_arr);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);
		$this->template->assign('amail','Y');

		$this->template->define('member_list',$this->skin.'/member/member_list.html');
		$this->template->define('member_search',$this->skin.'/member/member_search.html');
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");

		###
		if(!$email_mass['name'] && !$email_mass['email']){
			$callback = "<script>openDialog('이메일 대량 발송 설정','amail_chk',{'width':'300','height':'120'});</script>";
			echo $callback;
			exit;
		}
	}


	public function sms_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$table = !empty($_GET['table']) ? $_GET['table'] : 'fm_member';
		$this->template->assign('table',$table);

		###
		if($table=='fm_goods_restock_notify'){
			$mInfo['total'] = get_rows('fm_goods_restock_notify',array('notify_status'=>'none'));
			$action = "../goods_process/restock_notify_send_sms";
			$this->template->assign('action',$action);

			$this->template->assign('send_message',"[{$this->config_basic['shopName']}] 고객님께서 알림요청하신 상품({상품고유값},{상품명},{옵션})이 재입고되었습니다.");
		}else{
			$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
			$action = "../member_process/send_sms";
			$this->template->assign('action',$action);
		}
		$specialArr	= array('＃', '＆', '＊', '＠', '§', '※', '☆', '★', '○', '●', '◎', '◇', '◆', '□', '■', '△', '▲', '▽', '▼', '→', '←', '↑', '↓', '↔', '〓', '◁', '◀', '▷', '▶', '♤', '♠', '♡', '♥', '♧', '♣', '⊙', '◈', '▣', '◐', '◑', '▒', '▤', '▥', '▨', '▧', '▦', '▩', '♨', '☏', '☎', '☜', '☞', '¶', '†', '‡', '↕', '↗', '↙', '↖', '↘', '♭', '♩', '♪', '♬', '㉿', '㈜', '№', '㏇', '™', '㏂', '㏘', '℡', '?', 'ª', 'º');
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');

		$sms_info = config_load('sms_info','send_num');
		if($sms_info['send_num']) $send_num = $sms_info['send_num'];

		###
		$sql = "select count(seq) as total, category from fm_sms_album group by category";
		$query = $this->db->query($sql);
		$sms_data = $query->result_array();
		$sms_total = get_rows('fm_sms_album');
		array_push($sms_data,array('total'=>$sms_total,'category'=>'전체보기'));
		rsort($sms_data);

		
		$sms_id = $this->config_system['service']['sms_id'];
		$limit	= commonCountSMS();
		$sms_chk = $sms_id;

		$this->template->assign('count',$limit);
		$this->template->assign(array('mInfo'=>$mInfo,'number'=>($send_num)? $send_num : $basic['companyPhone']));
		$this->template->assign(array('sms_loop'=>$sms_data,'sms_total'=>$sms_total,'sms_cont'=>$specialArr,'chk'=>$sms_chk));
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function emoney_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		###
		$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		$this->template->assign('mInfo',$mInfo);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function point_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		###
		$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));
		$this->template->assign('mInfo',$mInfo);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function email_form()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$master = config_load('master');

		###
		$query = $this->db->query("select * from fm_log_email order by seq desc limit 10");
		$emailData = $query->result_array();

		###
		$this->load->model('usedmodel');
		$email_chk = $this->usedmodel->hosting_check();
		$this->template->assign('email_chk',$email_chk);

		###
		$mInfo['total'] = get_rows('fm_member',array('status !='=>'withdrawal'));

		$this->template->assign(array('mail_count'=>$master['mail_count'],'email'=>$basic['companyEmail']));
		$this->template->assign('mInfo',$mInfo);
		$this->template->assign('loop',$emailData);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}
	public function delivery(){
		$file_path	= $this->template_path();
		$this->template->assign('member_seq',$_GET['member_seq']);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function delivery_address(){
		//login_check();
		$this->admin_menu();
		$this->tempate_modules();
		$this->load->helper('shipping');
		$file_path	= $this->template_path();

		$sc=array();
		$sc['page']				= (!empty($_GET['page'])) ?		intval($_GET['page']):'1';
		$sc['perpage']			= $perpage ? $perpage : 10;
		$list_order=$_GET['order'];

		switch($list_order){
			case 'desc_up' :
				$orderby='address_description asc';
				break;
			case 'desc_dn' :
				$orderby='address_description desc';
				break;
			case 'name_up' :
				$orderby='recipient_user_name asc';
				break;
			case 'name_dn' :
				$orderby='recipient_user_name desc';
				break;
			case 'name_dn' :
				$orderby='address_seq desc';
				break;
			default :
				$orderby='address_seq desc';
				break;
		}

		$shipping = use_shipping_method();
		if( $shipping ){
			foreach($shipping as $key => $data){
				if($data) $shipping_cnt[$key] = count($data);
			}
		}
		$shipping_policy['policy'] 	= $shipping;
		$shipping_policy['count'] 	= $shipping_cnt;

		$deli_cnt = count($shipping_policy['policy']);

		$member_seq = $_GET['member_seq'];

		$tab=$_GET['tab'];
		$key = get_shop_key();

		$popup=$_GET['popup'];

		$sql="select *,
				AES_DECRYPT(UNHEX(recipient_phone), '{$key}') as recipient_phone,
				AES_DECRYPT(UNHEX(recipient_cellphone), '{$key}') as recipient_cellphone
			from fm_delivery_address ";

		if($popup == '1'){
			if($tab=='2'){
				if($deli_cnt < 2){
					$sql .= " where member_seq=".$member_seq." and lately='Y' and international ='domestic' order by ".$orderby." limit 30";
				}else{
					$sql .= " where member_seq=".$member_seq." and lately='Y' order by ".$orderby." limit 30";
				}
			}else{
				if($deli_cnt < 2){
					$sql .= " where member_seq=".$member_seq." and often='Y' and international ='domestic' order by ".$orderby." limit 30";
				}else{
					$sql .= "  where member_seq=".$member_seq." and often='Y'  order by ".$orderby." limit 30";
				}
			}
			$query = $this->db->query($sql);
			$result['record'] = $query -> result_array();
		}else{
			if($tab=='2'){
				if($deli_cnt < 2){
					$sql .= " where member_seq=".$member_seq." and lately='Y' and international ='domestic' order by ".$orderby;
				}else{
					$sql .= " where member_seq=".$member_seq." and lately='Y' order by ".$orderby;
				}
			}else{
				if($deli_cnt < 2){
					$sql .= " where member_seq=".$member_seq." and often='Y' and international ='domestic' order by ".$orderby;
				}else{

					$sql .= " where member_seq=".$member_seq." and often='Y'  order by ".$orderby;
				}
			}
			$result = select_page($sc['perpage'],$sc['page'],10,$sql,array());

		}

		foreach($result['record'] as $data){
			if($data['international'] == 'domestic'){
				$international_show = '국내';
			}elseif($data['international'] == 'international'){
				$international_show = '해외';
			}
			$data['international_show'] = $international_show;
			$loop[] = $data;
		}


		$this->template->assign('shop_shipping_policy',$cart['shop_shipping_policy']);
		$this->template->assign('shipping_policy',$shipping_policy);
		$this->template->assign('loop',$loop);
		$this->template->assign('member_seq',$member_seq);
		$this->template->assign($result);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## 사용중인 SNS 정보보기(2014-07-01)
	public function sns_detail(){

		$this->admin_menu();
		$this->tempate_modules();
		$this->load->model('snsmember');
		$file_path	= $this->template_path();
		$this->template->assign('member_seq',$_GET['member_seq']);
		$this->template->assign('snscd',$_GET['snscd']);
		$this->template->assign('no',$_GET['no']);

		$sql = "select rute,user_name,email
						,(case when sex='famale' then '여자' when sex='male' then '남자' else '' end) sex
						,(case when ifnull(birthday,'0000-00-00')!='0000-00-00' then birthday else '' end ) birthdayV
					from 
						fm_membersns
					where
						member_seq ='".$_GET['member_seq']."' and rute='".$_GET['snscd'] ."'";
		$query	= $this->db->query($sql);
		$result = $query -> result_array();

		if(!$result[0]) $result[0]['message'] = "연동 해제된 계정입니다.";

		$result[0]['rute_nm'] = $this->snsmember->snstype_name($_GET['snscd']);

		if($result[0]['rute'] == "facebook"){
			$sql	= "select sns_f from fm_member where member_seq='".$_GET['member_seq']."'";
			$query2	= $this->db->query($sql);
			$result2 = $query2->result_array();
			$result[0]['sns_f'] = $result2[0]['sns_f'];
		}
		$this->template->assign('data',$result[0]);

		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function replace_pop()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$this->load->model('membermodel');

		if($_GET['mode'] == "curation"){
			$title			= "SMS/메일";
			$replaceText	= $this->membermodel->get_replacetext('curation');
		}else{
			$title			= "메일";
			$replaceText	= $this->membermodel->get_replacetext();
		}

		$this->template->assign('title', $title);
		$this->template->assign('replaceText', $replaceText);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	/* 엑셀 다운로드 항목설정 */
	public function download_write(){
		$this->load->model('excelmembermodel');
		$itemList 	= $this->excelmembermodel->itemList;

		$this->template->assign('itemList',$itemList);
		$requireds 	= $this->excelmembermodel->requireds;
		$this->template->assign('requireds',$requireds);

		$data = get_data("fm_exceldownload",array("gb"=>'MEMBER'));
		$item = $data ? explode("|",$data[0]['item']) : array();
		$this->template->assign('items',$item);

		$this->template->assign($data[0]);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## 고객리마인드서비스 설정
	public function curation(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->load->helper('reservation');

		### 큐레이션 발송 구분
		$loop	= curation_menu();

		$_GET['sc_gb'] = "PERSONAL";
		## 전월 총 일수
		$month_t = date("t",strtotime(date("Y-m-d H:i:s")." -1 month"));
		$loop_day = array();
		for($i=1; $i<$month_t;$i++) $loop_day[] = ((int)$i<10) ? "0".(int)$i:$i;

		## 예약 시간대
		$loop_time = array();
		for($i=1; $i<=24;$i++) $loop_time[] = ((int)$i<10) ? "0".(int)$i:$i;

		$goodsname_length	= config_load('personal_goods_limit');
		$go_item_limit		= $goodsname_length['go_item_limit'];
		$go_item_use		= $goodsname_length['go_item_use'];

		/* 짧은 url 설정에 따른 안내 문구 추가 leewh 2014-12-04 */
		$set_url = true;
		$set_string = "";
		if (empty($this->arrSns['shorturl_app_id']) && empty($this->arrSns['shorturl_app_key'])) {
			$set_url = false;
			$set_string = "설정이 필요";
		}

		$shorturl_test = 'http://'.$this->config_system['domain'].'/personal_referer/access?inflow=shorturl&mid=1';
		$shorturl		= get_shortURL($shorturl_test);

		if (in_array($shorturl, array("INVALID_LOGIN","INVALID_APIKEY"))) {
			$shorturl = "http://bit.ly/xxxxxxxx";
			if ($set_url) {
				$set_string = "제대로 설정되지 않았습니다. ‘설정’ 을 확인해 주세요";
			}
		}

		if(!$go_item_limit) $go_item_limit = 20;
		$this->template->assign('tab1','-on');
		$this->template->assign('go_item_limit',$go_item_limit);
		$this->template->assign('go_item_use',$go_item_use);
		$this->template->assign(array('sns'=>$this->arrSns));
		$this->template->assign('loop',$loop);
		$this->template->assign('shorturl_test',$shorturl_test);
		$this->template->assign('shorturl',$shorturl);
		$this->template->assign('set_string',$set_string);
		$this->template->define(array('tpl'=>$file_path,'top_menu'=>$this->skin.'/member/top_menu.html','shorturl_setting'=>$this->skin."/setting/snsconf_shorturl_setting.html"));
		$this->template->print_("tpl");
	}

	## 리마인드 SMS발송내역
	public function  curation_history_sms(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->load->helper('reservation_helper');
		$this->load->model('membermodel');

		$curation_menu = curation_menu();
		foreach($curation_menu as $v){
			$loop = array();
			$tmp	= explode("_",$v['name']);
			$loop['name'] = $tmp[1];
			$loop['title'] = $v['title'];

			$curationmn[] = $loop;
		}

		$_GET['sc_gb'] = "PERSONAL";

		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

		###
		$data = $this->membermodel->curtion_history_sms($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			$datarow['kind_name'] = "";
			foreach($curationmn as $v){
				if($v['name']==$datarow['kind']){
					$datarow['kind_name'] = $v['title'];
				}
			}
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('curationmn',$curationmn);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$this->template->assign('tab2','-on');
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$this->template->define(array('tpl'=>$file_path,'top_menu'=>$this->skin.'/member/top_menu.html'));
		$this->template->print_("tpl");
	}

	## 리마인드 Email 발송내역
	public function  curation_history_email(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->load->helper('reservation_helper');
		$this->load->model('membermodel');

		$curation_menu = curation_menu();
		foreach($curation_menu as $v){
			$loop = array();
			$tmp	= explode("_",$v['name']);
			$loop['name'] = $tmp[1];
			$loop['title'] = $v['title'];

			$curationmn[] = $loop;
		}

		$_GET['sc_gb'] = "PERSONAL";

		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'10';

		###
		$data = $this->membermodel->curtion_history_email($sc);

		$sc['searchcount']	 = $data['count'];
		$sc['total_page']	 = ceil($sc['searchcount']	 / $sc['perpage']);
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;

			$datarow['kind_name'] = "";
			foreach($curationmn as $v){
				if($v['name']==$datarow['kind']){
					$datarow['kind_name'] = $v['title'];
				}
			}
			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		$this->template->assign('pagin',$paginlay);

		$this->template->assign('curationmn',$curationmn);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$this->template->assign('tab3','-on');
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$this->template->define(array('tpl'=>$file_path,'top_menu'=>$this->skin.'/member/top_menu.html'));
		$this->template->print_("tpl");
	}

	## 리마인드 유입통계 상세
	public function curation_stat_detail(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$this->load->helper('reservation_helper');
		$this->load->model('membermodel');

		$curation_menu = curation_menu();
		foreach($curation_menu as $v){
			$loop = array();
			$tmp	= explode("_",$v['name']);
			$loop['name'] = $tmp[1];
			$loop['title'] = $v['title'];

			$curationmn[] = $loop;
		}

		if($_GET['sc_type'] == "all") $_GET['sc_type'] = "";
		$sc = $_GET;
		$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'c.member_seq';
		$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
		$sc['page']				= (isset($_GET['page'])) ?		intval($_GET['page']):0;
		$sc['perpage']			= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):10;

		if($_GET['first']){
			if(!$sc['start_date2']){
				if($_GET['start_date']){
					$sc['start_date2'] = $_GET['start_date'];
				}else{
					$mktime				= strtotime(date("Y-m-d H:i:s")." -7 days");
					$sc['start_date2']	= date("Y-m-d",$mktime);
				}
			}
			if(!$sc['end_date2']){
				if($_GET['end_date']){
					$sc['end_date2'] = $_GET['end_date'];
				}else{
					$sc['end_date2']	= date("Y-m-d",mktime());
				}
			}
		}

		$data = $this->membermodel->curation_stat_detail($sc);
		$sc['searchcount']	 = $data['count'];
		$idx = 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']	= $sc['searchcount']	 - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$dataloop[] = $datarow;
		}

		foreach($curationmn as $v){
			if($v['name'] == $sc['sc_kind']) $kind_name = $v['title'];
		}

		if(!$sc['sc_type']){
			$sc_type = "SMS/EMAIL";
		}else{
			$sc_type = $sc['sc_type'];
		}
		if(!$sc['sc_kind']) $kind_name = "전체";
		$detail_title = "<span style='font-weight:bold;'>'".$kind_name."'</span>으로 발송된 <span style='font-weight:bold;'>".$sc_type."</span> 유입 ".number_format($sc['searchcount'])."건에 대한 상세 내역입니다.";

		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );

		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';

		$this->template->assign('detail_title',$detail_title);
		$this->template->assign('curationmn',$curationmn);
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		if(isset($data)) $this->template->assign('loop',$dataloop);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	## 리마인드 유입통계
	public function curation_stat(){

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->load->model('membermodel');
		$this->load->helper('reservation');

		### 큐레이션 발송 구분
		$curationmn	= curation_menu();

		$sc = $_GET;
		if($_GET['first']){
			if(!$sc['start_date']){
				$mktime				= strtotime(date("Y-m-d H:i:s")." -7 days");
				$sc['start_date']	= date("Y-m-d",$mktime);
			}
			if(!$sc['end_date']){
				$sc['end_date']	= date("Y-m-d",mktime());
			}
		}
		$data = $this->membermodel->curation_stat($sc);

		## 차트 기초 데이터 생성
		$inflow_kind		= array();
		foreach($data as $item){ $inflow_kind[] = $item['inflow_kind']; }
		$inflowDefault = array();
		$inflowDefault['inflow_sms_total']		= 0;
		$inflowDefault['inflow_email_total']	= 0;
		$inflowDefault['send_sms_total']		= 0;
		$inflowDefault['send_email_total']		= 0;
		$inflowDefault['login_cnt']				= 0;
		$inflowDefault['goodsview_cnt']			= 0;
		$inflowDefault['cart_cnt']				= 0;
		$inflowDefault['wish_cnt']				= 0;
		$inflowDefault['order_cnt']				= 0;
		if(!in_array("coupon",$inflow_kind)){ $inflowDefault['inflow_kind'] = "coupon"; $data[] = $inflowDefault; }
		if(!in_array("emoney",$inflow_kind)){ $inflowDefault['inflow_kind'] = "emoney"; $data[] = $inflowDefault; }
		if(!in_array("membership",$inflow_kind)){ $inflowDefault['inflow_kind'] = "membership"; $data[] = $inflowDefault; }
		if(!in_array("cart",$inflow_kind)){ $inflowDefault['inflow_kind'] = "cart"; $data[] = $inflowDefault; }
		if(!in_array("timesale",$inflow_kind)){ $inflowDefault['inflow_kind'] = "timesale"; $data[] = $inflowDefault; }
		if(!in_array("review",$inflow_kind)){ $inflowDefault['inflow_kind'] = "review"; $data[] = $inflowDefault; }

		/* 데이터 가공 */
		$maxValue = 0;
		$maxMonth = 12;

		$dataInflowChart	= array();
		$dataLoginChart		= array();
		$dataloop			= array();
		foreach($data as $item){

			if($item['send_sms_total']>0 && $item['inflow_sms_total']>0){
				$item['sms_stat_per'] = floor($item['inflow_sms_total']/$item['send_sms_total']*100);
			}else{
				$item['sms_stat_per'] = 0;
			}
			if($item['send_email_total']>0 && $item['inflow_email_total']>0){
				$item['email_stat_per'] = floor($item['inflow_email_total']/$item['send_email_total']*100);
			}else{
				$item['email_stat_per'] = 0;
			}
			if(!$item['login_cnt'])		$item['login_cnt']		= '0';
			if(!$item['goodsview_cnt']) $item['goodsview_cnt']	= '0';

			foreach($curationmn as $v){
				if(strstr($v['name'],$item['inflow_kind'])){
					$item['kind_name'] = $v['title'];
				}
			}
			## 접속, 로그인,상품뷰,위시리스트,장바구니,구매 : 순서 지킬 것.
			$KindLoop = array();
			$KindLoop[0]		= ($item['inflow_sms_total']+$item['inflow_email_total']);
			$KindLoop[1]		= $item['login_cnt'];
			$KindLoop[2]		= $item['goodsview_cnt'];
			$KindLoop[3]		= $item['cart_cnt'];
			$KindLoop[4]		= $item['wish_cnt'];
			$KindLoop[5]		= $item['order_cnt'];

			$inflowLoop			= array($item['kind_name'],($item['inflow_sms_total']+$item['inflow_email_total']));
			$dataInflowChart[]	= $inflowLoop;

			$LoginLoop			= array($item['kind_name'],$item['login_cnt']);
			$dataLoginChart[]	= $LoginLoop;

			$OrderLoop			= array($item['kind_name'],$item['order_cnt']);
			$dataOrderChart[]	= $OrderLoop;

			$dataKind[$item['inflow_kind']]['data']	= $KindLoop;
			$dataKind[$item['inflow_kind']]['max']	= $item['send_sms_total'] + $item['send_email_total'];
			$dataKind[$item['inflow_kind']]['lable']= $item['kind_name'];

			$dataloop[]			= $item;


		}
		$this->seriesColors1 = array("#75c8b4", "#c3b8f3", "#f383c9", "#c4b5e6","#d8f27b", "#a5aef1");
		$this->seriesColors2 = array("#445ebc", "#d33c34","#4bb2c5", "#c5b47f", "#EAA228", "#579575");

		$this->template->assign(array('seriesColors1'=>$this->seriesColors1,'seriesColors2'=>$this->seriesColors2));
		$_GET['sc_gb'] = "PERSONAL";
		###
		$this->template->assign('tab4','-on');
		$this->template->assign('sc',$sc);
		
		$this->template->assign(array(
			'dataKind'	=> $dataKind,
			'maxValue'		=> $maxValue
		));
		$this->template->assign(array('dataInflowChart'=>$dataInflowChart,'dataLoginChart'=>$dataLoginChart,'dataOrderChart'=>$dataOrderChart));
		if(isset($dataloop)) $this->template->assign('loop',$dataloop);
		$this->template->define(array('tpl'=>$file_path,'top_menu'=>$this->skin.'/member/top_menu.html'));
		$this->template->print_("tpl");
	}

}

/* End of file member.php */
/* Location: ./app/controllers/admin/member.php */