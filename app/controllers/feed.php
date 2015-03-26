<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class feed extends front_base {
	public function _remove_special_char($str)
	{
		$str = str_replace('&','',$str);
		return $str;
	}

	public function xml(){
		
		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');

		$sql = "SELECT 
					A.goods_seq, A.goods_name, 
					(SELECT image FROM fm_goods_image WHERE goods_seq = A.goods_seq AND image_type = 'large' limit 1) as large, 
					(SELECT image FROM fm_goods_image WHERE goods_seq = A.goods_seq AND image_type = 'list1' limit 1) as list1, 
					B.consumer_price, 
					B.price,
					category_code
				FROM 
					fm_goods A LEFT JOIN fm_goods_option B ON A.goods_seq = B.goods_seq AND B.default_option = 'y'
					,fm_category_link l
				where 
					A.goods_seq=l.goods_seq 
					and l.link			= 1
					and A.goods_type	= 'goods' 
					and A.goods_status	= 'normal' 
					and A.goods_view	= 'look' 
					and A.string_price_use != '1'
				group by A.goods_seq
				";		
		$query	= mysql_query($sql);

		$url = "http://".$_SERVER['HTTP_HOST'];
		
		header("Content-type: text/xml;charset=utf-8"); 
		header("Cache-Control: no-cache, must-revalidate"); 
		header("Pragma: no-cache");  

		$html	="<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$html .= "<products>\n";
		echo $html;
		while($v = mysql_fetch_array($query)){
			$html = "";
			$r_category = array();
			$r_category_code = array();

			$v['goods_name'] = $this->_remove_special_char($v['goods_name']);
			$v['contents'] = $this->_remove_special_char($v['contents']);			

			$r_category_code = $this->categorymodel->split_category($v['category_code']);
			foreach($r_category_code as $k=> $value_category){				
				$r_category[$k] = $this->categorymodel->one_category_name($value_category);
			}

			//정가 없을 시 판매가를 기본가로 설정
			if(!$v['consumer_price']) $v['consumer_price'] = $v['price'];

			// 할인가 적용
			$v['price'] = $this->apply_sale($v);
			
			$html .= "<product id=\"".$v['goods_seq']."\">\n";
			$html .= "<name>".str_replace('&','&amp;',$v['goods_name'])."</name>\n";
			$html .= "<smallimage>".urlencode($url.$v['list1'])."</smallimage>\n"; 
			$html .= "<bigimage>".urlencode($url.$v['large'])."</bigimage>\n"; 
			$html .= "<producturl>".urlencode($url."/goods/view?no=".$v['goods_seq'])."</producturl>\n"; 
			$html .= "<description>".str_replace('&','&amp;',$v['comment'])."</description>\n"; 
			$html .= "<price>".$v['price']."</price>\n"; 
			$html .= "<retailprice>".$v['consumer_price']."</retailprice>\n"; 
			foreach($r_category as $k => $value_category){
				if($k<3){					
					$num = $k+1;
					$html .= "<categoryid".$num.">".str_replace('&','&amp;',$value_category)."</categoryid".$num.">\n";
				}
			}
			
			$html .= "</product>\n";
			echo $html;
		}
		$html = "";
		$html .= "</products>";

		echo $html;
	}
	
	public function apply_sale(&$data_goods)
	{		
		$this->load->library('sale');
		$applypage		= 'list';
		if	(!$this->reserves)	$this->reserves	= config_load('reserve');

		//----> sale library 적용 ( 정가기준 목록에서는 sale_price를 넘기지 않음 )
		unset($param, $sales);
		$this->sale->reset_init();
		$param['cal_type']			= 'each';
		$param['option_type']		= 'option';
		$param['reserve_cfg']		= $this->reserves;
		$param['member_seq']		= 0;
		$param['group_seq']			= 0;
		$param['consumer_price']	= $data_goods['consumer_price'];
		$param['price']				= $data_goods['price'];
		$param['total_price']		= $data_goods['price'];
		$param['ea']				= 1;
		$param['goods_ea']			= 1;
		$param['category_code']		= $data_goods['r_category'];
		$param['goods_seq']			= $data_goods['goods_seq'];
		if ($data_goods['marketing_sale']) $param['marketing_sale']	 = $data_goods['marketing_sale'];
		$param['goods']				= $data_goods;
		$this->sale->set_init($param);
		$sales						= $this->sale->calculate_sale_price($applypage);
		if ($data_goods['marketing_sale'] && $sales['sale_list']['coupon'] > 0) {
			$data_goods['coupon_won'] = iconv("UTF-8","euc-kr",$sales['sale_list']['coupon'].'원');
		}
		$data_goods['price']		= $sales['result_price'];

		return $data_goods['price'];
	}

}