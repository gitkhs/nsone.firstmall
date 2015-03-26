<?php
/**
 * 검색옵션
 * @author ocw
 * @since version 1.0 - 2012.12.03
 */
class SearchoptionModel extends CI_Model {
	
	var $conditions = array();
	
	var $sqlSelectClause = "";
	var $sqlFromClause = "";
	var $sqlWhereClause = "";
	var $sqlGroupbyClause = "";

	function __construct() {
		parent::__construct();
	}
	
	public function set_conditions(){
		$params = $_POST ? $_POST : $_GET;

		if($params['category_code'])	$this->conditions['category_code']	= is_array($params['category_code'])?$params['category_code']:array($params['category_code']);
		if($params['brand_code'])		$this->conditions['brand_code']		= is_array($params['brand_code'])?$params['brand_code']:array($params['brand_code']);
		if($params['search_text'])		$this->conditions['search_text']	= $params['search_text']?$params['search_text']:null;
		
		if($params['ajax_so_category'])	$this->conditions['category_code']	= $params['ajax_so_category'];
		if($params['ajax_so_brand'])	$this->conditions['brand_code']		= $params['ajax_so_brand'];
		if($params['ajax_so_option1'])	$this->conditions['option1']		= $params['ajax_so_option1'];
		if($params['ajax_so_option2'])	$this->conditions['option2']		= $params['ajax_so_option2'];
		if($params['ajax_so_rate'])		$this->conditions['rate']			= $params['ajax_so_rate'];
	}
	
	public function set_where(){

		if($this->conditions['search_text']){
			$this->conditions['search_text'] = str_replace(array('"',"'"),"",$this->conditions['search_text']);
			
			$this->sqlWhereClause .= " and 
			(
				fm_goods.goods_name like '%{$this->conditions['search_text']}%'
				or fm_goods.goods_code = '{$this->conditions['search_text']}'
				or fm_goods.summary like '%{$this->conditions['search_text']}%'
				or fm_goods.keyword like '%{$this->conditions['search_text']}%'
			)
			";
		}
		
		if($this->conditions['category_code']){
			$this->sqlFromClause .= " inner join fm_category_link on fm_category_link.goods_seq = fm_goods.goods_seq";
			$this->sqlWhereClause .= " and fm_category_link.category_code in ('".implode("','",$this->conditions['category_code'])."')";
		}
		
		if($this->conditions['brand_code']){
			if(!in_array("fm_brand_link",$this->joinTables)) $this->sqlFromClause .= " inner join fm_brand_link on fm_brand_link.goods_seq = fm_goods.goods_seq";
			$this->sqlWhereClause .= " and fm_brand_link.category_code in ('".implode("','",$this->conditions['brand_code'])."')";
			
			$this->joinTables[] = "fm_brand_link";
		}
		
		if($this->conditions['option1']){
			if(!in_array("fm_goods_option",$this->joinTables)) $this->sqlFromClause .= " inner join fm_goods_option on fm_goods_option.goods_seq = fm_goods.goods_seq";
			$this->sqlWhereClause .= " and fm_goods_option.option1 in ('".implode("','",$this->conditions['option1'])."')";
			
			$this->joinTables[] = "fm_goods_option";
		}
		
		if($this->conditions['option2']){
			if(!in_array("fm_goods_option",$this->joinTables)) $this->sqlFromClause .= " inner join fm_goods_option on fm_goods_option.goods_seq = fm_goods.goods_seq";
			$this->sqlWhereClause .= " and fm_goods_option.option2 in ('".implode("','",$this->conditions['option2'])."')";
			
			$this->joinTables[] = "fm_goods_option";
		}
		
		if($this->conditions['brand_prefix_group'] || $this->conditions['brand_prefix']){

			if(!in_array("fm_brand_link",$this->joinTables)) $this->sqlFromClause .= " 
				inner join fm_brand_link on fm_brand_link.goods_seq = fm_goods.goods_seq
				inner join fm_brand on (fm_brand.category_code = fm_brand_link.category_code and fm_brand.hide='0')
			";
			
			if($this->conditions['brand_prefix_group']=='alpha'){
				if($this->conditions['brand_prefix']=='123'){
					$this->sqlWhereClause .= " and (
						substring(fm_brand.title,1,1) in ('0','1','2','3','4','5','6','7','8','9')
						or
						substring(fm_brand.title_eng,1,1) in ('0','1','2','3','4','5','6','7','8','9')
					)
					";
				}elseif($this->conditions['brand_prefix']){
					$prefix = strtolower(substr($this->conditions['brand_prefix'],0,1));
					$this->sqlWhereClause .= " and substring(fm_brand.title_eng,1,1) = '{$this->conditions['brand_prefix']}'";
				}else{
					$prefix = strtolower(substr($this->conditions['brand_prefix'],0,1));
					$this->sqlWhereClause .= " and substring(fm_brand.title_eng,1,1) in ('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z')";
				}
			}
			
			if($this->conditions['brand_prefix_group']=='korean'){
				if($this->conditions['brand_prefix']){
					$arr = array(
						'ㄱ' => array('가','나'),
						'ㄴ' => array('나','다'),
						'ㄷ' => array('다','라'),
						'ㄹ' => array('라','마'),
						'ㅁ' => array('마','바'),
						'ㅂ' => array('바','사'),
						'ㅅ' => array('사','아'),
						'ㅇ' => array('아','자'),
						'ㅈ' => array('자','차'),
						'ㅊ' => array('차','카'),
						'ㅋ' => array('카','타'),
						'ㅌ' => array('타','파'),
						'ㅍ' => array('파','하'),
						'ㅎ' => array('하','힣')
					);
					$prefix = $arr[$this->conditions['brand_prefix']];
					
					$this->sqlWhereClause .= " and fm_brand.title >= '{$prefix[0]}'";
					$this->sqlWhereClause .= " and fm_brand.title < '{$prefix[1]}'";
				}else{
					$this->sqlWhereClause .= " and fm_brand.title >= '가'";
					$this->sqlWhereClause .= " and fm_brand.title <= '힣'";
				}
			}

			$this->joinTables[] = "fm_brand_link";
		}
		
