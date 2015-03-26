<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class provider extends admin_base {
	public function __construct() {
		parent::__construct();
	}

	public function index(){
		redirect("/admin/provider/catalog");
	}

	/* 입점사 */
	public function catalog(){

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('provider_view');
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
		$this->load->model('membermodel');
		$this->load->model('providermodel');

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('minishop');
		if(!$result['type']){
			$this->template->assign('minishop_service_limit','Y');
		}

		### SEARCH
		$sc					= $_GET;
		$sc['orderby']		= (isset($_GET['orderby']))	? $_GET['orderby']	: 'A.regdate';
		if($sc['orderby'] == 'A.regdate')	$sc['sort']	= 'desc';
		else								$sc['sort']	= (isset($_GET['sort'])) ? $_GET['sort'] : 'asc';
		$sc['page']			= (isset($_GET['page'])) ?		intval($_GET['page']):'0';
		$sc['perpage']		= (isset($_GET['perpage'])) ?	intval($_GET['perpage']):'20';
		$sc['get_mshop']	= 'y';
		$data	= $this->providermodel->provider_list($sc);

		### PAGE & DATA
		$sc['searchcount']	= $data['count'];
		$sc['total_page']	= ceil($sc['searchcount'] / $sc['perpage']);
		$sc['totalcount']	= get_rows('fm_provider');
		$idx				= 0;
		foreach($data['result'] as $datarow){
			$idx++;
			$datarow['number']		= $sc['searchcount'] - ( ($sc['page'] -1 ) * 1 + $idx + 1) + 1;
			$datarow['provider_gb']	= ($datarow['provider_gb'] == "company") ? "입점(본사)" : "입점(업체)";
			$datarow['deli_group']	= ($datarow['deli_group'] == "company")	? "본사 배송" : "입점사 배송";
			$datarow['mshop_url']	= '/mshop/?m='.$datarow['provider_seq'];
			if($datarow['provider_status'] == 'Y')
				$datarow['provider_status']	= '<span style="color:blue;">정상</sapn>';
			else
				$datarow['provider_status']	= '<span style="color:red;">종료</span>';

			$dataloop[] = $datarow;
		}

		###
		if(isset($data)) $this->template->assign('loop',$dataloop);
		$paginlay = pagingtag($sc['searchcount'],$sc['perpage'],$this->membermodel->admin_member_url($file_path).'?', getLinkFilter('',array_keys($sc)) );
		if(empty($paginlay))$paginlay = '<p><a class="on red">1</a><p>';

		$provider	= $this->providermodel->provider_goods_list();
		
		### 등급리스트
		$group_list			= $this->providermodel->find_group_cnt_list();

		$this->template->assign('provider',$provider);
		$this->template->assign('pagin',$paginlay);
		$this->template->assign('group_list',$group_list);
		$this->template->assign('group_arr',$group_arr);
		$this->template->assign('perpage',$sc['perpage']);
		$this->template->assign('sc',$sc);

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function provider_reg(){
		$this->admin_menu();
		$this->tempate_modules();
		$filePath	= $this->template_path();
		$this->load->model('providermodel');

		$noti_acount_priod = config_load('noti_count');
		if(!$noti_acount_priod['order']) $noti_acount_priod = "6개월";
		if(!$noti_acount_priod['board']) $noti_acount_priod = "6개월";

		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('minishop');
		if(!$result['type']){
			$this->template->assign('minishop_service_limit','Y');
		}

		$calcu_count_limit = $this->usedmodel->get_provider_account_calcu_count();
		$this->template->assign('calcu_count_limit',$calcu_count_limit);

		$provider_grade_list = $this->providermodel->find_group_cnt_list();
		$this->template->assign('pgroup_list',$provider_grade_list);

		if(!isset($_GET['no']) && $this->config_system['service']['code']=='P_ADVL'){
			$limit = $this->usedmodel->get_provider_limit();
			$sql = "select count(*) as cnt from fm_provider where provider_id!='base' and provider_status='Y'";
			$query = $this->db->query($sql);
			$data = $query->row_array();
			if($data['cnt']>=$limit){
				pageBack("입점사는 총 {$limit}개까지 등록하실 수 있습니다.");
				exit;
			}
		}

		### BRAND
		$sql = "select * from fm_brand where length(category_code)=4 and parent_id = 2 order by `left` asc";
		$query = $this->db->query($sql);
		foreach ($query->result_array() as $row){
			$brand[] = $row;
		}
		$this->template->assign('brand',$brand);
		$this->template->assign('brand_cnt',count($brand));

		### MODIFY
		if(isset($_GET['no'])){
			$sql = "select * from fm_provider A left join fm_provider_charge B on A.provider_seq = B.provider_seq and B.link = 1 where A.provider_seq = '{$_GET['no']}'";
			$query = $this->db->query($sql);
			$data = $query->result_array();
			$data[0]['deli_zipcode']		= explode("-",$data[0]['deli_zipcode']);
			$data[0]['info_zipcode']		= explode("-",$data[0]['info_zipcode']);
			$data[0]['main_visual_name']	= basename($data[0]['main_visual']);
			$data[0]['mshop_url']			= '/mshop/?m='.$data[0]['provider_seq'];
			$mshop						= $this->providermodel->get_minishop_count($data[0]['provider_seq']);
			$data[0]['mshop_cnt']			= $mshop['cnt'];

			if($data[0]['limit_ip']){
				$limit_row = explode("|", $data[0]['limit_ip']);
				$count = count($limit_row)-1;
				for($i=0; $i<$count; $i++){
					$limit_ip[] = explode(".", $limit_row[$i]);
				}
				$data[0]['limit_ip'] = $limit_ip;
			}

			$this->template->assign($data[0]);

			### CHARGE
			$sql = "select * from fm_provider_charge where provider_seq = '{$_GET['no']}' and link =0";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$charge[] = $row;
			}
			$this->template->assign('charge_loop',$charge);

			### PERSON
			$person = array('ds1', 'ds2', 'cs', 'calcu', 'md', 'wcalcu');
			foreach($person as $k){
				unset($temp);
				$query = $this->db->query("select * from fm_provider_person where provider_seq = '{$_GET['no']}' and gb = '{$k}'");
				$temp = $query->result_array();
				$this->template->assign($k, $temp[0]);
			}


			$param['provider_seq']	= $_GET['no'];
			$certify				= $this->providermodel->get_certify_manager($param);
			$this->template->assign('certify',$certify);

		}

		$this->template->assign('noti_acount_priod',$noti_acount_priod);
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");
	}

	public function provider_shipping(){
		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));

		if( $_GET['reg']=='Y' ){
			$arr = explode("|",$_GET['company_code']);
			$cnt = 0;
			foreach($arr as $k){
				$tmp = config_load('delivery_url',$k);
				$data['deliveryCompany'][$k]		= $tmp[$k]['company'];
				$data['deliveryCompanyCode'][$cnt]	= $k;
				$cnt++;
			}

			if(!$_GET['company_code']) unset($data['deliveryCompanyCode']);
			###
			$data['summary']				= $_GET['summary'];
			$data['useYn']					= $_GET['use_yn'];
			$data['deliveryCostPolicy']		= $_GET['delivery_type'];
			if($_GET['delivery_type']=='pay'){
				$data['payDeliveryCost']		= $_GET['delivery_price'];
				$data['postpaidDeliveryCost']	= $_GET['post_price'];
				if($_GET['post_price']>0) $data['postpaidDeliveryCostYn'] = 'y';
			}else if($_GET['delivery_type']=='ifpay'){
				$data['ifpayFreePrice']			= $_GET['if_free_price'];
				$data['ifpayDeliveryCost']		= $_GET['delivery_price'];
				$data['ifpostpaidDeliveryCost']	= $_GET['post_price'];
				if($_GET['post_price']>0) $data['ifpostpaidDeliveryCostYn'] = 'y';
			}

			$arr2 = explode("|",$_GET['add_delivery_cost']);
			$cnt = 0;
			foreach($arr2 as $k){
				$tmps = explode(":", $k);
				$tmpsCount = count($tmps);
				if($tmpsCount == 3){
					$data['sigungu'][$cnt]			= $tmps[0];
					$data['sigungu_street'][$cnt]	= $tmps[1];
					$data['addDeliveryCost'][$cnt]	= $tmps[2];
				}else{
					$data['sigungu'][$cnt]			= $tmps[0];
					$data['addDeliveryCost'][$cnt]	= $tmps[1];

				}
				$cnt++;
			}
			/*
			echo "<pre>";
			print_r($data);
			*/

			$this->template->assign($data);
		}

		if( isset($_GET['seq']) && $_GET['seq']!="" ){
			$this->load->model('providershipping');
			$data = $this->providershipping->get_provider_shipping($_GET['seq']);
			$this->template->assign($data);
		}

		$this->template->print_("tpl");
	}

	public function salescost(){
		$this->load->model('providermodel');
		$sc['orderby']	= 'provider_name';
		$sc['sort']		= 'asc';
		$sc['page']		= 0;
		$sc['perpage']	= 9999;
		$provider		= $this->providermodel->provider_list($sc);
		if	($provider){
			foreach($provider['result'] as $k => $data){
				$provider_gb[$data['deli_group']][]	= $data;
			}
		}

		if	($_GET['provider_seq_list']){
			$provider_list	= substr(substr($_GET['provider_seq_list'], 1), 0, -1);
			$provider_arr	= explode('|', $provider_list);
			if	(count($provider_arr) > 0){
				$provider_select_list	= $this->providermodel->get_provider_range($provider_arr);
				if	($provider_select_list){
					foreach($provider_select_list as $k => $data){
						if	($data['deli_group'])	$default_deli_group	= $data['deli_group'];
						$selectedProvider[$data['provider_seq']] = $data['provider_name'];
					}
				}
			}

			$this->template->assign(array('selectedProvider'=>$selectedProvider));
		}

		$this->template->assign(array('default_deli_group'=>$default_deli_group));
		$this->template->assign(array('shippingtype'=>$_GET['shippingtype']));
		$this->template->assign(array('salescost_provider'=>$_GET['salescost_provider']));
		$this->template->assign(array('provider_gb'=>$provider_gb));
		$this->template->assign(array('provider'=>$provider['result']));
		$this->template->assign(array('calltype'=>$_GET['calltype']));
		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function provider_statistic(){

		$this->load->model('providermodel');
		if	(!$_GET['year'])	$_GET['year']	= date('Y');

		$sc				= $_GET;
		$provider_seq	= $_GET['provider_seq'];
		$pageType		= $_GET['pageType'];

		## 실매출 ( 월별 통계 )
		switch($pageType){
			case 'order':
				$order	= $this->providermodel->get_account_order($sc);
				if	($order){
					foreach($order as $k => $data){
						$stats[$data['export']]		= $data['opt_price'] + $data['sub_price'];
					}
				}
			break;
			case 'account':
				$order		= $this->providermodel->get_account_order($sc);
				if	($order){
					foreach($order as $k => $data){
						$sc['date']	= $sc['year'].'-'.$data['export'];
						$shipping	= $this->providermodel->get_account_shipping($sc);
						$refund		= $this->providermodel->get_account_refund($sc);
						$return		= $this->providermodel->get_account_return($sc);

						$price		= $data['opt_price'] + $data['sub_price'];
						$account	= $price + $shipping['shipping_cost'] + $data['goods_shipping_cost'];
						$account	= $account - ($price - $data['commission_price']);
						$account	= $account - $refund['refund_commission_price'];
						$account	= $account + $return['return_shipping_price'];

						$stats[$data['export']]		= $account;
					}
				}
			break;
			case 'charge':
				$order	= $this->providermodel->get_account_order($sc);
				if	($order){
					foreach($order as $k => $data){
						$price		= $data['opt_price'] + $data['sub_price'];
						$charge		= $price - $data['commission_price'];
						$stats[$data['export']]		= $charge;
					}
				}
			break;
			case 'mshop':
				$mshop	= $this->providermodel->get_account_mshop($sc);
				if	($mshop){
					foreach($mshop as $k => $data){
						$stats[$data['date']]		= $data['cnt'];
					}
				}
			break;
		}

		for ($m = 1; $m <= 12; $m++){
			$month	= str_pad($m, 2, '0', STR_PAD_LEFT);
			$value	= ($stats[$month])	? $stats[$month]	: 0;
			$dataForChart[]	= array($month.'월', $value);

			$maxValue	= ($maxValue < $value) ? $value : $maxValue;
		}

		$this->template->assign(array('maxValue'=>$maxValue));
		$this->template->assign(array('dataForChart'=>$dataForChart));

		$file_path		= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	// 확인코드 중복체크 및 유효성 체크
	public function chk_certify_code($cerfify_code = ''){

		$return	= 'ok';

		if	($_GET['certify_code'])
			$certify_code	= trim($_GET['certify_code']);

		if	($_GET['certify_seq'])
			$param['out_seq']	= trim($_GET['certify_seq']);

		if		(!$certify_code)											$return	= 'error_1';
		elseif	(strlen($certify_code) < 6 || strlen($certify_code) > 16)	$return	= 'error_2';
		elseif	(preg_match('/[^0-9a-zA-Z]/', $certify_code))				$return	= 'error_3';

		$this->load->model('providermodel');
		$param['certify_code']	= $certify_code;
		$certify				= $this->providermodel->get_certify_manager($param);
		if	($certify){
			$return	= 'duple';
		}

		if	($_GET['certify_code'])	echo $return;
		else						return $return;
	}

	/* 입점사 등급 */
	public function provider_group(){

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('provider_view');
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
		$this->load->model('providermodel');

		### 등급리스트
		$list			= $this->providermodel->find_group_cnt_list();
		$sql			= "select count(*) cnt from fm_provider where provider_seq > 1";
		$query			= $this->db->query($sql);
		$result			= $query->result_array();
		$totalcount		= $result[0]['cnt'];

		### 자동 등급 조정 설정 불러오기
		$grade_clone = config_load('provider_grade_clone');

		$grade_clone['chg_text']	= "";
		$grade_clone['chk_text']	= "";
		$grade_clone['keep_text']	= "";
		$next_grade_date			= "";
		$month = $grade_clone['start_month'] ? $grade_clone['start_month'] : '1';

		## 자동갱신 일자/산출기간/유지기간 계산
		if($grade_clone['chg_day']){
			$auto_result = $this->providermodel->calculate_date($month,$grade_clone);
		}

		# 자동 등급조정 기초값.
		$list_month	= array(1,2,3,4,5,6,7,8,9,10,11,12);
		$list_term	= array(1,3,6,12,18,24,36);
		$list_day	= array(1,15);

		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign('list_month',$list_month);
		$this->template->assign('list_term',$list_term);
		$this->template->assign('list_day',$list_day);
		$this->template->assign('auto_result',$auto_result);
		$this->template->assign('clone',$grade_clone);
		$this->template->assign('tot',$totalcount);
		if($list) $this->template->assign(array('loop'=>$list,'gcount'=>count($list)));

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->print_("tpl");

	}

	/* 입점사 등급 만들기 화면 */
	public function provider_group_reg(){

		$pgroup_seq = $_GET['pgroup_seq'];

		$this->admin_menu();
		$this->tempate_modules();


		### SERVICE CHECK
		$this->load->model('usedmodel');
		$result = $this->usedmodel->used_service_check('grade');

		if(!$result['type']){
			$this->template->assign('service_limit','Y');
		}

		if($pgroup_seq){
			###
			$this->load->model('providermodel');
			$data = $this->providermodel->get_pgroup_data($pgroup_seq);

			switch($data['use_type']){
				case "auto1": $no = 1; break;
				case "auto2": $no = 2; break;
				default: $no = 3; break;
			}

			$data['order_sum_price'.$no]	= $data['order_sum_price'];
			$data['order_sum_ea'.$no]		= $data['order_sum_ea'];
			$data['order_sum_cnt'.$no]		= $data['order_sum_cnt'];

			$data['order_sum_use'] = unserialize($data['order_sum_use']);

			foreach($data['order_sum_use'] as $key=>$val){
				$selected['order_sum_'.$val.'_use'] = "checked";
			}
		}

		$filePath	= $this->template_path();
		$this->template->define(array('tpl'=>$filePath));
		$this->template->assign('data',$data);
		$this->template->assign('selected',$selected);
		$this->template->print_("tpl");
	}

	/* 입점사 자동 등급조정 설정 미리보기 */
	public function grade_ajax()
	{
		$this->load->model('providermodel');
		$result = $this->providermodel->calculate_date($_GET['start_month'],$_GET);

		$grade_dt = array();
		foreach($result as $id=>$val1){

			$arr_txt = array();
			if(in_array($id,array("chg_text","chk_text","keep_text"))){
				$arr_txt[] = "<ul>";

				foreach($val1 as $k=>$cont){
					if($k%2==1){ $sty = " style='background-color:#eeeeee;'"; }else{ $sty = ""; }
					$arr_txt[] = "<li".$sty.">".$cont."</li>";
				}

				$arr_txt[] = "</ul>";

				$grade_dt[$id] = implode("",$arr_txt);
			}

		}
		echo json_encode($grade_dt);

	}


}