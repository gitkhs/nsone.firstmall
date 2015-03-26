<?php

function assignMypageSummaryData(){

	if(mypageSummaryDataAssigned===true) return;

	define('mypageSummaryDataAssigned',true);

	$CI =& get_instance();
	$CI->load->model('membermodel');
	if( $CI->userInfo['member_seq'] ) {
		$data = $CI->membermodel->get_member_data($CI->userInfo['member_seq']);
		$CI->template->assign($data);

		$CI->load->model('ordermodel');
		$CI->load->model('exportmodel');
		$CI->load->model('wishmodel');
		$CI->load->model('cartmodel');

		/*
		$query = "select count(*) cnt from fm_cart a, fm_cart_option b where a.member_seq=? and a.distribution='cart' and a.cart_seq = b.cart_seq";
		$query = $CI->db->query($query,array($CI->userInfo['member_seq']));
		$data = $query->result_array();
		*/
		$data_summary['cart_cnt'] = $CI->cartmodel->get_cart_count();

		$result = $CI->wishmodel->get_list( $CI->userInfo['member_seq'],'list2' );
		$data_summary['wish_cnt'] = $result['page']['totalcount'];

		$query = "select count(*) cnt from fm_order where member_seq=? and step > 0 and step < 75 and hidden='N'";
		$query = $CI->db->query($query,array($CI->userInfo['member_seq']));
		$data = $query->result_array();
		$data_summary['order_ing_cnt'] = $data[0]['cnt'];

		$query = "select count(ret.return_code) cnt from fm_order_return ret,fm_order ord where ret.order_seq=ord.order_seq and ret.status in ('request','ing') and  ord.member_seq=?";
		$query = $CI->db->query($query,array($CI->userInfo['member_seq']));
		$data = $query->result_array();
		$data_summary['return_ing_cnt'] = $data[0]['cnt'];

		$query = "select count(ref.refund_code) cnt from fm_order_refund ref,fm_order ord where ref.order_seq=ord.order_seq and ref.status in ('request','ing') and  ord.member_seq=?";
		$query = $CI->db->query($query,array($CI->userInfo['member_seq']));
		$data = $query->result_array();
		$data_summary['refund_ing_cnt'] = $data[0]['cnt'];

		###
		$cfg_mbupdateorder = config_load('member_update_order');
		if( $data['member_order_price'] && $cfg_mbupdateorder['update_date'] ) {
			$data_summary['step75_price'] = $data['member_order_price'];
		}else{
			$query = "select sum(step75_price) step75_price from fm_member_order where member_seq=?";
			$query = $CI->db->query($query,array($CI->userInfo['member_seq']));
			$data = $query->result_array();
			$query = "select sum(refund_price) refund_price from fm_member_order where member_seq=?";
			$query = $CI->db->query($query,array($CI->userInfo['member_seq']));
			$data2 = $query->result_array();
			$price = $data[0]['step75_price'] - $data[0]['refund_price'];
			$data_summary['step75_price'] = $price;
		}

		//쿠폰보유건 test
		$CI->load->model('couponmodel');
		$sc['today']			= date('Y-m-d',time());
		$dsc['whereis'] = " and member_seq=".$CI->userInfo['member_seq']." and use_status='unused' AND ( (issue_startdate is null  AND issue_enddate is null ) OR (issue_startdate <='".$sc['today']."' AND issue_enddate >='".$sc['today']."') )";//사용가능한
		$data_summary['coupondownloadtotal'] = $CI->couponmodel->get_download_total_count($dsc);
	}

	$CI->template->assign(array('summary'=>$data_summary));
}

