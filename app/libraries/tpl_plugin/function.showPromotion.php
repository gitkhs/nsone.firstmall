<?php
/* 프로모션코드 출력*/
function showPromotion($template_path='')
{
	$CI =& get_instance();
	$html = "<font color='red'>프로모션코드 테스트입니다.</font>";
	echo "<div class='ShowPromotionLay' id='ShowPromotionLay' >";
	echo $html;
	echo "</div>";

	return;
}

?>