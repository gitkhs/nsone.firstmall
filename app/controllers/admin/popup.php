<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

class popup extends admin_base {

	public function __construct() {
		parent::__construct();
	}	

	public function _zipcode_oldzibun()
	{
		// 우편번호 설정
		$cfg_zipcode = config_load('zipcode');
		if($this->siteType == "mobile")	
			$perpage = "5";
		else 
			$perpage = "10";		

	    $this->load->model('zipcodemodel');		
		$data = $this->zipcodemodel->zipcode_oldzibun($perpage);

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign("cfg_zipcode",$cfg_zipcode);
		$this->template->assign("zipcodeFlag",$_GET['zipcodeFlag']);
		$this->template->assign("zipcode_type",$data['zipcode_type']);
		$this->template->assign("query_string",$data['query_string']);
		$this->template->assign("keyword",$data['keyword']);
		$this->template->assign("page",$data['page']);
		$this->template->assign("loop",$data['loop']);
		$this->template->print_("tpl");
	}
	
	public function zipcode()
	{
		// 우편번호 설정
		$cfg_zipcode = config_load('zipcode');

		// 구지번 검색
		if($_GET['zipcode_type'] == 'oldzibun'){
			$this->_zipcode_oldzibun();
			exit;
		}

		if($this->siteType == "mobile")	
			$perpage = "5";
		else 
			$perpage = "10";		

	    $this->load->model('zipcodemodel');
		$data = $this->zipcodemodel->zipcode($perpage);
		
		
		if($this->siteType == "mobile"){
			$this->template->assign("zipcodeFlag",$_GET['zipcodeFlag']);			
		}
		
		$file_path	= $this->template_path();
		$this->template->assign("cfg_zipcode",$cfg_zipcode);
		$this->template->assign("zipcode_type",$data['zipcode_type']);
		$this->template->assign("query_string",$data['query_string']);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign("arrSido",$data['arrSido']);
		$this->template->assign("arrSigungu",$data['arrSigungu']);
		$this->template->assign("zipcodeFlag",$_GET['zipcodeFlag']);		
		$this->template->assign("keyword",$data['keyword']);
		$this->template->assign("page",$data['page']);
		$this->template->assign("loop",$data['loop']);
		$this->template->print_("tpl");

	}

	public function zipcode_street_sigungu()
	{
		$arrSigungu = array();
	    $this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();

		$zipcode_type = $_GET['zipcode_type'] ? $_GET['zipcode_type'] : "street";

		if(isset($_GET['zipcode_keyword'])){
			$keyword = $_GET['zipcode_keyword'];
		}else{
			$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : false;
		}

		if($keyword){
			$keyword = trim($keyword);
			$keyword = str_replace(array('>','<','"','\'','\\'),'',$keyword);
			$keyword = preg_replace("/\-$/","",$keyword);
			$keyword = preg_replace("/(\s)+/"," ",$keyword);
			$keyword = preg_replace("/(동|면|읍|리)([0-9])([^가])/","$1 $2$3",$keyword);

			if(preg_match("/(.*)로([0-9]+(.*))/i",$keyword,$matches)){
				$keyword = $matches[1].'로'.$matches[2];
			}
			if(preg_match("/(.*)동([0-9]+(.*))/i",$keyword,$matches)){
				$keyword = $matches[1].'동'.$matches[2];
			}

			$wheres = array();
			$keywords = array();		// 입력된 키워드
			$remain_keywords = array();	// 입력된 키워드중 where절에 포함되지 않은 나머지 키워드들
			$tmp_keywords = explode(' ',$keyword);

			// 키워드 정리
			foreach($tmp_keywords as $value){
				$value = trim($value);
				if(preg_match("/(.*)로([0-9]+(.*))/i",$value,$matches)){
					$keyword2 = $matches[1].'로 '.$matches[2];
					$tmp_keywords2 = explode(' ',$keyword2);
					foreach($tmp_keywords2 as $value2){
						$value2 = trim($value2);
						if(!$value) continue;
						$keywords[] = $value2;
					}
				}

				if(!$value) continue;
				$keywords[] = $value;
			}
			$remain_keywords = $keywords;

			// 검색된 절
			$clauses = array();

			// 검색모드(도로명이 포함되었는지, 동검색이 포함되었는지)
			$mode = '';

			foreach($keywords as $value){

				if($value=='서울시' || $value=='서울') $value='서울특별시';
				/*
				if(preg_match("/시$/",$value) || preg_match("/도$/",$value) || preg_match("/군$/",$value) || preg_match("/구$/",$value)) {
					if(preg_match("/시$/",$value)){
						$wheres[] = "(SIDO LIKE '".$value."%' or SIGUNGU LIKE '".$value."%')";
						$clauses['SIDO'] = $clauses['SIGUNGU'] = $clauses['SI'] = $value;
					}elseif(preg_match("/도$/",$value)){
						$wheres[] = "SIDO LIKE '".$value."%'";
						$clauses['SIDO'] = $clauses['DO'] = $value;
					}elseif(preg_match("/구$/",$value)){
						$wheres[] = "(SIGUNGU LIKE '".$value."%' or SIGUNGU LIKE '".trim($clauses['SI'].' '.$value)."%')";
						$clauses['SIGUNGU'] = $clauses['GU'] = $value;
					}else{
						$wheres[] = "SIGUNGU LIKE '".$value."%'";
					}

					continue;
				}
				*/

				if(preg_match("/로$/",$value) || preg_match("/길$/",$value)) {
					$wheres[] = $sidoWheres[] = "STREET LIKE '".$value."%'";
					$clauses['street'] = $value;
					$mode = 'street';
				}
				else if(preg_match("/동$/",$value) || preg_match("/면$/",$value) || preg_match("/읍$/",$value) || preg_match("/동[0-9]+가$/",$value)) {
					$wheres[] = $sidoWheres[] = "(DONG LIKE '".$value."%')";
					$mode = 'dong';
				}
				else if(preg_match("/리$/",$value)) {
					$wheres[] = $sidoWheres[] = "RI LIKE '".$value."%'";
					$mode = 'dong';
				}
				else if(!preg_match("/^[0-9]/",$value)){
					$wheres[] = $sidoWheres[] = "SIGUNGUBUILDING LIKE '".$value."%'";
					$mode = 'dong';
				}

				if(preg_match("/^[0-9]/",$value)){
					$nums = preg_replace("/[^0-9-]/","",$value);
					$nums = explode("-",$nums);

					if($mode=='dong'){
						$numWhere = "JIBUN1 = '".$nums[0]."'";
						if($nums[1])
						$numWhere = "(".$numWhere." and JIBUN2 = '".$nums[1]."')";
						$wheres[] = $sidoWheres[] = $numWhere;
					}

					if($mode=='street'){
						$numWhere = "BUILDINGNUM1 = '".$nums[0]."'";
						if($nums[1])
						$numWhere = "(".$numWhere." and BUILDINGNUM2 = '".$nums[1]."')";
						$numWhere = "((".$numWhere.") or STREET LIKE '".$clauses['street'].$nums[0]."번길%')";
						$wheres[] = $sidoWheres[] = $numWhere;
					}

					if(!$mode){
						$numWhere = "(JIBUN1 = '".$nums[0]."' or BUILDINGNUM1 = '".$nums[0]."')";
						if($nums[1])
						$numWhere = "(".$numWhere." and (JIBUN2 = '".$nums[1]."' or BUILDINGNUM2 = '".$nums[1]."'))";
						$wheres[] = $sidoWheres[] = $numWhere;
					}
				}
			}

			if(!$mode && !$wheres){
				$wheres[] = $sidoWheres[] = "STREET LIKE '".$keyword."%' or BUILDING LIKE '".$keyword."%' or DONG LIKE '".$keyword."%''".$keyword."%' or SIGUNGUBUILDING LIKE '".$keyword."%'";
			}

			if($sidoWheres){
				$arrSigungu = $ZIP_DB->query("select SIGUNGU from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' AND ".implode(" AND ", $sidoWheres)." GROUP BY SIGUNGU");
			}else{
				$arrSigungu = $ZIP_DB->query("select SIGUNGU from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' GROUP BY SIGUNGU");
			}

			$arrSigungu = $arrSigungu->result_array();

		}

		echo json_encode($arrSigungu);
	}

