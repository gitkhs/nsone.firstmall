<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class referer_process extends admin_base {

	public function __construct() {
		parent::__construct();

		/* 관리자 권한 체크 : 시작 */
		$auth = $this->authmodel->manager_limit_act('referer_act');
		if(!$auth){
			$callback = "";
			openDialogAlert($this->auth_msg,400,140,'parent',$callback);
			exit;
		}
		/* 관리자 권한 체크 : 끝 */

		$this->load->library(array('validation','pxl'));
		$this->load->model('referermodel');

		if	($this->config_system['service']['code'] == 'P_FREE' || !$this->isplusfreenot){
			$callback = "parent.openDialog('쇼핑몰 업그레이드 안내<span class=\'desc\'></span>', 'nofreeService', {'width':600,'height':200,'noClose':true});";
			echo '<script>'.$callback.'</script>';
			exit;
		}
	}


	// 유입경로 할인 PARAMETER 체크
	public function _check_param($mode = 'regist'){

		if	($mode == 'regist'){
			$_POST['refererUrl']		= trim($_POST['refererUrl']);
			// http나 https 제거
			if	(preg_match('/^http/', $_POST['refererUrl']))
				$_POST['refererUrl']	= preg_replace('/^https*\:\/\//', '', trim($_POST['refererUrl']));

			$this->validation->set_rules('refererName', '유입경로명','trim|required|xss_clean');
			$this->validation->set_rules('refererDesc', '유입경로설명','trim|xss_clean');
			$this->validation->set_rules('refererUrl', '유입경로 URL','trim|required|xss_clean');
		}

		$this->validation->set_rules('saleType', '혜택','trim|required|max_length[7]|xss_clean');
		$_POST['percentGoodsSale']		= ($_POST['percentGoodsSale'] > 0) ? $_POST['percentGoodsSale'] : '';
		$_POST['maxPercentGoodsSale']	= ($_POST['maxPercentGoodsSale'] > 0) ? $_POST['maxPercentGoodsSale'] : '';
		$_POST['wonGoodsSale']			= ($_POST['wonGoodsSale'] > 0) ? $_POST['wonGoodsSale'] : '';
		if($_POST['saleType'] == 'percent'){
			$this->validation->set_rules('percentGoodsSale', '할인율','trim|required|numeric|max_length[3]|xss_clean');
			$this->validation->set_rules('maxPercentGoodsSale', '최대 할인 금액','trim|required|numeric|xss_clean');
		}elseif($_POST['saleType']=='won'){
			$this->validation->set_rules('wonGoodsSale', '할인 금액','trim|required|numeric|xss_clean');
		}
		$this->validation->set_rules('issueDate[]', '유효 기간','trim|required|max_length[10]|xss_clean');
		if	(strtotime($_POST['issueDate'][0]) > strtotime($_POST['issueDate'][1])){
			$callback = "parent.document.getElementsByName('issueDate')[0].focus();";
			openDialogAlert("유효기간 시작일이 종료일보다 크게 입력되었습니다.",400,140,'parent',$callback);
			exit;
		}
		$this->validation->set_rules('limitGoodsPrice', '사용제한 금액','trim|numeric|xss_clean');

		if($_POST['issue_type'] == 'issue' ){
			$this->validation->set_rules('issueGoods[]', '적용 상품','trim|numeric|xss_clean');
			$this->validation->set_rules('issueCategoryCode[]', '적용 카테고리','trim|xss_clean');
		}elseif($_POST['issue_type'] == 'except' ){
			$this->validation->set_rules('exceptIssueGoods[]', '적용예외상품','trim|numeric|xss_clean');
			$this->validation->set_rules('exceptIssueCategoryCode[]', '적용 예외 카테고리','trim|xss_clean');
		}
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$callback = "if(parent.document.getElementsByName('{$err['key']}')[0]) parent.document.getElementsByName('{$err['key']}')[0].focus();";
			openDialogAlert($err['value'],400,140,'parent',$callback);
			exit;
		}

		// 중복확인
		$chkReferer	= $this->referermodel->chk_referersale_duple($_POST['refererUrl'], $_POST['refererUrlType'], $_POST['issueDate'][0], $_POST['issueDate'][1], $_POST['referersaleSeq']);
		if	($chkReferer['referersale_seq']){
			$callback = "parent.document.getElementsByName('refererUrl')[0].focus();";
			openDialogAlert("중복된 유입경로 URL입니다.",400,140,'parent',$callback);
			exit;
		}

		$_POST['issue_type'] 					= if_empty($_POST, 'issue_type', 'all');
		$_POST['duplicationUse']				= if_empty($_POST, 'duplicationUse', '0');
		$_POST['limitGoodsPrice']				= if_empty($_POST, 'limitGoodsPrice', '0');
		if($_POST['saleType']=='percent'){
			$_POST['percentGoodsSale']			= $_POST['percentGoodsSale'];
			$_POST['maxPercentGoodsSale']		= $_POST['maxPercentGoodsSale'];
			$_POST['wonGoodsSale']				= '0';
		}elseif($_POST['saleType']=='won'){
			$_POST['percentGoodsSale']			= '0';
			$_POST['maxPercentGoodsSale']		= '0';
			$_POST['wonGoodsSale']				= $_POST['wonGoodsSale'];
		}
		if(!$_POST['issueDate'][0])	$_POST['issueDate'][0]	= date('Y-m-d');
		if(!$_POST['issueDate'][1])	$_POST['issueDate'][1]	= date('Y-m-d');


		if	($mode == 'regist'){
			$retParam['referersale_name']		= $_POST['refererName'];
			$retParam['referersale_desc']		= $_POST['refererDesc'];
			$retParam['referersale_url']		= $_POST['refererUrl'];
			$retParam['url_type']				= ($_POST['refererUrlType']) ? $_POST['refererUrlType'] : 'equal';
		}
		$retParam['sale_type']				= $_POST['saleType'];
		$retParam['percent_goods_sale']		= $_POST['percentGoodsSale'];
		$retParam['max_percent_goods_sale']	= $_POST['maxPercentGoodsSale'];
		$retParam['won_goods_sale']			= $_POST['wonGoodsSale'];
		$retParam['issue_type']				= $_POST['issue_type'];
		$retParam['duplication_use']		= $_POST['duplicationUse'];
		$retParam['issue_goods_type']		= $_POST['issue_type'];
		$retParam['issue_category_type']	= $_POST['issue_type'];
		$retParam['issue_startdate']		= $_POST['issueDate'][0];
		$retParam['issue_enddate']			= $_POST['issueDate'][1];
		$retParam['limit_goods_price']		= $_POST['limitGoodsPrice'];
		$retParam['update_date']			= date('Y-m-d H:i:s');

		return $retParam;
	}

	// 유입경로 할인 등록
	public function regist(){

		$params	= $this->_check_param();
		$params['regist_date']	= date('Y-m-d H:i:s');
		$this->db->insert('fm_referersale', $params);
		$refererSaleSeq = $this->db->insert_id();

		if($_POST['issue_type'] == 'issue' ){
			if(isset($_POST['issueGoods'])){
				foreach($_POST['issueGoods'] as $goodsSeq){
					$paramIssuegoods['referersale_seq']		= $refererSaleSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']				= 'issue';
					$this->db->insert('fm_referersale_issuegoods', $paramIssuegoods);
				}
			}
			if(isset($_POST['issueCategoryCode'])){
				foreach($_POST['issueCategoryCode'] as $categoryCode){
					$paramIssuecategory['referersale_seq']	= $refererSaleSeq;
					$paramIssuecategory['category_code']	= $categoryCode;
					$paramIssuecategory['type']				= 'issue';
					$this->db->insert('fm_referersale_issuecategory', $paramIssuecategory);
				}
			}
		}elseif($_POST['issue_type'] == 'except' ){
			if(isset($_POST['exceptIssueGoods'])){
				foreach($_POST['exceptIssueGoods'] as $goodsSeq){
					$paramIssuegoods['referersale_seq']		= $refererSaleSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']				= 'except';
					$this->db->insert('fm_referersale_issuegoods', $paramIssuegoods);
				}
			}
			if(isset($_POST['exceptIssueCategoryCode'])){
				foreach($_POST['exceptIssueCategoryCode'] as $categoryCode){
					$paramIssuecategory['referersale_seq']	= $refererSaleSeq;
					$paramIssuecategory['category_code']	= $categoryCode;
					$paramIssuecategory['type']				= 'except';
					$this->db->insert('fm_referersale_issuecategory', $paramIssuecategory);
				}
			}
		}


		$callback = "parent.document.location.href='/admin/referer/referersale?no=".$refererSaleSeq."';";
		openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
	}

	// 유입경로 할인 수정
	public function modify(){

		$refererSaleSeq	= (int) $_POST['referersaleSeq'];
		$params			= $this->_check_param('modify');
		$this->db->where('referersale_seq', $refererSaleSeq);
		$this->db->update('fm_referersale', $params);

		$this->db->delete('fm_referersale_issuecategory', array('referersale_seq' => $refererSaleSeq));
		$this->db->delete('fm_referersale_issuegoods', array('referersale_seq' => $refererSaleSeq));
		if($_POST['issue_type'] == 'issue' ){
			if(isset($_POST['issueGoods'])){
				foreach($_POST['issueGoods'] as $goodsSeq){
					$paramIssuegoods['referersale_seq']		= $refererSaleSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']				= 'issue';
					$this->db->insert('fm_referersale_issuegoods', $paramIssuegoods);
				}
			}
			if(isset($_POST['issueCategoryCode'])){
				foreach($_POST['issueCategoryCode'] as $categoryCode){
					$paramIssuecategory['referersale_seq']	= $refererSaleSeq;
					$paramIssuecategory['category_code']	= $categoryCode;
					$paramIssuecategory['type']				= 'issue';
					$this->db->insert('fm_referersale_issuecategory', $paramIssuecategory);
				}
			}
		}elseif($_POST['issue_type'] == 'except' ){
			if(isset($_POST['exceptIssueGoods'])){
				foreach($_POST['exceptIssueGoods'] as $goodsSeq){
					$paramIssuegoods['referersale_seq']		= $refererSaleSeq;
					$paramIssuegoods['goods_seq']			= $goodsSeq;
					$paramIssuegoods['type']				= 'except';
					$this->db->insert('fm_referersale_issuegoods', $paramIssuegoods);
				}
			}
			if(isset($_POST['exceptIssueCategoryCode'])){
				foreach($_POST['exceptIssueCategoryCode'] as $categoryCode){
					$paramIssuecategory['referersale_seq']	= $refererSaleSeq;
					$paramIssuecategory['category_code']	= $categoryCode;
					$paramIssuecategory['type']				= 'except';
					$this->db->insert('fm_referersale_issuecategory', $paramIssuecategory);
				}
			}
		}


		$callback = "parent.document.location.reload();";
		openDialogAlert("저장 되었습니다.",400,140,'parent',$callback);
	}

	public function delete_referer(){
		if	($_GET['no']){
			$this->db->delete('fm_referersale', array('referersale_seq' => $_GET['no']));
			$this->db->delete('fm_referersale_issuecategory', array('referersale_seq' => $_GET['no']));
			$this->db->delete('fm_referersale_issuegoods', array('referersale_seq' => $_GET['no']));

			$callback = "parent.document.location.reload();";
			openDialogAlert("삭제 되었습니다.",400,140,'parent',$callback);
		}
	}
}

/* End of file coupon_process.php */
/* Location: ./app/controllers/admin/category.php */