		if($this->conditions['rate']){
			if(!in_array("fm_goods_option_default",$this->joinTables)) $this->sqlFromClause .= " inner join fm_goods_option as fm_goods_option_default on (fm_goods_option_default.goods_seq = fm_goods.goods_seq and fm_goods_option_default.default_option='y')";
			$tmpWheres = array();
			foreach($this->conditions['rate'] as $rate){
				$tmpWheres[] = "round(100-fm_goods_option_default.price/fm_goods_option_default.consumer_price*100) between '{$rate}' and '".($rate+9)."'";
			}
			$this->sqlWhereClause .= " and (".implode(" or ", $tmpWheres).")";
			$this->joinTables[] = "fm_goods_option_default";
		}
		
	}
	
	public function get_results($kind){
		
		$this->db->from("fm_goods");
		$this->set_conditions();
		
		$this->joinTables = array();
		$this->sqlSelectClause = "";
		$this->sqlFromClause = "fm_goods ";
		$this->sqlWhereClause = " 1 ";
		$this->sqlGroupbyClause = "";
		$this->sqlOrderByClause = "";

		switch($kind){
			case "category":
				unset($this->conditions['category_code']);
				$this->sqlSelectClause = "fm_category.category_code as `key`,fm_category.title as title, fm_category.level-2 as depth";
				$this->sqlFromClause .= "
					inner join fm_category_link on fm_category_link.goods_seq = fm_goods.goods_seq
					inner join fm_category on (fm_category.category_code = fm_category_link.category_code and fm_category.hide='0')
				";
				/*
				if($this->conditions['brand_code']){
					$this->sqlWhereClause .= " and fm_category.level>3 ";
				}
				*/
				$this->sqlGroupbyClause = "fm_category.category_code";
				$this->sqlOrderByClause = "order by `key`";
				 
			break;
			case "brand":
				unset($this->conditions['brand_code']);
				$this->sqlSelectClause = "fm_brand.category_code as `key`,fm_brand.title as title,fm_brand.title_eng as title_eng";
				$this->sqlFromClause .= "
					inner join fm_brand_link on fm_brand_link.goods_seq = fm_goods.goods_seq
					inner join fm_brand on (fm_brand.category_code = fm_brand_link.category_code and fm_brand.hide='0')
				";
				$this->sqlGroupbyClause = "fm_brand.category_code";
				
				$this->joinTables[] = "fm_brand_link";
			break;
			case "option1":
				unset($this->conditions['option1']);
				$this->sqlSelectClause = "fm_goods_option.option1 as `key`,fm_goods_option.option1 as title";
				if(!in_array("fm_goods_option",$this->joinTables)) $this->sqlFromClause .= " inner join fm_goods_option on fm_goods_option.goods_seq = fm_goods.goods_seq";
				$this->sqlGroupbyClause = "fm_goods_option.option1";
				
				$this->joinTables[] = "fm_goods_option";
			break;
			case "option2":
				unset($this->conditions['option2']);
				$this->sqlSelectClause = "fm_goods_option.option2 as `key`,fm_goods_option.option2 as title";
				if(!in_array("fm_goods_option",$this->joinTables)) $this->sqlFromClause .= " inner join fm_goods_option on fm_goods_option.goods_seq = fm_goods.goods_seq";
				$this->sqlGroupbyClause = "fm_goods_option.option2";
				
				$this->joinTables[] = "fm_goods_option";
			break;
			case "rate":
				unset($this->conditions['rate']);
				$this->sqlSelectClause = "round(100-fm_goods_option_default.price/fm_goods_option_default.consumer_price*100) as `key`,concat(round(100-fm_goods_option_default.price/fm_goods_option_default.consumer_price*100),'%') as title";
				if(!in_array("fm_goods_option_default",$this->joinTables)) $this->sqlFromClause .= " inner join fm_goods_option as fm_goods_option_default on (fm_goods_option_default.goods_seq = fm_goods.goods_seq and fm_goods_option_default.default_option='y')";
				$this->sqlGroupbyClause = "`key`";
				$this->sqlOrderByClause = "order by `key`";
				
				$this->joinTables[] = "fm_goods_option_default";
			break;
		}
		
		$this->set_where();

		$sql = "
		select {$this->sqlSelectClause}
		from {$this->sqlFromClause}
		where {$this->sqlWhereClause}
		group by {$this->sqlGroupbyClause}
		{$this->sqlOrderByClause}
		";

		$query = $this->db->query($sql);
		$result = array();
		foreach($query->result_array() as $row){
			if($row['title']) $result[] = $row;
		}
		
		if($kind=='rate'){
			$result_prn = array();
			foreach($result as $row){
				if(1	<=$row['key'] && $row['key']<=	10)		$result_prn['1'] = '10% 이하';
				if(11	<=$row['key'] && $row['key']<=	20)		$result_prn['11'] = '11% ~ 20%';
				if(21	<=$row['key'] && $row['key']<=	30)		$result_prn['21'] = '21% ~ 30%';
				if(31	<=$row['key'] && $row['key']<=	40)		$result_prn['31'] = '31% ~ 40%';
				if(41	<=$row['key'] && $row['key']<=	50)		$result_prn['41'] = '41% ~ 50%';
				if(51	<=$row['key'] && $row['key']<=	60)		$result_prn['51'] = '51% ~ 60%';
				if(61	<=$row['key'] && $row['key']<=	70)		$result_prn['61'] = '61% ~ 70%';
				if(71	<=$row['key'] && $row['key']<=	80)		$result_prn['71'] = '71% ~ 80%';
				if(81	<=$row['key'] && $row['key']<=	90)		$result_prn['81'] = '81% ~ 90%';
				if(91	<=$row['key'] && $row['key']<=	100)	$result_prn['91'] = '91% ~ 100%';
			}
			$result = array();
			foreach($result_prn as $k=>$v){
				$result[] = array('key'=>$k,'title'=>$v);
			}
		}
		
		return $result;
	}
}
?>