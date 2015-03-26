<?php
/*************************/
/* 네이버 공통유입 스크립트 */
/*************************/
function naverWcsScript(){
	$CI =& get_instance();

	if($CI->config_basic['naver_wcs_use']!='y') return;
	$naver_wcs = config_load('naver_wcs');

	$CI->load->model('navermileagemodel');
	$cfg_naver_mileage = $CI->navermileagemodel->naver_mileage_display();	
	if($_SERVER['HTTPS']=='on'){
		echo '<script type="text/javascript" src="https://wcs.naver.net/wcslog.js"></script>';
	}else{
		echo '<script type="text/javascript" src="http://wcs.naver.net/wcslog.js"></script>';
	}
?>
<script type="text/javascript">
	if(!wcs_add) var wcs_add = {};
	wcs_add["wa"] = "<?=$naver_wcs['accountId']?>";
	<?if(count($naver_wcs['checkoutWhitelist'])){?>
	wcs.checkoutWhitelist = ["<?=implode('","',$naver_wcs['checkoutWhitelist'])?>"]; // 체크아웃 White list가 있을 경우
	<?}?>
	<?if($CI->config_system['domain']){?>
	wcs.inflow('<?=$CI->config_system['domain']?>');
	<?}else{?>
	wcs.inflow();
	<?}?>
	<?if( in_array($cfg_naver_mileage['naver_mileage_yn'],array('y','t')) ){?>
	var inflowParam = wcs.getMileageInfo();
	var naver_mileage_ba = 0;
	var naver_mileage_aa = 0;
	if (inflowParam != false) {
		naver_mileage_ba = wcs.getBaseAccumRate();
		naver_mileage_aa = wcs.getAddAccumRate();
	}
	<?}?>
	$(window).load(function() {
		wcs_do(); // 로그 수집 함수 (페이지 로딩후 실행됩니다)
	});
</script>
<?
}
?>