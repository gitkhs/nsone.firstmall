<?php
function snslinkurl($snstype , $subject,$snsviewr = 'all' )
{
	$CI =& get_instance();
	$CI->load->library('snssocial');
	$CI->load->helper('design');
	$CI->config_basic = config_load('basic');

	if( defined('__ADMIN__') || defined('__SELLERADMIN__') ) {//관리자접근dir
		$CI->board_icon_src = '/data/skin/'.$CI->workingSkin.'/images/board/icon/';//
		$CI->board_icon_dir = ROOTPATH.'/data/skin/'.$CI->workingSkin.'/images/board/icon/';//
	}else{
		$CI->board_icon_src = '/data/skin/'.$CI->realSkin.'/images/board/icon/';//
		$CI->board_icon_dir = ROOTPATH.'/data/skin/'.$CI->realSkin.'/images/board/icon/';//
	}

	$CI->admin_board_icon_dir = ROOTPATH.'/admin/skin/'.$CI->config_system['adminSkin'].'/images/board/icon/';//

	$sns_icon_dir['tw']	= $CI->board_icon_dir.'sns_t0.gif';//트위터
	$sns_icon_dir['fa']	= $CI->board_icon_dir.'sns_f0.gif';//페이스북
	$sns_icon_dir['me']	= $CI->board_icon_dir.'sns_m0.gif';//미투데이
	$sns_icon_dir['yo']	= $CI->board_icon_dir.'sns_y0.gif';//요즘
	$sns_icon_dir['go']	= $CI->board_icon_dir.'sns_g0.gif';//Ask on Google+
	$sns_icon_dir['my']	= $CI->board_icon_dir.'sns_my0.gif';//마이피플
	$sns_icon_dir['pi']	= $CI->board_icon_dir.'sns_p0.gif';//핀터레스트 pinterest
	$sns_icon_dir['cy']	= $CI->board_icon_dir.'sns_c0.gif';//싸이월드
	$sns_icon_dir['na']	= $CI->board_icon_dir.'sns_na0.gif';//싸이월드

	$sns_icon_dir['ka']	= $CI->board_icon_dir.'sns_k0.gif';//카카오톡
	$sns_icon_dir['kakaostory']	= $CI->board_icon_dir.'sns_ks0.png';//카카오톡
	$sns_icon_dir['line']	= $CI->board_icon_dir.'sns_ln0.png';//LINE

	if( !is_file($sns_icon_dir['go'])) @copy($CI->admin_board_icon_dir.'sns_g0.gif',$sns_icon_dir['go']);
	if( !is_file($sns_icon_dir['ka'])) @copy($CI->admin_board_icon_dir.'sns_k0.gif',$sns_icon_dir['ka']);
	if( !is_file($sns_icon_dir['my'])) @copy($CI->admin_board_icon_dir.'sns_my0.gif',$sns_icon_dir['my']);
	if( !is_file($sns_icon_dir['pi'])) @copy($CI->admin_board_icon_dir.'sns_p0.gif',$sns_icon_dir['pi']);
	if( !is_file($sns_icon_dir['cy'])) @copy($CI->admin_board_icon_dir.'sns_c0.gif',$sns_icon_dir['cy']);
	if( !is_file($sns_icon_dir['na'])) @copy($CI->admin_board_icon_dir.'sns_na0.gif',$sns_icon_dir['na']);
	if( !is_file($sns_icon_dir['kakaostory'])) @copy($CI->admin_board_icon_dir.'sns_ks0.png',$sns_icon_dir['kakaostory']);
	if( !is_file($sns_icon_dir['line'])) @copy($CI->admin_board_icon_dir.'sns_ln0.png',$sns_icon_dir['line']);

	$sns_icon_src['tw']	= $CI->board_icon_src.'sns_t0.gif';//트위터
	$sns_icon_src['fa']	= $CI->board_icon_src.'sns_f0.gif';//페이스북
	$sns_icon_src['me']	= $CI->board_icon_src.'sns_m0.gif';//미투데이
	$sns_icon_src['yo']	= $CI->board_icon_src.'sns_y0.gif';//요즘

	$sns_icon_src['go']	= $CI->board_icon_src.'sns_g0.gif';//Ask on Google+
	$sns_icon_src['my']	= $CI->board_icon_src.'sns_my0.gif';//마이피플
	$sns_icon_src['pi']	= $CI->board_icon_src.'sns_p0.gif';//핀터레스트 pinterest
	//이미지만을 공유하는 특화된 소셜 네트워크 서비스 입니다.

	$sns_icon_src['cy']	= $CI->board_icon_src.'sns_c0.gif';//싸이월드공감 c로그
	$sns_icon_src['na']	= $CI->board_icon_src.'sns_na0.gif';//네이트

	$sns_icon_src['ka']	= $CI->board_icon_src.'sns_k0.gif';//카카오톡
	$sns_icon_src['kakaostory']	= $CI->board_icon_src.'sns_ks0.png';//카카오톡
	//카카오톡 mobile 인 경우에만 url복사 카카오톡은 URL이 아니라 어플리케이션이 가지고 있는 URI를 통해서 어플리케이션 자체를 호출하는 방식
	$sns_icon_src['line']	= $CI->board_icon_src.'sns_ln0.png';//LINE
	//snsset['pi']		= '';

	/* 따옴표,쌍따옴표 있을경우 에러 발생으로 추가. leewh 2014-10-29 */
	$tmp_sns_tit = str_replace(array('&quot;', '&apos;'), array('"', "'"), $CI->config_basic['shopName']);
	$tmp_sns_tit = str_replace("'", "\'", $tmp_sns_tit);
	$tmp_sns_tit = str_replace('"', "\'", $tmp_sns_tit);

	$tmp_sns_tag = str_replace(array('&quot;', '&apos;'), array('"', "'"), $CI->config_basic['shopTitleTag']);
	$tmp_sns_tag = str_replace("'", "\'", $tmp_sns_tag);
	$tmp_sns_tag = str_replace('"', "\'", $tmp_sns_tag);

	$sns_tit	= urlencode($tmp_sns_tit);
	$sns_tag	 = urlencode($tmp_sns_tag);

	if($CI->arrSns['likeurl']){
		$sns_url	= urlencode("http://".$CI->arrSns['likeurl'].$_SERVER['REQUEST_URI']);
		$sns_host	= "http://".$CI->arrSns['likeurl'];
	}else{ 
		$sns_url	= urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$sns_host	= "http://".$_SERVER['HTTP_HOST'];
	}  
	$sns_url_fa = ($sns_url);

	/**if($CI->arrSns['kakaotalk_app_domain']) {
		$sns_url_ka_http	= $CI->arrSns['kakaotalk_app_domain'];
		$sns_url_ka	= $sns_url_ka_http.$_SERVER['REQUEST_URI'];
	}else{
		$sns_url_ka_http	= "http://".$_SERVER['HTTP_HOST'];
	}**/
	$sns_url_ka_http	= "http://".$_SERVER['HTTP_HOST'];

	if($snstype == 'board'){
		if($CI->arrSns['likeurl']){
			$linkurl		 = ($_GET['id'])?'http://'.$CI->arrSns['likeurl'].'/board/view?id='.$_GET['id'].'&seq='.$_GET['seq']:'http://'.$_SERVER['HTTP_HOST'].'/board/view?id='.BOARDID.'&seq='.$_GET['seq'];

			$sns_url_ka		 = ($_GET['id'])?$sns_url_ka_http.'/board/'.urlencode('view?id='.$_GET['id'].'&seq='.$_GET['seq']):$sns_url_ka_http.'/board/'.urlencode('view?id='.BOARDID.'&seq='.$_GET['seq']);

		}else{
			$linkurl		 = ($_GET['id'])?'http://'.$_SERVER['HTTP_HOST'].'/board/view?id='.$_GET['id'].'&seq='.$_GET['seq']:'http://'.$_SERVER['HTTP_HOST'].'/board/view?id='.BOARDID.'&seq='.$_GET['seq'];
		}
		$sns_url	= urlencode($linkurl);
		$sns_url_fa = ($sns_url);

		$sns_sbj	= urlencode(strip_tags($subject));
	}elseif($snstype == 'goods'){
		$sns_url_fa	= urlencode($CI->likeurl.'&no='.$_GET['no']);
		$sns_sbj	= urlencode(strip_tags($subject));

		## 상품디스플레이에서 리턴되는 상품 url 2014-11-04
		if($CI->uri->uri_string == "common/snslinkurl_tag"){
			$sns_url = urlencode($sns_host."/goods/view?no=".$_GET['no']);
		}

		$CI->load->model('goodsmodel');
		$images = $CI->goodsmodel->get_goods_image($_GET['no']);
		if($images){
			foreach($images as $image){
				if($image['list1']['image']) {
					$filetypetmp = @getimagesize(ROOTPATH.$image['list1']['image']);
					if($filetypetmp[0] >= 70) {
						$imgurl			= $image['list1']['image']; 
						$imgwidth	= ($filetypetmp[0]>70)?$filetypetmp[0]:'70';
						$imgheight	=($filetypetmp[1]>70)?$filetypetmp[1]:'70';
						$imgurl			= $sns_url_ka_http.$imgurl;
						break;
					}
				}

				if($image['list2']['image']) {
					$filetypetmp = @getimagesize(ROOTPATH.$image['list2']['image']);
					if($filetypetmp[0] >= 70) {
						$imgurl			= $image['list2']['image']; 
						$imgwidth	= ($filetypetmp[0]>70)?$filetypetmp[0]:'70';
						$imgheight	=($filetypetmp[1]>70)?$filetypetmp[1]:'70';
						$imgurl			= $sns_url_ka_http.$imgurl;
						break;
					}
				}

				$filetypetmp = @getimagesize(ROOTPATH.$image['view']['image']);
				if($filetypetmp[0] >= 70) {
					$imgurl			= $image['view']['image']; 
					$imgwidth	= ($filetypetmp[0]>70)?$filetypetmp[0]:'70';
					$imgheight	=($filetypetmp[1]>70)?$filetypetmp[1]:'70';
					$imgurl			= $sns_url_ka_http.$imgurl;
					break;
				}
			}//endforeach

		}//endif
	}elseif($snstype == 'event'){
		$sns_sbj	= urlencode(strip_tags($subject));
	}

	if(empty($imgurl)) { 
		if( is_file(ROOTPATH.$CI->config_system['snslogo']) ) {
			$filetypetmp = @getimagesize(ROOTPATH.$CI->config_system['snslogo']);
			if($filetypetmp[0] >= 70) {
				$imgurl = $sns_url_ka_http.$CI->config_system['snslogo']; 
				$imgwidth = $filetypetmp[0];
				$imgheight =$filetypetmp[1];
			}
		}
	}

	$snskor = array('fa'=>'페이스북','tw'=>'트위터','go'=>'구글','cy'=>'싸이월드','my'=>'마이피플','ka'=>'카카오톡','kakaostory'=>'카카오스토리','line'=>'LINE');//,'pi'=>'핀터레스트''yo'=>'요즘','me'=>'미투데이',

	$imageTag = '';
	foreach($snskor as $_key=>$_val){
		if($snsviewr == 'all' || ($snsviewr != 'all' && $snsviewr == $_key)) {//전체출력이거나 개별출력시
			if( ($_key ==  'ka' || $_key ==  'kakaostory' || $_key ==  'line' ) && !$CI->_is_mobile_agent && !defined('__ADMIN__') ) continue;//mobile 접속시에만 추가

			$category_config = skin_configuration($CI->skin);
			if( $CI->storemobileMode || ($CI->mobileMode && $category_config['mobile_version'] == 2) ) {
				$iconsize = ' width="33" height="33" ';
			}
			if($CI->storeMode){
				//$iconsize = ' width="33" height="33" ';
			}

			if( $_key ==  'ka' || $_key ==  'kakaostory' || $_key ==  'line' ){
				$sns_tit	= urldecode($sns_tit);
				$sns_sbj	= urldecode($sns_sbj);
				$sns_tag	= urldecode($sns_tag); 
				$sns_url	= ($linkurlka)?$linkurlka:urldecode($sns_url);
			}

			if( is_file($sns_icon_dir[$_key]) ) {
				if( $_key ==  'fa' ) {
					$imageTag .= '<span class="snsbox hand "><img src="'.$sns_icon_src[$_key].'" alt="'.$_val.'"  title="'.$_val.'" '.$iconsize.' valign="middle" onclick="snsWin(\''.$_key.'\',\''.$sns_tit.'\', \''.$sns_sbj.'\', \''.$sns_tag.'\', \''.$sns_url_fa.'\',\''.$CI->_is_mobile_agent.'\',\'\',\'\',\'\');" /></span>&nbsp;';
				}elseif( $_key ==  'ka' ) {

					if(!empty($CI->arrSns['kakaotalk_app_javascript_key']) ) {// !empty($CI->arrSns['kakaotalk_app_domain']) && date('Ym')>='201407' || ( 7월
						if( !defined('__ADMIN__') && !defined('__SELLERADMIN__') ) {//관리자스크립트충돌수정
							//$imageTag .= '<script src="https://developers.kakao.com/sdk/js/kakao.min.js"></script>';
							$imageTag .= "<script type='text/javascript'src='/app/javascript/plugin/kakao/kakao.min.js'></script>";
							$imageTag .= "<script>Kakao.init('".$CI->arrSns['kakaotalk_app_javascript_key']."');</script>"; 
						}
						$imageTag .= '<span class="snsbox hand  kakao-link-btn"><img src="'.$sns_icon_src[$_key].'" alt="'.$_val.'"  title="'.$_val.'" '.$iconsize.' valign="middle" onclick="snsWin(\'kaapi\',\''.$sns_tit.'\', \''.$sns_sbj.'\', \''.$sns_tag.'\', \''.$sns_url.'\',\''.$CI->_is_mobile_agent.'\',\''.$imgurl.'\',\''.$imgwidth.'\',\''.$imgheight.'\');" /></span>&nbsp;'; 
					}else{
						$imageTag .= "<script type='text/javascript'src='/app/javascript/js/kakao.link.js'></script>";
						$imageTag .= '<span class="snsbox hand  kakao-link-btn"><img src="'.$sns_icon_src[$_key].'" alt="'.$_val.'"  title="'.$_val.'" '.$iconsize.' valign="middle" onclick="snsWin(\'ka\',\''.$sns_tit.'\', \''.$sns_sbj.'\', \''.$sns_tag.'\', \''.$sns_url.'\',\''.$CI->_is_mobile_agent.'\',\''.$imgurl.'\',\''.$imgwidth.'\',\''.$imgheight.'\');" /></span>&nbsp;';
					}
				}elseif( $_key ==  'kakaostory') { 
					$imageTag .= "<script type='text/javascript'src='/app/javascript/plugin/kakao/kakaostory.link.js'></script>";
					$imageTag .= '<span class="snsbox hand" ><img src="'.$sns_icon_src[$_key].'" alt="'.$_val.'"  title="'.$_val.'" '.$iconsize.' valign="middle" onclick="snsWin(\''.$_key.'\',\''.$sns_tit.'\', \''.$sns_sbj.'\', \''.$sns_tag.'\', \''.($sns_url).'\',\''.$CI->_is_mobile_agent.'\',\''.$imgurl.'\',\''.$imgwidth.'\',\''.$imgheight.'\');" /></span>&nbsp;';
				}else{
					$imageTag .= '<span class="snsbox hand" ><img src="'.$sns_icon_src[$_key].'" alt="'.$_val.'"  title="'.$_val.'" '.$iconsize.' valign="middle" onclick="snsWin(\''.$_key.'\',\''.$sns_tit.'\', \''.$sns_sbj.'\', \''.$sns_tag.'\', \''.$sns_url.'\',\''.$CI->_is_mobile_agent.'\',\''.$imgurl.'\',\''.$imgwidth.'\',\''.$imgheight.'\');" /></span>&nbsp;';
				}
			}
		}
	};//endforeach;

	echo $imageTag;

}
?>