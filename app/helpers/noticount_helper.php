<?php
// '관리자 할일 수'용 날짜
function str_to_priod_for_noti_count($str)
{	
	switch($str){
		case "1주일" : 
			$start_date = date('Y-m-d 00:00:00',strtotime("-1 week"));
			break;
		case "2주일" : 
			$start_date = date('Y-m-d 00:00:00',strtotime("-2 week"));
			break;
		case "1개월" : 
			$start_date = date('Y-m-d 00:00:00',strtotime("-1 month"));
			break;
		case "3개월" : 
			$start_date = date('Y-m-d 00:00:00',strtotime("-3 month"));
			break;
		case "6개월" : 
			$start_date = date('Y-m-d 00:00:00',strtotime("-6 month"));			
			break;
	}
	
	return $start_date;
}
?>