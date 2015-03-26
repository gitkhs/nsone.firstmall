<?php 
function assignBrandMenuData(){
	$CI =& get_instance();

	setlocale(LC_CTYPE,C);

	$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','ㄱ','ㄴ','ㄷ','ㄹ','ㅁ','ㅂ','ㅅ','ㅇ','ㅈ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ','123');
	$arr2 = array(
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

	$data = array();
	$chks = array();
	
	foreach($arr as $v) $data[$v] = array();
	
	$query = $CI->db->query("select title,title_eng,category_code from fm_brand where category_code!='' and level=2 and hide_in_gnb='0' order by title,title_eng");

	foreach($query->result_array() as $row){
		
		foreach($arr as $key=>$prefix){
			if($row['title_eng']){
				if($prefix=='123'){
					if(is_numeric(substr($row['title_eng'],0,1))){
						$row['prn_title'] = $row['title_eng'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
					if(is_numeric(substr($row['title_eng'],0,1))){
						$row['prn_title'] = $row['title_eng'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
				}elseif('a' <= $prefix && $prefix <= 'z'){
					if(strtolower(substr($row['title_eng'],0,1))==$prefix){
						$row['prn_title'] = $row['title_eng'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
				}elseif(in_array($prefix,array_keys($arr2))){
					if($arr2[$prefix][0]<=substr($row['title_eng'],0,3) && substr($row['title_eng'],0,3)<$arr2[$prefix][1]){
						$row['prn_title'] = $row['title_eng'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
				}
			}
			if($row['title']){
				if($prefix=='123'){
					if(is_numeric(substr($row['title'],0,1))){
						$row['prn_title'] = $row['title'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
					if(is_numeric(substr($row['title'],0,1))){
						$row['prn_title'] = $row['title'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
				}elseif('a' <= $prefix && $prefix <= 'z'){
					if(strtolower(substr($row['title'],0,1))==$prefix){
						$row['prn_title'] = $row['title'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
				}elseif(in_array($prefix,array_keys($arr2))){
					if($arr2[$prefix][0]<=substr($row['title'],0,3) && substr($row['title'],0,3)<$arr2[$prefix][1]){
						$row['prn_title'] = $row['title'];
						if(in_array($row['prn_title'],$chks)) continue;
						$chks[] = $row['prn_title'];
						$data[$prefix][] = $row;
					}
				}
			}

		}
	}

	$CI->template->assign(array('brandMenuData'=>$data));
}
?>