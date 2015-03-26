<?php

/* 상품디스플레이 페이징 출력*/
function showDesignDisplayPaging($display_seq,$perpage)
{
	$CI =& get_instance();
	$CI->template->include_('showDesignDisplay'); 
	
	return showDesignDisplay($display_seq,$perpage);
}
?>