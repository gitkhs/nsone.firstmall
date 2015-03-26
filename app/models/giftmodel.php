<?php
class giftmodel extends CI_Model {
	public function get_gift($today,$goods_price,$gift_goods,$gift_categorys){
		### GIFT		
		$sql = "SELECT * FROM fm_gift WHERE gift_gb = 'order' AND display = 'y' AND start_date <= '{$today}' AND end_date >= '{$today}' order by end_date asc,gift_seq asc";		
		$query = $this->db->query($sql);
		
		$gift_cnt = 0;
		foreach ($query->result_array() as $v){
			unset($g_result);
			if($v['goods_rule']=='all'){
				$g_result = $this->get_gift_benefit($v['gift_seq'], $v['gift_rule']);
				
			}else if($v['goods_rule']=='category'){
				$category_check = false;
				foreach($gift_categorys as $data){
					$category_codes = $this->categorymodel->split_category($data);
		
					foreach($category_codes as $category_code){
		
						$sql = "SELECT count(*) as cnt FROM fm_gift_choice WHERE category_code = '{$category_code}' and gift_seq = '".$v['gift_seq']."'";
		
						$query = $this->db->query($sql);
						$boolen = $query->result_array();
		
						if($boolen[0]["cnt"] > 0){
							$category_check = true;
						}
					}
		
				}
				if($category_check){					
					$g_result = $this->get_gift_benefit($v['gift_seq'], $v['gift_rule']);
				}
		
			}else if($v['goods_rule']=='goods'){
				$goods_check = false;
				foreach($gift_goods as $data){
					$sql = "SELECT count(*) as cnt FROM fm_gift_choice WHERE goods_seq = '{$data}' and gift_seq = '{$v['gift_seq']}'";
					$query = $this->db->query($sql);
		
					$boolen = $query->result_array();
					if($boolen[0]["cnt"] > 0){
						$goods_check = true;
					}
				}
				if($goods_check){		
					$g_result = $this->get_gift_benefit($v['gift_seq'], $v['gift_rule']);
				}				
			}
			
			$v['benifits'] = $g_result;
					
			$gift_list = $v;

			if($gift_list['benifits']){
				return $gift_list;
			}
		}
		
		return $gift_list;
	}
	
	public function get_gift_goods($goods_seq)
	{
		$sql	= "SELECT a.goods_view, a.goods_status, sum(b.stock) stock,a.goods_name FROM fm_goods a, fm_goods_supply b WHERE a.goods_seq = b.goods_seq and a.goods_seq = ? group by a.goods_seq";
		$query	= $this->db->query($sql,$goods_seq);
		list($info)	= $query->result_array();		
		if($info['stock'] < 1 || $info['goods_view'] != "look" || $info['goods_status'] != "normal"){
			$info['stock'] = 0;
		}
		
		return $info;
		
	}
	
	public function get_gift_benefit($gift_seq,$type){		
	
		$sql	= "SELECT * FROM fm_gift_benefit WHERE gift_seq = '{$gift_seq}' order by eprice asc";
		$query	= $this->db->query($sql);
		$i = 0;
		foreach( $query->result_array() as $info ){
			
			$garr	= explode("|",$info['gift_goods_seq']);
			if($type=='default'){
				$info['ea']	= 1;
			}else if($type=='price'){
				$garr	= explode("|",$info['gift_goods_seq']);
				$info['ea']		= 1;
			}
			
			// 사은품 재고 체크
			$goods = array();
			if(count($garr)>0){
				foreach($garr as $gift_goods_seq){
					$goods_data = $this->get_gift_goods($gift_goods_seq);
					if($goods_data['stock'] > 0){
						$goods[] = $goods_data;	
					}
				}
			}
			
			if(count($goods)>0){
				$info['goods'] = $goods;
				$result[] = $info;
			}			
		}		
		
		if($result) return $result;
		else return false;
	
		
	}
}