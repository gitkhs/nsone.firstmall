<?php
class stockmodel extends CI_Model {
	var $arr_reason = array(
		'input'	=> '입고',
		'lost'	=> '분실',
		'error'	=> '오류',
		'bad'	=> '불량',
		'etc'	=> '기타',
	);
	
	public function __construct()
	{
		$this->load->model('goodsmodel');
	}
	
	public function insert_stock_history($params){

		$data = array();
		$data['reason'] = $params['reason'];
		$data['supplier_seq'] = $params['supplier_seq'];
		$data['reason_detail'] = $params['reason_detail'];
		$data['stock_date'] = $params['stock_date'];	
		$data['manager_id'] = $this->managerInfo['manager_id'];
		$data['regist_date'] = date('Y-m-d H:i:s');
		
		$this->db->insert('fm_stock_history', $data);
		
		$stock_history_seq = $this->db->insert_id();
		$stock_code = 'S'.date('ymdH').$stock_history_seq;
		$update_data['stock_code'] = $stock_code;
		
		$this->db->where('stock_history_seq',$stock_history_seq);
		$this->db->update('fm_stock_history',$update_data);

		return $stock_code;
	}
	
	public function insert_stock_history_item($params){
		
		$goods = $this->goodsmodel->get_goods($params['goods_seq']);		
		
		$data = array();
		$data['stock_code'] = $params['stock_code'];
		$data['goods_seq'] = $params['goods_seq'];
		$data['goods_name'] = $goods['goods_name'];
		$data['option_type'] = $params['option_type'];
		$data['prev_supply_price'] = $params['prev_supply_price'];
		$data['supply_price'] = $params['supply_price'];
		$data['ea'] = $params['ea'];
		
		for($i=1;$i<=5;$i++){
			if(!empty($params['title'.$i])){
				$data['title'.$i] = $params['title'.$i];
				$data['option'.$i] = $params['option'.$i];
			}
		}
		$this->db->insert("fm_stock_history_item",$data);
	}
	
	public function option_modify($goods_seq, $options, $input_supply_price, $adjust_ea, $reason, $chk_supply_price_replace){
		
		$this->db->where("a.goods_seq",$goods_seq,false);
		foreach($options as $k=>$v) if(!empty($v)) $this->db->where("option".($k+1),$v);
		$this->db->from("fm_goods_option as a left join fm_goods_supply as b on a.option_seq=b.option_seq");
		$query = $this->db->get();
		$goods_options = $query->result_array();
		
		foreach($goods_options as $goods_option){
				
			if($goods_option['stock']+$adjust_ea<0) {
				$callback='';
				openDialogAlert("최종 재고수량은 0보다 작을 수 없습니다.",400,140,'parent',$callback);
				exit;
			}
			
			$updateData = false;
			
			/* 분실, 오류, 불량, 기타 */
			if($reason != 'input'){
				if($chk_supply_price_replace){
					/* 매입가 단순 수정 */
					$supply_price = $input_supply_price;
					$this->db->set("supply_price",$supply_price);
					$updateData = true;
				}
			}
			
			/* 입고시 */
			if($reason == 'input'){
				/* 매입가 평균 계산 */
				$supply_price = $goods_option['supply_price']*$goods_option['stock']+$input_supply_price*$adjust_ea;
				$supply_price = $supply_price / ($goods_option['stock']+$adjust_ea);
				$this->db->set("supply_price",$supply_price);
				$updateData = true;
			}
			
			if($adjust_ea > 0) {
				$this->db->set("stock","stock+".$adjust_ea,false);
				$updateData = true;
			}
			if($adjust_ea < 0) {
				$this->db->set("stock","stock".$adjust_ea,false);
				$updateData = true;
			}
			
			if($updateData){
				$this->db->where("supply_seq",$goods_option['supply_seq']);
				$this->db->update("fm_goods_supply");
			}

		}
	}
	
	public function suboption_modify($goods_seq, $options, $input_supply_price, $adjust_ea, $reason, $chk_supply_price_replace){
		
		$this->db->where("a.goods_seq",$goods_seq,false);
		foreach($options as $k=>$v) if(!empty($v)) $this->db->where("suboption",$v);
		$this->db->from("fm_goods_suboption as a left join fm_goods_supply as b on a.suboption_seq=b.suboption_seq");
		$query = $this->db->get();
		$goods_options = $query->result_array();
		
		foreach($goods_options as $goods_option){
			
			if($goods_option['stock']+$adjust_ea<0) {
				$callback='';
				openDialogAlert("최종 재고수량은 0보다 작아질 수 없습니다.",400,140,'parent',$callback);
				exit;
			}
			
			$updateData = false;

			/* 분실, 오류, 불량, 기타 */
			if($reason != 'input'){
				if($chk_supply_price_replace){
					/* 매입가 단순 수정 */
					$supply_price = $input_supply_price;
					$this->db->set("supply_price",$supply_price);
					$updateData = true;
				}
			}
			
			if($reason == 'input'){
				/* 매입가 평균 계산 */
				$supply_price = $goods_option['supply_price']*$goods_option['stock']+$input_supply_price*$adjust_ea;
				$supply_price = $supply_price / ($goods_option['stock']+$adjust_ea);
				$this->db->set("supply_price",$supply_price);
				$updateData = true;
			}

			if($adjust_ea > 0) {
				$updateData = true;
				$this->db->set("stock","stock+".$adjust_ea,false);
			}
			if($adjust_ea < 0) {
				$updateData = true;
				$this->db->set("stock","stock".$adjust_ea,false);
			}
			
			if($updateData){
				$this->db->where("supply_seq",$goods_option['supply_seq']);
				$this->db->update("fm_goods_supply");
			}
		
		}
	}

}

/* End of file stockmodel.php */
/* Location: ./app/models/stockmodel.php */