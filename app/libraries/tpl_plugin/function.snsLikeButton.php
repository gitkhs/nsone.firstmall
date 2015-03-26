<?php

/**
 * @author cow
 */

function snsLikeButton($goods_seq, $fbliketype, $adminview = null, $adminviewbox = 'unlike')
{
	$CI =& get_instance();

	$CI->is_file_facebook_css = '<link rel="stylesheet" type="text/css" href="/app/javascript/plugin/fblike/css/like.css?v=20140307" />';
	$CI->is_file_facebook_js = '<script type="text/javascript" src="/app/javascript/plugin/fblike/like-layout.js?v=20140307" charset="utf8"></script>';

	if($CI->arrSns['fb_like_box_type'] == 'NO' && defined('__ADMIN__') != true) return false;
	if( $adminview=='opengrapy' && defined('__ADMIN__') == true) {
		echo $CI->is_file_facebook_css;
		echo $CI->is_file_facebook_js;

		if( $adminviewbox != 'unlike' ) {
			$likeok = 'style="color:red" data-fblikeseq=""  data-fblikeid="" title="좋아요 취소" ';//
			$likeokimg = ($CI->arrSns['fb_likebox_icon'])?$CI->arrSns['fb_likebox_icon']:"/app/javascript/plugin/fblike/images/fblikebox.png";
		}else{
			$likeok = 'style="color:blue" data-fblikeseq=""  data-fblikeid="" title="좋아요" ';
			$likeokimg = ($CI->arrSns['fb_unlikebox_icon'])?$CI->arrSns['fb_unlikebox_icon']:"/app/javascript/plugin/fblike/images/fbunlikebox.png";
		}

		$facebooklink .= '<div class="fblikeopengrapybtn fb-og-like-lay'.$goods_seq.' hand" data-href="'.urlencode($href).'" goodsseq="'.$goods_seq.'" data-layout="'.$fblikebox.'" data-send="false" data-show-faces="false" data-host="'.$httphost.'" data-returnurl="'.$_SERVER['HTTP_HOST'].'"  data-ssid="'.$CI->session->userdata('session_id').'" '.$likeok.'>';
		$facebooklink .= '<div  style="display: inline-block;" ><img class=" fb-og-like-img'.$adminviewbox.'" src="'.$likeokimg.'"   align="absmiddle" ></div>';//img
		$facebooklink .= '<div style="display: inline-block;" >
		<div class="pluginCountButton pluginCountNum">
		<span >
			<span class="fb-og-like-count fb-og-like-count'.$goods_seq.'">1</span>
		</span>
		</div>
		<div class="pluginCountButtonNub"><s></s><i></i></div>
		</div>';//countbox
	$facebooklink .= '</div>';

	echo $facebooklink;
	}else{

		if( $CI->__APP_USE__ == 'f' ) {
			if($CI->mobileMode || $CI->storemobileMode){
				$href = str_replace("//m.","//",$CI->likeurl).'&no='.$goods_seq;
			}else{
				$href = $CI->likeurl.'&no='.$goods_seq;
			}
			$fblikebox = ($fbliketype==1 || $fbliketype =='box_count' )?'box_count':'button_count';
			if( $CI->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {
				$httphost = $_SERVER[HTTP_HOST];
			}else{
				$httphost = $CI->config_system['subDomain'];
			}

			if( strstr($_SERVER['HTTP_HOST'],"m.")   && !strstr($httphost,"m.") ){
				$httphost = "m.".$httphost;
			}

			if(( ($CI->arrSns['fb_like_box_type'] == 'API' && !$adminview) || ($adminview=='opengrapy') ) ){
				echo $CI->is_file_facebook_tag;
			}

			if(( ($CI->arrSns['fb_like_box_type'] == 'OP' && !$adminview) || ($adminview=='opengrapy') ) ) {
				if( defined('is_file_facebook_css_result') != true || strstr($CI->template_path,'goods/view') ) {
					echo $CI->is_file_facebook_css;
					echo $CI->is_file_facebook_js;
					define('is_file_facebook_css_result',true);
				}
			}
			if( ($CI->arrSns['fb_like_box_type'] == 'OP' && !$adminview) || ($adminview=='opengrapy') ) {//전용앱
				$goods_seq = (int) $goods_seq;
				$CI->load->model('goodsmodel');
				$goods = $CI->goodsmodel->get_goods($goods_seq);
				$likecount = $goods['like_count'];
				$likecountview = ($likecount)?like_count_print($likecount):like_count_print(0);
				//$likecount 천단위, 만단위 자르기

				if( $CI->session->userdata('fbuser') ) {
					$sns_id = $CI->session->userdata('fbuser');
				}elseif(get_cookie('fbuser')){
					$sns_id = get_cookie('fbuser');
				}
				$session_id = $CI->session->userdata('session_id');//like 한경우 DB 화처리

				$CI->load->model('goodsfblike');
				if($CI->userInfo['member_seq']){
					$CI->load->model('membermodel');
					$CI->mdata = $CI->membermodel->get_member_data($CI->userInfo['member_seq']);//회원정보
					$sns_id = $CI->mdata['sns_f'];
					if($sns_id){
						$addwhereis = " and (session_id='".$session_id."' or sns_id = '".$sns_id."'  or member_seq = '".$CI->userInfo['member_seq']."' ) ";
					}else{
						$addwhereis = " and (session_id='".$session_id."' or member_seq = '".$CI->userInfo['member_seq']."' ) ";
					}
				}else{
					if($sns_id){
						$addwhereis = " and (session_id='".$session_id."' or sns_id = '".$sns_id."' ) ";
					}else{
						$addwhereis = " and (session_id='".$session_id."' ) ";
					}
				}
				$sc['select']  = "  like_seq, member_seq, sns_id, fb_action_id, session_id  ";
				$sc['whereis'] = " and goods_seq='".$goods_seq."' ".$addwhereis;
				$ckfblike = $CI->goodsfblike->get_data($sc);//like 한경우 DB 화처리
				if($ckfblike && $likecount > 0 ) {
					$likeok = 'style="color:red" data-fblikeseq="'.$ckfblike['like_seq'].'"  data-fblikeid="'.$ckfblike['fb_action_id'].'" title="좋아요 취소" ';//
					$likeokimg = ($CI->arrSns['fb_likebox_icon'])?$CI->arrSns['fb_likebox_icon']:"/app/javascript/plugin/fblike/images/fblikebox.png";
				}else{
					$likeok = 'style="color:blue" data-fblikeseq=""  data-fblikeid="" title="좋아요" ';
					$likeokimg = ($CI->arrSns['fb_unlikebox_icon'])?$CI->arrSns['fb_unlikebox_icon']:"/app/javascript/plugin/fblike/images/fbunlikebox.png";
				}
				$facebooklogin = (empty($sns_id))?"-login":"";

				if( $fblikebox == "box_count" ){
					$facebooklink .= '<div class="fblikeopengrapyboxbtn  fb-og-like-lay'.$goods_seq.'  fb-og-like'.$facebooklogin.' hand" data-href="'.urlencode($href).'" goodsseq="'.$goods_seq.'" data-layout="'.$fblikebox.'" data-send="false" data-show-faces="false" data-host="'.$httphost.'" data-returnurl="'.$_SERVER['HTTP_HOST'].'"  data-ssid="'.$CI->session->userdata('session_id').'" '.$likeok.'>';
	$facebooklink .= '<div  >
		<div class="pluginCountBox">
			<div>
				<span >
				<span class="fb-og-like-count fb-og-like-count'.$goods_seq.'">'.($likecountview).'</span>
				</span>
		</div>
		</div>
		<div class="pluginCountBoxNub"><s></s><i></i></div>
	</div>';//countbox
					$facebooklink .= '<div><img  class="fb-og-like-img'.$goods_seq.'"  src="'.$likeokimg.'"  align="absmiddle" ></div>';//img
				$facebooklink .= '</div>';
				}else{
					$facebooklink .= '<div class="fblikeopengrapybtn fb-og-like-lay'.$goods_seq.' fb-og-like'.$facebooklogin.' hand" data-href="'.urlencode($href).'" goodsseq="'.$goods_seq.'" data-layout="'.$fblikebox.'" data-send="false" data-show-faces="false" data-host="'.$httphost.'" data-returnurl="'.$_SERVER['HTTP_HOST'].'"  data-ssid="'.$CI->session->userdata('session_id').'" '.$likeok.'>';
					$facebooklink .= '<div  style="display: inline-block;" ><img class=" fb-og-like-img'.$goods_seq.'" src="'.$likeokimg.'"   align="absmiddle" ></div>';//img
	$facebooklink .= '<div style="display: inline-block;" >
		<div class="pluginCountButton pluginCountNum">
			<span >
				<span class="fb-og-like-count fb-og-like-count'.$goods_seq.'">'.$likecountview.'</span>
			</span>
		</div>
		<div class="pluginCountButtonNub"><s></s><i></i></div>
	</div>';//countbox
				$facebooklink .= '</div>';
				}
				echo $facebooklink;
			}else{// fb-like box
				echo '<fb:like class="fb-like" action="like" href="'.$href.'" layout="'.$fblikebox.'" send="false" show-faces="false" share="false" width="100" ></fb:like>';
			}
		}
	}//endif
}
?>