<?php

/**
 * @author lgs
 */

function showNaverMileageButton($mode='settle')
{
	if($mode=='settle'){
		$tag = '<div id="naver_mileage_info">
		<a href="javascript:showNaverCashAccumPopup()"><img src="/data/icon/naver_mileage/icon_btn.gif" border="0"></a>
		버튼을 클릭해서 <span style="color:green;font-weight:bold"><span class="naver_mileage_accum_rate">0</span>% 적립</span> 받고 사용하세요
		<a href="javascript:openMileageIntroPopup()"><img src="http://static.mileage.naver.net/static/20120102/ext/v4/ico.gif" /></a>
		</div>
		<div id="naver_mileage" class="hide">
		<span id="naver_mileage_use_msg"></span>
		<a href="javascript:showNaverCashAccumPopup()"><img src="/data/icon/naver_mileage/icon_update.gif" border="0"></a>
		<a href="javascript:openMileageIntroPopup()"><img src="http://static.mileage.naver.net/static/20120102/ext/v4/ico.gif" /></a>
		</div>
		<input type="hidden" name="naver_mileage_txId" id="naver_mileage_txId" value="" />';
	}else if($mode=='view'){
		$tag = '
		<span style="color:green;font-weight:bold;">
		<span class="naver_mileage_basic_accum_rate">0</span>%
		<span class="naver_mileage_add_accum" style="display:none">+ 추가<span class="naver_mileage_add_accum_rate">0</span>%</span>
		</span>
		적립
		<a href="javascript:openMileageIntroPopup()"><img src="http://static.mileage.naver.net/static/20120102/ext/v4/ico.gif" /></a>
		';
	}
	return $tag;
}