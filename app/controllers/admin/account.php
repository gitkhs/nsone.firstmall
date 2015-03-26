<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class account extends admin_base {

	public function __construct() {
		parent::__construct();
		$this->arr_step = config_load('step');
		$this->arr_payment = config_load('payment');
		$this->load->model('accountmodel');
		$this->load->model('providermodel');
		
	}

	public function index()
	{
		redirect("/admin/account/catalog");
	}

	public function get_provider_for_period()
	{
		$period = $_GET['period'];
		$result = $this->providermodel->provider_list_for_account_period($period);		
		foreach($result as $data){
			if($data['provider_name']) echo("<option value='".$data['provider_seq']."'>".$data['provider_name']."</option>");
		}
	}

	public function set_missing_account_round()
	{
		$this->load->model('accountmodel');
		$this->load->model('exportmodel');
		$query = "select * from fm_goods_export where `status`='75' and (account_2round='' OR account_2round is null)";
		$query = $this->db->query($query);
		foreach($query->result_array() as $data_export){
			$data_export_item = $this->exportmodel->get_export_item($data_export['export_code']);
			if($data_export['shipping_date']&&$data_export['shipping_date']!='0000-00-00'){
				$shipping_date = $data_export['shipping_date'];
			}
			$this->accountmodel->set_account_round($data_export,$data_export_item,$shipping_date);
			
		}		
	}

	public function _period2date($period_type,$round,$date){
		return $this->accountmodel->period2date($period_type,$round,$date);
	}

	public function catalog()
	{
		$this->set_missing_account_round();

		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		
		$calcu_count_limit = $this->usedmodel->get_provider_account_calcu_count();
		$this->template->assign('calcu_count_limit',$calcu_count_limit);
		
		if(!$_GET['s_year'] && !$_GET['e_year']){
			$date_arr = explode("-",date("Y-m"));
			$_GET['s_year']		= $date_arr[0];
			$_GET['s_month']	= $date_arr[1];
			$_GET['e_year']		= $date_arr[0];
			$_GET['e_month']	= $date_arr[1];
		}
		$_GET['s_export'] = $_GET['s_year']."-".$_GET['s_month'];
		$_GET['e_export'] = $_GET['e_year']."-".$_GET['e_month'];

		if(isset($_GET['s_export']) && $_GET['s_export']!="" && isset($_GET['e_export']) && $_GET['e_export']!=""){
			$where_shipping_date_str = "substring( exp.shipping_date, 1, 7 ) between '{$_GET['s_export']}' and '{$_GET['e_export']}' ";
		}
		if(isset($_GET['provider_seq']) && $_GET['provider_seq']!=""){
			$where[] = " oitem.provider_seq = '{$_GET['provider_seq']}' ";
		}
		
		if($_GET['pay_period']==2){
			$field_period = ",exp.account_2round as account_round";
			$groupby_period = ",account_round";
			$where[] = "pvd.calcu_count=2";
			$account_round = "account_2round";
		}else if($_GET['pay_period']==4){
			$field_period = ",exp.account_4round as account_round";
			$groupby_period = ",account_round";
			$where[] = "pvd.calcu_count=4";
			$account_round = "account_4round";
		}else{			
			$where[] = "(pvd.calcu_count=1 OR pvd.calcu_count is null)";
		}

		if($where) $str_where = ' and '. $where_shipping_date_str . ' and ' . implode(' and ',$where);

		$sql = "
			SELECT
				pvd.provider_seq provider_seq, 
				pvd.provider_id provider_id, 
				pvd.provider_name provider_name,
				substring( shipping_date, 1, 7 ) export,
				sum(item.ea) export_ea,
				sum(opt.ori_price*item.ea) opt_price,
				sum(sub.price*item.ea) sub_price,
				(sum(ifnull(cast(opt.commission_price as signed)*cast(item.ea as signed),0)) + sum(ifnull(cast(sub.commission_price as signed)*cast(item.ea as signed),0))) as commission_price,

				sum(opt.member_sale)			as member_sale, 
				sum(opt.coupon_sale)			as coupon_sale, 
				sum(opt.promotion_code_sale)	as promotion_code_sale, 
				sum(opt.fblike_sale)			as fblike_sale, 
				sum(opt.mobile_sale)			as mobile_sale, 
				sum(opt.referer_sale)			as referer_sale, 

				sum(opt.salescost_provider_coupon * item.ea)		as salescost_provider_coupon,
				sum(opt.salescost_provider_promotion * item.ea)	as salescost_provider_promotion,
				sum(opt.salescost_provider_referer * item.ea)	as salescost_provider_referer,

				sum(opt.promotion_code_sale) as wcode,
				sum(ifnull(item.ea,0)) as ea,
				oitem.provider_seq,
				sum(opt.unit_emoney*opt.ea + sub.unit_emoney*sub.ea) as emoney,
				sum(opt.unit_cash*opt.ea + sub.unit_cash*sub.ea) as cash,
				sum(opt.unit_enuri*opt.ea + sub.unit_enuri*sub.ea) as enuri,
				oitem.provider_seq,
				exp.shipping_provider_seq
				".$field_period.",
				sum( if(oitem.account_date is null,ifnull(oitem.goods_shipping_cost,0),0) ) as goods_shipping_cost
			FROM
				fm_goods_export_item item
				LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
				LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
				LEFT JOIN fm_goods_export exp ON exp.export_code=item.export_code
				LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq
				LEFT JOIN fm_order_item oitem ON oitem.item_seq=item.item_seq					
				LEFT JOIN fm_provider pvd ON pvd.provider_seq = oitem.provider_seq
			WHERE
				exp.status = '75'
				and ord.orign_order_seq is null
				AND exp.shipping_provider_seq!=1
				and exp.account_date is null
				{$str_where}
			GROUP BY
				exp.shipping_provider_seq,export".$groupby_period."
			ORDER BY
				exp.shipping_provider_seq desc";	
		
		$query = $this->db->query($sql);
		

		foreach($query->result_array() as $data_acc){	

			$data_acc['period'] = implode('~<br/>',$this->_period2date($_GET['pay_period'],$data_acc['account_round'],$data_acc['export']));

			
			
			$field_query = "sum(shipping_cost) as shipping_cost,sum(shipping_coupon_sale) as shipping_coupon_sale,sum(salescost_provider_coupon)	as salescost_provider_coupon,sum(shipping_promotion_code_sale) as shipping_promotion_code_sale,sum(salescost_provider_promotion) as salescost_provider_promotion";			
			$query_shipping = $this->accountmodel->account_order_shipping_query($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$data_acc['provider_seq'],'',$field_query);
				
			$data_shipping = $query_shipping -> row_array();
			$data_acc['shipping_cost']	= $data_shipping['shipping_cost'];
			$data_acc['coupon_sale']					+= $data_shipping['shipping_coupon_sale'];
			$data_acc['shipping_provider_coupon']		+= $data_shipping['salescost_provider_coupon'];
			$data_acc['salescost_provider_coupon']		+= $data_acc['shipping_provider_coupon'];
			$data_acc['promotion_code_sale']			+= $data_shipping['shipping_promotion_code_sale'];
			$data_acc['shipping_provider_promotion']	+= $data_shipping['salescost_provider_promotion'];
			$data_acc['salescost_provider_promotion']	+= $data_acc['shipping_provider_promotion'];

			$field_query = "sum(B.ea) refund_ea,sum(refund_price) as refund_price,sum(ifnull(cast(opt.commission_price as signed)*cast(B.ea as signed),0)+ifnull(cast(sub.commission_price as signed)*cast(B.ea as signed),0)) as refund_commission_price,sum(ifnull((opt.ori_price-cast(opt.commission_price as signed))*cast(B.ea as signed),0)+ifnull((sub.price-cast(sub.commission_price as signed))*cast(B.ea as signed),0)) as refund_fee";
			$query_refund = $this->accountmodel->account_refund_query($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$data_acc['provider_seq'],'',$field_query);
			$data_acc_refund = $query_refund->row_array();
			$data_acc['refund_ea'] = $data_acc_refund['refund_ea'];
			$data_acc['refund_price'] = $data_acc_refund['refund_price'];
			$data_acc['refund_commission_price'] = $data_acc_refund['refund_commission_price'];
			
			if($data_acc_refund['refund_commission_price']) $data_acc['refund_fee'] = $data_acc_refund['refund_price']-$data_acc_refund['refund_commission_price'];
			
			$data_acc['refund_fee'] = $data_acc_refund['refund_fee'];			
			$field_query = "sum(return_shipping_price) as return_shipping_price";			
			$query_shipping = $this->accountmodel->account_return_query($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$data_acc['provider_seq'],'',$field_query);			

			## 할인부담금 관련 추가
			$data_acc['tot_salescost']			= $data_acc['coupon_sale'] + $data_acc['member_sale']
												+ $data_acc['fblike_sale'] + $data_acc['mobile_sale']
												+ $data_acc['promotion_code_sale']
												+ $data_acc['referer_sale']
												+ $data_acc['enuri'] + $data_acc['cash']
												+ $data_acc['emoney'];
			$data_acc['tot_salescost_provider']	= $data_acc['salescost_provider_coupon']
												+ $data_acc['salescost_provider_promotion']
												+ $data_acc['salescost_provider_referer'];
			$data_acc['tot_salescost_admin']	= $data_acc['tot_salescost']
												- $data_acc['tot_salescost_provider'];

			$data_acc['admin_coupon_sale']		= $data_acc['coupon_sale'] - $data_acc['salescost_provider_coupon'];
			$data_acc['admin_promotion_sale']	= $data_acc['promotion_code_sale'] - $data_acc['salescost_provider_promotion'];
			$data_acc['admin_referer_sale']		= $data_acc['referer_sale'] - $data_acc['salescost_provider_referer'];

			$data_shipping = $query_shipping -> row_array();
			$data_acc['return_shipping_price']	= $data_shipping['return_shipping_price'];			
			$data_acc['wmoney']	= $data_acc['emoney'] + $data_acc['cash'];
			$data_acc['price']		= $data_acc['opt_price'] + $data_acc['sub_price'];
			$data_acc['shipping']	= $data_acc['shipping_cost'] + $data_acc['goods_shipping_cost'];
			$data_acc['fee'] 		= $data_acc['price'] - $data_acc['commission_price'] - $data_acc['tot_salescost_provider'] + $data_acc['shipping_provider_coupon'] + $data_acc['shipping_provider_promotion'];
			$data_acc['tot_fee'] 	= $data_acc['fee'] - $data_acc['refund_fee'];
			$data_acc['sales']		= $data_acc['price'] - $data_acc['refund_price'];
			$data_acc['account_price']	= $data_acc['price'] + $data_acc['shipping'] - $data_acc['fee'] - $data_acc['refund_commission_price'] + $data_acc['return_shipping_price'] - $data_acc['tot_salescost_provider'];

			$data_acc['margin']	= $data_acc['tot_fee'] - $data_acc['tot_salescost_admin'];
			$data_acc['margin_percent'] = round( $data_acc['margin'] / ($data_acc['price']-$data_acc['refund_price']) * 10000 ) / 100;

			$loop[] = $data_acc;
		}

		$this->template->assign('loop',$loop);


		### 정산 시작 년/월
		$this_year = date("Y");
		$sql = "select regist_date from fm_order order by regist_date limit 1";
		$query = $this->db->query($sql);
		$order = $query->result_array();
		if($order[0]['regist_date']){
			$start = substr($order[0]['regist_date'],0,4);
		}else{
			$start = $this_year;
		}

		$cnt = $this_year - $start;
		if($cnt<1){
			$year[] = $start;
		}else{
			for($i=date("Y");$i>=$start;$i--){
				$year[] = $i;
			}
		}
		for($i=12;$i>0;$i--){
			$temp = strlen($i)>1 ? $i : "0".$i;
			$month[] = $temp;
		}
		$this->template->assign(array('year'=>$year,'month'=>$month));

		###
		$provider = $this->providermodel->provider_goods_list();
		$this->template->assign('provider',$provider);
		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function complete()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();

		$r_period_type[2] = "";
		$r_period_type[4] = "";

		if	(!$_GET['sc_status'])	$_GET['sc_status']	= 'complete';

		if(!$_GET['s_year'] && !$_GET['e_year']){
			$date_arr = explode("-",date("Y-m"));
			$_GET['s_year']		= $date_arr[0];
			$_GET['s_month']	= $date_arr[1];
			$_GET['e_year']		= $date_arr[0];
			$_GET['e_month']	= $date_arr[1];
		}

		$_GET['sdate'] = $_GET['s_year']."-".$_GET['s_month'];
		$_GET['edate'] = $_GET['e_year']."-".$_GET['e_month'];

		if	($_GET['provider_seq']){
			$addWhere	= " and ac.provider_seq = '".$_GET['provider_seq']."' ";
		}

		$sql = "SELECT ac.seq as account_seq,pvd.provider_seq, pvd.provider_id, pvd.provider_name, ac.* FROM fm_account ac, fm_provider pvd WHERE ac.provider_seq = pvd.provider_seq and ac.acc_status = '".$_GET['sc_status']."' and ac.acc_date >= '".$_GET['sdate']."' and ac.acc_date <= '".$_GET['edate']."' ".$addWhere." ORDER BY ac.acc_status DESC";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $v){
			$v['period'] = implode('~<br/>',$this->_period2date($v['period_type'],$v['account_round'],$v['acc_date']));
			$v['tot_salescost']			= $v['salescost_admin'] + $v['salescost_provider'];
			$v['admin_salescost']		= explode('|', $v['adminsale_list']);
			$v['provider_salescost']	= explode('|', $v['providersale_list']);
			$loop[] = $v;
		}

		###
		$sql = "select regist_date from fm_order order by regist_date limit 1";
		$query = $this->db->query($sql);
		$order = $query->result_array();
		$start = date('Y');
		if	($order[0]['regist_date'])	$start = substr($order[0]['regist_date'],0,4);
		$cnt = date("Y") - $start;
		if($cnt < 1){
			$year[] = date("Y")-1;
			$year[] = date("Y");
		}else{
			for($i=date("Y");$i>=$start;$i--){
				$year[] = $i;
			}
		}
		for($i=12;$i>0;$i--){
			$temp = strlen($i)>1 ? $i : "0".$i;
			$month[] = $temp;
		}

		###
		$provider = $this->providermodel->provider_goods_list();
		$this->template->assign('provider',$provider);

		$this->template->assign('loop',$loop);
		$this->template->assign(array('year'=>$year,'month'=>$month));
		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

	public function detail()
	{
		$this->admin_menu();
		$this->tempate_modules();
		$file_path	= $this->template_path();
		$this->load->model('providermodel');
		$provider = (int) $_GET['provider'];
		$data_provider = $this->providermodel->get_provider($provider);
		
		if($_GET['pay_period'] == '2'){
			$account_round = "account_2round";
			$where[] = "exp.".$account_round."='{$_GET['account_round']}'";
		}else if($_GET['pay_period'] == '4'){
			$account_round = "account_4round";
			$where[] = "exp.".$account_round."='{$_GET['account_round']}'";
		}
		if( $_GET['account_seq'] != '' ){
			$where[] = "exp.account_seq='{$_GET['account_seq']}'";
		}else{
			$where[] = "exp.account_seq is null";
		}
		$data_provider['period'] = $this->_period2date($_GET['pay_period'],$_GET['account_round'],$_GET['export']);

		$sql = "
			SELECT
				exp.*,
				substring( shipping_date, 1, 7 ) export,
				opt.option1,opt.option2,opt.option3,opt.option4,opt.option5,
				sub.suboption,
				oitem.goods_seq,
				
				opt.ori_price*item.ea opt_price,
				sub.price*item.ea sub_price,

				(ifnull(cast(opt.commission_price as signed)*cast(item.ea as signed),0) + ifnull(cast(sub.commission_price as signed)*cast(item.ea as signed),0)) as commission_price,

				opt.member_sale			as member_sale, 
				opt.coupon_sale			as coupon_sale, 
				opt.promotion_code_sale	as promotion_code_sale, 
				opt.fblike_sale			as fblike_sale, 
				opt.mobile_sale			as mobile_sale, 
				opt.referer_sale		as referer_sale, 

				(opt.salescost_provider_coupon * item.ea)	as salescost_provider_coupon,
				(opt.salescost_provider_promotion * item.ea)	as salescost_provider_promotion,
				(opt.salescost_provider_referer * item.ea)	as salescost_provider_referer,

				opt.promotion_code_sale wcode,
				ifnull(item.ea,0) ea,
				oitem.provider_seq,
				(opt.unit_emoney*opt.ea + sub.unit_emoney*sub.ea) as emoney,
				(opt.unit_cash*opt.ea + sub.unit_cash*sub.ea) as cash,
				(opt.unit_enuri*opt.ea + sub.unit_enuri*sub.ea) as enuri,
				ord.order_user_name,
				ord.payment,
				ord.step,
				ord.member_seq,				
				if((select provider_seq from fm_order_shipping where shipping_seq=oitem.shipping_seq)=1,0,oitem.goods_shipping_cost) as goods_shipping_cost,
				(SELECT userid FROM fm_member WHERE member_seq=ord.member_seq) userid,
				(SELECT rute FROM fm_member WHERE member_seq=ord.member_seq) mbinfo_rute,
				(SELECT group_name FROM fm_member m,fm_member_group g WHERE m.group_seq=g.group_seq and m.member_seq=ord.member_seq) group_name,
				(select goods_name from fm_order_item where item_seq = item.item_seq) goods_name,
				(select goods_shipping_cost from fm_order_item where item_seq=item.item_seq and (account_date is null or substring(account_date,1,7)=substring(shipping_date,1,7))) goods_shipping_cost,
				ord.admin_order,
				ord.sns_rute
			FROM
				fm_goods_export_item item
				LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq
				LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq
				LEFT JOIN fm_goods_export exp ON exp.export_code=item.export_code
				LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq
				LEFT JOIN fm_order_item oitem ON oitem.item_seq=item.item_seq
			WHERE
				exp.status = '75'
				and ord.orign_order_seq is null
				AND substring(exp.shipping_date,1,7)='".$_GET['export']."'
				AND exp.shipping_provider_seq=?";
			
		if($where){
			$sql .= ' AND '. implode(' AND ',$where);
		}			
		$sql .= " ORDER BY exp.export_seq desc";

		$bind[] = $_GET['provider'];
		$query = $this->db->query($sql,$bind);
				
		foreach($query->result_array() as $v){

			$query_shipping = "select shipping_cost, shipping_coupon_sale, salescost_provider_coupon, 
			shipping_promotion_code_sale, salescost_provider_promotion 
			from fm_order_shipping where order_seq=? and (account_date is null or substring(account_date,1,7)=?) and provider_seq=?";
			if($account_round){
				$query_shipping .= " and {$account_round}='".$v[$account_round]."'";
			}
			if( $_GET['account_seq'] != '' ){
				$where[] = "and account_seq='{$_GET['account_seq']}'";
			}
			$query_shipping = $this->db->query($query_shipping,array($v['order_seq'],$v['export'],$_GET['provider']));
			$data_shipping = $query_shipping -> row_array();

			if(!$r_ex_shipping[$v['order_seq']]) {
				$v['coupon_sale']				+= $data_shipping['shipping_coupon_sale'];
				$v['shipping_provider_coupon']	= $data_shipping['salescost_provider_coupon'];
				$v['salescost_provider_coupon']	+= $data_shipping['salescost_provider_coupon'];

				$v['promotion_code_sale']			+= $data_shipping['shipping_promotion_code_sale'];
				$v['shipping_provider_promotion']	= $data_shipping['salescost_provider_promotion'];
				$v['salescost_provider_promotion']	+= $data_shipping['salescost_provider_promotion'];
			}

			$v['shipping_cost']	= $data_shipping['shipping_cost'];
			if($r_ex_shipping[$v['order_seq']]) $v['shipping_cost']	= 0;
			$r_ex_shipping[$v['order_seq']] = 1;
			
			if($r_ex_shipping[$v['export_code']]) $v['goods_shipping_cost'] = 0;
			$r_ex_shipping[$v['export_code']] = 1;

			## 할인부담금 관련 추가
			$v['tot_salescost']				= $v['coupon_sale'] + $v['member_sale']
											+ $v['fblike_sale'] + $v['mobile_sale']
											+ $v['promotion_code_sale']
											+ $v['referer_sale']
											+ $v['enuri'] + $v['cash']
											+ $v['emoney'];
			$v['tot_salescost_provider']	= $v['salescost_provider_coupon']
											+ $v['salescost_provider_promotion']
											+ $v['salescost_provider_referer'];
			$v['tot_salescost_admin']		= $v['tot_salescost']
											- $v['tot_salescost_provider'];

			$v['admin_coupon_sale']		= $v['coupon_sale'] - $v['salescost_provider_coupon'];
			$v['admin_promotion_sale']	= $v['promotion_code_sale'] - $v['salescost_provider_promotion'];
			$v['admin_referer_sale']	= $v['referer_sale'] - $v['salescost_provider_referer'];

			$v['wmoney']	= $v['emoney'] + $v['cash'];
			$v['price']		= $v['opt_price'] + $v['sub_price'];
			
			$v['mstep']		= $this->arr_step[$v['status']];
			$v['mpayment']	= $this->arr_payment[$v['payment']];
			
			$v['fee']		= $v['price'] - $v['commission_price'] - $v['tot_salescost_provider'] + $v['shipping_provider_coupon'] + $v['shipping_provider_promotion'];			

			$v['fee_percent'] = $v['fee'] / $v['price'] * 100;
			$v['wmoney'] = $v['cash'] + $v['emoney']; // wmoney
			$v['account_price'] = $v['price'] - $v['fee'] + $v['goods_shipping_cost'] + $v['shipping_cost'] - $v['tot_salescost_provider'];
			$v['margin'] = $v['fee'] - $v['tot_salescost_admin'];
			$v['margin_percent'] = round($v['margin'] / $v['price'] * 10000) / 100;

			$tot['tot_salescost_admin']		+= $v['tot_salescost_admin'];
			$tot['tot_salescost_provider']	+= $v['tot_salescost_provider'];
			$tot['price'] += $v['price'];
			$tot['fee'] += $v['fee'];
			$tot['wcode'] += $v['wcode'];
			$tot['wemoney'] += $v['wemoney'];
			$tot['account_price'] += $v['account_price'];
			$tot['margin'] += $v['margin'];
			$tot['ea'] += $v['ea'];

			$tot_export['tot_salescost_admin']		+= $v['tot_salescost_admin'];
			$tot_export['tot_salescost_provider']	+= $v['tot_salescost_provider'];
			$tot_export['goods_shipping_cost'] += $v['goods_shipping_cost'];
			$tot_export['shipping_cost'] += $v['shipping_cost'];
			$tot_export['price'] += $v['price'];
			$tot_export['fee'] += $v['fee'];
			$tot_export['wcode'] += $v['wcode'];
			$tot_export['wemoney'] += $v['wemoney'];
			$tot_export['account_price'] += $v['account_price'];
			$tot_export['margin'] += $v['margin'];
			$tot_export['ea'] += $v['ea'];

			$loop[] = $v;
		}
		
		$tot_export['margin_percent'] = round($tot_export['margin'] / $tot_export['price'] * 10000) / 100;
		
		$field_query = "
		*,return_shipping_price as t_shipping,
		(SELECT concat(order_user_name,'|',m.member_seq,'|',m.userid,'|',m.rute,'|',(SELECT group_name FROM fm_member_group where group_seq=m.group_seq)) FROM fm_member m,fm_order ord WHERE m.member_seq=ord.member_seq and ord.order_seq=fm_order_return.order_seq) memberinfo
		";
		$query = $this->accountmodel->account_return_query($_GET['pay_period'],$_GET['account_round'],$_GET['export'],$_GET['provider'],$_GET['account_seq'],$field_query);
		foreach($query->result_array() as $k){
			if($k['return_shipping_price']){
				list($v['order_user_name'],$v['member_seq'],$v['userid'],$v['mbinfo_rute'],$v['group_name']) = explode('|',$v['memberinfo']);
				$k['mpayment']	= $this->arr_payment[$k['payment']];
				$tot['return_shipping_price'] += $k['return_shipping_price'];
				$tot['account_price'] += $k['return_shipping_price'];
				$tot['ea'] += $k['ea'];
				
				$tot_return['return_shipping_price'] += $k['return_shipping_price'];
				$tot_return['account_price'] += $k['return_shipping_price'];
				$tot_return['ea'] += $k['ea'];
				$loop2[] = $k;
			}
		}
		
		$field_query = "
			A.*,
			opt.price price,
			sub.price sub_price,
			ord.order_user_name,
			(SELECT concat(member_seq,'|',userid,'|',rute,'|',(SELECT group_name FROM fm_member_group where group_seq=fm_member.group_seq)) FROM fm_member WHERE member_seq=ord.member_seq) memberinfo,
			opt.option1,opt.option2,opt.option3,opt.option4,opt.option5,
			sub.suboption,
			ifnull(cast(opt.commission_price as signed),0) + ifnull(cast(sub.commission_price as signed),0) as commission_price,
			B.ea,
			A.refund_price,
			ord.admin_order";
		$query = $this->accountmodel->account_refund_query($_GET['pay_period'],$_GET['account_round'],$_GET['export'],$_GET['provider'],$_GET['account_seq'],$field_query);

		
		foreach($query->result_array() as $v){
			list($v['member_seq'],$v['userid'],$v['mbinfo_rute'],$v['group_name']) = explode('|',$v['memberinfo']);
			$v['price'] = $v['price'] + $v['sub_price'];
			$v['fee'] = ($v['price'] - $v['commission_price'])*$v['ea'];
			$v['account_price'] = $v['commission_price']*$v['ea'];
			$v['mpayment']	= $this->arr_payment[$v['payment']];

			$tot['price'] 	-= $v['price'];
			$tot['fee'] 	-= $v['fee'];
			$tot['account_price'] -= $v['account_price'];
			$tot['ea'] += $v['ea'];

			$tot_refund['price'] -= $v['price'];
			$tot_refund['fee'] -= $v['fee'];
			$tot_refund['account_price'] -= $v['account_price'];
			$tot_refund['ea'] += $v['ea'];
			$loop3[] = $v;
		}

		$tot['margin'] = $tot['margin'] + $tot_refund['fee'];
		
		$tot['margin_percent'] = round($tot['margin'] / $tot['price'] * 10000) / 100;

		$this->template->assign('data_provider',$data_provider);
		$this->template->assign('loop',$loop);
		$this->template->assign('loop2',$loop2);
		$this->template->assign('loop3',$loop3);
		$this->template->assign('tot_return',$tot_return);
		$this->template->assign('tot_refund',$tot_refund);
		$this->template->assign('tot_export',$tot_export);
		$this->template->assign('tot',$tot);
		
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}


	public function process(){		
		if(  $_GET['value'] == 'none' ){
			$callback = "parent.location.reload();";
			openDialogAlert("대기상태로는 변경하실 수 없습니다.",400,140,'parent',$callback);
			exit;
		}

		$r_period = $this->_period2date($_GET['pay_period'],$_GET['account_round'],$_GET['export']);
		

		if( $r_period[1] >= date('Y-m-d')){
			openDialogAlert("\'".$r_period[1]."\'이 지나야 정산하실수 있습니다.",400,140,'parent',$callback);
			exit;
		}
		
		
		if(isset($_GET['export']) && $_GET['export']!=""){
			$where_shipping_date_str = "substring( exp.shipping_date, 1, 7 ) = '".$_GET['export']."' ";
		}

		$_GET['provider_seq'] = $_GET['provider'];
		$shipping_provider_seq = $_GET['provider_seq'];

		if(isset($_GET['provider_seq']) && $_GET['provider_seq']!=""){
			$where[] = " exp.shipping_provider_seq = '{$shipping_provider_seq}' ";
		}
		
		$period_type = 1;
		if($_GET['pay_period']) $period_type = $_GET['pay_period'];

		if($_GET['pay_period']==2){
			$field_period = ",exp.account_2round as account_round";
			$groupby_period = ",account_round";
			$where[] = "pvd.calcu_count=2";
			$account_round = "account_2round";
		}else if($_GET['pay_period']==4){
			$field_period = ",exp.account_4round as account_round";
			$groupby_period = ",account_round";
			$where[] = "pvd.calcu_count=4";
			$account_round = "account_4round";
		}else{			
			$where[] = "(pvd.calcu_count=1 OR pvd.calcu_count is null)";
		}

		if($where) $str_where = ' and '. $where_shipping_date_str . ' and ' . implode(' and ',$where);

		$sql = "
			SELECT
				substring( shipping_date, 1, 7 ) export,
				sum(item.ea) export_ea,
				sum(opt.ori_price*item.ea) opt_price,
				sum(sub.price*item.ea) sub_price,
				(sum(ifnull(cast(opt.commission_price as signed)*cast(item.ea as signed),0)) + sum(ifnull(cast(sub.commission_price as signed)*cast(item.ea as signed),0))) as commission_price,

				sum(opt.member_sale)			as member_sale, 
				sum(opt.coupon_sale)			as coupon_sale, 
				sum(opt.promotion_code_sale)	as promotion_code_sale, 
				sum(opt.fblike_sale)			as fblike_sale, 
				sum(opt.mobile_sale)			as mobile_sale, 
				sum(opt.referer_sale)			as referer_sale, 

				sum(opt.salescost_provider_coupon * item.ea)		as salescost_provider_coupon,
				sum(opt.salescost_provider_promotion * item.ea)	as salescost_provider_promotion,
				sum(opt.salescost_provider_referer * item.ea)	as salescost_provider_referer,

				sum(opt.promotion_code_sale) as wcode,
				sum(ifnull(item.ea,0)) as ea,
				oitem.provider_seq,
				sum(ord.emoney) as emoney,
				sum(ord.cash) as cash,
				oitem.provider_seq
				".$field_period.",
				sum( if(oitem.account_date is null,ifnull(oitem.goods_shipping_cost,0),0) ) as goods_shipping_cost
			FROM
				fm_goods_export_item item
					LEFT JOIN fm_order_item_option opt ON opt.item_option_seq = item.option_seq and item.option_seq
					LEFT JOIN fm_order_item_suboption sub ON sub.item_suboption_seq = item.suboption_seq and item.suboption_seq
					LEFT JOIN fm_goods_export exp ON exp.export_code=item.export_code
					LEFT JOIN fm_order ord ON ord.order_seq=exp.order_seq
					LEFT JOIN fm_order_item oitem ON oitem.item_seq=item.item_seq					
					LEFT JOIN fm_provider pvd ON pvd.provider_seq = oitem.provider_seq
			WHERE
				exp.status = '75'
				and ord.orign_order_seq is null
				AND exp.shipping_provider_seq!=1				
				and exp.account_seq is null
				{$str_where}
			GROUP BY
				exp.shipping_provider_seq,export".$groupby_period."
			ORDER BY
				exp.shipping_provider_seq desc";		

		$query = $this->db->query($sql);		
		foreach($query->result_array() as $data_acc){

			$data_acc['period'] = $this->_period2date($_GET['pay_period'],$data_acc['account_round'],$data_acc['export']);
			
			$field_query = "sum(shipping_cost) as shipping_cost,sum(shipping_coupon_sale) as shipping_coupon_sale,sum(salescost_provider_coupon)	as salescost_provider_coupon,sum(shipping_promotion_code_sale) as shipping_promotion_code_sale,sum(salescost_provider_promotion) as salescost_provider_promotion";			
			$query_shipping = $this->accountmodel->account_order_shipping_query($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$_GET['provider'],'',$field_query);
			$data_shipping = $query_shipping -> row_array();			
			
			$data_acc['shipping_cost']					= $data_shipping['shipping_cost'];
			$data_acc['coupon_sale']					+= $data_shipping['shipping_coupon_sale'];
			$data_acc['shipping_provider_coupon']		+= $data_shipping['salescost_provider_coupon'];
			$data_acc['salescost_provider_coupon']		+= $data_acc['shipping_provider_coupon'];
			$data_acc['promotion_code_sale']			+= $data_shipping['shipping_promotion_code_sale'];
			$data_acc['shipping_provider_promotion']	+= $data_shipping['salescost_provider_promotion'];
			$data_acc['salescost_provider_promotion']	+= $data_acc['shipping_provider_promotion'];
			
			$field_query = "sum(B.ea) refund_ea,sum(ifnull(opt.ori_price*B.ea,0)+ifnull(sub.price*B.ea,0)) as refund_price,sum(ifnull(cast(opt.commission_price as signed)*cast(B.ea as signed),0)+ifnull(cast(sub.commission_price as signed)*cast(B.ea as signed),0)) as refund_commission_price,sum(ifnull((opt.ori_price-cast(opt.commission_price as signed))*cast(B.ea as signed),0)+ifnull((sub.price-cast(sub.commission_price as signed))*cast(B.ea as signed),0)) as refund_fee";
			$query_refund = $this->accountmodel->account_refund_query($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$_GET['provider'],'',$field_query);			
			$data_acc_refund = $query_refund->row_array();
			
			$data_acc['refund_ea'] = $data_acc_refund['refund_ea'];
			$data_acc['refund_price'] = $data_acc_refund['refund_price'];
			$data_acc['refund_commission_price'] = $data_acc_refund['refund_commission_price'];
			$data_acc['refund_fee'] = $data_acc_refund['refund_fee'];			
			
			$field_query = "sum(return_shipping_price) as return_shipping_price";			
			$query_shipping = $this->accountmodel->account_return_query($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$_GET['provider'],'',$field_query);
			
			

			## 할인부담금 관련 추가
			$data_acc['tot_salescost']			= $data_acc['coupon_sale'] + $data_acc['member_sale']
												+ $data_acc['fblike_sale'] + $data_acc['mobile_sale']
												+ $data_acc['promotion_code_sale']
												+ $data_acc['referer_sale']
												+ $data_acc['enuri'] + $data_acc['cash']
												+ $data_acc['emoney'];
			$data_acc['tot_salescost_provider']	= $data_acc['salescost_provider_coupon']
												+ $data_acc['salescost_provider_promotion']
												+ $data_acc['salescost_provider_referer'];
			$data_acc['tot_salescost_admin']	= $data_acc['tot_salescost']
												- $data_acc['tot_salescost_provider'];

			$data_acc['admin_coupon_sale']		= $data_acc['coupon_sale'] - $data_acc['salescost_provider_coupon'];
			$data_acc['admin_promotion_sale']	= $data_acc['promotion_code_sale'] - $data_acc['salescost_provider_promotion'];
			$data_acc['admin_referer_sale']		= $data_acc['referer_sale'] - $data_acc['salescost_provider_referer'];

			$data_shipping = $query_shipping -> row_array();			
			$data_acc['return_shipping_price']	= $data_shipping['return_shipping_price'];			
			$data_acc['wmoney']	= $data_acc['emoney'] + $data_acc['cash'];
			$data_acc['price']		= $data_acc['opt_price'] + $data_acc['sub_price'];
			$data_acc['shipping']	= $data_acc['shipping_cost'] + $data_acc['goods_shipping_cost'];
			$data_acc['fee'] 		= $data_acc['price'] - $data_acc['commission_price'] - $data_acc['tot_salescost_provider'] + $data_acc['shipping_provider_coupon'] + $data_acc['shipping_provider_promotion'];
			$data_acc['tot_fee'] 	= $data_acc['fee'] - $data_acc['refund_fee'];
			$data_acc['sales']		= $data_acc['price'] - $data_acc['refund_price'];
			$data_acc['account_price']	= $data_acc['price'] + $data_acc['shipping'] - $data_acc['fee'] - $data_acc['refund_commission_price'] + $data_acc['return_shipping_price'] - $data_acc['tot_salescost_provider'];

			$data_acc['margin']	= $data_acc['tot_fee'] - $data_acc['tot_salescost_admin'];
			$data_acc['margin_percent'] = round( $data_acc['margin'] / ($data_acc['price']-$data_acc['refund_price']) * 10000 ) / 100;
			
			$data[] = $data_acc;
		}

		###
		$insert['acc_date']			= $_GET['export'];
		$insert['acc_status']		= $_GET['value'];
		$insert['provider_id']		= get_provider_id($_GET['provider']);
		$insert['provider_seq']		= $_GET['provider'];
		$insert['sell_ea']			= $data[0]['export_ea'];
		$insert['sell_price']		= $data[0]['price'];
		$insert['sell_shipping']	= $data[0]['shipping'];
		$insert['ref_ea']			= $data[0]['refund_ea'];
		$insert['ref_price']		= $data[0]['refund_price'];
		$insert['ref_fee']			= $data[0]['refund_fee'];
		$insert['sales_ea']			= $data[0]['export_ea']-$data[0]['refund_ea'];
		$insert['sales_price']		= $data[0]['sales'];
		$insert['sales_shipping']	= $data[0]['shipping'];
		$insert['sales_sum']		= $data[0]['sales'] + $data[0]['shipping'];
		$insert['sales_charge']		= $data[0]['tot_fee'];
		$insert['prom_code']		= $data[0]['wcode'];
		$insert['prom_emoney']		= $data[0]['emoney'];
		$insert['prom_cash']		= $data[0]['cash'];
		$insert['prom_sum']			= $data[0]['wcode'] + $data[0]['emoney']  + $data[0]['cash'];
		$insert['ret_shipping']		= $data[0]['return_shipping_price'];
		$insert['acc_price']		= $data[0]['account_price'];
		$insert['profit_price']		= $data[0]['margin'];
		$insert['profit_per']		= $data[0]['margin_percent'];		
		$insert['period_type']		= $_GET['pay_period'];
		$insert['account_round']	= $data[0]['account_round'];
		$insert['regist_date']		= date("Y-m-d H:i:s");

		## 할인부담금 관련 추가
		$insert['salescost_admin']		= $data[0]['tot_salescost_admin'];
		$insert['salescost_provider']	= $data[0]['tot_salescost_provider'];
		$insert['adminsale_list']		= $data[0]['admin_coupon_sale'].'|'
										. $data[0]['member_sale'].'|'
										. $data[0]['fblike_sale'].'|'
										. $data[0]['mobile_sale'].'|'
										. $data[0]['admin_promotion_sale'].'|'
										. $data[0]['admin_referer_sale'].'|'
										. $data[0]['enuri'].'|'
										. $data[0]['cash'].'|'
										. $data[0]['emoney'];
		$insert['providersale_list']	= $data[0]['salescost_provider_coupon'].'|'
										. $data[0]['salescost_provider_promotion'].'|'
										. $data[0]['salescost_provider_referer'];
		$this->db->insert('fm_account', $insert);
		$account_seq = $this->db->insert_id();		

		$this->load->model("accountmodel");
		$param['account_status']	= $_GET['value'];
		$param['account_round']		= $data[0]['account_round'];
		$param['round_field']		= $account_round;
		$param['account_date']		= $_GET['export'];
		$param['provider_seq']		= $_GET['provider'];
		$param['account_seq']		= $account_seq;
		
		$this->accountmodel->account_complete_item($param);
		$this->accountmodel->set_export_accountstatus($param);		
		$this->accountmodel->account_return($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$_GET['provider'],$account_seq);
		$this->accountmodel->account_refund($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$_GET['provider'],$account_seq);		
		$this->accountmodel->account_order_shipping($_GET['pay_period'],$data_acc['account_round'],$data_acc['export'],$_GET['provider'],$account_seq);
		
		###
		$callback = "parent.location.reload();";
		openDialogAlert("처리 되었습니다.",400,140,'parent',$callback);
	}

	public function complete_process(){
		if($_GET['type']=='pay'){
			$updata['acc_pay_type']	= $_GET['value'];
			$updata['acc_pay_date'] = date("Y-m-d H:i:s");
		}elseif($_GET['type']=='status'){
			$updata['acc_status']	= $_GET['value'];
			$this->accountmodel->update_export_account_status($_GET['seq'],$updata['acc_status']);
		}else{
			$updata['acc_tax_type']	= $_GET['value'];
			$updata['acc_tax_date'] = date("Y-m-d H:i:s");
		}
		$this->db->where('seq', $_GET['seq']);
		$result = $this->db->update('fm_account', $updata);

		###
		$callback = "parent.location.reload();";
		openDialogAlert("처리 되었습니다.",400,140,'parent',$callback);
	}

	public function migration()
	{		
		exit;
		$this->accountmodel->migration();
		debug_var($this->db->queries);
	}
}

/* End of file brand.php */
/* Location: ./app/controllers/admin/brand.php */
