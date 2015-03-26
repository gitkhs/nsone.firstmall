<?php
/* 마이페이지 주문배송 상단 출력 */
function showMypageTop($step_type)
{
	$CI =& get_instance();

	$cnt = 0;

	if($CI->userInfo['member_seq'] && $step_type){

		$sc['member_seq']		= $CI->userInfo['member_seq'];
		$sc['step_type']			= $step_type;

		switch($sc['step_type']){
			case "order":
			case "deposit":
			case "export":
				$CI->load->model('ordermodel');
				$orders = $CI->ordermodel->get_order_list($sc);
				$cnt = $orders['page']['totalcount'];
			break;
			case "return":
			case "return_ing":
				$CI->load->model('returnmodel');
				$returns = $CI->returnmodel->get_return_list($sc);
				$cnt = $returns['page']['totalcount'];
			break;
			case "cancel":
			case "refund":
			case "refund_ing":
				$CI->load->model('refundmodel');
				$refunds = $CI->refundmodel->get_refund_list($sc);
				$cnt = $refunds['page']['totalcount'];
			break;
			case "emoney":
				$cnt = $CI->mdata['emoney'];
			break;
			case "cash":
				$cnt = $CI->mdata['cash'];
			break;
			case "point":
				$cnt = $CI->mdata['point'];
			break;
		}
		
	}

	return $cnt;
}
?>