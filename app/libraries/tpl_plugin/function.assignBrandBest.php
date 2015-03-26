<?php 
function assignBrandBest(){
	$CI =& get_instance();

	$sql = "SELECT * FROM fm_brand WHERE level = '2' AND best = 'Y' AND hide='0' ORDER BY category_code";
	$query = $CI->db->query($sql);
	
	foreach($query->result_array() as $row){
		$loop[] = $row;
	}

	return $loop;
}
?>