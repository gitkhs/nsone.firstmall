<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class openmarket_process extends admin_base {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('openmarketmodel');
		$this->openmarketmodel->chk_linkage_service();
	}

	## 판매마켓 환경 설정 저장
	public function save_config(){

		$params	= $this->openmarketmodel->chk_param_save();
		$this->openmarketmodel->save_config($params);
		$this->openmarketmodel->ended_linkage_service();

		$callback	= 'parent.location.reload();';
		openDialogAlert('설정이 저장되었습니다.',400,140,'parent',$callback);
		exit;
	}

	## 판매마켓 카테고리 연결
	public function save_link_category(){

		$param	= $this->openmarketmodel->chk_link_category();
		$this->openmarketmodel->save_link_category($param);
		$this->openmarketmodel->ended_linkage_service();

		$callback	= 'parent.save_result(\'ok\');';
		openDialogAlert('카테고리가 연결되었습니다.',400,140,'parent',$callback);
		exit;
	}

	## 판매마켓 카테고리 연결 해제
	public function save_unlink_category(){
		$unSeq			= trim($_GET['unSeq']);
		$chk_category	= $_POST['chk_category'];
		if		($unSeq){
			$this->openmarketmodel->save_unlink_category($unSeq);
			$this->openmarketmodel->ended_linkage_service();
		}elseif	($chk_category && count($chk_category) > 0){
			foreach($chk_category as $k => $unSeq){
				$this->openmarketmodel->save_unlink_category($unSeq);
			}
			$this->openmarketmodel->ended_linkage_service();
		}else{
			$callback	= 'parent.save_result(\'fail\');';
			openDialogAlert('해제 방법을 선택해 주세요.',400,140,'parent',$callback);
			exit;
		}

		$callback	= 'parent.save_result(\'ok\');';
		openDialogAlert('연결이 해제되었습니다.',400,140,'parent',$callback);
		exit;
	}

	## 판매마켓 금액 조정 ( 임시 )
	public function save_goods_option_tmp(){

		$params		= $this->openmarketmodel->chk_goods_price();
		$tmp_seq	= $this->openmarketmodel->save_goods_price_tmp($params);

		$callback	= 'parent.save_result(\''.$tmp_seq.'\');';
		openDialogAlert('임시 저장되었습니다.',400,140,'parent',$callback);
		exit;
	}

	## 선택 상품 일괄 전송
	public function send_select_goods(){
		$seq_list	= $_POST['goods_seq'];
		if	(is_array($seq_list) && count($seq_list) > 0){
			foreach($seq_list as $k => $goodsSeq){
				$this->openmarketmodel->request_send_goods($goodsSeq);
			}

			openDialogAlert('전송 요청되었습니다.',400,140,'parent',$callback);
			exit;
		}else{
			openDialogAlert('선택된 상품이 없습니다.',400,140,'parent',$callback);
			exit;
		}
	}
}

/* End of file openmarket_process.php */
/* Location: ./app/controllers/admin/openmarket_process.php */