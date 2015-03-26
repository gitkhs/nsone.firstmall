<?php
class zipcodemodel extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	public function get_where_query( $keyword, $zipcode_type )
	{			
		
		$return_keyword = $keyword;
		$keyword = trim($keyword);
		$keyword = str_replace(array('>','<','"','\'','\\'),'',$keyword);
		$keyword = preg_replace("/\-$/","",$keyword);
		$keyword = preg_replace("/(\s)+/"," ",$keyword);
		$keyword = preg_replace("/(동|면|읍|리)([0-9])([^가])/","$1 $2$3",$keyword);
		//$keyword = preg_replace("/(동|면|읍|리|로)([0-9])/","$1 $2",$keyword);
		$hangdong = false;		
		
		if(preg_match('/[0-9]+동/', $keyword)){
			$keywords_hangdong[] = preg_replace('/[0-9]+동/', '제$0', $keyword);
			$keywords_hangdong[] = preg_replace('/[0-9]+동/', '$0', $keyword);
			$hangdong = true;
		}

		if(preg_match("/(동|면|읍|리|로) [\s0-9가-하]+(번길|길|가)/",$keyword)){				
			$keyword = preg_replace("/(동|면|읍|리|로) ([\s0-9가-하]+)(번길|길|가)/","$1$2$3",$keyword);
		}

		if(preg_match("/[0-9가-하]+(번길|길|가)/",$keyword)){
			$keyword = preg_replace("/([0-9가-하])(번길|길|가)([\s0-9])/","$1$2 $3",$keyword);
			$keyword = str_replace("  "," ", $keyword);
		}else{
			$keyword = preg_replace("/(동|면|읍|리|로)([0-9])/","$1 $2",$keyword);
		}

		
		if(preg_match("/(.*)동([0-9]+(.*))/i",$keyword,$matches)){
			$keyword = $matches[1].'동'.$matches[2];
		}
		
		$keywords = array();		// 입력된 키워드
		$remain_keywords = array();	// 입력된 키워드중 where절에 포함되지 않은 나머지 키워드들
		$tmp_keywords = explode(' ',$keyword);

		// 키워드 정리
		
		foreach($tmp_keywords as $value){
			$value = trim($value);			
			if(!$value) continue;
			$keywords[] = $value;
		}

		$remain_keywords = $keywords;

		// 검색된 절
		$clauses = array();

		// 검색모드(도로명이 포함되었는지, 동검색이 포함되었는지)
		$mode = '';			
		foreach($keywords as $key=>$value){
			$value = mysql_real_escape_string($value);			
			if($value=='서울시' || $value=='서울') $value='서울특별시';				

			if(preg_match("/로$/",$value) || preg_match("/길$/",$value)) {
				$wheres[] = $sidoWheres[] = "STREET LIKE '".$value."%'";
				$clauses['street'] = $value;
				$mode = 'street';
			}
			else if(preg_match("/동$/",$value) || preg_match("/면$/",$value) || preg_match("/읍$/",$value) || preg_match("/동[0-9]+가$/",$value)) {
				if($hangdong && $zipcode_type=='street'){						
					$wheres[] = $sidoWheres[] = "(DONG LIKE '".$value."%' OR BUILDING LIKE '".$keywords_hangdong[0]."%' OR BUILDING LIKE '".$keywords_hangdong[1]."%')";
				}elseif($hangdong && $zipcode_type=='zibun'){
					$wheres[] = $sidoWheres[] = "(DONG LIKE '".$value."%' OR HANGDONG LIKE '".$keywords_hangdong[0]."%' OR HANGDONG LIKE '".$keywords_hangdong[1]."%')";
				}else{
					$wheres[] = $sidoWheres[] = "(DONG LIKE '".$value."%')";
				}
				$mode = 'dong';
			}else if(preg_match("/리$/",$value)) {
				$wheres[] = $sidoWheres[] = "RI LIKE '".$value."%'";
				$mode = 'dong';
			}else if(strlen($value) == 7 && preg_match("/([0-9]{3})-([0-9]){3}$/",$value)) { //우편번호 검색일 경우
				$wheres[] = $sidoWheres[] = "ZIPCODE = '".str_replace("-","",$value)."'";					
			}else{ // 키워드가 동면리읍로길 로 끝나지 않았을 경우
				if($key>0 && preg_match("/^[0-9]/",$value)){
					$nums = preg_replace("/[^0-9-]/","",$value);
					$nums = explode("-",$nums);

					$numWhere = "(JIBUN1 = '".$nums[0]."' or BUILDINGNUM1 = '".$nums[0]."')";
					if($nums[1])
					$numWhere = "(".$numWhere." and (JIBUN2 = '".$nums[1]."' or BUILDINGNUM2 = '".$nums[1]."'))";
					$wheres[] = $sidoWheres[] = $numWhere;
					
				}else{
					if($_GET['zipcode'] && preg_match("/^[0-9]/",$value)){ // 구지번으로 검색후 번지 검색

						$nums = preg_replace("/[^0-9-]/","",$value);
						$nums = explode("-",$nums);	

						$numWhere = "(JIBUN1 = '".$nums[0]."' or BUILDINGNUM1 = '".$nums[0]."')";
						if($nums[1])
						$numWhere = "(".$numWhere." and (JIBUN2 = '".$nums[1]."' or BUILDINGNUM2 = '".$nums[1]."'))";
						$wheres[] = $sidoWheres[] = $numWhere;

					}elseif($zipcode_type == "street"){ // 도로명 탭에서 로길이 없을경우
						$wheres[] = $sidoWheres[] = " (STREET LIKE '".$value."%' OR BUILDING LIKE '".$value."%' OR SIGUNGUBUILDING LIKE '".$value."%') ";
					}elseif($zipcode_type == "zibun"){ // 지번 탭에서 읍면동리가 없을경우
						if($hangdong){
							$wheres[] = $sidoWheres[] = "(DONG LIKE '".$value."%' OR SIGUNGUBUILDING LIKE '".$value."%') ";
						}else{
							$wheres[] = $sidoWheres[] = "(DONG LIKE '".$value."%' OR SIGUNGUBUILDING LIKE '".$value."%' OR HANGDONG LIKE '".$value."%') ";
						}
					}
				}
			}
		}		
		return array($wheres,$sidoWheres,$keyword);
	}

	public function zipcode_oldzibun($perpage)
	{
		/*
		디비추가 zipcode
		ALTER TABLE zipcode_street ADD INDEX zipcode_idx ( ZIPCODE );
		*/		
		
	    $this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();

		$zipcode_type = $_GET['zipcode_type'] ? $_GET['zipcode_type'] : "street";
		
		$_GET['page'] = $_GET['page'] ? $_GET['page'] : 1;
		$loop = "";
		if(isset($_GET['zipcode_keyword'])){
			$keyword = $_GET['zipcode_keyword'];
		}else{
			$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : false;
		}

		
		
	    $_GET['popup'] = 1;
		
		$loop = "";
		$dong = isset($keyword) ? $keyword : false;
		$dong = mysql_real_escape_string($dong);
		
		if($dong){
			$dong = preg_replace('/[\s]/','',trim($dong));
			
			/*
			// 2014-04-28 운영팀 요청 주석처리
			if( strlen($dong) >= 9 ){
				$tail = substr($dong,-3,3);
				if($tail == '동' || $tail == '면' || $tail == '읍'){
					$dong = substr($dong,0,-3);
				}
			}
			*/
			
			$query = "SELECT * FROM zipcode WHERE DONG LIKE '%".$dong."%'";
			$result = select_page($perpage,$_GET['page'],$perpage,$query,array(),$ZIP_DB);			
			foreach($result['record'] as $row){
				$row['ADDRESS'] = implode(' ',array($row['SIDO'],$row['GUGUN'],$row['DONG']));
				$row['ADDRESSVIEW'] = implode(' ',array($row['SIDO'],$row['GUGUN'],$row['DONG'],$row['BUNJI']));
				$loop[] = $row;
			}
		}	
		

		return array(			
			'page'=>$result['page'],
			'zipcode_type'=>$zipcode_type,
			'query_string'=>$query_string,
			'keyword'=>$keyword,
			'loop'=>$loop
		);
	}

	public function zipcode($perpage)
	{
		
		$this->load->helper('zipcode');
	    $ZIP_DB = get_zipcode_db();

		$zipcode_type = $_GET['zipcode_type'] ? $_GET['zipcode_type'] : "street";
		

		$_GET['page'] = $_GET['page'] ? $_GET['page'] : 1;
		$loop = "";
		if(isset($_GET['zipcode_keyword'])){
			$keyword = $_GET['zipcode_keyword'];
		}else{
			$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : false;
		}
		
		unset($_GET['zipcode_type']);
		//unset($_GET['old_zipcode']);
		foreach($_GET as $key => $value){			
			if($query_string) $query_string .= "&";
			if($key != "old_zipcode" && $key != "SIDO" && $key != "SIGUNGU" && $key != "keyword") $query_string .= $key."=".$value;
		}
		
		list($wheres,$sidoWheres,$keyword) = $this->get_where_query($keyword, $zipcode_type);
		
		if($wheres){
			
			$query = "SELECT * from zipcode_street WHERE ".implode(" AND ", $wheres);
			$str_query = $query;
			
			if($zipcode_type == 'street'){
				$query = "select * from ( {$query} ) A order by BUILDINGNUM1 asc";
			}else{
				$query = "select * from ( {$query} ) A order by JIBUN1 asc";
			}
			
			if($_GET['SIDO'] || $_GET['SIGUNGU']){
				$wheres = array();

				if($_GET['SIDO']){
					$wheres[] = "SIDO = '".$_GET['SIDO']."'";
				}

				if($_GET['SIGUNGU']){
					$wheres[] = "SIGUNGU = '".$_GET['SIGUNGU']."'";
				}

				$query = "select * from (
					{$str_query}
				) a where ".implode(" AND ", $wheres);
			}
		}

		
			
		if($query){
			$result = select_page($perpage,$_GET['page'],$perpage,$query,array(),$ZIP_DB);			
			
			foreach($result['record'] as $row){

				$BUILDINGNUM = $row['BUILDINGNUM2'] ? $row['BUILDINGNUM1'].'-'.$row['BUILDINGNUM2'] : $row['BUILDINGNUM1'];
				$JIBUN = $row['JIBUN2'] ? $row['JIBUN1'].'-'.$row['JIBUN2'] : $row['JIBUN1'];

				$row['ZIPCODE'] = substr($row['ZIPCODE'],0,3).'-'.substr($row['ZIPCODE'],3,3);
				$row['ADDRESS'] = implode(' ',array($row['SIDO'],$row['SIGUNGU'],$row['DONG'],$row['RI'],$JIBUN));
				$row['ADDRESS_STREET'] = implode(' ',array($row['SIDO'],$row['SIGUNGU'],$row['STREET'],$BUILDINGNUM));

				$arrDetails = array();
				if($row['DONG']) $arrDetails[] = $row['DONG'];
				if($row['SIGUNGUBUILDING']) {
					$row['ADDRESS'] .= ' '.$row['SIGUNGUBUILDING'];
					$arrDetails[] = $row['SIGUNGUBUILDING'];
				}

				if($arrDetails){
					$row['ADDRESS_STREET'] .= ' ('.implode(', ',$arrDetails).')';
				}

				$row['ADDRESS'] = preg_replace("/(\s)+/"," ",$row['ADDRESS']);
				$row['ADDRESS_STREET'] = preg_replace("/(\s)+/"," ",$row['ADDRESS_STREET']);

				$loop[] = $row;
			}
		}
		
		

		//$keyword = $return_keyword;

		if(count($sidoWheres)){
			$arrSido = $ZIP_DB->query("select distinct SIDO from zipcode_street WHERE ".implode(" AND ", $sidoWheres)." group by SIDO");
			$arrSido = $arrSido->result_array();
		}

		if($sidoWheres){
			$arrSigungu = $ZIP_DB->query("select SIGUNGU from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' AND ".implode(" AND ", $sidoWheres)." GROUP BY SIGUNGU");
		}else{
			$arrSigungu = $ZIP_DB->query("select SIGUNGU from zipcode_street WHERE SIDO = '".$_GET['SIDO']."' GROUP BY SIGUNGU");
		}

		$arrSigungu = $arrSigungu->result_array();
		
		return array(
			'arrSido'=>$arrSido,
			'arrSigungu'=>$arrSigungu,
			'page'=>$result['page'],
			'zipcode_type'=>$zipcode_type,
			'query_string'=>$query_string,
			'keyword'=>$keyword,
			'loop'=>$loop
		);

	}

}