	public function sido()
	{
	    $this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();

		$loop = "";
		if($_GET['sido']){
			$query = "SELECT
			concat(SIDO,' ',SIGUNGU) as sigungu_addr,
			concat(SIDO,' ',SIGUNGU,' ', DONG) as addr,
			concat(SIDO,' ',SIGUNGU,' ',STREET) as addr_street
			FROM zipcode_street WHERE";

			if($_GET['zipcode_type'] == "street"){
				$wheres[] = $sidoWheres[] = "(STREET LIKE '".$_GET['sido']."%' OR SIGUNGU LIKE '".$_GET['sido']."%')";// or BUILDING LIKE '".$_GET['sido']."%'
			}else{
				$wheres[] = $sidoWheres[] = "(DONG LIKE '".$_GET['sido']."%' OR SIGUNGU LIKE '".$_GET['sido']."%')";// or SIGUNGUBUILDING LIKE '".$_GET['sido']."%'
			}

			$query = $ZIP_DB->query($query.implode(" AND ", $sidoWheres)." group by SIDO,SIGUNGU,DONG");
			$i=0;
			foreach ($query->result_array() as $row){

				if($before_sigungu != $row['sigungu_addr']){
					$loop[$i]['addr'] = $row['sigungu_addr'];
					$loop[$i]['addr_street'] = $row['sigungu_addr'];
					$i++;
				}
				$before_sigungu = $row['sigungu_addr'];
				$loop[$i]['addr'] = $row['addr'];
				$loop[$i]['addr_street'] = $row['addr_street'];
				$i++;
			}

		}

		$file_path	= $this->template_path();
		$this->template->define(array('tpl'=>$file_path));
		$this->template->assign("sidoFlag",$_GET['sidoFlag']);
		$this->template->assign("sido",$_GET['sido']);
		$this->template->assign("idx",$_GET['idx']);
		$this->template->assign("loop",$loop);
		$this->template->print_("tpl");
	}

	public function information()
	{
		$file_path	= $this->template_path();
		$template_path = $_GET['template_path'].'.html';
		$file_path	= str_replace('information.html',$template_path,$file_path);
		$this->template->define(array('tpl'=>$file_path));
		$this->template->print_("tpl");
	}

}

/* End of file coupon.php */
/* Location: ./app/controllers/admin/coupon.php */